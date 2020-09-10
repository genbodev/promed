<?php

class SwServiceEFIS {
	private $url = null;
	private $authurl = null;
	private $timeout = 30;
	private $user;
	private $password;
	private $proxy_host;
	private $proxy_port;
	private $proxy_login;
	private $proxy_password;

	public $auth_error;

	public function __construct($config) {
		// Переменные класса
		$this->url = $config['apihost'].$config['apiurl'];
		$this->authurl = $config['authhost'].$config['authurl'];
		$this->timeout =$config['timeout'];
		$this->user = $config['user'];
		$this->password = $config['password'];

		$this->proxy_host = isset($config['proxy_host'])?$config['proxy_host']:null;
		$this->proxy_port = isset($config['proxy_port'])?$config['proxy_port']:null;
		$this->proxy_login = isset($config['proxy_login'])?$config['proxy_login']:null;
		$this->proxy_password = isset($config['proxy_password'])?$config['proxy_password']:null;
		// Авторизуемся сразу
		$this->auth();
	}

	public function auth() {
		$data = json_encode(array('login' => $this->user, 'password' => $this->password));
		$result = $this->data($this->authurl, 'auth', $data);
		if (is_object($result)) {
			// Ключ доступа к API
			if (isset($result->access_token)) {
				$this->apikey = ucfirst($result->token_type)." ".$result->access_token;
			} else {
				// Не получилось авторизоваться, вернем ошибку
				echo 'Авторизация в сервисе ЕФИС не выполнена! '/*.var_export($result,true)*/;
				return false;
			}
		}
		if (is_array($result) && !empty($result['errorMsg'])) {
			if ($result['errorCode'] == 400) {
				$result['errorMsg'] = 'Не удалось выполнить авторизацию в сервисе ЕФИС';
			}
			$this->auth_error = $result;
			return false;
		}
		return true;
	}

	public function data($method, $type = 'get', $data = null, $headers=array(), $returnArray = false) {
		$service = curl_init();
		$result = array();
		if (is_array($this->auth_error)) {
			return $this->auth_error;
		}
		if (count($headers)==0) { // Если передан хидер, то просто подменяем его
			$headers = array(
				"Content-Type: application/json; charset=utf-8",
				"Cache-Control: no-cache",
				"Pragma: no-cache",
				"Accept-Charset: UTF-8",
				//"Accept: application/atom+xml"
			);
			if (isset($this->apikey)) {
				$headers[] = "Authorization: ".$this->apikey;
			}
			if (strlen($data)>0) {
				$headers[] = "Content-length: ".strlen($data);
			}
		}

		if ($type == 'auth') { // если авторизация, то адрес другой
			$url = $method;
		} else {
			$url = $this->url.$method;
		}
		curl_setopt($service, CURLOPT_URL, $url );
		// остальные параметры
		curl_setopt($service, CURLOPT_CONNECTTIMEOUT, $this->timeout );
		curl_setopt($service, CURLOPT_TIMEOUT,        $this->timeout );
		curl_setopt($service, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($service, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($service, CURLOPT_SSL_VERIFYHOST, false);
		//curl_setopt($service, CURLOPT_FAILONERROR, true);
		// Если метод передачи post или это авторизация
		if (strtolower($type)=='post' || $type=='auth') {
			curl_setopt($service, CURLOPT_POST,       true);
			curl_setopt($service, CURLOPT_POSTFIELDS, $data);
		} else {
			curl_setopt($service, CURLOPT_POST,       false);
		}
		if (strtolower($type)=='put') {
			curl_setopt($service, CURLOPT_PUT,        true);
			curl_setopt($service, CURLOPT_POSTFIELDS, $data);
		}
		if (strtolower($type)=='delete') {
			curl_setopt($service, CURLOPT_CUSTOMREQUEST, 'DELETE');
			curl_setopt($service, CURLOPT_POSTFIELDS, $data);
		}
		// Заголовки
		curl_setopt($service, CURLOPT_HTTPHEADER,     $headers);
		// отладочные параметры
		// curl_setopt($service, CURLINFO_HEADER_OUT,    true);
		// curl_setopt($service, CURLOPT_HEADER,         true);
		// Прокси для тестового
		if ($this->proxy_host && $this->proxy_port) {
			curl_setopt($service, CURLOPT_PROXY,          $this->proxy_host.':'.$this->proxy_port);
			curl_setopt($service, CURLOPT_PROXYTYPE,      CURLPROXY_HTTP);
			if ($this->proxy_login && $this->proxy_password) {
				curl_setopt($service, CURLOPT_PROXYUSERPWD,   $this->proxy_login.':'.$this->proxy_password);
			}
		}
		$response = curl_exec($service);
		$response_code = curl_getinfo($service, CURLINFO_HTTP_CODE);

		/*echo '<pre>';
		print_r(array(
			$url,
			htmlentities($response),
			$response_code,
			curl_error($service)
		));*/

		//$sent_headers = curl_getinfo($service, CURLINFO_HEADER_OUT); echo $sent_headers; print_r($headers);// отправленный хидер
		if ($response_code != 200 || (empty($response) && $response_code != 200)) {
			$err = curl_error($service);
			switch($response_code) {
				case 401: $err = "Для запроса $method отказано в авторизации";break;
				case 404: $err = "Не найден ресурс $method";break;
				case 500:
					$tmp = json_decode($response, false, 512, JSON_BIGINT_AS_STRING);
					if (is_object($tmp) && !empty($tmp->Message)) {
						$err = $tmp->Message;
					}
					break;
				case 503: $err = "Сервис временно не доступен. Повторите попытку позже";break;
			}
			if (empty($err)) {
				$err = "Ресурс $method вернул код ошибки: $response_code";
			}
			$result['success'] = false;
			$result['errorMsg'] = 'Ошибка взаимодействия с сервисом: '.$err;
			$result['errorCode'] = $response_code;
		} else {
			if ($type == 'auth') {
				$result = json_decode($response);
			} else {
				$result = simplexml_load_string($response);
			}
		}
		if (is_object($result) && $returnArray) { // вернем в виде массива
			$result = objectToArray($result);
		}
		return $result;
	}
}