<?php

class WorkflowFormversionController extends WfsetupBaseController
{
    public function actionIndex()
    {
        $id = intval(EnvUtil::getRequest("id"));
        $list = FlowFormVersion::model()->fetchAllByFormId($id);
        $this->handleListData($list);
        $this->ajaxReturn(array("list" => $list, "isSuccess" => true));
    }

    public function actionRestore()
    {
        $verID = intval(EnvUtil::getRequest("verid"));
        $ver = FlowFormVersion::model()->fetchByPk($verID);
        $formID = $ver["formid"];

        foreach (FlowType::model()->fetchAllAssociatedFlowIDByFormID($formID) as $flowID) {
            $hasRun = FlowRun::model()->countAllByFlowId($flowID);

            if ($hasRun) {
                $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Formversion Still has run")));
            } else {
                continue;
            }
        }

        unset($ver["formid"]);
        unset($ver["id"]);
        unset($ver["time"]);
        unset($ver["mark"]);
        FlowFormType::model()->modify($formID, $ver);
        $form = new ICFlowForm($formID);
        $form->getParser()->parse(true);
        $this->ajaxReturn(array("isSuccess" => true));
    }

    public function actionDel()
    {
        $verID = intval(EnvUtil::getRequest("verid"));

        if (FlowFormVersion::model()->deleteByPk($verID)) {
            $this->ajaxReturn(array("isSuccess" => true));
        } else {
            $this->ajaxReturn(array("isSuccess" => false));
        }
    }

    protected function handleListData(&$list)
    {
        foreach ($list as &$ver) {
            $ver["value"] = $ver["id"];
            $ver["text"] = Ibos::lang("Form version text", "", array("{num}" => $ver["mark"], "{date}" => date("Y-m-d H:i:s", $ver["time"])));
        }
    }
}
