<?php

function parseNotes($output)
{
	$output = str_replace('default0', 'default', $output);
	$output = str_replace('default1', 'default', $output);
	$continue = 0;
	$begin = strpos($output, '<table>');
	$limit = strpos($output, '</table>', $begin);
	$limit = rstrpos($output, '</tr>', $limit);
	$return = array();
	$count = 0;
	while ($begin < $limit and $begin > 0)
	{
		$count2 = 0;
		$return[$count] = array();
		$begin = strpos($output, '<tr>', $begin);
		if ($begin < $limit and $begin > 0)
		{
			while ($count2 < 6)
			{
				if ($count2 === 3 or $count2 == 4)
				{
					$begin = strpos($output, "<td class='default' align='right'>", $begin);
					$begin = $begin + 34;
				}
				else
				{
					$begin = strpos($output, "<td class='default'>", $begin);
					$begin = $begin + 20;
				}
				$end = $begin;
				$end = strpos($output, "</td>", $end);
				$return[$count][$count2] = substr($output, $begin, $end - $begin);
				$end = $end + 5;
				$count2 = $count2 + 1;
			}
		}
		$count = $count+ 1;
	}
	if (empty($return[$count - 1][0]))
		unset($return[$count - 1]);
	return ($return);
}

function parseRapportNotes($output)
{
	$html = new DOMDocument();
	@$html->loadHTML($output);
	$xpath = new DOMXPath($html);
	$divs = $xpath->query('//div');
	$notes = array();
	foreach ($divs as $div)
	{
		if ($div->getAttribute("id") == "div1")
		{
			$elements = $div->getElementsByTagName("tr");
			foreach ($elements as $element)
			{
				if ($element->firstChild->getAttribute("class") == "default")
					continue;
				$childs = $element->childNodes;
				$note = array();
				foreach ($childs as $child)
				{
					if ($child->nodeName == "td")
						$note[] = trim($child->nodeValue);
				}
				$notes[] = $note;
			}
		}
	}
	return ($notes);
}

function parseSusieStatus($output)
{
	$ret = array();
	$ret[0] = "Unset";
	if (strpos($output, "La limite est d&eacute;pass&eacute;e") > 0)
		$ret[0] = "END";
	if (strpos($output, "S'inscrire") > 0)
		$ret[0] = "OUT";
	if (strpos($output, "Se desincrire") > 0)
	        $ret[0] = "IN";
	$begin = TRUE;
	$nbr = 0;
	$i = 1;
	$str = '<td class="contour_modif" valign="middle" align="center">';
	while ($begin != FALSE)
	{
		$begin = strpos($output, $str, $begin);
		if ($begin === FALSE)
		   break;
		$i++;
		$begin = $begin + 10;
		$nbr++;
	}
	if ($nbr == 0)
	   return $ret;
	$i = 0;
	$login = "";
	$log_1 = 0;
	$log_2 = 0;
	$font = '<font class="default0">';
	while ($i < $nbr)
	{
		$log_1 = strpos($output, $font, $log_1);
		$log_2 = strpos($output, "<br>", $log_1);
		$log_1 = $log_1 + strlen($font);
		$login = substr($output, $log_1, $log_2 - $log_1);
		$login = str_replace("&nbsp;", "", $login);
		$ret[$i + 1] = $login;
		$log_1++;
		$i++;
	}
	return $ret;
}

