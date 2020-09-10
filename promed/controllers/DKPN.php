<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * DKPN - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 *
 * @property DKPN_model dbmodel
 */

class DKPN extends swController {
	protected  $inputRules = array(
		'syncAll' => array(
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор события',
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

		$this->load->model('DKPN_model', 'dbmodel');
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