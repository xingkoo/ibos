<?php

class Syscode extends ICModel
{
    public static function model($className = "Syscode")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{syscode}}";
    }

    public function fetchAllByAllPid()
    {
        $return = array();
        $roots = parent::fetchAll("`pid` = 0 ORDER BY `sort` ASC");

        foreach ($roots as $root) {
            $root["child"] = parent::fetchAll("`pid` = {$root["id"]} ORDER BY `sort` ASC");
            $return[$root["id"]] = $root;
        }

        return $return;
    }

    public function deleteById($ids)
    {
        $id = explode(",", trim($ids, ","));
        return parent::deleteByPk($id, "`system` = '0'");
    }
}
