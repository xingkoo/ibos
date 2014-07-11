<?php

class AssignmentApply extends ICModel
{
    public static function model($className = "AssignmentApply")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{assignment_apply}}";
    }

    public function addDelay($uid, $assignmentId, $delayReason, $stattime, $endtime)
    {
        $this->deleteAll("uid = $uid AND assignmentid = $assignmentId");
        $data = array("uid" => $uid, "assignmentid" => $assignmentId, "isdelay" => 1, "delayreason" => $delayReason, "delaystarttime" => $stattime, "delayendtime" => $endtime);
        return $this->add($data);
    }

    public function addCancel($uid, $assignmentId, $cancelReason)
    {
        $this->deleteAll("uid = $uid AND assignmentid = $assignmentId");
        $data = array("uid" => $uid, "assignmentid" => $assignmentId, "iscancel" => 1, "cancelreason" => $cancelReason);
        return $this->add($data);
    }
}
