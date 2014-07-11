<?php

class PositionResponsibility extends ICModel
{
    public static function model($className = "PositionResponsibility")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{position_responsibility}}";
    }

    public function fetchAllByPosId($id)
    {
        return $this->fetchAll("`positionid` = :id", array(":id" => $id));
    }
}
