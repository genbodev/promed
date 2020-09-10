<?php
require_once("Timetable_model.php");
require_once("TimetableGraf_model_get.php");
/**
 * TimetableGraf_model - модель для работы с расписанием в поликлинике
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Загрузка базовой модели для работы с расписанием
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (megatherion@list.ru)
 * @version      22.05.2009
 *
 * @property LpuRegion_model $LpuRegion_model
 * @property EvnDirectionAll_model $EvnDirectionAll_model
 * @property LpuIndividualPeriod_model $lipmodel
 * @property Annotation_model $Annotation_model
 * @property Evn_model $Evn_model
 * @property CI_DB_driver $dbkvrachu
 * @property TimetableStac_model $ttsmodel
 */

class TimetableGraf_model extends Timetable_model
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	public $dateTimeForm108 = "HH24:MI:SS";
	public $dateTimeForm120 = "YYYY-MM-DD HH24:MI:SS";
	public $dateForm120 = "YYYY-MM-DD";
	public $dateTimeForm4 = "DD.MM.YY";

	/**
	 * Ведем счетчик фактического использования расписаний (фактом использования считается запись на бирку)
	 * @param $data
	 * @return array|bool
	 */
	function countApply($data)
	{
		$filterArray = ["TimetableObject_id = :TimetableObject_id"];
		$params["TimetableObject_id"] = $data["TimetableObject_id"];
		$params["Server_id"] = $data["Server_id"];
		$params["pmUser_id"] = $data["pmUser_id"];
		$params["MedStaffFact_id"] = null;
		$params["MedPersonal_id"] = null;
		$params["LpuSection_id"] = null;
		if ($data["TimetableObject_id"] == 1) {
			// Принадлежность расписания конкретному врачу полки идентифицируем по MedStaffFact_id и MedPersonal_id
			$params["MedStaffFact_id"] = $data["MedStaffFact_id"];
			$params["MedPersonal_id"] = $data["MedPersonal_id"];
			$filterArray[] = "MedStaffFact_id = :MedStaffFact_id";
			$filterArray[] = "MedPersonal_id = :MedPersonal_id";
		} else {
			// Принадлежность расписания конкретному отделению парки или стаца идентифицируем по LpuSection_id
			$params["LpuSection_id"] = $data["LpuSection_id"];
			$filterArray[] = "LpuSection_id = :LpuSection_id";
		}
		$filterString = implode(" and ", $filterArray);
		$sql = "
			select TimetableCount_id as \"TimetableCount_id\"
			from v_TimetableCount
			where {$filterString}
			  and pmUser_insID = :pmUser_id
			limit 1
		";
		/**
		 * @var CI_DB_result $tempResult
		 * @var CI_DB_result $result
		 */
		$tempResult = $this->db->query($sql, $params);
		if (!is_object($tempResult)) {
			return false;
		}
		$tempResult = $tempResult->result("array");
		if (count($tempResult) == 0) {
			$sql = "
				select
					timetablecount_id as \"TimetableCount_id\",
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_timetablecount_ins(
						timetablecount_count := 1,
						timetableobject_id := :TimetableObject_id,
						lpusection_id := :LpuSection_id,
						medpersonal_id := :MedPersonal_id,
						medstafffact_id := :MedStaffFact_id,
						server_id := :Server_id,
						pmuser_id := :pmUser_id
				);
			";
		} else {
			$params["TimetableCount_id"] = $tempResult[0]["TimetableCount_id"];
			$subQuery = "
				select sum(TimetableCount_Count)
				from v_TimetableCount
				where {$filterString}
				  and pmUser_insID = :pmUser_id
			";
			$sql = "
				select
					timetablecount_id as \"TimetableCount_id\",
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_timetablecount_upd(
				    	timetablecount_id := :TimetableCount_id,
						timetablecount_count := (1 + ({$subQuery}))::int8,
						timetableobject_id := :TimetableObject_id,
						lpusection_id := :LpuSection_id,
						medpersonal_id := :MedPersonal_id,
						medstafffact_id := :MedStaffFact_id,
						server_id := :Server_id,
						pmuser_id := :pmUser_id
				);
			";
		}
		$result = $this->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Проверка - записан ли такой пациент на сегодня
	 * @param $data
	 * @return array|bool
	 */
	function checkPersonByToday($data)
	{
		$whereArray = [
			"ttg.Person_id = :Person_id",
			"to_char(tzgetdate()::date, '{$this->dateTimeForm104}') = to_char(ttg." . $data["object"] . "_begTime::date, '{$this->dateTimeForm104}')"
		];
		$whereArray[] = "ttg.LpuSection_id = :LpuSection_id";
		if ($data["object"] == "TimetableGraf") {
			$whereArray[] = "ttg.MedStaffFact_id = :MedStaffFact_id";
		}
		$selectArray = [
			"ttg." . $data["object"] . "_id as \"" . $data["object"] . "_id\"",
			"to_char(ttg." . $data["object"] . "_begTime, '{$this->dateTimeForm108}') as \"TimetableGraf_begTime\""
		];
		$joinArray = [
			"v_" . $data["object"] . " ttg",
			"left join v_PersonState_all p on p.Person_id = ttg.Person_id"
		];
		if (allowPersonEncrypHIV($data['session'])) {
			$joinArray[] = " left join v_PersonEncrypHIV peh on peh.Person_id = p.Person_id";
			$selectArray[] = "
		       case when peh.PersonEncrypHIV_Encryp is null
		           then rtrim(p.Person_Surname) ||' '||coalesce(rtrim(p.Person_Firname), '')||' '||coalesce(rtrim(p.Person_Secname), '')
		           else rtrim(peh.PersonEncrypHIV_Encryp)
		       end as \"Person_FIO\"
			";
			$selectArray[] = "
		        case when peh.PersonEncrypHIV_Encryp is null
		            then to_char(p.Person_BirthDay, '{$this->dateTimeForm104}')
		            else null
		        end as \"Person_BirthDay\"
			";
			$selectArray[] = "
		        case when peh.PersonEncrypHIV_Encryp is null
		            then rtrim(p.Person_Secname)
		            else null
		        end as \"Person_Age\"
			";
		} else {
			$selectArray[] = "rtrim(p.Person_Surname)||' '||coalesce(rtrim(p.Person_Firname), '')||' '||coalesce(rtrim(p.Person_Secname), '') as \"Person_FIO\"";
			$selectArray[] = "to_char(p.Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_BirthDay\"";
			$selectArray[] = "Age2(p.Person_BirthDay, {$data['object']}_updDT) as \"Person_Age\"";
		}
		$selectString = implode(", ", $selectArray);
		$joinString = implode(" ", $joinArray);
		$whereString = implode(" and ", $whereArray);
		$sql = "
			SELECT {$selectString}
			FROM {$joinString}
			WHERE {$whereString}
			limit 1
		";
		$sqlParams = [
			"Person_id" => $data["Person_id"],
			"LpuSection_id" => $data["LpuSection_id"],
			"MedStaffFact_id" => $data["MedStaffFact_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Проверка
	 * @param $data
	 * @return array|bool
	 */
	function checkPersonByFuture($data)
	{
		$selectArray = [
			"ttg." . $data["object"] . "_id as \"" . $data["object"] . "_id\"",
			"to_char(ttg." . $data["object"] . "_begTime, '{$this->dateTimeForm108}') as \"TimetableGraf_begTime\""
		];
		$fromArray = [
			"v_" . $data["object"] . " ttg",
			"left join v_PersonState_all p on p.Person_id = ttg.Person_id"
		];
		$whereArray = [
			"ttg.Person_id = :Person_id",
			"ttg.{$data['object']}_begTime::date >= tzgetdate()::date",
			"ttg.LpuSection_id = :LpuSection_id"
		];
		if ($data["object"] == "TimetableGraf") {
			$whereArray[] = "ttg.MedStaffFact_id = :MedStaffFact_id";
		}
		if (allowPersonEncrypHIV($data["session"])) {
			$selectArray[] = "
		        case when peh.PersonEncrypHIV_Encryp is null
		            then rtrim(p.Person_Surname)||' '||coalesce(rtrim(p.Person_Firname), '')||' '||coalesce(rtrim(p.Person_Secname), '')
		            else rtrim(peh.PersonEncrypHIV_Encryp)
		        end as \"Person_FIO\"
			";
			$selectArray[] = "
		        case when peh.PersonEncrypHIV_Encryp is null
		            then to_char(p.Person_BirthDay, '{$this->dateTimeForm104}')
		            else null
		        end as \"Person_BirthDay\"
			";
			$selectArray[] = "
		        case when peh.PersonEncrypHIV_Encryp is null
		            then rtrim(p.Person_Secname)
		            else null
		        end as \"Person_Age\"
			";
			$fromArray[] = "left join v_PersonEncrypHIV peh on peh.Person_id = p.Person_id";
		} else {
			$selectArray[] = "rtrim(p.Person_Surname)||' '||coalesce(rtrim(p.Person_Firname), '')||' '||coalesce(rtrim(p.Person_Secname), '') as \"Person_FIO\"";
			$selectArray[] = "to_char(p.Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_BirthDay\"";
			$selectArray[] = "Age2(p.Person_BirthDay, " . $data["object"] . "_updDT) as \"Person_Age\"";
		}
		$selectString = implode(", ", $selectArray);
		$fromString = implode(" ", $fromArray);
		$whereString = implode(" and ", $whereArray);
		$sql = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
			limit 1
		";
		$sqlParams = [
			"Person_id" => $data["Person_id"],
			"LpuSection_id" => $data["LpuSection_id"],
			"MedStaffFact_id" => $data["MedStaffFact_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Создание экстренного посещения пациентом врача
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function Create($data)
	{
		$this->load->helper("Reg");
		$Timetable_Day = empty($data["Timetable_Day"]) ? TimeToDay(strtotime($data["date"])) : $data["Timetable_Day"];
		if (!isset($data["EmergencyData_id"])) {
			$data["EmergencyData_id"] = null;
		}
		$dataObjectName = $data["object"];
		$params = [
			"Person_id" => $data["Person_id"],
			"Timetable_Day" => $Timetable_Day,
			"pmUser_id" => $data["pmUser_id"]
		];
		$paramArray = [];
		if ($dataObjectName == "TimetableGraf") {
			$paramArray[] = "MedStaffFact_id := :MedStaffFact_id";
			$paramArray[] = "TimetableGraf_factTime := :TimetableGraf_factTime";
			$params["MedStaffFact_id"] = $data["MedStaffFact_id"];
			$params["TimetableGraf_factTime"] = $data["TimetableGraf_factTime"];
		} elseif ($dataObjectName == "TimetableStac") {
			// Создание экстренной койки
			$paramArray[] = "LpuSection_id := :LpuSection_id";
			$paramArray[] = "LpuSectionBedType_id := 3";
			$paramArray[] = "TimetableStac_setDate := tzgetdate()";
			$paramArray[] = "TimetableType_id := 6";
			$paramArray[] = "TimetableStac_EmStatus := null";
			$paramArray[] = "EmergencyData_id := :EmergencyData_id";
			$paramArray[] = "Evn_id := null";
			$params["LpuSection_id"] = $data["LpuSection_id"];
			$params["EmergencyData_id"] = $data["EmergencyData_id"];
		}
		if (in_array($dataObjectName, [
			"TimetableGraf",
			"TimetableStac",
			"TimeTablePar",
			"TimeTableResource"
		])
		) {
			$paramArray[] = "RecMethodType_id := 0";
		}
		$paramArray[] = "{$dataObjectName}_Day := :Timetable_Day";
		$paramString = implode(", ", $paramArray);
		$selectString = "
			{$dataObjectName}_id as \"{$dataObjectName}_id\"
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$sql = "
			select {$selectString}
			from p_{$dataObjectName}_ins(
			             person_id := :Person_id,
			             recclass_id := 3,
			             pmuser_id := :pmUser_id,
			    		 {$paramString}
			         );
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $params);
		if (!is_object($result)) {
			return $this->createError(null, 'Ошибка БД при создании бирки экстренного посещения пациентом врача!');
		}
		$resp = $result->result("array");
		if (!empty($resp[0]["{$dataObjectName}_id"])) {
			// отправка STOMP-сообщения
			sendFerStompMessage([
				"id" => $resp[0]["{$dataObjectName}_id"],
				"timeTable" => $dataObjectName,
				"action" => "AddTicket",
				"setDate" => date("c")
			], "Rule");
		}
		return $resp;
	}

	/**
	 * Удаление фактического времени посещения при удалении посещения
	 * Логичнее что этот метод вызывается после успешного удаления посещения, но сейчас при удалении посещения чистится ссылка TimetableGraf_id и этот метод не работает
	 * @param $data
	 * @return array|CI_DB_result|mixed
	 * @throws Exception
	 */
	function cancelVizitTime($data)
	{
		$TimetableGraf_id = 0;
		$is_recorded = null;
		$EvnVizit_id = 0;
		$vizit_object = $data["vizit_object"];
		//получить данные бирки по этому посещению
		$selectString = "
			TimetableGraf.TimetableGraf_id as \"TimetableGraf_id\",
			TimetableGraf.TimetableGraf_begTime as \"TimetableGraf_begTime\",
			EvnVizit.evn_id as \"EvnVizit_id\"
		";
		$query = "
			select {$selectString}
			from
				v_TimetableGraf_lite TimetableGraf
				inner join {$vizit_object} on {$vizit_object}.{$vizit_object}_id = :{$vizit_object}_id
				inner join EvnVizit on {$vizit_object}.{$vizit_object}_id = EvnVizit.evn_id and TimetableGraf.TimetableGraf_id = EvnVizit.TimetableGraf_id
			limit 1
		";
		/**
		 * @var CI_DB_result $result1
		 * @var CI_DB_result $result2
		 * @var CI_DB_result $result3
		 * @var CI_DB_result $result4
		 */
		$queryParams = ["{$vizit_object}_id" => $data["{$vizit_object}_id"]];
		$result1 = $this->db->query($query, $queryParams);
		if (!is_object($result1)) {
			return $this->createError(null, 'Ошибка при выполнении запроса к базе данных (получение данных о записи пациента на посещение поликлиники)');
		}
		$response = $result1->result("array");
		if (count($response) > 0 && !empty($response[0]["TimetableGraf_id"]) && !empty($response[0]["EvnVizit_id"])) {
			$TimetableGraf_id = $response[0]["TimetableGraf_id"];
			$EvnVizit_id = $response[0]["EvnVizit_id"];
			$is_recorded = empty($response[0]["TimetableGraf_begTime"]) ? false : true;
		}
		if ($EvnVizit_id > 0) {
			// Перед удалением бирки, нужно почистить EvnVizit.TimetableGraf_id (т.к. одна запись - одно посещение)
			$query = "
				update EvnVizit
				set TimetableGraf_id = NULL
				where evn_id = :EvnVizit_id
			";
			$queryParams = ["EvnVizit_id" => $EvnVizit_id];
			$result2 = $this->db->query($query, $queryParams);
			if ($result2 == false) {
				throw new Exception("Ошибка при выполнении запроса к базе данных (удаление связи посещения пациентом поликлиники без записи с биркой)");
			}
			$response = $result2;
		}
		if ($is_recorded === true && $TimetableGraf_id > 0) {
			// После удаления посещения нужно почистить TimetableGraf_factTime, если человек посещал по записи, чтобы на эту бирку можно было завести другое посещение.
			$query = "
				update TimetableGraf
				set TimetableGraf_factTime = null
				where TimetableGraf_id = :TimetableGraf_id
			";
			$queryParams = ["TimetableGraf_id" => $TimetableGraf_id];
			$result3 = $this->db->query($query, $queryParams);
			if ($result3 == false) {
				throw new Exception("Ошибка при выполнении запроса к базе данных (очистка времени фактического посещения)");
			}
			$response = $result3;
		}
		if ($is_recorded === false && $TimetableGraf_id > 0) {
			// После удаления посещения удалять бирку, если она создана на человека без записи.
			$query = "
				select
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_timetablegraf_del(
					timetablegraf_id := :TimetableGraf_id,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"TimetableGraf_id" => $TimetableGraf_id,
				"pmUser_id" => $data["pmUser_id"]
			];
			$result4 = $this->db->query($query, $queryParams);
			if (!is_object($result4)) {
				throw new Exception("Ошибка при выполнении запроса к базе данных (удаление записи о посещении пациентом поликлиники без записи)");
			}
			$response = $result4->result("array");
			if (!empty($response[0]["Error_Msg"])) {
				throw new Exception($response[0]["Error_Msg"]);
			}
			// отправка STOMP-сообщения
			sendFerStompMessage([
				"id" => $TimetableGraf_id,
				"timeTable" => "TimetableGraf",
				"action" => "DelTicket",
				"setDate" => date("c")
			], "Rule");
		}
		return $response;
	}

	/**
	 * Получение примечаний на бирку
	 * @param $data
	 * @return array
	 */
	function _getTimetableGrafFERAnnotation($data)
	{
		$resp = [];
		if (IsFerUser($data["pmUser_updID"])) {
			// получаем из TimetableExtend примечание по бирке, если её создал юзер ФЭР
			$sql = "
				select
					rtrim(te.TimetableExtend_Descr) as \"Annotation_Comment\",
					rtrim(pu.pmUser_Name) as \"pmUser_Name\",
					to_char(te.TimetableExtend_updDT, '{$this->dateTimeForm104} {$this->dateTimeForm108}') as \"Annotation_updDT\"
				from
					v_TimetableExtend te
					inner join v_pmUser pu on pu.pmUser_id = te.pmUser_updID
				where te.TimetableGraf_id = :TimetableGraf_id
			";
			$sqlParams = ["TimetableGraf_id" => $data["TimetableGraf_id"]];
			$resp_te = $this->queryResult($sql, $sqlParams);
			if (is_array($resp_te)) {
				$resp = array_merge($resp, $resp_te);
			}
		}
		return $resp;
	}

	/**
	 * Удаление бирки (поликлиника/параклиника/стационар)
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	function Delete($data)
	{
		if (isset($data["TimetableGraf_id"]) || isset($data["TimetableGrafGroup"])) {
			$data["object"] = "TimetableGraf";
		}
		if (isset($data["TimetableStac_id"]) || isset($data["TimetableStacGroup"])) {
			$data["object"] = "TimetableStac";
		}
		switch ($data["object"]) {
			case "TimetableGraf":
				return $this->DeleteTTG($data);
				break;
			case "TimetableStac":
				$this->load->model("TimetableStac_model", "ttsmodel");
				return $this->ttsmodel->DeleteTTS($data);
				break;
			default:
				return $this->createError(null, 'Неизвестная бирка.');
		}
	}

	/**
	 * Удаление бирки в поликлинике
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	function DeleteTTG($data)
	{
		$data["TimetableGrafGroup"] = (isset($data["TimetableGraf_id"])) ? [$data["TimetableGraf_id"]] : json_decode($data["TimetableGrafGroup"]);
		if (true !== ($res = $this->checkTimetablesFree($data))) {
			return $res;
		}
		// Получаем врача и список дней, на которые мы выделили бирки
		$TimetableGrafString = implode(", ", $data["TimetableGrafGroup"]);
		$sql = "
			select
				TimetableGraf_id as \"TimetableGraf_id\",
			    MedStaffFact_id as \"MedStaffFact_id\",
			    TimetableGraf_Day as \"TimetableGraf_Day\"
			from v_TimetableGraf_lite
			where TimetableGraf_id in ({$TimetableGrafString})
		";
		$res = $this->db->query($sql);
		if (!is_object($res)) {
			return false;
		}
		$res = $res->result("array");
		// Удаляем каждую бирку по отдельности. Не лучший вариант конечно
		foreach ($res as $row) {
			//Удаляем бирку
			$sql = "
				select
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_timetablegraf_del(
				    timetablegraf_id := :TimetableGraf_id,
				    pmuser_id := :pmUser_id
				);
			";
			$sqlParams = [
				"TimetableGraf_id" => $row["TimetableGraf_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$res = $this->db->query($sql, $sqlParams);
			if (is_object($res)) {
				$resp = $res->result("array");
				if (!is_array($resp) || count($resp) == 0 || !empty($resp[0]["Error_Msg"])) {
					return $resp;
				}
				// отправка STOMP-сообщения
				sendFerStompMessage([
					"id" => $row["TimetableGraf_id"],
					"timeTable" => "TimetableGraf",
					"action" => "DelTicket",
					"setDate" => date("c")
				], "Rule");
			}
		}
		return ["success" => true];
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) занятые бирки для поликлиники
	 * @param $data
	 * @return bool
	 */
	function checkTimetableGrafDayNotOccupied($data)
	{
		$res = null;
		if (isset($data["Day"])) {
			$sql = "
				SELECT count(*) as \"cnt\"
				FROM v_TimetableGraf_lite
				WHERE TimetableGraf_Day = :Day
				  and MedStaffFact_id = :MedStaffFact_id
				  and Person_id is not null
				  and TimetableGraf_begTime is not null
			";
			$sqlParams = [
				"Day" => $data["Day"],
				"MedStaffFact_id" => $data["MedStaffFact_id"],
			];
			$res = $this->db->query($sql, $sqlParams);
		}
		if (isset($data["StartDay"])) {
			$sql = "
				SELECT count(*) as \"cnt\"
				FROM v_TimetableGraf_lite
				WHERE TimetableGraf_day between :StartDay and :EndDay
				  and MedStaffFact_id = :MedStaffFact_id
				  and Person_id is not null
				  and TimetableGraf_begTime is not null
			";
			$sqlParams = [
				"StartDay" => $data["StartDay"],
				"EndDay" => $data["EndDay"],
				"MedStaffFact_id" => $data["MedStaffFact_id"],
			];
			$res = $this->db->query($sql, $sqlParams);
		}
		if (is_object($res)) {
			$res = $res->result("array");
		}
		if ($res[0]["cnt"] > 0) {
			return false;
		}
		return true;
	}

	/**
	 * Проверка, работает ли сотрудник в указанные дни
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function checkTimetableGrafTimeMsfIsWork($data)
	{
		if (isset($data["Day"])) {
			// Доп.бирка
			$date = date("Y-m-d", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"])));
			$params = [
				"StartDate" => $date,
				"EndDate" => $date
			];
		} elseif ($data["ScheduleCreationType"] == 2) {
			// Копирование
			$params = [
				"StartDate" => $data["CopyToDateRange"][0],
				"EndDate" => $data["CopyToDateRange"][1]
			];
		} else {
			// Создание
			$params = [
				"StartDate" => $data["CreateDateRange"][0],
				"EndDate" => $data["CreateDateRange"][1]
			];
		}
		$params["MedStaffFact_id"] = $data["MedStaffFact_id"];
		$sql = "
			select count(*) as \"cnt\"
			from v_MedStaffFact
			where MedStaffFact_id = :MedStaffFact_id
			  and WorkData_begDate <= :StartDate
			  and coalesce(WorkData_endDate, '2030-01-01'::date) >= :EndDate
		";
		$res = $this->db->query($sql, $params);
		if (is_object($res)) {
			$res = $res->result("array");
		}
		if ($res[0]["cnt"] == 0) {
			$errtext = isset($data["Day"])
				? "Добавление дополнительной бирки невозможно: дата, на которую добавляется бирка, не входит в период работы сотрудника"
				: "Создание расписания невозможно: дата начала/окончания расписания не входит в период работы сотрудника";
			return $this->createError(null, $errtext);
		}
		return true;
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) и интервале времени занятые бирки для поликлиники
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function checkTimetableGrafTimeNotOccupied($data)
	{
		if (isset($data["Day"])) {
			$sql = "
				select count(*) as \"cnt\"
				from v_TimetableGraf_lite
				where MedStaffFact_id = :MedStaffFact_id
				  and Person_id is not null
				  and (
						(TimetableGraf_begTime >= :StartTime and TimetableGraf_begTime < :EndTime) or
						((CAST(TimetableGraf_begTime as date) + CAST(TimetableGraf_Time||' minutes' as interval)) > :StartTime and (CAST(TimetableGraf_begTime as date) + CAST(TimetableGraf_Time||' minutes' as interval)) < :EndTime) or
						((CAST(TimetableGraf_begTime as date) + CAST(TimetableGraf_Time||' minutes' as interval)) > :StartTime and TimetableGraf_begTime < :StartTime)
				  )
			";
			$sqlParams = [
				"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
				"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
				"MedStaffFact_id" => $data["MedStaffFact_id"]
			];
			$res = $this->db->query($sql, $sqlParams);
			if (is_object($res)) {
				$res = $res->result("array");
			}
			if ($res[0]["cnt"] > 0) {
				return array(
					'Error_Msg' => 'Нельзя очистить расписание, так как есть занятые бирки.'
				);
			}
		}
		if (isset($data["StartDay"])) {
			//Если задано несколько дней - проходим в цикле
			for ($day = $data["StartDay"]; $day <= $data["EndDay"]; $day++) {
				$data["Day"] = $day;
				$sql = "
					select count(*) as \"cnt\"
					from v_TimetableGraf_lite
					where MedStaffFact_id = :MedStaffFact_id
					  and Person_id is not null
					  and (
							(TimetableGraf_begTime >= :StartTime and TimetableGraf_begTime < :EndTime) or
							((CAST(TimetableGraf_begTime as date) + CAST(TimetableGraf_Time||' minutes' as interval)) > :StartTime and (CAST(TimetableGraf_begTime as date) + CAST(TimetableGraf_Time||' minutes' as interval)) < :EndTime) or
							((CAST(TimetableGraf_begTime as date) + CAST(TimetableGraf_Time||' minutes' as interval)) > :StartTime and TimetableGraf_begTime < :StartTime)
					  )
				";
				$sqlParams = [
					"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
					"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
					"MedStaffFact_id" => $data["MedStaffFact_id"]
				];
				$res = $this->db->query($sql, $sqlParams);
				if (is_object($res)) {
					$res = $res->result("array");
				}
				if ($res[0]["cnt"] > 0) {
					return array(
						'Error_Msg' => 'Нельзя очистить расписание, так как есть занятые бирки.'
					);
				}
			}
		}
		return true;
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) и интервале времени созданные бирки для поликлиники
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function checkTimetableGrafTimeNotExists($data)
	{
		if (isset($data["Day"])) {
			$sql = "
				select count(*) as \"cnt\"
				from v_TimetableGraf_lite
				where MedStaffFact_id = :MedStaffFact_id
				  and (
				      (TimetableGraf_begTime >= :StartTime and TimetableGraf_begTime < :EndTime) or
				      ((CAST(TimetableGraf_begTime as date) + CAST(TimetableGraf_Time||' minutes' as interval)) > :StartTime and (CAST(TimetableGraf_begTime as date) + CAST(TimetableGraf_Time||' minutes' as interval)) < :EndTime) or
				      ((CAST(TimetableGraf_begTime as date) + CAST(TimetableGraf_Time||' minutes' as interval)) > :StartTime and TimetableGraf_begTime < :StartTime)
					)
			";
			$sqlParams = [
				"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
				"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
				"MedStaffFact_id" => $data["MedStaffFact_id"]
			];
			$res = $this->db->query($sql, $sqlParams);
			if (is_object($res)) {
				$res = $res->result("array");
			}
			if ($res[0]["cnt"] > 0) {
				return array(
					'Error_Msg' => 'В заданном интервале времени уже существуют бирки.'
				);
			}
		}
		if (isset($data["StartDay"])) {
			//Если задано несколько дней - проходим в цикле
			for ($day = $data["StartDay"]; $day <= $data["EndDay"]; $day++) {
				$data["Day"] = $day;
				$sql = "
					select count(*) as \"cnt\"
					from v_TimetableGraf_lite
					where MedStaffFact_id = :MedStaffFact_id
					  and (
					      (TimetableGraf_begTime >= :StartTime and TimetableGraf_begTime < :EndTime) or
					      ((CAST(TimetableGraf_begTime as date) + CAST(TimetableGraf_Time||' minutes' as interval)) > :StartTime and (CAST(TimetableGraf_begTime as date) + CAST(TimetableGraf_Time||' minutes' as interval)) < :EndTime) or
					      ((CAST(TimetableGraf_begTime as date) + CAST(TimetableGraf_Time||' minutes' as interval)) > :StartTime and TimetableGraf_begTime < :StartTime)
					  )
				";
				$sqlParams = [
					"StartTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"]))),
					"EndTime" => date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["EndTime"]))),
					"MedStaffFact_id" => $data["MedStaffFact_id"]
				];
				$res = $this->db->query($sql, $sqlParams);
				if (is_object($res)) {
					$res = $res->result("array");
				}
				if ($res[0]["cnt"] > 0) {
					return array(
						'Error_Msg' => 'В заданном интервале времени уже существуют бирки.'
					);
				}
			}
		}
		return true;
	}

	/**
	 * Проверка, что врач принимает по живой очереди
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function checkMsfIsLifeQueue($data)
	{
		$sql = "
			select count(*) as \"cnt\"
			from v_MedStaffFact
			where MedStaffFact_id = :MedStaffFact_id
			  and RecType_id = 3
		";
		$sqlParams = ["MedStaffFact_id" => $data["MedStaffFact_id"]];
		$res = $this->db->query($sql, $sqlParams);
		if (is_object($res)) {
			$res = $res->result("array");
		}
		if ($res[0]["cnt"] > 0) {
			return $this->createError(null, 'При создании расписания для места работы с типом записи «По живой очереди» возможно добавить только бирки с типом «Живая очередь»');
		}
		return true;
	}

	/**
	 * Очистка дня
	 * @param $data
	 * @return array
	 */
	function ClearDay($data)
	{
		// Не выдаём ошибку, что есть занятые бирки, вместо этого хранимка будет удалять только свободные бирки
		return $this->ClearDayTTG($data);
	}

	/**
	 * Очистка дня для поликлиники
	 * @param $data
	 * @return array
	 */
	function ClearDayTTG($data)
	{
		$sql = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_timetablegraf_clearday(
				timetablegraf_day := :TimetableGraf_Day,
			    medstafffact_id := :MedStaffFact_id,
			    pmuser_id := :pmUser_id
			);
		";
		$sqlParams = [
			"MedStaffFact_id" => $data["MedStaffFact_id"],
			"TimetableGraf_Day" => $data["Day"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$res = $this->db->query($sql, $sqlParams);
		if (is_object($res)) {
			$resp = $res->result("array");
			if (count($resp) > 0 && empty($resp[0]["Error_Msg"])) {
				// отправка STOMP-сообщения
				sendFerStompMessage([
					"timeTable" => "TimetableGraf",
					"action" => "DelTicket",
					"setDate" => date("c"),
					"begDate" => date("c", DayMinuteToTime($data["Day"], 0)),
					"endDate" => date("c", DayMinuteToTime($data["Day"], 0)),
					"MedStaffFact_id" => $data["MedStaffFact_id"]
				], "RulePeriod");
			}
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Создания расписания в поликлинике
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function createTTGSchedule($data)
	{
		$this->beginTransaction();
		if ($data["CreateAnnotation"] == 1) {
			$this->load->model("Annotation_model");
			$annotation_data = $data;
			$annotation_data["Annotation_id"] = null;
			$annotation_data["MedService_id"] = null;
			$annotation_data["Resource_id"] = null;
			$annotation_data["Annotation_begDate"] = $data["CreateDateRange"][0];
			$annotation_data["Annotation_endDate"] = $data["CreateDateRange"][1];
			$annotation_data["Annotation_begTime"] = $data["StartTime"];
			$annotation_data["Annotation_endTime"] = $data["EndTime"];
			$res = $this->Annotation_model->save($annotation_data);
			if (!empty($res["Error_Msg"])) {
				$this->rollbackTransaction();
				return $res;
			}
		}
		$data["StartDay"] = TimeToDay(strtotime($data["CreateDateRange"][0]));
		$data["EndDay"] = TimeToDay(strtotime($data["CreateDateRange"][1]));
		$archive_database_enable = $this->config->item("archive_database_enable");
		if (!empty($archive_database_enable)) {
			$archive_database_date = $this->config->item("archive_database_date");
			if (strtotime($data["CreateDateRange"][0]) < strtotime($archive_database_date)) {
				return $this->createError(null, 'Нельзя создать расписание на архивные даты.');
			}
		}
		if (strtotime($data["CreateDateRange"][0]) < strtotime(date("d.m.Y"))) {
			return $this->createError(null, 'Создание расписания на прошедшие периоды невозможно');
		}
		if (true !== ($res = $this->checkTimetableGrafTimeMsfIsWork($data))) {
			return $res;
		}
		if (true !== ($res = $this->checkTimetableGrafTimeNotOccupied($data))) {
			return $res;
		}
		if (true !== ($res = $this->checkTimetableGrafTimeNotExists($data))) {
			return $res;
		}
		if (12 != $data["TimetableType_id"] && true !== ($res = $this->checkMsfIsLifeQueue($data))) {
			return $res;
		}
		$ttgArray = [];
		$nStartTime = StringToTime($data["StartTime"]);
		$nEndTime = StringToTime($data["EndTime"]);
		for ($day = $data["StartDay"]; $day <= $data["EndDay"]; $day++) {
			$data["Day"] = $day;
			// Вставляем циклом бирки
			for ($nTime = $nStartTime; $nTime < $nEndTime; $nTime += $data["Duration"]) {
				// Для групповых бирок нужен флаг множественности
				if (!empty($data["TimetableType_id"]) && $data["TimetableType_id"] == 14) {
					$data["TimeTableGraf_IsMultiRec"] = 2;
				}
				$sql = "
					insert into TimetableGraf (
						MedStaffFact_id,
						Person_id,
						TimetableGraf_Day,
						TimetableGraf_begTime,
						TimetableGraf_factTime,
						TimetableGraf_Time,
						TimetableGraf_IsDop,
						TimetableGraf_IsModerated,
						RecClass_id,
						TimetableType_id,
						Evn_id,
						TimetableGraf_Mark,
						TimetableGraf_Guid,
						pmUser_insID,
						pmUser_updID,
						TimetableGraf_insDT,
						TimetableGraf_updDT,
						TimeTableGraf_IsMultiRec,
						TimeTableGraf_PersRecLim
					)
					values (
						:MedStaffFact_id,
						null,
						:TimetableGraf_Day,
						:TimetableGraf_begTime,
						null,
						:TimetableGraf_Time,
						null,
						null,
						null,
						:TimetableType_id,
						null,
						null,
						newid(),
						:pmUser_id,
						:pmUser_id,
						getdate(),
						getdate(),
						:TimeTableGraf_IsMultiRec,
						:TimeTableGraf_PersRecLim
					);
					select currval('timetablegraf_timetablegraf_id_seq') as \"TimetableGraf_id\";
				";
				$TimetableGraf_Guid = "";
				$sqlParams = [
					"MedStaffFact_id" => $data["MedStaffFact_id"],
					"TimetableGraf_Day" => $day,
					"TimetableGraf_begTime" => date("Y-m-d H:i:s", DayMinuteToTime($day, $nTime)),
					"TimetableGraf_Time" => $data["Duration"],
					"pmUser_id" => $data["pmUser_id"],
					"TimetableType_id" => $data["TimetableType_id"],
					"TimeTableGraf_IsMultiRec" => (!empty($data["TimeTableGraf_IsMultiRec"]) ? $data["TimeTableGraf_IsMultiRec"] : null),
					"TimeTableGraf_PersRecLim" => (!empty($data["TimeTableGraf_PersRecLim"]) ? $data["TimeTableGraf_PersRecLim"] : null),
				];
				$resp = $this->queryResult($sql, $sqlParams);
				if (empty($resp[0]["TimetableGraf_id"])) {
					$this->rollbackTransaction();
					return $this->createError(null, 'Ошибка при вставке бирки');
				}
				$ttgArray[] = $resp[0]["TimetableGraf_id"];
			}
			// Обновляем кэш по дню
			$sql = "
				select
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_medpersonalday_recount(
				    medstafffact_id := :MedStaffFact_id,
				    day_id := :TimetableGraf_Day,
				    pmuser_id := :pmUser_id
				);
			";
			$sqlParams = [
				"MedStaffFact_id" => $data["MedStaffFact_id"],
				"TimetableGraf_Day" => $day,
				"pmUser_id" => $data["pmUser_id"]
			];
			$this->db->query($sql, $sqlParams);
		}
		$ttg_list = $ttgArray;
		// Вставляем данные в историю (аналог p_AddTTGToHistory).
		while (count($ttgArray) > 0) {
			// берём пачками, чтобы идешников не получилось слишком много.
			$ttgIds = array_splice($ttgArray, 0, 1000);
			if (getRegionNick() == "ekb") {
				$ttgIdsString = implode("','", $ttgIds);
				$selectString = "
					select TimeTableGraf_id
					      ,(
					        select Lpu_id
					        from v_MedStaffFact
					        where MedStaffFact_id=:MedStaffFact_id
					        limit 1
					       )
					      ,MedStaffFact_id
					      ,Person_id
					      ,TimeTableGraf_Day
					      ,TimeTableGraf_begTime
					      ,TimetableGraf_factTime
					      ,TimetableGraf_Time
					      ,RecClass_id
					      ,TimeTableType_id
					      ,EvnDirection_id
					      ,1
					      ,pmUser_insID
					      ,pmUser_updID
					      ,TimeTableGraf_insDT
					      ,TimeTableGraf_updDT
					from v_TimetableGraf_lite
					where TimetableGraf_id in ('{$ttgIdsString}')
				";
				$sql = "
					insert into TimeTableGrafHistMIS(
						TimeTableGraf_id,
						Lpu_id,
						MedStaffFact_id,
						Person_id,
						TimeTableGraf_Day,
						TimeTableGraf_begTime,
						TimeTableGraf_factTime,
						TimeTableGraf_Time,
						RecClass_id,
						TimeTableType_id,
						EvnDirection_id,
						TimeTableGrafAction_id,
						pmUser_insID,
						pmUser_updID,
						TimeTableGraf_insDT,
						TimeTableGraf_updDT
					)
					{$selectString}
				";
				$sqlParams = [
					"MedStaffFact_id" => $data["MedStaffFact_id"]
				];
				$this->db->query($sql, $sqlParams);
			}
		}
		$this->commitTransaction();
		// отправка STOMP-сообщения
		sendFerStompMessage([
			"timeTable" => "TimetableGraf",
			"action" => "AddTicket",
			"setDate" => date("c"),
			"begDate" => date("c", DayMinuteToTime($data["StartDay"], $nStartTime)),
			"endDate" => date("c", DayMinuteToTime($data["EndDay"], $nEndTime)),
			"MedStaffFact_id" => $data["MedStaffFact_id"]
		], "RulePeriod");
		$response = ["Error_Msg" => ""];
		if (!empty($data["fromApi"]) && !empty($ttg_list)) {
			$response["ttg_list"] = $ttg_list;
		}
		return $response;
	}

	/**
	 * Копирование расписания в поликлинике
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	function copyTTGSchedule($data)
	{
		if (empty($data["CopyToDateRange"][0])) {
			return $this->createError(null, 'Не указан диапазон для вставки расписания.');
		}
		$this->beginTransaction();
		if (count($data["copyAnnotationGridData"])) {
			$this->load->model("Annotation_model");
			$annotation_data = $data;
		}
		$data["StartDay"] = TimeToDay(strtotime($data["CopyToDateRange"][0]));
		$data["EndDay"] = TimeToDay(strtotime($data["CopyToDateRange"][1]));
		$archive_database_enable = $this->config->item("archive_database_enable");
		if (!empty($archive_database_enable)) {
			$archive_database_date = $this->config->item("archive_database_date");
			if (strtotime($data["CreateDateRange"][1]) < strtotime($archive_database_date)) {
				return $this->createError(null, 'Нельзя скопировать расписание на архивные даты.');
			}
		}
		if (true !== ($res = $this->checkTimetableGrafTimeMsfIsWork($data))) {
			return $res;
		}
		if (true !== ($res = $this->checkTimetableGrafDayNotOccupied($data))) {
			return $this->createError(null, 'Нельзя скопировать расписание на промежуток, так как на нем занятые бирки.');
		}
		$n = 0;
		$nShift = TimeToDay(strtotime($data["CreateDateRange"][1])) - TimeToDay(strtotime($data["CreateDateRange"][0])) + 1;
		$nTargetEnd = 0;
		while ($nTargetEnd < $data["EndDay"]) {
			$nTargetStart = $data["StartDay"] + $nShift * $n;
			$nTargetEnd = $data["StartDay"] + $nShift * ($n + 1) - 1;
			$nTargetEnd = min($nTargetEnd, $data["EndDay"]);

			$SourceStartDay = TimeToDay(strtotime($data["CreateDateRange"][0]));
			$SourceEndDay = TimeToDay(strtotime($data["CreateDateRange"][1]));
			$SourceEndDay = min($SourceEndDay, (TimeToDay(strtotime($data["CreateDateRange"][0])) + $nTargetEnd - $nTargetStart));
			if (count($data["copyAnnotationGridData"])) {
				$annotation_data["Annotation_copyFromDate"] = date("Y-m-d", strtotime($data["CreateDateRange"][0])); // Начало копируемого интервала
				$annotation_data["Annotation_begDate"] = date("Y-m-d", strtotime($data["CopyToDateRange"][0]) + 86400 * $nShift * $n); // Начало целевого интервала
				$annotation_data["Annotation_endDate"] = date("Y-m-d", strtotime($data["CopyToDateRange"][0]) + 86400 * $nShift * ($n + 1) - 86400); // Окончание целевого интервала
				$annotation_data["Annotation_endDate"] = min($annotation_data["Annotation_endDate"], date("Y-m-d", strtotime($data["CopyToDateRange"][1])));
				foreach ($data["copyAnnotationGridData"] as $annotation_id) {
					$annotation_data["Annotation_id"] = $annotation_id;
					$res = $this->Annotation_model->copy($annotation_data);
					if (!empty($res["Error_Msg"])) {
						$this->rollbackTransaction();
						return $res;
					}
				}
			}
			$sqlParams = [
				"MedStaffFact_id" => $data["MedStaffFact_id"],
				"SourceStartDay" => $SourceStartDay,
				"SourceEndDay" => $SourceEndDay,
				"TargetStartDay" => $nTargetStart,
				"TargetEndDay" => $nTargetEnd,
				"CopyTimetableExtend_Descr" => NULL,
				"pmUser_id" => $data["pmUser_id"]
			];
			$sql = "
				select
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_timetablegraf_copy(
				             medstafffact_id := :MedStaffFact_id,
				             sourcestartday := :SourceStartDay,
				             sourceendday := :SourceEndDay,
				             targetstartday := :TargetStartDay,
				             targetendday := :TargetEndDay,
				             copytimetableextend_descr := :CopyTimetableExtend_Descr,
				             pmuser_id := :pmUser_id
				         );
			";
			$res = $this->db->query($sql, $sqlParams);
			if (is_object($res)) {
				$resp = $res->result("array");
				$this->commitTransaction();
				if (count($resp) > 0 && empty($resp[0]["Error_Msg"])) {
					// отправка STOMP-сообщения
					sendFerStompMessage([
						"timeTable" => "TimetableGraf",
						"action" => "AddTicket",
						"setDate" => date("c"),
						"begDate" => date("c", DayMinuteToTime($nTargetStart, 0)),
						"endDate" => date("c", DayMinuteToTime($nTargetEnd, 0)),
						"MedStaffFact_id" => $data["MedStaffFact_id"]
					], "RulePeriod");
				}
			}
			$n++;
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Добавление дополнительной бирки в поликлинике
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	function addTTGDop($data)
	{
		if (!empty($data["StartTime"])) {
			$archive_database_enable = $this->config->item("archive_database_enable");
			if (!empty($archive_database_enable)) {
				$archive_database_date = $this->config->item("archive_database_date");
				if (DayMinuteToTime($data["Day"], StringToTime($data["StartTime"])) < strtotime($archive_database_date)) {
					return $this->createError(null, 'Нельзя создать дополнительную бирку на архивную дату.');
				}
			}
			$date = date("Y-m-d H:i:s", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"])));
			$sql = "
				select count(*) as \"cnt\"
				from v_TimetableGraf_lite
				where MedStaffFact_id = :MedStaffFact_id
				  and TimetableGraf_begTime > (CAST(:StartTime as date) + CAST('-2 minutes' as interval))
				  and TimetableGraf_begTime < (CAST(:StartTime as date) + CAST('2 minutes' as interval))
				  and TimetableGraf_begTime is not null
			";
			$sqlParams = [
				"MedStaffFact_id" => $data["MedStaffFact_id"],
				"StartTime" => $date
			];
			$res = $this->db->query($sql, $sqlParams);
			if (!is_object($res)) {
				return false;
			}
		} else {
			// Если бирка создается на текущее время, проверка на то что она отстоит от других бирок не происходит
			$date = date("Y-m-d H:i:s", time());
		}
		if (true !== ($res = $this->checkTimetableGrafTimeMsfIsWork($data))) {
			return $res;
		}
		$this->beginTransaction();
		if ($data["CreateAnnotation"] == 1) {
			$this->load->model("Annotation_model");
			$annotation_data = $data;
			$annotation_data["Annotation_id"] = null;
			$annotation_data["MedService_id"] = null;
			$annotation_data["Resource_id"] = null;
			$annotation_data["Annotation_begDate"] = date("Y-m-d", DayMinuteToTime($data["Day"], 0));
			$annotation_data["Annotation_endDate"] = date("Y-m-d", DayMinuteToTime($data["Day"], 0));
			$annotation_data["Annotation_begTime"] = $data["StartTime"];
			$annotation_data["Annotation_endTime"] = date("H:i", DayMinuteToTime($data["Day"], StringToTime($data["StartTime"])) + 60);
			$ares = $this->Annotation_model->save($annotation_data);
			if (!empty($ares["Error_Msg"])) {
				$this->rollbackTransaction();
				return $ares;
			}
		}
		if ($res[0]["cnt"] != 0) {
			return $this->createError(null, 'Дополнительная бирка должна отстоять не менее чем на 2 минуты от существующих. Выберите другое время или удалите бирки.');
		}
		$sql = "
			select
				timetablegraf_id as \"TimetableGraf_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_timetablegraf_ins(
			    medstafffact_id := :MedStaffFact_id,
			    timetablegraf_day := :TimetableGraf_Day,
			    timetablegraf_begtime := :TimetableGraf_begTime,
			    timetablegraf_time := :TimetableGraf_Time,
			    timetablegraf_isdop := 1,
			    timetabletype_id := 1,
				pmuser_id := :pmUser_id
			);
		";
		$sqlParams = [
			"MedStaffFact_id" => $data["MedStaffFact_id"],
			"TimetableGraf_Day" => $data["Day"],
			"TimetableGraf_begTime" => $date,
			"TimetableGraf_Time" => 0,
			"pmUser_id" => $data["pmUser_id"]
		];
		$res = $this->db->query($sql, $sqlParams);
		if (!is_object($res)) {
			return $this->createError(null, 'Ошибка добавления бирки');
		}
		$resp = $res->result("array");
		if (!$this->isSuccessful($resp)) {
			return $this->createError(null, 'Ошибка добавления бирки');
		}
		$this->commitTransaction();
		if (empty($resp[0]["TimetableGraf_id"])) {
			return $this->createError(null, 'Ошибка добавления бирки');
		}
		// отправка STOMP-сообщения
		sendFerStompMessage([
			"id" => $resp[0]["TimetableGraf_id"],
			"timeTable" => "TimetableGraf",
			"action" => "AddTicket",
			"setDate" => date("c")
		], "Rule");
		return ["Error_Msg" => "", "TimetableGraf_id" => $resp[0]["TimetableGraf_id"]];
	}

	/**
	 * Удаление бирки незапланированного приема в поликлинике
	 * @param $data
	 * @return bool
	 */
	function rollbackUnScheduled($data)
	{
		if (empty($data["Unscheduled"])) {
			return true;
		}
		if ("polka" != $data["LpuUnitType_SysNick"]) {
			return true;
		}
		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_timetablegraf_del(
			    timetablegraf_id := :TimetableGraf_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"TimetableGraf_id" => $data["TimetableGraf_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$this->db->query($query, $queryParams);
		return true;
	}

	/**
	 * Добавление незапланированного приема в поликлинике
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function addTTGUnscheduled($data)
	{
		$sql = "
			select
				timetablegraf_id as \"TimetableGraf_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_timetablegraf_ins(
				medstafffact_id := :MedStaffFact_id,
			    timetablegraf_day := :TimetableGraf_Day,
			    timetablegraf_begtime := :TimetableGraf_begTime,
			    timetablegraf_facttime := null,
			    timetablegraf_time := :TimetableGraf_Time,
			    timetablegraf_isdop := 1,
			    timetabletype_id := 1,
			    pmuser_id := :pmUser_id
			);
		";
		$sqlParams = [
			"MedStaffFact_id" => $data["MedStaffFact_id"],
			"TimetableGraf_Day" => $data["Day"],
			"TimetableGraf_begTime" => date("Y-m-d H:i:s", time()),
			"TimetableGraf_Time" => 0,
			"pmUser_id" => $data["pmUser_id"]
		];
		$res = $this->db->query($sql, $sqlParams);
		if (!is_object($res)) {
			return $this->createError(null, 'Ошибка добавления бирки');
		}
		$res = $res->result("array");
		if (empty($res[0]["TimetableGraf_id"])) {
			return $this->createError(null, 'Ошибка добавления бирки');
		}
		// отправка STOMP-сообщения
		sendFerStompMessage([
			"id" => $res[0]["TimetableGraf_id"],
			"timeTable" => "TimetableGraf",
			"action" => "AddTicket",
			"setDate" => date("c")
		], "Rule");
		return ["Error_Msg" => "", "TimetableGraf_id" => $res[0]["TimetableGraf_id"]];
	}

	/**
	 * Получение названия действия для отправки в ФЭР
	 * @param $TimetableType_SysNick
	 * @return string
	 */
	function defineActionTypeByTimetableType($TimetableType_SysNick)
	{
		$action = "";
		switch ($TimetableType_SysNick) {
			case "free":
				$action = "ChType_NormalTicket";
				break;
			case "reserved":
				$action = "ChType_ReservTicket";
				break;
			case "pay":
				$action = "ChType_PaidTicket";
				break;
			case "vet":
				$action = "ChType_VeteranTicket";
				break;
			case "extr":
				$action = "ChType_OutTicket";
				break;
			case "emerg":
				$action = "ChType_ExtraBed";
				break;
			case "bed":
				$action = "ChType_NormalBed";
				break;
		}
		return $action;
	}

	/**
	 * Изменение типа бирки(бирок) в поликлинике
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function setTTGType($data)
	{
		/**@var CI_DB_result $res */
		$data["object"] = "TimetableGraf";
		$data["TimetableGrafGroup"] = (isset($data["TimetableGrafGroup"])) ? json_decode($data["TimetableGrafGroup"]) : $data["TimetableGrafGroup"];
		if (count(@$data["TimetableGrafGroup"]) > 0) {
			// Обработка группы бирок в отдельном методе
			return $this->setTTGTypeGroup($data);
		} elseif (true === ($res = $this->checkTimetableOccupied($data, true))) {
			throw new Exception("Бирка занята, изменение типа невозможно.");
		}
		// Получаем врача и день, а также заодно проверяем, что бирка существует
		$sql = "
			select
				MedStaffFact_id as \"MedStaffFact_id\",
			    TimetableGraf_Day as \"TimetableGraf_Day\"
			from v_TimetableGraf_lite
			where TimetableGraf_id = :TimetableGraf_id
        ";
		$sqlParams = ["TimetableGraf_id" => $data["TimetableGraf_id"]];
		$res = $this->db->query($sql, $sqlParams);
		if (!is_object($res)) {
			return false;
		}
		$res = $res->result("array");
		if (!isset($res[0])) {
			throw new Exception("Бирка с таким идентификатором не существует.");
		}
		$sqlParams = [
			"TimetableGraf_id" => $data["TimetableGraf_id"],
			"TimetableType_id" => $data["TimetableType_id"],
			"TimetableType_id50" => $data["TimetableType_id"] + 50,
			"MedStaffFact_id" => $res[0]["MedStaffFact_id"],
			"Day_id" => $res[0]["TimetableGraf_Day"],
			"pmUser_id" => ($data["pmUser_id"] != 0) ? $data["pmUser_id"] : null
		];
		$sql = "
            update TimetableGraf
            set
            	TimeTableType_id = :TimetableType_id,
            	pmUser_updID = :pmUser_id,
            	TimeTableGraf_updDT = getdate()
            where TimetableGraf_id = :TimetableGraf_id;
        ";
		if ($data["TimetableType_id"] == 14) {
			$sql .= "
                update TimetableGraf
                set
                	TimeTableGraf_PersRecLim = 100,
                    TimeTableGraf_IsMultiRec = 2,
                	pmUser_updID = :pmUser_id,
                    TimeTableGraf_updDT = getdate()
                where TimetableGraf_id = :TimetableGraf_id;
            ";
		} else {
			$sql .= "
                update TimetableGraf
                set
                	TimeTableGraf_PersRecLim = 0,
                    TimeTableGraf_IsMultiRec = 1,
                    pmUser_updID = :pmUser_id,
                    TimeTableGraf_updDT = getdate()
                where TimetableGraf_id = :TimetableGraf_id;
            ";
		}
		$sql .= "
			select *
			from p_addttgtohistory(
			    		timetablegraf_id := :TimetableGraf_id,
			    		timetablegrafaction_id := :TimetableType_id50,
			    		pmuser_id := :pmUser_id
			    	 );
			select *
			from p_medpersonalday_recount(
			             medstafffact_id := :MedStaffFact_id,
			             day_id := :Day_id,
			             pmuser_id := :pmUser_id
			         );
		";
		$this->db->query($sql, $sqlParams);
		$ttType = $this->getFirstRowFromQuery("select TimetableType_SysNick as \"TimetableType_SysNick\", TimetableType_Name  as \"TimetableType_Name\" from v_TimetableType where TimeTableType_id=?", [$data["TimetableType_id"]]);
		if (!empty($ttType["TimetableType_SysNick"])) {
			$action = $this->defineActionTypeByTimetableType($ttType["TimetableType_SysNick"]);
			if (!empty($action)) {
				// отправка STOMP-сообщения
				sendFerStompMessage([
					"id" => $data["TimetableGraf_id"],
					"timeTable" => "TimetableGraf",
					"action" => $action,
					"setDate" => date("c")
				], "Rule");
			}
		}
		return ["TimetableType_Name" => $ttType["TimetableType_Name"], "Error_Msg" => ""];
	}

	/**
	 * Изменение типа бирок в поликлинике для группы бирок
	 * @param $data
	 * @return array|bool|CI_DB_result
	 * @throws Exception
	 */
	function setTTGTypeGroup($data)
	{
		/**@var CI_DB_result $result */
		if (true !== ($res = $this->checkTimetablesFree($data))) {
			throw new Exception(@$res["Error_Msg"]);
		}
		// Получаем врача и список дней, на которые мы выделили бирки
		$TimetableGrafGroupString = implode(",", $data["TimetableGrafGroup"]);
		$sql = "
			select TimetableGraf_id as \"TimetableGraf_id\"
			      ,MedStaffFact_id as \"MedStaffFact_id\"
			      ,TimetableGraf_Day as \"TimetableGraf_Day\"
			from v_TimetableGraf_lite
			where TimetableGraf_id in ({$TimetableGrafGroupString})
        ";
		$result = $this->db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");

		$sql = "";
		$sqlParams = [
			"TimetableType_id" => $data["TimetableType_id"],
			"pmUser_id" => $data["pmUser_id"],
			"TimetableType_id50" => $data["TimetableType_id"] + 50,
		];
		$sendData = [];
		foreach ($result as $row) {
			$TimetableGraf_id = $row["TimetableGraf_id"];
			$MedStaffFact_id = $row["MedStaffFact_id"];
			$Day_id = $row["TimetableGraf_Day"];

			$sql .= "
                update TimetableGraf
                set	TimeTableType_id = :TimetableType_id
                   ,pmUser_updID = :pmUser_id
                   ,TimeTableGraf_updDT = getdate()
                where TimetableGraf_id = {$TimetableGraf_id};
            ";
			if ($data["TimetableType_id"] == 14) {
				$sql .= "
                    update TimetableGraf
                    set	TimeTableGraf_PersRecLim = 100
                       ,TimeTableGraf_IsMultiRec = 2
                       ,pmUser_updID = :pmUser_id
                       ,TimeTableGraf_updDT = getdate()
                    where TimetableGraf_id = {$TimetableGraf_id};
                ";
			} else {
				$sql .= "
                    update TimetableGraf
                    set	TimeTableGraf_PersRecLim=0
                       ,TimeTableGraf_IsMultiRec = 1
                       ,pmUser_updID = :pmUser_id
                       ,TimeTableGraf_updDT = getdate()
                    where TimetableGraf_id = {$TimetableGraf_id};
                ";
			}
			$sql .= "
				select *
				from p_addttgtohistory(
				             timetablegraf_id := {$TimetableGraf_id},
				             timetablegrafaction_id := :TimetableType_id50,
				             pmuser_id := :pmUser_id
				         );
				select *
				from p_medpersonalday_recount(
				             medstafffact_id := {$MedStaffFact_id},
				             day_id := {$Day_id},
				             pmuser_id := :pmUser_id
				         );
			";
			$sendData[] = $TimetableGraf_id;
		}
		$this->db->query($sql, $sqlParams);
		$ttType = $this->getFirstRowFromQuery("select TimetableType_SysNick as \"TimetableType_SysNick\", TimetableType_Name  as \"TimetableType_Name\" from v_TimetableType where TimeTableType_id=?", [$data["TimetableType_id"]]);
		if (!empty($ttType["TimetableType_SysNick"])) {
			$action = $this->defineActionTypeByTimetableType($ttType["TimetableType_SysNick"]);
			if (!empty($action)) {
				// отправка STOMP-сообщения
				foreach ($sendData as $sendDataItem) {
					sendFerStompMessage([
						"id" => $sendDataItem,
						"timeTable" => "TimetableGraf",
						"action" => $action,
						"setDate" => date("c")
					], "Rule");
				}
			}
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Функция связи идентификатора человека с биркой из фер
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function updatePersonForFerRecord($data)
	{
		$query = "
			select Person_id as \"Person_id\"
			from v_TimetableGraf_lite
			where TimetableGraf_id = :TimetableGraf_id
		";
		$result = $this->db->query($query, $data);
		$FER_PERSON_ID = $this->config->item("FER_PERSON_ID");
		if (is_object($result)) {
			$resp = $result->result("array");
			if (!empty($resp[0]["Person_id"]) && $resp[0]["Person_id"] != $FER_PERSON_ID) {
				return $this->createError(null, 'Пациент записанный на бирку не явялется записью ФЭР');
			}
		}
		$query = "
			update TimetableGraf
			set Person_id = :Person_id
			where TimetableGraf_id = :TimetableGraf_id
		";
		$this->db->query($query, $data);
		return ["Error_Msg" => ""];
	}

	/**
	 * Отметка на бирке о явке/неявке
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function setPersonMark($data)
	{
		$sql = "
			select
				timetablegraf_id as \"TimetableGraf_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_timetablegraf_setmark(
				timetablegraf_id := :TimetableGraf_id,
			    personmark_status := :PersonMark_Status,
			    personmark_comment := :PersonMark_Comment,
			    pmuser_id := :pmUser_id
			);
		";
		$sqlParams = [
			"TimetableGraf_id" => $data["TimetableGraf_id"],
			"PersonMark_Status" => $data["PersonMark_Status"],
			"PersonMark_Comment" => $data["PersonMark_Comment"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$res = $this->db->query($sql, $sqlParams);
		if (!is_object($res)) {
			return false;
		}
		$res = $res->result("array");
		if (!empty($res[0]["Error_Msg"])) {
			throw new Exception($res[0]["Error_Msg"], $res[0]["Error_Code"]);
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Модерация бирки в поликлинике (одобрение/отказ/подтверждение)
	 * @param $data
	 * @return mixed
	 */
	function setTimetableGrafModeration($data)
	{
		$sql = "
			select
				timetablegraf_id as \"TimetableGraf_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_timetablegraf_moderate(
			    timetablegraf_id := :TimetableGraf_id,
			    timetablegraf_ismoderated := :Status,
				pmuser_id := :pmUser_id
			);
		";
		$sqlParams = [
			"TimetableGraf_id" => $data["TimetableGraf_id"],
			"Status" => $data["Status"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$res = $this->db->query($sql, $sqlParams);
		$resp = $res->result("array");
		return $resp[0];
	}

	/**
	 * Получение участка и набора домов по ЛПУ и названию улицы
	 * @param $data
	 * @return array|bool
	 */
	function findLpuAddressRegions($data)
	{
		$query = "
			select
				LpuRegion_Name as \"LpuRegion_Name\",
			    LpuRegionStreet_HouseSet as \"LpuRegionStreet_HouseSet\"
			from
				v_LpuRegion lr
			    inner join v_LpuRegionStreet lrs on lrs.LpuRegion_id = lr.LpuRegion_id
			where lrs.KLStreet_id = :KLStreet_id
			  and lr.Lpu_id = :Lpu_id
		";
		$queryParams = [
			"KLStreet_id" => $data["KLStreet_id"],
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
	 * Печать списка пациентов поликлиники на день/период
	 * @param $data
	 * @return array|bool
	 */
	function printPacList($data)
	{
		$query = "
			select
				msf.MedPersonal_FIO as \"MedPersonal_FIO\",
				ls.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				ls.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				lr.LpuRegion_Name as \"LpuRegion_Name\",
				ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				msf.MedStaffFact_IsQueueOnFree as \"MedStaffFact_IsQueueOnFree\",
				msf.RecType_id as \"RecType_id\",
				lu.LpuUnit_Name as \"LpuUnit_Name\",
				a.Address_Address as \"Address_Address\",
				a.KLCity_id as \"KLCity_id\",
				l.Lpu_Nick as \"Lpu_Nick\",
				lu.LpuUnit_id as \"LpuUnit_id\",
				ls.LpuSection_Name as \"LpuSection_Name\",
				coalesce(LL.LpuLevel_Code, 0) as \"LpuLevel_Code\",
				lka.Kladr_Code as \"Kladr_Code\",
				l.Lpu_id as \"Lpu_id\"
			from v_MedstaffFact_ER msf
				left join v_MedStaffRegion msr on msr.MedPersonal_id = msf.MedPersonal_id
				left outer join v_LpuRegion lr on msr.LpuRegion_Id = lr.LpuRegion_Id
				left outer join v_LpuSection_ER ls on msf.LpuSection_Id = ls.LpuSection_Id
				left join v_LpuUnit_ER lu on lu.LpuUnit_id = msf.LpuUnit_id
				left outer join Address a on lu.Address_id = a.Address_id
				left join v_Lpu l on l.lpu_id = lu.lpu_id
				left join v_Address la on la.Address_id = l.PAddress_id
				left join v_KLArea lka on lka.KLArea_id = la.KLCity_id
				left join v_LpuLevel LL on LL.LpuLevel_id = l.LpuLevel_id
			where msf.MedStaffFact_id = :MedStaffFact_id
			limit 1
		";
		$queryParams = ["MedStaffFact_id" => $data["MedStaffFact_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$res = $result->result("array");
		$query = "
			select
				oh.OrgHeadPost_id as \"OrgHeadPost_id\",
				rtrim(ps.Person_SurName)||' '||rtrim(ps.Person_FirName)||' '||rtrim(coalesce(ps.Person_SecName, '')) as \"OrgHead_FIO\",
				rtrim(ohp.OrgHeadPost_Name) as \"OrgHeadPost_Name\",
				rtrim(coalesce(oh.OrgHead_Email, '')) as \"OrgHead_Email\",
				rtrim(coalesce(oh.OrgHead_Phone, '')) as \"OrgHead_Phone\",
				rtrim(coalesce(oh.OrgHead_Mobile, '')) as \"OrgHead_Mobile\",
				rtrim(coalesce(oh.OrgHead_CommissNum, '')) as \"OrgHead_CommissNum\",
				rtrim(coalesce(to_char(oh.OrgHead_CommissDate, '{$this->dateTimeForm104}'), '')) as \"OrgHead_CommissDate\",
				rtrim(coalesce(oh.OrgHead_Address, '')) as \"OrgHead_Address\"
			from
				v_OrgHead as oh
				inner join v_PersonState as ps on oh.Person_id = ps.Person_id
				inner join OrgHeadPost as ohp on ohp.OrgHeadPost_id = oh.OrgHeadPost_id
			where oh.Lpu_id = (
					select Lpu_id
					from v_MedStaffFact_Er
					where MedStaffFact_id = :MedStaffFact_id
				)
				and LpuUnit_id is null
				and oh.OrgHeadPost_id = 7
		";
		$queryParams = ["MedStaffFact_id" => $data["MedStaffFact_id"]];
		$result = $this->db->query($query, $queryParams);
		$res["OrgHead"] = (is_object($result)) ? $result->result("array") : null;
		$selectPersonData = "
			rtrim(p.Person_Firname) as \"Person_Firname\",
			rtrim(p.Person_Surname) as \"Person_Surname\",
			rtrim(p.Person_Secname) as \"Person_Secname\"
		";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = " left join v_PersonEncrypHIV peh on peh.Person_id = p.Person_id";
			$selectPersonData = "
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Surname) else rtrim(peh.PersonEncrypHIV_Encryp) end as \"Person_Surname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Firname) else '' end as \"Person_Firname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Secname) else '' end as \"Person_Secname\"
			";
		}
		$params = ["MedStaffFact_id" => $data["MedStaffFact_id"]];
		if (empty($data["isPeriod"])) {
			$whereTimetableGraf_Day = "t.TimetableGraf_Day = :TimetableGraf_Day";
			$params["TimetableGraf_Day"] = TimeToDay(strtotime($data["Day"]));
		} else {
			$whereTimetableGraf_Day = "t.TimetableGraf_Day between :TimetableGraf_begDay and :TimetableGraf_endDay";
			$params["TimetableGraf_begDay"] = TimeToDay(strtotime($data["Day"]));

			$temp = GetPolDayCount($res[0]["Lpu_id"]);
			$plusDays = empty($temp) ? 10 : $temp;
			$params["TimetableGraf_endDay"] = $params["TimetableGraf_begDay"] + $plusDays;
		}
		$selectString = "
			to_char(t.TimetableGraf_begTime, '{$this->dateTimeForm120}') as \"TimetableGraf_begTime\",
			{$selectPersonData}
		";
		$fromString = "
			v_TimetableGraf_lite t
			left outer join v_MedStaffFact_ER msf on msf.MedStaffFact_id=t.MedStaffFact_id
			left outer join v_Person_ER p on p.Person_id = t.Person_id
			{$joinPersonEncrypHIV}
		";
		$whereString = "
				{$whereTimetableGraf_Day}
			and t.MedStaffFact_Id = :MedStaffFact_id
			and t.TimetableGraf_begTime is not null
			and t.Person_id is not null		
		";
		$orderByString = "t.TimetableGraf_begTime";
		$query = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
			order by {$orderByString} 
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$res["ttgData"] = $result->result("array");
		return $res;
	}

	/**
	 * Печать списка записанных пациентов в стационаре или на мед. службе
	 * @param $data
	 * @return array|bool
	 */
	function printPacStacOrMSList(&$data)
	{
		if (isset($data['MedService_id'])) {
			$query = "
				select
					ms.MedService_Name as \"MedService_Name\",
					l.Lpu_Nick as \"Lpu_Nick\",
					a.Address_Address as \"Address_Address\",
					lka.Kladr_Code as \"Kladr_Code\",
					ls.LpuSection_Name as \"LpuSection_Name\",
					ms.Lpu_id as \"Lpu_id\"
				from v_MedService ms
					left join v_Lpu l on ms.Lpu_id = l.Lpu_id
					left join v_LpuSection ls on ms.LpuSection_id = ls.LpuSection_id
					left join v_Address a on ms.Address_id = a.Address_id
					left join v_Address la on la.Address_id = l.PAddress_id
					left join v_KLArea lka on lka.KLArea_id = la.KLCity_id
				where ms.MedService_id = :MedService_id
				limit 1
			";
		} else {
			$query = "
				select
					ls.LpuSection_Name as \"MedService_Name\",
					l.Lpu_Nick as \"Lpu_Nick\",
					a.Address_Address as \"Address_Address\",
					lka.Kladr_Code as \"Kladr_Code\",
					ls.LpuSection_Name as \"LpuSection_Name\",
					ls.Lpu_id as \"Lpu_id\"
				from v_LpuSection ls
					left join v_Lpu l on ls.Lpu_id = l.Lpu_id
					left join v_LpuUnit lu on ls.LpuUnit_id = lu.LpuUnit_id
					left join v_Address a on lu.Address_id = a.Address_id
					left join v_Address la on la.Address_id = l.PAddress_id
					left join v_KLArea lka on lka.KLArea_id = la.KLCity_id
				where ls.LpuSection_id = :LpuSection_id
				limit 1
			";
		}
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$res = $result->result("array");
		$whereLpu = $res[0]["Lpu_id"];
		$join = "";
		if (isset($data["isPeriod"]) && isset($data["MedService_id"])) {
			if ($data["endDate"] < $data["begDate"]) {
				$temp = GetMedServiceDayCount($res[0]["Lpu_id"]);
				$plusDays = empty($temp) ? 10 : $temp;
				$data["endDate"] = date("d.m.Y", strtotime($data["begDate"] . " + " . $plusDays . " days"));
			}
			$params = [
				"MedService_id" => $data["MedService_id"],
				"Timetable_begDay" => TimeToDay(strtotime($data["begDate"])),
				"Timetable_endDay" => TimeToDay(strtotime($data["endDate"]))
			];
			$selectDate = "to_char(t.TimetableResource_begTime, '{$this->dateTimeForm120}') as \"TimetableGraf_begTime\",";
			$from = "
				v_TimetableResource_lite t
				left outer join v_Person_ER p on t.Person_id = p.Person_id
			";
			$join = "
				left join v_Resource r on t.Resource_id = r.Resource_id
				left join v_MedService ms on r.MedService_id = ms.MedService_id
			";
			$whereTimetableGraf_Day = "
				    t.TimetableResource_Day between :Timetable_begDay and :Timetable_endDay
				and ms.MedService_id = :MedService_id
			";
			$orderBy = "t.TimetableResource_begTime";
		} else if (isset($data["MedService_id"])) {
			$temp = GetMedServiceDayCount($res[0]["Lpu_id"]);
			$plusDays = empty($temp) ? 10 : $temp;
			$data["endDate"] = date("d.m.Y", strtotime($data["begDate"] . " + " . $plusDays . " days"));
			$params = [
				"MedService_id" => $data["MedService_id"],
				"Timetable_begDay" => TimeToDay(strtotime($data["begDate"])),
				"Timetable_endDay" => TimeToDay(strtotime($data["endDate"])),
			];
			$selectDate = "to_char(t.TimetableMedService_begTime, '{$this->dateTimeForm120}') as \"TimetableGraf_begTime\",";
			$from = "
				v_TimetableMedService_lite t
				left outer join v_Person_ER p on t.Person_id = p.Person_id
			";
			$whereTimetableGraf_Day = "
					t.TimetableMedService_Day between :Timetable_begDay and :Timetable_endDay
				and t.MedService_id = :MedService_id
			";
			$orderBy = "t.TimetableMedService_begTime";
		} else {
			$temp = GetStacDayCount($data["Lpu_id"]);
			$plusDays = empty($temp) ? 10 : $temp;
			$data["endDate"] = date("d.m.Y", strtotime($data["begDate"] . " + " . $plusDays . " days"));
			$params = [
				"LpuSection_id" => $data["LpuSection_id"],
				"Timetable_begDay" => TimeToDay(strtotime($data["begDate"])),
				"Timetable_endDay" => TimeToDay(strtotime($data["endDate"])),
			];
			$selectDate = "to_char(TimeTableStac_setDate, '{$this->dateTimeForm120}') as \"TimeTableStac_begTime\",";
			$from = "
				v_TimeTableStac_lite t
				left outer join v_Person_ER p on t.Person_id = p.Person_id
			";
			$whereTimetableGraf_Day = "
					t.TimetableStac_Day between :Timetable_begDay and :Timetable_endDay
				and LpuSection_id = :LpuSection_id
			";
			$orderBy = "\"TimeTableStac_begTime\"";
		}
		$query = "
			select
				oh.OrgHeadPost_id as \"OrgHeadPost_id\",
				rtrim(ps.Person_SurName)||' '||rtrim(ps.Person_FirName)||' '||rtrim(coalesce(ps.Person_SecName, '')) as \"OrgHead_FIO\",
				rtrim(ohp.OrgHeadPost_Name) as \"OrgHeadPost_Name\",
				rtrim(coalesce(oh.OrgHead_Email, '')) as \"OrgHead_Email\",
				rtrim(coalesce(oh.OrgHead_Phone, '')) as \"OrgHead_Phone\",
				rtrim(coalesce(oh.OrgHead_Mobile, '')) as \"OrgHead_Mobile\",
				rtrim(coalesce(oh.OrgHead_CommissNum, '')) as \"OrgHead_CommissNum\",
				rtrim(coalesce(to_char(oh.OrgHead_CommissDate, '{$this->dateTimeForm104}'), '')) as \"OrgHead_CommissDate\",
				rtrim(coalesce(oh.OrgHead_Address, '')) as \"OrgHead_Address\"
			from
				v_OrgHead as oh
				inner join v_PersonState as ps on oh.Person_id = ps.Person_id
				inner join OrgHeadPost as ohp on ohp.OrgHeadPost_id = oh.OrgHeadPost_id
			where oh.Lpu_id = {$whereLpu}
			  and LpuUnit_id is null
			  and oh.OrgHeadPost_id = 7
		";
		$result = $this->db->query($query);
		$res["OrgHead"] = (is_object($result)) ? $result->result("array") : null;
		$selectPersonData = "
			rtrim(p.Person_Firname) as \"Person_Firname\",
			rtrim(p.Person_Surname) as \"Person_Surname\",
			rtrim(p.Person_Secname) as \"Person_Secname\"
		";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data["session"])) {
			$joinPersonEncrypHIV = " left join v_PersonEncrypHIV peh on peh.Person_id = p.Person_id";
			$selectPersonData = "
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Surname) else rtrim(peh.PersonEncrypHIV_Encryp) end as \"Person_Surname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Firname) else '' end as \"Person_Firname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Secname) else '' end as \"Person_Secname\"
			";
		}
		$query = "
			select
				{$selectDate}
				{$selectPersonData}
			from {$from}
				 {$join}
				 {$joinPersonEncrypHIV}
			where {$whereTimetableGraf_Day}
			  and t.Person_id is not null
			order by {$orderBy}
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$res["ttgData"] = $result->result("array");
		return $res;
	}

	/**
	 * Редактирование переданного набора бирок
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function editTTGSet($data)
	{
		$TTGSet = json_decode($data['selectedTTG']);
		if ($this->checkTTGOccupied($TTGSet)) {
			return $this->createError(null, 'Одна из выбранных бирок занята. Операция невозможна.');

		}
		// Пустая строка передается как NULL, надо как пустую строку передавать
		if ($data["ChangeTTGDescr"]) {
			$data["TimetableExtend_Descr"] = isset($data["TimetableExtend_Descr"]) ? $data["TimetableExtend_Descr"] : "";
		} else {
			$data["TimetableExtend_Descr"] = null;
		}
		if ($data["ChangeTTGType"]) {
			$data["TimetableType_id"] = isset($data["TimetableType_id"]) ? $data["TimetableType_id"] : 1;
		} else {
			$data["TimetableType_id"] = null;
		}
		foreach ($TTGSet as $TTG) {
			$query = "
				select
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\",
					(
						select TimetableType_SysNick
						from v_TimetableType
						where TimetableType_id = :TimetableType_id
						limit 1
					) as \"TimetableType_SysNick\"
				from p_timetablegraf_edit(
				    timetablegraf_id := :TimetableGraf_id,
				    timetabletype_id := :TimetableType_id,
				    timetableextend_descr := :TimetableExtend_Descr,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"TimetableGraf_id" => $TTG,
				"TimetableType_id" => $data["TimetableType_id"],
				"TimetableExtend_Descr" => $data["TimetableExtend_Descr"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$res = $this->db->query($query, $queryParams);
			if (is_object($res)) {
				$resp = $res->result("array");
				if (count($resp) > 0 && !empty($resp[0]["TimetableType_SysNick"])) {
					$action = $this->defineActionTypeByTimetableType($resp[0]["TimetableType_SysNick"]);
					if (!empty($action)) {
						// отправка STOMP-сообщения
						sendFerStompMessage([
							"id" => $TTG,
							"timeTable" => "TimetableGraf",
							"action" => $action,
							"setDate" => date("c")
						], "Rule");
					}
				}
			}
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Проверка, что хоть одна из набора переданных бирок занята
	 * @param $TTGSet
	 * @return bool
	 */
	function checkTTGOccupied($TTGSet)
	{
		if (count($TTGSet) == 0) {
			return false;
		}
		$TTGSetString = implode(",", $TTGSet);
		$sql = "
			SELECT count(*) as \"cnt\"
			FROM v_TimetableGraf_lite
			WHERE TimetableGraf_id in ({$TTGSetString})
			  and Person_id is not null
		";
		$res = $this->db->query($sql);
		if (is_object($res)) {
			$res = $res->result("array");
		}
		return $res[0]["cnt"] > 0;
	}

	/**
	 * Обработка перед сохранением посещения
	 * Тут логика по обслуживанию направления и записи фактического времени посещения
	 * Бирка без записи = дополнительная бирка без привязки к направлению
	 * В результате посещение всегда должно быть связано с биркой
	 * @param EvnVizitPL_model $evn
	 * @return array
	 * @throws Exception
	 */
	function onBeforeSaveEvnVizit(EvnVizitPL_model $evn)
	{
		$response = [
			"TimetableGraf_id" => $evn->TimetableGraf_id,
			"EvnDirection_id" => $evn->EvnDirection_id,
		];
		if (false == in_array($evn->evnClassId, array(11, 13))) {
			// ничего не делаем
			return $response;
		}
		if (empty($evn->MedStaffFact_id)) {
			throw new Exception("Не указан врач", 500);
		}
		// получаем данные для проверок
		$this->load->helper("Reg");
		$day = TimeToDay(strtotime($evn->setDate));
		$params = array(
			"MedStaffFact_id" => $evn->MedStaffFact_id,
			"Person_id" => $evn->Person_id,
		);
		// убрал условие  and ttg.MedStaffFact_id = :MedStaffFact_id т.к. вполне может быть, что был записан к одному врачу, а принят другим, т.е. направление обслужено другим врачом
		$add_where = "";
		$union = "";
		switch (true) {
			case (!empty($evn->EvnDirection_id)):
				// Если принимают по бирке из арм консультативного приёма, то надо проставить в ней фактическое время приёма
				$resp_ttms = $this->queryResult("
					select TimetableMedService_id as \"TimetableMedService_id\"
					from v_TimetableMedService_lite
					where EvnDirection_id = :EvnDirection_id
				", [
					"EvnDirection_id" => $evn->EvnDirection_id
				]);
				if (!empty($resp_ttms[0]["TimetableMedService_id"])) {
					// обновляем фактическое время приема
					$tmp = $this->swUpdate("TimetableMedService", [
						"TimetableMedService_id" => $resp_ttms[0]["TimetableMedService_id"],
						"Evn_id" => $evn->id,
						"TimetableMedService_factTime" => $evn->setDate . " " . $evn->setTime,
					], false);
					if (empty($tmp) || false == is_array($tmp)) {
						throw new Exception("Ошибка запроса к БД", 500);
					}
					if (false == empty($tmp[0]["Error_Msg"])) {
						throw new Exception($tmp[0]["Error_Msg"], 500);
					}
				}

				// Врач принимает по записи или редактирует посещение, созданное по направлению/записи на бирку или из очереди или созданное без записи и без направления
				$params["EvnDirection_id"] = $evn->EvnDirection_id;
				if (!$evn->isNewRecord && !empty($evn->TimetableGraf_id)) {
					$params["TimetableGraf_id"] = $evn->TimetableGraf_id;
					$union = "union all
						(select
							null as \"EvnStatus_id\",
							null as \"DirType_id\",
							ttg.RecClass_id as \"RecClass_id\",
							ttg.TimeTableGraf_IsModerated as \"TimeTableGraf_IsModerated\",
							ttg.Evn_id as \"Evn_id\",
							ttg.TimeTableGraf_Mark as \"TimeTableGraf_Mark\",
							ttg.TimetableGraf_IsDop as \"TimetableGraf_IsDop\",
							ttg.TimetableType_id as \"TimetableType_id\",
							ttg.TimetableGraf_Time as \"TimetableGraf_Time\",
							ttg.TimetableGraf_begTime as \"TimetableGraf_begTime\",
							ttg.TimetableGraf_factTime as \"TimetableGraf_factTime\",
							ttg.TimetableGraf_Day as \"TimetableGraf_Day\",
							ttg.EvnDirection_id as \"EvnDirection_id\",
							ttg.TimetableGraf_id as \"TimetableGraf_id\",
							ttg.MedStaffFact_id as \"MedStaffFact_id\",
							ttg.Person_id as \"Person_id\"
						from v_TimetableGraf_lite ttg
						where ttg.TimetableGraf_id = :TimetableGraf_id
							and ttg.Person_id = :Person_id
							and ttg.MedStaffFact_id is not null
							and (select EvnDirection_id from v_EvnDirection_all ed where EvnDirection_id = :EvnDirection_id limit 1) is null
						limit 1)
					";
				}
				$add_where = "
					and ED.EvnDirection_id = :EvnDirection_id
					and ED.Person_id = :Person_id
				";
				$needTtgData = "
					v_EvnDirection_all ED
					left join v_TimetableGraf_lite ttg on ttg.EvnDirection_id = ED.EvnDirection_id and ttg.MedStaffFact_id is not null
				";
				if ($evn->isNewRecord) {
					// принять можно только по необслуженному направлению
					$add_where .= "
						and not exists (
							select e.EvnVizit_id
							from v_EvnVizit e
							where e.EvnDirection_id = ED.EvnDirection_id
						)
					";
					//когда статусы будут нормально проставляться, тогда можно будет сделать так:
					//$add_where .= ' and ED.EvnStatus_id <> 15';
				}
				break;
			case (!empty($evn->TimetableGraf_id)):
				// Врач принимает по записи или редактирует посещение, созданное без записи и без направления
				$params["TimetableGraf_id"] = $evn->TimetableGraf_id;
				$add_where = "
					and ttg.TimetableGraf_id = :TimetableGraf_id
					and ttg.Person_id = :Person_id
					and ttg.MedStaffFact_id is not null
				";
				$needTtgData = "
					v_TimetableGraf_lite ttg
					left join v_EvnDirection_all ED on ttg.TimeTableGraf_id = ED.TimeTableGraf_id
				";
				if ($evn->isNewRecord) {
					// принять можно только по необслуженному направлению
					$add_where .= "
						and not exists (
							select e.EvnVizit_id
							from v_EvnVizit e
							where e.TimetableGraf_id = ttg.TimetableGraf_id
							limit 1
						)
					";
				}
				break;
			default:
				// Пациент принимается без записи и без выбранного направления
				// Пациент может быть записан на день приема или на поздний день по направлению
				// В этом случае никакая бирка освобождаться и заниматься не должна. Приём без записи.
				$needTtgData = false;
				break;
		}
		$ttg_rows = [];
		if ($needTtgData) {
			$selectString = implode(", ", [
				"ED.EvnStatus_id as \"EvnStatus_id\"",
				"ED.DirType_id as \"DirType_id\"",
				"ttg.RecClass_id as \"RecClass_id\"",
				"ttg.TimeTableGraf_IsModerated as \"TimeTableGraf_IsModerated\"",
				"ttg.Evn_id as \"Evn_id\"",
				"ttg.TimeTableGraf_Mark as \"TimeTableGraf_Mark\"",
				"ttg.TimetableGraf_IsDop as \"TimetableGraf_IsDop\"",
				"ttg.TimetableType_id as \"TimetableType_id\"",
				"ttg.TimetableGraf_Time as \"TimetableGraf_Time\"",
				"ttg.TimetableGraf_begTime as \"TimetableGraf_begTime\"",
				"ttg.TimetableGraf_factTime as \"TimetableGraf_factTime\"",
				"ttg.TimetableGraf_Day as \"TimetableGraf_Day\"",
				"coalesce(ttg.EvnDirection_id, ED.EvnDirection_id) as \"EvnDirection_id\"",
				"ttg.TimetableGraf_id as \"TimetableGraf_id\"",
				"ttg.MedStaffFact_id as \"MedStaffFact_id\"",
				"ttg.Person_id as \"Person_id\""
			]);
			$query = "
				select {$selectString}
				from {$needTtgData}
				where (1=1) {$add_where}
				{$union}
				order by \"TimetableGraf_IsDop\" asc, \"EvnDirection_id\" desc
				limit 10
			";
			$res = $this->db->query($query, $params);
			if (!is_object($res)) {
				throw new Exception("Не удалось выполнить запрос данных бирки", 500);
			}
			$ttg_rows = $res->result("array");
		}
		if ($needTtgData && empty($ttg_rows)) {
			//throw new Exception('Не удалось получить данные бирки', 500);
			// Пользователю ничего не показываем, считаем что $needTtgData не требуется 
			$needTtgData = false;
		}
		$need_free_ttg = false;
		$need_dop_ttg = false;
		$need_del_dop_ttg = false;
		$need_clear_ttg_fact_time = false;
		$need_update_ttg_fact_time = false;
		$need_set_serviced = false;
		$ttg_data = null;
		$ttg_dop_data = null;
		if (!empty($ttg_rows)) {
			// благодаря order by TimetableGraf_IsDop asc, EvnDirection_id desc бирки без записи будут последними
			$ttg_data = $ttg_rows[0];
			if (empty($response["EvnDirection_id"]) && isset($ttg_rows[0]["EvnDirection_id"])) {
				$response["EvnDirection_id"] = $ttg_rows[0]["EvnDirection_id"];
			}
			if (empty($response["DirType_id"])) {
				$response["DirType_id"] = $ttg_rows[0]["DirType_id"];
			}
			foreach ($ttg_rows as $row) {
				if (1 == $row["TimetableGraf_IsDop"] && empty($row["TimetableGraf_begTime"]) && empty($ttg_dop_data)) {
					$ttg_dop_data = $row;
				}
			}
		}
		/*
		 * 1) Создание посещения
		 * 1.1) Врач принимает по записи на бирку на день приема.
		 * На бирке записывается фактическое время, направление обслуживается
		 * 1.2) Врач принимает по записи на бирку на прошедший день.
		 * Создается бирка без записи на день приема, направление обслуживается
		 * 1.3) Врач принимает по записи на бирку на будущий день.
		 * Бирка освобождается, создается бирка без записи на день приема, направление обслуживается
		 * 1.4) Врач принимает по направлению из очереди.
		 * Создается бирка без записи на день приема, направление обслуживается
		 * 1.5) Врач принимает без записи.
		 * Создается бирка без записи на день приема
		 * 
		 * 2) Редактирование посещения (изменилась дата/время посещения/врач/было выбрано направление)
		 * 2.1) Изменилась дата или врач посещения, созданного без записи и без направления
		 * Удаляется старая бирка без записи, создается новая бирка без записи на день приема в расписании принявшего врача
		 * 2.2) Было выбрано направление для посещения, созданного без записи и без направления, с записью на бирку на день приема.
		 * Удаляется старая бирка без записи, на бирке записывается фактическое время, направление обслуживается
		 * 2.3) Было выбрано направление для посещения, созданного без записи и без направления, с записью на бирку на прошедший день.
		 * п. 2.1 + направление обслуживается
		 * 2.4) Было выбрано направление для посещения, созданного без записи и без направления, с записью на бирку на будущий день.
		 * п. 2.1 + бирка освобождается, направление обслуживается
		 * 2.5) Было выбрано направление из очереди для посещения, созданного без записи и без направления.
		 * п. 2.1 + направление обслуживается
		 * 2.6) Изменилась дата или врач посещения, созданного по направлению, которое было обслужено в день бирки
		 * Бирка освобождается, создается новая бирка без записи на день приема в расписании принявшего врача
		 * 2.7) Изменилась дата или врач посещения, созданного по направлению, которое было обслужено не в день бирки
		 * Удаляется старая бирка без записи, создается новая бирка без записи на день приема в расписании принявшего врача
		 * 2.8) Изменилось только время посещения
		 * На бирке записывается фактическое время
		 */
		switch (true) {
			case (empty($ttg_data) /*&& $evn->isNewRecord && empty($response["EvnDirection_id"])*/): // 1.5
				$need_dop_ttg = true;
				break;
			case ($evn->isNewRecord && $ttg_data["TimetableGraf_Day"] == $day): // 1.1
				if ($ttg_data["MedStaffFact_id"] != $evn->MedStaffFact_id) {
					$need_free_ttg = $ttg_data;
					$need_dop_ttg = true;
				} else {
					$need_update_ttg_fact_time = $ttg_data["TimetableGraf_id"];
				}
				$need_set_serviced = $response["EvnDirection_id"];
				break;
			case ($evn->isNewRecord && empty($ttg_data["TimetableGraf_Day"])): // 1.4
			case ($evn->isNewRecord && !empty($ttg_data["TimetableGraf_Day"]) && $ttg_data["TimetableGraf_Day"] < $day): // 1.2
				$need_dop_ttg = true;
				$need_set_serviced = $response["EvnDirection_id"];
				if (!empty($ttg_data["TimetableGraf_factTime"])) {
					$need_clear_ttg_fact_time = $ttg_data["TimetableGraf_id"];
				}
				break;
			case ($evn->isNewRecord && !empty($ttg_data["TimetableGraf_Day"]) && $ttg_data["TimetableGraf_Day"] > $day): // 1.3
				$need_free_ttg = $ttg_data;
				$need_dop_ttg = true;
				$need_set_serviced = $response["EvnDirection_id"];
				break;
			case (false == $evn->isNewRecord && !empty($response["EvnDirection_id"]) && $evn->TimetableGraf_id && !empty($ttg_data["TimetableGraf_Day"])
				&& is_array($ttg_dop_data) && $ttg_dop_data["TimetableGraf_id"] == $evn->TimetableGraf_id
				&& $ttg_data["TimetableGraf_id"] != $evn->TimetableGraf_id && $ttg_data["EvnStatus_id"] != 15 && $ttg_data["TimetableGraf_Day"] == $day
			): // 2.2
				$need_del_dop_ttg = $ttg_dop_data["TimetableGraf_id"];
				$need_update_ttg_fact_time = $ttg_data["TimetableGraf_id"];
				$need_set_serviced = $response["EvnDirection_id"];
				break;
			case (false == $evn->isNewRecord && !empty($response["EvnDirection_id"]) && $evn->TimetableGraf_id && !empty($ttg_data["TimetableGraf_Day"])
				&& is_array($ttg_dop_data) && $ttg_dop_data["TimetableGraf_id"] == $evn->TimetableGraf_id
				&& $ttg_data["TimetableGraf_id"] != $evn->TimetableGraf_id && $ttg_data["EvnStatus_id"] != 15 && $ttg_data["TimetableGraf_Day"] < $day
			): // 2.3
				if ($ttg_dop_data["TimetableGraf_Day"] != $day) {
					$need_del_dop_ttg = $ttg_dop_data["TimetableGraf_id"];
					$need_dop_ttg = true;
				} else {
					$need_update_ttg_fact_time = $ttg_dop_data["TimetableGraf_id"];
				}
				$need_set_serviced = $response["EvnDirection_id"];
				if (!empty($ttg_data["TimetableGraf_factTime"])) {
					$need_clear_ttg_fact_time = $ttg_data["TimetableGraf_id"];
				}
				break;
			case (false == $evn->isNewRecord && !empty($response["EvnDirection_id"]) && $evn->TimetableGraf_id && !empty($ttg_data["TimetableGraf_Day"])
				&& is_array($ttg_dop_data) && $ttg_dop_data["TimetableGraf_id"] == $evn->TimetableGraf_id
				&& $ttg_data["TimetableGraf_id"] != $evn->TimetableGraf_id && $ttg_data["EvnStatus_id"] != 15 && $ttg_data["TimetableGraf_Day"] > $day
			): // 2.4
				if ($ttg_dop_data["TimetableGraf_Day"] != $day) {
					$need_del_dop_ttg = $ttg_dop_data["TimetableGraf_id"];
					$need_dop_ttg = true;
				} else {
					$need_update_ttg_fact_time = $ttg_dop_data["TimetableGraf_id"];
				}
				$need_free_ttg = $ttg_data;
				$need_set_serviced = $response["EvnDirection_id"];
				break;
			case (false == $evn->isNewRecord && !empty($response["EvnDirection_id"]) && $evn->TimetableGraf_id && empty($ttg_data["TimetableGraf_Day"])
				&& is_array($ttg_dop_data) && $ttg_dop_data["TimetableGraf_id"] == $evn->TimetableGraf_id
				&& isset($ttg_data["EvnStatus_id"]) && $ttg_data["EvnStatus_id"] != 15
			): // 2.5
				if ($ttg_dop_data["TimetableGraf_Day"] != $day) {
					$need_del_dop_ttg = $ttg_dop_data["TimetableGraf_id"];
					$need_dop_ttg = true;
				} else {
					$need_update_ttg_fact_time = $ttg_dop_data["TimetableGraf_id"];
				}
				$need_set_serviced = $response["EvnDirection_id"];
				break;
			case (false == $evn->isNewRecord && $evn->TimetableGraf_id && $ttg_data["TimetableGraf_id"] == $evn->TimetableGraf_id
				&& ($ttg_data["TimetableGraf_Day"] != $day || $ttg_data["MedStaffFact_id"] != $evn->MedStaffFact_id)
			): // 2.1, 2.6, 2.7
				$need_free_ttg = $ttg_data;
				$need_dop_ttg = true;
				break;
			case (false == $evn->isNewRecord && $evn->TimetableGraf_id && is_array($ttg_dop_data) && $ttg_dop_data["TimetableGraf_id"] == $evn->TimetableGraf_id
				&& ($ttg_dop_data["TimetableGraf_Day"] != $day || $ttg_dop_data["MedStaffFact_id"] != $evn->MedStaffFact_id)
			): // 2.1, 2.6, 2.7
				$need_del_dop_ttg = $ttg_dop_data["TimetableGraf_id"];
				if ($ttg_data["TimetableGraf_Day"] == $day && $ttg_dop_data["TimetableGraf_id"] != $ttg_data["TimetableGraf_id"]) {
					$need_update_ttg_fact_time = $ttg_data["TimetableGraf_id"];
				} else {
					$need_dop_ttg = true;
				}
				break;
			case (false == $evn->isNewRecord && $ttg_data["TimetableGraf_Day"] == $day): // 2.8
				$need_update_ttg_fact_time = $ttg_data["TimetableGraf_id"];
				if (is_array($ttg_dop_data) && $ttg_dop_data["TimetableGraf_id"] != $ttg_data["TimetableGraf_id"]) {
					$need_del_dop_ttg = $ttg_dop_data["TimetableGraf_id"];
				}
				break;
			case (false == $evn->isNewRecord && empty($evn->TimetableGraf_id)):
				// это не исключено
				if ($ttg_data["TimetableGraf_id"] && $ttg_data["TimetableGraf_Day"] == $day) {
					$need_update_ttg_fact_time = $ttg_data["TimetableGraf_id"];
				} else if ($ttg_data["TimetableGraf_id"] && $ttg_data["TimetableGraf_Day"] > $day) {
					$need_free_ttg = $ttg_data;
				} else {
					$need_dop_ttg = true;
				}
				break;
			case (false == $evn->isNewRecord && !empty($response["EvnDirection_id"])):
				// если выбрали направление и ни под одно из вышестоящих условий наш случай не подходит, то просто обслуживаем направление
				// сюда заходит, например, если человек принят без записи и затем в его талоне выбирают направление поставленное в очередь
				$need_set_serviced = $response["EvnDirection_id"];
				break;
			default:
				// на случай, если что-то не учли
				$debug = [
					"evn_day" => $day,
					"evn_EvnDirection_id" => $evn->EvnDirection_id,
					"evn_TimetableGraf_id" => $evn->TimetableGraf_id,
					"evn_MedStaffFact_id" => $evn->MedStaffFact_id,
					"ttg_TimetableGraf_id" => $ttg_data["TimetableGraf_id"],
					"ttg_TimetableGraf_Day" => $ttg_data["TimetableGraf_Day"],
					"ttg_EvnDirection_id" => $ttg_data["EvnDirection_id"],
					"ttg_MedStaffFact_id" => $ttg_data["MedStaffFact_id"],
					"ttg_EvnStatus_id" => $ttg_data["EvnStatus_id"],
					"ttg_dop_TimetableGraf_id" => is_array($ttg_dop_data) ? $ttg_dop_data["TimetableGraf_id"] : null,
					"ttg_dop_TimetableGraf_Day" => is_array($ttg_dop_data) ? $ttg_dop_data["TimetableGraf_Day"] : null,
					"ttg_dop_EvnDirection_id" => is_array($ttg_dop_data) ? $ttg_dop_data["EvnDirection_id"] : null,
					"ttg_dop_MedStaffFact_id" => is_array($ttg_dop_data) ? $ttg_dop_data["MedStaffFact_id"] : null,
					"ttg_dop_EvnStatus_id" => is_array($ttg_dop_data) ? $ttg_dop_data["EvnStatus_id"] : null,
					"response_EvnDirection_id" => $response["EvnDirection_id"],
					"response_TimetableGraf_id" => $response["TimetableGraf_id"],
				];
				log_message("error", "Error in conditions fact time write. Data: " . var_export($debug, true));
				break;
		}

		$debug = [
			"day" => $day,
			"evn_EvnDirection_id" => $evn->EvnDirection_id,
			"evn_TimetableGraf_id" => $evn->TimetableGraf_id,
			"EvnDirection_id" => $response["EvnDirection_id"],
			"needTtgData" => $needTtgData,
			"need_del_dop_ttg" => $need_del_dop_ttg,
			"need_free_ttg" => $need_free_ttg,
			"need_dop_ttg" => $need_dop_ttg,
			"need_set_serviced" => $need_set_serviced,
			"need_update_ttg_fact_time" => $need_update_ttg_fact_time,
			"need_clear_ttg_fact_time" => $need_clear_ttg_fact_time,
		];

		if ($need_del_dop_ttg) {
			$tmp = $this->execCommonSP("p_TimetableGraf_del", [
				"pmUser_id" => $evn->promedUserId,
				"TimetableGraf_id" => $need_del_dop_ttg
			], "array_assoc");
			if (empty($tmp)) {
				throw new Exception("Ошибка запроса к БД", 500);
			}
			if (isset($tmp["Error_Msg"])) {
				throw new Exception($tmp["Error_Msg"], 500);
			}
		}
		if ($need_free_ttg && !empty($need_free_ttg["TimetableGraf_id"])) {
			/*
			 * Освободить бирку
			 * p_TimeTableGraf_cancel не подходит, т.к.
			 * 1) Освободить бирку это не тоже самое, что отменить запись
			 * 2) если бирка создана на человека без записи, то она не удаляется
			 * 3) не очищается ссылка на посещение Evn_id, которая сохраняется по задаче #64480
			 */
			if (1 == $need_free_ttg["TimetableGraf_IsDop"] && empty($need_free_ttg["TimetableGraf_begTime"])) {
				// удалять бирку, если она создана на человека без записи
				$tmp = $this->execCommonSP("p_TimetableGraf_del", [
					"pmUser_id" => $evn->promedUserId,
					"TimetableGraf_id" => $need_free_ttg["TimetableGraf_id"]
				], "array_assoc");
				if (empty($tmp)) {
					throw new Exception("Ошибка запроса к БД", 500);
				}
				if (isset($tmp["Error_Msg"])) {
					throw new Exception($tmp["Error_Msg"], 500);
				}
			} else {
				$this->load->library("textlog", array("file" => "free_ttg_" . date("Y-m-d", time()) . ".log", "logging" => true), "ttlog");
				$this->ttlog->add(print_r($debug, true));

				// освобождаю бирку без использования p_TimetableGraf_upd, т.к. в ней нет работы с историей и есть изменение поля TimetableGraf_updDT
				$tmp = $this->swUpdate("TimetableGraf", [
					"TimetableGraf_id" => $need_free_ttg["TimetableGraf_id"],
					"EvnDirection_id" => null,
					"Evn_id" => null,
					"Person_id" => null,
					"RecClass_id" => null,
					"TimetableGraf_factTime" => null,
					"TimetableGraf_IsModerated" => null,
					"pmUser_id" => $evn->promedUserId,
				], true);
				if (empty($tmp) || false == is_array($tmp)) {
					throw new Exception("Ошибка запроса к БД", 500);
				}
				if (false == empty($tmp[0]["Error_Msg"])) {
					throw new Exception($tmp[0]["Error_Msg"], 500);
				}
				if (!empty($need_free_ttg["EvnDirection_id"])) {
					// также убираем ссылку в направлении
					$tmp = $this->swUpdate("EvnDirection", [
						"key_field" => "Evn_id",
						"Evn_id" => $need_free_ttg["EvnDirection_id"],
						"TimetableGraf_id" => null
					], false);
				}
				if (empty($tmp) || false == is_array($tmp)) {
					throw new Exception("Ошибка запроса к БД", 500);
				}
				if (false == empty($tmp[0]["Error_Msg"])) {
					throw new Exception($tmp[0]["Error_Msg"], 500);
				}
				// Обновляем кэш по дню
				$tmp = $this->execCommonSP("p_MedPersonalDay_recount", [
					"MedStaffFact_id" => $need_free_ttg["MedStaffFact_id"],
					"Day_id" => $need_free_ttg["TimetableGraf_Day"],
					"pmUser_id" => $evn->promedUserId,
				], "array_assoc");
				if (empty($tmp)) {
					throw new Exception("Ошибка запроса к БД", 500);
				}
				if (isset($tmp["Error_Msg"])) {
					throw new Exception($tmp["Error_Msg"], 500);
				}
				// Заносим изменения бирки в историю
				$tmp = $this->execCommonSP("p_AddTTGToHistory", [
					"TimeTableGraf_id" => $need_free_ttg["TimetableGraf_id"],
					"TimeTableGrafAction_id" => 3, // Освобождение бирки
					"pmUser_id" => $evn->promedUserId,
				], "array_assoc");
				if (empty($tmp)) {
					throw new Exception("Ошибка запроса к БД", 500);
				}
				if (isset($tmp["Error_Msg"])) {
					throw new Exception($tmp["Error_Msg"], 500);
				}
			}
			if ($evn->TimetableGraf_id == $need_free_ttg["TimetableGraf_id"]) {
				$response["TimetableGraf_id"] = null;
			}
		}
		if ($need_clear_ttg_fact_time) {
			// очищаем фактическое время приема
			$tmp = $this->swUpdate("TimetableGraf", [
				"TimetableGraf_id" => $need_clear_ttg_fact_time,
				"EvnDirection_id" => $response["EvnDirection_id"],
				"Evn_id" => null,
				"TimetableGraf_factTime" => null,
			], false);
			if (empty($tmp) || false == is_array($tmp)) {
				throw new Exception("Ошибка запроса к БД", 500);
			}
			if (false == empty($tmp[0]["Error_Msg"])) {
				throw new Exception($tmp[0]["Error_Msg"], 500);
			}
			if ($evn->TimetableGraf_id == $need_clear_ttg_fact_time) {
				$response["TimetableGraf_id"] = null;
			}
		}
		if ($need_update_ttg_fact_time) {
			// обновляем фактическое время приема
			$tmp = $this->swUpdate("TimetableGraf", [
				"TimetableGraf_id" => $need_update_ttg_fact_time,
				"EvnDirection_id" => $response["EvnDirection_id"],
				"Evn_id" => $evn->id,
				"TimetableGraf_factTime" => $evn->setDate . " " . $evn->setTime,
			], false);
			if (empty($tmp) || false == is_array($tmp)) {
				throw new Exception("Ошибка запроса к БД", 500);
			}
			if (false == empty($tmp[0]["Error_Msg"])) {
				throw new Exception($tmp[0]["Error_Msg"], 500);
			}
			$response["TimetableGraf_id"] = $need_update_ttg_fact_time;
		}
		if ($need_dop_ttg) {
			// создание дополнительной бирки на день незапланированного приема
			$tmp = $this->execCommonSP("p_TimetableGraf_ins", [
				"TimetableGraf_id" => [
					"value" => null,
					"out" => true,
					"type" => "bigint",
				],
				"RecClass_id" => 1,// 3?
				"TimetableGraf_IsDop" => 1,
				"TimetableType_id" => 1,
				"TimetableGraf_Time" => 0,
				"TimetableGraf_begTime" => null, //время запланированного приема, заполняется при создании расписания
				"TimetableGraf_factTime" => $evn->setDate . " " . $evn->setTime,
				"TimetableGraf_Day" => $day,
				"EvnDirection_id" => $response["EvnDirection_id"],
				"Evn_id" => $evn->id,
				"MedStaffFact_id" => $evn->MedStaffFact_id,
				"Person_id" => $evn->Person_id,
				"pmUser_id" => $evn->promedUserId,
			], "array_assoc");
			if (empty($tmp)) {
				throw new Exception("Ошибка запроса к БД", 500);
			}
			if (isset($tmp["Error_Msg"])) {
				throw new Exception($tmp["Error_Msg"], 500);
			}
			$response["TimetableGraf_id"] = $tmp["TimetableGraf_id"];
		}
		if ($need_set_serviced) {
			if (empty($response["DirType_id"])) {
				// Если направление без типа обслуживается врачом поликлиники/стоматологии, то принудительно присаивать ему тип "на поликлинический прием".
				$tmp = $this->swUpdate("EvnDirection", [
					"DirType_id" => 16,
					"key_field" => "Evn_id",
					"Evn_id" => $need_set_serviced
				], false);
				if (empty($tmp) || false == is_array($tmp)) {
					throw new Exception("Ошибка запроса к БД", 500);
				}
				if (false == empty($tmp[0]["Error_Msg"])) {
					throw new Exception($tmp[0]["Error_Msg"], 500);
				}
				$response["DirType_id"] = 16;
			}
			// переводим в статус “Обслужено”
			$this->load->model("EvnDirectionAll_model");
			$this->EvnDirectionAll_model->setStatus([
				"Evn_id" => $need_set_serviced,
				"EvnStatus_SysNick" => EvnDirectionAll_model::EVN_STATUS_DIRECTION_SERVICED,
				"EvnClass_id" => $this->EvnDirectionAll_model->evnClassId,
				"pmUser_id" => $evn->promedUserId,
			]);
		}
		return $response;
	}

	/**
	 * Обработка после сохранения посещения
	 * Должна выполняться внутри транзакции
	 * @param EvnVizitPL_model $evn
	 * @return bool
	 * @throws Exception
	 */
	function onAfterSaveEvnVizit(EvnVizitPL_model $evn)
	{
		if (false == in_array($evn->evnClassId, [11, 13])) {
			// ничего не делаем
			return true;
		}
		if ($evn->isNewRecord && $evn->TimetableGraf_id) {
			// #64480 сохраняем ссылку на посещение
			$tmp = $this->swUpdate("TimetableGraf", [
				"TimetableGraf_id" => $evn->TimetableGraf_id,
				"Evn_id" => $evn->id,
			], false);
			if (empty($tmp) || false == is_array($tmp)) {
				throw new Exception("Ошибка запроса к БД", 500);
			}
			if (false == empty($tmp[0]["Error_Msg"])) {
				throw new Exception($tmp[0]["Error_Msg"], 500);
			}
			if ($evn->EvnDirection_id) {
				$tmp = $this->swUpdate("EvnDirection", [
					"TimetableGraf_id" => $evn->TimetableGraf_id,
					"key_field" => "Evn_id",
					"Evn_id" => $evn->EvnDirection_id
				], false);
				if (empty($tmp) || false == is_array($tmp)) {
					throw new Exception("Ошибка запроса к БД", 500);
				}
				if (false == empty($tmp[0]["Error_Msg"])) {
					throw new Exception($tmp[0]["Error_Msg"], 500);
				}
			}
		}
		return true;
	}

	/**
	 * Обработка удаления ТАП/посещения (перед удалением)
	 * Должна выполняться внутри транзакции
	 * @param EvnAbstract_model $evn
	 * @return bool
	 * @throws Exception
	 */
	function onBeforeDeleteEvn(EvnAbstract_model $evn)
	{
		if (false == in_array($evn->evnClassId, [3, 6, 11, 13])) {
			// ничего не делаем
			return true;
		}
		if (in_array($evn->evnClassId, [3, 6])) {
			// в p_EvnPL_setdel, p_EvnPLStom_setdel
			// в посещениях чистятся ссылки TimetableGraf_id
			// поэтому нужно получить $data["TimetableGrafArr"] до удаления
			// получить данные бирок по посещениям в рамках данного случая
			// и восстановить расписание, как будто случая лечения не было
			$where = "EvnVizit.EvnVizit_pid = :Evn_id";
		} else {
			// в p_EvnVizitPL_setdel, p_EvnVizitPLStom_setdel
			// в посещениях чистятся ссылки TimetableGraf_id
			// поэтому нужно получить данные бирок до удаления
			$where = "EvnVizit.EvnVizit_id = :Evn_id";
		}
		/*
		Можно попробовать реализовать следующим образом:
		при удалении случаев проверять связь с биркой и с направлением
		создавать запись в очереди только
		если бирка дополнительная и связана с направлением (т.к. для дополнительных бирок на текущий день направления вроде как не создаются, то допбирки с направлением - можно считать признаком того, что была очищена бирка на будущую дату).
		*/
		$query = "
			select
				case 
					when 1 = TimetableGraf.TimeTableGraf_IsDop AND TimetableGraf.TimeTableGraf_begTime is null AND TimetableGraf.EvnDirection_id is not null
						/* допбирка была создана по записи на прошедший или будущий день, бирка на прошедший день не освобождается */
						AND not exists(
						    select t1.* from (
								--ищем бирку на прошедший день по этому же направлению к этому же врачу
								(
                                    select
                                        ttg.EvnDirection_id
                                    from
                                        v_TimetableGraf_lite ttg
                                    where 
                                        ttg.EvnDirection_id = TimetableGraf.EvnDirection_id
                                    and 
                                        ttg.Person_id = EvnVizit.Person_id
                                    and 
                                        ttg.MedStaffFact_id = TimetableGraf.MedStaffFact_id
                                    and 
                                        ttg.TimeTableGraf_begTime is not null
                                    and 
                                        ttg.TimeTableGraf_begTime < EvnVizit.EvnVizit_setDT
                                    limit 1
                                )
								--возможно было обслужено направление по записи на другую бирку
								union all
								(
                                    select
                                        ttg.EvnDirection_id
                                    from
                                        v_TimetableGraf_lite ttg
                                    where
                                        ttg.EvnDirection_id = TimetableGraf.EvnDirection_id
                                    and
                                        ttg.Person_id = EvnVizit.Person_id
                                    and
                                        ttg.MedStaffFact_id is not null
                                    and
                                        ttg.TimeTableGraf_begTime is not null
                                    limit 1
                                )
								--возможно было обслужено направление на службу
								union all
								(
                                    select 
                                        ttms.EvnDirection_id
                                    from
                                        v_TimetableMedService_lite ttms
                                    where
                                        ttms.EvnDirection_id = TimetableGraf.EvnDirection_id
                                    and
                                        ttms.Person_id = EvnVizit.Person_id
                                    limit 1
                                )
							) t1
						)
						then 'in_queue_delete_timetablegraf'
					when 1 = TimetableGraf.TimeTableGraf_IsDop AND TimetableGraf.TimeTableGraf_begTime is null
						/* допбирка была создана по записи на прошедший день или при приеме без направления */ 
						then 'delete_timetablegraf'
					when EvnVizit.TimetableGraf_id is not null 
						then 'clear_timetablegraf_facttime'
					else 'undefined'
				end as \"operation\",
				TimetableGraf.TimetableGraf_id as \"TimetableGraf_id\",
				TimetableGraf.MedStaffFact_id as \"MedStaffFact_id\",
				TimetableGraf.TimetableGraf_Day as \"TimetableGraf_Day\",
				msf.MedPersonal_id as \"MedPersonal_id\",
				msf.LpuSection_id as \"LpuSection_id\",
				msf.LpuUnit_id as \"LpuUnit_id\",
				msf.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				EvnVizit.EvnDirection_id as \"EvnDirection_id\"
			from
				v_EvnVizit EvnVizit
				inner join v_TimetableGraf_lite TimetableGraf on
				    	(EvnVizit.TimetableGraf_id = TimetableGraf.TimetableGraf_id or TimetableGraf.Evn_id = EvnVizit.EvnVizit_id)
				        and TimetableGraf.Person_id = EvnVizit.Person_id
				        and TimetableGraf.MedStaffFact_id is not null
				left join v_MedStaffFact msf on msf.MedStaffFact_id = TimetableGraf.MedStaffFact_id
			where {$where}
		";
		$params = ["Evn_id" => $evn->id];
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			throw new Exception("Ошибка запроса к БД", 500);
		}
		$arr = $result->result("array");
		$isAllowRollbackEvnDirectionStatus = true;
		$deletedArr = [];
		foreach ($arr as $row) {
			switch ($row["operation"]) {
				case "delete_timetablegraf":
					if (in_array($row["TimetableGraf_id"], $deletedArr)) {
						// уже удалена
						continue 2;
					}
					// удалять бирку, если она создана на человека без записи
					$tmp = $this->execCommonSP("p_TimetableGraf_del", [
						"pmUser_id" => $evn->promedUserId,
						"TimetableGraf_id" => $row["TimetableGraf_id"]
					], "array_assoc");
					if (empty($tmp)) {
						throw new Exception("Ошибка запроса к БД", 500);
					}
					if (isset($tmp["Error_Msg"])) {
						throw new Exception($tmp["Error_Msg"], 500);
					}
					$deletedArr[] = $row["TimetableGraf_id"];
					break;
				case "clear_timetablegraf_facttime":
					// нужно почистить Evn_id, TimetableGraf_factTime, если человек посещал по записи, чтобы на эту бирку можно было завести другое посещение.
					$tmp = $this->swUpdate("TimetableGraf", [
						"TimetableGraf_id" => $row["TimetableGraf_id"],
						"TimetableGraf_factTime" => null,
						"Evn_id" => null,
					], false);
					if (empty($tmp) || false == is_array($tmp)) {
						throw new Exception("Ошибка запроса к БД", 500);
					}
					if (false == empty($tmp[0]["Error_Msg"])) {
						throw new Exception($tmp[0]["Error_Msg"], 500);
					}
					break;
				case "in_queue_delete_timetablegraf":
					// бирка была освобождена, ставим в очередь по профилю отделения
					if (empty($row["LpuSectionProfile_id"])) {
						throw new Exception("Освобожденная бирка была занята. Невозможно поставить в очередь по профилю!", 500);
					}
					$tmp = $this->execCommonSP("p_EvnQueue_ins", [
						"EvnQueue_id" => null,
						"EvnUslugaPar_id" => null,
						"MedService_did" => null,
						"EvnQueue_pid" => null,
						"EvnDirection_id" => $row["EvnDirection_id"],
						"LpuSectionProfile_did" => $row["LpuSectionProfile_id"],
						"LpuUnit_did" => $row["LpuUnit_id"],
						"MedPersonal_did" => $row["MedPersonal_id"],
						"LpuSection_did" => $row["LpuSection_id"],
						"Lpu_id" => $evn->sessionParams["lpu_id"],
						"EvnQueue_setDT" => $evn->currentDT->format("Y-m-d H:i:s"),
						"PersonEvn_id" => $evn->PersonEvn_id,
						"Server_id" => $evn->Server_id,
						"pmUser_id" => $evn->promedUserId,
					], "array_assoc");
					if (empty($tmp)) {
						throw new Exception("Ошибка запроса к БД", 500);
					}
					if (isset($tmp["Error_Msg"])) {
						throw new Exception($tmp["Error_Msg"], 500);
					}
					// переводим в статус “Поставлено в очередь”
					$this->load->model("EvnDirectionAll_model");
					$this->EvnDirectionAll_model->setStatus([
						"Evn_id" => $row["EvnDirection_id"],
						"EvnStatus_SysNick" => EvnDirectionAll_model::EVN_STATUS_DIRECTION_IN_QUEUE,
						"EvnClass_id" => $this->EvnDirectionAll_model->evnClassId,
						"pmUser_id" => $evn->promedUserId,
					]);
					if ($evn->EvnDirection_id == $row["EvnDirection_id"]) {
						$isAllowRollbackEvnDirectionStatus = false;
					}
					// удалять бирку, если она создана на человека без записи
					$tmp = $this->execCommonSP("p_TimetableGraf_del", [
						"pmUser_id" => $evn->promedUserId,
						"TimetableGraf_id" => $row["TimetableGraf_id"]
					], "array_assoc");
					if (empty($tmp)) {
						throw new Exception("Ошибка запроса к БД", 500);
					}
					if (isset($tmp["Error_Msg"])) {
						throw new Exception($tmp["Error_Msg"], 500);
					}
					break;
			}
			// Возвращаем направлению предыдущий статус
			if (!empty($evn->EvnDirection_id) && $isAllowRollbackEvnDirectionStatus) {
				$this->load->model("EvnDirectionAll_model");
				$this->EvnDirectionAll_model->rollbackStatus([
					"Evn_id" => $evn->EvnDirection_id,
					"EvnStatus_SysNick" => EvnDirectionAll_model::EVN_STATUS_DIRECTION_SERVICED,
					"EvnClass_id" => $this->EvnDirectionAll_model->evnClassId,
					"pmUser_id" => $evn->promedUserId,
				]);
			}
		}
		return true;
	}

	/**
	 * Перенос бирки с одного события на другое, используется при смене пациента в документе.
	 * @param $data
	 */
	function onSetAnotherPersonForDocument($data)
	{
		$query = "
			update TimetableGraf
			set Evn_id = :Evn_id,
			    Person_id = :Person_id
			where Evn_id = :Evn_oldid
		";
		$this->db->query($query, $data);
	}

	/**
	 * Получение списка записанных в МО. Метод для API
	 * @param $data
	 * @return array|bool
	 */
	function loadTimeTableGrafListbyMO($data)
	{
		$query = "
			select distinct
				to_char(ttg.TimeTableGraf_begTime, '{$this->dateTimeForm120}') as \"TimeTableGraf_begTime\",
				coalesce(psa2.PersonInn_Inn, 999999999999) as \"PersonInn_Inn\",
				msf.Post_id as \"Post_id\",
				psa.PersonSurName_SurName as \"PersonSurName_SurName\",
				psa.PersonFirName_FirName as \"PersonFirName_FirName\",
				psa.PersonSecName_SecName as \"PersonSecName_SecName\"
			from
				v_TimeTableGraf_lite ttg
				inner join v_MedStaffFact msf on msf.MedStaffFact_id = ttg.MedStaffFact_id
				left join lateral (
					select
						psai.PersonSurName_SurName,
						psai.PersonFirName_FirName,
						psai.PersonSecName_SecName
					from
						MedPersonalCache mpc
						inner join v_PersonStateAll psai on psai.Person_id = mpc.Person_id
						inner join Person pr on pr.Person_id = psai.Person_id
					where mpc.MedPersonal_id = msf.MedPersonal_id 
					  and mpc.Lpu_id = msf.Lpu_id
					  and coalesce(pr.Person_deleted, 1) = 1
					limit 1
				) as psa on true
				left join lateral (
					select psai2.PersonInn_Inn
					from v_PersonStateAll psai2
						 inner join Person pr2 on pr2.Person_id = psai2.Person_id
					where psai2.Person_id = ttg.Person_id
					  and coalesce(pr2.Person_deleted, 1) = 1
				    limit 1
				) as psa2 on true
			where ttg.TimeTableGraf_begTime >= :TimeTableGraf_beg 
			  and ttg.TimeTableGraf_begTime <= :TimeTableGraf_end
			  and msf.Lpu_id = :Lpu_id
			  and ttg.Person_id is not null
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение списка записанных к врачу. Метод для API
	 * @param $data
	 * @return array|bool
	 */
	public function loadTimeTableGrafByMedStaffFact($data)
	{
		$fields = "";
		$from = "";
		if (isset($data["extended"])) {
			$fields = "
				,Person.Person_SurName||' '||Person.Person_FirName||' '||Person.Person_SecName as \"FIO\"
				,to_char(Person.Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_BirthDay\"
			";
			$from = " left join v_PersonState Person on ttg.Person_id = Person.Person_id";
		}
		$query = "
			select
				ttg.TimeTableGraf_id as \"TimeTableGraf_id\",
				ttg.Person_id as \"Person_id\",
				to_char(ttg.TimeTableGraf_begTime::date, '{$this->dateTimeForm120}') as \"TimeTableGraf_begTime\",
				to_char(ttg.TimeTableGraf_factTime::date, '{$this->dateTimeForm120}') as \"TimeTableGraf_factTime\"
				{$fields}
			from v_TimeTableGraf_lite ttg
				 {$from}
			where 1=1
			  and ttg.TimeTableGraf_begTime >= :TimeTableGraf_beg 
			  and ttg.TimeTableGraf_begTime <= :TimeTableGraf_end
			  and ttg.MedStaffFact_id = :MedStaffFact_id
			  and ttg.Person_id is not null
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Запись на бирку из РИШ
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function writeTimetableGraf($data)
	{
		$sql = "
			select
				msf.Lpu_id as \"Lpu_id\",
				ps.Server_id as \"Server_id\",
				ps.PersonEvn_id as \"PersonEvn_id\"
			from
				v_TimeTableGraf_lite ttg
				inner join v_MedStaffFact msf on msf.MedStaffFact_id = ttg.MedStaffFact_id
				inner join v_PersonState ps on ps.Person_id = :Person_id
			where ttg.TimetableGraf_id = :TimetableGraf_id
		";
		$sqlPrams = [
			"TimetableGraf_id" => $data["TimetableGraf_id"],
			"Person_id" => $data["Person_id"]
		];
		$resp_ttg = $this->queryResult($sql, $sqlPrams);
		if (!empty($resp_ttg[0]["Lpu_id"])) {
			$data["Lpu_id"] = $resp_ttg[0]["Lpu_id"];
			$data["DirType_id"] = 16; // на поликлинический приём
			$data["LpuSectionProfile_id"] = null;
			$data["Lpu_did"] = null;
			$data["EvnDirection_id"] = null;
			$data["EvnDirection_Num"] = "0";
			$data["From_MedStaffFact_id"] = -1;
			$data["EvnDirection_pid"] = null;
			$data["Diag_id"] = null;
			$data["EvnDirection_Descr"] = null;
			$data["LpuSection_did"] = null;
			$data["LpuSection_id"] = null;
			$data["MedPersonal_id"] = null;
			$data["MedPersonal_zid"] = null;
			$data["OverrideWarning"] = true;
			$data["Server_id"] = $resp_ttg[0]["Server_id"];
			$data["PersonEvn_id"] = $resp_ttg[0]["PersonEvn_id"];
			$data["EvnDirection_setDT"] = date("Y-m-d");
			$data["ignoreCanRecord"] = 1;
			if (!empty($data["EvnQueue_id"])) {
				$data["redirectEvnDirection"] = 600; // запись из очереди
			}
			$resp = $this->Apply($data);
			if (!empty($resp["Error_Msg"])) {
				throw new Exception($resp["Error_Msg"]);
			}
			if (!empty($resp["id"])) {
				return [
					"Person_id" => $data["Person_id"],
					"TimeTableGraf_id" => $resp["id"]
				];
			}
		}
		return false;
	}

	/**
	 * Изменение статуса записи на прием
	 * @param $data
	 * @return array|bool|false
	 * @throws Exception
	 */
	function setTimeTableGrafStatus($data)
	{
		$sql = "
			select EvnDirection_id as \"EvnDirection_id\"
			from v_TimeTableGraf_lite ttg
			where ttg.Person_id = :Person_id
			  and ttg.TimetableGraf_id = :TimeTableGraf_id
		";
		$sqlParams = [
			"Person_id" => $data["Person_id"],
			"TimeTableGraf_id" => $data["TimeTableGraf_id"]
		];
		$resp = $this->queryResult($sql, $sqlParams);
		if (!empty($resp[0]["EvnDirection_id"])) {
			if (in_array($data["EvnStatus_id"], [12, 13])) {
				// Если EvnStatus_id меняется на 12 или 13, то в таблице dbo.TimeTableGraf значения полей RecClass_id, Person_id, EvnDirection_id меняется на NULL
				$resp = $this->Clear([
					"object" => "TimetableGraf",
					"cancelType" => ($data["EvnStatus_id"] == 13) ? "decline" : "cancel",
					"TimetableGraf_id" => $data["TimeTableGraf_id"],
					"DirFailType_id" => 11, // Ошибочное направление
					"EvnStatusCause_id" => 3, // Ошибочное направление
					"EvnComment_Comment" => "",
					"pmUser_id" => $data["pmUser_id"],
					"session" => $data["session"]
				]);
				if (!empty($resp["Error_Msg"])) {
					return $resp;
				}
			} else {
				$this->load->model("Evn_model", "Evn_model");
				$resp = $this->Evn_model->updateEvnStatus([
					"Evn_id" => $resp[0]["EvnDirection_id"],
					"EvnStatus_id" => $data["EvnStatus_id"],
					"EvnClass_SysNick" => "EvnDirection",
					"pmUser_id" => $data["pmUser_id"]
				]);
				if (!empty($resp["Error_Msg"])) {
					return $resp;
				}
			}
			return [];
		}
		return false;
	}

	/**
	 * Добавление расписания врача
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function addTimetableGraf($data)
	{
		$resp_tt = [];
		if (!empty($data["TimeTableGrafCreate"])) {
			$data["TimeTableGrafCreate"] = json_encode($data["TimeTableGrafCreate"]);
			$data["TimeTableGrafCreate"] = json_decode($data["TimeTableGrafCreate"], true);
			foreach ($data["TimeTableGrafCreate"] as $one) {
				if (!isset($one["TimeTableGraf_begTime"])) {
					throw new Exception("Не указана дата/время приёма");
				}
				if (!isset($one["TimeTableGraf_Time"])) {
					throw new Exception("Не указана длительность приёма");
				}
				if (!isset($one["TimeTableType_id"])) {
					throw new Exception("Не указан тип бирки");
				}
				if (
					isset($one["TimeTableGraf_IsDop"]) &&
					$one["TimeTableGraf_IsDop"] !== 1 &&
					$one["TimeTableGraf_IsDop"] !== 0 &&
					$one["TimeTableGraf_IsDop"] !== "1" &&
					$one["TimeTableGraf_IsDop"] !== "0"
				) {
					throw new Exception("Неверное значение в поле TimeTableGraf_IsDop");
				} elseif (!isset($one["TimeTableGraf_IsDop"])) {
					throw new Exception("Не указан признак дополнительной бирки");
				}
				$query = "
					select
						timetablegraf_id as \"TimetableGraf_id\",
						error_code as \"Error_Code\",
						error_message as \"Error_Msg\"
					from p_timetablegraf_ins(
					    medstafffact_id := :MedStaffFact_id,
					    timetablegraf_day := :TimetableGraf_Day,
					    timetablegraf_begtime := :TimetableGraf_begTime,
					    timetablegraf_facttime := null,
					    timetablegraf_time := :TimetableGraf_Time,
					    timetablegraf_isdop := :TimetableGraf_IsDop,
					    timetabletype_id := :TimeTableType_id,
					    pmuser_id := :pmUser_id
					);				
				";
				$queryParams = [
					"MedStaffFact_id" => $data["MedStaffFact_id"],
					"TimetableGraf_Day" => TimeToDay(strtotime($one["TimeTableGraf_begTime"])),
					"TimetableGraf_begTime" => date("Y-m-d H:i:s", strtotime($one["TimeTableGraf_begTime"])),
					"TimetableGraf_Time" => $one["TimeTableGraf_Time"],
					"TimeTableType_id" => $one["TimeTableType_id"],
					"TimetableGraf_IsDop" => !empty($one["TimeTableGraf_IsDop"]) ? $one["TimeTableGraf_IsDop"] : null,
					"pmUser_id" => $data["pmUser_id"]
				];
				$resp = $this->queryResult($query, $queryParams);
				if (!empty($resp[0]["TimetableGraf_id"])) {
					$resp_tt[] = [
						"TimeTableGraf_id" => $resp[0]["TimetableGraf_id"]
					];
				}
				if (!empty($resp[0]["Error_Msg"])) {
					throw new Exception($resp[0]["Error_Msg"]);
				}
			}
		}
		return $resp_tt;
	}

	/**
	 * Редактирование расписания врача
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function editTimetableGraf($data)
	{
		if (!empty($data['TimeTableGrafEdit'])) {
			foreach ($data['TimeTableGrafEdit'] as $one) {
				if (!isset($one['TimeTableGraf_id'])) {
					throw new Exception("Не указан идентификатор бирки");
				}
				if (!empty($one['TimeTableType_id'])) {
					// смена типа бирки
					$procedureName = "p_TimetableGraf_setType";
					$procedureParams = "
						timetablegraf_id := :TimetableGraf_id,
						timetabletype_id = :TimetableType_id,
						pmuser_id = :pmUser_id
					";
					$selectString = "
						error_code as \"Error_Code\",
						error_message as \"Error_Msg\"
					";
					$sql = "
						select {$selectString}
						from {$procedureName}({$procedureParams});
					";
					$sqlParams = [
						"TimetableGraf_id" => $one["TimeTableGraf_id"],
						"TimetableType_id" => $one["TimeTableType_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$tmp = $this->queryResult($sql, $sqlParams);
					if (empty($tmp)) {
						throw new Exception("Ошибка запроса к БД", 500);
					}
					if (isset($tmp["Error_Msg"])) {
						throw new Exception($tmp["Error_Msg"], 500);
					}
				}
				if (isset($one["TimeTableGraf_IsDop"])) {
					// проставление признака дополнительной бирки
					$sql = "
						update TimetableGraf
						set TimeTableGraf_IsDop = :TimeTableGraf_IsDop,
						    pmUser_updID = :pmUser_id,
						    TimeTableGraf_updDT = getdate()
						where TimetableGraf_id = :TimetableGraf_id
					";
					$sqlParams = [
						"TimetableGraf_id" => $one["TimeTableGraf_id"],
						"TimeTableGraf_IsDop" => $one["TimeTableGraf_IsDop"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$this->db->query($sql, $sqlParams);
				}
				if (!empty($one["TimeTableGrafDelStatus"])) {
					// удаление бирки
					$spParams = [
						"pmUser_id" => $data["pmUser_id"],
						"TimetableGraf_id" => $one["TimeTableGraf_id"]
					];
					$tmp = $this->execCommonSP("p_TimetableGraf_del", $spParams, "array_assoc");
					if (empty($tmp)) {
						throw new Exception("Ошибка запроса к БД", 500);
					}
					if (isset($tmp["Error_Msg"])) {
						throw new Exception($tmp["Error_Msg"], 500);
					}
				}
			}
		}
		return [];
	}
	#region get
	/**
	 * Получение данных по подразделению и профилю врача
	 * @param $data
	 * @return array|bool
	 */
	function getDataMedStafFact($data)
	{
		return TimetableGraf_model_get::getDataMedStafFact($this, $data);
	}

	/**
	 * Получение расписания для поликлиники в АРМе врача
	 * @param $data
	 * @param bool $OnlyPlan
	 * @return array|bool
	 */
	function GetDataPolka($data, $OnlyPlan = false)
	{
		return TimetableGraf_model_get::GetDataPolka($this, $data, $OnlyPlan);
	}

	/**
	 * Получение расписания для стационара
	 * @param $data
	 * @return array|bool
	 */
	function GetDataStac($data)
	{
		return TimetableGraf_model_get::GetDataStac($this, $data);
	}

	/**
	 * Получение расписания на заданную дату
	 * @param $data
	 * @param bool $OnlyPlan
	 * @return array|bool
	 */
	function getListByDay($data, $OnlyPlan = false)
	{
		return TimetableGraf_model_get::getListByDay($this, $data, $OnlyPlan);
	}

	/**
	 * Получение номера кабинета по врачу и дате (если указана)
	 * @param $data
	 * @return bool|mixed
	 */
	function getDoctorRoom($data)
	{
		return TimetableGraf_model_get::getDoctorRoom($this, $data);
	}

	/**
	 * Получение статуса записи
	 * @param $data
	 * @return array|bool
	 */
	function getTimeTableGrafStatus($data)
	{
		return TimetableGraf_model_get::getTimeTableGrafStatus($this, $data);
	}

	/**
	 * @param $data
	 * @return int
	 * @throws Exception
	 */
	function getFreeTimetable($data)
	{
		return TimetableGraf_model_get::getFreeTimetable($this, $data);
	}

	/**
	 * Получение расписания для редактирования
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function getTimetableGrafForEdit($data)
	{
		return TimetableGraf_model_get::getTimetableGrafForEdit($this, $data);
	}

	/**
	 * Выборка расписания на первом этапе
	 * @param $data
	 * @return array|bool
	 */
	function getListTimetableLpu($data)
	{
		return TimetableGraf_model_get::getListTimetableLpu($this, $data);
	}

	/**
	 * Получение списка подразделений для старой формы записи
	 * @param $data
	 * @return array|bool
	 */
	function getListTimetableLpuUnit($data)
	{
		return TimetableGraf_model_get::getListTimetableLpuUnit($this, $data);
	}

	/**
	 * Получение списка врачей для старой формы записи
	 * @param $data
	 * @return array|bool
	 */
	function getListTimetableMedPersonal($data)
	{
		return TimetableGraf_model_get::getListTimetableMedPersonal($this, $data);
	}

	/**
	 * Получение списка служб для старой формы записи
	 * @param $data
	 * @return array|bool
	 */
	function getListTimetableMedService($data)
	{
		return TimetableGraf_model_get::getListTimetableMedService($this, $data);
	}

	/**
	 * Получение списка отделений для старой формы записи
	 * @param $data
	 * @return array|bool
	 */
	function getListTimetableLpuSection($data)
	{
		return TimetableGraf_model_get::getListTimetableLpuSection($this, $data);
	}

	/**
	 * Получение атрибутов бирки по идентификатору
	 * @param $data
	 * @return array|false
	 */
	public function getTimeTableGrafById($data)
	{
		return TimetableGraf_model_get::getTimeTableGrafById($this, $data);
	}

	public function getRecord($data)
    {
        return TimetableGraf_model_get::getRecord($this, $data);
    }

	/**
	 * Получение записей на прием по МО
	 * @param $data
	 * @return array|false
	 */
	function getTimeTableGrafbyMO($data)
	{
		return TimetableGraf_model_get::getTimeTableGrafbyMO($this, $data);
	}

	/**
	 * Получение данных об изменениях по биркам поликлиники
	 * @param $data
	 * @return array|false
	 */
	function getTimeTableGrafByUpdPeriod($data)
	{
		return TimetableGraf_model_get::getTimeTableGrafByUpdPeriod($this, $data);
	}

	/**
	 * Получение свободных дат приема
	 * @param $data
	 * @return array|false
	 */
	function getTimeTableGrafFreeDate($data)
	{
		return TimetableGraf_model_get::getTimeTableGrafFreeDate($this, $data);
	}

	/**
	 * Получение свободного времени приема
	 * @param $data
	 * @return array|false
	 */
	function getTimeTableGrafFreeTime($data)
	{
		return TimetableGraf_model_get::getTimeTableGrafFreeTime($this, $data);
	}

	/**
	 * Получение расписания на один день
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function getTimetableGrafGroup($data)
	{
		return TimetableGraf_model_get::getTimetableGrafGroup($this, $data);
	}

	/**
	 * Получение расписания на один день
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function getTimetableGrafOneDay($data)
	{
		return TimetableGraf_model_get::getTimetableGrafOneDay($this, $data);
	}

	/**
	 * Получение данных для отображения и открытия самых часто используемых пользователем расписаний
	 * @param $data
	 * @return array|bool
	 */
	function getTopTimetable($data)
	{
		return TimetableGraf_model_get::getTopTimetable($this, $data);
	}

	/**
	 * Получение истории изменения примечаний по бирке
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function getTTDescrHistory($data)
	{
		return TimetableGraf_model_get::getTTDescrHistory($this, $data);
	}

	/**
	 * Данные пользователя для отправки в письме
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function getTTGDataForMail($data)
	{
		return TimetableGraf_model_get::getTTGDataForMail($this, $data);
	}

	/**
	 * Поиск интернет-записи для модерации
	 * @param $data
	 * @return array|bool
	 */
	function getTTGForModeration($data)
	{
		return TimetableGraf_model_get::getTTGForModeration($this, $data);
	}

	/**
	 * Получение истории изменения бирки поликлиники
	 * @param $data
	 * @return array|bool
	 */
	function getTTGHistory($data)
	{
		return TimetableGraf_model_get::getTTGHistory($this, $data);
	}

	/**
	 * Получение информации по бирке поликлиники
	 * @param $data
	 * @return array|bool
	 */
	function getTTGInfo($data)
	{
		return TimetableGraf_model_get::getTTGInfo($this, $data);
	}

	/**
	 * Получение типа бирки и id MO
	 * @param $data
	 * @return array|bool
	 */
	function getTTGType($data)
	{
		return TimetableGraf_model_get::getTTGType($this, $data);
	}
	#endregion get
	#region set
	/**
	 * Множественная модерация бирок в поликлинике (одобрение/отказ/подтверждение)
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function setMultipleTimetableGrafModeration($data)
	{
		if (empty($data["Status"]) || empty($data["TimetableGraf_ids"]) || !is_array($data["TimetableGraf_ids"])) {
			return $this->createError(null, 'Переданы некорректные данные');
		}
		if (count($data["TimetableGraf_ids"]) == 0 || count($data["TimetableGraf_ids"]) > 999) {
			return $this->createError(null, 'Количество передаваемых бирок на модерацию должно быть меньше 1000');
		}
		$TimetableGraf_ids = array_map("intval", array_filter($data["TimetableGraf_ids"], "is_numeric"));
		$sql = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_timetablegraf_moderategroup(
				timetablegraf_listid := :TimetableGraf_ids,
			    timetablegraf_ismoderated := :Status,
			    pmuser_id := :pmUser_id
			);
		";
		$sqlParams = [
			"TimetableGraf_ids" => implode(",", $TimetableGraf_ids),
			"Status" => $data["Status"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$res = $this->db->query($sql, $sqlParams);
		$resp = $res->result("array");
		return $resp[0];
	}

	/**
	 * Метод идентификации человека в ЛПУ
	 * @param $data
	 * @return array
	 */
	function setPersonInTimetableStac($data)
	{
		$query = "
			update EmergencyData
			set Person_lid = :Person_id
			where EmergencyData_id = (
			    select EmergencyData_id
			    from v_TimetableStac_lite
			    where TimetableStac_id = :TimetableStac_id
			)
		";
		$this->db->query($query, $data);
		return [["Error_Msg" => ""]];
	}

	#endregion set
}