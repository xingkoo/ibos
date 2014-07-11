<?php

class MessageContent extends ICModel
{
    const ONE_ON_ONE_CHAT = 1;
    const MULTIPLAYER_CHAT = 2;
    const SYSTEM_NOTIFY = 3;

    public static function model($className = "MessageContent")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{message_content}}";
    }

    public function fetchAllMessageListByUid($uid, $type = 1, $limit = 10, $offset = 0)
    {
        $uid = intval($uid);
        $type = (is_array($type) ? " IN (" . implode(",", $type) . ")" : "=$type");
        $list = Ibos::app()->db->createCommand()->from("{{message_user}} AS mu")->join("{{message_list}} AS ml", "`mu`.`listid`=`ml`.`listid`")->where("`mu`.`uid`=$uid AND `ml`.`type`$type AND `mu`.`isdel` = 0 AND mu.messagenum > 0")->order("mu.new DESC,mu.listctime DESC")->limit($limit, $offset)->queryAll();
        $this->parseMessageList($list);
        return $list;
    }

    public function fetchAllMessageByListId($listId, $uid, $sinceId = null, $maxId = null, $count = 20)
    {
        $listId = intval($listId);
        $uid = intval($uid);
        $sinceId = intval($sinceId);
        $maxId = intval($maxId);
        $count = intval($count);
        if (!$listId || !$uid || !$messageInfo = $this->isInList($listId, $uid, false)) {
            return false;
        }

        $where = "`listid`=$listId AND `isdel`=0";

        if (0 < $sinceId) {
            $where .= " AND `messageid` > $sinceId";
            (0 < $maxId) && ($where .= " AND `messageid` < $maxId");
            $limit = intval($count) + 1;
        } else {
            (0 < $maxId) && ($where .= " AND `messageid` < $maxId");
            $limit = intval($count) + 1;
        }

        $res = array();
        $res["data"] = $this->fetchAll(array("condition" => $where, "order" => "messageid DESC", "limit" => $limit));
        $res["count"] = count($res["data"]);

        if (0 < $sinceId) {
            $res["sinceid"] = (isset($res["data"][0]["messageid"]) ? $res["data"][0]["messageid"] : 0);
            $res["maxid"] = (0 < $res["count"] ? $res["data"][$res["count"] - 1]["messageid"] : 0);

            if ($res["count"] < $limit) {
                $res["maxid"] = 0;
            }
        } else {
            $res["sinceid"] = $res["data"][0]["messageid"];

            if ($res["count"] == $limit) {
                array_pop($res["data"]);
                $res["count"]--;
                $res["maxid"] = $res["data"][$res["count"] - 1]["messageid"];
            } elseif ($res["count"] < $limit) {
                $res["maxid"] = 0;
            }
        }

        return $res;
    }

    public function postMessage($data, $fromUid)
    {
        $fromUid = intval($fromUid);
        $data["touid"] = (is_array($data["touid"]) ? $data["touid"] : explode(",", $data["touid"]));
        $data["users"] = array_filter(array_merge(array($fromUid), $data["touid"]));
        $data["mtime"] = time();

        if (false == $data["listid"] = $this->addMessageList($data, $fromUid)) {
            $this->addError("message", Ibos::lang("private message send fail", "message.default"));
            return false;
        }

        if (false === $this->addMessageUser($data, $fromUid)) {
            $this->addError("message", Ibos::lang("private message send fail", "message.default"));
            return false;
        }

        if (false == $this->addMessage($data, $fromUid)) {
            $this->addError("message", Ibos::lang("private message send fail", "message.default"));
            return false;
        }

        $author = User::model()->fetchByUid($fromUid);
        $config["name"] = $author["realname"];
        $config["content"] = $data["content"];
        $config["ctime"] = date("Y-m-d H:i:s", $data["mtime"]);
        $config["source_url"] = Ibos::app()->urlManager->createUrl("message/pm/index");
        MessageUtil::push("pm", $data["touid"], $data["content"]);
        MessageUtil::appPush($data["touid"], $data["content"]);
        return $data["listid"];
    }

    public function getSinceMessageId($listId, $nums)
    {
        $map["listid"] = $listId;
        $map["isdel"] = 0;
        $criteria = array(
            "select"    => "messageid",
            "condition" => "`listid` = :listid AND `isdel` = 0",
            "params"    => array(":listid" => $listId),
            "order"     => "messageid DESC",
            "limit"     => $nums
            );
        $info = $this->fetchAll($criteria);

        if (0 < $nums) {
            return intval($info[$nums - 1]["messageid"] - 1);
        } else {
            return 0;
        }
    }

    public function countUnreadList($uid)
    {
        $unread = Ibos::app()->db->createCommand()->select("count(*)")->from("{{message_user}} AS mu")->leftJoin("{{message_list}} AS ml", "`mu`.`listid` = `ml`.`listid`")->where("mu.uid = $uid AND mu.new = 1")->queryScalar();
        return intval($unread);
    }

    public function countUnreadMessage($uid, $type = 0)
    {
        $condition = "mu.uid = $uid AND mu.new = 2";

        if ($type) {
            $type = (is_array($type) ? $type : explode(",", $type));
            $typeScope = implode(",", $type);
            $condition .= " AND `type` IN ($typeScope)";
        }

        $unread = Ibos::app()->db->createCommand()->select("count(*)")->from("{{message_user}} AS mu")->leftJoin("{{message_list}} AS ml", "`mu`.`listid` = `ml`.`listid`")->where($condition)->queryScalar();
        return intval($unread);
    }

    public function countMessageListByUid($uid, $type = 1)
    {
        $uid = intval($uid);
        $type = (is_array($type) ? " IN (" . implode(",", $type) . ")" : "=$type");
        $count = Ibos::app()->db->createCommand()->select("count(*)")->from("{{message_user}} AS mu")->join("{{message_list}} AS ml", "`mu`.`listid`=`ml`.`listid`")->where("`mu`.`uid`=$uid AND `ml`.`type`$type AND `mu`.`isdel` = 0 AND mu.messagenum > 0")->queryScalar();
        return intval($count);
    }

    public function replyMessage($listId, $content, $fromUid)
    {
        $listId = intval($listId);
        $fromUid = intval($fromUid);
        $time = time();
        $listInfo = MessageList::model()->fetch(array(
            "select"    => "type,usernum,minmax",
            "condition" => "listid = :listid",
            "params"    => array(":listid" => $listId)
        ));

        if (!in_array($listInfo["type"], array(self::ONE_ON_ONE_CHAT, self::MULTIPLAYER_CHAT))) {
            return false;
        } elseif (!$this->isInList($listId, $fromUid, false)) {
            return false;
        }

        $data["listid"] = $listId;
        $data["content"] = $content;
        $data["mtime"] = $time;
        $newMessageId = $this->addMessage($data, $fromUid);
        unset($data);
        $command = Ibos::app()->db->createCommand();

        if (!$newMessageId) {
            return false;
        } else {
            $listData["lastmessage"] = serialize(array("fromuid" => $fromUid, "content" => StringUtil::filterCleanHtml($content)));

            if (1 == $listInfo["type"]) {
                $listData["usernum"] = 2;
                MessageList::model()->updateByPk($listId, $listData);

                if ($listInfo["usernum"] < 2) {
                    $userData = array("listid" => $listId, "uid" => array_diff(explode("_", $listInfo["minmax"]), array($fromUid)), "ctime" => $time);
                    $this->addMessageUser($userData, $fromUid);
                } else {
                    $command->setText("UPDATE {{message_user}} SET `new` = 2,`messagenum` = `messagenum`+1,`listctime` = $time WHERE `listid` = $listId AND `uid`!=$fromUid")->execute();
                }
            } else {
                MessageList::model()->updateByPk($listId, $listData);
                $command->setText("UPDATE {{message_user}} SET `new` = 2,`messagenum` = `messagenum`+1,`listctime` = $time WHERE `listid` = $listId AND `uid`!=$fromUid")->execute();
            }

            $command->setText("UPDATE {{message_user}} SET `ctime` = $time,`messagenum` = `messagenum`+1,`listctime` = $time WHERE `listid` = $listId AND `uid`=$fromUid")->execute();
        }

        return $newMessageId;
    }

    public function isInList($listId, $uid, $showDetail = false)
    {
        $listId = intval($listId);
        $uid = intval($uid);
        $showDetail = ($showDetail ? 1 : 0);
        static $isMember = array();

        if (!isset($isMember[$listId][$uid][$showDetail])) {
            $map["listid"] = $listId;
            $map["uid"] = $uid;
            $rec = MessageUser::model()->findByAttributes($map);

            if ($showDetail) {
                $isMember[$listId][$uid][$showDetail] = $rec->attributes;
            } else {
                $isMember[$listId][$uid][$showDetail] = $rec["uid"];
            }
        }

        return $isMember[$listId][$uid][$showDetail];
    }

    private function parseMessageList(&$list)
    {
        foreach ($list as &$v) {
            $v["lastmessage"] = unserialize($v["lastmessage"]);
            $v["lastmessage"]["touid"] = $this->parseToUidByMinMax($v["minmax"], $v["lastmessage"]["fromuid"]);
            $v["lastmessage"]["user"] = User::model()->fetchByUid($v["lastmessage"]["fromuid"]);
            $v["touserinfo"] = User::model()->fetchAllByUids($v["lastmessage"]["touid"]);
        }
    }

    private function parseToUidByMinMax($minMaxUids, $fromUid)
    {
        $minMaxUids = explode("_", $minMaxUids);
        return array_values(array_diff($minMaxUids, array($fromUid)));
    }

    private function addMessageList($data, $fromUid)
    {
        if (!$data["content"] || !is_array($data["users"]) || !$fromUid) {
            return false;
        }

        $list["fromuid"] = $fromUid;
        $list["title"] = (isset($data["title"]) ? StringUtil::filterCleanHtml($data["title"]) : StringUtil::filterCleanHtml(StringUtil::cutStr($data["content"], 20)));
        $list["usernum"] = count($data["users"]);
        $list["type"] = (is_numeric($data["type"]) ? $data["type"] : (2 == $list["usernum"] ? 1 : 2));
        $list["minmax"] = $this->getUidMinMax($data["users"]);
        $list["mtime"] = $data["mtime"];
        $list["lastmessage"] = serialize(array("fromuid" => $fromUid, "content" => StringUtil::filterDangerTag($data["content"])));
        $listRec = MessageList::model()->findByAttributes(array("type" => $list["type"], "minmax" => $list["minmax"]));
        $listId = (!empty($listRec) ? $listRec["listid"] : null);
        if (($list["type"] == 1) && $listId) {
            $_list["usernum"] = $list["usernum"];
            $_list["lastmessage"] = $list["lastmessage"];
            $saved = MessageList::model()->updateAll($_list, "`type` = :type AND `minmax` = :minmax AND `listid`=:listid", array(":type" => $list["type"], ":minmax" => $list["minmax"], ":listid" => $listId));

            if (!$saved) {
                $listId = false;
            }
        } else {
            $listId = MessageList::model()->add($list, true);
        }

        return $listId;
    }

    private function addMessageUser($data, $fromUid)
    {
        if (!$data["listid"] || !is_array($data["users"]) || !$fromUid) {
            return false;
        }

        $user["listid"] = $data["listid"];
        $user["listctime"] = $data["mtime"];

        foreach ($data["users"] as $k => $u) {
            $userInfo = MessageUser::model()->findByAttributes(array("listid" => $data["listid"], "uid" => $u));

            if (!empty($userInfo)) {
                $user["ctime"] = $userInfo["ctime"];
                $user["new"] = ($u == $fromUid ? $userInfo["new"] : 2);
                $user["messagenum"] = $userInfo["messagenum"] + 1;
                MessageUser::model()->updateAll($user, "`listid` = :listid AND uid = :uid", array(":listid" => $data["listid"], ":uid" => $u));
            } else {
                $user["ctime"] = ($u == $fromUid ? time() : 0);
                $user["new"] = ($u == $fromUid ? 0 : 2);
                $user["messagenum"] = 1;
                $user["uid"] = $u;
                MessageUser::model()->add($user);
            }
        }
    }

    private function addMessage($data, $fromUid)
    {
        if (!$data["listid"] || !$data["content"] || !$fromUid) {
            return false;
        }

        $message["listid"] = $data["listid"];
        $message["fromuid"] = $fromUid;
        $message["content"] = $data["content"];
        $message["isdel"] = 0;
        $message["mtime"] = $data["mtime"];
        return $this->add($message, true);
    }

    private function getUidMinMax($uids)
    {
        sort($uids);
        return implode("_", $uids);
    }
}
