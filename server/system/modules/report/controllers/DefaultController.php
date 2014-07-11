<?php

class ReportDefaultController extends ReportBaseController
{
    public function getSidebar()
    {
        $sidebarAlias = "application.modules.report.views.sidebar";
        $uid = Ibos::app()->user->uid;
        $params = array("statModule" => Ibos::app()->setting->get("setting/statmodules"), "lang" => Ibos::getLangSource("report.default"), "reportTypes" => ReportType::model()->fetchAllTypeByUid($uid));
        $sidebarView = $this->renderPartial($sidebarAlias, $params);
        return $sidebarView;
    }

    public function actionIndex()
    {
        $typeid = EnvUtil::getRequest("typeid");
        $uid = Ibos::app()->user->uid;
        $op = EnvUtil::getRequest("op");

        if (!in_array($op, array("default", "showDetail", "getReaderList", "getCommentList"))) {
            $op = "default";
        }

        if ($op == "default") {
            if (EnvUtil::getRequest("param") == "search") {
                $this->search();
            }

            if (empty($typeid)) {
                $typeCondition = 1;
            } else {
                $typeCondition = "typeid = '$typeid'";
            }

            $this->_condition = ReportUtil::joinCondition($this->_condition, "uid = '$uid' AND $typeCondition");
            $paginationData = Report::model()->fetchAllByPage($this->_condition);
            $params = array("typeid" => $typeid, "pagination" => $paginationData["pagination"], "reportList" => ICReport::handelListData($paginationData["data"]), "reportCount" => Report::model()->count("uid='$uid'"), "commentCount" => Report::model()->count("uid='$uid' AND isreview=1"), "user" => User::model()->fetchByUid($uid));
            $this->setPageTitle(Ibos::lang("My report"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Personal Office")),
                array("name" => Ibos::lang("Work report"), "url" => $this->createUrl("default/index")),
                array("name" => Ibos::lang("My report list"))
            ));
            $this->render("index", $params);
        } else {
            $this->{$op}();
        }
    }

    public function actionAdd()
    {
        $op = EnvUtil::getRequest("op");

        if (!in_array($op, array("new", "save"))) {
            $op = "new";
        }

        if ($op == "new") {
            $typeid = intval(EnvUtil::getRequest("typeid"));

            if (!$typeid) {
                $typeid = 1;
            }

            $uid = Ibos::app()->user->uid;
            $upUid = UserUtil::getSupUid($uid);
            $reportType = ReportType::model()->fetchByPk($typeid);
            $summaryAndPlanDate = ReportUtil::getDateByIntervalType($reportType["intervaltype"], $reportType["intervals"]);
            $subject = ICReport::handleShowSubject($reportType, strtotime($summaryAndPlanDate["summaryBegin"]), strtotime($summaryAndPlanDate["summaryEnd"]));
            $lastRep = Report::model()->fetchLastRepByUidAndTypeid($uid, $typeid);
            $orgPlanList = array();

            if (!empty($lastRep)) {
                $orgPlanList = ReportRecord::model()->fetchRecordByRepidAndPlanflag($lastRep["repid"], 2);
            }

            $params = array("typeid" => $typeid, "summaryAndPlanDate" => $summaryAndPlanDate, "intervals" => $reportType["intervals"], "intervaltype" => $reportType["intervaltype"], "subject" => $subject, "upUid" => StringUtil::wrapId($upUid), "uploadConfig" => AttachUtil::getUploadConfig(), "orgPlanList" => $orgPlanList, "isInstallCalendar" => ModuleUtil::getIsEnabled("calendar"));
            $this->setPageTitle(Ibos::lang("Add report"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Personal Office")),
                array("name" => Ibos::lang("Work report"), "url" => $this->createUrl("default/index")),
                array("name" => Ibos::lang("Add report"))
            ));
            $this->render("add", $params);
        } else {
            $this->{$op}();
        }
    }

    private function save()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $postData = $_POST;
            $uid = Ibos::app()->user->uid;
            $postData["uid"] = $uid;
            $postData["subject"] = StringUtil::filterCleanHtml($_POST["subject"]);
            $toidArr = StringUtil::getId($postData["toid"]);
            $postData["toid"] = implode(",", $toidArr);
            $postData["begindate"] = strtotime($postData["begindate"]);
            $postData["enddate"] = strtotime($postData["enddate"]);
            $reportData = ICReport::handleSaveData($postData);
            $repid = Report::model()->add($reportData, true);

