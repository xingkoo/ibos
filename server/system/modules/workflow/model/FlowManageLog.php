<?php

class FlowManageLog extends ICModel
{
    public static function model($className = "FlowManageLog")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{flow_manage_log}}";
    }

    public function log($flowId, $flowName, $uid, $logType, $content)
    {
        $data = array("flowid" => $flowId, "flowname" => $flowName, "uid" => $uid, "time" => TIMESTAMP, "type" => $logType, "ip" => EnvUtil::getClientIp(), "content" => $content);
        return $this->add($data, true);
    }
}
