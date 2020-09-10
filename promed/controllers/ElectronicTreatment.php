<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ElectronicTreatment - контроллер для работы со справочником поводов обращений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 *
 * @property ElectronicTreatment_model dbmodel
 */

class ElectronicTreatment extends swController {
	protected  $inputRules = array(
		'delete' => array(
			array('field' => 'ElectronicTreatment_id', 'label' => 'Идентификатор повода', 'rules' => 'required', 'type' => 'id'),
		),
		'getCountTreatmentGroup' => array(
			array('field' => 'ElectronicQueueInfoIds', 'label' => 'Идентификаторы электронных очередей', 'rules' => 'required', 'type' => 'string')
		),
		'loadElectronicTreatmentGroupCombo' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
		),
		'loadList' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'ElectronicTreatment_pid', 'label' => 'Идентификатор группы поводов', 'rules' => '', 'type' => 'id'),
			array('default' => 0, 'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
			array('default' => 100, 'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
		),
		'loadElectronicInfomatTreatmentLink' => array(
			array('field' => 'ElectronicTreatment_id', 'label' => 'Идентификатор повода', 'rules' => 'required', 'type' => 'id'),
			array('default' => 0, 'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
			array('default' => 100, 'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
		),
		'load' => array(
			array('field' => 'ElectronicTreatment_id', 'label' => 'Идентификатор повода', 'rules' => 'required', 'type' => 'id'),
		),
		'loadElectronicQueueInfoCombo' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'rules' => '', 'type' => 'id'),
		),
		'loadElectronicTreatmentQueues' => array(
			array('field' => 'ElectronicTreatment_id', 'label' => 'Идентификатор повода', 'rules' => 'required', 'type' => 'id'),
		),
		'save' => array(
			array('field' => 'ElectronicTreatment_id', 'label' => 'Идентификатор повода', 'rules' => '', 'type' => 'id'),
			array('field' => 'ElectronicTreatment_pid', 'label' => 'Идентификатор группы поводов', 'rules' => '', 'type' => 'id'),
			array('field' => 'ElectronicTreatmentLevel_id', 'label' => 'Идентификатор уровня', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuilding_id', 'label' => 'Подразделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'ElectronicTreatment_Code', 'label' => 'Код', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'ElectronicTreatment_Name', 'label' => 'Наименование', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'ElectronicTreatment_Descr', 'label' => 'Примечание', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'ElectronicTreatment_begDate', 'label' => 'Дата начала', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'ElectronicTreatment_endDate', 'label' => 'Дата окончания', 'rules' => '', 'type' => 'date'),
			array('field' => 'ElectronicTreatment_isConfirmPage', 'label' => 'Пропускать страницу подтверждения', 'rules' => '', 'type' => 'id'),
			array('field' => 'ElectronicTreatment_isFIOShown', 'label' => 'Отображать ФИО врача в талоне', 'rules' => '', 'type' => 'id'),
			array('field' => 'queueData', 'label' => 'Набор данных табло-очереди', 'rules' => '', 'type' => 'string'),
		),
		'addElectronicInfomatTreatmentLink' => array(
			array('field' => 'ElectronicInfomatTreatmentLink_id', 'label' => 'Идентификатор связи инфомат-повод', 'rules' => '', 'type' => 'id'),
			array('field' => 'ElectronicInfomat_id', 'label' => 'Идентификатор инфомата', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'ElectronicTreatment_id', 'label' => 'Идентификатор повода', 'rules' => 'required', 'type' => 'id'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('ElectronicTreatment_model', 'dbmodel');
	}

	/**
	 * Удаление повода
	 */
	public function delete() {
		$data = $this->ProcessInputData('delete');
		if ($data === false) { return false; }

		$response = $this->dbmodel->delete($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении повода')->ReturnData();

		return true;
	}

	/**
	 * Возвращает количество групп поводов обращения связанных с очередью
	 */
	public function getCountTreatmentGroup() {
		$data = $this->ProcessInputData('getCountTreatmentGroup');
		if ($data === false) { return false; }

		$ElectronicQueueInfoIds = json_decode($data['ElectronicQueueInfoIds']);

		$eqError = false;
		if(!is_array($ElectronicQueueInfoIds)) {
			$eqError = true;
		} else {
			foreach ($ElectronicQueueInfoIds as $ElectronicQueueInfo_id) {
				if(!is_int($ElectronicQueueInfo_id) ) {
					$eqError = true;
				}
			}
		}

		if ($eqError) {
			throw new Exception("В ElectronicQueueInfoIds должен быть передан массив целых чисел");
		}

		$data['ElectronicQueueInfoIds'] = $ElectronicQueueInfoIds;

		$response = $this->dbmodel->getCountTreatmentGroup($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Возвращает список поводов
	 */
	public function loadList() {
		$data = $this->ProcessInputData('loadList', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Возвращает список связанных с поводом инфоматов
	 */
	public function loadElectronicInfomatTreatmentLink() {

		$data = $this->ProcessInputData('loadElectronicInfomatTreatmentLink', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadElectronicInfomatTreatmentLink($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Возвращает список групп поводов для комбо
	 */
	public function loadElectronicTreatmentGroupCombo() {
		$data = $this->ProcessInputData('loadElectronicTreatmentGroupCombo', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadElectronicTreatmentGroupCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Возвращает список очередей для комбо
	 */
	public function loadElectronicQueueInfoCombo() {
		$data = $this->ProcessInputData('loadElectronicQueueInfoCombo', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadElectronicQueueInfoCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Возвращает список очередей для табло
	 */
	public function loadElectronicTreatmentQueues() {
		$data = $this->ProcessInputData('loadElectronicTreatmentQueues');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadElectronicTreatmentQueues($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Возвращает данные повода
	 */
	public function load() {
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение повода
	 */
	public function save() {
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * Сохранение инфомата для повода
	 */
	public function addElectronicInfomatTreatmentLink() {
		$data = $this->ProcessInputData('addElectronicInfomatTreatmentLink');
		if ($data === false) { return false; }

		$response = $this->dbmodel->addElectronicInfomatTreatmentLink($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}
}