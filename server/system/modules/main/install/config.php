<?php

defined("IN_MODULE_ACTION") || exit("Access Denied");
return array(
    "param"     => array("name" => "核心模块", "description" => "系统核心模块。提供IBOS程序核心流程初始化及处理", "author" => "banyanCheung @ IBOS Team Inc", "version" => "1.0"),
    "configure" => array(
        "modules"    => array("main"),
        "import"     => array("application.modules.main.components.*", "application.modules.main.behaviors.*", "application.modules.main.controllers.*", "application.modules.main.model.*", "application.modules.main.utils.*", "application.modules.main.widgets.*"),
        "components" => array(
            "setting"      => array("class" => "ICSetting"),
            "session"      => array("class" => "ICSession"),
            "cron"         => array("class" => "ICCron"),
            "process"      => array("class" => "ICProcess"),
            "errorHandler" => array("errorAction" => "main/default/error"),
            "messages"     => array(
                "extensionPaths" => array("main" => "application.modules.main.language")
            )
        )
    ),
    "behaviors" => array(
        "onInitModule" => array("class" => "application.modules.main.behaviors.InitMainModuleBehavior")
    )
);
