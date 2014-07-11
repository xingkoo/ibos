<?php

class Cache extends ICModel
{
    public static function model($className = "Cache")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{cache}}";
    }
}
