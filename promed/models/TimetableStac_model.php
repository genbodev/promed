<?php

/**
 * TimetableStac_model - модель для работы с расписанием в стационаре
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (megatherion@list.ru)
 * @version      19.03.2012
 */
/**
 * Загрузка базовой модели для работы с расписанием
 */
require_once("Timetable_model.php");
/**
 * @property int EmergencyData_id
 */
class TimetableStac_model extends Timetable_model {

	/**
	 * 	Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_SET_ATTRIBUTE,
			/*self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_DELETE,*/
		));
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'TimetableStac';
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @todo доработать описание
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = array(
			self::ID_KEY => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_NOT_NULL,
				),
				'alias' => 'TimetableStac_id',
				'label' => 'Идентификатор',
				'save' => 'trim',
				'type' => 'id'
			)
		);
		$arr['person_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),// Записанный человек
			'alias' => 'Person_id',
		);
		$arr['setdate'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),// Дата начала
			'alias' => 'TimeTableStac_setDate',
		);
		$arr['day'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_NULL,
			),// День приема по бирке
			'alias' => 'TimeTableStac_Day',
		);
		$arr['recclass_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),// тип записи
			'alias' => 'RecClass_id',
		);
		$arr['TimeTableType_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),// тип бирки
			'alias' => 'TimeTableType_id',
		);
		$arr['lpusection_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_NULL,
			),// отделение МО
			'alias' => 'LpuSection_id',
		);
		$arr['lpusectionbedtype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_NULL,
			),// тип койки
			'alias' => 'LpuSectionBedType_id',
		);
		$arr['emstatus'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),//
			'alias' => 'TimetableStac_EmStatus',
		);
		$arr['emergencydata_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),// данные о вызове скорой помощи
			'alias' => 'EmergencyData_id',
		);
		$arr['evndirection_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),// идентификатор выписки направления
			'alias' => 'EvnDirection_id',
		);
		$arr['evn_pid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),// Учетный документ, в рамках которого было добавлено
			'alias' => 'Evn_pid',
		);
		$arr['evn_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),// Связь события как родителя
			'alias' => 'Evn_id',
		);
		return $arr;
	}

	/**
	 * 	Определение действия по типу бирки
	 */
	function defineActionTypeByTimetableType( $TimetableType_SysNick ) {
		$action = '';

		switch ( $TimetableType_SysNick ) {
			case 'free':
				$action = 'ChType_NormalBed';
				break;
			case 'reserved':
				$action = 'ChType_ReservBed';
				break;
			case 'pay':
				$action = 'ChType_PaidTicket';
				break;
			case 'vet':
				$action = 'ChType_VeteranTicket';
				break;
			case 'extr':
				$action = 'ChType_OutTicket';
				break;
			case 'emerg':
				$action = 'ChType_ExtraBed';
				break;
			case 'bed':
				$action = 'ChType_NormalBed';
				break;
		}

		return $action;
	}

	/**
	 * Изменение типа бирки в стационаре
	 */
	function setTTSType( $data ) {

		$data['object'] = 'TimetableStac';
		if ( isset($data['TimetableStacGroup']) ) {
			$data['TimetableStacGroup'] = json_decode($data['TimetableStacGroup']);
		}
		if ( isset($data['TimetableStacGroup']) && count($data['TimetableStacGroup']) > 0 ) {
			// Обработка группы бирок в отдельном методе
			return $this->setTTSTypeGroup($data);
		} else {
			if ( true === ($res = $this->checkTimetableOccupied($data)) ) {
				return array(
					'Error_Msg' => 'Бирка занята, изменение типа невозможно.'
				);
			}
		}
		
		// Получаем услугу и день, а также заодно проверяем, что бирка существует
		$res = $this->db->query("
			select
				LpuSection_id,
				TimetableStac_Day
			from v_TimetableStac_lite with (nolock)
			where TimetableStac_id = :TimetableStac_id", array(
			'TimetableStac_id' => $data['TimetableStac_id']
				)
		);

		if ( is_object($res) ) {
			$res = $res->result('array');
		} else {
			return false;
		}

		if ( !isset($res[0]) ) {
			return array(
				'Error_Msg' => 'Бирка с таким идентификатором не существует.'
			);
		} else {
			$LpuSection_id = $res[0]['LpuSection_id'];
			$Day = $res[0]['TimetableStac_Day'];
		}
		

		$tttype=$this->getFirstRowFromQuery("select TimetableType_Name from v_TimetableType with (nolock) where TimeTableType_id=?",array($data['TimetableType_id']));

		$sql = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@TimetableType_SysNick varchar(50),
				@TimetableStac_id bigint = :TimetableStac_id,
				@ErrMsg varchar(4000);
			set @Res = null;
			set @TimetableType_SysNick = (select top 1 TimetableType_SysNick from v_TimetableType (nolock) where TimetableType_id = :TimetableType_id);

			exec p_TimetableStac_setType
				@TimetableStac_id = @TimetableStac_id output,
				@TimetableType_id = :TimetableType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output
			select @Res as TimetableStac_id, @TimetableType_SysNick as TimetableType_SysNick, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$res = $this->db->query(
				$sql, array(
			'TimetableStac_id' => $data['TimetableStac_id'],
			'TimetableType_id' => $data['TimetableType_id'],
			'pmUser_id' => $data['pmUser_id']
				)
		);

		if ( is_object($res) ) {
			$resp = $res->result('array');
			if ( count($resp) > 0 && !empty($resp[0]['TimetableType_SysNick']) ) {
				$action = $this->defineActionTypeByTimetableType($resp[0]['TimetableType_SysNick']);
				if ( !empty($action) ) {
					// отправка STOMP-сообщения
					sendFerStompMessage(array(
						'id' => $data['TimetableStac_id'],
						'timeTable' => 'TimetableStac',
						'action' => $action,
						'setDate' => date("c")
							), 'Rule');
				}
			}
		}

		// Пересчет кэша коек по дням теперь прямо в хранимке

		return array(
			'TimetableType_Name'=>$tttype['TimetableType_Name'],
			'Error_Msg' => ''
		);
	}

	/**
	 * Изменение типа бирок в cтационаре для группы бирок
	 */
	function setTTSTypeGroup( $data ) {

		if ( true !== ($res = $this->checkTimetablesFree($data)) ) {
			return $res;
		}

		// Получаем врача и список дней, на которые мы выделили бирки
		$res = $this->db->query("
			select
					TimetableStac_id,
					LpuSection_id,
					TimetableStac_Day
			from v_TimetableStac_lite with (nolock)
			where TimetableStac_id in (" . implode(', ', $data['TimetableStacGroup']) . ")"
		);

		if ( is_object($res) ) {
			$res = $res->result('array');
		} else {
			return false;
		}
		// Меняем тип у каждой бирки по отдельности. Не лучший вариант конечно
		foreach ( $res as $row ) {

			$LpuSection_id = $row['LpuSection_id'];
			$Day = $row['TimetableStac_Day'];

			$sql = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@TimetableType_SysNick varchar(50),
					@TimetableStac_id bigint = :TimetableStac_id,
					@ErrMsg varchar(4000);
				set @Res = null;
				set @TimetableType_SysNick = (select top 1 TimetableType_SysNick from v_TimetableType (nolock) where TimetableType_id = :TimetableType_id);

				exec p_TimetableStac_setType
					@TimetableStac_id = @TimetableStac_id output,
					@TimetableType_id = :TimetableType_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output
				select @Res as TimetableStac_id, @TimetableType_SysNick as TimetableType_SysNick, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

			$res = $this->db->query(
					$sql, array(
				'TimetableStac_id' => $row['TimetableStac_id'],
				'TimetableType_id' => $data['TimetableType_id'],
				'pmUser_id' => $data['pmUser_id']
					)
			);

			if ( is_object($res) ) {
				$resp = $res->result('array');
				if ( count($resp) > 0 && !empty($resp[0]['TimetableType_SysNick']) ) {
					$action = $this->defineActionTypeByTimetableType($resp[0]['TimetableType_SysNick']);
					if ( !empty($action) ) {
						// отправка STOMP-сообщения
						sendFerStompMessage(array(
							'id' => $row['TimetableStac_id'],
							'timeTable' => 'TimetableStac',
							'action' => $action,
							'setDate' => date("c")
								), 'Rule');
					}
				}
			}

			// Пересчет кэша коек по дням теперь прямо в хранимке
		}

		return array(
			'Error_Msg' => ''
		);
	}

	/**
	 * Получение истории изменения бирки стационара
	 */
	function getTTSHistory( $data ) {

		$selectPersonData = "rtrim(rtrim(p.Person_Surname) + ' ' + rtrim(p.Person_Firname) + ' ' + isnull(rtrim(p.Person_Secname), '')) as Person_FIO,
					convert(varchar(10), Person_BirthDay, 104) as Person_BirthDay";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = p.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null then rtrim(rtrim(p.Person_Surname) + ' ' + rtrim(p.Person_Firname) + ' ' + isnull(rtrim(p.Person_Secname), '')) else rtrim(peh.PersonEncrypHIV_Encryp) end as Person_FIO,
					case when peh.PersonEncrypHIV_Encryp is null then convert(varchar(10), Person_BirthDay, 104) else null end as Person_BirthDay";
		}
		$sql = "
			select
				convert(varchar(10), TimetableStacHist_insDT, 104) + ' ' + convert(varchar(8), TimetableStacHist_insDT, 108) as TimetableHist_insDT,
				rtrim(PMUser_Name) as PMUser_Name,
				TimetableActionType_Name as TimetableActionType_Name,
				TimetableType_Name,
				{$selectPersonData}
			from TimetableStacHist ttsh with (nolock)
			left join v_pmUser pu with (nolock) on ttsh.TimetableStacHist_userID = pu.pmuser_id
			left join TimetableActionType ttat with (nolock) on ttat.TimetableActionType_id = ttsh.TimetableGrafAction_id
			left join v_TimetableType ttt with(nolock) on ttt.TimetableType_id = isnull(ttsh.TimetableType_id, 1)
			left join v_Person_ER p with (nolock) on ttsh.Person_id = p.Person_id
			{$joinPersonEncrypHIV}
			where TimetableStac_id = :TimetableStac_id";

		$res = $this->db->query(
				$sql, array(
			'TimetableStac_id' => $data['TimetableStac_id']
				)
		);

		if ( is_object($res) ) {
			return $res = $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение настроек стационара в указанный день
	 */
	function getStacSheduleSettings( $day, $lpuSection, $changeSettings = false, $pmUser_id = '') {
		//получим настройки на день
		$sql = "
				select top 1
					S.*
				from v_ScheduleParamStac S with(nolock)
				where
					S.ScheduleParamStac_Day = :Day
					and S.LpuSection_id = :LpuSection_id
				order by
					ScheduleParamStac_id desc
			";
		$settings_on_day = $this->getFirstRowFromQuery($sql, array(
			'Day' => $day,
			'LpuSection_id' => $lpuSection
		), true);
		
		//узнаем, существует ли расписание
		$sql = "
				select top 1
					t.TimeTableStac_id
				from v_TimetableStac t with (nolock)
				where t.TimetableStac_Day = :TimetableStac_Day
					and t.LpuSection_id = :LpuSection_id
				order by t.TimetableStac_setDate
			";
		
		$data['TimetableStac_Day'] = $day;
		$sheduleExists = $this->getFirstRowFromQuery($sql, array(
			'TimetableStac_Day' => $day,
			'LpuSection_id' => $lpuSection
		), true);
		
		$this->load->model("Options_model");
		$settings_now = $this->Options_model->getOptions();
		//если расписание существует, используем настройки на день, иначе текущие настройки
		if (!empty($settings_on_day && !empty($sheduleExists))) {
			$settings = array(
				'auto_create' => isset($settings_on_day['ScheduleParamStac_IsAuto']) ? $settings_on_day['ScheduleParamStac_IsAuto'] : null,
				'is_time' => isset($settings_on_day['ScheduleParamStac_IsTime']) ? $settings_on_day['ScheduleParamStac_IsTime'] : '',
				'duration_type' => $settings_now['stac']['stac_schedule_priority_duration']
			);
		}
		else {
			$settings = array(
				'auto_create' => $settings_now['stac']['stac_schedule_auto_create']+1,
				'is_time' => $settings_now['stac']['stac_schedule_time_binding'],
				'duration_type' => $settings_now['stac']['stac_schedule_priority_duration']
			);
			
			if ($changeSettings && !empty($pmUser_id)) {
				//создать запись о параметрах системы на текущий момент, т.к. расписания не существует
				$act = !empty($settings_on_day) ? 'upd':'ins';
				$sql = "
						declare
							@Res bigint,
							@ErrCode bigint,
							@ErrMsg varchar(4000);
						set @Res = null;
					
						exec p_ScheduleParamStac_{$act}
							@ScheduleParamStac_id = :ScheduleParamStac_id,
							@LpuSection_id = :LpuSection_id,
							@ScheduleParamStac_Day = :ScheduleParamStac_Day,
							@ScheduleParamStac_IsTime = :ScheduleParamStac_IsTime,
							@ScheduleParamStac_IsAuto = :ScheduleParamStac_IsAuto,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output;
						select @Res as ScheduleParamStac_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
					";
				$this->db->query(
					$sql, array(
						'ScheduleParamStac_id' =>  !empty($settings_on_day) ? $settings_on_day['ScheduleParamStac_id'] : null,
						'LpuSection_id' => $lpuSection,
						'ScheduleParamStac_IsTime' => $settings['is_time'],
						'ScheduleParamStac_IsAuto' => $settings['auto_create'],
						'ScheduleParamStac_Day' => $day,
						'pmUser_id' => $pmUser_id
					)
				);
			}
			
		}
		return $settings;
	}

	/**
	 * Удаление бирки стационара
	 */
	function Delete( $data ) {
		$data['object'] = 'TimetableStac';
		if ( isset($data['TimetableStac_id']) ) {
			$data['TimetableStacGroup'] = array($data['TimetableStac_id']);
		} else {
			$data['TimetableStacGroup'] = json_decode($data['TimetableStacGroup']);
		}

		if ( true !== ($res = $this->checkTimetablesFree($data)) ) {
			return $res;
		}

		// Получаем врача и список дней, на которые мы выделили бирки
		$res = $this->db->query("
			select
				TimetableStac_id,
				LpuSection_id,
				TimetableStac_Day
			from v_TimetableStac_lite with (nolock)
			where TimetableStac_id in (" . implode(', ', $data['TimetableStacGroup']) . ")"
		);

		if ( is_object($res) ) {
			$res = $res->result('array');
		} else {
			return false;
		}

		// Удаляем каждую бирку по отдельности. Не лучший вариант конечно
		foreach ( $res as $row ) {

			$LpuSection_id = $row['LpuSection_id'];
			$Day = $row['TimetableStac_Day'];

			//Удаляем бирку
			$sql = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec p_TimetableStac_del
					@TimetableStac_id = :TimetableStac_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$res = $this->db->query(
					$sql, array(
				'TimetableStac_id' => $row['TimetableStac_id'],
				'pmUser_id' => $data['pmUser_id']
					)
			);

			if ( is_object($res) ) {
				$resp = $res->result('array');
				if ( count($resp) > 0 && empty($resp[0]['Error_Msg']) ) {
					// отправка STOMP-сообщения
					sendFerStompMessage(array(
						'id' => $row['TimetableStac_id'],
						'timeTable' => 'TimetableStac',
						'action' => 'DelTicket',
						'setDate' => date("c")
							), 'Rule');
				}
			}

			// Пересчет кэша коек по дням теперь прямо в хранимке
		}

		return array(
			'success' => true
		);
	}

	/**
	 * Получение расписания стационара для редактирования
	 */
	function getTimetableStacForEdit( $data ) {
		$outdata = array();

		$StartDay = isset($data['StartDay']) ? strtotime($data['StartDay']) : time();

		$outdata['StartDay'] = $StartDay;

		$param['StartDay'] = TimeToDay($StartDay);
		$param['EndDay'] = TimeToDay(strtotime("+21 days", $StartDay));
		$param['LpuSection_id'] = $data['LpuSection_id'];

		$nTime = $StartDay;


		$outdata['header'] = array();
		$outdata['descr'] = array();
		$outdata['data'] = array();
		$outdata['occupied'] = array();
		for ( $nCol = 0; $nCol < 21; $nCol++ ) {
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
				lsd.Day_id,
				rtrim(lsd.LpuSectionDay_Descr) as LpuSectionDay_Descr,
				rtrim(u.pmUser_Name) as pmUser_Name,
				lsd.LpuSectionDay_updDT
			from LpuSectionDay lsd with (nolock)
			left join v_pmUser u with(nolock) on u.pmUser_id = lsd.pmUser_updID
			where LpuSection_id = :LpuSection_id
				and Day_id >= :StartDay
				and Day_id < :EndDay ";

		$res = $this->db->query($sql, $param);

		$daydescrdata = $res->result('array');

		foreach ( $daydescrdata as $day ) {
			$outdata['descr'][$day['Day_id']] = array(
				'LpuSectionDay_Descr' => $day['LpuSectionDay_Descr'],
				'pmUser_Name' => $day['pmUser_Name'],
				'LpuSectionDay_updDT' => isset($day['LpuSectionDay_updDT']) ? $day['LpuSectionDay_updDT']->format("d.m.Y H:i") : ''
			);
		}
		$selectPersonData = "
				p.Person_BirthDay,
				p.Person_Phone,
				p.PrivilegeType_id,
				rtrim(p.Person_Firname) as Person_Firname,
				rtrim(p.Person_Surname) as Person_Surname,
				rtrim(p.Person_Secname) as Person_Secname,";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = p.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null then p.Person_BirthDay else null end as Person_BirthDay,
				case when peh.PersonEncrypHIV_Encryp is null then p.Person_Phone else null end as Person_Phone,
				case when peh.PersonEncrypHIV_Encryp is null then p.PrivilegeType_id else null end as PrivilegeType_id,
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Surname) else rtrim(peh.PersonEncrypHIV_Encryp) end as Person_Surname,
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Firname) else '' end as Person_Firname,
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Secname) else '' end as Person_Secname,";
		}
		$resAll = array();
		for ($day = $param['StartDay']; $day < $param['EndDay']; $day++) {
			$settings = $this->getStacSheduleSettings($day, $data['LpuSection_id']);
			
			$dayParams = array(
				'currentDay' => $day,
				'LpuSection_id' => $param['LpuSection_id'],
				'recordCount' => ($settings['auto_create'] == 2) ?
					$this->getRecordCountForDay($param['LpuSection_id'], date('Y-m-d', DayMinuteToTime($day, 0))) : 100
			);
			$sql = "
				select
					t.pmUser_updID,
					t.TimetableStac_updDT,
					t.TimetableStac_id,
					t.Person_id,
					t.TimetableStac_Day,
					convert(varchar(10), t.TimetableStac_setDate,104) as TimetableStac_setDate,
					t.TimetableType_id,
					{$selectPersonData}
					t.PMUser_UpdID,
					case
						when t.pmUser_updid=999000
						then 'Запись через КМИС'
						when t.pmUser_updid between 1000000 and 5000000
						then 'Запись через интернет'
						else u.PMUser_Name
					end as PMUser_Name,
					lpud.Lpu_Nick as DirLpu_Nick,
					d.EvnDirection_Num as Direction_Num,
					convert(varchar(10), d.EvnDirection_setDT,104) as Direction_Date,
					d.EvnDirection_id as EvnDirection_id,
					qp.pmUser_Name as QpmUser_Name,
					q.EvnQueue_insDT as EvnQueue_insDT,
					dg.Diag_Code,
					u.Lpu_id as pmUser_Lpu_id,
					t.LpuSectionBedType_id,
					btype.LpuSectionBedType_Name,
					t.TimetableExtend_Descr,
					t.TimetableExtend_updDT,
					ud.pmUser_Name as TimetableExtend_pmUser_Name
				from v_TimetableStac t with (nolock)
				outer apply (
					(select count(*) as cnt
						from v_TimetableStac
						where
							TimetableStac_Day = t.TimetableStac_Day
							and LpuSection_id = t.LpuSection_id
							and TimetableStac_id <= t.TimetableStac_id
							and isnull(TimeTableStac_IsDop, 1) = 1
							and Person_id is null
							and isnull(t.TimeTableStac_IsDop, 1) = 1
							and t.Person_id is null
					)
				) cntRegular
				left outer join v_Person_ER2 p with (nolock) on t.Person_id = p.Person_id
				left outer join v_pmUser u with (nolock) on t.PMUser_UpdID = u.PMUser_id
				left outer join v_pmUser ud with (nolock) on t.TimetableExtend_pmUser_updid = ud.PMUser_id
				left join v_EvnDirection d with (nolock) on
					t.EvnDirection_id = d.EvnDirection_id
					and d.DirFailType_id is null
				left join v_Lpu lpud with (nolock) ON lpud.Lpu_id = d.Lpu_id
				left join v_EvnQueue q with (nolock) on
					t.TimetableStac_id = q.TimetableStac_id
					and t.Person_id = q.Person_id
				left join v_pmUser qp with (nolock) on q.pmUser_updId=qp.pmUser_id
				left join Diag dg with (nolock) on dg.Diag_id=d.Diag_id
				left join v_LpuSectionBedType btype (nolock) on btype.LpuSectionBedType_id = t.LpuSectionBedType_id
				{$joinPersonEncrypHIV}
				where t.TimetableStac_Day = :currentDay
					and t.LpuSection_id = :LpuSection_id
					and cntRegular.cnt <= :recordCount
				order by t.TimetableStac_setDate";
			
			$res = $this->db->query($sql, array_merge($param, $dayParams))->result('array');
			
			$resAll = array_merge($resAll, $res);
		}
		$ttpdata = $resAll;

		foreach ( $ttpdata as $ttp ) {
			$outdata['data'][$ttp['TimetableStac_Day']][] = $ttp;
			if ( isset($ttp['Person_id']) ) {
				$outdata['occupied'][$ttp['TimetableStac_Day']] = true;
			}
		}

		$sql = "
			select TimetableStac_id from TimetableLock with(nolock) where TimetableStac_id is not null";

		$res = $this->db->query($sql);

		$outdata['reserved'] = array();
		$reserved = $res->result('array');
		foreach ( $reserved as $lock ) {
			$outdata['reserved'][] = $lock['TimetableStac_id'];
		}

		return $outdata;
	}

	/**
	 * Получаем количество незанятых коек
	 */
	function getRecordCountForDay( $lpuSection, $day ) {
		//коечный фонд
		$sql = "
			select
				sum(LpuSectionBedState_Plan) as LpuSectionBedState_PlanCount,
				sum(LpuSectionBedState_Fact) as LpuSectionBedState_Fact
			from
				v_LpuSectionBedState LSBS with (nolock)
				left join v_LpuSection LSS with(nolock) on LSS.LpuSection_id = LSBS.LpuSection_id
			where
				LSBS.LpuSectionBedState_begDate <=cast(:currentDay as date) and (LSBS.LpuSectionBedState_endDate >= cast(:currentDay as date) or LSBS.LpuSectionBedState_endDate is null) and
				LSBS.LpuSection_id=:LpuSection_id
		";
		$res = $this->db->query($sql, array(
			'LpuSection_id' => $lpuSection,
			'currentDay' => $day
		))->result('array');
		$kf = $res[0]['LpuSectionBedState_Fact'];
		
		//занятые койки в отделении
		$sql = "
			select
				count(*) as cnt
			from
				v_EvnSection es with(nolock)
			where
				cast(es.EvnSection_setDate as date) <= :currentDay
				and es.EvnSection_disDate is null
				and (es.EvnSection_PlanDisDT is null or es.EvnSection_PlanDisDT > :currentDay)
				and es.LpuSection_id = :LpuSection_id
		";
		$res = $this->db->query($sql, array(
			'LpuSection_id' => $lpuSection,
			'currentDay' => $day
		))->result('array');
		$busy = $res[0]['cnt'];
		
		//занятые койки по необслуженным биркам
		$sql = "
			select
				count(*) as cnt
			from
				v_TimeTableStac tts with(nolock)
				inner join v_EvnDirection_all ED with(nolock) on ED.EvnDirection_id = tts.EvnDirection_id
				left join v_EvnStatus ES with(nolock) on ES.EvnStatus_id = ED.EvnStatus_id
			where
				tts.LpuSection_id = :LpuSection_id
				and tts.Person_id is not null
				and tts.TimeTableStac_CureDuration > 0
				and :currentDay between cast(tts.TimeTableStac_setDate as date) and dateadd(day, tts.TimeTableStac_CureDuration, cast(tts.TimeTableStac_setDate as date))
				and ES.EvnStatus_SysNick <> 'Serviced'
		";
		$res = $this->db->query($sql, array(
			'LpuSection_id' => $lpuSection,
			'currentDay' => $day
		))->result('array');
		$busy += $res[0]['cnt'];
		
		return $kf - $busy;
	}
	
	/**
	 * Получение сводного расписания стационара
	 */
	function getTimetableStacSummary( $data ) {
		$outdata = array();

		$StartDay = isset($data['StartDay']) ? strtotime($data['StartDay']) : time();

		$outdata['StartDay'] = $StartDay;

		$param['StartDay'] = TimeToDay($StartDay);
		$param['EndDay'] = TimeToDay(strtotime("+21 days", $StartDay));
		$param['Lpu_id'] = $data['Lpu_id'];

		$nTime = $StartDay;


		$outdata['header'] = array();
		$outdata['descr'] = array();
		$outdata['data'] = array();
		$outdata['occupied'] = array();
		for ( $nCol = 0; $nCol < 21; $nCol++ ) {
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
		
		$selectPersonData = "
				p.Person_BirthDay,
				p.Person_Phone,
				p.PrivilegeType_id,
				rtrim(p.Person_Firname) as Person_Firname,
				rtrim(p.Person_Surname) as Person_Surname,
				rtrim(p.Person_Secname) as Person_Secname,";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = p.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null then p.Person_BirthDay else null end as Person_BirthDay,
				case when peh.PersonEncrypHIV_Encryp is null then p.Person_Phone else null end as Person_Phone,
				case when peh.PersonEncrypHIV_Encryp is null then p.PrivilegeType_id else null end as PrivilegeType_id,
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Surname) else rtrim(peh.PersonEncrypHIV_Encryp) end as Person_Surname,
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Firname) else '' end as Person_Firname,
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Secname) else '' end as Person_Secname,";
		}
		$sql = "
			select
				t.pmUser_updID,
				t.TimetableStac_updDT,
				t.TimetableStac_id,
				t.Person_id,
				t.TimetableStac_Day,
				t.TimetableStac_setDate as TimetableStac_setDate,
				t.TimetableType_id,
				{$selectPersonData}
				t.PMUser_UpdID,
				case
					when t.pmUser_updid=999000
					then 'Запись через КМИС'
					when t.pmUser_updid between 1000000 and 5000000
					then 'Запись через интернет'
					else u.PMUser_Name
				end as PMUser_Name,
				lpud.Lpu_Nick as DirLpu_Nick,
				d.EvnDirection_Num as Direction_Num,
				convert(varchar(10), d.EvnDirection_setDT,104) as Direction_Date,
				d.EvnDirection_id as EvnDirection_id,
				qp.pmUser_Name as QpmUser_Name,
				q.EvnQueue_insDT as EvnQueue_insDT,
				dg.Diag_Code,
				u.Lpu_id as pmUser_Lpu_id,
				t.LpuSectionBedType_id,
				btype.LpuSectionBedType_Name,
				t.TimetableExtend_Descr,
				t.TimetableExtend_updDT,
				ls.LpuSection_FullName as LpuSectionName,
				ud.pmUser_Name as TimetableExtend_pmUser_Name
			from v_TimetableStac t with (nolock)
			left outer join v_Person_ER2 p with (nolock) on t.Person_id = p.Person_id
			left outer join v_pmUser u with (nolock) on t.PMUser_UpdID = u.PMUser_id
			left outer join v_pmUser ud with (nolock) on t.TimetableExtend_pmUser_updid = ud.PMUser_id
			left join v_EvnDirection d with (nolock) on
				t.EvnDirection_id = d.EvnDirection_id
				and d.DirFailType_id is null
			left join v_Lpu lpud with (nolock) ON lpud.Lpu_id = d.Lpu_id
			left join v_LpuSection ls with (nolock) ON ls.LpuSection_id = t.LpuSection_id
			left join v_EvnQueue q with (nolock) on
				t.TimetableStac_id = q.TimetableStac_id
				and t.Person_id = q.Person_id
			left join v_pmUser qp with (nolock) on q.pmUser_updId=qp.pmUser_id
			left join Diag dg with (nolock) on dg.Diag_id=d.Diag_id
			left join v_LpuSectionBedType btype (nolock) on btype.LpuSectionBedType_id = t.LpuSectionBedType_id
			{$joinPersonEncrypHIV}
			where t.TimetableStac_Day >= :StartDay
				and u.Lpu_id = :Lpu_id
				and t.TimetableStac_Day < :EndDay
			order by t.TimetableStac_setDate";

		$res = $this->db->query($sql, $param);

		//echo getDebugSql($sql, $param);
		//die();
		$ttpdata = $res->result('array');


		foreach ( $ttpdata as $ttp ) {
			$outdata['data'][$ttp['TimetableStac_Day']][] = $ttp;
			if ( isset($ttp['Person_id']) ) {
				$outdata['occupied'][$ttp['TimetableStac_Day']] = true;
			}
		}

		$sql = "
			select TimetableStac_id from TimetableLock with(nolock) where TimetableStac_id is not null";

		$res = $this->db->query($sql);

		$outdata['reserved'] = array();
		$reserved = $res->result('array');
		foreach ( $reserved as $lock ) {
			$outdata['reserved'][] = $lock['TimetableStac_id'];
		}

		return $outdata;
	}
	/**
	 * Получение расписания стационара для просмотра/редактирования
	 */
	function getTimetableStac( $data ) {
		$outdata = array();

		$StartDay = isset($data['StartDay']) ? strtotime($data['StartDay']) : time();

		$outdata['StartDay'] = $StartDay;

		$param['StartDay'] = TimeToDay($StartDay);
		$param['LpuSection_id'] = $data['LpuSection_id'];

		$nTime = $StartDay;
		
		$msflpu = $this->getFirstRowFromQuery("select Lpu_id from v_LpuSection with (nolock) where LpuSection_id = ?", array($data['LpuSection_id']));

		if (empty($_SESSION['setting']) || empty($_SESSION['setting']['server'])) { // Вынес отдельно, чтобы не повторять
			$maxDays = null;
		
		} elseif (!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] == 'regpol' && $_SESSION['lpu_id'] == $msflpu['Lpu_id']) { // Для регистратора запись в свою МО
			$this->load->model('LpuIndividualPeriod_model', 'lipmodel');
			$individualPeriod = $this->lipmodel->getObjectIndividualPeriod(array('Lpu_id' => $_SESSION['lpu_id']), 'LpuSection');

			if( !empty($data['LpuSection_id']) && !empty($individualPeriod[$data['LpuSection_id']]) ) {
				$maxDays = $individualPeriod[$data['LpuSection_id']];
			} else {
				$maxDays = !empty($_SESSION['setting']['server']['stac_record_day_count']) ? $_SESSION['setting']['server']['stac_record_day_count'] : null;
			}
			
		} elseif (!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] == 'regpol') { // Для регистратора запись в чужую МО
			$maxDays = !empty($_SESSION['setting']['server']['stac_record_day_count_reg']) ? $_SESSION['setting']['server']['stac_record_day_count_reg'] : null;
			
		} elseif ($_SESSION['lpu_id'] == $msflpu['Lpu_id']) { // Для остальных пользовалелей запись в свою МО
			$maxDays = !empty($_SESSION['setting']['server']['stac_record_day_count_own']) ? $_SESSION['setting']['server']['stac_record_day_count_own'] : null;
			
		} else { // Для остальных пользовалелей запись в чужую МО
			$maxDays = !empty($_SESSION['setting']['server']['stac_record_day_count_other']) ? $_SESSION['setting']['server']['stac_record_day_count_other'] : null;
			
		}
		if ( date("H:i") >= getShowNewDayTime() && $maxDays ) $maxDays++;
		$param['EndDayDefault'] = TimeToDay(strtotime("+21 days", $StartDay));
		$param['EndDay'] = !empty($maxDays) ? TimeToDay(strtotime("+".$maxDays." days", time())) : $param['EndDayDefault'];

		if ( $param['EndDay'] > $param['EndDayDefault'] ) {
			// чтобы не уходило в бесконечный цикл в timetablestac_data.php
			$param['EndDay'] = $param['EndDayDefault'];
		}

		$outdata['header'] = array();
		$outdata['descr'] = array();
		$outdata['data'] = array();
		$outdata['occupied'] = array();
		for ( $nCol = 0; $nCol < 21; $nCol++ ) {
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
				lsd.Day_id,
				rtrim(lsd.LpuSectionDay_Descr) as LpuSectionDay_Descr,
				rtrim(u.pmUser_Name) as pmUser_Name,
				lsd.LpuSectionDay_updDT
			from LpuSectionDay lsd with (nolock)
			left join v_pmUser u with(nolock) on u.pmUser_id = lsd.pmUser_updID
			where LpuSection_id = :LpuSection_id
				and Day_id >= :StartDay
				and Day_id < :EndDay ";

		$res = $this->db->query($sql, $param);

		$daydescrdata = $res->result('array');

		foreach ( $daydescrdata as $day ) {
			$outdata['descr'][$day['Day_id']] = array(
				'LpuSectionDay_Descr' => $day['LpuSectionDay_Descr'],
				'pmUser_Name' => $day['pmUser_Name'],
				'LpuSectionDay_updDT' => isset($day['LpuSectionDay_updDT']) ? $day['LpuSectionDay_updDT']->format("d.m.Y H:i") : ''
			);
		}
		
		$lpuSectionBedTypeSql = '';
		//фильтрация записей по полу переданного Person_id
		if (!empty($data['Person_id'])) {
			$param['Person_id'] = $data['Person_id'];
			$lpuSectionBedTypeSql = "and (t.LpuSectionBedType_id in (3, (select Sex_id from v_PersonState where Person_id = :Person_id)))";
		}

		$selectPersonData = "
				p.Person_BirthDay,
				p.Person_Phone,
				p.PrivilegeType_id,
				rtrim(p.Person_Firname) as Person_Firname,
				rtrim(p.Person_Surname) as Person_Surname,
				rtrim(p.Person_Secname) as Person_Secname,";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = p.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null then p.Person_BirthDay else null end as Person_BirthDay,
				case when peh.PersonEncrypHIV_Encryp is null then p.Person_Phone else null end as Person_Phone,
				case when peh.PersonEncrypHIV_Encryp is null then p.PrivilegeType_id else null end as PrivilegeType_id,
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Surname) else rtrim(peh.PersonEncrypHIV_Encryp) end as Person_Surname,
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Firname) else '' end as Person_Firname,
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Secname) else '' end as Person_Secname,";
		}
		$resAll = array();
		for ($day = $param['StartDay']; $day < $param['EndDay']; $day++) {
			$settings = $this->getStacSheduleSettings($day, $data['LpuSection_id']);
			
			$dayParams = array(
				'currentDay' => $day,
				'LpuSection_id' => $param['LpuSection_id'],
				'recordCount' => ($settings['auto_create'] == 2) ?
					$this->getRecordCountForDay($param['LpuSection_id'], date('Y-m-d', DayMinuteToTime($day, 0))) : 100
			);
			$sql = "
				select
					t.pmUser_updID,
					t.TimetableStac_updDT,
					t.TimetableStac_id,
					t.Person_id,
					t.TimetableStac_Day,
					t.TimetableStac_setDate as TimetableStac_setDate,
					t.TimetableType_id,
					{$selectPersonData}
					t.PMUser_UpdID,
					case
						when t.pmUser_updid=999000
						then 'Запись через КМИС'
						when t.pmUser_updid between 1000000 and 5000000
						then 'Запись через интернет'
						else u.PMUser_Name
					end as PMUser_Name,
					lpud.Lpu_Nick as DirLpu_Nick,
					d.EvnDirection_Num as Direction_Num,
					convert(varchar(10), d.EvnDirection_setDT,104) as Direction_Date,
					d.EvnDirection_id,
					qp.pmUser_Name as QpmUser_Name,
					q.EvnQueue_insDT as EvnQueue_insDT,
					dg.Diag_Code,
					u.Lpu_id as pmUser_Lpu_id,
					t.LpuSectionBedType_id,
					t.TimetableExtend_Descr,
					t.TimetableExtend_updDT,
					ud.pmUser_Name as TimetableExtend_pmUser_Name
				from v_TimetableStac t with (nolock)
				outer apply (
					(select count(*) as cnt
						from v_TimetableStac
						where
							TimetableStac_Day = t.TimetableStac_Day
							and LpuSection_id = t.LpuSection_id
							and TimetableStac_id <= t.TimetableStac_id
							and isnull(TimeTableStac_IsDop, 1) = 1
							and Person_id is null
							and isnull(t.TimeTableStac_IsDop, 1) = 1
							and t.Person_id is null
					)
				) cntRegular
				left outer join v_Person_ER2 p with (nolock) on t.Person_id = p.Person_id
				left outer join v_pmUser u with (nolock) on t.PMUser_UpdID = u.PMUser_id
				left outer join v_pmUser ud with (nolock) on t.TimetableExtend_pmUser_updid = ud.PMUser_id
				left join v_EvnDirection_all d with (nolock) on
					t.EvnDirection_id = d.EvnDirection_id
					and d.DirFailType_id is null
					and isnull(d.EvnDirection_isAuto, 1) != 2
				left join v_Lpu lpud with (nolock) ON lpud.Lpu_id = d.Lpu_id
				left join v_EvnQueue q with (nolock) on
					t.TimetableStac_id = q.TimetableStac_id
					and t.Person_id = q.Person_id
				left join v_pmUser qp with (nolock) on q.pmUser_updId=qp.pmUser_id
				left join Diag dg with (nolock) on dg.Diag_id=d.Diag_id
				{$joinPersonEncrypHIV}
				where t.TimetableStac_Day = :currentDay
					and t.LpuSection_id = :LpuSection_id
					and cntRegular.cnt <= :recordCount
					{$lpuSectionBedTypeSql}
				order by t.TimetableStac_setDate";

			$res = $this->db->query($sql, array_merge($param, $dayParams))->result('array');
			
			$resAll = array_merge($resAll, $res);
		}
		$ttpdata = $resAll;

		foreach ( $ttpdata as $ttp ) {
			$outdata['data'][$ttp['TimetableStac_Day']][] = $ttp;
			if ( isset($ttp['Person_id']) ) {
				$outdata['occupied'][$ttp['TimetableStac_Day']] = true;
			}
		}

		$sql = "
			select TimetableStac_id from TimetableLock with(nolock) where TimetableStac_id is not null";

		$res = $this->db->query($sql);

		$outdata['reserved'] = array();
		$reserved = $res->result('array');
		foreach ( $reserved as $lock ) {
			$outdata['reserved'][] = $lock['TimetableStac_id'];
		}

		return $outdata;
	}

	/**
	 * Получение комментария на день для врача
	 */
	function getTTSDayComment( $data ) {

		$sql = "
			select
				lsd.LpuSectionDay_Descr,
				lsd.LpuSectionDay_id
			from LpuSectionDay lsd with (nolock)
			where LpuSection_id = :LpuSection_id
				and Day_id = :Day_id";

		$res = $this->db->query(
				$sql, array(
			'LpuSection_id' => $data['LpuSection_id'],
			'Day_id' => $data['Day']
				)
		);

		if ( is_object($res) ) {
			return $res = $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение комментария на день
	 */
	function saveTTSDayComment( $data ) {

		$sql = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			exec p_LpuSectionDay_setDescr
				@Server_id = 2,
				@LpuSectionDay_id = @Res,
				@Day_id = :Day_id,
				@LpuSection_id = :LpuSection_id,
				@LpuSectionDay_Descr = :LpuSectionDay_Descr,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output";

		$res = $this->db->query(
				//echo getDebugSQL(
				$sql, array(
			'LpuSectionDay_Descr' => $data['LpuSectionDay_Descr'],
			'LpuSection_id' => $data['LpuSection_id'],
			'Day_id' => $data['Day'],
			'pmUser_id' => $data['pmUser_id']
				)
		);

		return array(
			'Error_Msg' => ''
		);
	}

	/**
	 * Создания расписания в стационаре
	 */
	function createTTSSchedule( $data ) {

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
		
		if (strtotime($data['CreateDateRange'][0]) < strtotime(date('d.m.Y'))) {
			return array(
				'Error_Msg' => 'Создание расписания на прошедшие периоды невозможно'
			);
		}

		// @task https://redmine.swan.perm.ru/issues/84969
		// Закомментировал
		/*if ( true !== ($res = $this->checkTimetableStacDayNotOccupied($data)) ) {
			return $res;
		}*/
		
		for ( $day = $data['StartDay']; $day <= $data['EndDay']; $day ++ ) {
			//узнаем настройки
			$settings = $this->getStacSheduleSettings($day, $data['LpuSection_id'], true, $data['pmUser_id']);
			
			//запрос на добавление бирок
			$sqlAddRecords = "
					declare
						@Res bigint,
						@ErrCode bigint,
						@ErrMsg varchar(4000);
					set @Res = null;
		
					exec p_TimeTableStac_fill
						@LpuSection_id = :LpuSection_id,
						@TimetableStac_Day = :TimetableStac_Day,
						@LpuSectionBedTypeCommon_Count = :LpuSectionBedTypeCommon_Count,
						@LpuSectionBedTypeMan_Count = :LpuSectionBedTypeMan_Count,
						@LpuSectionBedTypeWoman_Count = :LpuSectionBedTypeWoman_Count,
						@TimetableType_id = :TimetableType_id,
						@StartTime = :StartTime,
						@EndTime = :EndTime,
						@TimeTableStac_Time = :TimeTableStac_Time,
						@TimeTableStac_IsDop = :TimeTableStac_IsDop,
						@TimetableExtend_Descr = :TimetableExtend_Descr,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output;
					select @Res as TimetablePar_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
				";
			
			if ($settings['auto_create'] == 2) {
				//получаем количество коек
				$sql = "
					Select
					-- Общее количество коек в отделении, план
					sum(LpuSectionBedState_PlanCount) as LpuSection_CommonCount,
					-- Из них по основному профилю
					sum(LpuSectionBedState_ProfileCount) as LpuSection_ProfileCount,
					-- Из них узких коек
					sum(LpuSectionBedState_UzCount) as LpuSection_UzCount,
					-- Общее количество коек в отделении, факт
					sum(LpuSectionBedState_Fact) as LpuSection_Fact,
					-- Общее количество коек по палатам
					sum(LpuSectionWard_BedCount) as LpuSection_BedCount,
					-- Общее количество мужских коек
					sum(LpuSectionWard_Male) as LpuSectionWard_Male,
					-- Общее количество женских коек
					sum(LpuSectionWard_Female) as LpuSectionWard_Female,
					-- Общее количество коек по палатам
					sum(LpuSectionWard_BedCount) as LpuSectionWard_Common,
					-- Из них на ремонте
					sum(LpuSectionWard_BedRepair) as LpuSection_BedRepair,
					-- Плановый резерв коек для экстренных госпитализаций, не более
					isnull(LS.LpuSection_MaxEmergencyBed, 0) as LpuSection_MaxEmergencyBed
			
					from v_LpuSection LS with (nolock)
					outer apply (
						Select
							sum(LpuSectionWard_BedCount) as LpuSectionWard_BedCount,
							sum(LpuSectionWard_BedRepair) as LpuSectionWard_BedRepair
						from v_LpuSectionWard LSW with (nolock)
						where
							LSW.LpuSectionWard_setDate <=cast(dbo.tzGetDate() as date) and (LSW.LpuSectionWard_disDate >= cast(dbo.tzGetDate() as date) or LSW.LpuSectionWard_disDate is null) and
							LSW.LpuSection_id=LS.LpuSection_id
					) LSW
					outer apply (
						Select
							sum(LpuSectionWard_BedCount) as LpuSectionWard_Male
						from v_LpuSectionWard LSW with (nolock)
						where
							LSW.LpuSectionWard_setDate <=cast(dbo.tzGetDate() as date) and (LSW.LpuSectionWard_disDate >= cast(dbo.tzGetDate() as date) or LSW.LpuSectionWard_disDate is null) and
							LSW.LpuSection_id=LS.LpuSection_id
							and Sex_id = 1
					) LSWM
					outer apply (
						Select
							sum(LpuSectionWard_BedCount) as LpuSectionWard_Female
						from v_LpuSectionWard LSW with (nolock)
						where
							LSW.LpuSectionWard_setDate <=cast(dbo.tzGetDate() as date) and (LSW.LpuSectionWard_disDate >= cast(dbo.tzGetDate() as date) or LSW.LpuSectionWard_disDate is null) and
							LSW.LpuSection_id=LS.LpuSection_id
							and Sex_id = 2
					) LSWF
					outer apply (
						Select
							sum(LpuSectionBedState_Plan) as LpuSectionBedState_PlanCount,
							sum(case when LSBS.LpuSectionProfile_id = LSS.LpuSectionProfile_id then LpuSectionBedState_Plan else 0 end) as LpuSectionBedState_ProfileCount,
							sum(case when LSBS.LpuSectionProfile_id != LSS.LpuSectionProfile_id then LpuSectionBedState_Plan else 0 end) as LpuSectionBedState_UzCount,
							sum(LpuSectionBedState_Fact) as LpuSectionBedState_Fact
			
						from v_LpuSectionBedState LSBS with (nolock)
						left join v_LpuSection LSS with(nolock) on LSS.LpuSection_id = LSBS.LpuSection_id
						where
							LSBS.LpuSectionBedState_begDate <=cast(dbo.tzGetDate() as date) and (LSBS.LpuSectionBedState_endDate >= cast(dbo.tzGetDate() as date) or LSBS.LpuSectionBedState_endDate is null) and
							LSBS.LpuSection_id=LS.LpuSection_id
					) LSBS
					where
					LpuSection_id=:LpuSection_id
					group by LpuSection_MaxEmergencyBed
				";
				$res = $this->getFirstRowFromQuery($sql, $data);
				$data["AllBeds"] = $res['LpuSection_Fact'];
				$data["ManBeds"] = !empty($res['LpuSectionWard_Male']) ? $res['LpuSectionWard_Male']:0;
				$data["WomanBeds"] = !empty($res['LpuSectionWard_Female']) ? $res['LpuSectionWard_Female']:0;
				$data["CommonBeds"] = $res['LpuSection_Fact']-$data["ManBeds"]-$data["WomanBeds"];
				
				//количество дополнительных бирок
				$nDopBeds = $data["Faster"]+$data["Regular"];
				
				//узнаем, существует ли расписание
				$sql = "
					select top 1
						t.TimeTableStac_id
					from v_TimetableStac t with (nolock)
					where t.TimetableStac_Day = :TimetableStac_Day
						and t.LpuSection_id = :LpuSection_id
					order by t.TimetableStac_setDate
				";
				
				$data['TimetableStac_Day'] = $day;
				$res = $this->getFirstRowFromQuery($sql, $data, true);
				if (!empty($res)) {
					//если расписание уже существует
					
					//проверка на пересечение по времени
					$sql = "
						select
							count(*) as cnt
						from v_TimetableStac t with (nolock)
						where
							t.TimetableStac_Day = :TimetableStac_Day
							and t.LpuSection_id = :LpuSection_id
							and t.TimeTableStac_setDate between :StartTime and :EndTime
						";
					
					$res = $this->db->query(
						$sql,
						array(
							'StartTime' => date( "Y-m-d H:i:s",
								DayMinuteToTime( $day,
									StringToTime( $data['StartTime'] ) ) ),
							'EndTime' => date( "Y-m-d H:i:s",
								DayMinuteToTime( $day,
									StringToTime( $data['EndTime'] ) ) ),
							'TimetableStac_Day' => $data['TimetableStac_Day'],
							'LpuSection_id' => $data['LpuSection_id']
						)
					);
					if ( is_object( $res ) ) {
						$res = $res->result( 'array' );
					}
					if ( $res[0]['cnt'] > 0 ) {
						$date = date( "d.m.Y", DayMinuteToTime($day, 0));
						return array(
							'Error_Msg' => "В заданном интервале времени на дату $date уже существуют бирки."
						);
					}
					
					//узнаем количество незанятых коек
					$sql = "
						select
							Record_Male,
							Record_Female,
							Record_Regular
						from
							(
								select
									count(LpuSectionBedType_id) as Record_Male
								from v_TimetableStac t with (nolock)
								where
									t.TimetableStac_Day = :TimetableStac_Day
									and t.LpuSection_id = :LpuSection_id
									and isnull(TimeTableStac_IsDop, 1) = 1
									and t.LpuSectionBedType_id = 1
							) TSM
							outer apply (
								select
									count(LpuSectionBedType_id) as Record_Female
								from v_TimetableStac t with (nolock)
								where
									t.TimetableStac_Day = :TimetableStac_Day
									and t.LpuSection_id = :LpuSection_id
									and isnull(TimeTableStac_IsDop, 1) = 1
									and t.LpuSectionBedType_id = 2
							) TSF
							outer apply (
								select
									count(LpuSectionBedType_id) as Record_Regular
								from v_TimetableStac t with (nolock)
								where
									t.TimetableStac_Day = :TimetableStac_Day
									and t.LpuSection_id = :LpuSection_id
									and isnull(TimeTableStac_IsDop, 1) = 1
									and t.LpuSectionBedType_id = 3
							) TSR
						group by
							Record_Male,
							Record_Female,
							Record_Regular
					";
					$res = $this->getFirstRowFromQuery($sql, $data);
					$data["ManBeds"] -= $res['Record_Male'];
					$data["WomanBeds"] -= $res['Record_Female'];
					$data["CommonBeds"] -= $res['Record_Regular'];
					
					//если с привязкой ко времени
					if ($settings['is_time'] == 1) {
						$StartTime = $data['StartTime'];
						$EndTime = $data['EndTime'];
						$Duration = $data['Duration'];
						
						//максимальное количество бирок в промежутке времени
						$maxRecordsWithTime = floor((StringToTime( $EndTime ) - StringToTime( $StartTime )) / $Duration);
						
						//конечное время заполнения дополнительных экстренных бирок
						$fasterEndTime =  StringToTime( $StartTime ) + $data["Faster"] * $Duration;
						$fasterEndTimeString = (($fasterEndTime - ($fasterEndTime % 60))/60).':'.($fasterEndTime % 60);
						
						//конечное время заполнения дополнительных обычных бирок
						$regularEndTime =  $fasterEndTime + $data["Regular"] * $Duration;
						$regularEndTimeString = (($regularEndTime - ($regularEndTime % 60))/60).':'.($regularEndTime % 60);
						
						$data["AllBeds"] = $data['ManBeds']+$data['WomanBeds']+$data['CommonBeds'];
						//уменьшаем количество коек, чтобы они влезли в максимальное количество
						$left = -($maxRecordsWithTime - $nDopBeds - $data["AllBeds"]);
						while ($left>0) {
							if ($data["CommonBeds"]>0 && $left>0) {
								$data["CommonBeds"]--;
								$left--;
							}
							if ($data["WomanBeds"]>0 && $left>0) {
								$data["WomanBeds"]--;
								$left--;
							}
							if ($data["ManBeds"]>0 && $left>0) {
								$data["ManBeds"]--;
								$left--;
							}
						}
					}
					else {
						$StartTime = null;
						$EndTime = null;
						$Duration = null;
					}
					
					//создаем дополнительные бирки
					if ($nDopBeds) {
						//экстренные
						$this->db->query(
							$sqlAddRecords, array(
								'LpuSection_id' => $data['LpuSection_id'],
								'TimetableStac_Day' => $day,
								'LpuSectionBedTypeMan_Count' => 0,
								'LpuSectionBedTypeWoman_Count' => 0,
								'LpuSectionBedTypeCommon_Count' => $data["Faster"],
								'TimetableType_id' => 6,
								'StartTime' => $StartTime,
								'EndTime' => !empty($fasterEndTimeString) ? $fasterEndTimeString : $EndTime,
								'TimeTableStac_Time' => $Duration,
								'TimeTableStac_IsDop' => 2,
								'pmUser_id' => $data['pmUser_id'],
								'TimetableExtend_Descr' => $data['TimetableExtend_Descr']
							)
						);
						//обычные
						$this->db->query(
							$sqlAddRecords, array(
								'LpuSection_id' => $data['LpuSection_id'],
								'TimetableStac_Day' => $day,
								'LpuSectionBedTypeMan_Count' => 0,
								'LpuSectionBedTypeWoman_Count' => 0,
								'LpuSectionBedTypeCommon_Count' => $data["Regular"],
								'TimetableType_id' => 1,
								'StartTime' => !empty($fasterEndTimeString) ? $fasterEndTimeString : $StartTime,
								'EndTime' => !empty($regularEndTimeString) ? $regularEndTimeString : $EndTime,
								'TimeTableStac_Time' => $Duration,
								'TimeTableStac_IsDop' => 2,
								'pmUser_id' => $data['pmUser_id'],
								'TimetableExtend_Descr' => $data['TimetableExtend_Descr']
							)
						);
					}
					
					if ($data['AllBeds']) {
						//создаем расписание по койкам
						$this->db->query(
							$sqlAddRecords, array(
								'LpuSection_id' => $data['LpuSection_id'],
								'TimetableStac_Day' => $day,
								'LpuSectionBedTypeMan_Count' => $data["ManBeds"],
								'LpuSectionBedTypeWoman_Count' => $data["WomanBeds"],
								'LpuSectionBedTypeCommon_Count' => $data["CommonBeds"],
								'TimetableType_id' => $data['TimetableType_id'],
								'StartTime' => !empty($regularEndTimeString) ? $regularEndTimeString : $StartTime,
								'EndTime' => $EndTime,
								'TimeTableStac_Time' => $Duration,
								'TimeTableStac_IsDop' => 1,
								'pmUser_id' => $data['pmUser_id'],
								'TimetableExtend_Descr' => $data['TimetableExtend_Descr']
							)
						);
					}
				}
				else {
					//если расписания не существует
					//если с привязкой ко времени
					if ($settings['is_time'] == 1) {
						$StartTime = $data['StartTime'];
						$EndTime = $data['EndTime'];
						$Duration = $data['Duration'];
						
						//максимальное количество бирок в промежутке времени
						$maxRecordsWithTime = floor((StringToTime( $EndTime ) - StringToTime( $StartTime )) / $Duration);
						
						//уменьшаем количество коек, чтобы они влезли в максимальное количество
						$left = -($maxRecordsWithTime - $nDopBeds - $data["AllBeds"]);
						while ($left>0) {
							if ($data["CommonBeds"]>0 && $left>0) {
								$data["CommonBeds"]--;
								$left--;
							}
							if ($data["WomanBeds"]>0 && $left>0) {
								$data["WomanBeds"]--;
								$left--;
							}
							if ($data["ManBeds"]>0 && $left>0) {
								$data["ManBeds"]--;
								$left--;
							}
						}
						$data["AllBeds"] = $data["CommonBeds"]+$data["WomanBeds"]+$data["ManBeds"];
						
						//конечное время заполнения обычных бирок
						$commonEndTime =  StringToTime( $StartTime ) + $data["AllBeds"] * $Duration;
						$commonEndTimeString = (($commonEndTime - ($commonEndTime % 60))/60).':'.($commonEndTime % 60);
						
						//конечное время заполнения дополнительных экстренных бирок
						$fasterEndTime =  $commonEndTime + $data["Faster"] * $Duration;
						$fasterEndTimeString = (($fasterEndTime - ($fasterEndTime % 60))/60).':'.($fasterEndTime % 60);
					}
					else {
						$StartTime = null;
						$EndTime = null;
						$Duration = null;
					}
					
					//создаем расписание по койкам
					$this->db->query(
						$sqlAddRecords, array(
							'LpuSection_id' => $data['LpuSection_id'],
							'TimetableStac_Day' => $day,
							'LpuSectionBedTypeMan_Count' => $data["ManBeds"],
							'LpuSectionBedTypeWoman_Count' => $data["WomanBeds"],
							'LpuSectionBedTypeCommon_Count' => $data["CommonBeds"],
							'TimetableType_id' => $data['TimetableType_id'],
							'StartTime' => $StartTime,
							'EndTime' => !empty($commonEndTimeString) ? $commonEndTimeString : $EndTime,
							'TimeTableStac_Time' => $Duration,
							'TimeTableStac_IsDop' => 1,
							'pmUser_id' => $data['pmUser_id'],
							'TimetableExtend_Descr' => $data['TimetableExtend_Descr']
						)
					);
					
					//создаем дополнительные бирки
					if ($nDopBeds) {
						//экстренные
						$this->db->query(
							$sqlAddRecords, array(
								'LpuSection_id' => $data['LpuSection_id'],
								'TimetableStac_Day' => $day,
								'LpuSectionBedTypeMan_Count' => 0,
								'LpuSectionBedTypeWoman_Count' => 0,
								'LpuSectionBedTypeCommon_Count' => $data["Faster"],
								'TimetableType_id' => 6,
								'StartTime' => !empty($commonEndTimeString) ? $commonEndTimeString : $StartTime,
								'EndTime' => !empty($fasterEndTimeString) ? $fasterEndTimeString : $EndTime,
								'TimeTableStac_Time' => $Duration,
								'TimeTableStac_IsDop' => 2,
								'pmUser_id' => $data['pmUser_id'],
								'TimetableExtend_Descr' => $data['TimetableExtend_Descr']
							)
						);
						//обычные
						$this->db->query(
							$sqlAddRecords, array(
								'LpuSection_id' => $data['LpuSection_id'],
								'TimetableStac_Day' => $day,
								'LpuSectionBedTypeMan_Count' => 0,
								'LpuSectionBedTypeWoman_Count' => 0,
								'LpuSectionBedTypeCommon_Count' => $data["Regular"],
								'TimetableType_id' => 1,
								'StartTime' => !empty($fasterEndTimeString) ? $fasterEndTimeString : $StartTime,
								'EndTime' => $EndTime,
								'TimeTableStac_Time' => $Duration,
								'TimeTableStac_IsDop' => 2,
								'pmUser_id' => $data['pmUser_id'],
								'TimetableExtend_Descr' => $data['TimetableExtend_Descr']
							)
						);
					}
				}
				sendFerStompMessage(array(
					'timeTable' => 'TimeTableStac',
					'action' => 'AddTicket',
					'setDate' => date("c"),
					'begDate' => date("c", DayMinuteToTime($data['StartDay'], 0)),
					'endDate' => date("c", DayMinuteToTime($data['EndDay'], 0)),
					'MedStaffFact_id' => $data['session']['CurMedStaffFact_id']
				), 'RulePeriod');
			}
			else {
				//заполнение расписания как обычно
				$this->db->query(
					$sqlAddRecords, array(
						'LpuSection_id' => $data['LpuSection_id'],
						'TimetableStac_Day' => $day,
						'LpuSectionBedTypeMan_Count' => $data["ManBeds"],
						'LpuSectionBedTypeWoman_Count' => $data["WomanBeds"],
						'LpuSectionBedTypeCommon_Count' => $data["CommonBeds"],
						'TimetableType_id' => $data['TimetableType_id'],
						'pmUser_id' => $data['pmUser_id'],
						'TimetableExtend_Descr' => $data['TimetableExtend_Descr'],
						'StartTime' => !empty($data['StartTime']) ? $data['StartTime'] : null,
						'EndTime' => !empty($data['EndTime']) ? $data['EndTime'] : null,
						'TimeTableStac_Time' => !empty($data['Duration']) ? $data['Duration'] : null,
						'TimeTableStac_IsDop' => 1
					)
				);
				sendFerStompMessage(array(
					'timeTable' => 'TimeTableStac',
					'action' => 'AddTicket',
					'setDate' => date("c"),
					'begDate' => date("c", DayMinuteToTime($data['StartDay'], 0)),
					'endDate' => date("c", DayMinuteToTime($data['EndDay'], 0)),
					'MedStaffFact_id' => $data['session']['CurMedStaffFact_id']
				), 'RulePeriod');
			}
		}

		return array(
			'Error_Msg' => ''
		);
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) занятые бирки для стационара
	 */
	function checkTimetableStacDayNotOccupied( $data ) {
		if ( isset($data['Day']) ) {
			$sql = "
				SELECT count(*) as cnt
				FROM v_TimetableStac_lite with (nolock)
				WHERE
					TimetableStac_Day = :Day
					and LpuSection_id = :LpuSection_id
					and Person_id is not null
			";

			$res = $this->db->query(
					$sql, array(
				'Day' => $data['Day'],
				'LpuSection_id' => $data['LpuSection_id'],
					)
			);
		}
		if ( isset($data['StartDay']) ) {
			$sql = "
				SELECT count(*) as cnt
				FROM v_TimetableStac_lite with (nolock)
				WHERE
					TimetableStac_Day between :StartDay and :EndDay
					and LpuSection_id = :LpuSection_id
					and Person_id is not null
			";

			$res = $this->db->query(
					$sql, array(
				'StartDay' => $data['StartDay'],
				'EndDay' => $data['EndDay'],
				'LpuSection_id' => $data['LpuSection_id'],
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
	 * Копирование расписания в стационаре
	 */
	function copyTTSSchedule( $data ) {

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

		if ( true !== ($res = $this->checkTimetableStacDayNotOccupied($data)) ) {
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
				exec p_TimetableStac_copy
					@LpuSection_id = :LpuSection_id,
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
				'LpuSection_id' => $data['LpuSection_id'],
				'SourceStartDay' => $SourceStartDay,
				'SourceEndDay' => $SourceEndDay,
				'TargetStartDay' => $nTargetStart,
				'TargetEndDay' => $nTargetEnd,
				'CopyTimetableExtend_Descr' => ($data['CopyTTSComments'] == 1) ? 1 : NULL,
				'pmUser_id' => $data['pmUser_id']
					)
			);

			if ( is_object($res) ) {
				$resp = $res->result('array');
				if ( count($resp) > 0 && empty($resp[0]['Error_Msg']) ) {
					// отправка STOMP-сообщения
					sendFerStompMessage(array(
						'timeTable' => 'TimetableStac',
						'action' => 'AddTicket',
						'setDate' => date("c"),
						'begDate' => date("c", DayMinuteToTime($nTargetStart, 0)),
						'endDate' => date("c", DayMinuteToTime($nTargetEnd, 0)),
						'MedStaffFact_id' => $data['session']['CurMedStaffFact_id']
							), 'RulePeriod');
				}
			}

			for ( $i = 0; $i <= $nTargetEnd - $nTargetStart; $i++ ) {
				// Пересчет кэша коек по дням теперь прямо в хранимке
				if ( $data['CopyDayComments'] == 1 ) {
					$sql = "
					
						declare
							@LpuSectionDay_Descr varchar(200),
							@Res bigint,
							@ErrCode bigint,
							@ErrMsg varchar(4000);
					
						select
							@LpuSectionDay_Descr = LpuSectionDay_Descr
						from LpuSectionDay with (nolock)
						where LpuSection_id = :LpuSection_id
							and Day_id = :SourceDay_id
						exec p_LpuSectionDay_setDescr
							@Server_id = 2,
							@LpuSectionDay_id = @Res,
							@Day_id = :TargetDay_id,
							@LpuSection_id = :LpuSection_id,
							@LpuSectionDay_Descr = @LpuSectionDay_Descr,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output";

					$res = $this->db->query(
							$sql, array(
						'LpuSection_id' => $data['LpuSection_id'],
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
	 * Очистка дня для стационара
	 */
	function ClearDayTTS( $data ) {

		$sql = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			exec p_TimetableStac_clearDay
				@TimetableStac_Day = :TimetableStac_Day,
				@LpuSection_id = :LpuSection_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output
			select @Res as TimetableStac_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$res = $this->db->query(
				$sql, array(
			'LpuSection_id' => $data['LpuSection_id'],
			'TimetableStac_Day' => $data['Day'],
			'pmUser_id' => $data['pmUser_id']
				)
		);

		if ( is_object($res) ) {
			$resp = $res->result('array');
			if ( count($resp) > 0 && empty($resp[0]['Error_Msg']) ) {
				// отправка STOMP-сообщения
				sendFerStompMessage(array(
					'timeTable' => 'TimetableStac',
					'action' => 'DelTicket',
					'setDate' => date("c"),
					'begDate' => date("c", DayMinuteToTime($data['Day'], 0)),
					'endDate' => date("c", DayMinuteToTime($data['Day'], 0)),
					'MedStaffFact_id' => $data['session']['CurMedStaffFact_id']
						), 'RulePeriod');
			}
		}

		// Пересчет кэша коек по дням теперь прямо в хранимке

		return array(
			'Error_Msg' => ''
		);
	}

	/**
	 * Удаление бирки в стационаре
	 */
	function DeleteTTS( $data ) {

		if ( isset($data['TimetableStac_id']) ) {
			$data['TimetableStacGroup'] = array($data['TimetableStac_id']);
		} else {
			$data['TimetableStacGroup'] = json_decode($data['TimetableStacGroup']);
		}

		if ( true !== ($res = $this->checkTimetablesFree($data)) ) {
			return $res;
		}

		// Получаем отделение и день, а также заодно проверяем, что бирка существует
		$res = $this->db->query("
			select
				TimetableStac_id,
				LpuSection_id,
				TimetableStac_Day
			from v_TimetableStac_lite with (nolock)
			where TimetableStac_id in (" . implode(', ', $data['TimetableStacGroup']) . ")"
		);

		if ( is_object($res) ) {
			$res = $res->result('array');
		} else {
			return false;
		}
		// Удаляем каждую бирку по отдельности. Не лучший вариант конечно
		foreach ( $res as $row ) {

			$TimetableStac_id = $row['TimetableStac_id'];
			$LpuSection_id = $row['LpuSection_id'];
			$Day = $row['TimetableStac_Day'];

			//Удаляем бирку
			$sql = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec p_TimetableStac_del
					@TimetableStac_id = :TimetableStac_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$res = $this->db->query(
					$sql, array(
				'TimetableStac_id' => $data['TimetableStac_id'],
				'pmUser_id' => $data['pmUser_id']
					)
			);

			if ( is_object($res) ) {
				$resp = $res->result('array');
				if ( is_array($resp) && count($resp) > 0 && empty($resp[0]['Error_Msg']) ) {
					// отправка STOMP-сообщения
					sendFerStompMessage(array(
						'id' => $data['TimetableStac_id'],
						'timeTable' => 'TimetableStac',
						'action' => 'DelTicket',
						'setDate' => date("c")
							), 'Rule');
				} else {
					return $resp;
				}
			}

			// Пересчет кэша коек по дням теперь прямо в хранимке
		}

		return array(
			'success' => true
		);
	}

	/**
	 * Получение расписания на один день
	 */
	function getTimetableStacOneDay( $data ) {
		$outdata = array();

		$StartDay = isset($data['StartDay']) ? strtotime($data['StartDay']) : time();

		$outdata['StartDay'] = $StartDay;

		$param['StartDay'] = TimeToDay($StartDay);
		$param['LpuSection_id'] = $data['LpuSection_id'];

		$nTime = $StartDay;

		$outdata['day_comment'] = null;
		$outdata['data'] = array();


		$sql = "
			select
				lsd.Day_id,
				rtrim(lsd.LpuSectionDay_Descr) as LpuSectionDay_Descr,
				rtrim(u.pmUser_Name) as pmUser_Name,
				lsd.LpuSectionDay_updDT
			from LpuSectionDay lsd with (nolock)
			left join v_pmUser u with(nolock) on u.pmUser_id = lsd.pmUser_updID
			where LpuSection_id = :LpuSection_id
				and Day_id = :StartDay";

		$res = $this->db->query($sql, $param);

		$daydescrdata = $res->result('array');

		if ( isset($daydescrdata[0]['LpuSectionDay_Descr']) ) {
			$outdata['day_comment'] = array(
				'LpuSectionDay_Descr' => $daydescrdata[0]['LpuSectionDay_Descr'],
				'pmUser_Name' => $daydescrdata[0]['pmUser_Name'],
				'LpuSectionDay_updDT' => isset($daydescrdata[0]['LpuSectionDay_updDT']) ? $daydescrdata[0]['LpuSectionDay_updDT']->format("d.m.Y H:i") : ''
			);
		}

		$selectPersonData = "
				p.Person_BirthDay,
				p.Person_Phone,
				case when a1.Address_id is not null
				then 
					a1.Address_Address
				else
					a.Address_Address
				end as Address_Address,
				case
				when a1.Address_id is not null then a1.KLTown_id else a.KLTown_id
				end as KLTown_id,
				case
				when a1.Address_id is not null then a1.KLStreet_id else a.KLStreet_id
				end as KLStreet_id,
				case
				when a1.Address_id is not null then a1.Address_House else a.Address_House
				end as Address_House,
				j.Job_Name,
				lpu.Lpu_Nick,
				p.PrivilegeType_id,
				rtrim(p.Person_Firname) as Person_Firname,
				rtrim(p.Person_Surname) as Person_Surname,
				rtrim(p.Person_Secname) as Person_Secname,";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = p.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null then p.Person_BirthDay else null end as Person_BirthDay,
				case when peh.PersonEncrypHIV_Encryp is null then p.Person_Phone else null end as Person_Phone,
				case when peh.PersonEncrypHIV_Encryp is not null then null
				when a1.Address_id is not null then a1.Address_Address else a.Address_Address
				end as Address_Address,
				case when peh.PersonEncrypHIV_Encryp is not null then null
				when a1.Address_id is not null then a1.KLTown_id else a.KLTown_id
				end as KLTown_id,
				case when peh.PersonEncrypHIV_Encryp is not null then null
				when a1.Address_id is not null then a1.KLStreet_id else a.KLStreet_id
				end as KLStreet_id,
				case when peh.PersonEncrypHIV_Encryp is not null then null
				when a1.Address_id is not null then a1.Address_House else a.Address_House
				end as Address_House,
				case when peh.PersonEncrypHIV_Encryp is null then j.Job_Name else null end as Job_Name,
				case when peh.PersonEncrypHIV_Encryp is null then lpu.Lpu_Nick else null end as Lpu_Nick,
				case when peh.PersonEncrypHIV_Encryp is null then p.PrivilegeType_id else null end as PrivilegeType_id,
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Surname) else rtrim(peh.PersonEncrypHIV_Encryp) end as Person_Surname,
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Firname) else '' end as Person_Firname,
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Secname) else '' end as Person_Secname,";
		}

		$settings = $this->getStacSheduleSettings($param['StartDay'], $data['LpuSection_id']);

		$dayParams = array(
			'currentDay' => $param['StartDay'],
			'LpuSection_id' => $param['LpuSection_id'],
			'recordCount' => ($settings['auto_create'] == 2) ?
				$this->getRecordCountForDay($param['LpuSection_id'], date('Y-m-d', DayMinuteToTime($param['StartDay'], 0))) : 100
		);
		
		$sql = "
			select
				t.pmUser_updID,
				t.TimetableStac_updDT,
				t.TimetableStac_id,
				t.LpuSectionBedType_id,
				t.Person_id,
				t.TimetableStac_Day,
				{$selectPersonData}
				t.PMUser_UpdID,
				case 
					when t.pmUser_updid=999000
					then 'Запись через КМИС'
					when t.pmUser_updid between 1000000 and 5000000
					then 'Запись через интернет'
					else u.PMUser_Name 
				end as PMUser_Name,
				lpud.Lpu_Nick as DirLpu_Nick,
				d.EvnDirection_Num as Direction_Num,
				convert(varchar(10),d.EvnDirection_setDate,104) as Direction_Date,
				d.EvnDirection_id as EvnDirection_id,
				TimetableType_id,
				qp.pmUser_Name as QpmUser_Name,
				dg.Diag_Code,
				ls.LpuSectionHospType_id,
				u.Lpu_id as pmUser_Lpu_id,
				EvnQueue_insDT,
				t.TimetableStac_EmStatus,
				t.EmergencyData_id,
				ed.EmergencyData_BrigadeNum,
				ed.EmergencyData_CallNum,
				cd.Diag_Code + ' ' + cd.Diag_Name as Diag_Name,
				TimetableStac_setDate,
				t.TimetableExtend_Descr,
				t.TimetableExtend_updDT,
				ud.pmUser_Name as TimetableExtend_pmUser_Name
			from v_TimetableStac t with (nolock)
			outer apply (
				(select count(*) as cnt
					from v_TimetableStac
					where
						TimetableStac_Day = t.TimetableStac_Day
						and LpuSection_id = t.LpuSection_id
						and TimetableStac_id <= t.TimetableStac_id
						and isnull(TimeTableStac_IsDop, 1) = 1
						and Person_id is null
						and isnull(t.TimeTableStac_IsDop, 1) = 1
						and t.Person_id is null
				)
			) cntRegular
			left outer join LpuSection ls with (nolock) on ls.LpuSection_id = t.LpuSection_id
			left outer join v_Person_ER p with (nolock) on t.Person_id = p.Person_id
			left outer join Address a with (nolock) on p.UAddress_id = a.Address_id
			left outer join Address a1 with (nolock) on p.PAddress_id = a1.Address_id
			left outer join v_Job_ER j with (nolock) on p.Job_id = j.Job_id
			left outer join v_pmUser u with (nolock) on t.PMUser_UpdID = u.PMUser_id
			left outer join v_pmUser ud with (nolock) on t.TimetableExtend_pmUser_updid = ud.PMUser_id
			left outer join v_Lpu lpu with (nolock) on lpu.Lpu_id = p.Lpu_id
			left outer join v_EvnDirection_all d with (nolock) on 
				t.EvnDirection_id = d.EvnDirection_id
				and d.DirFailType_id is null
				and d.Person_id = t.Person_id
				and isnull(d.EvnDirection_isAuto, 1) != 2
			left outer join v_Lpu lpud with (nolock) ON lpud.Lpu_id = d.Lpu_id
			left join v_EvnQueue q with (nolock) on t.TimetableStac_id = q.TimetableStac_id and t.Person_id = q.Person_id
			left join v_pmUser qp with (nolock) on q.pmUser_updId = qp.pmUser_id
			left join Diag dg with (nolock) on dg.Diag_id = d.Diag_id
			left join EmergencyData ed with (nolock) on ed.EmergencyData_id = t.EmergencyData_id and t.TimetableType_id = 6
			left join Diag cd with (nolock) on cd.Diag_id = ed.Diag_id
			{$joinPersonEncrypHIV}
			where t.TimetableStac_Day = :currentDay
				and t.LpuSection_Id = :LpuSection_id
				and cntRegular.cnt <= :recordCount
			order by TimetableStac_Day, LpuSectionBedType_id, TimetableType_id, t.TimetableStac_id";

		$res = $this->db->query($sql, array_merge($param, $dayParams));

		$ttsdata = $res->result('array');


		foreach ( $ttsdata as $tts ) {
			$outdata['data'][] = $tts;
		}

		$sql = "
			select TimetableStac_id from TimetableLock with(nolock) where TimetableStac_id is not null";

		$res = $this->db->query($sql);

		$outdata['reserved'] = array();
		$reserved = $res->result('array');
		foreach ( $reserved as $lock ) {
			$outdata['reserved'][] = $lock['TimetableStac_id'];
		}

		return $outdata;
	}

	/**
	 * Редактирование переданного набора бирок
	 */
	function editTTSSet( $data ) {

		$TTSSet = json_decode($data['selectedTTS']);

		if ( $this->checkTTSOccupied($TTSSet) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Одна из выбранных бирок занята. Операция невозможна.'
			);
		}

		// Пустая строка передается как NULL, надо как пустую строку передавать
		if ( $data['ChangeTTSDescr'] ) {
			$data['TimetableExtend_Descr'] = isset($data['TimetableExtend_Descr']) ? $data['TimetableExtend_Descr'] : '';
		} else {
			$data['TimetableExtend_Descr'] = NULL;
		}

		if ( $data['ChangeTTSType'] ) {
			$data['TimetableType_id'] = isset($data['TimetableType_id']) ? $data['TimetableType_id'] : 1;
		} else {
			$data['TimetableType_id'] = NULL;
		}

		foreach ( $TTSSet as $TTS ) {
			$query = "
				declare
					@ErrCode int,
					@TimetableType_SysNick varchar(50),
					@ErrMessage varchar(4000);
				set @TimetableType_SysNick = (select top 1 TimetableType_SysNick from v_TimetableType (nolock) where TimetableType_id = :TimetableType_id);
				exec p_TimetableStac_edit
					@TimetableStac_id = :TimetableStac_id,
					@TimetableType_id = :TimetableType_id,
					@TimetableExtend_Descr = :TimetableExtend_Descr,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @TimetableType_SysNick as TimetableType_SysNick, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$res = $this->db->query(
					//echo getDebugSQL(
					$query, array(
				'TimetableStac_id' => $TTS,
				'TimetableType_id' => $data['TimetableType_id'],
				'TimetableExtend_Descr' => $data['TimetableExtend_Descr'],
				'pmUser_id' => $data['pmUser_id']
					)
			);
			if ( is_object($res) ) {
				$resp = $res->result('array');
				if ( count($resp) > 0 && !empty($resp[0]['TimetableType_SysNick']) ) {
					$action = $this->defineActionTypeByTimetableType($resp[0]['TimetableType_SysNick']);
					if ( !empty($action) ) {
						// отправка STOMP-сообщения
						sendFerStompMessage(array(
							'id' => $TTS,
							'timeTable' => 'TimetableStac',
							'action' => $action,
							'setDate' => date("c")
								), 'Rule');
					}
				}
			}
		}

		return array(
			'Error_Msg' => ''
		);
	}

	/**
	 * Проверка, что хоть одна из набора переданных бирок занята
	 */
	function checkTTSOccupied( $TTSSet ) {
		if ( count($TTSSet) == 0 ) {
			return false;
		}
		$sql = "
			SELECT count(*) as cnt
			FROM v_TimetableStac_lite with (nolock)
			WHERE
				TimetableStac_id in (" . implode(',', $TTSSet) . ")
				and Person_id is not null
		";

		$res = $this->db->query(
				$sql
		);
		if ( is_object($res) ) {
			$res = $res->result('array');
		}

		return $res[0]['cnt'] > 0;
	}
	
	/**
	* Перенос бирки с одного события на другое, используется при смене пациента в документе.
	*/
	function onSetAnotherPersonForDocument($data) {
		$this->db->query("update TimetableStac with (rowlock) set Evn_id = :Evn_id, Person_id = :Person_id where Evn_id = :Evn_oldid", $data);
	}	
}