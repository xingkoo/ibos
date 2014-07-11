<?php

class CalendarRecord extends ICModel
{
    public static function model($className = "CalendarRecord")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{calendar_record}}";
    }
}
