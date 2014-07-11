<?php

class CalendarUtil
{
    public static function php2JsTime($phpDate)
    {
        return "/Date(" . $phpDate . "000)/";
    }

    public static function js2PhpTime($jsdate)
    {
        $ret = strtotime($jsdate);
        return $ret;
    }

    public static function getDateAndWeekDay($dateStr)
    {
        list($year, $month, $day) = explode("-", $dateStr);
        $weekArray = array(Ibos::lang("Day", "date"), Ibos::lang("One", "date"), Ibos::lang("Two", "date"), Ibos::lang("Three", "date"), Ibos::lang("Four", "date"), Ibos::lang("Five", "date"), Ibos::lang("Six", "date"));
        $weekday = $weekArray[date("w", strtotime($dateStr))];
        return array("year" => $year, "month" => $month, "day" => $day, "weekday" => Ibos::lang("Weekday", "date") . $weekday);
    }

    public static function digitalToDay($digitalStr)
    {
        $digitalArr = explode(",", $digitalStr);
        $dayArr = array(1 => Ibos::lang("One", "date"), 2 => Ibos::lang("Two", "date"), 3 => Ibos::lang("Three", "date"), 4 => Ibos::lang("Four", "date"), 5 => Ibos::lang("Five", "date"), 6 => Ibos::lang("Six", "date"), 7 => Ibos::lang("day"));
        $recurringtime = "";

        foreach ($digitalArr as $digital) {
            $recurringtime .= $dayArr[$digital] . ",";
        }

        return rtrim($recurringtime, ",");
    }

    public static function joinCondition($condition1, $condition2)
    {
        if (empty($condition1)) {
            return $condition2;
        } else {
            return $condition1 . " AND " . $condition2;
        }
    }

    public static function getIsAllowAdd()
    {
        return Ibos::app()->setting->get("setting/calendaraddschedule");
    }

    public static function getIsAllowEdit()
    {
        return Ibos::app()->setting->get("setting/calendareditschedule");
    }

    public static function getIsAllowEidtTask()
    {
        return Ibos::app()->setting->get("setting/calendaredittask");
    }

    public static function getSetupStartTime($uid)
    {
        $workTime = CalendarSetup::model()->getWorkTimeByUid($uid);
        return $workTime["startTime"];
    }

    public static function getSetupEndTime($uid)
    {
        $workTime = CalendarSetup::model()->getWorkTimeByUid($uid);
        return $workTime["endTime"];
    }

    public static function getSetupHiddenDays($uid)
    {
        $hiddenDays = CalendarSetup::model()->getHiddenDaysByUid($uid);
        return implode(",", $hiddenDays);
    }
}
