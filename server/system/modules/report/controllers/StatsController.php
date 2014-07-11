<?php

class ReportStatsController extends ReportBaseController
{
    public function init()
    {
        if (!ModuleUtil::getIsEnabled("statistics")) {
            $this->error(Ibos::t("Module \"{module}\" is illegal.", "error", array("{module}" => Ibos::lang("Statistics"))), $this->createUrl("default/index"));
        }
    }

    public function getSidebar()
    {
        $uid = Ibos::app()->user->uid;
        $deptArr = UserUtil::getManagerDeptSubUserByUid($uid);
        $sidebarAlias = "application.modules.report.views.stats.sidebar";
        $params = array("lang" => Ibos::getLangSource("report.default"), "deptArr" => $deptArr, "dashboardConfig" => $this->getReportConfig(), "statModule" => Ibos::app()->setting->get("setting/statmodules"));
        $sidebarView = $this->renderPartial($sidebarAlias, $params, false);
        return $sidebarView;
    }

    public function actionPersonal()
    {
        $this->setPageTitle(Ibos::lang("Personal statistics"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Personal Office")),
            array("name" => Ibos::lang("Work report"), "url" => $this->createUrl("default/index")),
            array("name" => Ibos::lang("Personal statistics"))
        ));
        $this->render("stats", array_merge(array("type" => "personal"), $this->getData()));
    }

    public function actionReview()
    {
        $this->setPageTitle(Ibos::lang("Review statistics"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Personal Office")),
            array("name" => Ibos::lang("Work report"), "url" => $this->createUrl("default/index")),
            array("name" => Ibos::lang("Review statistics"))
        ));
        $this->render("stats", array_merge(array("type" => "review"), $this->getData()));
    }

    protected function getData()
    {
        $typeid = EnvUtil::getRequest("typeid");
        return array("typeid" => empty($typeid) ? 1 : $typeid, "statAssetUrl" => Ibos::app()->assetManager->getAssetsUrl("statistics"), "widgets" => StatCommonUtil::getWidget("report"));
    }
}
