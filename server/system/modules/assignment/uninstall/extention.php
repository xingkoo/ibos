<?php

CacheUtil::set("notifyNode", null);
$assignmentComments = Comment::model()->fetchAllByAttributes(array("module" => "assignment"));
$cidArr = ConvertUtil::getSubByKey($assignmentComments, "cid");

if (!empty($assignmentComments)) {
    $cidStr = implode(",", $cidArr);
    Comment::model()->deleteAll("rowid IN($cidStr)");
    Comment::model()->deleteAllByAttributes(array("module" => "assignment"));
}
