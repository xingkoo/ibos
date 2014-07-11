<?php

class Officialdoc extends ICModel
{
    public static function model($className = "Officialdoc")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{doc}}";
    }

    public function fetchAllAndPage($conditions = "", $pageSize = null)
    {
        $conditionArray = array("condition" => $conditions, "order" => "istop DESC, addtime DESC");
        $criteria = new CDbCriteria();

        foreach ($conditionArray as $key => $value) {
            $criteria->$key = $value;
        }

        $count = $this->count($criteria);
        $pages = new CPagination($count);
        $everyPage = (is_null($pageSize) ? Yii::app()->params["basePerPage"] : $pageSize);
        $pages->setPageSize(intval($everyPage));
        $pages->applyLimit($criteria);
        $datas = $this->fetchAll($criteria);
        return array("pages" => $pages, "datas" => $datas);
    }

    public function updateIsOverHighLight()
    {
        $result = $this->updateAll(array("ishighlight" => 0, "highlightstyle" => "", "highlightendtime" => ""), "ishighlight = 1 AND highlightendtime<" . TIMESTAMP);
        return $result;
    }

    public function updateHighlightStatus($ids, $ishighlight, $highlightstyle, $highlightendtime)
    {
        $attributes = array("ishighlight" => $ishighlight, "highlightstyle" => $highlightstyle, "highlightendtime" => $highlightendtime);
        return $this->updateAll($attributes, "docid IN ($ids)");
    }

    public function deleteAllByDocIds($ids)
    {
        return $this->deleteAll("docid IN ($ids)");
    }

    public function updateAllStatusByDocids($ids, $status, $approver)
    {
        return $this->updateAll(array("status" => $status, "approver" => $approver, "uptime" => TIMESTAMP), "docid IN ($ids)");
    }

    public function cancelTop()
    {
        $result = $this->updateAll(array("istop" => 0, "toptime" => "", "topendtime" => ""), "istop = 1 AND topendtime<" . TIMESTAMP);
        return $result;
    }

    public function updateTopStatus($ids, $isTop, $topTime, $topEndTime)
    {
        $condition = array("istop" => $isTop, "toptime" => $topTime, "topendtime" => $topEndTime);
        return $this->updateAll($condition, "docid IN ($ids)");
    }

    public function updateAllCatidByDocids($ids, $catid)
    {
        return $this->updateAll(array("catid" => $catid), "docid IN ($ids)");
    }

    public function updateClickCount($id, $clickCount = 0)
    {
        if (empty($clickCount)) {
            $record = $this->fetch(array("select" => "clickcount", "condition" => "docid = '$id'"));
            $clickCount = $record["clickcount"];
        }

        return $this->modify($id, array("clickcount" => $clickCount + 1));
    }

    public function countNoSignByUid($uid)
    {
        $isSignIdArr = OfficialdocReader::model()->fetchDocidsByUid($uid);
        $condition = OfficialdocUtil::joinListCondition("nosign", $isSignIdArr);
        $count = $this->count($condition);
        return $count;
    }

    public function fetchAidsByDocids($docids)
    {
        $rows = $this->fetchAll(array("select" => "attachmentid", "condition" => "FIND_IN_SET(docid,'$docids')"));
        $res = ConvertUtil::getSubByKey($rows, "attachmentid");
        return $res;
    }

    public function fetchAllUidsByDocId($docId)
    {
        $doc = $this->fetchByPk($docId);

        if (empty($doc)) {
            return null;
        }

        if (($doc["deptid"] == "alldept") || (empty($doc["deptid"]) && empty($doc["positionid"]) && empty($doc["uid"]))) {
            $users = UserUtil::loadUser();
            $uids = ConvertUtil::getSubByKey($users, "uid");
        } else {
            $uids = array();

            if (!empty($doc["deptid"])) {
                $deptids = Department::model()->fetchChildIdByDeptids($doc["deptid"], true);
                $uids = array_merge($uids, User::model()->fetchAllUidByDeptids($deptids, false));
            }

            if (!empty($doc["positionid"])) {
                $uids = array_merge($uids, User::model()->fetchAllUidByPositionIds($doc["positionid"], false));
            }

            if (!empty($doc["uid"])) {
                $uids = array_merge($uids, explode(",", $doc["uid"]));
            }
        }

        return array_unique($uids);
    }

    public function fetchUnApprovalDocIds($catid, $uid)
    {
        $backDocIds = OfficialdocBack::model()->fetchAllBackDocId();
        $backDocIdStr = implode(",", $backDocIds);
        $backCondition = (empty($backDocIdStr) ? "" : "AND `docid` NOT IN($backDocIdStr)");

        if (empty($catid)) {
            $catids = OfficialdocCategory::model()->fetchAllApprovalCatidByUid($uid);
            $catidStr = implode(",", $catids);
            $condition = "((FIND_IN_SET( `catid`, '$catidStr' ) $backCondition) OR `author` = $uid)";
        } else {
            $isApproval = OfficialdocCategory::model()->checkIsApproval($catid, $uid);
            $condition = ($isApproval ? "(`catid` = $catid $backCondition)" : " (`catid` = $catid AND `author` = $uid)");
        }

        $record = $this->fetchAll(array(
            "select"    => array("docid"),
            "condition" => "`status` = 2 AND " . $condition
        ));
        $docIds = ConvertUtil::getSubByKey($record, "docid");
        return $docIds;
    }
}
