<?php

defined("IN_MODULE_ACTION") || exit("Access Denied");
return array(
    "param"         => array(
        "name"        => "工作流",
        "category"    => "工作流",
        "description" => "用于办理、设计与管理企业工作流程。",
        "author"      => "banyan @ IBOS Team Inc",
        "version"     => "2.0",
        "indexShow"   => array("widget" => true, "link" => "workflow/list/index&op=category")
    ),
    "configure"     => array(
        "modules"    => array("workflow"),
        "import"     => array("application.modules.workflow.core.*", "application.modules.workflow.controllers.*", "application.modules.workflow.model.*", "application.modules.workflow.utils.*", "application.modules.workflow.widgets.*"),
        "components" => array(
            "messages" => array(
                "extensionPaths" => array("workflow" => "application.modules.workflow.language")
            )
        )
    ),
    "authorization" => array(
        "use"     => array(
            "type"          => "node",
            "name"          => "工作流使用",
            "group"         => "使用",
            "controllerMap" => array(
                "list"    => array("index", "count"),
                "focus"   => array("index"),
                "form"    => array("index"),
                "new"     => array("index", "add"),
                "monitor" => array("index"),
                "preview" => array("getprcs", "print", "newpreview", "flow", "redo", "sendremind"),
                "query"   => array("index", "advanced", "search", "add", "searchresult", "export")
            )
        ),
        "entrust" => array(
            "type"          => "node",
            "name"          => "工作流委托",
            "group"         => "使用",
            "controllerMap" => array(
                "entrust" => array("index", "add", "status", "del", "confirmpost", "confirm")
            )
        ),
        "destroy" => array(
            "type"          => "node",
            "name"          => "工作流销毁",
            "group"         => "使用",
            "controllerMap" => array(
                "recycle" => array("index", "restore", "destroy")
            )
        ),
        "flow"    => array(
            "type"          => "node",
            "name"          => "流程管理",
            "group"         => "工作流设置",
            "controllerMap" => array(
                "type"     => array("add", "del", "edit", "export", "freenew", "getguide", "import", "index", "trans", "verify"),
                "timer"    => array("index", "save"),
                "querytpl" => array("index", "add", "del", "edit"),
                "process"  => array("index", "getprocessinfo", "getprocess", "add", "saveview", "edit", "del"),
                "manager"  => array("index", "add", "edit", "del")
            )
        ),
        "form"    => array(
            "type"          => "node",
            "name"          => "表单管理",
            "group"         => "工作流设置",
            "controllerMap" => array(
                "formtype"    => array("index", "add", "edit", "del", "import", "export", "design", "preview"),
                "formversion" => array("index", "restore", "del")
            )
        )
    )
);
