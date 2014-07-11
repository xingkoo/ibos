<?php

class AuthUtil
{
    public static function loadAuthItem()
    {
        return Ibos::app()->setting->get("cache/authitem");
    }

    public static function getParams($route)
    {
        $positionid = Ibos::app()->user->positionid;
        $dataItems = Node::model()->fetchAllDataNode();

        if (isset($dataItems[$route])) {
            $identifier = $dataItems[$route];
            $param["purvId"] = NodeRelated::model()->fetchDataValByIdentifier($identifier, $positionid);
        } else {
            $param = array();
        }

        return $param;
    }

    public static function assignPosition($uid, $positionIds)
    {
        $auth = Ibos::app()->authManager;

        foreach ($positionIds as $positionId) {
            if ($positionId) {
                $auth->assign($positionId, $uid, "", "");
            }
        }
    }

    public static function updateAuthorization($authItem, $moduleName, $category)
    {
        foreach ($authItem as $key => $node) {
            $data["type"] = $node["type"];
            $data["category"] = $category;
            $data["module"] = $moduleName;
            $data["key"] = $key;
            $data["name"] = $node["name"];
            $data["node"] = "";

            if (isset($node["group"])) {
                $data["group"] = $node["group"];
            } else {
                $data["group"] = "";
            }

            $condition = "`module` = '$moduleName' AND `key` = '$key'";
            Node::model()->deleteAll($condition);

            if ($node["type"] === "data") {
                Node::model()->add($data);

                foreach ($node["node"] as $nKey => $subNode) {
                    $dataCondition = $condition . " AND `node` = '$nKey'";
                    Node::model()->deleteAll($dataCondition);
                    $data["name"] = $subNode["name"];
                    $routes = self::wrapControllerMap($moduleName, $subNode["controllerMap"]);
                    $data["routes"] = $routes;
                    $data["node"] = $nKey;
                    self::updateAuthItem(explode(",", $routes), true);
                    Node::model()->add($data);
                }
            } else {
                $data["routes"] = self::wrapControllerMap($moduleName, $node["controllerMap"]);
                self::updateAuthItem(explode(",", $data["routes"]), false);
                Node::model()->add($data);
            }
        }

        CacheUtil::update("authItem");
    }

    public static function addRoleChildItem($role, $currentNode, $routes = array())
    {
        if (!empty($routes)) {
            foreach ($routes as $route) {
                $role->addChild($route, $currentNode["name"], "", $currentNode["node"]);
            }
        }
    }

    public static function updateAuthItem($routes, $isData = false)
    {
        if (!empty($routes)) {
            $auth = Ibos::app()->authManager;

            foreach ($routes as $route) {
                $bizRule = ($isData ? "return UserUtil::checkDataPurv(\$purvId);" : "");
                $auth->removeAuthItem($route);
                $auth->createOperation($route, "", $bizRule, "");
            }
        }
    }

    private static function wrapControllerMap($module, $map)
    {
        $routes = array();

        foreach ($map as $controller => $actions) {
            foreach ($actions as $action) {
                $routes[] = sprintf("%s/%s/%s", $module, $controller, $action);
            }
        }

        return implode(",", $routes);
    }
}
