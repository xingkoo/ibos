<?php

class MessageUser extends ICModel
{
    public static function model($className = "MessageUser")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{message_user}}";
    }

    public function getMessageUsers($listId, $field = null)
    {
        $listId = intval($listId);
        static $users = array();

        if (!isset($users[$listId])) {
            $criteria = array("select" => $field, "condition" => "`listid`=$listId");
            $users[$listId] = $this->fetchAll($criteria);

            foreach ($users[$listId] as $userListKey => $userListValue) {
                $users[$listId][$userListKey]["user"] = User::model()->fetchByUid($userListValue["uid"]);
            }
        }

        return $users[$listId];
    }

    public function setMessageAllRead($uid, $val = 0)
    {
        $condition = "uid = " . intval($uid);
        $updateRows = $this->updateAll(array("new" => $val), $condition);
        return !!$updateRows;
    }

    public function setMessageIsRead($uid, $listIds = null, $val = 0)
    {
        $condition = "uid = " . intval($uid);

        if (!empty($listIds)) {
            !is_array($listIds) && ($listIds = explode(",", $listIds));
            $condition .= " AND `listid` IN (" . implode(",", $listIds) . ")";
        } else {
            $condition .= " AND `new` = 2";
        }

        $updateRows = $this->updateAll(array("new" => $val), $condition);
        return !!$updateRows;
    }

    public function deleteMessageByListId($uid, $listId)
    {
        if (!$listId || !$uid) {
            return false;
        }

        $res = $this->updateAll(array("messagenum" => 0), "FIND_IN_SET(listid,'$listId') AND uid = $uid");

        if ($res) {
            return true;
        } else {
            return false;
        }
    }
}
