<?php

class UserProfile extends ICModel
{
    public static function model($className = "UserProfile")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{user_profile}}";
    }

    public function fetchByUid($uid)
    {
        static $users = array();

        if (!isset($users[$uid])) {
            $user = $this->fetchByPk($uid);
            $users[$uid] = $user;
        }

        return $users[$uid];
    }
}
