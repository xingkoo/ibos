<?php

defined("IN_MODULE_ACTION") || exit("Access Denied");
return array(
    "param"     => array("name" => "IBOS移动平台", "description" => "提供IBOS2移动平台数据请求和处理相关功能", "author" => "Aeolus @ IBOS Team Inc", "version" => "1.0"),
    "configure" => array(
        "modules"    => array("mobile"),
        "import"     => array("application.modules.mobile.components.*", "application.modules.mobile.controllers.*", "application.modules.mobile.utils.*"),
        "components" => array(
            "messages" => array(
                "extensionPaths" => array("mobile" => "application.modules.mobile.language")
            )
        )
    )
);
