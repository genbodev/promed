<?php

/**
 * TimetableGraf_model - модель для работы с расписанием в поликлинике
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (megatherion@list.ru)
 * @version      22.05.2009
 */
/**
 * Загрузка базовой модели для работы с расписанием
 */
require_once("Timetable_model.php");

/**
 * @property LpuRegion_model LpuRegion_model
 * @property EvnDirectionAll_model $EvnDirectionAll_model
 */
class TimetableGraf_model extends Timetable_model {

	/**
	 * Получение данных для отображения и открытия самых часто используемых пользователем расписаний
	 * Входящие данные: pmUser_id
	 * На выходе массив
	 * @author       Alexander Permyakov
	 */
	function getTopTimetable( $data ) {
		$params['pmUser_id'] = $data['pmUser_id'];
		$sql = "
			SELECT TOP 20
				ttc.TimetableCount_id,
				ttc.TimetableCount_Count,
				ttc.TimetableObject_id,
				ttc.MedPersonal_id,
				ttc.MedStaffFact_id,
				ttc.LpuSection_id,
				case when ttc.TimetableObject_id > 1
				then lc.LpuSection_Name + ' / ' + lcl.Lpu_Nick
				else mp.Person_Fin + ' / ' + lcm.LpuSection_Name + ' / ' + lcml.Lpu_Nick
				end as caption,
				case when ttc.TimetableObject_id > 1
				then lc.LpuSectionProfile_id
				else lcm.LpuSectionProfile_id
				end as LpuSectionProfile_id,

				mp.Person_Fio as MedPersonal_FIO,

				case when ttc.TimetableObject_id > 1
				then lc.LpuUnit_id
				else lcm.LpuUnit_id
				end as LpuUnit_id,

				case when ttc.TimetableObject_id > 1
				then lc.LpuSection_Name
				else lcm.LpuSection_Name
				end as LpuSection_Name,

				case when ttc.TimetableObject_id > 1
				then lcl.Lpu_id
				else lcml.Lpu_id
				end as Lpu_id,
				case when ttc.TimetableObject_id > 1
				then lcl.Lpu_Nick
				else lcml.Lpu_Nick
				end as Lpu_Nick
			from
				v_TimetableCount ttc with (nolock)
				left join v_TimetableObject tto with (nolock) on ttc.TimetableObject_id = tto.TimetableObject_id
				left join v_LpuSection lc with (nolock) on ttc.LpuSection_id = lc.LpuSection_id
				left join v_Lpu lcl with (nolock) on lc.Lpu_id = lcl.Lpu_id
				left join v_MedStaffFact msf with (nolock) on ttc.MedStaffFact_id = msf.MedStaffFact_id
				left join v_MedPersonal mp with (nolock) on ttc.MedPersonal_id = mp.MedPersonal_id AND msf.Lpu_id = mp.Lpu_id
				left join v_LpuSection lcm with (nolock) on msf.LpuSection_id = lcm.LpuSection_id
				left join v_Lpu lcml with (nolock) on msf.Lpu_id = lcml.Lpu_id
			WHERE
				ttc.pmUser_insID = :pmUser_id
			ORDER BY
				ttc.TimetableCount_Count DESC
		";
		$res = $this->db->query( $sql,
			$params );
		if ( is_object( $res ) ) {
			return $res->result( 'array' );
		} else {
			return false;
		}
	}

	/**
	 * Ведем счетчик фактического использования расписаний (фактом использования считается запись на бирку)
	 * Входящие данные: TimetableObject_id, MedStaffFact_id, MedPersonal_id, LpuSection_id, Server_id, pmUser_id
	 * На выходе идентификатор счетчика или ошибка
	 * @author       Alexander Permyakov
	 */
	function countApply( $data ) {
		$filter = "TimetableObject_id = :TimetableObject_id ";
		$params['TimetableObject_id'] = $data['TimetableObject_id'];
		$params['Server_id'] = $data['Server_id'];
		$params['pmUser_id'] = $data['pmUser_id'];
		$params['MedStaffFact_id'] = NULL;
		$params['MedPersonal_id'] = NULL;
		$params['LpuSection_id'] = NULL;
		if ( 1 == $data['TimetableObject_id'] ) {
			// Принадлежность расписания конкретному врачу полки идентифицируем по MedStaffFact_id и MedPersonal_id
			$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
			$filter .= " AND MedStaffFact_id = :MedStaffFact_id AND MedPersonal_id = :MedPersonal_id";
		} else {
			// Принадлежность расписания конкретному отделению парки или стаца идентифицируем по LpuSection_id
			$params['LpuSection_id'] = $data['LpuSection_id'];
			$filter .= " AND LpuSection_id = :LpuSection_id";
		}
		$sql = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = (SELECT top 1 TimetableCount_id FROM v_TimetableCount with (nolock)
			WHERE {$filter} AND pmUser_insID = :pmUser_id);

			if isnull(@Res, 0) = 0
			begin

				exec p_TimetableCount_ins
					@Server_id = :Server_id,
					@TimetableCount_id = @Res output,
					@TimetableCount_Count = 1,
					@TimetableObject_id = :TimetableObject_id,
					@MedStaffFact_id = :MedStaffFact_id,
					@MedPersonal_id = :MedPersonal_id,
					@LpuSection_id = :LpuSection_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;

				select @Res as TimetableCount_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			end
			else
			begin
				declare
					@CountLoad bigint;
				set @CountLoad = 1 + (SELECT sum(TimetableCount_Count) FROM v_TimetableCount  with (nolock)
				WHERE {$filter} AND pmUser_insID = :pmUser_id);

				exec p_TimetableCount_upd
					@Server_id = :Server_id,
					@TimetableCount_id = @Res output,
					@TimetableCount_Count = @CountLoad,
					@TimetableObject_id = :TimetableObject_id,
					@MedStaffFact_id = :MedStaffFact_id,
					@MedPersonal_id = :MedPersonal_id,
					@LpuSection_id = :LpuSection_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;

				select @Res as TimetableCount_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			end
			";
		$res = $this->db->query( $sql,
			$params );
		if ( is_object( $res ) ) {
			return $res->result( 'array' );
		} else {
			return false;
		}
	}

