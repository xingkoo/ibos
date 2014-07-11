<?php

$value = Setting::model()->fetchSettingValueByKey("im");
$im = unserialize($value);
$neededUpgrade = false;

if (isset($im["qq"])) {
    $cfg = $im["qq"];
    if (isset($cfg["checkpass"]) && ($cfg["checkpass"] == "1")) {
        if (!empty($cfg["refresh_token"]) && !empty($cfg["time"])) {
            $secs = TIMESTAMP - $im["time"];

            if (!empty($cfg["expires_in"])) {
                $leftsecs = $cfg["expires_in"] - $secs;

                if (($leftsecs / 86400) < 7) {
                    $neededUpgrade = true;
                }
            } else {
                $neededUpgrade = true;
            }
        }
    }

    if ($neededUpgrade) {
        $factory = new ICIMFactory();
        $adapter = $factory->createAdapter("ICIMQq", $cfg);
        $api = $adapter->getApi();
        $infoJson = $api->getRefreshToken(array("app_id" => $cfg["appid"], "app_secret" => $cfg["appsecret"], "refresh_token" => $cfg["refresh_token"]));
        $info = CJSON::decode($infoJson);
        if (isset($info["ret"]) && ($info["ret"] == 0)) {
            $cfg["token"] = $info["data"]["company_token"];
            $cfg["refresh_token"] = $info["data"]["refresh_token"];
            $cfg["expires_in"] = $info["data"]["expires_in"];
            $cfg["time"] = time();
            $im["qq"] = $cfg;
            Setting::model()->updateSettingValueByKey("im", $im);
            CacheUtil::update(array("setting"));
        }
    }
}
