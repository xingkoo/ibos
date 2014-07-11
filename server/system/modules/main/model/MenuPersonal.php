<?php

class MenuPersonal extends ICModel
{
    public static function model($className = "MenuPersonal")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{menu_personal}}";
    }

    public function fetchMenuByUid($uid)
    {
        $menu = $this->fetch("uid = $uid");

        if (empty($menu)) {
            $ret = MenuCommon::model()->fetchCommonAndNotUsed();
        } else {
            $allMenus = MenuCommon::model()->fetchAllEnabledMenu();
            $allIds = ConvertUtil::getSubByKey($allMenus, "id");
            $menuIds = explode(",", $menu["common"]);
            $commonMenu = $notUsedMenu = array();

            foreach ($menuIds as $id) {
                if (in_array($id, $allIds)) {
                    $commonMenu[$id] = $allMenus[$id];
                }
            }

            foreach ($allMenus as $id => $menuInfo) {
                if (!in_array($menuInfo["id"], $menuIds)) {
                    $notUsedMenu[$id] = $menuInfo;
                }
            }

            $ret = array("commonMenu" => $commonMenu, "notUsedMenu" => $notUsedMenu);
        }

        return $ret;
    }
}
