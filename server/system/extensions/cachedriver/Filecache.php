<?php

class Filecache extends CFileCache
{
	public $directoryLevel = 2;

	public function getValue($name)
	{
		$content = parent::getValue($name);

		if ($content) {
			if (function_exists("gzcompress")) {
				$content = gzuncompress($content);
			}

			$content = unserialize($content);
		}

		return $content;
	}

	public function setValue($key, $value, $expire = 0)
	{
		$data = serialize($value);

		if (function_exists("gzcompress")) {
			$data = gzcompress($data, 3);
		}

		return parent::setValue($key, $data, $expire);
	}

	public function getMulti($keys)
	{
		return $this->getValues($keys);
	}

	public function flushValues()
	{
		parent::flushValues();
	}
}
