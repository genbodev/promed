<?php

/**
 * TimetableResource_model - модель для работы с расписанием ресурса
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

class TimetableResource_model extends Timetable_model {

	/**
	 * 	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Возвращает информацию по использованию примечаний
	 */
	function getTimeTableExtendData($data) {
		// Для Карелии и других менее нагруженных регионов 
		$extend = array(
			'source' => "v_TimetableResource_lite",
			'fields' =>
				"null as TimetableExtend_Descr,
				null as TimetableExtend_updDT,
				null as TimetableExtend_pmUser_Name",
			'join' => ""
		);
		$isExtend = in_array(getRegionNick(), array('kareliya'));
		if ($isExtend) {
			$extend = array(
				'source' => "v_TimetableResource",
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
	 * Получение расписания ресурса для редактирования
	 */
	function getTimetableResourceForEdit( $data ) {

		$outdata = array();

		if ( !isset($data['Resource_id']) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Не указана услуга, для которой показывать расписание'
			);
		}

		$StartDay = isset($data['StartDay']) ? strtotime($data['StartDay']) : time();

		$outdata['StartDay'] = $StartDay;

		$param['StartDay'] = TimeToDay($StartDay);
		$param['EndDay'] = TimeToDay(strtotime("+14 days", $StartDay));
		$param['Resource_id'] = $data['Resource_id'];

		$param['StartDate'] = date( "Y-m-d", $StartDay );
		$param['EndDate'] = date( "Y-m-d", strtotime( "+14 days", $StartDay ) );

		$param['nulltime'] = '00:00:00';

		//https://redmine.swan.perm.ru/issues/72692:
		if ($data['PanelID'] == 'TTRRecordPanel' || $data['PanelID'] == 'TTRDirectionPanel') { //Одна и та же функция используется как для редактирования расписания, так и для записи. Поэтому добавил условие на парент.
			$msflpu = $this->getFirstRowFromQuery("select ms.Lpu_id, ms.MedService_id from v_MedService ms with (nolock) inner join v_Resource r with (nolock) on ms.MedService_id = r.MedService_id where Resource_id = ?", array($data['Resource_id']));
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
		
		$param['StartDayA'] = TimeToDay(strtotime("-1 day",$StartDay));
		$param['EndDayA'] = TimeToDay(strtotime("+13 days", $StartDay));
		$param['Lpu_id'] = $data['Lpu_id'];

		$sql = "
			select
				D.Day_id,
				rtrim(A.Annotation_Comment) as Annotation_Comment,
				rtrim(u.pmUser_Name) as pmUser_Name,
				A.Annotation_updDT
			from v_Day D with (nolock)
			left join v_Annotation A with (nolock) on
				cast(A.Annotation_begDate as date) <= cast(D.day_date as date) AND
				(cast(A.Annotation_endDate as date) >= cast(D.day_date as date) OR A.Annotation_endDate is null) AND
				(A.Annotation_begTime is null or A.Annotation_begTime = :nulltime) AND
				(A.Annotation_endTime is null or A.Annotation_endTime = :nulltime)
			left join v_pmUser u with(nolock) on u.pmUser_id = A.pmUser_updID
			left join v_Resource r with (nolock) on r.Resource_id = A.Resource_id
			left join v_MedService ms with (nolock) on ms.MedService_id = r.MedService_id
			where A.Resource_id = :Resource_id
				and D.Day_id >= :StartDayA
				and D.Day_id < :EndDayA
				and (A.AnnotationVison_id != 3 or ms.Lpu_id = :Lpu_id)";

		//echo getDebugSQL($sql,$param );die;
		$res = $this->db->query( $sql,$param );

		$daydescrdata = $res->result( 'array' );

		foreach ( $daydescrdata as $day ) {
			$outdata['descr'][++$day['Day_id']][] = array(
				'Annotation_Comment' => $day['Annotation_Comment'],
				'pmUser_Name' => $day['pmUser_Name'],
				'Annotation_updDT' => isset( $day['Annotation_updDT'] ) ? $day['Annotation_updDT']->format( "d.m.Y H:i" ) : ''
			);
		}
		
		// Получаем примечания к биркам за период
		// @task https://redmine.swan.perm.ru/issues/128771
		$param['CurrentLpu_id'] = $data['session']['lpu_id'];

		$annotationdata = $this->queryResult("
			select
				convert(varchar(10), A.Annotation_begDate, 120) as Annotation_begDate,
				convert(varchar(10), A.Annotation_endDate, 120) as Annotation_endDate,
				convert(varchar(5), A.Annotation_begTime, 108) as Annotation_begTime,
				convert(varchar(5), A.Annotation_endTime, 108) as Annotation_endTime,
				rtrim(A.Annotation_Comment) as Annotation_Comment,
				rtrim(u.pmUser_Name) as pmUser_Name,
				A.Annotation_updDT
			from v_Annotation A with (nolock)
				left join v_pmUser u with(nolock) on u.pmUser_id = A.pmUser_updID
				left join v_Resource r with (nolock) on r.Resource_id = A.Resource_id
				left join v_MedService ms with (nolock) on ms.MedService_id = r.MedService_id
			where A.Resource_id = :Resource_id
				and (A.Annotation_begDate IS NULL or A.Annotation_begDate <= cast(:EndDate as date))
				and (A.Annotation_endDate IS NULL or cast(:StartDate as date) <= A.Annotation_endDate)
				and (A.Annotation_begTime IS NOT NULL or A.Annotation_endTime IS NOT NULL)
				and (A.AnnotationVison_id != 3 or ms.Lpu_id = :CurrentLpu_id)
		", $param);

		if ( $annotationdata === false ) {
			$annotationdata = array();
		}

		$ext = $this->getTimeTableExtendData($data);
		$selectPersonData = "
			p.Person_BirthDay,
			p.Person_Phone,
			p.PrivilegeType_id,
			rtrim(p.Person_Firname) as Person_Firname,
			rtrim(p.Person_Surname) as Person_Surname,
			rtrim(p.Person_Secname) as Person_Secname,
		";
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
		/*if(!in_array($data['session']['region']['nick'], array('kareliya'))) {  убрал фильтр #146162
			$filter = 'and (t.TimetableResource_deleted is Null or t.TimetableResource_deleted != 2)';
		}*/

		$sql = "
			select
				t.pmUser_updID,
				t.TimetableResource_updDT,
				t.TimetableResource_id,
				t.Person_id,
				t.TimetableResource_Day,
				t.TimetableResource_begTime,
				t.TimetableType_id,
				t.TimetableResource_IsDop,
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
				t.Resource_id,
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
					t.TimetableResource_id = d.TimetableResource_id
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
				/*left join v_EvnQueue q with (nolock) on t.TimetableResource_id = q.TimetableResource_id and t.Person_id = q.Person_id*/
				outer apply  (
					Select top 1 q.*, Evn.pmUser_updId, Evn_insDT as EvnQueue_insDT 
					from EvnQueue q with (nolock) 
					inner join Evn (nolock) on Evn.Evn_id = q.EvnQueue_id and Evn.Evn_deleted = 1 
					where 
						t.TimetableResource_id = q.TimetableResource_id
						--and t.Person_id = q.Person_id
				) q
				
				left join v_pmUser qp with (nolock) on q.pmUser_updId = qp.pmUser_id
				left join Diag dg with (nolock) on dg.Diag_id = d.Diag_id
				{$joinPersonEncrypHIV}
			where t.TimetableResource_Day >= :StartDay
				and t.TimetableResource_Day < :EndDay
				and t.Resource_id = :Resource_id
				and TimetableResource_begTime between :StartDate and :EndDate
				{$filter}
			order by t.TimetableResource_begTime
		";
		$res = $this->db->query($sql, $param);

		//echo getDebugSql($sql, $param);exit;

		$ttrdata = $res->result('array');

		foreach ( $ttrdata as $ttr ) {
			$ttrannotation = array();

			foreach ( $annotationdata as $annotation ) {
				if (
					(empty($annotation['Annotation_begDate']) || $annotation['Annotation_begDate'] <= $ttr['TimetableResource_begTime']->format('Y-m-d'))
					&& (empty($annotation['Annotation_endDate']) || $annotation['Annotation_endDate'] >= $ttr['TimetableResource_begTime']->format('Y-m-d'))
					&& (empty($annotation['Annotation_begTime']) || $annotation['Annotation_begTime'] <= $ttr['TimetableResource_begTime']->format('H:i'))
					&& (empty($annotation['Annotation_endTime']) || $annotation['Annotation_endTime'] >= $ttr['TimetableResource_begTime']->format('H:i'))
				) {
					$ttrannotation[] = $annotation;
				}
			}

			$ttr['annotation'] = $ttrannotation;
			$outdata['data'][$ttr['TimetableResource_Day']][] = $ttr;
			if ( isset($ttr['Person_id']) ) {
				$outdata['occupied'][$ttr['TimetableResource_Day']] = true;
			}
		}

		$outdata['reserved'] = array();
		$reserved = array();
		foreach ( $reserved as $lock ) {
			$outdata['reserved'][] = $lock['TimetableResource_id'];
		}

		return $outdata;
	}

	/**
	 * Получение расписания на один день на ресурс
	 */
	function getTimetableResourceOneDay( $data ) {
		$outdata = array();

		$StartDay = isset($data['StartDay']) ? strtotime($data['StartDay']) : time();

		$outdata['StartDay'] = $StartDay;

		$param['StartDay'] = TimeToDay($StartDay);
		$param['Resource_id'] = $data['Resource_id'];

		$param['EndDate'] = date( "Y-m-d", $StartDay);
		if ($data['PanelID'] == 'TTGRecordOneDayPanel') {
			$msflpu = $this->getFirstRowFromQuery("select ms.Lpu_id, ms.MedService_id from v_MedService ms with (nolock) inner join v_Resource r with (nolock) on ms.MedService_id = r.MedService_id where Resource_id = ?", array($data['Resource_id']));
			$maxDays = GetMedServiceDayCount($msflpu['Lpu_id'], $msflpu['MedService_id']);

			if (date("H:i") >= getShowNewDayTime() && $maxDays) $maxDays++;
			$param['EndDate'] = !empty($maxDays) ? date("Y-m-d", strtotime("+" . $maxDays . " days", time())) : $param['EndDate'];
		}

		$nTime = $StartDay;

		$outdata['day_comment'] = null;
		$outdata['data'] = array();


		$sql = "
			select
				rd.Day_id,
				rtrim(rd.ResourceDay_Descr) as ResourceDay_Descr,
				rtrim(u.pmUser_Name) as pmUser_Name,
				rd.ResourceDay_updDT
			from ResourceDay rd with (nolock)
			left join v_pmUser u with(nolock) on u.pmUser_id = rd.pmUser_updID
			where Resource_id = :Resource_id
				and Day_id = :StartDay";

		$res = $this->db->query($sql, $param);

		$daydescrdata = $res->result('array');

		if ( isset($daydescrdata[0]['ResourceDay_Descr']) ) {
			$outdata['day_comment'] = array(
				'Resource_Descr' => $daydescrdata[0]['ResourceDay_Descr'],
				'pmUser_Name' => $daydescrdata[0]['pmUser_Name'],
				'ResourceDay_updDT' => $daydescrdata[0]['ResourceDay_updDT']
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
					t.TimetableResource_updDT,
					t.TimetableResource_id,
					t.Person_id,
					t.TimetableResource_Day,
					TimetableResource_begTime,
					t.TimetableType_id,
					t.TimetableResource_IsDop,
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
				left outer join v_Resource r with (nolock) on r.Resource_id = t.Resource_id
				left outer join v_MedService ms with (nolock) on ms.MedService_id = r.MedService_id
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
					t.TimetableResource_id = d.TimetableResource_id
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
				/*left join v_EvnQueue q with (nolock) on t.TimetableResource_id = q.TimetableResource_id and t.Person_id = q.Person_id*/
				outer apply  (
					Select top 1 q.*, Evn.pmUser_updId, Evn_insDT as EvnQueue_insDT 
					from EvnQueue q with (nolock) 
					inner join Evn (nolock) on Evn.Evn_id = q.EvnQueue_id and Evn.Evn_deleted = 1 
					where 
						t.TimetableResource_id = q.TimetableResource_id
						--and t.Person_id = q.Person_id
				) q
				
				left join v_pmUser qp with (nolock) on q.pmUser_updId=qp.pmUser_id
				left join Diag dg with (nolock) on dg.Diag_id=d.Diag_id
				{$joinPersonEncrypHIV}
				where t.TimetableResource_Day = :StartDay
					and cast(t.TimetableResource_begTime as date) <= :EndDate
					and t.Resource_id = :Resource_id
					and TimetableResource_begTime is not null
				order by t.TimetableResource_begTime";

		$res = $this->db->query($sql, $param);


		$ttsdata = $res->result('array');


		foreach ( $ttsdata as $tts ) {
			$outdata['data'][] = $tts;
		}

		$sql = "
			select TimetableResource_id from TimetableLock with(nolock)";

		$res = $this->db->query($sql);

		$outdata['reserved'] = $res->result('array');

		return $outdata;
	}

	/**
	 * Приём из очереди без записи
	 */
	function acceptWithoutRecord( $data ) {
		$this->load->helper( 'Reg' );

		if (!empty($data['EvnDirection_id'])) {
			// если уже записано на бирку то ещё запись выполняться не должна
			$query = "
				select
					TimetableResource_id
				from
					v_TimetableResource_lite (nolock)
				where
					EvnDirection_id = :EvnDirection_id
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['TimetableResource_id'])) {
					return array('Error_Msg' => 'Пациент уже принят');
				}
			} else {
				return array('Error_Msg' => 'Ошибка проверки наличия бирки');
			}

			$query = "
				select
					TTR.Resource_id,
					ED.Person_id,
					convert(varchar(10), dbo.tzGetDate(), 104) as date
				from
					v_EvnDirection_all ED with(nolock)
					inner join v_TimetableResource_lite TTR with(nolock) on TTR.TimetableResource_id = ED.TimetableResource_id
				where
					ED.EvnDirection_id = :EvnDirection_id
			";

			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['date'])) {
					$data['date'] = $resp[0]['date'];
					$data['Resource_id'] = $resp[0]['Resource_id'];
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
			'Resource_id' => $data['Resource_id'],
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

			exec p_TimetableResource_ins
				@TimetableResource_id = @Res output,
				@Person_id = :Person_id,
				@Resource_id = :Resource_id,
				@TimetableResource_factTime = @fact_dt,
				@EvnDirection_id = :EvnDirection_id,
				@TimetableResource_Day = :Timetable_Day,
				@RecClass_id = 3,
				@RecMethodType_id = 1,
				@pmUser_id = :pmUser_id
			select @Res as TimetableResource_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		// die(getDebugSql($sql, $params));

		$res = $this->db->query( $sql,
			$params );
		if ( is_object( $res ) ) {
			$resp = $res->result( 'array' );
			if ( !empty( $resp[0]['TimetableResource_id'] ) ) {
				// отправка STOMP-сообщения
				sendFerStompMessage( array(
						'id' => $resp[0]['TimetableResource_id'],
						'timeTable' => 'TimetableResource',
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
	 * Создание расписания для ресурса
	 */
	function createTTRSchedule( $data ) {

		$this->beginTransaction();
		if ($data['CreateAnnotation'] == 1) {
			$this->load->model('Annotation_model');
			$annotation_data = $data;
			$annotation_data['Annotation_id'] = null;
			$annotation_data['MedService_id'] = null;
			$annotation_data['MedStaffFact_id'] = null;
			$annotation_data['Annotation_begDate'] = $data['CreateDateRange'][0];
			$annotation_data['Annotation_endDate'] = $data['CreateDateRange'][1];
			$annotation_data['Annotation_begTime'] = $data['StartTime'];
			$annotation_data['Annotation_endTime'] = $data['EndTime'];
			$res = $this->Annotation_model->save($annotation_data);
			if (!empty($res['Error_Msg'])) {
				$this->rollbackTransaction();
				return $res;
			}
		}
		
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

		if ( true !== ($res = $this->checkTimetableResourceTimeNotOccupied($data)) ) {
			return $res;
		}
		if ( true !== ($res = $this->checkTimetableResourceTimeNotExists($data)) ) {
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

				exec p_TimetableResource_fill
					@Resource_id = :Resource_id,
					@TimetableResource_Day = :TimetableResource_Day,
					@TimetableResource_Time = :TimetableResource_Time,
					@TimetableType_id = :TimetableType_id,
					@StartTime = :StartTime,
					@EndTime = :EndTime,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";
			$resp = $this->queryResult($sql, array(
				'Resource_id' => $data['Resource_id'],
				'TimetableResource_Day' => $day,
				'TimetableResource_Time' => $data['Duration'],
				'pmUser_id' => $data['pmUser_id'],
				'TimetableType_id' => $data['TimetableType_id'],
				'StartTime' => $data['StartTime'],
				'EndTime' => $data['EndTime'],
			));
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}

			// Пересчета для ресурса в хранимке нет, вызываем вручную
			$resp = $this->execCommonSP('p_ResourceDay_recount', array(
				'Resource_id' => $data['Resource_id'],
				'Day_id' => $day,
				'pmUser_id' => $data['pmUser_id'],
			));
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}
		
		$this->commitTransaction();

		// отправка STOMP-сообщения
		sendFerStompMessage(array(
			'timeTable' => 'TimetableResource',
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
	 * Проверка, есть ли на заданном дне(или интервале дней) и интервале времени занятые бирки для ресурса
	 */
	function checkTimetableResourceTimeNotOccupied( $data ) {

		if ( isset($data['Day']) ) {
			$sql = "
				select
					count(*) as cnt
				from v_TimetableResource_lite with (nolock)
				where
					Resource_id = :Resource_id
					and Person_id is not null
					and ( 
						( TimetableResource_begTime >= :StartTime and TimetableResource_begTime < :EndTime )
						or ( DATEADD( minute, TimetableResource_Time, TimetableResource_begTime  ) > :StartTime and DATEADD( minute, TimetableResource_Time, TimetableResource_begTime  ) < :EndTime )
						or ( DATEADD( minute, TimetableResource_Time, TimetableResource_begTime  ) > :StartTime and TimetableResource_begTime < :StartTime ) 
					)";

			$res = $this->db->query(
				$sql, array(
					'StartTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['StartTime']))),
					'EndTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['EndTime']))),
					'Resource_id' => $data['Resource_id']
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
					from v_TimetableResource_lite with (nolock)
					where
						Resource_id = :Resource_id
						and Person_id is not null
						and ( 
							( TimetableResource_begTime >= :StartTime and TimetableResource_begTime < :EndTime )
							or ( DATEADD( minute, TimetableResource_Time, TimetableResource_begTime  ) > :StartTime and DATEADD( minute, TimetableResource_Time, TimetableResource_begTime  ) < :EndTime )
							or ( DATEADD( minute, TimetableResource_Time, TimetableResource_begTime  ) > :StartTime and TimetableResource_begTime < :StartTime ) 
						)";

				$res = $this->db->query(
					$sql, array(
						'StartTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['StartTime']))),
						'EndTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['EndTime']))),
						'Resource_id' => $data['Resource_id']
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
	 * Проверка, есть ли на заданном дне(или интервале дней) и интервале времени созданные бирки для услуги
	 */
	function checkTimetableResourceTimeNotExists( $data ) {


		if ( isset($data['Day']) ) {
			$sql = "
				select
					count(*) as cnt
				from v_TimetableResource_lite with (nolock)
				where
					Resource_id = :Resource_id
					and ( 
						( TimetableResource_begTime >= :StartTime and TimetableResource_begTime < :EndTime )
						or ( DATEADD( minute, TimetableResource_Time, TimetableResource_begTime  ) > :StartTime and DATEADD( minute, TimetableResource_Time, TimetableResource_begTime  ) < :EndTime )
						or ( DATEADD( minute, TimetableResource_Time, TimetableResource_begTime  ) > :StartTime and TimetableResource_begTime < :StartTime ) 
					)
			";

			$res = $this->db->query(
				$sql, array(
					'StartTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['StartTime']))),
					'EndTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['EndTime']))),
					'UslugaComplexResource_id' => $data['UslugaComplexResource_id']
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
					from v_TimetableResource_lite with (nolock)
					where
						Resource_id = :Resource_id
						and ( 
							( TimetableResource_begTime >= :StartTime and TimetableResource_begTime < :EndTime )
							or ( DATEADD( minute, TimetableResource_Time, TimetableResource_begTime  ) > :StartTime and DATEADD( minute, TimetableResource_Time, TimetableResource_begTime  ) < :EndTime )
							or ( DATEADD( minute, TimetableResource_Time, TimetableResource_begTime  ) > :StartTime and TimetableResource_begTime < :StartTime ) 
						)
				";

				$res = $this->db->query(
					$sql, array(
						'StartTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['StartTime']))),
						'EndTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['EndTime']))),
						'Resource_id' => $data['Resource_id']
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
	 * Копирование расписания для ресурса
	 */
	function copyTTRSchedule( $data ) {

		if ( empty($data['CopyToDateRange'][0]) ) {
			return array(
				'Error_Msg' => 'Не указан диапазон для вставки расписания.'
			);
		}
	
		$this->beginTransaction();
		if (count($data['copyAnnotationGridData'])) {
			$this->load->model('Annotation_model');
			$annotation_data = $data;
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

		if ( true !== ($res = $this->checkTimetableResourceDayNotOccupied($data)) ) {
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
			
			if (count($data['copyAnnotationGridData'])) {
				$annotation_data['Annotation_copyFromDate'] = date('Y-m-d', strtotime($data['CreateDateRange'][0])); // Начало копируемого интервала
				$annotation_data['Annotation_begDate'] = date('Y-m-d', strtotime( $data['CopyToDateRange'][0] ) + 86400 * $nShift * $n); // Начало целевого интервала
				$annotation_data['Annotation_endDate'] = date('Y-m-d', strtotime( $data['CopyToDateRange'][0] ) + 86400 * $nShift * ($n+1) - 86400); // Окончание целевого интервала
				$annotation_data['Annotation_endDate'] = min($annotation_data['Annotation_endDate'], date('Y-m-d', strtotime($data['CopyToDateRange'][1])));
				foreach ($data['copyAnnotationGridData'] as $annotation_id) {
					$annotation_data['Annotation_id'] = $annotation_id;
					$res = $this->Annotation_model->copy($annotation_data);
					if (!empty($res['Error_Msg'])) {
						$this->rollbackTransaction();
						return $res;
					}
				}
			}
			
			$sql = "
				declare
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				exec p_TimetableResource_copy
					@Resource_id = :Resource_id,
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
					'Resource_id' => $data['Resource_id'],
					'SourceStartDay' => $SourceStartDay,
					'SourceEndDay' => $SourceEndDay,
					'TargetStartDay' => $nTargetStart,
					'TargetEndDay' => $nTargetEnd,
					'CopyTimetableExtend_Descr' => NULL,
					'pmUser_id' => $data['pmUser_id']
				)
			);

			if ( is_object($res) ) {
				$resp = $res->result('array');
				$this->commitTransaction();
				if ( count($resp) > 0 && empty($resp[0]['Error_Msg']) ) {
					// отправка STOMP-сообщения
					sendFerStompMessage(array(
						'timeTable' => 'TimetableResource',
						'action' => 'AddTicket',
						'setDate' => date("c"),
						'begDate' => date("c", DayMinuteToTime($nTargetStart, 0)),
						'endDate' => date("c", DayMinuteToTime($nTargetEnd, 0)),
						'MedStaffFact_id' => (!empty($data['session']['CurMedStaffFact_id']) ? $data['session']['CurMedStaffFact_id'] : null)
					), 'RulePeriod');
				}
			}
			
			$n++;
		}

		return array(
			'Error_Msg' => ''
		);
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
			exec p_TimetableResource_clearDay
				@TimetableResource_Day = :TimetableResource_Day,
				@Resource_id = :Resource_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output
			select @Res as TimetableResource_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$res = $this->db->query(
			$sql, array(
				'Resource_id' => $data['Resource_id'],
				'TimetableResource_Day' => $data['Day'],
				'pmUser_id' => $data['pmUser_id']
			)
		);

		if ( is_object($res) ) {
			$resp = $res->result('array');
			if ( count($resp) > 0 && empty($resp[0]['Error_Msg']) ) {
				// отправка STOMP-сообщения
				sendFerStompMessage(array(
					'timeTable' => 'TimetableResource',
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
	function getTTRHistory( $data ) {

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
				convert(varchar(10), TimetableResourceHist_insDT, 104) + ' ' + convert(varchar(8), TimetableResourceHist_insDT, 108) as TimetableHist_insDT,
				rtrim(PMUser_Name) as PMUser_Name,
				TimetableActionType_Name as TimetableActionType_Name,
				TimetableType_Name,
				{$selectPersonData}
			from TimetableResourceHist ttsh with (nolock)
			left join v_pmUser pu with (nolock) on ttsh.TimetableResourceHist_userID = pu.pmuser_id
			left join TimetableActionType ttat with (nolock) on ttat.TimetableActionType_id = ttsh.TimetableActionType_id
			left join v_TimetableType ttt with(nolock) on ttt.TimetableType_id = isnull(ttsh.TimetableType_id, 1)
			left join v_Person_ER p with (nolock) on ttsh.Person_id = p.Person_id
			{$joinPersonEncrypHIV}
			where TimetableResource_id = :TimetableResource_id";

		$res = $this->db->query(
			$sql, array(
				'TimetableResource_id' => $data['TimetableResource_id']
			)
		);

		if ( is_object($res) ) {
			return $res = $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function checkTimetableResourceOccupied( $data ) {
		$sql = "
			SELECT TimetableResource_id, Person_id
			FROM v_TimetableResource_lite with(nolock)
			WHERE
				TimetableResource_id = :Id
				
		";

		$res = $this->db->query(
			$sql, array(
				'Id' => $data['TimetableResource_id']
			)
		);
		if ( is_object($res) ) {
			$res = $res->result('array');
		}
		if ( !isset($res[0]) || !isset($res[0]['TimetableResource_id']) ) {
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
	 * Проверка, есть ли на заданном дне(или интервале дней) занятые бирки для ресурса
	 */
	function checkTimetableResourceDayNotOccupied($data) {
		if ( isset($data['Day']) ) {
			$sql = "
				SELECT count(*) as cnt
				FROM v_TimetableResource_lite with (nolock)
				WHERE
					TimetableResource_Day = :Day
					and Resource_id = :Resource_id
					and Person_id is not null
					and TimetableResource_begTime is not null
			";

			$res = $this->db->query(
				$sql, array(
					'Day' => $data['Day'],
					'Resource_id' => $data['Resource_id'],
				)
			);
		}
		if ( isset($data['StartDay']) ) {
			$sql = "
				SELECT count(*) as cnt
				FROM v_TimetableResource_lite with (nolock)
				WHERE
					TimetableResource_day between :StartDay and :EndDay
					and Resource_id = :Resource_id
					and Person_id is not null
					and TimetableResource_begTime is not null
			";

			$res = $this->db->query(
				$sql, array(
					'StartDay' => $data['StartDay'],
					'EndDay' => $data['EndDay'],
					'Resource_id' => $data['Resource_id'],
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
	function setTTRType( $data ) {
		$data['object'] = 'TimetableResource';

		if ( isset($data['TimetableResourceGroup']) ) {
			$data['TimetableResourceGroup'] = json_decode($data['TimetableResourceGroup']);
		}
		if ( isset($data['TimetableResourceGroup']) && count($data['TimetableResourceGroup']) > 0 ) {
			// Обработка группы бирок в отдельном методе
			return $this->setTTRTypeGroup($data);
		} else {
			if ( true === ($res = $this->checkTimetableResourceOccupied($data)) ) {
				return array(
					'Error_Msg' => 'Бирка занята, изменение типа невозможно.'
				);
			}
		}

		// Получаем службу и день, а также заодно проверяем, что бирка существует
		$res = $this->db->query("
			select
				Resource_id,
				TimetableResource_Day
			from v_TimetableResource_lite with (nolock)
			where TimetableResource_id = :TimetableResource_id", array(
				'TimetableResource_id' => $data['TimetableResource_id']
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
			$Resource_id = $res[0]['Resource_id'];
			$Day = $res[0]['TimetableResource_Day'];
		}

		$sql = "
			declare
				@ErrCode bigint,
				@TimetableType_SysNick varchar(50),
				@ErrMsg varchar(4000);
			set @TimetableType_SysNick = (select top 1 TimetableType_SysNick from v_TimetableType (nolock) where TimetableType_id = :TimetableType_id);

			exec p_TimetableResource_setType
				@TimetableResource_id = :TimetableResource_id,
				@TimetableType_id = :TimetableType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output
			select @TimetableType_SysNick as TimetableType_SysNick, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$res = $this->db->query(
			$sql, array(
				'TimetableResource_id' => $data['TimetableResource_id'],
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
						'id' => $data['TimetableResource_id'],
						'timeTable' => 'TimetableResource',
						'action' => $action,
						'setDate' => date("c")
					), 'Rule');
				}
			}
		}

		// Пересчет теперь прямо в хранимке

		return array(
			'Error_Msg' => ''
		);
	}

	/**
	 * Изменение типа бирок у службы для группы бирок
	 */
	function setTTRTypeGroup( $data ) {

		if ( true !== ($res = $this->checkTimetablesFree($data)) ) {
			return $res;
		}

		// Получаем службу и список дней, на которые мы выделили бирки
		$res = $this->db->query("
			select
				TimetableResource_id,
				Resource_id,
				TimetableResource_Day
			from v_TimetableResource_lite with (nolock)
			where TimetableResource_id in (" . implode(', ', $data['TimetableResourceGroup']) . ")", array(
				'TimetableResource_id' => $data['TimetableResource_id']
			)
		);

		if ( is_object($res) ) {
			$res = $res->result('array');
		} else {
			return false;
		}
		// Меняем тип у каждой бирки по отдельности. Не лучший вариант конечно
		foreach ( $res as $row ) {

			$Resource_id = $row['Resource_id'];
			$Day = $row['TimetableResource_Day'];

			$sql = "
				declare
					@ErrCode bigint,
					@TimetableType_SysNick varchar(50),
					@ErrMsg varchar(4000);
				set @TimetableType_SysNick = (select top 1 TimetableType_SysNick from v_TimetableType (nolock) where TimetableType_id = :TimetableType_id);

				exec p_TimetableResource_setType
					@TimetableResource_id = :TimetableResource_id,
					@TimetableType_id = :TimetableType_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output
				select @TimetableType_SysNick as TimetableType_SysNick, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

			$res = $this->db->query(
				$sql, array(
					'TimetableResource_id' => $row['TimetableResource_id'],
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
							'id' => $row['TimetableResource_id'],
							'timeTable' => 'TimetableResource',
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
		$data['object'] = 'TimetableResource';
		if ( isset($data['TimetableResource_id']) ) {
			$data['TimetableResourceGroup'] = array($data['TimetableResource_id']);
		} else {
			$data['TimetableResourceGroup'] = json_decode($data['TimetableResourceGroup']);
		}

		if ( true !== ($res = $this->checkTimetablesFree($data)) ) {
			return $res;
		}

		// Получаем врача и список дней, на которые мы выделили бирки
		$res = $this->db->query("
			select
					TimetableResource_id,
					TimetableResource_Day
			from v_TimetableResource_lite with (nolock)
			where TimetableResource_id in (" . implode(', ', $data['TimetableResourceGroup']) . ")"
		);

		if ( is_object($res) ) {
			$res = $res->result('array');
		} else {
			return false;
		}
		// Удаляем каждую бирку по отдельности. Не лучший вариант конечно
		foreach ( $res as $row ) {



			//Удаляем бирку
			$sql = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec p_TimetableResource_del
					@TimetableResource_id = :TimetableResource_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$res = $this->db->query(
				$sql, array(
					'TimetableResource_id' => $row['TimetableResource_id'],
					'pmUser_id' => $data['pmUser_id']
				)
			);

			if ( is_object($res) ) {
				$resp = $res->result('array');
				if ( count($resp) > 0 && empty($resp[0]['Error_Msg']) ) {
					// отправка STOMP-сообщения
					sendFerStompMessage(array(
						'id' => $row['TimetableResource_id'],
						'timeTable' => 'TimetableResource',
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
	 * Получение комментария на день для службы
	 */
	function getTTRDayComment( $data ) {
		$sql = "
			select
				mpd.ResourceDay_Descr,
				mpd.ResourceDay_id
			from ResourceDay mpd with (nolock)
			where Resource_id = :Resource_id and Day_id = :Day_id
		";

		$res = $this->db->query($sql, array(
			'Resource_id' => $data['Resource_id'],
			'Day_id' => $data['Day']
		));

		if ( is_object($res) ) {
			return $res = $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение комментария на день для службы
	 */
	function saveTTRDayComment( $data ) {

		$sql = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			exec p_ResourceDay_setDescr
				@ResourceDay_id = @Res,
				@Day_id = :Day_id,
				@Resource_id = :Resource_id,
				@ResourceDay_Descr = :ResourceDay_Descr,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output";

		$res = $this->db->query(
			$sql, array(
				'ResourceDay_Descr' => $data['ResourceDay_Descr'],
				'Resource_id' => $data['Resource_id'],
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

		$sql = "
			SELECT
				t.".$obj.",
				t.pmUser_updId,
				pu.Lpu_id,
				l.Org_id
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
	 * Редактирование переданного набора бирок
	 */
	function editTTRSet( $data ) {

		$TTRSet = json_decode($data['selectedTTR']);

		if ( $this->checkTTROccupied($TTRSet) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Одна из выбранных бирок занята. Операция невозможна.'
			);
		}

		// Пустая строка передается как NULL, надо как пустую строку передавать
		if ( $data['ChangeTTRDescr'] ) {
			$data['TimetableExtend_Descr'] = isset($data['TimetableExtend_Descr']) ? $data['TimetableExtend_Descr'] : '';
		} else {
			$data['TimetableExtend_Descr'] = NULL;
		}

		if ( $data['ChangeTTRType'] ) {
			$data['TimetableType_id'] = isset($data['TimetableType_id']) ? $data['TimetableType_id'] : 1;
		} else {
			$data['TimetableType_id'] = NULL;
		}

		foreach ( $TTRSet as $TTR ) {
			$query = "
				declare
					@ErrCode int,
					@TimetableType_SysNick varchar(50),
					@ErrMessage varchar(4000);
				set @TimetableType_SysNick = (select top 1 TimetableType_SysNick from v_TimetableType (nolock) where TimetableType_id = :TimetableType_id);
				exec p_TimetableResource_edit
					@TimetableResource_id = :TimetableResource_id,
					@TimetableType_id = :TimetableType_id,
					@TimetableExtend_Descr = :TimetableExtend_Descr,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @TimetableType_SysNick as TimetableType_SysNick, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$res = $this->db->query(
				$query, array(
					'TimetableResource_id' => $TTR,
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
							'id' => $TTR,
							'timeTable' => 'TimetableResource',
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
	function checkTTROccupied( $TTRSet ) {
		if ( count($TTRSet) == 0 ) {
			return false;
		}
		$sql = "
			SELECT count(*) as cnt
			FROM v_TimetableResource_lite with (nolock)
			WHERE
				TimetableResource_id in (" . implode(',', $TTRSet) . ")
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
	 * Добавление дополнительной бирки для службы
	 */
	function addTTRDop( $data ) {
		if ( empty($data['Day']) ) {
			$data['Day'] = TimeToDay(time());
		}
		if ( empty($data['StartTime']) ) {
			$data['StartTime'] = date('H:i');
		}
		if ( empty($data['Resource_id']) ) {
			$Resource_id = $this->getFirstResultFromQuery("
				select top 1 Resource_id
				from v_Resource with(nolock)
				where
					MedService_id = :MedService_id
					and Resource_begDT <= :begDate
					and (Resource_endDT is null or Resource_endDT > :begDate)
			", array(
				'MedService_id' => $data['MedService_id'],
				'begDate' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['StartTime']))),
			));
			if (!$Resource_id) {
				return false;
			}
			$data['Resource_id'] = $Resource_id;
		}

		$this->beginTransaction();
		if (!empty($data['CreateAnnotation']) && $data['CreateAnnotation'] == 1) {
			$this->load->model('Annotation_model');
			$annotation_data = $data;
			$annotation_data['Annotation_id'] = null;
			$annotation_data['MedService_id'] = null;
			$annotation_data['MedStaffFact_id'] = null;
			$annotation_data['Annotation_begDate'] = date("Y-m-d", DayMinuteToTime($data['Day'], 0));
			$annotation_data['Annotation_endDate'] = date("Y-m-d", DayMinuteToTime($data['Day'], 0));
			$annotation_data['Annotation_begTime'] = $data['StartTime'];
			$annotation_data['Annotation_endTime'] = date("H:i", DayMinuteToTime($data['Day'], StringToTime($data['StartTime'])) + 60);
			$res = $this->Annotation_model->save($annotation_data);
			if (!empty($res['Error_Msg'])) {
				$this->rollbackTransaction();
				return $res;
			}
		}

		if (empty($data['ignoreTTRExist'])) {
			$sql = "
				select
					count(*) as cnt
				from v_TimetableResource_lite with (nolock)
				where
					Resource_id = :Resource_id
					and TimetableResource_begTime > DATEADD( minute, -2, :StartTime)
					and TimetableResource_begTime < DATEADD( minute, 2, :StartTime)
					and TimetableResource_begTime is not null
			";
			$res = $this->db->query(
				$sql, array(
					'Resource_id' => $data['Resource_id'],
					'StartTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['StartTime'])))
				)
			);

			if ( is_object($res) ) {
				$res = $res->result('array');
			} else {
				return false;
			}
		}

		if ( !empty($data['ignoreTTRExist']) || empty($res[0]['cnt']) ) {
			$sql = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = null;

				exec p_TimetableResource_ins
					@TimetableResource_id = @Res output,
					@Resource_id = :Resource_id,
					@TimetableResource_Day = :TimetableResource_Day,
					@TimetableResource_begTime = :TimetableResource_begTime,
					@TimetableResource_Time = :TimetableResource_Time,
					@TimetableType_id = 1,
					@TimetableResource_IsDop = 1,
					@pmUser_id = :pmUser_id
				select @Res as TimetableResource_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;";
			$params = array(
				'Resource_id' => $data['Resource_id'],
				'TimetableResource_Day' => $data['Day'],
				'TimetableResource_begTime' => date("Y-m-d H:i:s", DayMinuteToTime($data['Day'], StringToTime($data['StartTime']))),
				'TimetableResource_Time' => 0,
				'pmUser_id' => $data['pmUser_id']
			);

			if (!empty($data['withoutRecord'])) {
				$params['TimetableResource_begTime'] = null; // доп. бирка б/з
			}

			$res = $this->db->query($sql, $params);

			if ( is_object($res) ) {
				$resp = $res->result('array');
				$this->commitTransaction();
				if ( count($resp) > 0 && !empty($resp[0]['TimetableResource_id']) ) {
					// отправка STOMP-сообщения
					sendFerStompMessage(array(
						'id' => $resp[0]['TimetableResource_id'],
						'timeTable' => 'TimetableResource',
						'action' => 'AddTicket',
						'setDate' => date("c")
					), 'Rule');

					// Пересчет теперь прямо в хранимке

					return array(
						'TimetableResource_id' => $resp[0]['TimetableResource_id'],
						'TimetableResource_begTime' => $params['TimetableResource_begTime'],
						'Error_Msg' => ''
					);
				}
			}
			return array(
				'Error_Msg' => 'Дополнительная бирка не создана.'
			);
		} else {
			return array(
				'Error_Msg' => 'Дополнительная бирка должна отстоять не менее чем на 2 минуты от существующих. Выберите другое время или удалите бирки.'
			);
		}
	}

	/**
	 * Проверка времени записи перед блокировкой бирки
	 *
	 * Если добавляемое назначение имеет разницу по времени менее 15 минут
	 * бирки с каким-либо уже имеющимся в списке справа,
	 * выдавать предупреждение "Существует назначение, близкое по времени записи к создаваемому."
	 */
	function checkBeforeLock($data) {
		$sql = "
		declare
			@TimetableResource_id bigint = :TimetableResource_id,
			@Person_id bigint = :Person_id,
			@pmUser_id bigint = :pmUser_id,
			@day bigint,
			@setTime datetime,
			@begTime datetime,
			@endTime datetime;

		select
			@setTime = TimetableResource_begTime,
			@day = TimetableResource_Day
			from v_TimetableResource_lite with (nolock)
			where TimetableResource_id = @TimetableResource_id

		set @begTime = DATEADD(minute, -14, @setTime);
		set @endTime = DATEADD(minute, 14, @setTime);

		select ttms.TimetableResource_id
			from v_TimetableResource_lite ttms with (nolock)
			where ttms.TimetableResource_Day = @day
			and ttms.TimetableResource_begTime between @begTime and @endTime
			and ttms.Person_id = @Person_id
		";
		$queryParams = array(
			'TimetableResource_id' => $data['TimetableResource_id'],
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id'],
		);
		//echo getDebugSQL($sql, $queryParams);exit();
		$res = $this->db->query($sql, $queryParams);
		$response = array(array('Error_Msg'=>null,'Error_Code'=>null));
		if ( is_object($res) ) {
			$res = $res->result('array');
			if (count($res) > 0) {
				$response[0]['Alert_Msg'] = 'Существует назначение, близкое по времени записи к создаваемому';
			}
			return $response;
		} else {
			$response[0]['Error_Msg'] = 'Ошибка запроса к БД';
			$response[0]['Error_Code'] = 500;
			return $response;
		}
	}

	/**
	 * Получение информации по бирке
	 */
	function getTTRInfo( $data ) {
		$query = "
			select top 1
				R.Resource_id,
				convert(varchar(20), TTR.TimetableResource_begTime, 120) as TimetableResource_abegTime,
				convert(varchar(20), TTRN.TimetableResource_begTime, 120) as TimetableResource_nextTime
			from
				v_TimetableResource_lite TTR with (nolock)
				inner join v_Resource R with(nolock) on R.Resource_id = TTR.Resource_id
				outer apply(
					select top 1 TimetableResource_begTime from v_TimetableResource_lite with(nolock) where 
						Resource_id = TTR.Resource_id and 
						TimetableResource_Day = TTR.TimetableResource_Day and 
						TimetableResource_begTime > TTR.TimetableResource_begTime
				) as TTRN
			where
				TTR.TimetableResource_id = :TimetableResource_id
		";

		$res = $this->db->query( $query,
			array(
			'TimetableResource_id' => $data['TimetableResource_id']
		));

		if ( is_object( $res ) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Добавление расписания на ресурс
	 */
	function addTimetableResource($data) {
		$resp_tt = array();

		if (!empty($data['TimeTableResourceCreate'])) {
			foreach ($data['TimeTableResourceCreate'] as $one) {
				if (!isset($one['TimeTableResource_begTime'])) {
					throw new Exception("Не указана дата/время начала приёма");
				}
				if (!isset($one['TimeTableResource_Time'])) {
					throw new Exception("Не указана длительность приёма");
				}
				if (!isset($one['TimeTableType_id'])) {
					throw new Exception("Не указан тип бирки");
				}
				if (
					isset($one['TimeTableResource_IsDop'])
					&& $one['TimeTableResource_IsDop'] !== 1 && $one['TimeTableResource_IsDop'] !== 0 
					&& $one['TimeTableResource_IsDop'] !== '1' && $one['TimeTableResource_IsDop'] !== '0'
				) {
					throw new Exception("Неверное значение в поле TimeTableResource_IsDop");
				} else if(!isset($one['TimeTableResource_IsDop'])){
					throw new Exception("Не указан признак дополнительной бирки");
				}
				$query = "
					declare
						@Res bigint,
						@ErrCode bigint,
						@ErrMsg varchar(4000);
					set @Res = null;
		
					exec p_TimetableResource_ins
						@TimetableResource_id = @Res output,
						@Resource_id = :Resource_id,
						@TimetableResource_Day = :TimetableResource_Day,
						@TimetableResource_begTime = :TimetableResource_begTime,
						@TimetableResource_Time = :TimetableResource_Time,
						@TimetableType_id = :TimeTableType_id,
						@TimetableResource_IsDop = :TimetableResource_IsDop,
						@pmUser_id = :pmUser_id;
					select @Res as TimetableResource_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
				";

				$resp = $this->queryResult($query, array(
					'Resource_id' => $data['Resource_id'],
					'TimetableResource_Day' => TimeToDay(strtotime($one['TimetableResource_begTime'])),
					'TimetableResource_begTime' => date('Y-m-d H:i:s', strtotime($one['TimetableResource_begTime'])),
					'TimetableResource_Time' => $one['TimetableResource_Time'],
					'TimeTableType_id' => $one['TimeTableType_id'],
					'TimetableResource_IsDop' => !empty($one['TimetableResource_IsDop']) ? $one['TimetableResource_IsDop'] : null,
					'pmUser_id' => $data['pmUser_id']
				));

				if (!empty($resp[0]['TimetableResource_id'])) {
					$resp_tt[] = array(
						'TimetableResource_id' => $resp[0]['TimetableResource_id']
					);
				}

				if (!empty($resp[0]['Error_Msg'])) {
					throw new Exception($resp[0]['Error_Msg']);
				}
			}
		}

		return $resp_tt;
	}

	/**
	 * Редактирование расписания на ресурс
	 */
	function editTimetableResource($data) {
		if (!empty($data['TimeTableResourceEdit'])) {
			foreach ($data['TimeTableResourceEdit'] as $one) {
				if (!isset($one['TimeTableResource_id'])) {
					throw new Exception("Не указан идентификатор бирки");
				}

				if (!empty($one['TimeTableType_id'])) {
					// смена типа бирки
					$tmp = $this->queryResult("
						declare
							@ErrCode bigint,
							@TimetableResource_id bigint = :TimetableResource_id,
							@ErrMsg varchar(4000);
		
						exec p_TimetableResource_setType
							@TimetableResource_id = @TimetableResource_id output,
							@TimetableType_id = :TimetableType_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
						select @TimetableResource_id as TimetableResource_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
					", array(
						'TimetableResource_id' => $one['TimeTableResource_id'],
						'TimetableType_id' => $one['TimeTableType_id'],
						'pmUser_id' => $data['pmUser_id']
					));
					if (empty($tmp)) {
						throw new Exception('Ошибка запроса к БД', 500);
					}
					if (isset($tmp['Error_Msg'])) {
						throw new Exception($tmp['Error_Msg'], 500);
					}
				}

				if (isset($one['TimeTableResource_IsDop'])) {
					// проставление признака дополнительной бирки
					$this->db->query("
						update
							TimetableResource with (rowlock)
						set
							TimeTableResource_IsDop = :TimeTableResource_IsDop,
							pmUser_updID = :pmUser_id,
							TimeTableResource_updDT = GETDATE()
						where
							TimetableResource_id = :TimetableResource_id
					", array(
						'TimetableResource_id' => $one['TimeTableResource_id'],
						'TimeTableResource_IsDop' => $one['TimeTableResource_IsDop'],
						'pmUser_id' => $data['pmUser_id']
					));
				}

				if (!empty($one['TimeTableResourceDelStatus'])) {
					// удаление бирки
					$tmp = $this->execCommonSP('p_TimetableResource_del', array(
						'pmUser_id' => $data['pmUser_id'],
						'TimetableResource_id' => $one['TimeTableResource_id']
					), 'array_assoc');
					if (empty($tmp)) {
						throw new Exception('Ошибка запроса к БД', 500);
					}
					if (isset($tmp['Error_Msg'])) {
						throw new Exception($tmp['Error_Msg'], 500);
					}
				}
			}
		}

		return array();
	}
}
