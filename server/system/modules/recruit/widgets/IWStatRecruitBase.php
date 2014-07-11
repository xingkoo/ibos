<?php

class IWStatRecruitBase extends CWidget
{
    /**
     * 统计的类型(日、月、周)
     * @var string 
     */
    private $_type = "day";
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

    protected function createComponent($class, $properties = array())
    {
        return Ibos::createComponent(array_merge(array("class" => $class), $properties));
    }
}
