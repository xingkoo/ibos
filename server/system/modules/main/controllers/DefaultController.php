<?php

class MainDefaultController extends ICController
{
    public function actionIndex()
    {
        $modules = Module::model()->fetchAllClientModule();
        $widgetModule = $modules;

        foreach ($widgetModule as $index => $module) {
            $conf = CJSON::decode($module["config"]);
            $param = $conf["param"];
            if (!isset($param["indexShow"]) || !isset($param["indexShow"]["widget"])) {
                unset($widgetModule[$index]);
            }
        }

        $moduleArr = ConvertUtil::getSubByKey($widgetModule, "module");
        $moduleSetting = MainUtil::execApiMethod("loadSetting", $moduleArr);
        $data = array("modules" => $modules, "widgetModule" => $widgetModule, "moduleSetting" => CJSON::encode($moduleSetting), "menus" => MenuPersonal::model()->fetchMenuByUid(Ibos::app()->user->uid));
        $this->setPageTitle(Ibos::lang("Home office"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Home office"))
        ));
        $this->render("index", $data);
    }

    public function actionUnsupportedBrowser()
    {
        $alias = "application.views.browserUpgrade";
        $this->renderPartial($alias);
    }

    public function actionUpdate()
    {
        CacheUtil::update();
        OrgUtil::update();

        if (LOCAL) {
            Ibos::app()->assetManager->republicAll();
        }

        ModuleUtil::updateConfig();
        EnvUtil::iExit("缓存更新完成");
    }

    public function actionGuide()
    {
        $operation = EnvUtil::getRequest("op");

        if (!in_array($operation, array("guideNextTime", "checkIsGuided", "companyInit", "addUser", "modifyPassword", "modifyProfile", "uploadAvatar"))) {
            $res["isSuccess"] = false;
            $res["msg"] = Ibos::lang("Parameters error", "error");
            $this->ajaxReturn($res);
        } else {
            $this->{$operation}();
        }
    }

    private function guideNextTime()
    {
        $uid = Ibos::app()->user->uid;
        MainUtil::setCookie("guideNextTime", md5($uid));
    }

    private function checkIsGuided()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $uid = Ibos::app()->user->uid;
            $isadministrator = ($uid == 1 ? true : false);
            $user = User::model()->fetchByAttributes(array("uid" => $uid));
            $newcomer = $user["newcomer"];

