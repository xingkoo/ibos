<?php

class OfficialdocBack extends ICModel
{
    public static function model($className = "OfficialdocBack")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{doc_back}}";
    }

    public function addBack($docId, $uid, $reason, $time = TIMESTAMP)
    {
        return $this->add(array("docid" => $docId, "uid" => $uid, "reason" => $reason, "time" => $time));
    }

    public function fetchAllBackDocId()
    {
        $record = $this->fetchAll();
        return ConvertUtil::getSubByKey($record, "docid");
    }

    public function deleteByDocIds($docids)
    {
        $docids = (is_array($docids) ? implode(",", $docids) : $docids);
        return $this->deleteAll("FIND_IN_SET(docid,'$docids')");
    }
}
