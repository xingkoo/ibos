<?php

class WorkflowFocusController extends WorkflowBaseController
{
    public function actionIndex()
    {
        if (isset($_GET["pagesize"])) {
            $this->setListPageSize($_GET["pagesize"]);
        }

        $key = StringUtil::filterCleanHtml(EnvUtil::getRequest("keyword"));
        $fields = array("frp.runid", "frp.processid", "frp.flowprocess", "frp.flag", "frp.opflag", "frp.processtime", "ft.freeother", "ft.flowid", "ft.name as typeName", "ft.type", "ft.listfieldstr", "fr.name as runName", "fr.beginuser", "fr.begintime", "fr.endtime", "fr.focususer");
        $sort = "frp.processtime";
        $group = "frp.runid";
        $condition = array("and", "fr.delflag = 0", "frp.childrun = 0", sprintf("frp.uid = %d", $this->uid), sprintf("FIND_IN_SET(fr.focususer,'%s')", $this->uid));

        if ($key) {
            $condition[] = array("like", "fr.runid", "%$key%");
            $condition[] = array("or like", "fr.name", "%$key%");
        }

        $count = Ibos::app()->db->createCommand()->select("count(*) as count")->from("{{flow_run_process}} frp")->leftJoin("{{flow_run}} fr", "frp.runid = fr.runid")->leftJoin("{{flow_type}} ft", "fr.flowid = ft.flowid")->where($condition)->group($group)->queryScalar();
        $pages = PageUtil::create($count, $this->getListPageSize());
        if ($key && $count) {
            $pages->params = array("keyword" => $key);
        }

        $offset = $pages->getOffset();
        $limit = $pages->getLimit();
        $list = Ibos::app()->db->createCommand()->select($fields)->from("{{flow_run_process}} frp")->leftJoin("{{flow_run}} fr", "frp.runid = fr.runid")->leftJoin("{{flow_type}} ft", "fr.flowid = ft.flowid")->where($condition)->order($sort)->group($group)->offset($offset)->limit($limit)->queryAll();
        $data = array_merge(array("pages" => $pages), $this->handleList($list));
        $this->setPageTitle(Ibos::lang("My focus"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Workflow")),
            array("name" => Ibos::lang(Ibos::lang("My focus")), "url" => $this->createUrl("focus/index")),
            array("name" => Ibos::lang("List"))
        ));
        $this->render("index", $data);
    }

    protected function handleList($runProcess)
    {
        $allProcess = FlowProcess::model()->fetchAllProcessSortByFlowId();

        foreach ($runProcess as &$run) {
            $run["user"] = User::model()->fetchByUid($run["beginuser"]);
            $rp = FlowRunProcess::model()->fetchCurrentNextRun($run["runid"], $this->uid);

            if (!empty($rp)) {
                $run["processid"] = $rp["processid"];
                $run["flowprocess"] = $rp["flowprocess"];
                $run["opflag"] = $rp["opflag"];
                $run["flag"] = $rp["flag"];
            }

            if ($run["type"] == 1) {
                if (isset($allProcess[$run["flowid"]][$run["flowprocess"]]["name"])) {
                    $run["stepname"] = $allProcess[$run["flowid"]][$run["flowprocess"]]["name"];
                } else {
                    $run["stepname"] = Ibos::lang("Process steps already deleted");
                }
            } else {
                $run["stepname"] = Ibos::lang("Step", "", array("{step}" => $run["processid"]));
            }

            $param = array("runid" => $run["runid"], "flowid" => $run["flowid"], "processid" => $run["processid"], "flowprocess" => $run["flowprocess"]);
            $run["key"] = WfCommonUtil::param($param);
        }

        return array("list" => $runProcess);
    }
}
