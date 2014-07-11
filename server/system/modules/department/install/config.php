<?php

defined("IN_MODULE_ACTION") || exit("Access Denied");
return array(
    "param"     => array("name" => "部门模块", "description" => "提供IBOS2部门管理所需功能", "author" => "banyanCheung @ IBOS Team Inc", "version" => "1.0"),
    "configure" => array(
        "modules"    => array("department"),
        "import"     => array("application.modules.department.components.*", "application.modules.department.model.*", "application.modules.department.utils.*"),
        "components" => array(
            "messages" => array(
                "extensionPaths" => array("department" => "application.modules.department.language")
            )
        )
    ),
    "model"     => array("department", "department_related")
);
