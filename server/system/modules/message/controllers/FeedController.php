<?php

class MessageFeedController extends MessageBaseController
{
    public function actionPostFeed()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $return = array("isSuccess" => true, "data" => "");
            $d["content"] = (isset($_POST["content"]) ? StringUtil::filterDangerTag($_POST["content"]) : "");
            $d["body"] = $_POST["body"];
            $d["rowid"] = (isset($_POST["rowid"]) ? intval($_POST["rowid"]) : 0);

            foreach ($_POST as $key => $val) {
                $_POST[$key] = StringUtil::filterCleanHtml($_POST[$key]);
            }

            $uid = Ibos::app()->user->uid;
            $user = User::model()->fetchByUid($uid);

            if (isset($_POST["view"])) {
                $_POST["view"] = $d["view"] = intval($_POST["view"]);

                if ($_POST["view"] == WbConst::SELFDEPT_VIEW_SCOPE) {
                    $d["deptid"] = $user["deptid"];
                }

                if ($_POST["view"] == WbConst::CUSTOM_VIEW_SCOPE) {
                    $scope = StringUtil::getId($_POST["viewid"], true);

                    if (isset($scope["u"])) {
                        $d["userid"] = implode(",", $scope["u"]);
                    }

                    if (isset($scope["d"])) {
                        $d["deptid"] = implode(",", $scope["d"]);
                    }

                    if (isset($scope["p"])) {
                        $d["positionid"] = implode(",", $scope["p"]);
                    }
                }
            }

            $d["source_url"] = (isset($_POST["source_url"]) ? urldecode($_POST["source_url"]) : "");
            $d["body"] = preg_replace("/#[\s]*([^#^\s][^#]*[^#^\s])[\s]*#/is", "#" . trim("\${1}") . "#", $d["body"]);

            if (isset($_POST["attachid"])) {
                $d["attach_id"] = trim(StringUtil::filterCleanHtml($_POST["attachid"]));

                if (!empty($d["attach_id"])) {
                    $d["attach_id"] = explode(",", $d["attach_id"]);
                    array_map("intval", $d["attach_id"]);
                }
            }

            $type = StringUtil::filterCleanHtml($_POST["type"]);
            $table = (isset($_POST["table"]) ? StringUtil::filterCleanHtml($_POST["table"]) : "feed");
            $module = (isset($_POST["module"]) ? StringUtil::filterCleanHtml($_POST["module"]) : "weibo");
            $data = Feed::model()->put(Ibos::app()->user->uid, $module, $type, $d, $d["rowid"], $table);

            if (!$data) {
                $return["isSuccess"] = false;
                $return["data"] = Feed::model()->getError("putFeed");
                $this->ajaxReturn($return);
            }

