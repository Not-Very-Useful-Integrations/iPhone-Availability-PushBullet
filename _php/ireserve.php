<?php

$phone_def = array(	
	array('MGAA2ZP/A', '+GO S'),
	array('MGA92ZP/A', '+SV S'),
	array('MGA82ZP/A', '+GY S'), 
	array('MGAK2ZP/A', '+GO M'),
	array('MGAJ2ZP/A', '+SV M'),
	array('MGAH2ZP/A', '+GY M'), 
	array('MGAF2ZP/A', '+GO L'),
	array('MGAE2ZP/A', '+SV L'),
	array('MGAC2ZP/A', '+GY L'), 
	array('MG492ZP/A', '-GO S'),
	array('MG482ZP/A', '-SV S'),
	array('MG472ZP/A', '-GY S'), 
	array('MG4J2ZP/A', '-GO M'),
	array('MG4H2ZP/A', '-SV M'),
	array('MG4F2ZP/A', '-GY M'), 
	array('MG4E2ZP/A', '-GO L'),
	array('MG4C2ZP/A', '-SV L'),
	array('MG4A2ZP/A', '-GY L')
);

$store_def = array (
	array('R485', 'KLT'),
	array('R409', 'CWB'),
	array('R428', 'IFC')
);

$pushKeys = array(	'v16KqXNi5pRkpQUX47mXZ3iuTMcQFmNAZoujxBr1bny5k', 
					'b5SpPpOe8B9CGb976YcQbm5nwiFCtVwV',
					'v1D50EttDgOqwNaRmmLccqDPRbLgtef2oLujxOSx9N7n2',
					'HjClrqRzVSPINuJNkqta2kgEvEmGo9j7',
					'ZIN4eHIi4bxANrbftKsy2LCRIpcLA6Sg' );

$pushUrl = 'https://api.pushbullet.com/v2/pushes';
$debug = false || ($_GET['debug'] == 1);

// GOGOGO
$json = file_get_contents('https://reserve.cdn-apple.com/HK/en_HK/reserve/iPhone/availability.json');
$obj = json_decode($json, true);
$hit = false;

foreach($store_def as $store) {

	if($debug) {
		var_dump($store[0]);
	}

	$pushMsg = '';
	$offers = $obj[$store[0]];

	foreach($phone_def as $phone) {
		if($offers[$phone[0]]) {
			$pushMsg = $pushMsg . $phone[1] . ' ';
		}

		if($debug) {	
			var_dump($offers[$phone[0]]);
		}
	}

	if($pushMsg !== '') {
		foreach ($pushKeys as $pushKey) {
			$queryData['type'] = 'link';
			$queryData['title'] = $store[1] . " " . $pushMsg;
			$queryData['url'] = 'https://reserve-hk.apple.com/HK/zh_HK/reserve/iPhone';
			push_content($pushUrl, $pushKey, 'POST', $queryData);
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
