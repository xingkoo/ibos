<?php

class UserInfoController extends ICController
{
    public function actionUserCard()
    {
        $uid = EnvUtil::getRequest("uid");
        $user = User::model()->fetchByUid($uid);
        $onlineStatus = UserUtil::getOnlineStatus($uid);
        $styleMap = array(-1 => "o-pm-offline", 1 => "o-pm-online");

        if (empty($user)) {
            $this->error(Ibos::lang("Request tainting", "error"));
        } else {
            $weiboExists = ModuleUtil::getIsEnabled("weibo");
            $data = array("user" => $user, "status" => $styleMap[$onlineStatus], "lang" => Ibos::getLangSources(), "weibo" => $weiboExists);

            if ($weiboExists) {
                $data["userData"] = UserData::model()->getUserData($user["uid"]);
                $data["states"] = Follow::model()->getFollowState(Ibos::app()->user->uid, $user["uid"]);
            }

            $content = $this->renderPartial("userCard", $data, true);
            echo $content;
            exit();
        }
    }

    public function actionCropImg()
    {
        if (EnvUtil::submitCheck("userSubmit")) {
            $params = $_POST;
            if (!isset($params) && empty($params)) {
                return null;
            }

            $tempAvatar = $params["src"];
            $avatarPath = "data/avatar/";
            $avatarBig = UserUtil::getAvatar($params["uid"], "big");
            $avatarMiddle = UserUtil::getAvatar($params["uid"], "middle");
            $avatarSmall = UserUtil::getAvatar($params["uid"], "small");

            if (LOCAL) {
                FileUtil::makeDirs($avatarPath . dirname($avatarBig));
            }

            FileUtil::createFile("data/avatar/" . $avatarBig, "");
            FileUtil::createFile("data/avatar/" . $avatarMiddle, "");
            FileUtil::createFile("data/avatar/" . $avatarSmall, "");
            Ibos::import("ext.ThinkImage.ThinkImage", true);
            $imgObj = new ThinkImage(THINKIMAGE_GD);
            $imgObj->open($tempAvatar)->crop($params["w"], $params["h"], $params["x"], $params["y"])->save($tempAvatar);
            $imgObj->open($tempAvatar)->thumb(180, 180, 1)->save($avatarPath . $avatarBig);
            $imgObj->open($tempAvatar)->thumb(60, 60, 1)->save($avatarPath . $avatarMiddle);
            $imgObj->open($tempAvatar)->thumb(30, 30, 1)->save($avatarPath . $avatarSmall);
            $this->success(Ibos::lang("Upload avatar succeed"), $this->createUrl("home/personal", array("op" => "avatar")));
            exit();
        }
    }

    public function actionUploadAvatar()
    {
        $upload = FileUtil::getUpload($_FILES["Filedata"]);

        if (!$upload->save()) {
            $this->ajaxReturn(array("msg" => Ibos::lang("Save failed", "message"), "IsSuccess" => false));
        } else {
            $info = $upload->getAttach();
            $file = FileUtil::getAttachUrl() . "/" . $info["type"] . "/" . $info["attachment"];
            $fileUrl = FileUtil::fileName($file);
            $tempSize = FileUtil::imageSize($fileUrl);
            if (($tempSize[0] < 180) || ($tempSize[1] < 180)) {
                $this->ajaxReturn(array("msg" => Ibos::lang("Avatar size error"), "IsSuccess" => false), "json");
            }

            $this->ajaxReturn(array("data" => $fileUrl, "file" => $file, "IsSuccess" => true));
        }
    }
}
