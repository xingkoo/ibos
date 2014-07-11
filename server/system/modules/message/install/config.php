<?php

defined("IN_MODULE_ACTION") || exit("Access Denied");
return array(
    "param"     => array("name" => "消息模块", "description" => "系统核心模块。提供IBOS程序消息体系的建立。包括@人，提醒,评论，私信，微博及动态", "author" => "banyanCheung @ IBOS Team Inc", "version" => "1.0"),
    "configure" => array(
        "modules"    => array("message"),
        "import"     => array("application.modules.message.core.*", "application.modules.message.controllers.*", "application.modules.message.model.*", "application.modules.message.utils.*"),
        "components" => array(
            "messages" => array(
                "extensionPaths" => array("message" => "application.modules.message.language")
            )
        )
    )
);
