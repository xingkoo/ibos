<?php

class DashboardServiceController extends DashboardBaseController
{
    const APPLY_ROUTE = "App/Token/Apply";
    const APP_LIST_ROUTE = "Api/App/Apilist";

    public function actionIndex()
    {
        $setting = Ibos::app()->setting->get("setting/iboscloud");

        if (EnvUtil::submitCheck("formhash")) {
            $this->openApp($setting);
        } else {
            $setting = Ibos::app()->setting->get("setting/iboscloud");

            if ($setting["isopen"] == 1) {
                $this->render("index", array("setting" => $setting));
            } else {
                $this->render("edit", array("setting" => $setting));
            }
        }
    }

    public function actionEdit()
    {
        $setting = Ibos::app()->setting->get("setting/iboscloud");

        if (EnvUtil::submitCheck("formhash")) {
            $this->openApp($setting);
        } else {
            $this->render("edit", array("setting" => $setting));
        }
    }

    public function actionUpdateApi()
    {
        $setting = Ibos::app()->setting->get("setting/iboscloud");

        if ($setting["isopen"] == 1) {
            $list = CloudApi::getInstance()->fetch(self::APP_LIST_ROUTE);

            if (substr($list, 0, 5) !== "error") {
                $res = json_decode($list, true);
                if (!empty($res) && ($res["ret"] == 0)) {
                    $setting["apilist"] = $res["data"];
                    Setting::model()->updateSettingValueByKey("iboscloud", $setting);
                    CacheUtil::update("setting");
                    $this->success($res["msg"]);
                }

                $this->error($res["msg"]);
            }

            $this->error(Ibos::lang("Cloud comm error"));
        } else {
            $this->error(Ibos::lang("App not open"), $this->createUrl("service/index"));
        }
    }

    protected function openApp($oldsetting)
    {
        $data = array("appid" => filter_input(INPUT_POST, "appid"), "secret" => filter_input(INPUT_POST, "secret"));
        $api = ApiUtil::getInstance();
        $rs = $api->fetchResult($oldsetting["url"] . self::APPLY_ROUTE, $data, "post");

        if (substr($rs, 0, 5) !== "error") {
            $rs = json_decode($rs, true);

            if ($rs["ret"] == 1) {
                Setting::model()->updateSettingValueByKey("iboscloud", array("appid" => $data["appid"], "secret" => $data["secret"], "url" => $oldsetting["url"], "isopen" => 1, "apilist" => $rs["data"]));
                CacheUtil::update("setting");
                $this->success($rs["msg"]);
            } else {
                $this->error($rs["msg"]);
            }
        } else {
            $this->error(Ibos::lang("Cloud comm error"));
        }
    }
}
