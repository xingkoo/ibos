<?php

class IWReportSublist extends CWidget
{
    const VIEW = "application.modules.report.views.widget.sublist";

    private $_instats;

    public function run()
    {
        $data = array("typeid" => EnvUtil::getRequest("typeid"), "lang" => Ibos::getLangSource("report.default"), "deptArr" => UserUtil::getManagerDeptSubUserByUid(Ibos::app()->user->uid), "dashboardConfig" => ReportUtil::getSetting(), "deptRoute" => $this->inStats() ? "stats/review" : "review/index", "userRoute" => $this->inStats() ? "stats/review" : "review/personal");
        $this->render(self::VIEW, $data);
    }

    public function inStats()
    {
        return $this->getStats() === true;
    }

    public function setStats($stats)
    {
        $this->_instats = $stats;
    }

    public function getStats()
    {
        return (bool) $this->_instats;
    }
}
