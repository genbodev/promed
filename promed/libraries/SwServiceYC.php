<?php

class SwServiceYC {
	private $url = null;
	private $timeout = 30;
	private $proxy_host;
	private $proxy_port;
	private $proxy_login;
	private $proxy_password;

	public function __construct($config) {
		// Переменные класса
		$this->url = $config['url'];
		$this->timeout = $config['timeout'];

		$this->proxy_host = $config['proxy_host'] ?? null;
		$this->proxy_port = $config['proxy_port'] ?? null;
		$this->proxy_login = $config['proxy_login'] ?? null;
		$this->proxy_password = $config['proxy_password'] ?? null;
	}

	public function data() {
		if ((extension_loaded('curl') !== true) || (is_resource($service = curl_init()) !== true)) {
			throw new Exception('Необходимо подключить расширение curl.');
		}
		$result = array();

		curl_setopt($service, CURLOPT_URL, $this->url);
		curl_setopt($service, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($service, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($service, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		curl_setopt($service, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($service, CURLOPT_HEADER, 0);

		// прокси
		if ($this->proxy_host && $this->proxy_port) {
			curl_setopt($service, CURLOPT_PROXY,          $this->proxy_host.':'.$this->proxy_port);
			curl_setopt($service, CURLOPT_PROXYTYPE,      CURLPROXY_HTTP);
			if ($this->proxy_login && $this->proxy_password) {
				curl_setopt($service, CURLOPT_PROXYUSERPWD, $this->proxy_login . ':' . $this->proxy_password);
			}
		}

		$response = curl_exec($service);
		$response_code = curl_getinfo($service, CURLINFO_HTTP_CODE);

		if ($response_code != 200) {
			$err = curl_error($service);
			$err = "Ресурс вернул код ошибки: {$response_code}, ответ: {$response}";
			$result['success'] = false;
			$result['errorMsg'] = 'Ошибка взаимодействия с сервисом: ' . $err;
			$result['errorCode'] = $response_code;
		} else if (mb_strpos($response, '<АккредитованныеУдостоверяющиеЦентры') !== false) {
			$result = $response;
		} else {
			$result = json_decode($response);
		}

		return $result;
	}
}