<?php

defined("IN_MODULE_ACTION") || exit("Access Denied");
CacheUtil::update(array("setting", "nav"));
$creditExists = CreditRule::model()->countByAttributes(array("action" => "addresume"));

if (!$creditExists) {
    $data = array("rulename" => "添加简历", "action" => "addresume", "cycletype" => "3", "rewardnum" => "1", "extcredits1" => "0", "extcredits2" => "1", "extcredits3" => "1");
    CreditRule::model()->add($data);
}

Ibos::import("application.modules.recruit.model.ResumeStats", true);
ResumeStats::model()->add(array("new" => 0, "pending" => 0, "interview" => 0, "employ" => 0, "eliminate" => 0, "datetime" => strtotime(date("Y-m-d")) - 86400));
