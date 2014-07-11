<?php

class WorkflowFormController extends WorkflowBaseController
{
    /**
     * 传递过来的编码后的id变量
     * @var string 
     */
    protected $key = "";
    /**
     *
     * @var type 
     */
    protected $runid;
    /**
     *
     * @var type 
     */
    protected $flowid;
    /**
     *
     * @var type 
     */
    protected $processid;
    /**
     *
     * @var type 
     */
    protected $flowprocess;

    public function init()
    {
        $key = EnvUtil::getRequest("key");

        if ($key) {
            $this->key = $key;
            $param = WfCommonUtil::param($key, "DECODE");
            $this->runid = $param["runid"];
            $this->flowid = $param["flowid"];
            $this->processid = $param["processid"];
            $this->flowprocess = $param["flowprocess"];
        } else {
            $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("list/index"));
        }

        parent::init();
    }

    public function actionIndex()
    {
        $flow = new ICFlowType(intval($this->flowid));

        if (EnvUtil::submitCheck("formhash")) {
            $data = array();
            $readOnly = $_POST["readonly"];
            $hidden = $_POST["hidden"];
            $saveflag = $_POST["saveflag"];
            $fbAttachmentId = $_POST["fbattachmentid"];
            $attachmentId = $_POST["attachmentid"];
            $content = (isset($_POST["content"]) ? StringUtil::filterCleanHtml($_POST["content"]) : "");
            $topflag = $_POST["topflag"];
            $this->checkRunAccess($this->runid, $this->processid, $this->createUrl("list/index"));

            if (FlowRunProcess::model()->getIsOp($this->uid, $this->runid, $this->processid)) {
                $formData = array();
                $structure = $flow->form->parser->structure;

                foreach ($structure as $index => $item) {
                    if (StringUtil::findIn($hidden, $item["itemid"]) || StringUtil::findIn($readOnly, $item["itemid"])) {
                        continue;
                    }

                    $value = (isset($_POST[$index]) ? $_POST[$index] : "");
                    $formData[$index] = $value;
                }

                $formData && $this->handleImgComponent($formData);
                $formData && FlowDataN::model()->update($this->flowid, $this->runid, $formData);
            }

            if (!empty($content) || !empty($fbAttachmentId)) {
                $fbData = array("runid" => $this->runid, "processid" => $this->processid, "flowprocess" => $this->flowprocess, "uid" => $this->uid, "content" => $content, "attachmentid" => $fbAttachmentId, "edittime" => TIMESTAMP);
                FlowRunfeedback::model()->add($fbData);
                AttachUtil::updateAttach($fbAttachmentId, $this->runid);
            }

            FlowRun::model()->modify($this->runid, array("attachmentid" => $attachmentId));
            AttachUtil::updateAttach($attachmentId, $this->runid);
            $plugin = FlowProcess::model()->fetchSavePlugin($this->flowid, $this->flowprocess);

            if (!empty($plugin)) {
                $pluginFile = "./system/modules/workflow/plugins/save/" . $plugin;

                if (file_exists($pluginFile)) {
                    include_once ($pluginFile);
                }
            }

            switch ($saveflag) {
                case "save":
                    MainUtil::setCookie("save_flag", 1);
                    $this->redirect($this->createUrl("form/index", array("key" => $this->key)));
                    break;

                case "turn":
                    MainUtil::setCookie("turn_flag", 1);
                    $this->redirect($this->createUrl("form/index", array("key" => $this->key)));
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
                    $isAllowBack = $this->isAllowBack($runProcess->parent);
                }
            } else {
                $process = array();
            }

            $run = new ICFlowRun($this->runid);
            $hasOtherOPUser = FlowRunProcess::model()->getHasOtherOPUser($this->runid, $this->processid, $this->flowprocess, $this->uid);

            if ($runProcess->flag == FlowConst::PRCS_UN_RECEIVE) {
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
                if ($flow->isFree() || ($flow->isFixed() && ($process->gathernode != 1))) {
                    $this->setProcessDone($preProcess);
                }
            }

            if ($flow->isFixed() && ($process->timeout != 0)) {
                if (($runProcess->flag == FlowConst::PRCS_UN_RECEIVE) && ($this->processid != 1)) {
                    $processBegin = FlowRunProcess::model()->fetchDeliverTime($this->runid, $preProcess);
                } else {
                    $processBegin = ($runProcess->processtime ? $runProcess->processtime : TIMESTAMP);
                }

                $timeUsed = TIMESTAMP - $processBegin;
            }

            $viewer = new ICFlowFormViewer(array("flow" => $flow, "form" => $flow->getForm(), "run" => $run, "process" => $process, "rp" => $runProcess));
            $data = array_merge(array("flow" => $flow->toArray(), "run" => $run->toArray(), "processid" => $this->processid, "process" => !empty($process) ? $process->toArray() : $process, "checkItem" => $checkitem, "prcscache" => WfCommonUtil::loadProcessCache($this->flowid), "rp" => $runProcess->toArray(), "fbSigned" => $this->isFeedBackSigned(), "allowBack" => isset($isAllowBack) ? $isAllowBack : false, "timeUsed" => isset($timeUsed) ? $timeUsed : 0, "uploadConfig" => AttachUtil::getUploadConfig()), $viewer->render());

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

            if (($flow->isFixed() && ($process->feedback != 1)) || $flow->isFree()) {
                $data["allowFeedback"] = true;
            } else {
                $data["allowFeedback"] = false;
            }

            if ($data["allowBack"]) {
                $data["backlist"] = $this->getBackList($runProcess->flowprocess);
            }

            $data["feedback"] = WfHandleUtil::loadFeedback($flow->getID(), $run->getID(), $flow->type, $this->uid);
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

            $this->setPageTitle(Ibos::lang("Handle work"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Workflow")),
                array("name" => Ibos::lang("My work"), "url" => $this->createUrl("list/index")),
                array("name" => Ibos::lang("Handle work"))
            ));
            $this->render("index", $data);
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

        $isHost = $rp->opflag == "1";
        $inProcessItem = $flow->isFixed() && StringUtil::findIn($process->processitem, "[A@]");
        $enabledInFreeItem = $this->isEnabledInFreeItem($flow, $rp);
        if ($isHost && ($inProcessItem || $enabledInFreeItem)) {
            if ($flow->isFixed()) {
                if (StringUtil::findIn($process->attachpriv, 2)) {
                    $edit = true;
                }

                if (StringUtil::findIn($process->attachpriv, 3)) {
                    $del = true;
                }
            } elseif ($flow->isFree()) {
                $edit = $del = true;
            }
        }

        if ($flow->isFixed() && StringUtil::findIn($process->processitem, 5)) {
            $print = true;
        }

        return array("down" => $down, "edit" => $edit, "del" => $del, "read" => $read, "print" => $print);
    }

    protected function setProcessDone($processId)
    {
        $condition = sprintf("processid = %d AND runid = %d", $processId, $this->runid);
        FlowRunProcess::model()->updateAll(array("flag" => FlowConst::PRCS_DONE), $condition);
    }

    protected function setParentToHandle($id, $child)
    {
        $criteria = array("condition" => sprintf("runid = %d AND uid = %d AND childrun = %d", $id, $this->uid, $child));
        FlowRunProcess::model()->updateAll(array("flag" => FlowConst::PRCS_HANDLE, "processtime" => TIMESTAMP), $criteria);
    }

    protected function setSelfToHandle($id)
    {
        FlowRunProcess::model()->modify($id, array("flag" => FlowConst::PRCS_HANDLE, "processtime" => TIMESTAMP));
    }

    protected function getBackList($parent)
    {
        $flowProcessIds = $this->getParent($parent);
        $list = array();

        foreach ($flowProcessIds as $flowprocess) {
            $list[] = array("id" => $flowprocess, "name" => FlowProcess::model()->fetchName($this->flowid, $flowprocess));
        }

        return $list;
    }

    private function getParent($parent)
    {
        static $ids = array();
        $tmpParent = Ibos::app()->db->createCommand()->select("parent")->from("{{flow_run_process}} frp")->where(array("and", "frp.runid = $this->runid", "frp.isfallback != 1", "frp.flowprocess = '$parent'"))->order("frp.processid DESC")->limit(1)->queryScalar();

        if ($tmpParent) {
            array_push($ids, $tmpParent);
            return $this->getParent($tmpParent);
        } else {
            return (array) $ids;
        }
    }

    protected function isAllowBack($parent = 0)
    {
        $hasOtherOP = FlowRunProcess::model()->getIsAllowBack($this->runid, $this->processid, $this->flowprocess, $parent);
        $lastPrcsIsChildRun = FlowRunProcess::model()->fetch(array("select" => "id", "condition" => sprintf("runid = %d AND processid = %d AND opflag ='1' AND childrun!=0", $this->runid, $parent), "limit" => 1));
        if ($hasOtherOP || $lastPrcsIsChildRun) {
            return false;
        } else {
            return true;
        }
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
}
