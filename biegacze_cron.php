<?php
$blocked_users = array('/users/biegacze', '/users/imf');

function formatduration($secs) {
	$hours = $secs / 3600 % 24;
	$minutes = $secs / 60 % 60;
	$seconds = $secs % 60;

	if (strlen($hours)<2) $hours='0'.$hours;
	if (strlen($minutes)<2) $minutes='0'.$minutes;
	if (strlen($seconds)<2) $seconds='0'.$seconds;

	return join(':', array($hours, $minutes, $seconds));
}

function biegacz_senddm($user, $body, $fromuser, $frompwd, $debug=false) {
	GLOBAL $HTTP_CODE;
	$ch = @curl_init('http://api.blip.pl/');
	if (!$ch) {
		print('CURL initialize error: '. curl_error($ch) . ' ' . curl_errno($ch));
	}
	$url = 'http://api.blip.pl/directed_messages';

	// BLIP
	$curlopts = array (
		CURLOPT_USERAGENT       => 'Adblix/Blip (http://adblix.pl)',
		CURLOPT_HTTPHEADER		=> array('X-Blip-API: 0.02', 'Accept: application/json'),
		CURLOPT_RETURNTRANSFER  => 1,
		CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_0,
		CURLOPT_CONNECTTIMEOUT  => 10,
		CURLOPT_URL				=> $url,
		CURLOPT_POST			=> 1,
		CURLOPT_POSTFIELDS		=> sprintf("directed_message[body]=%s&directed_message[recipient]=%s", $body, $user),
		CURLOPT_HTTPAUTH		=> CURLAUTH_BASIC,
		CURLOPT_USERPWD			=> sprintf('%s:%s', $fromuser, $frompwd)
	);

	# ustawiamy opcje
	curl_setopt_array($ch, $curlopts);
	$json = curl_exec($ch);
	$HTTP_CODE = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	$obj = json_decode($json);
	return $obj;
}

function get_pm_messages($user, $pass, $debug=false) {
	GLOBAL $HTTP_CODE;
	$ch = @curl_init('http://api.blip.pl/');
	if (!$ch) {
		print('CURL initialize error: '. curl_error($ch) . ' ' . curl_errno($ch));
	}
	$url = "http://api.blip.pl/private_messages?limit=50";

	$curlopts = array (
		CURLOPT_USERAGENT       => 'asystent/0.0.1 http://asystent.adblix.pl/',
		CURLOPT_HTTPHEADER		=> array('X-Blip-API: 0.02', 'Accept: application/json'),
		CURLOPT_RETURNTRANSFER  => 1,
		CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_0,
		CURLOPT_CONNECTTIMEOUT  => 10,
		CURLOPT_URL				=> $url,
		CURLOPT_HTTPAUTH		=> CURLAUTH_BASIC,
		CURLOPT_USERPWD			=> sprintf('%s:%s', $user, $pass)
	);

	# ustawiamy opcje
	curl_setopt_array($ch, $curlopts);
	$json = curl_exec($ch);
	$HTTP_CODE = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	$obj = json_decode($json);
	if ($debug) print "<h3>get_pm_messages HTTP CODE: " . $HTTP_CODE . "</h3>";
	if ($debug) print "<h3>curl_exec:</h3>";
	if ($debug) print_r($obj);

	return $obj;
}

function getduration($matches) {
	if (@$matches[5]!='') {
		$time = explode(':', $matches[5]);
        $seconds = 0;
		if (count($time)>2) {
			$seconds = $time[0]*3600 + $time[1]*60 + $time[2];
		} else {
			$seconds = $time[0]*60 + $time[1];
		}
		return $seconds;
	} else {
		return false;
	}
}

$HTTP_CODE = '';
function get_tag_messages($tag_name, $limit, $debug=false) {
	GLOBAL $HTTP_CODE;
	$ch = @curl_init('http://api.blip.pl/');
	if (!$ch) {
		print('CURL initialize error: '. curl_error($ch) . ' ' . curl_errno($ch));
	}
	$url = "http://api.blip.pl/tags/" . $tag_name . "?limit=" . $limit;

	$curlopts = array (
		CURLOPT_USERAGENT       => 'asystent/0.0.1 http://asystent.adblix.pl/',
		CURLOPT_HTTPHEADER		=> array('X-Blip-API: 0.02', 'Accept: application/json'),
		CURLOPT_RETURNTRANSFER  => 1,
		CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_0,
		CURLOPT_CONNECTTIMEOUT  => 10,
		CURLOPT_URL				=> $url
	);

	# ustawiamy opcje
	curl_setopt_array($ch, $curlopts);
	$json = curl_exec($ch);
	$HTTP_CODE = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	$obj = json_decode($json);
	if ($debug) print "<h3>HTTP CODE: " . $HTTP_CODE . "</h3>";
	if ($debug) print "<h3>curl_exec:</h3>";
	if ($debug) print_r($obj);

	return $obj;
}

