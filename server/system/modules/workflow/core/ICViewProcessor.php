<?php

class ICViewProcessor extends ICFlowBase
{
    public function __construct($objects)
    {
        $this->setAttributes($objects);
    }

    public function getID()
    {
        return null;
    }

    protected function getValue($item)
    {
        $ename = "data_" . $item["itemid"];

        if ($this->inApp) {
            $itemValue = $this->itemData[$ename];
        } else {
            $itemValue = (isset($item["data-value"]) ? $item["data-value"] : "");
        }

        $itemValue = str_ireplace(array("\"", "<", ">"), array("&quot;", "&lt;", "&gt;"), $itemValue);
        return $itemValue;
    }

    protected function setCommonReadOnly($item, &$content, $tag = "")
    {
        $tag = (!empty($tag) ? $tag : $item["tag"]);
        $content = str_ireplace("<" . $tag, "<" . $tag . " readonly class='disabled' ", $content);
    }

    protected function isHostItemReadOnly($item)
    {
        $inProcessItem = $this->inApp && $this->flow->isFixed() && StringUtil::findIn($this->process->processitem, $item["data-title"]);
        $infreeItem = $this->inApp && $this->flow->isFree() && (empty($this->rp->freeitem) || StringUtil::findIn($this->rp->freeitem, $item["data-title"]));
        $isHost = $this->inApp && $this->rp->opflag;
        $inDebug = $this->inDebug;
        if ((($inProcessItem || $infreeItem) && $isHost) || $inDebug) {
            return false;
        } else {
            return true;
        }
    }

    protected function execSysSql($sql, $opt = array(), $selectMode = true)
    {
        if (strtolower(substr($sql, 0, 6)) != "select") {
            return null;
        }

        $search = array("`", "&#13;&#10;", "[sys_user_id]", "[sys_dept_id]", "[sys_pos_id]", "[sys_run_id]");
        $replace = array("'", " ", $opt["uid"], strtok($opt["deptid"], ","), strtok($opt["positionid"], ","), $opt["runid"]);
        $sql = str_replace($search, $replace, $sql);

        try {
            $result = Ibos::app()->db->createCommand()->setText($sql)->queryAll();

            if (!$selectMode) {
                return current($result);
            }

            return $result;
        } catch (Exception $exc) {
            return "";
        }
    }

    protected function arrayTolist(&$arr, $selected = "")
    {
        $options = "";

        foreach ($arr as $k => $v) {
            if ($k !== "") {
                $options .= "<option value=\"" . $k . "\"";

                if ($v == $selected) {
                    $options .= " selected";
                }

                $options .= ">" . $v . "</option>\n";
            }
        }

        return $options;
    }

    protected function getProcessUser($process, &$ids)
    {
        if (!empty($process["uid"])) {
            foreach (explode(",", $process["uid"]) as $uid) {
                !empty($uid) && ($ids[] = "u_" . $uid);
            }
        }

        if (!empty($process["deptid"])) {
            foreach (explode(",", $process["deptid"]) as $deptID) {
                !empty($deptID) && ($ids[] = "d_" . $deptID);
            }
        }

        if (!empty($process["positionid"])) {
            foreach (explode(",", $process["positionid"]) as $posID) {
                !empty($posID) && ($ids[] = "p_" . $posID);
            }
        }
    }

    protected function getProcessUserList($flowID, $processID = 0, $value = "", $single = false)
    {
        $autoValue = "";
        $ids = array();

        if ($single) {
            $process = FlowProcess::model()->fetchProcess($flowID, $processID);
            $this->getProcessUser($process, $ids);
        } else {
            $allProcess = FlowProcess::model()->fetchAllByFlowId($flowID);

            foreach ($allProcess as $process) {
                $this->getProcessUser($process, $ids);
            }
        }

        if (!empty($ids)) {
            $uids = StringUtil::getUid($ids);

            foreach (User::model()->fetchAllByUids($uids) as $user) {
                $selected = ($value == $user["uid"] ? "selected" : "");
                $autoValue .= "<option $selected value='" . $user["uid"] . "'>" . $user["realname"] . "</option>";
            }
        }

        return $autoValue;
    }

