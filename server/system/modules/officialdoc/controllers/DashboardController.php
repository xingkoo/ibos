<?php

class OfficialdocDashboardController extends DashboardBaseController
{
    public function getAssetUrl($module = "")
    {
        $module = "dashboard";
        return Yii::app()->assetManager->getAssetsUrl($module);
    }

    public function actionIndex()
    {
        $docConfig = Yii::app()->setting->get("setting/docconfig");
        $data = RcType::model()->fetchAll();
        $params = array("commentSwitch" => $docConfig["doccommentenable"], "data" => $data);
        $this->render("index", $params);
    }

    public function actionAdd()
    {
        $oldRcType = $newRcType = $delRcids = $oldRcids = array();

        foreach ($_POST as $key => $value) {
            $value = trim($value);

            if (!empty($value)) {
                if ((strpos($key, "old_") === 0) || (strpos($key, "old_") !== false)) {
                    list(, $rcid) = explode("_", $key);
                    $oldRcType[$rcid] = $value;
                    $oldRcids[] = $rcid;
                }

                if ((strpos($key, "new_") === 0) || (strpos($key, "new_") !== false)) {
                    $newRcType[] = $value;
                }
            }
        }

        $rcTypes = RcType::model()->fetchAll(array(
            "select"    => array("rcid"),
            "condition" => "",
            "params"    => array()
        ));

        foreach ($rcTypes as $rcType) {
            if (!in_array($rcType["rcid"], $oldRcids)) {
                $delRcids[] = $rcType["rcid"];
            }
        }

        $docConfig = array("doccommentenable" => isset($_POST["commentSwitch"]) ? 1 : 0);
        Setting::model()->modify("docconfig", array("svalue" => serialize($docConfig)));

        foreach ($oldRcType as $key => $value) {
            RcType::model()->modify($key, array("name" => $value));
        }

        foreach ($newRcType as $key => $value) {
            $rcType = array("name" => $value);
            RcType::model()->add($rcType);
        }

        if (0 < count($delRcids)) {
            RcType::model()->deleteByPk($delRcids);
        }

        CacheUtil::update("setting");
        $this->success(Ibos::lang("Update succeed", "message"), $this->createUrl("dashboard/index"));
    }

    public function actionEdit()
    {
        $op = EnvUtil::getRequest("op");
        $option = (empty($op) ? "default" : $op);
        $routes = array("default", "update");

        if (!in_array($option, $routes)) {
            $this->error(Ibos::lang("Can not find the path"), $this->createUrl("officialdoc/index"));
        }

        if ($option == "default") {
            $rcid = EnvUtil::getRequest("rcid");

            if (empty($rcid)) {
                $this->error(Ibos::lang("Request param", "error"));
            }

            $data = RcType::model()->fetchByPk($rcid);
            $this->render("edit", array("data" => $data));
        } else {
            $this->{$option}();
        }
    }

    private function update()
    {
        $rcid = $_POST["rcid"];
        $name = $_POST["name"];
        $content = $_POST["content_text"];
        $escapeContent = $_POST["content"];
        RcType::model()->modify($rcid, array("name" => $name, "content" => $content, "escape_content" => $escapeContent));
        $this->success(Ibos::lang("Update succeed", "message"), $this->createUrl("dashboard/index"));
    }
}
