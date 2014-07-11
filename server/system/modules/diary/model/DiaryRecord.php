<?php

class DiaryRecord extends ICModel
{
    public static function model($className = "DiaryRecord")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{diary_record}}";
    }

    public function fetchAllByPlantime($plantime, $uid = 0)
    {
        $uid = (empty($uid) ? Ibos::app()->user->uid : $uid);
        $records = DiaryRecord::model()->fetchAll(array(
            "condition" => "plantime=:plantime AND uid=:uid",
            "order"     => "recordid ASC",
            "params"    => array(":plantime" => $plantime, ":uid" => $uid)
        ));
        return $records;
    }

    public function addRecord($plan, $diaryId, $planTime, $uid, $type)
    {
        foreach ($plan as $value) {
            $diaryRecord = array("diaryid" => $diaryId, "content" => htmlspecialchars($value["content"]), "planflag" => $type == "outside" ? 0 : 1, "schedule" => isset($value["schedule"]) ? $value["schedule"] : 0, "plantime" => $planTime, "flag" => isset($value["schedule"]) && ($value["schedule"] == 10) ? 1 : 0, "uid" => $uid, "timeremind" => isset($value["timeremind"]) ? $value["timeremind"] : "");
            $rid = $this->add($diaryRecord, true);
            $isInstallCalendar = ModuleUtil::getIsEnabled("calendar");
            if ($isInstallCalendar && isset($value["timeremind"]) && !empty($value["timeremind"])) {
                $timeArr = explode(",", $value["timeremind"]);
                $st = $planTime + ($timeArr[0] * 60 * 60);
                $et = $planTime + ($timeArr[1] * 60 * 60);
                $calendar = array("subject" => $diaryRecord["content"], "starttime" => $st, "endtime" => $et, "uid" => $uid, "upuid" => $uid, "lock" => 1, "category" => 3, "isfromdiary" => 1);
                $cid = Calendars::model()->add($calendar, true);
                CalendarRecord::model()->add(array("rid" => $rid, "cid" => $cid, "did" => $diaryId));
            }
        }
    }
}
