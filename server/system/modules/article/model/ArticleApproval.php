<?php

class ArticleApproval extends ICModel
{
    public static function model($className = "ArticleApproval")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{article_approval}}";
    }

    public function recordStep($artId, $uid)
    {
        $artApproval = $this->fetchLastStep($artId);

        if (empty($artApproval)) {
            $step = 0;
        } else {
            $step = $artApproval["step"] + 1;
        }

        return $this->add(array("articleid" => $artId, "uid" => $uid, "step" => $step));
    }

    public function fetchLastStep($artId)
    {
        $record = $this->fetch(array("condition" => "articleid=$artId", "order" => "step DESC"));
        return $record;
    }

    public function deleteByArtIds($artIds)
    {
        $artIds = (is_array($artIds) ? implode(",", $artIds) : $artIds);
        return $this->deleteAll("FIND_IN_SET(articleid,'$artIds')");
    }

    public function fetchAllGroupByArtId()
    {
        $result = array();
        $records = $this->fetchAll("step > 0");

        if (!empty($records)) {
            foreach ($records as $record) {
                $artId = $record["articleid"];
                $result[$artId][] = $record;
            }
        }

        return $result;
    }
}
