<?php

class ICIMRtx extends ICIM
{
    /**
     * 同步标记，是增加还是删除
     * @var type 
     */
    protected $syncFlag;
    /**
     * 同步用户时的密码。明文
     * @var type 
     */
    protected $pwd;
    /**
     *
     * @var type 
     */
    private $users = array();

    public function setPwd($pwd)
    {
        $this->pwd = StringUtil::filterCleanHtml($pwd);
    }

    public function getPwd()
    {
        return $this->pwd;
    }

    public function check()
    {
        if ($this->isEnabled("open")) {
            if (extension_loaded("com_dotnet") && LOCAL) {
                $obj = new COM("RTXSAPIRootObj.RTXSAPIRootObj");
                return is_object($obj);
            } else {
                $this->setError("服务器环境不支持调用组件，请联系系统管理员", self::ERROR_INIT);
                return false;
            }
        }
    }

    public function push()
    {
        $type = $this->getPushType();
        if (($type == "notify") && $this->isEnabled("push/note")) {
            $this->pushNotify();
        } else {
            if (($type == "pm") && $this->isEnabled("push/msg")) {
                $this->pushMsg();
            }
        }
    }

    public function syncOrg()
    {
        $obj = $this->getObj(false);
        $config = $this->getConfig();
        $rtxParam = new COM("rtxserver.collection");
        $obj->Name = "USERSYNC";
        $obj->ServerIP = $config["server"];
        $obj->ServerPort = $config["sdkport"];
        $xmlDoc = new DOMDocument("1.0", "GB2312");
        $xml = $this->makeOrgstructXml();

        if ($xml) {
            $xmlDoc->load("userdata.xml");
            $rtxParam->Add("DATA", $xmlDoc->saveXML());
            $rs = $obj->Call2(1, $rtxParam);
            $newObj = $this->getObj();

            try {
                $u = $newObj->UserManager();

                foreach ($this->users as $user) {
                    $u->SetUserPwd(ConvertUtil::iIconv($user, CHARSET, "gbk"), $this->pwd);
                }

                return true;
            } catch (Exception $exc) {
                $this->setError("同步过程中出现未知错误", self::ERROR_SYNC);
                return false;
            }
        } else {
            $this->setError("无法生成组织架构XML文件", self::ERROR_SYNC);
            return false;
        }
    }

    public function syncUser()
    {
        $syncFlag = $this->getSyncFlag();

        try {
            if (in_array($syncFlag, array(1, 0))) {
                $syncUsers = User::model()->fetchAllByUids($this->getUid());
                $obj = $this->getObj();
                $userObj = $obj->UserManager();

                foreach ($syncUsers as $user) {
                    $userName = ConvertUtil::iIconv($user["username"], CHARSET, "gbk");

                    if ($syncFlag == 1) {
                        $realName = ConvertUtil::iIconv($user["realname"], CHARSET, "gbk");
                        $userObj->AddUser($userName, 0);
                        $userObj->SetUserPwd($userName, $this->getPwd());
                        $userObj->SetUserBasicInfo($userName, $realName, -1, $user["mobile"], $user["email"], $user["telephone"], 0);
                    } elseif ($userObj->IsUserExist($userName)) {
                        $userObj->DeleteUser($userName);
                    }
                }
            }

            $exit = "\t\t\t<script>parent.Ui.tip('同步完成','success');parent.Ui.closeDialog();</script>";
            EnvUtil::iExit($exit);
        } catch (Exception $exc) {
            $exit = "\t\t\t<script>parent.Ui.tip('同步出现问题，无法完成。请联系系统管理员解决','danger');parent.Ui.closeDialog();</script>\t\t\t\t";
            EnvUtil::iExit($exit);
        }
    }

    protected function GUID()
    {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $hyphen = chr(45);
        $uuid = chr(123) . substr($charid, 0, 8) . $hyphen . substr($charid, 8, 4) . $hyphen . substr($charid, 12, 4) . $hyphen . substr($charid, 16, 4) . $hyphen . substr($charid, 20, 12) . chr(125);
        return $uuid;
    }

    protected function pushMsg()
    {
        $users = User::model()->fetchAllByUids($this->getUid());

        if (!empty($users)) {
            $userNames = ConvertUtil::getSubByKey($users, "username");
            $names = ConvertUtil::iIconv(implode(";", $userNames), CHARSET, "gbk");
            $message = $this->formatContent(strip_tags($this->getMessage(), "<a>"));

            try {
                $res = $this->obj->SendIM(ConvertUtil::iIconv(Ibos::app()->user->username, CHARSET, "gbk"), "", $names, $message, $this->GUID());
                return $res;
            } catch (Exception $exc) {
            }
        }

        return false;
    }

