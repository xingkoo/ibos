<?php

class SAECache extends CMemCache
{
	/**
	 * 缓存组件是否可用
	 * @var boolean
	 */
	public $enable = false;
	/**
	 * 缓存组件的类型
	 * @var string
	 */
	public $type;

	public function init()
	{
		parent::init();
		$cache = $this->getMemCache();

		if ($cache) {
			$this->enable = true;
			$this->type = "memcache";
		}
	}

	public function load($config)
	{
		return true;
	}

	public function rm($key, $prefix = "")
	{
		return $this->deleteValue($key, $prefix);
	}

	public function clear()
	{
		return $this->flushValues();
	}
}


?>
