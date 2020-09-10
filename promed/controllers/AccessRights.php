<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * AccessRights - контроллер для работы c правами доступа
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			28.08.2014
 *
 * @property AccessRights_model dbmodel
 */

class AccessRights extends swController {
	protected $model_name = 'AccessRights_model';

	protected  $inputRules = array(
		'loadAccessRightsGrid' => array(
			array(
				'field' => 'AccessRightsName_id',
				'label' => 'Идентификатор',
				'rules' => '',
				'type' => 'id'
			),
		),
		'saveAccessRights' => array(
			array(
				'field' => 'AccessRightsName_id',
				'label' => 'Идентификатор наименования группы доступа',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AccessRightsName_Name',
				'label' => 'Наименования группы доступа',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'AccessRightsName_Code',
				'label' => 'Код группы доступа',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'AccessRightsData',
				'label' => 'Данные для ограничения доступа',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'AccessRightsType_id',
				'label' => 'Идентификатор типа группы доступа',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'allowIntersection',
				'label' => 'Допустить пересечение групп',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'AccessRightsLpuBuildingData',
				'label' => 'Данные LpuBuilding для ограничения доступа',
				'rules' => '',
				'type' => 'string'
			)
		),
		'deleteAccessRights' => array(
			array(
				'field' => 'AccessRightsName_id',
				'label' => 'Идентификатор наименования группы доступа',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadAccessRightsForm' => array(
			array(
				'field' => 'AccessRightsName_id',
				'label' => 'Идентификатор наименования группы доступа',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadAccessRightsLimitGrid' => array(
			array(
				'field' => 'AccessRightsName_id',
				'label' => 'Идентификатор наименования группы доступа',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AccessRightsLimitType_SysNick',
				'label' => 'Тип предоставления доступа',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadAccessRightsLimitUsersGrid' => array(
			array(
				'field' => 'AccessRightsName_id',
				'label' => 'Идентификатор наименования группы доступа',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveAccessRightsArmSmo' => array(
			array(
				'field' => 'AccessRightsName_Code',
				'label' => 'Код наименования группы доступа',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'AccessRightsOrg_id',
				'label' => 'Идентификатор записи доступа организации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Идентификатор CМО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadAccessRightsArmSmoGrid' => array(
			array(
				'field' => 'AccessRightsName_Code',
				'label' => 'Код наименования группы доступа',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'AccessRightsOrg_id',
				'label' => 'Идентификатор записи доступа организации',
				'rules' => '',
				'type' => 'id'
			)
		),
		'checkArmSmoAccess' => array(
			array(
				'field' => 'Org_id',
				'label' => 'Идентификатор организации',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadSmoActualList' => array(
		),
		'saveAccessRightsLimitUsers' => array(
			array(
				'field' => 'AccessRightsName_id',
				'label' => 'Идентификатор наименования группы доступа',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'AccessRightsLimitUsersData',
				'label' => 'Данные ползователей для предоставления доступа',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'saveAccessRightsLimit' => array(
			array(
				'field' => 'AccessRightsName_id',
				'label' => 'Идентификатор наименования группы доступа',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LimitLpu_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LimitPost_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AccessRightsType_UserGroups',
				'label' => 'Группа пользователей',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LpuBuildings',
				'label' => 'Подразделения',
				'rules' => '',
				'type' => 'string'
			),
		),
		'deleteAccessRightsLimit' => array(
			array(
				'field' => 'AccessRightsLimit_id',
				'label' => 'Идентификатор доступа',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuildingAccessRightsLink_id',
				'label' => 'Идентификатор LpuBuildingAccessRightsLink',
				'rules' => '',
				'type' => 'id'
			),
		),
		'deleteAccessRightsArmSmo' => array(
			array(
				'field' => 'AccessRightsOrg_id',
				'label' => 'Идентификатор записи организации',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadAccessT9Grid' => array(
			array(
				'field' => 'Org_id',
				'label' => 'Идентификатор организации',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveAccessT9Grid' => array(
			array(
				'field' => 'Org_id',
				'label' => 'Идентификатор организации',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'groups',
				'label' => 'Список групп пользователей',
				'rules' => 'required',
				'type' => 'json_array'
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
		$this->load->model($this->model_name, 'dbmodel');
	}

	/**
	 * Получение списка групп с ограничением доступа
	 */
	function loadAccessRightsGrid() {
		$data = $this->ProcessInputData('loadAccessRightsGrid', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadAccessRightsGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение группы для ограничения доступа
	 */
	function saveAccessRights() {
		$data = $this->ProcessInputData('saveAccessRights');
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveAccessRights($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Удаление группы для ограничения доступа
	 */
	function deleteAccessRights() {
		$data = $this->ProcessInputData('deleteAccessRights');
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteAccessRights($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение данных для редактирования группы ограничения доступа
	 */
	function loadAccessRightsForm() {
		$data = $this->ProcessInputData('loadAccessRightsForm');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadAccessRightsForm($data);

		$this->ProcessModelList($response)->ReturnData();
		return true;
	}

	/**
	 * Полученение списка объектов с разрешенным доступом к группе
	 */
	function loadAccessRightsLimitGrid() {
		$data = $this->ProcessInputData('loadAccessRightsLimitGrid', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadAccessRightsLimitGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Полученение списка СМО с разрешенным доступом к справочнику МЭСов
	 */
	function loadAccessRightsArmSmoGrid() {
		$data = $this->ProcessInputData('loadAccessRightsArmSmoGrid', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadAccessRightsArmSmoGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение СМО с разрешенным доступом к справочнику МЭСов
	 */
	function saveAccessRightsArmSmo() {
		$data = $this->ProcessInputData('saveAccessRightsArmSmo');
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveAccessRightsArmSmo($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение списка пользователей для проставления доступа к группе
	 */
	function loadAccessRightsLimitUsersGrid() {
		$data = $this->ProcessInputData('loadAccessRightsLimitUsersGrid', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadAccessRightsLimitUsersGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение изменений доступа пользователей к группе
	 */
	function saveAccessRightsLimitUsers() {
		$data = $this->ProcessInputData('saveAccessRightsLimitUsers');
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveAccessRightsLimitUsers($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Сохранение доступа объекта к группе
	 */
	function saveAccessRightsLimit() {
		$data = $this->ProcessInputData('saveAccessRightsLimit');
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveAccessRightsLimit($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Удаление доступа объекта к группе
	 */
	function deleteAccessRightsLimit() {
		$data = $this->ProcessInputData('deleteAccessRightsLimit');
		if ($data === false) { return false; }

		//$response = $this->dbmodel->deleteAccessRightsLimit($data);
		$response = $this->dbmodel->deleteLpuBuildingOrObjectAccessRightsLimit($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Удаление доступа СМО к справочнику МЭСов
	 */
	function deleteAccessRightsArmSmo() {
		$data = $this->ProcessInputData('deleteAccessRightsArmSmo');
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteAccessRightsArmSmo($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Проверка наличия доступа к справочнику МЭСов
	 */
	function checkArmSmoAccess() {
		$data = $this->ProcessInputData('checkArmSmoAccess');
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkArmSmoAccess($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}


	/**
	 * Возвращает список действующих СМО
	 * @return bool
	 */
	function loadSmoActualList()
	{
		$data = $this->ProcessInputData('loadSmoActualList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadSmoActualList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Проверка наличия доступа организации OrgSMO с признаком ТФОМС  к справочнику МЭСов
	 */
	function checkAccessTfomsToFunctionalEMK() {
		$data = $this->ProcessInputData('checkArmSmoAccess');
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkAccessTfomsToFunctionalEMK($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Доступ групп пользователей МО к Т9 
	 */
	function loadAccessT9Grid() {
		$data = $this->ProcessInputData('loadAccessT9Grid');
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadAccessT9Grid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранить настройки доступа к Т9
	 */
	function saveAccessT9Grid() {
		$data = $this->ProcessInputData('saveAccessT9Grid');
		if ($data === false) { return false; }
		$response = $this->dbmodel->saveAccessT9Grid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}