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
 * Модель назначения "Диета"
 *
 * Назначения с типом "Диета" хранятся в таблицах EvnPrescr, EvnPrescrDiet
 * В EvnPrescr хранится само назначение, а в EvnPrescrDiet - календарь назначения и тип диеты, признак выполнения
 *
 * @package		EvnPrescr
 * @author		Александр Пермяков
 *
 * @property CI_DB_driver $db
 */
class EvnPrescrDiet_model extends EvnPrescrAbstract_model
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
		return 2;
	}

	/**
	 * Определение имени таблицы с данными назначения
	 * @return string
	 */
	public function getTableName()
	{
		return "EvnPrescrDiet";
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
					["field" => "PrescriptionDietType_id", "label" => "Тип диеты", "rules" => "", "type" => "id"],
					["field" => "EvnPrescr_Descr", "label" => "Комментарий", "rules" => "trim", "type" => "string"],
					["field" => "PersonEvn_id", "label" => "Идентификатор состояния человека", "rules" => "", "type" => "id"],
					["field" => "Server_id", "label" => "Идентификатор сервера", "rules" => "", "type" => "int"],
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
	function updatePrescrDiet($EvnPrescr_id, $pmUser_id, $filter = null)
	{
		if ($filter == null) {
			$filter = "";
		}
		$query = "
			select
				EvnPrescrDiet_id as \"EvnPrescrDiet_id\",
				Lpu_id as \"Lpu_id\",
				Server_id as \"Server_id\",
				PersonEvn_id as \"PersonEvn_id\",
				PrescriptionType_id as \"PrescriptionType_id\",
				PrescriptionDietType_id as \"PrescriptionDietType_id\",
				PrescriptionStatusType_id as \"PrescriptionStatusType_id\",
				to_char(EvnPrescrDiet_setDT, '{$this->dateTimeForm120}') as \"EvnPrescr_setDate\"
			from v_EvnPrescrDiet
			where EvnPrescrDiet_pid = :EvnPrescr_id
			{$filter}
			order by EvnPrescrDiet_setDT
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
					evnprescrdiet_id as \"EvnPrescrDiet_id\",
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_evnprescrdiet_upd(
				    evnprescrdiet_id := :EvnPrescrDiet_id,
				    evnprescrdiet_pid := :EvnPrescr_id,
				    lpu_id := :Lpu_id,
				    server_id := :Server_id,
				    personevn_id := :PersonEvn_id,
				    evnprescrdiet_setdt := :EvnPrescrDiet_setDT,
				    prescriptiontype_id := :PrescriptionType_id,
				    evnprescrdiet_iscito := 1,
				    prescriptionstatustype_id := :PrescriptionStatusType_id,
				    prescriptiondiettype_id := :PrescriptionDietType_id,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"EvnPrescr_id" => $this->EvnPrescr_id,
				"EvnPrescrDiet_setDT" => $val["EvnPrescr_setDate"],
				"EvnPrescrDiet_id" => $val["EvnPrescrDiet_id"],
				"EvnPrescrDiet_Count" => count($response),
				"Lpu_id" => $val["Lpu_id"],
				"EvnPrescrDiet_Index" => $index,
				"PrescriptionStatusType_id" => $val["PrescriptionStatusType_id"],
				"PrescriptionDietType_id" => $val["PrescriptionDietType_id"],
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
			$idList[] = $response[0]["EvnPrescrDiet_id"];
			$index++;
		}
		if ($this->EvnPrescr_id != $EvnPrescr_id) {
			$this->getCntDiet($EvnPrescr_id, $pmUser_id);
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
		$query = "
			select
				EP.EvnPrescr_id as \"EvnPrescr_id\",
				Diet.EvnPrescrDiet_id as \"EvnPrescrDiet_id\",
				PrescriptionDietType_id as \"PrescriptionDietType_id\",
				Diet.EvnPrescrDiet_setDT as \"EvnPrescrDiet_setDT\",
				EP.PrescriptionStatusType_id as \"PrescriptionStatusType_id\",
				case when CAST(Diet.EvnPrescrDiet_setDT as date) between CAST(:beg_date as date) and CAST(:end_date as date) then 'in' else 'out' end as \"interval\"
			from
				v_EvnPrescr EP
				inner join v_EvnPrescrDiet Diet on Diet.EvnPrescrDiet_pid = EP.EvnPrescr_id
			where EP.EvnPrescr_pid = :EvnPrescr_pid
			  and EP.PrescriptionType_id = :PrescriptionType_id
			  and EP.PrescriptionStatusType_id != 3
			  and CAST(Diet.EvnPrescrDiet_setDT as date) between CAST(:beg_date as date) - CAST(1||' days' as interval) and CAST(:end_date as date) + CAST(1||' days' as interval)
		";
		$result = $this->db->query($query, $queryParams);
		/**@var CI_DB_result $result */
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (Контроль пересечения дат)", 500);
		}
		$response = $result->result("array");

		$Diet = [];
		$cnt = 0;
		$arr = [];
		if (!empty($data["EvnPrescr_id"])) {
			$this->EvnPrescr_id = $data["EvnPrescr_id"];
		}
		foreach ($response as $day) {
			if ($day["interval"] == "out") {
				if ($data["PrescriptionDietType_id"] == $day["PrescriptionDietType_id"]) {
					if ($this->EvnPrescr_id == null || $day["EvnPrescr_id"] == $this->EvnPrescr_id) {
						$this->EvnPrescr_id = $day["EvnPrescr_id"];
					} else {
						$this->updatePrescrDiet($day["EvnPrescr_id"], $data["pmUser_id"]);
					}
				} else {
					$cnt++;
					if ($cnt == 2) {
						$this->cutPrescr($day["EvnPrescr_id"], $day["EvnPrescrDiet_setDT"], $data["pmUser_id"]);
						if (!empty($data["EvnPrescr_id"])) {
							$this->EvnPrescr_id = $data["EvnPrescr_id"];
						} else {
							$this->EvnPrescr_id = null;
						}
					}
				}
			}
			if (!in_array($day["EvnPrescrDiet_id"], $Diet) && $day["interval"] == "in") {
				$Diet[] = $day["EvnPrescrDiet_id"];
				$arr[$day["EvnPrescrDiet_id"]]["EvnPrescrDiet_id"] = $day["EvnPrescrDiet_id"];
				$arr[$day["EvnPrescrDiet_id"]]["EvnPrescrDiet_setDT"] = $day["EvnPrescrDiet_setDT"];
				$arr[$day["EvnPrescrDiet_id"]]["EvnPrescr_id"] = $day["EvnPrescr_id"];
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
		$response = $result->result('array');
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
		$filter = " and EvnPrescrDiet_setDT::date >= '" . ConvertDateFormat($date, 'Y-m-d') . "'::date";
		$this->updatePrescrDiet($EvnPrescr_id, $pmUser_id, $filter);
	}

	/**
	 * Сохранение календаря в EvnPrescrDiet
	 * @param $data
	 * @param $dateList
	 * @return array
	 * @throws Exception
	 */
	protected function _saveCalendar($data, $dateList)
	{
		$query = "
			select
				evnprescrdiet_id as \"EvnPrescrDiet_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_evnprescrdiet_ins(
			    evnprescrdiet_id := null,
			    evnprescrdiet_pid := :EvnPrescr_id,
			    lpu_id := :Lpu_id,
			    server_id := :Server_id,
			    personevn_id := :PersonEvn_id,
			    evnprescrdiet_setdt := :EvnPrescrDiet_setDT,
			    prescriptiontype_id := :PrescriptionType_id,
			    evnprescrdiet_iscito := 1,
			    prescriptionstatustype_id := :PrescriptionStatusType_id,
			    prescriptiondiettype_id := :PrescriptionDietType_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"EvnPrescr_id" => $data["EvnPrescr_id"],
			"Lpu_id" => $data["Lpu_id"],
			"Server_id" => $data["Server_id"],
			"PersonEvn_id" => $data["PersonEvn_id"],
			"PrescriptionType_id" => $this->getPrescriptionTypeId(),
			"PrescriptionDietType_id" => $data["PrescriptionDietType_id"],
			"PrescriptionStatusType_id" => 1,
			"pmUser_id" => $data["pmUser_id"],
		];
		$idList = [];
		foreach ($dateList as $addDate) {
			$queryParams["EvnPrescrDiet_setDT"] = $addDate;
			$result = $this->db->query($query, $queryParams);
			if (!is_object($result)) {
				throw new Exception("Ошибка при запросе к БД при сохранении календаря", 500);
			}
			$response = $result->result("array");
			if (!empty($response[0]["Error_Msg"])) {
				throw new Exception($response[0]["Error_Msg"], 500);
			}
			$idList[] = $response[0]["EvnPrescrDiet_id"];
		}
		return $idList;
	}

	/**
	 * @param $EvnPrescr_id
	 * @param $pmUser
	 * @throws Exception
	 */
	public function getCntDiet($EvnPrescr_id, $pmUser)
	{
		$query = "
			select count(1) as \"cnt\"
			from
				v_EvnPrescr EP
				inner join v_EvnPrescrDiet Diet on Diet.EvnPrescrDiet_pid = EP.EvnPrescr_id
			where EP.PrescriptionType_id = 2
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
				EvnPrescrDiet_id as \"EvnPrescrDiet_id\",
				PrescriptionDietType_id as \"PrescriptionDietType_id\",
				to_char(EvnPrescrDiet_setDT, '{$this->dateTimeForm120}') as \"EvnPrescr_setDate\"
			from v_EvnPrescrDiet
			where EvnPrescrDiet_pid = :EvnPrescr_id
			order by EvnPrescrDiet_setDT
		";
		$queryParams = ['EvnPrescr_id' => $EvnPrescr_id];
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
				from p_evnprescrdiet_del(
				    evnprescrdiet_id := :EvnPrescrDiet_id,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [];
			$queryParams["pmUser_id"] = $pmUser;
			$queryParams["EvnPrescrDiet_id"] = $EvnPrescr_id;
			$result = $this->db->query($query, $queryParams);
			if (!is_object($result)) {
				throw new Exception("Не удалось очистить календарь!");
			}
		} else {
			// Иначе заменяются новым только те дни назначения, которые попадают во введенный временной отрезок.
			foreach ($response as $row) {
				if (!in_array($row["EvnPrescr_setDate"], $dateList)) {
					$this->_destroy([
						"object" => "EvnPrescrDiet",
						"id" => $row["EvnPrescrDiet_id"],
						"pmUser_id" => $pmUser,
					]);
				}
			}
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
			if (empty($data["PrescriptionDietType_id"])) {
				throw new Exception("Не указан Тип диеты", 400);
			}
			if (empty($data["PersonEvn_id"])) {
				throw new Exception("Не указан Идентификатор состояния человека", 400);
			}
			if (!isset($data["Server_id"])) {
				throw new Exception("Не указан Идентификатор сервера", 400);
			}
			$dateList = $this->_createDateList($data);

			$action = (empty($data["EvnPrescr_id"]) ? "add" : "edit");
			if ($action == "edit") {
				$this->Clear($data["EvnPrescr_id"], $data["pmUser_id"], $dateList);
			}
			$cross = $this->_hasCrossingDates($data, $dateList);

			if ($this->EvnPrescr_id != null && empty($data["EvnPrescr_id"])) {
				$data["EvnPrescr_id"] = $this->EvnPrescr_id;
			}
			if($action == 'add' && !empty($cross)){
				throw new Exception('Ошибка при добавлении новой диеты.
				В случае уже добавлена аналогичная диета в том же диапазоне дат.
				Измените параметры или дату начала диеты',400);
			}
			// контроль пересечения дат
			if (isset($data["EvnPrescr_id"])) {
				foreach ($cross as $row) {
					$this->_destroy([
						"object" => "EvnPrescrDiet",
						"id" => $row["EvnPrescrDiet_id"],
						"pmUser_id" => $data["pmUser_id"],
					]);
					if ($row["EvnPrescr_id"] != $data["EvnPrescr_id"]) {
						$this->getCntDiet($row["EvnPrescr_id"], $data["pmUser_id"]);
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
		$idList = $this->updatePrescrDiet($data["EvnPrescr_id"], $data["pmUser_id"]);
		return [[
			"EvnPrescr_id" => $data["EvnPrescr_id"],
			"EvnPrescrDiet_id_list" => $idList,
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
				EPP.PrescriptionDietType_id as \"PrescriptionDietType_id\",
				to_char(EPP.EvnPrescrDiet_setDT, '{$this->dateTimeForm104}') as \"EvnPrescr_setDate\",
				EP.EvnPrescr_Descr as \"EvnPrescr_Descr\",
				EP.PersonEvn_id as \"PersonEvn_id\",
				EP.Server_id as \"Server_id\"
			from
				v_EvnPrescr EP
				inner join v_EvnPrescrDiet EPP on EPP.EvnPrescrDiet_pid = EP.EvnPrescr_id
			where EP.EvnPrescr_id = :EvnPrescr_id
			order by EPP.EvnPrescrDiet_setDT
		";
		$queryParams = ["EvnPrescr_id" => $data["EvnPrescr_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$tmp_arr = $result->result("array");
		if (count($tmp_arr) == 0) {
			return $tmp_arr;
		}
		$response = [];
		$dateList = [];
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
				case when {$sysnick}.Lpu_id = :Lpu_id and coalesce({$sysnick}.{$sysnick}_IsSigned, 1) = 1 then 'edit' else 'view' end as \"accessType\"
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
			    to_char(Diet.EvnPrescrDiet_setDT, '{$this->dateTimeForm104}') as \"EvnPrescr_setDate\",
			    Diet.EvnPrescrDiet_IsExec as \"EvnPrescr_IsExec\",
			    Diet.PrescriptionStatusType_id as \"PrescriptionStatusType_id\",
			    EP.PrescriptionType_id as \"PrescriptionType_id\",
			    EP.PrescriptionType_id as \"PrescriptionType_Code\",
			    coalesce(EP.EvnPrescr_IsCito, 1) as \"EvnPrescr_IsCito\",
			    Diet.EvnPrescrDiet_Descr as \"EvnPrescr_Descr\",
			    EP.EvnPrescr_Descr as \"EvnPrescr_MainDescr\",
				Diet.EvnPrescrDiet_id as \"EvnPrescrDiet_id\",
				Diet.EvnPrescrDiet_Count as \"EvnPrescr_dayNum\",
				coalesce(PRT.PrescriptionDietType_id, 0) as \"PrescriptionDietType_id\",
				coalesce(PRT.PrescriptionDietType_Code, '0') as \"PrescriptionDietType_Code\",
				coalesce(PRT.PrescriptionDietType_Name, '') as \"PrescriptionDietType_Name\"
			from v_EvnPrescr EP
				inner join v_EvnPrescrDiet Diet on Diet.EvnPrescrDiet_pid = EP.EvnPrescr_id
				left join PrescriptionDietType PRT on PRT.PrescriptionDietType_id = Diet.PrescriptionDietType_id
				{$addJoin}
			where EP.EvnPrescr_pid  = :EvnPrescr_pid
			  and EP.PrescriptionType_id = 2
			  and Diet.PrescriptionStatusType_id != 3
			order by
				EP.EvnPrescr_id,
				Diet.EvnPrescrDiet_setDT
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
		$response = array();
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
			if ($is_exe == false) {
				$is_exe = ($row["EvnPrescr_IsExec"] == 2);
			}
			if ($is_sign == false) {
				$is_sign = ($row["PrescriptionStatusType_id"] == 2);
			}
			if (empty($tmp_arr[$i + 1]) || $last_ep != $tmp_arr[$i + 1]["EvnPrescr_id"]) {
				if ($is_exe) $row["EvnPrescr_IsExec"] = 2;
				if ($is_sign) $row["PrescriptionStatusType_id"] = 2;

				if (!empty($section) && $section === "api") {
					$row["EvnPrescr_setDate"] = $tmp_arr[$first_index]["EvnPrescr_setDate"];
				} else {
					$row["EvnPrescr_setDate"] = $tmp_arr[$first_index]["EvnPrescr_setDate"] . "&nbsp;—&nbsp;" . $row["EvnPrescr_setDate"];
				}
				$row[$section . "_id"] = $row["EvnPrescr_id"] . "-" . $row["EvnPrescrDiet_id"];
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