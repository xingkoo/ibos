<?php

class DashboardUnitController extends DashboardBaseController
{
    public function actionIndex()
    {
        $unit = Setting::model()->fetchSettingValueByKey("unit");
        $formSubmit = EnvUtil::submitCheck("unitSubmit");

        if ($formSubmit) {
            $postData = array();

            if (!empty($_FILES["logo"]["name"])) {
                !empty($unit["logourl"]) && FileUtil::deleteFile($unit["logourl"]);
                $postData["logourl"] = $this->imgUpload("logo");
            } elseif (!empty($_POST["logourl"])) {
                $postData["logourl"] = $_POST["logourl"];
            } else {
                $postData["logourl"] = "";
            }

            $keys = array("phone", "fullname", "shortname", "fax", "zipcode", "address", "adminemail", "systemurl");

            foreach ($keys as $key) {
                if (isset($_POST[$key])) {
                    $postData[$key] = StringUtil::filterCleanHtml($_POST[$key]);
                } else {
                    $postData[$key] = "";
                }
            }

            Setting::model()->updateSettingValueByKey("unit", $postData);
            CacheUtil::update(array("setting"));
            $this->success(Ibos::lang("Save succeed", "message"));
        } else {
            $license = Setting::model()->fetchSettingValueByKey("license");
            $data = array("unit" => unserialize($unit), "license" => $license);
            $this->render("index", $data);
        }
    }
}
