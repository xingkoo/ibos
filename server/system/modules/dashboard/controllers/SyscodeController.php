<?php

class DashboardSyscodeController extends DashboardBaseController
{
    public function actionIndex()
    {
        $formSubmit = EnvUtil::submitCheck("sysCodeSubmit");

        if ($formSubmit) {
            $codes = $_POST["codes"];
            $newCodes = (isset($_POST["newcodes"]) ? $_POST["newcodes"] : array());

            foreach ($codes as $id => $code) {
                Syscode::model()->modify($id, $code);
            }

            foreach ($newCodes as $newCode) {
                Syscode::model()->add($newCode);
            }

            $removeId = $_POST["removeId"];

            if (!is_null($removeId)) {
                Syscode::model()->deleteById($removeId);
            }

            $this->success(Ibos::lang("Save succeed", "message"));
        } else {
            $record = Syscode::model()->fetchAllByAllPid();
            $data = array("data" => $record);
            $this->render("index", $data);
        }
    }
}