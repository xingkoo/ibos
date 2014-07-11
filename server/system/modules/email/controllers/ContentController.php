<?php

class EmailContentController extends EmailBaseController
{
    public function init()
    {
        parent::init();
        $this->archiveId = intval(EnvUtil::getRequest("archiveid"));
    }

    public function actionIndex()
    {
        $this->redirect("list/index");
    }

    public function actionAdd()
    {
        $id = intval(EnvUtil::getRequest("id"));
        $this->checkUserSize();
        $op = EnvUtil::getRequest("op");

        if (!in_array($op, array("new", "quickReply", "reply", "replyall", "fw", "forwardNew", "forwardDoc"))) {
            $op = "new";
        }

        if (EnvUtil::submitCheck("formhash")) {
            if ($op == "quickReply") {
                $data = EmailBody::model()->fetchByPk($id);
                $content = StringUtil::filterCleanHtml(EnvUtil::getRequest("content"));
                $bodyData = EmailBody::model()->getAttributes();
                $bodyData["issend"] = 1;
                $bodyData["fromid"] = $this->uid;
                $bodyData["toids"] = $data["fromid"];
                $bodyData["sendtime"] = TIMESTAMP;
                $bodyData["content"] = $content;
                $bodyData["subject"] = Ibos::lang("Reply subject", "", array("{subject}" => $data["subject"]));
            } else {
                $bodyData = $this->beforeSaveBody();
            }

            $bodyId = EmailBody::model()->add($bodyData, true);
            $this->save($bodyId, $bodyData);
        } else {
            $web = $in = array();

            if ($op == "new") {
                $toid = EnvUtil::getRequest("toid");
                $toWebid = EnvUtil::getRequest("webid");

                if ($toid) {
                    $in[] = StringUtil::wrapId($toid);
                }

                if ($toWebid) {
                    $web = $toWebid;
                }

                $subject = $content = "";
            } else {
                if (($op == "forwardNew") || ($op == "forwardDoc")) {
                    $method = "get" . ucfirst($op);
                    $bodyData = $this->{$method}();
                    $subject = $bodyData["subject"];
                    $content = $bodyData["content"];
                } elseif ($id) {
                    $bodyData = EmailBody::model()->fetchByPk($id);
                    $content = $this->handleEmailContentData($bodyData);

                    if ($bodyData) {
                        switch ($op) {
                            case "reply":
                            case "replyall":
                                if ($op == "reply") {
                                    if (empty($bodyData["fromid"]) && !empty($bodyData["fromwebmail"])) {
                                        $web[] = $bodyData["fromwebmail"];
                                    } else {
                                        $in[] = StringUtil::wrapId($bodyData["fromid"]);
                                    }
                                } elseif (empty($bodyData["fromid"])) {
                                    $allIds = StringUtil::filterStr($bodyData["toids"] . "," . $bodyData["copytoids"]);

                                    foreach (explode(",", $allIds) as $key => $uid) {
                                        if (!empty($uid)) {
                                            $tempUid = strpos($uid, "@");

                                            if (!$tempUid) {
                                                $in[$key] = StringUtil::wrapId($uid);
                                            } else {
                                                $web[$key] = $uid;
                                            }
                                        }
                                    }
                                } else {
                                    $toid = explode(",", $bodyData["toids"]);
                                    $copytoid = explode(",", $bodyData["copytoids"]);
                                    $toidAll = array_merge($toid, $copytoid);
                                    $toidAll = array_filter($toidAll);
                                    $toidAll = array_unique($toidAll);
                                    $uid = Ibos::app()->user->uid;

                                    if ($uid != $bodyData["fromid"]) {
                                        $in[] = StringUtil::wrapId($bodyData["fromid"]);
                                    }

                                    $selfInitTOid = array_search($this->uid, $toidAll);

                                    if ($selfInitTOid !== false) {
                                        unset($toidAll[$selfInitTOid]);
                                    }

                                    if (!empty($toidAll)) {
                                        $in[] = StringUtil::wrapId($toidAll);
                                    }
                                }

                                $subject = Ibos::lang("Reply subject", "", array("{subject}" => $bodyData["subject"]));
                                break;

                            case "fw":
                                $subject = Ibos::lang("Fw subject", "", array("{subject}" => $bodyData["subject"]));

                                if (!empty($bodyData["attachmentid"])) {
                                    $attach = AttachUtil::getAttach($bodyData["attachmentid"]);
                                }

                                break;

                            default:
                                break;
                        }
                    }
                }
            }

            $data = array("op" => $op, "subject" => $subject, "in" => $in, "web" => $web, "content" => $content, "allowWebMail" => $this->allowWebMail, "webMails" => $this->webMails, "systemRemind" => Yii::app()->setting->get("setting/emailsystemremind"), "uploadConfig" => AttachUtil::getUploadConfig());

            if (isset($attach)) {
                $data["attach"] = $attach;
            }

            $this->setPageTitle(Ibos::lang("Fill in email"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Personal Office")),
                array("name" => Ibos::lang("Email center"), "url" => $this->createUrl("list/index")),
                array("name" => Ibos::lang("Fill in email"))
            ));
            $this->render("add", $data);
        }
    }

