<?php

class WfQueryUtil
{
    public static function getFlowList($uid)
    {
        $temp = array();
        $user = User::model()->fetchByUid($uid);

        foreach (FlowType::model()->fetchAllFlow() as $flow) {
            if ($flow["usestatus"] == 3) {
                continue;
            }

            $per = WfNewUtil::checkProcessPermission($flow["flowid"], 0, $uid);
            $isManager = FlowPermission::model()->fetchPermission($uid, $flow["flowid"], array(0, 1));
            if ($per || $isManager || $user["isadministrator"]) {
                $data = array("id" => $flow["flowid"], "text" => $flow["name"]);

                if (!isset($temp[$flow["catid"]])) {
                    $temp[$flow["catid"]]["text"] = $flow["catname"];
                    $temp[$flow["catid"]]["children"] = array();
                }

                $temp[$flow["catid"]]["children"][] = $data;
            }
        }

        $result = array_merge(array(), $temp);
        return $result;
    }

    public static function getMyFlowIDs($uid)
    {
        $flowIDs = $orgIDs = array();
        $user = User::model()->fetchByUid($uid);
        $allDeptStr = Department::model()->queryDept($user["alldeptid"], true);
        $deptArr = DepartmentUtil::loadDepartment();

        foreach ($deptArr as $id => $dept) {
            if ($dept["pid"] == 0) {
                $orgIDs[] = $id;
            }
        }

        $orgIDs = implode(",", $orgIDs);

        foreach (FlowPermission::model()->fetchAllByPer() as $val) {
            switch ($val["scope"]) {
                case "selfdeptall":
                case "selfdept":
                    $deptid = FlowType::model()->fetchDeptIDByFlowID($val["flowid"]);
                    if (($deptid !== 0) && ($user["isadministrator"] != 1)) {
                        if ($val["scope"] == "selfdept") {
                            $deptAccess = StringUtil::findIn($user["alldeptid"], $val["deptid"]);
                            $userAccess = WfNewUtil::compareIds($user["uid"], $val["uid"], "u");
                            $posAccess = WfNewUtil::compareIds($user["allposid"], $val["positionid"], "p");
                            if ($deptAccess || $userAccess || $posAccess) {
                                $flowIDs[] = $val["flowid"];
                            }
                        } elseif (self::hasAccess($user, $val)) {
                            $flowIDs[] = $val["flowid"];
                        }
                    } else {
                        $flowIDs[] = $val["flowid"];
                    }

                    break;

                case "selforg":
                    if (StringUtil::findIn($allDeptStr, $orgIDs)) {
                        if (self::hasAccess($user, $val)) {
                            $flowIDs[] = $val["flowid"];
                        }
                    }

                    break;

                case "alldept":
                    if (self::hasAccess($user, $val)) {
                        $flowIDs[] = $val["flowid"];
                    }

                    break;

                default:
                    if (StringUtil::findIn($allDeptStr, $val["scope"])) {
                        if (self::hasAccess($user, $val)) {
                            $flowIDs[] = $val["flowid"];
                        }
                    }

                    break;
            }
        }

        return $flowIDs;
    }

