<?php

class IpbannedCacheProvider extends CBehavior
{
	public function attach($owner)
	{
		$owner->attachEventHandler("onUpdateCache", array($this, "handleIpbanned"));
	}

	public function handleIpbanned($event)
	{
		IpBanned::model()->DeleteByExpiration(TIMESTAMP);
		$data = array();
		$bannedArr = IpBanned::model()->fetchAll();

		if (!empty($bannedArr)) {
			$data["expiration"] = 0;
			$data["regexp"] = $separator = "";
		}

		foreach ($bannedArr as $banned ) {
			$data["expiration"] = (!$data["expiration"] || ($banned["expiration"] < $data["expiration"]) ? $banned["expiration"] : $data["expiration"]);
			$data["regexp"] .= $separator . ($banned["ip1"] == "-1" ? "\d+\." : $banned["ip1"] . "\.") . ($banned["ip2"] == "-1" ? "\d+\." : $banned["ip2"] . "\.") . ($banned["ip3"] == "-1" ? "\d+\." : $banned["ip3"] . "\.") . ($banned["ip4"] == "-1" ? "\d+" : $banned["ip4"]);
			$separator = "|";
		}

		Syscache::model()->modify("ipbanned", $data);
	}
}


?>
