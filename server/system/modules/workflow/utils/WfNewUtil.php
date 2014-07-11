<?php

class WfNewUtil
{
    public static function replaceAutoName(ICFlowType $type, $uid, $isChildFlow = false)
    {
        $user = User::model()->fetchByUid(intval($uid));
        list($year, $month, $day, $hour, $minute, $seconds) = explode("-", date("Y-m-d-H-i-s", TIMESTAMP));
        $catName = FlowCategory::model()->fetchNameByPk($type->catid);
        $autoNum = $type->autonum;
        $autoLen = $type->autolen;
        $autoNum++;
        $len = strlen($autoNum);

        for ($i = 0; $i < ($autoLen - $len); $i++) {
            $autoNum = "0" . $autoNum;
        }

        if (empty($type->autoname) && $isChildFlow) {
            $type->autoname = sprintf("{F} [{RUN}](%s)", Ibos::lang("Subflow", "workflow.default"));
        }

        $maxRun = FlowRun::model()->getMaxRunId();
        $mapping = array("{Y}" => $year, "{M}" => $month, "{D}" => $day, "{H}" => $hour, "{I}" => $minute, "{S}" => $seconds, "{F}" => $type->name, "{FC}" => $catName, "{U}" => $user["realname"], "{MD}" => $user["deptname"], "{AD}" => Department::model()->fetchDeptNameByDeptId($user["alldeptid"]), "{P}" => $user["posname"], "{N}" => $autoNum, "{RUN}" => $maxRun + 1);
        $search = array_keys($mapping);
        $replace = array_values($mapping);
        $runName = str_replace($search, $replace, $type->autoname);
        return $runName;
    }

    public static function createNewRun($flowId, $uid, $uidstr, $pid = 0, $remind = 0, $startTime = "")
    {
        if (!$startTime) {
            $startTime = TIMESTAMP;
        }

        $flow = new ICFlowType(intval($flowId));
        $runName = self::replaceAutoName($flow, $uid, !!$pid);
        $maxRunId = FlowRun::model()->getMaxRunId();

        if ($maxRunId) {
            $runId = $maxRunId + 1;
        }

        $data = array("runid" => $runId, "name" => $runName, "flowid" => $flowId, "beginuser" => $uid, "begintime" => $startTime, "parentrun" => $pid);
        FlowRun::model()->add($data);

        if (strstr($runName, "{RUN}") !== false) {
            $runName = str_replace("{RUN}", $runId, $runName);
            FlowRun::model()->modify($runId, array("name" => $runName));
        }

        foreach (explode(",", trim($uidstr, ",")) as $k => $v) {
            if ($v == $uid) {
                $opflag = 1;
            } else {
                $opflag = 0;
            }

            $wrpdata = array("runid" => $runId, "processid" => 1, "uid" => $v, "flag" => 1, "flowprocess" => 1, "opflag" => $opflag, "createtime" => $startTime);
            FlowRunProcess::model()->add($wrpdata);
        }

        if ($remind) {
            $remindUrl = Ibos::app()->urlManager->createUrl("workflow/form/index", array("key" => WfCommonUtil::param(array("runid" => $runId, "flowid" => $flowId, "processid" => 1, "flowprocess" => 1))));
            $config = array("{url}" => $remindUrl, "{runname}" => $runName, "{runid}" => $runId);
            Notify::model()->sendNotify($uid, "workflow_new_notice", $config);
        }

        if ($pid != 0) {
            $pflowId = FlowRun::model()->fetchFlowIdByRunId($pid);
            $pRundata = WfHandleUtil::getRunData($pid);
            $pfield = $subFlow = array();
            $relation = FlowProcess::model()->fetchRelationOut($pflowId, $flowId);

            if ($relation) {
                $relationArr = explode(",", trim($relation, ","));

                foreach ($relationArr as $field) {
                    $pfield[] = substr($field, 0, strpos($field, "=>"));
                    $subFlow[] = substr($field, strpos($field, "=>") + strlen("=>"));
                }
            }

            $runData = array("runid" => $runId, "name" => $runName, "begin" => $startTime, "beginuser" => $uid);
        }

        $structure = $flow->form->parser->structure;
        if (is_array($structure) && (0 < count($structure))) {
            foreach ($structure as $k => $v) {
                if ($v["data-type"] !== "label") {
                    if ($v["data-type"] == "checkbox") {
                        if (stristr($v["content"], "checked") || stristr($v["content"], " checked=\"checked\"")) {
                            $itemData = "on";
                        } else {
                            $itemData = "";
                        }
                    }

                    if (($v["data-type"] != "select") && ($v["data-type"] != "listview")) {
                        $itemData = (isset($v["data-value"]) ? $v["data-value"] : "");
                        $itemData = str_replace("\"", "", $itemData);

                        if ($v["data-type"] == "auto") {
                            $itemData = "";
                        }
                    }

                    if (($pid != 0) && in_array($v["data-title"], $subFlow)) {
                        $i = array_search($v["data-title"], $subFlow);
                        $ptitle = $pfield[$i];
                        $itemData = $pRundata["$ptitle"];
                        if (is_array($itemData) && ($v["data-type"] == "listview")) {
                            $itemDataStr = "";
                            $newDataStr = "";

                            for ($j = 1; $j < count($itemData); ++$j) {
                                foreach ($itemData[$j] as $val) {
                                    $newDataStr .= $val . "`";
                                }

                                $itemDataStr .= $newDataStr . "\r\n";
                                $newDataStr = "";
                            }

                            $itemData = $itemDataStr;
                        }
                    }

                    $runData[$k] = $itemData;
                }
            }
        }

        WfCommonUtil::addRunData($flowId, $runData, $structure);
        return $runId;
    }

