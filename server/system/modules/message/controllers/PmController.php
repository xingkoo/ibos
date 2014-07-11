<?php

class MessagePmController extends MessageBaseController
{
    public function actionIndex()
    {
        $uid = Yii::app()->user->uid;
        MessageUser::model()->setMessageIsRead($uid, EnvUtil::getRequest("id"), 1);
        $unreadCount = MessageContent::model()->countUnreadList($uid);
        $pageCount = MessageContent::model()->countMessageListByUid($uid, array(MessageContent::ONE_ON_ONE_CHAT, MessageContent::MULTIPLAYER_CHAT));
        $pages = PageUtil::create($pageCount);
        $list = MessageContent::model()->fetchAllMessageListByUid($uid, array(MessageContent::ONE_ON_ONE_CHAT, MessageContent::MULTIPLAYER_CHAT), $pages->getLimit(), $pages->getOffset());
        $data = array("list" => $list, "pages" => $pages, "unreadCount" => $unreadCount);
        $this->setPageTitle(Ibos::lang("PM"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Message center"), "url" => $this->createUrl("mention/index")),
            array("name" => Ibos::lang("PM"))
        ));
        $this->render("index", $data);
    }

    public function actionDetail()
    {
        $uid = Yii::app()->user->uid;
        $message = MessageContent::model()->isInList(StringUtil::filterCleanHtml(EnvUtil::getRequest("id")), $uid, true);

        if (empty($message)) {
            $this->error(Ibos::lang("Private message not exists"));
        }

        $message["user"] = MessageUser::model()->getMessageUsers(StringUtil::filterCleanHtml(EnvUtil::getRequest("id")), "uid");
        $message["to"] = array();

        foreach ($message["user"] as $v) {
            ($uid != $v["uid"]) && ($message["to"][] = $v);
        }

        MessageUser::model()->setMessageIsRead($uid, EnvUtil::getRequest("id"), 0);
        $message["sinceid"] = MessageContent::model()->getSinceMessageId($message["listid"], $message["messagenum"]);
        $this->setTitle("与" . $message["to"][0]["user"]["realname"] . "的私信对话");
        $this->setPageTitle(Ibos::lang("Detail pm"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Message center"), "url" => $this->createUrl("mention/index")),
            array("name" => Ibos::lang("PM"), "url" => $this->createUrl("pm/index")),
            array("name" => Ibos::lang("Detail pm"))
        ));
        $this->render("detail", array("message" => $message, "type" => intval($_GET["type"])));
    }

    public function actionLoadMessage()
    {
        $message = MessageContent::model()->fetchAllMessageByListId(intval($_POST["listid"]), Yii::app()->user->uid, intval(EnvUtil::getRequest("sinceid")), intval(EnvUtil::getRequest("maxid")));

        foreach ($message["data"] as $key => $value) {
            $message["data"][$key]["fromuser"] = User::model()->fetchByUid($value["fromuid"]);
        }

        $data = array("type" => intval($_POST["type"]), "message" => $message, "uid" => Yii::app()->user->uid);
        $message["data"] = ($message["data"] ? $this->renderPartial("message", $data, true) : "");
        $this->ajaxReturn($message);
    }

    public function actionReply()
    {
        $_POST["replycontent"] = StringUtil::filterCleanHtml($_POST["replycontent"]);
        $_POST["id"] = intval($_POST["id"]);
        if (!$_POST["id"] || empty($_POST["replycontent"])) {
            $this->ajaxReturn(array("IsSuccess" => false, "data" => Ibos::lang("Message content cannot be empty")));
        }

        $res = MessageContent::model()->replyMessage($_POST["id"], $_POST["replycontent"], Yii::app()->user->uid);

        if ($res) {
            $this->ajaxReturn(array("IsSuccess" => true, "data" => Ibos::lang("Private message send success")));
        } else {
            $this->ajaxReturn(array("IsSuccess" => false, "data" => Ibos::lang("Private message send fail")));
        }
    }

    public function actionPost()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $return = array("data" => Ibos::lang("Operation succeed", "message"), "IsSuccess" => true);

            if (empty($_POST["touid"])) {
                $return["data"] = Ibos::lang("Message receiver cannot be empty");
                $return["IsSuccess"] = false;
                $this->ajaxReturn($return);
            }

            if (trim(StringUtil::filterCleanHtml($_POST["content"])) == "") {
                $return["data"] = Ibos::lang("Message content cannot be empty");
                $return["IsSuccess"] = false;
                $this->ajaxReturn($return);
            }

            $_POST["touid"] = implode(",", StringUtil::getUid($_POST["touid"]));

            if (isset($_POST["type"])) {
                !in_array($_POST["type"], array(MessageContent::ONE_ON_ONE_CHAT, MessageContent::MULTIPLAYER_CHAT)) && ($_POST["type"] = null);
            } else {
                $_POST["type"] = null;
            }

            $_POST["content"] = StringUtil::filterDangerTag($_POST["content"]);
            $res = MessageContent::model()->postMessage($_POST, Yii::app()->user->uid);

            if ($res) {
                $this->ajaxReturn($return);
            } else {
                $return["IsSuccess"] = false;
                $return["data"] = MessageContent::model()->getError("message");
                $this->ajaxReturn($return);
            }
        }
    }

    public function actionSetAllRead()
    {
        $res = MessageUser::model()->setMessageAllRead(Yii::app()->user->uid);

        if ($res) {
            $this->ajaxReturn(array("IsSuccess" => true));
        } else {
            $this->ajaxReturn(array("IsSuccess" => false));
        }
    }

    public function actionSetIsRead()
    {
        $res = MessageUser::model()->setMessageIsRead(Yii::app()->user->uid, EnvUtil::getRequest("id"));
        $this->ajaxReturn(array("IsSuccess" => !!$res));
    }

    public function actionDelete()
    {
        $res = MessageUser::model()->deleteMessageByListId(Yii::app()->user->uid, StringUtil::filterCleanHtml(EnvUtil::getRequest("id")));
        $this->ajaxReturn(array("IsSuccess" => !!$res));
    }
}
