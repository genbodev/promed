<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnNotifyHIV - контроллер формы "ОПЕРАТИВНОЕ ДОНЕСЕНИЕ о лице, в крови которого при исследовании в реакции иммуноблота выявлены антитела к ВИЧ (форма N 266/У-88)"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Alexander Permyakov 
 * @version      12.2012
 *
 * @property EvnNotifyHIV_model dbmodel
 */
class EvnNotifyHIV extends swController 
{

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(
		
			'del' => array(
				array(
					'field' => 'EvnNotifyHIV_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		
			'load' => array(
				array(
					'field' => 'EvnNotifyHIV_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		
			'save' => array(
				array(
					'field' => 'EvnNotifyHIV_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnNotifyHIV_pid',
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
					'field' => 'EvnNotifyHIV_setDT',
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
				array(
					'field' => 'MorbusType_id',
					'label' => 'MorbusType_id',
					'rules' => '',
					'type' => 'id'
				), 
				array('field' => 'HIVContingentType_pid','label' => 'Гражданство','rules' => 'required','type' => 'id'),
				array('field' => 'HIVContingentType_id_list','label' => 'Код контингента','rules' => '','type' => 'string'),//id через запятую
				array('field' => 'MorbusHIVLab_BlotDT','label' => 'Дата постановки реакции иммуноблота','rules' => '','type' => 'date'),
				array('field' => 'MorbusHIVLab_TestSystem','label' => 'Тип тест-системы','rules' => 'max_length[64]','type' => 'string'),
				array('field' => 'MorbusHIVLab_BlotNum','label' => 'N серии','rules' => 'max_length[64]','type' => 'string'),
				array('field' => 'MorbusHIVLab_BlotResult','label' => 'Выявленные белки и гликопротеиды','rules' => 'max_length[100]','type' => 'string'),
				array('field' => 'Lpuifa_id','label' => 'Учреждение, первично выявившее положительный результат в ИФА','rules' => '','type' => 'id'),
				array('field' => 'MorbusHIVLab_IFADT','label' => 'Дата ИФА','rules' => '','type' => 'date'),
				array('field' => 'MorbusHIVLab_IFAResult','label' => 'Результат ИФА','rules' => 'max_length[30]','type' => 'string'),
				array('field' => 'MorbusHIVLab_PCRDT','label' => 'Дата ПЦР','rules' => '','type' => 'date'),
				array('field' => 'MorbusHIVLab_PCRResult','label' => 'Результат ПЦР','rules' => 'max_length[30]','type' => 'string'),
				array('field' => 'LabAssessmentResult_iid','label' => 'Результат рекации иммуноблота','rules' => '','type' => 'id'),
				array('field' => 'MorbusHIV_confirmDate','label' => 'Дата подтверждения диагноза','rules' => '','type' => 'date'),
				array('field' => 'MorbusHIV_EpidemCode','label' => 'Эпидемиологический код','rules' => 'max_length[100]','type' => 'string'),
			)
    );


	/**
	 * Method description
	 */
	function __construct ()
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->model('EvnNotifyHIV_model', 'dbmodel');
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