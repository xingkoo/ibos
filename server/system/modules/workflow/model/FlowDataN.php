<?php

class FlowDataN
{
    public static function model()
    {
        return new self();
    }

    public function tableName($flowID)
    {
        return sprintf("{{flow_data_%d}}", intval($flowID));
    }

    public function fetch($flowID, $runID)
    {
        return Ibos::app()->db->createCommand()->select("*")->from($this->tableName($flowID))->where(sprintf("runid = %d", $runID))->queryRow();
    }

    public function add($flowID, $data)
    {
        $tableName = $this->tableName($flowID);
        return Ibos::app()->db->createCommand()->insert($tableName, $data);
    }

    public function fetchItem($itemID, $flowID, $runID)
    {
        $data = $this->fetch($flowID, $runID);
        return $data["data_" . $itemID];
    }

    public function update($flowID, $runID, $data)
    {
        $tableName = $this->tableName($flowID);
        return Ibos::app()->db->createCommand()->update($tableName, $data, sprintf("runid = %d", $runID));
    }

    public function deleteByRunId($flowID, $runId)
    {
        return Ibos::app()->db->createCommand()->delete($this->tableName($flowID), sprintf("runid = %d", intval($runId)));
    }
}
