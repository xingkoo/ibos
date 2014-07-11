<?php

class FeedData extends ICModel
{
    public static function model($className = "FeedData")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{feed_data}}";
    }
}
