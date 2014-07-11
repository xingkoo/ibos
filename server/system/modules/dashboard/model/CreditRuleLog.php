<?php

class CreditRuleLog extends ICModel
{
    public static function model($className = "CreditRuleLog")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{credit_rule_log}}";
    }

    public function increase($clid, $logArr)
    {
        if ($clid && !empty($logArr) && is_array($logArr)) {
            $sqlText = "UPDATE %s SET %s WHERE clid=%d";
            return Ibos::app()->db->createCommand()->setText(sprintf($sqlText, $this->tableName(), implode(",", $logArr), $clid))->execute();
        }

        return 0;
    }

    public function fetchRuleLog($rid, $uid)
    {
        $log = array();
        if ($rid && $uid) {
            $log = $this->fetchByAttributes(array("uid" => $uid, "rid" => $rid));
        }

        return $log;
    }
}
