<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      ProfService
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 *
 * @property CI_DB_driver $db
 * @property ElectronicTalon_model $ElectronicTalon_model
 * @property Person_model $Person_model
 * @property ElectronicQueue_model $ElectronicQueue_model
 */
class ProfService_model extends swPgModel
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
        $this->load->helper('Reg');

        $queryParams = array(
            'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id'],
            'TimetableMedService_Day' => TimeToDay(strtotime($data['onDate'])),
            'curTimetableMedService_Day' => TimeToDay(time()),
            'ElectronicService_id' => $data['ElectronicService_id']
        );

        $filter = ""; $join = "";

        // если фильтруем только по этому ПО
        if (!empty($data['byElectronicService'])) {
            $filter .= " and es.ElectronicService_id = :ElectronicService_id ";
            $join .= " left join v_ElectronicService es on es.ElectronicService_id = et.ElectronicService_id ";
        }

        $query = "
			select
				ttms.TimetableMedService_id as \"TimetableMedService_id\",
				et.ElectronicTalon_Num as \"ElectronicTalon_Num\",
				ps.Person_SurName as \"Person_Surname\",
				ps.Person_FirName as \"Person_Firname\",
				ps.Person_SecName as \"Person_Secname\",
				coalesce(to_char(ttms.TimetableMedService_begTime, 'HH24:MI'), 'б/з') as \"TimetableMedService_begTime\",
				rtrim(coalesce(ps.Person_SurName,'')) || rtrim(coalesce(' ' || ps.Person_FirName,'')) || rtrim(coalesce(' ' || ps.Person_SecName, '')) as \"Person_Fio\",
				to_char(ps.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\",
				
				--формируем псевдо-статус если ПО не такой как у нас
				case when (eth.ElectronicTalon_IsProcessed is not null and et.ElectronicTalonStatus_id != 5)
					then 'Обслужен'
					else
						case when coalesce(et.ElectronicService_id, :ElectronicService_id) != :ElectronicService_id
						then esoa.ElectronicService_Name
						else ets.ElectronicTalonStatus_Name
						end
				end as \"ElectronicTalonStatus_Name\",
				--получаем признак что талон уже обслуживается
				case when coalesce(et.ElectronicService_id, :ElectronicService_id) != :ElectronicService_id
						then 1
						else null
				end as \"ElectronicTalon_IsBusy\",
				--получаем признак того что ПО уже обработал талон
				eth.ElectronicTalon_IsProcessed as \"ElectronicTalon_IsProcessed\",
				eth.ElectronicTalonHist_processedInsDT as \"ElectronicTalonHist_processedInsDT\",
				eth2.ElectronicTalonHist_insDT as \"ElectronicTalonHist_insDT\",
				case when (eth.ElectronicTalon_IsProcessed is null and et.ElectronicTalonStatus_id = 1)
					then DATEDIFF('mi', eth2.ElectronicTalonHist_insDT, getdate())
					else ''
				end as \"ElectronicTalon_IdleTime\",
				ps.Person_id as \"Person_id\",
				ps.PersonEvn_id as \"PersonEvn_id\",
				ps.Server_id as \"Server_id\",
				et.ElectronicService_id as \"ElectronicService_id\",
				et.ElectronicTalonStatus_id as \"ElectronicTalonStatus_id\",
				epdd.EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_id\",
				case when ttms.TimetableMedService_Day = :curTimetableMedService_Day then 1 else 0 end as \"IsCurrentDate\",
				et.ElectronicTalon_id as \"ElectronicTalon_id\",
				ps.Person_IsUnknown as \"Person_IsUnknown\",
				rmt.RecMethodType_Name as \"RecMethodType_Name\",
				ttms.EvnDirection_id as \"EvnDirection_id\"
			from v_TimetableMedService_lite ttms
			left join v_ElectronicTalon et on et.EvnDirection_id = ttms.EvnDirection_id
			left join v_PersonState ps on ps.Person_id = ttms.Person_id
			-- поменяли left на inner #139518
			inner join v_EvnPLDispTeenInspection epdd on epdd.EvnDirection_id = ttms.EvnDirection_id
			left join v_ElectronicTalonStatus ets on ets.ElectronicTalonStatus_id = et.ElectronicTalonStatus_id
			left join v_RecMethodType rmt on rmt.RecMethodType_id = ttms.RecMethodType_id
			left join v_ElectronicQueueInfo eq on eq.ElectronicQueueInfo_id = et.ElectronicQueueInfo_id
			{$join}
						
			--получаем название пункта в статусе
			left join lateral (
				select
					coalesce(esoa.ElectronicService_Name, 'На осмотре') as ElectronicService_Name
				from v_ElectronicService esoa
				where esoa.ElectronicService_id = et.ElectronicService_id
				limit 1
			) esoa on true

			--получаем признак того что ПО уже обработал талон
			left join lateral (
				select
					case when eth.ElectronicTalonHist_id is null
						then 0 
						else 1 
						end as ElectronicTalon_IsProcessed,
					eth.ElectronicTalonHist_insDT as ElectronicTalonHist_processedInsDT
				from v_ElectronicTalonHist eth
				where
					eth.ElectronicService_id = :ElectronicService_id
					and (eth.ElectronicTalonStatus_id = 4 or eth.ElectronicTalonStatus_id = 5)
					and eth.ElectronicTalon_id = et.ElectronicTalon_id
					limit 1
			) eth on true

			--получаем дату последнего обслуживания
			left join lateral (
				select
					max(eth2.ElectronicTalonHist_insDT) as ElectronicTalonHist_insDT
				from v_ElectronicTalonHist eth2
				where eth2.ElectronicTalon_id = et.ElectronicTalon_id
				limit 1
			) eth2 on true
			where
				ttms.UslugaComplexMedService_id = :UslugaComplexMedService_id
				and ttms.TimetableMedService_Day = :TimetableMedService_Day
				and et.ElectronicTalon_id is not null
				{$filter}
			order by
				case when et.ElectronicTalonStatus_id = 5 then 1 else 0 end,
				eth.ElectronicTalonHist_processedInsDT asc,
				--обслуживаются в ЭТОМ ПО
				case when et.ElectronicService_id = :ElectronicService_id then 0 else 1 end,
				--последняя дата по обслуживанию из истории
				eth2.ElectronicTalonHist_insDT desc,
				case when et.ElectronicTalon_Num is not null then 0 else 1 end
		";

        //echo '<pre>',print_r(getDebugSQL($query, $queryParams)),'</pre>'; die();
        $resp = $this->queryResult($query, $queryParams);
        return $resp;
	}

	/**
	 * Установка статуса электронного талона при неявке пациента
	 * @param $data
	 * @return array
	 */
	function setNoPatientTalonStatus($data)
	{
		$query = "
			select count(*) as \"cnt\"
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
					case when es.ElectronicService_Num = 1
						and ttms.TimetableMedService_begTime is not null
						and ttms.TimetableMedService_begTime + '00:'||eqi.ElectronicQueueInfo_LateTimeMin||':00'::time > tzgetdate()
						then 1
						else 0
					end as \"noCancel\"
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
	 * @return array|Exception
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
		$params = [
			"Server_newId" => $resp[0]["Server_newId"],
			"PersonEvn_newId" => $resp[0]["PersonEvn_newId"],
			"Person_newId" => $resp[0]["Person_newId"],
			"Person_oldId" => $resp[0]["Person_oldId"],
			"pmUser_id" => $data["pmUser_id"]
		];
		try {
			$resp = $this->queryResult("
				update TimetableGraf
				set Person_id = :Person_newId,
					pmUser_updID = :pmUser_id,
					TimetableGraf_updDT = dbo.tzGetDate()
				where Person_id = :Person_oldId; -- Бирка
			", $params);
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]["Error_Code"]);
			}
			$resp = $this->queryResult("
				update TimetableMedService
				set Person_id = :Person_newId,
					pmUser_updID = :pmUser_id,
					TimeTableMedService_updDT = dbo.tzGetDate()
				where Person_id = :Person_oldId; -- Бирка
			", $params);
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]["Error_Code"]);
			}
			$resp = $this->queryResult("
				update ElectronicTalon
				set Person_id = :Person_newId,
					pmUser_updID = :pmUser_id,
					ElectronicTalon_updDT = dbo.tzGetDate()
				where Person_id = :Person_oldId; -- Талон", $params);
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]["Error_Code"]);
			}
			$resp = $this->queryResult("
				update Evn
				set Person_id = :Person_newId,
					Server_id = :Server_newId,
					PersonEvn_id = :PersonEvn_newId,
					pmUser_updID = :pmUser_id,
					Evn_updDT = dbo.tzGetDate()
				where Person_id = :Person_oldId
					and EvnClass_id = 27; -- EvnDirection
			", $params);
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]["Error_Code"]);
			}
		} catch (\Exception $e) {
			$this->rollbackTransaction();
			return $e;
		}
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
	 * Завершение приёма
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function finishCall($data)
	{
		$query = "
			select
				et.ElectronicTalon_id as \"ElectronicTalon_id\",
				et.EvnDirection_uid as \"EvnDirection_uid\",
				epdd.EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_id\",
				coalesce(esRemote.ElectronicService_Num, es.ElectronicService_Num) as \"ElectronicService_Num\",
				coalesce(esRemote.ElectronicQueueInfo_id,es.ElectronicQueueInfo_id) as \"ElectronicQueueInfo_id\",
				coalesce(esRemote.ElectronicService_id, es.ElectronicService_id) as \"ElectronicService_id\",
				evdd.EvnVizitDispDop_id as \"EvnVizitDispDop_id\",
				evdd.SurveyType_Code as \"SurveyType_Code\",
				esRemote.ElectronicService_id as \"isRemoteService\"
			from
				v_ElectronicTalon et
				inner join v_EvnDirection_all ed on ed.EvnDirection_id = et.EvnDirection_id
				left join v_EvnPLDispTeenInspection epdd on epdd.EvnDirection_id = et.EvnDirection_id
				left join v_ElectronicService es on es.ElectronicService_id = et.ElectronicService_id
				left join v_ElectronicService esRemote on esRemote.ElectronicService_tid = es.ElectronicService_id
				-- смотрим сохранен ли осмотр для текущего ПО
				left join lateral(
					select
						evdd.EvnVizitDispDop_id,
						st.SurveyType_Code
					from
						v_DopDispInfoConsent ddic
						inner join v_EvnVizitDispDop evdd on evdd.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
						inner join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
						inner join v_SurveyType st on st.SurveyType_id = stl.SurveyType_id
					where ddic.EvnPLDisp_id = epdd.EvnPLDispTeenInspection_id
					  and evdd.ElectronicService_id = es.ElectronicService_id
				) as evdd on true
			where et.ElectronicTalon_id = :ElectronicTalon_id
		";
		$queryParams = ["ElectronicTalon_id" => $data["ElectronicTalon_id"]];
		$talon = $this->queryResult($query, $queryParams);
		if (empty($talon[0]["ElectronicTalon_id"])) {
			throw new Exception("Ошибка получении информации по талону");
		}
		$params = ["ElectronicQueueInfo_id" => $talon[0]["ElectronicQueueInfo_id"]];
		$dd_found = false;
		if (havingGroup("ProfPed")) {
			foreach ($talon as $t) {
				// еcли завершен осмотр педиатра тогда пускам дальше
				if (!empty($t["EvnVizitDispDop_id"]) && !empty($t["SurveyType_Code"]) && $t["SurveyType_Code"] == 27) {
					$dd_found = true;
					break;
				}
			}
		} else if (!empty($talon[0]["EvnVizitDispDop_id"])) {
			$dd_found = true;
		}
		if (!$dd_found && empty($talon[0]["isRemoteService"])) {
			throw new Exception("Необходимо сохранить результат осмотра в маршрутной карте");
		}
		$this->load->model("ElectronicTalon_model");
		// завершаем наш талон в этом ПО в истории талона
		$funcParams = [
			"ElectronicTalon_id" => $talon[0]["ElectronicTalon_id"],
			"ElectronicService_id" => $talon[0]["ElectronicService_id"],
			"ElectronicTalonStatus_id" => 4,
			"pmUser_id" => $data["pmUser_id"]
		];
		$this->ElectronicTalon_model->updateElectronicTalonHistory($funcParams);

		$this->load->model("ElectronicQueue_model");
		$funcParams = ["ElectronicQueueInfo_id" => $talon[0]["ElectronicQueueInfo_id"]];
		$is_linear_eq = $this->ElectronicQueue_model->getElectronicQueueType($funcParams);
		//если схема линейная идем по порядку
		if ($is_linear_eq) {
			$current = $talon[0]["ElectronicService_Num"];
			$next = intval($current) + 1;
			// определяем макс пор. номер ПО в ЭО
			$services_length = $this->getFirstResultFromQuery("
				select max(es.ElectronicService_Num) as \"services_length\"
				from v_ElectronicService es
				where es.ElectronicQueueInfo_id = :ElectronicQueueInfo_id
			", $params);
			$isLast = ($current == $services_length);
			$params["ElectronicService_Num"] = $next;
			$electronicService_id = null;
			if ($next <= $services_length) {
				// определяем id следующего ПО
				$query = "
					select
						coalesce(esRemote.ElectronicService_Code, es.ElectronicService_Code) as \"ElectronicService_Code\",
						coalesce(esRemote.ElectronicService_Name, es.ElectronicService_Name) as \"ElectronicService_Name\",
						coalesce(esRemote.ElectronicService_id, es.ElectronicService_id) as \"ElectronicService_id\"
					from
						v_ElectronicService es
						inner join v_ElectronicQueueInfo eqi on eqi.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
						left join v_ElectronicService esRemote on esRemote.ElectronicService_id = es.ElectronicService_tid
					where es.ElectronicService_Num = :ElectronicService_Num
					  and eqi.ElectronicQueueInfo_id = :ElectronicQueueInfo_id
					limit 1
				";
				$result = $this->queryResult($query, $params);
				if (!empty($result[0])) {
					$nextCab = "№{$result[0]["ElectronicService_Code"]} ({$result[0]["ElectronicService_Name"]})";
					$electronicService_id = $result[0]["ElectronicService_id"];
				}
			}
			if (!(!empty($result[0]) || $isLast)) {
				throw new Exception("Невозможно определить следующий пункт обслуживания");
			}
			// переводим в этот ПО и меняем статус, в зависимости от того
			// последний пункт или нет
			$funcParams = [
				"ElectronicTalon_id" => $data["ElectronicTalon_id"],
				"ElectronicService_id" => $electronicService_id,
				"ElectronicTalonStatus_id" => (($isLast) ? 4 : 1), // Изменяется текущий статус на Обслужен
				"pmUser_id" => $data["pmUser_id"],
				"ElectronicQueueInfo_id" => $talon[0]["ElectronicQueueInfo_id"],
				"isMultiserviceElectronicQueue" => true // признак того что очредь имеет много ПО
			];
			$this->ElectronicTalon_model->setElectronicTalonStatus($funcParams);
			$retData = ["Error_Msg" => ""];
			if (!empty($nextCab)) {
				$retData["nextCab"] = $nextCab;
			}
			return $retData;
		} else {
			// если схема нелинейная
			// подсчитаем сколько уникальных раз был обслужен талон
			$serviced_count = $this->getFirstResultFromQuery("
				with history (ElectronicService_id) as
				(
				   	select distinct
						eth.ElectronicService_id
					from
						v_ElectronicTalonHist eth
						left join v_ElectronicService es on es.ElectronicService_id =  eth.ElectronicService_id
						left join v_ElectronicQueueInfo eqi on eqi.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
					where eth.ElectronicTalon_id = :ElectronicTalon_id
					  and eth.ElectronicTalonStatus_id = 4
				)
				select count(ElectronicService_id) as \"serviced_count\"
				from history
			", ["ElectronicTalon_id" => $talon[0]["ElectronicTalon_id"]]);
			// посчитаем длину ЭО
			$services_length = $this->getFirstResultFromQuery("
				select count(es.ElectronicService_Num) as \"services_length\"
				from v_ElectronicService es
				where es.ElectronicQueueInfo_id = :ElectronicQueueInfo_id
			", $params);
			// если количество пройденных ПО равно количеству всех ПО на ЭО завершаем профосмотр
			$funcParams = ($serviced_count == $services_length)
				? [
					"ElectronicTalon_id" => $data["ElectronicTalon_id"],
					"ElectronicService_id" => $talon[0]["ElectronicService_id"],
					"ElectronicQueueInfo_id" => $talon[0]["ElectronicQueueInfo_id"],
					"ElectronicTalonStatus_id" => 4, // статус Обслужен
					"pmUser_id" => $data["pmUser_id"],
                    'alreadyInTalonHistory' => true, // признак того что уже есть запись в истории обслуживания
                    // очистим перенаправление талона
                    'clearRedirectLink' => !empty($talon[0]["EvnDirection_uid"])
				]
				: [
					"ElectronicTalon_id" => $data["ElectronicTalon_id"],
					"ElectronicTalonStatus_id" => 1, // статус Ожидает
					"ElectronicQueueInfo_id" => $talon[0]["ElectronicQueueInfo_id"],
                    "pmUser_id" => $data["pmUser_id"],
                    // очистим перенаправление талона
                    "clearRedirectLink" => !empty($talon[0]["EvnDirection_uid"])
				];
			$this->ElectronicTalon_model->setElectronicTalonStatus($funcParams);
			return ["Error_Msg" => ""];
		}
	}

	/**
	 * Получение группы по SurveyType
	 * @param $SurveyType_Code
	 * @return string|null
	 */
	function getGroupBySurveyType($SurveyType_Code)
	{
		$group = null;
		switch ($SurveyType_Code) {
			case "11": // Общий анализ мочи
				$group = "ProfUrine";
				break;
			case "17": // Электрокардиография (в покое)
				$group = "ProfElectro";
				break;
			case "18": // Электрокардиография (в покое)
				$group = "ProfNeur";
				break;
			case "27": // Прием (осмотр) врача - педиатра
				$group = "ProfPed";
				break;
			case "29": // Прием (осмотр) врача - детского хирурга
				$group = "ProfSurg";
				break;
			case "32": // Прием (осмотр) врача - травматолога-ортопеда
				$group = "ProfTrauma";
				break;
			case "102": // Осмотр (консультация) врачом-оториноларингологом
				$group = "ProfOto";
				break;
			case "127": // Общий анализ крови
				$group = "ProfBlood";
				break;
		}
		return $group;
	}

	/**
	 * Завершение приёма
	 * @param $data
	 * @return |null
	 */
	function nextElectronicServiceRecursion($data)
	{
		$result = null;
		$nextNum = $data["nextNum"];
		$numCount = $this->electronicServiceNumCount($data);
		// если есть какое-то количество ПО с таким порядковым
		if (!empty($numCount)) {
			// если он один
			if ($numCount == 1) {
				// получим идентификатор ПО
				$result = $this->getElectronicServiceId($data);
			} else {
				// если их много, получим идентификатор наименее загруженного ПО
				$result = $this->getElectronicServiceId($data, true);
			}
		} else { // если нет ПО с таким порядковым
			$nextNum++; // увеличиваем нумератор
			if ($nextNum < 6) { // чтобы не зацикливать, максимальный порядковый номер
				$data["nextNum"] = $nextNum;
				$result = $this->nextElectronicServiceRecursion($data); // повторяем рекурсию
			}
		}
		return $result;
	}

	/**
	 * Завершение приёма
	 * @param $data
	 * @return mixed|null
	 */
	function electronicServiceNumCount($data)
	{
		$params = [
			"ElectronicService_id" => $data["ElectronicService_id"], // тот с которого пришли
			"ElectronicService_Num" => $data["ElectronicService_Num"] // искомый порядковый
		];
		$query = "
			select count(es.ElectronicService_id) as \"cnt\"
			from v_ElectronicService es
			where es.ElectronicQueueInfo_id = (
				select eswh.ElectronicQueueInfo_id
				from v_ElectronicService eswh
				where eswh.ElectronicService_id = :ElectronicService_id
				limit 1
			  )
			  and es.ElectronicService_Num = :ElectronicService_Num
		";
		$resp = $this->queryResult($query, $params);
		return (!empty($resp[0]["cnt"])) ? $resp[0]["cnt"] : null;
	}

	/**
	 * динамический метод метод не нужен
	 * забили большой Х
	 */
	function getElectronicServiceId($data, $mostlyFree = false)
	{
		$params = [
			"ElectronicService_id" => $data["ElectronicService_id"], // тот с которого пришли
			"ElectronicService_Num" => $data["ElectronicService_Num"] // искомый порядковый
		];
		$query = "
			select es.ElectronicService_id as \"ElectronicService_id\"
			from v_ElectronicService es
			where es.ElectronicQueueInfo_id = (
				select eswh.ElectronicQueueInfo_id
				from v_ElectronicService eswh
				where eswh.ElectronicService_id = :ElectronicService_id
				limit 1
			  )
			  and es.ElectronicService_Num = :ElectronicService_Num
			limit 1
		";
		// наименее загруженный
		if ($mostlyFree) {
			$query = "
				select b.ElectronicService_id as \"ElectronicService_id\"
				from (
					select
						count(et1.ElectronicTalon_id) as cnt,
						et1.ElectronicService_id
					from v_ElectronicTalon et1
					where et1.ElectronicService_id in (
							select es2.ElectronicService_id
							from v_ElectronicService es2
							where es2.ElectronicQueueInfo_id = (
									select eswh.ElectronicQueueInfo_id
									from v_ElectronicService eswh
									where eswh.ElectronicService_id = :ElectronicService_id
									limit 1
							  )
							  and es2.ElectronicService_Num = :ElectronicService_Num
					  )
					  and to_char(et1.ElectronicTalon_insDT, '{$this->dateTimeForm104}') = to_char(dbo.tzGetDate(), '{$this->dateTimeForm104}')
					  and et1.ElectronicTalonStatus_id < 4
					group BY ElectronicService_id
				) as b
				where b.cnt = (
					select min(a.cnt) from (
						select
							count(et2.ElectronicTalon_id) as cnt,
							et2.ElectronicService_id
						from v_ElectronicTalon et2
						where et2.ElectronicService_id in (
							select es1.ElectronicService_id
							from v_ElectronicService es1
							where es1.ElectronicQueueInfo_id = (
									select eswh.ElectronicQueueInfo_id
									from v_ElectronicService eswh
									where eswh.ElectronicService_id = :ElectronicService_id
									limit 1
							  )
							  and es1.ElectronicService_Num = :ElectronicService_Num
						  )
						  and to_char(et2.ElectronicTalon_insDT, '{$this->dateTimeForm104}') = to_char(dbo.tzGetDate(), '{$this->dateTimeForm104}')
						  and et2.ElectronicTalonStatus_id < 4
						  group by ElectronicService_id
						) as a
					)
				limit 1
			";
		}
		$resp = $this->queryResult($query, $params);
		return (!empty($resp[0]["ElectronicService_id"]) ? $resp[0]["ElectronicService_id"] : null);
	}
}