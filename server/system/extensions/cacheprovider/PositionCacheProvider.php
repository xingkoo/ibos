<?php

class PositionCacheProvider extends CBehavior
{
	public function attach($owner)
	{
		$owner->attachEventHandler("onUpdateCache", array($this, "handlePosition"));
	}

	public function handlePosition($event)
	{
		$records = Position::model()->fetchAllSortByPk("positionid");
		Syscache::model()->modify("position", $records);
	}
}


?>
