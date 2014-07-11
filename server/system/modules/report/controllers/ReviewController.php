<?php

class ReportReviewController extends ReportBaseController
{
    public function getSidebar($getUid, $getUser)
    {
        $uid = Ibos::app()->user->uid;

        if (!empty($getUid)) {
            $subUids = $getUid;
        } elseif (!empty($getUser)) {
            $subUids = ConvertUtil::getSubByKey($getUser, "uid");
        } else {
            $subUids = UserUtil::getAllSubs($uid, "", true);
        }

        $deptArr = UserUtil::getManagerDeptSubUserByUid($uid);
        $sidebarAlias = "application.modules.report.views.review.sidebar";
        $params = array("statModule" => Ibos::app()->setting->get("setting/statmodules"), "lang" => Ibos::getLangSource("report.default"), "deptArr" => $deptArr, "dashboardConfig" => $this->getReportConfig(), "reportTypes" => ReportType::model()->fetchAllTypeByUid($subUids));
        $sidebarView = $this->renderPartial($sidebarAlias, $params, false);
        return $sidebarView;
    }

    public function actionIndex()
    {
        $op = EnvUtil::getRequest("op");

        if (!in_array($op, array("default", "showDetail", "personal", "getsubordinates"))) {
            $op = "default";
        }

        if ($op == "default") {
            if (EnvUtil::getRequest("param") == "search") {
                $this->search();
            }

            $typeid = intval(EnvUtil::getRequest("typeid"));
            $uid = Ibos::app()->user->uid;
            $getSubUids = EnvUtil::getRequest("subUids");
            $typeCondition = (empty($typeid) ? 1 : "typeid = $typeid");

            if (empty($getSubUids)) {
                $subUidArr = User::model()->fetchSubUidByUid($uid);
                $getSubUids = implode(",", $subUidArr);
            } else {
                $subUidArr = explode(",", $getSubUids);

                foreach ($subUidArr as $subUid) {
                    if (!UserUtil::checkIsSub($uid, $subUid)) {
                        $this->error(Ibos::lang("Have not permission"), $this->createUrl("default/index"));
                    }
                }
            }

            $userCondition = "FIND_IN_SET(uid, '$getSubUids')";
            $condition = "( " . $typeCondition . " AND (" . $userCondition . " OR FIND_IN_SET($uid, `toid`) ) )";
            $this->_condition = ReportUtil::joinCondition($this->_condition, $condition);
            $paginationData = Report::model()->fetchAllByPage($this->_condition);
            $params = array("typeid" => $typeid, "pagination" => $paginationData["pagination"], "reportList" => ICReport::handelListData($paginationData["data"]), "dashboardConfig" => $this->getReportConfig());
            $this->setPageTitle(Ibos::lang("Review subordinate report"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Personal Office")),
                array("name" => Ibos::lang("Work report"), "url" => $this->createUrl("default/index")),
                array("name" => Ibos::lang("Subordinate report"))
            ));
            $this->render("index", $params);
        } else {
            $this->{$op}();
        }
    }

    private function personal()
    {
        $uid = Ibos::app()->user->uid;
        $typeid = EnvUtil::getRequest("typeid");
        $getUid = intval(EnvUtil::getRequest("uid"));
        $condition = "uid = '$getUid'";

        if (!UserUtil::checkIsSub($uid, $getUid)) {
            $condition .= " AND FIND_IN_SET('$uid', toid )";
        }

        if (!empty($typeid)) {
            $condition .= " AND typeid = '$typeid'";
        }

        if (EnvUtil::getRequest("param") == "search") {
            $this->search();
        }

        $this->_condition = ReportUtil::joinCondition($this->_condition, $condition);
        $paginationData = Report::model()->fetchAllByPage($this->_condition);
        $params = array("dashboardConfig" => Ibos::app()->setting->get("setting/reportconfig"), "typeid" => $typeid, "pagination" => $paginationData["pagination"], "reportList" => ICReport::handelListData($paginationData["data"]), "reportCount" => Report::model()->count("uid = '$getUid'"), "commentCount" => Report::model()->count("uid='$getUid' AND isreview=1"), "user" => User::model()->fetchByUid($getUid), "supUid" => UserUtil::getSupUid($getUid));
        $this->setPageTitle(Ibos::lang("Review subordinate report"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Personal Office")),
            array("name" => Ibos::lang("Work report"), "url" => $this->createUrl("default/index")),
            array("name" => Ibos::lang("Subordinate personal report"))
        ));
        $this->render("personal", $params);
    }

    public function actionShow()
    {
        $repid = intval(EnvUtil::getRequest("repid"));
        $uid = Ibos::app()->user->uid;

        if (empty($repid)) {
            $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("review/index"));
        }

        $report = Report::model()->fetchByPk($repid);

        if (empty($report)) {
            $this->error(Ibos::lang("No data found", "error"), $this->createUrl("review/index"));
        }

        if ($report["uid"] == $uid) {
            $this->redirect($this->createUrl("default/show", array("repid" => $repid)));
        }

