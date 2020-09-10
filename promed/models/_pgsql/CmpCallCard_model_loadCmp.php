<?php

class CmpCallCard_model_loadCmp
{
	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadCmpCallCardAcceptorList(CmpCallCard_model $callObject, $data)
	{
		$query = "
			select
				CmpCallCardAcceptor_id as \"CmpCallCardAcceptor_id\",
				CmpCallCardAcceptor_SysNick as \"CmpCallCardAcceptor_SysNick\",
				CmpCallCardAcceptor_Code as \"CmpCallCardAcceptor_Code\",
				CmpCallCardAcceptor_Name as \"CmpCallCardAcceptor_Name\"
			from v_CmpCallCardAcceptor
		";
		$queryParams = ["pmUser_id" => $data["pmUser_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if(!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadCmpCallCardJournalGrid(CmpCallCard_model $callObject, $data)
	{
		$queryParams = [];
		$where = [];
		$join = "";
		if (isset($data["CmpCallCard_prmDT_From"])) {
			$where[] = "CmpCallCard_prmDT >= :CmpCallCard_prmDT_From";
			$queryParams["CmpCallCard_prmDT_From"] = $data["CmpCallCard_prmDT_From"];
		}
		if (isset($data["CmpCallCard_prmDT_To"])) {
			$where[] = "CmpCallCard_prmDT <= :CmpCallCard_prmDT_To";
			$queryParams["CmpCallCard_prmDT_To"] = $data["CmpCallCard_prmDT_To"] . " 23:59:59.999";
		}
		if (isset($data["CmpCallCard_IsPoli"])) {
			$where[] = "coalesce(CmpCallCard_IsPoli, 1) = :CmpCallCard_IsPoli";
			$queryParams["CmpCallCard_IsPoli"] = $data["CmpCallCard_IsPoli"];
		}
		if (isset($data["CmpLpu_id"])) {
			$where[] = "CmpLpu.Lpu_id = :CmpLpu_id";
			$queryParams["CmpLpu_id"] = $data["CmpLpu_id"];
		}
		if (isset($data["Lpu_aid"]) || isset($data["LpuRegion_id"]) || isset($data["MedPersonal_id"])) {
			$attach_where = "";
			if (isset($data["Lpu_aid"]) && $data["Lpu_aid"] > 0) {
				$attach_where .= " and pc.Lpu_id = :Lpu_aid ";
				$queryParams["Lpu_aid"] = $data["Lpu_aid"];
			}
			if (isset($data["LpuRegion_id"])) {
				$attach_where .= " and pc.LpuRegion_id = :LpuRegion_id ";
				$queryParams["LpuRegion_id"] = $data["LpuRegion_id"];
			}
			$msfreg_join = "";
			if (isset($data["MedPersonal_id"])) {
				$msfreg_join = " inner join v_MedStaffRegion msr on msr.MedPersonal_id = :MedPersonal_id and msr.LpuRegion_id = pc.LpuRegion_id ";
				$queryParams["MedPersonal_id"] = $data["MedPersonal_id"];
			}
			$table = "v_PersonCard";
			if (isset($data["LpuAttachType_id"]) && $data["LpuAttachType_id"] == 2) {
				$table = "v_PersonCard_all";
				$attach_where .= " and pc.PersonCard_begDate < ccc.CmpCallCard_prmDT and (pc.PersonCard_endDate > ccc.CmpCallCard_prmDT or pc.PersonCard_endDate is null) ";
			}
			$where[] = "
				exists (
					select 1
					from
						{$table} pc
						{$msfreg_join}
					where pc.Person_id = ps.Person_id
					{$attach_where}
				)
			";
		}
		$curMedpersonal_id = isset($data["session"]["medpersonal_id"]) ? $data["session"]["medpersonal_id"] : 0;
		if ($curMedpersonal_id > 0) {
			$lastVizitSql = "
				left join lateral (
					select
						EvnVizitPL_setDate,
						Diag_id
					from v_EvnVizitPL vpl1
					where vpl1.Person_id = ps.Person_id
					  and MedPersonal_id = '{$curMedpersonal_id}'
					  and EvnVizitPL_setDate <= CmpCallCard_prmDT
					order by EvnVizitPL_setDate desc
					limit 1
				) as vpl on true
			";
		} else {
			$lastVizitSql = "
				left join lateral (
					select
						null as EvnVizitPL_setDate,
						null as Diag_id
				) as vpl on true
			";
		}
		$selectString = "
			ccc.CmpCallCard_id as \"CmpCallCard_id\",
			ps.Person_id as \"Person_id\",
			ps.Server_id as \"Server_id\",
			ps.PersonEvn_id as \"PersonEvn_id\",
			ps.Person_SurName as \"Person_SurName\",
			ps.Person_FirName as \"Person_FirName\",
			ps.Person_SecName as \"Person_SecName\",
			to_char(ps.Person_BirthDay, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			to_char(CmpCallCard_prmDT, '{$callObject->dateTimeForm104}')||' '||substring(to_char(CmpCallCard_prmDT, '{$callObject->dateTimeForm108}'), 1, 5) as \"CmpCallCard_prmDT\",
			CmpReason_Name as \"CmpReason_Name\",
			coalesce(Lpu.Lpu_Nick, replace(replace(CmpLpu.CmpLpu_Name, '=', ''), '_+', ' ')) as \"CmpLpu_Name\",
			rtrim(udiag.Diag_Code)||' '||rtrim(udiag.Diag_Name) as \"Diag_UName\",
			rtrim(sdiag.Diag_Code)||' '||rtrim(sdiag.Diag_Name) as \"Diag_SName\",
			case when coalesce(CmpCallCard_IsPoli, 1) = 1 then 'false' else 'true' end as \"CmpCallCard_IsPoli\",
			to_char(CAST(vpl.EvnVizitPL_setDate as date), '{$callObject->dateTimeForm104}') as \"EvnVizitPL_setDate\",
			rtrim(vdiag.Diag_Code)||' '||rtrim(vdiag.Diag_Name) as \"Diag_VName\"
		";
		$fromString = "
			v_CmpCallCard ccc
			inner join v_PersonState ps on ps.Person_id = ccc.Person_id
			{$join}
			left join v_CmpReason cr on cr.CmpReason_id = ccc.CmpReason_id
			left join v_CmpLpu CmpLpu on CmpLpu.CmpLpu_id = ccc.CmpLpu_id
			left join v_Lpu Lpu on Lpu.Lpu_id = CmpLpu.Lpu_id
			left join v_Diag udiag on udiag.Diag_id = ccc.Diag_uid
			left join v_Diag sdiag on sdiag.Diag_id = ccc.Diag_sid
			{$lastVizitSql}
			left join v_Diag vdiag on vdiag.Diag_id = CAST(vpl.Diag_id as bigint)
		";
		$whereString = (count($where) != 0) ? "where 
		-- where
		" . implode(" and ", $where) . "
		-- end where" : "";
		$orderByString = "
			ccc.Person_SurName,
			ccc.Person_FirName
		";
		$sql = "
			select
			--select
			{$selectString}
			--end select
			from
			-- from
			{$fromString}
			-- end from
			{$whereString}
			order by
			-- order by
			{$orderByString}
			-- end order by
		";
		return $callObject->getPagingResponse($sql, $queryParams, $data["start"], $data["limit"], true);
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadCmpCloseCardEditForm(CmpCallCard_model $callObject, $data)
	{
		$query = "
			select
				'' as \"accessType\",
				CCC.CmpCallCard_id as \"CmpCallCard_id\",
				CCC.CmpCallCard_Numv as \"Day_num\",
				CCC.CmpCallCard_Ngod as \"Year_num\",
				CCC.CmpCallCard_NumvPr as \"CmpCloseCard_DayNumPr\",
				CCC.CmpCallCard_NgodPr as \"CmpCloseCard_YearNumPr\",
				rtrim(PMC.PMUser_Name) as \"FeldsherAcceptName\",
				rtrim(PMC.PMUser_Name) as \"Feldsher_id\",
				CCC.CmpCallCard_IsAlco as \"isAlco\",
				CCC.CmpCallType_id as \"CallType_id\",
				CCC.CmpReason_id as \"CallPovod_id\",
				CCC.Sex_id as \"Sex_id\",
				coalesce(CCC.KLSubRgn_id, UAD.KLSubRgn_id) as \"Area_id\",
				coalesce(CCC.KLCity_id, UAD.KLCity_id) as \"City_id\",
				coalesce(CCC.KLTown_id, UAD.KLTown_id) as \"Town_id\",
				coalesce(CCC.KLStreet_id, UAD.KLStreet_id) as \"Street_id\",
				CCC.CmpCallCard_Dom as \"House\",
				CCC.CmpCallCard_Korp as \"Korpus\",
				CCC.CmpCallCard_Room as \"Room\",
				CCC.CmpCallCard_Kvar as \"Office\",
				CCC.CmpCallCard_Podz as \"Entrance\",
				CCC.CmpCallCard_Etaj as \"Level\",
				CCC.CmpCallCard_Kodp as \"CodeEntrance\",
				CCC.CmpCallCard_Telf as \"Phone\",
				CCC.CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
				CCC.CmpCallCard_Ulic as \"CmpCloseCard_Street\",
				CCC.CmpCallCard_UlicSecond as \"CmpCloseCard_UlicSecond\",
				CCC.CmpCallCard_Comm as \"CmpCloseCard_DopInfo\",
				CCC.LpuBuilding_id as \"LpuBuilding_id\",
				CCC.Lpu_hid as \"Lpu_hid\",
				CCC.CmpCallCard_IsNMP as \"CmpCallCard_IsNMP\",
				CCC.CmpCallCard_IsExtra as \"CmpCloseCard_IsExtra\",
				case when PS.Document_Ser is not null then PS.Document_Ser end||' '||case when PS.Document_Num is not null then PS.Document_Num end as \"DocumentNum\",
				PS.Person_Snils as \"Person_Snils\",
				to_char(PS.Person_deadDT, '{$callObject->dateTimeForm120}') as \"Person_deadDT\",
				coalesce( CLC.Person_PolisSer, CCC.Person_PolisSer, PS.Polis_Ser, null) as \"Person_PolisSer\",
				coalesce( CLC.Person_PolisNum, CCC.Person_PolisNum, PS.Polis_Num, null) as \"Person_PolisNum\",
				coalesce( CLC.CmpCloseCard_PolisEdNum, CCC.CmpCallCard_PolisEdNum, PS.Person_EdNum, null) as \"CmpCloseCard_PolisEdNum\",
				CCC.Person_IsUnknown as \"Person_IsUnknown\",
				org1.Org_Name as \"Work\",
				dbfss.SocStatus_SysNick as \"SocStatusNick\",
				dbfss.SocStatus_id as \"SocStatus_id\",
				case
					when CCC.Person_Age > 0 then 219
					when datediff('day', coalesce(CCC.Person_BirthDay, PS.Person_BirthDay), CCC.CmpCallCard_insDT) > 365 then 219
					when datediff('day', coalesce(CCC.Person_BirthDay, PS.Person_BirthDay), CCC.CmpCallCard_insDT) > 31 then 220
					when datediff('day', coalesce(CCC.Person_BirthDay, PS.Person_BirthDay), CCC.CmpCallCard_insDT) > 0 then 221
					else 219
				end as \"AgeType_id2\",
				case
					when CCC.Person_Age > 0 then CCC.Person_Age
					when datediff('day', coalesce(CCC.Person_BirthDay, PS.Person_BirthDay), CCC.CmpCallCard_insDT) > 365 then datediff('year', coalesce(CCC.Person_BirthDay, PS.Person_BirthDay), CCC.CmpCallCard_insDT)
					when datediff('day', coalesce(CCC.Person_BirthDay, PS.Person_BirthDay), CCC.CmpCallCard_insDT) > 31 then datediff('month', coalesce(CCC.Person_BirthDay, PS.Person_BirthDay), CCC.CmpCallCard_insDT)
					when datediff('day', coalesce(CCC.Person_BirthDay, PS.Person_BirthDay), CCC.CmpCallCard_insDT) > 0 then datediff('day', coalesce(CCC.Person_BirthDay, PS.Person_BirthDay), CCC.CmpCallCard_insDT)
					else null
				end as \"Age\",
				CCC.CmpReasonNew_id as \"CallPovodNew_id\",
				to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm104}')||' '||to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm108}') as \"AcceptTime\",
				to_char(CCCStatusData.TransTime, '{$callObject->dateTimeForm104}')||' '||to_char(CCCStatusData.TransTime, '{$callObject->dateTimeForm108}') as \"TransTime\",
				to_char(CCC.CmpCallCard_Vyez, '{$callObject->dateTimeForm104}')||' '||to_char(CCC.CmpCallCard_Vyez, '{$callObject->dateTimeForm108}') as \"GoTime\",
				to_char(CCC.CmpCallCard_Przd, '{$callObject->dateTimeForm104}')||' '||to_char(CCC.CmpCallCard_Przd, '{$callObject->dateTimeForm108}') as \"ArriveTime\",
				to_char(CCC.CmpCallCard_Tgsp, '{$callObject->dateTimeForm104}')||' '||to_char(CCC.CmpCallCard_Tgsp, '{$callObject->dateTimeForm108}') as \"TransportTime\",
				to_char(CCC.CmpCallCard_HospitalizedTime, '{$callObject->dateTimeForm104}')||' '||to_char(CCC.CmpCallCard_HospitalizedTime, '{$callObject->dateTimeForm108}') as \"ToHospitalTime\",
				to_char(CCC.CmpCallCard_Tisp, '{$callObject->dateTimeForm104}')||' '||to_char(CCC.CmpCallCard_Tisp, '{$callObject->dateTimeForm108}') as \"EndTime\",
				to_char(CCC.CmpCallCard_Tisp, '{$callObject->dateTimeForm104}')||' '||to_char(CCC.CmpCallCard_Tisp, '{$callObject->dateTimeForm108}') as \"CmpCloseCard_PassTime\",
				coalesce(PS.Person_Surname, case when RTRIM(CCC.Person_SurName) = 'null' then '' else CCC.Person_SurName end) as \"Fam\",
				coalesce(PS.Person_Firname, case when RTRIM(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end) as \"Name\",
				coalesce(PS.Person_Secname, case when RTRIM(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end) as \"Middle\",
				CCC.Person_id as \"Person_id\",
				CCC.CmpCallCard_Ktov as \"Ktov\",
				CCC.CmpCallerType_id as \"CmpCallerType_id\",
				CCC.Lpu_id as \"Lpu_id\",
				CCC.Lpu_ppdid as \"Lpu_ppdid\",
				coalesce(L.Lpu_Nick, '') as \"CmpLpu_Name\",
				CCC.KLRgn_id as \"KLRgn_id\",
				CCC.KLSubRgn_id as \"KLSubRgn_id\",
				CCC.KLCity_id as \"KLCity_id\",
				CCC.KLTown_id as \"KLTown_id\",
				CCC.KLStreet_id as \"KLStreet_id\",
				case when coalesce(CCC.KLStreet_id, 0) = 0 then
					case when coalesce(CCC.UnformalizedAddressDirectory_id, 0) = 0 then null
					else 'UA.'||CCC.UnformalizedAddressDirectory_id::varchar end
				else 'ST.'||CCC.KLStreet_id::varchar end as \"StreetAndUnformalizedAddressDirectory_id\",
				EMT.EmergencyTeam_Num as \"EmergencyTeamNum\",
				EMT.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
				EMT.EmergencyTeam_HeadShift as \"MedPersonal_id\",
				EMT.EmergencyTeam_Assistant1 as \"MedPersonalAssistant_id\",
				EMT.EmergencyTeam_HeadShift2 as \"EmergencyTeam_HeadShift2_id\",
				PostKind.code as \"EmergencyTeam_HeadShift_Code\",
				EMT.EmergencyTeam_Driver as \"MedPersonalDriver_id\",
				coalesce(EMT.EmergencyTeam_HeadShiftWorkPlace, CLC.MedStaffFact_id, msf.MedStaffFact_id, null) as \"MedStaffFact_id\",
				L.Lpu_Nick as \"StationNum\",
				PMCins.MedPersonal_id as \"FeldsherAccept\",
				PMCinsTrans.MedPersonal_id as \"FeldsherTrans\",
				CLC.CmpCloseCard_id as \"CmpCloseCard_id\",
				CCC.EmergencyTeam_id as \"EmergencyTeam_id\",
				LB.LpuBuilding_IsWithoutBalance as \"LpuBuilding_IsWithoutBalance\",
				CCC.CmpCallCard_isControlCall as \"CmpCallCard_isControlCall\",
				MPh1.Person_Fio as \"EmergencyTeam_HeadShiftFIO\",
				MPh2.Person_Fio as \"EmergencyTeam_HeadShift2FIO\",
				MPd1.Person_Fio  as \"EmergencyTeam_DriverFIO\",
				MPa1.Person_Fio as \"EmergencyTeam_Assistant1FIO\",
				case when hospEvent.EmergencyTeamStatus_Code = 53 then '225' else
					case when hospEvent.EmergencyTeamStatus_Code = 3 then '226' else '' end
				end as \"ComboCheck_ResultUfa_id\",
				case when hospEvent.EmergencyTeamStatus_Code = 53 then CCC.Diag_gid else '' end as \"ComboValue_854\",
				case when hospEvent.EmergencyTeamStatus_Code = 3 then CCC.Diag_gid else '' end as \"ComboValue_243\"
			from
				v_CmpCallCard CCC
				left join v_CmpCloseCard CLC on CCC.CmpCallCard_id = CLC.CmpCallCard_id
				left join v_PersonState PS on PS.Person_id = CCC.Person_id
				left join v_Job as job1 ON PS.Job_id=job1.Job_id
				left join v_Org as org1 ON job1.Org_id=org1.Org_id
				left join SocStatus as dbfss on dbfss.SocStatus_id = PS.SocStatus_id
				left join v_Lpu L on L.Lpu_id = CCC.Lpu_id
				left join v_LpuBuilding LB on LB.LpuBuilding_id = CCC.LpuBuilding_id
				left join v_pmUserCache PMC on PMC.PMUser_id = CCC.pmUser_updID
				left join v_pmUserCache PMCins on PMCins.PMUser_id = CCC.pmUser_insID
				left join v_EmergencyTeam EMT on EMT.EmergencyTeam_id = CCC.EmergencyTeam_id
				left join lateral (
					select
						CCCS.CmpCallCardStatus_insDT as TransTime,
						CCCS.pmUser_insID as FeldsherTransPmUser_id
					from v_CmpCallCardStatus CCCS
					where CCCS.CmpCallCard_id = CCC.CmpCallCard_id
					  and CCCS.CmpCallCardStatusType_id = 2
					order by CCCS.pmUser_insID desc
				    limit 1
				) as CCCStatusData on true
				left join lateral (
					select ETS.EmergencyTeamStatus_Code
					from
						v_EmergencyTeamStatusHistory ETSH
						left join v_EmergencyTeamStatus ETS on ETS.EmergencyTeamStatus_id = ETSH.EmergencyTeamStatus_id
					where ETSH.CmpCallCard_id = CCC.CmpCallCard_id
					  and ETSH.EmergencyTeam_id = CCC.EmergencyTeam_id
					  and ETS.EmergencyTeamStatus_Code in (3,53)
					order by EmergencyTeamStatusHistory_id desc
				    limit 1
				) as hospEvent on true
				left join v_pmUserCache PMCinsTrans on PMCinsTrans.PMUser_id = CCCStatusData.FeldsherTransPmUser_id
				left join v_MedStaffFact msf ON (msf.MedStaffFact_id = EMT.EmergencyTeam_HeadShiftWorkPlace)
				LEFT JOIN v_MedPersonal MPh1 ON( MPh1.MedPersonal_id=EMT.EmergencyTeam_HeadShift )
				LEFT JOIN v_MedPersonal MPh2 ON( MPh2.MedPersonal_id=EMT.EmergencyTeam_HeadShift2 )
				LEFT JOIN v_MedPersonal MPd1 ON( MPd1.MedPersonal_id=EMT.EmergencyTeam_Driver )
				LEFT JOIN v_MedPersonal MPa1 ON( MPa1.MedPersonal_id=EMT.EmergencyTeam_Assistant1 )
				left join v_UnformalizedAddressDirectory UAD on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
				left join v_MedPersonal mp ON( mp.MedPersonal_id=EMT.EmergencyTeam_HeadShift)
				left join persis.v_Post Post on Post.id = MP.Dolgnost_id
				left join persis.v_PostKind PostKind on PostKind.id = Post.PostKind_id
			where CCC.CmpCallCard_id = :CmpCallCard_id
			limit 1
		";
		$queryParams = ['CmpCallCard_id' => $data['CmpCallCard_id']];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function loadCmpCloseCardViewForm(CmpCallCard_model $callObject, $data)
	{
		if ((empty($data["CmpCallCard_id"]) || !$data["CmpCallCard_id"]) && (empty($data["CmpCloseCard_id"]) || !$data["CmpCloseCard_id"])) {
			throw new Exception("Невозможно открыть карту закрытия вызова, т.к. не передан ни один идентификатор");
		}
		$where = [];
		$params = [];
		$Bad_end_Mensis = "";
		if (getRegionNick() == 'ufa') {
			$Bad_end_Mensis = "
				to_char(CClC.Bad_DT, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.Bad_DT, '{$callObject->dateTimeForm108}') as \"Bad_DT\",
				to_char(CClC.Mensis_DT, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.Mensis_DT, '{$callObject->dateTimeForm108}') as \"Mensis_DT\",
			";
		}
		if (!empty($data["CmpCallCard_id"])) {
			$where[] = "CCC.CmpCallCard_id = :CmpCallCard_id";
			$params["CmpCallCard_id"] = $data["CmpCallCard_id"];
		} elseif (!empty($data["CmpCloseCard_id"])) {
			$where[] = "CClC.CmpCloseCard_id = :CmpCloseCard_id";
			$params["CmpCloseCard_id"] = $data["CmpCloseCard_id"];
		}
		$whereString = (count($where) != 0) ? "where " . implode(" and ", $where) : "";
		$query = "
			select
				CClC.CmpCallCard_id as \"CmpCallCard_id\",
				CClC.CmpCloseCard_id as \"CmpCloseCard_id\",
				CClC.LpuSection_id as \"LpuSection_id\",
				CCC.CmpReason_id as \"CmpReason_id\",
				CClC.PayType_id as \"PayType_id\",
				CClC.Year_num as \"Year_num\",
				CClC.Day_num as \"Day_num\",
				CClC.CmpCloseCard_DayNumPr as \"CmpCloseCard_DayNumPr\",
				CClC.CmpCloseCard_YearNumPr as \"CmpCloseCard_YearNumPr\",
				CClC.Sex_id as \"Sex_id\",
				CClC.Area_id as \"Area_id\",
				CClC.City_id as \"City_id\",
				CClC.Town_id as \"Town_id\",
				CClC.Street_id as \"Street_id\",
				CClC.House as \"House\",
				CClC.Office as \"Office\",
				CClC.Entrance as \"Entrance\",
				CClC.Level as \"Level\",
				CClC.CodeEntrance as \"CodeEntrance\",
				CClC.Phone as \"Phone\",
				CClC.DescText as \"DescText\",
				CCC.CmpCallCard_Comm as \"CmpCallCard_Comm\",
				coalesce(PS.Person_SurName, CClC.Fam) as \"Fam\",
				coalesce(PS.Person_FirName, CClC.Name) as \"Name\",
				coalesce(PS.Person_SecName, CClC.Middle) as \"Middle\",
				CClC.Age as \"Age\",
				CCC.Person_id as \"Person_id\",
				case when PS.Person_id is not null and coalesce(PS.Person_IsUnknown, 1) != 2 then 1 else CCC.Person_IsUnknown end as \"Person_IsUnknown\",
				CClC.Ktov as \"Ktov\",
				CClC.CmpCallerType_id as \"CmpCallerType_id\",
				CClC.SocStatus_id as \"SocStatus_id\",
				coalesce(CClC.MedStaffFact_id, msf.MedStaffFact_id, null) as \"MedStaffFact_id\",
				CClC.MedStaffFact_cid as \"MedStaffFact_cid\",
				CClC.MedPersonal_id as \"MedPersonal_id\",
				CCC.Lpu_hid as \"Lpu_hid\",
				CCC.KLRgn_id as \"KLRgn_id\",
				CCC.KLSubRgn_id as \"KLSubRgn_id\",
				CCC.KLCity_id as \"KLCity_id\",
				CCC.KLTown_id as \"KLTown_id\",
				CClC.CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
				CClC.Street_id as \"Street_id\",
				CClC.CmpCloseCard_Street as \"CmpCloseCard_Street\",
				CClC.CmpCloseCard_UlicSecond as \"CmpCloseCard_UlicSecond\",
				CClC.Room as \"Room\",
				CClC.Korpus as \"Korpus\",
				case when coalesce(CClC.Street_id, 0) = 0 then
					case when coalesce(CClC.UnformalizedAddressDirectory_id, 0) = 0 then CClC.CmpCloseCard_Street
					else 'UA.'||CClC.UnformalizedAddressDirectory_id::varchar end
				else 'ST.'||CClC.Street_id::varchar end as \"StreetAndUnformalizedAddressDirectory_id\",
				coalesce(CClC.EmergencyTeamNum, EMT.EmergencyTeam_Num, null) as \"EmergencyTeamNum\",
				coalesce(CClC.EmergencyTeam_id, EMT.EmergencyTeam_id, null) as \"EmergencyTeam_id\",
				coalesce(CClC.EmergencyTeamSpec_id, EMT.EmergencyTeamSpec_id, null) as \"EmergencyTeamSpec_id\",
				CClC.StationNum as \"StationNum\",
				CClC.LpuBuilding_id as \"LpuBuilding_id\",
				LB.LpuBuilding_IsPrint as \"LpuBuilding_IsPrint\",
				CClC.pmUser_insID as \"Feldsher_id\",
				CCLC.FeldsherAccept as \"FeldsherAccept\",
				CClC.FeldsherTrans as \"FeldsherTrans\",
				CClC.CmpCloseCard_IsNMP as \"CmpCloseCard_IsNMP\",
				CClC.CmpCloseCard_IsExtra as \"CmpCloseCard_IsExtra\",
				CClC.CmpCloseCard_IsProfile as \"CmpCloseCard_IsProfile\",
				CClC.CmpCloseCard_IsSignList as \"CmpCloseCard_IsSignList\",
				CClC.CallPovodNew_id as \"CallPovodNew_id\",
				CClC.CmpResult_id as \"CmpResult_id\",
				coalesce(CClC.Person_PolisSer, PS.Polis_Ser, null) as \"Person_PolisSer\",
				coalesce(CClC.Person_PolisNum, PS.Polis_Num, null) as \"Person_PolisNum\",
				coalesce(CClC.CmpCloseCard_PolisEdNum, PS.Person_EdNum, null) as \"CmpCloseCard_PolisEdNum\",
				to_char(PS.Person_deadDT, '{$callObject->dateTimeForm120}') as \"Person_deadDT\",
				coalesce(CClC.Person_Snils, PS.Person_SNILS) as \"Person_Snils\",
				to_char(CClC.AcceptTime, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.AcceptTime, '{$callObject->dateTimeForm108}') as \"AcceptTime\",
				to_char(CClC.TransTime, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.TransTime, '{$callObject->dateTimeForm108}') as \"TransTime\",
				to_char(CClC.GoTime, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.GoTime, '{$callObject->dateTimeForm108}') as \"GoTime\",
				to_char(CClC.ArriveTime, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.ArriveTime, '{$callObject->dateTimeForm108}') as \"ArriveTime\",
				to_char(CClC.TransportTime, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.TransportTime, '{$callObject->dateTimeForm108}') as \"TransportTime\",
				to_char(CClC.CmpCloseCard_TranspEndDT, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.CmpCloseCard_TranspEndDT, '{$callObject->dateTimeForm108}') as \"CmpCloseCard_TranspEndDT\",
				to_char(CClC.ToHospitalTime, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.ToHospitalTime, '{$callObject->dateTimeForm108}') as \"ToHospitalTime\",
				to_char(CClC.EndTime, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.EndTime, '{$callObject->dateTimeForm108}') as \"EndTime\",
				to_char(CClC.BackTime, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.BackTime, '{$callObject->dateTimeForm108}') as \"BackTime\",
				to_char(CClC.CmpCloseCard_PassTime, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.CmpCloseCard_PassTime, '{$callObject->dateTimeForm108}') as \"CmpCloseCard_PassTime\",
				{$Bad_end_Mensis}
				CClC.SummTime as \"SummTime\",
				CClC.Work as \"Work\",
				CClC.DocumentNum as \"DocumentNum\",
				CClC.CallType_id as \"CallType_id\",
				CClC.CallPovod_id as \"CallPovod_id\",
				CClC.Alerg as \"Alerg\",
				CClC.Epid as \"Epid\",
				CClC.isVac as \"isVac\",
				CClC.Zev as \"Zev\",
				CClC.Perk as \"Perk\",
				CClC.isKupir as \"isKupir\",
				case when coalesce(CClC.isAlco, 0) = 0 then null else CClC.isAlco end as \"isAlco\",
				CClC.Complaints as \"Complaints\",
				CClC.Anamnez as \"Anamnez\",
				case when coalesce(CClC.isMenen, 0) = 0 then null else CClC.isMenen end as \"isMenen\",
				case when coalesce(CClC.isAnis, 0) = 0 then null else CClC.isAnis end as \"isAnis\",
				case when coalesce(CClC.isNist, 0) = 0 then null else CClC.isNist end as \"isNist\",
				case when coalesce(CClC.isLight, 0) = 0 then null else CClC.isLight end as \"isLight\",
				case when coalesce(CClC.isAcro, 0) = 0 then null else CClC.isAcro end as \"isAcro\",
				case when coalesce(CClC.isMramor, 0) = 0 then null else CClC.isMramor end as \"isMramor\",
				case when coalesce(CClC.isHale, 0) = 0 then null else CClC.isHale end as \"isHale\",
				case when coalesce(CClC.isPerit, 0) = 0 then null else CClC.isPerit end as \"isPerit\",
				CClC.Urine as \"Urine\",
				CClC.Shit as \"Shit\",
				CClC.OtherSympt as \"OtherSympt\",
				CClC.CmpCloseCard_AddInfo as \"CmpCloseCard_AddInfo\",
				CClC.WorkAD as \"WorkAD\",
				CClC.AD as \"AD\",
				case when coalesce(CClC.Pulse, 0)=0 THEN null else CClC.Pulse end as \"Pulse\",
				case when coalesce(CClC.Chss, 0)=0 THEN null else CClC.Chss end as \"Chss\",
				case when coalesce(CClC.Chd, 0)=0 THEN null else CClC.Chd end as \"Chd\",
				CClC.Temperature as \"Temperature\",
				CClC.Pulsks as \"Pulsks\",
				CClC.Gluck as \"Gluck\",
				CClC.LocalStatus as \"LocalStatus\",
				to_char(CClC.Ekg1Time, '{$callObject->dateTimeForm108}') as \"Ekg1Time\",
				CClC.Ekg1 as \"Ekg1\",
				to_char(CClC.Ekg2Time, '{$callObject->dateTimeForm108}') as \"Ekg2Time\",
				CClC.Ekg2 as \"Ekg2\",
				CClC.Diag_id as \"Diag_id\",
				CClC.Diag_uid as \"Diag_uid\",
				CClC.Diag_sid as \"Diag_sid\",
				CClC.EfAD as \"EfAD\",
				CClC.CmpCloseCard_Epid as \"CmpCloseCard_Epid\",
				CClC.CmpCloseCard_Glaz as \"CmpCloseCard_Glaz\",
				CClC.CmpCloseCard_GlazAfter as \"CmpCloseCard_GlazAfter\",
				CClC.CmpCloseCard_m1 as \"CmpCloseCard_m1\",
				CClC.CmpCloseCard_e1 as \"CmpCloseCard_e1\",
				CClC.CmpCloseCard_v1 as \"CmpCloseCard_v1\",
				CClC.CmpCloseCard_m2 as \"CmpCloseCard_m2\",
				CClC.CmpCloseCard_e2 as \"CmpCloseCard_e2\",
				CClC.CmpCloseCard_v2 as \"CmpCloseCard_v2\",
				CClC.CmpCloseCard_Topic as \"CmpCloseCard_Topic\",
				CCC.CmpCallCard_IsNMP as \"CmpCallCard_IsNMP\",
				case when coalesce(CClC.EfChss, 0) = 0 then null else CClC.EfChss end as \"EfChss\",
				case when coalesce(CClC.EfPulse, 0) = 0 then null else CClC.EfPulse end as \"EfPulse\",
				CClC.EfTemperature as \"EfTemperature\",
				case when coalesce(CClC.EfChd, 0) = 0 then null else CClC.EfChd end as \"EfChd\",
				CClC.EfPulsks as \"EfPulsks\",
				CClC.EfGluck as \"EfGluck\",
				CClC.Kilo as \"Kilo\",
				CClC.CmpCloseCard_UserKilo as \"CmpCloseCard_UserKilo\",
				CClC.CmpCloseCard_UserKiloCommon as \"CmpCloseCard_UserKiloCommon\",
				CClC.Lpu_id as \"Lpu_id\",
				CClC.HelpPlace as \"HelpPlace\",
				CClC.HelpAuto as \"HelpAuto\",
				CClC.CmpCloseCard_ClinicalEff as \"CmpCloseCard_ClinicalEff\",
				CClC.CmpCloseCard_DopInfo as \"CmpCloseCard_DopInfo\",
				CClC.DescText as \"DescText\",
				CClC.isSogl as \"isSogl\",
				CClC.isOtkazMed as \"isOtkazMed\",
				CClC.isOtkazHosp as \"isOtkazHosp\",
				CClC.isOtkazSign as \"isOtkazSign\",
				CClC.OtkazSignWhy as \"OtkazSignWhy\",
				CClC.CmpCloseCard_IsHeartNoise as \"CmpCloseCard_IsHeartNoise\",
				CClC.CmpCloseCard_IsIntestinal as \"CmpCloseCard_IsIntestinal\",
				to_char(CClC.CmpCloseCard_BegTreatDT, '{$callObject->dateTimeForm108}') as \"CmpCloseCard_BegTreatDT\",
				to_char(CClC.CmpCloseCard_EndTreatDT, '{$callObject->dateTimeForm108}') as \"CmpCloseCard_EndTreatDT\",
				to_char(CClC.CmpCloseCard_HelpDT, '{$callObject->dateTimeForm108}') as \"CmpCloseCard_HelpDT\",
				CClC.CmpCloseCard_Sat as \"CmpCloseCard_Sat\",
				CClC.CmpCloseCard_Rhythm as \"CmpCloseCard_Rhythm\",
				CClC.CmpCloseCard_AfterRhythm as \"CmpCloseCard_AfterRhythm\",
				CClC.CmpCloseCard_AfterSat as \"CmpCloseCard_AfterSat\",
				CClC.CmpCloseCard_IsDefecation as \"CmpCloseCard_IsDefecation\",
				CClC.CmpCloseCard_IsDiuresis as \"CmpCloseCard_IsDiuresis\",
				CClC.CmpCloseCard_IsVomit as \"CmpCloseCard_IsVomit\",
				CClC.CmpCloseCard_IsTrauma as \"CmpCloseCard_IsTrauma\",
				CClC.CmpLethalType_id as \"CmpLethalType_id\",
				CClC.CmpCloseCard_MenenAddiction as \"CmpCloseCard_MenenAddiction\",
				CClC.LeaveType_id as \"LeaveType_id\",
				MPh1.Person_Fio as \"EmergencyTeam_HeadShiftFIO\",
				MPh2.Person_Fio as \"EmergencyTeam_HeadShift2FIO\",
				MPd1.Person_Fio  as \"EmergencyTeam_DriverFIO\",
				MPa1.Person_Fio as \"EmergencyTeam_Assistant1FIO\",
				to_char(CClC.CmpCloseCard_LethalDT, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.CmpCloseCard_LethalDT, '{$callObject->dateTimeForm108}') as \"CmpCloseCard_LethalDT\",
				UCA.PMUser_Name as \"pmUser_insName\",
				UCT.PMUser_Name as \"FeldsherTransName\",
				LB.LpuBuilding_IsWithoutBalance as \"LpuBuilding_IsWithoutBalance\",
				coalesce(CCC.CmpCallCard_IsPaid, 1) as \"CmpCallCard_IsPaid\",
				coalesce(CCC.CmpCallCard_IndexRep, 0) as \"CmpCallCard_IndexRep\",
				coalesce(CCC.CmpCallCard_IndexRepInReg, 1) as \"CmpCallCard_IndexRepInReg\",
				coalesce(CCC.CmpCallCard_isControlCall, 1) as \"CmpCallCard_isControlCall\",
				case when CCC.CmpCallCard_IndexRep >= coalesce(CCC.CmpCallCard_IndexRepInReg, 0) then 'true' else 'false' end as \"CmpCallCard_RepFlag\",
				case when datediff('ss',CCC.CmpCallCard_insDT,CClC.CmpCloseCard_insDT) < 10 then 'true' else 'false' end as \"addedFromStreamMode\"
			from
				v_CmpCloseCard CClC
				left join v_CmpCallCard CCC on CCC.CmpCallCard_id = CClC.CmpCallCard_id
				left join v_PersonState PS on PS.Person_id = CCC.Person_id
				left join v_Lpu L on L.Lpu_id = CClC.Lpu_id
				left join v_LpuBuilding LB on LB.LpuBuilding_id = CCC.LpuBuilding_id
				left join v_EmergencyTeam EMT on EMT.EmergencyTeam_id = CCC.EmergencyTeam_id
				left join v_pmUserCache UCA on UCA.PMUser_id = CClC.pmUser_insID
				left join v_pmUserCache UCT on UCT.PMUser_id = to_number(CClC.FeldsherTrans)
				left join v_MedStaffFact msf on (msf.MedStaffFact_id= EMT.EmergencyTeam_HeadShiftWorkPlace)
				LEFT JOIN v_MedPersonal MPh1 ON( MPh1.MedPersonal_id=EMT.EmergencyTeam_HeadShift )
				LEFT JOIN v_MedPersonal MPh2 ON( MPh2.MedPersonal_id=EMT.EmergencyTeam_HeadShift2 )
				LEFT JOIN v_MedPersonal MPd1 ON( MPd1.MedPersonal_id=EMT.EmergencyTeam_Driver )
				LEFT JOIN v_MedPersonal MPa1 ON( MPa1.MedPersonal_id=EMT.EmergencyTeam_Assistant1 )
				left join lateral (
					select
						CCCS.CmpCallCardStatus_insDT as TransTime,
						CCCS.pmUser_insID as FeldsherTransPmUser_id
					from v_CmpCallCardStatus CCCS
					where CCCS.CmpCallCard_id = CClC.CmpCallCard_id
					  and CCCS.CmpCallCardStatusType_id = 2
					order by CCCS.pmUser_insID desc
				    limit 1
				) as CCCStatusData on true
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}
	/**
	 * Возвращает данные карты закрытия вызова
	 *
	 * @params array $data
	 * @return array or false
	 */
	public static function loadCmpCloseCardViewFormForDelDocs(CmpCallCard_model $callObject, $data){
		if ( ( empty( $data[ 'CmpCallCard_id' ] ) || !$data[ 'CmpCallCard_id' ] )
			&& ( empty( $data[ 'CmpCloseCard_id' ] ) || !$data[ 'CmpCloseCard_id' ] )
		) {
			return array( array( 'Error_Msg' => 'Невозможно открыть карту закрытия вызова, т.к. не передан ни один идентификатор' ) );
		}

		$where = array();
		$params = array();

		//поля Bad_DT и Mensis_DT есть только на Уфе
		$Bad_end_Mensis = '';
		if(getRegionNick() == 'ufa'){
			$Bad_end_Mensis = "
				to_char(CClC.Bad_DT, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.Bad_DT, '{$callObject->dateTimeForm104}') as \"Bad_DT\",
				to_char(CClC.Mensis_DT, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.Mensis_DT, '{$callObject->dateTimeForm104}') as \"Mensis_DT\",
			";
		}
		if ( !empty( $data[ 'CmpCallCard_id' ] ) ) {
			$where[] = "CCC.CmpCallCard_id = :CmpCallCard_id";
			$params[ 'CmpCallCard_id' ] = $data[ 'CmpCallCard_id' ];
		} elseif ( !empty( $data[ 'CmpCloseCard_id' ] ) ) {
			$where[] = "CClC.CmpCloseCard_id = :CmpCloseCard_id";
			$params[ 'CmpCloseCard_id' ] = $data[ 'CmpCloseCard_id' ];
		}

		$sql = "
			select
				CClC.CmpCallCard_id as \"CmpCallCard_id\",
				CClC.CmpCloseCard_id as \"CmpCloseCard_id\",
				CClC.LpuSection_id as \"LpuSection_id\",
				CCC.CmpReason_id as \"CmpReason_id\",
				CClC.PayType_id as \"PayType_id\",
				CClC.Year_num as \"Year_num\",
				CClC.Day_num as \"Day_num\",
				CClC.CmpCloseCard_DayNumPr as \"CmpCloseCard_DayNumPr\",
				CClC.CmpCloseCard_YearNumPr as \"CmpCloseCard_YearNumPr\",
				CClC.Sex_id as \"Sex_id\",
				CClC.Area_id as \"Area_id\",
				CClC.City_id as \"City_id\",
				CClC.Town_id as \"Town_id\",
				CClC.Street_id as \"Street_id\",
				CClC.House as \"House\",
				CClC.Office as \"Office\",
				CClC.Entrance as \"Entrance\",
				CClC.Level as \"Level\",
				CClC.CodeEntrance as \"CodeEntrance\",
				CClC.Phone as \"Phone\",
				CClC.DescText as \"DescText\",
				CCC.CmpCallCard_Comm as \"CmpCallCard_Comm\",
				coalesce(PS.Person_SurName,CClC.Fam) as \"Fam\",
				coalesce(PS.Person_FirName,CClC.Name) as \"Name\",
				coalesce(PS.Person_SecName,CClC.Middle) as \"Middle\",
				CClC.Age as \"Age\",
				CCC.Person_id as \"Person_id\",
				case when PS.Person_id is not null and coalesce(PS.Person_IsUnknown,1) != 2 then 1 else CCC.Person_IsUnknown end as \"Person_IsUnknown\",
				CClC.Ktov as \"Ktov\",
				CClC.CmpCallerType_id as \"CmpCallerType_id\",
				CClC.SocStatus_id as \"SocStatus_id\",
				coalesce(CClC.MedStaffFact_id, msf.MedStaffFact_id, null) as \"MedStaffFact_id\",
				CClC.MedStaffFact_cid as \"MedStaffFact_cid\",
				CClC.MedPersonal_id as \"MedPersonal_id\",
				CCC.Lpu_hid as \"Lpu_hid\",
				CCC.KLRgn_id as \"KLRgn_id\",
				CCC.KLSubRgn_id as \"KLSubRgn_id\",
				CCC.KLCity_id as \"KLCity_id\",
				CCC.KLTown_id as \"KLTown_id\",
				CClC.CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
				CClC.Street_id as \"Street_id\",
				CClC.CmpCloseCard_Street as \"CmpCloseCard_Street\",
				CClC.CmpCloseCard_UlicSecond as \"CmpCloseCard_UlicSecond\",
				CClC.Room as \"Room\",
				CClC.Korpus as \"Korpus\",

				case when coalesce(CClC.Street_id,0) = 0 then
					case when coalesce(CClC.UnformalizedAddressDirectory_id,0) = 0 then CClC.CmpCloseCard_Street
					else 'UA.'||cast(CClC.UnformalizedAddressDirectory_id as varchar(20)) end
				else 'ST.'||cast(CClC.Street_id as varchar(20)) end as \"StreetAndUnformalizedAddressDirectory_id\",

				coalesce(CClC.EmergencyTeamNum, EMT.EmergencyTeam_Num, null) as \"EmergencyTeamNum\",
				coalesce(CClC.EmergencyTeam_id, EMT.EmergencyTeam_id, null) as \"EmergencyTeam_id\",
				coalesce(CClC.EmergencyTeamSpec_id, EMT.EmergencyTeamSpec_id, null) as \"EmergencyTeamSpec_id\",

				CClC.StationNum as \"StationNum\",
				CClC.LpuBuilding_id as \"LpuBuilding_id\",
				LB.LpuBuilding_IsPrint as \"LpuBuilding_IsPrint\",
				CClC.pmUser_insID as \"pmUser_insID\",
				CCLC.FeldsherAccept as \"FeldsherAccept\",
				CClC.FeldsherTrans as \"FeldsherTrans\",
				CClC.CmpCloseCard_IsNMP as \"CmpCloseCard_IsNMP\",
				CClC.CmpCloseCard_IsExtra as \"CmpCloseCard_IsExtra\",
				CClC.CmpCloseCard_IsProfile as \"CmpCloseCard_IsProfile\",
				CClC.CmpCloseCard_IsSignList as \"CmpCloseCard_IsSignList\",
				CClC.CallPovodNew_id as \"CallPovodNew_id\",
				CClC.CmpResult_id as \"CmpResult_id\",

				coalesce( CClC.Person_PolisSer, PS.Polis_Ser, null) as \"Person_PolisSer\",
				coalesce( CClC.Person_PolisNum, PS.Polis_Num, null) as \"Person_PolisNum\",
				coalesce( CClC.CmpCloseCard_PolisEdNum, PS.Person_EdNum, null) as \"CmpCloseCard_PolisEdNum\",
				to_char(PS.Person_deadDT,'{$callObject->dateTimeForm120}') as \"Person_deadDT\",
				coalesce(CClC.Person_Snils, PS.Person_SNILS) as \"Person_Snils\",

				to_char(CClC.AcceptTime, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.AcceptTime, '{$callObject->dateTimeForm108}') as \"AcceptTime\",
				to_char(CClC.TransTime, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.TransTime, '{$callObject->dateTimeForm108}') as \"TransTime\",
				to_char(CClC.GoTime, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.GoTime, '{$callObject->dateTimeForm108}') as \"GoTime\",

				to_char(CClC.ArriveTime, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.ArriveTime, '{$callObject->dateTimeForm108}') as \"ArriveTime\",
				to_char(CClC.TransportTime, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.TransportTime, '{$callObject->dateTimeForm108}') as \"TransportTime\",
				to_char(CClC.CmpCloseCard_TranspEndDT, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.CmpCloseCard_TranspEndDT, '{$callObject->dateTimeForm108}') as \"CmpCloseCard_TranspEndDT\",
				to_char(CClC.ToHospitalTime, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.ToHospitalTime, '{$callObject->dateTimeForm108}') as \"ToHospitalTime\",
				to_char(CClC.EndTime, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.EndTime, '{$callObject->dateTimeForm108}') as \"EndTime\",
				to_char(CClC.BackTime, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.BackTime, '{$callObject->dateTimeForm108}') as \"BackTime\",
				to_char(CClC.CmpCloseCard_PassTime, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.CmpCloseCard_PassTime, '{$callObject->dateTimeForm108}') as \"CmpCloseCard_PassTime\",

				".$Bad_end_Mensis."

				CClC.SummTime as \"SummTime\",
				CClC.Work as \"Work\",
				CClC.DocumentNum as \"DocumentNum\",
				CClC.CallType_id as \"CallType_id\",
				CClC.CallPovod_id as \"CallPovod_id\",
				CClC.Alerg as \"Alerg\",
				CClC.Epid as \"Epid\",
				CClC.isVac as \"isVac\",
				CClC.Zev as \"Zev\",
				CClC.Perk as \"Perk\",
				CClC.isKupir as \"isKupir\",
				case when coalesce(CClC.isAlco,0) = 0 then null else CClC.isAlco end as \"isAlco\",
				CClC.Complaints as \"Complaints\",
				CClC.Anamnez as \"Anamnez\",
				case when coalesce(CClC.isMenen,0) = 0 then null else CClC.isMenen end as \"isMenen\",
				case when coalesce(CClC.isAnis,0) = 0 then null else CClC.isAnis end as \"isAnis\",
				case when coalesce(CClC.isNist,0) = 0 then null else CClC.isNist end as \"isNist\",
				case when coalesce(CClC.isLight,0) = 0 then null else CClC.isLight end as \"isLight\",
				case when coalesce(CClC.isAcro,0) = 0 then null else CClC.isAcro end as \"isAcro\",
				case when coalesce(CClC.isMramor,0) = 0 then null else CClC.isMramor end as \"isMramor\",
				case when coalesce(CClC.isHale,0) = 0 then null else CClC.isHale end as \"isHale\",
				case when coalesce(CClC.isPerit,0) = 0 then null else CClC.isPerit end as \"isPerit\",
				CClC.Urine as \"Urine\",
				CClC.Shit as \"Shit\",
				CClC.OtherSympt as \"OtherSympt\",
				CClC.CmpCloseCard_AddInfo as \"CmpCloseCard_AddInfo\",
				CClC.WorkAD as \"WorkAD\",
				CClC.AD as \"AD\",
				case when coalesce(CClC.Pulse,0)=0 then null else CClC.Pulse end as \"Pulse\",
				case when coalesce(CClC.Chss,0)=0 then null else CClC.Chss end as \"Chss\",
				case when coalesce(CClC.Chd,0)=0 then null else CClC.Chd end as \"Chd\",
				CClC.Temperature as \"Temperature\",
				CClC.Pulsks as \"Pulsks\",
				CClC.Gluck as \"Gluck\",
				CClC.LocalStatus as \"LocalStatus\",
				to_char(CClC.Ekg1Time, '{$callObject->dateTimeForm108}') as \"Ekg1Time\",
				CClC.Ekg1 as \"Ekg1\",
				to_char(CClC.Ekg2Time, '{$callObject->dateTimeForm108}') as \"Ekg2Time\",
				CClC.Ekg2 as \"Ekg2\",
				CClC.Diag_id as \"Diag_id\",
				CClC.Diag_uid as \"Diag_uid\",
				CClC.Diag_sid as \"Diag_sid\",
				CClC.EfAD as \"EfAD\",
				CClC.CmpCloseCard_Epid as \"CmpCloseCard_Epid\",
				CClC.CmpCloseCard_Glaz as \"CmpCloseCard_Glaz\",
				CClC.CmpCloseCard_GlazAfter as \"CmpCloseCard_GlazAfter\",
				CClC.CmpCloseCard_m1 as \"CmpCloseCard_m1\",
				CClC.CmpCloseCard_e1 as \"CmpCloseCard_e1\",
				CClC.CmpCloseCard_v1 as \"CmpCloseCard_v1\",
				CClC.CmpCloseCard_m2 as \"CmpCloseCard_m2\",
				CClC.CmpCloseCard_e2 as \"CmpCloseCard_e2\",
				CClC.CmpCloseCard_v2 as \"CmpCloseCard_v2\",
				CClC.CmpCloseCard_Topic as \"CmpCloseCard_Topic\",
				CCC.CmpCallCard_IsNMP as \"CmpCallCard_IsNMP\",
				case when coalesce(CClC.EfChss,0) = 0 then null else CClC.EfChss end as \"EfChss\",
				case when coalesce(CClC.EfPulse,0) = 0 then null else CClC.EfPulse end as \"EfPulse\",
				CClC.EfTemperature as \"EfTemperature\",
				case when coalesce(CClC.EfChd,0) = 0 then null else CClC.EfChd end as \"EfChd\",
				CClC.EfPulsks as \"EfPulsks\",
				CClC.EfGluck as \"EfGluck\",
				CClC.Kilo as \"Kilo\",
				CClC.CmpCloseCard_UserKilo as \"CmpCloseCard_UserKilo\",
				CClC.CmpCloseCard_UserKiloCommon as \"CmpCloseCard_UserKiloCommon\",
				CClC.Lpu_id as \"Lpu_id\",
				CClC.HelpPlace as \"HelpPlace\",
				CClC.HelpAuto as \"HelpAuto\",
				CClC.CmpCloseCard_ClinicalEff as \"CmpCloseCard_ClinicalEff\",
				CClC.CmpCloseCard_DopInfo as \"CmpCloseCard_DopInfo\",
				CClC.DescText as \"DescText\",
				CClC.isSogl as \"isSogl\",
				CClC.isOtkazMed as \"isOtkazMed\",
				CClC.isOtkazHosp as \"isOtkazHosp\",
				CClC.isOtkazSign as \"isOtkazSign\",
				CClC.OtkazSignWhy as \"OtkazSignWhy\",
				CClC.CmpCloseCard_IsHeartNoise as \"CmpCloseCard_IsHeartNoise\",
				CClC.CmpCloseCard_IsIntestinal as \"CmpCloseCard_IsIntestinal\",
				to_char(CClC.CmpCloseCard_BegTreatDT, '{$callObject->dateTimeForm108}') as \"CmpCloseCard_BegTreatDT\",
				to_char(CClC.CmpCloseCard_EndTreatDT, '{$callObject->dateTimeForm108}') as \"CmpCloseCard_EndTreatDT\",
				to_char(CClC.CmpCloseCard_HelpDT, '{$callObject->dateTimeForm108}') as \"CmpCloseCard_HelpDT\",
				CClC.CmpCloseCard_Sat as \"CmpCloseCard_Sat\",
				CClC.CmpCloseCard_Rhythm as \"CmpCloseCard_Rhythm\",
				CClC.CmpCloseCard_AfterRhythm as \"CmpCloseCard_AfterRhythm\",
				CClC.CmpCloseCard_AfterSat as \"CmpCloseCard_AfterSat\",
				CClC.CmpCloseCard_IsDefecation as \"CmpCloseCard_IsDefecation\",
				CClC.CmpCloseCard_IsDiuresis as \"CmpCloseCard_IsDiuresis\",
				CClC.CmpCloseCard_IsVomit as \"CmpCloseCard_IsVomit\",
				CClC.CmpCloseCard_IsTrauma as \"CmpCloseCard_IsTrauma\",
				CClC.CmpLethalType_id as \"CmpLethalType_id\",
				CClC.CmpCloseCard_MenenAddiction as \"CmpCloseCard_MenenAddiction\",
				CClC.LeaveType_id as \"LeaveType_id\",
				MPh1.Person_Fio as \"EmergencyTeam_HeadShiftFIO\",
				MPh2.Person_Fio as \"EmergencyTeam_HeadShift2FIO\",
				MPd1.Person_Fio  as \"EmergencyTeam_DriverFIO\",
				MPa1.Person_Fio as \"EmergencyTeam_Assistant1FIO\",
				to_char(CClC.CmpCloseCard_LethalDT, '{$callObject->dateTimeForm104}')||' '||to_char(CClC.CmpCloseCard_LethalDT, '{$callObject->dateTimeForm108}') as \"CmpCloseCard_LethalDT\",
				UCA.PMUser_Name as \"pmUser_insName\",
				UCT.PMUser_Name as \"FeldsherTransName\",
				LB.LpuBuilding_IsWithoutBalance as \"LpuBuilding_IsWithoutBalance\",
				coalesce(CCC.CmpCallCard_IsPaid, 1) as \"CmpCallCard_IsPaid\",
				coalesce(CCC.CmpCallCard_IndexRep, 0) as \"CmpCallCard_IndexRep\",
				coalesce(CCC.CmpCallCard_IndexRepInReg, 1) as \"CmpCallCard_IndexRepInReg\",
				coalesce(CCC.CmpCallCard_isControlCall, 1) as \"CmpCallCard_isControlCall\",
				case when CCC.CmpCallCard_IndexRep >= coalesce(CCC.CmpCallCard_IndexRepInReg, 0) then 'true' else 'false' end as \"CmpCallCard_RepFlag\",
				case when datediff('ss',CCC.CmpCallCard_insDT,CClC.CmpCloseCard_insDT) < 10 then 'true' else 'false' end as \"addedFromStreamMode\"
			from
				CmpCloseCard CClC
				left join CmpCallCard CCC on CCC.CmpCallCard_id = CClC.CmpCallCard_id
				left join v_PersonState PS on PS.Person_id = CCC.Person_id
				left join v_Lpu L on L.Lpu_id = CClC.Lpu_id
				left join v_LpuBuilding LB on LB.LpuBuilding_id = CCC.LpuBuilding_id
				left join v_EmergencyTeam EMT on EMT.EmergencyTeam_id = CCC.EmergencyTeam_id
				left join v_pmUserCache UCA on UCA.PMUser_id = CClC.pmUser_insID
				left join v_pmUserCache UCT on UCT.PMUser_id = CClC.FeldsherTrans::integer
				left join v_MedStaffFact msf on msf.MedStaffFact_id = EMT.EmergencyTeam_HeadShiftWorkPlace
				left join v_MedPersonal MPh1 on MPh1.MedPersonal_id = EMT.EmergencyTeam_HeadShift
				left join v_MedPersonal MPh2 on MPh2.MedPersonal_id = EMT.EmergencyTeam_HeadShift2
				left join v_MedPersonal MPd1 on MPd1.MedPersonal_id = EMT.EmergencyTeam_Driver
				left join v_MedPersonal MPa1 on MPa1.MedPersonal_id = EMT.EmergencyTeam_Assistant1
				left join lateral (
					select
						CCCS.CmpCallCardStatus_insDT as TransTime,
						CCCS.pmUser_insID as FeldsherTransPmUser_id
					from
						v_CmpCallCardStatus CCCS
					where
						CCCS.CmpCallCard_id = CClC.CmpCallCard_id
						and CCCS.CmpCallCardStatusType_id = 2
					order by
						CCCS.pmUser_insID desc
					limit 1
				) as CCCStatusData on true
			".ImplodeWherePH( $where )."
			limit 1
		";

		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}


	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadCmpCallCardEditForm(CmpCallCard_model $callObject, $data)
	{
		$query = "
			select
				'' as \"accessType\",
				CCC.CmpCallCard_id as \"CmpCallCard_id\",
				cclc.CmpCloseCard_id as \"CmpCloseCard_id\",
				coalesce(CCC.Person_id, 0) as \"Person_id\",
				CCC.CmpArea_gid as \"CmpArea_gid\",
				CCC.CmpArea_id as \"CmpArea_id\",
				CCC.CmpArea_pid as \"CmpArea_pid\",
				CCC.CmpCallCard_IsAlco as \"CmpCallCard_IsAlco\",
				CCC.RankinScale_id as \"RankinScale_id\",
				CCC.CmpCallCard_IsPoli as \"CmpCallCard_IsPoli\",
				CCC.MedService_id as \"MedService_id\",
				CCC.CmpCallType_id as \"CmpCallType_id\",
				CCC.CmpDiag_aid as \"CmpDiag_aid\",
				CCC.CmpDiag_oid as \"CmpDiag_oid\",
				CCC.CmpLpu_aid as \"CmpLpu_aid\",
				CCC.CmpLpu_id as \"CmpLpu_id\",
				CCC.Lpu_hid as \"Lpu_hid\",
				CL.Lpu_id as \"Lpu_oid\",
				CCC.CmpCallCardStatus_Comment as \"CmpCallCardStatus_Comment\",
				CRR.CmpRejectionReason_Name as \"CmpRejectionReason_Name\",
				CCC.CmpPlace_id as \"CmpPlace_id\",
				CCC.CmpProfile_bid as \"CmpProfile_bid\",
				CCC.CmpProfile_cid as \"CmpProfile_cid\",
				CCC.CmpReason_id as \"CmpReason_id\",
				CCC.CmpReasonNew_id as \"CmpReasonNew_id\",
				CCC.CmpResult_id as \"CmpResult_id\",
				CCC.ResultDeseaseType_id as \"ResultDeseaseType_id\",
				CCC.LeaveType_id as \"LeaveType_id\",
				CCC.CmpTalon_id as \"CmpTalon_id\",
				CCC.CmpTrauma_id as \"CmpTrauma_id\",
				CCC.Diag_sid as \"Diag_sid\",
				CCC.Diag_uid as \"Diag_uid\",
				CCC.Diag_sopid as \"Diag_sopid\",
				case when coalesce(CCC.Sex_id, 0) = 0 then (case when coalesce(PS.Sex_id, 0) = 0 then null else PS.Sex_id end) else CCC.Sex_id end as \"Sex_id\",
				PS.Sex_id as \"SexIdent_id\",
				PS.Person_deadDT as \"Person_deadDT\",
				CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
				CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
				CCC.CmpCallCard_NumvPr as \"CmpCallCard_NumvPr\",
				CCC.CmpCallCard_NgodPr as \"CmpCallCard_NgodPr\",
				CCC.CmpCallCard_Prty as \"CmpCallCard_Prty\",
				CCC.CmpCallCard_Sect as \"CmpCallCard_Sect\",
				CCC.CmpCallCard_City as \"CmpCallCard_City\",
				CCC.CmpCallCard_Ulic as \"CmpCallCard_Ulic\",
				CCC.CmpCallCard_Dom as \"CmpCallCard_Dom\",
				CCC.CmpCallCard_Korp as \"CmpCallCard_Korp\",
				CCC.CmpCallCard_Room as \"CmpCallCard_Room\",
				CCC.CmpCallCard_Kvar as \"CmpCallCard_Kvar\",
				CCC.CmpCallCard_Podz as \"CmpCallCard_Podz\",
				CCC.CmpCallCard_Etaj as \"CmpCallCard_Etaj\",
				CCC.CmpCallCard_Kodp as \"CmpCallCard_Kodp\",
				CCC.CmpCallCard_Telf as \"CmpCallCard_Telf\",
				CCC.CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
				CCC.CmpCallCard_Comm as \"CmpCallCard_Comm\",
				CCC.CmpCallCard_IsExtra as \"CmpCallCard_IsExtra\",
				CCC.Person_IsUnknown as \"Person_IsUnknown\",
				rtrim(ltrim(case when coalesce(CCC.Person_SurName, '') = '' then (case when coalesce(PS.Person_Surname, '') = '' then '' else PS.Person_Surname end) else CCC.Person_SurName end)) as \"Person_SurName\",
				rtrim(ltrim(case when coalesce(CCC.Person_FirName, '') = '' then (case when coalesce(PS.Person_Firname, '') = '' then '' else PS.Person_Firname end) else CCC.Person_FirName end)) as \"Person_FirName\",
				rtrim(ltrim(case when coalesce(CCC.Person_SecName, '') = '' then (case when coalesce(PS.Person_Secname, '') = '' then '' else PS.Person_Secname end) else CCC.Person_SecName end)) as \"Person_SecName\",
				rtrim(ltrim(coalesce(PS.Person_Surname, ''))) as \"PersonIdent_Surname\",
				rtrim(ltrim(coalesce(PS.Person_Firname, ''))) as \"PersonIdent_Firname\",
				rtrim(ltrim(coalesce(PS.Person_Secname, ''))) as \"PersonIdent_Secname\",
				to_char(coalesce(CCC.Person_BirthDay, PS.Person_Birthday), '{$callObject->dateTimeForm104}') as \"Person_BirthDay\",
				CCC.Person_Age as \"Person_Age\",
				coalesce(dbo.Age2(PS.Person_Birthday, CCC.CmpCallCard_prmDT), 0) as \"PersonIdent_Age\",
				coalesce(CCC.Person_PolisSer, PS.Polis_Ser, null) as \"Polis_Ser\",
				coalesce(CCC.Person_PolisNum, PS.Polis_Num, null) as \"Polis_Num\",
				coalesce(CCC.CmpCallCard_PolisEdNum, PS.Person_EdNum, null) as \"Polis_EdNum\",
				PS.Polis_Num as \"PolisIdent_Num\",
				CCC.CmpCallCard_Ktov as \"CmpCallCard_Ktov\",
				CCC.CmpCallerType_id as \"CmpCallerType_id\",
				CCC.CmpCallCard_Smpt as \"CmpCallCard_Smpt\",
				CCC.CmpCallCard_Stan as \"CmpCallCard_Stan\",
				to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm104}') as \"CmpCallCard_prmDate\",
				to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm108}') as \"CmpCallCard_prmTime\",
				to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm120}') as \"CmpCallCard_prmDT\",
				CCC.CmpCallCard_Line as \"CmpCallCard_Line\",
				CCC.CmpCallCard_Numb as \"CmpCallCard_Numb\",
				CCC.CmpCallCard_Smpb as \"CmpCallCard_Smpb\",
				CCC.CmpCallCard_Stbr as \"CmpCallCard_Stbr\",
				CCC.CmpCallCard_Stbb as \"CmpCallCard_Stbb\",
				CCC.CmpCallCard_Ncar as \"CmpCallCard_Ncar\",
				CCC.CmpCallCard_RCod as \"CmpCallCard_RCod\",
				CCC.CmpCallCard_TabN as \"CmpCallCard_TabN\",
				CCC.CmpCallCard_Dokt as \"CmpCallCard_Dokt\",
				CCC.MedPersonal_id as \"MedPersonal_id\",
				coalesce(CCC.MedStaffFact_id, msf1.MedStaffFact_id) as \"MedStaffFact_id\",
				coalesce(CCC.CmpCallCard_IsMedPersonalIdent, 1) as \"CmpCallCard_IsMedPersonalIdent\",
				CCC.CmpCallCard_Tab2 as \"CmpCallCard_Tab2\",
				CCC.CmpCallCard_Tab3 as \"CmpCallCard_Tab3\",
				CCC.CmpCallCard_Tab4 as \"CmpCallCard_Tab4\",
				CCC.CmpCallCard_Expo as \"CmpCallCard_Expo\",
				CCC.CmpCallCard_Smpp as \"CmpCallCard_Smpp\",
				CCC.CmpCallCard_Vr51 as \"CmpCallCard_Vr51\",
				CCC.CmpCallCard_D201 as \"CmpCallCard_D201\",
				CCC.CmpCallCard_Dsp1 as \"CmpCallCard_Dsp1\",
				CCC.CmpCallCard_Dsp2 as \"CmpCallCard_Dsp2\",
				CCC.CmpCallCard_Dsp3 as \"CmpCallCard_Dsp3\",
				CCC.CmpCallCard_Dspp as \"CmpCallCard_Dspp\",
				CCC.CmpCallCard_Kakp as \"CmpCallCard_Kakp\",
				to_char(CCC.CmpCallCard_Tper, '{$callObject->dateTimeForm120}') as \"CmpCallCard_Tper\",
				to_char(CCC.CmpCallCard_Vyez, '{$callObject->dateTimeForm120}') as \"CmpCallCard_Vyez\",
				to_char(CCC.CmpCallCard_Przd, '{$callObject->dateTimeForm120}') as \"CmpCallCard_Przd\",
				to_char(CCC.CmpCallCard_Tgsp, '{$callObject->dateTimeForm120}') as \"CmpCallCard_Tgsp\",
				to_char(CCC.CmpCallCard_Tsta, '{$callObject->dateTimeForm120}') as \"CmpCallCard_Tsta\",
				to_char(CCC.CmpCallCard_Tisp, '{$callObject->dateTimeForm120}') as \"CmpCallCard_Tisp\",
				to_char(CCC.CmpCallCard_Tvzv, '{$callObject->dateTimeForm120}') as \"CmpCallCard_Tvzv\",
				CCC.CmpCallCard_Kilo as \"CmpCallCard_Kilo\",
				CCC.CmpCallCard_Dlit as \"CmpCallCard_Dlit\",
				CCC.CmpCallCard_Prdl as \"CmpCallCard_Prdl\",
				CCC.CmpCallCard_PCity as \"CmpCallCard_PCity\",
				CCC.CmpCallCard_PUlic as \"CmpCallCard_PUlic\",
				CCC.CmpCallCard_PDom as \"CmpCallCard_PDom\",
				CCC.CmpCallCard_PKvar as \"CmpCallCard_PKvar\",
				CCC.cmpCallCard_Medc as \"cmpCallCard_Medc\",
				CCC.CmpCallCard_Izv1 as \"CmpCallCard_Izv1\",
				to_char(CCC.CmpCallCard_Tiz1, '{$callObject->dateTimeForm108}') as \"CmpCallCard_Tiz1\",
				CCC.CmpCallCard_Inf1 as \"CmpCallCard_Inf1\",
				CCC.CmpCallCard_Inf2 as \"CmpCallCard_Inf2\",
				CCC.CmpCallCard_Inf3 as \"CmpCallCard_Inf3\",
				CCC.CmpCallCard_Inf4 as \"CmpCallCard_Inf4\",
				CCC.CmpCallCard_Inf5 as \"CmpCallCard_Inf5\",
				CCC.CmpCallCard_Inf6 as \"CmpCallCard_Inf6\",
			    CCC.UslugaComplex_id as \"UslugaComplex_id\",
			    CCC.Lpu_id as \"Lpu_id\",
			    CCC.LpuBuilding_id as \"LpuBuilding_id\",
			    CCC.CmpCallCard_IsNMP as \"CmpCallCard_IsNMP\",
			    CCC.CmpCallCard_IsExtra as \"CmpCallCard_IsExtra\",
			    CCC.Lpu_ppdid as \"Lpu_ppdid\",
			    coalesce(L.Lpu_Nick, '') as \"CmpLpu_Name\",
			    case when coalesce(OC.OftenCallers_id, 0) = 0 then 1 else 2 end as \"Person_isOftenCaller\",
			    CCC.UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\",
			    case when coalesce(CCC.KLStreet_id, 0) = 0 then
					case when coalesce(CCC.UnformalizedAddressDirectory_id, 0) = 0 then null
					else 'UA.'||CCC.UnformalizedAddressDirectory_id::varchar end
				else 'ST.'||CCC.KLStreet_id::varchar end as \"StreetAndUnformalizedAddressDirectory_id\",
			    CCC.CmpCallCard_UlicSecond as \"CmpCallCard_UlicSecond\",
			    case when CArea.KLAreaLevel_id = 1 then CCC.KLCity_id else CCC.KLRgn_id end as \"KLRgn_id\",
			    case when CArea.KLAreaLevel_id = 2 then CCC.KLCity_id else CCC.KLSubRgn_id end as \"KLSubRgn_id\",
			    CCC.KLCity_id as \"KLCity_id\",
			    CCC.KLTown_id as \"KLTown_id\",
			    CCC.KLStreet_id as \"KLStreet_id\",
			    to_char(ccp.CmpCallCardCostPrint_setDT, '{$callObject->dateTimeForm104}') as \"CmpCallCardCostPrint_setDT\",
			    ccp.CmpCallCardCostPrint_IsNoPrint as \"CmpCallCardCostPrint_IsNoPrint\",
			    coalesce(CCC.CmpCallCard_IsPaid, 1) as \"CmpCallCard_IsPaid\",
			    coalesce(CCC.CmpCallCard_IndexRep, 0) as \"CmpCallCard_IndexRep\",
			    coalesce(CCC.CmpCallCard_IndexRepInReg, 1) as \"CmpCallCard_IndexRepInReg\",
			    CCC.LpuSection_id as \"LpuSection_id\",
				coalesce(CCC.CmpCallCard_isShortEditVersion,1) as \"CmpCallCard_isShortEditVersion\",
				coalesce(CCC.CmpCallCard_Condition,'') as \"CmpCallCard_Condition\",
				coalesce(CCC.CmpCallCard_Recomendations,'') as \"CmpCallCard_Recomendations\",
				CCC.Lpu_cid as \"Lpu_cid\",
				CCC.EmergencyTeam_id as \"EmergencyTeam_id\",
				CCC.CmpCallCard_IsPassSSMP as \"CmpCallCard_IsPassSSMP\",
				CCC.Lpu_smpid as \"Lpu_smpid\",
				CCC.CmpLeaveType_id as \"CmpLeaveType_id\",
				CCC.CmpLeaveTask_id as \"CmpLeaveTask_id\",
				CCC.CmpMedicalCareKind_id as \"CmpMedicalCareKind_id\",
				CCC.CmpResultDeseaseType_id as \"CmpResultDeseaseType_id\",
				CCC.CmpCallCardResult_id as \"CmpCallCardResult_id\",
				CCC.CmpMedicalCareKind_id as \"CmpMedicalCareKind_id\",
				CCC.CmpTransportType_id as \"CmpTransportType_id\",
				CCC.PayType_id as \"PayType_id\",
				CCC.CmpCallCard_isControlCall as \"CmpCallCard_isControlCall\",
				cccst.CmpCallCardStatusType_Code as \"CmpCallCardStatusType_Code\"
			from
				CmpCallCard CCC
				left join v_CmpCloseCard cclc on CCC.CmpCallCard_id = cclc.CmpCallCard_id
				left join v_CmpCallCardCostPrint ccp on ccp.CmpCallCard_id = CCC.CmpCallCard_id
				left join CmpLpu CL on CL.CmpLpu_id = CCC.CmpLpu_id
				left join v_KLArea CArea on CCC.KLCity_id = CArea.KLArea_id
				left join v_CmpCallCardStatusType cccst on cccst.CmpCallCardStatusType_id = CCC.CmpCallCardStatusType_id
				left join lateral (
					select
						*
					from v_CmpCallCardStatus CCCS
					where
						CCCS.CmpCallCard_id = CCC.CmpCallCard_id
					order by CCCS.CmpCallCardStatus_updDT asc
					limit 1
				) as lastStatus on true
				left join v_CmpRejectionReason CRR on CRR.CmpRejectionReason_id = lastStatus.CmpReason_id
				left join lateral (
					select
						pa.Person_id,
					    coalesce(pa.Person_SurName, '') as Person_Surname,
					    coalesce(pa.Person_FirName, '') as Person_Firname,
					    coalesce(pa.Person_SecName, '') as Person_Secname,
					    pa.Person_BirthDay as Person_Birthday,
					    to_char(pa.Person_deadDT, '{$callObject->dateTimeForm120}') as Person_deadDT,
					    coalesce(pa.Sex_id, 0) as Sex_id,
					    pa.Person_EdNum,
					    coalesce(p.Polis_Ser, '') as Polis_Ser,
					    coalesce(p.Polis_Num, '') as Polis_Num
					from
						v_Person_all pa
						left join v_Polis p on p.Polis_id = pa.Polis_id
					where Person_id = CCC.Person_id
					  and PersonEvn_insDT <= CCC.CmpCallCard_prmDT
					order by PersonEvn_insDT desc
					limit 1
				) as PS on true
				left join lateral (
					select MedStaffFact_id
					from v_MedStaffFact
					where MedPersonal_id = CCC.MedPersonal_id
					  and Lpu_id = CCC.Lpu_id
					  and (WorkData_begDate is null or WorkData_begDate <= CCC.CmpCallCard_prmDT)
					  and (WorkData_endDate is null or WorkData_endDate > CCC.CmpCallCard_prmDT)
					order by PostOccupationType_id
				    limit 1
				) as msf1 on true
				left join v_Lpu L on L.Lpu_id = CCC.CmpLpu_id
				left join v_OftenCallers OC on OC.Person_id = CCC.Person_id
			where CCC.CmpCallCard_id = :CmpCallCard_id
			limit 1
		";
		$queryParams = ["CmpCallCard_id" => $data["CmpCallCard_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadCmpStation(CmpCallCard_model $callObject, $data)
	{
		$query = "
			select
				CmpStation.Lpu_id as \"Lpu_id\",
				CmpStation_id as \"CmpStation_id\",
				CmpStation_Code as \"CmpStation_Code\",
				CmpStation_Name as \"CmpStation_Name\"
			from
				v_CmpStation CmpStation
				inner join v_Lpu Lpu on Lpu.Lpu_id = CmpStation.Lpu_id
			where CmpStation.Lpu_id = :Lpu_id or :Lpu_id is null
		";
		$queryParams = ["Lpu_id" => $data["Lpu_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadCmpIllegalActList(CmpCallCard_model $callObject, $data)
	{
		$query = "
			select
				CIA.CmpIllegalAct_id as \"CmpIllegalAct_id\",
				LPU.Lpu_Nick as \"Lpu_Nick\",
				rtrim(PS.Person_Surname)||' '||rtrim(PS.Person_FirName)||' ' ||rtrim(PS.Person_SecName) as \"Person_FIO\",
				to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_BirthDay\",
				to_char(CIA.CmpIllegalAct_prmDT, '{$callObject->dateTimeForm104}') as \"CmpIllegalAct_prmDT\",
				CIA.CmpIllegalAct_Comment as \"CmpIllegalAct_Comment\",
				case when City.KLCity_Name is not null
				    then 'г. '||City.KLCity_Name
				    else ''
				end||
				case when Town.KLTown_FullName is not null
				    then
						case when City.KLCity_Name is not null
						    then ', '
						    else ''
						end||coalesce(lower(Town.KLSocr_Nick)||'. ', '')||Town.KLTown_Name
				    else ''
				end||
				case when Street.KLStreet_FullName is not null then
					case when socrStreet.KLSocr_Nick is not null
					    then ', '||lower(socrStreet.KLSocr_Nick)||'. '||Street.KLStreet_Name
					    else ', '||Street.KLStreet_FullName
					end
				end||
				case when CIA.Address_House is not null then ', д.'||CIA.Address_House else '' end||
				case when CIA.Address_Corpus is not null then ', к.'||CIA.Address_Corpus else '' end||
				case when CIA.Address_Flat is not null then ', кв.'||CIA.Address_Flat::varchar else ''
				end as \"Address_Name\"
			from
				v_CmpIllegalAct as CIA
				left join v_PersonState PS on PS.Person_id = CIA.Person_id
				left join v_Lpu LPU on LPU.Lpu_id = CIA.Lpu_id
				left join v_KLRgn RGN on RGN.KLRgn_id = CIA.KLRgn_id
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CIA.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = CIA.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = CIA.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = CIA.KLStreet_id
				left join v_KLSocr socrStreet on Street.KLSocr_id = socrStreet.KLSocr_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadCmpIllegalActForm(CmpCallCard_model $callObject, $data)
	{
		$query = "
			select
				CIA.CmpIllegalAct_id as \"CmpIllegalAct_id\",
				CIA.Lpu_id as \"Lpu_id\",
				rtrim(PS.Person_Surname)||' '||rtrim(PS.Person_FirName)||' '||rtrim(PS.Person_SecName) as \"Person_Fio\",
				CIA.Person_id as \"Person_id\",
				CIA.CmpCallCard_id as \"CmpCallCard_id\",
				to_char(CIA.CmpIllegalAct_prmDT, '{$callObject->dateTimeForm104}') as \"CmpIllegalAct_prmDT\",
				CIA.CmpIllegalAct_Comment as \"CmpIllegalAct_Comment\",
				CIA.Address_Zip as \"Address_Zip\",
				CIA.KLCountry_id as \"KLCountry_id\",
				CIA.KLRgn_id as \"KLRgn_id\",
				CIA.KLSubRGN_id as \"KLSubRGN_id\",
				CIA.KLCity_id as \"KLCity_id\",
				CIA.KLTown_id as \"KLTown_id\",
				CIA.KLStreet_id as \"KLStreet_id\",
				CIA.Address_House as \"Address_House\",
				CIA.Address_Corpus as \"Address_Corpus\",
				CIA.Address_Flat as \"Address_Flat\",
				case when City.KLCity_Name is not null then 'г. '||City.KLCity_Name else '' end||
					case when Town.KLTown_FullName is not null
					    then
							case when City.KLCity_Name is not null
							    then ', '
							    else ''
							end||coalesce(lower(Town.KLSocr_Nick)||'. ', '')||Town.KLTown_Name
					    else ''
					end||
					case when Street.KLStreet_FullName is not null
					    then
							case when socrStreet.KLSocr_Nick is not null
							    then ', '||lower(socrStreet.KLSocr_Nick)||'. '||Street.KLStreet_Name
							    else ', '||Street.KLStreet_FullName
							end
					end||
					case when CIA.Address_House is not null then ', д.'||CIA.Address_House else '' end||
					case when CIA.Address_Corpus is not null then ', к.'||CIA.Address_Corpus else '' end||
					case when CIA.Address_Flat is not null then ', кв.'||CIA.Address_Flat::varchar else ''
				end as \"AddressText\"
			from
				v_CmpIllegalAct as CIA
				left join v_PersonState PS on PS.Person_id = CIA.Person_id
				left join v_Lpu LPU on LPU.Lpu_id = CIA.Lpu_id
				left join v_KLRgn RGN on RGN.KLRgn_id = CIA.KLRgn_id
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CIA.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = CIA.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = CIA.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = CIA.KLStreet_id
				left join v_KLSocr socrStreet on Street.KLSocr_id = socrStreet.KLSocr_id
			where CIA.CmpIllegalAct_id = :CmpIllegalAct_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadCmpCloseCardEquipmentViewForm(CmpCallCard_model $callObject, $data)
	{
		if (!isset($data["CmpCloseCard_id"])) {
			return false;
		}
		$query = "
			select
				CCCER.CmpCloseCardEquipmentRel_id as \"CmpCloseCardEquipmentRel_id\",
				CCCER.CmpEquipment_id as \"CmpEquipment_id\",
				CCCER.CmpCloseCardEquipmentRel_UsedOnSpotCnt as \"CmpCloseCardEquipmentRel_UsedOnSpotCnt\",
				CCCER.CmpCloseCardEquipmentRel_UsedInCarCnt as \"CmpCloseCardEquipmentRel_UsedInCarCnt\"
			from v_CmpCloseCardEquipmentRel as CCCER
			where CCCER.CmpCloseCard_id=:CmpCloseCard_id
		";
		$queryParams = ["CmpCloseCard_id" => $data["CmpCloseCard_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadCmpCloseCardEquipmentPrintForm(CmpCallCard_model $callObject, $data)
	{
		if (!isset($data["CmpCloseCard_id"])) {
			return false;
		}
		$query = "
			select
				CCCER.CmpCloseCardEquipmentRel_id as \"CmpCloseCardEquipmentRel_id\",
				CCCER.CmpEquipment_id as \"CmpEquipment_id\",
				CCCER.CmpCloseCardEquipmentRel_UsedOnSpotCnt as \"CmpCloseCardEquipmentRel_UsedOnSpotCnt\",
				CCCER.CmpCloseCardEquipmentRel_UsedInCarCnt as \"CmpCloseCardEquipmentRel_UsedInCarCnt\",
				CE.CmpEquipment_Name as \"CmpEquipment_Name\"
			from
				v_CmpCloseCardEquipmentRel as CCCER
				left join v_CmpEquipment as CE on CE.CmpEquipment_id=CCCER.CmpEquipment_id
			where CCCER.CmpCloseCard_id=:CmpCloseCard_id
		";
		$queryParams = ["CmpCloseCard_id" => $data["CmpCloseCard_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadCmpCloseCardComboboxesViewForm(CmpCallCard_model $callObject, $data)
	{
		if (!isset($data["CmpCallCard_id"])) {
			return false;
		}
		$selectString = "
			coalesce(dboCCCMCode.CmpCloseCardCombo_Code,CCCM.CmpCloseCardCombo_Code,null) as \"CmpCloseCardCombo_id\",
			CCCR.Localize as \"Localize\"
		";
		$fromString = "
			{$callObject->schema}.v_CmpCloseCard CCC
			left join {$callObject->schema}.v_CmpCloseCardRel CCCR on CCCR.CmpCloseCard_id = CCC.CmpCloseCard_id
			left join v_CmpCloseCardCombo dboCCCM on dboCCCM.CmpCloseCardCombo_id = CCCR.CmpCloseCardCombo_id
			left join v_CmpCloseCardCombo dboCCCMCode on dboCCCMCode.CmpCloseCardCombo_Code = CCCR.CmpCloseCardCombo_id
			left join {$callObject->comboSchema}.v_CmpCloseCardCombo CCCM on CCCM.CmpCloseCardCombo_Code = dboCCCM.CmpCloseCardCombo_Code
		";
		$whereString = "CCC.CmpCallCard_id = :CmpCallCard_id";
		$query = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function loadCmpCallCardUslugaGrid(CmpCallCard_model $callObject, $data)
	{
		$params = ["CmpCallCard_id" => $data["CmpCallCard_id"]];
		$query = "
			select
				CCC.Person_id as \"Person_id\",
				CCCU.CmpCallCardUsluga_id as \"CmpCallCardUsluga_id\",
				CCCU.CmpCallCard_id as \"CmpCallCard_id\",
				CCCU.CmpCallCardUsluga_Kolvo as \"CmpCallCardUsluga_Kolvo\",
				CCCU.CmpCallCardUsluga_Cost as \"CmpCallCardUsluga_Cost\",
				CCCU.UslugaComplexTariff_id as \"UslugaComplexTariff_id\",
				CCCU.MedStaffFact_id as \"MedStaffFact_id\",
				CCCU.PayType_id as \"PayType_id\",
				CCCU.UslugaCategory_id as \"UslugaCategory_id\",
				CCCU.UslugaComplex_id as \"UslugaComplex_id\",
				to_char(CCCU.CmpCallCardUsluga_setDate, '{$callObject->dateTimeForm104}') as \"CmpCallCardUsluga_setDate\",
				to_char(CCCU.CmpCallCardUsluga_setTime, '{$callObject->dateTimeForm108}') as \"CmpCallCardUsluga_setTime\",
				UC.UslugaComplex_Code as \"UslugaComplex_Code\",
				UC.UslugaComplex_Name as \"UslugaComplex_Name\",
				UCT.UslugaComplexTariff_Name as \"UslugaComplexTariff_Name\",
				'unchanged' as \"status\"
			from
				v_CmpCallCardUsluga CCCU
				left join v_UslugaComplex UC on UC.UslugaComplex_id = CCCU.UslugaComplex_id
				left join v_CmpCallCard CCC on CCC.CmpCallCard_id = CCCU.CmpCallCard_id
				left join v_UslugaComplexTariff UCT on CCCU.UslugaComplexTariff_id = UCT.UslugaComplexTariff_id
			where CCCU.CmpCallCard_id = :CmpCallCard_id
		";
		$response = $callObject->queryResult($query, $params);
		return $response;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function loadCmpCallCardUslugaForm(CmpCallCard_model $callObject, $data)
	{
		$params = ["CmpCallCardUsluga_id" => $data["CmpCallCardUsluga_id"]];
		$query = "
			select
				CCCU.CmpCallCardUsluga_id as \"CmpCallCardUsluga_id\",
				CCCU.CmpCallCard_id as \"CmpCallCard_id\",
				to_char(CCCU.CmpCallCardUsluga_setDate, '{$callObject->dateTimeForm120}') as \"CmpCallCardUsluga_setDate\",
				to_char(CCCU.CmpCallCardUsluga_setTime, '{$callObject->dateTimeForm108}') as \"CmpCallCardUsluga_setTime\",
				CCCU.MedStaffFact_id as \"MedStaffFact_id\",
				CCCU.PayType_id as \"PayType_id\",
				CCCU.UslugaCategory_id as \"UslugaCategory_id\",
				CCCU.UslugaComplex_id as \"UslugaComplex_id\",
				CCCU.UslugaComplexTariff_id as \"UslugaComplexTariff_id\",
				CCCU.CmpCallCardUsluga_Cost as \"CmpCallCardUsluga_Cost\",
				CCCU.CmpCallCardUsluga_Kolvo as \"CmpCallCardUsluga_Kolvo\",
				CCC.Person_id as \"Person_id\"
			from
				v_CmpCallCardUsluga CCCU
				left join v_CmpCallCard CCC on CCC.CmpCallCard_id = CCCU.CmpCallCard_id
			where CCCU.CmpCallCardUsluga_id = :CmpCallCardUsluga_id
			limit 1
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadCmpEquipmentCombo(CmpCallCard_model $callObject, $data)
	{
		$query = "select 
					CmpEquipment_id as \"CmpEquipment_id\",
					CmpEquipment_Code as \"CmpEquipment_Code\",
					CmpEquipment_Name as \"CmpEquipment_Name\",
					pmUser_insID as \"pmUser_insID\",
					pmUser_updID as \"pmUser_updID\",
					CmpEquipment_insDT as \"CmpEquipment_insDT\",
					CmpEquipment_updDT as \"CmpEquipment_updDT\"
		 from v_CmpEquipment";
		$result = $callObject->db->query($query);
		/**@var CI_DB_result $result */
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function loadCmpCallCardDrugList(CmpCallCard_model $callObject, $data)
	{
		$params = ["CmpCallCard_id" => $data["CmpCallCard_id"]];
		$query = "
			select
                cccd.CmpCallCardDrug_id as \"CmpCallCardDrug_id\",
                cccd.CmpCallCard_id as \"CmpCallCard_id\",
                to_char(cccd.CmpCallCardDrug_setDate, '{$callObject->dateTimeForm104}') as \"CmpCallCardDrug_setDate\",
                to_char(cccd.CmpCallCardDrug_setTime, '{$callObject->dateTimeForm108}') as \"CmpCallCardDrug_setTime\",
                cccd.MedStaffFact_id as \"MedStaffFact_id\",
                cccd.Drug_id as \"Drug_id\",
                cccd.CmpCallCardDrug_Ser as \"CmpCallCardDrug_Ser\",
                cccd.CmpCallCardDrug_Kolvo as \"CmpCallCardDrug_Kolvo\",
                cccd.GoodsUnit_id as \"GoodsUnit_id\",
                cccd.CmpCallCardDrug_KolvoUnit as \"CmpCallCardDrug_KolvoUnit\",
                cccd.CmpCallCardDrug_Cost as \"CmpCallCardDrug_Cost\",
                cccd.CmpCallCardDrug_Sum as \"CmpCallCardDrug_Sum\",
                cccd.DrugFinance_id as \"DrugFinance_id\",
                cccd.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
                cccd.Storage_id as \"Storage_id\",
                cccd.Mol_id as \"Mol_id\",
                cccd.DocumentUc_id as \"DocumentUc_id\",
                cccd.DocumentUcStr_id as \"DocumentUcStr_id\",
                cccd.DocumentUcStr_oid as \"DocumentUcStr_oid\",
                cccd.LpuBuilding_id as \"LpuBuilding_id\",
                d.DrugPrepFas_id as \"DrugPrepFas_id\",
                d.Drug_Name as \"Drug_Name\",
                d.DrugTorg_Name as \"DrugTorg_Name\",
                gu.GoodsUnit_Name as \"GoodsUnit_Name\",
                du.Contragent_sid as \"Contragent_id\",
                du.Lpu_id as \"Lpu_id\",
                du.Org_id as \"Org_id\",
                du.StorageZone_sid as \"StorageZone_id\",
                dus.PrepSeries_id as \"PrepSeries_id\",
                dcm.DrugComplexMnn_RusName as \"DrugComplexMnn_RusName\",
                dds.DrugDocumentStatus_Code as \"DrugDocumentStatus_Code\",
		        dn.DrugNomen_Code as \"DrugNomen_Code\"
			from
				v_CmpCallCardDrug cccd
				left join rls.v_Drug d on d.Drug_id = cccd.Drug_id
				left join v_GoodsUnit gu on gu.GoodsUnit_id = cccd.GoodsUnit_id
				left join v_DocumentUc du on du.DocumentUc_id = cccd.DocumentUc_id
				left join v_DocumentUcStr dus on dus.DocumentUcStr_id = cccd.DocumentUcStr_id
				left join v_DrugDocumentStatus dds on dds.DrugDocumentStatus_id = du.DrugDocumentStatus_id
				left join rls.v_DrugPrep dpf on dpf.DrugPrepFas_id = d.DrugPrepFas_id
				left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                left join lateral (
                    select i_dn.DrugNomen_Code
                    from rls.v_DrugNomen i_dn
                    where i_dn.Drug_id = d.Drug_id
                    order by i_dn.DrugNomen_Code desc
                    limit 1
                ) as dn on true
			where cccd.CmpCallCard_id = :CmpCallCard_id;
		";
		$response = $callObject->queryResult($query, $params);
		return $response;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function loadCmpCallCardEvnDrugList(CmpCallCard_model $callObject, $data)
	{
		$params = ["CmpCallCard_id" => $data["CmpCallCard_id"]];
		$query = "
			select
                ed.EvnDrug_id as \"EvnDrug_id\",
                ed.CmpCallCard_id as \"CmpCallCard_id\",
                ed.EvnDrug_Comment as \"EvnDrug_Comment\",
                to_char(ed.EvnDrug_setDate, '{$callObject->dateTimeForm104}') as \"EvnDrug_setDate\",
                to_char(ed.EvnDrug_setTime, '{$callObject->dateTimeForm108}') as \"EvnDrug_setTime\",
                ed.Drug_id as \"Drug_id\",
                ed.DrugNomen_id as \"DrugNomen_id\",
                ed.EvnDrug_Kolvo as \"EvnDrug_Kolvo\",
                ed.GoodsUnit_id as \"GoodsUnit_id\",
                gu.GoodsUnit_Name as \"GoodsUnit_Name\",
                ed.Lpu_id as \"Lpu_id\",
		        dn.DrugNomen_Code as \"DrugNomen_Code\",
		        dn.DrugNomen_Name as \"DrugNomen_Name\"
			from
				v_EvnDrug ed
				left join v_GoodsUnit gu on gu.GoodsUnit_id = ed.GoodsUnit_id
                left join lateral (
                    select
                        i_dn.DrugNomen_Code,
                        i_dn.DrugNomen_Name
                    from rls.v_DrugNomen i_dn
                    where i_dn.DrugNomen_id = ed.DrugNomen_id
                    order by i_dn.DrugNomen_Code desc
					limit 1
                ) as dn on true
			where ed.CmpCallCard_id = :CmpCallCard_id;
		";
		$response = $callObject->queryResult($query, $params);
		return $response;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool|false
	 */
	public static function loadCmpCallCardList(CmpCallCard_model $callObject, $data)
	{
		$params = [];
		$filters = [];
		if (!empty($data["CmpCallCard_id"])) {
			$filters[] = "CCC.CmpCallCard_id = :CmpCallCard_id";
			$params["CmpCallCard_id"] = $data["CmpCallCard_id"];
		} else {
			$filters[] = "CCC.CmpCallCard_Numv is not null";
			$date_str = !empty($data["date"]) ? $data["date"] : $callObject->currentDT->format("Y-m-d");
			$begDate = date_modify(date_create($date_str), "-1 day");
			$endDate = date_create($date_str);
			$filters[] = "CCC.CmpCallCard_prmDT between :begDate and :endDate";
			$params["begDate"] = $begDate->format("Y-m-d") . " 00:00";
			$params["endDate"] = $endDate->format("Y-m-d") . " 23:59";
			if (!empty($data["query"]) && is_numeric($data["query"])) {
				$filters[] = "CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
				$params["CmpCallCard_Numv"] = $data["query"];
			}
		}
		$whereString = (count($filters) != 0) ? "where " . implode(" and ", $filters) : "";
		$query = "
			select
				CCC.CmpCallCard_id as \"CmpCallCard_id\",
				CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
				to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm104}') as \"CmpCallCard_prmDate\",
				to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm108}') as \"CmpCallCard_prmTime\",
				CCC.Person_id as \"Person_id\",
				rtrim(CCC.Person_SurName) as \"Person_SurName\",
				rtrim(CCC.Person_FirName) as \"Person_FirName\",
				rtrim(CCC.Person_SecName) as \"Person_SecName\",
				to_char(BirthDay.Value, '{$callObject->dateTimeForm104}') as \"Person_BirthDay\",
				AgeYMD(BirthDay.Value, CCC.CmpCallCard_prmDT, 1) as \"PersonAgeYears\",
				AgeYMD(BirthDay.Value, CCC.CmpCallCard_prmDT, 2) as \"PersonAgeMonths\",
				AgeYMD(BirthDay.Value, CCC.CmpCallCard_prmDT, 3) as \"PersonAgeDays\"
			from
				v_CmpCallCard CCC
				left join v_PersonState PS on PS.Person_id = CCC.Person_id
				left join lateral (
					select coalesce(CCC.Person_BirthDay,PS.Person_BirthDay) as Value
				) as BirthDay on true
			{$whereString}
		";
		$response = $callObject->queryResult($query, $params);
		if (!is_array($response)) {
			return false;
		}
		foreach ($response as &$item) {
			$item["PersonAgeStr"] = "";
			switch (true) {
				case $item["PersonAgeYears"] > 0:
					$item["PersonAgeStr"] = $item["PersonAgeYears"] . " " . ru_word_case("год", "года", "лет", $item["PersonAgeYears"]);
					break;
				case $item["PersonAgeMonths"] > 0:
					$item["PersonAgeStr"] = $item["PersonAgeMonths"] . " " . ru_word_case("месяц", "месяца", "месяцев", $item["PersonAgeMonths"]);
					break;
				case $item["PersonAgeDays"] > 0:
					$item["PersonAgeStr"] = $item["PersonAgeDays"] . " " . ru_word_case("день", "дня", "дней", $item["PersonAgeDays"]);
					break;
			}
		}
		return $response;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function loadCmpCallCardSimpleDrugList(CmpCallCard_model $callObject, $data)
	{
		$params = ["CmpCallCard_id" => $data["CmpCallCard_id"]];
		$query = "
			select
                cccd.CmpCallCardDrug_id as \"CmpCallCardDrug_id\",
                cccd.CmpCallCard_id as \"CmpCallCard_id\",
                cccd.CmpCallCardDrug_Comment as \"CmpCallCardDrug_Comment\",
                to_char(cccd.CmpCallCardDrug_setDate, '{$callObject->dateTimeForm104}') as \"CmpCallCardDrug_setDate\",
                to_char(cccd.CmpCallCardDrug_setTime, '{$callObject->dateTimeForm108}') as \"CmpCallCardDrug_setTime\",
                cccd.Drug_id as \"Drug_id\",
                cccd.CmpCallCardDrug_Kolvo as \"CmpCallCardDrug_Kolvo\",
                cccd.GoodsUnit_id as \"GoodsUnit_id\",
                gu.GoodsUnit_Name as \"GoodsUnit_Name\",
                du.Lpu_id as \"Lpu_id\",
                cccd.DrugNomen_id as \"DrugNomen_id\",
                cccd.MedStaffFact_id as \"MedStaffFact_id\",
		        dn.DrugNomen_Code as \"DrugNomen_Code\",
		        dn.DrugNomen_Name as \"DrugNomen_Name\"
			from
				v_CmpCallCardDrug cccd
				left join rls.v_Drug d on d.Drug_id = cccd.Drug_id			
				left join v_GoodsUnit gu on gu.GoodsUnit_id = cccd.GoodsUnit_id
				left join v_DocumentUc du on du.DocumentUc_id = cccd.DocumentUc_id
                left join lateral (
                    select
                        i_dn.DrugNomen_Code,
                        i_dn.DrugNomen_Name,
                        i_dn.DrugNomen_id
                    from rls.v_DrugNomen i_dn
                    where i_dn.DrugNomen_id = cccd.DrugNomen_id
                    order by i_dn.DrugNomen_Code desc
					limit 1
                ) as dn on true
			where cccd.CmpCallCard_id = :CmpCallCard_id;
		";
		$response = $callObject->queryResult($query, $params);
		return $response;
	}
}