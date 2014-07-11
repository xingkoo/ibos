<?php

class UserDefaultController extends ICController
{
    public $layout = "";

    public function init()
    {
        return false;
    }

    public function actionCheckLogin()
    {
        $islogin = false;
        $isAutologin = MainUtil::getCookie("autologin");
        $isGuest = Ibos::app()->user->isGuest;
        $expires = Ibos::app()->user->getState(ICUser::AUTH_TIMEOUT_VAR);

        if ($isAutologin) {
            $islogin = true;
        } else {
            if (!$isGuest && (($expires == null) || (time() < $expires))) {
                $islogin = true;
            }
        }

        $this->ajaxReturn(array("islogin" => $islogin));
    }

    public function actionAjaxLogin()
    {
        $account = UserUtil::getAccountSetting();

        if (EnvUtil::submitCheck("formhash")) {
            $userName = EnvUtil::getRequest("username");
            $loginType = EnvUtil::getRequest("logintype");
            $passWord = EnvUtil::getRequest("password");
            return $this->doLogin($userName, $passWord, $loginType, $account, 1, 0, 1);
        }
    }

    public function actionLogin()
    {
        if (!Ibos::app()->user->isGuest) {
            $this->redirect(Ibos::app()->urlManager->createUrl("main/default/index"));
        }

        $account = UserUtil::getAccountSetting();

        if (!EnvUtil::submitCheck("loginsubmit", 1)) {
            $announcement = Announcement::model()->fetchByTime(TIMESTAMP);
            $data = array("assetUrl" => $this->getAssetUrl("user"), "lang" => Ibos::getLangSources(), "unit" => Ibos::app()->setting->get("setting/unit"), "account" => $account, "cookietime" => $account["cookietime"], "announcement" => $announcement, "loginBg" => LoginTemplate::model()->fetchAll("`disabled`= 0 AND `image`!=''"));
            $this->setTitle(Ibos::lang("Login page"));
            $this->renderPartial("login", $data);
        } else {
            $userName = EnvUtil::getRequest("username");
            $loginType = EnvUtil::getRequest("logintype");
            $passWord = EnvUtil::getRequest("password");
            $autoLogin = EnvUtil::getRequest("autologin");
            $cookieTime = EnvUtil::getRequest("cookietime");
            $this->doLogin($userName, $passWord, $loginType, $account, $autoLogin, $cookieTime);
        }
    }

    public function actionLogout()
    {
        Ibos::app()->user->logout();
        $loginUrl = Ibos::app()->urlManager->createUrl("user/default/login");
        $this->success(Ibos::lang("Logout succeed"), $loginUrl);
    }

    protected function doLogin($userName, $passWord, $loginType, $account, $autoLogin = 1, $cookieTime = 0, $inajax = 0)
    {

        if (!$passWord || ($passWord != addslashes($passWord))) {
            $this->error(Ibos::lang("Passwd illegal"));
        }

        $errornum = $this->loginCheck($account);
        $ip = Ibos::app()->setting->get("clientip");

        $identity = new ICUserIdentity($userName, $passWord, $loginType);
        $result = $identity->authenticate();

        if (0 < $result) {
            $user = Ibos::app()->user;

            if (empty($autoLogin)) {
                $user->setState($user::AUTH_TIMEOUT_VAR, TIMESTAMP + $account["timeout"]);
            } else {
                MainUtil::setCookie("autologin", 1, $cookieTime);
            }

            $user->login($identity, $cookieTime);

            if ($user->uid != 1) {
                MainUtil::checkLicenseLimit(true);
            }

            if (!$inajax) {
                $urlForward = EnvUtil::referer();
                $log = array("terminal" => "web", "password" => StringUtil::passwordMask($passWord), "ip" => $ip, "user" => $userName, "loginType" => $loginType, "address" => "", "gps" => "");
                Log::write($log, "login", sprintf("module.user.%d", $user->uid));
                $rule = UserUtil::updateCreditByAction("daylogin", $user->uid);

                if (!$rule["updateCredit"]) {
                    UserUtil::checkUserGroup($user->uid);
                }

                $this->success(Ibos::lang("Login succeed", "", array("{username}" => $user->realname)), $urlForward);
            } else {
                $this->ajaxReturn(array("isSuccess" => true));
            }
        } elseif ($result === 0) {
            $this->error(Ibos::lang("User not fount", "", array("{username}" => $userName)), "", array(), array("error" => $result));
        } elseif ($result === -1) {
            $this->error(Ibos::lang("User lock", "", array("{username}" => $userName)), "", array(), array("error" => $result));
        } elseif ($result === -2) {
            $this->error(Ibos::lang("User disabled", "", array("{username}" => $userName)), "", array(), array("error" => $result));
        } elseif ($result === -3) {
            FailedLogin::model()->updateFailed($ip);
            list($ip1, $ip2) = explode(".", $ip);
            $newIp = $ip1 . "." . $ip2;
            FailedIp::model()->insertIp($newIp);
            $log = array("user" => $userName, "password" => StringUtil::passwordMask($passWord), "ip" => $ip);
            Log::write($log, "illegal", "module.user.login");

            if ($errornum) {
                $this->error("登录失败，您还可以尝试" . ($errornum - 1) . "次");
            } else {
                $this->error(Ibos::lang("User name or password is not correct"), "", array(), array("error" => $result));
            }
        }
    }

