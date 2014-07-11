<?php

class MobileMailController extends MobileBaseController
{
    public function actionIndex()
    {
        $type = (isset($_GET["type"]) ? $_GET["type"] : "");
        $search = (isset($_GET["search"]) ? $_GET["search"] : "");

        if (!empty($search)) {
            $this->search($search);
        }

        $uid = Yii::app()->user->uid;
        $fid = 0;
        $count = Email::model()->countByListParam($type, $uid, $fid, 0);
        $pages = PageUtil::create($count);
        $list = Email::model()->fetchAllByListParam($type, $uid, $fid, 0, $pages->getLimit(), $pages->getOffset());
        $data = array(
            "datas" => $list,
            "pages" => array("pageCount" => $pages->getPageCount(), "page" => $pages->getCurrentPage(), "pageSize" => $pages->getPageSize())
        );
        $this->ajaxReturn($data, "JSONP");
    }

    private function search($kw)
    {
        $search["keyword"] = $kw;
        $condition = array();
        $condition = EmailUtil::mergeSearchCondition($search, Yii::app()->user->uid);
        $conditionStr = base64_encode(serialize($condition));

        if (empty($condition)) {
            $this->error(Ibos::lang("Request tainting", "error"), $this->createUrl("list/index"));
        }

        $emailData = Email::model()->fetchAllByArchiveIds("*", $condition["condition"], $condition["archiveId"], array("e", "eb"), null, null, SORT_DESC, "emailid");
        $count = count($emailData);
        $pages = PageUtil::create($count, 10, false);
        $pages->params = array("condition" => $conditionStr);
        $list = array_slice($emailData, $pages->getOffset(), $pages->getLimit(), false);

        foreach ($list as $index => &$mail) {
            $mail["fromuser"] = ($mail["fromid"] ? User::model()->fetchRealnameByUid($mail["fromid"]) : "");
        }

        $return = array(
            "datas" => $list,
            "pages" => array("pageCount" => $pages->getPageCount(), "page" => $pages->getCurrentPage(), "pageSize" => $pages->getPageSize())
            );
        $this->ajaxReturn($return, "JSONP");
    }

    public function actionCategory()
    {
        $uid = Yii::app()->user->uid;
        $myFolders = EmailBox::model()->fetchAllNotSysByUid($uid);
        $notReadCount = Email::model()->countNotReadByToid($uid, "inbox");
        $return = array("folders" => $myFolders, "notread" => $notReadCount);
        $this->ajaxReturn($return, "JSONP");
    }

    public function actionShow()
    {
        $id = (isset($_GET["id"]) ? $_GET["id"] : 0);
        $email = Email::model()->fetchById($id, 0);

        if (!empty($email)) {
            if (!empty($email["attachmentid"])) {
                $email["attach"] = AttachUtil::getAttach($email["attachmentid"], true, false, false, false, true);
                $attachmentArr = explode(",", $email["attachmentid"]);
            }
        }

        Email::model()->setRead($id, Yii::app()->user->uid);
        $this->ajaxReturn($email, "JSONP");
    }

    public function actionDraftShow()
    {
        $id = (isset($_GET["bodyid"]) ? $_GET["bodyid"] : 0);
        $emailBody = EmailBody::model()->fetchByPk($id);
        $this->ajaxReturn($emailBody, "JSONP");
    }

    public function actionEdit()
    {
        $bodyData["subject"] = EnvUtil::getRequest("subject");
        $bodyData["content"] = EnvUtil::getRequest("content");
        $bodyData["toids"] = EnvUtil::getRequest("toids");
        $bodyData["copytoids"] = EnvUtil::getRequest("ccids");
        $bodyData["secrettoids"] = EnvUtil::getRequest("mcids");
        $bodyData["isneedreceipt"] = 0;
        $bodyData["fromid"] = Yii::app()->user->uid;
        $bodyData["sendtime"] = time();
        $bodyId = EmailBody::model()->add($bodyData, true);
        Email::model()->send($bodyId, $bodyData);
        $this->ajaxReturn($bodyId, "JSONP");
    }

    public function actionDel()
    {
        $ids = EnvUtil::getRequest("emailid");
        $id = StringUtil::filterStr($ids);
        $status = false;

        if (!empty($id)) {
            $condition = "toid = " . intval(Yii::app()->user->uid) . " AND FIND_IN_SET(emailid,\"" . $id . "\")";
            $status = Email::model()->setField("isdel", 1, $condition);
        }

        $errorMsg = (!$status ? Ibos::lang("Operation failure", "message") : "");
        $this->ajaxReturn(array("isSuccess" => !!$status, "errorMsg" => $errorMsg), "JSONP");
    }

    public function actionMark()
    {
        $id = EnvUtil::getRequest("emailid");
        $status = false;

        if (!empty($id)) {
            $condition = "toid = " . $this->uid . " AND FIND_IN_SET(emailid,\"" . $id . "\")";
            $markFlag = EnvUtil::getRequest("ismark");
            $ismark = (strcasecmp($markFlag, "true") == 0 ? 1 : 0);
            $status = Email::model()->setField("ismark", $ismark, $condition);
        }

        $errorMsg = (!$status ? Ibos::lang("Operation failure", "message") : "");
        $this->ajaxReturn(array("isSuccess" => !!$status, "errorMsg" => $errorMsg), "JSONP");
    }
}
