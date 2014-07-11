<?php

defined("IN_MODULE_ACTION") || exit("Access Denied");
return array(
    "param"         => array(
        "name"         => "公文",
        "category"     => "信息中心",
        "description"  => "提供企业公文信息发布，以及版本记录",
        "author"       => "banyanCheung @ IBOS Team Inc",
        "version"      => "1.0",
        "pushMovement" => 1,
        "indexShow"    => array("widget" => true, "link" => "officialdoc/officialdoc/index")
    ),
    "configure"     => array(
        "modules"    => array("officialdoc"),
        "import"     => array("application.modules.officialdoc.components.*", "application.modules.officialdoc.core.*", "application.modules.officialdoc.controllers.*", "application.modules.officialdoc.model.*", "application.modules.officialdoc.utils.*"),
        "components" => array(
            "messages" => array(
                "extensionPaths" => array("officialdoc" => "application.modules.officialdoc.language")
            )
        )
    ),
    "authorization" => array(
        "view"    => array(
            "type"          => "node",
            "name"          => "公文浏览",
            "group"         => "公文",
            "controllerMap" => array(
                "officialdoc" => array("index", "show"),
                "category"    => array("index"),
                "comment"     => array("getcommentlist", "addcomment", "delcomment")
            )
        ),
        "manager" => array(
            "type"          => "node",
            "name"          => "公文管理",
            "group"         => "公文",
            "controllerMap" => array(
                "officialdoc" => array("add", "edit", "del"),
                "category"    => array("add", "edit", "del")
            )
        )
    )
);
