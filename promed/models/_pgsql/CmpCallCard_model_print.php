<?php

class CmpCallCard_model_print
{
	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	public static function printCmpCall(CmpCallCard_model $callObject, $data)
	{
		$query = "select 
                            CmpCallCard_Numv as \"CmpCallCard_Numv\",
                            CallDate as \"CallDate\",
                            CallTime as \"CallTime\",
                            adress as \"adress\",
                            person_fio as \"person_fio\",
                            Person_BirthDay as \"Person_BirthDay\",
                            yo as \"yo\",
                            Diag_Name as \"Diag_Name\",
                            LpuUnit_Name as \"LpuUnit_Name\",
                            HiMed as \"HiMed\",
                            MedStat as \"MedStat\"
                    from rpt2.pan_Spravka_SMPCall(:CmpCallCard_id)";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		$result[0]["success"] = true;
		return $result;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function printCmpCloseCard110(CmpCallCard_model $callObject, $data)
	{
		$selectArray = [
			"CLC.CmpCallCard_id as \"CmpCallCard_id\"",
			"CLC.CmpCloseCard_id as \"CmpCloseCard_id\"",
			"CLC.Day_num as \"Day_num\"",
			"CLC.Year_num as \"Year_num\"",
			"to_char(CC.CmpCallCard_insDT, '{$callObject->dateTimeForm104}') as \"CallCardDate\"",
			"CLC.Feldsher_id as \"Feldsher_id\"",
			"case when coalesce(CLC.LpuBuilding_id, 0) > 0 then LB.LpuBuilding_Name else CLC.StationNum end as \"StationNum\"",
			"CLC.EmergencyTeamNum as \"EmergencyTeamNum\"",
			"to_char(CLC.AcceptTime, '{$callObject->dateTimeForm108}') as \"AcceptTime\"",
			"to_char(CLC.AcceptTime, '{$callObject->dateTimeForm104}') as \"AcceptDate\"",
			"to_char(CLC.TransTime, '{$callObject->dateTimeForm108}') as \"TransTime\"",
			"to_char(CLC.GoTime, '{$callObject->dateTimeForm108}') as \"GoTime\"",
			"to_char(CLC.ArriveTime, '{$callObject->dateTimeForm108}') as \"ArriveTime\"",
			"to_char(CLC.TransportTime, '{$callObject->dateTimeForm108}') as \"TransportTime\"",
			"to_char(CLC.ToHospitalTime, '{$callObject->dateTimeForm108}') as \"ToHospitalTime\"",
			"to_char(CLC.EndTime, '{$callObject->dateTimeForm108}') as \"EndTime\"",
			"to_char(CLC.BackTime, '{$callObject->dateTimeForm108}') as \"BackTime\"",
			"CLC.SummTime as \"SummTime\"",
			"CLC.Area_id as \"Area_id\"",
			"KL_AR.KLArea_Name as \"Area\"",
			"CLC.City_id as \"City_id\"",
			"KL_CITY.KLArea_Name as \"City\"",
			"CLC.Town_id as \"Town_id\"",
			"KL_TOWN.KLArea_Name as \"Town\"",
			"CLC.Street_id as \"Street_id\"",
			"case when coalesce(CLC.Street_id,0) > 0 then KL_ST.KLStreet_Name else ClC.CmpCloseCard_Street end as \"Street\"",
			"case when SecondStreet.KLStreet_FullName is not null then case when socrSecondStreet.KLSocr_Nick is not null then upper(socrSecondStreet.KLSocr_Nick)||'. '||SecondStreet.KLStreet_Name else SecondStreet.KLStreet_FullName end else '' end as \"secondStreetName\"",
			"CLC.House as \"House\"",
			"Lpu.Lpu_name as \"Lpu_name\"",
			"Lpu.UAddress_Address as \"UAddress_Address\"",
			"Lpu.Lpu_Phone as \"Lpu_Phone\"",
			"CLC.Korpus as \"Korpus\"",
			"CLC.Room as \"Room\"",
			"CLC.Office as \"Office\"",
			"CLC.Entrance as \"Entrance\"",
			"CLC.Level as \"Level\"",
			"CLC.CodeEntrance as \"CodeEntrance\"",
			"CLC.Fam as \"Fam\"",
			"CLC.Name as \"Name\"",
			"CLC.Middle as \"Middle\"",
			"CLC.Age as \"Age\"",
			"coalesce(CLC.Person_Snils, PS.Person_Snils) as \"Person_Snils\"",
			"SX.Sex_name as \"Sex_name\"",
			"RS.CmpReason_Name as \"Reason\"",
			"CLC.Work as \"Work\"",
			"CLC.DocumentNum as \"DocumentNum\"",
			"CLC.Ktov as \"Ktov\"",
			"coalesce(CCrT.CmpCallerType_Name,CLC.Ktov) as \"CmpCallerType_Name\"",
			"CLC.Phone as \"Phone\"",
			"CLC.FeldsherAccept as \"FeldsherAccept\"",
			"CLC.FeldsherTrans as \"FeldsherTrans\"",
			"rtrim(MPA.Person_Fio) as \"FeldsherAcceptName\"",
			"rtrim(MPT.Person_Fio) as \"FeldsherTransName\"",
			"CLC.CallType_id as \"CallType_id\"",
			"CCT.CmpCallType_Name as \"CallType\"",
			"CCT.CmpCallType_Code as \"CmpCallType_Code\"",
			"case when coalesce(CLC.isAlco, 1) = 2 then 'Да' else 'Нет' end as \"isAlco\"",
			"CLC.Complaints as \"Complaints\"",
			"CLC.Anamnez as \"Anamnez\"",
			"case when coalesce(CLC.isMenen,1) = 2 then 'Да' else 'Нет' end as \"isMenen\"",
			"case when coalesce(CLC.isNist,1) = 2 then 'Да' else 'Нет' end as \"isNist\"",
			"case when coalesce(CLC.isAnis,1) = 2 then 'Да' else 'Нет' end as \"isAnis\"",
			"case when coalesce(CLC.isLight,1) = 2 then 'Да' else 'Нет' end as \"isLight\"",
			"case when coalesce(CLC.isAcro,1) = 2 then 'Да' else 'Нет' end as \"isAcro\"",
			"case when coalesce(CLC.isMramor,1) = 2 then 'Да' else 'Нет' end as \"isMramor\"",
			"case when coalesce(CLC.isHale,1) = 2 then 'Да' else 'Нет' end as \"isHale\"",
			"case when coalesce(CLC.isPerit,1) = 2 then 'Да' else 'Нет' end as \"isPerit\"",
			"case when coalesce(CLC.isSogl,1) = 2 then 'Да' else 'Нет' end as \"isSogl\"",
			"case when coalesce(CLC.isOtkazMed,1) = 2 then 'Да' else 'Нет' end as \"isOtkazMed\"",
			"case when coalesce(CLC.isOtkazHosp,1) = 2 then 'Да' else 'Нет' end as \"isOtkazHosp\"",
			"CLC.Urine as \"Urine\"",
			"CLC.Shit as \"Shit\"",
			"CLC.OtherSympt as \"OtherSympt\"",
			"CLC.CmpCloseCard_AddInfo as \"CmpCloseCard_AddInfo\"",
			"CLC.WorkAD as \"WorkAD\"",
			"CLC.AD as \"AD\"",
			"CLC.Chss as \"Chss\"",
			"CLC.Pulse as \"Pulse\"",
			"CLC.Temperature as \"Temperature\"",
			"CLC.Chd as \"Chd\"",
			"CLC.Pulsks as \"Pulsks\"",
			"CLC.Gluck as \"Gluck\"",
			"CLC.LocalStatus as \"LocalStatus\"",
			"CLC.Ekg1 as \"Ekg1\"",
			"to_char(CLC.Ekg1Time, '{$callObject->dateTimeForm108}') as \"Ekg1Time\"",
			"CLC.Ekg2 as \"Ekg2\"",
			"to_char(CLC.Ekg2Time, '{$callObject->dateTimeForm108}') as \"Ekg2Time\"",
			"CLC.Diag_id as \"Diag_id\"",
			"CLC.Diag_uid as \"Diag_uid\"",
			"DIAG.Diag_FullName as \"Diag\"",
			"DIAG.Diag_Code as \"CodeDiag\"",
			"UDIAG.Diag_FullName as \"uDiag\"",
			"UDIAG.Diag_Code as \"uCodeDiag\"",
			"CLC.HelpPlace as \"HelpPlace\"",
			"CLC.HelpAuto as \"HelpAuto\"",
			"CLC.CmpCloseCard_ClinicalEff as \"CmpCloseCard_ClinicalEff\"",
			"CLC.EfAD as \"EfAD\"",
			"CLC.EfChss as \"EfChss\"",
			"CLC.EfPulse as \"EfPulse\"",
			"CLC.EfTemperature as \"EfTemperature\"",
			"CLC.EfChd as \"EfChd\"",
			"CLC.EfPulsks as \"EfPulsks\"",
			"CLC.EfGluck as \"EfGluck\"",
			"CLC.Kilo as \"Kilo\"",
			"CLC.DescText as \"DescText\"",
			"CLC.CmpCloseCard_Epid as \"CmpCloseCard_Epid\"",
			"CLC.CmpCloseCard_Glaz as \"CmpCloseCard_Glaz\"",
			"CLC.CmpCloseCard_GlazAfter as \"CmpCloseCard_GlazAfter\"",
			"CLC.CmpCloseCard_m1 as \"CmpCloseCard_m1\"",
			"CLC.CmpCloseCard_e1 as \"CmpCloseCard_e1\"",
			"CLC.CmpCloseCard_v1 as \"CmpCloseCard_v1\"",
			"CLC.CmpCloseCard_m2 as \"CmpCloseCard_m2\"",
			"CLC.CmpCloseCard_e2 as \"CmpCloseCard_e2\"",
			"CLC.CmpCloseCard_v2 as \"CmpCloseCard_v2\"",
			"CLC.CmpCloseCard_Topic as \"CmpCloseCard_Topic\"",
			"CC.CmpTrauma_id as \"CmpTrauma_id\""
		];
		$fromArray = [
			"{$callObject->schema}.v_CmpCloseCard CLC",
			"left join v_Sex SX on SX.Sex_id = CLC.Sex_id",
			"left join v_MedPersonal MPA on MPA.MedPersonal_id = CLC.FeldsherAccept",
			"left join v_MedPersonal MPT on MPT.MedPersonal_id = CLC.FeldsherTrans",
			"left join v_PersonState PS on PS.Person_id = CLC.Person_id",
			"left join v_CmpReason RS on RS.CmpReason_id = CLC.CallPovod_id",
			"left join KLStreet KL_ST on KL_ST.KLStreet_id = CLC.Street_id",
			"left join v_KLStreet SecondStreet on SecondStreet.KLStreet_id = CLC.CmpCloseCard_UlicSecond",
			"left join v_KLSocr socrSecondStreet on SecondStreet.KLSocr_id = socrSecondStreet.KLSocr_id",
			"left join KLArea KL_AR on KL_AR.KLArea_id = CLC.Area_id",
			"left join KLArea KL_CITY on KL_CITY.KLArea_id = CLC.City_id",
			"left join KLArea KL_TOWN on KL_TOWN.KLArea_id = CLC.Town_id",
			"left join v_CmpCallType CCT on CCT.CmpCallType_id = CLC.CallType_id",
			"left join v_CmpCallerType CCrT on CCrT.CmpCallerType_id = CLC.CmpCallerType_id",
			"left join v_Diag DIAG on DIAG.Diag_id = CLC.Diag_id",
			"left join v_Diag UDIAG on UDIAG.Diag_id = CLC.Diag_uid",
			"left join v_CmpCallCard CC on CC.CmpCallCard_id = CLC.CmpCallCard_id",
			"left join v_Lpu Lpu on Lpu.Lpu_id = CC.Lpu_id",
			"left join v_LpuBuilding LB on LB.LpuBuilding_id = CLC.LpuBuilding_id"
		];
		$selectString = implode(",\n", $selectArray);
		$fromString = implode(" \n", $fromArray);
		$whereString = "CLC.CmpCallCard_id = :CmpCallCard_id";
		$query = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
			limit 1
		";
		$queryParams = ["CmpCallCard_id" => $data["CmpCallCard_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($query)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function printCmpCloseCardEMK(CmpCallCard_model $callObject, $data)
	{
		$selectArray = [
			"CLC.CmpCallCard_id as \"CmpCallCard_id\"",
			"CLC.CmpCloseCard_id as \"CmpCloseCard_id\"",
			"to_char(CC.CmpCallCard_insDT, '{$callObject->dateTimeForm104}') as \"CallCardDate\"",
			"CLC.Day_num as \"Day_num\"",
			"CLC.Year_num as \"Year_num\"",
			"ClC.CmpCloseCard_DayNumPr as \"CmpCloseCard_DayNumPr\"",
			"ClC.CmpCloseCard_YearNumPr as \"CmpCloseCard_YearNumPr\"",
			"to_char(CLC.AcceptTime, '{$callObject->dateTimeForm104}')||' '||to_char(CLC.AcceptTime, '{$callObject->dateTimeForm108}') as \"AcceptDateTime\"",
			"SX.Sex_name as \"Sex_name\"",
			"CLC.SummTime as \"SummTime\"",
			"CLC.Fam as \"Fam\"",
			"CLC.Name as \"Name\"",
			"CLC.Middle as \"Middle\"",
			"DIAG.Diag_FullName as \"Diag\"",
			"rtrim(coalesce(UCA.PMUser_surName, ''))||' '||rtrim(coalesce(UCA.PMUser_firName, ''))||' '||rtrim(coalesce(UCA.PMUser_secName, '')) as \"FeldsherAcceptName\"",
			"to_char(ccp.CmpCallCardCostPrint_setDT, '{$callObject->dateTimeForm104}') as \"CmpCallCardCostPrint_setDT\"",
			"ccp.CmpCallCardCostPrint_IsNoPrint as \"CmpCallCardCostPrint_IsNoPrint\"",
			"to_char(ccp.CmpCallCardCostPrint_Cost, '{$callObject->numericForm18_2}') as \"CostPrint\"",
			"coalesce(msfC.Person_Fio, msfE.Person_Fio) as \"EmergencyTeam_HeadShift_Name\""
		];
		$fromArray = [
			"{$callObject->schema}.v_CmpCloseCard CLC",
			"left join v_CmpCallCard CC on CC.CmpCallCard_id = CLC.CmpCallCard_id",
			"left join v_CmpCallCardCostPrint ccp on ccp.CmpCallCard_id = cc.CmpCallCard_id",
			"left join v_EmergencyTeam ET on CC.EmergencyTeam_id = ET.EmergencyTeam_id",
			"left join v_MedStaffFact msfE on msfE.MedPersonal_id = ET.EmergencyTeam_HeadShift",
			"left join v_MedStaffFact msfC on msfC.MedStaffFact_id = CLC.MedStaffFact_id",
			"left join Sex SX on SX.Sex_id = CLC.Sex_id",
			"left join v_Diag DIAG on DIAG.Diag_id = CLC.Diag_id",
			"left join v_pmUserCache UCA on UCA.PMUser_id = CLC.pmUser_insID",
			"left join v_pmUserCache UCT on UCT.PMUser_id = CLC.FeldsherTrans"
		];
		$selectString = implode(",\n", $selectArray);
		$fromString = implode(" \n", $fromArray);
		$whereString = "CLC.CmpCloseCard_id = :CmpCloseCard_id";
		$query = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
			limit 1
		";
		$queryParams = ["CmpCloseCard_id" => $data["CmpCloseCard_id"]];
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
	public static function printCmpCallCardEMK(CmpCallCard_model $callObject, $data)
	{
		$query = "
			select
				 CC.CmpCallCard_id as \"CmpCallCard_id\",
			    to_char(CC.CmpCallCard_insDT, '{$callObject->dateTimeForm104}') as \"CallCardDate\",
			    CC.CmpCallCard_Numv as \"Day_num\",
			    CC.CmpCallCard_Ngod as \"Year_num\",
			    CC.CmpCallCard_NumvPr as \"CmpCallCard_NumvPr\",
			    CC.CmpCallCard_NgodPr as \"CmpCallCard_NgodPr\",
			    to_char(CC.CmpCallCard_prmDT, '{$callObject->dateTimeForm104}')||' '||to_char(CC.CmpCallCard_prmDT, '{$callObject->dateTimeForm108}') as \"AcceptDateTime\",
			    SX.Sex_name as \"Sex_name\",
			    CC.CmpCallCard_Dlit as \"SummTime\",
			    CC.Person_SurName as \"Fam\",
			    CC.Person_FirName as \"Name\",
			    CC.Person_SecName as \"Middle\",
			    DIAG.Diag_FullName as \"Diag\",
			    CR.CmpReason_Name as \"CmpReason_Name\",
			    CMPD.CmpDiag_Name as \"CmpDiag_Name\",
			    MSF.Person_Fio as \"MedPersonal_Name\",
			    CRes.CmpResult_Name as \"CmpResult_Name\",
			    RDT.ResultDeseaseType_Name as \"ResultDeseaseType_Name\",
			    coalesce( RGN.KLRgn_FullName, '')||
					case when SRGN.KLSubRgn_FullName is not null then ', '||SRGN.KLSubRgn_FullName else ', г.'||City.KLCity_Name end||
					case when Town.KLTown_FullName is not null then ', '||Town.KLTown_FullName else '' end||
					case when Street.KLStreet_FullName is not null then ', ул.'||Street.KLStreet_Name else '' end||
					case when CC.CmpCallCard_Dom is not null then ', д.'||CC.CmpCallCard_Dom else '' end||
					case when CC.CmpCallCard_Kvar is not null then ', кв.'||CC.CmpCallCard_Kvar else '' end||
					case when CC.CmpCallCard_Comm is not null then '</br>'||CC.CmpCallCard_Comm else '' end
			    as \"Adress_Name\",
				case when coalesce(CC.CmpCallCard_City, '') != '' then 'Нас. пункт '||CC.CmpCallCard_City::varchar else '' end||
				case when coalesce(CC.CmpCallCard_Ulic, '') != '' then ', ул. '||CC.CmpCallCard_Ulic::varchar else '' end||
				case when coalesce(CC.CmpCallCard_Dom, '') != '' then ', дом '||CC.CmpCallCard_Dom::varchar else '' end||
				case when coalesce(CC.CmpCallCard_Kvar, '') != '' then ', кварт. '||CC.CmpCallCard_Kvar::varchar else '' end||
				case when coalesce(CC.CmpCallCard_Room, '') != '' then ', комната '||CC.CmpCallCard_Room::varchar else '' end||
				case when coalesce(CAST(CC.CmpCallCard_Podz as varchar), '') != '' then ', подъезд '||CC.CmpCallCard_Podz::varchar else '' end||
				case when coalesce(CAST(CC.CmpCallCard_Etaj as varchar), '') != '' then ', этаж '||CC.CmpCallCard_Etaj::varchar else '' end
				as \"CmpCallPlace\"
			from
				v_CmpCallCard CC
				LEFT JOIN Sex SX on SX.Sex_id = CC.Sex_id
				left join v_Diag DIAG on DIAG.Diag_id = CC.Diag_uid
				left join CmpDiag CMPD on CMPD.CmpDiag_id = CC.CmpDiag_oid
				left join v_MedStaffFact MSF on MSF.MedStaffFact_id = CC.MedStaffFact_id
				left join v_MedPersonal MP on MP.MedPersonal_id = CC.MedPersonal_id
				LEFT JOIN v_CmpReason CR on CR.CmpReason_id = CC.CmpReason_id
				left JOIN v_CmpResult CRes on CRes.CmpResult_id = CC.CmpResult_id
				left JOIN fed.v_ResultDeseaseType RDT on RDT.ResultDeseaseType_id = CC.ResultDeseaseType_id
				left join v_KLRgn RGN on RGN.KLRgn_id = CC.KLRgn_id
                left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CC.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = CC.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = CC.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = CC.KLStreet_id
			where CC.CmpCallCard_id = :CmpCallCard_id
			limit 1
		";
		$queryParams = ["CmpCallCard_id" => $data["CmpCallCard_id"]];
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
	 * @return bool
	 */
	public static function printCmpCallCardHeader(CmpCallCard_model $callObject, $data)
	{
		return false;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function printCmpCallCard(CmpCallCard_model $callObject, $data)
	{
		$query = "
			select
				CC.CmpCallCard_id as \"CmpCallCard_id\",
			    to_char(CC.CmpCallCard_insDT, '{$callObject->dateTimeForm104}') as \"CallCardDate\",
			    CC.CmpCallCard_Numv as \"Day_num\",
			    CC.CmpCallCard_Ngod as \"Year_num\",
			    to_char(CC.CmpCallCard_prmDT, '{$callObject->dateTimeForm104}')||' '||to_char(CC.CmpCallCard_prmDT, '{$callObject->dateTimeForm108}') as \"AcceptDateTime\",
			    to_char(CC.CmpCallCard_prmDT, '{$callObject->dateTimeForm104}') as \"AcceptDate\",
			    to_char(CC.CmpCallCard_prmDT, '{$callObject->dateTimeForm108}') as \"AcceptTime\",
			    case when to_char(CC.CmpCallCard_Tper, '{$callObject->dateTimeForm104}')!='01.01.1900' then to_char(CC.CmpCallCard_Tper, '{$callObject->dateTimeForm120}') else '' end as \"TransTime\",
			    case when to_char(CC.CmpCallCard_Vyez, '{$callObject->dateTimeForm104}')!='01.01.1900' then to_char(CC.CmpCallCard_Vyez, '{$callObject->dateTimeForm120}') else '' end as \"GoTime\",
			    case when to_char(CC.CmpCallCard_Przd, '{$callObject->dateTimeForm104}')!='01.01.1900' then to_char(CC.CmpCallCard_Przd, '{$callObject->dateTimeForm120}') else '' end as \"ArriveTime\",
			    case when to_char(CC.CmpCallCard_Tsta, '{$callObject->dateTimeForm104}')!='01.01.1900' then to_char(CC.CmpCallCard_Tsta, '{$callObject->dateTimeForm120}') else '' end as \"ToHospitalTime\",
			    case when to_char(CC.CmpCallCard_Tisp, '{$callObject->dateTimeForm104}')!='01.01.1900' then to_char(CC.CmpCallCard_Tisp, '{$callObject->dateTimeForm120}') else '' end as \"BackTime\",
			    '' as \"TransportTime\",
			    '' as \"EndTime\",
			    SX.Sex_name as \"Sex_name\",
			    CC.CmpCallCard_Dlit as \"SummTime\",
			    CC.Person_SurName as \"Fam\",
			    CC.Person_FirName as \"Name\",
			    CC.Person_SecName as \"Middle\",
			    CC.Person_Age as \"Age\",
			    DIAG.Diag_FullName as \"Diag\",
			    CR.CmpReason_Name as \"CmpReason_Name\",
			    CC.CmpCallCard_Numb as \"EmergencyTeamNum\",
			    CC.CmpCallCard_Stan as \"StationNum\",
			    Lpu.Lpu_name as \"Lpu_name\",
			    Lpu.UAddress_Address as \"UAddress_Address\",
			    Lpu.Lpu_Phone as \"Lpu_Phone\",
			    CCT.CmpCallType_Name as \"CmpCallType_Name\",
			    ODIAG.Diag_FullName as \"oDiag\",
			    UCA.PMUser_Name as \"FeldsherAcceptName\",
			    coalesce(RGN.KLRgn_FullName, '')||
					case when SRGN.KLSubRgn_FullName is not null then ', '||SRGN.KLSubRgn_FullName else ', г.'||City.KLCity_Name end||
					case when Town.KLTown_FullName is not null then ', '||Town.KLTown_FullName else '' end||
					case when Street.KLStreet_FullName is not null then ', ул.'||Street.KLStreet_Name else '' end||
					case when CC.CmpCallCard_Dom is not null then ', д.'||CC.CmpCallCard_Dom else '' end||
					case when CC.CmpCallCard_Kvar is not null then ', кв.'||CC.CmpCallCard_Kvar else '' end||
					case when CC.CmpCallCard_Comm is not null then '</br>'||CC.CmpCallCard_Comm else '' end
				as \"Adress_Name\"
			from
				v_CmpCallCard CC
				left join Sex SX on SX.Sex_id = CC.Sex_id
				left join v_Diag DIAG on DIAG.Diag_id = CC.Diag_uid
				left join v_Diag ODIAG on DIAG.Diag_id = CC.Diag_gid
				left join v_pmUserCache UCA on UCA.PMUser_id = CC.pmUser_insID
				left join v_pmUserCache UCT on UCT.PMUser_id = CC.MedPersonal_id
				left join v_CmpReason CR on CR.CmpReason_id = CC.CmpReason_id
				left join v_KLRgn RGN on RGN.KLRgn_id = CC.KLRgn_id
                left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CC.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = CC.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = CC.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = CC.KLStreet_id
				left join v_Lpu Lpu on Lpu.Lpu_id = CC.Lpu_id
				left join v_CmpCallType CCT on CCT.CmpCallType_id = CC.CmpCallType_id
			where CC.CmpCallCard_id = :CmpCallCard_id
			limit 1
		";
		$queryParams = ["CmpCallCard_id" => $data["CmpCallCard_id"]];
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
	 * @return array
	 */
	public static function printReportCmp(CmpCallCard_model $callObject, $data)
	{
		/**@var CI_DB_result $result */
		$res = [];
		// Отказаны
		$query = "
			select count(1) as \"reject\"
			from v_CmpCallCard CCC
			where CCC.CmpCallCardStatusType_id = 5
			  and CCC.Lpu_id = :Lpu_id
			  and CCC.pmUser_insID > 1
			  and CCC.CmpCallCard_prmDT >= CAST(:Daydate1 as date)
			  and CCC.CmpCallCard_prmDT <= CAST(:Daydate2 as date)
		";
		$queryParams = [
			"Daydate1" => $data["daydate1"],
			"Daydate2" => $data["daydate2"],
			"Lpu_id" => $data["Lpu_id"]
		];
		$result = $callObject->db->query($query, $queryParams);
		if (is_object($result)) {
			$preres = $result->result("array");
			$preres = $preres[0]["reject"];
			$res["reject"] = $preres;
		}

		// переданы в НМП
		$query = "
			select count(1) as \"transmit_nmp\"
			from v_CmpCallCard CCC
			where CCC.Lpu_ppdid is not null
			  and CCC.Lpu_id = :Lpu_id
			  and CCC.pmUser_insID > 1
			  and CCC.CmpCallCard_prmDT >= CAST(:Daydate1 as date)
			  and CCC.CmpCallCard_prmDT <= CAST(:Daydate2 as date)
		";
		$queryParams = [
			"Daydate1" => $data["daydate1"],
			"Daydate2" => $data["daydate2"],
			"Lpu_id" => $data["Lpu_id"]
		];
		$result = $callObject->db->query($query, $queryParams);
		if (is_object($result)) {
			$preres = $result->result("array");
			$preres = $preres[0]["transmit_nmp"];
			$res["transmit_nmp"] = $preres;
		}

		// переданы в НМП
		$query = "
			select count(1) as \"allcall\"
			from v_CmpCallCard CCC
			where CCC.Lpu_id = :Lpu_id
			  and CCC.CmpCallCardStatusType_id is not null
			  and CCC.pmUser_insID > 1
			  and CCC.CmpCallCard_prmDT >= CAST(:Daydate1 as date)
			  and CCC.CmpCallCard_prmDT <= CAST(:Daydate2 as date)
		";
		$queryParams = [
			"Daydate1" => $data["daydate1"],
			"Daydate2" => $data["daydate2"],
			"Lpu_id" => $data["Lpu_id"]
		];
		$result = $callObject->db->query($query, $queryParams);
		if (is_object($result)) {
			$preres = $result->result("array");
			$preres = $preres[0]["allcall"];
			$res["allcall"] = $preres;
		}
		return $res;
	}
}