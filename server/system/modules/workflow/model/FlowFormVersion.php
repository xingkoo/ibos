<?php

class FlowFormVersion extends ICModel
{
    public static function model($className = "FlowFormVersion")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{flow_form_version}}";
    }

    public function countByFormID($formID)
    {
        return $this->countByAttributes(array("formid" => intval($formID)));
    }

    public function getMaxMark($formID)
    {
        $criteria = array("select" => "mark", "condition" => "formid = " . intval($formID), "order" => "mark desc", "limit" => "1");
        $result = $this->fetch($criteria);
        return isset($result["mark"]) ? intval($result["mark"]) : 0;
    }

    public function fetchAllByFormId($formId)
    {
        $criteria = array("select" => "id,formid,time,mark", "condition" => "formid = " . intval($formId), "order" => "time desc");
        return $this->fetchAll($criteria);
    }
}
