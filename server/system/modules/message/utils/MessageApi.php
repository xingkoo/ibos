<?php

class MessageApi
{
    public function getCommentSourceDesc($sourceUser, $sourceType, $sourceUrl, $data)
    {
        $user = User::model()->fetchByUid($data["uid"]);
        $params = array("{sourceUser}" => $sourceUser, "{sourceType}" => $sourceType, "{user}" => $user["realname"], "{commentContent}" => StringUtil::cutStr($data["sourceContent"], 30), "{sourceUrl}" => $sourceUrl);
        return Ibos::lang("Comment source desc", "message.default", $params);
    }
}
