<?php
function showjpg($path) {
	header('Content-Type: image/jpeg');
    header('Content-Length: ' . filesize($path));
    readfile($path);
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

	/*
	if ($debug) {
		curl_setopt($ch, CURLOPT_HEADER, true); // Display headers
		curl_setopt($ch, CURLOPT_VERBOSE, true); // Display communication with server
	}
	*/

	# ustawiamy opcje
	curl_setopt_array($ch, $curlopts);
	$json = curl_exec($ch);
	$HTTP_CODE = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	$obj = json_decode($json);
	return $obj;
}

if ($_GET['user']!='') {
	$user = $_GET['user'];
	$image_path = "./imgcache/$user.jpg";

	if ( file_exists($image_path) ) {
		showjpg($image_path);
		exit;
	} else {
		// zciagamy
		$avatar = getavatar($user);

		// zapisujemy
		$ch = curl_init("http://blip.pl".$avatar->url_30);
		$fh = fopen($image_path, "w");
		curl_setopt($ch, CURLOPT_FILE, $fh);
		curl_exec($ch);
		curl_close($ch);
		fclose($fh);

		// serwujemy
		showjpg($image_path);
		exit;
	}
}
?>