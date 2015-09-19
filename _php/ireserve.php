<?php

// http://stackoverflow.com/questions/4901335/cronjob-every-minute
$phone_def = array(
	array('ML7C2CH/A','ireserve test'),
	array('MKU52ZP/A','5.5 rose 16gb'),
	array('MKU92ZP/A','5.5 rose 64gb'),
	array('MKUG2ZP/A','5.5 rose 128gb'),
	array('MKU32ZP/A','5.5 gold 16gb'),
	array('MKU82ZP/A','5.5 gold 64gb'),
	array('MKUF2ZP/A','5.5 gold 128gb'),
	array('MKQM2ZP/A','4.7 rose 16gb'),
	array('MKQR2ZP/A','4.7 rose 64gb'),
	array('MKQW2ZP/A','4.7 rose 128gb'),
	array('MKQL2ZP/A','4.7 gold 16gb'),
	array('MKQQ2ZP/A','4.7 gold 64gb'),
	array('MKQV2ZP/A','4.7 gold 128gb'),
	array('MKU22ZP/A','5.5 silver 16gb'),
	array('MKU72ZP/A','5.5 silver 64gb'),
	array('MKUE2ZP/A','5.5 silver 128gb'),
	array('MKU12ZP/A','5.5 gray 16gb'),
	array('MKU62ZP/A','5.5 gray 64gb'),
	array('MKUD2ZP/A','5.5 gray 128gb'),
	array('MKQK2ZP/A','4.7 silver 16gb'),
	array('MKQP2ZP/A','4.7 silver 64gb'),
	array('MKQU2ZP/A','4.7 silver 128gb'),
	array('MKQJ2ZP/A','4.7 gray 16gb'),
	array('MKQN2ZP/A','4.7 gray 64gb'),
	array('MKQT2ZP/A','4.7 gray 128gb')
);

$pushKeys = array();

$pushUrl = 'https://api.pushbullet.com/v2/pushes';
$debug = false || ($_GET['debug'] == 1);

$store_def = array (
	array('R485', 'KLT'),
	array('R409', 'CWB'),
	array('R428', 'IFC'),
	array('R499', 'TST')
);

$jsonUrl = 'https://reserve.cdn-apple.com/HK/en_HK/reserve/iPhone/availability.json';

if($debug) {
	// mainland store IDs for testing
	$jsonUrl = 'https://reserve.cdn-apple.com/CN/zh_CN/reserve/iPhone/availability.json';

	$store_def = array (
		array('R448', 'R448'),
		array('R534', 'R534'),
		array('R479', 'R479'),
		array('R502', 'R502'),
		array('R359', 'R359'),
		array('R532', 'R532'),
		array('R389', 'R389'),
		array('R401', 'R401'),
		array('R643', 'R643'),
		array('R574', 'R574'),
		array('R388', 'R388'),
		array('R476', 'R476'),
		array('R637', 'R637'),
		array('R484', 'R484'),
		array('R572', 'R572'),
		array('R573', 'R573'),
		array('R320', 'R320'),
		array('R471', 'R471'),
		array('R480', 'R480'),
		array('R390', 'R390')
	);
}

// GOGOGO
$json = file_get_contents($jsonUrl);
$obj = json_decode($json, true);
$hit = false;

foreach($store_def as $store) {

	if($debug) {
		var_dump($store[0]);
	}

	$offers = $obj[$store[0]];

	foreach($phone_def as $phone) {
		if($offers[$phone[0]] && $offers[$phone[0]] !== 'CONTRACT' && $offers[$phone[0]] !== 'NONE') {

			foreach ($pushKeys as $pushKey) {
				$queryData['type'] = 'link';
				$queryData['title'] = $store[1] . " " . $phone[1];
				$queryData['url'] = 'https://reserve.cdn-apple.com/HK/zh_HK/reserve/iPhone/availability?channel=1&appleCare=N&iPP=N&partNumber=' . $phone[0] . '&returnURL=http%3A%2F%2Fwww.apple.com%2Fhk%2Fshop%2Fbuy-iphone%2Fiphone6s';
				push_content($pushUrl, $pushKey, 'POST', $queryData);
			}
		}

		if($debug) {	
			var_dump($offers[$phone[0]]);
		}
	}
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
