<?php

class FlowProcessTurn extends ICModel
{
    public static function model($className = "FlowProcessTurn")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{flow_process_turn}}";
    }

    public function fetchByUnique($flowId, $processId, $to)
    {
        $con = sprintf("`flowid` = '%d' AND `processid` = '%d' AND `to` = '%d'", $flowId, $processId, $to);
        return $this->fetch($con);
    }
}
