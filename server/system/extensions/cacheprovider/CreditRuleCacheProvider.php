<?php

class CreditRuleCacheProvider extends CBehavior
{
	public function attach($owner)
	{
		$owner->attachEventHandler("onUpdateCache", array($this, "handleCreditRule"));
	}

	public function handleCreditRule($event)
	{
		$rules = array();
		$records = CreditRule::model()->fetchAll();

		if (!empty($records)) {
			foreach ($records as $rule ) {
				$rule["rulenameuni"] = urlencode(ConvertUtil::iIconv($rule["rulename"], CHARSET, "UTF-8", true));
				$rules[$rule["action"]] = $rule;
			}
		}

		Syscache::model()->modify("creditrule", $rules);
	}
}


?>
