<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnPLDispScreenOnko - контроллер для управления талонами скрининговых исследований по онкологии
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @access			public
* @copyright		Copyright (c) 2013 Swan Ltd.
* @author			Swan
* @version			07.06.2019
* @property EvnPLDispScreenOnko_model $dbmodel
*/

class EvnPLDispScreenOnko extends swController
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnPLDispScreenOnko_model', 'dbmodel');
		$this->inputRules = $this->dbmodel->getInputRulesAdv();
	}
	
	/**
	 * Добавить карту первич.онкоскрининга
	 */
	function addEvnPLDispScreenOnko() {
		$data = $this->ProcessInputData('addEvnPLDispScreenOnko', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->addEvnPLDispScreenOnko($data);
		
		//~ var_dump($response);exit();
		//~ $this->ProcessModelList($response, true, true)->ReturnData();
		
		$this->ProcessModelSave($response, true)->ReturnData();
	}
	
	/**
	 * Загрузка списка согласий
	 */
	function loadDopDispInfoConsent() {
		$data = $this->ProcessInputData('loadDopDispInfoConsent', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadDopDispInfoConsent($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Сохранение согласий
	 */
	function saveDopDispInfoConsent() {
		$data = $this->ProcessInputData('saveDopDispInfoConsent', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveDopDispInfoConsent($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();

		return true;
	}
	
	/**
	 * Загрузка основных данных
	 */
	function loadEvnPLDispScreenOnko() {
		$data = $this->ProcessInputData('loadEvnPLDispScreenOnko', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadEvnPLDispScreenOnko($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * удаление скрининга
	 */
	function deleteEvnPLDispScreenOnko() {
		$data = $this->ProcessInputData('deleteEvnPLDispScreenOnko', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteEvnPLDispScreenOnko($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	
	/**
	 * Получить поля для раздела Протокол осмотра
	 */
	function getProtokolFieldList() {
		$data = $this->ProcessInputData('getProtokolFieldList', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getProtokolFieldList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Сохранение раздела Протокола осмотра
	 */
	function saveFormalizedInspection() {
		$data = $this->ProcessInputData('saveFormalizedInspection', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->saveFormalizedInspection($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Сохранение раздела Результат
	 */
	function saveResult() {
		$data = $this->ProcessInputData('saveResult', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->saveResult($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Загрузка списка назначений в талоне скрининговых исследований
	 */
	function loadEvnPLDispScreenPrescrList() {
		$data = $this->ProcessInputData('loadEvnPLDispScreenPrescrList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnPLDispScreenPrescrList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Проверка наличия ПОС
	 */
	function checkEvnPLDispScreenOnkoExists() {
		$data = $this->ProcessInputData('checkEvnPLDispScreenOnkoExists', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkEvnPLDispScreenOnkoExists($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}