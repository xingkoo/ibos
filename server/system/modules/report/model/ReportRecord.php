<?php

class ReportRecord extends ICModel
{
    public static function model($className = "ReportRecord")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{report_record}}";
    }

    public function fetchRecordByRepidAndPlanflag($repid, $planflag)
    {
        $records = $this->fetchAll(array(
            "condition" => "repid = :repid AND planflag = :planflag",
            "params"    => array(":repid" => $repid, ":planflag" => $planflag),
            "order"     => "recordid ASC"
        ));
        return $records;
    }

    public function addPlans($plans, $repid, $begindate, $enddate, $uid, $type, $exedetail = "")
    {
        foreach ($plans as $plan) {
            $remindDate = (empty($plan["reminddate"]) ? 0 : strtotime($plan["reminddate"]));
            $record = array("repid" => $repid, "content" => StringUtil::filterCleanHtml($plan["content"]), "uid" => $uid, "flag" => isset($plan["process"]) && ($plan["process"] == 10) ? 1 : 0, "planflag" => $type, "process" => isset($plan["process"]) ? $plan["process"] : 0, "exedetail" => StringUtil::filterCleanHtml($exedetail), "begindate" => $begindate, "enddate" => $enddate, "reminddate" => $remindDate);
            $rid = $this->add($record, true);
            $isInstallCalendar = ModuleUtil::getIsEnabled("calendar");
            if ($isInstallCalendar && $remindDate) {
                $calendar = array("subject" => $record["content"], "starttime" => $remindDate, "endtime" => $remindDate, "uid" => $uid, "upuid" => $uid, "lock" => 1, "category" => 4, "isalldayevent" => 1);
                $cid = Calendars::model()->add($calendar, true);
                CalendarRepRecord::model()->add(array("rid" => $rid, "cid" => $cid, "repid" => $repid));
            }
        }
    }

    public function fetchAllRecordByRep($report)
    {
        $lastRep = Report::model()->fetchLastRepByRepid($report["repid"], $report["uid"], $report["typeid"]);
        $orgPlanList = array();

        if (!empty($lastRep)) {
            $orgPlanList = $this->fetchRecordByRepidAndPlanflag($lastRep["repid"], 2);
        }

        $outSidePlanList = $this->fetchRecordByRepidAndPlanflag($report["repid"], 1);
        $nextPlanList = $this->fetchRecordByRepidAndPlanflag($report["repid"], 2);
        $record = array("orgPlanList" => $orgPlanList, "outSidePlanList" => $outSidePlanList, "nextPlanList" => $nextPlanList);
        return $record;
    }
}
