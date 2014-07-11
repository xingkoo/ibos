<?php

class IWStatRecruitSummary extends IWStatRecruitBase
{
    const VIEW = "application.modules.recruit.views.widget.summary";

    public function run()
    {
        $time = StatCommonUtil::getCommonTimeScope();
        $this->renderOverview($time);
    }

    protected function renderOverview($time)
    {
        $data = array("new" => Resume::model()->countByStatus(array(1, 2, 3, 4, 5), $time["start"], $time["end"]), "pending" => Resume::model()->countByStatus(4, $time["start"], $time["end"]), "interview" => Resume::model()->countByStatus(1, $time["start"], $time["end"]), "employ" => Resume::model()->countByStatus(array(2, 3), $time["start"], $time["end"]), "eliminate" => Resume::model()->countByStatus(5, $time["start"], $time["end"]));
        $this->render(self::VIEW, $data);
    }
}
