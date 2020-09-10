<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * RepositoryObserv - наблюдения за пациентами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://rtmis.ru/
 *
 *
 * @package      Common
 * @region       All
 * @access       public
 * @copyright    Copyright (c) 2020 RT MIS Ltd.
 * @author       Stanislav Bykov
 * @version      03.04.2020
 */

class RepositoryObserv extends swController {
	public $inputRules = [
		'delete' => [
			[ 'field' => 'RepositoryObserv_id', 'label' => 'Иденнтификатор наблюдения', 'rules' => 'required', 'type' => 'id' ],
		],
		'getRepositoryObservDefaultData' => [
			[ 'field' => 'Person_id', 'label' => 'Иденнтификатор пациента', 'rules' => 'required', 'type' => 'id' ],
		],
		'getUseCase' => [
			[ 'field' => 'RepositoryObserv_id', 'label' => 'Иденнтификатор наблюдения', 'rules' => 'required', 'type' => 'id' ],
		],
		'load' => [
			[ 'field' => 'RepositoryObserv_id', 'label' => 'Иденнтификатор наблюдения', 'rules' => 'required', 'type' => 'id' ],
		],
		'loadList' => [
			[ 'field' => 'Evn_id', 'label' => 'Родительское событие', 'rules' => 'required', 'type' => 'id' ],
		],
		'loadQuarantineList' => [
			[ 'field' => 'PersonQuarantine_id', 'label' => 'Карантин', 'rules' => 'required', 'type' => 'int' ],
			[ 'field' => 'Evn_id', 'label' => 'Родительское событие', 'rules' => '', 'type' => 'int' ],
		],
		'loadSopDiagList' => [
			[ 'field' => 'Evn_id', 'label' => 'Родительское событие', 'rules' => 'required', 'type' => 'id' ],
		],
		'findByPerson' => [
			[ 'field' => 'Person_id', 'label' => 'Пациент', 'rules' => 'required', 'type' => 'id' ],
		],
	];
	
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('RepositoryObserv_model', 'dbmodel');
	}

	/**
	 * @return bool
	 */
	public function delete() {
		$data = $this->ProcessInputData('delete', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->delete($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	public function getRepositoryObservDefaultData() {
		$data = $this->ProcessInputData('getRepositoryObservDefaultData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getRepositoryObservDefaultData($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
		return true;
	}

	/**
	 * Получение типа открытия формы
	 * @return bool
	 */
	public function getUseCase() {
		$data = $this->ProcessInputData('getUseCase', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getUseCase($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	public function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	public function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelList($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	public function loadSopDiagList() {
		$data = $this->ProcessInputData('loadSopDiagList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadSopDiagList($data);
		$this->ProcessModelList($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	public function save() {
		$data = $this->ProcessInputData($this->dbmodel->getSaveRules(), true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->doSave($data);
		
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
		return true;
	}

	/**
	 * Загрузка наблюдений находящихся на карантине
	 */
	public function loadQuarantineList() {
		$data = $this->ProcessInputData('loadQuarantineList', true);
		if($data === false) { return false; };
		$response = $this->dbmodel->loadQuarantineList($data);
		$this->ProcessModelList($response, true,'Ошибка при выполнении запроса к базе данных')->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function findByPerson() {
		$data = $this->ProcessInputData('findByPerson', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->findByPerson($data);
		$this->ProcessModelList($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
		return true;
	}
}
