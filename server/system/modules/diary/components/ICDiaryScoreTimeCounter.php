<?php

class ICDiaryScoreTimeCounter extends ICDiaryTimeCounter
{
    public function getID()
    {
        return "score";
    }

    public function getCount()
    {
        static $return = array();

        if (empty($return)) {
            $time = $this->getTimeScope();

            foreach ($this->getUid() as $uid) {
                $list = DiaryStats::model()->fetchAllStatisticsByUid($uid, $time["start"], $time["end"]);
                $return[$uid]["list"] = $this->ReplenishingScore($list);
                $return[$uid]["name"] = User::model()->fetchRealnameByUid($uid);
            }
        }

        return $return;
    }

    protected function ReplenishingScore($list)
    {
        if (empty($list)) {
            return $list;
        }

        $dateScope = array_fill_keys($this->getDateScope(), "'-'");
        $diaryIds = ConvertUtil::getSubByKey($list, "diaryid");
        $timeList = Diary::model()->fetchAddTimeByDiaryId($diaryIds);
        $new = array();

        foreach ($timeList as $time) {
            $dayTime = date("Y-m-d", $time["addtime"]);
            $new[$dayTime] = $time["diaryid"];
        }

        $this->getLegalScore($dateScope, $new, $list);
        return $dateScope;
    }

    private function getLegalScore(&$dateScope, $new, $list)
    {
        foreach ($dateScope as $k => &$date) {
            if (!isset($new[$k])) {
                $date = 0;
            } else {
                $date = $list[$new[$k]]["integration"];
            }
        }
    }
}
