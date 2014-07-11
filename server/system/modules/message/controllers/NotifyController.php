<?php

class MessageNotifyController extends MessageBaseController
{
    public function actionIndex()
    {
        $uid = Yii::app()->user->uid;
        $pageCount = NotifyMessage::model()->fetchPageCountByUid($uid);
        $pages = PageUtil::create($pageCount);
        $list = NotifyMessage::model()->fetchAllNotifyListByUid($uid, "ctime DESC", $pages->getLimit(), $pages->getOffset());
        $unreadCount = 0;

        if (!empty($list)) {
            foreach ($list as $data) {
                if (array_key_exists("newlist", $data)) {
                    $unreadCount += count($data["newlist"]);
                }
            }
        }

        $data = array("list" => $list, "pages" => $pages, "unreadCount" => $unreadCount, "modules" => Ibos::app()->getEnabledModule());
        $this->setPageTitle(Ibos::lang("Notify"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Message center"), "url" => $this->createUrl("mention/index")),
            array("name" => Ibos::lang("Notify"))
        ));
        $this->render("index", $data);
    }

    public function actionDetail()
    {
        $uid = Yii::app()->user->uid;
        $module = EnvUtil::getRequest("module");
        $pageCount = Yii::app()->db->createCommand()->select("count(id)")->from("{{notify_message}}")->where("uid=$uid AND module = '$module'")->group("module")->queryScalar();
        $pages = PageUtil::create($pageCount);
        $list = NotifyMessage::model()->fetchAllDetailByTimeLine($uid, $module, $pages->getLimit(), $pages->getOffset());
        $data = array("list" => $list, "pages" => $pages);
        NotifyMessage::model()->setReadByModule($uid, $module);
        $this->setPageTitle(Ibos::lang("Detail notify"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Message center"), "url" => $this->createUrl("mention/index")),
            array("name" => Ibos::lang("Notify"), "url" => $this->createUrl("notify/index")),
            array("name" => Ibos::lang("Detail notify"))
        ));
        $this->render("detail", $data);
    }

    public function actionDelete()
    {
        $op = EnvUtil::getRequest("op");

        if (!in_array($op, array("id", "module"))) {
            $op = "id";
        }

        $res = NotifyMessage::model()->deleteNotify(EnvUtil::getRequest("id"), $op);
        $this->ajaxReturn(array("IsSuccess" => !!$res));
    }

    public function actionSetAllRead()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $uid = Yii::app()->user->uid;
            $res = NotifyMessage::model()->setRead($uid);
            $this->ajaxReturn(array("IsSuccess" => !!$res));
        }
    }

    public function actionSetIsRead()
    {
        $module = StringUtil::filterCleanHtml(EnvUtil::getRequest("module"));
        $res = NotifyMessage::model()->setReadByModule(Yii::app()->user->uid, $module);
        $this->ajaxReturn(array("IsSuccess" => !!$res));
    }

    public function actionDigg()
    {
        $this->setPageTitle(Ibos::lang("My digg"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Message center"), "url" => $this->createUrl("mention/index")),
            array("name" => Ibos::lang("Notify"), "url" => $this->createUrl("notify/index")),
            array("name" => Ibos::lang("My digg"))
        ));
        $this->render("digg");
    }
}
