<?php

class ICFlowProcess extends ICFlowBase
{
    public function __construct()
    {
        $args = func_get_args();
        $nums = func_num_args();

        if ($nums == 2) {
            call_user_func_array(array($this, "setByProcess"), $args);
        } elseif ($nums == 1) {
            call_user_func_array(array($this, "setByID"), $args);
        }
    }

    public function getID()
    {
        return $this->id;
    }

    protected function setByID($id)
    {
        $attr = FlowProcess::model()->fetchByPk(intval($id));
        $this->setAttributes($attr);
    }

    protected function setByProcess($flowId, $processId)
    {
        $attr = FlowProcess::model()->fetchByAttributes(array("flowid" => intval($flowId), "processid" => intval($processId)));
        $this->setAttributes($attr);
    }

    public function getProcessInfo()
    {
        $data = array("name" => $this->name);
        $preProcessName = FlowProcess::model()->fetchAllPreProcessName($this->flowid, $this->processid);

        foreach ($preProcessName as $key => $value) {
            $data["pre"][$key] = $value["name"];
        }

        if (!empty($this->processto)) {
            foreach (explode(",", $this->processto) as $key => $toId) {
                $toId = intval($toId);

                if ($toId == 0) {
                    $data["next"][$key] = Ibos::lang("End");
                } else {
                    $next = FlowProcess::model()->fetchProcess($this->flowid, $toId);
                    $data["next"][$key] = $next["name"];
                }

                if (isset($next) && !empty($next["processin"])) {
                    $data["prcsout"][$key]["name"] = $next["name"];
                    $data["prcsout"][$key]["con"] = $next["processin"];
                }
            }
        }

        if (!empty($this->processitem)) {
            $itemPart = explode(",", $this->processitem);
            $data["processitem"] = $this->processitem;
            $data["itemcount"] = count($itemPart);
        } else {
            $data["processitem"] = "";
            $data["itemcount"] = 0;
        }

        if (!empty($this->hiddenitem)) {
            $itemPart = explode(",", $this->hiddenitem);
            $data["hiddenitem"] = $this->hiddenitem;
            $data["hiddencount"] = count($itemPart);
        } else {
            $data["hiddenitem"] = "";
            $data["hiddencount"] = 0;
        }

        if (!empty($this->uid)) {
            $data["user"] = User::model()->fetchRealnamesByUids($this->uid);
        } else {
            $data["user"] = "";
        }

        if (!empty($this->deptid)) {
            $data["dept"] = Department::model()->fetchDeptNameByDeptId($this->deptid);
        } else {
            $data["dept"] = "";
        }

        if (!empty($this->positionid)) {
            $data["position"] = Position::model()->fetchPosNameByPosId($this->positionid);
        } else {
            $data["position"] = "";
        }

        return $data;
    }
}
