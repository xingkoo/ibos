<?php

class UserUtil
{
    public static function getUserProfile($field)
    {
        if (Ibos::app()->user->hasState($field)) {
            return Ibos::app()->user->$field;
        }

        static $modelFields = array(
            "count"  => array("extcredits1", "extcredits2", "extcredits3", "extcredits4", "extcredits5", "oltime", "attachsize"),
            "status" => array("regip", "lastip", "lastvisit", "lastactivity", "invisible")
        );
        $profileModel = "";

        foreach ($modelFields as $model => $fields) {
            if (in_array($field, $fields)) {
                $profileModel = $model;
                break;
            }
        }

        if ($profileModel) {
            $model = "User" . ucfirst($profileModel);
            $mergeArray = $model::model()->fetchByPk(Ibos::app()->user->uid);

            if ($mergeArray) {
                foreach ($mergeArray as $key => $val) {
                    Ibos::app()->user->setState($key, $val);
                }
            }

            return Ibos::app()->user->$field;
        }

        return null;
    }

    public static function loadUser()
    {
        $users = Ibos::app()->setting->get("cache/users");
        return $users;
    }

    public static function exportUser($uids)
    {
        $users = User::model()->fetchAllByUids($uids);
        $xmlContents = XmlUtil::arrayToXml($users);
        $xmlName = date("Y-m-d") . "-user";

        if (ob_get_length()) {
            ob_end_clean();
        }

        header("Cache-control: private");
        header("Content-type: text/xml");
        header("Content-Disposition: attachment; filename= $xmlName.xml");
        exit($xmlContents);
    }

    public static function cleanCache($uid)
    {
        $users = UserUtil::loadUser();

        if (isset($users[$uid])) {
            unset($users[$uid]);
            User::model()->makeCache($users);
        }

        CacheUtil::rm("userData_" . $uid);
    }

    public static function checkDataPurv($purvId)
    {
        return true;
    }

    public static function setPosition($positionId, $users)
    {
        $oldUids = User::model()->fetchUidByPosId($positionId, false);
        $userId = explode(",", trim($users, ","));
        $newUids = StringUtil::getUid($userId);
        $delDiff = array_diff($oldUids, $newUids);
        $addDiff = array_diff($newUids, $oldUids);
        if (!empty($addDiff) || !empty($delDiff)) {
            $updateUser = false;
            $userData = self::loadUser();

            if ($addDiff) {
                foreach ($addDiff as $newUid) {
                    $record = $userData[$newUid];

                    if (empty($record["positionid"])) {
                        User::model()->modify($newUid, array("positionid" => $positionId));
                        $updateUser = true;
                    } elseif (strcmp($record["positionid"], $positionId) !== 0) {
                        PositionRelated::model()->add(array("positionid" => $positionId, "uid" => $newUid), false, true);
                    }
                }
            }

            if ($delDiff) {
                foreach ($delDiff as $diffId) {
                    $record = $userData[$diffId];
                    PositionRelated::model()->delete("`positionid` = :positionid AND `uid` = :uid", array(":positionid" => $positionId, ":uid" => $diffId));

                    if (strcmp($positionId, $record["positionid"]) == 0) {
                        User::model()->modify($diffId, array("positionid" => 0));
                        $updateUser = true;
                    }
                }
            }

            $mainNumber = User::model()->count("`positionid` = :positionid", array(":positionid" => $positionId));
            $auxiliaryNumber = PositionRelated::model()->countByPositionId($positionId);
            Position::model()->modify($positionId, array("number" => (int) $mainNumber + $auxiliaryNumber));
            $updateUser && CacheUtil::update("users");
            OrgUtil::update();
        }
    }

