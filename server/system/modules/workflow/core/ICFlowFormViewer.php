<?php

class ICFlowFormViewer extends ICFlowBase
{
    public function __construct($objects)
    {
        $this->setAttributes($objects);
    }

    public function getID()
    {
        return $this->form->ID;
    }

    public function render($debug = false, $isPrint = false, $isApp = false)
    {
        $hiddenArr = $readOnlyArr = array();
        $model = $this->form->printmodelshort;
        $this->handleTags($model);
        if ($this->flow->allowattachment && strstr($model, "#[macro_attach")) {
            $this->handleAttach($model);
        }

        if (strstr($model, "#[macro_sign")) {
            $this->handleSignInfo($model);
        }

        if (strstr($model, "#[macro_timeout")) {
            $this->handleTimeOut($model);
        }

        if ($isPrint) {
            $this->handlePrintForm($model, $debug);
        } elseif ($isApp) {
            $this->handleAppForm($model, $hiddenArr, $readOnlyArr, $debug);
        } else {
            $this->handleForm($model, $hiddenArr, $readOnlyArr, $debug);
        }

        return array("hidden" => implode(",", $hiddenArr), "readonly" => implode(",", $readOnlyArr), "model" => $model);
    }

    public function handlePrintForm(&$model, $debug)
    {
        $structure = $this->form->structure;
        $itemData = FlowDataN::model()->fetch($this->flow->flowid, $this->run->runid);
        $params = array("itemData" => isset($itemData) ? $itemData : array());
        $processor = new ICPrintViewProcessor(array_merge($params, $this->toArray()));

        foreach ($structure as $ename => $item) {
            $etitle = (isset($item["data-title"]) ? $item["data-title"] : "");
            $etype = $item["data-type"];
            if (!$debug && $this->flow->isFixed() && StringUtil::findIn($this->process->hiddenitem, $etitle)) {
                $model = str_ireplace("{" . $ename . "}", "", $model);
                continue;
            }

            $method = $etype . "Processor";

            if (method_exists($processor, $method)) {
                $value = $processor->{$method}($item, true);
                $model = str_ireplace("{" . $ename . "}", $value, $model);
            }
        }
    }

    public function handleAppForm(&$model, &$hiddenArr, &$readOnlyArr, $debug = false)
    {
        $structure = $this->form->structure;
        $inAppMode = isset($this->flow);

        if ($inAppMode) {
            WfCommonUtil::updateTable($this->flow->flowid, $structure);
            $itemData = FlowDataN::model()->fetch($this->flow->flowid, $this->run->runid);
        }

        if (empty($structure)) {
            return $model;
        } else {
            $params = array("inDebug" => $debug, "inApp" => $inAppMode, "itemData" => isset($itemData) ? $itemData : array());
            $processor = new ICViewProcessor(array_merge($params, $this->toArray()));

            foreach ($structure as $ename => $item) {
                if ($item["data-type"] !== "label") {
                    $etitle = $item["data-title"];
                    $etype = $item["data-type"];
                    $itemID = $item["itemid"];

                    if (!$debug) {
                        if ($this->flow->isFixed() && StringUtil::findIn($this->process->hiddenitem, $etitle)) {
                            $model = str_ireplace("{" . $ename . "}", "", $model);
                            $hiddenArr[] = $itemID;
                            continue;
                        }

                        $readOnly = $this->isReadOnly($etitle);
                    } else {
                        $readOnly = false;
                    }

                    if ($readOnly) {
                        if (($this->flow->isFixed() && StringUtil::findIn($this->process->processitemauto, $etitle)) || ($etype != "calc")) {
                            $readOnlyArr[] = $itemID;
                        }
                    }

                    $method = $etype . "Processor";

                    if (method_exists($processor, $method)) {
                        $eleout[$ename] = $processor->{$method}($item, $readOnly);
                    }
                }
            }

            $model = array("structure" => $structure, "itemData" => isset($itemData) ? $itemData : array(), "eleout" => $eleout);
        }
    }

