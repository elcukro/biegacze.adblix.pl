<?php
$user = $_GET['user'];
if (trim($user)=='') {
	header("Location: index.php");
	exit(0);
}

function formatduration($secs) {
	$hours = $secs / 3600 % 24;
	$minutes = $secs / 60 % 60;
	$seconds = $secs % 60;

	if (strlen($hours)<2) $hours='0'.$hours;
	if (strlen($minutes)<2) $minutes='0'.$minutes;
	if (strlen($seconds)<2) $seconds='0'.$seconds;

	return join(':', array($hours, $minutes, $seconds));
}

function getavatar($user) {
	GLOBAL $HTTP_CODE;
	$ch = @curl_init('http://api.blip.pl/');
	if (!$ch) {
		print('CURL initialize error: '. curl_error($ch) . ' ' . curl_errno($ch));
	}
	$url = "http://api.blip.pl/users/$user/avatar";

	// print "\nURL: subscriptions url: $url\n";
	// BLIP
	$curlopts = array (
		CURLOPT_USERAGENT       => 'asystent/0.0.1 http://asystent.adblix.pl/',
		CURLOPT_HTTPHEADER		=> array('X-Blip-API: 0.02', 'Accept: application/json'),
		CURLOPT_RETURNTRANSFER  => 1,
		CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_0,
		CURLOPT_CONNECTTIMEOUT  => 10,
		CURLOPT_URL				=> $url,
	);

	# ustawiamy opcje
	curl_setopt_array($ch, $curlopts);
	$json = curl_exec($ch);
	$HTTP_CODE = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	$obj = json_decode($json);
	return $obj;
}

$link = mysql_connect('localhost', 'cardinalpharma', 'tweets');
if (!$link) die('Nie można się połaczyć: ' . mysql_error());
$db_selected = mysql_select_db('cardinalpharma', $link);
if (!$db_selected) die ('Nie można ustawić bazy : ' . mysql_error());
mysql_query("SET character_set_client=utf-8", $link);
mysql_query("SET character_set_connection=utf-8", $link);
mysql_query("SET CHARSET utf8", $link);
mysql_query("SET NAMES 'utf8' COLLATE 'utf8_polish_ci'", $link);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>biegacze / statystyki użytkownika <?php print $user ?></title>
<style type="text/css">
body {
	margin: 20px;
	background-color: ##F8F8F8;
	font-family: Georgia, Times New Roman, serif;
	font-size: 11pt;
	color: #050505;
}

td, th {
  font-family: Helvetica, Verdana, Arial, sans-serif;
  font-size: 12pt;
}

th {
	font-weight: bold;
	font-size: 13pt;
}
</style>
</head>
<body>
<?php
$sumares = mysql_query(
			sprintf(
				"SELECT sum(distance) FROM bieganie WHERE user = '%s'",
				mysql_real_escape_string($user))
		   );
$suma = mysql_fetch_row($sumares);
$suma = $suma[0];
?>

<div style="width: 950px">

<?php $avatar = getavatar($user); ?>
    <img src="http://blip.pl/<?php print $avatar->url_120 ?>" border="0"/>

	<h1><a href="/" title="Strona główna">Strona główna</a> / Statystyki użytkownika <?php print $user ?></h1>

	<hr />


<?php
$totals = sprintf("SELECT sum(distance) as totaldistance, sum(duration) as totalduration FROM bieganie WHERE user='%s'", mysql_real_escape_string($user));
$totalsres = mysql_query($totals);
$results = mysql_fetch_row($totalsres);

$totals2 = sprintf("SELECT sum(distance) as totaldistance, sum(duration) as totalduration FROM bieganie WHERE user='%s' AND duration>0", mysql_real_escape_string($user));
$totalsres2 = mysql_query($totals2);
$results2 = mysql_fetch_row($totalsres2);

?>
	<p>
	<span style="font-size: 16pt">

		Suma przebiegniętych kilometrów: <strong><?php print $results[0] ?> km.</strong><br />
        <?php if ($results2[1]!='') { ?>
		Czas poświęcony na bieganie: <strong><?php print formatduration($results[1]) ?></strong><br />
		<br />
		Średnia prędkość: <strong><?php print sprintf("%01.2f" , $results2[0]/($results2[1]/3600)) ?> km/h</strong><br/>
		<?php
          $minutes = $results[1]/60;

		?>
		Średnie tempo per kilometr: <strong><?php print sprintf("%01.2f" , ($results2[1]/60) / $results2[0]) ?> min.</strong><br />
		<?php } else { ?>
		<span style="font-size: 12pt">Użytkownik nie wprowadził danych o czasach biegów więc nie możemy wyliczyć średniego tempa.</span>
		<?php } ?>
	</span> <br />

<?php
$maxminsql = sprintf("SELECT max(distance), min(distance) FROM bieganie WHERE user = '%s' GROUP BY user", mysql_real_escape_string($user));
$maxminres = mysql_query($maxminsql);
$maxmin = mysql_fetch_row($maxminres);

$listasql = sprintf("SELECT distance, rundate FROM bieganie WHERE user = '%s' ORDER by rundate DESC", mysql_real_escape_string($user));
$listares = mysql_query($listasql);