    public static function wrapUserInfo($user)
    {
        $user["group_title"] = "";
        $user["next_group_credit"] = $user["upgrade_percent"] = 0;
        $currentGroup = (!empty($user["groupid"]) ? UserGroup::model()->fetchByPk($user["groupid"]) : array());

        if (!empty($currentGroup)) {
            $user["group_title"] = $currentGroup["title"];

            if ($currentGroup["creditslower"] !== "0") {
                $user["upgrade_percent"] = round((double) $user["credits"] / $currentGroup["creditslower"] * 100, 2);
            }

            $user["next_group_credit"] = (int) $currentGroup["creditslower"];
        }

        $user["level"] = self::getUserLevel($user["groupid"]);
        CacheUtil::load(array("department", "position"));
        $position = PositionUtil::loadPosition();
        $department = DepartmentUtil::loadDepartment();

        if (0 < $user["deptid"]) {
            $relatedDeptId = DepartmentRelated::model()->fetchAllDeptIdByUid($user["uid"]);
            $deptIds = array_merge((array) $relatedDeptId, array($user["deptid"]));
            $user["alldeptid"] = implode(",", array_unique($deptIds));
            $user["allupdeptid"] = Department::model()->queryDept($user["alldeptid"]);
            $user["alldowndeptid"] = Department::model()->fetchChildIdByDeptids($user["alldeptid"]);
            $user["relatedDeptId"] = implode(",", $relatedDeptId);
            $user["deptname"] = (isset($department[$user["deptid"]]) ? $department[$user["deptid"]]["deptname"] : "");
        } else {
            $user["alldeptid"] = $user["allupdeptid"] = $user["alldowndeptid"] = $user["relatedDeptId"] = $user["deptname"] = "";
        }

        if (0 < $user["positionid"]) {
            $relatedPosId = PositionRelated::model()->fetchAllPositionIdByUid($user["uid"]);
            $posIds = array_merge(array($user["positionid"]), (array) $relatedPosId);
            $user["allposid"] = implode(",", array_unique($posIds));
            $user["relatedPosId"] = implode(",", $relatedPosId);
            $user["posname"] = (isset($position[$user["positionid"]]) ? $position[$user["positionid"]]["posname"] : "");
        } else {
            $user["allposid"] = $user["relatedPosId"] = $user["posname"] = "";
        }

        $user["space_url"] = "?r=user/home/index&uid=" . $user["uid"];
        $user["avatar_middle"] = "avatar.php?uid={$user["uid"]}&size=middle&engine=" . ENGINE;
        $user["avatar_small"] = "avatar.php?uid={$user["uid"]}&size=small&engine=" . ENGINE;
        $user["avatar_big"] = "avatar.php?uid={$user["uid"]}&size=big&engine=" . ENGINE;
        $user["bg_big"] = "bg.php?uid={$user["uid"]}&size=big&engine=" . ENGINE;
        $user["bg_small"] = "bg.php?uid={$user["uid"]}&size=small&engine=" . ENGINE;
        $profile = UserProfile::model()->fetchByUid($user["uid"]);
        $user = array_merge($user, (array) $profile);
        return $user;
    }

    public static function getHomeBg($uid)
    {
        $uid = sprintf("%09d", abs(intval($uid)));
        $level1 = substr($uid, 0, 3);
        $level2 = substr($uid, 3, 2);
        $level3 = substr($uid, 5, 2);
        return $level1 . "/" . $level2 . "/" . $level3 . "/" . substr($uid, -2) . "_banner.jpg";
    }

    public static function getUserLevel($groupid)
    {
        $cache = Ibos::app()->setting->get("cache/usergroup");
        $userGroupId = array_keys($cache);
        $level = array_search($groupid, $userGroupId);
        $level++;

        if (20 < $level) {
            return "max";
        } else {
            return intval(abs($level));
        }
    }

    public static function checkUserGroup($uid = 0)
    {
        $credit = CreditUtil::getInstance();
        $credit->checkUserGroup($uid);
    }

    public static function batchUpdateCredit($action, $uids = 0, $extraSql = array(), $coef = 1)
    {
        $credit = CreditUtil::getInstance();

        if ($extraSql) {
            $credit->setExtraSql($extraSql);
        }

        return $credit->updateCreditByRule($action, $uids, $coef);
    }

