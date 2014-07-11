<?php

class WorkflowEntrustController extends WorkflowBaseController
{
    public function actionIndex()
    {
        $op = EnvUtil::getRequest("op");

        if (!in_array($op, array("rule", "berule", "record", "berecord"))) {
            $op = "rule";
        }

        $this->setPageTitle(Ibos::lang("Work entrust"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Workflow")),
            array("name" => Ibos::lang(Ibos::lang("Work entrust")), "url" => $this->createUrl("recycle/index")),
            array("name" => Ibos::lang("List"))
        ));
        $data = array("op" => $op);
        $this->render($op, array_merge($data, $this->getListData($op)));
    }

    public function actionAdd()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $begin = (empty($_POST["begindate"]) ? "" : strtotime($_POST["begindate"]));
            $end = (empty($_POST["enddate"]) ? "" : strtotime($_POST["enddate"]));
            $toId = implode(",", StringUtil::getId($_POST["uid"]));
            $flowId = intval($_POST["flowid"]);
            $data = array("flowid" => $flowId, "toid" => $toId, "uid" => $this->uid, "begindate" => $begin, "enddate" => $end, "status" => 1);
            FlowRule::model()->add($data);
            $this->redirect($this->createUrl("entrust/index"));
        } else {
            $list = WfCommonUtil::getFlowList($this->uid);
            $this->renderPartial("add", array("flowlist" => $list, "lang" => Ibos::getLangSources()));
        }
    }

    public function actionStatus()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $id = EnvUtil::getRequest("id");
            $ruleID = StringUtil::filterStr($id);
            $flag = intval(EnvUtil::getRequest("flag"));
            FlowRule::model()->updateAll(array("status" => $flag), sprintf("FIND_IN_SET(ruleid,'%s') AND uid = %d", $ruleID, $this->uid));
            $this->ajaxReturn(array("isSuccess" => true));
        }
    }

    public function actionDel()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $id = EnvUtil::getRequest("id");
            $ruleId = StringUtil::filterStr($id);

            if (!empty($ruleId)) {
                $res = FlowRule::model()->deleteAll(sprintf("FIND_IN_SET(ruleid,'%s') AND uid = %d", $ruleId, $this->uid));
                $this->ajaxReturn(array("isSuccess" => !!$res));
            }
        }
    }

    public function actionConfirmPost()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $key = EnvUtil::getRequest("key");
            $param = WfCommonUtil::param($key, "DECODE");
            $runId = intval($param["runid"]);
            $processId = intval($param["processid"]);
            $flowId = intval($param["flowid"]);
            $flowProcess = intval($param["flowprocess"]);
            $opflag = intval($_POST["opflag"]);
            $oldUid = intval($_POST["oldUid"]);
            $this->checkRunAccess($runId);
            $this->checkEntrustType($flowId);
            $referer = EnvUtil::referer();
            $frp = FlowRunProcess::model()->fetchRunProcess($runId, $processId, $flowProcess, $oldUid);

            if ($frp) {
                $parent = $frp["parent"];
                $topflag = $frp["topflag"];
            }

            $toid = implode(",", StringUtil::getId($_POST["prcs_other"]));
            $tempFRP = FlowRunProcess::model()->fetchRunProcess($runId, $processId, $flowProcess, $toid);

            if (!$tempFRP) {
                $data = array("runid" => $runId, "processid" => $processId, "uid" => $toid, "flag" => 1, "flowprocess" => $flowProcess, "opflag" => $opflag, "topflag" => $topflag, "parent" => $parent, "createtime" => TIMESTAMP);
                FlowRunProcess::model()->add($data);
            } else {
                if (($tempFRP["opflag"] == 0) && ($opflag == 1)) {
                    FlowRunProcess::model()->updateAll(array("opflag" => 1, "flag" => 2), sprintf("runid = %d AND processid = %d AND flowprocess = %d AND uid = %d", $runId, $processId, $flowProcess, $toid));
                } else {
                    $name = User::model()->fetchRealnameByUid($toid);
                    $this->error(Ibos::lang("Already are opuser", "", array("{name}" => $name)), $referer);
                }
            }

            FlowRunProcess::model()->updateProcessTime($runId, $processId, $flowProcess, $oldUid);
            FlowRunProcess::model()->updateAll(array("flag" => 4, "opflag" => 0, "delivertime" => TIMESTAMP), "runid = :runid AND processid = :prcsid AND flowprocess = :fp AND uid = :uid", array(":runid" => $runId, ":prcsid" => $processId, ":fp" => $flowProcess, ":uid" => $oldUid));
            $toName = User::model()->fetchRealnameByUid($toid);
            $userName = User::model()->fetchRealnameByUid($oldUid);
            $content = Ibos::lang("Entrust to desc", "", array("{username}" => $userName, "{toname}" => $toName));
            WfCommonUtil::runlog($runId, $processId, $flowProcess, $this->uid, 2, $content, $toid);
            $message = StringUtil::filterCleanHtml($_POST["message"]);

            if (!empty($message)) {
                Notify::model()->sendNotify($toid, "workflow_entrust_notice", array("{message}" => $message));
            }

            $this->redirect($referer);
        }
    }

    public function actionConfirm()
    {
        $key = EnvUtil::getRequest("key");

        if ($key) {
            $param = WfCommonUtil::param($key, "DECODE");
            $runId = intval($param["runid"]);
            $processId = intval($param["processid"]);
            $flowId = intval($param["flowid"]);
            $flowProcess = intval($param["flowprocess"]);
            $manager = EnvUtil::getRequest("manager");
            $this->checkRunPermission($runId);
            !$manager && $this->checkAgentInTodo($runId, $processId);
            $this->checkEntrustType($flowId);
            $this->checkRunDel($runId);
            $nextId = $this->getNextProcessID($flowId, $processId);

            if ($manager) {
                $rp = FlowRunProcess::model()->fetchOpUserByUniqueID($runId, $processId, $flowProcess);
            } else {
                $rp = FlowRunProcess::model()->fetchRunProcess($runId, $processId, $flowProcess, $this->uid);
            }

            $prcsUser = WfHandleUtil::getEntrustUser($flowId, $runId, $processId, $nextId);
            $data = array("runName" => FlowRun::model()->fetchNameByRunID($runId), "opflag" => $rp["opflag"], "processID" => $processId, "flowProcess" => $flowProcess, "oldUid" => $rp["uid"], "runID" => $runId, "key" => $key, "lang" => Ibos::getLangSources(), "prcsUser" => sprintf("[%s]", !empty($prcsUser) ? StringUtil::iImplode($prcsUser) : ""));
            $data = array_merge($data, $this->handleList($flowId, $runId, $processId));
            $this->renderPartial("confirm", $data);
        } else {
            exit(Ibos::lang("Parameters error", "error"));
        }
    }

    protected function checkRunPermission($runId)
    {
        $per = WfCommonUtil::getRunPermission($runId, $this->uid);

        if ($per == 5) {
            exit(Ibos::lang("Permission denied"));
        }
    }

    protected function checkAgentInTodo($runId, $processId)
    {
        $agentInTodo = FlowRunProcess::model()->getIsAgentInTodo($this->uid, $runId, $processId);

        if (!$agentInTodo) {
            exit(Ibos::lang("Permission denied"));
        }
    }

    protected function checkEntrustType($flowId)
    {
        $freeOther = FlowType::model()->fetchFreeOtherByFlowID($flowId);

        if ($freeOther == 0) {
            exit(Ibos::lang("No entrust permission"));
        }
    }

    protected function checkRunDel($runId)
    {
        $isDel = FlowRun::model()->countByAttributes(array("delflag" => 1, "runid" => $runId));

        if ($isDel) {
            exit(Ibos::lang("Run instance has been deleted"));
        }
    }

    protected function handleList($flowId, $runId, $processId)
    {
        $processNameArr = $list = array();

        for ($pId = 1; $pId <= $processId; $pId++) {
            $process = array("count" => 0);
            $processes = FlowRunProcess::model()->fetchAllProcessByProcessID($runId, $pId);

            foreach ($processes as $value) {
                $process["count"]++;
                $process["flowprocess"] = $flowProcess = $value["flowprocess"];
                $uids = FlowRunProcess::model()->fetchAllUidByRealProcess($runId, $pId, $flowProcess);
                $process["userName"] = User::model()->fetchRealnamesByUids($uids);

                if (!isset($processNameArr[$flowProcess])) {
                    $name = FlowProcess::model()->fetchName($flowId, $flowProcess);
                    $name && ($processNameArr[$flowProcess] = $name);
                }
            }

            $list[$pId] = $process;
        }

        return array("list" => $list, "prcsName" => $processNameArr);
    }

    protected function getNextProcessID($flowId, $processId)
    {
        $maxId = FlowProcess::model()->fetchMaxProcessIDByFlowID($flowId);

        if (($maxId - $processId) == 0) {
            $nextId = $maxId;
        } elseif (0 < ($maxId - $processId)) {
            $nextId = $processId + 1;
        }

        return $nextId;
    }

    protected function getListData($op)
    {
        switch ($op) {
            case "rule":
            case "berule":
                if ($op == "rule") {
                    $where = "fr.uid = $this->uid";
                } else {
                    $where = "fr.toid = $this->uid AND fr.status = 1";
                }

                $sqlText = "SELECT fr.*,ft.name as typeName,fr.toid as userID FROM {{flow_rule}} fr LEFT JOIN {{flow_type}} ft ON ft.flowid = fr.flowid LEFT JOIN {{user}} u ON fr.toid = u.uid WHERE $where ORDER BY fr.ruleid DESC";
                break;

            case "record":
            case "berecord":
                if ($op == "record") {
                    $idField = "uid";
                } else {
                    $idField = "toid";
                }

                $sqlText = "SELECT log.flowid,log.runid,log.runname,log.processid,log.toid as userID,log.time,frp.flag,frp.flowprocess,ft.type,ft.name as typeName,ft.flowid FROM {{flow_run_log}} log INNER JOIN {{flow_type}} ft ON log.flowid = ft.flowid LEFT JOIN {{flow_run_process}} frp ON frp.runid = log.runid WHERE frp.processid = log.processid AND log.$idField = $this->uid AND log.type = 2 GROUP BY log.processid ORDER BY log.runid DESC";
                break;
        }

        $query = Ibos::app()->db->createCommand()->setText($sqlText)->query();
        $count = $query->count();
        $pages = PageUtil::create($count, $this->getListPageSize());
        $offset = $pages->getOffset();
        $limit = $pages->getLimit();
        $list = Ibos::app()->db->createCommand()->setText($sqlText . " LIMIT $offset,$limit")->queryAll();
        $now = strtotime(date("Y-m-d", TIMESTAMP));
        $proceses = FlowProcess::model()->fetchAllProcessSortByFlowId();

        foreach ($list as &$rec) {
            if (($op == "rule") || ($op == "berule")) {
                $condition1 = WfHandleUtil::compareTimestamp($now, $rec["begindate"]);
                $condition2 = WfHandleUtil::compareTimestamp($now, $rec["enddate"]);
                $rec["enabled"] = false;
                $rec["datedesc"] = "";

                if ($rec["status"] == 1) {
                    if (($rec["begindate"] != 0) && ($rec["enddate"] != 0)) {
                        $rec["datedesc"] = date("Y-m-d", $rec["begindate"]) . "--" . date("Y-m-d", $rec["enddate"]);
                        if ((0 <= $condition1) && ($condition2 <= 0)) {
                            $rec["enabled"] = true;
                        }
                    } elseif ($rec["begindate"] != 0) {
                        $rec["datedesc"] = Ibos::lang("Entrust begin with", "", array("{date}" => date("Y-m-d", $rec["begindate"])));

                        if (0 <= $condition1) {
                            $rec["enabled"] = true;
                        }
                    } elseif ($rec["enddate"] != 0) {
                        $rec["datedesc"] = Ibos::lang("Entrust finish up width", "", array("{date}" => date("Y-m-d", $rec["enddate"])));

                        if ($condition2 <= 0) {
                            $rec["enabled"] = true;
                        }
                    } else {
                        $rec["datedesc"] = Ibos::lang("Always effective");
                        $rec["enabled"] = true;
                    }
                }
            } else {
                $rec["key"] = WfCommonUtil::param(array("runid" => $rec["runid"], "processid" => $rec["processid"], "flowprocess" => $rec["flowprocess"], "flowid" => $rec["flowid"]));

                if ($rec["type"] == 1) {
                    if (isset($proceses[$rec["flowid"]][$rec["flowprocess"]])) {
                        $rec["processname"] = $proceses[$rec["flowid"]][$rec["flowprocess"]]["name"];
                    }
                } else {
                    $rec["processname"] = Ibos::lang("Steps", "", array("{step}" => $rec["processid"]));
                }
            }

            $rec["user"] = User::model()->fetchByUid($rec["userID"]);
        }

        return array("pages" => $pages, "list" => $list);
    }
}
