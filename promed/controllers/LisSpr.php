<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* LisSpr - контроллер для работы со справочниками ЛИС
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Lis
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
*/
class LisSpr extends swController {
	public $inputRules = array(
		'loadEquipmentsGrid' => array(
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы',
				'rules' => 'required',
				'type' => 'id',
			)
		),
		'loadTestsGrid' => array(
			array(
				'field' => 'UslugaComplexMedService_pid',
				'label' => 'Идентификатор родительской услуги',
				'rules' => 'required',
				'type' => 'id',
			)
		),
		'loadEquipmentTestsGrid' => array(
			array(
				'field' => 'equipment_id',
				'label' => 'Идентификатор анализатора',
				'rules' => 'required',
				'type' => 'id',
			)
		),
		'loadUnitList' => array(
			array(
				'field' => 'MedService_id',
				'label' => 'Служба',
				'rules' => '',
				'type' => 'id',
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Услуга',
				'rules' => '',
				'type' => 'id',
			),
			array(
				'field' => 'Analyzer_id',
				'label' => 'Анализатор',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnUslugaPar_id',
				'label' => 'Услуга',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RefValues_id',
				'label' => 'Референсное значение',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadTestUnitList' => array(
			array(
				'field' => 'MedService_id',
				'label' => 'Служба',
				'rules' => '',
				'type' => 'id',
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Услуга',
				'rules' => '',
				'type' => 'id',
			),
			array(
				'field' => 'Analyzer_id',
				'label' => 'Анализатор',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaTest_id',
				'label' => 'Услуга',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RefValues_id',
				'label' => 'Референсное значение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UnitOld_id',
				'label' => 'Старое значение единицы измерения',
				'rules' => '',
				'type' => 'id'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		if ($this->usePostgreLis) {
			$this->load->swapi('lis');
		} else {
			$this->load->database();
			$this->load->model('LisSpr_model', 'dbmodel');
		}
	}

	/**
	 * Скрипт перехода на новые "услуги анализаторов" на лабораторной службе
	 */
	function convertUslugaComplexMedServiceToAnalyzerTest()
	{
		if (!isSuperadmin()) {
			$this->ReturnError('Функционал только для суперадмина');
			return false;
		}
		
		$this->dbmodel->convertUslugaComplexMedServiceToAnalyzerTest();
	}
	
	/**
	 * Получение списка анализаторов
	 */
	function loadEquipmentsGrid()
	{
		$data = $this->ProcessInputData('loadEquipmentsGrid', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET('LisSpr/EquipmentsGrid', $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->loadEquipmentsGrid($data);
			$this->ProcessModelList($response,true,true)->ReturnData();
		}
	}
	
	/**
	 * Получение списка тестов
	 */
	function loadTestsGrid()
	{
		$data = $this->ProcessInputData('loadTestsGrid', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET('LisSpr/TestsGrid', $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->loadTestsGrid($data);
			$this->ProcessModelList($response,true,true)->ReturnData();
		}
	}
	
	/**
	 * Получение списка тестов анализатора ЛИС
	 */
	function loadEquipmentTestsGrid()
	{
		$data = $this->ProcessInputData('loadEquipmentTestsGrid', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET('LisSpr/EquipmentTestsGrid', $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->loadEquipmentTestsGrid($data);
			$this->ProcessModelList($response,true,true)->ReturnData();
		}
	}
	
	/**
	 * Получение списка единиц измерения
	 */
	function loadUnitList()
	{
		$data = $this->ProcessInputData('loadUnitList', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET('LisSpr/UnitList', $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->loadUnitList($data);
			$this->ProcessModelList($response,true,true)->ReturnData();
		}
	}
	
	/**
	 * Получение списка единиц измерения
	 */
	function loadTestUnitList()
	{
		$data = $this->ProcessInputData('loadTestUnitList', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET('LisSpr/TestUnitList', $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->loadTestUnitList($data);
			$this->ProcessModelList($response,true,true)->ReturnData();
		}
	}
}