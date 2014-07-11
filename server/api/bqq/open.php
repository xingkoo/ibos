<?php

function getScriptUrl()
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

function geturl()
{
    $phpself = getscripturl();
    $isHTTPS = (isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) != "off") ? true : false);
    $url = StringUtil::ihtmlSpecialChars("http" . ($isHTTPS ? "s" : "") . "://" . $_SERVER["HTTP_HOST"] . $phpself);
    return $url;
}

define("ENGINE", "LOCAL");
define("PATH_ROOT", dirname(__FILE__) . "/../../");
$defines = PATH_ROOT . "/system/defines.php";
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
define("OAUTH2_TOKEN", "https://openapi.b.qq.com/oauth2/token");
define("OPEN_CALLBACKURL", geturl());

if (isset($_GET["code"])) {
    $code = $_GET["code"];
    $query = array("grant_type" => "authorization_code", "app_id" => $imCfg["appid"], "app_secret" => $imCfg["appsecret"], "code" => $code, "state" => md5(rand()), "redirect_uri" => OPEN_CALLBACKURL);
    $options = array(CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => false, CURLOPT_ENCODING => "", CURLOPT_USERAGENT => "spider", CURLOPT_AUTOREFERER => true, CURLOPT_CONNECTTIMEOUT => 15, CURLOPT_TIMEOUT => 120, CURLOPT_MAXREDIRS => 10, CURLOPT_POST => 0, CURLOPT_POSTFIELDS => "", CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_VERBOSE => 1);
    $url = OAUTH2_TOKEN . "?" . http_build_query($query);
    $curl = curl_init($url);

    if (curl_setopt_array($curl, $options)) {
        $result = curl_exec($curl);
    }

    curl_close($curl);

    if (false !== $result) {
        $company_info = json_decode($result, true);

        if ($company_info["ret"] == 0) {
            $data = $company_info["data"];
            $imCfg["id"] = $data["open_id"];
            $imCfg["token"] = $data["access_token"];
            $imCfg["refresh_token"] = $data["refresh_token"];
            $imCfg["expires_in"] = $data["expires_in"];
            $imCfg["time"] = time();
            $im["qq"] = $imCfg;
            Setting::model()->updateSettingValueByKey("im", $im);
            CacheUtil::update(array("setting"));
            echo json_encode(array("ret" => 0));
            exit();
        }
    }
}

echo json_encode(array("ret" => -1));
exit();
