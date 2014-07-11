<?php

class DashboardUpdateController extends DashboardBaseController
{
    public function actionIndex()
    {
        $types = EnvUtil::getRequest("updatetype");
        $data = array();

        if (EnvUtil::submitCheck("formhash")) {
            $type = implode(",", $types);

            if (!empty($type)) {
                $this->redirect($this->createUrl("update/index", array("doupdate" => 1, "updatetype" => $type)));
            }
        }

        if (Ibos::app()->request->getIsAjaxRequest()) {
            $op = EnvUtil::getRequest("op");

            if (LOCAL) {
                @set_time_limit(0);
            }

            if ($op == "data") {
                CacheUtil::update();
            }

            if ($op == "static") {
                LOCAL && Ibos::app()->assetManager->republicAll();
                OrgUtil::update();
            }

            if ($op == "module") {
                ModuleUtil::updateConfig();
            }

            Ibos::app()->cache->clear();
            $this->ajaxReturn(array("isSuccess" => true));
        }

        if (EnvUtil::getRequest("doupdate") == 1) {
            $type = explode(",", trim($types, ","));
            $data["doUpdate"] = true;

            foreach ($type as $index => $act) {
                if (!empty($act)) {
                    if (in_array("data", $type)) {
                        unset($type[$index]);
                        $data["typedesc"] = Ibos::lang("Update") . Ibos::lang("Data cache");
                        $data["op"] = "data";
                        break;
                    }

                    if (in_array("static", $type)) {
                        unset($type[$index]);
                        $data["typedesc"] = Ibos::lang("Update") . Ibos::lang("Static cache");
                        $data["op"] = "static";
                        break;
                    }

                    if (in_array("module", $type)) {
                        $data["typedesc"] = Ibos::lang("Update") . Ibos::lang("Module setting");
                        $data["op"] = "module";
                        unset($type[$index]);
                        break;
                    }
                }
            }

            $data["next"] = $this->createUrl("update/index", array("doupdate" => intval(!empty($type)), "updatetype" => implode(",", $type)));
        } else {
            $data["doUpdate"] = false;
        }

        $this->render("index", $data);
    }
}
