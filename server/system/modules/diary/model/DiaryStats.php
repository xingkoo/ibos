<?php

class DiaryStats extends ICModel
{
    public static function model($className = "DiaryStats")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{diary_statistics}}";
    }

    public function countScoreByUid($uid, $start, $end)
    {
        $criteria = array("select" => "diaryid", "condition" => sprintf("uid = %d AND addtime BETWEEN %d AND %d", $uid, $start, $end));
        $res = Diary::model()->fetchAll($criteria);
        $diaids = ConvertUtil::getSubByKey($res, "diaryid");
        $score = $this->getDbConnection()->createCommand()->select("SUM(integration)")->from($this->tableName())->where(sprintf("FIND_IN_SET(diaryid,'%s')", implode(",", $diaids)))->queryScalar();
        return intval($score);
    }

    public function fetchAllStampByUid($uid, $start, $end)
    {
        $criteria = array("select" => "stamp", "condition" => sprintf("uid = %d AND scoretime BETWEEN %d AND %d", $uid, $start, $end));
        $datas = $this->fetchAll($criteria);
        return ConvertUtil::getSubByKey($datas, "stamp");
    }

    public function fetchAllStatisticsByUid($uid, $start, $end)
    {
        $criteria = array("condition" => sprintf("uid = %d AND scoretime BETWEEN %d AND %d", $uid, $start, $end));
        return $this->fetchAllSortByPk("diaryid", $criteria);
    }

    public function scoreDiary($diaryId, $uid, $stamp)
    {
        $record = $this->fetchByAttributes(array("diaryid" => $diaryId));
        $attributes = array("diaryid" => $diaryId, "uid" => $uid, "stamp" => $stamp, "integration" => DiaryUtil::getScoreByStamp($stamp), "scoretime" => TIMESTAMP);

        if (empty($record)) {
            $this->add($attributes);
        } else {
            $this->modify($record["id"], $attributes);
        }
    }
}
