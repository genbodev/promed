<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * APPBN_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 *
 */
class APPBN_model extends swModel {
	protected $_config = array();
	protected $_soapClients = array();
	protected $_ticket = ""; // токен авторизованного пользователя

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		ini_set("default_socket_timeout", 600);

		$this->load->library('textlog', array('file'=>'APPBN_'.date('Y-m-d').'.log'));

		$this->_config = $this->config->item('APPBN');
	}

	/**
	 * Выполнение запросов к сервису РПН и обработка ошибок, которые возвращает сервис
	 */
	protected function exec($method, $type = 'get', $data = null) {
		$this->load->library('swServiceKZ', $this->config->item('APPBN'), 'swserviceappbn');
		$this->textlog->add("exec method: $method, type: $type, data: ".print_r($data,true));
		$result = $this->swserviceappbn->data($method, $type, $data);
		$this->textlog->add("result: ".print_r($result,true));
		if ( is_object($result) && !empty($result->Message) ) {
			$result = array(
				'success' => false,
				'errorMsg' => 'Ошибка в работе сервиса: '.$result->Message
			);
		}
		if ( is_object($result) && !empty($result->ExceptionMessage) ) {
			$result = array(
				'success' => false,
				'errorMsg' => 'Ошибка в работе сервиса: '.$result->ExceptionMessage
			);
		}
		return $result;
	}

	/**
	 * Получение справочников
	 */
	public function getDictionary($data) {
		$dictList = explode(',', $data['list']);
		$response = array();

		try {
			foreach ( $dictList as $dict ) {
				try {
					$result = $this->exec('/sp/dictionaries/' . $dict, 'get');
					
					if ( !empty($_REQUEST['getDebug']) ) {
						var_dump($result);
					}

					$response[$dict] = $result;
				}
				catch ( Exception $e ) {
					if ( !empty($_REQUEST['getDebug']) ) {
						var_dump($e);
					}
					// падать не будем, просто пишем в лог инфу и идем дальше
					$this->textlog->add("getDictionary error: code: " . $e->getCode() . " message: " . $e->getMessage());

					$response[$dict] = $e->getMessage();
				}
			}
		}
		catch ( Exception $e ) {
			if ( !empty($_REQUEST['getDebug']) ) {
				var_dump($e);
			}
		}

		return $response;
	}
}