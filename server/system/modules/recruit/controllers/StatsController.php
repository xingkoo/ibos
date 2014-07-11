<?php

class RecruitStatsController extends RecruitBaseController
{
    public function init()
    {
        if (!ModuleUtil::getIsEnabled("statistics")) {
            $this->error(Ibos::t("Module \"{module}\" is illegal.", "error", array("{module}" => Ibos::lang("Statistics"))), $this->createUrl("default/index"));
        }
    }

    public function getSidebar()
    {
        $sidebarAlias = "application.modules.recruit.views.resume.sidebar";
        $params = array("lang" => Ibos::getLangSource("recruit.default"), "statModule" => Ibos::app()->setting->get("setting/statmodules"));
        $sidebarView = $this->renderPartial($sidebarAlias, $params, false);
        return $sidebarView;
    }

    public function actionIndex()
    {
        $this->setPageTitle(Ibos::lang("Recruit statistics"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Talent management"), "url" => $this->createUrl("resume/index")),
            array("name" => Ibos::lang("Recruit statistics"))
        ));
        $this->render("stats", array_merge(array("type" => "personal"), $this->getData()));
    }

    protected function getData()
    {
        $type = EnvUtil::getRequest("type");
        $timestr = EnvUtil::getRequest("time");
        return array("type" => $type, "timestr" => $timestr, "statAssetUrl" => Ibos::app()->assetManager->getAssetsUrl("statistics"), "widgets" => StatCommonUtil::getWidget("recruit"));
    }
}
