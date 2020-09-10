<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Msk_BedDowntimeLog - контроллер для работы с формой "простой коек"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @region       Msk
 * @access       public
 * @copyright    Copyright (c) 2020 Swan Ltd.
 * @author       Borisov Igor
 * @version      18.04.2020
 * @property Msk_BedDowntimeLog_model dbmodel
 */
class Msk_BedDowntimeLog extends swController
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->init();
	}

	/**
	 * Дополнительная инициализация
	 */
	private function init()
	{
		$this->load->database();
		$this->load->model('Msk_BedDowntimeLog_model', 'dbmodel');
	}

	/**
	 * @var string[]
	 */
	public $inputRules = [
		'getEvnBedDowntimeInfo' => [
			[
				'field' => 'EvnDirection_id',
				'label' => 'Направление',
				'rules' => 'required',
				'type' => 'id'
			],
			[
				'field' => 'LpuSection',
				'label' => 'Отделение',
				'rules' => 'required',
				'type' => 'int'
			],
			[
				'field' => 'BedProfile_id',
				'label' => 'Профиль коек',
				'rules' => 'required',
				'type' => 'int'
			],
			[
				'field' => 'BedDowntimeLog_Count',
				'label' => 'Количество коек на момент расчета',
				'rules' => 'required',
				'type' => 'int'
			],
			[
				'field' => 'BedDowntimeLog_begDate',
				'label' => 'Дата начала простоя',
				'rules' => 'required',
				'type' => 'date'
			],
			[
				'field' => 'BedDowntimeLog_endDate',
				'label' => 'Дата окончания простоя',
				'rules' => 'required',
				'type' => 'date'
			],
		],
		'getEvnBedDowntimeLog' => [
			[
				'field' => 'begDate',
				'label' => 'Дата начала простоя',
				'rules' => 'required',
				'type' => 'date'
			],
			[
				'field' => 'endDate',
				'label' => 'Дата окончания простоя',
				'rules' => 'required',
				'type' => 'date'
			],
			[
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => 'required',
				'type' => 'int'
			],
			[
				'field' => 'limit',
				'label' => 'Количество',
				'rules' => '',
				'type' => 'int'
			],
			[
				'field' => 'start',
				'label' => 'Начальная запись',
				'rules' => '',
				'type' => 'int'
			],
			[
				'field' => 'BedProfile_id',
				'label' => 'Профиль коек',
				'rules' => '',
				'type' => 'int'
			],
			[
				'field' => 'Export_BedProfile_id',
				'label' => 'Профиль коек для формы экспорта',
				'rules' => '',
				'type' => 'int'
			],
			[
				'field' => 'sortField',
				'label' => 'Сортировка по полю',
				'rules' => '',
				'type' => 'string'
			],
			[
				'field' => 'sortDirection',
				'label' => 'Тип сортировки',
				'rules' => '',
				'type' => 'string'
			],
		],
		'loadBedDowntimeJournalForm' => [
			[
				'field' => 'BedDowntimeLog_id',
				'label' => 'id',
				'rules' => 'required',
				'type' => 'int'
			],
		],
		'getBedDowntimeLog_Count' => [
			[
				'field' => 'LpuSection_id',
				'label' => 'id',
				'rules' => 'required',
				'type' => 'int'
			],
			[
				'field' => 'LpuSectionBedProfile_id',
				'label' => 'id',
				'rules' => 'required',
				'type' => 'int'
			],
		],
		'getSumEnvPS' => [
			[
				'field' => 'LpuSection_id',
				'label' => 'id',
				'rules' => 'required',
				'type' => 'int'
			],
			[
				'field' => 'LpuSectionBedProfile_id',
				'label' => 'id',
				'rules' => 'required',
				'type' => 'int'
			],
			[
				'field' => 'begDate',
				'label' => 'Дата начала',
				'rules' => 'required',
				'type' => 'date'
			],
			[
				'field' => 'endDate',
				'label' => 'Дата окончания',
				'rules' => 'required',
				'type' => 'date'
			],
		],
		'deleteBedDowntimeRecord' => [
			[
				'field' => 'BedDowntimeLog_id',
				'label' => 'id',
				'rules' => 'required',
				'type' => 'int'
			],
		],
		'saveBedDowntimeLog' => [
			[
				'field' => 'BedDowntimeLog_id',
				'label' => 'id',
				'rules' => '',
				'type' => 'int'
			],
			[
				'field' => 'pmuser_id',
				'label' => '',
				'rules' => 'required',
				'type' => 'int'
			],
			[
				'field' => 'action',
				'label' => 'Действие',
				'rules' => '',
				'type' => 'string'
			],
			[
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => 'required',
				'type' => 'int'
			],
			[
				'field' => 'LpuSectionBedProfile_id',
				'label' => 'Профиль коек',
				'rules' => 'required',
				'type' => 'int'
			],
			[
				'field' => 'begDate',
				'label' => 'Дата начала',
				'rules' => 'required',
				'type' => 'date'
			],
			[
				'field' => 'endDate',
				'label' => 'Дата окончания',
				'rules' => 'required',
				'type' => 'date'
			],
			[
				'field' => 'BedDowntimeLog_Count',
				'label' => 'Количество коек на момент расчета',
				'rules' => 'required',
				'type' => 'int'
			],
			[
				'field' => 'BedDowntimeLog_Count',
				'label' => 'Количество коек на момент расчета',
				'rules' => 'required',
				'type' => 'int'
			],
			[
				'field' => 'BedDowntimeLog_RepairCount',
				'label' => 'Из них на ремонте',
				'rules' => 'required',
				'type' => 'int'
			],
			[
				'field' => 'BedDowntimeLog_ReasonsCount',
				'label' => 'По другим причинам',
				'rules' => 'required',
				'type' => 'int'
			],
			[
				'field' => 'BedDowntimeLog_ReasonsCount',
				'label' => 'По другим причинам',
				'rules' => 'required',
				'type' => 'int'
			],
			[
				'field' => 'BedDowntimeLog_Reasons',
				'label' => 'Причины',
				'rules' => '',
				'type' => 'string'
			],
		]
	];


	/**
	 *  Получение списка коек
	 *  На выходе: JSON-строка
	 *  Используется: форма журнала простоя коек
	 */
	public function loadBedDowntimeJournal()
	{
		$data = $this->ProcessInputData('getEvnBedDowntimeLog', true, true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadBedDowntimeJournal($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * @return bool
	 * @throws PHPExcel_Exception
	 * @throws PHPExcel_Reader_Exception
	 * @throws PHPExcel_Writer_Exception
	 */
	function exportToXLS()
	{
		$data = $this->ProcessInputData('getEvnBedDowntimeLog', true, true);
		if ($data === false) {
			return false;
		}

		$exportData = $this->dbmodel->getDataForXLS($data);

		if (!is_array($exportData)) {
			$this->ReturnError('Ошибка при запросе данных для выгрузки');
			return false;
		}
		if (count($exportData) == 0) {
			$this->ReturnError('Отсутствуют данные для выгрузки');
			return false;
		}

		$fieldsMap = $this->dbmodel->getFieldsMapForXLS();

		$fileName = 'tub_register_' . time();

		require_once("promed/libraries/PHPExcel.php");
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties();
		$objPHPExcel->getActiveSheet()->setTitle('Лист1');
		$sheet = $objPHPExcel->setActiveSheetIndex(0);

		$colIdx = 0;
		$rowIdx = 1;
		foreach ($fieldsMap as $name => $title) {
			$sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, $title);
			$colIdx++;
		}
		foreach ($exportData as $rowData) {
			$rowIdx++;
			$colIdx = 0;
			foreach ($fieldsMap as $name => $title) {
				if (isset($rowData[$name])) {
					$sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, $rowData[$name]);
				}
				$colIdx++;
			}
		}

		require_once("promed/libraries/PHPExcel/IOFactory.php");
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		$path = EXPORTPATH_ROOT . 'tub_register';
		if (!file_exists($path)) {
			mkdir($path);
		}

		$file = "{$path}/{$fileName}.xlsx";
		$objWriter->save($file);

		$response = array('success' => true, 'file' => $file);

		$this->ProcessModelSave($response, true, 'Ошибка при выгрузке')->ReturnData();

		return true;
	}

	/**
	 * @return bool
	 */
	public function loadBedDowntimeJournalForm()
	{
		$data = $this->ProcessInputData('loadBedDowntimeJournalForm', true, true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadBedDowntimeJournalForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * @return bool
	 */
	public function getBedDowntimeLog_Count()
	{
		$data = $this->ProcessInputData('getBedDowntimeLog_Count', true, true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->getBedDowntimeLog_Count($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * @return bool
	 */
	public function getSumEnvPS()
	{
		$data = $this->ProcessInputData('getSumEnvPS', true, true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->getSumEnvPS($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * @return bool
	 */
	public function deleteBedDowntimeRecord()
	{
		$data = $this->ProcessInputData('deleteBedDowntimeRecord', true, true);
		if ($data === false) {
			return false;
		}

		$this->dbmodel->deleteBedDowntimeRecord($data);

		return true;
	}

	/**
	 * @return bool
	 */
	public function saveBedDowntimeLog()
	{
		$data = $this->ProcessInputData('saveBedDowntimeLog', true, true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->saveBedDowntimeLog($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
}