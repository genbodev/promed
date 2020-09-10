<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnNotifyNarco - контроллер формы "Извещение об орфанном заболевании"
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
 * @property EvnNotifyNarco_model $dbmodel
 */

class EvnNotifyNarco extends swController 
{

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(
		
			'del' => array(
				array(
					'field' => 'EvnNotifyNarco_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		
			'load' => array(
				array(
					'field' => 'EvnNotifyNarco_id',
					'label' => 'Идентификатор',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'EvnNotifyNarco_pid',
					'label' => 'Идентификатор',
					'rules' => 'trim',
					'type' => 'id'
				),
			),
		
			'save' => array(
				array(
					'field' => 'EvnNotifyNarco_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnNotifyNarco_pid',
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
					'field' => 'Post_id',
					'label' => 'Post_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Diag_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnNotifyNarco_JobPlace',
					'label' => 'EvnNotifyNarco_JobPlace',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Diag_sid',
					'label' => 'Diag_sid',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'NarcoReceiveType_id',
					'label' => 'NarcoReceiveType_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnNotifyNarco_NarcoName',
					'label' => 'EvnNotifyNarco_NarcoName',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'EvnNotifyNarco_NarcoDate',
					'label' => 'EvnNotifyNarco_NarcoDate',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'NarcoUseType_id',
					'label' => 'NarcoUseType_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'NarcoTreatInitiate_id',
					'label' => 'NarcoTreatInitiate_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnNotifyNarco_setDT',
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
		$this->load->model('EvnNotifyNarco_model', 'dbmodel');
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