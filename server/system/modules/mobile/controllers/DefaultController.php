<?php

class MobileDefaultController extends MobileBaseController
{
    public function actionLogin()
    {
        if (!Ibos::app()->user->isGuest) {
            $return = array("login" => true, "formhash" => FORMHASH, "uid" => Yii::app()->user->uid, "user" => user::model()->fetchByUid(Ibos::app()->user->uid), "APPID" => Ibos::app()->setting->get("setting/iboscloud/appid"));

            if (EnvUtil::getRequest("issetuser") != "true") {
                $userData = UserUtil::getUserByPy();
                $return["userData"] = $userData;
            }

            if (ModuleUtil::getIsEnabled("weibo")) {
                $udata = UserData::model()->getUserData();
            }

            $return["user"]["following_count"] = (isset($udata["following_count"]) ? $udata["following_count"] : 0);
            $return["user"]["follower_count"] = (isset($udata["follower_count"]) ? $udata["follower_count"] : 0);
            $return["user"]["weibo_count"] = (isset($udata["weibo_count"]) ? $udata["weibo_count"] : 0);
            $this->ajaxReturn($return, "JSONP");
        }

        $account = Ibos::app()->setting->get("setting/account");
        $userName = EnvUtil::getRequest("username");
        $passWord = EnvUtil::getRequest("password");
        $gps = EnvUtil::getRequest("gps");
        $address = EnvUtil::getRequest("address");
        $ip = Ibos::app()->setting->get("clientip");
        $cookieTime = 0;
        if (!$passWord || ($passWord != addslashes($passWord))) {
            $this->ajaxReturn(array("login" => false, "msg" => Ibos::lang("Passwd illegal", "user.default")), "JSONP");
        }

        $identity = new ICUserIdentity($userName, $passWord);
        $result = $identity->authenticate(false);

        if (0 < $result) {
            $user = Ibos::app()->user;

            if ($account["allowshare"] != 1) {
                $user->setStateKeyPrefix(Ibos::app()->setting->get("sid"));
            }

            MainUtil::setCookie("autologin", 1, $cookieTime);
            $user->login($identity, $cookieTime);

            if ($user->uid != 1) {
                MainUtil::checkLicenseLimit(true);
            }

            $urlForward = EnvUtil::referer();
            $log = array("terminal" => "app", "password" => StringUtil::passwordMask($passWord), "ip" => $ip, "user" => $userName, "loginType" => "username", "address" => $address, "gps" => $gps);
            Log::write($log, "login", sprintf("module.user.%d", Ibos::app()->user->uid));
            $return = array("login" => true, "formhash" => EnvUtil::formHash(), "uid" => Ibos::app()->user->uid, "user" => user::model()->fetchByUid(Ibos::app()->user->uid), "APPID" => Ibos::app()->setting->get("setting/iboscloud/appid"));

            if (ModuleUtil::getIsEnabled("weibo")) {
                $udata = UserData::model()->getUserData();
            }

            $return["user"]["following_count"] = (isset($udata["following_count"]) ? $udata["following_count"] : 0);
            $return["user"]["follower_count"] = (isset($udata["follower_count"]) ? $udata["follower_count"] : 0);
            $return["user"]["weibo_count"] = (isset($udata["weibo_count"]) ? $udata["weibo_count"] : 0);

            if (EnvUtil::getRequest("issetuser") != "true") {
                $userData = UserUtil::getUserByPy();
                $return["userData"] = $userData;
            }

            $this->ajaxReturn($return, "JSONP");
        } elseif ($result === 0) {
            $this->ajaxReturn(array("login" => false, "msg" => Ibos::lang("User not fount", "user.default", array("{username}" => $userName))), "JSONP");
        } elseif ($result === -1) {
            $this->ajaxReturn(array("login" => false, "msg" => Ibos::lang("User lock", "user.default", array("{username}" => $userName))), "JSONP");
        } elseif ($result === -2) {
            $this->ajaxReturn(array("login" => false, "msg" => Ibos::lang("User disabled", "", array("{username}" => $userName))), "JSONP");
        } elseif ($result === -3) {
            $log = array("user" => $userName, "password" => StringUtil::passwordMask($passWord), "ip" => $ip);
            Log::write($log, "illegal", "module.user.login");
            $this->ajaxReturn(array("login" => false, "msg" => Ibos::lang("User name or password is not correct", "user.default")), "JSONP");
        }
    }

    public function actionLogout()
    {
        Ibos::app()->user->logout();
        MainUtil::setCookie("autologin", 0, 0);
        $this->ajaxReturn(array("login" => false), "JSONP");
    }

    public function actionIndex()
    {
        $access = parent::getAccess();

        if (0 < $access) {
            $this->ajaxReturn(array("login" => true, "formhash" => FORMHASH, "uid" => Yii::app()->user->uid, "user" => user::model()->fetchByUid(Yii::app()->user->uid)), "JSONP");
        } else {
            $this->ajaxReturn(array("login" => false, "msg" => "登录已超时，请重新登录"), "JSONP");
            exit();
        }
    }

    public function actionToken()
    {
        $devtoken = EnvUtil::getRequest("devtoken");
        $platform = EnvUtil::getRequest("platform");
        $uniqueid = EnvUtil::getRequest("uniqueid");
        $param = array("uid" => Ibos::app()->user->uid, "devtoken" => $devtoken, "platform" => $platform, "uniqueid" => $uniqueid);
        $rs = CloudApi::getInstance()->fetch("Api/push/token", $param, "post");

        if (substr($rs, 0, 5) !== "error") {
            $this->ajaxReturn(array("isSucess" => true), "JSONP");
        }

        $this->ajaxReturn(array("isSucess" => false), "JSONP");
    }
}
