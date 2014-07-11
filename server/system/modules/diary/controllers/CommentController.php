<?php

class DiaryCommentController extends ICController
{
    public function actionGetCommentList()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $module = StringUtil::filterCleanHtml($_POST["module"]);
            $table = StringUtil::filterCleanHtml($_POST["table"]);
            $limit = EnvUtil::getRequest("limit");
            $offset = EnvUtil::getRequest("offset");
            $rowid = intval($_POST["rowid"]);
            $type = EnvUtil::getRequest("type");
            $properties = array(
                "module"     => $module,
                "table"      => $table,
                "attributes" => array("rowid" => $rowid, "limit" => $limit ? intval($limit) : 10, "offset" => $offset ? intval($offset) : 0, "type" => $type)
            );
            $widget = Ibos::app()->getWidgetFactory()->createWidget($this, "IWDiaryComment", $properties);
            $list = $widget->fetchCommentList();
            $this->ajaxReturn(array("isSuccess" => true, "data" => $list));
        }
    }

    public function actionAddComment()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $widget = Ibos::app()->getWidgetFactory()->createWidget($this, "IWDiaryComment");
            return $widget->addComment();
        }
    }

    public function actionDelComment()
    {
        $widget = Ibos::app()->getWidgetFactory()->createWidget($this, "IWDiaryComment");
        return $widget->delComment();
    }
}
