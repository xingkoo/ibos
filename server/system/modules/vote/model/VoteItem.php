<?php

class VoteItem extends ICModel
{
    public static function model($className = "VoteItem")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{vote_item}}";
    }

    public function updateNumber($itemids)
    {
        $result = 0;

        if (is_numeric($itemids)) {
            $voteItem = $this->findByPk($itemids);
            $result = $this->updateByPk($voteItem["itemid"], array("number" => $voteItem["number"] + 1));
        } elseif (is_array($itemids)) {
            foreach ($itemids as $itemid) {
                $voteItem = $this->findByPk($itemid);
                $result = $this->updateByPk($itemid, array("number" => $voteItem["number"] + 1));
            }
        } else {
            $itemids = explode(",", rtrim($itemids, ","));

            foreach ($itemids as $itemid) {
                $voteItem = $this->findByPk($itemid);
                $result = $this->updateByPk($itemid, array("number" => $voteItem["number"] + 1));
            }
        }

        return $result;
    }
}
