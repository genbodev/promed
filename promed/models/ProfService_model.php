<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      ProfService
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 */

class ProfService_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Загрузка области данных АРМ
	 */
	function loadWorkPlaceGrid($data) {

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
			$join .= " left join v_ElectronicService es (nolock) on es.ElectronicService_id = et.ElectronicService_id ";
		}

		$query = "
			select
				ttms.TimetableMedService_id,
				et.ElectronicTalon_Num,
				ps.Person_SurName as Person_Surname,
				ps.Person_FirName as Person_Firname,
				ps.Person_SecName as Person_Secname,
				ISNULL(convert(varchar(5), ttms.TimetableMedService_begTime, 108), 'б/з') as TimetableMedService_begTime,
				rtrim(isnull(ps.Person_SurName,'')) + rtrim(isnull(' ' + ps.Person_FirName,'')) + rtrim(isnull(' ' + ps.Person_SecName, '')) as Person_Fio,
				convert(varchar(10), ps.Person_BirthDay, 104) as Person_BirthDay,
				
				--формируем псевдо-статус если ПО не такой как у нас
				case when (eth.ElectronicTalon_IsProcessed is not null and et.ElectronicTalonStatus_id != 5)
					then 'Обслужен'
					else
						case when ISNULL(et.ElectronicService_id, :ElectronicService_id) != :ElectronicService_id
						then esoa.ElectronicService_Name
						else ets.ElectronicTalonStatus_Name
						end
				end as ElectronicTalonStatus_Name,
				--получаем признак что талон уже обслуживается
				case when ISNULL(et.ElectronicService_id, :ElectronicService_id) != :ElectronicService_id
						then 1
						else null
				end as ElectronicTalon_IsBusy,
				--получаем признак того что ПО уже обработал талон
				eth.ElectronicTalon_IsProcessed,
				--null as ElectronicTalon_IsProcessed,eth.ElectronicTalonHist_processedInsDT,
				eth2.ElectronicTalonHist_insDT,
				case when (eth.ElectronicTalon_IsProcessed is null and et.ElectronicTalonStatus_id = 1)
					then DATEDIFF(mi, eth2.ElectronicTalonHist_insDT, getdate())
					else ''
				end as ElectronicTalon_IdleTime,
				ps.Person_id,
				ps.PersonEvn_id,
				ps.Server_id,
				et.ElectronicService_id,
				et.ElectronicTalonStatus_id,
				epdd.EvnPLDispTeenInspection_id,
				case when ttms.TimetableMedService_Day = :curTimetableMedService_Day then 1 else 0 end as IsCurrentDate,
				et.ElectronicTalon_id,
				ps.Person_IsUnknown,
				rmt.RecMethodType_Name,
				ttms.EvnDirection_id
			from v_TimetableMedService_lite ttms (nolock)
			left join v_ElectronicTalon et (nolock) on et.EvnDirection_id = ttms.EvnDirection_id
			left join v_PersonState ps (nolock) on ps.Person_id = ttms.Person_id
			-- поменяли left на inner #139518
			inner join v_EvnPLDispTeenInspection epdd (nolock) on epdd.EvnDirection_id = ttms.EvnDirection_id
			left join v_ElectronicTalonStatus ets (nolock) on ets.ElectronicTalonStatus_id = et.ElectronicTalonStatus_id
			left join v_RecMethodType rmt (nolock) on rmt.RecMethodType_id = ttms.RecMethodType_id
			left join v_ElectronicQueueInfo eq (nolock) on eq.ElectronicQueueInfo_id = et.ElectronicQueueInfo_id
			{$join}
						
			--получаем название пункта в статусе
			outer apply (
				select top 1
					ISNULL(esoa.ElectronicService_Name, 'На осмотре') as ElectronicService_Name
				from v_ElectronicService esoa (nolock)
				where esoa.ElectronicService_id = et.ElectronicService_id
			) as esoa

			--получаем признак того что ПО уже обработал талон
			outer apply (
				select top 1
					case when eth.ElectronicTalonHist_id is null
						then 0 
						else 1 
						end as ElectronicTalon_IsProcessed,
					eth.ElectronicTalonHist_insDT as ElectronicTalonHist_processedInsDT
				from v_ElectronicTalonHist eth (nolock)
				where
					eth.ElectronicService_id = :ElectronicService_id
					and (eth.ElectronicTalonStatus_id = 4 or eth.ElectronicTalonStatus_id = 5)
					and eth.ElectronicTalon_id = et.ElectronicTalon_id
			) as eth

			--получаем дату последнего обслуживания
			outer apply (
				select top 1
					max(eth2.ElectronicTalonHist_insDT) as ElectronicTalonHist_insDT
				from v_ElectronicTalonHist eth2 (nolock)
				where eth2.ElectronicTalon_id = et.ElectronicTalon_id
			) as eth2
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
	 */
	function setNoPatientTalonStatus($data) {
		$resp_check = $this->queryResult("
			select
				count(*) as cnt
			from
				v_ElectronicTalonHist
			where
				ElectronicTalon_id = :ElectronicTalon_id
				and ElectronicTalonStatus_id = 2
		", array(
			'ElectronicTalon_id' => $data['ElectronicTalon_id']
		));

		if (!empty($resp_check[0]['cnt']) && $resp_check[0]['cnt'] >= 2) {
			// Если в истории статусов для текущего пункта обслуживания есть 2 записи со статусом «Вызван», то "Отменён"
			// получаем данные по бирке (не отменять если со временем и текущее дата время меньше, чем [дата время бирки + время опоздания при регистрации в очереди (мин.)])
			$resp_noc = $this->queryResult("
				select
					case
						when es.ElectronicService_Num = 1 and ttms.TimetableMedService_begTime is not null and DATEADD(MINUTE, eqi.ElectronicQueueInfo_LateTimeMin, ttms.TimetableMedService_begTime) > dbo.tzGetDate() then 1
						else 0
					end as noCancel
				from
					v_ElectronicTalon et (nolock)
					left join v_ElectronicQueueInfo eqi (nolock) on eqi.ElectronicQueueInfo_id = et.ElectronicQueueInfo_id
					left join v_TimetableMedService_lite ttms (nolock) on ttms.EvnDirection_id = et.EvnDirection_id
					left join v_ElectronicService es (nolock) on es.ElectronicService_id = et.ElectronicService_id
				where
					et.ElectronicTalon_id = :ElectronicTalon_id
			", array(
				'ElectronicTalon_id' => $data['ElectronicTalon_id']
			));

			if (!empty($resp_noc[0]['noCancel'])) {
				$data['ElectronicTalonStatus_id'] = 1; // ожидает
			} else {
				$data['ElectronicTalonStatus_id'] = 5; // отменён
			}
		} else {
			// Иначе "Ожидает"
			$data['ElectronicTalonStatus_id'] = 1;
		}

		$this->load->model('ElectronicTalon_model');
		return $this->ElectronicTalon_model->setElectronicTalonStatus($data);
	}

	/**
	 * Проверка активности электронной очереди
	 */
	function checkElectronicQueueInfoEnabled($data) {
		$resp = $this->queryResult("
			select
				eqi.ElectronicQueueInfo_id,
				ISNULL(eqi.ElectronicQueueInfo_IsOff, 1) as ElectronicQueueInfo_IsOff
			from
				v_ElectronicQueueInfo eqi (nolock)
				inner join v_ElectronicService es (nolock) on es.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id
			where
				es.ElectronicService_id = :ElectronicService_id
		", array(
			'ElectronicService_id' => $data['ElectronicService_id']
		));

		if (!empty($resp[0]['ElectronicQueueInfo_id'])) {
			return array('Error_Msg' => '', 'ElectronicQueueInfo_id' => $resp[0]['ElectronicQueueInfo_id'], 'ElectronicQueueInfo_IsOff' => $resp[0]['ElectronicQueueInfo_IsOff']);
		}

		return array('Error_Msg' => 'Ошибка определения активности электронной очереди');
	}

	/**
	 * Замена неизвестного человека на известного
	 */
	function fixPersonUnknown($data) {
		// убеждаемся, что выбран именно неизвестный человек
		$resp = $this->queryResult("
			select top 1
				pso.Person_id as Person_oldId,
				psn.Person_id as Person_newId,
				psn.Server_id as Server_newId,
				psn.PersonEvn_id as PersonEvn_newId
			from
				v_PersonState pso (nolock)
				left join v_PersonState psn (nolock) on psn.Person_id = :Person_newId
			where
				pso.Person_id = :Person_oldId
				and pso.Person_IsUnknown = 2",
		array(
			'Person_oldId' => $data['Person_oldId'],
			'Person_newId' => $data['Person_newId']
		));

		if (empty($resp[0]['Person_oldId'])) {
			return array('Error_Msg' => 'Ошибка получения данных по неизвестному человеку');
		}

		if (empty($resp[0]['Person_newId'])) {
			return array('Error_Msg' => 'Ошибка получения данных по человеку');
		}

		$this->beginTransaction();
		// Бирка связывается с идентификатором выбранного человека.
		// Направление связывается с идентификатором выбранного человека.
		// Талон ЭО связывается с идентификатором выбранного человека.
		$resp_upd = $this->queryResult("
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);

			set nocount on;

			begin try
				update TimetableGraf with (rowlock) set Person_id = :Person_newId, pmUser_updID = :pmUser_id, TimetableGraf_updDT = dbo.tzGetDate() where Person_id = :Person_oldId; -- Бирка
				update TimetableMedService with (rowlock) set Person_id = :Person_newId, pmUser_updID = :pmUser_id, TimeTableMedService_updDT = dbo.tzGetDate() where Person_id = :Person_oldId; -- Бирка
				update ElectronicTalon with (rowlock) set Person_id = :Person_newId, pmUser_updID = :pmUser_id, ElectronicTalon_updDT = dbo.tzGetDate() where Person_id = :Person_oldId; -- Талон
				update Evn with (rowlock) set Person_id = :Person_newId, Server_id = :Server_newId, PersonEvn_id = :PersonEvn_newId, pmUser_updID = :pmUser_id, Evn_updDT = dbo.tzGetDate() where Person_id = :Person_oldId and EvnClass_id = 27; -- EvnDirection
			end try

			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
			end catch

			set nocount off;

			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		", array(
			'Server_newId' => $resp[0]['Server_newId'],
			'PersonEvn_newId' => $resp[0]['PersonEvn_newId'],
			'Person_newId' => $resp[0]['Person_newId'],
			'Person_oldId' => $resp[0]['Person_oldId'],
			'pmUser_id' => $data['pmUser_id']
		));
		if (!empty($resp_upd[0]['Error_Msg'])) {
			$this->rollbackTransaction();
			return $resp_upd[0];
		}

		// Человек с признаком «Неизвестный», ранее связанный с биркой, направлением, талоном ЭО, удаляется из БД.
		// Если у него нет других учетных документов
		$this->load->model('Person_model');
		$toDel = $this->Person_model->checkToDelPerson(array(
			'Person_id' => $resp[0]['Person_oldId']
		));
		if(empty($toDel['Person_id'])) {
			$resp_del = $this->Person_model->deletePerson(array(
				'Person_id' => $resp[0]['Person_oldId'],
				'pmUser_id' => $data['pmUser_id']
			));
			if (!empty($resp_del[0]['Error_Msg'])) {
				$this->rollbackTransaction();
				return $resp_del[0];
			}
		}

		$this->commitTransaction();
		return array('Error_Msg' => '');
	}

	/**
	 * Завершение приёма
	 */
	function finishCall($data) {

		$query = "
			select
				et.ElectronicTalon_id,
				et.EvnDirection_uid,
				epdd.EvnPLDispTeenInspection_id,
				isnull(esRemote.ElectronicService_Num, es.ElectronicService_Num) as ElectronicService_Num,
				isnull(esRemote.ElectronicQueueInfo_id,es.ElectronicQueueInfo_id) as ElectronicQueueInfo_id,
				isnull(esRemote.ElectronicService_id, es.ElectronicService_id) as ElectronicService_id,
				evdd.EvnVizitDispDop_id,
				evdd.SurveyType_Code,
				esRemote.ElectronicService_id as isRemoteService
			from
				v_ElectronicTalon et (nolock)
				inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = et.EvnDirection_id
				left join v_EvnPLDispTeenInspection epdd (nolock) on epdd.EvnDirection_id = et.EvnDirection_id
				left join v_ElectronicService es (nolock) on es.ElectronicService_id = et.ElectronicService_id
				left join v_ElectronicService esRemote (nolock) on esRemote.ElectronicService_tid = es.ElectronicService_id
				-- смотрим сохранен ли осмотр для текущего ПО
				outer apply (
					select
						evdd.EvnVizitDispDop_id,
						st.SurveyType_Code
					from v_DopDispInfoConsent ddic (nolock)
					inner join v_EvnVizitDispDop evdd (nolock) on evdd.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
					inner join v_SurveyTypeLink stl (nolock) on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
					inner join v_SurveyType st (nolock) on st.SurveyType_id = stl.SurveyType_id
					where
						ddic.EvnPLDisp_id = epdd.EvnPLDispTeenInspection_id
						and evdd.ElectronicService_id = es.ElectronicService_id
				) evdd
			where et.ElectronicTalon_id = :ElectronicTalon_id
		";

		//echo '<pre>',print_r(getDebugSQL($query, array('ElectronicTalon_id' => $data['ElectronicTalon_id']))),'</pre>'; die();
		$talon = $this->queryResult($query, array('ElectronicTalon_id' => $data['ElectronicTalon_id']));

		if (empty($talon[0]['ElectronicTalon_id'])) {
			return array('Error_Msg' => 'Ошибка получении информации по талону');
		}

		//echo '<pre>',print_r($talon),'</pre>'; die();

		$params = array('ElectronicQueueInfo_id' => $talon[0]['ElectronicQueueInfo_id']);
		$dd_found = false;

		// todo: потом от этого говнокода надо бы избавиться
		if (havingGroup('ProfPed')) {
			foreach($talon as $t) {
				// еcли завершен осмотр педиатра тогда пускам дальше
				if (!empty($t['EvnVizitDispDop_id'])
					&& !empty($t['SurveyType_Code']) && $t['SurveyType_Code'] == 27)
				{
					$dd_found = true;
					break;
				}
			}
		} else if (!empty($talon[0]['EvnVizitDispDop_id'])) $dd_found = true;

		if (!$dd_found && empty($talon[0]['isRemoteService'])) {
			return array('Error_Msg' => 'Необходимо сохранить результат осмотра в маршрутной карте');
		}

		$this->load->model('ElectronicTalon_model');

		// завершаем наш талон в этом ПО в истории талона
		$this->ElectronicTalon_model->updateElectronicTalonHistory(array(
			'ElectronicTalon_id' => $talon[0]['ElectronicTalon_id'],
			'ElectronicService_id' => $talon[0]['ElectronicService_id'],
			'ElectronicTalonStatus_id' => 4,
			'pmUser_id' => $data['pmUser_id']
		));

		$this->load->model('ElectronicQueue_model');

		$is_linear_eq = $this->ElectronicQueue_model->getElectronicQueueType(
			array('ElectronicQueueInfo_id' => $talon[0]['ElectronicQueueInfo_id'])
		);

		//echo '<pre>',print_r($is_linear_eq, true),'</pre>'; die();

		//если схема линейная идем по порядку
		if ($is_linear_eq) {

			$current = $talon[0]['ElectronicService_Num'];
			$next = intval($current)+1;

			// определяем макс пор. номер ПО в ЭО
			$services_length = $this->getFirstResultFromQuery("
				select max(es.ElectronicService_Num) as services_length
				from v_ElectronicService es (nolock)
				where es.ElectronicQueueInfo_id = :ElectronicQueueInfo_id
			", $params);

			$isLast = ($current == $services_length);

			$params['ElectronicService_Num'] = $next;
			$electronicService_id = null;

			if  ($next <= $services_length) {
				// определяем id следующего ПО

				$query = "
					select top 1
						isnull(esRemote.ElectronicService_Code, es.ElectronicService_Code) as ElectronicService_Code,
						isnull(esRemote.ElectronicService_Name, es.ElectronicService_Name) as ElectronicService_Name,
						isnull(esRemote.ElectronicService_id, es.ElectronicService_id) as ElectronicService_id
					from
						v_ElectronicService es (nolock)
						inner join v_ElectronicQueueInfo eqi (nolock) on eqi.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
						left join v_ElectronicService esRemote (nolock) on esRemote.ElectronicService_id = es.ElectronicService_tid
					where
						es.ElectronicService_Num = :ElectronicService_Num
						and eqi.ElectronicQueueInfo_id = :ElectronicQueueInfo_id
				";

				$result = $this->queryResult($query,$params);
				if (!empty($result[0])) {
					$nextCab = '№'
						. $result[0]['ElectronicService_Code']
						. ' ('
						. $result[0]['ElectronicService_Name']
						. ')';

					$electronicService_id = $result[0]['ElectronicService_id'];
				}
			}

			//echo '<pre>',print_r($result[0]),'</pre>'; die();

			if (!empty($result[0]) || $isLast) {

				// переводим в этот ПО и меняем статус, в зависимости от того
				// последний пункт или нет

				$this->ElectronicTalon_model->setElectronicTalonStatus(array(
					'ElectronicTalon_id' => $data['ElectronicTalon_id'],
					'ElectronicService_id' => $electronicService_id,
					'ElectronicTalonStatus_id' => (($isLast) ? 4 : 1), // Изменяется текущий статус на Обслужен
					'pmUser_id' => $data['pmUser_id'],
					'ElectronicQueueInfo_id' => $talon[0]['ElectronicQueueInfo_id'],
					'isMultiserviceElectronicQueue' => true // признак того что очредь имеет много ПО
				));

				$retData = array('Error_Msg' => '');
				if (!empty($nextCab)) $retData['nextCab'] = $nextCab;

				return $retData;
			} else return array('Error_Msg' => 'Невозможно определить следующий пункт обслуживания');

		} else {
			//если схема нелинейная

			// подсчитаем сколько уникальных раз был обслужен талон
			$serviced_count = $this->getFirstResultFromQuery("
				WITH history (ElectronicService_id)
				AS
				(
				   	SELECT DISTINCT
						eth.ElectronicService_id
					FROM v_ElectronicTalonHist eth (nolock)
					left join v_ElectronicService es (nolock) on es.ElectronicService_id =  eth.ElectronicService_id
					left join v_ElectronicQueueInfo eqi (nolock) on eqi.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
					where
						eth.ElectronicTalon_id = :ElectronicTalon_id
						and eth.ElectronicTalonStatus_id = 4
				)
				SELECT count(ElectronicService_id) as serviced_count
				FROM history
			", array('ElectronicTalon_id' => $talon[0]['ElectronicTalon_id']));

			// посчитаем длину ЭО
			$services_length = $this->getFirstResultFromQuery("
				select count(es.ElectronicService_Num) as services_length
				from v_ElectronicService es (nolock)
				where es.ElectronicQueueInfo_id = :ElectronicQueueInfo_id
			", $params);

			// если количество пройденных ПО равно количеству всех ПО на ЭО завершаем профосмотр
			if ($serviced_count == $services_length) {
				$this->ElectronicTalon_model->setElectronicTalonStatus(array(
					'ElectronicTalon_id' => $data['ElectronicTalon_id'],
					'ElectronicService_id' => $talon[0]['ElectronicService_id'],
					'ElectronicQueueInfo_id' => $talon[0]['ElectronicQueueInfo_id'],
					'ElectronicTalonStatus_id' => 4, // статус Обслужен
					'pmUser_id' => $data['pmUser_id'],
					'alreadyInTalonHistory' => true, // признак того что уже есть запись в истории обслуживания
					// очистим перенаправление талона
					'clearRedirectLink' => !empty($talon[0]['EvnDirection_uid'])
				));
			} else {
				// иначе сбрасываем текущий ПО - талон в общей куче
				$this->ElectronicTalon_model->setElectronicTalonStatus(array(
					'ElectronicTalon_id' => $data['ElectronicTalon_id'],
					'ElectronicTalonStatus_id' => 1, // статус Ожидает
					'ElectronicQueueInfo_id' => $talon[0]['ElectronicQueueInfo_id'],
					'pmUser_id' => $data['pmUser_id'],
					// очистим перенаправление талона
					'clearRedirectLink' => !empty($talon[0]['EvnDirection_uid'])
				));
			}

			$retData = array('Error_Msg' => '');
			return $retData;
		}
	}

	/**
	 * Получение группы по SurveyType
	 */
	function getGroupBySurveyType($SurveyType_Code) {
		$group = null;
		switch($SurveyType_Code) {
			case '11': // Общий анализ мочи
				$group = 'ProfUrine';
				break;
			case '17': // Электрокардиография (в покое)
				$group = 'ProfElectro';
				break;
			case '18': // Электрокардиография (в покое)
				$group = 'ProfNeur';
				break;
			case '27': // Прием (осмотр) врача - педиатра
				$group = 'ProfPed';
				break;
			case '29': // Прием (осмотр) врача - детского хирурга
				$group = 'ProfSurg';
				break;
			case '32': // Прием (осмотр) врача - травматолога-ортопеда
				$group = 'ProfTrauma';
				break;
			case '102': // Осмотр (консультация) врачом-оториноларингологом
				$group = 'ProfOto';
				break;
			case '127': // Общий анализ крови
				$group = 'ProfBlood';
				break;
		}
		return $group;
	}

	/**
	 * Завершение приёма
	 */
	function nextElectronicServiceRecursion($data) {

		$result = NULL;

		$nextNum = $data['nextNum'];
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
				$data['nextNum'] = $nextNum;
				$result = $this->nextElectronicServiceRecursion($data); // повторяем рекурсию
			}
		}

		return $result;
	}

	/**
	 * Завершение приёма
	 */
	function electronicServiceNumCount($data) {
		$params = array(
			'ElectronicService_id' => $data['ElectronicService_id'], // тот с которого пришли
			'ElectronicService_Num' => $data['ElectronicService_Num'] // искомый порядковый
		);

		$query = "
			select
				count(es.ElectronicService_id) as cnt
			from v_ElectronicService es (nolock)
			where
				es.ElectronicQueueInfo_id = (
					select top 1
						eswh.ElectronicQueueInfo_id
					from v_ElectronicService eswh (nolock)
					where eswh.ElectronicService_id = :ElectronicService_id
				)
				and es.ElectronicService_Num = :ElectronicService_Num
		";

		$resp = $this->queryResult($query, $params);
		return (!empty($resp[0]['cnt']) ? $resp[0]['cnt'] : NULL);
	}

	/**
	 * динамический метод метод не нужен
	 * забили большой Х
	 */
	function getElectronicServiceId($data, $mostlyFree = false) {

		$params = array(
			'ElectronicService_id' => $data['ElectronicService_id'], // тот с которого пришли
			'ElectronicService_Num' => $data['ElectronicService_Num'] // искомый порядковый
		);

		$query = "
			select top 1
				es.ElectronicService_id
			from v_ElectronicService es (nolock)
			where
				es.ElectronicQueueInfo_id = (
					select top 1
						eswh.ElectronicQueueInfo_id
					from v_ElectronicService eswh (nolock)
					where eswh.ElectronicService_id = :ElectronicService_id
				)
				and es.ElectronicService_Num = :ElectronicService_Num
		";

		// наименее загруженный
		if ($mostlyFree) {

			$query = "
				select top 1
					b.ElectronicService_id
				from (
					select
						count(et1.ElectronicTalon_id) as cnt,
						et1.ElectronicService_id
					from v_ElectronicTalon et1
					where et1.ElectronicService_id in (
							select
								es2.ElectronicService_id
							from v_ElectronicService es2 (nolock)
							where
								es2.ElectronicQueueInfo_id = (
									select top 1
										eswh.ElectronicQueueInfo_id
									from v_ElectronicService eswh (nolock)
									where eswh.ElectronicService_id = :ElectronicService_id
								)
								and es2.ElectronicService_Num = :ElectronicService_Num
					)
					and CONVERT(varchar,et1.ElectronicTalon_insDT, 104) = CONVERT(varchar,dbo.tzGetDate(), 104)
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
							select
								es1.ElectronicService_id
							from v_ElectronicService es1 (nolock)
							where
								es1.ElectronicQueueInfo_id = (
									select top 1
										eswh.ElectronicQueueInfo_id
									from v_ElectronicService eswh (nolock)
									where eswh.ElectronicService_id = :ElectronicService_id
								)
								and es1.ElectronicService_Num = :ElectronicService_Num
						)
						and CONVERT(varchar,et2.ElectronicTalon_insDT, 104) = CONVERT(varchar,dbo.tzGetDate(), 104)
						and et2.ElectronicTalonStatus_id < 4
						group by ElectronicService_id
						) as a
					)
			";
		}

		$resp = $this->queryResult($query, $params);
		return (!empty($resp[0]['ElectronicService_id']) ? $resp[0]['ElectronicService_id'] : NULL);
	}

	/**
	 * Метод возвращает справочник форм целей профосмотров
	 */
	function getProfGoalFormList() {
		$query = "
		SELECT
			ProfGoal_id,
		    ProfGoal_Code,
			ProfGoal_Name
		FROM v_ProfGoal with (nolock)
		ORDER BY ProfGoal_id ASC
		";
		$result = $this->db->query($query);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
}