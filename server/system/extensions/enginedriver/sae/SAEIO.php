<?php

class SAEIO extends IO
{
	public function upload($attach, $module)
	{
		return new SAEUpload($attach, $module);
	}

	public function file()
	{
		return $this->getObject("SAEFile");
	}
}


?>
