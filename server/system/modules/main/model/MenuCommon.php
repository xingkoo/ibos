<?php

class MenuCommon extends ICModel
{
    public static function model($className = "MenuCommon")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{menu_common}}";
    }

    public function fetchAllEnabledMenu()
    {
        $allEnabledModules = Module::model()->fetchAllEnabledModule();
        $enabledModStr = implode(",", array_keys($allEnabledModules));
        $criteria = array("condition" => "(FIND_IN_SET(`module`, '$enabledModStr') OR iscustom=1) AND disabled=0", "order" => "`sort` ASC");
        $menus = $this->fetchAllSortByPk("id", $criteria);
        return $menus;
    }

    public function fetchCommonAndNotUsed()
    {
        $allMenus = $this->fetchAllEnabledMenu();
        $commonMenu = $notUsedMenu = array();

        foreach ($allMenus as $moduleName => $menuInfo) {
            if ($menuInfo["iscommon"] == 1) {
                $commonMenu[$moduleName] = $menuInfo;
            } else {
                $notUsedMenu[$moduleName] = $menuInfo;
            }
        }

        $ret = array("commonMenu" => $commonMenu, "notUsedMenu" => $notUsedMenu);
        return $ret;
    }
}
