<?php

defined("IN_MODULE_ACTION") || exit("Access Denied");
return array(
    "param"        => array("name" => "投票模块", "description" => "提供信息中心等模块投票调查使用", "author" => "banyanCheung @ IBOS Team Inc", "version" => "1.0"),
    "configure"    => array(
        "modules"    => array("vote"),
        "import"     => array("application.modules.vote.components.*", "application.modules.vote.model.*", "application.modules.vote.utils.*"),
        "components" => array(
            "messages" => array(
                "extensionPaths" => array("vote" => "application.modules.vote.language")
            )
        )
    ),
    "dependencies" => array("main")
);
