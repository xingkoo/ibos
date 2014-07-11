<?php

class WfCommonUtil
{
    public static function getWeek()
    {
        $lang = Ibos::getLangSource("date");
        $mapping = array($lang["Weekday"] . $lang["Chinese day"], $lang["Weekday"] . $lang["One"], $lang["Weekday"] . $lang["Two"], $lang["Weekday"] . $lang["Three"], $lang["Weekday"] . $lang["Four"], $lang["Weekday"] . $lang["Five"], $lang["Weekday"] . $lang["Six"]);
        return $mapping[date("w", TIMESTAMP)];
    }

    public static function getTime($secs, $format = "dhis")
    {
        return DateTimeUtil::getTime($secs, $format);
    }

    public static function param($param, $type = "ENCODE")
    {
        if ($type == "ENCODE") {
            $str = http_build_query($param);
            return rtrim(strtr(base64_encode($str), "+/", "-"), "=");
        } elseif ($type == "DECODE") {
            $str = base64_decode($param);
            $params = array();
            parse_str(urldecode($str), $params);
            return $params;
        }
    }

    public static function addRunData($flowId, $runData, $structure)
    {
        self::updateTable($flowId, $structure);
        FlowDataN::model()->add($flowId, $runData);
    }

    public static function updateTable($flowId, $structure = array())
    {
        $tableName = sprintf("{{flow_data_%d}}", intval($flowId));

        if (!self::tableExists($tableName)) {
            if (!self::createTable($tableName, $structure)) {
                return false;
            }

            return true;
        }

        if ($structure) {
            $rows = Ibos::app()->db->createCommand()->setText("SHOW FIELDS FROM " . $tableName)->queryAll();
            $fields = ConvertUtil::getSubByKey($rows, "Field");
            $items = array();

            foreach ($structure as $eName => $config) {
                $field = strtolower(trim($eName));

                if (!in_array($field, $fields)) {
                    if (substr($field, 0, 5) == "data_") {
                        $items[] = "ALTER TABLE " . $tableName . " ADD `$field` text NOT NULL;";
                    }
                }
            }

            if (!empty($items)) {
                Ibos::app()->db->createCommand()->setText(implode("\n", $items))->execute();
            }
        }

        return true;
    }

