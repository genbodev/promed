<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnNotifyHIVBorn - контроллер формы Извещение о новорожденном, рожденном ВИЧ-инфицированной матерью
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
 * @property EvnNotifyHIVBorn_model $dbmodel
 */

class EvnNotifyHIVBorn extends swController 
{

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(
		
			'del' => array(
				array(
					'field' => 'EvnNotifyHIVBorn_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		
			'load' => array(
				array(
					'field' => 'EvnNotifyHIVBorn_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		
			'save' => array(
				array(
					'field' => 'EvnNotifyHIVBorn_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnNotifyHIVBorn_pid',
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
					'label' => 'Ребенок',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnNotifyHIVBorn_setDT',
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
				
				array('field' => 'MorbusHIVChem_data','label' => 'Проведение химиопрофилактики ВИЧ-инфекции ребенку','rules' => '','type' => 'string'),//JsonStr
				array('field' => 'MorbusHIVChemPreg_data','label' => 'Проведение перинатальной профилактики ВИЧ','rules' => '','type' => 'string'),//JsonStr
				array('field' => 'Person_mid','label' => 'Мать','rules' => 'required','type' => 'id'),
				array('field' => 'EvnNotifyHIVBorn_IsRefuse','label' => 'Отказной ребенок','rules' => '','type' => 'id'),
				array('field' => 'Lpu_rid','label' => 'ЛПУ рождения','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyHIVBorn_IsBreastFeed','label' => 'Грудное вскармливание ребенка','rules' => '','type' => 'id'),
				array('field' => 'Lpu_fid','label' => 'ЛПУ первого обращения по поводу беременности','rules' => '','type' => 'id'),
				array('field' => 'HIVRegPregnancyType_id','label' => 'Срок постановки на учет в ЖК','rules' => '','type' => 'id'),
				array('field' => 'HIVPregPathTransType_id','label' => 'Путь ВИЧ-инфицирования','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyHIVBorn_IsCes','label' => 'Кесарево сечение','rules' => '','type' => 'id'),
				array('field' => 'EvnNotifyHIVBorn_ChildMass','label' => 'Масса ребенка при рождении','rules' => '','type' => 'int'),
				array('field' => 'EvnNotifyHIVBorn_ChildHeight','label' => 'Рост ребенка при рождении','rules' => '','type' => 'int'),
				array('field' => 'EvnNotifyHIVBorn_Srok','label' => 'Родоразрешение в срок беременности (в неделях)','rules' => '','type' => 'int'),
				array('field' => 'EvnNotifyHIVBorn_Diag','label' => 'Клинический диагноз ребенка','rules' => 'max_length[100]','type' => 'string'),
				array('field' => 'EvnNotifyHIVBorn_FirstPregDT','label' => 'Дата первого обращения по поводу беременности','rules' => '','type' => 'date'),
				array('field' => 'EvnNotifyHIVBorn_HIVDT','label' => 'Дата установления ВИЧ-инфицирования','rules' => '','type' => 'date'),
			)
    );


	/**
	 * Method description
	 */
	function __construct ()
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->model('EvnNotifyHIVBorn_model', 'dbmodel');
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