function parseSusieList($output)
{
	$susie_begin = "<tr class=\"main_page\">";
	$output =  str_replace("default0", "default", $output);
	$output =  str_replace(" default", "default", $output);
	$output =  str_replace("&nbsp;", "", $output);
	$output =  str_replace("default1", "default", $output);
	$output =  str_replace(" align=\"center\" width=\"100\"", "", $output);
	$output =  str_replace(" align=\"center\" width=\"70\"", "", $output);
	$output =  str_replace(" align=\"center\" width=\"50\"", "", $output);
	$output =  str_replace(" align=\"center\"", "", $output);
	$output =  str_replace(" align=\"center\"", "", $output);
	$output =  str_replace(" width=\"110\" nowrap", "", $output);
	$nb_susie = 0;
	$i = 0;
	$j = 0;
	$susie = array();
	if (strpos($output, $susie_begin) <= 0)
		return $susie;
	while (!(strpos($output, $susie_begin, $i) <= 0))
	{
		$i = strpos($output, $susie_begin, $i);
		$i = $i + 1;
		$nb_susie++;
	}
	$i = 0;
	$pos = 0;
	while ($i < $nb_susie)
	{
		$j = 0;
		$susie[$i] = array();
		while ($j < 7)
		{
			if ($j == 0)
				$pos = strpos($output, $susie_begin, $pos);
			else
				$pos = strpos($output, "<td class=\"default\">", $pos);
			$pos2 = strpos($output, "</td>", $pos);
			if ($j == 0)
				$pos = $pos + strlen($susie_begin);	
			else
				$pos = $pos + strlen("<td class=\"default\">");
			$champ = substr($output, $pos, $pos2 - $pos);
			$pos++;
			$pos2++;
			if ($j != 0)
				$susie[$i][$j] = strip_tags($champ);
			else
				{
					$pos_link = strpos($output, "<a href=\"", $pos);
					$pos_link2 = strpos($output, "\">", $pos_link);
					$pos_link = $pos_link + strlen("<a href=\"");
					$susie[$i][$j] = URL_INTRA_SIMPLE.substr($output, $pos_link, $pos_link2 - $pos_link);
				}
			$j++;
		}
		$i++;
	}
	return ($susie);
}	

function parseReport($output)
{
	$infos = array();
	$loc1 = 0;
	$loc2 = 0;
	$total = 8;
	$count = 0;
	while ($count < $total)
	{
		$loc1 = strpos($output, '<TD CLASS="default', $loc2);
		$loc2 = strpos($output,'</TD>', $loc1);
		if (($loc1 != FALSE) && ($loc2 != FALSE))
			$infos[$count] = substr($output, $loc1 + 21, $loc2 - ($loc1 + 21));
		$count = $count + 1;
	}
	$loc1 = strpos($output, '<center>');
	$loc2 = strpos($output, '</center>');
	if (($loc1 != FALSE) && ($loc2 != FALSE))
		$infos[$count] = substr($output,$loc1 + 8, $loc2 - ($loc1 + 8));
	return($infos);
}

function parseModules($output)
{
	$html = new DOMDocument();
	@$html->loadHTML($output);
	$xpath = new DOMXPath($html);
	$divs = $xpath->query('//div');
	foreach ($divs as $div)
	{
		if ($div->getAttribute("id") == "div9")
		{
			$elements = $div->getElementsByTagName("tr");
			foreach ($elements as $element)
			{
				if ($element->firstChild->getAttribute("class") == "default")
					continue;
				$childs = $element->childNodes;
				$module = array();
				foreach ($childs as $child)
				{
					if ($child->nodeName == "td")
						$module[] = trim($child->nodeValue);
				}
				$modules[] = $module;
			}
		}
	}
	return ($modules);
}

function parseMyTokens($output)
{
  //$fichier='testhtml';
  //$output = fread(fopen($fichier, "r"), filesize($fichier));
  $output = str_replace('&#39;', '\'', $output); 
  $output = str_replace('<br />', '<br>', $output); 
  $output = str_replace('onClick', 'onclick', $output); 
  $ret = array();
  $i = 0;
  $nb = 0;
  while (1)
    {
      $i = strpos($output, '<img onclick="popBoxTokenMark', $i);
      if ($i === FALSE)
	break;
      $i++;
      $nb++;
    }
  $i = 0;
  $debut = 0;
  while ($i < $nb)
    {
      $ret[$i] = array();
      $debut = strpos($output, '<img onclick="popBoxTokenMark(\'', $debut);
      $end = strpos($output, '\',', $debut + 1);
      $debut = $debut + strlen('<img onclick="popBoxTokenMark(\'');
      $str = substr($output, $debut, $end - $debut);
      $ret[$i][0] = $str;
      $debut2 = rstrpos($output, '<strong>', $debut);
      $debut2 = rstrpos($output, '<strong>', $debut2);
      $end2 = strpos($output, '</strong>', $debut2);
      $str2 = substr($output, $debut2, $end2 - $debut2);
      $ret[$i][1] = $str2;
      $debut2++;
      $debut3 = strpos($output, '<br>', $debut2);
      $end3 = strpos($output, '<br>', $debut3 + 1);
      $str3 = substr($output, $debut3, $end3 - $debut3);
      $ret[$i][2] = $str3;
      $debut++;
      $i++;
    }
  return $ret;
}

