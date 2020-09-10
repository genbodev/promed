<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * LpuBuildingOfficeMedStaffLink_model - модель для работы с местами работы в кабинетах
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 */

class LpuBuildingOfficeMedStaffLink_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Удаление связки кабинета с местом работы
	 */
	public function delete($data) {
		$result = array();

		// нагребаем данные до удаления
		$broadcast_data = $this->dbmodel->getFirstRowFromQuery("
			select top 1
				msf.MedStaffFact_id,
				ls.LpuBuilding_id,
				ls.LpuSection_id
			from v_LpuBuildingOfficeMedStaffLink lbomsfl (nolock)
			inner join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = lbomsfl.MedStaffFact_id
			inner join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
			where lbomsfl.LpuBuildingOfficeMedStaffLink_id = :LpuBuildingOfficeMedStaffLink_id
		", $data);

		$query = "
			declare
				@Error_Code int,
				@Error_Msg varchar(4000);

			exec p_LpuBuildingOfficeMedStaffLink_del
				@LpuBuildingOfficeMedStaffLink_id = :LpuBuildingOfficeMedStaffLink_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;

			select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";
		$resp = $this->queryResult($query, $data);

		if ( !empty($resp['Error_Msg']) ) {
			$result['success'] = false;
			$result['Error_Msg'] = $resp['Error_Msg'];
			return array($result);
		}

		$result['success'] = true;

		if (!empty($broadcast_data)) {
			// нагребаем параметры для НОДА
			$nodeParams = array(
				'message' => 'ScoreboardTimetableRemoveDoctor',
				'LpuBuilding_id' => $broadcast_data['LpuBuilding_id'], // комната для броадкастинга сообщения
				'LpuSection_id' => $broadcast_data['LpuSection_id'], // комната для броадкастинга сообщения
				'MedStaffFact_id' => $broadcast_data['MedStaffFact_id'], // врач
				'LpuBuildingOfficeMedStaffLink_id' => $data['LpuBuildingOfficeMedStaffLink_id'], // связка врач-кабинет
			);

			// отправляем сообщение через нод всем ТВ (в отделении или подразделении)
			$this->broadcastNodeMessage($nodeParams);
		}

		return array($result);
	}

	/**
	 * Возвращает список связок кабинетов с местами работы
	 */
	public function loadList($data) {
		$filterList = array('(1 = 1)');
		$queryParams = array();

		if ( !empty($data['LpuBuildingOffice_id'])) {
			$filterList[] = "lbomsfl.LpuBuildingOffice_id = :LpuBuildingOffice_id";
			$queryParams['LpuBuildingOffice_id'] = $data['LpuBuildingOffice_id'];
		}

		if ( !empty($data['isClose'])) {
			if ( $data['isClose'] == 1 ) {
				$filterList[] = "(lbomsfl.LpuBuildingOfficeMedStaffLink_begDate is null or lbomsfl.LpuBuildingOfficeMedStaffLink_begDate <= @getdate)";
				$filterList[] = "(lbomsfl.LpuBuildingOfficeMedStaffLink_endDate is null or lbomsfl.LpuBuildingOfficeMedStaffLink_endDate >= @getdate)";
			}
			else if ( $data['isClose'] == 2 ) {
				$filterList[] = "lbomsfl.LpuBuildingOfficeMedStaffLink_endDate < @getdate";
			}
		}

		$query = "
			-- variables
			declare @getdate datetime = cast(dbo.tzGetDate() as date);
			-- end variables

			select
				-- select
				 lbomsfl.LpuBuildingOfficeMedStaffLink_id
				,lbomsfl.LpuBuildingOffice_id
				,lbomsfl.MedService_id
				,lbomsfl.MedStaffFact_id
				,ms.MedService_Name
				,msf.Person_Fio as MedPersonal_FIO
				,convert(varchar(10), lbomsfl.LpuBuildingOfficeMedStaffLink_begDate, 104) as LpuBuildingOfficeMedStaffLink_begDate
				,convert(varchar(10), lbomsfl.LpuBuildingOfficeMedStaffLink_endDate, 104) as LpuBuildingOfficeMedStaffLink_endDate
				,'' as LpuBuildingOfficeVizitTimeData
				-- end select
			from
				-- from
				v_LpuBuildingOfficeMedStaffLink lbomsfl with(nolock)
				left join v_MedService ms with (nolock) on ms.MedService_id = lbomsfl.MedService_id
				left join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = lbomsfl.MedStaffFact_id
				-- end from
			where
				-- where
				" . implode(' and ', $filterList) . "
				-- end where
			order by
				-- order by
				lbomsfl.LpuBuildingOfficeMedStaffLink_begDate
				-- end order by
		";

		$response = $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);

		if ( is_array($response) && count($response) > 0 && array_key_exists('data', $response) && is_array($response['data']) && count($response['data']) > 0 ) {
			$LpuBuildingOfficeMedStaffLinks = array();
			$LpuBuildingOfficeVizitTimeData = array();

			foreach ( $response['data'] as $row ) {
				$LpuBuildingOfficeMedStaffLinks[] = $row['LpuBuildingOfficeMedStaffLink_id'];
			}

			if ( count($LpuBuildingOfficeMedStaffLinks) > 0 ) {
				$LpuBuildingOfficeVizitTimeList = $this->queryResult("
					select
						 LpuBuildingOfficeMedStaffLink_id
						,CalendarWeek_id
						,convert(varchar(5), LpuBuildingOfficeVizitTime_begDate, 108) as LpuBuildingOfficeVizitTime_begDate
						,convert(varchar(5), LpuBuildingOfficeVizitTime_endDate, 108) as LpuBuildingOfficeVizitTime_endDate
					from v_LpuBuildingOfficeVizitTime with (nolock)
					where
						LpuBuildingOfficeMedStaffLink_id in (" . implode(',', $LpuBuildingOfficeMedStaffLinks) . ")
					order by
						LpuBuildingOfficeMedStaffLink_id
				");

				if ( is_array($LpuBuildingOfficeVizitTimeList) && count($LpuBuildingOfficeVizitTimeList) > 0 ) {
					foreach ( $LpuBuildingOfficeVizitTimeList as $row ) {
						$LpuBuildingOfficeVizitTimeData[$row['LpuBuildingOfficeMedStaffLink_id']][] = array(
							'CalendarWeek_id' => $row['CalendarWeek_id'],
							'LpuBuildingOfficeVizitTime_begDate' => $row['LpuBuildingOfficeVizitTime_begDate'],
							'LpuBuildingOfficeVizitTime_endDate' => $row['LpuBuildingOfficeVizitTime_endDate'],
						);
					}
				}
			}

			foreach ( $response['data'] as $key => $row ) {
				if ( !empty($LpuBuildingOfficeVizitTimeData[$row['LpuBuildingOfficeMedStaffLink_id']]) ) {
					$response['data'][$key]['LpuBuildingOfficeVizitTimeData'] = json_encode($LpuBuildingOfficeVizitTimeData[$row['LpuBuildingOfficeMedStaffLink_id']]);
				}
			}
		}

		return $response;
	}

	/**
	 * Возвращает данные связки кабинета с местом работы
	 */
	public function load($data) {
		$query = "
			select top 1
				 LpuBuildingOfficeMedStaffLink_id
				,LpuBuildingOffice_id
				,MedService_id
				,MedStaffFact_id
				,convert(varchar(10), LpuBuildingOfficeMedStaffLink_begDate, 104) as LpuBuildingOfficeMedStaffLink_begDate
				,convert(varchar(10), LpuBuildingOfficeMedStaffLink_endDate, 104) as LpuBuildingOfficeMedStaffLink_endDate
			from
				v_LpuBuildingOfficeMedStaffLink with (nolock)
			where
				LpuBuildingOfficeMedStaffLink_id = :LpuBuildingOfficeMedStaffLink_id
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * Сохраняет кабинет
	 */
	public function save($data) {
		try {
			// начнем транзакцию
			$this->beginTransaction();

			if ( empty($data['MedService_id']) && empty($data['MedStaffFact_id']) ) {
				throw new Exception('Не заполнено одно из обязательных полей. Укажите значение в поле «Служба» или «Место работы». (' . __LINE__ . ')');
			}
			else if ( !empty($data['MedService_id']) && !empty($data['MedStaffFact_id']) ) {
				throw new Exception('Одновременное заполнение полей «Служба» и «Место работы» недопустимо. (' . __LINE__ . ')');
			}

			$LpuBuildingOfficeMedStaffLink_begDate = DateTime::createFromFormat('Y-m-d', $data['LpuBuildingOfficeMedStaffLink_begDate']);
			$LpuBuildingOfficeMedStaffLink_endDate = (!empty($data['LpuBuildingOfficeMedStaffLink_endDate']) ? DateTime::createFromFormat('Y-m-d', $data['LpuBuildingOfficeMedStaffLink_endDate']) : null);

			$action = (empty($data['LpuBuildingOfficeMedStaffLink_id']) ? 'ins' : 'upd');

			// Добавить проверку на "свою МО"
			$data['Lpu_id'] = $data['session']['lpu_id'];

			$LpuBuildingOfficeData = $this->getFirstRowFromQuery("
				select top 1
					 Lpu_id
					,LpuBuildingOffice_Number
					,LpuBuildingOffice_begDate
					,LpuBuildingOffice_endDate
				from v_LpuBuildingOffice with (nolock)
				where LpuBuildingOffice_id = :LpuBuildingOffice_id
			", $data, true);

			if ( $LpuBuildingOfficeData === false || is_null($LpuBuildingOfficeData) ) {
				throw new Exception('Ошибка при получении данных кабинета');
			}

			if ( $LpuBuildingOfficeData['Lpu_id'] != $data['Lpu_id'] ) {
				throw new Exception('Запрещено менять связи мест работы и кабинетов в чужой МО');
			}

			$LpuBuildingOfficeVizitTimeData = array();

			if ( !empty($data['LpuBuildingOfficeVizitTimeData']) ) {
				$LpuBuildingOfficeVizitTimeData = json_decode($data['LpuBuildingOfficeVizitTimeData'], true);
			}

			if ( !empty($data['MedStaffFact_id']) ) {
				// Если период действия связи «кабинет – место работы» не полностью включается в период действия места работы, то выдается сообщение об ошибке
				// «Период работы врача <ФИО врача> с <Дата начала периода работы  врача> по <Дата окончания периода работы врача>. Период действия связи кабинета с
				// врачом с <Дата начала действия периода связи кабинет – место работы> по <дата окончания периода действия периода связи кабинет – место работы>».
				// Кнопка «ОК» при нажатии на кнопку, окно закрывается, форма остается открытой, фокус устанавливается в поле «Дата начала периода действия».
				$MSFWorkDates = $this->getFirstRowFromQuery("
					select top 1
						convert(varchar(10), WorkData_begDate, 120) as WorkData_begDate -- 120 формат для гггг-мм-дд
						,convert(varchar(10), WorkData_endDate, 120) as WorkData_endDate
						,Person_Fio as MedPersonal_FIO
					from v_MedStaffFact with (nolock)
					where MedStaffFact_id = :MedStaffFact_id
				", $data);

				if ( $MSFWorkDates === false ) {
					throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');
				}

				if (
					$MSFWorkDates['WorkData_begDate'] > $data['LpuBuildingOfficeMedStaffLink_begDate']
					|| (
						!empty($MSFWorkDates['WorkData_endDate'])
						&& (
							empty($data['LpuBuildingOfficeMedStaffLink_endDate'])
							|| $MSFWorkDates['WorkData_endDate'] < $data['LpuBuildingOfficeMedStaffLink_endDate']
						)
					)
				) {
					throw new Exception('
						<div>Период работы врача ' . $MSFWorkDates['MedPersonal_FIO'] . ' с ' . $MSFWorkDates['WorkData_begDate'] . ' по ' . (!empty($MSFWorkDates['WorkData_endDate']) ? $MSFWorkDates['WorkData_endDate'] : 'настоящее время') . '.</div>
						<div>Период действия связи кабинета с врачом с ' . $LpuBuildingOfficeMedStaffLink_begDate->format('d.m.Y') . ' по ' . (!empty($LpuBuildingOfficeMedStaffLink_endDate) ? $LpuBuildingOfficeMedStaffLink_endDate->format('d.m.Y') : 'настоящее время') . '.</div>
					');
				}

				// Если добавляют связь «кабинет1 – место работы2», при этом для кабинета1 в этом периоде времени (учитывать даты периода действия и время периода приема
				// с учетом дня недели) уже есть связи (одна или более действующих в указанном периоде) «кабинет1 – место работы», то выдается предупреждение «В указанный
				// период времени в кабинете <номер кабинета> ведет прием врач <ФИО врача 1>». Кнопки «ОК» - при нажатии на копку связь сохраняется, форма закрывается.
				// Кнопка «Отмена» - при нажатии на кнопку связь не сохраняется, форма остается открытой.
				if ( empty($data['ignoreMedStaffFactDoubles']) ) {
					$resp = $this->queryResult("
						select
							 t3.LpuBuildingOffice_Number
							,t4.Person_Fio as MedPersonal_FIO
							,t1.CalendarWeek_id
							,convert(varchar(5), t1.LpuBuildingOfficeVizitTime_begDate, 108) as LpuBuildingOfficeVizitTime_begDate
							,convert(varchar(5), t1.LpuBuildingOfficeVizitTime_endDate, 108) as LpuBuildingOfficeVizitTime_endDate
						from v_LpuBuildingOfficeVizitTime t1 with (nolock)
							inner join v_LpuBuildingOfficeMedStaffLink t2 with (nolock) on t2.LpuBuildingOfficeMedStaffLink_id = t1.LpuBuildingOfficeMedStaffLink_id
							inner join v_LpuBuildingOffice t3 with (nolock) on t3.LpuBuildingOffice_id = t2.LpuBuildingOffice_id
							inner join v_MedStaffFact t4 with (nolock) on t4.MedStaffFact_id = t2.MedStaffFact_id
						where t2.LpuBuildingOffice_id = :LpuBuildingOffice_id
							and (:LpuBuildingOfficeMedStaffLink_endDate is null or t2.LpuBuildingOfficeMedStaffLink_begDate <= :LpuBuildingOfficeMedStaffLink_endDate)
							and (t2.LpuBuildingOfficeMedStaffLink_endDate is null or :LpuBuildingOfficeMedStaffLink_begDate <= t2.LpuBuildingOfficeMedStaffLink_endDate)
							and t2.LpuBuildingOfficeMedStaffLink_id != ISNULL(:LpuBuildingOfficeMedStaffLink_id, 0)
						order by
							t2.MedStaffFact_id
					", $data);

					if ( $resp === false ) {
						throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');
					}

					$DoublesLpuBuildingOfficeVizitTimeData = array();

					foreach ( $resp as $row ) {
						$DoublesLpuBuildingOfficeVizitTimeData[$row['CalendarWeek_id']] = $row;
					}

					if ( count($DoublesLpuBuildingOfficeVizitTimeData) > 0 && count($LpuBuildingOfficeVizitTimeData) > 0 ) {
						foreach ( $LpuBuildingOfficeVizitTimeData as $row ) {
							if (
								array_key_exists($row['CalendarWeek_id'], $DoublesLpuBuildingOfficeVizitTimeData)
								&& (string)$DoublesLpuBuildingOfficeVizitTimeData[$row['CalendarWeek_id']]['LpuBuildingOfficeVizitTime_begDate'] <= (string)$row['LpuBuildingOfficeVizitTime_endDate']
								&& (string)$DoublesLpuBuildingOfficeVizitTimeData[$row['CalendarWeek_id']]['LpuBuildingOfficeVizitTime_endDate'] >= (string)$row['LpuBuildingOfficeVizitTime_begDate']
							) {
								throw new Exception('В указанный период времени в кабинете ' . $DoublesLpuBuildingOfficeVizitTimeData[$row['CalendarWeek_id']]['LpuBuildingOffice_Number'] . ' ведет прием врач ' . $DoublesLpuBuildingOfficeVizitTimeData[$row['CalendarWeek_id']]['MedPersonal_FIO'] . '. Продолжить сохранение?', 1);
							}
						}
					}
				}

				// Если период действия связи кабинет – место работы входит не полностью в период действия кабинета, то выдается сообщение «Период действия кабинета
				// <№ кабинета> с <Дата начала периода действия кабинета> по Окончание периода действия кабинета>. Период действия связи кабинета с врачом с <Дата начала
				// действия периода связи кабинет – место работы> по <дата окончания периода действия периода связи кабинет – место работы>». Кнопка «ОК» при нажатии на
				// кнопку, окно закрывается, форма остается открытой, фокус устанавливается в поле «Дата начала периода действия».
				if (
					$LpuBuildingOfficeData['LpuBuildingOffice_begDate']->format('Y-m-d') > $data['LpuBuildingOfficeMedStaffLink_begDate']
					|| (
						!empty($LpuBuildingOfficeData['LpuBuildingOffice_endDate'])
						&& (
							empty($data['LpuBuildingOfficeMedStaffLink_endDate'])
							|| $data['LpuBuildingOfficeMedStaffLink_endDate'] > $LpuBuildingOfficeData['LpuBuildingOffice_endDate']->format('Y-m-d')
						)
					)
				) {
					throw new Exception('
						<div>Период действия кабинета ' .  $LpuBuildingOfficeData['LpuBuildingOffice_Number'] . ' с ' . $LpuBuildingOfficeData['LpuBuildingOffice_begDate']->format('d.m.Y') . ' по ' . (!empty($LpuBuildingOfficeData['LpuBuildingOffice_endDate']) ? $LpuBuildingOfficeData['LpuBuildingOffice_endDate']->format('d.m.Y') : 'настоящее время') . '.</div>
						<div>Период действия связи кабинета с врачом с ' . $LpuBuildingOfficeMedStaffLink_begDate->format('d.m.Y') . ' по ' . (!empty($LpuBuildingOfficeMedStaffLink_endDate) ? $LpuBuildingOfficeMedStaffLink_endDate->format('d.m.Y') : 'настоящее время') . '.</div>
					');
				}

				// Если связывается в один и тот же период времени (учитывать даты периода действия и время периода приема с учетом дня недели) одно место работы с
				// несколькими кабинетами, выдается сообщение об ошибке «Врач <ФИО врача> в указанный период времени осуществляет прием в кабинете <номер кабинета>».
				// Кнопка «ОК» - при нажатии на кнопку, окно закрывается форма остается открытой.
				$resp = $this->queryResult("
					select
						 t2.LpuBuildingOfficeMedStaffLink_id
						,t2.LpuBuildingOffice_id
						,t2.MedStaffFact_id
						,t3.LpuBuildingOffice_Number
						,t4.Person_Fio as MedPersonal_FIO
						,t1.CalendarWeek_id
						,convert(varchar(5), t1.LpuBuildingOfficeVizitTime_begDate, 108) as LpuBuildingOfficeVizitTime_begDate
						,convert(varchar(5), t1.LpuBuildingOfficeVizitTime_endDate, 108) as LpuBuildingOfficeVizitTime_endDate
						,convert(varchar(10), t2.LpuBuildingOfficeMedStaffLink_begDate, 120) as LpuBuildingOfficeMedStaffLink_begDate
						,convert(varchar(10), t2.LpuBuildingOfficeMedStaffLink_endDate, 120) as LpuBuildingOfficeMedStaffLink_endDate
					from v_LpuBuildingOfficeVizitTime t1 with (nolock)
						inner join v_LpuBuildingOfficeMedStaffLink t2 with (nolock) on t2.LpuBuildingOfficeMedStaffLink_id = t1.LpuBuildingOfficeMedStaffLink_id
						inner join v_LpuBuildingOffice t3 with (nolock) on t3.LpuBuildingOffice_id = t2.LpuBuildingOffice_id
						inner join v_MedStaffFact t4 with (nolock) on t4.MedStaffFact_id = t2.MedStaffFact_id
					where t2.MedStaffFact_id = :MedStaffFact_id
						and (:LpuBuildingOfficeMedStaffLink_endDate is null or t2.LpuBuildingOfficeMedStaffLink_begDate <= :LpuBuildingOfficeMedStaffLink_endDate)
						and (t2.LpuBuildingOfficeMedStaffLink_endDate is null or :LpuBuildingOfficeMedStaffLink_begDate <= t2.LpuBuildingOfficeMedStaffLink_endDate)
						and t2.LpuBuildingOfficeMedStaffLink_id != ISNULL(:LpuBuildingOfficeMedStaffLink_id, 0)
					order by
						t3.LpuBuildingOffice_id
				", $data);

				if ( $resp === false ) {
					throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');
				}

				$DoublesLpuBuildingOfficeVizitTimeData = array();

				foreach ( $resp as $row ) {
					$DoublesLpuBuildingOfficeVizitTimeData[$row['CalendarWeek_id']] = $row;
				}

				if ( count($DoublesLpuBuildingOfficeVizitTimeData) > 0 && count($LpuBuildingOfficeVizitTimeData) > 0 ) {
					foreach ( $LpuBuildingOfficeVizitTimeData as $row ) {
						if (
							array_key_exists($row['CalendarWeek_id'], $DoublesLpuBuildingOfficeVizitTimeData)
							&& (string)$DoublesLpuBuildingOfficeVizitTimeData[$row['CalendarWeek_id']]['LpuBuildingOfficeVizitTime_begDate'] >= (string)$row['LpuBuildingOfficeVizitTime_begDate']
							&& (string)$DoublesLpuBuildingOfficeVizitTimeData[$row['CalendarWeek_id']]['LpuBuildingOfficeVizitTime_endDate'] <= (string)$row['LpuBuildingOfficeVizitTime_endDate']
						) {
							if ( $data['LpuBuildingOfficeMedStaffLink_begDate'] <= (string)$DoublesLpuBuildingOfficeVizitTimeData[$row['CalendarWeek_id']]['LpuBuildingOfficeMedStaffLink_begDate'] ) {
								throw new Exception('Дата начала приёма врача в кабинете ' . $LpuBuildingOfficeData['LpuBuildingOffice_Number'] . ' должна быть больше даты начала приёма в кабинете ' . $DoublesLpuBuildingOfficeVizitTimeData[$row['CalendarWeek_id']]['LpuBuildingOffice_Number'] . '.');
							}

							if ( empty($data['ignoreLpuBuildingDoubles']) ) {
								throw new Exception('Врач ' . $DoublesLpuBuildingOfficeVizitTimeData[$row['CalendarWeek_id']]['MedPersonal_FIO'] . ' в указанный период времени осуществляет прием в кабинете ' . $DoublesLpuBuildingOfficeVizitTimeData[$row['CalendarWeek_id']]['LpuBuildingOffice_Number'] . '. Изменить номер кабинета?', 2);
							}
						}
						else {
							unset($DoublesLpuBuildingOfficeVizitTimeData[$row['CalendarWeek_id']]);
						}
					}

					if ( count($DoublesLpuBuildingOfficeVizitTimeData) > 0 ) {
						// Пересохраняем данные, указывая дату окончания у дубля, равную дате начала действия всохраняемой записи минус 1 день
						foreach ( $DoublesLpuBuildingOfficeVizitTimeData as $row ) {
							$query = "
								declare
									@LpuBuildingOfficeMedStaffLink_id bigint = :LpuBuildingOfficeMedStaffLink_id,
									@LpuBuildingOfficeMedStaffLink_endDate datetime = dateadd(DAY, -1, cast(:LpuBuildingOfficeMedStaffLink_endDate as date)),
									@Error_Code bigint,
									@Error_Message varchar(4000);

								exec p_LpuBuildingOfficeMedStaffLink_upd
									@LpuBuildingOfficeMedStaffLink_id = @LpuBuildingOfficeMedStaffLink_id output,
									@LpuBuildingOffice_id = :LpuBuildingOffice_id,
									@MedStaffFact_id = :MedStaffFact_id,
									@LpuBuildingOfficeMedStaffLink_begDate = :LpuBuildingOfficeMedStaffLink_begDate,
									@LpuBuildingOfficeMedStaffLink_endDate = @LpuBuildingOfficeMedStaffLink_endDate,
									@pmUser_id = :pmUser_id,
									@Error_Code = @Error_Code output,
									@Error_Message = @Error_Message output;

								select @LpuBuildingOfficeMedStaffLink_id as LpuBuildingOfficeMedStaffLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
							";

							$row['LpuBuildingOfficeMedStaffLink_endDate'] = $data['LpuBuildingOfficeMedStaffLink_begDate'];
							$row['pmUser_id'] = $data['pmUser_id'];

							$response = $this->queryResult($query, $row);

							if ( !empty($response[0]['Error_Msg']) ) {
								throw new Exception($response[0]['Error_Msg']);
							}
						}
					}
				}
			}
			else if ( !empty($data['MedService_id']) ) {
				// Период действия связки «Служба»-«Кабинет» должен входить в период работы «Службы». Если условие не выполняется, то сообщение об ошибке:
				// «На указанный период действия служба <Наименование службы> не работает. Проверьте корректность введенных данных в полях «период действия» или
				// «служба» Ок. При закрытии сообщения, изменения не сохраняются, форма «Назначение связи Кабинет – Место работы» остается открытой.
				$MSWorkDates = $this->getFirstRowFromQuery("
					select top 1
						 convert(varchar(10), MedService_begDT, 120) as MedService_begDT -- 120 формат для гггг-мм-дд
						,convert(varchar(10), MedService_endDT, 120) as MedService_endDT
						,MedService_Name
					from v_MedService with (nolock)
					where MedService_id = :MedService_id
				", $data);

				if ( $MSWorkDates === false ) {
					throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');
				}

				if (
					$MSWorkDates['MedService_begDT'] > $data['LpuBuildingOfficeMedStaffLink_begDate']
					|| (
						!empty($MSWorkDates['MedService_endDT'])
						&& (
							empty($data['LpuBuildingOfficeMedStaffLink_endDate'])
							|| $MSWorkDates['MedService_endDT'] < $data['LpuBuildingOfficeMedStaffLink_endDate']
						)
					)
				) {
					throw new Exception('На указанный период действия служба <b>' . $MSWorkDates['MedService_Name'] . '</b> не работает. Проверьте корректность введенных данных в полях «Период действия» или «Служба»');
				}

				// Проверка на дубли связи «кабинет - служба» по периоду действия
				// Проверка наличия другой записи связи «кабинет – служба» с параметрами:
				// - Период действия пересекает период действия сохраняемой записи (пересечение хотя бы в 1 день)
				// - Служба совпадает со службой в сохраняемой записи
				// Если такая запись найдена, то сообщение об ошибке: «В указанном периоде действия служба <наименование службы> работает в кабинете <№ кабинета>.
				// Работа одной службы одновременно в нескольких кабинетах недоступна.
				$resp = $this->queryResult("
					select
						 t3.LpuBuildingOffice_Number
						,t4.MedService_Name
						,t1.CalendarWeek_id
						,convert(varchar(5), t1.LpuBuildingOfficeVizitTime_begDate, 108) as LpuBuildingOfficeVizitTime_begDate
						,convert(varchar(5), t1.LpuBuildingOfficeVizitTime_endDate, 108) as LpuBuildingOfficeVizitTime_endDate
					from v_LpuBuildingOfficeVizitTime t1 with (nolock)
						inner join v_LpuBuildingOfficeMedStaffLink t2 with (nolock) on t2.LpuBuildingOfficeMedStaffLink_id = t1.LpuBuildingOfficeMedStaffLink_id
						inner join v_LpuBuildingOffice t3 with (nolock) on t3.LpuBuildingOffice_id = t2.LpuBuildingOffice_id
						inner join v_MedService t4 with (nolock) on t4.MedService_id = t2.MedService_id
					where t2.MedService_id = :MedService_id
						and (:LpuBuildingOfficeMedStaffLink_endDate is null or t2.LpuBuildingOfficeMedStaffLink_begDate <= :LpuBuildingOfficeMedStaffLink_endDate)
						and (t2.LpuBuildingOfficeMedStaffLink_endDate is null or :LpuBuildingOfficeMedStaffLink_begDate <= t2.LpuBuildingOfficeMedStaffLink_endDate)
						and t2.LpuBuildingOfficeMedStaffLink_id != ISNULL(:LpuBuildingOfficeMedStaffLink_id, 0)
					order by
						t2.MedService_id
				", $data);

				if ( $resp === false ) {
					throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');
				}

				$DoublesLpuBuildingOfficeVizitTimeData = array();

				foreach ( $resp as $row ) {
					$DoublesLpuBuildingOfficeVizitTimeData[$row['CalendarWeek_id']] = $row;
				}

				if ( count($DoublesLpuBuildingOfficeVizitTimeData) > 0 && count($LpuBuildingOfficeVizitTimeData) > 0 ) {
					foreach ( $LpuBuildingOfficeVizitTimeData as $row ) {
						if (
							array_key_exists($row['CalendarWeek_id'], $DoublesLpuBuildingOfficeVizitTimeData)
							&& (string)$DoublesLpuBuildingOfficeVizitTimeData[$row['CalendarWeek_id']]['LpuBuildingOfficeVizitTime_begDate'] <= (string)$row['LpuBuildingOfficeVizitTime_endDate']
							&& (string)$DoublesLpuBuildingOfficeVizitTimeData[$row['CalendarWeek_id']]['LpuBuildingOfficeVizitTime_endDate'] >= (string)$row['LpuBuildingOfficeVizitTime_begDate']
						) {
							throw new Exception('В указанном периоде действия служба <b>' . $DoublesLpuBuildingOfficeVizitTimeData[$row['CalendarWeek_id']]['MedService_Name'] . '</b> работает в кабинете <b>' . $DoublesLpuBuildingOfficeVizitTimeData[$row['CalendarWeek_id']]['LpuBuildingOffice_Number'] . '</b>. Работа одной службы одновременно в нескольких кабинетах недоступна.');
						}
					}
				}
			}

			$query = "
				declare
					@LpuBuildingOfficeMedStaffLink_id bigint = :LpuBuildingOfficeMedStaffLink_id,
					@Error_Code bigint,
					@Error_Message varchar(4000);

				exec p_LpuBuildingOfficeMedStaffLink_{$action}
					@LpuBuildingOfficeMedStaffLink_id = @LpuBuildingOfficeMedStaffLink_id output,
					@LpuBuildingOffice_id = :LpuBuildingOffice_id,
					@MedService_id = :MedService_id,
					@MedStaffFact_id = :MedStaffFact_id,
					@LpuBuildingOfficeMedStaffLink_begDate = :LpuBuildingOfficeMedStaffLink_begDate,
					@LpuBuildingOfficeMedStaffLink_endDate = :LpuBuildingOfficeMedStaffLink_endDate,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;

				select @LpuBuildingOfficeMedStaffLink_id as LpuBuildingOfficeMedStaffLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			";

			$response = $this->queryResult($query, $data);

			if ( !empty($response[0]['Error_Msg']) ) {
				throw new Exception($response[0]['Error_Msg']);
			}

			$LpuBuildingOfficeMedStaffLink_id = $response[0]['LpuBuildingOfficeMedStaffLink_id'];

			$CurrentLpuBuildingOfficeVizitTimeData = array();

			if ( $action == 'upd' ) {
				$resp = $this->queryResult("
					select
						 LpuBuildingOfficeVizitTime_id
						,CalendarWeek_id
					from v_LpuBuildingOfficeVizitTime with (nolock)
					where LpuBuildingOfficeMedStaffLink_id = :LpuBuildingOfficeMedStaffLink_id
				", array('LpuBuildingOfficeMedStaffLink_id' => $LpuBuildingOfficeMedStaffLink_id));

				if ( $resp === false ) {
					throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');
				}

				foreach ( $resp as $row ) {
					$CurrentLpuBuildingOfficeVizitTimeData[$row['CalendarWeek_id']] = $row['LpuBuildingOfficeVizitTime_id'];
				}
			}

			$Worktime = array();

			foreach ( $LpuBuildingOfficeVizitTimeData as $row ) {

				$LpuBuildingOfficeVizitTime_id = null;
				$tmpAction = 'ins';

				if ( array_key_exists($row['CalendarWeek_id'], $CurrentLpuBuildingOfficeVizitTimeData) ) {
					$LpuBuildingOfficeVizitTime_id = $CurrentLpuBuildingOfficeVizitTimeData[$row['CalendarWeek_id']];
					$tmpAction = 'upd';
					unset($CurrentLpuBuildingOfficeVizitTimeData[$row['CalendarWeek_id']]);
				}

				$Worktime[$row['CalendarWeek_id']] = $row['LpuBuildingOfficeVizitTime_begDate'].' - '.$row['LpuBuildingOfficeVizitTime_endDate'];

				$resp = $this->queryResult("
					declare
						@LpuBuildingOfficeVizitTime_id bigint = :LpuBuildingOfficeVizitTime_id,
						@Error_Code bigint,
						@Error_Message varchar(4000);

					exec p_LpuBuildingOfficeVizitTime_{$tmpAction}
						@LpuBuildingOfficeVizitTime_id = @LpuBuildingOfficeVizitTime_id output,
						@LpuBuildingOfficeMedStaffLink_id = :LpuBuildingOfficeMedStaffLink_id,
						@CalendarWeek_id = :CalendarWeek_id,
						@LpuBuildingOfficeVizitTime_begDate = :LpuBuildingOfficeVizitTime_begDate,
						@LpuBuildingOfficeVizitTime_endDate = :LpuBuildingOfficeVizitTime_endDate,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;

					select @LpuBuildingOfficeVizitTime_id as LpuBuildingOfficeVizitTime_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
				", array(
					'LpuBuildingOfficeVizitTime_id' => $LpuBuildingOfficeVizitTime_id,
					'LpuBuildingOfficeMedStaffLink_id' => $LpuBuildingOfficeMedStaffLink_id,
					'CalendarWeek_id' => $row['CalendarWeek_id'],
					'LpuBuildingOfficeVizitTime_begDate' => $row['LpuBuildingOfficeVizitTime_begDate'],
					'LpuBuildingOfficeVizitTime_endDate' => $row['LpuBuildingOfficeVizitTime_endDate'],
					'pmUser_id' => $data['pmUser_id'],
				));

				if ( $resp === false ) {
					throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');
				}
				else if ( !empty($resp[0]['Error_Msg']) ) {
					throw new Exception($response[0]['Error_Msg']);
				}
			}

			// Удаляем ненужные
			foreach ( $CurrentLpuBuildingOfficeVizitTimeData as $LpuBuildingOfficeVizitTime_id ) {
				$resp = $this->queryResult("
					declare
						@Error_Code bigint,
						@Error_Message varchar(4000);

					exec p_LpuBuildingOfficeVizitTime_del
						@LpuBuildingOfficeVizitTime_id = :LpuBuildingOfficeVizitTime_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;

					select @Error_Code as Error_Code, @Error_Message as Error_Msg;
				", array(
					'LpuBuildingOfficeVizitTime_id' => $LpuBuildingOfficeVizitTime_id,
				));

				if ( $resp === false ) {
					throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');
				}
				else if ( !empty($resp[0]['Error_Msg']) ) {
					throw new Exception($response[0]['Error_Msg']);
				}
			}

			$this->commitTransaction();

			// нагребаем данные до удаления
			$broadcast_data = $this->dbmodel->getFirstRowFromQuery("
				select top 1
					ls.LpuBuilding_id,
					ls.LpuSection_id,
					lbo.LpuBuildingOffice_Number,
					post.name as Post_Name,
					msf.Person_SurName,
					(LEFT(msf.Person_FirName, 1) + '.' + LEFT(msf.Person_SecName, 1) + '.') as Person_Initials
				from v_LpuBuildingOfficeMedStaffLink lbomsfl (nolock)
				inner join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = lbomsfl.MedStaffFact_id
				inner join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
				inner join v_LpuBuildingOffice lbo (nolock) on lbo.LpuBuildingOffice_id = lbomsfl.LpuBuildingOffice_id
				left join persis.Post as post with (nolock) on post.id = msf.Post_id
				where lbomsfl.LpuBuildingOfficeMedStaffLink_id = :LpuBuildingOfficeMedStaffLink_id
			", array('LpuBuildingOfficeMedStaffLink_id' => (!empty($data['LpuBuildingOfficeMedStaffLink_id']) ? $data['LpuBuildingOfficeMedStaffLink_id'] : $LpuBuildingOfficeMedStaffLink_id)));

			if (!empty($data['LpuBuildingOfficeVizitTimeRemovedDays'])) {
				$removed_days = json_decode($data['LpuBuildingOfficeVizitTimeRemovedDays'], true);

				foreach ($removed_days as $day) {
					$Worktime[$day] = ''; // опустошаем день если он убран
				}
			}

			if (!empty($Worktime) && !empty($broadcast_data)) {

				// нагребаем параметры для НОДА
				$nodeParams = array(
					'LpuBuilding_id' => $broadcast_data['LpuBuilding_id'], // комната для броадкастинга сообщения
					'LpuSection_id' => $broadcast_data['LpuSection_id'], // комната для броадкастинга сообщения
					'Worktime' => json_encode($Worktime) // изменившееся время работы
				);

				if (!empty($data['LpuBuildingOfficeMedStaffLink_id'])) {

					// обновляем рабочее время
					$nodeParams['message'] = 'ScoreboardTimetableChangeWorktime';
					$nodeParams['LpuBuildingOfficeMedStaffLink_id'] = $data['LpuBuildingOfficeMedStaffLink_id']; // связка врач-кабинет

				} else {

					// добавляем связь кабинет-доктор, обонвляем время
					$nodeParams['message'] = 'ScoreboardTimetableAppendDoctor';
					$nodeParams['LpuBuildingOffice_Number'] = $broadcast_data['LpuBuildingOffice_Number'];
					$nodeParams['Doctor_Fio'] = mb_ucfirst(mb_strtolower($broadcast_data['Person_SurName'])).' '.$broadcast_data['Person_Initials'];
					$nodeParams['Post_Name'] = $broadcast_data['Post_Name'];
					$nodeParams['LpuBuildingOfficeMedStaffLink_id'] = $LpuBuildingOfficeMedStaffLink_id; // связка врач-кабинет
				}

				// отправляем сообщение через нод всем ТВ (в отделении или подразделении)
				$this->broadcastNodeMessage($nodeParams);
			}

		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();

			$code = $e->getCode();

			if ( !empty($code) ) {
				$response = array(array('Alert_Msg' => $e->getMessage(), 'Error_Code' => $code));
			}
			else {
				$response = array(array('Error_Msg' => $e->getMessage()));
			}
		}

		return $response;
	}

	/**
	 * Отправка информационного сообщение на табло, через нод-портала
	 */
	function broadcastNodeMessage($data) {

		// инициализируем настройки соединения
		$config = null;
		if (defined('NODEJS_PORTAL_PROXY_HOSTNAME') && defined('NODEJS_PORTAL_PROXY_HTTPPORT')) {
			// берём хост и порт из конфига, если есть
			$config = array(
				'host' => NODEJS_PORTAL_PROXY_HOSTNAME,
				'port' => NODEJS_PORTAL_PROXY_HTTPPORT
			);


			$this->load->helper('NodeJS');
			$response = NodePostRequest($data, $config);

			if (!empty($response[0]['Error_Msg'])) {
				return $response[0];
			}
		}
	}


	/**
	 *
	 *
	 * @param $data
	 * @return array|false
	 */
	public function loadScheduleWorkDoctor($data) {
		$filterList = array('(1 = 1)');
		$queryParams = array();

		if ( ! empty($data['Lpu_id'])) {
			$filterList[] = "v_msf.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if ( ! empty($data['LpuBuilding_id'])) {
			$filterList[] = "v_msf.LpuBuilding_id = :LpuBuilding_id";
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}

		if ( ! empty($data['LpuSection_id'])) {
			$filterList[] = "v_msf.LpuSection_id = :LpuSection_id";
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ( ! empty($data['LpuSectionProfile_id'])) {
			$filterList[] = "v_msf.LpuSectionProfile_id = :LpuSectionProfile_id";
			$queryParams['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
		}

		if ( ! empty($data['Post_id'])) {
			$filterList[] = "v_msf.Post_id = :Post_id";
			$queryParams['Post_id'] = $data['Post_id'];
		}

		if ( ! empty($data['MedStaffFact_id'])) {
			$filterList[] = "v_lbomsl.MedStaffFact_id = :MedStaffFact_id";
			$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
		}

		if ( ! empty($data['LpuRegion_id'])) {
			$filterList[] = "v_msr.LpuRegion_id = :LpuRegion_id";
			$queryParams['LpuRegion_id'] = $data['LpuRegion_id'];
		}

		if ( ! empty($data['LpuBuildingOffice_id'])) {
			$filterList[] = "v_lbomsl.LpuBuildingOffice_id = :LpuBuildingOffice_id";
			$queryParams['LpuBuildingOffice_id'] = $data['LpuBuildingOffice_id'];
		}

		if ( ! empty($data['mondayDate']) &&  ! empty($data['sundayDate'])) {
			$filterList[] = "
				(:sundayDate IS NULL OR v_lbomsl.LpuBuildingOfficeMedStaffLink_begDate <= :sundayDate) AND
				(v_lbomsl.LpuBuildingOfficeMedStaffLink_endDate IS NULL OR :mondayDate <= v_lbomsl.LpuBuildingOfficeMedStaffLink_endDate)
			";
			$queryParams['mondayDate'] = $data['mondayDate'];
			$queryParams['sundayDate'] = $data['sundayDate'];
		}

		$select = "
			v_lbomsl.LpuBuildingOfficeMedStaffLink_id,
			v_p.name as Post_Name,
			v_lbo.LpuBuildingOffice_Number,
			v_msf.Person_SurName,
			(LEFT(v_msf.Person_FirName, 1) + '.' + LEFT(v_msf.Person_SecName, 1) + '.') as Person_Initials
		";

		if (empty($data['fromApi'])) {
			$select .= ",
				v_msf.MedStaffFact_id,
				v_lbomsl.LpuBuildingOffice_id,
				convert(varchar(10), v_lbomsl.LpuBuildingOfficeMedStaffLink_begDate, 104) as LpuBuildingOfficeMedStaffLink_begDate,
				convert(varchar(10), v_lbomsl.LpuBuildingOfficeMedStaffLink_endDate, 104) as LpuBuildingOfficeMedStaffLink_endDate,
				LTRIM(RTRIM(v_msf.Person_Fio)) as Person_Fio,
				v_lr.LpuRegion_Name
			";
		}

		$query = "
			select
				{$select}
			from
				v_LpuBuildingOfficeMedStaffLink v_lbomsl with (nolock)
				inner join v_MedStaffFact v_msf with (nolock) on v_msf.MedStaffFact_id = v_lbomsl.MedStaffFact_id
				inner join v_LpuBuildingOffice v_lbo with (nolock) on v_lbo.LpuBuildingOffice_id = v_lbomsl.LpuBuildingOffice_id
				left join persis.Post as v_p with (nolock) on v_p.id = v_msf.Post_id
				left join v_MedStaffRegion as v_msr  with (nolock) on v_msr.MedStaffFact_id = v_msf.MedStaffFact_id
				outer apply (
					select substring((
						select ',' + v_lr.LpuRegion_Name  AS [text()] from v_LpuRegion v_lr (nolock) where v_lr.LpuRegion_id = v_msr.LpuRegion_id FOR XML PATH('')
					), 2, 1000) LpuRegion_Name
				) v_lr
			where
				" . implode(' and ', $filterList) . "
			order by
				v_p.name,
				v_msf.Person_SurName
		";

		$offices = $this->queryResult($query, $queryParams);

		$response = array();
		if (!empty($offices)) {


			foreach ($offices as $item) {

				if (!empty($data['fromApi'])) {
					$item['Doctor_Fio'] = mb_ucfirst(mb_strtolower($item['Person_SurName'])).' '.$item['Person_Initials'];
					unset($item['Person_SurName']);
					unset($item['Person_Initials']);
				}

				$response[$item['LpuBuildingOfficeMedStaffLink_id']] = $item;
			}

			$in_list = implode(',', array_keys($response));

			// mondayDate и sundayDate показывают начало дня т.е. (0ч 00мин)
			$vizit_time = $this->queryResult("
				select
					v_lbovt.LpuBuildingOfficeVizitTime_id,
					v_lbovt.LpuBuildingOfficeMedStaffLink_id, 
					v_lbovt.CalendarWeek_id, 
					convert(varchar(5), v_lbovt.LpuBuildingOfficeVizitTime_begDate, 108) as LpuBuildingOfficeVizitTime_begDate, 
					convert(varchar(5), v_lbovt.LpuBuildingOfficeVizitTime_endDate, 108) as LpuBuildingOfficeVizitTime_endDate
				from v_LpuBuildingOfficeVizitTime as v_lbovt with (nolock)
				where v_lbovt.LpuBuildingOfficeMedStaffLink_id in ({$in_list})
				order by v_lbovt.LpuBuildingOfficeMedStaffLink_id
			");

			if (!empty($vizit_time)) {
				foreach ($vizit_time as $item) {

					// назовем имя поля для апи адекватнее
					$fieldName = empty($data['fromApi']) ? 'LpuBuildingOfficeVizitTimeData' : 'Worktime' ;

					if (isset($response[$item['LpuBuildingOfficeMedStaffLink_id']])) {

						$msf_link = &$response[$item['LpuBuildingOfficeMedStaffLink_id']];
						if (!isset($msf_link[$fieldName])) $msf_link[$fieldName] = array();

						// для апи наполнение будет другое
						if (empty($data['fromApi'])) {
							$msf_link[$fieldName][$item['CalendarWeek_id']] = array(
								'LpuBuildingOfficeVizitTime_id' => $item['LpuBuildingOfficeVizitTime_id'],
								'CalendarWeek_id' => $item['CalendarWeek_id'],
								'LpuBuildingOfficeVizitTime_begDate' => $item['LpuBuildingOfficeVizitTime_begDate'],
								'LpuBuildingOfficeVizitTime_endDate' => $item['LpuBuildingOfficeVizitTime_endDate'],
							);
						} else {
							$msf_link[$fieldName][$item['CalendarWeek_id']] =
								$item['LpuBuildingOfficeVizitTime_begDate'].' - '.$item['LpuBuildingOfficeVizitTime_endDate'];
						}
					}
				}
			}
		}

		$response = array_values($response);
		return $response;
	}


	public function loadScheduleWorkDoctorScoreboard($data)
	{
		$filterList = array('(1 = 1)');
		$queryParams = array();

		if (!empty($data['Lpu_id'])) {
			$filterList[] = "v_msf.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if (!empty($data['LpuBuilding_id'])) {
			$filterList[] = "v_msf.LpuBuilding_id = :LpuBuilding_id";
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}

		if (!empty($data['LpuSection_id'])) {
			$filterList[] = "v_msf.LpuSection_id = :LpuSection_id";
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}

		if (!empty($data['LpuSectionProfile_id'])) {
			$filterList[] = "v_msf.LpuSectionProfile_id = :LpuSectionProfile_id";
			$queryParams['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
		}

		if (!empty($data['Post_id'])) {
			$filterList[] = "v_msf.Post_id = :Post_id";
			$queryParams['Post_id'] = $data['Post_id'];
		}

		if (!empty($data['MedStaffFact_id'])) {
			$filterList[] = "v_lbomsl.MedStaffFact_id = :MedStaffFact_id";
			$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
		}

		if (!empty($data['LpuRegion_id'])) {
			$filterList[] = "v_msr.LpuRegion_id = :LpuRegion_id";
			$queryParams['LpuRegion_id'] = $data['LpuRegion_id'];
		}

		if (!empty($data['LpuBuildingOffice_id'])) {
			$filterList[] = "v_lbomsl.LpuBuildingOffice_id = :LpuBuildingOffice_id";
			$queryParams['LpuBuildingOffice_id'] = $data['LpuBuildingOffice_id'];
		}

		if (!empty($data['mondayDate']) && !empty($data['sundayDate'])) {
			$filterList[] = "
				(:sundayDate IS NULL OR v_lbomsl.LpuBuildingOfficeMedStaffLink_begDate <= :sundayDate) AND
				(v_lbomsl.LpuBuildingOfficeMedStaffLink_endDate IS NULL OR :mondayDate <= v_lbomsl.LpuBuildingOfficeMedStaffLink_endDate)
			";
			$queryParams['mondayDate'] = $data['mondayDate'];
			$queryParams['sundayDate'] = $data['sundayDate'];
		}



		$query = "
			select
				v_msf.MedStaffFact_id,
				v_lbomsl.LpuBuildingOffice_id,
				v_lbomsl.LpuBuildingOfficeMedStaffLink_id,
				
				convert(varchar(10), v_lbomsl.LpuBuildingOfficeMedStaffLink_begDate, 104) as LpuBuildingOfficeMedStaffLink_begDate,
				convert(varchar(10), v_lbomsl.LpuBuildingOfficeMedStaffLink_endDate, 104) as LpuBuildingOfficeMedStaffLink_endDate,
				v_p.name as Post_Name,
				LTRIM(RTRIM(v_msf.Person_Fio)) as Person_Fio,
				v_msf.Person_SurName,
				(LEFT(v_msf.Person_FirName, 1) + '.' + LEFT(v_msf.Person_SecName, 1) + '.') as Person_Initials,
				v_lbo.LpuBuildingOffice_Number			
			from
				v_LpuBuildingOfficeMedStaffLink v_lbomsl with (nolock)
				inner join v_MedStaffFact v_msf with (nolock) on v_msf.MedStaffFact_id = v_lbomsl.MedStaffFact_id
				inner join v_LpuBuildingOffice v_lbo with (nolock) on v_lbo.LpuBuildingOffice_id = v_lbomsl.LpuBuildingOffice_id			
				left join persis.Post as v_p with (nolock) on v_p.id = v_msf.Post_id					
			where
				" . implode(' and ', $filterList) . "
			order by
				v_lbomsl.LpuBuildingOfficeMedStaffLink_begDate
		";

		$response = $this->queryResult($query, $queryParams);

		if ( is_array($response) && count($response) > 0  && is_array($response) && count($response) > 0 ) {
			$LpuBuildingOfficeMedStaffLinks = array();
			$LpuBuildingOfficeVizitTimeData = array();

			foreach ( $response as $row ) {
				$LpuBuildingOfficeMedStaffLinks[] = $row['LpuBuildingOfficeMedStaffLink_id'];
			}

			if ( count($LpuBuildingOfficeMedStaffLinks) > 0 ) {


				// mondayDate и sundayDate показывают начало дня т.е. (0ч 00мин)
				$LpuBuildingOfficeVizitTimeList = $this->queryResult("

					select
						v_lbovt.LpuBuildingOfficeVizitTime_id,
						v_lbovt.LpuBuildingOfficeMedStaffLink_id, 
						v_lbovt.CalendarWeek_id, 
						convert(varchar(5), v_lbovt.LpuBuildingOfficeVizitTime_begDate, 108) as LpuBuildingOfficeVizitTime_begDate, 
						convert(varchar(5), v_lbovt.LpuBuildingOfficeVizitTime_endDate, 108) as LpuBuildingOfficeVizitTime_endDate
					from 
						v_LpuBuildingOfficeVizitTime as v_lbovt with (nolock)
					where
						v_lbovt.LpuBuildingOfficeMedStaffLink_id in (" . implode(',', $LpuBuildingOfficeMedStaffLinks) . ")
					order by
						v_lbovt.LpuBuildingOfficeMedStaffLink_id
				");

				if ( is_array($LpuBuildingOfficeVizitTimeList) && count($LpuBuildingOfficeVizitTimeList) > 0 ) {
					foreach ( $LpuBuildingOfficeVizitTimeList as $row ) {
						$LpuBuildingOfficeVizitTimeData[$row['LpuBuildingOfficeMedStaffLink_id']][$row['CalendarWeek_id']] = array(
							'LpuBuildingOfficeVizitTime_id' => $row['LpuBuildingOfficeVizitTime_id'],
							'CalendarWeek_id' => $row['CalendarWeek_id'],
							'LpuBuildingOfficeVizitTime_begDate' => $row['LpuBuildingOfficeVizitTime_begDate'],
							'LpuBuildingOfficeVizitTime_endDate' => $row['LpuBuildingOfficeVizitTime_endDate'],
						);
					}
				}
			}

			foreach ( $response as $key => $row ) {
				if ( ! empty($LpuBuildingOfficeVizitTimeData[$row['LpuBuildingOfficeMedStaffLink_id']]) ) {
					$response[$key]['LpuBuildingOfficeVizitTimeData'] = $LpuBuildingOfficeVizitTimeData[$row['LpuBuildingOfficeMedStaffLink_id']];
				}
			}
		}



		return $response;
	}



	/**
	 * Сохранение формы «Выбор кабинета»
	 *
	 * Форма предназначена для изменения выбранной на форме «Расписание работы врачей» связи Кабинет – Место работы,
	 * открывается по нажатию на номер кабинета в области данных формы «Расписание работы врачей».
	 *
	 * одна строка (врач, кабинет, время приёма по дням...) - это одна связь (в описанном условии именно эта связь
	 * называется существующей). И несколько не может быть найдено
	 *
	 * @param $data
	 * @return array|bool|false
	 */
	public function saveChoiceLpuBuildingOffice($data){

		try {

			$response = FALSE;

			$this->beginTransaction();


			$LpuBuildingOfficeMedStaffLink_begDate = DateTime::createFromFormat('Y-m-d', $data['LpuBuildingOfficeMedStaffLink_begDate']);
			$LpuBuildingOfficeMedStaffLink_endDate = (!empty($data['LpuBuildingOfficeMedStaffLink_endDate']) ? DateTime::createFromFormat('Y-m-d', $data['LpuBuildingOfficeMedStaffLink_endDate']) : null);

			$action = (empty($data['LpuBuildingOfficeMedStaffLink_id']) ? 'ins' : 'upd');

			$decodeLpuBuildingOfficeVizitTimeData = array();
			if ( ! empty($data['LpuBuildingOfficeVizitTimeData']) ) {
				$decodeLpuBuildingOfficeVizitTimeData = json_decode($data['LpuBuildingOfficeVizitTimeData'], true);
			}


			// Добавить проверку на "свою МО"
			$resultCheckOwnLpu = $this->_checkOwnLpu($data['session']['lpu_id'], $data['LpuBuildingOffice_id']);
			if($resultCheckOwnLpu['result'] == 2){
				throw new Exception('Ошибка при получении данных кабинета');
			} else if($resultCheckOwnLpu['result'] == 3){
				throw new Exception('Запрещено менять связи мест работы и кабинетов в чужой МО');
			}




			// v_lbomsl.LpuBuildingOfficeMedStaffLink_id != ISNULL(:LpuBuildingOfficeMedStaffLink_id, 0) AND (сказали убрать, я убрал)
			// Проверка на изменение кабинета (v_LpuBuildingOffice) у места работы (v_MedStaffFact)
			$crossDates_LpuBuildingOfficeMedStaffLink = $this->getFirstRowFromQuery("
				SELECT TOP 1
					v_lbomsl.LpuBuildingOfficeMedStaffLink_id,
					v_lbomsl.MedStaffFact_id,
					v_lbomsl.LpuBuildingOffice_id,
					v_lbo.LpuBuildingOffice_Number,
					v_lbo.LpuBuildingOffice_Name,
					v_lbomsl.LpuBuildingOfficeMedStaffLink_begDate,
					v_lbomsl.LpuBuildingOfficeMedStaffLink_endDate
				FROM 
					v_LpuBuildingOfficeMedStaffLink v_lbomsl WITH (nolock)
					INNER JOIN v_MedStaffFact v_msf WITH (nolock) ON v_msf.MedStaffFact_id = v_lbomsl.MedStaffFact_id
					INNER JOIN v_LpuBuildingOffice v_lbo WITH (nolock) ON v_lbo.LpuBuildingOffice_id = v_lbomsl.LpuBuildingOffice_id
				WHERE 
					v_msf.Lpu_id = :Lpu_id AND
					v_msf.MedStaffFact_id = :MedStaffFact_id AND
					(:LpuBuildingOfficeMedStaffLink_endDate IS NULL OR v_lbomsl.LpuBuildingOfficeMedStaffLink_begDate <= :LpuBuildingOfficeMedStaffLink_endDate) AND
				    (v_lbomsl.LpuBuildingOfficeMedStaffLink_endDate IS NULL OR :LpuBuildingOfficeMedStaffLink_begDate <= v_lbomsl.LpuBuildingOfficeMedStaffLink_endDate)
			", [
					'Lpu_id' => $data['Lpu_id'],
					'MedStaffFact_id' => $data['MedStaffFact_id'],
					// 'LpuBuildingOfficeMedStaffLink_id' => $data['LpuBuildingOfficeMedStaffLink_id'],
					'LpuBuildingOfficeMedStaffLink_begDate' => $data['LpuBuildingOfficeMedStaffLink_begDate'],
					'LpuBuildingOfficeMedStaffLink_endDate' => $data['LpuBuildingOfficeMedStaffLink_endDate'],
				]
			, true);

			if ( $crossDates_LpuBuildingOfficeMedStaffLink === false ) {
				throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');
			}

			// ---------------------------------------------------------------------------------------------
			// Если добавляется связь кабинет 2 – место работы 1, а для этого места работы 1 в тот же период времени
			// (учитывать даты периода действия и время периода приема с учетом дня недели) уже существует связь
			// кабинет 1 – место работы 1, то
			if(count($crossDates_LpuBuildingOfficeMedStaffLink) > 0){

				// ---------------------------------------------------------------------------------------------
				// Если дата начала добавляемой связи меньше или равна дате начала существующей связи, то
				// открывается сообщение: «Дата начала приёма врача в кабинете <номер кабинета добавляемой связи>
				// должна быть больше даты начала приёма в кабинете <номер кабинета существующей связи>.
				// Кнопка «ОК». При нажатии на кнопку сообщение закрывается.
				if($data['LpuBuildingOfficeMedStaffLink_begDate'] <= $crossDates_LpuBuildingOfficeMedStaffLink['LpuBuildingOfficeMedStaffLink_begDate']->format('Y-m-d')){
					throw new Exception('Дата начала приёма врача в кабинете <'. $data['LpuBuildingOffice_Number'] .'> должна быть больше даты начала приёма в кабинете <'.($crossDates_LpuBuildingOfficeMedStaffLink['LpuBuildingOffice_Number'].'. '.$crossDates_LpuBuildingOfficeMedStaffLink['LpuBuildingOffice_Name']).'>');
				}
				// ---------------------------------------------------------------------------------------------

				// ---------------------------------------------------------------------------------------------
				// Если дата начала добавляемой связи больше даты начала существующей, то открывается сообщение
				// «Врач <ФИО врача> в указанный период времени осуществляет прием в кабинете <номер кабинета>.
				// Изменить номер кабинета?» Кнопки «ОК»/«Отмена».
				// При нажатии на кнопку «ОК» создаётся новая связь кабинет 2 – место работы 1 иначе связь 1 не создаётся.
				if($data['LpuBuildingOfficeMedStaffLink_begDate'] > $crossDates_LpuBuildingOfficeMedStaffLink['LpuBuildingOfficeMedStaffLink_begDate']->format('Y-m-d')){

					// Спрашиваем нужно ли менять номер кабинета
					if(empty($data['checkDatesToChangeOfficeNumber'])) {
						throw new Exception('Врач <'. $data['Person_Fio'] .'> в указанный период времени осуществляет прием в кабинете <'.($crossDates_LpuBuildingOfficeMedStaffLink['LpuBuildingOffice_Number'].'. '.$crossDates_LpuBuildingOfficeMedStaffLink['LpuBuildingOffice_Name']).'>. Изменить номер кабинета?', 1);
					}

					// Пользователь дал согласие на изменение номера кабинета
					else if($data['checkDatesToChangeOfficeNumber'] == 1) {

						// Создаем новую связь кабинет 2 – место работы 1, при этом:

						// ---------------------------------------------------------------------------------------------
						// Для существующей связи кабинет 1 – место работы 1 автоматически устанавливается дата
						// окончания периода, равная дате начала периода новой связи минус один день;
						$query = "
							declare
								@LpuBuildingOfficeMedStaffLink_id bigint = :LpuBuildingOfficeMedStaffLink_id,
								@LpuBuildingOfficeMedStaffLink_endDate datetime = dateadd(DAY, -1, cast(:LpuBuildingOfficeMedStaffLink_endDate as date)),
								@Error_Code bigint,
								@Error_Message varchar(4000);

							exec p_LpuBuildingOfficeMedStaffLink_upd
								@LpuBuildingOfficeMedStaffLink_id = @LpuBuildingOfficeMedStaffLink_id output,
								@LpuBuildingOffice_id = :LpuBuildingOffice_id,
								@MedStaffFact_id = :MedStaffFact_id,
								@LpuBuildingOfficeMedStaffLink_begDate = :LpuBuildingOfficeMedStaffLink_begDate,
								@LpuBuildingOfficeMedStaffLink_endDate = @LpuBuildingOfficeMedStaffLink_endDate,
								@pmUser_id = :pmUser_id,
								@Error_Code = @Error_Code output,
								@Error_Message = @Error_Message output;

							select @LpuBuildingOfficeMedStaffLink_id as LpuBuildingOfficeMedStaffLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
						";

						$queryData = array(
							'LpuBuildingOfficeMedStaffLink_id' => $crossDates_LpuBuildingOfficeMedStaffLink['LpuBuildingOfficeMedStaffLink_id'],
							'LpuBuildingOffice_id' => $crossDates_LpuBuildingOfficeMedStaffLink['LpuBuildingOffice_id'],
							'MedStaffFact_id' => $crossDates_LpuBuildingOfficeMedStaffLink['MedStaffFact_id'],
							'LpuBuildingOfficeMedStaffLink_begDate' => ($crossDates_LpuBuildingOfficeMedStaffLink['LpuBuildingOfficeMedStaffLink_begDate']->format('Y-m-d')),
							'LpuBuildingOfficeMedStaffLink_endDate' => $data['LpuBuildingOfficeMedStaffLink_begDate'],
							'pmUser_id' => $data['pmUser_id'],
						);

						$response = $this->queryResult($query, $queryData);

						if ( ! empty($response[0]['Error_Msg']) ) {
							throw new Exception($response[0]['Error_Msg']);
						}

						// Создаем новую связь кабинет 2 – место работы 1
						$query = "
							declare
								@LpuBuildingOfficeMedStaffLink_id bigint = :LpuBuildingOfficeMedStaffLink_id,
								@Error_Code bigint,
								@Error_Message varchar(4000);
			
							exec p_LpuBuildingOfficeMedStaffLink_ins
								@LpuBuildingOfficeMedStaffLink_id = @LpuBuildingOfficeMedStaffLink_id output,
								@LpuBuildingOffice_id = :LpuBuildingOffice_id,
								@MedStaffFact_id = :MedStaffFact_id,
								@LpuBuildingOfficeMedStaffLink_begDate = :LpuBuildingOfficeMedStaffLink_begDate,
								@LpuBuildingOfficeMedStaffLink_endDate = :LpuBuildingOfficeMedStaffLink_endDate,
								@pmUser_id = :pmUser_id,
								@Error_Code = @Error_Code output,
								@Error_Message = @Error_Message output;
			
							select @LpuBuildingOfficeMedStaffLink_id as LpuBuildingOfficeMedStaffLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
						";

						$queryData = array(
							'LpuBuildingOfficeMedStaffLink_id' => NULL,
							'LpuBuildingOffice_id' => $data['LpuBuildingOffice_id'],
							'MedStaffFact_id' => $data['MedStaffFact_id'],
							'LpuBuildingOfficeMedStaffLink_begDate' => $data['LpuBuildingOfficeMedStaffLink_begDate'],
							'LpuBuildingOfficeMedStaffLink_endDate' => $data['LpuBuildingOfficeMedStaffLink_endDate'],
							'pmUser_id' => $data['pmUser_id']
						);


						$response = $this->queryResult($query, $queryData);

						if ( ! empty($response[0]['Error_Msg']) ) {
							throw new Exception($response[0]['Error_Msg']);
						}
						// ---------------------------------------------------------------------------------------------

						// ---------------------------------------------------------------------------------------------
						// Если у связи кабинет 2 – место работы 1 указана дата окончания, при этом дата окончания меньше
						// даты окончания существующей связи, то создаётся ещё одна связь 1 кабинет 1 – место работы 1  (копируются параметры существующей связи: кабинет, место работы, время приема по дням недели) с датой начала равной дате окончания добавляемой связи плюс 1 день;
						if(
							! empty($data['LpuBuildingOfficeMedStaffLink_endDate']) AND
							($data['LpuBuildingOfficeMedStaffLink_endDate'] < ($crossDates_LpuBuildingOfficeMedStaffLink['LpuBuildingOfficeMedStaffLink_endDate']->format('Y-m-d')))
						){
							// Создаем еще одну связь 1 кабинет 1 – место работы 1 (копируются параметры существующей
							// связи: кабинет, место работы, время приема по дням недели) с датой начала равной
							// дате окончания добавляемой связи плюс 1 день;
							$query = "
								declare
									@LpuBuildingOfficeMedStaffLink_id bigint = :LpuBuildingOfficeMedStaffLink_id,
									@LpuBuildingOfficeMedStaffLink_begDate datetime = dateadd(DAY, 1, cast(:LpuBuildingOfficeMedStaffLink_begDate as date)),
									@Error_Code bigint,
									@Error_Message varchar(4000);
				
								exec p_LpuBuildingOfficeMedStaffLink_ins
									@LpuBuildingOfficeMedStaffLink_id = @LpuBuildingOfficeMedStaffLink_id output,
									@LpuBuildingOffice_id = :LpuBuildingOffice_id,
									@MedStaffFact_id = :MedStaffFact_id,
									@LpuBuildingOfficeMedStaffLink_begDate = @LpuBuildingOfficeMedStaffLink_begDate,
									@LpuBuildingOfficeMedStaffLink_endDate = :LpuBuildingOfficeMedStaffLink_endDate,
									@pmUser_id = :pmUser_id,
									@Error_Code = @Error_Code output,
									@Error_Message = @Error_Message output;
				
								select @LpuBuildingOfficeMedStaffLink_id as LpuBuildingOfficeMedStaffLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
							";

							$response = $this->queryResult($query, array(
								'LpuBuildingOfficeMedStaffLink_id' => NULL,
								'LpuBuildingOffice_id' => $crossDates_LpuBuildingOfficeMedStaffLink['LpuBuildingOffice_id'],
								'MedStaffFact_id' => $crossDates_LpuBuildingOfficeMedStaffLink['MedStaffFact_id'],
								'LpuBuildingOfficeMedStaffLink_begDate' => $data['LpuBuildingOfficeMedStaffLink_endDate'],
								'LpuBuildingOfficeMedStaffLink_endDate' => ($crossDates_LpuBuildingOfficeMedStaffLink['LpuBuildingOfficeMedStaffLink_endDate']->format('Y-m-d')),
								'pmUser_id' => $data['pmUser_id']
							));

							if ( !empty($response[0]['Error_Msg']) ) {
								throw new Exception($response[0]['Error_Msg']);
							}

							$LpuBuildingOfficeMedStaffLink_id = $response[0]['LpuBuildingOfficeMedStaffLink_id'];


							$CurrentLpuBuildingOfficeVizitTimeData = array();

							$resp = $this->queryResult("
								SELECT
									 LpuBuildingOfficeVizitTime_begDate, 
									 LpuBuildingOfficeVizitTime_endDate, 
									 CalendarWeek_id
								FROM 
									v_LpuBuildingOfficeVizitTime with (nolock)
								WHERE 
									LpuBuildingOfficeMedStaffLink_id = :LpuBuildingOfficeMedStaffLink_id
							", array(
								'LpuBuildingOfficeMedStaffLink_id' => $crossDates_LpuBuildingOfficeMedStaffLink['LpuBuildingOfficeMedStaffLink_id']
							));

							if ($resp === false) {
								throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');
							}

							foreach($resp as $row) {
								$CurrentLpuBuildingOfficeVizitTimeData[$row['CalendarWeek_id']] = $row;
							}

							foreach($CurrentLpuBuildingOfficeVizitTimeData as $row) {
								$LpuBuildingOfficeVizitTime_id = NULL;

								$response = $this->queryResult("
									declare
										@LpuBuildingOfficeVizitTime_id bigint = :LpuBuildingOfficeVizitTime_id,
										@Error_Code bigint,
										@Error_Message varchar(4000);
				
									exec p_LpuBuildingOfficeVizitTime_ins
										@LpuBuildingOfficeVizitTime_id = @LpuBuildingOfficeVizitTime_id output,
										@LpuBuildingOfficeMedStaffLink_id = :LpuBuildingOfficeMedStaffLink_id,
										@CalendarWeek_id = :CalendarWeek_id,
										@LpuBuildingOfficeVizitTime_begDate = :LpuBuildingOfficeVizitTime_begDate,
										@LpuBuildingOfficeVizitTime_endDate = :LpuBuildingOfficeVizitTime_endDate,
										@pmUser_id = :pmUser_id,
										@Error_Code = @Error_Code output,
										@Error_Message = @Error_Message output;
				
									select @LpuBuildingOfficeVizitTime_id as LpuBuildingOfficeVizitTime_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
								", array(
									'LpuBuildingOfficeVizitTime_id' => $LpuBuildingOfficeVizitTime_id,
									'LpuBuildingOfficeMedStaffLink_id' => $LpuBuildingOfficeMedStaffLink_id,
									'CalendarWeek_id' => $row['CalendarWeek_id'],
									'LpuBuildingOfficeVizitTime_begDate' => $row['LpuBuildingOfficeVizitTime_begDate'],
									'LpuBuildingOfficeVizitTime_endDate' => $row['LpuBuildingOfficeVizitTime_endDate'],
									'pmUser_id' => $data['pmUser_id'],
								));

								if ($response === false) {
									throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');

								} else if ( ! empty($response[0]['Error_Msg'])) {
									throw new Exception($response[0]['Error_Msg']);
								}
							}
						}
						// ---------------------------------------------------------------------------------------------
					}
				}
				// ---------------------------------------------------------------------------------------------
			}
			// ---------------------------------------------------------------------------------------------

			$this->commitTransaction() ;
		}
		catch (Exception $e){

			$this->rollbackTransaction();

			$code = $e->getCode();

			if ( ! empty($code) ) {
				$response = array(array('Alert_Msg' => $e->getMessage(), 'Error_Code' => $code));
			}
			else {
				$response = array(array('Error_Msg' => $e->getMessage()));
			}
		}

		return $response;
	}

	/**
	 * Объединяет дату (Y-m-d) и время (H:i) в формат для SQL datetime Y-m-d H:i:00.000
	 *
	 * @param $date
	 * @param $time
	 * @param string $format
	 * @return false|string
	 */
	public static function _concatDateAndTime($date, $time, $format = 'Y-m-d H:i:00.000'){
		if(empty($date) || empty($time)){
			return false;
		}

		$d = date('Y-m-d', strtotime($date));
		$t = date('H:i', strtotime($time));

		return date($format, strtotime($d.' '.$t));
	}


	/**
	 * @param $LpuBuildingOfficeMedStaffLink_id
	 * @param $CalendarWeek_id
	 * @param $LpuBuildingOfficeVizitTime_begDate
	 * @param $LpuBuildingOfficeVizitTime_endDate
	 * @param $curDate
	 * @param $pmUser_id
	 * @return array|false
	 */
	public function _addNewVizitTime($LpuBuildingOfficeMedStaffLink_id, $CalendarWeek_id, $LpuBuildingOfficeVizitTime_begDate, $LpuBuildingOfficeVizitTime_endDate, $curDate, $pmUser_id){
		$LpuBuildingOfficeVizitTime_id = NULL;

		$response = $this->queryResult("
			declare
				@LpuBuildingOfficeVizitTime_id bigint = :LpuBuildingOfficeVizitTime_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);

			exec p_LpuBuildingOfficeVizitTime_ins
				@LpuBuildingOfficeVizitTime_id = @LpuBuildingOfficeVizitTime_id output,
				@LpuBuildingOfficeMedStaffLink_id = :LpuBuildingOfficeMedStaffLink_id,
				@CalendarWeek_id = :CalendarWeek_id,
				@LpuBuildingOfficeVizitTime_begDate = :LpuBuildingOfficeVizitTime_begDate,
				@LpuBuildingOfficeVizitTime_endDate = :LpuBuildingOfficeVizitTime_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @LpuBuildingOfficeVizitTime_id as LpuBuildingOfficeVizitTime_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		", array(
			'LpuBuildingOfficeVizitTime_id' => $LpuBuildingOfficeVizitTime_id,
			'LpuBuildingOfficeMedStaffLink_id' => $LpuBuildingOfficeMedStaffLink_id,
			'CalendarWeek_id' => $CalendarWeek_id,
			'LpuBuildingOfficeVizitTime_begDate' => self::_concatDateAndTime($curDate, $LpuBuildingOfficeVizitTime_begDate),
			'LpuBuildingOfficeVizitTime_endDate' => self::_concatDateAndTime($curDate, $LpuBuildingOfficeVizitTime_endDate),
			'pmUser_id' => $pmUser_id,
		));

		return $response;
	}

	/**
	 * Сохранение формы «Выбор времени приёма»
	 *
	 * Форма предназначена для изменения времени приёма врача в соответствующем кабинете в выбранный день недели,
	 * открывается по нажатию на время приёма в области данных формы «Расписание работы врачей».
	 *
	 * $data:
	 * 	CalendarWeek_id, [required], Выбранный день недели
	 * 	LpuBuildingOfficeVizitTime_id, [может быть NULL], Идентификатор времени приема
	 * 	LpuBuildingOfficeMedStaffLink_id, [required], Идентификатор связки кабинета с местом работы
	 * 	curDate, [required], Дата выбранного дня недели
	 * 	LpuBuildingOfficeVizitTime_begDate, [required], Время начала
	 * 	LpuBuildingOfficeVizitTime_endDate, [required], Время окончания
	 * 	LpuBuildingOfficeVizitTime_period, [required], Период (Один день, Весь период работы врача в кабинете)
	 * 	pmUser_id
	 *
	 * @param $data
	 * @return array|bool|false
	 */
	public function saveChoiceVizitTime($data){

		try {
			$response = FALSE;

			// Если выбрано значение «Один день» (LpuBuildingOfficeVizitTime_period), то изменяется
			// время приёма (LpuBuildingOfficeVizitTime) только на один день в выбранный день недели (CalendarWeek_id)
			if($data['LpuBuildingOfficeVizitTime_period'] == 1){

				// Если на текущую дату время работы установлено, то мы просто изменяем время
				if( ! empty($data['LpuBuildingOfficeVizitTime_id'])){

					$response = $this->queryResult("
						declare
							@LpuBuildingOfficeVizitTime_id bigint = :LpuBuildingOfficeVizitTime_id,
							@Error_Code bigint,
							@Error_Message varchar(4000);
	
						exec p_LpuBuildingOfficeVizitTime_upd
							@LpuBuildingOfficeVizitTime_id = @LpuBuildingOfficeVizitTime_id output,
							@LpuBuildingOfficeMedStaffLink_id = :LpuBuildingOfficeMedStaffLink_id,
							@CalendarWeek_id = :CalendarWeek_id,
							@LpuBuildingOfficeVizitTime_begDate = :LpuBuildingOfficeVizitTime_begDate,
							@LpuBuildingOfficeVizitTime_endDate = :LpuBuildingOfficeVizitTime_endDate,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code output,
							@Error_Message = @Error_Message output;
	
						select @LpuBuildingOfficeVizitTime_id as LpuBuildingOfficeVizitTime_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
					", array(
						'LpuBuildingOfficeVizitTime_id' => $data['LpuBuildingOfficeVizitTime_id'],
						'LpuBuildingOfficeMedStaffLink_id' => $data['LpuBuildingOfficeMedStaffLink_id'],
						'CalendarWeek_id' => $data['CalendarWeek_id'],
						'LpuBuildingOfficeVizitTime_begDate' => self::_concatDateAndTime($data['curDate'], $data['LpuBuildingOfficeVizitTime_begDate']),
						'LpuBuildingOfficeVizitTime_endDate' => self::_concatDateAndTime($data['curDate'], $data['LpuBuildingOfficeVizitTime_endDate']),
						'pmUser_id' => $data['pmUser_id'],
					));

					if ( $response === false ) {
						throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');
					}
					else if ( !empty($response[0]['Error_Msg']) ) {
						throw new Exception($response[0]['Error_Msg']);
					}
				}

				// Если на текущую дату время работы НЕ установлено, то мы добавляем строку в таблицу
				// LpuBuildingOfficeVizitTime с выбранным временем и текущей датой (датой выбранного дня)
				else if(empty($data['LpuBuildingOfficeVizitTime_id'])){

					$response = $this->_addNewVizitTime(
						$data['LpuBuildingOfficeMedStaffLink_id'],
						$data['CalendarWeek_id'],
						$data['LpuBuildingOfficeVizitTime_begDate'],
						$data['LpuBuildingOfficeVizitTime_endDate'],
						$data['curDate'],
						$data['pmUser_id']
					);

					if ($response === false) {
						throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');

					} else if ( ! empty($response[0]['Error_Msg'])) {
						throw new Exception($response[0]['Error_Msg']);
					}
				}
			}

			// Если выбрано значение «Весь период работы врача в кабинете», то изменяется время приёма в выбранный день
			// недели на весь период действия связи Кабинет – Место работы.
			else if($data['LpuBuildingOfficeVizitTime_period'] == 2){

				if(empty($data['LpuBuildingOfficeVizitTime_id'])){
					$response = $this->_addNewVizitTime(
						$data['LpuBuildingOfficeMedStaffLink_id'],
						$data['CalendarWeek_id'],
						$data['LpuBuildingOfficeVizitTime_begDate'],
						$data['LpuBuildingOfficeVizitTime_endDate'],
						$data['curDate'],
						$data['pmUser_id']
					);

					if ($response === false) {
						throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');

					} else if ( ! empty($response[0]['Error_Msg'])) {
						throw new Exception($response[0]['Error_Msg']);
					}
				}

				$resp = $this->queryResult("
					SELECT
						LpuBuildingOfficeVizitTime_id,
						LpuBuildingOfficeVizitTime_begDate,
						LpuBuildingOfficeVizitTime_endDate
					FROM 
						v_LpuBuildingOfficeVizitTime with (nolock)
					WHERE 
						LpuBuildingOfficeMedStaffLink_id = :LpuBuildingOfficeMedStaffLink_id AND
						CalendarWeek_id = :CalendarWeek_id
				", array(
					'LpuBuildingOfficeMedStaffLink_id' => $data['LpuBuildingOfficeMedStaffLink_id'],
					'CalendarWeek_id' => $data['CalendarWeek_id'],
				));

				if ( $resp === false ) {
					throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');
				}

				if(is_array($resp)){

					foreach($resp as $row) {

						$response = $this->queryResult("
							declare
								@LpuBuildingOfficeVizitTime_id bigint = :LpuBuildingOfficeVizitTime_id,
								@Error_Code bigint,
								@Error_Message varchar(4000);

							exec p_LpuBuildingOfficeVizitTime_upd
								@LpuBuildingOfficeVizitTime_id = @LpuBuildingOfficeVizitTime_id output,
								@LpuBuildingOfficeMedStaffLink_id = :LpuBuildingOfficeMedStaffLink_id,
								@CalendarWeek_id = :CalendarWeek_id,
								@LpuBuildingOfficeVizitTime_begDate = :LpuBuildingOfficeVizitTime_begDate,
								@LpuBuildingOfficeVizitTime_endDate = :LpuBuildingOfficeVizitTime_endDate,
								@pmUser_id = :pmUser_id,
								@Error_Code = @Error_Code output,
								@Error_Message = @Error_Message output;

							select @LpuBuildingOfficeVizitTime_id as LpuBuildingOfficeVizitTime_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
						", array(
							'LpuBuildingOfficeVizitTime_id' => $row['LpuBuildingOfficeVizitTime_id'],
							'LpuBuildingOfficeMedStaffLink_id' => $data['LpuBuildingOfficeMedStaffLink_id'],
							'CalendarWeek_id' => $data['CalendarWeek_id'],
							'LpuBuildingOfficeVizitTime_begDate' => self::_concatDateAndTime($row['LpuBuildingOfficeVizitTime_begDate']->format('Y-m-d'), $data['LpuBuildingOfficeVizitTime_begDate']),
							'LpuBuildingOfficeVizitTime_endDate' => self::_concatDateAndTime($row['LpuBuildingOfficeVizitTime_endDate']->format('Y-m-d'), $data['LpuBuildingOfficeVizitTime_endDate']),
							'pmUser_id' => $data['pmUser_id'],
						));

						if ( $response === false ) {
							throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');
						}
						else if ( ! empty($response[0]['Error_Msg']) ) {
							throw new Exception($response[0]['Error_Msg']);
						}
					}

				}

			}

		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();

			$code = $e->getCode();

			if ( !empty($code) ) {
				$response = array(array('Alert_Msg' => $e->getMessage(), 'Error_Code' => $code));
			}
			else {
				$response = array(array('Error_Msg' => $e->getMessage()));
			}
		}

		return $response;
	}



	/**
	 * Проверка на "свою МО"
	 *
	 * @param $Lpu_id
	 * @return array
	 * @throws Exception
	 */
	private function _checkOwnLpu($Lpu_id, $LpuBuildingOffice_id){

		$data['Lpu_id'] = $Lpu_id;
		$data['LpuBuildingOffice_id'] = $LpuBuildingOffice_id;

		$LpuBuildingOfficeData = $this->getFirstRowFromQuery("
				select top 1
					 Lpu_id
					,LpuBuildingOffice_Number
					,LpuBuildingOffice_begDate
					,LpuBuildingOffice_endDate
				from v_LpuBuildingOffice with (nolock)
				where LpuBuildingOffice_id = :LpuBuildingOffice_id
			", $data, true);



		if ( $LpuBuildingOfficeData === false || is_null($LpuBuildingOfficeData) ) {

			// Ошибка при получении данных кабинета
			return [
				'result' => 2,
				'data' => false
			];
		}

		if ( $LpuBuildingOfficeData['Lpu_id'] != $data['Lpu_id'] ) {

			// Запрещено менять связи мест работы и кабинетов в чужой МО
			return [
				'result' => 3,
				'data' => $LpuBuildingOfficeData
			];
		}

		// Проверка пройдена успешно
		return [
			'result' => 1,
			'data' => $LpuBuildingOfficeData
		];
	}


	/**
	 * Проверка периода работы врача при сохранении кабинета
	 *
	 * Если период действия связи «кабинет – место работы» не полностью включается в период действия места работы, то выдается сообщение об ошибке
	 * «Период работы врача <ФИО врача> с <Дата начала периода работы  врача> по <Дата окончания периода работы врача>. Период действия связи кабинета с
	 * врачом с <Дата начала действия периода связи кабинет – место работы> по <дата окончания периода действия периода связи кабинет – место работы>».
	 * Кнопка «ОК» при нажатии на кнопку, окно закрывается, форма остается открытой, фокус устанавливается в поле «Дата начала периода действия».
	 *
	 * @param $MedStaffFact_id
	 * @param $LpuBuildingOfficeMedStaffLink_begDate
	 * @param $LpuBuildingOfficeMedStaffLink_endDate
	 * @return array
	 */
	private function _checkPeriodRabotyVracha($MedStaffFact_id, $LpuBuildingOfficeMedStaffLink_begDate, $LpuBuildingOfficeMedStaffLink_endDate){

		$MSFWorkDates = $this->getFirstRowFromQuery("
				select top 1
					 convert(varchar(10), WorkData_begDate, 104) as WorkData_begDate
					,convert(varchar(10), WorkData_endDate, 104) as WorkData_endDate
					,Person_Fio as MedPersonal_FIO
				from v_MedStaffFact with (nolock)
				where MedStaffFact_id = :MedStaffFact_id
			", ['MedStaffFact_id' => $MedStaffFact_id]);

		if ( $MSFWorkDates === false ) {
			return [
				'result' => 2,
				'data' => false
			];
		}

		if (
			$MSFWorkDates['WorkData_begDate'] > $LpuBuildingOfficeMedStaffLink_begDate
			|| (
				! empty($MSFWorkDates['WorkData_endDate'])
				&& (
					empty($LpuBuildingOfficeMedStaffLink_endDate)
					|| $MSFWorkDates['WorkData_endDate'] < $LpuBuildingOfficeMedStaffLink_endDate
				)
			)
		) {
			return [
				'result' => 3,
				'data' => $MSFWorkDates
			];
		}

		return [
			'result' => 1,
			'data' => $MSFWorkDates
		];
	}


	/**
	 * Проверка периода дат при сохранении кабинета
	 *
	 * Если добавляют связь «кабинет1 – место работы2», при этом для кабинета1 в этом периоде времени (учитывать даты периода действия и время периода приема
	 * с учетом дня недели) уже есть связи (одна или более действующих в указанном периоде) «кабинет1 – место работы», то выдается предупреждение «В указанный
	 * период времени в кабинете <номер кабинета> ведет прием врач <ФИО врача 1>». Кнопки «ОК» - при нажатии на копку связь сохраняется, форма закрывается.
	 * Кнопка «Отмена» - при нажатии на кнопку связь не сохраняется, форма остается открытой.
	 *
	 * @param $LpuBuildingOffice_id
	 * @param $LpuBuildingOfficeMedStaffLink_id
	 * @param $LpuBuildingOfficeMedStaffLink_begDate
	 * @param $LpuBuildingOfficeMedStaffLink_endDate
	 * @return array
	 */
	private function _checkIgnoreMedStaffFactDoubles($LpuBuildingOffice_id, $LpuBuildingOfficeMedStaffLink_id, $LpuBuildingOfficeMedStaffLink_begDate, $LpuBuildingOfficeMedStaffLink_endDate){

		$resp = $this->queryResult("
				select
					 t3.LpuBuildingOffice_Number
					,t4.Person_Fio as MedPersonal_FIO
					,t1.CalendarWeek_id
					,convert(varchar(5), t1.LpuBuildingOfficeVizitTime_begDate, 108) as LpuBuildingOfficeVizitTime_begDate
					,convert(varchar(5), t1.LpuBuildingOfficeVizitTime_endDate, 108) as LpuBuildingOfficeVizitTime_endDate
				from v_LpuBuildingOfficeVizitTime t1 with (nolock)
					inner join v_LpuBuildingOfficeMedStaffLink t2 with (nolock) on t2.LpuBuildingOfficeMedStaffLink_id = t1.LpuBuildingOfficeMedStaffLink_id
					inner join v_LpuBuildingOffice t3 with (nolock) on t3.LpuBuildingOffice_id = t2.LpuBuildingOffice_id
					inner join v_MedStaffFact t4 with (nolock) on t4.MedStaffFact_id = t2.MedStaffFact_id
				where 
					t2.LpuBuildingOffice_id = :LpuBuildingOffice_id
					and (:LpuBuildingOfficeMedStaffLink_endDate is null or t2.LpuBuildingOfficeMedStaffLink_begDate <= :LpuBuildingOfficeMedStaffLink_endDate)
					and (t2.LpuBuildingOfficeMedStaffLink_endDate is null or :LpuBuildingOfficeMedStaffLink_begDate <= t2.LpuBuildingOfficeMedStaffLink_endDate)
					and t2.LpuBuildingOfficeMedStaffLink_id != ISNULL(:LpuBuildingOfficeMedStaffLink_id, 0)
				order by
					t2.MedStaffFact_id
			", [
				'LpuBuildingOffice_id' => $LpuBuildingOffice_id,
				'LpuBuildingOfficeMedStaffLink_id' => $LpuBuildingOfficeMedStaffLink_id,
				'LpuBuildingOfficeMedStaffLink_begDate' => $LpuBuildingOfficeMedStaffLink_begDate,
				'LpuBuildingOfficeMedStaffLink_endDate' => $LpuBuildingOfficeMedStaffLink_endDate,
			]
		);

		if ( $resp === false ) {
			return [
				'result' => 2,
				'data' => false
			];
		}

		$DoublesLpuBuildingOfficeVizitTimeData = array();

		foreach($resp as $row){
			$DoublesLpuBuildingOfficeVizitTimeData[$row['CalendarWeek_id']] = $row;
		}

		if ( count($DoublesLpuBuildingOfficeVizitTimeData) > 0) {
			return [
				'result' => 3,
				'data' => $DoublesLpuBuildingOfficeVizitTimeData
			];
		}

		return [
			'result' => 1,
			'data' => $DoublesLpuBuildingOfficeVizitTimeData
		];
	}

	/**
	 * Возвращает данные связки кабинета
	 * с местом работы на основании рабочего места или службы
	 */
	public function getCurrentOffice($data) {

		if (empty($data['MedStaffFact_id']) && empty($data['MedService_id'])) {
			return array(
				'success' => false,
				'Error_Msg' => 'Не указано место работы врача или служба'
			);
		}

		$filter = ""; $params = array();

		if (!empty($data['MedStaffFact_id'])) {
			$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
			$filter .= " and MedStaffFact_id = :MedStaffFact_id ";
		}

		if (!empty($data['MedService_id'])) {
			$params['MedService_id'] = $data['MedService_id'];
			$filter .= " and MedService_id = :MedService_id ";
		}

		$result = $this->queryResult("
		
			-- установим признак что первый день недели Понедельник
			SET DATEFIRST 1;

			declare @currentDate datetime = dbo.tzGetDate();

			-- возьмем текущий номер дня недели
			declare @currentCalendarWeek int = (select DATEPART(dw, @currentDate));
		
			select top 1
				lbomsl.LpuBuildingOffice_id,
				lbo.LpuBuildingOffice_Number,
				lbomsl.LpuBuildingOfficeMedStaffLink_id,
				lbomsl.MedStaffFact_id,
				lbomsl.MedService_id,
				cast(lbomsl.LpuBuildingOfficeMedStaffLink_begDate as date) as LpuBuildingOfficeMedStaffLink_begDate,
				cast(lbomsl.LpuBuildingOfficeMedStaffLink_endDate as date) as LpuBuildingOfficeMedStaffLink_endDate,
				(cast(dbo.tzGetDate() + 1 as date)) as nextBegDate,
				(cast(dbo.tzGetDate() - 1  as date)) as closeEndDate,
				cast(dbo.tzGetDate() as date) as currentDate,
				vizitTime.begTime,
				vizitTime.endTime
			from v_LpuBuildingOfficeMedStaffLink lbomsl (nolock)
			left join v_LpuBuildingOffice lbo (nolock) on lbo.LpuBuildingOffice_id = lbomsl.LpuBuildingOffice_id
			outer apply(
				select top 1
					CONVERT(varchar(5), vt.LpuBuildingOfficeVizitTime_begDate, 108) as begTime,
					CONVERT(varchar(5), vt.LpuBuildingOfficeVizitTime_endDate, 108) as endTime,
					vt.CalendarWeek_id
				from v_LpuBuildingOfficeVizitTime vt (nolock)
				where 
					vt.LpuBuildingOfficeMedStaffLink_id = lbomsl.LpuBuildingOfficeMedStaffLink_id
					and vt.CalendarWeek_id = @currentCalendarWeek
			) vizitTime
			where (1=1)
			and cast(lbomsl.LpuBuildingOfficeMedStaffLink_begDate as date) <= cast(dbo.tzGetDate() as date)
			and cast(isnull(lbomsl.LpuBuildingOfficeMedStaffLink_endDate, '2050-01-01 00:00:00') as date) >= cast(dbo.tzGetDate() as date)
			{$filter}
		", $params);

		return $result;
	}

	/**
	 * Сохраняет связь кабинета и рабочего места врача (из АРМА)
	 */
	public function changeCurrentOffice($data) {

		if (empty($data['MedStaffFact_id']) && empty($data['MedService_id'])) {
			return array(
				'success' => false,
				'Error_Msg' => 'Не указано место работы врача или служба'
			);
		}

		if (!empty($data['LpuBuildingOfficeVizitTime_begDate']) && !empty($data['LpuBuildingOfficeVizitTime_endDate'])) {
			if ($data['LpuBuildingOfficeVizitTime_begDate'] > $data['LpuBuildingOfficeVizitTime_endDate']) {
				return array(
					'success' => false,
					'Error_Msg' => 'Время начала приема больше времени окончания'
				);
			}
		}

		$currentOfficeResult = $this->getCurrentOffice($data);
		$canSave = true;

		// проверяем что с текущим местом работы уже связан кабинет
		if (!empty($currentOfficeResult[0]['LpuBuildingOffice_id'])) {

			$currentOffice = $currentOfficeResult[0];

			// когда даты начала и окончания в предудущей связи равны текущему дню
			// тогда просто обновляем эту связь
			if (
				!empty($currentOffice['LpuBuildingOfficeMedStaffLink_id'])
				&& $currentOffice['LpuBuildingOfficeMedStaffLink_begDate'] == $currentOffice['currentDate']
				&& $currentOffice['LpuBuildingOfficeMedStaffLink_endDate'] == $currentOffice['currentDate']
			) {

				$updateResult = $this->updateLpuBuildingOfficeMedStaffLinkData(array(
					'changedParams' => array(
						'LpuBuildingOffice_id' => $data['LpuBuildingOffice_id']
					),
					'pmUser_id' => $data['pmUser_id'],
					'LpuBuildingOfficeMedStaffLink_id' => $currentOffice['LpuBuildingOfficeMedStaffLink_id'],
					'LpuBuildingOfficeVizitTime_begDate' => !empty($data['LpuBuildingOfficeVizitTime_begDate']) ? $data['LpuBuildingOfficeVizitTime_begDate'] : null,
					'LpuBuildingOfficeVizitTime_endDate' => !empty($data['LpuBuildingOfficeVizitTime_endDate']) ? $data['LpuBuildingOfficeVizitTime_endDate'] : null
				));

					$canSave = false;
				$saveResult = array(
					'success' => true,
					'LpuBuildingOfficeMedStaffLink_id' => $currentOffice['LpuBuildingOfficeMedStaffLink_id']
				);

			} else {

				// иначе для существуеющей связи проставляем дату окончания
				$updateResult = $this->updateLpuBuildingOfficeMedStaffLinkData(array(
					'changedParams' => array(
						'LpuBuildingOfficeMedStaffLink_endDate' => $currentOffice['closeEndDate']
					),
					'pmUser_id' => $data['pmUser_id'],
					'LpuBuildingOfficeMedStaffLink_id' => $currentOffice['LpuBuildingOfficeMedStaffLink_id']
				));

				if (empty($currentOffice['LpuBuildingOfficeMedStaffLink_endDate'])
					|| $currentOffice['LpuBuildingOfficeMedStaffLink_endDate'] > $currentOffice['currentDate']
				) {
					// копируем и создаем эту связь заново, исключая сегодняшний день
					$cloneParams = $currentOffice;
					$cloneParams['LpuBuildingOfficeMedStaffLink_begDate'] = $currentOffice['nextBegDate'];
					$cloneParams['LpuBuildingOfficeMedStaffLink_id'] = null;
					$cloneParams['pmUser_id'] = $data['pmUser_id'];

					$saveCloneResult = $this->saveLpuBuildingOfficeMedStaffLink($cloneParams);
				}
			}
		}

		if ($canSave) {

			// иначе создаем новую связь кабинет-место работы
			$params = $data;
			$currDate = $this->getFirstResultFromQuery(" select cast(dbo.tzGetDate() as date) as dt ", array());

			$params['LpuBuildingOfficeMedStaffLink_begDate'] = $currDate;
			$params['LpuBuildingOfficeMedStaffLink_endDate'] = $currDate;

			if (!empty($data['LpuBuildingOfficeVizitTime_begDate'])) {
				$params['LpuBuildingOfficeVizitTime_begDate'] = $data['LpuBuildingOfficeVizitTime_begDate'];
			}

			if (!empty($data['LpuBuildingOfficeVizitTime_endDate'])) {
				$params['LpuBuildingOfficeVizitTime_endDate'] = $data['LpuBuildingOfficeVizitTime_endDate'];
			}

			$saveResult = $this->saveLpuBuildingOfficeMedStaffLink($params);
		}

		// отправим уведомление о смене кабинета
		if (!empty($data['LpuBuildingOffice_id'])) {

			if (
				(!empty($currentOfficeResult[0]['LpuBuildingOffice_id']) && $currentOfficeResult[0]['LpuBuildingOffice_id'] != $data['LpuBuildingOffice_id'])
				|| empty($currentOfficeResult[0]['LpuBuildingOffice_id'])
			){
				$office_num = $this->getFirstResultFromQuery("
					select LpuBuildingOffice_Number 
					from v_LpuBuildingOffice (nolock) 
					where LpuBuildingOffice_id = :LpuBuildingOffice_id
				", array('LpuBuildingOffice_id' => $data['LpuBuildingOffice_id']));

				if (!empty($office_num)) {
					$this->sendChangeOfficePortalNotification(array(
						'office_num' => $office_num,
						'MedStaffFact_id' => !empty($data['MedStaffFact_id']) ? $data['MedStaffFact_id'] : null
					));
				}
			}
		}

		return $saveResult;
	}

	/**
	 * Отправка сообщения о том что изменился кабинет приема
	 */
	function sendChangeOfficePortalNotification($data) {

		if (empty($data['MedStaffFact_id'])) {
			return null;
		}

		$this->load->model("UserPortalNotifications_model");
		$this->load->helper('Reg');

		$records = $this->queryResult("
			select 
				tt.Person_id,
				convert(varchar(10), tt.TimetableGraf_begTime, 104) + ' ' + convert(varchar(5), tt.TimetableGraf_begTime, 108) as time,
				msf.Person_Fio as Doctor_Fio,
				msf.Person_Fin as Doctor_Fin,
				lpu.Lpu_Nick,
				lsp.ProfileSpec_Name_Vin
			from v_TimetableGraf_lite tt (nolock)
			left join v_MedStaffFact msf (nolock) on tt.MedStaffFact_id = msf.MedStaffFact_id
			left join v_Lpu lpu (nolock) on lpu.Lpu_id = msf.Lpu_id
			left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = msf.LpuSectionProfile_id
			where (1=1) 
				and tt.MedStaffFact_id = :MedStaffFact_id
				and tt.TimetableGraf_begTime > dbo.tzGetDate()
				and tt.TimetableGraf_Day = :Day_id
				and tt.Person_id is not null
			", array(
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'Day_id' => TimeToDay(time())
		));

		foreach ($records as $tt) {
			// отправляем оповещения пользователям портала
			$this->UserPortalNotifications_model->send(
				array(
					'notify_object' => 'eq',
					'notify_action' => 'changeOffice',
					'Person_id' => $tt['Person_id'],
					'doctor_fio' => $tt['Doctor_Fio'],
					'doctor_fin' => $tt['Doctor_Fin'],
					'office_num' => $data['office_num'],
					'profile' => !empty($tt['ProfileSpec_Name_Vin']) ? $tt['ProfileSpec_Name_Vin'] : '',
					'Lpu_Nick' => !empty($tt['Lpu_Nick']) ? $tt['Lpu_Nick'] : '',
					'time' => !empty($tt['time'])? $tt['time'] : ''
				)
			);
		}
	}

	/**
	 * Обновление связи кабинета и места работы
	 */
	function updateLpuBuildingOfficeMedStaffLinkData($data) {

		$params = array(
			'LpuBuildingOfficeMedStaffLink_id' => $data['LpuBuildingOfficeMedStaffLink_id'],
			'pmUser_updID' => $data['pmUser_id'],
		);

		$changedData = "
			pmUser_updID = :pmUser_updID
		";

		if (!empty($data['changedParams'])) {
			foreach ($data['changedParams'] as $field => $value) {
				$changedData .= ", {$field} = :{$field}";
				$params[$field] = $value;
			}
		}

		$query = "
			declare
				@Err_Code int,
				@Err_Msg varchar(4000);

				set nocount on;

				begin try
					update LpuBuildingOfficeMedStaffLink with (rowlock)
					set 
						{$changedData}
					where 
						LpuBuildingOfficeMedStaffLink_id = :LpuBuildingOfficeMedStaffLink_id
				end try

				begin catch
					set @Err_Code = error_number();
					set @Err_Msg = error_message();
				end catch

				set nocount off;
	
			select @Err_Code as Error_Code, @Err_Msg as Error_Msg;
		";

		$result = $this->db->query($query, $params);

		// сохраним время приема если оно пришло
		$this->saveVizitTimeOnOfficeChange($data);

		return $result;
	}

	function saveLpuBuildingOfficeMedStaffLink($data) {

		// если на текущую дату с местом работы не связан кабинет
		$params = array(
			'LpuBuildingOfficeMedStaffLink_id' =>  !empty($data['LpuBuildingOfficeMedStaffLink_id']) ? $data['LpuBuildingOfficeMedStaffLink_id'] : null,
			'LpuBuildingOffice_id' => $data['LpuBuildingOffice_id'],
			'MedStaffFact_id' => !empty($data['MedStaffFact_id']) ? $data['MedStaffFact_id'] : null,
			'MedService_id' => !empty($data['MedService_id']) ? $data['MedService_id'] : null,
			'LpuBuildingOfficeMedStaffLink_begDate' => !empty($data['LpuBuildingOfficeMedStaffLink_begDate']) ? $data['LpuBuildingOfficeMedStaffLink_begDate'] : null,
			'LpuBuildingOfficeMedStaffLink_endDate' => !empty($data['LpuBuildingOfficeMedStaffLink_endDate']) ? $data['LpuBuildingOfficeMedStaffLink_endDate'] : null,
			'pmUser_id' => $data['pmUser_id']
		);

		$action = !empty($data['LpuBuildingOfficeMedStaffLink_id']) ? 'upd' : 'ins';

		$query = "
				declare
					@LpuBuildingOfficeMedStaffLink_id bigint = :LpuBuildingOfficeMedStaffLink_id,
					@Error_Code bigint,
					@Error_Message varchar(4000);

				exec p_LpuBuildingOfficeMedStaffLink_{$action}
					@LpuBuildingOfficeMedStaffLink_id = @LpuBuildingOfficeMedStaffLink_id output,
					@LpuBuildingOffice_id = :LpuBuildingOffice_id,
					@MedService_id = :MedService_id,
					@MedStaffFact_id = :MedStaffFact_id,
					@LpuBuildingOfficeMedStaffLink_begDate = :LpuBuildingOfficeMedStaffLink_begDate,
					@LpuBuildingOfficeMedStaffLink_endDate = :LpuBuildingOfficeMedStaffLink_endDate,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;

				select @LpuBuildingOfficeMedStaffLink_id as LpuBuildingOfficeMedStaffLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			";

		$saveOfficeResult = $this->getFirstRowFromQuery($query, $params);

		if (!empty($saveOfficeResult['LpuBuildingOfficeMedStaffLink_id'])) {
			// сохраним время приема если оно пришло
			$data['LpuBuildingOfficeMedStaffLink_id'] = $saveOfficeResult['LpuBuildingOfficeMedStaffLink_id'];
			$this->saveVizitTimeOnOfficeChange($data);
		}

		return $saveOfficeResult;
	}

	/**
	 * Сохранение времени приема при обновлении связи кабинет-врач из АРМа
	 */
	function saveVizitTimeOnOfficeChange($data) {

		// сохраним время приема
		if (
			!empty($data['LpuBuildingOfficeVizitTime_begDate'])
			&& !empty($data['LpuBuildingOfficeVizitTime_endDate'])
		) {

			$params = array(
				'CalendarWeek_id' => $this->getCurrentCalendarWeek(),
				'LpuBuildingOfficeMedStaffLink_id' => $data['LpuBuildingOfficeMedStaffLink_id'],
				'LpuBuildingOfficeVizitTime_begDate' => $data['LpuBuildingOfficeVizitTime_begDate'],
				'LpuBuildingOfficeVizitTime_endDate' => $data['LpuBuildingOfficeVizitTime_endDate'],
				'pmUser_id' => $data['pmUser_id']
			);

			// поробуем найти текущую запись
			$LpuBuildingOfficeVizitTime_id = $this->getFirstResultFromQuery("
				select top 1
				 	LpuBuildingOfficeVizitTime_id
				from v_LpuBuildingOfficeVizitTime (nolock)
				where (1=1)
					and CalendarWeek_id = :CalendarWeek_id
					and LpuBuildingOfficeMedStaffLink_id = :LpuBuildingOfficeMedStaffLink_id
			", $params);

			// если есть запись обновляем время приема
			if (!empty($LpuBuildingOfficeVizitTime_id)) {

				$query = "
					declare
						@Err_Code int,
						@Err_Msg varchar(4000);
		
						set nocount on;
		
						begin try
							update LpuBuildingOfficeVizitTime with (rowlock)
							set 
								LpuBuildingOfficeVizitTime_begDate = :LpuBuildingOfficeVizitTime_begDate,
								LpuBuildingOfficeVizitTime_endDate = :LpuBuildingOfficeVizitTime_endDate,
								pmUser_updID = :pmUser_id
							where 
								LpuBuildingOfficeVizitTime_id = :LpuBuildingOfficeVizitTime_id
						end try
		
						begin catch
							set @Err_Code = error_number();
							set @Err_Msg = error_message();
						end catch
		
						set nocount off;
		
					select @Err_Code as Error_Code, @Err_Msg as Error_Msg;
				";

				$params['LpuBuildingOfficeVizitTime_id'] = $LpuBuildingOfficeVizitTime_id;
				$result = $this->db->query($query, $params);

			} else {

				$params['LpuBuildingOfficeVizitTime_id'] = null;

				// иначе добавляем время приема для текущего дня
				$query = "
					declare
						@LpuBuildingOfficeVizitTime_id bigint = :LpuBuildingOfficeVizitTime_id,
						@Error_Code bigint,
						@Error_Message varchar(4000);
	
					exec p_LpuBuildingOfficeVizitTime_ins
						@LpuBuildingOfficeVizitTime_id = @LpuBuildingOfficeVizitTime_id output,
						@LpuBuildingOfficeMedStaffLink_id = :LpuBuildingOfficeMedStaffLink_id,
						@CalendarWeek_id = :CalendarWeek_id,
						@LpuBuildingOfficeVizitTime_begDate = :LpuBuildingOfficeVizitTime_begDate,
						@LpuBuildingOfficeVizitTime_endDate = :LpuBuildingOfficeVizitTime_endDate,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
					
					select @LpuBuildingOfficeVizitTime_id as LpuBuildingOfficeVizitTime_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
				";

				$saveVizitTimeResult = $this->getFirstRowFromQuery($query, $params);
			}
		}
	}

	function getCurrentCalendarWeek() {
		return $this->getFirstResultFromQuery("
			-- установим признак что первый день недели Понедельник
			SET DATEFIRST 1;

			select DATEPART(dw, dbo.tzGetDate()) as cw;
		", array());
	}
}