    public static function checkProcessPermission($flowId, $processId, $uid)
    {
        $user = User::model()->fetchByUid(intval($uid));
        $flow = new ICFlowType(intval($flowId), false);
        $criteria = array("select" => "processid,uid,deptid,positionid", "condition" => sprintf("processid > 0 AND flowid = %d%s", $flowId, $processId ? " AND processid = $processId" : ""));

        foreach (FlowProcess::model()->fetchAll($criteria) as $process) {
            $deptAccess = self::compareIds($user["alldeptid"], $process["deptid"], "d");
            $userAccess = self::compareIds($uid, $process["uid"], "u");
            $posAccess = self::compareIds($user["allposid"], $process["positionid"], "p");
            if ($deptAccess || $userAccess || $posAccess) {
                return true;
            }
        }

        if (empty($processId)) {
            $hasPermission = FlowPermission::model()->fetchPermission($uid, $flowId);

            if ($hasPermission) {
                return true;
            }
        }

        if ($flow->isFree()) {
            if ($processId != 1) {
                return true;
            } else {
                $ids = $flow->newuser;

                if (!empty($ids)) {
                    $deptAccess = self::compareMixedIds($user["alldeptid"], $ids, "d");
                    $userAccess = self::compareMixedIds($uid, $ids, "u");
                    $posAccess = self::compareMixedIds($user["allposid"], $ids, "p");
                    if ($deptAccess || $userAccess || $posAccess) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        }

        unset($flow);
        return false;
    }

    public static function getEnabledFlowIdByUid($uid)
    {
        $user = User::model()->fetchByUid(intval($uid));
        $flowIds = array();
        $fixedFlowPer = FlowProcess::model()->fetchAllFirstStepPermission();

        foreach ($fixedFlowPer as $fixedFlow) {
            $deptAccess = self::compareIds($user["alldeptid"], $fixedFlow["deptid"], "d");
            $userAccess = self::compareIds($uid, $fixedFlow["uid"], "u");
            $posAccess = self::compareIds($user["allposid"], $fixedFlow["positionid"], "p");
            if ($deptAccess || $userAccess || $posAccess) {
                $flowIds[] = $fixedFlow["flowid"];
            }
        }

        $freeFlowPer = FlowType::model()->fetchAllFreePermission();

        foreach ($freeFlowPer as $freeFlow) {
            $ids = $freeFlow["newuser"];

            if (!empty($ids)) {
                $deptAccess = self::compareMixedIds($user["alldeptid"], $ids, "d");
                $userAccess = self::compareMixedIds($uid, $ids, "u");
                $posAccess = self::compareMixedIds($user["allposid"], $ids, "p");
                if ($deptAccess || $userAccess || $posAccess) {
                    $flowIds[] = $freeFlow["flowid"];
                }
            }
        }

        return array_unique($flowIds);
    }

    public static function compareMixedIds($userIds, $mixedIds, $type)
    {
        static $ids = array();

        if (empty($ids)) {
            $ids = StringUtil::getId($mixedIds, true);
        }

        $access = false;

        if (isset($ids[$type])) {
            $flowIds = implode(",", $ids[$type]);
            $access = self::compareIds($userIds, $flowIds, $type);
        }

        return $access;
    }

    public static function compareIds($userIds, $flowIds, $type)
    {
        $access = false;
        if (($type == "u") || ($type == "p")) {
            $access = StringUtil::findIn($userIds, $flowIds);
        } elseif ($type == "d") {
            static $parentDeptId = "";

            if ($flowIds == "alldept") {
                return true;
            }

            if (empty($parentDeptId)) {
                $parentDeptId = Department::model()->queryDept($userIds);
            }

            $access = StringUtil::findIn($userIds, $flowIds) || StringUtil::findIn($parentDeptId, $flowIds);
        }

        return $access;
    }
}
