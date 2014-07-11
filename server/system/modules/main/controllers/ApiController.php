<?php

class MainApiController extends ICController
{
    public function actionLoadModule()
    {
        $moduleStr = EnvUtil::getRequest("module");
        $moduleStr = urldecode($moduleStr);
        $moduleArr = explode(",", $moduleStr);
        $data = MainUtil::execApiMethod("renderIndex", $moduleArr);
        $this->ajaxReturn($data);
    }

    public function actionLoadNew()
    {
        $moduleStr = EnvUtil::getRequest("module");
        $moduleStr = urldecode($moduleStr);
        $moduleArr = explode(",", $moduleStr);
        $data = MainUtil::execApiMethod("loadNew", $moduleArr);
        $data["timestamp"] = TIMESTAMP;
        $this->ajaxReturn($data);
    }
}
