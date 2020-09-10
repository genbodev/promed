<?php

class SwServiceNSI {
	private $url = null;
	private $timeout = 30;
	private $userkey;
	private $proxy_host;
	private $proxy_port;
	private $proxy_login;
	private $proxy_password;

	public $auth_error;

	public function __construct($config) {
		// Переменные класса
		$this->url = $config['apiurl'];
		$this->timeout =$config['timeout'];
		$this->userkey = $config['userkey'];

		$this->proxy_host = isset($config['proxy_host'])?$config['proxy_host']:null;
		$this->proxy_port = isset($config['proxy_port'])?$config['proxy_port']:null;
		$this->proxy_login = isset($config['proxy_login'])?$config['proxy_login']:null;
		$this->proxy_password = isset($config['proxy_password'])?$config['proxy_password']:null;
	}

	public function data($method, $data = null) {
		$service = curl_init();
		$result = array();

		$url = $this->url . $method . '?userKey=' . $this->userkey;
		if (!empty($data)) {
			$url .= '&' . http_build_query($data);
		}

		curl_setopt($service, CURLOPT_URL, $url);
		curl_setopt($service, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		curl_setopt($service, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($service, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($service, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($service, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($service, CURLOPT_POST, false);

		// прокси
		if ($this->proxy_host && $this->proxy_port) {
			curl_setopt($service, CURLOPT_PROXY, $this->proxy_host . ':' . $this->proxy_port);
			curl_setopt($service, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			if ($this->proxy_login && $this->proxy_password) {
				curl_setopt($service, CURLOPT_PROXYUSERPWD, $this->proxy_login . ':' . $this->proxy_password);
			}
		}

		$response = curl_exec($service);
		$response_code = curl_getinfo($service, CURLINFO_HTTP_CODE);

		if ($response_code != 200) {
			$err = curl_error($service);
			$err = "Ресурс $method вернул код ошибки: {$response_code}, ответ: {$response}";
			$result['success'] = false;
			$result['errorMsg'] = 'Ошибка взаимодействия с сервисом: ' . $err;
			$result['errorCode'] = $response_code;
		} else {
			$result = json_decode($response, true);
		}

		return $result;
	}
}