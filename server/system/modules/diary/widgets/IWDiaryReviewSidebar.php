<?php

class IWDiaryReviewSidebar extends CWidget
{
    const VIEW = "application.modules.diary.views.widget.reviewSidebar";

    public function run()
    {
        $data = array("hasSub" => DiaryUtil::checkIsHasSub(), "statModule" => Ibos::app()->setting->get("setting/statmodules"), "config" => DiaryUtil::getSetting(), "id" => $this->getController()->getId());
        $this->render(self::VIEW, $data);
    }
}
