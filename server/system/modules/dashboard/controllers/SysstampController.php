<?php

class DashboardSysstampController extends DashboardBaseController
{
    public function actionIndex()
    {
        $formSubmit = EnvUtil::submitCheck("stampSubmit");
        $stampPath = Stamp::STAMP_PATH;

        if ($formSubmit) {
            if (isset($_POST["stamps"])) {
                foreach ($_POST["stamps"] as $id => $stamp) {
                    if (FileUtil::fileExists($stamp["stamp"])) {
                        Stamp::model()->delImg($id, "stamp");
                        $stamp["stamp"] = DashboardUtil::moveTempFile($stamp["stamp"], $stampPath);
                    }

                    if (FileUtil::fileExists($stamp["icon"])) {
                        Stamp::model()->delImg($id, "icon");
                        $stamp["icon"] = DashboardUtil::moveTempFile($stamp["icon"], $stampPath);
                    }

                    Stamp::model()->modify($id, $stamp);
                }
            }

            if (isset($_POST["newstamps"])) {
                foreach ($_POST["newstamps"] as $value) {
                    if (!empty($value["stamp"])) {
                        $value["stamp"] = DashboardUtil::moveTempFile($value["stamp"], $stampPath);
                    }

                    if (!empty($value["icon"])) {
                        $value["icon"] = DashboardUtil::moveTempFile($value["icon"], $stampPath);
                    }

                    Stamp::model()->add($value);
                }
            }

            if (!empty($_POST["removeId"])) {
                $removeIds = explode(",", trim($_POST["removeId"], ","));
                Stamp::model()->deleteByIds($removeIds);
            }

            clearstatcache();
            $this->success(Ibos::lang("Save succeed", "message"));
        } else {
            if (EnvUtil::getRequest("op") === "upload") {
                $fakeUrl = $this->imgUpload("stamp");
                $realUrl = FileUtil::fileName($fakeUrl);
                return $this->ajaxReturn(array("fakeUrl" => $fakeUrl, "url" => $realUrl));
            }

            $data = array("stampUrl" => $stampPath, "list" => Stamp::model()->fetchAll(), "maxSort" => Stamp::model()->getMaxSort());
            $this->render("index", $data);
        }
    }
}
