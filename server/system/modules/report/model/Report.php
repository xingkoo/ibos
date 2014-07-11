<?php

class Report extends ICModel
{
    public static function model($className = "Report")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{report}}";
    }

    public function fetchAllByPage($condition, $pageSize = 0)
    {
        $conditionArray = array("condition" => $condition, "order" => "addtime DESC");
        $criteria = new CDbCriteria();

        foreach ($conditionArray as $key => $value) {
            $criteria->$key = $value;
        }

        $count = $this->count($criteria);
        $pagination = new CPagination($count);
        $everyPage = (empty($pageSize) ? Ibos::app()->params["basePerPage"] : $pageSize);
        $pagination->setPageSize(intval($everyPage));
        $pagination->applyLimit($criteria);
        $reportList = $this->fetchAll($criteria);
        return array("pagination" => $pagination, "data" => $reportList);
    }

    public function fetchRepidAndAidByTypeids($typeids)
    {
        $typeids = (is_array($typeids) ? implode(",", $typeids) : trim($typeids, ","));
        $reports = $this->fetchAll(array("select" => "repid, attachmentid", "condition" => "typeid IN($typeids)"));
        $return = array();

        if (!empty($reports)) {
            $return["repids"] = implode(",", ConvertUtil::getSubByKey($reports, "repid"));
            $attachmentidArr = ConvertUtil::getSubByKey($reports, "attachmentid");
            $return["aids"] = implode(",", array_filter($attachmentidArr));
        }

        return $return;
    }

    public function fetchAllAidByRepids($repids)
    {
        $ids = (is_array($repids) ? implode(",", $repids) : trim($repids, ","));
        $records = $this->fetchAll(array(
            "select"    => array("attachmentid"),
            "condition" => "repid IN($ids)"
        ));
        $result = array();

        foreach ($records as $record) {
            if (!empty($record["attachmentid"])) {
                $result[] = trim($record["attachmentid"], ",");
            }
        }

        return implode(",", $result);
    }

    public function fetchLastRepByUidAndTypeid($uid, $typeid, $time = TIMESTAMP)
    {
        $lastRep = $this->fetch(array(
            "select"    => "repid",
            "condition" => "uid = :uid AND typeid = :typeid AND addtime < :time",
            "params"    => array(":uid" => $uid, ":typeid" => $typeid, ":time" => $time),
            "order"     => "addtime DESC"
        ));
        return $lastRep;
    }

    public function fetchLastRepByRepid($repid, $uid, $typeid)
    {
        $lastRep = $this->fetch(array(
            "select"    => "repid",
            "condition" => "repid < :repid AND uid = :uid AND typeid = :typeid",
            "params"    => array(":repid" => $repid, ":uid" => $uid, ":typeid" => $typeid),
            "order"     => "repid DESC",
            "limit"     => 1
        ));
        return $lastRep;
    }

    public function countCommentByUid($uid)
    {
        $sql = "SELECT count(repid) as sum FROM {{report}} WHERE uid=$uid AND isreview=1";
        $record = $this->getDbConnection()->createCommand($sql)->queryAll();
        $sum = (empty($record[0]["sum"]) ? 0 : $record[0]["sum"]);
        return $sum;
    }

    public function addReaderuid($report, $uid)
    {
        $readeruid = $report["readeruid"];

        if ($uid == $report["uid"]) {
            return false;
        }

        $readerArr = explode(",", trim($readeruid, ","));

        if (in_array($uid, $readerArr)) {
            return false;
        } else {
            $readeruid = (empty($readeruid) ? $uid : $readeruid . "," . $uid);
            return $this->modify($report["repid"], array("readeruid" => $readeruid));
        }
    }

    public function addReaderuidByPk($report, $uid)
    {
        $readeruid = $report["readeruid"];

        if ($uid == $report["uid"]) {
            return false;
        }

        $readerArr = explode(",", trim($readeruid, ","));

        if (in_array($uid, $readerArr)) {
            return false;
        } else {
            $readeruid = (empty($readeruid) ? $uid : $readeruid . "," . $uid);
            return $this->modify($report["repid"], array("readeruid" => $readeruid));
        }
    }

    public function fetchPreAndNextRep($report)
    {
        $preRep = $this->fetch(array(
            "select"    => "repid, subject",
            "condition" => "repid < :repid AND uid = :uid",
            "params"    => array(":repid" => $report["repid"], ":uid" => $report["uid"]),
            "order"     => "repid DESC"
        ));
        $nextRep = $this->fetch(array(
            "select"    => "repid, subject",
            "condition" => "repid > :repid AND uid = :uid",
            "params"    => array(":repid" => $report["repid"], ":uid" => $report["uid"]),
            "order"     => "repid ASC"
        ));
        $preAndNextRep = array("preRep" => "", "nextRep" => "");

        if (!empty($preRep)) {
            $preAndNextRep["preRep"] = $preRep;
        }

        if (!empty($nextRep)) {
            $preAndNextRep["nextRep"] = $nextRep;
        }

        return $preAndNextRep;
    }

    public function fetchAllRepByUids($uids, $limit = 4)
    {
        $ids = (is_array($uids) ? implode(",", $uids) : trim($uids, ","));
        $reports = $this->fetchAll(array("select" => "repid, uid, subject, stamp", "condition" => "FIND_IN_SET(`uid`, '$ids')", "order" => "addtime DESC", "limit" => $limit));
        return $reports;
    }

    public function fetchUnreviewReps($joinCondition = "")
    {
        $condition = "isreview = 0";

        if (!empty($joinCondition)) {
            $condition .= " AND " . $joinCondition;
        }

        $unreviewReps = $this->fetchAll($condition);
        return $unreviewReps;
    }

    public function fetchUidByRepId($repId)
    {
        $report = $this->fetchByPk($repId);

        if (!empty($report)) {
            return $report["uid"];
        }
    }

    public function countReportTotalByUid($uid, $start, $end, $typeid)
    {
        $uid = (is_array($uid) ? implode(",", $uid) : $uid);
        return $this->getDbConnection()->createCommand()->select("count(repid)")->from($this->tableName())->where(sprintf("uid IN ('%s') AND begindate < %d AND enddate > %d AND typeid = %d", $uid, $end, $start, $typeid))->queryScalar();
    }

    public function countReviewTotalByUid($uid, $start, $end, $typeid)
    {
        return $this->getDbConnection()->createCommand()->select("count(repid)")->from($this->tableName())->where(sprintf("isreview = 1 AND uid = %d AND begindate < %d AND enddate > %d AND typeid = %d", $uid, $end, $start, $typeid))->queryScalar();
    }

    public function countUnReviewByUids($uid, $start, $end, $typeid)
    {
        is_array($uid) && ($uid = implode(",", $uid));
        return $this->getDbConnection()->createCommand()->select("count(repid)")->from($this->tableName())->where(sprintf("isreview = 0 AND uid IN ('%s') AND begindate < %d AND enddate > %d AND typeid = %d", $uid, $end, $start, $typeid))->queryScalar();
    }

    public function fetchAddTimeByRepId($repIds)
    {
        is_array($repIds) && ($repIds = implode(",", $repIds));
        $criteria = array("select" => "repid,addtime", "condition" => sprintf("FIND_IN_SET(repid,'%s')", $repIds));
        return $this->fetchAllSortByPk("repid", $criteria);
    }
}
