<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Тарифы и объёмы
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2015 Swan Ltd.
 * @author       DimICE
 * @version
 * @property TariffVolumes_model TariffVolumes_model
 */

class TariffVolumes extends swController
{
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();

		$this->inputRules = array(
			'importKSGVolumes' => array(
				array(
					'field' => 'StartDate',
					'label' => 'Дата начала',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'MesType_id',
					'label' => 'Тип КСГ',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Descr',
					'label' => 'Примечание',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ImportFile',
					'label' => 'Файл',
					'rules' => '',
					'type' => 'string'
				)
			),
			'getValuesFields' => array(
				array(
					'field' => 'AttributeVision_TableName',
					'label' => 'Объект БД',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'AttributeVision_TablePKey',
					'label' => 'Ключ',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'AttributeValue_id',
					'label' => 'ID записи',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'getFilters',
					'label' => 'Получить фильтры',
					'rules' => '',
					'type' => 'int'
				)
			),
			'saveAttributeValue' => array(
				array(
					'field' => 'AttributeVision_TableName',
					'label' => 'Объект БД',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'AttributeVision_TablePKey',
					'label' => 'Ключ',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'AttributeValue_id',
					'label' => 'ID записи',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AttributeValue_begDate',
					'label' => 'Дата начала',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'AttributeValue_endDate',
					'label' => 'Дата окончания',
					'rules' => '',
					'type' => 'date'
				)
			),
			'loadTariffClassGrid' => array(
				array(
					'field' => 'isClose',
					'label' => 'Закрытые',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TariffClass_begDate_From',
					'label' => 'Дата начала от',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'TariffClass_begDate_To',
					'label' => 'Дата начала до',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'TariffClass_endDate_From',
					'label' => 'Дата окончания от',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'TariffClass_endDate_To',
					'label' => 'Дата окончания до',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'TariffClass_Code',
					'label' => 'Код тарифа',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'TariffClass_noKeyValue',
					'label' => 'Без значения',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'start',
					'label' => '',
					'rules' => '',
					'type' => 'int',
					'default' => 0
				),
				array(
					'field' => 'limit',
					'label' => '',
					'rules' => '',
					'type' => 'int',
					'default' => 100
				)
			),
			'loadVolumeTypeGrid' => array(
				array(
					'field' => 'isClose',
					'label' => 'Закрытые',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'VolumeType_begDate_From',
					'label' => 'Дата начала от',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'VolumeType_begDate_To',
					'label' => 'Дата начала до',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'VolumeType_endDate_From',
					'label' => 'Дата окончания от',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'VolumeType_endDate_To',
					'label' => 'Дата окончания до',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'VolumeType_Code',
					'label' => 'Код объема',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'VolumeType_noKeyValue',
					'label' => 'Без значения',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'start',
					'label' => '',
					'rules' => '',
					'type' => 'int',
					'default' => 0
				),
				array(
					'field' => 'limit',
					'label' => '',
					'rules' => '',
					'type' => 'int',
					'default' => 100
				)
			),
			'deleteValue' => array(
				array(
					'field' => 'AttributeValue_id',
					'label' => 'Значение',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadValuesGrid' => array(
				array(
					'field' => 'filters',
					'label' => 'Фильтры',
					'rules' => '',
					'type' => 'json_array',
					'assoc' => true
				),
				array(
					'field' => 'isClose',
					'label' => 'Закрытые',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AttributeVision_TableName',
					'label' => 'Объект БД',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'AttributeVision_TablePKey',
					'label' => 'Ключ',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'start',
					'label' => '',
					'rules' => '',
					'type' => 'int',
					'default' => 0
				),
				array(
					'field' => 'limit',
					'label' => '',
					'rules' => '',
					'type' => 'int',
					'default' => 100
				)
			),
			'checkLpuHasSmpSokrVolume'=>array(),
			'checkLpuHasVolume'=>array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'VolumeType_Code',
					'label' => 'Код объема',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Registry_endDate',
					'label' => 'Окончание периода',
					'rules' => 'required',
					'type' => 'date'
				)
			),
			'checkVizitCodeHasVolume' => array(
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Идентификатор профиля отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'VizitClass_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'VizitType_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TreatmentClass_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'isPrimaryVizit',
					'label' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'UslugaComplex_Date',
					'label' => 'Дата услуги',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnClass_SysNick',
					'label' => 'Класс',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PayType_SysNick',
					'label' => 'Вид оплаты',
					'rules' => '',
					'type' => 'string'
				)
			),
			'getTariffClassListByLpu' => array(
				array(
					'field' => 'Lpu_oid',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Date',
					'label' => 'Дата',
					'rules' => 'required',
					'type' => 'date'
				),
			),
			'getDiagListByLpuSectionProfile' => array(
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Идентификатор профиля отделения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Date',
					'label' => 'Дата',
					'rules' => 'required',
					'type' => 'date'
				),
			)
		);

		$this->load->database();
		$this->load->model('TariffVolumes_model', 'TariffVolumes_model');
	}

	/**
	 * Загрузка списка типов тарифов
	 */
	function loadTariffClassGrid() {
		$data = $this->ProcessInputData('loadTariffClassGrid', true);
		if ($data === false) { return false; }

		$response = $this->TariffVolumes_model->loadTariffClassGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Загрузка списка типов объемов
	 */
	function loadVolumeTypeGrid() {
		$data = $this->ProcessInputData('loadVolumeTypeGrid', true);
		if ($data === false) { return false; }

		$response = $this->TariffVolumes_model->loadVolumeTypeGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Загрузка списка типов тарифов
	 */
	function loadValuesGrid() {
		$data = $this->ProcessInputData('loadValuesGrid', false);
		if ($data === false) { return false; }

		$response = $this->TariffVolumes_model->loadValuesGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Метод проверки наличия у ЛПУ объёма с кодом СМП_сокр
	 */
	public function checkLpuHasSmpSokrVolume() {
		$data = $this->ProcessInputData('checkLpuHasSmpSokrVolume', true);
		if ($data === false) { return false; }

		$response = $this->TariffVolumes_model->checkLpuHasSmpSokrVolume($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Метод проверки наличия у МО объёма
	 */
	public function checkLpuHasVolume() {
		$data = $this->ProcessInputData('checkLpuHasVolume', true);
		if ($data === false) { return false; }

		$response = $this->TariffVolumes_model->checkLpuHasVolume($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Метод проверки наличия у ЛПУ объёма с кодом 2015-06Проф_Цель
	 */
	public function checkVizitCodeHasVolume() {
		$data = $this->ProcessInputData('checkVizitCodeHasVolume', true);
		if ($data === false) { return false; }

		$response = $this->TariffVolumes_model->checkVizitCodeHasVolume($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Загрузка списка полей для формы
	 */
	function getValuesFields() {
		$data = $this->ProcessInputData('getValuesFields', true);
		if ($data === false) { return false; }

		$response = $this->TariffVolumes_model->getValuesFields($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения данных')->ReturnData();

		return true;
	}

	/**
	 * Сохранение значения
	 */
	function saveAttributeValue() {
		$data = $this->ProcessInputData('saveAttributeValue', true);
		if ($data === false) { return false; }

		$response = $this->TariffVolumes_model->saveAttributeValue($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении значения')->ReturnData();

		return true;
	}

	/**
	 * Импорт объёмов по КСГ
	 */
	function importKSGVolumes() {
		$data = $this->ProcessInputData('importKSGVolumes', true);
		if ($data === false) { return false; }

		if (!isSuperAdmin()) {
			$this->ReturnError('Функционал только для суперадмина!');
			return false;
		}

		$response = $this->TariffVolumes_model->importKSGVolumes($data);
		$this->ProcessModelSave($response, true, 'Ошибка при импорте объёмов по КСГ')->ReturnData();

		return true;
	}

	/**
	 * Удаление значения
	 */
	function deleteValue() {
		$data = $this->ProcessInputData('deleteValue', true);
		if ($data === false) { return false; }

		$response = $this->TariffVolumes_model->deleteValue($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении значения')->ReturnData();

		return true;
	}

	/**
	 * Получение списка тарифов по МО
	 */
	function getTariffClassListByLpu() {
		$data = $this->ProcessInputData('getTariffClassListByLpu', true);
		if ($data === false) { return false; }

		$response = $this->TariffVolumes_model->getTariffClassListByLpu($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка идентификаторов МО по объемам профиля отделения
	 */
	function getDiagListByLpuSectionProfile() {
		$data = $this->ProcessInputData('getDiagListByLpuSectionProfile', true);
		if ($data === false) { return false; }

		$response = $this->TariffVolumes_model->getDiagListByLpuSectionProfile($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
}