<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnNotifyHepatitis - контроллер извещения извещение о больном вирусным гепатитом
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Alexander Permyakov 
 * @version      02.2013
 *
 * @property EvnNotifyHepatitis_model $dbmodel
 */

class EvnNotifyHepatitis extends swController 
{

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(
		'save' => array(
			array(
				'field' => 'EvnNotifyHepatitis_id',
				'label' => 'Идентификатор',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnNotifyHepatitis_pid',
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
				'field' => 'EvnNotifyHepatitis_setDT',
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
		)
    );

	/**
	 * Method description
	 */
	function __construct()
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->model('EvnNotifyHepatitis_model', 'dbmodel');
	}

	/**
	 * Сохранение
	 */
	function save()
	{
		$data = $this->ProcessInputData('save', true);
		if ($data === false) { return false; }
		$data['scenario'] = swModel::SCENARIO_AUTO_CREATE;
		$response = $this->dbmodel->doSave($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
}