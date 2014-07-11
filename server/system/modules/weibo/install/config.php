<?php

defined("IN_MODULE_ACTION") || exit("Access Denied");
return array(
    "param"         => array("name" => "企业微博", "description" => "企业微博", "author" => "banyan @ IBOS Team Inc", "version" => "1.0"),
    "configure"     => array(
        "modules"    => array("weibo"),
        "import"     => array("application.modules.weibo.core.*", "application.modules.weibo.controllers.*", "application.modules.weibo.model.*", "application.modules.weibo.utils.*"),
        "components" => array(
            "messages" => array(
                "extensionPaths" => array("weibo" => "application.modules.weibo.language")
            )
        )
    ),
    "authorization" => array()
);
