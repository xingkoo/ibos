<?php

class UserStatus extends ICModel
{
    public static function model($className = "UserStatus")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{user_status}}";
    }
}
