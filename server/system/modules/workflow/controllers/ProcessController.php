<?php

class WorkflowProcessController extends WfsetupBaseController
{
    /**
     * 步骤设计器不适用全局layout
     * @var boolean 
     */
    public $layout = false;
    /**
     * 工作流类型实例
     * @var mixed 
     */
    protected $flow;

    public function init()
    {
        $flowId = intval(EnvUtil::getRequest("flowid"));

        if (!empty($flowId)) {
            $this->flow = new ICFlowType($flowId);
        } else {
            $this->error(Ibos::lang("Parameters error", "error"));
        }

        parent::init();
    }

    public function actionIndex()
    {
        $data = array("flowId" => $this->flow->getID(), "flowName" => $this->flow->name);
        $this->render("index", $data);
    }

    public function actionGetProcessInfo()
    {
        $processId = intval(EnvUtil::getRequest("processid"));
        $process = new ICFlowProcess($this->flow->getID(), $processId);
        $data = $process->getProcessInfo();
        $data["lang"] = Ibos::getLangSources();
        $content = $this->renderPartial("processInfo", $data, true);
        $this->ajaxReturn(array("isSuccess" => true, "data" => $content));
    }

    public function actionGetProcess()
    {
        $data = FlowProcess::model()->fetchAllByFlowId($this->flow->getID());
        $list = $this->handleListData($data);
        $this->ajaxReturn(array("isSuccess" => true, "data" => $list));
    }

    public function actionAdd()
    {
        if (EnvUtil::submitCheck("formhash")) {
            if (!empty($_POST["steps"])) {
                $flowId = $this->flow->getID();
                $new = array();

                foreach ($_POST["steps"] as $index => $newProcess) {
                    $data = array("flowid" => $flowId, "name" => StringUtil::filterCleanHtml($newProcess["name"]), "processid" => intval($newProcess["processId"]));
                    $new[$index] = FlowProcess::model()->add($data, true);
                }

                CacheUtil::rm("flowprocess_" . $flowId);
                $this->ajaxReturn(array("isSuccess" => true, "data" => $new));
            }
        }
    }

    public function actionSaveView()
    {
        if (EnvUtil::submitCheck("formhash")) {
            if (isset($_POST["data"])) {
                $updateData = $processIdMapping = array();

                foreach ($_POST["data"]["steps"] as $step) {
                    $updateData[$step["processid"]] = array("setleft" => intval($step["left"]), "settop" => intval($step["top"]));
                }

                if (isset($_POST["data"]["connects"])) {
                    foreach ($_POST["data"]["connects"] as $value) {
                        list($prcs, $to) = explode(",", $value["prcs"]);

                        if (isset($updateData[$prcs]["processto"])) {
                            $updateData[$prcs]["processto"] .= "," . $to;
                        } else {
                            $updateData[$prcs]["processto"] = $to;
                        }
                    }
                }

                FlowProcess::model()->updateAll(array("processto" => ""), sprintf("flowid = %d", $this->flow->getID()));

                foreach ($updateData as $processId => $data) {
                    if (!empty($data["processto"])) {
                        FlowProcessTurn::model()->deleteAll(sprintf("flowid = %d AND processid = %d AND NOT FIND_IN_SET(`to`,'%s')", $this->flow->getID(), $processId, $data["processto"]));
                    }

                    FlowProcess::model()->updateAll($data, sprintf("flowid = %d AND processid = %d", $this->flow->getID(), $processId));
                }

                CacheUtil::rm("flowprocess_" . $this->flow->getID());
                $this->ajaxReturn(array("isSuccess" => true));
            }
        }
    }

    public function actionEdit()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $newPrcsId = intval(EnvUtil::getRequest("processid"));
            $oldPrcsId = intval(EnvUtil::getRequest("oldprcsid"));
            if (($newPrcsId != $oldPrcsId) && FlowProcess::model()->checkProcessIdIsExist($this->flow->getID(), $newPrcsId)) {
                $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Step number already exists")));
            }

            $this->setGuideProcess($this->flow, 3);
            $data = $this->beforeSave();
            FlowProcess::model()->updateAll($data, sprintf("flowid = %d AND processid = %d", $this->flow->getID(), $oldPrcsId));

