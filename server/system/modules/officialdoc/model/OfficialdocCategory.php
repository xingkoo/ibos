<?php

class OfficialdocCategory extends ICModel
{
    /**
     * 是否开启缓存
     * @var boolean 
     * @access protected
     */
    protected $allowCache = false;
    /**
     * 缓存生命周期 单位秒
     * @var integer 
     * @access protected
     */
    protected $cacheLife = 0;

    public static function model($className = "OfficialdocCategory")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{doc_category}}";
    }

    public function fetchAllSubCatidByPid($pid)
    {
        $result = array();
        $datas = $this->fetchAll(array("select" => "catid", "condition" => "pid=$pid", "order" => "sort ASC"));

        foreach ($datas as $data) {
            $result[] = $data["catid"];
        }

        return $result;
    }

    public function fetchAllCatidAndPid()
    {
        $result = array();
        $datas = $this->fetchAll(array("order" => "pid"));

        foreach ($datas as $data) {
            $pid = $data["pid"];
            $array = array();
            $array[$pid] = array("catid" => $data["catid"], "pid" => $pid);
            array_push($result, $array);
        }

        return $result;
    }

    public function fetchCateNameByCatid($catid)
    {
        $data = $this->fetch(array("select" => "name", "condition" => "catid='$catid'"));
        return !empty($data) ? $data["name"] : "";
    }

    public function fetchSubCatidByCatid($catid = 0)
    {
        $categoryAllDatas = self::fetchAllCatidAndPid();
        $str = self::fetchCatidByPid($categoryAllDatas, $catid);
        return $str;
    }

    public function fetchCatidByPid($pid, $flag = false)
    {
        $categoryAllData = self::fetchAllCatidAndPid();
        $list = array();

        foreach ($categoryAllData as $key => $value) {
            foreach ($value as $cate) {
                $list[$key]["catid"] = $cate["catid"];
                $list[$key]["pid"] = $cate["pid"];
            }
        }

        $catids = "";
        $result = $this->fetchCategoryList($list, $pid, 0);

        foreach ($result as $value) {
            $catids .= $value["catid"] . ",";
        }

        if ($flag) {
            return trim($pid . "," . $catids, ",");
        } else {
            return trim($catids);
        }
    }

    private function fetchCategoryList($list, $pid, $level)
    {
        static $result = array();

        foreach ($list as $category) {
            if ($category["pid"] == $pid) {
                $category["level"] = $level;
                $result[] = $category;
                array_merge($result, $this->fetchCategoryList($list, $category["catid"], $level + 1));
            }
        }

        return $result;
    }

    public function checkHaveChild($catid)
    {
        $count = $this->count("pid=:pid", array(":pid" => $catid));
        return 0 < $count ? true : false;
    }

    public function afterDelete()
    {
        $category = Yii::app()->setting->get("officialdoccategory");
        $pk = $this->getPrimaryKey();
        unset($category[$pk]);
        Syscache::model()->modify("officialdoccategory", $category);
        CacheUtil::load("officialdoccategory", true);
        parent::afterDelete();
    }

    public function afterSave()
    {
        $pk = $this->getPrimaryKey();

        if ($pk) {
            $category = Yii::app()->setting->get("officialdoccategory");
            $attr = $this->getAttributes();
            $category[$pk] = $attr;
            Syscache::model()->modify("officialdoccategory", $category);
            CacheUtil::load("officialdoccategory", true);
        }

        parent::afterSave();
    }

    public function checkIsAllowPublish($catid, $uid)
    {
        $allowPublish = 0;

        if (empty($catid)) {
            $catid = 1;
        }

        $category = $this->fetchByPk($catid);

        if (empty($category)) {
            return $allowPublish;
        } elseif ($category["aid"] == 0) {
            return 1;
        }

        $approval = Approval::model()->fetchByPk($category["aid"]);
        if (!empty($catid) && !empty($category)) {
            if ($category["aid"] == 0) {
                $allowPublish = 1;
            } else {
                if (!empty($approval) && in_array($uid, explode(",", $approval["free"]))) {
                    $allowPublish = 1;
                }
            }
        }

        return $allowPublish;
    }

    public function fetchAids()
    {
        $categorys = $this->fetchAll();
        $aids = ConvertUtil::getSubByKey($categorys, "aid");
        $aids = array_unique($aids);
        $aids = array_filter($aids);
        return $aids;
    }

    public function fetchAidByCatid($catid)
    {
        $aid = 0;

        if (!empty($catid)) {
            $record = $this->fetchByPk($catid);
            $aid = $record["aid"];
        }

        return $aid;
    }

    public function checkIsApproval($catid, $uid)
    {
        $aid = $this->fetchAidByCatid($catid);
        $approvalUids = Approval::model()->fetchApprovalUidsByIds($aid);
        $res = in_array($uid, $approvalUids);
        return $res;
    }

    public function fetchAllApprovalCatidByUid($uid)
    {
        $res = array();
        $categorys = $this->fetchAll();

        foreach ($categorys as $cate) {
            if ($this->checkIsApproval($cate["catid"], $uid)) {
                $res[] = $cate["catid"];
            }
        }

        return $res;
    }
}
