<?php

class FailedIp extends ICModel
{
    public static function model($className = "FailedIp")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{failedip}}";
    }

    public function insertIp($ip)
    {
        if ($this->countByAttributes(array("ip" => $ip, "lastupdate" => TIMESTAMP))) {
            $this->getDbConnection()->createCommand()->setText(sprintf("UPDATE %s SET `count`=`count`+1 WHERE ip='%s' AND lastupdate=%d", $this->tableName(), $ip, TIMESTAMP))->execute();
        } else {
            $this->add(array("ip" => $ip, "lastupdate" => TIMESTAMP, "count" => 1));
        }

        $this->deleteAll(sprintf("lastupdate < %d", TIMESTAMP - 3600));
    }
}