    public static function updateUserCount($uids, $dataArr = array(), $checkGroup = true, $operation = "", $relatedid = 0, $ruletxt = "")
    {
        if (!empty($uids) && is_array($dataArr) && $dataArr) {
            return self::_updateUserCount($uids, $dataArr, $checkGroup, $operation, $relatedid, $ruletxt);
        }

        return true;
    }

    public static function updateCreditByAction($action, $uid = 0, $extraSql = array(), $needle = "", $coef = 1, $update = 1)
    {
        $credit = CreditUtil::getInstance();

        if (!empty($extraSql)) {
            $credit->setExtraSql($extraSql);
        }

        return $credit->execRule($action, $uid, $needle, $coef, $update);
    }

    public static function creditLog($uids, $operation, $relatedid, $data)
    {
        if (!$operation || empty($relatedid) || empty($uids) || empty($data)) {
            return null;
        }

        $log = array("uid" => $uids, "operation" => $operation, "relatedid" => $relatedid, "dateline" => TIMESTAMP);

        foreach ($data as $k => $v) {
            $log[$k] = $v;
        }

        if (is_array($uids)) {
            foreach ($uids as $k => $uid) {
                $log["uid"] = $uid;
                $log["relatedid"] = (is_array($relatedid) ? $relatedid[$k] : $relatedid);
                CreditLog::model()->add($log);
            }
        } else {
            CreditLog::model()->add($log);
        }
    }

    private static function _updateUserCount($uids, $dataArr = array(), $checkGroup = true, $operation = "", $relatedid = 0, $ruletxt = "")
    {
        if (empty($uids)) {
            return null;
        }

        if (!is_array($dataArr) || empty($dataArr)) {
            return null;
        }

        if ($operation && $relatedid) {
            $writeLog = true;
        } else {
            $writeLog = false;
        }

        $data = $log = array();

        foreach ($dataArr as $key => $val) {
            if (empty($val)) {
                continue;
            }

            $val = intval($val);
            $id = intval($key);
            $id = (!$id && (substr($key, 0, -1) == "extcredits") ? intval(substr($key, -1, 1)) : $id);
            if ((0 < $id) && ($id < 9)) {
                $data["extcredits" . $id] = $val;

                if ($writeLog) {
                    $log["extcredits" . $id] = $val;
                }
            } else {
                $data[$key] = $val;
            }
        }

        if ($writeLog) {
            self::creditLog($uids, $operation, $relatedid, $log);
        }

        if ($data) {
            $credit = CreditUtil::getInstance();
            $credit->updateUserCount($data, $uids, $checkGroup, $ruletxt);
        }
    }

    public static function getManagerDeptSubUserByUid($uid)
    {
        $subUserArr = User::model()->fetchSubByPk($uid);
        $uidArr = ConvertUtil::getSubByKey($subUserArr, "uid");
        $allDeptidArr = ConvertUtil::getSubByKey($subUserArr, "deptid");
        $deptidArr = array_unique($allDeptidArr);
        $unit = Yii::app()->setting->get("setting/unit");
        $undefindDeptName = (isset($unit) ? $unit["fullname"] : "未定义部门");
        $uidStr = implode(",", $uidArr);
        $dept = array();

        foreach ($deptidArr as $index => $deptid) {
            if ($deptid == 0) {
                $dept[$index]["deptname"] = $undefindDeptName;
            } else {
                $dept[$index] = Department::model()->fetchByPk($deptid);
            }

            $subUser = User::model()->fetchAll(array(
                "select"    => "*",
                "condition" => "deptid=:deptid AND uid IN($uidStr) AND status != 2",
                "params"    => array(":deptid" => $deptid)
            ));

            if (empty($subUser)) {
                unset($dept[$index]);
                continue;
            }

            foreach ($subUser as $k => $user) {
                $subUser[$k]["hasSub"] = self::hasSubUid($user["uid"]);
            }

            $dept[$index]["user"] = $subUser;
            $subUids = ConvertUtil::getSubByKey($subUser, "uid");
            $dept[$index]["subUids"] = implode(",", $subUids);
        }

        return $dept;
    }