function get_dm_messages($user, $pass, $debug=false) {
	GLOBAL $HTTP_CODE;
	$ch = @curl_init('http://api.blip.pl/');
	if (!$ch) {
		print('CURL initialize error: '. curl_error($ch) . ' ' . curl_errno($ch));
	}
	$url = "http://api.blip.pl/directed_messages?limit=50";

	$curlopts = array (
		CURLOPT_USERAGENT       => 'asystent/0.0.1 http://asystent.adblix.pl/',
		CURLOPT_HTTPHEADER		=> array('X-Blip-API: 0.02', 'Accept: application/json'),
		CURLOPT_RETURNTRANSFER  => 1,
		CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_0,
		CURLOPT_CONNECTTIMEOUT  => 10,
		CURLOPT_URL				=> $url,
		CURLOPT_HTTPAUTH		=> CURLAUTH_BASIC,
		CURLOPT_USERPWD			=> sprintf('%s:%s', $user, $pass)
	);

	# ustawiamy opcje
	curl_setopt_array($ch, $curlopts);
	$json = curl_exec($ch);
	$HTTP_CODE = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	$obj = json_decode($json);
	if ($debug) print "<h3>get_dm_messages HTTP CODE: " . $HTTP_CODE . "</h3>";
	if ($debug) print "<h3>curl_exec:</h3>";
	if ($debug) print_r($obj);

	return $obj;
}

function save_runs($json_messages, $regexp, $debug=false) {
	GLOBAL $blocked_users;
	$link = mysql_connect('localhost', 'cardinalpharma', 'tweets');
	if (!$link) die('Nie można się połaczyć: ' . mysql_error());
	$db_selected = mysql_select_db('cardinalpharma', $link);
	if (!$db_selected) die ('Nie można ustawić bazy : ' . mysql_error());
	mysql_query("SET character_set_client=utf-8", $link);
	mysql_query("SET character_set_connection=utf-8", $link);
	mysql_query("SET CHARSET utf8", $link);
	mysql_query("SET NAMES 'utf8' COLLATE 'utf8_polish_ci'", $link);

	foreach ($json_messages as $blip) {
		if ( in_array($blip->user_path, $blocked_users) ) continue;
		// if ($blip->user_path == '/users/biegacze') continue;
		if (preg_match($regexp, $blip->body, $matches)) {
			// print_r($matches);
			if ($duration = getduration($matches)) {
				if ($debug) print "<h3>".str_replace('/users/', '', $blip->user_path).": ".floatval($matches[1].".".$matches[3])."km on ".$blip->created_at." [dur. $duration]</h3>";
				$runsql = sprintf("INSERT IGNORE INTO bieganie (blip_id, user, distance, rundate, blipsent, duration, bliptxt) VALUES (%d, '%s', %f, '%s', 0, %d, '%s')",
									$blip->id,
									str_replace('/users/', '', $blip->user_path),
									floatval($matches[1].".".$matches[3]),
									$blip->created_at,
									$duration,
									mysql_real_escape_string($blip->body)
								);
				if ($debug) print "<br>$runsql<br>\n";
				mysql_query($runsql);
			} else {
				if ($debug) print "<h3>".str_replace('/users/', '', $blip->user_path).": ".floatval($matches[1].".".$matches[3])."km on ".$blip->created_at."</h3>";
				$runsql = sprintf("INSERT IGNORE INTO bieganie (blip_id, user, distance, rundate, blipsent, bliptxt) VALUES (%d, '%s', %f, '%s', 0, '%s')",
									$blip->id,
									str_replace('/users/', '', $blip->user_path),
									floatval(@$matches[1].".".@$matches[3]),
									$blip->created_at,
									mysql_real_escape_string($blip->body)
								);
				if ($debug) print "<br>$runsql<br>\n";
				mysql_query($runsql);
			}
		}
	}
}

