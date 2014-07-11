<?php

class HproseResultMode
{
	const Normal = 0;
	const Serialized = 1;
	const Raw = 2;
	const RawWithEndTag = 3;
}

interface HproseFilter
{
	public function inputFilter($data);

	public function outputFilter($data);
}

class HproseDate
{
	public $year;
	public $month;
	public $day;
	public $utc = false;

	public function __construct()
	{
		$args_num = func_num_args();
		$args = func_get_args();

		switch ($args_num) {
		case 0:
			$time = getdate();
			$this->year = $time["year"];
			$this->month = $time["mon"];
			$this->day = $time["mday"];
			break;

		case 1:
			$time = false;

			if (is_int($args[0])) {
				$time = getdate($args[0]);
			}
			else if (is_string($args[0])) {
				$time = getdate(strtotime($args[0]));
			}

			if (is_array($time)) {
				$this->year = $time["year"];
				$this->month = $time["mon"];
				$this->day = $time["mday"];
			}
			else if ($args[0] instanceof HproseDate) {
				$this->year = $args[0]->year;
				$this->month = $args[0]->month;
				$this->day = $args[0]->day;
			}
			else {
				throw new HproseException("Unexpected arguments");
			}

			break;

		case 4:
			$this->utc = $args[3];
		case 3:
			if (!self::isValidDate($args[0], $args[1], $args[2])) {
				throw new HproseException("Unexpected arguments");
			}

			$this->year = $args[0];
			$this->month = $args[1];
			$this->day = $args[2];
			break;

		default:
			throw new HproseException("Unexpected arguments");
		}
	}

	public function addDays($days)
	{
		if (!is_int($days)) {
			return false;
		}

		$year = $this->year;

		if ($days == 0) {
			return true;
		}

		if ((146097 <= $days) || ($days <= -146097)) {
			$remainder = $days % 146097;

			if ($remainder < 0) {
				$remainder += 146097;
			}

			$years = 400 * (int) ($days - $remainder) / 146097;
			$year += $years;
			if (($year < 1) || (9999 < $year)) {
				return false;
			}

			$days = $remainder;
		}

		if ((36524 <= $days) || ($days <= -36524)) {
			$remainder = $days % 36524;

			if ($remainder < 0) {
				$remainder += 36524;
			}

			$years = 100 * (int) ($days - $remainder) / 36524;
			$year += $years;
			if (($year < 1) || (9999 < $year)) {
				return false;
			}

			$days = $remainder;
		}

		if ((1461 <= $days) || ($days <= -1461)) {
			$remainder = $days % 1461;

			if ($remainder < 0) {
				$remainder += 1461;
			}

			$years = 4 * (int) ($days - $remainder) / 1461;
			$year += $years;
			if (($year < 1) || (9999 < $year)) {
				return false;
			}

			$days = $remainder;
		}

		$month = $this->month;

		while (365 <= $days) {
			if (9999 <= $year) {
				return false;
			}

			if ($month <= 2) {
				if (($year % 4) == 0 ? (($year % 100) == 0 ? ($year % 400) == 0 : true) : false) {
					$days -= 366;
				}
				else {
					$days -= 365;
				}

				$year++;
			}
			else {
				$year++;
				if (($year % 4) == 0 ? (($year % 100) == 0 ? ($year % 400) == 0 : true) : false) {
					$days -= 366;
				}
				else {
					$days -= 365;
				}
			}
		}

		while ($days < 0) {
			if ($year <= 1) {
				return false;
			}

			if ($month <= 2) {
				$year--;
				if (($year % 4) == 0 ? (($year % 100) == 0 ? ($year % 400) == 0 : true) : false) {
					$days += 366;
				}
				else {
					$days += 365;
				}
			}
			else {
				if (($year % 4) == 0 ? (($year % 100) == 0 ? ($year % 400) == 0 : true) : false) {
					$days += 366;
				}
				else {
					$days += 365;
				}

				$year--;
			}
		}

		$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
		$day = $this->day;

		while ($daysInMonth < ($day + $days)) {
			$days -= ($daysInMonth - $day) + 1;
			$month++;

			if (12 < $month) {
				if (9999 <= $year) {
					return false;
				}

				$year++;
				$month = 1;
			}

			$day = 1;
			$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
		}

		$day += $days;
		$this->year = $year;
		$this->month = $month;
		$this->day = $day;
		return true;
	}

