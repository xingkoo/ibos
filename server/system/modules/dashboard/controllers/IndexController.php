<?php

class DashboardIndexController extends DashboardBaseController
{
    const SECURITY_URL = "http://www.ibos.com.cn/security.php";
    const COLLECT_URL = "http://www.ibos.com.cn/index.php?m=count&c=collect&a=collect";

    public function actionIndex()
    {
        $systemInfo = EnvUtil::getSystemInfo();
        $databaseSize = DatabaseUtil::getDatabaseSize();
        list($dataSize, $dataUnit) = explode(" ", $databaseSize);
        $appClosed = Setting::model()->fetchSettingValueByKey("appclosed");
        $newVersion = Ibos::app()->setting->get("newversion");
        $getSecurityUrl = Ibos::app()->urlManager->createUrl("dashboard/index/getsecurity");
        $mainModule = Module::model()->fetchByPk("main");
        $authkey = Ibos::app()->setting->get("config/security/authkey");
        $unit = Setting::model()->fetchSettingValueByKey("unit");
        $license = Setting::model()->fetchSettingValueByKey("license");
        $licenseUrl = $this->getLicenseUrl(unserialize($unit), $authkey);

        if (isset($_GET["attachsize"])) {
            $attachSize = Attachment::model()->getTotalFilesize();
            $attachSize = (is_numeric($attachSize) ? ConvertUtil::sizeCount($attachSize) : Ibos::lang("Unknow"));
        } else {
            $attachSize = "";
        }

        $data = array("sys" => $systemInfo, "dataSize" => $dataSize, "dataUnit" => $dataUnit, "appClosed" => $appClosed, "newVersion" => $newVersion, "getSecurityUrl" => $getSecurityUrl, "installDate" => $mainModule["installdate"], "authkey" => $authkey, "license" => unserialize($license), "licenseUrl" => $licenseUrl, "attachSize" => $attachSize);
        $this->render("index", $data);
    }

    private function getLicenseUrl($unit, $authkey)
    {
        $fullname = (isset($unit["fullname"]) ? urlencode($unit["fullname"]) : "");
        $name = (isset($unit["shortname"]) ? urlencode($unit["shortname"]) : "");
        $url = (isset($unit["systemurl"]) ? $unit["systemurl"] : "");
        $snkey = $authkey;
        $phone = (isset($unit["phone"]) ? $unit["phone"] : "");
        $fax = (isset($unit["fax"]) ? $unit["fax"] : "");
        $mail = (isset($unit["adminemail"]) ? $unit["adminemail"] : "");
        return "http://www.ibos.com.cn/index.php?m=license&fullname=" . $fullname . "&name=" . $name . "&url=" . $url . "&snkey=" . $snkey . "&phone=" . $phone . "&fax=" . $fax . "&mail=" . $mail;
    }

    public function actionSwitchstatus()
    {
        if (Ibos::app()->getRequest()->getIsAjaxRequest()) {
            $val = EnvUtil::getRequest("val");
            $result = Setting::model()->updateSettingValueByKey("appclosed", (int) $val);
            CacheUtil::update(array("setting"));
            return $this->ajaxReturn(array("IsSuccess" => $result), "json");
        }
    }

    public function actionGetSecurity()
    {
        if (Ibos::app()->getRequest()->getIsAjaxRequest()) {
            $return = FileUtil::fileSockOpen(self::SECURITY_URL, 0, "charset=" . CHARSET);
            $this->collect();
            $this->ajaxReturn($return, "EVAL");
        }
    }

    public function actionLicense()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $licensekey = StringUtil::filterCleanHtml(EnvUtil::getRequest("licensekey"));
            $filename = PATH_ROOT . "/data/licence.key";
            @file_put_contents($filename, $licensekey);
            $this->success(Ibos::lang("Save succeed", "message"));
        }
    }

    private function collect()
    {
        $s = Ibos::app()->setting->toArray();
        $serverinfo = PHP_OS . " / PHP v" . PHP_VERSION;
        $serverinfo .= (@ini_get("safe_mode") ? " Safe Mode" : null);
        $serversoft = $_SERVER["SERVER_SOFTWARE"];
        $param = array("snkey" => $s["config"]["security"]["authkey"], "url" => $s["siteurl"], "name" => isset($s["unit"]["shortname"]) ? urlencode($s["unit"]["shortname"]) : "", "sitename" => isset($s["unit"]["fullname"]) ? urlencode($s["unit"]["fullname"]) : "", "sys" => $serverinfo, "soft" => $serversoft, "dbver" => Ibos::app()->db->getServerVersion(), "dbsize" => DatabaseUtil::getDatabaseSize(), "path" => $_SERVER["SCRIPT_FILENAME"], "licence" => sprintf("LIMIT:%s|VER:%s|STIME:%s|ETIME:%s", LICENCE_LIMIT, defined("LICENCE_VER") ? LICENCE_VER : "", defined("LICENCE_STIME") ? LICENCE_STIME : "", defined("LICENCE_ETIME") ? LICENCE_ETIME : ""), "contactman" => Ibos::app()->user->realname, "tel" => Ibos::app()->user->mobile, "email" => Ibos::app()->user->email);
        $options = array(CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_TIMEOUT => 10);
        $url = self::COLLECT_URL . "&k=" . base64_encode(http_build_query($param));
        $curl = curl_init($url);

        if (curl_setopt_array($curl, $options)) {
            curl_exec($curl);
        }

        curl_close($curl);
    }
}
