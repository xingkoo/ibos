<?php

class FailedLogin extends ICModel
{
    public static function model($className = "FailedLogin")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{failedlogin}}";
    }

    public function fetchIp($ip)
    {
        $criteria = array("condition" => sprintf("ip='%s'", $ip));
        return $this->fetch($criteria);
    }

    public function deleteOld($time)
    {
        $criteria = array("condition" => sprintf("lastupdate<%d", TIMESTAMP - intval($time)));
        $this->deleteAll($criteria);
    }

    public function updateFailed($ip)
    {
        $this->getDbConnection()->createCommand()->setText(sprintf("UPDATE %s SET count=count+1, lastupdate=%d WHERE ip='%s'", $this->tableName(), TIMESTAMP, $ip))->execute();
    }
}