    protected function pushNotify()
    {
        $users = User::model()->fetchAllByUids($this->getUid());

        if (!empty($users)) {
            $userNames = ConvertUtil::getSubByKey($users, "username");
            $names = ConvertUtil::iIconv(implode(";", $userNames), CHARSET, "gbk");
            $title = ConvertUtil::iIconv(Ibos::lang("System notify", "default"), CHARSET, "gbk");
            $message = $this->formatContent(strip_tags($this->getMessage(), "<a>"));

            try {
                return $this->obj->SendNotify($names, $title, 0, $message);
            } catch (Exception $exc) {
            }
        }

        return false;
    }

    protected function getObj($newApi = true)
    {
        $config = $this->getConfig();

        if ($newApi) {
            $rtxObj = new COM("RTXSAPIRootObj.RTXSAPIRootObj");
        } else {
            $rtxObj = new COM("rtxserver.rtxobj");
        }

        $rtxObj->ServerIP = $config["server"];
        $rtxObj->ServerPort = ($newApi ? $config["appport"] : $config["sdkport"]);
        return $rtxObj;
    }

    private function formatContent($content)
    {
        if (!empty($this->url)) {
            $url = parse_url($this->getUrl());
            $str = "";
            if (!isset($url["scheme"]) && !isset($url["host"])) {
                $str .= trim(Ibos::app()->setting->get("siteurl"), "/");
            }

            $content = sprintf("[%s|%s]", $content, $str . $url);
        }

        return ConvertUtil::iIconv($content, CHARSET, "gbk");
    }

    private function makeOrgstructXml()
    {
        $deptArr = DepartmentUtil::loadDepartment();
        $unit = Ibos::app()->setting->get("setting/unit");
        $str = "<?xml version=\"1.0\" encoding=\"gb2312\" ?>";
        $str .= "<enterprise name=\"" . $unit["fullname"] . "\" postcode=\"" . $unit["zipcode"] . "\" address=\"" . $unit["address"] . "\" phone=\"" . $unit["phone"] . "\" email=\"" . $unit["adminemail"] . "\">";
        $str .= "<departments>";
        $str .= $this->getDeptree($deptArr);
        $str .= "</departments>";
        $str .= "</enterprise>";
        $file = "userdata.xml";
        $fp = @fopen($file, "wb");

        if ($fp) {
            $str = ConvertUtil::iIconv($str, CHARSET, "gbk");
            file_put_contents($file, $str);

            if (0 < filesize($file)) {
                return true;
            }
        }

        return false;
    }

    private function getDeptree($deptArr, $id = 0)
    {
        $str = "";

        foreach ($deptArr as $key => $value) {
            $upid = $value["pid"];

            if ($id == $upid) {
                $tmp = $this->getDeptree($deptArr, $value["deptid"]);

                if (!$tmp) {
                    $tmp .= self::getUserlistByDept($value["deptid"]);
                    $str .= "<department name=\"" . $value["deptname"] . "\" describe=\"{$value["func"]}\">";
                } else {
                    $str .= "<department name=\"" . $value["deptname"] . "\" describe=\"{$value["func"]}\">";
                }

                $str .= $tmp;
                $str .= "</department>";
                unset($deptArr[$key]);
            }
        }

        return $str;
    }

    private function getUserlistByDept($deptId)
    {
        $str = "";
        $querys = Ibos::app()->db->createCommand()->select("uid")->from("{{user}} u")->where("`status` = 0 AND deptid = " . intval($deptId))->queryAll();

        foreach ($querys as $row) {
            $user = User::model()->fetchByUid($row["uid"]);
            $gender = ($user["gender"] == "1" ? 0 : 1);
            array_push($this->users, $user["username"]);
            //$str .= "\t\t\t\t\t<user uid=\"{$user["username"]}\" name=\"{$user["realname"]}\" email=\"{$user["email"]}\" mobile=\"{$user["mobile"]}\" rtxno=\"\" phone=\"{$user["telephone"]}\" \r\n\t\tposition=\"{$user["posname"]}\" fax=\"\" homepage=\"\" address=\"{$user["address"]}\" age=\"0\" gender=\"$gender\" />  ";
            $str .= "<user uid=\"{$user["username"]}\" name=\"{$user["realname"]}\" email=\"{$user["email"]}\" mobile=\"{$user["mobile"]}\" rtxno=\"\" phone=\"{$user["telephone"]}\" position=\"{$user["posname"]}\" fax=\"\" homepage=\"\" address=\"{$user["address"]}\" age=\"0\" gender=\"$gender\" />  ";
        }

        return $str;
    }
}
