<?php

class MainUtil
{
    public static function setCookie($var, $value = "", $life = 0, $prefix = 1, $httpOnly = false)
    {
        $global = Ibos::app()->setting->toArray();
        Ibos::app()->setting->set("cookie/" . $var, $value);
        $config = $global["config"]["cookie"];
        $var = ($prefix ? $config["cookiepre"] : "") . $var;
        $_COOKIE[$var] = $value;
        if (($value == "") || ($life < 0)) {
            $value = "";
            $life = -1;
        }

        if (IN_MOBILE) {
            $httpOnly = false;
        }

        $life = (0 < $life ? $global["timestamp"] + $life : ($life < 0 ? $global["timestamp"] - 31536000 : 0));
        $path = $config["cookiepath"];
        $secure = ($_SERVER["SERVER_PORT"] == 443 ? 1 : 0);
        @setcookie($var, $value, $life, $path, $config["cookiedomain"], $secure, $httpOnly);
    }

    public static function getCookie($var, $prefix = 1)
    {
        $global = Ibos::app()->setting->toArray();
        $config = $global["config"]["cookie"];
        $var = ($prefix ? $config["cookiepre"] : "") . $var;

        if (array_key_exists($var, $_COOKIE)) {
            return $_COOKIE[$var];
        } else {
            return null;
        }
    }

    public static function clearCookies()
    {
        $global = Ibos::app()->setting->toArray();

        foreach ($global["cookie"] as $key => &$value) {
            self::setCookie($key);
            $value = "";
        }

        Ibos::app()->setting->copyFrom($global);
    }

    public static function getIncentiveWord()
    {
        $words = Ibos::getLangSource("incentiveword");
        $luckyOne = array_rand($words);
        $source = $words[$luckyOne];
        return Ibos::lang("Custom title", "main.default") . $source[array_rand($source)];
    }

    public static function execApiMethod($method, $moduleArr)
    {
        $data = array();
        $paramNum = func_num_args();

        if (2 < $paramNum) {
            $params = func_get_args();
            $args = array_slice($params, 2, count($params));
        } else {
            $args = array();
        }

        $enableModule = Module::model()->fetchAllEnabledModule();

        foreach ($moduleArr as $module) {
            if (array_key_exists($module, $enableModule)) {
                $class = ucfirst($module) . "Api";

                if (class_exists($class)) {
                    $api = new $class();

                    if ($args) {
                        $data[$module] = call_user_func_array(array($api, $method), $args);
                    } else {
                        $data[$module] = $api->{$method}();
                    }
                }
            }
        }

        return $data;
    }

    public static function checkLicenseLimit($logout = false)
    {
        if (!defined("LICENCE_LIMIT")) {
            exit("授权信息错误，请联系管理员检查");
        }

        $count = intval(User::model()->count("`status` IN (1,0)"));

        if (LICENCE_LIMIT < $count) {
            $msg = "您的授权只支持" . LICENCE_LIMIT . "用户以内登录，请设置少于" . LICENCE_LIMIT . "个用户帐号，扩展人数或商用授权请访问 http://www.ibos.com.cn 申请。";

            if ($logout) {
                Ibos::app()->user->logout();
            }

            Ibos::app()->getController()->error($msg, "", array(
                "autoJump"         => false,
                "jumpLinksOptions" => array("官网" => "http://www.ibos.com.cn")
            ), Ibos::app()->request->getIsAjaxRequest());
            exit();
        }
    }
}
