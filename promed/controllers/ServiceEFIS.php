<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ServiceEFIS - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 *
 * @property Efis_model dbmodel
 */

class ServiceEFIS extends swController {
	public $inputRules = array(
		'runImport' => array(
			array(
				'field' => 'objects',
				'label' => 'Список объектов для импорта',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'importWhsDocumentUc' => array(
			array(
				'field'	=> 'WhsDocumentUc_Num',
				'label'	=> 'Номер договора',
				'rules'	=> '',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'suppl_agreement',
				'label'	=> 'Номер доп соглашения',
				'rules'	=> '',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'import_Type',
				'label'	=> 'Тип импорта', //1 - импорт договора; 2 - импорт доп соглашения
				'rules'	=> '',
				'type'	=> 'int'
			)
		),
		'importDocumentUc'	=> array(
			array(
				'field' => 'Document_Number',
				'label'	=> 'Номер накладной',
				'rules'	=> 'required',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'MedService_id',
				'label'	=> 'Идентификатор службы',
				'rules'	=> '',
				'type'	=> 'int'
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

		$this->load->model('ServiceEFIS_model', 'dbmodel');
	}

	/**
	 * Запуск импорта данных из ЕФИС
	 */
	function runImport() {
		$data = $this->ProcessInputData('runImport');
		if ($data === false) return false;

		$response = $this->dbmodel->runImport($data);

		$this->ProcessModelSave($response, true, 'Ошибка импорта данных из сервиса ЕФИС СК-Фармация')->ReturnData();
		return true;
	}

	/**
	*	Запуск импорта договоров / доп соглашений
	*/
	function importWhsDocumentUc() {
		$data = $this->ProcessInputData('importWhsDocumentUc', true);
		if ($data === false)
			return false;

		
		$response = $this->dbmodel->importWhsDocumentUc($data);
		$this->ProcessModelSave($response, true, 'Ошибка импорта данных из СУР')->ReturnData();
		return true;
	}

	/**
	*	Запуск импорта накладной
	*/
	function importDocumentUc(){
		$data = $this->ProcessInputData('importDocumentUc', true);
		if ($data === false)
			return false;
		$response = $this->dbmodel->importDocumentUc($data);
		$this->ProcessModelSave($response, true, 'Ошибка при получении данных')->ReturnData();
		return true;
		//$this->ReturnData($response);
	}

	/**
	*	test
	*/
	function test() {
		$this->dbmodel->test();
	}
}