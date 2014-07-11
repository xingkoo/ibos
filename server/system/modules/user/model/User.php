<?php

class User extends ICModel
{
    public static function model($className = "User")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{user}}";
    }

    public function fetchByRealname($name)
    {
        $user = $this->fetch("realname = :name", array(":name" => $name));
        $users = UserUtil::loadUser();

        if (!empty($user)) {
            if (isset($users[$user["uid"]])) {
                $user = $users[$user["uid"]];
            } else {
                $user = UserUtil::wrapUserInfo($user);
            }
        }

        return $user;
    }

    public function fetchAllByRealnames($realnames)
    {
        $usersData = array();

        if (!empty($realnames)) {
            $users = UserUtil::loadUser();
            $criteria = array("select" => "*", "condition" => sprintf("realname IN (%s)", StringUtil::iImplode($realnames)));
            $list = $this->fetchAll($criteria);

            foreach ($list as $user) {
                if (isset($users[$user["uid"]])) {
                    $usersData[$user["uid"]] = $users[$user["uid"]];
                } else {
                    $usersData[$user["uid"]] = UserUtil::wrapUserInfo($user);
                }
            }
        }

        return $usersData;
    }

    public function fetchRealnameByUid($uid)
    {
        static $users = array();

        if (!isset($users[$uid])) {
            $user = $this->fetchByUid($uid);

            if (isset($user["realname"])) {
                $users[$uid] = $user["realname"];
            } else {
                return false;
            }
        }

        return $users[$uid];
    }

    public function fetchRealnamesByUids($uids, $glue = ",")
    {
        $uid = (is_array($uids) ? $uids : explode(",", StringUtil::filterStr($uids)));
        $names = array();

        foreach ($uid as $id) {
            if (!empty($id)) {
                $names[] = $this->fetchRealnameByUid($id);
            }
        }

        return implode($glue, $names);
    }

    public function fetchByUid($uid)
    {
        $users = UserUtil::loadUser();

        if (!isset($users[$uid])) {
            $object = $this->findByPk($uid);

            if (is_object($object)) {
                $user = $object->attributes;
                $users[$uid] = UserUtil::wrapUserInfo($user);
                $this->makeCache($users);
            } else {
                return array();
            }
        }

        return $users[$uid];
    }

    public function makeCache($users)
    {
        Syscache::model()->modify("users", $users);
        CacheUtil::load("users");
    }

    public function fetchAllFitDeptUser($dept)
    {
        $list = Ibos::app()->db->createCommand()->select("u.uid")->from("{{user}} u")->leftJoin("{{department_related}} dr", "u.uid = dr.uid")->where("u.status IN (1,0) AND ((FIND_IN_SET(u.deptid,'$dept') OR FIND_IN_SET(dr.deptid,'$dept')))")->queryAll();

        foreach ($list as &$user) {
            $user = $this->fetchByUid($user["uid"]);
        }

        return $list;
    }

    public function fetchAllOtherManager($dept)
    {
        $list = Ibos::app()->db->createCommand()->select("u.uid")->from("{{user}} u")->leftJoin("{{department_related}} dr", "u.uid = dr.uid")->where("u.status IN (1,0) AND ((FIND_IN_SET(u.deptid,'$dept') OR FIND_IN_SET(dr.deptid,'$dept')))")->queryAll();
        return $list;
    }

    public function fetchAllByUids($uids)
    {
        $users = UserUtil::loadUser();
        $record = array_intersect_key($users, array_flip($uids));
        if (empty($record) || (count($uids) != count($record))) {
            if (is_array($record) && !empty($record)) {
                $uids = array_diff($uids, array_keys($record));
            }

            if (!empty($uids)) {
                $records = $this->findAllByPk(array_merge($uids));

                if (!empty($records)) {
                    foreach ($records as $rec) {
                        $user = $rec->attributes;
                        $record[$user["uid"]] = $users[$user["uid"]] = UserUtil::wrapUserInfo($user);
                    }

                    $this->makeCache($users);
                }
            }
        }

        return $record;
    }

    public function fetchUidByPosId($posId, $returnDisabled = true)
    {
        static $posIds = array();

        if (!isset($posIds[$posId])) {
            $posIds[$posId] = array();
            $extCondition = ($returnDisabled ? 1 : " status != 2 ");
            $criteria = array("select" => "uid", "condition" => "`positionid`=$posId AND $extCondition");
            $posCriteria = array("select" => "uid", "condition" => "`positionid`=$posId");
            $main = $this->fetchAll($criteria);
            $auxiliary = PositionRelated::model()->fetchAll($posCriteria);

            foreach (array_merge($main, $auxiliary) as $uid) {
                $posIds[$posId][] = $uid["uid"];
            }
        }

        return $posIds[$posId];
    }

    public function fetchAllUidByPositionIds($positionIds, $returnDisabled = true)
    {
        $positionIds = (is_array($positionIds) ? implode(",", $positionIds) : $positionIds);
        $extCondition = ($returnDisabled ? 1 : " status != 2 ");
        $criteria = array("select" => "uid", "condition" => "FIND_IN_SET(`positionid`, '$positionIds') AND $extCondition");
        $users = $this->fetchAll($criteria);
        $uids = ConvertUtil::getSubByKey($users, "uid");
        return $uids;
    }

    public function fetchAllUidByDeptid($deptId, $returnDisabled = true)
    {
        static $deptIds = array();

        if (!isset($deptIds[$deptId])) {
            $extCondition = ($returnDisabled ? 1 : " status != 2 ");
            $criteria = array("select" => "uid", "condition" => "`deptid`=$deptId AND $extCondition");
            $uids = $this->fetchAll($criteria);
            $uids = ConvertUtil::getSubByKey($uids, "uid");
            $deptIds[$deptId] = $uids;
        }

        return $deptIds[$deptId];
    }

    public function fetchAllUidByDeptids($deptIds, $returnDisabled = true)
    {
        $deptIds = (is_array($deptIds) ? implode(",", $deptIds) : $deptIds);
        $extCondition = ($returnDisabled ? 1 : " status != 2 ");
        $criteria = array("select" => "uid", "condition" => "FIND_IN_SET(`deptid`, '$deptIds') AND $extCondition");
        $users = $this->fetchAll($criteria);
        $uids = ConvertUtil::getSubByKey($users, "uid");
        return $uids;
    }

    public function fetchAllCredit()
    {
        $condition = array("select" => "uid", "condition" => "status != 2", "order" => "credits DESC");
        $ids = $this->fetchAll($condition);
        $result = array();

        if (!empty($ids)) {
            $result = ConvertUtil::getSubByKey($ids, "uid");
        }

        return $result;
    }

    public function fetchAllByDeptIdType($deptId, $type, $limit, $offset)
    {
        $condition = array("condition" => $this->getConditionByDeptIdType($deptId, $type), "order" => "createtime DESC", "limit" => $limit, "offset" => $offset);
        return $this->fetchAll($condition);
    }

    public function updateByUids($uids, $attributes = array())
    {
        $uids = (is_array($uids) ? $uids : explode(",", $uids));
        $condition = "FIND_IN_SET(uid,'" . implode(",", $uids) . "')";
        $counter = $this->updateAll($attributes, $condition);
        $users = UserUtil::loadUser();
        $records = $this->findAllByPk($uids);

        if (!empty($records)) {
            foreach ($records as $rec) {
                $user = $rec->attributes;
                $users[$user["uid"]] = UserUtil::wrapUserInfo($user);
            }

            $this->makeCache($users);
        }

        return $counter;
    }

    public function updateByUid($uid, $attributes)
    {
        $counter = $this->updateByPk($uid, $attributes);
        $users = UserUtil::loadUser();
        $users[$uid] = UserUtil::wrapUserInfo(array_merge($users[$uid], $attributes));
        $this->makeCache($users);
        return $counter;
    }

    public function countByDeptIdType($deptId, $type)
    {
        return $this->count(array("condition" => $this->getConditionByDeptIdType($deptId, $type)));
    }

    public function getConditionByDeptIdType($deptId, $type)
    {
        $condition = ($deptId ? "`deptid` = $deptId AND " : "");

        switch ($type) {
            case "enabled":
                $condition .= "`status` = 0";
                break;

            case "lock":
                $condition .= "`status` = 1";
                break;

            case "disabled":
                $condition .= "`status` = 2";
                break;

            default:
                $condition .= "1";
                break;
        }

        return $condition;
    }

    public function fetchSubUidByUid($uid)
    {
        $subUid = $this->fetchAll(array(
            "select"    => "uid",
            "condition" => "upuid=:upuid AND status != 2",
            "params"    => array(":upuid" => $uid)
        ));
        $uidArr = ConvertUtil::getSubByKey($subUid, "uid");
        return $uidArr;
    }

    public function fetchSubByPk($uid, $limitCondition = "")
    {
        $records = $this->fetchAll(array(
            "select"    => "uid, username, deptid, upuid, realname",
            "condition" => "upuid=:upuid AND status != 2" . $limitCondition,
            "params"    => array(":upuid" => $uid)
        ));
        $userArr = array();

        foreach ($records as $user) {
            $userArr[] = $user;
        }

        return $userArr;
    }

    public function fetchAllUidsByStatus($status)
    {
        $records = $this->fetchAll(array(
            "select"    => "uid",
            "condition" => "status = :status",
            "params"    => array("status" => $status)
        ));
        return ConvertUtil::getSubByKey($records, "uid");
    }

    public function removeDisableUids($uids)
    {
        $uids = (is_array($uids) ? implode(",", $uids) : $uids);
        $records = $this->fetchAll(array("select" => "uid", "condition" => sprintf("FIND_IN_SET(`uid`, '%s') AND status != %d", $uids, 2)));
        return ConvertUtil::getSubByKey($records, "uid");
    }
}
