<?php

class ICRecruitLineChart extends ICRecruitChart
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
