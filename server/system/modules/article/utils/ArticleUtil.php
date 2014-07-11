<?php

class ArticleUtil
{
    public static function joinListCondition($type, $articleidArr, $catid = 0, $condition = "", $articleids = 0)
    {
        $typeWhere = "";
        $uid = Ibos::app()->user->uid;
        if (($type == "new") || ($type == "old")) {
            $articleidsStr = implode(",", $articleidArr);
            $articleids = (empty($articleidsStr) ? "-1" : $articleidsStr);
            $flag = ($type == "new" ? "NOT" : "");
            $typeWhere = " articleid " . $flag . " IN($articleids) AND status=1";
        } elseif ($type == "notallow") {
            $artIds = Article::model()->fetchUnApprovalArtIds($catid, $uid);
            $artIdStr = implode(",", $artIds);
            $typeWhere = "FIND_IN_SET(`articleid`, '$artIdStr')";
        } elseif ($type == "draft") {
            $typeWhere = "status='3' AND author='$uid'";
        } else {
            $typeWhere = "status ='1' AND approver!=0";
        }

        if (!empty($condition)) {
            $condition .= " AND " . $typeWhere;
        } else {
            $condition = $typeWhere;
        }

        $allDeptId = Ibos::app()->user->alldeptid . "";
        $allDeptId .= "," . Ibos::app()->user->allupdeptid . "";
        $allPosId = Ibos::app()->user->allposid . "";
        $deptCondition = "";
        $deptIdArr = explode(",", trim($allDeptId, ","));

        if (0 < count($deptIdArr)) {
            foreach ($deptIdArr as $deptId) {
                $deptCondition .= "FIND_IN_SET('$deptId',deptid) OR ";
            }

            $deptCondition = substr($deptCondition, 0, -4);
        } else {
            $deptCondition = "FIND_IN_SET('',deptid)";
        }

        $scopeCondition = " ( ((deptid='alldept' OR $deptCondition OR FIND_IN_SET('$allPosId',positionid) OR FIND_IN_SET('$uid',uid)) OR (deptid='' AND positionid='' AND uid='') OR (author='$uid') OR (approver='$uid')) )";
        $condition .= " AND " . $scopeCondition;

        if (!empty($catid)) {
            $condition .= " AND catid IN ($catid)";
        }

        return $condition;
    }

    public static function joinSearchCondition(array $search, $condition)
    {
        $searchCondition = "";
        $keyword = $search["keyword"];
        $starttime = $search["starttime"];
        $endtime = $search["endtime"];

        if (!empty($keyword)) {
            $searchCondition .= " subject LIKE '%$keyword%' AND ";
        }

        if (!empty($starttime)) {
            $starttime = strtotime($starttime);
            $searchCondition .= " addtime>=$starttime AND";
        }

        if (!empty($endtime)) {
            $endtime = strtotime($endtime) + (24 * 60 * 60);
            $searchCondition .= " addtime<=$endtime AND";
        }

        $newCondition = (empty($searchCondition) ? "" : substr($searchCondition, 0, -4));
        return $condition . $newCondition;
    }

    public static function checkReadScope($uid, $data)
    {
        if ($data["deptid"] == "alldept") {
            return true;
        }

        if ($uid == $data["author"]) {
            return true;
        }

        if (empty($data["deptid"]) && empty($data["positionid"]) && empty($data["uid"])) {
            return true;
        }

        $user = User::model()->fetch(array(
        "select"    => array("deptid", "positionid"),
        "condition" => "uid=:uid",
        "params"    => array(":uid" => $uid)
        ));
        $childDeptid = Department::model()->fetchChildIdByDeptids($data["deptid"]);

        if (StringUtil::findIn($user["deptid"], $childDeptid . "," . $data["deptid"])) {
            return true;
        }

        if (StringUtil::findIn($data["positionid"], $user["positionid"])) {
            return true;
        }

        if (StringUtil::findIn($data["uid"], $uid)) {
            return true;
        }

        return false;
    }