    protected function getCalculate($value)
    {
        if ($value == "") {
            return null;
        }

        $search = array("abs(", "rmb(", "max(", "min(", "mod(", "day(", "hour(", "avg(", "date(", "list(");
        $replace = array("calc.abs(", "calc.rmb(", "calc.max(", "calc.min(", "calc.mod(", "calc.day(", "calc.hour(", "calc.avg(", "calc.date(", "calc.list(");
        $value = str_replace($search, $replace, strtolower($value));
        $flag = false;

        if (preg_match("/[\+|\-|\*|\/|,]+/i", $value) == 0) {
            $flag = true;
        }

        foreach ($this->form->parser->structure as $item) {
            if (isset($item["data-title"])) {
                if ($flag && ($item["data-title"] == $value)) {
                    $value = "calc.getVal('" . $item["itemid"] . "')";
                } else {
                    if (strstr($item["data-title"], "/")) {
                        $item["data-title"] = str_replace(array("/", "+", "-"), array("\/", "\+", "\-"), $item["data-title"]);
                    }

                    $pattern = "/([\+|\-|\*|\/|\(|,]+)" . $item["data-title"] . "([\+|\-|\*|\/|\)|,]+)/i";
                    $value = preg_replace($pattern, "\$1calc.getVal('" . $item["itemid"] . "')\$2", $value);
                    $pattern = "/([\+|\-|\*|\/|,]+)" . $item["data-title"] . "$/i";
                    $value = preg_replace($pattern, "\$1calc.getVal('" . $item["itemid"] . "')", $value);
                    $pattern = "/^" . $item["data-title"] . "([\+|\-|\*|\/|,]+)/i";
                    $value = preg_replace($pattern, "calc.getVal('" . $item["itemid"] . "')\$1", $value);
                }
            }
        }

        return $value;
    }

    protected function getManagerList($deptid, $value = "")
    {
        $deptCache = DepartmentUtil::loadDepartment();
        $uid = Ibos::app()->user->uid;
        $autoValue = "";
        $manager = array();
        $myarr = explode(",", $deptid);

        foreach ($myarr as $k => $v) {
            if (!empty($v)) {
                if (isset($deptCache[$v]) && ($deptCache[$v]["manager"] != 0)) {
                    $manager[] = $deptCache[$v]["manager"];
                }
            }
        }

        if (!empty($manager)) {
            foreach (User::model()->fetchAllByUids($manager) as $v) {
                $selected = ($v["uid"] == $value ? " selected" : "");
                $autoValue .= "<option value=\"" . $v["uid"] . "\"$selected>" . $v["realname"] . "</option>\n";
            }
        } else {
            $criteria = array("select" => "uid,realname", "condition" => "uid!=$uid AND FIND_IN_SET(deptid,'$deptid')" . WfCommonUtil::implodeSql($deptid), "order" => "groupid,uid,username");

            foreach (User::model()->fetchAll($criteria) as $user) {
                $selected = ($user["uid"] == $value ? " selected" : "");
                $autoValue .= "<option value=\"" . $user["uid"] . "\"$selected>" . $user["realname"] . "</option>\n";
            }
        }

        return $autoValue;
    }

    public function textProcessor($item, $readOnly)
    {
        $hidden = (isset($item["data-hide"]) ? $item["data-hide"] : "0");

        if ($hidden == "1") {
            $hiddenProp = "type=\"hidden\"";
        } else {
            $hiddenProp = "";
        }

        $eleout = str_ireplace("disabled=\"true\"", "", $item["content"]);

        if (isset($item["data-value"])) {
            $eleout = str_ireplace("value=\"" . $item["data-value"] . "\"", "", $item["content"]);
        }

        $eleout = str_ireplace("<" . $item["tag"], "<" . $item["tag"] . ' value="' . $this->getValue($item) .'" ' . $hiddenProp .'"', $eleout);

        if ($readOnly) {
            $this->setCommonReadOnly($item, $eleout);
        }

        return $eleout;
    }

