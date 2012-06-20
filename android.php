<?php

require_once("user.class.php");


define("SEPARATOR", ":_:");
define("VERSION", 4.6);

$login = isset($_REQUEST['login']) ? $_REQUEST['login'] : NULL;
$passwd = isset($_REQUEST['passwd']) ? $_REQUEST['passwd'] : NULL;
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : NULL;
$date = isset($_REQUEST['date']) ? $_REQUEST['date'] : NULL;
$user = isset($_REQUEST['user']) ? $_REQUEST['user'] : NULL;
$url = isset($_REQUEST['url']) ? $_REQUEST['url'] : NULL;

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
		echo $i."-".$dday."/".$mmonth."/".$yyear.SEPARATOR;
		foreach ($day as $res) {
			foreach($res as $e) {
				echo trim(epur_html($e));
				echo SEPARATOR;
			}
		}
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

if ($action == 'my_susie')
{
	$susie = $epiuser->get_my_susie();
	$i = 0;
	while ($i < sizeof($susie))
	{
		$j = 0;
		while ($susie[$i][$j])
		{
			echo stripSpacesAndTabs(epur_html($susie[$i][$j]));
			echo SEPARATOR;
			$j++;
		}
		$i++;
	}
}

if ($action == 'my_tokens')
{
  $tokens = $epiuser->get_my_tokens();
  	$i = 0;
	while ($i < sizeof($tokens))
	{
		$j = 0;
		while ($tokens[$i][$j])
		{
			echo stripSpacesAndTabs(epur_html($tokens[$i][$j]));
			echo SEPARATOR;
			$j++;
		}
		$i++;
	}
}

if ($action == 'susie_register')
{
	$rep = $epiuser->get_susie_register($url);
	echo $rep;
}

if ($action == 'susie_unregister')
{
	$rep = $epiuser->get_susie_unregister($url);
	echo $rep;
}

if ($action == 'susie_status')
{
	$fire = $epiuser->get_susiestatus($url);
	echo " ";
	$i = 0;
	while ($fire[$i])
	{
		echo stripSpacesAndTabs(epur_html($fire[$i]));
		echo SEPARATOR;
		$i++;
	}
}

if ($action == 'susie_list')
{
	$susie = $epiuser->get_susielist($date);
	$i = 0;
	while ($i < sizeof($susie))
	{
		$j = 0;
		while ($susie[$i][$j])
		{
			echo stripSpacesAndTabs(epur_html($susie[$i][$j]));
			echo SEPARATOR;
			$j++;
		}
		$i++;
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

if ($action == 'token')
  {
    $event_id = isset($_REQUEST['event_id']) ? $_REQUEST['event_id'] : NULL;
    $token_val = isset($_REQUEST['token_val']) ? $_REQUEST['token_val'] : NULL;
    if (!isset($event_id) || !isset($token_val))
      die('ERR_PARAMS');
    $epiuser->validate_token(array('event_id' => $event_id, 'token_val' => $token_val));
  }

?>