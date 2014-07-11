<?php

function handleLoadSysCache()
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

define("ENGINE", "LOCAL");
define("PATH_ROOT", dirname(__FILE__) . "/../../");
$defines = PATH_ROOT . "/system/defines.php";
defined("TIMESTAMP") || define("TIMESTAMP", time());
$yii = PATH_ROOT . "/library/yii.php";
$config = PATH_ROOT . "/system/config/common.php";
$ibosApplication = PATH_ROOT . "/system/core/components/ICApplication.php";
require_once ($defines);
require_once ($yii);
require_once ($ibosApplication);
$ibos = Yii::createApplication("ICApplication", $config);
$im = Setting::model()->fetchSettingValueByKey("im");
$im = unserialize($im);
$imCfg = $im["qq"];
$cid = filter_input(INPUT_GET, "company_id", FILTER_SANITIZE_STRING);
$openId = filter_input(INPUT_GET, "open_id", FILTER_SANITIZE_STRING);
$hashskey = filter_input(INPUT_GET, "hashskey", FILTER_SANITIZE_STRING);
$hashkey = filter_input(INPUT_GET, "hashkey", FILTER_SANITIZE_STRING);
$returnurl = filter_input(INPUT_GET, "returnurl", FILTER_SANITIZE_STRING);
if (empty($openId) || empty($hashskey) || empty($cid)) {
    exit("参数错误");
}

$uid = UserBinding::model()->fetchUidByValue(StringUtil::filterCleanHtml($openId), "bqq");

if ($uid) {
    $checkCId = strcmp($imCfg["id"], $cid) == 0;
    $properties = array("company_id" => $cid, "company_token" => $imCfg["token"], "app_id" => $imCfg["appid"], "client_ip" => EnvUtil::getClientIp());
    $api = new BQQApi($properties);
    $status = $api->getVerifyStatus(array("open_id" => $openId, "hashskey" => $hashskey));

    if ($status["ret"] == 0) {
        $config = @include (PATH_ROOT . "/system/config/config.php");

        if (empty($config)) {
            throw new Exception(Ibos::Lang("Config not found", "error"));
        } else {
            define("IN_MOBILE", EnvUtil::checkInMobile());
            $global = array("clientip" => EnvUtil::getClientIp(), "config" => $config, "timestamp" => time());
            Ibos::app()->setting->copyFrom($global);
            handleloadsyscache();
            $saltkey = MainUtil::getCookie("saltkey");

            if (empty($saltkey)) {
                $saltkey = StringUtil::random(8);
                MainUtil::setCookie("saltkey", $saltkey, 86400 * 30, 1, 1);
            }

            $curUser = User::model()->fetchByUid($uid);
            $identity = new ICUserIdentity($curUser["username"], $curUser["password"]);
            $identity->setId($uid);
            $identity->setPersistentStates($curUser);
            $ip = Ibos::app()->setting->get("clientip");

            foreach ($_COOKIE as $k => $v) {
                $cookiePath = $config["cookie"]["cookiepath"];
                $cookieDomain = $config["cookie"]["cookiedomain"];
                $secure = ($_SERVER["SERVER_PORT"] == 443 ? 1 : 0);
                @setcookie($k, "", time() - 86400, $cookiePath, $cookieDomain, $secure, false);
            }

            $account = Ibos::app()->setting->get("setting/account");
            $user = Ibos::app()->user;

            if ($account["allowshare"] != 1) {
                $user->setStateKeyPrefix(Ibos::app()->setting->get("sid"));
            }

            $user->login($identity);
            $log = array("terminal" => "bqqsso", "password" => "", "ip" => $ip, "user" => $curUser["username"], "loginType" => $identity::LOGIN_BY_USERNAME, "address" => "", "gps" => "");
            Log::write($log, "login", sprintf("module.user.%d", $uid));
            $rule = UserUtil::updateCreditByAction("daylogin", $uid);

            if (!$rule["updateCredit"]) {
                UserUtil::checkUserGroup($uid);
            }

            if ($returnurl == "index") {
                header("Location: ../../index.php", true);
            } else {
                $url = parse_url($returnurl);

                if (isset($url["scheme"])) {
                    header("Location:" . $returnurl, true);
                } else {
                    header("Location:../../" . $returnurl, true);
                }
            }
        }
    } else {
        EnvUtil::iExit($status["msg"]);
    }
} else {
    EnvUtil::iExit("该用户未绑定企业QQ");
}
