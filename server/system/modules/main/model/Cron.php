<?php

class Cron extends ICModel
{
    public static function model($className = "Cron")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{cron}}";
    }

    public function fetchByNextRun($timestamp = TIMESTAMP)
    {
        $timestamp = intval($timestamp);
        return $this->fetch("`available` > 0 AND `nextrun`<=$timestamp ORDER BY nextrun");
    }

    public function fetchByNextCron()
    {
        return $this->fetch("`available` > 0 ORDER BY nextrun");
    }
}
