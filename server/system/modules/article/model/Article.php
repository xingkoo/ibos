<?php

class Article extends ICModel
{
    public static function model($className = "Article")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{article}}";
    }

    public function fetchAllAndPage($conditions = "", $pageSize = null)
    {
        $conditionArray = array("condition" => $conditions, "order" => "istop DESC,toptime ASC,addtime DESC");
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

    public function fetchReadersByArticleid($articleid)
    {
        $record = $this->fetch(array("select" => "readers", "condition" => "articleid = '$articleid'"));
        return $record;
    }

    public function fetchFieldValueByArticleid($field, $articleid)
    {
        $record = $this->fetch(array("select" => $field, "condition" => "articleid=$articleid"));
        return !empty($record) ? $record[$field] : "";
    }

    public function fetchAllFieldValueByArticleids($field, $articleids)
    {
        $returnArray = array();
        $rows = $this->fetchAll(array("select" => $field, "condition" => "FIND_IN_SET(articleid,'$articleids')"));

        if (!empty($rows)) {
            foreach ($rows as $row) {
                $returnArray[] = $row[$field];
            }
        }

        return $returnArray;
    }

    public function fetchByArticleidAndStatus($articleid, $status = 1)
    {
        $record = $this->fetch("articleid='$articleid' AND status='$status'");
        return $record;
    }

    public function cancelTop()
    {
        $result = $this->updateAll(array("istop" => 0, "toptime" => 0, "topendtime" => 0), "istop = 1 AND topendtime<" . TIMESTAMP);
        return $result;
    }

    public function updateTopStatus($ids, $isTop, $topTime, $topEndTime)
    {
        $condition = array("istop" => $isTop, "toptime" => $topTime, "topendtime" => $topEndTime);
        return Article::model()->updateAll($condition, "articleid IN ($ids)");
    }

    public function updateIsOverHighLight()
    {
        $result = $this->updateAll(array("ishighlight" => 0, "highlightstyle" => "", "highlightstackdate" => ""), "ishighlight = 1 AND highlightendtime<" . TIMESTAMP);
        return $result;
    }

    public function updateHighlightStatus($ids, $ishighlight, $highlightstyle, $highlightendtime)
    {
        $condition = array("ishighlight" => $ishighlight, "highlightstyle" => $highlightstyle, "highlightendtime" => $highlightendtime);
        return Article::model()->updateAll($condition, "articleid IN ($ids)");
    }

    public function deleteAllByArticleIds($ids)
    {
        return $this->deleteAll("articleid IN ($ids)");
    }

    public function updateAllStatusAndApproverByPks($ids, $approver, $status = 1)
    {
        return $this->updateAll(array("status" => $status, "approver" => $approver, "uptime" => TIMESTAMP), "articleid IN ($ids)");
    }

    public function updateAllCatidByArticleIds($ids, $catid)
    {
        return $this->updateAll(array("catid" => $catid), "articleid IN ($ids)");
    }

    public function updateClickCount($id, $clickCount = 0)
    {
        if (empty($clickCount)) {
            $record = parent::fetchByPk($id);
            $clickCount = $record["clickcount"];
        }

        return parent::modify($id, array("clickcount" => $clickCount + 1));
    }

    public function getSourceInfo($id)
    {
        $info = $this->fetchByPk($id);
        return $info;
    }

    public function fetchUnApprovalArtIds($catid, $uid)
    {
        $backArtIds = ArticleBack::model()->fetchAllBackArtId();
        $backArtIdStr = implode(",", $backArtIds);
        $backCondition = (empty($backArtIdStr) ? "" : "AND `articleid` NOT IN($backArtIdStr)");

        if (empty($catid)) {
            $catids = ArticleCategory::model()->fetchAllApprovalCatidByUid($uid);
            $catidStr = implode(",", $catids);
            $condition = "((FIND_IN_SET( `catid`, '$catidStr') $backCondition ) OR `author` = $uid)";
        } else {
            $isApproval = ArticleCategory::model()->checkIsApproval($catid, $uid);
            $condition = ($isApproval ? "(`catid` = $catid $backCondition )" : " (`catid` = $catid AND `author` = $uid)");
        }

        $record = $this->fetchAll(array(
            "select"    => array("articleid"),
            "condition" => "`status` = 2 AND " . $condition
        ));
        $artIds = ConvertUtil::getSubByKey($record, "articleid");
        return $artIds;
    }
}
