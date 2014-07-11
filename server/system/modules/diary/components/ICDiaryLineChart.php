<?php

class ICDiaryLineChart extends ICDiaryChart
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
