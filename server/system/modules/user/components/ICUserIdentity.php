<?php

class ICUserIdentity extends CUserIdentity
{
    const USER_NOT_FOUND = 0;
    const USER_LOCK = -1;
    const USER_DISABLED = -2;
    const USER_PASSWORD_INCORRECT = -3;
    const USER_NO_ACCESS = -4;
    const LOGIN_BY_USERNAME = 1;
    const LOGIN_BY_EMAIL = 2;
    const LOGIN_BY_JOBNUMBER = 3;
    const LOGIN_BY_MOBILE = 4;

    /**
     * 账号登录类型
     * @var integer 
     */
    private $loginType;
    /**
     * 用户id
     * @var integer 
     */
    private $uid = 0;

    public function __construct($username, $password, $loginType = self::LOGIN_BY_USERNAME)
    {
        $this->loginType = (int) $loginType;
        parent::__construct($username, $password);
    }

    public function getId()
    {
        return $this->uid;
    }

    public function setId($uid)
    {
        $this->uid = $uid;
    }

    public function authenticate($isAdministrator = false)
    {
        $username = $this->username;
        $password = $this->password;
        switch ($this->loginType) {
            case 1:
                $user = User::model()->fetch("`username` = :username", array(":username" => $username));
                break;
        
            case 2:
                $user = User::model()->fetch("`email` = :username", array(":username" => $username));
                break;

            case 3:
                $user = User::model()->fetch("`mobile` = :username", array(":username" => $username));
                break;

            case 4:
                $user = User::model()->fetch("`jobnumber` = :username", array(":username" => $username));
                break;
            default:
                $user = array();
                break;
        }

        $passwordMd5 = (preg_match("/^\w{32}$/", $password) ? $password : md5($password));

        if (empty($user)) {
            $status = self::USER_NOT_FOUND;
        } elseif ($user["status"] == 1) {
            $status = self::USER_LOCK;
        } elseif ($user["status"] == 2) {
            $status = self::USER_DISABLED;
        } elseif ($user["password"] != md5($passwordMd5 . $user["salt"])) {
            $status = self::USER_PASSWORD_INCORRECT;
        } else {
            if ($isAdministrator && ($user["isadministrator"] !== "1")) {
                $status = self::USER_NO_ACCESS;
            } else {
                $status = $user["uid"];
            }
        }

        if (0 < $status) {
            $cache = User::model()->fetchByUid($status);
            $user = array_merge($user, $cache);
            $this->uid = $status;
            $this->persistentStates = $user;
        }

        return $status;
    }
}
