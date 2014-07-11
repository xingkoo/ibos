<?php

class EmailApiController extends EmailBaseController
{
    public function filterRoutes($routes)
    {
        return true;
    }

    public function init()
    {
        parent::init();
        $this->fid = intval(EnvUtil::getRequest("fid"));
        $this->archiveId = intval(EnvUtil::getRequest("archiveid"));
    }

    public function actionGetCount()
    {
        $uid = $this->uid;
        $data = array();
        $data["inboxcount"] = Email::model()->countUnreadByListParam("inbox", $uid);
        $data["todocount"] = Email::model()->countUnreadByListParam("todo", $uid);
        $data["delcount"] = Email::model()->countUnreadByListParam("del", $uid);
        $this->ajaxReturn($data);
    }

    public function actionSetAllRead()
    {
        $uid = $this->uid;
        Email::model()->setAllRead($uid);
        $this->ajaxReturn(array("isSuccess" => true));
    }

    public function actionRecall()
    {
        $ids = EnvUtil::getRequest("emailids");
        $id = StringUtil::filterStr($ids);
        $status = false;

        if (!empty($id)) {
            $status = Email::model()->recall($id, $this->uid);
        }

        $errorMsg = (!$status ? Ibos::lang("Operation failure", "message") : "");
        $this->ajaxReturn(array("isSuccess" => !!$status, "errorMsg" => $errorMsg));
    }

    public function actionDelDraft()
    {
        $ids = EnvUtil::getRequest("emailids");
        $id = StringUtil::filterStr($ids);
        $status = false;

        if (!empty($id)) {
            $status = EmailBody::model()->delBody($id, $this->archiveId);
        }

        $errorMsg = (!$status ? Ibos::lang("Operation failure", "message") : "");
        $this->ajaxReturn(array("isSuccess" => !!$status, "errorMsg" => $errorMsg));
    }

    public function actionCpDel()
    {
        $ids = EnvUtil::getRequest("emailids");
        $id = StringUtil::filterStr($ids);
        $status = false;

        if (!empty($id)) {
            $status = Email::model()->completelyDelete(explode(",", $id), $this->uid, $this->archiveId);
        }

        $errorMsg = (!$status ? Ibos::lang("Operation failure", "message") : "");
        $this->ajaxReturn(array("isSuccess" => !!$status, "errorMsg" => $errorMsg));
    }

    public function actionMark()
    {
        $op = EnvUtil::getRequest("op");
        $opList = array("todo", "read", "unread", "sendreceipt", "cancelreceipt", "del", "restore", "batchdel", "move");

        if (!in_array($op, $opList)) {
            exit();
        }

        $ids = EnvUtil::getRequest("emailids");
        $id = StringUtil::filterStr($ids);
        $extends = array();
        $condition = "toid = " . $this->uid . " AND FIND_IN_SET(emailid,\"" . $id . "\")";
        $valueDriver = array(
            "read"          => array("isread", 1),
            "unread"        => array("isread", 0),
            "sendreceipt"   => array("isreceipt", 1),
            "cancelreceipt" => array("isreceipt", 2),
            "restore"       => array("isdel", 0)
        );

        switch ($op) {
            case "del":
            case "batchdel":
                if ($op == "del") {
                    $next = Email::model()->fetchNext($id, $this->uid, $this->fid, $this->archiveId);

                    if (!empty($next)) {
                        $extends["url"] = $this->createUrl("content/show", array("id" => $next["emailid"], "archiveid" => $this->archiveId));
                    } else {
                        $extends["url"] = $this->createUrl("list/index");
                    }
                }

                $status = Email::model()->setField("isdel", 3, $condition);
                break;

            case "move":
                $fid = intval(EnvUtil::getRequest("fid"));
                $status = Email::model()->updateAll(array("fid" => $fid, "isdel" => 0), $condition);
                break;

            case "todo":
                $markFlag = EnvUtil::getRequest("ismark");
                $ismark = (strcasecmp($markFlag, "true") == 0 ? 1 : 0);
                $status = Email::model()->setField("ismark", $ismark, $condition);
                break;

            case "sendreceipt":
                $fromInfo = Ibos::app()->db->createCommand()->select("eb.bodyid,eb.subject,eb.fromid")->from("{{email_body}} eb")->leftJoin("{{email}} e", "e.bodyid = eb.bodyid")->where("e.emailid = " . intval($id))->queryRow();

                if ($fromInfo) {
                    $config = array("{reader}" => Ibos::app()->user->realname, "{url}" => Ibos::app()->urlManager->createUrl("email/content/show", array("id" => $fromInfo["bodyid"])), "{title}" => $fromInfo["subject"]);
                    Notify::model()->sendNotify($fromInfo["fromid"], "email_receive_message", $config);
                }
            default:
                if (isset($valueDriver[$op])) {
                    $value = $valueDriver[$op][1][0];
                    $valueDriver;
                    $status = Email::model()->setField($key, $value, $condition);
                } else {
                    $status = false;
                }

                break;
        }

        $errorMsg = (!$status ? Ibos::lang("Operation failure", "message") : "");
        $this->ajaxReturn(array_merge(array("isSuccess" => !!$status, "errorMsg" => $errorMsg), $extends));
    }
}
