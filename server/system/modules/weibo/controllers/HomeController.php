<?php

class WeiboHomeController extends WeiboBaseController
{
    public function actionIndex()
    {
        $data = array();
        $data["userData"] = UserData::model()->getUserData($this->uid);
        $data["activeUser"] = UserData::model()->fetchActiveUsers();
        $data["movements"] = Ibos::app()->setting->get("setting/wbmovement");
        $data["enableMovementModule"] = WbCommonUtil::getMovementModules();
        $data["uploadConfig"] = AttachUtil::getUploadConfig();
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Enterprise weibo")),
            array("name" => Ibos::lang("Index"), "url" => $this->createUrl("list/index")),
            array("name" => Ibos::lang("List"))
        ));
        $var["type"] = (isset($_GET["type"]) ? StringUtil::filterCleanHtml($_GET["type"]) : "all");
        $var["feedtype"] = (isset($_GET["feedtype"]) ? StringUtil::filterCleanHtml($_GET["feedtype"]) : "all");
        $var["feedkey"] = (isset($_GET["feedkey"]) ? StringUtil::filterCleanHtml(urldecode($_GET["feedkey"])) : "");
        $var["loadNew"] = (isset($_GET["page"]) ? 0 : 1);
        $var["loadMore"] = (isset($_GET["page"]) ? 0 : 1);
        $var["loadId"] = 0;
        $var["nums"] = (isset($_GET["page"]) ? WbConst::DEF_LIST_FEED_NUMS : 10);
        $this->render("index", array_merge($data, $var, $this->getData($var)));
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

    protected function getData($var)
    {
        $data = array();
        $type = (isset($var["new"]) ? "new" . $var["type"] : $var["type"]);

        switch ($type) {
            case "following":
                $pages = PageUtil::create(1000, WbConst::DEF_LIST_FEED_NUMS);

                if (!empty($var["feedkey"])) {
                    $loadId = (isset($var["loadId"]) ? $var["loadId"] : 0);
                    $list = Feed::model()->searchFeed($var["feedkey"], "following", $loadId, $var["nums"], $pages->getOffset());
                    $count = Feed::model()->countSearchFollowing($var["feedkey"], $loadId);
                } else {
                    $where = "a.isdel = 0 AND " . WbfeedUtil::getViewCondition($this->uid, "a.");
                    if (isset($var["loadId"]) && (0 < $var["loadId"])) {
                        $where .= " AND a.feedid < '" . intval($var["loadId"]) . "'";
                    }

                    if (!empty($var["feedtype"]) && ($var["feedtype"] !== "all")) {
                        $where .= " AND a.type = '" . $var["feedtype"] . "'";
                    }

                    $list = Feed::model()->getFollowingFeed($where, $var["nums"], $pages->getOffset());
                    $count = Feed::model()->countFollowingFeed($where);
                }

                break;

            case "all":
                $pages = PageUtil::create(WbConst::MAX_VIEW_FEED_NUMS, WbConst::DEF_LIST_FEED_NUMS);

                if (!empty($var["feedkey"])) {
                    $loadId = (isset($var["loadId"]) ? $var["loadId"] : 0);
                    $list = Feed::model()->searchFeed($var["feedkey"], "all", $loadId, $var["nums"], $pages->getOffset());
                    $count = Feed::model()->countSearchAll($var["feedkey"], $loadId);
                } else {
                    $where = "isdel = 0 AND " . WbfeedUtil::getViewCondition($this->uid);
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
                    $list = Feed::model()->searchFeed($var["feedkey"], "movement", $loadId, $var["nums"], $pages->getOffset());
                    $count = Feed::model()->countSearchMovement($var["feedkey"], $loadId);
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
                    $count = Feed::model()->count($where);
                }

                break;

            case "newmovement":
                if (0 < $var["maxId"]) {
                    $where = sprintf("isdel = 0 AND %s AND feedid > %d", WbfeedUtil::getViewCondition($this->uid), intval($var["maxId"]), $this->uid);
                    $list = Feed::model()->getList($where);
                    $count = Feed::model()->count($where);
                    $data["count"] = count($list);
                }

                break;

            case "newfollowing":
                $where = "a.isdel = 0 AND " . WbfeedUtil::getViewCondition($this->uid, "a.");

                if (0 < $var["maxId"]) {
                    $where .= " AND a.feedid > '" . intval($var["maxId"]) . "'";
                    $list = Feed::model()->getFollowingFeed($where);
                    $count = Feed::model()->countFollowingFeed($where);
                    $data["count"] = count($list);
                }

                break;

            case "newall":
                if (0 < $var["maxId"]) {
                    $where = sprintf("isdel = 0 AND %s AND feedid > %d AND uid <> %d", WbfeedUtil::getViewCondition($this->uid), intval($var["maxId"]), $this->uid);
                    $list = Feed::model()->getList($where);
                    $count = Feed::model()->count($where);
                    $data["count"] = count($list);
                }

                break;

            default:
                break;
        }

        $count = (isset($count) ? $count : WbConst::MAX_VIEW_FEED_NUMS);
        $pages = PageUtil::create($count, WbConst::DEF_LIST_FEED_NUMS);

        if (!isset($var["new"])) {
            $pages->route = "home/index";
            $currentUrl = (string) Ibos::app()->getRequest()->getUrl();
            $replaceUrl = str_replace("weibo/home/loadmore", "weibo/home/index", $currentUrl);
            $data["pageData"] = $this->widget("IWPage", array("pages" => $pages, "currentUrl" => $replaceUrl), true);
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
            }

            $data["html"] = $this->renderPartial("application.modules.message.views.feed.feedlist", array("list" => $list, "diggArr" => $diggArr), true);
        } else {
            $data["html"] = "";
            $data["firstId"] = $data["lastId"] = 0;
        }

        return $data;
    }
}
