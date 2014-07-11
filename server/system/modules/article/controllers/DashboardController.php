<?php

class ArticleDashboardController extends DashboardBaseController
{
    public function getAssetUrl($module = "")
    {
        $module = "dashboard";
        return Yii::app()->assetManager->getAssetsUrl($module);
    }

    public function actionIndex()
    {
        $result = array();
        $fields = array("articlecommentenable", "articlevoteenable", "articlemessageenable", "articlethumbenable", "articlethumbwh");

        foreach ($fields as $field) {
            $result[$field] = Yii::app()->setting->get("setting/" . $field);
        }

        $thumbOperate = $result["articlethumbwh"];
        list($result["articlethumbwidth"], $result["articlethumbheight"]) = explode(",", $thumbOperate);
        $this->render("index", array("data" => $result));
    }

    public function actionEdit()
    {
        $data = array();
        $fields = array("articlecommentenable", "articlevoteenable", "articlemessageenable", "articlethumbenable", "articlethumbwidth", "articlethumbheight");

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                if (empty($_POST[$field])) {
                    $data[$field] = 0;
                } else {
                    $data[$field] = $_POST[$field];
                }
            } else {
                $data[$field] = 0;
            }
        }

        $data["articlethumbwh"] = $data["articlethumbwidth"] . "," . $data["articlethumbheight"];
        unset($data["articlethumbwidth"]);
        unset($data["articlethumbhieght"]);

        foreach ($data as $key => $value) {
            Setting::model()->updateAll(array("svalue" => $value), "skey=:skey", array(":skey" => $key));
        }

        CacheUtil::update("setting");
        $this->success(Ibos::lang("Update succeed"), $this->createUrl("dashboard/index"));
    }
}
