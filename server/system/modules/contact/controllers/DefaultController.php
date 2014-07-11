<?php

class ContactDefaultController extends ContactBaseController
{
    public function actionIndex()
    {
        $op = EnvUtil::getRequest("op");
        $op = (in_array($op, array("dept", "letter")) ? $op : "dept");
        $params = array();

        if ($op == "letter") {
            $params["datas"] = $this->getDataByLetter();
        } else {
            $params["datas"] = $this->getDataByDept();
        }

        $userDatas = array();

        if (!empty($params["datas"])) {
            foreach ($params["datas"] as $datas) {
                $userDatas = ($op == "dept" ? array_merge($userDatas, $datas["users"]) : array_merge($userDatas, $datas));
            }
        }

        $params["uids"] = implode(",", ConvertUtil::getSubByKey($userDatas, "uid"));
        $uid = Ibos::app()->user->uid;
        $params["cuids"] = Contact::model()->fetchAllConstantByUid($uid);
        $this->setPageTitle(Ibos::lang("Contact"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Contact"), "url" => $this->createUrl("defalut/index")),
            array("name" => Ibos::lang("Company contact"))
        ));
        $view = ($op == "letter" ? "letter" : "dept");
        $params["allLetters"] = $this->allLetters;
        $this->render($view, $params);
    }

    public function actionAjaxApi()
    {
        $this->ajaxApi();
    }

    public function actionExport()
    {
        $this->export();
    }

    public function actionPrintContact()
    {
        $this->printContact();
    }
}
