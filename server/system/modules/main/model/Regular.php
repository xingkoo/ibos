<?php

class Regular extends ICModel
{
    public static function model($className = "Regular")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{regular}}";
    }

    public function fetchFieldRuleByType($type)
    {
        $regular = $this->findByAttributes(array("type" => $type));
        return $regular;
    }

    public function fetchAllFieldRuleType()
    {
        $allFieldRule = $this->fetchAll(array("select" => "type"));
        $allFieldRuletype = ConvertUtil::getSubByKey($allFieldRule, "type");
        return $allFieldRuletype;
    }
}
