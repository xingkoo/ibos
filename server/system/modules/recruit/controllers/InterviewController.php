<?php

class RecruitInterviewController extends RecruitBaseController
{
    public function actionIndex()
    {
        $paginationData = ResumeInterview::model()->fetchAllByPage($this->condition);
        $resumes = CJSON::encode(ResumeDetail::model()->fetchAllRealnames());
        $params = array("sidebar" => $this->getSidebar(), "resumeInterviewList" => ICRecruitInterview::processListData($paginationData["data"]), "pagination" => $paginationData["pagination"], "exportData" => json_encode($paginationData["data"]), "resumes" => $resumes);
        $this->setPageTitle(Ibos::lang("Interview management"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Recruitment management"), "url" => $this->createUrl("resume/index")),
            array("name" => Ibos::lang("Interview management"), "url" => $this->createUrl("interview/index")),
            array("name" => Ibos::lang("Interview list"))
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

            $data = ICRecruitInterview::processAddOrEditData($_POST);
            $data["resumeid"] = $resumeid;
            $interviewid = ResumeInterview::model()->add($data, true);

            if ($interviewid) {
                $interview = ResumeInterview::model()->fetchByPk($interviewid);
                $interview["interviewtime"] = date("Y-m-d", $interview["interviewtime"]);
                $interview["process"] = StringUtil::cutStr($interview["process"], 12);
                $interview["interviewer"] = User::model()->fetchRealnameByUid($interview["interviewer"]);
                $interview["fullname"] = $fullname;
                $this->ajaxReturn($interview);
            } else {
                $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Add fail")));
            }
        }
    }

    public function actionEdit()
    {
        $op = EnvUtil::getRequest("op");
        $interviewid = EnvUtil::getRequest("interviewid");
        if (!in_array($op, array("update", "getEditData")) || empty($interviewid)) {
            $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("interview/index"));
        } else {
            $this->{$op}();
        }
    }

    private function getEditData()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $interviewid = EnvUtil::getRequest("interviewid");
            $interview = ResumeInterview::model()->fetchByPk($interviewid);
            $interview["interviewtime"] = date("Y-m-d", $interview["interviewtime"]);
            $interview["interviewer"] = StringUtil::wrapId($interview["interviewer"]);
            $this->ajaxReturn($interview);
        }
    }

    private function update()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $interviewid = EnvUtil::getRequest("interviewid");
            $data = ICRecruitInterview::processAddOrEditData($_POST);
            $modifySuccess = ResumeInterview::model()->modify($interviewid, $data);

            if ($modifySuccess) {
                $interview = ResumeInterview::model()->fetchByPk($interviewid);
                $interview["fullname"] = ResumeDetail::model()->fetchRealnameByResumeid($interview["resumeid"]);
                $interview["interviewtime"] = date("Y-m-d", $interview["interviewtime"]);
                $interview["interviewer"] = User::model()->fetchRealnameByUid($interview["interviewer"]);
                $interview["process"] = StringUtil::cutStr($interview["process"], 12);
                $this->ajaxReturn($interview);
            } else {
                $this->ajaxReturn(array("isSuccess" => 0));
            }
        }
    }

    public function actionDel()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $interviewids = EnvUtil::getRequest("interviewids");

            if (empty($interviewids)) {
                $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("interview/index"));
            }

            $pk = "";

            if (strpos($interviewids, ",")) {
                $pk = explode(",", trim($interviewids, ","));
            } else {
                $pk = $interviewids;
            }

            $delSuccess = ResumeInterview::model()->deleteByPk($pk);

            if ($delSuccess) {
                $this->ajaxReturn(array("isSuccess" => 1, "msg" => Ibos::lang("Del succeed", "message")));
            } else {
                $this->ajaxReturn(array("isSuccess" => 0, "msg" => Ibos::lang("Del failed", "message")));
            }
        }
    }

    public function actionExport()
    {
        $interviews = EnvUtil::getRequest("interviews");
        $interviewArr = ResumeInterview::model()->fetchAll("FIND_IN_SET(interviewid, '$interviews')");
        $fieldArr = array(Ibos::lang("Name"), Ibos::lang("Interview time"), Ibos::lang("Interview people"), Ibos::lang("Interview types"), Ibos::lang("Interview process"));
        $str = implode(",", $fieldArr) . "\n";

        foreach ($interviewArr as $interview) {
            $realname = ResumeDetail::model()->fetchRealnameByResumeid($interview["resumeid"]);
            $time = (empty($interview["interviewtime"]) ? "" : date("Y-m-d", $interview["interviewtime"]));
            $interviewer = User::model()->fetchRealnameByUid($interview["interviewer"]);
            $type = $interview["type"];
            $process = $interview["process"];
            $str .= $realname . "," . $time . "," . $interviewer . "," . $type . "," . $process . "\n";
        }

        $outputStr = iconv("utf-8", "gbk//ignore", $str);
        $filename = date("Y-m-d") . mt_rand(100, 999) . ".csv";
        FileUtil::exportCsv($filename, $outputStr);
    }
}
