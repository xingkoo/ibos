<?php

class ResumeBgchecks extends ICModel
{
    public static function model($className = "ResumeBgchecks")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{resume_bgchecks}}";
    }

    public function fetchAllByPage($condition = "", $pageSize = 0)
    {
        $count = (empty($condition) ? $this->count() : $this->countBySearchCondition($condition));
        $pagination = new CPagination($count);
        $pageSize = (empty($pageSize) ? Yii::app()->params["basePerPage"] : $pageSize);
        $pagination->setPageSize($pageSize);
        $offset = $pagination->getOffset();
        $limit = $pagination->getLimit();
        $criteria = new CDbCriteria(array("limit" => $limit, "offset" => $offset));
        $pagination->applyLimit($criteria);
        $fields = "rd.realname,rb.checkid,rb.resumeid,rb.company,rb.position,rb.entrytime,rb.quittime";
        $sql = "SELECT $fields FROM {{resume_bgchecks}} rb LEFT JOIN {{resume_detail}} rd ON rb.resumeid=rd.resumeid ";

        if (!empty($condition)) {
            $sql .= " WHERE " . $condition;
        }

        $sql .= " LIMIT $offset,$limit";
        $records = $this->getDbConnection()->createCommand($sql)->queryAll();
        return array("pagination" => $pagination, "data" => $records);
    }

    public function countBySearchCondition($condition)
    {
        $whereCondition = " WHERE " . $condition;
        $sql = "SELECT COUNT(rb.checkid) AS number FROM {{resume_bgchecks}} rb LEFT JOIN {{resume_detail}} rd ON rb.resumeid=rd.resumeid $whereCondition";
        $record = $this->getDbConnection()->createCommand($sql)->queryAll();
        return $record[0]["number"];
    }

    public function fetchResumeidByCheckid($checkid)
    {
        $bgcheck = $this->fetchByPk($checkid);
        return $bgcheck["resumeid"];
    }
}
