<?php
require_once("Timetable_model.php");
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
 * Загрузка базовой модели для работы с расписанием
 *
 * @property int EmergencyData_id
 * @property LpuIndividualPeriod_model lipmodel
 */
class TimetableStac_model extends Timetable_model
{
	private $dateTimeForm104 = "DD.MM.YYYY";
	private $dateTimeForm108 = "HH24:MI:SS";
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList([
			self::SCENARIO_SET_ATTRIBUTE,
		]);
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return "TimetableStac";
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = [
			self::ID_KEY => [
				"properties" => [
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_NOT_NULL,
				],
				"alias" => "TimetableStac_id",
				"label" => "Идентификатор",
				"save" => "trim",
				"type" => "id"
			]
		];
		$arr["person_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],// Записанный человек
			"alias" => "Person_id",
		];
		$arr["setdate"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],// Дата начала
			"alias" => "TimeTableStac_setDate",
		];
		$arr["day"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_NULL,
			],// День приема по бирке
			"alias" => "TimeTableStac_Day",
		];
		$arr["recclass_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],// тип записи
			"alias" => "RecClass_id",
		];
		$arr["TimeTableType_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],// тип бирки
			"alias" => "TimeTableType_id",
		];
		$arr["lpusection_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_NULL,
			],// отделение МО
			"alias" => "LpuSection_id",
		];
		$arr["lpusectionbedtype_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_NULL,
			],// тип койки
			"alias" => "LpuSectionBedType_id",
		];
		$arr["emstatus"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],//
			"alias" => "TimetableStac_EmStatus",
		];
		$arr["emergencydata_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],// данные о вызове скорой помощи
			"alias" => "EmergencyData_id",
		];
		$arr["evndirection_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],// идентификатор выписки направления
			"alias" => "EvnDirection_id",
		];
		$arr["evn_pid"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],// Учетный документ, в рамках которого было добавлено
			"alias" => "Evn_pid",
		];
		$arr["evn_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],// Связь события как родителя
			"alias" => "Evn_id",
		];
		return $arr;
	}

	/**
	 * Определение действия по типу бирки
	 * @param $TimetableType_SysNick
	 * @return string
	 */
	function defineActionTypeByTimetableType($TimetableType_SysNick)
	{
		if ($TimetableType_SysNick == "free") {
			return "ChType_NormalBed";
		} elseif ($TimetableType_SysNick == "reserved") {
			return "ChType_ReservBed";
		} elseif ($TimetableType_SysNick == "pay") {
			return "ChType_PaidTicket";
		} elseif ($TimetableType_SysNick == "vet") {
			return "ChType_VeteranTicket";
		} elseif ($TimetableType_SysNick == "extr") {
			return "ChType_OutTicket";
		} elseif ($TimetableType_SysNick == "emerg") {
			return "ChType_ExtraBed";
		} elseif ($TimetableType_SysNick == "bed") {
			return "ChType_NormalBed";
		} else {
			return "";
		}
	}

	/**
	 * Изменение типа бирки в стационаре
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	function setTTSType($data)
	{
		$data["object"] = "TimetableStac";
		if (isset($data["TimetableStacGroup"])) {
			$data["TimetableStacGroup"] = json_decode($data["TimetableStacGroup"]);
		}
		if (isset($data["TimetableStacGroup"]) && count($data["TimetableStacGroup"]) > 0) {
			// Обработка группы бирок в отдельном методе
			return $this->setTTSTypeGroup($data);
		} else {
			if (true === ($res = $this->checkTimetableOccupied($data))) {
				throw new Exception("Бирка занята, изменение типа невозможно.");
			}
		}
		// Получаем услугу и день, а также заодно проверяем, что бирка существует
		$query = "
			select
				LpuSection_id as \"LpuSection_id\",
				TimetableStac_Day as \"TimetableStac_Day\"
			from v_TimetableStac_lite
			where TimetableStac_id = :TimetableStac_id
		";
		$queryParams = ["TimetableStac_id" => $data["TimetableStac_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		if (!isset($result[0])) {
			throw new Exception("Бирка с таким идентификатором не существует.");
		}
		$tttype = $this->getFirstRowFromQuery("select TimetableType_Name as \"TimetableType_Name\" from v_TimetableType where TimeTableType_id = :TimetableType_id", ["TimetableType_id" => $data["TimetableType_id"]]);
		$query = "
			select
			    null as \"TimetableStac_id\",
			    (select TimetableType_SysNick from v_TimetableType where TimetableType_id = :TimetableType_id limit 1) as \"TimetableType_SysNick\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_timetablestac_settype(
			    timetablestac_id := :TimetableStac_id,
			    timetabletype_id := :TimetableType_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"TimetableStac_id" => $data["TimetableStac_id"],
			"TimetableType_id" => $data["TimetableType_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$resp = $result->result("array");
			if (count($resp) > 0 && !empty($resp[0]["TimetableType_SysNick"])) {
				$action = $this->defineActionTypeByTimetableType($resp[0]["TimetableType_SysNick"]);
				if (!empty($action)) {
					// отправка STOMP-сообщения
					sendFerStompMessage([
						"id" => $data["TimetableStac_id"],
						"timeTable" => "TimetableStac",
						"action" => $action,
						"setDate" => date("c")
					], "Rule");
				}
			}
		}
		return ["TimetableType_Name" => $tttype["TimetableType_Name"], "Error_Msg" => ""];
	}

	/**
	 * Изменение типа бирок в cтационаре для группы бирок
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	function setTTSTypeGroup($data)
	{
		if (true !== ($res = $this->checkTimetablesFree($data))) {
			return $res;
		}
		// Получаем врача и список дней, на которые мы выделили бирки
		$TimetableStacGroupString = implode(", ", $data["TimetableStacGroup"]);
		$query = "
			select
				TimetableStac_id as \"TimetableStac_id\",
				LpuSection_id as \"LpuSection_id\",
				TimetableStac_Day as \"TimetableStac_Day\"
			from v_TimetableStac_lite
			where TimetableStac_id in ({$TimetableStacGroupString})
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		// Меняем тип у каждой бирки по отдельности. Не лучший вариант конечно
		foreach ($result as $row) {
			$query = "
				select
				    null as \"TimetableStac_id\",
				    (select TimetableType_SysNick from v_TimetableType where TimetableType_id = :TimetableType_id limit 1) as \"TimetableType_SysNick\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from p_timetablestac_settype(
				    timetablestac_id := :TimetableStac_id,
				    timetabletype_id := :TimetableType_id,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"TimetableStac_id" => $row["TimetableStac_id"],
				"TimetableType_id" => $data["TimetableType_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$result = $this->db->query($query, $queryParams);
			if (is_object($result)) {
				$resp = $result->result("array");
				if (count($resp) > 0 && !empty($resp[0]["TimetableType_SysNick"])) {
					$action = $this->defineActionTypeByTimetableType($resp[0]["TimetableType_SysNick"]);
					if (!empty($action)) {
						// отправка STOMP-сообщения
						sendFerStompMessage([
							"id" => $row["TimetableStac_id"],
							"timeTable" => "TimetableStac",
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
	 * Получение истории изменения бирки стационара
	 * @param $data
	 * @return array|bool
	 */
	function getTTSHistory($data)
	{
		$selectPersonData = "
			rtrim(rtrim(p.Person_Surname)||' '||rtrim(p.Person_Firname)||' '||coalesce(rtrim(p.Person_Secname), '')) as \"Person_FIO\",
			to_char(Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_BirthDay\"
		";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = " left join v_PersonEncrypHIV peh on peh.Person_id = p.Person_id";
			$selectPersonData = "
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(rtrim(p.Person_Surname)||' '||rtrim(p.Person_Firname)||' '||coalesce(rtrim(p.Person_Secname), '')) else rtrim(peh.PersonEncrypHIV_Encryp) end as \"Person_FIO\",
				case when peh.PersonEncrypHIV_Encryp is null then to_char(Person_BirthDay, '{$this->dateTimeForm104}') else null end as \"Person_BirthDay\"
			";
		}
		$query = "
			select
				to_char(TimetableStacHist_insDT, '{$this->dateTimeForm104} {$this->dateTimeForm108}') as \"TimetableHist_insDT\",
				rtrim(PMUser_Name) as \"PMUser_Name\",
				TimetableActionType_Name as \"TimetableActionType_Name\",
				TimetableType_Name as \"TimetableType_Name\",
				{$selectPersonData}
			from
				TimetableStacHist ttsh
				left join v_pmUser pu on ttsh.TimetableStacHist_userID = pu.pmuser_id
				left join TimetableActionType ttat on ttat.TimetableActionType_id = ttsh.TimetableGrafAction_id
				left join v_TimetableType ttt on ttt.TimetableType_id = coalesce(ttsh.TimetableType_id, 1)
				left join v_Person_ER p on ttsh.Person_id = p.Person_id
				{$joinPersonEncrypHIV}
			where TimetableStac_id = :TimetableStac_id
		";
		$queryParams = ["TimetableStac_id" => $data["TimetableStac_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}
	
	/**
	 * Получение настроек стационара в указанный день
	 * @param $day
	 * @param $lpuSection
	 * @param $changeSettings
	 * @param $pmUser_id
	 * @return array
	 */
	function getStacSheduleSettings( $day, $lpuSection, $changeSettings = false, $pmUser_id = '') {
		//получим настройки на день
		$sql = "
				select
					S.ScheduleParamStac_id as \"ScheduleParamStac_id\",
					S.LpuSection_id as \"LpuSection_id\",
					S.ScheduleParamStac_Day as \"ScheduleParamStac_Day\",
					S.ScheduleParamStac_IsAuto as \"ScheduleParamStac_IsAuto\",
					S.ScheduleParamStac_IsTime as \"ScheduleParamStac_IsTime\"
				from v_ScheduleParamStac S
				where
					S.ScheduleParamStac_Day = :Day
					and S.LpuSection_id = :LpuSection_id
				order by
					ScheduleParamStac_id desc
				limit 1
			";
		$settings_on_day = $this->getFirstRowFromQuery($sql, array(
			'Day' => $day,
			'LpuSection_id' => $lpuSection
		), true);
		
		//узнаем, существует ли расписание
		$sql = "
				select
					t.TimeTableStac_id as \"TimeTableStac_id\"
				from v_TimetableStac t
				where t.TimetableStac_Day = :TimetableStac_Day
					and t.LpuSection_id = :LpuSection_id
				order by t.TimetableStac_setDate
				limit 1
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
				$timetablestac_id = !empty($settings_on_day) ? 'timetablestac_id := :TimetableStac_id,':'';
				$sql = "
						select
							scheduleparamstac_id as \"ScheduleParamStac_id\",
							error_code as \"Error_Code\",
							error_message as \"Error_Msg\"
						from p_scheduleparamstac_{$act}(
							{$timetablestac_id}
							pmuser_id := :pmUser_id,
							scheduleparamstac_id := :ScheduleParamStac_id,
							lpusection_id := :LpuSection_id,
							scheduleparamstac_day := :ScheduleParamStac_Day,
							scheduleparamstac_istime := :ScheduleParamStac_IsTime,
							scheduleparamstac_isauto := :ScheduleParamStac_IsAuto
						);
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
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	function Delete($data)
	{
		$data["object"] = "TimetableStac";
		$data["TimetableStacGroup"] = (isset($data["TimetableStac_id"]))?[$data["TimetableStac_id"]]:json_decode($data["TimetableStacGroup"]);
		if (true !== ($res = $this->checkTimetablesFree($data))) {
			return $res;
		}
		// Получаем врача и список дней, на которые мы выделили бирки
		$TimetableStacGroupString = implode(", ", $data["TimetableStacGroup"]);
		$query = "
			select
				TimetableStac_id as \"TimetableStac_id\",
				LpuSection_id as \"LpuSection_id\",
				TimetableStac_Day as \"TimetableStac_Day\"
			from v_TimetableStac_lite
			where TimetableStac_id in ({$TimetableStacGroupString})
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		// Удаляем каждую бирку по отдельности. Не лучший вариант конечно
		foreach ($result as $row) {
			//Удаляем бирку
			$query = "
				select
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from p_timetablestac_del(
				    timetablestac_id := :TimetableStac_id,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"TimetableStac_id" => $row["TimetableStac_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$res = $this->db->query($query, $queryParams);
			if (is_object($res)) {
				$resp = $res->result("array");
				if (count($resp) > 0 && empty($resp[0]["Error_Msg"])) {
					// отправка STOMP-сообщения
					sendFerStompMessage([
						"id" => $row["TimetableStac_id"],
						"timeTable" => "TimetableStac",
						"action" => "DelTicket",
						"setDate" => date("c")
					], "Rule");
				}
			}
		}
		return ["success" => true];
	}

	/**
	 * Получение расписания стационара для редактирования
	 * @param $data
	 * @return array
	 */
	function getTimetableStacForEdit($data)
	{
		$outdata = [];
		$StartDay = isset($data["StartDay"]) ? strtotime($data["StartDay"]) : time();
		$outdata["StartDay"] = $StartDay;
		$param["StartDay"] = TimeToDay($StartDay);
		$param["EndDay"] = TimeToDay(strtotime("+21 days", $StartDay));
		$param["LpuSection_id"] = $data["LpuSection_id"];
		$nTime = $StartDay;
		$outdata["header"] = [];
		$outdata["descr"] = [];
		$outdata["data"] = [];
		$outdata["occupied"] = [];
		for ($nCol = 0; $nCol < 21; $nCol++) {
			$nWeekDay = date("w", $nTime);
			$sClass = "work";
			if (($nWeekDay == 6) || ($nWeekDay == 0)) {
				$sClass = "relax";
			}
			$outdata["header"][TimeToDay($nTime)] = "<td class='$sClass'>" . "<b>" . $this->arShortWeekDayName[$nWeekDay] . "</b>" . date(" d", $nTime) . "</td>";
			$outdata["descr"][TimeToDay($nTime)] = [];
			$outdata["data"][TimeToDay($nTime)] = [];
			$outdata["occupied"][TimeToDay($nTime)] = false;
			$nTime = strtotime("+1 day", $nTime);
		}
		$sql = "
			select
				lsd.Day_id as \"Day_id\",
				rtrim(lsd.LpuSectionDay_Descr) as \"LpuSectionDay_Descr\",
				rtrim(u.pmUser_Name) as \"pmUser_Name\",
				to_char(lsd.LpuSectionDay_updDT, 'dd.mm.yyyy HH24:MI') as \"LpuSectionDay_updDT\"
			from
				LpuSectionDay lsd
				left join v_pmUser u on u.pmUser_id = lsd.pmUser_updID
			where LpuSection_id = :LpuSection_id
			  and Day_id >= :StartDay
			  and Day_id < :EndDay
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $param);
		$daydescrdata = $result->result("array");
		foreach ($daydescrdata as $day) {
			/**@var DateTime $LpuSectionDay_updDT */
			$LpuSectionDay_updDT = $day["LpuSectionDay_updDT"];
			$outdata["descr"][$day["Day_id"]] = [
				"LpuSectionDay_Descr" => $day["LpuSectionDay_Descr"],
				"pmUser_Name" => $day["pmUser_Name"],
				"LpuSectionDay_updDT" => isset($day["LpuSectionDay_updDT"]) ? ConvertDateFormat($LpuSectionDay_updDT,"d.m.Y H:i") : ""
			];
		}
		$selectPersonData = "
			p.Person_BirthDay as \"Person_BirthDay\",
			p.Person_Phone as \"Person_Phone\",
			p.PrivilegeType_id as \"PrivilegeType_id\",
			rtrim(p.Person_Firname) as \"Person_Firname\",
			rtrim(p.Person_Surname) as \"Person_Surname\",
			rtrim(p.Person_Secname) as \"Person_Secname\",
		";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = " left join v_PersonEncrypHIV peh on peh.Person_id = p.Person_id";
			$selectPersonData = "
				case when peh.PersonEncrypHIV_Encryp is null then p.Person_BirthDay else null end as \"Person_BirthDay\",
				case when peh.PersonEncrypHIV_Encryp is null then p.Person_Phone else null end as \"Person_Phone\",
				case when peh.PersonEncrypHIV_Encryp is null then p.PrivilegeType_id else null end as \"PrivilegeType_id\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Surname) else rtrim(peh.PersonEncrypHIV_Encryp) end as \"Person_Surname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Firname) else '' end as \"Person_Firname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Secname) else '' end as \"Person_Secname\",
			";
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
					t.pmUser_updID as \"pmUser_updID\",
					t.TimetableStac_updDT as \"TimetableStac_updDT\",
					t.TimetableStac_id as \"TimetableStac_id\",
					t.Person_id as \"Person_id\",
					t.TimetableStac_Day as \"TimetableStac_Day\",
					t.TimetableStac_setDate as \"TimetableStac_setDate\",
					t.TimetableType_id as \"TimetableType_id\",
					{$selectPersonData}
					t.PMUser_UpdID as \"PMUser_UpdID\",
					case
						when t.pmUser_updid=999000
						then 'Запись через КМИС'
						when t.pmUser_updid between 1000000 and 5000000
						then 'Запись через интернет'
						else u.PMUser_Name
					end as \"PMUser_Name\",
					lpud.Lpu_Nick as \"DirLpu_Nick\",
					d.EvnDirection_Num as \"Direction_Num\",
					to_char(d.EvnDirection_setDT, '{$this->dateTimeForm104}') as \"Direction_Date\",
					d.EvnDirection_id as \"EvnDirection_id\",
					qp.pmUser_Name as \"QpmUser_Name\",
					q.EvnQueue_insDT as \"EvnQueue_insDT\",
					dg.Diag_Code as \"Diag_Code\",
					u.Lpu_id as \"pmUser_Lpu_id\",
					t.LpuSectionBedType_id as \"LpuSectionBedType_id\",
					btype.LpuSectionBedType_Name as \"LpuSectionBedType_Name\",
					t.TimetableExtend_Descr as \"TimetableExtend_Descr\",
					t.TimetableExtend_updDT as \"TimetableExtend_updDT\",
				ls.LpuSection_FullName as \"LpuSectionName\",
					ud.pmUser_Name as \"TimetableExtend_pmUser_Name\"
				from
					v_TimetableStac t
					left join lateral (
						(select count(*) as \"cnt\"
							from v_TimetableStac
							where
								TimetableStac_Day = t.TimetableStac_Day
								and LpuSection_id = t.LpuSection_id
								and TimetableStac_id <= t.TimetableStac_id
								and COALESCE(TimeTableStac_IsDop, 1) = 1
								and Person_id is null
								and COALESCE(t.TimeTableStac_IsDop, 1) = 1
								and t.Person_id is null
						)
					) as cntRegular on true
					left outer join v_Person_ER2 p on t.Person_id = p.Person_id
					left outer join v_pmUser u on t.PMUser_UpdID = u.PMUser_id
					left outer join v_pmUser ud on t.TimetableExtend_pmUser_updid = ud.PMUser_id
					left join v_EvnDirection d on t.EvnDirection_id = d.EvnDirection_id and d.DirFailType_id is null
					left join v_Lpu lpud ON lpud.Lpu_id = d.Lpu_id
				left join v_LpuSection ls ON ls.LpuSection_id = t.LpuSection_id
					left join v_EvnQueue q on t.TimetableStac_id = q.TimetableStac_id and t.Person_id = q.Person_id
					left join v_pmUser qp on q.pmUser_updId=qp.pmUser_id
					left join Diag dg on dg.Diag_id=d.Diag_id
					left join v_LpuSectionBedType btype on btype.LpuSectionBedType_id = t.LpuSectionBedType_id
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
		$sql = "select TimetableStac_id as \"TimetableStac_id\" from TimetableLock where TimetableStac_id is not null";
		$res = $this->db->query($sql);
		$outdata["reserved"] = [];
		$reserved = $res->result("array");
		foreach ($reserved as $lock) {
			$outdata["reserved"][] = $lock["TimetableStac_id"];
		}
		return $outdata;
	}
	
	/**
	 * Получаем количество незанятых коек
	 * @param $lpuSection
	 * @param $day
	 * @return int
	 */
	function getRecordCountForDay( $lpuSection, $day ) {
		//коечный фонд
		$sql = "
			select
				sum(LpuSectionBedState_Plan) as LpuSectionBedState_PlanCount,
				sum(LpuSectionBedState_Fact) as LpuSectionBedState_Fact
			from
				v_LpuSectionBedState LSBS
				left join v_LpuSection LSS on LSS.LpuSection_id = LSBS.LpuSection_id
			where
				LSBS.LpuSectionBedState_begDate <=cast(:currentDay as date) and (LSBS.LpuSectionBedState_endDate >= cast(:currentDay as date) or LSBS.LpuSectionBedState_endDate is null) and
				LSBS.LpuSection_id=:LpuSection_id
		";
		$res = $this->db->query($sql, array(
			'LpuSection_id' => $lpuSection,
			'currentDay' => $day
		))->result('array');
		//$kf = $res[0]['LpuSectionBedState_Fact'];
		$kf = $res[0]["lpusectionbedstate_fact"];
		
		//занятые койки в отделении
		$sql = "
			select
				count(*) as \"cnt\"
			from
				v_EvnSection es
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
				count(*) as \"cnt\"
			from
				v_TimeTableStac tts
				inner join v_EvnDirection_all ED on ED.EvnDirection_id = tts.EvnDirection_id
				left join v_EvnStatus ES on ES.EvnStatus_id = ED.EvnStatus_id
			where
				tts.LpuSection_id = :LpuSection_id
				and tts.Person_id is not null
				and tts.TimeTableStac_CureDuration > 0
				and :currentDay between cast(tts.TimeTableStac_setDate as date) and (cast(tts.TimeTableStac_setDate as date) + tts.TimeTableStac_CureDuration * interval '1 day')
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
	 * @param $data
	 * @return array
	 */
	function getTimetableStacSummary($data)
	{
		$outdata = [];
		$StartDay = isset($data["StartDay"]) ? strtotime($data["StartDay"]) : time();
		$outdata["StartDay"] = $StartDay;
		$param["StartDay"] = TimeToDay($StartDay);
		$param["EndDay"] = TimeToDay(strtotime("+21 days", $StartDay));
		$param['Lpu_id'] = $data['Lpu_id'];
		$nTime = $StartDay;
		$outdata["header"] = [];
		$outdata["descr"] = [];
		$outdata["data"] = [];
		$outdata["occupied"] = [];
		for ($nCol = 0; $nCol < 21; $nCol++) {
			$nWeekDay = date("w", $nTime);
			$sClass = "work";
			if (($nWeekDay == 6) || ($nWeekDay == 0)) {
				$sClass = "relax";
			}
			$outdata["header"][TimeToDay($nTime)] = "<td class='$sClass'>" . "<b>" . $this->arShortWeekDayName[$nWeekDay] . "</b>" . date(" d", $nTime) . "</td>";
			$outdata["descr"][TimeToDay($nTime)] = [];
			$outdata["data"][TimeToDay($nTime)] = [];
			$outdata["occupied"][TimeToDay($nTime)] = false;
			$nTime = strtotime("+1 day", $nTime);
		}
		
		$selectPersonData = "
			p.Person_BirthDay as \"Person_BirthDay\",
			p.Person_Phone as \"Person_Phone\",
			p.PrivilegeType_id as \"PrivilegeType_id\",
			rtrim(p.Person_Firname) as \"Person_Firname\",
			rtrim(p.Person_Surname) as \"Person_Surname\",
			rtrim(p.Person_Secname) as \"Person_Secname\",
		";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = " left join v_PersonEncrypHIV peh on peh.Person_id = p.Person_id";
			$selectPersonData = "
				case when peh.PersonEncrypHIV_Encryp is null then p.Person_BirthDay else null end as \"Person_BirthDay\",
				case when peh.PersonEncrypHIV_Encryp is null then p.Person_Phone else null end as \"Person_Phone\",
				case when peh.PersonEncrypHIV_Encryp is null then p.PrivilegeType_id else null end as \"PrivilegeType_id\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Surname) else rtrim(peh.PersonEncrypHIV_Encryp) end as \"Person_Surname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Firname) else '' end as \"Person_Firname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Secname) else '' end as \"Person_Secname\",
			";
		}
		$sql = "
			select
				t.pmUser_updID as \"pmUser_updID\",
				t.TimetableStac_updDT as \"TimetableStac_updDT\",
				t.TimetableStac_id as \"TimetableStac_id\",
				t.Person_id as \"Person_id\",
				t.TimetableStac_Day as \"TimetableStac_Day\",
				t.TimetableStac_setDate as \"TimetableStac_setDate\",
				t.TimetableType_id as \"TimetableType_id\",
				{$selectPersonData}
				t.PMUser_UpdID as \"PMUser_UpdID\",
				case
					when t.pmUser_updid=999000
					then 'Запись через КМИС'
					when t.pmUser_updid between 1000000 and 5000000
					then 'Запись через интернет'
					else u.PMUser_Name
				end as \"PMUser_Name\",
				lpud.Lpu_Nick as \"DirLpu_Nick\",
				d.EvnDirection_Num as \"Direction_Num\",
				to_char(d.EvnDirection_setDT, '{$this->dateTimeForm104}') as \"Direction_Date\",
				d.EvnDirection_id as \"EvnDirection_id\",
				qp.pmUser_Name as \"QpmUser_Name\",
				q.EvnQueue_insDT as \"EvnQueue_insDT\",
				dg.Diag_Code as \"Diag_Code\",
				u.Lpu_id as \"pmUser_Lpu_id\",
				t.LpuSectionBedType_id as \"LpuSectionBedType_id\",
				btype.LpuSectionBedType_Name as \"LpuSectionBedType_Name\",
				t.TimetableExtend_Descr as \"TimetableExtend_Descr\",
				t.TimetableExtend_updDT as \"TimetableExtend_updDT\",
				ls.LpuSection_FullName as \"LpuSectionName\",
				ud.pmUser_Name as \"TimetableExtend_pmUser_Name\"
			from
				v_TimetableStac t
				left outer join v_Person_ER2 p on t.Person_id = p.Person_id
				left outer join v_pmUser u on t.PMUser_UpdID = u.PMUser_id
				left outer join v_pmUser ud on t.TimetableExtend_pmUser_updid = ud.PMUser_id
				left join v_EvnDirection d on t.EvnDirection_id = d.EvnDirection_id and d.DirFailType_id is null
				left join v_Lpu lpud ON lpud.Lpu_id = d.Lpu_id
				left join v_LpuSection ls ON ls.LpuSection_id = t.LpuSection_id
				left join v_EvnQueue q on t.TimetableStac_id = q.TimetableStac_id and t.Person_id = q.Person_id
				left join v_pmUser qp on q.pmUser_updId=qp.pmUser_id
				left join Diag dg on dg.Diag_id=d.Diag_id
				left join v_LpuSectionBedType btype on btype.LpuSectionBedType_id = t.LpuSectionBedType_id
				{$joinPersonEncrypHIV}
			where t.TimetableStac_Day >= :StartDay
			  and t.TimetableStac_Day < :EndDay
			  and u.Lpu_id = :Lpu_id
			order by t.TimetableStac_setDate
		";
		$result = $this->db->query($sql, $param);
		$ttpdata = $result->result("array");
		foreach ($ttpdata as $ttp) {
			$outdata["data"][$ttp["TimetableStac_Day"]][] = $ttp;
			if (isset($ttp["Person_id"])) {
				$outdata["occupied"][$ttp["TimetableStac_Day"]] = true;
			}
		}
		$sql = "select TimetableStac_id as \"TimetableStac_id\" from TimetableLock where TimetableStac_id is not null";
		$res = $this->db->query($sql);
		$outdata["reserved"] = [];
		$reserved = $res->result("array");
		foreach ($reserved as $lock) {
			$outdata["reserved"][] = $lock["TimetableStac_id"];
		}
		return $outdata;
	}

	/**
	 * Получение расписания стационара для просмотра/редактирования
	 * @param $data
	 * @return array
	 */
	function getTimetableStac($data)
	{
		$outdata = [];
		$StartDay = isset($data["StartDay"]) ? strtotime($data["StartDay"]) : time();
		$outdata["StartDay"] = $StartDay;
		$param["StartDay"] = TimeToDay($StartDay);
		$param["LpuSection_id"] = $data["LpuSection_id"];
		$nTime = $StartDay;
		$msflpu = $this->getFirstRowFromQuery("select Lpu_id as \"Lpu_id\" from v_LpuSection where LpuSection_id = :LpuSection_id", ["LpuSection_id" => $data["LpuSection_id"]]);
		if (empty($_SESSION["setting"]) || empty($_SESSION["setting"]["server"])) {
			// Вынес отдельно, чтобы не повторять
			$maxDays = null;
		} elseif (!empty($_SESSION["CurArmType"]) && $_SESSION["CurArmType"] == "regpol" && $_SESSION["lpu_id"] == $msflpu["Lpu_id"]) {
			// Для регистратора запись в свою МО
			$this->load->model("LpuIndividualPeriod_model", "lipmodel");
			$individualPeriod = $this->lipmodel->getObjectIndividualPeriod(["Lpu_id" => $_SESSION["lpu_id"]], "LpuSection");
			if (!empty($data["LpuSection_id"]) && !empty($individualPeriod[$data["LpuSection_id"]])) {
				$maxDays = $individualPeriod[$data["LpuSection_id"]];
			} else {
				$maxDays = !empty($_SESSION["setting"]["server"]["stac_record_day_count"]) ? $_SESSION["setting"]["server"]["stac_record_day_count"] : null;
			}
		} elseif (!empty($_SESSION["CurArmType"]) && $_SESSION["CurArmType"] == "regpol") {
			// Для регистратора запись в чужую МО
			$maxDays = !empty($_SESSION["setting"]["server"]["stac_record_day_count_reg"]) ? $_SESSION["setting"]["server"]["stac_record_day_count_reg"] : null;
		} elseif (@$_SESSION["lpu_id"] == @$msflpu["Lpu_id"]) {
			// Для остальных пользовалелей запись в свою МО
			$maxDays = !empty($_SESSION["setting"]["server"]["stac_record_day_count_own"]) ? $_SESSION["setting"]["server"]["stac_record_day_count_own"] : null;
		} else {
			// Для остальных пользовалелей запись в чужую МО
			$maxDays = !empty($_SESSION["setting"]["server"]["stac_record_day_count_other"]) ? $_SESSION["setting"]["server"]["stac_record_day_count_other"] : null;
		}
		if (date("H:i") >= getShowNewDayTime() && $maxDays) $maxDays++;
		$param["EndDayDefault"] = TimeToDay(strtotime("+21 days", $StartDay));
		$param["EndDay"] = !empty($maxDays) ? TimeToDay(strtotime("+" . $maxDays . " days", time())) : $param["EndDayDefault"];
		if ($param["EndDay"] > $param["EndDayDefault"]) {
			// чтобы не уходило в бесконечный цикл в timetablestac_data.php
			$param["EndDay"] = $param["EndDayDefault"];
		}
		$outdata["header"] = [];
		$outdata["descr"] = [];
		$outdata["data"] = [];
		$outdata["occupied"] = [];
		for ($nCol = 0; $nCol < 21; $nCol++) {
			$nWeekDay = date('w', $nTime);
			$sClass = "work";
			if (($nWeekDay == 6) || ($nWeekDay == 0)) {
				$sClass = "relax";
			}
			$outdata["header"][TimeToDay($nTime)] = "<td class='$sClass'>" . "<b>" . $this->arShortWeekDayName[$nWeekDay] . "</b>" . date(" d", $nTime) . "</td>";
			$outdata["descr"][TimeToDay($nTime)] = [];
			$outdata["data"][TimeToDay($nTime)] = [];
			$outdata["occupied"][TimeToDay($nTime)] = false;
			$nTime = strtotime("+1 day", $nTime);
		}

		$sql = "
			select
				lsd.Day_id as \"Day_id\",
				rtrim(lsd.LpuSectionDay_Descr) as \"LpuSectionDay_Descr\",
				rtrim(u.pmUser_Name) as \"pmUser_Name\",
				lsd.LpuSectionDay_updDT as \"LpuSectionDay_updDT\"
			from
				LpuSectionDay lsd
				left join v_pmUser u on u.pmUser_id = lsd.pmUser_updID
			where LpuSection_id = :LpuSection_id
			  and Day_id >= :StartDay
			  and Day_id < :EndDay
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $param);
		$daydescrdata = $result->result("array");
		foreach ($daydescrdata as $day) {
			/**@var DateTime $LpuSectionDay_updDT */
			$LpuSectionDay_updDT = $day["LpuSectionDay_updDT"];
			$outdata["descr"][$day["Day_id"]] = [
				"LpuSectionDay_Descr" => $day["LpuSectionDay_Descr"],
				"pmUser_Name" => $day["pmUser_Name"],
				"LpuSectionDay_updDT" => isset($day["LpuSectionDay_updDT"]) ? ConvertDateFormat($LpuSectionDay_updDT,"d.m.Y H:i") : ""
			];
		}
		$lpuSectionBedTypeSql = '';
		//фильтрация записей по полу переданного Person_id
		if (!empty($data['Person_id'])) {
			$param['Person_id'] = $data['Person_id'];
			$lpuSectionBedTypeSql = "and (t.LpuSectionBedType_id in (3, (select Sex_id from v_PersonState where Person_id = :Person_id)))";
		}
		$selectPersonData = "
			p.Person_BirthDay as \"Person_BirthDay\",
			p.Person_Phone as \"Person_Phone\",
			p.PrivilegeType_id as \"PrivilegeType_id\",
			rtrim(p.Person_Firname) as \"Person_Firname\",
			rtrim(p.Person_Surname) as \"Person_Surname\",
			rtrim(p.Person_Secname) as \"Person_Secname\",
		";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = " left join v_PersonEncrypHIV peh on peh.Person_id = p.Person_id";
			$selectPersonData = "
				case when peh.PersonEncrypHIV_Encryp is null then p.Person_BirthDay else null end as \"Person_BirthDay\",
				case when peh.PersonEncrypHIV_Encryp is null then p.Person_Phone else null end as \"Person_Phone\",
				case when peh.PersonEncrypHIV_Encryp is null then p.PrivilegeType_id else null end as \"PrivilegeType_id\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Surname) else rtrim(peh.PersonEncrypHIV_Encryp) end as \"Person_Surname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Firname) else '' end as \"Person_Firname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Secname) else '' end as \"Person_Secname\",
			";
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
				t.pmUser_updID as \"pmUser_updID\",
				t.TimetableStac_updDT as \"TimetableStac_updDT\",
				t.TimetableStac_id as \"TimetableStac_id\",
				t.Person_id as \"Person_id\",
				t.TimetableStac_Day as \"TimetableStac_Day\",
				t.TimetableStac_setDate as \"TimetableStac_setDate\",
				t.TimetableType_id as \"TimetableType_id\",
				{$selectPersonData}
				t.PMUser_UpdID as \"PMUser_UpdID\",
				case 
					when t.pmUser_updid=999000
					then 'Запись через КМИС'
					when t.pmUser_updid between 1000000 and 5000000
					then 'Запись через интернет'
					else u.PMUser_Name 
				end as \"PMUser_Name\",
				lpud.Lpu_Nick as \"DirLpu_Nick\",
				d.EvnDirection_Num as \"Direction_Num\",
				to_char(d.EvnDirection_setDT, '{$this->dateTimeForm104}') as \"Direction_Date\",
				d.EvnDirection_id as \"EvnDirection_id\",
				qp.pmUser_Name as \"QpmUser_Name\",
				q.EvnQueue_insDT as \"EvnQueue_insDT\",
				dg.Diag_Code as \"Diag_Code\",
				u.Lpu_id as \"pmUser_Lpu_id\",
				t.LpuSectionBedType_id as \"LpuSectionBedType_id\",
				t.TimetableExtend_Descr as \"TimetableExtend_Descr\",
				t.TimetableExtend_updDT as \"TimetableExtend_updDT\",
				ud.pmUser_Name as \"TimetableExtend_pmUser_Name\"
			from
				v_TimetableStac t
				left join lateral (
					(select count(*) as \"cnt\"
						from v_TimetableStac
						where
							TimetableStac_Day = t.TimetableStac_Day
							and LpuSection_id = t.LpuSection_id
							and TimetableStac_id <= t.TimetableStac_id
							and COALESCE(TimeTableStac_IsDop, 1) = 1
							and Person_id is null
							and COALESCE(t.TimeTableStac_IsDop, 1) = 1
							and t.Person_id is null
					)
				) as cntRegular on true
				left outer join v_Person_ER2 p on t.Person_id = p.Person_id
				left outer join v_pmUser u on t.PMUser_UpdID = u.PMUser_id
				left outer join v_pmUser ud on t.TimetableExtend_pmUser_updid = ud.PMUser_id
				left join v_EvnDirection_all d on t.EvnDirection_id = d.EvnDirection_id and d.DirFailType_id is null and coalesce(d.EvnDirection_isAuto, 1) != 2
				left join v_Lpu lpud ON lpud.Lpu_id = d.Lpu_id
				left join v_EvnQueue q on t.TimetableStac_id = q.TimetableStac_id and t.Person_id = q.Person_id
				left join v_pmUser qp on q.pmUser_updId=qp.pmUser_id
				left join Diag dg on dg.Diag_id=d.Diag_id
				{$joinPersonEncrypHIV}
			where t.TimetableStac_Day = :currentDay
			  and t.LpuSection_id = :LpuSection_id
			  and cntRegular.cnt <= :recordCount
			  {$lpuSectionBedTypeSql}
			order by t.TimetableStac_setDate
		";
			
			$res = $this->db->query($sql, array_merge($param, $dayParams))->result('array');
			
			$resAll = array_merge($resAll, $res);
		}
		$ttpdata = $resAll;
		foreach ($ttpdata as $ttp) {
			$outdata["data"][$ttp["TimetableStac_Day"]][] = $ttp;
			if (isset($ttp["Person_id"])) {
				$outdata["occupied"][$ttp["TimetableStac_Day"]] = true;
			}
		}
		$sql = "
			select TimetableStac_id as \"TimetableStac_id\"
			from TimetableLock
			where TimetableStac_id is not null
		";
		$result = $this->db->query($sql);
		$outdata["reserved"] = [];
		$reserved = $result->result("array");
		foreach ($reserved as $lock) {
			$outdata["reserved"][] = $lock["TimetableStac_id"];
		}
		return $outdata;
	}

	/**
	 * Получение комментария на день для врача
	 * @param $data
	 * @return array|bool
	 */
	function getTTSDayComment($data)
	{
		$query = "
			select
				lsd.LpuSectionDay_Descr as \"LpuSectionDay_Descr\",
				lsd.LpuSectionDay_id as \"LpuSectionDay_id\"
			from LpuSectionDay lsd
			where LpuSection_id = :LpuSection_id
			  and Day_id = :Day_id
		";
		$queryParams = [
			"LpuSection_id" => $data["LpuSection_id"],
			"Day_id" => $data["Day"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Сохранение комментария на день
	 * @param $data
	 * @return array
	 */
	function saveTTSDayComment($data)
	{
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_lpusectionday_setdescr(
			    server_id := 2,
			    day_id := :Day_id,
			    lpusection_id := :LpuSection_id,
			    lpusectionday_descr := :LpuSectionDay_Descr,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"LpuSectionDay_Descr" => $data["LpuSectionDay_Descr"],
			"LpuSection_id" => $data["LpuSection_id"],
			"Day_id" => $data["Day"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$this->db->query($query, $queryParams);
		return ["Error_Msg" => ""];
	}

	/**
	 * Создания расписания в стационаре
	 * @param $data
	 * @return array
	 * @throws Exception
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
					select
						error_code as \"Error_Code\",
						error_message as \"Error_Msg\"
					from p_timetablestac_fill(
						lpusection_id := :LpuSection_id,
						timetablestac_day := :TimetableStac_Day,
						lpusectionbedtypecommon_count := :LpuSectionBedTypeCommon_Count,
						lpusectionbedtypeman_count := :LpuSectionBedTypeMan_Count,
						lpusectionbedtypewoman_count := :LpuSectionBedTypeWoman_Count,
						timetabletype_id := :TimetableType_id,
						starttime := :StartTime,
						endtime := :EndTime,
						timetablestac_time := :TimeTableStac_Time,
						timetablestac_isdop := :TimeTableStac_IsDop,
						timetableextend_descr := :TimetableExtend_Descr,
						pmuser_id := :pmUser_id
					);
				";
			
			if ($settings['auto_create'] == 2) {
				//получаем количество коек
				$sql = "
					Select
					-- Общее количество коек в отделении, план
					sum(LpuSectionBedState_PlanCount) as \"LpuSection_CommonCount\",
					-- Из них по основному профилю
					sum(LpuSectionBedState_ProfileCount) as \"LpuSection_ProfileCount\",
					-- Из них узких коек
					sum(LpuSectionBedState_UzCount) as \"LpuSection_UzCount\",
					-- Общее количество коек в отделении, факт
					sum(LpuSectionBedState_Fact) as \"LpuSection_Fact\",
					-- Общее количество коек по палатам
					sum(LpuSectionWard_BedCount) as \"LpuSection_BedCount\",
					-- Общее количество мужских коек
					sum(LpuSectionWard_Male) as \"LpuSectionWard_Male\",
					-- Общее количество женских коек
					sum(LpuSectionWard_Female) as \"LpuSectionWard_Female\",
					-- Общее количество коек по палатам
					sum(LpuSectionWard_BedCount) as \"LpuSectionWard_Common\",
					-- Из них на ремонте
					sum(LpuSectionWard_BedRepair) as \"LpuSection_BedRepair\",
					-- Плановый резерв коек для экстренных госпитализаций, не более
					COALESCE(LS.LpuSection_MaxEmergencyBed, 0) as \"LpuSection_MaxEmergencyBed\"
			
					from v_LpuSection LS
					left join lateral (
						Select
							sum(LpuSectionWard_BedCount) as \"LpuSectionWard_BedCount\",
							sum(LpuSectionWard_BedRepair) as \"LpuSectionWard_BedRepair\"
						from v_LpuSectionWard LSW
						where
							LSW.LpuSectionWard_setDate <=cast(dbo.tzGetDate() as date) and (LSW.LpuSectionWard_disDate >= cast(dbo.tzGetDate() as date) or LSW.LpuSectionWard_disDate is null) and
							LSW.LpuSection_id=LS.LpuSection_id
					) as LSW on true
					left join lateral (
						Select
							sum(LpuSectionWard_BedCount) as \"LpuSectionWard_Male\"
						from v_LpuSectionWard LSW
						where
							LSW.LpuSectionWard_setDate <=cast(dbo.tzGetDate() as date) and (LSW.LpuSectionWard_disDate >= cast(dbo.tzGetDate() as date) or LSW.LpuSectionWard_disDate is null) and
							LSW.LpuSection_id=LS.LpuSection_id
							and Sex_id = 1
					) as LSWM on true
					left join lateral (
						Select
							sum(LpuSectionWard_BedCount) as \"LpuSectionWard_Female\"
						from v_LpuSectionWard LSW
						where
							LSW.LpuSectionWard_setDate <=cast(dbo.tzGetDate() as date) and (LSW.LpuSectionWard_disDate >= cast(dbo.tzGetDate() as date) or LSW.LpuSectionWard_disDate is null) and
							LSW.LpuSection_id=LS.LpuSection_id
							and Sex_id = 2
					) as LSWF on true
					left join lateral (
						Select
							sum(LpuSectionBedState_Plan) as \"LpuSectionBedState_PlanCount\",
							sum(case when LSBS.LpuSectionProfile_id = LSS.LpuSectionProfile_id then LpuSectionBedState_Plan else 0 end) as \"LpuSectionBedState_ProfileCount\",
							sum(case when LSBS.LpuSectionProfile_id != LSS.LpuSectionProfile_id then LpuSectionBedState_Plan else 0 end) as \"LpuSectionBedState_UzCount\",
							sum(LpuSectionBedState_Fact) as \"LpuSectionBedState_Fact\"
			
						from v_LpuSectionBedState LSBS
						left join v_LpuSection LSS on LSS.LpuSection_id = LSBS.LpuSection_id
						where
							LSBS.LpuSectionBedState_begDate <=cast(dbo.tzGetDate() as date) and (LSBS.LpuSectionBedState_endDate >= cast(dbo.tzGetDate() as date) or LSBS.LpuSectionBedState_endDate is null) and
							LSBS.LpuSection_id=LS.LpuSection_id
					) as LSBS on true
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
					select
						t.TimeTableStac_id as \"TimeTableStac_id\"
					from v_TimetableStac t
					where t.TimetableStac_Day = :TimetableStac_Day
						and t.LpuSection_id = :LpuSection_id
					order by t.TimetableStac_setDate
					limit 1
				";
				
				$data['TimetableStac_Day'] = $day;
				$res = $this->getFirstRowFromQuery($sql, $data, true);
				if (!empty($res)) {
					//если расписание уже существует
					
					//проверка на пересечение по времени
					$sql = "
						select
							count(*) as \"cnt\"
						from v_TimetableStac t
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
							Record_Male as \"Record_Male\",
							Record_Female as \"Record_Female\",
							Record_Regular as \"Record_Regular\"
						from
							(
								select
									count(LpuSectionBedType_id) as \"Record_Male\"
								from v_TimetableStac t
								where
									t.TimetableStac_Day = :TimetableStac_Day
									and t.LpuSection_id = :LpuSection_id
									and COALESCE(TimeTableStac_IsDop, 1) = 1
									and t.LpuSectionBedType_id = 1
							) TSM
							left join lateral (
								select
									count(LpuSectionBedType_id) as \"Record_Female\"
								from v_TimetableStac t
								where
									t.TimetableStac_Day = :TimetableStac_Day
									and t.LpuSection_id = :LpuSection_id
									and COALESCE(TimeTableStac_IsDop, 1) = 1
									and t.LpuSectionBedType_id = 2
							) as TSF on true
							left join lateral (
								select
									count(LpuSectionBedType_id) as \"Record_Regular\"
								from v_TimetableStac t
								where
									t.TimetableStac_Day = :TimetableStac_Day
									and t.LpuSection_id = :LpuSection_id
									and COALESCE(TimeTableStac_IsDop, 1) = 1
									and t.LpuSectionBedType_id = 3
							) as TSR on true
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
	 * @param $data
	 * @return bool
	 */
	function checkTimetableStacDayNotOccupied($data)
	{
		if (isset($data["Day"])) {
			$query = "
				select count(1) as cnt
				from v_TimetableStac_lite
				where TimetableStac_Day = :Day
				  and LpuSection_id = :LpuSection_id
				  and Person_id is not null
			";
			$queryParams = [
				"Day" => $data["Day"],
				"LpuSection_id" => $data["LpuSection_id"],
			];
			$result = $this->db->query($query, $queryParams);
		}
		if (isset($data["StartDay"])) {
			$query = "
				select count(1) as cnt
				from v_TimetableStac_lite
				where TimetableStac_Day between :StartDay and :EndDay
				  and LpuSection_id = :LpuSection_id
				  and Person_id is not null
			";
			$queryParams = [
				"StartDay" => $data["StartDay"],
				"EndDay" => $data["EndDay"],
				"LpuSection_id" => $data["LpuSection_id"],
			];
			$result = $this->db->query($query, $queryParams);
		}
		/**@var CI_DB_result $result */
		if (is_object($result)) {
			$result = $result->result("array");
		}
		if ($result[0]["cnt"] > 0) {
			return false;
		}
		return true;
	}

	/**
	 * Копирование расписания в стационаре
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function copyTTSSchedule($data)
	{
		if (empty($data["CopyToDateRange"][0])) {
			throw new Exception("Не указан диапазон для вставки расписания.");
		}
		$data["StartDay"] = TimeToDay(strtotime($data["CopyToDateRange"][0]));
		$data["EndDay"] = TimeToDay(strtotime($data["CopyToDateRange"][1]));
		$archive_database_enable = $this->config->item("archive_database_enable");
		if (!empty($archive_database_enable)) {
			$archive_database_date = $this->config->item("archive_database_date");
			if (strtotime($data["CreateDateRange"][1]) < strtotime($archive_database_date)) {
				throw new Exception("Нельзя скопировать расписание на архивные даты.");
			}
		}
		if (true !== ($res = $this->checkTimetableStacDayNotOccupied($data))) {
			throw new Exception("Нельзя скопировать расписание на промежуток, так как на нем занятые бирки.");
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
			$query = "
				select
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from p_timetablestac_copy(
				    lpusection_id := :LpuSection_id,
				    sourcestartday := :SourceStartDay,
				    sourceendday := :SourceEndDay,
				    targetstartday := :TargetStartDay,
				    targetendday := :TargetEndDay,
				    copytimetableextend_descr := :CopyTimetableExtend_Descr,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"LpuSection_id" => $data["LpuSection_id"],
				"SourceStartDay" => $SourceStartDay,
				"SourceEndDay" => $SourceEndDay,
				"TargetStartDay" => $nTargetStart,
				"TargetEndDay" => $nTargetEnd,
				"CopyTimetableExtend_Descr" => ($data["CopyTTSComments"] == 1) ? 1 : null,
				"pmUser_id" => $data["pmUser_id"]
			];
			/**@var CI_DB_result $result */
			$result = $this->db->query($query, $queryParams);
			if (is_object($result)) {
				$resp = $result->result("array");
				if (count($resp) > 0 && empty($resp[0]["Error_Msg"])) {
					// отправка STOMP-сообщения
					sendFerStompMessage([
						"timeTable" => "TimetableStac",
						"action" => "AddTicket",
						"setDate" => date("c"),
						"begDate" => date("c", DayMinuteToTime($nTargetStart, 0)),
						"endDate" => date("c", DayMinuteToTime($nTargetEnd, 0)),
						"MedStaffFact_id" => $data["session"]["CurMedStaffFact_id"]
					], "RulePeriod");
				}
			}
			for ($i = 0; $i <= $nTargetEnd - $nTargetStart; $i++) {
				// Пересчет кэша коек по дням теперь прямо в хранимке
				if ($data["CopyDayComments"] == 1) {
					$query = "
						select
						    error_code as \"Error_Code\",
						    error_message as \"Error_Msg\"
						from p_lpusectionday_setdescr(
						    server_id := 2,
						    lpusectionday_id := null,
						    day_id := :TargetDay_id,
						    lpusection_id := :LpuSection_id,
						    lpusectionday_descr := (select LpuSectionDay_Descr from LpuSectionDay where LpuSection_id = :LpuSection_id and Day_id = :SourceDay_id),
						    pmuser_id := :pmUser_id
						);
					";
					$queryParams = [
						"LpuSection_id" => $data["LpuSection_id"],
						"TargetDay_id" => $nTargetStart + $i,
						"SourceDay_id" => TimeToDay(strtotime($data["CreateDateRange"][0])) + $i,
						"pmUser_id" => $data["pmUser_id"]
					];
					$this->db->query($query, $queryParams);
				}
			}
			$n++;
		}
		return ["Error_Msg" => ""];
	}

	/**
	 * Очистка дня для стационара
	 * @param $data
	 * @return array
	 */
	function ClearDayTTS($data)
	{
		$query = "
			select
				cast(null as bigint) as \"TimetableStac_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_TimetableStac_clearDay(
				TimetableStac_Day := :TimetableStac_Day,
				LpuSection_id := :LpuSection_id,
				pmUser_id := :pmUser_id
			)
		";
		$queryParams = [
			"LpuSection_id" => $data["LpuSection_id"],
			"TimetableStac_Day" => $data["Day"],
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$resp = $result->result("array");
			if (count($resp) > 0 && empty($resp[0]["Error_Msg"])) {
				// отправка STOMP-сообщения
				sendFerStompMessage([
					"timeTable" => "TimetableStac",
					"action" => "DelTicket",
					"setDate" => date("c"),
					"begDate" => date("c", DayMinuteToTime($data["Day"], 0)),
					"endDate" => date("c", DayMinuteToTime($data["Day"], 0)),
					"MedStaffFact_id" => $data["session"]["CurMedStaffFact_id"]
				], "RulePeriod");
			}
		}
		return ["Error_Msg" => ""];
	}

	/***
	 * Удаление бирки в стационаре
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	function DeleteTTS($data)
	{
		$data["TimetableStacGroup"] = (isset($data["TimetableStac_id"]))?[$data["TimetableStac_id"]]:json_decode($data["TimetableStacGroup"]);
		if (true !== ($res = $this->checkTimetablesFree($data))) {
			return $res;
		}
		// Получаем отделение и день, а также заодно проверяем, что бирка существует
		$TimetableStacGroupString = implode(", ", $data["TimetableStacGroup"]);
		$query = "
			select
				TimetableStac_id as \"TimetableStac_id\",
				LpuSection_id as \"LpuSection_id\",
				TimetableStac_Day as \"TimetableStac_Day\"
			from v_TimetableStac_lite
			where TimetableStac_id in ({$TimetableStacGroupString})
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result('array');
		// Удаляем каждую бирку по отдельности. Не лучший вариант конечно
		foreach ($result as $row) {
			//Удаляем бирку
			$query = "
				select
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from p_timetablestac_del(
				    timetablestac_id := :TimetableStac_id,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"TimetableStac_id" => $row["TimetableStac_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$result = $this->db->query($query, $queryParams);
			if (is_object($result)) {
				$resp = $result->result("array");
				if (!is_array($resp) || count($resp) == 0 || !empty($resp[0]["Error_Msg"])) {
					return $resp;
				}
				// отправка STOMP-сообщения
				sendFerStompMessage([
					"id" => $data["TimetableStac_id"],
					"timeTable" => "TimetableStac",
					"action" => "DelTicket",
					"setDate" => date("c")
				], "Rule");
			}
		}
		return ["success" => true];
	}

	/**
	 * Получение расписания на один день
	 * @param $data
	 * @return array
	 */
	function getTimetableStacOneDay($data)
	{
		$outdata = [];
		$StartDay = isset($data["StartDay"]) ? strtotime($data["StartDay"]) : time();
		$outdata["StartDay"] = $StartDay;
		$param["StartDay"] = TimeToDay($StartDay);
		$param["LpuSection_id"] = $data["LpuSection_id"];
		$outdata["day_comment"] = null;
		$outdata["data"] = [];
		$query = "
			select
				lsd.Day_id as \"Day_id\",
				rtrim(lsd.LpuSectionDay_Descr) as \"LpuSectionDay_Descr\",
				rtrim(u.pmUser_Name) as \"pmUser_Name\",
				lsd.LpuSectionDay_updDT as \"LpuSectionDay_updDT\"
			from
				LpuSectionDay lsd
				left join v_pmUser u on u.pmUser_id = lsd.pmUser_updID
			where LpuSection_id = :LpuSection_id
			  and Day_id = :StartDay
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $param);
		$daydescrdata = $result->result("array");
		if (isset($daydescrdata[0]["LpuSectionDay_Descr"])) {
			/**@var DateTime $LpuSectionDay_updDT */
			$LpuSectionDay_updDT = $daydescrdata[0]["LpuSectionDay_updDT"];
			$outdata["day_comment"] = [
				"LpuSectionDay_Descr" => $daydescrdata[0]["LpuSectionDay_Descr"],
				"pmUser_Name" => $daydescrdata[0]["pmUser_Name"],
				"LpuSectionDay_updDT" => isset($daydescrdata[0]["LpuSectionDay_updDT"]) ? ConvertDateFormat($LpuSectionDay_updDT,"d.m.Y H:i") : ""
			];
		}
		$selectPersonData = "
			p.Person_BirthDay as \"Person_BirthDay\",
			p.Person_Phone as \"Person_Phone\",
			case when a1.Address_id is not null
				then  a1.Address_Address
				else a.Address_Address
			end as \"Address_Address\",
			case
				when a1.Address_id is not null then a1.KLTown_id else a.KLTown_id
			end as \"KLTown_id\",
			case
				when a1.Address_id is not null then a1.KLStreet_id else a.KLStreet_id
			end as \"KLStreet_id\",
			case
				when a1.Address_id is not null then a1.Address_House else a.Address_House
			end as \"Address_House\",
			j.Job_Name as \"Job_Name\",
			lpu.Lpu_Nick as \"Lpu_Nick\",
			p.PrivilegeType_id as \"PrivilegeType_id\",
			rtrim(p.Person_Firname) as \"Person_Firname\",
			rtrim(p.Person_Surname) as \"Person_Surname\",
			rtrim(p.Person_Secname) as \"Person_Secname\",
		";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data["session"])) {
			$joinPersonEncrypHIV = " left join v_PersonEncrypHIV peh on peh.Person_id = p.Person_id";
			$selectPersonData = "
				case when peh.PersonEncrypHIV_Encryp is null then p.Person_BirthDay else null end as \"Person_BirthDay\",
				case when peh.PersonEncrypHIV_Encryp is null then p.Person_Phone else null end as \"Person_Phone\",
				case when peh.PersonEncrypHIV_Encryp is not null then null
					when a1.Address_id is not null then a1.Address_Address else a.Address_Address
				end as \"Address_Address\",
				case when peh.PersonEncrypHIV_Encryp is not null then null
					when a1.Address_id is not null then a1.KLTown_id else a.KLTown_id
				end as \"KLTown_id\",
				case when peh.PersonEncrypHIV_Encryp is not null then null
					when a1.Address_id is not null then a1.KLStreet_id else a.KLStreet_id
				end as \"KLStreet_id\",
				case when peh.PersonEncrypHIV_Encryp is not null then null
					when a1.Address_id is not null then a1.Address_House else a.Address_House
				end as \"Address_House\",
				case when peh.PersonEncrypHIV_Encryp is null then j.Job_Name else null end as \"Job_Name\",
				case when peh.PersonEncrypHIV_Encryp is null then lpu.Lpu_Nick else null end as \"Lpu_Nick\",
				case when peh.PersonEncrypHIV_Encryp is null then p.PrivilegeType_id else null end as \"PrivilegeType_id\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Surname) else rtrim(peh.PersonEncrypHIV_Encryp) end as \"Person_Surname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Firname) else '' end as \"Person_Firname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Secname) else '' end as \"Person_Secname\",
			";
		}
		
		$settings = $this->getStacSheduleSettings($param['StartDay'], $data['LpuSection_id']);
		
		$dayParams = array(
			'currentDay' => $param['StartDay'],
			'LpuSection_id' => $param['LpuSection_id'],
			'recordCount' => ($settings['auto_create'] == 2) ?
				$this->getRecordCountForDay($param['LpuSection_id'], date('Y-m-d', DayMinuteToTime($param['StartDay'], 0))) : 100
		);
		
		$query = "
			select
				t.pmUser_updID as \"pmUser_updID\",
				t.TimetableStac_updDT as \"TimetableStac_updDT\",
				t.TimetableStac_id as \"TimetableStac_id\",
				t.LpuSectionBedType_id as \"LpuSectionBedType_id\",
				t.Person_id as \"Person_id\",
				t.TimetableStac_Day as \"TimetableStac_Day\",
				{$selectPersonData}
				t.PMUser_UpdID as \"PMUser_UpdID\",
				case 
					when t.pmUser_updid=999000
					then 'Запись через КМИС'
					when t.pmUser_updid between 1000000 and 5000000
					then 'Запись через интернет'
					else u.PMUser_Name 
				end as \"PMUser_Name\",
				lpud.Lpu_Nick as \"DirLpu_Nick\",
				d.EvnDirection_Num as \"Direction_Num\",
				to_char(d.EvnDirection_setDate, '{$this->dateTimeForm104}') as \"Direction_Date\",
				d.EvnDirection_id as \"EvnDirection_id\",
				TimetableType_id as \"TimetableType_id\",
				qp.pmUser_Name as \"QpmUser_Name\",
				dg.Diag_Code as \"Diag_Code\",
				ls.LpuSectionHospType_id as \"LpuSectionHospType_id\",
				u.Lpu_id as \"pmUser_Lpu_id\",
				EvnQueue_insDT as \"EvnQueue_insDT\",
				t.TimetableStac_EmStatus as \"TimetableStac_EmStatus\",
				t.EmergencyData_id as \"EmergencyData_id\",
				ed.EmergencyData_BrigadeNum as \"EmergencyData_BrigadeNum\",
				ed.EmergencyData_CallNum as \"EmergencyData_CallNum\",
				cd.Diag_Code||' '||cd.Diag_Name as \"Diag_Name\",
				TimetableStac_setDate as \"TimetableStac_setDate\",
				t.TimetableExtend_Descr as \"TimetableExtend_Descr\",
				t.TimetableExtend_updDT as \"TimetableExtend_updDT\",
				ud.pmUser_Name as \"TimetableExtend_pmUser_Name\"
			from
				v_TimetableStac t
				left join lateral (
					(select count(*) as \"cnt\"
						from v_TimetableStac
						where
							TimetableStac_Day = t.TimetableStac_Day
							and LpuSection_id = t.LpuSection_id
							and TimetableStac_id <= t.TimetableStac_id
							and COALESCE(TimeTableStac_IsDop, 1) = 1
							and Person_id is null
							and COALESCE(t.TimeTableStac_IsDop, 1) = 1
							and t.Person_id is null
					)
				) as cntRegular on true
				left outer join LpuSection ls on ls.LpuSection_id = t.LpuSection_id
				left outer join v_Person_ER p on t.Person_id = p.Person_id
				left outer join Address a on p.UAddress_id = a.Address_id
				left outer join Address a1 on p.PAddress_id = a1.Address_id
				left outer join v_Job_ER j on p.Job_id = j.Job_id
				left outer join v_pmUser u on t.PMUser_UpdID = u.PMUser_id
				left outer join v_pmUser ud on t.TimetableExtend_pmUser_updid = ud.PMUser_id
				left outer join v_Lpu lpu on lpu.Lpu_id = p.Lpu_id
				left outer join v_EvnDirection_all d on t.EvnDirection_id = d.EvnDirection_id and d.DirFailType_id is null and d.Person_id = t.Person_id and coalesce(d.EvnDirection_isAuto, 1) != 2
				left outer join v_Lpu lpud ON lpud.Lpu_id = d.Lpu_id
				left join v_EvnQueue q on t.TimetableStac_id = q.TimetableStac_id and t.Person_id = q.Person_id
				left join v_pmUser qp on q.pmUser_updId = qp.pmUser_id
				left join Diag dg on dg.Diag_id = d.Diag_id
				left join EmergencyData ed on ed.EmergencyData_id = t.EmergencyData_id and t.TimetableType_id = 6
				left join Diag cd on cd.Diag_id = ed.Diag_id
				{$joinPersonEncrypHIV}
			where t.TimetableStac_Day = :currentDay
			  and t.LpuSection_Id = :LpuSection_id
			  and cntRegular.cnt <= :recordCount
			order by
				TimetableStac_Day,
			    LpuSectionBedType_id,
				TimetableType_id,
			    t.TimetableStac_id
		";
		$result = $this->db->query($query, array_merge($param, $dayParams));
		$ttsdata = $result->result("array");
		foreach ($ttsdata as $tts) {
			$outdata["data"][] = $tts;
		}
		$sql = "
			select TimetableStac_id as \"TimetableStac_id\"
			from TimetableLock
			where TimetableStac_id is not null
		";
		$result = $this->db->query($sql);
		$outdata["reserved"] = [];
		$reserved = $result->result("array");
		foreach ($reserved as $lock) {
			$outdata["reserved"][] = $lock["TimetableStac_id"];
		}
		return $outdata;
	}

	/**
	 * Редактирование переданного набора бирок
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function editTTSSet($data)
	{
		$TTSSet = json_decode($data["selectedTTS"]);
		if ($this->checkTTSOccupied($TTSSet)) {
			throw new Exception("Одна из выбранных бирок занята. Операция невозможна.");
		}
		// Пустая строка передается как NULL, надо как пустую строку передавать
		$data["TimetableExtend_Descr"] = ($data["ChangeTTSDescr"])
			?isset($data["TimetableExtend_Descr"]) ? $data["TimetableExtend_Descr"] : ""
			:null;
		$data["TimetableType_id"] = ($data["ChangeTTSType"])
			?isset($data["TimetableType_id"]) ? $data["TimetableType_id"] : 1
			:null;
		foreach ($TTSSet as $TTS) {
			$query = "
				select
				    (select TimetableType_SysNick from v_TimetableType where TimetableType_id = :TimetableType_id limit 1) as \"TimetableType_SysNick\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from p_timetablestac_edit(
				    timetablestac_id := :TimetableStac_id,
				    timetabletype_id := :TimetableType_id,
				    timetableextend_descr := :TimetableExtend_Descr,
				    pmuser_id := :pmUser_id
				);
			";
			$queryParams = [
				"TimetableStac_id" => $TTS,
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
							"id" => $TTS,
							"timeTable" => "TimetableStac",
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
	 * @param $TTSSet
	 * @return bool
	 */
	function checkTTSOccupied($TTSSet)
	{
		if (count($TTSSet) == 0) {
			return false;
		}
		$TTSSetString = implode(",", $TTSSet);
		$sql = "
			select count(1) as cnt
			from v_TimetableStac_lite
			where TimetableStac_id in ({$TTSSetString})
			  and Person_id is not null
		";
		$res = $this->db->query($sql);
		if (is_object($res)) {
			$res = $res->result("array");
		}
		return $res[0]["cnt"] > 0;
	}

	/**
	 * Перенос бирки с одного события на другое, используется при смене пациента в документе.
	 * @param $data
	 */
	function onSetAnotherPersonForDocument($data)
	{
		$query = "
			update TimetableStac
			set Evn_id = :Evn_id,
			    Person_id = :Person_id
			where Evn_id = :Evn_oldid
		";
		$this->db->query($query, $data);
	}
}