<?php

class FlowRun extends ICModel
{
    public static function model($className = "FlowRun")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{flow_run}}";
    }

    public function countAllByFlowId($flowId)
    {
        return (int) $this->countByAttributes(array("flowid" => $flowId));
    }

    public function countDelFlowByFlowId($flowId)
    {
        return (int) $this->countByAttributes(array("flowid" => $flowId, "delflag" => 1));
    }

    public function checkExistRunName($flowId, $runName)
    {
        return $this->countByAttributes(array("flowid" => $flowId, "name" => $runName));
    }

    public function getMaxRunId()
    {
        $max = $this->getDbConnection()->createCommand()->select("MAX(runid) as max")->from($this->tableName())->queryScalar();
        return intval($max);
    }

    public function setFocus($setFocus, $runId, $uid)
    {
        $run = $this->fetchByPk(intval($runId));
        $focusUser = $run["focususer"];

        if ($setFocus) {
            if (StringUtil::findIn($focusUser, $uid)) {
                return false;
            } else {
                $focusUser = array_unique(array_merge(array($uid), !empty($focusUser) ? explode(",", $focusUser) : array()));
                $allUser = FlowRunProcess::model()->fetchAllUidByRunId($runId);

                if (!empty($allUser)) {
                    $config = array("{runName}" => $run["name"], "{userName}" => User::model()->fetchRealNameByUid($uid));
                    Notify::model()->sendNotify($allUser, "workflow_focus_notice", $config);
                }
            }
        } elseif (!StringUtil::findIn($focusUser, $uid)) {
            return false;
        } else {
            $userPart = explode(",", $focusUser);
            $index = array_search($uid, $userPart);

            if (is_int($index)) {
                unset($userPart[$index]);
            }

            $focusUser = $userPart;
        }

        return $this->modify($runId, array("focususer" => implode(",", $focusUser)));
    }

    public function fetchBeginUserByRunID($runId)
    {
        $criteria = array("select" => "beginuser", "condition" => sprintf("runid = %d", $runId));
        $run = $this->fetch($criteria);
        return isset($run["beginuser"]) ? intval($run["beginuser"]) : 0;
    }

    public function fetchParentRun($runId)
    {
        $criteria = array("select" => "parentrun", "condition" => sprintf("runid = %d AND parentrun <> 0", $runId));
        $result = $this->fetch($criteria);
        return isset($result["parentrun"]) ? intval($result["parentrun"]) : 0;
    }

    public function fetchFlowTypeByRunId($runId)
    {
        $result = Ibos::app()->db->createCommand()->select("type")->from("{{flow_run}} fr")->leftJoin("{{flow_type}} ft", "fr.flowid = ft.flowid")->where("runid = " . intval($runId))->queryScalar();
        return $result;
    }

    public function fetchFlowIdByRunId($runId)
    {
        $criteria = array("select" => "flowid", "condition" => sprintf("runid = %d", $runId));
        $run = $this->fetch($criteria);
        return isset($run["flowid"]) ? intval($run["flowid"]) : 0;
    }

    public function fetchParentByRunID($runId)
    {
        $criteria = array("select" => "parentrun", "condition" => sprintf("runid = %d", $runId));
        $run = $this->fetch($criteria);
        return isset($run["parentrun"]) ? intval($run["parentrun"]) : 0;
    }

    public function fetchNameByRunID($runId)
    {
        $criteria = array("select" => "name", "condition" => sprintf("runid = %d", $runId));
        $run = $this->fetch($criteria);
        return isset($run["name"]) ? $run["name"] : "";
    }

    public function fetchCommonlyUsedFlowId($uid, $num = 8)
    {
        $criteria = array("select" => "count(flowid) as count,flowid", "condition" => sprintf("beginuser = %d", $uid), "group" => "flowid", "order" => "count DESC", "offset" => 0, "limit" => $num);
        $ids = $this->fetchAll($criteria);
        return ConvertUtil::getSubByKey($ids, "flowid");
    }

    public function fetchAllMyRunID($uid, $flowId = null)
    {
        $list = $this->getDbConnection()->createCommand()->select("fr.runid")->from(sprintf("%s fr", $this->tableName()))->leftJoin(sprintf("{{%s}} ft", "flow_type"), "fr.flowid = ft.flowid")->leftJoin(sprintf("{{%s}} frp", "flow_run_process"), "fr.runid = frp.runid")->where(sprintf("uid = %d AND frp.flag<>5 %s", $uid, $flowId ? "AND ft.flowid = " . intval($flowId) : ""))->group("frp.runid")->queryAll();
        return ConvertUtil::getSubByKey($list, "runid");
    }

    public function fetchAllEndByFlowID($flowID)
    {
        $criteria = array("select" => "endtime", "condition" => "delflag = 0 AND flowid = " . intval($flowID));
        return $this->fetchAll($criteria);
    }

    public function fetchAllByFlowId($flowId)
    {
        return $this->fetchAllByAttributes(array("flowid" => intval($flowId)));
    }

    public function fetchAllAttachID()
    {
        $criteria = array("select" => "runid,attachmentid", "condition" => "attachmentid != ''");
        return $this->fetchAll($criteria);
    }

    public function fetchAllRunIdByFlowIdFeatCondition($flowId, $conArr)
    {
        $condition = array();
        if (!empty($conArr["begin"]) && !empty($conArr["end"])) {
            $condition[] = " AND (begintime > {$conArr["begin"]} AND endtime <= {$conArr["end"]})";
        } elseif (!empty($conArr["begin"])) {
            $condition[] = " AND begintime >= {$conArr["begin"]}";
        } elseif (!empty($conArr["end"])) {
            $condition[] = " AND endtime < {$conArr["begin"]}";
        }

        if (!empty($conArr["runbegin"]) && !empty($conArr["runend"])) {
            $condition[] = " AND runid BETWEEN {$conArr["runbegin"]} AND {$conArr["runend"]}";
        } elseif (!empty($conArr["runbegin"])) {
            $condition[] = " AND runid > {$conArr["runbegin"]}";
        } elseif (!empty($conArr["runbegin"])) {
            $condition[] = " AND runid < {$conArr["runend"]}";
        }

        $condition[] = " AND FIND_IN_SET(flowid,'$flowId')";
        $criteria = array("select" => "runid", "condition" => sprintf("1 %s", implode("", $condition)));
        $result = $this->fetchAll($criteria);
        $runId = ConvertUtil::getSubByKey($result, "runid");
        return implode(",", $runId);
    }

    public function del($id, $uid)
    {
        $ids = (is_array($id) ? $id : explode(",", $id));
        $count = 0;
        $logContent = Ibos::lang("Del run", "workflow.default");

        foreach ($ids as $runID) {
            $per = WfCommonUtil::getRunPermission($runID, $uid, 1);
            $isOnly = FlowRunProcess::model()->getIsOnlyOne($runID);
            if (!StringUtil::findIn($per, 2) && $isOnly && !StringUtil::findIn($per, 1) && !StringUtil::findIn($per, 3)) {
                continue;
            }

            if ($this->modify($runID, array("delflag" => 1))) {
                $count++;
                WfCommonUtil::runlog($runID, 0, 0, $uid, 3, $logContent);
            }
        }

        return $count;
    }
}
