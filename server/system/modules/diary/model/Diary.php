<?php

class Diary extends ICModel
{
    public static function model($className = "Diary")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{diary}}";
    }

    public function getSourceInfo($id)
    {
        $info = $this->fetchByPk($id);
        return $info;
    }

    public function fetchAllByPage($condition, $pageSize = 0)
    {
        $conditionArray = array("condition" => $condition, "order" => "diarytime DESC");
        $criteria = new CDbCriteria();

        foreach ($conditionArray as $key => $value) {
            $criteria->$key = $value;
        }

        $count = $this->count($criteria);
        $pagination = new CPagination($count);
        $everyPage = (empty($pageSize) ? Yii::app()->params["basePerPage"] : $pageSize);
        $pagination->setPageSize(intval($everyPage));
        $pagination->applyLimit($criteria);
        $diaryList = $this->fetchAll($criteria);
        return array("pagination" => $pagination, "data" => $diaryList);
    }

    public function fetchAllByPage2($condition, $pageSize = 0)
    {
        $conditionArray = array("condition" => $condition, "order" => "diarytime DESC");
        $criteria = new CDbCriteria();

        foreach ($conditionArray as $key => $value) {
            $criteria->$key = $value;
        }

        $count = $this->count($criteria);
        $pagination = new CPagination($count);
        $everyPage = (empty($pageSize) ? Yii::app()->params["basePerPage"] : $pageSize);
        $pagination->setPageSize(intval($everyPage));
        $pagination->applyLimit($criteria);
        $diaryList = $this->fetchAll($criteria);
        $params = array();

        for ($i = 0; $i < count($diaryList); $i++) {
            $data = $this->fetchDiaryRecord($diaryList[$i]);
            $params[$i]["diary"] = $diaryList[$i];
            $params[$i]["originalPlanList"] = $data["originalPlanList"];
            $params[$i]["outsidePlanList"] = $data["outsidePlanList"];
            $params[$i]["tomorrowPlanList"] = $data["tomorrowPlanList"];
        }

        return array("pagination" => $pagination, "data" => $params);
    }

    public function checkDiaryisAdd($diarytime, $uid)
    {
        return $this->count("diarytime=:diarytime AND uid=:uid", array(":diarytime" => $diarytime, ":uid" => $uid));
    }

    public function fetchPreDiary($diarytime, $uid)
    {
        $preDiary = $this->fetch(array(
        "condition" => "uid = :uid AND diarytime < :diarytime ORDER BY diarytime DESC",
        "params"    => array(":uid" => $uid, "diarytime" => $diarytime)
        ));
        return $preDiary;
    }

    public function fetchPrevAndNextPKByPK($diaryid)
    {
        $diary = $this->fetchByPk($diaryid);
        $uid = $diary["uid"];
        $nextPK = $prevPK = 0;
        $sql = "SELECT diaryid FROM {{diary}} WHERE uid=$uid AND diaryid>$diaryid ORDER BY diaryid ASC LIMIT 1";
        $nextRecord = $this->getDbConnection()->createCommand($sql)->queryAll();

        if (!empty($nextRecord)) {
            $nextPK = $nextRecord[0]["diaryid"];
        }

        $sql2 = "SELECT diaryid FROM {{diary}} WHERE uid=$uid AND diaryid<$diaryid ORDER BY diaryid DESC LIMIT 1";
        $prevRecord = $this->getDbConnection()->createCommand($sql2)->queryAll();

        if (!empty($prevRecord)) {
            $prevPK = $prevRecord[0]["diaryid"];
        }

        return array("prevPK" => $prevPK, "nextPK" => $nextPK);
    }

    public function fetchDiaryRecord($diary)
    {
        $data = array();
        $todayRecordList = DiaryRecord::model()->fetchAll(array(
            "condition" => "plantime=:plantime AND uid=:uid",
            "params"    => array(":plantime" => $diary["diarytime"], ":uid" => $diary["uid"]),
            "order"     => "recordid ASC"
        ));
        $data["originalPlanList"] = array();
        $data["outsidePlanList"] = array();

        foreach ($todayRecordList as $diaryRecord) {
            if ($diaryRecord["planflag"] == 1) {
                $data["originalPlanList"][] = $diaryRecord;
            } else {
                $data["outsidePlanList"][] = $diaryRecord;
            }
        }

        $recordList = DiaryRecord::model()->fetchAll(array(
            "condition" => "diaryid=:diaryid AND uid=:uid AND planflag=:planflag",
            "params"    => array(":diaryid" => $diary["diaryid"], ":uid" => $diary["uid"], ":planflag" => 1),
            "order"     => "recordid ASC"
        ));
        $data["tomorrowPlanList"] = $recordList;
        return $data;
    }

    public function addReaderuidByPk($diary, $uid)
    {
        $readeruid = $diary["readeruid"];

        if ($uid == $diary["uid"]) {
            return false;
        }

        $readerArr = explode(",", trim($readeruid, ","));

        if (in_array($uid, $readerArr)) {
            return false;
        } else {
            $readeruid = (empty($readeruid) ? $uid : $readeruid . "," . $uid);
            return $this->modify($diary["diaryid"], array("readeruid" => $readeruid));
        }
    }

    public function fetchReaderAndDepartmentByPk($pk)
    {
        $data = array();
        $record = $this->fetch(array(
            "select"    => array("readeruid"),
            "condition" => "diaryid=:diaryid",
            "params"    => array(":diaryid" => $pk)
        ));
        $readeruid = $record["readeruid"];

        if (empty($readeruid)) {
            return null;
        } else {
            $readerArr = explode(",", $readeruid);

            for ($i = 0; $i < count($readerArr); $i++) {
                $deptName = Department::model()->fetchDeptNameByUid($readerArr[$i]);
                $data[$i]["departmentName"] = $deptName;
                $data[$i]["realname"] = User::model()->fetchRealnameByUid($readerArr[$i]);
            }

            $data = DiaryUtil::processReaderList($data);
        }

        return $data;
    }

    public function fetchAllByShareCondition($uid, $number)
    {
        $sql = "SELECT * FROM {{diary}} WHERE FIND_IN_SET('$uid',shareuid) AND uid NOT IN($uid) ORDER BY diarytime DESC LIMIT $number";
        $records = $this->getDbConnection()->createCommand($sql)->queryAll();
        return $records;
    }

    public function updateAttentionByPk($diaryid, $type, $uid)
    {
        $record = $this->fetch(array(
            "select"    => array("attention"),
            "condition" => "diaryid=:diaryid",
            "params"    => array(":diaryid" => $diaryid)
        ));
        $attention = $record["attention"];

        if ($type == "asterisk") {
            if (empty($attention)) {
                $attention = $uid;
            } else {
                $attention = "," . $uid;
            }
        } elseif ($type == "unasterisk") {
            if (strpos($attention, $uid) !== false) {
                $attention = str_replace($uid, "", $attention);

                if (strpos($attention, ",,") !== true) {
                    $attention = str_replace(",,", ",", $attention);
                }
            }
        }

        return $this->modify($diaryid, array("attention" => $attention));
    }

    public function fetchAllByUidAndDiarytime($ym, $uid)
    {
        $year = substr($ym, 0, 4) + 0;
        $month = substr($ym, 4) + 0;
        $firstDay = date("Y-m-01", strtotime($year . "-" . $month));
        $lastDay = date("Y-m-d", strtotime("$firstDay +1 month -1 day"));
        $startTime = strtotime($firstDay);
        $endTime = strtotime($lastDay);
        $records = Diary::model()->fetchAll(array(
            "select"    => array("diaryid", "diarytime", "commentcount"),
            "condition" => "diarytime>=$startTime AND diarytime<=$endTime AND uid=:uid",
            "params"    => array(":uid" => $uid)
        ));
        $result = array();

        foreach ($records as $diary) {
            $diarytime = $diary["diarytime"];
            $day = date("d", $diarytime) + 0;
            $result[$day]["isLog"] = true;
            $result[$day]["isComment"] = (0 < $diary["commentcount"] ? true : false);
            $result[$day]["diaryid"] = $diary["diaryid"];
        }

        list(, , $startDay) = explode("-", $firstDay);
        list(, , $endDay) = explode("-", $lastDay);

        for ($i = $startDay + 0; $i <= $endDay; $i++) {
            if (!array_key_exists($i, $result)) {
                $result[$i]["isLog"] = false;
                $result[$i]["isComment"] = false;
                $result[$i]["diaryid"] = "";
            }
        }

        return $result;
    }

    public function fetchAllDiaryidByUid($uid)
    {
        $records = Diary::model()->fetchAll(array(
            "select"    => array("diaryid"),
            "condition" => "uid=:uid",
            "params"    => array(":uid" => $uid)
        ));
        $diaryStr = "";

        foreach ($records as $diary) {
            $diaryStr .= $diary["diaryid"] . ",";
        }

        if (!empty($diaryStr)) {
            $diaryStr = substr($diaryStr, 0, -1);
        }

        return $diaryStr;
    }

    public function fetchAllAidByPks($diaryIds)
    {
        $ids = (is_array($diaryIds) ? implode(",", $diaryIds) : trim($diaryIds, ","));
        $records = $this->fetchAll(array(
            "select"    => array("attachmentid"),
            "condition" => "diaryid IN($ids)"
        ));
        $result = array();

        foreach ($records as $record) {
            if (!empty($record["attachmentid"])) {
                $result[] = trim($record["attachmentid"], ",");
            }
        }

        return implode(",", $result);
    }

    public function fetchUidByDiaryId($diaryId)
    {
        $diary = $this->fetchByPk($diaryId);

        if (!empty($diary)) {
            return $diary["uid"];
        }
    }

    public function countCommentByUid($uid)
    {
        $curUid = Ibos::app()->user->uid;
        $sql = "SELECT count(diaryid) as sum FROM {{diary}} WHERE uid=$uid AND isreview=1 and FIND_IN_SET('$curUid', `shareuid`)";
        $record = $this->getDbConnection()->createCommand($sql)->queryAll();
        $sum = (empty($record[0]["sum"]) ? 0 : $record[0]["sum"]);
        return $sum;
    }

    public function countCommentByReview($uid)
    {
        $sql = "SELECT count(diaryid) as sum FROM {{diary}} WHERE uid=$uid AND isreview=1";
        $record = $this->getDbConnection()->createCommand($sql)->queryAll();
        $sum = (empty($record[0]["sum"]) ? 0 : $record[0]["sum"]);
        return $sum;
    }

    public function countDiaryTotalByUid($uid, $start, $end)
    {
        $uid = (is_array($uid) ? implode(",", $uid) : $uid);
        return $this->getDbConnection()->createCommand()->select("count(diaryid)")->from($this->tableName())->where(sprintf("uid IN ('%s') AND diarytime BETWEEN %d AND %d", $uid, $start, $end))->queryScalar();
    }

    public function countReviewTotalByUid($uid, $start, $end)
    {
        return $this->getDbConnection()->createCommand()->select("count(diaryid)")->from($this->tableName())->where(sprintf("isreview = 1 AND uid = %d AND diarytime BETWEEN %d AND %d", $uid, $start, $end))->queryScalar();
    }

    public function countUnReviewByUids($uid, $start, $end)
    {
        is_array($uid) && ($uid = implode(",", $uid));
        return $this->getDbConnection()->createCommand()->select("count(diaryid)")->from($this->tableName())->where(sprintf("isreview = 0 AND uid IN ('%s') AND diarytime BETWEEN %d AND %d", $uid, $start, $end))->queryScalar();
    }

    public function countOnTimeRateByUid($uid, $start, $end)
    {
        $criteria = array("select" => "diarytime,addtime", "condition" => sprintf("uid = %d AND addtime BETWEEN %d AND %d", $uid, $start, $end));
        $datas = $this->fetchAll($criteria);
        $diaryNums = count($datas);

        if (0 < $diaryNums) {
            $notOnTime = 0;

            foreach ($datas as $diary) {
                if (86400 < ($diary["addtime"] - $diary["diarytime"])) {
                    $notOnTime++;
                }
            }

            if (0 < $notOnTime) {
                return round((1 - ($notOnTime / $diaryNums)) * 100);
            } else {
                return 100;
            }
        }

        return 0;
    }

    public function fetchAddTimeByUid($uid, $start, $end)
    {
        is_array($uid) && ($uid = implode(",", $uid));
        $criteria = array("select" => "diarytime,addtime,uid", "condition" => sprintf("FIND_IN_SET(uid,'%s') AND diarytime BETWEEN %d AND %d", $uid, $start, $end));
        return $this->fetchAll($criteria);
    }

    public function fetchAddTimeByDiaryId($diaryIds)
    {
        is_array($diaryIds) && ($diaryIds = implode(",", $diaryIds));
        $criteria = array("select" => "diaryid,addtime", "condition" => sprintf("FIND_IN_SET(diaryid,'%s')", $diaryIds));
        return $this->fetchAllSortByPk("diaryid", $criteria);
    }
}
