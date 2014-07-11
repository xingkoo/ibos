<?php

$articleComments = Comment::model()->fetchAllByAttributes(array("module" => "article"));
$cidArr = ConvertUtil::getSubByKey($articleComments, "cid");

if (!empty($articleComments)) {
    $cidStr = implode(",", $cidArr);
    Comment::model()->deleteAll("rowid IN($cidStr)");
    Comment::model()->deleteAllByAttributes(array("module" => "article"));
}

$isInstallVote = ModuleUtil::getIsEnabled("vote");

if ($isInstallVote) {
    $articleVotes = Vote::model()->fetchAllByAttributes(array("relatedmodule" => "article"));
    $voteidArr = ConvertUtil::getSubByKey($articleVotes, "voteid");
    $voteidStr = implode(",", $voteidArr);
    $articleVoteItems = VoteItem::model()->fetchAll("FIND_IN_SET(voteid, '$voteidStr')");
    $itemidArr = ConvertUtil::getSubByKey($articleVoteItems, "itemid");
    $itemidStr = implode(",", $itemidArr);
    VoteItemCount::model()->deleteAll("FIND_IN_SET(itemid, '$itemidStr')");
    VoteItem::model()->deleteAll("FIND_IN_SET(itemid, '$itemidStr')");
    Vote::model()->deleteAllByAttributes(array("relatedmodule" => "article"));
}
