<?php

require_once("func.inc.php");
require_once("parser.php");

define("URL_LOGIN", "https://www.epitech.eu/intra/index.php");
define("URL_INTRA", "https://www.epitech.eu/intra/index.php?section=etudiant&page=eboard");
define("URL_INTRA_SIMPLE", "https://www.epitech.eu/intra/");
define("URL_DATE", "https://www.epitech.eu/intra/index.php?section=etudiant&page=eboard&date=");
define("URL_REPORT", "https://www.epitech.eu/intra/index.php?section=etudiant&page=rapport&login=");
define("URL_SUSIE", "https://www.epitech.eu/intra/index.php?section=susie&date=");
define("URL_SUSIE_SIMPLE", "https://www.epitech.eu/intra/index.php?section=susie");
define("URL_TOKEN", "https://www.epitech.eu/intra/index.php?section=etudiant&page=vue_hebdo");

class EpitechUser
{

  CONST ERR_LOGIN = -84;
  CONST ERR_USER_NOTSET = -42;
  CONST ERR_CURL = -41;
  CONST URL_INTRA = 3;
  public $isset = false;
  public $error = NULL;
  private $login = NULL;
  private $passwd_intra = NULL;
  private $passwd_unix = NULL;
  private $tmp_fname = NULL;

  public function __construct($login = NULL, $passwd_intra = NULL, $passwd_unix = NULL)
  {
    if (!isset($login) && !isset($passwd_intra))
      {
	$this->error = EpitechUser::ERR_USER_NOTSET;
	return ($this);
      }
    $this->tmp_fname = tempnam("/tmp", "COOKIE");
    $POST_array = array('login' => $login, 'passwd' => $passwd_intra, 'action' => 'login', 'path' => '', 'qs' => '/intra/index.php');
    $curl_handle = curl_init(URL_LOGIN);
    curl_setopt($curl_handle, CURLOPT_ENCODING, 'gzip');
    curl_setopt($curl_handle, CURLOPT_COOKIESESSION, true);
    curl_setopt($curl_handle, CURLOPT_COOKIEJAR, $this->tmp_fname);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl_handle, CURLOPT_POST, true);
    curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $POST_array);
    $output = curl_exec($curl_handle);
    $date = new DateTime();
    $timestamp = $date->getTimestamp();
    $newurl = "https://www.epitech.eu/intra/index.php?section=etudiant&t=".$timestamp."&user=".$login;
    curl_setopt($curl_handle, CURLOPT_URL, $newurl);
    $output = curl_exec($curl_handle);
    if ($output === false)
      return ($this->error = EpitechUser::ERR_CURL);
    if (strpos($output, "Utilisateur non identifi") != 0)
      return ($this->error = EpitechUser::ERR_LOGIN);
    $this->isset = true;
    $this->login = $login;
    $this->passwd_intra = $passwd_intra;
    curl_close($curl_handle);
  }

  private function get_pages($pages)
  {
    if ($this->isset === false)
      return ($this->error = EpitechUser::ERR_USER_NOTSET);
    $mh = curl_multi_init();
    $curl_array = array();
    foreach($pages as $i => $url)
      {
	$curl_array[$i] = curl_init($url);
	curl_setopt($curl_array[$i], CURLOPT_COOKIEFILE, $this->tmp_fname);
	curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, true);

	curl_setopt($curl_array[$i], CURLOPT_ENCODING, 'gzip');
	curl_setopt($curl_array[$i], CURLOPT_COOKIESESSION, true);
	curl_setopt($curl_array[$i], CURLOPT_COOKIEJAR, $this->tmp_fname);
	curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_array[$i], CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_array[$i], CURLOPT_FOLLOWLOCATION, true);
	//curl_setopt($curl_array[$i], CURLOPT_POST, true);
	//curl_setopt($curl_array[$i], CURLOPT_POSTFIELDS, $POST_array);
	curl_multi_add_handle($mh, $curl_array[$i]);
      }
    $running = NULL;
    do
      {
	usleep(10000);
	curl_multi_exec($mh, $running);
      } while($running > 0);

    $res = array();
    $j = 0;
    foreach($pages as $i => $url)
      {
	$res[$url] = curl_multi_getcontent($curl_array[$i]);
	$res[$j] = &$res[$url];
	$j++;
      }
    foreach($pages as $i => $url)
      curl_multi_remove_handle($mh, $curl_array[$i]);
    curl_multi_close($mh);       
    return ($res); 
  }

  public function get_notes($login = NULL, $year = NULL)
  {
    if ($this->isset === false)
      return ($this->error = EpitechUser::ERR_USER_NOTSET);
    if (!isset($login))
      $login = $this->login;
    if (!isset($year))
      $year = date('y');
    $url = "https://www.epitech.eu/intra/index.php?section=etudiant&page=notes&action=index_act";
    $output = $this->get_pages(array($url));
    $ret = parseNotes($output[0]);
    return ($ret);
  }

  public function get_modules($login = NULL, $year = NULL)
  {
    if ($this->isset === false)
      return ($this->error = EpitechUser::ERR_USER_NOTSET);
    if (!isset($login))
      $login = $this->login;
    if (!isset($year))
      $year = date('y');
    $url = URL_LOGIN."?section=etudiant&page=rapport&login=".$login."&open_div=9&scolaryear_notes=".$year;
    $output = $this->get_pages(array($url));
    $ret = parseModules($output[0]);
    return ($ret);
  }

  public function get_gpa($modules = NULL, $login = NULL, $year = NULL)
  {
    if ($this->isset === false)
      return ($this->error = EpitechUser::ERR_USER_NOTSET);
    if (!isset($login))
      $login = $this->login;
    if (!isset($year))
      $year = date('y');
    if (!isset($modules))
      $modules = $this->get_modules($login, $year);
    $nb_credits = 0;
    $score = 0;
    foreach ($modules as $module)
      {
	$grade = explode("/", $module[5]);
	$grade = trim($grade[1]);
	if ($grade != "-" && $grade != "Acquis")
	  {
	    $nb_credits += $module[3];
	    $score += $module[3] * ($grade == 'A' ? 4 : ($grade == 'B' ? 3 : ($grade == 'C' ? 2 : ($grade == 'D' ? 1 : 0))));	
	  }
      }
    return ($score / $nb_credits);
  }

  public function get_binomes($login = NULL)
  {
    if ($this->isset === false)
      return ($this->error = EpitechUser::ERR_USER_NOTSET);
    if (!isset($login))
      $login = $this->login;
    $url = URL_LOGIN."?section=all&page=binomes_detail&login=".$login;
    $output = $this->get_pages(array($url));
    $ret = parseBinomes($output[0]);
    return ($ret);
  }

  public function get_day($date = NULL)
  {
    if ($this->isset === false)
      return ($this->error = EpitechUser::ERR_USER_NOTSET);
    if (!isset($date))
      $date = date("dmY");
    $url = URL_DATE.$date;
    $output = $this->get_pages(array($url));
    $ret = parseDay($output[0], $date);
    return ($ret);
  }

  public function get_week($date = NULL)
  {
    if ($this->isset === false)
      return ($this->error = EpitechUser::ERR_USER_NOTSET);
    if (!isset($date))
      $date = date("dmY");
    $days = week_from_monday($date);
    $url = array();
    for ($i = 0; $i <= 6; $i++)
      $url[$i] = URL_DATE.$days[$i];
    $output = $this->get_pages($url);
    $ret = array();
    for ($i = 0; $i <= 6; $i++)
      $ret[$days[$i]] = parseDay($output[$i], $days[$i]);
    return ($ret);
  }

  public function get_fire()
  {
    if ($this->isset === false)
      return ($this->error = EpitechUser::ERR_USER_NOTSET);
    $url = URL_LOGIN;
    $output = $this->get_pages(array($url));
    $ret = parseFire($output[0]);
    return ($ret);
  }

  public function get_susielist($day)
  {
    if ($this->isset === false)
      return ($this->error = EpitechUser::ERR_USER_NOTSET);
    if (!isset($date))
      $date = date("dmY");
    $url = URL_SUSIE.$day;
    $output = $this->get_pages(array($url));
    $ret = parseSusieList($output[0]);
    return ($ret);
  }

  public function get_my_susie()
  {
    $url = URL_SUSIE_SIMPLE;
    if ($this->isset === false)
      return ($this->error = EpitechUser::ERR_USER_NOTSET);
    $output = $this->get_pages(array($url));
    $ret = parseMySusie($output[0]);
    return ($ret);
  }

  public function get_my_tokens()
  {
    $url = array();
    $url[0] = "https://www.epitech.eu/intra/index.php?section=etudiant&page=vue_hebdo&semaine=".$_SERVER['REQUEST_TIME'];
    $url[1] = "https://www.epitech.eu/intra/index.php?section=etudiant&page=vue_hebdo&semaine=".$_SERVER['REQUEST_TIME']-604800;
    if ($this->isset === false)
      return ($this->error = EpitechUser::ERR_USER_NOTSET);
    $output = $this->get_pages($url);
    $ret1 = parseMyTokens($output[0]);
    $ret2 = parseMyTokens($output[1]);
    $ret = array_merge($ret1, $ret2);
    return ($ret);
  }

  public function get_susie_unregister($url)
  {
    $url .= "&p=del";
    if ($this->isset === false)
      return ($this->error = EpitechUser::ERR_USER_NOTSET);
    $output = $this->get_pages(array($url));
    if (strpos($output[0], "Vous avez ete supprime") > 0)
      $rep = "OUT";
    else
      $ret = "IN";
    return ($rep);
  }

  public function get_susie_register($url)
  {
    $url .= "&p=add";
    if ($this->isset === false)
      return ($this->error = EpitechUser::ERR_USER_NOTSET);
    $output = $this->get_pages(array($url));
    if (strpos($output[0], "Vous avez ete ajoute") > 0)
      $rep = "IN";
    else
      $ret = "OUT";
    return ($rep);
  }


  public function get_susiestatus($url)
  {
    if ($this->isset === false)
      return ($this->error = EpitechUser::ERR_USER_NOTSET);
    $output = $this->get_pages(array($url));
    $ret = parseSusieStatus($output[0]);
    return ($ret);
  }

  public function get_projects()
  {
    if ($this->isset === false)
      return ($this->error = EpitechUser::ERR_USER_NOTSET);
    $url = URL_INTRA;
    $output = $this->get_pages(array($url));
    $ret = parseProjects($output[0]);
    return ($ret);
  }

  public function get_report($login = NULL)
  {
    if ($this->isset === false)
      return ($this->error = EpitechUser::ERR_USER_NOTSET);
    if (!isset($login))
      $login = $this->login;
    $url = URL_LOGIN."?section=etudiant&page=rapport&login=".$login;
    $output = $this->get_pages(array($url));
    $ret['report'] = parseReport($output[0]);
    $ret['notes'] = parseRapportNotes($output[0]);
    return ($ret);
  }

  public function validate_token($token)
  {
    $POST_array = array('event_id' => $token['event_id'],
			'token_val' => $token['token_val'],
			'xjxfun' => 'ajaxTokenMark',
			'xjxr' => '1338294829480',
			'xjxargs[]' => '<xjxobj><e><k>event_id</k><v>S'.$token['event_id'].'</v></e><e><k>token_val</k><v>S'.$token['token_val'].'</v></e><e><k>mark1_val</k><v>S</v></e><e><k>mark2_val</k><v>S</v></e><e><k>sendToken</k><v>SEnvoyez</v></e><e><k>type_val</k><v>S0</v></e><e><k>com_val</k><v>S</v></e></xjxobj>',
			'sendToken' => '1');
    $curl_handle = curl_init(URL_TOKEN);
    curl_setopt($curl_handle, CURLOPT_ENCODING, 'gzip');
    curl_setopt($curl_handle, COOKIE_SESSION, true);
    curl_setopt($curl_handle, CURLOPT_COOKIEFILE, $this->tmp_fname);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl_handle, CURLOPT_POST, true);
    curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $POST_array);
    $output = curl_exec($curl_handle);
    $output = strip_tags($output);
    echo substr($output, 1 );
  }

  public function get_login()
  {
    if ($this->isset === false)
      return ($this->error = EpitechUser::ERR_USER_NOTSET);
    return ($this->login);
  }

  public function get_passwd()
  {
    if ($this->isset === false)
      return ($this->error = EpitechUser::ERR_USER_NOTSET);
    return ($this->passwd);
  }
}
?>