	public function addMonths($months)
	{
		if (!is_int($months)) {
			return false;
		}

		if ($months == 0) {
			return true;
		}

		$month = $this->month + $months;
		$months = (($month - 1) % 12) + 1;

		if ($months < 1) {
			$months += 12;
		}

		$years = (int) ($month - $months) / 12;

		if ($this->addYears($years)) {
			$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $months, $this->year);

			if ($daysInMonth < $this->day) {
				$months++;
				$this->day -= $daysInMonth;
			}

			$this->month = (int) $months;
			return true;
		}
		else {
			return false;
		}
	}

	public function addYears($years)
	{
		if (!is_int($years)) {
			return false;
		}

		if ($years == 0) {
			return true;
		}

		$year = $this->year + $years;
		if (($year < 1) || (9999 < $year)) {
			return false;
		}

		$this->year = $year;
		return true;
	}

	public function timestamp()
	{
		if ($this->utc) {
			return gmmktime(0, 0, 0, $this->month, $this->day, $this->year);
		}
		else {
			return mktime(0, 0, 0, $this->month, $this->day, $this->year);
		}
	}

	public function toString($fullformat = true)
	{
		$format = ($fullformat ? "%04d-%02d-%02d" : "%04d%02d%02d");
		$str = sprintf($format, $this->year, $this->month, $this->day);

		if ($this->utc) {
			$str .= "Z";
		}

		return $str;
	}

	public function __toString()
	{
		return $this->toString();
	}

	static public function isLeapYear($year)
	{
		return ($year % 4) == 0 ? (($year % 100) == 0 ? ($year % 400) == 0 : true) : false;
	}

	static public function daysInMonth($year, $month)
	{
		if (($month < 1) || (12 < $month)) {
			return false;
		}

		return cal_days_in_month(CAL_GREGORIAN, $month, $year);
	}

	static public function isValidDate($year, $month, $day)
	{
		if ((1 <= $year) && ($year <= 9999)) {
			return checkdate($month, $day, $year);
		}

		return false;
	}

	public function dayOfWeek()
	{
		$num = func_num_args();

		if ($num == 3) {
			$args = func_get_args();
			$y = $args[0];
			$m = $args[1];
			$d = $args[2];
		}
		else {
			$y = $this->year;
			$m = $this->month;
			$d = $this->day;
		}

		$d += ($m < 3 ? $y-- : $y - 2);
		return ((((int) (23 * $m) / 9 + $d + 4 + (int) $y / 4) - (int) $y / 100) + (int) $y / 400) % 7;
	}

	public function dayOfYear()
	{
		static $daysToMonth365 = array(0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334, 365);
		static $daysToMonth366 = array(0, 31, 60, 91, 121, 152, 182, 213, 244, 274, 305, 335, 366);
		$num = func_num_args();

		if ($num == 3) {
			$args = func_get_args();
			$y = $args[0];
			$m = $args[1];
			$d = $args[2];
		}
		else {
			$y = $this->year;
			$m = $this->month;
			$d = $this->day;
		}

		$days = (self::isLeapYear($y) ? $daysToMonth365 : $daysToMonth366);
		return $days[$m - 1] + $d;
	}
}

class HproseTime
{
	public $hour;
	public $minute;
	public $second;
	public $microsecond = 0;
	public $utc = false;

	public function __construct()
	{
		$args_num = func_num_args();
		$args = func_get_args();

		switch ($args_num) {
		case 0:
			$time = getdate();
			$timeofday = gettimeofday();
			$this->hour = $time["hours"];
			$this->minute = $time["minutes"];
			$this->second = $time["seconds"];
			$this->microsecond = $timeofday["usec"];
			break;

		case 1:
			$time = false;

			if (is_int($args[0])) {
				$time = getdate($args[0]);
			}
			else if (is_string($args[0])) {
				$time = getdate(strtotime($args[0]));
			}

			if (is_array($time)) {
				$this->hour = $time["hours"];
				$this->minute = $time["minutes"];
				$this->second = $time["seconds"];
			}
			else if ($args[0] instanceof HproseTime) {
				$this->hour = $args[0]->hour;
				$this->minute = $args[0]->minute;
				$this->second = $args[0]->second;
				$this->microsecond = $args[0]->microsecond;
			}
			else {
				throw new HproseException("Unexpected arguments");
			}

			break;

		case 5:
			$this->utc = $args[4];
		case 4:
			if (($args[3] < 0) || (999999 < $args[3])) {
				throw new HproseException("Unexpected arguments");
			}

			$this->microsecond = $args[3];
		case 3:
			if (!self::isValidTime($args[0], $args[1], $args[2])) {
				throw new HproseException("Unexpected arguments");
			}

			$this->hour = $args[0];
			$this->minute = $args[1];
			$this->second = $args[2];
			break;

		default:
			throw new HproseException("Unexpected arguments");
		}
	}

