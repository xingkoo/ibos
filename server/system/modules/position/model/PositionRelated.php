<?php

class PositionRelated extends ICModel
{
    public static function model($className = "PositionRelated")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{position_related}}";
    }

    public function countByPositionId($positionId)
    {
        return $this->count("`positionid` = :positionid", array(":positionid" => $positionId));
    }

    public function fetchAllPositionIdByUid($uid)
    {
        static $uids = array();

        if (!isset($uids[$uid])) {
            $posids = $this->fetchAll(array(
                "select"    => "positionid",
                "condition" => "`uid` = :uid",
                "params"    => array(":uid" => $uid)
            ));
            $uids[$uid] = ConvertUtil::getSubByKey($posids, "positionid");
        }

        return $uids[$uid];
    }
}
