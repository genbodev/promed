<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusHepatitisVaccination - контроллер для выполнения операций с вакцинацией по гепатиту
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

class MorbusHepatitisVaccination extends swController 
{

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(
		
			'load' => array(
				array(
					'field' => 'MorbusHepatitisVaccination_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		
			'save' => array(
				array(
					'field' => 'MorbusHepatitisVaccination_id',
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
					'field' => 'MorbusHepatitisVaccination_setDT',
					'label' => 'Дата',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'Drug_id',
					'label' => 'Препарат',
					'rules' => 'required',
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
	 * MorbusHepatitisVaccination constructor.
	 */
	function __construct() 
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->model('MorbusHepatitisVaccination_model', 'dbmodel');
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