<?php

class AppCategory extends ICModel
{
    public static function model($className = "AppCategory")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{app_category}}";
    }

    public function fetchAllSort()
    {
        $category = $this->fetchAllSortByPk("catid", array("order" => "`sort` ASC"));
        return $category;
    }
}
