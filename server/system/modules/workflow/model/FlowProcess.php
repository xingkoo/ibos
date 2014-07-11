<?php

class FlowProcess extends ICModel
{
    public static function model($className = "FlowProcess")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{flow_process}}";
    }

    public function addSpecialNode($flowId)
    {
        $this->add(array("flowid" => $flowId, "processid" => "-1", "name" => Ibos::lang("Start", "workflow.default")));
        $this->add(array("flowid" => $flowId, "processid" => "0", "name" => Ibos::lang("End", "workflow.default")));
    }

    public function getNotInAttachPer($runID, $flowID, $uid, $perstr = "4")
    {
        $result = $this->getDbConnection()->createCommand()->select("id")->from(sprintf("%s fp"), $this->tableName())->leftJoin(sprintf("{{%s}} frp", "flow_run_process"), "fp.processid = frp.flowprocess")->where(sprintf("frp.runid = %d AND fp.flowid = %d AND frp.uid = %d AND NOT FIND_IN_SET('%s',fp.attachpriv)", $runID, $flowID, $uid, $perstr))->queryScalar();
        return $result;
    }

    public function checkProcessUserByFlowId($flowId)
    {
        $emptyProcessUserStep = array();
        $criteria = array("select" => "processid,name", "condition" => "processid > 0 AND flowid = " . intval($flowId) . " AND uid = '' AND positionid = '' AND deptid = ''", "order" => "processid");
        $steps = $this->fetchAll($criteria);

        foreach ($steps as $step) {
            $emptyProcessUserStep[$step["processid"]] = $step["name"];
        }

        return $emptyProcessUserStep;
    }

    public function checkProcessCirculatingByFlowId($flowId)
    {
        $processInfo = $errorInfo = array();
        $tempId = $errorStr = "";
        $maxProcessId = 0;
        $criteria = array("select" => "processid,name,processto", "condition" => "flowid = " . intval($flowId), "order" => "processid");
        $steps = $this->fetchAll($criteria);

        foreach ($steps as $step) {
            $temp = array();
            $temp["id"] = $step["processid"];
            $temp["name"] = $step["name"];
            $temp["to"] = $step["processto"];

            if ($maxProcessId < $step["processid"]) {
                $maxProcessId = $step["processid"];
            }

            array_push($processInfo, $temp);
            $tempId .= $step["processid"] . ",";
        }

        foreach ($processInfo as $process) {
            $id = $process["id"];
            $name = $process["name"];
            $to = $process["to"];

            if (empty($to)) {
                if ($maxProcessId !== $id) {
                    $nextId = $id + 1;

                    if (StringUtil::findIn($tempId, $nextId)) {
                        continue;
                    }

                    $errorStr .= "$nextId";
                }
            } else {
                $processArr = explode(",", $to);

                foreach ($processArr as $toStep) {
                    if (!StringUtil::findIn($tempId, $toStep)) {
                        $errorStr .= $toStep . ",";
                    }
                }
            }

            $errorInfo[$id]["name"] = $name;
            $errorInfo[$id]["error"] = trim($errorStr, ",");
            $errorStr = "";
        }

        return $errorInfo;
    }

    public function checkWritableFieldByFlowId($flowId)
    {
        $result = array();
        $criteria = array("select" => "processid,name", "condition" => "flowid = " . intval($flowId) . " AND processitem = '' AND processid > 0", "order" => "processid");
        $steps = $this->fetchAll($criteria);

        foreach ($steps as $step) {
            $result[$step["processid"]] = $step["name"];
        }

        return $result;
    }

    public function fetchProcessto($flowId, $processId)
    {
        $processTo = Ibos::app()->db->createCommand()->select("processto")->from($this->tableName())->where(sprintf("flowid = %d AND processid = %d", $flowId, $processId))->queryScalar();
        return (string) $processTo;
    }

    public function fetchTimeoutInfo($flowID, $processID)
    {
        $criteria = array("select" => "name,timeouttype", "condition" => sprintf("processid = %d AND flowid = %d", $processID, $flowID), "limit" => 1);
        return $this->fetch($criteria);
    }

    public function fetchMaxProcessIDByFlowID($flowID)
    {
        $criteria = array("select" => "MAX(processid) as id", "condition" => sprintf("processid NOT IN (-1,0) AND flowid = %d", $flowID));
        $result = $this->fetch($criteria);
        return isset($result["id"]) ? intval($result["id"]) : 0;
    }

    public function fetchAllGatherNode($flowID, $processID)
    {
        $criteria = array("select" => "processid", "condition" => sprintf("flowid = %d AND (FIND_IN_SET('%d',processto) || (processid = %d AND processto = '')) AND processid > 0", $flowID, $processID, $processID - 1));
        $list = $this->fetchAll($criteria);
        return $list;
    }

    public function fetchName($flowId, $processId)
    {
        $criteria = array("select" => "name", "condition" => sprintf("flowid = %d AND processid = %d", $flowId, $processId));
        $result = $this->fetch($criteria);
        return isset($result["name"]) ? $result["name"] : "";
    }

    public function fetchSavePlugin($flowID, $processID)
    {
        $criteria = array("select" => "pluginsave", "condition" => sprintf("flowid = %d AND processid = %d", $flowID, $processID));
        $result = $this->fetch($criteria);
        return isset($result["pluginsave"]) ? $result["pluginsave"] : "";
    }

    public function fetchTurnPlugin($flowID, $processID)
    {
        $criteria = array("select" => "plugin", "condition" => sprintf("flowid = %d AND processid = %d", $flowID, $processID));
        $result = $this->fetch($criteria);
        return isset($result["plugin"]) ? $result["plugin"] : "";
    }

    public function fetchProcess($flowId, $processId)
    {
        $process = $this->fetchByAttributes(array("flowid" => $flowId, "processid" => $processId));
        return $process;
    }

    public function fetchRelationOut($flowId, $childFlow, $fp = 0)
    {
        if ($fp) {
            $sql = " AND processid = '$fp'";
        } else {
            $sql = "";
        }

        $criteria = array("select" => "relationout", "condition" => sprintf("flowid = %d AND childflow = %d %s", $flowId, $childFlow, $sql));
        $result = $this->fetch($criteria);
        return isset($result["relationout"]) ? $result["relationout"] : "";
    }

    public function fetchAllOtherProcess($flowId, $processId)
    {
        $criteria = array("select" => "processid,name", "condition" => sprintf("flowid = %d AND processid <> %d AND processid > 0", $flowId, $processId), "order" => "processid");
        $list = $this->fetchAll($criteria);
        return $list;
    }

    public function fetchAllByFlowId($flowId, $filterSpecial = false)
    {
        $filter = ($filterSpecial ? " AND processid > 0" : "");
        $criteria = array("condition" => "flowid = " . intval($flowId) . $filter, "order" => "processid DESC");
        return $this->fetchAll($criteria);
    }

    public function fetchAllProcessNameByFlowId($flowId)
    {
        $criteria = array("select" => "processid,name", "condition" => sprintf("flowid = %d AND processid > 0", $flowId), "order" => "processid");
        return $this->fetchAll($criteria);
    }

    public function fetchAllPreProcessName($flowId, $processId)
    {
        $criteria = array("select" => "id,name", "condition" => sprintf("FIND_IN_SET('%d',processto) AND flowid = %d", $processId, $flowId));
        return $this->fetchAll($criteria);
    }

    public function fetchAllProcessSortByFlowId()
    {
        $return = array();
        $criteria = array("select" => "flowid,processid,name", "condition" => "processid NOT IN (-1,0)");

        foreach ($this->fetchAll($criteria) as $process) {
            $return[$process["flowid"]][$process["processid"]]["name"] = $process["name"];
            $return[$process["flowid"]][$process["processid"]]["timeout"] = $process["timeout"];
        }

        return $return;
    }

    public function fetchAllFirstStepPermission()
    {
        $criteria = array("select" => "uid,deptid,positionid,flowid", "condition" => "processid = 1", "group" => "flowid");
        return $this->fetchAll($criteria);
    }

    public function del($flowId, $processId)
    {
        FlowProcess::model()->deleteAllByAttributes(array("flowid" => $flowId, "processid" => $processId));
        $criteria = array("select" => "id,processto", "condition" => sprintf("flowid = %d AND FIND_IN_SET('%d',processto)", $flowId, $processId));
        $list = $this->fetchAll($criteria);

        if (!empty($list)) {
            foreach ($list as $process) {
                $toIds = explode(",", $process["processto"]);
                $diff = array_diff($toIds, array($processId));
                $this->updateByPk($process["id"], array("processto" => implode(",", $diff)));
            }
        }
    }

    public function updateProcessto($flowid, $oldPrcsId, $newPrcsId)
    {
        $allProcess = FlowProcess::model()->fetchAll(sprintf("flowid = %d AND FIND_IN_SET(%d, `processto`)", $flowid, $oldPrcsId));

        if (!empty($allProcess)) {
            foreach ($allProcess as $processs) {
                $oldProcesstoArr = explode(",", $process["processto"]);
                $key = array_search($oldPrcsId, $oldProcesstoArr);

                if (isset($oldProcesstoArr[$key])) {
                    $oldProcesstoArr[$key] = $newPrcsId;
                    $newProcessto = implode(",", $oldProcesstoArr);
                    FlowProcess::model()->updateByPk($process["id"], array("processto" => $newProcessto));
                }
            }
        }
    }

    public function fetchAllProcessIdByFlowId($flowid)
    {
        $process = $this->fetchAllByFlowId($flowid);
        return ConvertUtil::getSubByKey($process, "processid");
    }

    public function checkProcessIdIsExist($flowid, $processid)
    {
        $processidArr = $this->fetchAllProcessIdByFlowId($flowid);
        $isExist = false;

        if (in_array($processid, $processidArr)) {
            $isExist = true;
        }

        return $isExist;
    }
}
