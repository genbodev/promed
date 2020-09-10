<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnNotifyHIVDisp - контроллер формы Донесение о подтверждении диагноза у ребенка, рожденного ВИЧ-инфицированной матерью
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Alexander Permyakov 
 * @version      07.2013
 */
/**
 * @property EvnNotifyHIVDisp_model $dbmodel
 */

class EvnNotifyHIVDisp extends swController 
{

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(
		
			'del' => array(
				array(
					'field' => 'EvnNotifyHIVDisp_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		
			'load' => array(
				array(
					'field' => 'EvnNotifyHIVDisp_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		
			'save' => array(
				array(
					'field' => 'EvnNotifyHIVDisp_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnNotifyHIVDisp_pid',
					'label' => 'Идентификатор движения или посещения',
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
					'label' => 'Идентификатор состояния человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Ребенок',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnNotifyHIVDisp_setDT',
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
					'field' => 'Morbus_id',
					'label' => 'Заболевание',
					'rules' => '',
					'type' => 'id'
				),
				
				array('field' => 'MorbusHIVChem_data','label' => 'Противоретровирусная терапия','rules' => '','type' => 'string'),//JsonStr
				array('field' => 'MorbusHIVVac_data','label' => 'Вакцинация','rules' => '','type' => 'string'),//JsonStr
				array('field' => 'MorbusHIVSecDiag_data','label' => 'Вторичные заболевания и оппортунистические инфекции','rules' => '','type' => 'string'),//JsonStr
				
				array('field' => 'Person_mid','label' => 'Мать','rules' => 'required','type' => 'id'),
				array('field' => 'EvnNotifyHIVDisp_IsRefuse','label' => 'Отказной ребенок','rules' => '','type' => 'id'),
				array('field' => 'HIVChildType_id','label' => 'Ребенок','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyHIVDisp_OtherChild','label' => 'Ребенок:прочее','rules' => 'max_length[30]','type' => 'string'),
				array('field' => 'EvnNotifyHIVDisp_Place','label' => 'Место пребывания','rules' => 'max_length[30]','type' => 'string'),
				array('field' => 'EvnNotifyHIVDisp_DiagDT','label' => 'Дата установления диагноза ВИЧ-инфекции','rules' => '','type' => 'date'),
				array('field' => 'EvnNotifyHIVDisp_Diag','label' => 'Полный клинический диагноз','rules' => 'max_length[50]','type' => 'string'),
				array('field' => 'EvnNotifyHIVDisp_CountCD4','label' => 'Количество CD4 Т-лимфоцитов (мм)','rules' => '','type' => 'int'),
				array('field' => 'EvnNotifyHIVDisp_PartCD4','label' => 'Процент содержания CD4 Т-лимфоцитов','rules' => '','type' => 'float'),

				array('field' => 'MorbusHIVLab_BlotDT','label' => 'Дата постановки реакции иммуноблота','rules' => '','type' => 'date'),
				array('field' => 'MorbusHIVLab_TestSystem','label' => 'Тип тест-системы','rules' => 'max_length[64]','type' => 'string'),
				array('field' => 'MorbusHIVLab_BlotNum','label' => 'N серии','rules' => 'max_length[64]','type' => 'string'),
				array('field' => 'MorbusHIVLab_BlotResult','label' => 'Выявленные белки и гликопротеиды','rules' => 'max_length[100]','type' => 'string'),
				array('field' => 'Lpuifa_id','label' => 'Учреждение, первично выявившее положительный результат в ИФА','rules' => '','type' => 'id'),
				array('field' => 'MorbusHIVLab_IFADT','label' => 'Дата ИФА','rules' => '','type' => 'date'),
				array('field' => 'MorbusHIVLab_IFAResult','label' => 'Результат ИФА','rules' => 'max_length[30]','type' => 'string'),
				array('field' => 'MorbusHIVLab_PCRDT','label' => 'Дата ПЦР','rules' => '','type' => 'date'),
				array('field' => 'MorbusHIVLab_PCRResult','label' => 'Результат ПЦР','rules' => 'max_length[30]','type' => 'string'),

				array('field' => 'MorbusHIV_NumImmun','label' => '№ иммуноблота','rules' => '', 'type' => 'int'),
			)
    );

	/**
	 * Method description
	 */
	function __construct ()
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->model('EvnNotifyHIVDisp_model', 'dbmodel');
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
		
		$response = $this->dbmodel->save($data);
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