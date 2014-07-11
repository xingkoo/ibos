<?php

class AssignmentApi extends MessageApi
{
    private $_indexTab = array("charge", "designee");

    public function loadSetting()
    {
        return array(
        "name"  => "assignment",
        "title" => "任务指派",
        "style" => "in-assignment",
        "tab"   => array(
            array("name" => "charge", "title" => "我负责的任务", "icon" => "o-ol-am-user"),
            array("name" => "designee", "title" => "我指派的任务", "icon" => "o-ol-am-appoint")
            )
        );
    }

    public function renderIndex()
    {
        $return = array();
        $viewAlias = "application.modules.assignment.views.indexapi.assignment";
        $uid = Ibos::app()->user->uid;
        $chargeData = Assignment::model()->fetchUnfinishedByChargeuid($uid);
        $designeeData = Assignment::model()->fetchUnfinishedByDesigneeuid($uid);
        $data = array("chargeData" => AssignmentUtil::handleListData($chargeData), "designeeData" => AssignmentUtil::handleListData($designeeData), "lang" => Ibos::getLangSource("assignment.default"), "assetUrl" => Ibos::app()->assetManager->getAssetsUrl("assignment"));

        foreach ($this->_indexTab as $tab) {
            $data["tab"] = $tab;
            $data[$tab] = Ibos::app()->getController()->renderPartial($viewAlias, $data, true);
        }

        return $data;
    }

    public function loadNew()
    {
        $uid = Ibos::app()->user->uid;
        return Assignment::model()->getUnfinishCountByUid($uid);
    }
}
