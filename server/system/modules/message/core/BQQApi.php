<?php

class BQQApi
{
    const CORPORATION_BASE_URL = "https://openapi.b.qq.com/api/corporation/get";
    const REFRESH_TOKEN_URL = "https://openapi.b.qq.com/oauth2/companyRefresh";
    const ADD_ACCOUNT_URL = "https://openapi.b.qq.com/api/dept/adduser";
    const SET_USERSTATUS_URL = "https://openapi.b.qq.com/api/dept/setuserstatus";
    const SEND_TIPS_URL = "https://openapi.b.qq.com/api/tips/send";
    const USER_LIST_URL = "https://openapi.b.qq.com/api/user/list";
    const VERIFY_HASH_URL = "https://openapi.b.qq.com/api/login/verifyhashskey";

    /**
     * 公共参数
     * @var array 
     */
    private $_publicParam = array("company_id" => "{id}", "company_token" => "{token}", "app_id" => "{appid}", "client_ip" => "{ip}", "oauth_version" => "2");

    public function __construct($param = array())
    {
        $publicParam = &$this->_publicParam;

        foreach ($param as $key => $value) {
            if (isset($publicParam[$key])) {
                $publicParam[$key] = $value;
            }
        }
    }

    public function addAccount($acountData)
    {
        return $this->fetchResult(self::ADD_ACCOUNT_URL, array_merge($this->_publicParam, $acountData), "post");
    }

    public function setStatus($openId, $flag)
    {
        $param = array_merge(array("open_id" => $openId, "status" => intval($flag)), $this->_publicParam);
        return $this->fetchResult(self::SET_USERSTATUS_URL, $param, "post");
    }

    public function sendNotify($param)
    {
        $param = array_merge($param, $this->_publicParam);
        return $this->fetchResult(self::SEND_TIPS_URL, $param, "post");
    }

    public function getCorBase()
    {
        $url = $this->buildUrl(self::CORPORATION_BASE_URL);
        return $this->fetchResult($url);
    }

    public function getVerifyStatus($param)
    {
        $url = $this->buildUrl(self::VERIFY_HASH_URL, $param);
        return $this->fetchResult($url);
    }

    public function getUserList($param)
    {
        $url = $this->buildUrl(self::USER_LIST_URL, $param);
        return $this->fetchResult($url);
    }

    public function getRefreshToken($param)
    {
        $url = $this->buildUrl(self::REFRESH_TOKEN_URL, $param, false);
        return $this->fetchResult($url);
    }

    protected function fetchResult($url, $param = array(), $type = "get")
    {
        return ApiUtil::getInstance()->fetchResult($url, $param, $type);
    }

    protected function buildUrl($url, $param = array(), $includePublic = true)
    {
        if ($includePublic) {
            $param = array_merge($this->_publicParam, $param);
        }

        return ApiUtil::getInstance()->buildUrl($url, $param);
    }
}