function parseBinomes($output)
{
	$html = new DOMDocument();
	@$html->loadHTML($output);
	$xpath = new DOMXPath($html);
	$tables = $xpath->query('//table');
	$binomes = array();
	$i = 0;
	foreach ($tables as $table)
	{
		if ($i == 3)
		{
			$elements = $table->getElementsByTagName("tr");
			foreach ($elements as $element) {
				if ($i > 9)
				{
					if ($element->getAttribute("class") == "default")
						continue;
					$childs = $element->childNodes;
					$binome = array();
					foreach ($childs as $child)
					{
						if ($child->nodeName == "td")
							$binome[] = trim($child->nodeValue);
					}
					$binomes[] = $binome;
				}
				$i++;
			}
		}
		$i++;
	}
	return ($binomes);
}

function parseDay($output, $date)
{
	$ret = array();
	$ret = parseMyDay($output);
	$soutenances = parseMySoutenances($output, $date);
	$sou_ret = array();
	$i = 0;
	foreach ($soutenances as $alone) {
        $sou_ret[$i][0] = epur_html(trim($alone[1])); //Nom
        $sou_ret[$i][1] = "Soutenance"; //Type
        $sou_ret[$i][2] = "Inconnu"; //epur_html(trim($alone[2])); //Lieu
        $sou_ret[$i][3] = substr(epur_html(trim($alone[3])), -5); //Heure
        $sou_ret[$i][3][2] = ":";
        $sou_ret[$i][4] = "Inconnue"; //Durée
        $i++;
    }
    $retok = array_merge($ret, $sou_ret);
    if (sizeof($retok) > 0)
    	$retok = sort_multi_array ($retok, 3);
    return ($retok);
}

function parseMyDay($output)
{
	$array_res = array();
	$count = 0;
	$langfr = strpos($output, 'Duree');
	$langen = strpos($output, 'Length');
	if ($langfr > $langen)
		$parse_len = 'Duree';
	else
		$parse_len = 'Length';
	$pos_end  = strpos($output, $parse_len);
	if ($pos_end == FALSE)
	{
		$valid = 0;
	}
	else
		$valid = 1;
	$pos_end  = strpos($output, '</table>', $pos_end);
	$pos_end  = strpos($output, '</table>', $pos_end + 8);
	$pos1 = strpos($output, $parse_len);
	$pos1 = strpos($output, '<tr>', $pos1);
	while (($pos1 < $pos_end) && ($pos1 != -1) && $valid == 1)
	{
		$in = array();
		$count2 = 0;
		while ($count2 < 5)
		{
			if ($count2 == 0)
			{
				$pos1 = strpos($output, 'default', $pos1);
				$pos1 = $pos1 + 10;
			}
			else
			{
				$pos1 = strpos($output, 'center">', $pos1);
				$pos1 = $pos1 + 9;
			}
			$pos2 = strpos($output, "</td>", $pos1);
			$affich = substr($output, $pos1, $pos2 - $pos1);
			$return = $affich;
			$in[$count2] = $return;
			if ($count2 > 2 && trim($in[1]) == "Susie")
				$in[2] = "Susie Spot";
			if ($count2 == 4)
				$array_res[] = $in;
			$count2 = $count2 + 1;
		}
		$pos1 = strpos($output, '<tr>', $pos1);
		$count = $count + 1;
	}
	return ($array_res);
}

