<?php

class UsersCacheProvider extends CBehavior
{
	public function attach($owner)
	{
		$owner->attachEventHandler("onUpdateCache", array($this, "handleUsers"));
	}

	public function handleUsers($event)
	{
		$users = array();
		$records = User::model()->fetchAll(array("condition" => "status IN (0,1)"));

		if (!empty($records)) {
			foreach ($records as $record ) {
				$users[$record["uid"]] = UserUtil::wrapUserInfo($record);
			}
		}

		Syscache::model()->modify("users", $users);
	}
}


?>
