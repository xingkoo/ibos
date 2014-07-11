<?php

class AttachmentUnused extends ICModel
{
    public static function model($className = "AttachmentUnused")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{attachment_unused}}";
    }
}
