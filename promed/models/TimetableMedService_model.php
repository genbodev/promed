<?php

/**
 * TimetableMedService_model - модель для работы с расписанием службы
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

class TimetableMedService_model extends Timetable_model {

	/**
	 * 	Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->helper('Reg');
	}
	
	/**
	 * Возвращает информацию по использованию примечаний 
	 */
	function getTimeTableExtendData($data) {
		// Для Карелии и других менее нагруженных регионов 
		$extend = array(
			'source' => "v_TimetableMedService_lite",
			'fields' => 
					"null as TimetableExtend_Descr,
					null as TimetableExtend_updDT,
					null as TimetableExtend_pmUser_Name",
			'join' => ""
		);
		$isExtend=(isset($data['session']['region']) && in_array($data['session']['region']['nick'], array('kareliya')));
		if ($isExtend) {
				$extend = array(
				'source' => "v_TimetableMedService",
				'fields' => 
					"t.TimetableExtend_Descr,
					t.TimetableExtend_updDT,
					ud.pmUser_Name as TimetableExtend_pmUser_Name",
				'join' => "left outer join v_pmUser ud with (nolock) on t.TimetableExtend_pmUser_updid = ud.PMUser_id"
			);
		}
		return $extend;
	}

	/**
	 * Получение расписания службы для редактирования
	 */
	function getTimetableMedServiceForEdit( $data ) {

		$outdata = array(); $filter= '';

		if ( !isset($data['MedService_id']) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Не указана служба, для которой показывать расписание'
			);
		}

		// без бирок на услугу службы
		if (!empty($data['withoutUslugaComplexTimetable'])) {
			$filter = "  and t.UslugaComplexMedService_id is null ";
		}

		$StartDay = isset($data['StartDay']) ? strtotime($data['StartDay']) : time();

		$outdata['StartDay'] = $StartDay;

		$param['StartDay'] = TimeToDay($StartDay);
		$param['EndDay'] = TimeToDay(strtotime("+14 days", $StartDay));
		$param['MedService_id'] = $data['MedService_id'];
		
		$param['StartDate'] = date( "Y-m-d", $StartDay );
		$param['EndDate'] = date( "Y-m-d", strtotime( "+14 days", $StartDay ) );

		$nTime = $StartDay;

		if (empty($data['dntUseFilterMaxDayRecord']) || $data['dntUseFilterMaxDayRecord'] != true) {
			$msflpu = $this->getFirstRowFromQuery("select Lpu_id from v_MedService with (nolock) where MedService_id = ?", array($data['MedService_id']));
			$maxDays = GetMedServiceDayCount($msflpu['Lpu_id'], $data['MedService_id']);

			if (date("H:i") >= getShowNewDayTime() && $maxDays) $maxDays++;
			$param['EndDate'] = !empty($maxDays) ? date("Y-m-d", strtotime("+" . $maxDays . " days", time())) : $param['EndDate'];
		}

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
				msd.Day_id,
				rtrim(msd.MedServiceDay_Descr) as MedServiceDay_Descr,
				rtrim(u.pmUser_Name) as pmUser_Name,
				msd.MedServiceDay_updDT
			from MedServiceDay msd with (nolock)
			left join v_pmUser u with (nolock) on u.pmUser_id = msd.pmUser_updID
			where MedService_id = :MedService_id
				and Day_id >= :StartDay
				and Day_id < :EndDay ";

		$res = $this->db->query($sql, $param);

		$daydescrdata = $res->result('array');

		foreach ( $daydescrdata as $day ) {
			$outdata['descr'][$day['Day_id']] = array(
				'MedServiceDay_Descr' => $day['MedServiceDay_Descr'],
				'pmUser_Name' => $day['pmUser_Name'],
				'MedServiceDay_updDT' => isset($day['MedServiceDay_updDT']) ? $day['MedServiceDay_updDT']->format("d.m.Y H:i") : ''
			);
		}
		$ext = $this->getTimeTableExtendData($data);

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

		// if(!in_array($data['session']['region']['nick'], array('kareliya'))) {    #146162
		// 	$filter = 'and (t.TimetableMedService_deleted is Null or t.TimetableMedService_deleted != 2)';
		// }
		$sql = "
			select 
					t.pmUser_updID,
					t.TimetableMedService_updDT,
					t.TimetableMedService_id,
					t.Person_id,
					t.TimetableMedService_Day,
					t.TimetableMedService_begTime as TimetableMedService_begTime,
					t.TimetableType_id,
					t.TimetableMedService_IsDop,
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
					d.EvnDirection_TalonCode as Direction_TalonCode,
					convert(varchar(10), d.EvnDirection_setDate,104) as Direction_Date,
					d.EvnDirection_id as EvnDirection_id,
					qp.pmUser_Name as QpmUser_Name,
					epd.EvnPrescr_id,
					q.EvnQueue_insDT as EvnQueue_insDT,
					dg.Diag_Code,
					u.Lpu_id as pmUser_Lpu_id,
					{$ext['fields']}
				from {$ext['source']} t with (nolock)
				left join v_MedService ms with (nolock) on ms.MedService_id = t.MedService_id
				left join v_Person_ER2 p with (nolock) on t.Person_id = p.Person_id
				left join v_pmUser u with (nolock) on t.PMUser_UpdID = u.PMUser_id
				{$ext['join']}
				outer apply  (
					Select top 1 d.*, Evn.Evn_setDT as EvnDirection_setDate, Evn.Lpu_id from EvnDirection d with (nolock)  
					inner join Evn (nolock) on Evn.Evn_id = d.EvnDirection_id and Evn.Evn_deleted = 1 
					where
					t.TimetableMedService_id = d.TimetableMedService_id
					and d.DirFailType_id is null
					--and d.Person_id = t.Person_id
				) d
				outer apply (
					select top 1
						epd.EvnPrescr_id
					from
						v_EvnPrescrDirection epd (nolock)
					where
						epd.EvnDirection_id = d.EvnDirection_id
				) epd
				left join v_Lpu lpud with (nolock) ON lpud.Lpu_id = d.Lpu_id
				outer apply  (
					Select top 1 q.*, Evn.pmUser_updId, Evn_insDT as EvnQueue_insDT from EvnQueue q with (nolock) 
					inner join Evn (nolock) on Evn.Evn_id = q.EvnQueue_id and Evn.Evn_deleted = 1 
					where 
					t.TimetableMedService_id = q.TimetableMedService_id
					--and t.Person_id = q.Person_id
				) q
				left join v_pmUser qp with (nolock) on q.pmUser_updId = qp.pmUser_id
				left join v_Diag dg with (nolock) on dg.Diag_id = d.Diag_id
				{$joinPersonEncrypHIV}
				where t.TimetableMedService_Day >= :StartDay
					and t.TimetableMedService_Day < :EndDay
					and t.MedService_id = :MedService_id
					and cast(TimetableMedService_begTime as date) between :StartDate and :EndDate
					{$filter}
				order by t.TimetableMedService_begTime";


		$res = $this->db->query($sql, $param);

		//echo getDebugSql($sql, $param);

		$ttgdata = $res->result('array');


		foreach ( $ttgdata as $ttg ) {
			$outdata['data'][$ttg['TimetableMedService_Day']][] = $ttg;
			if ( isset($ttg['Person_id']) ) {
				$outdata['occupied'][$ttg['TimetableMedService_Day']] = true;
			}
		}

		$sql = "
			select TimetableMedService_id from TimetableLock with(nolock) where TimetableMedService_id is not null";

		$res = $this->db->query($sql);

		$outdata['reserved'] = array();
		$reserved = $res->result('array');
		foreach ( $reserved as $lock ) {
			$outdata['reserved'][] = $lock['TimetableMedService_id'];
		}

		return $outdata;
	}

	/**
	 * Проверка есть ли бирки на услуге службы
	 */
	function getTimetableUslugaComplexCount( $data ) {

		if ( !isset($data['UslugaComplexMedService_id']) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Не указана услуга, для которой показывать расписание'
			);
		}

		$StartDay = isset($data['StartDay']) ? strtotime($data['StartDay']) : time();

		$params = array(
			'StartDay' => TimeToDay($StartDay),
			'EndDay' => TimeToDay(strtotime("+14 days", $StartDay)),
			'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id'],
			'StartDate' =>  date("Y-m-d", $StartDay),
			'EndDate' => date( "Y-m-d", strtotime( "+14 days", $StartDay ) )
		);

		$result = $this->getFirstResultFromQuery("
			select count(t.TimetableMedService_id) as cnt
			from v_TimetableMedService_lite t (nolock)
			where (1=1)
					and t.TimetableMedService_Day >= :StartDay
					and t.TimetableMedService_Day < :EndDay
					and t.UslugaComplexMedService_id = :UslugaComplexMedService_id
					and t.TimetableMedService_begTime between :StartDate and :EndDate
		", $params);

		return $result;
	}

	/**
	 * Получение расписания услуги для редактирования
	 */
	function getTimetableUslugaComplexForEdit( $data ) {

		$outdata = array();

		if ( !isset($data['UslugaComplexMedService_id']) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Не указана услуга, для которой показывать расписание'
			);
		}

		$StartDay = isset($data['StartDay']) ? strtotime($data['StartDay']) : time();

		$outdata['StartDay'] = $StartDay;

		$param['StartDay'] = TimeToDay($StartDay);
		$param['EndDay'] = TimeToDay(strtotime("+14 days", $StartDay));
		$param['UslugaComplexMedService_id'] = $data['UslugaComplexMedService_id'];
		$param['StartDate'] = date( "Y-m-d", $StartDay );
		$param['EndDate'] = date( "Y-m-d", strtotime( "+14 days", $StartDay ) );

		if (empty($data['dntUseFilterMaxDayRecord']) || $data['dntUseFilterMaxDayRecord'] != true) {
			$msflpu = $this->getFirstRowFromQuery("select ms.Lpu_id, ms.MedService_id from v_UslugaComplexMedService ucms with (nolock) left join v_MedService ms (nolock) on ucms.MedService_id = ms.MedService_id where ucms.UslugaComplexMedService_id = ?", array($data['UslugaComplexMedService_id']));
			$maxDays = GetMedServiceDayCount($msflpu['Lpu_id'], $msflpu['MedService_id']);

			if (date("H:i") >= getShowNewDayTime() && $maxDays) $maxDays++;
			$param['EndDate'] = !empty($maxDays) ? date("Y-m-d", strtotime("+" . $maxDays . " days", time())) : $param['EndDate'];
		}

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
				msd.Day_id,
				rtrim(msd.MedServiceDay_Descr) as MedServiceDay_Descr,
				rtrim(u.pmUser_Name) as pmUser_Name,
				msd.MedServiceDay_updDT
			from MedServiceDay msd with (nolock)
			left join v_pmUser u with(nolock) on u.pmUser_id = msd.pmUser_updID
			where UslugaComplexMedService_id = :UslugaComplexMedService_id
				and Day_id >= :StartDay
				and Day_id < :EndDay ";

		$res = $this->db->query($sql, $param);

		$daydescrdata = $res->result('array');

		foreach ( $daydescrdata as $day ) {
			$outdata['descr'][$day['Day_id']] = array(
				'MedServiceDay_Descr' => $day['MedServiceDay_Descr'],
				'pmUser_Name' => $day['pmUser_Name'],
				'MedServiceDay_updDT' => isset($day['MedServiceDay_updDT']) ? $day['MedServiceDay_updDT']->format("d.m.Y H:i") : ''
			);
		}
		$ext = $this->getTimeTableExtendData($data);
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

		$filter= '';
		// if(!in_array($data['session']['region']['nick'], array('kareliya'))) {  #146162
		// 	$filter = 'and (t.TimetableMedService_deleted is Null or t.TimetableMedService_deleted != 2)';
		// }

		$sql = "
			select
					t.pmUser_updID,
					t.TimetableMedService_updDT,
					t.TimetableMedService_id,
					t.Person_id,
					t.TimetableMedService_Day,
					t.TimetableMedService_begTime as TimetableMedService_begTime,
					t.TimetableType_id,
					t.TimetableMedService_IsDop,
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
					d.EvnDirection_TalonCode as Direction_TalonCode,
					convert(varchar(10), d.EvnDirection_setDate,104) as Direction_Date,
					d.EvnDirection_id as EvnDirection_id,
					qp.pmUser_Name as QpmUser_Name,
					epd.EvnPrescr_id,
					q.EvnQueue_insDT as EvnQueue_insDT,
					dg.Diag_Code,
					u.Lpu_id as pmUser_Lpu_id,
					{$ext['fields']}
				from {$ext['source']} t with (nolock)
				--left outer join v_MedService ms with (nolock) on ms.MedService_id = t.MedService_id
				left outer join v_Person_ER2 p with (nolock) on t.Person_id = p.Person_id
				left outer join v_pmUser u with (nolock) on t.PMUser_UpdID = u.PMUser_id
				{$ext['join']}
				outer apply  (
					Select top 1 d.*, Evn.Evn_setDT as EvnDirection_setDate, Evn.Lpu_id from EvnDirection d with (nolock)  
					inner join Evn (nolock) on Evn.Evn_id = d.EvnDirection_id and Evn.Evn_deleted = 1 
					where
					t.TimetableMedService_id = d.TimetableMedService_id
					and d.DirFailType_id is null
					--and d.Person_id = t.Person_id
				) d
				outer apply (
					select top 1
						epd.EvnPrescr_id
					from
						v_EvnPrescrDirection epd (nolock)
					where
						epd.EvnDirection_id = d.EvnDirection_id
				) epd
				left join v_Lpu lpud with (nolock) ON lpud.Lpu_id = d.Lpu_id
				outer apply  (
					Select top 1 q.*, Evn.pmUser_updId, Evn_insDT as EvnQueue_insDT 
					from EvnQueue q with (nolock) 
					inner join Evn (nolock) on Evn.Evn_id = q.EvnQueue_id and Evn.Evn_deleted = 1 
					where 
						t.TimetableMedService_id = q.TimetableMedService_id
						--and t.Person_id = q.Person_id
				) q
				left join v_pmUser qp with (nolock) on q.pmUser_updId = qp.pmUser_id
				left join Diag dg with (nolock) on dg.Diag_id = d.Diag_id
				{$joinPersonEncrypHIV}
				where t.TimetableMedService_Day >= :StartDay
					and t.TimetableMedService_Day < :EndDay
					and t.UslugaComplexMedService_id = :UslugaComplexMedService_id
					and cast(TimetableMedService_begTime as date) between :StartDate and :EndDate
					{$filter}
				order by t.TimetableMedService_begTime";

		//echo getDebugSql($sql, $param);die;
		$res = $this->db->query($sql, $param);
		$ttgdata = $res->result('array');

		foreach ( $ttgdata as $ttg ) {
			$outdata['data'][$ttg['TimetableMedService_Day']][] = $ttg;
			if ( isset($ttg['Person_id']) ) {
				$outdata['occupied'][$ttg['TimetableMedService_Day']] = true;
			}
		}

		$sql = "
			select TimetableMedService_id from TimetableLock with(nolock) where TimetableMedService_id is not null";

		$res = $this->db->query($sql);

		$outdata['reserved'] = array();
		$reserved = $res->result('array');
		foreach ( $reserved as $lock ) {
			$outdata['reserved'][] = $lock['TimetableMedService_id'];
		}

		return $outdata;
	}

	/**
	 * @param $data
	 * Запись пациента на дополнительную бирку при приему пациента врачем службы консультативного приема без записи
	 */

	function acceptPerson($data){
		$params = array(
			'Person_id' => $data['Person_id'],
			'MedService_id' => $data['MedService_id'],
			'EvnDirection_id' => $data['EvnDirection_id'],
			'Timetable_Day' => $data['Day'],
			'pmUser_id' => $data['pmUser_id'],
			'Fact_DT' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['StartTime'])))
		);

		$sql = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = null;

			exec p_TimetableMedService_ins
				@TimetableMedService_id = @Res output,
				@Person_id = :Person_id,
				@MedService_id = :MedService_id,
				@TimetableMedService_factTime = :Fact_DT,
				@TimetableMedService_begTime = :Fact_DT,
				@EvnDirection_id = :EvnDirection_id,
				@TimetableMedService_Day = :Timetable_Day,
				@TimetableMedService_Time = 0,
				@TimetableMedService_IsDop = 1,
				@TimetableExtend_Descr = 'Прием c pfgbcm.',
				@RecClass_id = 3,
				@RecMethodType_id = 1,
				@pmUser_id = :pmUser_id
			select @Res as TimetableMedService_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$res = $this->db->query( $sql,
			$params );
		if ( is_object( $res ) ) {
			$resp = $res->result( 'array' );
			return $resp;
		} else {
			return array(array('Error_Msg' => 'Ошибка БД при создании бирки экстренного посещения пациентом врача!'));
		}
	}


	/**
	 * Приём из очереди без записи
	 */
	function acceptWithoutRecord( $data ) {
		if (!empty($data['EvnDirection_id'])) {
			// если уже записано на бирку то ещё запись выполняться не должна
			$query = "
				select
					TimetableMedService_id
				from
					v_TimetableMedService_lite (nolock)
				where
					EvnDirection_id = :EvnDirection_id
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['TimetableMedService_id'])) {
					return array('Error_Msg' => 'Пациент уже принят');
				}
			} else {
				return array('Error_Msg' => 'Ошибка проверки наличия бирки');
			}

			$query = "
				select
					MedService_id,
					Person_id,
					convert(varchar(10), dbo.tzGetDate(), 104) as date
				from
					v_EvnDirection_all (nolock)
				where
					EvnDirection_id = :EvnDirection_id
			";
			
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['date'])) {
					$data['date'] = $resp[0]['date'];
					$data['MedService_id'] = $resp[0]['MedService_id'];
					$data['Person_id'] = $resp[0]['Person_id'];
				}
			}
		}
		
		if (empty($data['Person_id'])) {
			return array('Error_Msg' => 'Ошибка получения данных по направлению');
		}

		$Timetable_Day = empty( $data['Timetable_Day'] ) ? TimeToDay( strtotime( $data['date'] ) ) : $data['Timetable_Day'];
		
		$params = array(
			'Person_id' => $data['Person_id'],
			'MedService_id' => $data['MedService_id'],
			'EvnDirection_id' => $data['EvnDirection_id'],
			'Timetable_Day' => $Timetable_Day,
			'pmUser_id' => $data['pmUser_id']
		);

		$sql = "
			declare
				@Res bigint,
				@fact_dt datetime,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = null;
			set @fact_dt = dbo.tzGetDate();

			exec p_TimetableMedService_ins
				@TimetableMedService_id = @Res output,
				@Person_id = :Person_id,
				@MedService_id = :MedService_id,
				@TimetableMedService_factTime = @fact_dt,
				@EvnDirection_id = :EvnDirection_id,
				@TimetableMedService_Day = :Timetable_Day,
				@RecClass_id = 3,
				@RecMethodType_id = 1,
				@pmUser_id = :pmUser_id
			select @Res as TimetableMedService_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		// die(getDebugSql($sql, $params));

		$res = $this->db->query( $sql,
			$params );
		if ( is_object( $res ) ) {
			$resp = $res->result( 'array' );
			if ( !empty( $resp[0]['TimetableMedService_id'] ) ) {
				// отправка STOMP-сообщения
				sendFerStompMessage( array(
					'id' => $resp[0]['TimetableMedService_id'],
					'timeTable' => 'TimetableMedService',
					'action' => 'AddTicket',
					'setDate' => date( "c" )
					),
					'Rule' );
			}
			return $resp;
		} else {
			return array(array('Error_Msg' => 'Ошибка БД при создании бирки экстренного посещения пациентом врача!'));
		}
	}
	
	/**
	 * Создание расписания для службы
	 */
	function createTTMSSchedule( $data ) {

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

		if ( true !== ($res = $this->checkTimetableMedServiceTimeNotOccupied($data)) ) {
			return $res;
		}
		if ( true !== ($res = $this->checkTimetableMedServiceTimeNotExists($data)) ) {
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

				exec p_TimetableMedService_fill
					@MedService_id = :MedService_id,
					@TimetableMedService_Day = :TimetableMedService_Day,
					@TimetableMedService_Time = :TimetableMedService_Time,
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
				'MedService_id' => $data['MedService_id'],
				'TimetableMedService_Day' => $day,
				'TimetableMedService_Time' => $data['Duration'],
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
			'timeTable' => 'TimetableMedService',
			'action' => 'AddTicket',
			'setDate' => date("c"),
			'begDate' => date("c", DayMinuteToTime($data['StartDay'], $nStartTime)),
			'endDate' => date("c", DayMinuteToTime($data['EndDay'], $nEndTime)),
			'MedStaffFact_id' => (!empty($data['session']['CurMedStaffFact_id']) ? $data['session']['CurMedStaffFact_id'] : null)
				), 'RulePeriod');

		return array(
			'Error_Msg' => ''
		);
	}
	
	/**
	 * Создание расписания для услуги
	 */
	function createTTMSScheduleUslugaComplex( $data ) {

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

		if ( true !== ($res = $this->checkTimetableMedServiceTimeNotOccupiedUslugaComplex($data)) ) {
			return $res;
		}
		if ( true !== ($res = $this->checkTimetableMedServiceTimeNotExistsUslugaComplex($data)) ) {
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

				exec p_TimetableMedService_fill
					@UslugaComplexMedService_id = :UslugaComplexMedService_id,
					@TimetableMedService_Day = :TimetableMedService_Day,
					@TimetableMedService_Time = :TimetableMedService_Time,
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
				'TimetableMedService_Day' => $day,
				'TimetableMedService_Time' => $data['Duration'],
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
			'timeTable' => 'TimetableMedService',
			'action' => 'AddTicket',
			'setDate' => date("c"),
			'begDate' => date("c", DayMinuteToTime($data['StartDay'], $nStartTime)),
			'endDate' => date("c", DayMinuteToTime($data['EndDay'], $nEndTime)),
			'MedStaffFact_id' => (!empty($data['session']['CurMedStaffFact_id']) ? $data['session']['CurMedStaffFact_id'] : null)
				), 'RulePeriod');

		return array(
			'Error_Msg' => ''
		);
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) и интервале времени занятые бирки для службы
	 */
	function checkTimetableMedServiceTimeNotOccupied( $data ) {

		if ( isset($data['Day']) ) {
			$sql = "
				select
					count(*) as cnt
				from v_TimetableMedService_lite with (nolock)
				where
					MedService_id = :MedService_id
					and Person_id is not null
					and ( 
						( TimetableMedService_begTime >= :StartTime and TimetableMedService_begTime < :EndTime )
						or ( DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) > :StartTime and DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) < :EndTime )
						or ( DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) > :StartTime and TimetableMedService_begTime < :StartTime ) 
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
					from v_TimetableMedService_lite with (nolock)
					where
						MedService_id = :MedService_id
						and Person_id is not null
						and ( 
							( TimetableMedService_begTime >= :StartTime and TimetableMedService_begTime < :EndTime )
							or ( DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) > :StartTime and DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) < :EndTime )
							or ( DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) > :StartTime and TimetableMedService_begTime < :StartTime ) 
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
	function checkTimetableMedServiceTimeNotOccupiedUslugaComplex( $data ) {

		if ( isset($data['Day']) ) {
			$sql = "
				select
					count(*) as cnt
				from v_TimetableMedService_lite with (nolock)
				where
					UslugaComplexMedService_id = :UslugaComplexMedService_id
					and Person_id is not null
					and ( 
						( TimetableMedService_begTime >= :StartTime and TimetableMedService_begTime < :EndTime )
						or ( DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) > :StartTime and DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) < :EndTime )
						or ( DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) > :StartTime and TimetableMedService_begTime < :StartTime ) 
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
					from v_TimetableMedService_lite with (nolock)
					where
						UslugaComplexMedService_id = :UslugaComplexMedService_id
						and Person_id is not null
						and ( 
							( TimetableMedService_begTime >= :StartTime and TimetableMedService_begTime < :EndTime )
							or ( DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) > :StartTime and DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) < :EndTime )
							or ( DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) > :StartTime and TimetableMedService_begTime < :StartTime ) 
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
	function checkTimetableMedServiceTimeNotExists( $data ) {


		if ( isset($data['Day']) ) {
			$sql = "
				select
					count(*) as cnt
				from v_TimetableMedService_lite with (nolock)
				where
					MedService_id = :MedService_id
					and ( 
						( TimetableMedService_begTime >= :StartTime and TimetableMedService_begTime < :EndTime )
						or ( DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) > :StartTime and DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) < :EndTime )
						or ( DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) > :StartTime and TimetableMedService_begTime < :StartTime ) 
					)
					";
			
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
					from v_TimetableMedService_lite with (nolock)
					where
						MedService_id = :MedService_id
						and ( 
							( TimetableMedService_begTime >= :StartTime and TimetableMedService_begTime < :EndTime )
							or ( DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) > :StartTime and DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) < :EndTime )
							or ( DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) > :StartTime and TimetableMedService_begTime < :StartTime ) 
						)
				";

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
	function checkTimetableMedServiceTimeNotExistsUslugaComplex( $data ) {


		if ( isset($data['Day']) ) {
			$sql = "
				select
					count(*) as cnt
				from v_TimetableMedService_lite with (nolock)
				where
					UslugaComplexMedService_id = :UslugaComplexMedService_id
					and ( 
						( TimetableMedService_begTime >= :StartTime and TimetableMedService_begTime < :EndTime )
						or ( DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) > :StartTime and DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) < :EndTime )
						or ( DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) > :StartTime and TimetableMedService_begTime < :StartTime ) 
					)
			";

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
					from v_TimetableMedService_lite with (nolock)
					where
						UslugaComplexMedService_id = :UslugaComplexMedService_id
						and ( 
							( TimetableMedService_begTime >= :StartTime and TimetableMedService_begTime < :EndTime )
							or ( DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) > :StartTime and DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) < :EndTime )
							or ( DATEADD( minute, TimetableMedService_Time, TimetableMedService_begTime  ) > :StartTime and TimetableMedService_begTime < :StartTime ) 
						)
				";

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
	function copyTTMSSchedule( $data ) {

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

		if ( true !== ($res = $this->checkTimetableMedServiceDayNotOccupied($data)) ) {
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
				exec p_TimetableMedService_copy
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
				'CopyTimetableExtend_Descr' => ($data['CopyTTMSComments'] == 1) ? 1 : NULL,
				'pmUser_id' => $data['pmUser_id']
					)
			);

			if ( is_object($res) ) {
				$resp = $res->result('array');
				if ( count($resp) > 0 && empty($resp[0]['Error_Msg']) ) {
					// отправка STOMP-сообщения
					sendFerStompMessage(array(
						'timeTable' => 'TimetableMedService',
						'action' => 'AddTicket',
						'setDate' => date("c"),
						'begDate' => date("c", DayMinuteToTime($nTargetStart, 0)),
						'endDate' => date("c", DayMinuteToTime($nTargetEnd, 0)),
						'MedStaffFact_id' => (!empty($data['session']['CurMedStaffFact_id']) ? $data['session']['CurMedStaffFact_id'] : null)
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
	function copyTTMSScheduleUslugaComplex( $data ) {

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

		if ( true !== ($res = $this->checkTimetableMedServiceDayNotOccupiedUslugaComplex($data)) ) {
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
				exec p_TimetableMedService_copyUslugaComplex
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
				'SourceStartDay' => $SourceStartDay,
				'SourceEndDay' => $SourceEndDay,
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
						'timeTable' => 'TimetableMedService',
						'action' => 'AddTicket',
						'setDate' => date("c"),
						'begDate' => date("c", DayMinuteToTime($nTargetStart, 0)),
						'endDate' => date("c", DayMinuteToTime($nTargetEnd, 0)),
						'MedStaffFact_id' => (!empty($data['session']['CurMedStaffFact_id']) ? $data['session']['CurMedStaffFact_id'] : null)
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
			
			$n++;
		}

		return array(
			'Error_Msg' => ''
		);
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) занятые бирки для службы
	 */
	function checkTimetableMedServiceDayNotOccupied( $data ) {
		if ( isset($data['Day']) ) {
			$sql = "
				SELECT count(*) as cnt
				FROM v_TimetableMedService_lite with (nolock)
				WHERE
					TimetableMedService_Day = :Day
					and MedService_id = :MedService_id
					and Person_id is not null
					and TimetableMedService_begTime is not null
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
				FROM v_TimetableMedService_lite with (nolock)
				WHERE
					TimetableMedService_day between :StartDay and :EndDay
					and MedService_id = :MedService_id
					and Person_id is not null
					and TimetableMedService_begTime is not null
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
	function checkTimetableMedServiceDayNotOccupiedUslugaComplex( $data ) {
		if ( isset($data['Day']) ) {
			$sql = "
				SELECT count(*) as cnt
				FROM v_TimetableMedService_lite with (nolock)
				WHERE
					TimetableMedService_Day = :Day
					and UslugaComplexMedService_id = :UslugaComplexMedService_id
					and Person_id is not null
					and TimetableMedService_begTime is not null
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
				FROM v_TimetableMedService_lite with (nolock)
				WHERE
					TimetableMedService_day between :StartDay and :EndDay
					and UslugaComplexMedService_id = :UslugaComplexMedService_id
					and Person_id is not null
					and TimetableMedService_begTime is not null
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
			exec p_TimetableMedService_clearDay
				@TimetableMedService_Day = :TimetableMedService_Day,
				@MedService_id = :MedService_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output
			select @Res as TimetableMedService_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$res = $this->db->query(
				$sql, array(
			'MedService_id' => $data['MedService_id'],
			'TimetableMedService_Day' => $data['Day'],
			'pmUser_id' => $data['pmUser_id']
				)
		);

		if ( is_object($res) ) {
			$resp = $res->result('array');
			if ( count($resp) > 0 && empty($resp[0]['Error_Msg']) ) {
				// отправка STOMP-сообщения
				sendFerStompMessage(array(
					'timeTable' => 'TimetableMedService',
					'action' => 'DelTicket',
					'setDate' => date("c"),
					'begDate' => date("c", DayMinuteToTime($data['Day'], 0)),
					'endDate' => date("c", DayMinuteToTime($data['Day'], 0)),
					'MedStaffFact_id' => (!empty($data['session']['CurMedStaffFact_id']) ? $data['session']['CurMedStaffFact_id'] : null)
						), 'RulePeriod');
			}
		}

		// Пересчет теперь прямо в хранимке

		return array(
			'Error_Msg' => ''
		);
	}

	/**
	 * Очистка дня для услуги
	 */
	function ClearDayUslugaComplex( $data ) {

		$sql = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			exec p_TimetableMedService_clearDayUslugaComplex
				@TimetableMedService_Day = :TimetableMedService_Day,
				@UslugaComplexMedService_id = :UslugaComplexMedService_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output
			select @Res as TimetableMedService_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$res = $this->db->query(
				//echo getDebugSql(
				$sql, array(
			'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id'],
			'TimetableMedService_Day' => $data['Day'],
			'pmUser_id' => $data['pmUser_id']
				)
		);

		if ( is_object($res) ) {
			$resp = $res->result('array');
			if ( count($resp) > 0 && empty($resp[0]['Error_Msg']) ) {
				// отправка STOMP-сообщения
				sendFerStompMessage(array(
					'timeTable' => 'TimetableMedService',
					'action' => 'DelTicket',
					'setDate' => date("c"),
					'begDate' => date("c", DayMinuteToTime($data['Day'], 0)),
					'endDate' => date("c", DayMinuteToTime($data['Day'], 0)),
					'MedStaffFact_id' => (!empty($data['session']['CurMedStaffFact_id']) ? $data['session']['CurMedStaffFact_id'] : null)
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
	function getTTMSHistory( $data ) {
		$selectPersonData = "rtrim(rtrim(p.Person_Surname) + ' ' + rtrim(p.Person_Firname) + ' ' + isnull(rtrim(p.Person_Secname), '')) as Person_FIO,
					convert(varchar(10), Person_BirthDay, 104) as Person_BirthDay";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = p.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null then rtrim(rtrim(p.Person_Surname) + ' ' + rtrim(p.Person_Firname) + ' ' + isnull(rtrim(p.Person_Secname), '')) else rtrim(peh.PersonEncrypHIV_Encryp) end as Person_FIO,
					case when peh.PersonEncrypHIV_Encryp is null then convert(varchar(10), Person_BirthDay, 104) else null end as Person_BirthDay";
		}
		if ( !isset($data['ShowFullHistory']) ) {
			$sql = "
				select
					convert(varchar(10), TimetableMedServiceHist_insDT, 104) + ' ' + convert(varchar(8), TimetableMedServiceHist_insDT, 108) as TimetableHist_insDT,
					rtrim(PMUser_Name) as PMUser_Name,
					TimetableActionType_Name as TimetableActionType_Name,
					TimetableType_Name,
					{$selectPersonData}
				from TimetableMedServiceHist ttsh with (nolock)
				left join v_pmUser pu with (nolock) on ttsh.TimetableMedServiceHist_userID = pu.pmuser_id
				left join TimetableActionType ttat with (nolock) on ttat.TimetableActionType_id = ttsh.TimetableActionType_id
				left join v_TimetableType ttt with(nolock) on ttt.TimetableType_id = isnull(ttsh.TimetableType_id, 1)
				left join v_Person_ER p with (nolock) on ttsh.Person_id = p.Person_id
				{$joinPersonEncrypHIV}
				where TimetableMedService_id = :TimetableMedService_id";
		} else {
			$sql = "declare
						@MedService_id bigint,
						@TimetableMedService_begTime datetime
					
					select
						@MedService_id = MedService_id, 
						@TimetableMedService_begTime = TimetableMedService_begTime
					from v_TimetableMedService_lite with (nolock)
					where TimetableMedService_id = :TimetableMedService_id
			
					select
						convert(varchar(10), TimetableMedServiceHist_insDT, 104) + ' ' + convert(varchar(8), TimetableMedServiceHist_insDT, 108) as TimetableHist_insDT,
						rtrim(PMUser_Name) as PMUser_Name,
						TimetableActionType_Name as TimetableActionType_Name,
						TimetableType_Name,
						{$selectPersonData}
					from TimetableMedServiceHist ttsh with (nolock)
					left join v_pmUser pu with (nolock) on ttsh.TimetableMedServiceHist_userID = pu.pmuser_id
					left join TimetableActionType ttat with (nolock) on ttat.TimetableActionType_id = ttsh.TimetableActionType_id
					left join v_TimetableType ttt with(nolock) on ttt.TimetableType_id = isnull(ttsh.TimetableType_id, 1)
					left join v_Person_ER p with (nolock) on ttsh.Person_id = p.Person_id
					{$joinPersonEncrypHIV}
					where 
						MedService_id = @MedService_id
						and TimetableMedService_begTime = @TimetableMedService_begTime";
		}

		$res = $this->db->query(
				$sql, array(
			'TimetableMedService_id' => $data['TimetableMedService_id']
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
	function checkTimetableMedServiceOccupied( $data ) {
		$sql = "
			SELECT TimetableMedService_id, Person_id
			FROM v_TimetableMedService_lite with(nolock)
			WHERE
				TimetableMedService_id = :Id 
		";

		$res = $this->db->query(
				$sql, array(
			'Id' => $data['TimetableMedService_id']
				)
		);
		if ( is_object($res) ) {
			$res = $res->result('array');
		}
		if ( !isset($res[0]) || !isset($res[0]['TimetableMedService_id']) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Бирка с таким идентификатором не существует.'
			);
		}
		if ( !isset($res[0]['Person_id']) ) {
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
	function checkTimetableMedServiceFree( $data ) {
		$sql = "
			SELECT
				TimetableMedService_id,
				Person_id
			FROM TimetableMedService
			WHERE
				TimetableMedService_id = :Id
		";

		$res = $this->db->query(
				$sql, array(
			'Id' => $data['TimetableMedService_id']
				)
		);
		if ( is_object($res) ) {
			$res = $res->result('array');
		}
		if ( !isset($res[0]) || $res[0]['TimetableMedService_id'] == null ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Бирка с таким идентификатором не существует.'
			);
		}
		if ( $res[0]['Person_id'] != null ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Выбранная вами бирка уже занята.'
			);
		}
		return true;
	}

	/**
	 * 	Определение действия по типу расписания
	 */
	function defineActionTypeByTimetableType( $TimetableType_SysNick ) {
		$action = '';

		switch ( $TimetableType_SysNick ) {
			case 'free':
				$action = 'ChType_NormalTicket';
				break;
			case 'reserved':
				$action = 'ChType_ReservTicket';
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
	 * Изменение типа бирки у службы
	 */
	function setTTMSType( $data ) {
		$data['object'] = 'TimetableMedService';

		if ( isset($data['TimetableMedServiceGroup']) ) {
			$data['TimetableMedServiceGroup'] = json_decode($data['TimetableMedServiceGroup']);
		}
		if ( isset($data['TimetableMedServiceGroup']) && count($data['TimetableMedServiceGroup']) > 0 ) {
			// Обработка группы бирок в отдельном методе
			return $this->setTTMSTypeGroup($data);
		} else {
			if ( true === ($res = $this->checkTimetableMedServiceOccupied($data)) ) {
				return array(
					'Error_Msg' => 'Бирка занята, изменение типа невозможно.'
				);
			}
		}

		// Получаем службу и день, а также заодно проверяем, что бирка существует
		$res = $this->db->query("
			select
				MedService_id,
				UslugaComplexMedService_id,
				TimetableMedService_Day
			from v_TimetableMedService_lite with (nolock)
			where TimetableMedService_id = :TimetableMedService_id", array(
			'TimetableMedService_id' => $data['TimetableMedService_id']
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
			$MedService_id = $res[0]['MedService_id'];
			$UslugaComplexMedService_id = $res[0]['UslugaComplexMedService_id'];
			$Day = $res[0]['TimetableMedService_Day'];
		}
		$tttype=$this->getFirstRowFromQuery("select TimetableType_Name from v_TimetableType with (nolock) where TimeTableType_id=?",array($data['TimetableType_id']));
		$sql = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@TimetableMedService_id bigint = :TimetableMedService_id,
				@TimetableType_SysNick varchar(50),
				@ErrMsg varchar(4000);
			set @Res = null;
			set @TimetableType_SysNick = (select top 1 TimetableType_SysNick from v_TimetableType (nolock) where TimetableType_id = :TimetableType_id);

			exec p_TimetableMedService_setType
				@TimetableMedService_id = @TimetableMedService_id output,
				@TimetableType_id = :TimetableType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output
			select @Res as TimetableMedService_id, @TimetableType_SysNick as TimetableType_SysNick, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$res = $this->db->query(
				$sql, array(
			'TimetableMedService_id' => $data['TimetableMedService_id'],
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
						'id' => $data['TimetableMedService_id'],
						'timeTable' => 'TimetableMedService',
						'action' => $action,
						'setDate' => date("c")
							), 'Rule');
				}
			}
		}

		// Пересчет теперь прямо в хранимке

		return array(
			'TimetableType_Name'=>$tttype['TimetableType_Name'],
			'Error_Msg' => ''
		);
	}

	/**
	 * Изменение типа бирок у службы для группы бирок
	 */
	function setTTMSTypeGroup( $data ) {

		if ( true !== ($res = $this->checkTimetablesFree($data)) ) {
			return $res;
		}

		// Получаем службу и список дней, на которые мы выделили бирки
		$res = $this->db->query("
			select
				TimetableMedService_id,
				MedService_id,
				UslugaComplexMedService_id,
				TimetableMedService_Day
			from v_TimetableMedService_lite with (nolock)
			where TimetableMedService_id in (" . implode(', ', $data['TimetableMedServiceGroup']) . ")", array(
			'TimetableMedService_id' => $data['TimetableMedService_id']
				)
		);

		if ( is_object($res) ) {
			$res = $res->result('array');
		} else {
			return false;
		}
		// Меняем тип у каждой бирки по отдельности. Не лучший вариант конечно
		foreach ( $res as $row ) {

			$MedService_id = $row['MedService_id'];
			$UslugaComplexMedService_id = $row['UslugaComplexMedService_id'];
			$Day = $row['TimetableMedService_Day'];

			$sql = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@TimetableMedService_id bigint = :TimetableMedService_id,
					@TimetableType_SysNick varchar(50),
					@ErrMsg varchar(4000);
				set @Res = null;
				set @TimetableType_SysNick = (select top 1 TimetableType_SysNick from v_TimetableType (nolock) where TimetableType_id = :TimetableType_id);

				exec p_TimetableMedService_setType
					@TimetableMedService_id = @TimetableMedService_id output,
					@TimetableType_id = :TimetableType_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output
				select @Res as TimetableMedService_id, @TimetableType_SysNick as TimetableType_SysNick, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

			$res = $this->db->query(
					$sql, array(
				'TimetableMedService_id' => $row['TimetableMedService_id'],
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
							'id' => $row['TimetableMedService_id'],
							'timeTable' => 'TimetableMedService',
							'action' => $action,
							'setDate' => date("c")
								), 'Rule');
					}
				}
			}

			// Пересчет теперь прямо в хранимке
		}

		return array(
			'Error_Msg' => ''
		);
	}

	/**
	 * Удаление бирки для службы
	 */
	function Delete( $data ) {
		$data['object'] = 'TimetableMedService';
		if ( isset($data['TimetableMedService_id']) ) {
			$data['TimetableMedServiceGroup'] = array($data['TimetableMedService_id']);
		} else {
			$data['TimetableMedServiceGroup'] = json_decode($data['TimetableMedServiceGroup']);
		}

		if ( true !== ($res = $this->checkTimetablesFree($data)) ) {
			return $res;
		}

		// Получаем врача и список дней, на которые мы выделили бирки
		$res = $this->db->query("
			select
					TimetableMedService_id,
					MedService_id,
					UslugaComplexMedService_id,
					TimetableMedService_Day
			from v_TimetableMedService_lite with (nolock)
			where TimetableMedService_id in (" . implode(', ', $data['TimetableMedServiceGroup']) . ")"
		);

		if ( is_object($res) ) {
			$res = $res->result('array');
		} else {
			return false;
		}
		// Удаляем каждую бирку по отдельности. Не лучший вариант конечно
		foreach ( $res as $row ) {

			$MedService_id = $row['MedService_id'];
			$UslugaComplexMedService_id = $row['UslugaComplexMedService_id'];
			$Day = $row['TimetableMedService_Day'];

			//Удаляем бирку
			$sql = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec p_TimetableMedService_del
					@TimetableMedService_id = :TimetableMedService_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$res = $this->db->query(
					$sql, array(
				'TimetableMedService_id' => $row['TimetableMedService_id'],
				'pmUser_id' => $data['pmUser_id']
					)
			);

			if ( is_object($res) ) {
				$resp = $res->result('array');
				if ( count($resp) > 0 && empty($resp[0]['Error_Msg']) ) {
					// отправка STOMP-сообщения
					sendFerStompMessage(array(
						'id' => $row['TimetableMedService_id'],
						'timeTable' => 'TimetableMedService',
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
	 * Добавление дополнительной бирки для службы
	 */
	function addTTMSDop( $data ) {
		if ( empty($data['Day']) ) {
			$data['Day'] = TimeToDay(time());
		}
		if ( empty($data['StartTime']) ) {
			$data['StartTime'] = date('H:i');
		}
		$sql = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = null;

			exec p_TimetableMedService_ins 
				@TimetableMedService_id = @Res output,
				@MedService_id = :MedService_id,
				@TimetableMedService_Day = :TimetableMedService_Day,
				@TimetableMedService_begTime = :TimetableMedService_begTime,
				@TimetableMedService_Time = :TimetableMedService_Time,
				@TimetableType_id = 1,
				@TimetableMedService_IsDop = 1,
				@TimetableExtend_Descr = :TimetableExtend_Descr,
				@pmUser_id = :pmUser_id
			select @Res as TimetableMedService_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;";
		$params = array(
			'MedService_id' => $data['MedService_id'],
			'TimetableMedService_Day' => $data['Day'],
			'TimetableMedService_begTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['StartTime']))),
			'TimetableMedService_Time' => 0,
			'TimetableExtend_Descr' => !empty($data['TimetableExtend_Descr']) ? $data['TimetableExtend_Descr'] : null,
			'pmUser_id' => $data['pmUser_id']
		);
		$res = $this->db->query($sql, $params);

		if ( is_object($res) ) {
			$resp = $res->result('array');
			if ( count($resp) > 0 && !empty($resp[0]['TimetableMedService_id']) ) {
				// отправка STOMP-сообщения
				sendFerStompMessage(array(
					'id' => $resp[0]['TimetableMedService_id'],
					'timeTable' => 'TimetableMedService',
					'action' => 'AddTicket',
					'setDate' => date("c")
						), 'Rule');

				// Пересчет теперь прямо в хранимке

				return array(
					'TimetableMedService_id' => $resp[0]['TimetableMedService_id'],
					'TimetableMedService_begTime' => $params['TimetableMedService_begTime'],
					'Error_Msg' => ''
				);
			}
		}
		return array(
			'Error_Msg' => 'Дополнительная бирка не создана.'
		);
	}

	/**
	 * Добавление дополнительной бирки для службы
	 */
	function addTTMSDopUslugaComplex( $data ) {
		if ( empty($data['Day']) ) {
			$data['Day'] = TimeToDay(time());
		}
		if ( empty($data['StartTime']) ) {
			$data['StartTime'] = date('H:i');
		}
		$sql = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = null;

			exec p_TimetableMedService_ins 
				@TimetableMedService_id = @Res output,
				@UslugaComplexMedService_id = :UslugaComplexMedService_id,
				@TimetableMedService_Day = :TimetableMedService_Day,
				@TimetableMedService_begTime = :TimetableMedService_begTime,
				@TimetableMedService_Time = :TimetableMedService_Time,
				@TimetableType_id = 1,
				@TimetableMedService_IsDop = 1,
				@pmUser_id = :pmUser_id
			select @Res as TimetableMedService_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;";

		$res = $this->db->query(
				$sql, array(
			'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id'],
			'TimetableMedService_Day' => $data['Day'],
			'TimetableMedService_begTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['StartTime']))),
			'TimetableMedService_Time' => 0,
			'pmUser_id' => $data['pmUser_id']
				)
		);

		if ( is_object($res) ) {
			$resp = $res->result('array');
			if ( count($resp) > 0 && !empty($resp[0]['TimetableMedService_id']) ) {
				// отправка STOMP-сообщения
				sendFerStompMessage(array(
					'id' => $resp[0]['TimetableMedService_id'],
					'timeTable' => 'TimetableMedService',
					'action' => 'AddTicket',
					'setDate' => date("c")
						), 'Rule');
			}
		}

		// Пересчет теперь прямо в хранимке

		return array(
			'Error_Msg' => ''
		);
	}

	/**
	 * Получение комментария на день для службы
	 */
	function getTTMSDayComment( $data ) {
		if ( !isset($data['UslugaComplexMedService_id']) ) {
			$where = 'MedService_id = :MedService_id';
		} else {
			$where = 'UslugaComplexMedService_id = :UslugaComplexMedService_id';
		}

		$sql = "
			select
				mpd.MedServiceDay_Descr,
				mpd.MedServiceDay_id
			from MedServiceDay mpd with (nolock)
			where {$where}
				and Day_id = :Day_id";

		$res = $this->db->query(
				$sql, array(
			'MedService_id' => $data['MedService_id'],
			'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id'],
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
	 * Сохранение комментария на день для службы
	 */
	function saveTTMSDayComment( $data ) {

		$sql = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			exec p_MedServiceDay_setDescr
				@MedServiceDay_id = @Res,
				@Day_id = :Day_id,
				@MedService_id = :MedService_id,
				@UslugaComplexMedService_id = :UslugaComplexMedService_id,
				@MedServiceDay_Descr = :MedServiceDay_Descr,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output";

		$res = $this->db->query(
				$sql, array(
			'MedServiceDay_Descr' => $data['MedServiceDay_Descr'],
			'MedService_id' => $data['MedService_id'],
			'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id'],
			'Day_id' => $data['Day'],
			'pmUser_id' => $data['pmUser_id']
				)
		);

		return array(
			'Error_Msg' => ''
		);
	}

	/**
	 * Проверка прав на очистку бирки
	 */
	function checkHasRightsToClearRecord( $data ) {
	
		$obj=$data['object'].'_id';

		$fields = "";
		if ($data['object'] == 'TimetableMedService') {
			$fields .= ',MedService_id';
		}
		
		$sql = "
			SELECT
				t.".$obj.",
				t.pmUser_updId,
				pu.Lpu_id,
				l.Org_id
				{$fields}
			FROM ".$data['object']." t (nolock)
			left join v_pmUser pu (nolock) on t.pmUser_updId = pu.pmUser_id
			left join v_Lpu l (nolock) on l.Lpu_id = pu.Lpu_id
			WHERE
				t.".$obj." = :obj
		";

		$res = $this->db->query(
				$sql, array(
			'obj' => $data[$obj]
				)
		);
		if ( is_object($res) ) {
			$res = $res->result('array');
		}
		if ( $res[0][$obj] == null ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Бирка с таким идентификатором не существует.'
			);
		}
		if (
			!(
				($res[0]['pmUser_updId'] == $data['session']['pmuser_id']) ||
				isCZAdmin() ||
				isLpuRegAdmin($res[0]['Org_id']) ||
				isInetUser($res[0]['pmUser_updId']) ||
				(!empty($res[0]['MedService_id']) && !empty($data['session']['CurMedService_id']) && $res[0]['MedService_id'] == $data['session']['CurMedService_id']) // служба бирки равна текущей службе врача
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
	function getTimetableMedServiceOneDay( $data ) {
		$outdata = array();

		$StartDay = isset($data['StartDay']) ? strtotime($data['StartDay']) : time();

		$outdata['StartDay'] = $StartDay;

		$param['StartDay'] = TimeToDay($StartDay);
		$param['MedService_id'] = $data['MedService_id'];

		$param['EndDate'] = date( "Y-m-d", $StartDay);
		if (empty($data['dntUseFilterMaxDayRecord']) || $data['dntUseFilterMaxDayRecord'] != true) {
			$msflpu = $this->getFirstRowFromQuery("select Lpu_id from v_MedService with (nolock) where MedService_id = ?", array($data['MedService_id']));
			$maxDays = GetMedServiceDayCount($msflpu['Lpu_id'], $data['MedService_id']);

			if (date("H:i") >= getShowNewDayTime() && $maxDays) $maxDays++;
			$param['EndDate'] = !empty($maxDays) ? date("Y-m-d", strtotime("+" . $maxDays . " days", time())) : $param['EndDate'];
		}

		$nTime = $StartDay;

		$outdata['day_comment'] = null;
		$outdata['data'] = array();


		$sql = "
			select
				msd.Day_id,
				rtrim(msd.MedServiceDay_Descr) as MedServiceDay_Descr,
				rtrim(u.pmUser_Name) as pmUser_Name,
				msd.MedServiceDay_updDT
			from MedServiceDay msd with (nolock)
			left join v_pmUser u with(nolock) on u.pmUser_id = msd.pmUser_updID
			where MedService_id = :MedService_id
				and Day_id = :StartDay";

		$res = $this->db->query($sql, $param);

		$daydescrdata = $res->result('array');

		if ( isset($daydescrdata[0]['MedServiceDay_Descr']) ) {
			$outdata['day_comment'] = array(
				'MedServiceDay_Descr' => $daydescrdata[0]['MedServiceDay_Descr'],
				'pmUser_Name' => $daydescrdata[0]['pmUser_Name'],
				'MedServiceDay_updDT' => $daydescrdata[0]['MedServiceDay_updDT']
			);
		}
		$ext = $this->getTimeTableExtendData($data);
		
		$selectPersonData = "
				p.Person_BirthDay,
				p.Person_Phone,
				p.PersonInfo_InternetPhone as Person_InetPhone,
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
				case when peh.PersonEncrypHIV_Encryp is null then p.PersonInfo_InternetPhone else null end as Person_InetPhone,
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
		$sql = "
			select
					t.pmUser_updID,
					t.TimetableMedService_updDT,
					t.TimetableMedService_id,
					t.Person_id,
					t.TimetableMedService_Day,
					TimetableMedService_begTime,
					t.TimetableType_id,
					t.TimetableMedService_IsDop,
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
					d.EvnDirection_TalonCode as Direction_TalonCode,
					convert(varchar(10),d.EvnDirection_setDate,104) as Direction_Date,
					d.EvnDirection_id as EvnDirection_id,
					t.EvnDirection_id as ttEvnDirection_id,
					qp.pmUser_Name as QpmUser_Name,
					epd.EvnPrescr_id,
					q.EvnQueue_insDT as EvnQueue_insDT,
					dg.Diag_Code,
					u.Lpu_id as pmUser_Lpu_id,
					ms.Lpu_id,
					{$ext['fields']}
				from {$ext['source']} t with (nolock)
				left outer join v_MedService ms with (nolock) on ms.MedService_id = t.MedService_id
				left outer join v_Person_ER p with (nolock) on t.Person_id = p.Person_id
				left outer join Address a with (nolock) on p.UAddress_id = a.Address_id
				left outer join Address a1 with (nolock) on p.PAddress_id = a1.Address_id
				left outer join KLStreet pas with (nolock) on a.KLStreet_id = pas.KLStreet_id
				left outer join KLStreet pas1 with (nolock) on a1.KLStreet_id = pas1.KLStreet_id
				left outer join v_Job_ER j with (nolock) on p.Job_id=j.Job_id
				left outer join v_pmUser u with (nolock) on t.PMUser_UpdID = u.PMUser_id
				{$ext['join']}
				left outer join v_Lpu lpu with (nolock) on lpu.Lpu_id = p.Lpu_id
				outer apply  (
					Select top 1 d.*, Evn.Evn_setDT as EvnDirection_setDate, Evn.Lpu_id from EvnDirection d with (nolock)  
					inner join Evn (nolock) on Evn.Evn_id = d.EvnDirection_id and Evn.Evn_deleted = 1 
					where
					t.TimetableMedService_id = d.TimetableMedService_id
					and d.DirFailType_id is null
					--and d.Person_id = t.Person_id
				) d
				outer apply (
					select top 1
						epd.EvnPrescr_id
					from
						v_EvnPrescrDirection epd (nolock)
					where
						epd.EvnDirection_id = d.EvnDirection_id
				) epd
				left outer join v_Lpu lpud with (nolock) ON lpud.Lpu_id = d.Lpu_id
				outer apply  (
					Select top 1 q.*, Evn.pmUser_updId, Evn_insDT as EvnQueue_insDT 
					from EvnQueue q with (nolock) 
					inner join Evn (nolock) on Evn.Evn_id = q.EvnQueue_id and Evn.Evn_deleted = 1 
					where 
						t.TimetableMedService_id = q.TimetableMedService_id
						--and t.Person_id = q.Person_id
				) q
				left join v_pmUser qp with (nolock) on q.pmUser_updId=qp.pmUser_id
				left join Diag dg with (nolock) on dg.Diag_id=d.Diag_id
				{$joinPersonEncrypHIV}
				where t.TimetableMedService_Day = :StartDay
					and cast(t.TimetableMedService_begTime as date) <= :EndDate
					and t.MedService_id = :MedService_id
					and TimetableMedService_begTime is not null
				order by t.TimetableMedService_begTime";

		$res = $this->db->query($sql, $param);
		$ttsdata = $res->result('array');

		// Получаем данные по коду брони в направлениях для ЛИС
		// если служба связана с ЭО и имеет тип ЛАБОРАТОРИИ или ПЗ
		$getLisElectronicQueueData = false;
		if ($this->usePostgreLis) {

			$this->load->swapi('lis');
			$ms_data = $this->getFirstRowFromQuery("
				select top 1
					mst.MedServiceType_SysNick 
				from v_MedService ms (nolock)
				inner join v_ElectronicQueueInfo eqi (nolock) on eqi.MedService_id = ms.MedService_id
				left join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
				where (1=1) 
					and ms.MedService_id = :MedService_id					
			", array('MedService_id' => $data['MedService_id']));

			if (!empty($ms_data) && in_array($ms_data['MedServiceType_SysNick'],array('lab','pzm'))) {
				$getLisElectronicQueueData = true;
			}
		}

		if ($getLisElectronicQueueData) {

			$ed_list = array();
			foreach ($ttsdata as $tts) {
				$outdata['data'][$tts['TimetableMedService_id']] = $tts;
				if (!empty($tts['ttEvnDirection_id'])) {
					$ed_list[] = $tts['ttEvnDirection_id'];
				}
			}

			$tc_params = array('list' => implode(',', $ed_list));
			$talon_code_data = $this->lis->GET('EvnDirection/getTalonCodeByEvnDirectionList', $tc_params);
			if (
				empty($talon_code_data['Error_Msg'])
				&& !empty($talon_code_data['data'])
				&& is_array($talon_code_data['data'])
			) {
				foreach ($talon_code_data['data'] as $lisItem) {
					if (isset($outdata['data'][$lisItem['TimetableMedService_id']])) {
						$outdata['data'][$lisItem['TimetableMedService_id']]['Direction_TalonCode'] = $lisItem['EvnDirection_TalonCode'];
					}
				}
				$outdata['data'] = array_values($outdata['data']);
			}

		} else {
			foreach ($ttsdata as $tts) {
				$outdata['data'][] = $tts;
			}
		}

		$sql = "
			select TimetableMedService_id from TimetableLock with(nolock)";

		$res = $this->db->query($sql);

		$outdata['reserved'] = $res->result('array');

		return $outdata;
	}

	/**
	 * Получение расписания на один день на услугу
	 */
	function getTimetableUslugaComplexOneDay( $data ) {
		$outdata = array();

		$StartDay = isset($data['StartDay']) ? strtotime($data['StartDay']) : time();

		$outdata['StartDay'] = $StartDay;

		$param['StartDay'] = TimeToDay($StartDay);
		$param['UslugaComplexMedService_id'] = $data['UslugaComplexMedService_id'];

		$nTime = $StartDay;

		$outdata['day_comment'] = null;
		$outdata['data'] = array();


		$sql = "
			select
				msd.Day_id,
				rtrim(msd.MedServiceDay_Descr) as MedServiceDay_Descr,
				rtrim(u.pmUser_Name) as pmUser_Name,
				msd.MedServiceDay_updDT
			from MedServiceDay msd with (nolock)
			left join v_pmUser u with(nolock) on u.pmUser_id = msd.pmUser_updID
			where UslugaComplexMedService_id = :UslugaComplexMedService_id
				and Day_id = :StartDay";

		$res = $this->db->query($sql, $param);

		$daydescrdata = $res->result('array');

		if ( isset($daydescrdata[0]['MedServiceDay_Descr']) ) {
			$outdata['day_comment'] = array(
				'MedServiceDay_Descr' => $daydescrdata[0]['MedServiceDay_Descr'],
				'pmUser_Name' => $daydescrdata[0]['pmUser_Name'],
				'MedServiceDay_updDT' => $daydescrdata[0]['MedServiceDay_updDT']
			);
		}
		
		$ext = $this->getTimeTableExtendData($data);
		
		$selectPersonData = "
				p.Person_BirthDay,
				p.Person_Phone,
				p.PersonInfo_InternetPhone as Person_InetPhone,
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
				case when peh.PersonEncrypHIV_Encryp is null then p.PersonInfo_InternetPhone else null end as Person_InetPhone,
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
		$sql = "
			select
					t.pmUser_updID,
					t.TimetableMedService_updDT,
					t.TimetableMedService_id,
					t.Person_id,
					t.TimetableMedService_Day,
					TimetableMedService_begTime,
					t.TimetableType_id,
					t.TimetableMedService_IsDop,
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
					d.EvnDirection_TalonCode as Direction_TalonCode,
					convert(varchar(10),d.EvnDirection_setDate,104) as Direction_Date,
					d.EvnDirection_id as EvnDirection_id,
					qp.pmUser_Name as QpmUser_Name,
					epd.EvnPrescr_id,
					q.EvnQueue_insDT as EvnQueue_insDT,
					dg.Diag_Code,
					u.Lpu_id as pmUser_Lpu_id,
					ms.Lpu_id,
					{$ext['fields']}
				from {$ext['source']} t with (nolock)
				left outer join v_MedService ms with (nolock) on ms.MedService_id = t.MedService_id
				left outer join v_Person_ER p with (nolock) on t.Person_id = p.Person_id
				left outer join Address a with (nolock) on p.UAddress_id = a.Address_id
				left outer join Address a1 with (nolock) on p.PAddress_id = a1.Address_id
				left outer join KLStreet pas with (nolock) on a.KLStreet_id = pas.KLStreet_id
				left outer join KLStreet pas1 with (nolock) on a1.KLStreet_id = pas1.KLStreet_id
				left outer join v_Job_ER j with (nolock) on p.Job_id=j.Job_id
				left outer join v_pmUser u with (nolock) on t.PMUser_UpdID = u.PMUser_id
				{$ext['join']}
				left outer join v_Lpu lpu with (nolock) on lpu.Lpu_id = p.Lpu_id
				outer apply  (
					Select top 1 d.*, Evn.Evn_setDT as EvnDirection_setDate, Evn.Lpu_id from EvnDirection d with (nolock)  
					inner join Evn (nolock) on Evn.Evn_id = d.EvnDirection_id and Evn.Evn_deleted = 1 
					where
					t.TimetableMedService_id = d.TimetableMedService_id
					and d.DirFailType_id is null
					--and d.Person_id = t.Person_id
				) d
				outer apply (
					select top 1
						epd.EvnPrescr_id
					from
						v_EvnPrescrDirection epd (nolock)
					where
						epd.EvnDirection_id = d.EvnDirection_id
				) epd
				left outer join v_Lpu lpud with (nolock) ON lpud.Lpu_id = d.Lpu_id
				outer apply  (
					Select top 1 q.*, Evn.pmUser_updId, Evn_insDT as EvnQueue_insDT 
					from EvnQueue q with (nolock) 
					inner join Evn (nolock) on Evn.Evn_id = q.EvnQueue_id and Evn.Evn_deleted = 1 
					where 
						t.TimetableMedService_id = q.TimetableMedService_id
						--and t.Person_id = q.Person_id
				) q
				left join v_pmUser qp with (nolock) on q.pmUser_updId=qp.pmUser_id
				left join Diag dg with (nolock) on dg.Diag_id=d.Diag_id
				{$joinPersonEncrypHIV}
				where t.TimetableMedService_Day = :StartDay
					and t.UslugaComplexMedService_id = :UslugaComplexMedService_id
					and TimetableMedService_begTime is not null
				order by t.TimetableMedService_begTime";

		$res = $this->db->query($sql, $param);


		$ttsdata = $res->result('array');


		foreach ( $ttsdata as $tts ) {
			$outdata['data'][] = $tts;
		}

		$sql = "
			select TimetableMedService_id from TimetableLock with(nolock)";

		$res = $this->db->query($sql);

		$outdata['reserved'] = $res->result('array');

		return $outdata;
	}

	/**
	 * Редактирование переданного набора бирок
	 */
	function editTTMSSet( $data ) {

		$TTMSSet = json_decode($data['selectedTTMS']);

		if ( $this->checkTTMSOccupied($TTMSSet) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Одна из выбранных бирок занята. Операция невозможна.'
			);
		}

		// Пустая строка передается как NULL, надо как пустую строку передавать
		if ( $data['ChangeTTMSDescr'] ) {
			$data['TimetableExtend_Descr'] = isset($data['TimetableExtend_Descr']) ? $data['TimetableExtend_Descr'] : '';
		} else {
			$data['TimetableExtend_Descr'] = NULL;
		}

		if ( $data['ChangeTTMSType'] ) {
			$data['TimetableType_id'] = isset($data['TimetableType_id']) ? $data['TimetableType_id'] : 1;
		} else {
			$data['TimetableType_id'] = NULL;
		}

		foreach ( $TTMSSet as $TTMS ) {
			$query = "
				declare
					@ErrCode int,
					@TimetableType_SysNick varchar(50),
					@ErrMessage varchar(4000);
				set @TimetableType_SysNick = (select top 1 TimetableType_SysNick from v_TimetableType (nolock) where TimetableType_id = :TimetableType_id);
				exec p_TimetableMedService_edit
					@TimetableMedService_id = :TimetableMedService_id,
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
				'TimetableMedService_id' => $TTMS,
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
							'id' => $TTMS,
							'timeTable' => 'TimetableMedService',
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
	function checkTTMSOccupied( $TTMSSet ) {
		if ( count($TTMSSet) == 0 ) {
			return false;
		}
		$sql = "
			SELECT count(*) as cnt
			FROM v_TimetableMedService_lite with (nolock)
			WHERE
				TimetableMedService_id in (" . implode(',', $TTMSSet) . ")
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
	 * Получение идентификатора направления выписанного на бирку
	 */
	function load($data) {
		$params = array();
		$filter = "";

		if (!empty($data['TimetableMedService_id'])) {
			$params['TimetableMedService_id'] = $data['TimetableMedService_id'];
			$filter = "TTMS.TimetableMedService_id = :TimetableMedService_id";
		} else if (!empty($data['EvnDirection_id'])) {
			$params['EvnDirection_id'] = $data['EvnDirection_id'];
			$filter = "TTMS.EvnDirection_id = :EvnDirection_id";
		}

		$query = "
			select top 1 TTMS.*
			from v_TimetableMedService TTMS with(nolock)
			where {$filter}
		";
		$resp = $this->queryResult($query, $data);
		if (!is_array($resp)) {
			return false;
		}

		if (isset($resp[0])) {
			unset($resp[0]['TimetableMedService_Rowversion']);
		}

		array_walk_recursive($resp, function(&$value, $key) {
			if ($value instanceof DateTime) {
				$value = $value->format('Y-m-d H:i:s');
			}
		});

		return $resp;
	}

	/**
	 * Возвращает список бирок на 2 недели по идентификаторам различных типов служб
	 */
	function loadAllUslugaTTList($data)
	{
		$MSList = array();
		$MStypes = array('arrRes','arrMS','arrUsl');
		$result = array();
		foreach($MStypes as $type){
			if(!empty($data[$type])){
				$MSList[$type] = json_decode($data[$type]/*, true*/);
			}
		}
		if(empty($MSList)){
			return array('success' => false, 'Error_Msg' => 'Необходим список идентификаторов служб, ресурсов или услуг');
		}
		// Тянем бирки для каждого типа места оказания
		if (!empty($MSList['arrRes']))
			$ResourceTTList = $this->loadResourceTTList($data, $MSList['arrRes']);
		if (!empty($MSList['arrMS']))
			$MedServiceTTList = $this->loadMedServiceTTList($data, $MSList['arrMS']);
		if (!empty($MSList['arrUsl']))
			$UslugaComplexTTList = $this->loadUslugaComplexTTList($data, $MSList['arrUsl']);

		// Формируем массив расписания для ресурсов по идентификаторам Resource_id
		if (!empty($ResourceTTList)){
			foreach ($ResourceTTList as $resTT) {
				$result['arrRes'][$resTT['Resource_id']][$resTT['dataIndex']] = $resTT;
			}
		}
		// Формируем массив расписания для служб по идентификаторам MedService_id
		if (!empty($MedServiceTTList)){
			foreach ($MedServiceTTList as $msTT) {
				$result['arrMS'][$msTT['MedService_id']][$msTT['dataIndex']] = $msTT;
			}
		}
		// Формируем массив расписания для услуг по идентификаторам UslugaComplexMedService_id
		if (!empty($UslugaComplexTTList)){
			foreach ($UslugaComplexTTList as $uslTT) {
				$result['arrUsl'][$uslTT['UslugaComplexMedService_id']][$uslTT['dataIndex']] = $uslTT;
			}
		}

		return array('success' => true, 'data' => $result);
	}
	/**
	 * Возвращает список бирок для ресурсов
	 */
	function loadResourceTTList($data,$MSList) {
		if(empty($MSList) )
			return false;
		$param = array();

		$StartDay = isset($data['StartDay']) ? strtotime($data['StartDay']) : time();
		$param['StartDay'] = TimeToDay($StartDay);
		$param['EndDay'] = TimeToDay(strtotime("+14 days", $StartDay));
		$param['nulltime'] = '00:00:00';

		$query = "
			-- Для ресурса
			BEGIN
			
				SET NOCOUNT ON;
				IF OBJECT_ID(N'tempdb..#AllTimetable', N'U') IS NOT NULL
					DROP TABLE #AllTimetable;
			
				DECLARE @cur_dt DATETIME = GETDATE();
				DECLARE @upper_dt DATETIME = DATEADD(DAY, 14, @cur_dt);
				DECLARE @cur_date DATE = CAST(@cur_dt AS DATE);
			
				SELECT t.TimetableResource_id,
					   t.TimetableResource_Day,
					   CONVERT(VARCHAR(5), t.TimetableResource_begTime, 108) AS formatTime,
					   convert(varchar(8), t.TimetableResource_begTime, 112) as dataIndex,
					   convert(varchar(10), t.TimetableResource_begTime, 104) + ' ' + convert(varchar(5), t.TimetableResource_begTime, 108) as TimetableResource_begTime,
					   t.TimetableType_id,
					   t.Resource_id
				INTO #AllTimetable
				FROM v_TimetableResource_lite t WITH (NOLOCK)
				WHERE t.TimetableResource_Day >= :StartDay
					  AND t.TimetableResource_Day < :EndDay
					  AND t.Resource_id IN (" . implode(',', $MSList) . ")
					  AND t.Person_id IS NULL
					  /* AND t.TimetableResource_begTime BETWEEN '2019-05-13' AND '2019-05-27'*/
					  AND
					  (
						  CAST(t.TimetableResource_begTime AS DATE) <= @upper_dt
						  AND CAST(t.TimetableResource_begTime AS DATE) >= @cur_date
					  )
				ORDER BY t.TimetableResource_begTime;
				SET NOCOUNT OFF;
			
				WITH allDay AS (SELECT DISTINCT TimetableResource_Day, Resource_id FROM #AllTimetable )
				SELECT
					alltt.*,
					ann.*
				FROM
				 allDay allt
					OUTER APPLY
				(
					SELECT TOP 1
						   *
					FROM #AllTimetable tt
					WHERE 
						tt.Resource_id = allt.Resource_id
						AND allt.TimetableResource_Day = tt.TimetableResource_Day
					ORDER BY tt.TimetableResource_begTime
				) alltt
				OUTER APPLY
				(
					SELECT TOP 1
						rtrim(A.Annotation_Comment) as annotate
					from v_Day D with (nolock)
					left join v_Annotation A with (nolock) on
						cast(A.Annotation_begDate as date) <= cast(D.day_date as date) AND
						(cast(A.Annotation_endDate as date) >= cast(D.day_date as date) OR A.Annotation_endDate is null) AND
						(A.Annotation_begTime is null or A.Annotation_begTime = :nulltime) AND
						(A.Annotation_endTime is null or A.Annotation_endTime = :nulltime)
					left join v_Resource r with (nolock) on r.Resource_id = A.Resource_id
					left join v_MedService ms with (nolock) on ms.MedService_id = r.MedService_id
					where A.Resource_id = allt.Resource_id
						and D.Day_id = allt.TimetableResource_Day
				) ann
				ORDER BY Resource_id, TimetableResource_Day
			END;
		";

		$result = $this->db->query($query, $param);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Возвращает список бирок для служб
	 */
	function loadMedServiceTTList($data,$MSList) {
		if(empty($MSList) )
			return false;
		$param = array();

		$StartDay = isset($data['StartDay']) ? strtotime($data['StartDay']) : time();
		$param['StartDay'] = TimeToDay($StartDay);
		$param['EndDay'] = TimeToDay(strtotime("+14 days", $StartDay));

		$query = "
		-- Для пункта забора
		BEGIN
		
			SET NOCOUNT ON;
			IF OBJECT_ID(N'tempdb..#AllTimetable', N'U') IS NOT NULL
				DROP TABLE #AllTimetable;
		
			DECLARE @cur_dt DATETIME = GETDATE();
			DECLARE @upper_dt DATETIME = DATEADD(DAY, 14, @cur_dt);
			DECLARE @cur_date DATE = CAST(@cur_dt AS DATE);
		
			SELECT t.TimetableMedService_id,
				   t.TimetableMedService_Day,
				   CONVERT(VARCHAR(5), t.TimetableMedService_begTime, 108) AS formatTime,
				   convert(varchar(8), t.TimetableMedService_begTime, 112) as dataIndex,
				   convert(varchar(10), t.TimetableMedService_begTime, 104) + ' ' + convert(varchar(5), t.TimetableMedService_begTime, 108) as TimetableMedService_begTime,
				   t.TimetableType_id,
				   t.TimetableMedService_IsDop,
				   t.MedService_id
			INTO #AllTimetable
			FROM v_TimetableMedService_lite t WITH (NOLOCK)
			WHERE t.TimetableMedService_Day >= :StartDay
					  AND t.TimetableMedService_Day < :EndDay
				  AND t.MedService_id IN (" . implode(',', $MSList) . ")
				  AND t.Person_id IS NULL
				  AND
				  (
					  CAST(t.TimetableMedService_begTime AS DATE) <= @upper_dt
					  AND CAST(t.TimetableMedService_begTime AS DATE) >= @cur_date
				  )
				  /* AND t.TimetableResource_begTime
					BETWEEN '2019-05-13' AND '2019-05-27'*/
				 
			ORDER BY t.TimetableMedService_begTime;
			SET NOCOUNT OFF;
		
			SELECT 
				allTT.*,
				ann.*
			FROM (SELECT DISTINCT TimetableMedService_Day, MedService_id FROM #AllTimetable) alld
			OUTER APPLY
			(
				SELECT TOP 1
					   *
				FROM #AllTimetable temp
				WHERE temp.TimetableMedService_Day = alld.TimetableMedService_Day
				AND  temp.MedService_id = alld.MedService_id
				ORDER BY temp.TimetableMedService_begTime
			) allTT
			OUTER APPLY
			(
				SELECT TOP 1
					RTRIM(msd.MedServiceDay_Descr) as annotate
				from MedServiceDay msd with (nolock)
				where MedService_id = alld.MedService_id
				and Day_id = alld.TimetableMedService_Day
			) ann
			ORDER BY allTT.MedService_id, TimetableMedService_Day;
		END;
		";

		$result = $this->db->query($query, $param);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Возвращает список бирок для ресурса за день
	 */
	function loadResourceTTListByDay($data) {
		$param = array();

		$StartDay = isset($data['StartDay']) ? strtotime($data['StartDay']) : time();
		$param['StartDay'] = TimeToDay($StartDay);
		$param['Resource_id'] = $data['Resource_id'];

		$query = "
			select
				t.TimetableResource_id,
				t.Person_id,
				case when t.Person_id is null then 'empty' else 'full'
				end as class,
				t.TimetableResource_Day,
				convert(varchar(19), t.TimetableResource_begTime, 120) as TimetableResource_begTime,
				convert(varchar(5), t.TimetableResource_begTime, 108) as formatTime,
				t.TimetableType_id,
				t.TimetableResource_IsDop  as IsDop
			from v_TimetableResource_lite t with (nolock)
				
			where t.TimetableResource_Day = :StartDay
				and t.Resource_id = :Resource_id
				
			order by t.TimetableResource_begTime
		";

		$result = $this->db->query($query, $param);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Возвращает список бирок за день
	 */
	function loadMedServiceTTListByDay($data) {
		$param = array();

		$StartDay = isset($data['StartDay']) ? strtotime($data['StartDay']) : time();
		$param['StartDay'] = TimeToDay($StartDay);
		$param['MedService_id'] = !empty($data['pzm_MedService_id']) ? $data['pzm_MedService_id'] : $data['MedService_id'];
		$UslugaFilter = ' and t.MedService_id = :MedService_id';
		if(!empty($data['UslugaComplexMedService_id']) || !empty($data['pzm_UslugaComplexMedService_id'])){
			$UslugaFilter = ' and t.UslugaComplexMedService_id = :UslugaComplexMedService_id';
			$param['UslugaComplexMedService_id'] = !empty($data['pzm_UslugaComplexMedService_id']) ? $data['pzm_UslugaComplexMedService_id'] : $data['UslugaComplexMedService_id'];
		}

		$query = "
			declare @cur_dt datetime = GETDATE();
			select 
				t.TimetableMedService_id,
				t.Person_id,
				t.TimetableMedService_Day,
				convert(varchar(19), t.TimetableMedService_begTime, 120) as TimetableMedService_begTime,
				convert(varchar(5), t.TimetableMedService_begTime, 108) as formatTime,
				t.TimetableType_id,
				t.TimetableMedService_IsDop as IsDop
				
			from v_TimetableMedService_lite t with (nolock)
			
			where t.TimetableMedService_Day = :StartDay
				{$UslugaFilter}
				and @cur_dt < t.TimetableMedService_begTime
				
			order by t.TimetableMedService_begTime
		";

		$result = $this->db->query($query, $param);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Возвращает примечание врача для службы за день
	 */
	function loadMedServiceAnnotateByDay($data) {

		$param = array();

		$StartDay = isset($data['StartDay']) ? strtotime($data['StartDay']) : time();
		$param['StartDay'] = TimeToDay($StartDay);
		$param['MedService_id'] = $data['MedService_id'];
		$UslugaFilter = ' and msd.MedService_id = :MedService_id';
		if(!empty($data['UslugaComplexMedService_id'])){
			$UslugaFilter = ' and msd.UslugaComplexMedService_id = :UslugaComplexMedService_id';
			$param['UslugaComplexMedService_id'] = $data['UslugaComplexMedService_id'];
		}

		$query = "
			select
				'' as Error_Msg,
				rtrim(msd.MedServiceDay_Descr) as annotate
			from MedServiceDay msd with (nolock)
			where Day_id = :StartDay 
				{$UslugaFilter}
		";


		$res = $this->db->query($query, $param);
		if ( is_object($res) ) {
			$resp = $res->result('array');
			if (!empty($resp)) {
				return $resp[0];
			} else {
				return array('Error_Msg' => '');
			}
		}
		return array('success'=>false, 'Error_Msg' => 'Ошибка загрузки примечания');

	}

	/**
	 * Возвращает примечания врача для ресурса за день
	 */
	function loadResourceAnnotateByDay($data)
	{

		$param = array();

		$StartDay = isset($data['StartDay']) ? strtotime($data['StartDay']) : time();
		$param['StartDay'] = TimeToDay($StartDay);
		$param['Resource_id'] = $data['Resource_id'];
		$param['Lpu_id'] = $data['Lpu_id'];
		$param['nulltime'] = '00:00:00';

		$query = "
			select
				'' as Error_Msg,
				rtrim(A.Annotation_Comment) as annotate
			from v_Day D with (nolock)
			left join v_Annotation A with (nolock) on
				cast(A.Annotation_begDate as date) <= cast(D.day_date as date) AND
				(cast(A.Annotation_endDate as date) >= cast(D.day_date as date) OR A.Annotation_endDate is null) AND
				(A.Annotation_begTime is null or A.Annotation_begTime = :nulltime) AND
				(A.Annotation_endTime is null or A.Annotation_endTime = :nulltime)
			left join v_Resource r with (nolock) on r.Resource_id = A.Resource_id
			left join v_MedService ms with (nolock) on ms.MedService_id = r.MedService_id
			where A.Resource_id = :Resource_id
				and D.Day_id = :StartDay
				and (A.AnnotationVison_id != 3 or ms.Lpu_id = :Lpu_id)
		";

		$res = $this->db->query($query, $param);
		if ( is_object($res) ) {
			$resp = $res->result('array');
			if (!empty($resp)) {
				return $resp[0];
			} else {
				return array('Error_Msg' => '');
			}
		}
		return array('success'=>false, 'Error_Msg' => 'Ошибка загрузки примечания');
	}

	/**
	 * Возвращает список бирок на услуге
	 */
	function loadUslugaComplexTTList($data,$UslList) {
		if(empty($UslList) )
			return false;
		$param = array();

		$StartDay = isset($data['StartDay']) ? strtotime($data['StartDay']) : time();
		$param['StartDay'] = TimeToDay($StartDay);
		$param['EndDay'] = TimeToDay(strtotime("+14 days", $StartDay));

		$query = "
			-- расписание на услугах
			BEGIN
		
			SET NOCOUNT ON;
			IF OBJECT_ID(N'tempdb..#AllTimetable', N'U') IS NOT NULL
				DROP TABLE #AllTimetable;
				
			DECLARE @cur_dt DATETIME = GETDATE();
			DECLARE @upper_dt DATETIME = DATEADD(DAY, 14, @cur_dt);
			DECLARE @cur_date DATE = CAST(@cur_dt AS DATE);
		
			SELECT
				t.TimetableMedService_updDT,
				t.TimetableMedService_id,
				t.Person_id,
				t.TimetableMedService_Day,
				CONVERT(VARCHAR(5), t.TimetableMedService_begTime, 108) AS formatTime,
				convert(varchar(8), t.TimetableMedService_begTime, 112) as dataIndex,
				convert(varchar(10), t.TimetableMedService_begTime, 104) + ' ' + convert(varchar(5), t.TimetableMedService_begTime, 108) as TimetableMedService_begTime,
				t.TimetableType_id,
				t.TimetableMedService_IsDop,
				t.UslugaComplexMedService_id
				INTO #AllTimetable
			from v_TimetableMedService_lite t with (nolock)
			
			WHERE t.TimetableMedService_Day >= :StartDay
				  AND t.TimetableMedService_Day < :EndDay
				  AND t.Person_id IS NULL
				and t.UslugaComplexMedService_id IN (" . implode(',', $UslList) . ")
				 AND
				  (
					  CAST(t.TimetableMedService_begTime AS DATE) <= @upper_dt
					  AND CAST(t.TimetableMedService_begTime AS DATE) >= @cur_date
				  )
				
			order by t.UslugaComplexMedService_id, t.TimetableMedService_begTime;
						SET NOCOUNT OFF;
		
			SELECT 
				allTT.*,
				ann.*
			FROM
			(SELECT DISTINCT TimetableMedService_Day, UslugaComplexMedService_id FROM #AllTimetable) alld
			OUTER APPLY
			(
				SELECT TOP 1
					   *
				FROM #AllTimetable temp
				WHERE temp.TimetableMedService_Day = alld.TimetableMedService_Day
				AND  temp.UslugaComplexMedService_id = alld.UslugaComplexMedService_id
				ORDER BY temp.TimetableMedService_begTime
			) allTT
			OUTER APPLY
			(
				SELECT TOP 1
					RTRIM(msd.MedServiceDay_Descr) as annotate
				from MedServiceDay msd with (nolock)
				where UslugaComplexMedService_id  = alld.UslugaComplexMedService_id
				and Day_id = alld.TimetableMedService_Day
			) ann
			ORDER BY allTT.UslugaComplexMedService_id, TimetableMedService_Day;
		
		END;
		";

		$result = $this->db->query($query, $param);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
	}

	function getPzmRecordData($data){

		$params['TimetableMedService_id'] = $data['TimetableMedService_id'];

		$query = "
			select top 1
				ttms.TimetableMedService_id,
				ms_pzm.MedService_id as MedService_pzid,
				convert(varchar, ttms.TimetableMedService_begTime, 120) as EvnLabRequest_prmTime
			from
				v_TimetableMedService_lite ttms (nolock)
				left join v_UslugaComplexMedService ucms (nolock) on ucms.UslugaComplexMedService_id = ttms.UslugaComplexMedService_id
				outer apply(
					select top 1
						ms.MedService_id
					from
						v_MedService ms (nolock)
						inner join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
					where
						ms.MedService_id = ISNULL(ucms.MedService_id, ttms.MedService_id)
						and mst.MedServiceType_SysNick = 'pzm'
				) ms_pzm
			where
				ttms.TimetableMedService_id = :TimetableMedService_id
		";

		$result = $this->queryResult($query, $params);
		return $result;
	}

	function getMedPesonalZid($data){
		$sql = "
				select top 1
					msf.MedPersonal_id
				from
					v_MedStaffFact msf with (nolock)
					inner join v_PostMed ps with (nolock) on ps.PostMed_id = msf.Post_id
				where
					ps.PostMed_Name like '%заведующ%' -- запрос для заведующих
					and
					msf.LpuSection_id = :LpuSection_id
					order by msf.WorkData_begDate desc
			";
		$MedPersonal_zid = $this->db->query($sql, array(
				'LpuSection_id' => $data['LpuSection_id']
		))->row_array();
		if (!empty($MedPersonal_zid['MedPersonal_id'])) {
			return $MedPersonal_zid['MedPersonal_id'];
		}
		return null;
	}
}