            if ($repid) {
                if (!empty($postData["attachmentid"])) {
                    AttachUtil::updateAttach($postData["attachmentid"]);
                }

                $orgPlan = $outSidePlan = array();

                if (array_key_exists("orgPlan", $_POST)) {
                    $orgPlan = $_POST["orgPlan"];
                }

                if (!empty($orgPlan)) {
                    foreach ($orgPlan as $recordid => $val) {
                        $updateData = array("process" => intval($val["process"]), "exedetail" => StringUtil::filterCleanHtml($val["exedetail"]));

                        if ($updateData["process"] == self::COMPLETE_FALG) {
                            $updateData["flag"] = 1;
                        }

                        ReportRecord::model()->modify($recordid, $updateData);
                    }
                }

                if (array_key_exists("outSidePlan", $_POST)) {
                    $outSidePlan = array_filter($_POST["outSidePlan"], create_function("\$v", "return !empty(\$v[\"content\"]);"));
                }

                if (!empty($outSidePlan)) {
                    ReportRecord::model()->addPlans($outSidePlan, $repid, $postData["begindate"], $postData["enddate"], $uid, 1);
                }

                $nextPlan = array_filter($_POST["nextPlan"], create_function("\$v", "return !empty(\$v[\"content\"]);"));
                ReportRecord::model()->addPlans($nextPlan, $repid, strtotime($_POST["planBegindate"]), strtotime($_POST["planEnddate"]), $uid, 2);
                $wbconf = WbCommonUtil::getSetting(true);
                if (isset($wbconf["wbmovement"]["report"]) && ($wbconf["wbmovement"]["report"] == 1)) {
                    $userid = $postData["toid"];
                    $supUid = UserUtil::getSupUid($uid);
                    if ((0 < intval($supUid)) && !in_array($supUid, explode(",", $userid))) {
                        $userid = $userid . "," . $supUid;
                    }

                    $data = array("title" => Ibos::lang("Feed title", "", array("{subject}" => $postData["subject"], "{url}" => Ibos::app()->urlManager->createUrl("report/review/show", array("repid" => $repid)))), "body" => StringUtil::cutStr($_POST["content"], 140), "actdesc" => Ibos::lang("Post report"), "userid" => trim($userid, ","), "deptid" => "", "positionid" => "");
                    WbfeedUtil::pushFeed($uid, "report", "report", $repid, $data);
                }

                UserUtil::updateCreditByAction("addreport", $uid);

                if (!empty($toidArr)) {
                    $config = array("{sender}" => User::model()->fetchRealnameByUid($uid), "{subject}" => $reportData["subject"], "{url}" => Ibos::app()->urlManager->createUrl("report/review/show", array("repid" => $repid)));
                    Notify::model()->sendNotify($toidArr, "report_message", $config, $uid);
                }

                $this->success(Ibos::lang("Save succeed", "message"), $this->createUrl("default/index"));
            } else {
                $this->error(Ibos::lang("Save faild", "message"), $this->createUrl("default/index"));
            }
        }
    }

    public function actionEdit()
    {
        $op = EnvUtil::getRequest("op");
        $repid = intval(EnvUtil::getRequest("repid"));
        $uid = Ibos::app()->user->uid;
        if (empty($op) || !in_array($op, array("getEditData", "update"))) {
            $op = "getEditData";
        }

        if ($op == "getEditData") {
            if (empty($repid)) {
                $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("default/index"));
            }

            $report = Report::model()->fetchByPk($repid);
            $reportType = ReportType::model()->fetchByPk($report["typeid"]);

            if (empty($report)) {
                $this->error(Ibos::lang("No data found", "error"), $this->createUrl("default/index"));
            }

            if ($report["uid"] != $uid) {
                $this->error(Ibos::lang("Request tainting", "error"), $this->createUrl("default/index"));
            }

            $upUid = UserUtil::getSupUid($uid);
            $record = ReportRecord::model()->fetchAllRecordByRep($report);
            $attachs = array();

            if (!empty($report["attachmentid"])) {
                $attachs = AttachUtil::getAttach($report["attachmentid"]);
            }

            $params = array("report" => $report, "reportType" => $reportType, "upUid" => $upUid, "preAndNextRep" => Report::model()->fetchPreAndNextRep($report), "orgPlanList" => $record["orgPlanList"], "outSidePlanList" => $record["outSidePlanList"], "nextPlanList" => $record["nextPlanList"], "attachs" => $attachs, "uploadConfig" => AttachUtil::getUploadConfig(), "isInstallCalendar" => ModuleUtil::getIsEnabled("calendar"));

            if (!empty($params["nextPlanList"])) {
                $firstPlan = $params["nextPlanList"][0];
                $params["nextPlanDate"] = array("planBegindate" => $firstPlan["begindate"], "planEnddate" => $firstPlan["enddate"]);
            } else {
                $params["nextPlanDate"] = array("planBegindate" => 0, "planEnddate" => 0);
            }

            $this->setPageTitle(Ibos::lang("Edit report"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Personal Office")),
                array("name" => Ibos::lang("Work report"), "url" => $this->createUrl("default/index")),
                array("name" => Ibos::lang("Edit report"))
            ));
            $this->render("edit", $params);
        } else {
            $this->{$op}();
        }
    }

    private function update()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $repid = $_POST["repid"];
            $typeid = $_POST["typeid"];
            $uid = Ibos::app()->user->uid;
            $editRepData = array("uid" => $uid, "begindate" => strtotime($_POST["begindate"]), "enddate" => strtotime($_POST["enddate"]), "typeid" => $typeid, "subject" => StringUtil::filterCleanHtml($_POST["subject"]), "content" => $_POST["content"], "attachmentid" => $_POST["attachmentid"], "toid" => implode(",", StringUtil::getId($_POST["toid"])));
            Report::model()->modify($repid, $editRepData);

            if (isset($_POST["orgPlan"])) {
                foreach ($_POST["orgPlan"] as $recordid => $orgPlan) {
                    $updateData = array("process" => intval($orgPlan["process"]), "exedetail" => StringUtil::filterCleanHtml($orgPlan["exedetail"]));

                    if ($updateData["process"] == self::COMPLETE_FALG) {
                        $updateData["flag"] = 1;
                    }

                    ReportRecord::model()->modify($recordid, $updateData);
                }
            }

            ReportRecord::model()->deleteAll("repid=:repid AND planflag!=:planflag", array(":repid" => $repid, ":planflag" => 0));
            $isInstallCalendar = ModuleUtil::getIsEnabled("calendar");

            if ($isInstallCalendar) {
                Calendars::model()->deleteALL("`calendarid` IN(select `cid` from {{calendar_rep_record}} where `repid`=$repid)");
                CalendarRepRecord::model()->deleteAll("repid = $repid");
            }

            if (isset($_POST["outSidePlan"])) {
                $outSidePlan = array_filter($_POST["outSidePlan"], create_function("\$v", "return !empty(\$v[\"content\"]);"));

                if (!empty($outSidePlan)) {
                    ReportRecord::model()->addPlans($outSidePlan, $repid, $editRepData["begindate"], $editRepData["enddate"], $uid, 1);
                }
            }

            if (isset($_POST["nextPlan"])) {
                $nextPlan = array_filter($_POST["nextPlan"], create_function("\$v", "return !empty(\$v[\"content\"]);"));

                if (!empty($nextPlan)) {
                    ReportRecord::model()->addPlans($nextPlan, $repid, strtotime($_POST["planBegindate"]), strtotime($_POST["planEnddate"]), $uid, 2);
                }
            }

            $attachmentid = trim($_POST["attachmentid"], ",");
            AttachUtil::updateAttach($attachmentid);
            $this->success(Ibos::lang("Update succeed", "message"), $this->createUrl("default/index"));
        }
    }

    public function actionDel()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $repids = EnvUtil::getRequest("repids");
            $uid = Ibos::app()->user->uid;

            if (empty($repids)) {
                $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("default/index"));
            }

            $pk = "";

            if (strpos($repids, ",")) {
                $repids = trim($repids, ",");
                $pk = explode(",", $repids);
            } else {
                $pk = array($repids);
            }

            $reports = Report::model()->fetchAllByPk($pk);

            foreach ($reports as $report) {
                if ($report["uid"] != $uid) {
                    $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("You do not have permission to delete the report")));
                }
            }

            $aids = Report::model()->fetchAllAidByRepids($pk);

            if ($aids) {
                AttachUtil::delAttach($aids);
            }

            $isInstallCalendar = ModuleUtil::getIsEnabled("calendar");

            if ($isInstallCalendar) {
                Calendars::model()->deleteALL("`calendarid` IN(select `cid` from {{calendar_rep_record}} where FIND_IN_SET(`repid`, '$repids')) ");
                CalendarRepRecord::model()->deleteAll("repid IN ($repids)");
            }

            $delSuccess = Report::model()->deleteByPk($pk);

            if ($delSuccess) {
                ReportRecord::model()->deleteAll("repid IN('$repids')");
                ReportStats::model()->deleteAll("repid IN ($repids)");
                $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Del succeed", "message")));
            } else {
                $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Del failed", "message")));
            }
        }
    }

    public function actionShow()
    {
        $repid = EnvUtil::getRequest("repid");
        $uid = Ibos::app()->user->uid;

        if (empty($repid)) {
            $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("default/index"));
        }

        $report = Report::model()->fetchByPk($repid);

        if (empty($report)) {
            $this->error(Ibos::lang("File does not exists", "error"), $this->createUrl("default/index"));
        }

        if ($report["uid"] != $uid) {
            $this->error(Ibos::lang("Request tainting", "error"), $this->createUrl("default/index"));
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

        $params = array("report" => $report, "preAndNextRep" => Report::model()->fetchPreAndNextRep($report), "orgPlanList" => $record["orgPlanList"], "outSidePlanList" => $record["outSidePlanList"], "nextPlanList" => $record["nextPlanList"], "attachs" => $attachs, "readers" => $readers, "stampUrl" => $stampUrl, "realname" => User::model()->fetchRealnameByUid($report["uid"]), "departmentName" => Department::model()->fetchDeptNameByUid($report["uid"]), "isInstallCalendar" => ModuleUtil::getIsEnabled("calendar"));

        if (!empty($params["nextPlanList"])) {
            $reportType = ReportType::model()->fetchByPk($report["typeid"]);
            $firstPlan = $params["nextPlanList"][0];
            $params["nextSubject"] = ICReport::handleShowSubject($reportType, $firstPlan["begindate"], $firstPlan["enddate"], 1);
        }

        $this->setPageTitle(Ibos::lang("Show report"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Personal Office")),
            array("name" => Ibos::lang("Work report"), "url" => $this->createUrl("default/index")),
            array("name" => Ibos::lang("Show report"))
        ));
        $this->render("show", $params);
    }
}
