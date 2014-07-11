<?php

class FlowQueryTpl extends ICModel
{
    public static function model($className = "FlowQueryTpl")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{flow_query_tpl}}";
    }

    public function checkTplNameExists($tplName, $sid = 0)
    {
        $criteria = array("select" => "1", "condition" => "tplname = '$tplName'");

        if ($sid) {
            $criteria["condition"] .= " AND seqid != " . intval($sid);
        }

        $ret = $this->fetch($criteria);
        return $ret ? true : false;
    }

    public function fetchAllByFlowId($flowID)
    {
        return $this->fetchAllByAttributes(array("flowid" => intval($flowID)), array("select" => "seqid,tplname"));
    }

    public function fetchAllBySearch($flowID, $uID)
    {
        $criteria = array("condition" => sprintf("flowid = %d AND (uid=  '%d' OR uid = 0)", $flowID, $uID), "order" => "uid DESC,seqid");
        return $this->fetchAll($criteria);
    }
}
