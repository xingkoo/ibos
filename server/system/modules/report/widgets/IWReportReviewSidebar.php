<?php

class IWReportReviewSidebar extends CWidget
{
    const VIEW = "application.modules.report.views.widget.reviewSidebar";

    public function run()
    {
        $data = array("config" => ReportUtil::getSetting(), "id" => $this->getController()->getId());
        $this->render(self::VIEW, $data);
    }
}
