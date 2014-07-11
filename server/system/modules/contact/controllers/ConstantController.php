<?php

class ContactConstantController extends ContactBaseController
{
    public function actionIndex()
    {
        $uid = Ibos::app()->user->uid;
        $cuids = Contact::model()->fetchAllConstantByUid($uid);
        $res = UserUtil::getUserByPy($cuids);
        $group = ContactUtil::handleLetterGroup($res);
        $userDatas = array();

        foreach ($group as $users) {
            $userDatas = array_merge($userDatas, $users);
        }

        $params = array("datas" => $group, "letters" => array_keys($group), "allLetters" => $this->allLetters, "uids" => implode(",", ConvertUtil::getSubByKey($userDatas, "uid")));
        $this->setPageTitle(Ibos::lang("Regular contact"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Contact"), "url" => $this->createUrl("default/index")),
            array("name" => Ibos::lang("Regular contact"))
        ));
        $this->render("index", $params);
    }
}
