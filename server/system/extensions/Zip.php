<?php

class Zip
{
	public $datasec = array();
	public $ctrl_dir = array();
	public $eof_ctrl_dir = "PK\005\006\000\000\000\000";
	public $old_offset = 0;

	public function unix2DosTime($unixtime = 0)
	{
		$timearray = ($unixtime == 0 ? getdate() : getdate($unixtime));

		if ($timearray["year"] < 1980) {
			$timearray["year"] = 1980;
			$timearray["mon"] = 1;
			$timearray["mday"] = 1;
			$timearray["hours"] = 0;
			$timearray["minutes"] = 0;
			$timearray["seconds"] = 0;
		}

		return (($timearray["year"] - 1980) << 25) | ($timearray["mon"] << 21) | ($timearray["mday"] << 16) | ($timearray["hours"] << 11) | ($timearray["minutes"] << 5) | ($timearray["seconds"] >> 1);
	}

	public function addFile($data, $name, $time = 0)
	{
		$name = str_replace("\\", "/", $name);
		$dtime = dechex($this->unix2DosTime($time));
		$hexdtime = "\\x" . $dtime[6] . $dtime[7] . "\\x" . $dtime[4] . $dtime[5] . "\\x" . $dtime[2] . $dtime[3] . "\\x" . $dtime[0] . $dtime[1];
		eval ("\$hexdtime = \"" . $hexdtime . "\";");
		$fr = "PK\003\004";
		$fr .= "\024\000";
		$fr .= "\000\000";
		$fr .= "\010\000";
		$fr .= $hexdtime;
		$unc_len = strlen($data);
		$crc = crc32($data);
		$zdata = gzcompress($data);
		$zdata = substr(substr($zdata, 0, strlen($zdata) - 4), 2);
		$c_len = strlen($zdata);
		$fr .= pack("V", $crc);
		$fr .= pack("V", $c_len);
		$fr .= pack("V", $unc_len);
		$fr .= pack("v", strlen($name));
		$fr .= pack("v", 0);
		$fr .= $name;
		$fr .= $zdata;
		$this->datasec[] = $fr;
		$cdrec = "PK\001\002";
		$cdrec .= "\000\000";
		$cdrec .= "\024\000";
		$cdrec .= "\000\000";
		$cdrec .= "\010\000";
		$cdrec .= $hexdtime;
		$cdrec .= pack("V", $crc);
		$cdrec .= pack("V", $c_len);
		$cdrec .= pack("V", $unc_len);
		$cdrec .= pack("v", strlen($name));
		$cdrec .= pack("v", 0);
		$cdrec .= pack("v", 0);
		$cdrec .= pack("v", 0);
		$cdrec .= pack("v", 0);
		$cdrec .= pack("V", 32);
		$cdrec .= pack("V", $this->old_offset);
		$this->old_offset += strlen($fr);
		$cdrec .= $name;
		$this->ctrl_dir[] = $cdrec;
	}

	public function file()
	{
		$data = implode("", $this->datasec);
		$ctrldir = implode("", $this->ctrl_dir);
		return $data . $ctrldir . $this->eof_ctrl_dir . pack("v", sizeof($this->ctrl_dir)) . pack("v", sizeof($this->ctrl_dir)) . pack("V", strlen($ctrldir)) . pack("V", strlen($data)) . "\000\000";
	}
}

class SimpleUnzip
{
	public $Comment = "";
	public $Entries = array();
	public $Name = "";
	public $Size = 0;
	public $Time = 0;

	public function SimpleUnzip($in_FileName = "")
	{
		if ($in_FileName !== "") {
			SimpleUnzip::ReadFile($in_FileName);
		}
	}

	public function Count()
	{
		return count($this->Entries);
	}

	public function GetData($in_Index)
	{
		return $this->Entries[$in_Index]->Data;
	}

	public function GetEntry($in_Index)
	{
		return $this->Entries[$in_Index];
	}

	public function GetError($in_Index)
	{
		return $this->Entries[$in_Index]->Error;
	}

	public function GetErrorMsg($in_Index)
	{
		return $this->Entries[$in_Index]->ErrorMsg;
	}

	public function GetName($in_Index)
	{
		return $this->Entries[$in_Index]->Name;
	}

	public function GetPath($in_Index)
	{
		return $this->Entries[$in_Index]->Path;
	}

	public function GetTime($in_Index)
	{
		return $this->Entries[$in_Index]->Time;
	}

