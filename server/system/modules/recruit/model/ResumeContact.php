<?php

class ResumeContact extends ICModel
{
    public static function model($className = "ResumeContact")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{resume_contact}}";
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
        $fields = "rd.realname,rc.contactid,rc.resumeid,rc.input,rc.inputtime,rc.contact,rc.purpose,rc.detail";
        $sql = "SELECT $fields FROM {{resume_contact}} rc LEFT JOIN {{resume_detail}} rd ON rc.resumeid=rd.resumeid ";

        if (!empty($condition)) {
            $sql .= " WHERE " . $condition;
        }

        $sql .= " ORDER BY rc.inputtime DESC LIMIT $offset,$limit";
        $records = $this->getDbConnection()->createCommand($sql)->queryAll();
        return array("pagination" => $pagination, "data" => $records);
    }

    public function countBySearchCondition($condition)
    {
        $whereCondition = " WHERE " . $condition;
        $sql = "SELECT COUNT(rc.resumeid) AS number FROM {{resume_contact}} rc LEFT JOIN {{resume_detail}} rd ON rc.resumeid=rd.resumeid $whereCondition";
        $record = $this->getDbConnection()->createCommand($sql)->queryAll();
        return $record[0]["number"];
    }

    public function fetchResumeidByContactid($contactid)
    {
        $contact = $this->fetchByPk($contactid);
        return $contact["resumeid"];
    }
}
