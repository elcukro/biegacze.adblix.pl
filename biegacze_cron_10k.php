<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>biegacze</title>
</head>
<body>
<pre>
<?php
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
/* ALGORYTM

http://biegacze.blip.pl/
user: biegacze
pass: runstats

1. skanujemy bliposferę - tag #bieganie
2. szukamy ciągu znaków "#bieganie ([0-9]+)[\.\,][kKmM]"
3. zapisujemy nadawcę, długość i czas+datę wysłania wiadomości
4. odsyłamy dm do nadawcy, że jego bieganie zostało zalogowane

*/

$link = mysql_connect('localhost', 'cardinalpharma', 'tweets');
if (!$link) die('Nie można się połaczyć: ' . mysql_error());
$db_selected = mysql_select_db('cardinalpharma', $link);
if (!$db_selected) die ('Nie można ustawić bazy : ' . mysql_error());
mysql_query("SET character_set_client=utf-8", $link);
mysql_query("SET character_set_connection=utf-8", $link);
mysql_query("SET CHARSET utf8", $link);
mysql_query("SET NAMES 'utf8' COLLATE 'utf8_polish_ci'", $link);


for ($i=0; $i<10000; $i=$i+50) {
	GLOBAL $HTTP_CODE;
	$ch = @curl_init('http://api.blip.pl/');
	if (!$ch) {
		print('CURL initialize error: '. curl_error($ch) . ' ' . curl_errno($ch));
	}
	$url = "http://api.blip.pl/tags/bieganie?limit=50&offset=$i";

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
	print "<h3>HTTP CODE: " . $HTTP_CODE . "</h3>";
	//print "<h3>curl_exec:</h3>";
	// print_r($obj);
	// print "<hr>";

	$regexp = "/#bieganie[\s]+([0-9]+)([\.\,]([0-9]+))*[\s]*km([\s]*;[\s]*([0-9]+:[0-9]+(:[0-9]+)*))*/sim";

	foreach ($obj as $blip) {
		if (preg_match($regexp, $blip->body, $matches)) {
			// print_r($matches);
			if ($duration = getduration($matches)) {
				print "<h3>".str_replace('/users/', '', $blip->user_path).": ".floatval($matches[1].".".$matches[3])."km on ".$blip->created_at." [dur. $duration]</h3>";
				$runsql = sprintf("INSERT IGNORE INTO bieganie (blip_id, user, distance, rundate, blipsent, duration, bliptxt) VALUES (%d, '%s', %f, '%s', 0, %d, '%s')",
									$blip->id,
									str_replace('/users/', '', $blip->user_path),
									floatval($matches[1].".".$matches[3]),
									$blip->created_at,
									$duration,
									mysql_real_escape_string($blip->body)
								);
				// print "<br>$runsql<br>\n";
				mysql_query($runsql);
			} else {
				print "<h3>".str_replace('/users/', '', $blip->user_path).": ".floatval($matches[1].".".$matches[3])."km on ".$blip->created_at."</h3>";
				$runsql = sprintf("INSERT IGNORE INTO bieganie (blip_id, user, distance, rundate, blipsent, bliptxt) VALUES (%d, '%s', %f, '%s', 0, '%s')",
									$blip->id,
									str_replace('/users/', '', $blip->user_path),
									floatval($matches[1].".".$matches[3]),
									$blip->created_at,
									mysql_real_escape_string($blip->body)
								);
				// print "<br>$runsql<br>\n";
				mysql_query($runsql);
			}
		}
	}
}
?>
</pre>
</body>
</html>