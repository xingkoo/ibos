<?php

class FeedDigg extends ICModel
{
    public static function model($className = "FeedDigg")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{feed_digg}}";
    }

    public function fetchUserList($feedId, $nums, $offset = 0, $order = "ctime DESC")
    {
        $criteria = array("select" => "uid,ctime", "condition" => sprintf("feedid = %d", $feedId), "order" => $order, "offset" => $offset, "limit" => $nums);
        $result = $this->fetchAll($criteria);

        if ($result) {
            foreach ($result as &$res) {
                $res["user"] = User::model()->fetchByUid($res["uid"]);
                $res["diggtime"] = ConvertUtil::formatDate($res["ctime"]);
            }
        } else {
            $result = array();
        }

        return $result;
    }

    public function checkIsDigg($feedIds, $uid)
    {
        if (!is_array($feedIds)) {
            $feedIds = array($feedIds);
        }

        $res = array();

        if (!empty($feedIds)) {
            $feedIds = array_filter($feedIds);
            $criteria = array("select" => "feedid", "condition" => sprintf("`uid` = %d AND `feedid` IN (%s)", $uid, implode(",", $feedIds)));
            $list = $this->fetchAll($criteria);

            foreach ($list as $v) {
                $res[$v["feedid"]] = 1;
            }
        }

        return $res;
    }

    public function getIsExists($feedId, $uid)
    {
        $criteria = array("select" => "1", "condition" => sprintf("feedid = %d AND uid = %d", $feedId, $uid));
        $result = $this->fetch($criteria);
        return $result ? true : false;
    }

    public function addDigg($feedId, $uid)
    {
        $data["feedid"] = $feedId;
        $data["uid"] = $uid;
        $data["uid"] = (!$data["uid"] ? Ibos::app()->user->uid : $data["uid"]);

        if (!$data["uid"]) {
            $this->addError("addDigg", "未登录不能赞");
            return false;
        }

        $isExit = $this->getIsExists($feedId, $uid);

        if ($isExit) {
            $this->addError("addDigg", "你已经赞过");
            return false;
        }

        $data["ctime"] = time();
        $res = $this->add($data);

        if ($res) {
            $feed = Source::getSourceInfo("feed", $feedId);
            Feed::model()->updateCounters(array("diggcount" => 1), "feedid = " . $feedId);
            Feed::model()->cleanCache($feedId);
            $user = User::model()->fetchByUid($uid);
            $config["{user}"] = $user["realname"];
            $config["{sourceContent}"] = StringUtil::filterCleanHtml($feed["source_body"]);
            $config["{sourceContent}"] = str_replace("◆", "", $config["{sourceContent}"]);
            $config["{sourceContent}"] = StringUtil::cutStr($config["{sourceContent}"], 34);
            $config["{url}"] = $feed["source_url"];
            $config["{content}"] = Ibos::app()->getController()->renderPartial("application.modules.message.views.remindcontent", array("recentFeeds" => Feed::model()->getRecentFeeds()), true);
            Notify::model()->sendNotify($feed["uid"], "message_digg", $config);
            UserUtil::updateCreditByAction("diggweibo", $uid);
            UserUtil::updateCreditByAction("diggedweibo", $feed["uid"]);
        }

        return $res;
    }

    public function delDigg($feedId, $uid)
    {
        $data["feedid"] = $feedId;
        $data["uid"] = $uid;
        $data["uid"] = (!$data["uid"] ? Ibos::app()->user->uid : $data["uid"]);

        if (!$data["uid"]) {
            $this->addError("delDigg", "未登录不能取消赞");
            return false;
        }

        $isExit = $this->getIsExists($feedId, $uid);

        if (!$isExit) {
            $this->addError("delDigg", "取消赞失败，您可能已取消过赞信息");
            return false;
        }

        $res = $this->deleteAllByAttributes($data);

        if ($res) {
            Feed::model()->updateCounters(array("diggcount" => -1), "feedid=" . $feedId);
            Feed::model()->cleanCache($feedId);
        }

        return $res;
    }
}
