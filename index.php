<?php
function formatduration($secs) {
	$hours = $secs / 3600 % 24;
	$minutes = $secs / 60 % 60;
	$seconds = $secs % 60;

	if (strlen($hours)<2) $hours='0'.$hours;
	if (strlen($minutes)<2) $minutes='0'.$minutes;
	if (strlen($seconds)<2) $seconds='0'.$seconds;

	return join(':', array($hours, $minutes, $seconds));
}

function has_nikeplus($user) {
	$has = mysql_query("select id from bieganie_nikeplus where user='$user'");
	if (mysql_num_rows($has)>0) return true;
	else return false;
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
	<title>biegacze</title>
<style type="text/css">
body {
	margin: 20px;
	background-color: ##F8F8F8;
	font-family: Georgia, Times New Roman, serif;
	font-size: 9pt;
	color: #050505;
}

td, th {
  font-family: Helvetica, Verdana, Arial, sans-serif;
  font-size: 8pt;
}

th {
	font-weight: bold;
	font-size: 9pt;
}


</style>
</head>
<body>

<?php
$sumares = mysql_query("SELECT sum(distance) FROM bieganie");
$suma = mysql_fetch_row($sumares);
$suma = $suma[0];

$biegaczeres = mysql_query("SELECT COUNT( DISTINCT user) FROM bieganie AS ilosc");
$biegacze = mysql_fetch_row($biegaczeres);
$biegacze = $biegacze[0];
?>

<div style="text-align: center; float: left; width: 180px; padding: 20px; margin: 20px; background-color: #6A7DFF; color: #ffffff">
<span style="font-size: 34pt; font-weight: bold"><?php print $suma ?></span><br>
<span style="font-size: 15pt">km w sumie od <?php print $biegacze ?> biegaczy!</span>
</div>
<div style="background-color: #FFFFCC; padding: 6px; width: 950px; font-size: 9pt">
<strong>Informacja:</strong> Jak dodać swoje biegi do rankingu?</br>
Wystarczy że po każdym biegu zalogujesz się na <a href="http://blip.pl" target="_blank">blip</a> i napiszesz wiadomość na kanale <a href="http://blip.pl/tags/bieganie" target="_blank">#bieganie</a> o treści:<br>
<br>
<tt>#bieganie 6,5km</tt><br>
<br>
a twój dystans razem z datą blipnięcia zostanie dodany do listy.<br>
<b>Info:</b> możesz też dodać po średniku informację o długości trwania biegu wg. formatu <i>hh:mm:ss</i>:<br />
<br />
<tt>#bieganie 13,75km;01:25:44</tt><br>
<br>
<b>TODO:</b><ul>
	<li>wysyłanie powiadomień raz dziennie z podsumowaniem zalogowanych biegów (ilość, sumaryczna długość)
	<li>Prywatność: możliwość zgłaszania wniosków o niezapisywanie wyników danego użytkownika
	<li>Prywatność: dystanse zgłaszane przez PM nie będą uwzględniane w publicznym rankingu na tej stronie
	<li>Stronicowanie długich wyników
	<li>bugfixy, regexp-fixy :) - chętni do pomocy proszeni o kontakt na elcukro(at)gmail.com
	<li style="font-size: 9pt">[ZROBIONE] Możliwość dodania informacji o czasie biegu
	<li style="font-size: 9pt">[ZROBIONE] uwzględnianie wiadomości kierowanych na konto <a href="biegacze.blip.pl" target="_blank">biegacze</a>
	<li style="font-size: 9pt">[ZROBIONE] Możliwość wysyłania zapytań o własne wyniki
</ul>
<a href="http://biegacze.blip.pl">Biegacze na blipie</a>
</div>
<div style="clear:both">

<hr>



<div style="width: 950px">

	<div style="float: left; width: 200px">
	<?php
	$top10sql = "SELECT sum(distance) as suma, user FROM bieganie GROUP BY user ORDER by suma DESC LIMIT 10";
	$top10res = mysql_query($top10sql);
	?>
	<h3>TOP 10 biegaczy</h3>
	<table border="1" cellspacing="0" cellpadding="4">
	<tr>
		<th>Użytkownik</th>
		<th>Dystans</th>
	</tr>
	<?php while ($row = mysql_fetch_row($top10res)) { ?>
	<tr>
		<td><a href="./users/<?php print $row[1] ?>" title="Szczegółowe statystyki <?php print $row[1] ?>"><img src="avatar/<?php print $row[1] ?>" border="0"> <?php print $row[1] ?></a><?php if (has_nikeplus($row[1])) print " <img border=\"0\" src=\"nikeplus.png\" width=\"25\" height=\"18\" alt=\"Konto spięte z usługą Nike+\" title=\"Konto spięte z usługą Nike+\"/>" ?></td>
		<td><?php print $row[0] ?> km</td>
	</tr>
	<?php } ?>
	</table>
	</div>


	<div style="width: 750px; float: right">
	<h3>Lista zapisanych biegów</h3>

	<?php
	$limit = 20;
	$max_offset = mysql_fetch_row(mysql_query('select count(blip_id) from bieganie'));
	$max_offset = $max_offset[0];
	
	$offset = $_GET['o'];

	if ( ($offset * $limit) > $max_offset ) $offset = 0;
	
	$next_link = "<a href='?o=".($offset+1)."'>następne &gt;&gt;</a>";
	$prev_link = "<a href='?o=".($offset-1)."'>&lt;&lt; poprzednie</a>";

	if ($offset==0) $prev_link = '';
	if (($offset+1)*$limit >= $max_offset) $next_link = '';

	$listasql = sprintf(
					"SELECT user, distance, rundate, duration, bliptxt, blip_id FROM bieganie ORDER by rundate DESC LIMIT %d OFFSET %d",
					$limit,
					$offset*$limit
				);
	$listares = mysql_query($listasql);
	?>
	<table border="1" cellspacing="0" cellpadding="4">
	<tr>
		<td colspan="4"><table border="0" width="100%"><tr><td align="left"><?php print $prev_link ?></td><td align="right"><?php print $next_link ?></td></tr></table></td>
	</tr>
	<tr>
		<th>Użytkownik</th>
		<th>Dystans</th>
		<th>Data i czas biegu</th>
		<th>Wiadomość</th>
	</tr>
	<?php while ($row = mysql_fetch_row($listares)) { ?>
	<tr valign="top">
		<td><a href="./users/<?php print $row[0] ?>" title="szczegółowe statystyki <?php print $row[0] ?>"><img src="avatar/<?php print $row[0] ?>" border="0"> <?php print $row[0] ?></a></td>
		<td><?php print $row[1].' km'; if (trim($row[3])!='') { print "<br>[".formatduration($row[3])."]"; } ?></td>
		<td><?php print $row[2] ?></td>
		<td><a href='http://blip.pl/s/<?php print $row[5] ?>' target="_blank" title='<?php print $row[4] ?>'><?php print $row[4] ?></td>
	</tr>
	<?php } ?>
	<tr>
		<td colspan="4"><table border="0" width="100%"><tr><td align="left"><?php print $prev_link ?></td><td align="right"><?php print $next_link ?></td></tr></table></td>
	</tr>
	</table>
	</div>

</div>

<br><br>

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