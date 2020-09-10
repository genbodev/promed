<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ObjectSynchronLog - контролер для работы с журналом синхронизации данных со сторонними сервисами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			15.04.2015
 */

class ObjectSynchronLog extends swController {
	protected  $inputRules = array(
		'deleteObjectSynchronLog' => array(
			array(
				'field' => 'ObjectSynchronLog_id',
				'label' => 'Идентификатор строки',
				'rules' => 'required',
				'type' => 'id'
			),
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('ObjectSynchronLog_model', 'dbmodel');
	}

	/**
	 * Удаление строки из журнала синхронизации объекта
	 */
	function deleteObjectSynchronLog() {
		$data = $this->ProcessInputData('deleteObjectSynchronLog', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteObjectSynchronLog($data['ObjectSynchronLog_id']);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
}
