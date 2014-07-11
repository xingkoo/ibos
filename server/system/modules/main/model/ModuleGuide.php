<?php

class ModuleGuide extends ICModel
{
    public static function model($className = "ModuleGuide")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{module_guide}}";
    }

    public function fetchGuide($route, $uid)
    {
        return $this->fetch(array(
            "condition" => "route = :route AND uid = :uid",
            "params"    => array(":route" => $route, ":uid" => $uid)
        ));
    }
}
