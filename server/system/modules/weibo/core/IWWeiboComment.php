<?php

class IWWeiboComment extends IWComment
{
    public function init()
    {
        $var = array();
        $var["cancomment"] = 1;
        $var["canrepost"] = 1;
        $var["cancomment_old"] = 1;
        $var["showlist"] = 0;
        $var["tpl"] = "application.modules.weibo.views.comment.loadcomment";
        $var["module"] = "weibo";
        $var["table"] = "feed";
        $var["limit"] = 10;
        $var["order"] = "cid DESC";
        $var["inAjax"] = 0;
        $attr = $this->getAttributes();
        if (empty($attr) && EnvUtil::submitCheck("formhash")) {
            $attr["moduleuid"] = intval($_POST["moduleuid"]);
            $attr["rowid"] = intval($_POST["rowid"]);
            $attr["module_rowid"] = intval($_POST["module_rowid"]);
            $attr["module_table"] = StringUtil::filterCleanHtml($_POST["module_table"]);
            $attr["inAjax"] = intval($_POST["inAjax"]);
            $attr["showlist"] = intval($_POST["showlist"]);
            $attr["cancomment"] = intval($_POST["cancomment"]);
            $attr["cancomment_old"] = intval($_POST["cancomment_old"]);
            $attr["module"] = StringUtil::filterCleanHtml($_POST["module"]);
            $attr["table"] = StringUtil::filterCleanHtml($_POST["table"]);
            $attr["canrepost"] = intval($_POST["canrepost"]);
        }

        is_array($attr) && ($var = array_merge($var, $attr));
        $var["moduleuid"] = intval($var["moduleuid"]);
        $var["rowid"] = intval($var["rowid"]);
        if (($var["table"] == "feed") && (Ibos::app()->user->uid != $var["moduleuid"])) {
            $sourceInfo = Feed::model()->get($var["rowid"]);
            $var["feedtype"] = $sourceInfo["type"];
            $moduleRowData = Feed::model()->get(intval($var["module_rowid"]));
            $var["user_info"] = $moduleRowData["user_info"];
        }

        $this->setAttributes($var);
    }

    public function run()
    {
        $attr = $this->getAttributes();

        if ($attr["showlist"] == 1) {
            $attr["list"] = $this->fetchCommentList();
        }

        $content = $this->render($attr["tpl"], $attr);
        $ajax = $attr["inAjax"];
        unset($attr);
        $return = array("isSuccess" => true, "data" => $content);
        return $ajax == 1 ? CJSON::encode($return) : $return["data"];
    }

    public function fetchCommentList()
    {
        $count = $this->getCommentCount();
        $limit = $this->getAttributes("limit");
        $pages = PageUtil::create($count, $limit);
        $this->setAttributes(array("offset" => $pages->getOffset(), "limit" => $pages->getLimit()));
        $var = array("list" => $this->getCommentList(), "lang" => Ibos::getLangSources(array("message.default")), "count" => $count, "limit" => $limit, "rowid" => $this->getAttributes("rowid"), "moduleuid" => $this->getAttributes("moduleuid"), "showlist" => $this->getAttributes("showlist"), "pages" => $pages);
        $content = $this->render("application.modules.weibo.views.comment.loadreply", $var, true);
        return $content;
    }

    public function addComment()
    {
        $this->setParseView("comment", self::REPLY_PARSE_VIEW, "parse");
        return parent::addComment();
    }

    protected function afterAdd($data, $sourceInfo)
    {
        $lessUids = array();

        if (!empty($data["touid"])) {
            $lessUids[] = $data["touid"];
        }

        if (isset($data["sharefeed"]) && (intval($data["sharefeed"]) == 1)) {
            $this->updateToWeibo($data, $sourceInfo, $lessUids);
        }

        if (isset($data["comment"]) && (intval($data["comment"]) == 1)) {
            $this->updateToComment($data, $sourceInfo, $lessUids);
        }
    }

    private function updateToWeibo($data, $sourceInfo, $lessUids)
    {
        $commentInfo = Source::getSourceInfo($data["table"], $data["rowid"], false, $data["module"]);
        $oldInfo = (isset($commentInfo["sourceInfo"]) ? $commentInfo["sourceInfo"] : $commentInfo);
        $arr = array("post", "postimage");
        $scream = "";

        if (!in_array($sourceInfo["type"], $arr)) {
            $scream = "//@" . $commentInfo["source_user_info"]["realname"] . "：" . $commentInfo["source_content"];
        }

        if (!empty($data["tocid"])) {
            $replyInfo = Comment::model()->getCommentInfo($data["tocid"], false);
            $replyScream = "//@" . $replyInfo["user_info"]["realname"] . " ：";
            $data["content"] .= $replyScream . $replyInfo["content"];
        }

        $s["body"] = $data["content"] . $scream;
        $s["curid"] = null;
        $s["sid"] = $oldInfo["source_id"];
        $s["module"] = $oldInfo["module"];
        $s["type"] = $oldInfo["source_table"];
        $s["comment"] = 1;
        $s["comment_touid"] = $data["moduleuid"];
        if (($sourceInfo["type"] == "post") && empty($data["touid"])) {
            $lessUids[] = Ibos::app()->user->uid;
        }

        Feed::model()->shareFeed($s, "comment", $lessUids);
        UserUtil::updateCreditByAction("forwardedweibo", Ibos::app()->user->uid);
    }

    private function updateToComment($data, $sourceInfo, $lessUids)
    {
        $commentInfo = Source::getSourceInfo($data["module_table"], $data["module_rowid"], false, $data["module"]);
        $oldInfo = (isset($commentInfo["sourceInfo"]) ? $commentInfo["sourceInfo"] : $commentInfo);
        $c["module"] = $data["module"];
        $c["table"] = "feed";
        $c["moduleuid"] = (!empty($oldInfo["source_user_info"]["uid"]) ? $oldInfo["source_user_info"]["uid"] : $oldInfo["uid"]);
        $c["content"] = $data["content"];
        $c["rowid"] = (!empty($oldInfo["sourceInfo"]) ? $oldInfo["sourceInfo"]["source_id"] : $oldInfo["source_id"]);

        if ($data["module"]) {
            $c["rowid"] = $oldInfo["feedid"];
        }

        $c["from"] = EnvUtil::getVisitorClient();
        Comment::model()->addComment($c, false, false, $lessUids);
    }
}
