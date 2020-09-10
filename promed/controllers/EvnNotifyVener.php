<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnNotifyVener - контроллер формы "Извещение об венерическом заболевании"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Alexander Permyakov 
 * @version      10.2012
 *
 * @property EvnNotifyVener_model $dbmodel
 */

class EvnNotifyVener extends swController 
{

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(
		
			'del' => array(
				array(
					'field' => 'EvnNotifyVener_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		
			'load' => array(
				array(
					'field' => 'EvnNotifyVener_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		
			'save' => array(
				array(
					'field' => 'EvnNotifyVener_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnNotifyVener_pid',
					'label' => 'Идентификатор движения или посещения',
					'rules' => 'required',
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
					'label' => 'Идентификатор состояния человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnNotifyVener_setDT',
					'label' => 'Дата заполнения извещения',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач, заполнивший извещение',
					'rules' => 'required',
					'type' => 'id'
				),
				array('field' => 'PersonCategoryType_id','label' => 'Категория населения','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyVener_OtherPersonCategory','label' => 'Другое ведомство','rules' => '','type' => 'string'),
				//array('field' => 'EvnNotifyVener_IsDecreeGroup','label' => 'Принадлежность к декретированным группам','rules' => '','type' => 'id'),
				array('field' => 'Diag_id','label' => 'Диагноз по МКБ-10','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyVener_DiagDT','label' => 'Дата установления диагноза','rules' => '','type' => 'date'),
				array('field' => 'EvnNotifyVener_IsReInfect','label' => 'Реинфекция','rules' => '','type' => 'id'),
				array('field' => 'VenerPathTransType_id','label' => 'Путь передачи','rules' => '','type' => 'id'),
				array('field' => 'VenerPregPeriodType_id','label' => 'Период беременности','rules' => '','type' => 'id'),
				array('field' => 'VenerLabConfirmType_id','label' => 'Лабораторное подтверждение','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyVener_OtherLabConfirm','label' => 'Другой вид лабораторного подтверждения','rules' => '','type' => 'string'),
				array('field' => 'VenerDetectionPlaceType_id','label' => 'Место выявления заболевания','rules' => '','type' => 'id'),
				array('field' => 'LpuSectionProfile_id','label' => 'Профиль койки','rules' => '','type' => 'id'),
				array('field' => 'MedPersonal_pid','label' => 'Специалист выявивший заболевание','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyVener_OtherDetectPlace','label' => 'Другое место выявления заболевания','rules' => '','type' => 'string'),
				array('field' => 'VenerDetectionFactType_id','label' => 'Обстоятельства выявления','rules' => '','type' => 'id'),
				array('field' => 'MedPersonal_fid','label' => 'Специалист к которому обратились','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyVener_OtherDetectFact','label' => 'Другие обстоятельства выявления','rules' => '','type' => 'string'),
				
				array('field' => 'EvnNotifyVener_IsIFA', 'label' => 'ИФА', 'rules' => '', 'type' => 'checkbox', 'default'=>0),
				array('field' => 'EvnNotifyVener_IsImmun', 'label' => 'Иммуноблот', 'rules' => '', 'type' => 'checkbox', 'default'=>0),
				array('field' => 'EvnNotifyVener_IsKSR', 'label' => 'КСР', 'rules' => '', 'type' => 'checkbox', 'default'=>0),
				array('field' => 'EvnNotifyVener_IsRIBT', 'label' => 'РИБТ', 'rules' => '', 'type' => 'checkbox', 'default'=>0),
				array('field' => 'EvnNotifyVener_IsRIF', 'label' => 'РИФ', 'rules' => '', 'type' => 'checkbox', 'default'=>0),
				array('field' => 'EvnNotifyVener_IsRMP', 'label' => 'РМП', 'rules' => '', 'type' => 'checkbox', 'default'=>0),
				array('field' => 'EvnNotifyVener_IsRPGA', 'label' => 'РПГА', 'rules' => '', 'type' => 'checkbox', 'default'=>0),
				array('field' => 'EvnNotifyVener_IsRPR', 'label' => 'РПР', 'rules' => '', 'type' => 'checkbox', 'default'=>0),
				array('field' => 'EvnNotifyVener_IsTPM', 'label' => 'ТПМ', 'rules' => '', 'type' => 'checkbox', 'default'=>0),
				array('field' => 'VenerSocGroup_id','label' => 'Специалист к которому обратились','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyVener_Pathogen','label' => 'Социальная группа','rules' => '','type' => 'string')
			)
    );

	/**
	 * Method description
	 */
	function __construct()
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->model('EvnNotifyVener_model', 'dbmodel');
	}
	
	/**
	 * Загрузка  формы
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
		$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		$response = $this->dbmodel->doSave($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	
	/**
	 * Удаление
	 */
	function del()
	{
		$data = $this->ProcessInputData('del', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->del($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
		
	}
	
}