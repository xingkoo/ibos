<?php

class OnlineTime extends ICModel
{
    public static function model($className = "OnlineTime")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{onlinetime}}";
    }

    public function updateOnlineTime($uid, $total, $thisMonth, $lastUpdate)
    {
        $record = $this->findByPk($uid);

        if (is_null($record)) {
            return false;
        }

        $record->total = $record->total + $total;
        $record->thismonth = $record->thismonth + $thisMonth;
        $record->lastupdate = $lastUpdate;
        $result = $record->save();
        return $result;
    }

    public function updateThisMonth()
    {
        $this->updateAll(array("thismonth" => 0));
    }
}
