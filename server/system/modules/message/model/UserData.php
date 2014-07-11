<?php

class UserData extends ICModel
{
    public static function model($className = "UserData")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{user_data}}";
    }

    public function fetchKeyValueByUid($uid, $key)
    {
        $criteria = array(
            "select"    => "value",
            "condition" => "`key` = :key AND uid=:uid",
            "params"    => array(":key" => $key, ":uid" => $uid)
        );
        $res = $this->fetch($criteria);
        return !empty($res["value"]) ? unserialize($res["value"]) : array();
    }

    public function fetchRecentAt($uid)
    {
        $criteria = array(
            "select"    => "value",
            "condition" => "`key` = 'user_recentat' AND uid=:uid",
            "params"    => array(":uid" => $uid)
        );
        $res = $this->fetch($criteria);
        return !empty($res["value"]) ? unserialize($res["value"]) : array();
    }

    public function fetchActiveUsers($nums = 10)
    {
        $criteria = array("select" => "uid,value", "condition" => "`key` = 'feed_count'", "order" => "value*1 DESC", "limit" => $nums);
        $res = $this->fetchAll($criteria);

        foreach ($res as &$v) {
            $v["user"] = User::model()->fetchByUid($v["uid"]);
        }

        return $res;
    }

    public function updateUserCount($uid, $key, $rate)
    {
        $this->updateKey($key, $rate, true, $uid);
    }

    public function updateKey($key, $nums, $add = true, $uid = "")
    {
        if ($nums == 0) {
            $this->addError("updateKey", Ibos::lang("Dont need to modify", "message.default"));
            return false;
        }

        ($nums < 0) && ($add = false);
        $key = StringUtil::filterCleanHtml($key);
        $data = $this->getUserData($uid);
        if (empty($data) || !$data) {
            $data = array();
            $data[$key] = $nums;
        } else {
            $data[$key] = ($add ? (int) @$data[$key] + abs($nums) : (int) @$data[$key] - abs($nums));
        }

        ($data[$key] < 0) && ($data[$key] = 0);
        $map["uid"] = (empty($uid) ? Ibos::app()->user->uid : $uid);
        $map["key"] = $key;
        $this->deleteAll("`key` = :key AND uid = :uid", array(":key" => $key, ":uid" => $map["uid"]));
        $map["value"] = $data[$key];
        $map["mtime"] = date("Y-m-d H:i:s");
        $this->add($map);
        CacheUtil::rm("userData_" . $map["uid"]);
        return $data;
    }

    public function getUserData($uid = "")
    {
        if (empty($uid)) {
            $uid = Ibos::app()->user->uid;
        }

        if ((($data = CacheUtil::get("userData_" . $uid)) === false) || (count($data) == 1)) {
            $data = array();
            $list = $this->fetchAll("`uid` = :uid", array(":uid" => $uid));

            if (!empty($list)) {
                foreach ($list as $v) {
                    $data[$v["key"]] = (int) $v["value"];
                }
            }

            CacheUtil::set("userData_" . $uid, $data, 60);
        }

        return $data;
    }

    public function getUnreadCount($uid)
    {
        $userData = $this->getUserData($uid);
        $return["unread_notify"] = intval(NotifyMessage::model()->countUnreadByUid($uid));
        $return["unread_atme"] = (isset($userData["unread_atme"]) ? intval($userData["unread_atme"]) : 0);
        $return["unread_comment"] = (isset($userData["unread_comment"]) ? intval($userData["unread_comment"]) : 0);
        $return["unread_message"] = MessageContent::model()->countUnreadMessage($uid, array(MessageContent::ONE_ON_ONE_CHAT, MessageContent::MULTIPLAYER_CHAT));
        $return["new_folower_count"] = (isset($userData["new_folower_count"]) ? intval($userData["new_folower_count"]) : 0);
        $return["unread_total"] = array_sum($return);
        return $return;
    }

    public function setKeyValue($uid, $key, $value)
    {
        $map["uid"] = $uid;
        $map["key"] = $key;
        $this->deleteAllByAttributes($map);
        $map["value"] = intval($value);
        $map["mtime"] = date("Y-m-d H:i:s");
        $this->add($map);
        CacheUtil::rm("userData_" . $uid);
    }

    public function countUnreadAtMeByUid($uid)
    {
        $criteria = array(
            "select"    => "value",
            "condition" => "`uid` = :uid AND `key` = :key",
            "params"    => array(":uid" => $uid, "key" => "unread_atme")
        );
        $res = $this->fetch($criteria);
        return !empty($res) ? intval($res["value"]) : 0;
    }

    public function resetUserCount($uid, $key, $value = 0)
    {
        $this->setKeyValue($uid, $key, $value);
    }
}
