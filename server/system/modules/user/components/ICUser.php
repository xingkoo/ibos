<?php

class ICUser extends CWebUser
{
    /**
     * 允许自动登录
     * @var boolean 
     */
    public $allowAutoLogin = true;
    /**
     * 账户安全设置
     * @var array 
     */
    protected $account = array();

    public function init()
    {
        $account = Ibos::app()->setting->get("setting/account");
        $this->account = $account;
        $isAutologin = MainUtil::getCookie("autologin");

        if (!$isAutologin) {
            $this->authTimeout = (int) $account["timeout"] * 60;
        }

        parent::init();
    }

    public function afterLogin($fromCookie)
    {
        $uid = $this->getId();
        MainUtil::setCookie("lastactivity", TIMESTAMP);
        UserStatus::model()->updateByPk($uid, array("lastip" => EnvUtil::getClientIp(), "lastvisit" => TIMESTAMP, "lastactivity" => TIMESTAMP, "invisible" => 1));

        if (!$fromCookie) {
            Ibos::app()->session->isNew = true;
            Ibos::app()->session->updateSession();
        }
    }

    public function beforeLogout()
    {
        $uid = $this->getId();
        Session::model()->deleteAllByAttributes(array("uid" => $uid));
        UserStatus::model()->updateByPk($uid, array("invisible" => 0));
        return true;
    }

    public function checkAccess($operation, $params = array(), $allowCaching = true)
    {
        if ($this->isadministrator) {
            return true;
        }

        $purv = UserUtil::getUserPurv($this->uid);
        return isset($purv[$operation]);
    }

    protected function updateAuthStatus()
    {
        if (!Ibos::app()->request->getIsAjaxRequest()) {
            if (($this->account["allowshare"] != 1) && !$this->getIsGuest()) {
                $criteria = array("condition" => sprintf("`uid` = %d", $this->uid));
                $session = Session::model()->fetch($criteria);
                if ($session && ($session["sid"] != Ibos::app()->setting->get("sid"))) {
                    Ibos::app()->getRequest()->getCookies()->remove($this->getStateKeyPrefix());
                    Ibos::app()->getSession()->destroy();
                }
            }

            parent::updateAuthStatus();
        }
    }

    protected function getIsNeedReset()
    {
        $neededReset = false;

        if ($this->account["expiration"] != 0) {
            if (Ibos::app()->user->lastchangepass == 0) {
                $neededReset = true;
            } else {
                $time = TIMESTAMP - Ibos::app()->user->lastchangepass;

                switch ($this->account["expiration"]) {
                    case "1":
                        if (30 < ($time / 86400)) {
                            $neededReset = true;
                        }

                        break;

                    case "2":
                        if (60 < ($time / 86400)) {
                            $neededReset = true;
                        }

                        break;

                    case "3":
                        if (180 < ($time / 86400)) {
                            $neededReset = true;
                        }

                        break;

                    default:
                        break;
                }
            }
        }

        return $neededReset;
    }
}
