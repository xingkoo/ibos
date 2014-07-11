<?php

class CalendarDashboardController extends DashboardBaseController
{
    /**
     *配置项
     * @var array 
     */
    private $_fields = array("calendaraddschedule", "calendareditschedule", "calendarworkingtime", "calendaredittask");

    public function getAssetUrl($module = "")
    {
        $module = "dashboard";
        return Yii::app()->assetManager->getAssetsUrl($module);
    }

    public function actionIndex()
    {
        $calendarSetting = array();
        $setting = Yii::app()->setting->get("setting");

        foreach ($this->_fields as $field) {
            $calendarSetting[$field] = $setting[$field];
        }

        $data["setting"] = $calendarSetting;
        $this->render("index", $data);
    }

    public function actionUpdate()
    {
        if (EnvUtil::submitCheck("calendarSubmit")) {
            $setting = array();

            foreach ($this->_fields as $field) {
                if (array_key_exists($field, $_POST)) {
                    $setting[$field] = $_POST[$field];
                } else {
                    $setting[$field] = 0;
                }
            }

            foreach ($setting as $key => $value) {
                Setting::model()->updateSettingValueByKey($key, $value);
            }

            CacheUtil::update("setting");
            $this->success(Ibos::lang("Update succeed", "message"), $this->createUrl("dashboard/index"));
        }
    }
}