function parseMySusie($output)
{	 
	 $output = str_replace(' colspan="3"', '',$output);
	 $output = str_replace('default0', '',$output);
 	 $output = str_replace(' default1', '',$output);
 	 $output = str_replace('default1', '',$output);
	 $output = str_replace(' align="center" width="70"', '',$output);
	 $output = str_replace(' align="center" width="50"', '',$output);
	 $output = str_replace(' align="center" width="110"', '',$output);
	 $ret = array();
	 $nb = 0;
	 $susie_count = '<tr class="main_page">';
	 $susie_beg = '<td class="">';
	 $susie_end = '</td>';
	 $dep = 0;
	 while (1)
	 {
		$dep = strpos($output, $susie_count, $dep);
		if ($dep < 0 || $dep === FALSE)
			break;
		$dep = $dep + strlen($susie_count);
		$nb++;
	 }
	 $i = 0;
	 $begin = strpos($output, $susie_count);
	 while ($i < $nb)
	 {
		$ret[$i] = array();
		$j = 0;
	  	while ($j < 7)
	  	{
			$begin = strpos($output, $susie_beg, $begin);
			$end = strpos($output, $susie_end, $begin);
			$str = substr($output, $begin, $end - $begin);
			$ret[$i][$j] = $str;
			if ($j == 6)
			{
			  $link = $ret[$i][0];
			  $link_b = strpos($link, '<a href="');
			  $link_e = strpos($link, '&date');
			  $link_b = $link_b + strlen('<a href="');
			  $link = substr($link, $link_b, $link_e - $link_b);
			  $link = "https://www.epitech.eu/intra/".$link;
			  $ret[$i][$j] = $link;
			}
			$begin++;
			$j++;
	  	}
		$ret[$i][$j] = $ret[$i][6];
	  	$i++;
	 }
	 return ($ret);
}	

function parseMySoutenances($output, $date)
{
	$day = $date[0].$date[1];
	$month = $date[2].$date[3];
	$year = $date[4].$date[5].$date[6].$date[7];
	$return_tab = array();
	$ret = -5;
	$ret2 = 0;
	$nb = 0;
	$parseme = "Soutenance le " . $day . "/" . $month . "/" . $year;
	$nb_sout = 0;
	if (strpos($output, $parseme, $ret2) == FALSE)
		return ($return_tab);
	while ($ret != FALSE)
	{
		$ret = strpos($output, $parseme, $ret2);
		$ret2 = $ret + 10;
		if ($ret != FALSE)
			$nb = $nb + 1;
	}
	$first = 0;
	$f2a = 0;
	$nbv = 0;
	$f2b = 0;
	$f2 = 0;
	$correct = "\"";
	$f3 = 0;
	$parseme2a = "<td class=\" default1\" align=\"center\">";
	$parseme2b = "<td class=\"default0\" align=\"center\">";
	$parseme3 = "</table>";
	$count = 0;
	$pos1 = 0;
	$pos2 = 0;
	$pos1 = strpos($output, $parseme, $pos1);
	$pos1 = rstrpos ($output, "<tr>", $pos1);
	$pos1 = $pos1 - 1;
	$pos1 = rstrpos ($output, "<tr>", $pos1);
	while ($nbv < $nb)
	{
		$count2 = 0;
		$return_tab[$count] = array();
		while ($count2 < 4)
		{
			if (($count2 == 0) || ($count2 == 1) || ($count2 == 5))
			{
				$pos1 = strpos($output, '">', $pos1);
				$pos1 = $pos1 + 3;
			}
			if ($count2 == 3)
			{
				$pos1 = strpos($output, "center", $pos1);
				$pos1 = strpos($output, "center", $pos1 + 2);
				$pos1 = strpos($output, ">", $pos1 + 2);
				$pos1 = $pos1 + 2;
			}
			if ($count2 == 2)
			{
				$pos1 = strpos($output, 'width', $pos1);
				$pos1 = strpos($output, '>', $pos1);
				$pos1 = $pos1 + 1;
			}
			if ($count2 == 4)
			{
				$pos1 = strpos($output, 'align=center', $pos1);
				$pos1 = strpos($output, '>', $pos1);
				$pos1 = strpos($output, '>', $pos1);
				$pos1 = $pos1 + 1;
			}
			if ($count2 != 4)
				$pos2 = strpos($output, "</td>", $pos1);
			if ($count2 == 4)
				$pos2 = strpos($output, "</a>", $pos1);
			if ($count2 == 3)
				$pos2 = strpos($output, "</a>", $pos1);
			$return_tab[$count][$count2] = trim(substr($output, $pos1, $pos2 - $pos1));
			$a = $return_tab[$count][$count2];
	  // LOLILOL reg = /\s+/;
	  //return_tab[count][count2].replace(reg,'+')
			if ($count2 == 4)
				$pos2 = strpos($output, "</td>", $pos1);
			if ($count2 == 3)
				$pos2 = strpos($output, "</td>", $pos1);
			$pos1 = $pos2;
			$count2 = $count2 + 1;
		}
		$pos1 = strpos($output, $parseme, $pos1);
		if ($pos1 != FALSE)
		{
			$pos1 = rstrpos($output, '<tr>', $pos1);
			$pos1 = rstrpos($output, '<tr>', $pos1 - 4);
		}
		$count = $count + 1;
		$nbv = $nbv + 1;
	}
	return($return_tab);
}

