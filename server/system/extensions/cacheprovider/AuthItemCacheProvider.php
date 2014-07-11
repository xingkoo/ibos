<?php

class AuthItemCacheProvider extends CBehavior
{
	public function attach($owner)
	{
		$owner->attachEventHandler("onUpdateCache", array($this, "handleAuthItem"));
	}

	public function handleAuthItem($event)
	{
		$categorys = array();
		$nodes = Node::model()->fetchAllEmptyNode();

		foreach ($nodes as $node ) {
			if (empty($node["category"])) {
				continue;
			}

			$category = base64_encode($node["category"]);
			$categorys[$category]["category"] = $node["category"];
			if (($node["type"] == "data") && empty($node["node"])) {
				$node["node"] = Node::model()->fetchAllNotEmptyNodeByModuleKey($node["module"], $node["key"]);
			}

			if (!empty($node["group"])) {
				$group = base64_encode($node["group"]);
				$categorys[$category]["group"][$group]["groupName"] = $node["group"];
				$categorys[$category]["group"][$group]["node"][] = $node;
			}
			else {
				$categorys[$category]["node"][] = $node;
			}
		}

		Syscache::model()->modify("authitem", $categorys);
	}
}


?>
