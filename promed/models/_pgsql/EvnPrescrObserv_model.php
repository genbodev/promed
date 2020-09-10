<?php
defined("BASEPATH") or die ("No direct script access allowed");
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
 * Модель назначения "Наблюдение"
 *
 * Назначения с типом "Наблюдение" хранятся в таблицах EvnPrescr, EvnPrescrObserv, EvnPrescrObservPos
 * В EvnPrescr хранится само назначение,
 * в EvnPrescrObserv - календарь назначения и тип времени наблюдения, признак выполнения
 * в EvnPrescrObservPos - список параметров наблюдения
 *
 * @package		EvnPrescr
 * @author		Александр Пермяков
 *
 * @property CI_DB_driver $db
 */
class EvnPrescrObserv_model extends EvnPrescrAbstract_model
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	public $dateTimeForm120 = "YYYY-MM-DD HH24:MI:SS";

	public function __construct()
	{
		parent::__construct();
	}

	public $EvnPrescr_id = null;
	public $out = [];
	public $outForDel = [];
	public $inForDel = [];

	/**
	 * Определение идентификатора типа назначения
	 * @return int
	 */
	public function getPrescriptionTypeId()
	{
		return 10;
	}

	/**
	 * @param $data
	 * @return mixed
	 * @throws Exception
	 */
	public function getFreeDay($data)
	{
		$freeDate = 0;
		$inc = 0;
		$queryParams["EvnPrescr_pid"] = $data["EvnPrescr_pid"];
		while ($freeDate == 0) {
			$query = "
				select distinct COUNT(Obs.EvnPrescrObserv_setDate) as cnt
				from
					v_EvnPrescr EP
					inner join v_EvnPrescrObserv Obs on Obs.EvnPrescrObserv_pid = EP.EvnPrescr_id
				where EP.EvnPrescr_pid = :EvnPrescr_pid
				  and EP.PrescriptionType_id = 10
				  and EP.PrescriptionStatusType_id != 3
				  and Obs.EvnPrescrObserv_setDT::date = (tzgetdate() + ({$inc}||' days')::interval)::date
			";
			$num = $this->getFirstResultFromQuery($query, $queryParams);
			if((int)$num == 0) {
				$freeDate = 1;
			} else {
				$inc++;
			}
		}
		$query = "select to_char((tzgetdate() + ({$inc}||' days')::interval)::date, '{$this->dateTimeForm104}') as FreeDate";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (Контроль пересечения дат)", 500);
		}
		$response = $result->result("array");
		if (!is_array($response) || count($response) == 0) {
			throw new Exception("Ошибка при проверке возможности добавить назначение");
		}
		return $response;
	}

	/**
	 * Определение имени таблицы с данными назначения
	 * @return string
	 */
	public function getTableName()
	{
		return "EvnPrescrObserv";
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
					["field" => "observParamTypeList", "label" => "Список типов назначаемых параметров наблюдения", "rules" => "trim", "type" => "string"],
					["field" => "observTimeTypeList", "label" => "Список времен суток, в которые должно проводиться наблюдение", "rules" => "trim", "type" => "string"],
					["field" => "EvnPrescr_Descr", "label" => "Комментарий", "rules" => "trim", "type" => "string"],
					["field" => "PersonEvn_id", "label" => "Идентификатор состояния человека", "rules" => "", "type" => "id"],
					["field" => "Server_id", "label" => "Идентификатор сервера", "rules" => "", "type" => "int"],
				];
				break;
			case "getFreeDay":
				$rules = [
					["field" => "EvnPrescr_pid", "label" => "Идентификатор родительского события", "rules" => "", "type" => "id"]
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
	 * Контроль пересечения дат
	 * @param $data
	 * @param $dateList
	 * @return bool
	 * @throws Exception
	 */
	protected function hasCrossingDatesTypes($data, $dateList)
	{
		$lastIndex = count($dateList) - 1;
		$where = "";
		$queryParams = [
			"EvnPrescr_pid" => $data["EvnPrescr_pid"],
			"PrescriptionType_id" => $this->getPrescriptionTypeId(),
			"beg_date" => $dateList[0],
			"end_date" => $dateList[$lastIndex]
		];
		if (!empty($data["observParamTypeList"])) {
			$observParamTypeList = json_decode(toUTF($data["observParamTypeList"]));
			$observParamTypeListString = implode(",", $observParamTypeList);
			$where .= " and eps.ObservParamType_id in ({$observParamTypeListString}) ";
		}
		$query = "
			select count(ep.EvnPrescr_id) as cnt
			from
				v_EvnPrescr EP
				inner join v_EvnPrescrObserv Obs on Obs.EvnPrescrObserv_pid = EP.EvnPrescr_id
				inner join v_EvnPrescrObservPos eps on eps.EvnPrescr_id = ep.EvnPrescr_id
			where EP.EvnPrescr_pid = :EvnPrescr_pid
			  and EP.PrescriptionType_id = :PrescriptionType_id
			  and EP.PrescriptionStatusType_id != 3
			  and Obs.EvnPrescrObserv_setDT::date between :beg_date::date and :end_date::date
			{$where}
		";
		/**@var CI_DB_result $result */
		$result = $this->getFirstRowFromQuery($query, $queryParams);
		if (!is_array($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (Контроль пересечения дат)", 500);
		}
		if ($result["cnt"] == 0) {
			return false;
		}
		return true;
	}

	/**
	 * Контроль пересечения дат
	 * @param $data
	 * @param $dateList
	 * @return array|bool
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
				case when EP.PrescriptionStatusType_id = 1 
					and not exists(
					    select EOD.EvnObservData_Value
					    from
					    	v_EvnPrescrObservPos EPOP
							inner join v_EvnPrescrObserv EPO on EPO.EvnPrescrObserv_pid = EPOP.EvnPrescr_id
							left join v_EvnObserv EO on EO.EvnObserv_pid = EPO.EvnPrescrObserv_id	
							left join v_EvnObservData EOD on EOD.EvnObserv_id = EO.EvnObserv_id
						where EPO.EvnPrescrObserv_id = Obs.EvnPrescrObserv_id
						  and EOD.EvnObservData_Value is not null
					) then 'edit' else 'view'
				end as \"accessType\",
				ep.EvnPrescr_id as \"EvnPrescr_id\",
				ep.EvnPrescr_IsExec as \"EvnPrescr_IsExec\",
				Obs.EvnPrescrObserv_id as \"EvnPrescrObserv_id\",
				Obs.EvnPrescrObserv_setDate::varchar as \"EvnPrescrObserv_setDate\",
				eps.ObservParamType_id as \"ObservParamType_id\",
				Obs.ObservTimeType_id as \"ObservTimeType_id\",
				eps.EvnPrescrObservPos_id as \"EvnPrescrObservPos_id\",
				case when Obs.EvnPrescrObserv_setDT::date between :beg_date::date and :end_date::date then 'in' else 'out' end as interval
			from
				v_EvnPrescr EP
				inner join v_EvnPrescrObserv Obs on Obs.EvnPrescrObserv_pid = EP.EvnPrescr_id
				inner join v_EvnPrescrObservPos eps on eps.EvnPrescr_id = ep.EvnPrescr_id
			where EP.EvnPrescr_pid = :EvnPrescr_pid
			  and EP.PrescriptionType_id = :PrescriptionType_id
			  and EP.PrescriptionStatusType_id != 3
			  and Obs.EvnPrescrObserv_setDT::date between (:beg_date::date - (60||' days')::interval) and (:end_date::date + (60||' days')::interval)
			  {$add_where}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (Контроль пересечения дат)", 500);
		}
		$response = $result->result("array");
		if (!is_array($response)) {
			throw new Exception("Ошибка при проверке возможности добавить назначение");
		}
		$Observ = [];
		$arr = [];
		if (!empty($data["EvnPrescr_id"])) {
			$this->EvnPrescr_id = $data["EvnPrescr_id"];
		}
		$split = [];
		foreach ($response as $day) {
			if (($day["EvnPrescr_IsExec"] == 2 || ($day["accessType"] == "view" && $data["EvnPrescr_id"] != $day["EvnPrescr_id"]))) {
				if ($day["interval"] == "in") {
					$this->rollbackTransaction();
					throw new Exception("Указанная продолжительность курса пересекается 
						с продолжительностью курса назначения указанного типа, 
						которое уже имеется в рамках выбранного случая посещения/движения", 400);
				}
			}
			if ($day["interval"] == "out") {
				if (isset($split[$day["EvnPrescr_id"]])) {
					if (!in_array($day["ObservParamType_id"], $split[$day["EvnPrescr_id"]]["ObservParamType_id"])) {
						$split[$day["EvnPrescr_id"]]["ObservParamType_id"][] = $day["ObservParamType_id"];
						$split[$day["EvnPrescr_id"]]["EvnPrescr_id"] = $day["EvnPrescr_id"];
						$split[$day["EvnPrescr_id"]]["ObservTimeType_id"][] = $day["ObservTimeType_id"];
					}
				} else {
					$split[$day["EvnPrescr_id"]]["ObservParamType_id"][] = $day["ObservParamType_id"];
					$split[$day["EvnPrescr_id"]]["EvnPrescr_id"] = $day["EvnPrescr_id"];
					$split[$day["EvnPrescr_id"]]["ObservTimeType_id"][] = $day["ObservTimeType_id"];
				}
				$this->out[$day["EvnPrescr_id"]]["prescr_id"] = $day["EvnPrescr_id"];
				$this->out[$day["EvnPrescr_id"]]["params"][$day["ObservParamType_id"]] = $day["EvnPrescrObservPos_id"];
				if (isset($day["EvnPrescrObserv_id"])) {
					array_push($this->outForDel, $day["EvnPrescrObserv_id"]);
				}
			} else {
				if (!in_array($day["EvnPrescrObserv_id"], $Observ)) {
					$Observ[] = $day["EvnPrescrObserv_id"];
					$arr[$day["EvnPrescrObserv_id"]]["EvnPrescrObserv_id"] = $day["EvnPrescrObserv_id"];
					$arr[$day["EvnPrescrObserv_id"]]["EvnPrescrObserv_setDate"] = $day["EvnPrescrObserv_setDate"];
					$arr[$day["EvnPrescrObserv_id"]]["EvnPrescr_id"] = $day["EvnPrescr_id"];
					$arr[$day["EvnPrescrObserv_id"]]["accessType"] = $day["accessType"];
				}
				$arr[$day["EvnPrescrObserv_id"]]["ObservParamType_id"][] = $day["ObservParamType_id"];
				$arr[$day["EvnPrescrObserv_id"]]["ObservTimeType_id"][] = $day["ObservTimeType_id"];
				$arr[$day["EvnPrescrObserv_id"]]["params"][$day["ObservParamType_id"]] = $day["EvnPrescrObservPos_id"];
				if (isset($day["EvnPrescrObserv_id"])) {
					array_push($this->inForDel, $day["EvnPrescrObserv_id"]);
				}
			}

		}
		foreach ($split as $val) {
			if ($val["ObservParamType_id"] == json_decode(toUTF($data["observParamTypeList"]), true)
				&& in_array($val["ObservTimeType_id"][0], json_decode(toUTF($data["observTimeTypeList"]), true))) {
				if ($this->EvnPrescr_id == null || $val["EvnPrescr_id"] == $this->EvnPrescr_id) {
					$this->EvnPrescr_id = $val["EvnPrescr_id"];
				} else {
					$this->updatePrescrObserv($val["EvnPrescr_id"], $data["pmUser_id"]);
				}
			}
		}
		return $arr;
	}

	/**
	 * @param $EvnPrescr_id
	 * @param $pmUser
	 * @param bool $all
	 * @throws Exception
	 */
	public function Clear($EvnPrescr_id, $pmUser, $all = false)
	{
		// календарь полностью очищается
		$response = $this->clearEvnPrescrTable([
			"object" => "EvnPrescrObserv",
			"fk_pid" => "EvnPrescrObserv_pid",
			"pid" => $EvnPrescr_id,
			"pmUser_id" => $pmUser,
		]);
		if (!$response) {
			throw new Exception("Не удалось очистить календарь!");
		}
		if (!empty($response[0]["Error_Msg"])) {
			throw new Exception($response[0]["Error_Msg"]);
		}
		if ($all) {
			$query = "
				select
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_evnprescrobserv_del(
				    evnprescrobserv_id := :EvnPrescrObserv_id,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"pmUser_id" => $pmUser,
				"EvnPrescrObserv_id" => $EvnPrescr_id
			];
			/**@var CI_DB_result $result */
			$result = $this->db->query($query, $queryParams);
			if (!is_object($result)) {
				throw new Exception("Не удалось очистить календарь!");
			}
		} else {
			// назначеные параметры полностью очищается
			$response = $this->clearEvnPrescrTable([
				"object" => "EvnPrescrObservPos",
				"fk_pid" => "EvnPrescr_id",
				"pid" => $EvnPrescr_id,
			]);
			if (!$response) {
				throw new Exception("Не удалось очистить назначеные параметры!");
			}
			if (!empty($response[0]["Error_Msg"])) {
				throw new Exception($response[0]["Error_Msg"]);
			}
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	public function ClearDay($data)
	{
		//Проверяем существуют ли выполненные назначения (при наблюдении утром и вечером их может быть 2)
		$query0 = "
			select 
				epr.EvnPrescr_id as \"EvnPrescr_id\",
				EO.ObservTimeType_id as \"ObservTimeType_id\"
			from
				v_EvnPrescr epr
				inner join v_EvnObserv EO on EO.EvnObserv_pid = epr.EvnPrescr_id
			where epr.EvnPrescr_pid in (
			    select ep.EvnPrescr_pid
			    from v_EvnPrescr ep
			    where ep.EvnPrescr_id =:EvnPrescr_id
			  )
			  and epr.EvnPrescr_IsExec = 2
		";
		$params0 = ["EvnPrescr_id" => $data["EvnPrescr_id"]];
		$result0 = $this->db->query($query0, $params0);
		$response0 = $result0->result("array");
		$useNigthId = false;
		$nigthId = null;
		$where = "";
		//Если выполненны утреннее и вечернее наблюдение, то исключаем из списка на удаление общие параметры
		if (count($response0) == 2) {
			$where = " and eod.ObservParamType_id not in (5,6,7,8,9,10,11) ";
		} else {
			//Если выполнено только утреннее наблюдение и мы собираемся отменить выполнение, то берем айдишник вечернего для использования при удалении общих параметров (они сохраняются и выводятся всегда с вечерним)
			if ($response0[0]["ObservTimeType_id"] == 1) {
				$useNigthId = true;
				$query00 = "
					select epr.EvnPrescr_id as \"EvnPrescr_id\"
					from
					    v_EvnPrescr epr
						inner join v_EvnObserv EO on EO.EvnObserv_pid = epr.EvnPrescr_id
					where epr.EvnPrescr_pid in (
					    select ep.EvnPrescr_pid
					    from v_EvnPrescr ep
					    where ep.EvnPrescr_id =:EvnPrescr_id
					  )
					  and EO.ObservTimeType_id = 3
				";
				$params00 = array("EvnPrescr_id" => $data["EvnPrescr_id"]);
				$result00 = $this->db->query($query00, $params00);
				$response00 = $result00->result("array");
				$nigthId = $response00[0]["EvnPrescr_id"];
			}
		}
		$query = "
			select
				eod.EvnObservData_id as \"EvnObservData_id\",
				eod.ObservParamType_id as \"ObservParamType_id\"
			from
				v_EvnObservData eod
				inner join v_EvnObserv EO on EO.EvnObserv_id = eod.EvnObserv_id
			where EO.EvnObserv_pid =:EvnPrescr_id {$where}
		";
		$params = ["EvnPrescr_id" => $data["EvnPrescr_id"]];
		$result = $this->db->query($query, $params);
		$err = true;
		$response = $result->result("array");
		foreach ($response as $val) {
			if ($val["ObservParamType_id"] > 4 && $useNigthId === true) {
				//Для общих параметров при отмене единственно выполненного (из двух - утро и вечер) утреннего наблюдения берем данные сохраненные с вечерним айдишником
				$query1 = "
					select eod.EvnObservData_id as \"EvnObservData_id\"
					from
						v_EvnObservData eod
						inner join v_EvnObserv EO on EO.EvnObserv_id = eod.EvnObserv_id
					where EO.EvnObserv_pid =:nightId
					  and eod.ObservParamType_id = :ObservParamType_id
				";
				$params1 = [
					"EvnPrescr_id" => $data["EvnPrescr_id"],
					"ObservParamType_id" => $val["ObservParamType_id"],
					"nightId" => $nigthId
				];
				$result1 = $this->db->query($query1, $params1);
				$response1 = $result1->result("array");
				$val["EvnObservData_id"] = $response1[0]["EvnObservData_id"];
			}
			$query = "
				select
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_evnobservdata_del(evnobservdata_id := :EvnObservData_id);
			";
			$queryParams = ["EvnObservData_id" => $val["EvnObservData_id"]];
			$result = $this->db->query($query, $queryParams);
			if (is_object($result)) {
				$response = $result->result("array");
				if (!empty($response[0]["Error_Msg"])) {
					$err = false;
				}
			} else {
				$err = false;
			}
		}
		return $err;
	}

	/**
	 * @param $EvnPrescr_id
	 * @param $pmUser
	 * @throws Exception
	 */
	public function getCntObserv($EvnPrescr_id, $pmUser)
	{
		$query = "
			select count(1) as cnt
			from
				v_EvnPrescr EP
				inner join v_EvnPrescrObserv Obs on Obs.EvnPrescrObserv_pid = EP.EvnPrescr_id
			where EP.PrescriptionType_id = 10
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
			$this->Clear($EvnPrescr_id, $pmUser, true);
		}
	}

	/**
	 * @param $EvnPrescr_id
	 * @return string
	 * @throws Exception
	 */
	public function getStatus($EvnPrescr_id)
	{
		$query = "
			select count(1) as cnt
			from
				v_EvnPrescrObservPos EPOP
				inner join v_EvnPrescrObserv EPO on EPO.EvnPrescrObserv_pid = EPOP.EvnPrescr_id
				left join v_EvnObserv EO on EO.EvnObserv_pid = EPO.EvnPrescrObserv_id	
				left join v_EvnObservData EOD on EOD.EvnObserv_id = EO.EvnObserv_id
			where EPO.EvnPrescrObserv_pid = :EvnPrescr_id
			  and EOD.EvnObservData_Value is not null
			  and EOD.EvnObservData_Value!=''
		";
		$queryParams["EvnPrescr_id"] = $EvnPrescr_id;
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных ", 500);
		}
		$response = $result->result("array");
		if ($response[0]["cnt"] == 0) {
			return "edit";
		}
		return "view";
	}

	/**
	 * Сохранение назначения
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return array
	 * @throws Exception
	 */
	public function doSave($data = array(), $isAllowTransaction = true)
	{
		// Стартуем транзакцию
		$this->beginTransaction();
		try {
			if (empty($data["EvnPrescr_pid"])) {
				throw new Exception("Не указан Идентификатор родительского события", 400);
			}
			if (empty($data["PersonEvn_id"])) {
				throw new Exception("Не указан Идентификатор состояния человека", 400);
			}
			if (!isset($data["Server_id"])) {
				throw new Exception("Не указан Идентификатор сервера", 400);
			}
			$observParamTypeList = [];
			$observTimeTypeList = [];
			if (!empty($data["observParamTypeList"])) {
				$observParamTypeList = json_decode(toUTF($data["observParamTypeList"]), true);
			}
			if (!empty($data["observTimeTypeList"])) {
				$observTimeTypeList = json_decode(toUTF($data["observTimeTypeList"]), true);
			}

			if (!is_array($observParamTypeList) || count($observParamTypeList) == 0) {
				throw new Exception("Не выбран ни один параметр наблюдения", 400);
			}

			if (!is_array($observTimeTypeList) || count($observTimeTypeList) == 0) {
				throw new Exception("Не выбрано время наблюдений", 400);
			}
			if ($data["EvnPrescr_id"]) {
				$data["action"] = $this->getStatus($data["EvnPrescr_id"]);
			}
			$dateList = $this->_createDateList($data);
			$action = (empty($data["EvnPrescr_id"]) ? "add" : "edit");
			if ($action == "edit" && $data["action"] == "edit") {
				$this->Clear($data["EvnPrescr_id"], $data["pmUser_id"]);
			}
			$cross = [];
			$crossDates = $this->hasCrossingDatesTypes($data, $dateList);
			if ($crossDates) {
				throw new Exception("Ошибка при добавлении нового наблюдения.
				В случае уже добавлено аналогичное наблюдение в том же диапазоне дат.
				Измените параметры или дату начала наблюдения", 400);
			}
			if ($this->EvnPrescr_id != null && $data["EvnPrescr_id"] == null) {
				$data["EvnPrescr_id"] = $this->EvnPrescr_id;
				$data["action"] = $this->getStatus($data["EvnPrescr_id"]);
			}
			$data["EvnPrescr_id"] = $this->_save($data);
			$this->_saveCalendar($data, $dateList, $observTimeTypeList, $cross);
			$idList2 = [];
			// Сохранение выбранных типов наблюдения
			foreach ($observParamTypeList as $ObservParamType_id) {
				$EPO_id = null;
				foreach ($cross as $row) {
					if ($row["EvnPrescr_id"] == $data["EvnPrescr_id"] && $row["accessType"] == "view") {
						foreach ($row["params"] as $key => $val) {
							if ($ObservParamType_id == $key) {
								$EPO_id = $val;
								break;
							}
						}
					}
				}
				if ($EPO_id == null) {
					foreach ($this->out as $row) {
						if ($row["prescr_id"] == $data["EvnPrescr_id"]) {
							foreach ($row["params"] as $key => $val) {
								if ($ObservParamType_id == $key) {
									$EPO_id = $val;
									break;
								}
							}
						}
					}
				}
				$response = $this->_savePos([
					"ObservParamType_id" => $ObservParamType_id,
					"EvnPrescr_id" => $data["EvnPrescr_id"],
					"EvnPrescrObservPos_id" => $EPO_id,
					"pmUser_id" => $data["pmUser_id"]
				]);
				if (!is_array($response) || count($response) == 0) {
					throw new Exception("Ошибка при сохранении выбранного типа наблюдения", 500);
				} else if (!empty($response[0]["Error_Msg"])) {
					throw new Exception($response[0]["Error_Msg"], 500);
				}
				$idList2[] = $response[0]["EvnPrescrObservPos_id"];
			}
			$this->EvnPrescr_id = $data["EvnPrescr_id"];
		} catch (Exception $e) {
			$this->rollbackTransaction();
			throw new Exception($e->getMessage(), $e->getCode());
		}
		$this->commitTransaction();
		$idList = $this->updatePrescrObserv($data["EvnPrescr_id"], $data["pmUser_id"]);
		return [[
			"EvnPrescr_id" => $data["EvnPrescr_id"],
			"EvnPrescrObserv_id_list" => $idList,
			"EvnPrescrObservPos_id_list" => $idList2,
			"Error_Msg" => null,
			"Error_Code" => null,
		]];
	}

	/**
	 * @param $EvnPrescr_id
	 * @param $pmUser_id
	 * @return array
	 * @throws Exception
	 */
	function updatePrescrObserv($EvnPrescr_id, $pmUser_id)
	{
		$query = "
			select
				EvnPrescrObserv_id as \"EvnPrescrObserv_id\",
				Lpu_id as \"Lpu_id\",
				Server_id as \"Server_id\",
				PersonEvn_id as \"PersonEvn_id\",
				PrescriptionType_id as \"PrescriptionType_id\",
				PrescriptionStatusType_id as \"PrescriptionStatusType_id\",
				ObservTimeType_id as \"ObservTimeType_id\",
				to_char(EvnPrescrObserv_setDT, '{$this->dateTimeForm120}') as \"EvnPrescr_setDate\"
			from v_EvnPrescrObserv
			where EvnPrescrObserv_pid = :EvnPrescr_id
			order by EvnPrescrObserv_setDT
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
					evnprescrobserv_id as \"EvnPrescrObserv_id\",
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_evnprescrobserv_upd(
				    evnprescrobserv_id := :EvnPrescrObserv_id,
				    evnprescrobserv_pid := :EvnPrescr_id,
				    lpu_id := :Lpu_id,
				    server_id := :Server_id,
				    personevn_id := :PersonEvn_id,
				    evnprescrobserv_setdt := :EvnPrescrObserv_setDT,
				    prescriptiontype_id := :PrescriptionType_id,
				    evnprescrobserv_iscito := 1,
				    prescriptionstatustype_id := :PrescriptionStatusType_id,
				    observtimetype_id := :ObservTimeType_id,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"EvnPrescr_id" => $this->EvnPrescr_id,
				"EvnPrescrObserv_setDT" => $val["EvnPrescr_setDate"],
				"EvnPrescrObserv_id" => $val["EvnPrescrObserv_id"],
				"EvnPrescrObserv_Count" => count($response),
				"Lpu_id" => $val["Lpu_id"],
				"EvnPrescrObserv_Index" => $index,
				"PrescriptionStatusType_id" => 1,
				"ObservTimeType_id" => $val["ObservTimeType_id"],
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
			$idList[] = $response[0]["EvnPrescrObserv_id"];
			$index++;
		}
		if ($this->EvnPrescr_id != $EvnPrescr_id) {
			$this->getCntObserv($EvnPrescr_id, $pmUser_id);
		}
		return $idList;
	}

	/**
	 * Сохранение календаря в EvnPrescrObserv
	 * @param $data
	 * @param $dateList
	 * @param $observTimeTypeList
	 * @param $cross
	 * @return array
	 * @throws Exception
	 */
	protected function _saveCalendar($data, $dateList, $observTimeTypeList, $cross)
	{
		$queryParams = [
			"EvnPrescrObserv_id" => null,
			"EvnPrescr_id" => $data["EvnPrescr_id"],
			"Lpu_id" => $data["Lpu_id"],
			"Server_id" => $data["Server_id"],
			"PersonEvn_id" => $data["PersonEvn_id"],
			"PrescriptionType_id" => $this->getPrescriptionTypeId(),
			"PrescriptionStatusType_id" => 1,
			"pmUser_id" => $data["pmUser_id"],
		];
		$idList = [];
		foreach ($dateList as $addDate) {
			$action = "ins";
			$queryParams["EvnPrescrObserv_id"] = null;
			$queryParams["EvnPrescrObserv_setDT"] = $addDate;
			foreach ($observTimeTypeList as $ObservTimeType_id) {
				$queryParams["ObservTimeType_id"] = $ObservTimeType_id;
				foreach ($cross as $row) {
					if ($row["EvnPrescr_id"] == $data["EvnPrescr_id"] && $row["EvnPrescrObserv_setDate"] == $addDate && $row["accessType"] == "view" && $ObservTimeType_id == $row["ObservTimeType_id"][0]) {
						$queryParams["EvnPrescrObserv_id"] = $row["EvnPrescrObserv_id"];
						$action = "upd";
						break;
					}
				}
				$selectSrting  = "
					evnprescrobserv_id as \"EvnPrescrObserv_id\",
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				";
				$query = "
					select {$selectSrting}
					from p_evnprescrobserv_{$action}(
					    evnprescrobserv_id := :EvnPrescrObserv_id,
					    evnprescrobserv_pid := :EvnPrescr_id,
					    lpu_id := :Lpu_id,
					    server_id := :Server_id,
					    personevn_id := :PersonEvn_id,
					    evnprescrobserv_setdt := :EvnPrescrObserv_setDT,
					    prescriptiontype_id := :PrescriptionType_id,
					    evnprescrobserv_iscito := 1,
					    prescriptionstatustype_id := :PrescriptionStatusType_id,
					    observtimetype_id := :ObservTimeType_id,
					    pmuser_id := :pmUser_id
					);
				";
				/**@var CI_DB_result $result */
				$result = $this->db->query($query, $queryParams);
				if (!is_object($result)) {
					throw new Exception("Ошибка при запросе к БД при сохранении календаря", 500);
				}
				$response = $result->result("array");
				if (!empty($response[0]["Error_Msg"])) {
					throw new Exception($response[0]["Error_Msg"], 500);
				}
				$idList[] = $response[0]["EvnPrescrObserv_id"];
			}
		}
		return $idList;
	}

	/**
	 * Метод сохранения параметра наблюдения
	 * @param $data
	 * @return array|bool
	 */
	private function _savePos($data)
	{
		$code = !empty($data["EvnPrescrObservPos_id"]) && $data["EvnPrescrObservPos_id"] > 0 ? "upd" : "ins";
		$selectString = "
			evnprescrobservpos_id as \"EvnPrescrObservPos_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from p_evnprescrobservpos_{$code}(
			    evnprescrobservpos_id := :EvnPrescrObservPos_id,
			    evnprescr_id := :EvnPrescr_id,
			    observparamtype_id := :ObservParamType_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"EvnPrescrObservPos_id" => $data["EvnPrescrObservPos_id"],
			"EvnPrescr_id" => $data["EvnPrescr_id"],
			"ObservParamType_id" => $data["ObservParamType_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
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
				case when EP.PrescriptionStatusType_id = 1 and not exists (
				    select EOD.EvnObservData_Value
					from
						v_EvnPrescrObservPos EPOP
						inner join v_EvnPrescrObserv EPO on EPO.EvnPrescrObserv_pid = EPOP.EvnPrescr_id
						left join v_EvnObserv EO on EO.EvnObserv_pid = EPO.EvnPrescrObserv_id	
						left join v_EvnObservData EOD on EOD.EvnObserv_id = EO.EvnObserv_id
					where EPO.EvnPrescrObserv_pid = :EvnPrescr_id
					  and EOD.EvnObservData_Value is not null
				) then 'edit' else 'view' end as \"accessType\",
				EP.EvnPrescr_id as \"EvnPrescr_id\",
				EP.EvnPrescr_pid as \"EvnPrescr_pid\",
				EP.EvnPrescr_IsCito as \"EvnPrescr_IsCito\",
				EP.EvnPrescr_Descr as \"EvnPrescr_Descr\",
				EP.PersonEvn_id as \"PersonEvn_id\",
				EP.Server_id as \"Server_id\",
				to_char(EPP.EvnPrescrObserv_setDT, '{$this->dateTimeForm104}') as \"EvnPrescrObserv_setDT\",
				EPP.ObservTimeType_id as \"ObservTimeType_id\",
				eps.ObservParamType_id as \"ObservParamType_id\"
			from
				v_EvnPrescr ep
				inner join v_EvnPrescrObserv EPP on EPP.EvnPrescrObserv_pid = ep.EvnPrescr_id
				inner join v_EvnPrescrObservPos eps on eps.EvnPrescr_id = ep.EvnPrescr_id
			where  ep.EvnPrescr_id = :EvnPrescr_id
			order by EPP.EvnPrescrObserv_setDT
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
		$TimeTypeList = [];
		$paramtype = [];
		foreach ($tmp_arr as $row) {
			if (!in_array($row["EvnPrescrObserv_setDT"], $dateList))
				$dateList[] = $row["EvnPrescrObserv_setDT"];
			if (!in_array($row["ObservTimeType_id"], $TimeTypeList))
				$TimeTypeList[] = $row["ObservTimeType_id"];
			if (!in_array($row["ObservParamType_id"], $paramtype))
				$paramtype[] = $row["ObservParamType_id"];
		}
		$response[0] = $tmp_arr[0];
		$response[0]["EvnPrescr_setDate"] = $dateList[0];
		$response[0]["EvnPrescr_dayNum"] = count($dateList);
		$response[0]["ObservTimeType_id"] = $TimeTypeList;
		$response[0]["ObservParamType_id"] = $paramtype;
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
			    to_char(Obs.EvnPrescrObserv_setDT, '{$this->dateTimeForm104}') as \"EvnPrescr_setDate\",
			    null as \"EvnPrescr_setTime\",
			    coalesce(Obs.EvnPrescrObserv_IsExec, 1) as \"EvnPrescr_IsExec\",
			    Obs.PrescriptionStatusType_id as \"PrescriptionStatusType_id\",
			    EP.PrescriptionType_id as \"PrescriptionType_id\",
			    EP.PrescriptionType_id as \"PrescriptionType_Code\",
			    coalesce(EP.EvnPrescr_IsCito, 1) as \"EvnPrescr_IsCito\",
			    Obs.EvnPrescrObserv_Descr as \"EvnPrescr_Descr\",
				Obs.EvnPrescrObserv_id as \"EvnPrescrObserv_id\",
				OTT.ObservTimeType_id as \"ObservTimeType_id\",
				OTT.ObservTimeType_Name as \"ObservTimeType_Name\",
				EPOP.EvnPrescrObservPos_id as \"EvnPrescrObservPos_id\",
				OPT.ObservParamType_Name as \"ObservParamType_Name\"
			from v_EvnPrescr EP
				inner join v_EvnPrescrObserv Obs on Obs.EvnPrescrObserv_pid = EP.EvnPrescr_id
				left join v_ObservTimeType OTT on OTT.ObservTimeType_id = Obs.ObservTimeType_id
				left join v_EvnPrescrObservPos EPOP on EPOP.EvnPrescr_id = EP.EvnPrescr_id
				left join ObservParamType OPT on OPT.ObservParamType_id = EPOP.ObservParamType_id
				{$addJoin}
			where EP.EvnPrescr_pid  = :EvnPrescr_pid
			  and EP.PrescriptionType_id = 10
			  and Obs.PrescriptionStatusType_id != 3
			order by
				EP.EvnPrescr_id,
				Obs.EvnPrescrObserv_setDT
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
		$time_arr = [];
		$params_arr = [];
		foreach ($tmp_arr as $i => $row) {
			if ($last_ep != $row["EvnPrescr_id"]) {
				//это первая итерация с другим назначением
				$first_index = $i;
				$last_ep = $row["EvnPrescr_id"];
				$is_exe = false;
				$is_sign = false;
				$time_arr = [];
				$params_arr = [];
			}
			if (!in_array($row["ObservTimeType_Name"], $time_arr))
				$time_arr[] = $row["ObservTimeType_Name"];
			if (empty($params_arr[$row["EvnPrescrObservPos_id"]]))
				$params_arr[$row["EvnPrescrObservPos_id"]] = $row["ObservParamType_Name"];
			if ($is_exe == true) {
				$is_exe = ($row["EvnPrescr_IsExec"] == 2);
			}
			if ($is_sign == false) {
				$is_sign = ($row["PrescriptionStatusType_id"] == 2);
			}
			if (empty($tmp_arr[$i + 1]) || $last_ep != $tmp_arr[$i + 1]["EvnPrescr_id"]) {
				$row["EvnPrescr_IsExec"] = $is_exe ? 2 : 1;
				if ($is_sign) $row["PrescriptionStatusType_id"] = 2;
				$row["Params"] = implode(", ", $params_arr);
				$row["EvnPrescr_setTime"] = implode(", ", $time_arr);
				$row["EvnPrescr_setDate"] = $tmp_arr[$first_index]["EvnPrescr_setDate"] . "&nbsp;—&nbsp;" . $row["EvnPrescr_setDate"];
				$row[$section . "_id"] = $row["EvnPrescr_id"] . "-" . $row["EvnPrescrObserv_id"];
				$response[] = $row;
			}
		}
		return $response;
	}
}
