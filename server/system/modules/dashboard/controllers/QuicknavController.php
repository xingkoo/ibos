<?php

class DashboardQuicknavController extends DashboardBaseController
{
    const TTF_FONT_File = "data/font/msyh.ttf";

    private $_iconPath = "data/icon/";
    private $_iconTempPath = "data/icon/temp/";

    public function actionIndex()
    {
        $menus = MenuCommon::model()->fetchAll(array("order" => "sort ASC"));

        foreach ($menus as $k => $menu) {
            if ($menu["iscustom"]) {
                $menus[$k]["icon"] = $this->_iconPath . $menu["icon"];
            } else {
                $menus[$k]["icon"] = Ibos::app()->assetManager->getAssetsUrl($menu["module"]) . "/image/icon.png";
            }
        }

        $this->render("index", array("menus" => $menus));
    }

    public function actionDel()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $id = intval(EnvUtil::getRequest("id"));
            MenuCommon::model()->deleteByPk($id);
            $this->ajaxReturn(array("isSuccess" => true));
        }
    }

    public function actionAdd()
    {
        if (EnvUtil::getRequest("formhash")) {
            $data = $this->beforeSave();
            $cond = array("select" => "sort", "order" => "`sort` DESC");
            $sortRecord = MenuCommon::model()->fetch($cond);

            if (empty($sortRecord)) {
                $sortId = 0;
            } else {
                $sortId = $sortRecord["sort"];
            }

            $data["sort"] = $sortId + 1;
            $data["module"] = "";
            $data["iscommon"] = 0;
            $data["iscustom"] = 1;
            $data["disabled"] = 0;
            $data["openway"] = 0;
            MenuCommon::model()->add($data);
            $this->success(Ibos::lang("Save succeed", "message"), $this->createUrl("quicknav/index"));
        } else {
            $this->render("add");
        }
    }

    protected function beforeSave()
    {
        $name = StringUtil::filterStr(EnvUtil::getRequest("name"));
        $url = StringUtil::filterStr(EnvUtil::getRequest("url"));
        $icon = StringUtil::filterStr(EnvUtil::getRequest("quicknavimg"));

        if (LOCAL) {
            FileUtil::makeDirs($this->_iconPath);
        }

        $saveName = StringUtil::random(16) . ".png";

        if (!empty($icon)) {
            $this->createImgIcon($icon, $saveName);
        } else {
            $val = EnvUtil::getRequest("fontvalue");
            $this->createColorImg($saveName, $val);
        }

        $data = array("name" => $name, "url" => $url, "description" => "", "icon" => $saveName);
        return $data;
    }

    public function actionEdit()
    {
        if (EnvUtil::getRequest("formhash")) {
            $id = intval(EnvUtil::getRequest("id"));
            $name = StringUtil::filterStr(EnvUtil::getRequest("name"));
            $url = StringUtil::filterStr(EnvUtil::getRequest("url"));
            $icon = StringUtil::filterStr(EnvUtil::getRequest("quicknavimg"));

            if (!empty($icon)) {
                FileUtil::copyToDir($icon, $this->_iconPath);
                $info = pathinfo($icon);
                $saveName = $info["basename"];
            } else {
                $saveName = StringUtil::random(16) . ".png";
                $val = EnvUtil::getRequest("fontvalue");
                $this->createColorImg($saveName, $val);
            }

            $data = array("name" => $name, "url" => $url, "description" => "", "icon" => $saveName);
            MenuCommon::model()->modify($id, $data);
            $this->success(Ibos::lang("Update succeed", "message"), $this->createUrl("quicknav/index"));
        } else {
            $op = EnvUtil::getRequest("op");

            if (empty($op)) {
                $id = intval(EnvUtil::getRequest("id"));
                $menu = MenuCommon::model()->fetchByPk($id);

                if (empty($menu)) {
                    $this->error(Ibos::lang("Quicknav not fount tip"), $this->createUrl("quicknav/index"));
                }

                $menu["icon"] = FileUtil::fileName($this->_iconPath . $menu["icon"]);
                $this->render("edit", array("menu" => $menu));
            } else {
                $this->{$op}();
            }
        }
    }

    private function createColorImg($saveName, $val, $fontsize = 15)
    {
        $hexColor = EnvUtil::getRequest("quicknavcolor");
        $tempFile = $this->getTempByHex($hexColor);

        if (!$tempFile) {
            $this->error(Ibos::lang("Quicknav add faild"), $this->createUrl("quicknav/index"));
        }

        $outputFile = $this->_iconPath . $saveName;
        $rgb = array("r" => 255, "g" => 255, "b" => 255);
        ImageUtil::waterMarkString($val, $fontsize, $tempFile, $outputFile, 5, 100, $rgb, self::TTF_FONT_File);
        return true;
    }

    private function createImgIcon($tempFile, $outputName)
    {
        $outputFile = $this->_iconPath . $outputName;
        FileUtil::createFile($outputFile, "");
        Ibos::import("ext.ThinkImage.ThinkImage", true);
        $imgObj = new ThinkImage(THINKIMAGE_GD);
        $imgObj->open($tempFile)->save($outputFile);
        return true;
    }

    public function actionUploadIcon()
    {
        $upload = FileUtil::getUpload($_FILES["Filedata"]);

        if (!$upload->save()) {
            $this->ajaxReturn(array("msg" => Ibos::lang("Save failed", "message"), "isSuccess" => false));
        } else {
            $info = $upload->getAttach();
            $file = FileUtil::getAttachUrl() . "/" . $info["type"] . "/" . $info["attachment"];
            $fileUrl = FileUtil::fileName($file);
            $tempSize = FileUtil::imageSize($fileUrl);
            if (($tempSize[0] < 64) || ($tempSize[1] < 64)) {
                $this->ajaxReturn(array("msg" => Ibos::lang("Icon size error"), "isSuccess" => false));
            }

            $this->ajaxReturn(array("imgurl" => $fileUrl, "aid" => $fileUrl, "name" => $info["name"], "isSuccess" => true));
        }
    }

    private function changeEnabled()
    {
        $id = intval(EnvUtil::getRequest("id"));
        $type = StringUtil::filterStr(EnvUtil::getRequest("type"));

        if ($type == "disabled") {
            $disabled = 1;
        } else {
            $disabled = 0;
        }

        MenuCommon::model()->modify($id, array("disabled" => $disabled));
        $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Operation succeed", "message")));
    }

    private function changeOpenWay()
    {
        $id = intval(EnvUtil::getRequest("id"));
        $type = StringUtil::filterStr(EnvUtil::getRequest("type"));

        if ($type == "disabled") {
            $openway = 1;
        } else {
            $openway = 0;
        }

        MenuCommon::model()->modify($id, array("openway" => $openway));
        $this->ajaxReturn(array("openway" => $openway, "isSuccess" => true, "msg" => Ibos::lang("Operation succeed", "message")));
    }

    protected function getTempByHex($hex)
    {
        $res = false;
        $allTemp = array("#E47E61" => "red.png", "#F09816" => "orange.png", "#D29A63" => "yellow.png", "#7BBF00" => "green.png", "#3497DB" => "blue.png", "#82939E" => "gray.png", "#8EABCD" => "inky.png", "#AD85CC" => "purple.png", "#58585C" => "black.png");

        if (in_array($hex, array_keys($allTemp))) {
            $file = FileUtil::fileName($this->_iconTempPath . $allTemp[$hex]);

            if (FileUtil::fileExists($file)) {
                $res = $file;
            }
        }

        return $res;
    }
}
