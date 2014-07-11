<?php

class Nav extends ICModel
{
    public static function model($className = "Nav")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{nav}}";
    }

    public function fetchAllByAllPid()
    {
        $return = array();
        $roots = $this->fetchAll("`pid` = 0 ORDER BY `sort` ASC");

        foreach ($roots as $root) {
            $root["child"] = $this->fetchAll("`pid` = {$root["id"]} ORDER BY `sort` ASC");
            $return[$root["id"]] = $root;
        }

        return $return;
    }

    public function deleteById($ids)
    {
        $id = explode(",", trim($ids, ","));
        $affecteds = $this->deleteByPk($id, "`system` = '0'");
        $affecteds += $this->deleteAll("FIND_IN_SET(pid,'" . implode(",", $id) . "')");
        return $affecteds;
    }
}
