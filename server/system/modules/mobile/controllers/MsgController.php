<?php

class MobileMsgController extends MobileBaseController
{
    public function actionIndex()
    {
        $uid = Ibos::app()->user->uid;
        $list = NotifyMessage::model()->fetchAllNotifyListByUid($uid, "ctime DESC");
        $module = Ibos::app()->getEnabledModule();
        $datas = array();

        if (!empty($list)) {
            $i = 0;

            foreach ($list as $key => $value) {
                $datas[$i] = $value;

                if (array_key_exists("newlist", $value)) {
                    $datas[$i]["unread"] = count($value["newlist"]);
                } else {
                    $datas[$i]["unread"] = 0;
                }

                if (isset($module[$key])) {
                    $datas[$i]["name"] = $module[$key]["name"];
                } else {
                    $datas[$i]["name"] = "";
                }

                $datas[$i]["id"] = $key;
                $i++;
            }
        }

        $this->ajaxReturn($datas, "JSONP");
    }

    public function actionList()
    {
        $uid = Ibos::app()->user->uid;
        $module = $_GET["module"];
        $list = NotifyMessage::model()->fetchAllDetailByTimeLine($uid, $module);
        NotifyMessage::model()->setReadByModule($uid, $module);
        $data = array("datas" => $list);
        $this->ajaxReturn($data, "JSONP");
    }

    public function actionShow()
    {
        $message = MessageContent::model()->fetchAllMessageByListId(EnvUtil::getRequest("id"), Ibos::app()->user->uid, intval(EnvUtil::getRequest("sinceid")), intval(EnvUtil::getRequest("maxid")), 10);
        $message["data"] = array_reverse($message["data"]);

        foreach ($message["data"] as $key => $value) {
            $tmpuser = User::model()->fetchByUid($value["fromuid"]);
            $message["data"][$key]["fromrealname"] = $tmpuser["realname"];
            $message["data"][$key]["avatar_small"] = $tmpuser["avatar_small"];
            unset($tmpuser);
        }

        $this->ajaxReturn($message, "JSONP");
    }
}
