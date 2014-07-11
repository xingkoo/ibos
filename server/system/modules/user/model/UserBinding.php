<?php

class UserBinding extends ICModel
{
    public static function model($className = "UserBinding")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{user_binding}}";
    }

    public function fetchValuesByUids($uids, $app)
    {
        $rs = $this->fetchAll(array("select" => "bindvalue", "condition" => sprintf("FIND_IN_SET(uid,'%s') AND app = '%s'", implode(",", $uids), $app)));
        return ConvertUtil::getSubByKey($rs, "bindvalue");
    }

    public function fetchBindValue($uid, $app)
    {
        $rs = $this->fetch(array("select" => "bindvalue", "condition" => sprintf("uid = %d AND app ='%s'", $uid, $app)));
        return isset($rs["bindvalue"]) ? $rs["bindvalue"] : "";
    }

    public function fetchUidByValue($value, $app)
    {
        $rs = $this->fetch(array("select" => "uid", "condition" => sprintf("bindvalue = '%s' AND app ='%s'", $value, $app)));
        return !empty($rs["uid"]) ? intval($rs["uid"]) : 0;
    }

    public function getIsBinding($uid, $app)
    {
        return $this->countByAttributes(array("uid" => intval($uid), "app" => $app)) != 0;
    }
}