    public static function getScopeUidArr($data)
    {
        $uidArr = array();

        if ($data["deptid"] == "alldept") {
            $users = Yii::app()->setting->get("cache/users");

            foreach ($users as $value) {
                $uidArr[] = $value["uid"];
            }
        } elseif (!empty($data["deptid"])) {
            foreach (explode(",", $data["deptid"]) as $value) {
                $criteria = array("select" => "uid", "condition" => "`deptid`=$value");
                $records = User::model()->fetchAll($criteria);

                foreach ($records as $record) {
                    $uidArr[] = $record["uid"];
                }
            }
        }

        if (!empty($data["positionid"])) {
            foreach (explode(",", $data["positionid"]) as $value) {
                $criteria = array("select" => "uid", "condition" => "`positionid`=$value");
                $records = User::model()->fetchAll($criteria);

                foreach ($records as $record) {
                    $uidArr[] = $record["uid"];
                }
            }
        }

        if (!empty($data["uid"])) {
            foreach (explode(",", $data["uid"]) as $value) {
                $uidArr[] = $value;
            }
        }

        return array_unique($uidArr);
    }

    public static function joinStringByArray($str, $data, $field, $join)
    {
        if (empty($str)) {
            return "";
        }

        $result = array();
        $strArr = explode(",", $str);

        foreach ($strArr as $value) {
            if (array_key_exists($value, $data)) {
                $result[] = $data[$value][$field];
            }
        }

        $resultStr = implode($join, $result);
        return $resultStr;
    }

    public static function joinSelectBoxValue($deptid, $positionid, $uid)
    {
        $tmp = array();

        if (!empty($deptid)) {
            if ($deptid == "alldept") {
                return "c_0";
            }

            $tmp[] = StringUtil::wrapId($deptid, "d");
        }

        if (!empty($positionid)) {
            $tmp[] = StringUtil::wrapId($positionid, "p");
        }

        if (!empty($uid)) {
            $tmp[] = StringUtil::wrapId($uid, "u");
        }

        return implode(",", $tmp);
    }

    public static function processHighLightRequestData($data)
    {
        $highLight = array();
        $highLight["highlightstyle"] = "";

        if (!empty($data["endTime"])) {
            $highLight["highlightendtime"] = (strtotime($data["endTime"]) + (24 * 60 * 60)) - 1;
        }

        if (empty($data["bold"])) {
            $data["bold"] = 0;
        }

        $highLight["highlightstyle"] = $highLight["highlightstyle"] . $data["bold"] . ",";

        if (empty($data["color"])) {
            $data["color"] = "";
        }

        $highLight["highlightstyle"] = $highLight["highlightstyle"] . $data["color"] . ",";

        if (empty($data["italic"])) {
            $data["italic"] = 0;
        }

        $highLight["highlightstyle"] = $highLight["highlightstyle"] . $data["italic"] . ",";

        if (empty($data["underline"])) {
            $data["underline"] = 0;
        }

        $highLight["highlightstyle"] = $highLight["highlightstyle"] . $data["underline"] . ",";
        $highLight["highlightstyle"] = substr($highLight["highlightstyle"], 0, strlen($highLight["highlightstyle"]) - 1);
        if (!empty($highLight["highlightendtime"]) || (3 < strlen($highLight["highlightstyle"]))) {
            $highLight["ishighlight"] = 1;
        } else {
            $highLight["ishighlight"] = 0;
        }

        return $highLight;
    }

    public static function handleSelectBoxData($data)
    {
        $result = array("deptid" => "", "positionid" => "", "uid" => "");

        if (!empty($data)) {
            if (isset($data["c"])) {
                $result = array("deptid" => "alldept", "positionid" => "", "uid" => "");
                return $result;
            }

            if (isset($data["d"])) {
                $result["deptid"] = implode(",", $data["d"]);
            }

            if (isset($data["p"])) {
                $result["positionid"] = implode(",", $data["p"]);
            }

            if (isset($data["u"])) {
                $result["uid"] = implode(",", $data["u"]);
            }
        } else {
            $result = array("deptid" => "alldept", "positionid" => "", "uid" => "");
        }

        return $result;
    }

    public static function getCategoryList($list, $pid, $level)
    {
        static $result = array();

        foreach ($list as $category) {
            if ($category["pid"] == $pid) {
                $category["level"] = $level;
                $result[] = $category;
                array_merge($result, self::getCategoryList($list, $category["catid"], $level + 1));
            }
        }

        return $result;
    }
}
