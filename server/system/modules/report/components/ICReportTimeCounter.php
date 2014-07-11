<?php

class ICReportTimeCounter extends ICCounter
{
    /**
     * 统计的用户数组
     * @var array 
     */
    private $_uids;
    /**
     * 统计的时间范围
     * @var array 
     */
    private $_timeScope;
    /**
     * 统计的类型id（1周、2月、3季、4年）
     * @var integer 
     */
    private $_typeid = 1;

    public function getID()
    {
        return false;
    }

    public function getCount()
    {
        return false;
    }

    public function getUid()
    {
        return $this->_uids;
    }

    public function setUid($uid)
    {
        $this->_uids = $uid;
    }

    public function getTypeid()
    {
        return $this->_typeid;
    }

    public function setTypeid($typeid)
    {
        $this->_typeid = $typeid;
    }

    public function setTimeScope($timeScope)
    {
        $this->_timeScope = $timeScope;
    }

    public function getTimeScope()
    {
        return $this->_timeScope;
    }

    public function getDays()
    {
        $scope = $this->getTimeScope();
        return DateTimeUtil::getDays($scope["start"], $scope["end"]);
    }

    public function getDateScope()
    {
        static $return = array();

        if (empty($return)) {
            $scope = $this->getTimeScope();
            $return = $this->getFormatDate($scope["start"], $scope["end"]);
        }

        return $return;
    }

    public function getFormatDate($start, $end)
    {
        $typeid = $this->getTypeid();

        if ($typeid == 1) {
            return $this->formateDateByWeek($start, $end);
        } elseif ($typeid == 2) {
            return $this->formateDateByMoon($start, $end);
        } elseif ($typeid == 3) {
            return $this->formateDateBySeason($start, $end);
        } elseif ($typeid == 4) {
            return $this->formateDateByYear($start, $end);
        }
    }

    public function formateDateByWeek($start, $end)
    {
        $return = array();
        $st = strtotime("Monday 00:00:00 this week", $start);
        $et = strtotime("Sunday 23:59:59 this week", $end);
        $days = DateTimeUtil::getDays($st, $et);

        for ($i = 0; $i <= $days; $i += 7) {
            $k = $i + 6;
            $sd = date("Y-m-d", strtotime("+$i day", $st));
            $ed = date("Y-m-d", strtotime("+$k day", $st));
            $return[$sd . ":" . $ed] = $sd . "至" . $ed . "周报";
        }

        return $return;
    }

    public function formateDateByMoon($start, $end)
    {
        $return = array();
        $st = date("Y-m-d", $start);
        $et = date("Y-m-d", $end);
        $dates = DateTimeUtil::getDiffDate($st, $et);
        $moons = ($dates["y"] * 12) + $dates["m"];

        if (0 < $dates["d"]) {
            $moons += 1;
        }

        for ($i = 0; $i < $moons; $i++) {
            $sd = date("Y-m", strtotime("+$i month $st")) . "-1";
            $ed = date("Y-m-d", strtotime("+1 month -1 day $sd"));
            $return[$sd . ":" . $ed] = date("Y-m", strtotime("+$i month $st")) . "月报";
        }

        return $return;
    }

    public function formateDateBySeason($start, $end)
    {
        $return = array();
        $st = date("Y-m-d", $start);
        $et = date("Y-m-d", $end);
        $dates = DateTimeUtil::getDiffDate($st, $et);
        $moons = ($dates["y"] * 12) + $dates["m"];

        if (0 < ($dates["d"] + ($dates["m"] * 12))) {
            $moons += 1;
        }

        for ($i = 0; $i < $moons; $i += 3) {
            $time = strtotime("+$i month $st");
            $season = DateTimeUtil::getSeasonByMonty(date("m", $time));
            $sd = date("Y-m", strtotime("+$i month $st")) . "-1";
            $ed = date("Y-m-d", strtotime("+3 month -1 day $sd"));
            $return[$sd . ":" . $ed] = date("Y", $time) . "年第" . $season . "季报";
        }

        return $return;
    }

    public function formateDateByYear($start, $end)
    {
        $return = array();
        $st = date("Y-m-d", $start);
        $et = date("Y-m-d", $end);
        $dates = DateTimeUtil::getDiffDate($st, $et);
        $years = $dates["y"];

        if (0 < ($dates["d"] + ($dates["m"] * 12))) {
            $years += 1;
        }

        for ($i = 0; $i < $years; $i++) {
            $sd = date("Y-m", strtotime("+$i year $st")) . "-1";
            $ed = date("Y-m-d", strtotime("+1 year -1 day $sd"));
            $return[$sd . ":" . $ed] = date("Y", strtotime("+$i year $st")) . "年报";
        }

        return $return;
    }
}
