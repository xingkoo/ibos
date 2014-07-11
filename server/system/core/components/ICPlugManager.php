<?php

class ICPlugManager extends CApplicationComponent
{
    /**
     * 是否已安装
     * @var boolean 
     * @access private
     */
    private $_init = false;

    public function setInit($moduleName)
    {
        $installedModule = Ibos::app()->getEnabledModule();

        if (isset($installedModule[$moduleName])) {
            Yii::app()->getModule($moduleName);
            $this->_init = true;
        }
    }

    public function getInit()
    {
        return $this->_init;
    }
}
