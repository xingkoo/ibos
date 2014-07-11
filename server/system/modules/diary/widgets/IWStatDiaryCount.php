<?php

class IWStatDiaryCount extends IWStatDiaryBase
{
    const VIEW = "application.modules.diary.views.widget.count";

    public function init()
    {
        $this->checkReviewAccess();
    }

    public function run()
    {
        $factory = new ICChartFactory();
        $properties = array("uid" => $this->getUid(), "timeScope" => StatCommonUtil::getCommonTimeScope());
        $timeCounter = $this->createComponent("ICDiarySubmitTimeCounter", $properties);
        $scoreCounter = $this->createComponent("ICDiaryScoreTimeCounter", $properties);
        $stampCounter = $this->createComponent("ICDiaryStampCounter", $properties);
        $data = array("statAssetUrl" => Ibos::app()->assetManager->getAssetsUrl("statistics"), "time" => $factory->createChart($timeCounter, "ICDiaryLineChart"), "score" => $factory->createChart($scoreCounter, "ICDiaryLineChart"), "stamp" => $factory->createChart($stampCounter, "ICDiaryBarChart"));
        $this->render(self::VIEW, $data);
    }
}
