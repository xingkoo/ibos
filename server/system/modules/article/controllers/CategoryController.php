<?php

class ArticleCategoryController extends ArticleBaseController
{
    /**
     * 分类对象
     * @var object
     * @access private
     */
    private $_category;

    public function init()
    {
        if ($this->_category === null) {
            $this->_category = new ICArticleCategory("ArticleCategory");
        }
    }

    public function actionIndex()
    {
        if (Yii::app()->request->getIsAjaxRequest()) {
            $data = ArticleCategory::model()->fetchAll(array("order" => "sort ASC"));
            $this->ajaxReturn($this->_category->getAjaxCategory($data), "json");
        }
    }

    public function actionAdd()
    {
        $pid = EnvUtil::getRequest("pid");
        $name = trim(EnvUtil::getRequest("name"));
        $aid = intval(EnvUtil::getRequest("aid"));
        $cond = array("select" => "sort", "order" => "`sort` DESC");
        $sortRecord = ArticleCategory::model()->fetch($cond);

        if (empty($sortRecord)) {
            $sortId = 0;
        } else {
            $sortId = $sortRecord["sort"];
        }

        $newSortId = $sortId + 1;
        $ret = ArticleCategory::model()->add(array("sort" => $newSortId, "pid" => $pid, "name" => $name, "aid" => $aid), true);
        $url = $this->createUrl("default/index&catid=" . $ret);
        $this->ajaxReturn(array("IsSuccess" => !!$ret, "id" => $ret, "url" => $url, "aid" => $aid), "json");
    }

    public function actionEdit()
    {
        $op = EnvUtil::getRequest("op");
        $option = (empty($op) ? "default" : $op);

        if ($option == "default") {
            $pid = intval(EnvUtil::getRequest("pid"));
            $name = trim(EnvUtil::getRequest("name"));
            $catid = intval(EnvUtil::getRequest("catid"));
            $aid = intval(EnvUtil::getRequest("aid"));

            if ($pid == $catid) {
                $this->error(Ibos::lang("Parent and current can not be the same"));
            }

            $ret = ArticleCategory::model()->modify($catid, array("pid" => $pid, "name" => $name, "aid" => $aid));
            $this->ajaxReturn(array("IsSuccess" => !!$ret, "aid" => $aid), "json");
        } else {
            $this->{$option}();
        }
    }

    public function actionDel()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $catid = EnvUtil::getRequest("catid");
            $category = ArticleCategory::model()->fetchByPk($catid);
            $supCategoryNum = ArticleCategory::model()->countByAttributes(array("pid" => 0));
            if (!empty($category) && ($category["pid"] == 0) && ($supCategoryNum == 1)) {
                $this->ajaxReturn(array("IsSuccess" => false, "msg" => Ibos::lang("Leave at least a Category")), "json");
            }

            $ret = $this->_category->delete($catid);

            if ($ret == -1) {
                $this->ajaxReturn(array("IsSuccess" => false, "msg" => Ibos::lang("Contents under this classification only be deleted when no content")), "json");
            }

            $this->ajaxReturn(array("IsSuccess" => !!$ret), "json");
        }
    }

    protected function move()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $moveType = EnvUtil::getRequest("type");
            $pid = EnvUtil::getRequest("pid");
            $catid = EnvUtil::getRequest("catid");
            $ret = $this->_category->move($moveType, $catid, $pid);
            $this->ajaxReturn(array("IsSuccess" => !!$ret), "json");
        }
    }

    protected function getApproval()
    {
        $approvals = Approval::model()->fetchAllApproval();
        $this->ajaxReturn(array("approvals" => $approvals));
    }

    protected function getCurApproval()
    {
        $catid = EnvUtil::getRequest("catid");
        $category = ArticleCategory::model()->fetchByPk($catid);
        $approval = Approval::model()->fetchByPk($category["aid"]);
        $this->ajaxReturn(array("approval" => $approval));
    }
}
