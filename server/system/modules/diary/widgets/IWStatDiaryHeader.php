<?php

class IWStatDiaryHeader extends IWStatDiaryBase
{
    const VIEW = "application.modules.diary.views.widget.header";

    public function run()
    {
        $module = $this->getController()->getModule()->getId();
        $timeRoute = $this->getTimeRoute($module);
        $data = array("module" => $module, "timeRoute" => $timeRoute, "lang" => Ibos::getLangSources(array("diary.default")), "time" => StatCommonUtil::getCommonTimeScope());
        $this->render(self::VIEW, $data);
    }

    protected function getTimeRoute($module)
    {
        if ($module == "diary") {
            $timeRoute = "diary/stats/" . $this->getType();
        } else {
            $timeRoute = "statistics/module/index";
        }

        return $timeRoute;
    }
}
