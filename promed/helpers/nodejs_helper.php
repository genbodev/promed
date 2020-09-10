<?php
/**
 * NodeJS_helper - хелпер с самыми базовыми функциями :)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 * @author       Dmitriy Vlasenko
 */

defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Отправка запроса серверу NODEJS и получение ответа
 */
function NodePostRequest($data, $config = null)
{

	//TODO: REMOVE!!
	//set_time_limit(200);

	if (empty($data) || !is_array($data)) {
		return array(array('success' => false, 'Error_Msg' => 'Отсутствуют параметры сообщения'));
	}

	if (empty($config) || !is_array($config)) {
		if (defined('NODEJS_SERVER_HOSTNAME') && defined('NODEJS_HTTPSERVER_PORT')) {
			$config = array(
				'host' => NODEJS_SERVER_HOSTNAME,
				'port' => NODEJS_HTTPSERVER_PORT
			);
		}
	}

	if (isset($config['host']) && isset($config['port'])) {
		if (!empty($_REQUEST['isDebug'])) {
			var_dump('host: '. $config['host']);
			var_dump('port: '. $config['port']);
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $config['host']);
		curl_setopt($ch, CURLOPT_PORT, $config['port']);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$POST = http_build_query($data, '', '&');
		if (!empty($_REQUEST['isDebug'])) {
			var_dump('post: '. $POST);
		}
		curl_setopt($ch, CURLOPT_POSTFIELDS, $POST);
		$body = curl_exec($ch);

		if ($body === false) {
			return array(array('success' => false, 'Error_Msg' => 'Ошибка соединения с сервером NODEJS: ' . curl_error($ch)));
		} else {
			return array(array('success' => true, 'data' => $body, "Error_Code" => null, "Error_Msg" => null));
		}

		curl_close($ch);
	} else {
		return array(array('success' => false, 'Error_Msg' => 'Невозвможно соединиться с сервером NODEJS'));
	}
}

?>