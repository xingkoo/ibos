<?php

class ArticleBack extends ICModel
{
    public static function model($className = "ArticleBack")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{article_back}}";
    }

    public function addBack($artId, $uid, $reason, $time = TIMESTAMP)
    {
        return $this->add(array("articleid" => $artId, "uid" => $uid, "reason" => $reason, "time" => $time));
    }

    public function fetchAllBackArtId()
    {
        $record = $this->fetchAll();
        return ConvertUtil::getSubByKey($record, "articleid");
    }
}
