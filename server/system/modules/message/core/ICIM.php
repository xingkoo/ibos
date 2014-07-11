<?php

abstract class ICIM extends CApplicationComponent
{
    const ERROR_INIT = 0;
    const ERROR_PUSH = 1;
    const ERROR_SYNC = 2;
    const ERROR_UNKNOWN = 3;

    /**
     * 每个适配器的配置数组
     * @var array 
     */
    protected $config = array();
    /**
     * 可能出现的错误信息，按const定义的类型推进该error数组
     * @var string 
     */
    protected $error = array();
    /**
     * 处理的用户
     * @var type 
     */
    protected $uid = array();
    /**
     * 推送类型
     * @var string 
     */
    protected $pushType = "";
    /**
     * 推送的内容
     * @var string 
     */
    protected $message = "";
    /**
     * 点击跳转的url
     * @var string 
     */
    protected $url = "";

    abstract public function check();

    abstract public function push();

    abstract public function syncUser();

    abstract public function syncOrg();

    public function __construct($config)
    {
        $this->setConfig($config);
    }

    public function setSyncFlag($flag)
    {
        $this->syncFlag = intval($flag);
    }

    public function getSyncFlag()
    {
        return $this->syncFlag;
    }

    public function setError($msg, $errorLevel = self::ERR_INIT)
    {
        $this->error[$errorLevel][] = $msg;
    }

    public function getError($level = null)
    {
        return empty($level) ? $this->error : (isset($this->error[$level]) ? $this->error[$level] : array("Unknow error"));
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setPushType($type)
    {
        $this->pushType = $type;
    }

    public function getPushType()
    {
        return $this->pushType;
    }

    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    protected function isEnabled($key)
    {
        $cfg = $this->getConfig();
        $key = explode("/", $key);
        $v = &$cfg;

        foreach ($key as $k) {
            if (!isset($v[$k])) {
                return false;
            }

            $v = &$v[$k];
        }

        return $v ? true : false;
    }
}