            if ($newPrcsId != $oldPrcsId) {
                FlowProcess::model()->updateProcessto($this->flow->getID(), $oldPrcsId, $newPrcsId);
            }

            $process = new ICFlowProcess($this->flow->getID(), $data["processid"]);
            $return = $this->handleListData(array($process->toArray()));
            CacheUtil::rm("flowprocess_" . $this->flow->getID());
            $this->ajaxReturn(array("isSuccess" => true, "data" => array_shift($return)));
        } else {
            $processId = intval(EnvUtil::getRequest("processid"));
            $op = EnvUtil::getRequest("op");
            $opList = array("base", "field", "handle", "condition", "setting");

            if (!in_array($op, $opList)) {
                $op = "base";
            }

            $process = new ICFlowProcess($this->flow->getID(), $processId);
            $prcs = $process->toArray();

            if ($prcs["type"] == "1") {
                $op = "base";
            }

            $structure = $this->flow->form->parser->getStructure();
            $data = array("lang" => Ibos::getLangSources(array("workflow.item")), "prcs" => $prcs, "structure" => $structure, "op" => $op, "flows" => FlowType::model()->fetchAllFlow());
            $this->handleBase($data);
            $this->handleFormItem($data);
            $this->handleProcess($data);
            $this->renderPartial("edit", $data);
        }
    }

    public function actionDel()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $processId = intval(EnvUtil::getRequest("processid"));
            $flowId = $this->flow->getID();
            FlowProcess::model()->del($flowId, $processId);
            $this->ajaxReturn(array("isSuccess" => true));
        }
    }

    protected function handleBase(&$data)
    {
        if ($data["prcs"]["deptid"] == "alldept") {
            $data["prcs"]["prcsuser"] = "c_0";
        } else {
            $uid = StringUtil::wrapId($data["prcs"]["uid"]);
            $deptId = StringUtil::wrapId($data["prcs"]["deptid"], "d");
            $posId = StringUtil::wrapId($data["prcs"]["positionid"], "p");
            $prcsUser = sprintf("%s,%s,%s", $uid, $deptId, $posId);
            $data["prcs"]["prcsuser"] = StringUtil::filterStr($prcsUser);
        }

        $data["prcs"]["autouserop"] = StringUtil::wrapId($data["prcs"]["autouserop"]);
        $data["prcs"]["autouser"] = StringUtil::wrapId($data["prcs"]["autouser"]);

        if (!empty($data["prcs"]["attachpriv"])) {
            $data["prcs"]["attachpriv"] = explode(",", $data["prcs"]["attachpriv"]);
        } else {
            $data["prcs"]["attachpriv"] = array();
        }
    }

    protected function handleProcess(&$data)
    {
        $p = $data["prcs"];
        $data["backprcs"] = FlowProcess::model()->fetchAllOtherProcess($p["flowid"], $p["processid"]);
        $data["autoprcs"] = array();

        foreach (FlowProcess::model()->fetchAllProcessNameByFlowId($p["flowid"]) as $process) {
            $data["autoprcs"][$process["processid"]] = $process["name"];
        }

        if ($p["processto"] !== "") {
            $conItem = array();

            if (isset($data["allItemName"])) {
                foreach ($data["allItemName"] as $item) {
                    $conItem[] = "<option>" . $item . "</option>";
                }
            }

            $data["conItem"] = $conItem;
            $data["con"] = $this->getCon($this->flow->getID(), $p["processid"], $p["processto"]);
        }
    }

    protected function handleFormItem(&$data)
    {
        $p = $data["prcs"];
        $processItem = (!empty($p["processitem"]) ? explode(",", $p["processitem"]) : array());
        $hiddenItem = (!empty($p["hiddenitem"]) ? explode(",", $p["hiddenitem"]) : array());
        $microItem = (!empty($p["processitemauto"]) ? explode(",", $p["processitemauto"]) : array());
        $checkField = $checkRule = array();

        if (!empty($p["checkitem"])) {
            $items = explode(",", $p["checkitem"]);

            foreach ($items as $index => $item) {
                list($checkField[$index], $checkRule[$index]) = explode("=", $item);
            }
        }

        $data["check"] = $data["prcsItem"] = $data["microItem"] = $data["hiddenItem"] = $data["parentField"] = array();

        foreach ($data["structure"] as &$config) {
            if ($config["data-type"] !== "label") {
                $title = $config["data-title"];
                $id = $config["itemid"];

                if (in_array($title, $processItem)) {
                    $data["prcsItem"][] = $id;
                }

                if (in_array($title, $microItem)) {
                    $data["microItem"][] = $id;
                }

                if (in_array($title, $hiddenItem)) {
                    $data["hiddenItem"][] = $id;
                }

                if (in_array($title, $checkField)) {
                    $index = array_search($title, $checkField);
                    $index & ($data["check"][$id] = $checkRule[$index]);
                }

                if ($config["data-type"] != "sign") {
                    $data["parentField"][] = $title;
                }

                $config["desc"] = $this->getFormFieldDesc($config, $data["lang"]);
            }
        }

        if (strstr($p["processitem"], "[A@],")) {
            $data["prcsItem"][] = "a";
        }

        if (!empty($data["check"])) {
            $data["checkStr"] = CJSON::encode($data["check"]);
        }

        $data["checkList"] = Regular::model()->fetchAll();
        $data["allItemName"] = WfFormUtil::getAllItemName($data["structure"], array(), "[A@],[B@]");
    }

    protected function getCon($flowId, $processId, $toIds)
    {
        $conArr = array();
        $idPart = explode(",", trim($toIds, ","));

        foreach ($idPart as $toId) {
            $conArr[$toId] = array();
            $con = FlowProcessTurn::model()->fetchByUnique($flowId, $processId, $toId);

            if (!empty($con)) {
                if (!empty($con["processout"])) {
                    $part = explode("\n", $con["processout"]);

                    while (list(, $condition) = each($part)) {
                        if (!empty($condition)) {
                            $conArr[$toId]["options"][] = $condition;
                        }
                    }
                }

                $conArr[$toId]["desc"] = $con["conditiondesc"];
            } else {
                $conArr[$toId]["desc"] = "";
            }

            if ($toId == "0") {
                $conArr[$toId]["name"] = Ibos::lang("End step");
            } else {
                $conArr[$toId]["name"] = FlowProcess::model()->fetchName($flowId, $toId);
            }
        }

        return $conArr;
    }

    protected function getFormFieldDesc($config, $lang)
    {
        $mapping = array(
            "input"    => array("auto" => $lang["Macro control"] . ":" . $lang["Single input box"], "text" => $lang["Single input box"], "checkbox" => $lang["Check box"], "radio" => $lang["Radio buttons"], "date" => $lang["Calendar control"], "calc" => $lang["Calculate control"], "user" => $lang["User control"]),
            "textarea" => array("textarea" => $lang["Multiline input box"] . (isset($config["data-rich"]) && ($config["data-rich"] == "1") ? ":" . $lang["Rich text"] : "")),
            "select"   => array("auto" => $lang["Macro control"] . ":" . $lang["Dropdown menu"], "select" => $lang["Dropdown menu"]),
            "button"   => array("fetch" => $lang["Data selection control"], "form-data" => $lang["Form data control"]),
            "img"      => array("sign" => $lang["Signature control"], "listview" => $lang["List control"], "progressbar" => $lang["Progressbar control"], "imgupload" => $lang["Image upload control"], "qrcode" => $lang["Qr code control"], "fileupload" => $lang["Attach upload control"])
        );
        $tag = $config["tag"];
        $type = $config["data-type"];
        return isset($mapping[$tag][$type]) ? $mapping[$tag][$type] : "";
    }

    protected function handleListData($data)
    {
        foreach ($data as $process) {
            $prcs = array("id" => intval($process["id"]), "processid" => intval($process["processid"]), "left" => $process["setleft"], "top" => $process["settop"], "name" => $process["name"], "to" => $process["processto"]);
            $return[] = $prcs;
        }

        return $return;
    }

    private function beforeSave()
    {
        $data = &$_POST;
        $process = array("processid" => intval($data["processid"]), "type" => intval($data["type"]));
        if (isset($data["attachpriv"]) && is_array($data["attachpriv"])) {
            $process["attachpriv"] = implode(",", $data["attachpriv"]);
        } else {
            $process["attachpriv"] = "";
        }

        if (!empty($data["prcsuser"])) {
            $users = StringUtil::getId($data["prcsuser"], true);

            if (isset($users["c"])) {
                $process["deptid"] = "alldept";
            } else {
                if (isset($users["d"])) {
                    $process["deptid"] = implode(",", $users["d"]);
                } else {
                    $process["deptid"] = "";
                }

                if (isset($users["p"])) {
                    $process["positionid"] = implode(",", $users["p"]);
                } else {
                    $process["positionid"] = "";
                }

                if (isset($users["u"])) {
                    $process["uid"] = implode(",", $users["u"]);
                } else {
                    $process["uid"] = "";
                }
            }
        } else {
            $process["uid"] = $process["deptid"] = $process["positionid"] = "";
        }

        if ($process["type"] == 1) {
            $childFlow = intval($data["childflow"]);
            $typeData = array("name" => FlowType::model()->fetchNameByFlowId($childFlow), "processto" => $data["prcsback"], "autouserop" => !empty($data["backuserop"]) ? implode(",", StringUtil::getId($data["backuserop"])) : "", "autouser" => !empty($data["backuser"]) ? implode(",", StringUtil::getId($data["backuser"])) : "", "childflow" => $childFlow, "relationout" => $data["map"]);
        } else {
            $autoUser = (!empty($data["autouser"]) ? implode(",", StringUtil::getId($data["autouser"])) : "");
            $autoType = intval($data["autotype"]);

            if ($autoType == 7) {
                $autoUser = intval($data["itemid"]);
            } elseif ($autoType == 8) {
                $autoUser = intval($data["autoprcsuser"]);
            }

            $typeData = array("name" => StringUtil::filterCleanHtml($data["name"]), "plugin" => $data["plugin"], "pluginsave" => $data["pluginsave"], "feedback" => intval($data["feedback"]), "signlook" => intval($data["signlook"]), "autotype" => $autoType, "autouserop" => !empty($data["autouserop"]) ? implode(",", StringUtil::getId($data["autouserop"])) : "", "autouser" => $autoUser, "userfilter" => intval($data["userfilter"]), "timeout" => $data["timeout"], "syncdeal" => intval($data["syncdeal"]), "userlock" => isset($data["userlock"]) ? 1 : 0, "turnpriv" => isset($data["turnpriv"]) ? 1 : 0, "topdefault" => intval($data["topdefault"]), "gathernode" => intval($data["gathernode"]), "allowback" => $data["allowback"], "childflow" => 0, "autobaseuser" => intval($data["autobaseuser"]));
        }

        $process = array_merge($process, $typeData);
        $write = (isset($data["write"]) ? $data["write"] : array());
        $secret = (isset($data["secret"]) ? $data["secret"] : array());
        $check = (isset($data["check"]) ? $data["check"] : array());
        $micro = (isset($data["micro"]) ? $data["micro"] : array());
        $checkSelect = (isset($data["check_select"]) ? $data["check_select"] : array());

        if (!empty($write)) {
            $prcsItem = $checkItem = array();

            foreach ($write as $title) {
                $prcsItem[] = $title;

                if (in_array($title, $check)) {
                    $key = array_search($title, $check);

                    if (isset($checkSelect[$key])) {
                        $checkItem[] = sprintf("%s=%s", $title, $checkSelect[$key]);
                    }
                }
            }

            $process["processitem"] = implode(",", $prcsItem);
            $process["checkitem"] = implode(",", $checkItem);
        } else {
            $process["processitem"] = $process["checkitem"] = "";
        }

        if (!empty($secret)) {
            $process["hiddenitem"] = implode(",", $secret);
        } else {
            $process["hiddenitem"] = "";
        }

        if (!empty($micro)) {
            $process["processitemauto"] = implode(",", $micro);
        } else {
            $process["processitemauto"] = "";
        }

        if (isset($data["conresult"])) {
            foreach ($data["conresult"] as $id => $cond) {
                FlowProcessTurn::model()->deleteAll(sprintf("`flowid` = '%d' AND `processid` = '%d' AND `to` = '%d'", $this->flow->getID(), $process["processid"], $id));
                $desc = $data["condesc"][$id];
                FlowProcessTurn::model()->add(array("processout" => $cond, "processid" => $process["processid"], "conditiondesc" => $desc, "flowid" => $this->flow->getID(), "to" => $id));
            }
        }

        return $process;
    }
}