    public function dateProcessor($item, $readOnly)
    {
        $value = $this->getValue($item);

        if ($this->isHostItemReadOnly($item)) {
            $read = "readonly";
        } else {
            $read = "";
        }

        $width = (isset($item["data-width"]) ? $item["data-width"] : 200);
        $eleout = "\t\t\t<span data-item=\"date\" data-date-format=\"{$item["data-date-format"]}\" class=\"datepicker\" style=\"display: inline-block; vertical-align: middle; width:$width px;\">\r\n\t\t\t\t<input name=\"data_{$item["itemid"]}\" type=\"text\" value=\"$value\" $read data-flag=\"date\" class=\"datepicker-input\" title=\"{$item["data-title"]}\" />\r\n\t\t\t\t<a href=\"javascript:;\" class=\"datepicker-btn\" ></a>\r\n\t\t\t</span>";
        return $eleout;
    }

    public function checkboxProcessor($item, $readOnly)
    {
        $eleout = $item["content"];
        $eleout = str_ireplace(" value=\"on\"", "", $eleout);
        $eleout = str_ireplace(" value=\"\"", "", $eleout);

        if (!$this->inDebug) {
            $eleout = str_ireplace(" checked", "", $eleout);
        }

        $eleout = str_ireplace(" checked=\"checked\"", "", $eleout);

        if ($this->getValue($item) == "on") {
            $eleout = str_ireplace("<" . $item["tag"], "<" . $item["tag"] . " checked", $eleout);
        }

        if ($readOnly) {
            if (strstr($eleout, " checked")) {
                $eleout = str_ireplace("<" . $item["tag"], "<" . $item["tag"] . " readonly onclick='this.checked=1;' class='disabled'", $eleout);
            } else {
                $eleout = str_ireplace("<" . $item["tag"], "<" . $item["tag"] . " readonly onclick='this.checked=0;' class='disabled'", $eleout);
            }
        }

        return $eleout;
    }

    public function textareaProcessor($item, $readOnly)
    {
        $value = $this->getValue($item);
        $eleout = str_replace("disabled=\"true\"", "", $item["content"]);

        if ($item["data-rich"] == "1") {
            if ($readOnly) {
                $eleout = $value;
            } else {
                $value = stripslashes($value);
                $height = (isset($item["data-rows"]) ? $item["data-rows"] * 20 : 200);
                $eleout = "\t\t\t\t<div style=\"width:{$item["data-width"]}px;height:$heightpx;overflow-x:hidden;overflow-y:scroll;\"> \r\n\t\t\t\t\t<script name=\"data_{$item["itemid"]}\" id=\"data_{$item["itemid"]}\" type=\"text/plain\" data-width=\"{$item["data-width"]}\" data-height=\"$height\" data-item=\"rich\">$value</script>\r\n\t\t\t\t</div>";
            }
        } else {
            $search = ">" . (isset($item["data-value"]) ? $item["data-value"] : "") . "<";
            $eleout = str_ireplace($search, ">\n" . $value . "<", $eleout);

            if ($readOnly) {
                $this->setCommonReadOnly($item, $eleout);
            }
        }

        return $eleout;
    }

    public function selectProcessor($item, $readOnly)
    {
        $value = $this->getValue($item);
        $eleout = str_ireplace(" selected=\"selected\"", "", $item["content"]);
        $eleout = str_ireplace("<option value=" . $value . ">", "<option selected value=\"" . $value . "\">", $eleout);
        $eleout = str_ireplace("<option value=\"" . $value . "\">", "<option selected value=\"" . $value . "\">", $eleout);

        if ($readOnly) {
            $this->setCommonReadOnly($item, $eleout);
        }

        return $eleout;
    }