$distance = array();
$rundate = array();
while ($row = mysql_fetch_row($listares)) {
 	$distance[] = $row[0];
	$rundate[] = substr($row[1], 0, 10);
}

$distance_list = implode(',', $distance);
$rundate_list = implode('|', $rundate);

sort($distance);
$range = implode('|', array_unique($distance));
?>

<!--
<br />
<img src="http://chart.apis.google.com/chart?
cht=lc
&chs=950x200
&chco=6A7DFF
&chtt=Przebiegnięte+kilometry+w+czasie
&chts=333333,18
&chdlp=t
&chdl=kilometry
&chxt=x,y
&chd=t:<?php print $distance_list ?>
&chds=0,<?php print $maxmin[0] ?>
&chxl=0:|<?php print $rundate_list ?>|1:|0|<?php print $range ?>|
" border="0" />
-->
</p>

	<h3>Lista zapisanych biegów</h3>

<?php
$listasql = sprintf("SELECT distance, rundate, duration, bliptxt, blip_id FROM bieganie WHERE user = '%s' ORDER by rundate DESC", mysql_real_escape_string($user));
$listares = mysql_query($listasql);
?>
	<table border="1" cellspacing="0" cellpadding="4" width="950">
	<tr>
		<th>Dystans</th>
		<th>Czas</th>
		<th>Data i czas biegu</th>
		<th>Wiadomość</th>
	</tr>
	<?php while ($row = mysql_fetch_row($listares)) { ?>
	<tr valign="top">
		<td><?php print $row[0].' km'; ?></td>
		<td><?php if (trim($row[2])!='') { print formatduration($row[2]); } else { print "--"; } ?></td>
		<td><?php print $row[1] ?></td>
		<td><a href='http://blip.pl/s/<?php print $row[4] ?>' target="_blank" title='<?php print $row[3] ?>'><?php print $row[3] ?></td>
	</tr>
	<?php } ?>
	</table>

<?php
$nikeplusres = mysql_query(sprintf("select nikeplus_user, nikeplus_pass from bieganie_nikeplus where user='%s'", mysql_real_escape_string($user)));
if (mysql_num_rows($nikeplusres)>0) {
?>
	<h3>Dane z usługi Nike+ [wkrótce możliwość synchronizacji]</h3>

<?php
	include_once('nikeplus.class.php');

	date_default_timezone_set('Europe/Warsaw');
	$nikeplus_auth_data = mysql_fetch_row($nikeplusres);
	$np = new NikePlus($nikeplus_auth_data[0], $nikeplus_auth_data[1], './imgcache');

	print "<strong>Podsumowanie:</strong><ul>";
	print "<li>Total # biegów: " . $np->runs->runListSummary->runs . "\n";
	print "<li>Total dystans: " . sprintf("%01.2f", $np->runs->runListSummary->distance) . " km\n";
	print "<li>Total czas: " . $np->duration($np->runs->runListSummary->duration) . "\n";
	print "<li>Spalonych kalorii: " . $np->runs->runListSummary->calories . "\n";
	print "<li>Ulubiony dzień do biegania: " . $np->data->userTotals->preferredRunDayOfWeek . "\n";
	print "<li>Power song: " . $np->data->userOptions->powerSong->title . ", " . $np->data->userOptions->powerSong->album . " autor: " . $np->data->userOptions->powerSong->artist;
	print "</ul>";

	print "<br><strong>Lista biegów:</strong><ol>";
	foreach ($np->runs->runList->run as $runs) {
		print "<li>".date("d-m-Y G:i:s", strtotime($runs->startTime)).", ".sprintf("%01.2f", $runs->distance)." km, ".$np->duration($runs->duration)." tempo: ".$np->pace($runs->distance,$runs->duration,"km")." min./km. [".$runs->calories." kal.]</li>\n";
	}
	print "</ol>";
} else { ?>

	<h3>Czy wiesz już o Nike+?</h3>
	<strong>Twoje konto nie zostało jeszcze połączone z usługą Nike+</strong><br />
	<br />
	Czy wiesz że możesz połączyć swoje konto na blipie z Twoim kontem Nike+?<br />
	Jeśli używasz systemu Nike+ do logowania biegów, biegacz może (opcja dostępna w niedalekiej przyszłości) przesyłać na blipa informacje o Twoich biegach zalogowanych przez Nike+!<br />
	Już więcej nie trzeba będzie dublować wpisów.<br />
	<br />
	Aby to zrobić, wyślij na konto <a href="http://biegacze.blip.pl/" target="_blank">biegacze</a> wiadomość o następującej treści:<br />
    <br />
	<tt>&gt;&gt;biegacze: nikeplus [nazwa_użytkownika_nike+] [hasło_nike+]</tt><br />
    <br />
	na przykład:<br />
	<tt>&gt;&gt;biegacze: nikeplus adam@nowacki.pl mojeHaslo</tt><br />
	<br />
	Biegacz potwierdzi fakt dodania konta odpowiednią wiadomością.<br />
	Od tego czasu Twoje dane Nike+ będą dostępne na Twojej stronie. Więcej szczegółów już wkrótce!
<?php } ?>
</div>

<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-69271-19");
pageTracker._trackPageview();
} catch(err) {}</script>
</body>
</html>
