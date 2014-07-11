<?php

class StatisticsDashboardController extends DashboardBaseController
{
    public function actionIndex()
    {
        if (EnvUtil::submitCheck("formhash")) {
            if (isset($_POST["statmodules"])) {
            } else {
                $_POST["statmodules"] = array();
            }

            Setting::model()->updateSettingValueByKey("statmodules", $_POST["statmodules"]);
            CacheUtil::update("setting");
            $this->success(Ibos::lang("Operation succeed", "message"));
        } else {
            $res = Setting::model()->fetchSettingValueByKey("statmodules");
            $statModules = ($res ? unserialize($res) : array());
            $data = array("statModules" => $statModules, "enabledModules" => StatCommonUtil::getStatisticsModules());
            $this->render("index", $data);
        }
    }
}
