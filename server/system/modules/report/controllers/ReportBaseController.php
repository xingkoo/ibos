<?php

class ReportBaseController extends ICController
{
    const COMPLETE_FALG = 10;
    const UNSTART_FALG = 0;

    /**
     * 查询条件
     * @var string 
     * @access protected 
     */
    protected $_condition;

    public function getReportConfig()
    {
        return ReportUtil::getSetting();
    }

    protected function showDetail()
    {
        $repid = intval(EnvUtil::getRequest("repid"));
        $isShowTitle = EnvUtil::getRequest("isShowTitle");
        $fromController = EnvUtil::getRequest("fromController");
        $report = Report::model()->fetchByPk($repid);
        $uid = Ibos::app()->user->uid;
        Report::model()->addReaderuid($report, $uid);
        $record = ReportRecord::model()->fetchAllRecordByRep($report);
        $attachs = array();

        if (!empty($report["attachmentid"])) {
            $attachs = AttachUtil::getAttach($report["attachmentid"], true, true, false, false, true);
        }

        $readers = array();

        if (!empty($report["readeruid"])) {
            $readerArr = explode(",", $report["readeruid"]);
            $readers = User::model()->fetchAllByPk($readerArr);
        }

        $stampUrl = "";

        if ($report["stamp"] != 0) {
            $stamp = Stamp::model()->fetchStampById($report["stamp"]);
            $stampUrl = FileUtil::fileName(Stamp::STAMP_PATH) . $stamp;
        }

        $report["addtime"] = ConvertUtil::formatDate($report["addtime"], "u");
        $params = array("lang" => Ibos::getLangSource("report.default"), "repid" => $repid, "report" => $report, "uid" => $uid, "orgPlanList" => $record["orgPlanList"], "outSidePlanList" => $record["outSidePlanList"], "nextPlanList" => $record["nextPlanList"], "attachs" => $attachs, "readers" => $readers, "stampUrl" => $stampUrl, "fromController" => $fromController, "isShowTitle" => $isShowTitle, "allowComment" => $this->getIsAllowComment($fromController));
        $detailAlias = "application.modules.report.views.detail";
        $detailView = $this->renderPartial($detailAlias, $params, true);
        $this->ajaxReturn(array("data" => $detailView, "isSuccess" => true));
    }

    protected function getIsAllowComment($controller)
    {
        $ret = 0;

        if ($controller == "review") {
            $ret = 1;
        }

        return $ret;
    }

    protected function search()
    {
        $type = EnvUtil::getRequest("type");
        $conditionCookie = MainUtil::getCookie("condition");

        if (empty($conditionCookie)) {
            MainUtil::setCookie("condition", $this->_condition, 10 * 60);
        }

        if ($type == "advanced_search") {
            $search = $_POST["search"];
            $this->_condition = ReportUtil::joinSearchCondition($search);
        } elseif ($type == "normal_search") {
            $keyword = $_POST["keyword"];
            MainUtil::setCookie("keyword", $keyword, 10 * 60);
            $this->_condition = " ( content LIKE '%$keyword%' OR subject LIKE '%$keyword%' ) ";
        } else {
            $this->_condition = $conditionCookie;
        }

        if ($this->_condition != MainUtil::getCookie("condition")) {
            MainUtil::setCookie("condition", $this->_condition, 10 * 60);
        }
    }

    protected function getStamp()
    {
        $config = $this->getReportConfig();

        if ($config["stampenable"]) {
            $stampDetails = $config["stampdetails"];
            $stamps = array();

            if (!empty($stampDetails)) {
                $stampidArr = explode(",", trim($stampDetails));

                if (0 < count($stampidArr)) {
                    foreach ($stampidArr as $stampidStr) {
                        list($stampId, $score) = explode(":", $stampidStr);

                        if ($stampId != 0) {
                            $stamps[$score] = intval($stampId);
                        }
                    }
                }
            }

            $stampList = Stamp::model()->fetchAll();
            $temp = array();

            foreach ($stampList as $stamp) {
                $stampid = $stamp["id"];
                $temp[$stampid]["title"] = $stamp["code"];
                $temp[$stampid]["stamp"] = $stamp["stamp"];
                $temp[$stampid]["value"] = $stamp["id"];
                $temp[$stampid]["path"] = FileUtil::fileName(Stamp::STAMP_PATH . $stamp["icon"]);
            }

            $result = array();

            if (!empty($stamps)) {
                foreach ($stamps as $score => $stampid) {
                    $result[$score] = $temp[$stampid];
                    $result[$score]["point"] = $score;
                }
            }

            $ret = CJSON::encode(array_values($result));
        } else {
            $ret = CJSON::encode("");
        }

        return $ret;
    }

