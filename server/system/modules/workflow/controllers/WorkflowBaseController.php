<?php

class WorkflowBaseController extends ICController
{
    /**
     * 当前用户ID
     * @var integer 
     */
    protected $uid = 0;

    protected function setUid($uid)
    {
        $this->uid = $uid;
    }

    protected function getUid()
    {
        return $this->uid;
    }

    public function init()
    {
        $this->setUid(intval(Ibos::app()->user->uid));
        $pageSize = filter_input(INPUT_GET, "pagesize", FILTER_SANITIZE_NUMBER_INT);

        if (!empty($pageSize)) {
            $this->setListPageSize($pageSize);
        }

        parent::init();
    }

    public function checkRunAccess($runId, $processId = 0, $jump = "")
    {
        $per = WfCommonUtil::getRunPermission($runId, $this->getUid(), $processId);

        if (empty($per)) {
            $errMsg = Ibos::lang("Permission denied");

            if (!empty($jump)) {
                $this->error($errMsg, $jump);
            } else {
                exit($errMsg);
            }
        }
    }

    public function checkFlowAccess($flowId, $processId, $jump = "")
    {
        $per = WfNewUtil::checkProcessPermission($flowId, $processId, $this->getUid());

        if (!$per) {
            $errMsg = Ibos::lang("Permission denied");

            if (!empty($jump)) {
                $this->error($errMsg, $jump);
            } else {
                exit($errMsg);
            }
        }
    }

    protected function setListPageSize($size)
    {
        $size = intval($size);
        if ((0 < $size) && in_array($size, array(10, 20, 30, 40, 50))) {
            MainUtil::setCookie("workflow_pagesize_" . $this->getUid(), $size);
        }
    }

    protected function getListPageSize()
    {
        $pageSize = MainUtil::getCookie("workflow_pagesize_" . $this->getUid());

        if (is_null($pageSize)) {
            $pageSize = FlowConst::DEF_PAGE_SIZE;
        }

        return $pageSize;
    }

    protected function countUnReceive()
    {
        return FlowRunProcess::model()->countByAttributes(array("uid" => $this->uid, "flag" => 1));
    }

    protected function countFocus()
    {
        return FlowRun::model()->count(sprintf("FIND_IN_SET(focususer,'%s')", $this->uid));
    }

    protected function countRecycle()
    {
        return FlowRun::model()->countByAttributes(array("beginuser" => $this->uid, "delflag" => 1));
    }
}
