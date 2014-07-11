<?php

class MobileWeiboController extends MobileBaseController
{
    public function actionIndex()
    {
        $var["type"] = (isset($_GET["type"]) ? StringUtil::filterCleanHtml($_GET["type"]) : "all");
        $var["feedtype"] = (isset($_GET["feedtype"]) ? StringUtil::filterCleanHtml($_GET["feedtype"]) : "all");
        $var["feedkey"] = (isset($_GET["feedkey"]) ? StringUtil::filterCleanHtml(urldecode($_GET["feedkey"])) : "");
        $var["loadNew"] = (isset($_GET["page"]) ? 0 : 1);
        $var["loadMore"] = (isset($_GET["page"]) ? 0 : 1);
        $var["loadId"] = (isset($_GET["loadid"]) ? $_GET["loadid"] : 0);
        $var["nums"] = (isset($_GET["page"]) ? WbConst::DEF_LIST_FEED_NUMS : 10);
        $data = $this->getData($var);
        $var["loadId"] = (isset($data["lastId"]) ? $data["lastId"] : 0);
        $this->ajaxReturn(array_merge($var, $data), "JSONP");
    }

    public function actionAdd()
    {
        $return = array("isSuccess" => true, "data" => "");
        $d["content"] = (isset($_GET["content"]) ? StringUtil::filterDangerTag($_GET["content"]) : "");
        $d["body"] = EnvUtil::getRequest("body");
        $d["rowid"] = (isset($_GET["rowid"]) ? intval($_GET["rowid"]) : 0);
        $d["from"] = EnvUtil::getRequest("from");

        foreach ($_GET as $key => $val) {
            $_GET[$key] = StringUtil::filterCleanHtml($_GET[$key]);
        }

        if (isset($_GET["view"])) {
        }

        $d["source_url"] = (isset($_GET["source_url"]) ? urldecode($_GET["source_url"]) : "");
        $d["body"] = preg_replace("/#[\s]*([^#^\s][^#]*[^#^\s])[\s]*#/is", "#" . trim("\${1}") . "#", $d["body"]);

        if (isset($_GET["attachid"])) {
            $d["attach_id"] = trim(StringUtil::filterCleanHtml($_GET["attachid"]));

            if (!empty($d["attach_id"])) {
                $d["attach_id"] = explode(",", $d["attach_id"]);
                array_map("intval", $d["attach_id"]);
            }
        }

        $type = StringUtil::filterCleanHtml(EnvUtil::getRequest("type"));
        $table = (isset($_GET["table"]) ? StringUtil::filterCleanHtml($_GET["table"]) : "feed");
        $module = (isset($_GET["module"]) ? StringUtil::filterCleanHtml($_GET["module"]) : "weibo");
        $data = Feed::model()->put(Ibos::app()->user->uid, $module, $type, $d, $d["rowid"], $table);

        if (!$data) {
            $return["isSuccess"] = false;
            $return["data"] = Feed::model()->getError("putFeed");
            $this->ajaxReturn($return);
        }

        UserUtil::updateCreditByAction("addweibo", Ibos::app()->user->uid);
        $data["from"] = EnvUtil::getFromClient($data["from"], $data["module"]);
        $return["data"] = $data;
        $return["feedid"] = $data["feedid"];
        $this->ajaxReturn($return, "JSONP");
    }

    public function actionShare()
    {
        if (empty($_GET["curid"])) {
            $map["feedid"] = EnvUtil::getRequest("sid");
        } else {
            $map["feedid"] = EnvUtil::getRequest("curid");
        }

        $map["isdel"] = 0;
        $isExist = Feed::model()->countByAttributes($map);

        if ($isExist == 0) {
            $return["isSuccess"] = false;
            $return["data"] = "内容已被删除，转发失败";
            $this->ajaxReturn($return);
        }

        $return = Feed::model()->shareFeed($_GET, "share");

        if ($return["isSuccess"]) {
            $module = $_GET["module"];

            if ($module == "weibo") {
                UserUtil::updateCreditByAction("forwardweibo", Ibos::app()->user->uid);
                $suid = Ibos::app()->db->createCommand()->select("uid")->from("{{feed}}")->where(sprintf("feedid = %d AND isdel = 0", $map["feedid"]))->queryScalar();
                $suid && UserUtil::updateCreditByAction("forwardedweibo", $suid);
            }
        }

        $this->ajaxReturn($return, "JSONP");
    }

