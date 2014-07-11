<?php

class DashboardDateController extends DashboardBaseController
{
    public function actionIndex()
    {
        $formSubmit = EnvUtil::submitCheck("dateSetupSubmit");

        if ($formSubmit) {
            $data = array("dateformat" => $_POST["dateFormat"], "timeformat" => $_POST["timeFormat"], "dateconvert" => $_POST["dateConvert"], "timeoffset" => $_POST["timeOffset"]);

            foreach ($data as $sKey => $sValue) {
                Setting::model()->updateSettingValueByKey($sKey, $sValue);
            }

            CacheUtil::update(array("setting"));
            $this->success(Ibos::lang("Save succeed", "message"));
        } else {
            $date = Setting::model()->fetchSettingValueByKeys("dateformat,dateconvert,timeformat,timeoffset");
            $data = array("timeZone" => Ibos::getLangSource("dashboard.timeZone"), "date" => $date);
            $this->render("index", $data);
        }
    }
}
