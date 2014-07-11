<?php

class Atme extends ICModel
{
    private $_atRegex = "/@(.+?)(?=[\s|:]|$)/is";

    public static function model($className = "Atme")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{atme}}";
    }

    public function fetchAllAtmeListByUid($uid, $limit, $offset, $order = "atid DESC")
    {
        $criteria = array(
            "condition" => "`uid` = :uid",
            "params"    => array(":uid" => $uid),
            "order"     => $order,
            "limit"     => $limit,
            "offset"    => $offset
        );
        $data = $this->fetchAll($criteria);

        if (!empty($data)) {
            foreach ($data as &$v) {
                $v = Source::getSourceInfo($v["table"], $v["rowid"], false, $v["module"]);
            }
        }

        $uid && UserData::model()->resetUserCount($uid, "unread_atme", 0);
        return $data;
    }

    public function addAtme($module, $table, $content, $rowId, $extraUids = null, $lessUids = null)
    {
        $extraUids = array_diff((array) $extraUids, array(Ibos::app()->user->uid));
        $extraUids = array_unique($extraUids);
        $extraUids = array_filter($extraUids);
        $lessUids[] = (int) Ibos::app()->user->uid;
        $lessUids = array_unique($lessUids);
        $lessUids = array_filter($lessUids);
        $uids = $this->getUids($content, $extraUids, $lessUids);
        $result = $this->saveAtme($module, $table, $uids, $rowId);
        return $result;
    }

    public function updateRecentAt($content)
    {
        preg_match_all($this->_atRegex, $content, $matches);
        $unames = $matches[1];

        if (isset($unames[0])) {
            $curUid = Ibos::app()->user->uid;
            $map = array("select" => "uid", "condition" => "realname in ('" . implode("','", $unames) . "') AND uid!=" . $curUid);
            $userIds = User::model()->fetchAllSortByPk("uid", $map);
            $matchUids = ConvertUtil::getSubByKey($userIds, "uid");
            $value = UserData::model()->fetchKeyValueByUid($curUid, "user_recentat");

            if ($value) {
                $atData = ConvertUtil::getSubByKey($value, "id");
                $atData && ($matchUids = array_merge($matchUids, $atData));
                $matchUids = array_slice(array_unique($matchUids), 0, 10);

                foreach ($matchUids as $uid) {
                    $user = User::model()->fetchByUid($uid);
                    $udata[] = array("id" => $user["uid"], "name" => $user["realname"], "imgUrl" => $user["avatar_small"]);
                }

                UserData::model()->updateAll(array("value" => serialize($udata)), "`key`='user_recentat' AND uid=" . $curUid);
            } else {
                $udata = array();
                $matchUids = array_slice($matchUids, 0, 10);

                foreach ($matchUids as $uid) {
                    $user = User::model()->fetchByUid($uid);
                    $udata[] = array("id" => $user["uid"], "name" => $user["realname"]);
                }

                $data["uid"] = $curUid;
                $data["key"] = "user_recentat";
                $data["value"] = serialize($udata);
                $data["mtime"] = date("Y-m-d H:i:s");
                UserData::model()->add($data);
            }
        }
    }

    public function getUids($content, $extraUids = null, $lessUids = null)
    {
        preg_match_all($this->_atRegex, $content, $matches);
        $unames = $matches[1];
        $map = "realname in ('" . implode("','", $unames) . "')";
        $ulist = User::model()->fetchAll($map);
        $matchUids = ConvertUtil::getSubByKey($ulist, "uid");
        if (empty($matchUids) && !empty($extraUids)) {
            if (!empty($lessUids)) {
                foreach ($lessUids as $k => $v) {
                    if (in_array($v, $extraUids)) {
                        unset($extraUids[$k]);
                    }
                }
            }

            return is_array($extraUids) ? $extraUids : array($extraUids);
        }

        $suid = array();

        foreach ($matchUids as $v) {
            !in_array($v, $suid) && ($suid[] = (int) $v);
        }

        if (!empty($lessUids)) {
            foreach ($suid as $k => $v) {
                if (in_array($v, $lessUids)) {
                    unset($suid[$k]);
                }
            }
        }

        return array_unique(array_filter(array_merge($suid, (array) $extraUids)));
    }

    private function saveAtme($module, $table, $uids, $rowId)
    {
        $self = Ibos::app()->user->uid;

        foreach ($uids as $uid) {
            if ($uid == $self) {
                continue;
            }

            $data[] = array("module" => $module, "table" => $table, "rowid" => $rowId, "uid" => $uid);
            UserData::model()->updateUserCount($uid, "unread_atme", 1);
        }

        $res = array();

        if (!empty($data)) {
            foreach ($data as $value) {
                $res[] = $this->add($value, true);
            }
        }

        return !empty($res) ? implode(",", $res) : "";
    }

    public function deleteAtme($table, $content, $rowId, $extraUids = null)
    {
        $uids = $this->getUids($content, $extraUids);
        $result = $this->_deleteAtme($table, $uids, $rowId);
        return $result;
    }

    private function _deleteAtme($table, $uids, $rowId)
    {
        $result = false;

        if (!empty($uids)) {
            $res = $this->deleteAll(array(
                "condition" => "`table` = :table AND `rowid` = :rowid AND `uid` IN (:uid)",
                "params"    => array(":table" => $table, ":rowid" => $rowId, ":uid" => implode(",", $uids))
            ));
            ($res !== false) && ($result = true);
        } else {
            $res = $this->deleteAll(array(
                "condition" => "`table` = :table AND `rowid` = :rowid",
                "params"    => array(":table" => $table, ":rowid" => $rowId)
            ));
            ($res !== false) && ($result = true);
        }

        return $result;
    }
}
