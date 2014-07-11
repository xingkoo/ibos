<?php

class WeiboCommentController extends WeiboBaseController
{
    public function actionGetCommentList()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $module = StringUtil::filterCleanHtml($_POST["module"]);
            $table = StringUtil::filterCleanHtml($_POST["table"]);
            $rowid = intval($_POST["rowid"]);
            $moduleuid = intval($_POST["moduleuid"]);
            $properties = array(
                "module"     => $module,
                "table"      => $table,
                "attributes" => array("rowid" => $rowid, "limit" => 10, "moduleuid" => $moduleuid)
            );
            $widget = Ibos::app()->getWidgetFactory()->createWidget($this, "IWWeiboComment", $properties);
            $list = $widget->fetchCommentList();
            $this->ajaxReturn(array("isSuccess" => true, "data" => $list));
        }
    }

    public function actionAddComment()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $widget = Ibos::app()->getWidgetFactory()->createWidget($this, "IWWeiboComment");
            return $widget->addComment();
        }
    }

    public function actionDelComment()
    {
        $widget = Ibos::app()->getWidgetFactory()->createWidget($this, "IWWeiboComment");
        return $widget->delComment();
    }
}