function send_blip_confirmations() {
    $link = mysql_connect('localhost', 'cardinalpharma', 'tweets');
	if (!$link) die('Nie można się połaczyć: ' . mysql_error());
	$db_selected = mysql_select_db('cardinalpharma', $link);
	if (!$db_selected) die ('Nie można ustawić bazy : ' . mysql_error());
	mysql_query("SET character_set_client=utf-8", $link);
	mysql_query("SET character_set_connection=utf-8", $link);
	mysql_query("SET CHARSET utf8", $link);
	mysql_query("SET NAMES 'utf8' COLLATE 'utf8_polish_ci'", $link);

	$dmsql = "SELECT blip_id, user FROM bieganie WHERE blipsent=0";
	$dmres = mysql_query($dmsql) or die("bad sql query");
	while ($row = mysql_fetch_row($dmres)) {
		biegacz_senddm(
				$row[1],
				"Twój bieg został zapisany. Szczegóły na stronie http://biegacze.adblix.pl",
				"biegacze",
				"runstats"
		);
		mysql_query("UPDATE bieganie SET blipsent=1 WHERE blip_id={$row[0]}");
	}
}

function get_user_summary($user) {
    $link = mysql_connect('localhost', 'cardinalpharma', 'tweets');
	if (!$link) die('Nie można się połaczyć: ' . mysql_error());
	$db_selected = mysql_select_db('cardinalpharma', $link);
	if (!$db_selected) die ('Nie można ustawić bazy : ' . mysql_error());
	mysql_query("SET character_set_client=utf-8", $link);
	mysql_query("SET character_set_connection=utf-8", $link);
	mysql_query("SET CHARSET utf8", $link);
	mysql_query("SET NAMES 'utf8' COLLATE 'utf8_polish_ci'", $link);

	$numruns = mysql_query(
					sprintf("select count(blip_id) from bieganie where user='%s'", mysql_real_escape_string($user))
				);
	$numruns_count = mysql_fetch_row($numruns);
	$numruns_count = $numruns_count[0];


	$totals = sprintf("SELECT sum(distance) as totaldistance, sum(duration) as totalduration FROM bieganie WHERE user='%s'", mysql_real_escape_string($user));
	$totalsres = mysql_query($totals);
	$results = mysql_fetch_row($totalsres);

	$totals2 = sprintf("SELECT sum(distance) as totaldistance, sum(duration) as totalduration FROM bieganie WHERE user='%s' AND duration>0", mysql_real_escape_string($user));
	$totalsres2 = mysql_query($totals2);
	$results2 = mysql_fetch_row($totalsres2);

	$string = '';
    if ($results2[1]!='') {
		$string = sprintf(
				"%d zalogowanych biegów. Total %01.2f km, avg. speed: %01.2f km/h, avg. pace %01.2f min/km, total time: %s. http://biegacze.adblix.pl/users/%s",
                 $numruns_count,
                 $results[0],
                 $results2[0]/($results2[1]/3600),
                 ($results2[1]/60) / $results2[0],
                 formatduration($results[1]),
				 $user
			);
	} else {
		$string = sprintf(
				"%d zapisanych biegów. Total km: %01.2f. Wprowadzaj czasy biegów aby otrzymać więcej danych. http://biegacze.adblix.pl/users/%s",
                 $numruns_count,
                 $results[0],
				 $user
			);
	}

	return $string;
}

/* ALGORYTM

http://biegacze.blip.pl/
user: biegacze
pass: runstats

1. skanujemy bliposferę - tag #bieganie
2. szukamy ciągu znaków "#bieganie ([0-9]+)[\.\,][kKmM]"
3. zapisujemy nadawcę, długość i czas+datę wysłania wiadomości
4. odsyłamy dm do nadawcy, że jego bieganie zostało zalogowane
*/

$regexp = "/#bieganie[\s]+([0-9]+)([\.\,]([0-9]+))*[\s]*km([\s]*;[\s]*([0-9]+:[0-9]+(:[0-9]+)*))*/sim";

$dm_runs = get_dm_messages('biegacze', 'runstats');
$tag_runs = get_tag_messages('bieganie', 50);
save_runs($dm_runs, $regexp);
save_runs($tag_runs, $regexp);
send_blip_confirmations();


