<?php

class CloudApi extends ApiUtil
{
    protected $setting = array();

    public static function getInstance($className = "CloudApi")
    {
        return parent::getInstance($className);
    }

    public function __construct()
    {
        $setting = Ibos::app()->setting->get("setting/iboscloud");
        $this->setSetting($setting);
    }

    public function getSetting()
    {
        return $this->setting;
    }

    public function setSetting($setting)
    {
        $this->setting = $setting;
    }

    public function isOpen()
    {
        $setting = $this->getSetting();
        return $setting["isopen"] == "1";
    }

    public function build($route, $param = array())
    {
        $param = array_merge($this->getAuthParam(), $param);
        return $this->buildUrl($this->getUrl() . $route, $param);
    }

    public function fetch($route, $param = array(), $method = "get")
    {
        $param = array_merge($this->getAuthParam(), $param);
        $url = $this->getUrl() . $route;
        return $this->fetchResult($url, $param, $method);
    }

    public function exists($apiName)
    {
        $setting = $this->getSetting();

        if (!empty($setting["apilist"])) {
            foreach ($setting["apilist"] as $api) {
                if (strcmp($apiName, $api["name"]) == 0) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getUrl()
    {
        $setting = $this->getSetting();
        return $setting["url"];
    }

    private function getAuthParam($build = false)
    {
        $setting = $this->getSetting();
        $time = TIMESTAMP;
        $param = array("appid" => $setting["appid"], "signature" => self::createSignature($setting["appid"], $setting["secret"], $time), "timestamp" => $time);
        return $build ? http_build_query($param) : $param;
    }

    private function createSignature($id, $secret, $time = null)
    {
        return strtoupper(md5($id . $secret . ($time == null ? TIMESTAMP : $time)));
    }
}
