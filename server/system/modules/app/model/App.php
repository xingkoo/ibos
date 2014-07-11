<?php

class App extends ICModel
{
    public static function model($className = "App")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{app}}";
    }
}
