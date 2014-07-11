<?php

class ArticlePicture extends ICModel
{
    public static function model($className = "ArticlePicture")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{article_picture}}";
    }

    public function updateArticleidAndSortByPk($pk, $articleid, $sort)
    {
        return $this->updateByPk($pk, array("articleid" => $articleid, "sort" => $sort));
    }

    public function deleteAllByPictureIds($ids)
    {
        return $this->deleteAll("FIND_IN_SET(picid,'$ids')");
    }

    public function deleteAllByArticleIds($ids)
    {
        return $this->deleteAll("articleid IN ($ids)");
    }

    public function fetchPictureByArticleId($articleid)
    {
        return $this->fetchAll("articleid='$articleid' ORDER BY sort Desc");
    }
}
