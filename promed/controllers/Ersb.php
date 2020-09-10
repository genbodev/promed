<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Ersb - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 *
 * @property Ersb_model dbmodel
 */

class Ersb extends swController {
	protected  $inputRules = array(
		'syncAll' => array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => '',
				'type' => 'id'
			)
		),
		'savePatientAdmissionMoveData' => array()
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

		$this->load->model('Ersb_model', 'dbmodel');
	}

	/**
	 * Отправка всех закрытых КВС за прошедшие сутки
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
}
