<?php

class ICAuthManager extends CDbAuthManager
{
    /**
     * @var string 存储认证项目的表
     */
    public $itemTable = "{{auth_item}}";
    /**
     * @var string the name of the table storing authorization item hierarchy.
     */
    public $itemChildTable = "{{auth_item_child}}";
    /**
     * @var string the name of the table storing authorization item assignments. 
     */
    public $assignmentTable = "{{auth_assignment}}";
}
