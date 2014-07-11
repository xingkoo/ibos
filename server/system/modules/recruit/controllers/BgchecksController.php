<?php

class RecruitBgchecksController extends RecruitBaseController
{
    public function actionIndex()
    {
        $paginationData = ResumeBgchecks::model()->fetchAllByPage($this->condition);
        $resumes = CJSON::encode(ResumeDetail::model()->fetchAllRealnames());
        $params = array("sidebar" => $this->getSidebar(), "resumeBgchecksList" => ICRecruitBgchecks::processListData($paginationData["data"]), "pagination" => $paginationData["pagination"], "exportData" => json_encode($paginationData["data"]), "resumes" => $resumes);
        $this->setPageTitle(Ibos::lang("Background investigation"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Recruitment management"), "url" => $this->createUrl("resume/index")),
            array("name" => Ibos::lang("Background investigation"), "url" => $this->createUrl("bgchecks/index")),
            array("name" => Ibos::lang("Bgchecks list"))
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

            $data = ICRecruitBgchecks::processAddOrEditData($_POST);
            $data["resumeid"] = $resumeid;
            $bgcheckid = ResumeBgchecks::model()->add($data, true);

            if ($bgcheckid) {
                $bgcheck = ResumeBgchecks::model()->fetchByPk($bgcheckid);
                $bgcheck["entrytime"] = ($bgcheck["entrytime"] == 0 ? "-" : date("Y-m-d", $bgcheck["entrytime"]));
                $bgcheck["quittime"] = ($bgcheck["quittime"] == 0 ? "-" : date("Y-m-d", $bgcheck["quittime"]));
                $bgcheck["fullname"] = $fullname;
                $this->ajaxReturn($bgcheck);
            } else {
                $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Add fail")));
            }
        }
    }

    public function actionEdit()
    {
        $op = EnvUtil::getRequest("op");
        $checkid = EnvUtil::getRequest("checkid");
        if (!in_array($op, array("update", "getEditData")) || empty($checkid)) {
            $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("bgchecks/index"));
        } else {
            $this->{$op}();
        }
    }

    private function getEditData()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $checkid = EnvUtil::getRequest("checkid");
            $bgcheck = ResumeBgchecks::model()->fetchByPk($checkid);
            $bgcheck["entrytime"] = ($bgcheck["entrytime"] == 0 ? "" : date("Y-d-d", $bgcheck["entrytime"]));
            $bgcheck["quittime"] = ($bgcheck["quittime"] == 0 ? "" : date("Y-d-d", $bgcheck["quittime"]));
            $bgcheck["fullname"] = ResumeDetail::model()->fetchRealnameByResumeid($bgcheck["resumeid"]);
            $this->ajaxReturn($bgcheck);
        }
    }

    private function update()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $checkid = EnvUtil::getRequest("checkid");
            $data = ICRecruitBgchecks::processAddOrEditData($_POST);
            $modifySuccess = ResumeBgchecks::model()->modify($checkid, $data);

            if ($modifySuccess) {
                $bgcheck = ResumeBgchecks::model()->fetchByPk($checkid);
                $bgcheck["entrytime"] = ($bgcheck["entrytime"] == 0 ? "-" : date("Y-m-d", $bgcheck["entrytime"]));
                $bgcheck["quittime"] = ($bgcheck["entrytime"] == 0 ? "-" : date("Y-m-d", $bgcheck["quittime"]));
                $bgcheck["fullname"] = ResumeDetail::model()->fetchRealnameByResumeid($bgcheck["resumeid"]);
                $this->ajaxReturn($bgcheck);
            } else {
                $this->ajaxReturn(array("isSuccess" => 0));
            }
        }
    }

    public function actionDel()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $checkids = EnvUtil::getRequest("checkids");

            if (empty($checkids)) {
                $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("bgchecks/index"));
            }

            $pk = "";

            if (strpos($checkids, ",")) {
                $pk = explode(",", trim($checkids, ","));
            } else {
                $pk = $checkids;
            }

            $delSuccess = ResumeBgchecks::model()->deleteByPk($pk);

            if ($delSuccess) {
                $this->ajaxReturn(array("isSuccess" => 1, "msg" => Ibos::lang("Del succeed", "message")));
            } else {
                $this->ajaxReturn(array("isSuccess" => 0, "msg" => Ibos::lang("Del failed", "message")));
            }
        }
    }

    public function actionExport()
    {
        $checkids = EnvUtil::getRequest("checkids");
        $bgcheckArr = ResumeBgchecks::model()->fetchAll("FIND_IN_SET(checkid, '$checkids')");
        $fieldArr = array(Ibos::lang("Name"), Ibos::lang("Company name"), Ibos::lang("Position"), Ibos::lang("Entry time"), Ibos::lang("Departure time"), Ibos::lang("Details"));
        $str = implode(",", $fieldArr) . "\n";

        foreach ($bgcheckArr as $bgcheck) {
            $realname = ResumeDetail::model()->fetchRealnameByResumeid($bgcheck["resumeid"]);
            $company = $bgcheck["company"];
            $position = $bgcheck["position"];
            $entryTime = (empty($bgcheck["entrytime"]) ? "" : date("Y-m-d", $bgcheck["entrytime"]));
            $quitTime = (empty($bgcheck["quittime"]) ? "" : date("Y-m-d", $bgcheck["quittime"]));
            $detail = $bgcheck["detail"];
            $str .= $realname . "," . $company . "," . $position . "," . $entryTime . "," . $quitTime . "," . $detail . "\n";
        }

        $outputStr = iconv("utf-8", "gbk//ignore", $str);
        $filename = date("Y-m-d") . mt_rand(100, 999) . ".csv";
        FileUtil::exportCsv($filename, $outputStr);
    }
}