	public function ReadFile($in_FileName)
	{
		$this->Entries = array();
		$this->Name = $in_FileName;
		$this->Time = filemtime($in_FileName);
		$this->Size = filesize($in_FileName);
		$oF = fopen($in_FileName, "rb");
		$vZ = fread($oF, $this->Size);
		fclose($oF);
		$aE = explode("PK\005\006", $vZ);
		$aP = unpack("x16/v1CL", $aE[1]);
		$this->Comment = substr($aE[1], 18, $aP["CL"]);
		$this->Comment = strtr($this->Comment, array("\r\n" => "\n", "\r" => "\n"));
		$aE = explode("PK\001\002", $vZ);
		$aE = explode("PK\003\004", $aE[0]);
		array_shift($aE);

		foreach ($aE as $vZ ) {
			$aI = array();
			$aI["E"] = 0;
			$aI["EM"] = "";
			$aP = unpack("v1VN/v1GPF/v1CM/v1FT/v1FD/V1CRC/V1CS/V1UCS/v1FNL", $vZ);
			$bE = ($aP["GPF"] && 1 ? true : false);
			$nF = $aP["FNL"];

			if ($aP["GPF"] & 8) {
				$aP1 = unpack("V1CRC/V1CS/V1UCS", substr($vZ, -12));
				$aP["CRC"] = $aP1["CRC"];
				$aP["CS"] = $aP1["CS"];
				$aP["UCS"] = $aP1["UCS"];
				$vZ = substr($vZ, 0, -12);
			}

			$aI["N"] = substr($vZ, 26, $nF);

			if (substr($aI["N"], -1) == "/") {
				continue;
			}

			$aI["P"] = dirname($aI["N"]);
			$aI["P"] = ($aI["P"] == "." ? "" : $aI["P"]);
			$aI["N"] = basename($aI["N"]);
			$vZ = substr($vZ, 26 + $nF);

			if (strlen($vZ) != $aP["CS"]) {
				$aI["E"] = 1;
				$aI["EM"] = "Compressed size is not equal with the value in header information.";
			}
			else if ($bE) {
				$aI["E"] = 5;
				$aI["EM"] = "File is encrypted, which is not supported from this class.";
			}
			else {
				switch ($aP["CM"]) {
				case 0:
					break;

				case 8:
					$vZ = gzinflate($vZ);
					break;

				case 12:
					if (!extension_loaded("bz2")) {
						if (strtoupper(substr(PHP_OS, 0, 3)) == "WIN") {
							@dl("php_bz2.dll");
						}
						else {
							@dl("bz2.so");
						}
					}

					if (extension_loaded("bz2")) {
						$vZ = bzdecompress($vZ);
					}
					else {
						$aI["E"] = 7;
						$aI["EM"] = "PHP BZIP2 extension not available.";
					}

					break;

				default:
					$aI["E"] = 6;
					$aI["EM"] = "De-/Compression method {$aP["CM"]} is not supported.";
				}

				if (!$aI["E"]) {
					if ($vZ === false) {
						$aI["E"] = 2;
						$aI["EM"] = "Decompression of data failed.";
					}
					else if (strlen($vZ) != $aP["UCS"]) {
						$aI["E"] = 3;
						$aI["EM"] = "Uncompressed size is not equal with the value in header information.";
					}
					else if (crc32($vZ) != $aP["CRC"]) {
						$aI["E"] = 4;
						$aI["EM"] = "CRC32 checksum is not equal with the value in header information.";
					}
				}
			}

			$aI["D"] = $vZ;
			$aI["T"] = mktime(($aP["FT"] & 63488) >> 11, ($aP["FT"] & 2016) >> 5, ($aP["FT"] & 31) << 1, ($aP["FD"] & 480) >> 5, $aP["FD"] & 31, (($aP["FD"] & 65024) >> 9) + 1980);
			$this->Entries[] = new SimpleUnzipEntry($aI);
		}

		return $this->Entries;
	}
}

class SimpleUnzipEntry
{
	public $Data = "";
	public $Error = 0;
	public $ErrorMsg = "";
	public $Name = "";
	public $Path = "";
	public $Time = 0;

	public function SimpleUnzipEntry($in_Entry)
	{
		$this->Data = $in_Entry["D"];
		$this->Error = $in_Entry["E"];
		$this->ErrorMsg = $in_Entry["EM"];
		$this->Name = $in_Entry["N"];
		$this->Path = $in_Entry["P"];
		$this->Time = $in_Entry["T"];
	}
}