    public static function export($condition, $param, $type = "html")
    {
        $name = FlowType::model()->fetchNameByFlowId($param["flowid"]);
        $list = Ibos::app()->db->createCommand()->select("fr.name as runName,ft.name as typeName,fr.*,ft.*")->from("{{flow_run}} fr")->leftJoin("{{flow_type}} ft", "fr.flowid = ft.flowid")->where($condition)->queryAll();
        $edata = self::getExportData($list, $param);
        $title = $edata["title"];
        $data = $edata["data"];
        $sum = $edata["sum"];
        $sum_data = $edata["sum_data"];
        $group = $edata["group"];

        if (empty($data)) {
            EnvUtil::iExit("无符合条件的记录");
        }

        $groupTitle = $title[$group];

        if ($type == "html") {
            $html = "                <html>\r\n                    <head>\r\n                        <title>工作流分组统计报表 - $name</title>\r\n                        <style>\r\n                        .TableData {\r\n                            background: none repeat scroll 0 0 #FFFFFF;\r\n                            color: #000000;\r\n                        }\r\n                        .TableContent {\r\n                            background: none repeat scroll 0 0 #F2F8FF;\r\n                        }\r\n                        </style>\r\n                        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\r\n                    </head>\r\n                    <body topmargin=\"5\">\r\n\t\t\t\t\t<table>\r\n\t\t\t\t\t\t<tr>";
        } else {
            ob_end_clean();
            header("Cache-control: private");
            header("Content-type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename= " . urlencode($name) . ".xls");
            $html = "                <html xmlns:o=\"urn:schemas-microsoft-com:office:office\"\r\n                xmlns:x=\"urn:schemas-microsoft-com:office:excel\"\r\n                xmlns=\"http://www.w3.org/TR/REC-html40\">\r\n                <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\r\n\t\t\t\t<body>\r\n\t\t\t\t\t<table>\r\n\t\t\t\t\t<tr style=\"background: #D3E5FA; color: #000000; font-weight: bold;\">";
        }

        $html .= "             <td class=\"TableControl\" align=\"center\" nowrap><strong>分组：$groupTitle&nbsp;</strong></td>\n";

        foreach ($title as $key => $name) {
            if (($key == $group) || ($name == "")) {
                continue;
            }

            $html .= "<td align=\"center\" nowrap><strong>$name&nbsp;</strong></td>\n";
        }

        $html .= "</tr>\n";
        $rowCount = count($data);
        $colCount = count($data[0]);
        $data_index = $data_group = $sum_total_data = array();

        for ($i = 0; $i < $rowCount; $i++) {
            $data_index[0][$i] = $data[$i][$group];
            $data_index[1][$i] = $i;
        }

        if ($group == 0) {
            $sort_type = 1;
        } else {
            $sort_type = 2;
        }

        $sort_desc = 3;
        $sort_asc = 4;
        $sort_string = 1;

        if ($param["groupbyfields"]["order"] == "asc") {
            array_multisort($data_index[0], $sort_type, $sort_asc, $data_index[1], $sort_string, $sort_asc);
        } else {
            array_multisort($data_index[0], $sort_type, $sort_desc, $data_index[1], $sort_string, $sort_asc);
        }

        $cur_value_prev = "oa_null_value";
        $group_count = -1;

        for ($i = 0; $i < $rowCount; $i++) {
            if ($data_index[0][$i] != $cur_value_prev) {
                $group_count++;
            }

            $data_group[$group_count][0] = $data_index[0][$i];

            if (!isset($data_group[$group_count][1])) {
                $data_group[$group_count][1] = "";
            }

            $data_group[$group_count][1] .= $data_index[1][$i] . ",";
            $cur_value_prev = $data_index[0][$i];
        }

        for ($i = 0; $i <= $group_count; $i++) {
            $row_array = explode(",", $data_group[$i][1]);
            $array_count = count($row_array) - 1;
            $rowspan = $array_count;

            if (!empty($sum)) {
                $rowspan++;
            }

            for ($j = 0; $j < $array_count; $j++) {
                $html .= "<tr class=\"TableData\">\n";

                if ($j == 0) {
                    $html .= "<td rowspan=\"$rowspan\" class=\"TableContent\">\n";
                    $html .= $data_group[$i][0];

                    if ($param["groupbyfields"]["field"] != "runid") {
                        $html .= "<br><strong>共" . $array_count . "项";
                    }

                    $html .= "</td>\n";
                }

                for ($k = 0; $k < $colCount; $k++) {
                    if (($k == $group) || ($title[$k] == "")) {
                        continue;
                    }

                    if (in_array($k, $sum)) {
                        if (!isset($sum_data[$k])) {
                            $sum_data[$k] = "";
                        }

                        if (!isset($sum_total_data[$k])) {
                            $sum_total_data[$k] = "";
                        }

                        $sum_data[$k] += $data[$row_array[$j]][$k];
                        $sum_total_data[$k] += $data[$row_array[$j]][$k];
                    }

                    $html .= "<td";

                    if (in_array($k, $sum)) {
                        $html .= " align=\"right\"";
                    }

                    $html .= ">" . $data[$row_array[$j]][$k] . "</td>\n";
                }

                $html .= "</tr>\n";
            }

            if (!empty($sum)) {
                $html .= "<tr class=\"TableData\">\n";
                $flag = 0;

                for ($k = 0; $k < $colCount; $k++) {
                    if ($k == $group) {
                        continue;
                    }

                    if ($flag == 0) {
                        $flag = 1;
                        $html .= " <td class=\"TableControl\" align=right><b>小计</b></td>\n";
                        continue;
                    }

                    $html .= "<td class=\"TableControl\" align=right>" . (isset($sum_data[$k]) ? $sum_data[$k] : "") . "&nbsp;</td>\n";
                }

                $html .= "</tr>\n";
            }
        }

        if (!empty($sum)) {
            $html .= "                \n<tr class=\"TableControl\">\r\n                    <td align=\"center\"><b>合计</b></td>\n";

            for ($k = 0; $k < $colCount; $k++) {
                if ($k == $group) {
                    continue;
                }

                $html .= "<td align=\"right\">" . (isset($sum_total_data[$k]) ? $sum_total_data[$k] : "") . "&nbsp;</td>\n";
            }

            $html .= "</tr>\n";
        }

        $html .= "</table></body></html>";
        echo $html;
    }

