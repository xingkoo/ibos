<?php

class IWStatRecruitHeader extends IWStatRecruitBase
{
    const VIEW = "application.modules.recruit.views.widget.header";

    public function run()
    {
        $module = $this->getController()->getModule()->getId();
        $timeRoute = $this->getTimeRoute($module);
        $type = $this->getType();
        $timestr = $this->getTimestr();

        if (empty($type)) {
            $type = "day";
        }

        if (empty($timestr)) {
            $timestr = "thisweek";
        }

        $data = array("module" => $module, "timeRoute" => $timeRoute, "lang" => Ibos::getLangSources(array("recruit.default")), "time" => StatCommonUtil::getCommonTimeScope(), "type" => $type, "timestr" => $timestr);
        $this->render(self::VIEW, $data);
    }

    protected function getTimeRoute($module)
    {
        if ($module == "recruit") {
            $timeRoute = "recruit/stats/index";
        } else {
            $timeRoute = "statistics/module/index";
        }

        return $timeRoute;
    }
}
