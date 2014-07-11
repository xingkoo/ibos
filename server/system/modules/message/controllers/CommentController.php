<?php

class MessageCommentController extends MessageBaseController
{
    public function actionIndex()
    {
        $uid = Ibos::app()->user->uid;
        $type = EnvUtil::getRequest("type");
        $map = array("and");

        if (!in_array($type, array("receive", "sent"))) {
            $type = "receive";
        }

        if ($type == "receive") {
            $con = "touid = '$uid' AND uid != '$uid' AND `isdel` = 0";
        } else {
            $con = "`uid` = $uid AND `isdel` = 0";
        }

        $map[] = $con;
        $count = Comment::model()->count($con . " AND `isdel` = 0");
        $pages = PageUtil::create($count);
        $list = Comment::model()->getCommentList($map, "cid DESC", $pages->getLimit(), $pages->getOffset(), true);
        $data = array("list" => $list, "type" => $type, "pages" => $pages);
        UserData::model()->resetUserCount($uid, "unread_comment", 0);
        $this->setPageTitle(Ibos::lang("Comment"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Message center"), "url" => $this->createUrl("mention/index")),
            array("name" => Ibos::lang("Comment"), "url" => $this->createUrl("comment/index"))
        ));
        $this->render("index", $data);
    }

    public function actionDel()
    {
        $widget = Ibos::app()->getWidgetFactory()->createWidget($this, "IWComment");
        return $widget->delComment();
    }
}