    public static function getExportData($list, $param)
    {
        $lang = Ibos::getLangSources(array("workflow.default"));
        $data = $titles = $sum = array();
        $group = "";
        $flow = new ICFlowType($param["flowid"]);
        $structure = $flow->form->parser->structure;

        foreach ($list as $index => $row) {
            if (!empty($param["condition"])) {
                $formData = WfHandleUtil::getFormData($row["flowid"], $row["runid"]);
                $notPass = WfHandleUtil::checkCondition($formData, $param["condition"], "");

                if ($notPass !== "") {
                    continue;
                }
            }

            $queryHidden = Ibos::app()->db->createCommand()->select("hiddenitem")->from("{{flow_process}} fp")->leftJoin("{{flow_run_process}} frp", "fp.processid = frp.flowprocess")->where(sprintf("fp.flowid = %d AND frp.runid = %d AND frp.uid = %d", $param["flowid"], $row["runid"], $param["uid"]))->queryAll();
            $hidden = ConvertUtil::getSubByKey($queryHidden, "hiddenitem");
            $itemData = FlowDataN::model()->fetch($param["flowid"], $row["runid"]);
            $processor = new ICPrintViewProcessor(array("itemData" => $itemData));

            foreach (explode(",", $param["viewextfields"]) as $key => $field) {
                if (strpos($field, ".") !== false) {
                    list(, $itemID) = explode(".", $field);
                    $item = (isset($structure[$itemID]) ? $structure[$itemID] : array());

                    if (empty($item)) {
                        continue;
                    }

                    if (($item["data-type"] == "sign") || ($item["data-type"] == "label")) {
                        continue;
                    }

                    if (in_array($item["data-title"], $hidden)) {
                        $value = "";
                    } else {
                        $itemValue = $itemData[$itemID];

                        switch ($item["data-type"]) {
                            case "checkbox":
                                if ($itemValue == "on") {
                                    $value = $lang["Yes"];
                                } else {
                                    $value = $lang["No"];
                                }

                                break;

                            case "user":
                            case "auto":
                                $method = $item["data-type"] . "Processor";

                                if (method_exists($processor, $method)) {
                                    $value = $processor->{$method}($item, true);
                                }

                                break;

                            case "listview":
                                $sumflag = 0;
                                $lv_subject = $item["data-lv-title"];
                                $lv_sum = $item["data-lv-sum"];
                                $lv_sum_array = explode("`", $lv_sum);

                                if (strstr($lv_sum, "1")) {
                                    $sumflag = 1;
                                }

                                $lv_value = $itemValue;
                                $item_value = "<table class='commonTable2' ><tr>\n";
                                $my_array = explode("`", $lv_subject);
                                $array_count_title = sizeof($my_array);

                                if ($my_array[$array_count_title - 1] == "") {
                                    $array_count_title--;
                                }

                                for ($i = 0; $i < $array_count_title; $i++) {
                                    $item_value .= "<td>" . $my_array[$i] . "</td>\n";
                                }

                                $item_value .= "</tr>\n";
                                $my_array = explode("\r\n", $lv_value);
                                $array_count = sizeof($my_array);

                                if ($my_array[$array_count - 1] == "") {
                                    $array_count--;
                                }

                                $sum_data = array();

                                for ($i = 0; $i < $array_count; $i++) {
                                    $item_value .= "<tr>\n";
                                    $tr_data = $my_array[$i];
                                    $my_array1 = explode("`", $tr_data);

                                    for ($j = 0; $j < $array_count_title; $j++) {
                                        if ($lv_sum_array[$j] == 1) {
                                            $sum_data[$j] += $my_array1[$j];
                                        }

                                        $td_data = $my_array1[$j];

                                        if ($td_data == "") {
                                            $td_data = "&nbsp;";
                                        }

                                        $item_value .= "<td>" . $td_data . "</td>\n";
                                    }

                                    $item_value .= "</tr>\n";
                                }

                                if (($sumflag == 1) && (0 < $array_count)) {
                                    $item_value .= "<tr style='font-weight:bold;'>\n";

                                    for ($j = 0; $j < $array_count_title; $j++) {
                                        if ($sum_data[$j] == "") {
                                            $sumvalue = "&nbsp;";
                                        } else {
                                            $sumvalue = "合计：" . $sum_data[$j];
                                        }

                                        $item_value .= "<td align=right>" . $sumvalue . "</td>\n";
                                    }

                                    $item_value .= "</tr>\n";
                                }

                                $item_value .= "</table>\n";
                                break;

                            default:
                                $value = (isset($itemData[$itemID]) ? $itemData[$itemID] : "");
                                break;
                        }
                    }

                    $title = $item["data-title"];
                } else {
                    switch ($field) {
                        case "runid":
                            $value = $row["runid"];
                            $title = $lang["Flow no"];
                            break;

                        case "runname":
                            $value = $row["runName"];
                            $title = $lang["Flow subject/num"];
                            break;

                        case "runstatus":
                            if ($param["flowconditions"]["flowstatus"] == "all") {
                                if ($row["endtime"] == 0) {
                                    $status = "<span class=\"red\">{$lang["Perform"]}</span>";
                                } else {
                                    $status = $lang["Has ended"];
                                }
                            } elseif ($param["flowconditions"]["flowstatus"] == "0") {
                                $status = "<span class=\"red\">{$lang["Perform"]}</span>";
                            } else {
                                $status = $lang["Has ended"];
                            }

                            $value = $status;
                            $title = $lang["Flow status"];
                            break;

                        case "rundate":
                            $value = date("Y-m-d", $row["begintime"]);
                            $title = $lang["Flow begin date"];
                            break;

                        case "runtime":
                            $value = ConvertUtil::formatDate($row["begintime"]);
                            $title = $lang["Flow begin time"];
                            break;

                        default:
                            break;
                    }
                }

                if (StringUtil::findIn($param["sumfields"], $field)) {
                    $sum[] = $key;
                }

                $data[$index][$key] = $value;

                if ($index == 0) {
                    if (strcmp($param["groupbyfields"]["field"], $field) == 0) {
                        $group = $key;
                    }

                    $titles[$key] = $title;
                }
            }
        }

        return array("title" => $titles, "data" => $data, "sum" => $sum, "group" => $group, "sum_data" => isset($sum_data) ? $sum_data : array());
    }

