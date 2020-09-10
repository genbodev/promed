<?php
defined("BASEPATH") or die ("No direct script access allowed");
require_once("EvnLeaveAbstract_model.php");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 *
 * EvnLeave_model - Модель "Выписка из стационара"
 *
 * @package      Hospital
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       swan developers
 * @version      09.2014
 *
 * @property-read int $IsAmbul Направлен на амбулаторное лечение EvnLeave_IsAmbul
 */
class EvnLeave_model extends EvnLeaveAbstract_model
{
	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]["alias"] = "EvnLeave_id";
		$arr["pid"]["alias"] = "EvnLeave_pid";
		$arr["setdate"]["alias"] = "EvnLeave_setDate";
		$arr["settime"]["alias"] = "EvnLeave_setTime";
		$arr["disdt"]["alias"] = "EvnLeave_disDT";
		$arr["diddt"]["alias"] = "EvnLeave_didDT";
		$arr["ukl"]["alias"] = "EvnLeave_UKL";
		$arr["isambul"] = [
			"properties" => [self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM],
			"alias" => "EvnLeave_IsAmbul",
			"label" => "Направлен на амбулаторное лечение",
			"save" => "trim",
			"type" => "id"
		];
		return $arr;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 39;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return "EvnLeave";
	}

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();
		if (
			empty($this->IsAmbul) &&
			in_array($this->parent->leaveTypeSysNick, ["leave", "ksleave", "dsleave"]) &&
			in_array($this->scenario, [self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE])
		) {
			throw new Exception("Не указан признак направления на амбулаторное долечивание");
		}
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 * @param array $data
	 * @throws Exception
	 */
	protected function _beforeSave($data = [])
	{
		parent::_beforeSave($data);
		if (in_array($this->parent->leaveTypeSysNick, ["ksinicpac", "ksiniclpu", "ksprerv"])) {
			// https://redmine.swan.perm.ru/issues/30661
			// 107. Лечение прервано по инициативе пациента
			// 108. Лечение прервано по инициативе ЛПУ
			// 110. Самовольно прерванное лечение
			$this->setAttribute("isambul", null);
		}
	}

	/**
	 * Получение данных для копии
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function getEvnLeaveCopyData($data)
	{
		return $this->doLoadCopyData($data);
	}

	/**
	 * Получение данных для копии
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function doLoadCopyData($data)
	{
		$response = parent::doLoadCopyData($data);
		$response["EvnLeave_IsAmbul"] = $this->IsAmbul;
		return $response;
	}

	/**
	 * Получение данных для формы
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function loadEvnLeaveEditForm($data)
	{
		return $this->doLoadEditForm($data);
	}

	/**
	 * Получение данных для формы
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function doLoadEditForm($data)
	{
		$response = parent::doLoadEditForm($data);
		$response[0]["EvnLeave_IsAmbul"] = $this->IsAmbul;
		return $response;
	}

	/**
	 * Получение идентификатора случая исхода госпитализации
	 * @param $data
	 * @return array|bool
	 */
	function getEvnLeaveBaseId($data)
	{
		$id = $this->getIdByRid($data["EvnPS_id"]);
		return ($id == 0) ? false : [[
			"EvnPS_id" => $data["EvnPS_id"],
			"EvnLeaveBase_id" => $id,
		]];
	}

	/**
	 * Удаление
	 * @param $data
	 * @return array
	 */
	function deleteEvnLeave($data)
	{
		return [$this->doDelete($data)];
	}

	/**
	 * Сохранение
	 * @param $data
	 * @return array
	 */
	function saveEvnLeave($data)
	{
		if (empty($data["scenario"])) {
			$data["scenario"] = self::SCENARIO_DO_SAVE;
		}
		return [$this->doSave($data)];
	}
}