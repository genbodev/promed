<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для журнала работы заданий
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Salakhov R. 
 * @version      12.2018
 *
 * @property ReceptTask_model $dbmodel
 */

class ReceptTask extends swController {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model("ReceptTask_model", "ReceptTask_model");

		$this->inputRules = array(
			'loadList' => array(
				array('field' => 'begDate', 'label' => 'Дата начала', 'rules' => '', 'type' => 'date'),
				array('field' => 'endDate', 'label' => 'Дата окончания', 'rules' => '', 'type' => 'date')
			),
			'loadReceptTaskLogList' => array(
				array('field' => 'ReceptTask_id', 'label' => 'Идентификатор записи журнала', 'rules' => 'required', 'type' => 'id')
			)
		);
	}

	/**
	 * Загрузка списка
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', false);
		if ($data){
			$response = $this->ReceptTask_model->loadList($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка лога
	 */
	function loadReceptTaskLogList() {
		$data = $this->ProcessInputData('loadReceptTaskLogList', false);
		if ($data){
			$response = $this->ReceptTask_model->loadReceptTaskLogList($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}