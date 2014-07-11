<?php

defined("IN_MODULE_ACTION") || exit("Access Denied");
return array(
    "param"         => array("name" => "组织架构", "category" => "人力资源", "description" => "整合部门，岗位，用户模块功能为一体，方便管理", "author" => "banyanCheung @ IBOS Team Inc", "version" => "1.0"),
    "configure"     => array(
        "modules"    => array("organization"),
        "import"     => array("application.modules.organization.controllers.*", "application.modules.organization.core.*", "application.modules.organization.model.*", "application.modules.organization.utils.*"),
        "components" => array(
            "messages" => array(
                "extensionPaths" => array("organization" => "application.modules.organization.language")
            )
        )
    ),
    "dependencies"  => array("user", "department", "position"),
    "authorization" => array(
        "user"       => array(
            "type"  => "data",
            "name"  => "用户管理",
            "group" => "组织架构",
            "node"  => array(
                "view"    => array(
                    "name"          => "查看",
                    "controllerMap" => array(
                        "user" => array("index")
                    )
                ),
                "manager" => array(
                    "name"          => "管理",
                    "controllerMap" => array(
                        "user" => array("add", "edit", "del", "isRegistered")
                    )
                )
            )
        ),
        "position"   => array(
            "type"          => "node",
            "name"          => "岗位管理",
            "group"         => "组织架构",
            "controllerMap" => array(
                "position" => array("index", "add", "edit", "del"),
                "category" => array("index")
            )
        ),
        "department" => array(
            "type"          => "node",
            "name"          => "部门管理",
            "group"         => "组织架构",
            "controllerMap" => array(
                "department" => array("index", "add", "edit", "del")
            )
        )
    )
);
