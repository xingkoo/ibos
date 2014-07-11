<?php

class VoteUtil
{
    public static function processVoteData($data)
    {
        if (!empty($data)) {
            $data["voteItemList"] = self::getPercentage($data["voteItemList"]);
            $data["vote"]["remainTime"] = self::getRemainTime($data["vote"]["starttime"], $data["vote"]["endtime"]);
        }

        return $data;
    }

    public static function checkVote($relatedModule, $relatedId, $uid = 0)
    {
        $result = false;
        $uid = (empty($uid) ? Ibos::app()->user->uid : $uid);
        $condition = "relatedmodule=:relatedmodule AND relatedid=:relatedid";
        $params = array(":relatedmodule" => $relatedModule, ":relatedid" => $relatedId);
        $vote = Vote::model()->fetch($condition, $params);

        if (!empty($vote)) {
            $voteid = $vote["voteid"];
            $voteItemList = VoteItem::model()->fetchAll("voteid=:voteid", array(":voteid" => $voteid));

            foreach ($voteItemList as $voteItem) {
                $itemid = $voteItem["itemid"];
                $itemCountList = VoteItemCount::model()->fetchAll("itemid=:itemid", array(":itemid" => $itemid));
                if (!empty($itemCountList) && (0 < count($itemCountList))) {
                    foreach ($itemCountList as $itemCount) {
                        if ($itemCount["uid"] == $uid) {
                            $result = true;
                            break;
                        }
                    }
                }
            }
        }

        return $result;
    }

    private static function getPercentage($voteItemList)
    {
        $numberCount = 0;

        foreach ($voteItemList as $index => $voteItem) {
            $voteItemList[$index]["picpath"] = FileUtil::fileName($voteItem["picpath"]);
            $numberCount += $voteItem["number"];
        }

        $length = count($voteItemList);

        if ($numberCount == 0) {
            for ($i = 0; $i < $length; $i++) {
                $voteItemList[$i]["percentage"] = "0%";
                $voteItemList[$i]["color_style"] = "";
            }
        } else {
            $percentageCount = 0;
            $count = 0;
            $colors = array("#91CE31", "#EE8C0C", "#E26F50", "#3497DB");
            $colorLength = count($colors);

            for ($i = 0; $i < $length; $i++) {
                $percentage = round(($voteItemList[$i]["number"] / $numberCount) * 100);
                $voteItemList[$i]["percentage"] = $percentage;
                $percentageCount = $percentageCount + $voteItemList[$i]["percentage"];
                $voteItemList[$i]["color_style"] = $colors[$count];
                $count++;

                if ($colorLength <= $count) {
                    $count = 0;
                }
            }

            if ($percentageCount != 100) {
                $voteItemList[0]["percentage"] = $voteItemList[0]["percentage"] + 1;
            }

            for ($i = 0; $i < $length; $i++) {
                $voteItemList[$i]["percentage"] = $voteItemList[$i]["percentage"] . "%";
            }
        }

        return $voteItemList;
    }

    public static function setEndTime($startTime, $dayNumber)
    {
        return $startTime + ($dayNumber * 24 * 60 * 60);
    }

    public static function getRemainTime($startTime, $endTime)
    {
        $remainTime = $endTime - time();

        if ($endTime == 0) {
            return 0;
        } else {
            if (($startTime < $endTime) && (0 < $remainTime)) {
                $minuteCount = floor($remainTime / 60);
                $dayNumber = floor($minuteCount / (60 * 24));
                $remainHour = floor(($minuteCount - ($dayNumber * 24 * 60)) / 60);
                $remainMinute = floor(($minuteCount - ($dayNumber * 24 * 60)) % 60);
                $remainSecond = round((($remainTime / 60) - $minuteCount) * 60);
                $remainTime = array("day" => $dayNumber, "hour" => $remainHour, "minute" => $remainMinute, "second" => $remainSecond);
                return $remainTime;
            } else {
                if (($startTime < $endTime) && ($remainTime <= 0)) {
                    return -1;
                }
            }
        }
    }

    public static function processDateTime($dateTime)
    {
        $resultTime = 0;

        if ($dateTime == "One week") {
            $resultTime = time() + (7 * 24 * 60 * 60);
        } elseif ($dateTime == "One month") {
            $resultTime = time() + (30 * 24 * 60 * 60);
        } elseif ($dateTime == "half of a year") {
            $resultTime = time() + (6 * 30 * 24 * 60 * 60);
        } elseif ($dateTime == "One year") {
            $resultTime = time() + (365 * 24 * 60 * 60);
        }

        return $resultTime;
    }

    public static function getEndTime($endtime, $selectEndIime)
    {
        $result = "";
        $selectEndTime = trim($selectEndIime);
        if (isset($endtime) && ($selectEndTime == "Custom")) {
            $result = (strtotime($endtime) + (24 * 60 * 60)) - 1;
        } elseif ($selectEndTime !== "Custom") {
            $result = self::processDateTime($selectEndTime);
        }

        return $result;
    }
}
