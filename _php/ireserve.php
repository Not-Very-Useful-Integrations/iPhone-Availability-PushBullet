<?php

$pushKeys = array(	'v16KqXNi5pRkpQUX47mXZ3iuTMcQFmNAZoujxBr1bny5k', 
					'b5SpPpOe8B9CGb976YcQbm5nwiFCtVwV',
					'v1D50EttDgOqwNaRmmLccqDPRbLgtef2oLujxOSx9N7n2' );

$pushUrl = 'https://api.pushbullet.com/v2/pushes';
$debug = false || ($_GET['debug'] == 1);

// GOGOGO
$json = file_get_contents('https://reserve.cdn-apple.com/HK/en_HK/reserve/iPhone/availability.json');
$obj = json_decode($json, true);
$hit = false;

foreach($obj as $store) {
	foreach($store as $phone) {

		if($debug) {
			var_dump($phone);
		}

		if($phone) {
			foreach ($pushKeys as $pushKey) {
				$queryData['type'] = 'link';
				$queryData['title'] = 'iReserve Opens';
				$queryData['url'] = 'https://reserve-hk.apple.com/HK/zh_HK/reserve/iPhone';
				push_content($pushUrl, $pushKey, 'POST', $queryData);
			}
			$hit = true;
			break;
		}
	}

	if($hit) {
		break;
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
