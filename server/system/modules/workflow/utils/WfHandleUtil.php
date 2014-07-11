<?php

class WfHandleUtil
{
    public static function getFormData($flowId, $runId)
    {
        $formData = array();
        $flow = new ICFlowType(intval($flowId));
        $data = FlowDataN::model()->fetch($flowId, $runId);

        foreach ($flow->form->parser->structure as $key => $item) {
            if ($item["data-type"] !== "label") {
                $title = $item["data-title"];
                $formData[base64_encode($title)] = (isset($data[$key]) ? $data[$key] : "");
            }
        }

        return $formData;
    }

    public static function getPrcsUser($flowId, $nextId)
    {
        $flow = new ICFlowType(intval($flowId));
        $join = "";

        if ($flow->isFixed()) {
            $process = FlowProcess::model()->fetchProcess($flowId, $nextId);

            if ($process) {
                $prcsUser = $process["uid"];
                $prcsDept = $process["deptid"];
                $prcsPos = $process["positionid"];
                $userFilter = $process["userfilter"];
            } else {
                $prcsUser = $prcsDept = $prcsPos = $userFilter = "";
            }

            $queryStr = "(1=2";

            if (!empty($prcsUser)) {
                $queryStr .= " OR FIND_IN_SET(u.uid,'$prcsUser')";
            }

            if (!empty($prcsDept)) {
                if ($prcsDept == "alldept") {
                    $queryStr = "(1=1";
                } else {
                    $join .= " LEFT JOIN {{department_related}} dr ON u.uid = dr.uid";
                    $queryStr .= " OR (FIND_IN_SET(u.deptid,'$prcsDept') OR FIND_IN_SET(dr.deptid,'$prcsDept'))";
                }
            }

            if (!empty($prcsPos)) {
                $join .= " LEFT JOIN {{position_related}} pr ON u.uid = pr.uid";
                $queryStr .= " OR (FIND_IN_SET(u.positionid,'$prcsPos') OR FIND_IN_SET(pr.positionid,'$prcsPos'))";
            }

            if (!empty($queryStr)) {
                $queryStr .= ")";
            } else {
                $queryStr = "";
            }

            if ($userFilter == "1") {
                $queryStr .= " AND (u.deptid='" . Ibos::app()->user->deptid . "')";
            } elseif ($userFilter == "2") {
                $queryStr .= " AND u.positionid='" . Ibos::app()->user->positionid . "'";
            } elseif ($userFilter == "3") {
                if (!empty(Ibos::app()->user->allupdeptid)) {
                    $queryStr .= " AND (FIND_IN_SET(u.deptid,'" . Ibos::app()->user->allupdeptid . "'))";
                } else {
                    $queryStr .= " AND (u.deptid='0')";
                }
            } elseif ($userFilter == "4") {
                if (!empty(Ibos::app()->user->alldowndeptid)) {
                    $queryStr .= " AND (FIND_IN_SET(u.deptid,'" . Ibos::app()->user->alldowndeptid . "'))";
                } else {
                    $queryStr .= " AND (u.deptid='0')";
                }
            }
        } elseif ($flow->isFree()) {
            $queryStr = "1=1";
        }

        $text = sprintf("SELECT CONCAT(\"u_\",u.uid) as uid FROM {{user}} u %s WHERE %s", $join, $queryStr);
        $uids = Ibos::app()->db->createCommand()->setText($text)->queryAll();
        return ConvertUtil::getSubByKey($uids, "uid");
    }

