<?php

class ICArticleVote extends ICVote
{
    public function addVote($voteData, $voteItemList)
    {
        $vote = new Vote();

        foreach ($vote->attributes as $field => $value) {
            if (isset($voteData[$field])) {
                $vote->$field = $voteData[$field];
            }
        }

        $vote->save();
        $voteid = Yii::app()->db->getLastInsertID();

        for ($i = 0; $i < count($voteItemList); $i++) {
            $voteItem = new VoteItem();
            $voteItem->voteid = $voteid;
            $voteItem->content = $voteItemList[$i];
            $voteItem->number = 0;
            $voteItem->type = $voteData["voteItemType"];
            $voteItem->save();
        }
    }
}
