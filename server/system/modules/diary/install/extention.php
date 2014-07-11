<?php

defined("IN_MODULE_ACTION") || exit("Access Denied");
$creditExists = CreditRule::model()->countByAttributes(array("action" => "adddiary"));

if (!$creditExists) {
    $data = array("rulename" => "发表工作日志", "action" => "adddiary", "cycletype" => "3", "rewardnum" => "2", "extcredits1" => "0", "extcredits2" => "2", "extcredits3" => "1");
    CreditRule::model()->add($data);
}

$isInstallCalendar = ModuleUtil::getIsEnabled("calendar");

if ($isInstallCalendar) {
    $sql = "CREATE TABLE IF NOT EXISTS {{calendar_record}} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `cid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '日程的id',
        `rid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '来自日志的计划id',
        `did` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '日志的id',
        PRIMARY KEY (`id`),
        KEY `cid` (`cid`) USING BTREE,
        KEY `rid` (`rid`) USING BTREE,
        KEY `did` (`did`) USING BTREE
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;";
    Yii::app()->db->createCommand($sql)->query();
}