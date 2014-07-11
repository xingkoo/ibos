<?php

class ICChartFactory extends CApplicationComponent
{
    /**
     *
     * @var array 
     */
    public $charts = array();

    public function createChart($counter, $className, $properties = array())
    {
        $className = Ibos::import($className, true);
        $chart = new $className($counter);
        $this->chkInstance($chart);

        if (isset($this->charts[$className])) {
            $properties = ($properties === array() ? $this->charts[$className] : CMap::mergeArray($this->charts[$className], $properties));
        }

        foreach ($properties as $name => $value) {
            $chart->$name = $value;
        }

        return $chart;
    }

    private function chkInstance($chart)
    {
        if (!$chart instanceof ICChart) {
            throw new CException(Ibos::t("error", "Class \"{class}\" is illegal.", array("{class}" => get_class($chart))));
        }
    }
}
