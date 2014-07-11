<?php

defined("IN_MODULE_ACTION") || exit("Access Denied");
return array(
    "param"         => array(
        "name"         => "日志",
        "category"     => "个人办公",
        "description"  => "提供企业工作日志发布",
        "author"       => "banyanCheung @ IBOS Team Inc",
        "version"      => "1.0",
        "pushMovement" => 1,
        "indexShow"    => array("widget" => true, "link" => "diary/default/index")
    ),
    "configure"     => array(
        "modules"    => array("diary"),
        "import"     => array("application.modules.diary.components.*", "application.modules.diary.controllers.*", "application.modules.diary.model.*", "application.modules.diary.utils.*", "application.modules.diary.widgets.*"),
        "components" => array(
            "messages" => array(
                "extensionPaths" => array("diary" => "application.modules.diary.language")
            )
        )
    ),
    "authorization" => array(
        "diary"      => array(
            "type"          => "node",
            "name"          => "日志管理",
            "group"         => "工作日志",
            "controllerMap" => array(
                "default"   => array("index", "add", "edit", "del", "show"),
                "share"     => array("index", "show"),
                "attention" => array("index", "edit", "show"),
                "comment"   => array("getcommentlist", "addcomment", "delcomment")
            )
        ),
        "review"     => array(
            "type"          => "node",
            "name"          => "评阅下属",
            "group"         => "工作日志",
            "controllerMap" => array(
                "review" => array("index", "personal", "add", "edit", "del", "show")
            )
        ),
        "statistics" => array(
            "type"          => "node",
            "name"          => "查看统计",
            "group"         => "工作日志",
            "controllerMap" => array(
                "stats" => array("personal", "review")
            )
        )
    ),
    "statistics"    => array("sidebar" => "IWStatDiarySidebar", "header" => "IWStatDiaryHeader", "summary" => "IWStatDiarySummary", "count" => "IWStatDiaryCount", "footer" => "IWStatDiaryFooter")
);