<?php

class ICCache extends CCache
{
    /**
     * 缓存组件是否可用
     * @var boolean
     */
    public $enable = false;
    /**
     * 缓存组件的类型
     * @var string
     */
    public $type;
    /**
     * 读取config.php里的cache配置
     * @var array
     */
    private $_config;
    /**
     * 根据当前系统环境所匹配的缓存处理对象
     * @var mixed
     */
    private $_instance;
    /**
     * 缓存组件的检测结果数组
     * @var array
     */
    private $_extension = array();
    /**
     * 在前缀的基础上再添加自定义前缀
     * @var string
     */
    private $_userPrefix;
    /**
     * 系统配置里自动生成的前缀
     * @var type
     */
    private $_prefix;

    public function init()
    {
        $mainConfig = Ibos::engine()->getMainConfig();
        $config = $mainConfig["cache"];
        $this->_config = $config;
        $this->setExtension();
        $this->_prefix = (empty($config["prefix"]) ? substr(md5($_SERVER["HTTP_HOST"]), 0, 6) . "_" : $config["prefix"]);

        foreach (array("eaccelerator", "apc", "xcache", "wincache", "filecache") as $cache) {
            if (!is_object($this->_instance) && $this->_extension[$cache] && $this->config[strtolower($cache)]) {
                $className = ucfirst($cache);
                $this->_instance = new $className();
                $this->_instance->init(null);
                break;
            }
        }

        if (is_object($this->_instance)) {
            $this->enable = true;
            $this->type = get_class($this->_instance);

            if (strtolower($this->type) == "filecache") {
                $this->_prefix = "";
            }
        } else {
            throw new EnvException(Ibos::lang("Cache init error", "error"));
        }
    }

    public function get($key, $prefix = "")
    {
        static $getMulti;
        $result = false;

        if ($this->enable) {
            if (!isset($getMulti)) {
                $getMulti = method_exists($this->_instance, "getMulti");
            }

            $this->_userPrefix = $prefix;

            if (is_array($key)) {
                if ($getMulti) {
                    $result = $this->_instance->getMulti($this->key($key));
                    if (($result !== false) && !empty($result)) {
                        $_result = array();

                        foreach ((array) $result as $_key => $value) {
                            $_result[$this->trimKey($_key)] = $value;
                        }

                        $result = $_result;
                    }
                } else {
                    $result = array();
                    $_result = false;

                    foreach ($key as $id) {
                        if ((($_result = $this->_instance->getValue($this->key($id))) !== false) && isset($_result)) {
                            $result[$id] = $_result;
                        }
                    }
                }

                if (empty($result)) {
                    $result = false;
                }
            } else {
                $result = $this->_instance->getValue($this->key($key));

                if (!isset($result)) {
                    $result = false;
                }
            }
        }

        return $result;
    }

    public function set($key, $value, $ttl = 0, $prefix = "")
    {
        $result = false;

        if ($value === false) {
            $value = "";
        }

        if ($this->enable) {
            !is_object($prefix) ? $this->_userPrefix = $prefix : "";
            $result = $this->_instance->setValue($this->key($key), $value, $ttl);
        }

        return $result;
    }

    public function rm($key, $prefix = "")
    {
        $result = false;

        if ($this->enable) {
            $this->_userPrefix = $prefix;
            $key = $this->key($key);

            foreach ((array) $key as $id) {
                $result = $this->_instance->deleteValue($id);
            }
        }

        return $result;
    }

    public function clear()
    {
        $result = false;
        if ($this->enable && method_exists($this->_instance, "flushValues")) {
            $result = $this->_instance->flushValues();
        }

        return $result;
    }

    public function inc($key, $step = 1)
    {
        static $hasInc;
        $result = false;

        if ($this->enable) {
            if (!isset($hasInc)) {
                $hasInc = method_exists($this->_instance, "inc");
            }

            if ($hasInc) {
                $result = $this->_instance->inc($this->key($key), $step);
            } elseif (($data = $this->_instance->get($key)) !== false) {
                $result = ($this->_instance->set($key, $data + $step) !== false ? $this->_instance->get($key) : false);
            }
        }

        return $result;
    }

    public function dec($key, $step = 1)
    {
        static $hasDec;
        $ret = false;

        if ($this->enable) {
            if (!isset($hasDec)) {
                $hasDec = method_exists($this->_instance, "dec");
            }

            if ($hasDec) {
                $ret = $this->_instance->dec($this->key($key), $step);
            } elseif (($data = $this->_instance->get($key)) !== false) {
                $ret = ($this->_instance->set($key, $data - $step) !== false ? $this->_instance->get($key) : false);
            }
        }

        return $ret;
    }

    public function getExtension()
    {
        return $this->_extension;
    }

    public function getConfig()
    {
        return $this->_config;
    }

    private function key($str)
    {
        $prefix = $this->_prefix . $this->_userPrefix;

        if (is_array($str)) {
            foreach ($str as &$val) {
                $val = $prefix . $val;
            }
        } else {
            $str = $prefix . $str;
        }

        return $str;
    }

    private function trimKey($str)
    {
        return substr($str, strlen($this->_prefix . $this->_userPrefix));
    }

    private function setExtension()
    {
        $params = Ibos::app()->params;
        $cacheopen = $params["cacheopen"];
        $this->_extension["apc"] = $cacheopen && function_exists("apc_cache_info") && @apc_cache_info();
        $this->_extension["eaccelerator"] = $cacheopen && function_exists("eaccelerator_get");
        $this->_extension["xcache"] = $cacheopen && function_exists("xcache_get");
        $this->_extension["wincache"] = $cacheopen && extension_loaded("wincache");
        $this->_extension["filecache"] = (LOCAL ? 1 : 0);
    }
}