	public function timestamp()
	{
		if ($this->utc) {
			return gmmktime($this->hour, $this->minute, $this->second) + ($this->microsecond / 1000000);
		}
		else {
			return mktime($this->hour, $this->minute, $this->second) + ($this->microsecond / 1000000);
		}
	}

	public function toString($fullformat = true)
	{
		if ($this->microsecond == 0) {
			$format = ($fullformat ? "%02d:%02d:%02d" : "%02d%02d%02d");
			$str = sprintf($format, $this->hour, $this->minute, $this->second);
		}

		if (($this->microsecond % 1000) == 0) {
			$format = ($fullformat ? "%02d:%02d:%02d.%03d" : "%02d%02d%02d.%03d");
			$str = sprintf($format, $this->hour, $this->minute, $this->second, (int) $this->microsecond / 1000);
		}
		else {
			$format = ($fullformat ? "%02d:%02d:%02d.%06d" : "%02d%02d%02d.%06d");
			$str = sprintf($format, $this->hour, $this->minute, $this->second, $this->microsecond);
		}

		if ($this->utc) {
			$str .= "Z";
		}

		return $str;
	}

	public function __toString()
	{
		return $this->toString();
	}

	static public function isValidTime($hour, $minute, $second, $microsecond = 0)
	{
		return !($hour < 0) || (23 < $hour) || ($minute < 0) || (59 < $minute) || ($second < 0) || (59 < $second) || ($microsecond < 0) || (999999 < $microsecond);
	}
}

class HproseDateTime extends HproseDate
{
	public $hour;
	public $minute;
	public $second;
	public $microsecond = 0;

	public function addMicroseconds($microseconds)
	{
		if (!is_int($microseconds)) {
			return false;
		}

		if ($microseconds == 0) {
			return true;
		}

		$microsecond = $this->microsecond + $microseconds;
		$microseconds = $microsecond % 1000000;

		if ($microseconds < 0) {
			$microseconds += 1000000;
		}

		$seconds = (int) ($microsecond - $microseconds) / 1000000;

		if ($this->addSeconds($seconds)) {
			$this->microsecond = (int) $microseconds;
			return true;
		}
		else {
			return false;
		}
	}

	public function addSeconds($seconds)
	{
		if (!is_int($seconds)) {
			return false;
		}

		if ($seconds == 0) {
			return true;
		}

		$second = $this->second + $seconds;
		$seconds = $second % 60;

		if ($seconds < 0) {
			$seconds += 60;
		}

		$minutes = (int) ($second - $seconds) / 60;

		if ($this->addMinutes($minutes)) {
			$this->second = (int) $seconds;
			return true;
		}
		else {
			return false;
		}
	}

	public function addMinutes($minutes)
	{
		if (!is_int($minutes)) {
			return false;
		}

		if ($minutes == 0) {
			return true;
		}

		$minute = $this->minute + $minutes;
		$minutes = $minute % 60;

		if ($minutes < 0) {
			$minutes += 60;
		}

		$hours = (int) ($minute - $minutes) / 60;

		if ($this->addHours($hours)) {
			$this->minute = (int) $minutes;
			return true;
		}
		else {
			return false;
		}
	}

	public function addHours($hours)
	{
		if (!is_int($hours)) {
			return false;
		}

		if ($hours == 0) {
			return true;
		}

		$hour = $this->hour + $hours;
		$hours = $hour % 24;

		if ($hours < 0) {
			$hours += 24;
		}

		$days = (int) ($hour - $hours) / 24;

		if ($this->addDays($days)) {
			$this->hour = (int) $hours;
			return true;
		}
		else {
			return false;
		}
	}

