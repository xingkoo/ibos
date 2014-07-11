<?php

class AuthItemChild extends ICModel
{
    public static function model($className = "AuthItemChild")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{auth_item_child}}";
    }

    public function deleteByParent($parent)
    {
        return $this->deleteAll("`parent` = :parent", array(":parent" => $parent));
    }
}
