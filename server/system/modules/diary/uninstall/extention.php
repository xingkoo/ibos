<?php

CacheUtil::set("notifyNode", null);
$diaryComments = Comment::model()->fetchAllByAttributes(array("module" => "diary"));
$cidArr = ConvertUtil::getSubByKey($diaryComments, "cid");

if (!empty($diaryComments)) {
    $cidStr = implode(",", $cidArr);
    Comment::model()->deleteAll("rowid IN($cidStr)");
    Comment::model()->deleteAllByAttributes(array("module" => "diary"));
}