    public function actionEdit()
    {
        $id = intval(EnvUtil::getRequest("id"));

        if (empty($id)) {
            $this->error(IBos::lang("Parameters error", "error"), $this->createUrl("list/index"));
        }

        $emailBody = EmailBody::model()->fetchByPk($id);

        if (empty($emailBody)) {
            $this->error(Ibos::lang("Email not exists"), $this->createUrl("list/index"));
        }

        if (intval($emailBody["fromid"]) !== $this->uid) {
            $this->error(Ibos::lang("Request tainting", "error"), $this->createUrl("list/index"));
        }

        if (EnvUtil::submitCheck("formhash")) {
            $bodyData = $this->beforeSaveBody();
            EmailBody::model()->modify($id, $bodyData);
            $this->save($id, $bodyData);
        } else {
            $emailBody["toids"] = StringUtil::wrapId($emailBody["toids"]);
            $emailBody["copytoids"] = StringUtil::wrapId($emailBody["copytoids"]);
            $emailBody["secrettoids"] = StringUtil::wrapId($emailBody["secrettoids"]);

            if (!empty($emailBody["attachmentid"])) {
                $emailBody["attach"] = AttachUtil::getAttach($emailBody["attachmentid"]);
            }

            $data = array("email" => $emailBody, "allowWebMail" => $this->allowWebMail, "webMails" => $this->webMails, "systemRemind" => Yii::app()->setting->get("setting/emailsystemremind"), "uploadConfig" => AttachUtil::getUploadConfig());
            $this->setPageTitle(Ibos::lang("Edit email"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Personal Office")),
                array("name" => Ibos::lang("Email center"), "url" => $this->createUrl("list/index")),
                array("name" => Ibos::lang("Edit email"))
            ));
            $this->render("edit", $data);
        }
    }

