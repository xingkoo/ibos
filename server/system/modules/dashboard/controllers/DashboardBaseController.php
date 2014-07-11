<?php

class DashboardBaseController extends ICController
{
    /**
     * @var boolean 
     */
    public $layout = "dashboard.views.layouts.dashboard";
    /**
     * 当前登录后台的用户数组
     * @var array 
     */
    protected $user = array();
    /**
     * 后台用户登录路由
     * @var string 
     */
    protected $loginUrl = "dashboard/default/login";
    /**
     * 是否有管理权限标识
     * @var boolean 
     */
    private $_isAdministrator = false;
    /**
     * session生命周期,默认20分钟
     * @var integer 
     */
    private $_sessionLife = 1200;
    /**
     * 当前时间减去声明周期后的偏移值
     * @var integer 
     */
    private $_sessionLimit = 0;
    /**
     * cookie生命周期,默认20分钟
     * @var integer 
     */
    private $_cookieLife = 1200;
    /**
     * 当前时间减去声明周期后的偏移值
     * @var integer 
     */
    private $_cookieLimit = 0;
    /**
     * 权限标识
     * @var integer 
     */
    private $_access = 0;

    public function getAssetUrl($module = "")
    {
        return parent::getAssetUrl("dashboard");
    }

    public function init()
    {
        $this->user = (Ibos::app()->user->isGuest ? array() : User::model()->fetchByUid(Ibos::app()->user->uid));
        $this->_isAdministrator = $this->checkAdministrator($this->user);
        $this->_sessionLimit = (int) TIMESTAMP - $this->_sessionLife;
        $this->_cookieLimit = (int) TIMESTAMP - $this->_cookieLife;
        $this->checkAccess();
    }

    public function beforeAction($action)
    {
        if (!Ibos::app()->user->isGuest) {
            $param = ConvertUtil::implodeArray(array("GET" => $_GET, "POST" => $_POST), array("username", "password", "formhash"));
            $action = $action->getId();
            $log = array("user" => Ibos::app()->user->username, "ip" => Ibos::app()->setting->get("clientip"), "action" => $action, "param" => $param);
            Log::write($log, "admincp", sprintf("module.dashboard.%s", $action));
        }

        return true;
    }

    protected function userLogin()
    {
        Ibos::app()->user->loginUrl = array($this->loginUrl);
        Ibos::app()->user->loginRequired();
    }

    protected function getAccess()
    {
        return $this->_access;
    }

    protected function imgUpload($fileArea, $inajax = false)
    {
        $_FILES[$fileArea]["name"] = StringUtil::iaddSlashes(urldecode($_FILES[$fileArea]["name"]));
        $file = $_FILES[$fileArea];
        $upload = FileUtil::getUpload($file, "dashboard");

        if ($upload->save()) {
            $info = $upload->getAttach();
            $file = FileUtil::getAttachUrl() . "/" . $info["type"] . "/" . $info["attachment"];

            if (!$inajax) {
                return $file;
            } else {
                $this->ajaxReturn(array("url" => $file));
            }
        } else {
            return false;
        }
    }

    private function checkAdministrator(array $user)
    {
        if (!empty($user)) {
            $alreadyLogin = 0 < (int) $user["uid"];
            $inAdminIdentity = $user["isadministrator"] == 1;
            if ($alreadyLogin && $inAdminIdentity) {
                return true;
            }
        }

        return false;
    }

    private function checkAccess()
    {
        if (isset($this->user["uid"]) && ($this->user["uid"] == 0)) {
            $this->_access = 0;
        } elseif ($this->_isAdministrator) {
            $lastactivity = MainUtil::getCookie("lastactivity");
            $frozenTime = intval(TIMESTAMP - $lastactivity);

            if ($frozenTime < $this->_cookieLife) {
                $this->_access = 1;
                MainUtil::setCookie("lastactivity", TIMESTAMP);
            } else {
                $this->_access = -1;
            }
        } else {
            $this->_access = -1;
        }

        if ($this->_access == 1) {
            Ibos::app()->session->update();
        } else {
            $requestUrl = Ibos::app()->getRequest()->getUrl();
            $loginUrl = Ibos::app()->getUrlManager()->createUrl($this->loginUrl);

            if (strpos($requestUrl, $loginUrl) !== 0) {
                $this->userLogin();
            }
        }
    }
}
