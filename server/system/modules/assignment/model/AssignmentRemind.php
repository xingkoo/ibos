<?php

class AssignmentRemind extends ICModel
{
    public static function model($className = "AssignmentRemind")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{assignment_remind}}";
    }

    public function fetchAllByUid($uid)
    {
        $record = $this->fetchAll("uid = $uid");
        $res = array();

        foreach ($record as $remind) {
            $res[$remind["assignmentid"]] = $remind["remindtime"];
        }

        return $res;
    }

    public function fetchCalendarids($assignmentId, $uid)
    {
        $records = $this->fetchAll("assignmentid = $assignmentId AND uid = $uid");
        return ConvertUtil::getSubByKey($records, "calendarid");
    }
}
