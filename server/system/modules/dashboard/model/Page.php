<?php

class Page extends ICModel
{
    public static function model($className = "Page")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{page}}";
    }
}
