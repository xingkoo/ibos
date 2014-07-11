<?php

class UserSkinController extends UserHomeBaseController
{
    public function actionCropBg()
    {
        if (EnvUtil::submitCheck("bgSubmit") && !empty($_POST["src"])) {
            $params = $_POST;
            if (!isset($params) && empty($params)) {
                return null;
            }

            $tempBg = $params["src"];
            $bgPath = "data/home/";
            $bgBig = UserUtil::getBg($params["uid"], "big");
            $bgMiddle = UserUtil::getBg($params["uid"], "middle");
            $bgSmall = UserUtil::getBg($params["uid"], "small");

            if (LOCAL) {
                FileUtil::makeDirs($bgPath . dirname($bgBig));
            }

            FileUtil::createFile("data/home/" . $bgBig, "");
            FileUtil::createFile("data/home/" . $bgMiddle, "");
            FileUtil::createFile("data/home/" . $bgSmall, "");
            Ibos::import("ext.ThinkImage.ThinkImage", true);
            $imgObj = new ThinkImage(THINKIMAGE_GD);

            if (!isset($params["noCrop"])) {
                $imgObj->open($tempBg)->crop($params["w"], $params["h"], $params["x"], $params["y"], 1000, 300)->save($tempBg);
            }

            $imgObj->open($tempBg)->thumb(1000, 300, 1)->save($bgPath . $bgBig);
            $imgObj->open($tempBg)->thumb(520, 156, 1)->save($bgPath . $bgMiddle);
            $imgObj->open($tempBg)->thumb(400, 120, 1)->save($bgPath . $bgSmall);
            if (isset($params["commonSet"]) && $params["commonSet"]) {
                $this->setCommonBg($bgPath . $bgBig);
            }

            $this->ajaxReturn(array("isSuccess" => true));
            exit();
        }
    }

    public function actionUploadBg()
    {
        $upload = FileUtil::getUpload($_FILES["Filedata"]);

        if (!$upload->save()) {
            $this->ajaxReturn(array("msg" => Ibos::lang("Save failed", "message"), "isSuccess" => false));
        } else {
            $info = $upload->getAttach();
            $file = FileUtil::getAttachUrl() . "/" . $info["type"] . "/" . $info["attachment"];
            $fileUrl = FileUtil::fileName($file);
            $tempSize = FileUtil::imageSize($fileUrl);
            if (($tempSize[0] < 1000) || ($tempSize[1] < 300)) {
                $this->ajaxReturn(array("msg" => Ibos::lang("Bg size error"), "isSuccess" => false), "json");
            }

            $this->ajaxReturn(array("data" => $fileUrl, "file" => $file, "isSuccess" => true));
        }
    }

    public function actionDelBg()
    {
        $id = intval(EnvUtil::getRequest("id"));
        BgTemplate::model()->deleteByPk($id);
        $this->ajaxReturn(array("isSuccess" => true));
    }

    private function setCommonBg($src)
    {
        $bgPath = "data/home/";
        $random = StringUtil::random(16);
        $bgBig = $random . "_big.jpg";
        $bgMiddle = $random . "_middle.jpg";
        $bgSmall = $random . "_small.jpg";
        FileUtil::createFile($bgPath . $bgBig, "");
        FileUtil::createFile($bgPath . $bgMiddle, "");
        FileUtil::createFile($bgPath . $bgSmall, "");
        Ibos::import("ext.ThinkImage.ThinkImage", true);
        $imgObj = new ThinkImage(THINKIMAGE_GD);
        $imgObj->open($src)->thumb(1000, 300, 1)->save($bgPath . $bgBig);
        $imgObj->open($src)->thumb(520, 156, 1)->save($bgPath . $bgMiddle);
        $imgObj->open($src)->thumb(400, 120, 1)->save($bgPath . $bgSmall);
        $data = array("desc" => "", "status" => 0, "system" => 0, "image" => $random);
        $addRes = BgTemplate::model()->add($data);
        return $addRes;
    }
}
