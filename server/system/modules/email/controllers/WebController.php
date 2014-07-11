<?php

class EmailWebController extends EmailBaseController
{
    public function actionIndex()
    {
        $count = EmailWeb::model()->countByAttributes(array("uid" => $this->uid));
        $pages = PageUtil::create($count, $this->getListPageSize());
        $list = EmailWeb::model()->fetchByList($this->uid, $pages->getOffset(), $pages->getLimit());
        $data = array("pages" => $pages, "list" => $list);
        $this->setPageTitle(Ibos::lang("Web email"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Personal Office")),
            array("name" => Ibos::lang("Email center"), "url" => $this->createUrl("list/index")),
            array("name" => Ibos::lang("Web email"))
        ));
        $this->render("index", $data);
    }

    public function actionAdd()
    {
        $inAjax = intval(EnvUtil::getRequest("inajax"));

        if ($inAjax) {
            return $this->ajaxAdd();
        }

        if (EnvUtil::submitCheck("emailSubmit")) {
            $this->processAddWebMail(false);
            $this->success(Ibos::lang("Save succeed", "message"), $this->createUrl("web/index"));
        } else {
            $this->setPageTitle(Ibos::lang("Add web email"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Personal Office")),
                array("name" => Ibos::lang("Email center"), "url" => $this->createUrl("list/index")),
                array("name" => Ibos::lang("Add web email"))
            ));
            $this->render("add", array("more" => false));
        }
    }

