<?php

class InitMainModuleBehavior extends CBehavior
{
    /**
     * 允许未登录用户访问的URL
     * @var array
     */
    protected $allowedGuestUserRoutes = array("user/default/login", "user/default/reset", "user/default/logout", "user/default/ajaxlogin", "user/default/checklogin", "dashboard/default/login", "dashboard/default/logout", "mobile/api", "mobile/default/login", "mobile/default/logout", "main/default/getCert", "main/default/unsupportedBrowser");

    public function attach($owner)
    {
        $owner->attachEventHandler("onInitModule", array($this, "handleInitEnvironment"));
        $owner->attachEventHandler("onInitModule", array($this, "handleInitInput"));
        $owner->attachEventHandler("onInitModule", array($this, "handleLoadSysCache"));
        $owner->attachEventHandler("onInitModule", array($this, "handleBeginRequest"));
        $owner->attachEventHandler("onInitModule", array($this, "handleInitSession"));
        $owner->attachEventHandler("onInitModule", array($this, "handleSystemConfigure"));
        $owner->attachEventHandler("onInitModule", array($this, "handleInitCron"));
        $owner->attachEventHandler("onInitModule", array($this, "handleInitOrg"));
        $owner->attachEventHandler("onInitModule", array($this, "handleCheckUpgrade"));
        $owner->attachEventHandler("onInitModule", array($this, "handleCheckLicence"));
    }

    public function handleBeginRequest($event)
    {
        $allowedGuestUserUrls = array();

        foreach ($this->allowedGuestUserRoutes as $allowedGuestUserRoute) {
            $allowedGuestUserUrls[] = Ibos::app()->createUrl($allowedGuestUserRoute);
        }

        $reqestedUrl = Ibos::app()->getRequest()->getUrl();
        $isUrlAllowedToGuests = false;

        foreach ($allowedGuestUserUrls as $url) {
            if (strpos($reqestedUrl, $url) === 0) {
                $isUrlAllowedToGuests = true;
                break;
            }
        }

        $uid = EnvUtil::getRequest("uid");
        $swfHash = EnvUtil::getRequest("hash");
        if ($uid && $swfHash) {
            define("IN_SWFHASH", true);
            $authKey = Ibos::app()->setting->get("config/security/authkey");
            if (empty($uid) || ($swfHash != md5(substr(md5($authKey), 8) . $uid))) {
                exit();
            }
        } elseif (Ibos::app()->user->isGuest) {
            define("IN_SWFHASH", false);

            if (!$isUrlAllowedToGuests) {
                if (IN_DASHBOARD) {
                    Ibos::app()->request->redirect(Ibos::app()->createUrl("dashboard/default/login"));
                } else {
                    Ibos::app()->user->loginRequired();
                }
            }
        }
    }

