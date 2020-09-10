<?php

/**
 * TimetableGraf6E_model - модель для работы с расписанием в поликлинике для форм на ExtJS 6
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
class TimetableGraf6E_model extends swModel {
	/**
	 * Получение расписания для поликлиники в АРМе врача
	 */
	function loadPolkaWorkPlaceList( $data, $OnlyPlan = false ) {

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
				p.Person_Phone,
				p.Lpu_id,
				RTrim(pcard.PersonCard_Code) as PersonCard_Code,
				RTrim(l.Lpu_Nick) as Lpu_Nick,
				RTrim(pcard.LpuRegion_Name) as LpuRegion_Name,
				convert(varchar(10), p.Person_BirthDay, 104) as Person_BirthDay,
				dbo.Age2(p.Person_BirthDay, @curDT) as Person_Age,
		";

		if (empty($data['forMobileArm'])) {
			$selectPersonData .= "
				rtrim(p.Person_Surname) as Person_Surname,
				rtrim(p.Person_Firname) as Person_Firname,
				rtrim(p.Person_Secname) as Person_Secname,
				null as PersonEncrypHIV_Encryp,
			";
		}

		if (allowPersonEncrypHIV($data['session'])) {
			$isSearchByEncryp = isSearchByPersonEncrypHIV($data['Person_SurName']);
			$selectPersonData = "case
					when PEH.PersonEncrypHIV_id is not null then isnull(rtrim(PEH.PersonEncrypHIV_Encryp),'')
					else rtrim(rtrim(p.Person_Surname) + ' ' + isnull(rtrim(p.Person_Firname),'') + ' ' + isnull(rtrim(p.Person_Secname),''))
				end as Person_FIO,
				case when PEH.PersonEncrypHIV_id is null then p.Person_Phone else '' end as Person_Phone,
				case when PEH.PersonEncrypHIV_id is null then rtrim(p.Lpu_id) else null end as Lpu_id,
				case when PEH.PersonEncrypHIV_id is null then rtrim(pcard.PersonCard_Code) else '' end as PersonCard_Code,
				case when PEH.PersonEncrypHIV_id is null then rtrim(l.Lpu_Nick) else '' end as Lpu_Nick,
				case when PEH.PersonEncrypHIV_id is null then rtrim(pcard.LpuRegion_Name) else '' end as LpuRegion_Name,
				case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), p.Person_BirthDay, 104) else null end as Person_BirthDay,
				case when PEH.PersonEncrypHIV_id is null then dbo.Age2(p.Person_BirthDay, @curDT) else null end as Person_Age,
			";

			if (empty($data['forMobileArm'])) {
				$selectPersonData .= "
					case when PEH.PersonEncrypHIV_id is null then rtrim(p.Person_Surname) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname,
					case when PEH.PersonEncrypHIV_id is null then rtrim(p.Person_Firname) else '' end as Person_Firname,
					case when PEH.PersonEncrypHIV_id is null then rtrim(p.Person_Secname) else '' end as Person_Secname,
					rtrim(PEH.PersonEncrypHIV_Encryp) as PersonEncrypHIV_Encryp,			
				";
			}
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

		if ( !empty($data['showLiveQueue']) && !empty($data['ElectronicService_id']) ) {
			$params['ElectronicService_id'] = $data['ElectronicService_id'];
		}


		if (empty($data['MedStaffFactFilterType_id'])) {
			$data['MedStaffFactFilterType_id'] = 3; // Все
		}

		// получаем врачей по замещению
		$msfArray = array();
		$resp_msfr = $this->queryResult("
			declare @curDate date = dbo.tzGetDate();
			
			select distinct
				MedStaffFact_id
			from
				v_MedStaffFactReplace with (nolock)
			where
				MedStaffFact_rid = :MedStaffFact_id
				and MedStaffFactReplace_BegDate <= ISNULL(:endDate, @curDate)
				and MedStaffFactReplace_EndDate >= ISNULL(:begDate, @curDate)
		", array(
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

		$select1 = '';
		$select2 = '';
		if (empty($data['forMobileArm'])) {
			$select1 .= '
				, ttg.MedStaffFact_id,
				1 as liveQueueSort,
				ttg.TimetableGraf_Day,
				case when ttg.Person_id is not null then convert(varchar(10), TimetableGraf_updDT, 104) + \' \' + convert(varchar(5), TimetableGraf_updDT, 108) end as TimetableGraf_updDT,
				case when ttg.Person_id is not null then
					case
						when pu.pmUser_id is not null then rtrim(pu.pmUser_Name)
						else \'Запись через интернет\'
					end
				end as pmUser_Name,
				ttg.pmUser_updId,
				ttg.pmUser_insId,
				case when ed.EvnDirection_isAuto != 2 then \'true\' else \'false\' end as IsEvnDirection,
				ed.EvnQueue_id,
				ed.EvnStatus_id,
				MSF.Person_Fin as MSF_Person_Fin,
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
				eviz.EvnVizit_rid as Evn_id,
				case when exists(select top 1 ttg2.TimetableGraf_id from v_TimetableGraf_lite ttg2 (nolock) where ttg2.MedStaffFact_id = ttg.MedStaffFact_id and ttg2.Person_id = ttg.Person_id and ttg2.TimetableGraf_Day > ttg.TimetableGraf_Day - 21 and ttg2.TimetableGraf_Day < ttg.TimetableGraf_Day and ttg.Evn_id is not null) then 2 else 1 end as VizitType
			';
			$select2 .= '
				, ttg.MedStaffFact_id,
				2 as liveQueueSort,
				ttg.TimetableGraf_Day,
				case when ttg.Person_id is not null then convert(varchar(10), TimetableGraf_updDT, 104) + \' \' + convert(varchar(5), TimetableGraf_updDT, 108) end as TimetableGraf_updDT,
				case when ttg.Person_id is not null then
					case
						when pu.pmUser_id is not null then rtrim(pu.pmUser_Name)
						else \'Запись через интернет\'
					end
				end as pmUser_Name,
				ttg.pmUser_updId,
				ttg.pmUser_insId,
				case when ed.EvnDirection_isAuto != 2 then \'true\' else \'false\' end as IsEvnDirection,
				ed.EvnQueue_id,
				ed.EvnStatus_id,
				MSF.Person_Fin as MSF_Person_Fin,
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
				eviz.EvnVizit_rid as Evn_id,
				1 as VizitType
			';
		}

		$sql = "
			SELECT
				ttg.TimetableGraf_id,
				ttg.TimetableType_id,
				ttg.TimeTableGraf_countRec,
				ttg.TimeTableGraf_PersRecLim,
				case
					when ttg.Person_id is not null then IsNull(ttt.TimetableType_SysNick,'busy')
					else IsNull(ttt.TimetableType_SysNick,'free') end as TimetableType_SysNick,
				IsNull(ttt.TimetableType_Name,'') as TimetableType_Name,
				MSF.LpuSection_id,
				ttg.Person_id,
				case
					when TimetableGraf_begTime is not null then convert(varchar,TimetableGraf_begTime,104)
					when TimetableGraf_factTime is not null then convert(varchar,TimetableGraf_factTime,104)
					else convert(varchar,TimetableGraf_insDT,104)
				end as TimetableGraf_Date,
				isnull(convert(varchar(5), TimetableGraf_begTime, 108),'б/з') as TimetableGraf_begTime,
				isnull(eviz.EvnVizit_setTime, convert(varchar(5), ttg.TimetableGraf_factTime, 108)) as TimetableGraf_factTime,
				isnull(eviz.EvnVizit_id, 0) as EvnVizit_id,
				TimetableGraf_begTime as timetableDatetime,
				convert(varchar(10), ed.EvnDirection_setDate, 104) as EvnDirection_setDate,
				ed.EvnDirection_Num
				{$select1}
			FROM
				v_TimetableGraf_lite ttg with (nolock)
				{$join_sql}
				left join v_ElectronicTalon et (nolock) on et.EvnDirection_id = ttg.EvnDirection_id
				left join v_ElectronicTalonStatus ets (nolock) on ets.ElectronicTalonStatus_id = et.ElectronicTalonStatus_id
				left join v_ElectronicTalonRedirect etr (nolock) on (etr.ElectronicTalon_id = et.ElectronicTalon_id and (etr.EvnDirection_uid = et.EvnDirection_uid or etr.EvnDirection_uid is null))
				left join v_ElectronicTreatment etre (nolock) on etre.ElectronicTreatment_id = et.ElectronicTreatment_id
				left join v_pmUser pu with (nolock) on pu.pmUser_id = ttg.pmUser_updId
				left join v_TimetableType ttt with (nolock) on ttt.TimetableType_id = ttg.TimetableType_id
				left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = ttg.MedStaffFact_id
				left join v_EvnVizit eviz with (nolock) on eviz.TimetableGraf_id = ttg.TimetableGraf_id /*eviz.EvnDirection_id = ttg.EvnDirection_id*/
				left join v_EvnDirection_all ed with (nolock) on ed.EvnDirection_id = ttg.EvnDirection_id and ed.DirFailType_id is null and ED.EvnStatus_id not in (12,13)
				left join LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_id
			WHERE
				{$filter}
				{$filterMSF}
				and (ttg.TimetableType_id != 12 or ttg.Person_id is not null)

			" . (!empty($data['showLiveQueue']) && !empty($data['ElectronicService_id']) ? "
			UNION ALL

			SELECT
				ttg.TimetableGraf_id,
				ttg.TimetableType_id,
				ttg.TimeTableGraf_countRec,
				ttg.TimeTableGraf_PersRecLim,
				case
					when ttg.Person_id is not null then IsNull(ttt.TimetableType_SysNick,'busy')
					else IsNull(ttt.TimetableType_SysNick,'free') end as TimetableType_SysNick,
				IsNull(ttt.TimetableType_Name,'') as TimetableType_Name,
				MSF.LpuSection_id,
				ttg.Person_id,
				case
					when TimetableGraf_begTime is not null then convert(varchar,TimetableGraf_begTime,104)
					when TimetableGraf_factTime is not null then convert(varchar,TimetableGraf_factTime,104)
					else convert(varchar,TimetableGraf_insDT,104)
				end as TimetableGraf_Date,
				isnull(convert(varchar(5), TimetableGraf_begTime, 108),'б/з') as TimetableGraf_begTime,
				isnull(eviz.EvnVizit_setTime, convert(varchar(5), ttg.TimetableGraf_factTime, 108)) as TimetableGraf_factTime,
				isnull(eviz.EvnVizit_id, 0) as EvnVizit_id,
				TimetableGraf_begTime as timetableDatetime,
				convert(varchar(10), ed.EvnDirection_setDate, 104) as EvnDirection_setDate,
				ed.EvnDirection_Num
				{$select2}
			FROM
				v_TimetableGraf_lite ttg with (nolock)
				{$join_sql}
				left join v_ElectronicTalon et (nolock) on et.EvnDirection_id = ttg.EvnDirection_id
				left join v_ElectronicTalonStatus ets (nolock) on ets.ElectronicTalonStatus_id = et.ElectronicTalonStatus_id
				left join v_ElectronicTalonRedirect etr (nolock) on (etr.ElectronicTalon_id = et.ElectronicTalon_id and (etr.EvnDirection_uid = et.EvnDirection_uid or etr.EvnDirection_uid is null))
				left join v_ElectronicTreatment etre (nolock) on etre.ElectronicTreatment_id = et.ElectronicTreatment_id
				left join v_pmUser pu with (nolock) on pu.pmUser_id = ttg.pmUser_updId
				left join v_TimetableType ttt with (nolock) on ttt.TimetableType_id = ttg.TimetableType_id
				left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = ttg.MedStaffFact_id
				left join v_EvnVizit eviz with (nolock) on eviz.TimetableGraf_id = ttg.TimetableGraf_id /*eviz.EvnDirection_id = ttg.EvnDirection_id*/
				left join v_EvnDirection_all ed with (nolock) on ed.EvnDirection_id = ttg.EvnDirection_id and ed.DirFailType_id is null and ED.EvnStatus_id not in (12,13)
				left join LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_id
			WHERE
				{$filter}
				and MSF.MedStaffFact_id != :MedStaffFact_id
				and ttg.TimetableType_id = 12
				and ttg.Person_id is not null
				and MSF.MedStaffFact_id in (
					select
						mseq.MedStaffFact_id
					from v_MedServiceElectronicQueue mseq with (nolock)
					inner join v_ElectronicService es with (nolock) on es.ElectronicService_id = mseq.ElectronicService_id
					inner join v_ElectronicQueueInfo eqi with (nolock) on eqi.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
					inner join v_ElectronicTreatmentLink etl with (nolock) on etl.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id

					where etl.ElectronicTreatment_id in (
						select
							etlIn.ElectronicTreatment_id
						from v_ElectronicTreatmentLink etlIn with (nolock)
						inner join v_ElectronicQueueInfo eqiIn with (nolock) on eqiIn.ElectronicQueueInfo_id = etlIn.ElectronicQueueInfo_id
						inner join v_ElectronicService esIn2 with (nolock) on esIn2.ElectronicQueueInfo_id = eqiIn.ElectronicQueueInfo_id
						where esIn2.ElectronicService_id = :ElectronicService_id
					)
				)
			" : "") . "
				
			ORDER BY
				 TimetableGraf_Day
				" . (!empty($data['showLiveQueue']) && !empty($data['ElectronicService_id']) ? ", liveQueueSort" : "") . "
				,TimetableGraf_Date
		";

		//echo getDebugSql($sql, $params); exit;
		$res = $this->db->query( $sql,
			$params );

		$FER_PERSON_ID = $this->config->item('FER_PERSON_ID');

		if ( is_object( $res ) ) {

			$resp = $res->result( 'array' );

			$this->load->model('Registry_model');
			foreach($resp as &$response) {
				if ($response['EvnVizit_id'] != 0) {
					$response['Registry_id'] = $this->Registry_model->getRegistryIdForEvnVizit(array(
						'Evn_id' => $response['EvnVizit_id']
					));
				} else {
					$response['Registry_id'] = null;
				}
			}

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

				//карты ДВН и ПОВН в текущем году
				$joinPEH .= "
					outer apply (
						select top 1
							MSR.MedStaffRegion_id
						from
							v_MedStaffRegion MSR with(nolock)
						where
							MSR.LpuRegion_id = pcard.LpuRegion_id
							and MSR.MedStaffFact_id = :MedStaffFact_id
							and (MSR.MedStaffRegion_begDate is null or MSR.MedStaffRegion_begDate <= @curDT)
							and (MSR.MedStaffRegion_endDate is null or MSR.MedStaffRegion_endDate > @curDT)
					) MSR
					outer apply (
						select top 1
							EvnPLDispDop13_id, 
							EvnPLDispDop13_IsEndStage, 
							EvnPLDispDop13_setDate,
							EPDD13.DispClass_id,
							convert(varchar(10), EvnPLDispDop13_setDate, 104) as EvnPLDispDop13_Date
						from
							v_EvnPLDispDop13 EPDD13 (nolock)
						where
							YEAR(EPDD13.EvnPLDispDop13_consDT) = @Year
							and EPDD13.Person_id = p.Person_id
							and EPDD13.DispClass_id = 1
					) EPLDD13
					outer apply (
						select top 1
							EvnPLDispDop13_id,
							EvnPLDispDop13_IsEndStage, 
							EvnPLDispDop13_setDate,
							EPDD13sec.DispClass_id,
							convert(varchar(10), EvnPLDispDop13_setDate, 104) as EvnPLDispDop13_Date
						from
							v_EvnPLDispDop13 EPDD13sec (nolock)
						where
							EvnPLDispDop13_fid = EPLDD13.EvnPLDispDop13_id
							and DispClass_id = 2
					) EPLDD13sec
					outer apply (
						select top 1
							EvnPLDispProf_id
						from
							v_EvnPLDispProf (nolock)
						where
							YEAR(EvnPLDispProf_consDT) = @Year
							and Person_id = p.Person_id
					) EPLDP
					outer apply (
						select top 1
							convert(varchar(10), DispRefuse_setDT, 104) as DispRefuse_Date,
							DispRefuse_setDT as DispRefuse_DT,
							L.Lpu_Nick,
							MSF.Person_Fin,
							DR.DispClass_id
						from
							v_DispRefuse DR (nolock)
							left join v_MedStaffFact MSF (nolock) on MSF.MedStaffFact_id = DR.MedStaffFact_id
							left join v_Lpu L (nolock) on L.Lpu_id = MSF.Lpu_id
						where
							DR.Person_id = p.Person_id and DR.DispClass_id in (1,2)
							and YEAR(DR.DispRefuse_setDT) = @Year
						order by DispRefuse_setDT DESC
					) DR
				";
			
				$selectPersonData.="
					dbo.Age2(p.Person_BirthDay, @YearLastDay) as Person_AgeEndYear, -- возраст пациента на конец текущего года
					EPLDD13.EvnPLDispDop13_id,
					EPLDD13.EvnPLDispDop13_IsEndStage,
					EPLDP.EvnPLDispProf_id,
					MSR.MedStaffRegion_id,
					EPLDD13.EvnPLDispDop13_Date,
					DR.DispRefuse_Date,
					DR.Lpu_Nick as DispRefuse_Lpu,
					DR.Person_Fin as DispRefuse_MedPersonalFio,
				";
				if (getRegionNick() == 'ufa') {
					$joinPEH.="outer apply (
						select top 1
							PersonPrivilegeWOW_id
						from
							v_PersonPrivilegeWOW (nolock)
						where
							Person_id = p.Person_id
					) PPW";
					$selectPersonData.="
						PPW.PersonPrivilegeWOW_id,
						";
				}
			

				$query = "
					declare @curDT datetime = dbo.tzGetDate(),
						@Year bigint = YEAR(dbo.tzGetDate()),
						@YearLastDay datetime = cast(YEAR(dbo.tzGetDate()) as varchar) + '-12-31';
					
					select
						p.Person_id as Person_id,
						p.PersonEvn_id as PersonEvn_id,
						p.Server_id as Server_id,
						{$selectPersonData}
						{$isBDZ}
						pers.Person_IsUnknown,
						case when p.Person_IsFedLgot = 1 or p.Person_IsRegLgot = 1 then 'true' else 'false' end as Person_IsLgot,
						CASE WHEN p.Person_IsFedLgot = 1 THEN 'true' ELSE 'false' END as Person_IsFedLgot,
						CASE WHEN p.Person_IsRegLgot = 1 THEN 'true' ELSE 'false' END as Person_IsRegLgot,
						CASE WHEN pr.PersonRefuse_IsRefuse = 2 THEN 'true' ELSE 'false' END as Person_IsRefuse,
						convert(varchar(10), PQ.PersonQuarantine_begDT, 104) as PersonQuarantine_begDT,
						CASE WHEN PQ.PersonQuarantine_id is not null THEN 'true' ELSE 'false' END as PersonQuarantine_IsOn
					from
						v_PersonState_all p with (nolock)
						left join v_Polis pls with (nolock) on pls.Polis_id = p.Polis_id
						outer apply (
							select top 1 
								PQ.PersonQuarantine_id,
								PQ.PersonQuarantine_begDT
							from v_PersonQuarantine PQ with(nolock)
							where PQ.Person_id = p.Person_id 
							and PQ.PersonQuarantine_endDT is null
						) PQ
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
						left join v_LpuRegion LpuRegion with (nolock) on LpuRegion.LpuRegion_id = pcard.LpuRegion_id
						left outer join v_Lpu l with (nolock) on l.Lpu_id = pcard.Lpu_id
						left join v_PersonRefuse pr with (nolock) ON pr.Person_id = p.Person_id and pr.PersonRefuse_IsRefuse = 2 and pr.PersonRefuse_Year = YEAR(@curDT)
						" . $joinPEH. "
					where
						p.Person_id in ('" . implode("','", $arrayFromPersonState) . "') -- возможно надо поделить на несколько запросов
				";
				$result_ps = $this->db->query($query, array(
					'LpuAttachType_id' => $params['LpuAttachType_id'],
					'MedStaffFact_id' => $params['MedStaffFact_id']
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
					$respone['Person_FIO'] = $one_ps['Person_FIO'];
					$respone['Person_BirthDay'] = $one_ps['Person_BirthDay'];
					$respone['Person_Age'] = $one_ps['Person_Age'];
					$respone['Person_Phone'] = $one_ps['Person_Phone'];
					$respone['PersonCard_Code'] = $one_ps['PersonCard_Code'];
					$respone['Lpu_id'] = $one_ps['Lpu_id'];
					$respone['Lpu_Nick'] = $one_ps['Lpu_Nick'];
					$respone['LpuRegion_Name'] = $one_ps['LpuRegion_Name'];
					$respone['Person_IsBDZ'] = $one_ps['Person_IsBDZ'];
					$respone['Person_IsFedLgot'] = $one_ps['Person_IsFedLgot'];
					$respone['Person_IsRegLgot'] = $one_ps['Person_IsRegLgot'];
					$respone['Person_IsRefuse'] = $one_ps['Person_IsRefuse'];
					$respone['PersonQuarantine_IsOn'] = $one_ps['PersonQuarantine_IsOn'];
					$respone['PersonQuarantine_begDT'] = $one_ps['PersonQuarantine_begDT'];


					if(getRegionNick()=='krym') {
						$respone['Person_AgeEndYear'] = $one_ps['Person_AgeEndYear'];
						$respone['EvnPLDispDop13_id'] = $one_ps['EvnPLDispDop13_id'];
						$respone['EvnPLDispDop13_IsEndStage'] = $one_ps['EvnPLDispDop13_IsEndStage'];
						$respone['EvnPLDispProf_id'] = $one_ps['EvnPLDispProf_id'];
						$respone['MedStaffRegion_id'] = $one_ps['MedStaffRegion_id'];
						$respone['EvnPLDispDop13_Date'] = $one_ps['EvnPLDispDop13_Date'];
						$respone['DispRefuse_Date'] = $one_ps['DispRefuse_Date'];
						$respone['DispRefuse_Lpu'] = $one_ps['DispRefuse_Lpu'];
						$respone['DispRefuse_MedPersonalFio'] = $one_ps['DispRefuse_MedPersonalFio'];
					}
					if (empty($data['forMobileArm'])) {
						$respone['Person_Surname'] = $one_ps['Person_Surname'];
						$respone['Person_Firname'] = $one_ps['Person_Firname'];
						$respone['Person_Secname'] = $one_ps['Person_Secname'];
						$respone['PersonEncrypHIV_Encryp'] = $one_ps['PersonEncrypHIV_Encryp'];
					}
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
					$respone['Person_FIO'] = $one_fer['Person_FIO'];
					$respone['Person_BirthDay'] = '';
					$respone['Person_Age'] = '';
					$respone['Person_Phone'] = '';
					$respone['PersonCard_Code'] = '';
					$respone['Lpu_Nick'] = '';
					$respone['LpuRegion_Name'] = '';
					$respone['IsEvnDirection'] = 'false';
					$respone['Person_IsBDZ'] = 'false';
					$respone['Person_IsFedLgot'] = 'false';
					$respone['Person_IsRegLgot'] = 'false';
					$respone['Person_IsRefuse'] = 'false';
					$respone['PersonQuarantine_IsOn'] = 'false';
				}
			}

			return $resp;
		} else {
			return false;
		}
	}
	/**
	 * Получение списка записавшихся пациентов на бирку
	 */
	function loadTimeTableGrafRecList( $data ) {

		$filter = "(1 = 1)";
		$params = array();
		$filter .= " and t.TimetableGraf_id = :TimetableGraf_id";
		$filter .= " AND trl.TimeTableGrafRecList_id IS NOT null";
		$params['TimetableGraf_id'] = $data['TimetableGraf_id'];


		$sql = "
			declare @curDT datetime = dbo.tzGetDate();
			SELECT 
					t.TimeTableGraf_id,
					trl.Person_id,
					trl.TimetableGrafRecList_id,
					trl.TimeTableGrafRecList_insDT,
					trl.TimeTableGrafRecList_IsGroupFact,
					t.TimeTableType_id,
					RTRIM(RTRIM(p.Person_SurName) + ' ' + ISNULL(RTRIM(p.Person_FirName), '') + ' '
						 + ISNULL(RTRIM(p.Person_SecName), '')
						) AS Person_FIO,
					CONVERT(VARCHAR(10), p.Person_BirthDay, 104) AS Person_BirthDay,
					dbo.Age2(p.Person_BirthDay, @curDT) AS Person_Age
			FROM v_TimeTableGraf t WITH (NOLOCK)
				LEFT JOIN v_TimeTableGrafRecList trl WITH (NOLOCK)
					ON trl.TimeTableGraf_id = t.TimeTableGraf_id
				LEFT JOIN v_PersonState_all p WITH (NOLOCK)
					ON p.Person_id = trl.Person_id
			WHERE
			{$filter}
			order by 
				trl.TimeTableGrafRecList_IsGroupFact, 
				Person_FIO 
		";

		//echo getDebugSql($sql, $params); exit;
		$res = $this->db->query( $sql,$params );


		if ( is_object( $res ) ) {
			$resp = $res->result( 'array' );
			return $resp;
		} else {
			return false;
		}
	}
	/**
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array|boolean
	 */
	function saveCheckedPerson($data)
	{
		if(!empty($data['TimetableGrafRecList']))
			$TimetableGrafRecList = json_decode($data['TimetableGrafRecList'], true);
		else
			return array(array('success' => false, 'Error_Msg' => 'Отсутствуют изменяемые параметры'));

		foreach($TimetableGrafRecList as $pers){
			$queryParams = array(
				'TimetableGrafRecList_id' => $pers['TimetableGrafRecList_id'],
				'TimeTableGrafRecList_isGroupFact' => $pers['TimeTableGrafRecList_IsGroupFact']?2:1
			);
			$res = $this->queryResult("
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec p_TimetableGrafRecList_isGroupFact
						@TimetableGrafRecList_id = :TimetableGrafRecList_id,
						@TimeTableGrafRecList_isGroupFact = :TimeTableGrafRecList_isGroupFact,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				", $queryParams);
			if (!empty($res[0]['Error_Msg'])) {
				return array(array('success' => false, 'Error_Msg' => $res[0]['Error_Msg']));
			}
		}
		return array(array('Error_Msg'=>''));
	}
}