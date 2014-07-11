<?php

class FlowRunfeedback extends ICModel
{
    public static function model($className = "FlowRunfeedback")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{flow_run_feedback}}";
    }

    public function getHasSignAccess($runId, $processId, $uid)
    {
        $result = $this->getDbConnection()->createCommand()->select("feedid")->from($this->tableName())->where(array("and", sprintf("runid = %d", $runId), sprintf("processid = %d", $processId), sprintf("uid = %d", $uid)))->queryScalar();
        return $result;
    }

    public function getFeedbackReply($feedId)
    {
        $criteria = array("condition" => sprintf("replyid = %d", intval($feedId)), "order" => "edittime");
        $list = $this->fetchAll($criteria);

        foreach ($list as &$fb) {
            $fb["user"] = User::model()->fetchByUid($fb["uid"]);
        }

        return $list;
    }

    public function fetchAllByRunID($runId)
    {
        $criteria = array("condition" => sprintf("runid = %d AND feedflag = 0", intval($runId)), "order" => "processid,edittime");
        return $this->fetchAll($criteria);
    }

    public function fetchAllAttachByRunId($runId)
    {
        $criteria = array("select" => "attachmentid", "condition" => sprintf("runid = %d AND attachmentid != ''", intval($runId)));
        return $this->fetchAll($criteria);
    }
}
