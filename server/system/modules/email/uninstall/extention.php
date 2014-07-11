<?php

$settingFields = "emailexternalmail,emailrecall,emailsystemremind,emailroleallocation,emaildefsize";
Setting::model()->deleteAll("FIND_IN_SET(skey,'$settingFields')");
Setting::model()->updateSettingValueByKey("emailtableids", "a:2:{i:0;i:0;i:1;i:1;}");
Setting::model()->updateSettingValueByKey("emailtable_info", "a:2:{i:0;a:1:{s:4:\"memo\";s:0:\"\";}i:1;a:2:{s:4:\"memo\";s:0:\"\";s:11:\"displayname\";s:12:\"默认归档\";}}");
Nav::model()->deleteAllByAttributes(array("module" => "email"));
Menu::model()->deleteAllByAttributes(array("m" => "email"));
MenuCommon::model()->deleteAllByAttributes(array("module" => "email"));
Notify::model()->deleteAllByAttributes(array("node" => "email_message"));
NotifyMessage::model()->deleteAllByAttributes(array("module" => "email"));
CacheUtil::set("notifyNode", null);
Node::model()->deleteAllByAttributes(array("module" => "email"));
NodeRelated::model()->deleteAllByAttributes(array("module" => "email"));
AuthItem::model()->deleteAll("name LIKE 'email%'");
AuthItemChild::model()->deleteAll("child LIKE 'email%'");
$db = Ibos::app()->db->createCommand();
$prefix = $db->getConnection()->tablePrefix;
$tables = $db->setText("SHOW TABLES LIKE '" . str_replace("_", "\_", $prefix . "email_%") . "'")->queryAll(false);

foreach ($tables as $table) {
    $tableName = $table[0];
    !empty($tableName) && $db->dropTable($tableName);
}