    public function actionReset()
    {
        if (Ibos::app()->user->isGuest) {
            Ibos::app()->user->loginRequired();
        }

        if (EnvUtil::submitCheck("formhash")) {
            $original = filter_input(INPUT_POST, "originalpass", FILTER_SANITIZE_SPECIAL_CHARS);
            $new = filter_input(INPUT_POST, "newpass", FILTER_SANITIZE_SPECIAL_CHARS);
            $newConfirm = filter_input(INPUT_POST, "newpass_confirm", FILTER_SANITIZE_SPECIAL_CHARS);

            if ($original == "") {
                $this->error(Ibos::lang("Original password require"));
            } elseif (strcasecmp(md5(md5($original) . Ibos::app()->user->salt), Ibos::app()->user->password) !== 0) {
                $this->error(Ibos::lang("Password is not correct"));
            } else {
                if (!empty($new) && (strcasecmp($new, $newConfirm) !== 0)) {
                    $this->error(Ibos::lang("Confirm password is not correct"));
                } else {
                    $password = md5(md5($new) . Ibos::app()->user->salt);
                    $success = User::model()->updateByUid(Ibos::app()->user->uid, array("password" => $password, "lastchangepass" => TIMESTAMP));
                    $success && Ibos::app()->user->logout();
                    $this->success(Ibos::lang("Reset success"), $this->createUrl("default/login"));
                }
            }
        } else {
            $userName = Ibos::app()->user->realname;
            $data = array("assetUrl" => $this->getAssetUrl("user"), "account" => UserUtil::getAccountSetting(), "lang" => Ibos::getLangSources(), "unit" => Ibos::app()->setting->get("setting/unit"), "user" => $userName);
            $this->renderPartial("reset", $data);
        }
    }

    protected function loginCheck($account)
    {
        $return = 0;

        if ($account["errorlimit"] != 0) {
            $ip = Ibos::app()->setting->get("clientip");
            $login = FailedLogin::model()->fetchIp($ip);
            $errrepeat = intval($account["errorrepeat"]);
            $errTime = $account["errortime"] * 60;
            $return = (!$login || ($errTime < (TIMESTAMP - $login["lastupdate"])) ? $errrepeat : max(0, $errrepeat - $login["count"]));

            if (!$login) {
                FailedLogin::model()->add(array("ip" => $ip, "count" => 0, "lastupdate" => TIMESTAMP));
            } elseif ($errTime < (TIMESTAMP - $login["lastupdate"])) {
                FailedLogin::model()->deleteOld($errTime + 1);
                FailedLogin::model()->add(array("ip" => $ip, "count" => 0, "lastupdate" => TIMESTAMP));
            }
            if ($return == 0) {
                $this->error(Ibos::lang("Login check error", "", array("{minute}" => $account["errortime"])));
                exit('11111');
            }
                    
        }

        return $return;
    }
}
