<?php

class DiaryDefaultController extends DiaryBaseController
{
    public function actionIndex()
    {
        $op = EnvUtil::getRequest("op");
        $option = (empty($op) ? "default" : $op);
        $routes = array("default", "show", "showdiary", "getreaderlist", "getcommentlist", "getAjaxSidebar");

        if (!in_array($option, $routes)) {
            $this->error(Ibos::lang("Can not find the path"), $this->createUrl("default/index"));
        }

        if ($option == "default") {
            $uid = Ibos::app()->user->uid;

            if (EnvUtil::getRequest("param") == "search") {
                $this->search();
            }

            $this->_condition = DiaryUtil::joinCondition($this->_condition, "uid = $uid");
            $paginationData = Diary::model()->fetchAllByPage($this->_condition);
            $params = array("pagination" => $paginationData["pagination"], "data" => ICDiary::processDefaultListData($paginationData["data"]), "diaryCount" => Diary::model()->count($this->_condition), "commentCount" => Diary::model()->countCommentByReview($uid), "user" => User::model()->fetchByUid($uid), "diaryIsAdd" => Diary::model()->checkDiaryisAdd(strtotime(date("Y-m-d")), $uid));
            $this->setPageTitle(Ibos::lang("My diary"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Personal Office")),
                array("name" => Ibos::lang("Work diary"), "url" => $this->createUrl("default/index")),
                array("name" => Ibos::lang("Diary list"))
            ));
            $this->render("index", $params);
        } else {
            $this->{$option}();
        }
    }

    public function actionAdd()
    {
        $op = EnvUtil::getRequest("op");
        $option = (empty($op) ? "default" : $op);
        $routes = array("default", "save", "planFromSchedule");

        if (!in_array($option, $routes)) {
            $this->error(Ibos::lang("Can not find the path"), $this->createUrl("default/index"));
        }

        if ($option == "default") {
            $todayDate = date("Y-m-d");

            if (array_key_exists("diaryDate", $_GET)) {
                $todayDate = $_GET["diaryDate"];

                if (strtotime(date("Y-m-d")) < strtotime($todayDate)) {
                    $this->error(Ibos::lang("No new permissions"), $this->createUrl("default/index"));
                }
            }

            $todayTime = strtotime($todayDate);
            $uid = Ibos::app()->user->uid;

            if (Diary::model()->checkDiaryisAdd($todayTime, $uid)) {
                $this->error(Ibos::lang("Do not repeat to add"), $this->createUrl("default/index"));
            }

            $diaryRecordList = DiaryRecord::model()->fetchAllByPlantime($todayTime);
            $originalPlanList = $outsidePlanList = array();

            foreach ($diaryRecordList as $diaryRecord) {
                if ($diaryRecord["planflag"] == 1) {
                    $originalPlanList[] = $diaryRecord;
                } else {
                    $outsidePlanList[] = $diaryRecord;
                }
            }

            $dashboardConfig = Ibos::app()->setting->get("setting/diaryconfig");
            $isInstallCalendar = ModuleUtil::getIsEnabled("calendar");
            $workTime = $this->getWorkTime($isInstallCalendar);
            $params = array("originalPlanList" => $originalPlanList, "outsidePlanList" => $outsidePlanList, "dateWeekDay" => DiaryUtil::getDateAndWeekDay($todayDate), "nextDateWeekDay" => DiaryUtil::getDateAndWeekDay(date("Y-m-d", strtotime("+1 day", $todayTime))), "dashboardConfig" => $dashboardConfig, "todayDate" => $todayDate, "uploadConfig" => AttachUtil::getUploadConfig(), "isInstallCalendar" => $isInstallCalendar, "workTime" => $workTime);

            if ($dashboardConfig["sharepersonnel"]) {
                $data = DiaryShare::model()->fetchShareInfoByUid($uid);
                $params["defaultShareList"] = $data["shareInfo"];
                $params["deftoid"] = StringUtil::wrapId($data["deftoid"]);
            }

            $this->setPageTitle(Ibos::lang("Add Diary"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Personal Office")),
                array("name" => Ibos::lang("Work diary"), "url" => $this->createUrl("default/index")),
                array("name" => Ibos::lang("Add Diary"))
            ));
            $this->render("add", $params);
        } else {
            $this->{$option}();
        }
    }

