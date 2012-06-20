<?php

require_once("user.class.php");


define("SEPARATOR", ":_:");
define("VERSION", 4.6);

$login = isset($_REQUEST['login']) ? $_REQUEST['login'] : NULL;
$passwd = isset($_REQUEST['passwd']) ? $_REQUEST['passwd'] : NULL;
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : NULL;
$date = isset($_REQUEST['date']) ? $_REQUEST['date'] : NULL;
$user = isset($_REQUEST['user']) ? $_REQUEST['user'] : NULL;

function stripSpacesAndTabs($str)
{
  $str = preg_replace( '/\s+/', ' ', $str);
  return ($str);
}

if (!isset($login) || !isset($passwd) || !isset($action))
	die('ERR_PARAMS');

$epiuser = new EpitechUser($login, $passwd);
if ($epiuser->isset == false)
{
	if ($epiuser->error == EpitechUser::ERR_USER_NOTSET)
		die('ERR_USER_NOTSET');
	else if ($epiuser->error == EpitechUser::ERR_LOGIN)
		die('ERR_LOGIN');
	else if ($epiuser->error == EpitechUser::ERR_CURL)
		die('ERR_INTRA');
	die('ERR_UNKNOWN');
}

if ($action == 'day')
{
	$day = $epiuser->get_day($date);
	foreach ($day as $res) {
		foreach($res as $e) {
			echo epur_html($e);
			echo SEPARATOR;
		}
	}
}

if ($action == 'week')
{
	$week = $epiuser->get_week($date);
	$i = 1;
	foreach ($week as $e => $day)
	{
		$wtf = substr($e, -8);
		$mmonth = $wtf[2].$wtf[3];
		$dday = $wtf[0].$wtf[1];
		$yyear = substr($wtf, -4);
		//echo $i."-".$dday."/".$mmonth."/".$yyear.SEPARATOR;
		echo " ";
		foreach ($day as $res) {
			foreach($res as $e) {
				echo trim(epur_html($e));
				echo SEPARATOR;
			}
		}
		echo "_OGC_";
		$i++;
	}
}

if ($action == 'projects')
{
	$projects = $epiuser->get_projects();
	foreach ($projects as $res) {
		foreach($res as $e) {
			echo epur_html($e);
			echo SEPARATOR;
		}
	}
}

if ($action == 'notes')
{
	$notes = $epiuser->get_notes();
	foreach ($notes as $res) {
		$i = 0;
		foreach($res as $e) {
			echo epur_html($e);
			echo SEPARATOR;
			$i++;
		}
	}
}

if ($action == 'fire')
{
	$fire = $epiuser->get_fire();
	foreach ($fire as $res) {
		echo stripSpacesAndTabs(epur_html($res));
		echo SEPARATOR;
	}
}

if ($action == 'report')
{
	$projects = $epiuser->get_report($user);
	foreach ($projects['report'] as $e)
	{
		echo epur_html($e);
		echo SEPARATOR;
	}
	foreach ($projects['notes'] as $res) {
		foreach($res as $e) {
			echo epur_html($e);
			echo SEPARATOR;
		}
	}
}

if ($action == 'version')
	echo VERSION;

?>