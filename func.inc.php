<?php

function sort_multi_array($array, $key)
{
	$keys = array();
	for ($i = 1;$i < func_num_args(); $i++) {
		$keys[$i - 1] = func_get_arg($i);
	}
	$func = function ($a, $b) use ($keys)
	{
		for ($i = 0; $i<count($keys); $i++) 
		{
			if ($a[$keys[$i]] != $b[$keys[$i]]) {
				return (($a[$keys[$i]] < $b[$keys[$i]]) ? -1 : 1);
			}
		}
		return (0);
	};
	usort($array, $func);
	return ($array);
}

function rstrpos($haystack, $needle, $offset)
{
	$size = strlen($haystack);
	$pos = strpos(strrev($haystack), strrev($needle), ($size - $offset));
	if ($pos === false)
		return (false);
	return ($size - $pos - strlen($needle));
}

function epur_html($html) {
	if (empty($html))
		return ($html);
	return (strip_tags($html));
}

function week_from_monday($date) {
	$day = $date[0].$date[1];
	$month = $date[2].$date[3];
	$year = $date[4].$date[5].$date[6].$date[7];
	$wkday = date('l', mktime('0', '0', '0', $month, $day, $year));
	switch($wkday) {
		case 'Monday': $numDaysToMon = 0; break;
		case 'Tuesday': $numDaysToMon = 1; break;
		case 'Wednesday': $numDaysToMon = 2; break;
		case 'Thursday': $numDaysToMon = 3; break;
		case 'Friday': $numDaysToMon = 4; break;
		case 'Saturday': $numDaysToMon = 5; break;
		case 'Sunday': $numDaysToMon = 6; break;   
	}
	$monday = mktime('0','0','0', $month, $day-$numDaysToMon, $year);
	$seconds_in_a_day = 86400;
	for($i = 0; $i < 7; $i++)
		$dates[$i] = date('dmY',$monday + ($seconds_in_a_day * $i));
	return ($dates);
}

function rec_tree($ssh, $path, $depth = 0)
{
	$file_list = $ssh->exec('ls -aF '.$path);
	$file_list = explode("\n", $file_list);
	foreach ($file_list as $file)
	{
		if (empty($file) || $file == "./" || $file == '../')
			continue;
		$flen = strlen($file) - 1;
		echo str_repeat('	', $depth);
		if ($flen > 0 && $file[$flen] == '/')
		{
			echo $file."<br />";
			rec_tree($ssh, $path.$file, $depth + 1);
		}
		else
			echo $file."<br />";
	}
}

?>