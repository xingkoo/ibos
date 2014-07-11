<?php

class UserGroup extends ICModel
{
    public static function model($className = "UserGroup")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{user_group}}";
    }

    public function fetchNextLevel($creditsLower)
    {
        $criteria = array(
            "condition" => "creditshigher = :lower",
            "params"    => array(":lower" => $creditsLower),
            "limit"     => 1
        );
        return $this->fetch($criteria);
    }

    public function fetchByCredits($credits)
    {
        if (is_array($credits)) {
            $creditsf = intval($credits[0]);
            $creditse = intval($credits[1]);
        } else {
            $creditsf = $creditse = intval($credits);
        }

        $criteria = array(
            "select"    => "title,gid",
            "condition" => ":creditsf>=creditshigher AND :creditse<creditslower",
            "params"    => array(":creditsf" => $creditsf, ":creditse" => $creditse),
            "limit"     => 1
        );
        return $this->fetch($criteria);
    }

    public function deleteById($ids)
    {
        $id = explode(",", trim($ids, ","));
        return parent::deleteByPk($id, "`system` = '0'");
    }
}
