<?php

class WorkflowPreviewController extends WorkflowBaseController
{
    public $layout = false;

    public function actionGetPrcs()
    {
        $flowId = EnvUtil::getRequest("flowid");
        $runId = EnvUtil::getRequest("runid");
        $flow = FlowType::model()->fetchByPk($flowId);

        if ($flow["type"] == 1) {
            $prcs = WfPreviewUtil::getFixedPrcs($flowId, $runId);
        } else {
            $prcs = WfPreviewUtil::getFreePrcs($runId);
        }

        $this->ajaxReturn($prcs);
    }

    public function actionPrint()
    {
        $expWord = EnvUtil::getRequest("word");
        $expHtml = EnvUtil::getRequest("html");
        $key = EnvUtil::getRequest("key");

        if ($key) {
            $param = WfCommonUtil::param($key, "DECODE");
            $flowid = (isset($param["flowid"]) ? intval($param["flowid"]) : 0);
            $runid = (isset($param["runid"]) ? intval($param["runid"]) : 0);
            $flowprocess = (isset($param["flowprocess"]) ? intval($param["flowprocess"]) : 0);
        }

        $view = EnvUtil::getRequest("view");

        if ($view) {
            MainUtil::setCookie("flowview", $view, TIMESTAMP + (60 * 60 * 24 * 3000));
        } else {
            $view = MainUtil::getCookie("flowview");

            if (!$view) {
                $view = "1234";
            }
        }

        $data = array("formview" => false, "attachview" => false, "signview" => false, "chartview" => false, "flowid" => $flowid, "runid" => $runid, "key" => $key);
        $this->checkRunAccess($runid);
        $flow = new ICFlowType(intval($flowid));
        $process = new ICFlowProcess($flow->getID(), $flowprocess);
        $run = new ICFlowRun($runid);

        if (strstr($view, "1")) {
            $data["formview"] = true;
            $viewer = new ICFlowFormViewer(array("flow" => $flow, "form" => $flow->form, "run" => $run, "process" => $process));
            $data = array_merge($data, array("runname" => $run->name, "script" => $flow->form->script, "css" => $flow->form->css), $viewer->render(false, true));
        }

        if (strstr($view, "2")) {
            $data["attachview"] = true;

            if ($run->attachmentid !== "") {
                if ($flow->isFixed()) {
                    if (FlowRunProcess::model()->getHasDownper($runid, $flowid, $this->uid)) {
                        $down = 0;
                    } else {
                        $down = 1;
                    }
                } else {
                    $down = 1;
                }

                $data["prcscache"] = WfCommonUtil::loadProcessCache($flowid);
                $data["attachData"] = AttachUtil::getAttach($run->attachmentid, $down);
            }
        }

        if (strstr($view, "3")) {
            $data["signview"] = true;
            $data["feedback"] = WfHandleUtil::loadFeedback($flowid, $runid, $flow->type, $this->uid, true);
        }

        if (strstr($view, "4")) {
            $data["chartview"] = true;
        }

        if ($expHtml || $expWord) {
            $data["chartview"] = false;
        }

        $this->layout = "";
        $this->setPageTitle(Ibos::lang("Print preview"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Workflow"), "url" => $this->createUrl("list/index")),
            array("name" => Ibos::lang("Print preview"), "url" => $this->createUrl("preview/print", array("key" => $key)))
        ));
        $this->render("print", $data);
    }

    public function actionNewPreview()
    {
        $flowId = intval(EnvUtil::getRequest("flowid"));
        $flow = new ICFlowType($flowId);
        $printmodel = $flow->form->printmodelshort;
        $hidden = $read = array();
        $viewer = new ICFlowFormViewer(array("form" => $flow->form));
        $viewer->handleForm($printmodel, $hidden, $read, true);
        $data = array("formname" => $flow->form->formname, "flowID" => $flowId, "type" => $flow->type, "printmodel" => $printmodel, "script" => $flow->form->script, "css" => $flow->form->css);
        $this->render("newPreview", $data);
    }

    public function actionFlow()
    {
        $key = EnvUtil::getRequest("key");

        if ($key) {
            $param = WfCommonUtil::param($key, "DECODE");
        }

        $runId = intval($param["runid"]);
        $flowId = intval($param["flowid"]);
        $this->checkRunAccess($runId);
        $run = FlowRun::model()->fetchByPk($runId);
        $remindUid = array();
        $sidebar = WfPreviewUtil::getViewFlowData($runId, $flowId, $this->uid, $remindUid);
        $data = array("fl" => $sidebar, "remindUid" => $remindUid, "run" => $run, "key" => $key, "flowID" => $flowId);
        $this->render("flow", $data);
    }

    public function actionRedo()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $key = EnvUtil::getRequest("key");

            if ($key) {
                $param = WfCommonUtil::param($key, "DECODE");
                $uid = intval(EnvUtil::getRequest("uid"));
                $per = WfCommonUtil::getRunPermission($param["runid"], $this->uid, $param["processid"]);

                if (!StringUtil::findIn($per, 2)) {
                    exit(Ibos::lang("Permission denied"));
                }

                if ($this->redo($param["runid"], $param["processid"], $uid, $param["flowprocess"])) {
                    $this->ajaxReturn(array("isSuccess" => true));
                } else {
                    $this->ajaxReturn(array("isSuccess" => false));
                }
            }
        }
    }

    public function actionSendRemind()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $toId = $_POST["toid"];
            $runId = intval($_POST["runid"]);
            $message = StringUtil::filterCleanHtml($_POST["message"]);
            Notify::model()->sendNotify($toId, "workflow_todo_remind", array("{message}" => $message));
            MainUtil::setCookie("workflow_todo_remind_" . $runId, 1, 60);
            $this->ajaxReturn(array("isSuccess" => true));
        }
    }

    protected function redo($runId, $processId, $uid, $flowProcess)
    {
        if (FlowRunProcess::model()->updateRedo($runId, $processId, $uid, $flowProcess)) {
            $name = User::model()->fetchRealnameByUid($uid);
            $message = Ibos::lang("Agent redo", "", array("{user}" => $name));
            WfCommonUtil::runlog($runId, $processId, $flowProcess, $this->uid, 7, $message);
            return true;
        }

        return false;
    }
}
