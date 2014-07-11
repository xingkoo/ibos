<?php

class ReportStats extends ICModel
{
    public static function model($className = "ReportStats")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{report_statistics}}";
    }

    public function countScoreByUid($uid, $start, $end, $typeid)
    {
        $score = $this->getDbConnection()->createCommand()->select("SUM(integration)")->from($this->tableName())->where(sprintf("uid = %d AND scoretime BETWEEN %d AND %d AND typeid = %d", $uid, $start, $end, $typeid))->queryScalar();
        return intval($score);
    }

    public function fetchAllStampByUid($uid, $start, $end, $typeid)
    {
        $criteria = array("select" => "stamp", "condition" => sprintf("uid = %d AND scoretime BETWEEN %d AND %d AND typeid = %d", $uid, $start, $end, $typeid));
        $datas = $this->fetchAll($criteria);
        return ConvertUtil::getSubByKey($datas, "stamp");
    }

    public function fetchAllStatisticsByUid($uid, $start, $end, $typeid)
    {
        $criteria = array("condition" => sprintf("uid = %d AND scoretime BETWEEN %d AND %d AND typeid = %d", $uid, $start, $end, $typeid));
        return $this->fetchAllSortByPk("repid", $criteria);
    }

    public function scoreReport($repId, $uid, $stamp)
    {
        $record = $this->fetchByAttributes(array("repid" => $repId));
        $attributes = array("repid" => $repId, "uid" => $uid, "stamp" => $stamp, "integration" => ReportUtil::getScoreByStamp($stamp), "scoretime" => TIMESTAMP);

        if (empty($record)) {
            $this->add($attributes);
        } else {
            $this->modify($record["id"], $attributes);
        }
    }
}
