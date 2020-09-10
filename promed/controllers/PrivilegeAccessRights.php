<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PrivilegeAccessRights - контроллер для работы с ограничением прав доступа по льготам
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			22.10.2013
 *
 * @property PrivilegeAccessRights_model dbmodel
 */

class PrivilegeAccessRights extends swController {
	protected  $inputRules = array(
		'savePrivilegeAccessRights' => array(
			array(
				'field' => 'PrivilegeType_id',
				'label' => 'Идентификатор типа льготы',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PrivilegeAccessRightsData',
				'label' => 'Данные ограничений прав доступа по льготе',
				'rules' => 'required|trim',
				'type' => 'string'
			),
			array(
				'field' => 'RecordStatus_isNewRecord',
				'label' => 'Флаг новой записи',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'deletePrivilegeAccessRights' => array(
			array(
				'field' => 'PrivilegeType_id',
				'label' => 'Идентификатор типа льготы',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPrivilegeAccessRightsGrid' => array(
			array(
				'field' => 'PrivilegeType_id',
				'label' => 'Идентификатор типа льготы',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadPrivilegeAccessRightsLpuGrid' => array(
			array(
				'field' => 'PrivilegeType_id',
				'label' => 'Идентификатор типа льготы',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPrivilegeAccessRightsForm' => array(
			array(
				'field' => 'PrivilegeType_id',
				'label' => 'Идентификатор типа льготы',
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
		$this->load->model('PrivilegeAccessRights_model', 'dbmodel');
	}

	/**
	 * Сохранение ограничения прав доступа по льготе
	 * @return bool
	 */
	function savePrivilegeAccessRights()
	{
		$data = $this->ProcessInputData('savePrivilegeAccessRights', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->savePrivilegeAccessRights($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Удалние ограничения прав доступа по льготе
	 * @return bool
	 */
	function deletePrivilegeAccessRights()
	{
		$data = $this->ProcessInputData('deletePrivilegeAccessRights', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deletePrivilegeAccessRights($data);
		$this->ProcessModelSave($response, true, 'При удалении возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Возвращает список ограничений прав доступа по льготе
	 * @return bool
	 */
	function loadPrivilegeAccessRightsGrid()
	{
		$data = $this->ProcessInputData('loadPrivilegeAccessRightsGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPrivilegeAccessRightsGrid($data);
		$this->ProcessModelMultiList($response, true, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Возвращает список МО, которым разрешён доступ к льготам указанного типа
	 * @return bool
	 */
	function loadPrivilegeAccessRightsLpuGrid()
	{
		$data = $this->ProcessInputData('loadPrivilegeAccessRightsLpuGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPrivilegeAccessRightsLpuGrid($data);
		$this->ProcessModelMultiList($response, true, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Возвращает данные для формы редактирования ораничений прав доступа по льготе
	 * @return bool
	 */
	function loadPrivilegeAccessRightsForm()
	{
		$data = $this->ProcessInputData('loadPrivilegeAccessRightsForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPrivilegeAccessRightsForm($data);
		$this->ProcessModelList($response, true, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}
}