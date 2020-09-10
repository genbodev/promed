<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* ServiceRPN - контроллер для работы с порталом РПН
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			ServiceRPN
* @access			public
* @copyright		Copyright (c) 2015 Swan Ltd.
* @author			Markoff Andrew
* @version			07.2015
*/
require_once(APPPATH.'controllers/ServiceRPN.php');

class Kz_ServiceRPN extends ServiceRPN
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->inputRules = array(
			'getPersonCardList' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_SurName',
					'label' => 'Фамилия',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Person_FirName',
					'label' => 'Имя',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Person_SecName',
					'label' => 'Отчество',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_BirthDay',
					'label' => 'Дата рождения',
					'rules' => 'required',
					'type' => 'date'
				)
			),
			'getLpuRegionList' => array(
			),
			'getSprList' => array(
				array(
					'field' => 'spr',
					'label' => 'Наименование справочника',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'page',
					'label' => 'page',
					'rules' => '',
					'default' => 1,
					'type' => 'int'
				),
				array(
					'field' => 'pagesize',
					'label' => 'pagesize',
					'rules' => '',
					'default' => 100,
					'type' => 'int'
				),
			),
			'getOblList' => array(
			),
			'getMOList' => array(
				array(
					'field' => 'level',
					'label' => 'Уровень',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'startImportPersonList' => array(
			),
			'stopImportPersonList' => array(
			),
			'checkImportPersonListStatus' => array(
			),
			'resetImportPersonListParams' => array(
				array(
					'field' => 'paramList',
					'label' => 'Список параметров',
					'rules' => '',
					'type' => 'string'
				)
			),
			'setImportPersonListParams' => array(
				array(
					'field' => 'paramValueList',
					'label' => 'Список параметров',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'importGetTerrServiceList' => array(
			),
			'importGetAttachmentList' => array(
			),
			'sendBirthSvidToRPN' => array(
				array(
					'field' => 'BirthSvid_id',
					'label' => 'Идентификатор свидетельства о рождении',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getHistoryAttachByPeriod' => array(
			)
		);
	}

	/**
	 * Получение информации из РПН-сервиса по прикреплению
	 */
	function getPersonCardList() {
		$data = $this->ProcessInputData('getPersonCardList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getPersonCardList($data);
		$this->ProcessModelSave($response, true, 'Ошибка при обращении к РПН-сервису')->ReturnData();
	}
	
	/**
	 * Получение информации из РПН-сервиса по участкам МО
	 */
	function getLpuRegionList() {
		$data = $this->ProcessInputData('getLpuRegionList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getLpuRegionList($data);
		$this->ProcessModelSave($response, true, 'Ошибка при обращении к РПН-сервису')->ReturnData();
	}
	
	/**
	 * Получение справочника
	 * GET api/dict/{dict}/page/{page}/{pagesize} 
	 */
	function getSprList() {
		$data = $this->ProcessInputData('getSprList', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->getSprList($data);
	}
	/**
	 * Получение информации об областях
	 * GET api/addresses/obls 
	 */
	function getOblList() {
		$data = $this->ProcessInputData('getOblList', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->getOblList($data);
	}
	/**
	 * Получение информации об МО по области
	 * GET api/addresses/pmsps/{countryLevel} 
	 */
	function getMOList() {
		$data = $this->ProcessInputData('getMOList', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->getMOList($data);
	}

	/**
	 * Получение информации о пациентах МО и их активных прикреплениях
	 */
	function startImportPersonList() {
		$data = $this->ProcessInputData('startImportPersonList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->startImportPersonList($data);
		$this->ProcessModelSave($response, true, 'Ошибка при обращении к РПН-сервису')->ReturnData();
	}

	/**
	 * Отслеживает остановку получения информации о пациентах МО и их активных прикреплениях
	 */
	function stopImportPersonList() {
		$data = $this->ProcessInputData('stopImportPersonList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->stopImportPersonList($data);
		$this->ProcessModelSave($response, true, 'Ошибка при обращении к РПН-сервису')->ReturnData();
	}

	/**
	 * Сброс параметров получения информации о пациентах МО и их активных прикреплениях
	 */
	function resetImportPersonListParams() {
		$data = $this->ProcessInputData('resetImportPersonListParams', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->resetImportPersonListParams($data);
		$this->ProcessModelSave($response, true, 'Ошибка при обращении к РПН-сервису')->ReturnData();
	}

	/**
	 * Сброс параметров получения информации о пациентах МО и их активных прикреплениях
	 */
	function setImportPersonListParams() {
		$data = $this->ProcessInputData('setImportPersonListParams', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->setImportPersonListParams($data);
		$this->ProcessModelSave($response, true, 'Ошибка при обращении к РПН-сервису')->ReturnData();
	}

	/**
	 * Проверка состояния загрузки информации о пациентах МО и их активных прикреплениях
	 */
	function checkImportPersonListStatus() {
		$data = $this->ProcessInputData('checkImportPersonListStatus', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkImportPersonListStatus($data);
		$this->ProcessModelSave($response, true, 'Ошибка при обращении к РПН-сервису')->ReturnData();
		return true;
	}



	/**
	 * Получение прикреплений из РПН за период
	 */
	function importGetTerrServiceList() {
		$data = $this->ProcessInputData('importGetTerrServiceList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->importGetTerrServiceList($data);
		$this->ProcessModelSave($response, true, 'Ошибка при обращении к РПН-сервису')->ReturnData();
		return true;
	}

	/**
	 * Получение прикреплений из РПН за период
	 */
	function importGetAttachmentList() {
		$data = $this->ProcessInputData('importGetAttachmentList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->importGetAttachmentList($data);
		$this->ProcessModelSave($response, true, 'Ошибка при обращении к РПН-сервису')->ReturnData();
		return true;
	}

	/**
	 * Передача свидетельства о рождении в РПН
	 */
	function sendBirthSvidToRPN() {
		$data = $this->ProcessInputData('sendBirthSvidToRPN', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->sendBirthSvidToRPN($data);
		$this->ProcessModelSave($response, true, 'Ошибка при обращении к РПН-сервису')->ReturnData();
		return true;
	}

	/*function test() {
		$this->dbmodel->test();
	}*/
	
	/**
	 * Получение изменений истории прикрепления за период
	 */
	function getHistoryAttachByPeriod() {
		$data = $this->ProcessInputData('getHistoryAttachByPeriod', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getHistoryAttachByPeriod($data);
		$this->ProcessModelSave($response, true, 'Ошибка при обращении к РПН-сервису')->ReturnData();
	}
}
?>