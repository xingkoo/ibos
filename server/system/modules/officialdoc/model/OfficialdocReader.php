<?php

class OfficialdocReader extends ICModel
{
    public static function model($className = "OfficialdocReader")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{doc_reader}}";
    }

    public function checkIsRead($docid, $uid)
    {
        $result = false;
        $readerInfo = $this->fetch("docid=:docid AND uid=:uid", array(":docid" => $docid, ":uid" => $uid));

        if (!empty($readerInfo)) {
            $result = true;
        }

        return $result;
    }

    public function fetchReadArtIdsByUid($uid)
    {
        $record = $this->fetchAll("uid = $uid");
        $readDocIds = ConvertUtil::getSubByKey($record, "docid");
        return $readDocIds;
    }

    public function fetchSignArtIdsByUid($uid)
    {
        $record = $this->fetchAll("uid = $uid AND issign = 1");
        $signedDocIds = ConvertUtil::getSubByKey($record, "docid");
        return $signedDocIds;
    }

    public function addReader($docid, $uid)
    {
        if ($this->checkIsRead($docid, $uid) == false) {
            $reader = array("docid" => $docid, "uid" => $uid, "addtime" => TIMESTAMP);
            return OfficialdocReader::model()->add($reader);
        }
    }

    public function fetchDocidsByUid($uid)
    {
        $result = array();
        $readerList = $this->fetchAll("uid=:uid AND issign='1'", array(":uid" => $uid));

        if (!empty($readerList)) {
            foreach ($readerList as $reader) {
                $result[$reader["readerid"]] = $reader["docid"];
            }
        }

        return $result;
    }

    public function updateSignByDocid($docid, $uid)
    {
        $attributes = array("issign" => 1, "signtime" => TIMESTAMP);
        $condition = "docid=:docid AND uid=:uid";
        $params = array(":docid" => $docid, ":uid" => $uid);
        return $this->updateAll($attributes, $condition, $params);
    }

    public function fetchSignInfo($docid, $uid)
    {
        $record = $this->fetch(array(
            "condition" => "docid=:docid AND uid=:uid",
            "params"    => array(":docid" => $docid, ":uid" => $uid)
        ));
        return $record;
    }

    public function fetchSignByDocid($docid, $uid)
    {
        $record = $this->fetchSignInfo($docid, $uid);

        if (!empty($record)) {
            return $record["issign"];
        }

        return 0;
    }

    public function fetchSignedByDocId($docId)
    {
        $ret = $this->fetchAll(array(
            "condition" => "docid=:docid AND issign = :issign",
            "params"    => array(":docid" => $docId, ":issign" => 1)
        ));
        return $ret;
    }

    public function fetchSignedUidsByDocId($docId)
    {
        $ret = $this->fetchSignedByDocId($docId);
        $signedUids = ConvertUtil::getSubByKey($ret, "uid");
        return $signedUids;
    }

    public function deleteReaderByDocIds($docids)
    {
        return $this->deleteAll("FIND_IN_SET(docid,'$docids')");
    }
}
