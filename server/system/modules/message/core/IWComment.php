<?php

class IWComment extends CWidget
{
    const SOURCE_TABLE = "Feed";
    const REPLY_LIST_VIEW = "application.modules.message.views.comment.loadReply";
    const COMMENT_LIST_VIEW = "application.modules.message.views.comment.loadComment";
    const COMMENT_PARSE_VIEW = "application.modules.message.views.comment.parseComment";
    const REPLY_PARSE_VIEW = "application.modules.message.views.comment.parseReply";

    /**
     * 当前评论对象所指向的模块
     * @var string 
     */
    private $_module;
    /**
     * 当前评论对象所指向的表名
     * @var string 
     */
    private $_table;
    /**
     * 当前评论对象的其他属性
     * @var array 
     */
    private $_attributes = array();
    /**
     * 解析视图时的默认视图
     * @var array 
     */
    private $_views = array(
        "list"  => array("comment" => self::COMMENT_LIST_VIEW, "reply" => self::REPLY_LIST_VIEW),
        "parse" => array("comment" => self::COMMENT_PARSE_VIEW, "reply" => self::REPLY_PARSE_VIEW)
        );

    public function setModule($moduleName = "")
    {
        $this->_module = StringUtil::filterCleanHtml($moduleName);
    }

    public function getModule()
    {
        if ($this->_module !== null) {
            return $this->_module;
        } else {
            return Ibos::getCurrentModuleName();
        }
    }

    public function setTable($tableName = "")
    {
        $this->_table = StringUtil::filterCleanHtml($tableName);
    }

    public function getTable()
    {
        if ($this->_table !== null) {
            return $this->_table;
        } else {
            return self::SOURCE_TABLE;
        }
    }

    public function setAttributes($attributes = array())
    {
        foreach ($attributes as $key => $value) {
            $this->_attributes[$key] = $value;
        }
    }

    public function getAttributes($name = null)
    {
        if ($name !== null) {
            if (isset($this->_attributes[$name])) {
                return $this->_attributes[$name];
            } else {
                return null;
            }
        }

        return $this->_attributes;
    }

    public function setParseView($type = "comment", $view = self::COMMENT_PARSE_VIEW, $index = "list")
    {
        if (isset($this->_views[$index]) && isset($this->_views[$index][$type])) {
            $this->_views[$index][$type] = $view;
        }
    }

    public function getParseView($type, $index = "list")
    {
        if (isset($this->_views[$index]) && isset($this->_views[$index][$type])) {
            return $this->_views[$index][$type];
        }
    }

    public function getCommentCount()
    {
        $map = $this->getCommentMap();
        return Comment::model()->countCommentByMap($map);
    }

    public function getCommentList()
    {
        $map = $this->getCommentMap();
        $attr = $this->getAttributes();

        if (!isset($attr["limit"])) {
            $attr["limit"] = 10;
        }

        if (!isset($attr["offset"])) {
            $attr["offset"] = 0;
        }

        if (!isset($attr["order"])) {
            $attr["order"] = "cid DESC";
        }

        $list = Comment::model()->getCommentList($map, $attr["order"], $attr["limit"], $attr["offset"]);
        return $list;
    }

    public function addComment()
    {
        $return = array("isSuccess" => false, "data" => Ibos::lang("Post comment fail", "message"));
        $data = $_POST;

        foreach ($data as $key => $val) {
            $data[$key] = StringUtil::filterCleanHtml($data[$key]);
        }

        $data["uid"] = Ibos::app()->user->uid;
        $data["content"] = StringUtil::filterDangerTag($data["content"]);
        $table = ucfirst($data["table"]);
        $pk = $table::model()->getTableSchema()->primaryKey;
        $sourceInfo = $table::model()->fetch(array("condition" => "`$pk` = {$data["rowid"]}"));

        if (!$sourceInfo) {
            $return["isSuccess"] = false;
            $return["data"] = Ibos::lang("Comment has been delete", "message.default");
            $this->getOwner()->ajaxReturn($return);
        }

        $data["cid"] = Comment::model()->addComment($data);

        if (!empty($data["attachmentid"])) {
            AttachUtil::updateAttach($data["attachmentid"]);
        }

        $data["ctime"] = TIMESTAMP;

        if ($data["cid"]) {
            $this->afterAdd($data, $sourceInfo);
            $return["isSuccess"] = true;
            $return["data"] = $this->parseComment($data);
        }

        $this->getOwner()->ajaxReturn($return);
    }

    public function delComment()
    {
        $cid = intval(EnvUtil::getRequest("cid"));
        $comment = Comment::model()->getCommentInfo($cid);

        if (!$comment) {
            return false;
        }

        if ($comment["uid"] != Ibos::app()->user->uid) {
            if (!Ibos::app()->user->isadministrator) {
                $this->getOwner()->ajaxReturn(array("isSuccess" => false));
            }
        }

        if (!empty($cid)) {
            $this->beforeDelComment($comment, $cid);
            $res = Comment::model()->deleteComment($cid, Ibos::app()->user->uid);

            if ($res) {
                $this->getOwner()->ajaxReturn(array("isSuccess" => true));
            } else {
                $msg = Comment::model()->getError("deletecomment");
                $this->getOwner()->ajaxReturn(array("isSuccess" => false, "msg" => $msg));
            }
        }

        $this->getOwner()->ajaxReturn(array("isSuccess" => false));
    }

    protected function getCommentMap()
    {
        $map = array("and");
        $rowid = $this->getAttributes("rowid");
        $map[] = sprintf("`module` = '%s'", $this->getModule());
        $map[] = sprintf("`table` = '%s'", $this->getTable());
        $map[] = "`rowid` = " . intval($rowid);
        $map[] = "`isdel` = 0";
        return $map;
    }

    protected function parseComment($data)
    {
        $uid = Ibos::app()->user->uid;
        $isAdministrator = Ibos::app()->user->isadministrator;
        $data["userInfo"] = User::model()->fetchByUid($uid);
        $data["content"] = StringUtil::pregHtml($data["content"]);
        $data["content"] = StringUtil::parseHtml($data["content"]);
        $data["lang"] = Ibos::getLangSources(array("message.default"));
        $data["isCommentDel"] = $isAdministrator || ($uid === $data["uid"]);

        if (!empty($data["attachmentid"])) {
            $data["attach"] = AttachUtil::getAttach($data["attachmentid"]);
        }

        return $this->render($this->getParseView("comment", "parse"), $data, true);
    }

    protected function afterAdd($data, $sourceInfo)
    {
        return false;
    }

    protected function beforeDelComment($comment, &$cid)
    {
        if ($comment["table"] == "comment") {
            $childId = Comment::model()->fetchReplyIdByCid($comment["cid"]);
            $cid = array_merge(array($cid), $childId);
        }
    }
}
