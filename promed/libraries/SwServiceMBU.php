<?php

class SwServiceMBU {
	private $url = null;
	private $timeout = 600;
	private $proxy_host;
	private $proxy_port;
	private $proxy_login;
	private $proxy_password;
	
	public function __construct($config) {
		// Переменные класса
		$this->url = $config['apiurl'];
		$this->timeout =$config['timeout'];
		
		$this->proxy_host = isset($config['proxy_host'])?$config['proxy_host']:null;
		$this->proxy_port = isset($config['proxy_port'])?$config['proxy_port']:null;
		$this->proxy_login = isset($config['proxy_login'])?$config['proxy_login']:null;
		$this->proxy_password = isset($config['proxy_password'])?$config['proxy_password']:null;
	}
	/**
	 * Передача данных
	 */ 
	public function data($methodType, $params = array(), $data = null, $log = null, $objectId = null) {
		$service = curl_init();
		$result = array();
		$url = $this->url;
		
		curl_setopt($service, CURLOPT_URL, $url);
		curl_setopt($service, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		curl_setopt($service, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($service, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($service, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($service, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($service, CURLOPT_POST, false);
		curl_setopt($service, CURLINFO_HEADER_OUT, true);
		$json = json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); // не экранируем cлеши и не переводим русский текст в unicode
		if ($methodType == 'POST') {
			curl_setopt($service, CURLOPT_POST, true);
		}
		if ($methodType == 'PUT') {
			curl_setopt($service, CURLOPT_CUSTOMREQUEST, 'PUT');
		}
		curl_setopt($service, CURLOPT_POSTFIELDS, $json);
		// Авторизация по токену
		$headers = array(
			"Authorization: N3 ".$params['MbuLpu_Token'],
			"Accept: application/json",
			"Content-Type: application/json",
			"Accept: */*",
			"Cache-Control: no-cache",
			"Accept-Encoding: gzip, deflate",
			"Connection: keep-alive"
		);

		curl_setopt($service, CURLOPT_HTTPHEADER, $headers);
		// при необходимости использования прокси
		if ($this->proxy_host && $this->proxy_port) {
			curl_setopt($service, CURLOPT_PROXY, $this->proxy_host . ':' . $this->proxy_port);
			curl_setopt($service, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			if ($this->proxy_login && $this->proxy_password) {
				curl_setopt($service, CURLOPT_PROXYUSERPWD, $this->proxy_login . ':' . $this->proxy_password);
			}
		}
		
		$response = curl_exec($service);
		$errNo = curl_errno($service);
		$response_code = curl_getinfo($service, CURLINFO_HTTP_CODE);

		if (!empty($_REQUEST['getDebug'])) {
			echo "request url: ".$url."<br>";
			echo "request type: ".$methodType."<br>";
			echo "<textarea cols=150 rows=20>" . $json . "</textarea><br><br>";
			echo "response_code: " . $response_code."<br>";
			echo "<textarea cols=150 rows=20>" . $response . "</textarea><br><br>";
		}

		$resp = null;
		/*switch ($method) {
			case 'Person':
				$resp = $log->addPackage(null, $objectId, $requestId, null, 'RECEPT_NUM');
				break;
			case 'Data':
				$resp = $log->addPackage('EvnRecept', $objectId, $requestId, null, 'RECEPT_UPD', 'Insert');
				break;
		}
		*/
		if (!empty($errNo)) {
			$response = strip_tags($response);
			$err = "Сервис вернул номер ошибки: {$errNo}, ответ: {$response}";
			$result['success'] = false;
			$result['errorMsg'] = 'Ошибка взаимодействия с сервисом: ' . $err;
			$result['errorCode'] = $errNo;
		}
		elseif (!in_array($response_code, array(200,201))) {
			$response = strip_tags($response);
			$err = "Сервис вернул код ошибки: {$response_code}, ответ: {$response}";
			$result['success'] = false;
			$result['errorMsg'] = 'Ошибка взаимодействия с сервисом: ' . $err;
			$result['errorCode'] = $response_code;
		}
		else {
			$result = json_decode($response, true);
		}
		
		return $result;
	}
}
