<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnNotifyCrazy - контроллер формы "Извещение об орфанном заболевании"
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
 * @property EvnNotifyCrazy_model $dbmodel
 */

class EvnNotifyCrazy extends swController 
{

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(
		
			'del' => array(
				array(
					'field' => 'EvnNotifyCrazy_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		
			'load' => array(
				array(
					'field' => 'EvnNotifyCrazy_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		
			'save' => array(
				array(
					'field' => 'EvnNotifyCrazy_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnNotifyCrazy_pid',
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
					'field' => 'EvnNotifyCrazy_setDT',
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
		$this->load->model('EvnNotifyCrazy_model', 'dbmodel');
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