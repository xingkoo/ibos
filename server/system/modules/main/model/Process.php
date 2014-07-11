<?php

class Process extends ICModel
{
    public static function model($className = "Process")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{process}}";
    }

    public function deleteProcess($name, $time)
    {
        $name = addslashes($name);
        return $this->deleteAll("processid='$name' OR expiry<" . intval($time));
    }
}
