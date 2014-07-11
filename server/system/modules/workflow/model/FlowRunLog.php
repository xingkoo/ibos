<?php

class FlowRunLog extends ICModel
{
    public static function model($className = "FlowRunLog")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{flow_run_log}}";
    }

    public function fetchLog($runId, $processId, $flowProcess, $type = 0)
    {
        return $this->fetch(array("select" => "content", "condition" => sprintf("runid = %d AND processid = %d AND flowprocess=%d%s", $runId, $processId, $flowProcess, 0 < $type ? " AND type=" . $type : "")));
    }
}
