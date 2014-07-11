<?php

class ICPerformanceMeasurement extends CApplicationComponent
{
    /**
     * 开始时间，一般记录在流程初始化开始
     * @var interger 
     */
    protected $startTime;
    /**
     * 内存使用
     * @var interger 
     */
    protected $memoryUsage;
    /**
     * 用于生产模式的测试运行时间标记数组。
     * @var array
     */
    protected $timings = array();

    public function startClock()
    {
        $this->startTime = microtime(true);
    }

    public function endClockAndGet()
    {
        $endTime = microtime(true);
        return number_format($endTime - $this->startTime, 6);
    }

    public function addTimingById($identifer, $time)
    {
        if (isset($this->timings[$identifer])) {
            $this->timings[$identifer] = $this->timings[$identifer] + $time;
        } else {
            $this->timings[$identifer] = $time;
        }
    }

    public function getTimings()
    {
        return $this->timings;
    }

    public function startMemoryUsageMarker()
    {
        $this->memoryUsage = memory_get_usage();
    }

    public function getMemoryMarkerUsage($format = true)
    {
        $usage = (int) memory_get_usage() - $this->memoryUsage;

        if ($format) {
            return ConvertUtil::sizeCount($usage);
        }

        return $usage;
    }

    public function getDbStats()
    {
        $stats = Ibos::app()->db->getStats();
        return $stats[0];
    }

    public function getMemoryUsage()
    {
        return memory_get_usage();
    }
}