    public function radioProcessor($item, $readOnly)
    {
        $radio_field = $item["data-radio-field"];
        $radio_check = (isset($item["data-radio-check"]) ? $item["data-radio-check"] : "");
        $radioarr = explode("`", rtrim($radio_field, "`"));
        $eleout = "";
        $value = $this->getValue($item);

        if ($value != "") {
            $radio_check = $value;
        }

        $disabled = ($readOnly ? "disabled" : "");

        foreach ($radioarr as $radio) {
            $checked = "";
            if (($radio == $radio_check) && !empty($radio_check)) {
                $checked = "checked";
            }

            $eleout .= "<label style=\"padding: 0 5px;\"><input type=\"radio\" name=\"data_" . $item["itemid"] . "\" value=\"" . $radio . "\" " . $checked . " " . $disabled . ">" . $radio . "</label>";
        }

        return $eleout;
    }

    public function progressbarProcessor($item, $readOnly)
    {
        $value = $this->getValue($item);
        if (($value == "") || ($value < 0)) {
            $value = 0;
        }

        if (100 < $value) {
            $value = 100;
        }

        $disabled = ($readOnly ? " disabled" : "");
        $eleout = "\t\t\t\t <span data-item=\"progressbar\" data-step=\"{$item["data-step"]}\" class=\"progress progress-striped active $disabled\" style=\"display: inline-block; vertical-align: middle; width:{$item["data-width"]}px\" title=\"{$item["data-title"]}\">\r\n\t\t\t\t\t<div class=\"progress-bar progress-bar-{$item["data-progress-style"]}\" style=\"width:$value%\"></div>\r\n\t\t\t\t\t<input type=\"hidden\" name=\"data_{$item["itemid"]}\" value=\"$value\" $disabled/>\r\n\t\t\t\t </span>";
        return $eleout;
    }

    public function labelProcessor($item, $readOnly)
    {
        return $item["content"];
    }

    public function userProcessor($item, $readOnly)
    {
        $value = $this->getValue($item);
        $selectType = $item["data-select-type"];

        if ($this->isHostItemReadOnly($item)) {
            $disabled = "data-disabled=\"1\"";
        } else {
            $disabled = "data-disabled=\"0\"";
        }

        $width = (isset($item["data-width"]) ? $item["data-width"] : 200);
        $eleout = "\t\t\t\t<span data-single=\"{$item["data-single"]}\" data-item=\"user\" style=\"display: inline-block; vertical-align: middle; width:$width px;\" data-select-type=\"$selectType\" $disabled>\r\n\t\t\t\t\t<input type=\"text\" name=\"data_{$item["itemid"]}\" value=\"$value\" />\r\n\t\t\t\t</span>";
        return $eleout;
    }

