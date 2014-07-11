<?php

Nav::model()->deleteAllByAttributes(array("module" => "workflow"));
Notify::model()->deleteAllByAttributes(array("module" => "workflow"));
NotifyMessage::model()->deleteAllByAttributes(array("module" => "workflow"));
CacheUtil::set("notifyNode", null);
Node::model()->deleteAllByAttributes(array("module" => "workflow"));
NodeRelated::model()->deleteAllByAttributes(array("module" => "workflow"));
AuthItem::model()->deleteAll("name LIKE 'workflow%'");
AuthItemChild::model()->deleteAll("child LIKE 'workflow%'");
MenuCommon::model()->deleteAllByAttributes(array("module" => "workflow"));
$settingFields = "wfremindbefore,wfremindafter,sealfrom";
Setting::model()->deleteAll("FIND_IN_SET(skey,'$settingFields')");
Menu::model()->deleteAllByAttributes(array("m" => "workflow"));
Nav::model()->deleteAllByAttributes(array("module" => "workflow"));
Node::model()->deleteAllByAttributes(array("module" => "workflow"));
NodeRelated::model()->deleteAllByAttributes(array("module" => "workflow"));
AuthItem::model()->deleteAll("name LIKE 'workflow%'");
AuthItemChild::model()->deleteAll("child LIKE 'workflow%'");
$db = Ibos::app()->db->createCommand();
$prefix = $db->getConnection()->tablePrefix;
$tables = $db->setText("SHOW TABLES LIKE '" . str_replace("_", "\_", $prefix . "flow_data_%") . "'")->queryAll(false);

foreach ($tables as $table) {
    $tableName = $table[0];
    !empty($tableName) && $db->dropTable($tableName);
}
