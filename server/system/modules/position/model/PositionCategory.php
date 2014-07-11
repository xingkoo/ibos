<?php

class PositionCategory extends ICModel
{
    public static function model($className = "PositionCategory")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{position_category}}";
    }

    public function afterDelete()
    {
        CacheUtil::update("PositionCategory");
        parent::afterDelete();
    }

    public function afterSave()
    {
        CacheUtil::update("PositionCategory");
        parent::afterSave();
    }
}
