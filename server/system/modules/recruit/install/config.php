<?php

defined("IN_MODULE_ACTION") || exit("Access Denied");
return array(
    "param"         => array("name" => "招聘", "category" => "人力资源", "description" => "提供企业招聘信息", "author" => "banyanCheung @ IBOS Team Inc", "version" => "1.0"),
    "configure"     => array(
        "modules"    => array("recruit"),
        "import"     => array("application.modules.recruit.components.*", "application.modules.recruit.controllers.*", "application.modules.recruit.model.*", "application.modules.recruit.utils.*", "application.modules.recruit.core.*", "application.modules.recruit.widgets.*"),
        "components" => array(
            "messages" => array(
                "extensionPaths" => array("recruit" => "application.modules.recruit.language")
            )
        )
    ),
    "authorization" => array(
        "resume"     => array(
            "type"          => "node",
            "name"          => "人才管理",
            "group"         => "招聘管理",
            "controllerMap" => array(
                "resume" => array("index", "add", "show", "edit", "sendEmail", "del")
            )
        ),
        "contact"    => array(
            "type"          => "node",
            "name"          => "联系记录",
            "group"         => "招聘管理",
            "controllerMap" => array(
                "contact" => array("index", "add", "edit", "del", "export")
            )
        ),
        "interview"  => array(
            "type"          => "node",
            "name"          => "面试记录",
            "group"         => "招聘管理",
            "controllerMap" => array(
                "interview" => array("index", "add", "edit", "del", "export")
            )
        ),
        "bgchecks"   => array(
            "type"          => "node",
            "name"          => "背景调查",
            "group"         => "招聘管理",
            "controllerMap" => array(
                "bgchecks" => array("index", "add", "edit", "del", "export")
            )
        ),
        "statistics" => array(
            "type"          => "node",
            "name"          => "招聘统计",
            "group"         => "招聘管理",
            "controllerMap" => array(
                "stats" => array("index")
            )
        )
    ),
    "statistics"    => array("sidebar" => "IWStatRecruitSidebar", "header" => "IWStatRecruitHeader", "summary" => "IWStatRecruitSummary", "count" => "IWStatRecruitCount")
);
