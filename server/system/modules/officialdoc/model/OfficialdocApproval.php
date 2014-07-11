<?php

class OfficialdocApproval extends ICModel
{
    public static function model($className = "OfficialdocApproval")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{doc_approval}}";
    }

    public function recordStep($docid, $uid)
    {
        $docApproval = $this->fetchLastStep($docid);

        if (empty($docApproval)) {
            $step = 0;
        } else {
            $step = $docApproval["step"] + 1;
        }

        return $this->add(array("docid" => $docid, "uid" => $uid, "step" => $step));
    }

    public function fetchLastStep($docId)
    {
        $record = $this->fetch(array("condition" => "docid=$docId", "order" => "step DESC"));
        return $record;
    }

    public function deleteByDocIds($docids)
    {
        $docids = (is_array($docids) ? implode(",", $docids) : $docids);
        return $this->deleteAll("FIND_IN_SET(docid,'$docids')");
    }

    public function fetchAllGroupByDocId()
    {
        $result = array();
        $records = $this->fetchAll("step > 0");

        if (!empty($records)) {
            foreach ($records as $record) {
                $docId = $record["docid"];
                $result[$docId][] = $record;
            }
        }

        return $result;
    }
}
