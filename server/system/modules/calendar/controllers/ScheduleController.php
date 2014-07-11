<?php

class CalendarScheduleController extends CalendarBaseController
{
    public function actionIndex()
    {
        $op = EnvUtil::getRequest("op");

        if ($op == "list") {
            $this->getList();
        } else {
            if (!$this->checkIsMe()) {
                $this->error(Ibos::lang("No permission to view schedule"), $this->createUrl("schedule/index"));
            }

            $sysSetting = Ibos::app()->setting->get("setting");
            $workingtime = explode(",", $sysSetting["calendarworkingtime"]);
            $setting = array("worktimestart" => $workingtime[0], "worktimeend" => $workingtime[1]);
            $data = array("setting" => $setting, "user" => User::model()->fetchByUid($this->uid));
            $this->setPageTitle(Ibos::lang("Personal schedule"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Personal Office")),
                array("name" => Ibos::lang("Calendar arrangement"), "url" => $this->createUrl("schedule/index")),
                array("name" => Ibos::lang("Personal schedule"))
            ));
            $this->render("index", $data);
        }
    }

    public function actionSubSchedule()
    {
        $op = EnvUtil::getRequest("op");

        if ($op == "getsubordinates") {
            $this->getsubordinates();
        } elseif ($op == "list") {
            $this->getList();
        } else {
            $workTime = Ibos::app()->setting->get("setting/calendarworkingtime");
            $workingtime = explode(",", $workTime);
            $setting = array("worktimestart" => $workingtime[0], "worktimeend" => $workingtime[1], "allowAdd" => CalendarUtil::getIsAllowAdd(), "allowEdit" => CalendarUtil::getIsAllowEdit());
            $getUid = EnvUtil::getRequest("uid");

            if (!$getUid) {
                $deptArr = UserUtil::getManagerDeptSubUserByUid($this->uid);

                if (!empty($deptArr)) {
                    $firstDept = reset($deptArr);
                    $uid = $firstDept["user"][0]["uid"];
                } else {
                    $this->error(IBos::lang("You do not subordinate"), $this->createUrl("schedule/index"));
                }
            } else {
                $uid = $getUid;
            }

            if (!UserUtil::checkIsSub(Ibos::app()->user->uid, $uid)) {
                $this->error(Ibos::lang("No permission to view schedule"), $this->createUrl("schedule/index"));
            }

            $data = array("setting" => $setting, "user" => User::model()->fetchByUid($uid), "supUid" => UserUtil::getSupUid($this->uid));
            $this->setPageTitle(Ibos::lang("Subordinate schedule"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Personal Office")),
                array("name" => Ibos::lang("Calendar arrangement"), "url" => $this->createUrl("schedule/index")),
                array("name" => Ibos::lang("Subordinate schedule"))
            ));
            $this->render("subschedule", $data);
        }
    }

