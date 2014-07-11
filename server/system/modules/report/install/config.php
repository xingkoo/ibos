<?php

defined("IN_MODULE_ACTION") || exit("Access Denied");
return array(
    "param"         => array(
        "name"         => "总结",
        "category"     => "个人办公",
        "description"  => "提供企业工作总结与计划",
        "author"       => "banyanCheung @ IBOS Team Inc",
        "version"      => "1.0",
        "pushMovement" => 1,
        "indexShow"    => array("widget" => true, "link" => "report/default/index")
    ),
    "configure"     => array(
        "modules"    => array("report"),
        "import"     => array("application.modules.report.components.*", "application.modules.report.controllers.*", "application.modules.report.model.*", "application.modules.report.utils.*", "application.modules.report.core.*", "application.modules.report.widgets.*"),
        "components" => array(
            "messages" => array(
                "extensionPaths" => array("report" => "application.modules.report.language")
            )
        )
    ),
    "authorization" => array(
        "report"     => array(
            "type"          => "node",
            "name"          => "个人工作总结与计划",
            "group"         => "工作总结与计划",
            "controllerMap" => array(
                "default" => array("index", "add", "edit", "del", "show"),
                "type"    => array("add", "edit", "del"),
                "comment" => array("getcommentlist", "addcomment", "delcomment")
            )
        ),
        "review"     => array(
            "type"          => "node",
            "name"          => "评阅下属总结与计划",
            "group"         => "工作总结与计划",
            "controllerMap" => array(
                "review" => array("index", "personal", "add", "edit", "del", "show")
            )
        ),
        "statistics" => array(
            "type"          => "node",
            "name"          => "查看统计",
            "group"         => "工作总结与计划",
            "controllerMap" => array(
                "stats" => array("personal", "review")
            )
        )
    ),
    "statistics"    => array("sidebar" => "IWStatReportSidebar", "header" => "IWStatReportHeader", "summary" => "IWStatReportSummary", "count" => "IWStatReportCount")
);
