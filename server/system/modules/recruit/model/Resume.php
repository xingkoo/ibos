<?php

class Resume extends ICModel
{
    public static function model($className = "Resume")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{resume}}";
    }

    public function fetchAllByPage($conditions = "", $pageSize = null)
    {
        $pages = new CPagination($this->countByCondition($conditions));
        $pageSize = (is_null($pageSize) ? Yii::app()->params["basePerPage"] : $pageSize);
        $pages->setPageSize(intval($pageSize));
        $criteria = new CDbCriteria(array("limit" => $pages->getLimit(), "offset" => $pages->getOffset()));
        $pages->applyLimit($criteria);
        $fields = "r.resumeid,rd.detailid,rd.realname,rd.positionid,rd.gender,rd.birthday,rd.education,rd.workyears,r.flag,r.status";
        $sql = "SELECT $fields FROM {{resume}} r LEFT JOIN {{resume_detail}} rd ON r.resumeid=rd.resumeid ";

        if (!empty($conditions)) {
            $sql .= " WHERE " . $conditions;
        }

        $offset = $pages->getOffset();
        $limit = $pages->getLimit();
        $sql .= " ORDER BY r.entrytime DESC LIMIT $offset,$limit";
        $records = $this->getDbConnection()->createCommand($sql)->queryAll();
        return array("pages" => $pages, "datas" => $records);
    }

    public function fetchPrevAndNextPKByPK($resumeid)
    {
        $nextPK = $prevPK = 0;
        $sql = "SELECT resumeid FROM {{resume}} WHERE resumeid<$resumeid ORDER BY resumeid ASC LIMIT 1";
        $nextRecord = $this->getDbConnection()->createCommand($sql)->queryAll();

        if (!empty($nextRecord)) {
            $nextPK = $nextRecord[0]["resumeid"];
        }

        $sql2 = "SELECT resumeid FROM {{resume}} WHERE resumeid>$resumeid ORDER BY resumeid DESC LIMIT 1";
        $prevRecord = $this->getDbConnection()->createCommand($sql2)->queryAll();

        if (!empty($prevRecord)) {
            $prevPK = $prevRecord[0]["resumeid"];
        }

        return array("prevPK" => $prevPK, "nextPK" => $nextPK);
    }

    public function updateFieldValueByPK($PK, $field, $value)
    {
        return $this->modify($PK, array($field => $value));
    }

    public function countByCondition($condition = "")
    {
        if (!empty($condition)) {
            $whereCondition = " WHERE " . $condition;
            $sql = "SELECT COUNT(r.resumeid) AS number FROM {{resume}} r LEFT JOIN {{resume_detail}} rd ON r.resumeid=rd.resumeid $whereCondition";
            $record = $this->getDbConnection()->createCommand($sql)->queryAll();
            return $record[0]["number"];
        } else {
            return $this->count();
        }
    }

    public function fetchStatusByResumeid($resumeid)
    {
        $record = $this->fetch(array(
            "select"    => array("status"),
            "condition" => "resumeid=:resumeid",
            "params"    => array(":resumeid" => $resumeid)
        ));

        if (0 < count($record)) {
            return $record["status"];
        } else {
            return "";
        }
    }

    public function countByStatus($status, $start, $end)
    {
        is_array($status) && ($status = implode(",", $status));
        return $this->getDbConnection()->createCommand()->select("count(resumeid)")->from($this->tableName())->where(sprintf("FIND_IN_SET(`status`,'%s') AND entrytime BETWEEN %d AND %d", $status, $start, $end))->queryScalar();
    }

    public function fetchAllByTime($start, $end)
    {
        $resumes = $this->getDbConnection()->createCommand()->select("resumeid")->from($this->tableName())->where(sprintf("entrytime BETWEEN %d AND %d", $start, $end))->queryAll();
        $resumeidArr = ConvertUtil::getSubByKey($resumes, "resumeid");
        return implode(",", $resumeidArr);
    }
}
