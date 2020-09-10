<?php

/**
 * TimetableMedServiceOrg_model - модель для работы с расписанием службы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (megatherion@list.ru)
 * @version      28.12.2011
 */
/**
 * Загрузка базовой модели для работы с расписанием
 */
require_once("Timetable_model.php");

class TimetableMedServiceOrg_model extends Timetable_model {

	/**
	 * 	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение расписания службы для редактирования
	 */
	function getTimetableMedServiceOrgForEdit( $data ) {

		$outdata = array();

		if ( !isset($data['MedService_id']) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Не указана служба, для которой показывать расписание'
			);
		}

		$StartDay = isset($data['StartDay']) ? strtotime($data['StartDay']) : time();

		$outdata['StartDay'] = $StartDay;

		$param['StartDay'] = TimeToDay($StartDay);
		$param['EndDay'] = TimeToDay(strtotime("+14 days", $StartDay));
		$param['MedService_id'] = $data['MedService_id'];
		
		$param['StartDate'] = date( "Y-m-d", $StartDay );
		$param['EndDate'] = date( "Y-m-d", strtotime( "+14 days", $StartDay ) );

		$nTime = $StartDay;


		$outdata['header'] = array();
		$outdata['descr'] = array();
		$outdata['data'] = array();
		$outdata['occupied'] = array();
		for ( $nCol = 0; $nCol < 14; $nCol++ ) {
			//echo $nTime." - ".TimeToDay( $nTime )."<br>";
			$nDay = TimeToDay($nTime);
			$nWeekDay = date('w', $nTime);
			$sClass = "work";
			if ( ( $nWeekDay == 6 ) || ( $nWeekDay == 0 ) ) {
				$sClass = "relax";
			}
			$outdata['header'][TimeToDay($nTime)] = "<td class='$sClass'>" . "<b>" . $this->arShortWeekDayName[$nWeekDay] . "</b>" . date(" d", $nTime) . "</td>";
			$outdata['descr'][TimeToDay($nTime)] = array();
			$outdata['data'][TimeToDay($nTime)] = array();
			$outdata['occupied'][TimeToDay($nTime)] = false;

			$nTime = strtotime("+1 day", $nTime);
		}

		$sql = "
			select 
					p.Org_Phone,
					t.pmUser_updID,
					t.TimetableMedServiceOrg_updDT,
					t.TimetableMedServiceOrg_id,
					t.Org_id,
					t.TimetableMedServiceOrg_Day,
					t.TimetableMedServiceOrg_begTime as TimetableMedServiceOrg_begTime,
					1 as TimetableType_id,
					p.Org_Nick,
					t.PMUser_UpdID,
					u.PMUser_Name,
					u.Lpu_id as pmUser_Lpu_id,
					null as Address_Address
				from TimetableMedServiceOrg t with (nolock)
				left join v_MedService ms with (nolock) on ms.MedService_id = t.MedService_id
				left join Org p with (nolock) on t.Org_id = p.Org_id
				left join v_pmUser u with (nolock) on t.PMUser_UpdID = u.PMUser_id
				where t.TimetableMedServiceOrg_Day >= :StartDay
					and t.TimetableMedServiceOrg_Day < :EndDay
					and t.MedService_id = :MedService_id
					and TimetableMedServiceOrg_begTime between :StartDate and :EndDate
				order by t.TimetableMedServiceOrg_begTime";

		$res = $this->db->query($sql, $param);

		

		$ttgdata = $res->result('array');


		foreach ( $ttgdata as $ttg ) {
			$outdata['data'][$ttg['TimetableMedServiceOrg_Day']][] = $ttg;
			if ( isset($ttg['Org_id']) ) {
				$outdata['occupied'][$ttg['TimetableMedServiceOrg_Day']] = true;
			}
		}

		$sql = "
			select TimetableMedService_id as TimetableMedServiceOrg_id from TimetableLock with(nolock) where TimetableMedService_id is not null";

		$res = $this->db->query($sql);

		$outdata['reserved'] = array();
		$reserved = $res->result('array');
		foreach ( $reserved as $lock ) {
			$outdata['reserved'][] = $lock['TimetableMedServiceOrg_id'];
		}

