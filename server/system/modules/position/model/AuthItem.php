<?php

class AuthItem extends ICModel
{
    public static function model($className = "AuthItem")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{auth_item}}";
    }
}
