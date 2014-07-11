<?php

class Module extends ICModel
{
    public static function model($className = "Module")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{module}}";
    }

    public function fetchNameByModule($moduleName)
    {
        static $modules;

        if (!$modules) {
            $modules = $this->fetchAllEnabledModule();
        }

        $module = (isset($modules[$moduleName]) ? $modules[$moduleName] : $this->fetchByAttributes(array("module" => $moduleName)));
        return is_array($module) ? $module["name"] : "";
    }

    public function fetchAllNotCoreModule()
    {
        $modules = $this->fetchAllSortByPk("module", array("condition" => "`iscore` = 0 AND `disabled` = 0", "order" => "`sort` ASC"));
        return $modules;
    }

    public function fetchAllClientModule()
    {
        $modules = $this->fetchAllSortByPk("module", array("condition" => "`iscore` = 0 AND `disabled` = 0 AND `category` != ''", "order" => "`sort` ASC"));
        return $modules;
    }

    public function fetchAllEnabledModule()
    {
        $module = CacheUtil::get("module");

        if ($module == false) {
            $criteria = array("condition" => "`disabled` = 0", "order" => "`sort` ASC");
            $module = $this->fetchAllSortByPk("module", $criteria);
            CacheUtil::set("module", $module);
        }

        return $module;
    }
}
