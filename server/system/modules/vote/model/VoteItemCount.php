<?php

class VoteItemCount extends ICModel
{
    public static function model($className = "VoteItemCount")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{vote_item_count}}";
    }
}
