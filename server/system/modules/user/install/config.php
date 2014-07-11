<?php

defined("IN_MODULE_ACTION") || exit("Access Denied");
return array(
    "param"        => array("name" => "用户模块", "description" => "核心模块。提供用户管理，登录验证等功能", "author" => "banyanCheung @ IBOS Team Inc", "version" => "1.0"),
    "configure"    => array(
        "modules"    => array("user"),
        "import"     => array("application.modules.user.components.*", "application.modules.user.controllers.*", "application.modules.user.model.*", "application.modules.user.utils.*"),
        "components" => array(
            "user"     => array(
                "allowAutoLogin" => 1,
                "class"          => "ICUser",
                "loginUrl"       => array("user/default/login")
            ),
            "messages" => array(
                "extensionPaths" => array("user" => "application.modules.user.language")
            )
        )
    ),
    "dependencies" => array("main")
);
