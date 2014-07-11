<?php

class NodeRelated extends ICModel
{
    public static function model($className = "NodeRelated")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{node_related}}";
    }

    public function fetchDataValByIdentifier($id, $positionId)
    {
        list($module, $key, $node) = explode("/", $id);
        $criteria = array(
            "select"    => "val",
            "condition" => "`module` = :module AND `key`= :key AND `node` = :node AND `positionid` = :positionid",
            "params"    => array(":module" => $module, ":key" => $key, ":node" => $node, ":positionid" => $positionId)
            );
        $record = $this->fetch($criteria);
        return $record ? $record["val"] : "";
    }

    public function fetchAllByPosId($id)
    {
        return $this->fetchAllSortByPk("id", "`positionid` = :id", array(":id" => $id));
    }

    public function deleteAllByPositionId($id)
    {
        return $this->deleteAll("positionid = :id", array(":id" => $id));
    }

    public function addRelated($val = "", $positionId = 0, $node = array())
    {
        unset($node["id"]);
        $relatedData = array("val" => $val, "positionid" => $positionId);
        $related = array_merge($node, $relatedData);
        return $this->add($related, true);
    }
}
