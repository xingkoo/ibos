<?php

class WeiboPersonalController extends UserHomeBaseController
{
    public function actionIndex()
    {
        $data = array("movements" => Ibos::app()->setting->get("setting/wbmovement"), "colleagues" => $this->getRelation("colleague"), "assetUrl" => Ibos::app()->assetManager->getAssetsUrl("user"), "moduleAssetUrl" => Ibos::app()->assetManager->getAssetsUrl("weibo"));

        if (!$this->getIsMe()) {
            $data["bothfollow"] = $this->getRelation("bothfollow");
            $data["secondfollow"] = $this->getRelation("secondfollow");
        }

        $var["movements"] = Ibos::app()->setting->get("setting/wbmovement");
        $var["enableMovementModule"] = WbCommonUtil::getMovementModules();
        $var["type"] = (isset($_GET["type"]) ? StringUtil::filterCleanHtml($_GET["type"]) : "all");
        $var["feedtype"] = (isset($_GET["feedtype"]) ? StringUtil::filterCleanHtml($_GET["feedtype"]) : "all");
        $var["feedkey"] = (isset($_GET["feedkey"]) ? StringUtil::filterCleanHtml(urldecode($_GET["feedkey"])) : "");
        $var["loadNew"] = (isset($_GET["page"]) ? 0 : 1);
        $var["loadMore"] = (isset($_GET["page"]) ? 0 : 1);
        $var["loadId"] = 0;
        $var["nums"] = (isset($_GET["page"]) ? WbConst::DEF_LIST_FEED_NUMS : 10);
        $user = $this->getUser();
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Enterprise weibo"), "url" => $this->createUrl("home/index")),
            array("name" => $user["realname"] . Ibos::lang("sbs feed"), "url" => $this->createUrl("personal/index", array("uid" => $this->getUid()))),
            array("name" => Ibos::lang("List"))
        ));
        $this->render("index", array_merge($data, $var, $this->getData($var)), false, array("user.default"));
    }

    public function actionLoadMore()
    {
        $data = $_GET + $_POST;
        if (!empty($data["page"]) || (intval($data["loadcount"]) == 2)) {
            unset($data["loadId"]);
            $data["nums"] = WbConst::DEF_LIST_FEED_NUMS;
        } else {
            $return = array("status" => -1, "msg" => Ibos::lang("Loading ID isnull"));
            $data["loadId"] = intval($data["loadId"]);
            $data["nums"] = 5;
        }

        $content = $this->getData($data);
        if (empty($content["html"]) || (empty($data["loadId"]) && (intval($data["loadcount"]) != 2))) {
            $return = array("status" => 0, "msg" => Ibos::lang("Weibo is not new"));
        } else {
            $return = array("status" => 1, "msg" => Ibos::lang("Weibo success load"));
            $return["data"] = $content["html"];
            $return["loadId"] = $content["lastId"];
            $return["firstId"] = (empty($data["page"]) && empty($data["loadId"]) ? $content["firstId"] : 0);
            $return["pageData"] = $content["pageData"];
        }

        $this->ajaxReturn($return);
    }

    public function actionLoadNew()
    {
        $return = array("status" => -1, "msg" => "");
        $_REQUEST["maxId"] = intval($_REQUEST["maxId"]);

        if (empty($_REQUEST["maxId"])) {
            $this->ajaxReturn($return);
        }

        $content = $this->getData($_REQUEST);

        if (empty($content["html"])) {
            $return = array("status" => 0, "msg" => Ibos::lang("Weibo is not new"));
        } else {
            $return = array("status" => 1, "msg" => Ibos::lang("Weibo success load"));
            $return["html"] = $content["html"];
            $return["maxId"] = intval($content["firstId"]);
            $return["count"] = intval($content["count"]);
        }

        $this->ajaxReturn($return);
    }

    public function actionLoadFollow()
    {
        $type = EnvUtil::getRequest("type");
        $offset = intval(EnvUtil::getRequest("offset"));
        $count = Follow::model()->getFollowCount(array($this->getUid()));
        $list = $this->getFollowData($type, $offset, WbConst::DEF_LIST_FEED_NUMS);
        $res = array("isSuccess" => true, "data" => $this->renderPartial("followlist", array("list" => $list)), "offset" => $offset + WbConst::DEF_LIST_FEED_NUMS, "more" => !!0 < ($count[$this->getUid()][$type] - $offset));
        $this->ajaxReturn($res);
    }

    public function actionGetRelation()
    {
        $type = EnvUtil::getRequest("type");
        $offset = EnvUtil::getRequest("offset");
        $data = $this->getRelation($type, $offset);
        $res = array("isSuccess" => true, "data" => $this->renderPartial("relation", array("list" => $data["list"]), true));

        if (!empty($data["count"])) {
            $res["offset"] = intval($offset) + 4;
        }

        $this->ajaxReturn($res);
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

        if ($feedInfo["type"] === "postimage") {
            $var = unserialize($feedInfo["feeddata"]);
            $feedInfo["image_body"] = $var["body"];

            if (!empty($var["attach_id"])) {
                $attach = AttachUtil::getAttachData($var["attach_id"]);
                $attachUrl = FileUtil::getAttachUrl();

                foreach ($attach as $ak => $av) {
                    $_attach = array("attach_id" => $av["aid"], "attach_name" => $av["filename"], "attach_url" => FileUtil::fileName($attachUrl . "/" . $av["attachment"]), "extension" => StringUtil::getFileExt($av["filename"]), "size" => $av["filesize"]);
                    $_attach["attach_small"] = WbCommonUtil::getThumbImageUrl($av, WbConst::ALBUM_DISPLAY_WIDTH, WbConst::ALBUM_DISPLAY_HEIGHT);
                    $_attach["attach_middle"] = WbCommonUtil::getThumbImageUrl($av, WbConst::WEIBO_DISPLAY_WIDTH, WbConst::WEIBO_DISPLAY_HEIGHT);
                    $feedInfo["attachInfo"][$ak] = $_attach;
                }
            }
        }

        $diggArr = FeedDigg::model()->checkIsDigg($feedid, Ibos::app()->user->uid);
        $data = array("diggArr" => $diggArr, "fd" => $feedInfo, "assetUrl" => Ibos::app()->assetManager->getAssetsUrl("user"), "moduleAssetUrl" => Ibos::app()->assetManager->getAssetsUrl("weibo"), "colleagues" => $this->getRelation("colleague"));

        if (!$this->getIsMe()) {
            $data["bothfollow"] = $this->getRelation("bothfollow");
            $data["secondfollow"] = $this->getRelation("secondfollow");
        }

        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Enterprise weibo"), "url" => $this->createUrl("home/index")),
            array("name" => $feedInfo["user_info"]["realname"] . Ibos::lang("sbs feed"), "url" => $this->createUrl("personal/index", array("uid" => $this->getUid()))),
            array("name" => Ibos::lang("Detail"))
        ));
        $this->render("detail", $data, false, array("user.default"));
    }

    public function actionFollower()
    {
        $user = $this->getUser();
        $count = Follow::model()->getFollowCount(array($user["uid"]));
        $list = $this->getFollowData("follower", 0, WbConst::DEF_LIST_FEED_NUMS);

        if ($this->getIsMe()) {
            UserData::model()->resetUserCount($this->getUid(), "new_folower_count", 0);
        }

        $data = array("count" => $count[$user["uid"]], "list" => $list, "assetUrl" => Ibos::app()->assetManager->getAssetsUrl("user"), "moduleAssetUrl" => Ibos::app()->assetManager->getAssetsUrl("weibo"), "limit" => WbConst::DEF_LIST_FEED_NUMS);
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Enterprise weibo"), "url" => $this->createUrl("home/index")),
            array("name" => $user["realname"] . Ibos::lang("sbs fans"), "url" => $this->createUrl("personal/follower", array("uid" => $user["uid"]))),
            array("name" => Ibos::lang("List"))
        ));
        $this->render("follower", $data, false, array("user.default"));
    }

    public function actionFollowing()
    {
        $user = $this->getUser();
        $count = Follow::model()->getFollowCount(array($user["uid"]));
        $list = $this->getFollowData("following", 0, WbConst::DEF_LIST_FEED_NUMS);
        $data = array("count" => $count[$user["uid"]], "list" => $list, "assetUrl" => Ibos::app()->assetManager->getAssetsUrl("user"), "moduleAssetUrl" => Ibos::app()->assetManager->getAssetsUrl("weibo"), "limit" => WbConst::DEF_LIST_FEED_NUMS);
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Enterprise weibo"), "url" => $this->createUrl("home/index")),
            array("name" => $user["realname"] . Ibos::lang("sbs follow"), "url" => $this->createUrl("personal/following", array("uid" => $user["uid"]))),
            array("name" => Ibos::lang("List"))
        ));
        $this->render("following", $data, false, array("user.default"));
    }

    protected function getFollowData($type, $offset, $limit)
    {
        if ($type == "follower") {
            $data = Follow::model()->getFollowerList($this->getUid(), $offset, $limit);
        } else {
            $data = Follow::model()->getFollowingList($this->getUid(), $offset, $limit);
        }

        if (!empty($data)) {
            $fids = ConvertUtil::getSubByKey($data, "fid");
            $followStates = Follow::model()->getFollowStateByFids(Ibos::app()->user->uid, $fids);

            foreach ($followStates as $uid => &$followState) {
                $followState["user"] = User::model()->fetchByUid($uid);
            }

            $list = &$followStates;
        } else {
            $list = array();
        }

        return $list;
    }

    protected function getRelation($type, $offset = 0, $limit = 4)
    {
        $data = array();

        switch ($type) {
            case "colleague":
                $data = $this->getColleagues($this->getUser(), false);
                $data = array_merge($data, array());
                break;

            case "bothfollow":
                $data = Follow::model()->getBothFollow($this->getUid(), Ibos::app()->user->uid);

                if (!empty($data)) {
                    $data = User::model()->fetchAllByUids($data);
                }

                break;

            case "secondfollow":
                $data = Follow::model()->getSecondFollow(Ibos::app()->user->uid, $this->getUid());

                if (!empty($data)) {
                    $data = User::model()->fetchAllByUids($data);
                }

                break;

            default:
                break;
        }

        return array("count" => count($data), "list" => array_slice($data, $offset, $limit));
    }

    protected function getData($var)
    {
        $data = array();
        $type = (isset($var["new"]) ? "new" . $var["type"] : $var["type"]);
        $where = "isdel = 0 AND uid = " . $this->getUid() . ($this->getIsMe() ? "" : " AND " . WbfeedUtil::getViewCondition(Ibos::app()->user->uid));

        switch ($type) {
            case "all":
                $pages = PageUtil::create(WbConst::MAX_VIEW_FEED_NUMS, WbConst::DEF_LIST_FEED_NUMS);

                if (!empty($var["feedkey"])) {
                    $loadId = (isset($var["loadId"]) ? $var["loadId"] : 0);
                    $list = Feed::model()->searchFeed($var["feedkey"], "all", $loadId, $var["nums"], $pages->getOffset(), "", $this->getUid());
                    $count = Feed::model()->countSearchAll($var["feedkey"], $loadId);
                } else {
                    if (isset($var["loadId"]) && (0 < $var["loadId"])) {
                        $where .= " AND feedid < '" . intval($var["loadId"]) . "'";
                    }

                    if (!empty($var["feedtype"]) && ($var["feedtype"] !== "all")) {
                        $where .= " AND type = '" . StringUtil::filterCleanHtml($var["feedtype"]) . "'";
                    }

                    $list = Feed::model()->getList($where, $var["nums"], $pages->getOffset());
                    $count = Feed::model()->count($where);
                }

                break;

            case "movement":
                $pages = PageUtil::create(WbConst::MAX_VIEW_FEED_NUMS, WbConst::DEF_LIST_FEED_NUMS);

                if (!empty($var["feedkey"])) {
                    $loadId = (isset($var["loadId"]) ? $var["loadId"] : 0);
                    $list = Feed::model()->searchFeed($var["feedkey"], "movement", $loadId, $var["nums"], $pages->getOffset(), "", $this->getUid());
                    $count = Feed::model()->countSearchMovement($var["feedkey"], $loadId);
                } else {
                    if (isset($var["loadId"]) && (0 < $var["loadId"])) {
                        $where .= " AND feedid < '" . intval($var["loadId"]) . "'";
                    }

                    if (!empty($var["feedtype"]) && ($var["feedtype"] !== "all")) {
                        $where .= " AND module = '" . StringUtil::filterCleanHtml($var["feedtype"]) . "'";
                    } else {
                        $where .= " AND module != 'weibo'";
                    }

                    $list = Feed::model()->getList($where, $var["nums"], $pages->getOffset());
                    $count = Feed::model()->count($where);
                }

                break;

            case "newmovement":
                if (0 < $var["maxId"]) {
                    $where = sprintf("isdel = 0 AND %s AND feedid > %d AND uid = %d", WbfeedUtil::getViewCondition(Ibos::app()->user->uid), intval($var["maxId"]), $this->uid);
                    $list = Feed::model()->getList($where);
                    $count = Feed::model()->count($where);
                    $data["count"] = count($list);
                }

                break;

            case "newall":
                if (0 < $var["maxId"]) {
                    $where = sprintf("isdel = 0 %s AND feedid > %d AND uid = %d", $this->getIsMe() ? "" : " AND " . WbfeedUtil::getViewCondition(Ibos::app()->user->uid), intval($var["maxId"]), $this->getUid());
                    $list = Feed::model()->getList($where);
                    $count = Feed::model()->countFollowingFeed($where);
                    $data["count"] = count($list);
                }

                break;

            default:
                break;
        }

        $count = (isset($count) ? $count : WbConst::MAX_VIEW_FEED_NUMS);
        $pages = PageUtil::create($count, WbConst::DEF_LIST_FEED_NUMS);

        if (!isset($var["new"])) {
            $pages->route = "personal/index";
            $currentUrl = (string) Ibos::app()->getRequest()->getUrl();
            $replaceUrl = str_replace("weibo/personal/loadmore", "weibo/personal/index", $currentUrl);
            $data["pageData"] = $this->widget("IWPage", array("pages" => $pages, "currentUrl" => $replaceUrl), true);
        }

        if (!empty($list)) {
            $data["firstId"] = $list[0]["feedid"];
            $data["lastId"] = $list[count($list) - 1]["feedid"];
            $feedids = ConvertUtil::getSubByKey($list, "feedid");
            $diggArr = FeedDigg::model()->checkIsDigg($feedids, $this->getUid());

            foreach ($list as &$v) {
                switch ($v["module"]) {
                    case "mobile":
                        break;

                    default:
                        $v["from"] = EnvUtil::getFromClient($v["from"], $v["module"]);
                        break;
                }
            }

            $data["html"] = $this->renderPartial("application.modules.message.views.feed.feedlist", array("list" => $list, "diggArr" => $diggArr), true);
        } else {
            $data["html"] = "";
            $data["firstId"] = $data["lastId"] = 0;
        }

        return $data;
    }
}