    public static function getEntrustUser($flowId, $runId, $processId, $nextId)
    {
        $flow = new ICFlowType(intval($flowId));
        $join = "";

        if ($flow->freeother == "2") {
            $queryStr = "1=1";
        } elseif ($flow->freeother == "3") {
            if ($flow->isFixed()) {
                $process = FlowProcess::model()->fetchProcess($flowId, $nextId);

                if ($process) {
                    $prcsUser = $process["uid"];
                    $prcsDept = $process["deptid"];
                    $prcsPos = $process["positionid"];
                    $userFilter = $process["userfilter"];
                } else {
                    $prcsUser = $prcsDept = $prcsPos = $userFilter = "";
                }

                $queryStr = "(1=2";

                if (!empty($prcsUser)) {
                    $queryStr .= " OR FIND_IN_SET(u.uid,'$prcsUser')";
                }

                if (!empty($prcsDept)) {
                    if ($prcsDept == "alldept") {
                        $queryStr = "(1=1";
                    } else {
                        $join .= " LEFT JOIN {{department_related}} dr ON u.uid = dr.uid";
                        $queryStr .= " OR (FIND_IN_SET(u.deptid,'$prcsDept') OR FIND_IN_SET(dr.deptid,'$prcsDept'))";
                    }
                }

                if (!empty($prcsPos)) {
                    $join .= " LEFT JOIN {{position_related}} pr ON u.uid = pr.uid";
                    $queryStr .= " OR (FIND_IN_SET(u.positionid,'$prcsPos') OR FIND_IN_SET(pr.positionid,'$prcsPos'))";
                }

                if (!empty($queryStr)) {
                    $queryStr .= ")";
                } else {
                    $queryStr = "";
                }

                if ($userFilter == "1") {
                    $queryStr .= " AND (u.deptid='" . Ibos::app()->user->deptid . "')";
                } elseif ($userFilter == "2") {
                    $queryStr .= " AND u.positionid='" . Ibos::app()->user->positionid . "'";
                }
            } elseif ($flow->isFree()) {
                $queryStr = "1=1";
                $join = "";
            }
        } elseif ($flow->freeother == "1") {
            $uids = Ibos::app()->db->createCommand()->select("uid")->from("{{flow_run_process}}")->where(sprintf("runid = %d AND processid = %d AND opflag = 0", $runId, $processId))->queryAll();

            if (!empty($uids)) {
                $uid = implode(",", $uids);
                $queryStr = " AND FIND_IN_SET(u.uid,'$uid')";
            } else {
                $queryStr = " AND (1=2)";
            }
        } else {
            $queryStr = "1=2";
        }

        $text = sprintf("SELECT CONCAT(\"u_\",u.uid) as uid FROM {{user}} u %s WHERE %s", $join, $queryStr);
        $uids = Ibos::app()->db->createCommand()->setText($text)->queryAll();
        return ConvertUtil::getSubByKey($uids, "uid");
    }

    public static function getRunData($runId, $eleArr = array())
    {
        if (!$runId) {
            return null;
        }

        $flowId = FlowRun::model()->fetchFlowIdByRunId($runId);

        if (!$eleArr) {
            $flow = new ICFlowType(intval($flowId));
            $structure = $flow->form->parser->structure;
        }

        $output = array();
        $runData = FlowDataN::model()->fetch($flowId, $runId);

        foreach ($structure as $item) {
            if ($item["data-type"] !== "label") {
                $itemStr = "data_" . $item["itemid"];
                $itemData = $runData[$itemStr];

                if ($item["data-type"] != "listview") {
                    $output["{$item["data-title"]}"] = $itemData;
                } else {
                    $lvTitle = $item["data-lv-title"];
                    $titlearr = explode("`", $lvTitle);
                    $output["{$item["data-title"]}"][] = $titlearr;
                    $data = array();
                    $myarr = explode("\r\n", $itemData);
                    $count = count($myarr);

                    if ($myarr[$count - 1] == "") {
                        --$count;
                    }

                    for ($i = 0; $i < $count; ++$i) {
                        $data[] = explode("`", rtrim($myarr[$i], "`"));
                    }

                    $output[$item["data-title"]] = array_merge($output[$item["data-title"]], $data);
                }
            }
        }

        return $output;
    }

