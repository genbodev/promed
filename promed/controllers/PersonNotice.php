<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PersonNotice - контроллер для работы с уведомлениями по пациенту
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			04.04.2014
 *
 * @property PersonNotice_model dbmodel
 * @property Evn_model evnmodel
 */

class PersonNotice extends swController {
	protected  $inputRules = array(
		'savePersonNotice' => array(
			array(
				'field' => 'PersonNotice_id',
				'label' => 'Идентификатор настройки уведомлений',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNotice_IsSend',
				'label' => 'Флаг отправки сообщений о пациенте',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getPersonNotice' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
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
		$this->load->model('PersonNotice_model', 'dbmodel');
	}

	/**
	 * Вкл/выкл уведомлений по пациенту для врача
	 */
	function savePersonNotice() {
		$data = $this->ProcessInputData('savePersonNotice',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->savePersonNotice($data);
		$this->ProcessModelSave($response,true)->ReturnData();
	}

	/**
	 * Возвращает найстройки уведомлений по пациенту
	 */
	function getPersonNotice() {
		$data = $this->ProcessInputData('getPersonNotice',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->getPersonNotice($data);
		$this->ProcessModelList($response,true)->ReturnData();
	}
}