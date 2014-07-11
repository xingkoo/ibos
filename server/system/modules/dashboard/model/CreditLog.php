<?php

class CreditLog extends ICModel
{
    public static function model($className = "CreditLog")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{credit_log}}";
    }
}