    public static function checkCondition($form, $con, $conDesc)
    {
        $lang = Ibos::getLangSource("workflow.default");

        if ($con == "") {
            return "";
        }

        $conditionSetDesc = "";
        $conReplace = $con;
        $conReplace = trim(str_replace(array("\n", " "), array("", ""), $con));
        $len = strlen($conReplace);
        $j = 0;
        $pos = 0;

        while ($pos < $len) {
            $pos = strpos($conReplace, "'", $pos);

            if ($pos === false) {
                break;
            }

            $pos1 = strpos($conReplace, "AND", $pos);
            $pos2 = strpos($conReplace, "OR", $pos);
            if ($pos1 && $pos2) {
                if ($pos2 < $pos1) {
                    if ($pos2) {
                        $str2 = substr($conReplace, $pos, $pos2 - $pos);
                        $str2 = ltrim($str2, "(");
                        $str2 = rtrim($str2, ")");
                        $conArr[$j] = $str2;
                        $j++;
                        $conReplace = str_replace($str2, "[$j]", $conReplace);
                    }
                } elseif ($pos1) {
                    $str = substr($conReplace, $pos, $pos1 - $pos);
                    $str = ltrim($str, "(");
                    $str = rtrim($str, ")");
                    $conArr[$j] = $str;
                    $j++;
                    $conReplace = str_replace($str, "[$j]", $conReplace);
                }
            } else {
                $pos3 = false;

                if ($pos1) {
                    $pos3 = $pos1;
                } elseif ($pos2) {
                    $pos3 = $pos2;
                } else {
                    $pos3 = strpos($conReplace, ")", $pos);

                    if (!$pos3) {
                        $pos3 = strpos($conReplace, "'", $pos);
                    }
                }

                if ($pos3) {
                    if ($pos3 == $pos) {
                        $str3 = substr($conReplace, $pos);
                    } else {
                        $str3 = substr($conReplace, $pos, $pos3 - $pos);
                    }

                    $str3 = ltrim($str3, "(");
                    $str3 = rtrim($str3, ")");
                    $conArr[$j] = $str3;
                    $j++;
                    $conReplace = str_replace($str3, "[$j]", $conReplace);
                } else {
                    break;
                }
            }
        }

        if (empty($conArr)) {
            unset($conReplace);
            $conArr = explode("\n", $con);
            $arrCount = count($conArr);

            if ($conArr[$arrCount - 1] == "") {
                $arrCount--;
            }
        } else {
            $arrCount = count($conArr);
        }

        for ($i = 0; $i < $arrCount; $i++) {
            $rule = $conArr[$i];
            $tmp = trim(str_replace(" ", "", $rule));

            if ($tmp == "") {
                continue;
            }

            $ruleArr = explode("'", $tmp);
            $itemTitle = $itemDesc = $ruleArr[1];
            $itemCon = $ruleArr[2];
            $itemValue = $ruleArr[3];
            $checkPass = 0;
            $itemTitle = base64_encode($itemTitle);

            if (strcasecmp($itemCon, "include") == 0) {
                $itemConDesc = $lang["Include"];
                $checkPass = (strstr($form[$itemTitle], $itemValue) ? 1 : 0);
            } elseif (strcasecmp($itemCon, "exclude") == 0) {
                $itemConDesc = $lang["Exclude"];
                $checkPass = (!strstr($form[$itemTitle], $itemValue) ? 1 : 0);
            } elseif (strcasecmp($itemCon, ">=") == 0) {
                $itemConDesc = $lang["Greater than or equal to"];
                $checkPass = ($itemValue <= $form[$itemTitle] ? 1 : 0);
            } elseif (strcasecmp($itemCon, "<=") == 0) {
                $itemConDesc = $lang["Less than or equal to"];
                $checkPass = ($form[$itemTitle] <= $itemValue ? 1 : 0);
            } elseif (strcasecmp($itemCon, "<>") == 0) {
                $itemConDesc = $lang["Not equal to"];
                $checkPass = ($form[$itemTitle] != $itemValue ? 1 : 0);
            } elseif (strcasecmp($itemCon, ">") == 0) {
                $itemConDesc = $lang["Greater than"];
                $checkPass = ($itemValue < $form[$itemTitle] ? 1 : 0);
            } elseif (strcasecmp($itemCon, "<") == 0) {
                $itemConDesc = $lang["Less than"];
                $checkPass = ($form[$itemTitle] < $itemValue ? 1 : 0);
            } elseif ($itemCon == "=") {
                $itemConDesc = $lang["Equal to"];
                $checkPass = ($form[$itemTitle] == $itemValue ? 1 : 0);
            } else {
                $itemCon = "";
            }

            if ($itemValue == "") {
                $itemValue = $lang["Empty"];
            }

            $setDesc = "$itemDesc $itemConDesc $itemValue";

            if (!$checkPass) {
                if (($itemCon == "") || ($itemTitle == "")) {
                    $notPass = $lang["Conditional expression errors"] . "：\n" . $rule . "\n";
                    return $notPass;
                } else {
                    if (empty($conReplace)) {
                        $notPass = $lang["Not conform the conditions"] . "：【" . $setDesc . "】\n" . $conDesc;
                        return $notPass;
                    }

                    $setValue = "0";
                }
            } else {
                $setValue = "1";
                $notPass = "";
            }

            $count = $i + 1;
            $conReplace = (isset($conReplace) ? str_replace("[$count]", $setValue, $conReplace) : "");
            $conditionSetDesc .= $setDesc . ",";
        }

        if (!empty($conReplace)) {
            $conReplace = str_replace(array("AND", "OR"), array(" AND ", " OR "), $conReplace);
            eval ("\$result=($conReplace);");

            if (!$result) {
                $notPass = ($conDesc == "" ? $lang["Not conform the conditions"] . ": 【" . $conditionSetDesc . "】\\n" : $conDesc);
            } else {
                $notPass = "setok" . $conditionSetDesc;
            }
        }

        return $notPass;
    }

