<?php

class ContactBaseController extends ICController
{
    protected $allLetters = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");

    protected function getSidebar()
    {
        $sidebarAlias = "application.modules.contact.views.sidebar";
        $dept = DepartmentUtil::loadDepartment();
        $params = array("dept" => $dept, "lang" => Ibos::getLangSource("contact.default"), "unit" => Ibos::app()->setting->get("setting/unit"));
        $sidebarView = $this->renderPartial($sidebarAlias, $params, true);
        return $sidebarView;
    }

    protected function getDataByDept()
    {
        $deptid = intval(EnvUtil::getRequest("deptid"));
        $allDepts = DepartmentUtil::loadDepartment();

        if (!empty($deptid)) {
            $childDepts = Department::model()->fetchChildDeptByDeptid($deptid, $allDepts);
            $selfDept = Department::model()->fetchByPk($deptid);
            $depts = array_merge(array($selfDept), $childDepts);
            $deptsTmp = ContactUtil::handleDeptData($depts, $deptid);
            $depts = array_merge(array($selfDept), $deptsTmp);
        } else {
            $depts = ContactUtil::handleDeptData($allDepts, 0);
        }

        if (!empty($depts)) {
            foreach ($depts as $k => $childDept) {
                $pDeptids = Department::model()->queryDept($childDept["deptid"]);
                $depts[$k]["pDeptids"] = (!empty($pDeptids) ? array_reverse(explode(",", trim($pDeptids))) : array());
                $deptUids = User::model()->fetchAllUidByDeptid($childDept["deptid"], false);
                $deptRelatedUids = DepartmentRelated::model()->fetchAllUidByDeptId($childDept["deptid"]);
                $uids = array_unique(array_merge($deptUids, $deptRelatedUids));
                $uids = $this->removeDisabledUid($uids);
                $depts[$k]["users"] = User::model()->fetchAllByUids($uids);
            }
        }

        return $depts;
    }

    private function removeDisabledUid($uids)
    {
        if (!is_array($uids)) {
            return null;
        }

        $disabledUids = User::model()->fetchAllUidsByStatus(2);

        foreach ($uids as $k => $uid) {
            if (in_array($uid, $disabledUids)) {
                unset($uids[$k]);
            }
        }

        return $uids;
    }

    protected function getDataByLetter()
    {
        $deptid = intval(EnvUtil::getRequest("deptid"));

        if (!empty($deptid)) {
            $deptids = Department::model()->fetchChildIdByDeptids($deptid, true);
            $uids = User::model()->fetchAllUidByDeptids($deptids, false);
        } else {
            $users = UserUtil::loadUser();
            $uids = ConvertUtil::getSubByKey($users, "uid");
        }

        $uids = $this->removeDisabledUid($uids);
        $res = UserUtil::getUserByPy($uids);
        return ContactUtil::handleLetterGroup($res);
    }

    protected function ajaxApi()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $op = EnvUtil::getRequest("op");

            if (!in_array($op, array("getProfile", "changeConstant", "export", "printContact"))) {
                $this->ajaxReturn(array("isSuccess" => false, 0 => Ibos::lang("Request tainting", "error")));
            }

            $this->{$op}();
        }
    }

    protected function getProfile()
    {
        $uid = intval(EnvUtil::getRequest("uid"));
        $user = User::model()->fetchByUid($uid);
        $user["fax"] = "";

        if (!empty($user["deptid"])) {
            $dept = Department::model()->fetchByPk($user["deptid"]);
            $user["fax"] = $dept["fax"];
        }

        $user["birthday"] = (!empty($user["birthday"]) ? date("Y-m-d", $user["birthday"]) : "");
        $cuids = Contact::model()->fetchAllConstantByUid(Ibos::app()->user->uid);
        $this->ajaxReturn(array("isSuccess" => true, "user" => $user, "uid" => Ibos::app()->user->uid, "cuids" => $cuids));
    }

    protected function changeConstant()
    {
        $uid = Ibos::app()->user->uid;
        $cuid = intval(EnvUtil::getRequest("cuid"));
        $status = EnvUtil::getRequest("status");

        if ($status == "mark") {
            Contact::model()->addConstant($uid, $cuid);
        } elseif ($status == "unmark") {
            Contact::model()->deleteConstant($uid, $cuid);
        }

        $this->ajaxReturn(array("isSuccess" => true));
    }

    public function export()
    {
        $userDatas = $this->getUserData();
        $fieldArr = array(Ibos::lang("Real name"), Ibos::lang("Position"), Ibos::lang("Telephone"), Ibos::lang("Cell phone"), Ibos::lang("Email"), Ibos::lang("QQ"));
        $str = implode(",", $fieldArr) . "\n";

        foreach ($userDatas as $user) {
            $realname = $user["realname"];
            $posname = $user["posname"];
            $telephone = $user["telephone"];
            $mobile = $user["mobile"];
            $email = $user["email"];
            $qq = $user["qq"];
            $str .= $realname . "," . $posname . "," . $telephone . "," . $mobile . "," . $email . "," . $qq . "\n";
        }

        $outputStr = iconv("utf-8", "gbk//ignore", $str);
        $filename = date("Y-m-d") . mt_rand(100, 999) . ".csv";
        FileUtil::exportCsv($filename, $outputStr);
    }

    public function printContact()
    {
        $datas = $this->getDataByDept();
        $params = array("datas" => $datas, "lang" => Ibos::getLangSource("contact.default"), "uint" => Ibos::app()->setting->get("setting/unit"), "assetUrl" => Ibos::app()->assetManager->getAssetsUrl("contact"));
        $detailAlias = "application.modules.contact.views.default.print";
        $detailView = $this->renderPartial($detailAlias, $params, true);
        $this->ajaxReturn(array("view" => $detailView, "isSuccess" => true));
    }

    protected function getUserData()
    {
        $uids = EnvUtil::getRequest("uids");
        $userDatas = array();

        if (!empty($uids)) {
            $uidArr = explode(",", $uids);

            foreach ($uidArr as $uid) {
                $userDatas[] = User::model()->fetchByUid($uid);
            }
        }

        return $userDatas;
    }
}
