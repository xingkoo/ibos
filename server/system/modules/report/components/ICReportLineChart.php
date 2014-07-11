<?php

class ICReportLineChart extends ICReportChart
{
    public function getSeries()
    {
        return $this->getCounter()->getCount();
    }

    public function getXaxis()
    {
        return $this->getCounter()->getDateScope();
    }
}
