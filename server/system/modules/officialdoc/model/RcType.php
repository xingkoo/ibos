<?php

class RcType extends ICModel
{
    public static function model($className = "RcType")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{rc_type}}";
    }
}
