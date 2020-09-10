<?php
/* 
 * Хелпер для работы с CURL
 */

defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Отправка запроса через CURL
 * 
 * @param string $url URL
 * @param mixed $data Данные передаваемые в запросе массив, строка через & или null
 * Пока передавайте данные так: `http_build_query(array(), '', '&')`
 * или строкой: `param1=value&param2=value`
 * @param string $method Тип HTTP запроса GET, POST и тп
 * @param mixed $cookie Путь к файлу для хранения и чтения cookie или null
 * Пример:
 * 
 * ```php
 * 
 * $cookie_jar = strval(tempnam('/tmp', 'cookie'));
 * 
 * ```
 * 
 * @param mixed $options Массив настроек curl или null
 * @param int $retries Количество попыток установить соединение. По умолчанию 3.
 * @return boolean В случае успеха true
 */
function CURL($url, $data = null, $method = 'GET', $cookie = null, $options = null, $retries = 3){
	if ((extension_loaded('curl') !== true) || (is_resource($curl = curl_init()) !== true)) {
		throw new Exception('Необходимо подключить расширение curl.');
	}

	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_MAXREDIRS => 5,
		CURLOPT_TIMEOUT => 5,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_RETURNTRANSFER => true, // Возвращает HTML страницу и заголовки если запрос был удачным
		CURLOPT_HEADER => true,
		CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.3; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0",
		//CURLOPT_PROXY => $this->proxy_host,
		//CURLOPT_PROXYPORT => $this->proxy_port,
		//CURLOPT_PROXYTYPE => 'HTTPS',
		//CURLOPT_PROXYUSERPWD => $this->proxy_login . ':' . $this->proxy_passwd,
		CURLINFO_HEADER_OUT => true,
	));

	//curl_setopt($curl, CURLOPT_FAILONERROR, true);
	//curl_setopt($curl, CURLOPT_AUTOREFERER, true);

	if (!preg_match('#^(?:DELETE|GET|HEAD|OPTIONS|POST|PUT)$#i', $method)) {
		return false;
	}

	if (preg_match('#^(?:HEAD|OPTIONS)$#i', $method) > 0) {
		curl_setopt_array($curl, array(
			CURLOPT_NOBODY => true
		));

		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));
	} else if (preg_match('#^(?:POST|PUT)$#i', $method) > 0) {
		if (is_array($data) === true) {
			/*
			Закомментировал иначе не работает отправка файла
			foreach (preg_grep('#^@#', $data) as $key => $value) {
				$data[$key] = sprintf('@%s', rtrim(str_replace('\\', '/', realpath(ltrim($value, '@'))), '/') . (is_dir(ltrim($value, '@')) ? '/' : ''));
			}

			$data = http_build_query($data, '', '&');
			*/
		}

		if (preg_match('#^(?:POST)$#i', $method) > 0) {
			curl_setopt($curl, CURLOPT_POST, true);
		} else {
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		}

		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	} else {
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));
	}

	if (isset($cookie) === true) {
		curl_setopt_array($curl, array_fill_keys(array(CURLOPT_COOKIEJAR, CURLOPT_COOKIEFILE), strval($cookie)));
	}

	if ((intval(ini_get('safe_mode')) == 0) && (ini_set('open_basedir', null) !== false)) {
		curl_setopt_array($curl, array(CURLOPT_MAXREDIRS => 5, CURLOPT_FOLLOWLOCATION => true));
	}

	if (is_array($options) === true) {
		curl_setopt_array($curl, $options);
	}

	$result = false;

	for ($i = 1; $i <= $retries; ++$i) {
		session_write_close(); // Bag with no sending PHPSESSID (@link http://stackoverflow.com/questions/15627217/curl-not-passing-phpsessid)
		$result = curl_exec($curl);
		session_start();

		if (($i == $retries) || ($result !== false)) {
			break;
		}

		usleep(pow(2, $i - 2) * 1000000);
	}

	$info = curl_getinfo($curl);

	curl_close($curl);

	if (!$result) {
		return false;
	}

	return array(
		'data' => $result,
		'info' => $info,
	);
}

