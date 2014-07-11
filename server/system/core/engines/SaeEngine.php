<?php

class SaeEngine extends ICEngine
{
    public function initConfig($appConfig, $mainConfig)
    {
        $config = array(
            "language"    => $mainConfig["env"]["language"],
            "runtimePath" => SAE_TMP_PATH,
            "theme"       => $mainConfig["env"]["theme"],
            "components"  => array(
                "db" => array("charset" => $mainConfig["db"]["charset"], "tablePrefix" => $mainConfig["db"]["tableprefix"])
                ),
            "params"      => array("installed" => $mainConfig["env"]["installed"])
            );
        return CMap::mergeArray($appConfig, $config);
    }

    protected function init()
    {
        Ibos::setPathOfAlias("engineDriver", Ibos::getPathOfAlias("ext.enginedriver.sae"));
        $alias = Ibos::getPathOfAlias("engineDriver");
        $classes = array("CMemCache" => $alias . "/caching/CMemCache.php", "CDbCommand" => $alias . "/db/CDbCommand.php", "CDbConnection" => $alias . "/db/CDbConnection.php");
        Ibos::$classMap = CMap::mergeArray(Ibos::$classMap, $classes);
        Ibos::import("engineDriver.*");
    }

    public function IO()
    {
        return $this->getEngine("SAEIO");
    }
}
