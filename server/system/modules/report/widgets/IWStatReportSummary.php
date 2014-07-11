<?php

class IWStatReportSummary extends IWStatReportBase
{
    const PERSONAL = "application.modules.report.views.widget.psummary";
    const REVIEW = "application.modules.report.views.widget.rsummary";

    public function run()
    {
        $time = StatCommonUtil::getCommonTimeScope();
        $typeid = $this->getTypeid();

        if ($this->inPersonal()) {
            $this->renderPersonal($time, $typeid);
        } else {
            $this->checkReviewAccess();
            $this->renderReview($time, $typeid);
        }
    }

    protected function renderPersonal($time, $typeid)
    {
        $uid = Ibos::app()->user->uid;
        $data = array("title" => $this->handleTitleByTypeid($typeid), "total" => Report::model()->countReportTotalByUid($uid, $time["start"], $time["end"], $typeid), "beingreviews" => Report::model()->countReviewTotalByUid($uid, $time["start"], $time["end"], $typeid), "score" => ReportStats::model()->countScoreByUid($uid, $time["start"], $time["end"], $typeid));
        $this->render(self::PERSONAL, $data);
    }

    protected function renderReview($time, $typeid)
    {
        $uid = $this->getUid();
        $data = array("title" => $this->handleTitleByTypeid($typeid), "total" => Report::model()->countReportTotalByUid($uid, $time["start"], $time["end"], $typeid), "unreviews" => Report::model()->countUnReviewByUids($uid, $time["start"], $time["end"], $typeid));
        $data["reviewrate"] = $this->calcReviewRate($data["unreviews"], $data["total"]);
        $this->render(self::REVIEW, $data);
    }

    private function calcReviewRate($unreview, $total)
    {
        if (($unreview == 0) && $total) {
            return 100;
        } else {
            if ($unreview && $total) {
                return round((1 - ($unreview / $total)) * 100);
            } else {
                return 0;
            }
        }
    }

    protected function handleTitleByTypeid($typeid)
    {
        $title = array(1 => "周报", 2 => "月报", 3 => "季报", 4 => "年报");

        if (in_array($typeid, array_keys($title))) {
            return $title[$typeid];
        } else {
            return $title[1];
        }
    }
}
