<?php
defined("BASEPATH") or die("No direct script access allowed");
require_once("EvnPrescrAbstract_model.php");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		PromedWeb
 * @access		public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		09.2013
 * 
 * Модель назначения "Режим"
 *
 * Назначения с типом "Режим" хранятся в таблицах EvnPrescr, EvnPrescrRegime
 * В EvnPrescr хранится само назначение, а в EvnPrescrRegime - календарь назначения и тип режима, признак выполнения
 *
 * @package		EvnPrescr
 * @author		Александр Пермяков
 */
class EvnPrescrRegime_model extends EvnPrescrAbstract_model
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	public $dateTimeForm120 = "YYYY-MM-DD HH24:MI:SS";

	public function __construct()
	{
		parent::__construct();
	}

	public $EvnPrescr_id = null;

	/**
	 * Определение идентификатора типа назначения
	 * @return int
	 */
	public function getPrescriptionTypeId()
	{
		return 1;
	}

	/**
	 * Определение имени таблицы с данными назначения
	 * @return string
	 */
	public function getTableName()
	{
		return "EvnPrescrRegime";
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $scenario
	 * @return array
	 */
	public function getInputRules($scenario)
	{
		$rules = [];
		switch ($scenario) {
			case "doSave":
				$rules = [
					["field" => "EvnPrescr_id", "label" => "Идентификатор назначения", "rules" => "", "type" => "id"],
					["field" => "EvnPrescr_pid", "label" => "Идентификатор родительского события", "rules" => "", "type" => "id"],
					["field" => "EvnPrescr_setDate", "label" => "Начать", "rules" => "", "type" => "date"],
					["field" => "EvnPrescr_dayNum", "label" => "Продолжать", "rules" => "", "type" => "int"],
					["field" => "PrescriptionRegimeType_id", "label" => "Тип режима", "rules" => "", "type" => "id"],
					["field" => "EvnPrescr_Descr", "label" => "Комментарий", "rules" => "trim", "type" => "string"],
					["field" => "PersonEvn_id", "label" => "Идентификатор состояния человека", "rules" => "", "type" => "id"],
					["field" => "Server_id", "label" => "Идентификатор сервера", "rules" => "", "type" => "int"],
					["field" => "accessType", "label" => "Режим", "rules" => "", "type" => "string"],
				];
				break;
			case "doLoad":
				$rules[] = [
					"field" => "EvnPrescr_id",
					"label" => "Идентификатор назначения",
					"rules" => "required",
					"type" => "id"
				];
				break;
		}
		return $rules;
	}

	/**
	 * @param $EvnPrescr_id
	 * @param $pmUser_id
	 * @param null $filter
	 * @return array
	 * @throws Exception
	 */
	function updatePrescrRegime($EvnPrescr_id, $pmUser_id, $filter = null)
	{
		if ($filter == null) {
			$filter = "";
		}
		$query = "
			select
				EvnPrescrRegime_id as \"EvnPrescrRegime_id\",
				Lpu_id as \"Lpu_id\",
				Server_id as \"Server_id\",
				PersonEvn_id as \"PersonEvn_id\",
				PrescriptionType_id as \"PrescriptionType_id\",
				PrescriptionRegimeType_id as \"PrescriptionRegimeType_id\",
				PrescriptionStatusType_id as \"PrescriptionStatusType_id\",
				to_char(EvnPrescrRegime_setDT, '{$this->dateTimeForm120}') as \"EvnPrescr_setDate\"
			from v_EvnPrescrRegime
			where EvnPrescrRegime_pid = :EvnPrescr_id
			{$filter}
			order by EvnPrescrRegime_setDT
		";
		$queryParams = ["EvnPrescr_id" => $EvnPrescr_id];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при запросе данных календаря");
		}
		$idList = [];
		$index = 1;
		$response = $result->result("array");
		foreach ($response as $val) {
			$query = "
				select
					evnprescrregime_id as \"EvnPrescrRegime_id\",
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_evnprescrregime_upd(
				    evnprescrregime_id := :EvnPrescrRegime_id,
				    evnprescrregime_pid := :EvnPrescr_id,
				    lpu_id := :Lpu_id,
				    server_id := :Server_id,
				    personevn_id := :PersonEvn_id,
				    evnprescrregime_setdt := :EvnPrescrRegime_setDT,
				    prescriptiontype_id := :PrescriptionType_id,
				    evnprescrregime_iscito := 1,
				    prescriptionstatustype_id := :PrescriptionStatusType_id,
				    prescriptionregimetype_id := :PrescriptionRegimeType_id,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"EvnPrescr_id" => $this->EvnPrescr_id,
				"EvnPrescrRegime_setDT" => $val["EvnPrescr_setDate"],
				"EvnPrescrRegime_id" => $val["EvnPrescrRegime_id"],
				"EvnPrescrRegime_Count" => count($response),
				"Lpu_id" => $val["Lpu_id"],
				"EvnPrescrRegime_Index" => $index,
				"PrescriptionStatusType_id" => $val["PrescriptionStatusType_id"],
				"PrescriptionRegimeType_id" => $val["PrescriptionRegimeType_id"],
				"PrescriptionType_id" => $val["PrescriptionType_id"],
				"PersonEvn_id" => $val["PersonEvn_id"],
				"Server_id" => $val["Server_id"],
				"pmUser_id" => $pmUser_id
			];
			$result = $this->db->query($query, $queryParams);
			if (!is_object($result)) {
				throw new Exception("Ошибка при запросе к БД при сохранении календаря", 500);
			}
			$response = $result->result("array");
			if (!empty($response[0]["Error_Msg"])) {
				throw new Exception($response[0]["Error_Msg"], 500);
			}
			$idList[] = $response[0]["EvnPrescrRegime_id"];
			$index++;
		}
		if ($this->EvnPrescr_id != $EvnPrescr_id) {
			$this->getCntRegime($EvnPrescr_id, $pmUser_id);
		}
		return $idList;
	}

	/**
	 * Контроль пересечения дат
	 * @param $data
	 * @param $dateList
	 * @return array
	 * @throws Exception
	 */
	protected function _hasCrossingDates($data, $dateList)
	{
		$lastIndex = count($dateList) - 1;
		$queryParams = [
			"EvnPrescr_pid" => $data["EvnPrescr_pid"],
			"PrescriptionType_id" => $this->getPrescriptionTypeId(),
			"beg_date" => $dateList[0],
			"end_date" => $dateList[$lastIndex],
		];
		$add_where = "";
		$query = "
			select
				EP.EvnPrescr_id as \"EvnPrescr_id\",
				Regime.EvnPrescrRegime_id as \"EvnPrescrRegime_id\",
				PrescriptionRegimeType_id as \"PrescriptionRegimeType_id\",
				Regime.EvnPrescrRegime_setDT as \"EvnPrescrRegime_setDT\",
				EP.PrescriptionStatusType_id as \"PrescriptionStatusType_id\",
				case when CAST(Regime.EvnPrescrRegime_setDT as date) between CAST(:beg_date as date) and CAST(:end_date as date) then 'in' else 'out' end as \"interval\"
			from
				v_EvnPrescr EP
				inner join v_EvnPrescrRegime Regime on Regime.EvnPrescrRegime_pid = EP.EvnPrescr_id
			where EP.EvnPrescr_pid = :EvnPrescr_pid
			  and EP.PrescriptionType_id = :PrescriptionType_id
			  and EP.PrescriptionStatusType_id != 3
			  and CAST(Regime.EvnPrescrRegime_setDT as date) between CAST(:beg_date as date) - CAST(1||' day' as interval)  and CAST(:end_date as date) + CAST(1||' day' as interval)
				{$add_where}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (Контроль пересечения дат)", 500);
		}
		$response = $result->result("array");
		$Regime = [];
		$cnt = 0;
		$arr = array();
		if (!empty($data["EvnPrescr_id"])) {
			$this->EvnPrescr_id = $data["EvnPrescr_id"];
		}
		foreach ($response as $day) {
			if ($day["interval"] == "out") {
				if ($data["PrescriptionRegimeType_id"] == $day["PrescriptionRegimeType_id"]) {
					if ($this->EvnPrescr_id == null || $day["EvnPrescr_id"] == $this->EvnPrescr_id) {
						$this->EvnPrescr_id = $day["EvnPrescr_id"];
					} else {
						$this->updatePrescrRegime($day["EvnPrescr_id"], $data["pmUser_id"]);

					}
				} else {
					$cnt++;
					if ($cnt == 2) {
						$this->cutPrescr($day["EvnPrescr_id"], $day["EvnPrescrRegime_setDT"], $data["pmUser_id"]);
						if (!empty($data["EvnPrescr_id"])) {
							$this->EvnPrescr_id = $data["EvnPrescr_id"];
						} else {
							$this->EvnPrescr_id = null;
						}
					}
				}
			}
			if (!in_array($day["EvnPrescrRegime_id"], $Regime) && $day["interval"] == "in") {
				$Regime[] = $day["EvnPrescrRegime_id"];
				$arr[$day["EvnPrescrRegime_id"]]["EvnPrescrRegime_id"] = $day["EvnPrescrRegime_id"];
				$arr[$day["EvnPrescrRegime_id"]]["EvnPrescrRegime_setDT"] = $day["EvnPrescrRegime_setDT"];
				$arr[$day["EvnPrescrRegime_id"]]["EvnPrescr_id"] = $day["EvnPrescr_id"];
			}
		}
		return $arr;
	}

	/**
	 * @param $EvnPrescr_id
	 * @param $date
	 * @param $pmUser_id
	 * @throws Exception
	 */
	function cutPrescr($EvnPrescr_id, $date, $pmUser_id)
	{
		$query = "
			select
				EvnPrescr_pid as \"EvnPrescr_pid\",
				Lpu_id as \"Lpu_id\",
				Server_id as \"Server_id\",
				PersonEvn_id as \"PersonEvn_id\",
				PrescriptionType_id as \"PrescriptionType_id\",
				EvnPrescr_Descr as \"EvnPrescr_Descr\",
				PrescriptionStatusType_id as \"PrescriptionStatusType_id\",
				EvnPrescr_IsCito as \"EvnPrescr_IsCito\"
			from v_EvnPrescr
			where EvnPrescr_id = :EvnPrescr_id
		";
		$queryParams = ["EvnPrescr_id" => $EvnPrescr_id];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при запросе данных календаря");
		}
		$response = $result->result("array");
		$data = [
			"EvnPrescr_pid" => $response[0]["EvnPrescr_pid"],
			"Lpu_id" => $response[0]["Lpu_id"],
			"Server_id" => $response[0]["Server_id"],
			"PersonEvn_id" => $response[0]["PersonEvn_id"],
			"PrescriptionType_id" => $response[0]["PrescriptionType_id"],
			"PrescriptionStatusType_id" => $response[0]["PrescriptionStatusType_id"],
			"EvnPrescr_Descr" => $response[0]["EvnPrescr_Descr"],
			"EvnPrescr_IsCito" => $response[0]["EvnPrescr_IsCito"],
			"pmUser_id" => $pmUser_id
		];
		$this->EvnPrescr_id = $this->_save($data);
		/**@var DateTime $date */
		$filter = " and EvnPrescrRegime_setDT::date >= '" . ConvertDateFormat($date, "Y-m-d") . "'::date";
		$this->updatePrescrRegime($EvnPrescr_id, $pmUser_id, $filter);
	}

	/**
	 * Сохранение календаря в EvnPrescrRegime
	 * @param $data
	 * @param $dateList
	 * @return array
	 * @throws Exception
	 */
	protected function _saveCalendar($data, $dateList)
	{
		$query = "
			select
				evnprescrregime_id as \"EvnPrescrRegime_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_evnprescrregime_ins(
			    evnprescrregime_id := null,
			    evnprescrregime_pid := :EvnPrescr_id,
			    lpu_id := :Lpu_id,
			    server_id := :Server_id,
			    personevn_id := :PersonEvn_id,
			    evnprescrregime_setdt := :EvnPrescrRegime_setDT,
			    prescriptiontype_id := :PrescriptionType_id,
			    evnprescrregime_iscito := 1,
			    prescriptionstatustype_id := :PrescriptionStatusType_id,
			    prescriptionregimetype_id := :PrescriptionRegimeType_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"EvnPrescr_id" => $data["EvnPrescr_id"],
			"Lpu_id" => $data["Lpu_id"],
			"Server_id" => $data["Server_id"],
			"PersonEvn_id" => $data["PersonEvn_id"],
			"PrescriptionType_id" => $this->getPrescriptionTypeId(),
			"PrescriptionRegimeType_id" => $data["PrescriptionRegimeType_id"],
			"PrescriptionStatusType_id" => 1,
			"pmUser_id" => $data["pmUser_id"],
		];
		$idList = [];
		foreach ($dateList as $addDate) {
			$queryParams["EvnPrescrRegime_setDT"] = $addDate;
			$result = $this->db->query($query, $queryParams);
			if (!is_object($result)) {
				throw new Exception("Ошибка при запросе к БД при сохранении календаря", 500);
			}
			$response = $result->result("array");
			if (!empty($response[0]["Error_Msg"])) {
				throw new Exception($response[0]["Error_Msg"], 500);
			}
			$idList[] = $response[0]["EvnPrescrRegime_id"];
		}
		return $idList;
	}

	/**
	 * @param $EvnPrescr_id
	 * @param $pmUser
	 * @param $dateList
	 * @param bool $all
	 * @throws Exception
	 */
	public function Clear($EvnPrescr_id, $pmUser, $dateList, $all = false)
	{
		$query = "
			select
				EvnPrescrRegime_id as \"EvnPrescrRegime_id\",
				PrescriptionRegimeType_id as \"PrescriptionRegimeType_id\",
				to_char(EvnPrescrRegime_setDT, '{$this->dateTimeForm120}') as \"EvnPrescr_setDate\"
			from v_EvnPrescrRegime
			where EvnPrescrRegime_pid = :EvnPrescr_id
			order by EvnPrescrRegime_setDT
		";
		$queryParams = ["EvnPrescr_id" => $EvnPrescr_id];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при запросе данных календаря");
		}
		$response = $result->result("array");
		if ($all) {
			$query = "
				select
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_evnprescrregime_del(
				    evnprescrregime_id := :EvnPrescrRegime_id,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [];
			$queryParams["pmUser_id"] = $pmUser;
			$queryParams["EvnPrescrRegime_id"] = $EvnPrescr_id;
			$result = $this->db->query($query, $queryParams);
			if (!is_object($result)) {
				throw new Exception("Не удалось очистить календарь!");
			}
		} else {
			// Иначе заменяются новым только те дни назначения, которые попадают во введенный временной отрезок.
			foreach ($response as $row) {
				if (!in_array($row["EvnPrescr_setDate"], $dateList)) {
					$this->_destroy([
						"object" => "EvnPrescrRegime",
						"id" => $row["EvnPrescrRegime_id"],
						"pmUser_id" => $pmUser,
					]);
				}
			}
		}
	}

	/**
	 * @param $EvnPrescr_id
	 * @param $pmUser
	 * @throws Exception
	 */
	public function getCntRegime($EvnPrescr_id, $pmUser)
	{
		$query = "
			select count(1) as \"cnt\"
			from
				v_EvnPrescr EP
				inner join v_EvnPrescrRegime Regime on Regime.EvnPrescrRegime_pid = EP.EvnPrescr_id
			where EP.PrescriptionType_id = 1
			  and EP.EvnPrescr_id = :EvnPrescr_id
		";
		$queryParams["EvnPrescr_id"] = $EvnPrescr_id;
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (Контроль пересечения дат)", 500);
		}
		$response = $result->result("array");
		if ($response[0]["cnt"] == 0) {
			$this->Clear($EvnPrescr_id, $pmUser, 0, true);
		}
	}

	/**
	 * Сохранение назначения
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return array
	 * @throws Exception
	 */
	public function doSave($data = [], $isAllowTransaction = true)
	{
		// Стартуем транзакцию
		$this->beginTransaction();
		try {
			if (empty($data["EvnPrescr_pid"])) {
				throw new Exception("Не указан Идентификатор родительского события", 400);
			}
			if (empty($data["PrescriptionRegimeType_id"])) {
				throw new Exception("Не указан Тип режима", 400);
			}
			if (empty($data["PersonEvn_id"])) {
				throw new Exception("Не указан Идентификатор состояния человека", 400);
			}
			if (!isset($data["Server_id"])) {
				throw new Exception("Не указан Идентификатор сервера", 400);
			}
			$dateList = $this->_createDateList($data);
			$action = (empty($data["EvnPrescr_id"]) ? "add" : "edit");
			if ($action == "edit" && (isset($data["accessType"]) && $data["accessType"] == "edit")) {
				$this->Clear($data["EvnPrescr_id"], $data["pmUser_id"], $dateList);
			}
			$cross = $this->_hasCrossingDates($data, $dateList);
			if ($this->EvnPrescr_id != null && empty($data["EvnPrescr_id"])) {
				$data["EvnPrescr_id"] = $this->EvnPrescr_id;
			}
			if($action == 'add' && !empty($cross)){
				throw new Exception('Ошибка при добавлении нового режима.
				В случае уже добавлен аналогичный режим в том же диапазоне дат.
				Измените параметры или дату начала режима',400);
			}
			// контроль пересечения дат
			if (!empty($data["EvnPrescr_id"])) {
				foreach ($cross as $row) {
					$this->_destroy([
						"object" => "EvnPrescrRegime",
						"id" => $row["EvnPrescrRegime_id"],
						"pmUser_id" => $data["pmUser_id"],
					]);
					if ($row["EvnPrescr_id"] != $data["EvnPrescr_id"]) {
						$this->getCntRegime($row["EvnPrescr_id"], $data["pmUser_id"]);
					}
				}
			}
			$data["EvnPrescr_id"] = $this->_save($data);
			$this->EvnPrescr_id = $data["EvnPrescr_id"];
			$this->_saveCalendar($data, $dateList);
		} catch (Exception $e) {
			$this->rollbackTransaction();
			throw new Exception($e->getMessage(), $e->getCode());
		}
		$this->commitTransaction();
		$idList = $this->updatePrescrRegime($data["EvnPrescr_id"], $data["pmUser_id"]);
		return [[
			"EvnPrescr_id" => $data["EvnPrescr_id"],
			"EvnPrescrRegime_id_list" => $idList,
			"Error_Msg" => null,
			"Error_Code" => null,
		]];
	}

	/**
	 * Получение данных для формы редактирования
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array|boolean
	 */
	public function doLoad($data)
	{
		$query = "
			select
				case when coalesce(EP.PrescriptionStatusType_id, 1) = 1 then 'edit' else 'view' end as \"accessType\",
				EP.EvnPrescr_id as \"EvnPrescr_id\",
				EP.EvnPrescr_pid as \"EvnPrescr_pid\",
				EPP.PrescriptionRegimeType_id as \"PrescriptionRegimeType_id\",
				to_char(EPP.EvnPrescrRegime_setDT, '{$this->dateTimeForm104}') as \"EvnPrescr_setDate\",
				EP.EvnPrescr_Descr as \"EvnPrescr_Descr\",
				EP.PersonEvn_id as \"PersonEvn_id\",
				EP.Server_id as \"Server_id\"
			from 
				v_EvnPrescr EP
				inner join v_EvnPrescrRegime EPP on EPP.EvnPrescrRegime_pid = EP.EvnPrescr_id
			where EP.EvnPrescr_id = :EvnPrescr_id
			order by EPP.EvnPrescrRegime_setDT
		";
		$queryParams = ["EvnPrescr_id" => $data["EvnPrescr_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$tmp_arr = $result->result("array");
		if (count($tmp_arr) > 0) {
			$response = [];
			$dateList = [];
		} else {
			return $tmp_arr;
		}
		foreach ($tmp_arr as $row) {
			$dateList[] = $row["EvnPrescr_setDate"];
		}
		$response[0] = $tmp_arr[0];
		$response[0]["EvnPrescr_dayNum"] = count($dateList);
		return $response;
	}

	/**
	 * Получение данных для панели просмотра ЭМК и правой панели формы добавления назначений
	 * @param $section
	 * @param $evn_pid
	 * @param $sessionParams
	 * @return array|bool
	 */
	public function doLoadViewData($section, $evn_pid, $sessionParams)
	{
		$sysnick = swPrescription::getParentEvnClassSysNickBySectionName($section);
		$addJoin = "";
		if ($sysnick) {
			$accessType = "
				case when {$sysnick}.Lpu_id = :Lpu_id AND coalesce({$sysnick}.{$sysnick}_IsSigned, 1) = 1 then 'edit' else 'view' end as \"accessType\"
			";
			$addJoin = "
				left join v_{$sysnick} {$sysnick} on {$sysnick}.{$sysnick}_id = EP.EvnPrescr_pid
			";
		} else {
			$accessType = "
				'view' as \"accessType\"
			";
		}
		$query = "
			select
				{$accessType},
				EP.EvnPrescr_id as \"EvnPrescr_id\",
			    EP.EvnPrescr_pid as \"EvnPrescr_pid\",
			    EP.EvnPrescr_rid as \"EvnPrescr_rid\",
			    to_char(Regime.EvnPrescrRegime_setDT, '{$this->dateTimeForm104}') as \"EvnPrescr_setDate\",
			    Regime.EvnPrescrRegime_IsExec as \"EvnPrescr_IsExec\",
			    Regime.PrescriptionStatusType_id as \"PrescriptionStatusType_id\",
			    EP.PrescriptionType_id as \"PrescriptionType_id\",
			    EP.PrescriptionType_id as \"PrescriptionType_Code\",
			    coalesce(EP.EvnPrescr_IsCito, 1) as \"EvnPrescr_IsCito\",
			    Regime.EvnPrescrRegime_Descr as \"EvnPrescr_Descr\",
				EP.EvnPrescr_Descr as \"EvnPrescr_MainDescr\",
				Regime.EvnPrescrRegime_id as \"EvnPrescrRegime_id\",
				Regime.EvnPrescrRegime_Count as \"EvnPrescr_dayNum\",
				coalesce(PRT.PrescriptionRegimeType_id, 0) as \"PrescriptionRegimeType_id\",--тип режима
				coalesce(PRT.PrescriptionRegimeType_Code, 0) as \"PrescriptionRegimeType_Code\",
				coalesce(PRT.PrescriptionRegimeType_Name, '') as \"PrescriptionRegimeType_Name\"
			from v_EvnPrescr EP
				inner join v_EvnPrescrRegime Regime on Regime.EvnPrescrRegime_pid = EP.EvnPrescr_id
				left join PrescriptionRegimeType PRT on PRT.PrescriptionRegimeType_id = Regime.PrescriptionRegimeType_id
				{$addJoin}
			where EP.EvnPrescr_pid  = :EvnPrescr_pid
			  and EP.PrescriptionType_id = 1
			  and Regime.PrescriptionStatusType_id != 3
			order by
				EP.EvnPrescr_id,
				Regime.EvnPrescrRegime_setDT
		";
		$queryParams = [
			"EvnPrescr_pid" => $evn_pid,
			"Lpu_id" => $sessionParams["lpu_id"],
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$tmp_arr = $result->result("array");
		$response = [];
		$last_ep = null;
		$is_exe = null;
		$is_sign = null;
		$first_index = 0;
		foreach ($tmp_arr as $i => $row) {
			if ($last_ep != $row["EvnPrescr_id"]) {
				//это первая итерация с другим назначением
				$first_index = $i;
				$last_ep = $row["EvnPrescr_id"];
				$is_exe = false;
				$is_sign = false;
			}
			if ($is_exe == false) $is_exe = ($row["EvnPrescr_IsExec"] == 2);
			if ($is_sign == false) $is_sign = ($row["PrescriptionStatusType_id"] == 2);
			if (empty($tmp_arr[$i + 1]) || $last_ep != $tmp_arr[$i + 1]["EvnPrescr_id"]) {
				if ($is_exe) $row["EvnPrescr_IsExec"] = 2;
				if ($is_sign) $row["PrescriptionStatusType_id"] = 2;
				if (!empty($section) && $section === "api") {
					$row["EvnPrescr_setDate"] = $tmp_arr[$first_index]["EvnPrescr_setDate"];
				} else {
					$row["EvnPrescr_setDate"] = $tmp_arr[$first_index]["EvnPrescr_setDate"] . "&nbsp;—&nbsp;" . $row["EvnPrescr_setDate"];
				}
				$row[$section . "_id"] = $row["EvnPrescr_id"] . "-" . $row["EvnPrescrRegime_id"];
				if ($section === "api" && empty($row["EvnPrescr_Descr"]) && !empty($row["EvnPrescr_MainDescr"])) {
					$row["EvnPrescr_Descr"] = $row["EvnPrescr_MainDescr"];
					unset($row["EvnPrescr_MainDescr"]);
				}
				$response[] = $row;
			}
		}
		return $response;
	}
}