    public function autoProcessor($item, $readOnly)
    {
        $field = $item["data-field"];
        $width = (isset($item["data-width"]) ? $item["data-width"] : "200");
        $autoValue = "";
        $value = $this->getValue($item);
        $hourTime = date("H:i:s", TIMESTAMP);
        $date = date("Y-m-d");
        $time = $date . " " . $hourTime;
        $isTextAuto = substr($field, 0, 8) !== "sys_list";
        $lang = Ibos::getLangSource("workflow.default");

        if ($isTextAuto) {
            switch ($field) {
                case "sys_date":
                    $autoValue = $date;
                    break;

                case "sys_date_cn":
                    $autoValue = ConvertUtil::formatDate(TIMESTAMP, "Y" . $lang["Year"] . "m" . $lang["Month"] . "d" . $lang["Chinese day"]);
                    break;

                case "sys_date_cn_short1":
                    $autoValue = ConvertUtil::formatDate(TIMESTAMP, "Y" . $lang["Year"] . "m" . $lang["month"]);
                    break;

                case "sys_date_cn_short2":
                    $autoValue = ConvertUtil::formatDate(TIMESTAMP, "m" . $lang["Month"] . "d" . $lang["Chinese day"]);
                    break;

                case "sys_date_cn_short3":
                    $autoValue = ConvertUtil::formatDate(TIMESTAMP, "Y" . $lang["Year"]);
                    break;

                case "sys_date_cn_short4":
                    $autoValue = date("Y", TIMESTAMP);
                    break;

                case "sys_time":
                    $autoValue = $hourTime;
                    break;

                case "sys_datetime":
                    $autoValue = $time;
                    break;

                case "sys_week":
                    $autoValue = WfCommonUtil::getWeek();
                    break;

                case "sys_userid":
                    $autoValue = Ibos::app()->user->uid;
                    break;

                case "sys_realname":
                    $autoValue = Ibos::app()->user->realname;
                    break;

                case "sys_userpos":
                    $autoValue = Ibos::app()->user->posname;
                    break;

                case "sys_realname_date":
                    $autoValue = Ibos::app()->user->realname . " " . $date;
                    break;

                case "sys_realname_datetime":
                    $autoValue = Ibos::app()->user->realname . " " . $time;
                    break;

                case "sys_deptname":
                    $autoValue = Department::model()->fetchDeptNameByDeptId(Ibos::app()->user->alldeptid);
                    break;

                case "sys_deptname_short":
                    $autoValue = Ibos::app()->user->deptname;
                    break;

                case "sys_formname":
                    $autoValue = $this->form->formname;
                    break;

                case "sys_runname":
                    $autoValue = ($this->inDebug ? "" : $this->run->name);
                    break;

                case "sys_rundate":
                    $autoValue = ($this->inDebug ? "" : ConvertUtil::formatDate($this->run->begintime, "d"));
                    break;

                case "sys_rundatetime":
                    $autoValue = ($this->inDebug ? "" : ConvertUtil::formatDate($this->run->begintime));
                    break;

                case "sys_runid":
                    $autoValue = ($this->inDebug ? "" : $this->run->runid);
                    break;

                case "sys_autonum":
                    $autoValue = ($this->inApp ? $this->flow->autonum : "");
                    break;

                case "sys_ip":
                    $autoValue = EnvUtil::getClientIp();
                    break;

                case "sys_sql":
                    $sql = $item["data-src"];
                    $tempopt = array("uid" => Ibos::app()->user->uid, "deptid" => Ibos::app()->user->deptid, "positionid" => Ibos::app()->user->positionid, "runid" => $this->inDebug ? "" : $this->run->runid);
                    $autoValue = $this->execSysSql($sql, $tempopt, false);
                    break;

                case "sys_manager1":
                    $main = Ibos::app()->user->deptid;
                    $deptCache = DepartmentUtil::loadDepartment();
                    $managerID = $deptCache[$main]["manager"];

                    if ($managerID != 0) {
                        $autoValue = User::model()->fetchRealnameByUid($managerID);
                    }

                    break;

                case "sys_manager2":
                    $main = Ibos::app()->user->deptid;
                    $deptCache = DepartmentUtil::loadDepartment();
                    $upid = $deptCache[$main]["upid"];

                    if ($upid != 0) {
                        if ($deptCache[$upid]["manager"] != 0) {
                            $autoValue = User::model()->fetchRealnameByUid($deptCache[$upid]["manager"]);
                        }
                    }

                    break;

                case "sys_manager3":
                    $main = Ibos::app()->user->deptid;
                    $deptCache = DepartmentUtil::loadDepartment();
                    $dept_str = Department::model()->queryDept($main);
                    $temp = explode(",", $dept_str);
                    $count = count($temp);
                    $dept = $temp[$count - 2];

                    if ($deptCache[$dept]["manager"] != 0) {
                        $autoValue = User::model()->fetchRealnameByUid($deptCache[$dept]["manager"]);
                    }

                    break;

                default:
                    break;
            }

            if ((($value == "") && !$readOnly) || ($this->flow->isFixed() && $readOnly && StringUtil::findIn($this->process->processitemauto, $item["data-title"]) && $this->rp->opflag)) {
                $eleout = "\t\t\t\t<input type=\"text\" style=\"width:$width px;\" name=\"data_{$item["itemid"]}\" value=\"$autoValue\" title=\"{$item["data-title"]}\" />";
            } else {
                $eleout = "\t\t\t\t<input type=\"text\" style=\"width:$width px;\" name=\"data_{$item["itemid"]}\" value=\"$value\" title=\"{$item["data-title"]}\" />";
            }

            $hidden = (isset($item["data-hide"]) ? $item["data-hide"] : "0");

            if ($hidden == "1") {
                $eleout = str_ireplace("type=\"text\"", "type=\"hidden\"", $eleout);
            }

            if (!$readOnly) {
                if ($this->inApp && $this->flow->isFixed() && StringUtil::findIn($this->process->processitemauto, $item["data-title"])) {
                    $readOnly = true;
                } else {
                    $eleout = str_ireplace("<input", "<input data-orig-value=\"$autoValue\" data-focus=\"restore\"", $eleout);
                }
            }

            if ($readOnly) {
                $this->setCommonReadOnly($item, $eleout, "input");
            }
        } else {
            $autoValue = "<option value=\"\"";

            if ($value == "") {
                $autoValue .= " selected";
            }

            $autoValue .= "></option>\n";

            switch ($field) {
                case "sys_list_dept":
                    $cache = DepartmentUtil::loadDepartment();
                    $str = StringUtil::getTree($cache, "<option value='\$deptid' \$selected>\$spacer\$deptname</option>", $value);
                    $autoValue .= $str;
                    break;

                case "sys_list_user":
                    foreach (UserUtil::loadUser() as $user) {
                        $selected = ($value == $user["uid"] ? "selected" : "");
                        $autoValue .= "<option $selected value='" . $user["uid"] . "'>" . $user["realname"] . "</option>";
                    }

                    break;

                case "sys_list_pos":
                    foreach (PositionUtil::loadPosition() as $pos) {
                        $selected = ($value == $pos["positionid"] ? "selected" : "");
                        $autoValue .= "<option $selected value='" . $pos["positionid"] . "'>" . $pos["posname"] . "</option>";
                    }

                    break;

                case "sys_list_prcsuser1":
                    $autoValue .= $this->getProcessUserList($this->flow->flowid, 0, $value);
                    break;

                case "sys_list_prcsuser2":
                    $autoValue .= $this->getProcessUserList($this->flow->flowid, $this->process->processid, $value, true);
                    break;

                case "sys_list_sql":
                    $sql = $item["data-src"];
                    $tempopt = array("uid" => Ibos::app()->user->uid, "deptid" => Ibos::app()->user->deptid, "positionid" => Ibos::app()->user->positionid, "runid" => $this->inDebug ? "" : $this->run->runid);
                    $autoValue = $this->execSysSql($sql, $tempopt);
                    $autoValue .= $this->arrayTolist($autoValue, $value);
                    break;

                case "sys_list_manager1":
                    $main = Ibos::app()->user->deptid;
                    $autoValue .= $this->getManagerList($main, $value);
                    break;

                case "sys_list_manager2":
                    $main = Ibos::app()->user->deptid;
                    $deptCache = DepartmentUtil::loadDepartment();
                    $upid = $deptCache[$main]["upid"];

                    if ($upid != 0) {
                        $autoValue .= $this->getManagerList($main, $value);
                    }

                    break;

                case "sys_list_manager3":
                    $main = Ibos::app()->user->deptid;
                    $deptCache = DepartmentUtil::loadDepartment();
                    $dept_str = Department::model()->queryDept($main);
                    $temp = explode(",", $dept_str);
                    $count = count($temp);
                    $dept = $temp[$count - 2];
                    $autoValue .= $this->getManagerList($dept, $value);
                    break;
            }

            $eleout = "\t\t\t\t\t<select title=\"{$item["data-title"]}\" name=\"data_{$item["itemid"]}\">\r\n\t\t\t\t\t$autoValue\r\n\t\t\t\t\t</select>";

            if ($readOnly) {
                $this->setCommonReadOnly($item, $eleout, "select");
            }
        }

        return $eleout;
    }

