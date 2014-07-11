<?php

class UserGroupCacheProvider extends CBehavior
{
	public function attach($owner)
	{
		$owner->attachEventHandler("onUpdateCache", array($this, "handleUserGroup"));
	}

	public function handleUserGroup($event)
	{
		$usergroup = array();
		$records = UserGroup::model()->findAll(array("order" => "creditslower ASC"));

		if (!empty($records)) {
			foreach ($records as $record ) {
				$group = $record->attributes;
				$usergroup[$group["gid"]] = $group;
			}
		}

		Syscache::model()->modify("usergroup", $usergroup);
	}
}


?>
