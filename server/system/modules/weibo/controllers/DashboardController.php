<?php

class WeiboDashboardController extends DashboardBaseController
{
    public function actionSetup()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $_POST["wbpostfrequency"] = (5 < intval($_POST["wbpostfrequency"]) ? $_POST["wbpostfrequency"] : 5);
            $_POST["wbnums"] = (140 <= intval($_POST["wbnums"]) ? $_POST["wbnums"] : 140);
            $wbwatermark = (isset($_POST["wbwatermark"]) ? 1 : 0);
            $wbwcenabled = (isset($_POST["wbwcenabled"]) ? 1 : 0);
            $postType = array("image" => 0, "topic" => 0, "praise" => 0);

            if (isset($_POST["wbposttype"])) {
                foreach ($postType as $key => &$val) {
                    if (isset($_POST["wbposttype"][$key])) {
                        $val = 1;
                    }
                }
            }

            if (isset($_POST["wbmovements"])) {
            } else {
                $_POST["wbmovements"] = array();
            }

            $data = array("wbnums" => $_POST["wbnums"], "wbpostfrequency" => $_POST["wbpostfrequency"], "wbposttype" => $postType, "wbwatermark" => $wbwatermark, "wbwcenabled" => $wbwcenabled, "wbmovement" => $_POST["wbmovements"]);

            foreach ($data as $key => $value) {
                Setting::model()->updateSettingValueByKey($key, $value);
            }

            CacheUtil::update("setting");
            $this->success(Ibos::lang("Operation succeed", "message"));
        } else {
            $data = array("config" => WbCommonUtil::getSetting(), "movementModule" => WbCommonUtil::getMovementModules());
            $this->render("setup", $data);
        }
    }

    public function actionManage()
    {
        $op = EnvUtil::getRequest("op");

        if (EnvUtil::submitCheck("formhash")) {
            if (!in_array($op, array("delFeed", "deleteFeed", "feedRecover"))) {
                exit();
            }

            $ids = EnvUtil::getRequest("ids");

            foreach (explode(",", $ids) as $id) {
                Feed::model()->doEditFeed($id, $op);
            }

            $this->ajaxReturn(array("isSuccess" => true));
        } else {
            if (!in_array($op, array("list", "recycle"))) {
                $op = "list";
            }

            $map = "";

            if ($op == "list") {
                $map = "isdel = 0";
            } else {
                $map = "isdel = 1";
            }

            if (EnvUtil::getRequest("search")) {
                $key = StringUtil::filterCleanHtml(EnvUtil::getRequest("search"));
                $count = Feed::model()->countSearchFeeds($key);
                $inSearch = true;
            } else {
                $count = Feed::model()->count($map);
                $inSearch = false;
            }

            $pages = PageUtil::create($count);

            if ($inSearch) {
                $list = Feed::model()->searchFeeds($key, null, $pages->getLimit(), $pages->getOffset());
            } else {
                $list = Feed::model()->getList($map, $pages->getLimit(), $pages->getOffset());
            }

            $data = array("op" => $op, "list" => $list, "pages" => $pages, "moduleAssetUrl" => Ibos::app()->assetManager->getAssetsUrl("weibo"));
            $this->render("manage", $data);
        }
    }

    public function actionComment()
    {
        $op = EnvUtil::getRequest("op");

        if (EnvUtil::submitCheck("formhash")) {
            if (!in_array($op, array("delComment", "deleteComment", "commentRecover"))) {
                exit();
            }

            $ids = EnvUtil::getRequest("ids");

            foreach (explode(",", $ids) as $id) {
                Comment::model()->doEditComment($id, $op);
            }

            $this->ajaxReturn(array("isSuccess" => true));
        } else {
            if (!in_array($op, array("list", "recycle"))) {
                $op = "list";
            }

            $map = "";

            if ($op == "list") {
                $map = "isdel = 0";
            } else {
                $map = "isdel = 1";
            }

            if (EnvUtil::getRequest("search")) {
                $key = StringUtil::filterCleanHtml(EnvUtil::getRequest("search"));
                $map .= " AND content LIKE '%$key%'";
            }

            $count = Comment::model()->count($map);
            $pages = PageUtil::create($count);
            $list = Comment::model()->getCommentList($map, "cid DESC", $pages->getLimit(), $pages->getOffset());
            $data = array("op" => $op, "list" => $list, "pages" => $pages, "moduleAssetUrl" => Ibos::app()->assetManager->getAssetsUrl("weibo"));
            $this->render("comment", $data);
        }
    }
}
