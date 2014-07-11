<?php

class Wincache extends CWinCache
{
	public function getMulti($keys)
	{
		return $this->getValues($keys);
	}
}
