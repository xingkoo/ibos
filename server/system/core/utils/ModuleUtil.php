<?php

class ModuleUtil
{
    const MODULE_ALIAS = "modules";
    const INSTALL_PATH_ALIAS = "install";
    const UNINSTALL_PATH_ALIAS = "uninstall";
    const DS = DIRECTORY_SEPARATOR;

    /**
     * 核心模块
     * @var array 
     */
    private static $_coreModule = array("main", "user", "department", "position", "organization", "message", "dashboard");
    /**
     * 核心依赖模块，相比于核心模块的不可关闭与不可卸载来说，它能关闭但不能卸载
     * 因为这种类型的模块在代码与视图层面上与系统核心模块有耦合性
     * @var array 
     */
    private static $_sysDependModule = array("weibo");

    public static function getIsEnabled($moduleName)
    {
        static $modules = array();

        if (empty($modules)) {
            $modules = Ibos::app()->getEnabledModule();
        }

        return isset($modules[$moduleName]);
    }

    public static function getCoreModule()
    {
        return self::$_coreModule;
    }

    public static function getDependModule()
    {
        return self::$_sysDependModule;
    }

    public static function install($moduleName)
    {
        defined("IN_MODULE_ACTION") || define("IN_MODULE_ACTION", true);
        $checkError = self::check($moduleName);

        if (!empty($checkError)) {
            throw new EnvException($checkError);
        }

        $installPath = self::getInstallPath($moduleName);
        $modelSqlFile = $installPath . "model.sql";

        if (file_exists($modelSqlFile)) {
            $modelSql = file_get_contents($modelSqlFile);
            self::executeSql($modelSql);
        }

        $config = require ($installPath . "config.php");
        $icon = self::getModulePath() . $moduleName . "/static/image/icon.png";

        if (is_file($icon)) {
            $config["param"]["icon"] = 1;
        } else {
            $config["param"]["icon"] = 0;
        }

        if (!isset($config["param"]["category"])) {
            $config["param"]["category"] = "";
        }

        if (isset($config["param"]["indexShow"]) && isset($config["param"]["indexShow"]["link"])) {
            $config["param"]["url"] = $config["param"]["indexShow"]["link"];
        } else {
            $config["param"]["url"] = "";
        }

        $configs = CJSON::encode($config);
        $record = array("module" => $moduleName, "name" => $config["param"]["name"], "url" => $config["param"]["url"], "category" => $config["param"]["category"], "version" => $config["param"]["version"], "description" => $config["param"]["description"], "icon" => $config["param"]["icon"], "config" => $configs, "installdate" => TIMESTAMP);

        if (in_array($moduleName, self::getCoreModule())) {
            $record["iscore"] = 1;
        } else if (in_array($moduleName, self::getDependModule())) {
            $record["iscore"] = 2;
        } else {
            $record["iscore"] = 0;
        }

        $insertStatus = Module::model()->add($record);
        CacheUtil::rm("module");
        if ($insertStatus && isset($config["authorization"])) {
            self::updateAuthorization($config["authorization"], $moduleName, $config["param"]["category"]);
        }

        $extentionScript = $installPath . "extention.php";

        if (file_exists($extentionScript)) {
            include_once ($extentionScript);
        }

        return $insertStatus;
    }

    public static function uninstall($moduleName)
    {
        defined("IN_MODULE_ACTION") || define("IN_MODULE_ACTION", true);
        $record = Module::model()->fetchByPk($moduleName);

        if (!empty($record)) {
            Module::model()->deleteByPk($moduleName);
            CacheUtil::rm("module");
        }

        $uninstallPath = self::getUninstallPath($moduleName);
        $extentionScript = $uninstallPath . "extention.php";
        $modelSqlFile = $uninstallPath . "model.sql";

        if (file_exists($modelSqlFile)) {
            $modelSql = file_get_contents($modelSqlFile);
            self::executeSql($modelSql);
        }

        if (is_file($extentionScript)) {
            include_once ($extentionScript);
        }

        return true;
    }

