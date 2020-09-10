<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MeasuresRehab - контроллер для работы c мероприятиями реабилитации и абилитации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			12.12.2016
 *
 * @property MeasuresRehab_model dbmodel
 */

class MeasuresRehab extends swController {
	protected  $inputRules = array(
		'loadEvnUslugaList' => array(
			array(
				'field' => 'IPRARegistry_id',
				'label' => 'Идентификатор записи в регистре ИПРА',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadEvnList' => array(
			array(
				'field' => 'IPRARegistry_id',
				'label' => 'Идентификатор записи в регистре ИПРА',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadReceptOtovList' => array(
			array(
				'field' => 'IPRARegistry_id',
				'label' => 'Идентификатор записи в регистре ИПРА',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadMeasuresRehabGrid' => array(
			array(
				'field' => 'IPRARegistry_id',
				'label' => 'Идентификатор записи в регистре ИПРА',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadMeasuresRehabGridPerson' => array(
			array(
				'field' => 'EvnPrescrMse_id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'saveMeasuresForMedicalRehabilitation' => array(
			array('field' => 'MeasuresRehabMSE_BegDate',	'label' => 'Дата начала',		'rules' => 'required',	'type' => 'date'),
			array('field' => 'MeasuresRehabMSE_EndDate',	'label' => 'Дата окончания',	'rules' => '',			'type' => 'date'),
			array('field' => 'MeasuresRehabMSE_Type',		'label' => 'Тип мероприятия',	'rules' => '',			'type' => 'string'),
			array('field' => 'MeasuresRehabMSE_SubType',	'label' => 'Подтип мероприятия','rules' => '',			'type' => 'string'),
			array('field' => 'MeasuresRehabMSE_Name',		'label' => 'Наименование',		'rules' => 'required',	'type' => 'string'),
			array('field' => 'MeasuresRehabMSE_Result',		'label' => 'Результат',			'rules' => 'required',	'type' => 'string'),
			array('field' => 'action',						'label' => 'action',			'rules' => 'required',	'type' => 'string'),
			array('field' => 'MeasuresRehabMSE_id',			'label' => 'Идентификатор',		'rules' => '',			'type' => 'id'),
			array('field' => 'EvnPrescrMse_id',				'label' => 'Идентификатор',		'rules' => 'required',	'type' => 'id')
		),
		'deleteMeasuresForMedicalRehabilitation' => array(
			array(
				'field' => 'MeasuresRehabMSE_id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'clearMeasuresFMR' => array(
			array(
				'field' => 'EvnPrescrMse_id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'downloadIPRAinMeasuresFMR' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadMeasuresRehabExportGrid' => array(
			array(
				'field' => 'MeasuresRehab_begRange',
				'label' => 'Начало периода',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'MeasuresRehab_endRange',
				'label' => 'Окончание периода',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'LpuAttach_id',
				'label' => 'МО прикрепления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MeasuresRehab_IsExport',
				'label' => 'Мероприятие передано',
				'rules' => '',
				'type' => 'id'
			),
		),
		'loadMeasuresRehabForm' => array(
			array(
				'field' => 'MeasuresRehab_id',
				'label' => 'Идентификатор мероприятия реабилитации',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'saveMeasuresRehab' => array(
			array(
				'field' => 'MeasuresRehab_id',
				'label' => 'Идентификатор мероприятия реабилитации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'IPRARegistry_id',
				'label' => 'Идентификатор записи в регистре ИПРА',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MeasuresRehabType_id',
				'label' => 'Идентификатор типа мероприятия',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MeasuresRehabSubType_id',
				'label' => 'Идентификатор подтипа мероприятия',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MeasuresRehab_setDate',
				'label' => 'Дата мероприятия',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'MeasuresRehab_OrgName',
				'label' => 'Наименование организации',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Идентификатор организации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MeasuresRehab_Name',
				'label' => 'Наименование мероприятия',
				'rules' => 'trim|max_length[128]',
				'type' => 'string'
			),
			array(
				'field' => 'MeasuresRehabResult_id',
				'label' => 'Идентификатор результата мероприятия',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnUsluga_id',
				'label' => 'Идентификатор услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор случая лечения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ReceptOtov_id',
				'label' => 'Идентификатор отоваренного рецепта',
				'rules' => '',
				'type' => 'id'
			),
		),
		'deleteMeasuresRehab' => array(
			array(
				'field' => 'MeasuresRehab_id',
				'label' => 'Идентификатор мероприятия реабилитации',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'exportMeasuresRehab' => array(
			array(
				'field' => 'MeasuresRehab_ids',
				'label' => 'Список идентификатор мероприятия реабилитации',
				'rules' => 'required',
				'type' => 'json_array'
			),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('MeasuresRehab_model', 'dbmodel');
	}

	/**
	 * Получение списка услуг, доступных для мероприятия реабилитации
	 */
	function loadEvnUslugaList() {
		$data = $this->ProcessInputData('loadEvnUslugaList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnUslugaList($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка случаев лечения, доступных для мероприятия реабилитации
	 */
	function loadEvnList() {
		$data = $this->ProcessInputData('loadEvnList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnList($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка медикаментов, доступных для мероприятия реабилитации
	 */
	function loadReceptOtovList() {
		$data = $this->ProcessInputData('loadReceptOtovList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadReceptOtovList($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Получение списка мероприятий реабилитации или абилитации по пациенту
	 */
	function loadMeasuresRehabGridPerson() {
		$data = $this->ProcessInputData('loadMeasuresRehabGridPerson');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadMeasuresRehabGridPerson($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * сохранение формы «Мероприятия по медицинской реабилитации»
	 */
	function saveMeasuresForMedicalRehabilitation() {
		$data = $this->ProcessInputData('saveMeasuresForMedicalRehabilitation');
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->saveMeasuresForMedicalRehabilitation($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	
	/**
	 * удаление записи из формы «Мероприятия по медицинской реабилитации»
	 */
	function deleteMeasuresForMedicalRehabilitation() {
		$data = $this->ProcessInputData('deleteMeasuresForMedicalRehabilitation');
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->deleteMeasuresForMedicalRehabilitation($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение списка мероприятий реабилитации или абилитации
	 */
	function loadMeasuresRehabGrid() {
		$data = $this->ProcessInputData('loadMeasuresRehabGrid');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadMeasuresRehabGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка мероприятий реабилитации или абилитации для экспорта
	 */
	function loadMeasuresRehabExportGrid() {
		$data = $this->ProcessInputData('loadMeasuresRehabExportGrid');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadMeasuresRehabExportGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение данных мероприятия реабилитиции для редактирования
	 */
	function loadMeasuresRehabForm() {
		$data = $this->ProcessInputData('loadMeasuresRehabForm');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadMeasuresRehabForm($data);

		$this->ProcessModelList($response)->ReturnData();
		return true;
	}

	/**
	 * Сохранение мероприятия реабилитации
	 */
	function saveMeasuresRehab() {
		$data = $this->ProcessInputData('saveMeasuresRehab');
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveMeasuresRehab($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Удаление мероприятия реабилитации
	 */
	function deleteMeasuresRehab() {
		$data = $this->ProcessInputData('deleteMeasuresRehab');
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteMeasuresRehab($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Экспорт данных о мероприятиях реабилитации
	 */
	function exportMeasuresRehab() {
		$data = $this->ProcessInputData('exportMeasuresRehab');
		if ($data === false) { return false; }

		$resp = $this->dbmodel->genMeasuresRehabExportScript($data);

		if (!$this->dbmodel->isSuccessful($resp)) {
			$response = $resp;
		} else {
			$export_path = EXPORTPATH_ROOT . "measures_rehab/";
			$file_sign = "measures_rehab_".time();

			if (!file_exists($export_path)) {
				mkdir($export_path);
			}

			$file_name = $export_path . $file_sign . ".sql";

			file_put_contents($file_name, $resp[0]['SqlExportInsert']);

			$file_zip_name = $export_path . $file_sign . ".zip";

			$zip = new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile($file_name, $file_sign.'.sql');
			$zip->close();

			unlink($file_name);

			$response = array(array(
				'success' => true,
				'link' => $file_zip_name
			));
		}

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	
	/**
	 *	получение данных по "Мероприятия по медицинской реабилитации"
	 */
	function getMeasuresForMedicalRehabilitation()
	{
		$val = array();
		$data = $this->ProcessInputData('getMeasuresForMedicalRehabilitation', true);
		if($data){
			$response = $this->dbmodel->getEvnStickOfYear($data);
			if(is_array($response) && count($response)>0){
				$i = 1;
				foreach($response as $row) {
					$row['num'] = $i;
					$val[] = $row;
					$i++;
				}
			}
			$this->ProcessModelList($val, true, true)->ReturnData();
		}
	}
	
	/**
	 * чоистка списка мероприятий  из формы «Мероприятия по медицинской реабилитации»
	 * загруженного из регистра ИПРА
	 */
	function clearMeasuresFMR(){
		$data = $this->ProcessInputData('clearMeasuresFMR');
		if ($data === false) { return false; }

		$response = $this->dbmodel->clearMeasuresFMR($data);
		
		$this->ProcessModelList(array_keys($response), true, true)->ReturnData();
		return true;
	}
	
	/**
	 * загрузка мероприятий в форму «Мероприятия по медицинской реабилитации»
	 * из регистра ИПРА
	 */
	function downloadIPRAinMeasuresFMR(){
		$data = $this->ProcessInputData('downloadIPRAinMeasuresFMR');
		if ($data === false) { return false; }

		$response = $this->dbmodel->downloadIPRAinMeasuresFMR($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}