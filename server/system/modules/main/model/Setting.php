<?php

class Setting extends ICModel
{
    public static function model($className = "Setting")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{setting}}";
    }

    public function fetchSettingValueByKey($sKey)
    {
        $record = $this->fetch("skey='$sKey'");

        if (!empty($record)) {
            return $record["svalue"];
        }

        return null;
    }

    public function fetchSettingValueByKeys($sKeys, $autoUnserialize = false)
    {
        $return = array();
        $record = $this->fetchAll("FIND_IN_SET(skey,'$sKeys')");

        if (!empty($record)) {
            foreach ($record as $value) {
                $return[$value["skey"]] = ($autoUnserialize ? (array) unserialize($value["svalue"]) : $value["svalue"]);
            }
        }

        return $return;
    }

    public function updateSettingValueByKey($sKey, $sValue)
    {
        $sValue = (is_array($sValue) ? serialize($sValue) : $sValue);
        $updateResult = $this->modify($sKey, array("svalue" => $sValue));
        return (bool) $updateResult;
    }

    public function fetchAllSetting()
    {
        $setting = array();
        $records = $this->findAll();

        foreach ($records as $record) {
            $value = $record->attributes;
            $isSerialized = ($value["svalue"] == serialize(false)) || (@unserialize($value["svalue"]) !== false);
            $setting[$value["skey"]] = ($isSerialized ? unserialize($value["svalue"]) : $value["svalue"]);
        }

        return $setting;
    }
}
