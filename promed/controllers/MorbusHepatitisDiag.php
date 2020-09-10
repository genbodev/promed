<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusHepatitisDiag - контроллер формы "Диагноз" (Специфика по вир.гепатиту)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Alexander Chebukin 
 * @version      07.2012
 */

class MorbusHepatitisDiag extends swController 
{

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(
		
			'load' => array(
				array(
					'field' => 'MorbusHepatitisDiag_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		
			'save' => array(
				array(
					'field' => 'MorbusHepatitisDiag_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MorbusHepatitis_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MorbusHepatitisDiag_setDT',
					'label' => 'Дата',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'HepatitisDiagType_id',
					'label' => 'Диагноз',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MorbusHepatitisDiag_ConfirmDT',
					'label' => 'Дата подтверждения',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'HepatitisDiagActiveType_id',
					'label' => 'Активность',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'HepatitisFibrosisType_id',
					'label' => 'Фиброз',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnSection_id',
					'label' => 'Идентификатор движения',
					'rules' => '',
					'type' => 'id'
				)
			)
    );

	/**
	 * MorbusHepatitisDiag constructor.
	 */
	function __construct() 
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->model('MorbusHepatitisDiag_model', 'dbmodel');
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
?>