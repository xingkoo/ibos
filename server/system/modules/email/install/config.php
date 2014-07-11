<?php

defined("IN_MODULE_ACTION") || exit("Access Denied");
return array(
    "param"         => array(
        "name"        => "邮件",
        "category"    => "个人办公",
        "description" => "提供企业内外邮件沟通。",
        "author"      => "banyan @ IBOS Team Inc",
        "version"     => "1.0",
        "indexShow"   => array("widget" => true, "link" => "email/list/index")
    ),
    "configure"     => array(
        "modules"    => array("email"),
        "import"     => array("application.modules.email.components.*", "application.modules.email.controllers.*", "application.modules.email.model.*", "application.modules.email.core.*", "application.modules.email.utils.*", "application.modules.email.extensions.*"),
        "components" => array(
            "messages" => array(
                "extensionPaths" => array("email" => "application.modules.email.language")
            )
        )
    ),
    "authorization" => array(
        "inbox"    => array(
            "type"          => "node",
            "name"          => "内部邮箱",
            "group"         => "邮件管理",
            "controllerMap" => array(
                "list"    => array("index", "search"),
                "folder"  => array("index", "add", "edit", "del"),
                "content" => array("index", "add", "edit", "show", "export")
            )
        ),
        "webinbox" => array(
            "type"          => "node",
            "name"          => "外部邮箱",
            "group"         => "邮件管理",
            "controllerMap" => array(
                "web" => array("index", "add", "edit", "del", "receive", "show")
            )
        )
    )
);
