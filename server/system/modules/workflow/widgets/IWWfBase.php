<?php

class IWWfBase extends CWidget
{
    /**
     * 工作流挂件渲染视图Path
     * @var string 
     */
    protected $widgetPath = "application.modules.workflow.views.widget";
    /**
     * 用户ID
     * @var integer 
     */
    protected $uid;
    /**
     * 工作流办理的属性数组
     * @var array 
     */
    protected $key = array();

    public function init()
    {
        $widgetPath = Ibos::getPathOfAlias($this->widgetPath);
        Ibos::setPathOfAlias("wfwidget", $widgetPath);
    }

    public function setKey($key)
    {
        $param = WfCommonUtil::param($key, "DECODE");
        $this->key = $param;
    }

    public function getKey($index = null)
    {
        if (!empty($index) && isset($this->key[$index])) {
            return $this->key[$index];
        } else {
            return $this->key;
        }
    }

    public function makeKey($param = array())
    {
        if (!empty($param)) {
            return WfCommonUtil::param($param);
        }
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function setUid($uid)
    {
        $this->uid = intval($uid);
    }
}
