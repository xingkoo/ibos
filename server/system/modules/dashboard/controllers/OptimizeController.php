<?php

class DashboardOptimizeController extends DashboardBaseController
{
    const DEFAULT_SEARCH_MODULE = "email,diary,article";

    public function actionCache()
    {
        if (LOCAL) {
            $operation = EnvUtil::getRequest("op");

            if ($operation == "clear") {
                Ibos::app()->cache->clear();
                $this->success(Ibos::lang("Operation successful", "message"));
            }

            $cacheExtension = Ibos::app()->cache->getExtension();
            $cacheConfig = Ibos::app()->cache->getConfig();
            $cacheType = Ibos::app()->cache->type;
            $caches = array();

            foreach ($cacheExtension as $cacheName => $enable) {
                $index = ucfirst($cacheName);
                $caches[$index]["extension"] = (bool) $enable;
                $caches[$index]["config"] = (isset($cacheConfig[$cacheName]) ? true : false);
                $caches[$index]["op"] = strcasecmp($cacheType, $cacheName) === 0;
            }

            $data = array("list" => $caches);
            $this->render("cache", $data);
        } else {
            echo Ibos::lang("Not compatible service", "message");
        }
    }

    public function actionSearch()
    {
        if (LOCAL) {
            $sphinxFields = "sphinxon,sphinxmsgindex,sphinxsubindex,sphinxmaxquerytime,sphinxlimit,sphinxrank";
            $sphinx = Setting::model()->fetchSettingValueByKeys($sphinxFields);
            $formSubmit = EnvUtil::submitCheck("searchSubmit");

            if ($formSubmit) {
                $operation = $_POST["operation"];
                $data = array("sphinxon" => isset($_POST["sphinxon"][$operation]) ? 1 : 0, "sphinxsubindex" => $_POST["sphinxsubindex"][$operation], "sphinxmsgindex" => $_POST["sphinxmsgindex"][$operation], "sphinxmaxquerytime" => $_POST["sphinxmaxquerytime"][$operation], "sphinxlimit" => $_POST["sphinxlimit"][$operation], "sphinxrank" => $_POST["sphinxrank"][$operation]);

                foreach ($sphinx as $sKey => $sValue) {
                    $value = unserialize($sValue);
                    $value[$operation] = $data[$sKey];
                    Setting::model()->updateSettingValueByKey($sKey, $value);
                }

                CacheUtil::update(array("setting"));
                $this->success(Ibos::lang("Save succeed", "message"));
            } else {
                $operation = EnvUtil::getRequest("op");
                $moduleList = explode(",", self::DEFAULT_SEARCH_MODULE);

                if (!in_array($operation, $moduleList)) {
                    $operation = $moduleList[0];
                }

                $data["operation"] = $operation;
                $data["moduleList"] = $moduleList;

                foreach ($sphinx as $sKey => $sValue) {
                    $data[$sKey] = unserialize($sValue);
                }

                $this->render("search", $data);
            }
        } else {
            echo Ibos::lang("Not compatible service", "message");
        }
    }

    public function actionSphinx()
    {
        if (LOCAL) {
            $formSubmit = EnvUtil::submitCheck("sphinxSubmit");

            if ($formSubmit) {
                $sphinxHost = $_POST["sphinxhost"];
                $sphinxPort = $_POST["sphinxport"];
                Setting::model()->updateSettingValueByKey("sphinxhost", $sphinxHost);
                Setting::model()->updateSettingValueByKey("sphinxport", $sphinxPort);
                CacheUtil::update(array("setting"));
                $this->success(Ibos::lang("Save succeed", "message"));
            } else {
                $record = Setting::model()->fetchSettingValueByKeys("sphinxhost,sphinxport");
                $sphinxPort = Setting::model()->fetchSettingValueByKey("sphinxport");
                $data = array("sphinxHost" => $record["sphinxhost"], "sphinxPort" => $record["sphinxport"]);
                $this->render("sphinx", $data);
            }
        } else {
            echo Ibos::lang("Not compatible service", "message");
        }
    }
}
