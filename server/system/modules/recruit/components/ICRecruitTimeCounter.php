<?php

class ICRecruitTimeCounter extends ICCounter
{
    /**
     * 统计的类型(日、月、周)
     * @var string 
     */
    private $_type = "day";
    /**
     * 统计的时间范围
     * @var array 
     */
    private $_timeScope;
    /**
     * 选择的时间(本周、上周、本月、上月)
     * @var string 
     */
    private $_timestr;

    public function setType($type)
    {
        $this->_type = $type;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function setTimestr($timestr)
    {
        $this->_timestr = $timestr;
    }

    public function getTimestr()
    {
        return $this->_timestr;
    }

    public function getID()
    {
        return false;
    }

    public function getCount()
    {
        return false;
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
        $type = $this->getType();

        if ($type == "week") {
            return $this->formateDateByWeek($start, $end);
        } elseif ($type == "month") {
            return $this->formateDateByMoon($start, $end);
        } else {
            return DateTimeUtil::getFormatDate($start, $end, "Y-m-d");
        }
    }

    public function formateDateByWeek($start, $end)
    {
        $return = array();
        $sDate = date("Y-m-d", $start);
        $eDate = date("Y-m-d", $end);
        $st = strtotime("Monday 00:00:00 this week", $start);
        $days = DateTimeUtil::getDays($start, $end);

        for ($i = 0; $i < $days; $i += 7) {
            $k = $i + 6;
            $sd = date("Y-m-d", strtotime("+$i day", $st));
            $ed = date("Y-m-d", strtotime("+$k day", $st));

            if ($i == 0) {
                $return[$sDate . ":" . $ed] = $sDate . "至" . $ed;
            } elseif ($days < ($i + 7)) {
                $return[$sd . ":" . $eDate] = $sd . "至" . $eDate;
            } else {
                $return[$sd . ":" . $ed] = $sd . "至" . $ed;
            }
        }

        return $return;
    }

    public function formateDateByMoon($start, $end)
    {
        $return = array();
        $st = date("Y-m-d", $start);
        $et = date("Y-m-d", $end);
        $firstDateOfStartMonth = date("Y-m", $start) . "-1";
        $firstDateOfEndMonth = date("Y-m", $end) . "-1";
        $lastDateOfEndMonth = date("Y-m-d", strtotime("+1 month -1 day $firstDateOfEndMonth"));
        $dates = DateTimeUtil::getDiffDate($firstDateOfStartMonth, $lastDateOfEndMonth);
        $moons = ($dates["y"] * 12) + $dates["m"] + 1;

        if ($moons == 1) {
            $return[$st . ":" . $et] = $st . "至" . $et;
            return $return;
        }

        for ($i = 0; $i < $moons; $i++) {
            $sd = date("Y-m", strtotime("+$i month $st")) . "-1";
            $ed = date("Y-m-d", strtotime("+1 month -1 day $sd"));

            if ($i == 0) {
                $return[$st . ":" . $ed] = $st . "至" . $ed;
            } elseif ($moons <= $i + 1) {
                $return[$sd . ":" . $et] = $sd . "至" . $et;
            } else {
                $return[$sd . ":" . $ed] = date("Y-m", strtotime("+$i month $st"));
            }
        }

        return $return;
    }
}
