<?php

class WorkflowApiController extends ICController
{
    public function filterRoutes($routes)
    {
        return true;
    }

    public function actionGetField()
    {
        $flowId = intval(EnvUtil::getRequest("flowid"));
        $flow = new ICFlowType($flowId);
        $structure = $flow->form->parser->getStructure();
        $exceptType = array("sign");
        $field = WfFormUtil::getAllItemName($structure, $exceptType, "[A@],[B@]");
        $this->ajaxReturn(array("isSuccess" => true, "data" => $field));
    }

    public function actionGetText()
    {
        $formID = intval(EnvUtil::getRequest("formid"));
        $field = StringUtil::filterCleanHtml(EnvUtil::getRequest("field"));
        $rs = Ibos::app()->db->createCommand()->select($field)->from("{{flow_form_type}}")->where("formid = " . $formID)->queryScalar();
        exit($rs);
    }

    public function actionSetText()
    {
        $formID = intval(EnvUtil::getRequest("formid"));
        $field = StringUtil::filterCleanHtml(EnvUtil::getRequest("field"));
        $content = EnvUtil::getRequest("content");
        Ibos::app()->db->createCommand()->update("{{flow_form_type}}", array($field => $content), "formid = " . $formID);
        exit("1");
    }

    public function actionGetNextItemID()
    {
        $id = intval(EnvUtil::getRequest("id"));
        $this->ajaxReturn(array("isSuccess" => true, "id" => FlowFormType::model()->getNextItemID($id)));
    }

    public function actionGetItem()
    {
        $flowId = intval(EnvUtil::getRequest("flowid"));
        $flow = new ICFlowType($flowId);
        $structure = $flow->form->parser->getStructure();
        $return = array();

        foreach ($structure as $config) {
            if ($config["data-type"] !== "label") {
                if (in_array($config["tag"], array("input", "textarea", "select")) || ($config["data-type"] == "auto") || ($config["data-type"] == "user")) {
                    $return[$config["itemid"]] = $config["data-title"];
                }
            }
        }

        $this->ajaxReturn(array("isSuccess" => true, "data" => $return));
    }

    public function actionTestSql()
    {
        $str = trim(EnvUtil::getRequest("sql"));

        if (empty($str)) {
            exit(Ibos::lang("Empty sql statement"));
        }

        if (strtolower(substr($str, 0, 6)) != "select") {
            exit(Ibos::lang("Illegal sql statement"));
        }

        $map = array("`" => "'", "&#13;&#10;" => " ", "[sys_user_id]" => Ibos::app()->user->uid, "[sys_deptid]" => Ibos::app()->user->deptid, "[sys_pos_id]" => Ibos::app()->user->positionid, "[sys_run_id]" => 0);
        $sql = str_replace(array_keys($map), array_values($map), $str);

        try {
            $query = Ibos::app()->db->createCommand()->setText($sql)->query();

            if (!$query) {
                exit(Ibos::lang("Error sql statement"));
            }

            exit(Ibos::lang("SQL statement success"));
        } catch (Exception $exc) {
            exit(Ibos::lang("Error sql statement"));
        }
    }
}
