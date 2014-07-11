<?php

class OrganizationUserController extends OrganizationBaseController
{
    /**
     *
     * @var string 下拉列表中的<option>格式字符串 
     */
    public $selectFormat = "<option value='\$deptid' \$selected>\$spacer\$deptname</option>";

    public function actionIndex()
    {
        $deptId = intval(EnvUtil::getRequest("deptid"));

        if (EnvUtil::getRequest("op") == "tree") {
            return $this->getDeptTree();
        }

        $type = EnvUtil::getRequest("type");

        if (!in_array($type, array("enabled", "lock", "disabled", "all"))) {
            $type = "enabled";
        }

        $data = array();

        if (EnvUtil::submitCheck("search")) {
            $key = $_POST["keyword"];
            $condition = User::model()->getConditionByDeptIdType(false, $type);
            $list = User::model()->fetchAll("(`username` LIKE '%$key%' OR `realname` LIKE '%$key%') AND " . $condition);
        } else {
            $count = User::model()->countByDeptIdType($deptId, $type);
            $pages = PageUtil::create($count);
            $list = User::model()->fetchAllByDeptIdType($deptId, $type, $pages->getLimit(), $pages->getOffset());
            $data["pages"] = $pages;
        }

        //var_dump($list);exit;

        $data["list"] = $this->handleUserListByPurv($list);
        $data["deptId"] = $deptId;
        $data["type"] = $type;
        $managerVal = NodeRelated::model()->fetchDataValByIdentifier("organization/user/view", Ibos::app()->user->positionid);

        if ($managerVal) {
            $param = array("purvId" => $managerVal);
        } else {
            $param = array();
        }

        $data["perManager"] = Ibos::app()->user->checkAccess("organization/user/add", $param);
        $this->setPageTitle(Ibos::lang("User manager"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Organization"), "url" => $this->createUrl("department/index")),
            array("name" => Ibos::lang("User manager"), "url" => $this->createUrl("user/index")),
            array("name" => Ibos::lang("User list"))
        ));
        $this->render("index", $data);
    }

