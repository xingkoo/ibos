<?php

class MessageList extends ICModel
{
    public static function model($className = "MessageList")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{message_list}}";
    }
}
