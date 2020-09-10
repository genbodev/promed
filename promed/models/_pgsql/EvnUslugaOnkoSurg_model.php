<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Хирургическое лечение
 *
 * @package      MorbusOnko
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @version      06.2013
 *
 * @property MorbusOnkoSpecifics_model $MorbusOnkoSpecifics
 * @property CI_DB_driver $db
 * @property EvnAgg_model $EvnAgg_model
 * @property EvnUsluga_model $EvnUsluga_model
 */
class EvnUslugaOnkoSurg_model extends SwPgModel
{
	private $dateTimeForm104 = "DD.MM.YYYY";
	private $dateTimeForm108 = "HH24:MI";
	private $dateTimeForm120 = "YYYY-MM-DD";

	private $EvnUslugaOnkoSurg_id; //EvnUslugaOnkoSurg_id
	private $pmUser_id; //Идентификатор пользователя системы Промед
	private $inputRules;

	/**
	 * Получение идентификатора
	 *
	 * @return mixed
	 */
	public function getId()
	{
		return $this->EvnUslugaOnkoSurg_id;
	}

	/**
	 * Установка идентификатора
	 *
	 * @param $value
	 */
	public function setId($value)
	{
		$this->EvnUslugaOnkoSurg_id = $value;
	}

	/**
	 * Получение идентификатора
	 *
	 * @return mixed
	 */
	public function getpmUser_id()
	{
		return $this->pmUser_id;
	}

	/**
	 * Установка идентификатора
	 *
	 * @param $value
	 */
	public function setpmUser_id($value)
	{
		$this->pmUser_id = $value;
	}

	/**
	 * EvnUslugaOnkoSurg_model constructor.
	 * @throws Exception
	 */
	function __construct()
	{
		parent::__construct();
		if (!isset($_SESSION["pmuser_id"])) {
			throw new Exception("Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)");
		}
		$this->setpmUser_id($_SESSION["pmuser_id"]);
		$this->inputRules = [
			"save" => [
				[
					"field" => "EvnUslugaOnkoSurg_pid",
					"label" => "Учетный документ (посещение или движение в стационаре)",
					"rules" => "",
					"type" => "id"
				],
				[
					"field" => "EvnPL_id",
					"label" => "Случай лечения",
					"rules" => "",
					"type" => "id"
				],
				[
					"field" => "Server_id",
					"label" => "Источник",
					"rules" => "required",
					"type" => "int"
				],
				[
					"field" => "PersonEvn_id",
					"label" => "Состояние данных человека",
					"rules" => "required",
					"type" => "id"
				],
				[
					"field" => "Person_id",
					"label" => "Человек",
					"rules" => "required",
					"type" => "id"
				],
				[
					"field" => "EvnUslugaOnkoSurg_setDate",
					"label" => "Дата начала",
					"rules" => "trim|required",
					"type" => "date"
				],
				[
					"field" => "EvnUslugaOnkoSurg_setTime",
					"label" => "Время начала",
					"rules" => "trim|required",
					"type" => "time"
				],
				[
					"field" => "EvnUslugaOnkoSurg_disDate",
					"label" => "Дата окончания",
					"rules" => "trim",
					"type" => "date"
				],
				[
					"field" => "EvnUslugaOnkoSurg_disTime",
					"label" => "Время окончания",
					"rules" => "trim",
					"type" => "time"
				],
				[
					"field" => "Morbus_id",
					"label" => "Заболевание",
					"rules" => "required",
					"type" => "id"
				],
				[
					"field" => "PayType_id",
					"label" => "Тип оплаты",
					"rules" => "",
					"type" => "id"
				],
				[
					"field" => "UslugaPlace_id",
					"label" => "Тип места проведения",
					"rules" => "",
					"type" => "id"
				],
				[
					"field" => "Lpu_uid",
					"label" => "Место выполнения",
					"rules" => "",
					"type" => "id"
				],
				[
					"field" => "EvnUslugaOnkoSurg_id",
					"label" => "Хирургическое лечение",
					"rules" => "",
					"type" => "id"
				],
				[
					"field" => "UslugaComplex_id",
					"label" => "Название операции",
					"rules" => "required",
					"type" => "id"
				],
				[
					"field" => "OperType_id",
					"label" => "Тип операции",
					"rules" => "",
					"type" => "id"
				],
				[
					"field" => "MedPersonal_id",
					"label" => "Кто проводил",
					"rules" => "",
					"type" => "id"
				],
				[
					"field" => "OnkoSurgTreatType_id",
					"label" => "Характер хирургического лечения",
					"rules" => "",
					"type" => "id"
				],
				[
					"field" => "OnkoSurgicalType_id",
					"label" => "Тип лечения",
					"rules" => "required",
					"type" => "id"
				],
				[
					"field" => "AggType_id",
					"label" => "Интраоперационное осложнение",
					"rules" => "",
					"type" => "id"
				],
				[
					"field" => "AggType_sid",
					"label" => "Послеоперационное осложнение",
					"rules" => "",
					"type" => "id"
				],
				[
					"field" => "AggTypes",
					"label" => "Интраоперационные осложнения",
					"rules" => "",
					"type" => "string"
				],
				[
					"field" => "AggTypes2",
					"label" => "Послеоперационные осложнения",
					"rules" => "",
					"type" => "string"
				],
				[
					"field" => "TreatmentConditionsType_id",
					"label" => "Условие проведения лечения",
					"rules" => "",
					"type" => "id"
				]
			],
			"load" => [
				[
					"field" => "EvnUslugaOnkoSurg_id",
					"label" => "Хирургическое  лечение",
					"rules" => "required",
					"type" => "id"
				],
			],
			"getDefaultTreatmentConditionsTypeId" => [
				[
					"field" => "EvnUslugaOnkoSurg_pid",
					"label" => "Учетный документ (посещение или движение в стационаре)",
					"rules" => "required",
					"type" => "id"
				],
			],
			"delete" => [
				[
					"field" => "EvnUslugaOnkoSurg_id",
					"label" => "Хирургическое  лечение",
					"rules" => "required",
					"type" => "id"
				],
			],
			"loadForPrint" => [
				[
					"field" => "EvnUslugaOnkoSurg_id",
					"label" => "Хирургическое  лечение",
					"rules" => "required",
					"type" => "id"
				],
			],
		];
	}

