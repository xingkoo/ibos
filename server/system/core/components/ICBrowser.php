<?php

class ICBrowser extends CApplicationComponent
{
    /**
     * 浏览器名称
     * @var string
     * @access private
     */
    private $name;
    /**
     * 浏览器版本
     * @var string
     * @access private
     */
    private $version;
    /**
     * 用户所在系统平台
     * @var string
     * @access private
     */
    private $platform;
    /**
     * 用户接口识别的字符串，通过$_SERVER['HTTP_USER_AGENT']变量获得
     * @var string
     * @access private
     */
    private $userAgent;

    public function init()
    {
        parent::init();
        $this->detect();
    }

    protected function detect()
    {
        $userAgent = null;

        if (isset($_SERVER["HTTP_USER_AGENT"])) {
            $userAgent = strtolower($_SERVER["HTTP_USER_AGENT"]);
        }

        if (preg_match("/opera/", $userAgent)) {
            $name = "opera";
        } else if (preg_match("/chrome/", $userAgent)) {
            $name = "chrome";
        } else if (preg_match("/apple/", $userAgent)) {
            $name = "safari";
        } else if (preg_match("/msie/", $userAgent)) {
            $name = "msie";
        } else {
            if (preg_match("/mozilla/", $userAgent) && !preg_match("/compatible/", $userAgent)) {
                $name = "mozilla";
            } else {
                $name = "unrecognized";
            }
        }

        if (preg_match("/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/", $userAgent, $matches)) {
            $version = $matches[1];
        } else {
            $version = "unknown";
        }

        if (preg_match("/linux/", $userAgent)) {
            $platform = "linux";
        } elseif (preg_match("/macintosh|mac os x/", $userAgent)) {
            $platform = "mac";
        } elseif (preg_match("/windows|win32/", $userAgent)) {
            $platform = "windows";
        } else {
            $platform = "unrecognized";
        }

        $this->name = $name;
        $this->version = $version;
        $this->platform = $platform;
        $this->userAgent = $userAgent;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getPlatform()
    {
        return $this->platform;
    }

    public function getUserAgent()
    {
        return $this->userAgent;
    }
}
