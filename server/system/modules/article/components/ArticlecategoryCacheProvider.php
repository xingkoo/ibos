<?php

class ArticleCategoryCacheProvider extends CBehavior
{
    public function attach($owner)
    {
        $owner->attachEventHandler("onUpdateCache", array($this, "handleArticleCategory"));
    }

    public function handleArticleCategory($event)
    {
        $categorys = array();
        Yii::import("application.modules.article.model.ArticleCategory", true);
        $records = ArticleCategory::model()->findAll(array("order" => "sort ASC"));

        if (!empty($records)) {
            foreach ($records as $record) {
                $cat = $record->attributes;
                $categorys[$cat["catid"]] = $cat;
            }
        }

        Syscache::model()->modify("articlecategory", $categorys);
    }
}
