<?php

class WorkflowQuerytplController extends WfsetupBaseController
{
    public function init()
    {
        $this->flowid = intval(EnvUtil::getRequest("flowid"));
        parent::init();
    }

    public function actionIndex()
    {
        $list = FlowQueryTpl::model()->fetchAllByFlowId($this->flowid);
        $data = array("list" => $list, "lang" => Ibos::getLangSources());
        $this->renderPartial("index", $data);
    }

    public function actionAdd()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $data = $this->beforeSave();
            $data["createtime"] = TIMESTAMP;
            FlowQueryTpl::model()->add($data);
            $this->ajaxReturn(array("isSuccess" => true));
        } else {
            $data = $this->handleTplData($this->flowid);
            $this->renderPartial("add", $data);
        }
    }

    public function actionEdit()
    {
        $sid = EnvUtil::getRequest("sid");

        if (EnvUtil::submitCheck("formhash")) {
            $data = $this->beforeSave();
            FlowQueryTpl::model()->modify($sid, $data);
            $this->ajaxReturn(array("isSuccess" => true));
        } else {
            $data = $this->handleTplData($this->flowid);
            $tpl = FlowQueryTpl::model()->fetchByPk(intval($sid));
            $tpl["flow"] = unserialize($tpl["flowconditions"]);

            if (!empty($tpl["flow"]["beginuser"])) {
                $tpl["flow"]["beginuser"] = StringUtil::wrapId($tpl["flow"]["beginuser"]);
            }

            $tpl["group"] = unserialize($tpl["groupbyfields"]);
            $tpl["viewfields"] = (!empty($tpl["viewextfields"]) ? explode(",", $tpl["viewextfields"]) : array());

            if (!empty($tpl["condformula"])) {
                $conArr = explode("\\n", $tpl["condformula"]);
            } else {
                $conArr = array();
            }

            $data["tpl"] = $tpl;
            $data["conArr"] = $conArr;
            $this->renderPartial("edit", $data);
        }
    }

    public function actionDel()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $sid = EnvUtil::getRequest("sid");
            FlowQueryTpl::model()->deleteByPk(intval($sid));
            $this->ajaxReturn(array("isSuccess" => true));
        }
    }

    protected function beforeSave()
    {
        $viewExtFields = StringUtil::filterStr($_POST["viewextfields"]);
        $sumFields = StringUtil::filterStr($_POST["sumfields"]);
        $flowConditions = array("flowquerytype" => $_POST["flow_query_type"], "beginuser" => StringUtil::getId($_POST["begin_user"]), "runname" => StringUtil::filterCleanHtml($_POST["run_name"]), "flowstatus" => $_POST["flow_status"], "time1" => $_POST["time1"], "time2" => $_POST["time2"], "time3" => $_POST["time3"], "time4" => $_POST["time4"], "attachname" => StringUtil::filterCleanHtml($_POST["attach_name"]));
        $groupbyFields = array("field" => $_POST["group_field"], "order" => $_POST["group_sort"]);
        $name = StringUtil::filterCleanHtml($_POST["tplname"]);
        $sid = intval(EnvUtil::getRequest("sid"));
        $data = array("flowid" => $this->flowid, "uid" => $this->uid, "tplname" => $this->tplNameExists($name, $sid) ? $name . StringUtil::random(3) : $name, "viewextfields" => $viewExtFields, "sumfields" => $sumFields, "flowconditions" => serialize($flowConditions), "groupbyfields" => serialize($groupbyFields), "condformula" => $_POST["condformula"]);
        return $data;
    }

    protected function tplNameExists($name, $sid)
    {
        return FlowQueryTpl::model()->checkTplNameExists($name, $sid);
    }

    protected function handleTplData($flowId)
    {
        $data = array();
        $lang = Ibos::getLangSources();
        $flow = new ICFlowType($flowId);
        $flowArr = $flow->toArray();

        if (!empty($flowArr)) {
            $data["flow"] = $flowArr;
            $data["form"] = $flow->form->toArray();
            $formStructure = $flow->form->parser->getStructure();
            $defTitleArr = array(
                array("key" => "runid", "title" => $lang["Flow no"]),
                array("key" => "runname", "title" => $lang["Flow subject/num"]),
                array("key" => "runstatus", "title" => $lang["Flow status"]),
                array("key" => "rundate", "title" => $lang["Flow begin date"]),
                array("key" => "runtime", "title" => $lang["Flow begin time"])
                );
            $titleArr = array();
            $table = "flow_data_" . $flowId;

            foreach ($formStructure as $structure) {
                if (($structure["data-type"] == "sign") || ($structure["data-type"] == "label")) {
                    continue;
                }

                $titleIdentifier = sprintf("%s.%s", $table, "data_" . $structure["itemid"]);
                $structure["data-title"] = stripslashes(str_replace(array("<", ">"), array("&lt", "&gt"), $structure["data-title"]));
                $titleArr[] = array("key" => $titleIdentifier, "title" => $structure["data-title"]);
            }

            $data["deftitle"] = $defTitleArr;
            $data["title"] = $titleArr;
        }

        $data["lang"] = $lang;
        return $data;
    }
}
