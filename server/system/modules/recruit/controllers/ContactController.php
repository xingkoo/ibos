<?php

class RecruitContactController extends RecruitBaseController
{
    public function actionIndex()
    {
        $paginationData = ResumeContact::model()->fetchAllByPage($this->condition);
        $resumes = CJSON::encode(ResumeDetail::model()->fetchAllRealnames());
        $params = array("sidebar" => $this->getSidebar(), "resumeContactList" => ICResumeContact::processListData($paginationData["data"]), "pagination" => $paginationData["pagination"], "exportData" => json_encode($paginationData["data"]), "resumes" => $resumes);
        $this->setPageTitle(Ibos::lang("Contact record"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Recruitment management"), "url" => $this->createUrl("resume/index")),
            array("name" => Ibos::lang("Contact record"), "url" => $this->createUrl("contact/index")),
            array("name" => Ibos::lang("Contact list"))
        ));
        $this->render("index", $params);
    }

    public function actionAdd()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $fullname = EnvUtil::getRequest("fullname");
            $resumeid = ResumeDetail::model()->fetchResumeidByRealname($fullname);

            if (empty($resumeid)) {
                $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("This name does not exist resume")));
            }

            $data = ICResumeContact::processAddOrEditData($_POST);
            $data["resumeid"] = $resumeid;
            $contactid = ResumeContact::model()->add($data, true);

            if ($contactid) {
                $contact = ResumeContact::model()->fetchByPk($contactid);
                $contact["inputtime"] = date("Y-m-d", $contact["inputtime"]);
                $contact["input"] = User::model()->fetchRealnameByUid($contact["input"]);
                $contact["fullname"] = $fullname;
                $status = Resume::model()->fetchStatusByResumeid($resumeid);

                if ($status == 4) {
                    Resume::model()->modify($resumeid, array("status" => 1));
                }

                $this->ajaxReturn($contact);
            } else {
                $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Add fail")));
            }
        }
    }

    public function actionDel()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $contactids = EnvUtil::getRequest("contactids");

            if (empty($contactids)) {
                $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("contact/index"));
            }

            if (strpos($contactids, ",")) {
                $pk = explode(",", trim($contactids, ","));
            } else {
                $pk = $contactids;
            }

            $delSuccess = ResumeContact::model()->deleteByPk($pk);

            if ($delSuccess) {
                $this->ajaxReturn(array("isSuccess" => 1, "msg" => Ibos::lang("Del succeed", "message")));
            } else {
                $this->ajaxReturn(array("isSuccess" => 0, "msg" => Ibos::lang("Del failed", "message")));
            }
        }
    }

    public function actionEdit()
    {
        $op = EnvUtil::getRequest("op");
        $contactid = EnvUtil::getRequest("contactid");
        if (!in_array($op, array("update", "getEditData")) || empty($contactid)) {
            $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("contact/index"));
        } else {
            $this->{$op}();
        }
    }

    private function update()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $contactid = EnvUtil::getRequest("contactid");
            $data = ICResumeContact::processAddOrEditData($_POST);
            $modifySuccess = ResumeContact::model()->modify($contactid, $data);

            if ($modifySuccess) {
                $contact = ResumeContact::model()->fetchByPk($contactid);
                $contact["inputtime"] = date("Y-m-d", $contact["inputtime"]);
                $contact["input"] = User::model()->fetchRealnameByUid($contact["input"]);
                $contact["fullname"] = ResumeDetail::model()->fetchRealnameByResumeid($contact["resumeid"]);
                $contact["detail"] = StringUtil::cutStr($contact["detail"], 12);
                $this->ajaxReturn($contact);
            } else {
                $this->ajaxReturn(array("isSuccess" => 0));
            }
        }
    }

    private function getEditData()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $contactid = EnvUtil::getRequest("contactid");
            $contact = ResumeContact::model()->fetchByPk($contactid);
            $contact["inputtime"] = date("Y-m-d", $contact["inputtime"]);
            $contact["upuid"] = StringUtil::wrapId($contact["input"]);
            $this->ajaxReturn($contact);
        }
    }

    public function actionExport()
    {
        $contactids = EnvUtil::getRequest("contactids");
        $contactArr = ResumeContact::model()->fetchAll("FIND_IN_SET(contactid, '$contactids')");
        $fieldArr = array(Ibos::lang("Name"), Ibos::lang("Contact date"), Ibos::lang("Contact staff"), Ibos::lang("Contact method"), Ibos::lang("Contact purpose"), Ibos::lang("Content"));
        $str = implode(",", $fieldArr) . "\n";

        foreach ($contactArr as $contact) {
            $realname = ResumeDetail::model()->fetchRealnameByResumeid($contact["resumeid"]);
            $input = User::model()->fetchRealnameByUid($contact["input"]);
            $inputtime = (empty($contact["inputtime"]) ? "" : date("Y-m-d", $contact["inputtime"]));
            $method = $contact["contact"];
            $purpose = $contact["purpose"];
            $detail = $contact["detail"];
            $str .= $realname . "," . $inputtime . "," . $input . "," . $method . "," . $purpose . "," . $detail . "\n";
        }

        $outputStr = iconv("utf-8", "gbk//ignore", $str);
        $filename = date("Y-m-d") . mt_rand(100, 999) . ".csv";
        FileUtil::exportCsv($filename, $outputStr);
    }
}