    public function handleInitEnvironment($event)
    {
        Ibos::app()->performance->startClock();
        Ibos::app()->performance->startMemoryUsageMarker();
        define("STATICURL", Ibos::app()->assetManager->getBaseUrl());
        define("IN_MOBILE", EnvUtil::checkInMobile());
        define("IN_DASHBOARD", EnvUtil::checkInDashboard());
        define("TIMESTAMP", time());
        define("IN_APP", EnvUtil::checkInApp());
        $this->setTimezone();

        if (function_exists("ini_get")) {
            $memorylimit = @ini_get("memory_limit");
            if ($memorylimit && (ConvertUtil::ConvertBytes($memorylimit) < 33554432) && function_exists("ini_set")) {
                ini_set("memory_limit", "128m");
            }
        }

        $global = array(
            "timestamp"  => TIMESTAMP,
            "version"    => VERSION,
            "clientip"   => EnvUtil::getClientIp(),
            "referer"    => "",
            "charset"    => CHARSET,
            "authkey"    => "",
            "newversion" => 0,
            "config"     => array(),
            "setting"    => array(),
            "user"       => array(),
            "cookie"     => array(),
            "session"    => array(),
            "lunar"      => DateTimeUtil::getlunarCalendar(),
            "title"      => MainUtil::getIncentiveWord(),
            "staticurl"  => STATICURL
            );
        $global["phpself"] = $this->getScriptUrl();
        $sitePath = substr($global["phpself"], 0, strrpos($global["phpself"], "/"));
        $global["isHTTPS"] = (isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) != "off") ? true : false);
        $global["siteurl"] = StringUtil::ihtmlSpecialChars("http" . ($global["isHTTPS"] ? "s" : "") . "://" . $_SERVER["HTTP_HOST"] . $sitePath . "/");
        $url = parse_url($global["siteurl"]);
        $global["siteroot"] = (isset($url["path"]) ? $url["path"] : "");
        $global["siteport"] = (empty($_SERVER["SERVER_PORT"]) || ($_SERVER["SERVER_PORT"] == "80") || ($_SERVER["SERVER_PORT"] == "443") ? "" : ":" . $_SERVER["SERVER_PORT"]);
        $config = @include (PATH_ROOT . "/system/config/config.php");

        if (empty($config)) {
            throw new NotFoundException(Ibos::Lang("Config not found", "error"));
        } else {
            $global["config"] = $config;
        }

        Ibos::app()->setting->copyFrom($global);
    }

    public function handleInitInput($event)
    {
        if (isset($_GET["GLOBALS"]) || isset($_POST["GLOBALS"]) || isset($_COOKIE["GLOBALS"]) || isset($_FILES["GLOBALS"])) {
            throw new RequestException(Ibos::lang("Parameters error", "error"));
        }

        $global = Ibos::app()->setting->toArray();
        $config = $global["config"];
        $preLength = strlen($global["config"]["cookie"]["cookiepre"]);

        foreach ($_COOKIE as $key => $value) {
            if (substr($key, 0, $preLength) == $config["cookie"]["cookiepre"]) {
                $global["cookie"][substr($key, $preLength)] = $value;
            }
        }

        $global["sid"] = $global["cookie"]["sid"] = (isset($global["cookie"]["sid"]) ? StringUtil::ihtmlSpecialChars($global["cookie"]["sid"]) : "");

        if (empty($global["cookie"]["saltkey"])) {
            $global["cookie"]["saltkey"] = StringUtil::random(8);
            MainUtil::setCookie("saltkey", $global["cookie"]["saltkey"], 86400 * 30, 1, 1);
        }

        $global["authkey"] = md5($global["config"]["security"]["authkey"] . $global["cookie"]["saltkey"]);
        Ibos::app()->setting->copyFrom($global);
    }

    public function handleInitSession($event)
    {
        $global = Ibos::app()->setting->toArray();
        Ibos::app()->session->load($global["cookie"]["sid"], $global["clientip"], Ibos::app()->user->isGuest ? 0 : Ibos::app()->user->uid);
        $global["sid"] = Ibos::app()->session->sid;
        $global["session"] = Ibos::app()->session->var;
        if (!empty($global["sid"]) && ($global["sid"] != $global["cookie"]["sid"])) {
            MainUtil::setCookie("sid", $global["sid"], 86400);
        }

        Ibos::app()->setting->copyFrom($global);
        $isNewSession = Ibos::app()->session->isNew;

        if ($isNewSession) {
            if (EnvUtil::ipBanned($global["clientip"])) {
                Ibos::error(Ibos::lang("User banned", "message"));
            }
        }

        if (!Ibos::app()->user->isGuest && ($isNewSession || ((Ibos::app()->session->getKey("lastactivity") + 600) < TIMESTAMP))) {
            Ibos::app()->session->setKey("lastactivity", TIMESTAMP);

            if ($isNewSession) {
                UserStatus::model()->updateByPk(Ibos::app()->user->uid, array("lastip" => $global["clientip"], "lastvisit" => TIMESTAMP));
            }
        }
    }

    public function handleInitCron($event)
    {
        $cronNextRunTime = Ibos::app()->setting->get("cache/cronnextrun");
        $enableCronRun = $cronNextRunTime && ($cronNextRunTime <= TIMESTAMP);

        if ($enableCronRun) {
            Ibos::app()->cron->run();
        }
    }

    public function handleInitOrg($event)
    {
        if (!FileUtil::fileExists("data/org.js")) {
            OrgUtil::update();
        }
    }

    public function handleLoadSysCache($event)
    {
        $caches = Syscache::model()->fetchAll();

        foreach ($caches as $cache) {
            $value = ($cache["type"] == "1" ? unserialize($cache["value"]) : $cache["value"]);

            if ($cache["name"] == "setting") {
                Ibos::app()->setting->set("setting", $value);
            } else {
                Ibos::app()->setting->set("cache/" . $cache["name"], $value);
            }
        }
    }

    public function handleCheckUpgrade($event)
    {
        if (!Ibos::app()->user->isGuest && Ibos::app()->user->isadministrator) {
            $upgrade = Ibos::app()->setting->get("setting/upgrade");

            if (!empty($upgrade)) {
                Ibos::app()->setting->set("newversion", 1);
            }

            $cookie = Ibos::app()->setting->get("cookie");
            $needUpgrade = isset($cookie["checkupgrade"]);

            if ($needUpgrade) {
                $checkReturn = upgradeUtil::checkUpgrade();
                Ibos::app()->setting->set("newversion", $checkReturn ? 1 : 0);
                MainUtil::setCookie("checkupgrade", 1, 7200);
            }
        }
    }

    public function handleCheckLicence($event)
    {
        $err = 0;
        $errinfo = "";
        $licenceArr = Ibos::app()->getLicence();

        if (!empty($licenceArr)) {
            $unit = Ibos::app()->setting->get("setting/unit");
            if (($licenceArr["etime"] < time()) || (time() < $licenceArr["stime"])) {
                $err = 4;
                $errinfo = "授权文件已过期请续期";
            } elseif (time() < $licenceArr["stime"]) {
                $err = 5;
                $errinfo = "服务器时间可能不正确";
            } elseif ($unit["shortname"] != $licenceArr["name"]) {
                $err = 3;
                $errinfo = "授权简称与系统不一致";
            } elseif ($unit["fullname"] != $licenceArr["fullname"]) {
                $err = 3;
                $errinfo = "授权全称与系统不一致";
            } elseif ($_SERVER["SERVER_NAME"] != "") {
                if (is_array($licenceArr["url"])) {
                    if (!in_array($_SERVER["SERVER_NAME"], $licenceArr["url"]) && !in_array($_SERVER["HTTP_HOST"], $licenceArr["url"])) {
                        $err = 3;
                        $errinfo = "授权网址与访问网址不同";
                    } else {
                        define("LICENCE_URL", join(",", $licenceArr["url"]));
                    }
                } else {
                    if (substr($licenceArr["url"], 0, 7) == "http://") {
                        $licenceArr["url"] = trim(substr($licenceArr["url"], 7), "/");
                    }

                    if (($_SERVER["SERVER_NAME"] != trim($licenceArr["url"])) && ($_SERVER["HTTP_HOST"] != trim($licenceArr["url"])) && ($_SERVER["HTTP_HOST"] != "127.0.0.1")) {
                        $err = 3;
                        $errinfo = "授权网址与访问网址不同";
                    } else {
                        define("LICENCE_URL", $licenceArr["url"]);
                    }
                }
            }

            if ($err) {
                define("LICENCE_LIMIT", 20);
                define("LICENCE_STIME", "NO");
                define("LICENCE_ETIME", "NO");
                define("LICENCE_VERNAME", "未授权");
                define("LICENCE_ERR", "授权出错：" . $errinfo);
            } else {
                define("LICENCE_NAME", $licenceArr["name"]);
                define("LICENCE_FULLNAME", $licenceArr["fullname"]);
                define("LICENCE_STIME", $licenceArr["stime"]);
                define("LICENCE_ETIME", $licenceArr["etime"]);
                define("LICENCE_VER", $licenceArr["ver"]);
                define("LICENCE_LIMIT", $licenceArr["limit"]);

                if (isset($licenceArr["vername"])) {
                    define("LICENCE_VERNAME", $licenceArr["vername"]);
                } else {
                    define("LICENCE_VERNAME", "定制版");
                }
            }
        } else {
            $err = 2;
            define("LICENCE_LIMIT", 20);
            define("LICENCE_STIME", "NO");
            define("LICENCE_ETIME", "NO");
            define("LICENCE_VERNAME", "未授权");
            define("LICENCE_ERR", "授权无法解析或授权文件不存在");
        }

        switch ($err) {
            case 0:
                break;

            case 1:
            case 2:
                Ibos::app()->setting->set("setting/unit/shortname", Ibos::app()->setting->get("setting/unit/shortname") . "[系统未注册]");
                break;

            case 3:
                Ibos::app()->setting->set("setting/unit/shortname", $licenceArr["name"] . "[授权无效]");
                Ibos::app()->setting->set("setting/unit/fullname", $licenceArr["fullname"] . "[授权无效]");

                if (8 < rand(0, 10)) {
                    echo "授权文件错误,请检查公司简称全称是否与授权码一致，访问域是否与授权码一致等。";
                }

                break;

            case 4:
            case 5:
                Ibos::app()->setting->set("setting/unit/fullname", $licenceArr["name"] . "[授权已过期]");
                break;
        }
    }

    public function handleSystemConfigure($event)
    {
        $global = Ibos::app()->setting->toArray();
        $timeOffset = $global["setting"]["timeoffset"];
        $this->setTimezone($timeOffset);

        if (!Ibos::app()->user->isGuest) {
            define("FORMHASH", EnvUtil::formHash());
        } else {
            define("FORMHASH", "");
        }

        define("VERHASH", $global["setting"]["verhash"]);

        if ($global["setting"]["appclosed"]) {
            $route = Ibos::app()->getUrlManager()->parseUrl(Ibos::app()->getRequest());

            if (!empty($route)) {
                list($module) = explode("/", $route);
            } else {
                $module = "";
            }

            if (!Ibos::app()->user->isGuest && Ibos::app()->user->isadministrator) {
            } elseif (in_array($module, array("dashboard", "user"))) {
            } else {
                if (defined("IN_SWFHASH") && IN_SWFHASH) {
                } else {
                    EnvUtil::iExit(Ibos::lang("System closed", "message"));
                }
            }
        }
    }

    private function setTimezone($timeOffset = 0)
    {
        if (function_exists("date_default_timezone_set")) {
            @date_default_timezone_set("Etc/GMT" . (0 < $timeOffset ? "-" : "+") . abs($timeOffset));
        }
    }

    private function getScriptUrl()
    {
        $phpSelf = "";
        $scriptName = basename($_SERVER["SCRIPT_FILENAME"]);

        if (basename($_SERVER["SCRIPT_NAME"]) === $scriptName) {
            $phpSelf = $_SERVER["SCRIPT_NAME"];
        } elseif (basename($_SERVER["PHP_SELF"]) === $scriptName) {
            $phpSelf = $_SERVER["PHP_SELF"];
        } else {
            if (isset($_SERVER["ORIG_SCRIPT_NAME"]) && (basename($_SERVER["ORIG_SCRIPT_NAME"]) === $scriptName)) {
                $phpSelf = $_SERVER["ORIG_SCRIPT_NAME"];
            } elseif (($pos = strpos($_SERVER["PHP_SELF"], "/" . $scriptName)) !== false) {
                $phpSelf = substr($_SERVER["SCRIPT_NAME"], 0, $pos) . "/" . $scriptName;
            } else {
                if (isset($_SERVER["DOCUMENT_ROOT"]) && (strpos($_SERVER["SCRIPT_FILENAME"], $_SERVER["DOCUMENT_ROOT"]) === 0)) {
                    $phpSelf = str_replace("\\", "/", str_replace($_SERVER["DOCUMENT_ROOT"], "", $_SERVER["SCRIPT_FILENAME"]));
                    ($phpSelf[0] != "/") && ($phpSelf = "/" . $phpSelf);
                } else {
                    throw new EnvException(Ibos::lang("Request tainting", "error"));
                }
            }
        }

        return $phpSelf;
    }
}
