<?php
/* 
 * Хелпер для работы с CURL
 */

defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Отправка пуш уведомлений
 */
function sendPush($to, $data, $apiKey) {
	$url = 'https://fcm.googleapis.com/fcm/send';

	if (!empty($apiKey)) {
		$fields = array(
			"to" => $to,
			"data" => $data
		);

		$headers = array(
			'Authorization: key=' . $apiKey,
			'Content-Type: application/json'
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$output = curl_exec($ch);
		if ($output === false) {
			return curl_error($ch);
		}
		curl_close($ch);
		
		return $output;
	}

	return "empty apiKey";
}

