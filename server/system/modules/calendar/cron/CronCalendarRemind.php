<?php

$row = cron::model()->fetch(array(
    "select"    => "`lastrun`,`nextrun`",
    "condition" => "filename = :filename",
    "params"    => array(":filename" => basename(__FILE__))
 ));
$clist = Calendars::model()->listCalendarByRange($row["lastrun"], $row["nextrun"] + (86400 * 3));

foreach ($clist["events"] as $calendar) {
    if ($calendar["isalldayevent"] == 1) {
        $start_date = date("Y-m-d", $calendar["starttime"]);
        $remind_date_min = $start_date . " 07:59:00";
        $remind_date_max = $start_date . " 10:01:00";
        $remind_time_min = strtotime($remind_date_min);
        $remind_time_max = strtotime($remind_date_max);
        if (($remind_time_min < time()) && (time() < $remind_time_max)) {
            $stime = date("m-d", $calendar["starttime"]);
            $title = $stime . "全天日程";
            $subject = StringUtil::cutStr($calendar["subject"], 20);
            $config = array("{subject}" => $subject, "{url}" => Ibos::app()->urlManager->createUrl("calendar/schedule/index"));
            Notify::model()->sendNotify($calendar["uid"], "calendar_message", $config);
        }
    } elseif ($calendar["starttime"] <= $row["nextrun"]) {
        $stime = date("m-d H:i", $calendar["starttime"]);
        $etime = date("m-d H:i", $calendar["endtime"]);
        $title = $stime . " 至 " . $etime . "日程";
        $subject = StringUtil::cutStr($calendar["subject"], 20);
        $config = array("{subject}" => $subject, "{url}" => Ibos::app()->urlManager->createUrl("calendar/schedule/index"));
        Notify::model()->sendNotify($calendar["uid"], "calendar_message", $config);
    }
}