    public static function getBeginTimeSearch($timeOne = "", $timeTwo = "")
    {
        $condition = array();
        if (!empty($timeOne) && !empty($timeTwo)) {
            $condition[] = "fr.begintime BETWEEN " . strtotime($timeOne . " 00:00:00") . " AND " . strtotime($timeTwo . " 23:59:59");
        } elseif (!empty($timeOne)) {
            $condition[] = "fr.begintime >= " . strtotime($timeOne . " 00:00:00");
        } elseif (!empty($timeTwo)) {
            $condition[] = "fr.begintime >= " . strtotime($timeTwo . " 23:59:59");
        }

        return $condition;
    }

    public static function getEndTimeSearch($timeOne = "", $timeTwo = "", $timeThree = "", $timeFour = "")
    {
        $condition = array();
        if (!empty($timeThree) && !empty($timeFour)) {
            $condition[] = "fr.endtime BETWEEN " . strtotime($timeThree . " 00:00:00") . " AND " . strtotime($timeFour . " 23:59:59");
        } elseif (!empty($timeOne)) {
            $condition[] = "fr.endtime <= " . strtotime($timeThree . " 00:00:00");
        } elseif (!empty($timeTwo)) {
            $condition[] = "fr.endtime <= " . strtotime($timeFour . " 23:59:59");
        }

        return $condition;
    }