    public function actionShow()
    {
        $id = (is_null($_GET["id"]) ? 0 : intval($_GET["id"]));

        if ($id) {
            $data = array();
            $email = Email::model()->fetchById($id, $this->archiveId);

            if (!$email) {
                $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("list/index"));
            }

            $isReceiver = ($email["toid"] == $this->uid) || ($email["fromid"] == $this->uid) || StringUtil::findIn($email["copytoids"], $this->uid) || StringUtil::findIn($email["toids"], $this->uid);

            if (!$isReceiver) {
                $this->error(Ibos::lang("View access invalid"), $this->createUrl("list/index"));
            }

            if (EnvUtil::getRequest("op") == "showframe") {
                echo $email["content"];
                exit();
            }

            if ((($email["toid"] == $this->uid) || StringUtil::findIn($email["toids"], $this->uid)) && ($email["isread"] == 0)) {
                Email::model()->setRead($id, $this->uid);
            }

            $email["dateTime"] = ConvertUtil::formatDate($email["sendtime"]);

            if ($this->uid == $email["fromid"]) {
                $email["fromName"] = Ibos::lang("Me");
            } elseif (!empty($email["fromid"])) {
                $email["fromName"] = User::model()->fetchRealnameByUid($email["fromid"]);
            } else {
                $email["fromName"] = $email["fromwebmail"];
            }

            $allIds = StringUtil::filterStr($email["toids"] . "," . $email["copytoids"]);
            $copyToId = explode(",", $email["copytoids"]);
            $toId = explode(",", $email["toids"]);
            $allUsers = $copyToUsers = $toUsers = array();

            foreach (explode(",", $allIds) as $key => $uid) {
                if (!empty($uid)) {
                    $tempUid = strpos($uid, "@");

                    if (!$tempUid) {
                        if ($this->uid == $uid) {
                            $name = Ibos::lang("Self");
                        } else {
                            $name = User::model()->fetchRealnameByUid($uid);
                        }
                    } else {
                        $name = $uid;
                    }

                    if (in_array($uid, $copyToId)) {
                        $copyToUsers[$key] = $allUsers[$key] = $name;
                    } elseif (in_array($uid, $toId)) {
                        $allUsers[$key] = $toUsers[$uid] = $name;
                    } else {
                        $allUsers[$key] = $name;
                    }
                }
            }

            if (!empty($email["towebmail"])) {
                $towebmails = explode(";", $email["towebmail"]);

                while (!empty($towebmails)) {
                    $toUsers[] = $allUsers[] = array_pop($towebmails);
                }

                $toUsers = array_unique($toUsers);
                $allUsers = array_unique($allUsers);
            }

            $data["allUsers"] = $allUsers;
            $data["toUsers"] = $toUsers;
            $data["copyToUsers"] = $copyToUsers;
            $data["isSecretUser"] = StringUtil::findIn($this->uid, $email["secrettoids"]);
            !empty($email["attachmentid"]) && ($data["attach"] = AttachUtil::getAttach($email["attachmentid"]));
            $data["next"] = Email::model()->fetchNext($id, $this->uid, $email["fid"], $this->archiveId);
            $data["prev"] = Email::model()->fetchPrev($id, $this->uid, $email["fid"], $this->archiveId);
            $data["email"] = $email;
            $data["weekDay"] = DateTimeUtil::getWeekDay($email["sendtime"]);
            $this->setPageTitle(Ibos::lang("Show email"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Personal Office")),
                array("name" => Ibos::lang("Email center"), "url" => $this->createUrl("list/index")),
                array("name" => Ibos::lang("Show email"))
            ));
            $this->render("show", $data);
        } else {
            $this->error(Ibos::lang("Parameters error"), $this->createUrl("list/index"));
        }
    }

    public function actionExport()
    {
        $id = intval(EnvUtil::getRequest("id"));
        $op = EnvUtil::getRequest("op");

        if ($op == "eml") {
            EmailUtil::exportEml($id);
        } elseif ($op == "excel") {
            EmailUtil::exportExcel($id);
        }
    }

    protected function checkUserSize()
    {
        $userSize = EmailUtil::getUserSize($this->uid);
        $usedSize = EmailFolder::model()->getUsedSize($this->uid);

        if (ConvertUtil::ConvertBytes($userSize . "m") < $usedSize) {
            $this->error(Ibos::lang("Capacity overflow", "", array("{size}" => $usedSize)), $this->createUrl("email/list"));
        }
    }

    private function beforeSaveBody()
    {
        $data = $_POST["emailbody"];
        if (empty($data["towebmail"]) && empty($data["toids"])) {
            $this->error(Ibos::lang("Empty receiver"));
        }

        $data["fromid"] = $this->uid;
        $bodyData = EmailBody::model()->handleEmailBody($data);
        return $bodyData;
    }

    private function save($bodyId, $bodyData)
    {
        if (!empty($bodyData["attachmentid"]) && $bodyId) {
            AttachUtil::updateAttach($bodyData["attachmentid"], $bodyId);
        }

        if ($bodyData["issend"]) {
            Email::model()->send($bodyId, $bodyData);

            if (!empty($bodyData["towebmail"])) {
                $toUsers = StringUtil::filterStr($bodyData["towebmail"], ";");

                if (!empty($toUsers)) {
                    $webBox = EmailWeb::model()->fetchByPk($bodyData["fromwebid"]);
                    WebMailUtil::sendWebMail($toUsers, $bodyData, $webBox);
                }
            }

            UserUtil::updateCreditByAction("postmail", $this->uid);
            $message = Ibos::lang("Send succeed");
        } else {
            $message = Ibos::lang("Save succeed", "message");
        }

        if (Yii::app()->request->getIsAjaxRequest()) {
            $this->ajaxReturn(array("isSuccess" => true, "messsage" => $message));
        } else {
            $this->success($message, $this->createUrl("list/index"));
        }
    }

    private function handleEmailContentData($bodyData)
    {
        $lang = Ibos::getLangSources();
        $contentData = array("lang" => $lang, "body" => $bodyData);
        $toids = (!empty($bodyData["toids"]) ? explode(",", $bodyData["toids"]) : array());
        $copyToIds = (!empty($bodyData["copytoids"]) ? explode(",", $bodyData["copytoids"]) : array());
        $toid = $copyToId = array();

        if (!empty($toids)) {
            $toUsers = User::model()->fetchAllByUids($toids);
            $toid = ConvertUtil::getSubByKey($toUsers, "realname");
        }

        if (!empty($copyToIds)) {
            $copyToUsers = User::model()->fetchAllByUids($copyToIds);
            $copyToId = ConvertUtil::getSubByKey($copyToUsers, "realname");
        }

        if (!empty($bodyData["towebmail"])) {
            $webMailAddress = explode(";", $bodyData["towebmail"]);
            $toid = array_merge($toid, $webMailAddress);
        }

        $contentData["toid"] = $toid;
        $contentData["copyToId"] = $copyToId;
        $content = $this->renderPartial("content", $contentData, true);
        return $content;
    }

    private function getForwardNew()
    {
        $artId = intval(EnvUtil::getRequest("relatedid"));
        $article = Article::model()->fetchByPk($artId);

        if (empty($article)) {
            $this->error(Ibos::lang("转发的新闻不存在或者已删掉"), Ibos::app()->urlManager->createUrl("article/default/index"));
        }

        return $article;
    }

    private function getForwardDoc()
    {
        $docId = intval(EnvUtil::getRequest("relatedid"));
        $doc = Officialdoc::model()->fetchByPk($docId);

        if (empty($doc)) {
            $this->error(Ibos::lang("转发的公文不存在或者已删掉"), Ibos::app()->urlManager->createUrl("officialdoc/officialdoc/index"));
        }

        return $doc;
    }
}
