<?php

class Node extends ICModel
{
    public static function model($className = "Node")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{node}}";
    }

    public function fetchAllEmptyNode()
    {
        return $this->fetchAll("`node` = ''");
    }

    public function fetchAllDataNode()
    {
        static $dataNodes = array();

        if (empty($dataNodes)) {
            $record = $this->fetchAll("`type` = 'data' AND `node` != ''");

            foreach ($record as $node) {
                $routes = explode(",", $node["routes"]);

                foreach ($routes as $route) {
                    $dataNodes[strtolower($route)] = strtolower(sprintf("%s/%s/%s", $node["module"], $node["key"], $node["node"]));
                }
            }
        }

        return $dataNodes;
    }

    public function fetchAllNotEmptyNodeByModuleKey($module, $key)
    {
        $params = array(":module" => $module, ":key" => $key);
        return $this->fetchAll("`node` != '' AND `module` = :module AND `key` = :key", $params);
    }
}
