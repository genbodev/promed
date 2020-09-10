<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MedServiceElectronicQueue - связь службы и очереди
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 *
 * @property MedServiceElectronicQueue_model dbmodel
 */

class MedServiceElectronicQueue extends swController {
	protected  $inputRules = array(
		'delete' => array(
			array(
				'field' => 'MedServiceElectronicQueue_id',
				'label' => 'Идентификатор связи',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadList' => array(
			array(
				'field' => 'MedService_id',
				'label' => 'Служба',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Подразделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			),
		),
		'load' => array(
			array(
				'field' => 'MedServiceElectronicQueue_id',
				'label' => 'Идентификатор связи',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'save' => array(
			array(
				'field' => 'MedServiceElectronicQueue_id',
				'label' => 'Идентификатор связи',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedServiceMedPersonal_id',
				'label' => 'Сотрудник на службе',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplexMedService_id',
				'label' => 'Услуга',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ElectronicService_id',
				'label' => 'Пункт обслуживания',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'remoteMedService_id',
				'label' => 'Идентификатор сторонней службы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedServiceType_SysNick',
				'label' => 'Наименование службы',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'ignoreDoublesByMedPersonal',
				'label' => 'Признак игнорирования проверки о нескольких сотрудниках на одном пункте обслуживания',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Resource_id',
				'label' => 'Ресурс на службе',
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
		$this->load->model('MedServiceElectronicQueue_model', 'dbmodel');
	}

	/**
	 * Удаление связи
	 */
	function delete()
	{
		$data = $this->ProcessInputData('delete');
		if ($data === false) { return false; }

		$response = $this->dbmodel->delete($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении очереди')->ReturnData();
		return true;
	}

	/**
	 * Возвращает список очередей
	 */
	function loadList()
	{
		$data = $this->ProcessInputData('loadList');
		if ($data === false) { return false; }

		if ( empty($data['MedService_id']) && empty($data['LpuBuilding_id']) && empty($data['LpuSection_id'])) {
			$this->ReturnData(array(
				'Error_Msg' => 'Не указана служба, подразделение или отделение',
				'Error_Code' => 149,
				'success' => false
			));
			return false;
		}

		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает очередь
	 */
	function load()
	{
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение очереди
	 */
	function save()
	{
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }

		if ( empty($data['MedServiceMedPersonal_id']) && empty($data['MedStaffFact_id'])) {
			$this->ReturnData(array(
				'Error_Msg' => 'Не указан сотрудник на службе или врач',
				'Error_Code' => 149,
				'success' => false
			));
			return false;
		}

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
}