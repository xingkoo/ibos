<?php

class IWArticleComment extends IWComment
{
    public function init()
    {
        $var["loadmore"] = EnvUtil::getRequest("loadmore");
        $var["inAjax"] = intval(EnvUtil::getRequest("inajax"));
        $var["module"] = $this->getModule();
        $var["assetUrl"] = Ibos::app()->assetManager->getAssetsUrl("message");
        $var["getUrl"] = Ibos::app()->urlManager->createUrl("article/comment/getcommentlist");
        $var["addUrl"] = Ibos::app()->urlManager->createUrl("article/comment/addcomment");
        $var["delUrl"] = Ibos::app()->urlManager->createUrl("article/comment/delcomment");
        $this->setAttributes($var);
    }

    public function run()
    {
        $attr = $this->getAttributes();
        $map = array("module" => $this->getModule(), "table" => $this->getTable(), "rowid" => $attr["rowid"], "isdel" => 0);
        $attr["count"] = Comment::model()->countByAttributes($map);
        $list = $this->getCommentList();
        $isAdministrator = Ibos::app()->user->isadministrator;
        $uid = Ibos::app()->user->uid;

        foreach ($list as &$cm) {
            $cm["isCommentDel"] = $isAdministrator || ($uid === $cm["uid"]);
            $cm["replys"] = intval(Comment::model()->countByAttributes(array("module" => "message", "table" => "comment", "rowid" => $cm["cid"], "isdel" => 0)));
        }

        $attr["comments"] = $list;
        $attr["lang"] = Ibos::getLangSources(array("message.default"));
        $content = $this->render($this->getParseView("comment"), $attr, true);
        $ajax = $attr["inAjax"];
        $count = $attr["count"];
        unset($attr);
        $return = array("isSuccess" => true, "data" => $content, "count" => $count);

        if ($ajax == 1) {
            $this->getOwner()->ajaxReturn($return);
        } else {
            echo $return["data"];
        }
    }

    public function fetchCommentList()
    {
        $type = $this->getAttributes("type");
        $this->setAttributes(array("inAjax" => 1, "loadmore" => EnvUtil::getRequest("loadmore")));

        if ($type == "reply") {
            $this->setParseView("comment", self::REPLY_LIST_VIEW);
        } else {
            $this->setParseView("comment", self::COMMENT_LIST_VIEW);
        }

        return $this->run();
    }

    protected function afterAdd($data, $sourceInfo)
    {
        if (isset($data["type"])) {
            if ($data["type"] == "reply") {
                $this->setParseView("comment", self::REPLY_PARSE_VIEW, "parse");
            } else {
                $this->setParseView("comment", self::COMMENT_PARSE_VIEW, "parse");
            }
        }
    }
}
