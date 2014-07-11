<?php

class DashboardLoginController extends DashboardBaseController
{
    public function actionIndex()
    {
        $formSubmit = EnvUtil::submitCheck("loginSubmit");
        $bgPath = LoginTemplate::BG_PATH;

        if ($formSubmit) {
            if (isset($_POST["bgs"])) {
                foreach ($_POST["bgs"] as $id => $bg) {
                    if (FileUtil::fileExists($bg["image"])) {
                        LoginTemplate::model()->delImg($id);
                        $bg["image"] = DashboardUtil::moveTempFile($bg["image"], $bgPath);
                    }

                    $bg["disabled"] = (isset($bg["disabled"]) ? 0 : 1);
                    LoginTemplate::model()->modify($id, $bg);
                }
            }

            if (isset($_POST["newbgs"])) {
                foreach ($_POST["newbgs"] as $value) {
                    if (!empty($value["image"])) {
                        $value["image"] = DashboardUtil::moveTempFile($value["image"], $bgPath);
                    }

                    LoginTemplate::model()->add($value);
                }
            }

            if (!empty($_POST["removeId"])) {
                $removeIds = explode(",", trim($_POST["removeId"], ","));
                LoginTemplate::model()->deleteByIds($removeIds, $bgPath);
            }

            clearstatcache();
            $this->success(Ibos::lang("Save succeed", "message"));
        } else {
            if (EnvUtil::getRequest("op") === "upload") {
                $fakeUrl = $this->imgUpload("bg");
                $realUrl = FileUtil::fileName($fakeUrl);
                return $this->ajaxReturn(array("fakeUrl" => $fakeUrl, "url" => $realUrl));
            }

            $data = array("list" => LoginTemplate::model()->fetchAll(), "bgpath" => $bgPath);
            $this->render("index", $data);
        }
    }
}
