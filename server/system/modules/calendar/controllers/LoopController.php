<?php

class CalendarLoopController extends CalendarBaseController
{
    public function init()
    {
        parent::init();

        if (!$this->checkIsMe()) {
            $this->error(Ibos::lang("No permission to view loop"), $this->createUrl("loop/index"));
        }
    }

    public function actionIndex()
    {
        $loopList = Calendars::model()->fetchLoopsAndPage("uid=" . $this->uid . " AND instancetype = 1");
        $datas = $loopList["datas"];
        $loops = $this->handleLoops($datas);
        $params = array("pages" => $loopList["pages"], "loopList" => $loops);
        $this->setPageTitle(Ibos::lang("Periodic affairs"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Personal Office")),
            array("name" => Ibos::lang("Calendar arrangement"), "url" => $this->createUrl("schedule/index")),
            array("name" => Ibos::lang("Periodic affairs"))
        ));
        $this->render("index", $params);
    }

    private function handleLoops($loops)
    {
        if (!empty($loops)) {
            foreach ($loops as $k => $v) {
                $loops[$k]["subject"] = StringUtil::cutStr($v["subject"], 12);
                $loops[$k]["uptime"] = date("Y-m-d H:i", $v["uptime"]);
                $time = date("H:i", $v["starttime"]) . "至" . date("H:i", $v["endtime"]);

                switch ($v["recurringtype"]) {
                    case "week":
                        $recurringtime = CalendarUtil::digitalToDay($v["recurringtime"]);
                        $loops[$k]["cycle"] = "每周" . $recurringtime . " " . $time;
                        break;

                    case "month":
                        $loops[$k]["cycle"] = "每月" . $v["recurringtime"] . "号 " . $time;
                        break;

                    case "year":
                        $monthDay = explode("-", $v["recurringtime"]);
                        $loops[$k]["cycle"] = "每年" . $monthDay[0] . "月" . $monthDay[1] . "号 " . $time;
                        break;
                }
            }
        }

        return $loops;
    }

    public function actionAdd()
    {
        $data = $this->beforeSave();
        $insertId = Calendars::model()->add($data, true);

        if ($insertId) {
            $loop = Calendars::model()->fetchByPk($insertId);
            $retTemp = $this->handleLoops(array($loop));
            $ret = $retTemp[0];
            $ret["isSuccess"] = true;
        } else {
            $ret["isSuccess"] = false;
        }

        $this->ajaxReturn($ret);
    }

    public function actionEdit()
    {
        $op = EnvUtil::getRequest("op");
        $editCalendarid = EnvUtil::getRequest("editCalendarid");

        if (empty($editCalendarid)) {
            $this->error(Ibos::lang("Parameters error", "error"));
        }

        if ($op == "geteditdata") {
            $editData = Calendars::model()->fetchEditLoop($editCalendarid);
            $this->ajaxReturn($editData);
        } else {
            $data = $this->beforeSave();
            $editSuccess = Calendars::model()->modify($editCalendarid, $data);

            if ($editSuccess) {
                $retTemp = $this->handleLoops(array($data));
                $ret = $retTemp[0];
            }

            $ret["isSuccess"] = $editSuccess;
            $this->ajaxReturn($ret);
        }
    }

    public function actionDel()
    {
        if (!Yii::app()->request->getIsAjaxRequest()) {
            $this->error(IBos::lang("Parameters error", "error"), $this->createUrl("schedule/index"));
        }

        $delCalendarid = EnvUtil::getRequest("delCalendarid");

        if (empty($delCalendarid)) {
            $this->error(Ibos::lang("Parameters error", "error"));
        }

        $delArr = explode(",", $delCalendarid);

        foreach ($delArr as $calendarid) {
            Calendars::model()->remove($calendarid);
        }

        $ret["isSuccess"] = true;
        $this->ajaxReturn($ret);
    }

    private function beforeSave()
    {
        if (!Yii::app()->request->getIsAjaxRequest()) {
            $this->error(IBos::lang("Parameters error", "error"), $this->createUrl("schedule/index"));
        }

        $subject = EnvUtil::getRequest("subject");
        $starttime = EnvUtil::getRequest("starttime");
        $endtime = EnvUtil::getRequest("endtimes");
        $category = EnvUtil::getRequest("category");
        $getSetday = EnvUtil::getRequest("setday");
        $setday = (empty($getSetday) ? date("Y-m-h") : $getSetday);
        $reply = EnvUtil::getRequest("reply");
        $getRBegin = EnvUtil::getRequest("recurringbegin");
        $rBegin = (empty($getRBegin) ? time() : strtotime($getRBegin));
        $getREnd = EnvUtil::getRequest("recurringend");
        $rEnd = (empty($getREnd) ? 0 : strtotime($getREnd));
        $rType = EnvUtil::getRequest("recurringtype");
        $data = array("uid" => $this->uid, "subject" => empty($subject) ? "无标题的活动" : $subject, "uptime" => time(), "starttime" => empty($starttime) ? time() : strtotime($setday . " " . $starttime), "endtime" => empty($endtime) ? strtotime($setday . " 23:59:59") : strtotime($setday . " " . $endtime), "category" => empty($category) ? "-1" : $category, "upuid" => $this->uid);

        if ($data["endtime"] < $data["starttime"]) {
            $bigtime = $data["starttime"];
            $data["starttime"] = $data["endtime"];
            $data["endtime"] = $bigtime;
        }

        if ($reply == "true") {
            $data["instancetype"] = "1";
            $data["recurringbegin"] = $rBegin;
            $data["recurringend"] = $rEnd;
            if (($data["recurringend"] < $data["recurringbegin"]) && ($data["recurringend"] != 0)) {
                $bigtime = $data["recurringbegin"];
                $data["recurringbegin"] = $data["recurringend"];
                $data["recurringend"] = $bigtime;
            }

            $data["recurringtype"] = $rType;

            switch ($data["recurringtype"]) {
                case "week":
                    $getWeekbox = EnvUtil::getRequest("weekbox");
                    $weekbox = (empty($getWeekbox) ? "1,2,3,4,5,6,7" : $getWeekbox);
                    $data["recurringtime"] = $weekbox;
                    break;

                case "month":
                    $data["recurringtime"] = EnvUtil::getRequest("month");
                    break;

                case "year":
                    $data["recurringtime"] = EnvUtil::getRequest("year");
                    break;
            }
        }

        return $data;
    }
}