    public function actionFeed()
    {
        $feedid = intval(EnvUtil::getRequest("feedid"));
        $feedInfo = Feed::model()->get($feedid);

        if (!$feedInfo) {
            $this->error(Ibos::lang("Weibo not exists"));
        }

        if ($feedInfo["isdel"] == "1") {
            $this->error(Ibos::lang("No relate weibo"));
            exit();
        }

        if ($feedInfo["from"] == "1") {
            $feedInfo["from"] = EnvUtil::getFromClient(6, $feedInfo["module"], "3G版");
        } else {
            switch ($feedInfo["module"]) {
                case "mobile":
                    break;

                default:
                    $feedInfo["from"] = EnvUtil::getFromClient($feedInfo["from"], $feedInfo["module"]);
                    break;
            }
        }

        if (isset($v["attach_id"][0])) {
            $_tmp = AttachUtil::getAttachData($v["attach_id"][0]);
            $v["attach_url"] = FileUtil::getAttachUrl() . "/" . $_tmp[$v["attach_id"][0]]["attachment"];
        }

        $diggArr = FeedDigg::model()->checkIsDigg($feedid, Ibos::app()->user->uid);
        $data = array("diggArr" => $diggArr, "fd" => $feedInfo, "assetUrl" => Ibos::app()->assetManager->getAssetsUrl("user"), "moduleAssetUrl" => Ibos::app()->assetManager->getAssetsUrl("weibo"));
        $this->ajaxReturn($data, "JSONP");
    }

    public function actionGetCommentList()
    {
        $module = StringUtil::filterCleanHtml($_REQUEST["module"]);
        $table = StringUtil::filterCleanHtml($_REQUEST["table"]);
        $rowid = intval($_REQUEST["feedid"]);
        $moduleuid = intval($_REQUEST["moduleuid"]);
        $properties = array(
            "module"     => $module,
            "table"      => $table,
            "attributes" => array("rowid" => $rowid, "limit" => 10, "moduleuid" => $moduleuid)
        );
        $widget = Ibos::app()->getWidgetFactory()->createWidget($this, "IWWeiboComment", $properties);
        $list = $widget->getCommentList();

        foreach ($list as &$v) {
            unset($v["user_info"]);
            unset($v["sourceInfo"]);
        }

        $this->ajaxReturn($list, "JSONP");
    }

    public function actionDigg()
    {
        $uid = Ibos::app()->user->uid;
        $feedId = intval(EnvUtil::getRequest("feedId"));
        $alreadyDigg = FeedDigg::model()->getIsExists($feedId, $uid);

        if ($alreadyDigg) {
            $result = FeedDigg::model()->delDigg($feedId, $uid);

            if ($result) {
                $feed = Feed::model()->get($feedId);
                $res["isSuccess"] = true;
                $res["count"] = intval($feed["diggcount"]);
                $res["digg"] = 0;
            } else {
                $res["isSuccess"] = false;
                $res["msg"] = FeedDigg::model()->getError("delDigg");
            }
        } else {
            $result = FeedDigg::model()->addDigg($feedId, $uid);

            if ($result) {
                $feed = Feed::model()->get($feedId);
                $res["isSuccess"] = true;
                $res["count"] = intval($feed["diggcount"]);
                $res["digg"] = 1;
            } else {
                $res["isSuccess"] = false;
                $res["msg"] = FeedDigg::model()->getError("addDigg");
            }
        }

        $this->ajaxReturn($res, "JSONP");
    }

    public function actionDiggList()
    {
        $feedId = intval(EnvUtil::getRequest("feedid"));
        $count = FeedDigg::model()->countByAttributes(array("feedid" => $feedId));
        $res = array();

        if ($count) {
            $result = FeedDigg::model()->fetchUserList($feedId, 100);
            $res["count"] = $count;
            $res["data"] = $result;
            $res["isSuccess"] = true;
            $this->ajaxReturn($res, "JSONP");
        } else {
            $this->ajaxReturn(array("count" => 0, "isSuccess" => true), "JSONP");
        }
    }

