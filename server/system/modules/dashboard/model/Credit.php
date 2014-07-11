<?php

class Credit extends ICModel
{
    public static function model($className = "Credit")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{credit}}";
    }
}
