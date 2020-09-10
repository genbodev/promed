<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * AisPolka - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 *
 * @property AisPolka_model dbmodel
 */

class AisPolka extends swController {
	protected  $inputRules = array(
		'syncAll' => array(
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор ТАП',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnUslugaPar_id',
				'label' => 'Идентификатор услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLStom_id',
				'label' => 'Идентификатор ТАП стоматологии',
				'rules' => '',
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

		$this->load->model('AisPolka_model', 'dbmodel');
	}

	/**
	 * Отправка всех закрытых ТАП
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
