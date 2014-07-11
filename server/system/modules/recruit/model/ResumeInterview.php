<?php

class ResumeInterview extends ICModel
{
    public static function model($className = "ResumeInterview")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{resume_interview}}";
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
        $fields = "rd.realname,ri.*";
        $sql = "SELECT $fields FROM {{resume_interview}} ri LEFT JOIN {{resume_detail}} rd ON ri.resumeid=rd.resumeid ";

        if (!empty($condition)) {
            $sql .= " WHERE " . $condition;
        }

        $sql .= " ORDER BY ri.interviewtime DESC LIMIT $offset,$limit";
        $records = $this->getDbConnection()->createCommand($sql)->queryAll();
        return array("pagination" => $pagination, "data" => $records);
    }

    public function countBySearchCondition($condition)
    {
        $whereCondition = " WHERE " . $condition;
        $sql = "SELECT COUNT(ri.resumeid) AS number FROM {{resume_interview}} ri LEFT JOIN {{resume_detail}} rd ON ri.resumeid=rd.resumeid $whereCondition";
        $record = $this->getDbConnection()->createCommand($sql)->queryAll();
        return $record[0]["number"];
    }

    public function fetchResumeidByInterviewid($interviewid)
    {
        $interview = $this->fetchByPk($interviewid);
        return $interview["resumeid"];
    }
}