    public function actionAdd()
    {
        if (!Ibos::app()->request->getIsAjaxRequest()) {
            $this->error(IBos::lang("Parameters error", "error"), $this->createUrl("schedule/index"));
        }

        if (!$this->checkAddPermission()) {
            $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("No permission to add schedule")));
        }

        $getStartTime = EnvUtil::getRequest("CalendarStartTime");
        $sTime = (empty($getStartTime) ? date("y-m-d h:i", time()) : $getStartTime);
        $getEndTime = EnvUtil::getRequest("CalendarEndTime");
        $eTime = (empty($getEndTime) ? date("y-m-d h:i", time()) : $getEndTime);
        $getTitle = EnvUtil::getRequest("CalendarTitle");
        $title = (empty($getTitle) ? "" : $getTitle);

        if ($this->uid != $this->upuid) {
            $title .= " (" . User::model()->fetchRealnameByUid($this->upuid) . ")";
        }

        $getIsAllDayEvent = EnvUtil::getRequest("IsAllDayEvent");
        $isAllDayEvent = (empty($getIsAllDayEvent) ? 0 : intval($getIsAllDayEvent));
        $getCategory = EnvUtil::getRequest("Category");
        $category = (empty($getCategory) ? -1 : $getCategory);
        $schedule = array("uid" => $this->uid, "subject" => $title, "starttime" => CalendarUtil::js2PhpTime($sTime), "endtime" => CalendarUtil::js2PhpTime($eTime), "isalldayevent" => $isAllDayEvent, "category" => $category, "uptime" => time(), "upuid" => $this->upuid);
        $addId = Calendars::model()->add($schedule, true);

        if ($addId) {
            $ret["isSuccess"] = true;
            $ret["msg"] = "success";
            $ret["data"] = intval($addId);

            if ($this->upuid != $this->uid) {
                $config = array("{sender}" => User::model()->fetchRealnameByUid($this->upuid), "{subject}" => $title, "{url}" => Ibos::app()->urlManager->createUrl("calendar/schedule/index"));
                Notify::model()->sendNotify($this->uid, "add_calendar_message", $config, $this->upuid);
            }
        } else {
            $ret["isSuccess"] = false;
            $ret["msg"] = "fail";
        }

        $this->ajaxReturn($ret);
    }

    public function actionEdit()
    {
        if (!Ibos::app()->request->getIsAjaxRequest()) {
            $this->error(IBos::lang("Parameters error", "error"), $this->createUrl("schedule/index"));
        }

        if (!$this->checkEditPermission()) {
            $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("No permission to edit schedule")));
        }

        $op = EnvUtil::getRequest("op");

        if (empty($op)) {
            $params = $this->getEditData();

            if (0 < $params["calendarid"]) {
                $ret = Calendars::model()->updateSchedule($params["calendarid"], $params["sTime"], $params["eTime"], $params["subject"], $params["category"]);
                $falseid = $this->checkEqLoop($params["calendarid"]);

                if ($falseid) {
                    $ret["cid"] = $falseid;
                }
            } else {
                $masterid = abs($params["calendarid"]);
                $createSubCalendarid = $this->createSubCalendar($masterid, $params["sTimeed"], $params["sTime"], $params["eTime"], $params["subject"], $params["category"]);

                if ($createSubCalendarid) {
                    $ret["isSuccess"] = true;
                    $ret["msg"] = "success";
                    $ret["cid"] = $createSubCalendarid;
                    $ret["instanceType"] = "2";
                } else {
                    $ret["isSuccess"] = false;
                    $ret["msg"] = "fail";
                }
            }
        } else {
            $ret = $this->{$op}();
        }

        $this->ajaxReturn($ret);
    }

    public function actionDel()
    {
        if (!Ibos::app()->request->getIsAjaxRequest()) {
            $this->error(IBos::lang("Parameters error", "error"), $this->createUrl("schedule/index"));
        }

        if (!$this->checkEditPermission()) {
            $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("No permission to del schedule")));
        }

        $getCalendarId = EnvUtil::getRequest("calendarId");
        $calendarId = $this->checkCalendarid($getCalendarId);
        $type = EnvUtil::getRequest("type");
        $allDoptions = array("only", "after", "all");
        $getDoption = EnvUtil::getRequest("doption");
        $doption = (in_array($getDoption, $allDoptions) ? $getDoption : "only");
        $getStartTime = EnvUtil::getRequest("CalendarStartTime");
        $sTime = (empty($getStartTime) ? date("y-m-d h:i", time()) : $getStartTime);

        if ($type == 0) {
            $ret = $this->removeCalendar($calendarId);
        } else {
            if (($type == 1) || ($type == 2)) {
                $calendarId = abs($calendarId);
                $ret = $this->removeLoopCalendar($calendarId, $type, $doption, $sTime);
            } else {
                $ret["isSuccess"] = false;
            }
        }

        $this->ajaxReturn($ret);
    }

    private function setup()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $interval = EnvUtil::getRequest("interval");
            $hiddenDays = EnvUtil::getRequest("hiddenDays");
            $startTime = (isset($interval[0]) ? $interval[0] : "8");
            $endTime = (isset($interval[1]) ? $interval[1] : "18");
            CalendarSetup::model()->updataSetup(Ibos::app()->user->uid, $startTime, $endTime, $hiddenDays);
            $this->ajaxReturn(array("isSuccess" => true));
        } else {
            $alias = "application.modules.calendar.views.schedule.setup";
            $uid = Ibos::app()->user->uid;
            $data["workTime"] = CalendarSetup::model()->getWorkTimeByUid($uid);
            $data["hiddenDays"] = CalendarSetup::model()->getHiddenDaysByUid($uid);
            $view = $this->renderPartial($alias, $data, true);
            $this->ajaxReturn(array("isSuccess" => true, "view" => $view));
        }
    }

    private function finish()
    {
        $params = $this->getEditData();

        if (0 < $params["calendarid"]) {
            $ret = Calendars::model()->updateSchedule($params["calendarid"], $params["sTime"], $params["eTime"], $params["subject"], $params["category"], 1);
        } else {
            $masterid = abs($params["calendarid"]);
            $params["sTimeed"] = $params["sTime"];
            $createSubCalendarid = $this->createSubCalendar($masterid, $params["sTimeed"], $params["sTime"], $params["eTime"], $params["subject"], $params["category"], 1);

            if ($createSubCalendarid) {
                $ret["isSuccess"] = true;
                $ret["cid"] = $createSubCalendarid;
            } else {
                $ret["isSuccess"] = false;
            }
        }

        return $ret;
    }

    private function nofinish()
    {
        $params = $this->getEditData();
        $isSuccess = Calendars::model()->modify($params["calendarid"], array("status" => 0));

        if ($isSuccess) {
            $ret["isSuccess"] = true;
        } else {
            $ret["isSuccess"] = false;
        }

        if ($falseid = $this->checkEqLoop($params["calendarid"])) {
            $ret["cid"] = $falseid;
        }

        return $ret;
    }

    private function getList()
    {
        $st = EnvUtil::getRequest("startDate");
        $et = EnvUtil::getRequest("endDate");
        $ret = $this->listCalendar(strtotime($st), strtotime($et), $this->uid);
        $this->ajaxReturn($ret);
    }

    private function getEditData()
    {
        $getCalendarId = EnvUtil::getRequest("calendarId");
        $getStartTime = EnvUtil::getRequest("CalendarStartTime");
        $getEndTime = EnvUtil::getRequest("CalendarEndTime");
        $getSubject = EnvUtil::getRequest("Subject");
        $getCategory = EnvUtil::getRequest("Category");
        $getStartTimeed = EnvUtil::getRequest("CalendarStartTimeed");
        $params = array("calendarid" => $this->checkCalendarid($getCalendarId), "sTime" => empty($getStartTime) ? date("y-m-d h:i", time()) : $getStartTime, "eTime" => empty($getEndTime) ? date("y-m-d h:i", time()) : $getEndTime, "subject" => empty($getSubject) ? "" : $getSubject, "category" => empty($getCategory) ? -1 : $getCategory, "sTimeed" => empty($getStartTimeed) ? date("y-m-d h:i", time()) : $getStartTimeed);
        return $params;
    }

    private function removeCalendar($calendarid)
    {
        $ret = array();
        $removeSuccess = Calendars::model()->remove($calendarid);

        if ($removeSuccess) {
            $ret["isSuccess"] = true;
            $ret["msg"] = "success";
        } else {
            $ret["isSuccess"] = false;
            $ret["msg"] = "fail";
        }

        return $ret;
    }

    private function checkCalendarid($id)
    {
        if (!empty($id)) {
            if ($id < 0) {
                $id = "-" . substr($id, 11);
            }

            return intval($id);
        } else {
            return 0;
        }
    }

    private function checkEqLoop($calendarid)
    {
        $subrow = Calendars::model()->fetchByPk($calendarid);

        if ($subrow["masterid"] != 0) {
            $mstrow = Calendars::model()->fetchByPk($subrow["masterid"]);

            if ($mstrow) {
                $subject = ($subrow["subject"] == $mstrow["subject"] ? true : false);
                $category = ($subrow["category"] == $mstrow["category"] ? true : false);
                $location = ($subrow["location"] == $mstrow["location"] ? true : false);
                $status = ($subrow["status"] == $mstrow["status"] ? true : false);
                $starttimeed = ($subrow["starttime"] == strtotime($subrow["mastertime"] . " " . date("H:i:s", $mstrow["starttime"])) ? true : false);
                $endtimeed = ($subrow["endtime"] == strtotime($subrow["mastertime"] . " " . date("H:i:s", $mstrow["endtime"])) ? true : false);
                if ($subject && $category && $location && $status && $starttimeed && $endtimeed) {
                    Calendars::model()->remove($calendarid);
                    return "-" . strtotime($subrow["mastertime"] . " " . date("H:i:s", $mstrow["starttime"])) . $subrow["masterid"];
                } else {
                    return false;
                }
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
            $htmlStr = "<ul class=\"mng-trd-list\">";
            $num = 0;

            foreach ($users as $user) {
                if ($num < $item) {
                    $htmlStr .= '<li class="mng-item sub">
                                     <a href="' . $this->createUrl("schedule/subSchedule", array("uid" => $user["uid"])) . '">
                                     <img src="' . $user["avatar_middle"] . '" alt="">' . $user["realname"] . '
                                     <a href="' . $this->createUrl("schedule/subschedule", array("uid" => $user["uid"])) . '" class="o-cal-calendar pull-right mlm" title="日程"></a>
                                     <a href="' . $this->createUrl("task/subtask", array("uid" => $user["uid"])) . '" class="o-cal-todo pull-right" title="任务"></a>
                                     </a>
                                </li>';
                    //$htmlStr .= "<li class=\"mng-item sub\">\r\n                                            <a href=\"" . $this->createUrl("schedule/subSchedule", array("uid" => $user["uid"])) . "\">\r\n                                                <img src=\"" . $user["avatar_middle"] . "\" alt=\"\">\r\n                                                " . $user["realname"] . "\r\n\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"" . $this->createUrl("schedule/subschedule", array("uid" => $user["uid"])) . "\" class=\"o-cal-calendar pull-right mlm\" title=\"日程\"></a>\r\n\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"" . $this->createUrl("task/subtask", array("uid" => $user["uid"])) . "\" class=\"o-cal-todo pull-right\" title=\"任务\"></a>\r\n                                            </a>\r\n\t\t\t\t\t\t\t\t\t\t\t\r\n                                        </li>";
                }

                $num++;
            }

            $subNums = count($users);

            if ($item < $subNums) {
                $htmlStr .= '<li class="mng-item view-all" data-uid="' . $uid . '" sub-nums="' . $subNums . '">
                                <a href="javascript:;">
                                    <i class="o-cal-allsub"></i>' . Ibos::lang("View all subordinate") . '
                                </a>
                            </li>';
                //$htmlStr .= "<li class=\"mng-item view-all\" data-uid=\"" . $uid . "\" sub-nums=\"" . $subNums . "\">\r\n                                                <a href=\"javascript:;\">\r\n                                                   <i class=\"o-cal-allsub\"></i>\r\n                                                    " . Ibos::lang("View all subordinate") . "\r\n                                                </a>\r\n                                            </li>";
            }

            $htmlStr .= "</ul>";
            echo $htmlStr;
        }
    }
}
