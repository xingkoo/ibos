<?php

class MobileBaseController extends ICController
{
    const TIMESTAMP = TIMESTAMP;

    /**
     * 移动端模块不适用全局layout
     * @var boolean 
     */
    public $layout = false;
    /**
     * 默认控制器
     * @var string 
     */
    protected $defaultController = "mobile/default/index";
    /**
     * 手机端登录页
     * @var string 
     */
    private $_loginUrl = "mobile/default/login";
    /**
     * session
     * @var array 
     */
    private $_session = array();
    /**
     * 当前登录的用户数组
     * @var array 
     */
    private $_user = array();
    /**
     * 权限标识
     * @var integer 
     */
    private $_access = 0;
    /**
     * 默认的页面属性
     * @var array 
     */
    private $_attributes = array("uid" => 0);
    protected $_extraAttributes = array();

    public function __set($name, $value)
    {
        if (isset($this->_attributes[$name])) {
            $this->_attributes[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    public function __get($name)
    {
        if (isset($this->_attributes[$name])) {
            return $this->_attributes[$name];
        } else {
            parent::__get($name);
        }
    }

    public function __isset($name)
    {
        if (isset($this->_attributes[$name])) {
            return true;
        } else {
            parent::__isset($name);
        }
    }

    public function init()
    {
        $this->_attributes = array_merge($this->_attributes, $this->_extraAttributes);

        if (isset(Ibos::app()->user->uid)) {
            $this->uid = intval(Ibos::app()->user->uid);
        } else {
            $this->uid = 0;
        }

        $user = User::model()->fetchByUid($this->uid);
        $this->_user = $user;
        $this->checkAccess();
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function getUser()
    {
        return $this->_user;
    }

    final public function userLogin()
    {
        Yii::app()->user->loginUrl = array($this->_loginUrl);
        Yii::app()->user->loginRequired();
    }

    private function checkAccess()
    {
        if (!isset($this->_user["uid"]) || ($this->_user["uid"] == 0)) {
            $this->_access = 0;
        } else {
            $this->_session = Session::model()->findByAttributes(array("uid" => $this->_user["uid"]));
            $this->_access = 1;
        }
    }

    protected function getAccess()
    {
        return $this->_access;
    }

    public function filterRoutes($routes)
    {
        return true;
    }
}
