<?php

class ICRecruitPieChart extends ICRecruitChart
{
    public function getSeries()
    {
        $datas = $this->getCounter()->getCount();
        return $datas;
    }
}
