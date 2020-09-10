<?php defined("BASEPATH") or die ("No direct script access allowed");
require_once("EvnLeaveAbstract_model.php");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 * 
 * EvnOtherStac_model - Модель "Выписка в стационар другого типа"
 *
 * @package      Hospital
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       swan developers
 * @version      09.2014
 *
 * @property-read int $LpuUnitType_oid Тип подразделения, куда производится выписка
 * @property-read int $LpuSection_oid Отделение, куда производится выписка
 *
 * @property CI_DB_driver $db
 */
class EvnOtherStac_model extends EvnLeaveAbstract_model
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]["alias"] = "EvnOtherStac_id";
		$arr["pid"]["alias"] = "EvnOtherStac_pid";
		$arr["setdate"]["alias"] = "EvnOtherStac_setDate";
		$arr["settime"]["alias"] = "EvnOtherStac_setTime";
		$arr["disdt"]["alias"] = "EvnOtherStac_disDT";
		$arr["diddt"]["alias"] = "EvnOtherStac_didDT";
		$arr["ukl"]["alias"] = "EvnOtherStac_UKL";
		$arr["lpuunittype_oid"] = [
			"properties" => [self::PROPERTY_IS_SP_PARAM],
			"alias" => "LpuUnitType_oid",
			"label" => "Тип подразделения, куда производится выписка",
			"save" => "trim",
			"type" => "id"
		];
		$arr["lpusection_oid"] = [
			"properties" => [self::PROPERTY_IS_SP_PARAM],
			"alias" => "LpuSection_oid",
			"label" => "Отделение, куда производится выписка",
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
		return 42;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return "EvnOtherStac";
	}

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
		if (empty($this->LpuUnitType_oid) && in_array($this->scenario, [self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE])) {
			throw new Exception("Не указан тип стационара", 400);
		}
		if (empty($this->LpuSection_oid) && in_array($this->scenario, [self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE])) {
			throw new Exception("Не указано отделение", 400);
		}
	}

	/**
	 * Удаление
	 * @param $data
	 * @return array
	 */
	function deleteEvnOtherStac($data)
	{
		return [$this->doDelete($data)];
	}

	/**
	 * Сохранение
	 * @param $data
	 * @return array
	 */
	function saveEvnOtherStac($data)
	{
		if (empty($data["scenario"])) {
			$data["scenario"] = self::SCENARIO_DO_SAVE;
		}
		return [$this->doSave($data)];
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
		$response["LpuSection_oid"] = $this->LpuSection_oid;
		$response["LpuUnitType_oid"] = $this->LpuUnitType_oid;
		return $response;
	}

	/**
	 * Загрузка данных для формы
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnOtherStacEditForm($data)
	{
		$query = "
			select
				EOS.EvnOtherStac_id as \"EvnOtherStac_id\",
				EOS.EvnOtherStac_pid as \"EvnOtherStac_pid\",
				to_char(EOS.EvnOtherStac_setDT, '{$this->dateTimeForm104}') as \"EvnOtherStac_setDate\",
				EOS.EvnOtherStac_setTime as \"EvnOtherStac_setTime\",
				round(EOS.EvnOtherStac_UKL, 3) as \"EvnOtherStac_UKL\",
				EOS.LeaveCause_id as \"LeaveCause_id\",
				EOS.LpuSection_oid as \"LpuSection_oid\",
				EOS.LpuUnitType_oid as \"LpuUnitType_oid\",
				EOS.Person_id as \"Person_id\",
				EOS.PersonEvn_id as \"PersonEvn_id\",
				EOS.ResultDesease_id as \"ResultDesease_id\",
				EOS.Server_id as \"Server_id\"
			from v_EvnOtherStac EOS
			where EOS.EvnOtherStac_id = :EvnOtherStac_id
			  and EOS.Lpu_id = :Lpu_id
			limit 1
		";
		$queryParams = [
			"EvnOtherStac_id" => $data["EvnOtherStac_id"],
			"Lpu_id" => $data["Lpu_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}
}