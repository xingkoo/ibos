<?php

class NotifyMessage extends ICModel
{
    public static function model($className = "NotifyMessage")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{notify_message}}";
    }

    public function fetchAllNotifyListByUid($uid, $order = "ctime DESC", $limit = 10, $offset = 0)
    {
        $criteria = array(
            "condition" => "uid = :uid",
            "params"    => array(":uid" => intval($uid)),
            "group"     => "module",
            "order"     => $order,
            "limit"     => $limit,
            "offset"    => $offset
            );
        $return = array();
        $records = $this->findAll($criteria);

        if (!empty($records)) {
            foreach ($records as $record) {
                $msg = $record->attributes;
                $return[$msg["module"]] = array();
                $criteria = array(
                    "condition" => "isread = 0 AND module = :module AND uid = :uid",
                    "params"    => array(":module" => $msg["module"], ":uid" => $uid),
                    "order"     => "ctime DESC"
                 );
                $new = $this->fetchAll($criteria);

                if (!empty($new)) {
                    $return[$msg["module"]]["newlist"] = $new;
                } else {
                    $return[$msg["module"]]["latest"] = $this->fetch(array(
                        "condition" => "module = :module AND uid = :uid",
                        "params"    => array(":uid" => $uid, ":module" => $msg["module"]),
                        "order"     => "ctime DESC"
                    ));
                }
            }
        }

        return $return;
    }

    public function fetchAllDetailByTimeLine($uid, $module, $limit = 10, $offset = 0)
    {
        $criteria = array(
            "condition" => "uid = :uid AND module = :module",
            "params"    => array(":uid" => intval($uid), "module" => $module),
            "order"     => "ctime DESC",
            "limit"     => $limit,
            "offset"    => $offset
            );
        $return = array();
        $records = $this->findAll($criteria);

        if (!empty($records)) {
            foreach ($records as $record) {
                $msg = $record->attributes;
                $index = date("Yn", $msg["ctime"]);
                $return[$index][$msg["id"]] = $msg;
            }
        }

        return $return;
    }

    public function countUnreadByUid($uid)
    {
        return $this->count("`uid` = :uid AND `isread` = :isread", array(":uid" => $uid, ":isread" => 0));
    }

    public function setRead($uid)
    {
        return $this->updateAll(array("isread" => 1), "uid = :uid", array(":uid" => intval($uid)));
    }

    public function setReadByModule($uid, $module)
    {
        return $this->updateAll(array("isread" => 1), "uid = :uid AND FIND_IN_SET(module,:module)", array(":uid" => intval($uid), ":module" => $module));
    }

    public function sendMessage($data)
    {
        if (empty($data["uid"])) {
            return false;
        }

        $s["uid"] = intval($data["uid"]);
        $s["node"] = StringUtil::filterCleanHtml($data["node"]);
        $s["module"] = StringUtil::filterCleanHtml($data["module"]);
        $s["isread"] = 0;
        $s["title"] = StringUtil::filterCleanHtml($data["title"]);
        $s["body"] = StringUtil::filterDangerTag($data["body"]);
        $s["ctime"] = time();
        $s["url"] = $data["url"];
        return $this->add($s, true);
    }

    public function deleteNotify($id, $type = "id")
    {
        $uid = Yii::app()->user->uid;

        if ($type == "id") {
            return $this->deleteAll("uid = :uid AND FIND_IN_SET(id,:id)", array(":uid" => $uid, ":id" => $id));
        } elseif ($type == "module") {
            return $this->deleteAll("uid = :uid AND FIND_IN_SET(module,:module)", array(":uid" => $uid, ":module" => $id));
        }
    }

    public function fetchPageCountByUid($uid)
    {
        $pageCount = $this->count(array(
            "select"    => "id",
            "condition" => "uid=:uid",
            "params"    => array(":uid" => $uid),
            "group"     => "module"
        ));
        return $pageCount;
    }
}
