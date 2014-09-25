<?php

$phone_def = array(	
	array('MGAA2ZP/A', '+GO 16'),
	array('MGA92ZP/A', '+SV 16'),
	array('MGA82ZP/A', '+GY 16'), 
	array('MGAK2ZP/A', '+GO 64'),
	array('MGAJ2ZP/A', '+SV 64'),
	array('MGAH2ZP/A', '+GY 64'), 
	array('MGAF2ZP/A', '+GO T8'),
	array('MGAE2ZP/A', '+SV T8'),
	array('MGAC2ZP/A', '+GY T8'), 
	array('MG492ZP/A', '-GO 16'),
	array('MG482ZP/A', '-SV 16'),
	array('MG472ZP/A', '-GY 16'), 
	array('MG4J2ZP/A', '-GO 64'),
	array('MG4H2ZP/A', '-SV 64'),
	array('MG4F2ZP/A', '-GY 64'), 
	array('MG4E2ZP/A', '-GO T8'),
	array('MG4C2ZP/A', '-SV T8'),
	array('MG4A2ZP/A', '-GY T8')
);

$store_def = array (
	array('R485', 'FW'),
	array('R409', 'CWB'),
	array('R428', 'IFC')
);

$pushKeys = array(	'v16KqXNi5pRkpQUX47mXZ3iuTMcQFmNAZoujxBr1bny5k' );

$pushUrl = 'https://api.pushbullet.com/v2/pushes';
$debug = false || ($_GET['debug'] == 1);

// GOGOGO
$json = file_get_contents('https://reserve.cdn-apple.com/HK/en_HK/reserve/iPhone/availability.json');

$json = '{
  "R485" : {
    "MGAF2ZP/A" : false,
    "MG492ZP/A" : false,
    "MGAC2ZP/A" : false,
    "MGA92ZP/A" : false,
    "MG4F2ZP/A" : false,
    "MG472ZP/A" : false,
    "MG4A2ZP/A" : false,
    "MGAK2ZP/A" : false,
    "MGAA2ZP/A" : false,
    "MG4J2ZP/A" : false,
    "MGAJ2ZP/A" : false,
    "MG4H2ZP/A" : false,
    "MGAE2ZP/A" : false,
    "MG4E2ZP/A" : false,
    "MG482ZP/A" : false,
    "MGAH2ZP/A" : false,
    "MG4C2ZP/A" : false,
    "MGA82ZP/A" : false
  },
  "R409" : {
    "MGAF2ZP/A" : false,
    "MG492ZP/A" : false,
    "MGAC2ZP/A" : false,
    "MGA92ZP/A" : false,
    "MG4F2ZP/A" : false,
    "MG472ZP/A" : true,
    "MG4A2ZP/A" : false,
    "MGAK2ZP/A" : false,
    "MGAA2ZP/A" : false,
    "MG4J2ZP/A" : false,
    "MGAJ2ZP/A" : false,
    "MG4H2ZP/A" : false,
    "MGAE2ZP/A" : false,
    "MG4E2ZP/A" : false,
    "MG482ZP/A" : false,
    "MGAH2ZP/A" : false,
    "MG4C2ZP/A" : false,
    "MGA82ZP/A" : false
  },
  "updated" : 1411198260116,
  "R428" : {
    "MGAF2ZP/A" : false,
    "MG492ZP/A" : false,
    "MGAC2ZP/A" : false,
    "MGA92ZP/A" : false,
    "MG4F2ZP/A" : false,
    "MG472ZP/A" : false,
    "MG4A2ZP/A" : false,
    "MGAK2ZP/A" : false,
    "MGAA2ZP/A" : false,
    "MG4J2ZP/A" : false,
    "MGAJ2ZP/A" : false,
    "MG4H2ZP/A" : false,
    "MGAE2ZP/A" : false,
    "MG4E2ZP/A" : false,
    "MG482ZP/A" : false,
    "MGAH2ZP/A" : false,
    "MG4C2ZP/A" : false,
    "MGA82ZP/A" : false
  }
}';

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
		var_dump($store[1] . ':' . $pushMsg);
		$queryData['type'] = 'link';
		$queryData['title'] = $store[1] . ' ' . $pushMsg;
		$queryData['url'] = 'https://reserve-hk.apple.com/HK/zh_HK/reserve/iPhone';
		push_content($pushUrl, $pushKey, 'POST', $queryData);
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
