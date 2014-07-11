<?php

class ICDiaryTimeCounter extends ICCounter
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
            $return = DateTimeUtil::getFormatDate($scope["start"], $scope["end"], "Y-m-d");
        }

        return $return;
    }
}
