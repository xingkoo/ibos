<?php

class IWStatReportCount extends IWStatReportBase
{
    const VIEW = "application.modules.report.views.widget.count";

    public function init()
    {
        $this->checkReviewAccess();
    }

    public function run()
    {
        $factory = new ICChartFactory();
        $properties = array("uid" => $this->getUid(), "typeid" => $this->getTypeid(), "timeScope" => $this->getTimeScope());
        $scoreCounter = $this->createComponent("ICReportScoreTimeCounter", $properties);
        $stampCounter = $this->createComponent("ICReportStampCounter", $properties);
        $data = array("statAssetUrl" => Ibos::app()->assetManager->getAssetsUrl("statistics"), "score" => $factory->createChart($scoreCounter, "ICReportLineChart"), "stamp" => $factory->createChart($stampCounter, "ICReportBarChart"));
        $this->render(self::VIEW, $data);
    }
}
