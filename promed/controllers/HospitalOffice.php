<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * HospitalOffice - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 *
 * @property HospitalOffice_model dbmodel
 */

class HospitalOffice extends swController {
	
	var $NeedCheckLogin = false;
	var $inputRules = array(
		'syncAll' => array(
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'mt',
				'label' => 'Метод',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'user',
				'label' => 'Пользователь',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'pass',
				'label' => 'Пароль',
				'rules' => 'trim',
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

		$this->load->model('HospitalOffice_model', 'dbmodel');
	}

	/**
	 * Самый главный метод
	 */
	function syncAll() {
		$data = $this->ProcessInputData('syncAll', false);
		if ($data === false) { return false; }

		if (!defined('CRON') && !isSuperadmin()) {
			$this->ReturnError('Функционал доступен только для суперадмина');
			return false;
		}

		$this->dbmodel->syncAll($data);

		$this->ReturnData(array('success' => true));
		return true;
	}
}