    public static function updateParentOver($runId, $pId)
    {
        if (($runId == 0) || ($pId == 0)) {
            return null;
        }

        $parentProcess = FlowRunProcess::model()->fetchIDByChild($pId, $runId);

        if ($parentProcess) {
            $prcsId = $parentProcess["processid"];
            $flowprocess = $parentProcess["flowprocess"];
        }

        $runId = $pId;
        FlowRunProcess::model()->updateToOver($pId, $prcsId, $flowprocess);
        $isNotOver = FlowRunProcess::model()->getIsNotOver($pId);

        if ($isNotOver) {
            FlowRun::model()->modify($pId, array("endtime" => TIMESTAMP));
        }

        $pId = FlowRun::model()->fetchParentRun($pId);

        if ($pId) {
            self::updateParentOver($runId, $pId);
        }
    }

    public static function compareTimestamp($t1, $t2)
    {
        if (is_numeric($t1) && is_numeric($t2)) {
            if ($t2 < $t1) {
                return 1;
            } elseif ($t1 < $t2) {
                return -1;
            } else {
                return 0;
            }
        }

        return null;
    }

    public static function turnOther($inputStr, $flowId, $runId, $processId, $flowProcess, $except = "")
    {
        $inputArray = explode(",", $inputStr);
        $count = count($inputArray);

        for ($i = 0; $i < $count; $i++) {
            $rule = FlowRule::model()->fetchRuleByFlowIDUid($flowId, $inputArray[$i]);

            if ($rule) {
                $toid = $rule["toid"];
                $now = strtotime(date("Y-m-d", TIMESTAMP));
                $condition1 = self::compareTimestamp($now, $rule["begindate"]);
                $condition2 = self::compareTimestamp($now, $rule["enddate"]);
                $status = 0;
                if (($rule["begindate"] != 0) && ($rule["enddate"] != 0)) {
                    if ((0 <= $condition1) && ($condition2 <= 0)) {
                        $status = 1;
                    }
                } elseif ($rule["begindate"] != 0) {
                    if (0 <= $condition1) {
                        $status = 1;
                    }
                } elseif ($rule["enddate"] != 0) {
                    if ($condition1 <= 0) {
                        $status = 1;
                    }
                } else {
                    $status = 1;
                }

                if ($status == 1) {
                    if ($except != $inputArray[$i]) {
                        $toname = User::model()->fetchRealnameByUid($toid);
                        $content = Ibos::lang("Entrust by rule", "workflow.default") . "[" . $toname . "]";
                        WfCommonUtil::runlog($runId, $processId, $flowProcess, $inputArray[$i], 2, $content, $toid);
                    }

                    $inputArray[$i] = $toid;
                }
            }
        }

        $outputStr = implode(",", $inputArray);
        return trim($outputStr, ",");
    }