    public static function hasSubUid($uid)
    {
        static $users = array();

        if (!isset($users[$uid])) {
            $users[$uid] = User::model()->countByAttributes(array("upuid" => $uid), "status != :status", array(":status" => 2));
        }

        return $users[$uid];
    }

    public static function getAllSubs($uid, $limitCondition = "", $uidFlag = false)
    {
        $departmentList = DepartmentUtil::loadDepartment();
        $uidArr = User::model()->fetchSubUidByUid($uid);
        $deptArr = array();

        if (!empty($departmentList)) {
            foreach ($departmentList as $department) {
                if ($department["manager"] == $uid) {
                    $deptArr[] = $department;
                }
            }
        }

        $deptAllUidArr = array();

        if (0 < count($deptArr)) {
            foreach ($deptArr as $department) {
                $records = User::model()->fetchAll(array(
                    "select"    => array("uid"),
                    "condition" => "deptid=:deptid AND uid NOT IN(:uid) AND status != 2 " . $limitCondition,
                    "params"    => array(":deptid" => $department["deptid"], ":uid" => $uid)
                ));
                $deptUidArr = array();

                foreach ($records as $record) {
                    $deptUidArr[] = $record["uid"];
                }

                $deptAllUidArr = array_merge($deptAllUidArr, $deptUidArr);
            }
        }

        $allUidArr = array_merge($uidArr, $deptAllUidArr);
        $arr = array_unique($allUidArr);

        if ($uidFlag) {
            return $arr;
        }

        $users = array();

        if (!empty($arr)) {
            $users = User::model()->fetchAllByUids($arr);
        }

        return $users;
    }

