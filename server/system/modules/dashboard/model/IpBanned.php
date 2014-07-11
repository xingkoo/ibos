<?php

class IpBanned extends ICModel
{
    public static function model($className = "IpBanned")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{ipbanned}}";
    }

    public function fetchAllOrderDateline()
    {
        return parent::fetchAll(array("order" => "dateline DESC"));
    }

    public function updateExpirationById($id, $expiration, $admin)
    {
        return $this->updateByPk($id, array("expiration" => $expiration), "admin = '$admin'");
    }

    public function DeleteByExpiration($expiration)
    {
        return $this->deleteAll("expiration < :exp", array(":exp" => $expiration));
    }
}