    public static function getModuleDirs()
    {
        $modulePath = self::getModulePath();
        $dirs = (array) glob($modulePath . "*");
        $moduleDirs = array();

        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                $d = basename($dir);
                $moduleDirs[] = $d;
            }
        }

        return $moduleDirs;
    }

    public static function getModulePath()
    {
        static $path;

        if (!$path) {
            $path = Ibos::getPathOfAlias("application") . self::DS . self::MODULE_ALIAS . self::DS;
        }

        return $path;
    }

    public static function getInstallPath($module)
    {
        return self::getModulePath() . $module . self::DS . self::INSTALL_PATH_ALIAS . self::DS;
    }

    public static function getUninstallPath($module)
    {
        return self::getModulePath() . $module . self::DS . self::UNINSTALL_PATH_ALIAS . self::DS;
    }

    public static function filterInstalledModule(array $installedModule, array $moduleDirs)
    {
        $dirs = array();

        foreach ($moduleDirs as $index => $moduleName) {
            if (array_key_exists($moduleName, $installedModule)) {
                continue;
            } else {
                $dirs[] = $moduleName;
            }
        }

        return $dirs;
    }

    public static function initModuleParameter($moduleName)
    {
        defined("IN_MODULE_ACTION") || define("IN_MODULE_ACTION", true);
        $param = array();
        $installPath = self::getInstallPath($moduleName);

        if (is_dir($installPath)) {
            $file = $installPath . "config.php";
            if (is_file($file) && is_readable($file)) {
                $config = include_once ($file);
            }

            if (isset($config) && is_array($config)) {
                $param = (array) $config["param"];
                $icon = self::getModulePath() . $moduleName . "/static/image/icon.png";

                if (is_file($icon)) {
                    $param["icon"] = 1;
                } else {
                    $param["icon"] = 0;
                }
            }
        }

        return $param;
    }

    public static function initModuleParameters(array $moduleDirs)
    {
        $modules = array();

        foreach ($moduleDirs as $index => $moduleName) {
            $param = self::initModuleParameter($moduleName);

            if (!empty($param)) {
                $modules[$moduleName] = $param;
            }
        }

        return $modules;
    }

    public static function updateConfig($module = "")
    {
        static $execute = false;

        if (!$execute) {
            defined("IN_MODULE_ACTION") || define("IN_MODULE_ACTION", true);
            $updateList = (empty($module) ? array() : (is_array($module) ? $module : array($module)));
            $modules = array();
            $installedModule = Ibos::app()->getEnabledModule();

            if (!$updateList) {
                foreach ($installedModule as $module) {
                    $modules[] = $module["module"];
                }
            } else {
                $modules = $updateList;
            }

            foreach ($modules as $name) {
                $installPath = self::getInstallPath($name);
                $file = $installPath . "config.php";
                if (is_file($file) && is_readable($file)) {
                    $config = include_once ($file);
                    if (isset($config) && is_array($config) && array_key_exists($name, $installedModule)) {
                        $icon = self::getModulePath() . $name . "/static/image/icon.png";

                        if (is_file($icon)) {
                            $config["param"]["icon"] = 1;
                        } else {
                            $config["param"]["icon"] = 0;
                        }

                        if (!isset($config["param"]["category"])) {
                            $config["param"]["category"] = "";
                        }

                        $data = array("updatedate" => TIMESTAMP, "config" => CJSON::encode($config), "icon" => $config["param"]["icon"], "name" => $config["param"]["name"], "category" => $config["param"]["category"], "version" => $config["param"]["version"], "description" => $config["param"]["description"]);
                        Module::model()->modify($name, $data);

                        if (isset($config["authorization"])) {
                            self::updateAuthorization($config["authorization"], $name, $config["param"]["category"]);
                        }
                    }
                }
            }

            CacheUtil::rm("module");
            $execute = true;
        }

        return $execute;
    }

    public static function updateAuthorization($authItem, $moduleName, $category)
    {
        return AuthUtil::updateAuthorization($authItem, $moduleName, $category);
    }

    private static function check($moduleName)
    {
        $error = "";
        $record = Module::model()->fetchByPk($moduleName);

        if (!empty($record)) {
            $error = Ibos::lang("This module has been installed", "error");
            return $error;
        }

        $installPath = self::getInstallPath($moduleName);

        if (!is_dir($installPath)) {
            $error = Ibos::lang("Install dir does not exists", "error");
            return $error;
        }

        if (!file_exists($installPath . "config.php")) {
            $error = Ibos::lang("Module config missing", "error");
            return $error;
        }

        $configFile = $installPath . "config.php";
        $config = (array) include_once ($configFile);
        $configFormatCorrect = isset($config["param"]) && isset($config["configure"]);

        if (!$configFormatCorrect) {
            $error = Ibos::lang("Module config format error", "error");
            return $error;
        }

        return $error;
    }

    private static function executeSql($sql)
    {
        $sqls = StringUtil::splitSql($sql);
        $command = Ibos::app()->db->createCommand();

        if (is_array($sqls)) {
            foreach ($sqls as $sql) {
                if (trim($sql) != "") {
                    $command->setText($sql)->execute();
                }
            }
        } else {
            $command->setText($sqls)->execute();
        }

        return true;
    }
}
