<?php

class WorkflowNewController extends WorkflowBaseController
{
    public function actionIndex()
    {
        $data = array();
        $this->handleStartFlowList($data);
        $this->setPageTitle(Ibos::lang("Start work"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Workflow")),
            array("name" => Ibos::lang("Start work"), "url" => $this->createUrl("new/index")),
            array("name" => Ibos::lang("List"))
        ));
        $this->render("index", $data);
    }

    public function actionAdd()
    {
        $flowId = intval(EnvUtil::getRequest("flowid"));
        $flow = new ICFlowType($flowId);

        if (EnvUtil::submitCheck("formhash")) {
            $this->checkFlowAccess($flowId, 1, $this->createUrl("new/add"));
            $this->beforeAdd($_POST, $flow);
            $run = array("name" => $_POST["name"], "flowid" => $flowId, "beginuser" => $this->uid, "begintime" => TIMESTAMP);
            $runId = FlowRun::model()->add($run, true);
            $runProcess = array("runid" => $runId, "processid" => 1, "uid" => $this->uid, "flag" => FlowConst::PRCS_UN_RECEIVE, "flowprocess" => 1, "createtime" => TIMESTAMP);
            FlowRunProcess::model()->add($runProcess);

            if (strstr($flow->autoname, "{N}")) {
                FlowType::model()->updateCounters(array("autonum" => 1), sprintf("flowid = %d", $flowId));
                CacheUtil::rm("flowtype_" . $flowId);
            }

            $runData = array("runid" => $runId, "name" => $_POST["name"], "beginuser" => $this->uid, "begin" => TIMESTAMP);
            $this->handleRunData($flow, $runData);
            $param = array("flowid" => $flowId, "runid" => $runId, "processid" => 1, "flowprocess" => 1, "fromnew" => 1);
            $jumpUrl = $this->createUrl("form/index", array("key" => WfCommonUtil::param($param)));
            $this->ajaxReturn(array("isSuccess" => true, "jumpUrl" => $jumpUrl));
        } else {
            $this->checkFlowAccess($flowId, 1);

            if (!empty($flow->autoname)) {
                $runName = WfNewUtil::replaceAutoName($flow, $this->uid);
            } else {
                $runName = sprintf("%s (%s)", $flow->name, date("Y-m-d H:i:s"));
            }

            $data = array("flow" => $flow->toArray(), "runName" => $runName, "lang" => Ibos::getLangSources());
            $this->renderPartial("add", $data);
        }
    }

    protected function beforeAdd(&$data, ICFlowType $type)
    {
        $name = $data["name"];

        if (isset($data["prefix"])) {
            $name = $data["prefix"] . $name;
        }

        if (isset($data["suffix"])) {
            $name = $name . $data["suffix"];
        }

        $runName = StringUtil::filterCleanHtml($name);
        $runNameExists = FlowRun::model()->checkExistRunName($type->getID(), $runName);

        if ($runNameExists) {
            $this->error(Ibos::lang("Duplicate run name"));
        }

        $data["name"] = $runName;
    }

    protected function handleRunData(ICFlowType $type, &$runData)
    {
        $structure = $type->form->structure;

        foreach ($structure as $k => $v) {
            if (($v["data-type"] == "checkbox") && stristr($v["content"], "checkbox")) {
                if (stristr($v["content"], "checked") || stristr($v["content"], " checked=\"checked\"")) {
                    $itemData = "on";
                } else {
                    $itemData = "";
                }
            } elseif (!in_array($v["data-type"], array("select", "listview"))) {
                if (isset($v["data-value"])) {
                    $itemData = str_replace("\"", "", $v["data-value"]);

                    if ($itemData == "{macro}") {
                        $itemData = "";
                    }
                } else {
                    $itemData = "";
                }
            } else {
                $itemData = "";
            }

            $runData[strtolower($k)] = $itemData;
        }

        WfCommonUtil::addRunData($type->getID(), $runData, $structure);
    }

    protected function handleStartFlowList(&$data)
    {
        $flowList = $commonlyFlowList = $sort = array();
        $enabledFlowIds = WfNewUtil::getEnabledFlowIdByUid($this->uid);
        $commonlyFlowIds = FlowRun::model()->fetchCommonlyUsedFlowId($this->uid);

        foreach (FlowType::model()->fetchAll(array("order" => "sort,flowid")) as $flow) {
            $catId = $flow["catid"];
            $flowId = $flow["flowid"];

            if (!isset($flowList[$catId])) {
                $sort[$catId] = array();
                $cat = FlowCategory::model()->fetchByPk($catId);

                if ($cat) {
                    $sort[$catId] = $cat;
                }
            }

            if ($flow["usestatus"] == 3) {
                continue;
            }

            $enabled = in_array($flowId, $enabledFlowIds);
            if (!$enabled && ($flow["usestatus"] == 2)) {
                continue;
            }

            $flow["enabled"] = $enabled;

            if (in_array($flowId, $commonlyFlowIds)) {
                $commonlyFlowList[] = $flow;
            }

            $flowList[$catId][$flowId] = $flow;
        }

        ksort($flowList, SORT_NUMERIC);
        $data["flows"] = $flowList;
        $data["sort"] = $sort;
        $data["commonlyFlows"] = $commonlyFlowList;
    }
}
