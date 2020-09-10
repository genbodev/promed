<?php	defined('BASEPATH') or die ('No direct script access allowed');

class NoticeDiagGroup extends swController {
	public $inputRules = array(
		'loadNoticeDiagGroupForm' => array(
			array(
				'field' => 'NoticeDiagGroup_id',
				'label' => 'Идентификатор группы диагнозов',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadNoticeDiagGroupGrid' => array(),
		'saveNoticeDiagGroup' => array(
			array(
				'field' => 'NoticeDiagGroup_id',
				'label' => 'Идентификатор наименования группы диагнозов',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'NoticeDiagGroup_Name',
				'label' => 'Наименования группы диагнозов',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'NoticeDiagGroupData',
				'label' => 'Данные для групп диагнозов',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'allowIntersection',
				'label' => 'Допустить пересечение групп',
				'rules' => '',
				'type' => 'int'
			)
		),
		'deleteNoticeDiagGroup' => array(
			array(
				'field' => 'NoticeDiagGroup_id',
				'label' => 'Идентификатор наименования группы диагнозов',
				'rules' => '',
				'type' => 'id'
			)
		),
		'upcomingDispNotifyTask' => array()
	);

	/**
	 * constructor.
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('NoticeDiagGroup_model', 'dbmodel');
	}


	/**
	 *  Загрузка данных формы
	 */
	function loadNoticeDiagGroupForm() {
		$data = $this->ProcessInputData('loadNoticeDiagGroupForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadNoticeDiagGroupForm($data);
		$this->ProcessModelList($response)->ReturnData();

		return true;
	}

	/**
	 *  Сохранение группы диагнозов
	 */
	function saveNoticeDiagGroup() {
		$data = $this->ProcessInputData('saveNoticeDiagGroup', true);
		//echo '<pre>',print_r($data),'</pre>'; die();
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveNoticeDiagGroup($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 *  * Получение списка групп
	 */
	function loadNoticeDiagGroupGrid() {
		$data = $this->ProcessInputData('loadNoticeDiagGroupGrid', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadNoticeDiagGroupGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Задание отправляет уведомления тем
	 * кому необходимо записаться на плановый прием
	 */
	function upcomingDispNotifyTask() {
		$data = $this->ProcessInputData('upcomingDispNotifyTask');
		if ($data === false) { return false; }

		$this->dbmodel->upcomingDispNotifyTask($data);
		return true;
	}
}