    public function actionAdd()
    {
        MainUtil::checkLicenseLimit();

        if (EnvUtil::submitCheck("userSubmit")) {
            $origPass = filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING);
            $_POST["salt"] = StringUtil::random(6);
            $_POST["password"] = (!empty($origPass) ? md5(md5($origPass) . $_POST["salt"]) : "");
            $_POST["createtime"] = TIMESTAMP;
            $_POST["guid"] = StringUtil::createGuid();
            $this->dealWithSpecialParams();
            $data = User::model()->create();
            $newId = User::model()->add($data, true);

            if ($newId) {
                UserCount::model()->add(array("uid" => $newId));
                $ip = Ibos::app()->setting->get("clientip");
                UserStatus::model()->add(array("uid" => $newId, "regip" => $ip, "lastip" => $ip));
                UserProfile::model()->add(array("uid" => $newId));

                if (!empty($_POST["auxiliarydept"])) {
                    $deptIds = StringUtil::getId($_POST["auxiliarydept"]);
                    $this->handleAuxiliaryDept($newId, $deptIds, $_POST["deptid"]);
                }

                if (!empty($_POST["auxiliarypos"])) {
                    $posIds = StringUtil::getId($_POST["auxiliarypos"]);
                    $this->handleAuxiliaryPosition($newId, $posIds, $_POST["positionid"]);
                }

                $newUser = User::model()->fetchByPk($newId);
                $users = UserUtil::loadUser();
                $users[$newId] = UserUtil::wrapUserInfo($newUser);
                User::model()->makeCache($users);
                OrgUtil::update();
                OrgUtil::hookSyncUser($newId, $origPass, 1);
                $this->success(Ibos::lang("Save succeed", "message"), $this->createUrl("user/index"));
            } else {
                $this->error(Ibos::lang("Add user failed"), $this->createUrl("user/index"));
            }
        } else {
            $deptid = "";
            $manager = "";
            $account = Ibos::app()->setting->get("setting/account");

            if ($account["mixed"]) {
                $preg = "[0-9]+[A-Za-z]+|[A-Za-z]+[0-9]+";
            } else {
                $preg = "^[A-Za-z0-9\!\@\#$\%\^\&\*\.\~]{" . $account["minlength"] . ",32}\$";
            }

            if ($deptid = EnvUtil::getRequest("deptid")) {
                $deptid = StringUtil::wrapId(EnvUtil::getRequest("deptid"), "d");
                $manager = StringUtil::wrapId(Department::model()->fetchManagerByDeptid(EnvUtil::getRequest("deptid")), "u");
            }

            $this->setPageTitle(Ibos::lang("Add user"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Organization"), "url" => $this->createUrl("department/index")),
                array("name" => Ibos::lang("User manager"), "url" => $this->createUrl("user/index")),
                array("name" => Ibos::lang("Add user"))
            ));
            $this->render("add", array("deptid" => $deptid, "manager" => $manager, "passwordLength" => $account["minlength"], "preg" => $preg));
        }
    }

    public function actionEdit()
    {
        $op = EnvUtil::getRequest("op");
        if ($op && in_array($op, array("enabled", "disabled", "lock")) && Ibos::app()->request->getIsAjaxRequest()) {
            $ids = EnvUtil::getRequest("uid");

            if ($op !== "disabled") {
                MainUtil::checkLicenseLimit();
            }

            return $this->setStatus($op, $ids);
        } else {
            MainUtil::checkLicenseLimit();
        }

        $uid = EnvUtil::getRequest("uid");
        $user = User::model()->fetchByUid($uid);

        if (EnvUtil::submitCheck("userSubmit")) {
            $this->dealWithSpecialParams();

            if (empty($_POST["password"])) {
                unset($_POST["password"]);
            } else {
                $_POST["password"] = md5(md5($_POST["password"]) . $user["salt"]);
                $_POST["lastchangepass"] = TIMESTAMP;
            }

            if (isset($_POST["auxiliarydept"])) {
                $deptIds = StringUtil::getId($_POST["auxiliarydept"]);
                $this->handleAuxiliaryDept($uid, $deptIds, $_POST["deptid"]);
            }

            if (isset($_POST["auxiliarypos"])) {
                $posIds = StringUtil::getId($_POST["auxiliarypos"]);
                $this->handleAuxiliaryPosition($uid, $posIds, $_POST["positionid"]);
            }

            $data = User::model()->create();
            User::model()->updateByUid($uid, $data);
            OrgUtil::update();
            $this->success(Ibos::lang("Save succeed", "message"), $this->createUrl("user/index"));
        } else {
            if (empty($user)) {
                $this->error(Ibos::lang("Request param"), $this->createUrl("user/index"));
            }

            $user["auxiliarydept"] = DepartmentRelated::model()->fetchAllDeptIdByUid($user["uid"]);
            $user["auxiliarypos"] = PositionRelated::model()->fetchAllPositionIdByUid($user["uid"]);
            $account = Ibos::app()->setting->get("setting/account");

            if ($account["mixed"]) {
                $preg = "[0-9]+[A-Za-z]+|[A-Za-z]+[0-9]+";
            } else {
                $preg = "^[A-Za-z0-9\!\@\#$\%\^\&\*\.\~]{" . $account["minlength"] . ",32}\$";
            }

            $this->setPageTitle(Ibos::lang("Edit user"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Organization"), "url" => $this->createUrl("department/index")),
                array("name" => Ibos::lang("User manager"), "url" => $this->createUrl("user/index")),
                array("name" => Ibos::lang("Edit user"))
            ));
            $this->render("edit", array("user" => $user, "passwordLength" => $account["minlength"], "preg" => $preg));
        }
    }

    public function actionExport()
    {
        $uid = urldecode(EnvUtil::getRequest("uid"));
        return UserUtil::exportUser(explode(",", trim($uid, ",")));
    }

    protected function setStatus($status, $uids)
    {
        $uidArr = explode(",", trim($uids, ","));
        $attributes = array();

        switch ($status) {
            case "lock":
                $attributes["status"] = 1;
                break;

            case "disabled":
                $attributes["status"] = 2;
                OrgUtil::hookSyncUser($uids, "", 0);
                break;

            case "enabled":
            default:
                $attributes["status"] = 0;
                OrgUtil::hookSyncUser($uids, "", 2);
                break;
        }

        $return = User::model()->updateByUids($uidArr, $attributes);
        OrgUtil::update();
        return $this->ajaxReturn(array("isSuccess" => !!$return), "json");
    }

    protected function handleAuxiliaryDept($uid, $deptIds, $except = "")
    {
        DepartmentRelated::model()->deleteAll("`uid` = :uid", array(":uid" => $uid));

        foreach ($deptIds as $deptId) {
            if (strcmp($deptId, $except) !== 0) {
                DepartmentRelated::model()->add(array("uid" => $uid, "deptid" => $deptId));
            }
        }
    }

    protected function handleAuxiliaryPosition($uid, $posIds, $except = "")
    {
        PositionRelated::model()->deleteAll("`uid` = :uid", array(":uid" => $uid));

        foreach ($posIds as $posId) {
            if (strcmp($posId, $except) !== 0) {
                PositionRelated::model()->add(array("uid" => $uid, "positionid" => $posId));
            }
        }
    }

    protected function dealWithSpecialParams()
    {
        $_POST["upuid"] = implode(",", StringUtil::getUid($_POST["upuid"]));
        $_POST["deptid"] = implode(",", StringUtil::getId($_POST["deptid"]));
        $_POST["positionid"] = implode(",", StringUtil::getId($_POST["positionid"]));
    }

    protected function getDeptTree()
    {
        $component = new ICDepartmentCategory("Department", "", array("index" => "deptid", "name" => "deptname"));
        $this->ajaxReturn($component->getAjaxCategory($component->getData()), "json");
    }

    public function actionIsRegistered()
    {
        $fieldName = EnvUtil::getRequest("clientid");
        $fieldValue = EnvUtil::getRequest($fieldName);
        $uid = EnvUtil::getRequest("uid");

        if ($uid) {
            $userInfo = User::model()->findByPk($uid);
            $fieldExists = User::model()->fetch("$fieldName = '$fieldValue' and $fieldName != '$userInfo[$fieldName]'");
        } else {
            if (($fieldValue == "") || ($fieldValue == null)) {
                return $this->ajaxReturn(array("isSuccess" => true), "json");
            } else {
                $fieldExists = User::model()->find("$fieldName = :getValue", array(":getValue" => $fieldValue));
            }
        }

        $isRegistered = ($fieldExists ? true : false);
        return $this->ajaxReturn(array("isSuccess" => !$isRegistered), "json");
    }

    protected function getMaxPurv($uid, $posid, $url)
    {
        $allPosIds = PositionRelated::model()->fetchAllPositionIdByUid($uid);
        array_push($allPosIds, $posid);
        array_unique($allPosIds);
        $purvs = array();

        foreach ($allPosIds as $posid) {
            $p = NodeRelated::model()->fetchDataValByIdentifier($url, $posid);
            $purvs[] = intval($p);
        }

        $viewPurv = max($purvs);
        return $viewPurv;
    }

    private function handleUserListByPurv($list)
    {
        if (Ibos::app()->user->isadministrator) {
            return $this->grantManagePermission($list, 1);
        }

        $uid = Ibos::app()->user->uid;
        $curUser = User::model()->fetchByUid($uid);
        $viewPurv = $this->getMaxPurv($uid, $curUser["positionid"], "organization/user/view");
        $ret = array();
        switch ($viewPurv) {
            case 0:
                break;

            case 1:
                foreach ($list as $user) {
                    if ($user["uid"] == $uid) {
                        $ret[] = $user;
                        break;
                    }
                }

                break;

            case 2:
                $subUids = UserUtil::getAllSubs($uid, "", true);
                array_push($subUids, $uid);
                $accordUid = array_unique($subUids);

                foreach ($list as $user) {
                    if (in_array($user["uid"], $accordUid)) {
                        $ret[] = $user;
                    }
                }

                break;

            case 4:
                $branch = Department::model()->getBranchParent($curUser["deptid"]);

                if (!empty($branch)) {
                    $childDeptIds = Department::model()->fetchChildIdByDeptids($branch["deptid"], true);
                    $accordUid = User::model()->fetchAllUidByDeptids($childDeptIds, false);

                    foreach ($list as $user) {
                        if (in_array($user["uid"], $accordUid)) {
                            $ret[] = $user;
                        }
                    }
                } else {
                    $ret = $list;
                }

                break;

            case 8:
                $ret = $list;
                break;
            default:
                break;
        }

        return $this->handleUserManage($ret);
    }

    private function handleUserManage($list)
    {
        if (empty($list)) {
            return $list;
        }

        $uid = Ibos::app()->user->uid;
        $curUser = User::model()->fetchByUid($uid);
        $managePurv = $this->getMaxPurv($uid, $curUser["positionid"], "organization/user/manager");
        switch ($managePurv) {
            /*
        case parent:
            $list = $this->grantManagePermission($list, 0);
            break;

        case :
            foreach ($list as $k => $user ) {
                if ($user["uid"] == $uid) {
                    $list[$k]["perManager"] = 1;
                }
                else {
                    $list[$k]["perManager"] = 0;
                }
            }

            break;

        case :
            $subUids = UserUtil::getAllSubs($uid, "", true);
            array_push($subUids, $uid);
            $accordUid = array_unique($subUids);

            foreach ($list as $k => $user ) {
                if (in_array($user["uid"], $accordUid)) {
                    $list[$k]["perManager"] = 1;
                }
                else {
                    $list[$k]["perManager"] = 0;
                }
            }

            break;

        case :
            $branch = Department::model()->getBranchParent($curUser["deptid"]);

            if (!empty($branch)) {
                $childDeptIds = Department::model()->fetchChildIdByDeptids($branch["deptid"], true);
                $accordUid = User::model()->fetchAllUidByDeptids($childDeptIds, false);

                foreach ($list as $k => $user ) {
                    if (in_array($user["uid"], $accordUid)) {
                        $list[$k]["perManager"] = 1;
                    }
                    else {
                        $list[$k]["perManager"] = 0;
                    }
                }
            }
            else {
                $list = $this->grantManagePermission($list, 1);
            }

            break;

        case :
            $list = $this->grantManagePermission($list, 1);
            break;
*/
            default:
                $list = $this->grantManagePermission($list, 0);
                break;
        }

        return $list;
    }

    private function grantManagePermission($users, $permission)
    {
        foreach ($users as $k => $user) {
            $users[$k]["perManager"] = $permission;
        }

        return $users;
    }
}
