<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnObserv - контроллер для работы с наблюдениями за пациентами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			28.09.2016
 *
 * @property EvnObserv_model dbmodel
 */

class EvnObserv extends swController {
	protected $inputRules = array(
		'loadEvnObservGrid' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_id',
				'label' => 'Идентификатор специфики новорожденного',
				'rules' => '',
				'type' => 'id'
			),
		),
		'loadEvnObservForm' => array(
			array(
				'field' => 'EvnObserv_id',
				'label' => 'Идентификатор наблюдения',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'saveEvnObserv' => array(
			array(
				'field' => 'EvnObserv_id',
				'label' => 'Идентификатор наблюдения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnObserv_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор периодики',
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
				'field' => 'EvnObserv_setDate',
				'label' => 'Дата наблюдения',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'ObservTimeType_id',
				'label' => 'Идентификатор времени наблюдения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_id',
				'label' => 'Идентификатор специфики новорожденного',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnObservDataList',
				'label' => 'Массив данных наблюдения',
				'rules' => 'required',
				'type' => 'string'
			),
		),
		'deleteEvnObserv' => array(
			array(
				'field' => 'EvnObserv_id',
				'label' => 'Идентификатор наблюдения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_id',
				'label' => 'Идентификатор специфики новорожденного',
				'rules' => '',
				'type' => 'id'
			),
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('EvnObserv_model', 'dbmodel');
	}

	/**
	 * Получение списка наблюдений
	 */
	function loadEvnObservGrid() {
		$data = $this->ProcessInputData('loadEvnObservGrid');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnObservGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение данных наблюдения для редактирования
	 */
	function loadEvnObservForm() {
		$data = $this->ProcessInputData('loadEvnObservForm');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnObservForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение наблюдения
	 */
	function saveEvnObserv() {
		$data = $this->ProcessInputData('saveEvnObserv');
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveEvnObserv($data);

		if (!empty($data['PersonNewBorn_id'])) {
			$this->load->model('PersonNewBorn_model');
			$this->PersonNewBorn_model->updatePersonNewbornIsHighRisk($data);
		}

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Удаление данных наблюдения
	 */
	function deleteEvnObserv() {
		$data = $this->ProcessInputData('deleteEvnObserv');
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteEvnObserv($data);

		if (!empty($data['PersonNewBorn_id'])) {
			$this->load->model('PersonNewBorn_model');
			$this->PersonNewBorn_model->updatePersonNewbornIsHighRisk($data);
		}

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
}