    public function actionEdit()
    {
        if (EnvUtil::getRequest("op") == "setDefault") {
            $webId = EnvUtil::getRequest("webid");
            return $this->setDefault($webId);
        }

        $id = EnvUtil::getRequest("id");

        if (EnvUtil::submitCheck("emailSubmit")) {
            $data = $_POST["web"];
            $this->submitCheck($data, false);
            $web = $this->beforeSave($data);
            $web["ssl"] = (isset($web["ssl"]) ? 1 : 0);
            $web["smtpssl"] = (isset($web["smtpssl"]) ? 1 : 0);
            EmailWeb::model()->modify($id, $web);

            if (!empty($web["foldername"])) {
                EmailFolder::model()->updateAll(array("name" => StringUtil::filterCleanHtml($web["foldername"])), "webid = " . $id . " AND uid = " . $this->uid);
            }

            $this->success(Ibos::lang("Save succeed", "message"), $this->createUrl("web/index"));
        } else {
            $web = EmailWeb::model()->fetch("webid = $id AND uid = " . $this->uid);

            if ($web) {
                $web["foldername"] = EmailFolder::model()->fetchFolderNameByWebId($id);
                $web["password"] = StringUtil::authCode($web["password"], "DECODE", Yii::app()->user->salt);
                $this->setPageTitle(Ibos::lang("Edit web email"));
                $this->setPageState("breadCrumbs", array(
                    array("name" => Ibos::lang(Ibos::lang("Personal Office"))),
                    array("name" => Ibos::lang(Ibos::lang("Email center")), "url" => $this->createUrl("list/index")),
                    array("name" => Ibos::lang(Ibos::lang("Edit web email")))
                ));
                $this->render("edit", array("web" => $web));
            } else {
                $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("web/index"));
            }
        }
    }

    public function actionReceive()
    {
        $webId = intval(EnvUtil::getRequest("webid"));
        $webList = $this->webMails;

        if ($webId === 0) {
            $web = $webList;
        } else {
            $web = (isset($webList[$webId]) ? array($webList[$webId]) : array());
        }

        if (empty($web)) {
            exit();
        }

        $msg = array();

        foreach ($web as $webMail) {
            WebMailUtil::receiveMail($webMail);
        }

        $this->ajaxReturn(array("isSuccess" => true));
    }

    public function actionDel()
    {
        $id = EnvUtil::getRequest("webids");

        if ($id) {
            $id = StringUtil::filterStr($id);
            $delStatus = EmailWeb::model()->delClear($id, $this->uid);

            if ($delStatus) {
                if (Yii::app()->request->getIsAjaxRequest()) {
                    $this->ajaxReturn(array("isSuccess" => true));
                } else {
                    $this->success(Ibos::lang("Del succeed", "message"), $this->createUrl("web/index"));
                }
            }
        }
    }

    public function actionShow()
    {
        $webId = intval(EnvUtil::getRequest("webid"));
        $id = intval(EnvUtil::getRequest("id"));
        $folder = EnvUtil::getRequest("folder");
        $part = EnvUtil::getRequest("part");
        $cid = EnvUtil::getRequest("cid");
        $web = EmailWeb::model()->fetchByPk($webId);

        if (intval($web["uid"]) !== $this->uid) {
            exit();
        }

        list($prefix) = explode(".", $web["server"]);
        $user = User::model()->fetchByUid($web["uid"]);
        $pwd = StringUtil::authCode($web["password"], "DECODE", $user["salt"]);

        if ($prefix == "imap") {
            $obj = new WebMailImap();
        } else {
            $obj = new WebMailPop();
        }

        $conn = $obj->connect($web["server"], $web["username"], $pwd, $web["ssl"], $web["port"], "plain");

        if (!$conn) {
            exit("Login failed");
        } else {
            if (strpos(getenv("HTTP_USER_AGENT"), "MSIE")) {
                $dispositionMode = "inline";
            } else {
                $dispositionMode = "attachment";
            }

            $header = $obj->fetchHeader($conn, $folder, $id);

            if (!$header) {
                exit();
            }

            $structure_str = $obj->fetchStructureString($conn, $folder, $id);
            $structure = EmailMimeUtil::getRawStructureArray($structure_str);
            if (!$part && $cid) {
                $parts_list = EmailMimeUtil::getPartList($structure, "");

                if (is_array($parts_list)) {
                    reset($parts_list);

                    while (list($part_id, $part_a) = each($parts_list)) {
                        if ($part_a["id"] == $cid) {
                            $part = $part_id;
                        }
                    }
                }

                if (!isset($part)) {
                    exit();
                }
            }

            if (isset($source)) {
            } elseif (isset($show_header)) {
            } elseif (isset($printer_friendly)) {
            } elseif (isset($tneffid)) {
            } else {
                $header_obj = $header;
                $type = EmailMimeUtil::getPartTypeCode($structure, $part);
                if (empty($part) || ($part == 0)) {
                    $typestr = $header_obj->ctype;
                } else {
                    $typestr = EmailMimeUtil::getPartTypeString($structure, $part);
                }

                list($majortype, $subtype) = explode("/", $typestr);

                if ($type == EmailMimeUtil::MIME_APPLICATION) {
                    $name = str_replace("/", ".", EmailMimeUtil::getPartName($structure, $part));
                    header("Content-type: $typestr; name=\"" . $name . "\"");
                    header("Content-Disposition: " . $dispositionMode . "; filename=\"" . $name . "\"");
                    header("Expires: 0");
                    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                    header("Pragma: public");
                } elseif ($type == EmailMimeUtil::MIME_MESSAGE) {
                    $name = str_replace("/", ".", EmailMimeUtil::getPartName($structure, $part));
                    header("Content-Type: text/plain; name=\"" . $name . "\"");
                } elseif ($type != EmailMimeUtil::MIME_INVALID) {
                    $charset = EmailMimeUtil::getPartCharset($structure, $part);
                    $name = str_replace("/", ".", EmailMimeUtil::getPartName($structure, $part));
                    $header = "Content-type: $typestr";

                    if (!empty($charset)) {
                        $header .= "; charset=\"" . $charset . "\"";
                    }

                    if (!empty($name)) {
                        $header .= "; name=\"" . $name . "\"";
                    }

                    header($header);
                    if (($type != EmailMimeUtil::MIME_TEXT) && ($type != EmailMimeUtil::MIME_IMAGE)) {
                        header("Content-Disposition: " . $dispositionMode . "; filename=\"" . $name . "\"");
                        header("Expires: 0");
                        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                        header("Pragma: public");
                    } elseif (!empty($name)) {
                        header("Content-Disposition: inline; filename=\"" . $name . "\"");
                    }
                }

                if (($type == EmailMimeUtil::MIME_TEXT) && (strcasecmp($subtype, "html") == 0)) {
                    $is_html = true;
                    $img_url = Yii::app()->urlManager->createUrl("email/web/show", array("webid" => $webId, "folder" => $folder, "id" => $id, "cid" => ""));
                } else {
                    $is_html = false;
                    $img_url = "";
                }

                if (isset($print)) {
                    $obj->printPartBody($conn, $folder, $id, $part);
                } else {
                    $encoding = EmailMimeUtil::getPartEncodingCode($structure, $part);
                    if (isset($raw) && $raw) {
                        $obj->printPartBody($conn, $folder, $id, $part);
                    } elseif ($encoding == 3) {
                        if ($is_html) {
                            $body = $obj->fetchPartBody($conn, $folder, $id, $part);
                            $body = preg_replace("/[^a-zA-Z0-9\/\+]/", "", $body);
                            $body = base64_decode($body);
                            $body = preg_replace("/src=\"cid:/", "src=\"" . $img_url, $body);
                            RyosImapUtil::sanitizeHTML($body);
                            echo $body;
                        } else {
                            $obj->printBase64Body($conn, $folder, $id, $part);
                        }
                    } elseif ($encoding == 4) {
                        $body = $obj->fetchPartBody($conn, $folder, $id, $part);
                        $body = quoted_printable_decode(str_replace("=\r\n", "", $body));

                        if ($is_html) {
                            RyosImapUtil::sanitizeHTML($body);
                            $body = preg_replace("/src=\"cid:/", "src=\"" . $img_url, $body);
                        }

                        echo $body;
                    } elseif ($is_html) {
                        $body = $obj->fetchPartBody($conn, $folder, $id, $part);
                        RyosImapUtil::sanitizeHTML($body);
                        $body = preg_replace("/src=\"cid:/", "src=\"" . $img_url, $body);
                        echo $body;
                    } else {
                        $obj->printPartBody($conn, $folder, $id, $part);
                    }
                }

                $obj->close($conn);
            }
        }
    }

    protected function processAddWebMail($inAjax = false)
    {
        $web = $_POST["web"];
        $errMsg = "";
        $this->submitCheck($web, $inAjax);

        if (isset($_POST["moreinfo"])) {
            if (empty($web["server"])) {
                $this->error(Ibos::lang("Empty server address"), "", array(), $inAjax);
            }

            $passCheck = WebMailUtil::checkAccount($web["address"], $web["password"], $web);

            if ($passCheck) {
                $web = WebMailUtil::mergePostConfig($web["address"], $web["password"], $web);
            } else {
                $errMsg = Ibos::lang("Error server info");
            }
        } else {
            $passCheck = WebMailUtil::checkAccount($web["address"], $web["password"]);

            if ($passCheck) {
                $web = WebMailUtil::getEmailConfig($web["address"], $web["password"]);
            } else {
                $errMsg = Ibos::lang("More server info");
            }
        }

        if (!$passCheck) {
            if (!$inAjax) {
                $this->setPageTitle(Ibos::lang("Add web email"));
                $this->setPageState("breadCrumbs", array(
                    array("name" => Ibos::lang("Personal Office")),
                    array("name" => Ibos::lang("Email center"), "url" => $this->createUrl("list/index")),
                    array("name" => Ibos::lang("Add web email"))
                ));
                $this->render("add", array("more" => true, "errMsg" => $errMsg, "web" => $web));
            } else {
                $data = array("lang" => Ibos::getLangSources(), "more" => true, "errMsg" => $errMsg, "web" => $web);
                $content = $this->renderPartial("ajaxAdd", $data, true);
                $this->ajaxReturn(array("moreinfo" => true, "content" => $content));
            }

            exit();
        }

        $web = $this->beforeSave($web);
        $newId = EmailWeb::model()->add($web, true);
        $folder = array("sort" => 0, "name" => isset($_POST["web"]["name"]) ? StringUtil::filterCleanHtml($_POST["web"]["name"]) : $web["address"], "uid" => $this->uid, "webid" => $newId);
        $fid = EmailFolder::model()->add($folder, true);
        EmailWeb::model()->modify($newId, array("fid" => $fid));
        return $newId;
    }

    protected function setDefault($webId)
    {
        if ($webId) {
            EmailWeb::model()->updateAll(array("isdefault" => 0), "uid = " . $this->uid);
            EmailWeb::model()->modify($webId, array("uid" => $this->uid, "isdefault" => 1));
            $isSuccess = true;
        } else {
            $isSuccess = false;
        }

        $this->ajaxReturn(array("isSuccess" => $isSuccess));
    }

    protected function ajaxAdd()
    {
        if (Yii::app()->request->getIsPostRequest()) {
            $newId = $this->processAddWebMail(true);
            $this->success(Ibos::lang("Save succeed", "message"), "", array(), array("webId" => $newId));
        } else {
            $data = array("lang" => Ibos::getLangSources(), "more" => false);
            $this->renderPartial("ajaxAdd", $data);
        }
    }

    private function beforeSave($web)
    {
        $web["nickname"] = (isset($_POST["web"]["nickname"]) ? trim(htmlspecialchars($_POST["web"]["nickname"])) : "");

        if (empty($web["nickname"])) {
            $web["nickname"] = Yii::app()->user->realname;
        }

        $web["uid"] = $this->uid;
        $web["password"] = StringUtil::authCode($web["password"], "ENCODE", Yii::app()->user->salt);
        return $web;
    }

    private function submitCheck($data, $inAjax)
    {
        if (isset($data["address"]) && empty($data["address"])) {
            $this->error(Ibos::lang("Empty email address"), "", array(), $inAjax);
        }

        if (empty($data["password"])) {
            $this->error(Ibos::lang("Empty email password"), "", array(), $inAjax);
        }
    }
}
