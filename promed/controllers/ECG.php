<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ECG - контроллер
 *
 *
 * @package     ECG
 * @access      public
 * @author		ApaevAV
 * @version     06.11.2019
 *
 * @property ECG_model dbmodel
 */

class ECG extends swController {
	protected  $inputRules = array(
		'execCommand' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnUslugaPar_id',
				'label' => 'Идентификатор услуги',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Server_id',
				'rules' => '',
				'type' => 'id',
			)
		),
		'connect' => array(
			array(
				'field' => 'ecg_server',
				'label' => 'Адрес сервиса',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'ecg_port',
				'label' => 'Порт',
				'rules' => 'trim|required',
				'type' => 'string'
			)
		)
		
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();

		set_time_limit(0);

		ini_set("max_execution_time", "0");
		ini_set("default_socket_timeout", "999");

		$this->load->model('ECG_model', 'dbmodel');
	}
	/**
	 * Проверка подключения к сервису AI Server Service
	 */
	function connect() {
		$data = $this->ProcessInputData('connect');
		if ($data === false) { return false; }
		$result = $this->dbmodel->connect($data, true);
		return $this->ReturnData($result);
	}
	/*
	* Формируем xml для службы AI_ServerService
	*/
	function getXMLfoECG()
	{	
		$data = $this->ProcessInputData('execCommand', true);
		if ($data === false) { return false; }

		try {
			$result = $this->dbmodel->getXmlForTransfer($data);
		} catch(Exception $e) {
			$this->ReturnError($e->getMessage(), $e->getCode());
			return false;
		}
		$this->ReturnData(array(
			'success' => true,
			'send' => $result
		));
		
		return true;
	}
}