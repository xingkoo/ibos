<?php

CacheUtil::set("notifyNode", null);
$reportComments = Comment::model()->fetchAllByAttributes(array("module" => "report"));
$cidArr = ConvertUtil::getSubByKey($reportComments, "cid");

if (!empty($reportComments)) {
    $cidStr = implode(",", $cidArr);
    Comment::model()->deleteAll("rowid IN($cidStr)");
    Comment::model()->deleteAllByAttributes(array("module" => "report"));
}
