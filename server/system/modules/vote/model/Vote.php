<?php

class Vote extends ICModel
{
    public static function model($className = "Vote")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{vote}}";
    }

    public function fetchVote($relatedModule, $relatedId)
    {
        $result = array();
        $condition = "relatedmodule=:relatedmodule AND relatedid=:relatedid";
        $params = array(":relatedmodule" => $relatedModule, ":relatedid" => $relatedId);
        $vote = $this->fetch($condition, $params);

        if (!empty($vote)) {
            $voteid = $vote["voteid"];
            $voteItemList = VoteItem::model()->fetchAll("voteid=:voteid", array(":voteid" => $voteid));
            $result["voteItemList"] = $voteItemList;
            $result["vote"] = $vote;
            $result["vote"]["type"] = $result["voteItemList"][0]["type"];
        }

        return $result;
    }

    public function fetchUserVoteCount($relatedModule, $relatedId)
    {
        $condition = "relatedmodule=:relatedmodule AND relatedid=:relatedid";
        $params = array(":relatedmodule" => $relatedModule, ":relatedid" => $relatedId);
        $vote = $this->fetch($condition, $params);
        $voteid = $vote["voteid"];
        $voteItemList = VoteItem::model()->fetchAll("voteid=:voteid", array(":voteid" => $voteid));
        $uidArray = array();

        foreach ($voteItemList as $voteItem) {
            $itemid = $voteItem["itemid"];
            $ItemCountList = VoteItemCount::model()->fetchAll("itemid=:itemid", array(":itemid" => $itemid));

            if (!empty($ItemCountList)) {
                foreach ($ItemCountList as $itemCount) {
                    $uid = $itemCount["uid"];
                    $uidArray[] = $uid;
                }
            }
        }

        $result = count(array_unique($uidArray));
        return $result;
    }

    public function deleteAllByRelationIdsAndModule($relatedids, $relatedModule)
    {
        $relatedidArr = explode(",", $relatedids);

        foreach ($relatedidArr as $relatedid) {
            $vote = $this->fetch(array(
                "select"    => array("voteid"),
                "condition" => "relatedid=:relatedid AND relatedmodule=:relatedmodule",
                "params"    => array(":relatedid" => $relatedid, ":relatedmodule" => $relatedModule)
            ));

            if (!empty($vote)) {
                $voteId = $vote["voteid"];
                $voteItemList = VoteItem::model()->fetchAll("voteid=:voteid", array(":voteid" => $voteId));

                if (!empty($voteItemList)) {
                    $voteItemIds = "";

                    foreach ($voteItemList as $voteitem) {
                        $voteItemIds .= $voteitem["itemid"] . ",";
                    }

                    $voteitemids = trim($voteItemIds, ",");
                    VoteItemCount::model()->deleteAll("itemid IN($voteitemids)");
                    VoteItem::model()->deleteAll("itemid IN($voteitemids)");
                }
            }
        }

        return $this->deleteAll("relatedmodule='$relatedModule' AND relatedid IN($relatedids)");
    }
}
