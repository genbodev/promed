<?php

defined( 'BASEPATH' ) or die( 'No direct script access allowed' );

/**
 * MedService - контроллер работы со службами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @property MedService_model MedService_model
 */
class MedService4E extends swController {

	public $inputRules = array(
		'loadMedServiceMedPersonalList' => array(
			array( 'field' => 'Lpu_pid', 'label' => 'Идентификатор родительской МО', 'rules' => '', 'type' => 'id' ),
			array( 'field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id' ),
			array( 'field' => 'MedServiceType_id', 'label' => 'Тип', 'rules' => '', 'type' => 'id' )
		),
		'getLpusWithMedService' => array(
			array(
				'field' => 'ConcreteLpu_id',
				'label' => 'ИД МО',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'MedServiceType_id',
				'label' => 'Тип службы',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'comAction',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'KLAreaStat_idEdit',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'KLSubRgn_id',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'KLCity_id',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'KLTown_id',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'KLStreet_id',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CmpCallCard_Dom',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Age',
				'rules' => '',
				'type' => 'int'
			),
		),
		'loadMedPersonalRolesExpertList' => array(),
		'getVolumeTypeLpuHospitalizationSMPExist' => array(),
		'loadMedPersonalLpuBuildings' => array(),
		'loadLpu' => array(
			array(
				'field' => 'viewAllMO',
				'label' => 'Показать все МО',
				'rules' => '',
				'type' => 'checkbox'
			),
		),
		'loadNmpMedServiceList' => array(
			array(
				'field' => 'Lpu_ppdid',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'isClose',
				'rules' => '',
				'type' => 'int'
			)
		),
		'loadSectionProfileByMO' => array(
			array(
				'field' => 'Lpu_id',
				'rules' => 'required',
				'type' => 'int'
			)
		)
	);

	/**
	 * Конструктор
	 */
	public function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model( 'MedService_model4E', 'MedService_model4E' );
	}

	/**
	 *  Функция загрузки хранилища для комбобокса врачей служб
	 *  На выходе: JSON-строка
	 *  Используется: комбобокс врачей служб
	 */
	function loadMedServiceMedPersonalList() {
		$data = $this->ProcessInputData( 'loadMedServiceMedPersonalList', true );
		if ( $data ) {
			$response = $this->MedService_model4E->loadMedServiceMedPersonalList( $data );
			$this->ProcessModelList( $response, true, true )->ReturnData();
		}
	}

	/**
	 * 	Возвращает все ЛПУ, в которых есть служба определенного типа
	 */
	function getLpusWithMedService() {
		$data = $this->ProcessInputData( 'getLpusWithMedService', true );
		if ( $data ) {
			$response = $this->MedService_model4E->getLpusWithMedService( $data );
			$this->ProcessModelList( $response, true, true )->ReturnData();
		}
	}
		
	/**
	 * 	Возвращает  список МО c указанным профилем отделения
	 */
	function loadLpu() {
		$data = $this->ProcessInputData( 'loadLpu', true );

		if ( $data ) {
			$response = $this->MedService_model4E->loadLpu( $data );
			$this->ProcessModelList( $response, true, true )->ReturnData();
		}
	}
	
	/**
	 * 	 Возвращает список подстанций доступных для пользователя
	 */
	function loadMedPersonalLpuBuildings() {
		$data = $this->ProcessInputData( 'loadMedPersonalLpuBuildings', true );

		if ( $data ) {
			$response = $this->MedService_model4E->loadMedPersonalLpuBuildings( $data );
			$this->ProcessModelList( $response, true, true )->ReturnData();
		}
	}

	/**
	 * Возвращает список врачей привязанных к пользователям имеющим службу "Эксперта БСМЭ"
	 *
	 * @return output JSON
	 */
	public function loadMedPersonalRolesExpertList() {
		if ( !( $data = $this->ProcessInputData( 'loadMedPersonalRolesExpertList', true ) ) ) {
			return;
		}

		$response = $this->MedService_model4E->loadMedPersonalRolesExpertList( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

	/**
	 * Получение списка служб НМП региона
	 */
	public function loadNmpMedServiceList() {
		$data = $this->ProcessInputData('loadNmpMedServiceList');
		if (!$data) return;

		$response = $this->MedService_model4E->loadNmpMedServiceList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	/**
	 * 	Возвращает  список профилей всех отделений МО госпитализации
	 */
	function loadSectionProfileByMO() {
		$data = $this->ProcessInputData( 'loadSectionProfileByMO', true );

		if ( $data ) {
			$response = $this->MedService_model4E->loadSectionProfileByMO( $data );
			$this->ProcessModelList( $response, true, true )->ReturnData();
		}
	}
	/**
	 * 	Существуют ли значения атрибутов вида объема LpuHospitalizationSMP 
	 */
	function getVolumeTypeLpuHospitalizationSMPExist() {

		$data = $this->ProcessInputData( 'getVolumeTypeLpuHospitalizationSMPExist', true );

		if ( $data ) {
			$response = $this->MedService_model4E->getVolumeTypeLpuHospitalizationSMPExist( $data );
			$this->ReturnData($response);
		}
	}
}
