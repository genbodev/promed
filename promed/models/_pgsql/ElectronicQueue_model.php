<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      ElectronicQueue
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 *
 * @property CI_DB_driver $db
 * @property ElectronicTalon_model $ElectronicTalon_model
 * @property Person_model $Person_model
 * @property ProfService_model $ProfService_model
 */
class ElectronicQueue_model extends swPgModel
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	public $dateTimeForm108 = "HH24:MI:SS";

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Загрузка области данных АРМ
	 * @param $data
	 * @return array|false
	 */
	function loadWorkPlaceGrid($data)
	{
		$this->load->helper("Reg");
		$queryParams = [
			"UslugaComplexMedService_id" => $data["UslugaComplexMedService_id"],
			"TimetableMedService_Day" => TimeToDay(strtotime($data["onDate"])),
			"curTimetableMedService_Day" => TimeToDay(time())
		];
		$orderby = "";
		if (!empty($data["session"]["CurARM"]["ElectronicService_id"])) {
			$orderby .= "case when et.ElectronicService_id = :ElectronicService_id then 0 else 1 end,";
			$queryParams["ElectronicService_id"] = $data["session"]["CurARM"]["ElectronicService_id"];
		}
		$query = "
			select
				ttms.TimetableMedService_id as \"TimetableMedService_id\",
				et.ElectronicTalon_Num as \"ElectronicTalon_Num\",
				coalesce(to_char(ttms.TimetableMedService_begTime, '{$this->dateTimeForm108}'), 'б/з') as \"TimetableMedService_begTime\",
				rtrim(coalesce(ps.Person_SurName, ''))||rtrim(coalesce(' '||ps.Person_FirName, ''))||rtrim(coalesce(' '||ps.Person_SecName, '')) as \"Person_Fio\",
				to_char(ps.Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_BirthDay\",
				epdd.EvnPLDispDriver_Num as \"EvnPLDispDriver_Num\",
				ets.ElectronicTalonStatus_Name as \"ElectronicTalonStatus_Name\",
				coalesce(es.ElectronicService_Code::varchar||' ', '')||es.ElectronicService_Name as \"ElectronicService_Name\",
				ps.Person_id as \"Person_id\",
				ps.PersonEvn_id as \"PersonEvn_id\",
				ps.Server_id as \"Server_id\",
				et.ElectronicService_id as \"ElectronicService_id\",
				et.ElectronicTalonStatus_id as \"ElectronicTalonStatus_id\",
				epdd.EvnPLDispDriver_id as \"EvnPLDispDriver_id\",
				case when ttms.TimetableMedService_Day = :curTimetableMedService_Day then 1 else 0 end as \"IsCurrentDate\",
				et.ElectronicTalon_id as \"ElectronicTalon_id\",
				ps.Person_IsUnknown as \"Person_IsUnknown\",
				rmt.RecMethodType_Name as \"RecMethodType_Name\"
			from
				v_TimetableMedService_lite ttms
				left join v_ElectronicTalon et on et.EvnDirection_id = ttms.EvnDirection_id
				left join v_PersonState ps on ps.Person_id = ttms.Person_id
				left join v_EvnPLDispDriver epdd on epdd.EvnDirection_id = ttms.EvnDirection_id
				left join v_ElectronicTalonStatus ets on ets.ElectronicTalonStatus_id = et.ElectronicTalonStatus_id
				left join v_ElectronicService es on es.ElectronicService_id = et.ElectronicService_id
				left join v_RecMethodType rmt on rmt.RecMethodType_id = ttms.RecMethodType_id
			where ttms.UslugaComplexMedService_id = :UslugaComplexMedService_id
			  and ttms.TimetableMedService_Day = :TimetableMedService_Day
			order by
				{$orderby}
				case when et.ElectronicTalon_Num is not null then 0 else 1 end,
				case when et.ElectronicTalonStatus_id = 4 then 1 else 0 end,
				case when ttms.Person_id is not null then 0 else 1 end,
				TimetableMedService_begTime
		";
		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Установка статуса электронного талона при неявке пациента
	 * @param $data
	 * @return array
	 */
	function setNoPatientTalonStatus($data)
	{
		$query = "
			select count(*) as cnt
			from v_ElectronicTalonHist
			where ElectronicTalon_id = :ElectronicTalon_id
			  and ElectronicTalonStatus_id = 2
		";
		$queryParams = ["ElectronicTalon_id" => $data["ElectronicTalon_id"]];
		$resp_check = $this->queryResult($query, $queryParams);
		if (!empty($resp_check[0]["cnt"]) && $resp_check[0]["cnt"] >= 2) {
			// Если в истории статусов для текущего пункта обслуживания есть 2 записи со статусом «Вызван», то "Отменён"
			// получаем данные по бирке (не отменять если со временем и текущее дата время меньше, чем [дата время бирки + время опоздания при регистрации в очереди (мин.)])
			$query = "
				select
					case when es.ElectronicService_Num = 1 and ttms.TimetableMedService_begTime is not null and dateadd('MINUTE', eqi.ElectronicQueueInfo_LateTimeMin, ttms.TimetableMedService_begTime) > tzgetdate()
						then 1
						else 0
					end as noCancel
				from
					v_ElectronicTalon et
					left join v_ElectronicQueueInfo eqi on eqi.ElectronicQueueInfo_id = et.ElectronicQueueInfo_id
					left join v_TimetableMedService_lite ttms on ttms.EvnDirection_id = et.EvnDirection_id
					left join v_ElectronicService es on es.ElectronicService_id = et.ElectronicService_id
				where et.ElectronicTalon_id = :ElectronicTalon_id
			";
			$queryParams = ["ElectronicTalon_id" => $data["ElectronicTalon_id"]];
			$resp_noc = $this->queryResult($query, $queryParams);
			$data["ElectronicTalonStatus_id"] = (!empty($resp_noc[0]["noCancel"])) ? 1 : 5;
		} else {
			// Иначе "Ожидает"
			$data["ElectronicTalonStatus_id"] = 1;
		}
		$this->load->model("ElectronicTalon_model");
		return $this->ElectronicTalon_model->setElectronicTalonStatus($data);
	}

	/**
	 * Проверка активности электронной очереди
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function checkElectronicQueueInfoEnabled($data)
	{
		$query = "
			select
				eqi.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\",
				coalesce(eqi.ElectronicQueueInfo_IsOff, 1) as \"ElectronicQueueInfo_IsOff\"
			from
				v_ElectronicQueueInfo eqi
				inner join v_ElectronicService es on es.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id
			where es.ElectronicService_id = :ElectronicService_id
		";
		$queryParams = ["ElectronicService_id" => $data["ElectronicService_id"]];
		$resp = $this->queryResult($query, $queryParams);
		if (empty($resp[0]["ElectronicQueueInfo_id"])) {
			throw new Exception("Ошибка определения активности электронной очереди");
		}
		return [
			"Error_Msg" => "",
			"ElectronicQueueInfo_id" => $resp[0]["ElectronicQueueInfo_id"],
			"ElectronicQueueInfo_IsOff" => $resp[0]["ElectronicQueueInfo_IsOff"]
		];
	}

	/**
	 * Замена неизвестного человека на известного
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function fixPersonUnknown($data)
	{
		// убеждаемся, что выбран именно неизвестный человек
		$query = "
			select
				pso.Person_id as \"Person_oldId\",
				psn.Person_id as \"Person_newId\",
				psn.Server_id as \"Server_newId\",
				psn.PersonEvn_id as \"PersonEvn_newId\"
			from
				v_PersonState pso
				left join v_PersonState psn on psn.Person_id = :Person_newId
			where pso.Person_id = :Person_oldId
			  and pso.Person_IsUnknown = 2
			limit 1
		";
		$queryParams = [
			"Person_oldId" => $data["Person_oldId"],
			"Person_newId" => $data["Person_newId"]
		];
		$resp = $this->queryResult($query, $queryParams);
		if (empty($resp[0]["Person_oldId"])) {
			throw new Exception("Ошибка получения данных по неизвестному человеку");
		}
		if (empty($resp[0]["Person_newId"])) {
			throw new Exception("Ошибка получения данных по человеку");
		}
		$this->beginTransaction();
		// Бирка связывается с идентификатором выбранного человека.
		// Направление связывается с идентификатором выбранного человека.
		// Талон ЭО связывается с идентификатором выбранного человека.
		$query = "
			update TimetableMedService
			set Person_id = :Person_newId,
			    pmUser_updID = :pmUser_id,
			    TimeTableMedService_updDT = tzgetdate()
			where Person_id = :Person_oldId;
			update ElectronicTalon
			set Person_id = :Person_newId,
			    pmUser_updID = :pmUser_id,
			    ElectronicTalon_updDT = tzgetdate()
			where Person_id = :Person_oldId;
			update Evn
			set Person_id = :Person_newId,
			    Server_id = :Server_newId,
			    PersonEvn_id = :PersonEvn_newId,
			    pmUser_updID = :pmUser_id,
			    Evn_updDT = tzgetdate()
			where Person_id = :Person_oldId
			  and EvnClass_id = 27;
		";
		$queryParams = [
			"Server_newId" => $resp[0]["Server_newId"],
			"PersonEvn_newId" => $resp[0]["PersonEvn_newId"],
			"Person_newId" => $resp[0]["Person_newId"],
			"Person_oldId" => $resp[0]["Person_oldId"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$this->queryResult($query, $queryParams);
		// Человек с признаком «Неизвестный», ранее связанный с биркой, направлением, талоном ЭО, удаляется из БД.
		// Если у него нет других учетных документов
		$this->load->model("Person_model");
		$funcParams = ["Person_id" => $resp[0]["Person_oldId"]];
		$toDel = $this->Person_model->checkToDelPerson($funcParams);
		if (empty($toDel["Person_id"])) {
			$funcParams = [
				"Person_id" => $resp[0]["Person_oldId"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$resp_del = $this->Person_model->deletePerson($funcParams);
			if (!empty($resp_del[0]["Error_Msg"])) {
				$this->rollbackTransaction();
				return $resp_del[0];
			}
		}
		$this->commitTransaction();
		return ["Error_Msg" => ""];
	}

	/**
	 * Проверка на занятость текущего сервиса
	 * @param $data
	 * @return array
	 */
	function checkIsDigitalServiceBusy($data)
	{
		$params["ElectronicService_id"] = $data["ElectronicService_id"];
		$ttg_type = "TimetableGraf";
		if (!empty($data["ttg_type"])) {
			$ttg_type = $data["ttg_type"];
		}
		if ($ttg_type == "TimetableGraf") {
			$query = "
                select
                    et.ElectronicTalon_id as \"ElectronicTalon_id\",
                    et.ElectronicTalon_Num as \"ElectronicTalon_Num\",
                    ed.EvnDirection_TalonCode as \"EvnDirection_TalonCode\",
                    coalesce(to_char(ttg.TimetableGraf_begTime, '{$this->dateTimeForm104}'), to_char(et.ElectronicTalon_insDT, '{$this->dateTimeForm104}')) as \"day\",
					coalesce(to_char(ttg.TimetableGraf_begTime, '{$this->dateTimeForm108}'), to_char(et.ElectronicTalon_insDT, '{$this->dateTimeForm108}')) as \"time\"
                from
                	v_ElectronicTalon et
                    left join v_EvnDirection_all ed on ed.EvnDirection_id = et.EvnDirection_id
                    left join v_TimetableGraf_lite ttg on ttg.TimetableGraf_id = ed.TimetableGraf_id
                    left join v_Day day on day.day_id = (ttg.TimetableGraf_Day - 1)
                where et.ElectronicTalonStatus_id = 3
                  and et.ElectronicService_id = :ElectronicService_id
                  and et.ElectronicTalon_insDT::date = tzGetdate()
                order by ttg.TimetableGraf_begTime desc
				limit 1
		    ";
		} elseif ($ttg_type == "TimetableMedService") {
			$query = "
                select
                    et.ElectronicTalon_id as \"ElectronicTalon_id\",
                    et.ElectronicTalon_Num as \"ElectronicTalon_Num\",
                    ed.EvnDirection_TalonCode as \"EvnDirection_TalonCode\",
                    coalesce(to_char(ttms.TimetableMedService_begTime, '{$this->dateTimeForm104}'), to_char(day.day_date, '{$this->dateTimeForm104}')) as \"day\",
                    to_char(ttms.TimetableMedService_begTime, '{$this->dateTimeForm108}') as \"time\"
                from
                	v_ElectronicTalon et
                    left join v_EvnDirection_all ed on ed.EvnDirection_id = et.EvnDirection_id
                    left join v_TimetableMedService_lite ttms on ttms.TimeTableMedService_id = ed.TimeTableMedService_id
                    left join v_Day day on day.day_id = (ttms.TimeTableMedService_Day - 1)
                where et.ElectronicTalonStatus_id = 3
                  and et.ElectronicService_id = :ElectronicService_id
                  and et.ElectronicTalon_insDT::date = tzGetdate()
                order by ttms.TimetableMedService_begTime desc
				limit 1
		    ";
		}
		$resp = $this->queryResult($query, $params);
		if (empty($resp[0]["ElectronicTalon_id"])) {
			return ["Error_Msg" => ""];
		}
		return [
			"Error_Msg" => "",
			"data" => ["ElectronicTalon_id" => $resp[0]["ElectronicTalon_id"]],
			"Check_Msg" =>
				"Перед " .
				((!empty($data["ServiceAction"]) && ($data["ServiceAction"] == "doCall" || $data["ServiceAction"] == "call")) ? "вызовом" : "приемом") .
				" нового пациента нужно завершить обслуживание для пациента с талоном №{$resp[0]["EvnDirection_TalonCode"]} от {$resp[0]["day"]}" .
				(!empty($resp[0]["time"]) ? " (" . $resp[0]["time"] . ")" : ""),
		];
	}

	/**
	 * Завершение приёма
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function finishCall($data)
	{
		$result = ["Error_Msg" => ""];
		// получаем связанные события
		$query = "
			select
			    et.ElectronicTalon_id as \"ElectronicTalon_id\",
				et.EvnDirection_id as \"EvnDirection_id\",
				et.ElectronicService_id as \"ElectronicService_id\",
				mst.MedServiceType_SysNick as \"MedServiceType_SysNick\"
			from
				v_ElectronicTalon et
				left join v_ElectronicQueueInfo eqi on eqi.ElectronicQueueInfo_id = et.ElectronicQueueInfo_id
				left join v_MedService ms on ms.MedService_id = eqi.MedService_id
				left join v_MedServiceType mst on mst.MedServiceType_id = ms.MedServiceType_id
			where et.ElectronicTalon_id = :ElectronicTalon_id
			limit 1
		";
		$queryParams = ["ElectronicTalon_id" => $data["ElectronicTalon_id"]];
		$talon = $this->getFirstRowFromQuery($query, $queryParams);
		if (!empty($talon["ElectronicTalon_id"])) {
			if ($this->usePostgreLis && !empty($talon["MedServiceType_SysNick"]) && in_array($talon["MedServiceType_SysNick"], ["pzm", "lab"])) {
				$this->load->swapi("lis");
				$lisParams = ["EvnDirection_id" => $talon["EvnDirection_id"]];
				$dir_data = $this->lis->GET("EvnDirection/getElectronicTalonDirectionData", $lisParams, "single");
			} else {
				$query = "
        			select
        				EvnDirection_id as \"EvnDirection_id\",
        				DirType_id as \"DirType_id\"
					from v_EvnDirection_all
					where EvnDirection_id = :EvnDirection_id
					limit 1
				";
				$queryParams = ["EvnDirection_id" => $talon["EvnDirection_id"]];
				$dir_data = $this->getFirstRowFromQuery($query, $queryParams);
			}
			if (!empty($dir_data) && $this->isSuccessful($dir_data)) {
				if (!empty($talon['ElectronicService_id']) && $talon['ElectronicService_id'] !== $data['ElectronicService_id']) {
					return array('Error_Msg' => 'Нельзя завершить талон электронной очереди обслуживаемый в настоящий момент у другого врача');
				}

				if ($dir_data["DirType_id"] == 25) {
					$this->load->model("ProfService_model");
					$result = $this->ProfService_model->finishCall($data);
				} else {
					$this->load->model("ElectronicTalon_model");
					$funcParams = [
						"ElectronicTalon_id" => $data["ElectronicTalon_id"],
						"ElectronicService_id" => $data["ElectronicService_id"],
						"ElectronicTalonStatus_id" => 4,
						"pmUser_id" => $data["pmUser_id"]
					];
					$this->ElectronicTalon_model->setElectronicTalonStatus($funcParams);
				}

			} else {
				return array('Error_Msg' => 'Не найдено направление связанное с талоном электронной очереди');
			}

		} else {
			return array('Error_Msg' => 'Талон электронной очереди не найден');
		}
		return $result;
	}

	/**
	 * Приём пациента
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function applyCall($data)
	{
		$this->load->model("ElectronicTalon_model");
		$this->beginTransaction();
		$params = [
			"ElectronicTalon_id" => $data["ElectronicTalon_id"],
			"ElectronicService_id" => (!empty($data["ElectronicService_id"]) ? $data["ElectronicService_id"] : null),
			"ElectronicTalonStatus_id" => 3,
			"pmUser_id" => $data["pmUser_id"]
		];
		$setStatus = $this->ElectronicTalon_model->setElectronicTalonStatus($params);
		if (!empty($setStatus["Error_Msg"])) {
			throw new Exception($setStatus["Error_Msg"]);
		}
		if (empty($data["DispClass_id"])) {
			$this->commitTransaction();
			return ["Error_Msg" => ""];
		}
		switch ($data["DispClass_id"]) {
			case 10:
				// создаем профосмотр, если не создан
				if (empty($data["EvnPLDispTeenInspection_id"])) {
					return $this->createEvnPLDispAndAgreeConsent($data);
				}
				$this->commitTransaction();
				return ["Error_Msg" => "", "EvnPLDispTeenInspection_id" => $data["EvnPLDispTeenInspection_id"]];
				break;
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Проверка есть ли у связи ПО-ОБЪЕКТ-СОТРУДНИК направления без кода брони
	 * @param $data
	 * @return mixed
	 */
	function isEnableEvnDirectionsWithEmptyTalonCode($data)
	{
		$targetField = "";
		$entity = "";
		$response["checkResult"] = false;
		if (!empty($data["MedStaffFact_id"])) {
			$entity = "TimetableGraf";
			$targetField = "MedStaffFact_id";
		} else if (!empty($data["Resource_id"])) {
			$entity = "TimetableResource";
			$targetField = "Resource_id";
		} else if (!empty($data["UslugaComplexMedService_id"])) {
			$entity = "TimetableMedService";
			$targetField = "UslugaComplexMedService_id";
		} else if (!empty($data["MedService_id"])) {
			$entity = "TimetableMedService";
			$targetField = "MedService_id";
		}
		if (!empty($targetField)) {
			$params[$targetField] = $data[$targetField];
			$selectString = "tt.TimetableGraf_id";
			$fromString = "
				v_{$entity}_lite as tt
				inner join v_EvnDirection_all as ed on tt.EvnDirection_id = ed.EvnDirection_id
				inner join v_MedServiceElectronicQueue as mseq on mseq.{$targetField} = tt.{$targetField}
			";
			$whereString = "
					{$entity}_begTime::date > tzgetdate()::date
				and ed.EvnDirection_TalonCode is null
				and tt.{$targetField} = :{$targetField}
			";
			$query = "
				select {$selectString}
				from {$fromString}
				where {$whereString}
				limit 1
			";
			$result = $this->getFirstResultFromQuery($query, $params);
			if (!empty($result)) {
				$response["checkResult"] = true;
			}
		}
		return $response;
	}

	/**
	 * Создаем коды бронирования для записей, у которых нет кода брони после создания очереди
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function generateTalonCodeForExistedRecords($data)
	{
		$filter = "";
		$params = [];
		$procedure = "";
		$targetField = "";
		if (!empty($data["MedStaffFact_id"])) {
			$entity = "TimetableGraf";
			$targetField = "MedStaffFact_id";
			$procedure = "xp_GenTalonCodeGraf";
		} else if (!empty($data["Resource_id"])) {
			$entity = "TimetableResource";
			$targetField = "Resource_id";
			$procedure = "xp_GenTalonCodeResource";
		} else if (!empty($data["UslugaComplexMedService_id"])) {
			$entity = "TimetableMedService";
			$targetField = "UslugaComplexMedService_id";
			$procedure = "xp_GenTalonCodeMedService";
		} else if (!empty($data["MedService_id"])) {
			$entity = "TimetableMedService";
			$targetField = "MedService_id";
			$procedure = "xp_GenTalonCodeMedService";
		}
		if (!empty($targetField)) {
			if (!empty($data["inList"])) {
				$inList = "";
				foreach ($data["inList"] as $id) {
					$inList .= "{$id},";
				}
				if (!empty($inList)) {
					$inList = rtrim($inList, ",");
					$filter = " and tt.{$targetField} in({$inList}) ";
				}
			} else {
				$filter = "
                    and tt.{$targetField} = :{$targetField}
                ";
				$params[$targetField] = $data[$targetField];
			}
			$selectString = "
				tt.{$entity}_id as \"{$entity}_id\",
				ed.Lpu_did as \"Lpu_did\",
				ed.EvnDirection_id as \"EvnDirection_id\"
			";
			$fromString = "
				v_{$entity}_lite as tt
				inner join v_EvnDirection_all as ed on tt.EvnDirection_id = ed.EvnDirection_id
				inner join v_MedServiceElectronicQueue as mseq on mseq.{$targetField} = tt.{$targetField}
			";
			$whereString = "
					{$entity}_begTime::date > tzgetdate()::date
				and ed.EvnDirection_TalonCode is null
				{$filter}
			";
			$query = "
				select {$selectString}
				from {$fromString}
				where {$whereString}
			";
			/**
			 * @var CI_DB_result $result
			 * @var CI_DB_result $resultSp
			 */
			$result = $this->db->query($query, $params);
			if (!is_object($result)) {
				throw new Exception("Ошибка выполнения запроса к БД");
			}
			$result = $result->result_array();
			foreach ($result as $resultRow) {
				$selectString = "evndirection_taloncode";
				$query = "
					select {$selectString}
					from {$procedure}(
					    {$entity}_id := 0,
					    lpu_did := 0
					);
				";
				$paramsSp = [
					"timetablegraf_id" => $resultRow["{$entity}_id"],
					"lpu_did" => $resultRow["Lpu_did"]
				];
				$resultSp = $this->db->query($query, $paramsSp);
				$evndirection_taloncode = @$resultSp[0]["evndirection_taloncode"];
				if ($evndirection_taloncode != null) {
					$paramsUpdate = [
						"EvnDirection_id" => $resultRow["EvnDirection_id"],
						"EvnDirection_TalonCode" => $evndirection_taloncode
					];
					$query = "
						UPDATE EvnDirection
						SET EvnDirection_TalonCode = :EvnDirection_TalonCode
						WHERE evn_id = :EvnDirection_id;
						UPDATE Evn
						SET pmUser_updID = 1
						WHERE Evn_id = :EvnDirection_id
					";
					$this->db->query($query, $paramsUpdate);
				}
			}
		}
		return ["Error_Msg" => null];
	}

	/**
	 * получим тип ЭО линейный или нелинейный
	 * @param $data
	 * @return bool
	 */
	public function getElectronicQueueType($data)
	{
		$is_linear_electronic_queue = true; // схема линейная
		$non_linear_list = $this->config->item("NON_LINEAR_ELECTRONIC_QUEUE_LIST");
		if (!empty($non_linear_list) && in_array($data["ElectronicQueueInfo_id"], $non_linear_list)) {
			// схема нелинейная
			$is_linear_electronic_queue = false;
		}
		return $is_linear_electronic_queue;
	}
}