    public function listviewProcessor($item, $readOnly)
    {
        if ($readOnly) {
            $readOnly = 1;
        } else {
            $readOnly = 0;
        }

        $titleStr = "";
        $titleArr = explode("`", trim($item["data-lv-title"], "`"));

        foreach ($titleArr as $title) {
            $titleStr .= "<th style='width: 1%'>" . $title . "</th>\n";
        }

        $value = $this->getValue($item);
        $number = Ibos::lang("Number", "workflow.default");
        $operation = Ibos::lang("Operation", "default");
        $eleout = "\t\t\t<div id=\"lv_{$item["itemid"]}\" data-readonly=\"$readOnly\" title=\"{$item["data-title"]}\" data-item=\"listview\">\r\n\t\t\t\t<table data-sum=\"{$item["data-lv-sum"]}\" data-coltype=\"{$item["data-lv-coltype"]}\" data-colvalue=\"{$item["data-lv-colvalue"]}\" class=\"table table-head-condensed table-condensed table-bordered\">\r\n\t\t\t\t\t<thead>\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<th style=\"width: 1%\">$number</th>\r\n\t\t\t\t\t\t\t$titleStr\r\n\t\t\t\t\t\t\t<th style=\"width: 1%\">$operation</th>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t</thead>\r\n\t\t\t\t\t<tbody></tbody>\r\n\t\t\t\t</table>\r\n\t\t\t\t<input type=\"hidden\" value=\"$value\" data-flag=\"listview_data\" />\r\n\t\t\t\t<input type=\"hidden\" name=\"data_{$item["itemid"]}\" data-flag=\"listview\" />\r\n\t\t\t</div>";
        return $eleout;
    }

