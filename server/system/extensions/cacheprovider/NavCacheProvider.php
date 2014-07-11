<?php

class NavCacheProvider extends CBehavior
{
	public function attach($owner)
	{
		$owner->attachEventHandler("onUpdateCache", array($this, "handleNav"));
	}

	public function handleNav($event)
	{
		$navs = Nav::model()->fetchAllByAllPid();
		Syscache::model()->modify("nav", $navs);
	}
}


?>
