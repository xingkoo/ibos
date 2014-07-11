<?php

class ICReportScoreTimeCounter extends ICReportTimeCounter
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
            $typeid = $this->getTypeid();

            foreach ($this->getUid() as $uid) {
                $list = ReportStats::model()->fetchAllStatisticsByUid($uid, $time["start"], $time["end"], $typeid);
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

        $dateScopeTmp = $this->getDateScope();
        $dateScope = array_flip($dateScopeTmp);
        $repIds = ConvertUtil::getSubByKey($list, "repid");
        $timeList = Report::model()->fetchAddTimeByRepId($repIds);
        $new = array();

        foreach ($timeList as $time) {
            $dayTime = date("Y-m-d", $time["addtime"]);
            $new[$dayTime] = $time["repid"];
        }

        $ret = $this->getLegalScore($dateScope, $new, $list);
        return $ret;
    }

    private function getLegalScore($dateScope, $new, $list)
    {
        $newDates = array_flip($new);

        foreach ($dateScope as $k => $date) {
            list($st, $et) = explode(":", $date);

            foreach ($newDates as $repid => $newDate) {
                if ((strtotime($st) < strtotime($newDate)) && (strtotime($newDate) < strtotime($et))) {
                    $dateScope[$k] = $list[$repid]["integration"];
                    break;
                }

                $dateScope[$k] = 0;
            }
        }

        return $dateScope;
    }
}