	/**
	 * проверка - записан ли такой пациент на сегодня
	 */
	function checkPersonByToday( $data ) {
		$add_where = 'AND ttg.LpuSection_id = :LpuSection_id ';
		if ( $data['object'] == 'TimetableGraf' ) {
			$add_where = 'AND ttg.MedStaffFact_id = :MedStaffFact_id ';
		}
		$selectPersonData = "rtrim(p.Person_Surname) + ' ' + isnull(rtrim(p.Person_Firname),'') + ' ' + isnull(rtrim(p.Person_Secname),'') as Person_FIO,
				convert(varchar(10), p.Person_BirthDay, 104) as Person_BirthDay,
				dbo.Age2(p.Person_BirthDay, {$data['object']}_updDT) as Person_Age";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = p.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null rtrim(p.Person_Surname) + ' ' + isnull(rtrim(p.Person_Firname),'') + ' ' + isnull(rtrim(p.Person_Secname),'') else rtrim(peh.PersonEncrypHIV_Encryp) end as Person_FIO,
					case when peh.PersonEncrypHIV_Encryp is null then convert(varchar(10), p.Person_BirthDay, 104) else null end as Person_BirthDay,
					case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Secname) else null end as Person_Age";
		}
		$sql = "
			SELECT top 1
				ttg.{$data['object']}_id,
				convert(varchar(5), ttg.{$data['object']}_begTime, 108) as TimetableGraf_begTime,
				{$selectPersonData}
			FROM
				v_{$data['object']} ttg with (nolock)
				left join v_PersonState_all p with (nolock) on p.Person_id = ttg.Person_id
				{$joinPersonEncrypHIV}
			WHERE
				ttg.Person_id = :Person_id
				AND convert(varchar(10),dbo.tzGetDate(),104) = convert(varchar(10),ttg.{$data['object']}_begTime,104)
				{$add_where}
		";
		$res = $this->db->query( $sql,
			array('Person_id' => $data['Person_id'], 'LpuSection_id' => $data['LpuSection_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id']) );
		if ( is_object( $res ) ) {
			return $res->result( 'array' );
		} else {
			return false;
		}
	}

	/**
	 * проверка 
	 */
	function checkPersonByFuture( $data ) {
		$add_where = 'AND ttg.LpuSection_id = :LpuSection_id ';
		if ( $data['object'] == 'TimetableGraf' ) {
			$add_where = 'AND ttg.MedStaffFact_id = :MedStaffFact_id ';
		}
		$selectPersonData = "rtrim(p.Person_Surname) + ' ' + isnull(rtrim(p.Person_Firname),'') + ' ' + isnull(rtrim(p.Person_Secname),'') as Person_FIO,
				convert(varchar(10), p.Person_BirthDay, 104) as Person_BirthDay,
				dbo.Age2(p.Person_BirthDay, {$data['object']}_updDT) as Person_Age";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = p.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null rtrim(p.Person_Surname) + ' ' + isnull(rtrim(p.Person_Firname),'') + ' ' + isnull(rtrim(p.Person_Secname),'') else rtrim(peh.PersonEncrypHIV_Encryp) end as Person_FIO,
					case when peh.PersonEncrypHIV_Encryp is null then convert(varchar(10), p.Person_BirthDay, 104) else null end as Person_BirthDay,
					case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Secname) else null end as Person_Age";
		}
		$sql = "
			SELECT top 1
				ttg.{$data['object']}_id,
				convert(varchar(5), ttg.{$data['object']}_begTime, 108) as TimetableGraf_begTime,
				{$selectPersonData}
			FROM
				v_{$data['object']} ttg with (nolock)
				left join v_PersonState_all p with (nolock) on p.Person_id = ttg.Person_id
				{$joinPersonEncrypHIV}
			WHERE
				ttg.Person_id = :Person_id
				AND  convert(varchar(10),ttg.{$data['object']}_begTime,104)>=convert(varchar(10),dbo.tzGetDate(),104) 
				{$add_where}
		";
		$res = $this->db->query( $sql,
			array('Person_id' => $data['Person_id'], 'LpuSection_id' => $data['LpuSection_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id']) );
		if ( is_object( $res ) ) {
			return $res->result( 'array' );
		} else {
			return false;
		}
	}
	
	/**
	 * Получение расписания для поликлиники в АРМе врача
	 */
	function GetDataPolka( $data, $OnlyPlan = false ) {

		if ( empty( $data['begDate'] ) ) {
			$begDay_id = TimeToDay( mktime( 0,
					0,
					0,
					date( "m" ),
					date( "d" ),
					date( "Y" ) ) );
			$endDay_id = TimeToDay( mktime( 0,
					0,
					0,
					date( "m" ),
					date( "d" ) + 15,
					date( "Y" ) ) );
		} else {
			$begDay_id = TimeToDay( strtotime( $data['begDate'] ) );
			$endDay_id = TimeToDay( strtotime( $data['endDate'] ) );
		}

		$filter = "(1 = 1)";
		$params = array();

		$filter .= " and TimetableGraf_Day between :begDay_id and :endDay_id";
		$params['begDay_id'] = $begDay_id;
		$params['endDay_id'] = $endDay_id;
		$params['Lpu_id'] = $data['Lpu_id'];
		if ( empty( $data['MedPersonal_id'] ) ) {
			$data['MedPersonal_id'] = $data['session']['medpersonal_id'];
		}

		$params['MedPersonal_id'] = $data['MedPersonal_id'];
		if ( empty( $data['MedStaffFact_id'] ) ) {
			$params['MedStaffFact_id'] = isset( $data['session']['CurMedStaffFact_id'] ) ? $data['session']['CurMedStaffFact_id'] : $data['session']['MedStaffFact'][0];
		} else {
			$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
		}
		if ( (!isset( $data['session']['medpersonal_id'] )) || (empty( $data['session']['medpersonal_id'] )) ) {
			return false; // Только пользовател врач или админ
		}

		$isSearchByEncryp = false;
		$selectPersonData = "rtrim(rtrim(p.Person_Surname) + ' ' + isnull(rtrim(p.Person_Firname),'') + ' ' + isnull(rtrim(p.Person_Secname),'')) as Person_FIO,
				rtrim(p.Person_Surname) as Person_Surname,
				rtrim(p.Person_Firname) as Person_Firname,
				rtrim(p.Person_Secname) as Person_Secname,
				[dbo].[getPersonPhones](p.Person_id, '<br />') as Person_Phone_all,
				p.Lpu_id,
				RTrim(pcard.PersonCard_Code) as PersonCard_Code,				
				RTrim(l.Lpu_Nick) as Lpu_Nick,
				RTrim(pcard.LpuRegion_Name) as LpuRegion_Name,
				convert(varchar(10), p.Person_BirthDay, 104) as Person_BirthDay,
				dbo.Age2(p.Person_BirthDay, @curDT) as Person_Age,
				null as PersonEncrypHIV_Encryp,";
		if (allowPersonEncrypHIV($data['session'])) {
			$isSearchByEncryp = isSearchByPersonEncrypHIV($data['Person_SurName']);
			$selectPersonData = "case
					when PEH.PersonEncrypHIV_id is not null then isnull(rtrim(PEH.PersonEncrypHIV_Encryp),'')
					else rtrim(rtrim(p.Person_Surname) + ' ' + isnull(rtrim(p.Person_Firname),'') + ' ' + isnull(rtrim(p.Person_Secname),''))
				end as Person_FIO,
				case when PEH.PersonEncrypHIV_id is null then rtrim(p.Person_Surname) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname,
				case when PEH.PersonEncrypHIV_id is null then rtrim(p.Person_Firname) else '' end as Person_Firname,
				case when PEH.PersonEncrypHIV_id is null then rtrim(p.Person_Secname) else '' end as Person_Secname,
				case when PEH.PersonEncrypHIV_id is null then [dbo].[getPersonPhones](p.Person_id, '<br />') else '' end as Person_Phone_all,
				case when PEH.PersonEncrypHIV_id is null then rtrim(p.Lpu_id) else null end as Lpu_id,
				case when PEH.PersonEncrypHIV_id is null then rtrim(pcard.PersonCard_Code) else '' end as PersonCard_Code,
				case when PEH.PersonEncrypHIV_id is null then rtrim(l.Lpu_Nick) else '' end as Lpu_Nick,
				case when PEH.PersonEncrypHIV_id is null then rtrim(pcard.LpuRegion_Name) else '' end as LpuRegion_Name,
				case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), p.Person_BirthDay, 104) else null end as Person_BirthDay,
				case when PEH.PersonEncrypHIV_id is null then dbo.Age2(p.Person_BirthDay, @curDT) else null end as Person_Age,
				rtrim(PEH.PersonEncrypHIV_Encryp) as PersonEncrypHIV_Encryp,";
		}

		$join = array();
		if ( !empty( $data['Person_SurName'] ) ) {
			if (allowPersonEncrypHIV($data['session']) && $isSearchByEncryp) {
				$filter .= " and PEH.PersonEncrypHIV_Encryp like (:Person_SurName+'%')";
				$join['PEH'] = "inner join v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = ttg.Person_id";
			} else {
				$filter .= " and p.Person_SurName like (:Person_SurName+'%')";
				$join['P'] = "inner join v_PersonState P with(nolock) on P.Person_id = ttg.Person_id";
			}
			$params['Person_SurName'] = rtrim( $data['Person_SurName'] );
		}
		
		
		if ( !empty( $data['Person_FirName'] ) ) {
			$filter .= " and p.Person_FirName like (:Person_FirName+'%')";
			$params['Person_FirName'] = rtrim( $data['Person_FirName'] );
			$join['P'] = "inner join v_PersonState P with(nolock) on P.Person_id = ttg.Person_id";
		}
		if ( !empty( $data['Person_SecName'] ) ) {
			$filter .= " and p.Person_SecName like (:Person_SecName+'%')";
			$params['Person_SecName'] = rtrim( $data['Person_SecName'] );
			$join['P'] = "inner join v_PersonState P with(nolock) on P.Person_id = ttg.Person_id";
		}
		if ( !empty( $data['Person_BirthDay'] ) ) {
			$filter .= " and p.Person_BirthDay = :Person_BirthDay";
			$params['Person_BirthDay'] = $data['Person_BirthDay'];
			$join['P'] = "inner join v_PersonState P with(nolock) on P.Person_id = ttg.Person_id";
		}

		//В зависимости от профиля врача будем показывать соответствующее прикрепление
		$this->load->model( 'LpuRegion_model',
			'LpuRegion_model' );
		$data['MedStaffFact_id'] = $params['MedStaffFact_id'];
		$params['LpuAttachType_id'] = $this->LpuRegion_model->defineLpuAttachTypeId( $data );

		if ( $OnlyPlan ) {
			$filter .= " and TimetableGraf_factTime is null";
		}
		$isPerm = $data['session']['region']['nick'] == 'perm';
		$isBDZ = "CASE
					WHEN pls.Polis_endDate is not null and pls.Polis_endDate <= cast(convert(char(10), @curDT, 112) as datetime) THEN 'orange'
					ELSE CASE
						WHEN p.PersonCloseCause_id = 2 and p.Person_closeDT is not null THEN 'red'
						ELSE CASE
							WHEN p.Server_pid = 0 THEN 'true'
							ELSE 'false'
						END
					END
				END as [Person_IsBDZ],";
		if($isPerm){
			$isBDZ ="case 
				when p.Server_pid = 0 then 
	case when p.Person_IsInErz = 1  then 'blue' 
	else case when pls.Polis_endDate is not null and pls.Polis_endDate <= cast(convert(char(10), @curDT, 112) as datetime) THEN 
		case when p.Person_deadDT is not null then 'red' else 'yellow' end
	else 'true' end end 
	else 'false' end as [Person_IsBDZ],";
		}
		if($this->getRegionNick() == 'kz'){
			$isBDZ ="
				case
					when pers.Person_IsInFOMS = 1 then 'orange'
					when pers.Person_IsInFOMS = 2 then 'true'
					else 'false'
				end as [Person_IsBDZ],
			";
		}


		$needUnion = false;
		if (!empty($data['showLiveQueue']) && !empty($data['ElectronicService_id'])) {
			$needUnion = true;
		}

		if ( $needUnion ) {
			$params['ElectronicService_id'] = $data['ElectronicService_id'];
		}


		if (empty($data['MedStaffFactFilterType_id'])) {
			$data['MedStaffFactFilterType_id'] = 3; // Все
		}

		// получаем врачей по замещению
		$msfArray = array();
		
		// формируем фильтр по дате в зависимости от данных // для задачи #133626
		$filterdate = "";
		$filterdate .= (!empty($data['begDate']))?"and MedStaffFactReplace_BegDate <= :begDate ":"and MedStaffFactReplace_BegDate <= @curDate ";
		$filterdate .= (!empty($data['endDate']))?"and MedStaffFactReplace_EndDate >= :endDate ":"and MedStaffFactReplace_EndDate <= @curDate ";
		$sql = "
			declare @curDate date = dbo.tzGetDate();
			select distinct
				MedStaffFact_id
			from
				v_MedStaffFactReplace with (nolock)
			where
				MedStaffFact_rid = :MedStaffFact_id
				".$filterdate."
		";
		
		$resp_msfr = $this->queryResult($sql, array(
			'MedStaffFact_id' => $params['MedStaffFact_id'],
			'begDate' => !empty($data['begDate'])?$data['begDate']:null,
			'endDate' => !empty($data['endDate'])?$data['endDate']:null
		));
		if (!empty($resp_msfr)) {
			foreach($resp_msfr as $one_msfr) {
				$msfArray[] = $one_msfr['MedStaffFact_id'];
			}
		}

		switch($data['MedStaffFactFilterType_id']) {
			case 1:
				$filterMSF = "and MSF.MedStaffFact_id = :MedStaffFact_id";
				break;
			case 2:
				if (!empty($msfArray)) {
					$filterMSF = "and MSF.MedStaffFact_id IN ('" . implode("','", $msfArray) . "')"; // врачи по замещению
				} else {
					$filterMSF = "and 1=0"; // нет врачей по замещению
				}
				break;
			default:
				$msfArray[] = $params['MedStaffFact_id'];
				$filterMSF = "and MSF.MedStaffFact_id IN ('" . implode("','", $msfArray) . "')"; // свой + врачи по замещению
				break;
		}

		$join_sql = "";
		if (!empty($join)) {
			$join_sql = implode(" ", $join);
		}

		$presql = '';
		if ($needUnion) {
			$presql = '
				set nocount on;
				select distinct
					mseq.MedStaffFact_id
				into #tmp
				from
					v_MedServiceElectronicQueue mseq with (nolock)
					inner join v_ElectronicService es with (nolock) on es.ElectronicService_id = mseq.ElectronicService_id
					inner join v_ElectronicQueueInfo eqi with (nolock) on eqi.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
					inner join v_ElectronicTreatmentLink etl with (nolock) on etl.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id
				where
					etl.ElectronicTreatment_id in (
						select
							etlIn.ElectronicTreatment_id
						from v_ElectronicTreatmentLink etlIn with (nolock)
						inner join v_ElectronicQueueInfo eqiIn with (nolock) on eqiIn.ElectronicQueueInfo_id = etlIn.ElectronicQueueInfo_id
						inner join v_ElectronicService esIn2 with (nolock) on esIn2.ElectronicQueueInfo_id = eqiIn.ElectronicQueueInfo_id
						where esIn2.ElectronicService_id = :ElectronicService_id
					);
				set nocount off;
			';
		}

		$sql = "
			{$presql}

			SELECT
				ttg.TimetableGraf_id,
				ttg.MedStaffFact_id,
				1 as liveQueueSort,
				ttg.TimetableGraf_Day,
				ttg.TimetableType_id,
				ttg.TimeTableGraf_countRec,
				ttg.TimeTableGraf_PersRecLim,
				case
					when ttg.Person_id is not null then IsNull(ttt.TimetableType_SysNick,'busy')
					else IsNull(ttt.TimetableType_SysNick,'free') end as TimetableType_SysNick,
				IsNull(ttt.TimetableType_Name,'') as TimetableType_Name,
				MSF.LpuSection_id,
				ttg.Person_id,
				case when exists(
					select * 
					from v_PersonQuarantine PQ with(nolock)
					where PQ.Person_id = ttg.Person_id
					and PQ.PersonQuarantine_endDT is null
				) then 'true' else 'false' end as PersonQuarantine_IsOn,
				case
					when TimetableGraf_begTime is not null then convert(varchar,TimetableGraf_begTime,104)
					when TimetableGraf_factTime is not null then convert(varchar,TimetableGraf_factTime,104)
					else convert(varchar,TimetableGraf_insDT,104)
				end as TimetableGraf_Date,
				isnull(convert(varchar(5), TimetableGraf_begTime, 108),'б/з') as TimetableGraf_begTime,
				convert(varchar(5), ttg.TimetableGraf_factTime, 108) as TimetableGraf_factTime,
				case when ttg.Person_id is not null then convert(varchar(10), TimetableGraf_updDT, 104) + ' ' + convert(varchar(5), TimetableGraf_updDT, 108) end as TimetableGraf_updDT,
				case when ttg.Person_id is not null then
					case
						when pu.pmUser_id is not null then rtrim(pu.pmUser_Name)
						else 'Запись через интернет'
					end
				end as pmUser_Name,
				ttg.pmUser_updId,
				ttg.pmUser_insId,
				case when ed.EvnDirection_isAuto != 2 then 'true' else 'false' end as IsEvnDirection,
				ed.MedPersonal_id,
				ed.MedPersonal_did,
				ed.EvnQueue_id,
				ed.EvnStatus_id,
				MSF.Person_Fin as MSF_Person_Fin,
				ed.EvnDirection_Num,
				RTRIM(LSP.LpuSectionProfile_Name) as LpuSectionProfile_Name,
				ed.EvnDirection_id,
				ed.ARMType_id,
				et.ElectronicTalon_Num,
				ets.ElectronicTalonStatus_Name,
				et.ElectronicService_id,
				et.ElectronicTalonStatus_id,
				et.ElectronicTalon_id,
				et.EvnDirection_uid,
				etr.ElectronicService_id as toElectronicService_id,
				etr.ElectronicService_uid as fromElectronicService_id,
				et.ElectronicTreatment_id,
				etre.ElectronicTreatment_Name,
				DATEDIFF(ss, et.ElectronicTalon_insDT, getdate()) as ElectronicTalon_TimeHasPassed,
				PAC.PersonAmbulatCard_id,
				PAC.PersonAmbulatCard_Num,
				ACR.AmbulatCardRequest_id,
				ACR.AmbulatCardRequestStatus_id,
				ambulatCard.MedStaffFact_id as locationMedStaffFact_id, --у кого находится карта
				visitPerson.TimetableGrafRecList_id
			FROM
				v_TimetableGraf_lite ttg with (nolock)
				{$join_sql}
				left join v_ElectronicTalon et (nolock) on (et.EvnDirection_uid = ttg.EvnDirection_id) OR (et.EvnDirection_uid IS NULL AND et.EvnDirection_id = ttg.EvnDirection_id)
				left join v_MedServiceElectronicQueue mseq (nolock) on mseq.ElectronicService_id = et.ElectronicService_id
				left join v_ElectronicTalonStatus ets (nolock) on ets.ElectronicTalonStatus_id = et.ElectronicTalonStatus_id
				left join v_ElectronicTalonRedirect etr (nolock) on (etr.ElectronicTalon_id = et.ElectronicTalon_id and (etr.EvnDirection_uid = et.EvnDirection_uid or etr.EvnDirection_uid is null))
				left join v_ElectronicTreatment etre (nolock) on etre.ElectronicTreatment_id = et.ElectronicTreatment_id
				left join v_pmUser pu with (nolock) on pu.pmUser_id = ttg.pmUser_updId
				left join v_TimetableType ttt with (nolock) on ttt.TimetableType_id = ttg.TimetableType_id
				left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = ttg.MedStaffFact_id
				left join v_MedStaffFact ETMSF with (nolock) on ETMSF.MedStaffFact_id = mseq.MedStaffFact_id
				left join v_EvnDirection_all ed with (nolock) on ed.EvnDirection_id = ttg.EvnDirection_id and ed.DirFailType_id is null and ED.EvnStatus_id not in (12,13)
				left join LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_id
				left join v_PersonAmbulatCard PAC with(nolock) on PAC.PersonAmbulatCard_id = ttg.PersonAmbulatCard_id AND ttg.Person_id = PAC.Person_id
				left join v_AmbulatCardRequest ACR with(nolock) on ACR.TimeTableGraf_id = ttg.TimeTableGraf_id AND ACR.PersonAmbulatCard_id = PAC.PersonAmbulatCard_id
				outer apply(
					select top 1 vPACL.PersonAmbulatCardLocat_id, vPACL.MedStaffFact_id
					from 
						v_PersonAmbulatCardLocat vPACL with(nolock)
					where PersonAmbulatCard_id = PAC.PersonAmbulatCard_id
					order by PersonAmbulatCardLocat_begDate desc
				) ambulatCard
				outer apply(
					select top 1 ttgrl.TimetableGrafRecList_id
					from 
						TimetableGrafRecList ttgrl with(nolock)
					where ttgrl.TimetableGraf_id = ttg.TimetableGraf_id
					and ttgrl.TimetableGrafRecList_isGroupFact = 2
				) visitPerson
			WHERE
				{$filter}
				{$filterMSF}
				and (ttg.TimetableType_id != 12 or ttg.Person_id is not null)

			" . ($needUnion ? "
			UNION ALL

			SELECT
				ttg.TimetableGraf_id,
				ttg.MedStaffFact_id,
				2 as liveQueueSort,
				ttg.TimetableGraf_Day,
				ttg.TimetableType_id,
				ttg.TimeTableGraf_countRec,
				ttg.TimeTableGraf_PersRecLim,
				case
					when ttg.Person_id is not null then IsNull(ttt.TimetableType_SysNick,'busy')
					else IsNull(ttt.TimetableType_SysNick,'free') end as TimetableType_SysNick,
				IsNull(ttt.TimetableType_Name,'') as TimetableType_Name,
				MSF.LpuSection_id,
				ttg.Person_id,
				case when exists(
					select * 
					from v_PersonQuarantine PQ with(nolock)
					where PQ.Person_id = ttg.Person_id
					and PQ.PersonQuarantine_endDT is null
				) then 'true' else 'false' end as PersonQuarantine_IsOn,
				case
					when TimetableGraf_begTime is not null then convert(varchar,TimetableGraf_begTime,104)
					when TimetableGraf_factTime is not null then convert(varchar,TimetableGraf_factTime,104)
					else convert(varchar,TimetableGraf_insDT,104)
				end as TimetableGraf_Date,
				isnull(convert(varchar(5), TimetableGraf_begTime, 108),'б/з') as TimetableGraf_begTime,
				convert(varchar(5), ttg.TimetableGraf_factTime, 108) as TimetableGraf_factTime,
				case when ttg.Person_id is not null then convert(varchar(10), TimetableGraf_updDT, 104) + ' ' + convert(varchar(5), TimetableGraf_updDT, 108) end as TimetableGraf_updDT,
				case when ttg.Person_id is not null then
					case
						when pu.pmUser_id is not null then rtrim(pu.pmUser_Name)
						else 'Запись через интернет'
					end
				end as pmUser_Name,
				ttg.pmUser_updId,
				ttg.pmUser_insId,
				case when ed.EvnDirection_isAuto != 2 then 'true' else 'false' end as IsEvnDirection,
				ed.MedPersonal_id,
				ed.MedPersonal_did,
				ed.EvnQueue_id,
				ed.EvnStatus_id,
				MSF.Person_Fin as MSF_Person_Fin,
				ed.EvnDirection_Num,
				RTRIM(LSP.LpuSectionProfile_Name) as LpuSectionProfile_Name,
				ed.EvnDirection_id,
				ed.ARMType_id,
				et.ElectronicTalon_Num,
				ets.ElectronicTalonStatus_Name,
				et.ElectronicService_id,
				et.ElectronicTalonStatus_id,
				et.ElectronicTalon_id,
				et.EvnDirection_uid,
				etr.ElectronicService_id as toElectronicService_id,
				etr.ElectronicService_uid as fromElectronicService_id,
				et.ElectronicTreatment_id,
				etre.ElectronicTreatment_Name,
				DATEDIFF(ss, et.ElectronicTalon_insDT, getdate()) as ElectronicTalon_TimeHasPassed,
				PAC.PersonAmbulatCard_id,
				PAC.PersonAmbulatCard_Num,
				ACR.AmbulatCardRequest_id,
				ACR.AmbulatCardRequestStatus_id,
				ambulatCard.MedStaffFact_id as locationMedStaffFact_id, --у кого находится карта
				visitPerson.TimetableGrafRecList_id
			FROM
				#tmp as preMsf
				inner join v_TimetableGraf_lite ttg with (nolock) on preMsf.MedStaffFact_id = ttg.MedStaffFact_id
				{$join_sql}
				left join v_ElectronicTalon et (nolock) on et.EvnDirection_id = ttg.EvnDirection_id
				left join v_ElectronicTalonStatus ets (nolock) on ets.ElectronicTalonStatus_id = et.ElectronicTalonStatus_id
				left join v_ElectronicTalonRedirect etr (nolock) on (etr.ElectronicTalon_id = et.ElectronicTalon_id and (etr.EvnDirection_uid = et.EvnDirection_uid or etr.EvnDirection_uid is null))
				left join v_ElectronicTreatment etre (nolock) on etre.ElectronicTreatment_id = et.ElectronicTreatment_id
				left join v_pmUser pu with (nolock) on pu.pmUser_id = ttg.pmUser_updId
				left join v_TimetableType ttt with (nolock) on ttt.TimetableType_id = ttg.TimetableType_id
				left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = ttg.MedStaffFact_id
				left join v_EvnDirection_all ed with (nolock) on ed.EvnDirection_id = ttg.EvnDirection_id and ed.DirFailType_id is null and ED.EvnStatus_id not in (12,13)
				left join LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_id
				left join v_PersonAmbulatCard PAC with(nolock) on PAC.PersonAmbulatCard_id = ttg.PersonAmbulatCard_id AND ttg.Person_id = PAC.Person_id
				left join v_AmbulatCardRequest ACR with(nolock) on ACR.TimeTableGraf_id = ttg.TimeTableGraf_id AND ACR.PersonAmbulatCard_id = PAC.PersonAmbulatCard_id
				outer apply(
					select top 1 vPACL.PersonAmbulatCardLocat_id, vPACL.MedStaffFact_id
					from 
						v_PersonAmbulatCardLocat vPACL with(nolock)
					where PersonAmbulatCard_id = PAC.PersonAmbulatCard_id
					order by PersonAmbulatCardLocat_begDate desc
				) ambulatCard
				outer apply(
					select top 1 ttgrl.TimetableGrafRecList_id
					from 
						TimetableGrafRecList ttgrl with(nolock)
					where ttgrl.TimetableGraf_id = ttg.TimetableGraf_id
					and ttgrl.TimetableGrafRecList_isGroupFact = 2
				) visitPerson
			WHERE
				{$filter}
				and MSF.MedStaffFact_id != :MedStaffFact_id
				and ttg.TimetableType_id = 12
				and ttg.Person_id is not null
			" : "") . "

			ORDER BY
				 TimetableGraf_Day
				" . ($needUnion ? ", liveQueueSort" : "") . "
				,TimetableGraf_Date
		";

		//echo getDebugSql($sql, $params); exit;
		$res = $this->db->query($sql, $params, true);

		$FER_PERSON_ID = $this->config->item('FER_PERSON_ID');

		if ( is_object( $res ) ) {

			$resp = $res->result( 'array' );

			$arrayFromPersonState = array();
			foreach($resp as &$respone) {
				if (!empty($respone['Person_id'])) {
					$arrayFromPersonState[] = $respone['Person_id'];
				}
			}

			$psData = array();
			if (!empty($arrayFromPersonState)) {
				// делаем запрос в PersonState
				$joinPEH = "";

				if ( allowPersonEncrypHIV($data['session']) ) {
					$joinPEH = ($isSearchByEncryp ? "inner" : "left") . " join v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = p.Person_id";
				}

				$query = "
					declare @curDT datetime = dbo.tzGetDate();
					
					select
						p.Person_id as Person_id,
						p.PersonEvn_id as PersonEvn_id,
						p.Server_id as Server_id,
						{$selectPersonData}
						{$isBDZ}
						ambulatCard.PersonAmbulatCard_id,
						ambulatCard.PersonAmbulatCard_Num,
						ambulatCard.MedStaffFact_id as locationMedStaffFact_id, --у кого находится карта
						pers.Person_IsUnknown,
						case when p.Person_IsFedLgot = 1 or p.Person_IsRegLgot = 1 then 'true' else 'false' end as Person_IsLgot,
						CASE WHEN p.Person_IsFedLgot = 1 THEN 'true' ELSE 'false' END as Person_IsFedLgot,
						CASE WHEN p.Person_IsRegLgot = 1 THEN 'true' ELSE 'false' END as Person_IsRegLgot
					from
						v_PersonState_all p with (nolock)
						left join v_Polis pls with (nolock) on pls.Polis_id = p.Polis_id
						left join v_Person pers with (nolock) on pers.Person_id = p.Person_id
						outer apply (select top 1
							pc.Person_id as PersonCard_Person_id,
							pc.Lpu_id,
							pc.LpuRegion_id,
							pc.LpuRegion_Name,
							case when pc.LpuAttachType_id = 1 then pc.PersonCard_Code else null end as PersonCard_Code
						from v_PersonCard pc with (nolock)
						where pc.Person_id = p.Person_id and LpuAttachType_id = :LpuAttachType_id
						order by PersonCard_begDate desc
						) as pcard
						outer apply(
							SELECT TOP 1
								PAC.PersonAmbulatCard_id,
								PAC.PersonAmbulatCard_Num,
								--ACLB.AmbulatCardLpuBuilding_begDate,
								--ACLB.AmbulatCardLpuBuilding_endDate,
								ACLB.LpuBuilding_id,
								PACL.MedStaffFact_id
							FROM v_PersonAmbulatCard PAC with(nolock)
								left join v_PersonAmbulatCardLocat PACL with(nolock) on PAC.PersonAmbulatCard_id = PACL.PersonAmbulatCard_id
								left join v_AmbulatCardLpuBuilding ACLB with(nolock) on ACLB.PersonAmbulatCard_id = PAC.PersonAmbulatCard_id
							WHERE 1=1
								AND PAC.Person_id = p.Person_id
								AND PAC.Lpu_id = :Lpu_id
								AND @curDT BETWEEN  isnull(ACLB.AmbulatCardLpuBuilding_begDate, @curDT) and isnull(ACLB.AmbulatCardLpuBuilding_endDate, @curDT)
							ORDER BY PACL.PersonAmbulatCardLocat_begDate DESC, PAC.PersonAmbulatCard_id DESC
						) as ambulatCard
						left join v_LpuRegion LpuRegion with (nolock) on LpuRegion.LpuRegion_id = pcard.LpuRegion_id
						left outer join v_Lpu l with (nolock) on l.Lpu_id = pcard.Lpu_id
						" . $joinPEH. "
					where
						p.Person_id in ('" . implode("','", $arrayFromPersonState) . "') -- возможно надо поделить на несколько запросов
				";
				$result_ps = $this->db->query($query, array(
					'LpuAttachType_id' => $params['LpuAttachType_id'],
					'Lpu_id' => $data['Lpu_id']
				));
				if (is_object($result_ps)) {
					$resp_ps = $result_ps->result('array');
					foreach($resp_ps as $one_ps) {
						$psData[$one_ps['Person_id']] = $one_ps;
					}
				}
			}

			$arrayFromSlot = array();

			foreach($resp as &$respone) {
				if (!empty($psData[$respone['Person_id']])) {
					$one_ps = $psData[$respone['Person_id']];
					$respone['PersonEvn_id'] = $one_ps['PersonEvn_id'];
					$respone['Server_id'] = $one_ps['Server_id'];
					$respone['Person_IsUnknown'] = $one_ps['Person_IsUnknown'];
					$respone['Person_Surname'] = $one_ps['Person_Surname'];
					$respone['Person_Firname'] = $one_ps['Person_Firname'];
					$respone['Person_Secname'] = $one_ps['Person_Secname'];
					$respone['Person_FIO'] = ($respone['TimetableType_id']==14)?'ГРУППОВОЙ ПРИЁМ':$one_ps['Person_FIO'];
					$respone['Person_BirthDay'] = $one_ps['Person_BirthDay'];
					$respone['Person_Age'] = $one_ps['Person_Age'];
					$respone['Person_Phone_all'] = $one_ps['Person_Phone_all'];
					$respone['PersonCard_Code'] = $one_ps['PersonCard_Code'];
					$respone['locationMedStaffFact_id'] = (!empty($respone['PersonAmbulatCard_id'])) ? $respone['locationMedStaffFact_id'] : $one_ps['locationMedStaffFact_id'];
					$respone['PersonAmbulatCard_id'] = (!empty($respone['PersonAmbulatCard_id'])) ? $respone['PersonAmbulatCard_id'] : $one_ps['PersonAmbulatCard_id'];
					$respone['PersonAmbulatCard_Num'] = (!empty($respone['PersonAmbulatCard_Num'])) ? $respone['PersonAmbulatCard_Num'] : $one_ps['PersonAmbulatCard_Num'];
					$respone['Lpu_id'] = $one_ps['Lpu_id'];
					$respone['Lpu_Nick'] = $one_ps['Lpu_Nick'];
					$respone['LpuRegion_Name'] = $one_ps['LpuRegion_Name'];
					$respone['Person_IsBDZ'] = $one_ps['Person_IsBDZ'];
					$respone['Person_IsFedLgot'] = $one_ps['Person_IsFedLgot'];
					$respone['Person_IsRegLgot'] = $one_ps['Person_IsRegLgot'];
					$respone['PersonEncrypHIV_Encryp'] = $one_ps['PersonEncrypHIV_Encryp'];
				}

				if (empty($respone['PersonEncrypHIV_Encryp']) && !empty($respone['TimetableGraf_id']) && !empty($respone['Person_id']) && !empty($FER_PERSON_ID) && $FER_PERSON_ID == $respone['Person_id']) {
					$arrayFromSlot[] = $respone['TimetableGraf_id'];
				}
			}
			$slotData = array();
			if (!empty($arrayFromSlot)) {
				// делаем запрос в fer.slot
				$query = "
					select
						Slot_id,
						Slot_SurName as Person_Surname,
						Slot_FirName as Person_Firname,
						Slot_SecName as Person_Secname,
						Slot_SurName + ' ' + isnull(Slot_FirName,'') + ' ' + isnull(Slot_SecName,'') as Person_FIO,
						TimetableGraf_id
					from
						fer.v_Slot (nolock)
					where
						TimetableGraf_id in ('" . implode("','", $arrayFromSlot) . "') -- возможно надо поделить на несколько запросов
				";
				$result_fer = $this->db->query($query);
				if (is_object($result_fer)) {
					$resp_fer = $result_fer->result('array');
					foreach($resp_fer as $one_fer) {
						$slotData[$one_fer['TimetableGraf_id']] = $one_fer;
					}
				}
			}

			foreach($resp as &$respone) {
				if (!empty($slotData[$respone['TimetableGraf_id']])) {
					$one_fer = $slotData[$respone['TimetableGraf_id']];
					$respone['Person_Surname'] = $one_fer['Person_Surname'];
					$respone['Person_Firname'] = $one_fer['Person_Firname'];
					$respone['Person_Secname'] = $one_fer['Person_Secname'];
					$respone['Person_FIO'] = ($respone['TimetableType_id']==14)?'ГРУППОВОЙ ПРИЁМ':$one_fer['Person_FIO'];
					$respone['Person_BirthDay'] = '';
					$respone['Person_Age'] = '';
					$respone['Person_Phone_all'] = '';
					$respone['PersonCard_Code'] = '';
					$respone['Lpu_Nick'] = '';
					$respone['LpuRegion_Name'] = '';
					$respone['IsEvnDirection'] = 'false';
					$respone['Person_IsBDZ'] = 'false';
					$respone['Person_IsFedLgot'] = 'false';
					$respone['Person_IsRegLgot'] = 'false';
				}
			}

			return $resp;
		} else {
			return false;
		}
	}

	/**
	 * Получение расписания для стационара
	 */
	function GetDataStac( $data ) {
		if ( empty( $data['begDate'] ) ) {
			$begDay_id = TimeToDay( mktime( 0,
					0,
					0,
					date( "m" ),
					date( "d" ),
					date( "Y" ) ) );
			$endDay_id = TimeToDay( mktime( 0,
					0,
					0,
					date( "m" ),
					date( "d" ) + 15,
					date( "Y" ) ) );
		} else {
			$begDay_id = TimeToDay( strtotime( $data['begDate'] ) );
			$endDay_id = TimeToDay( strtotime( $data['endDate'] ) );
		}

		$filter = "t.TimetableType_id != 6";
		$params = array();

		if ( !empty( $data['LpuSection_id'] ) ) {
			$filter .= " and t.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		} else {
			return false;
		}

		$filter .= " and t.TimetableStac_Day between :begDay_id and :endDay_id";
		$params['begDay_id'] = $begDay_id;
		$params['endDay_id'] = $endDay_id;
		$params['Lpu_id'] = $data['Lpu_id'];

		$selectPersonData = "
				p.Person_Phone,
				rtrim(rtrim(p.Person_Surname) + ' ' + isnull(rtrim(p.Person_Firname),'') + ' ' + isnull(rtrim(p.Person_Secname),''))
				as Person_FIO,
				case when t.Person_id is not null then convert(varchar(10), p.Person_BirthDay, 104) end as Person_BirthDay,
				case when t.Person_id is not null then dbo.Age2(p.Person_BirthDay, TimetableStac_updDT) end as Person_Age,
				p.Lpu_id,
				RTrim(p.Lpu_Nick) as Lpu_Nick,
				RTrim(pcard.LpuRegion_Name) as LpuRegion_Name,
				rtrim(p.Person_Firname) as Person_Firname,
				rtrim(p.Person_Surname) as Person_Surname,
				rtrim(p.Person_Secname) as Person_Secname,";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = p.Person_id";
			$selectPersonData = "
				case when peh.PersonEncrypHIV_Encryp is null then p.Person_Phone end as Person_Phone,
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(rtrim(p.Person_Surname) + ' ' + isnull(rtrim(p.Person_Firname),'') + ' ' + isnull(rtrim(p.Person_Secname),'')) else rtrim(peh.PersonEncrypHIV_Encryp) end as Person_FIO,
				case when peh.PersonEncrypHIV_Encryp is null and t.Person_id is not null then convert(varchar(10), p.Person_BirthDay, 104) end as Person_BirthDay,
				case when peh.PersonEncrypHIV_Encryp is null and t.Person_id is not null then dbo.Age2(p.Person_BirthDay, TimetableStac_updDT) end as Person_Age,
				case when peh.PersonEncrypHIV_Encryp is null then p.Lpu_id end as Lpu_id,
				case when peh.PersonEncrypHIV_Encryp is null then RTrim(p.Lpu_Nick) else '' end as Lpu_Nick,
				case when peh.PersonEncrypHIV_Encryp is null then RTrim(pcard.LpuRegion_Name) else '' end as LpuRegion_Name,
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Surname) else rtrim(peh.PersonEncrypHIV_Encryp) end as Person_Surname,
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Firname) else '' end as Person_Firname,
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Secname) else '' end as Person_Secname,";
		}
		
		$sql = "
			select
				t.pmUser_updId,
				t.pmUser_insId,
				t.TimetableStac_id,
				t.TimetableStac_Day,
				t.LpuSectionBedType_id,
				t.Person_id,
				{$selectPersonData}
				t.TimetableType_id,
				case
					when t.Person_id is not null then IsNull(ttt.TimetableType_SysNick,'busy')
					else IsNull(ttt.TimetableType_SysNick,'free') end as TimetableType_SysNick,
				IsNull(ttt.TimetableType_Name,'') as TimetableType_Name,

				lsbt.LpuSectionBedType_Name,
				convert(varchar,cast(TimetableStac_setDate as datetime),104) as TimetableStac_Date,

				case when p.Person_IsFedLgot = 1 or p.Person_IsRegLgot = 1 then 'true' else 'false' end as Person_IsLgot,
				--case when p.Lpu_id = MSF.Lpu_id then 'true' else 'false' end as Person_IsPrik,
				case when p.Person_IsBDZ = 1 then 'true' else 'false' end as Person_IsBDZ,

				case when t.Person_id is not null then convert(varchar(10), TimetableStac_updDT, 104) + ' ' + convert(varchar(5), TimetableStac_updDT, 108) end as TimetableStac_updDT,

				case when t.Person_id is not null then
					case
						when pu.pmUser_id is not null then rtrim(pu.pmUser_Name)
						else 'Запись через интернет'
					end
				end as pmUser_Name,
				ls.LpuSectionHospType_id,
				EvnQueue_insDT
			from v_TimetableStac_lite t with (nolock)
			left outer join LpuSection ls with (nolock) on ls.LpuSection_id = t.LpuSection_id
			left join v_PersonState_all p with (nolock) on p.Person_id = t.Person_id
			left join v_pmUser pu with (nolock) on pu.pmUser_id = t.pmUser_updId
			outer apply (select top 1
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id,
						pc.LpuRegion_id,
						pc.LpuRegion_Name
					from v_PersonCard pc with (nolock)
					where pc.Person_id = p.Person_id and LpuAttachType_id = 1
					order by PersonCard_begDate desc
					) as pcard
			left join v_LpuRegion LpuRegion with (nolock) on LpuRegion.LpuRegion_id = pcard.LpuRegion_id
			left join v_TimetableType ttt with (nolock) on ttt.TimetableType_id = t.TimetableType_id
			--left outer join v_Lpu lpu with(nolock) on lpu.Lpu_id = p.Lpu_id
			left join v_EvnQueue q with (nolock) on t.TimetableStac_id = q.TimetableStac_id and t.Person_id = q.Person_id
			--left outer join v_Direction_ER d with (nolock) on t.TimetableStac_id = d.TimetableStac_id and d.DirFailType_id is null
			left join LpuSectionBedType lsbt with (nolock) on lsbt.LpuSectionBedType_id = t.LpuSectionBedType_id
			{$joinPersonEncrypHIV}
			where
				{$filter}
			order by TimetableStac_Day, LpuSectionBedType_id
		";
		/*
		  echo getDebugSql($sql, $params);
		  exit;
		 */
		$res = $this->db->query( $sql,
			$params );

		if ( is_object( $res ) ) {

			return $res->result( 'array' );
		} else {
			return false;
		}
	}

	/**
	 * Получение расписания на заданную дату
	 */
	function getListByDay( $data, $OnlyPlan = false ) {

		switch ( $data['LpuUnitType_SysNick'] ) {
			case 'polka':
				return $this->GetDataPolka( $data,
						$OnlyPlan );
				break;
			case 'stac': case 'dstac': case 'hstac': case 'pstac':
				return $this->GetDataStac( $data,
						$OnlyPlan );
				break;
		}
		return false;
	}

	/**
	 * Получение данных по подразделению и профилю врача
	 */
	function getDataMedStafFact( $data ) {
		// За текущую дату на этого человека у этого врача / отделения
		$filter = 'MedStaffFact_id = :MedStaffFact_id ';
		$params['MedStaffFact_id'] = $data['MedStaffFact_id'];

		$sql = "
			SELECT top 1
				ls.LpuSectionProfile_id,
				ls.LpuUnit_id
			FROM
				v_MedStaffFact msf with(nolock)
				left join v_LpuSection ls with(nolock) on msf.LpuSection_id = ls.LpuSection_id
			WHERE
				{$filter}
		";

		$res = $this->db->query( $sql,
			$params );
		if ( is_object( $res ) ) {
			$res = $res->result( 'array' );
		}
		if ( count( $res ) > 0 ) {
			return array('LpuSectionProfile_id' => $res[0]['LpuSectionProfile_id'],
				'LpuUnit_id' => $res[0]['LpuUnit_id']);
		} else {
			return false;
		}
	}

	/* сейчас не используется, можно будет удалить если не будет нужно.
	  function getTimetableGrafId($data) {
	  // На дату посещения на этого человека у этого врача / отделения
	  $params = array(
	  'Person_id' => $data['Person_id'],
	  'vizit_date' => $data['date'],
	  );
	  if ($data['object']=='TimetableGraf')
	  {
	  $filter = 'MedStaffFact_id = :MedStaffFact_id ';
	  $params['MedStaffFact_id'] = $data['MedStaffFact_id'];
	  }
	  if ($data['object']=='TimetablePar')
	  {
	  $filter = 'LpuSection_id = :LpuSection_id ';
	  $params['LpuSection_id'] = $data['LpuSection_id'];
	  }
	  if ($data['object']=='TimetableStac')
	  {
	  $filter = 'LpuSection_id = :LpuSection_id ';
	  $params['LpuSection_id'] = $data['LpuSection_id'];
	  }
	  $sql = "
	  SELECT {$data['object']}_id
	  FROM {$data['object']}
	  WHERE
	  convert(varchar(10),{$data['object']}_begTime,121)= :vizit_date and
	  Person_id = :Person_id and
	  {$filter}
	  ";

	  $res = $this->db->query(
	  $sql,
	  $params
	  );
	  if ( is_object($res) )
	  $res = $res->result('array');
	  if (count($res)>0)
	  {
	  return $res[0][$data['object'].'_id'];
	  }
	  else
	  {
	  return false;
	  }
	  }
	 */

	/**
	 * Создание экстренного посещения пациентом врача
	 */
	function Create( $data ) {
		$this->load->helper( 'Reg' );
		$Timetable_Day = empty( $data['Timetable_Day'] ) ? TimeToDay( strtotime( $data['date'] ) ) : $data['Timetable_Day'];
		if (!isset($data['EmergencyData_id'])) {
			$data['EmergencyData_id'] = null;
		}
		$params = array(
			//'LpuSection_id' => $data['LpuSection_id'],
			'Person_id' => $data['Person_id'],
			'Timetable_Day' => $Timetable_Day,
			'pmUser_id' => $data['pmUser_id']
		);
		$add_param = '';
		$add_declare = '';
		if ( $data['object'] == 'TimetableGraf' ) {
			$add_declare = '@fact_dt datetime = cast(:TimetableGraf_factTime as datetime),';
			$add_param = '@MedStaffFact_id = :MedStaffFact_id,
				@TimetableGraf_factTime = @fact_dt,';
			$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
			$params['TimetableGraf_factTime'] = $data['TimetableGraf_factTime'];
		} else if ( $data['object'] == 'TimetableStac' ) { // Создание экстренной койки
			$add_declare = '@gd datetime =  cast(dbo.tzGetDate() as date),';
			$add_param = '
				@LpuSection_id = :LpuSection_id,
				@LpuSectionBedType_id = 3,
				@TimetableStac_setDate = @gd,
				@TimetableType_id = 6,
				@TimetableStac_EmStatus = null,
				@EmergencyData_id = :EmergencyData_id,
				@Evn_id = null,';
			$params['LpuSection_id'] = $data['LpuSection_id'];
			$params['EmergencyData_id'] = $data['EmergencyData_id'];
		}
		
		if ($data['object'] == 'TimetableGraf' || $data['object'] == 'TimetableStac' || $data['object'] == 'TimeTablePar' || $data['object'] == 'TimeTableResource') {
			$add_param .= '
				@RecMethodType_id = 1,';
		}

		$sql = "
			declare
				@Res bigint,
				{$add_declare}
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = null;

			exec p_{$data['object']}_ins
				@{$data['object']}_id = @Res output,
				{$add_param}
				@Person_id = :Person_id,
				@{$data['object']}_Day = :Timetable_Day,
				@RecClass_id = 3,
				@pmUser_id = :pmUser_id
			select @Res as {$data['object']}_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		// die(getDebugSql($sql, $params));

		$res = $this->db->query( $sql,
			$params );
		if ( is_object( $res ) ) {
			$resp = $res->result( 'array' );
			if ( !empty( $resp[0][$data['object'] . '_id'] ) ) {
				// отправка STOMP-сообщения
				sendFerStompMessage( array(
					'id' => $resp[0][$data['object'] . '_id'],
					'timeTable' => $data['object'],
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
	 * Удаление фактического времени посещения при удалении посещения
	 * Логичнее что этот метод вызывается после успешного удаления посещения, но сейчас при удалении посещения чистится ссылка TimetableGraf_id и этот метод не работает
	 * Вход.данные: $data['pmUser_id'], $data['vizit_object'], $data['EvnVizitPL_id'], $data['EvnVizitPLStom_id']
	 * На выходе массив.
	 */
	function cancelVizitTime( $data ) {
		$process_good = true;
		$TimetableGraf_id = 0;
		$is_recorded = null;
		$EvnVizit_id = 0;
		$vizit_object = $data['vizit_object'];
		//получить данные бирки по этому посещению
		$query = "
			select top 1
				TimetableGraf.TimetableGraf_id,
				TimetableGraf.TimetableGraf_begTime,
				EvnVizit.EvnVizit_id
			from
				v_TimetableGraf_lite TimetableGraf with (nolock)
				inner join {$vizit_object} with (nolock) on {$vizit_object}.{$vizit_object}_id = :{$vizit_object}_id
				inner join EvnVizit with (nolock) on {$vizit_object}.{$vizit_object}_id = EvnVizit.EvnVizit_id
				AND TimetableGraf.TimetableGraf_id = EvnVizit.TimetableGraf_id
		";
		$result1 = $this->db->query( $query,
			array(
			$vizit_object . '_id' => $data[$vizit_object . '_id']
			) );
		if ( is_object( $result1 ) ) {
			$response = $result1->result( 'array' );
			if ( count( $response ) > 0 AND ! empty( $response[0]['TimetableGraf_id'] ) AND ! empty( $response[0]['EvnVizit_id'] ) ) {
				$TimetableGraf_id = $response[0]['TimetableGraf_id'];
				$EvnVizit_id = $response[0]['EvnVizit_id'];
				$is_recorded = empty( $response[0]['TimetableGraf_begTime'] ) ? false : true;
			}
		} else {
			$response = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение данных о записи пациента на посещение поликлиники)'));
			$process_good = false;
		}
		//$response1 = $response;
		// Перед удалением бирки, нужно почистить EvnVizit.TimetableGraf_id (т.к. одна запись - одно посещение)
		if ( $process_good AND $EvnVizit_id > 0 ) {
			$query = "
				UPDATE EvnVizit with (ROWLOCK) set
				TimetableGraf_id = NULL
				where EvnVizit_id = :EvnVizit_id
			";
			$result2 = $this->db->query( $query,
				array(
				'EvnVizit_id' => $EvnVizit_id
				) );
			if ( $result2 == true ) {
				$response = $result2;
			} else {
				$response = array(array('result2' => $result2, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление связи посещения пациентом поликлиники без записи с биркой)'));
				$process_good = false;
			}
			//$response2 = $response;
		}

		// После удаления посещения нужно почистить TimetableGraf_factTime, если человек посещал по записи, чтобы на эту бирку можно было завести другое посещение.
		if ( $process_good AND $is_recorded === true AND $TimetableGraf_id > 0 ) {
			$query = "
				UPDATE TimetableGraf with (ROWLOCK) set
				TimetableGraf_factTime = NULL
				where TimetableGraf_id = :TimetableGraf_id
			";
			$result3 = $this->db->query( $query,
				array(
				'TimetableGraf_id' => $TimetableGraf_id
				) );
			if ( $result3 == true ) {
				$response = $result3;
			} else {
				$response = array(array('result3' => $result3, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (очистка времени фактического посещения)'));
				$process_good = false;
			}
			//$response3 = $response;
		}

		// После удаления посещения удалять бирку, если она создана на человека без записи.
		if ( $process_good AND $is_recorded === false AND $TimetableGraf_id > 0 ) {
			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec p_TimetableGraf_del
					@TimetableGraf_id = :TimetableGraf_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result4 = $this->db->query( $query,
				array(
				'TimetableGraf_id' => $TimetableGraf_id,
				'pmUser_id' => $data['pmUser_id']
				) );
			if ( is_object( $result4 ) ) {
				$response = $result4->result( 'array' );
				if ( !empty( $response[0]['Error_Msg'] ) ) {
					$process_good = false;
				} else {
					// отправка STOMP-сообщения
					sendFerStompMessage( array(
						'id' => $TimetableGraf_id,
						'timeTable' => 'TimetableGraf',
						'action' => 'DelTicket',
						'setDate' => date( "c" )
						),
						'Rule' );
				}
			} else {
				$response = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление записи о посещении пациентом поликлиники без записи)'));
				$process_good = false;
			}
			//$response4 = $response;
		}
		// для отладки
		/*
		  $response[0]['response1'] = $response1;
		  $response[0]['response2'] = isset($response2)?$response2:false;
		  $response[0]['response3'] = isset($response3)?$response3:false;
		  $response[0]['response4'] = isset($response4)?$response4:false;
		 */
		return $response;
	}

	/**
	 * Выборка расписания на первом этапе
	 */
	function getListTimetableLpu( $data ) {
		/*
		  $begDay_id = TimeToDay(mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		  $endDay_id = TimeToDay(mktime(0, 0, 0, date("m"), date("d")+30, date("Y"));
		 */
		$filter = '(1 = 1)';
		$filter_in = '(1 = 1)';
		$filter_tt = '';
		$filter_ttp = '';

		$params = array();

		$join = "";
		if ( !empty( $data['LpuSectionProfile_id'] ) ) {
			$filter_in .= " and lss.LpuSectionProfile_id = :LpuSectionProfile_id";
			$filter_tt .= " AND msf.LpuSectionProfile_id = :LpuSectionProfile_id";
			$filter_ttp .= " AND ls.LpuSectionProfile_id = :LpuSectionProfile_id";
			$params['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
		}
		if ( !empty( $data['MedPersonal_id'] ) ) {
			$filter_in .= " and msf.MedPersonal_id = :MedPersonal_id";
			$filter_tt .= " AND msf.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		if ( (!empty( $data['LpuSectionProfile_id'] )) || (!empty( $data['MedPersonal_id'] )) ) {
			$filter .= " and ms.LpuUnit_id = lu.LpuUnit_id";
			$join = "
			outer apply (
				select
						lss.LpuUnit_id
					from v_MedStaffFact_ER msf with (NOLOCK)
						left join LpuSection lss with (NOLOCK) on lss.LpuSection_id = msf.LpuSection_id
				 and {$filter_in}
				) ms";
		}

		if ( !empty( $data['LpuUnitType_id'] ) ) {
			$filter .= " and lu.LpuUnitType_id = :LpuUnitType_id";
			$params['LpuUnitType_id'] = $data['LpuUnitType_id'];
		}
		if ( !empty( $data['Lpu_id'] ) ) {
			$filter .= " and lu.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			select
				-- select
				lu.Lpu_id,
				Lpu.Lpu_Nick,
				lu.LpuUnit_id,
				lu.LpuUnit_Name,
				lu.LpuUnit_Descr,
				(Rtrim(luas.KLStreet_Name) + ' ' + lua.Address_House ) as LpuUnit_Address,
				lu.LpuUnit_Phone,
				lu.LpuUnit_Enabled,
				lu.LpuUnit_ExtMedCnt as ExtMed,
				lu.LpuUnitType_id,
				LpuUnit_updDT,
				null as FreeTime --Первое свободное время
				-- end select
			from
			-- from
			v_LpuUnit_ER lu with (NOLOCK)
			left outer join Address lua with (NOLOCK) on lu.Address_id = lua.Address_id
			left outer join KLStreet luas with (NOLOCK) on lua.KLStreet_id = luas.KLStreet_id
			left join v_Lpu Lpu with (NOLOCK) on Lpu.Lpu_id = lu.Lpu_id
			left join v_pmUser pu with (NOLOCK) on lu.pmUser_updId = pu.pmUser_id
			{$join}
			-- end from
			where
				-- where
				lu.LpuUnitType_id !=5
				and (Lpu.Lpu_endDate is null or Lpu.Lpu_endDate > dbo.tzGetDate()) and
				{$filter}
				-- end where
				order by
				-- order by
				lu.LpuUnit_Enabled DESC,  Lpu.Lpu_Nick ASC, lu.LpuUnit_Name ASC
				-- end order by
		";
		/*
		  echo getDebugSql(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		  exit;
		 */
		$result = $this->db->query( getLimitSQLPH( $query,
				$data['start'],
				$data['limit'] ),
			$params );
		$result_count = $this->db->query( getCountSQLPH( $query ),
			$params );

		if ( is_object( $result_count ) ) {
			$cnt_arr = $result_count->result( 'array' );
			$count = $cnt_arr[0]['cnt'];
			unset( $cnt_arr );
		} else {
			$count = 0;
		}
		if ( is_object( $result ) ) {
			$response = array();
			$response['data'] = $result->result( 'array' );
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка подразделений для старой формы записи
	 */
	function getListTimetableLpuUnit( $data ) {
		/*
		  $begDay_id = TimeToDay(mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		  $endDay_id = TimeToDay(mktime(0, 0, 0, date("m"), date("d")+30, date("Y"));
		 */
		$filter = "(1 = 1)";
		$filter_in = "(1 = 1)";
		$filter_tt = "";
		$filter_ttp = '';

		$params = array();

		$join = "";
		if ( !empty( $data['LpuSectionProfile_id'] ) ) {
			$filter_in .= " and lss.LpuSectionProfile_id = :LpuSectionProfile_id";
			$filter_tt .= " AND msf.LpuSectionProfile_id = :LpuSectionProfile_id";
			$filter_ttp = " AND ls.LpuSectionProfile_id = :LpuSectionProfile_id";
			$params['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
		}
		if ( !empty( $data['MedPersonal_id'] ) ) {
			$filter_in .= " and msf.MedPersonal_id = :MedPersonal_id";
			$filter_tt .= " AND msf.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}
		if ( (!empty( $data['LpuSectionProfile_id'] )) || (!empty( $data['MedPersonal_id'] )) ) {
			$filter .= " and ms.LpuUnit_id = lu.LpuUnit_id";
			$join = "
			outer apply (
				select
						lss.LpuUnit_id
					from v_MedStaffFact_ER msf with (NOLOCK)
						left join LpuSection lss with (NOLOCK) on lss.LpuSection_id = msf.LpuSection_id
				 and {$filter_in}
				) ms";
		}

		if ( !empty( $data['LpuUnitType_id'] ) ) {
			$filter .= " and lu.LpuUnitType_id = :LpuUnitType_id";
			$params['LpuUnitType_id'] = $data['LpuUnitType_id'];
		}
		if ( !empty( $data['Lpu_id'] ) ) {
			$filter .= " and lu.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		} else {
			return false;
		}

		$query = "
			select
				-- select
				lu.Lpu_id,
				Lpu.Lpu_Nick,
				lu.LpuUnit_id,
				lut.LpuUnitType_SysNick,
				lu.LpuUnit_Name,
				lu.LpuUnit_Descr,
				(Rtrim(luas.KLStreet_Name) + ' ' + lua.Address_House ) as LpuUnit_Address,
				lu.LpuUnit_Phone,
				lu.LpuUnit_Enabled,
				lu.LpuUnit_ExtMedCnt as ExtMed,
				lu.LpuUnitType_id,
				LpuUnit_updDT,
				ISNULL(convert(varchar,coalesce(TT.min_time,TTP.min_time),4),'нет') +' '+ ISNULL(convert(varchar,coalesce(TT.min_time,TTP.min_time),108),'') as FreeTime --Первое свободное время
				-- end select
			from
			-- from
			v_LpuUnit_ER lu with (NOLOCK)
			left outer join Address lua with (NOLOCK) on lu.Address_id = lua.Address_id
			left outer join KLStreet luas with (NOLOCK) on lua.KLStreet_id = luas.KLStreet_id
			left join v_Lpu Lpu with (NOLOCK) on Lpu.Lpu_id = lu.Lpu_id
			left join v_pmUser pu with (NOLOCK) on lu.pmUser_updId = pu.pmUser_id
			left join LpuUnitType lut with (NOLOCK) on lut.LpuUnitType_id = lu.LpuUnitType_id
			outer apply (
				select
					MIN(ttg.TimetableGraf_begTime) as min_time
				from v_TimetableGraf_lite ttg with (NOLOCK)
				where
					ttg.MedStaffFact_id in (
						select msf.MedStaffFact_id from v_MedStaffFact_ER msf with (NOLOCK)
						where msf.LpuUnit_id = lu.LpuUnit_id
						{$filter_tt}
					)
					and ttg.TimetableType_id not in (2,3,4)
					and ttg.Person_id is null
					and ttg.TimetableGraf_begTime >= dbo.tzGetDate()
			) TT
			outer apply (
				select top 1
					ttg.TimetablePar_begTime as min_time
				from v_TimetablePar ttg with (NOLOCK)
				where
					ttg.LpuSection_id in (select LpuSection_id from v_LpuSection_ER ls with (NOLOCK) where ls.Lpu_id = lu.Lpu_id and ls.LpuUnit_id = lu.LpuUnit_id {$filter_ttp})
					and  ttg.TimetablePar_IsReserv is null
					and ttg.Person_id is null
					and ttg.TimetablePar_isPay is null
					and ttg.TimetablePar_IsDop is null
					and ttg.TimetablePar_begTime >= dbo.tzGetDate()
				order by ttg.TimetablePar_begTime
			) TTP
			{$join}
			-- end from
			where
				-- where
				lu.LpuUnitType_id !=5
				and (Lpu.Lpu_endDate is null or Lpu.Lpu_endDate > dbo.tzGetDate()) and
				{$filter}
				-- end where
				order by
				-- order by
				lu.LpuUnit_Enabled DESC,  Lpu.Lpu_Nick ASC, lu.LpuUnit_Name ASC
				-- end order by
		";
		$res = $this->db->query( $query,
			$params );
		if ( is_object( $res ) ) {
			return $res->result( 'array' );
		} else {
			return false;
		}
	}

	/**
	 * Получение списка врачей для старой формы записи
	 */
	function getListTimetableMedPersonal( $data ) {
		/*
		  $begDay_id = TimeToDay(mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		  $endDay_id = TimeToDay(mktime(0, 0, 0, date("m"), date("d")+30, date("Y"));
		 */
		$filter = "(1 = 1)";
		$filter_in = "(1 = 1)";

		$params = array();

		$join = "";

		if ( !empty( $data['LpuSectionProfile_id'] ) ) {
			$filter .= " and msf.LpuSectionProfile_id = :LpuSectionProfile_id";
			$params['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
		}
		if ( !empty( $data['MedPersonal_id'] ) ) {
			$filter .= " and msf.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		if ( !empty( $data['LpuUnit_id'] ) ) {
			$filter .= " and msf.LpuUnit_id = :LpuUnit_id";
			$params['LpuUnit_id'] = $data['LpuUnit_id'];
		}

		if ( !empty( $data['Lpu_id'] ) ) {
			$filter .= " and msf.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			select
				-- select
				MedPersonal_FIO as MedPersonal_FIO,
				msf.MedStaffFact_id,
				msf.MedPersonal_id,
				msf.LpuSection_id,
				msf.LpuSectionProfile_id,
				msf.LpuSectionProfile_Name,
				null as MedStaffFact_Descr,
				u.pmuser_name,
				msf.Lpu_id,
				msf.LpuUnit_id,
				msf.MedStaffFact_updDT,
				msf.RecType_id,
				msf.MedStatus_id,
				sign(LR.LpuRegion_Count) as isRegion,
				rtrim(LpuSection_Name) as LpuSection_Name,
				rtrim(LR.LpuRegion_Name) as LpuRegion_Names, -- участки врача этого профиля  + ' ' + convert(varchar,LR.LpuRegion_Count,4)
				ISNULL(convert(varchar,TT.min_time,4),'нет') +' '+ ISNULL(convert(varchar,TT.min_time,108),'') as FreeTime, --Первое свободное время
				(select count(*) from v_EvnQueue with (NOLOCK) where LpuSectionProfile_did = msf.LpuSectionProfile_id
					and Lpu_id = msf.Lpu_id
					and EvnDirection_id is null
					and EvnQueue_recDT is null
					and pmUser_recID is null
					and TimetableGraf_id is null
					and TimetableStac_id is null
					and TimetablePar_id is null) as EvnQueue_Names -- Количество в очереди к врачу
				-- end select
			from
			-- from
			v_MedStaffFact_ER msf with (NOLOCK)
			left join v_pmUser u with (NOLOCK) on u.pmUser_id=msf.pmUser_updId
			outer apply (
				select
					MIN(ttg.TimetableGraf_begTime) as min_time
				from v_TimetableGraf_lite ttg with (NOLOCK)
				where msf.MedStaffFact_id=ttg.MedStaffFact_id
					and ttg.TimetableType_id not in (2,3,4)
					and ttg.Person_id is null
					and ttg.TimetableGraf_begTime >= dbo.tzGetDate()
			) TT
			outer apply (
				select
					count(*) as LpuRegion_Count,
					lr.LpuRegion_Name
				from
					v_MedstaffRegion msr with (NOLOCK)
					left join v_LpuRegion lr with (NOLOCK) on lr.LpuRegion_id = msr.LpuRegion_id
				where
					msf.MedPersonal_id = msr.MedPersonal_id
					and msr.Lpu_id = msf.Lpu_id
				group by lr.LpuRegion_Name
			) LR
			-- end from
			where
				-- where
				{$filter}
				and (msf.Medstafffact_disDate is null
				or convert(varchar, msf.Medstafffact_disDate, 112) > dbo.tzGetDate())
				and (msf.RecType_id != 6 or msf.RecType_id is null)
				-- end where
				order by
				-- order by
				sign(LR.LpuRegion_Count) DESC, MedPersonal_FIO ASC
				-- end order by
		";
		/*
		  echo getDebugSql(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		  exit;
		 */
		$result = $this->db->query( getLimitSQLPH( $query,
				$data['start'],
				$data['limit'] ),
			$params );
		$result_count = $this->db->query( getCountSQLPH( $query ),
			$params );

		if ( is_object( $result_count ) ) {
			$cnt_arr = $result_count->result( 'array' );
			$count = $cnt_arr[0]['cnt'];
			unset( $cnt_arr );
		} else {
			$count = 0;
		}
		if ( is_object( $result ) ) {
			$response = array();
			$response['data'] = $result->result( 'array' );
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка служб для старой формы записи
	 */
	function getListTimetableMedService( $data ) {
		$filter = "(1 = 1)";
		$uc_filter = '';

		$params = array();

		if ( !empty( $data['Lpu_id'] ) ) {
			$filter .= " and ms.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		if ( !empty( $data['LpuUnitType_id'] ) ) {
			$filter .= " and ms.LpuUnitType_id = :LpuUnitType_id";
			$params['LpuUnitType_id'] = $data['LpuUnitType_id'];
		}

		if ( !empty( $data['LpuUnit_id'] ) ) {
			$filter .= " and ms.LpuUnit_id = :LpuUnit_id";
			$params['LpuUnit_id'] = $data['LpuUnit_id'];
		} else {
			$filter .= " and ms.LpuUnit_id is null and ms.LpuSection_id is null";
		}

		if ( !empty( $data['MedService_id'] ) ) {
			$filter = " ms.MedService_id = :MedService_id";
			$params['MedService_id'] = $data['MedService_id'];
		}

		if ( !empty( $data['UslugaComplex_id'] ) ) {
			$uc_filter = " AND UCMS.UslugaComplex_id = :UslugaComplex_id";
			$params['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}

		if ( !empty( $data['uslugaList'] ) ) {
			$uslugaList = explode( ',',
				$data['uslugaList'] );
			foreach ( $uslugaList as &$UslugaComplex_id ) {
				$UslugaComplex_id = trim( $UslugaComplex_id );
				if ( !(is_numeric( $UslugaComplex_id ) && $UslugaComplex_id > 0) ) {
					return false;
				}
			}
			$uslugaList = implode( ',',
				$uslugaList );
			$filter = "(1 = 1)";
			$uc_filter = " AND UCMS.UslugaComplex_id in({$uslugaList})";
		}

		$query = "
			select
				-- select
				case when ms.Lpu_id = :Lpu_id then 1 else 0 end as isUserLpu,
				ms.MedService_Nick as MedService_Nick,
				ms.MedService_id,
				ms.Lpu_id,
				ms.LpuBuilding_id,
				ms.LpuUnitType_id,
				LUT.LpuUnitType_SysNick,
				ms.LpuUnit_id,
				ms.LpuSection_id,
				LS.LpuSectionProfile_id,
				MT.MedServiceType_id,
				MT.MedServiceType_SysNick,
				UC.UslugaComplex_Name,
				UCMS.UslugaComplex_id,
				'нет' as FreeTime, --Первое свободное время
				0 as EvnQueue_Names -- Количество в очереди
				-- end select
			from
			-- from
				v_MedService ms with (NOLOCK)
				inner join v_UslugaComplexMedService UCMS with (NOLOCK) on ms.MedService_id = UCMS.MedService_id {$uc_filter}
				inner join v_UslugaComplex UC with (NOLOCK) on UCMS.UslugaComplex_id = UC.UslugaComplex_id
				left join v_MedServiceType MT with (NOLOCK) on ms.MedServiceType_id = MT.MedServiceType_id
				left join v_LpuUnitType LUT with (NOLOCK) on ms.LpuUnitType_id = LUT.LpuUnitType_id
				left join v_LpuSection LS with (NOLOCK) on ms.LpuSection_id = LS.LpuSection_id
			-- end from
			where
				-- where
				{$filter}
				AND ( cast(dbo.tzGetDate() as date) between cast(UCMS.UslugaComplexMedService_begDT as date) AND cast(isnull(UCMS.UslugaComplexMedService_endDT,dbo.tzGetDate()) as date) )
				-- end where
			order by
				-- order by
				isUserLpu desc, UC.UslugaComplex_Name ASC
				-- end order by		";
		/*
		  echo getDebugSql(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		  exit;
		 */
		$result = $this->db->query( getLimitSQLPH( $query,
				$data['start'],
				$data['limit'] ),
			$params );
		$result_count = $this->db->query( getCountSQLPH( $query ),
			$params );

		if ( is_object( $result_count ) ) {
			$cnt_arr = $result_count->result( 'array' );
			$count = $cnt_arr[0]['cnt'];
			unset( $cnt_arr );
		} else {
			$count = 0;
		}
		if ( is_object( $result ) ) {
			$response = array();
			$response['data'] = $result->result( 'array' );
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка отделений для старой формы записи
	 */
	function getListTimetableLpuSection( $data ) {
		/*
		  $begDay_id = TimeToDay(mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		  $endDay_id = TimeToDay(mktime(0, 0, 0, date("m"), date("d")+30, date("Y"));
		 */
		$filter = "(1 = 1)";
		$filter_in = "(1 = 1)";
		$filter_tt = "";

		$params = array();

		$join = "";
		if ( !empty( $data['LpuSectionProfile_id'] ) ) {
			$filter .= " and ls.LpuSectionProfile_id = :LpuSectionProfile_id";
			$filter_tt .= " AND msf.LpuSectionProfile_id = :LpuSectionProfile_id";
			$params['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
		}

		if ( !empty( $data['LpuUnit_id'] ) ) {
			$filter .= " and ls.LpuUnit_id = :LpuUnit_id";
			$filter_in .= " and LSWC.LpuUnit_id = :LpuUnit_id";
			$filter_tt .= " AND msf.LpuUnit_id = :LpuUnit_id";
			$params['LpuUnit_id'] = $data['LpuUnit_id'];
		}

		if ( !empty( $data['Lpu_id'] ) ) {
			$filter .= " and lu.Lpu_id = :Lpu_id";
			$filter_tt .= " AND msf.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		$for_free_time = 'outer apply (
				select
					null as min_time
			) TT';
		if ( $data['LpuUnitType_SysNick'] == 'parka' ) {

			$usluga = "
				join v_UslugaComplex UC with (NOLOCK) on UC.LpuSection_id = ls.LpuSection_id
			";

			$usluga_select = "
				UC.UslugaComplex_id,
				UC.UslugaComplex_Name,
			";

			$for_free_time = 'outer apply (
				select top 1
					ttg.TimetablePar_begTime as min_time
				from v_TimetablePar ttg with (NOLOCK)
				where
					ttg.LpuSection_id = ls.LpuSection_id
					and  ttg.TimetablePar_IsReserv is null
					and ttg.Person_id is null
					and ttg.TimetablePar_isPay is null
					and ttg.TimetablePar_IsDop is null
					and ttg.TimetablePar_begTime >= dbo.tzGetDate()
				order by ttg.TimetablePar_begTime
			) TT';
		} else {
			$usluga = "";
			$usluga_select = "";
		}

		// не показываем подотделения отделений стационарного типа
		if ( in_array( $data['LpuUnitType_SysNick'],
				array('stac', 'dstac', 'hstac', 'pstac') ) ) {
			$filter .= " and ls.LpuSection_pid is null";
		} else {
			//непонятно зачем было это условие, но для стаца оно точно не нужно, т.к. отсеивает отделения, имеющие подотделеления
			$filter .= " and ls.LpuSection_id not in (select LpuSection_pid from v_LpuSectionWithCabs LSWC with (NOLOCK) where LpuSection_pid is not null and {$filter_in})";
		}

		$query = "
			select
				-- select
				LpuUnitType_id,
				ls.LpuSection_id,
				ls.LpuSection_Name,
				--LpuSectionType_id,
				ls.LpuSection_Descr,
				ls.LpuSectionProfile_Name,
				rtrim(pmUser_Name) as pmuser_name,
				ls.LpuSection_updDT,
				ls.LpuSectionProfile_id,
				lu.LpuUnit_id,
				{$usluga_select}
				ISNULL(convert(varchar,TT.min_time,4),'нет') +' '+ ISNULL(convert(varchar,TT.min_time,108),'') as FreeTime, --Первое свободное время
				(select count(*) from v_EvnQueue with (NOLOCK) where LpuSectionProfile_did = ls.LpuSectionProfile_id
					and Lpu_id = lu.Lpu_id
					and EvnDirection_id is null
					and EvnQueue_recDT is null
					and pmUser_recID is null
					and TimetableGraf_id is null
					and TimetableStac_id is null
					and TimetablePar_id is null) as EvnQueue_Names -- Количество в очереди
				-- end select
			from
			-- from
			v_LpuSection_ER ls with (NOLOCK)
			left join v_LpuUnit_ER lu with (NOLOCK) on lu.LpuUnit_id = ls.LpuUnit_id
			left join v_pmUser pu  with (NOLOCK) on ls.pmUser_updId = pu.pmUser_id
			{$usluga}
			{$for_free_time}
			-- end from
			where
			-- where
				{$filter} and isnull(LpuSectionHospType_id, 1) != 5
			-- end where
			order by
			-- order by
			LpuSection_Name ASC
			-- end order by
		";
		/*
		  echo getDebugSql(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		  exit;
		 */
		$result = $this->db->query( getLimitSQLPH( $query,
				$data['start'],
				$data['limit'] ),
			$params );
		$result_count = $this->db->query( getCountSQLPH( $query ),
			$params );

		if ( is_object( $result_count ) ) {
			$cnt_arr = $result_count->result( 'array' );
			$count = $cnt_arr[0]['cnt'];
			unset( $cnt_arr );
		} else {
			$count = 0;
		}
		if ( is_object( $result ) ) {
			$response = array();
			$response['data'] = $result->result( 'array' );
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Получение первой свободной бирки
	 */
	function getFreeTimetable( $data ) {
		switch ( $data['object'] ) {
			case 'TimetableGraf': // При необходимости требуется доработка
				$query = '
					select null
				';
				break;
			case 'TimetablePar': // При необходимости требуется доработка
				$query = '
					select null
				';
				break;
			case 'TimetableStac':
				// Экстренные бирки
				$query = '
					Select top 1 TimetableStac_id
					from v_TimetableStac_lite with (nolock)
					where TimetableStac_setDate = cast(dbo.tzGetDate() as date)
					and TimetableType_id = 6 and Person_id is null
					and LpuSection_id = :LpuSection_id';
				break;
			default:
				return array(array('Error_Msg' => 'Указанный тип расписания не существует.'));
		}
		$result = $this->db->query( $query,
			$data );

		if ( is_object( $result ) ) {
			$r = $result->result( 'array' );
			if ( count( $r ) > 0 ) {
				return $r[0]['TimetableStac_id'];
			} else {
				return 0;
			}
		} else {
			return 0;
		}
	}

	/**
	 * Получение расписания для редактирования
	 */
	function getTimetableGrafForEdit( $data ) {
		$outdata = array();

		if ( !isset( $data['MedStaffFact_id'] ) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Не указан врач, для которого показывать расписание'
			);
		}

		$StartDay = isset( $data['StartDay'] ) ? strtotime( $data['StartDay'] ) : time();

		$outdata['StartDay'] = $StartDay;

		$param['StartDay'] = TimeToDay( $StartDay );
		$param['EndDay'] = TimeToDay( strtotime( "+14 days", $StartDay ) );
		$param['MedStaffFact_id'] = $data['MedStaffFact_id'];
		
		$param['StartDate'] = date( "Y-m-d", $StartDay );
		$param['EndDate'] = date( "Y-m-d", strtotime( "+14 days", $StartDay ) );
		
		if ($data['PanelID'] == 'TTGRecordPanel' || $data['PanelID'] == 'TTGDirectionPanel') {

			$msflpu = $this->getFirstRowFromQuery("select Lpu_id from v_MedStaffFact with (nolock) where MedStaffFact_id = ?", array($data['MedStaffFact_id']));

			if (empty($_SESSION['setting']) || empty($_SESSION['setting']['server'])) { // Вынес отдельно, чтобы не повторять
				$maxDays = null;

			} elseif (!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] == 'regpol' && $_SESSION['lpu_id'] == $msflpu['Lpu_id']) { // Для регистратора запись в свою МО
				$this->load->model('LpuIndividualPeriod_model', 'lipmodel');
				$individualPeriod = $this->lipmodel->getObjectIndividualPeriod(array('Lpu_id' => $_SESSION['lpu_id']), 'MedStaffFact');

				if( !empty($data['MedStaffFact_id']) && !empty($individualPeriod[$data['MedStaffFact_id']]) ) {
					$maxDays = $individualPeriod[$data['MedStaffFact_id']];
				} else {
					$maxDays = !empty($_SESSION['setting']['server']['pol_record_day_count']) ? $_SESSION['setting']['server']['pol_record_day_count'] : null;
				}

			} elseif (!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] == 'regpol') { // Для регистратора запись в чужую МО
				$maxDays = !empty($_SESSION['setting']['server']['pol_record_day_count_reg']) ? $_SESSION['setting']['server']['pol_record_day_count_reg'] : null;

			} elseif (!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] == 'callcenter') { // Для оператора call-центра
				$maxDays = !empty($_SESSION['setting']['server']['pol_record_day_count_cc']) ? $_SESSION['setting']['server']['pol_record_day_count_cc'] : null;

			} elseif ($_SESSION['lpu_id'] == $msflpu['Lpu_id']) { // Для остальных пользовалелей запись в свою МО
				$maxDays = !empty($_SESSION['setting']['server']['pol_record_day_count_own']) ? $_SESSION['setting']['server']['pol_record_day_count_own'] : null;

			} else { // Для остальных пользовалелей запись в чужую МО
				$maxDays = !empty($_SESSION['setting']['server']['pol_record_day_count_other']) ? $_SESSION['setting']['server']['pol_record_day_count_other'] : null;

			}

			//var_dump(date_default_timezone_get());
			/*var_dump(date("H:i"));
			var_dump(getShowNewDayTime());
			var_dump(date("H:i") >= getShowNewDayTime() && $maxDays);*/

			if ( date("H:i") >= getShowNewDayTime() && $maxDays ) $maxDays++;
			//exit;
			$param['EndDate'] = !empty($maxDays) ? date( "Y-m-d", strtotime( "+".$maxDays." days", time()) ) : $param['EndDate'];
		}

		$param['nulltime'] = '00:00:00';

		$nTime = $StartDay;


		$outdata['header'] = array();
		$outdata['descr'] = array();
		$outdata['data'] = array();
		$outdata['occupied'] = array();
		for ( $nCol = 0; $nCol < 14; $nCol++ ) {
			//echo $nTime." - ".TimeToDay( $nTime )."<br>";
			$nDay = TimeToDay( $nTime );
			$nWeekDay = date( 'w',
				$nTime );
			$sClass = "work";
			if ( ( $nWeekDay == 6 ) || ( $nWeekDay == 0 ) ) {
				$sClass = "relax";
			}
			$outdata['header'][TimeToDay( $nTime )] = "<td class='$sClass'>" . "<b>" . $this->arShortWeekDayName[$nWeekDay] . "</b>" . date( " d",
					$nTime ) . "</td>";
			$outdata['descr'][TimeToDay( $nTime )] = array();
			$outdata['data'][TimeToDay( $nTime )] = array();
			$outdata['occupied'][TimeToDay( $nTime )] = false;

			$nTime = strtotime( "+1 day",
				$nTime );
		}

		$param['StartDayA'] = TimeToDay(strtotime("-1 day",$StartDay));
		$param['EndDayA'] = TimeToDay(strtotime("+13 days", $StartDay));
		$param['Lpu_id'] = $data['Lpu_id'];
		$param['pmUser_id'] = $data['pmUser_id'];

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
			left join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = A.MedStaffFact_id
			where A.MedStaffFact_id = :MedStaffFact_id
				and D.Day_id >= :StartDayA
				and D.Day_id < :EndDayA
				and (A.AnnotationVison_id != 3 or msf.Lpu_id = :Lpu_id)";

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
				left join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = A.MedStaffFact_id
			where A.MedStaffFact_id = :MedStaffFact_id
				and (A.Annotation_begDate IS NULL or A.Annotation_begDate <= cast(:EndDate as date))
				and (A.Annotation_endDate IS NULL or cast(:StartDate as date) <= A.Annotation_endDate)
				and (A.Annotation_begTime IS NOT NULL or A.Annotation_endTime IS NOT NULL)
				and (A.AnnotationVison_id != 3 or msf.Lpu_id = :CurrentLpu_id)
		", $param);

		if ( $annotationdata === false ) {
			$annotationdata = array();
		}

		$joinAccessFilter = '';
		$lpuFilter = getAccessRightsLpuFilter('laf.Lpu_id');
		if (!empty($lpuFilter)) {
			$joinAccessFilter .= " left join v_Lpu laf with(nolock) on laf.Lpu_id = msf.Lpu_id and ($lpuFilter or t.pmUser_updID = :pmUser_id)";
		} else {
			$joinAccessFilter .= " left join v_Lpu laf with(nolock) on laf.Lpu_id = msf.Lpu_id";
		}

		$selectPersonData = "case when laf.Lpu_id is null then '' else p.Person_BirthDay end as Person_BirthDay,
					case when laf.Lpu_id is null then '' else p.Person_Phone end as Person_Phone,
					case when laf.Lpu_id is null then '' else rtrim(p.Person_Firname) end as Person_Firname,
					case when laf.Lpu_id is null then '' else rtrim(p.Person_Surname) end as Person_Surname,
					case when laf.Lpu_id is null then '' else rtrim(p.Person_Secname) end as Person_Secname,";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = p.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then p.Person_BirthDay else null end as Person_BirthDay,
					case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then p.Person_Phone else null end as Person_Phone,
					case when laf.Lpu_id is null then ''
						when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Surname)
						else rtrim(peh.PersonEncrypHIV_Encryp)
					end as Person_Surname,
					case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then rtrim(p.Person_Firname) else '' end as Person_Firname,
					case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then rtrim(p.Person_Secname) else '' end as Person_Secname,";
		}

		$filters = "";
		if (!havingGroup(array('CallCenterAdmin', 'OperatorCallCenter'))) {
			// если не оператор Call-центра, то фильтруем по МО.
			if(isset($data['filterByLpu']) && $data['filterByLpu'] != 'false')
				$filters .= "and (isnull(msf.MedStaffFact_IsDirRec, 2) = 2 or msf.Lpu_id = :Lpu_id)";
		}

		$sql = "
			select
				t.pmUser_updID,
				t.TimetableGraf_updDT,
				t.TimetableGraf_id,
				t.Person_id,
				t.TimetableGraf_Day,
				t.TimetableGraf_begTime,
				t.TimetableType_id,
				t.TimetableGraf_IsDop,
				t.TimeTableGraf_countRec,
				t.TimeTableGraf_PersRecLim,
				p.PrivilegeType_id,
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
				ISNULL(cast(et.ElectronicTalon_Num as varchar), cast(ED.EvnDirection_TalonCode as varchar)) as Direction_TalonCode,
				convert(varchar(10), d.EvnDirection_setDT,104) as Direction_Date,
				d.EvnDirection_id,
				qp.pmUser_Name as QpmUser_Name,
				q.EvnQueue_insDT as EvnQueue_insDT,
				dg.Diag_Code,
				u.Lpu_id as pmUser_Lpu_id,
				msf.MedStaffFact_id,
				msf.MedPersonal_id,
				msf.LpuUnit_id
			from v_TimetableGraf_lite t with (nolock)
				left outer join v_MedStaffFact_ER msf with (nolock) on msf.MedStaffFact_id = t.MedStaffFact_id
				left outer join v_Person_ER2 p with (nolock) on t.Person_id = p.Person_id
				left outer join v_pmUser u with (nolock) on t.PMUser_UpdID = u.PMUser_id
				left join v_EvnDirection d with (nolock) on
					t.EvnDirection_id = d.EvnDirection_id
					and d.DirFailType_id is null
				left join v_Lpu lpud with (nolock) ON lpud.Lpu_id = d.Lpu_id
				left join v_EvnQueue q with (nolock) on
					t.TimetableGraf_id = q.TimetableGraf_id
					and t.Person_id = q.Person_id
				left join v_pmUser qp with (nolock) on q.pmUser_updId = qp.pmUser_id
				left join Diag dg with (nolock) on dg.Diag_id = d.Diag_id
				left join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = t.EvnDirection_id
				left join v_ElectronicTalon et (nolock) on et.EvnDirection_id = t.EvnDirection_id
				{$joinPersonEncrypHIV}
				{$joinAccessFilter}
			where t.TimetableGraf_Day >= :StartDay
				and t.TimetableGraf_Day < :EndDay
				and t.MedStaffFact_Id = :MedStaffFact_id
				and cast(TimetableGraf_begTime as date) between :StartDate and :EndDate
				{$filters}
			order by t.TimetableGraf_begTime
		";

		$res = $this->db->query( $sql,
			$param );

		//echo getDebugSql($sql, $param);
		$ttgdata = $res->result( 'array' );

		foreach ( $ttgdata as $ttg ) {
			$ttgannotation = array();

			foreach ( $annotationdata as $annotation ) {
				if (
					(empty($annotation['Annotation_begDate']) || $annotation['Annotation_begDate'] <= $ttg['TimetableGraf_begTime']->format('Y-m-d'))
					&& (empty($annotation['Annotation_endDate']) || $annotation['Annotation_endDate'] >= $ttg['TimetableGraf_begTime']->format('Y-m-d'))
					&& (empty($annotation['Annotation_begTime']) || $annotation['Annotation_begTime'] <= $ttg['TimetableGraf_begTime']->format('H:i'))
					&& (empty($annotation['Annotation_endTime']) || $annotation['Annotation_endTime'] >= $ttg['TimetableGraf_begTime']->format('H:i'))
				) {
					$ttgannotation[] = $annotation;
				}
			}

			$ferAnnotation = $this->_getTimetableGrafFERAnnotation($ttg);

			if ( is_array($ferAnnotation) ) {
				$ttgannotation = array_merge($ttgannotation, $ferAnnotation);
			}

			$ttg['annotation'] = $ttgannotation;
			$outdata['data'][$ttg['TimetableGraf_Day']][] = $ttg;
			if ( isset( $ttg['Person_id'] ) ) {
				$outdata['occupied'][$ttg['TimetableGraf_Day']] = true;
			}

			if (!empty($data['timetable_blocked']) && !isset($ttg['Person_id']) && in_array($ttg['TimetableType_id'],array(1,11))) {
				$outdata['occupied'][$ttg['TimetableGraf_Day']] = true;
			}
		}

		$sql = "
			select TimetableGraf_id from TimetableLock with(nolock) where TimetableGraf_id is not null";

		$res = $this->db->query( $sql );

		$outdata['reserved'] = array();
		$reserved = $res->result( 'array' );
		foreach ( $reserved as $lock ) {
			$outdata['reserved'][] = $lock['TimetableGraf_id'];
		}

		return $outdata;
	}

	/**
	 * Получение примечаний на бирку
	 */
	protected function _getTimetableGrafFERAnnotation($data) {
		$resp = array();

		if ( IsFerUser($data['pmUser_updID']) ) {
			// получаем из TimetableExtend примечание по бирке, если её создал юзер ФЭР
			$resp_te = $this->queryResult("
				select
					rtrim(te.TimetableExtend_Descr) as Annotation_Comment,
					rtrim(pu.pmUser_Name) as pmUser_Name,
					te.TimetableExtend_updDT as Annotation_updDT
				from
					v_TimetableExtend te (nolock)
					inner join v_pmUser pu (nolock) on pu.pmUser_id = te.pmUser_updID
				where
					te.TimetableGraf_id = :TimetableGraf_id
			", array(
				'TimetableGraf_id' => $data['TimetableGraf_id']
			));

			if ( is_array($resp_te) ) {
				$resp = array_merge($resp, $resp_te);
			}
		}

		return $resp;
	}

	/**
	 * Получение расписания на один день
	 */
	function getTimetableGrafOneDay( $data ) {
		$outdata = array();

		if ( !isset( $data['MedStaffFact_id'] ) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Не указан врач, для которого показывать расписание'
			);
		}

		$StartDay = isset( $data['StartDay'] ) ? strtotime( $data['StartDay'] ) : time();

		$outdata['StartDay'] = $StartDay;

		$param['pmUser_id'] = $data['pmUser_id'];
		$param['StartDay'] = TimeToDay( $StartDay );
		$param['StartDayA'] = $param['StartDay'] - 1;
		$param['MedStaffFact_id'] = $data['MedStaffFact_id'];
		$param['Lpu_id'] = $data['Lpu_id'];
		$param['nulltime'] = '00:00:00';

		$param['EndDate'] = date( "Y-m-d", $StartDay);
		if ($data['PanelID'] == 'TTGRecordOneDayPanel') {

			$msflpu = $this->getFirstRowFromQuery("select Lpu_id from v_MedStaffFact with (nolock) where MedStaffFact_id = ?", array($data['MedStaffFact_id']));

			if (empty($_SESSION['setting']) || empty($_SESSION['setting']['server'])) { // Вынес отдельно, чтобы не повторять
				$maxDays = null;

			} elseif (!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] == 'regpol' && $_SESSION['lpu_id'] == $msflpu['Lpu_id']) { // Для регистратора запись в свою МО
				$this->load->model('LpuIndividualPeriod_model', 'lipmodel');
				$individualPeriod = $this->lipmodel->getObjectIndividualPeriod(array('Lpu_id' => $_SESSION['lpu_id']), 'MedStaffFact');

				if( !empty($data['MedStaffFact_id']) && !empty($individualPeriod[$data['MedStaffFact_id']]) ) {
					$maxDays = $individualPeriod[$data['MedStaffFact_id']];
				} else {
					$maxDays = !empty($_SESSION['setting']['server']['pol_record_day_count']) ? $_SESSION['setting']['server']['pol_record_day_count'] : null;
				}

			} elseif (!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] == 'regpol') { // Для регистратора запись в чужую МО
				$maxDays = !empty($_SESSION['setting']['server']['pol_record_day_count_reg']) ? $_SESSION['setting']['server']['pol_record_day_count_reg'] : null;

			} elseif (!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] == 'callcenter') { // Для оператора call-центра
				$maxDays = !empty($_SESSION['setting']['server']['pol_record_day_count_cc']) ? $_SESSION['setting']['server']['pol_record_day_count_cc'] : null;

			} elseif ($_SESSION['lpu_id'] == $msflpu['Lpu_id']) { // Для остальных пользовалелей запись в свою МО
				$maxDays = !empty($_SESSION['setting']['server']['pol_record_day_count_own']) ? $_SESSION['setting']['server']['pol_record_day_count_own'] : null;

			} else { // Для остальных пользовалелей запись в чужую МО
				$maxDays = !empty($_SESSION['setting']['server']['pol_record_day_count_other']) ? $_SESSION['setting']['server']['pol_record_day_count_other'] : null;

			}

			if ( $maxDays ) $maxDays--; // лишний день
			if ( date("H:i") >= getShowNewDayTime() && $maxDays ) $maxDays++;
			$param['EndDate'] = !empty($maxDays) ? date( "Y-m-d", strtotime( "+".$maxDays." days", time()) ) : $param['EndDate'];
		}
		
		$nTime = $StartDay;

		$outdata['day_comment'] = null;
		$outdata['data'] = array();

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
			left join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = A.MedStaffFact_id
			where A.MedStaffFact_id = :MedStaffFact_id
				and D.Day_id = :StartDayA
				and (A.AnnotationVison_id != 3 or msf.Lpu_id = :Lpu_id)";

		$res = $this->db->query( $sql,
			$param );

		$daydescrdata = $res->result( 'array' );

		foreach ( $daydescrdata as $day ) {
			$outdata['day_comment'][] = array(
				'Annotation_Comment' => $day['Annotation_Comment'],
				'pmUser_Name' => $day['pmUser_Name'],
				'Annotation_updDT' => isset( $day['Annotation_updDT'] ) ? $day['Annotation_updDT']->format( "d.m.Y H:i" ) : ''
			);
		}

		$joinAccessFilter = '';
		$lpuFilter = getAccessRightsLpuFilter('laf.Lpu_id');
		if (!empty($lpuFilter)) {
			$joinAccessFilter .= " left join v_Lpu laf with(nolock) on laf.Lpu_id = msf.Lpu_id and ($lpuFilter or t.pmUser_updID = :pmUser_id)";
		} else {
			$joinAccessFilter .= " left join v_Lpu laf with(nolock) on laf.Lpu_id = msf.Lpu_id";
		}

		$selectPersonData = "case when laf.Lpu_id is null then null else p.Person_BirthDay end as Person_BirthDay,
					case when laf.Lpu_id is null then '' else p.Person_Phone end as Person_Phone,
					pcs.PersonCardState_Code as PersonCard_Code,
					pcs.PersonCard_id as PersonCard_id,
					case when laf.Lpu_id is null then null else p.PersonInfo_InternetPhone end as Person_InetPhone,
					case when laf.Lpu_id is null then ''
						when a1.Address_id is not null
						then
							a1.Address_Address
						else
							a.Address_Address
					end	as Address_Address,
					case when a1.Address_id is not null
					then
						a1.KLTown_id
					else
						a.KLTown_id
					end as KLTown_id,
					case when laf.Lpu_id is null then ''
						when a1.Address_id is not null
						then
							a1.KLStreet_id
						else
							a.KLStreet_id
					end as KLStreet_id,
					case when a1.Address_id is not null
					then
						a1.Address_House
					else
						a.Address_House
					end as Address_House,".
					//Ufa, gaf #116387, для ГАУЗ РВФД
					(((isSuperadmin() || $param['Lpu_id'] == 81) && $this->getRegionNick() == "ufa")
						? "(select pp.post_name from v_PersonState (nolock) vps , job (nolock) jj, post (nolock) pp where vps.person_id=t.person_id and vps.job_id=jj.job_id and jj.post_id=pp.Post_id) as Job_Name,"
						: "case when laf.Lpu_id is null then '' else j.Job_Name end as Job_Name,").
					"case when laf.Lpu_id is null then '' else lpu.Lpu_Nick end as Lpu_Nick,
					case when laf.Lpu_id is null then '' else rtrim(p.Person_Firname) end as Person_Firname,
					case when laf.Lpu_id is null then '' else rtrim(p.Person_Surname) end as Person_Surname,
					case when laf.Lpu_id is null then '' else rtrim(p.Person_Secname) end as Person_Secname,
					case when laf.Lpu_id is null then '1' else '0' end as Person_Filter,";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = p.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then p.Person_BirthDay else null end as Person_BirthDay,
					case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then p.Person_Phone else null end as Person_Phone,
					case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then pcs.PersonCardState_Code else null end as PersonCard_Code,
					case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then pcs.PersonCard_id else null end as PersonCard_id,
					case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then p.PersonInfo_InternetPhone else null end as Person_InetPhone,
					case when peh.PersonEncrypHIV_Encryp is not null or laf.Lpu_id is null then null
					when a1.Address_id is not null then a1.Address_Address else a.Address_Address
					end as Address_Address,
					case when peh.PersonEncrypHIV_Encryp is not null or laf.Lpu_id is null then null
					when a1.Address_id is not null then a1.KLTown_id else a.KLTown_id
					end as KLTown_id,
					case when peh.PersonEncrypHIV_Encryp is not null or laf.Lpu_id is null then null
					when a1.Address_id is not null then a1.KLStreet_id else a.KLStreet_id
					end as KLStreet_id,
					case when peh.PersonEncrypHIV_Encryp is not null or laf.Lpu_id is null then null
					when a1.Address_id is not null then a1.Address_House else a.Address_House
					end as Address_House,".
					//ГАУЗ РВФД, #116387, gilmiyarov_25092017
					(((isSuperadmin() || $param['Lpu_id'] == 81) && $this->getRegionNick() == "ufa")
						? "(select pp.post_name from v_PersonState (nolock) vps , job (nolock) jj, post (nolock) pp where vps.person_id=t.person_id and vps.job_id=jj.job_id and jj.post_id=pp.Post_id) as Job_Name,"
						: "case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then j.Job_Name else null end as Job_Name,").
					"case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then lpu.Lpu_Nick else null end as Lpu_Nick,
					case when laf.Lpu_id is null then ''
						when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then rtrim(p.Person_Surname)
						else rtrim(peh.PersonEncrypHIV_Encryp)
					end as Person_Surname,
					case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then rtrim(p.Person_Firname) else '' end as Person_Firname,
					case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then rtrim(p.Person_Secname) else '' end as Person_Secname,
					case when laf.Lpu_id is null then '1' else '0' end as Person_Filter,";
		}
		// Для Астрахани выводится информация о полисе
		$selectPolis = "";
		$joinPolis = "";
		if ($this->getRegionNick() == "astra") {
			$selectPolis = "case when pol.PolisType_id = 4 then '' else pol.Polis_Ser end as Polis_Ser,
					case when pol.PolisType_id = 4 then p.Person_EdNum else pol.Polis_Num end as Polis_Num,";
			$joinPolis = "left join v_polis pol (nolock) on pol.polis_id = p.polis_id";
		}

		$sql = "
			select
					t.pmUser_updID,
					t.TimetableGraf_updDT,
					t.TimetableGraf_id,
					t.Person_id,
					t.TimetableGraf_Day,
					t.TimetableGraf_begTime as TimetableGraf_begTime,
					t.TimetableType_id,
					t.TimetableGraf_IsDop,
					t.TimeTableGraf_countRec,
					t.TimeTableGraf_PersRecLim,
					p.PrivilegeType_id,
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
					ISNULL(cast(et.ElectronicTalon_Num as varchar), cast(ED.EvnDirection_TalonCode as varchar)) as Direction_TalonCode,
					t.EvnDirection_id as EvnDirection_tid,
					convert(varchar(10),d.EvnDirection_setDate,104) as Direction_Date,
					d.EvnDirection_id as EvnDirection_id,
					qp.pmUser_Name as QpmUser_Name,
					q.EvnQueue_insDT as EvnQueue_insDT,
					dg.Diag_Code,
					u.Lpu_id as pmUser_Lpu_id,
					t.TimetableGraf_IsModerated,
					msf.Lpu_id,
					TimetableExtend_Descr,
					t.TimetableExtend_updDT,
					ud.pmUser_Name as TimetableExtend_pmUser_Name,
					msf.MedStaffFact_id,
					msf.MedPersonal_id,
					msf.LpuUnit_id,
					{$selectPolis}
					case when t.TimetableGraf_factTime is not null then 2 else 1 end as Person_IsPriem
				from v_TimetableGraf t with (nolock)
				left outer join v_MedStaffFact_ER msf with (nolock) on msf.MedStaffFact_id = t.MedStaffFact_id
				left outer join v_Person_ER p with (nolock) on t.Person_id = p.Person_id
				outer apply (
					Select top 1 PersonCardState_Code, PersonCard_id
					from PersonCardState pcs (nolock)
					where pcs.Person_id = p.Person_id and pcs.Lpu_id = msf.Lpu_id
					order by LpuAttachType_id
				) pcs
				outer apply(
					select top 1 Lpu_id
					from v_PersonCardState
					where Person_id = p.Person_id
					order by LpuAttachType_id
				) pcs_l
				--left outer join PersonCardState pcs (nolock) on pcs.Person_id = p.Person_id and LpuAttachType_id = 1
				left outer join Address a with (nolock) on p.UAddress_id = a.Address_id
				left outer join Address a1 with (nolock) on p.PAddress_id = a1.Address_id
				left outer join KLStreet pas with (nolock) on a.KLStreet_id = pas.KLStreet_id
				left outer join KLStreet pas1 with (nolock) on a1.KLStreet_id = pas1.KLStreet_id
				left outer join v_Job_ER j with (nolock) on p.Job_id=j.Job_id
				left outer join v_pmUser u with (nolock) on t.PMUser_UpdID = u.PMUser_id
				left outer join v_pmUser ud with (nolock) on t.TimetableExtend_pmUser_updid = ud.PMUser_id
				left outer join v_Lpu lpu with (nolock) on lpu.Lpu_id = pcs_l.Lpu_id
				left outer join v_EvnDirection d with (nolock) on
					t.TimetableGraf_id=d.TimetableGraf_id
					and d.DirFailType_id is null
					and d.Person_id = t.Person_id
				left outer join v_Lpu lpud with (nolock) ON lpud.Lpu_id = d.Lpu_id
				left join v_EvnQueue q with (nolock) on t.TimetableGraf_id = q.TimetableGraf_id and t.Person_id = q.Person_id
				left join v_pmUser qp with (nolock) on q.pmUser_updId=qp.pmUser_id
				left join Diag dg with (nolock) on dg.Diag_id=d.Diag_id
				left join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = t.EvnDirection_id
				left join v_ElectronicTalon et (nolock) on et.EvnDirection_id = t.EvnDirection_id
				{$joinPolis}
				{$joinPersonEncrypHIV}
				{$joinAccessFilter}
				where t.TimetableGraf_Day = :StartDay
					and cast(t.TimetableGraf_begTime as date) <= :EndDate
					and t.MedStaffFact_Id = :MedStaffFact_id
					and t.TimetableGraf_begTime is not null
				order by t.TimetableGraf_begTime";

		$res = $this->db->query( $sql,
			$param );

		//echo getDebugSql($sql, $param);

		$ttgdata = $res->result( 'array' );


		foreach ( $ttgdata as $ttg ) {
			$outdata['data'][] = $ttg;
		}

		$sql = "
			select TimetableGraf_id from TimetableLock with(nolock) where TimetableGraf_id is not null";

		$res = $this->db->query( $sql );

		$outdata['reserved'] = array();
		$reserved = $res->result( 'array' );
		foreach ( $reserved as $lock ) {
			$outdata['reserved'][] = $lock['TimetableGraf_id'];
		}

		return $outdata;
	}

	/**
	 * Удаление бирки (поликлиника/параклиника/стационар)
	 */
	function Delete( $data ) {
		if ( isset( $data['TimetableGraf_id'] ) || isset( $data['TimetableGrafGroup'] ) ) {
			$data['object'] = 'TimetableGraf';
		}
		if ( isset( $data['TimetableStac_id'] ) || isset( $data['TimetableStacGroup'] ) ) {
			$data['object'] = 'TimetableStac';
		}


		switch ( $data['object'] ) {
			case 'TimetableGraf':
				return $this->DeleteTTG( $data );
				break;
			case 'TimetableStac':
				$this->load->model( 'TimetableStac_model',
					'ttsmodel' );
				return $this->ttsmodel->DeleteTTS( $data );
				break;
			default:
				return array(
					'Error_Msg' => 'Неизвестная бирка.'
				);
		}
	}

	/**
	 * Удаление бирки в поликлинике
	 */
	function DeleteTTG( $data ) {

		if ( isset( $data['TimetableGraf_id'] ) ) {
			$data['TimetableGrafGroup'] = array($data['TimetableGraf_id']);
		} else {
			$data['TimetableGrafGroup'] = json_decode( $data['TimetableGrafGroup'] );
		}

		if ( true !== ($res = $this->checkTimetablesFree( $data )) ) {
			return $res;
		}

		// Получаем врача и список дней, на которые мы выделили бирки
		$res = $this->db->query( "
			select
					TimetableGraf_id,
					MedStaffFact_id,
					TimetableGraf_Day
			from v_TimetableGraf_lite with (nolock)
			where TimetableGraf_id in (" . implode( ', ',
				$data['TimetableGrafGroup'] ) . ")"
		);

		if ( is_object( $res ) ) {
			$res = $res->result( 'array' );
		} else {
			return false;
		}
		// Удаляем каждую бирку по отдельности. Не лучший вариант конечно
		foreach ( $res as $row ) {

			$MedStaffFact_id = $row['MedStaffFact_id'];
			$Day = $row['TimetableGraf_Day'];

			//Удаляем бирку
			$sql = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec p_TimetableGraf_del
					@TimetableGraf_id = :TimetableGraf_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$res = $this->db->query(
				$sql,
				array(
				'TimetableGraf_id' => $row['TimetableGraf_id'],
				'pmUser_id' => $data['pmUser_id']
				)
			);

			if ( is_object( $res ) ) {
				$resp = $res->result( 'array' );

				if ( is_array( $resp ) && count( $resp ) > 0 && empty( $resp[0]['Error_Msg'] ) ) {
					// отправка STOMP-сообщения
					sendFerStompMessage( array(
						'id' => $row['TimetableGraf_id'],
						'timeTable' => 'TimetableGraf',
						'action' => 'DelTicket',
						'setDate' => date( "c" )
						),
						'Rule' );
				} else {
					return $resp;
				}
			}
			// Пересчет теперь прямо в хранимке удаления
		}

		return array(
			'success' => true
		);
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) занятые бирки для поликлиники
	 */
	function checkTimetableGrafDayNotOccupied( $data ) {
		if ( isset( $data['Day'] ) ) {
			$sql = "
				SELECT count(*) as cnt
				FROM v_TimetableGraf_lite with (nolock)
				WHERE
					TimetableGraf_Day = :Day
					and MedStaffFact_id = :MedStaffFact_id
					and Person_id is not null
					and TimetableGraf_begTime is not null
			";

			$res = $this->db->query(
				$sql,
				array(
				'Day' => $data['Day'],
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				)
			);
		}
		if ( isset( $data['StartDay'] ) ) {
			$sql = "
				SELECT count(*) as cnt
				FROM v_TimetableGraf_lite with (nolock)
				WHERE
					TimetableGraf_day between :StartDay and :EndDay
					and MedStaffFact_id = :MedStaffFact_id
					and Person_id is not null
					and TimetableGraf_begTime is not null
			";

			$res = $this->db->query(
				$sql,
				array(
				'StartDay' => $data['StartDay'],
				'EndDay' => $data['EndDay'],
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				)
			);
		}
		if ( is_object( $res ) ) {
			$res = $res->result( 'array' );
		}
		if ( $res[0]['cnt'] > 0 ) {
			return false;
		}
		return true;
	}

	/**
	 * Проверка, работает ли сотрудник в указанные дни
	 */
	function checkTimetableGrafTimeMsfIsWork( $data ) {

		if (isset($data['Day'])) { // Доп.бирка
			$date = date("Y-m-d", DayMinuteToTime($data['Day'], StringToTime($data['StartTime'])));
			$params = array(
				'StartDate' => $date,
				'EndDate' => $date
			);
		} elseif ($data['ScheduleCreationType'] == 2) { // Копирование
			$params = array(
				'StartDate' => $data['CopyToDateRange'][0],
				'EndDate' => $data['CopyToDateRange'][1]
			);
		} else { // Создание
			$params = array(
				'StartDate' => $data['CreateDateRange'][0],
				'EndDate' => $data['CreateDateRange'][1]
			);
		}

		$params['MedStaffFact_id'] = $data['MedStaffFact_id'];

		$sql = "
			select
				count(*) as cnt
			from v_MedStaffFact with (nolock)
			where
				MedStaffFact_id = :MedStaffFact_id and
				WorkData_begDate <= :StartDate and
				isnull(cast(WorkData_endDate as date), '2030-01-01') >= :EndDate
		";

		$res = $this->db->query($sql,$params);
		
		if ( is_object( $res ) ) {
			$res = $res->result( 'array' );
		}
		
		if ( $res[0]['cnt'] == 0 ) {
			$errtext = isset($data['Day']) 
				? 'Добавление дополнительной бирки невозможно: дата, на которую добавляется бирка, не входит в период работы сотрудника'
				: 'Создание расписания невозможно: дата начала/окончания расписания не входит в период работы сотрудника';
			return array('Error_Msg' => $errtext);
		}
		
		return true;
	}

	/**
	 * Проверка, есть ли на заданном дне(или интервале дней) и интервале времени занятые бирки для поликлиники
	 */
	function checkTimetableGrafTimeNotOccupied( $data ) {


		if ( isset( $data['Day'] ) ) {
			$sql = "
				select
					count(*) as cnt
				from v_TimetableGraf_lite with (nolock)
				where
					MedStaffFact_id = :MedStaffFact_id
					and Person_id is not null
					and (
						( TimetableGraf_begTime >= :StartTime and TimetableGraf_begTime < :EndTime )
						or ( DATEADD( minute, TimetableGraf_Time, TimetableGraf_begTime  ) > :StartTime and DATEADD( minute, TimetableGraf_Time, TimetableGraf_begTime  ) < :EndTime )
						or ( DATEADD( minute, TimetableGraf_Time, TimetableGraf_begTime  ) > :StartTime and TimetableGraf_begTime < :StartTime )
					)";

			$res = $this->db->query(
				$sql,
				array(
				'StartTime' => date( "Y-m-d H:i:s",
					DayMinuteToTime( $data['Day'],
						StringToTime( $data['StartTime'] ) ) ),
				'EndTime' => date( "Y-m-d H:i:s",
					DayMinuteToTime( $data['Day'],
						StringToTime( $data['EndTime'] ) ) ),
				'MedStaffFact_id' => $data['MedStaffFact_id']
				)
			);
			if ( is_object( $res ) ) {
				$res = $res->result( 'array' );
			}
			if ( $res[0]['cnt'] > 0 ) {
				return array(
					'Error_Msg' => 'Нельзя очистить расписание, так как есть занятые бирки.'
				);
			}
		}

		//Если задано несколько дней - проходим в цикле
		if ( isset( $data['StartDay'] ) ) {
			for ( $day = $data['StartDay']; $day <= $data['EndDay']; $day ++ ) {
				$data['Day'] = $day;
				$sql = "
					select
						count(*) as cnt
					from v_TimetableGraf_lite with (nolock)
					where
						MedStaffFact_id = :MedStaffFact_id
						and Person_id is not null
						and (
							( TimetableGraf_begTime >= :StartTime and TimetableGraf_begTime < :EndTime )
							or ( DATEADD( minute, TimetableGraf_Time, TimetableGraf_begTime  ) > :StartTime and DATEADD( minute, TimetableGraf_Time, TimetableGraf_begTime  ) < :EndTime )
							or ( DATEADD( minute, TimetableGraf_Time, TimetableGraf_begTime  ) > :StartTime and TimetableGraf_begTime < :StartTime )
						)";

				$res = $this->db->query(
					$sql,
					array(
					'StartTime' => date( "Y-m-d H:i:s",
						DayMinuteToTime( $data['Day'],
							StringToTime( $data['StartTime'] ) ) ),
					'EndTime' => date( "Y-m-d H:i:s",
						DayMinuteToTime( $data['Day'],
							StringToTime( $data['EndTime'] ) ) ),
					'MedStaffFact_id' => $data['MedStaffFact_id']
					)
				);
				if ( is_object( $res ) ) {
					$res = $res->result( 'array' );
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
	 * Проверка, есть ли на заданном дне(или интервале дней) и интервале времени созданные бирки для поликлиники
	 */
	function checkTimetableGrafTimeNotExists( $data ) {


		if ( isset( $data['Day'] ) ) {
			$sql = "
				select
					count(*) as cnt
				from v_TimetableGraf_lite with (nolock)
				where
					MedStaffFact_id = :MedStaffFact_id
					and (
						( TimetableGraf_begTime >= :StartTime and TimetableGraf_begTime < :EndTime )
						or ( DATEADD( minute, TimetableGraf_Time, TimetableGraf_begTime  ) > :StartTime and DATEADD( minute, TimetableGraf_Time, TimetableGraf_begTime  ) < :EndTime )
						or ( DATEADD( minute, TimetableGraf_Time, TimetableGraf_begTime  ) > :StartTime and TimetableGraf_begTime < :StartTime )
					)";

			$res = $this->db->query(
				$sql,
				array(
				'StartTime' => date( "Y-m-d H:i:s",
					DayMinuteToTime( $data['Day'],
						StringToTime( $data['StartTime'] ) ) ),
				'EndTime' => date( "Y-m-d H:i:s",
					DayMinuteToTime( $data['Day'],
						StringToTime( $data['EndTime'] ) ) ),
				'MedStaffFact_id' => $data['MedStaffFact_id']
				)
			);
			if ( is_object( $res ) ) {
				$res = $res->result( 'array' );
			}
			if ( $res[0]['cnt'] > 0 ) {
				return array(
					'Error_Msg' => 'В заданном интервале времени уже существуют бирки.'
				);
			}
		}

		//Если задано несколько дней - проходим в цикле
		if ( isset( $data['StartDay'] ) ) {
			for ( $day = $data['StartDay']; $day <= $data['EndDay']; $day ++ ) {
				$data['Day'] = $day;
				$sql = "
					select
						count(*) as cnt
					from v_TimetableGraf_lite with (nolock)
					where
						MedStaffFact_id = :MedStaffFact_id
						and (
							( TimetableGraf_begTime >= :StartTime and TimetableGraf_begTime < :EndTime )
							or ( DATEADD( minute, TimetableGraf_Time, TimetableGraf_begTime  ) > :StartTime and DATEADD( minute, TimetableGraf_Time, TimetableGraf_begTime  ) < :EndTime )
							or ( DATEADD( minute, TimetableGraf_Time, TimetableGraf_begTime  ) > :StartTime and TimetableGraf_begTime < :StartTime )
						)";

				$res = $this->db->query(
					$sql,
					array(
					'StartTime' => date( "Y-m-d H:i:s",
						DayMinuteToTime( $data['Day'],
							StringToTime( $data['StartTime'] ) ) ),
					'EndTime' => date( "Y-m-d H:i:s",
						DayMinuteToTime( $data['Day'],
							StringToTime( $data['EndTime'] ) ) ),
					'MedStaffFact_id' => $data['MedStaffFact_id']
					)
				);
				if ( is_object( $res ) ) {
					$res = $res->result( 'array' );
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
	 * Проверка, что врач принимает по живой очереди
	 */
	function checkMsfIsLifeQueue( $data ) {
		
		$sql = "
			select
				count(*) as cnt
			from v_MedStaffFact with (nolock)
			where
				MedStaffFact_id = :MedStaffFact_id and 
				RecType_id = 3";

		$res = $this->db->query($sql, array('MedStaffFact_id' => $data['MedStaffFact_id']));
		if ( is_object( $res ) ) {
			$res = $res->result( 'array' );
		}
		if ( $res[0]['cnt'] > 0 ) {
			return array(
				'Error_Msg' => 'При создании расписания для места работы с типом записи «По живой очереди» возможно добавить только бирки с типом «Живая очередь»'
			);
		}

		return true;
	}

	/**
	 * Очистка дня
	 */
	function ClearDay( $data ) {
		// Не выдаём ошибку, что есть занятые бирки,
		// вместо этого хранимка будет удалять только свободные бирки
		/* if ( true !== ($res = $this->checkTimetableGrafDayNotOccupied($data)) ) {
		  return $res;
		  } */
		return $this->ClearDayTTG( $data );
	}

	/**
	 * Очистка дня для поликлиники
	 */
	function ClearDayTTG( $data ) {

		$sql = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			exec p_TimetableGraf_clearDay
				@TimetableGraf_Day = :TimetableGraf_Day,
				@MedStaffFact_id = :MedStaffFact_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output
			select @Res as TimetableGraf_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$res = $this->db->query(
			$sql,
			array(
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'TimetableGraf_Day' => $data['Day'],
			'pmUser_id' => $data['pmUser_id']
			)
		);

		if ( is_object( $res ) ) {
			$resp = $res->result( 'array' );
			if ( count( $resp ) > 0 && empty( $resp[0]['Error_Msg'] ) ) {
				// отправка STOMP-сообщения
				sendFerStompMessage( array(
					'timeTable' => 'TimetableGraf',
					'action' => 'DelTicket',
					'setDate' => date( "c" ),
					'begDate' => date( "c",
						DayMinuteToTime( $data['Day'],
							0 ) ),
					'endDate' => date( "c",
						DayMinuteToTime( $data['Day'],
							0 ) ),
					'MedStaffFact_id' => $data['MedStaffFact_id']
					),
					'RulePeriod' );
			}
		}
		// Пересчет теперь прямо в хранимке очистки дня

		return array(
			'Error_Msg' => ''
		);
	}

	/**
	 * Создания расписания в поликлинике
	 */
	function createTTGSchedule( $data ) {
	
		$this->beginTransaction();
		if ($data['CreateAnnotation'] == 1) {
			$this->load->model('Annotation_model');
			$annotation_data = $data;
			$annotation_data['Annotation_id'] = null;
			$annotation_data['MedService_id'] = null;
			$annotation_data['Resource_id'] = null;
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

		$data['StartDay'] = TimeToDay( strtotime( $data['CreateDateRange'][0] ) );
		$data['EndDay'] = TimeToDay( strtotime( $data['CreateDateRange'][1] ) );

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

		if ( true !== ($res = $this->checkTimetableGrafTimeMsfIsWork( $data )) ) {
			return $res;
		}
		if ( true !== ($res = $this->checkTimetableGrafTimeNotOccupied( $data )) ) {
			return $res;
		}
		if ( true !== ($res = $this->checkTimetableGrafTimeNotExists( $data )) ) {
			return $res;
		}
		if ( 12 != $data['TimetableType_id'] && true !== ($res = $this->checkMsfIsLifeQueue( $data )) ) {
			return $res;
		}

		$ttgArray = array();

		$nStartTime = StringToTime( $data['StartTime'] );
		$nEndTime = StringToTime( $data['EndTime'] );

		for ( $day = $data['StartDay']; $day <= $data['EndDay']; $day ++ ) {
			$data['Day'] = $day;

			// Вставляем циклом бирки
			for( $nTime = $nStartTime; $nTime < $nEndTime; $nTime += $data['Duration'] ) {
				// Для групповых бирок нужен флаг множественности
				if(!empty($data['TimetableType_id']) && $data['TimetableType_id'] == 14)
					$data['TimeTableGraf_IsMultiRec'] = 2;

				$resp = $this->queryResult("
					declare
						@TimetableGraf_Guid UNIQUEIDENTIFIER = null
    				
    				set nocount on;
    				
    				set @TimetableGraf_Guid = newid();
					insert into dbo.TimetableGraf with (ROWLOCK) (MedStaffFact_id, Person_id, TimetableGraf_Day, TimetableGraf_begTime, TimetableGraf_factTime, TimetableGraf_Time, TimetableGraf_IsDop, TimetableGraf_IsModerated, RecClass_id, TimetableType_id, Evn_id, TimetableGraf_Mark,TimetableGraf_Guid, pmUser_insID, pmUser_updID, TimetableGraf_insDT, TimetableGraf_updDT, TimeTableGraf_IsMultiRec, TimeTableGraf_PersRecLim)
					values (:MedStaffFact_id, null, :TimetableGraf_Day, :TimetableGraf_begTime, null, :TimetableGraf_Time, null, null, null, :TimetableType_id, null, null, @TimetableGraf_Guid, :pmUser_id, :pmUser_id, GetDate(), GetDate(), :TimeTableGraf_IsMultiRec, :TimeTableGraf_PersRecLim);
					
					set nocount off;
					
					select scope_identity() as TimetableGraf_id;
				", array(
					'MedStaffFact_id' => $data['MedStaffFact_id'],
					'TimetableGraf_Day' => $day,
					'TimetableGraf_begTime' => date("Y-m-d H:i:s", DayMinuteToTime( $day, $nTime )),
					'TimetableGraf_Time' => $data['Duration'],
					'pmUser_id' => $data['pmUser_id'],
					'TimetableType_id' => $data['TimetableType_id'],
					'TimeTableGraf_IsMultiRec' => (!empty($data['TimeTableGraf_IsMultiRec']) ? $data['TimeTableGraf_IsMultiRec'] : null),
					'TimeTableGraf_PersRecLim' => (!empty($data['TimeTableGraf_PersRecLim']) ? $data['TimeTableGraf_PersRecLim'] : null),
				));

				if (!empty($resp[0]['TimetableGraf_id'])) {
					$ttgArray[] = $resp[0]['TimetableGraf_id'];
				} else {
					$this->rollbackTransaction();
					return array('Error_Msg' => 'Ошибка при вставке бирки');
				}
			}

			// Обновляем кэш по дню
			$this->db->query(" 
				exec p_MedPersonalDay_recount
					@MedStaffFact_id = :MedStaffFact_id,
					@Day_id = :TimetableGraf_Day,
					@pmUser_id = :pmUser_id
			", array(
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'TimetableGraf_Day' => $day,
				'pmUser_id' => $data['pmUser_id']
			));
		}

		$ttg_list = $ttgArray;

		// Вставляем данные в историю (аналог p_AddTTGToHistory).
		while (count($ttgArray) > 0) {
			// берём пачками, чтобы идешников не получилось слишком много.
			$ttgIds = array_splice($ttgArray, 0, 1000);
			// #147876 при создании бирок создавать не нужно, вся история есть в бирке
			/*$this->db->query("
				insert into TimeTableGrafHist with (rowlock)
					(TimeTableGrafHist_userID,TimeTableGrafHist_InsDT,TimeTableGrafAction_id,TimeTableGraf_id,MedStaffFact_id,TimetableGraf_Day,Person_id,TimetableGraf_begTime,TimetableGraf_Time,PMUser_InsID,PMUser_UpdID,TimetableGraf_InsDT,TimetableGraf_UpdDT,TimeTableType_id,TimetableGraf_IsDop,EvnDirection_id,TimeTableGrafHist_IsMultiRec,TimeTableGrafHist_PersRecLim)
				select
					:pmUser_id,GETDATE(),1,TimeTableGraf_id,MedStaffFact_id,TimetableGraf_Day,Person_id,TimetableGraf_begTime,TimetableGraf_Time,PMUser_InsID,PMUser_UpdID,TimetableGraf_InsDT,TimetableGraf_UpdDT,TimeTableType_id,TimeTableGraf_isDop,EvnDirection_id,TimeTableGraf_IsMultiRec,TimeTableGraf_PersRecLim
				from
					v_TimetableGraf_lite with (nolock)
				where
					TimetableGraf_id in ('".implode("','", $ttgIds)."')
			", array(
				'pmUser_id' => $data['pmUser_id']
			));
			*/
			if (getRegionNick() == 'ekb') {
				$this->db->query("
					declare @Lpu_id bigint;
					select @Lpu_id = Lpu_id FROM dbo.v_MedStaffFact WITH (NOLOCK) WHERE MedStaffFact_id=:MedStaffFact_id;
			
					insert into dbo.TimeTableGrafHistMIS with (rowlock)
						( TimeTableGraf_id ,Lpu_id ,MedStaffFact_id ,Person_id ,TimeTableGraf_Day ,TimeTableGraf_begTime ,TimeTableGraf_factTime ,TimeTableGraf_Time ,RecClass_id ,TimeTableType_id ,EvnDirection_id ,TimeTableGrafAction_id ,pmUser_insID ,pmUser_updID ,TimeTableGraf_insDT ,TimeTableGraf_updDT)
					select
						TimeTableGraf_id , @Lpu_id ,MedStaffFact_id ,Person_id ,TimeTableGraf_Day ,TimeTableGraf_begTime , TimetableGraf_factTime , TimetableGraf_Time ,RecClass_id ,TimeTableType_id ,EvnDirection_id ,1 ,pmUser_insID ,pmUser_updID ,TimeTableGraf_insDT ,TimeTableGraf_updDT
					from
						v_TimetableGraf_lite with (nolock)
					where
						TimetableGraf_id in ('".implode("','", $ttgIds)."')
				", array(
					'MedStaffFact_id' => $data['MedStaffFact_id']
				));
			}
		}
			
		$this->commitTransaction();

		// отправка STOMP-сообщения
		sendFerStompMessage( array(
			'timeTable' => 'TimetableGraf',
			'action' => 'AddTicket',
			'setDate' => date( "c" ),
			'begDate' => date( "c",
				DayMinuteToTime( $data['StartDay'],
					$nStartTime ) ),
			'endDate' => date( "c",
				DayMinuteToTime( $data['EndDay'],
					$nEndTime ) ),
			'MedStaffFact_id' => $data['MedStaffFact_id']
			),
			'RulePeriod' );

		$response = array('Error_Msg' => '');

		if (!empty($data['fromApi']) && !empty($ttg_list)) {
			$response['ttg_list'] = $ttg_list;
		}

		return $response;
	}

	/**
	 * Копирование расписания в поликлинике
	 */
	function copyTTGSchedule( $data ) {

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

		if ( true !== ($res = $this->checkTimetableGrafTimeMsfIsWork( $data )) ) {
			return $res;
		}
		if ( true !== ($res = $this->checkTimetableGrafDayNotOccupied( $data )) ) {
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
				exec p_TimetableGraf_copy
					@MedStaffFact_id = :MedStaffFact_id,
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
				$sql,
				array(
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'SourceStartDay' => $SourceStartDay,
				'SourceEndDay' => $SourceEndDay,
				'TargetStartDay' => $nTargetStart,
				'TargetEndDay' => $nTargetEnd,
				'CopyTimetableExtend_Descr' => NULL,
				'pmUser_id' => $data['pmUser_id']
				)
			);

			if ( is_object( $res ) ) {
				$resp = $res->result( 'array' );
				$this->commitTransaction();
				if ( count( $resp ) > 0 && empty( $resp[0]['Error_Msg'] ) ) {
					// отправка STOMP-сообщения
					sendFerStompMessage( array(
						'timeTable' => 'TimetableGraf',
						'action' => 'AddTicket',
						'setDate' => date( "c" ),
						'begDate' => date( "c",
							DayMinuteToTime( $nTargetStart,
								0 ) ),
						'endDate' => date( "c",
							DayMinuteToTime( $nTargetEnd,
								0 ) ),
						'MedStaffFact_id' => $data['MedStaffFact_id']
						),
						'RulePeriod' );
				}
			}
			
			$n++;
		}

		return array(
			'Error_Msg' => ''
		);
	}

	/**
	 * Добавление дополнительной бирки в поликлинике
	 */
	function addTTGDop( $data ) {

		if ( !empty( $data['StartTime'] ) ) {
			$archive_database_enable = $this->config->item('archive_database_enable');
			if (!empty($archive_database_enable)) {
				$archive_database_date = $this->config->item('archive_database_date');
				if (DayMinuteToTime( $data['Day'], StringToTime( $data['StartTime'] )) < strtotime($archive_database_date)) {
					return array(
						'Error_Msg' => 'Нельзя создать дополнительную бирку на архивную дату.'
					);
				}
			}

			$date = date( "Y-m-d H:i:s",
				DayMinuteToTime( $data['Day'],
					StringToTime( $data['StartTime'] ) ) );

			$sql = "
				select
					count(*) as cnt
				from v_TimetableGraf_lite with (nolock)
				where
					MedStaffFact_id = :MedStaffFact_id
					and TimetableGraf_begTime > DATEADD( minute, -2, :StartTime)
					and TimetableGraf_begTime < DATEADD( minute, 2, :StartTime)
					and TimetableGraf_begTime is not null
			";
			$res = $this->db->query(
				$sql,
				array(
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'StartTime' => $date
				)
			);

			if ( is_object( $res ) ) {
				$res = $res->result( 'array' );
			} else {
				return false;
			}
		} else {
			// Если бирка создается на текущее время, проверка на то что она отстоит от других бирок не происходит
			$date = date( "Y-m-d H:i:s",
				time() );
			$res = array(
				array(
					'cnt' => 0
				)
			);
		}
		
		if ( true !== ($res = $this->checkTimetableGrafTimeMsfIsWork( $data )) ) {
			return $res;
		}

		$this->beginTransaction();
		if ($data['CreateAnnotation'] == 1) {
			$this->load->model('Annotation_model');
			$annotation_data = $data;
			$annotation_data['Annotation_id'] = null;
			$annotation_data['MedService_id'] = null;
			$annotation_data['Resource_id'] = null;
			$annotation_data['Annotation_begDate'] = date("Y-m-d", DayMinuteToTime($data['Day'], 0));
			$annotation_data['Annotation_endDate'] = date("Y-m-d", DayMinuteToTime($data['Day'], 0));
			$annotation_data['Annotation_begTime'] = $data['StartTime'];
			$annotation_data['Annotation_endTime'] = date("H:i", DayMinuteToTime($data['Day'], StringToTime($data['StartTime'])) + 60);
			$ares = $this->Annotation_model->save($annotation_data);
			if (!empty($ares['Error_Msg'])) {
				$this->rollbackTransaction();
				return $ares;
			}
		}

		if ( $res[0]['cnt'] == 0 ) {
			$sql = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = null;

				exec p_TimetableGraf_ins
					@TimetableGraf_id = @Res output,
					@MedStaffFact_id = :MedStaffFact_id,
					@TimetableGraf_Day = :TimetableGraf_Day,
					@TimetableGraf_begTime = :TimetableGraf_begTime,
					@TimetableGraf_Time = :TimetableGraf_Time,
					@TimetableType_id = 1,
					@TimetableGraf_IsDop = 1,
					@pmUser_id = :pmUser_id
				select @Res as TimetableGraf_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;";

			$res = $this->db->query(
				$sql,
				array(
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'TimetableGraf_Day' => $data['Day'],
				'TimetableGraf_begTime' => $date,
				'TimetableGraf_Time' => 0,
				'pmUser_id' => $data['pmUser_id']
				)
			);

			if ( is_object( $res ) ) {
				$resp = $res->result( 'array' );
				$this->commitTransaction();
				if ( !empty( $resp[0]['TimetableGraf_id'] ) ) {
					// отправка STOMP-сообщения
					sendFerStompMessage( array(
						'id' => $resp[0]['TimetableGraf_id'],
						'timeTable' => 'TimetableGraf',
						'action' => 'AddTicket',
						'setDate' => date( "c" )
						),
						'Rule' );

					// Пересчет теперь прямо в хранимке
					return array(
						'Error_Msg' => '', 'TimetableGraf_id' => $resp[0]['TimetableGraf_id']
					);
				} else {
					return array(
						'Error_Msg' => 'Ошибка добавления бирки'
					);
				}
			}
		} else {
			return array(
				'Error_Msg' => 'Дополнительная бирка должна отстоять не менее чем на 2 минуты от существующих. Выберите другое время или удалите бирки.'
			);
		}
	}

	/**
	 * Удаление бирки незапланированного приема в поликлинике
	 */
	function rollbackUnScheduled( $data ) {
		if ( empty( $data['Unscheduled'] ) ) {
			return true;
		}
		if ( 'polka' != $data['LpuUnitType_SysNick'] ) {
			return true;
		}
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_TimetableGraf_del
				@TimetableGraf_id = :TimetableGraf_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$this->db->query( $query,
			array(
			'TimetableGraf_id' => $data['TimetableGraf_id'],
			'pmUser_id' => $data['pmUser_id']
		) );
		return true;
	}

	/**
	 * Добавление незапланированного приема в поликлинике
	 */
	function addTTGUnscheduled( $data ) {

		$sql = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = null;

			exec p_TimetableGraf_ins
				@TimetableGraf_id = @Res output,
				@MedStaffFact_id = :MedStaffFact_id,
				@TimetableGraf_Day = :TimetableGraf_Day,
				@TimetableGraf_begTime = :TimetableGraf_begTime,
				@TimetableGraf_Time = :TimetableGraf_Time,
				@TimetableType_id = 1,
				@TimetableGraf_factTime = null,
				@TimetableGraf_IsDop = 1,
				@pmUser_id = :pmUser_id
			select @Res as TimetableGraf_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;";

		$res = $this->db->query(
			$sql,
			array(
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'TimetableGraf_Day' => $data['Day'],
			'TimetableGraf_begTime' => date("Y-m-d H:i:s", time()),
			'TimetableGraf_Time' => 0,
			'pmUser_id' => $data['pmUser_id']
			)
		);

		if ( is_object( $res ) ) {
			$res = $res->result( 'array' );
			if ( !empty( $res[0]['TimetableGraf_id'] ) ) {
				// отправка STOMP-сообщения
				sendFerStompMessage( array(
					'id' => $res[0]['TimetableGraf_id'],
					'timeTable' => 'TimetableGraf',
					'action' => 'AddTicket',
					'setDate' => date( "c" )
					),
					'Rule' );

				return array(
					'Error_Msg' => '', 'TimetableGraf_id' => $res[0]['TimetableGraf_id']
				);
			} else {
				return array(
					'Error_Msg' => 'Ошибка добавления бирки'
				);
			}
		}
	}

	/**
	 * Получение названия действия для отправки в ФЭР
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
	 * Получение типа бирки и id MO
	 * @param $data
	 */
	function getTTGType($data)
	{
		$query = "
			select top 1
				TTG.TimeTableType_id,
				MSF.Lpu_id
			from
				v_TimetableGraf_lite TTG with (nolock)
			left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = TTG.MedStaffFact_id
			where
				TTG.TimetableGraf_id = :TimetableGraf_id
		";

		$res = $this->db->query( $query,
				['TimetableGraf_id' => $data['TimetableGraf_id']]
		);

		if ( !is_object( $res ) ) {
			return false;
		}
		return $res->result( 'array' );
	}

    /**
     * Изменение типа бирки(бирок) в поликлинике
     *
     * @param $data
     * @return array|bool
     * @throws Exception
     */
	function setTTGType($data)
    {
        /**@var CI_DB_result $res */
        $data["object"] = "TimetableGraf";
        $data["TimetableGrafGroup"] = (isset($data["TimetableGrafGroup"])) ? json_decode($data["TimetableGrafGroup"]) : $data["TimetableGrafGroup"];
        if (count(@$data["TimetableGrafGroup"]) > 0) {
            // Обработка группы бирок в отдельном методе
            return $this->setTTGTypeGroup($data);
        } elseif (true === ($res = $this->checkTimetableOccupied($data, true))) {
            throw new Exception("Бирка занята, изменение типа невозможно.");
        }
        // Получаем врача и день, а также заодно проверяем, что бирка существует
        $sql = "
			select MedStaffFact_id
			      ,TimetableGraf_Day
			from v_TimetableGraf_lite with (nolock)
			where TimetableGraf_id = :TimetableGraf_id
        ";
        $res = $this->db->query($sql, ["TimetableGraf_id" => $data["TimetableGraf_id"]]);
        if (is_object($res)) {
            $res = $res->result("array");
        } else {
            return false;
        }
        if (!isset($res[0])) {
            throw new Exception("Бирка с таким идентификатором не существует.");
        }
        $sqlParams = [
            "TimetableGraf_id" => $data["TimetableGraf_id"],
            "TimetableType_id" => $data["TimetableType_id"],
            "TimetableType_id50" => $data["TimetableType_id"] + 50,
            "MedStaffFact_id" => $res[0]["MedStaffFact_id"],
            "Day_id" => $res[0]["TimetableGraf_Day"],
            "pmUser_id" => ($data["pmUser_id"] != 0) ? $data["pmUser_id"] : null
        ];
        $sql = "
            update TimetableGraf with (ROWLOCK)
            set	TimeTableType_id = :TimetableType_id
               ,pmUser_updID = :pmUser_id
               ,TimeTableGraf_updDT = GETDATE()
            where TimetableGraf_id = :TimetableGraf_id;
        ";
        if($data["TimetableType_id"] == 14) {
            $sql .= "
                update TimetableGraf with (ROWLOCK)
                set	TimeTableGraf_PersRecLim = 100
                   ,TimeTableGraf_IsMultiRec = 2
                   ,pmUser_updID = :pmUser_id
                   ,TimeTableGraf_updDT = GETDATE()
                where TimetableGraf_id = :TimetableGraf_id;
            ";
        } else {
            $sql .= "
                update TimetableGraf with (ROWLOCK)
                set	TimeTableGraf_PersRecLim=0
                   ,TimeTableGraf_IsMultiRec = 1
                   ,pmUser_updID = :pmUser_id
                   ,TimeTableGraf_updDT = GETDATE()
                where TimetableGraf_id = :TimetableGraf_id;
            ";
        }
        $sql .= "
            exec dbo.p_AddTTGToHistory
                @TimetableGraf_id = :TimetableGraf_id,
                @TimeTableGrafAction_id = :TimetableType_id50,
                @pmUser_id = :pmUser_id;
            exec p_MedPersonalDay_recount
                @MedStaffFact_id = :MedStaffFact_id,
                @Day_id = :Day_id,
                @pmUser_id = :pmUser_id
        ";
        $this->db->query($sql, $sqlParams);
        $ttType = $this->getFirstRowFromQuery("select TimetableType_SysNick, TimetableType_Name from v_TimetableType with (nolock) where TimeTableType_id=?", [$data["TimetableType_id"]]);
        if (!empty($ttType["TimetableType_SysNick"])) {
            $action = $this->defineActionTypeByTimetableType($ttType["TimetableType_SysNick"]);
            if (!empty($action)) {
                // отправка STOMP-сообщения
                sendFerStompMessage([
                    "id" => $data["TimetableGraf_id"],
                    "timeTable" => "TimetableGraf",
                    "action" => $action,
                    "setDate" => date("c")
                ], "Rule");
            }
        }
        return [
            "TimetableType_Name" => $ttType["TimetableType_Name"],
            "Error_Msg" => ""
        ];
    }

    /**
     * Изменение типа бирок в поликлинике для группы бирок
     *
     * @param $data
     * @return array|bool|CI_DB_result
     *
     * @throws Exception
     */
	function setTTGTypeGroup($data)
    {
        /**@var CI_DB_result $result */
        if (true !== ($res = $this->checkTimetablesFree($data))) {
            throw new Exception(@$res["Error_Msg"]);
        }
        // Получаем врача и список дней, на которые мы выделили бирки
        $TimetableGrafGroupString = implode(",", $data["TimetableGrafGroup"]);
        $sql = "
			select TimetableGraf_id
			      ,MedStaffFact_id
			      ,TimetableGraf_Day
			from v_TimetableGraf_lite with (nolock)
			where TimetableGraf_id in ({$TimetableGrafGroupString})
        ";
        $result = $this->db->query($sql);
        if (is_object($result)) {
            $result = $result->result("array");
        } else {
            return false;
        }

        $sql = "";
        $sqlParams = [
            "TimetableType_id" => $data["TimetableType_id"],
            "pmUser_id" => $data["pmUser_id"],
            "TimetableType_id50" => $data["TimetableType_id"] + 50,
        ];
        $sendData = [];
        foreach ($result as $row) {
            $TimetableGraf_id = $row["TimetableGraf_id"];
            $MedStaffFact_id = $row["MedStaffFact_id"];
            $Day_id = $row["TimetableGraf_Day"];

            $sql .= "
                update TimetableGraf with (ROWLOCK)
                set	TimeTableType_id = :TimetableType_id
                   ,pmUser_updID = :pmUser_id
                   ,TimeTableGraf_updDT = GETDATE()
                where TimetableGraf_id = {$TimetableGraf_id};
            ";
            if ($data["TimetableType_id"] == 14) {
                $sql .= "
                    update TimetableGraf with (ROWLOCK)
                    set	TimeTableGraf_PersRecLim = 100
                       ,TimeTableGraf_IsMultiRec = 2
                       ,pmUser_updID = :pmUser_id
                       ,TimeTableGraf_updDT = GETDATE()
                    where TimetableGraf_id = {$TimetableGraf_id};
                ";
            } else {
                $sql .= "
                    update TimetableGraf with (ROWLOCK)
                    set	TimeTableGraf_PersRecLim=0
                       ,TimeTableGraf_IsMultiRec = 1
                       ,pmUser_updID = :pmUser_id
                       ,TimeTableGraf_updDT = GETDATE()
                    where TimetableGraf_id = {$TimetableGraf_id};
                ";
            }
            $sql .= "
                exec dbo.p_AddTTGToHistory
                    @TimetableGraf_id = {$TimetableGraf_id},
                    @TimeTableGrafAction_id = :TimetableType_id50,
                    @pmUser_id = :pmUser_id;
                exec p_MedPersonalDay_recount
                    @MedStaffFact_id = {$MedStaffFact_id},
                    @Day_id = {$Day_id},
                    @pmUser_id = :pmUser_id;
                ";
            $sendData[] = $TimetableGraf_id;
        }
        $this->db->query($sql, $sqlParams);
        $ttType = $this->getFirstRowFromQuery("select TimetableType_SysNick, TimetableType_Name from v_TimetableType with (nolock) where TimeTableType_id=?", [$data["TimetableType_id"]]);
        if (!empty($ttType["TimetableType_SysNick"])) {
            $action = $this->defineActionTypeByTimetableType($ttType["TimetableType_SysNick"]);
            if (!empty($action)) {
                // отправка STOMP-сообщения
                foreach ($sendData as $sendDataItem) {
                    sendFerStompMessage([
                        "id" => $sendDataItem,
                        "timeTable" => "TimetableGraf",
                        "action" => $action,
                        "setDate" => date("c")
                    ], "Rule");
                }
            }
        }
        return ["Error_Msg" => ""];
    }

	/**
	 * Получение истории изменения бирки поликлиники
	 */
	function getTTGHistory( $data ) {
		$selectPersonData = "rtrim(rtrim(p.Person_Surname) + ' ' + rtrim(p.Person_Firname) + ' ' + isnull(rtrim(p.Person_Secname), '')) as Person_FIO,
					convert(varchar(10), Person_BirthDay, 104) as Person_BirthDay";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = p.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null then rtrim(rtrim(p.Person_Surname) + ' ' + rtrim(p.Person_Firname) + ' ' + isnull(rtrim(p.Person_Secname), '')) else rtrim(peh.PersonEncrypHIV_Encryp) end as Person_FIO,
					case when peh.PersonEncrypHIV_Encryp is null then convert(varchar(10), Person_BirthDay, 104) else null end as Person_BirthDay";
		}
		if ( !isset( $data['ShowFullHistory'] ) ) {
			/*$sql = "
				select
					convert(varchar(10), TimetableGrafHist_insDT, 104) + ' ' + convert(varchar(8), TimetableGrafHist_insDT, 108) as TimetableHist_insDT,
					case
						when pu.pmUser_id is not null then rtrim(PMUser_Name)
						else '@@inet'
					end as PMUser_Name,
					ttgh.TimetableGrafHist_userID as pmUser_id,
					TimetableActionType_Name,
					TimetableType_Name,
					RecMethodType_id,
					{$selectPersonData}
				from TimetableGrafHist ttgh with (nolock)
				left join v_pmUser pu with (nolock) on ttgh.TimetableGrafHist_userID = pu.pmuser_id
				left join TimetableActionType ttga with (nolock) on ttga.TimetableActionType_id=ttgh.TimetableGrafAction_id
				left join TimetableType ttt with(nolock) on ttt.TimetableType_id = isnull(ttgh.TimetableType_id, 1)
				left join v_Person_ER p with (nolock) on ttgh.Person_id = p.Person_id
				{$joinPersonEncrypHIV}
				where TimetableGraf_id = :TimetableGraf_id";*/
			$sql = "
					WITH hist as (
						SELECT 
							convert(varchar(10), ttg.TimeTableGraf_insDT, 104) + ' ' + convert(varchar(8), ttg.TimeTableGraf_insDT, 108) as TimetableHist_insDT,
							ttg.pmUser_insID AS TimetableGrafHist_userID,
							1 AS TimetableGrafAction_id,
							ttg.TimeTableType_id,
							ttg.RecMethodType_id,
							ttg.Person_id
						FROM v_TimetableGraf_lite ttg with (nolock)
						where TimetableGraf_id = :TimetableGraf_id
					union
						SELECT 
							convert(varchar(10), TimetableGrafHist_insDT, 104) + ' ' + convert(varchar(8), TimetableGrafHist_insDT, 108) as TimetableHist_insDT,
							ttgh.TimetableGrafHist_userID as TimetableGrafHist_userID,
							ttgh.TimetableGrafAction_id,
							isnull(ttgh.TimetableType_id, 1) AS TimetableType_id,
							ttgh.RecMethodType_id,
							ttgh.Person_id
						FROM TimetableGrafHist ttgh with (nolock)
						where TimetableGraf_id = :TimetableGraf_id
						-- можно поставить все события кроме создания
						-- AND TimetableGrafAction_id <> 1
					) 
					SELECT 
						hist.TimetableHist_insDT,
						case
							when hist.TimetableGrafHist_userID is not null then rtrim(PMUser_Name)
							else '@@inet'
						end as PMUser_Name,
						hist.TimetableGrafHist_userID as pmUser_id,
						ttga.TimetableActionType_Name,
						ttt.TimetableType_Name,
						hist.RecMethodType_id,
						{$selectPersonData}
					FROM hist
						left join v_pmUser pu with (nolock) on hist.TimetableGrafHist_userID = pu.pmuser_id
						left join TimetableActionType ttga with (nolock) on ttga.TimetableActionType_id=hist.TimetableGrafAction_id
						left join v_TimetableType ttt with(nolock) on ttt.TimetableType_id = isnull(hist.TimetableType_id, 1)
						left join v_Person_ER p with (nolock) on hist.Person_id = p.Person_id
						{$joinPersonEncrypHIV}
			";
		} else {
			$sql = "
				declare
						@MedStaffFact_id bigint,
						@TimetableGraf_begTime datetime
						select
						@MedStaffFact_id = MedStaffFact_id,
						@TimetableGraf_begTime = TimetableGraf_begTime
					from v_TimetableGraf_lite with (nolock)
					where TimetableGraf_id = :TimetableGraf_id;
				WITH hist as (
					
					SELECT 
							convert(varchar(10), ttg.TimeTableGraf_insDT, 104) + ' ' + convert(varchar(8), ttg.TimeTableGraf_insDT, 108) as TimetableHist_insDT,
							ttg.pmUser_insID AS TimetableGrafHist_userID,
							1 AS TimetableGrafAction_id,
							ttg.TimeTableType_id,
							ttg.RecMethodType_id,
							ttg.Person_id
						FROM v_TimetableGraf_lite ttg with (nolock)
						where
							MedStaffFact_id = @MedStaffFact_id
							and TimetableGraf_begTime = @TimetableGraf_begTime
					union
						SELECT 
							convert(varchar(10), TimetableGrafHist_insDT, 104) + ' ' + convert(varchar(8), TimetableGrafHist_insDT, 108) as TimetableHist_insDT,
							ttgh.TimetableGrafHist_userID as TimetableGrafHist_userID,
							ttgh.TimetableGrafAction_id,
							isnull(ttgh.TimetableType_id, 1) AS TimetableType_id,
							ttgh.RecMethodType_id,
							ttgh.Person_id
						FROM TimetableGrafHist ttgh with (nolock)
						where
							MedStaffFact_id = @MedStaffFact_id
							and TimetableGraf_begTime = @TimetableGraf_begTime
						-- можно поставить все события кроме создания
						-- AND TimetableGrafAction_id <> 1
					) 
					SELECT 
						hist.TimetableHist_insDT,
						case
							when hist.TimetableGrafHist_userID is not null then rtrim(PMUser_Name)
							else '@@inet'
						end as PMUser_Name,
						hist.TimetableGrafHist_userID as pmUser_id,
						ttga.TimetableActionType_Name,
						ttt.TimetableType_Name,
						hist.RecMethodType_id,
						{$selectPersonData}
					FROM hist
						left join v_pmUser pu with (nolock) on hist.TimetableGrafHist_userID = pu.pmuser_id
						left join TimetableActionType ttga with (nolock) on ttga.TimetableActionType_id=hist.TimetableGrafAction_id
						left join v_TimetableType ttt with(nolock) on ttt.TimetableType_id = isnull(hist.TimetableType_id, 1)
						left join v_Person_ER p with (nolock) on hist.Person_id = p.Person_id
						{$joinPersonEncrypHIV}
			";
		}

		$res = $this->db->query(
			$sql,
			array(
			'TimetableGraf_id' => $data['TimetableGraf_id']
			)
		);

		if ( is_object( $res ) ) {
			return $res = $res->result( 'array' );
		} else {
			return false;
		}
	}

	/**
	 * Получение истории изменения примечаний по бирке
	 */
	function getTTDescrHistory( $data ) {
		if ( isset( $data['TimetableGraf_id'] ) ) {
			$sql = "
				select
					convert(varchar(10), TimetableExtendHist_insDT, 104) + ' ' + convert(varchar(8), TimetableExtendHist_insDT, 108) as TimetableExtendHist_insDT,
					rtrim(PMUser_Name) as PMUser_Name,
					TimetableExtend_Descr
				from TimetableExtendHist tteh with (nolock)
				left join v_pmUser pu with (nolock) on tteh.TimetableExtendHist_userID = pu.pmuser_id
				where TimetableGraf_id = :TimetableGraf_id";
		}
		if ( isset( $data['TimetableStac_id'] ) ) {
			$sql = "
				select
					convert(varchar(10), TimetableExtendHist_insDT, 104) + ' ' + convert(varchar(8), TimetableExtendHist_insDT, 108) as TimetableExtendHist_insDT,
					rtrim(PMUser_Name) as PMUser_Name,
					TimetableExtend_Descr
				from TimetableExtendHist tteh with (nolock)
				left join v_pmUser pu with (nolock) on tteh.TimetableExtendHist_userID = pu.pmuser_id
				where TimetableStac_id = :TimetableStac_id";
		}
		if ( isset( $data['TimetableMedService_id'] ) ) {
			$sql = "
				select
					convert(varchar(10), TimetableExtendHist_insDT, 104) + ' ' + convert(varchar(8), TimetableExtendHist_insDT, 108) as TimetableExtendHist_insDT,
					rtrim(PMUser_Name) as PMUser_Name,
					TimetableExtend_Descr
				from TimetableExtendHist tteh with (nolock)
				left join v_pmUser pu with (nolock) on tteh.TimetableExtendHist_userID = pu.pmuser_id
				where TimetableMedService_id = :TimetableMedService_id";
		}
		if ( isset( $data['TimetableResource_id'] ) ) {
			$sql = "
				select
					convert(varchar(10), TimetableExtendHist_insDT, 104) + ' ' + convert(varchar(8), TimetableExtendHist_insDT, 108) as TimetableExtendHist_insDT,
					rtrim(PMUser_Name) as PMUser_Name,
					TimetableExtend_Descr
				from TimetableExtendHist tteh with (nolock)
				left join v_pmUser pu with (nolock) on tteh.TimetableExtendHist_userID = pu.pmuser_id
				where TimetableResource_id = :TimetableResource_id";
		}

		$res = $this->db->query(
			$sql,
			array(
			'TimetableGraf_id' => $data['TimetableGraf_id'],
			'TimetableStac_id' => $data['TimetableStac_id'],
			'TimetableMedService_id' => $data['TimetableMedService_id'],
			'TimetableResource_id' => $data['TimetableResource_id'],
			)
		);

		if ( is_object( $res ) ) {
			return $res = $res->result( 'array' );
		} else {
			return false;
		}
	}

	/**
	 * Функция связи идентификатора человека с биркой из фер
	 */
	function updatePersonForFerRecord($data) {
		$query = "
			select
				Person_id
			from
				v_TimetableGraf_lite (nolock)
			where
				TimetableGraf_id = :TimetableGraf_id
		";
		$result = $this->db->query($query, $data);

		$FER_PERSON_ID = $this->config->item('FER_PERSON_ID');
		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['Person_id']) && $resp[0]['Person_id'] != $FER_PERSON_ID) {
				return array('Error_Msg' => 'Пациент записанный на бирку не явялется записью ФЭР');
			}
		}
		$query = "
			update
				TimetableGraf with (rowlock)
			set
				Person_id = :Person_id
			where
				TimetableGraf_id = :TimetableGraf_id
		";

		$result = $this->db->query($query, $data);

		return array('Error_Msg' => '');
	}

	/**
	 * Метод идентификации человека в ЛПУ
	 */
	function setPersonInTimetableStac( $data ) {

		$query = "
			update EmergencyData with (rowlock)
				set Person_lid = :Person_id
			where EmergencyData_id = (select EmergencyData_id from v_TimetableStac_lite with (nolock) where TimetableStac_id = :TimetableStac_id)
		";
		$result = $this->db->query( $query,
			$data );
		return array(array('Error_Msg' => ''));
	}

	/**
	 * Получение информации по бирке поликлиники
	 */
	function getTTGInfo( $data ) {
		$query = "
			select top 1
				MP.MedPersonal_id,
				PA.Person_Fio,
				cast(isnull(TTG.TimetableGraf_begTime, TTG.TimetableGraf_factTime) as datetime) as TimetableGraf_begTime,
				convert(varchar(20), TTG.TimetableGraf_begTime, 120) as TimetableGraf_abegTime,
				convert(varchar(20), TTGN.TimetableGraf_begTime, 120) as TimetableGraf_nextTime,
				MSF.MedStaffFact_PriemTime
			from
				v_TimetableGraf_lite TTG with (nolock)
				left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = TTG.MedStaffFact_id
				left join v_MedPersonal MP with(nolock) on MP.MedPersonal_id = MSF.MedPersonal_id
				outer apply(
					select top 1 Person_Fio from v_Person_all with(nolock) where Person_id = TTG.Person_id
				) as PA
				outer apply(
					select top 1 TimetableGraf_begTime from v_TimetableGraf_lite with(nolock) where 
						MedStaffFact_id = TTG.MedStaffFact_id and 
						TimeTableGraf_Day = TTG.TimeTableGraf_Day and 
						TimetableGraf_begTime > TTG.TimetableGraf_begTime
				) as TTGN
			where
				TTG.TimetableGraf_id = :TimetableGraf_id
		";

		$res = $this->db->query( $query,
			array(
			'TimetableGraf_id' => $data['TimetableGraf_id']
			) );

		if ( is_object( $res ) ) {
			return $res->result( 'array' );
		} else {
			return false;
		}
	}

	/**
	 * Отметка на бирке о явке/неявке
	 */
	function setPersonMark( $data ) {
		$sql = "
			declare
				@ErrCode bigint,
				@ErrMsg varchar(4000);

			exec p_TimetableGraf_setMark
				@TimetableGraf_id = :TimetableGraf_id,
				@PersonMark_Status = :PersonMark_Status,
				@PersonMark_Comment = :PersonMark_Comment,
				@pmUser_id = :pmUser_id
			select @ErrCode as Error_Code, @ErrMsg as Error_Msg;";

		$res = $this->db->query(
			$sql,
			array(
			'TimetableGraf_id' => $data['TimetableGraf_id'],
			'PersonMark_Status' => $data['PersonMark_Status'],
			'PersonMark_Comment' => $data['PersonMark_Comment'],
			'pmUser_id' => $data['pmUser_id']
			)
		);

		if ( is_object( $res ) ) {
			$res = $res->result( 'array' );
			if ( empty( $res[0]['Error_Msg'] ) ) {
				return array(
					'Error_Msg' => ''
				);
			} else {
				return array(
					'Error_Code' => $res[0]['Error_Code'],
					'Error_Msg' => $res[0]['Error_Msg']
				);
			}
		} else {
			return false;
		}
	}

	/**
	 * Поиск интернет-записи для модерации
	 */
	function getTTGForModeration( $data ) {

		$queryParams = array();
		$sFilters = '';
		$topFilters = '';
		
		$isSearchByEncryp = false;
		$selectPersonData = "
			( p.Person_surname + ' ' + p.Person_firname + ' ' + isnull(p.Person_secname, '') ) as PersonFullName,
			case
				when p.Person_Phone is null and p.PersonInfo_InternetPhone is null then ''
				when p.Person_Phone is not null and p.PersonInfo_InternetPhone is not null then 'В нашей базе: ' + p.Person_Phone + '<br />Указано человеком: ' + isnull(p.PersonInfo_InternetPhone, '')
				when p.Person_Phone is not null then 'В нашей базе: ' + p.Person_Phone
				when p.PersonInfo_InternetPhone is not null then 'Указано человеком: ' + isnull(p.PersonInfo_InternetPhone, '')
			end as Person_Phone,
			convert(varchar(10),p.Person_BirthDay,104) as Person_BirthDay,
			
			rtrim(replace(isnull(a1.Address_Address, ''), 'РОССИЯ, ПЕРМСКИЙ КРАЙ, ', '')) as PAddress_Address,
			rtrim(replace(isnull(a.Address_Address, ''), 'РОССИЯ, ПЕРМСКИЙ КРАЙ, ', ''))  as UAddress_Address,
			case when a1.Address_id is not null
				then
					rtrim(a1.Address_House)
				else
					rtrim(a.Address_House)
			end as Address_House,
			case when a1.Address_id is not null
				then
					rtrim(a1.KLStreet_id)
				else
					rtrim(a.KLStreet_id)
			end as KLStreet_id,";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$isSearchByEncryp = isSearchByPersonEncrypHIV($data['Person_Surname']);
			$joinPersonEncrypHIV = " left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = p.Person_id";
			$selectPersonData = "
			case when peh.PersonEncrypHIV_Encryp is null then ( p.Person_surname + ' ' + p.Person_firname + ' ' + isnull(p.Person_secname, '') ) else rtrim(peh.PersonEncrypHIV_Encryp) end as PersonFullName,
			case when peh.PersonEncrypHIV_Encryp is not null then null
				when p.Person_Phone is null and p.PersonInfo_InternetPhone is null then ''
				when p.Person_Phone is not null and p.PersonInfo_InternetPhone is not null then 'В нашей базе: ' + p.Person_Phone + '<br />Указано человеком: ' + isnull(p.PersonInfo_InternetPhone, '')
				when p.Person_Phone is not null then 'В нашей базе: ' + p.Person_Phone
				when p.PersonInfo_InternetPhone is not null then 'Указано человеком: ' + isnull(p.PersonInfo_InternetPhone, '')
			end as Person_Phone,
			case when peh.PersonEncrypHIV_Encryp is null then convert(varchar(10),p.Person_BirthDay,104) end as Person_BirthDay,
			case when peh.PersonEncrypHIV_Encryp is null then (
				rtrim(replace(isnull(a.Address_Address, ''), 'РОССИЯ, ПЕРМСКИЙ КРАЙ, ', ''))
			
			) end as UAddress_Address,
			case when peh.PersonEncrypHIV_Encryp is null then (
				 rtrim(replace(isnull(a1.Address_Address, ''), 'РОССИЯ, ПЕРМСКИЙ КРАЙ, ', '')) 
			) end as PAddress_Address,
			case when peh.PersonEncrypHIV_Encryp is not null then null
				when a1.Address_id is not null then rtrim(a1.Address_House) else rtrim(a.Address_House)
			end as Address_House,
			case when peh.PersonEncrypHIV_Encryp is not null then null
				when a1.Address_id is not null then rtrim(a1.KLStreet_id) else rtrim(a.KLStreet_id)
			end as KLStreet_id,";
		}
		
		if ( !empty($data['Person_Surname']) ) {
			if (allowPersonEncrypHIV($data['session']) && $isSearchByEncryp) {
				$sFilters .= " and peh.PersonEncrypHIV_Encryp like :Person_Surname";
			} else {
				$sFilters .= " and p.Person_SurName like :Person_Surname ";
			}
			$queryParams['Person_Surname'] = $data['Person_Surname'] . "%";
		}
		
		if ( !empty( $data['Person_Firname'] ) ) {
			$sFilters .= " and p.Person_FirName like :Person_FirName ";
			$queryParams['Person_FirName'] = $data['Person_Firname'] . "%";
		}
		if ( !empty( $data['Person_Secname'] ) ) {
			$sFilters .= " and p.Person_SecName like :Person_SecName ";
			$queryParams['Person_SecName'] = $data['Person_Secname'] . "%";
		}
		if ( !empty( $data['Person_Phone'] ) ) {
			$sFilters .= " and p.Person_Phone like :Person_Phone ";
			$queryParams['Person_Phone'] = $data['Person_Phone'] . "%";
		}
		if ( !empty( $data['ModerateType_id'] ) ) {
			If ( $data['ModerateType_id'] == 1 ) {
				$sFilters .= " and g.TimetableGraf_IsModerated = 1 ";
			}
			If ( $data['ModerateType_id'] == 2 ) {
				$sFilters .= " and (g.TimetableGraf_IsModerated is null or g.TimetableGraf_IsModerated = 2) ";
			}
			If ( $data['ModerateType_id'] == 3 ) {
				$sFilters .= " and g.TimetableGraf_IsModerated = 2 ";
			}
			If ( $data['ModerateType_id'] == 4 ) {
				$sFilters .= " and g.TimetableGraf_IsModerated is null";
			}
		}
		if ( !empty( $data['StartDate'] ) ) {
			$sFilters .= " and cast(g.timetablegraf_upddt as date) = :StartDate ";
			$queryParams['StartDate'] = $data['StartDate'];
		}
		if ( !empty( $data['ZapDate'] ) ) {
			$sFilters .= " and cast(g.timetablegraf_begtime as date) = :ZapDate ";
			$queryParams['ZapDate'] = $data['ZapDate'];
		}
		
		if ( !empty( $data['MedPersonal_id'] ) ) {
			$sFilters .= " and msf.MedPersonal_id = :MedPersonal_id";
			$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
		}
		
		if ( !empty( $data['KLCity_id'] ) ) {
			$sFilters .= " and lua.KLCity_id = :KLCity_id ";
			$queryParams['KLCity_id'] = $data['KLCity_id'];
		}
		if ( !empty( $data['KLTown_id'] ) ) {
			$sFilters .= " and lua.KLTown_id = :KLTown_id ";
			$queryParams['KLTown_id'] = $data['KLTown_id'];
		}
		if ( !empty( $data['TTGLpu_id'] ) ) {
			$sFilters .= " and msf.Lpu_id = :Lpu_id ";
			$queryParams['Lpu_id'] = $data['TTGLpu_id'];
			$topFilters = " and msf.Lpu_id = :Lpu_id ";
		}
		
		$query = "
		-- variables
		declare @curdate datetime = dbo.tzGetDate();
		-- end variables

		-- addit with
		with ttg (
			TimetableGraf_id,
			TimetableGraf_updDT
		) as (
			select
				g.TimetableGraf_id,
				g.TimetableGraf_updDT
			from
				v_TimetableGraf_lite g (nolock)
				inner join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = g.MedStaffFact_id
			where
				g.TimetableGraf_begTime >= @curdate
				and g.pmUser_updID between 1000000 and 5000000
				and g.Person_id is not null
				{$topFilters}
		)
		-- end addit with

		select
			-- select
			g.TimetableGraf_id,
			g.TimetableGraf_Day,
			convert(varchar(10), cast(g.TimetableGraf_begTime as datetime), 104) + ' ' + convert(varchar(8), cast(g.TimetableGraf_begTime as datetime), 108) as TimetableGraf_begTime,
			convert(varchar(10), cast(g.TimetableGraf_updDT as datetime), 104) + ' ' + convert(varchar(8), cast(g.TimetableGraf_updDT as datetime), 108) as TimetableGraf_updDT,
			g.TimetableGraf_IsModerated,
			l.Lpu_Nick,
			( luas.KLStreet_Name + ', ' + lua.Address_House ) as LpuUnit_Address,
			lu.LpuUnit_Name,
			( msf.MedPersonal_FIO ) as MedPersonFullName,
			ls.LpuSectionProfile_Name,
			SUBSTRING ( lr.LpuRegion_Name , 1 , len(lr.LpuRegion_Name)-1 ) as MedLpuRegion_Name,
			LPR.LpuRegion_Name,
			null as LpuRegion_Name_Pr,
			lu.LpuUnit_id,
			msf.MedStaffFact_id,
			p.Person_id,
			{$selectPersonData}
			l.Lpu_id,
			case when cast(isnull(p.Polis_endDate,@curdate) as date) >= @curdate then '' else 'true' end as Person_IsBDZ,
			p.Lpu_id as PrikLpu_id,
			p.Lpu_Nick as PrikLpu_Nick,
			null as MedpersonalDay_Descr,
			null as MedstaffFact_Descr,
			p.Server_pid,
			isnull(g.pmUser_updID,-1) as pmUser_updID
			-- end select
		from
			-- from
			ttg with (nolock)
			inner join v_TimetableGraf_lite g with (nolock) on g.TimetableGraf_id = ttg.TimetableGraf_id
			inner join v_Person_ER p with (nolock) on p.Person_id = g.Person_id
			inner join v_MedStaffFact_ER msf with (nolock) on msf.MedStaffFact_id = g.MedStaffFact_id
			left join v_LpuSection_ER ls with (nolock) on ls.LpuSection_id = msf.LpuSection_id
			left join v_LpuUnit_ER lu with (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
			left join Address lua with (nolock) on lu.Address_id = lua.Address_id
			left join KLStreet luas with (nolock) on lua.KLStreet_id = luas.KLStreet_id
			left join v_Lpu l with (nolock) on l.Lpu_id = lu.Lpu_id
			outer apply (
                    select top 1 
						LpuRegion_id
                    from v_PersonCard_all with(nolock)
                    where Person_id = p.Person_id
						and PersonCard_endDate is null
                    order by
						case when LpuAttachType_id = 4 and Lpu_id = p.Lpu_id then 0 else LpuAttachType_id end,
						PersonCard_begDate
                ) as PersonCard
			left join v_LpuRegion LPR with (nolock) on LPR.LpuRegion_id = PersonCard.LpuRegion_id
			outer apply (
				select
					( select LpuRegion_Name + ', ' as 'data()'
						from v_MedStaffRegion t2 with (nolock)
						left join v_LpuRegion lr2 with(nolock) on t2.LpuRegion_id = lr2.LpuRegion_id
						where t1.MedPersonal_id=t2.MedPersonal_id
						order by cast(lr2.LpuRegion_Name as int)
						for xml path('') ) as LpuRegion_Name
				from v_MedStaffRegion t1 with (nolock)
				left join v_LpuRegion lr1 with (nolock) on t1.LpuRegion_id = lr1.LpuRegion_id
				where t1.MedPersonal_id = msf.MedPersonal_id
				group by t1.MedPersonal_id
			) lr
			left outer join Address a with (nolock) on p.UAddress_id = a.Address_id
			left outer join Address a1 with (nolock) on p.PAddress_id = a1.Address_id
			left outer join KLStreet pas with (nolock) on a.KLStreet_id = pas.KLStreet_id
			left outer join KLStreet pas1 with (nolock) on a1.KLStreet_id = pas1.KLStreet_id
			left join MedpersonalDay mpd with (nolock) on mpd.MedStaffFact_id = g.MedStaffFact_id and mpd.Day_id = g.TimetableGraf_Day
			{$joinPersonEncrypHIV}
			-- end from
		where
			-- where
			(1=1)
			{$sFilters}
			-- end where
		order by
			-- order by
			ttg.TimetableGraf_updDT asc
			-- end order by";
		//echo getDebugSql(getLimitSQLPH($query, $data['start'], $data['limit']), $queryParams); die();
		$response = $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);
		if ( !is_array($response) || !isset($response['data']) ) {
			return false;
		}
		// Получаем данные из другой БД
		$this->dbkvrachu = $this->load->database('UserPortal', true); // загружаем БД к-врачу.ру
		$pmUserList = array();

		foreach ($response['data'] as $row) {
			if($row['pmUser_updID'] != -1)
				$pmUserList[] = array('Person_id' => $row['Person_id'], 'pmUser_updID' => $row['pmUser_updID']);
		}

		// Если есть записи...
		if ( count($pmUserList) > 0 ) {
			if ( true) {
				$query = "
					Select 
						:pmUser_updID as pmUser_updID, :Person_id as Person_id, u.username as Login, u.Email, 
						rtrim(replace(isnull(a2.Address_Address, ''), 'РОССИЯ, ПЕРМСКИЙ КРАЙ, ', '')) + ' - <small>Указано человеком</small>' as Address_Address
					from Users u with (nolock)
					left join Person p1 with (nolock) on p1.Person_mainId = :Person_id and u.id = p1.pmUser_id and p1.pmUser_id = u.id
					left join Address a2 with (nolock) on a2.Address_id = p1.Address_id
					where
						u.id = :pmUser_updID and u.username is not null";
			} else {
				$query = "
					Select 
						:pmUser_updID as pmUser_updID, :Person_id as Person_id, u.Login as Login, u.Email,
						rtrim(replace(isnull(a2.Address_Address, ''), 'РОССИЯ, ПЕРМСКИЙ КРАЙ, ', '')) + ' - <small>Указано человеком</small>' as Address_Address
					from Usr u with (nolock)
					left join Person p1 with (nolock) on p1.Person_mainId = :Person_id and u.User_id = p1.pmUser_id
					and p1.Person_mainid in (select PersonCard.Person_id from UserReg.PersonCard with (nolock))
					left join Address a2 with (nolock) on a2.Address_id = p1.Address_id
					where
						u.User_id = :pmUser_updID and u.Login is not null";
			}
			// Для каждой записи будет получать отдельный запрос 
			$sql = '';
			foreach ( $pmUserList as $row ) {
				$sql .= getDebugSql($query, $row);
			}
			
			$resultUser = $this->dbkvrachu->query($sql, array());

			if (!is_object($resultUser)) {
				return false;
			}

			$res = $resultUser->result('array');

			foreach ($res as $row) {
				foreach ($response['data'] as $key => $array) {
					if ($row['Person_id'] == $array['Person_id'] && $row['pmUser_updID'] == $array['pmUser_updID']) {
						//$response['data'][$key]['Address_Address'] = $response['data'][$key]['Address_Address'].$row['Address_Address'];
						$response['data'][$key]['Login'] = $row['Login'];
						$response['data'][$key]['Email'] = $row['Email'];
					}
				}
			}
		}

		return $response;
	}

	/**
	 * Модерация бирки в поликлинике (одобрение/отказ/подтверждение)
	 */
	function setTimetableGrafModeration( $data ) {
		$sql = "
			declare
				@ErrCode int,
				@ErrMsg varchar(4000);

			exec p_TimetableGraf_Moderate
				@TimetableGraf_id = :TimetableGraf_id,
				@TimetableGraf_IsModerated = :Status,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$res = $this->db->query( $sql,
			array(
			'TimetableGraf_id' => $data['TimetableGraf_id'],
			'Status' => $data['Status'],
			'pmUser_id' => $data['pmUser_id']
			)
		);
		$resp = $res->result('array');

		return $resp[0];
	}
	
	/**
	 * Множественная модерация бирок в поликлинике (одобрение/отказ/подтверждение)
	 */
	function setMultipleTimetableGrafModeration( $data ) {
		if( empty($data['Status']) || empty($data['TimetableGraf_ids']) || !is_array($data['TimetableGraf_ids']) ) return array('Error_Msg' => 'переданы некорректные данные');
		if( count($data['TimetableGraf_ids']) == 0 || count($data['TimetableGraf_ids']) > 999) return array('Error_Msg' => 'количество передаваемых бирок на модерацию должно быть меньше 1000');
		
		$TimetableGraf_ids = array_map('intval', array_filter($data['TimetableGraf_ids'], 'is_numeric'));		
		$sql = "
			declare
				@ErrCode int,
				@ErrMsg varchar(4000);

			exec dbo.p_TimeTableGraf_ModerateGroup
				@TimeTableGraf_ListID = :TimetableGraf_ids,
				@TimetableGraf_IsModerated = :Status,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$res = $this->db->query( $sql,
			array(
			'TimetableGraf_ids' => implode(",", $TimetableGraf_ids),
			'Status' => $data['Status'],
			'pmUser_id' => $data['pmUser_id']
			)
		);
		$resp = $res->result('array');

		return $resp[0];
	}

	/**
	 * Данные пользователя для отправки в письме
	 */
	function getTTGDataForMail( $data ) {
		
		$selectPersonData = "
					rtrim(p.Person_Firname) as Person_Firname,
					rtrim(p.Person_Surname) as Person_Surname,
					rtrim(p.Person_Secname) as Person_Secname,";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = p.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Surname) else rtrim(peh.PersonEncrypHIV_Encryp) end as Person_Surname,
					case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Firname) else '' end as Person_Firname,
					case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Secname) else '' end as Person_Secname,";
		}
		$query = "
			select
				ttg.TimetableGraf_begTime,
				{$selectPersonData}
				MedPersonal_FIO,
				msf.Person_Surname as Med_Person_Surname,
				msf.Person_Firname as Med_Person_Firname,
				msf.Person_Secname as Med_Person_Secname,
				ttg.pmUser_updID as User_id,
				TimetableGraf_IsModerated,
				lsp.ProfileSpec_Name_Rod
			from v_TimetableGraf_lite ttg with (nolock)
			left join v_Person_ER p with (nolock) on ttg.Person_id = p.Person_id
			left join v_MedStaffFact_ER msf with (nolock) on msf.MedStaffFact_id = ttg.MedStaffFact_id
			left join v_LpuSectionProfile lsp with (nolock) on msf.LpuSectionProfile_id = lsp.LpuSectionProfile_id
			{$joinPersonEncrypHIV}
			where TimetableGraf_id = :TimetableGraf_id
				and ttg.Person_id is not null";

		$result = $this->db->query( $query,
			array(
			'TimetableGraf_id' => $data['TimetableGraf_id']
			) );

		if ( is_object( $result ) ) {

			$ttgData = $result->result( 'array' );

			$this->dbkvrachu = $this->load->database('UserPortal', true); // загружаем БД к-врачу.ру
			
			if ( KVRACHU_TYPE == 2 ) {
				$query = "
					Select top 1
						Users.id as User_id,
						Users.first_name as FirstName,
						Users.second_name as MidName,
						UserNotify_Phone,
						VizitNotify_Email as UserNotify_AcceptIsEmail,
						VizitNotify_SMS as UserNotify_AcceptIsSMS,
						Users.email as EMail
					from UserNotify with (nolock)
						inner join Users with (nolock) on Users.id = UserNotify.User_id
						inner join Person with (nolock) on Person.pmUser_id = UserNotify.User_id
						left join VizitNotify on VizitNotify.pmUser_id = UserNotify.User_id and VizitNotify.TimetableGraf_id = :TimetableGraf_id
					where
						Users.id = :User_id";
			} else {
				$query = "
					select
						u.User_id,
						u.EMail,
						u.FirstName,
						u.MidName,
						un.UserNotify_Phone,
						un.UserNotify_AcceptIsEmail,
						un.UserNotify_AcceptIsSMS
					from Usr u with (nolock)
					left join UserNotify un with (nolock) on u.User_id = un.User_id
					where u.User_id = :User_id";
			}

			$result = $this->dbkvrachu->query( $query,
				array(
				'User_id' => $ttgData[0]['User_id'],
				'TimetableGraf_id' => $data['TimetableGraf_id']
				) );

			if ( is_object( $result ) ) {
				$userData = $result->result( 'array' );
				if ( count( $userData ) > 0 ) {
					$userData[0] = array_merge( $ttgData[0],
						$userData[0] );
				}
				return $userData;
			} else {
				return array('Error_Msg' => 'Ошибка при получении данных пользователя для отправки сообщений');
			}
		} else {
			return array('Error_Msg' => 'Ошибка при получении данных пользователя для отправки сообщений');
		}
	}

	/**
	 * Получение участка и набора домов по ЛПУ и названию улицы
	 */
	function findLpuAddressRegions( $data ) {

		$query = "select
			LpuRegion_Name, LpuRegionStreet_HouseSet
		from v_LpuRegion lr with (nolock)
		inner join v_LpuRegionStreet lrs with(nolock) on lrs.LpuRegion_id = lr.LpuRegion_id
		where
			lrs.KLStreet_id = :KLStreet_id
			and lr.Lpu_id = :Lpu_id";

		$result = $this->db->query( $query,
			array(
			'KLStreet_id' => $data['KLStreet_id'],
			'Lpu_id' => $data['Lpu_id']
			) );

		if ( is_object( $result ) ) {
			return $result->result( 'array' );
		} else {
			return false;
		}
	}

	/**
	 * Печать списка пациентов поликлиники на день/период
	 */
	function printPacList( $data ) {
		$query = "
			select top 1
				msf.MedPersonal_FIO,
				ls.LpuSectionProfile_Name,
				ls.LpuSectionProfile_Code,
				lr.LpuRegion_Name,
				ls.LpuSectionProfile_id,
				msf.MedStaffFact_IsQueueOnFree,
				msf.RecType_id,
				lu.LpuUnit_Name,
				a.Address_Address,
				a.KLCity_id,
				l.Lpu_Nick as Lpu_Nick,
				lu.LpuUnit_id,
				ls.LpuSection_Name,
				ISNULL(LL.LpuLevel_Code,0) as LpuLevel_Code,
				lka.Kladr_Code,
				l.Lpu_id
			from v_MedstaffFact_ER msf with (nolock)
				left join v_MedStaffRegion msr with (nolock) on msr.MedPersonal_id = msf.MedPersonal_id
				left outer join v_LpuRegion lr with (nolock) on msr.LpuRegion_Id = lr.LpuRegion_Id
				left outer join v_LpuSection_ER ls with (nolock) on msf.LpuSection_Id = ls.LpuSection_Id
				left join v_LpuUnit_ER lu with (nolock) on lu.LpuUnit_id = msf.LpuUnit_id
				left outer join [Address] a with (nolock) on lu.Address_id = a.Address_id
				left join v_Lpu l with (nolock) on l.lpu_id = lu.lpu_id
				left join v_Address la with (nolock) on la.Address_id = l.PAddress_id
				left join v_KLArea lka with (nolock) on lka.KLArea_id = la.KLCity_id
				left join v_LpuLevel LL with (nolock) on LL.LpuLevel_id = l.LpuLevel_id
			where
				msf.MedStaffFact_id = :MedStaffFact_id
			";

		$result = $this->db->query( $query,
			array(
			'MedStaffFact_id' => $data['MedStaffFact_id']
			) );

		if ( is_object( $result ) ) {
			$res = $result->result( 'array' );
		} else {
			return false;
		}

		$query = "
			select
				oh.OrgHeadPost_id,
				rtrim(ps.Person_SurName) + ' ' + rtrim(ps.Person_FirName) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as OrgHead_FIO,
				rtrim(ohp.OrgHeadPost_Name) as OrgHeadPost_Name,
				rtrim(isnull(oh.OrgHead_Email, '')) as OrgHead_Email,
				rtrim(isnull(oh.OrgHead_Phone, '')) as OrgHead_Phone,
				rtrim(isnull(oh.OrgHead_Mobile, '')) as OrgHead_Mobile,
				rtrim(isnull(oh.OrgHead_CommissNum, '')) as OrgHead_CommissNum,
				rtrim(isNull(convert(varchar,cast(oh.OrgHead_CommissDate as datetime),104),'')) as OrgHead_CommissDate,
				rtrim(isnull(oh.OrgHead_Address, '')) as OrgHead_Address
			from
				v_OrgHead as oh with(nolock)
				inner join v_PersonState as ps with(nolock) on oh.Person_id = ps.Person_id
				inner join OrgHeadPost as ohp with(nolock) on ohp.OrgHeadPost_id = oh.OrgHeadPost_id
			where
				oh.Lpu_id = (select Lpu_id from v_MedStaffFact_Er with(nolock) where MedStaffFact_id = :MedStaffFact_id) and LpuUnit_id is null and oh.OrgHeadPost_id = 7";

		$result = $this->db->query( $query,
			array(
			'MedStaffFact_id' => $data['MedStaffFact_id']
			) );

		if ( is_object( $result ) ) {
			$res['OrgHead'] = $result->result( 'array' );
		} else {
			$res['OrgHead'] = null;
		}

		$selectPersonData = "
					rtrim(p.Person_Firname) as Person_Firname,
					rtrim(p.Person_Surname) as Person_Surname,
					rtrim(p.Person_Secname) as Person_Secname";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = p.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Surname) else rtrim(peh.PersonEncrypHIV_Encryp) end as Person_Surname,
					case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Firname) else '' end as Person_Firname,
					case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Secname) else '' end as Person_Secname";
		}

		$params = array(
			'MedStaffFact_id' => $data['MedStaffFact_id']
		);

		if (empty($data['isPeriod'])) {
			$whereTimetableGraf_Day = "t.TimetableGraf_Day = :TimetableGraf_Day";
			$params['TimetableGraf_Day'] = TimeToDay(strtotime($data['Day']));
		} else {
			$whereTimetableGraf_Day = "t.TimetableGraf_Day between :TimetableGraf_begDay and :TimetableGraf_endDay";
			$params['TimetableGraf_begDay'] = TimeToDay(strtotime($data['Day']));

			$temp = GetPolDayCount($res[0]['Lpu_id']);
			$plusDays = empty($temp) ? 10 : $temp;
			$params['TimetableGraf_endDay'] = $params['TimetableGraf_begDay'] + $plusDays;
		}

		$query = "
			select
				convert( varchar(40), t.TimetableGraf_begTime, 120 ) as TimetableGraf_begTime,
				{$selectPersonData}
			from v_TimetableGraf_lite t with(nolock)
			left outer join v_MedStaffFact_ER msf with(nolock) on msf.MedStaffFact_id=t.MedStaffFact_id
			left outer join v_Person_ER p with(nolock) on t.Person_id = p.Person_id
			{$joinPersonEncrypHIV}
			where {$whereTimetableGraf_Day}
				and t.MedStaffFact_Id = :MedStaffFact_id
				and t.TimetableGraf_begTime is not null -- только обычные плановые бирки
				and t.Person_id is not null
			order by t.TimetableGraf_begTime";

		$result = $this->db->query( $query, $params);

		if ( is_object( $result ) ) {
			$res['ttgData'] = $result->result( 'array' );
			return $res;
		} else {
			return false;
		}
	}

	/**
	 * Печать списка записанных пациентов в стационаре или на мед. службе
	*/
	function printPacStacOrMSList(&$data) {

		if (isset($data['MedService_id'])) {
			$query = "
				select top 1
					ms.MedService_Name,
					l.Lpu_Nick as Lpu_Nick,
					a.Address_Address,
					lka.Kladr_Code,
					ls.LpuSection_Name,
					ms.Lpu_id
				from v_MedService ms with (nolock)
					left join v_Lpu l with (nolock) on ms.Lpu_id = l.Lpu_id
					left join v_LpuSection ls with (nolock) on ms.LpuSection_id = ls.LpuSection_id
					left join v_Address a with (nolock) on ms.Address_id = a.Address_id
					left join v_Address la with (nolock) on la.Address_id = l.PAddress_id
					left join v_KLArea lka with (nolock) on lka.KLArea_id = la.KLCity_id
				where ms.MedService_id = :MedService_id
			";
		} else {
			$query = "
			select top 1
				ls.LpuSection_Name as MedService_Name,
				l.Lpu_Nick as Lpu_Nick,
				a.Address_Address,
				lka.Kladr_Code,
				ls.LpuSection_Name,
				ls.Lpu_id
			from v_LpuSection ls with (nolock)
				left join v_Lpu l with (nolock) on ls.Lpu_id = l.Lpu_id
				left join v_LpuUnit lu with (nolock) on ls.LpuUnit_id = lu.LpuUnit_id
				left join v_Address a with (nolock) on lu.Address_id = a.Address_id
				left join v_Address la with (nolock) on la.Address_id = l.PAddress_id
				left join v_KLArea lka with (nolock) on lka.KLArea_id = la.KLCity_id
			where ls.LpuSection_id = :LpuSection_id	
			";
		}

		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			$res = $result->result('array');
		} else {
			return false;
		}

		$whereLpu = $res[0]['Lpu_id'];
		$join = "";
		if (isset($data['isPeriod']) && isset($data['MedService_id'])) {

			if ($data['endDate'] < $data['begDate']) {
				$temp = GetMedServiceDayCount($res[0]['Lpu_id']);
				$plusDays = empty($temp) ? 10 : $temp;
				$data['endDate'] = date('d.m.Y', strtotime($data['begDate'] . " + " . $plusDays . " days"));
			}

			$params = array(
				'MedService_id' => $data['MedService_id'],
				'Timetable_begDay' => TimeToDay(strtotime($data['begDate'])),
				'Timetable_endDay' => TimeToDay(strtotime($data['endDate']))
			);

			$selectDate = "convert( varchar(40), t.TimetableResource_begTime, 120 ) as TimetableGraf_begTime,";
			$from = "v_TimetableResource_lite";
			$join = "left join v_Resource r with (nolock) on t.Resource_id = r.Resource_id
				left join v_MedService ms with (nolock) on r.MedService_id = ms.MedService_id";
			$whereTimetableGraf_Day = "t.TimetableResource_Day between :Timetable_begDay and :Timetable_endDay
				and ms.MedService_id = :MedService_id";
			$orderBy = "t.TimetableResource_begTime";
		} else if (isset($data['MedService_id'])) {
			$temp = GetMedServiceDayCount($res[0]['Lpu_id']);
			$plusDays = empty($temp) ? 10 : $temp;
			$data['endDate'] = date('d.m.Y', strtotime($data['begDate'] . " + " . $plusDays . " days"));
			$params = array(
				'MedService_id' => $data['MedService_id'],
				'Timetable_begDay' => TimeToDay(strtotime($data['begDate'])),
				'Timetable_endDay' => TimeToDay(strtotime($data['endDate'])),
			);

			$selectDate = "convert( varchar(40), t.TimetableMedService_begTime, 120 ) as TimetableGraf_begTime,";
			$from = "v_TimetableMedService_lite";
			$whereTimetableGraf_Day = "t.TimetableMedService_Day between :Timetable_begDay and :Timetable_endDay
				and t.MedService_id = :MedService_id";
			$orderBy = "t.TimetableMedService_begTime";
		} else {
			$temp = GetStacDayCount($data['Lpu_id']);
			$plusDays = empty($temp) ? 10 : $temp;
			$data['endDate'] = date('d.m.Y', strtotime($data['begDate'] . " + " . $plusDays . " days"));
			$params = array(
				'LpuSection_id' => $data['LpuSection_id'],
				'Timetable_begDay' => TimeToDay(strtotime($data['begDate'])),
				'Timetable_endDay' => TimeToDay(strtotime($data['endDate'])),
			);

			$selectDate = "convert(varchar(40), TimeTableStac_setDate, 120) as TimeTableStac_begTime,";
			$from = "v_TimeTableStac_lite";
			$whereTimetableGraf_Day = "t.TimetableStac_Day between :Timetable_begDay and :Timetable_endDay
				and LpuSection_id = :LpuSection_id";
			$orderBy = "TimeTableStac_begTime";
		}

		$query = "
			select
				oh.OrgHeadPost_id,
				rtrim(ps.Person_SurName) + ' ' + rtrim(ps.Person_FirName) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as OrgHead_FIO,
				rtrim(ohp.OrgHeadPost_Name) as OrgHeadPost_Name,
				rtrim(isnull(oh.OrgHead_Email, '')) as OrgHead_Email,
				rtrim(isnull(oh.OrgHead_Phone, '')) as OrgHead_Phone,
				rtrim(isnull(oh.OrgHead_Mobile, '')) as OrgHead_Mobile,
				rtrim(isnull(oh.OrgHead_CommissNum, '')) as OrgHead_CommissNum,
				rtrim(isNull(convert(varchar,cast(oh.OrgHead_CommissDate as datetime),104),'')) as OrgHead_CommissDate,
				rtrim(isnull(oh.OrgHead_Address, '')) as OrgHead_Address
			from
				v_OrgHead as oh with(nolock)
				inner join v_PersonState as ps with(nolock) on oh.Person_id = ps.Person_id
				inner join OrgHeadPost as ohp with(nolock) on ohp.OrgHeadPost_id = oh.OrgHeadPost_id
			where
				oh.Lpu_id = {$whereLpu} and LpuUnit_id is null and oh.OrgHeadPost_id = 7";

		$result = $this->db->query($query);

		if (is_object($result)) {
			$res['OrgHead'] = $result->result('array');
		} else {
			$res['OrgHead'] = null;
		}

		$selectPersonData = "
					rtrim(p.Person_Firname) as Person_Firname,
					rtrim(p.Person_Surname) as Person_Surname,
					rtrim(p.Person_Secname) as Person_Secname";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = p.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Surname) else rtrim(peh.PersonEncrypHIV_Encryp) end as Person_Surname,
					case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Firname) else '' end as Person_Firname,
					case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Secname) else '' end as Person_Secname";
		}



		$query = "
			select
				{$selectDate}
				{$selectPersonData}
			from {$from} t with (nolock)
				left outer join v_Person_ER p with(nolock) on t.Person_id = p.Person_id
				{$join}
				{$joinPersonEncrypHIV}
			where {$whereTimetableGraf_Day}
				and t.Person_id is not null
			order by {$orderBy}
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			$res['ttgData'] = $result->result('array');
			return $res;
		} else {
			return false;
		}
	}

	/**
	 * Редактирование переданного набора бирок
	 */
	function editTTGSet( $data ) {

		$TTGSet = json_decode( $data['selectedTTG'] );

		if ( $this->checkTTGOccupied( $TTGSet ) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Одна из выбранных бирок занята. Операция невозможна.'
			);
		}

		// Пустая строка передается как NULL, надо как пустую строку передавать
		if ( $data['ChangeTTGDescr'] ) {
			$data['TimetableExtend_Descr'] = isset( $data['TimetableExtend_Descr'] ) ? $data['TimetableExtend_Descr'] : '';
		} else {
			$data['TimetableExtend_Descr'] = NULL;
		}

		if ( $data['ChangeTTGType'] ) {
			$data['TimetableType_id'] = isset( $data['TimetableType_id'] ) ? $data['TimetableType_id'] : 1;
		} else {
			$data['TimetableType_id'] = NULL;
		}

		foreach ( $TTGSet as $TTG ) {
			$query = "
				declare
					@ErrCode int,
					@TimetableType_SysNick varchar(50),
					@ErrMessage varchar(4000);
				set @TimetableType_SysNick = (select top 1 TimetableType_SysNick from v_TimetableType (nolock) where TimetableType_id = :TimetableType_id);
				exec p_TimetableGraf_edit
					@TimetableGraf_id = :TimetableGraf_id,
					@TimetableType_id = :TimetableType_id,
					@TimetableExtend_Descr = :TimetableExtend_Descr,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @TimetableType_SysNick as TimetableType_SysNick, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$res = $this->db->query(
				//echo getDebugSQL(
				$query,
				array(
				'TimetableGraf_id' => $TTG,
				'TimetableType_id' => $data['TimetableType_id'],
				'TimetableExtend_Descr' => $data['TimetableExtend_Descr'],
				'pmUser_id' => $data['pmUser_id']
				)
			);

			if ( is_object( $res ) ) {
				$resp = $res->result( 'array' );
				if ( count( $resp ) > 0 && !empty( $resp[0]['TimetableType_SysNick'] ) ) {
					$action = $this->defineActionTypeByTimetableType( $resp[0]['TimetableType_SysNick'] );
					if ( !empty( $action ) ) {
						// отправка STOMP-сообщения
						sendFerStompMessage(
							array(
							'id' => $TTG,
							'timeTable' => 'TimetableGraf',
							'action' => $action,
							'setDate' => date( "c" )
							),
							'Rule'
						);
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
	function checkTTGOccupied( $TTGSet ) {
		if ( count( $TTGSet ) == 0 ) {
			return false;
		}
		$sql = "
			SELECT count(*) as cnt
			FROM v_TimetableGraf_lite with (nolock)
			WHERE
				TimetableGraf_id in (" . implode( ',',
				$TTGSet ) . ")
				and Person_id is not null
		";

		$res = $this->db->query(
			$sql
		);
		if ( is_object( $res ) ) {
			$res = $res->result( 'array' );
		}

		return $res[0]['cnt'] > 0;
	}

	/**
	 * Обработка перед сохранением посещения
	 * 
	 * Тут логика по обслуживанию направления и записи фактического времени посещения
	 * Бирка без записи = дополнительная бирка без привязки к направлению
	 * В результате посещение всегда должно быть связано с биркой
	 * 
	 * @param EvnVizitPL_model $evn
	 * @return array
	 * @throws Exception отменяет сохранение
	 */
	function onBeforeSaveEvnVizit(EvnVizitPL_model $evn)
	{
		$response = array(
			'TimetableGraf_id' => $evn->TimetableGraf_id,
			'EvnDirection_id' => $evn->EvnDirection_id,
		);
		if (false == in_array($evn->evnClassId, array(11,13))) {
			// ничего не делаем
			return $response;
		}
		if(empty($evn->MedStaffFact_id)){
			throw new Exception('Не указан врач', 500);
		}
		// получаем данные для проверок
		$this->load->helper('Reg');
		$day = TimeToDay(strtotime($evn->setDate));
		$params = array(
			'MedStaffFact_id' => $evn->MedStaffFact_id,
			'Person_id' => $evn->Person_id,
		);
		// убрал условие  and ttg.MedStaffFact_id = :MedStaffFact_id т.к. вполне может быть, что был записан к одному врачу, а принят другим, т.е. направление обслужено другим врачом
		$add_where = '';
		$union = '';
		$declare = '';
		switch (true) {
			case (!empty($evn->EvnDirection_id)):
				// Если принимают по бирке из арм консультативного приёма, то надо проставить в ней фактическое время приёма
				$resp_ttms = $this->queryResult("
					select
						TimetableMedService_id
					from
						v_TimetableMedService_lite (nolock)
					where
						EvnDirection_id = :EvnDirection_id
				", array(
					'EvnDirection_id' => $evn->EvnDirection_id
				));
				if (!empty($resp_ttms[0]['TimetableMedService_id'])) {
					// обновляем фактическое время приема
					$tmp = $this->swUpdate('TimetableMedService', array(
						'TimetableMedService_id' => $resp_ttms[0]['TimetableMedService_id'],
						'Evn_id' => $evn->id,
						'TimetableMedService_factTime' => $evn->setDate . ' ' . $evn->setTime,
					), false);
					if (empty($tmp) || false == is_array($tmp)) {
						throw new Exception('Ошибка запроса к БД', 500);
					}
					if (false == empty($tmp[0]['Error_Msg'])) {
						throw new Exception($tmp[0]['Error_Msg'], 500);
					}
				}

				// Врач принимает по записи или редактирует посещение, созданное по направлению/записи на бирку или из очереди или созданное без записи и без направления
				$params['EvnDirection_id'] = $evn->EvnDirection_id;
				if ( !$evn->isNewRecord && !empty($evn->TimetableGraf_id) ) {
					$params['TimetableGraf_id'] = $evn->TimetableGraf_id;
					$union = "union all
						select top 1
							null as EvnStatus_id,
							null as DirType_id,
							ttg.RecClass_id,
							ttg.TimeTableGraf_IsModerated,
							ttg.Evn_id,
							ttg.TimeTableGraf_Mark,
							ttg.TimetableGraf_IsDop,
							ttg.TimetableType_id,
							ttg.TimetableGraf_Time,
							ttg.TimetableGraf_begTime,
							ttg.TimetableGraf_factTime,
							ttg.TimetableGraf_Day,
							ttg.EvnDirection_id,
							ttg.TimetableGraf_id,
							ttg.MedStaffFact_id,
							ttg.Person_id
						from v_TimetableGraf_lite ttg with (nolock)
						where ttg.TimetableGraf_id = :TimetableGraf_id and ttg.Person_id = :Person_id
							and ttg.MedStaffFact_id is not null
							-- чтобы исключить удаленные направления
							and @d_id is null
					";
				}
				$declare = "Declare @d_id as bigint = (select top 1 EvnDirection_id from v_EvnDirection_all ed (nolock) where EvnDirection_id = :EvnDirection_id);";
				$add_where = ' and ED.EvnDirection_id = :EvnDirection_id and ED.Person_id = :Person_id';
				$needTtgData = 'v_EvnDirection_all ED with (nolock)
				left join v_TimetableGraf_lite ttg with (nolock) on ttg.EvnDirection_id = ED.EvnDirection_id and ttg.MedStaffFact_id is not null';
				if ($evn->isNewRecord) {
					// принять можно только по необслуженному направлению
					$add_where .= ' and not exists (
					select top 1 e.EvnVizit_id
					from v_EvnVizit e (nolock)
					where e.EvnDirection_id = ED.EvnDirection_id
					)';
					//когда статусы будут нормально проставляться, тогда можно будет сделать так:
					//$add_where .= ' and ED.EvnStatus_id <> 15';
				}
				break;
			case (!empty($evn->TimetableGraf_id)):
				// Врач принимает по записи или редактирует посещение, созданное без записи и без направления
				$params['TimetableGraf_id'] = $evn->TimetableGraf_id;
				$add_where = ' and ttg.TimetableGraf_id = :TimetableGraf_id and ttg.Person_id = :Person_id and ttg.MedStaffFact_id is not null';
				$needTtgData = 'v_TimetableGraf_lite ttg with (nolock)
				left join v_EvnDirection_all ED with (nolock) on ttg.TimeTableGraf_id = ED.TimeTableGraf_id';
				if ($evn->isNewRecord) {
					// принять можно только по необслуженному направлению
					$add_where .= ' and not exists (
					select top 1 e.EvnVizit_id
					from v_EvnVizit e (nolock)
					where e.TimetableGraf_id = ttg.TimetableGraf_id
					)';
					//$add_where .= ' and ttg.TimetableGraf_factTime is null';
				}
				break;
			default:
				// Пациент принимается без записи и без выбранного направления
				// Пациент может быть записан на день приема или на поздний день по направлению
				// В этом случае никакая бирка освобождаться и заниматься не должна. Приём без записи.
				$needTtgData = false;
				break;
		}
		$ttg_rows = array();
		if ($needTtgData) {
			$query = "
				{$declare}
				select top 10
					ED.EvnStatus_id,
					ED.DirType_id,
					ttg.RecClass_id,
					ttg.TimeTableGraf_IsModerated,
					ttg.Evn_id,
					ttg.TimeTableGraf_Mark,
					ttg.TimetableGraf_IsDop,
					ttg.TimetableType_id,
					ttg.TimetableGraf_Time,
					ttg.TimetableGraf_begTime,
					ttg.TimetableGraf_factTime,
					ttg.TimetableGraf_Day,
					ISNULL(ttg.EvnDirection_id, ED.EvnDirection_id) as EvnDirection_id,
					ttg.TimetableGraf_id,
					ttg.MedStaffFact_id,
					ttg.Person_id
				from {$needTtgData}
				where (1=1) {$add_where}
				{$union}
				order by TimetableGraf_IsDop asc, EvnDirection_id desc
			";
			// throw new Exception(getDebugSQL($query, $params));
			$res = $this->db->query($query, $params);
			if ( is_object($res) ) {
				$ttg_rows = $res->result('array');
			} else {
				throw new Exception('Не удалось выполнить запрос данных бирки', 500);
			}
		}
		if ($needTtgData && empty($ttg_rows)) {
			//throw new Exception('Не удалось получить данные бирки', 500);
			// Пользователю ничего не показываем, считаем что $needTtgData не требуется 
			$needTtgData = false;
		}
		$need_free_ttg = false;
		$need_dop_ttg = false;
		$need_del_dop_ttg = false;
		$need_clear_ttg_fact_time = false;
		$need_update_ttg_fact_time = false;
		$need_set_serviced = false;
		$ttg_data = null;
		$ttg_dop_data = null;
		if (!empty($ttg_rows) ) {
			// благодаря order by TimetableGraf_IsDop asc, EvnDirection_id desc бирки без записи будут последними
			$ttg_data = $ttg_rows[0];
			if (empty($response['EvnDirection_id']) && isset($ttg_rows[0]['EvnDirection_id'])) {
				$response['EvnDirection_id'] = $ttg_rows[0]['EvnDirection_id'];
			}
			if (empty($response['DirType_id'])) {
				$response['DirType_id'] = $ttg_rows[0]['DirType_id'];
			}
			foreach ($ttg_rows as $row) {
				if (1 == $row['TimetableGraf_IsDop'] && empty($row['TimetableGraf_begTime']) && empty($ttg_dop_data)) {
					$ttg_dop_data = $row;
				}
			}
		}
		/*
		 * 1) Создание посещения
		 * 1.1) Врач принимает по записи на бирку на день приема.
		 * На бирке записывается фактическое время, направление обслуживается
		 * 1.2) Врач принимает по записи на бирку на прошедший день.
		 * Создается бирка без записи на день приема, направление обслуживается
		 * 1.3) Врач принимает по записи на бирку на будущий день.
		 * Бирка освобождается, создается бирка без записи на день приема, направление обслуживается
		 * 1.4) Врач принимает по направлению из очереди.
		 * Создается бирка без записи на день приема, направление обслуживается
		 * 1.5) Врач принимает без записи.
		 * Создается бирка без записи на день приема
		 * 
		 * 2) Редактирование посещения (изменилась дата/время посещения/врач/было выбрано направление)
		 * 2.1) Изменилась дата или врач посещения, созданного без записи и без направления
		 * Удаляется старая бирка без записи, создается новая бирка без записи на день приема в расписании принявшего врача
		 * 2.2) Было выбрано направление для посещения, созданного без записи и без направления, с записью на бирку на день приема.
		 * Удаляется старая бирка без записи, на бирке записывается фактическое время, направление обслуживается
		 * 2.3) Было выбрано направление для посещения, созданного без записи и без направления, с записью на бирку на прошедший день.
		 * п. 2.1 + направление обслуживается
		 * 2.4) Было выбрано направление для посещения, созданного без записи и без направления, с записью на бирку на будущий день.
		 * п. 2.1 + бирка освобождается, направление обслуживается
		 * 2.5) Было выбрано направление из очереди для посещения, созданного без записи и без направления.
		 * п. 2.1 + направление обслуживается
		 * 2.6) Изменилась дата или врач посещения, созданного по направлению, которое было обслужено в день бирки
		 * Бирка освобождается, создается новая бирка без записи на день приема в расписании принявшего врача
		 * 2.7) Изменилась дата или врач посещения, созданного по направлению, которое было обслужено не в день бирки
		 * Удаляется старая бирка без записи, создается новая бирка без записи на день приема в расписании принявшего врача
		 * 2.8) Изменилось только время посещения
		 * На бирке записывается фактическое время
		 */
		switch (true) {
			case (empty($ttg_data) /*&& $evn->isNewRecord && empty($response['EvnDirection_id'])*/): // 1.5
				$need_dop_ttg = true;
				break;
			case ($evn->isNewRecord && $ttg_data['TimetableGraf_Day'] == $day): // 1.1
				if ($ttg_data['MedStaffFact_id'] != $evn->MedStaffFact_id) {
					$need_free_ttg = $ttg_data;
					$need_dop_ttg = true;
				} else {
					$need_update_ttg_fact_time = $ttg_data['TimetableGraf_id'];
				}
				$need_set_serviced = $response['EvnDirection_id'];
				break;
			case ($evn->isNewRecord && empty($ttg_data['TimetableGraf_Day'])): // 1.4
			case ($evn->isNewRecord && !empty($ttg_data['TimetableGraf_Day']) && $ttg_data['TimetableGraf_Day'] < $day): // 1.2
				$need_dop_ttg = true;
				$need_set_serviced = $response['EvnDirection_id'];
				if (!empty($ttg_data['TimetableGraf_factTime'])) {
					$need_clear_ttg_fact_time = $ttg_data['TimetableGraf_id'];
				}
				break;
			case ($evn->isNewRecord && !empty($ttg_data['TimetableGraf_Day']) && $ttg_data['TimetableGraf_Day'] > $day): // 1.3
				$need_free_ttg = $ttg_data;
				$need_dop_ttg = true;
				$need_set_serviced = $response['EvnDirection_id'];
				break;
			case (false == $evn->isNewRecord && !empty($response['EvnDirection_id']) && $evn->TimetableGraf_id  && !empty($ttg_data['TimetableGraf_Day'])
				&& is_array($ttg_dop_data) && $ttg_dop_data['TimetableGraf_id'] == $evn->TimetableGraf_id
				&& $ttg_data['TimetableGraf_id'] != $evn->TimetableGraf_id && $ttg_data['EvnStatus_id'] != 15 && $ttg_data['TimetableGraf_Day'] == $day
				): // 2.2
				$need_del_dop_ttg = $ttg_dop_data['TimetableGraf_id'];
				$need_update_ttg_fact_time = $ttg_data['TimetableGraf_id'];
				$need_set_serviced = $response['EvnDirection_id'];
				break;
			case (false == $evn->isNewRecord && !empty($response['EvnDirection_id']) && $evn->TimetableGraf_id  && !empty($ttg_data['TimetableGraf_Day'])
				&& is_array($ttg_dop_data) && $ttg_dop_data['TimetableGraf_id'] == $evn->TimetableGraf_id 
				&& $ttg_data['TimetableGraf_id'] != $evn->TimetableGraf_id && $ttg_data['EvnStatus_id'] != 15 && $ttg_data['TimetableGraf_Day'] < $day
				): // 2.3
				if ($ttg_dop_data['TimetableGraf_Day'] != $day) {
					$need_del_dop_ttg = $ttg_dop_data['TimetableGraf_id'];
					$need_dop_ttg = true;
				} else {
					$need_update_ttg_fact_time = $ttg_dop_data['TimetableGraf_id'];
				}
				$need_set_serviced = $response['EvnDirection_id'];
				if (!empty($ttg_data['TimetableGraf_factTime'])) {
					$need_clear_ttg_fact_time = $ttg_data['TimetableGraf_id'];
				}
				break;
			case (false == $evn->isNewRecord && !empty($response['EvnDirection_id']) && $evn->TimetableGraf_id  && !empty($ttg_data['TimetableGraf_Day'])
				&& is_array($ttg_dop_data) && $ttg_dop_data['TimetableGraf_id'] == $evn->TimetableGraf_id 
				&& $ttg_data['TimetableGraf_id'] != $evn->TimetableGraf_id && $ttg_data['EvnStatus_id'] != 15 && $ttg_data['TimetableGraf_Day'] > $day
				): // 2.4
				if ($ttg_dop_data['TimetableGraf_Day'] != $day) {
					$need_del_dop_ttg = $ttg_dop_data['TimetableGraf_id'];
					$need_dop_ttg = true;
				} else {
					$need_update_ttg_fact_time = $ttg_dop_data['TimetableGraf_id'];
				}
				$need_free_ttg = $ttg_data;
				$need_set_serviced = $response['EvnDirection_id'];
				break;
			case (false == $evn->isNewRecord && !empty($response['EvnDirection_id']) && $evn->TimetableGraf_id  && empty($ttg_data['TimetableGraf_Day'])
				&& is_array($ttg_dop_data) && $ttg_dop_data['TimetableGraf_id'] == $evn->TimetableGraf_id
				&& isset($ttg_data['EvnStatus_id']) && $ttg_data['EvnStatus_id'] != 15
				): // 2.5
				if ($ttg_dop_data['TimetableGraf_Day'] != $day) {
					$need_del_dop_ttg = $ttg_dop_data['TimetableGraf_id'];
					$need_dop_ttg = true;
				} else {
					$need_update_ttg_fact_time = $ttg_dop_data['TimetableGraf_id'];
				}
				$need_set_serviced = $response['EvnDirection_id'];
				break;
			case (false == $evn->isNewRecord && $evn->TimetableGraf_id && $ttg_data['TimetableGraf_id'] == $evn->TimetableGraf_id
				&& ($ttg_data['TimetableGraf_Day'] != $day || $ttg_data['MedStaffFact_id'] != $evn->MedStaffFact_id)
			): // 2.1, 2.6, 2.7
				$need_free_ttg = $ttg_data;
				$need_dop_ttg = true;
				break;
			case (false == $evn->isNewRecord && $evn->TimetableGraf_id && is_array($ttg_dop_data) && $ttg_dop_data['TimetableGraf_id'] == $evn->TimetableGraf_id
				&& ($ttg_dop_data['TimetableGraf_Day'] != $day || $ttg_dop_data['MedStaffFact_id'] != $evn->MedStaffFact_id)
			): // 2.1, 2.6, 2.7
				$need_del_dop_ttg = $ttg_dop_data['TimetableGraf_id'];
				if ($ttg_data['TimetableGraf_Day'] == $day && $ttg_dop_data['TimetableGraf_id'] != $ttg_data['TimetableGraf_id']) {
					$need_update_ttg_fact_time = $ttg_data['TimetableGraf_id'];
				} else {
					$need_dop_ttg = true;
				}
				break;
			case (false == $evn->isNewRecord && $ttg_data['TimetableGraf_Day'] == $day): // 2.8
				$need_update_ttg_fact_time = $ttg_data['TimetableGraf_id'];
				if (is_array($ttg_dop_data) && $ttg_dop_data['TimetableGraf_id'] != $ttg_data['TimetableGraf_id']) {
					$need_del_dop_ttg = $ttg_dop_data['TimetableGraf_id'];
				}
				break;
			case (false == $evn->isNewRecord && empty($evn->TimetableGraf_id)):
				// это не исключено
				if ($ttg_data['TimetableGraf_id'] && $ttg_data['TimetableGraf_Day'] == $day) {
					$need_update_ttg_fact_time = $ttg_data['TimetableGraf_id'];
				} else if ($ttg_data['TimetableGraf_id'] && $ttg_data['TimetableGraf_Day'] > $day) {
					$need_free_ttg = $ttg_data;
				} else {
					$need_dop_ttg = true;
				}
				break;
			case (false == $evn->isNewRecord && !empty($response['EvnDirection_id'])):
				// если выбрали направление и ни под одно из вышестоящих условий наш случай не подходит, то просто обслуживаем направление
				// сюда заходит, например, если человек принят без записи и затем в его талоне выбирают направление поставленное в очередь
				$need_set_serviced = $response['EvnDirection_id'];
				break;
			default:
				// на случай, если что-то не учли
				$debug = array(
					'evn_day' => $day,
					'evn_EvnDirection_id' => $evn->EvnDirection_id,
					'evn_TimetableGraf_id' => $evn->TimetableGraf_id,
					'evn_MedStaffFact_id' => $evn->MedStaffFact_id,
					'ttg_TimetableGraf_id' => $ttg_data['TimetableGraf_id'],
					'ttg_TimetableGraf_Day' => $ttg_data['TimetableGraf_Day'],
					'ttg_EvnDirection_id' => $ttg_data['EvnDirection_id'],
					'ttg_MedStaffFact_id' => $ttg_data['MedStaffFact_id'],
					'ttg_EvnStatus_id' => $ttg_data['EvnStatus_id'],
					'ttg_dop_TimetableGraf_id' => is_array($ttg_dop_data) ? $ttg_dop_data['TimetableGraf_id'] : null,
					'ttg_dop_TimetableGraf_Day' => is_array($ttg_dop_data) ? $ttg_dop_data['TimetableGraf_Day'] : null,
					'ttg_dop_EvnDirection_id' => is_array($ttg_dop_data) ? $ttg_dop_data['EvnDirection_id'] : null,
					'ttg_dop_MedStaffFact_id' => is_array($ttg_dop_data) ? $ttg_dop_data['MedStaffFact_id'] : null,
					'ttg_dop_EvnStatus_id' => is_array($ttg_dop_data) ? $ttg_dop_data['EvnStatus_id'] : null,
					'response_EvnDirection_id' => $response['EvnDirection_id'],
					'response_TimetableGraf_id' => $response['TimetableGraf_id'],
				);
				log_message('error', 'Error in conditions fact time write. Data: '. var_export($debug, true));
				/*if ($this->isDebug) {
					// только на тестовом выводим ошибку и не даем сохранить посещение
					throw new Exception('Ошибка в условиях записи фактического времени посещения', 500);
				}*/
				break;
		}

		$debug = array(
			'day' => $day,
			'evn_EvnDirection_id' => $evn->EvnDirection_id,
			'evn_TimetableGraf_id' => $evn->TimetableGraf_id,
			'EvnDirection_id' => $response['EvnDirection_id'],
			'needTtgData' => $needTtgData,
			'need_del_dop_ttg' => $need_del_dop_ttg,
			'need_free_ttg' => $need_free_ttg,
			'need_dop_ttg' => $need_dop_ttg,
			'need_set_serviced' => $need_set_serviced,
			'need_update_ttg_fact_time' => $need_update_ttg_fact_time,
			'need_clear_ttg_fact_time' => $need_clear_ttg_fact_time,
		);
		//throw new Exception(var_export($debug, true), 700);

		if ($need_del_dop_ttg) {
			$tmp = $this->execCommonSP('p_TimetableGraf_del', array(
				'pmUser_id' => $evn->promedUserId,
				'TimetableGraf_id' => $need_del_dop_ttg
			), 'array_assoc');
			if (empty($tmp)) {
				throw new Exception('Ошибка запроса к БД', 500);
			}
			if (isset($tmp['Error_Msg'])) {
				throw new Exception($tmp['Error_Msg'], 500);
			}
		}
		if ($need_free_ttg && !empty($need_free_ttg['TimetableGraf_id'])) {
			/*
			 * Освободить бирку
			 * p_TimeTableGraf_cancel не подходит, т.к.
			 * 1) Освободить бирку это не тоже самое, что отменить запись
			 * 2) если бирка создана на человека без записи, то она не удаляется
			 * 3) не очищается ссылка на посещение Evn_id, которая сохраняется по задаче #64480
			 */
			if (1 == $need_free_ttg['TimetableGraf_IsDop'] && empty($need_free_ttg['TimetableGraf_begTime']) ) {
				// удалять бирку, если она создана на человека без записи
				$tmp = $this->execCommonSP('p_TimetableGraf_del', array(
					'pmUser_id' => $evn->promedUserId,
					'TimetableGraf_id' => $need_free_ttg['TimetableGraf_id']
				), 'array_assoc');
				if (empty($tmp)) {
					throw new Exception('Ошибка запроса к БД', 500);
				}
				if (isset($tmp['Error_Msg'])) {
					throw new Exception($tmp['Error_Msg'], 500);
				}
			} else {
				$this->load->library('textlog', array('file'=>'free_ttg_'.date('Y-m-d', time()).'.log', 'logging'=>true), 'ttlog');
				$this->ttlog->add(print_r($debug, true));

				// освобождаю бирку без использования p_TimetableGraf_upd, т.к. в ней нет работы с историей и есть изменение поля TimetableGraf_updDT
				$tmp = $this->swUpdate('TimetableGraf', array(
					'TimetableGraf_id' => $need_free_ttg['TimetableGraf_id'],
					'EvnDirection_id' => null,
					'Evn_id' => null,
					'Person_id' => null,
					'RecClass_id' => null,
					'TimetableGraf_factTime' => null,
					'TimetableGraf_IsModerated' => null,
					'pmUser_id' => $evn->promedUserId,
				), true);
				if (empty($tmp) || false == is_array($tmp)) {
					throw new Exception('Ошибка запроса к БД', 500);
				}
				if (false == empty($tmp[0]['Error_Msg'])) {
					throw new Exception($tmp[0]['Error_Msg'], 500);
				}
				if (!empty($need_free_ttg['EvnDirection_id'])) {
					// также убираем ссылку в направлении
					$tmp = $this->swUpdate('EvnDirection', array(
						'EvnDirection_id' => $need_free_ttg['EvnDirection_id'],
						'TimetableGraf_id' => null,
					), false);
				}
				if (empty($tmp) || false == is_array($tmp)) {
					throw new Exception('Ошибка запроса к БД', 500);
				}
				if (false == empty($tmp[0]['Error_Msg'])) {
					throw new Exception($tmp[0]['Error_Msg'], 500);
				}
				// Обновляем кэш по дню
				$tmp = $this->execCommonSP('p_MedPersonalDay_recount', array(
					'MedStaffFact_id' => $need_free_ttg['MedStaffFact_id'],
					'Day_id' => $need_free_ttg['TimetableGraf_Day'],
					'pmUser_id' => $evn->promedUserId,
				), 'array_assoc');
				if (empty($tmp)) {
					throw new Exception('Ошибка запроса к БД', 500);
				}
				if (isset($tmp['Error_Msg'])) {
					throw new Exception($tmp['Error_Msg'], 500);
				}
				// Заносим изменения бирки в историю
				$tmp = $this->execCommonSP('p_AddTTGToHistory', array(
					'TimeTableGraf_id' => $need_free_ttg['TimetableGraf_id'],
					'TimeTableGrafAction_id' => 3, // Освобождение бирки
					'pmUser_id' => $evn->promedUserId,
				), 'array_assoc');
				if (empty($tmp)) {
					throw new Exception('Ошибка запроса к БД', 500);
				}
				if (isset($tmp['Error_Msg'])) {
					throw new Exception($tmp['Error_Msg'], 500);
				}
			}
			if ($evn->TimetableGraf_id == $need_free_ttg['TimetableGraf_id']) {
				$response['TimetableGraf_id'] = null;
			}
		}
		if ($need_clear_ttg_fact_time) {
			// очищаем фактическое время приема
			$tmp = $this->swUpdate('TimetableGraf', array(
				'TimetableGraf_id' => $need_clear_ttg_fact_time,
				'EvnDirection_id' => $response['EvnDirection_id'],
				'Evn_id' => null,
				'TimetableGraf_factTime' => null,
			), false);
			if (empty($tmp) || false == is_array($tmp)) {
				throw new Exception('Ошибка запроса к БД', 500);
			}
			if (false == empty($tmp[0]['Error_Msg'])) {
				throw new Exception($tmp[0]['Error_Msg'], 500);
			}
			if ($evn->TimetableGraf_id == $need_clear_ttg_fact_time) {
				$response['TimetableGraf_id'] = null;
			}
		}
		if ($need_update_ttg_fact_time) {
			// обновляем фактическое время приема
			$tmp = $this->swUpdate('TimetableGraf', array(
				'TimetableGraf_id' => $need_update_ttg_fact_time,
				'EvnDirection_id' => $response['EvnDirection_id'],
				'Evn_id' => $evn->id,
				'TimetableGraf_factTime' => $evn->setDate . ' ' . $evn->setTime,
			), false);
			if (empty($tmp) || false == is_array($tmp)) {
				throw new Exception('Ошибка запроса к БД', 500);
			}
			if (false == empty($tmp[0]['Error_Msg'])) {
				throw new Exception($tmp[0]['Error_Msg'], 500);
			}
			$response['TimetableGraf_id'] = $need_update_ttg_fact_time;
		}
		if ($need_dop_ttg) {
			// создание дополнительной бирки на день незапланированного приема
			$tmp = $this->execCommonSP('p_TimetableGraf_ins', array(
				'TimetableGraf_id' => array(
					'value' => null,
					'out' => true,
					'type' => 'bigint',
				),
				'RecClass_id' => 1,// 3?
				'TimetableGraf_IsDop' => 1,
				'TimetableType_id' => 1,
				'TimetableGraf_Time' => 0,
				'TimetableGraf_begTime' => null, //время запланированного приема, заполняется при создании расписания
				'TimetableGraf_factTime' => $evn->setDate . ' ' . $evn->setTime,
				'TimetableGraf_Day' => $day,
				'EvnDirection_id' => $response['EvnDirection_id'],
				'Evn_id' => $evn->id,
				'MedStaffFact_id' => $evn->MedStaffFact_id,
				'Person_id' => $evn->Person_id,
				'pmUser_id' => $evn->promedUserId,
			), 'array_assoc');
			if (empty($tmp)) {
				throw new Exception('Ошибка запроса к БД', 500);
			}
			if (isset($tmp['Error_Msg'])) {
				throw new Exception($tmp['Error_Msg'], 500);
			}
			$response['TimetableGraf_id'] = $tmp['TimetableGraf_id'];
		}
		if ($need_set_serviced) {
			if (empty($response['DirType_id'])) {
				// Если направление без типа обслуживается врачом поликлиники/стоматологии, то принудительно присаивать ему тип "на поликлинический прием".
				$tmp = $this->swUpdate('EvnDirection', array(
					'DirType_id' => 16,
					'EvnDirection_id' => $need_set_serviced,
				), false);
				if (empty($tmp) || false == is_array($tmp)) {
					throw new Exception('Ошибка запроса к БД', 500);
				}
				if (false == empty($tmp[0]['Error_Msg'])) {
					throw new Exception($tmp[0]['Error_Msg'], 500);
				}
				$response['DirType_id'] = 16;
			}
			// переводим в статус “Обслужено”
			$this->load->model('EvnDirectionAll_model');
			$this->EvnDirectionAll_model->setStatus(array(
				'Evn_id' => $need_set_serviced,
				'EvnStatus_SysNick' => EvnDirectionAll_model::EVN_STATUS_DIRECTION_SERVICED,
				'EvnClass_id' => $this->EvnDirectionAll_model->evnClassId,
				'pmUser_id' => $evn->promedUserId,
			));
		}
		return $response;
	}

	/**
	 * Обработка после сохранения посещения
	 * Должна выполняться внутри транзакции
	 * @param EvnVizitPL_model $evn
	 * @return bool
	 * @throws Exception отменяет сохранение
	 */
	function onAfterSaveEvnVizit(EvnVizitPL_model $evn)
	{
		if (false == in_array($evn->evnClassId, array(11,13))) {
			// ничего не делаем
			return true;
		}
		if ($evn->isNewRecord && $evn->TimetableGraf_id) {
			// #64480 сохраняем ссылку на посещение
			$tmp = $this->swUpdate('TimetableGraf', array(
				'TimetableGraf_id' => $evn->TimetableGraf_id,
				'Evn_id' => $evn->id,
			), false);
			if (empty($tmp) || false == is_array($tmp)) {
				throw new Exception('Ошибка запроса к БД', 500);
			}
			if (false == empty($tmp[0]['Error_Msg'])) {
				throw new Exception($tmp[0]['Error_Msg'], 500);
			}
			if ($evn->EvnDirection_id) {
				$tmp = $this->swUpdate('EvnDirection', array(
					'TimetableGraf_id' => $evn->TimetableGraf_id,
					'EvnDirection_id' => $evn->EvnDirection_id
				), false);
				if (empty($tmp) || false == is_array($tmp)) {
					throw new Exception('Ошибка запроса к БД', 500);
				}
				if (false == empty($tmp[0]['Error_Msg'])) {
					throw new Exception($tmp[0]['Error_Msg'], 500);
				}
			}
		}
		return true;
	}


	/**
	 * Обработка удаления ТАП/посещения (перед удалением)
	 * Должна выполняться внутри транзакции
	 * @param EvnAbstract_model $evn
	 * @return bool
	 * @throws Exception отменяет удаление
	 */
	function onBeforeDeleteEvn(EvnAbstract_model $evn)
	{
		if (false == in_array($evn->evnClassId, array(3,6,11,13))) {
			// ничего не делаем
			return true;
		}

		if (in_array($evn->evnClassId, array(3,6))) {
			// в p_EvnPL_setdel, p_EvnPLStom_setdel
			// в посещениях чистятся ссылки TimetableGraf_id
			// поэтому нужно получить $data['TimetableGrafArr'] до удаления
			// получить данные бирок по посещениям в рамках данного случая
			// и восстановить расписание, как будто случая лечения не было
			$where = 'EvnVizit.EvnVizit_pid = :Evn_id';
		} else {
			// в p_EvnVizitPL_setdel, p_EvnVizitPLStom_setdel
			// в посещениях чистятся ссылки TimetableGraf_id
			// поэтому нужно получить данные бирок до удаления
			$where = 'EvnVizit.EvnVizit_id = :Evn_id';
		}
		/*
		Можно попробовать реализовать следующим образом:
		при удалении случаев проверять связь с биркой и с направлением
		создавать запись в очереди только
		если бирка дополнительная и связана с направлением (т.к. для дополнительных бирок на текущий день направления вроде как не создаются, то допбирки с направлением - можно считать признаком того, что была очищена бирка на будущую дату).
		*/
		$query = "
			select
				case 
					when 1 = TimetableGraf.TimeTableGraf_IsDop AND TimetableGraf.TimeTableGraf_begTime is null AND TimetableGraf.EvnDirection_id is not null
						/* допбирка была создана по записи на прошедший или будущий день, бирка на прошедший день не освобождается */
						AND not exists(
							/* ищем бирку на прошедший день по этому же направлению к этому же врачу */
							select top 1 ttg.EvnDirection_id
							from v_TimetableGraf_lite ttg with (nolock)
							where 
								ttg.EvnDirection_id = TimetableGraf.EvnDirection_id
								and ttg.Person_id = EvnVizit.Person_id
								and ttg.MedStaffFact_id = TimetableGraf.MedStaffFact_id
								and ttg.TimeTableGraf_begTime is not null
								and CAST(ttg.TimeTableGraf_begTime as DATE) < CAST(EvnVizit.EvnVizit_setDT as DATE)
							/* возможно было обслужено направление по записи на другую бирку */
							union all
							select top 1 ttg.EvnDirection_id
							from v_TimetableGraf_lite ttg with (nolock)
							where 
								ttg.EvnDirection_id = TimetableGraf.EvnDirection_id
								and ttg.Person_id = EvnVizit.Person_id
								and ttg.MedStaffFact_id is not null
								and ttg.TimeTableGraf_begTime is not null
							/* возможно было обслужено направление на службу */
							union all
							select top 1 ttms.EvnDirection_id
							from v_TimetableMedService_lite ttms with (nolock)
							where 
								ttms.EvnDirection_id = TimetableGraf.EvnDirection_id
								and ttms.Person_id = EvnVizit.Person_id
						)
						then 'in_queue_delete_timetablegraf'
					when 1 = TimetableGraf.TimeTableGraf_IsDop AND TimetableGraf.TimeTableGraf_begTime is null
						/* допбирка была создана по записи на прошедший день или при приеме без направления */ 
						then 'delete_timetablegraf'
					when EvnVizit.TimetableGraf_id is not null 
						then 'clear_timetablegraf_facttime'
					else 'undefined'
				end as operation,
				TimetableGraf.TimetableGraf_id,
				TimetableGraf.MedStaffFact_id,
				TimetableGraf.TimetableGraf_Day,
				msf.MedPersonal_id,
				msf.LpuSection_id,
				msf.LpuUnit_id,
				msf.LpuSectionProfile_id,
				EvnVizit.EvnDirection_id
			from
				v_EvnVizit EvnVizit with (nolock)
				inner join v_TimetableGraf_lite TimetableGraf with (nolock) on (EvnVizit.TimetableGraf_id = TimetableGraf.TimetableGraf_id or TimetableGraf.Evn_id = EvnVizit.EvnVizit_id) and TimetableGraf.Person_id = EvnVizit.Person_id and TimetableGraf.MedStaffFact_id is not null
				left join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = TimetableGraf.MedStaffFact_id
			where {$where}
		";
		$params = array('Evn_id' => $evn->id);
		//throw new Exception(getdebugsql($query, $params), 400);
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			$arr = $result->result('array');
		} else {
			throw new Exception('Ошибка запроса к БД', 500);
		}
		$isAllowRollbackEvnDirectionStatus = true;
		$deletedArr = array();
		foreach ($arr as $row) {
			switch ($row['operation']) {
				case 'delete_timetablegraf':
					if (in_array($row['TimetableGraf_id'], $deletedArr)) {
						// уже удалена
						continue 2;
					}
					// удалять бирку, если она создана на человека без записи
					$tmp = $this->execCommonSP('p_TimetableGraf_del', array(
						'pmUser_id' => $evn->promedUserId,
						'TimetableGraf_id' => $row['TimetableGraf_id']
					), 'array_assoc');
					if (empty($tmp)) {
						throw new Exception('Ошибка запроса к БД', 500);
					}
					if (isset($tmp['Error_Msg'])) {
						throw new Exception($tmp['Error_Msg'], 500);
					}
					$deletedArr[] = $row['TimetableGraf_id'];
					break;
				case 'clear_timetablegraf_facttime':
					// нужно почистить Evn_id, TimetableGraf_factTime, если человек посещал по записи, чтобы на эту бирку можно было завести другое посещение.
					$tmp = $this->swUpdate('TimetableGraf', array(
						'TimetableGraf_id' => $row['TimetableGraf_id'],
						'TimetableGraf_factTime' => null,
						'Evn_id' => null,
					), false);
					if (empty($tmp) || false == is_array($tmp)) {
						throw new Exception('Ошибка запроса к БД', 500);
					}
					if (false == empty($tmp[0]['Error_Msg'])) {
						throw new Exception($tmp[0]['Error_Msg'], 500);
					}
					break;
				case 'in_queue_delete_timetablegraf':
					// бирка была освобождена, ставим в очередь по профилю отделения
					if (empty($row['LpuSectionProfile_id'])) {
						throw new Exception('Освобожденная бирка была занята. Невозможно поставить в очередь по профилю!', 500);
					}
					$tmp = $this->execCommonSP('p_EvnQueue_ins', array(
						'EvnQueue_id' => null,
						'EvnUslugaPar_id' => null,
						'MedService_did' => null,
						'EvnQueue_pid' => null,
						'EvnDirection_id' => $row['EvnDirection_id'],
						'LpuSectionProfile_did' => $row['LpuSectionProfile_id'],
						'LpuUnit_did' => $row['LpuUnit_id'],
						'MedPersonal_did' => $row['MedPersonal_id'],
						'LpuSection_did' => $row['LpuSection_id'],
						'Lpu_id' => $evn->sessionParams['lpu_id'],
						'EvnQueue_setDT' => $evn->currentDT->format('Y-m-d H:i:s'),
						'PersonEvn_id' => $evn->PersonEvn_id,
						'Server_id' => $evn->Server_id,
						'pmUser_id' => $evn->promedUserId,
					), 'array_assoc');
					if (empty($tmp)) {
						throw new Exception('Ошибка запроса к БД', 500);
					}
					if (isset($tmp['Error_Msg'])) {
						throw new Exception($tmp['Error_Msg'], 500);
					}
					// переводим в статус “Поставлено в очередь”
					$this->load->model('EvnDirectionAll_model');
					$this->EvnDirectionAll_model->setStatus(array(
						'Evn_id' => $row['EvnDirection_id'],
						'EvnStatus_SysNick' => EvnDirectionAll_model::EVN_STATUS_DIRECTION_IN_QUEUE,
						'EvnClass_id' => $this->EvnDirectionAll_model->evnClassId,
						'pmUser_id' => $evn->promedUserId,
					));
					if ($evn->EvnDirection_id == $row['EvnDirection_id']) {
						$isAllowRollbackEvnDirectionStatus = false;
					}
					// удалять бирку, если она создана на человека без записи
					$tmp = $this->execCommonSP('p_TimetableGraf_del', array(
						'pmUser_id' => $evn->promedUserId,
						'TimetableGraf_id' => $row['TimetableGraf_id']
					), 'array_assoc');
					if (empty($tmp)) {
						throw new Exception('Ошибка запроса к БД', 500);
					}
					if (isset($tmp['Error_Msg'])) {
						throw new Exception($tmp['Error_Msg'], 500);
					}
					break;
			}
			// Возвращаем направлению предыдущий статус
			if (!empty($evn->EvnDirection_id) && $isAllowRollbackEvnDirectionStatus) {
				$this->load->model('EvnDirectionAll_model');
				$this->EvnDirectionAll_model->rollbackStatus(array(
					'Evn_id' => $evn->EvnDirection_id,
					'EvnStatus_SysNick' => EvnDirectionAll_model::EVN_STATUS_DIRECTION_SERVICED,
					'EvnClass_id' => $this->EvnDirectionAll_model->evnClassId,
					'pmUser_id' => $evn->promedUserId,
				));
			}
		}
		// все прекрасно, ошибок нет
		return true;
	}

	/**
	 * Перенос бирки с одного события на другое, используется при смене пациента в документе.
	 */
	function onSetAnotherPersonForDocument($data) {
		$this->db->query("update TimetableGraf with (rowlock) set Evn_id = :Evn_id, Person_id = :Person_id where Evn_id = :Evn_oldid", $data);
	}

	/**
	 * Получение списка записанных в МО. Метод для API
	 */
	function loadTimeTableGrafListbyMO($data)
	{
		$query = "
			select distinct
				convert(varchar(19), ttg.TimeTableGraf_begTime, 120) as TimeTableGraf_begTime,
				isnull(psa2.PersonInn_Inn, 999999999999) as PersonInn_Inn,
				msf.Post_id,
				psa.PersonSurName_SurName,
				psa.PersonFirName_FirName,
				psa.PersonSecName_SecName
			from
				v_TimeTableGraf_lite ttg with (nolock)
				inner join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = ttg.MedStaffFact_id
				outer apply (
					select top 1
						psai.PersonSurName_SurName,
						psai.PersonFirName_FirName,
						psai.PersonSecName_SecName
					from MedPersonalCache mpc with (nolock)
					inner join v_PersonStateAll psai with (nolock) on psai.Person_id = mpc.Person_id
					inner join Person pr with (nolock) on pr.Person_id = psai.Person_id
					where mpc.MedPersonal_id = msf.MedPersonal_id 
						and mpc.Lpu_id = msf.Lpu_id
						and ISNULL(pr.Person_deleted, 1) = 1
				) psa
				outer apply (
					select top 1
						psai2.PersonInn_Inn
					from v_PersonStateAll psai2 with (nolock) 
					inner join Person pr2 with (nolock) on pr2.Person_id = psai2.Person_id
					where psai2.Person_id = ttg.Person_id
						and ISNULL(pr2.Person_deleted, 1) = 1
				) psa2
			where
				ttg.TimeTableGraf_begTime >= cast(:TimeTableGraf_beg as datetime) 
				and ttg.TimeTableGraf_begTime <= cast(:TimeTableGraf_end as datetime)
				and msf.Lpu_id = :Lpu_id
				and ttg.Person_id is not null
		";

		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка записанных к врачу. Метод для API
	 */
	public function loadTimeTableGrafByMedStaffFact($data)
	{
		$fields = '';
		$from = '';
		if(isset($data['extended'])){
			$fields = "
				,Person.Person_SurName + ' ' + Person.Person_FirName + ' ' + Person.Person_SecName as FIO,
				convert(varchar(10), Person.Person_BirthDay, 104) as Person_BirthDay";
			$from = " left join v_PersonState Person with (nolock) on ttg.Person_id = Person.Person_id";
		}
		$query = "
			select
				ttg.TimeTableGraf_id,
				ttg.Person_id,
				convert(varchar(19), ttg.TimeTableGraf_begTime, 120) as TimeTableGraf_begTime,
				convert(varchar(19), ttg.TimeTableGraf_factTime, 120) as TimeTableGraf_factTime
				{$fields}
			from
				v_TimeTableGraf_lite ttg with (nolock)
				{$from}
			where 1=1
				and ttg.TimeTableGraf_begTime >= cast(:TimeTableGraf_beg as datetime) 
				and ttg.TimeTableGraf_begTime <= cast(:TimeTableGraf_end as datetime)
				and ttg.MedStaffFact_id = :MedStaffFact_id
				and ttg.Person_id is not null
		";

		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение свободных дат приема
	 */
	function getTimeTableGrafFreeDate($data) {

		$filter = null;

		if (!empty($data['freeforinternetrecord']))
		{
			$filter = ' and TimeTableType_id in (1, 9, 11) and TimeTableGraf_begTime >= dbo.tzGetDate() ';
		}

		$resp = $this->queryResult("
			select distinct
				convert(varchar(10), TimetableGraf_begTime, 120) as TimeTableGraf_begTime
			from
				v_TimeTableGraf_lite (nolock)
			where
				MedStaffFact_id = :MedStaffFact_id
				and cast(TimetableGraf_begTime as date) >= :TimeTableGraf_beg
				and cast(TimetableGraf_begTime as date) <= :TimeTableGraf_end
				and Person_id is null
				{$filter}
		", array(
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'TimeTableGraf_beg' => $data['TimeTableGraf_beg'],
			'TimeTableGraf_end' => $data['TimeTableGraf_end']
		));

		return $resp;
	}

	/**
	 * Получение свободного времени приема
	 */
	function getTimeTableGrafFreeTime($data) {
		$resp = $this->queryResult("
			select
				TimeTableGraf_id,
				convert(varchar(19), TimetableGraf_begTime, 120) as TimeTableGraf_begTime,
				TimeTableGraf_Time,
				TimeTableType_id
			from
				v_TimeTableGraf_lite (nolock)
			where
				MedStaffFact_id = :MedStaffFact_id
				and cast(TimetableGraf_begTime as date) = :TimeTableGraf_begTime
				and Person_id is null
			order by
				TimetableGraf_begTime asc
		", array(
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'TimeTableGraf_begTime' => $data['TimeTableGraf_begTime']
		));

		return $resp;
	}

	/**
	 * Запись на бирку из РИШ
	 */
	function writeTimetableGraf($data) {
		$resp_ttg = $this->queryResult("
			select
				msf.Lpu_id,
				ps.Server_id,
				ps.PersonEvn_id
			from
				v_TimeTableGraf_lite ttg (nolock)
				inner join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = ttg.MedStaffFact_id
				inner join v_PersonState ps (nolock) on ps.Person_id = :Person_id
			where
				ttg.TimetableGraf_id = :TimetableGraf_id
		", array(
			'TimetableGraf_id' => $data['TimetableGraf_id'],
			'Person_id' => $data['Person_id']
		));

		if (!empty($resp_ttg[0]['Lpu_id'])) {
			$data['Lpu_id'] = $resp_ttg[0]['Lpu_id'];
			$data['DirType_id'] = 16; // на поликлинический приём
			$data['LpuSectionProfile_id'] = null;
			$data['Lpu_did'] = null;
			$data['EvnDirection_id'] = null;
			$data['EvnDirection_Num'] = '0';
			$data['From_MedStaffFact_id'] = -1;
			$data['EvnDirection_pid'] = null;
			$data['Diag_id'] = null;
			$data['EvnDirection_Descr'] = null;
			$data['LpuSection_did'] = null;
			$data['LpuSection_id'] = null;
			$data['MedPersonal_id'] = null;
			$data['MedPersonal_zid'] = null;
			$data['OverrideWarning'] = true;
			$data['Server_id'] = $resp_ttg[0]['Server_id'];
			$data['PersonEvn_id'] = $resp_ttg[0]['PersonEvn_id'];
			$data['EvnDirection_setDT'] = date('Y-m-d');
			$data['ignoreCanRecord'] = 1;

			if (!empty($data['EvnQueue_id'])) {
				$data['redirectEvnDirection'] = 600; // запись из очереди
			}

			$resp = $this->Apply($data);
			if (!empty($resp['Error_Msg'])) {
				throw new Exception($resp['Error_Msg']);
			}

			if (!empty($resp['id'])) {
				return array(
					'Person_id' => $data['Person_id'],
					'TimeTableGraf_id' => $resp['id']
				);
			}
		}

		return false;
	}

	/**
	 * Получение записей на прием по МО
	 */
	function getTimeTableGrafbyMO($data) {
		$resp = $this->queryResult("
			select
				ttg.TimetableGraf_id as TimeTableGraf_id,
				ttg.Person_id
			from
				v_TimeTableGraf_lite ttg (nolock)
				inner join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = ttg.MedStaffFact_id
			where
				msf.Lpu_id = :Lpu_id
				and cast(ttg.TimetableGraf_begTime as date) >= :TimeTableGraf_beg
				and cast(ttg.TimetableGraf_begTime as date) <= :TimeTableGraf_end
				and ttg.Person_id is not null
		", array(
			'Lpu_id' => $data['Lpu_id'],
			'TimeTableGraf_beg' => $data['TimeTableGraf_beg'],
			'TimeTableGraf_end' => $data['TimeTableGraf_end']
		));
		return $resp;
	}

	/**
	 * Получение атрибутов бирки по идентификатору
	 */
	public function getTimeTableGrafById($data) {
		$resp = $this->queryResult("
			select top 1
				ttg.MedStaffFact_id,
				convert(varchar(19), ttg.TimetableGraf_begTime, 120) as TimeTableGraf_begTime,
				ttg.TimetableGraf_Time,
				ttg.TimeTableType_id,
				yn.YesNo_Code as TimeTableGraf_IsDop
			from
				v_TimeTableGraf_lite ttg (nolock)
				left join YesNo yn (nolock) on yn.YesNo_id = ISNULL(ttg.TimeTableGraf_IsDop, 1)
			where
				ttg.TimeTableGraf_id = :TimeTableGraf_id
		", array(
			'TimeTableGraf_id' => $data['TimeTableGraf_id'],
		));
		return $resp;
	}

	/**
	 * Получение номера кабинета по врачу и дате (если указана)
	 */
	function getDoctorRoom($data) {

		if (empty($data['MedStaffFact_id'])) return false;

		$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
		$datetime = 'dbo.tzGetDate()';

		if (!empty($data['datetime'])) {
			$datetime = ':recordDate';
			$params['recordDate'] = $data['datetime'];
		}

		$query = "
			-- установим признак что первый день недели Понедельник
			SET DATEFIRST 1;

			declare @currentDate datetime = {$datetime};

			-- возьмем текущий номер дня недели
			declare @currentCalendarWeek int = (select DATEPART(dw, @currentDate));
			declare @currentTime varchar(5) = (select CONVERT(varchar,  @currentDate, 108))

			select top 1
				lbo.LpuBuildingOffice_Number
			from v_LpuBuildingOfficeMedStaffLink lboml (nolock)
			left join v_LpuBuildingOffice lbo (nolock) on lbo.LpuBuildingOffice_id = lboml.LpuBuildingOffice_id
			-- вариант когда указано время на дне
			outer apply(
				select top 1
					lbovtoa.LpuBuildingOfficeMedStaffLink_id
				from v_LpuBuildingOfficeVizitTime lbovtoa (nolock)
				where
					(1=1)
					and lbovtoa.LpuBuildingOfficeMedStaffLink_id in(
						select
							LpuBuildingOfficeMedStaffLink_id
						from v_LpuBuildingOfficeMedStaffLink lboml2 (nolock)
						where
							lboml2.LpuBuildingOfficeMedStaffLink_begDate <= @currentDate
							and isnull(lboml2.LpuBuildingOfficeMedStaffLink_endDate, '2030-01-01') >= @currentDate
							and lboml2.MedStaffFact_id = :MedStaffFact_id
					)
					and lbovtoa.CalendarWeek_id = @currentCalendarWeek
					and CONVERT(varchar(5), lbovtoa.LpuBuildingOfficeVizitTime_begDate, 108) <= @currentTime
					and CONVERT(varchar(5), lbovtoa.LpuBuildingOfficeVizitTime_endDate, 108) >= @currentTime
			) mainRoom
			-- вариант когда на дне время не указано, но связь кабинета и врача есть (первый попавшийся)
			outer apply(
				select top 1
					lbomloa.LpuBuildingOfficeMedStaffLink_id
				from v_LpuBuildingOfficeMedStaffLink lbomloa (nolock)
				where
					(1=1)
					and lbomloa.LpuBuildingOfficeMedStaffLink_begDate <= @currentDate
					and isnull(lbomloa.LpuBuildingOfficeMedStaffLink_endDate, '2030-01-01') >= @currentDate
					and lbomloa.MedStaffFact_id = :MedStaffFact_id
					-- order by LpuBuildingOfficeMedStaffLink_id desc
			) reserveRoom
			where
				(1=1)
				and isnull(mainRoom.LpuBuildingOfficeMedStaffLink_id, reserveRoom.LpuBuildingOfficeMedStaffLink_id) = lboml.LpuBuildingOfficeMedStaffLink_id;
		";

		$resp = $this->queryResult($query, $params);

		if (!empty($resp[0]['LpuBuildingOffice_Number'])) { return $resp[0]['LpuBuildingOffice_Number'];}
		else return false;
	}

	/**
	 * Получение статуса записи
	 */
	function getTimeTableGrafStatus($data) {

		$query = "
			select
				EvnStatus_id,
				ttg.MedStaffFact_id,
				ttg.TimeTableGraf_begTime
			from
				v_TimeTableGraf_lite ttg (nolock)
				inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = ttg.EvnDirection_id
			where
				ttg.Person_id = :Person_id
				and ttg.TimetableGraf_id = :TimeTableGraf_id
		";

		$resp = $this->queryResult($query, array(
			'Person_id' => $data['Person_id'],
			'TimeTableGraf_id' => $data['TimeTableGraf_id']
		));

		if (!empty($resp[0]) && !empty($resp[0]['EvnStatus_id'])) {

			$responseArray = array ('EvnStatus_id' => $resp[0]['EvnStatus_id']);

			if (!empty($data['extended']) && !empty($resp[0]['MedStaffFact_id'])) {
				// вынес получение номера кабинета в отдельный метод, т.к. метод будем исползовать еще кое где
				$room = $this->getDoctorRoom(array(
					'MedStaffFact_id' => $resp[0]['MedStaffFact_id'],
					'datetime' => (!empty($resp[0]['TimeTableGraf_begTime']) ? $resp[0]['TimeTableGraf_begTime'] : null)
				));

				$responseArray['Room'] = (!empty($room) ? $room : null);
			}

			return $responseArray;
		}

		return false;
	}

	/**
	 * Изменение статуса записи на прием
	 */
	function setTimeTableGrafStatus($data) {
		$resp = $this->queryResult("
			select
				EvnDirection_id
			from
				v_TimeTableGraf_lite ttg (nolock)
			where
				ttg.Person_id = :Person_id
				and ttg.TimetableGraf_id = :TimeTableGraf_id
		", array(
			'Person_id' => $data['Person_id'],
			'TimeTableGraf_id' => $data['TimeTableGraf_id']
		));

		if (!empty($resp[0]['EvnDirection_id'])) {
			if (in_array($data['EvnStatus_id'], array(12,13))) {
				// Если EvnStatus_id меняется на 12 или 13, то в таблице dbo.TimeTableGraf значения полей RecClass_id, Person_id, EvnDirection_id меняется на NULL
				$resp = $this->Clear(array(
					'object' => 'TimetableGraf',
					'cancelType' => ($data['EvnStatus_id'] == 13)?'decline':'cancel',
					'TimetableGraf_id' => $data['TimeTableGraf_id'],
					'DirFailType_id' => 11, // Ошибочное направление
					'EvnStatusCause_id' => 3, // Ошибочное направление
					'EvnComment_Comment' => '',
					'pmUser_id' => $data['pmUser_id'],
					'session' => $data['session']
				));
				if (!empty($resp['Error_Msg'])) {
					return $resp;
				}
			} else {
				$this->load->model('Evn_model', 'Evn_model');
				$resp = $this->Evn_model->updateEvnStatus(array(
					'Evn_id' => $resp[0]['EvnDirection_id'],
					'EvnStatus_id' => $data['EvnStatus_id'],
					'EvnClass_SysNick' => 'EvnDirection',
					'pmUser_id' => $data['pmUser_id']
				));
				if (!empty($resp['Error_Msg'])) {
					return $resp;
				}
			}

			return array();
		}

		return false;
	}

	/**
	 * Добавление расписания врача
	 */
	function addTimetableGraf($data) {
		$resp_tt = array();

		if (!empty($data['TimeTableGrafCreate'])) {

			$data['TimeTableGrafCreate'] = json_encode($data['TimeTableGrafCreate']);
			$data['TimeTableGrafCreate'] = json_decode($data['TimeTableGrafCreate'], true);

			foreach ($data['TimeTableGrafCreate'] as $one) {
				if (!isset($one['TimeTableGraf_begTime'])) {
					throw new Exception("Не указана дата/время приёма");
				}
				if (!isset($one['TimeTableGraf_Time'])) {
					throw new Exception("Не указана длительность приёма");
				}
				if (!isset($one['TimeTableType_id'])) {
					throw new Exception("Не указан тип бирки");
				}
				if (
					isset($one['TimeTableGraf_IsDop'])
					&& $one['TimeTableGraf_IsDop'] !== 1 && $one['TimeTableGraf_IsDop'] !== 0 
					&& $one['TimeTableGraf_IsDop'] !== '1' && $one['TimeTableGraf_IsDop'] !== '0'
				) {
					throw new Exception("Неверное значение в поле TimeTableGraf_IsDop");
				} else if(!isset($one['TimeTableGraf_IsDop'])){
					throw new Exception("Не указан признак дополнительной бирки");
				}
				$query = "
					declare
						@Res bigint,
						@ErrCode bigint,
						@ErrMsg varchar(4000);
					set @Res = null;
		
					exec p_TimetableGraf_ins
						@TimetableGraf_id = @Res output,
						@MedStaffFact_id = :MedStaffFact_id,
						@TimetableGraf_Day = :TimetableGraf_Day,
						@TimetableGraf_begTime = :TimetableGraf_begTime,
						@TimetableGraf_Time = :TimetableGraf_Time,
						@TimetableType_id = :TimeTableType_id,
						@TimetableGraf_factTime = null,
						@TimetableGraf_IsDop = :TimetableGraf_IsDop,
						@pmUser_id = :pmUser_id
					select @Res as TimetableGraf_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
				";

				$resp = $this->queryResult($query, array(
					'MedStaffFact_id' => $data['MedStaffFact_id'],
					'TimetableGraf_Day' => TimeToDay(strtotime($one['TimeTableGraf_begTime'])),
					'TimetableGraf_begTime' => date('Y-m-d H:i:s', strtotime($one['TimeTableGraf_begTime'])),
					'TimetableGraf_Time' => $one['TimeTableGraf_Time'],
					'TimeTableType_id' => $one['TimeTableType_id'],
					'TimetableGraf_IsDop' => !empty($one['TimeTableGraf_IsDop']) ? $one['TimeTableGraf_IsDop'] : null,
					'pmUser_id' => $data['pmUser_id']
				));

				if (!empty($resp[0]['TimetableGraf_id'])) {
					$resp_tt[] = array(
						'TimeTableGraf_id' => $resp[0]['TimetableGraf_id']
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
	 * Редактирование расписания врача
	 */
	function editTimetableGraf($data) {
		if (!empty($data['TimeTableGrafEdit'])) {
			foreach ($data['TimeTableGrafEdit'] as $one) {
				if (!isset($one['TimeTableGraf_id'])) {
					throw new Exception("Не указан идентификатор бирки");
				}

				if (!empty($one['TimeTableType_id'])) {
					// смена типа бирки
					$tmp = $this->queryResult("
						declare
							@ErrCode bigint,
							@TimetableGraf_id bigint = :TimetableGraf_id,
							@ErrMsg varchar(4000);
		
						exec p_TimetableGraf_setType
							@TimetableGraf_id = @TimetableGraf_id output,
							@TimetableType_id = :TimetableType_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
						select @TimetableGraf_id as TimetableGraf_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
					", array(
						'TimetableGraf_id' => $one['TimeTableGraf_id'],
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

				if (isset($one['TimeTableGraf_IsDop'])) {
					// проставление признака дополнительной бирки
					$this->db->query("
						update
							TimetableGraf with (rowlock)
						set
							TimeTableGraf_IsDop = :TimeTableGraf_IsDop,
							pmUser_updID = :pmUser_id,
							TimeTableGraf_updDT = GETDATE()
						where
							TimetableGraf_id = :TimetableGraf_id
					", array(
						'TimetableGraf_id' => $one['TimeTableGraf_id'],
						'TimeTableGraf_IsDop' => $one['TimeTableGraf_IsDop'],
						'pmUser_id' => $data['pmUser_id']
					));
				}

				if (!empty($one['TimeTableGrafDelStatus'])) {
					// удаление бирки
					$tmp = $this->execCommonSP('p_TimetableGraf_del', array(
						'pmUser_id' => $data['pmUser_id'],
						'TimetableGraf_id' => $one['TimeTableGraf_id']
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

	/**
	 * Получение данных об изменениях по биркам поликлиники
	 * @param array $data
	 * @return array|false
	 */
	function getTimeTableGrafByUpdPeriod($data) {
		$params = array();
		$filters = array();

		$filters[] = "cast(TTGHM.TimeTableGraf_updDT as date) between :TimeTableGraf_updbeg and :TimeTableGraf_updend";
		$params['TimeTableGraf_updbeg'] = $data['TimeTableGraf_updbeg'];
		$params['TimeTableGraf_updend'] = $data['TimeTableGraf_updend'];

		if (!empty($data['Lpu_id'])) {
			$filters[] = "TTGHM.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		$filters_str = implode(" and ", $filters);

		$query = "
			select
				TTGHM.Lpu_id,
				TTGHM.MedStaffFact_id,
				TTGHM.TimeTableGraf_id,
				convert(varchar(19), TTGHM.TimeTableGraf_begTime, 120) as TimeTableGraf_begTime,
				TTGHM.TimeTableType_id,
				TTGHM.Person_id,
				TTGHM.TimeTableGrafAction_id,
				convert(varchar(19), TTGHM.TimeTableGraf_insDT, 120) as TimeTableGraf_insDT,
				convert(varchar(19), TTGHM.TimeTableGraf_updDT, 120) as TimeTableGraf_updDT
			from
				TimeTableGrafHistMIS TTGHM with(nolock)
			where
				{$filters_str}
		";

		return $this->queryResult($query, $params);
	}
	/**
	 * Получение расписания на один день
	 */
	function getTimetableGrafGroup( $data ) {
		$outdata = array();

		if ( !isset( $data['MedStaffFact_id'] ) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Не указан врач, для которого показывать расписание'
			);
		}

		$StartDay = isset( $data['StartDay'] ) ? strtotime( $data['StartDay'] ) : time();

		$outdata['StartDay'] = $StartDay;

		$param['pmUser_id'] = $data['pmUser_id'];
		$param['StartDay'] = TimeToDay( $StartDay );
		$param['StartDayA'] = $param['StartDay'] - 1;
		$param['MedStaffFact_id'] = $data['MedStaffFact_id'];
		$param['Lpu_id'] = $data['Lpu_id'];
		$param['nulltime'] = '00:00:00';
		$param['TimeTableGraf_id'] = $data['TimeTableGraf_id'];


		$param['EndDate'] = date( "Y-m-d", $StartDay);
		if ($data['PanelID'] == 'TTGRecordInGroupPanel') {

			$msflpu = $this->getFirstRowFromQuery("select Lpu_id from v_MedStaffFact with (nolock) where MedStaffFact_id = ?", array($data['MedStaffFact_id']));

			if (empty($_SESSION['setting']) || empty($_SESSION['setting']['server'])) { // Вынес отдельно, чтобы не повторять
				$maxDays = null;

			} elseif (!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] == 'regpol' && $_SESSION['lpu_id'] == $msflpu['Lpu_id']) { // Для регистратора запись в свою МО
				$maxDays = !empty($_SESSION['setting']['server']['pol_record_day_count']) ? $_SESSION['setting']['server']['pol_record_day_count'] : null;

			} elseif (!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] == 'regpol') { // Для регистратора запись в чужую МО
				$maxDays = !empty($_SESSION['setting']['server']['pol_record_day_count_reg']) ? $_SESSION['setting']['server']['pol_record_day_count_reg'] : null;

			} elseif (!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] == 'callcenter') { // Для оператора call-центра
				$maxDays = !empty($_SESSION['setting']['server']['pol_record_day_count_cc']) ? $_SESSION['setting']['server']['pol_record_day_count_cc'] : null;

			} elseif ($_SESSION['lpu_id'] == $msflpu['Lpu_id']) { // Для остальных пользовалелей запись в свою МО
				$maxDays = !empty($_SESSION['setting']['server']['pol_record_day_count_own']) ? $_SESSION['setting']['server']['pol_record_day_count_own'] : null;

			} else { // Для остальных пользовалелей запись в чужую МО
				$maxDays = !empty($_SESSION['setting']['server']['pol_record_day_count_other']) ? $_SESSION['setting']['server']['pol_record_day_count_other'] : null;

			}

			if ( $maxDays ) $maxDays--; // лишний день
			if ( date("H:i") >= getShowNewDayTime() && $maxDays ) $maxDays++;
			$param['EndDate'] = !empty($maxDays) ? date( "Y-m-d", strtotime( "+".$maxDays." days", time()) ) : $param['EndDate'];
		}
		$ttGrafQuery = "SELECT 
				ttg.TimeTableGraf_id,
				ttg.TimeTableGraf_IsMultiRec,
				ttg.TimeTableGraf_PersRecLim,
				ttg.TimeTableType_id
			FROM
				v_TimeTableGraf ttg WITH (NOLOCK)
			WHERE 
				ttg.TimeTableGraf_id = :TimeTableGraf_id";
		$TimeTableGraf = $this->getFirstRowFromQuery($ttGrafQuery, array(
			'TimeTableGraf_id' => $data['TimeTableGraf_id']
		));

		/*echo '<pre>';
		print_r($TimeTableGraf);
		echo '</pre>';*/
		$nTime = $StartDay;

		$outdata['day_comment'] = null;
		$outdata['data'] = array();

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
			left join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = A.MedStaffFact_id
			where A.MedStaffFact_id = :MedStaffFact_id
				and D.Day_id = :StartDayA
				and (A.AnnotationVison_id != 3 or msf.Lpu_id = :Lpu_id)";

		$res = $this->db->query( $sql,
			$param );

		$daydescrdata = $res->result( 'array' );

		/*echo '<pre>';
		print_r($daydescrdata);
		echo '</pre>';*/

		foreach ( $daydescrdata as $day ) {
			$outdata['day_comment'][] = array(
				'Annotation_Comment' => $day['Annotation_Comment'],
				'pmUser_Name' => $day['pmUser_Name'],
				'Annotation_updDT' => isset( $day['Annotation_updDT'] ) ? $day['Annotation_updDT']->format( "d.m.Y H:i" ) : ''
			);
		}

		$joinAccessFilter = '';
		$lpuFilter = getAccessRightsLpuFilter('laf.Lpu_id');
		if (!empty($lpuFilter)) {
			$joinAccessFilter .= " left join v_Lpu laf with(nolock) on laf.Lpu_id = msf.Lpu_id and ($lpuFilter or t.pmUser_updID = :pmUser_id)";
		} else {
			$joinAccessFilter .= " left join v_Lpu laf with(nolock) on laf.Lpu_id = msf.Lpu_id";
		}

		$selectPersonData = "case when laf.Lpu_id is null then null else p.Person_BirthDay end as Person_BirthDay,
			case when laf.Lpu_id is null then '' else p.Person_Phone end as Person_Phone,
			pcs.PersonCardState_Code as PersonCard_Code,
			pcs.PersonCard_id as PersonCard_id,
			case when laf.Lpu_id is null then null else p.PersonInfo_InternetPhone end as Person_InetPhone,
			case when laf.Lpu_id is null then ''
				when a1.Address_id is not null
				then
					a1.Address_Address
				else
					a.Address_Address
			end	as Address_Address,
			case when a1.Address_id is not null
			then
				a1.KLTown_id
			else
				a.KLTown_id
			end as KLTown_id,
			case when laf.Lpu_id is null then ''
				when a1.Address_id is not null
				then
					a1.KLStreet_id
				else
					a.KLStreet_id
			end as KLStreet_id,
			case when a1.Address_id is not null
			then
				a1.Address_House
			else
				a.Address_House
			end as Address_House,".
			//Ufa, gaf #116387, для ГАУЗ РВФД
			(((isSuperadmin() || $param['Lpu_id'] == 81) && $this->getRegionNick() == "ufa")
				? "(select pp.post_name from v_PersonState (nolock) vps , job (nolock) jj, post (nolock) pp where vps.person_id=t.person_id and vps.job_id=jj.job_id and jj.post_id=pp.Post_id) as Job_Name,"
				: "case when laf.Lpu_id is null then '' else j.Job_Name end as Job_Name,").
			"case when laf.Lpu_id is null then '' else lpu.Lpu_Nick end as Lpu_Nick,
					case when laf.Lpu_id is null then '' else rtrim(p.Person_Firname) end as Person_Firname,
					case when laf.Lpu_id is null then '' else rtrim(p.Person_Surname) end as Person_Surname,
					case when laf.Lpu_id is null then '' else rtrim(p.Person_Secname) end as Person_Secname,
					case when laf.Lpu_id is null then '1' else '0' end as Person_Filter,";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = p.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then p.Person_BirthDay else null end as Person_BirthDay,
					case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then p.Person_Phone else null end as Person_Phone,
					case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then pcs.PersonCardState_Code else null end as PersonCard_Code,
					case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then pcs.PersonCard_id else null end as PersonCard_id,
					case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then p.PersonInfo_InternetPhone else null end as Person_InetPhone,
					case when peh.PersonEncrypHIV_Encryp is not null or laf.Lpu_id is null then null
					when a1.Address_id is not null then a1.Address_Address else a.Address_Address
					end as Address_Address,
					case when peh.PersonEncrypHIV_Encryp is not null or laf.Lpu_id is null then null
					when a1.Address_id is not null then a1.KLTown_id else a.KLTown_id
					end as KLTown_id,
					case when peh.PersonEncrypHIV_Encryp is not null or laf.Lpu_id is null then null
					when a1.Address_id is not null then a1.KLStreet_id else a.KLStreet_id
					end as KLStreet_id,
					case when peh.PersonEncrypHIV_Encryp is not null or laf.Lpu_id is null then null
					when a1.Address_id is not null then a1.Address_House else a.Address_House
					end as Address_House,".
				//ГАУЗ РВФД, #116387, gilmiyarov_25092017
				(((isSuperadmin() || $param['Lpu_id'] == 81) && $this->getRegionNick() == "ufa")
					? "(select pp.post_name from v_PersonState (nolock) vps , job (nolock) jj, post (nolock) pp where vps.person_id=t.person_id and vps.job_id=jj.job_id and jj.post_id=pp.Post_id) as Job_Name,"
					: "case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then j.Job_Name else null end as Job_Name,").
				"case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then lpu.Lpu_Nick else null end as Lpu_Nick,
					case when laf.Lpu_id is null then ''
						when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then rtrim(p.Person_Surname)
						else rtrim(peh.PersonEncrypHIV_Encryp)
					end as Person_Surname,
					case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then rtrim(p.Person_Firname) else '' end as Person_Firname,
					case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then rtrim(p.Person_Secname) else '' end as Person_Secname,
					case when laf.Lpu_id is null then '1' else '0' end as Person_Filter,";
		}
		// Для Астрахани выводится информация о полисе
		$selectPolis = "";
		$joinPolis = "";
		if ($this->getRegionNick() == "astra") {
			$selectPolis = "case when pol.PolisType_id = 4 then '' else pol.Polis_Ser end as Polis_Ser,
					case when pol.PolisType_id = 4 then p.Person_EdNum else pol.Polis_Num end as Polis_Num,";
			$joinPolis = "left join v_polis pol (nolock) on pol.polis_id = p.polis_id";
		}

		$sql = "
			select
					t.pmUser_updID,
					t.TimetableGraf_updDT,
					t.TimetableGraf_id,
					trl.Person_id,
					trl.TimetableGrafRecList_id,
					t.TimetableGraf_Day,
					t.TimetableGraf_begTime as TimetableGraf_begTime,
					t.TimetableType_id,
					t.TimetableGraf_IsDop,
					p.PrivilegeType_id,
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
					ed.EvnDirection_Num as Direction_Num,
					ISNULL(cast(et.ElectronicTalon_Num as varchar), cast(ED.EvnDirection_TalonCode as varchar)) as Direction_TalonCode,
					t.EvnDirection_id as EvnDirection_tid,
					convert(varchar(10),ed.EvnDirection_setDate,104) as Direction_Date,
					ed.EvnDirection_id as EvnDirection_id,
					qp.pmUser_Name as QpmUser_Name,
					q.EvnQueue_insDT as EvnQueue_insDT,
					dg.Diag_Code,
					u.Lpu_id as pmUser_Lpu_id,
					t.TimetableGraf_IsModerated,
					msf.Lpu_id,
					t.TimetableExtend_Descr,
					t.TimetableExtend_updDT,
					ud.pmUser_Name as TimetableExtend_pmUser_Name,
					msf.MedStaffFact_id,
					msf.MedPersonal_id,
					msf.LpuUnit_id,
					{$selectPolis}
					case when t.TimetableGraf_factTime is not null then 2 else 1 end as Person_IsPriem
				from v_TimetableGraf t with (nolock)
				LEFT JOIN v_TimeTableGrafRecList trl with (nolock) ON trl.TimeTableGraf_id = t.TimeTableGraf_id
				left outer join v_MedStaffFact_ER msf with (nolock) on msf.MedStaffFact_id = t.MedStaffFact_id
				left outer join v_Person_ER p with (nolock) on trl.Person_id = p.Person_id
				outer apply (
					Select top 1 PersonCardState_Code, PersonCard_id
					from PersonCardState pcs (nolock)
					where pcs.Person_id = p.Person_id and pcs.Lpu_id = msf.Lpu_id
					order by LpuAttachType_id
				) pcs
				outer apply(
					select top 1 Lpu_id
					from v_PersonCardState
					where Person_id = p.Person_id
					order by LpuAttachType_id
				) pcs_l
				--left outer join PersonCardState pcs (nolock) on pcs.Person_id = p.Person_id and LpuAttachType_id = 1
				left outer join Address a with (nolock) on p.UAddress_id = a.Address_id
				left outer join Address a1 with (nolock) on p.PAddress_id = a1.Address_id
				left outer join KLStreet pas with (nolock) on a.KLStreet_id = pas.KLStreet_id
				left outer join KLStreet pas1 with (nolock) on a1.KLStreet_id = pas1.KLStreet_id
				left outer join v_Job_ER j with (nolock) on p.Job_id=j.Job_id
				left outer join v_pmUser u with (nolock) on t.PMUser_UpdID = u.PMUser_id
				left outer join v_pmUser ud with (nolock) on t.TimetableExtend_pmUser_updid = ud.PMUser_id
				left outer join v_Lpu lpu with (nolock) on lpu.Lpu_id = pcs_l.Lpu_id
				left outer join v_EvnDirection d with (nolock) on
					trl.EvnDirection_id=d.EvnDirection_id
					and d.DirFailType_id is null
					and d.Person_id = trl.Person_id
				left join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = trl.EvnDirection_id
				left outer join v_Lpu lpud with (nolock) ON lpud.Lpu_id = ed.Lpu_id
				left join v_EvnQueue q with (nolock) on t.TimetableGraf_id = q.TimetableGraf_id and trl.Person_id = q.Person_id
				left join v_pmUser qp with (nolock) on q.pmUser_updId=qp.pmUser_id
				left join Diag dg with (nolock) on dg.Diag_id=ed.Diag_id
				left join v_ElectronicTalon et (nolock) on et.EvnDirection_id = trl.EvnDirection_id
				{$joinPolis}
				{$joinPersonEncrypHIV}
				{$joinAccessFilter}
				where t.TimeTableGraf_id = :TimeTableGraf_id
					and t.MedStaffFact_Id = :MedStaffFact_id
					and t.TimetableGraf_begTime is not null";

		$res = $this->db->query( $sql,$param );

		//echo getDebugSql($sql, $param);

		$ttgdata = $res->result( 'array' );

		foreach ( $ttgdata as $ttg ) {
			$outdata['data'][] = $ttg;
		}
		$defaultTimeTableGraf = $ttgdata[0];
		$arrAttrNotNull = array(
			'pmUser_updID',
			'TimetableGraf_updDT',
			'TimetableGraf_id',
			'TimetableGraf_Day',
			'TimetableGraf_begTime',
			'TimetableType_id',
			'TimetableGraf_IsDop',
			'Lpu_Nick',
			'Person_Filter',
			'PMUser_UpdID',
			'QpmUser_Name',
			'EvnQueue_insDT',
			'Diag_Code',
			'pmUser_Lpu_id',
			'TimetableGraf_IsModerated',
			'Lpu_id',
			'TimetableExtend_Descr',
			'TimetableExtend_updDT',
			'TimetableExtend_pmUser_Name',
			'MedStaffFact_id',
			'MedPersonal_id',
			'LpuUnit_id',
			'Person_IsPriem'
		);
		foreach($defaultTimeTableGraf as $key=>$attr){
			$defaultTimeTableGraf[$key] = null;
			if(in_array($key,$arrAttrNotNull))
				$defaultTimeTableGraf[$key] = $attr;
		}
		for($i = 0; $i < (intval($TimeTableGraf['TimeTableGraf_PersRecLim'])-count($ttgdata)); $i++)
			$outdata['data'][] = $defaultTimeTableGraf;
		/*
		echo '<pre>';
		print_r($outdata['data'][0]);
		echo '</pre>'; die();*/


		$sql = "select TimetableGraf_id from TimetableLock with(nolock) where TimetableGraf_id is not null";

		$res = $this->db->query( $sql );

		$outdata['reserved'] = array();
		$reserved = $res->result( 'array' );
		foreach ( $reserved as $lock ) {
			$outdata['reserved'][] = $lock['TimetableGraf_id'];
		}

		return $outdata;
	}
}