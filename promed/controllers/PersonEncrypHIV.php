<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PersonEncrypHIV - контроллер для работы с шифрами вич-инфецированных
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			04.06.2015
 *
 * @property PersonEncrypHIV_model dbmodel
 */

class PersonEncrypHIV extends swController {
	protected  $inputRules = array(
		'getPersonEncrypHIVEncryp' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEncrypHIV_setDT',
				'label' => 'Дата создания шифра',
				'rules' => 'required',
				'type' => 'date'
			),
		),
		'checkPersonEncrypHIVExists' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			),
		),
		'deletePersonEncrypHIV' => array(
			array(
				'field' => 'PersonEncrypHIV_id',
				'label' => 'Идентификатор шифра ВИЧ-инфицированного',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'ignoreAnonymWarning',
				'label' => 'Флаг игнорирования предупреждения об удалении шифра анонимного пациента',
				'rules' => '',
				'type' => 'int'
			),
		),
		'savePersonEncrypHIV' => array(
			array(
				'field' => 'PersonEncrypHIV_id',
				'label' => 'Идентификатор шифра ВИЧ-инфицированного',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEncrypHIV_Encryp',
				'label' => 'Шифр ВИЧ-инфицированного',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EncrypHIVTerr_id',
				'label' => 'Территория шифрования',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEncrypHIV_setDT',
				'label' => 'Дата создания шифра',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			),
		),
		'loadPersonEncrypHIVGrid' => array(
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_BirthDay',
				'label' => 'День рождения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EncrypHIVTerr_id',
				'label' => 'Территория шифрования',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonType_id',
				'label' => 'Тип пациентов',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Sex_id',
				'label' => 'Пол',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальная запись',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Лимит записей',
				'rules' => '',
				'type' => 'int'
			),
		),
		'loadPersonEncrypHIVForm' => array(
			array(
				'field' => 'PersonEncrypHIV_id',
				'label' => 'Идентификатор шифрования ВИЧ-инфецированного',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'changePersonInPersonEncrypHIV' => array(
			array(
				'field' => 'PersonEncrypHIV_id',
				'label' => 'Идентификатор шифрования ВИЧ-инфецированного',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_nid',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('PersonEncrypHIV_model', 'dbmodel');
	}

	/**
	 * Получение шифра
	 */
	function getPersonEncrypHIVEncryp() {
		$data = $this->ProcessInputData('getPersonEncrypHIVEncryp',true);
		if (!$data) return false;

		$response = $this->dbmodel->getPersonEncrypHIVEncryp($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Проверка существования шифра у пациента
	 */
	function checkPersonEncrypHIVExists() {
		$data = $this->ProcessInputData('checkPersonEncrypHIVExists',true);
		if (!$data) return false;

		$response = $this->dbmodel->checkPersonEncrypHIVExists($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Сохранение шифра
	 */
	function savePersonEncrypHIV() {
		$data = $this->ProcessInputData('savePersonEncrypHIV',true);
		if (!$data) return false;

		$response = $this->dbmodel->savePersonEncrypHIV($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Удаление шифра
	 */
	function deletePersonEncrypHIV() {
		$data = $this->ProcessInputData('deletePersonEncrypHIV',true);
		if (!$data) return false;

		$response = $this->dbmodel->deletePersonEncrypHIV($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список шифров
	 */
	function loadPersonEncrypHIVGrid() {
		$data = $this->ProcessInputData('loadPersonEncrypHIVGrid',true);
		if (!$data) return false;

		$response = $this->dbmodel->loadPersonEncrypHIVGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получения данных шифрования для редактирования
	 */
	function loadPersonEncrypHIVForm() {
		$data = $this->ProcessInputData('loadPersonEncrypHIVForm',true);
		if (!$data) return false;

		$response = $this->dbmodel->loadPersonEncrypHIVForm($data);

		$this->ProcessModelList($response, true)->ReturnData();
		return true;
	}

	/**
	 * Смена пациента
	 */
	function changePersonInPersonEncrypHIV() {
		$data = $this->ProcessInputData('changePersonInPersonEncrypHIV',true);
		if (!$data) return false;

		$response = $this->dbmodel->changePersonInPersonEncrypHIV($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
}