	public function after($when)
	{
		if (!$when instanceof HproseDateTime) {
			$when = new HproseDateTime($when);
		}

		if ($this->utc != $when->utc) {
			return $when->timestamp() < $this->timestamp();
		}

		if ($this->year < $when->year) {
			return false;
		}

		if ($when->year < $this->year) {
			return true;
		}

		if ($this->month < $when->month) {
			return false;
		}

		if ($when->month < $this->month) {
			return true;
		}

		if ($this->day < $when->day) {
			return false;
		}

		if ($when->day < $this->day) {
			return true;
		}

		if ($this->hour < $when->hour) {
			return false;
		}

		if ($when->hour < $this->hour) {
			return true;
		}

		if ($this->minute < $when->minute) {
			return false;
		}

		if ($when->minute < $this->minute) {
			return true;
		}

		if ($this->second < $when->second) {
			return false;
		}

		if ($when->second < $this->second) {
			return true;
		}

		if ($this->microsecond < $when->microsecond) {
			return false;
		}

		if ($when->microsecond < $this->microsecond) {
			return true;
		}

		return false;
	}

	public function before($when)
	{
		if (!$when instanceof HproseDateTime) {
			$when = new HproseDateTime($when);
		}

		if ($this->utc != $when->utc) {
			return $this->timestamp() < $when->timestamp();
		}

		if ($this->year < $when->year) {
			return true;
		}

		if ($when->year < $this->year) {
			return false;
		}

		if ($this->month < $when->month) {
			return true;
		}

		if ($when->month < $this->month) {
			return false;
		}

		if ($this->day < $when->day) {
			return true;
		}

		if ($when->day < $this->day) {
			return false;
		}

		if ($this->hour < $when->hour) {
			return true;
		}

		if ($when->hour < $this->hour) {
			return false;
		}

		if ($this->minute < $when->minute) {
			return true;
		}

		if ($when->minute < $this->minute) {
			return false;
		}

		if ($this->second < $when->second) {
			return true;
		}

		if ($when->second < $this->second) {
			return false;
		}

		if ($this->microsecond < $when->microsecond) {
			return true;
		}

		if ($when->microsecond < $this->microsecond) {
			return false;
		}

		return false;
	}

	public function equals($when)
	{
		if (!$when instanceof HproseDateTime) {
			$when = new HproseDateTime($when);
		}

		if ($this->utc != $when->utc) {
			return $this->timestamp() == $when->timestamp();
		}

		return ($this->year == $when->year) && ($this->month == $when->month) && ($this->day == $when->day) && ($this->hour == $when->hour) && ($this->minute == $when->minute) && ($this->second == $when->second) && ($this->microsecond == $when->microsecond);
	}

	static public function isValidTime($hour, $minute, $second, $microsecond = 0)
	{
		return HproseTime::isValidTime($hour, $minute, $second, $microsecond);
	}
}

function is_utf8($string)
{
	return preg_match("%^(?:[\\x00-\\x7F]|[\\xC2-\\xDF][\\x80-\\xBF]|\\xE0[\\xA0-\\xBF][\\x80-\\xBF]|[\\xE1-\\xEC\\xEE\\xEF][\\x80-\\xBF]{2}|\\xED[\\x80-\\x9F][\\x80-\\xBF]|\\xF0[\\x90-\\xBF][\\x80-\\xBF]{2}|[\\xF1-\\xF3][\\x80-\\xBF]{3}|\\xF4[\\x80-\\x8F][\\x80-\\xBF]{2})*$%xs", $string);
}

function ustrlen($s)
{
	$pos = 0;
	$length = strlen($s);
	$len = $length;

	while ($pos < $length) {
		$a = ord($s[$pos++]);

		if ($a < 128) {
			continue;
		}
		else if (($a & 224) == 192) {
			++$pos;
			--$len;
		}
		else if (($a & 240) == 224) {
			$pos += 2;
			$len -= 2;
		}
		else if (($a & 248) == 240) {
			$pos += 3;
			$len -= 2;
		}
	}

	return $len;
}

function is_list($a)
{
	return is_array($a) && ((count($a) == 0) || (count(array_diff(range(0, count($a) - 1), array_keys($a))) == 0));
}

class HproseException extends Exception
{}


?>
