<?php

class Feed extends ICModel
{
    public static function model($className = "Feed")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{feed}}";
    }

    public function getRecentFeeds($num = 4)
    {
        $criteria = array("select" => "feedid", "condition" => "`module` = 'weibo'", "order" => "ctime DESC", "offset" => 0, "limit" => $num, "group" => "uid");
        $feedIds = ConvertUtil::getSubByKey($this->fetchAll($criteria), "feedid");
        return $this->getFeeds($feedIds);
    }

    public function getList($map, $limit = 10, $offset = 0, $order = null)
    {
        $order = (!empty($order) ? $order : "feedid DESC");
        $criteria = array("select" => "feedid", "condition" => $map, "order" => $order, "offset" => $offset, "limit" => $limit);
        $feedIds = ConvertUtil::getSubByKey($this->fetchAll($criteria), "feedid");
        return $this->getFeeds($feedIds);
    }

    public function getFollowingFeed($where = "", $limit = 10, $offset = 0, $uid = "")
    {
        $buid = intval(empty($uid) ? Ibos::app()->user->uid : $uid);
        $_where = (!empty($where) ? "(a.uid = '$buid' OR b.uid = '$buid') AND ($where)" : "(a.uid = '$buid' OR b.uid = '$buid')");
        $feedlist = $this->getDbConnection()->createCommand()->select("a.feedid")->from("{{feed}} AS a")->leftJoin("{{user_follow}} AS b", "a.uid = b.fid AND b.uid = " . $buid)->where($_where)->order("a.feedid DESC")->limit($limit)->offset($offset)->queryAll();
        $feedids = ConvertUtil::getSubByKey($feedlist, "feedid");
        return $this->getFeeds($feedids);
    }

    public function countFollowingFeed($where = "", $uid = "")
    {
        $buid = intval(empty($uid) ? Ibos::app()->user->uid : $uid);
        $_where = (!empty($where) ? "(a.uid = '$buid' OR b.uid = '$buid') AND ($where)" : "(a.uid = '$buid' OR b.uid = '$buid')");
        $count = $this->getDbConnection()->createCommand()->select("count(a.feedid)")->from("{{feed}} AS a")->leftJoin("{{user_follow}} AS b", "a.uid = b.fid AND b.uid = " . $buid)->where($_where)->queryScalar();
        return $count;
    }

    public function get($feedId)
    {
        $feedList = $this->getFeeds(array($feedId));

        if (!$feedList) {
            $this->addError("get", Ibos::lang("Get info fail", "message.default"));
            return false;
        } else {
            return $feedList[0];
        }
    }

    public function getFeeds($feedIds)
    {
        !is_array($feedIds) && ($feedIds = explode(",", $feedIds));
        $feedList = array();
        $feedIds = array_filter(array_unique($feedIds));

        if (0 < count($feedIds)) {
            $cacheList = CacheUtil::get($feedIds, "feed_");
        } else {
            return false;
        }

        foreach ($feedIds as $key => $v) {
            if ($cacheList[$v]) {
                $feedList[$key] = $cacheList[$v];
            } else {
                $feed = $this->setFeedCache(array(), $v);
                $feedList[$key] = $feed[$v];
            }
        }

        return $feedList;
    }

    public function put($uid, $module = "weibo", $type = "", $data = array(), $rowid = 0, $table = "feed", $extUid = null, $lessUids = null, $isAtMe = true, $isRepost = 0)
    {
        if (!$uid || ($type == "")) {
            $this->addError("putFeed", Ibos::lang("Operation failure", "message"));
            return false;
        }

        if (!in_array($type, array("post", "repost", "postimage"))) {
            $type = "post";
        }

        if (!ModuleUtil::getIsEnabled($module)) {
            $module = "weibo";
            $type = "post";
            $table = "feed";
        }

        $table = strtolower($table);
        $data["uid"] = $uid;
        $data["module"] = $module;
        $data["type"] = $type;
        $data["rowid"] = $rowid;
        $data["table"] = $table;
        $data["ctime"] = time();
        $data["from"] = (isset($data["from"]) ? intval($data["from"]) : EnvUtil::getVisitorClient());
        $data["isdel"] = $data["commentcount"] = $data["diggcount"] = $data["repostcount"] = 0;
        $data["isrepost"] = $isRepost;
        $content = $this->formatFeedContent($data["body"]);
        $data["body"] = $content["body"];
        $data["content"] = $content["content"];
        $data["body"] .= (isset($data["source_url"]) ? $data["source_url"] : "");
        $data["content"] .= (isset($data["source_url"]) ? $data["source_url"] : "");
        $feedId = $this->add($data, true);

        if (!$feedId) {
            return false;
        }

        $data["content"] = str_replace(chr(31), "", $data["content"]);
        $data["body"] = str_replace(chr(31), "", $data["body"]);
        $feedData = array("feedid" => $feedId, "feeddata" => serialize($data), "clientip" => EnvUtil::getClientIp(), "feedcontent" => $data["body"]);
        $feedDataId = FeedData::model()->add($feedData, true);
        if ($feedId && $feedDataId) {
            if ($data["isrepost"] == 1) {
                if ($isAtMe) {
                    $content = $data["content"];
                } else {
                    $content = $data["body"];
                }

                $extUid[] = (isset($data["sourceInfo"]["transpond_data"]) ? $data["sourceInfo"]["transpond_data"]["uid"] : null);
                if ($isAtMe && !empty($data["curid"])) {
                    $appRowData = $this->get($data["curid"]);
                    $extUid[] = $appRowData["uid"];
                }
            } else {
                $content = $data["content"];
                Atme::model()->updateRecentAt($content);
            }

            Atme::model()->addAtme("weibo", "feed", $content, $feedId, $extUid, $lessUids);
            $data["clientip"] = EnvUtil::getClientIp();
            $data["feedid"] = $feedId;
            $data["feeddata"] = serialize($data);
            $return = $this->setFeedCache($data);
            $return["user_info"] = User::model()->fetchByUid($uid);
            $return["feedid"] = $feedId;
            $return["rowid"] = $data["rowid"];

            if ($module == "weibo") {
                UserData::model()->updateKey("feed_count", 1);
                UserData::model()->updateKey("weibo_count", 1);
            }

            return $return;
        } else {
            $this->addError("putFeed", Ibos::lang("Operation failure", "message"));
            return false;
        }
    }

    public function formatFeedContent($content, $weiboNums = 0)
    {
        $content = str_replace(Ibos::app()->setting->get("siteurl"), "[SITE_URL]", StringUtil::pregHtml($content));
        $content = preg_replace_callback("/((?:https?|mailto|ftp):\/\/([^\\x{2e80}-\\x{9fff}\s<'\\\"“”‘’，。}]*)?)/u", "StringUtil::formatFeedContentUrlLength", $content);

        if (isset($GLOBALS["replaceHash"])) {
            $replaceHash = $GLOBALS["replaceHash"];
            unset($GLOBALS["replaceHash"]);
        } else {
            $replaceHash = array();
        }

        $scream = explode("//", $content);
        $feedNums = 0;

        if (empty($weiboNums)) {
            $feedNums = intval(Ibos::app()->setting->get("setting/wbnums"));
        } else {
            $feedNums = $weiboNums;
        }

        $body = array();
        $patterns = array_keys($replaceHash);
        $replacements = array_values($replaceHash);

        foreach ($scream as $value) {
            $tbody[] = $value;
            $bodyStr = implode("//", $tbody);

            if ($feedNums < StringUtil::getStrLength(ltrim($bodyStr))) {
                break;
            }

            $body[] = str_replace($patterns, $replacements, $value);
            unset($bodyStr);
        }

        $data["body"] = implode("//", $body);
        $scream[0] = str_replace($patterns, $replacements, $scream[0]);
        $data["content"] = trim($scream[0]);
        return $data;
    }

    public function doEditFeed($feedid, $type, $uid = null)
    {
        $return = array("isSuccess" => false);

        if (empty($feedid)) {
        } else {
            $feedid = (is_array($feedid) ? implode(",", $feedid) : intval($feedid));
            $con = sprintf("feedid = %d", $feedid);
            $isdel = ($type == "delFeed" ? 1 : 0);

            if ($type == "deleteFeed") {
                $msg = array("user" => Ibos::app()->user->username, "ip" => EnvUtil::getClientIp(), "id" => $feedid, "value" => $this->get($feedid));
                Log::write($msg, "db", "module.weibo.deleteFeed");
                $res = $this->deleteAll($con);
                $res && $this->_deleteFeedAttach($feedid);
            } else {
                $ids = explode(",", $feedid);
                $feedList = $this->getFeeds($ids);
                $res = $this->updateAll(array("isdel" => $isdel), $con);

                if ($type == "feedRecover") {
                    foreach ($feedList as $v) {
                        UserData::model()->updateKey("feed_count", 1, true, $v["user_info"]["uid"]);
                        UserData::model()->updateKey("weibo_count", 1, true, $v["user_info"]["uid"]);
                    }
                } else {
                    foreach ($feedList as $v) {
                        UserData::model()->updateKey("feed_count", -1, false, $v["user_info"]["uid"]);
                        UserData::model()->updateKey("weibo_count", -1, false, $v["user_info"]["uid"]);
                    }
                }

                $this->cleanCache($ids);
                $query = $this->fetchAll(array("select" => "feedid", "condition" => sprintf("rowid = %d", $feedid)));
                $sids = ConvertUtil::getSubByKey($query, "feedid");
                $sids && $this->cleanCache($sids);
            }

            $commentQuery = $this->getDbConnection()->createCommand()->select("cid")->from("{{comment}}")->where(sprintf("`module` = 'weibo' AND `table` = 'feed' AND `rowid` = %d", $feedid))->queryAll();
            $commentIds = ConvertUtil::getSubByKey($commentQuery, "cid");
            $commentIds && Comment::model()->deleteComment($commentIds, null, "weibo");
            FeedTopic::model()->deleteWeiboJoinTopic($feedid);
            Atme::model()->deleteAtme("feed", null, $feedid);
            $topics = FeedTopicLink::model()->fetchAll(array("select" => "topicid", "condition" => "feedid=" . $feedid));
            $topicId = ConvertUtil::getSubByKey($topics, "topicid");
            $topicId && FeedTopic::model()->updateCounters(array("count" => -1), sprintf("FIND_IN_SET(topicid,'%s')", implode(",", $topicId)));
            FeedTopicLink::model()->deleteAll("feedid=" . $feedid);

            if ($res) {
                $return = array("isSuccess" => true);
                $uid && UserUtil::updateCreditByAction("deleteweibo", $uid);
            }
        }

        return $return;
    }

    public function getFeedInfo($id, $forApi = false)
    {
        $data = CacheUtil::get("feed_info_" . $id);
        if (($data !== false) && ($forApi === false)) {
            return $data;
        }

        $data = Ibos::app()->db->createCommand()->from("{{feed}} a")->leftJoin("{{feed_data}} b", "a.feedid = b.feedid")->where("a.feedid = " . $id)->queryRow();
        $fd = unserialize($data["feeddata"]);
        $userInfo = User::model()->fetchByUid($data["uid"]);
        $data["ctime"] = ConvertUtil::formatDate($data["ctime"], "n月d日H:i");
        $data["content"] = ($forApi ? StringUtil::parseForApi($fd["body"]) : $fd["body"]);
        $data["realname"] = $userInfo["realname"];
        $data["avatar_big"] = $userInfo["avatar_big"];
        $data["avatar_middle"] = $userInfo["avatar_middle"];
        $data["avatar_small"] = $userInfo["avatar_small"];
        unset($data["feeddata"]);

        if ($data["type"] == "repost") {
            $data["transpond_id"] = $data["rowid"];
            $data["transpond_data"] = $this->getFeedInfo($data["transpond_id"], $forApi);
        }

        if (!empty($fd["attach_id"])) {
            $data["has_attach"] = 1;
            $attach = AttachUtil::getAttachData($fd["attach_id"]);
            $attachUrl = FileUtil::getAttachUrl();

            foreach ($attach as $ak => $av) {
                $_attach = array("attach_id" => $av["aid"], "attach_name" => $av["filename"], "attach_url" => FileUtil::fileName($attachUrl . "/" . $av["attachment"]), "extension" => StringUtil::getFileExt($av["filename"]), "size" => $av["filesize"]);

                if ($data["type"] == "postimage") {
                    $_attach["attach_small"] = WbCommonUtil::getThumbImageUrl($av, WbConst::ALBUM_DISPLAY_WIDTH, WbConst::ALBUM_DISPLAY_HEIGHT);
                    $_attach["attach_middle"] = WbCommonUtil::getThumbImageUrl($av, WbConst::WEIBO_DISPLAY_WIDTH, WbConst::WEIBO_DISPLAY_HEIGHT);
                }

                $data["attach"][] = $_attach;
            }
        } else {
            $data["has_attach"] = 0;
        }

        $data["feedType"] = $data["type"];
        $feedInfo = $this->get($id);
        $data["source_body"] = $feedInfo["body"];
        $data["api_source"] = $feedInfo["api_source"];
        CacheUtil::set("feed_info_" . $id, $data, 60);

        if ($forApi) {
            $data["content"] = StringUtil::realStripTags($data["content"]);
            unset($data["isdel"]);
            unset($data["fromdata"]);
            unset($data["table"]);
            unset($data["rowid"]);
            unset($data["source_body"]);
        }

        return $data;
    }

    public function cleanCache($feedIds = array(), $uid = "")
    {
        if (!empty($uid)) {
            CacheUtil::rm("feed_foli_" . $uid);
            CacheUtil::rm("feed_uli_" . $uid);
        }

        if (empty($feedIds)) {
            return true;
        }

        if (is_array($feedIds)) {
            foreach ($feedIds as $v) {
                CacheUtil::rm("feed_" . $v);
                CacheUtil::rm("feed_info_" . $v);
            }
        } else {
            CacheUtil::rm("feed_" . $feedIds);
            CacheUtil::rm("feed_info_" . $feedIds);
        }
    }

    public function countSearchFeeds($key, $feedType = null, $sTime = 0, $eTime = 0)
    {
        $map = $this->mergeSearchCondition($key, $feedType, $sTime, $eTime);
        $count = $this->getDbConnection()->createCommand()->select("count(a.feedid)")->from(sprintf("%s a", $this->tableName()))->leftJoin("{{feed_data}} b", "a.feedid = b.feedid")->where($map)->queryScalar();
        return intval($count);
    }

    private function mergeSearchFollowingCondition($key, $loadId)
    {
        $me = intval(Ibos::app()->user->uid);
        $where = (!empty($loadId) ? " a.isdel = 0 AND a.feedid <'$loadId'" : "a.isdel = 0");
        $where .= " AND (a.uid = '$me' OR b.uid = '$me' ) AND " . WbfeedUtil::getViewCondition($me, "a.");
        $where .= " AND c.feedcontent LIKE '%" . StringUtil::filterCleanHtml($key) . "%'";
        return $where;
    }

    public function countSearchFollowing($key, $loadId)
    {
        $me = intval(Ibos::app()->user->uid);
        $where = $this->mergeSearchFollowingCondition($key, $loadId);
        $count = $this->getDbConnection()->createCommand()->select("count(a.feedid)")->from(sprintf("%s a", $this->tableName()))->leftJoin("{{user_follow}} b", "a.uid = b.fid AND b.uid = $me")->leftJoin("{{feed_data}} c", "a.feedid = c.feedid")->where($where)->queryScalar();
        return $count;
    }

    private function mergeSearchAllCondition($key, $loadId, $feedtype = "", $uid = 0)
    {
        $me = intval(Ibos::app()->user->uid);
        $map = array("and");

        if (!$uid) {
            $map[] = "a.isdel = 0 AND " . WbfeedUtil::getViewCondition($me);
        } else {
            $map[] = "a.isdel = 0 AND uid = " . $uid . ($me == $uid ? "" : " AND " . WbfeedUtil::getViewCondition($me));
        }

        !empty($loadId) && ($map[] = "a.feedid < " . intval($loadId));
        $map[] = array("LIKE", "b.feedcontent", "%" . StringUtil::filterCleanHtml($key) . "%");

        if ($feedtype) {
            if ($feedtype == "post") {
                $map[] = "a.isrepost = 0";
            }

            $map[] = "a.type = " . $feedtype;
        }

        return $map;
    }

    public function countSearchAll($key, $loadId, $feedtype = "", $uid = 0)
    {
        $map = $this->mergeSearchAllCondition($key, $loadId, $feedtype, $uid);
        $count = $this->getDbConnection()->createCommand()->select("count(a.feedid)")->from(sprintf("%s a", $this->tableName()))->leftJoin("{{feed_data}} b", "a.feedid = b.feedid")->where($map)->queryScalar();
        return $count;
    }

    private function mergeSearchMovementCondition($key, $loadId, $feedtype = "", $uid = 0)
    {
        $me = intval(Ibos::app()->user->uid);
        $map = array("and");

        if (!$uid) {
            $map[] = "a.isdel = 0 AND " . WbfeedUtil::getViewCondition($me);
        } else {
            $map[] = "a.isdel = 0 AND uid = " . $uid . ($me == $uid ? "" : " AND " . WbfeedUtil::getViewCondition($me));
        }

        !empty($loadId) && ($map[] = "a.feedid < " . intval($loadId));
        $map[] = array("LIKE", "b.feedcontent", "%" . StringUtil::filterCleanHtml($key) . "%");

        if ($feedtype) {
            $map[] = "a.module = " . $feedtype;
        } else {
            $map[] = "a.module != 'weibo'";
        }

        return $map;
    }

    public function countSearchMovement($key, $loadId, $feedtype = "", $uid = 0)
    {
        $map = $this->mergeSearchMovementCondition($key, $loadId, $feedtype, $uid);
        $count = $this->getDbConnection()->createCommand()->select("count(a.feedid)")->from(sprintf("%s a", $this->tableName()))->where($map)->leftJoin("{{feed_data}} b", "a.feedid = b.feedid")->queryScalar();
        return $count;
    }

    public function searchFeed($key, $type, $loadId, $limit, $offset, $feedtype = "", $uid = 0)
    {
        $me = intval(Ibos::app()->user->uid);

        switch ($type) {
            case "following":
                $buid = $me;
                $where = $this->mergeSearchFollowingCondition($key, $loadId, $buid);
                $feedlist = $this->getDbConnection()->createCommand()->select("a.feedid")->from(sprintf("%s a", $this->tableName()))->leftJoin("{{user_follow}} b", "a.uid = b.fid AND b.uid = $buid")->leftJoin("{{feed_data}} c", "a.feedid = c.feedid")->where($where)->order("a.ctime DESC")->offset($offset)->limit($limit)->queryAll();
                break;

            case "all":
                $map = $this->mergeSearchAllCondition($key, $loadId, $feedtype, $uid);
                $feedlist = $this->getDbConnection()->createCommand()->select("a.feedid")->from(sprintf("%s a", $this->tableName()))->where($map)->leftJoin("{{feed_data}} b", "a.feedid = b.feedid")->order("a.ctime DESC")->offset($offset)->limit($limit)->queryAll();
                break;

            case "movement":
                $map = $this->mergeSearchMovementCondition($key, $loadId, $feedtype, $uid);
                $feedlist = $this->getDbConnection()->createCommand()->select("a.feedid")->from(sprintf("%s a", $this->tableName()))->where($map)->leftJoin("{{feed_data}} b", "a.feedid = b.feedid")->order("a.ctime DESC")->offset($offset)->limit($limit)->queryAll();
                break;
        }

        $feedids = ConvertUtil::getSubByKey($feedlist, "feedid");
        $feedlist = $this->getFeeds($feedids);
        return $feedlist;
    }

    public function searchFeeds($key, $feedType = null, $limit = 10, $offset = 0, $sTime = 0, $eTime = 0)
    {
        $map = $this->mergeSearchCondition($key, $feedType, $sTime, $eTime);
        $list = $this->getDbConnection()->createCommand()->select("a.feedid")->from(sprintf("%s a", $this->tableName()))->leftJoin("{{feed_data}} b", "a.feedid = b.feedid")->where($map)->order("a.ctime DESC")->limit($limit)->offset($offset)->queryAll();
        $feedids = ConvertUtil::getSubByKey($list, "feedid");
        $feedlist = $this->getFeeds($feedids);
        return $feedlist;
    }

    private function mergeSearchCondition($key, $feedType = null, $sTime = 0, $eTime = 0)
    {
        $map[] = "and";
        $map[] = "a.isdel = 0";
        $map[] = array("like", "b.feedcontent", "%" . StringUtil::filterCleanHtml($key) . "%");

        if ($feedType) {
            $map[] = "a.type = " . $feedType;

            if ($feedType == "post") {
                $map[] = "a.isrepost = 0";
            }
        }

        if ($sTime && $eTime) {
            $map[] = sprintf("'a.ctime' BETWEEN %d AND %d", $sTime, $eTime);
        }

        return $map;
    }

    private function setFeedCache($value = array(), $feedId = array())
    {
        if (!empty($feedId)) {
            !is_array($feedId) && ($feedId = explode(",", $feedId));
            $feedId = implode(",", $feedId);
            $list = Ibos::app()->db->createCommand()->select("a.*,b.clientip,b.feeddata")->from("{{feed}} a")->leftJoin("{{feed_data}} b", "a.feedid = b.feedid")->where("a.feedid IN ($feedId)")->queryAll();
            $r = array();

            foreach ($list as &$v) {
                $parseData = $this->parseTemplate($v);
                $v["info"] = $parseData["info"];
                $v["title"] = $parseData["title"];
                $v["content"] = $parseData["content"];

                if (isset($parseData["attach_id"])) {
                    $v["attach_id"] = $parseData["attach_id"];
                }

                $v["body"] = $parseData["body"];
                $v["api_source"] = $parseData["api_source"];
                $v["actions"] = $parseData["actions"];
                $v["user_info"] = $parseData["userInfo"];
                CacheUtil::set("feed_" . $v["feedid"], $v);
                $r[$v["feedid"]] = $v;
            }

            return $r;
        } else {
            $parseData = $this->parseTemplate($value);
            $value["info"] = $parseData["info"];
            $value["title"] = $parseData["title"];
            $value["content"] = $parseData["content"];

            if (isset($parseData["attach_id"])) {
                $v["attach_id"] = $parseData["attach_id"];
            }

            $value["body"] = $parseData["body"];
            $value["api_source"] = $parseData["api_source"];
            $value["actions"] = $parseData["actions"];
            $value["user_info"] = $parseData["userInfo"];
            CacheUtil::set("feed_" . $value["feedid"], $value);
            return $value;
        }
    }

    private function parseTemplate($_data)
    {
        $user = User::model()->fetchByUid($_data["uid"]);
        $_data["data"] = unserialize($_data["feeddata"]);
        $var = $_data["data"];

        if (!empty($var["attach_id"])) {
            $var["attachInfo"] = AttachUtil::getAttach($var["attach_id"]);
            $attachUrl = FileUtil::getAttachUrl();

            foreach ($var["attachInfo"] as $ak => $av) {
                $_attach = array("attach_id" => $av["aid"], "attach_name" => $av["filename"], "attach_url" => FileUtil::fileName($attachUrl . "/" . $av["attachment"]), "extension" => StringUtil::getFileExt($av["filename"]), "size" => $av["filesize"]);

                if ($_data["type"] == "postimage") {
                    $_attach["attach_small"] = WbCommonUtil::getThumbImageUrl($av, WbConst::ALBUM_DISPLAY_WIDTH, WbConst::ALBUM_DISPLAY_HEIGHT);
                    $_attach["attach_middle"] = WbCommonUtil::getThumbImageUrl($av, WbConst::WEIBO_DISPLAY_WIDTH, WbConst::WEIBO_DISPLAY_HEIGHT);
                }

                $var["attachInfo"][$ak] = $_attach;
            }
        }

        $var["uid"] = $_data["uid"];
        $var["actor"] = "<a href='{$user["space_url"]}' data-toggle='usercard' data-param=\"uid={$user["uid"]}\">{$user["realname"]}</a>";
        $var["actor_uid"] = $user["uid"];
        $var["actor_uname"] = $user["realname"];
        $var["feedid"] = $_data["feedid"];

        if (!empty($_data["rowid"])) {
            empty($_data["table"]) && ($_data["table"] = "feed");
            $var["sourceInfo"] = Source::getSourceInfo($_data["table"], $_data["rowid"], false, $_data["module"]);
        } else {
            $var["sourceInfo"] = null;
        }

        $feedTemplateAlias = "application.modules.message.config.feed.{$_data["type"]}Feed";
        $file = Ibos::getPathOfAlias($feedTemplateAlias);

        if (!file_exists($file . ".php")) {
            $feedTemplateAlias = "application.modules.message.config.feed.postFeed";
        }

        $feedXmlContent = Ibos::app()->getController()->renderPartial($feedTemplateAlias, $var, true);
        $s = simplexml_load_string($feedXmlContent);

        if (!$s) {
            return false;
        }

        $result = $s->xpath("//feed[@type='" . StringUtil::filterCleanHtml($_data["type"]) . "']");
        $actions = (array) $result[0]->feedAttr;
        $return["content"] = $var["content"];

        if (isset($var["attach_id"])) {
            $return["attach_id"] = $var["attach_id"];
        }

        $return["userInfo"] = $user;
        $return["title"] = trim((string) $result[0]->title);
        $return["body"] = trim((string) $result[0]->body);
        $return["info"] = trim((string) $result[0]["info"]);
        $return["body"] = StringUtil::parseHtml($return["body"]);
        $return["api_source"] = $var["sourceInfo"];
        $return["actions"] = $actions["@attributes"];

        if (!$this->notDel($_data["module"], $_data["type"], $_data["rowid"])) {
            $return["body"] = Ibos::lang("Info already delete", "message.default");
        }

        return $return;
    }

    public function shareFeed($data, $from = "share", $lessUids = null)
    {
        $return = array("isSuccess" => false, "data" => "转发失败");

        if (empty($data["sid"])) {
            return $return;
        }

        $type = StringUtil::filterCleanHtml($data["type"]);
        $table = (isset($data["table"]) ? $data["table"] : $type);
        $module = (isset($data["module"]) ? $data["module"] : "weibo");
        $forApi = (isset($data["forApi"]) && $data["forApi"] ? true : false);

        if (!$oldInfo = Source::getSourceInfo($table, $data["sid"], $forApi, $data["module"])) {
            $return["data"] = "此信息不可以被转发";
            return $return;
        }

        $d["content"] = (isset($data["content"]) ? str_replace(Ibos::app()->setting->get("siteurl"), "[SITE_URL]", $data["content"]) : "");
        $d["body"] = str_replace(Ibos::app()->setting->get("siteurl"), "[SITE_URL]", $data["body"]);
        $feedType = "repost";
        if (!empty($oldInfo["feedType"]) && !in_array($oldInfo["feedType"], array("post", "postimage"))) {
            $feedType = $oldInfo["feedType"];
        }

        $d["sourceInfo"] = (!empty($oldInfo["sourceInfo"]) ? $oldInfo["sourceInfo"] : $oldInfo);
        $isOther = ($from == "comment" ? false : true);
        $d["curid"] = $data["curid"];

        if ($oldInfo["rowid"] == 0) {
            $id = $oldInfo["source_id"];
            $table = $oldInfo["source_table"];
        } else {
            $id = $oldInfo["rowid"];
            $table = $oldInfo["table"];
        }

        $d["from"] = (isset($data["from"]) ? intval($data["from"]) : 0);
        $res = $this->put(Ibos::app()->user->uid, $module, $feedType, $d, $id, $table, null, $lessUids, $isOther, 1);

        if ($res) {
            if (isset($data["comment"])) {
                $c["module"] = $module;
                $c["moduleuid"] = $data["curid"];
                $c["table"] = "feed";
                $c["uid"] = $oldInfo["uid"];
                $c["content"] = (!empty($d["body"]) ? $d["body"] : $d["content"]);
                $c["rowid"] = (!empty($oldInfo["sourceInfo"]) ? $oldInfo["sourceInfo"]["source_id"] : $id);
                $c["from"] = EnvUtil::getVisitorClient();
                $notCount = ($from == "share" ? ($data["comment"] == 1 ? false : true) : false);
                Comment::model()->addComment($c, false, $notCount, $lessUids);
            }

            FeedTopic::model()->addTopic(html_entity_decode($d["body"], ENT_QUOTES), $res["feedid"], $feedType);
            $rdata = $res;
            $rdata["feedid"] = $res["feedid"];
            $rdata["rowid"] = $data["sid"];
            $rdata["table"] = $data["type"];
            $rdata["module"] = $module;
            $rdata["isrepost"] = 1;

            switch ($module) {
                case "mobile":
                    break;

                default:
                    $rdata["from"] = EnvUtil::getFromClient($from, $module);
                    break;
            }

            $return["data"] = $rdata;
            $return["isSuccess"] = true;
            if (($module == "weibo") && ($type == "feed")) {
                $this->updateCounters(array("repostcount" => 1), "feedid = " . $data["sid"]);
                $this->cleanCache($data["sid"]);
                if (($data["curid"] != $data["sid"]) && !empty($data["curid"])) {
                    $this->updateCounters(array("repostcount" => 1), "feedid = " . $data["curid"]);
                    $this->cleanCache($data["curid"]);
                }
            }
        } else {
            $return["data"] = $this->getError("putFeed");
        }

        return $return;
    }

    private function notDel($module, $feedType, $rowid)
    {
        if (empty($rowid)) {
            return true;
        }

        return true;
    }

    private function _deleteFeedAttach($feedIds)
    {
        $feeddata = $this->getFeeds($feedIds);
        $feedDataInfo = ConvertUtil::getSubByKey($feeddata, "feeddata");
        $attachIds = array();

        foreach ($feedDataInfo as $value) {
            $value = unserialize($value);
            !empty($value["attach_id"]) && ($attachIds = array_merge($attachIds, $value["attach_id"]));
        }

        array_filter($attachIds);
        array_unique($attachIds);
        !empty($attachIds) && AttachUtil::delAttach($attachIds);
    }
}