    public static function loadFeedback($flowId, $runId, $type, $uid, $loadReply = false)
    {
        $proceses = WfCommonUtil::loadProcessCache($flowId);
        $feedback = $slarr = $prcs = $wparr = array();

        if ($type == "1") {
            foreach (FlowRunProcess::model()->fetchAllIDByRunID($runId) as $rp) {
                if (isset($proceses[$rp["flowprocess"]])) {
                    $name = $proceses[$rp["flowprocess"]]["name"];
                    $signlook = $proceses[$rp["flowprocess"]]["signlook"];
                    $wparr[$rp["flowprocess"]] = $name;

                    if (!isset($prcs[$rp["processid"]])) {
                        $prcs[$rp["processid"]] = $name;
                    } else {
                        if (isset($prcs[$rp["processid"]]) && !StringUtil::findIn($prcs[$rp["processid"]], $name)) {
                            $prcs[$rp["processid"]] .= "," . $name;
                        }
                    }

                    if ($type == "1") {
                        $slarr[$rp["processid"]] = $signlook;
                    }
                }
            }
        }

        foreach (FlowRunfeedback::model()->fetchAllByRunID($runId) as $fb) {
            if (isset($proceses[$fb["flowprocess"]])) {
                $fid = $fb["feedid"];

                if ($type == "1") {
                    if (FlowRunProcess::model()->getIsAgent($uid, $runId, $fb["processid"], $fb["flowprocess"])) {
                        $isPrcsUser = true;
                    } else {
                        $isPrcsUser = false;
                    }

                    if ($slarr[$fb["processid"]] == 2) {
                        if (!$isPrcsUser) {
                            continue;
                        }
                    } elseif ($slarr[$fb["processid"]] == 1) {
                        if ($fb["uid"] != $uid) {
                            continue;
                        }
                    }
                }

                if ($type == "1") {
                    $fb["name"] = (0 < $fb["flowprocess"] ? $wparr[$fb["flowprocess"]] : $prcs[$fb["processid"]]);
                } else {
                    $fb["name"] = Ibos::lang("Free flow", "workflow.default");
                }

                $fb["edittime"] = ConvertUtil::formatDate($fb["edittime"], "u");
                $fb["user"] = User::model()->fetchByUid($fb["uid"]);
                $fb["content"] = html_entity_decode($fb["content"]);
                $fb["attachment"] = (empty($fb["attachmentid"]) ? "" : AttachUtil::getAttach($fb["attachmentid"], 1, 1, 1, 1));
                $fb["count"] = FlowRunfeedback::model()->countByAttributes(array("replyid" => $fb["feedid"]));
                if ($loadReply && $fb["count"]) {
                    $fb["reply"] = FlowRunfeedback::model()->getFeedbackReply($fid);
                }

                $feedback[$fid] = $fb;
            }
        }

        return $feedback;
    }

