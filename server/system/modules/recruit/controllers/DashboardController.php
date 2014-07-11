<?php

class RecruitDashboardController extends DashboardBaseController
{
    public function getAssetUrl($module = "")
    {
        $module = "dashboard";
        return Yii::app()->assetManager->getAssetsUrl($module);
    }

    public function actionIndex()
    {
        $config = Yii::app()->setting->get("setting/recruitconfig");
        $result = array();
        $allFieldRuleType = Regular::model()->fetchAllFieldRuleType();

        foreach ($config as $configName => $configValue) {
            list($visi, $fieldRule) = explode(",", $configValue);
            $result[$configName]["visi"] = $visi;
            $result[$configName]["fieldrule"] = $fieldRule;

            if (in_array($fieldRule, $allFieldRuleType)) {
                $regular = Regular::model()->fetchFieldRuleByType($fieldRule);
            } elseif ($fieldRule == "notrequirement") {
                $regular["type"] = "notrequirement";
                $regular["desc"] = Ibos::lang("Not requirement");
            } else {
                $regular["type"] = $regular["desc"] = $fieldRule;
            }

            $result[$configName]["regulartype"] = $regular["type"];
            $result[$configName]["regulardesc"] = $regular["desc"];
        }

        $notRequirementRegulars = array(
            array("type" => "notrequirement", "desc" => Ibos::lang("Not requirement"))
        );
        $sysRegulars = Regular::model()->fetchAll();
        $result["regular"] = array_merge($notRequirementRegulars, $sysRegulars);
        $this->render("index", array("config" => $result));
    }

    public function actionUpdate()
    {
        $fieldArr = array("recruitrealname" => "recruitrealname", "recruitsex" => "recruitsex", "recruitbirthday" => "recruitbirthday", "recruitbirthplace" => "recruitbirthplace", "recruitworkyears" => "recruitworkyears", "recruiteducation" => "recruiteducation", "recruitstatus" => "recruitstatus", "recruitidcard" => "recruitidcard", "recruitheight" => "recruitheight", "recruitweight" => "recruitweight", "recruitmaritalstatus" => "recruitmaritalstatus", "recruitresidecity" => "recruitresidecity", "recruitzipcode" => "recruitzipcode", "recruitmobile" => "recruitmobile", "rucruitemail" => "rucruitemail", "recruittelephone" => "recruittelephone", "recruitqq" => "recruitqq", "recruitmsn" => "recruitmsn", "recruitbeginworkday" => "recruitbeginworkday", "recruittargetposition" => "recruittargetposition", "recruitexpectsalary" => "recruitexpectsalary", "recruitworkplace" => "recruitworkplace", "recruitrecchannel" => "recruitrecchannel", "recruitworkexperience" => "recruitworkexperience", "recruitprojectexperience" => "recruitprojectexperience", "recruiteduexperience" => "recruiteduexperience", "recruitlangskill" => "recruitlangskill", "recruitcomputerskill" => "recruitcomputerskill", "recruitprofessionskill" => "recruitprofessionskill", "recruittrainexperience" => "recruittrainexperience", "recruitselfevaluation" => "recruitselfevaluation", "recruitrelevantcertificates" => "recruitrelevantcertificates", "recruitsocialpractice" => "recruitsocialpractice");
        $data = array();

        foreach ($_POST as $key => $value) {
            if (in_array($key, $fieldArr)) {
                $data[$key] = $value;
                unset($fieldArr[$key]);
            }

            $data[$key]["visi"] = (isset($data[$key]["visi"]) ? $data[$key]["visi"] : 0);
            $data[$key]["fieldrule"] = (isset($data[$key]["fieldrule"]) ? $data[$key]["fieldrule"] : "notrequirement");

            if ($data[$key]["fieldrule"] == "") {
                $data[$key]["fieldrule"] = "notrequirement";
            }

            $data[$key] = $data[$key]["visi"] . "," . $data[$key]["fieldrule"];
        }

        foreach ($fieldArr as $field) {
            $data[$field] = "0,notrequirement";
        }

        Setting::model()->updateSettingValueByKey("recruitconfig", $data);
        CacheUtil::update("setting");
        $this->success(Ibos::lang("Update succeed", "message"), $this->createUrl("dashboard/index"));
    }
}
