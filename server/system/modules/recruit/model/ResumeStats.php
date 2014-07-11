<?php

class ResumeStats extends ICModel
{
    public static function model($className = "ResumeStats")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{resume_statistics}}";
    }

    public function fetchAllByTime($start, $end)
    {
        return $this->fetchAll(array("select" => "*", "condition" => sprintf("datetime BETWEEN %d AND %d", $start, $end), "order" => "datetime ASC"));
    }
}
