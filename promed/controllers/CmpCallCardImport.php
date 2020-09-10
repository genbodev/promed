<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * CmpCallCardImport - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 *
 * @property CmpCallCardImport_model dbmodel
 */

class CmpCallCardImport extends swController {
	protected  $inputRules = array(
		'syncAll' => array(
			array(
				'field' => 'limit',
				'label' => 'Ограничение количества',
				'rules' => '',
				'type' => 'int'
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

		$this->load->model('CmpCallCardImport_model', 'dbmodel');
	}

	/**
	 * Загрузка карт
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
