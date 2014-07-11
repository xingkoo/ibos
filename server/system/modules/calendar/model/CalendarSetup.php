<?php

class CalendarSetup extends ICModel
{
    public static function model($className = "CalendarSetup")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{calendar_setup}}";
    }

    public function fetchSetupByUid($uid)
    {
        $setupData = $this->fetch(array(
            "condition" => "uid=:uid",
            "params"    => array(":uid" => $uid)
        ));
        return $setupData;
    }

    public function getWorkTimeByUid($uid)
    {
        $setupData = $this->fetchSetupByUid($uid);
        $workTime = explode(",", Ibos::app()->setting->get("setting/calendarworkingtime"));

        if (empty($setupData)) {
            $data["startTime"] = (isset($workTime[0]) ? $workTime[0] : "8");
            $data["endTime"] = (isset($workTime[1]) ? $workTime[1] : "18");
        } else {
            $data["startTime"] = $setupData["mintime"];
            $data["endTime"] = $setupData["maxtime"];
        }

        return $data;
    }

    public function getHiddenDaysByUid($uid)
    {
        $hiddenDays = array();
        $setupData = $this->fetchSetupByUid($uid);
        if (!empty($setupData) && !empty($setupData["hiddendays"])) {
            $hiddenDays = unserialize($setupData["hiddendays"]);
        }

        return $hiddenDays;
    }

    public function updataSetup($uid, $minTime, $maxTime, $hiddenDays)
    {
        $hiddenDays = (empty($hiddenDays) ? "" : serialize($hiddenDays));
        $newSetup = array("mintime" => $minTime, "maxtime" => $maxTime, "hiddendays" => $hiddenDays);
        $setupData = $this->fetchSetupByUid($uid);

        if (empty($setupData)) {
            $newSetup["uid"] = $uid;
            $this->add($newSetup);
        } else {
            $this->updateAll($newSetup, "uid=:uid", array(":uid" => $uid));
        }
    }
}
