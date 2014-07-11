<?php

class ICVote
{
    public function clickVote($relatedModule, $relatedId, $voteItemids)
    {
        $result = 0;

        if (!VoteUtil::checkVote($relatedModule, $relatedId)) {
            $affectedRow = VoteItem::model()->updateNumber($voteItemids);

            if ($affectedRow) {
                $voteitemidArray = explode(",", rtrim($voteItemids, ","));

                foreach ($voteitemidArray as $voteitemid) {
                    $voteItemCount = new VoteItemCount();
                    $voteItemCount->itemid = $voteitemid;
                    $voteItemCount->uid = Ibos::app()->user->uid;
                    $voteItemCount->save();
                }

                $voteData = Vote::model()->fetchVote($relatedModule, $relatedId);
                $result = VoteUtil::processVoteData($voteData);
            }
        } else {
            $result = -1;
        }

        return $result;
    }

    public function getStatus($relatedModule, $relatedId, $voteData = "")
    {
        if (empty($voteData)) {
            $condition = "relatedmodule=:relatedmodule AND relatedid=:relatedid";
            $params = array(":relatedmodule" => $relatedModule, ":relatedid" => $relatedId);
            $vote = Vote::model()->fetch($condition, $params);
        } else {
            $vote = $voteData;
        }

        if ($vote["status"] == 0) {
            return $vote["status"];
        } elseif ($vote["status"] == 2) {
            return $vote["status"];
        } else {
            $remainTime = VoteUtil::getRemainTime($vote["starttime"], $vote["endtime"]);

            if ($remainTime == 0) {
                return 1;
            } elseif ($remainTime == -1) {
                $affectedRow = Vote::model()->updateByPk($vote["voteid"], array("status" => 2));

                if ($affectedRow) {
                    return 2;
                }
            } elseif (is_array($remainTime)) {
                return 1;
            }
        }
    }

    public static function getView($view)
    {
        $currentController = Yii::app()->getController();
        $basePath = "application.modules.vote.views.default.";
        $relatedModule = Ibos::getCurrentModuleName();
        $relatedId = EnvUtil::getRequest($relatedModule . "id");

        if ($view == "articleView") {
            $voteData = Vote::model()->fetchVote($relatedModule, $relatedId);
            $votes = VoteUtil::processVoteData($voteData);

            if (!empty($votes)) {
                $voteItemList = $votes["voteItemList"];
                $voteType = $voteItemList[0]["type"];

                if ($voteType == 1) {
                    $view = "articleTextView";
                } elseif ($voteType == 2) {
                    $view = "articleImageView";
                }

                $selectView = $basePath . $view;
                $votePeopleNumber = Vote::model()->fetchUserVoteCount($relatedModule, $relatedId);
                $userHasVote = VoteUtil::checkVote($relatedModule, $relatedId);
                $mothedName = "get" . ucfirst($relatedModule) . "Vote";
                $voteStatus = ICVotePlugManager::$mothedName()->getStatus($relatedModule, $relatedId, $votes["vote"]);
                $votes["vote"]["subject"] = StringUtil::cutStr($votes["vote"]["subject"], 60);
                $data = array("voteData" => $votes, "votePeopleNumber" => $votePeopleNumber, "userHasVote" => $userHasVote, "voteStatus" => $voteStatus, "attachUrl" => Yii::app()->setting->get("setting/attachurl"));

                if ($voteStatus == 2) {
                    $partView = null;
                } else {
                    $partView = $currentController->renderPartial($selectView, $data, true);
                }
            } else {
                $partView = null;
            }
        } elseif ($view == "articleAdd") {
            $selectView = $basePath . $view;
            $partView = $currentController->renderPartial($selectView, array("uploadConfig" => AttachUtil::getUploadConfig()), true);
        } elseif ($view == "articleEdit") {
            $selectView = $basePath . $view;
            $voteData = Vote::model()->fetchVote($relatedModule, $relatedId);
            if (!empty($voteData) && isset($voteData["voteItemList"])) {
                foreach ($voteData["voteItemList"] as $k => $voteItem) {
                    $voteData["voteItemList"][$k]["thumburl"] = FileUtil::fileName($voteItem["picpath"]);
                }
            }

            $data = array("voteData" => $voteData, "uploadConfig" => AttachUtil::getUploadConfig());
            $partView = $currentController->renderPartial($selectView, $data, true);
        }

        return $partView;
    }
}
