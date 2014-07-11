<?php

class NotifyNodeCacheProvider extends CBehavior
{
	public function attach($owner)
	{
		$owner->attachEventHandler("onUpdateCache", array($this, "handleNode"));
	}

	public function handleNode($event)
	{
		CacheUtil::set("notifyNode", NULL);
		Notify::model()->getNodeList();
	}
}


?>
