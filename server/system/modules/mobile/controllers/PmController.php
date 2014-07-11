<?php

class MobilePmController extends MobileBaseController
{
    public function actionIndex()
    {
    }

    public function actionList()
    {
        $uid = Yii::app()->user->uid;
        $unreadCount = MessageContent::model()->countUnreadList($uid);
        $pageCount = MessageContent::model()->countMessageListByUid($uid, array(MessageContent::ONE_ON_ONE_CHAT, MessageContent::MULTIPLAYER_CHAT));
        $pages = PageUtil::create($pageCount);
        $list = MessageContent::model()->fetchAllMessageListByUid($uid, array(MessageContent::ONE_ON_ONE_CHAT, MessageContent::MULTIPLAYER_CHAT), $pages->getLimit(), $pages->getOffset());
        $data = array("datas" => $list, "pages" => $pages, "unreadCount" => $unreadCount);
        $this->ajaxReturn($data, "JSONP");
    }

    public function actionShow()
    {
        $message = MessageContent::model()->fetchAllMessageByListId(EnvUtil::getRequest("id"), Yii::app()->user->uid, intval(EnvUtil::getRequest("sinceid")), intval(EnvUtil::getRequest("maxid")), 10);
        $message["data"] = array_reverse($message["data"]);

        foreach ($message["data"] as $key => $value) {
            $tmpuser = User::model()->fetchByUid($value["fromuid"]);
            $message["data"][$key]["fromrealname"] = $tmpuser["realname"];
            $message["data"][$key]["avatar_small"] = $tmpuser["avatar_small"];
            unset($tmpuser);
        }

        $this->ajaxReturn($message, "JSONP");
    }

    public function actionSend()
    {
        $content = StringUtil::filterCleanHtml($_GET["content"]);
        $id = intval(isset($_GET["id"]) ? $_GET["id"] : 0);
        $touid = intval(isset($_GET["touid"]) ? $_GET["touid"] : 0);
        if (!$id && $touid) {
            $data = array("content" => $content, "touid" => $touid, "type" => 1);
            $res = MessageContent::model()->postMessage($data, Yii::app()->user->uid);
            $message = array("listid" => $res, "IsSuccess" => true);
        } else {
            $res = MessageContent::model()->replyMessage($id, $content, Yii::app()->user->uid);

            if ($res) {
                $message = array("IsSuccess" => true, "data" => Ibos::lang("Private message send success"));
            } else {
                $message = array("IsSuccess" => false, "data" => Ibos::lang("Private message send fail"));
            }
        }

        $this->ajaxReturn($message, "JSONP");
    }

    public function actionPostimg()
    {
        $upload = FileUtil::getUpload($_FILES["pmimage"], "mobile");

        if (!$upload->save()) {
            echo "出错了";
        } else {
            $info = $upload->getAttach();
            $file = FileUtil::getAttachUrl() . "/" . $info["type"] . "/" . $info["attachment"];
            $fileUrl = FileUtil::fileName($file);
            $filePath = FileUtil::getAttachUrl() . "/" . $info["type"] . "/" . $info["attachdir"];
            $filename = "tumb_" . $info["attachname"];

            if (LOCAL) {
                FileUtil::makeDirs($filePath . dirname($filename));
            }

            FileUtil::createFile($filePath . $filename, "");
            Yii::import("ext.ThinkImage.ThinkImage", true);
            $imgObj = new ThinkImage(THINKIMAGE_GD);
            $imgObj->open($fileUrl)->thumb(180, 180, 1)->save($filePath . $filename);
            $content = "<a href='" . $fileUrl . "'><img src='" . $filePath . $filename . "' /></a>";
            $id = intval(isset($_POST["pmid"]) ? $_POST["pmid"] : 0);
            $touid = intval(isset($_POST["pmtouid"]) ? $_POST["touid"] : 0);
            if (!$id && $touid) {
                $data = array("content" => $content, "touid" => $touid, "type" => 1);
                $res = MessageContent::model()->postMessage($data, Yii::app()->user->uid);
                $message = array("listid" => $res, "IsSuccess" => true);
            } else {
                $res = MessageContent::model()->replyMessage($id, $content, Yii::app()->user->uid);

                if ($res) {
                    $message = array("IsSuccess" => true, "data" => Ibos::lang("Private message send success"));
                } else {
                    $message = array("IsSuccess" => false, "data" => Ibos::lang("Private message send fail"));
                }
            }

            $this->ajaxReturn($message, "JSONP");
        }
    }
}
