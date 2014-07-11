<?php

class LocalEngine extends ICEngine
{
    public function initConfig($appConfig, $mainConfig)
    {
        $connectionString = "mysql:host={$mainConfig["db"]["host"]};port={$mainConfig["db"]["port"]};dbname={$mainConfig["db"]["dbname"]}";
        $config = array(
            "runtimePath" => PATH_ROOT . DIRECTORY_SEPARATOR . "data/runtime",
            "language"    => $mainConfig["env"]["language"],
            "theme"       => $mainConfig["env"]["theme"],
            "params"      => array("installed" => $mainConfig["env"]["installed"]),
            "components"  => array(
                "db" => array("connectionString" => $connectionString, "username" => $mainConfig["db"]["username"], "password" => $mainConfig["db"]["password"], "tablePrefix" => $mainConfig["db"]["tableprefix"], "charset" => $mainConfig["db"]["charset"])
                )
            );
        return CMap::mergeArray($appConfig, $config);
    }

    protected function init()
    {
        Yii::setPathOfAlias("data", PATH_ROOT . DIRECTORY_SEPARATOR . "data");
        Yii::setPathOfAlias("engineDriver", Yii::getPathOfAlias("ext.enginedriver.local"));
        Yii::import("engineDriver.*");
    }

    public function IO()
    {
        return $this->getEngine("LocalIO");
    }
}