    private function save()
    {
        $uid = Ibos::app()->user->uid;
        $realname = User::model()->fetchRealnameByUid($uid);
        $originalPlan = $planOutside = array();

        if (array_key_exists("originalPlan", $_POST)) {
            $originalPlan = $_POST["originalPlan"];
        }

        if (array_key_exists("planOutside", $_POST)) {
            $planOutside = array_filter($_POST["planOutside"], create_function("\$v", "return !empty(\$v[\"content\"]);"));
        }

        if (!empty($originalPlan)) {
            foreach ($originalPlan as $key => $value) {
                DiaryRecord::model()->modify($key, array("schedule" => $value));
            }
        }

        $date = $_POST["todayDate"] . " " . Ibos::lang("Weekday", "date") . DateTimeUtil::getWeekDay(strtotime($_POST["todayDate"]));
        $shareUidArr = (isset($_POST["shareuid"]) ? StringUtil::getId($_POST["shareuid"]) : array());
        $diary = array("uid" => $uid, "diarytime" => strtotime($_POST["todayDate"]), "nextdiarytime" => strtotime($_POST["plantime"]), "addtime" => TIMESTAMP, "content" => $_POST["diaryContent"], "shareuid" => implode(",", $shareUidArr), "readeruid" => "", "remark" => "", "attention" => "");

        if (!empty($_POST["attachmentid"])) {
            AttachUtil::updateAttach($_POST["attachmentid"]);
        }

        $diary["attachmentid"] = $_POST["attachmentid"];
        $diaryId = Diary::model()->add($diary, true);

        if (!empty($planOutside)) {
            DiaryRecord::model()->addRecord($planOutside, $diaryId, strtotime($_POST["todayDate"]), $uid, "outside");
        }

        $plan = array_filter($_POST["plan"], create_function("\$v", "return !empty(\$v[\"content\"]);"));
        DiaryRecord::model()->addRecord($plan, $diaryId, strtotime($_POST["plantime"]), $uid, "new");
        $wbconf = WbCommonUtil::getSetting(true);
        if (isset($wbconf["wbmovement"]["diary"]) && ($wbconf["wbmovement"]["diary"] == 1)) {
            $supUid = UserUtil::getSupUid($uid);

            if (0 < intval($supUid)) {
                $data = array("title" => Ibos::lang("Feed title", "", array("{subject}" => $realname . " " . $date . " " . Ibos::lang("Work diary"), "{url}" => Ibos::app()->urlManager->createUrl("diary/review/show", array("diaryid" => $diaryId)))), "body" => StringUtil::cutStr($diary["content"], 140), "actdesc" => Ibos::lang("Post diary"), "userid" => $supUid, "deptid" => "", "positionid" => "");
                WbfeedUtil::pushFeed($uid, "diary", "diary", $diaryId, $data);
            }
        }

        UserUtil::updateCreditByAction("adddiary", $uid);
        $upUid = UserUtil::getSupUid($uid);

        if (!empty($upUid)) {
            $config = array("{sender}" => User::model()->fetchRealnameByUid($uid), "{title}" => Ibos::lang("New diary title", "", array("{sub}" => $realname, "{date}" => $date)), "{content}" => $this->renderPartial("remindcontent", array("realname" => $realname, "date" => $date, "lang" => Ibos::getLangSources(), "originalPlan" => array_values($originalPlan), "planOutside" => array_values($planOutside), "content" => StringUtil::cutStr(strip_tags($_POST["diaryContent"]), 200), "plantime" => $_POST["plantime"] . " " . Ibos::lang("Weekday", "date") . DateTimeUtil::getWeekDay(strtotime($_POST["plantime"])), "plan" => array_values($plan)), true), "{url}" => Ibos::app()->urlManager->createUrl("diary/review/show", array("diaryid" => $diaryId)));
            Notify::model()->sendNotify($upUid, "diary_message", $config, $uid);
        }

        $this->success(Ibos::lang("Save succeed", "message"), $this->createUrl("default/index"));
    }

    private function setShare()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $postDeftoid = $_POST["deftoid"];
            $uid = Ibos::app()->user->uid;

            if (empty($postDeftoid)) {
                DiaryShare::model()->delDeftoidByUid($uid);
            } else {
                $deftoid = StringUtil::getId($postDeftoid);
                DiaryShare::model()->addOrUpdateDeftoidByUid($uid, $deftoid);
            }

