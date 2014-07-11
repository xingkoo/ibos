<?php

class FlowRule extends ICModel
{
    public static function model($className = "FlowRule")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{flow_rule}}";
    }

    public function fetchRuleByFlowIDUid($flowID, $uid)
    {
        $criteria = array("condition" => sprintf("flowid = %d AND uid = %d AND status = 1", $flowID, $uid));
        return $this->fetch($criteria);
    }
}
