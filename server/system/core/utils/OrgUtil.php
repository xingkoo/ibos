<?php

class OrgUtil
{
    public static function update()
    {
        static $execute = false;

        if (!$execute) {
            self::createStaticJs();
            $execute = true;
        }

        return $execute;
    }

    public static function hookSyncUser($uid, $pwd = "", $syncFlag = 1)
    {
        $type = "";
        $imCfg = array();

        foreach (Ibos::app()->setting->get("setting/im") as $imType => $config) {
            if ($config["open"] == "1") {
                $type = $imType;
                $imCfg = $config;
                break;
            }
        }

        if (!empty($type) && !empty($imCfg) && ($imCfg["syncuser"] == "1")) {
            MainUtil::setCookie("hooksyncuser", 1, 30);
            MainUtil::setCookie("syncurl", Ibos::app()->createUrl("organization/api/syncUser", array("type" => $type, "uid" => $uid, "pwd" => $pwd, "flag" => $syncFlag)), 30);
        }
    }

    private static function createStaticJs()
    {
        CacheUtil::load(array("department", "position"), true);
        $unit = Ibos::app()->setting->get("setting/unit");
        $department = DepartmentUtil::loadDepartment();
        $users = UserUtil::loadUser();
        $position = PositionUtil::loadPosition();
        $positionCategory = PositionUtil::loadPositionCategory();
        $companyData = self::initCompany($unit);
        $deptData = self::initDept($department);
        $userData = self::initUser($users);
        $posData = self::initPosition($position);
        $posCatData = self::initPositionCategory($positionCategory);
        $default = file_get_contents(PATH_ROOT . "/static/js/src/org.default.js");

        if ($default) {
            $patterns = array("/\{\{(company)\}\}/", "/\{\{(department)\}\}/", "/\{\{(position)\}\}/", "/\{\{(users)\}\}/", "/\{\{(positioncategory)\}\}/");
            $replacements = array($companyData, $deptData, $posData, $userData, $posCatData);
            $new = preg_replace($patterns, $replacements, $default);
            FileUtil::createFile("data/org.js", $new);
            CacheUtil::update("setting");
        }
    }

    private static function initPositionCategory($categorys)
    {
        $catList = "";

        foreach ($categorys as $catId => $category) {
            $catList .= "{id: 'f_$catId', text: '{$category["name"]}', name: '{$category["name"]}', type: 'positioncategory', pId: 'f_{$category["pid"]}',open: 1,nocheck:true},\n";
        }

        return rtrim($catList, ",\n");
    }

    private static function initCompany($unit)
    {
        $comList = "{id: 'c_0', text: '{$unit["fullname"]}', name: '{$unit["fullname"]}', iconSkin: 'department', type: 'department', enable: 1, type: 0, open: 1}";
        return $comList;
    }

    private static function initDept($department)
    {
        $deptList = "";

        foreach ($department as $deptId => $dept) {
            $deptList .= "{id: 'd_$deptId', text: '{$dept["deptname"]}', name: '{$dept["deptname"]}', iconSkin: 'department', type: 'department', pId: 'd_{$dept["pid"]}', type: 3, enable: 1, open: 1},\n";
        }

        return rtrim($deptList, ",\n");
    }

    private static function initUser($users)
    {
        $userList = "";

        foreach ($users as $uid => $user) {
            $deptStr = $posStr = "";

            if (!empty($user["alldeptid"])) {
                $deptStr = StringUtil::wrapId($user["alldeptid"], "d");
            }

            if (!empty($user["allposid"])) {
                $posStr = StringUtil::wrapId($user["allposid"], "p");
            }

            $userList .= "{id: 'u_$uid', text: '{$user["realname"]}', name: '{$user["realname"]}', iconSkin: 'user', type: 'user', enable: 1, imgUrl:'{$user["avatar_small"]}',spaceurl:'{$user["space_url"]}',department:'$deptStr',position: '$posStr'},\n";
        }

        return rtrim($userList, ",\n");
    }

    private static function initPosition($position)
    {
        $posList = "";

        foreach ($position as $posId => $position) {
            $posList .= "{id: 'p_$posId', text: '{$position["posname"]}', name: '{$position["posname"]}', iconSkin: 'position', type: 'position', pId:'f_{$position["catid"]}', enable: 1, open: 0},\n";
        }

        return rtrim($posList, ",\n");
    }
}