            $result["isSuccess"] = true;
            $this->ajaxReturn($result);
        }
    }

    public function actionShow()
    {
        $diaryid = EnvUtil::getRequest("diaryid");
        $diaryDate = EnvUtil::getRequest("diarydate");
        if (empty($diaryid) && empty($diaryDate)) {
            $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("default/index"));
        }

        $diary = array();
        $uid = Ibos::app()->user->uid;

        if (!empty($diaryid)) {
            $diary = Diary::model()->fetchByPk($diaryid);
        } else {
            $diary = Diary::model()->fetch("diarytime=:diarytime AND uid=:uid", array(":diarytime" => strtotime($diaryDate), ":uid" => $uid));
        }

        if (empty($diary)) {
            $this->error(Ibos::lang("File does not exists", "error"), $this->createUrl("default/index"));
        }

        if ($diary["uid"] != $uid) {
            $this->error(Ibos::lang("You do not have permission to view the log"), $this->createUrl("default/index"));
        }

        Diary::model()->addReaderuidByPk($diary, $uid);
        $data = Diary::model()->fetchDiaryRecord($diary);
        $data["tomorrowPlanList"] = $this->handelRemindTime($data["tomorrowPlanList"]);
        $params = array("diary" => ICDiary::processDefaultShowData($diary), "prevAndNextPK" => Diary::model()->fetchPrevAndNextPKByPK($diary["diaryid"]), "data" => $data, "isInstallCalendar" => ModuleUtil::getIsEnabled("calendar"));

        if (!empty($diary["attachmentid"])) {
            $params["attach"] = AttachUtil::getAttach($diary["attachmentid"], true, true, false, false, true);
            $params["count"] = 0;
        }

        if (!empty($diary["readeruid"])) {
            $readerArr = explode(",", $diary["readeruid"]);
            $params["readers"] = User::model()->fetchAllByPk($readerArr);
        } else {
            $params["readers"] = "";
        }

        if (!empty($diary["stamp"])) {
            $params["stampUrl"] = Stamp::model()->fetchStampById($diary["stamp"]);
        }

        $this->setPageTitle(Ibos::lang("Show Diary"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Personal Office")),
            array("name" => Ibos::lang("Work diary"), "url" => $this->createUrl("default/index")),
            array("name" => Ibos::lang("Show Diary"))
        ));
        $this->render("show", $params);
    }

    public function actionEdit()
    {
        $op = EnvUtil::getRequest("op");
        $option = (empty($op) ? "default" : $op);
        $routes = array("default", "update", "setShare");

        if (!in_array($option, $routes)) {
            $this->error(Ibos::lang("Can not find the path"), $this->createUrl("default/index"));
        }

        if ($option == "default") {
            $diaryid = intval(EnvUtil::getRequest("diaryid"));

            if (empty($diaryid)) {
                $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("default/index"));
            }

            $diary = Diary::model()->fetchByPk($diaryid);

            if (empty($diary)) {
                $this->error(Ibos::lang("No data found", "error"), $this->createUrl("default/index"));
            }

            if (!ICDiary::checkReadScope(Ibos::app()->user->uid, $diary)) {
                $this->error(Ibos::lang("You do not have permission to edit the log"), $this->createUrl("default/index"));
            }

            $dashboardConfig = Ibos::app()->setting->get("setting/diaryconfig");

            if (!empty($dashboardConfig["lockday"])) {
                $isLock = ($dashboardConfig["lockday"] * 24 * 60 * 60) < (time() - $diary["addtime"]);

                if ($isLock) {
                    $this->error(Ibos::lang("The diary is locked"), $this->createUrl("default/index"));
                }
            }

            $data = Diary::model()->fetchDiaryRecord($diary);
            $isInstallCalendar = ModuleUtil::getIsEnabled("calendar");
            $workTime = $this->getWorkTime($isInstallCalendar);
            $params = array("diary" => ICDiary::processDefaultShowData($diary, $data), "prevAndNextPK" => Diary::model()->fetchPrevAndNextPKByPK($diaryid), "data" => $data, "dashboardConfig" => $dashboardConfig, "uploadConfig" => AttachUtil::getUploadConfig(), "isInstallCalendar" => $isInstallCalendar, "workTime" => $workTime);

            if (!empty($diary["attachmentid"])) {
                $params["attach"] = AttachUtil::getAttach($diary["attachmentid"]);
            }

            if ($dashboardConfig["sharepersonnel"]) {
                $shareData = DiaryShare::model()->fetchShareInfoByUid(Ibos::app()->user->uid);
                $params["defaultShareList"] = $shareData["shareInfo"];
            }

            $this->setPageTitle(Ibos::lang("Edit Diary"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Personal Office")),
                array("name" => Ibos::lang("Work diary"), "url" => $this->createUrl("default/index")),
                array("name" => Ibos::lang("Edit Diary"))
            ));
            $this->render("edit", $params);
        } else {
            $this->{$option}();
        }
    }

    private function update()
    {
        $diaryId = $_POST["diaryid"];
        $diary = Diary::model()->fetchByPk($diaryId);
        $uid = Ibos::app()->user->uid;

        if (!ICDiary::checkReadScope($uid, $diary)) {
            $this->error(Ibos::lang("You do not have permission to edit the log"), $this->createUrl("default/index"));
        }

        if (isset($_POST["originalPlan"])) {
            foreach ($_POST["originalPlan"] as $key => $value) {
                if (isset($value)) {
                    DiaryRecord::model()->modify($key, array("schedule" => $value));
                }
            }
        }

        DiaryRecord::model()->deleteAll("diaryid=:diaryid AND planflag=:planflag", array(":diaryid" => $diaryId, ":planflag" => 0));

        if (!empty($_POST["planOutside"])) {
            $planOutside = array_filter($_POST["planOutside"], create_function("\$v", "return !empty(\$v[\"content\"]);"));
            DiaryRecord::model()->addRecord($planOutside, $diaryId, $_POST["diarytime"], $uid, "outside");
        }

        $attributes = array("content" => $_POST["diaryContent"]);

        if (array_key_exists("shareuid", $_POST)) {
            $shareUidArr = StringUtil::getId($_POST["shareuid"]);
            $attributes["shareuid"] = implode(",", $shareUidArr);
        }

        Diary::model()->modify($diaryId, $attributes);
        $attachmentid = trim($_POST["attachmentid"], ",");
        AttachUtil::updateAttach($attachmentid);
        Diary::model()->modify($diaryId, array("attachmentid" => $attachmentid));
        $isInstallCalendar = ModuleUtil::getIsEnabled("calendar");

        if ($isInstallCalendar) {
            Calendars::model()->deleteALL("`calendarid` IN(select `cid` from {{calendar_record}} where `did`=$diaryId)");
            CalendarRecord::model()->deleteAll("did = $diaryId");
        }

        DiaryRecord::model()->deleteAll("plantime=:plantime AND uid=:uid AND planflag=:planflag", array(":plantime" => strtotime($_POST["plantime"]), ":uid" => $uid, ":planflag" => 1));

        if (!isset($_POST["plan"])) {
            $this->error(Ibos::lang("Please fill out at least one work plan"), $this->createUrl("default/edit", array("diaryid" => $diaryId)));
        }

        $plan = array_filter($_POST["plan"], create_function("\$v", "return !empty(\$v[\"content\"]);"));
        DiaryRecord::model()->addRecord($plan, $diaryId, strtotime($_POST["plantime"]), $uid, "new");
        $this->success(Ibos::lang("Update succeed", "message"), $this->createUrl("default/index"));
    }

    public function actionDel()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $diaryids = EnvUtil::getRequest("diaryids");
            $uid = Ibos::app()->user->uid;

            if (empty($diaryids)) {
                $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Select at least one")));
            }

            $pk = "";

            if (strpos($diaryids, ",")) {
                $diaryids = trim($diaryids, ",");
                $pk = explode(",", $diaryids);
            } else {
                $pk = array($diaryids);
            }

            $diarys = Diary::model()->fetchAllByPk($pk);

            foreach ($diarys as $diary) {
                if (!ICDiary::checkReadScope($uid, $diary)) {
                    $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("You do not have permission to delete the log")));
                }
            }

            $aids = Diary::model()->fetchAllAidByPks($pk);

            if ($aids) {
                AttachUtil::delAttach($aids);
            }

            $isInstallCalendar = ModuleUtil::getIsEnabled("calendar");

            if ($isInstallCalendar) {
                Calendars::model()->deleteALL("`calendarid` IN(select `cid` from {{calendar_record}} where FIND_IN_SET(`did`, '$diaryids')) ");
                CalendarRecord::model()->deleteAll("did IN ($diaryids)");
            }

            Diary::model()->deleteByPk($pk);
            DiaryRecord::model()->deleteAll("diaryid IN ($diaryids)");
            DiaryStats::model()->deleteAll("diaryid IN ($diaryids)");
            $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Del succeed", "message")));
        }
    }

    private function showdiary()
    {
        $diaryid = intval($_GET["diaryid"]);
        $isShowDiarytime = EnvUtil::getRequest("isShowDiarytime");
        $fromController = EnvUtil::getRequest("fromController");
        $uid = Ibos::app()->user->uid;

        if (empty($diaryid)) {
            $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("default/index"));
        }

        $diary = Diary::model()->fetchByPk($diaryid);

        if (empty($diary)) {
            $this->error(Ibos::lang("No data found", "error"), $this->createUrl("default/index"));
        }

        if (!ICDiary::checkScope($uid, $diary)) {
            $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("You do not have permission to view the log")));
        }

        Diary::model()->addReaderuidByPK($diary, $uid);
        $data = Diary::model()->fetchDiaryRecord($diary);
        $data["tomorrowPlanList"] = $this->handelRemindTime($data["tomorrowPlanList"]);
        $attachs = array();

        if (!empty($diary["attachmentid"])) {
            $attachs = AttachUtil::getAttach($diary["attachmentid"], true, true, false, false, true);
        }

        $readers = array();

        if (!empty($diary["readeruid"])) {
            $readerArr = explode(",", $diary["readeruid"]);
            $readers = User::model()->fetchAllByPk($readerArr);
        }

        $stampUrl = "";

        if ($diary["stamp"] != 0) {
            $stamp = Stamp::model()->fetchStampById($diary["stamp"]);
            $stampUrl = FileUtil::fileName(Stamp::STAMP_PATH) . $stamp;
        }

        $diary["diarytime"] = DiaryUtil::getDateAndWeekDay(date("Y-m-d", $diary["diarytime"]));
        $diary["nextdiarytime"] = DiaryUtil::getDateAndWeekDay(date("Y-m-d", $diary["nextdiarytime"]));
        $diary["addtime"] = ConvertUtil::formatDate($diary["addtime"], "u");
        $params = array("lang" => Ibos::getLangSource("diary.default"), "diaryid" => $diaryid, "diary" => $diary, "uid" => $uid, "data" => $data, "attachs" => $attachs, "readers" => $readers, "stampUrl" => $stampUrl, "fromController" => $fromController, "isShowDiarytime" => $isShowDiarytime, "allowComment" => $this->getIsAllowComment($fromController, $uid, $diary));
        $detailAlias = "application.modules.diary.views.detail";
        $detailView = $this->renderPartial($detailAlias, $params, true);
        $this->ajaxReturn(array("data" => $detailView, "isSuccess" => true));
    }

    private function getreaderlist()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $diaryId = EnvUtil::getRequest("diaryid");
            $record = Diary::model()->fetch(array(
            "select"    => "readeruid",
            "condition" => "diaryid=:diaryid",
            "params"    => array(":diaryid" => $diaryId)
            ));
            $readerUids = $record["readeruid"];
            $htmlStr = "<table class=\"pop-table\">";

            if (!empty($readerUids)) {
                $htmlStr .= "<div class=\"da-reviews-avatar\">";
                $readerUidArr = explode(",", trim($readerUids, ","));
                $users = User::model()->fetchAllByUids($readerUidArr);

                foreach ($users as $user) {
                    //$htmlStr .= "<a href=\"" . Ibos::app()->createUrl("user/home/index", array("uid" => $user["uid"])) . "\">\n\t\t\t\t\t\t\t\t<img class=\"img-rounded\" src=\"" . $user["avatar_small"] . "\" title=\"" . $user["realname"] . "\" />\n\t\t\t\t\t\t\t</a>";
                    $htmlStr .= '<a href="' . Ibos::app()->createUrl("user/home/index", array("uid" => $user["uid"])) . '">
                                    <img class="img-rounded" src="' . $user["avatar_small"] . '" title="' . $user["realname"] . '" />
                                </a>';
                }
            } else {
                $htmlStr .= "<div><li align=\"middle\">" . Ibos::lang("Has not reader") . "</li>";
            }

            $htmlStr .= "</div></table>";
            echo $htmlStr;
        }
    }

    private function getcommentlist()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $diaryid = EnvUtil::getRequest("diaryid");
            $records = Comment::model()->fetchAll(array(
            "select"    => array("uid", "content", "ctime"),
            "condition" => "module=:module AND `table`=:table AND rowid=:rowid AND isdel=:isdel ORDER BY ctime DESC LIMIT 0,5",
            "params"    => array(":module" => "diary", ":table" => "diary", ":rowid" => $diaryid, ":isdel" => 0)
            ));
            $htmlStr = "<div class=\"pop-comment\"><ul class=\"pop-comment-list\">";

            if (!empty($records)) {
                foreach ($records as $record) {
                    $record["realname"] = User::model()->fetchRealnameByUid($record["uid"]);
                    $content = StringUtil::cutStr($record["content"], 45);
                    //$htmlStr .= "<li class=\"media\">\n\t\t\t\t\t\t\t\t\t<a href=\"" . Ibos::app()->createUrl("user/home/index", array("uid" => $record["uid"])) . "\" class=\"pop-comment-avatar pull-left\">\n\t\t\t\t\t\t\t\t\t\t<img src=\"avatar.php?uid=" . $record["uid"] . "&size=small&engine=" . ENGINE . "\" title=\"" . $record["realname"] . "\" class=\"img-rounded\"/>\n\t\t\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t\t\t\t<div class=\"media-body\">\n\t\t\t\t\t\t\t\t\t\t<p class=\"pop-comment-body\"><em>" . $record["realname"] . ": </em>" . $content . "</p>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</li>";
                    $htmlStr .= '<li class="media">
                                    <a href="' . Ibos::app()->createUrl("user/home/index", array("uid" => $record["uid"])) . '" class="pop-comment-avatar pull-left">
                                        <img src="avatar.php?uid=' . $record["uid"] . '&size=small&engine=' . ENGINE . '" title="' . $record["realname"] . '" class="img-rounded"/>
                                    </a>
                                    <div class="media-body">
                                        <p class="pop-comment-body"><em>' . $record["realname"] . ': </em>' . $content . '</p>
                                    </div>
                                </li>';
                }
            } else {
                $htmlStr .= '<li align="middle">' . Ibos::lang("Has not comment") . '</li>';
            }

            $htmlStr .= "</ul></div>";
            echo $htmlStr;
        }
    }

    private function planFromSchedule()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $uid = Ibos::app()->user->uid;
            $todayDate = $_GET["todayDate"];
            $st = intval(strtotime($todayDate));
            $et = ($st + (24 * 60 * 60)) - 1;
            $calendars = Calendars::model()->listCalendarByRange($st, $et, $uid);
            $plans = $calendars["events"];

            foreach ($plans as $k => $v) {
                $plans[$k]["schedule"] = ($v["status"] ? self::COMPLETE_FALG : 0);

                if ($v["isfromdiary"]) {
                    unset($plans[$k]);
                }
            }

            $this->ajaxReturn(array_values($plans));
        }
    }

    private function getWorkTime($isInstallCalendar)
    {
        if ($isInstallCalendar) {
            $workingTime = Ibos::app()->setting->get("setting/calendarworkingtime");
            $workingTimeArr = explode(",", $workingTime);
            $start = floor($workingTimeArr[0] - 0.5);
            $end = ceil($workingTimeArr[1] + 0.5);

            if ($start < 0) {
                $start = 0;
            }

            if (24 < $end) {
                $end = 24;
            }

            $workTime["start"] = intval($start);
            $workTime["cell"] = intval(($end - $start) * 2);
        } else {
            $workTime["start"] = 6;
            $workTime["cell"] = 28;
        }

        return $workTime;
    }

    private function handelRemindTime($recordList)
    {
        if (!empty($recordList)) {
            foreach ($recordList as $k => $record) {
                if (!empty($record["timeremind"])) {
                    $timeremind = explode(",", $record["timeremind"]);
                    $timeremindSt = date("H:i", strtotime(date("Y-m-d")) + ($timeremind[0] * 60 * 60));
                    $timeremindEt = date("H:i", strtotime(date("Y-m-d")) + ($timeremind[1] * 60 * 60));
                    $recordList[$k]["timeremind"] = $timeremindSt . "-" . $timeremindEt;
                }
            }
        }

        return $recordList;
    }
}
