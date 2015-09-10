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
$arg2 = array(
		array('MKQK2ZP','4.7 silver 16gb'),
		array('MKQL2ZP','4.7 gold 16gb'),
		array('MKQJ2ZP','4.7 gray 16gb'),
		array('MKQM2ZP','4.7 rose 16gb'),
		array('MKQP2ZP','4.7 silver 64gb'),
		array('MKQQ2ZP','4.7 gold 64gb'),
		array('MKQN2ZP','4.7 gray 64gb'),
		array('MKQR2ZP','4.7 rose 64gb'),
		array('MKQU2ZP','4.7 silver 128gb'),
		array('MKQV2ZP','4.7 gold 128gb'),
		array('MKQT2ZP','4.7 gray 128gb'),
		array('MKQW2ZP','4.7 rose 128gb'),
		array('MKU22ZP','5.5 silver 16gb'),
		array('MKU32ZP','5.5 gold 16gb'),
		array('MKU12ZP','5.5 gray 16gb'),
		array('MKU52ZP','5.5 rose 16gb'),
		array('MKU72ZP','5.5 silver 64gb'),
		array('MKU82ZP','5.5 gold 64gb'),
		array('MKU62ZP','5.5 gray 64gb'),
		array('MKU92ZP','5.5 rose 64gb'),
		array('MKUE2ZP','5.5 silver 128gb'),
		array('MKUF2ZP','5.5 gold 128gb'),
		array('MKUD2ZP','5.5 gray 128gb'),
		array('MKUG2ZP','5.5 rose 128gb')
	);

$pushKeys = array(	'xxx' );

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
