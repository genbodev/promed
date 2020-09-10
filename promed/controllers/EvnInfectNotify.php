<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnInfectNotify - контроллер формы "Журнал Извещений форма №058/У"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Alexander Chebukin 
 * @version      08.2012
 *
 * @property EvnInfectNotify_model $dbmodel
 */

class EvnInfectNotify extends swController 
{

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(
		
			'load' => array(
				array(
					'field' => 'EvnInfectNotify_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		
			'isIsset' => array(
				array(
					'field' => 'EvnInfectNotify_pid',
					'label' => 'Идентификатор учётного документа',
					'rules' => '',
					'type' => 'id'
				)
			),
		
			'save' => array(
				array(
					'field' => 'EvnInfectNotify_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnInfectNotify_pid',
					'label' => 'Идентификатор движения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnInfectNotify_IsLabDiag',
					'label' => 'Подтвержден лабораторно',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnInfectNotify_DiseaseDate',
					'label' => 'Дата заболевания',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnInfectNotify_FirstTreatDate',
					'label' => 'Дата первичного обращения (выявления)',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnInfectNotify_SetDiagDate',
					'label' => 'Дата установления диагноза',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnInfectNotify_NextVizitDate',
					'label' => 'Дата последнего посещения детского учреждения, школы',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Место госпитализации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'PersonEvn_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnInfectNotify_PoisonDescr',
					'label' => 'Где произошло отравление, чем',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnInfectNotify_FirstMeasures',
					'label' => 'Проведенные первичные противоэпидемические мероприятия и дополнительные сведения',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnInfectNotify_FirstSESDT_Date',
					'label' => 'Дата первичной сигнализации в СЭС',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnInfectNotify_FirstSESDT_Time',
					'label' => 'Время первичной сигнализации в СЭС',
					'rules' => '',
					'type' => 'time'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Фамилия сообщившего',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnInfectNotify_ReceiverMessage',
					'label' => 'Кто принял сообщение',
					'rules' => '',
					'type' => 'string'
				)
			)
    );

	/**
	 * Method description
	 */
	function __construct()
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->model('EvnInfectNotify_model', 'dbmodel');
	}
	
	
	/**
	 * Проверка наличия извещения 
	 */
	function isIsset() {
		
		$data = $this->ProcessInputData('isIsset', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->isIsset($data);

		if (count($response) > 0) {
			$this->ReturnData(array("success" => true));
		} else {
			$this->ReturnData(array("success" => false));
		}
			
		return true;
		
	}
	
	
	/**
	 * Загрузка 
	 */
	function load() {
		
		$data = $this->ProcessInputData('load', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	
	
	/**
	 * Сохранение
	 */
	function save()
	{
		$data = $this->ProcessInputData('save', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
		
	}
	
}