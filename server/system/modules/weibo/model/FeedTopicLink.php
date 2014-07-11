<?php

class FeedTopicLink extends ICModel
{
    public static function model($className = "FeedTopicLink")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{feed_topic_link}}";
    }
}
