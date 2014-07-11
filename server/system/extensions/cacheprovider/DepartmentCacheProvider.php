<?php

class DepartmentCacheProvider extends CBehavior
{
	public function attach($owner)
	{
		$owner->attachEventHandler("onUpdateCache", array($this, "handleDepartment"));
	}

	public function handleDepartment($event)
	{
		$departments = array();
		$records = Department::model()->findAll(array("order" => "sort ASC"));

		if (!empty($records)) {
			foreach ($records as $record ) {
				$dept = $record->attributes;
				$departments[$dept["deptid"]] = $dept;
			}
		}

		Syscache::model()->modify("department", $departments);
	}
}


?>
