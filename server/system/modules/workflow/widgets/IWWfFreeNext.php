<?php

class IWWfFreeNext extends IWWfBase
{
    const VIEW = "wfwidget.shownextfree";

    /**
     * 视图要用到的变量
     * @var array 
     */
    private $_var = array();
    /**
     * 额外操作 $op='manage'
     * @var string 
     */
    private $_op;
    /**
     * 没有完成当前步骤的人员
     * @var string 
     */
    private $_notFinished = "";
    /**
     *
     * @var type 
     */
    private $_topflag = "";

    public function init()
    {
        $key = $this->getKey();
        $flow = new ICFlowType(intval($key["flowid"]), true);
        $this->_var = array_merge($key, array("lang" => Ibos::getLangSources(), "flow" => $flow, "key" => $this->makeKey($key), "flowName" => $flow->name, "freePreset" => $flow->freepreset, "runName" => FlowRun::model()->fetchNameByRunID($key["runid"])));
        parent::init();
    }

    public function run()
    {
        $var = $this->_var;
        $allItemName = $this->getAllItemName($var);
        $var["op"] = $this->getOp();
        $itemArr = (is_array($allItemName) ? $allItemName : explode(",", $allItemName));
        $var["itemArr"] = $itemArr;
        $var["itemCount"] = count($itemArr);
        $var["prcsUser"] = $this->getProcessUser($var);
        $var["preset"] = $this->getPreset($var["processid"], $var["runid"], $var["lang"]);
        $var["notAllFinished"] = $this->_notFinished;
        $var["topflag"] = $this->getTopflag();
        $this->render(self::VIEW, $var);
    }

    public function nextPost()
    {
        $var = $this->_var;
        $topflag = $this->getTopflag();
        $topflagOld = filter_input(INPUT_POST, "topflagOld", FILTER_SANITIZE_NUMBER_INT);
        $prcsUserOpNext = implode(",", StringUtil::getId(filter_input(INPUT_POST, "prcsUserOp", FILTER_SANITIZE_STRING)));
        $op = $this->getOp();
        $prcsUserNext = StringUtil::getId(filter_input(INPUT_POST, "prcsUser", FILTER_SANITIZE_STRING));
        array_push($prcsUserNext, $prcsUserOpNext);
        $prcsUserNext = implode(",", array_unique($prcsUserNext));
        $freeOther = $var["flow"]->freeother;
        $processIdNext = $var["processid"] + 1;
        $preset = filter_input(INPUT_POST, "preset", FILTER_SANITIZE_NUMBER_INT);

        if (is_null($preset)) {
            $lineCount = filter_input(INPUT_POST, "lineCount", FILTER_SANITIZE_NUMBER_INT);

            for ($i = 0; $i <= $lineCount; $i++) {
                $prcsIdSet = $processIdNext + $i;
                $tmp = ($i == 0 ? "" : $i);
                $str = "prcsUserOp" . $tmp;
                $prcsUserOp = implode(",", StringUtil::getId(filter_input(INPUT_POST, $str, FILTER_SANITIZE_STRING)));
                $prcsUserOpOld = $prcsUserOp;

                if ($freeOther == 2) {
                    $prcsUserOp = WfHandleUtil::turnOther($prcsUserOp, $var["flowid"], $var["runid"], $var["processid"], $var["flowprocess"]);
                }

                $str = "prcsUser" . $tmp;
                $prcsUser = StringUtil::getId(filter_input(INPUT_POST, $str, FILTER_SANITIZE_STRING));
                array_push($prcsUser, $prcsUserOp);
                $prcsUser = implode(",", array_unique($prcsUser));

                if ($freeOther == 2) {
                    $prcsUser = WfHandleUtil::turnOther($prcsUser, $var["flowid"], $var["runid"], $var["processid"], $var["flowprocess"], $prcsUserOpOld);
                }

                $str = "topflag" . $tmp;
                $topflag = filter_input(INPUT_POST, $str, FILTER_SANITIZE_NUMBER_INT);
                $prcsFlag = ($i == 0 ? 1 : 5);
                $str = "freeItem" . $tmp;
                $freeItem = filter_input(INPUT_POST, $str, FILTER_SANITIZE_STRING);
                if (is_null($freeItem) || empty($freeItem)) {
                    $freeItem = filter_input(INPUT_POST, "freeItemOld", FILTER_SANITIZE_STRING);
                }

                $tok = strtok($prcsUser, ",");

                while ($tok != "") {
                    if (($tok == $prcsUserOp) || ($topflag == 1)) {
                        $opflag = 1;
                    } else {
                        $opflag = 0;
                    }

                    if ($topflag == 2) {
                        $opflag = 0;
                    }

                    if ($opflag == 0) {
                        $freeItem = "";
                    }

                    $data = array("runid" => $var["runid"], "processid" => $prcsIdSet, "flowprocess" => $prcsIdSet, "uid" => $tok, "flag" => $prcsFlag, "opflag" => $opflag, "topflag" => $topflag, "freeitem" => $freeItem);
                    FlowRunProcess::model()->add($data);
                    $tok = strtok(",");
                }
            }
        } else {
            FlowRunProcess::model()->updateAll(array("flag" => 1), sprintf("runid = %d AND processid = %d", $var["runid"], $processIdNext));
        }

        $presetDesc = (!is_null($preset) ? $var["lang"]["Default step"] : "");
        $userNameStr = User::model()->fetchRealnamesByUids($prcsUserNext);
        $content = $var["lang"]["To the steps"] . $processIdNext . $presetDesc . "," . $var["lang"]["Transactor"] . ":" . $userNameStr;
        WfCommonUtil::runlog($var["runid"], $var["processid"], 0, Ibos::app()->user->uid, 1, $content);
        FlowRunProcess::model()->updateAll(array("flag" => 3), sprintf("runid = %d AND processid = %d", $var["runid"], $var["processid"]));
        FlowRunProcess::model()->updateAll(array("delivertime" => TIMESTAMP), sprintf("runid = %d AND processid = %d AND uid = %d", $var["runid"], $var["processid"], Ibos::app()->user->uid));
        $content = filter_input(INPUT_POST, "message", FILTER_SANITIZE_STRING);

        if (!is_null($content)) {
            $key = array("runid" => $var["runid"], "flowid" => $var["flowid"], "processid" => $processIdNext, "flowprocess" => $var["flowprocess"]);
            $ext = array("{url}" => Ibos::app()->createUrl("workflow/form/index", array("key" => WfCommonUtil::param($key))), "{message}" => $content);
            Notify::model()->sendNotify($prcsUserNext, "workflow_turn_notice", $ext);
        }

        if ($op == "manage") {
            $prcsFirst = $var["processid"] - 1;
            $prcsNext = $var["processid"] - 2;
            FlowRunProcess::model()->updateAll(array("flag" => 4), sprintf("runid = %d AND (processid = %d OR processid = %d)", $var["runid"], $prcsFirst, $prcsNext));
        }

        MainUtil::setCookie("flow_turn_flag", 1, 30);
        $url = Ibos::app()->createUrl("workflow/list/index", array("op" => "list", "type" => "trans", "sort" => "all"));
        $this->getController()->redirect($url);
    }

