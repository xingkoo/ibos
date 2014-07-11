<?php

class MobileWorkController extends MobileBaseController
{
    const TODO = "1,2";
    const FORCE = 1;
    const UN_RECEIVE = 1;
    const HANDLE = 2;
    const TRANS = "3,4";
    const DONE = 4;
    const PRESET = 5;
    const DELAY = 6;
    const DEFAULT_PAGE_SIZE = 10;

    /**
     * 列表页专用属性
     * @var array 
     */
    protected $_extraAttributes = array("uid" => 0, "op" => "", "sort" => "", "type" => "", "runid" => "", "flowid" => "", "processid" => "", "flowprocess" => "", "sortText" => "", "key" => "");
    /**
     * 检索类型 - 数据库标识 映射数组
     * @var array 
     */
    protected $typeMapping = array("todo" => self::TODO, "trans" => self::TRANS, "done" => self::DONE, "delay" => self::DELAY);

    public function checkRunAccess($runID, $processID = 0, $jump = "")
    {
        $per = WfCommonUtil::getRunPermission($runID, $this->uid, $processID);

        if (empty($per)) {
            $errMsg = Ibos::lang("Permission denied");

            if (!empty($jump)) {
                $this->error($errMsg, $jump);
            } else {
                exit($errMsg);
            }
        }
    }

    public function checkFlowAccess($flowId, $processId, $jump = "")
    {
        $per = WfNewUtil::checkProcessPermission($flowId, $processId, $this->uid);

        if (!$per) {
            $errMsg = Ibos::lang("Permission denied");

            if (!empty($jump)) {
                $this->error($errMsg, $jump);
            } else {
                exit($errMsg);
            }
        }
    }

    public function actionIndex()
    {
        $param = array("op" => $this->op, "type" => $this->type, "sort" => $this->sort);
        $data = array_merge($param, $this->getListData());
        $this->ajaxReturn($data, "JSONP");
    }

    public function actionFollow()
    {
        $fields = array("frp.runid", "frp.processid", "frp.flowprocess", "frp.flag", "frp.opflag", "frp.processtime", "ft.freeother", "ft.flowid", "ft.name as typeName", "ft.type", "ft.listfieldstr", "fr.name as runName", "fr.beginuser", "fr.begintime", "fr.endtime", "fr.focususer");
        $flag = $this->typeMapping[$this->type];
        $sort = "frp.processtime";
        $group = "frp.runid";
        $condition = array("and", "fr.delflag = 0", "frp.childrun = 0", sprintf("frp.uid = %d", $this->uid), sprintf("FIND_IN_SET(fr.focususer,'%s')", $this->uid));
        $count = Ibos::app()->db->createCommand()->select("count(*) as count")->from("{{flow_run_process}} frp")->leftJoin("{{flow_run}} fr", "frp.runid = fr.runid")->leftJoin("{{flow_type}} ft", "fr.flowid = ft.flowid")->where($condition)->group($group)->queryScalar();
        $pages = PageUtil::create($count, self::DEFAULT_PAGE_SIZE);
        $offset = $pages->getOffset();
        $limit = $pages->getLimit();
        $list = Ibos::app()->db->createCommand()->select($fields)->from("{{flow_run_process}} frp")->leftJoin("{{flow_run}} fr", "frp.runid = fr.runid")->leftJoin("{{flow_type}} ft", "fr.flowid = ft.flowid")->where($condition)->order($sort)->group($group)->offset($offset)->limit($limit)->queryAll();
        $data = array_merge(array(
            "pages" => array("pageCount" => $pages->getPageCount(), "page" => $pages->getCurrentPage(), "pageSize" => $pages->getPageSize())
        ), $this->handleList($list, $flag));
        $this->ajaxReturn($data, "JSONP");
    }

    public function actionNew()
    {
        $data = array();
        $this->handleStartFlowList($data);
        $this->ajaxReturn($data, "JSONP");
    }

