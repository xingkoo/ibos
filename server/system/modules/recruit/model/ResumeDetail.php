<?php

class ResumeDetail extends ICModel
{
    public static function model($className = "ResumeDetail")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{resume_detail}}";
    }

    public function fetchEmailsByResumeids($resumeids, $join = ";")
    {
        $emails = "";
        $select = "email";
        $condition = "resumeid IN ($resumeids)";
        $data = $this->fetchAll(array("select" => $select, "condition" => $condition));

        if (0 < count($data)) {
            foreach ($data as $record) {
                if (!empty($record["email"])) {
                    $emails .= $record["email"] . $join;
                }
            }
        }

        return $emails;
    }

    public function fetchPKAndRealnameByKeyword($keyword)
    {
        $condition = "realname LIKE '%$keyword%'";
        $records = $this->fetchAll(array(
            "select"    => array("resumeid", "realname"),
            "condition" => $condition
        ));
        return $records;
    }

    public function fetchRealnameByResumeid($resumeid)
    {
        $record = $this->fetch(array(
            "select"    => array("realname"),
            "condition" => "resumeid=:resumeid",
            "params"    => array(":resumeid" => $resumeid)
        ));

        if (0 < count($record)) {
            return $record["realname"];
        } else {
            return null;
        }
    }

    public function fetchResumeidByRealname($realname)
    {
        $record = $this->fetch(array(
            "select"    => array("resumeid"),
            "condition" => "realname = :realname",
            "params"    => array(":realname" => $realname)
        ));

        if (0 < count($record)) {
            return $record["resumeid"];
        } else {
            return null;
        }
    }

    public function fetchAllRealnames()
    {
        $fields = "r.resumeid,rd.detailid,rd.realname,rd.positionid,rd.gender,r.status";
        $sql = "SELECT $fields FROM {{resume}} r LEFT JOIN {{resume_detail}} rd ON r.resumeid=rd.resumeid ORDER BY r.entrytime DESC";
        $resumes = $this->getDbConnection()->createCommand($sql)->queryAll();
        $realnames = ConvertUtil::getSubByKey($resumes, "realname");
        return $realnames;
    }

    public function fetchFieldByRerumeids($resumeids, $field)
    {
        $resumeids = (is_array($resumeids) ? implode(",", $resumeids) : $resumeids);
        $return = $this->fetchAll(array("select" => $field, "condition" => "FIND_IN_SET(`resumeid`, '$resumeids')"));
        return ConvertUtil::getSubByKey($return, $field);
    }
}
