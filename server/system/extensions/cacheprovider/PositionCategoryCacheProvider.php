<?php

class PositionCategoryCacheProvider extends CBehavior
{
	public function attach($owner)
	{
		$owner->attachEventHandler("onUpdateCache", array($this, "handlePositionCategory"));
	}

	public function handlePositionCategory($event)
	{
		$categorys = array();
		$records = PositionCategory::model()->findAll(array("order" => "sort ASC"));

		if (!empty($records)) {
			foreach ($records as $record ) {
				$cat = $record->attributes;
				$categorys[$cat["catid"]] = $cat;
			}
		}

		Syscache::model()->modify("positioncategory", $categorys);
	}
}


?>
