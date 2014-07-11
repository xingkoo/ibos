<?php

class IWReportType extends IWStatReportBase
{
    const VIEW = "application.modules.report.views.widget.type";

    public function run()
    {
        $module = $this->getController()->getModule()->getId();
        $data = array("type" => $this->getType(), "uid" => implode(",", $this->getUid()), "lang" => Ibos::getLangSource("report.default"), "reportTypes" => ReportType::model()->fetchSysType());
        $this->render(self::VIEW, $data);
    }
}
