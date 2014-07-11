<?php

class VoteDashboardController extends DashboardBaseController
{
    public function getAssetUrl($module = "")
    {
        $module = "dashboard";
        return Yii::app()->assetManager->getAssetsUrl($module);
    }

    public function actionIndex()
    {
        $votethumbwh = Yii::app()->setting->get("setting/votethumbwh");
        list($width, $height) = explode(",", $votethumbwh);
        $config = array("votethumbenable" => Yii::app()->setting->get("setting/votethumbenable"), "votethumbwidth" => $width, "votethumbheight" => $height);
        $this->render("index", $config);
    }

    public function actionEdit()
    {
        $votethumbenable = 0;

        if (isset($_POST["votethumbenable"])) {
            $votethumbenable = $_POST["votethumbenable"];
        }

        $width = (empty($_POST["votethumbwidth"]) ? 0 : $_POST["votethumbwidth"]);
        $height = (empty($_POST["votethumbheight"]) ? 0 : $_POST["votethumbheight"]);
        $votethumbewh = $width . "," . $height;
        Setting::model()->modify("votethumbenable", array("svalue" => $votethumbenable));
        Setting::model()->modify("votethumbwh", array("svalue" => $votethumbewh));
        CacheUtil::update("setting");
        $this->success(Ibos::lang("Update succeed", "message"), $this->createUrl("dashboard/index"));
    }
}
