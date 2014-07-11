<?php

CacheUtil::update(array("setting", "nav"));
$docComments = Comment::model()->fetchAllByAttributes(array("module" => "officialdoc"));
$cidArr = ConvertUtil::getSubByKey($docComments, "cid");

if (!empty($docComments)) {
    $cidStr = implode(",", $cidArr);
    Comment::model()->deleteAll("rowid IN($cidStr)");
    Comment::model()->deleteAllByAttributes(array("module" => "officialdoc"));
}
