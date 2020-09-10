<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		PromedWeb
 * @access		public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		31.10.2014
 *
 * Модель шаблонов по умолчанию.
 *
 * Шаблон по умолчанию назначается для связки место работы/служба+врач,
 * класс события, тип документа.
 * В качестве шаблона по умолчанию можно выбрать шаблон, недоступную для редактирования.
 * Один и тот же шаблон можно использовать по умолчанию для разных типов документов, мест работы, пользователей.
 *
 * @package		XmlTemplate
 * @author		Александр Пермяков
 *
 * @property int $XmlTemplate_id
 * @property-read int $XmlType_id
 * @property-read int $EvnClass_id
 * @property int $UslugaComplex_id
 * @property int $Server_id
 * @property int $Lpu_id
 * @property int $MedPersonal_id
 * @property int $LpuSection_id
 * @property int $MedStaffFact_id
 * @property int $MedService_id
 *
 * @property CI_DB_driver $db
 */
class XmlTemplateDefault_model extends swPgModel
{
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList([
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
			"search",
		]);
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return "XmlTemplateDefault";
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes()
	{
		return [
			self::ID_KEY => [
				"properties" => [
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_NOT_NULL,
				],
				"alias" => "XmlTemplateDefault_id",
				"label" => "Идентификатор",
				"save" => "trim",
				"type" => "id"
			],
			"insdt" => [
				"properties" => [
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				],
			],
			"pmuser_insid" => [
				"properties" => [
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				],
			],
			"upddt" => [
				"properties" => [
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				],
			],
			"pmuser_updid" => [
				"properties" => [
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				],
			],
			"server_id" => [
				"properties" => [
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NOT_SAFE,
				],
			],
			"xmltemplate_id" => [
				"properties" => [
					self::PROPERTY_IS_SP_PARAM,
				],
				"alias" => "XmlTemplate_id",
				"label" => "Шаблон",
				"save" => "trim|required",
				"type" => "id"
			],
			"uslugacomplex_id" => [
				"properties" => [
					self::PROPERTY_IS_SP_PARAM,
				],
				"alias" => "UslugaComplex_id",
				"label" => "Услуга",
				"save" => "trim",
				"type" => "id"
			],
			"lpu_id" => [
				"properties" => [
					self::PROPERTY_IS_SP_PARAM,
				],
				"alias" => "Lpu_id",
			],
			"medpersonal_id" => [
				"properties" => [
					self::PROPERTY_IS_SP_PARAM,
				],
				"alias" => "MedPersonal_id",
				"label" => "Медицинский сотрудник",
				"save" => "trim",
				"type" => "id"
			],
			"lpusection_id" => [
				"properties" => [
					self::PROPERTY_IS_SP_PARAM,
				],
				"alias" => "LpuSection_id",
				"label" => "Отделение",
				"save" => "trim",
				"type" => "id"
			],
			"medstafffact_id" => [
				"properties" => [
					self::PROPERTY_IS_SP_PARAM,
				],
				"alias" => "MedStaffFact_id",
				"label" => "Рабочее место врача",
				"save" => "trim",
				"type" => "id"
			],
			"medservice_id" => [
				"properties" => [
					self::PROPERTY_IS_SP_PARAM,
				],
				"alias" => "MedService_id",
				"label" => "Служба",
				"save" => "trim",
				"type" => "id"
			],
			"xmltype_id" => [
				"properties" => [
					self::PROPERTY_READ_ONLY,
				],
				"alias" => "XmlType_id",
				"label" => "Тип Документа",
				"save" => "trim|required",
				"type" => "id",
				"select" => "tpl.XmlType_id",
				"join" => "inner join v_XmlTemplate tpl on tpl.XmlTemplate_id = {ViewName}.XmlTemplate_id",
			],
			"evnclass_id" => [
				"properties" => [
					self::PROPERTY_READ_ONLY,
				],
				"alias" => "EvnClass_id",
				"label" => "Категория Документа",
				"save" => "trim|required",
				"type" => "id",
				"select" => "tpl.EvnClass_id",
			],
		];
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $name
	 * @return array
	 */
	function getInputRules($name)
	{
		$rules = parent::getInputRules($name);
		switch ($name) {
			case "search":
				$rules = [
					["field" => "XmlType_id", "label" => "Идентификатор типа документа", "rules" => "trim|required", "type" => "id"],
					["field" => "EvnClass_id", "label" => "Идентификатор категории документа", "rules" => "trim|required", "type" => "id"],
					["field" => "MedStaffFact_id", "label" => "Идентификатор рабочего места", "rules" => "trim", "type" => "id"],
					["field" => "MedService_id", "label" => "Идентификатор службы", "rules" => "trim", "type" => "id"],
					["field" => "MedPersonal_id", "label" => "Идентификатор врача", "rules" => "trim", "type" => "id"],
					["field" => "UslugaComplex_id", "label" => "Идентификатор услуги", "rules" => "trim", "type" => "id"],
				];
				break;
			case "searchByUsluga":
				$rules = [
					["field" => "XmlType_id", "label" => "Идентификатор типа документа", "rules" => "trim", "type" => "id"],
					["field" => "EvnClass_id", "label" => "Идентификатор категории документа", "rules" => "trim", "type" => "id"],
					["field" => "MedService_id", "label" => "Идентификатор службы", "rules" => "trim", "type" => "id"],
					["field" => "MedPersonal_id", "label" => "Идентификатор врача", "rules" => "trim", "type" => "id"],
					["field" => "UslugaComplex_id", "label" => "Идентификатор услуги", "rules" => "trim|required", "type" => "id"],
				];
				break;
		}
		return $rules;
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();

		if (in_array($this->scenario, [self::SCENARIO_AUTO_CREATE, self::SCENARIO_DO_SAVE])) {
			if (empty($this->XmlTemplate_id)) {
				throw new Exception("Не указан шаблон", 500);
			} else {
				$query = "
					select
						XmlType_id as \"XmlType_id\",
					    EvnClass_id as \"EvnClass_id\"
					from v_XmlTemplate
					where XmlTemplate_id = :id
				";
				$queryParams = ["id" => $this->XmlTemplate_id];

				$tmp = $this->getFirstRowFromQuery($query, $queryParams);
				if (empty($tmp)) {
					throw new Exception("Шаблон не найден", 400);
				}
				if ($tmp["XmlType_id"] != $this->XmlType_id || $tmp["EvnClass_id"] != $this->EvnClass_id) {
					throw new Exception("Шаблон должен быть той же категории и типа документа", 400);
				}
			}
		}
		if ("search" == $this->scenario) {
			if (empty($this->XmlType_id)) {
				throw new Exception("Не указан тип документа", 500);
			}
			if (empty($this->EvnClass_id)) {
				throw new Exception("Не указана категория документа", 500);
			}
		}
		if (in_array($this->scenario, [self::SCENARIO_AUTO_CREATE, self::SCENARIO_DO_SAVE, "search"])) {
			$emptyKey = true;
			if ($this->MedStaffFact_id > 0) {
				$emptyKey = false;
			}
			if ($this->UslugaComplex_id > 0) {
				$emptyKey = false;
			}
			if ($this->MedService_id > 0 && $this->MedPersonal_id) {
				$emptyKey = false;
			}
			if ($emptyKey) {
				throw new Exception("Не указана связка место работы/служба+врач/услуга", 7001);
			}
		}
	}

	/**
	 * Формируется запрос поиска шаблона по умолчанию с учетом фильтров
	 * @param string $add_select
	 * @param string $add_join
	 * @param string $filters
	 * @param array $params
	 * @return array
	 */
	private function _getLoadQuery($add_select = "", $add_join = "", $filters = "", $params = [])
	{
		$query = [];
		$query["params"] = $params;
		$query["params"]["EvnClass_id"] = $this->EvnClass_id;
		$query["params"]["XmlType_id"] = $this->XmlType_id;
		if (!empty($this->MedStaffFact_id)) {
			$filters .= "
				and xtd.MedStaffFact_id = :MedStaffFact_id
			";
			$query["params"]["MedStaffFact_id"] = $this->MedStaffFact_id;
		} else if (!empty($this->UslugaComplex_id)) {
			$filters .= "
				and xtd.UslugaComplex_id = :UslugaComplex_id
				and xtd.pmUser_insID = :pmUser_id
			";
			$query["params"]["UslugaComplex_id"] = $this->UslugaComplex_id;
			$query["params"]["pmUser_id"] = $this->promedUserId;
		} else {
			$filters .= "
				and xtd.MedService_id = :MedService_id
				and xtd.MedPersonal_id = :MedPersonal_id
			";
			$query["params"]["MedService_id"] = $this->MedService_id;
			$query["params"]["MedPersonal_id"] = $this->MedPersonal_id;
		}
		if (!empty($this->EvnClass_id)) {
			$query["params"]["EvnClass_id"] = $this->EvnClass_id;
		}
		if (!empty($this->XmlType_id)) {
			$query["params"]["XmlType_id"] = $this->XmlType_id;
		}
		$selectString = "xtd.XmlTemplateDefault_id as \"XmlTemplateDefault_id\" {$add_select}";
		$fromString = "
			v_XmlTemplateDefault xtd
			inner join dbo.v_XmlTemplate tpl on tpl.XmlTemplate_id = xtd.XmlTemplate_id
			{$add_join}
		";
		$whereString = "
				tpl.EvnClass_id = :EvnClass_id
			and tpl.XmlType_id = :XmlType_id {$filters}
			and COALESCE(tpl.XmlTemplate_IsDeleted, 1) = 1
			--PROMEDWEB-10896
			--Иногда шаблоны по умолчанию - удаляют, нужна проверка
		";
		$query["sql"] = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
			limit 1
		";
		return $query;
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array())
	{
		parent::_beforeSave($data);
		if ($this->MedService_id > 0) {
			$query = "
				select
					Lpu_id as \"Lpu_id\",
				    LpuSection_id as \"LpuSection_id\"
				from v_MedService
				where MedService_id = :id
			";
			$queryParams = ["id" => $this->MedService_id];
			$tmp = $this->getFirstRowFromQuery($query, $queryParams);
			if (empty($tmp)) {
				throw new Exception("Служба не найдена", 400);
			}
			$this->setAttribute("lpu_id", $tmp["Lpu_id"]);
			$this->setAttribute("lpusection_id", $tmp["LpuSection_id"]);
		} else if (empty($this->MedPersonal_id) && empty($this->LpuSection_id)) {
			$query = "
				select
					Lpu_id as \"Lpu_id\",
				    LpuSection_id as \"LpuSection_id\",
				    MedPersonal_id as \"MedPersonal_id\"
				from v_MedStaffFact
				where MedStaffFact_id = :id
			";
			$queryParams = ["id" => $this->MedStaffFact_id];
			$tmp = $this->getFirstRowFromQuery($query, $queryParams);
			if (empty($tmp)) {
				throw new Exception("Рабочее место не найдено", 400);
			}
			$this->setAttribute("lpu_id", $tmp["Lpu_id"]);
			$this->setAttribute("lpusection_id", $tmp["LpuSection_id"]);
			$this->setAttribute("medpersonal_id", $tmp["MedPersonal_id"]);
		}
	}

	/**
	 * Метод для сохранения шаблона по умолчанию
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public function save($data)
	{
		// ищем шаблон по умолчанию
		$data["scenario"] = "search";
		$data["XmlTemplateDefault_id"] = null;
		$this->applyData($data);
		$this->_validate();

		$query = $this->_getLoadQuery("
			,tpl.XmlTemplate_id as \"oldXmlTemplate_id\"
			,tpl.XmlTemplate_Caption as \"oldXmlTemplate_Caption\"
			,tpl1.XmlTemplate_id as \"newXmlTemplate_id\"
			,tpl1.XmlTemplate_Caption as \"newXmlTemplate_Caption\"
		", "
			left join v_XmlTemplate tpl1 on tpl1.XmlTemplate_id = :XmlTemplate_id
		");
		$query["params"]["XmlTemplate_id"] = $this->XmlTemplate_id;
		$result = $this->getFirstRowFromQuery($query["sql"], $query["params"], true);
		if ($result === false) {
			throw new Exception("Ошибка запроса к БД при попытке получить идентификатор шаблона по умолчанию.", 500);
		}
		if (!empty($result)) {
			$data["XmlTemplateDefault_id"] = $result["XmlTemplateDefault_id"];
			if (!empty($data["checkSetDefault"]) && $result["oldXmlTemplate_id"] != $result["newXmlTemplate_id"]) {
				$oldCaption = $result["oldXmlTemplate_Caption"];
				$newCaption = $result["newXmlTemplate_Caption"];
				$msg = "По умолчанию уже стоит шаблон \"{$oldCaption}\". Заменить его на \"{$newCaption}\"?";
				return [[
					"success" => false,
					"Error_Msg" => "YesNo",
					"Error_Code" => "201",
					"Alert_Msg" => $msg,
				]];
			}
		}
		$data["scenario"] = self::SCENARIO_DO_SAVE;
		$response = $this->doSave($data);
		if (!empty($response["XmlTemplateDefault_id"])) {
			$response["oldXmlTemplate_id"] = $result ? $result["oldXmlTemplate_id"] : null;
			$response["newXmlTemplate_id"] = $data["XmlTemplate_id"];
		}
		return $response;
	}

	/**
	 * Получение идентификатора шаблона по умолчанию
	 * @param $data
	 * @return array|bool|float|int|string
	 * @throws Exception
	 */
	public function getXmlTemplateId($data)
	{
		$data["scenario"] = "search";
		if (empty($data["EvnClass_id"])) {
			$data["EvnClass_id"] = 11;
		}
		if (empty($data["XmlType_id"])) {
			$data["XmlType_id"] = 3;
		}
		$this->applyData($data);
		try {
			$this->_validate();
			$query = $this->_getLoadQuery(",xtd.XmlTemplate_id as \"XmlTemplate_id\"");
			/**@var CI_DB_result $result */
			$result = $this->db->query($query["sql"], $query["params"]);
			if (false == is_object($result)) {
				return false;
			}
			$tmp = $result->result("array");
			if (empty($tmp)) {
				throw new Exception("Пользователь не установил себе шаблон по умолчанию", 7001);
			}
			return $tmp;
		} catch (Exception $e) {
			if ($e->getCode() == 7001) {
				// Не указана связка место работы/служба+врач/услуга (например, при работе в АРМ регистратора поликлиники)
				// Или пользователь не установил себе шаблон по умолчанию
				$query = "
					select XmlTemplate_id as \"XmlTemplate_id\"
					from v_XmlTemplateBase
					where XmlType_id = :XmlType_id
					  and (EvnClass_id is null or EvnClass_id = :EvnClass_id)
					limit 1
				";
				$queryParams = [
					"XmlType_id" => $this->XmlType_id,
					"EvnClass_id" => $this->EvnClass_id
				];
				$tmp = $this->getFirstResultFromQuery($query, $queryParams);
				if ($tmp > 0) {
					$tmp = [["XmlTemplate_id" => $tmp]];
				} else {
					$tmp = [];
				}
				return $tmp;
			}
		}
		return false;
	}

	/**
	 * Получение идентификатора шаблона по умолчанию для услуги
	 * Пробуем получить шаблон, который пользователь назначил себе по умолчанию для услуги
	 * Если шаблон не назначен,
	 * то берем из справочника услуг
	 * @param $data
	 * @return array|bool|float|int|string
	 * @throws Exception
	 */
	 public function getXmlTemplateIdByUsluga($data)
	{
		$data["scenario"] = "search";
		$data["XmlType_id"] = 4; //Протокол оказания услуги, 7 - Протокол лабораторной услуги
		$data["EvnClass_id"] = 47;
		$this->applyData($data);
		$this->_validate();
		$query = $this->_getLoadQuery(",xtd.XmlTemplate_id as \"XmlTemplate_id\"", "", " and COALESCE(tpl.XmlTemplate_IsDeleted, 1) = 1");
		$result = $this->db->query($query["sql"], $query["params"]);
		if (!is_object($result)) {
			return false;
		}
		$tmp = $result->result("array");
		if (empty($tmp)) {
			$query = "
				select UC.XmlTemplate_id as \"XmlTemplate_id\"
				from
					v_UslugaComplex UC
					inner join v_XmlTemplate tpl on tpl.XmlTemplate_id = uc.XmlTemplate_id
				where UC.UslugaComplex_id = :id
				  and coalesce(tpl.XmlTemplate_IsDeleted, 1) = 1
			";
			$queryParams = ["id" => $this->UslugaComplex_id];
			$tmp = $this->getFirstResultFromQuery($query, $queryParams);
			if ($tmp > 0) {
				$tmp = [["XmlTemplate_id" => $tmp]];
			} else {
				$tmp = [];
			}
		}
		return $tmp;
	}
}