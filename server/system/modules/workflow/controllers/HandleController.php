<?php

class WorkflowHandleController extends WorkflowBaseController
{
    public function filterRoutes($routes)
    {
        return true;
    }

    public function actionLoadReply()
    {
        $feedId = intval(EnvUtil::getRequest("feedid"));
        $list = FlowRunfeedback::model()->getFeedbackReply($feedId);
        $view = $this->renderPartial("loadReply", array("list" => $list, "lang" => Ibos::getLangSources()), true);
        $this->ajaxReturn(array("isSuccess" => true, "data" => $view));
    }

    public function actionAddReply()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $content = StringUtil::filterCleanHtml(EnvUtil::getRequest("content"));
            $replyId = intval(EnvUtil::getRequest("replyid"));
            $key = EnvUtil::getRequest("key");
            $param = WfCommonUtil::param($key, "DECODE");
            $rep = array("content" => $content, "uid" => $this->uid, "runid" => $param["runid"], "processid" => $param["processid"], "flowprocess" => $param["flowprocess"], "edittime" => TIMESTAMP, "feedflag" => 1, "replyid" => $replyId);
            $newId = FlowRunfeedback::model()->add($rep, true);
            $data = array("replyID" => $replyId, "newID" => $newId, "lang" => Ibos::getLangSources(), "reply" => $rep, "user" => User::model()->fetchByUid($this->uid));
            $view = $this->renderPartial("parseReply", $data, true);
            $this->ajaxReturn(array("isSuccess" => true, "data" => $view));
        }
    }

    public function actionDelFb()
    {
        $feedId = intval(EnvUtil::getRequest("feedid"));
        $feed = FlowRunfeedback::model()->fetchByAttributes(array("feedid" => $feedId, "uid" => $this->uid));

        if ($feed) {
            if ($feed["feedflag"] == "0") {
                if (!empty($feed["attachmentid"])) {
                    AttachUtil::delAttach($feed["attachmentid"]);
                    FlowRunfeedback::model()->deleteAllByAttributes(array("replyid" => $feedId));
                }
            }

            FlowRunfeedback::model()->remove($feedId);
            $this->ajaxReturn(array("isSuccess" => true));
        }

        $this->ajaxReturn(array("isSuccess" => false));
    }

    public function actionDelFbAttach()
    {
        $feedId = intval(EnvUtil::getRequest("feedid"));
        $attachId = intval(EnvUtil::getRequest("aid"));
        $feed = FlowRunfeedback::model()->fetchByAttributes(array("feedid" => $feedId, "uid" => $this->uid));

        if ($feed) {
            $aids = explode(",", $feed["attachmentid"]);

            foreach ($aids as $i => &$aid) {
                if ($aid == $attachId) {
                    unset($aids[$i]);
                    break;
                }
            }

            FlowRunfeedback::model()->modify($feedId, array("attachmentid" => implode(",", $aids)));
            AttachUtil::delAttach($attachId);
            $this->ajaxReturn(array("isSuccess" => true));
        }

        $this->ajaxReturn(array("isSuccess" => false));
    }

    public function actionDelAttach()
    {
        $runId = intval(EnvUtil::getRequest("runid"));
        $attachId = intval(EnvUtil::getRequest("aid"));
        $run = FlowRun::model()->fetchByPk($runId);

        if ($run) {
            $aids = explode(",", $run["attachmentid"]);

            foreach ($aids as $i => $aid) {
                if ($aid == $attachId) {
                    unset($aids[$i]);
                    break;
                }
            }

            FlowRun::model()->modify($runId, array("attachmentid" => implode(",", $aids)));
            AttachUtil::delAttach($attachId);
            $this->ajaxReturn(array("isSuccess" => true));
        }

        $this->ajaxReturn(array("isSuccess" => false));
    }

    public function actionFocus()
    {
        $op = intval(EnvUtil::getRequest("focus"));
        $runId = EnvUtil::getRequest("id");
        $ids = StringUtil::filterStr($runId);

        foreach (explode(",", $ids) as $id) {
            $status = FlowRun::model()->setFocus($op, $id, $this->uid);
        }

        $this->ajaxReturn(array("isSuccess" => $status));
    }

    public function actionExport()
    {
        $runid = EnvUtil::getRequest("runid");
        WfHandleUtil::export($runid);
    }

    public function actionFallback()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $key = EnvUtil::getRequest("key");
            $param = WfCommonUtil::param($key, "DECODE");
            $flowId = $param["flowid"];
            $processId = $param["processid"];
            $flowProcess = $param["flowprocess"];
            $runId = $param["runid"];
            $last = intval(EnvUtil::getRequest("id"));
            $msg = StringUtil::filterCleanHtml(EnvUtil::getRequest("remind"));
            $per = WfCommonUtil::getRunPermission($runId, $this->uid, $processId);
            if (!StringUtil::findIn($per, 1) && !StringUtil::findIn($per, 2) && !StringUtil::findIn($per, 3)) {
                $this->ajaxReturn(array("isSuccess" => false));
            }

            $process = new ICFlowProcess($flowId, $flowProcess);
            if ((0 < $process->allowback) && ($processId != 1)) {
                $prcsIDNew = $processId + 1;

                if (empty($last)) {
                    $temp = Ibos::app()->db->createCommand()->select("frp.flowprocess,frp.uid,fp.name")->from("{{flow_run}} fr")->leftJoin("{{flow_process}} fp", "fr.flowid = fp.flowid")->leftJoin("{{flow_run_process}} frp", "fr.runid = frp.runid AND frp.flowprocess = fp.processid")->where(array("and", "fr.runid = $runId", "frp.parent!=$flowProcess", "frp.processid < $processId", "frp.flowprocess >= 1", "frp.flowprocess!=$flowProcess"))->group("frp.flowprocess")->order("frp.processid DESC")->limit(1)->queryRow();

                    if ($temp) {
                        $flowProcessNew = $temp["flowprocess"];
                        $lastUID = $temp["uid"];
                    }

                    $log = Ibos::lang("Return to prev step") . "【{$temp["name"]}】";
                } else {
                    $flowProcessNew = $last;
                    $temp = FlowRunProcess::model()->fetch(array("select" => "uid,flowprocess", "condition" => "runid = $runId AND flowprocess = '$last' AND opflag = 1", "order" => "processid", "limit" => 1));

                    if ($temp) {
                        $lastUID = $temp["uid"];
                    }

                    $log = Ibos::lang("Return to step", "", array("{step}" => FlowProcess::model()->fetchName($flowId, $flowProcessNew)));
                }

                $data = array("runid" => $runId, "processid" => $prcsIDNew, "uid" => $lastUID, "flag" => "1", "flowprocess" => $flowProcessNew, "opflag" => "1", "topflag" => "0", "isfallback" => "1", "parent" => $flowProcess);
                FlowRunProcess::model()->add($data);
                FlowRunProcess::model()->updateAll(array("delivertime" => TIMESTAMP, "flag" => FlowConst::PRCS_TRANS), "runid = $runId AND processid = $processId AND flowprocess = '$flowProcess' AND flag IN('1','2')");
                $key = WfCommonUtil::param(array("runid" => $runId, "flowid" => $flowId, "processid" => $prcsIDNew, "flowprocess" => $flowProcessNew));
                $url = Ibos::app()->urlManager->createUrl("workflow/form/index", array("key" => $key));
                $config = array("{url}" => $url, "{msg}" => $msg);
                Notify::model()->sendNotify($lastUID, "workflow_goback_notice", $config);
                WfCommonUtil::runlog($runId, $processId, $flowProcess, $this->uid, 8, $log);
                $this->ajaxReturn(array("isSuccess" => true));
            } else {
                $this->ajaxReturn(array("isSuccess" => false));
            }
        }
    }

    public function actionTurnNextPost()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $runId = filter_input(INPUT_POST, "runid", FILTER_SANITIZE_NUMBER_INT);
            $flowId = filter_input(INPUT_POST, "flowid", FILTER_SANITIZE_NUMBER_INT);
            $processId = filter_input(INPUT_POST, "processid", FILTER_SANITIZE_NUMBER_INT);
            $flowProcess = filter_input(INPUT_POST, "flowprocess", FILTER_SANITIZE_NUMBER_INT);
            $topflag = filter_input(INPUT_POST, "topflag", FILTER_SANITIZE_NUMBER_INT);
            $op = filter_input(INPUT_POST, "op", FILTER_SANITIZE_STRIPPED);
            $this->nextAccessCheck($topflag, $runId, $processId);
            $plugin = FlowProcess::model()->fetchTurnPlugin($flowId, $flowProcess);

            if ($plugin) {
                $pluginFile = "./system/modules/workflow/plugins/turn/" . $plugin;

                if (file_exists($pluginFile)) {
                    include_once ($pluginFile);
                }
            }

            $prcsTo = filter_input(INPUT_POST, "processto", FILTER_SANITIZE_STRING);
            $prcsChoose = filter_input(INPUT_POST, "prcs_choose", FILTER_SANITIZE_STRING);
            $prcsToArr = explode(",", trim($prcsTo, ","));
            $prcsChooseArr = explode(",", trim($prcsChoose, ","));
            $message = filter_input(INPUT_POST, "message", FILTER_SANITIZE_STRING);
            $toId = $nextId = $beginUserId = $toallId = "";
            $ext = array("{url}" => Ibos::app()->urlManager->createUrl("workflow/list/index", array("op" => "category")), "{message}" => $message);

            if (isset($_POST["remind"][1])) {
                $nextId = "";

                if (isset($_POST["prcs_user_op"])) {
                    $nextId = intval($_POST["prcs_user_op"]);
                } else {
                    foreach ($prcsChooseArr as $k => $v) {
                        if (isset($_POST["prcs_user_op" . $k])) {
                            $nextId .= filter_input(INPUT_POST, "prcs_user_op" . $k, FILTER_SANITIZE_STRING) . ",";
                        }
                    }

                    $nextId = trim($nextId, ",");
                }
            }

            if (isset($_POST["remind"][2])) {
                $beginuser = FlowRunProcess::model()->fetchAllOPUid($runId, 1, true);

                if ($beginuser) {
                    $beginUserId = StringUtil::wrapId($beginuser[0]["uid"]);
                }
            }

            if (isset($_POST["remind"]["3"])) {
                $toallId = "";

                if (isset($_POST["prcs_user"])) {
                    $toallId = filter_input(INPUT_POST, "prcs_user", FILTER_SANITIZE_STRING);
                } else {
                    foreach ($prcsChooseArr as $k => $v) {
                        if (isset($_POST["prcs_user" . $k])) {
                            $toallId .= filter_input(INPUT_POST, "prcs_user" . $k, FILTER_SANITIZE_STRING);
                        }
                    }
                }
            }

            $idstr = $nextId . "," . $beginUserId . "," . $toallId;
            $toId = StringUtil::getId(StringUtil::filterStr($idstr));

            if ($toId) {
                Notify::model()->sendNotify($toId, "workflow_turn_notice", $ext);
            }

            if ($prcsChoose == "") {
                $prcsUserOp = (!empty($_POST["prcs_user_op"]) ? implode(",", StringUtil::getId($_POST["prcs_user_op"])) : "");
                $prcsUser = (!empty($_POST["prcs_user"]) ? implode(",", StringUtil::getId($_POST["prcs_user"])) : "");
                $run = FlowRun::model()->fetchByPk($runId);

                if ($run) {
                    $pId = $run["parentrun"];
                    $runName = $run["name"];
                }

                FlowRunProcess::model()->updateAll(array("flag" => FlowConst::PRCS_DONE), sprintf("runid = %d AND processid = %d AND flowprocess = %d", $runId, $processId, $flowProcess));
                FlowRunProcess::model()->updateAll(array("flag" => FlowConst::PRCS_DONE), sprintf("runid = %d AND flag = 3", $runId));
                FlowRunProcess::model()->updateAll(array("delivertime" => TIMESTAMP), sprintf("runid = %d AND processid = %d AND flowprocess = %d AND uid = %d", $runId, $processId, $flowProcess, $this->uid));
                $isUnique = FlowRunProcess::model()->getIsUnique($runId);

                if (!$isUnique) {
                    FlowRun::model()->modify($runId, array("endtime" => TIMESTAMP));
                }

                if ($pId != 0) {
                    $parentflowId = FlowRun::model()->fetchFlowIdByRunId($pId);
                    $parentFormId = FlowType::model()->fetchFormIDByFlowID($parentflowId);
                    $parentPrcs = FlowRunProcess::model()->fetchIDByChild($pId, $runId);

                    if ($parentPrcs) {
                        $parentPrcsId = $parentPrcs["processid"];
                        $parentFlowProcess = $parentPrcs["flowprocess"];
                    }

                    $parentProcess = FlowProcess::model()->fetchProcess($parentflowId, $parentPrcsId);

                    if ($parentProcess["relationout"] !== "") {
                        $relationArr = explode(",", trim($parentProcess["relationout"], ","));
                        $src = $des = $set = array();

                        foreach ($relationArr as $field) {
                            $src[] = substr($field, 0, strpos($field, "=>"));
                            $des[] = substr($field, strpos($field, "=>") + strlen("=>"));
                        }

                        $runData = WfHandleUtil::getRunData($runId);
                        $form = new ICFlowForm($parentFormId);
                        $structure = $form->parser->structure;

                        foreach ($structure as $k => $v) {
                            if (($v["data-type"] !== "label") && in_array($v["data-title"], $des)) {
                                $i = array_search($v["data-title"], $des);
                                $ptitle = $src[$i];
                                $itemData = $runData[$ptitle];
                                if (is_array($itemData) && ($v["data-type"] == "listview")) {
                                    $itemDataStr = "";
                                    $newDataStr = "";

                                    for ($j = 1; $j < count($itemData); ++$j) {
                                        foreach ($itemData[$i] as $val) {
                                            $newDataStr .= $val . "`";
                                        }

                                        $itemDataStr .= $newDataStr . "\r\n";
                                        $newDataStr = "";
                                    }

                                    $itemData = $itemDataStr;
                                }

                                $field = "data_" . $v["itemid"];
                                $set[$field] = $itemData;
                            }
                        }

                        if (!empty($set)) {
                            FlowDataN::model()->update($parentflowId, $pId, $set);
                        }
                    }

                    WfHandleUtil::updateParentOver($runId, $pId);
                    $prcsBack = $_POST["prcsback"] . "";

                    if ($prcsBack != "") {
                        $parentPrcsIdNew = $parentPrcsId + 1;
                        $data = array("runid" => $pId, "processid" => $parentPrcsIdNew, "uid" => $prcsUserOp, "flag" => "1", "flowprocess" => $prcsBack, "opflag" => 1, "topflag" => 0, "parent" => $parentFlowProcess);
                        FlowRunProcess::model()->add($data);

                        foreach (explode(",", trim($prcsUser, ",")) as $k => $v) {
                            if (($v != $prcsUserOp) && !empty($v)) {
                                $data = array("runid" => $pId, "processid" => $parentPrcsIdNew, "uid" => $v, "flag" => "1", "flowprocess" => $prcsBack, "opflag" => 0, "topflag" => 0, "parent" => $parentFlowProcess);
                                FlowRunProcess::model()->add($data);
                            }
                        }

                        $parentRunName = FlowRun::model()->fetchNameByRunID($pId);
                        $content = "[$runName]" . Ibos::lang("Log return the parent process") . ":[$parentRunName]";
                        WfCommonUtil::runlog($runId, $processId, $flowProcess, $this->uid, 1, $content);
                        FlowRun::model()->modify($pId, array("endtime" => null));
                    }
                }

                $content = Ibos::lang("Form endflow");
                WfCommonUtil::runlog($runId, $processId, $flowProcess, $this->uid, 1, $content);
            } else {
                $freeother = FlowType::model()->fetchFreeOtherByFlowID($flowId);
                $prcsChooseArrCount = count($prcsChooseArr);

                for ($i = 0; $i < $prcsChooseArrCount; $i++) {
                    $flowPrcsNext = $prcsToArr[$prcsChooseArr[$i]];
                    $prcsIdNew = $processId + 1;
                    $str = "prcs_user_op" . $prcsChooseArr[$i];
                    $prcsUserOp = implode(",", StringUtil::getId($_POST[$str]));

                    if ($freeother == 2) {
                        $prcsUserOp = WfHandleUtil::turnOther($prcsUserOp, $flowId, $runId, $processId, $flowProcess);
                    }

                    $str = "prcs_user" . $prcsChooseArr[$i];
                    $prcsUser = StringUtil::getId($_POST[$str]);
                    array_push($prcsUser, $prcsUserOp);
                    $prcsUser = implode(",", array_unique($prcsUser));

                    if ($freeother == 2) {
                        $prcsUser = WfHandleUtil::turnOther($prcsUser, $flowId, $runId, $processId, $flowProcess, $prcsUserOp);
                    }

                    $str = "topflag" . $prcsChooseArr[$i];
                    $topflag = intval($_POST[$str]);
                    $fp = FlowProcess::model()->fetchProcess($flowId, $flowPrcsNext);

                    if ($fp["childflow"] == 0) {
                        $_topflag = FlowRunProcess::model()->fetchTopflag($runId, $prcsIdNew, $flowPrcsNext);

                        if ($_topflag) {
                            $topflag = $_topflag;
                        }

                        $isOpHandle = FlowRunProcess::model()->getIsOpOnTurn($runId, $prcsIdNew, $flowPrcsNext);

                        if ($isOpHandle) {
                            $prcsUserOp = "";
                            $t_flag = 1;
                        } else {
                            $t_flag = 0;
                        }

                        foreach (explode(",", trim($prcsUser)) as $k => $v) {
                            if (($v == $prcsUserOp) || ($topflag == 1)) {
                                $opflag = 1;
                            } else {
                                $opflag = 0;
                            }

                            if ($topflag == 2) {
                                $opflag = 0;
                            }

                            $workedId = FlowRunProcess::model()->fetchProcessIDOnTurn($runId, $prcsIdNew, $flowPrcsNext, $v, $fp["gathernode"]);

                            if (!$workedId) {
                                $wrp = FlowRunProcess::model()->fetchRunProcess($runId, $processId, $flowProcess, $this->uid);

                                if ($wrp) {
                                    $otherUser = ($wrp["otheruser"] != "" ? $wrp["otheruser"] : "");
                                } else {
                                    $otherUser = "";
                                }

                                $data = array("runid" => $runId, "processid" => $prcsIdNew, "uid" => $v, "flag" => 1, "flowprocess" => $flowPrcsNext, "opflag" => $opflag, "topflag" => $topflag, "parent" => $flowProcess, "createtime" => TIMESTAMP, "otheruser" => $otherUser);
                                FlowRunProcess::model()->add($data);
                            } else {
                                if ($prcsIdNew < $workedId) {
                                    $prcsIdNew = $workedId;
                                }

                                $lastPrcsId = $workedId;
                                FlowRunProcess::model()->updateTurn($flowProcess, $prcsIdNew, $runId, $lastPrcsId, $flowPrcsNext, $v);
                            }
                        }

                        if ($t_flag == 1) {
                            FlowRunProcess::model()->updateToOver($runId, $processId, $flowProcess);
                        } else {
                            FlowRunProcess::model()->updateToTrans($runId, $processId, $flowProcess);
                        }

                        $userNameStr = User::model()->fetchRealnamesByUids($prcsUser);
                        $content = Ibos::lang("To the steps") . $prcsIdNew . "," . Ibos::lang("Transactor") . ":" . $userNameStr;
                        WfCommonUtil::runlog($runId, $processId, $flowProcess, $this->uid, 1, $content);
                    } else {
                        $runidNew = WfNewUtil::createNewRun($fp["childflow"], $prcsUserOp, $prcsUser, $runId);
                        $data = array("runid" => $runId, "processid" => $prcsIdNew, "uid" => $prcsUserOp, "flag" => 1, "flowprocess" => $flowPrcsNext, "opflag" => 1, "topflag" => 0, "parent" => $flowProcess, "childrun" => $runidNew, "createtime" => TIMESTAMP);
                        FlowRunProcess::model()->add($data);
                        FlowRunProcess::model()->updateToOver($runId, $processId, $flowProcess);
                        $content = Ibos::lang("Log new subflow") . $runidNew;
                        WfCommonUtil::runlog($runId, $processId, $flowProcess, $this->uid, 1, $content);
                    }
                }
            }

            if ($op == "manage") {
                $parent = Ibos::app()->db->createCommand()->select("parent")->from("{{flow_run_process}}")->where(sprintf("runid = %d AND processid = %d AND flowprocess = %d", $runId, $processId, $flowProcess))->queryScalar();
                $prcsIdpre = $processId - 1;
                $sql = "UPDATE {{flow_run_process}} SET flag='4' WHERE runid='$runId' AND processid='$prcsIdpre'";
                if ($parent && ($parent != "0")) {
                    $sql .= " AND flowprocess IN ('$parent')";
                }

                Ibos::app()->db->createCommand()->setText($sql)->execute();
            }

            MainUtil::setCookie("flow_turn_flag", 1, 30);
            $url = Ibos::app()->urlManager->createUrl("workflow/list/index", array("op" => "list", "type" => "trans", "sort" => "all"));
            $this->redirect($url);
        }
    }

    public function actionShowNext()
    {
        $key = EnvUtil::getRequest("key");

        if ($key) {
            $param = WfCommonUtil::param($key, "DECODE");
            $flowId = $param["flowid"];
            $runId = $param["runid"];
            $processId = $param["processid"];
            $flowProcess = $param["flowprocess"];
            $op = (isset($param["op"]) ? $param["op"] : "");
            $topflag = EnvUtil::getRequest("topflag");
            $lang = Ibos::getLangSources();
            $this->nextAccessCheck($topflag, $runId, $processId);
            $run = new ICFlowRun($runId);
            $process = new ICFlowProcess($flowId, $flowProcess);
            $notAllFinished = array();
            $parent = "";

            foreach (FlowRunProcess::model()->fetchAllByRunIDProcessID($runId, $processId) as $rp) {
                if ($rp["flowprocess"] == $flowProcess) {
                    $parent .= $rp["parent"] . ",";
                    if ((($rp["flag"] == FlowConst::PRCS_TRANS) || ($rp["flag"] == FlowConst::PRCS_DONE)) && ($rp["uid"] == $this->uid)) {
                        $turnForbidden = true;
                    } else {
                        $turnForbidden = false;
                    }

                    if (($rp["flag"] != FlowConst::PRCS_DONE) && ($rp["uid"] != $this->uid)) {
                        $notAllFinished[] = $rp["uid"];
                    }
                }
            }

            if ($turnForbidden) {
                EnvUtil::iExit(Ibos::lang("Already trans"));
            }

            if (!empty($notAllFinished)) {
                $notAllFinished = User::model()->fetchRealnamesbyUids($notAllFinished);
            } else {
                $notAllFinished = "";
            }

            if ($process->gathernode == 1) {
                foreach (FlowProcess::model()->fetchAllGatherNode($flowId, $flowProcess) as $fp) {
                    $isUntrans = FlowRunProcess::model()->getIsUntrans($runId, $fp["processid"]);

                    if (!StringUtil::findIn($fp["processid"], $parent)) {
                        if ($isUntrans) {
                            EnvUtil::iExit(Ibos::lang("Gathernode trans error"));
                        }
                    }
                }
            }

            if ($process->processto == "") {
                $prcsMax = FlowProcess::model()->fetchMaxProcessIDByFlowID($flowId);

                if ($flowProcess !== $prcsMax) {
                    $process->processto = $flowProcess + 1;
                } else {
                    $process->processto = "0";
                }
            }

            $prcsArr = explode(",", trim($process->processto, ","));
            $prcsArrCount = count($prcsArr);
            $prcsEnableCount = 0;
            $prcsStop = "S";
            $prcsback = "";
            $prcsEnableFirst = null;
            $list = array();
            $formData = WfHandleUtil::getFormData($flowId, $runId);

            foreach ($prcsArr as $key => $to) {
                $param = array("checked" => "false");

                if ($to == "0") {
                    $param["isover"] = true;
                    $param["prcsname"] = ($run->parentrun !== "0" ? Ibos::lang("End subflow") : Ibos::lang("Form endflow"));
                    $prcsStop = $key;
                    $prcsEnableCount++;

                    if ($prcsEnableCount == 1) {
                        $param["checked"] = "true";
                        $prcsEnableFirst = $key;
                    }

                    if ($run->parentrun != 0) {
                        $parentFlowId = FlowRun::model()->fetchFlowIdByRunId($run->parentrun);
                        $parentProcess = FlowRunProcess::model()->fetchIDByChild($run->parentrun, $runId);
                        $parentFlowProcess = $parentProcess["flowprocess"];
                        if ($parentFlowId && $parentFlowProcess) {
                            $temp = FlowProcess::model()->fetchProcess($parentFlowId, $parentFlowProcess);

                            if ($temp) {
                                $prcsback = $temp["processto"];
                                $backUserOP = StringUtil::wrapId($temp["autouserop"]);
                                $param["backuser"] = StringUtil::wrapId($temp["autouser"]);
                            }
                        }

                        if ($prcsback != "") {
                            $prcsuser = WfHandleUtil::getPrcsUser($flowId, $prcsback);
                            $param["prcsusers"] = sprintf("[%s]", !empty($prcsuser) ? StringUtil::iImplode($prcsuser) : "");
                            $param["display"] = ($prcsEnableFirst !== $prcsStop ? false : true);

                            if (isset($backUserOP)) {
                                $param["prcsopuser"] = $backUserOP;
                            }
                        }
                    }
                } else {
                    $param["isover"] = false;
                    $curProcess = FlowProcess::model()->fetchProcess($flowId, $to);
                    $param["prcsname"] = $curProcess["name"];
                    $processOut = FlowProcessTurn::model()->fetchByUnique($flowId, $flowProcess, $to);

                    if (!$processOut) {
                        $processOut = array("processout" => "", "conditiondesc" => "");
                    }

                    $notpass = WfHandleUtil::checkCondition($formData, $processOut["processout"], $processOut["conditiondesc"]);

                    if ($curProcess["childflow"] !== "0") {
                        $param["prcsname"] .= "(" . $lang["Subflow"] . ")";
                    }

                    if (substr($notpass, 0, 5) == "setok") {
                        $notpass = "";
                    }

                    if ($notpass == "") {
                        $prcsEnableCount++;
                        if (($prcsEnableCount == 1) || ((0 < $process->syncdeal) && !is_numeric($prcsStop))) {
                            $param["checked"] = "true";

                            if ($prcsEnableCount == 1) {
                                $prcsEnableFirst = $key;
                            }
                        }

                        unset($param["notpass"]);
                        $param["selectstr"] = $this->makeUserSelect($runId, $key, $curProcess, $param["prcsname"], $flowId, $processId);
                    } else {
                        $param["notpass"] = $notpass;
                    }
                }

                $list[$key] = $param;
            }

            if ($prcsEnableCount == 0) {
                if ($notpass == "") {
                    EnvUtil::iExit($lang["Process define error"]);
                } else {
                    EnvUtil::iExit($notpass);
                }
            }

            $data = array("lang" => $lang, "notAllFinished" => $notAllFinished, "enableCount" => $prcsEnableCount, "prcsto" => $prcsArr, "prcsback" => $prcsback, "notpass" => isset($notpass) ? $notpass : "", "process" => $process->toArray(), "run" => $run->toArray(), "runid" => $runId, "flowid" => $flowId, "processid" => $processId, "flowprocess" => $flowProcess, "count" => $prcsArrCount, "prcsStop" => $prcsStop, "topflag" => $topflag, "list" => $list, "op" => $op);
            $this->renderPartial("shownext", $data);
        }
    }

    public function actionFreeNext()
    {
        $key = EnvUtil::getRequest("key");
        $op = EnvUtil::getRequest("op");
        $topflag = EnvUtil::getRequest("topflag");
        $widget = $this->createWidget("IWWfFreeNext", array("key" => $key, "op" => $op, "topflag" => $topflag));

        if (Ibos::app()->request->getIsPostRequest()) {
            $topflag = EnvUtil::getRequest("topflagOld");
        }

        $this->nextAccessCheck($topflag, $widget->getKey("runid"), $widget->getKey("processid"));

        if (EnvUtil::submitCheck("formhash")) {
            return $widget->nextPost();
        } else {
            echo $widget->run();
        }
    }

    public function actionComplete()
    {
        $key = EnvUtil::getRequest("key");

        if ($key) {
            $param = WfCommonUtil::param($key, "DECODE");
            $processId = $param["processid"];
            $flowProcess = $param["flowprocess"];
            $runId = $param["runid"];
            $topflag = EnvUtil::getRequest("topflag");
            $opflag = EnvUtil::getRequest("opflag");
            $inajax = EnvUtil::getRequest("inajax");
            $op = EnvUtil::getRequest("op");
            $this->complete($runId, $processId, $opflag, $topflag, $inajax, $flowProcess, $op);
        }
    }

    public function actionEnd()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $id = EnvUtil::getRequest("id");

            if (WfHandleUtil::endRun($id, $this->uid)) {
                $this->ajaxReturn(array("isSuccess" => true));
            } else {
                $this->ajaxReturn(array("isSuccess" => false));
            }
        }
    }

    public function actionDel()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $id = EnvUtil::getRequest("id");

            if (FlowRun::model()->del($id, $this->uid)) {
                $this->ajaxReturn(array("isSuccess" => true));
            } else {
                $this->ajaxReturn(array("isSuccess" => false));
            }
        }
    }

    public function actionTakeBack()
    {
        $key = EnvUtil::getRequest("key");

        if ($key) {
            $param = WfCommonUtil::param($key, "DECODE");
            $status = WfHandleUtil::takeBack($param["runid"], $param["flowprocess"], $param["processid"], $this->uid);
            $this->ajaxReturn(array("status" => $status));
        }
    }

    protected function complete($runId, $processId, $opflag = 1, $topflag = 0, $inajax = 0, $flowProcess = "", $op = "")
    {
        $flowType = FlowRun::model()->fetchFlowTypeByRunId($runId);
        if ($opflag || ($op == "manage")) {
            $pidNext = $processId + 1;

            if (FlowRunProcess::model()->getHasDefaultStep($runId, $pidNext)) {
                if ($op != "manage") {
                    if ($inajax) {
                        $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Subsequent default steps in the process")));
                    } else {
                        $this->error(Ibos::lang("Subsequent default steps in the process"), $this->createUrl("list/index"));
                    }
                } else {
                    FlowRunProcess::model()->deleteByIDScope($runId, $pidNext);
                }
            }

            if ($op != "manage") {
                FlowRunProcess::model()->updateAll(array("delivertime" => TIMESTAMP), sprintf("runid = %d AND processid = %d AND uid = %d", $runId, $processId, $this->uid));
            } else {
                FlowRunProcess::model()->updateAll(array("delivertime" => TIMESTAMP), sprintf("runid = %d AND delivertime = 0", $runId));
                FlowRunProcess::model()->updateAll(array("processtime" => TIMESTAMP), sprintf("runid = %d AND processtime = 0", $runId));
            }

            FlowRunProcess::model()->updateAll(array("flag" => FlowConst::PRCS_DONE), "runid = $runId");
            FlowRun::model()->modify($runId, array("endtime" => TIMESTAMP));
            $content = ($op != "manage" ? Ibos::lang("Form endflow") : Ibos::app()->user->realname . Ibos::lang("Forced end process"));
            WfCommonUtil::runlog($runId, $processId, $flowProcess, $this->uid, 1, $content);
            $parentRun = FlowRun::model()->fetchParentByRunID($runId);

            if ($parentRun != 0) {
                $parentFlowId = FlowRun::model()->fetchFlowIdByRunId($parentRun);
                $temp = FlowRunProcess::model()->fetchIDByChild($parentRun, $runId);

                if ($temp) {
                    $parentProcessId = $temp["processid"];
                    $parentFlowprocess = $temp["flowprocess"];
                }

                $parentProcess = FlowProcess::model()->fetchProcess($parentFlowId, $parentFlowprocess);

                if ($parentProcess) {
                    $prcsBack = $parentProcess["processto"];
                    $backUserOp = $parentProcess["autouserop"];
                    $backUser = $parentProcess["autouser"];
                }

                FlowRunProcess::model()->updateToOver($parentRun, $parentProcessId, $parentFlowprocess);

                if ($prcsBack != "") {
                    $parentProcessIdNew = $parentProcessId + 1;
                    $data = array("runid" => $parentRun, "processid" => $parentProcessIdNew, "uid" => $backUserOp, "flag" => 1, "flowprocess" => $prcsBack, "opflag" => 1, "topflag" => 0, "parent" => $parentFlowprocess);
                    FlowRunProcess::model()->add($data);
                    $backUserArr = explode(",", $backUser);

                    for ($k = 0; $k < count($backUserArr); $k++) {
                        if (($backUserArr[$k] != "") && ($backUserArr[$k] != $backUserOp)) {
                            $data = array("runid" => $parentRun, "processid" => $parentProcessIdNew, "uid" => $backUserArr[$k], "flag" => 1, "flowprocess" => $prcsBack, "opflag" => 0, "topflag" => 0, "parent" => $parentFlowprocess);
                            FlowRunProcess::model()->add($data);
                        }
                    }
                } elseif (!FlowRunProcess::model()->getIsNotOver($parentRun)) {
                    FlowRun::model()->modify($parentRun, array("endtime" => TIMESTAMP));
                }
            }

            $flag = EnvUtil::getRequest("flag");
            if (($flowType == 2) && ($flag != 1)) {
                $inajax && $this->ajaxReturn(array("isSuccess" => true));
                $this->redirect($this->createUrl("list/index"));
            }

            $inajax && $this->ajaxReturn(array("isSuccess" => true));
        } else {
            $flowId = FlowRun::model()->fetchFlowIDByRunID($runId);

            if ($topflag == 2) {
                if (!FlowRunProcess::model()->getHasOtherOPUser($runId, $processId, $flowProcess, $this->uid)) {
                    if (is_null($flowProcess) || ($flowProcess == "0")) {
                        $turnpage = "showNextFree";
                    } else {
                        $turnpage = "showNext";
                    }

                    $param = array("flowid" => $flowId, "processid" => $processId, "flowprocess" => $flowProcess, "runid" => $runId);
                    $url = $this->createUrl("handle/" . $turnpage, array("key" => WfCommonUtil::param($param), "topflag" => $topflag));
                    $this->ajaxReturn(array("status" => 2, "url" => $url));
                }
            }

            $con = sprintf("runid = %d AND processid = %d AND uid = %d", $runId, $processId, $this->uid);
            if (($flowProcess !== "") && ($flowProcess !== "0")) {
                $con .= " AND flowprocess = " . $flowProcess;
            }

            FlowRunProcess::model()->updateAll(array("flag" => "4", "delivertime" => TIMESTAMP), $con);

            if (!FlowRunProcess::model()->getHasOtherAgentNotDone($runId, $processId)) {
                $run = FlowRun::model()->fetchByPk($runId);
                $uid = FlowRunProcess::model()->fetchNotDoneOpuser($runId, $processId);

                if ($uid) {
                    $param = array("runid" => $runId, "flowid" => $flowId, "processid" => $processId, "flowprocess" => $flowProcess);
                    $config = array("{runname}" => $run["name"], "{url}" => Ibos::app()->urlManager->createUrl("workflow/form/index", array("key" => WfCommonUtil::param($param))));
                    Notify::model()->sendNotify($uid, "workflow_sign_notice", $config);
                }
            }

            MainUtil::setCookie("flow_complete_flag", 1, 30);
            $url = Ibos::app()->urlManager->createUrl("workflow/list/index", array("op" => "list", "type" => "trans", "sort" => "all"));
            $this->redirect($url);
        }
    }

    public function actionAddDelay()
    {
        $key = EnvUtil::getRequest("key");
        $param = WfCommonUtil::param($key, "DECODE");
        $time = EnvUtil::getRequest("time");

        if ($time == "1") {
            $time = strtotime("+1 day");
        } elseif ($time == "2") {
            $time = strtotime("+2 day");
        } elseif ($time == "3") {
            $time = strtotime("+1 week");
        } else {
            $time = strtotime($time);
        }

        $this->checkRunAccess($param["runid"], $param["processid"]);

        if (FlowRunProcess::model()->addDelay($time, $param["runid"], $param["processid"], $param["flowprocess"])) {
            $this->ajaxReturn(array("isSuccess" => true));
        } else {
            $this->ajaxReturn(array("isSuccess" => false));
        }
    }

    public function actionRestoreDelay()
    {
        $key = EnvUtil::getRequest("key");
        $param = WfCommonUtil::param($key, "DECODE");
        $this->checkRunAccess($param["runid"], $param["processid"]);

        if (FlowRunProcess::model()->restoreDelay($param["runid"], $param["processid"], $param["flowprocess"])) {
            $this->ajaxReturn(array("isSuccess" => true));
        } else {
            $this->ajaxReturn(array("isSuccess" => false));
        }
    }

    protected function makeUserSelect($runId, $index, $process, $name, $flowId, $processId)
    {
        $lang = Ibos::getLangSource("workflow.default");
        $tablestr = "";

        if ($index) {
            $display = "none;";
        } else {
            $display = "";
        }

        if ($process["childflow"] != 0) {
            $flow = FlowType::model()->fetchByPk($process["childflow"]);

            if ($flow) {
                $type = $flow["type"];
            }

            if ($type == 2) {
                $process["prcs_id_next"] = "";
            }

            $subfp = FlowProcess::model()->fetchProcess($process["childflow"], 1);

            if ($subfp) {
                $prcsuser = WfHandleUtil::getPrcsUser($process["childflow"], $processId);
            } else {
                $prcsuser = "";
            }

            $prcsuser = sprintf("[%s]", !empty($prcsuser) ? StringUtil::iImplode($prcsuser) : "");
            $nopriv = "";
            if (empty($subfp["uid"]) && empty($subfp["deptid"]) && empty($subfp["positionid"])) {
                $nopriv = $lang["Not set step permissions"];
            }

            $tablestr = "            <div style='display:$display;' id='user_select_$index'>\r\n                <div class=\"control-group first-group\">\r\n                    <label class=\"control-label\">{$lang["Host"]}</label>\r\n                    <div class=\"controls\">\r\n                        <strong>$name $nopriv</strong>\r\n                        <input type=\"hidden\" name=\"topflag$index\" value=\"0\">\r\n                        <input id=\"prcs_user_op$index\" name=\"prcs_user_op$index\" type=\"text\" />\r\n                    </div>\r\n                </div>\r\n                <div class=\"control-group\">\r\n                    <label class=\"control-label\">{$lang["Agent"]}</label>\r\n                    <div class=\"controls\">\r\n                        <input id=\"prcs_user$index\" name=\"prcs_user$index\" type=\"text\" />\r\n                    </div>\r\n                </div>\r\n            </div>\r\n            <script>\r\n\t\t\t\t$(function(){\r\n\t\t\t\t\tvar prcsData$index = $prcsuser;\r\n\t\t\t\t\t$('#prcs_user_op$index').userSelect({\r\n\t\t\t\t\t\tbox:$('<div id=\"prcs_user_op_box$index\"></div>').appendTo(document.body),\r\n\t\t\t\t\t\tdata:Ibos.data.includes(prcsData$index),\r\n\t\t\t\t\t\ttype:'user',\r\n\t\t\t\t\t\tmaximumSelectionSize:'1'\r\n\t\t\t\t\t});\r\n\t\t\t\t\t$('#prcs_user$index').userSelect({\r\n\t\t\t\t\t\tbox:$('<div id=\"prcs_user_box$index\"></div>').appendTo(document.body),\r\n\t\t\t\t\t\tdata:Ibos.data.includes(prcsData$index),\r\n\t\t\t\t\t\ttype:'user'\r\n\t\t\t\t\t});\r\n\t\t\t\t});\r\n\t\t\t</script>";
        } else {
            if (empty($process["uid"]) && empty($process["deptid"]) && empty($process["positionid"])) {
                $nopriv = $lang["Not set step permissions"];
            }

            $prcsOpUser = $prcsUserAuto = "";
            $deptArr = DepartmentUtil::loadDepartment();

            if ($process["autotype"] == 1) {
                $uid = FlowRun::model()->fetchBeginUserByRunID($runId);
                $prcsuser = User::model()->fetchByUid($uid);
                if (($process["deptid"] == "alldept") || StringUtil::findIn($process["uid"], $prcsuser["uid"]) || StringUtil::findIn($process["deptid"], $prcsuser["alldeptid"]) || StringUtil::findIn($process["positionid"], $prcsuser["allposid"])) {
                    $prcsOpUser = $prcsuser["uid"];
                    $prcsUserAuto = $prcsuser["uid"] . ",";
                }
            } elseif (in_array($process["autotype"], array(2, 4, 5, 6))) {
                if ($process["autobaseuser"] != 0) {
                    $baseUid = FlowRunProcess::model()->fetchBaseUid($runId, $process["autobaseuser"]);

                    if ($baseUid) {
                        $baseuser = User::model()->fetchByUid($baseUid);
                        $autodept = $baseuser["deptid"];
                    } else {
                        $autodept = 0;
                    }
                } else {
                    $autodept = Ibos::app()->user->deptid;
                }

                if (0 < intval($autodept)) {
                    if ($process["autotype"] == 2) {
                        $tmpdept = $autodept;
                    } else {
                        if (($process["autotype"] == 4) || ($process["autotype"] == 6)) {
                            $tmpdept = ($deptArr[$autodept]["pid"] == 0 ? $autodept : $deptArr[$autodept]["pid"]);
                        } elseif ($process["autotype"] == 5) {
                            $deptStr = Department::model()->queryDept($autodept, true);
                            $temp = explode(",", $deptStr);
                            $count = count($temp);
                            $dept = (isset($temp[$count - 2]) ? $temp[$count - 2] : $autodept);

                            if ($deptArr[$dept]["pid"] != 0) {
                                $tmpdept = $deptArr[$dept]["deptid"];
                            } else {
                                $tmpdept = $autodept;
                            }
                        }
                    }

                    $manager = $deptArr[$tmpdept]["manager"];
                    if (($process["autotype"] == 4) || ($process["autotype"] == 6)) {
                        $leader = $deptArr[$autodept]["leader"];
                        $subleader = $deptArr[$autodept]["subleader"];
                        if (($leader != "0") && ($process["autotype"] == 4)) {
                            $manager = $leader;
                        }

                        if (($subleader != "0") && ($process["autotype"] == 6)) {
                            $manager = $subleader;
                        }
                    }

                    if (!empty($manager)) {
                        $muser = User::model()->fetchByUid($manager);

                        if (!empty($muser)) {
                            if (($process["deptid"] == "alldept") || StringUtil::findIn($process["uid"], $muser["uid"]) || StringUtil::findIn($process["deptid"], $muser["alldeptid"]) || StringUtil::findIn($process["positionid"], $muser["allposid"])) {
                                $prcsUserAuto = $muser["uid"] . ",";
                            }

                            if ($prcsUserAuto != "") {
                                $prcsOpUser = strtok($prcsUserAuto, ",");
                            }
                        }
                    } else {
                        $userPerMax = "";

                        foreach (User::model()->fetchAllOtherManager($tmpdept) as $user) {
                            $user = User::model()->fetchByUid($user["uid"]);
                            $uid = $user["uid"];
                            $position = $user["allposid"];
                            if (($process["deptid"] == "alldept") || StringUtil::findIn($process["uid"], $uid) || StringUtil::findIn($process["deptid"], $user["alldeptid"]) || StringUtil::findIn($process["positionid"], $position)) {
                                if ($userPerMax == "") {
                                    $prcsOpUser = $uid;
                                    $prcsUserAuto .= $uid . ",";
                                    $userPerMax = $position;
                                } elseif ($position == $userPerMax) {
                                    $prcsUserAuto .= $uid . ",";
                                }
                            }
                        }
                    }
                }
            } elseif ($process["autotype"] == 3) {
                $autouserop = User::model()->fetchByUid($process["autouserop"]);

                if (!empty($autouserop)) {
                    if (($process["deptid"] == "alldept") || StringUtil::findIn($process["uid"], $autouserop["uid"]) || StringUtil::findIn($process["deptid"], $autouserop["alldeptid"]) || StringUtil::findIn($process["positionid"], $autouserop["allposid"])) {
                        $prcsOpUser = $autouserop["uid"];
                    }
                }

                if (!empty($process["autouser"])) {
                    foreach (User::model()->fetchAllByUids(explode(",", trim($process["autouser"], ","))) as $user) {
                        if (($process["deptid"] == "alldept") || StringUtil::findIn($process["uid"], $user["uid"]) || StringUtil::findIn($process["deptid"], $user["alldeptid"]) || StringUtil::findIn($process["positionid"], $user["allposid"])) {
                            $prcsUserAuto .= $user["uid"] . ",";
                        }
                    }
                }
            } elseif ($process["autotype"] == 7) {
                if (is_numeric($process["autouser"])) {
                    $itemData = FlowDataN::model()->fetchItem($process["autouser"], $process["flowid"], $runId);
                    $tmp = strtok($itemData, ",");
                    $userarr = array();

                    while ($tmp) {
                        $userarr[$tmp] = array();
                        $tmp = strtok(",");
                    }

                    $tempArray = explode(",", trim($itemData, ","));

                    foreach ($tempArray as $key => $value) {
                        if (!is_numeric($value)) {
                            if (strpos($value, "u") === false) {
                                continue;
                            } else {
                                $value = implode(",", StringUtil::getId($value));
                            }
                        }

                        $value = User::model()->fetchRealnameByUid($value, "");
                        $tempArray[$key] = $value;
                    }

                    foreach (User::model()->fetchAllByRealnames($tempArray) as $k => $v) {
                        $dept = Department::model()->queryDept($v["alldeptid"]);
                        if (($process["deptid"] == "alldept") || StringUtil::findIn($process["uid"], $v["uid"]) || StringUtil::findIn($process["deptid"], $dept) || StringUtil::findIn($process["positionid"], $v["allposid"])) {
                            $prcsUserAuto .= $v["uid"] . ",";
                        }
                    }

                    if ($prcsUserAuto != "") {
                        $prcsOpUser = strtok($prcsUserAuto, ",");
                    }
                }
            } else {
                if (($process["autotype"] == 8) && is_numeric($process["autouser"])) {
                    $uid = FlowRunProcess::model()->fetchBaseUid($runId, $process["autouser"]);

                    if ($uid) {
                        $temp = User::model()->fetchByUid($uid);

                        if ($temp) {
                            if (($process["deptid"] == "alldept") || StringUtil::findIn($process["uid"], $temp["uid"]) || StringUtil::findIn($process["deptid"], $temp["alldeptid"]) || StringUtil::findIn($process["positionid"], $temp["allposid"])) {
                                $prcsOpUser = $prcsUserAuto = $temp["uid"];
                                $prcsUserAuto .= ",";
                            }
                        }
                    }
                } elseif ($process["autotype"] == 9) {
                    $main = Ibos::app()->user->deptid;

                    foreach (User::model()->fetchAllFitDeptUser($main) as $k => $v) {
                        if (($process["deptid"] == "alldept") || StringUtil::findIn($process["uid"], $v["uid"]) || StringUtil::findIn($process["deptid"], $v["alldeptid"]) || StringUtil::findIn($process["positionid"], $v["allposid"])) {
                            $prcsUserAuto .= $v["uid"] . ",";
                        }
                    }

                    if (!empty($prcsUserAuto)) {
                        $prcsOpUser = strtok($prcsUserAuto, ",");
                    }
                } elseif ($process["autotype"] == 10) {
                    $main = Ibos::app()->user->deptid;
                    $deptStr = Department::model()->queryDept($main, true);
                    $temp = explode(",", $deptStr);
                    $count = count($temp);
                    $dept = (isset($temp[$count - 2]) ? $temp[$count - 2] : $main);

                    if ($deptArr[$dept]["pid"] != 0) {
                        $tmpdept = $deptArr[$dept]["deptid"];
                    } else {
                        $tmpdept = $main;
                    }

                    foreach (User::model()->fetchAllFitDeptUser($tmpdept) as $k => $v) {
                        if (($process["deptid"] == "alldept") || StringUtil::findIn($process["uid"], $v["uid"]) || StringUtil::findIn($process["deptid"], $v["alldeptid"]) || StringUtil::findIn($process["positionid"], $v["allposid"])) {
                            $prcsUserAuto .= $v["uid"] . ",";
                        }
                    }

                    if (!empty($prcsUserAuto)) {
                        $prcsOpUser = strtok($prcsUserAuto, ",");
                    }
                } else {
                    if (($process["uid"] != "") && ($process["deptid"] == "") && ($process["positionid"] == "")) {
                        $prcsUserArr = explode(",", $process["uid"]);
                        $prcsUserCount = count($prcsUserArr) - 1;

                        if ($prcsUserCount == 1) {
                            $prcsUserAuto = $process["uid"];
                            $prcsOpUser = $prcsUserAuto;
                        }
                    }
                }
            }

            $prcsuser = WfHandleUtil::getPrcsUser($flowId, $process["processid"]);
            $prcsuser = sprintf("[%s]", !empty($prcsuser) ? StringUtil::iImplode($prcsuser) : "");

            if ($process["userlock"] != 1) {
                $attr = "islock=\"1\"";
            } else {
                $attr = "";
            }

            if (!empty($prcsOpUser)) {
                $prcsOpUser = StringUtil::wrapId($prcsOpUser);
            }

            if (!empty($prcsUserAuto)) {
                $prcsUserAuto = StringUtil::wrapId(StringUtil::filterStr($prcsUserAuto));
            }

            $tablestr = "        <div class=\"control-group\" style=\"display:$display;\" id='user_select_$index'>\r\n        \t<div class=\"control-group\">\r\n\t\t\t\t<label class=\"control-label\">&nbsp;</label>\r\n        \t\t<div class=\"controls\">\r\n        \t\t\t<select name=\"topflag$index\" id=\"topflag$index\">\r\n        \t\t\t\t<option value=\"0\">{$lang["Host"]}</option>\r\n        \t\t\t\t<option value=\"1\">{$lang["First receiver host"]}</option>\r\n        \t\t\t\t<option value=\"2\">{$lang["No host"]}</option>\r\n        \t\t\t</select>\r\n        \t\t</div>\r\n        \t</div>\r\n            <div class=\"control-group first-group\">\r\n                <label class=\"control-label\">{$lang["Host"]}</label>\r\n                <div class=\"controls\">\r\n\t\t\t\t\t<input id=\"prcs_user_op$index\" $attr name=\"prcs_user_op$index\"  value=\"$prcsOpUser\" type=\"text\" />\r\n\t\t\t\t</div>\r\n            </div>\r\n            <div class=\"control-group\">\r\n                <label class=\"control-label\">{$lang["Agent"]}</label>\r\n                <div class=\"controls\">\r\n\t\t\t\t\t<input id=\"prcs_user$index\" name=\"prcs_user$index\" value=\"$prcsUserAuto\" type=\"text\" />\r\n\t\t\t\t</div>\r\n            </div>\r\n        </div>\r\n        <script>\r\n            $(function(){\r\n\t\t\t\tvar prcsData$index = $prcsuser;\r\n                var puo = $('#prcs_user_op$index');\r\n                var pu = $('#prcs_user$index');\r\n                var lockHostOption = (puo.attr(\"islock\") == 1);// 是否锁定主办人选项，即不可修改\r\n\t\t\t\tvar topdef = '{$process["topdefault"]}';\r\n\t\t\t\t// 主办类型选择\r\n\t\t\t\t$(\"#topflag$index\").on(\"change\", function(){\r\n\t\t\t\t\t$(this).closest(\".control-group\").next().toggle(this.value == \"0\");\r\n\t\t\t\t}).val(topdef).change().prop(\"readonly\", lockHostOption);\r\n                puo.userSelect({\r\n                    data:Ibos.data.includes(prcsData$index),\r\n\t\t\t\t\ttype:'user',\r\n                    maximumSelectionSize:'1'\r\n                });\r\n                pu.userSelect({\r\n                    data:Ibos.data.includes(prcsData$index),\r\n\t\t\t\t\ttype:'user'\r\n                });\r\n            });\r\n        </script>";
        }

        return $tablestr;
    }

    protected function nextAccessCheck($topflag, $runId, $processId)
    {
        $per = WfCommonUtil::getRunPermission($runId, $this->uid, $processId);

        if ($topflag != 2) {
            if (!StringUtil::findIn($per, 1) && !StringUtil::findIn($per, 2) && !StringUtil::findIn($per, 3)) {
                EnvUtil::iExit("必须是系统管理员，主办人，管理或监控人才能进行操作");
            }
        } elseif (!StringUtil::findIn($per, 4)) {
            EnvUtil::iExit("您不是经办人，没有权限进行操作。");
        }
    }
}
