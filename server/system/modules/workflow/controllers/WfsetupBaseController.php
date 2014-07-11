<?php

class WfsetupBaseController extends ICController
{
    const DEFAULT_PAGE_SIZE = 10;

    /**
     * 默认的页面属性
     * @var array 
     */
    private $_attributes = array(
        "uid"      => 0,
        "catid"    => 0,
        "flowid"   => 0,
        "category" => array()
        );

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
        $this->uid = intval(Ibos::app()->user->uid);
        parent::init();
    }

    protected function getCatId()
    {
        if (empty($this->catid) && !empty($this->category)) {
            $firstCat = array_slice($this->category, 0, 1);
            $catId = $this->catid = $firstCat[0]["catid"];
        } else {
            $catId = $this->catid;
        }

        return $catId;
    }

    protected function setListPageSize($size)
    {
        $size = intval($size);
        if ((0 < $size) && in_array($size, array(10, 20, 30, 40, 50))) {
            MainUtil::setCookie("workflow_pagesize_" . $this->uid, $size);
        }
    }

    protected function setGuideProcess(ICFlowType $flow, $curStep)
    {
        $guideProcess = $flow->guideprocess;
        $processPart = explode(",", $guideProcess);

        if (!in_array($curStep, $processPart)) {
            $processPart[] = $curStep;
            sort($processPart);
            $newProcess = implode(",", $processPart);
            FlowType::model()->updateByPk($flow->getID(), array("guideprocess" => $newProcess));
        }
    }

    protected function getListPageSize()
    {
        $pageSize = MainUtil::getCookie("workflow_pagesize_" . $this->uid);

        if (is_null($pageSize)) {
            $pageSize = self::DEFAULT_PAGE_SIZE;
        }

        return $pageSize;
    }
}
