<?php

class Contact extends ICModel
{
    public static function model($className = "Contact")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{contact}}";
    }

    public function checkIsConstant($uid, $cuid)
    {
        $record = $this->fetch(array(
            "condition" => "uid = :uid AND cuid = :cuid",
            "params"    => array(":uid" => $uid, "cuid" => $cuid)
        ));

        if (empty($record)) {
            return false;
        }

        return true;
    }

    public function fetchAllConstantByUid($uid)
    {
        $record = $this->fetchAll(array(
            "condition" => "uid = :uid",
            "params"    => array(":uid" => $uid)
        ));
        return ConvertUtil::getSubByKey($record, "cuid");
    }

    public function addConstant($uid, $cuid)
    {
        $this->add(array("uid" => $uid, "cuid" => $cuid));
    }

    public function deleteConstant($uid, $cuid)
    {
        $this->deleteAll(array(
            "condition" => "uid = :uid AND cuid = :cuid",
            "params"    => array(":uid" => $uid, ":cuid" => $cuid)
        ));
    }
}
