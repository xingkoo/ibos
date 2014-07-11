<?php

class CreditRuleLogField extends ICModel
{
    public static function model($className = "CreditRuleLogField")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{credit_rule_log_field}}";
    }
}
