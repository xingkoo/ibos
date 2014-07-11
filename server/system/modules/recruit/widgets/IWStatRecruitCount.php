<?php

class IWStatRecruitCount extends IWStatRecruitBase
{
    const VIEW = "application.modules.recruit.views.widget.count";

    public function run()
    {
        $factory = new ICChartFactory();
        $properties = array("timeScope" => StatCommonUtil::getCommonTimeScope(), "type" => $this->getType(), "timestr" => $this->getTimestr());
        $flowCounter = $this->createComponent("ICRecruitTalentFlowCounter", $properties);
        $sexRatioCounter = $this->createComponent("ICRecruitSexCounter", $properties);
        $ageCounter = $this->createComponent("ICRecruitAgeCounter", $properties);
        $degreeCounter = $this->createComponent("ICRecruitDegreeCounter", $properties);
        $workYearsCounter = $this->createComponent("ICRecruitWorkYearsCounter", $properties);
        $data = array("statAssetUrl" => Ibos::app()->assetManager->getAssetsUrl("statistics"), "talentFlow" => $factory->createChart($flowCounter, "ICRecruitLineChart"), "sexRatio" => $factory->createChart($sexRatioCounter, "ICRecruitPieChart"), "age" => $factory->createChart($ageCounter, "ICRecruitPieChart"), "degree" => $factory->createChart($degreeCounter, "ICRecruitPieChart"), "workYears" => $factory->createChart($workYearsCounter, "ICRecruitPieChart"));
        $this->render(self::VIEW, $data);
    }
}
