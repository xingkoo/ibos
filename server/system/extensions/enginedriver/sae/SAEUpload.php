<?php

class SAEUpload extends ICUpload
{
	public function save()
	{
		if ($this->getError() == 0) {
			$storage = new SAEFile();
			$attach = $this->getAttach();
			$arr = array("type" => $attach["type"]);
			$rs = $storage->uploadFile($attach["target"], $attach["tmp_name"], $arr);
			return $rs;
		}
		else {
			return false;
		}
	}
}


?>
