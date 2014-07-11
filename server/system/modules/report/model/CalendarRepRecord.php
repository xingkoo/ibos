<?php

class CalendarRepRecord extends ICModel
{
    public static function model($className = "CalendarRepRecord")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{calendar_rep_record}}";
    }
}