    public static function createTable($tableName, $structure = array())
    {
        $items = array();

        if (!empty($structure)) {
            foreach ($structure as $eName => $config) {
                $field = strtolower(trim($eName));

                if (substr($field, 0, 5) == "data_") {
                    $items[$field] = " `" . $field . "` text CHARACTER SET utf8 NOT NULL,";
                }
            }
        }

        natsort($items);
        $dbVersion = Ibos::app()->db->getServerVersion();

        if ("4.1" < $dbVersion) {
            $type = "ENGINE";
        } else {
            $type = "TYPE";
        }

        $extra = implode("\n", $items);
        $createStr = "CREATE TABLE IF NOT EXISTS `$tableName` (
                        `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                        `runid` mediumint(8) unsigned NOT NULL,
                        `name` varchar(255) NOT NULL,
                        `beginuser` mediumint(8) unsigned NOT NULL,
                        `begin` int(10) NOT NULL,
                        $extra
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `runid` (`runid`)
                    ) $type=MyISAM DEFAULT CHARSET=utf8;";
        Ibos::app()->db->createCommand()->setText($createStr)->execute();
        return true;
    }

    public static function getRunPermission($runId, $uid, $processId = 0)
    {
        $user = User::model()->fetchByUid($uid);
        $per = array();
        $flowId = FlowRun::model()->fetchFlowIdByRunId($runId);

        if (!$flowId) {
            return "";
        }

        if ($user["isadministrator"]) {
            $per[] = 1;
        }

        if (FlowRunProcess::model()->getIsOp($uid, $runId, $processId)) {
            $per[] = 2;
        }

        $permissions = FlowPermission::model()->fetchPermission($uid, $flowId);

        if (in_array($permissions, array(0, 1, 2), true)) {
            $per[] = 3;
        } elseif ($permissions == 3) {
            $per[] = 5;
        }

        if (FlowRunProcess::model()->getIsAgent($uid, $runId, $processId)) {
            $per[] = 4;
        }

        return implode(",", $per);
    }

    public static function tableExists($tableName)
    {
        return Ibos::app()->db->createCommand()->setText(sprintf("SHOW TABLES LIKE '%s'", $tableName))->execute();
    }

    public static function checkDeptPurv($uid, $deptId = 0, $catId = 0)
    {
        $user = User::model()->fetchByUid($uid);

        if ($user["isadministrator"] == 1) {
            return true;
        }

        if ($deptId == "") {
            if (0 < $catId) {
                $cat = FlowCategory::model()->fetchByPk($catId);

                if ($cat) {
                    return self::checkDeptPurv($uid, $cat["deptid"], 0);
                }
            }

            return true;
        }

        $purv = false;

        if (!empty($user["alldeptid"])) {
            foreach (explode(",", $user["alldeptid"]) as $userDept) {
                foreach (explode(",", $deptId) as $dept) {
                    if (($userDept == $dept) || DepartmentUtil::isDeptParent($userDept, $dept)) {
                        $purv = true;
                        break;
                    }
                }
            }
        }

        return $purv;
    }

    public static function loadProcessCache($flowId)
    {
        $cacheName = "flowprocess_" . intval($flowId);
        $cache = CacheUtil::get($cacheName);

        if ($cache === false) {
            $cache = array();
            $data = Ibos::app()->db->createCommand()->select("ft.name,ft.type,fp.*")->from("{{flow_type}} ft")->leftJoin("{{flow_process}} fp", "ft.flowid = fp.flowid")->where(sprintf("ft.flowid = %d", $flowId))->order("ft.flowid,fp.processid")->queryAll();

            foreach ($data as $process) {
                $cache[$process["processid"]] = $process;
            }

            CacheUtil::set($cacheName, $cache);
        }

        return $cache;
    }

    public static function runlog($runId, $processId, $flowProcess, $uid, $logtype, $content, $toid = "")
    {
        $userip = EnvUtil::getClientIp();
        $run = new ICFlowRun($runId);
        $data = array("runid" => $runId, "runname" => $run->name, "flowid" => $run->flowid, "processid" => $processId, "flowprocess" => $flowProcess, "uid" => $uid, "time" => TIMESTAMP, "type" => $logtype, "ip" => $userip, "content" => $content, "toid" => $toid);

        if (FlowRunLog::model()->add($data)) {
            UserUtil::updateCreditByAction("wfnextpost", $uid);
            return true;
        }

        return false;
    }

    public static function getFlowList($uid, $filterUseStatus = true)
    {
        $temp = array();

        foreach (FlowType::model()->fetchAllFlow() as $flow) {
            if (!WfNewUtil::checkProcessPermission($flow["flowid"], 0, $uid)) {
                continue;
            }

            if ($filterUseStatus && ($flow["usestatus"] == 3)) {
                continue;
            }

            $data = array("id" => $flow["flowid"], "text" => $flow["name"]);

            if (!isset($temp[$flow["catid"]])) {
                $temp[$flow["catid"]]["text"] = $flow["catname"];
                $temp[$flow["catid"]]["children"] = array();
            }

            $temp[$flow["catid"]]["children"][] = $data;
        }

        $result = array_merge(array(), $temp);
        return $result;
    }

    public static function implodeSql($idstr, $field = "deptid")
    {
        $arr = explode(",", trim($idstr, ","));
        $sql = "";

        foreach ($arr as $k => $v) {
            $sql .= " OR $field LIKE '$v,%' OR $field LIKE '%,$v,%' OR $field LIKE '%$v%'";
        }

        return $sql;
    }
}
