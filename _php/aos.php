<?php

function get_content($URL){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $URL);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

function push_content($url, $key, $method, $data = NULL, $sendAsJSON = TRUE, $auth = TRUE)
{
	$curl = curl_init();
	if ($method == 'GET' && $data !== NULL) {
		$url .= '?' . http_build_query($data);
	}
	curl_setopt($curl, CURLOPT_URL, $url);
	if ($auth) {
		curl_setopt($curl, CURLOPT_USERPWD, $key);
	}
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
	if ($method == 'POST' && $data !== NULL) {
		if ($sendAsJSON) {
			$data = json_encode($data);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($data)
				));
		}
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	}
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($curl, CURLOPT_HEADER, FALSE);
	$response = curl_exec($curl);
	if ($response === FALSE) {
		$curlError = curl_error($curl);
		curl_close($curl);
		throw new Exception('cURL Error: ' . $curlError);
	}
	$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if ($httpCode >= 400) {
		curl_close($curl);
		throw new Exception('HTTP Error ' . $httpCode);
	}
	curl_close($curl);
	return json_decode($response);
}

function startsWith($haystack, $needle)
{
	return $needle === "" || strpos($haystack, $needle) === 0;
}

function joinPaths() {
	$args = func_get_args();
	$paths = array();
	foreach ($args as $arg) {
		$paths = array_merge($paths, (array)$arg);
	}

	$paths = array_map(create_function('$p', 'return trim($p, "/");'), $paths);
	$paths = array_filter($paths);
	return join('/', $paths);
}

$baseurl = 'http://store.apple.com/hk/buy-%s/%s?cppart=UNLOCKED/WW&product=%s/A&step=accessories';
$arg0 = 'iphone';
$arg1 = 'iphone6';
$arg2 = array(	array('MGAC2ZP', 'iP 6+ Grey 128G'), 
				array('MGAE2ZP', 'iP 6+ Silver 128G'),
				array('MGAF2ZP', 'iP 6+ Gold 128G'),
				array('MGAH2ZP', 'iP 6+ Grey 64G'), 
				array('MGAJ2ZP', 'iP 6+ Silver 64G'),
				array('MGAK2ZP', 'iP 6+ Gold 64G'),
				array('MGA82ZP', 'iP 6+ Grey 16G'), 
				array('MGA92ZP', 'iP 6+ Silver 16G'),
				array('MGAA2ZP', 'iP 6+ Gold 16G'),
array('MG4A2ZP', 'iP 6 Grey 128G'), 
				array('MG4C2ZP', 'iP 6 Silver 128G'),
				array('MG4E2ZP', 'iP 6 Gold 128G'),
				array('MG4F2ZP', 'iP 6 Grey 64G'), 
				array('MG4H2ZP', 'iP 6 Silver 64G'),
				array('MG4J2ZP', 'iP 6 Gold 64G'),
				array('MG472ZP', 'iP 6 Grey 16G'), 
				array('MG482ZP', 'iP 6 Silver 16G'),
				array('MG492ZP', 'iP 6 Gold 16G')
				 );

$pushKeys = array(	'v16KqXNi5pRkpQUX47mXZ3iuTMcQFmNAZoujxBr1bny5k', 
					'b5SpPpOe8B9CGb976YcQbm5nwiFCtVwV',
					'v1D50EttDgOqwNaRmmLccqDPRbLgtef2oLujxOSx9N7n2' );

$pushUrl = 'https://api.pushbullet.com/v2/pushes';
$debug = false || ($_GET['debug'] == 1);

for($i = 0; $i < count($arg2); $i++) {

	$eachUrl = sprintf($baseurl, $arg0, $arg1, $arg2[$i][0]);
	$html = get_content($eachUrl);

	$dom = new DOMDocument;
	$dom->loadHTML($html);
	$xpath = new DOMXPath($dom);

	$exp = "//span[contains(@class, 'customer_commit_display')]";
	$srcNodes = $xpath->query($exp);

	if(!is_null($srcNodes) && $srcNodes->length > 0) {
		$msg = $srcNodes->item(0)->nodeValue;
		printf('%s: %s (<a href="%s">Link</a>) <br />', $arg2[$i][1], $msg, $eachUrl);

		if($msg != "Currently unavailable") {
			$queryData['type'] = 'link';
			$queryData['title'] = $arg2[$i][1];
			$queryData['url'] = $eachUrl;
			foreach ($pushKeys as $pushKey) {
				push_content($pushUrl, $pushKey, 'POST', $queryData);
			}
		} elseif($debug) {
			$queryData['type'] = 'link';
			$queryData['title'] = 'Unavailable: ' . $arg2[$i][1];
			$queryData['url'] = $eachUrl;
			foreach ($pushKeys as $pushKey) {
				push_content($pushUrl, $pushKey, 'POST', $queryData);
			}
		}
	}
}
