<?php

class IWReportSidebar extends IWStatReportBase
{
    const VIEW = "application.modules.report.views.widget.sidebar";

    public function run()
    {
        $data = array("lang" => Ibos::getLangSource("report.default"), "id" => $this->getController()->getId());
        $this->render(self::VIEW, $data);
    }
}