    public static function destroy($runIds)
    {
        $count = 0;
        !is_array($runIds) && ($runIds = explode(",", $runIds));

        foreach ($runIds as $runId) {
            $run = FlowRun::model()->fetchByPk($runId);
            $attachmentid = $run["attachmentid"];

            foreach (FlowRunfeedback::model()->fetchAllAttachByRunId($runId) as $ftatt) {
                $attachmentid .= $ftatt["attachmentid"] . ",";
            }

            if (!empty($attachmentid)) {
                AttachUtil::delAttach($attachmentid);
            }

            FlowRunfeedback::model()->deleteAllByAttributes(array("runid" => $runId));
            FlowDataN::model()->deleteByRunId($run["flowid"], $runId);
            FlowRunProcess::model()->deleteAllByAttributes(array("runid" => $runId));
            FlowRun::model()->remove($runId);
            $count++;
        }

        return $count;
    }

    public static function takeBack($runId, $flowProcess, $processId, $uid)
    {
        $per = WfCommonUtil::getRunPermission($runId, $uid, $processId);
        if (!StringUtil::findIn($per, 1) && !StringUtil::findIn($per, 2)) {
            return 1;
        }

        $prcsIDNext = $processId + 1;

        if (FlowRunProcess::model()->getIsParentOnTakeBack($runId, $prcsIDNext, $flowProcess)) {
            return 2;
        }

        if (FlowRunProcess::model()->getHasDefaultStep($runId, $prcsIDNext)) {
            FlowRunProcess::model()->updateAll(array("flag" => 5), sprintf("runid = %d AND processid >= '%d'", $runId, $processId));
        } else {
            $next = FlowRunProcess::model()->fetch(array("select" => "flowprocess,parent", "condition" => sprintf("runid = %d AND processid >= '%d' AND FIND_IN_SET('%d',parent)", $runId, $processId, $flowProcess)));

            if ($next) {
                $parr = explode(",", $next["parent"]);
                $len = count($parr);

                if ($len == 1) {
                    FlowRunProcess::model()->deleteAll(sprintf("runid='%d' AND processid>='%d' AND FIND_IN_SET('%d',parent)", $runId, $prcsIDNext, $flowProcess));
                } else {
                    foreach ($parr as $k => $v) {
                        if ($v == $flowProcess) {
                            array_splice($parr, $k, 1);
                        }

                        break;
                    }

                    $parent = "";

                    foreach ($parr as $k => $v) {
                        $parent .= $v . ",";
                    }

                    $parent = trim($parent, ",");
                    FlowRunProcess::model()->updateAll(array("parent" => $parent), sprintf("runid = %d AND processid >= '%d' AND flowprocess = %d AND FIND_IN_SET('%d',parent)", $runId, $prcsIDNext, $next["flowprocess"], $flowProcess));
                }
            } else {
                FlowRunProcess::model()->deleteAllByAttributes(array("runid" => $runId, "processid" => $prcsIDNext));
            }
        }

        $content = Ibos::lang("Takeback work", "workflow.default");
        WfCommonUtil::runlog($runId, $processId, $flowProcess, $uid, 1, $content);
        FlowRunProcess::model()->updateAll(array("flag" => 2), sprintf("runid = %d AND processid = %d AND flowprocess = %d AND uid = %d AND opflag = 1", $runId, $processId, $flowProcess, $uid));
        return 0;
    }

