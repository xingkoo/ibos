<?php

class WeiboBaseController extends ICController
{
    protected $_extraAttributes = array();
    /**
     * 默认的页面属性
     * @var array 
     */
    private $_attributes = array("uid" => 0);

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
        $this->uid = intval(Ibos::app()->user->uid);
        parent::init();
    }
}