        $permission = ICReport::checkPermission($report, $uid);

        if (!$permission) {
            $this->error(Ibos::lang("You do not have permission to view the report"), $this->createUrl("review/index"));
        }

        $record = ReportRecord::model()->fetchAllRecordByRep($report);
        $attachs = $readers = array();

        if (!empty($report["attachmentid"])) {
            $attachments = AttachUtil::getAttach($report["attachmentid"], true, true, false, false, true);
            $attachs = array_values($attachments);
        }

        if (!empty($report["readeruid"])) {
            $readerArr = explode(",", $report["readeruid"]);
            $readers = User::model()->fetchAllByPk($readerArr);
        }

        $stampUrl = "";

        if (!empty($report["stamp"])) {
            $stampUrl = Stamp::model()->fetchStampById($report["stamp"]);
        }

        $params = array("report" => $report, "preAndNextRep" => Report::model()->fetchPreAndNextRep($report), "orgPlanList" => $record["orgPlanList"], "outSidePlanList" => $record["outSidePlanList"], "nextPlanList" => $record["nextPlanList"], "attachs" => $attachs, "readers" => $readers, "stampUrl" => $stampUrl, "realname" => User::model()->fetchRealnameByUid($report["uid"]), "departmentName" => Department::model()->fetchDeptNameByUid($report["uid"]));

        if (!empty($params["nextPlanList"])) {
            $reportType = ReportType::model()->fetchByPk($report["typeid"]);
            $firstPlan = $params["nextPlanList"][0];
            $params["nextSubject"] = ICReport::handleShowSubject($reportType, $firstPlan["begindate"], $firstPlan["enddate"], 1);
        }

        $dashboardConfig = $this->getReportConfig();
        if ($dashboardConfig["stampenable"] && $dashboardConfig["autoreview"]) {
            $this->changeIsreview($repid);
        }

        $this->setPageTitle(Ibos::lang("Show subordinate report"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Personal Office")),
            array("name" => Ibos::lang("Work report"), "url" => $this->createUrl("default/index")),
            array("name" => Ibos::lang("Show subordinate report"))
        ));
        $this->render("show", $params);
    }

    public function actionEdit()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $op = EnvUtil::getRequest("op");
            $routes = array("changeIsreview");

            if (!in_array($op, $routes)) {
                $this->error(Ibos::lang("Can not find the path"), $this->createUrl("default/index"));
            }

            if ($op == "changeIsreview") {
                $repid = EnvUtil::getRequest("repid");
                $this->changeIsreview($repid);
            } else {
                $this->{$op}();
            }
        }
    }

    private function changeIsreview($repid)
    {
        $report = Report::model()->fetchByPk($repid);
        if (!empty($report) && UserUtil::checkIsUpUid($report["uid"], Ibos::app()->user->uid)) {
            if ($report["stamp"] == 0) {
                $stamp = $this->getAutoReviewStamp();
                Report::model()->modify($repid, array("isreview" => 1, "stamp" => $stamp));
                ReportStats::model()->scoreReport($report["repid"], $report["uid"], $stamp);
            } else {
                Report::model()->modify($repid, array("isreview" => 1));
            }
        }
    }

    private function getsubordinates()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $uid = $_GET["uid"];
            $getItem = EnvUtil::getRequest("item");
            $item = (empty($getItem) ? 5 : $getItem);
            $users = UserUtil::getAllSubs($uid);

            if (EnvUtil::getRequest("act") == "stats") {
                $theUrl = "report/stats/review";
            } else {
                $theUrl = "report/review/index";
            }

            $htmlStr = "<ul class=\"mng-trd-list\">";
            $num = 0;

            foreach ($users as $user) {
                if ($num < $item) {
                    $htmlStr .= "<li class=\"mng-item\">\r\n                                            <a href=\"" . Ibos::app()->urlManager->createUrl($theUrl, array("op" => "personal", "uid" => $user["uid"])) . "\">\r\n                                                <img src=\"" . $user["avatar_middle"] . "\" alt=\"\">\r\n                                                " . $user["realname"] . "\r\n                                            </a>\r\n                                        </li>";
                    $num++;
                }
            }

            $subNums = count($users);

            if ($item < $subNums) {
                $htmlStr .= "<li class=\"mng-item view-all\" data-uid=\"" . $uid . "\">\r\n                                                <a href=\"javascript:;\">\r\n                                                    <i class=\"o-da-allsub\"></i>\r\n                                                    " . Ibos::lang("View all subordinate") . "\r\n                                                </a>\r\n                                            </li>";
            }

            $htmlStr .= "</ul>";
            echo $htmlStr;
        }
    }

    private function getStampIcon()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $repid = $_GET["repid"];
            $report = Report::model()->fetchByPk($repid);

            if ($report["stamp"] != 0) {
                $icon = Stamp::model()->fetchIconById($report["stamp"]);
                $this->ajaxReturn(array("isSuccess" => true, "icon" => $icon));
            }
        }
    }
}
