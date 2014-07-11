<?php

class LocalIO extends IO
{
	public function upload($fileArea, $module)
	{
		return new LocalUpload($fileArea, $module);
	}

	public function file()
	{
		return $this->getObject("LocalFile");
	}
}


?>
