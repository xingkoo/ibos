<?php

class ReportType extends ICModel
{
    public static function model($className = "ReportType")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{report_type}}";
    }

    public function fetchAllTypeByUid($uids)
    {
        $ids = (is_array($uids) ? implode(",", $uids) : trim($uids, ","));
        $types = $this->fetchAll("uid = 0 OR FIND_IN_SET(uid, '$ids') ORDER BY issystype DESC, sort ASC, typeid ASC");
        return $types;
    }

    public function fetchIntervaltypeByTypeid($typeid)
    {
        $type = $this->fetchByPk($typeid);
        return $type["intervaltype"];
    }

    public function fetchSysType()
    {
        return $this->fetchAllByAttributes(array("issystype" => 1));
    }
}
