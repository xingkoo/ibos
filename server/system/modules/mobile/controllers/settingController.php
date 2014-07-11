<?php

class MobileSettingController extends MobileBaseController
{
    public function actionIndex()
    {
    }

    public function actionUpload()
    {
        if ($_FILES["avatar"]) {
            $upload = FileUtil::getUpload($_FILES["avatar"]);

            if (!$upload->save()) {
                echo "出错了";
            } else {
                $info = $upload->getAttach();
                $file = FileUtil::getAttachUrl() . "/" . $info["type"] . "/" . $info["attachment"];
                $fileUrl = FileUtil::fileName($file);
                $uid = Yii::app()->user->uid;
                $tempAvatar = $file;
                $avatarPath = "data/avatar/";
                $avatarBig = UserUtil::getAvatar($uid, "big");
                $avatarMiddle = UserUtil::getAvatar($uid, "middle");
                $avatarSmall = UserUtil::getAvatar($uid, "small");

                if (LOCAL) {
                    FileUtil::makeDirs($avatarPath . dirname($avatarBig));
                }

                FileUtil::createFile("data/avatar/" . $avatarBig, "");
                FileUtil::createFile("data/avatar/" . $avatarMiddle, "");
                FileUtil::createFile("data/avatar/" . $avatarSmall, "");
                Yii::import("ext.ThinkImage.ThinkImage", true);
                $imgObj = new ThinkImage(THINKIMAGE_GD);
                $imgTemp = $imgObj->open($tempAvatar);
                $params = array("w" => $imgTemp->width(), "h" => $imgTemp->height(), "x" => "0", "y" => "0");

                if ($params["h"] < $params["w"]) {
                    $params["x"] = ($params["w"] - $params["h"]) / 2;
                    $params["w"] = $params["h"];
                } else {
                    $params["y"] = ($params["h"] - $params["w"]) / 2;
                    $params["h"] = $params["w"];
                }

                $imgObj->open($tempAvatar)->crop($params["w"], $params["h"], $params["x"], $params["y"])->save($tempAvatar);
                $imgObj->open($tempAvatar)->thumb(180, 180, 1)->save($avatarPath . $avatarBig);
                $imgObj->open($tempAvatar)->thumb(60, 60, 1)->save($avatarPath . $avatarMiddle);
                $imgObj->open($tempAvatar)->thumb(30, 30, 1)->save($avatarPath . $avatarSmall);
            }
        }
    }

    public function actionUpdate()
    {
        $profileField = array("birthday", "bio", "telephone", "address", "qq");
        $userField = array("mobile", "email");
        $model = array();

        foreach ($_POST as $key => $value) {
            if (in_array($key, $profileField)) {
                if (($key == "birthday") && !empty($value)) {
                    $value = strtotime($value);
                }

                $model["UserProfile"][$key] = StringUtil::filterCleanHtml($value);
            } elseif (in_array($key, $userField)) {
                $model["User"][$key] = StringUtil::filterCleanHtml($value);
            }
        }

        foreach ($model as $modelObject => $value) {
            $modelObject::model()->modify(Yii::app()->user->uid, $value);
        }

        UserUtil::cleanCache(Yii::app()->user->uid);
        exit();
    }

    public function actionChangePass()
    {
        $user["salt"] = Yii::app()->user->salt;
        $user["password"] = Yii::app()->user->password;
        $oldpass = $_REQUEST["oldpass"];
        $newpass = $_REQUEST["newpass"];
        $repass = $_REQUEST["repass"];
        $update = false;

        if ($oldpass == "") {
            $errorMsg = Ibos::lang("Original password require");
            $this->ajaxReturn(array("isSuccess" => "false", "msg" => $errorMsg), "JSONP");
        } elseif (strcasecmp(md5(md5($oldpass) . $user["salt"]), $user["password"]) !== 0) {
            $errorMsg = Ibos::lang("Original password error");
            $this->ajaxReturn(array("isSuccess" => "false", "msg" => $errorMsg), "JSONP");
        } else {
            if (!empty($newpass) && (strcasecmp($newpass, $repass) !== 0)) {
                $errorMsg = Ibos::lang("Confirm password is not correct");
                $this->ajaxReturn(array("isSuccess" => "false", "msg" => $errorMsg), "JSONP");
            } else {
                $password = md5(md5($newpass) . $user["salt"]);
                $update = User::model()->updateByUid(Yii::app()->user->uid, array("password" => $password));
                $msg = Ibos::lang("Change password succeed");
                $this->ajaxReturn(array("isSuccess" => "true", "msg" => $msg, "login" => "false"), "JSONP");
            }
        }

        exit();
    }
}
