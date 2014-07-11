<?php

class OrganizationCategoryController extends OrganizationBaseController
{
    /**
     * 当前分类对象
     * @var mixed 
     */
    private $_category;

    public function init()
    {
        if ($this->_category === null) {
            $this->_category = new ICPositionCategory("PositionCategory");
        }
    }

    public function actionIndex()
    {
        if (Yii::app()->request->getIsAjaxRequest()) {
            $data = $this->_category->getData();
            $this->ajaxReturn($this->_category->getAjaxCategory($data), "json");
        }
    }

    public function actionAdd()
    {
        $pid = EnvUtil::getRequest("pid");
        $name = trim(EnvUtil::getRequest("name"));
        $ret = $this->_category->add($pid, $name);
        $this->ajaxReturn(array("IsSuccess" => !!$ret, "id" => $ret), "json");
    }

    public function actionEdit()
    {
        $pid = EnvUtil::getRequest("pid");
        $catid = EnvUtil::getRequest("catid");

        if (EnvUtil::getRequest("op") === "move") {
            $moveType = EnvUtil::getRequest("type");
            return $this->move($moveType, $catid, $pid);
        }

        $name = trim(EnvUtil::getRequest("name"));
        $ret = $this->_category->edit($catid, $pid, $name);
        $this->ajaxReturn(array("IsSuccess" => !!$ret), "json");
    }

    public function actionDelete()
    {
        $catid = EnvUtil::getRequest("catid");
        $category = PositionCategory::model()->fetchByPk($catid);
        $supCategoryNum = PositionCategory::model()->countByAttributes(array("pid" => 0));
        if (!empty($category) && ($category["pid"] == 0) && ($supCategoryNum == 1)) {
            $this->ajaxReturn(array("IsSuccess" => false, "msg" => Ibos::lang("Leave at least a Category")), "json");
        }

        $ret = $this->_category->delete($catid);
        $this->ajaxReturn(array("IsSuccess" => !!$ret), "json");
    }

    protected function move($type, $catid, $pid)
    {
        $ret = $this->_category->move($type, $catid, $pid);
        $this->ajaxReturn(array("IsSuccess" => !!$ret), "json");
    }
}
