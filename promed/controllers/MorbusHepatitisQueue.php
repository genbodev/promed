<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusHepatitisQueue - контроллер для выполнения операций с очередью по гепатиту
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

class MorbusHepatitisQueue extends swController 
{

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(
		
			'load' => array(
				array(
					'field' => 'MorbusHepatitisQueue_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
		
			'save' => array(
				array(
					'field' => 'MorbusHepatitisQueue_id',
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
					'field' => 'HepatitisQueueType_id',
					'label' => 'Тип очереди',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MorbusHepatitisQueue_Num',
					'label' => 'Номер в очереди',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MorbusHepatitisQueue_IsCure',
					'label' => 'Лечение проведено',
					'rules' => '',
					'type' => 'id'
				)
			),
		
			'getQueueNumber' => array(
				array(
					'field' => 'HepatitisQueueType_id',
					'label' => 'Тип очереди',
					'rules' => '',
					'type' => 'id'
				)
			),
    );

	/**
	 * MorbusHepatitisQueue constructor.
	 */
	function __construct() 
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->model('MorbusHepatitisQueue_model', 'dbmodel');
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
	
	/**
	 * Запрос номера в очереди
	 */
	function getQueueNumber()
	{
		$data = $this->ProcessInputData('getQueueNumber', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getQueueNumber($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
		
	}
	
}
?>