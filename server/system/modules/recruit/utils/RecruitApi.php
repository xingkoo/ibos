<?php

class RecruitApi
{
    private $_indexTab = array("TalentManagement", "InterviewManagement");

    public function renderIndex()
    {
        $data = array("lant" => Ibos::getLangSource("recruit.default"), "assetUrl" => Yii::app()->assetManager->getAssetUrl("recruit"));

        foreach ($this->_indexTab as $tab) {
            $data["recruits"] = $this->loadRecruit($tab);
            $data["tab"] = $tab;
        }

        $viewAlias = "application.modules.recruit.views.indexapi.recruit";
        $return["recruit"] = Yii::app()->getController()->renderPartial($viewAlias, $data, true);
        return $return;
    }

    public function loadSetting()
    {
        return array("name" => "recruit", "title" => Ibos::lang("Recruitment management", "recruit.default"), "style" => "in-recruit");
    }

    private function loadRecruit($type = "TalentManagement", $num = 4)
    {
        $uid = Yii::app()->user->uid;

        switch ($type) {
            case "TalentManagement":
                $status = 4;
                break;

            case "InterviewManagement":
                $status = 1;
                break;

            default:
                return false;
        }

        $criteria = array("select" => "resumeid,input,suitableposition,uptime,status", "condition" => "`status`=$status", "order" => "`uptime` DESC", "offset" => 0, "limit" => $num);
        $resume = Resume::model()->findAll($criteria);
        var_dump($resume);
        exit();
    }
}