    public static function checkIsSub($uid, $subUid)
    {
        $subUidArr = User::model()->fetchSubUidByUid($uid);

        if (in_array($subUid, $subUidArr)) {
            return true;
        }

        if (!empty($subUidArr)) {
            foreach ($subUidArr as $uid) {
                $allSubUids = self::getAllSubs($uid, "", true);

                if (in_array($subUid, $allSubUids)) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function checkIsUpUid($uid, $upUid)
    {
        $user = User::model()->fetchByPk($uid);
        if (!empty($user) && ($upUid == $user["upuid"])) {
            return true;
        } else {
            return false;
        }
    }

    public static function getSupUid($uid)
    {
        $user = User::model()->fetchByUid($uid);
        $supUid = 0;

        if ($user["upuid"] != 0) {
            $supUid = $user["upuid"];
        } elseif ($user["deptid"] != 0) {
            $dept = Department::model()->fetchByPk($user["deptid"]);
            $supUid = $dept["manager"];
        }

        return $supUid;
    }

    public static function getAvatar($uid, $size = "middle")
    {
        $size = (in_array($size, array("big", "middle", "small")) ? $size : "middle");
        $uid = sprintf("%09d", abs(intval($uid)));
        $level1 = substr($uid, 0, 3);
        $level2 = substr($uid, 3, 2);
        $level3 = substr($uid, 5, 2);
        return $level1 . "/" . $level2 . "/" . $level3 . "/" . substr($uid, -2) . "_avatar_$size.jpg";
    }

    public static function getBg($uid, $size = "small")
    {
        $size = (in_array($size, array("big", "middle", "small")) ? $size : "small");
        $uid = sprintf("%09d", abs(intval($uid)));
        $level1 = substr($uid, 0, 3);
        $level2 = substr($uid, 3, 2);
        $level3 = substr($uid, 5, 2);
        return $level1 . "/" . $level2 . "/" . $level3 . "/" . substr($uid, -2) . "_bg_$size.jpg";
    }

    public static function getTempBg($name, $size)
    {
        $path = "./data/home/";
        $bgUrl = $path . $name . "_" . $size . ".jpg";
        return $bgUrl;
    }

    public static function isOnline($uid)
    {
        static $userOnline = array();

        if (empty($userOnline[$uid])) {
            $user = Session::model()->fetchByUid($uid);
            if ($user && ($user["invisible"] === "0")) {
                $userOnline[$uid] = 1;
            }
        }

        return isset($userOnline[$uid]) ? true : false;
    }

    public static function getOnlineStatus($uid)
    {
        static $userOnline = array();

        if (empty($userOnline[$uid])) {
            $user = Session::model()->fetchByUid($uid);

            if ($user) {
                $userOnline[$uid] = $user["invisible"];
            }
        }

        return isset($userOnline[$uid]) ? intval($userOnline[$uid]) : -1;
    }

    public static function checkNavPurv($nav)
    {
        if (($nav["system"] == "1") && !empty($nav["module"]) && !Ibos::app()->user->isadministrator) {
            $access = self::getUserPurv(Ibos::app()->user->uid);
            return isset($access[$nav["url"]]);
        }

        return true;
    }

    public static function getUserPurv($uid)
    {
        static $users = array();

        if (!isset($users[$uid])) {
            $access = array();
            $user = User::model()->fetchByUid($uid);

            foreach (explode(",", $user["allposid"]) as $posId) {
                $access = array_merge($access, PositionUtil::getPurv($posId));
            }

            $users[$uid] = $access;
        }

        return $users[$uid];
    }

    public static function getUserByPy($uids = null, $first = false)
    {
        $group = array();

        if (is_array($uids)) {
            $list = User::model()->fetchAllByUids($uids);
        } else {
            $list = UserUtil::loadUser();
        }

        foreach ($list as $k => $v) {
            $py = ConvertUtil::getPY($v["realname"], $first);

            if (!empty($py)) {
                $group[strtoupper($py[0])][] = $k;
            }
        }

        ksort($group);
        $data = array("datas" => $list, "group" => $group);
        return $data;
    }

    public static function getJsConstantUids($uid)
    {
        $inEnabledContact = ModuleUtil::getIsEnabled("contact");
        $cUids = ($inEnabledContact ? Contact::model()->fetchAllConstantByUid($uid) : array());
        $cUidStr = (empty($cUids) ? "" : StringUtil::wrapId($cUids));
        return empty($cUidStr) ? "" : CJSON::encode(explode(",", $cUidStr));
    }

    public static function getAccountSetting()
    {
        $account = unserialize(Setting::model()->fetchSettingValueByKey("account"));

        if ($account["mixed"]) {
            $preg = "[0-9]+[A-Za-z]+|[A-Za-z]+[0-9]+";
        } else {
            $preg = "^[A-Za-z0-9\!\@\#$\%\^\&\*\.\~]{" . $account["minlength"] . ",32}\$";
        }

        switch ($account["autologin"]) {
            case "1":
                $cookieTime = 86400 * 7;
                break;

            case "2":
                $cookieTime = 86400 * 30;
                break;

            case "3":
                $cookieTime = 86400 * 90;
                break;

            case "0":
                $cookieTime = 86400;
            default:
                $cookieTime = 0;
                break;
        }

        $account["preg"] = $preg;
        $account["cookietime"] = $cookieTime;
        $account["timeout"] = $account["timeout"] * 60;
        return $account;
    }

    public static function handleUserGroupByDept($users)
    {
        if (empty($users)) {
            return array();
        }

        $ret = array();
        $deptIdsTemp = ConvertUtil::getSubByKey($users, "deptid");
        $deptIds = array_unique($deptIdsTemp);
        $departments = DepartmentUtil::loadDepartment();

        foreach ($deptIds as $deptId) {
            $ret[$deptId]["deptname"] = (isset($departments[$deptId]) ? $departments[$deptId]["deptname"] : "未定义部门");

            foreach ($users as $k => $user) {
                if ($user["deptid"] == $deptId) {
                    $ret[$deptId]["users"][$user["uid"]] = $user;
                    unset($user[$k]);
                }
            }
        }

        return $ret;
    }
}
