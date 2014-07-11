<?php

class CalendarBaseController extends ICController
{
    private $_attributes = array("uid" => 0, "upuid" => 0);

    public function __set($name, $value)
    {
        if (isset($this->_attributes[$name])) {
            $this->_attributes[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    public function __get($name)
    {
        if (isset($this->_attributes[$name])) {
            return $this->_attributes[$name];
        } else {
            parent::__get($name);
        }
    }

    public function init()
    {
        $uid = intval(EnvUtil::getRequest("uid"));
        $this->uid = ($uid ? $uid : Ibos::app()->user->uid);
        $this->upuid = Ibos::app()->user->uid;
        parent::init();
    }

    protected function getSidebar()
    {
        $sidebarAlias = "application.modules.calendar.views.sidebar";
        $params = array("hasSubUid" => UserUtil::hasSubUid(Ibos::app()->user->uid), "lang" => Ibos::getLangSource("calendar.default"));
        $sidebarView = $this->renderPartial($sidebarAlias, $params, true);
        return $sidebarView;
    }

    protected function getSubSidebar()
    {
        $deptArr = UserUtil::getManagerDeptSubUserByUid(Ibos::app()->user->uid);
        $sidebarAlias = "application.modules.calendar.views.subsidebar";
        $sidebarView = $this->renderPartial($sidebarAlias, array("deptArr" => $deptArr), true);
        return $sidebarView;
    }

    protected function listCalendar($st, $et, $uid)
    {
        $curUid = Ibos::app()->user->uid;
        $list["calendar"] = Calendars::model()->listCalendarByRange($st, $et, $uid);
        $allowEdit = CalendarUtil::getIsAllowEdit();
        $tmpret["events"] = array();

        foreach ($list["calendar"]["events"] as $key => $row) {
            $spanday = (date("Y-m-d", $row["starttime"]) < date("Y-m-d", $row["endtime"]) ? 1 : 0);

            if ($row["lock"]) {
                $editAble = 0;
            } else {
                if (($row["uid"] == $curUid) || $allowEdit || ($curUid == $row["upuid"])) {
                    $editAble = 1;
                } else {
                    $editAble = 0;
                }
            }

            $tmpret["events"][] = array("id" => $row["calendarid"], "title" => $row["subject"], "start" => CalendarUtil::php2JsTime($row["starttime"]), "end" => CalendarUtil::php2JsTime($row["endtime"]), "allDay" => $row["isalldayevent"], "acrossDay" => $spanday, "type" => $row["instancetype"], "category" => $row["category"], "editable" => $editAble, "location" => $row["location"], "attends" => "", "status" => $row["status"], "loopId" => $row["masterid"]);
        }

        foreach ($tmpret["events"] as $key => $row) {
            $beginarr[$key] = $row["start"];
        }

        if (!empty($beginarr)) {
            array_multisort($beginarr, SORT_ASC, $tmpret["events"]);
        }

        $ret = $list["calendar"];
        $ret["events"] = $tmpret["events"];
        return $ret;
    }

    protected function getCalendarViewFormat($showDate, $viewType = "week")
    {
        $phpTime = strtotime($showDate);

        switch ($viewType) {
            case "month":
                $sd = mktime(0, 0, 0, date("m", $phpTime), 1, date("Y", $phpTime));
                $ed = mktime(0, 0, 0, date("m", $phpTime) + 1, 1, date("Y", $phpTime)) - 1;
                $st_day = date("N", $sd);
                $ed_day = date("N", $ed);
                $st = $sd - (($st_day - 1) * 24 * 60 * 60);
                $et = $ed + ((7 - $ed_day) * 24 * 60 * 60);
                break;

            case "week":
                $monday = (date("d", $phpTime) - date("N", $phpTime)) + 1;
                $st = mktime(0, 0, 0, date("m", $phpTime), $monday, date("Y", $phpTime));
                $et = mktime(0, 0, -1, date("m", $phpTime), $monday + 7, date("Y", $phpTime));
                break;

            case "day":
                $st = mktime(0, 0, 0, date("m", $phpTime), date("d", $phpTime), date("Y", $phpTime));
                $et = mktime(0, 0, -1, date("m", $phpTime), date("d", $phpTime) + 1, date("Y", $phpTime));
                break;
        }

        $result["st"] = $st;
        $result["et"] = $et;
        return $result;
    }

    protected function createSubCalendar($masterid, $mastertime, $starttime, $endtime, $subject, $category, $status = 0)
    {
        $uid = Yii::app()->user->uid;
        $rows = Calendars::model()->fetchByPk($masterid);
        unset($rows["calendarid"]);
        $rows["masterid"] = $masterid;
        $rows["subject"] = $subject;
        $rows["category"] = $category;
        $rows["mastertime"] = date("Y-m-d", strtotime($mastertime));
        $rows["starttime"] = strtotime($starttime);
        $rows["endtime"] = strtotime($endtime);
        $rows["upaccount"] = $uid;
        $rows["instancetype"] = 2;
        $rows["status"] = $status;
        return Calendars::model()->add($rows, true);
    }

    protected function removeLoopCalendar($id, $type, $doption, $starttime)
    {
        $ret = array();
        $isSuccess = "";

        switch ($type) {
            case "1":
                switch ($doption) {
                    case "only":
                        $sid = $this->createSubCalendar($id, $starttime, $starttime, time(), "", -1, 3);
            
                        if ($sid) {
                            $ret["isSuccess"] = true;
                        } else {
                            $ret["isSuccess"] = false;
                        }
            
                        return $ret;
                    case "after":
                        $endday = explode(" ", $starttime);
                        $endday = strtotime($endday[0]) - (24 * 60 * 60);
                        $isSuccess = Calendars::model()->modify($id, array("recurringend" => $endday));
            
                        if ($isSuccess) {
                            Calendars::model()->deleteAll(array(
                                "condition" => "masterid = :masterid AND starttime > :starttime",
                                "params"    => array(":masterid" => $id, ":starttime" => strtotime($starttime))
                            ));
                        }
            
                        break;
            
                    case "all":
                        $isSuccess = Calendars::model()->remove($id);
            
                        if ($isSuccess) {
                            Calendars::model()->deleteAll(array(
                                "condition" => "masterid = :masterid",
                                "params"    => array(":masterid" => $id)
                            ));
                        }
            
                        break;
                }
            
                break;
            
            case "2":
                $isSuccess = Calendars::model()->modify($id, array("status" => 3));
                break;
        }

        if ($isSuccess) {
            $ret["isSuccess"] = true;
        } else {
            $ret["isSuccess"] = false;
        }

        return $ret;
    }

    protected function checkIsMe()
    {
        if ($this->uid != Ibos::app()->user->uid) {
            return false;
        } else {
            return true;
        }
    }

    protected function checkAddPermission()
    {
        if (!$this->checkIsMe() && (!UserUtil::checkIsSub(Ibos::app()->user->uid, $this->uid) || !CalendarUtil::getIsAllowAdd())) {
            return false;
        } else {
            return true;
        }
    }

    protected function checkEditPermission()
    {
        if (!$this->checkIsMe() && !UserUtil::checkIsSub(Ibos::app()->user->uid, $this->uid)) {
            return false;
        } else {
            return true;
        }
    }

    protected function checkTaskPermission()
    {
        if (!$this->checkIsMe() && (!UserUtil::checkIsSub(Ibos::app()->user->uid, $this->uid) || !CalendarUtil::getIsAllowEidtTask())) {
            return false;
        } else {
            return true;
        }
    }
}
