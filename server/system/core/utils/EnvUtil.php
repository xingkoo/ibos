<?php

class EnvUtil
{
    /**
     * 手机浏览器列表
     * @staticvar array 
     */
    private static $mobileBrowserList = array("iphone", "android", "phone", "mobile", "wap", "netfront", "java", "opera mobi", "opera mini", "ucweb", "windows ce", "symbian", "series", "webos", "sony", "blackberry", "dopod", "nokia", "samsung", "palmsource", "xda", "pieplus", "meizu", "midp", "cldc", "motorola", "foma", "docomo", "up.browser", "up.link", "blazer", "helio", "hosin", "huawei", "novarra", "coolpad", "webos", "techfaith", "palmsource", "alcatel", "amoi", "ktouch", "nexian", "ericsson", "philips", "sagem", "wellcom", "bunjalloo", "maui", "smartphone", "iemobile", "spice", "bird", "zte-", "longcos", "pantech", "gionee", "portalmmm", "jig browser", "hiptop", "benq", "haier", "^lct", "320x320", "240x320", "176x220");
    /**
     * 平板标识列表
     * @staticvar array 
     */
    private static $padList = array("pad", "gt-p1000");

    public static function getClientIp()
    {
        $ip = $_SERVER["REMOTE_ADDR"];

        if (getenv("HTTP_CLIENT_IP")) {
            $clientIp = getenv("HTTP_CLIENT_IP");
            $matcheClientIp = preg_match("/^([0-9]{1,3}\.){3}[0-9]{1,3}$/", $clientIp);

            if ($matcheClientIp) {
                $ip = $clientIp;
            }
        } else {
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]) && preg_match_all("#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s", $_SERVER["HTTP_X_FORWARDED_FOR"], $matches)) {
                foreach ($matches[0] as $xip) {
                    if (!preg_match("#^(10|172\.16|192\.168)\.#", $xip)) {
                        $ip = $xip;
                        break;
                    }
                }
            }
        }

        return $ip;
    }

    public static function ipBanned($onlineip)
    {
        CacheUtil::load("ipbanned");
        $ipBanned = Ibos::app()->setting->get("cache/ipbanned");

        if (empty($ipBanned)) {
            return false;
        } else {
            if ($ipBanned["expiration"] < TIMESTAMP) {
                CacheUtil::update("ipbanned");
                CacheUtil::load("ipbanned", true);
                $ipBanned = Ibos::app()->setting->get("cache/ipbanned");
            }

            return preg_match("/^(" . $ipBanned["regexp"] . ")$/", $onlineip);
        }

        return preg_match("/^(" . $ipBanned["regexp"] . ")$/", $onlineip);
    }

    public static function checkInMobile()
    {
        $userAgent = strtolower($_SERVER["HTTP_USER_AGENT"]);

        if (StringUtil::istrpos($userAgent, self::$padList)) {
            return false;
        }

        $value = StringUtil::istrpos($userAgent, self::$mobileBrowserList, true);

        if ($value) {
            Ibos::app()->setting->set("mobile", $value);
            return true;
        }

        return false;
    }

    public static function checkInApp()
    {
        $route = Ibos::app()->getUrlManager()->parseUrl(Ibos::app()->getRequest());

        if (!empty($route)) {
            list($module) = explode("/", $route);

            if (strtolower($module) == "mobile") {
                return true;
            }
        }

        return false;
    }

    public static function checkInDashboard()
    {
        $route = Ibos::app()->getUrlManager()->parseUrl(Ibos::app()->getRequest());

        if (!empty($route)) {
            $notIn = strpos($route, "dashboard") === false;

            if ($notIn) {
                return false;
            } else {
                return true;
            }
        }

        return false;
    }

    public static function referer($default = "")
    {
        $referer = Ibos::app()->setting->get("referer");
        $default = (empty($default) ? Ibos::app()->urlManager->createUrl("main/default/index") : $default);
        $referer = (!empty($_GET["referer"]) ? $_GET["referer"] : (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : $default));
        $loginPage = Ibos::app()->urlManager->createUrl("user/default/login");

        if (strpos($referer, $loginPage)) {
            $referer = $default;
        }

        $referer = StringUtil::ihtmlSpecialChars($referer, ENT_QUOTES);
        $referer = strip_tags(str_replace("&amp;", "&", $referer));
        Ibos::app()->setting->set("referer", $referer);
        return $referer;
    }

    public static function getRequest($key, $type = "GP")
    {
        $type = strtoupper($type);
        $request = Ibos::app()->request;

        switch ($type) {
            case "G":
                $var = $request->getQuery($key);
                break;
    
            case "P":
                $var = $request->getPost($key);
                break;
    
            case "C":
                $var = (isset($_COOKIE[$key]) ? $_COOKIE[$key] : null);
                break;
    
            default:
                $var = $request->getParam($key);
                break;
        }

        return $var;
    }

    public static function formHash()
    {
        $global = Ibos::app()->setting->toArray();
        $hashAdd = (defined("IN_DASHBOARD") ? "Only For IBOS Admin DASHBOARD" : "");
        return substr(md5(substr($global["timestamp"], 0, -7) . Ibos::app()->user->username . Ibos::app()->user->uid . $global["authkey"] . $hashAdd), 8, 8);
    }

    public static function submitCheck($var, $allowGet = 0)
    {
        if (EnvUtil::getRequest($var) === null) {
            return false;
        } else {
            $isPostRequest = Ibos::app()->request->getIsPostRequest();
            $emptyFlashProtected = empty($_SERVER["HTTP_X_FLASH_VERSION"]);
            $emptyReferer = empty($_SERVER["HTTP_REFERER"]);
            $formHash = Ibos::app()->request->getParam("formhash");
            $formHashCorrect = !empty($formHash) && ($formHash == EnvUtil::formHash());
            $formPostCorrect = $isPostRequest && $formHashCorrect && $emptyFlashProtected && $emptyReferer;
            $refererEqualsHost = preg_replace("/https?:\/\/([^\:\/]+).*/i", "\1", $_SERVER["HTTP_REFERER"]) == preg_replace("/([^\:]+).*/", "\1", $_SERVER["HTTP_HOST"]);
            if ($allowGet || $formPostCorrect || $refererEqualsHost) {
                return true;
            } else {
                throw new RequestException(Ibos::lang("Data type invalid", "error"));
            }
        }
    }

    public static function getSystemInfo()
    {
        $info = array("operating_system" => php_uname("s"), "runtime_environment" => $_SERVER["SERVER_SOFTWARE"], "php_runtime" => php_sapi_name(), "upload_size" => ini_get("upload_max_filesize"), "execution_time" => ini_get("max_execution_time"), "server_time" => date("Y-n-j H:i:s"), "beijing_time" => gmdate("Y-n-j- H:i:s", time() + (8 * 3600)), "server_domain" => $_SERVER["SERVER_NAME"], "server_ip" => gethostbyname($_SERVER["SERVER_NAME"]), "disk_space" => round(disk_free_space(".") / (1024 * 1024), 2) . "M", "register_globals" => get_cfg_var("register_globals") == "1" ? "open" : "closed", "magic_quotes_gpc" => 1 === get_magic_quotes_gpc() ? true : false, "magic_quotes_runtime" => 1 === get_magic_quotes_runtime() ? true : false);
        return $info;
    }

    public static function getSocketOpen($hostName, &$errno, &$errstr, $port = 80, $timeout = 15)
    {
        $fp = "";

        if (function_exists("fsockopen")) {
            $fp = @fsockopen($hostName, $port, $errno, $errstr, $timeout);
        } else if (function_exists("pfsockopen")) {
            $fp = @pfsockopen($hostName, $port, $errno, $errstr, $timeout);
        } elseif (function_exists("stream_socket_client")) {
            $fp = @stream_socket_client($hostName . ":" . $port, $errno, $errstr, $timeout);
        }

        return $fp;
    }

    public static function getVisitorClient()
    {
        return "0";
    }

    public static function getFromClient($type = 0, $module = "weibo")
    {
        if ($module != "weibo") {
            $modules = Ibos::app()->getEnabledModule();

            if (isset($modules[$module])) {
                return "来自" . $modules[$module]["name"];
            } else {
                return "来自未知客户端";
            }
        }

        $type = intval($type);
        $clientType = array("来自网页", "来自手机版", "来自Android客户端", "来自iPhone客户端", "来自iPad客户端", "来自win.Phone客户端");

        if (in_array($type, array_keys($clientType))) {
            return $clientType[$type];
        } else {
            return $clientType[0];
        }
    }

    public static function iExit($msg = 0)
    {
        header("Content-Type:text/html; charset=" . CHARSET);
        exit($msg);
    }
}
