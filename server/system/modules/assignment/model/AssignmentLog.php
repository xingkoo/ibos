<?php

class AssignmentLog extends ICModel
{
    public static function model($className = "AssignmentLog")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{assignment_log}}";
    }

    public function addLog($assignmentId, $type, $content)
    {
        $uid = Ibos::app()->user->uid;
        $realname = User::model()->fetchRealnameByUid($uid);
        $data = array("assignmentid" => $assignmentId, "uid" => $uid, "time" => TIMESTAMP, "ip" => EnvUtil::getClientIp(), "type" => $type, "content" => $realname . $content);
        return $this->add($data);
    }
}