	/**
	 * Получение входящих параметров
	 *
	 * @param null $name
	 * @return array
	 */
	function getInputRules($name = null)
	{
		return $this->inputRules;
	}

	/**
	 * Загрузка
	 *
	 * @return array|bool
	 */
	function load()
	{
		$sql = "
			select
				EU.EvnUslugaOnkoSurg_id as \"EvnUslugaOnkoSurg_id\",
				EU.EvnUslugaOnkoSurg_pid as \"EvnUslugaOnkoSurg_pid\",
				EU.Server_id as \"Server_id\",
				EU.PersonEvn_id as \"PersonEvn_id\",
				EU.Person_id as \"Person_id\",
				to_char(EU.EvnUslugaOnkoSurg_setDT, '{$this->dateTimeForm104}') as \"EvnUslugaOnkoSurg_setDate\",
				to_char(EU.EvnUslugaOnkoSurg_setDT, '{$this->dateTimeForm108}') as \"EvnUslugaOnkoSurg_setTime\",
				to_char(EU.EvnUslugaOnkoSurg_disDT, '{$this->dateTimeForm104}') as \"EvnUslugaOnkoSurg_disDate\",
				to_char(EU.EvnUslugaOnkoSurg_disDT, '{$this->dateTimeForm108}') as \"EvnUslugaOnkoSurg_disTime\",
				MO.Morbus_id as \"Morbus_id\",
				MO.MorbusOnko_id as \"MorbusOnko_id\",
				EU.Lpu_uid as \"Lpu_uid\",
				UC.UslugaComplex_id as \"UslugaComplex_id\",
				UC.UslugaCategory_id as \"UslugaCategory_id\",
				EU.OperType_id as \"OperType_id\",
				EU.AggType_id as \"AggType_id\",
				EU.TreatmentConditionsType_id as \"TreatmentConditionsType_id\",
				EU.MedPersonal_id as \"MedPersonal_id\",
				EU.AggType_sid as \"AggType_sid\",
				EU.OnkoSurgTreatType_id as \"OnkoSurgTreatType_id\",
				EU.OnkoSurgicalType_id as \"OnkoSurgicalType_id\"
			from
				v_EvnUslugaOnkoSurg EU
				inner join v_MorbusOnko MO on EU.Morbus_id = MO.Morbus_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
			where EvnUslugaOnkoSurg_id = :EvnUslugaOnkoSurg_id
		";
		$sqlParams = ["EvnUslugaOnkoSurg_id" => $this->EvnUslugaOnkoSurg_id];
		$result = $this->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		if (is_array($result) && count($result) > 0) {
			$this->load->model("EvnAgg_model");
			$aggs = $this->EvnAgg_model->loadEvnAggList([
				"EvnAgg_pid" => $this->EvnUslugaOnkoSurg_id,
				"AggWhen_id" => 1
			]);
			if (is_array($aggs) && count($aggs) > 0) {
				$result[0]["AggTypes"] = $aggs;
			} else {
				$result[0]["AggTypes"] = "";
			}
			$aggs2 = $this->EvnAgg_model->loadEvnAggList([
				"EvnAgg_pid" => $this->EvnUslugaOnkoSurg_id,
				"AggWhen_id" => 2
			]);
			if (is_array($aggs2) && count($aggs2) > 0) {
				$result[0]["AggTypes2"] = $aggs2;
			} else {
				$result[0]["AggTypes2"] = "";
			}
		}
		return $result;
	}

