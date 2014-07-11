<?php

class FlowRunProcess extends ICModel
{
    public static function model($className = "FlowRunProcess")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{flow_run_process}}";
    }

    public function deleteByRunId($id)
    {
        return $this->deleteAllByAttributes(array("runid" => intval($id)));
    }

    public function getHasDefaultStep($runId, $processId)
    {
        $result = $this->getDbConnection()->createCommand()->select("id")->from($this->tableName())->where(array("and", sprintf("runid = %d", $runId), sprintf("processid >= %d", $processId), "flag = 5"))->queryScalar();
        return $result ? true : false;
    }

    public function getHasOtherOPUser($runId, $processId, $flowProcess, $uid)
    {
        $result = $this->getDbConnection()->createCommand()->select("id")->from($this->tableName())->where(array(
            "and",
            sprintf("runid = %d", $runId),
            sprintf("processid = %d", $processId),
            sprintf("flowprocess = %d", $flowProcess),
            sprintf("uid <> %d", $uid),
            array(
                "in",
                "flag",
                array(1, 2)
            )
        ))->queryScalar();
        return $result ? true : false;
    }

    public function getHasDownper($runId, $flowId, $uid)
    {
        $result = $this->getDbConnection()->createCommand()->select("frp.id")->from(sprintf("%s frp", $this->tableName()))->leftJoin(sprintf("{{%s}} fp", "flow_process"), "frp.flowprocess = fp.processid")->where(array("and", sprintf("frp.runid = %d", $runId), sprintf("flowid = %d", $flowId), sprintf("frp.uid = %d", $uid), "fp.attachpriv <> ''", "NOT FIND_IN_SET('4',attachpriv)"))->queryScalar();
        return $result ? $result : 0;
    }

    public function getIsAllowBack($runId, $processId, $flowProcess, $parent)
    {
        $criteria = array("select" => "1", "condition" => sprintf("runid = %d AND processid = %d AND opflag = 1 AND parent = %d AND flowprocess <> '%s'", $runId, $processId, $parent, $flowProcess));
        $result = $this->fetch($criteria);
        return $result ? true : false;
    }

    public function getIsAgentInTodo($uid, $runId, $processId)
    {
        $result = $this->getDbConnection()->createCommand()->select("id")->from($this->tableName())->where(array(
            "and",
            sprintf("runid = %d", $runId),
            sprintf("processid = %d", $processId),
            sprintf("uid = %d", $uid),
            array(
                "in",
                "flag",
                array(1, 2)
            )
        ))->queryScalar();
        return $result ? true : false;
    }

    public function getIsOp($uid, $runId, $processId = 0)
    {
        $queryStr = ($processId ? sprintf(" AND processid = %d", $processId) : "");
        $criteria = array("select" => "1", "condition" => sprintf("uid = %d AND runid = %d AND opflag = 1%s", $uid, $runId, $queryStr));
        $result = $this->fetch($criteria);
        return $result ? true : false;
    }

    public function getIsAgent($uid, $runId, $processId = 0, $flowprocess = 0)
    {
        $queryStr = "";
        $queryStr .= ($processId ? sprintf(" AND processid = %d", $processId) : "");
        $queryStr .= ($flowprocess ? sprintf(" AND flowprocess = %d", $flowprocess) : "");
        $criteria = array("select" => "1", "condition" => sprintf(" uid = %d AND runid = %d%s", $uid, $runId, $queryStr));
        $result = $this->fetch($criteria);
        return $result ? true : false;
    }

    public function getIsIllegal($runId, $processId, $flowProcess, $uid)
    {
        $result = $this->getDbConnection()->createCommand()->select("id")->from($this->tableName())->where(array(
            "and",
            sprintf("runid = %d", $runId),
            sprintf("processid = %d", $processId),
            sprintf("flowprocess = %d", $flowProcess),
            sprintf("uid = %d", $uid),
            array(
                "in",
                "flag",
                array(1, 2)
            )
        ))->limit(1)->queryScalar();
        return $result ? false : true;
    }

    public function getIsUntrans($runId, $processId)
    {
        $criteria = array("select" => "1", "condition" => sprintf("runid = %d AND flowprocess = %d AND flag <= 2", $runId, $processId));
        $result = $this->fetch($criteria);
        return $result ? true : false;
    }

    public function getIsUnique($runId)
    {
        $criteria = array("select" => "1", "condition" => sprintf("runid = %d AND flag IN ('1','2') AND ((topflag IN (0,1) AND opflag=1) OR topflag=2)", $runId));
        $result = $this->fetch($criteria);
        return $result ? true : false;
    }

    public function getIsOnlyOne($runId)
    {
        $criteria = array("select" => "1", "condition" => sprintf("runid = %d AND processid > 1", $runId));
        $result = $this->fetch($criteria);
        return $result ? false : true;
    }

    public function getIsNotOver($runId)
    {
        $criteria = array("select" => "1", "condition" => sprintf("runid = %d AND flag != '4'", $runId));
        $result = $this->fetch($criteria);
        return $result ? true : false;
    }

    public function getIsOpOnTurn($runId, $processId, $flowProcess)
    {
        $criteria = array("select" => "1", "condition" => sprintf("runid = %d AND processid = %d AND flowprocess = %d AND opflag = 1 AND topflag = 0 AND flag IN ('1','2')", $runId, $processId, $flowProcess));
        $result = $this->fetch($criteria);
        return $result ? true : false;
    }

    public function getIsParentOnTakeBack($runID, $processID, $flowprocess)
    {
        $criteria = array("select" => "1", "condition" => sprintf("runid = %d AND processid = %d AND flag IN ('2','3','4') AND (parent='' OR FIND_IN_SET('%d',parent))", $runID, $processID, $flowprocess));
        $result = $this->fetch($criteria);
        return $result ? true : false;
    }

    public function getHasOtherAgentNotDone($runID, $processID)
    {
        $criteria = array("select" => "1", "condition" => sprintf("runid = %d AND processid = %d AND opflag = 0 AND flag <> '4'", $runID, $processID));
        $result = $this->fetch($criteria);
        return $result ? true : false;
    }

    public function fetchOpUserByUniqueID($runId, $processId, $flowProcess)
    {
        $criteria = array("condition" => sprintf("runid = %d AND processid = %d AND flowprocess = %d AND opflag = 1", $runId, $processId, $flowProcess));
        return $this->fetch($criteria);
    }

    public function fetchMaxIDByRunID($runId)
    {
        $criteria = array("select" => "MAX(processid) as processid", "condition" => sprintf("runid = %d", $runId));
        $result = $this->fetch($criteria);
        return isset($result["processid"]) ? intval($result["processid"]) : 0;
    }

    public function fetchProcessIDOnTurn($runId, $processId, $flowProcess, $uid, $gatherNode = 0)
    {
        if (!$gatherNode) {
            $sqladd = "AND processid='$processId'";
        } else {
            $sqladd = "";
        }

        $criteria = array("select" => "processid", "condition" => sprintf("runid = %d AND flowprocess = %d AND uid = %d AND flag IN ('1','2') %s", $runId, $flowProcess, $uid, $sqladd));
        $result = $this->fetch($criteria);
        return isset($result["processid"]) ? intval($result["processid"]) : 0;
    }

    public function fetchTopflag($runId, $processId, $flowProcess)
    {
        $criteria = array("select" => "topflag", "condition" => sprintf("runid = %d AND processid=%d AND flowprocess=%d AND topflag IN ('1','2')", $runId, $processId, $flowProcess));
        $result = $this->fetch($criteria);
        return isset($result["topflag"]) ? intval($result["topflag"]) : 0;
    }

    public function fetchBaseUid($runId, $processId)
    {
        $criteria = array("select" => "uid", "condition" => sprintf("runid = %d AND flowprocess = %d AND opflag = 1", $runId, $processId), "order" => "processid", "limit" => 1);
        $result = $this->fetch($criteria);
        return isset($result["uid"]) ? intval($result["uid"]) : 0;
    }

    public function fetchDeliverTime($runId, $processId)
    {
        $criteria = array("select" => "delivertime", "condition" => sprintf("runid = %d AND processid = %d", $runId, $processId));
        $result = $this->fetch($criteria);
        return isset($result["delivertime"]) ? $result["delivertime"] : 0;
    }

    public function fetchTimeoutRecord($runId, $processId)
    {
        $criteria = array("select" => "flowprocess,processtime,createtime,delivertime", "condition" => sprintf("runid = %d AND processid = %d AND timeoutflag = 1", $runId, $processId));
        $result = $this->fetch($criteria);
        return $result;
    }

    public function fetchCurrentNextRun($runId, $uid, $flag = "")
    {
        $criteria = array("select" => "*", "condition" => sprintf("runid = %d AND uid = %d%s", $runId, $uid, $flag ? " AND flag IN ('$flag')" : ""), "order" => "processid DESC", "limit" => 1);
        return $this->fetch($criteria);
    }

    public function fetchRunProcess($runId, $processId, $flowProcess, $uid)
    {
        return $this->fetchByAttributes(array("runid" => $runId, "processid" => $processId, "flowprocess" => $flowProcess, "uid" => $uid));
    }

    public function fetchIDByChild($runId, $childRun)
    {
        $criteria = array("select" => "uid,processid,flowprocess", "condition" => sprintf("runid = %d AND childrun = %d", $runId, $childRun));
        return $this->fetch($criteria);
    }

    public function fetchNotDoneOpuser($runId, $processId)
    {
        $criteria = array("select" => "uid", "condition" => sprintf("runid = %d AND processid = %d AND opflag='1' AND flag <> '4'", $runId, $processId));
        $result = $this->fetch($criteria);
        return isset($result["uid"]) ? $result["uid"] : 0;
    }

    public function fetchFreeitem($runId, $processId)
    {
        $criteria = array("select" => "freeitem", "condition" => sprintf("runid = %d AND processid = %d AND opflag='1'", $runId, $processId));
        $result = $this->fetch($criteria);
        return isset($result["freeitem"]) ? $result["freeitem"] : "";
    }

    public function fetchAllProcessByFlowProcess($runId, $processId, $flowProcess)
    {
        $criteria = array("condition" => sprintf("runid = '%d' AND processid = '%d' AND flowprocess = '%d'", $runId, $processId, $flowProcess), "order" => "flag DESC , processtime");
        return $this->fetchAll($criteria);
    }

    public function fetchAllOPUid($runId, $processId, $isOP = true)
    {
        if ($isOP) {
            $sqlAdd = "AND opflag = 1";
        } else {
            $sqlAdd = "AND opflag = 0";
        }

        $criteria = array("select" => "uid", "condition" => sprintf("runid = %d AND processid = %d %s", $runId, $processId, $sqlAdd));
        return $this->fetchAll($criteria);
    }

    public function fetchAllIDByRunID($runId)
    {
        $criteria = array("select" => "uid,processid,flowprocess", "condition" => sprintf("runid = %d", $runId));
        return $this->fetchAll($criteria);
    }

    public function fetchAllByRunIDProcessID($runId, $processId)
    {
        $criteria = array("condition" => sprintf("runid = %d AND processid = %d", $runId, $processId));
        return $this->fetchAll($criteria);
    }

    public function fetchAllUidByRealProcess($runId, $processId, $flowProcess)
    {
        $criteria = array("select" => "uid", "condition" => sprintf("runid = %d AND processid = %d AND flowprocess = %d", $runId, $processId, $flowProcess));
        $ids = $this->fetchAll($criteria);
        return ConvertUtil::getSubByKey($ids, "uid");
    }

    public function fetchAllProcessByProcessID($runId, $processId)
    {
        $criteria = array("select" => "*", "condition" => sprintf("runid = %d AND processid = %d", $runId, $processId), "group" => "flowprocess");
        return $this->fetchAll($criteria);
    }

    public function addDelay($time, $runId, $processId, $flowProcess)
    {
        $set = array("flag" => 6, "activetime" => $time);
        return $this->updateAll($set, sprintf("runid = %d AND processid = %d AND flowprocess = %d", $runId, $processId, $flowProcess));
    }

    public function restoreDelay($runId, $processId, $flowProcess, $uid = "")
    {
        $set = array("flag" => 2, "activetime" => 0);
        $result = $this->updateAll($set, sprintf("runid = %d AND processid = %d AND flowprocess = %d", $runId, $processId, $flowProcess));

        if (!empty($uid)) {
            $runName = FlowRun::model()->fetchNameByRunID($runId);
            Notify::model()->sendNotify($uid, "workflow_restore_delay_notice", array("{runname}" => $runName));
        }

        return $result;
    }

    public function fetchAllUidByRunId($runId)
    {
        $criteria = array("select" => "uid", "condition" => sprintf("runid = %d", $runId));
        $uids = $this->fetchAll($criteria);
        return ConvertUtil::getSubByKey($uids, "uid");
    }

    public function fetchAllByRunID($runId)
    {
        return $this->fetchAllByAttributes(array("runid" => $runId));
    }

    public function updateTurn($flowprocess, $processId, $runId, $lastPrcsId, $flowPrcsNext, $uid)
    {
        $sqltext = sprintf("UPDATE %s SET parent = CONCAT(parent,',%d'),processid = %d WHERE runid = %d AND processid = %d AND flowprocess = %d AND uid = %d AND flag IN('1','2')", $this->tableName(), $flowprocess, $processId, $runId, $lastPrcsId, $flowPrcsNext, $uid);
        return Ibos::app()->db->createCommand()->setText($sqltext)->execute();
    }

    public function updateToOver($runId, $processId, $flowProcess)
    {
        $set = array("delivertime" => TIMESTAMP, "flag" => "4");
        return $this->updateAll($set, sprintf("runid = %d AND processid = %d AND flowprocess = %d AND flag IN ('1','2')", $runId, $processId, $flowProcess));
    }

    public function updateToTrans($runId, $processId, $flowProcess)
    {
        $set = array("delivertime" => TIMESTAMP, "flag" => 3);
        return $this->updateAll($set, sprintf("runid = %d AND processid = %d AND flowprocess = %d AND flag IN ('1','2')", $runId, $processId, $flowProcess));
    }

    public function updateTransRun($uid, $toid, $runIds)
    {
        $criteria = array("select" => "runid,processid", "condition" => sprintf("FIND_IN_SET(runid,'%s') AND uid = %d AND flag!=3 AND flag!=4", $runIds, $uid));
        $fitProcess = $this->fetchAll($criteria);

        foreach ($fitProcess as $process) {
            $this->updateAll(array("uid" => $toid, "fromuser" => $uid), sprintf("processid = %d AND runid = %d", $process["processid"], $process["runid"]));
        }
    }

    public function updateTop($uid, $runId, $processId, $flowProcess)
    {
        $criteria = array("condition" => sprintf("runid = %d AND processid = %d AND flowprocess = %d AND uid NOT IN('%s')", $runId, $processId, $flowProcess, $uid));
        $this->updateAll(array("opflag" => 0), $criteria);
    }

    public function updateProcessTime($runId, $processId, $flowProcess, $uid)
    {
        return $this->updateAll(array("processtime" => TIMESTAMP), sprintf("runid = %d AND processid = %d AND flowprocess = %d AND uid = %d AND flag = 1 AND processtime IS NULL", $runId, $processId, $flowProcess, $uid));
    }

    public function updateRedo($runId, $processId, $uid, $flowProcess = 0)
    {
        $set = array("flag" => 2);
        return $this->updateAll($set, array("condition" => sprintf("runid = %d AND processid = %d AND uid = %d AND flag IN('3','4')%s", $runId, $processId, $uid, $flowProcess ? " AND flowprocess = " . $flowProcess : "")));
    }

    public function deleteByIDScope($runId, $processId)
    {
        return $this->deleteAll(sprintf("runid = %d AND processid >= %d", $runId, $processId));
    }

    public function countByRunID($runId)
    {
        return $this->countBySql(sprintf("SELECT COUNT(DISTINCT processid) FROM %s WHERE runid = %d", $this->tableName(), $runId));
    }
}
