<?php

defined("ONE_DATE_TIME") || define("ONE_DATE_TIME", 86400);
$todayTime = strtotime(date("Y-m-d"));
$stats = ResumeStats::model()->fetch(array("select" => "datetime", "order" => "datetime DESC"));

if (ONE_DATE_TIME <= $todayTime - $stats["datetime"]) {
    for ($i = $stats["datetime"] + ONE_DATE_TIME; $i < $todayTime; $i += ONE_DATE_TIME) {
        $newCount = Resume::model()->count(sprintf("`entrytime` BETWEEN %d AND %d", $i, $i + ONE_DATE_TIME));
        $resumes = Resume::model()->fetchAll(array("select" => "status", "condition" => sprintf("`statustime` = %d", $i)));
        $status = ConvertUtil::getSubByKey($resumes, "status");
        $ac = array_count_values($status);
        $data = array("new" => $newCount, "pending" => isset($ac["4"]) ? $ac["4"] : 0, "interview" => isset($ac["1"]) ? $ac["1"] : 0, "employ" => isset($ac["2"]) ? $ac["2"] : 0, "eliminate" => isset($ac["5"]) ? $ac["5"] : 0, "datetime" => $i);
        ResumeStats::model()->add($data);
    }
}