    public function handleForm(&$model, &$hiddenArr, &$readOnlyArr, $debug = false)
    {
        $structure = $this->form->structure;
        $inAppMode = isset($this->flow);

        if ($inAppMode) {
            WfCommonUtil::updateTable($this->flow->flowid, $structure);
            $itemData = FlowDataN::model()->fetch($this->flow->flowid, $this->run->runid);
        }

        if (empty($structure)) {
            return $model;
        } else {
            $params = array("inDebug" => $debug, "inApp" => $inAppMode, "itemData" => isset($itemData) ? $itemData : array());
            $processor = new ICViewProcessor(array_merge($params, $this->toArray()));

            foreach ($structure as $ename => $item) {
                $etitle = (isset($item["data-title"]) ? $item["data-title"] : "");
                $etype = $item["data-type"];
                $itemID = $item["itemid"];

                if (!$debug) {
                    if ($this->flow->isFixed() && StringUtil::findIn($this->process->hiddenitem, $etitle)) {
                        $model = str_ireplace("{" . $ename . "}", "", $model);
                        $hiddenArr[] = $itemID;
                        continue;
                    }

                    $readOnly = $this->isReadOnly($etitle);
                } else {
                    $readOnly = false;
                }

                if ($readOnly) {
                    if (($this->flow->isFixed() && StringUtil::findIn($this->process->processitemauto, $etitle)) || ($etype != "calc")) {
                        $readOnlyArr[] = $itemID;
                    }
                }

                $method = $etype . "Processor";

                if (method_exists($processor, $method)) {
                    $eleout = $processor->{$method}($item, $readOnly);
                }

                $model = str_ireplace("{" . $ename . "}", $eleout, $model);
            }
        }
    }

    protected function handleTags(&$model)
    {
        $processDate = ConvertUtil::formatDate($this->run->begintime);
        $tagMap = array("#[macro_form]" => sprintf("<strong>%s</strong>", $this->form->formname), "#[macro_run_name]" => $this->run->name, "#[macro_counter]" => $this->flow->autonum, "#[macro_time]" => sprintf("%s:%s", Ibos::lang("Date", "workflow.default"), $processDate), "#[macro_run_id]" => $this->run->runid);
        $model = str_replace(array_keys($tagMap), array_values($tagMap), $model);
    }

    protected function handleAttach(&$model)
    {
        $label = "macro_attach";
        $exp = "/#\[" . $label . "(\d*)(\*?)\]/i";
        preg_match_all($exp, $model, $matches);
        $attachIds = explode(",", trim($this->run->attachmentid, ","));

        foreach ($matches[1] as $k => $v) {
            if ($v != "") {
                $attachinfo = $this->parseMarcroAttach($this->run->runid, $attachIds[$v - 1]);
                $model = str_replace($v[0][$k], $attachinfo, $model);
            }
        }

        if (strstr($model, "#[macro_attach]")) {
            foreach ($attachIds as $k => $v) {
                $attachall .= $this->parseMarcroAttach($this->run->runid, $v);
            }

            $model = str_replace("#[macro_attach]", $attachall, $model);
        }
    }

    protected function handleSignInfo(&$model, &$signObjectCount)
    {
        $prcs = $sm = $sm_prcs = $wparr = array();

        foreach (FlowRunProcess::model()->fetchAllIDByRunID($this->run->runid) as $rp) {
            $processName = FlowProcess::model()->fetchName($this->flow->flowid, $rp["flowprocess"]);
            $wparr[$rp["flowprocess"]] = $processName;

            if ($prcs[$rp["processid"]] == "") {
                $prcs[$rp["processid"]] = $processName;
            } elseif ($prcs[$rp["processid"]] != $processName) {
                $prcs[$rp["processid"]] .= "," . $processName;
            }
        }

        $label = "macro_sign";
        $exp = "/#\[" . $label . "(\d*)(\*?)\]\[([\s\s]*?)\]/i";
        preg_match_all($exp, $model, $matches);

        foreach ($matches[0] as $k => $sign_macro_name) {
            if ($matches[2][$k] == "*") {
                $sm_prcs[] = array($sign_macro_name, $matches[1][$k], $matches[3][$k]);
            } else {
                $sm[] = array($sign_macro_name, $matches[1][$k], $matches[3][$k]);
            }
        }

        if ($sm_prcs) {
            $model = $this->signReplace($sm_prcs, 1, $model, $prcs, $wparr, $signObjectCount);
        }

        $model = $this->signReplace($sm, 2, $model, $prcs, $wparr, $signObjectCount);
    }

