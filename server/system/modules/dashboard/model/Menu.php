<?php

class Menu extends ICModel
{
    public static function model($className = "Menu")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{menu}}";
    }

    public function fetchByModule($module)
    {
        $condition = "`m` = '$module' AND `pid` = 0 AND `disabled` = 0";
        return parent::fetch($condition);
    }

    public function fetchAllRootMenu()
    {
        $condition = "pid = 0 AND disabled = 0";
        $result = parent::fetchAll($condition);
        return $result;
    }
}
