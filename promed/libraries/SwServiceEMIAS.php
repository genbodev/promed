<?php

class SwServiceEMIAS {
	private $url = null;
	private $timeout = 600;
	private $userkey;
	private $secretkey;
	private $authurl;
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
		$this->secretkey = $config['secretkey'];
		$this->authurl = $config['authurl'];
		
		$this->proxy_host = isset($config['proxy_host'])?$config['proxy_host']:null;
		$this->proxy_port = isset($config['proxy_port'])?$config['proxy_port']:null;
		$this->proxy_login = isset($config['proxy_login'])?$config['proxy_login']:null;
		$this->proxy_password = isset($config['proxy_password'])?$config['proxy_password']:null;
	}
	
	private function s4() {
		return bin2hex(openssl_random_pseudo_bytes(2));
	}
	private function requestId() {
		return $this->s4() . $this->s4() . '-' . $this->s4() . '-' . $this->s4() . '-' . $this->s4() . '-' . $this->s4() . $this->s4() . $this->s4();
	}
	private function prepareUrl($url) {
		$url = mb_strtolower(
			preg_replace('/^https?:\/\/[^\/]+\//', '/', urldecode($url))
		);
		$result = base64_encode($url);
		return $result;
	}

	public function authData($ticket ,$operation = 'login') {
		$service = curl_init();
		$ticketEncoded = urlencode($ticket);

		curl_setopt($service, CURLOPT_URL, $this->authurl."?o=$operation");
		curl_setopt($service, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		curl_setopt($service, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($service, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($service, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($service, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($service, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($service, CURLOPT_POSTFIELDS, "ticket=$ticketEncoded");
		curl_setopt($service, CURLINFO_HEADER_OUT, true);
		$headers = array(
			"Content-Type:application/x-www-form-urlencoded"
		);
		curl_setopt($service, CURLOPT_HTTPHEADER, $headers);

		// прокси
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
			echo "request url: ".$this->authurl."<br>";
			echo "operation: ".$operation."<br>";
			echo "ticket: ".$ticket."<br>";
			echo "ticketEncoded: ".$ticketEncoded."<br>";
			echo "response_code: " . $response_code."<br>";
			echo "<textarea cols=150 rows=20>" . $response . "</textarea><br><br>";
		}

		if (!empty($errNo)) {
			$response = strip_tags($response);
			$err = "Проверка наличия токена провалилась с ошибкой: {$errNo}, ответ: {$response}";
			$result['success'] = false;
			$result['errorMsg'] = 'Ошибка взаимодействия с сервисом: ' . $err;
			$result['errorCode'] = $errNo;
		}
		elseif (!in_array($response_code, array(200,201))) {
			$response = strip_tags($response);
			$err = "Проверка наличия токена провалилась с ошибкой: {$response_code}, ответ: {$response}";
			$result['success'] = false;
			$result['errorMsg'] = 'Ошибка взаимодействия с сервисом: ' . $err;
			$result['errorCode'] = $response_code;
		}
		else {
			$result = json_decode($response, true);
		}

		return $result;
	}

	public function data($methodType, $method, $data = null, $log, $objectId = null, $isRetry = false) {
		$service = curl_init();
		$result = array();
		
		$url = $this->url . $method;
		if ($methodType == 'GET' && !empty($data)) {
			$url .= '?' . http_build_query($data);
		}
		
		curl_setopt($service, CURLOPT_URL, $url);
		curl_setopt($service, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		curl_setopt($service, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($service, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($service, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($service, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($service, CURLOPT_POST, false);
		curl_setopt($service, CURLINFO_HEADER_OUT, true);

		$reqBodyMD5 = '';

		if ($methodType == 'POST') {
			$json = json_encode($data, JSON_FORCE_OBJECT);
			$reqBodyMD5 = base64_encode(md5($json, true));
			curl_setopt($service, CURLOPT_POST, true);
			curl_setopt($service, CURLOPT_POSTFIELDS, $json);
		}

		if ($methodType == 'PUT') {
			$json = json_encode($data, JSON_FORCE_OBJECT);
			$reqBodyMD5 = base64_encode(md5($json, true));
			curl_setopt($service, CURLOPT_CUSTOMREQUEST, 'PUT');
			curl_setopt($service, CURLOPT_POSTFIELDS, $json);
		}

		//нужно составить строку авторизации через HMAC
		$userKey = $this->userkey;
		$secretKey = base64_decode($this->secretkey);
		$requestId = $this->requestId();
		$timestamp = time();
		$signatureRawData = $userKey . $methodType . $this->prepareUrl($url) . $timestamp . $requestId . $reqBodyMD5;
		$signature = hash_hmac('SHA256', $signatureRawData, $secretKey, true);
		$signatureEncoded = base64_encode($signature);
		$headers = array(
			"Authorization: HMAC {$userKey}:{$signatureEncoded}:{$requestId}:{$timestamp}",
			"Accept: application/json",
			"Content-Type: application/json",
			"User-Agent: PostmanRuntime/7.17.1", //без этой шляпы кривое апи выдает "не найден указанный тэг"
			"Accept: */*",
			"Cache-Control: no-cache",
			"Accept-Encoding: gzip, deflate",
			"Connection: keep-alive"
		);

		curl_setopt($service, CURLOPT_HTTPHEADER, $headers);
		
		// прокси
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
			echo "<textarea cols=150 rows=20>" . var_export($data, true) . "</textarea><br><br>";
			echo "response_code: " . $response_code."<br>";
			echo "<textarea cols=150 rows=20>" . $response . "</textarea><br><br>";
		}

		$resp = null;
		switch ($method) {
			case 'Recipe/GetNewRecipeTemplate':
				$resp = $log->addPackage(null, $objectId, $requestId, null, 'RECEPT_NUM');
				break;
			case 'Recipe/SaveRecipe':
				$resp = $log->addPackage('EvnRecept', $objectId, $requestId, null, 'RECEPT_UPD', 'Insert');
				break;
			case 'Recipe/CancelRecipe':
				$resp = $log->addPackage('EvnRecept', $objectId, $requestId, null, 'RECEPT_UPD', 'Delete');
				break;
			case 'Patient/AddPatient':
				$resp = $log->addPackage('PersonState', $objectId, $requestId, null, 'PATIENT', 'Insert');
				break;
			case 'Patient/UpdatePatient':
				$resp = $log->addPackage('PersonState', $objectId, $requestId, null, 'PATIENT', 'Update');
				break;
			case 'Patient/CancelPatient':
				$resp = $log->addPackage('PersonState', $objectId, $requestId, null, 'PATIENT', 'Delete');
				break;
			case 'Privilege/AddPatientPrivilege':
				$resp = $log->addPackage('PersonPrivilege', $objectId, $requestId, null, 'LGOT', 'Insert');
				break;
			case 'Privilege/UpdatePatientPrivilege':
				$resp = $log->addPackage('PersonPrivilege', $objectId, $requestId, null, 'LGOT', 'Update');
				break;
			case 'Privilege/RemovePatientPrivilege':
				$resp = $log->addPackage('PersonPrivilege', $objectId, $requestId, null, 'LGOT', 'Delete');
				break;
		}
		
		if ((!empty($errNo) || !in_array($response_code, array(200, 201))) && !$isRetry) {
			usleep(200);
			return $this->data($methodType, $method, $data, $log, $objectId, true);
		}
		elseif (!empty($errNo)) {
			$response = strip_tags($response);
			$err = "Ресурс $method вернул номер ошибки: {$errNo}, ответ: {$response}";
			$result['success'] = false;
			$result['errorMsg'] = 'Ошибка взаимодействия с сервисом: ' . $err;
			$result['errorCode'] = $errNo;
		}
		elseif (!in_array($response_code, array(200,201))) {
			$response = strip_tags($response);
			$err = "Ресурс $method вернул код ошибки: {$response_code}, ответ: {$response}";
			$result['success'] = false;
			$result['errorMsg'] = 'Ошибка взаимодействия с сервисом: ' . $err;
			$result['errorCode'] = $response_code;
		}
		else {
			if (!empty($resp[0]['ServiceListPackage_id'])) {
				$log->add(true, empty($response) ? "Запрос $method успешно выполнен" : $response, $resp[0]['ServiceListPackage_id']);
			}
			$result = json_decode($response, true);
		}
		
		return $result;
	}
}
