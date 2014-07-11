<?php

class IWStatReportHeader extends IWStatReportBase
{
    const VIEW = "application.modules.report.views.widget.header";

    public function run()
    {
        $module = $this->getController()->getModule()->getId();
        $timeRoute = $this->getTimeRoute($module);
        $data = array("module" => $module, "timeRoute" => $timeRoute, "lang" => Ibos::getLangSources(array("report.default")), "time" => StatCommonUtil::getCommonTimeScope());
        $this->render(self::VIEW, $data);
    }

    protected function getTimeRoute($module)
    {
        if ($module == "report") {
            $timeRoute = "report/stats/" . $this->getType();
        } else {
            $timeRoute = "statistics/module/index";
        }

        return $timeRoute;
    }
}
