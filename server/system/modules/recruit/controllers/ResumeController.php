<?php

class RecruitResumeController extends RecruitBaseController
{
    public function actionIndex()
    {
        $type = EnvUtil::getRequest("type");
        $this->condition = RecruitUtil::joinTypeCondition($type, $this->condition);
        $data = Resume::model()->fetchAllByPage($this->condition);
        $resumeList = ICResumeDetail::processListData($data["datas"]);
        $params = array("sidebar" => $this->getSidebar(), "resumeList" => $resumeList, "pages" => $data["pages"], "isInstallEmail" => $this->checkIsInstallEmail());
        $this->setPageTitle(Ibos::lang("Talent management"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Recruitment management"), "url" => $this->createUrl("resume/index")),
            array("name" => Ibos::lang("Talent management")),
            array("name" => Ibos::lang("Resume list"))
        ));
        $this->render("index", $params);
    }

    public function actionAdd()
    {
        $op = EnvUtil::getRequest("op");

        if (!in_array($op, array("new", "save", "analysis"))) {
            $op = "new";
        }

        if ($op == "new") {
            $params = array("sidebar" => $this->getSidebar(), "dashboardConfig" => $this->getDashboardConfig(), "uploadConfig" => AttachUtil::getUploadConfig());
            $params["dashboardConfigToJson"] = CJSON::encode($params["dashboardConfig"]);
            $regulars = Regular::model()->fetchAll();
            $params["regulars"] = CJSON::encode($regulars);
            $this->setPageTitle(Ibos::lang("Add resume"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Recruitment management"), "url" => $this->createUrl("resume/index")),
                array("name" => Ibos::lang("Talent management"), "url" => $this->createUrl("resume/index")),
                array("name" => Ibos::lang("Add resume"))
            ));
            $this->render("add", $params);
        } else {
            $this->{$op}();
        }
    }

    private function save()
    {
        $data = ICResumeDetail::processAddRequestData();
        $resume = array("input" => Ibos::app()->user->uid, "positionid" => $data["positionid"], "entrytime" => TIMESTAMP, "uptime" => TIMESTAMP, "status" => $data["status"], "statustime" => strtotime(date("Y-m-d")));
        $resumeId = Resume::model()->add($resume, true);

        if ($resumeId) {
            $data["resumeid"] = $resumeId;
            $data["birthday"] = strtotime($data["birthday"]);
            ResumeDetail::model()->add($data);

            if (!empty($data["avatarid"])) {
                AttachUtil::updateAttach($data["avatarid"]);
            }

            if (!empty($data["attachmentid"])) {
                AttachUtil::updateAttach($data["attachmentid"]);
            }

            $uid = Ibos::app()->user->uid;
            UserUtil::updateCreditByAction("addresume", $uid);
            $this->success(Ibos::lang("Save succeed", "message"), $this->createUrl("resume/index"));
        }
    }

    public function actionShow()
    {
        $resumeid = EnvUtil::getRequest("resumeid");

        if (empty($resumeid)) {
            $this->error(Ibos::lang("Parameters error", "error"));
        }

        $resumeDetail = ResumeDetail::model()->fetch("resumeid=" . $resumeid);
        $prevAndNextPK = Resume::model()->fetchPrevAndNextPKByPK($resumeid);
        $contactList = ResumeContact::model()->fetchAll("resumeid=:resumeid", array(":resumeid" => $resumeid));
        $interviewList = ResumeInterview::model()->fetchAll("resumeid=:resumeid", array(":resumeid" => $resumeid));
        $bgcheckList = ResumeBgchecks::model()->fetchAll("resumeid=:resumeid", array(":resumeid" => $resumeid));
        $avatarid = $resumeDetail["avatarid"];

        if (empty($avatarid)) {
            $resumeDetail["avatarUrl"] = "";
        } else {
            $avatar = AttachUtil::getAttachData($avatarid);
            $resumeDetail["avatarUrl"] = FileUtil::fileName(FileUtil::getAttachUrl() . "/" . $avatar[$avatarid]["attachment"]);
        }

        if (!empty($resumeDetail["attachmentid"])) {
            $resumeDetail["attach"] = AttachUtil::getAttach($resumeDetail["attachmentid"]);
        }

        $data = array("sidebar" => $this->getSidebar(), "resumeDetail" => ICResumeDetail::processShowData($resumeDetail), "prevAndNextPK" => $prevAndNextPK, "contactList" => ICResumeContact::processListData($contactList), "interviewList" => ICRecruitInterview::processListData($interviewList), "bgcheckList" => ICRecruitBgchecks::processListData($bgcheckList), "resumeid" => $resumeid);
        $this->setPageTitle(Ibos::lang("Show resume"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Recruitment management"), "url" => $this->createUrl("resume/index")),
            array("name" => Ibos::lang("Talent management"), "url" => $this->createUrl("resume/index")),
            array("name" => Ibos::lang("Show resume"))
        ));
        $this->render("show", $data);
    }