	/**
	 * определениe условия проведения лечения по умолчанию
	 * @param $data
	 * @return int|null
	 */
	function getDefaultTreatmentConditionsTypeId($data)
	{
		// При вводе из посещения/движения с отделением любого типа, кроме «круглосуточный стационар»
		// автоматом подставлять «амбулаторно»,
		// если тип отделения «круглосуточный стационар», то «стационарно»
		$lpuunittype_sysnick = null;
		if (!isset($data["EvnUslugaOnkoSurg_pid"])) {
			return null;
		}
		$sql = "
			select lu.LpuUnitType_SysNick as \"LpuUnitType_SysNick\"
			from
				v_EvnSection es
				inner join v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
				inner join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
			where es.EvnSection_id = :EvnUslugaOnkoSurg_pid
		";
		$result = $this->db->query($sql, $data);
		if (is_object($result)) {
			$tmp = $result->result("array");
			if (count($tmp) > 0) {
				$lpuunittype_sysnick = $tmp[0]["LpuUnitType_SysNick"];
			}
		}
		if ($lpuunittype_sysnick == "stac") {
			return 2; //Стационарно
		}
		return 1; //Амбулаторно
	}

	/**
	 * Сохранение
	 *
	 * @param $data
	 * @return array
	 *
	 * @throws Exception
	 */
	function save($data)
	{
		// проверки перед сохранением
		$this->load->model("MorbusOnkoSpecifics_model", "MorbusOnkoSpecifics");
		$tmp = $this->MorbusOnkoSpecifics->getDataForCheckEvnUslugaOnkoByEvn($data["EvnUslugaOnkoSurg_pid"], $data["Morbus_id"]);
		if (empty($tmp)) {
			throw new Exception("Не удалось получить данные заболевания");
		}
		if (!empty($data["EvnUslugaOnkoSurg_pid"])) {
			$check = $this->MorbusOnkoSpecifics->checkDatesBeforeSave([
				"Evn_id" => $data["EvnUslugaOnkoSurg_pid"],
				"dateOnko" =>  $data["EvnUslugaOnkoSurg_setDate"]
			]);
			if (isset($check["Err_Msg"])) {
				throw new Exception($check["Err_Msg"]);
			}
		}

        $data["EvnUslugaOnkoSurg_setDT"] = $data["EvnUslugaOnkoSurg_setDate"] . " " . $data["EvnUslugaOnkoSurg_setTime"];
        $data["EvnUslugaOnkoSurg_disDT"] = null;

        if (!empty($data["EvnUslugaOnkoSurg_disDate"])) {
			$data["EvnUslugaOnkoSurg_disDT"] = $data["EvnUslugaOnkoSurg_disDate"];

			if (!empty($data["EvnUslugaOnkoSurg_disTime"])) {
				$data["EvnUslugaOnkoSurg_disDT"] .= " " . $data["EvnUslugaOnkoSurg_disTime"];
			}
		}
		if (
			!empty($tmp[0]["MorbusOnko_specSetDT"]) &&
			(
				$data["EvnUslugaOnkoSurg_setDate"] < $tmp[0]["MorbusOnko_specSetDT"] ||
				(!empty($tmp[0]["MorbusOnko_specDisDT"]) && $data["EvnUslugaOnkoSurg_setDate"] > $tmp[0]["MorbusOnko_specDisDT"])
			)
		) {
			throw new Exception("Дата проведения не входит в период специального лечения");
		}
		if (
			!empty($data["EvnUslugaOnkoSurg_disDate"]) &&
			!empty($tmp[0]["MorbusOnko_specSetDT"]) &&
			(
				$data["EvnUslugaOnkoSurg_disDate"] < $tmp[0]["MorbusOnko_specSetDT"] ||
				(!empty($tmp[0]["MorbusOnko_specDisDT"]) && $data["EvnUslugaOnkoSurg_disDate"] > $tmp[0]["MorbusOnko_specDisDT"])
			)
		) {
			throw new Exception("Дата окончания не входит в период специального лечения");
		}
		if (!empty($data["EvnUslugaOnkoSurg_setDT"]) && !empty($data["EvnUslugaOnkoSurg_disDT"]) && $data["EvnUslugaOnkoSurg_setDT"] > $data["EvnUslugaOnkoSurg_disDT"]) {
			throw new Exception("Дата начала не может быть больше даты окончания");
		}
		// сохраняем
		$procedure = "p_EvnUslugaOnkoSurg_upd";
		if (empty($data["EvnUslugaOnkoSurg_id"])) {
			$procedure = "p_EvnUslugaOnkoSurg_ins";
			$data["EvnUslugaOnkoSurg_id"] = null;
		}
		if (empty($data["TreatmentConditionsType_id"])) {
			$data["TreatmentConditionsType_id"] = $this->getDefaultTreatmentConditionsTypeId($data);
		}
		$pt = $data["PayType_id"];

		if($pt == null && $data["EvnUslugaOnkoSurg_pid"] != null) {
			$pt = $this->getFirstResultFromQuery("select PayType_id from v_EvnSection where EvnSection_id = :EvnUslugaOnkoSurg_pid limit 1", $data);
		}
		if($pt == null && $data["EvnUslugaOnkoSurg_pid"] != null) {
			$pt = $this->getFirstResultFromQuery("select PayType_id from v_EvnVizit where EvnVizit_id = :EvnUslugaOnkoSurg_pid limit 1", $data);
		}
		if($pt == null) {
			$pt = $this->getFirstResultFromQuery("select PayType_id from v_PayType where PayType_SysNick = :PayType_SysNickOMS limit 1", [
				'PayType_SysNickOMS' => getPayTypeSysNickOMS()
			]);
		}
		$data["PayType_id"] = $pt;
		$selectString = "
			evnuslugaonkosurg_id as \"EvnUslugaOnkoSurg_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$sql = "
			select {$selectString}
			from {$procedure}(
				evnuslugaonkosurg_id := :EvnUslugaOnkoSurg_id,
			    evnuslugaonkosurg_pid := :EvnUslugaOnkoSurg_pid,
			    lpu_id := :Lpu_id,
			    server_id := :Server_id,
			    personevn_id := :PersonEvn_id,
			    evnuslugaonkosurg_setdt := :EvnUslugaOnkoSurg_setDT,
			    evnuslugaonkosurg_disdt := :EvnUslugaOnkoSurg_disDT,
			    morbus_id := :Morbus_id,
			    paytype_id := :PayType_id,
			    medpersonal_id := :MedPersonal_id,
			    uslugaplace_id := :UslugaPlace_id,
			    lpu_uid := :Lpu_uid,
			    uslugacomplex_id := :UslugaComplex_id,
			    evnprescr_id := null,
			    evnprescrtimetable_id := null,
			    opertype_id := :OperType_id,
			    onkosurgtreattype_id := :OnkoSurgTreatType_id,
			    aggtype_id := :AggType_id,
			    aggtype_sid := :AggType_sid,
			    treatmentconditionstype_id := :TreatmentConditionsType_id,
			    onkosurgicaltype_id := :OnkoSurgicalType_id,
			    pmuser_id := :pmUser_id
			);
		";
		$sqlParams = [
			"EvnUslugaOnkoSurg_pid" => $data["EvnUslugaOnkoSurg_pid"],
			"Lpu_id" => $data["Lpu_id"],
			"Server_id" => $data["Server_id"],
			"PersonEvn_id" => $data["PersonEvn_id"],
			"EvnUslugaOnkoSurg_setDT" => $data["EvnUslugaOnkoSurg_setDT"],
			"EvnUslugaOnkoSurg_disDT" => $data["EvnUslugaOnkoSurg_disDT"],
			"Morbus_id" => $data["Morbus_id"],
			"PayType_id" => $data["PayType_id"],
			"PayType_SysNickOMS" => getPayTypeSysNickOMS(),
			"UslugaPlace_id" => empty($data["UslugaPlace_id"]) ? 1 : $data["UslugaPlace_id"],
			"Lpu_uid" => $data["Lpu_uid"],
			"EvnUslugaOnkoSurg_id" => $data["EvnUslugaOnkoSurg_id"],
			"MedPersonal_id" => $data["MedPersonal_id"],
			"AggType_sid" => $data["AggType_sid"],
			"OnkoSurgTreatType_id" => $data["OnkoSurgTreatType_id"],
			"OnkoSurgicalType_id" => $data["OnkoSurgicalType_id"],
			"UslugaComplex_id" => $data["UslugaComplex_id"],
			"OperType_id" => $data["OperType_id"],
			"AggType_id" => $data["AggType_id"],
			"TreatmentConditionsType_id" => $data["TreatmentConditionsType_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$result = $result->result("array");
		if (!empty($result[0]["EvnUslugaOnkoSurg_id"])) {
			$this->load->model("EvnAgg_model");
			//интраоперационные
			$aggs = $this->EvnAgg_model->loadEvnAggList([
				"EvnAgg_pid" => $result[0]["EvnUslugaOnkoSurg_id"],
				"AggWhen_id" => 1
			]);
			if (!empty($aggs[0]["EvnAgg_id"])) {
				foreach ($aggs as $value) {
					if (!empty($value["EvnAgg_id"])) {
						$value["pmUser_id"] = $data["pmUser_id"];
						$this->EvnAgg_model->deleteEvnAgg($value);
					}
				}
			}
			if (!empty($data["AggTypes"])) {
				$compls = $data["AggTypes"];
				if (strpos($compls, ",") > 0) {
					$compls = explode(",", $compls);
				} else {
					$compls = ["0" => $compls];
				}
				foreach ($compls as $value) {
					$params = [
						"EvnAgg_id" => null,
						"EvnAgg_pid" => $result[0]["EvnUslugaOnkoSurg_id"],
						"Lpu_id" => $data["Lpu_id"],
						"Server_id" => $data["Server_id"],
						"PersonEvn_id" => $data["PersonEvn_id"],
						"EvnAgg_setDate" => null,
						"EvnAgg_setTime" => null,
						"AggType_id" => $value,
						"AggWhen_id" => 1,
						"pmUser_id" => $data["pmUser_id"]
					];
					$this->EvnAgg_model->saveEvnAgg($params);
				}
			}
			//послеоперационные
			$aggs2 = $this->EvnAgg_model->loadEvnAggList([
				"EvnAgg_pid" => $result[0]["EvnUslugaOnkoSurg_id"],
				"AggWhen_id" => 2
			]);
			if (!empty($aggs2[0]["EvnAgg_id"])) {
				foreach ($aggs2 as $value) {
					if (!empty($value["EvnAgg_id"])) {
						$value["pmUser_id"] = $data["pmUser_id"];
						$this->EvnAgg_model->deleteEvnAgg($value);
					}
				}
			}
			if (!empty($data["AggTypes2"])) {
				$compls2 = $data["AggTypes2"];
				if (strpos($compls2, ",") > 0) {
					$compls2 = explode(",", $compls2);
				} else {
					$compls2 = ["0" => $compls2];
				}
				foreach ($compls2 as $value) {
					$params = [
						"EvnAgg_id" => null,
						"EvnAgg_pid" => $result[0]["EvnUslugaOnkoSurg_id"],
						"Lpu_id" => $data["Lpu_id"],
						"Server_id" => $data["Server_id"],
						"PersonEvn_id" => $data["PersonEvn_id"],
						"EvnAgg_setDate" => null,
						"EvnAgg_setTime" => null,
						"AggType_id" => $value,
						"AggWhen_id" => 2,
						"pmUser_id" => $data["pmUser_id"]
					];
					$this->EvnAgg_model->saveEvnAgg($params);
				}
			}
			if ($data["EvnUslugaOnkoSurg_id"] == null && !isset($data["isAutoDouble"])) {
				$this->load->model("EvnUsluga_model");
				$euc = $this->EvnUsluga_model->saveEvnUslugaOnkoOper([
					"EvnUsluga_pid" => $data["EvnUslugaOnkoSurg_pid"],
					"Lpu_id" => $data["Lpu_id"],
					"Server_id" => $data["Server_id"],
					"Person_id" => $data["Person_id"],
					"PersonEvn_id" => $data["PersonEvn_id"],
					"EvnUslugaCommon_Kolvo" => 1,
					"EvnUsluga_setDT" => $data["EvnUslugaOnkoSurg_setDT"],
					"EvnUsluga_disDT" => $data["EvnUslugaOnkoSurg_disDT"],
					"PayType_id" => $data["PayType_id"],
					"PayType_SysNickOMS" => getPayTypeSysNickOMS(),
					"UslugaPlace_id" => empty($data["UslugaPlace_id"]) ? 1 : $data["UslugaPlace_id"],
					"Lpu_uid" => $data["Lpu_uid"],
					"UslugaComplex_id" => (!empty($data["UslugaComplex_id"]) ? $data["UslugaComplex_id"] : null),
					"OperType_id" => $data["OperType_id"],
					"session" => $data["session"],
					"pmUser_id" => $data["pmUser_id"]
				]);
				if (is_array($euc) && !empty($euc[0]["EvnUslugaOper_id"])) {
					$result[0]["EvnUslugaOper_id"] = $euc[0]["EvnUslugaOper_id"];
				}
			}
		}
		return $result;
	}

	/**
	 * Удаление
	 *
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	function delete($data)
	{
		$this->load->model("EvnAgg_model");
		$aggs = $this->EvnAgg_model->loadEvnAggList([
			"EvnAgg_pid" => $this->EvnUslugaOnkoSurg_id
		]);
		if (!empty($aggs[0]["EvnAgg_id"])) {
			foreach ($aggs as $value) {
				if (!empty($value["EvnAgg_id"])) {
				    $value['pmUser_id'] = $data['pmUser_id'];
					$this->EvnAgg_model->deleteEvnAgg($value);
				}
			}
		}
		$sql = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_evnuslugaonkosurg_del(evnuslugaonkosurg_id := :EvnUslugaOnkoSurg_id);
		";
		$sqlParams = ["EvnUslugaOnkoSurg_id" => $this->EvnUslugaOnkoSurg_id];
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $sqlParams);
		if (is_object($result)) {
			return $result->result("array");
		} else {
			return false;
		}
	}

	/**
	 * Загрузка
	 * @return array|bool
	 */
	function loadForPrint()
	{
		$sql = "
			select
				to_char(EU.EvnUslugaOnkoSurg_setDT, '{$this->dateTimeForm104}') as \"EvnUslugaOnkoSurg_setDate\",
				rtrim(coalesce(UC.UslugaComplex_Code, '')||' '||coalesce(UC.UslugaComplex_Name, '')) as \"UslugaComplex\",
				OT.OperType_Name as \"OperType_Name\",
				coalesce(AT.AggType_Code, '') as \"AggType_Code\",
				coalesce(AT.AggType_Name, '') as \"AggType_Name\",
				coalesce(ATs.AggType_Code, '') as \"sAggType_Code\",
				coalesce(ATs.AggType_Name, '') as \"sAggType_Name\", 
				rtrim(MP.Person_SurName||' '||coalesce(MP.Person_FirName, '')||' '||coalesce(MP.Person_SecName, '')) as \"MedPersonal_Fio\", 
				OSTT.OnkoSurgTreatType_Name as \"OnkoSurgTreatType_Name\"
			from
				v_EvnUslugaOnkoSurg EU
				inner join v_MorbusOnko MO on EU.Morbus_id = MO.Morbus_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
				left join v_OperType OT on OT.OperType_id = EU.OperType_id
				left join v_MedPersonal MP on MP.MedPersonal_id = EU.MedPersonal_id
				left join v_OnkoSurgTreatType OSTT on OSTT.OnkoSurgTreatType_id = EU.OnkoSurgTreatType_id
				left join v_AggType AT on AT.AggType_id = EU.AggType_id
				left join v_AggType ATs on ATs.AggType_id = EU.AggType_sid
			where EvnUslugaOnkoSurg_id = :EvnUslugaOnkoSurg_id
		";
		/**@var CI_DB_result $result */
		$sqlParams = ["EvnUslugaOnkoSurg_id" => $this->EvnUslugaOnkoSurg_id];
		$result = $this->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение данных по хирургическому лечению в рамках специфики онкологии. Метод для API.
	 *
	 * @param $data
	 * @return array|false
	 */
	function getEvnUslugaOnkoSurgForAPI($data)
	{
		$queryParams = [];
		$filter = "";
		if (!empty($data["MorbusOnko_id"])) {
			$filter .= " and mo.MorbusOnko_id = :MorbusOnko_id";
			$queryParams["MorbusOnko_id"] = $data["MorbusOnko_id"];
		}
		if (empty($filter)) {
			return [];
		}
		$query = "
			select
				mo.MorbusOnko_id as \"MorbusOnko_id\",
				euos.EvnUslugaOnkoSurg_id as \"EvnUslugaOnkoSurg_id\",
				euos.EvnUslugaOnkoSurg_id as \"EvnUsluga_id\",
				to_char(euos.EvnUslugaOnkoSurg_setDT, '$this->dateTimeForm120') as \"Evn_setDT\",
				to_char(euos.EvnUslugaOnkoSurg_disDT, '$this->dateTimeForm120') as \"Evn_disDT\",
				euos.OperType_id as \"OperType_id\",
				euos.OnkoSurgTreatType_id as \"OnkoSurgTreatType_id\",
				euos.OnkoSurgicalType_id as \"OnkoSurgicalType_id\",
				euos.AggType_id as \"AggType_id\",
				euos.AggType_sid as \"AggType_sid\",
				euos.TreatmentConditionsType_id as \"TreatmentConditionsType_id\"
			from
				v_MorbusOnko mo
				inner join v_EvnUslugaOnkoSurg euos on euos.Morbus_id = mo.Morbus_id
			where 1=1 {$filter}
		";
		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Создание данных по хирургическому лечению в рамках специфики онкологии
	 *
	 * @param $data
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	function saveEvnUslugaOnkoSurgAPI($data)
	{
		$query = "
			select 
				mo.MorbusOnko_id as \"MorbusOnko_id\",
				mo.Evn_pid as \"Evn_pid\",
				E.Person_id as \"Person_id\",
				E.PersonEvn_id as \"PersonEvn_id\",
				mo.Morbus_id as \"Morbus_id\"
			from
				v_MorbusOnko mo
				left join v_Evn E on E.Evn_id = mo.Evn_pid
			where mo.MorbusOnko_id = :MorbusOnko_id
		";
		$res = $this->getFirstRowFromQuery($query, $data);
		if (empty($res['Morbus_id']) || empty($res['PersonEvn_id']) || empty($res['Person_id']) || empty($res['Evn_pid'])) {
			throw new Exception("Не найдена специфика онкологии");
		}
		$data["Morbus_id"] = $res["Morbus_id"];
		$data["PersonEvn_id"] = $res["PersonEvn_id"];
		$data["Person_id"] = $res["Person_id"];
		$data["EvnUslugaOnkoSurg_pid"] = $res["Evn_pid"];
		$res = $this->save($data);

		return $res;
	}

	/**
	 * Изменение данных по хирургическому лечению в рамках специфики онкологии
	 *
	 * @param $data
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	function updateEvnUslugaOnkoSurgAPI($data)
	{
		if (empty($data["EvnUslugaOnkoSurg_id"])) {
			return false;
		}
		$this->setId($data["EvnUslugaOnkoSurg_id"]);
		$sql = "
			select
				EvnUslugaOnkoSurg_pid as \"EvnUslugaOnkoSurg_pid\",
				Lpu_id as \"Lpu_id\",
				Server_id as \"Server_id\",
				PersonEvn_id as \"PersonEvn_id\",
				EvnUslugaOnkoSurg_setDT as \"EvnUslugaOnkoSurg_setDT\",
				EvnUslugaOnkoSurg_disDT as \"EvnUslugaOnkoSurg_disDT\",
				Morbus_id as \"Morbus_id\",
				PayType_id as \"PayType_id\",
				UslugaPlace_id as \"UslugaPlace_id\",
				Lpu_uid as \"Lpu_uid\",
				EvnUslugaOnkoSurg_id as \"EvnUslugaOnkoSurg_id\",
				MedPersonal_id as \"MedPersonal_id\",
				AggType_sid as \"AggType_sid\",
				OnkoSurgTreatType_id as \"OnkoSurgTreatType_id\",
				OnkoSurgicalType_id as \"OnkoSurgicalType_id\",
				UslugaComplex_id as \"UslugaComplex_id\",
				OperType_id as \"OperType_id\",
				AggType_id as \"AggType_id\",
				TreatmentConditionsType_id as \"TreatmentConditionsType_id\"
			from v_EvnUslugaOnkoSurg
			where EvnUslugaOnkoSurg_id = :EvnUslugaOnkoSurg_id
			limit 1
		";
		$record = $this->queryResult($sql, $data);
		if (empty($record[0]["EvnUslugaOnkoSurg_id"])) {
			throw new Exception("Данных по лучевому лечению не найдены");
		}
		$params = [
			"EvnUslugaOnkoSurg_pid" => (!empty($data["EvnUslugaOnkoSurg_pid"])) ? $data["EvnUslugaOnkoSurg_pid"] : $record[0]["EvnUslugaOnkoSurg_pid"],
			"Lpu_id" => $record[0]["Lpu_id"],
			"Server_id" => $record[0]["Server_id"],
			"PersonEvn_id" => (!empty($data["PersonEvn_id"])) ? $data["PersonEvn_id"] : $record[0]["PersonEvn_id"],
			"EvnUslugaOnkoSurg_setDT" => (!empty($data["EvnUslugaOnkoSurg_setDT"])) ? $data["EvnUslugaOnkoSurg_setDT"] : $record[0]["EvnUslugaOnkoSurg_setDT"],
			"Morbus_id" => (!empty($data["Morbus_id"])) ? $data["Morbus_id"] : $record[0]["Morbus_id"],
			"PayType_id" => (!empty($data["PayType_id"])) ? $data["PayType_id"] : $record[0]["PayType_id"],
			"UslugaPlace_id" => empty($data["UslugaPlace_id"]) ? $record[0]["UslugaPlace_id"] : $data["UslugaPlace_id"],
			"Lpu_uid" => (!empty($data["Lpu_uid"])) ? $data["Lpu_uid"] : $record[0]["Lpu_uid"],
			"EvnUslugaOnkoSurg_id" => (!empty($data["EvnUslugaOnkoSurg_id"])) ? $data["EvnUslugaOnkoSurg_id"] : $record[0]["EvnUslugaOnkoSurg_id"],
			"MedPersonal_id" => (!empty($data["MedPersonal_id"])) ? $data["MedPersonal_id"] : $record[0]["MedPersonal_id"],
			"AggType_sid" => (!empty($data["AggType_sid"])) ? $data["AggType_sid"] : $record[0]["AggType_sid"],
			"OnkoSurgTreatType_id" => (!empty($data["OnkoSurgTreatType_id"])) ? $data["OnkoSurgTreatType_id"] : $record[0]["OnkoSurgTreatType_id"],
			"OnkoSurgicalType_id" => (!empty($data["OnkoSurgicalType_id"])) ? $data["OnkoSurgicalType_id"] : $record[0]["OnkoSurgicalType_id"],
			"UslugaComplex_id" => (!empty($data["UslugaComplex_id"])) ? $data["UslugaComplex_id"] : $record[0]["UslugaComplex_id"],
			"OperType_id" => (!empty($data["OperType_id"])) ? $data["OperType_id"] : $record[0]["OperType_id"],
			"AggType_id" => (!empty($data["AggType_id"])) ? $data["AggType_id"] : $record[0]["AggType_id"],
			"TreatmentConditionsType_id" => (!empty($data["TreatmentConditionsType_id"])) ? $data["TreatmentConditionsType_id"] : $record[0]["TreatmentConditionsType_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$res = $this->save($params);
		return $res;
	}
}
