<?php

class DashboardModuleController extends DashboardBaseController
{
    public function actionManager()
    {
        $moduleType = EnvUtil::getRequest("op");

        if (!in_array($moduleType, array("installed", "uninstalled"))) {
            $moduleType = "installed";
        }

        if ($moduleType == "uninstalled") {
            $moduleDirs = ModuleUtil::getModuleDirs();

            if (!empty($moduleDirs)) {
                $moduleDirs = ModuleUtil::filterInstalledModule(Module::model()->fetchAllSortByPk("module"), $moduleDirs);
            }

            $modules = ModuleUtil::initModuleParameters($moduleDirs);
        } else {
            $modules = Module::model()->fetchAll(array("order" => "iscore ,installdate desc"));

            foreach ($modules as $index => $module) {
                $menu = Menu::model()->fetchByModule($module["module"]);

                if (!empty($menu)) {
                    $route = $menu["m"] . "/" . $menu["c"] . "/" . $menu["a"];
                    $param = StringUtil::splitParam($menu["param"]);
                    $module["managerUrl"] = Ibos::app()->urlManager->createUrl($route, $param);
                } else {
                    $module["managerUrl"] = "";
                }

                $modules[$index] = $module;
            }
        }

        $data = array("modules" => $modules);
        $this->render("module" . ucfirst($moduleType), $data);
    }

    public function actionInstall()
    {
        $moduleName = EnvUtil::getRequest("module");
        $status = ModuleUtil::install($moduleName);

        if ($status) {
            $jumpUrl = $this->createUrl("module/manager");
            CacheUtil::update();
            $this->success(Ibos::lang("Install module success"), $jumpUrl);
        } else {
            $this->error(Ibos::lang("Install module failed"));
        }
    }

    public function actionUninstall()
    {
        $moduleName = EnvUtil::getRequest("module");

        if (Ibos::app()->getRequest()->getIsAjaxRequest()) {
            $status = ModuleUtil::uninstall($moduleName);
            CacheUtil::update();
            $this->ajaxReturn(array("IsSuccess" => (bool) $status), "json");
        }
    }

    public function actionStatus()
    {
        $moduleStatus = EnvUtil::getRequest("type");
        $module = EnvUtil::getRequest("module");

        if (Ibos::app()->getRequest()->getIsAjaxRequest()) {
            $status = 0;

            if ($moduleStatus == "disabled") {
                $status = 1;
            }

            $changeStatus = Module::model()->modify($module, array("disabled" => $status));
            Nav::model()->updateAll(array("disabled" => $status), "module = :module", array(":module" => $module));
            CacheUtil::update(array("setting", "nav"));
            ModuleUtil::updateConfig($module);
            $this->ajaxReturn(array("IsSuccess" => $changeStatus), "json");
        }
    }
}