    protected function issetStamp()
    {
        $config = $this->getReportConfig();
        return !!$config["stampenable"];
    }

    protected function issetAutoReview()
    {
        $config = $this->getReportConfig();
        return !!$config["autoreview"];
    }

    protected function getAutoReviewStamp()
    {
        $config = $this->getReportConfig();
        return intval($config["autoreviewstamp"]);
    }

    protected function getUnreviews()
    {
        $uid = Ibos::app()->user->uid;
        $subUidArr = User::model()->fetchSubUidByUid($uid);
        $count = "";

        if (!empty($subUidArr)) {
            $subUidStr = implode(",", $subUidArr);
            $unreviewReps = Report::model()->fetchUnreviewReps("FIND_IN_SET(`uid`, '$subUidStr')");

            if (!empty($unreviewReps)) {
                $count = count($unreviewReps);
            }
        }

        return $count;
    }

    protected function getReaderList()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $repid = EnvUtil::getRequest("repid");
            $record = Report::model()->fetchByPk($repid);
            $readerUids = $record["readeruid"];
            $htmlStr = "<table class=\"pop-table\">";

            if (!empty($readerUids)) {
                $htmlStr .= "<div class=\"rp-reviews-avatar\">";
                $readerUidArr = explode(",", trim($readerUids, ","));
                $users = User::model()->fetchAllByUids($readerUidArr);

                foreach ($users as $user) {
                    $htmlStr .= "<a href=\"" . Ibos::app()->createUrl("user/home/index", array("uid" => $user["uid"])) . "\">\r\n\t\t\t\t\t\t\t\t<img class=\"img-rounded\" src=\"" . $user["avatar_small"] . "\" title=\"" . $user["realname"] . "\" />\r\n\t\t\t\t\t\t\t</a>";
                }
            } else {
                $htmlStr .= "<div><li align=\"middle\">" . Ibos::lang("Has not reader") . "</li>";
            }

            $htmlStr .= "</div></table>";
            echo $htmlStr;
        }
    }

    protected function getCommentList()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $repid = EnvUtil::getRequest("repid");
            $records = Comment::model()->fetchAll(array(
                "select"    => array("uid", "content", "ctime"),
                "condition" => "module=:module AND `table`=:table AND rowid=:rowid AND isdel=:isdel ORDER BY ctime DESC LIMIT 0,5",
                "params"    => array(":module" => "report", ":table" => "report", ":rowid" => $repid, ":isdel" => 0)
            ));
            $htmlStr = "<div class=\"pop-comment\"><ul class=\"pop-comment-list\">";

            if (!empty($records)) {
                foreach ($records as $record) {
                    $record["realname"] = User::model()->fetchRealnameByUid($record["uid"]);
                    $content = StringUtil::cutStr($record["content"], 45);
                    $htmlStr .= "<li class=\"media\">\r\n\t\t\t\t\t\t\t\t\t<a href=\"" . Ibos::app()->createUrl("user/home/index", array("uid" => $record["uid"])) . "\" class=\"pop-comment-avatar pull-left\">\r\n\t\t\t\t\t\t\t\t\t\t<img src=\"avatar.php?uid=" . $record["uid"] . "&size=small&engine=" . ENGINE . "\" title=\"" . $record["realname"] . "\" class=\"img-rounded\"/>\r\n\t\t\t\t\t\t\t\t\t</a>\r\n\t\t\t\t\t\t\t\t\t<div class=\"media-body\">\r\n\t\t\t\t\t\t\t\t\t\t<p class=\"pop-comment-body\"><em>" . $record["realname"] . ": </em>" . $content . "</p>\r\n\t\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t\t</li>";
                }
            } else {
                $htmlStr .= "<li align=\"middle\">" . Ibos::lang("Has not comment") . "</li>";
            }

            $htmlStr .= "</ul></div>";
            echo $htmlStr;
        }
    }

    protected function checkIsHasSub()
    {
        $subUidArr = User::model()->fetchSubUidByUid(Ibos::app()->user->uid);

        if (!empty($subUidArr)) {
            return true;
        } else {
            return false;
        }
    }
}