    public function actionEdit()
    {
        $op = EnvUtil::getRequest("op");
        $resumeid = EnvUtil::getRequest("resumeid");

        if (empty($op)) {
            $op = "default";
        }

        if (!in_array($op, array("default", "update", "mark", "status")) || empty($resumeid)) {
            $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("resume/index"));
        }

        if ($op == "default") {
            $detail = ResumeDetail::model()->fetch("resumeid=:resumeid", array(":resumeid" => $resumeid));
            $detail["birthday"] = date("Y-m-d", $detail["birthday"]);
            $detail["status"] = Resume::model()->fetchStatusByResumeid($detail["resumeid"]);
            $avatarid = $detail["avatarid"];

            if (empty($avatarid)) {
                $detail["avatarUrl"] = "";
            } else {
                $avatar = AttachUtil::getAttachData($avatarid);
                $detail["avatarUrl"] = FileUtil::fileName(FileUtil::getAttachUrl() . "/" . $avatar[$avatarid]["attachment"]);
            }

            if (!empty($detail["attachmentid"])) {
                $detail["attach"] = AttachUtil::getAttach($detail["attachmentid"]);
            }

            $data = array("sidebar" => $this->getSidebar(), "resumeDetail" => $detail, "dashboardConfig" => $this->getDashboardConfig(), "uploadConfig" => AttachUtil::getUploadConfig());
            $data["dashboardConfigToJson"] = CJSON::encode($data["dashboardConfig"]);
            $regulars = Regular::model()->fetchAll();
            $data["regulars"] = CJSON::encode($regulars);
            $this->setPageTitle(Ibos::lang("Edit resume"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Recruitment management"), "url" => $this->createUrl("resume/index")),
                array("name" => Ibos::lang("Talent management"), "url" => $this->createUrl("resume/index")),
                array("name" => Ibos::lang("Edit resume"))
            ));
            $this->render("edit", $data);
        } else {
            $this->{$op}();
        }
    }

    private function update()
    {
        $resumeDetail = ICResumeDetail::processAddRequestData();
        $resumeid = EnvUtil::getRequest("resumeid");
        $detailid = EnvUtil::getRequest("detailid");
        $resume = Resume::model()->fetchByPk($resumeid);
        $statustime = ($resume["status"] == $resumeDetail["status"] ? $resume["statustime"] : strtotime(date("Y-m-d")));
        $data = array("input" => Ibos::app()->user->uid, "positionid" => $resumeDetail["positionid"], "uptime" => TIMESTAMP, "status" => $resumeDetail["status"], "statustime" => $statustime);
        $flag = Resume::model()->modify($resumeid, $data);

        if ($flag) {
            unset($resumeDetail["status"]);
            $resumeDetail["birthday"] = strtotime($resumeDetail["birthday"]);
            $orgDetail = ResumeDetail::model()->fetchByPk($detailid);

            if ($resumeDetail["avatarid"] != $orgDetail["avatarid"]) {
                AttachUtil::updateAttach($resumeDetail["avatarid"]);
            }

            if ($resumeDetail["attachmentid"] != $orgDetail["attachmentid"]) {
                AttachUtil::updateAttach($resumeDetail["attachmentid"]);
            }

            ResumeDetail::model()->modify($detailid, $resumeDetail);
            $this->success(Ibos::lang("Update succeed", "message"), $this->createUrl("resume/show", array("resumeid" => $resumeid)));
        } else {
            $this->error(Ibos::lang("Update failed", "message"), $this->createUrl("resume/show", array("resumeid" => $resumeid)));
        }
    }

    public function actionDel()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $resumeids = EnvUtil::getRequest("resumeids");

            if (empty($resumeids)) {
                $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("resume/index"));
            }

            $pk = "";

            if (strpos($resumeids, ",")) {
                $pk = explode(",", trim($resumeids, ","));
            } else {
                $pk = $resumeids;
            }

            $delSuccess = Resume::model()->deleteByPk($pk);

            if ($delSuccess) {
                ResumeContact::model()->deleteAll("FIND_IN_SET(resumeid,'$resumeids') ");
                ResumeInterview::model()->deleteAll("FIND_IN_SET(resumeid,'$resumeids') ");
                ResumeBgchecks::model()->deleteAll("FIND_IN_SET(resumeid,'$resumeids') ");
                $detail = ResumeDetail::model()->fetchAll("FIND_IN_SET(resumeid,'$resumeids') ");
                $avataridArr = ConvertUtil::getSubByKey($detail, "avatarid");
                $attachmentidArr = ConvertUtil::getSubByKey($detail, "attachmentid");

                if (!empty($avataridArr)) {
                    foreach ($avataridArr as $avatarid) {
                        AttachUtil::delAttach($avatarid);
                    }
                }

                if (!empty($attachmentidArr)) {
                    foreach ($attachmentidArr as $attachmentid) {
                        AttachUtil::delAttach($attachmentid);
                    }
                }

                ResumeDetail::model()->deleteAll("FIND_IN_SET(resumeid,'$resumeids') ");
                $this->ajaxReturn(array("isSuccess" => 1, "msg" => Ibos::lang("Del succeed", "message")));
            } else {
                $this->ajaxReturn(array("isSuccess" => 0, "msg" => Ibos::lang("Del failed", "message")));
            }
        }
    }

    private function mark()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $resumeid = intval(EnvUtil::getRequest("resumeid"));
            $flag = intval(EnvUtil::getRequest("flag"));
            $modifySuccess = Resume::model()->modify($resumeid, array("flag" => $flag));

            if ($modifySuccess) {
                $this->ajaxReturn(array("isSuccess" => 1, "msg" => Ibos::lang("Operation succeed", "message")));
            } else {
                $this->ajaxReturn(array("isSuccess" => 0, "msg" => Ibos::lang("Operation failure", "message")));
            }
        }
    }

    private function status()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $resumeid = EnvUtil::getRequest("resumeid");
            $status = EnvUtil::getRequest("status");
            Resume::model()->updateAll(array("status" => $status, "uptime" => TIMESTAMP, "statustime" => strtotime(date("Y-m-d"))), "FIND_IN_SET(resumeid,'$resumeid')");
            $showStatus = ICResumeDetail::handleResumeStatus($status);
            $this->ajaxReturn(array("showStatus" => $showStatus, "isSuccess" => 1, "msg" => Ibos::lang("Operation succeed", "message")));
        }
    }

    private function analysis()
    {
        $importType = intval(EnvUtil::getRequest("importType"));

        if ($importType == 1) {
            $file = $_FILES["importFile"];

            if (0 < $file["error"]) {
                $this->error("上传失败，失败类型：" . $file["error"], $this->createUrl("resume/index"));
            }

            if (!preg_match("/.(txt)$/i", $file["name"], $match)) {
                $this->error("不支持的文件类型", $this->createUrl("resume/index"));
            }

            if ($match[1] == "txt") {
                header("Content-Type:text/html;charset=utf-8");
                $importContent = file_get_contents($file["tmp_name"]);
            }
        } elseif ($importType == 2) {
            $importContent = EnvUtil::getRequest("importContent");
        }

        $code = strtolower(mb_detect_encoding($importContent, array("ASCII", "UTF-8", "GB2312", "GBK", "BIG5")));
        if ((($code == "gb2312") || ($code == "GBK") || ($code == "euc-cn")) && ($code != CHARSET)) {
            $importContent = iconv($code, CHARSET, $importContent);
        }

        $config = AnalysisConfig::getAnalconf();
        $analysis = new ResumeAnalysis($importContent, $config);
        $result = $analysis->parse_content();
        $result["gender"] = (preg_match("/女/", $result["gender"]) ? 2 : 1);
        $result["maritalstatus"] = (preg_match("/是|已/", $result["maritalstatus"]) ? 1 : 0);
        $result["workyears"] = ($result["workyears"] ? intval($result["workyears"]) + 0 : "");
        $result["mobile"] = ($result["mobile"] ? $result["mobile"] + 0 : "");
        $result["height"] = ($result["height"] ? $result["height"] + 0 : "");
        $result["weight"] = ($result["weight"] ? $result["weight"] + 0 : "");
        $result["zipcode"] = ($result["zipcode"] ? $result["zipcode"] + 0 : "");
        $result["qq"] = ($result["qq"] ? intval($result["qq"]) + 0 : "");

        if ($result["birthday"]) {
            $result["birthday"] = date("Y-m-d", strtotime($result["birthday"]));
        } elseif (!empty($result["age"])) {
            $result["birthday"] = (date("Y") - ($result["age"] + 0)) . "-00-00";
        }

        $regulars = Regular::model()->fetchAll();
        $params = array("importInfo" => CJSON::encode($result), "sidebar" => $this->getSidebar(), "dashboardConfig" => $this->getDashboardConfig(), "uploadConfig" => AttachUtil::getUploadConfig(), "regulars" => CJSON::encode($regulars));
        $params["dashboardConfigToJson"] = CJSON::encode($params["dashboardConfig"]);
        $this->setPageTitle(Ibos::lang("Add resume"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Recruitment management"), "url" => $this->createUrl("resume/index")),
            array("name" => Ibos::lang("Talent management"), "url" => $this->createUrl("resume/index")),
            array("name" => Ibos::lang("Add resume"))
        ));
        $this->render("add", $params);
    }

    public function actionSendEmail()
    {
        $resumeids = EnvUtil::getRequest("resumeids");
        $resumeidsStr = trim($resumeids, ",");

        if (empty($resumeidsStr)) {
            $this->error(Ibos::lang("Parameters error", "error"));
        }

        $details = ResumeDetail::model()->fetchAll(array("select" => "email", "condition" => "resumeid IN ($resumeidsStr)"));
        $emails = ConvertUtil::getSubByKey($details, "email");
        $this->redirect(Ibos::app()->urlManager->createUrl("email/content/add", array("webid" => $emails)));
    }
}
