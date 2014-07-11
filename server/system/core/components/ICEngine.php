<?php

abstract class ICEngine
{
    /**
     * 当前引擎处理过后的配置文件
     * @var mixed 
     */
    private $_engineConfig;
    /**
     * 这里存放实例化后的引擎类型
     * @var array 
     */
    private $_engines;
    private $_mainConfig;

    public function __construct($appConfig)
    {
        if (is_file(PATH_ROOT . "/system/config/config.php")) {
            $mainConfigFile = PATH_ROOT . "/system/config/config.php";
            $mainConfig = require_once ($mainConfigFile);
            $this->_mainConfig = $mainConfig;
            $this->_engineConfig = $this->initConfig($appConfig, $mainConfig);
            $this->init();
        } else {
            header("Location:./install/");
        }
    }

    protected function getEngine($obj)
    {
        $key = strtolower($obj);

        if (isset($this->_engines[$key])) {
            return $this->_engines[$key];
        } else {
            if (1 < ($n = func_num_args())) {
                $args = func_get_args();

                if ($n === 2) {
                    $object = new $obj($args[1]);
                } elseif ($n === 3) {
                    $object = new $obj($args[1], $args[2]);
                } elseif ($n === 4) {
                    $object = new $obj($args[1], $args[2], $args[3]);
                } else {
                    unset($args[0]);
                    $class = new ReflectionClass($obj);
                    $object = call_user_func_array(array($class, "newInstance"), $args);
                }
            } else {
                $object = new $obj();
            }

            $this->_engines[$key] = $object;
            return $object;
        }
    }

    public function getMainConfig()
    {
        return (array) $this->_mainConfig;
    }

    public function getEngineConfig()
    {
        return (array) $this->_engineConfig;
    }

    protected function init()
    {
        return true;
    }

    abstract public function initConfig($appConfig, $mainConfig);

    abstract public function IO();
}
