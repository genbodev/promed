<?php
defined("BASEPATH") or die ("No direct script access allowed");
require_once("EvnOtherSection_model.php");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 * EvnOtherSectionBedProfile_model - Модель исхода с узкой койки в другое отделение
 *
 * @package      Hospital
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       swan developers
 * @version      09.2014
 *
 * @property-read int $LpuSectionBedProfile_oid Профиль коек отделения, куда производится выписка
 * @property CI_DB_driver $db
 */
class EvnOtherSectionBedProfile_model extends EvnOtherSection_model
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]["alias"] = "EvnOtherSectionBedProfile_id";
		$arr["pid"]["alias"] = "EvnOtherSectionBedProfile_pid";
		$arr["setdate"]["alias"] = "EvnOtherSectionBedProfile_setDate";
		$arr["settime"]["alias"] = "EvnOtherSectionBedProfile_setTime";
		$arr["disdt"]["alias"] = "EvnOtherSectionBedProfile_disDT";
		$arr["diddt"]["alias"] = "EvnOtherSectionBedProfile_didDT";
		$arr["ukl"]["alias"] = "EvnOtherSectionBedProfile_UKL";
		$arr["lpusectionbedprofile_oid"] = [
			"properties" => [self::PROPERTY_IS_SP_PARAM],
			"alias" => "LpuSectionBedProfile_oid",
			"label" => "Профиль коек отделения, куда производится выписка",
			"save" => "trim",
			"type" => "id"
		];
		$arr["lpusectionbedprofilelink_fedid"] = [
			"properties" => [self::PROPERTY_IS_SP_PARAM],
			"alias" => "LpuSectionBedProfileLink_fedid",
			"label" => "Ссылка на таблицу связки регионального и ФЕД профиля коек",
			"save" => "trim",
			"type" => "id"
		];
		return $arr;
	}

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 113;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return "EvnOtherSectionBedProfile";
	}

	/**
	 * Удаление
	 * @param $data
	 * @return array
	 */
	function deleteEvnOtherSectionBedProfile($data)
	{
		return array($this->doDelete($data));
	}

	/**
	 * Сохранение
	 * @param $data
	 * @return array
	 */
	function saveEvnOtherSectionBedProfile($data)
	{
		if (empty($data["scenario"])) {
			$data["scenario"] = self::SCENARIO_DO_SAVE;
		}
		return array($this->doSave($data));
	}

	/**
	 * Получение данных для копии
	 * @param $data
	 * @return array
	 */
	function doLoadCopyData($data)
	{
		$response = parent::doLoadCopyData($data);
		$response["LpuSection_oid"] = $this->LpuSection_oid;
		$response["LpuSectionBedProfile_oid"] = $this->LpuSectionBedProfile_oid;
		return $response;
	}

	/**
	 * Загрузка данных для формы
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnOtherSectionBedProfileEditForm($data)
	{
		$query = "
			select
				EOSBP.EvnOtherSectionBedProfile_id as \"EvnOtherSectionBedProfile_id\",
				EOSBP.EvnOtherSectionBedProfile_pid as \"EvnOtherSectionBedProfile_pid\",
				to_char(EOSBP.EvnOtherSectionBedProfile_setDT, '{$this->dateTimeForm104}') as \"EvnOtherSectionBedProfile_setDate\",
				EOSBP.EvnOtherSectionBedProfile_setTime as \"EvnOtherSectionBedProfile_setTime\",
				round(EOSBP.EvnOtherSectionBedProfile_UKL, 3) as \"EvnOtherSectionBedProfile_UKL\",
				EOSBP.LeaveCause_id as \"LeaveCause_id\",
				EOSBP.LpuSection_oid as \"LpuSection_oid\",
				EOSBP.LpuSectionBedProfile_oid as \"LpuSectionBedProfile_oid\",
				EOSBP.Person_id as \"Person_id\",
				EOSBP.PersonEvn_id as \"PersonEvn_id\",
				EOSBP.ResultDesease_id as \"ResultDesease_id\",
				EOSBP.Server_id as \"Server_id\"
			from v_EvnOtherSectionBedProfile EOSBP
			where EOSBP.EvnOtherSectionBedProfile_id = :EvnOtherSectionBedProfile_id
			  and EOSBP.Lpu_id = :Lpu_id
			limit 1
		";
		$queryParams = [
			"EvnOtherSectionBedProfile_id" => $data["EvnOtherSectionBedProfile_id"],
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