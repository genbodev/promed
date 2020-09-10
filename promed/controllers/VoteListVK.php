<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * VoteListVK - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @access			public
 *
 * @property VoteExpertVK_model dbmodel
 */

class VoteListVK extends swController {
	protected  $inputRules = [
		'save' => [
			['field' => 'EvnPrescrVK_id', 'label' => 'Идентификатор направления на ВК', 'rules' => 'required', 'type' => 'id'],
			['field' => 'GridData', 'label' => '', 'rules' => '','type' => 'json_array'],
		],
		'loadList' => [
			['field' => 'EvnPrescrVK_id', 'label' => 'Идентификатор направления на ВК', 'rules' => 'required', 'type' => 'id'],
		],
		'delete' => [
			['field' => 'EvnPrescrVK_id', 'label' => 'Идентификатор направления на ВК', 'rules' => 'required', 'type' => 'id'],
		],
		'getDecision' => [
			['field' => 'VoteExpertVK_id', 'label' => 'Идентификатор эксперта', 'rules' => 'required', 'type' => 'id'],
		],
		'saveDecision' => [
			['field' => 'VoteExpertVK_id', 'label' => 'Идентификатор эксперта', 'rules' => 'required', 'type' => 'id'],
			['field' => 'VoteExpertVK_isInternalRequest', 'label' => 'Запросить очную экспертизу', 'rules' => '', 'type' => 'id'],
			['field' => 'VoteExpertVK_isApproved', 'label' => 'Решение эксперта', 'rules' => '', 'type' => 'id'],
			['field' => 'VoteExpertVK_Descr', 'label' => 'Комментарий эксперта', 'rules' => 'trim', 'type' => 'string'],
		],
	];

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('VoteListVK_model', 'dbmodel');
	}

	/**
	 * Сохранение
	 */
	function save() {
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * список
	 */
	function loadList()	{
		$data = $this->ProcessInputData('loadList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * решение
	 */
	function getDecision()	{
		$data = $this->ProcessInputData('getDecision');
		if ($data === false) { return false; }

		$response = $this->dbmodel->getDecision($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение
	 */
	function saveDecision() {
		$data = $this->ProcessInputData('saveDecision');
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveDecision($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Удаление
	 */
	function delete() {
		$data = $this->ProcessInputData('delete');
		if ($data === false) { return false; }

		$response = $this->dbmodel->delete($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении')->ReturnData();
		return true;
	}
}