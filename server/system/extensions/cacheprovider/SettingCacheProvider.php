<?php

class SettingCacheProvider extends CBehavior
{
	private $_setting = array();

	public function attach($owner)
	{
		$owner->attachEventHandler("onUpdateCache", array($this, "handleSetting"));
	}

	public function handleSetting($event)
	{
		$settings = Setting::model()->fetchAllSetting();
		$this->_setting = &$settings;
		$this->handleCredits();
		$this->handleCreditsFormula();
		$this->_setting["verhash"] = StringUtil::random(3);
		Syscache::model()->modify("setting", $settings);
	}

	private function handleCreditsFormula()
	{
		if (!DashboardUtil::checkFormulaCredits($this->_setting["creditsformula"])) {
			$this->_setting["creditsformula"] = "\$user['extcredits1']";
		}
		else {
			$this->_setting["creditsformula"] = preg_replace("/(extcredits[1-5])/", "\$user['\1']", $this->_setting["creditsformula"]);
		}
	}

	private function handleCredits()
	{
		$criteria = array("condition" => "`enable` = 1", "order" => "`cid` ASC", "limit" => 5);
		$record = Credit::model()->fetchAll($criteria);

		if (!empty($record)) {
			$index = 1;

			foreach ($record as $credit ) {
				$this->_setting["extcredits"][$index] = $credit;
				$this->_setting["creditremind"] && ($this->_setting["creditnames"][] = str_replace("'", "\'", StringUtil::ihtmlSpecialChars($credit["cid"] . "|" . $credit["name"])));
				$index++;
			}
		}

		$this->_setting["creditnames"] = ($this->_setting["creditremind"] ? @implode(",", $this->_setting["creditnames"]) : "");
	}
}


?>
