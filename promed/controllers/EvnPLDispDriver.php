<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * EvnPLDispDriver - контроллер для управления талонами диспансеризации водителей
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package			Polka
 * @access			public
 * @copyright		Copyright (c) 2009 - 2016 Swan Ltd.
 *
 * @property EvnPLDispDriver_model $dbmodel
 */

class EvnPLDispDriver extends swController
{
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model("EvnPLDispDriver_model", "dbmodel");
		$this->inputRules = [
			"deleteEvnPLDispDriver" => [
				["field" => "EvnPLDispDriver_id", "label" => "Идентификатор талона освидетельствования", "rules" => "trim|required", "type" => "id"]
			],
			"saveDopDispInfoConsent" => [
				["field" => "EvnPLDispDriver_id", "label" => "Идентификатор талона по доп. диспансеризации", "rules" => "", "type" => "id"],
				["field" => "DispClass_id", "label" => "Идентификатор этапа", "rules" => "required", "type" => "id"],
				["field" => "PayType_id", "label" => "Вид оплаты", "rules" => "required", "type" => "id"],
				["field" => "EvnPLDispDriver_fid", "label" => "Идентификатор карты предыдущего этапа", "rules" => "", "type" => "id"],
				["field" => "Lpu_mid", "label" => "МО мобильной бригады", "rules" => "", "type" => "id"],
				["field" => "EvnPLDispDriver_IsMobile", "label" => "Обслужен мобильной бригадой", "rules" => "", "type" => "checkbox"],
				["field" => "EvnPLDispDriver_consDate", "label" => "Дата подписания согласия/отказа", "rules" => "required", "type" => "date"],
				["field" => "PersonEvn_id", "label" => "Идентификатор человека в событии", "rules" => "required", "type" => "id"],
				["field" => "Person_id", "label" => "Идентификатор человека", "rules" => "required", "type" => "id"],
				["field" => "Server_id", "label" => "Server_id", "rules" => "", "type" => "int"],
				["field" => "DopDispInfoConsentData", "label" => "Данные грида по информир. добр. согласию", "rules" => "", "type" => "string"]
			],
			"updateDopDispInfoConsent" => [
				["field" => "EvnPLDispDriver_id", "label" => "Идентификатор талона", "rules" => "required", "type" => "id"],
				["field" => "DopDispInfoConsent_id", "label" => "Идентификатор согласия", "rules" => "required", "type" => "int"],
				["field" => "DopDispInfoConsent_IsAgree", "label" => "Согласен/нет", "rules" => "required", "type" => "id"],
				["field" => "DopDispInfoConsent_IsEarlier", "label" => "Пройдено ранее", "rules" => "required", "type" => "id"]
			],
			"loadEvnPLDispDriverEditForm" => [
				["field" => "EvnPLDispDriver_id", "label" => "Идентификатор талона", "rules" => "required", "type" => "id"]
			],
			"loadDopDispInfoConsent" => [
				["field" => "EvnPLDispDriver_id", "label" => "Идентификатор талона", "rules" => "", "type" => "id"],
				["field" => "Person_id", "label" => "Идентификатор человека", "rules" => "", "type" => "id"],
				["field" => "DispClass_id", "label" => "Идентификатор этапа", "rules" => "", "type" => "id"],
				["field" => "EvnPLDispDriver_consDate", "label" => "Дата согласия/отказа", "rules" => "required", "type" => "date"]
			],
			"loadEvnUslugaDispDopGrid" => [
				["field" => "EvnPLDispDriver_id", "label" => "Идентификатор талона", "rules" => "required", "type" => "id"]
			],
			"getInfectData" => [
				["field" => "EvnPLDispDriver_id", "label" => "Идентификатор талона", "rules" => "required", "type" => "id"]
			],
			"getUslugaResult" => [
				["field" => "DopDispInfoConsent_id", "label" => "Идентификатор согласия", "rules" => "required", "type" => "id"]
			],
			"saveEvnPLDispDriver" => [
				["field" => "EvnPLDispDriver_id", "label" => "Идентификатор карты", "rules" => "", "type" => "id"],
				["field" => "EvnPLDispDriver_Num", "label" => "Номер карты", "rules" => "", "type" => "int"],
				["field" => "EvnDirection_id", "label" => "Идентификатор направления", "rules" => "", "type" => "id"],
				["field" => "EvnPLDispDriver_IndexRep", "label" => "Признак повторной подачи", "rules" => "", "type" => "int"],
				["field" => "EvnPLDispDriver_IndexRepInReg", "label" => "Признак повторной подачи в реестре", "rules" => "", "type" => "int"],
				["field" => "EvnPLDispDriver_IndexRep", "label" => "Признак повторной подачи", "rules" => "", "type" => "int"],
				["field" => "EvnPLDispDriver_IndexRepInReg", "label" => "Признак повторной подачи в реестре", "rules" => "", "type" => "int"],
				["field" => "PayType_id", "label" => "Вид оплаты", "rules" => "", "type" => "id"],
				["field" => "DispClass_id", "label" => "Идентификатор этапа", "rules" => "", "type" => "id"],
				["field" => "Person_id", "label" => "Идентификатор пациента", "rules" => "", "type" => "id"],
				["field" => "PersonEvn_id", "label" => "Идентификатор пациента", "rules" => "", "type" => "id"],
				["field" => "Server_id", "label" => "Server_id", "rules" => "", "type" => "int"],
				["field" => "EvnPLDispDriver_consDate", "label" => "Дата подписания согласия", "rules" => "required", "type" => "date"],
				["field" => "EvnPLDispDriver_IsFinish", "label" => "Медицинское обследование закончено", "rules" => "", "type" => "id"],
				["field" => "ResultDispDriver_id", "label" => "Результат", "rules" => "", "type" => "id"],
				["field" => "EvnPLDispDriver_MedSer", "label" => "Заключение - серия", "rules" => "", "type" => "int"],
				["field" => "EvnPLDispDriver_MedNum", "label" => "Заключение - номер", "rules" => "", "type" => "int"],
				["field" => "EvnPLDispDriver_MedDate", "label" => "Заключение - дата", "rules" => "", "type" => "date"],
				["field" => "DriverCategory", "label" => "Категории ТС, на управлении которыми предоставляется право", "rules" => "", "type" => "string"],
				["field" => "DriverMedicalClose", "label" => "Медицинские ограничения к управлению ТС", "rules" => "", "type" => "string"],
				["field" => "DriverMedicalIndication", "label" => "Медицинские показания к управлению ТС", "rules" => "", "type" => "string"],
			],
			"getRegistryInfo" => [
				["field" => "Person_id", "label" => "Идентификатор человека", "rules" => "required", "type" => "id"]
			],
			"saveCB" => [
				["field" => "EvnPLDispDriver_id", "label" => "Идентификатор карты", "rules" => "required", "type" => "id"],
				["field" => "type", "label" => "Тип списка", "rules" => "required", "type" => "string"],
				["field" => "data", "label" => "Данные флагов", "rules" => "required", "type" => "string"],
			],
			"checkAllDopDispInfoConsent" => [
				["field" => "EvnPLDispDriver_id", "label" => "Идентификатор карты", "rules" => "required", "type" => "id"],
				["field" => "DopDispInfoConsent_IsAgree", "label" => "Согласие", "rules" => "required", "type" => "id"],
				["field" => "DopDispInfoConsent_IsEarlier", "label" => "Пройдено ранее", "rules" => "", "type" => "id"],
			]
		];
	}

	/**
	 * Удаление талона освидетельствования
	 * @return bool
	 */
	function deleteEvnPLDispDriver()
	{
		$data = $this->ProcessInputData("deleteEvnPLDispDriver", true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->deleteEvnPLDispDriver($data);
		$this->ProcessModelSave($response, true, "При удалении талона освидетельствования возникли ошибки")->ReturnData();
		return true;
	}

	/**
	 * Сохранение данных по информир. добр. согласию
	 * @return bool
	 * @throws Exception
	 */
	function saveDopDispInfoConsent()
	{
		$data = $this->ProcessInputData("saveDopDispInfoConsent", true);
		if ($data === false) {
			return false;
		}
		$this->load->library("swFilterResponse");
		$response = $this->dbmodel->saveDopDispInfoConsent($data);
		$this->ProcessModelSave($response, true, "При сохранении возникли ошибки")->ReturnData();
		return true;
	}

	/**
	 * Обновление данных по информир. добр. согласию (штучно)
	 * @return bool
	 */
	function updateDopDispInfoConsent()
	{
		$data = $this->ProcessInputData("updateDopDispInfoConsent", true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->updateDopDispInfoConsent($data);
		$this->ProcessModelSave($response, true, "При сохранении возникли ошибки")->ReturnData();
		return true;
	}

	/**
	 * Получение данных для формы редактирования талона
	 * Входящие данные: $_POST["EvnPLDispDriver_id"]
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона
	 * @return bool
	 */
	function loadEvnPLDispDriverEditForm()
	{
		$data = $this->ProcessInputData("loadEvnPLDispDriverEditForm", true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadEvnPLDispDriverEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение грида "информированное добровольное согласие"
	 * Входящие данные: EvnPLDispDriver_id
	 * @return bool
	 */
	function loadDopDispInfoConsent()
	{
		$data = $this->ProcessInputData("loadDopDispInfoConsent", true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadDopDispInfoConsent($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение грида "информированное добровольное согласие"
	 * Входящие данные: EvnPLDispDriver_id
	 * @return bool
	 */
	function loadEvnUslugaDispDopGrid()
	{
		$data = $this->ProcessInputData("loadEvnUslugaDispDopGrid", true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->loadEvnUslugaDispDopGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение карты
	 * @return bool
	 */
	function saveEvnPLDispDriver()
	{
		$data = $this->ProcessInputData("saveEvnPLDispDriver", true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->saveEvnPLDispDriver($data);
		$this->ProcessModelSave($response, true, "При сохранении возникли ошибки")->ReturnData();
		return true;
	}

	/**
	 * Результаты услуги (если есть)
	 * @return bool
	 */
	function getUslugaResult()
	{
		$data = $this->ProcessInputData("getUslugaResult", true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->getUslugaResult($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * данные из регистров
	 * @return bool
	 */
	function getRegistryInfo()
	{
		$data = $this->ProcessInputData("getRegistryInfo", true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->getRegistryInfo($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * сохранение флагов (из эмк)
	 * @return bool
	 */
	function saveCB()
	{
		$data = $this->ProcessInputData("saveCB", true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->saveCBemk($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * выбрать всё (согласие)
	 */
	function checkAllDopDispInfoConsent()
	{
		$data = $this->ProcessInputData("checkAllDopDispInfoConsent", true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->checkAllDopDispInfoConsent($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();
		return true;
	}
}