/* PETLA PRZETWARZANIA ZAPYTAN PM
 * Algorytm:
 * 1. pobieramy wiadomosci PM
 * 2. zapisujemy id wiadomosci
 * 3. jesli rozne od tych, ktore juz zapisalismy, przetwarzamy
 * 4. mozliwe komendy: info, nikeplus, #bieganie
 * 5. >#bieganie -> przetwarzamy tak jak tagi
 * 5. >>info -> zwracamy ostatni bieg oraz sumaryczne podsumowanie
 * 6. >>nikeplus user pass -> zapisujemy dane konta w bazie i zwracamy potwierdzenie zapisu
 * 7. Koniec
*/

/* PETLA PRZETWARZANIA NIKEPLUS */
$info_regexp = "/^info$/i";
$nikeplus_regexp = "/^nikeplus[\s]+(.[^\s]+)[\s]+([^\s].+)$/sim";
$pm_commands = get_pm_messages('biegacze', 'runstats');
foreach ($pm_commands as $blip) {
	if ($blip->user_path == '/users/biegacze') continue;
	$blip_id = $blip->id;

	$blip_id_res = mysql_query("select blip_id from bieganie_pm_blip_id where blip_id = $blip_id");
	if ( mysql_num_rows($blip_id_res) > 0) continue;

	if (preg_match($info_regexp, $blip->body, $matches)) {
    	// wyslij dane uzytkownika $blip->user_path
		$to_user = str_replace('/users/', '', $blip->user_path);
		print "<h3>Mamy zapytanie info od $to_user</h3>";
		biegacz_senddm($to_user, get_user_summary($to_user), 'biegacze', 'runstats');
		mysql_query("insert into bieganie_pm_blip_id (blip_id) values ($blip_id)");
	} else if (preg_match($nikeplus_regexp, $blip->body, $matches)) {
		// zapisz dane nikeplus
		$to_user = str_replace('/users/', '', $blip->user_path);
		print "<h3>Mamy zapytanie nikeplus od " . str_replace('/users/', '', $blip->user_path) . "</h3>";
		// print "<h3>user: {$matches[1]}, pass: {$matches[2]}</h3>";

		$isactive = mysql_query(sprintf("select id from bieganie_nikeplus where user='%s'", $to_user));
		if (mysql_num_rows($isactive)>0) {
			// update
			mysql_query(sprintf("update bieganie_nikeplus set nikeplus_user='{$matches[1]}', nikeplus_pass='{$matches[2]}' where user='%s'", mysql_real_escape_string($to_user)));
			biegacz_senddm($to_user, "Dane autoryzacji do NikePlus zostały uaktualnione. Szczegóły http://biegacze.adblix.pl/users/$to_user", 'biegacze', 'runstats');
		} else {
			// dodanie nowego
			mysql_query(sprintf("insert into bieganie_nikeplus (user, nikeplus_user, nikeplus_pass) values ('%s', '{$matches[1]}', '{$matches[2]}')", mysql_real_escape_string($to_user)));
			biegacz_senddm($to_user, "Spięcie z usługą NikePlus zostało uaktywnione. Szczegóły http://biegacze.adblix.pl/users/$to_user", 'biegacze', 'runstats');
		}

		mysql_query("insert into bieganie_pm_blip_id (blip_id) values ($blip_id)");
	}
}

$dm_commands = get_dm_messages('biegacze', 'runstats');
foreach ($dm_commands as $blip) {
	if ($blip->user_path == '/users/biegacze') continue;
	$blip_id = $blip->id;

	$blip_id_res = mysql_query("select blip_id from bieganie_pm_blip_id where blip_id = $blip_id");
	if ( mysql_num_rows($blip_id_res) > 0) continue;

	if (preg_match($info_regexp, $blip->body, $matches)) {
    	// wyslij dane uzytkownika $blip->user_path
		$to_user = str_replace('/users/', '', $blip->user_path);
		print "<h3>Mamy zapytanie info od $to_user</h3>";
		biegacz_senddm($to_user, get_user_summary($to_user), 'biegacze', 'runstats');
		mysql_query("insert into bieganie_pm_blip_id (blip_id) values ($blip_id)");
	}
}
mysql_query("INSERT into cronjobs (name, status, lastrun) values ('biegacze 5min', 'OK', NOW())");
print "OK.";
?>