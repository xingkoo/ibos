<?php

class IWStatDiarySummary extends IWStatDiaryBase
{
    const PERSONAL = "application.modules.diary.views.widget.psummary";
    const REVIEW = "application.modules.diary.views.widget.rsummary";

    public function run()
    {
        $time = StatCommonUtil::getCommonTimeScope();

        if ($this->inPersonal()) {
            $this->renderPersonal($time);
        } else {
            $this->checkReviewAccess();
            $this->renderReview($time);
        }
    }

    protected function renderPersonal($time)
    {
        $uid = Ibos::app()->user->uid;
        $data = array("total" => Diary::model()->countDiaryTotalByUid($uid, $time["start"], $time["end"]), "beingreviews" => Diary::model()->countReviewTotalByUid($uid, $time["start"], $time["end"]), "ontimerate" => Diary::model()->countOnTimeRateByUid($uid, $time["start"], $time["end"]), "score" => DiaryStats::model()->countScoreByUid($uid, $time["start"], $time["end"]));
        $this->render(self::PERSONAL, $data);
    }

    protected function renderReview($time)
    {
        $uid = $this->getUid();
        $data = array("total" => Diary::model()->countDiaryTotalByUid($uid, $time["start"], $time["end"]), "unreviews" => Diary::model()->countUnReviewByUids($uid, $time["start"], $time["end"]));
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
}