		return $outdata;
	}

	/**
	 * Создание расписания для службы
	 */
	function createTTMSOSchedule( $data ) {

		$data['StartDay'] = TimeToDay(strtotime($data['CreateDateRange'][0]));
		$data['EndDay'] = TimeToDay(strtotime($data['CreateDateRange'][1]));

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$archive_database_date = $this->config->item('archive_database_date');
			if (strtotime( $data['CreateDateRange'][0] ) < strtotime($archive_database_date)) {
				return array(
					'Error_Msg' => 'Нельзя создать расписание на архивные даты.'
				);
			}
		}

		if ( true !== ($res = $this->checkTimetableMedServiceOrgTimeNotOccupied($data)) ) {
			return $res;
		}
		if ( true !== ($res = $this->checkTimetableMedServiceOrgTimeNotExists($data)) ) {
			return $res;
		}

		$nStartTime = StringToTime($data['StartTime']);
		$nEndTime = StringToTime($data['EndTime']);

		for ( $day = $data['StartDay']; $day <= $data['EndDay']; $day ++ ) {
			$data['Day'] = $day;

			$sql = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = null;

				exec p_TimetableMedServiceOrg_fill
					@MedService_id = :MedService_id,
					@TimetableMedServiceOrg_Day = :TimetableMedServiceOrg_Day,
					@TimetableMedServiceOrg_Time = :TimetableMedServiceOrg_Time,
					@StartTime = :StartTime,
					@EndTime = :EndTime,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
				";
			$res = $this->db->query(
					$sql, array(
				'MedService_id' => $data['MedService_id'],
				'TimetableMedServiceOrg_Day' => $day,
				'TimetableMedServiceOrg_Time' => $data['Duration'],
				'pmUser_id' => $data['pmUser_id'],
				'StartTime' => $data['StartTime'],
				'EndTime' => $data['EndTime'],
					)
			);

			// Пересчет теперь прямо в хранимке
		}

		// отправка STOMP-сообщения
		sendFerStompMessage(array(
			'timeTable' => 'TimetableMedServiceOrg',
			'action' => 'AddTicket',
			'setDate' => date("c"),
			'begDate' => date("c", DayMinuteToTime($data['StartDay'], $nStartTime)),
			'endDate' => date("c", DayMinuteToTime($data['EndDay'], $nEndTime)),
			'MedStaffFact_id' => $data['session']['CurMedStaffFact_id']
				), 'RulePeriod');

		return array(
			'Error_Msg' => ''
		);
	}

	
	/**
	 * Создание расписания для услуги
	 */
	function createTTMSOScheduleUslugaComplex( $data ) {

		$data['StartDay'] = TimeToDay(strtotime($data['CreateDateRange'][0]));
		$data['EndDay'] = TimeToDay(strtotime($data['CreateDateRange'][1]));

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$archive_database_date = $this->config->item('archive_database_date');
			if (strtotime( $data['CreateDateRange'][0] ) < strtotime($archive_database_date)) {
				return array(
					'Error_Msg' => 'Нельзя создать расписание на архивные даты.'
				);
			}
		}

		if ( true !== ($res = $this->checkTimetableMedServiceOrgTimeNotOccupiedUslugaComplex($data)) ) {
			return $res;
		}
		if ( true !== ($res = $this->checkTimetableMedServiceOrgTimeNotExistsUslugaComplex($data)) ) {
			return $res;
		}

		$nStartTime = StringToTime($data['StartTime']);
		$nEndTime = StringToTime($data['EndTime']);

		for ( $day = $data['StartDay']; $day <= $data['EndDay']; $day ++ ) {
			$data['Day'] = $day;

			$sql = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = null;

				exec p_TimetableMedServiceOrg_fill
					@UslugaComplexMedService_id = :UslugaComplexMedService_id,
					@TimetableMedServiceOrg_Day = :TimetableMedServiceOrg_Day,
					@TimetableMedServiceOrg_Time = :TimetableMedServiceOrg_Time,
					@TimetableType_id = :TimetableType_id,
					@TimetableExtend_Descr = :TimetableExtend_Descr,
					@StartTime = :StartTime,
					@EndTime = :EndTime,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
				";
			$res = $this->db->query(
					$sql, array(
				'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id'],
				'TimetableMedServiceOrg_Day' => $day,
				'TimetableMedServiceOrg_Time' => $data['Duration'],
				'pmUser_id' => $data['pmUser_id'],
				'TimetableType_id' => $data['TimetableType_id'],
				'TimetableExtend_Descr' => $data['TimetableExtend_Descr'],
				'StartTime' => $data['StartTime'],
				'EndTime' => $data['EndTime'],
					)
			);

			// Пересчет теперь прямо в хранимке
		}

		// отправка STOMP-сообщения
		sendFerStompMessage(array(
			'timeTable' => 'TimetableMedServiceOrg',
			'action' => 'AddTicket',
			'setDate' => date("c"),
			'begDate' => date("c", DayMinuteToTime($data['StartDay'], $nStartTime)),
			'endDate' => date("c", DayMinuteToTime($data['EndDay'], $nEndTime)),
			'MedStaffFact_id' => $data['session']['CurMedStaffFact_id']
				), 'RulePeriod');

		return array(
			'Error_Msg' => ''
		);
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) и интервале времени занятые бирки для службы
	 */
	function checkTimetableMedServiceOrgTimeNotOccupied( $data ) {

		if ( isset($data['Day']) ) {
			$sql = "
				select
					count(*) as cnt
				from TimetableMedServiceOrg with (nolock)
				where
					MedService_id = :MedService_id
					and Org_id is not null
					and ( 
						( TimetableMedServiceOrg_begTime >= :StartTime and TimetableMedServiceOrg_begTime < :EndTime )
						or ( DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) > :StartTime and DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) < :EndTime )
						or ( DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) > :StartTime and TimetableMedServiceOrg_begTime < :StartTime ) 
					)";

			$res = $this->db->query(
					$sql, array(
				'StartTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['StartTime']))),
				'EndTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['EndTime']))),
				'MedService_id' => $data['MedService_id']
					)
			);
			if ( is_object($res) ) {
				$res = $res->result('array');
			}
			if ( $res[0]['cnt'] > 0 ) {
				return array(
					'Error_Msg' => 'Нельзя очистить расписание, так как есть занятые бирки.'
				);
			}
		}

		//Если задано несколько дней - проходим в цикле
		if ( isset($data['StartDay']) ) {
			for ( $day = $data['StartDay']; $day <= $data['EndDay']; $day ++ ) {
				$data['Day'] = $day;
				$sql = "
					select
						count(*) as cnt
					from TimetableMedServiceOrg with (nolock)
					where
						MedService_id = :MedService_id
						and Org_id is not null
						and ( 
							( TimetableMedServiceOrg_begTime >= :StartTime and TimetableMedServiceOrg_begTime < :EndTime )
							or ( DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) > :StartTime and DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) < :EndTime )
							or ( DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) > :StartTime and TimetableMedServiceOrg_begTime < :StartTime ) 
						)";

				$res = $this->db->query(
						$sql, array(
					'StartTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['StartTime']))),
					'EndTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['EndTime']))),
					'MedService_id' => $data['MedService_id']
						)
				);
				if ( is_object($res) ) {
					$res = $res->result('array');
				}
				if ( $res[0]['cnt'] > 0 ) {
					return array(
						'Error_Msg' => 'Нельзя очистить расписание, так как есть занятые бирки.'
					);
				}
			}
		}



		return true;
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) и интервале времени занятые бирки для услуги
	 */
	function checkTimetableMedServiceOrgTimeNotOccupiedUslugaComplex( $data ) {

		if ( isset($data['Day']) ) {
			$sql = "
				select
					count(*) as cnt
				from TimetableMedServiceOrg with (nolock)
				where
					UslugaComplexMedService_id = :UslugaComplexMedService_id
					and Org_id is not null
					and ( 
						( TimetableMedServiceOrg_begTime >= :StartTime and TimetableMedServiceOrg_begTime < :EndTime )
						or ( DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) > :StartTime and DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) < :EndTime )
						or ( DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) > :StartTime and TimetableMedServiceOrg_begTime < :StartTime ) 
					)";

			$res = $this->db->query(
					$sql, array(
				'StartTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['StartTime']))),
				'EndTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['EndTime']))),
				'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id']
					)
			);
			if ( is_object($res) ) {
				$res = $res->result('array');
			}
			if ( $res[0]['cnt'] > 0 ) {
				return array(
					'Error_Msg' => 'Нельзя очистить расписание, так как есть занятые бирки.'
				);
			}
		}

		//Если задано несколько дней - проходим в цикле
		if ( isset($data['StartDay']) ) {
			for ( $day = $data['StartDay']; $day <= $data['EndDay']; $day ++ ) {
				$data['Day'] = $day;
				$sql = "
					select
						count(*) as cnt
					from TimetableMedServiceOrg with (nolock)
					where
						UslugaComplexMedService_id = :UslugaComplexMedService_id
						and Org_id is not null
						and ( 
							( TimetableMedServiceOrg_begTime >= :StartTime and TimetableMedServiceOrg_begTime < :EndTime )
							or ( DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) > :StartTime and DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) < :EndTime )
							or ( DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) > :StartTime and TimetableMedServiceOrg_begTime < :StartTime ) 
						)";

				$res = $this->db->query(
						$sql, array(
					'StartTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['StartTime']))),
					'EndTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['EndTime']))),
					'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id']
						)
				);
				if ( is_object($res) ) {
					$res = $res->result('array');
				}
				if ( $res[0]['cnt'] > 0 ) {
					return array(
						'Error_Msg' => 'Нельзя очистить расписание, так как есть занятые бирки.'
					);
				}
			}
		}



		return true;
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) и интервале времени созданные бирки для службы
	 */
	function checkTimetableMedServiceOrgTimeNotExists( $data ) {


		if ( isset($data['Day']) ) {
			$sql = "
				select
					count(*) as cnt
				from TimetableMedServiceOrg with (nolock)
				where
					MedService_id = :MedService_id
					and ( 
						( TimetableMedServiceOrg_begTime >= :StartTime and TimetableMedServiceOrg_begTime < :EndTime )
						or ( DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) > :StartTime and DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) < :EndTime )
						or ( DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) > :StartTime and TimetableMedServiceOrg_begTime < :StartTime ) 
					)";

			$res = $this->db->query(
					$sql, array(
				'StartTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['StartTime']))),
				'EndTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['EndTime']))),
				'MedService_id' => $data['MedService_id']
					)
			);
			if ( is_object($res) ) {
				$res = $res->result('array');
			}
			if ( $res[0]['cnt'] > 0 ) {
				return array(
					'Error_Msg' => 'В заданном интервале времени уже существуют бирки.'
				);
			}
		}

		//Если задано несколько дней - проходим в цикле
		if ( isset($data['StartDay']) ) {
			for ( $day = $data['StartDay']; $day <= $data['EndDay']; $day ++ ) {
				$data['Day'] = $day;
				$sql = "
					select
						count(*) as cnt
					from TimetableMedServiceOrg with (nolock)
					where
						MedService_id = :MedService_id
						and ( 
							( TimetableMedServiceOrg_begTime >= :StartTime and TimetableMedServiceOrg_begTime < :EndTime )
							or ( DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) > :StartTime and DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) < :EndTime )
							or ( DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) > :StartTime and TimetableMedServiceOrg_begTime < :StartTime ) 
						)";

				$res = $this->db->query(
						$sql, array(
					'StartTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['StartTime']))),
					'EndTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['EndTime']))),
					'MedService_id' => $data['MedService_id']
						)
				);
				if ( is_object($res) ) {
					$res = $res->result('array');
				}
				if ( $res[0]['cnt'] > 0 ) {
					return array(
						'Error_Msg' => 'В заданном интервале времени уже существуют бирки.'
					);
				}
			}
		}



		return true;
	}


	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function checkTimetableMedServiceOrgTimeNotExistsUslugaComplex( $data ) {


		if ( isset($data['Day']) ) {
			$sql = "
				select
					count(*) as cnt
				from TimetableMedServiceOrg with (nolock)
				where
					UslugaComplexMedService_id = :UslugaComplexMedService_id
					and ( 
						( TimetableMedServiceOrg_begTime >= :StartTime and TimetableMedServiceOrg_begTime < :EndTime )
						or ( DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) > :StartTime and DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) < :EndTime )
						or ( DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) > :StartTime and TimetableMedServiceOrg_begTime < :StartTime ) 
					)";

			$res = $this->db->query(
					$sql, array(
				'StartTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['StartTime']))),
				'EndTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['EndTime']))),
				'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id']
					)
			);
			if ( is_object($res) ) {
				$res = $res->result('array');
			}
			if ( $res[0]['cnt'] > 0 ) {
				return array(
					'Error_Msg' => 'В заданном интервале времени уже существуют бирки.'
				);
			}
		}

		//Если задано несколько дней - проходим в цикле
		if ( isset($data['StartDay']) ) {
			for ( $day = $data['StartDay']; $day <= $data['EndDay']; $day ++ ) {
				$data['Day'] = $day;
				$sql = "
					select
						count(*) as cnt
					from TimetableMedServiceOrg with (nolock)
					where
						UslugaComplexMedService_id = :UslugaComplexMedService_id
						and ( 
							( TimetableMedServiceOrg_begTime >= :StartTime and TimetableMedServiceOrg_begTime < :EndTime )
							or ( DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) > :StartTime and DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) < :EndTime )
							or ( DATEADD( minute, TimetableMedServiceOrg_Time, TimetableMedServiceOrg_begTime  ) > :StartTime and TimetableMedServiceOrg_begTime < :StartTime ) 
						)";

				$res = $this->db->query(
						$sql, array(
					'StartTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['StartTime']))),
					'EndTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['EndTime']))),
					'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id']
						)
				);
				if ( is_object($res) ) {
					$res = $res->result('array');
				}
				if ( $res[0]['cnt'] > 0 ) {
					return array(
						'Error_Msg' => 'В заданном интервале времени уже существуют бирки.'
					);
				}
			}
		}



		return true;
	}

	/**
	 * Копирование расписания для службы
	 */
	function copyTTMSOSchedule( $data ) {

		if ( empty($data['CopyToDateRange'][0]) ) {
			return array(
				'Error_Msg' => 'Не указан диапазон для вставки расписания.'
			);
		}

		$data['StartDay'] = TimeToDay( strtotime( $data['CopyToDateRange'][0] ) );
		$data['EndDay'] = TimeToDay( strtotime( $data['CopyToDateRange'][1] ) );

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$archive_database_date = $this->config->item('archive_database_date');
			if (strtotime( $data['CreateDateRange'][1] ) < strtotime($archive_database_date)) {
				return array(
					'Error_Msg' => 'Нельзя скопировать расписание на архивные даты.'
				);
			}
		}

		if ( true !== ($res = $this->checkTimetableMedServiceOrgDayNotOccupied($data)) ) {
			return array(
				'Error_Msg' => 'Нельзя скопировать расписание на промежуток, так как на нем занятые бирки.'
			);
		}

		$n = 0;
		$nShift = TimeToDay(strtotime($data['CreateDateRange'][1])) - TimeToDay(strtotime($data['CreateDateRange'][0])) + 1;
		$nTargetStart = 0;
		$nTargetEnd = 0;
		while ($nTargetEnd < $data['EndDay']) {
			$nTargetStart = $data['StartDay'] + $nShift * $n;
			$nTargetEnd = $data['StartDay'] + $nShift * ($n+1) - 1;
			$nTargetEnd = min($nTargetEnd, $data['EndDay']);
			
			$SourceStartDay = TimeToDay(strtotime($data['CreateDateRange'][0]));
			$SourceEndDay = TimeToDay(strtotime($data['CreateDateRange'][1]));
			$SourceEndDay = min($SourceEndDay, (TimeToDay(strtotime($data['CreateDateRange'][0])) + $nTargetEnd - $nTargetStart));
			
			$sql = "
				declare
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				exec p_TimetableMedServiceOrg_copy
					@MedService_id = :MedService_id,
					@SourceStartDay = :SourceStartDay,
					@SourceEndDay = :SourceEndDay,
					@TargetStartDay = :TargetStartDay,
					@TargetEndDay = :TargetEndDay,
					@CopyTimetableExtend_Descr = :CopyTimetableExtend_Descr,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @ErrCode as Error_Code, @ErrMsg as Error_Msg;";

			$res = $this->db->query(
					$sql, array(
				'MedService_id' => $data['MedService_id'],
				'SourceStartDay' => $SourceStartDay,
				'SourceEndDay' => $SourceEndDay,
				'TargetStartDay' => $nTargetStart,
				'TargetEndDay' => $nTargetEnd,
				'CopyTimetableExtend_Descr' => ($data['CopyTTMSOComments'] == 1) ? 1 : NULL,
				'pmUser_id' => $data['pmUser_id']
					)
			);

			if ( is_object($res) ) {
				$resp = $res->result('array');
				if ( count($resp) > 0 && empty($resp[0]['Error_Msg']) ) {
					// отправка STOMP-сообщения
					sendFerStompMessage(array(
						'timeTable' => 'TimetableMedServiceOrg',
						'action' => 'AddTicket',
						'setDate' => date("c"),
						'begDate' => date("c", DayMinuteToTime($nTargetStart, 0)),
						'endDate' => date("c", DayMinuteToTime($nTargetEnd, 0)),
						'MedStaffFact_id' => $data['session']['CurMedStaffFact_id']
							), 'RulePeriod');
				}
			}

			for ( $i = 0; $i <= $nTargetEnd - $nTargetStart; $i++ ) {
				// Пересчет теперь прямо в хранимке
				if ( $data['CopyDayComments'] == 1 ) {
					$sql = "
					
						declare
							@MedServiceDay_Descr varchar(200),
							@Res bigint,
							@ErrCode bigint,
							@ErrMsg varchar(4000);
					
						select
							@MedServiceDay_Descr = MedServiceDay_Descr
						from MedServiceDay with (nolock)
						where MedService_id = :MedService_id
							and Day_id = :SourceDay_id
						exec p_MedServiceDay_setDescr
							@MedServiceDay_id = @Res,
							@Day_id = :TargetDay_id,
							@MedService_id = :MedService_id,
							@MedServiceDay_Descr = @MedServiceDay_Descr,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output";

					$res = $this->db->query(
							$sql, array(
						'MedService_id' => $data['MedService_id'],
						'TargetDay_id' => $nTargetStart + $i,
						'SourceDay_id' => TimeToDay(strtotime($data['CreateDateRange'][0])) + $i,
						'pmUser_id' => $data['pmUser_id']
							)
					);
				}
			}
			
			$n++;
		}

		return array(
			'Error_Msg' => ''
		);
	}

	/**
	 * Копирование расписания для услуги службы
	 */
	function copyTTMSOScheduleUslugaComplex( $data ) {

		$data['StartDay'] = TimeToDay(strtotime($data['CreateDateRange'][1])) + 1;
		$data['EndDay'] = $data['StartDay'] + (TimeToDay(strtotime($data['CreateDateRange'][1])) - TimeToDay(strtotime($data['CreateDateRange'][0]))) * $data['CopyTimes'];

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$archive_database_date = $this->config->item('archive_database_date');
			if (strtotime( $data['CreateDateRange'][1] ) < strtotime($archive_database_date)) {
				return array(
					'Error_Msg' => 'Нельзя скопировать расписание на архивные даты.'
				);
			}
		}

		if ( true !== ($res = $this->checkTimetableMedServiceOrgDayNotOccupiedUslugaComplex($data)) ) {
			return array(
				'Error_Msg' => 'Нельзя скопировать расписание на промежуток, так как на нем занятые бирки.'
			);
		}

		for ( $n = 1; $n <= $data['CopyTimes']; $n++ ) {
			$nShift = $n * (TimeToDay(strtotime($data['CreateDateRange'][1])) - TimeToDay(strtotime($data['CreateDateRange'][0])) + 1);
			$nTargetStart = TimeToDay(strtotime($data['CreateDateRange'][0])) + $nShift;
			$nTargetEnd = TimeToDay(strtotime($data['CreateDateRange'][1])) + $nShift;
			$sql = "
				declare
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				exec p_TimetableMedServiceOrg_copyUslugaComplex
					@UslugaComplexMedService_id = :UslugaComplexMedService_id,
					@SourceStartDay = :SourceStartDay,
					@SourceEndDay = :SourceEndDay,
					@TargetStartDay = :TargetStartDay,
					@TargetEndDay = :TargetEndDay,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @ErrCode as Error_Code, @ErrMsg as Error_Msg;";
			$res = $this->db->query(
					$sql, array(
				'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id'],
				'SourceStartDay' => TimeToDay(strtotime($data['CreateDateRange'][0])),
				'SourceEndDay' => TimeToDay(strtotime($data['CreateDateRange'][1])),
				'TargetStartDay' => $nTargetStart,
				'TargetEndDay' => $nTargetEnd,
				'pmUser_id' => $data['pmUser_id']
					)
			);

			if ( is_object($res) ) {
				$resp = $res->result('array');
				if ( count($resp) > 0 && empty($resp[0]['Error_Msg']) ) {
					// отправка STOMP-сообщения
					sendFerStompMessage(array(
						'timeTable' => 'TimetableMedServiceOrg',
						'action' => 'AddTicket',
						'setDate' => date("c"),
						'begDate' => date("c", DayMinuteToTime($nTargetStart, 0)),
						'endDate' => date("c", DayMinuteToTime($nTargetEnd, 0)),
						'MedStaffFact_id' => $data['session']['CurMedStaffFact_id']
							), 'RulePeriod');
				}
			}

			for ( $i = 0; $i <= $nTargetEnd - $nTargetStart; $i++ ) {
				// Пересчет теперь прямо в хранимке
				if ( $data['CopyDayComments'] == 1 ) {
					$sql = "
					
						declare
							@MedServiceDay_Descr varchar(200),
							@Res bigint,
							@ErrCode bigint,
							@ErrMsg varchar(4000);
					
						select
							@MedServiceDay_Descr = MedServiceDay_Descr
						from MedServiceDay with (nolock)
						where UslugaComplexMedService_id = :UslugaComplexMedService_id
							and Day_id = :SourceDay_id
						exec p_MedServiceDay_setDescr
							@MedServiceDay_id = @Res,
							@Day_id = :TargetDay_id,
							@UslugaComplexMedService_id = :UslugaComplexMedService_id,
							@MedServiceDay_Descr = @MedServiceDay_Descr,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output";

					$res = $this->db->query(
							$sql, array(
						'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id'],
						'TargetDay_id' => $nTargetStart + $i,
						'SourceDay_id' => TimeToDay(strtotime($data['CreateDateRange'][0])) + $i,
						'pmUser_id' => $data['pmUser_id']
							)
					);
				}
			}
		}

		return array(
			'Error_Msg' => ''
		);
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) занятые бирки для службы
	 */
	function checkTimetableMedServiceOrgDayNotOccupied( $data ) {
		if ( isset($data['Day']) ) {
			$sql = "
				SELECT count(*) as cnt
				FROM TimetableMedServiceOrg with (nolock)
				WHERE
					TimetableMedServiceOrg_Day = :Day
					and MedService_id = :MedStaffFact_id
					and Person_id is not null
					and TimetableMedServiceOrg_begTime is not null
			";

			$res = $this->db->query(
					$sql, array(
				'Day' => $data['Day'],
				'MedService_id' => $data['MedService_id'],
					)
			);
		}
		if ( isset($data['StartDay']) ) {
			$sql = "
				SELECT count(*) as cnt
				FROM TimetableMedServiceOrg with (nolock)
				WHERE
					TimetableMedServiceOrg_day between :StartDay and :EndDay
					and MedService_id = :MedService_id
					and Person_id is not null
					and TimetableMedServiceOrg_begTime is not null
			";

			$res = $this->db->query(
					$sql, array(
				'StartDay' => $data['StartDay'],
				'EndDay' => $data['EndDay'],
				'MedService_id' => $data['MedService_id'],
					)
			);
		}
		if ( is_object($res) ) {
			$res = $res->result('array');
		}
		if ( $res[0]['cnt'] > 0 ) {
			return false;
		}
		return true;
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) занятые бирки для службы
	 */
	function checkTimetableMedServiceOrgDayNotOccupiedUslugaComplex( $data ) {
		if ( isset($data['Day']) ) {
			$sql = "
				SELECT count(*) as cnt
				FROM TimetableMedServiceOrg with (nolock)
				WHERE
					TimetableMedServiceOrg_Day = :Day
					and UslugaComplexMedService_id = :UslugaComplexMedService_id
					and Person_id is not null
					and TimetableMedServiceOrg_begTime is not null
			";

			$res = $this->db->query(
					$sql, array(
				'Day' => $data['Day'],
				'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id'],
					)
			);
		}
		if ( isset($data['StartDay']) ) {
			$sql = "
				SELECT count(*) as cnt
				FROM TimetableMedServiceOrg with (nolock)
				WHERE
					TimetableMedServiceOrg_day between :StartDay and :EndDay
					and UslugaComplexMedService_id = :UslugaComplexMedService_id
					and Person_id is not null
					and TimetableMedServiceOrg_begTime is not null
			";

			$res = $this->db->query(
					$sql, array(
				'StartDay' => $data['StartDay'],
				'EndDay' => $data['EndDay'],
				'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id'],
					)
			);
		}
		if ( is_object($res) ) {
			$res = $res->result('array');
		}
		if ( $res[0]['cnt'] > 0 ) {
			return array(
				'Error_Msg' => 'Нельзя очистить расписание, так как есть занятые бирки.'
			);
		}
		return true;
	}

	/**
	 * Очистка дня для службы
	 */
	function ClearDay( $data ) {

		$sql = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			exec p_TimetableMedServiceOrg_clearDay
				@TimetableMedServiceOrg_Day = :TimetableMedServiceOrg_Day,
				@MedService_id = :MedService_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output
			select @Res as TimetableMedServiceOrg_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$res = $this->db->query(
				$sql, array(
			'MedService_id' => $data['MedService_id'],
			'TimetableMedServiceOrg_Day' => $data['Day'],
			'pmUser_id' => $data['pmUser_id']
				)
		);

		if ( is_object($res) ) {
			$resp = $res->result('array');
			if ( count($resp) > 0 && empty($resp[0]['Error_Msg']) ) {
				// отправка STOMP-сообщения
				sendFerStompMessage(array(
					'timeTable' => 'TimetableMedServiceOrg',
					'action' => 'DelTicket',
					'setDate' => date("c"),
					'begDate' => date("c", DayMinuteToTime($data['Day'], 0)),
					'endDate' => date("c", DayMinuteToTime($data['Day'], 0)),
					'MedStaffFact_id' => $data['session']['CurMedStaffFact_id']
						), 'RulePeriod');
			}
		}

		// Пересчет теперь прямо в хранимке

		return array(
			'Error_Msg' => ''
		);
	}

	/**
	 * Получение истории изменения бирки службы
	 */
	function getTTMSOHistory( $data ) {
		if ( !isset($data['ShowFullHistory']) ) {
			$sql = "
				select
					convert(varchar(10), TimetableMedServiceOrgHist_insDT, 104) + ' ' + convert(varchar(8), TimetableMedServiceOrgHist_insDT, 108) as TimetableHist_insDT,
					rtrim(PMUser_Name) as PMUser_Name,
					TimetableActionType_Name as TimetableActionType_Name,
					TimetableType_Name,
					rtrim(rtrim(p.Person_Surname) + ' ' + rtrim(Person_Firname) + ' ' + isnull(rtrim(Person_Secname), '')) as Person_FIO,
					convert(varchar(10), Person_BirthDay, 104) as Person_BirthDay
				from TimetableMedServiceOrgHist ttsh with (nolock)
				left join v_pmUser pu with (nolock) on ttsh.TimetableMedServiceOrgHist_userID = pu.pmuser_id
				left join TimetableActionType ttat with (nolock) on ttat.TimetableActionType_id = ttsh.TimetableActionType_id
				left join v_TimetableType ttt with(nolock) on ttt.TimetableType_id = isnull(ttsh.TimetableType_id, 1)
				left join v_Person_ER p with (nolock) on ttsh.Person_id = p.Person_id
				where TimetableMedServiceOrg_id = :TimetableMedServiceOrg_id";
		} else {
			$sql = "declare
						@MedService_id bigint,
						@TimetableMedServiceOrg_begTime datetime
					
					select
						@MedService_id = MedService_id, 
						@TimetableMedServiceOrg_begTime = TimetableMedServiceOrg_begTime
					from TimetableMedServiceOrg with (nolock)
					where TimetableMedServiceOrg_id = :TimetableMedServiceOrg_id
			
					select
						convert(varchar(10), TimetableMedServiceOrgHist_insDT, 104) + ' ' + convert(varchar(8), TimetableMedServiceOrgHist_insDT, 108) as TimetableHist_insDT,
						rtrim(PMUser_Name) as PMUser_Name,
						TimetableActionType_Name as TimetableActionType_Name,
						TimetableType_Name,
						rtrim(rtrim(p.Person_Surname) + ' ' + rtrim(Person_Firname) + ' ' + isnull(rtrim(Person_Secname), '')) as Person_FIO,
						convert(varchar(10), Person_BirthDay, 104) as Person_BirthDay
					from TimetableMedServiceOrgHist ttsh with (nolock)
					left join v_pmUser pu with (nolock) on ttsh.TimetableMedServiceOrgHist_userID = pu.pmuser_id
					left join TimetableActionType ttat with (nolock) on ttat.TimetableActionType_id = ttsh.TimetableActionType_id
					left join v_TimetableType ttt with(nolock) on ttt.TimetableType_id = isnull(ttsh.TimetableType_id, 1)
					left join v_Person_ER p with (nolock) on ttsh.Person_id = p.Person_id
					where 
						MedService_id = @MedService_id
						and TimetableMedServiceOrg_begTime = @TimetableMedServiceOrg_begTime";
		}

		$res = $this->db->query(
				$sql, array(
			'TimetableMedServiceOrg_id' => $data['TimetableMedServiceOrg_id']
				)
		);

		if ( is_object($res) ) {
			return $res = $res->result('array');
		} else {
			return false;
		}
	}


	/**
	 * Проверка, что бирка существует и занята
	 */
	function checkTimetableMedServiceOrgOccupied( $data ) {
		$sql = "
			SELECT TimetableMedServiceOrg_id, Org_id
			FROM TimetableMedServiceOrg with(nolock)
			WHERE
				TimetableMedServiceOrg_id = :Id
		";

		$res = $this->db->query(
				$sql, array(
			'Id' => $data['TimetableMedServiceOrg_id']
				)
		);
		if ( is_object($res) ) {
			$res = $res->result('array');
		}
		if ( !isset($res[0]) || !isset($res[0]['TimetableMedServiceOrg_id']) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Бирка с таким идентификатором не существует.'
			);
		}
		if ( !isset($res[0]['Org_id']) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Выбранная вами бирка уже свободна.'
			);
		}
		return true;
	}



	/**
	 * Проверка, что бирка существует и свободна
	 */
	function checkTimetableMedServiceOrgFree( $data ) {
		$sql = "
			SELECT
				TimetableMedServiceOrg_id,
				Org_id
			FROM TimetableMedServiceOrg with(nolock)
			WHERE
				TimetableMedServiceOrg_id = :Id
		";

		$res = $this->db->query(
				$sql, array(
			'Id' => $data['TimetableMedServiceOrg_id']
				)
		);
		if ( is_object($res) ) {
			$res = $res->result('array');
		}
		if ( !isset($res[0]) || $res[0]['TimetableMedServiceOrg_id'] == null ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Бирка с таким идентификатором не существует.'
			);
		}
		if ( $res[0]['Org_id'] != null ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Выбранная вами бирка уже занята.'
			);
		}
		return true;
	}



	/**
	 * Удаление бирки для службы
	 */
	function Delete( $data ) {
		$data['object'] = 'TimetableMedServiceOrg';
		if ( isset($data['TimetableMedServiceOrg_id']) ) {
			$data['TimetableMedServiceOrgGroup'] = array($data['TimetableMedServiceOrg_id']);
		} else {
			$data['TimetableMedServiceOrgGroup'] = json_decode($data['TimetableMedServiceOrgGroup']);
		}

		if ( true !== ($res = $this->checkTimetablesFree($data)) ) {
			return $res;
		}

		// Получаем службу и список дней, на которые мы выделили бирки
		$res = $this->db->query("
			select
					TimetableMedServiceOrg_id,
					MedService_id,
					TimetableMedServiceOrg_Day
			from TimetableMedServiceOrg with (nolock)
			where TimetableMedServiceOrg_id in (" . implode(', ', $data['TimetableMedServiceOrgGroup']) . ")"
		);

		if ( is_object($res) ) {
			$res = $res->result('array');
		} else {
			return false;
		}
		// Удаляем каждую бирку по отдельности. Не лучший вариант конечно
		foreach ( $res as $row ) {

			/*$MedService_id = $row['MedService_id'];
			$UslugaComplexMedService_id = $row['UslugaComplexMedService_id'];
			$Day = $row['TimetableMedServiceOrg_Day'];*/

			//Удаляем бирку
			$sql = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec p_TimetableMedServiceOrg_del
					@TimetableMedServiceOrg_id = :TimetableMedServiceOrg_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$res = $this->db->query($sql, array('TimetableMedServiceOrg_id' => $row['TimetableMedServiceOrg_id']));

			if ( is_object($res) ) {
				$resp = $res->result('array');
				if ( count($resp) > 0 && empty($resp[0]['Error_Msg']) ) {
					// отправка STOMP-сообщения
					sendFerStompMessage(array(
						'id' => $row['TimetableMedServiceOrg_id'],
						'timeTable' => 'TimetableMedServiceOrg',
						'action' => 'DelTicket',
						'setDate' => date("c")
							), 'Rule');
				}
			}

			// Пересчет теперь прямо в хранимке
		}

		return array(
			'success' => true
		);
	}
	

	
	/**
	 * Проверка прав на очистку бирки
	 */
	function checkHasRightsToClearRecord( $data ) {

		$sql = "
			SELECT
				t.TimetableMedServiceOrg_id,
				t.pmUser_updId,
				pu.Lpu_id,
				l.Org_id
			FROM TimetableMedServiceOrg t (nolock)
			left join v_pmUser pu (nolock) on t.pmUser_updId = pu.pmUser_id
			left join v_Lpu l (nolock) on l.Lpu_id = pu.Lpu_id
			WHERE
				t.TimetableMedServiceOrg_id = :TimetableMedServiceOrg_id
		";

		$res = $this->db->query(
				$sql, array(
			'TimetableMedServiceOrg_id' => $data['TimetableMedServiceOrg_id']
				)
		);
		if ( is_object($res) ) {
			$res = $res->result('array');
		}
		if ( $res[0]['TimetableMedServiceOrg_id'] == null ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Бирка с таким идентификатором не существует.'
			);
		}
		if (
				!(($res[0]['pmUser_updId'] == $data['session']['pmuser_id']) ||
				isCZAdmin() ||
				isLpuRegAdmin($res[0]['Org_id']) ||
				isInetUser($res[0]['pmUser_updId'])
				)
		) {
			return array(
				'success' => false,
				'Error_Msg' => 'У вас нет прав отменить запись на прием, <br/>так как она сделана не вами.'
			);
		}

		return true;
	}

	/**
	 * Получение расписания на один день на службу
	 */
	function getTimetableMedServiceOrgOneDay( $data ) {
		$outdata = array();

		$StartDay = isset($data['StartDay']) ? strtotime($data['StartDay']) : time();

		$outdata['StartDay'] = $StartDay;

		$param['StartDay'] = TimeToDay($StartDay);
		$param['MedService_id'] = $data['MedService_id'];

		$nTime = $StartDay;

		$outdata['day_comment'] = null;
		$outdata['data'] = array();
		
		$sql = "
			select
					p.Org_Phone,
					case when a1.Address_id is not null
					then 
						a1.Address_Address
					else
						a.Address_Address
					end as Address_Address,
					t.pmUser_updID,
					t.TimetableMedServiceOrg_updDT,
					t.TimetableMedServiceOrg_id,
					t.Org_id,
					t.TimetableMedServiceOrg_Day,
					TimetableMedServiceOrg_begTime,
					p.Org_Nick,
					t.PMUser_UpdID,
					u.PMUser_Name,
					u.Lpu_id as pmUser_Lpu_id
				from TimetableMedServiceOrg t with (nolock)
				left outer join v_MedService ms with (nolock) on ms.MedService_id = t.MedService_id
				left outer join Org p with (nolock) on t.Org_id = p.Org_id
				left outer join Address a with (nolock) on p.UAddress_id = a.Address_id
				left outer join Address a1 with (nolock) on p.PAddress_id = a1.Address_id
				left outer join v_pmUser u with (nolock) on t.PMUser_UpdID = u.PMUser_id
				where t.TimetableMedServiceOrg_Day = :StartDay
					and t.MedService_id = :MedService_id
					and TimetableMedServiceOrg_begTime is not null
				order by t.TimetableMedServiceOrg_begTime";

		$res = $this->db->query($sql, $param);


		$ttsdata = $res->result('array');


		foreach ( $ttsdata as $tts ) {
			$outdata['data'][] = $tts;
		}

		$sql = "
			select TimetableMedService_id as TimetableMedServiceOrg_id from TimetableLock with(nolock)";

		$res = $this->db->query($sql);

		$outdata['reserved'] = $res->result('array');

		return $outdata;
	}


	/**
	 * Получение первой даты записи МО на защиту в МЗ
	 */
	function getFirstTimetableMedServiceOrgDate($data) {
		$query = "
		select top 1
			TTMSO.TimetableMedServiceOrg_begTime
		from
			v_TimetableMedServiceOrg TTMSO (nolock)
			inner join v_Lpu L (nolock) on TTMSO.Org_id = L.Org_id
		where
			L.Lpu_id = :Lpu_id
		order by
			TimetableMedServiceOrg_begTime
		";

		//echo getDebugSQL($sql, $data);exit();
		$res = $this->db->query($query, $data);
		$response = array(array('Error_Msg'=>null,'Error_Code'=>null));
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			$response[0]['Error_Msg'] = 'Ошибка запроса к БД при получении даты первой записи МО на защиту.';
			$response[0]['Error_Code'] = 199;
			return $response;
		}
	}

}

?>
