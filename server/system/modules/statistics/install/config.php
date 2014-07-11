<?php

defined("IN_MODULE_ACTION") || exit("Access Denied");
return array(
    "param"         => array("name" => "统计模块", "description" => "统计模块，提供各支持模块的数据统计及汇总，采用模块扩展的方式灵活定义统计内容与视图", "author" => "banyan @ IBOS Team Inc", "version" => "1.0"),
    "configure"     => array(
        "modules"    => array("statistics"),
        "import"     => array("application.modules.statistics.core.*", "application.modules.statistics.model.*", "application.modules.statistics.controllers.*", "application.modules.statistics.utils.*", "application.modules.statistics.widgets.*"),
        "components" => array(
            "messages" => array(
                "extensionPaths" => array("statistics" => "application.modules.statistics.language")
            )
        )
    ),
    "authorization" => array()
);
