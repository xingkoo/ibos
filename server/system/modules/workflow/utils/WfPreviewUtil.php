<?php

class WfPreviewUtil
{
    /**
     *
     * @var array 
     */
    private static $typeMapping = array(1 => "inactive", 2 => "processing", 3 => "processing", 4 => "finish", 6 => "suspend", 7 => "sub");

    public static function getFixedPrcs($flowId, $runId)
    {
        $return = $temp = array();

        foreach (FlowRunProcess::model()->fetchAllByRunID($runId) as $rp) {
            $fp = $rp["flowprocess"];
            $temp[$fp]["flag"] = $rp["flag"];

            if ($rp["childrun"] !== "0") {
                $temp[$fp]["flag"] = "7";
            }
        }

        $process = WfCommonUtil::loadProcessCache($flowId);
        arsort($process);
        $isEnd = false;

        foreach ($process as $processId => $val) {
            if (isset($temp[$processId])) {
                $val = $val + $temp[$processId];
                $val["type"] = self::$typeMapping[$val["flag"]];

                if (StringUtil::findIn($val["processto"], 0)) {
                    if ($val["type"] == "finish") {
                        $isEnd = true;
                    }
                }
            } else {
                $val["flag"] = 1;
                $val["childrun"] = 0;
                $val["type"] = "inactive";
                if (($processId == 0) && $isEnd) {
                    $val["type"] = "finish";
                }
            }

            $val["to"] = $val["processto"];
            $val["left"] = intval($val["setleft"]);
            $val["top"] = intval($val["settop"]);
            $val["processid"] = intval($val["processid"]);
            $return[] = $val;
        }

        return $return;
    }

    public static function getFreePrcs($runId)
    {
        $count = FlowRunProcess::model()->countByRunID($runId);

        if ($count) {
            $return = array();

            for ($i = 1; $i <= $count; $i++) {
                $v = array();
                $rp = FlowRunProcess::model()->fetch(array("condition" => sprintf("runid = %d AND processid = %d", $runId, $i), "group" => "flowprocess"));

                if ($rp) {
                    $fp = FlowRunProcess::model()->fetch(array("condition" => sprintf("runid = %d AND processid = %d AND flowprocess = %d", $runId, $i, $rp["flowprocess"]), "order" => "opflag,flag"));

                    if ($fp["flag"] == "5") {
                        continue;
                    }

                    $v["id"] = $i;
                    $v["type"] = self::$typeMapping[$fp["flag"]];
                    $v["left"] = 0;
                    $v["top"] = 0;
                    $v["flowprocess"] = $rp["flowprocess"];
                    $v["name"] = Ibos::lang("No.step", "workflow.default", array("{step}" => $i));
                    $return[] = $v;
                }
            }

            return $return;
        }
    }

    public static function getViewFlowData($runId, $flowId, $uid, &$remindUid)
    {
        $fl = array();
        $flow = new ICFlowType(intval($flowId));
        $pMaxId = FlowRunProcess::model()->fetchMaxIDByRunID($runId);
        $process = WfCommonUtil::loadProcessCache($flowId);

        for ($processId = 1; $processId <= $pMaxId; $processId++) {
            foreach (FlowRunProcess::model()->fetchAllProcessByProcessID($runId, $processId) as $rp) {
                $temp = array("flowprocess" => $rp["flowprocess"], "parent" => $rp["parent"], "runid" => $rp["runid"], "processid" => $rp["processid"]);

                if (FlowRunProcess::model()->getIsAgent($runId, $processId, $uid, $rp["flowprocess"])) {
                    $temp["isprocuser"] = 1;
                } else {
                    $temp["isprocuser"] = 0;
                }

                $op = FlowRunProcess::model()->fetchOpUserByUniqueID($runId, $processId, $rp["flowprocess"]);

                if (!empty($op)) {
                    $temp["opuser"] = (!empty($op["uid"]) ? User::model()->fetchRealnameByUid($op["uid"]) : "");
                    $temp["opprocessflag"] = $op["flag"];
                } else {
                    $temp["opuser"] = User::model()->fetchRealnameByUid($rp["uid"]);
                    $temp["opprocessflag"] = $rp["flag"];
                }

                if ($flow->isFixed()) {
                    if (isset($process[$rp["flowprocess"]])) {
                        $temp["name"] = $process[$rp["flowprocess"]]["name"];
                        $temp["timeout"] = $process[$rp["flowprocess"]]["timeout"];
                        $temp["signlook"] = $process[$rp["flowprocess"]]["signlook"];
                    } else {
                        $temp["name"] = Ibos::lang("Process steps already deleted", "workflow.default");
                    }
                } else {
                    $temp["timeout"] = 0;
                }

                foreach (FlowRunProcess::model()->fetchAllProcessByFlowProcess($runId, $processId, $rp["flowprocess"]) as $arp) {
                    $temp["prcsuid"] = $arp["uid"];
                    $temp["opflag"] = $arp["opflag"];
                    $temp["flag"] = $arp["flag"];
                    $temp["processtime"] = ConvertUtil::formatDate($arp["processtime"], "u");
                    $temp["delivertime"] = ($arp["delivertime"] != 0 ? ConvertUtil::formatDate($arp["delivertime"], "u") : $arp["delivertime"]);

                    if ($arp["flag"] == "1") {
                        $temp["timeused"] = 0;
                    } elseif ($arp["flag"] == "2") {
                        $temp["timeused"] = TIMESTAMP - $arp["processtime"];
                    } elseif ($arp["delivertime"] == 0) {
                        $temp["timeused"] = 0;
                    } else {
                        $temp["timeused"] = $arp["delivertime"] - $arp["processtime"];
                    }

                    if ($arp["processtime"] == "") {
                        $temp["timeused"] = 0;
                    }

                    $temp["timestr"] = WfCommonUtil::getTime($temp["timeused"]);
                    $temp["timeoutflag"] = 0;
                    if (($arp["flag"] == "2") && ($arp["processtime"] != "") && ($temp["timeout"] != 0)) {
                        if (($temp["timeout"] * 3600) < $temp["timeused"]) {
                            $temp["timeoutflag"] = 1;
                            $temp["timeused"] = WfCommonUtil::getTime($temp["timeused"] - ($temp["timeout"] * 3600));
                        }
                    }

                    if (($arp["flag"] == 1) || ($temp["timeoutflag"] == 1)) {
                        $remindUid[] = $arp["uid"];
                    }

                    $temp["redo"] = false;
                    if (($temp["opuser"] == $uid) && ($arp["uid"] != $uid) && (($temp["opprocessflag"] == 1) || ($temp["opprocessflag"] == 2)) && (($arp["flag"] == 3) || ($arp["flag"] == 4))) {
                        $temp["redo"] = true;
                    }

                    $temp["log"] = FlowRunLog::model()->fetchLog($temp["runid"], $temp["processid"], $temp["flowprocess"], 8);
                }

                $fl[count($fl) + 1] = $temp;
            }
        }

        return $fl;
    }
}