function parseFire($output)
{
	$html = new DOMDocument();
	@$html->loadHTML($output);
	$xpath = new DOMXPath($html);
	$tds = $xpath->query('//td');
	$alerts = 0;
	$fire = array();
	foreach ($tds as $td)
	{
		if ($td->getAttribute("rowspan") == "2")
		{
			$childs = $td->childNodes;
			foreach($childs as $child)
			{
				if ($alerts == 3)
				{
					$fire['alerts'] = $child->nodeValue;
					$alerts = -1;
				}
				if ($child->nodeName == "small")
				{
					$fire['color'] = ((strpos($child->nodeValue, "rouge") != 0) ? 'red' : ((strpos($child->nodeValue, "green") != 0) ? 'green' : 'orange'));
					$fire['desc'] = $child->nodeValue;
				}
				else if ($child->nodeName == 'i');
				else if ($child->nodeName == 'strong')
					$fire['nb_alerts'] = $child->nodeValue;
				else if ($child->nodeName == 'br')
					$alerts = ($alerts >= 0 ? $alerts + 1 : -1);
			}
			break;
		}
	}
	return ($fire);
}

function parseProjects($output)
{
	$projects = array();
	$red = strpos($output, '<td style="background-color: #ff0000;" colspan="8">');
	if ($red == FALSE)
		$red = 0;
	$first = 0;
	$nbr = 0;
	$first = $red;
	while ($first != FALSE)
	{
		$first = strpos($output, 'rendu le', $first + 6);
		$nbr = $nbr + 1;
	}
	$nbr = $nbr - 1;
	$count = 0;
	$first = $red;
	while ($count < $nbr)
	{
		$first = strpos($output, 'rendu le', $first + 6);
		$dep = rstrpos($output, "<tr>", $first);
		$dep = strpos($output, '">', $dep);
		$dep = $dep + 2;
		$dep2 = strpos($output, '</td>', $dep);
		$part1 = substr($output, $dep, $dep2 - $dep);
		$dep = strpos($output, '">', $dep2);
		$dep = strpos($output, '">', $dep + 2);
		$dep = $dep + 2;
		$dep2 = strpos($output, '</a>', $dep);
		$part2 = substr($output, $dep, $dep2 - $dep);
		$dep = strpos($output, '</td>', $dep2 + 5);
		$dep2 = strpos($output, '</td>', $dep + 5);
		$dep = strpos($output, '>', $dep + 5);
		$dep = $dep + 1;
		$part3 = substr($output, $dep, $dep2 - $dep);
		$dep = strpos($output, '">', $dep2);
		$dep = strpos($output, '">', $dep + 2);
		$dep = strpos($output, '">', $dep + 2);
		$dep2 = strpos($output, '</a>', $dep);
		$dep = $dep + 2;
		$part4 = substr($output, $dep, $dep2 - $dep);
		$projects[] = array('name' => trim($part2), 'date' => trim($part3));
		$count++;
	}
	return ($projects);
}

?>