    protected function handleTimeOut($model)
    {
        $lang = Ibos::getLangSources("workflow.default");
        $label = "macro_timeout";
        $exp = "/#\[" . $label . "(\d*)(\*?)\]/i";
        preg_match_all($exp, $model, $matches);

        foreach ($matches[1] as $k => $v) {
            if (!empty($v)) {
                $data = FlowRunProcess::model()->fetchTimeoutRecord($this->run->runid, $v);

                if ($data) {
                    $flowPrcs = $data["flowprocess"];
                    $processTime = $data["processtime"];
                    $createTime = $data["createtime"];
                    $deliverTime = $data["delivertime"];
                } else {
                    $flowPrcs = "";
                }

                if ($flowPrcs) {
                    $info = FlowProcess::model()->fetchTimeoutInfo($this->run->flowid, $flowPrcs);

                    if ($info) {
                        $processName = $lang["The"] . $v . $lang["Steps"] . ":" . $info["name"];
                        if (!isset($timeout) && ($timeout == "")) {
                            $timeout = $info["timeout"];
                        }

                        $timeOutType = $info["timeouttype"];
                    }

                    if (isset($timeOutType) && ($timeOutType == 0)) {
                        $prcsBeginTime = $processTime;

                        if (!$processTime) {
                            $prcsBeginTime = $createTime;
                        }
                    } else {
                        $prcsBeginTime = $createTime;
                    }

                    $prcsEndTime = $deliverTime;
                    $prcsBeginTime = strtotime($prcsBeginTime);
                    $prcsEndTime = strtotime($prcsEndTime);

                    if (!$prcsBeginTime) {
                        $prcsBeginTime = TIMESTAMP;
                    }

                    if (!$prcsEndTime) {
                        $prcsEndTime = TIMESTAMP;
                    }

                    $timeUsedDesc = WfCommonUtil::getTime($prcsEndTime - $prcsBeginTime);
                    $str = $processName . $lang["Timeout"] . ":" . $timeUsedDesc;
                    $model = str_replace($matches[0][$k], $str, $model);
                } else {
                    $model = str_replace($matches[0][$k], "", $model);
                }
            }
        }
    }

    protected function isReadOnly($etitle)
    {
        $notInFreeItem = $this->flow->isFree() && ($this->rp->freeitem !== "") && !StringUtil::findIn($this->rp->freeitem, $etitle);
        $notInProcessItem = $this->flow->isFixed() && !StringUtil::findIn($this->process->processitem, $etitle);
        $notTheHost = !$this->rp->opflag;
        if ($notTheHost || $notInFreeItem || $notInProcessItem) {
            return true;
        } else {
            return false;
        }
    }

