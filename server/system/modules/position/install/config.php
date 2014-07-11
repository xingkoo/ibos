<?php

defined("IN_MODULE_ACTION") || exit("Access Denied");
return array(
    "param"     => array("name" => "岗位模块", "description" => "提供IBOS2岗位管理所需功能", "author" => "banyanCheung @ IBOS Team Inc", "version" => "1.0"),
    "configure" => array(
        "modules"    => array("position"),
        "import"     => array("application.modules.position.components.*", "application.modules.position.model.*", "application.modules.position.utils.*"),
        "components" => array(
            "messages" => array(
                "extensionPaths" => array("position" => "application.modules.position.language")
            )
        )
    )
);
