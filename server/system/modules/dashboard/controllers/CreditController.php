<?php

class DashboardCreditController extends DashboardBaseController
{
    const MAX_RULE_ID = 5;

    public function actionSetup()
    {
        $operation = EnvUtil::getRequest("op");
        if (!empty($operation) && in_array($operation, array("add", "del"))) {
            $method = $operation . "Credit";

            if (method_exists($this, $method)) {
                return $this->{$method}();
            }
        }

        $formSubmit = EnvUtil::submitCheck("creditSetupSubmit");

        if ($formSubmit) {
            $changeRemind = (isset($_POST["changeRemind"]) ? 1 : 0);
            Setting::model()->updateSettingValueByKey("creditremind", $changeRemind);
            $credits = $_POST["credit"];

            foreach ($credits as $cid => $credit) {
                if (isset($credit["enable"])) {
                    $credit["enable"] = 1;
                } else {
                    $credit["enable"] = 0;
                }

                Credit::model()->modify($cid, $credit);
            }

            CacheUtil::update(array("setting"));
            $this->success(Ibos::lang("Save succeed", "message"));
        } else {
            $credits = Credit::model()->fetchAll();
            $data = array("data" => $credits, "curentMaxId" => Credit::model()->getMaxId("cid"), "maxId" => self::MAX_RULE_ID);
            $this->render("setup", $data);
        }
    }

    public function actionFormula()
    {
        $formSubmit = EnvUtil::submitCheck("creditSetupSubmit");

        if ($formSubmit) {
            $formula = $_POST["creditsFormula"];
            $formulaCheckCorrect = DashboardUtil::checkFormulaCredits($formula);

            if ($formulaCheckCorrect) {
                Setting::model()->updateSettingValueByKey("creditsformula", $formula);
            } else {
                $this->error(Ibos::lang("Credits formula invalid"));
            }

            $formulaExp = $_POST["creditsFormulaExp"];
            Setting::model()->updateSettingValueByKey("creditsformulaexp", $formulaExp);
            CacheUtil::update(array("setting"));
            $this->success(Ibos::lang("Save succeed", "message"));
        } else {
            $credits = Credit::model()->fetchAll();
            $data = array("data" => $credits, "creditsFormula" => Setting::model()->fetchSettingValueByKey("creditsformula"), "creditFormulaExp" => Setting::model()->fetchSettingValueByKey("creditsformulaexp"));
            $this->render("formula", $data);
        }
    }

    public function actionRule()
    {
        $formSubmit = EnvUtil::submitCheck("creditRuleSubmit");

        if ($formSubmit) {
            $cycles = $_POST["cycles"];
            $credits = $_POST["credits"];
            $rewardNums = $_POST["rewardnums"];
            $rulesParam = array();

            foreach ($cycles as $ruleId => $cycle) {
                $rulesParam[$ruleId]["cycletype"] = $cycle;
            }

            foreach ($credits as $ruleId => $credit) {
                foreach ($credit as $extcreditOffset => $creditValue) {
                    $rulesParam[$ruleId]["extcredits" . $extcreditOffset] = $creditValue;
                }
            }

            foreach ($rewardNums as $ruleId => $rewardNum) {
                $rulesParam[$ruleId]["rewardnum"] = $rewardNum;
            }

            foreach ($rulesParam as $ruleId => $updateValue) {
                CreditRule::model()->modify($ruleId, $updateValue);
            }

            CacheUtil::update(array("creditRule"));
            $this->success(Ibos::lang("Save succeed", "message"));
        } else {
            $rules = CreditRule::model()->fetchAll();
            $credits = Credit::model()->fetchAll();
            $data = array("rules" => $rules, "credits" => $credits);
            $this->render("rule", $data);
        }
    }

    private function addCredit()
    {
        if (Ibos::app()->getRequest()->getIsAjaxRequest()) {
            $attributes = array("name" => EnvUtil::getRequest("name"), "initial" => EnvUtil::getRequest("initial"), "lower" => EnvUtil::getRequest("lower"), "enable" => EnvUtil::getRequest("enable"));
            $newId = Credit::model()->add($attributes, true);

            if ($newId) {
                $return = array("id" => $newId, "IsSuccess" => true);
                $this->ajaxReturn($return);
            }
        }
    }

    private function delCredit()
    {
        if (Ibos::app()->getRequest()->getIsAjaxRequest()) {
            $id = EnvUtil::getRequest("id");
            $affected = Credit::model()->deleteByPk($id);

            if ($affected) {
                $this->ajaxReturn(array("IsSuccess" => true));
            }
        }
    }
}