    public static function export($runID)
    {
        $runIDs = explode(",", StringUtil::filterStr($runID));
        $zip = new Zip();
        $replaceMapping = array("/" => "", "\\" => "", "*" => "", ":" => "", "?" => "", "\\\"" => "", "<" => "", ">" => "", "|" => "");
        $attachDir = FileUtil::getAttachUrl();
        $controller = Ibos::app()->getController();
        $uid = Ibos::app()->user->uid;
        $lang = Ibos::getLangSources(array("workflow.default"));

        foreach ($runIDs as $id) {
            $run = FlowRun::model()->fetchByPk($id);
            $name = str_replace(array_keys($replaceMapping), array_values($replaceMapping), $run["name"]);
            $run["runname"] = ConvertUtil::iIconv($name, CHARSET, "gbk");
            $data = array_merge(array("run" => $run, "lang" => $lang), self::exportFlowData($id, "123"));
            $content = $controller->renderPartial("application.modules.workflow.views.export", $data, true);
            $zip->addFile($content, $run["runname"] . "/" . $run["runname"] . ".html");

            if (!empty($run["attachmentid"])) {
                $attachData = AttachUtil::getAttachData($run["attachmentid"]);

                foreach ($attachData as $attach) {
                    $attachType = AttachUtil::attachType(StringUtil::getFileExt($attach["filename"]), "id");

                    if (in_array($attachType, array("3", "4", "5"))) {
                        $flow = FlowType::model()->fetchByPk($run["flowid"]);

                        if ($flow["type"] == 1) {
                            if (FlowProcess::model()->getHasAttachPer($runID, $run["flowid"], $uid)) {
                                continue;
                            }
                        }

                        $attach["filename"] = ConvertUtil::iIconv($attach["filename"], CHARSET, "gbk");
                    }

                    $filepath = $attachDir . "/" . $attach["attachment"];

                    if (!FileUtil::fileExists($filepath)) {
                        continue;
                    }

                    $zip->addFile(FileUtil::readFile($filepath), $run["runname"] . "/" . ConvertUtil::iIconv($attach["filename"], CHARSET, "gbk"));
                }
            }
        }

        $output = $zip->file();
        header("Content-type: text/html; charset=" . CHARSET);
        header("Cache-control: private");
        header("Content-type: application/x-zip");
        header("Accept-Ranges: bytes");
        header("Accept-Length: " . strlen($output));
        header("Content-Length: " . strlen($output));
        header("Content-Disposition: attachment; filename= IBOS" . urlencode(Ibos::lang("Workflow", "workflow.default")) . "(" . date("Y-m-d", TIMESTAMP) . ").zip");
        echo $output;
    }

    protected static function exportFlowData($runID, $view = "123")
    {
        $run = new ICFlowRun($runID);
        $flow = new ICFlowType(intval($run->flowid));
        $data = array();

        if (strstr($view, "1")) {
            $form = $flow->form;
            $viewer = new ICFlowFormViewer(array("flow" => $flow, "form" => $flow->form, "run" => $run));
            $data = array_merge($data, array("formname" => $form->formname, "script" => $form->script, "css" => $form->css), $viewer->render(true, true));
        }

        if (strstr($view, "2")) {
            $data["feedback"] = WfHandleUtil::loadFeedback($flow->getID(), $runID, $flow->type, Ibos::app()->user->uid, true);
        }

        if (strstr($view, "3")) {
            $tempArr = array();
            $data["viewflow"] = WfPreviewUtil::getViewFlowData($runID, $flow->getID(), Ibos::app()->user->uid, $tempArr);
        }

        return $data;
    }

    public static function endRun($id, $uid)
    {
        $part = (is_array($id) ? $id : explode(",", StringUtil::filterStr($id)));
        $runid = $part[0];
        $per = WfCommonUtil::getRunPermission($runid, $uid, 0);
        $allow = (StringUtil::findIn($per, 3) ? 1 : 0);
        if ((Ibos::app()->user->isadministrator != 1) && ($allow == 0)) {
            return false;
        }

        $condition = sprintf("FIND_IN_SET(runid,'%s') AND flag <> '4'", implode(",", $part));
        FlowRunProcess::model()->updateAll(array("delivertime" => TIMESTAMP, "flag" => 4), $condition);
        FlowRun::model()->updateByPk($part, array("endtime" => TIMESTAMP));
        return true;
    }
}
