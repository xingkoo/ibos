<?php

class MainAttachController extends ICController
{
    public function actionUpload()
    {
        $attachType = EnvUtil::getRequest("type");

        if (empty($attachType)) {
            $attachType = "common";
        }

        $module = EnvUtil::getRequest("module");
        $object = ucfirst($attachType) . "Attach";

        if (class_exists($object)) {
            $attach = new $object("Filedata", $module);
            $return = $attach->upload();
            $this->ajaxReturn($return, "eval");
        }
    }

    public function actionDownload()
    {
        $data = $this->getData();

        if (!empty($data)) {
            return FileUtil::download($data["attach"], $data["decodeArr"]);
        }

        $this->setPageTitle(Ibos::lang("Filelost"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Filelost"))
        ));
        $this->render("filelost");
    }

    public function actionOffice()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $widget = Ibos::app()->getWidgetFactory()->createWidget($this, "IWMainOffice", array());
            return $widget->handleRequest();
        } else {
            $data = $this->getData();
            $widget = $this->createWidget("IWMainOffice", array("param" => $data["decodeArr"], "attach" => $data["attach"]));
            echo $widget->run();
        }
    }

    private function getData()
    {
        $id = EnvUtil::getRequest("id");
        $aidString = base64_decode(rawurldecode($id));

        if (empty($aidString)) {
            $this->error(Ibos::lang("Parameters error", "error"), "", array("autoJump" => 0));
        }

        $salt = Ibos::app()->user->salt;
        $decodeString = StringUtil::authCode($aidString, "DECODE", $salt);
        $decodeArr = explode("|", $decodeString);
        $count = count($decodeArr);

        if ($count < 3) {
            $this->error(Ibos::lang("Data type invalid", "error"), "", array("autoJump" => 0));
        } else {
            $aid = $decodeArr[0];
            $tableId = $decodeArr[1];
            if ((0 <= $tableId) && ($tableId < 10)) {
                $attach = AttachmentN::model()->fetch($tableId, $aid);
            }

            $return = array(
                "decodeArr" => $decodeArr,
                "attach"    => array()
                );

            if (!empty($attach)) {
                $return["attach"] = $attach;
            }

            return $return;
        }
    }
}
