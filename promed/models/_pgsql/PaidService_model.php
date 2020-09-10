<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      PaidService
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 */

class PaidService_model extends swPgModel {
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
			'curTimetableMedService_Day' => TimeToDay(time())
		);

		$orderby = "";
		if (!empty($data['session']['CurARM']['ElectronicService_id'])) {
			$orderby .= "case when et.ElectronicService_id = :ElectronicService_id then 0 else 1 end,";
			$queryParams['ElectronicService_id'] = $data['session']['CurARM']['ElectronicService_id'];
		}

		return $this->queryResult("			
			select
				ttms.TimetableMedService_id as \"TimetableMedService_id\",
				et.ElectronicTalon_Num as \"ElectronicTalon_Num\",
				coalesce(to_char( ttms.TimetableMedService_begTime, 'dd.mm.yyyy'), 'б/з') as \"TimetableMedService_begTime\",
				rtrim(coalesce(ps.Person_SurName,'')) || rtrim(coalesce(' ' || ps.Person_FirName,'')) || rtrim(coalesce(' ' || ps.Person_SecName, '')) as \"Person_Fio\",
				to_char(ps.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				epdd.EvnPLDispDriver_Num as \"EvnPLDispDriver_Num\",
				ets.ElectronicTalonStatus_Name as \"ElectronicTalonStatus_Name\",
				coalesce(cast(es.ElectronicService_Code as varchar) || ' ', '') || es.ElectronicService_Name as \"ElectronicService_Name\",
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
			where
				ttms.UslugaComplexMedService_id = :UslugaComplexMedService_id
				and ttms.TimetableMedService_Day = :TimetableMedService_Day
			order by
				{$orderby} 
				case when et.ElectronicTalon_Num is not null then 0 else 1 end,
				case when et.ElectronicTalonStatus_id = 4 then 1 else 0 end,
				case when ttms.Person_id is not null then 0 else 1 end,
				TimetableMedService_begTime
		", $queryParams);
	}

	/**
	 * Установка статуса электронного талона при неявке пациента
	 */
	function setNoPatientTalonStatus($data) {
		$resp_check = $this->queryResult("
			select
				count(*) as \"cnt\"
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
					case when es.ElectronicService_Num = 1
							and ttms.TimetableMedService_begTime is not null
							and (ttms.TimetableMedService_begTime +  cast('00:' || eqi.ElectronicQueueInfo_LateTimeMin|| ':00' as time) > dbo.tzGetDate())
						then 1
						else 0
					end as \"noCancel\"
				from
					v_ElectronicTalon et
					left join v_ElectronicQueueInfo eqi on eqi.ElectronicQueueInfo_id = et.ElectronicQueueInfo_id
					left join v_TimetableMedService_lite ttms on ttms.EvnDirection_id = et.EvnDirection_id
					left join v_ElectronicService es on es.ElectronicService_id = et.ElectronicService_id
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
				eqi.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\",
				coalesce(eqi.ElectronicQueueInfo_IsOff, 1) as \"ElectronicQueueInfo_IsOff\"
			from
				v_ElectronicQueueInfo eqi
				inner join v_ElectronicService es on es.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id
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
			select
				pso.Person_id as \"Person_oldId\",
				psn.Person_id as \"Person_newId\",
				psn.Server_id as \"Server_newId\",
				psn.PersonEvn_id as \"PersonEvn_newId\"
			from
				v_PersonState pso
				left join v_PersonState psn on psn.Person_id = :Person_newId
			where
				pso.Person_id = :Person_oldId
				and pso.Person_IsUnknown = 2
			limit 1",
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
		try {
			$params = [
				'Server_newId' => $resp[0]['Server_newId'],
				'PersonEvn_newId' => $resp[0]['PersonEvn_newId'],
				'Person_newId' => $resp[0]['Person_newId'],
				'Person_oldId' => $resp[0]['Person_oldId'],
				'pmUser_id' => $data['pmUser_id']
			];
			$resp_upd = $this->queryResult("
				update TimetableGraf
				set Person_id = :Person_newId,
					pmUser_updID = :pmUser_id,
					TimetableGraf_updDT = dbo.tzGetDate()
				where Person_id = :Person_oldId -- Бирка
			", $params);
			$resp_upd = $this->queryResult("
				update TimetableMedService
				set Person_id = :Person_newId,
					pmUser_updID = :pmUser_id,
					TimeTableMedService_updDT = dbo.tzGetDate()
				where Person_id = :Person_oldId
			", $params);
			$resp_upd = $this->queryResult("
				update ElectronicTalon
				set Person_id = :Person_newId,
					pmUser_updID = :pmUser_id,
					ElectronicTalon_updDT = dbo.tzGetDate()
				where Person_id = :Person_oldId -- Талон
			", $params);
			$resp_upd = $this->queryResult("
				update Evn
				set Person_id = :Person_newId,
					Server_id = :Server_newId,
					PersonEvn_id = :PersonEvn_newId,
					pmUser_updID = :pmUser_id,
					Evn_updDT = dbo.tzGetDate()
				where Person_id = :Person_oldId and EvnClass_id = 27 -- EvnDirection
			", $params);
		} catch(\Exception $e) {
			$this->rollbackTransaction();
			return $resp_upd;
		}

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
	 * Проверка на занятость текущего сервиса
	 */
	function checkIsDigitalServiceBusy($data) {

		$params['ElectronicService_id'] = $data['ElectronicService_id'];

		//$currDate = DateTime::createFromFormat('Y-m-d', $data['ElectronicServiceCallDate']);
		//$params['ElectronicServiceCallDate'] = $currDate->format('d.m.Y');

		$query = "

			select
				et.ElectronicTalon_id as \"ElectronicTalon_id\",
				et.ElectronicTalon_Num as \"ElectronicTalon_Num\",
				COALESCE(to_char(ttms.TimetableMedService_begTime, 'dd.mm.yyyy'), to_char(day.day_date, 'dd.mm.yyyy')) as \"day\",
				to_char(ttms.TimetableMedService_begTime, 'dd.mm.yyyy') as \"time\"
			from
				v_ElectronicTalon et
				left join v_EvnDirection_all ed on ed.EvnDirection_id = et.EvnDirection_id
				left join v_TimeTableMedService_lite ttms on ttms.TimeTableMedService_id = ed.TimeTableMedService_id
				left join v_Day day on day.day_id = (ttms.TimeTableMedService_Day - 1)
				left join v_EvnPLDispDriver epdd on epdd.EvnDirection_id = et.EvnDirection_id
			where
				(1=1)
				and et.ElectronicTalonStatus_id = 3 -- на обслуживании
				and et.ElectronicService_id = :ElectronicService_id
			order by
				ttms.TimetableMedService_begTime desc
			limit 1
		";

		$resp = $this->queryResult($query, $params);

		if (empty($resp[0]['ElectronicTalon_id'])) {

			return array(
				'Error_Msg' => '',
			);

		} elseif (!empty($resp[0]['ElectronicTalon_id'])) {

			return array(
				'Error_Msg' => '',
				'data' => array(
					'ElectronicTalon_id' => $resp[0]['ElectronicTalon_id']
				),
				'Check_Msg' =>
					'Перед '. ((!empty($data['ServiceAction'])&&$data['ElectronicServiceAction'] == 'call') ? 'вызовом' : 'приемом') .' нового пациента нужно завершить обслуживание или отменить услугу для пациента с талоном №'
					.$resp[0]['ElectronicTalon_Num']
					. ' от '
					. $resp[0]['day']
					. (!empty($resp[0]['time']) ? ' ('.$resp[0]['time'].')' : ''),
			);

		} else {
			return array('Error_Msg' => 'Ошибка проверки текущего сервиса на возможность вызова');
		}
	}

	/**
	 * Завершение приёма
	 */
	function finishCall($data) {

		//TODO: REMOVE!
		//set_time_limit(200);

		// ЕСЛИ осмотр текущим специалистом НЕ ЗАКРЫТ
		// ИЛИ в рамках осмотра пациенту НЕ СДЕЛАНЫ НАЗНАЧЕНИЯ

		// СООБЩЕНИЕ «Для продолжения создайте назначения на обследования,
		// либо заполните поле "Результат" в осмотре»

		$params['ElectronicTalon_id'] = $data['ElectronicTalon_id'];

		$query = "
			select
				et.ElectronicTalon_id as \"ElectronicTalon_id\",
				epdd.EvnPLDispDriver_id as \"EvnPLDispDriver_id\",
				epdd.ResultDispDriver_id as \"ResultDispDriver_id\",
				et.ElectronicService_id as \"ElectronicService_id\",
				es.ElectronicService_Num as \"ElectronicService_Num\",
				ed.MedService_id as \"MedService_id\",
				ep.EvnPrescr_id as \"EvnPrescr_id\",
				eda.EvnDirection_id as \"EvnDirection_id\",
				eu.Rate_ValuesIs as \"Rate_ValuesIs\"
			from
				v_ElectronicTalon et
				inner join v_EvnDirection_all ed on ed.EvnDirection_id = et.EvnDirection_id
				left join v_EvnPLDispDriver epdd on epdd.EvnDirection_id = et.EvnDirection_id
				left join v_ElectronicService es on es.ElectronicService_id = et.ElectronicService_id
				left join lateral (
					select
						ep.EvnPrescr_id
					from
						v_EvnPrescr ep
						inner join v_DopDispInfoConsent ddic on ddic.DopDispInfoConsent_id = ep.DopDispInfoConsent_id
					where
						ddic.EvnPLDisp_id = epdd.EvnPLDispDriver_id
					limit 1
				) ep on true
				left join lateral (
					select
						eda.EvnDirection_id
					from
						v_EvnDirection_all eda
						inner join v_DopDispInfoConsent ddic on ddic.DopDispInfoConsent_id = eda.DopDispInfoConsent_id
					where
						ddic.EvnPLDisp_id = epdd.EvnPLDispDriver_id
					limit 1
				) eda on true
				left join lateral (
					select
						r.Rate_ValuesIs
					from
						v_EvnUslugaDispDop eudd
						inner join v_EvnUslugaRate eur on eur.EvnUsluga_id = eudd.EvnUslugaDispDop_id
						inner join v_Rate r on r.Rate_id = eur.Rate_id
					where
						eudd.EvnUslugaDispDop_rid = epdd.EvnPLDispDriver_id
					order by
						eudd.EvnUslugaDispDop_id desc
					limit 1
				) eu on true
			where
				et.ElectronicTalon_id = :ElectronicTalon_id
			limit 1
		";


		$resp = $this->queryResult($query, $params);

		if (empty($resp[0]['ElectronicTalon_id'])) {
			return array('Error_Msg' => 'Ошибка получении инфомрации по талону');
		}

		if (empty($resp[0]['MedService_id'])) {
			return array('Error_Msg' => 'Ошибка получении информации по службе');
		}

		if (
			false == (havingGroup('DrivingCommissionReg') //
                        && !havingGroup(
                            array(
                                'DrivingCommissionOphth',
                                'DrivingCommissionPsych',
                                'DrivingCommissionPsychNark',
                                'DrivingCommissionTherap')
                        )
                    )
			&& !empty($resp[0]['EvnPLDispDriver_id'])
			&& empty($resp[0]['ResultDispDriver_id'])
			&& empty($resp[0]['EvnPrescr_id']) // если нет назначений на анализы, диагностику
			&& empty($resp[0]['EvnDirection_id']) // если нет направлений к другим врачам
			&& empty($resp[0]['Rate_ValuesIs'])
		) {
			return array('Error_Msg' => 'Для продолжения создайте назначения на обследования, либо заполните поле "Результат" в осмотре');
		}

		$this->load->model('ElectronicTalon_model');

		// порядковый номер пункта обслуживания из диапазона: 1-3
		// пункт обслуживания с наименьшим порядковым номером среди всех пунктов
		// случай осмотра специалиста не закрыт.

		// Если в текущем пункте осмотра случай осмотра текущим специалистом не создан,
		// то идентификатор текущего пункта обслуживания
		// (регистратор платных услуг не создает случаи, проверка не проводится).

		if ($resp[0]['ElectronicService_Num'] >= 1 && $resp[0]['ElectronicService_Num'] <= 3) {

			$ElectronicService_id = null;

			// ('DrivingCommissionReg','DrivingCommissionOphth','DrivingCommissionPsych','DrivingCommissionPsychNark','DrivingCommissionTherap')
			$groups = array();

			// получаем список незакрытых осмотров специалистов
			if (!empty($resp[0]['EvnPLDispDriver_id'])) {

				$filter = '';

				if (getRegionNick() == 'perm') {
					$filter = ' and ddic.DopDispInfoConsent_IsAgree = 2 ';
				}

				$params['EvnPLDispDriver_id'] = $resp[0]['EvnPLDispDriver_id'];

				$query = "
					select
						uc.UslugaComplex_Code as \"UslugaComplex_Code\"
					from
						v_DopDispInfoConsent ddic
						inner join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
						inner join v_UslugaComplex uc on uc.UslugaComplex_id = stl.UslugaComplex_id
					where
						ddic.EvnPLDisp_id = :EvnPLDispDriver_id
						and not exists (
							select
								evdd.EvnVizitDispDop_id
							from
								v_EvnVizitDispDop evdd
							where
								evdd.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
							limit 1
						)
						{$filter}
				";

				$resp_ddic = $this->queryResult($query, $params);

				foreach($resp_ddic as $one_ddic) {
					switch($one_ddic['UslugaComplex_Code']) {
						case 'B04.029.002': // Профилактический прием (осмотр, консультация) врача-офтальмолога
							$groups[] = 'DrivingCommissionOphth';
							break;
						case 'B04.035.002': // Профилактический прием (осмотр, консультация) врача-психиатра
							$groups[] = 'DrivingCommissionPsych';
							break;
						case 'B04.036.002': // Профилактический прием (осмотр, консультация) врача психиатра-нарколога
							$groups[] = 'DrivingCommissionPsychNark';
							break;
						case 'B04.047.002': // Профилактический прием (осмотр, консультация) врача-терапевта
							$groups[] = 'DrivingCommissionTherap';
							break;
					}
				}
			}

			if (count($groups) > 0) {

				$params = array(
					'MedService_id' => $resp[0]['MedService_id'],
					'ElectronicService_id' => $resp[0]['ElectronicService_id']
				);

				$query = "
					select
						es.ElectronicService_id as \"ElectronicService_id\"
					from
						v_MedServiceElectronicQueue mseq
						inner join v_ElectronicService es on es.ElectronicService_id = mseq.ElectronicService_id
						inner join v_MedServiceMedPersonal msmp on msmp.MedServiceMedPersonal_id = mseq.MedServiceMedPersonal_id
						inner join v_pmUserCache puc on puc.MedPersonal_id = msmp.MedPersonal_id
						inner join v_pmUserCacheGroupLink pucgl on pucgl.pmUserCache_id = puc.pmUser_id
						inner join v_pmUserCacheGroup pucg on pucg.pmUserCacheGroup_id = pucgl.pmUserCacheGroup_id
					where
						msmp.MedService_id = :MedService_id
						and pucg.pmUserCacheGroup_SysNick in ('" . implode("','", $groups) . "')
						and es.ElectronicService_id != :ElectronicService_id
					order by
						es.ElectronicService_Num asc
					limit 1
				";

				//echo getDebugSql($query, $params); exit();
				$resp_es = $this->queryResult($query, $params);

				if (!empty($resp_es[0]['ElectronicService_id'])) {
					$ElectronicService_id = $resp_es[0]['ElectronicService_id'];
				}
			}

			// Если у Пользователя группа доступа «Регистратор платных услуг (электронная очередь)
			// Водительская комиссия А,B» и не сохранено информированное добровольное согласие,
			// идентификатор текущего пункта обслуживания
			if (havingGroup('DrivingCommissionReg') && empty($ElectronicService_id)) {
				$ElectronicService_id = $resp[0]['ElectronicService_id'];
			}

			if (empty($ElectronicService_id)) {
				return array('Error_Msg' => 'Не удалось определить следующий пункт обслуживания. Сохраните согласие.');
			}

			$this->ElectronicTalon_model->setElectronicTalonStatus(array(
				'ElectronicTalon_id' => $data['ElectronicTalon_id'],
				'ElectronicService_id' => $ElectronicService_id,
				'ElectronicTalonStatus_id' => 1, // Изменяется текущий статус на Ожидает
				'pmUser_id' => $data['pmUser_id']
			));

			// Для Пользователей, связанных с сотрудником службы,
			// для которого порядковый номер пункта обслуживания равен 4 (четырем)
		} else if ($resp[0]['ElectronicService_Num'] == 4) {

			if (empty($resp[0]['EvnPLDispDriver_id'])) {
				// Если в текущем пункте осмотра случай осмотра текущим специалистом не создан, то:
				$this->ElectronicTalon_model->setElectronicTalonStatus(array(
					'ElectronicTalon_id' => $data['ElectronicTalon_id'],
					'ElectronicTalonStatus_id' => 1, // Изменяется текущий статус на Ожидает
					'pmUser_id' => $data['pmUser_id']
				));
			} else if (!empty($resp[0]['EvnPrescr_id'])) {
				// Если в одном из осмотров в рамках текущего случая медицинского освидетельствования водителя есть назначения, то
				// Идентификатор пункта обслуживания: пункт обслуживания с наименьшим порядковым номером среди всех пунктов, из которых были назначения на дополнительное обследование.
				$ElectronicService_id = null;
				$groups = array();
				if (!empty($resp[0]['EvnPLDispDriver_id'])) {
					// получаем список осмотров специалистов из которых были назначения
					$filter = '';
					if (getRegionNick() == 'perm') {
						$filter = ' and ddic.DopDispInfoConsent_IsAgree = 2 ';
					}
					$resp_ddic = $this->queryResult("
						select
							uc.UslugaComplex_Code as \"UslugaComplex_Code\"
						from
							v_DopDispInfoConsent ddic
							inner join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = stl.UslugaComplex_id
						where
							ddic.EvnPLDisp_id = :EvnPLDispDriver_id
							and exists (
								select
									ep.EvnPrescr_id
								from
									v_EvnPrescr ep
								where
									ep.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
								limit 1
							)
							{$filter}
					", array(
						'EvnPLDispDriver_id' => $resp[0]['EvnPLDispDriver_id']
					));
					foreach($resp_ddic as $one_ddic) {
						switch($one_ddic['UslugaComplex_Code']) {
							case 'B04.029.002': // Профилактический прием (осмотр, консультация) врача-офтальмолога
								$groups[] = 'DrivingCommissionOphth';
								break;
							case 'B04.035.002': // Профилактический прием (осмотр, консультация) врача-психиатра
								$groups[] = 'DrivingCommissionPsych';
								break;
							case 'B04.036.002': // Профилактический прием (осмотр, консультация) врача психиатра-нарколога
								$groups[] = 'DrivingCommissionPsychNark';
								break;
							case 'B04.047.002': // Профилактический прием (осмотр, консультация) врача-терапевта
								$groups[] = 'DrivingCommissionTherap';
								break;
						}
					}
				}
				if (count($groups) > 0) {
					$resp_es = $this->queryResult("
						select
							es.ElectronicService_id as \"ElectronicService_id\"
						from
							v_MedServiceElectronicQueue mseq
							inner join v_ElectronicService es on es.ElectronicService_id = mseq.ElectronicService_id
							inner join v_MedServiceMedPersonal msmp on msmp.MedServiceMedPersonal_id = mseq.MedServiceMedPersonal_id
							inner join v_pmUserCache puc on puc.MedPersonal_id = msmp.MedPersonal_id
							inner join v_pmUserCacheGroupLink pucgl on pucgl.pmUserCache_id = puc.pmUser_id
							inner join v_pmUserCacheGroup pucg on pucg.pmUserCacheGroup_id = pucgl.pmUserCacheGroup_id
						where
							msmp.MedService_id = :MedService_id
							and pucg.pmUserCacheGroup_SysNick in ('" . implode("','", $groups) . "')
						order by
							es.ElectronicService_Num asc
						limit 1
					", array(
						'MedService_id' => $resp[0]['MedService_id']
					));
					if (!empty($resp_es[0]['ElectronicService_id'])) {
						$ElectronicService_id = $resp_es[0]['ElectronicService_id'];
					}
				}
				$this->ElectronicTalon_model->setElectronicTalonStatus(array(
					'ElectronicTalon_id' => $data['ElectronicTalon_id'],
					'ElectronicService_id' => $ElectronicService_id,
					'ElectronicTalonStatus_id' => 5, // Изменяется текущий статус на Отменен
					'pmUser_id' => $data['pmUser_id']
				));
			} else if (!empty($resp[0]['EvnDirection_id'])) {
				// Если в одном из осмотров в рамках текущего случая медицинского освидетельствования водителя есть назначения, то
				// Идентификатор пункта обслуживания: пункт обслуживания с наименьшим порядковым номером среди всех пунктов, из которых были назначения на дополнительное обследование.
				$ElectronicService_id = null;
				$groups = array();
				if (!empty($resp[0]['EvnPLDispDriver_id'])) {
					// получаем список осмотров специалистов из которых были назначения
					$filter = '';
					if (getRegionNick() == 'perm') {
						$filter = ' and ddic.DopDispInfoConsent_IsAgree = 2 ';
					}
					$resp_ddic = $this->queryResult("
						select
							uc.UslugaComplex_Code as \"UslugaComplex_Code\"
						from
							v_DopDispInfoConsent ddic
							inner join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = stl.UslugaComplex_id
						where
							ddic.EvnPLDisp_id = :EvnPLDispDriver_id
							and exists (
								select
									eda.EvnDirection_id
								from
									v_EvnDirection_all eda
								where
									eda.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
								limit 1
							)
							{$filter}
					", array(
						'EvnPLDispDriver_id' => $resp[0]['EvnPLDispDriver_id']
					));
					foreach($resp_ddic as $one_ddic) {
						switch($one_ddic['UslugaComplex_Code']) {
							case 'B04.029.002': // Профилактический прием (осмотр, консультация) врача-офтальмолога
								$groups[] = 'DrivingCommissionOphth';
								break;
							case 'B04.035.002': // Профилактический прием (осмотр, консультация) врача-психиатра
								$groups[] = 'DrivingCommissionPsych';
								break;
							case 'B04.036.002': // Профилактический прием (осмотр, консультация) врача психиатра-нарколога
								$groups[] = 'DrivingCommissionPsychNark';
								break;
							case 'B04.047.002': // Профилактический прием (осмотр, консультация) врача-терапевта
								$groups[] = 'DrivingCommissionTherap';
								break;
						}
					}
				}
				if (count($groups) > 0) {
					$resp_es = $this->queryResult("
						select
							es.ElectronicService_id as \"ElectronicService_id\"
						from
							v_MedServiceElectronicQueue mseq
							inner join v_ElectronicService es on es.ElectronicService_id = mseq.ElectronicService_id
							inner join v_MedServiceMedPersonal msmp on msmp.MedServiceMedPersonal_id = mseq.MedServiceMedPersonal_id
							inner join v_pmUserCache puc on puc.MedPersonal_id = msmp.MedPersonal_id
							inner join v_pmUserCacheGroupLink pucgl on pucgl.pmUserCache_id = puc.pmUser_id
							inner join v_pmUserCacheGroup pucg on pucg.pmUserCacheGroup_id = pucgl.pmUserCacheGroup_id
						where
							msmp.MedService_id = :MedService_id
							and pucg.pmUserCacheGroup_SysNick in ('" . implode("','", $groups) . "')
						order by
							es.ElectronicService_Num asc
						limit 1
					", array(
						'MedService_id' => $resp[0]['MedService_id']
					));
					if (!empty($resp_es[0]['ElectronicService_id'])) {
						$ElectronicService_id = $resp_es[0]['ElectronicService_id'];
					}
				}
				$this->ElectronicTalon_model->setElectronicTalonStatus(array(
					'ElectronicTalon_id' => $data['ElectronicTalon_id'],
					'ElectronicService_id' => $ElectronicService_id,
					'ElectronicTalonStatus_id' => 5, // Изменяется текущий статус на Отменен
					'pmUser_id' => $data['pmUser_id']
				));
			} else {
				// Иначе
				// Идентификатор пункта обслуживания: пункт обслуживания с наименьшим порядковым номером равным 5.
				$ElectronicService_id = null;
				$resp_es = $this->queryResult("
					select
						es.ElectronicService_id as \"ElectronicService_id\"
					from
						v_ElectronicService es
						inner join v_ElectronicQueueInfo eqi on eqi.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
					where
						eqi.MedService_id = :MedService_id
						and es.ElectronicService_Num = 5
					limit 1
				", array(
					'MedService_id' => $resp[0]['MedService_id']
				));
				if (!empty($resp_es[0]['ElectronicService_id'])) {
					$ElectronicService_id = $resp_es[0]['ElectronicService_id'];
				}

				if (empty($ElectronicService_id)) {
					return array('Error_Msg' => 'Не удалось определить пункт обслуживания с наименьшим порядковым номером равным 5.');
				}

				$this->ElectronicTalon_model->setElectronicTalonStatus(array(
					'ElectronicTalon_id' => $data['ElectronicTalon_id'],
					'ElectronicService_id' => $ElectronicService_id,
					'ElectronicTalonStatus_id' => 1, // Изменяется текущий статус на Ожидает
					'pmUser_id' => $data['pmUser_id']
				));
			}
		} else if ($resp[0]['ElectronicService_Num'] == 5) {
			// 4.	Для Пользователей, связанных с сотрудником службы, для которого порядковый номер пункта обслуживания равен 5
			$this->ElectronicTalon_model->setElectronicTalonStatus(array(
				'ElectronicTalon_id' => $data['ElectronicTalon_id'],
				'ElectronicService_id' => null, // Удаляется ссылка на пункт обслуживания
				'ElectronicTalonStatus_id' => 4, // Изменяется текущий статус на Обслужен
				'pmUser_id' => $data['pmUser_id']
			));
		}

		if (!empty($data['takeNext'])) {
			$this->load->helper('Reg');
			// для записи Талона ЭО с наименьшим порядковым номером талона в статусе «Ожидает» и пункт обслуживания = пункт обслуживания текущего пользователя выполняется процедура вызова следующего пациента
			$resp_next = $this->queryResult("
				select
					et.ElectronicTalon_id as \"ElectronicTalon_id\"
				from
					v_ElectronicTalon et
					inner join v_EvnDirection_all ed on ed.EvnDirection_id = et.EvnDirection_id
					inner join v_TimetableMedService_lite ttms on ttms.EvnDirection_id = ed.EvnDirection_id
				where
					et.ElectronicTalonStatus_id = 1
					and et.ElectronicService_id = :ElectronicService_id
					and ttms.TimetableMedService_Day = :TimetableMedService_Day
				order by
					et.ElectronicTalon_Num
				desc
				limit 1
			", array(
				'ElectronicService_id' => $resp[0]['ElectronicService_id'],
				'TimetableMedService_Day' => TimeToDay(time())
			));

			if (!empty($resp_next[0]['ElectronicTalon_id'])) {
				$this->ElectronicTalon_model->setElectronicTalonStatus(array(
					'ElectronicTalon_id' => $resp_next[0]['ElectronicTalon_id'],
					'ElectronicTalonStatus_id' => 2, // Вызван
					'pmUser_id' => $data['pmUser_id']
				));
			}
		}

		return array('Error_Msg' => '');
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
				count(es.ElectronicService_id) as \"cnt\"
			from v_ElectronicService es
			where
				es.ElectronicQueueInfo_id = (
					select
						eswh.ElectronicQueueInfo_id
					from v_ElectronicService eswh
					where eswh.ElectronicService_id = :ElectronicService_id
					limit 1
				)
				and es.ElectronicService_Num = :ElectronicService_Num
		";

		$resp = $this->queryResult($query, $params);
		return (!empty($resp[0]['cnt']) ? $resp[0]['cnt'] : NULL);
	}

	/**
	 * Завершение приёма
	 */
	function getElectronicServiceId($data, $mostlyFree = false) {

		$params = array(
			'ElectronicService_id' => $data['ElectronicService_id'], // тот с которого пришли
			'ElectronicService_Num' => $data['ElectronicService_Num'] // искомый порядковый
		);

		$query = "
			select
				es.ElectronicService_id as \"ElectronicService_id\"
			from v_ElectronicService es
			where
				es.ElectronicQueueInfo_id = (
					select
						eswh.ElectronicQueueInfo_id
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
				select
					b.ElectronicService_id as \"ElectronicService_id\"
				from (
					select
						count(et1.ElectronicTalon_id) as cnt,
						et1.ElectronicService_id
					from v_ElectronicTalon et1
					where et1.ElectronicService_id in (
							select
								es2.ElectronicService_id
							from v_ElectronicService es2
							where
								es2.ElectronicQueueInfo_id = (
									select
										eswh.ElectronicQueueInfo_id
									from v_ElectronicService eswh
									where eswh.ElectronicService_id = :ElectronicService_id
									limit 1
								)
								and es2.ElectronicService_Num = :ElectronicService_Num
					)
					and to_char(et1.ElectronicTalon_insDT, 'dd.mm.yyyy') = to_char(dbo.tzGetDate(), 'dd.mm.yyyy')
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
							from v_ElectronicService es1
							where
								es1.ElectronicQueueInfo_id = (
									select
										eswh.ElectronicQueueInfo_id
									from v_ElectronicService eswh
									where eswh.ElectronicService_id = :ElectronicService_id
									limit 1
								)
								and es1.ElectronicService_Num = :ElectronicService_Num
						)
						and to_char(et2.ElectronicTalon_insDT, 'dd.mm.yyyy') = to_char(dbo.tzGetDate(), 'dd.mm.yyyy')
						and et2.ElectronicTalonStatus_id < 4
						group by ElectronicService_id
						) as a
					)
				limit 1
			";
		}

		$resp = $this->queryResult($query, $params);
		return (!empty($resp[0]['ElectronicService_id']) ? $resp[0]['ElectronicService_id'] : NULL);
	}

	/**
	 * Приём пациента
	 */
	function applyCall($data) {

		//TODO: REMOVE!
		//set_time_limit(200);

		// получаем информацию по талону
		$resp = $this->queryResult("
			select
				ed.Person_id as \"Person_id\",
				et.ElectronicTalonStatus_id as \"ElectronicTalonStatus_id\",
				et.EvnDirection_id as \"EvnDirection_id\",
				rtrim(coalesce(ps.Person_SurName,'')) || rtrim(coalesce(' ' || ps.Person_FirName,'')) || rtrim(coalesce(' ' || ps.Person_SecName, '')) as \"Person_Fio\",
				epdd.EvnPLDispDriver_id as \"EvnPLDispDriver_id\",
				cet.ElectronicTalon_id as \"CalledElectronicTalon_id\",
				cet.Person_Fio as \"CalledPerson_Fio\",
				cet.Person_id as \"CalledPerson_id\",
				cet.Server_id as \"CalledServer_id\",
				cet.PersonEvn_id as \"CalledPersonEvn_id\",
				aet.ElectronicTalon_id as \"AppliedElectronicTalon_id\"
			from
				v_ElectronicTalon et
				inner join v_EvnDirection_all ed on ed.EvnDirection_id = et.EvnDirection_id
				left join v_PersonState ps on ps.Person_id = et.Person_id
				left join v_EvnPLDispDriver epdd on epdd.EvnDirection_id = ed.EvnDirection_id
				left join lateral (
					select
						cet.ElectronicTalon_id,
						rtrim(coalesce(cps.Person_SurName,'')) || rtrim(coalesce(' ' || cps.Person_FirName,'')) || rtrim(coalesce(' ' || cps.Person_SecName, '')) as Person_Fio,
						cps.Person_id,
						cps.Server_id,
						cps.PersonEvn_id
					from
						v_ElectronicTalon cet
						inner join v_TimetableMedService_lite ttms on ttms.EvnDirection_id = cet.EvnDirection_id
						left join v_PersonState cps on cps.Person_id = cet.Person_id
					where
						cet.ElectronicService_id = et.ElectronicService_id
						and cet.ElectronicTalonStatus_id = 2
					limit 1
				) cet on true
				left join lateral (
					select
						aet.ElectronicTalon_id
					from
						v_ElectronicTalon aet
						inner join v_TimetableMedService_lite ttms on ttms.EvnDirection_id = aet.EvnDirection_id
					where
						aet.ElectronicService_id = et.ElectronicService_id
						and aet.ElectronicTalonStatus_id = 3
					limit 1
				) aet on true
			where
				et.ElectronicTalon_id = :ElectronicTalon_id
			limit 1
		", array(
			'ElectronicTalon_id' => $data['ElectronicTalon_id']
		));

		if (empty($resp[0]['Person_id'])) {
			return array('Error_Msg' => 'Ошибка получения информации по электронному талону');
		}

		if (empty($data['withoutElectronicQueue'])) {


			// НОВЫЙ -  «Ожидает»
			// ТЕКУЩИЙ - «Вызван»
			// предупреждение: «На прием был вызван пациент %ФИО пациента со статусом «Вызван»...
			if (empty($data['ignoreCheckAnotherElectronicTalon']) && $resp[0]['ElectronicTalonStatus_id'] == 1 && !empty($resp[0]['CalledElectronicTalon_id'])) {
				return array(
					'Error_Msg' => 'CheckAnotherElectronicTalon',
					'Alert_Msg' => 'На прием был вызван пациент ' . $resp[0]['CalledPerson_Fio'] . ' со статусом «Вызван». Вы действительно хотите принять другого пациента: ' . $resp[0]['Person_Fio'] . ' из записи, для которой была нажата кнопка "Принять"?',
					'CalledPersonData' => array(
						'ElectronicTalon_id' => $resp[0]['CalledElectronicTalon_id'],
						'Person_id' => $resp[0]['CalledPerson_id'],
						'Server_id' => $resp[0]['CalledServer_id'],
						'PersonEvn_id' => $resp[0]['CalledPersonEvn_id']
					)
				);
			}
		}

		$this->load->model('ElectronicTalon_model');

		if (empty($data['ignoreCheckRegister']) && havingGroup('DrivingCommissionReg')) {
			// 5.	Для Пользователей с группой доступа  «Регистратор платных услуг (электронная очередь) Водительская комиссия А,B» производится проверка в регистрах (см. Описание процедуры проверки в регистрах по наркологии и психиатрии)
			// 6.	Для Пользователей с группой доступа «Регистратор платных услуг (электронная очередь) Водительская комиссия А,B» если в  регистрах по психиатрии и по наркологии был найден человек, то дальнейшие действия не производить.
			$resp_register = $this->queryResult("
				select
					case when mt.MorbusType_SysNick = 'narc' then 'наркологии' else 'психиатрии' end as \"RegisterType\",
					D.Diag_Code || ' ' || D.Diag_Name as \"Diag_Name\",
					to_char(PR.PersonRegister_setDate, 'dd.mm.yyyy') as \"PersonRegister_setDate\"
				from
					v_PersonState ps
					inner join v_PersonRegister PR on PR.Person_id = PS.Person_id
					inner join v_MorbusType MT on MT.MorbusType_id = PR.MorbusType_id
					left join v_EvnNotifyNarco EN on EN.EvnNotifyNarco_id = PR.EvnNotifyBase_id
					left join v_MorbusCrazy MO on MO.Morbus_id = coalesce(EN.Morbus_id, PR.Morbus_id)
					left join v_Diag D on D.Diag_id = PR.Diag_id
				where
					ps.Person_id = :Person_id
					and PR.PersonRegister_disDate is null
					and mt.MorbusType_SysNick in ('narc', 'crazy')
			", array(
				'Person_id' => $resp[0]['Person_id']
			));

			// Если в результате поиска пациент был найден в регистрах, то показать Предупреждение: «%ФИО пациента% был найден в регистре по %тип регистра (наркология/психиатрия)% с диагнозом: %код диагноза%, дата включения в регистр %дата включения в регистр% Все равно принять. Отменить услугу» Для каждой записи найденной в регистре информацию о регистре выводить в одном сообщении.
			if (count($resp_register) > 0) {
				$message = '';
				foreach($resp_register as $one_register) {
					if (!empty($message)) {
						$message .= ', ';
					}
					$message .= 'в регистре по ' . $one_register['RegisterType'] . ' с диагнозом ' . $one_register['Diag_Name'] . ', дата включения в регистр ' . $one_register['PersonRegister_setDate'];
				}
				return array('Error_Msg' => 'CheckRegister', 'Alert_Msg' => $resp[0]['Person_Fio'] . ' был найден '.$message);
			}
		}

		// Стартуем транзакцию
		// Если при создании карты произойдёт ошибка, приёма не будет
		$this->db->trans_begin();

		// 2.1.	Принять другого пациента.
		if (!empty($resp[0]['CalledElectronicTalon_id'])) {
			// В Талоне ЭО (с текущим статусом «Вызван») устанавливается текущий статус «В ожидании» и новая запись в истории талонов ЭО (статус, пункт обслуживания, дата время)
			$this->ElectronicTalon_model->setElectronicTalonStatus(array(
				'ElectronicTalon_id' => $resp[0]['CalledElectronicTalon_id'],
				'ElectronicTalonStatus_id' => 1, // Ожидает
				'pmUser_id' => $data['pmUser_id']
			));
		}

		// 3.	Изменение текущего статуса в Талоне ЭО. Меняется идентификатор текущего статус на «На обслуживании» ,
		// 4.	 Новая запись в истории талонов ЭО (статус, пункт обслуживания, дата время)
		$this->ElectronicTalon_model->setElectronicTalonStatus(array(
			'ElectronicTalon_id' => $data['ElectronicTalon_id'],
			'ElectronicTalonStatus_id' => 3, // На обслуживании
			'pmUser_id' => $data['pmUser_id']
		));

		// 7.	Открывается «Случай медицинского освидетельствования водителя» (в ЭМК), связанный с направлением на бирку в режиме:
		// a.	Редактирования – если по идентификатору направления найден случай медицинского освидетельствования водителя
		// b.	Создания – если по идентификатору направления не найден случай медицинского освидетельствования водителя
		if (!empty($resp[0]['EvnPLDispDriver_id'])) {
			$this->db->trans_commit();
			return array('Error_Msg' => '', 'EvnPLDispDriver_id' => $resp[0]['EvnPLDispDriver_id']);
		} else {
			$this->load->model('EvnPLDisp_model');
			$resp_save = $this->EvnPLDisp_model->createEvnPLDisp(array(
				'Person_id' => $resp[0]['Person_id'],
				'DispClass_id' => 26,
				'EvnDirection_id' => $resp[0]['EvnDirection_id'],
				'Lpu_id' => $data['Lpu_id'],
				'session' => $data['session'],
				'pmUser_id' => $data['pmUser_id']
			));
			if (!empty($resp_save['Error_Msg'])) {
				$this->db->trans_rollback();
				return array('Error_Msg' => $resp_save['Error_Msg']);
			}
			if (!empty($resp_save['EvnPLDispDriver_id'])) {
				$this->db->trans_commit();
				return array('Error_Msg' => '', 'EvnPLDispDriver_id' => $resp_save['EvnPLDispDriver_id']);
			}

			$this->db->trans_rollback();
			return array('Error_Msg' => 'Ошибка создания случая медицинского освидетельствования водителя');
		}
	}
}