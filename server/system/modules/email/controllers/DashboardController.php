<?php

class EmailDashboardController extends DashboardBaseController
{
    /**
     * setting配置项
     * @var array 
     */
    private $_fields = array("emailexternalmail", "emailrecall", "emailsystemremind", "emailroleallocation", "emaildefsize");

    public function actionIndex()
    {
        $emailSetting = array();
        $setting = Yii::app()->setting->get("setting");

        foreach ($this->_fields as $field) {
            $emailSetting[$field] = $setting[$field];
        }

        $data["setting"] = $emailSetting;
        $this->render("index", $data);
    }

    public function actionEdit()
    {
        if (EnvUtil::submitCheck("emailSubmit")) {
            $setting = array();

            foreach ($this->_fields as $field) {
                if (array_key_exists($field, $_POST)) {
                    $setting[$field] = intval($_POST[$field]);
                } else {
                    $setting[$field] = 0;
                }
            }

            $roles = array();

            if (isset($_POST["role"])) {
                foreach ($_POST["role"] as $role) {
                    if (!empty($role["positionid"]) && !empty($role["size"])) {
                        $positionId = StringUtil::getId($role["positionid"]);
                        $roles[implode(",", $positionId)] = intval($role["size"]);
                    }
                }
            }

            $setting["emailroleallocation"] = serialize($roles);

            foreach ($setting as $key => $value) {
                Setting::model()->updateSettingValueByKey($key, $value);
            }

            CacheUtil::update("setting");
            $this->success(Ibos::lang("Update succeed", "message"), $this->createUrl("dashboard/index"));
        }
    }
}
