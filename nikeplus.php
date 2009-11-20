<html>
<body>

<form method="post">
<h1>Nike+ fetch user data</h1>
Nike+ user: <input type="text" name="user"><br>
Nike+ pass: <input type="password" name="pass"><br>
<br>
<input type="submit" value="check">
</form>

<?php
if ($_POST['user']!='' && $_POST['pass']!='') {
	print "<pre>";
	date_default_timezone_set('Europe/Warsaw');

	include_once('nikeplus.class.php');
	$np = new NikePlus($_POST['user'], $_POST['pass'], './imgcache');

	// print_r($np->runs);
	// print_r($np->data);

	print "<strong>Summary:</strong>\n";
	print "Total # runs: " . $np->runs->runListSummary->runs . "\n";
	print "Total distance: " . sprintf("%01.2f", $np->runs->runListSummary->distance) . " km\n";
	print "Total duration: " . $np->duration($np->runs->runListSummary->duration) . "\n";
	print "Total cals burned: " . $np->runs->runListSummary->calories . "\n";
	print "Preffered run day: " . $np->data->userTotals->preferredRunDayOfWeek . "\n";
	print "Power song: " . $np->data->userOptions->powerSong->title . ", " . $np->data->userOptions->powerSong->album . " by " . $np->data->userOptions->powerSong->artist . "\n";

	print "<ol>";
	foreach ($np->runs->runList->run as $runs) {
		// print "<li>".date("d-m-Y G:i:s", date_parse($runs->startTime)).", ".sprintf("%01.2f", $runs->distance)." km, ".$np->duration($runs->duration)." pace: ".$np->pace($runs->distance,$runs->duration,"km")." min./km.</li>\n";
		print "<li>".date("d-m-Y G:i:s", strtotime($runs->startTime)).", ".sprintf("%01.2f", $runs->distance)." km, ".$np->duration($runs->duration)." pace: ".$np->pace($runs->distance,$runs->duration,"km")." min./km. [".$runs->calories." cal.]</li>\n";
	}
	print "</ol>";

	/*
	if ($_POST['userid']!='' && $_POST['url']!='') {
		print "<pre>";
		GLOBAL $HTTP_CODE;
		$ch = @curl_init('http://api.blip.pl/');
		if (!$ch) {
			print('CURL initialize error: '. curl_error($ch) . ' ' . curl_errno($ch));
		}
		$url = $_POST['url'] . $_POST['userid'];

		print "\nURL: $url\n";
		// BLIP
		$curlopts = array (
			CURLOPT_USERAGENT       => 'asystent/0.0.1 http://asystent.adblix.pl/',
			CURLOPT_HTTPHEADER		=> array('X-Blip-API: 0.02', 'Accept: application/json'),
			CURLOPT_RETURNTRANSFER  => 1,
			CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_0,
			CURLOPT_CONNECTTIMEOUT  => 10,
			CURLOPT_URL				=> $url,
		);

		if ($debug) {
			curl_setopt($ch, CURLOPT_HEADER, true); // Display headers
			curl_setopt($ch, CURLOPT_VERBOSE, true); // Display communication with server
		}

		# ustawiamy opcje
		curl_setopt_array($ch, $curlopts);
		$xml = curl_exec($ch);
		$HTTP_CODE = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		print "\nHTTP CODE: $HTTP_CODE\n";
		print "\nRESPONSE:\n";
		print "<textarea cols='100' rows='40'>";
		print str_replace('><', ">\n<", $xml);
		print "</textarea>";

		print "</pre>";
	}
	*/
	print "</pre>";
}
?>

</body>
</html>