    public function actionForm()
    {
        $key = EnvUtil::getRequest("key");

        if ($key) {
            $this->key = $key;
            $param = WfCommonUtil::param($key, "DECODE");
            $this->_extraAttributes = $param;
            $this->runid = $param["runid"];
            $this->flowid = $param["flowid"];
            $this->processid = $param["processid"];
            $this->flowprocess = $param["flowprocess"];
        } else {
            $this->ajaxReturn("<script>alert('工作流数据错误，可能已转交或被回退')</script>", "EVAL");
        }

        $flow = new ICFlowType(intval($this->flowid));

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $data = array();
            $readOnly = $_POST["readonly"];
            $hidden = $_POST["hidden"];
            $saveflag = $_POST["saveflag"];
            $fbAttachmentID = $_POST["fbattachmentid"];
            $attachmentID = $_POST["attachmentid"];
            $content = (isset($_POST["content"]) ? StringUtil::filterCleanHtml($_POST["content"]) : "");
            $topflag = $_POST["topflag"];
            $this->checkRunAccess($this->runid, $this->processid, $this->createUrl("list/index"));

            if (FlowRunProcess::model()->getIsOp($this->uid, $this->runid, $this->processid)) {
                $enablefiledArr = explode(",", $_POST["enablefiled"]);
                $formData = array();
                $structure = $flow->form->parser->structure;

                foreach ($structure as $index => $item) {
                    if (!in_array("data_" . $item["itemid"], $enablefiledArr)) {
                        continue;
                    }

                    $value = (isset($_POST[$index]) ? $_POST[$index] : "");
                    $formData[$index] = $value;
                }

                $formData && $this->handleImgComponent($formData);
                $formData && FlowDataN::model()->update($this->flowid, $this->runid, $formData);
            }

            if (!empty($content) || !empty($fbAttachmentID)) {
                $fbData = array("runid" => $this->runid, "processid" => $this->processid, "flowprocess" => $this->flowprocess, "uid" => $this->uid, "content" => $content, "attachmentid" => $fbAttachmentID, "edittime" => TIMESTAMP);
                FlowRunfeedback::model()->add($fbData);
                AttachUtil::updateAttach($fbAttachmentID, $this->runid);
            }

            FlowRun::model()->modify($this->runid, array("attachmentid" => $attachmentID));
            AttachUtil::updateAttach($attachmentID, $this->runid);
            $plugin = FlowProcess::model()->fetchSavePlugin($this->flowid, $this->flowprocess);

            if (!empty($plugin)) {
                $pluginFile = "./system/modules/workflow/plugins/save/" . $plugin;

                if (file_exists($pluginFile)) {
                    include_once ($pluginFile);
                }
            }

            switch ($saveflag) {
                case "save":
                    MainUtil::setCookie("save_flag", 1, 300);
                    $this->ajaxReturn("<script>alert('保存成功')</script>", "EVAL");
                    break;

                case "turn":
                    MainUtil::setCookie("turn_flag", 1, 300);
                    break;

                case "end":
                case "finish":
                    if ($saveflag == "end") {
                        $param = array("opflag" => 1);
                    } else {
                        $param = array("topflag" => $topflag);
                    }

                    $this->redirect($this->createUrl("handle/complete", array_merge($param, array("key" => $this->key))));
                    break;

                default:
                    break;
            }
        } else {
            $this->checkRunDel();
            $this->checkIllegal();
            $len = strlen($flow->autonum);

            for ($i = 0; $i < ($flow->autolen - $len); $i++) {
                $flow->autonum = "0" . $flow->autonum;
            }

            $runProcess = new ICFlowRunProcess($this->runid, $this->processid, $this->flowprocess, $this->uid);
            $checkitem = "";

            if ($flow->isFixed()) {
                $process = new ICFlowProcess($this->flowid, $this->flowprocess);

                if ($runProcess->opflag != 0) {
                    $checkitem = $process->checkitem;
                }

                if (0 < $process->allowback) {
                    $isAllowBack = true;
                }
            } else {
                $process = array();
            }

            $run = new ICFlowRun($this->runid);
            $hasOtherOPUser = FlowRunProcess::model()->getHasOtherOPUser($this->runid, $this->processid, $this->flowprocess, $this->uid);

            if ($runProcess->flag == self::UN_RECEIVE) {
                $this->setSelfToHandle($runProcess->id);
            }

            if (($runProcess->topflag == 1) && ($runProcess->opflag == 1)) {
                FlowRunProcess::model()->updateTop($this->uid, $this->runid, $this->processid, $this->flowprocess);
            }

            if ($runProcess->topflag == 2) {
                if (!$hasOtherOPUser) {
                    $runProcess->opflag = 1;
                }
            }

            if ($this->processid == 1) {
                FlowRun::model()->modify($this->runid, array("beginuser" => $this->uid, "begintime" => TIMESTAMP));

                if (!empty($run->parentrun)) {
                    $this->setParentToHandle($run->parentrun, $this->runid);
                }
            }

            $preProcess = $this->processid - 1;

            if ($preProcess) {
                if ($flow->isFree() || ($flow->isFixed() && ($process->gathernode != self::FORCE))) {
                    $this->setProcessDone($preProcess);
                }
            }

            if ($flow->isFixed() && ($process->timeout != 0)) {
                if (($runProcess->flag == self::UN_RECEIVE) && ($this->processid !== 1)) {
                    $processBegin = FlowRunProcess::model()->fetchDeliverTime($this->runid, $preProcess);
                } else {
                    $processBegin = ($runProcess->processtime ? $runProcess->processtime : TIMESTAMP);
                }

                $timeUsed = TIMESTAMP - $processBegin;
            }

            $viewer = new ICFlowFormViewer(array("flow" => $flow, "form" => $flow->getForm(), "run" => $run, "process" => $process, "rp" => $runProcess));
            $data = array_merge(array("flow" => $flow->toArray(), "run" => $run->toArray(), "process" => !empty($process) ? $process->toArray() : $process, "checkItem" => $checkitem, "prcscache" => WfCommonUtil::loadProcessCache($this->flowid), "rp" => $runProcess->toArray(), "rpcache" => WfPreviewUtil::getViewFlowData($this->runid, $this->flowid, $this->uid, $remindUid), "fbSigned" => $this->isFeedBackSigned(), "allowBack" => isset($isAllowBack) ? $isAllowBack : false, "timeUsed" => isset($timeUsed) ? $timeUsed : 0, "uploadConfig" => AttachUtil::getUploadConfig()), $viewer->render(true, false, true));
            $formdata = array("run" => $data["run"], "flow" => $data["flow"], "enableArr" => "", "valueArr" => "", "emptyArr" => "");
            $data["enablefiled"] = array();

            if (is_array($data["model"]["itemData"])) {
                if (isset($data["prcscache"][$data["rp"]["flowprocess"]]["processitem"])) {
                    $enableFiled = explode(",", $data["prcscache"][$data["rp"]["flowprocess"]]["processitem"]);
                } else {
                    $enableFiled = array();
                }

                foreach ($data["model"]["itemData"] as $k => $v) {
                    if (substr($k, 0, 5) != "data_") {
                        continue;
                    }

                    $data["model"]["structure"][$k]["value"] = $v;

                    if (in_array($data["model"]["structure"][$k]["data-title"], $enableFiled)) {
                        $data["enablefiled"][] = $k;
                        $data["model"]["structure"][$k]["value"] = $data["model"]["eleout"][$k];
                        $formdata["enableArr"][] = $data["model"]["structure"][$k];
                        continue;
                    }

                    if ($v != "") {
                        $formdata["valueArr"][] = $data["model"]["structure"][$k];
                        continue;
                    }

                    $formdata["emptyArr"][] = $data["model"]["structure"][$k];
                }
            }

            $data["model"] = $this->renderPartial("application.modules.mobile.views.work.form", $formdata, true);
            $data["model"] .= "<input type=\"hidden\" name=\"key\" value=\"" . $this->key . "\">";
            $data["model"] .= "<input type=\"hidden\" name=\"hidden\" value=\"" . $data["hidden"] . "\">";
            $data["model"] .= "<input type=\"hidden\" name=\"readonly\" value=\"" . $data["readonly"] . "\">";
            $data["model"] .= "<input type=\"hidden\" name=\"attachmentid\" id=\"attachmentid\" value=\"" . $data["run"]["attachmentid"] . "\">";
            $data["model"] .= "<input type=\"hidden\" name=\"fbattachmentid\" id=\"fbattachmentid\" value=\"\">";
            $data["model"] .= "<input type=\"hidden\" name=\"topflag\" value=\"" . $data["rp"]["opflag"] . "\">";
            $data["model"] .= "<input type=\"hidden\" name=\"saveflag\">";
            $data["model"] .= "<input type=\"hidden\" name=\"formhash\" value=\"" . FORMHASH . "\">";
            $data["model"] .= "<input type=\"hidden\" name=\"enablefiled\" value=\"" . implode(",", $data["enablefiled"]) . "\">";

            if ($this->isEnabledAttachment($flow, $run, $process, $runProcess)) {
                $data["allowAttach"] = true;

                if (!empty($run->attachmentid)) {
                    $attachPurv = $this->getAttachPriv($flow, $process, $runProcess);
                    $down = $attachPurv["down"];
                    $edit = $attachPurv["edit"];
                    $del = $attachPurv["del"];
                    $data["attachData"] = AttachUtil::getAttach($run->attachmentid, $down, $down, $edit, $del);
                }
            } else {
                $data["allowAttach"] = false;
            }

            if ($flow->isFixed() && ($process->feedback != 1)) {
                $data["allowFeedback"] = true;
                $data["feedback"] = WfHandleUtil::loadFeedback($flow->getID(), $run->getID(), $flow->type, $this->uid);
            } else {
                $data["allowFeedback"] = false;
            }

            if ($flow->isFree() && ($runProcess->opflag == "1")) {
                $hasDefault = FlowRunProcess::model()->getHasDefaultStep($this->runid, $this->processid);

                if (!$hasDefault) {
                    $data["defaultEnd"] = true;
                }
            }

            if ($flow->isFree() && ($runProcess->topflag == "2")) {
                if (!$hasOtherOPUser) {
                    $data["otherEnd"] = true;
                }
            }

            $this->ajaxReturn($data, "JSONP");
        }
    }

    protected function getAttachPriv(ICFlowType $flow, $process, ICFlowRunProcess $rp)
    {
        $down = $edit = $del = $read = $print = false;

        if ($flow->isFree()) {
            $down = true;
        } elseif (StringUtil::findIn($process->attachpriv, 4)) {
            $down = true;
        }

        if ($flow->isFixed() && empty($process->attachpriv)) {
            $down = $edit = $del = true;
        }

        $isHost = $rp->opflag == "1";
        $inProcessItem = $flow->isFixed() && StringUtil::findIn($process->processitem, "[A@]");
        $enabledInFreeItem = $this->isEnabledInFreeItem($flow, $rp);
        if ($isHost && ($inProcessItem || $enabledInFreeItem)) {
            if (StringUtil::findIn($process->attachpriv, 2)) {
                $edit = true;
            }

            if (StringUtil::findIn($process->attachpriv, 3)) {
                $del = true;
            }

            if ($flow->isFixed()) {
                $edit = $del = true;
            }
        }

        if ($flow->isFixed() && StringUtil::findIn($process->processitem, 5)) {
            $print = true;
        }

        return array("down" => $down, "edit" => $edit, "del" => $del, "read" => $read, "print" => $print);
    }

    protected function setProcessDone($processID)
    {
        $condition = sprintf("processid = %d AND runid = %d", $processID, $this->runid);
        FlowRunProcess::model()->updateAll(array("flag" => self::DONE), $condition);
    }

    protected function setParentToHandle($id, $child)
    {
        $criteria = array(
            "condition" => array(
                array("and", sprintf("runid = %d", $id), sprintf("uid = %d", $this->uid), sprintf("childrun = %d", $child))
            )
        );
        FlowRunProcess::model()->updateAll(array("flag" => self::HANDLE, "processtime" => TIMESTAMP), $criteria);
    }

    protected function setSelfToHandle($id)
    {
        FlowRunProcess::model()->modify($id, array("flag" => self::HANDLE, "processtime" => TIMESTAMP));
    }

    protected function isAllowBack($parent = 0)
    {
        return FlowRunProcess::model()->getIsAllowBack($this->runid, $this->processid, $this->flowprocess, $parent);
    }

    protected function isFeedBackSigned()
    {
        return FlowRunfeedback::model()->getHasSignAccess($this->uid, $this->processid, $this->uid);
    }

    protected function isEnabledAttachment(ICFlowType $flow, ICFlowRun $run, $process, ICFlowRunProcess $rp)
    {
        $enabled = false;

        if ($flow->allowattachment) {
            $alreadyHaveAttach = $run->attachmentid !== "";
            $enabledInFreeItem = $this->isEnabledInFreeItem($flow, $rp);
            $isHost = $rp->opflag == "1";
            $inProcessItem = $flow->isFixed() && StringUtil::findIn($process->processitem, "[A@]");
            if ($alreadyHaveAttach || $enabledInFreeItem || ($inProcessItem && $isHost)) {
                $enabled = true;
            }
        }

        return $enabled;
    }

    protected function isEnabledInFreeItem(ICFlowType $flow, ICFlowRunProcess $rp)
    {
        return ($flow->isFree() && ($rp->freeitem == "")) || StringUtil::findIn($rp->freeitem, "[A@]");
    }

    protected function checkRunDel()
    {
        $isDel = FlowRun::model()->countByAttributes(array("delflag" => 1, "runid" => $this->runid));

        if ($isDel) {
            $this->error(Ibos::lang("Form run has been deleted"), $this->createUrl("list/index"));
        }
    }

    protected function checkIllegal()
    {
        $illegal = FlowRunProcess::model()->getIsIllegal($this->runid, $this->processid, $this->flowprocess, $this->uid);

        if ($illegal) {
            $this->error(Ibos::lang("Form run has been processed"), $this->createUrl("list/index"));
        }
    }

    protected function handleImgComponent(&$formData)
    {
        foreach ($GLOBALS["_FILES"] as $key => $value) {
            if (strtolower(substr($key, 0, 5)) == "data_") {
                $formData["$key"] = "";
                $old = $_POST["imgid_" . substr($key, 5)];

                if ($value["name"] != "") {
                    if (!empty($old)) {
                        AttachUtil::delAttach($old);
                    }

                    $upload = FileUtil::getUpload($_FILES[$key], "workflow");
                    $upload->save();
                    $attach = $upload->getAttach();
                    $attachment = "workflow/" . $attach["attachment"];
                    $imgData = array("dateline" => TIMESTAMP, "filename" => $attach["name"], "filesize" => $attach["size"], "isimage" => $attach["isimage"], "attachment" => $attachment, "uid" => $this->uid);
                    $aid = Attachment::model()->add(array("uid" => $this->uid, "rid" => $this->runid, "tableid" => AttachUtil::getTableId($this->runid)), true);
                    $imgData["aid"] = $aid;
                    $newAttach = AttachmentN::model()->add(sprintf("rid:%d", $this->runid), $imgData, true);
                    $formData["$key"] = $newAttach;
                } else {
                    $formData["$key"] = $old;
                }
            }
        }
    }

    public function actionAdd()
    {
        $flowId = intval(EnvUtil::getRequest("flowid"));
        $flowname = EnvUtil::getRequest("name");
        $flow = new ICFlowType($flowId);

        if (!empty($flow->autoname)) {
            $flowname = WfNewUtil::replaceAutoName($flow, $this->uid);
        } else {
            $flowname = sprintf("%s (%s)", $flow->name, date("Y-m-d H:i:s"));
        }

        $this->checkFlowAccess($flowId, 1, $this->createUrl("new/add"));
        $run = array("name" => $flowname, "flowid" => $flowId, "beginuser" => $this->uid, "begintime" => TIMESTAMP);
        $runId = FlowRun::model()->add($run, true);
        $runProcess = array("runid" => $runId, "processid" => 1, "uid" => $this->uid, "flag" => FlowConst::PRCS_UN_RECEIVE, "flowprocess" => 1, "createtime" => TIMESTAMP);
        FlowRunProcess::model()->add($runProcess);

        if (strstr($flow->autoname, "{N}")) {
            FlowType::model()->updateCounters(array("autonum" => 1), sprintf("flowid = %d", $flowId));
        }

        $runData = array("runid" => $runId, "name" => $flowname, "beginuser" => $this->uid, "begin" => TIMESTAMP);
        $this->handleRunData($flow, $runData);
        $param = array("flowid" => $flowId, "runid" => $runId, "processid" => 1, "flowprocess" => 1, "fromnew" => 1);

        if (Ibos::app()->request->getIsAjaxRequest()) {
            $this->ajaxReturn(array("isSuccess" => true, "key" => WfCommonUtil::param($param)), "JSONP");
        } else {
            $url = Ibos::app()->urlManager->createUrl("workflow/form/index", array("key" => WfCommonUtil::param($param)));
            header("Location: $url");
        }
    }

    protected function beforeAdd(&$data, ICFlowType $type)
    {
        $name = $data["name"];

        if (isset($data["prefix"])) {
            $name = $data["prefix"] . $name;
        }

        if (isset($data["suffix"])) {
            $name = $name . $data["suffix"];
        }

        $runName = StringUtil::filterCleanHtml($name);
        $runNameExists = FlowRun::model()->checkExistRunName($type->getID(), $runName);

        if ($runNameExists) {
            $this->error(Ibos::lang("Duplicate run name"));
        }

        $data["name"] = $runName;
    }

    protected function handleRunData(ICFlowType $type, &$runData)
    {
        $structure = $type->form->structure;

        foreach ($structure as $k => $v) {
            if (($v["data-type"] == "checkbox") && stristr($v["content"], "checkbox")) {
                if (stristr($v["content"], "checked") || stristr($v["content"], " checked=\"checked\"")) {
                    $itemData = "on";
                } else {
                    $itemData = "";
                }
            } elseif (!in_array($v["data-type"], array("select", "listview"))) {
                if (isset($v["data-value"])) {
                    $itemData = str_replace("\"", "", $v["data-value"]);

                    if ($itemData == "{macro}") {
                        $itemData = "";
                    }
                } else {
                    $itemData = "";
                }
            } else {
                $itemData = "";
            }

            $runData[strtolower($k)] = $itemData;
        }

        WfCommonUtil::addRunData($type->getID(), $runData, $structure);
    }

    protected function handleStartFlowList(&$data)
    {
        $flowList = $commonlyFlowList = $sort = array();
        $enabledFlowIds = WfNewUtil::getEnabledFlowIdByUid($this->uid);
        $commonlyFlowIds = FlowRun::model()->fetchCommonlyUsedFlowId($this->uid);

        foreach (FlowType::model()->fetchAll(array("order" => "sort,flowid")) as $flow) {
            $catId = $flow["catid"];
            $flowId = $flow["flowid"];

            if (!isset($flowList[$catId])) {
                $sort[$catId] = array();
                $cat = FlowCategory::model()->fetchByPk($catId);

                if ($cat) {
                    $sort[$catId] = $cat;
                }
            }

            if ($flow["usestatus"] == 3) {
                continue;
            }

            $enabled = in_array($flowId, $enabledFlowIds);
            if (!$enabled && ($flow["usestatus"] == 2)) {
                continue;
            }

            $flow["enabled"] = $enabled;

            if (in_array($flowId, $commonlyFlowIds)) {
                $commonlyFlowList[] = $flow;
            }

            $flowList[$catId][] = $flow;
        }

        ksort($flowList, SORT_NUMERIC);
        $data["common"] = $commonlyFlowList;

        foreach ($sort as $key => &$cate) {
            $cate["flows"] = $flowList[$key];
            $cate["flowcount"] = count($flowList[$key]);
            $data["cate"][] = $cate;
        }
    }

    public function init()
    {
        parent::init();
        $op = EnvUtil::getRequest("op");

        if (!in_array($op, array("category", "list"))) {
            $op = "list";
        }

        $sort = EnvUtil::getRequest("sort");
        $sortMap = array("all" => Ibos::lang("All of it"), "host" => Ibos::lang("Host"), "sign" => Ibos::lang("Sign"), "rollback" => Ibos::lang("Rollback"));

        if (!isset($sortMap[$sort])) {
            $sort = "all";
        }

        $type = EnvUtil::getRequest("type");

        if (!isset($this->typeMapping[$type])) {
            $type = "todo";
        }

        $flowID = EnvUtil::getRequest("flowid");

        if ($flowID) {
            $this->flowid = intval($flowID);
        }

        $this->op = $op;
        $this->sort = $sort;
        $this->type = $type;
        $this->sortText = $sortMap[$sort];
    }

    protected function getListData()
    {
        $fields = array("frp.runid", "frp.processid", "frp.flowprocess", "frp.flag", "frp.opflag", "frp.processtime", "ft.freeother", "ft.flowid", "ft.name as typeName", "ft.type", "ft.listfieldstr", "fr.name as runName", "fr.beginuser", "fr.begintime", "fr.endtime", "fr.focususer");
        $flag = $this->typeMapping[$this->type];
        $condition = array("and", "fr.delflag = 0", "frp.childrun = 0", sprintf("frp.uid = %d", $this->uid));

        if ($flag == self::DONE) {
            $condition[] = "fr.endtime != '0'";
        } else {
            $condition[] = array("in", "frp.flag", explode(",", $flag));
        }

        $sort = "frp.runid DESC";
        $group = "";

        if ($this->getIsOver()) {
            if ($this->type == "trans") {
                $sort = "frp.processtime DESC";
            } else {
                $sort = "fr.endtime DESC";
            }

            $group = "frp.runid";
        } elseif ($this->getIsTodo()) {
            $sort = "frp.createtime DESC";
        } elseif ($this->getIsDelay()) {
            $sort = "frp.flag DESC";
        }

        if ($this->sort == "host") {
            $condition[] = "frp.opflag = 1";
        } elseif ($this->sort == "sign") {
            $condition[] = "frp.opflag = 0";
        } elseif ($this->sort == "rollback") {
            $condition[] = "frp.processid != frp.flowprocess";
        }

        if ($this->flowid !== "") {
            $condition[] = "fr.flowid = " . $this->flowid;
        }

        $key = StringUtil::filterCleanHtml(EnvUtil::getRequest("keyword"));

        if ($key) {
            $condition[] = array("or", "fr.runid LIKE '%$key%'", "fr.name LIKE '%$key%'");
        }

        if ($this->op == "list") {
            $count = Ibos::app()->db->createCommand()->select("count(*) as count")->from("{{flow_run_process}} frp")->leftJoin("{{flow_run}} fr", "frp.runid = fr.runid")->leftJoin("{{flow_type}} ft", "fr.flowid = ft.flowid")->where($condition)->group($group)->queryScalar();
            $pages = PageUtil::create($count, self::DEFAULT_PAGE_SIZE);
            if ($key && $count) {
                $pages->params = array("keyword" => $key);
            }

            $offset = $pages->getOffset();
            $limit = $pages->getLimit();
        } else {
            $offset = $limit = -1;
        }

        $runProcess = Ibos::app()->db->createCommand()->select($fields)->from("{{flow_run_process}} frp")->leftJoin("{{flow_run}} fr", "frp.runid = fr.runid")->leftJoin("{{flow_type}} ft", "fr.flowid = ft.flowid")->where($condition)->order($sort)->group($group)->offset($offset)->limit($limit)->queryAll();

        if ($this->op == "list") {
            return array_merge(array(
                "pages" => array("pageCount" => $pages->getPageCount(), "page" => $pages->getCurrentPage(), "pageSize" => $pages->getPageSize())
            ), $this->handleList($runProcess, $flag));
        } elseif ($this->op == "category") {
            return $this->handleCategory($runProcess);
        }
    }

    protected function getIsTodo()
    {
        return $this->type == "todo";
    }

    protected function getIsOver()
    {
        return in_array($this->type, array("trans", "done"));
    }

    protected function getIsDelay()
    {
        return $this->type == "delay";
    }

    protected function handleList($runProcess, $flag)
    {
        $allProcess = FlowProcess::model()->fetchAllProcessSortByFlowId();

        foreach ($runProcess as &$run) {
            if ($this->getIsOver()) {
                $rp = FlowRunProcess::model()->fetchCurrentNextRun($run["runid"], $this->uid, $flag);

                if (!empty($rp)) {
                    $run["processid"] = $rp["processid"];
                    $run["flowprocess"] = $rp["flowprocess"];
                    $run["opflag"] = $rp["opflag"];
                    $run["flag"] = $rp["flag"];
                }
            }

            if ($run["type"] == 1) {
                if (isset($allProcess[$run["flowid"]][$run["flowprocess"]]["name"])) {
                    $run["stepname"] = $allProcess[$run["flowid"]][$run["flowprocess"]]["name"];
                } else {
                    $run["stepname"] = Ibos::lang("Process steps already deleted");
                }
            } else {
                $run["stepname"] = Ibos::lang("Step", "", array("{step}" => $run["processid"]));
            }

            if ($this->type !== "done") {
                $run["focus"] = StringUtil::findIn($this->uid, $run["focususer"]);
            } elseif (!empty($run["endtime"])) {
                $usedTime = $run["endtime"] - $run["begintime"];
                $run["usedtime"] = WfCommonUtil::getTime($usedTime);
            }

            $param = array("runid" => $run["runid"], "flowid" => $run["flowid"], "processid" => $run["processid"], "flowprocess" => $run["flowprocess"]);
            $run["key"] = WfCommonUtil::param($param);
        }

        return array("datas" => $runProcess);
    }

    public function actionFallback()
    {
        $key = EnvUtil::getRequest("key");
        $param = WfCommonUtil::param($key, "DECODE");
        $flowId = $param["flowid"];
        $processId = $param["processid"];
        $flowProcess = $param["flowprocess"];
        $runId = $param["runid"];
        $msg = StringUtil::filterCleanHtml(EnvUtil::getRequest("remind"));
        $per = WfCommonUtil::getRunPermission($runId, $this->uid, $processId);
        if (!StringUtil::findIn($per, 1) && !StringUtil::findIn($per, 2) && !StringUtil::findIn($per, 3)) {
            $this->ajaxReturn(array("isSuccess" => false), "JSONP");
        }

        $process = new ICFlowProcess($flowId, $flowProcess);
        if ((0 < $process->allowback) && ($processId != 1)) {
            $prcsIDNew = $processId + 1;

            if ($process->allowback == 1) {
                $prcsIdLast = $processId - 1;
                $temp = FlowRunProcess::model()->fetch(array("select" => "uid,flowprocess", "condition" => "runid = $runId AND processid = '$prcsIdLast' AND opflag = 1"));

                if ($temp) {
                    $flowProcessNew = $temp["flowprocess"];
                    $lastUID = $temp["uid"];
                }

                $log = "回退至上一步骤";
                $data = array("runid" => $runId, "processid" => $prcsIDNew, "uid" => $lastUID, "flag" => "1", "flowprocess" => $flowProcessNew, "opflag" => "1", "topflag" => "0", "parent" => $flowProcess);
                FlowRunProcess::model()->add($data);
                FlowRunProcess::model()->updateAll(array("delivertime" => TIMESTAMP, "flag" => "3"), "runid = $runId AND processid = $processId AND flowprocess = '$flowProcess' AND flag IN('1','2')");
                $key = WfCommonUtil::param(array("runid" => $runId, "flowid" => $flowId, "processid" => $prcsIDNew, "flowprocess" => $flowProcessNew));
                $url = Ibos::app()->urlManager->createUrl("workflow/form/index", array("key" => $key));
                $config = array("{url}" => $url, "{msg}" => $msg);
                Notify::model()->sendNotify($lastUID, "workflow_goback_notice", $config);
                WfCommonUtil::runlog($runId, $processId, $flowProcess, $this->uid, 1, $log);
                $this->ajaxReturn(array("isSuccess" => true), "JSONP");
            }
        } else {
            $this->ajaxReturn(array("isSuccess" => false), "JSONP");
        }
    }

    public function actionTurnNextPost()
    {
        $runId = $_GET["runid"];
        $flowId = $_GET["flowid"];
        $processId = $_GET["processid"];
        $flowProcess = $_GET["flowprocess"];
        $topflag = (isset($_GET["topflag"]) ? $_GET["topflag"] : null);
        $this->nextAccessCheck($topflag, $runId, $processId);
        $plugin = FlowProcess::model()->fetchTurnPlugin($flowId, $flowProcess);

        if ($plugin) {
            $pluginFile = "./system/modules/workflow/plugins/turn/" . $plugin;

            if (file_exists($pluginFile)) {
                include_once ($pluginFile);
            }
        }

        $prcsTo = $_GET["processto"];
        $prcsToArr = explode(",", trim($prcsTo, ","));
        $prcsChooseArr = $_GET["prcs_choose"];
        $prcsChoose = implode($prcsChooseArr, ",");
        $message = $_GET["message"];
        $toId = $nextId = $beginUserId = $toallId = "";
        $ext = array("{url}" => Ibos::app()->urlManager->createUrl("workflow/list/index", array("op" => "category")), "{message}" => $message);

        if (isset($_GET["remind"][1])) {
            $nextId = "";

            if (isset($_GET["prcs_user_op"])) {
                $nextId = intval($_GET["prcs_user_op"]);
            } else {
                foreach ($prcsChooseArr as $k => $v) {
                    if (isset($_GET["prcs_user_op" . $k])) {
                        $nextId .= $_GET["prcs_user_op" . $k] . ",";
                    }
                }

                $nextId = trim($nextId, ",");
            }
        }

        if (isset($_GET["remind"][2])) {
            $beginuser = FlowRunProcess::model()->fetchAllOPUid($runId, 1, true);

            if ($beginuser) {
                $beginUserId = StringUtil::wrapId($beginuser[0]["uid"]);
            }
        }

        if (isset($_GET["remind"]["3"])) {
            $toallId = "";

            if (isset($_GET["prcs_user"])) {
                $toallId = filter_input(INPUT_POST, "prcs_user", FILTER_SANITIZE_STRING);
            } else {
                foreach ($prcsChooseArr as $k => $v) {
                    if (isset($_GET["prcs_user" . $k])) {
                        $toallId .= filter_input(INPUT_POST, "prcs_user" . $k, FILTER_SANITIZE_STRING);
                    }
                }
            }
        }

        $idstr = $nextId . "," . $beginUserId . "," . $toallId;
        $toId = StringUtil::filterStr($idstr);

        if ($toId) {
            Notify::model()->sendNotify($toId, "workflow_turn_notice", $ext);
        }

        if ($prcsChoose == "") {
            $prcsUserOp = (isset($_GET["prcs_user_op"]) ? intval($_GET["prcs_user_op"]) : "");
            $prcsUser = (isset($_GET["prcs_user"]) ? $_GET["prcs_user"] : "");
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
                $prcsBack = $_GET["prcsback"] . "";

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
                $prcsUserOp = $_GET[$str];

                if (empty($prcsUserOp)) {
                    $this->ajaxReturn(array("isSuccess" => false, "msg" => "必须选择主办人"), "JSONP");
                    exit();
                }

                if ($freeother == 2) {
                    $prcsUserOp = WfHandleUtil::turnOther($prcsUserOp, $flowId, $runId, $processId, $flowProcess);
                }

                $str = "prcs_user" . $prcsChooseArr[$i];
                $prcsUser = explode(",", $_GET[$str]);
                array_push($prcsUser, $prcsUserOp);
                $prcsUser = implode(",", array_unique($prcsUser));

                if ($freeother == 2) {
                    $prcsUser = WfHandleUtil::turnOther($prcsUser, $flowId, $runId, $processId, $flowProcess, $prcsUserOp);
                }

                $str = "topflag" . $prcsChooseArr[$i];
                $topflag = intval($_GET[$str]);
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

        $this->ajaxReturn(array("isSuccess" => true), "JSONP");
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
                    if ((($rp["flag"] == self::TRANS) || ($rp["flag"] == self::DONE)) && ($rp["uid"] == $this->uid)) {
                        $turnForbidden = true;
                    } else {
                        $turnForbidden = false;
                    }

                    if (($rp["flag"] != self::DONE) && ($rp["uid"] != $this->uid)) {
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

            if ($process->gathernode == self::FORCE) {
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

                    if ($run->parentrun !== "0") {
                        $parentFlowId = FlowRun::model()->fetchFlowIdByRunId($run->parentrun);
                        $parentProcess = FlowRun::model()->fetchIDByChild($run->parentrun, $runId);
                        $parentFlowProcess = $parentProcess["flowprocess"];
                        if ($parentFlowId && $parentFlowProcess) {
                            $temp = FlowProcess::model()->fetchProcess($parentFlowId, $parentFlowProcess);

                            if ($temp) {
                                $prcsback = $temp["processto"];
                                $backUserOP = $temp["autouserop"];
                                $param["backuser"] = $temp["autouser"];
                            }
                        }

                        if ($prcsback != "") {
                            $param["prcsusers"] = WfHandleUtil::getPrcsUser($flowId, $prcsback);
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
                    $processOut = FlowProcessTurn::model()->fetchByUnique($flowId, $processId, $to);

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
            $this->ajaxReturn($data, "JSONP");
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
                    $baseuser = User::model()->fetchByUid($baseUid);
                    $autodept = $baseuser["deptid"];
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
                        if (is_numeric($value)) {
                            $value = User::model()->fetchRealnameByUid($value, "");
                            $tempArray[$key] = $value;
                        }
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
                $attr = 'islock="1"';
            } else {
                $attr = '';
            }

            if (!empty($prcsOpUser)) {
                $prcsOpUser = StringUtil::wrapId($prcsOpUser);
            }

            if (!empty($prcsUserAuto)) {
                $prcsUserAuto = StringUtil::wrapId(StringUtil::filterStr($prcsUserAuto));
            }

            $tablestr = "        <div class=\"control-group\" style=\"display:$display;\" id='user_select_$index'>\r\n            <div class=\"control-group first-group\">\r\n                <label class=\"control-label\">{$lang["Host"]}</label>\r\n                <div class=\"controls\">\r\n\t\t\t\t\t<input type=\"hidden\" name=\"topflag$index\" value=\"{$process["topdefault"]}\">\r\n\t\t\t\t\t<input id=\"prcs_user_op$index\" $attr name=\"prcs_user_op$index\"  value=\"$prcsOpUser\" type=\"text\" />\r\n\t\t\t\t</div>\r\n            </div>\r\n            <div class=\"control-group\">\r\n                <label class=\"control-label\">{$lang["Agent"]}</label>\r\n                <div class=\"controls\">\r\n\t\t\t\t\t<input id=\"prcs_user$index\" $attr name=\"prcs_user$index\" value=\"$prcsUserAuto\" type=\"text\" />\r\n\t\t\t\t</div>\r\n            </div>\r\n        </div>\r\n        <script>\r\n            $(function(){\r\n\t\t\t\tvar prcsData$index = $prcsuser;\r\n                var puo = $('#prcs_user_op$index');\r\n                var pu = $('#prcs_user$index');\r\n\t\t\t\tvar topdef = '{$process["topdefault"]}';\r\n                puo.userSelect({\r\n                    box:$('<div id=\"prcs_user_op_box$index\"></div>').appendTo(document.body),\r\n                    data:Ibos.data.includes(prcsData$index),\r\n\t\t\t\t\ttype:'user',\r\n                    maximumSelectionSize:'1'\r\n                });\r\n                if(puo.attr('islock')==1 || topdef != 0){\r\n                    puo.userSelect('setReadOnly');\r\n                }     \r\n                pu.userSelect({\r\n\t\t\t\t\tbox:$('<div id=\"prcs_user_box$index\"></div>').appendTo(document.body),\r\n                    data:Ibos.data.includes(prcsData$index),\r\n\t\t\t\t\ttype:'user'\r\n                });\r\n                if(pu.attr('islock')==1){\r\n                    pu.userSelect('setReadOnly');\r\n                }\r\n            });\r\n        </script>";
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
