<?php
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		Library
 * @access		public
 * @copyright	Copyright (c) 2010 Swan Ltd.
 * @author		Bykov Stas aka Savage (savage@swan.perm.ru)
 * @link		http://swan.perm.ru/PromedWeb
 * @version		22.12.2010
 */

/**
 * Класс для работы с сервисом РПН [Казахстан]
 *
 * @package		Library
 * @author		A. Markoff
 */

class swServiceRPNKZ {
	private $url = null;
	private $authurl = null;
	private $timeout = 30;
	private $user;
	private $password;
	private $authkey;
	private $apikey;
	private $proxy_host;
	private $proxy_port;
	private $proxy_login;
	private $proxy_password;

	/**
	 * Constructor
	 * @param array $config
	 */
	public function __construct($config = array()) {
		if (!$config) {
			$this->CI = get_instance();
			$config = $this->CI->config->item('RPN');
		}
		if (!$config) {
			die('Не заполнены конфигурационные данные в файле config/[регион]/promed.php [RPN].');
		}
		// Переменные класса
		$this->url = $config['apiurl'];
		$this->authurl = $config['authurl'];
		$this->timeout =$config['timeout'];
		$this->user = $config['user'];
		$this->password = $config['password'];
		$this->authkey = $config['authkey'];
		$this->proxy_host = isset($config['proxy_host'])?$config['proxy_host']:null;
		$this->proxy_port = isset($config['proxy_port'])?$config['proxy_port']:null;
		$this->proxy_login = isset($config['proxy_login'])?$config['proxy_login']:null;
		$this->proxy_password = isset($config['proxy_password'])?$config['proxy_password']:null;
		// Авторизуемся сразу
		$this->auth();
	}

	/**
	 * Авторизация в сервисе РПН
	 * @return bool
	 */
	public function auth() {
		// todo: Во избежание постоянной авторизации в сервисе можно ключ доступа к API сохранять в сессии и использовать его
		$headers = array(
			"Authorization: ".$this->authkey, 
			"Content-Type: application/x-www-form-urlencoded", 
			"Accept: application/json;odata=verbose"
		);
		$result = $this->data($this->authurl, 'auth','grant_type=password&username='.$this->user.'&password='.$this->password.'&scope=profile', $headers, false);
		if (is_object($result)) {
			// Ключ доступа к API
			if (isset($result->access_token)) {
				$this->apikey = $result->token_type." ".$result->access_token;
			} else {
				// Не получилось авторизоваться, вернем ошибку
				echo 'Авторизация в сервисе РПН не выполнена! '/*.var_export($result,true)*/;
				return false;
			}
		}
		return true;
	}

	/**
	 * Получение данных из сервиса РПН (использует CURL)
	 * @param $method
	 * @param string $type
	 * @param null $data
	 * @param array $headers
	 * @param bool $returnArray
	 * @return array|mixed
	 */
	public function data($method, $type = 'get', $data = null, $headers=array(), $returnArray = false) {
		$service = curl_init(); 
		$result = array();
		if (count($headers)==0) { // Если передан хидер, то просто подменяем его 
			$headers = array(
				"Content-Type: application/json; charset=utf-8", 
				"Cache-Control: no-cache", 
				"Pragma: no-cache",
				"Accept-Charset: UTF-8",
				"Accept: application/atom+xml"
			); 
			if (isset($this->apikey)) {
				$headers[] = "Authorization: ".$this->apikey;
			}
			if (strlen($data)>0) {
				$headers[] = "Content-length: ".strlen($data);
			}
		}
		
		if ($type == 'auth') { // если авторизация, то адрес другой 
			curl_setopt($service, CURLOPT_URL, $method );   
		} else {
			curl_setopt($service, CURLOPT_URL, $this->url.$method );   
		}
		// остальные параметры 
		curl_setopt($service, CURLOPT_CONNECTTIMEOUT, $this->timeout ); 
		curl_setopt($service, CURLOPT_TIMEOUT,        $this->timeout ); 
		curl_setopt($service, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($service, CURLOPT_SSL_VERIFYPEER, false);  
		curl_setopt($service, CURLOPT_SSL_VERIFYHOST, false); 
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
		// $sent_headers = curl_getinfo($service, CURLINFO_HEADER_OUT); echo $sent_headers; print_r($headers);// отправленный хидер
		
		if (!$response) {
			$err = curl_error($service); 
			$result['success'] = false;
			$result['errorMsg'] = 'Ошибка взаимодействия с сервисом РПН: '.$err;
			/*
			$CI->load->library('textlog', array('file'=>'PersonIdentRequest_'.date('Y-m-d', time()).'.log'));
			$CI->textlog->add('timeout: '.$this->socketTimeout.', serviceURL: '.$this->serviceURI);
			$CI->textlog->add('headers: '.print_r($headers, true));
			$CI->textlog->add('requestText: '.$post_string);
			$CI->textlog->add('responseText: '.$err);
			$result['errorMsg'] = 'Ошибка взаимодействия с сервисом идентификации: '.$err;
			$result['success'] = false;*/
		} else {
			$result = json_decode($response);
		}
		if (is_object($result) && $returnArray) { // вернем в виде массива
			$result = objectToArray($result);
		}
		return $result;
	}
}
