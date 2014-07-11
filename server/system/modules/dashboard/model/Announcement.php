<?php

class Announcement extends ICModel
{
    public static function model($className = "Announcement")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{announcement}}";
    }

    public function fetchByTime($timestamp)
    {
        $condition = array(
            "order"     => "sort DESC",
            "condition" => "`starttime` <= :timestamp AND `endtime` > :timestamp",
            "params"    => array(":timestamp" => $timestamp)
        );
        return $this->fetch($condition);
    }

    public function fetchAllOnList($limit, $offset)
    {
        $condition = array("order" => "sort ASC", "limit" => $limit, "offset" => $offset);
        return $this->fetchAll($condition);
    }

    public function deleteById($ids)
    {
        $id = explode(",", trim($ids, ","));
        $affecteds = $this->deleteAll("FIND_IN_SET(id,'" . implode(",", $id) . "')");
        return $affecteds;
    }
}
