<?php

defined("IN_MODULE_ACTION") || exit("Access Denied");
return array(
    "param"         => array(
        "name"         => "新闻",
        "category"     => "信息中心",
        "description"  => "提供企业新闻信息发布",
        "author"       => "banyanCheung @ IBOS Team Inc",
        "version"      => "1.0",
        "pushMovement" => 1,
        "indexShow"    => array("widget" => true, "link" => "article/default/index")
    ),
    "configure"     => array(
        "modules"    => array("article"),
        "import"     => array("application.modules.article.components.*", "application.modules.article.core.*", "application.modules.article.controllers.*", "application.modules.article.model.*", "application.modules.article.utils.*"),
        "components" => array(
            "ICArticleVote" => array("class" => "application.modules.article.components.ICArticleVote"),
            "messages"      => array(
                "extensionPaths" => array("article" => "application.modules.article.language")
            )
        )
    ),
    "authorization" => array(
        "view"    => array(
            "type"          => "node",
            "name"          => "新闻浏览",
            "group"         => "新闻",
            "controllerMap" => array(
                "default"  => array("index"),
                "category" => array("index"),
                "comment"  => array("getcommentlist", "addcomment", "delcomment")
            )
        ),
        "manager" => array(
            "type"          => "node",
            "name"          => "新闻管理",
            "group"         => "新闻",
            "controllerMap" => array(
                "default"  => array("add", "edit", "del"),
                "category" => array("add", "edit", "del")
            )
        )
    )
);
