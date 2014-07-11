<?php

class OrganizationDepartmentController extends OrganizationBaseController
{
    /**
     * 下拉选择框字符串格式
     * @var string 
     */
    public $selectFormat = "<option value='\$deptid' \$selected>\$spacer\$deptname</option>";

    public function actionIndex()
    {
        $op = EnvUtil::getRequest("op");
        if (!empty($op) && in_array($op, array("get"))) {
            return $this->{$op}();
        }

        $dept = DepartmentUtil::loadDepartment();
        $data = array("dept" => $dept, "tree" => StringUtil::getTree($dept, $this->selectFormat), "unit" => Ibos::app()->setting->get("setting/unit"), "license" => Ibos::app()->setting->get("setting/license"), "perAdd" => Ibos::app()->user->checkAccess("organization/department/add"), "perEdit" => Ibos::app()->user->checkAccess("organization/department/edit"), "perDel" => Ibos::app()->user->checkAccess("organization/department/del"));
        $this->setPageTitle(Ibos::lang("Department manage"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Organization"), "url" => $this->createUrl("department/index")),
            array("name" => Ibos::lang("Department manage"), "url" => $this->createUrl("department/index")),
            array("name" => Ibos::lang("Department list"))
        ));
        $this->render("index", $data);
    }

    public function actionAdd()
    {
        if (Ibos::app()->request->getIsAjaxRequest()) {
            $this->dealWithBranch();
            $this->dealWithSpecialParams();
            $data = Department::model()->create();
            $newId = Department::model()->add($data, true);
            Department::model()->modify($newId, array("sort" => $newId));
            $newId && OrgUtil::update();
            $this->ajaxReturn(array("IsSuccess" => !!$newId, "id" => $newId), "json");
        }
    }

    public function actionEdit()
    {
        if (Ibos::app()->request->getIsAjaxRequest()) {
            if (EnvUtil::getRequest("op") === "structure") {
                $curId = EnvUtil::getRequest("curid");
                $objId = EnvUtil::getRequest("objid");
                $status = $this->setStructure($curId, $objId);
                $this->ajaxReturn(array("IsSuccess" => $status), "json");
            }

            $deptId = EnvUtil::getRequest("deptid");

            if ($deptId == 0) {
                $keys = array("phone", "fullname", "shortname", "fax", "zipcode", "address", "adminemail");
                $postData = array();

                foreach ($_POST as $key => $value) {
                    if (in_array($key, $keys)) {
                        $postData[$key] = $value;
                    }
                }

                Setting::model()->updateSettingValueByKey("unit", $postData);
                $editStatus = true;
                CacheUtil::update(array("setting"));
            } else {
                $this->dealWithBranch();
                $this->dealWithSpecialParams();
                $data = Department::model()->create();
                $editStatus = Department::model()->modify($data["deptid"], $data);
                $editStatus && OrgUtil::update();
            }

            $this->ajaxReturn(array("IsSuccess" => !!$editStatus), "json");
        }
    }

    public function actionDel()
    {
        if (Ibos::app()->request->getIsAjaxRequest()) {
            $delId = EnvUtil::getRequest("id");

            if (Department::model()->countChildByDeptId($delId)) {
                $delStatus = false;
                $msg = Ibos::lang("Remove the child department first");
            } else {
                $delStatus = Department::model()->remove($delId);
                DepartmentRelated::model()->deleteAll("deptid = :deptid", array(":deptid" => $delId));
                $relatedIds = User::model()->fetchAllUidByDeptid($delId);

                if (!empty($relatedIds)) {
                    User::model()->updateByUids($relatedIds, array("deptid" => 0));
                }

                $delStatus && OrgUtil::update();
                $msg = Ibos::lang("Operation succeed", "message");
            }

            $this->ajaxReturn(array("IsSuccess" => !!$delStatus, "msg" => $msg), "json");
        }
    }

    protected function get()
    {
        if (Ibos::app()->request->getIsAjaxRequest()) {
            $id = EnvUtil::getRequest("id");

            if ($id == 0) {
                $result = Ibos::app()->setting->get("setting/unit");
            } else {
                $result = Department::model()->fetchByPk($id);
                $result["manager"] = StringUtil::wrapId(array($result["manager"]));
                $result["leader"] = StringUtil::wrapId(array($result["leader"]));
                $result["subleader"] = StringUtil::wrapId(array($result["subleader"]));
            }

            $this->ajaxReturn($result, "json");
        }
    }

    protected function setStructure($curId, $objId)
    {
        $obj = Department::model()->fetchByPk($objId);
        $current = Department::model()->fetchByPk($curId);
        $curSort = $current["sort"];
        $objSort = $obj["sort"];
        Department::model()->modify($curId, array("sort" => $objSort));
        Department::model()->modify($objId, array("sort" => $curSort));
        return true;
    }

    protected function dealWithBranch()
    {
        $isBranch = EnvUtil::getRequest("isbranch");
        $pid = EnvUtil::getRequest("pid");

        if ($isBranch) {
            if (($pid == 0) || Department::model()->getIsBranch($pid)) {
            } else {
                $this->ajaxReturn(array("IsSuccess" => false, "msg" => Ibos::lang("Incorrect branch setting")), "json");
            }
        }
    }

    protected function dealWithSpecialParams()
    {
        $_POST["manager"] = implode(",", StringUtil::getUid($_POST["manager"]));
        $_POST["leader"] = implode(",", StringUtil::getUid($_POST["leader"]));
        $_POST["subleader"] = implode(",", StringUtil::getUid($_POST["subleader"]));
    }
}
