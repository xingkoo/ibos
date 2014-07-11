<?php

class FlowTimer extends ICModel
{
    public static function model($className = "FlowTimer")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{flow_timer}}";
    }

    public function fetchAllByFlowId($flowId)
    {
        $list = $this->fetchAllByAttributes(array("flowid" => intval($flowId)));

        foreach ($list as &$timer) {
            $timer["value"] = StringUtil::wrapId($timer["uid"]);
            $timer["period"] = $timer["type"];
            $timer["id"] = $timer["tid"];
            $timer["date"] = $timer["remindtime"];

            if (!in_array($timer["type"], array(1, 5))) {
                $timer["selected"] = $timer["reminddate"];
            }
        }

        return $list;
    }
}
