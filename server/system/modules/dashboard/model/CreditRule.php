<?php

class CreditRule extends ICModel
{
    public static function model($className = "CreditRule")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{credit_rule}}";
    }
}
