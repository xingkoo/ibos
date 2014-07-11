<?php

class WeiboShareController extends WeiboBaseController
{
    public function actionIndex()
    {
        $shareInfo["sid"] = intval(EnvUtil::getRequest("sid"));
        $shareInfo["stable"] = StringUtil::filterCleanHtml(EnvUtil::getRequest("stable"));
        $shareInfo["initHTML"] = StringUtil::filterDangerTag(EnvUtil::getRequest("initHTML"));
        $shareInfo["curid"] = StringUtil::filterCleanHtml(EnvUtil::getRequest("curid"));
        $shareInfo["curtable"] = StringUtil::filterCleanHtml(EnvUtil::getRequest("curtable"));
        $shareInfo["module"] = StringUtil::filterCleanHtml(EnvUtil::getRequest("module"));
        $shareInfo["isrepost"] = intval(EnvUtil::getRequest("isrepost"));
        if (empty($shareInfo["stable"]) || empty($shareInfo["sid"])) {
            echo "类型和资源ID不能为空";
            exit();
        }

        if (!$oldInfo = Source::getSourceInfo($shareInfo["stable"], $shareInfo["sid"], false, $shareInfo["module"])) {
            echo "此信息不可以被转发";
            exit();
        }

        empty($shareInfo["module"]) && ($shareInfo["module"] = $oldInfo["module"]);
        if (empty($shareInfo["initHTML"]) && !empty($shareInfo["curid"])) {
            if (($shareInfo["curid"] != $shareInfo["sid"]) && ($shareInfo["isrepost"] == 1)) {
                $curInfo = Source::getSourceInfo($shareInfo["curtable"], $shareInfo["curid"], false, "weibo");
                $userInfo = $curInfo["source_user_info"];
                $shareInfo["initHTML"] = " //@" . $userInfo["realname"] . "：" . $curInfo["source_content"];
                $shareInfo["initHTML"] = str_replace(array("\n", "\r"), array("", ""), $shareInfo["initHTML"]);
            }
        }

        $shareInfo["shareHtml"] = (!empty($oldInfo["shareHtml"]) ? $oldInfo["shareHtml"] : "");
        $data = array("shareInfo" => $shareInfo, "oldInfo" => $oldInfo);
        $this->renderPartial("index", $data);
    }
}
