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
 * EvnDie_model - Модель "Смерть пациента"
 *
 * @package      Hospital
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       swan developers
 * @version      09.2014
 *
 * @property-read int $MedPersonal_id Медицинский работник, установивший смерть
 * @property-read int $IsWait Умер в приемном покое
 * @property-read int $DeathPlace_id Место смерти
 * @property-read int $IsAnatom	Необходимость патологоанатомической экспертизы
 * @property-read DateTime $expDT Дата и время экспертизы
 * @property-read string $expDate Дата экспертизы в формате Y-m-d
 * @property-read string $expTime Время экспертизы в формате H:i
 * @property-read int $MedPersonal_aid	Медицинский работник, проводивший вскрытие
 * @property-read int $AnatomWhere_id Место проведения патологоанатомической экспертизы
 * @property-read int $OrgAnatom_id Патологоанатомическая организация
 * @property-read int $LpuSection_aid Отделение, где проводилась экспертиза
 * @property-read int $Lpu_aid ЛПУ, где проводилась экспертиза
 * @property-read int $Diag_aid Патологоанатомический диагноз
 * @property-read int $DiagSetPhase_id Стадия/Фаза
 * @property-read string $PhaseDescr Описание фазы
 *
 * @property CI_DB_driver $db
 */
class EvnDie_model extends EvnLeaveAbstract_model
{
	public $dateTimeForm104 = "DD.MM.YYYY";

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]["alias"] = "EvnDie_id";
		$arr["pid"]["alias"] = "EvnDie_pid";
		$arr["setdate"]["alias"] = "EvnDie_setDate";
		$arr["settime"]["alias"] = "EvnDie_setTime";
		$arr["disdt"]["alias"] = "EvnDie_disDT";
		$arr["diddt"]["alias"] = "EvnDie_didDT";
		$arr["ukl"]["alias"] = "EvnDie_UKL";
		$arr["medpersonal_id"] = [
			"properties" => [self::PROPERTY_IS_SP_PARAM],
			"alias" => "MedPersonal_id",
			"label" => "Медицинский работник, установивший смерть",
			"save" => "trim",
			"type" => "id"
		];
		$arr["iswait"] = [
			"properties" => [self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM],
			"alias" => "EvnDie_IsWait",
			"label" => "Умер в приемном покое",
			"save" => "trim",
			"type" => "id"
		];
		$arr["deathplace_id"] = [
			"properties" => [self::PROPERTY_IS_SP_PARAM],
			"alias" => "DeathPlace_id",
			"label" => "Место смерти",
			"save" => "trim",
			"type" => "id"
		];
		$arr["isanatom"] = [
			"properties" => [self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM],
			"alias" => "EvnDie_IsAnatom",
			"label" => "Необходимость патологоанатомической экспертизы",
			"save" => "trim",
			"type" => "id"
		];
		$arr["expdt"] = [
			"properties" => [self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM, self::PROPERTY_DATE_TIME],
			"alias" => "EvnDie_expDT",
			"applyMethod" => "_applyExpDT",
			"dateKey" => "expdate",
			"timeKey" => "exptime",
		];
		$arr["expdate"] = [
			"properties" => [self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_READ_ONLY, self::PROPERTY_NOT_LOAD],
			// только для извлечения из POST и обработки методом _applyExpDT
			"alias" => "EvnDie_expDate",
			"label" => "Дата экспертизы",
			"save" => "trim",
			"type" => "date"
		];
		$arr["exptime"] = [
			"properties" => [self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_READ_ONLY, self::PROPERTY_NOT_LOAD],
			// только для извлечения из POST и обработки методом _applyExpDT
			"alias" => "EvnDie_expTime",
			"label" => "Время экспертизы",
			"save" => "trim",
			"type" => "time"
		];
		$arr["medpersonal_aid"] = [
			"properties" => [self::PROPERTY_IS_SP_PARAM],
			"alias" => "MedPersonal_aid",
			"label" => "Медицинский работник, проводивший вскрытие",
			"save" => "trim",
			"type" => "id"
		];
		$arr["anatomwhere_id"] = [
			"properties" => [self::PROPERTY_IS_SP_PARAM],
			"alias" => "AnatomWhere_id",
			"label" => "Место проведения экспертизы",
			"save" => "trim",
			"type" => "id"
		];
		$arr["organatom_id"] = [
			"properties" => [self::PROPERTY_IS_SP_PARAM],
			"alias" => "OrgAnatom_id",
			"label" => "Патологоанатомическая организация",
			"save" => "trim",
			"type" => "id"
		];
		$arr["lpu_aid"] = [
			"properties" => [self::PROPERTY_IS_SP_PARAM],
			"alias" => "Lpu_aid",
			"label" => "ЛПУ, где проводилась экспертиза",
			"save" => "trim",
			"type" => "id"
		];
		$arr["lpusection_aid"] = [
			"properties" => [self::PROPERTY_IS_SP_PARAM],
			"alias" => "LpuSection_aid",
			"label" => "Отделение, где проводилась экспертиза",
			"save" => "trim",
			"type" => "id"
		];
		$arr["diag_aid"] = [
			"properties" => [self::PROPERTY_IS_SP_PARAM],
			"alias" => "Diag_aid",
			"label" => "Патологоанатомический диагноз",
			"save" => "trim",
			"type" => "id"
		];
		$arr["diagsetphase_id"] = [
			"properties" => [self::PROPERTY_IS_SP_PARAM],
			"alias" => "DiagSetPhase_id",
			"label" => "Стадия/Фаза",
			"save" => "trim",
			"type" => "id"
		];
		$arr["phasedescr"] = [
			"properties" => [self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM],
			"alias" => "EvnDie_PhaseDescr",
			"label" => "Описание фазы",
			"save" => "trim",
			"type" => "string"
		];
		return $arr;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 38;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return "EvnDie";
	}

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Извлечение даты и времени экспертизы из входящих параметров
	 * @param $data
	 * @return bool
	 */
	protected function _applyExpDT($data)
	{
		return $this->_applyDT($data, "exp");
	}

	/**
	 * Дополнительная обработка значения атрибута сохраненного объекта из БД
	 * перед записью в модель
	 * @param string $column Имя колонки в строчными символами
	 * @param mixed $value Значение. Значения, которые в БД имеют тип datetime, являются экземлярами DateTime.
	 * @return mixed
	 * @throws Exception
	 */
	protected function _processingSavedValue($column, $value)
	{
		$this->_processingDtValue($column, $value, "exp");
		return parent::_processingSavedValue($column, $value);
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();
		if (in_array($this->scenario, [self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE])) {
			if (empty($this->MedPersonal_id)) {
				throw new Exception("Не указан врач, установивший смерть", 400);
			}
			if (empty($this->IsAnatom)) {
				throw new Exception("Не указан признак необходимости проведения экспертизы", 400);
			}
			if ($this->IsAnatom != 2) {
				$this->setAttribute("isanatom", 1);
				$this->setAttribute("expdt", null);
				$this->setAttribute("diag_aid", null);
				$this->setAttribute("anatomwhere_id", null);
				$this->setAttribute("organatom_id", null);
				$this->setAttribute("lpu_aid", null);
				$this->setAttribute("lpusection_aid", null);
				$this->setAttribute("medpersonal_aid", null);
			}
		}
	}

	/**
	 * Удаление
	 */
	function deleteEvnDie($data)
	{
		return [$this->doDelete($data)];
	}

	/**
	 * Сохранение
	 */
	function saveEvnDie($data)
	{
		if (empty($data["scenario"])) {
			$data["scenario"] = self::SCENARIO_DO_SAVE;
		}
		return [$this->doSave($data)];
	}

	/**
	 * Получение данных для формы
	 */
	function loadEvnDieEditForm($data)
	{
		$filter = getLpuIdFilter($data);
		$query = "
			select
				ED.EvnDie_id as \"EvnDie_id\",
				ED.EvnDie_pid as \"EvnDie_pid\",
				to_char(ED.EvnDie_setDT, '{$this->dateTimeForm104}') as \"EvnDie_setDate\",
				ED.EvnDie_setTime as \"EvnDie_setTime\",
				ROUND(ED.EvnDie_UKL, 3) as \"EvnDie_UKL\",
				ED.MedPersonal_id as \"MedStaffFact_id\",
				ED.EvnDie_IsWait as \"EvnDie_IsWait\",
				ED.EvnDie_IsAnatom as \"EvnDie_IsAnatom\",
				to_char(ED.EvnDie_expDT, '{$this->dateTimeForm104}') as \"EvnDie_expDate\",
				ED.EvnDie_expTime as \"EvnDie_expTime\",
				ED.AnatomWhere_id as \"AnatomWhere_id\",
				ED.LpuSection_aid as \"LpuSection_aid\",
				coalesce(ED.OrgAnatom_id, ED.Lpu_aid) as \"Org_aid\",
				ED.MedPersonal_aid as \"MedStaffFact_aid\",
				ED.Diag_aid as \"Diag_aid\",
				ED.Person_id as \"Person_id\",
				ED.PersonEvn_id as \"PersonEvn_id\",
				ED.Server_id as \"Server_id\"
			from v_EvnDie ED
			where ED.EvnDie_id = :EvnDie_id
			  and ED.Lpu_id {$filter}
			limit 1
		";
		$queryParams = [
			"EvnDie_id" => $data["EvnDie_id"],
			"Lpu_id" => $data["Lpu_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Логика после успешного выполнения запроса сохранения объекта
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{
		parent::_afterSave($result);
	}
}