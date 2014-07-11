<?php

class Follow extends ICModel
{
    public static function model($className = "Follow")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{user_follow}}";
    }

    public function doFollow($uid, $fid)
    {
        if ((intval($uid) <= 0) || ($fid <= 0)) {
            $this->addError("doFollow", Ibos::lang("Parameters error", "error"));
            return false;
        }

        if ($uid == $fid) {
            $this->addError("doFollow", Ibos::lang("Following myself forbidden", "message.default"));
            return false;
        }

        if (!User::model()->fetchByUid($fid)) {
            $this->addError("doFollow", Ibos::lang("Following people noexist", "message.default"));
            return false;
        }

        $followState = $this->getFollowState($uid, $fid);

        if (0 == $followState["following"]) {
            $map["uid"] = $uid;
            $map["fid"] = $fid;
            $map["ctime"] = time();
            $result = $this->add($map);
            $user = User::model()->fetchByUid($uid);
            $config = array("{user}" => $user["realname"], "{url}" => Ibos::app()->urlManager->createUrl("weibo/personal/follower"));
            Notify::model()->sendNotify($fid, "user_follow", $config);

            if ($result) {
                $this->addError("doFollow", Ibos::lang("Add follow success", "message.default"));
                $this->_updateFollowCount($uid, $fid, true);
                $followState["following"] = 1;
                return $followState;
            } else {
                $this->addError("doFollow", Ibos::lang("Add follow fail", "message.default"));
                return false;
            }
        } else {
            $this->addError("doFollow", Ibos::lang("Following", "message.default"));
            return false;
        }
    }

    public function unFollow($uid, $fid)
    {
        $map["uid"] = $uid;
        $map["fid"] = $fid;
        $followState = $this->getFollowState($uid, $fid);

        if ($followState["following"] == 1) {
            if ($this->deleteAllByAttributes($map)) {
                $this->addError("unFollow", Ibos::lang("Operation succeed", "message"));
                $this->_updateFollowCount($uid, $fid, false);
                $followState["following"] = 0;
                return $followState;
            } else {
                $this->addError("unFollow", Ibos::lang("Operation failure", "message"));
                return false;
            }
        } else {
            $this->addError("unFollow", Ibos::lang("Operation failure", "message"));
            return false;
        }
    }

    public function getFollowState($uid, $fid)
    {
        $followState = $this->getFollowStateByFids($uid, $fid);
        return $followState[$fid];
    }

    public function getFollowStateByFids($uid, $fids)
    {
        is_array($fids) && array_map("intval", $fids);
        $_fids = (is_array($fids) ? implode(",", $fids) : $fids);

        if (empty($_fids)) {
            return array();
        }

        $followData = $this->fetchAll(" ( uid = '$uid' AND fid IN($_fids) ) OR ( uid IN($_fids) AND fid = '$uid')");
        $followStates = $this->_formatFollowState($uid, $fids, $followData);
        return $followStates[$uid];
    }

    public function getFollowingList($uid, $offset = 0, $limit = 10)
    {
        $list = $this->fetchAll(array("condition" => "`uid`=$uid", "order" => "`followid` DESC", "offset" => $offset, "limit" => $limit));
        return $list;
    }

    public function getFollowingListAll($uid)
    {
        $list = $this->fetchAll(array("condition" => "`uid`=$uid", "order" => "`followid` DESC"));
        return $list;
    }

    public function getFollowerList($uid, $offset = 0, $limit = 10)
    {
        $criteria = array("condition" => "`fid`=$uid", "order" => "`followid` DESC", "offset" => $offset, "limit" => $limit);
        $list = $this->fetchAll($criteria);

        foreach ($list as $key => $value) {
            $uid = $value["uid"];
            $fid = $value["fid"];
            $list[$key]["uid"] = $fid;
            $list[$key]["fid"] = $uid;
        }

        return $list;
    }

    public function getFollowCount($uids)
    {
        $count = array();

        foreach ($uids as $uid) {
            $count[$uid] = array("following" => 0, "follower" => 0);
        }

        $followingMap = array("in", "uid", $uids);
        $followerMap = array("in", "fid", $uids);
        $following = $this->getDbConnection()->createCommand()->select("COUNT(1) AS `count`,`uid`")->from($this->tableName())->where(array("and", $followingMap))->group("uid")->queryAll();

        foreach ($following as $v) {
            $count[$v["uid"]]["following"] = $v["count"];
        }

        $follower = $this->getDbConnection()->createCommand()->select("COUNT(1) AS `count`,`fid`")->from($this->tableName())->where(array("and", $followerMap))->group("fid")->queryAll();

        foreach ($follower as $v) {
            $count[$v["fid"]]["follower"] = $v["count"];
        }

        return $count;
    }

    public function getBothFollow($uid, $secondUid)
    {
        $con = "uid = %d";
        $firstfids = $this->fetchAll(array("select" => "fid", "condition" => sprintf($con, $uid)));
        $secondfids = $this->fetchAll(array("select" => "fid", "condition" => sprintf($con, $secondUid)));
        $first = ConvertUtil::getSubByKey($firstfids, "fid");
        $second = ConvertUtil::getSubByKey($secondfids, "fid");
        $bothfollowUid = array_intersect($first, $second);
        return $bothfollowUid;
    }

    public function getSecondFollow($uid, $secondUid)
    {
        $followList = $this->getFollowingListAll($uid);
        $fids = ConvertUtil::getSubByKey($followList, "fid");
        $criteria = array("select" => "uid", "condition" => sprintf("FIND_IN_SET(uid,'%s') AND fid = %d", implode(",", $fids), $secondUid));
        $result = $this->fetchAll($criteria);
        return ConvertUtil::getSubByKey($result, "uid");
    }

    private function _updateFollowCount($uid, $fids, $inc = true)
    {
        !is_array($fids) && ($fids = explode(",", $fids));
        UserData::model()->updateKey("following_count", count($fids), $inc, $uid);

        foreach ($fids as $fid) {
            UserData::model()->updateKey("follower_count", 1, $inc, $fid);
            UserData::model()->updateKey("new_folower_count", 1, $inc, $fid);
        }
    }

    private function _formatFollowState($uid, $fids, $followData)
    {
        $followStates = array();
        !is_array($fids) && ($fids = explode(",", $fids));

        foreach ($fids as $fid) {
            $followStates[$uid][$fid] = array("following" => 0, "follower" => 0);
        }

        foreach ($followData as $v) {
            if ($v["uid"] == $uid) {
                $followStates[$v["uid"]][$v["fid"]]["following"] = 1;
            } elseif ($v["fid"] == $uid) {
                $followStates[$v["fid"]][$v["uid"]]["follower"] = 1;
            }
        }

        return $followStates;
    }
}
