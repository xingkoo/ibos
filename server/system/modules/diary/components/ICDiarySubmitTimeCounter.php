<?php

class ICDiarySubmitTimeCounter extends ICDiaryTimeCounter
{
    public function getID()
    {
        return "submit";
    }

    public function getCount()
    {
        static $return = array();

        if (empty($return)) {
            $time = $this->getTimeScope();

            foreach ($this->getUid() as $uid) {
                $list = Diary::model()->fetchAddTimeByUid($uid, $time["start"], $time["end"]);
                $return[$uid]["list"] = $this->ReplenishingDate($list);
                $return[$uid]["name"] = User::model()->fetchRealnameByUid($uid);
            }
        }

        return $return;
    }

    protected function ReplenishingDate($list = array())
    {
        if (empty($list)) {
            return $list;
        }

        $dateScope = array_fill_keys($this->getDateScope(), "'-'");

        foreach ($list as $time) {
            $dayTime = date("Y-m-d", $time["diarytime"]);
            $dateScope[$dayTime] = $this->getLegalDate($time["addtime"], $time["diarytime"]);
        }

        return $dateScope;
    }

    protected function getLegalDate($addTime, $diaryTime)
    {
        if (86400 < ($addTime - $diaryTime)) {
            $date = 0;
        } else {
            $date = date("G.i", $addTime);
        }

        return $date;
    }
}