            UserUtil::updateCreditByAction("addweibo", Ibos::app()->user->uid);
            $data["from"] = EnvUtil::getFromClient($data["from"], $data["module"]);
            $lang = Ibos::getLangSources();
            $return["data"] = $this->renderPartial("feedlist", array(
                "list" => array($data),
                "lang" => $lang
            ), true);
            $return["feedid"] = $data["feedid"];
            FeedTopic::model()->addTopic(html_entity_decode($d["body"], ENT_QUOTES, "UTF-8"), $data["feedid"], $type);
            $this->ajaxReturn($return);
        }
    }

    public function actionAllDiggList()
    {
        $feedId = intval(EnvUtil::getRequest("feedid"));
        $result = FeedDigg::model()->fetchUserList($feedId, 5);
        $uids = ConvertUtil::getSubByKey($result, "uid");
        $followStates = Follow::model()->getFollowStateByFids(Ibos::app()->user->uid, $uids);
        $this->renderPartial("alldigglist", array("list" => $result, "followstates" => $followStates, "feedid" => $feedId));
    }

    public function actionSimpleDiggList()
    {
        $feedId = intval(EnvUtil::getRequest("feedid"));
        $count = FeedDigg::model()->countByAttributes(array("feedid" => $feedId));
        $res = array();

        if ($count) {
            $result = FeedDigg::model()->fetchUserList($feedId, 4);
            $res["count"] = $count;
            $res["data"] = $this->renderPartial("digglist", array("result" => $result, "count" => $count, "feedid" => $feedId), true);
            $res["isSuccess"] = true;
        } else {
            $res["isSuccess"] = false;
        }

        $this->ajaxReturn($res);
    }

    public function actionSetDigg()
    {
        $uid = Ibos::app()->user->uid;
        $feedId = intval(EnvUtil::getRequest("feedid"));
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
                $user = User::model()->fetchByUid($uid);
                $feed = Feed::model()->get($feedId);
                $res["isSuccess"] = true;
                $res["count"] = intval($feed["diggcount"]);
                $res["data"] = $this->renderPartial("digg", array("user" => $user), true);
                $res["digg"] = 1;
            } else {
                $res["isSuccess"] = false;
                $res["msg"] = FeedDigg::model()->getError("addDigg");
            }
        }

        $this->ajaxReturn($res);
    }

    public function actionRemoveFeed()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $return = array("isSuccess" => false, "data" => Ibos::lang("Del failed", "message"));
            $feedId = intval($_POST["feedid"]);
            $feed = Feed::model()->getFeedInfo($feedId);

            if (!$feed) {
                $this->ajaxReturn($return);
            }

            if ($feed["uid"] != Ibos::app()->user->uid) {
                if (!Ibos::app()->user->isadministrator) {
                    $this->ajaxReturn($return);
                }
            }

            $return = Feed::model()->doEditFeed($feedId, "delFeed", Ibos::app()->user->uid);
            $return["msg"] = ($return["isSuccess"] ? Ibos::lang("Del succeed", "message") : Ibos::lang("Del failed", "message"));
            $this->ajaxReturn($return);
        }
    }

    public function actionShareFeed()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $post = $_POST;

            foreach ($post as $key => $val) {
                $post[$key] = StringUtil::filterCleanHtml($post[$key]);
            }

            if (empty($post["curid"])) {
                $map["feedid"] = $post["sid"];
            } else {
                $map["feedid"] = $post["curid"];
            }

            $map["isdel"] = 0;
            $isExist = Feed::model()->countByAttributes($map);

            if ($isExist == 0) {
                $return["isSuccess"] = false;
                $return["data"] = "内容已被删除，转发失败";
                $this->ajaxReturn($return);
            }

            $return = Feed::model()->shareFeed($post, "share");

            if ($return["isSuccess"]) {
                $module = $post["module"];

                if ($module == "weibo") {
                    UserUtil::updateCreditByAction("forwardweibo", Ibos::app()->user->uid);
                    $suid = Ibos::app()->db->createCommand()->select("uid")->from("{{feed}}")->where(sprintf("feedid = %d AND isdel = 0", $map["feedid"]))->queryScalar();
                    $suid && UserUtil::updateCreditByAction("forwardedweibo", $suid);
                }

                $lang = Ibos::getLangSources();
                $return["data"] = $this->renderPartial("feedlist", array(
                    "list" => array($return["data"]),
                    "lang" => $lang
                ), true);
            }

            $this->ajaxReturn($return);
        }
    }

    public function actionAllowedlist()
    {
        $feedId = intval(EnvUtil::getRequest("feedid"));
        $feed = Feed::model()->getFeedInfo($feedId);

        if (!$feed) {
            exit("该条动态不存在");
        }

        $list = array();

        if ($feed["view"] == "1") {
            $list["users"] = Ibos::lang("My self");
        } elseif (!empty($feed["userid"])) {
            $list["users"] = User::model()->fetchRealnamesByUids($feed["userid"]);
        }

        if (!empty($feed["deptid"])) {
            if (($feed["deptid"] == "alldept") || ($feed["view"] == "0")) {
                $list["dept"] = Ibos::lang("All dept");
            } else {
                if ($feed["view"] == "2") {
                    $deptIds = StringUtil::filterStr(Ibos::app()->user->alldeptid . "," . Ibos::app()->user->alldowndeptid);
                } else {
                    $deptIds = $feed["deptid"];
                }

                if (!empty($deptIds)) {
                    $list["dept"] = Department::model()->fetchDeptNameByDeptId($deptIds);
                } else {
                    $list["dept"] = "";
                }
            }
        }

        if (!empty($feed["positionid"])) {
            $list["pos"] = Position::model()->fetchPosNameByPosId($feed["positionid"]);
        }

        $this->renderPartial("allowedlist", $list);
    }

    public function actionGetexp()
    {
        $this->ajaxReturn(array("data" => ExpressionUtil::getAllExpression()));
    }
}
