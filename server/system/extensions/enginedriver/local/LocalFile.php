<?php

class LocalFile
{
	/**
	 * 扫描文件夹时忽略的文件夹
	 * @var array 
	 */
	public $excludeFiles = array(".svn", ".gitignore", ".", "..");

	public function fileExists($file)
	{
		return file_exists($file);
	}

	public function createFile($fileName, $content)
	{
		return file_put_contents($fileName, $content);
	}

	public function deleteFile($fileName)
	{
		return @unlink($fileName);
	}

	public function fileName($fileName)
	{
		return sprintf("%s", $fileName);
	}

	public function readFile($fileName)
	{
		return file_get_contents($fileName);
	}

	public function fileSize($file)
	{
		return sprintf("%u", filesize($file));
	}

	public function imageSize($image)
	{
		return getimagesize($image);
	}

	public function getTempPath()
	{
		return sprintf("%s", "data/temp");
	}

	public function clearDir($dir)
	{
		$directory = @dir($dir);

		if (is_object($directory)) {
			while ($entry = $directory->read()) {
				$file = $dir . DIRECTORY_SEPARATOR . $entry;

				if (is_file($file)) {
					@unlink($file);
				}
			}

			$directory->close();
			@touch($dir . "/index.htm");
		}
	}

	public function clearDirs($srcDir)
	{
		$dir = @opendir($srcDir);

		while ($entry = @readdir($dir)) {
			$file = $srcDir . DIRECTORY_SEPARATOR . $entry;

			if (!in_array($entry, $this->excludeFiles)) {
				if (is_dir($file)) {
					$this->clearDirs($file . DIRECTORY_SEPARATOR);
				}
				else {
					@unlink($file);
				}
			}
		}

		closedir($dir);
		rmdir($srcDir);
	}

	public function download($attach, $downloadInfo = array())
	{
		$file = FileUtil::getAttachUrl() . "/" . $attach["attachment"];

		if (file_exists($file)) {
			if ((Ibos::app()->browser->name == "msie") || (Ibos::app()->browser->getVersion() == "10.0") || (Ibos::app()->browser->getVersion() == "11.0")) {
				$usingIe = true;
			}
			else {
				$usingIe = false;
			}

			$typeArr = array("1" => "application/octet-stream", "3" => "application/msword", "4" => "application/msexcel", "5" => "application/mspowerpoint", "7" => "application/octet-stream", "8" => "application/x-shockwave-flash", "10" => "application/pdf");
			$attachType = AttachUtil::Attachtype(StringUtil::getFileExt($attach["filename"]), "id");
			$content = false;

			if (isset($downloadInfo["directView"])) {
				if (!in_array($attachType, array("1", "7", "8", "10"))) {
					$content = true;
				}

				$contentType = $typeArr[$attachType];
			}
			else {
				if (in_array($attachType, array("3", "4", "5")) && $usingIe) {
					$contentType = $typeArr[$attachType];
				}
				else {
					$content = 1;
					$contentType = "application/octet-stream";
				}
			}

			ob_end_clean();
			header("Cache-control: private");
			header("Content-type: $contentType");
			header("Accept-Ranges: bytes");
			header("Content-Length: " . sprintf("%u", $this->fileSize($file)));

			if ($usingIe) {
				$attach["filename"] = urlencode($attach["filename"]);
			}

			if ($content) {
				header("Content-Disposition: attachment; filename=\"" . $attach["filename"] . "\"");
			}
			else {
				header("Content-Disposition: filename=\"" . $attach["filename"] . "\"");
			}

			Attachment::model()->updateDownload($attach["aid"]);
			readfile($file);
			exit();
		}
	}
}


?>