    public static function getAttachNameSearch($attachName = "")
    {
        $condition = array();

        if (!empty($attachName)) {
            $runIds = array();

            foreach (FlowRun::model()->fetchAllAttachID() as $run) {
                $table = "attachment_" . AttachUtil::getTableId($run["runid"]);
                $sql = "SELECT 1 FROM {{$table}} WHERE FIND_IN_SET(aid,'{$run["attachmentid"]}') AND filename LIKE '%$attachName%'";
                $flag = Ibos::app()->db->createCommand()->setText($sql)->queryScalar();

                if ($flag) {
                    $runIds[] = $run["runid"];
                }
            }

            if (!empty($runIds)) {
                $condition[] = sprintf("FIND_IN_SET(fr.runid,'%s')", implode(",", $runIds));
            } else {
                $condition[] = "1=2";
            }
        }

        return $condition;
    }

    public static function getFlowStatusSearch($status)
    {
        $condition = array();

        if ($status !== "all") {
            if ($status == "0") {
                $condition[] = "fr.endtime = 0";
            } else {
                $condition[] = "fr.endtime != 0";
            }
        }

        return $condition;
    }

    public static function getFlowSearch($flowId, $searchType, $uid, $toId = "")
    {
        $condition = array();
        $flowIds = WfQueryUtil::getMyFlowIDs($uid);
        $myRuns = FlowRun::model()->fetchAllMyRunID($uid, $flowId);
        if (($searchType == "all") && (Ibos::app()->user->isadministrator != 1)) {
            $condition[] = sprintf("(fr.runid IN (%s) OR FIND_IN_SET('%s',ft.flowid))", implode(",", $myRuns), implode(",", $flowIds));
        } elseif ($searchType == "1") {
            $beginUser = $uid;
        } elseif ($searchType == "2") {
            $condition[] = sprintf("fr.runid IN (%s)", implode(",", $myRuns));
        } else {
            if (($searchType == "3") && (Ibos::app()->user->isadministrator != 1)) {
                $condition[] = sprintf("(FIND_IN_SET('%s',ft.flowid)", implode(",", $flowIds));
            } elseif (Ibos::app()->user->isadministrator != 1) {
                EnvUtil::iExit(Ibos::lang("Parameters error", "error"));
            }
        }

        if (!empty($toId) && ($searchType !== "1")) {
            $beginUser = $toId;
        }

        if (isset($beginUser)) {
            $condition[] = "fr.beginuser = $beginUser";
        }

        return $condition;
    }

    public static function getFormConditionSearch($flowId, $conditionStr)
    {
        $conditionStr = str_replace("flow_data_" . $flowId, "fd", $conditionStr);
        $conArr = explode("\n", $conditionStr);
        return array("(" . implode(" ", $conArr) . ")");
    }

    public static function hasAccess($user, $compareArr)
    {
        $deptAccess = WfNewUtil::compareIds($user["alldeptid"], $compareArr["deptid"], "d");
        $userAccess = WfNewUtil::compareIds($user["uid"], $compareArr["uid"], "u");
        $posAccess = WfNewUtil::compareIds($user["allposid"], $compareArr["positionid"], "p");
        if ($deptAccess || $userAccess || $posAccess) {
            return true;
        } else {
            return false;
        }
    }
}
