<?php

class WorkflowApi extends MessageApi
{
    public function renderIndex()
    {
        $data = array("todo" => $this->loadTodo(), "lang" => Ibos::getLangSource("workflow.default"), "assetUrl" => Ibos::app()->assetManager->getAssetsUrl("workflow"));
        $viewAlias = "application.modules.workflow.views.indexapi.workflow";
        $return["workflow"] = Ibos::app()->getController()->renderPartial($viewAlias, $data, true);
        return $return;
    }

    public function loadNew()
    {
        return intval(FlowRunProcess::model()->countByAttributes(array("uid" => Ibos::app()->user->uid, "flag" => 1)));
    }

    public function loadSetting()
    {
        return array("name" => "workflow", "title" => "我的工作", "style" => "in-workflow");
    }

    private function loadTodo($num = 4)
    {
        $uid = Ibos::app()->user->uid;
        $fields = array("frp.runid", "frp.processid", "frp.flowprocess", "ft.type", "frp.flag", "ft.flowid", "fr.name as runName", "fr.beginuser", "fr.focususer");
        $condition = array("and", "fr.delflag = 0", "frp.childrun = 0", sprintf("frp.uid = %d", $uid));
        $condition[] = array(
            "in",
            "frp.flag",
            array(1, 2)
            );
        $sort = "frp.createtime DESC";
        $group = "frp.id";
        $runProcess = Ibos::app()->db->createCommand()->select($fields)->from("{{flow_run_process}} frp")->leftJoin("{{flow_run}} fr", "frp.runid = fr.runid")->leftJoin("{{flow_type}} ft", "fr.flowid = ft.flowid")->where($condition)->order($sort)->group($group)->offset(0)->limit($num)->queryAll();
        $allProcess = FlowProcess::model()->fetchAllProcessSortByFlowId();

        foreach ($runProcess as &$run) {
            $run["user"] = User::model()->fetchByUid($run["beginuser"]);

            if ($run["type"] == 1) {
                if (isset($allProcess[$run["flowid"]][$run["flowprocess"]]["name"])) {
                    $run["stepname"] = $allProcess[$run["flowid"]][$run["flowprocess"]]["name"];
                } else {
                    $run["stepname"] = Ibos::lang("Process steps already deleted", "workflow.default");
                }
            } else {
                $run["stepname"] = Ibos::lang("Step", "", array("{step}" => $run["processid"]));
            }

            $run["focus"] = StringUtil::findIn($uid, $run["focususer"]);
            $param = array("runid" => $run["runid"], "flowid" => $run["flowid"], "processid" => $run["processid"], "flowprocess" => $run["flowprocess"]);
            $run["key"] = WfCommonUtil::param($param);
        }

        return $runProcess;
    }
}
