<?php

class IWStatReportSidebar extends IWStatReportBase
{
    const VIEW = "application.modules.report.views.widget.sidebar";

    private $_hasSub;

    public function setHasSub($hasSub)
    {
        $this->_hasSub = $hasSub;
    }

    public function getHasSub()
    {
        return $this->_hasSub;
    }

    public function run()
    {
        $id = $this->getController()->getId();
        $action = $this->getController()->getAction()->getId();
        $data = array("inPersonal" => ($id == "stats") && ($action == "personal"), "inReview" => ($id == "stats") && ($action == "review"), "hasSub" => $this->getHasSub(), "lang" => Ibos::getLangSource("report.default"));
        $this->render(self::VIEW, $data);
    }
}
