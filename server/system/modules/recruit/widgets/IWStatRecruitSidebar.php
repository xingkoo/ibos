<?php

class IWStatRecruitSidebar extends IWStatRecruitBase
{
    const VIEW = "application.modules.recruit.views.widget.sidebar";

    public function run()
    {
        $data = array("lang" => Ibos::getLangSource("recruit.default"));
        $this->render(self::VIEW, $data);
    }
}