    public function setOp($op)
    {
        $this->_op = StringUtil::filterCleanHtml($op);
    }

    public function getOp()
    {
        return $this->_op;
    }

    public function setTopflag($topFlag)
    {
        $this->_topflag = $topFlag;
    }

    public function getTopflag()
    {
        return $this->_topflag;
    }

    private function getProcessUser(&$var)
    {
        $return = array();

        for ($i = 1; $i <= $var["processid"]; $i++) {
            $querys = Ibos::app()->db->createCommand()->select("*")->from("{{flow_run_process}}")->where(sprintf("runid = %d AND processid = %d", $var["runid"], $i))->order("opflag DESC")->queryAll();
            $userNamestr = "";
            $this->handleUserNames($querys, $userNamestr, $i, $var);
            $return[$i]["userName"] = $userNamestr;
        }

        return $return;
    }

    private function handleUserNames($rows, &$userNamestr, $step, $var)
    {
        foreach ($rows as $row) {
            $userName = User::model()->fetchRealnameByUid($row["uid"]);

            if ($row["opflag"]) {
                $userName .= "[{$var["lang"]["Host user"]}]";
            }

            $isCurStep = $step == $var["processid"];
            $isMe = $row["uid"] == Ibos::app()->user->uid;
            $userNamestr .= $this->handleFlag(intval($row["flag"]), $userName, $var["lang"]);
            if ($isCurStep && ($row["flag"] != 4) && !$isMe) {
                $this->_notFinished .= $userName . ",";
            }

            $isDone = ($row["flag"] == 3) || ($row["flag"] == 4);
            $notInManageMode = $var["op"] != "manage";
            if ($isCurStep && $isDone && $isMe && $notInManageMode) {
                EnvUtil::iExit($var["lang"]["Already trans"]);
            }

            $userNamestr = StringUtil::filterStr($userNamestr);
        }
    }

    private function handleFlag($flag, $userName, $lang)
    {
        $userNamestr = "";

        if ($flag == 1) {
            $userNamestr .= "<span style='color:red;'>" . $userName . "({$lang["Not receive"]})</span><br/>";
        } elseif ($flag == 2) {
            $userNamestr .= "<span style='color:red;'>" . $userName . "({$lang["In handle"]})</span><br/>";
        } elseif ($flag == 4) {
            $userNamestr .= "<span style='color:green;'>" . $userName . "({$lang["Have been transferred"]})</span><br/>";
        } else {
            $userNamestr .= $userName . "<br>";
        }

        return $userNamestr;
    }

    private function getAllItemName($var)
    {
        $flow = $var["flow"];

        if ($var["processid"] == 1) {
            $allItemName = WfFormUtil::getAllItemName($flow->form->structure, array(), ($flow->allowattachment == "1" ? "" : "[A@]") . "[B@]");
            unset($flow);
        } else {
            $allItemName = FlowRunProcess::model()->fetchFreeitem($var["runid"], $var["processid"]);
        }

        return $allItemName;
    }

    private function getPreset($processId, $runId, $lang)
    {
        $preset = "";
        $querys = Ibos::app()->db->createCommand()->select("*")->from("{{flow_run_process}}")->where("runid = $runId AND processid = $processId AND flag = 5")->order("opflag DESC")->queryAll();

        foreach ($querys as $row) {
            $userName = User::model()->fetchRealnameByUid($row["uid"]);

            if ($row["opflag"]) {
                $userName .= "[{$lang["Host user"]}]";
            }

            $preset .= $userName . ";";
        }

        return $preset;
    }
}
