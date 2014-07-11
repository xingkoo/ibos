<?php

defined("IN_MODULE_ACTION") || exit("Access Denied");
return array(
    "param"     => array("name" => "后台管理", "description" => "提供IBOS2后台管理所需功能", "author" => "banyanCheung @ IBOS Team Inc", "version" => "1.0"),
    "configure" => array(
        "modules"    => array("dashboard"),
        "import"     => array("application.modules.dashboard.components.*", "application.modules.dashboard.controllers.*", "application.modules.dashboard.behaviors.*", "application.modules.dashboard.model.*", "application.modules.dashboard.utils.*"),
        "components" => array(
            "messages" => array(
                "extensionPaths" => array("dashboard" => "application.modules.dashboard.language")
            )
        )
    )
);