    public function actionFollower()
    {
        $uid = $_REQUEST["uid"];
        $count = Follow::model()->getFollowCount(array($uid));
        $list = $this->getFollowData("follower", $uid, 0, WbConst::DEF_LIST_FEED_NUMS);
        $data = array("count" => $count[$uid], "list" => $list);
        $this->ajaxReturn($data, "JSONP");
    }

    public function actionFollowing()
    {
        $uid = $_REQUEST["uid"];
        $count = Follow::model()->getFollowCount(array($uid));
        $list = $this->getFollowData("following", $uid, 0, 100);
        $data = array("count" => $count[$uid], "list" => $list);
        $this->ajaxReturn($data, "JSONP");
    }

    protected function getFollowData($type, $uid, $offset, $limit)
    {
        if ($type == "follower") {
            $data = Follow::model()->getFollowerList($uid, $offset, $limit);
        } else {
            $data = Follow::model()->getFollowingList($uid, $offset, $limit);
        }

        if (!empty($data)) {
            $fids = ConvertUtil::getSubByKey($data, "fid");
            $list = Follow::model()->getFollowStateByFids(Ibos::app()->user->uid, $fids);
        } else {
            $list = array();
        }

        return $list;
    }

    protected function getData($var)
    {
        $data = array();
        $type = (isset($var["new"]) ? "new" . $var["type"] : $var["type"]);

        switch ($type) {
            case "following":
                $pages = PageUtil::create(1000, WbConst::DEF_LIST_FEED_NUMS);

                if (!empty($var["feedkey"])) {
                    $list = Feed::model()->searchFeed($var["feedkey"], "following", $var["loadId"], $var["nums"], $pages->getOffset());
                } else {
                    $where = "a.isdel = 0 AND " . WbfeedUtil::getViewCondition($this->uid, "a.");
                    if (isset($var["loadId"]) && (0 < $var["loadId"])) {
                        $where .= " AND a.feedid < '" . intval($var["loadId"]) . "'";
                    }

                    if (!empty($var["feedtype"]) && ($var["feedtype"] !== "all")) {
                        $where .= " AND a.type = '" . $var["feedtype"] . "'";
                    }

                    $list = Feed::model()->getFollowingFeed($where, $var["nums"], $pages->getOffset());
                }

                break;

            case "all":
                $pages = PageUtil::create(WbConst::MAX_VIEW_FEED_NUMS, WbConst::DEF_LIST_FEED_NUMS);

                if (!empty($var["feedkey"])) {
                    $list = Feed::model()->searchFeed($var["feedkey"], "all", $var["loadId"], $var["nums"], $pages->getOffset());
                } else {
                    $where = "isdel = 0 AND " . WbfeedUtil::getViewCondition($this->uid);
                    if (isset($var["loadId"]) && (0 < $var["loadId"])) {
                        $where .= " AND feedid < '" . intval($var["loadId"]) . "'";
                    }

                    if (!empty($var["feedtype"]) && ($var["feedtype"] !== "all")) {
                        $where .= " AND type = '" . StringUtil::filterCleanHtml($var["feedtype"]) . "'";
                    }

                    $list = Feed::model()->getList($where, $var["nums"], $pages->getOffset());
                }

                break;

            case "movement":
                $pages = PageUtil::create(WbConst::MAX_VIEW_FEED_NUMS, WbConst::DEF_LIST_FEED_NUMS);

                if (!empty($var["feedkey"])) {
                    $list = Feed::model()->searchFeed($var["feedkey"], "movement", $var["loadId"], $var["nums"], $pages->getOffset());
                } else {
                    $where = "isdel = 0 AND " . WbfeedUtil::getViewCondition($this->uid);
                    if (isset($var["loadId"]) && (0 < $var["loadId"])) {
                        $where .= " AND feedid < '" . intval($var["loadId"]) . "'";
                    }

                    if (!empty($var["feedtype"]) && ($var["feedtype"] !== "all")) {
                        $where .= " AND module = '" . StringUtil::filterCleanHtml($var["feedtype"]) . "'";
                    } else {
                        $where .= " AND module != 'weibo'";
                    }

                    $list = Feed::model()->getList($where, $var["nums"], $pages->getOffset());
                }

                break;

            case "newmovement":
                if (0 < $var["maxId"]) {
                    $where = sprintf("isdel = 0 AND %s AND feedid > %d", WbfeedUtil::getViewCondition($this->uid), intval($var["maxId"]), $this->uid);
                    $list = Feed::model()->getList($where);
                    $data["count"] = count($list);
                }

                break;

            case "newfollowing":
                $where = "a.isdel = 0 AND " . WbfeedUtil::getViewCondition($this->uid, "a.");

                if (0 < $var["maxId"]) {
                    $where .= " AND a.feedid > '" . intval($var["maxId"]) . "'";
                    $list = Feed::model()->getFollowingFeed($where);
                    $data["count"] = count($list);
                }

                break;

            case "newall":
                if (0 < $var["maxId"]) {
                    $where = sprintf("isdel = 0 AND %s AND feedid > %d AND uid <> %d", WbfeedUtil::getViewCondition($this->uid), intval($var["maxId"]), $this->uid);
                    $list = Feed::model()->getList($where);
                    $data["count"] = count($list);
                }

                break;

            default:
                break;
        }

        if (!isset($var["new"])) {
            $pages->route = "home/index";
        }

        if (!empty($list)) {
            $data["firstId"] = $list[0]["feedid"];
            $data["lastId"] = $list[count($list) - 1]["feedid"];
            $feedids = ConvertUtil::getSubByKey($list, "feedid");
            $diggArr = FeedDigg::model()->checkIsDigg($feedids, $this->uid);

            foreach ($list as &$v) {
                switch ($v["module"]) {
                    case "mobile":
                        break;

                    default:
                        $v["from"] = EnvUtil::getFromClient($v["from"], $v["module"]);
                        break;
                }

                if (isset($v["attach_id"][0])) {
                    $_tmp = AttachUtil::getAttachData($v["attach_id"][0]);
                    $v["attach_url"] = FileUtil::getAttachUrl() . "/" . $_tmp[$v["attach_id"][0]]["attachment"];
                }

                if (isset($v["api_source"]["attach"][0]["attach_url"])) {
                    $v["api_source"]["attach_url"] = $v["api_source"]["attach"][0]["attach_url"];
                    unset($v["api_source"]["attach"]);
                    unset($v["api_source"]["source_body"]);
                }

                unset($v["user_info"]);
                unset($v["body"]);
                unset($v["sourceInfo"]);
                unset($v["api_source"]["source_user_info"]);
                unset($v["api_source"]["avatar_big"]);
                unset($v["api_source"]["avatar_middle"]);
                unset($v["api_source"]["avatar_small"]);
                unset($v["api_source"]["source_url"]);
                unset($v["feeddata"]);
            }

            $data["list"] = $list;
            $data["diggArr"] = $diggArr;
        } else {
            $data["list"] = array();
            $data["firstId"] = $data["lastId"] = 0;
        }

        return $data;
    }

    public function actionAddComment()
    {
        $return = array("isSuccess" => false);
        $data = $_GET;

        foreach ($data as $key => $val) {
            $data[$key] = StringUtil::filterCleanHtml($data[$key]);
        }

        $data["uid"] = Ibos::app()->user->uid;
        $data["content"] = StringUtil::filterDangerTag($data["content"]);
        $table = ucfirst($data["table"]);
        $pk = $table::model()->getTableSchema()->primaryKey;
        $sourceInfo = $table::model()->fetch(array("condition" => "`$pk` = {$data["rowid"]}"));

        if (!$sourceInfo) {
            $return["isSuccess"] = false;
            $this->ajaxReturn($return, "JSONP");
        }

        $data["cid"] = Comment::model()->addComment($data);
        $data["ctime"] = TIMESTAMP;

        if ($data["cid"]) {
            $return["isSuccess"] = true;
        }

        $this->ajaxReturn($return, "JSONP");
    }
}
