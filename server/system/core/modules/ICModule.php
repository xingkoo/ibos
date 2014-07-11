<?php

class ICModule extends CWebModule
{
    public static function getPluralCamelCasedName()
    {
        $name = get_called_class();
        $name = substr($name, 0, strlen($name) - strlen("Module"));
        return $name;
    }

    public static function getModuleObjects()
    {
        $moduleConfig = Ibos::app()->getModules();
        $modules = array();

        foreach ($moduleConfig as $moduleName => $info) {
            $module = Ibos::app()->findModule($moduleName);
            if (isset($info["modules"]) && is_array($info["modules"])) {
                foreach ($info["modules"] as $nestedModuleName => $nestedInfo) {
                    $modules[$nestedModuleName] = $module->getModule($nestedModuleName);
                }
            }

            $modules[$moduleName] = $module;
        }

        return $modules;
    }

    public function getName()
    {
        $calledClassName = get_called_class();
        return $calledClassName::getDirectoryName();
    }

    public static function getDirectoryName()
    {
        $name = get_called_class();
        $name = substr($name, 0, strlen($name) - strlen("Module"));
        $name = lcfirst($name);
        return $name;
    }
}
