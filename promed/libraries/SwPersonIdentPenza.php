<?php

/*require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;*/

class SwPersonIdentPenza {
	protected $url;
	protected $timeout;
	protected $login;
	protected $password;
	protected $client;
	protected $cookieJar;

	/**
	 * SwPersonIdentPenza constructor.
	 * @param array $config
	 */
	public function __construct($config) {
		$this->url = $config['url'];
		$this->timeout = $config['timeout'];
		$this->login = $config['login'];
		$this->password = $config['password'];

		get_instance()->load->helper('CURL');
	}

	protected function getBody($data, $info, $force_json = false){
		$body = $info["size_download"] ? substr($data, $info["header_size"], $info["size_download"]) : "";
		if ($force_json) {
			$body = json_decode($body, true);
		}
		return $body;
	}

	/**
	 * @return string
	 */
	protected function getCookieJar(){
		if ($this->cookieJar === null) {
			$this->cookieJar = strval(tempnam('/tmp', 'cookie'));
		}
		return $this->cookieJar;
	}

	/**
	 * @param $url
	 * @param $params
	 * @return mixed|string
	 * @throws Exception
	 */
	public function request($url, $params) {
		$resp = CURL($this->url.$url, json_encode($params), 'POST', $this->getCookieJar(), array(
			CURLOPT_HTTPHEADER => array(
				"Content-Type: application/json; charset=UTF-8"
			)
		));
		if (!$resp || !isset($resp['info'])) {
			throw new Exception('Не удалось соединиться с сервисом', 400);
		}
		if ($resp['info']['http_code'] != 200) {
			$body = $this->getBody($resp['data'], $resp['info']);
			$parsed = is_string($body)?json_decode($body, true):null;
			$code = !empty($resp['info']['http_code'])?$resp['info']['http_code']:400;
			$msg = (is_array($parsed) && isset($parsed['Message']))?$parsed['Message']:(
				!empty($body)?$body:'Ошибка соединения с сервисом');
			throw new Exception($msg, $code);
		}
		return $this->getBody($resp['data'], $resp['info']);
	}

	/**
	 * @return array
	 */
	public function login() {
		try {
			$resp = $this->request('/login', array(
				'login' => $this->login,
				'password' => $this->password,
			));
		} catch (Exception $e) {
			return array(array(
				'Error_Code' => $e->getCode(),
				'Error_Msg' => 'Ошибка авторизации'
			));
		}
		if ($resp != '"logged in"') {
			return array(array(
				'Error_Code' => null,
				'Error_Msg' => 'Ошибка авторизации'
			));
		}
		return array(array('success' => true));
	}

	/**
	 * @param array $person
	 * @return array
	 */
	public function search($person) {
		try {
			$resp = $this->request('/search', array(
				'birthdate' => $person['birthdate'],
				'sex' => $person['sex'],
				'doc_code' => $person['doc_code'],
				'doc_series' => $person['doc_series'],
				'doc_number' => $person['doc_number'],
			));
		} catch (Exception $e) {
			return array(array(
				'Error_Code' => $e->getCode(),
				'Error_Msg' => $e->getMessage(),
			));
		}
		return array(array(
			'success' => true,
			'list' => json_decode($resp, true),
		));
	}
}