            if (!$newcomer) {
                $this->ajaxReturn(array("isNewcommer" => false));
            } else {
                if ($uid == 1) {
                    $guideAlias = "application.modules.main.views.default.adminGuide";
                    $unit = unserialize(Setting::model()->fetchSettingValueByKey("unit"));
                    $data["fullname"] = $unit["fullname"];
                    $data["shortname"] = $unit["shortname"];
                    $data["pageUrl"] = $this->getPageUrl();
                } else {
                    $data["swfConfig"] = AttachUtil::getUploadConfig($uid);
                    $data["uid"] = $uid;
                    $guideAlias = "application.modules.main.views.default.initGuide";
                }

                $account = unserialize(Setting::model()->fetchSettingValueByKey("account"));
                $data["account"] = $account;

                if ($account["mixed"]) {
                    $data["preg"] = "[0-9]+[A-Za-z]+|[A-Za-z]+[0-9]+";
                } else {
                    $data["preg"] = "^[A-Za-z0-9\!\@\#$\%\^\&\*\.\~]{" . $account["minlength"] . ",32}\$";
                }

                $data["lang"] = Ibos::getLangSource("main.default");
                $data["assetUrl"] = $this->getAssetUrl();
                $guideView = $this->renderPartial($guideAlias, $data, true);
                $this->ajaxReturn(array("isNewcommer" => true, "guideView" => $guideView, "isadministrator" => $isadministrator));
            }
        }
    }

    private function companyInit()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $postData = array();
            $keys = array("phone", "fullname", "shortname", "fax", "zipcode", "address", "adminemail", "systemurl");

            foreach ($keys as $key) {
                if (isset($_POST[$key])) {
                    $postData[$key] = StringUtil::filterCleanHtml($_POST[$key]);
                } else {
                    $postData[$key] = "";
                }
            }

            Setting::model()->updateSettingValueByKey("unit", $postData);
            CacheUtil::update(array("setting"));
            $depts = EnvUtil::getRequest("depts");
            $isSuccess = $this->handleDept($depts);

            if ($isSuccess) {
                $uid = Ibos::app()->user->uid;
                User::model()->modify($uid, array("newcomer" => 0));
                $deptCache = Ibos::app()->setting->get("cache/department");
                $posCache = Ibos::app()->setting->get("cache/position");
                $selectFormat = "<option value='\$deptid' \$selected>\$spacer\$deptname</option>";
                $res["isSuccess"] = true;
                $res["depts"] = StringUtil::getTree($deptCache, $selectFormat);
                $res["positions"] = $posCache;
            } else {
                $res["isSuccess"] = false;
                $res["msg"] = Ibos::lang("Add department fail");
            }

            $this->ajaxReturn($res);
        }
    }

    private function handleDept($depts)
    {
        $depts = trim(StringUtil::filterCleanHtml($depts));
        $deptArr = preg_split("/\n/", $depts);

        if (!empty($deptArr)) {
            foreach ($deptArr as $index => $deptName) {
                $deptName = rtrim($deptName);

                if (empty($deptName)) {
                    unset($deptArr[$index]);
                    continue;
                }

                $deptArr[$index] = $deptName;
            }
        }

        if (!empty($deptArr)) {
            $deptArr = array_values($deptArr);

            foreach ($deptArr as $index => $deptName) {
                $dept = array();
                $dept["deptname"] = trim($deptName);
                $currBlank = strspn($deptName, " ");
                $indentBlank = $currBlank - strspn(trim($deptName), " ");

                if ($indentBlank == 0) {
                    $dept["pid"] = 0;
                    $newId = Department::model()->add($dept, true);

                    if ($newId) {
                        Department::model()->modify($newId, array("sort" => $newId));
                    }
                } else {
                    $accordItem = array();

                    foreach ($deptArr as $k => $v) {
                        $currBlank2 = strspn($v, " ");
                        $indentBlank2 = $currBlank2 - strspn(trim($v), " ");
                        if (($k < $index) && ($indentBlank2 < $indentBlank)) {
                            $accordItem[$k] = $v;
                        }
                    }

                    $upDeptName = "";

                    if (count($accordItem) == 1) {
                        $upDeptName = array_shift($accordItem);
                    } elseif (1 < count($accordItem)) {
                        $maxKey = max(array_keys($accordItem));
                        $upDeptName = $deptArr[$maxKey];
                    }

                    $upDept = Department::model()->fetchByAttributes(array("deptname" => trim($upDeptName)));

                    if (!empty($upDept)) {
                        $dept["pid"] = $upDept["deptid"];
                        $newId = Department::model()->add($dept, true);

                        if ($newId) {
                            Department::model()->modify($newId, array("sort" => $newId));
                        }
                    }
                }
            }
        }

        $newId && OrgUtil::update();
        return !!$newId;
    }

    private function addUser()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $fields = array("username", "password", "realname", "mobile", "deptid", "positionid", "email");
            if (empty($_POST["username"]) || empty($_POST["password"])) {
                $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Username or password not empty")));
            }

            foreach ($fields as $field) {
                if (isset($_POST[$field]) && !empty($_POST[$field])) {
                    $_POST[$field] = StringUtil::filterDangerTag($_POST[$field]);
                }
            }

            $salt = StringUtil::random(6);
            $userData = array("salt" => $salt, "username" => $_POST["username"], "password" => !empty($_POST["password"]) ? md5(md5($_POST["password"]) . $salt) : "", "realname" => $_POST["realname"], "mobile" => $_POST["mobile"], "createtime" => TIMESTAMP, "deptid" => intval($_POST["deptid"]), "positionid" => intval($_POST["positionid"]), "email" => $_POST["email"]);
            $newId = User::model()->add($userData, true);

            if ($newId) {
                UserCount::model()->add(array("uid" => $newId));
                $ip = Ibos::app()->setting->get("clientip");
                UserStatus::model()->add(array("uid" => $newId, "regip" => $ip, "lastip" => $ip));
                UserProfile::model()->add(array("uid" => $newId));
                $newUser = User::model()->fetchByPk($newId);
                $users = UserUtil::loadUser();
                $users[$newId] = UserUtil::wrapUserInfo($newUser);
                User::model()->makeCache($users);
                OrgUtil::update();
                $res["isSuccess"] = true;
            } else {
                $res["isSuccess"] = false;
                $res["msg"] = Ibos::lang("Add user failed");
            }

            $this->ajaxReturn($res);
        }
    }

    private function modifyPassword()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $uid = Ibos::app()->user->uid;
            $user = User::model()->fetchByAttributes(array("uid" => $uid));

            if (EnvUtil::getRequest("checkOrgPass")) {
                $originalpass = EnvUtil::getRequest("originalpass");
                $isSuccess = (strcasecmp(md5(md5($originalpass) . $user["salt"]), $user["password"]) == 0 ? true : false);
                $this->ajaxReturn(array("isSuccess" => $isSuccess));
            }

            $data = $_POST;

            if ($data["originalpass"] == "") {
                $res["isSuccess"] = false;
                $res["msg"] = Ibos::lang("Original password require");
            } elseif (strcasecmp(md5(md5($data["originalpass"]) . $user["salt"]), $user["password"]) !== 0) {
                $res["isSuccess"] = false;
                $res["msg"] = Ibos::lang("Password is not correct");
            } else {
                if (!empty($data["newpass"]) && (strcasecmp($data["newpass"], $data["newpass_confirm"]) !== 0)) {
                    $res["isSuccess"] = false;
                    $res["msg"] = Ibos::lang("Confirm password is not correct");
                } else {
                    $password = md5(md5($data["newpass"]) . $user["salt"]);
                    User::model()->updateByUid($uid, array("password" => $password, "lastchangepass" => TIMESTAMP));
                    $res["realname"] = $user["realname"];
                    $res["mobile"] = $user["mobile"];
                    $res["email"] = $user["email"];
                    $res["isSuccess"] = true;
                }
            }

            $this->ajaxReturn($res);
        }
    }

    private function uploadAvatar()
    {
        $upload = FileUtil::getUpload($_FILES["Filedata"]);

        if (!$upload->save()) {
            $this->ajaxReturn(array("msg" => Ibos::lang("Save failed", "message"), "IsSuccess" => false));
        } else {
            $info = $upload->getAttach();
            $file = FileUtil::getAttachUrl() . "/" . $info["type"] . "/" . $info["attachment"];
            $fileUrl = FileUtil::fileName($file);
            $tempSize = FileUtil::imageSize($fileUrl);
            if (($tempSize[0] < 180) || ($tempSize[1] < 180)) {
                $this->ajaxReturn(array("msg" => Ibos::lang("Avatar size error"), "IsSuccess" => false), "json");
            }

            Ibos::import("ext.ThinkImage.ThinkImage", true);
            $imgObj = new ThinkImage(THINKIMAGE_GD);
            $imgTemp = $imgObj->open($fileUrl);
            $params = array("w" => $imgTemp->width(), "h" => $imgTemp->height(), "x" => "0", "y" => "0");

            if ($params["h"] < $params["w"]) {
                $params["x"] = ($params["w"] - $params["h"]) / 2;
                $params["w"] = $params["h"];
            } else {
                $params["y"] = ($params["h"] - $params["w"]) / 2;
                $params["h"] = $params["w"];
            }

            $imgObj->open($fileUrl)->crop($params["w"], $params["h"], $params["x"], $params["y"])->save($fileUrl);
            $this->ajaxReturn(array("data" => $fileUrl, "file" => $fileUrl, "IsSuccess" => true));
        }
    }

    private function modifyProfile()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $uid = Ibos::app()->user->uid;

            if (!empty($_POST["src"])) {
                $this->cropImg();
            }

            $profileField = array("birthday");
            $userField = array("mobile", "email");
            $model = array();

            foreach ($_POST as $key => $value) {
                if (in_array($key, $profileField) && !empty($value)) {
                    if ($key == "birthday") {
                        $value = strtotime($value);
                        $model["UserProfile"][$key] = StringUtil::filterCleanHtml($value);
                    }
                } elseif (in_array($key, $userField)) {
                    $model["User"][$key] = StringUtil::filterCleanHtml($value);
                }
            }

            foreach ($model as $modelObject => $value) {
                $modelObject::model()->modify($uid, $value);
            }

            User::model()->modify($uid, array("newcomer" => 0));
            $isInstallWeibo = ModuleUtil::getIsEnabled("weibo");
            $this->ajaxReturn(array("isSuccess" => true, "isInstallWeibo" => !!$isInstallWeibo));
        }
    }

    private function cropImg()
    {
        $uid = Ibos::app()->user->uid;
        $tempAvatar = $_POST["src"];
        $avatarPath = "data/avatar/";
        $avatarBig = UserUtil::getAvatar($uid, "big");
        $avatarMiddle = UserUtil::getAvatar($uid, "middle");
        $avatarSmall = UserUtil::getAvatar($uid, "small");

        if (LOCAL) {
            FileUtil::makeDirs($avatarPath . dirname($avatarBig));
        }

        FileUtil::createFile("data/avatar/" . $avatarBig, "");
        FileUtil::createFile("data/avatar/" . $avatarMiddle, "");
        FileUtil::createFile("data/avatar/" . $avatarSmall, "");
        Ibos::import("ext.ThinkImage.ThinkImage", true);
        $imgObj = new ThinkImage(THINKIMAGE_GD);
        $imgObj->open($tempAvatar)->thumb(180, 180, 1)->save($avatarPath . $avatarBig);
        $imgObj->open($tempAvatar)->thumb(60, 60, 1)->save($avatarPath . $avatarMiddle);
        $imgObj->open($tempAvatar)->thumb(30, 30, 1)->save($avatarPath . $avatarSmall);
    }

    private function getPageUrl()
    {
        $pageURL = "http";
        if (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on")) {
            $pageURL .= "s";
        }

        $pageURL .= "://";
        $thisPage = $_SERVER["REQUEST_URI"];

        if (strpos($thisPage, "?") !== false) {
            $thisPageParams = explode("?", $thisPage);
            $thisPage = reset($thisPageParams);
        }

        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $thisPage;
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"] . $thisPage;
        }

        return $pageURL;
    }

    public function actionModuleGuide()
    {
        $uid = Ibos::app()->user->uid;
        $id = StringUtil::filterCleanHtml(EnvUtil::getRequest("id"));
        $op = EnvUtil::getRequest("op");

        if ($op == "checkHasGuide") {
            $guide = ModuleGuide::model()->fetchGuide($id, $uid);
            $hasGuide = (empty($guide) ? false : true);
            $this->ajaxReturn(array("hasGuide" => $hasGuide));
        } elseif ($op == "finishGuide") {
            ModuleGuide::model()->add(array("route" => $id, "uid" => $uid));
        }
    }

    public function actionGetCert()
    {
        $certAlias = "application.modules.main.views.default.cert";
        $params = array("lang" => Ibos::getLangSource("main.default"));
        $certView = $this->renderPartial($certAlias, $params, true);
        echo $certView;
    }

    public function actionPersonalMenu()
    {
        if (EnvUtil::submitCheck("personalMenu")) {
            $ids = EnvUtil::getRequest("mod");
            $uid = Ibos::app()->user->uid;
            MenuPersonal::model()->deleteAll("uid = $uid");

            if (!empty($ids)) {
                $common = implode(",", $ids);
            } else {
                $common = "";
            }

            $data = array("uid" => $uid, "common" => $common);
            MenuPersonal::model()->add($data);
            $this->ajaxReturn(array("isSuccess" => true));
        }
    }

    public function actionCommonMenu()
    {
        if (EnvUtil::submitCheck("commonMenu")) {
            $ids = EnvUtil::getRequest("mod");
            MenuCommon::model()->updateAll(array("sort" => 0, "iscommon" => 0));

            if (!empty($ids)) {
                foreach ($ids as $index => $id) {
                    MenuCommon::model()->updateAll(array("sort" => intval($index) + 1, "iscommon" => 1), "module='$id'");
                }
            }

            $this->ajaxReturn(array("isSuccess" => true));
        }
    }

    public function actionRestoreMenu()
    {
        if (EnvUtil::submitCheck("restoreMenu")) {
            $uid = Ibos::app()->user->uid;
            MenuPersonal::model()->deleteAll("uid = $uid");
            $this->ajaxReturn(array("isSuccess" => true));
        }
    }
}
