<?php

class IWDiarySublist extends CWidget
{
    const VIEW = "application.modules.diary.views.widget.sublist";

    private $_instats;
    private $_fromController = "review";

    public function setFromController($fromController)
    {
        $this->_fromController = $fromController;
    }

    public function getFromController()
    {
        return $this->_fromController;
    }

    public function run()
    {
        $data = array("deptArr" => UserUtil::getManagerDeptSubUserByUid(Ibos::app()->user->uid), "dashboardConfig" => DiaryUtil::getSetting(), "deptRoute" => $this->inStats() ? "stats/review" : "review/index", "userRoute" => $this->inStats() ? "stats/review" : "review/personal", "fromController" => $this->getController()->getId());
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
