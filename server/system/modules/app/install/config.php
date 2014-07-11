<?php

defined("IN_MODULE_ACTION") || exit("Access Denied");
return array(
    "param"         => array(
        "name"         => "应用门户",
        "category"     => "个人办公",
        "description"  => "提供企业app应用功能",
        "author"       => "gzpjh @ IBOS Team Inc",
        "version"      => "1.0",
        "pushMovement" => 0,
        "indexShow"    => array("link" => "app/default/index")
    ),
    "configure"     => array(
        "modules"    => array("app"),
        "import"     => array("application.modules.app.controllers.*", "application.modules.app.model.*"),
        "components" => array(
            "messages" => array(
                "extensionPaths" => array("app" => "application.modules.app.language")
            )
        )
    ),
    "authorization" => array(
        "app" => array(
            "type"          => "node",
            "name"          => "管理应用",
            "group"         => "应用门户",
            "controllerMap" => array(
                "default" => array("index", "applist", "getapp", "add", "edit", "del")
            )
        )
    )
);