    public function calcProcessor($item, $readOnly)
    {
        $calc = $this->getCalculate(isset($item["data-value"]) ? $item["data-value"] : "");
        $eleout = "\t\t\t<input type=\"text\" title=\"{$item["data-title"]}\" data-prec=\"{$item["data-prec"]}\" style=\"width:{$item["data-width"]}px;\" data-id=\"{$item["itemid"]}\" data-item=\"calc\" data-exp=\"$calc\" name=\"data_{$item["itemid"]}\" />";

        if ($readOnly) {
            $this->setCommonReadOnly($item, $eleout, "input");
        }

        return $eleout;
    }

    public function qrcodeProcessor($item, $readOnly)
    {
        $value = $this->getValue($item);
        $eleout = "\t\t\t<span id=\"qrcode_preview_{$item["itemid"]}\" title=\"{$item["data-title"]}\" style=\"display: inline-block; vertical-align: middle;\"></span>\r\n\t\t\t<input type=\"hidden\" data-size=\"{$item["data-qrcode-size"]}\" title=\"{$item["data-title"]}\" data-id=\"{$item["itemid"]}\" value=\"$value\" data-item=\"qrcode\" name=\"data_{$item["itemid"]}\" />";
        return $eleout;
    }

    public function imguploadProcessor($item, $readOnly)
    {
        $imgWidth = $item["data-width"];
        $imgHeight = $item["data-height"];
        $value = $this->getValue($item);

        if ($value !== "") {
            $attach = AttachmentN::model()->fetch(sprintf("rid:%d", $this->run->runid), $value);
            $imgSrc = FileUtil::fileName(FileUtil::getAttachUrl() . "/" . $attach["attachment"]);
            $imgID = $value;
        } else {
            $imgSrc = "";
            $imgID = "";
        }

        $bg = Ibos::app()->assetManager->getAssetsUrl("workflow") . "/image/pic_upload.png";

        if ($readOnly) {
            $read = 1;
        } else {
            $read = 0;
        }

        $eleout = "\t\t\t\t<span data-item=\"imgupload\" data-width=\"$imgWidth\" data-height=\"$imgHeight\" data-id=\"{$item["itemid"]}\" data-bg=\"$bg\" data-read=\"$read\" data-src=\"$imgSrc\" style=\"display: inline-block; vertical-align: middle;\">\r\n\t\t\t\t\t<input type=\"hidden\" name=\"imgid_{$item["itemid"]}\" value=\"$imgID\" title=\"{$item["data-title"]}\" />\r\n\t\t\t\t</span>";
        return $eleout;
    }
}
