<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Erdb - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 *
 * @property Erdb_model dbmodel
 */

class Erdb extends swController {
	protected  $inputRules = array(
		'syncAll' => array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор карты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'start',
				'label' => '',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'end',
				'label' => '',
				'rules' => '',
				'type' => 'date'
			)
		),
		'getHuman' => array(
			array(
				'field' => 'Person_Inn',
				'label' => 'ИИН',
				'rules' => 'required',
				'type' => 'id'
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

		$this->load->model('Erdb_model', 'dbmodel');
	}

	/**
	 * Отправка всех карт за прошедшие сутки
	 */
	function syncAll() {
		$data = $this->ProcessInputData('syncAll', true);
		if ($data === false) { return false; }

		if (!isSuperadmin()) {
			$this->ReturnError('Функционал доступен только для суперадмина');
			return false;
		}

		$this->dbmodel->syncAll($data);

		$this->ReturnData(array('success' => true));
		return true;
	}

	/**
	 * Получение карты пациента от сервиса
	 */
	function getHuman() {
		$data = $this->ProcessInputData('getHuman', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getHuman($data);

		$this->ReturnData($response);
		return true;
	}
}