    protected function signReplace($sm, $type, $model, &$prcs, &$wparr, &$signObjectCount)
    {
        $lang = Ibos::getLangSource("workflow.default");
        $count = 0;
        $versionFlag = 0;

        foreach ($sm as $v) {
            $signContenTpl = ($v[2] == "" ? "{PRCS}{U}({MD}):{C} {Y}" . $lang["Year"] . "{M}" . $lang["Month"] . "{D}" . $lang["Chinese day"] . " {H}:{I}:{S}" : $v[2]);
            $count1 = 0;
            $ouputContent = "";

            foreach ($this->getAllSignByType($type, $v[1]) as $i => $k) {
                $count++;
                $count1++;
                $edittime = $k["edittime"];
                $signData = $k["signdata"];
                $content = htmlspecialchars($k["content"]);
                $user = User::model()->fetchByUid($k["uid"]);
                if (($v[1] == "") && !empty($this->process) && ($this->process->processid != 0)) {
                    $prcsName .= (0 < $k["flowprocess"] ? $wparr[$k["flowprocess"]] : $prcs[$k["processid"]]);
                }

                $signContent = $signContenTpl;

                if (false !== strpos($signContent, "{Y}")) {
                    $signContent = str_replace("{Y}", date("Y", $edittime), $signContent);
                }

                if (false !== strpos($signContent, "{M}")) {
                    $signContent = str_replace("{M}", date("m", $edittime), $signContent);
                }

                if (false !== strpos($signContent, "{D}")) {
                    $signContent = str_replace("{D}", date("d", $edittime), $signContent);
                }

                if (false !== strpos($signContent, "{H}")) {
                    $signContent = str_replace("{H}", date("H", $edittime), $signContent);
                }

                if (false !== strpos($signContent, "{I}")) {
                    $signContent = str_replace("{I}", date("i", $edittime), $signContent);
                }

                if (false !== strpos($signContent, "{S}")) {
                    $signContent = str_replace("{S}", date("s", strtotime($edittime)), $signContent);
                }

                if (false !== strpos($signContent, "{PRCS}")) {
                    $signContent = str_replace("{PRCS}", $prcsName, $signContent);
                }

                if (false !== strpos($signContent, "{C}")) {
                    $signContent = str_replace("{C}", $content, $signContent);
                }

                if (false !== strpos($signContent, "{U}")) {
                    $signContent = str_replace("{U}", $user["realname"], $signContent);
                }

                if (false !== strpos($signContent, "{MD}")) {
                    $signContent = str_replace("{MD}", $user["deptname"], $signContent);
                }

                if (false !== strpos($signContent, "{AD}")) {
                    $signContent = str_replace("{AD}", Department::model()->fetchDeptNameByDeptId($user["alldeptid"]), $signContent);
                }

                if (false !== strpos($signContent, "{P}")) {
                    $signContent = str_replace("{P}", $user["posname"], $signContent);
                }

                if (false !== strpos($signContent, "{SH}")) {
                    if ($signData !== "") {
                        $signInfo = "\t\t\t\t\t\t<div id=\"personal_sign$count\">\r\n\t\t\t\t\t\t\t<script type=\"text/javascript\">\r\n\t\t\t\t\t\t\t\t$(document).ready(function(){\r\n\t\t\t\t\t\t\t\t\twf.fnLoadSignData('$signData','$count','$content','$versionFlag');\r\n\t\t\t\t\t\t\t\t})\r\n\t\t\t\t\t\t\t</script>\r\n\t\t\t\t\t\t</div>";
                    } else {
                        $signInfo = "";
                    }

                    $signObjectCount++;
                    $signContent = str_replace("{SH}", $signInfo, $signContent);
                }

                if (false !== strpos($signContent, "{F}")) {
                    $attachall = "";

                    if (!empty($k["attachmentid"])) {
                        foreach (explode(",", trim($k["attachmentid"], ",")) as $j => $aid) {
                            $attachall .= $this->parseMarcroAttach($this->run->runid, $aid);
                        }

                        $signContent = str_replace("{F}", $attachall, $signContent);
                    }
                }

                $ouputContent .= $signContent . "<br>";
            }

            if (0 < $count1) {
                $model = str_replace($v[0], $ouputContent, $model);
            } else {
                $model = str_replace($v[0], "", $model);
            }
        }
    }

    protected function getAllSignByType($type, $con = "")
    {
        if ($type == 1) {
            $extra = ($con ? " AND flowprocess = '$con'" : "");
        } else {
            $extra = ($con ? " AND processid = '$con'" : "");
        }

        $criteria = array("select" => "*", "condition" => sprintf("runid = %d %s", $this->run->runid, $extra), "order" => "processid,edittime");
        return FlowRunfeedback::model()->fetchAll($criteria);
    }

    protected function parseMarcroAttach($runID, $attachmentID, $showImg = true)
    {
        $attachDir = Ibos::app()->setting->get("setting/attachdir");
        $attach = AttachmentN::model()->fetch("rid:" . $runID, $attachmentID);
        if ($attach["isimage"] && $showImg) {
            $imgAttr = FileUtil::imageSize($attachDir . $attach["attachment"]);
            $attachLink = "<img src=\"$attachDir" . $attach["attachment"] . "\" $imgAttr[3] alt=\"{$attach["filename"]}\">";
            return $attachLink;
        }

        if ($this->flow->isFixed() && (StringUtil::findIn($this->process->attachpriv, "4") || !$this->process->attachpriv)) {
            $down = 1;
        } else {
            $down = 0;
        }

        if ($down) {
            $attachstr = AttachUtil::getAttachStr($attachmentID);
            $url = Ibos::app()->urlManager->createUrl("main/attach/download", array("id" => $attachstr));
            $link = "<a target=\"_blank\" class=\"xi2\" href=\"$url\">{$attach["filename"]}</a>";
        } else {
            $link = "<a class=\"xi2\" href=\"#\">{$attach["filename"]}</a>";
        }

        $type = StringUtil::getFileExt($attach["attach"]);
        $path = AttachUtil::attachType($type);
        $attachLink = "<span><img src=\"$path\">$link</span>";
        return $attachLink;
    }
}
