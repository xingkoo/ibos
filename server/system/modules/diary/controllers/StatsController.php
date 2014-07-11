<?php

class DiaryStatsController extends DiaryBaseController
{
    public function init()
    {
        if (!ModuleUtil::getIsEnabled("statistics")) {
            $this->error(Ibos::t("Module \"{module}\" is illegal.", "error", array("{module}" => Ibos::lang("Statistics"))), $this->createUrl("default/index"));
        }
    }

    protected function getSidebar()
    {
        $sidebarAlias = "application.modules.diary.views.stats.sidebar";
        $sidebarView = $this->renderPartial($sidebarAlias, array("statModule" => Ibos::app()->setting->get("setting/statmodules")), true);
        return $sidebarView;
    }

    public function actionPersonal()
    {
        $this->setPageTitle(Ibos::lang("Personal statistics"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Personal Office")),
            array("name" => Ibos::lang("Work diary"), "url" => $this->createUrl("default/index")),
            array("name" => Ibos::lang("Personal statistics"))
        ));
        $this->render("stats", array_merge(array("type" => "personal"), $this->getData()));
    }

    public function actionReview()
    {
        $this->setPageTitle(Ibos::lang("Review statistics"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Personal Office")),
            array("name" => Ibos::lang("Work diary"), "url" => $this->createUrl("default/index")),
            array("name" => Ibos::lang("Review statistics"))
        ));
        $this->render("stats", array_merge(array("type" => "review"), $this->getData()));
    }

    protected function getData()
    {
        return array("statAssetUrl" => Ibos::app()->assetManager->getAssetsUrl("statistics"), "widgets" => StatCommonUtil::getWidget("diary"));
    }
}
