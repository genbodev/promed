<?php

class Search_model_selectFunc
{
	/**
	 * @param $PS_prefix
	 * @param $PL_prefix
	 * @param $PLS_prefix
	 * @param $data
	 * @return string
	 */
	public static function searchData_SearchFormTypeDbf($PS_prefix, $PL_prefix, $PLS_prefix, $data)
	{
		switch ($data["SearchFormType"]) {
			case "KvsPerson":
				$fld_name = $PS_prefix . ($data["kvs_date_type"] == 2 ? ".PersonEvn_id" : ".Person_id");
				break;
			case "KvsPersonCard":
				$fld_name = "PC.PersonCard_id";
				break;
			case "KvsEvnDiag":
				$fld_name = "EvnDiagPS_id";
				break;
			case "KvsEvnPS":
				$fld_name = "EPS.EvnPS_id";
				break;
			case "KvsEvnLeave":
			case "KvsEvnSection":
				$fld_name = "ESEC.EvnSection_id";
				break;
			case "KvsNarrowBed":
				$fld_name = "ESNB.EvnSectionNarrowBed_id";
				break;
			case "KvsEvnUsluga":
				$fld_name = "EU.EvnUsluga_id";
				break;
			case "KvsEvnUslugaOB":
				$fld_name = "EUOB.EvnUslugaOperBrig_id";
				break;
			case "KvsEvnUslugaAn":
				$fld_name = "EUOA.EvnUslugaOperAnest_id";
				break;
			case "KvsEvnUslugaOsl":
				$fld_name = "EA.EvnAgg_id";
				break;
			case "KvsEvnDrug":
				$fld_name = "ED.EvnDrug_id";
				break;
			case "KvsEvnStick":
				$fld_name = "EST.EvnStick_id";
				break;
			case "EPLPerson":
				$fld_name = $PL_prefix . ($data["epl_date_type"] == 2 ? ".PersonEvn_id" : ".Person_id");
				break;
			case "EPLStomPerson":
				$fld_name = $PLS_prefix . ($data["eplstom_date_type"] == 2 ? ".PersonEvn_id" : ".Person_id");
				break;
			case "EvnPL":
				$fld_name = "EPL.EvnPL_id";
				break;
			case "EvnPLStom":
				$fld_name = "EPLS.EvnPLStom_id";
				break;
			case "EvnVizitPL":
				$fld_name = "EVizitPL.EvnVizitPL_id";
				break;
			case "EvnVizitPLStom":
				$fld_name = "EVPLS.EvnVizitPLStom_id";
				break;
			case "EvnUsluga":
				$fld_name = "EvnUsluga.EvnUsluga_id";
				break;
			case "EvnUslugaStom":
				$fld_name = "EvnUsluga.EvnUslugaStom_id";
				break;
			case "EvnAgg":
			case "EvnAggStom":
				$fld_name = "EvnAgg.EvnAgg_id";
				break;
			default:
				$fld_name = "EPS.EvnPs_id";
		}
		return $fld_name;
	}

	/**
	 * @param Search_model $callObject
	 * @param $prefix
	 * @return string
	 */
	public static function searchData_1(Search_model $callObject, $prefix)
	{
		return "
			{$prefix}.PersonEvn_id as \"PCT_ID\",
			{$prefix}.Person_id as \"P_ID\",
			{$prefix}.Person_SurName as \"SURNAME\",
			{$prefix}.Person_FirName as \"FIRNAME\",
			{$prefix}.Person_SecName as \"SECNAME\",
			trim(coalesce(to_char({$prefix}.Person_BirthDay, '{$callObject->dateTimeForm104}'), '')) as \"BIRTHDAY\",
			{$prefix}.Person_Snils as \"SNILS\",
			PrsIsInv.YesNo_Code as \"INV_N\",
			PrsInvD.Diag_Code as \"INV_DZ\",
			trim(coalesce(to_char(PrsPCh.PersonChild_invDate, '{$callObject->dateTimeForm104}'), '')) as \"INV_DATA\",
			PrsSex.Sex_Name as \"SEX\",
			PrsSoc.SocStatus_Name as \"SOC\",
			PrsOMSST.OMSSprTerr_Code as \"P_TERK\",
			PrsOMSST.OMSSprTerr_Name as \"P_TER\",
			PrsPolTp.PolisType_Name as \"P_NAME\",
			PrsPol.Polis_Ser as \"P_SER\",
			PrsPol.Polis_Num as \"P_NUM\",
			{$prefix}.Person_EdNum as \"P_NUMED\",
			trim(coalesce(to_char(PrsPol.Polis_begDate, '{$callObject->dateTimeForm104}'), '')) as \"P_DATA\",
			PrsOSO.Org_Code as \"SMOK\",
			PrsOS.OrgSmo_Name as \"SMO\",
			0 as \"AR_TP\",
			PrsUA.Address_Zip as \"AR_IDX\",
			PrsUA.KLCountry_Name as \"AR_LND\",
			PrsUA.KLRGN_Name as \"AR_RGN\",
			PrsUA.KLSubRGN_Name as \"AR_RN\",
			PrsUA.KLCity_Name as \"AR_CTY\",
			PrsUA.KLTown_Name as \"AR_NP\",
			PrsUA.KLStreet_Name as \"AR_STR\",
			PrsUA.Address_House as \"AR_DOM\",
			PrsUA.Address_Corpus as \"AR_K\",
			PrsUA.Address_Flat as \"AR_KV\",
			0 as \"AP_TP\",
			PrsPA.Address_Zip as \"AP_IDX\",
			PrsPA.KLCountry_Name as \"AP_LND\",
			PrsPA.KLRGN_Name as \"AP_RGN\",
			PrsPA.KLSubRGN_Name as \"AP_RN\",
			PrsPA.KLCity_Name as \"AP_CTY\",
			PrsPA.KLTown_Name as \"AP_NP\",
			PrsPA.KLStreet_Name as \"AP_STR\",
			PrsPA.Address_House as \"AP_DOM\",
			PrsPA.Address_Corpus as \"AP_K\",
			PrsPA.Address_Flat as \"AP_KV\",
			PrsDocTp.DocumentType_Name as \"D_TIP\",
			PrsDoc.Document_Ser as \"D_SER\",
			PrsDoc.Document_Num as \"D_NOM\",
			PrsOrgD.Org_id as \"D_OUT\",
			trim(coalesce(to_char(PrsDoc.Document_begDate, '{$callObject->dateTimeForm104}'), '')) as \"D_DATA\",
		";
	}

	/**
	 * @param $isPerm
	 * @return string
	 */
	public static function searchData_isBDZ($isPerm)
	{
		return $isPerm
			? "
				case when ps.Server_pid = 0
then
	case when ps.Person_IsInErz = 1 
		then 'blue'
		else case when pls.Polis_endDate is not null and pls.Polis_endDate <= (select dt from mv)
		then
			case when ps.Person_deadDT is not null
			then 'red'
			else 'yellow'
			end
		else 'true'
		end
	end
else 'false'
				end as \"Person_IsBDZ\",
			"
			: "
				case when pls.Polis_endDate is not null and pls.Polis_endDate <= (select dt from mv)
then 'orange'
else case when ps.PersonCloseCause_id = 2 and ps.Person_closeDT is not null
		then 'red'
		else case when ps.Server_pid = 0
				then 'true'
				else 'false'
		end
end
				end as \"Person_IsBDZ\",
			";
	}

	public static function searchData_SearchFormType_CmpCallCard(Search_model $callObject, $isBDZ)
	{
		return "
			'' as \"accessType\",
			CCC.CmpCallCard_id||''||CLC.CmpCloseCard_id as \"CmpCallCard_uid\",
			CCC.CmpCallCard_id as \"CmpCallCard_id\",
			coalesce(CCC.MedPersonal_id, CLC.MedPersonal_id) as \"MedPersonal_id\",
			PS.Person_id as \"Person_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			PS.Server_id as \"Server_id\",
			ET.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
			CLC.EmergencyTeamNum as \"EmergencyTeamNum\",
			LB.LpuBuilding_Name as \"LpuBuilding_Name\",
			to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm104}') as \"CmpCallCard_prmDate\",
			to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm108}') as \"CmpCallCard_prmTime\",
			to_char(CCC.CmpCallCard_Przd, '{$callObject->dateTimeForm108}') as \"CmpCallCard_Przd\",
			CCC.CmpCallCard_Numv||' '||coalesce(CCC.CmpCallCard_NumvPr, '') as \"CmpCallCard_Numv\",
			CCC.CmpCallCardInputType_id as \"CmpCallCardInputType_id\",
			case when coalesce(CLC.CmpCloseCard_id, 0) = 0 then 0 else CLC.CmpCloseCard_id end as \"CmpCloseCard_id\",
			rtrim(coalesce(PS.Person_Surname, CCC.Person_SurName)) as \"Person_Surname\",
			rtrim(coalesce(PS.Person_Firname, CCC.Person_FirName)) as \"Person_Firname\",
			rtrim(coalesce(PS.Person_Secname, CCC.Person_SecName)) as \"Person_Secname\",
			to_char(coalesce(CCC.Person_BirthDay, PS.Person_Birthday), '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			coalesce(dbo.Age2(PS.Person_BirthDay, (select dt from mv)), CCC.Person_Age) as \"Person_Age\",
			case when PS.Person_id is not null and coalesce(PS.Person_IsUnknown, 1) != 2 then 'true' else 'false' end as \"Person_IsIdentified\",
			coalesce(CR.CmpReason_Code||'. ', '')||CR.CmpReason_Name as \"CmpReason_Name\",
			coalesce(CSecondR.CmpReason_Code||'. ', '')||CSecondR.CmpReason_Name as \"CmpSecondReason_Name\",
			case when (CCC.CmpCallCardInputType_id in (1,2)) then coalesce(Lpu.Lpu_Nick, Lpu.Lpu_Name, '') else coalesce(LpuHid.Lpu_Nick, LpuHid.Lpu_Name, '') end as \"CmpLpu_Name\",
			rtrim(case when CLD.diag_FullName is not null then CLD.diag_FullName else CD.CmpDiag_Code end) as \"CmpDiag_Name\",
			rtrim(coalesce(D.Diag_Name, '')) as \"StacDiag_Name\",
			{$isBDZ}
			coalesce(PAddr.Address_Address, rtrim(replace(CCC.CmpCallCard_PCity, '=', ''))||', '||rtrim(CCC.CmpCallCard_PUlic)||', '||rtrim(CCC.CmpCallCard_PDom)||', '||rtrim(CCC.CmpCallCard_PKvar), '') as \"Person_Address\",
			to_char(ccp.CmpCallCardCostPrint_setDT, '{$callObject->dateTimeForm104}') as \"CmpCallCardCostPrint_setDT\",
			case when ccp.CmpCallCardCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ccp.CmpCallCardCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as \"CmpCallCardCostPrint_IsNoPrintText\",
			CCCInput.CmpCallCardInputType_Name as \"CCCInput.CmpCallCardInputType_Name\",
			acceptNmpFlag.CmpCallCardEvent_id as \"acceptNmpFlag.CmpCallCardEvent_id\"
		";
	}

	public static function searchData_SearchFormType_CmpCloseCard(Search_model $callObject, $isBDZ, $data)
	{
		$reasonCase = (in_array($data["session"]["region"]["nick"], ["kz"]))
			? "(coalesce(CRTalon.CmpReason_Code||'. ', '')||CRTalon.CmpReason_Name)"
			: "(case when CLC.CallPovod_id is not null then coalesce(CR.CmpReason_Code||'. ', '')||CR.CmpReason_Name else coalesce(CRTalon.CmpReason_Code||'. ', '')||CRTalon.CmpReason_Name end)";
		return "
			'' as \"accessType\",
			CCC.CmpCallCard_id as \"CmpCallCard_id\",
			CCC.CmpCallCard_id||''||CLC.CmpCloseCard_id as \"CmpCallCard_uid\",
			ETLB.LpuBuilding_id as \"ETLBLpuBuilding_id\",
			coalesce(CCC.MedPersonal_id, CLC.MedPersonal_id) as \"MedPersonal_id\",
			PS.Person_id as \"Person_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			PS.Server_id as \"Server_id\",
			ET.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
			CLC.EmergencyTeamNum as \"EmergencyTeamNum\",
			CCC.CmpCallCardInputType_id as \"CmpCallCardInputType_id\",
			to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm104}') as \"CmpCallCard_prmDate\",
			to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm108}') as \"CmpCallCard_prmTime\",
			to_char(CCC.CmpCallCard_Przd, '{$callObject->dateTimeForm108}') as \"CmpCallCard_Przd\",
			coalesce(CLC.Day_num, CCC.CmpCallCard_Numv)||' '||coalesce(CLC.CmpCloseCard_DayNumPr, '') as \"CmpCallCard_Numv\",
			LB.LpuBuilding_Name as \"LpuBuilding_Name\",
			CLC.CmpCloseCard_id as \"CmpCloseCard_id\",
			rtrim(coalesce(CLC.Fam, PS.Person_Surname, CCC.Person_SurName)) as \"Person_Surname\",
			rtrim(coalesce(CLC.Name, PS.Person_Firname, CCC.Person_FirName)) as \"Person_Firname\",
			rtrim(coalesce(CLC.Middle, PS.Person_Secname, CCC.Person_SecName)) as \"Person_Secname\",
			to_char(coalesce(PSCLC.Person_BirthDay, CCC.Person_BirthDay, PS.Person_BirthDay), '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			coalesce(dbo.Age2(PS.Person_BirthDay, (select dt from mv)), CLC.Age) as \"Person_Age\",
			case when PS.Person_id is not null and coalesce(PS.Person_IsUnknown, 1) != 2 then 'true' else 'false' end as \"Person_IsIdentified\",
			{$reasonCase} as \"CmpReason_Name\",
			case when (CCC.CmpCallCardInputType_id in (1,2)) then coalesce(Lpu.Lpu_Nick, Lpu.Lpu_Name, '') else coalesce(LpuHid.Lpu_Nick, LpuHid.Lpu_Name, '') end as \"CmpLpu_Name\",
			rtrim(case when CLD.diag_FullName is not null then CLD.diag_FullName else CD.CmpDiag_Code end) as \"CmpDiag_Name\",
			rtrim(coalesce(D.Diag_Name, '')) as \"StacDiag_Name\",
			coalesce(PAddr.Address_Address, rtrim(replace(CCC.CmpCallCard_PCity, '=', ''))||', '||rtrim(CCC.CmpCallCard_PUlic)||', '||rtrim(CCC.CmpCallCard_PDom)||', '||rtrim(CCC.CmpCallCard_PKvar), '') as \"Person_Address\",
			{$isBDZ}
			to_char(ccp.CmpCallCardCostPrint_setDT, '{$callObject->dateTimeForm104}') as \"CmpCallCardCostPrint_setDT\",
			case when ccp.CmpCallCardCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ccp.CmpCallCardCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as \"CmpCallCardCostPrint_IsNoPrintText\",
			CCCInput.CmpCallCardInputType_Name as \"CmpCallCardInputType_Name\"
		";
	}

	public static function searchData_SearchFormType_PersonDopDisp(Search_model $callObject, $data)
	{
		$query = "
			DD.PersonDopDisp_id as \"PersonDopDisp_id\",
			PS.Person_id as \"Person_id\",
			coalesce(epldd.Server_id, PS.Server_id) as \"Server_id\",
			coalesce(epldd.PersonEvn_id, PS.PersonEvn_id) as \"PersonEvn_id\",
			Sex.Sex_Name as \"Sex_Name\",
			PS.Polis_Ser as \"Polis_Ser\",
			PS.Polis_Num as \"Polis_Num\",
			okved1.Okved_Name as \"PersonOrg_Okved\",
			org1.Org_OGRN as \"PersonOrg_OGRN\",
			astat1.KLArea_Name as \"Person_KLAreaStat_Name\",
			astat2.KLArea_Name as \"PersonOrg_KLAreaStat_Name\",
			rtrim(addr1.Address_Nick) as \"UAddress_Address\",
			coalesce(rtrim(otherddlpu.Lpu_Nick), '') as \"OnDispInOtherLpu\",
			max(epldd.EvnPLDispDop_id) as \"EvnPLDispDop_id\",
			case when max(epldd.EvnPLDispDop_id) is null then 'false' else 'true' end as \"ExistsDDPL\"
		";
		if (allowPersonEncrypHIV($data["session"])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then trim(PS.Person_SurName) else trim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then trim(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then trim(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
			";
		} else {
			$query .= "
				,rtrim(PS.Person_SurName) as \"Person_Surname\"
				,rtrim(PS.Person_FirName) as \"Person_Firname\"
				,rtrim(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_PersonDispOrp(Search_model $callObject, $data)
	{
		$query = "
			DOr.PersonDispOrp_id as \"PersonDispOrp_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			UAdd.Address_Nick as \"ua_name\",
			PAdd.Address_Nick as \"pa_name\",
			Sex.Sex_Name as \"Sex_Name\",
			PS.Polis_Ser as \"Polis_Ser\",
			PS.Polis_Num as \"Polis_Num\",
			okved1.Okved_Name as \"PersonOrg_Okved\",
			org1.Org_OGRN as \"PersonOrg_OGRN\",
			astat1.KLArea_Name as \"Person_KLAreaStat_Name\",
			astat2.KLArea_Name as \"PersonOrg_KLAreaStat_Name\",
			rtrim(addr1.Address_Nick) as \"UAddress_Address\",
			case when DOr.Org_id IS NOT NULL then 'Да' else 'Нет' end as \"OrgExist\",
			coalesce(rtrim(ODL.Lpu_Nick), '') as \"OnDispInOtherLpu\",
			EPLDO.EvnPLDispOrp_id as \"EvnPLDispOrp_id\",
			case when EPLDO.EvnPLDispOrp_id is null then 'false' else 'true' end as \"ExistsDOPL\"
		";
		if (allowPersonEncrypHIV($data["session"])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
			";
		} else {
			$query .= "
				,rtrim(PS.Person_SurName) as \"Person_Surname\"
				,rtrim(PS.Person_FirName) as \"Person_Firname\"
				,rtrim(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_PersonDispOrpPeriod(Search_model $callObject, $data)
	{
		$query = "
			DOr.PersonDispOrp_id as \"PersonDispOrp_id\",
			DOr.EducationInstitutionType_id as \"EducationInstitutionType_id\",
			PS.Person_id as \"Person_id\",
			DOr.Org_id as \"Org_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			UAdd.Address_Nick as \"ua_name\",
			PAdd.Address_Nick as \"pa_name\",
			Sex.Sex_Name as \"Sex_Name\",
			LATT.Lpu_Nick as \"Lpu_Nick\",
			EIT.EducationInstitutionType_Name as \"EducationInstitutionType_Name\",
			EPLDTI.EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_id\",
			case when EPLDTI.EvnPLDispTeenInspection_id is null then 'false' else 'true' end as \"ExistsDirection\",
			case when EPLDTI.EvnPLDispTeenInspection_id is null then 'false' else 'true' end as \"ExistsDOPL\"
		";
		if (allowPersonEncrypHIV($data["session"])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surnamev\"
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_FirName) else '' end as \"Person_Firnamev\"
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SecName) else '' end as \"Person_Secnamev\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthdayv\"
			";
		} else {
			$query .= "
				,rtrim(PS.Person_SurName) as \"Person_Surname\"
				,rtrim(PS.Person_FirName) as \"Person_Firname\"
				,rtrim(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_PersonDispOrpPred(Search_model $callObject, $data)
	{
		$query = "
			DOr.PersonDispOrp_id as \"PersonDispOrp_id\",
			DOr.EducationInstitutionType_id as \"EducationInstitutionType_id\",
			DOr.Org_id as \"Org_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			Sex.Sex_Name as \"Sex_Name\",
			LATT.Lpu_Nick as \"Lpu_Nick\",
			EIT.EducationInstitutionType_Name as \"EducationInstitutionType_Name\",
			EPLDTI.EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_id\",
			case when EPLDTI.EvnPLDispTeenInspection_id is null then 'false' else 'true' end as \"ExistsDOPL\"
		";
		if (allowPersonEncrypHIV($data["session"])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
			";
		} else {
			$query .= "
				,rtrim(PS.Person_SurName) as \"Person_Surname\"
				,rtrim(PS.Person_FirName) as \"Person_Firname\"
				,rtrim(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_PersonDispOrpProf(Search_model $callObject, $data)
	{
		$query = "
			DOr.PersonDispOrp_id as \"PersonDispOrp_id\",
			DOr.AgeGroupDisp_id as \"AgeGroupDisp_id\",
			DOr.Org_id as \"Org_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			Sex.Sex_Name as \"Sex_Name\",
			AGD.AgeGroupDisp_Name as \"AgeGroupDisp_Name\",
			to_char(DOr.PersonDispOrp_begDate, '{$callObject->dateTimeForm104}') as \"PersonDispOrp_begDate\",
			EPLDTI.EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_id\",
			case when DOr.Org_id is null then 'false' else 'true' end as \"OrgExist\",
			case when EPLDTI.EvnPLDispTeenInspection_id is null then 'false' else 'true' end as \"ExistsDOPL\"
		";
		if (allowPersonEncrypHIV($data['session'])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
			";
		} else {
			$query .= "
				,rtrim(PS.Person_SurName) as \"Person_Surname\"
				,rtrim(PS.Person_FirName) as \"Person_Firname\"
				,rtrim(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_PersonDispOrpOld(Search_model $callObject, $data)
	{
		$query = "
			DOr.PersonDispOrp_id as \"PersonDispOrp_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			Sex.Sex_Name as \"Sex_Name\",
			PS.Polis_Ser as \"Polis_Ser\",
			PS.Polis_Num as \"Polis_Num\",
			okved1.Okved_Name as \"PersonOrg_Okved\",
			org1.Org_OGRN as \"PersonOrg_OGRN\",
			astat1.KLArea_Name as \"Person_KLAreaStat_Name\",
			astat2.KLArea_Name as \"PersonOrg_KLAreaStat_Name\",
			rtrim(addr1.Address_Nick) as \"UAddress_Address\",
			coalesce(rtrim(ODL.Lpu_Nick), '') as \"OnDispInOtherLpu\",
			EPLDO.EvnPLDispOrp_id as \"EvnPLDispOrp_id\",
			CASE WHEN EPLDO.EvnPLDispOrp_id is null THEN 'false' ELSE 'true' END as \"ExistsDOPL\"
		";
		if (allowPersonEncrypHIV($data['session'])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then trim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then trim(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then trim(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
			";
		} else {
			$query .= "
				,rtrim(PS.Person_SurName) as \"Person_Surname\"
				,rtrim(PS.Person_FirName) as \"Person_Firname\"
				,rtrim(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_EvnPLDispDop13(Search_model $callObject, $data)
	{
		$query = "
			coalesce(EPLDD13.EvnPLDispDop13_id, 0)||'_'||coalesce(EPLDD13.Person_id, 0) as \"id\",
			EPLDD13.EvnPLDispDop13_id as \"EvnPLDispDop13_id\",
			coalesce(EPLDD13.EvnPLDispDop13_IsTransit, 1) as \"EvnPLDispDop13_IsTransit\",
			EPLDD13.Person_id as \"Person_id\",
			EPLDD13.Server_id as \"Server_id\",
			EPLDD13.PersonEvn_id as \"PersonEvn_id\",
			rtrim(EPLDD13.Person_Surname) as \"Person_Surname\",
			rtrim(EPLDD13.Person_Firname) as \"Person_Firname\",
			rtrim(EPLDD13.Person_Secname) as \"Person_Secname\",
			to_char(EPLDD13.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			IsFinish.YesNo_Name as \"EvnPLDispDop13_IsEndStage\",
			IsMobile.YesNo_Name as \"EvnPLDispDop13_IsMobile\",
			IsFinishSecond.YesNo_Name as \"EvnPLDispDop13Second_IsEndStage\",
			to_char(EPLDD13EvnPLDispDop13_consDT, '{$callObject->dateTimeForm104}') as \"EvnPLDispDop13_setDate\",
			to_char(EPLDD13EvnPLDispDop13_disDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispDop13_disDate\",
			HK.HealthKind_Name as \"EvnPLDispDop13_HealthKind_Name\",
			case when EPLDD13.EvnPLDispDop13_IsEndStage = 2 and EPLDD13.EvnPLDispDop13_IsTwoStage = 2 then to_char(EPLDD13.EvnPLDispDop13_disDate, '{$callObject->dateTimeForm104}') else null end as \"EvnPLDispDop13Second_napDate\",
			DopDispSecond.HealthKind_Name as \"EvnPLDispDop13Second_HealthKind_Name,
			case when EPLDD13.EvnPLDispDop13_IsRefusal = 2 then to_char(EPLDD13.EvnPLDispDop13_consDT, '{$callObject->dateTimeForm104}') else null end as \"EvnPLDispDop13_rejDate\",
			case
				when EPLDD13AL.EvnPLDispDop13_id is not null then 4
				when EPLDD13.EvnPLDispDop13_id is null then 0
				when EPLDD13.Lpu_id = :Lpu_id then 0
				when EPLDD13.Lpu_id " . getLpuIdFilter($data) . " and coalesce(EPLDD13.EvnPLDispDop13_IsTransit, 1) = 2 then 0
				else 4
			end as \"AccessType_Code\",
			DopDispSecond.EvnPLDispDop13_id as \"EvnPLDispDop13Second_id\",
			to_char(DopDispSecond.EvnPLDispDop13_consDT, '{$callObject->dateTimeForm104}') as \"EvnPLDispDop13Second_setDate\",
			to_char(DopDispSecond.EvnPLDispDop13_disDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispDop13Second_disDate\",
			case when DDICDataSecond.DopDispInfoConsent_IsAgree = 1 then to_char(DopDispSecond.EvnPLDispDop13_consDT, '{$callObject->dateTimeForm104}') else null end as \"EvnPLDispDop13Second_rejDate\",
			to_char(ecp.EvnCostPrint_setDT, '{$callObject->dateTimeForm104}') as \"EvnCostPrint_setDT\",
			case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as \"EvnCostPrint_IsNoPrintText\"
		";
		if (in_array($data["session"]["region"]["nick"], ["ufa"])) {
			$query .= ",lpu.Lpu_Nick";
		}
		if (in_array($data["session"]["region"]["nick"], ["buryatiya"])) {
			$query .= ",coalesce(UC.UslugaComplex_Code||'. ', '')||UC.UslugaComplex_Name as \"UslugaComplex_Name\"";
		}
		$query .= "
			,coalesce(UAdd.Address_Nick, UAdd.Address_Address) as \"ua_name\"
			,coalesce(PAdd.Address_Nick, PAdd.Address_Address) as \"pa_name\"
		";
		return $query;
	}

	public static function searchData_SearchFormType_EvnPLDispDop13Sec(Search_model $callObject, $data)
	{
		$query = "
			coalesce(EPLDD13.EvnPLDispDop13_id, 0)||'_'||coalesce(PS.Person_id, 0) as \"id\",
			EPLDD13.EvnPLDispDop13_id as \"EvnPLDispDop13_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			EPLDD13.PersonEvn_id as \"PersonEvn_id\",
			EPLDD13.PayType_id as \"PayType_id\"\",
			IsFinish.YesNo_Name as \"EvnPLDispDop13_IsEndStage\",
			IsMobile.YesNo_Name as \"EvnPLDispDop13_IsMobile\",
			IsFinishSecond.YesNo_Name as \"EvnPLDispDop13Second_IsEndStage\",
			to_char(EPLDD13.EvnPLDispDop13_consDT, '{$callObject->dateTimeForm104}') as \"EvnPLDispDop13_setDate\",
			to_char(EPLDD13.EvnPLDispDop13_disDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispDop13_disDate\",
			HK.HealthKind_Name as \"EvnPLDispDop13_HealthKind_Name\",
			case when EPLDD13.EvnPLDispDop13_IsEndStage = 2 and EPLDD13.EvnPLDispDop13_IsTwoStage = 2 then to_char(EPLDD13.EvnPLDispDop13_disDate, '{$callObject->dateTimeForm104}') else null end as \"EvnPLDispDop13Second_napDate\",
			HK_SEC.HealthKind_Name as \"EvnPLDispDop13Second_HealthKind_Name\",
			case when DDICData.DopDispInfoConsent_IsAgree = 1 then to_char(EPLDD13.EvnPLDispDop13_consDT, '{$callObject->dateTimeForm104}') else null end as \"EvnPLDispDop13_rejDate\",
			case
				when EPLDD13AL.EvnPLDispDop13_id is not null then 4
				when DopDispSecond.EvnPLDispDop13_id is null then 0
				when EPLDD13.Lpu_id = :Lpu_id then 0
				when DopDispSecond.Lpu_id = :Lpu_id then 0
				when DopDispSecond.Lpu_id " . getLpuIdFilter($data) . " and coalesce(DopDispSecond.EvnPLDispDop13_IsTransit, 1) = 2 then 0
				else 4
			end as \"AccessType_Code\",
			DopDispSecond.EvnPLDispDop13_id as \"EvnPLDispDop13Second_id\",
			to_char(DOCOSMDT.EvnUslugaDispDop_disDate, '{$callObject->dateTimeForm104}') as \"VopOsm_EvnUslugaDispDop_disDate\", -- дата осмотра врача-терапевта на первом этапе
			coalesce(DopDispSecond.EvnPLDispDop13_IsTransit, 1) as \"EvnPLDispDop13Second_IsTransit\",
			to_char(DopDispSecond.EvnPLDispDop13_consDT, '{$callObject->dateTimeForm104}') as \"EvnPLDispDop13Second_setDate\",
			to_char(DopDispSecond.EvnPLDispDop13_disDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispDop13Second_disDate\",
			case when DDICDataSecond.DopDispInfoConsent_IsAgree = 1 then to_char(DopDispSecond.EvnPLDispDop13_consDT, '{$callObject->dateTimeForm104}') else null end as \"EvnPLDispDop13Second_rejDate\",
			to_char(ecp.EvnCostPrint_setDT, '{$callObject->dateTimeForm104}') as \"EvnCostPrint_setDT\",
			case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as \"EvnCostPrint_IsNoPrintText\"
		";
		if (in_array($data["session"]["region"]["nick"], ["ufa"])) {
			$query .= ",lpu.Lpu_Nick as \"Lpu_Nick\"";
		}
		if (in_array($data["session"]["region"]["nick"], ["buryatiya"])) {
			$query .= ",coalesce(UC.UslugaComplex_Code||'. ', '')||UC.UslugaComplex_Name as \"UslugaComplex_Name\"";
		}
		$query .= "
			,coalesce(UAdd.Address_Nick, UAdd.Address_Address) as \"ua_name\"
			,coalesce(PAdd.Address_Nick, PAdd.Address_Address) as \"pa_name\"
		";
		if (allowPersonEncrypHIV($data['session'])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
			";
		} else {
			$query .= "
				,rtrim(PS.Person_SurName) as \"Person_Surname\"
				,rtrim(PS.Person_FirName) as \"Person_Firname\"
				,rtrim(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_EvnPLDispProf(Search_model $callObject, $data)
	{
		$query = "
			EPLDP.EvnPLDispProf_id as \"EvnPLDispProf_id\",
			coalesce(EPLDP.EvnPLDispProf_IsTransit, 1) as \"EvnPLDispProf_IsTransit\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			EPLDP.PersonEvn_id as \"PersonEvn_id\",
			coalesce(UAdd.Address_Nick, UAdd.Address_Address) as \"ua_name\",
			coalesce(PAdd.Address_Nick, PAdd.Address_Address) as \"pa_name\",
			IsFinish.YesNo_Name as \"EvnPLDispProf_IsEndStage\",
			to_char(EPLDP.EvnPLDispProf_consDT, '{$callObject->dateTimeForm104}') as \"EvnPLDispProf_setDate\",
			to_char(EPLDP.EvnPLDispProf_disDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispProf_disDate\",
			HK.HealthKind_Name as \"EvnPLDispProf_HealthKind_Name\",
			case when EPLDP.EvnPLDispProf_IsRefusal = 2 then to_char(EPLDP.EvnPLDispProf_consDT, '{$callObject->dateTimeForm104}') else null end as \"EvnPLDispProf_rejDate\",
			case
				when exists (select EvnPLDispProf_id from v_EvnPLDispProf where Person_id = PS.Person_id and date_part('year', EvnPLDispProf_setDate) = :PersonDopDisp_Year and Lpu_id " . getLpuIdFilter($data, true) . " limit 1) then 4
				when EPLDP.EvnPLDispProf_id is null then 0
				when EPLDP.Lpu_id = :Lpu_id then 0
				when EPLDP.Lpu_id " . getLpuIdFilter($data) . " and coalesce(EPLDP.EvnPLDispProf_IsTransit, 1) = 2 then 0
				else 4
			end as \"AccessType_Code\",
			to_char(ecp.EvnCostPrint_setDT, '{$callObject->dateTimeForm104}') as \"EvnCostPrint_setDT\",
			case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as \"EvnCostPrint_IsNoPrintText\"
		";
		if (allowPersonEncrypHIV($data['session'])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
			";
		} else {
			$query .= "
				,rtrim(PS.Person_SurName) as \"Person_Surname\"
				,rtrim(PS.Person_FirName) as \"Person_Firname\"
				,rtrim(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_EvnPLDispScreen(Search_model $callObject, $data)
	{
		$query = "
			EPLDS.EvnPLDispScreen_id as \"EvnPLDispScreen_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			EPLDS.PersonEvn_id as \"PersonEvn_id\",
			Sex.Sex_Name as \"Sex_Name\",
			AGD.AgeGroupDisp_Name as \"Sex_Name\",
			to_char(EPLDS.EvnPLDispScreen_setDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispScreen_setDate\",
			to_char(EPLDS.EvnPLDispScreen_disDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispScreen_disDate\",
			IsFinish.YesNo_Name as \"EvnPLDispScreen_IsEndStage\"
		";
		if (allowPersonEncrypHIV($data['session'])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
			";
		} else {
			$query .= "
				,rtrim(PS.Person_SurName) as \"Person_Surname\"
				,rtrim(PS.Person_FirName) as \"Person_Firname\"
				,rtrim(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_EvnPLDispScreenChild(Search_model $callObject, $data)
	{
		$query = "
			EPLDS.EvnPLDispScreenChild_id as \"EvnPLDispScreenChild_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			EPLDS.PersonEvn_id as \"PersonEvn_id\",
			Sex.Sex_Name as \"Sex_Name\",
			AGD.AgeGroupDisp_Name as \"AgeGroupDisp_Name\",
			to_char(EPLDS.EvnPLDispScreenChild_setDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispScreenChild_setDate\",
			to_char(EPLDS.EvnPLDispScreenChild_disDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispScreenChild_disDate\",
			IsFinish.YesNo_Name as \"EvnPLDispScreenChild_IsEndStage\"
		";
		if (allowPersonEncrypHIV($data['session'])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
			";
		} else {
			$query .= "
				,rtrim(PS.Person_SurName) as \"Person_Surname\"
				,rtrim(PS.Person_FirName) as \"Person_Firname\"
				,rtrim(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
			";
		}
		//$query .= " ,CASE WHEN sllerr.ServiceListDetailLog_id is not null THEN 'true' ELSE 'false' END as \"AisError\"";
		return $query;
	}

	public static function searchData_SearchFormType_EvnPLDispDop(Search_model $callObject, $data)
	{
		$query = "
			EPLDD.EvnPLDispDop_id as \"EvnPLDispDop_id\",
			EPLDD.Person_id as \"Person_id\",
			EPLDD.Server_id as \"Server_id\",
			EPLDD.PersonEvn_id as \"PersonEvn_id\",
			EPLDD.EvnPLDispDop_VizitCount as \"EvnPLDispDop_VizitCount\",
			IsFinish.YesNo_Name as \"EvnPLDispDop_IsFinish\",
			to_char(EPLDD.EvnPLDispDop_setDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispDop_setDate\",
			to_char(EPLDD.EvnPLDispDop_disDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispDop_disDate\"
		";
		if (allowPersonEncrypHIV($data['session'])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
			";
		} else {
			$query .= "
				,rtrim(PS.Person_SurName) as \"Person_Surname\"
				,rtrim(PS.Person_FirName) as \"Person_Firname\"
				,rtrim(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_EvnPLDispTeen14(Search_model $callObject, $data)
	{
		$query = "
			EPLDT14.EvnPLDispTeen14_id as \"EvnPLDispTeen14_id\",
			EPLDT14.Person_id as \"Person_id\",
			EPLDT14.Server_id as \"Server_id\",
			EPLDT14.PersonEvn_id as \"PersonEvn_id\",
			EPLDT14.EvnPLDispTeen14_VizitCount as \"EvnPLDispTeen14_VizitCount\",
			IsFinish.YesNo_Name as \"EvnPLDispTeen14_IsFinish\",
			to_char(EPLDT14.EvnPLDispTeen14_setDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispTeen14_setDate\",
			to_char(EPLDT14.EvnPLDispTeen14_disDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispTeen14_disDate\"
		";
		if (allowPersonEncrypHIV($data["session"])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
			";
		} else {
			$query .= "
				,RTRIM(PS.Person_SurName) as \"Person_Surname\"
				,RTRIM(PS.Person_FirName) as \"Person_Firname\"
				,RTRIM(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_EvnPLDispOrp(Search_model $callObject, $data)
	{
		$query = "
			EPLDO.EvnPLDispOrp_id as \"EvnPLDispOrp_id\",
			coalesce(EPLDO.EvnPLDispOrp_IsTransit, 1) as \"EvnPLDispOrp_IsTransit\",
			EPLDO.Person_id as \"Person_id\",
			EPLDO.Server_id as \"Server_id\",
			EPLDO.PersonEvn_id as \"PersonEvn_id\",
			Sex.Sex_Name as \"Sex_Name\",
			EPLDO.EvnPLDispOrp_VizitCount as \"EvnPLDispOrp_VizitCount\",
			EPLDO.DispClass_id as \"DispClass_id\",
			IsFinish.YesNo_Name as \"EvnPLDispOrp_IsFinish\",
			IsTwoStage.YesNo_Name as \"EvnPLDispOrp_IsTwoStage\",
			HK.HealthKind_Name as \"EvnPLDispOrp_HealthKind_Name\",
			to_char(EPLDO.EvnPLDispOrp_setDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispOrp_setDate\",
			to_char(EPLDO.EvnPLDispOrp_disDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispOrp_disDate\",
			to_char(ecp.EvnCostPrint_setDT, '{$callObject->dateTimeForm104}') as \"EvnCostPrint_setDT\",
			case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as \"EvnCostPrint_IsNoPrintText\"
		";
		if (in_array($data['session']['region']['nick'], array('buryatiya', 'krym'))) {
			$query .= ",coalesce(UC.UslugaComplex_Code || '. ','') || UC.UslugaComplex_Name as \"UslugaComplex_Name\"";
		}
		if (allowPersonEncrypHIV($data['session'])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
			";
		} else {
			$query .= "
				,RTRIM(PS.Person_SurName) as \"Person_Surname\"
				,RTRIM(PS.Person_FirName) as \"Person_Firname\"
				,RTRIM(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_EvnPLDispOrpOld(Search_model $callObject, $data)
	{
		$query = "
			EPLDO.EvnPLDispOrp_id as \"EvnPLDispOrp_id\",
			EPLDO.Person_id as \"Person_id\",
			EPLDO.Server_id as \"Server_id\",
			EPLDO.PersonEvn_id as \"PersonEvn_id\",
			EPLDO.EvnPLDispOrp_VizitCount as \"EvnPLDispOrp_VizitCount\",
			IsFinish.YesNo_Name as \"EvnPLDispOrp_IsFinish\",
			to_char(EPLDO.EvnPLDispOrp_setDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispOrp_setDate\",
			to_char(EPLDO.EvnPLDispOrp_disDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispOrp_disDate\"
		";
		if (allowPersonEncrypHIV($data['session'])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
			";
		} else {
			$query .= "
				,RTRIM(PS.Person_SurName) as \"Person_Surname\"
				,RTRIM(PS.Person_FirName) as \"Person_Firname\"
				,RTRIM(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_EvnPLDispOrpSec(Search_model $callObject, $data)
	{
		$query = "
			EPLDO.EvnPLDispOrp_id as \"EvnPLDispOrp_id\",
			EPLDO.Person_id as \"Person_id\",
			EPLDO.Server_id as \"Server_id\",
			EPLDO.PersonEvn_id as \"PersonEvn_id\",
			Sex.Sex_Name as \"Sex_Name\",
			EPLDO.EvnPLDispOrp_VizitCount as \"EvnPLDispOrp_VizitCount\",
			EPLDO.DispClass_id as \"DispClass_id\",
			IsFinish.YesNo_Name as \"EvnPLDispOrp_IsFinish\",
			HK.HealthKind_Name as \"EvnPLDispOrp_HealthKind_Name\",
			IsTwoStage.YesNo_Name as \"EvnPLDispOrp_IsTwoStage\",
			to_char(EPLDO.EvnPLDispOrp_setDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispOrp_setDate\",
			to_char(EPLDO.EvnPLDispOrp_disDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispOrp_disDate\",
			to_char(ecp.EvnCostPrint_setDT, '{$callObject->dateTimeForm104}') as \"EvnCostPrint_setDT\",
			case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as \"EvnCostPrint_IsNoPrintText\"
		";
		if (in_array($data["session"]["region"]["nick"], ["buryatiya", "krym"])) {
			$query .= ",coalesce(UC.UslugaComplex_Code || '. ','') || UC.UslugaComplex_Name as \"UslugaComplex_Name\"";
		}
		if (allowPersonEncrypHIV($data["session"])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
			";
		} else {
			$query .= "
				,RTRIM(PS.Person_SurName) as \"Person_Surname\"
				,RTRIM(PS.Person_FirName) as \"Person_Firname\"
				,RTRIM(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_EvnPLDispTeenInspectionPred(Search_model $callObject, $data)
	{
		$query = "
			EPLDTI.EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_id\",
			EPLDTI.EvnPLDispTeenInspection_fid as \"EvnPLDispTeenInspection_fid\",
			HGT.HealthGroupType_Name as \"HealthGroupType_Name\",
			coalesce(EPLDTI.EvnPLDispTeenInspection_IsTransit, 1) as \"EvnPLDispTeenInspection_IsTransit\",
			EPLDTI.Person_id as \"Person_id\",
			EPLDTI.Server_id as \"Server_id\",
			EPLDTI.PersonEvn_id as \"PersonEvn_id\",
			coalesce(UAdd.Address_Nick, UAdd.Address_Address) as \"ua_name\",
			coalesce(PAdd.Address_Nick, PAdd.Address_Address) as \"pa_name\",
			Sex.Sex_Name as \"Sex_Name\",
			AGD.AgeGroupDisp_Name as \"AgeGroupDisp_Name\",
			case when coalesce(EPLDTI.Org_id,PDORP.Org_id) IS NOT NULL then 'true' else 'false' end as \"OrgExist\",
			EPLDTI.EvnPLDispTeenInspection_VizitCount as \"EvnPLDispTeenInspection_VizitCount\",
			IsFinish.YesNo_Name as \"EvnPLDispTeenInspection_IsFinish\",
			HK.HealthKind_Name as \"EvnPLDispTeenInspection_HealthKind_Name\",
			IsTwoStage.YesNo_Name as \"EvnPLDispTeenInspection_IsTwoStage\",
			case when PDORP.PersonDispOrp_id is not null then 'true' else 'false' end as \"EvnPLDispTeenInspection_hasDirection\",
			to_char(EPLDTI.EvnPLDispTeenInspection_setDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispTeenInspection_setDate\",
			to_char(EPLDTI.EvnPLDispTeenInspection_disDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispTeenInspection_disDate\",
			to_char(ecp.EvnCostPrint_setDT, '{$callObject->dateTimeForm104}') as \"EvnCostPrint_setDT\",
			case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as \"EvnCostPrint_IsNoPrintText\"
		";
		if (in_array($data["session"]["region"]["nick"], ["buryatiya", "krym"])) {
			$query .= ",coalesce(UC.UslugaComplex_Code || '. ','') || UC.UslugaComplex_Name as \"UslugaComplex_Name\"";
		}
		if (allowPersonEncrypHIV($data["session"])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
			";
		} else {
			$query .= "
				,RTRIM(PS.Person_SurName) as \"Person_Surname\"
				,RTRIM(PS.Person_FirName) as \"Person_Firname\"
				,RTRIM(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_EvnPLDispDopStream(Search_model $callObject)
	{
		return "
			EPLDD.EvnPLDispDop_id as \"EvnPLDispDop_id\",
			EPLDD.Person_id as \"Person_id\",
			EPLDD.Server_id as \"Server_id\",
			EPLDD.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(PS.Person_Surname) as \"Person_Surname\",
			RTRIM(PS.Person_Firname) as \"Person_Firname\",
			RTRIM(PS.Person_Secname) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			EPLDD.EvnPLDispDop_VizitCount as \"EvnPLDispDop_VizitCount\",
			IsFinish.YesNo_Name as \"EvnPLDispDop_IsFinish\",
			to_char(EPLDD.EvnPLDispDop_setDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispDop_setDate\",
			to_char(EPLDD.EvnPLDispDop_disDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispDop_disDate\"
		";
	}

	public static function searchData_SearchFormType_EvnPLDispTeen14Stream(Search_model $callObject)
	{
		return "
			EPLDT14.EvnPLDispTeen14_id as \"EvnPLDispTeen14_id\",
			EPLDT14.Person_id as \"Person_id\",
			EPLDT14.Server_id as \"Server_id\",
			EPLDT14.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(PS.Person_Surname) as \"Person_Surname\",
			RTRIM(PS.Person_Firname) as \"Person_Firname\",
			RTRIM(PS.Person_Secname) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			EPLDT14.EvnPLDispTeen14_VizitCount as \"EvnPLDispTeen14_VizitCount\",
			IsFinish.YesNo_Name as \"EvnPLDispTeen14_IsFinish\",
			to_char(EPLDT14.EvnPLDispTeen14_setDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispTeen14_setDate\",
			to_char(EPLDT14.EvnPLDispTeen14_disDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispTeen14_disDate\"
		";
	}

	public static function searchData_SearchFormType_EvnPLDispOrpStream(Search_model $callObject)
	{
		return "
			EPLDO.EvnPLDispOrp_id as \"EvnPLDispOrp_id\",
			EPLDO.Person_id as \"Person_id\",
			EPLDO.Server_id as \"Server_id\",
			EPLDO.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(PS.Person_Surname) as \"Person_Surname\",
			RTRIM(PS.Person_Firname) as \"Person_Firname\",
			RTRIM(PS.Person_Secname) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			EPLDO.EvnPLDispOrp_VizitCount as \"EvnPLDispOrp_VizitCount\",
			EPLDO.DispClass_id as \"DispClass_id\",
			IsFinish.YesNo_Name as \"EvnPLDispOrp_IsFinish\",
			IsTwoStage.YesNo_Name as \"EvnPLDispOrp_IsTwoStage\",
			to_char(EPLDO.EvnPLDispOrp_setDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispOrp_setDate\",
			to_char(EPLDO.EvnPLDispOrp_disDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispOrp_disDate\"
		";
	}

	public static function searchData_SearchFormType_EvnPLDispMigrant(Search_model $callObject, $data)
	{
		$query = "
			EPLDM.EvnPLDispMigrant_id as \"EvnPLDispMigrant_id\",
			EPLDM.Person_id as \"Person_id\",
			EPLDM.Server_id as \"Server_id\",
			EPLDM.PersonEvn_id as \"PersonEvn_id\",
			to_char(EPLDM.EvnPLDispMigrant_setDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispMigrant_setDate\",
			case when EPLDM.ResultDispMigrant_id is not null then to_char(EVDD.EvnVizitDispDop_setDate, '{$callObject->dateTimeForm104}') else null end as \"EvnPLDispMigrant_disDate\",
			UA.Address_Nick as \"PersonUAddress\",
			PA.Address_Nick as \"PersonPAddress\",
			RDM.ResultDispMigrant_Name as \"ResultDispMigrant_Name\"
		";
		if (allowPersonEncrypHIV($data['session'])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
			";
		} else {
			$query .= "
				,RTRIM(PS.Person_SurName) as \"Person_Surname\"
				,RTRIM(PS.Person_FirName) as \"Person_Firname\"
				,RTRIM(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_EvnPLDispDriver(Search_model $callObject, $data)
	{
		$query = "
			EPLDD.EvnPLDispDriver_id as \"EvnPLDispDriver_id\",
			EPLDD.Person_id as \"Person_id\",
			EPLDD.Server_id as \"Server_id\",
			EPLDD.PersonEvn_id as \"PersonEvn_id\",
			EPLDD.EvnPLDispDriver_IsSigned as \"EvnPLDispDriver_IsSigned\",
			to_char(EPLDD.EvnPLDispDriver_signDT, '{$callObject->dateTimeForm104}') as \"EvnPLDispDriver_signDT\",
			to_char(EPLDD.EvnPLDispDriver_setDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispDriver_setDate\",
			to_char(EVDD.EvnVizitDispDop_setDate, '{$callObject->dateTimeForm104}') as \"EvnPLDispDriver_disDate\",
			UA.Address_Nick as \"PersonUAddress\",
			PA.Address_Nick as \"PersonPAddress\",
			RDD.ResultDispDriver_Name as \"ResultDispDriver_Name\"
		";
		if (allowPersonEncrypHIV($data['session'])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
			";
		} else {
			$query .= "
				,RTRIM(PS.Person_SurName) as \"Person_Surname\"
				,RTRIM(PS.Person_FirName) as \"Person_Firname\"
				,RTRIM(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_EvnUsluga(Search_model $callObject, $data)
	{
		$add_pu = ($callObject->getRegionNick() != "kareliya")
			? ""
			: (isset($data["and_eplperson"]) && $data["and_eplperson"]
				? ""
				: "PS.PersonEvn_id as \"PCT_ID\",");
		return "
			EPL.EvnPL_id as \"EPL_ID\",
			{$add_pu}
			EvnUsluga.EvnUsluga_pid as \"EPZ_ID\",
			EvnUsluga.EvnUsluga_id as \"EUS_ID\",
			EvnUsluga.EvnClass_SysNick as \"EU_CLASS\",
			RTRIM(coalesce(to_char(EvnUsluga.EvnUsluga_setDT, '{$callObject->dateTimeForm104}'), '')) as \"SETDATE\",
			EvnUsluga.EvnUsluga_setTime as \"SETTIME\",
			dbfusluga.UslugaComplex_Code as \"USL_CODE\",
			EvnUsluga.EvnUsluga_Kolvo as \"KOLVO\",
			dbfup.UslugaPlace_Code as \"UP_CODE\",
			dbfmp.MedPersonal_TabCode as \"MP_CODE\",
			dbfpt.PayType_Code as \"PT_CODE\"
		";
	}

	public static function searchData_SearchFormType_EvnUslugaStom(Search_model $callObject, $data)
	{
		$code = (isset($data["and_eplstomperson"]) && $data["and_eplstomperson"])
			? ""
			: "PS.PersonEvn_id as \"PCT_ID\",";
		return "
			EPLS.EvnPLStom_id as \"EPL_ID\",
			{$code}
			EvnUsluga.EvnUslugaStom_pid as \"EPZ_ID\",
			EvnUsluga.EvnUslugaStom_id as \"EUS_ID\",
			(select EvnClass_SysNick from v_EvnClass where EvnClass_id = EvnUsluga.EvnClass_id ) as \"EU_CLASS\",
			RTRIM(coalesce(to_char(EvnUsluga.EvnUslugaStom_setDT, '{$callObject->dateTimeForm104}'), '')) as \"SETDATE\",
			EvnUsluga.EvnUslugaStom_setTime as \"SETTIME\",
			dbfusluga.UslugaComplex_Code as \"USL_CODE\",
			EvnUsluga.EvnUslugaStom_Kolvo as \"KOLVO\",
			dbfup.UslugaPlace_Code as \"UP_CODE\",
			dbfmp.MedPersonal_TabCode as \"MP_CODE\",
			dbfpt.PayType_Code as \"PT_CODE\"
		";
	}

	public static function searchData_SearchFormType_EvnSection(Search_model $callObject, $data, $dbf, $isBDZ, &$filter, &$queryParams)
	{
		if ($dbf === true) {
			$query = "
				ESEC.EvnSection_id as \"ESEC_COD\",
				ESEC.EvnSection_pid as \"EPS_COD\",
				RTRIM(coalesce(to_char(ESEC.EvnSection_setDate, '{$callObject->dateTimeForm104}'), '')) as \"SETDATE\",
				ESEC.EvnSection_setTime as \"SETTIME\",
				RTRIM(coalesce(to_char(ESEC.EvnSection_disDate, '{$callObject->dateTimeForm104}'), '')) as \"DISDATE\",
				ESEC.EvnSection_disTime as \"DISTIME\",
				dbfsec.LpuSection_Code as \"LS_COD\",
				dbfpay.PayType_Code as \"PAY_COD\",
				dbftar.TariffClass_Code as \"TFCLS_COD\",
				dbfmp.MedPersonal_TabCode as \"MP_COD\"
			";
		} else {
			$ksg_query = " ,coalesce(ksgkpg.Mes_Code, '')||' '||coalesce(ksgkpg.Mes_Name, '') as \"EvnSection_KSG\"";
			$kpg_query = " ,kpg.Mes_Code as \"EvnSection_KPG\"";
			if ($callObject->getRegionNick() == "ekb") {
				$ksg_query = " ,coalesce(sksg.Mes_Code, '')||' '||coalesce(sksg.Mes_Name, '') as \"EvnSection_KSG\"";
			}
			if ($callObject->getRegionNick() == "kareliya") {
				$ksg_query = "
,case when ksgkpg.Mes_id = ksg.Mes_id
	then coalesce(ksgkpg.Mes_Code, '')||' '||coalesce(ksgkpg.Mes_Name, '')
end as \"EvnSection_KSG\"
				";
				$kpg_query = "
,case when ksgkpg.Mes_id = kpg.Mes_id 
	then coalesce(ksgkpg.Mes_Code, '')||' '||coalesce(ksgkpg.Mes_Name, '')
end as \"EvnSection_KPG\"
				";
			}
			$query = "
				 ESEC.EvnSection_id as \"EvnSection_id\"
				,ESEC.EvnSection_pid as \"EvnSection_pid\"
				,EPS.Person_id as \"Person_id\"
				,EPS.PersonEvn_id as \"PersonEvn_id\"
				,EPS.Server_id as \"Server_id\"
				,EPS.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\"
				,RTRIM(coalesce(EPS.EvnPS_NumCard, '')) as \"EvnPS_NumCard\"
				,coalesce(LS.LpuSection_Name, '') as \"LpuSection_Name\"
				,coalesce(LSW.LpuSectionWard_Name, '') as \"LpuSectionWard_Name\"
				,coalesce(Dtmp.Diag_FullName, '') as \"Diag_Name\"
				,to_char(ESEC.EvnSection_setDate, '{$callObject->dateTimeForm104}') as \"EvnSection_setDate\"
				,to_char(ESEC.EvnSection_disDate, '{$callObject->dateTimeForm104}') as \"EvnSection_disDate\"
				,coalesce(LT.LeaveType_Name, '') as \"LeaveType_Name\"
				,PT.PayType_Name as \"PayType_Name\"
				,RTRIM(coalesce(MP.Person_Fio, '')) as \"MedPersonal_Fio\"
				,case when ESEC.EvnSection_disDate is not null
then
	case
		when LUT.LpuUnitType_Code = 2 and date_part('day', ESEC.EvnSection_disDate - ESEC.EvnSection_setDate) + 1 > 1
		then date_part('day', ESEC.EvnSection_disDate - ESEC.EvnSection_setDate)
		else date_part('day', ESEC.EvnSection_disDate - ESEC.EvnSection_setDate) + 1
	end
else null
				  end as \"EvnSection_KoikoDni\"
				 ,MES.Mes_Code as \"Mes_Code\"
				 ,{$isBDZ}
				 CASE WHEN ESEC.EvnSection_Index = ESEC.EvnSection_Count-1 THEN 1 ELSE 0 END as \"EvnSection_isLast\"
				{$ksg_query}
				{$kpg_query}
				,case when ESEC.EvnSection_IsAdultEscort = 2 then 'Да' else 'Нет' end as \"EvnSection_IsAdultEscort\"
				,coalesce(ksgkpg.Mes_Code, '')||' '||coalesce(ksgkpg.Mes_Name, '') as \"EvnSection_KSGKPG\"
			";
			if (allowPersonEncrypHIV($data["session"])) {
				$query .= "
,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as \"Person_Firname\"
,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as \"Person_Secname\"
,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_DeadDT, '{$callObject->dateTimeForm104}') else null end as \"Person_deadDT\"
				";
			} else {
				$query .= "
,RTRIM(PS.Person_SurName) as \"Person_Surname\"
,RTRIM(PS.Person_FirName) as \"Person_Firname\"
,RTRIM(PS.Person_SecName) as \"Person_Secname\"
,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
,to_char(PS.Person_DeadDT, '{$callObject->dateTimeForm104}') as \"Person_deadDT\"
				";
			}
			$query .= ",case when SES1.ServiceEvnStatus_SysNick in ('sendegis','loadegis') then 'true' else 'false' end as \"fedservice_iemk\"";
			if (isset($data["Service1EvnStatus_id"])) {
				$filter .= " and SES1.ServiceEvnStatus_id = :Service1EvnStatus_id ";
				$queryParams["Service1EvnStatus_id"] = $data["Service1EvnStatus_id"];
			}
		}
		return $query;
	}

	public static function searchData_SearchFormType_EvnDiag(Search_model $callObject)
	{
		return "
			EDPS.EvnDiagPS_id as \"EPSDZ_COD\",
			EDPS.EvnDiagPS_pid as \"EVN_COD\",
			RTRIM(coalesce(to_char(EDPS.EvnDiagPS_setDate, '{$callObject->dateTimeForm104}'), '')) as \"SETDATE\",
			EDPS.EvnDiagPS_setTime as \"SETTIME\",
			EDPS.Diag_id as \"DZ_COD\",
			EDPS.DiagSetClass_id as \"DZCLS_COD\",
			EDPS.DiagSetType_id as \"DZTYP_COD\",
			dbfmes.Mes_id as \"MES_COD\"
		";
	}

	public static function searchData_SearchFormType_EvnLeave()
	{
		return "
			COALESCE(ELV.EvnLeave_id, dbfeol.EvnOtherLpu_id, dbfed.EvnDie_id, dbfeost.EvnOtherStac_id, dbfeos.EvnOtherSection_id, dbfeosbp.EvnOtherSectionBedProfile_id) as \"ELV_COD\",
			EPS.EvnPS_id as \"EPS_COD\",
			EPS.LeaveType_id as \"LVTYP_COD\",
			coalesce(ELV.LeaveCause_id, coalesce(dbfeol.LeaveCause_id, coalesce(dbfeost.LeaveCause_id, dbfeos.LeaveCause_id))) as \"LVCS_COD\",
			coalesce(ELV.ResultDesease_id, coalesce(dbfeol.ResultDesease_id, coalesce(dbfeost.ResultDesease_id, dbfeos.ResultDesease_id))) as \"RSTDSS_COD\",
			ELV.EvnLeave_IsAmbul as \"ISAMBUL\",
			COALESCE(ELV.EvnLeave_UKL, dbfeol.EvnOtherLpu_UKL, dbfed.EvnDie_UKL, dbfeost.EvnOtherStac_UKL, dbfeos.EvnOtherSection_UKL, dbfeosbp.EvnOtherSectionBedProfile_UKL) as \"UKL\",
			dbflu.LpuUnitType_id as \"LSTYP_COD\",
			dbfeost.LeaveCause_id as \"OPSCS_COD\",
			dbfls.LpuSection_id as \"LS_COD\",
			dbfeol.Lpu_id as \"OLPU_OGRN\",
			dbfeol.LeaveCause_id as \"OLPUCS_COD\",
			dbfeos.LeaveCause_id as \"OLSCS_COD\",
			dbfed.MedPersonal_id as \"MP_COD\",
			dbfed.IsWait as \"ISWAIT\",
			dbfed.EvnDie_IsAnatom as \"ISANATOM\",
			null as \"ISBIRTH\",
			dbfed.MedPersonal_aid as \"AMP_COD\",
			dbfed.AnatomWhere_id as \"ANWHR_COD\"
		";
	}

	public static function searchData_SearchFormType_EvnStick(Search_model $callObject)
	{
		return "
			EST.EvnStick_id as \"EST_COD\",
			EST.EvnStick_pid as \"EPS_COD\",
			RTRIM(coalesce(to_char(EPS.EvnPS_setDate, '{$callObject->dateTimeForm104}'), '')) as \"SETDATE\",
			EST.StickType_id as \"STKTYP_COD\",
			EST.EvnStick_Ser as \"SER\",
			EST.EvnStick_Num as \"NUM\",
			EST.StickOrder_id as \"ISCONT\",
			EST.EvnStick_Age as \"AGE\",
			RTRIM(coalesce(to_char(EST.EvnStick_begDate, '{$callObject->dateTimeForm104}'), '')) as \"BEGDATE\",
			RTRIM(coalesce(to_char(EST.EvnStick_endDate, '{$callObject->dateTimeForm104}'), '')) as \"ENDDATE\",
			EST.Sex_id as \"SEX_COD\",
			EST.StickCause_id as \"STKCS_COD\"
		";
	}

	public static function searchData_SearchFormType_KvsPerson(Search_model $callObject, $prefix)
	{
		return "
			{$prefix}.PersonEvn_id as \"PCT_ID\",
			{$prefix}.Person_id as \"P_ID\",
			{$prefix}.Person_SurName as \"SURNAME\",
			{$prefix}.Person_FirName as \"FIRNAME\",
			{$prefix}.Person_SecName as \"SECNAME\",
			rtrim(coalesce(to_char({$prefix}.Person_BirthDay, '{$callObject->dateTimeForm104}'),'')) as \"BIRTHDAY\",
			{$prefix}.Person_Snils as \"SNILS\",
			IsInv.YesNo_Code as \"INV_N\",
			InvD.Diag_Code as \"INV_DZ\",
			rtrim(coalesce(to_char(PCh.PersonChild_invDate, '{$callObject->dateTimeForm104}'),'')) as \"INV_DATA\",
			Sex.Sex_Name as \"SEX\",
			Soc.SocStatus_Name as \"SOC\",
			OMSST.OMSSprTerr_Code as \"P_TERK\",
			OMSST.OMSSprTerr_Name as \"P_TER\",
			PolTp.PolisType_Name as \"P_NAME\",
			Pol.Polis_Ser as \"P_SER\",
			Pol.Polis_Num as \"P_NUM\",
			{$prefix}.Person_EdNum as \"P_NUMED\",
			rtrim(coalesce(to_char(Pol.Polis_begDate, '{$callObject->dateTimeForm104}'),'')) as \"P_DATA\",
			OSO.Org_Code as \"SMOK\",
			OS.OrgSmo_Name as \"SMO\",
			0 as \"AR_TP\",
			UA.Address_Zip as \"AR_IDX\",
			UA.KLCountry_Name as \"AR_LND\",
			UA.KLRGN_Name as \"AR_RGN\",
			UA.KLSubRGN_Name as \"AR_RN\",
			UA.KLCity_Name as \"AR_CTY\",
			UA.KLTown_Name as \"AR_NP\",
			UA.KLStreet_Name as \"AR_STR\",
			UA.Address_House as \"AR_DOM\",
			UA.Address_Corpus as \"AR_K\",
			UA.Address_Flat as \"AR_KV\",
			0 as \"AP_TP\",
			PA.Address_Zip as \"AP_IDX\",
			PA.KLCountry_Name as \"AP_LND\",
			PA.KLRGN_Name as \"AP_RGN\",
			PA.KLSubRGN_Name as \"AP_RN\",
			PA.KLCity_Name as \"AP_CTY\",
			PA.KLTown_Name as \"AP_NP\",
			PA.KLStreet_Name as \"AP_STR\",
			PA.Address_House as \"AP_DOM\",
			PA.Address_Corpus as \"AP_K\",
			PA.Address_Flat as \"AP_KV\",
			DocTp.DocumentType_Name as \"D_TIP\",
			Doc.Document_Ser as \"D_SER\",
			Doc.Document_Num as \"D_NOM\",
			OrgD.Org_id as \"D_OUT\",
			rtrim(coalesce(to_char(Doc.Document_begDate, '{$callObject->dateTimeForm104}'),'')) as \"D_DATA\"
		";
	}

	public static function searchData_SearchFormType_EPLPerson(Search_model $callObject, $prefix)
	{
		return "
			{$prefix}.PersonEvn_id as \"PCT_ID\",
			{$prefix}.Person_id as \"P_ID\",
			{$prefix}.Person_SurName as \"SURNAME\",
			{$prefix}.Person_FirName as \"FIRNAME\",
			{$prefix}.Person_SecName as \"SECNAME\",
			rtrim(coalesce(to_char({$prefix}.Person_BirthDay, '{$callObject->dateTimeForm104}'),'')) as \"BIRTHDAY\",
			{$prefix}.Person_Snils as \"SNILS\",
			IsInv.YesNo_Code as \"INV_N\",
			InvD.Diag_Code as \"INV_DZ\",
			rtrim(coalesce(to_char(PCh.PersonChild_invDate, '{$callObject->dateTimeForm104}'),'')) as \"INV_DATA\",
			Sex.Sex_Name as \"SEX\",
			Soc.SocStatus_Name as \"SOC\",
			OMSST.OMSSprTerr_Code as \"P_TERK\",
			OMSST.OMSSprTerr_Name as \"P_TER\",
			PolTp.PolisType_Name as \"P_NAME\",
			Pol.Polis_Ser as \"P_SER\",
			Pol.Polis_Num as \"P_NUM\",
			{$prefix}.Person_EdNum as \"P_NUMED\",
			rtrim(coalesce(to_char(Pol.Polis_begDate, '{$callObject->dateTimeForm104}'),'')) as \"P_DATA\",
			OSO.Org_Code as \"SMOK\",
			OS.OrgSmo_Name as \"SMO\",
			0 as \"AR_TP\",
			UA.Address_Zip as \"AR_IDX\",
			UA.KLCountry_Name as \"AR_LND\",
			UA.KLRGN_Name as \"AR_RGN\",
			UA.KLSubRGN_Name as \"AR_RN\",
			UA.KLCity_Name as \"AR_CTY\",
			UA.KLTown_Name as \"AR_NP\",
			UA.KLStreet_Name as \"AR_STR\",
			UA.Address_House as \"AR_DOM\",
			UA.Address_Corpus as \"AR_K\",
			UA.Address_Flat as \"AR_KV\",
			0 as \"AP_TP\",
			PA.Address_Zip as \"AP_IDX\",
			PA.KLCountry_Name as \"AP_LND\",
			PA.KLRGN_Name as \"AP_RGN\",
			PA.KLSubRGN_Name as \"AP_RN\",
			PA.KLCity_Name as \"AP_CTY\",
			PA.KLTown_Name as \"AP_NP\",
			PA.KLStreet_Name as \"AP_STR\",
			PA.Address_House as \"AP_DOM\",
			PA.Address_Corpus as \"AP_K\",
			PA.Address_Flat as \"AP_KV\",
			DocTp.DocumentType_Name as \"D_TIP\",
			Doc.Document_Ser as \"D_SER\",
			Doc.Document_Num as \"D_NOM\",
			OrgD.Org_id as \"D_OUT\",
			rtrim(coalesce(to_char(Doc.Document_begDate, '{$callObject->dateTimeForm104}'),'')) as \"D_DATA\"
		";
	}

	public static function searchData_SearchFormType_EPLStomPerson(Search_model $callObject, $prefix)
	{
		return "
			{$prefix}.PersonEvn_id as \"PCT_ID\",
			{$prefix}.Person_id as \"P_ID\",
			{$prefix}.Person_SurName as \"SURNAME\",
			{$prefix}.Person_FirName as \"FIRNAME\",
			{$prefix}.Person_SecName as \"SECNAME\",
			rtrim(coalesce(to_char({$prefix}.Person_BirthDay, '{$callObject->dateTimeForm104}'),'')) as \"BIRTHDAY\",
			{$prefix}.Person_Snils as \"SNILS\",
			IsInv.YesNo_Code as \"INV_N\",
			InvD.Diag_Code as \"INV_DZ\",
			rtrim(coalesce(to_char(PCh.PersonChild_invDate, '{$callObject->dateTimeForm104}'),'')) as \"INV_DATA\",
			Sex.Sex_Name as \"SEX\",
			Soc.SocStatus_Name as \"SOC\",
			OMSST.OMSSprTerr_Code as \"P_TERK\",
			OMSST.OMSSprTerr_Name as \"P_TER\",
			PolTp.PolisType_Name as \"P_NAME\",
			Pol.Polis_Ser as \"P_SER\",
			Pol.Polis_Num as \"P_NUM\",
			{$prefix}.Person_EdNum as \"P_NUMED\",
			rtrim(coalesce(to_char(Pol.Polis_begDate, '{$callObject->dateTimeForm104}'),'')) as \"P_DATA\",
			OSO.Org_Code as \"SMOK\",
			OS.OrgSmo_Name as \"SMO\",
			0 as \"AR_TP\",
			UA.Address_Zip as \"AR_IDX\",
			UA.KLCountry_Name as \"AR_LND\",
			UA.KLRGN_Name as \"AR_RGN\",
			UA.KLSubRGN_Name as \"AR_RN\",
			UA.KLCity_Name as \"AR_CTY\",
			UA.KLTown_Name as \"AR_NP\",
			UA.KLStreet_Name as \"AR_STR\",
			UA.Address_House as \"AR_DOM\",
			UA.Address_Corpus as \"AR_K\",
			UA.Address_Flat as \"AR_KV\",
			0 as \"AP_TP\",
			PA.Address_Zip as \"AP_IDX\",
			PA.KLCountry_Name as \"AP_LND\",
			PA.KLRGN_Name as \"AP_RGN\",
			PA.KLSubRGN_Name as \"AP_RN\",
			PA.KLCity_Name as \"AP_CTY\",
			PA.KLTown_Name as \"AP_NP\",
			PA.KLStreet_Name as \"AP_STR\",
			PA.Address_House as \"AP_DOM\",
			PA.Address_Corpus as \"AP_K\",
			PA.Address_Flat as \"AP_KV\",
			DocTp.DocumentType_Name as \"D_TIP\",
			Doc.Document_Ser as \"D_SER\",
			Doc.Document_Num as \"D_NOM\",
			OrgD.Org_id as \"D_OUT\",
			rtrim(coalesce(to_char(Doc.Document_begDate, '{$callObject->dateTimeForm104}'),'')) as \"D_DATA\"
		";
	}

	public static function searchData_SearchFormType_KvsPersonCard(Search_model $callObject, $data)
	{
		$code = (isset($data["and_kvsperson"]) && $data["and_kvsperson"])
			? "" :
			"PS.PersonEvn_id as \"PCT_ID\",";
		return "
			PC.PersonCard_id as \"REG_ID\",
			{$code}
			PC.Person_id as \"P_ID\",
			PC.PersonCard_Code as \"PR_AK\",
			PC.LpuAttachType_Name as \"PR_TP\",
			rtrim(coalesce(to_char(PC.PersonCard_begDate, '{$callObject->dateTimeForm104}'),'')) as \"PR_DATA\",
			Lpu.Org_Code as \"LPUK\",
			Lpu.Lpu_Name as \"LPU\",
			PC.LpuRegionType_Name as \"TPLOT\",
			PC.LpuRegion_Name as \"LOT\"
		";
	}

	public static function searchData_SearchFormType_KvsEvnDiag(Search_model $callObject, $data)
	{
		$code = (isset($data["and_kvsperson"]) && $data["and_kvsperson"])
			? "" :
			"PS.PersonEvn_id as \"PCT_ID\",";
		return "
			EDPS.EvnDiagPS_id as \"DZ_ID\",
			EPS.EvnPS_id as \"GSP_ID\",
			{$code}
			PS.Person_id as \"P_ID\",
			(case
				when EDPS.DiagSetType_id = 1 then 1
				when EDPS.DiagSetType_id = 2 then 2
				when EDPS.DiagSetType_id = 3 then 3
			else 0
			end) as \"KTO\",
			Lpu.Org_Code as \"LPUK\",
			Lpu.Lpu_Name as \"LPU\",
			LS.LpuSection_Code as \"OTDK\",
			LS.LpuSection_Name as \"OTD\",
			rtrim(coalesce(to_char(EDPS.EvnDiagPS_setDT, '{$callObject->dateTimeForm104}'),'')) as \"DZ_DATA\",
			DSC.DiagSetClass_Name as \"DZ_W\",
			DST.DiagSetType_Name as \"DZ_T\",
			Diag.Diag_Code as \"DZ_DZ\"
		";
	}

	public static function searchData_SearchFormType_KvsEvnPS(Search_model $callObject, $data)
	{
		$code = (isset($data["and_kvsperson"]) && $data["and_kvsperson"])
			? "" :
			"PS.PersonEvn_id as \"PCT_ID\",";
		return "
			EPS.EvnPs_id as \"GSP_ID\",
			{$code}
			PS.Person_id as \"P_ID\",
			IsCont.YesNo_Code as \"KARTPR\",
			EPS.EvnPS_NumCard as \"KART\",
			PT.PayType_Name as \"WOPL\",
			rtrim(coalesce(to_char(EPS.EvnPS_setDate, '{$callObject->dateTimeForm104}'),'')) as \"DATAPOST\",
			EPS.EvnPS_setTime as \"TIMEPOST\",
			dbo.Age(PS.Person_BirthDay, EPS.EvnPS_setDate) as \"AGEPOST\",
			PD.PrehospDirect_Name as \"KN_KT\",
			LS.LpuSection_Code as \"KN_OTDLK\",
			LS.LpuSection_Name as \"KN_OTDL\",
			Org.Org_Code as \"KN_ORGK\",
			Org.Org_Name as \"KN_ORG\",
			IsFond.YesNo_Code as \"KN_FD\",
			EPS.EvnDirection_Num as \"KN_N\",
			rtrim(coalesce(to_char(EPS.EvnDirection_setDT, '{$callObject->dateTimeForm104}'),'')) as \"KN_DATA\",
			PA.PrehospArrive_Name as \"KD_KT\",
			EPS.EvnPS_CodeConv as \"KD_KOD\",
			EPS.EvnPS_NumConv as \"KD_NN\",
			DiagD.Diag_Code as \"DZGOSP\",
			IsImperHosp.YesNo_Code as \"DEF_NG\",
			IsShortVolume.YesNo_Code as \"DEF_NOO\",
			IsWrongCure.YesNo_Code as \"DEF_NTL\",
			IsDiagMismatch.YesNo_Code as \"DEF_ND\",
			Toxic.PrehospToxic_Name as \"ALKO\",
			PType.PrehospType_Name as \"PR_GP\",
			EPS.EvnPS_HospCount as \"PR_N\",
			EPS.EvnPS_TimeDesease as \"PR_W\",
			Trauma.TraumaType_Name as \"TR_T\",
			IsUnLaw.YesNo_Code as \"TR_P\",
			IsUnport.YesNo_Code as \"TR_N\",
			pLS.LpuSection_Code as \"PRO_NK\",
			pLS.LpuSection_Name as \"PRO_N\",
			MP.MedPersonal_TabCode as \"PRO_DOCK\",
			MP.Person_FIO as \"PRO_DOC\",
			Diag.Diag_Code as \"PRO_DZ\"
		";
	}

	public static function searchData_SearchFormType_KvsEvnSection(Search_model $callObject, $data)
	{
		$code = (isset($data["and_kvsperson"]) && $data["and_kvsperson"])
			? "" :
			"PS.PersonEvn_id as \"PCT_ID\",";
		return "
			ESEC.EvnSection_id as \"HSTRY_ID\",
			EPS.EvnPS_id as \"GSP_ID\",
			{$code}
			PS.Person_id as \"P_ID\",
			rtrim(coalesce(to_char(ESEC.EvnSection_setDate, '{$callObject->dateTimeForm104}'), '')) as \"DATAP\",
			ESEC.EvnSection_setTime as \"TIMEP\",
			rtrim(coalesce(to_char(ESEC.EvnSection_disDate, '{$callObject->dateTimeForm104}'), '')) as \"DATAW\",
			ESEC.EvnSection_disTime as \"TIMEW\",
			LS.LpuSection_Code as \"OTDLK\",
			LS.LpuSection_Name as \"OTDL\",
			PT.PayType_Name as \"WO\",
			TC.TariffClass_Name as \"WT\",
			MP.MedPersonal_TabCode as \"DOCK\",
			MP.Person_Fio as \"DOC\",
			Diag.Diag_Code as \"DZ\",
			Mes.Mes_Code as \"MES\",
			Mes.Mes_KoikoDni as \"NORM\",
			case when ESEC.EvnSection_disDate is not null
				then
case
	when LUT.LpuUnitType_Code = 2 and DATEDIFF('day', ESEC.EvnSection_setDate, ESEC.EvnSection_disDate) + 1 > 1
	then DATEDIFF('day', ESEC.EvnSection_setDate, ESEC.EvnSection_disDate)
	else DATEDIFF('day', ESEC.EvnSection_setDate, ESEC.EvnSection_disDate) + 1
end
				else null
			end as \"KDN\"
		";
	}

	public static function searchData_SearchFormType_KvsNarrowBed(Search_model $callObject, $data)
	{
		$code = (isset($data["and_kvsperson"]) && $data["and_kvsperson"])
			? ""
			: "PS.PersonEvn_id as \"PCT_ID\",";
		return "
			ESNB.EvnSectionNarrowBed_id as \"UK_ID\",
			{$code}
			PS.Person_id as \"P_ID\",
			EPS.EvnPS_id as \"GSP_ID\",
			ESEC.EvnSection_id as \"HSTRY_ID\",
			rtrim(coalesce(to_char(ESNB.EvnSectionNarrowBed_setDT, '{$callObject->dateTimeForm104}'), '')) as \"DATAP\",
			rtrim(coalesce(to_char(ESNB.EvnSectionNarrowBed_disDT, '{$callObject->dateTimeForm104}'), '')) as \"DATAW\",
			LS.LpuSection_Code as \"OTDLK\",
			LS.LpuSection_Name as \"OTDL\"
		";
	}

	public static function searchData_SearchFormType_KvsEvnUsluga(Search_model $callObject, $data)
	{
		$code = (isset($data["and_kvsperson"]) && $data["and_kvsperson"])
			? "" :
			"PS.PersonEvn_id as \"PCT_ID\",";
		return "
			EU.EvnUsluga_id as \"U_ID\",
			{$code}
			PS.Person_id as \"P_ID\",
			EPS.EvnPS_id as \"GSP_ID\",
			UC.UslugaClass_Name as \"U_TIP\",
			rtrim(coalesce(to_char(EU.EvnUsluga_setDate, '{$callObject->dateTimeForm104}'), '')) as \"U_DATA\",
			EU.EvnUsluga_setTime as \"U_TIME\",
			UP.UslugaPlace_Name as \"U_MESTO\",
			LS.LpuSection_Code as \"U_OTELK\",
			LS.LpuSection_Name as \"U_OTEL\",
			Lpu.Org_Code as \"U_LPUK\",
			Lpu.Lpu_Name as \"U_LPU\",
			Org.Org_Code as \"U_ORGK\",
			Org.Org_Name as \"U_ORG\",
			MP.MedPersonal_TabCode as \"U_DOCK\",
			MP.Person_Fio as \"U_DOC\",
			U.UslugaComplex_Code as \"U_USLKOD\",
			U.UslugaComplex_Name as \"U_USL\",
			PT.Paytype_Name as \"U_WO\",
			OT.OperType_Name as \"U_TIPOP\",
			OD.OperDiff_Name as \"U_KATSLOJ\",
			IsEndoskop.YesNo_Code as \"U_PREND\",
			IsLazer.YesNo_Code as \"U_PRLAS\",
			IsKriogen.YesNo_Code as \"U_PRKRI\",
			EU.EvnUsluga_Kolvo as \"U_KOL\"
		";
	}

	public static function searchData_SearchFormType_KvsEvnUslugaOB(Search_model $callObject, $data)
	{
		$code = (isset($data["and_kvsperson"]) && $data["and_kvsperson"])
			? ""
			: "PS.PersonEvn_id as \"PCT_ID\",";
		return "
			EU.EvnUsluga_id as \"U_ID\",
			{$code}
			PS.Person_id as \"P_ID\",
			EPS.EvnPS_id as \"GSP_ID\",
			ST.SurgType_Name as \"U_WID\",
			MP.MedPersonal_TabCode as \"U_DOCK\",
			MP.Person_Fio as \"U_DOC\"
		";
	}

	public static function searchData_SearchFormType_KvsEvnUslugaAn($data)
	{
		$code = (isset($data["and_kvsperson"]) && $data["and_kvsperson"])
			? "" :
			"PS.PersonEvn_id as \"PCT_ID\",";
		return "
			EU.EvnUsluga_id as \"U_ID\",
			{$code}
			PS.Person_id as \"P_ID\",
			EPS.EvnPS_id as \"GSP_ID\",
			AC.AnesthesiaClass_Name as \"U_ANEST\"
		";
	}

	public static function searchData_SearchFormType_KvsEvnUslugaOsl(Search_model $callObject, $data)
	{
		$code = (isset($data["and_kvsperson"]) && $data["and_kvsperson"])
			? ""
			: "PS.PersonEvn_id as \"PCT_ID\",";
		return "
			EU.EvnUsluga_id as \"U_ID\",
			{$code}
			PS.Person_id as \"P_ID\",
			EPS.EvnPS_id as \"GSP_ID\",
			rtrim(coalesce(to_char(EA.EvnAgg_setDate, '{$callObject->dateTimeForm104}'),'')) as \"U_DATA\",
			EA.EvnAgg_setTime as \"U_TIME\",
			AT.AggType_Name as \"U_WID\",
			AW.AggWhen_Name as \"U_KONT\"
		";
	}

	public static function searchData_SearchFormType_KvsEvnDrug(Search_model $callObject, $data)
	{
		$code = (isset($data["and_kvsperson"]) && $data["and_kvsperson"])
			? ""
			: "PS.PersonEvn_id as \"PCT_ID\",";
		return "
			ED.EvnDrug_id as \"MED_ID\",
			{$code}
			PS.Person_id as \"P_ID\",
			EPS.EvnPS_id as \"GSP_ID\",
			rtrim(coalesce(to_char(ED.EvnDrug_setDate, '{$callObject->dateTimeForm104}'),'')) as \"M_DATA\",
			LS.LpuSection_Code as \"M_OTDLK\",
			LS.LpuSection_Name as \"M_OTDL\",
			(Mol.Person_SurName || ' ' || Mol.Person_FirName || ' ' || Mol.Person_SecName) as \"M_MOL\",
			Drug.Drug_Code as \"MEDK\",
			Drug.Drug_Name as \"MED\",
			(
				'годн. ' || coalesce(to_char(Part.DocumentUcStr_godnDate, '{$callObject->dateTimeForm104}'),'отсут.') ||
				', цена ' || ROUND(coalesce(Part.DocumentUcStr_PriceR,0), 2) ||
				', ост. ' || Round(coalesce(Part.DocumentUcStr_Ost,0),4) ||
				', фин. ' || RTRIM(RTRIM(coalesce(Part.DrugFinance_Name, 'отсут.'))) ||
				', серия ' || RTRIM(coalesce(Part.DocumentUcStr_Ser, ''))
			) as \"M_PART\",
			Drug.Drug_Fas as \"M_KOL\",
			Drug.Drug_PackName as \"M_EU\",
			DUS.DocumentUcStr_Count as \"M_EU_OCT\",
			ED.EvnDrug_Kolvo as \"M_EU_KOL\",
			Drug.DrugForm_Name as \"M_ED\",
			DUS.DocumentUcStr_EdCount as \"M_ED_OCT\",
			ED.EvnDrug_KolvoEd as \"M_ED_KOL\",
			ED.EvnDrug_Price as \"M_CENA\",
			ED.EvnDrug_Sum as \"M_SUM\"
		";
	}

	public static function searchData_SearchFormType_KvsEvnLeave(Search_model $callObject, $data)
	{
		$code = (isset($data["and_kvsperson"]) && $data["and_kvsperson"])
			? ""
			: "PS.PersonEvn_id as \"PCT_ID\",";
		return "
			(case
				when ESEC.LeaveType_id = 1 then ELV.EvnLeave_id
				when ESEC.LeaveType_id = 2 then EOLpu.EvnOtherLpu_id
				when ESEC.LeaveType_id = 3 then EDie.EvnDie_id
				when ESEC.LeaveType_id = 4 then EOStac.EvnOtherStac_id
				when ESEC.LeaveType_id = 5 then EOSect.EvnOtherSection_id
			end) as \"ISCH_ID\",
			{$code}
			PS.Person_id as \"P_ID\",
			EPS.EvnPS_id as \"GSP_ID\",
			LType.LeaveType_Name as \"IG_W\",
			rtrim(coalesce(to_char((case
				when ESEC.LeaveType_id = 1 then ELV.EvnLeave_setDate
				when ESEC.LeaveType_id = 2 then EOLpu.EvnOtherLpu_setDate
				when ESEC.LeaveType_id = 3 then EDie.EvnDie_setDate
				when ESEC.LeaveType_id = 4 then EOStac.EvnOtherStac_setDate
				when ESEC.LeaveType_id = 5 then EOSect.EvnOtherSection_setDate
			end), '{$callObject->dateTimeForm104}'),'')) as \"IS_DATA\",
			(case
				when ESEC.LeaveType_id = 1 then ELV.EvnLeave_setTime
				when ESEC.LeaveType_id = 2 then EOLpu.EvnOtherLpu_setTime
				when ESEC.LeaveType_id = 3 then EDie.EvnDie_setTime
				when ESEC.LeaveType_id = 4 then EOStac.EvnOtherStac_setTime
				when ESEC.LeaveType_id = 5 then EOSect.EvnOtherSection_setTime
			end) as \"IS_TIME\",
			(case
				when ESEC.LeaveType_id = 1 then ELV.EvnLeave_UKL
				when ESEC.LeaveType_id = 2 then EOLpu.EvnOtherLpu_UKL
				when ESEC.LeaveType_id = 3 then EDie.EvnDie_UKL
				when ESEC.LeaveType_id = 4 then EOStac.EvnOtherStac_UKL
				when ESEC.LeaveType_id = 5 then EOSect.EvnOtherSection_UKL
			end) as \"IS_URUW\",
			RD.ResultDesease_Name as \"IS_BOL\",
			LC.LeaveCause_Name as \"IS_PR\",
			IsAmbul.YesNo_Code as \"IS_NAPR\",
			EOLpuL.Org_Code as \"IS_LPUK\",
			EOLpuL.Org_Name as \"IS_LPU\",
			EOStacLUT.LpuUnitType_Name as \"IS_TS\",
			LS.LpuSection_Code as \"IS_STACK\",
			LS.LpuSection_Name as \"IS_STAC\",
			MP.MedPersonal_TabCode as \"IS_DOCK\",
			MP.Person_Fio as \"IS_DOC\",
			DieDiag.Diag_Code as \"IS_DZ\"
		";
	}

	public static function searchData_SearchFormType_KvsEvnStick(Search_model $callObject, $data)
	{
		$code = (isset($data["and_kvsperson"]) && $data["and_kvsperson"])
			? ""
			: "PS.PersonEvn_id as \"PCT_ID\",";
		return "
			EST.EvnStick_id as \"LWN_ID\",
			{$code}
			PS.Person_id as \"P_ID\",
			EPS.EvnPS_id as \"GSP_ID\",
			SO.StickOrder_Name as \"PORYAD\",
			coalesce(NULLIF(LTRIM(RTRIM(coalesce(EST.EvnStick_Ser, '') || ' ' || coalesce(EST.EvnStick_Num, '') || ', ' || rtrim(coalesce(to_char(EST.EvnStick_begDate, '{$callObject->dateTimeForm104}'),'')))), 'от'), '') as \"LWNOLD\",
			EST.EvnStick_Ser as \"LWN_S\",
			EST.EvnStick_Num as \"LWN_N\",
			rtrim(coalesce(to_char(EST.EvnStick_begDate, '{$callObject->dateTimeForm104}'),'')) as \"LWN_D\",
			SC.StickCause_Name as \"LWN_PR\",
			(coalesce(PS.Person_SurName, '') || ' ' || coalesce(PS.Person_FirName, '') || ' ' || coalesce(PS.Person_SecName, '')) as \"ROD_FIO\",
			dbo.Age(PS.Person_BirthDay, dbo.tzgetdate()) as \"ROD_W\",
			Sex.Sex_Name as \"ROD_POL\",
			rtrim(coalesce(to_char(EST.EvnStick_sstBegDate, '{$callObject->dateTimeForm104}'),'')) as \"SKL_DN\",
			rtrim(coalesce(to_char(EST.EvnStick_sstEndDate, '{$callObject->dateTimeForm104}'),'')) as \"SKL_DK\",
			EST.EvnStick_sstNum as \"SKL_NOM\",
			EST.EvnStick_sstPlace as \"SKL_LPU\",
			SR.StickRegime_Name as \"LWN_R\",
			SLT.StickLeaveType_Name as \"LWN_ISCH\",
			EST.EvnStick_SerCont as \"LWN_SP\",
			EST.EvnStick_NumCont as \"LWN_NP\",
			rtrim(coalesce(to_char(EST.EvnStick_workDT, '{$callObject->dateTimeForm104}'),'')) as \"LWN_DR\",
			MP.MedPersonal_TabCode as \"LWN_DOCK\",
			MP.Person_Fio as \"LWN_DOC\",
			Lpu.Org_Code as \"LWN_LPUK\",
			Lpu.Lpu_Name as \"LWN_LPU\",
			D1.Diag_Code as \"LWN_DZ1\",
			'' as \"LWN_DZ2\"
		";
	}

	public static function searchData_SearchFormType_EvnAgg(Search_model $callObject, $data)
	{
		$add_pa = ($callObject->getRegionNick() != "kareliya")
			? ""
			: ((isset($data["and_eplperson"]) && $data["and_eplperson"])
				? ""
				: "PS.PersonEvn_id as \"PCT_ID\",");
		return "
			EvnAgg.EvnAgg_pid as \"EUS_ID\",
			{$add_pa}
			EvnAgg.EvnAgg_id as \"EAGG_ID\",
			RTRIM(coalesce(to_char(EvnAgg.EvnAgg_setDT, '{$callObject->dateTimeForm104}'), '')) as \"SETDATE\",
			EvnAgg.EvnAgg_setTime as \"SETTIME\",
			dbfaw.AggWhen_Code as \"AW_CODE\",
			dbfat.AggType_Code as \"AT_CODE\"
		";
	}

	public static function searchData_SearchFormType_EvnAggStom(Search_model $callObject, $data)
	{
		$code = (isset($data["and_eplstomperson"]) && $data["and_eplstomperson"])
			? ""
			: "PS.PersonEvn_id as \"PCT_ID\",";
		return "
			EvnAgg.EvnAgg_pid as \"EUS_ID\",
			{$code}
			EvnAgg.EvnAgg_id as \"EAGG_ID\",
			RTRIM(coalesce(to_char(EvnAgg.EvnAgg_setDT, '{$callObject->dateTimeForm104}'), '')) as \"SETDATE\",
			EvnAgg.EvnAgg_setTime as \"SETTIME\",
			dbfaw.AggWhen_Code as \"AW_CODE\",
			dbfat.AggType_Code as \"AT_CODE\"
		";
	}

	public static function searchData_SearchFormType_EvnVizitPL(Search_model $callObject, $data, $isBDZ, $dbf, &$filter, &$queryParams)
	{
		if ($dbf === true) {
			$add_pv = ($callObject->getRegionNick() != "kareliya")
				? ""
				: ((isset($data["and_eplperson"]) && $data["and_eplperson"])
					? ""
					: "PS.PersonEvn_id as \"PCT_ID\",");
			$query = "
				EVizitPL.EvnVizitPL_pid as \"EPL_ID\",
				{$add_pv}
				EVizitPL.EvnVizitPL_id as \"EVZ_ID\",
				EPL.EvnPL_NumCard as \"NUMCARD\",
				rtrim(coalesce(to_char(EVizitPL.EvnVizitPL_setDate, '{$callObject->dateTimeForm104}'),'')) as \"SETDATE\",
				EVizitPL.EvnVizitPL_setTime as \"SETTIME\",
				dbfvc.VizitClass_Code as \"PERVVTOR\",
				dbfls.LpuSection_Code as \"LS_COD\",
				rtrim(dbfls.LpuSection_Name) as \"LS_NAM\",
				dbfmp.MedPersonal_TabCode as \"MP_COD\",
				rtrim(dbfmp.Person_FIO) as \"MP_FIO\",
				dbfpt.PayType_Code as \"PAY_COD\",
				dbfvt.VizitType_Code as \"VZT_COD\",
				dbfst.ServiceType_Code as \"SRT_COD\",
				dbfpg.ProfGoal_code as \"PRG_COD\",
				dbfdiag.Diag_Code as \"DZ_COD\",
				rtrim(dbfdiag.Diag_Name) as \"DZ_NAM\",
				dbfdt.DeseaseType_Code as \"DST_COD\"
			";
		} else {
			$code = (in_array($data["session"]["region"]["nick"], ["ufa"]))
				? "EU.UslugaComplex_Code as \"UslugaComplex_Code\", "
				: "null as \"UslugaComplex_Code\",";
			$query = "
				EVizitPL.EvnVizitPL_id as \"EvnVizitPL_id\",
				EVizitPL.EvnVizitPL_pid as \"EvnPL_id\",
				Evn.Person_id as \"Person_id\",
				Evn.PersonEvn_id as \"PersonEvn_id\",
				Evn.Server_id as \"Server_id\",
				RTRIM(EPL.EvnPL_NumCard) as \"EvnPL_NumCard\",
				RTRIM(evpldiag.Diag_Code) || '. ' || RTRIM(evpldiag.Diag_Name) as \"Diag_Name\",
				RTRIM(evplls.LpuSection_Name) as \"LpuSection_Name\",
				RTRIM(evplmp.Person_Fio) as \"MedPersonal_Fio\",
				{$code}
				to_char(EVizitPL.EvnVizitPL_setDate, '{$callObject->dateTimeForm104}') as \"EvnVizitPL_setDate\",
				RTRIM(evplst.ServiceType_Name) as \"ServiceType_Name\",
				RTRIM(evplvt.VizitType_Name) as \"VizitType_Name\",
				RTRIM(evplpt.PayType_Name) as \"PayType_Name\",
				{$isBDZ}
				RTRIM(evplhk.HealthKind_Name) as \"HealthKind_Name\"
			";
			if (allowPersonEncrypHIV($data["session"])) {
				$query .= "
,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as \"Person_Firname\"
,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as \"Person_Secname\"
,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') else null end as \"Person_deadDT\"
				";
			} else {
				$query .= "
,RTRIM(PS.Person_SurName) as \"Person_Surname\"
,RTRIM(PS.Person_FirName) as \"Person_Firname\"
,RTRIM(PS.Person_SecName) as \"Person_Secname\"
,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
,to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') as \"Person_deadDT\"
				";
			}
			if (!empty($data["InterruptLeaveType_id"])) {
				$queryParams["InterruptLeaveType_id"] = $data["InterruptLeaveType_id"];
				$filter .= " and EPL.InterruptLeaveType_id = :InterruptLeaveType_id";
			}
			$query .= ",case when SES1.ServiceEvnStatus_SysNick in ('sendegis','loadegis') then 'true' else 'false' end as \"fedservice_iemk\"";
			if (isset($data["Service1EvnStatus_id"])) {
				$filter .= " and SES1.ServiceEvnStatus_id = :Service1EvnStatus_id ";
				$queryParams["Service1EvnStatus_id"] = $data["Service1EvnStatus_id"];
			}
		}
		return $query;
	}

	public static function searchData_SearchFormType_EvnVizitPLStom(Search_model $callObject, $data, $isBDZ, $dbf, &$filter, &$queryParams)
	{
		if ($dbf === true) {
			$code = (isset($data["and_eplperson"]) && $data["and_eplperson"])
				? ""
				: "PS.PersonEvn_id as \"PCT_ID\",";
			$query = "
				EVPLS.EvnVizitPLStom_pid as \"EPL_ID\",
				{$code}
				EVPLS.EvnVizitPLStom_id as \"EVZ_ID\",
				EPLS.EvnPLStom_NumCard as \"NUMCARD\",
				rtrim(coalesce(to_char(EVPLS.EvnVizitPLStom_setDate, '{$callObject->dateTimeForm104}'), '')) as \"SETDATE\",
				EVPLS.EvnVizitPLStom_setTime as \"SETTIME\",
				dbfvc.VizitClass_Code as \"PERVVTOR\",
				dbfls.LpuSection_Code as \"LS_COD\",
				rtrim(dbfls.LpuSection_Name) as \"LS_NAM\",
				dbfmp.MedPersonal_TabCode as \"MP_COD\",
				rtrim(dbfmp.Person_FIO) as \"MP_FIO\",
				dbfpt.PayType_Code as \"PAY_COD\",
				dbfvt.VizitType_Code as \"VZT_COD\",
				dbfst.ServiceType_Code as \"SRT_COD\",
				dbfpg.ProfGoal_code as \"PRG_COD\",
				dbfdiag.Diag_Code as \"DZ_COD\",
				rtrim(dbfdiag.Diag_Name) as \"DZ_NAM\",
				dbfdt.DeseaseType_Code as \"DST_COD\"
			";
		} else {
			$query = "
				EVPLS.EvnVizitPLStom_id as \"EvnVizitPLStom_id\",
				EVPLS.EvnVizitPLStom_pid as \"EvnPLStom_id\",
				EPLS.Person_id as \"Person_id\",
				EPLS.PersonEvn_id as \"PersonEvn_id\",
				EPLS.Server_id as \"Server_id\",
				RTRIM(EPLS.EvnPLStom_NumCard) as \"EvnPLStom_NumCard\",
				coalesce((RTRIM(evpldiag.Diag_Code) || '. ' || RTRIM(evpldiag.Diag_Name)),'') as \"Diag_Name\",
				RTRIM(evplls.LpuSection_Name) as \"LpuSection_Name\",
				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				to_char(EVPLS.EvnVizitPLStom_setDate, '{$callObject->dateTimeForm104}') as \"EvnVizitPLStom_setDate\",
				RTRIM(evplst.ServiceType_Name) as \"ServiceType_Name\",
				RTRIM(evplvt.VizitType_Name) as \"VizitType_Name\",
				RTRIM(evplpt.PayType_Name) as \"PayType_Name\",
				{$isBDZ}
				EVPLS.EvnVizitPLStom_Uet as \"EvnVizitPLStom_Uet\"
			";
			if (allowPersonEncrypHIV($data['session'])) {
				$query .= "
,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as \"Person_Firname\"
,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as \"Person_Secname\"
,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') else null end as \"Person_deadDT\"
				";
			} else {
				$query .= "
,RTRIM(PS.Person_SurName) as \"Person_Surname\"
,RTRIM(PS.Person_FirName) as \"Person_Firname\"
,RTRIM(PS.Person_SecName) as \"Person_Secname\"
,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
,to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') as \"Person_deadDT\"
				";
			}
			if (!empty($data["InterruptLeaveType_id"])) {
				$queryParams["InterruptLeaveType_id"] = $data["InterruptLeaveType_id"];
				$filter .= " and EPLS.InterruptLeaveType_id = :InterruptLeaveType_id";
			}
		}
		return $query;
	}

	public static function searchData_SearchFormType_EvnPL(Search_model $callObject, $data, $isBDZ, $dbf, &$filter, &$queryParams)
	{
		if ($dbf === true) {
			if ($callObject->getRegionNick() != 'kareliya') {
				$add_p = "";
				$add_p2 = "
PS.Person_SurName as \"SURNAME\",
PS.Person_FirName as \"FIRNAME\",
PS.Person_SecName as \"SECNAME\",
to_char(PS.Person_BirthDay, '{$callObject->dateTimeForm104}') as \"BIRTHDAY\",
dbfsex.Sex_Code as \"POL_COD\",
dbfss.SocStatus_code as \"SOC_COD\",
coalesce(dbfkls.Kladr_Code, dbfkla.Kladr_Code) as \"KOD_TER\",
PS.person_Snils as \"SNILS\",
				";
			} else {
				$add_p = (isset($data['and_eplperson']) && $data['and_eplperson']) ? "" : "PS.PersonEvn_id as \"PCT_ID\",";
				$add_p2 = "";
			}
			$query = "
				EPL.EvnPL_id as \"EPL_ID\",
				{$add_p}
				dbfpd.PrehospDirect_Code as \"DIR_CODE\",
				case
when dbfpd.PrehospDirect_Code = 1 then coalesce(NULLIF(RTRIM(dbflsd.LpuSection_Code), ''), '')
when dbfpd.PrehospDirect_Code = 2 then coalesce(NULLIF(RTRIM(CAST(dbfprehosplpu.Lpu_Ouz as varchar)), ''), '')
else '' end
				as \"PDO_CODE\",
				to_char(Evn.Evn_setDT, '{$callObject->dateTimeForm104}') as \"SETDATE\",
				to_char(Evn.Evn_setDT, '{$callObject->dateTimeForm108}') as \"SETTIME\",
				to_char(Evn.Evn_disDT, '{$callObject->dateTimeForm104}') as \"DISDATE\",
				to_char(Evn.Evn_disDT, '{$callObject->dateTimeForm108}') as \"DISTIME\",
				EPL.EvnPL_NumCard as \"NUMCARD\",
				dbfift.YesNo_Code as \"VPERV\",
				coalesce(EPL.EvnPL_Complexity, 0) as \"KATEGOR\",
				dbflpu.Lpu_OGRN as \"OGRN\",
				{$add_p2}
				case when EvnPLBase.EvnPLBase_IsFinish = 2 then '1' else '0' end as \"FINISH_ID\",
				dbfrc.ResultClass_Code as \"RSC_COD\",
				dbfdiag.Diag_Code as \"DZ_COD\",
				dbfdiag.Diag_Name as \"DZ_NAM\",
				dbfdt.DeseaseType_code as \"DST_COD\",
				EPL.EvnPL_UKL as \"UKL\",
				coalesce(dbfdc.DirectClass_Code, 0) as \"CODE_NAP\",
				case
when dbfdc.DirectClass_Code = 1 then coalesce(NULLIF(RTRIM(dbflsdir.LpuSection_Code), ''), '')
when dbfdc.DirectClass_Code = 2 then coalesce(NULLIF(RTRIM(CAST(dbflpudir.Lpu_Ouz as varchar)), ''), '')
else '' end
				as \"KUDA_NAP\",
				dbfinv.PersonChild_IsInvalid_Code as \"INVALID\",
				dbfinv.PermRegion_Code as \"REG_PERM\",
				CASE WHEN PS.Server_pid = 0 THEN 'Да' ELSE 'Нет' END as \"BDZ\"
			";
		} else {
			$query = "
				EPL.EvnPL_id as \"EvnPL_id\",
				EPL.Person_id as \"Person_id\",
				EPL.PersonEvn_id as \"PersonEvn_id\",
				EPL.Server_id as \"Server_id\",
				CASE WHEN pls.PolisType_id = 4 THEN PS.Person_edNum ELSE pls.Polis_Num END as \"Polis_Num\",
				coalesce(EPL.EvnPL_IsTransit, 1) as \"EvnPL_IsTransit\",
				RTRIM(EPL.EvnPL_NumCard) as \"EvnPL_NumCard\",
				EPL.EvnPL_VizitCount as \"EvnPL_VizitCount\",
				IsFinish.YesNo_Name as \"EvnPL_IsFinish\",
				RTRIM(EVPLD.Diag_Code) || '. ' || RTRIM(EVPLD.Diag_Name) as \"Diag_Name\",
				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				to_char(EPL.EvnPL_setDT, '{$callObject->dateTimeForm104}') as \"EvnPL_setDate\",
				to_char(EPL.EvnPL_disDT, '{$callObject->dateTimeForm104}') as \"EvnPL_disDate\",
				RTRIM(HK.HealthKind_Name) as \"HealthKind_Name\",
				{$isBDZ}
				coalesce(VT.VizitType_Name,'') as \"VizitType_Name\",
				to_char(ecp.EvnCostPrint_setDT, '{$callObject->dateTimeForm104}') as \"EvnCostPrint_setDT\",
				case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as \"EvnCostPrint_IsNoPrintText\"
			";
			if (!empty($data["InterruptLeaveType_id"])) {
				$queryParams["InterruptLeaveType_id"] = $data["InterruptLeaveType_id"];
				$filter .= " and EPL.InterruptLeaveType_id = :InterruptLeaveType_id";
			}
			$query .= ",case when SES1.ServiceEvnStatus_SysNick in ('sendegis','loadegis') then 'true' else 'false' end as \"fedservice_iemk\"";
			if (isset($data["Service1EvnStatus_id"])) {
				$filter .= " and SES1.ServiceEvnStatus_id = :Service1EvnStatus_id ";
				$queryParams["Service1EvnStatus_id"] = $data["Service1EvnStatus_id"];
			}
			if (allowPersonEncrypHIV($data["session"])) {
				$query .= "
,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as \"Person_Firname\"
,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as \"Person_Secname\"
,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') else null end as \"Person_deadDT\"
				";
			} else {
				$query .= "
,RTRIM(PS.Person_SurName) as \"Person_Surname\"
,RTRIM(PS.Person_FirName) as \"Person_Firname\"
,RTRIM(PS.Person_SecName) as \"Person_Secname\"
,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
,to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') as \"Person_deadDT\"
				";
			}
			if ($callObject->regionNick == "kz") {
				$query .= "
,CASE WHEN air.AISResponse_id is not null THEN 'true' ELSE 'false' END as \"toAis25\"
,CASE WHEN air9.AISResponse_id is not null THEN 'true' ELSE 'false' END as \"toAis259\"
				";
			}
		}
		return $query;
	}

	public static function searchData_SearchFormType_EvnPLStom(Search_model $callObject, $data, $isBDZ, $dbf, &$filter, &$queryParams)
	{
		if ($dbf === true) {
			$code = (isset($data["and_eplstomperson"]) && $data["and_eplstomperson"])
				? ""
				: "PS.PersonEvn_id as \"PCT_ID\",";
			$query = "
				EPLS.EvnPLStom_id as \"EPL_ID\",
				{$code}
				dbfpd.PrehospDirect_Code as \"DIR_CODE\",
				case
when dbfpd.PrehospDirect_Code = 1 then coalesce(NULLIF(RTRIM(dbflsd.LpuSection_Code), ''), '')
when dbfpd.PrehospDirect_Code = 2 then coalesce(NULLIF(RTRIM(CAST(dbfprehosplpu.Lpu_Ouz as varchar)), ''), '')
else ''
				end as \"PDO_CODE\",
				to_char(EPLS.EvnPLStom_setDate, '{$callObject->dateTimeForm104}') as \"SETDATE\",
				to_char(EPLS.EvnPLStom_setDate, '{$callObject->dateTimeForm108}') as \"SETTIME\",
				to_char(EPLS.EvnPLStom_disDate, '{$callObject->dateTimeForm104}') as \"DISDATE\",
				to_char(EPLS.EvnPLStom_disDate, '{$callObject->dateTimeForm108}') as \"DISTIME\",
				RTRIM(EPLS.EvnPLStom_NumCard) as \"NUMCARD\",
				dbfift.YesNo_Code as \"VPERV\",
				coalesce(EPLS.EvnPLStom_Complexity, 0) as \"KATEGOR\",
				dbflpu.Lpu_OGRN as \"OGRN\",
				case when EPLS.EvnPLStom_IsFinish = 2
then '1'
else '0'
				end as \"FINISH_ID\",
				dbfrc.ResultClass_Code as \"RSC_COD\",
				dbfdiag.Diag_Code as \"DZ_COD\",
				dbfdiag.Diag_Name as \"DZ_NAM\",
				dbfdt.DeseaseType_code as \"DST_COD\",
				cast(EPLS.EvnPLStom_UKL as float) as \"UKL\",
				coalesce(dbfdc.DirectClass_Code, 0) as \"CODE_NAP\",
				case
when dbfdc.DirectClass_Code = 1 then coalesce(NULLIF(RTRIM(dbflsdir.LpuSection_Code), ''), '')
when dbfdc.DirectClass_Code = 2 then coalesce(NULLIF(RTRIM(cast(dbflpudir.Lpu_Ouz as varchar)), ''), '')
else ''
				end as \"KUDA_NAP\",
				dbfinv.PersonChild_IsInvalid_Code as \"INVALID\",
				dbfinv.PermRegion_Code as \"REG_PERM\",
				CASE WHEN PS.Server_pid = 0 THEN 'Да' ELSE 'Нет' END as \"BDZ\"
			";
		} else {
			$query = "
				EPLS.EvnPLStom_id as \"EvnPLStom_id\",
				EPLS.Person_id as \"Person_id\",
				EPLS.PersonEvn_id as \"PersonEvn_id\",
				EPLS.Server_id as \"Server_id\",
				coalesce(EPLS.EvnPLStom_IsTransit, 1) as \"EvnPLStom_IsTransit\",
				RTRIM(EPLS.EvnPLStom_NumCard) as \"EvnPLStom_NumCard\",
				CNT.EvnPLStom_VizitCount as \"EvnPLStom_VizitCount\",
				IsFinish.YesNo_Name as \"EvnPLStom_IsFinish\",
				RTRIM(EVPLSD.Diag_Code) || '. ' || RTRIM(EVPLSD.Diag_Name) as \"Diag_Name\",
				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				to_char(EPLS.EvnPLStom_setDate, '{$callObject->dateTimeForm104}') as \"EvnPLStom_setDate\",
				to_char(EPLS.EvnPLStom_disDate, '{$callObject->dateTimeForm104}') as \"EvnPLStom_disDate\",
				{$isBDZ}
				to_char(ecp.EvnCostPrint_setDT, '{$callObject->dateTimeForm104}') as \"EvnCostPrint_setDT\",
				case when ecp.EvnCostPrint_IsNoPrint = 2
then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1
	then 'Справка выдана'
	else ''
				end as \"EvnCostPrint_IsNoPrintText\"
			";
			if ($callObject->regionNick == "kz") {
				$query .= "
,CASE WHEN air.AISResponse_id is not null THEN 'true' ELSE 'false' END as \"toAis25\"
,CASE WHEN air9.AISResponse_id is not null THEN 'true' ELSE 'false' END as \"toAis259\"
				";
			}
			if (isset($data["EvnPLStom_KSG"])) {
				if ($data["EvnPLStom_KSG"] == 2) {
					$ksg_filter = " AND EDPLS.Mes_id is not null";
					if (isset($data["EvnPLStom_KSG_Num"])) {
						$queryParams["Mes_Code"] = $data["EvnPLStom_KSG_Num"];
						$ksg_filter .= " AND v_MesOld.Mes_Code = :Mes_Code";
					}
				}
				if ($data["EvnPLStom_KSG"] == 1) {
					$ksg_filter = " and EDPLS.Mes_id is null";
				}
				$filter .= "
and exists (
	select EDPLS.*
	  				    from
	  				        v_EvnDiagPLStom EDPLS
	  	left join v_MesOld on v_MesOld.Mes_id = EDPLS.Mes_id
	  where EDPLS.EvnDiagPLStom_pid = EVPLS.EvnVizitPLStom_id
	      {$ksg_filter}
)
				";
			}
			if (allowPersonEncrypHIV($data["session"])) {
				$query .= "
,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as \"Person_Firname\"
,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as \"Person_Secname\"
,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') else null end as \"Person_deadDT\"
,case when PEH.PersonEncrypHIV_id is null then RTRIM(ua.Address_Address) else '' end as \"Person_AdrReg\"
,case when PEH.PersonEncrypHIV_id is null then RTRIM(pa.Address_Address) else '' end as \"Person_AdrProj\"
				";
			} else {
				$query .= "
,RTRIM(PS.Person_SurName) as \"Person_Surname\"
,RTRIM(PS.Person_FirName) as \"Person_Firname\"
,RTRIM(PS.Person_SecName) as \"Person_Secname\"
,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
,to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') as \"Person_deadDT\"
,ua.Address_Address as \"Person_AdrReg\"
,pa.Address_Address as \"Person_AdrProj\"
				";
			}
			if (!empty($data["InterruptLeaveType_id"])) {
				$queryParams["InterruptLeaveType_id"] = $data["InterruptLeaveType_id"];
				$filter .= " and EPLS.InterruptLeaveType_id = :InterruptLeaveType_id";
			}
		}
		return $query;
	}

	public static function searchData_SearchFormType_EvnPS(Search_model $callObject, $data, $isBDZ, $dbf, &$filter, &$queryParams)
	{
		if ($dbf === true) {
			$query = "
				EPS.EvnPS_id as \"EPS_COD\",
				EPS.EvnPS_IsCont as \"IS_CONT\",
				RTRIM(coalesce(to_char(EPS.EvnPS_setDate, '{$callObject->dateTimeForm104}'), '')) as \"SETDATE\",
				EPS.EvnPS_setTime as \"SETTIME\",
				RTRIM(coalesce(to_char(EPS.EvnPS_disDate, '{$callObject->dateTimeForm104}'), '')) as \"DISDATE\",
				EPS.EvnPS_disTime as \"DISTIME\",
				EPS.Person_id as \"PERS_COD\",
				dbflpu.Lpu_OGRN as \"LPU_OGRN\",
				RTRIM(EPS.EvnPS_NumCard) as \"NUMBER\",
				dbfpa.PrehospArrive_Code as \"PRHARR_COD\",
				dbfpd.PrehospDirect_Code as \"PRHDIR_COD\",
				dbfpt.PrehospToxic_Code as \"PRHTOX_COD\",
				dbfpayt.PayType_Code as \"PAY_COD\",
				dbfprtr.PrehospTrauma_Code as \"PRHTRV_COD\",
				dbfprtype.PrehospType_Code as \"PRHTYP_COD\",
				dbfdorg.Org_OGRN as \"ORG_OGRN\",
				dbflsd.LpuSection_Code as \"LS_COD\",
				EPS.EvnDirection_Num as \"NUMORD\",
				RTRIM(coalesce(to_char(EPS.EvnDirection_setDT, '{$callObject->dateTimeForm104}'), '')) as \"DATEORD\",
				EPS.EvnPS_CodeConv as \"CODCON\",
				EPS.EvnPS_NumConv as \"NUMCON\",
				EPS.EvnPS_TimeDesease as \"DESSTIME\",
				EPS.EvnPS_HospCount as \"HOSPCOUNT\",
				dbfoorg.Org_OGRN as \"OLPU_OGRN\",
				EPS.EvnPS_IsUnlaw as \"ISUNLAW\",
				EPS.EvnPS_IsUnport as \"ISUNPORT\",
				dbfmp.MedPersonal_TabCode as \"MP_COD\"
			";
		} else {
			$ksg_query = " coalesce(ksgkpg.Mes_Code, '') || ' ' ||  coalesce(ksgkpg.Mes_Name, '') as \"EvnSection_KSG\",";
			$kpg_query = " kpg.Mes_Code as \"EvnSection_KPG\",";
			if ($callObject->getRegionNick() == "ekb") {
				$ksg_query = " coalesce(sksg.Mes_Code, '') || ' ' ||  coalesce(sksg.Mes_Name, '') as \"EvnSection_KSG\",";
			}
			if ($callObject->getRegionNick() == "kareliya") {
				$ksg_query = "
case when ksgkpg.Mes_id = ksg.Mes_id
	then coalesce(ksgkpg.Mes_Code, '') || ' ' ||  coalesce(ksgkpg.Mes_Name, '')
end as \"EvnSection_KSG\",
				";
				$kpg_query = "
case when ksgkpg.Mes_id = kpg.Mes_id 
	then coalesce(ksgkpg.Mes_Code, '') || ' ' ||  coalesce(ksgkpg.Mes_Name, '')
end as \"EvnSection_KPG\",
				";
			}
			$dni_query = "
				case when LpuUnitType.LpuUnitType_SysNick = 'stac'
then date_part('day', EPS.EvnPS_disDate - EPS.EvnPS_setDate) + abs(sign(date_part('day', EPS.EvnPS_disDate - EPS.EvnPS_setDate)) - 1)
else (date_part('day', EPS.EvnPS_disDate - EPS.EvnPS_setDate) + 1)
				end as \"EvnPS_KoikoDni\",
			";
			if ($callObject->getRegionNick() == "kz") {
				$dni_query = "
case when LpuUnitType.LpuUnitType_SysNick = 'stac'
	then date_part('day', EPS.EvnPS_disDate - EPS.EvnPS_setDate) + abs(sign(date_part('day', EPS.EvnPS_disDate - EPS.EvnPS_setDate)) - 1)
	else (
		date_part('day', EPS.EvnPS_disDate - EPS.EvnPS_setDate)
			- ((date_part('day', EPS.EvnPS_disDate) - 2)/7 - (date_part('day', EPS.EvnPS_setDate) - 1)/7)
			- ((date_part('day', EPS.EvnPS_disDate) - 1)/7 - date_part('day', EPS.EvnPS_setDate)/7)
			+ 1
		)
end as \"EvnPS_KoikoDni\",
				";
			}
			$code = ($callObject->regionNick == "kz")
				? " coalesce(objsync.ObjectSynchronLogService_id, '') || '' || EPS.EvnPS_id as \"Evn_sendERSB\", "
				: "";
			$code1 = ($callObject->regionNick == "kz")
				? ",epsl.Hospitalization_id as \"Hospitalization_id\""
				: "";
			$query = "
				EPS.EvnPS_id as \"EvnPS_id\",
				{$code}
				EPS.Person_id as \"Person_id\",
				EPS.PersonEvn_id as \"PersonEvn_id\",
				EPS.Server_id as \"Server_id\",
				coalesce(EPS.EvnPS_IsTransit, 1) as \"EvnPS_IsTransit\",
				RTRIM(EPS.EvnPS_NumCard) as \"EvnPS_NumCard\",
				to_char(EPS.EvnPS_setDate, '{$callObject->dateTimeForm104}') as \"EvnPS_setDate\",
				to_char(EPS.EvnPS_disDate, '{$callObject->dateTimeForm104}') as \"EvnPS_disDate\",
				coalesce(LStmp.LpuSection_Name, '') as \"LpuSection_Name\",
				coalesce(Dtmp.Diag_FullName, DP.Diag_FullName) as \"Diag_Name\",
				{$dni_query}
				{$isBDZ}
				dbfpayt.PayType_Name as \"PayType_Name\", --Вид оплаты
				CASE
WHEN LT.LeaveType_Name is not null THEN LT.LeaveType_Name
WHEN EPS.PrehospWaifRefuseCause_id > 0 THEN pwrc.PrehospWaifRefuseCause_Name
ELSE '' END as \"LeaveType_Name\",
				LT.LeaveType_Code as \"LeaveType_Code\",
				CASE WHEN DeathSvid.DeathSvid_id is null
then 'false'
else 'true'
				end as \"DeadSvid\",
				EPS.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\",
				{$ksg_query}
				coalesce(ksgkpg.Mes_Code, '') || ' ' ||  coalesce(ksgkpg.Mes_Name, '') as \"EvnSection_KSGKPG\",
				{$kpg_query}
				to_char(ecp.EvnCostPrint_setDT, '{$callObject->dateTimeForm104}') as \"EvnCostPrint_setDT\",
				case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as \"EvnCostPrint_IsNoPrintText\"
				{$code1}
			";
			if (allowPersonEncrypHIV($data["session"])) {
				$query .= "
,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as \"Person_Firname\"
,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as \"Person_Secname\"
,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
,case when PEH.PersonEncrypHIV_id is null
	then COALESCE(to_char(DeathSvid.DeathSvid_DeathDate, '{$callObject->dateTimeForm104}'),to_char(EvnDie.EvnDie_setDate, '{$callObject->dateTimeForm104}'),to_char(PS.Person_DeadDT, '{$callObject->dateTimeForm104}'), '')
	else null
end as \"Person_deadDT\"
				";
			} else {
				$query .= "
,RTRIM(PS.Person_SurName) as \"Person_Surname\"
,RTRIM(PS.Person_FirName) as \"Person_Firname\"
,RTRIM(PS.Person_SecName) as \"Person_Secname\"
,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
,COALESCE(to_char(DeathSvid.DeathSvid_DeathDate, '{$callObject->dateTimeForm104}'),to_char(EvnDie.EvnDie_setDate, '{$callObject->dateTimeForm104}'),to_char(PS.Person_DeadDT, '{$callObject->dateTimeForm104}'),'') as \"Person_deadDT\"
				";
			}
			$query .= ",case when SES1.ServiceEvnStatus_SysNick in ('sendegis','loadegis') then 'true' else 'false' end as \"fedservice_iemk\"";
			if (isset($data["Service1EvnStatus_id"])) {
				$filter .= " and SES1.ServiceEvnStatus_id = :Service1EvnStatus_id ";
				$queryParams["Service1EvnStatus_id"] = $data["Service1EvnStatus_id"];
			}
		}
		return $query;
	}

	public static function searchData_SearchFormType_EvnRecept(Search_model $callObject, $data, $isBDZ)
	{
		$query = "
			ER.EvnRecept_id as \"EvnRecept_id\",
			ER.Person_id as \"Person_id\",
			ER.PersonEvn_id as \"PersonEvn_id\",
			ER.Server_id as \"Server_id\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			Lpu.Lpu_id as \"Lpu_id\",
			ER.Drug_id as \"Drug_id\",
			ER.Drug_rlsid as \"Drug_rlsid\",
			ER.DrugComplexMnn_id as \"DrugComplexMnn_id\",
			ER.ReceptDelayType_id as \"ReceptDelayType_id\",
			ER.OrgFarmacy_oid as \"OrgFarmacy_oid\",
			case 
				when ER.ReceptDelayType_id in ('1','2','3','5') then ER_RDT.ReceptDelayType_Name 
				when (
	ER.ReceptDelayType_id is null and
	(
			(RV.ReceptValidType_id = 1 and ((ER.EvnRecept_setDate + RV.ReceptValid_Value * interval '1 day') >= (select dt from mv))) or
			(RV.ReceptValidType_id = 2 and ((ER.EvnRecept_setDate + RV.ReceptValid_Value * 30 * interval '1 day') >= (select dt from mv)))
	) and
	((ER.EvnRecept_otpDT is null) and (ER.EvnRecept_obrDT is null)) 
)
then 'Выписан'
				when
((RV.ReceptValidType_id = 1 and
((ER.EvnRecept_setDate + RV.ReceptValid_Value * interval '1 day') < (select dt from mv))) or
(RV.ReceptValidType_id = 2 and ((ER.EvnRecept_setDate + RV.ReceptValid_Value * 30 * interval '1 day') < (select dt from mv))))
then 'Просрочен'
else ''
			end as \"ReceptDelayType_Name\",
			(select Org_Name from v_OrgFarmacy ER_OF where ER_OF.OrgFarmacy_id = ER.OrgFarmacy_oid limit 1) as \"OrgFarmacy_oName\",
			(
				case when ER.ReceptDelayType_id  > 0
				then (select ReceptDelayType_Name from ReceptDelayType ER_RDT where ER_RDT.ReceptDelayType_id = ER.ReceptDelayType_id) || coalesce(' '||coalesce((select Org_Name from v_OrgFarmacy ER_OF where ER_OF.OrgFarmacy_id = ER.OrgFarmacy_oid), '') || case when ER.ReceptDelayType_id = 3 and Wr.ReceptWrong_Decr is not null then ' (' || Wr.ReceptWrong_Decr || ')' else '' end, '')
				else ''
				end
			) as \"Delay_info\",
			to_char(ER.EvnRecept_setDate, '{$callObject->dateTimeForm104}') as \"EvnRecept_setDate\",
			to_char(COALESCE(ER.EvnRecept_otpDT,RecOt.EvnRecept_otpDate), '{$callObject->dateTimeForm104}') as \"EvnRecept_otpDate\",
			to_char(COALESCE(ER.EvnRecept_obrDT,RecOt.EvnRecept_obrDate), '{$callObject->dateTimeForm104}') as \"EvnRecept_obrDate\",
			case when ((ER.EvnRecept_otpDT is not null) and (ER.EvnRecept_obrDT is not null)) then date_part('day', ER.EvnRecept_otpDT - ER.EvnRecept_obrDT) else null end as \"ServePeriod\",
			RTRIM(wdcit.WhsDocumentCostItemType_Name) as \"WhsDocumentCostItemType_Name\",
			RTRIM(drugFin.DrugFinance_Name) as \"DrugFinance_Name\",
			coalesce(RecOtovSum.recSum,0) as \"ReceptOtovSum\",
			RTRIM(ER.EvnRecept_Ser) as \"EvnRecept_Ser\",
			RTRIM(ER.EvnRecept_Num) as \"EvnRecept_Num\",
			ROUND(ER.EvnRecept_Kolvo, 3) as \"EvnRecept_Kolvo\",
			(RTRIM(ERMP.Person_Fio) || ' (' || coalesce(Lpu.Lpu_Nick,'') || ')') as \"MedPersonal_Fio\",
			RTRIM(COALESCE(ERDrugRls.Drug_Name, ERDrug.Drug_Name, DCM.DrugComplexMnn_RusName, ER.EvnRecept_ExtempContents)) as \"Drug_Name\",
			RTRIM(DrugNomen.DrugNomen_Code) as \"DrugNomen_Code\",
			{$isBDZ}
			CASE WHEN (ER.EvnRecept_IsSigned = 2 or (ER.pmUser_signID is not null and ER.EvnRecept_signDT is not null)) THEN 'true' ELSE 'false' END as \"EvnRecept_IsSigned\",
			CASE WHEN ER.EvnRecept_IsPrinted = 2 THEN 'true' ELSE 'false' END as \"EvnRecept_IsPrinted\",
			RecF.ReceptForm_id as \"ReceptForm_id\",
			RecF.ReceptForm_Code as \"ReceptForm_Code\",
			RecT.ReceptType_Code as \"ReceptType_Code\",
			RecT.ReceptType_Name as \"ReceptType_Name\",
			CASE WHEN ER_RDT.ReceptDelayType_Code = 4 THEN 'true' ELSE 'false' END as \"Recept_MarkDeleted\",
			ER.ReceptRemoveCauseType_id as \"ReceptRemoveCauseType_id\",
			pmUC.PMUser_Name as \"PMUser_Name\",
			coalesce(wdcit.MorbusType_id, 1) as \"MorbusType_id\",
			DocUc.DocumentUc_id as \"DocumentUc_id\",
			case
				when ER.ReceptDelayType_id IS Not null then null -- Рецепт имеет статус
				when RV.ReceptValid_Code = 1 then to_char(dateadd('month', 1, ER.EvnRecept_setDate), '{$callObject->dateTimeForm104}') -- ReceptValid_Name = 'Месяц'
				when RV.ReceptValid_Code = 2 then to_char(dateadd('month', 3, ER.EvnRecept_setDate), '{$callObject->dateTimeForm104}') -- ReceptValid_Name = 'Три месяца'
				when RV.ReceptValid_Code = 3 then to_char(dateadd('day', 14, ER.EvnRecept_setDate), '{$callObject->dateTimeForm104}') -- ReceptValid_Name = '14 дней'
				when RV.ReceptValid_Code = 4 then to_char(dateadd('day', 5, ER.EvnRecept_setDate), '{$callObject->dateTimeForm104}') -- when ReceptValid_Name = '5 дней'
				when RV.ReceptValid_Code = 5 then to_char(dateadd('month', 2, ER.EvnRecept_setDate), '{$callObject->dateTimeForm104}') -- ReceptValid_Name = 'Два месяца'
				when RV.ReceptValid_Code = 7 then to_char(dateadd('day', 10, ER.EvnRecept_setDate), '{$callObject->dateTimeForm104}') -- ReceptValid_Name = '10 дней'
				when RV.ReceptValid_Code = 8 then to_char(dateadd('day', 60, ER.EvnRecept_setDate), '{$callObject->dateTimeForm104}') -- ReceptValid_Name = '60 дней'
				when RV.ReceptValid_Code = 9 then to_char(dateadd('day', 30, ER.EvnRecept_setDate), '{$callObject->dateTimeForm104}') -- ReceptValid_Name = '30 дней'
				when RV.ReceptValid_Code = 10 then to_char(dateadd('day', 90, ER.EvnRecept_setDate), '{$callObject->dateTimeForm104}') -- ReceptValid_Name = '90 дней'
				when RV.ReceptValid_Code = 11 then to_char(dateadd('day', 15, ER.EvnRecept_setDate), '{$callObject->dateTimeForm104}') -- ReceptValid_Name = '15 дней'
				else
 to_char(ER.EvnRecept_setDate, '{$callObject->dateTimeForm104}')
			end as \"EvnRecept_DateCtrl\",
			case
				when ER.ReceptDelayType_id IS Not null then 0 -- Рецепт имеет статус
				when RV.ReceptValid_Code = 1 and dateadd('month', 1, ER.EvnRecept_setDate) < GETDATE() then 1 -- ReceptValid_Name = 'Месяц'
				when RV.ReceptValid_Code = 2 and dateadd('month', 3, ER.EvnRecept_setDate) < GETDATE() then 1 -- ReceptValid_Name = 'Три месяца'
				when RV.ReceptValid_Code = 3 and dateadd('day', 14, ER.EvnRecept_setDate) < GETDATE() then 1 -- ReceptValid_Name = '14 дней'
				when RV.ReceptValid_Code = 4 and dateadd('day', 5, ER.EvnRecept_setDate)  < GETDATE() then 1 -- when ReceptValid_Name = '5 дней'
				when RV.ReceptValid_Code = 5 and dateadd('month', 2, ER.EvnRecept_setDate)  < GETDATE() then 1 -- ReceptValid_Name = 'Два месяца'
				when RV.ReceptValid_Code = 7 and dateadd('day', 10, ER.EvnRecept_setDate)  < GETDATE() then 1 -- ReceptValid_Name = '10 дней'
				when RV.ReceptValid_Code = 8 and dateadd('day', 60, ER.EvnRecept_setDate)  < GETDATE() then 1 -- ReceptValid_Name = '60 дней'
				when RV.ReceptValid_Code = 9 and dateadd('day', 30, ER.EvnRecept_setDate)  < GETDATE() then 1 -- ReceptValid_Name = '30 дней'
				when RV.ReceptValid_Code = 10 and dateadd('day', 90, ER.EvnRecept_setDate)  < GETDATE() then 1 -- ReceptValid_Name = '90 дней'
				when RV.ReceptValid_Code = 11 and dateadd('day', 15, ER.EvnRecept_setDate)  < GETDATE() then 1 -- ReceptValid_Name = '15 дней'
				else 0
			end as \"EvnRecept_Shelf\",
			case when Wr.ReceptWrong_id is not null then 1 else 0 end as \"EvnRecept_IsWrong\",
			case when ((RV.ReceptValidType_id = 1 and ((ER.EvnRecept_setDate + RV.ReceptValid_Value * interval '1 day') >= (select dt from mv))) or (RV.ReceptValidType_id = 2 and ((ER.EvnRecept_setDate + (RV.ReceptValid_Value * 30) * interval '1 day') >= (select dt from mv))))
				then 0
				else 1
			end as \"inValidRecept\"
		";
		if (allowPersonEncrypHIV($data['session'])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') else null end as \"Person_deadDT\"
			";
		} else {
			$query .= "
				,RTRIM(PS.Person_SurName) as \"Person_Surname\"
				,RTRIM(PS.Person_FirName) as \"Person_Firname\"
				,RTRIM(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
				,to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') as \"Person_deadDT\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_EvnReceptGeneral(Search_model $callObject, $data, $isBDZ)
	{
		$query = "
			ERG.EvnReceptGeneral_id as \"EvnRecept_id\",
			ERG.Person_id as \"Person_id\",
			ERG.PersonEvn_id as \"PersonEvn_id\",
			ERG.Server_id as \"Server_id\",
			ERG.Drug_id as \"Drug_id\",
			ERG.Drug_rlsid as \"Drug_rlsid\",
			ERG.DrugComplexMnn_id as \"DrugComplexMnn_id\",
			ERG.ReceptDelayType_id as \"ReceptDelayType_id\",
			ERG.OrgFarmacy_oid as \"OrgFarmacy_oid\",
			case 
				when ERG.ReceptDelayType_id in ('1','2','3','5') then ER_RDT.ReceptDelayType_Name 
				when (
ERG.ReceptDelayType_id is null and
(
	(RV.ReceptValidType_id = 1 and ((ERG.EvnReceptGeneral_setDate + RV.ReceptValid_Value * interval '1 day') >= (select dt from mv))) or
	(RV.ReceptValidType_id = 2 and ((ERG.EvnReceptGeneral_setDate + RV.ReceptValid_Value * 30 * interval '1 day') >= (select dt from mv)))
) and
(ERG.EvnReceptGeneral_otpDT is null and ERG.EvnReceptGeneral_obrDT is null) 
				) then 'Выписан'
				when ((RV.ReceptValidType_id = 1 and ((ERG.EvnReceptGeneral_setDate + RV.ReceptValid_Value * interval '1 day') < (select dt from mv))) or (RV.ReceptValidType_id = 2 and ((ERG.EvnReceptGeneral_setDate + RV.ReceptValid_Value * 30 * interval '1 day') < (select dt from mv)))) then 'Просрочен'
				else ''
			end as \"ReceptDelayType_Name\",
			(select Org_Name from v_OrgFarmacy ER_OF where ER_OF.OrgFarmacy_id = ERG.OrgFarmacy_oid limit 1) as \"OrgFarmacy_oName\",
			(
				case when ERG.ReceptDelayType_id  > 0
				then (select ReceptDelayType_Name from ReceptDelayType ER_RDT where ER_RDT.ReceptDelayType_id = ERG.ReceptDelayType_id limit 1) || coalesce(' '||(select Org_Name from v_OrgFarmacy ER_OF where ER_OF.OrgFarmacy_id = ERG.OrgFarmacy_oid limit 1),'')
				else ''
				end
			) as \"Delay_info\",
			to_char(ERG.EvnReceptGeneral_setDate, '{$callObject->dateTimeForm104}') as \"EvnRecept_setDate\",
			to_char(COALESCE(ERG.EvnReceptGeneral_otpDT,RecOt.EvnRecept_otpDate), '{$callObject->dateTimeForm104}') as \"EvnRecept_otpDate\",
			to_char(COALESCE(ERG.EvnReceptGeneral_obrDT,RecOt.EvnRecept_obrDate), '{$callObject->dateTimeForm104}') as \"EvnRecept_obrDate\",
			case when (ERG.EvnReceptGeneral_otpDT is not null and ERG.EvnReceptGeneral_obrDT is not null) then date_part('day', ERG.EvnReceptGeneral_otpDT - ERG.EvnReceptGeneral_obrDT) else null end as \"ServePeriod\",
			RTRIM(wdcit.WhsDocumentCostItemType_Name) as \"WhsDocumentCostItemType_Name\",
			RTRIM(drugFin.DrugFinance_Name) as \"DrugFinance_Name\",
			coalesce(RecOtovSum.recSum,0) as \"ReceptOtovSum\",
			RTRIM(ERG.EvnReceptGeneral_Ser) as \"EvnRecept_Ser\",
			RTRIM(ERG.EvnReceptGeneral_Num) as \"EvnRecept_Num\",
			ROUND(ERG.EvnReceptGeneral_Kolvo, 3) as \"EvnRecept_Kolvo\",
			RTRIM(ERMP.Person_Fio) as \"MedPersonal_Fio\",
			RTRIM(COALESCE(ERDrugRls.Drug_Name, ERDrug.Drug_Name, DCM.DrugComplexMnn_RusName, ERG.EvnReceptGeneral_ExtempContents)) as \"Drug_Name\",
			RTRIM(DrugNomen.DrugNomen_Code) as \"DrugNomen_Code\",
			{$isBDZ}
			CASE WHEN ERG.EvnReceptGeneral_IsSigned = 2 THEN 'true' ELSE 'false' END as \"EvnRecept_IsSigned\",
			RecF.ReceptForm_id as \"ReceptForm_id\",
			RecF.ReceptForm_Code as \"ReceptForm_Code\",
			ERG.ReceptRemoveCauseType_id as \"ReceptRemoveCauseType_id\",
			coalesce(wdcit.MorbusType_id, 1) as \"MorbusType_id\",
			DocUc.DocumentUc_id as \"DocumentUc_id\",
			case when(
				(RV.ReceptValidType_id = 1 and ((ERG.EvnReceptGeneral_setDate + RV.ReceptValid_Value * interval '1 day') >= (select dt from mv))) or
				(RV.ReceptValidType_id = 2 and ((ERG.EvnReceptGeneral_setDate + RV.ReceptValid_Value * 30 * interval '1 day') >= (select dt from mv)))
			) then 0 else 1 end as \"inValidRecept\"
		";
		if (allowPersonEncrypHIV($data['session'])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') else null end as \"Person_deadDT\"
			";
		} else {
			$query .= "
				,RTRIM(PS.Person_SurName) as \"Person_Surname\"
				,RTRIM(PS.Person_FirName) as \"Person_Firname\"
				,RTRIM(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
				,to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') as \"Person_deadDT\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_EvnUslugaPar(Search_model $callObject, $data, &$queryParams)
	{
		$accessType = "EUP.Lpu_id = :Lpu_id";
		//$accessType .= " and (ED.EvnDirection_IsReceive = 2 OR (ED.MedService_id IS NULL and not exists(select EvnFuncRequest_id from v_EvnFuncRequest where EvnFuncRequest_pid = EUP.EvnUslugaPar_id limit 1)))"; // не даём редактировать услуги связанные с направлением в лабораторию и с заявкой ФД
		if (!isSuperAdmin() && empty($data["session"]["isMedStatUser"]) && !empty($data["session"]["medpersonal_id"])) {
			$accessType .= " and coalesce(EUP.MedPersonal_id, :user_MedPersonal_id) = :user_MedPersonal_id";
			$queryParams["user_MedPersonal_id"] = $data["session"]["medpersonal_id"];
		}
		$result_deviation = (isset($data["SignalInfo"]) && $data["SignalInfo"] == 1) ? "doc.XmlTemplate_HtmlTemplate as \"XmlTemplate_HtmlTemplate\"," : "";
		$query = "
			case when {$accessType} then 'edit' else 'view' end as \"accessType\",
			doc.EvnXml_id as \"EvnXml_id\",
			{$result_deviation}
			EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
			EvnUslugaPar_pid as \"EvnUslugaPar_pid\",
			EUP.Person_id as \"Person_id\",
			EUP.PersonEvn_id as \"PersonEvn_id\",
			EUP.Server_id as \"Server_id\",
			to_char(EUP.EvnUslugaPar_setDate, '{$callObject->dateTimeForm104}') as \"EvnUslugaPar_setDate\",
			coalesce(EUP.EvnUslugaPar_IsSigned, 1) as \"EvnUslugaPar_IsSigned\",
			RTRIM(LS.LpuSection_Name) as \"LpuSection_Name\",
			RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
			lpu.Lpu_Name as \"Lpu_Name\",
			CASE WHEN EUP.UslugaComplex_id IS NULL THEN RTRIM(Usluga.Usluga_Code) ELSE RTRIM(UslugaComplex.UslugaComplex_Code) END as \"Usluga_Code\",
			CASE WHEN EUP.UslugaComplex_id IS NULL THEN RTRIM(Usluga.Usluga_Name) ELSE RTRIM(UslugaComplex.UslugaComplex_Name) END as \"Usluga_Name\",
			RTRIM(PT.PayType_Name) as \"PayType_Name\",
			coalesce(EUP.EvnUslugaPar_Kolvo, 0) as \"EvnUslugaPar_Kolvo\",
			coalesce(PostMed.PostMed_Name, '') as \"PostMed_Name\",
			to_char(ecp.EvnCostPrint_setDT, '{$callObject->dateTimeForm104}') as \"EvnCostPrint_setDT\",
			case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as \"EvnCostPrint_IsNoPrintText\",
			coalesce(Org.Org_Nick, LD_sid.Lpu_nick, v_Lpu_org_1.Lpu_Nick, v_Lpu_org_2.Lpu_Nick, Org_3.Org_Nick) as \"Referral_Org_Nick\", -- направившая МО
			(select LpuSection_Name from v_LpuSection where LpuSection_id = EUP.LpuSection_did) as \"LSD\"
		";
		if (allowPersonEncrypHIV($data['session'])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
				,case when PEH.PersonEncrypHIV_id is null then dbo.Age2(PS.Person_BirthDay, (select dt from mv)) else null end as \"Person_Age\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') else null end as \"Person_deadDT\"
			";
		} else {
			$query .= "
				,RTRIM(PS.Person_SurName) as \"Person_Surname\"
				,RTRIM(PS.Person_FirName) as \"Person_Firname\"
				,RTRIM(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
				,dbo.Age2(PS.Person_BirthDay, (select dt from mv))  as \"Person_Age\"
				,to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') as \"Person_deadDT\"
			";
		}
		if ($callObject->regionNick == "kz") {
			$query .= "
				,CASE WHEN air.AISResponse_id is not null THEN 'true' ELSE 'false' END as \"toAis25\"
				,CASE WHEN air9.AISResponse_id is not null THEN 'true' ELSE 'false' END as \"toAis259\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_WorkPlacePolkaReg(Search_model $callObject, $data, $isBDZ)
	{
		$query = "
			PC.PersonCard_id as \"PersonCard_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			CASE WHEN PS.Person_DeadDT is not null THEN 'true' ELSE 'false' END as \"Person_IsDead\",
			PC.PersonCard_Code as \"PersonCard_Code\",
			coalesce('<a href=''#'' onClick=''getWnd(\"swPolisInfoWindow\").show({Person_id:' || PS.Person_id || '});''>'|| case when PLS.PolisType_id = 4 and coalesce(PS.Person_EdNum, '') != '' then PS.Person_EdNum else coalesce(PS.Polis_Ser, '') || ' ' || coalesce(PS.Polis_Num, '') end ||'</a>','') as \"Person_PolisInfo\",
			(select coalesce(Person_Inn,'') from v_PersonState where Person_id = ps.Person_id) as \"Person_Inn\",
			case
			    when dbo.getPersonPhones(PS.Person_id, '<br />') != '' then coalesce('<a href=''#'' onClick=''getWnd(\"swPhoneInfoWindow\").show({Person_id:' || PS.Person_id || '});''>'|| dbo.getPersonPhones(PS.Person_id, '<br />') ||'</a>','')
			    else '<a href=''#'' onClick=''getWnd(\"swPhoneInfoWindow\").show({Person_id:' || PS.Person_id || '});''>'|| 'Отсутствует' ||'</a>'
			end as \"Person_Phone\",
			case when PS.Person_id is not null then dbo.Age2(PS.Person_BirthDay, (select dt from mv)) end as \"Person_Age\",
			coalesce(AttachLpu.Lpu_Nick, 'Не прикреплен') as \"AttachLpu_Name\",
			coalesce(AttachLpu.Lpu_id, 0) as \"AttachLpu_id\",
			to_char(PC.PersonCard_begDate, '{$callObject->dateTimeForm104}') as \"PersonCard_begDate\",
			to_char(PC.PersonCard_endDate, '{$callObject->dateTimeForm104}') as \"PersonCard_endDate\",
			PC.LpuAttachType_Name as \"LpuAttachType_Name\",
			PC.LpuRegionType_Name as \"LpuRegionType_Name\",
			LR.LpuRegion_Name as \"LpuRegion_Name\",
			CASE WHEN coalesce(PC.PersonCard_IsAttachCondit, 1) = 2 then 'true' else 'false' end as \"PersonCard_IsAttachCondit\",
			CASE WHEN PC.PersonCardAttach_id IS NULL then 'false' else 'true' end as \"PersonCardAttach\",
			CASE WHEN PS.Person_IsRefuse = 1 THEN 'true' ELSE 'false' END as \"Person_IsRefuse\",
			CASE WHEN PRef.PersonRefuse_IsRefuse = 2 THEN 'true' ELSE 'false' END as \"Person_NextYearRefuse\",
			CASE WHEN PS.Person_IsFedLgot = 1 THEN 'true' ELSE 'false' END as \"Person_IsFedLgot\",
			CASE WHEN PS.Person_IsRegLgot = 1 THEN 'true' ELSE 'false' END as \"Person_IsRegLgot\",
			CASE WHEN disp.OwnLpu = 1 THEN 'true' ELSE CASE WHEN disp.OwnLpu is not null THEN 'gray' ELSE 'false' END END as \"Person_Is7Noz\",
			{$isBDZ}
			coalesce(paddr.Address_Nick, paddr.Address_Address) as \"Person_PAddress\",
			coalesce(uaddr.Address_Nick, uaddr.Address_Address) as \"Person_UAddress\"
		";
		if (allowPersonEncrypHIV($data['session'])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') else null end as \"Person_deadDT\"
			";
		} else {
			$query .= "
				,RTRIM(PS.Person_SurName) as \"Person_Surname\"
				,RTRIM(PS.Person_FirName) as \"Person_Firname\"
				,RTRIM(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
				,to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') as \"Person_deadDT\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_PersonCard(Search_model $callObject, $data, $isBDZ, &$filter)
	{
		$query = "
			PC.PersonCard_id as \"PersonCard_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			CASE WHEN PS.Person_IsDead = 2 THEN 'true' ELSE 'false' END as \"Person_IsDead\",
			PC.PersonCard_Code as \"PersonCard_Code\",
			dbo.getPersonPhones(PS.Person_id, '<br />') as \"Person_Phone\",
			coalesce(AttachLpu.Lpu_Nick, 'Не прикреплен') as \"AttachLpu_Name\",
			to_char(PC.PersonCard_begDate, '{$callObject->dateTimeForm104}') as \"PersonCard_begDate\",
			to_char(PC.PersonCard_endDate, '{$callObject->dateTimeForm104}') as \"PersonCard_endDate\",
			coalesce(PC.LpuAttachType_id::text, '') as \"LpuAttachType_id\",
			PC.LpuAttachType_Name as \"LpuAttachType_Name\",
			PC.LpuRegionType_Name as \"LpuRegionType_Name\",
			LR.LpuRegion_Name as \"LpuRegion_Name\",
			coalesce(LR_Fap.LpuRegion_Name,'') as \"LpuRegion_FapName\",
			PACLT.AmbulatCardLocatType_Name as \"AmbulatCardLocatType_Name\",
			PACLT.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
			CASE WHEN coalesce(PC.PersonCard_IsAttachCondit, 1) = 2 then 'true' else 'false' end as \"PersonCard_IsAttachCondit\",
			CASE WHEN PC.PersonCardAttach_id IS NULL then 'false' else 'true' end as \"PersonCardAttach\",
			CASE WHEN PS.Person_IsRefuse = 1 THEN 'true' ELSE 'false' END as \"Person_IsRefuse\",
			CASE WHEN PRef.PersonRefuse_IsRefuse = 2 THEN 'true' ELSE 'false' END as \"Person_NextYearRefuse\",
			CASE WHEN PS.Person_IsFedLgot = 1 THEN 'true' ELSE 'false' END as \"Person_IsFedLgot\",
			CASE WHEN PS.Person_IsRegLgot = 1 THEN 'true' ELSE 'false' END as \"Person_IsRegLgot\",
			CASE WHEN disp.OwnLpu = 1 THEN 'true' ELSE CASE WHEN disp.OwnLpu is not null THEN 'gray' ELSE 'false' END END as \"Person_Is7Noz\",
			{$isBDZ}
			coalesce(paddr.Address_Nick, paddr.Address_Address) as \"Person_PAddress\",
			coalesce(uaddr.Address_Nick, uaddr.Address_Address) as \"Person_UAddress\"
		";
		if (!empty($data["dontShowUnknowns"])) {
			$filter .= " and coalesce(PS.Person_IsUnknown,1) != 2 ";
		}
		if (allowPersonEncrypHIV($data["session"])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') else null end as \"Person_deadDT\"
			";
		} else {
			$query .= "
				,RTRIM(PS.Person_SurName) as \"Person_Surname\"
				,RTRIM(PS.Person_FirName) as \"Person_Firname\"
				,RTRIM(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
				,to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') as \"Person_deadDT\"
			";
		}
		if ($callObject->getRegionNick() == "ufa" and $data["hasObrTalonMse"] == "on") {
			$query .= "
				,OBTMSE.InvalidGroupType_Name as \"MseInvalidGroupType_Name\"
				,OBTMSE.Diag_Code as \"MseDiag_Code\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_PersonCallCenter(Search_model $callObject, $data, $isBDZ, &$filter)
	{
		$query = "
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			CASE WHEN PS.Person_DeadDT is not null  THEN 'true' ELSE 'false' END as \"Person_IsDead\",
			case when PersonCard.Lpu_id = :Lpu_id then PersonCard.PersonCard_Code else null end as \"PersonCard_Code\",
			coalesce('<a href=''#'' onClick=''getWnd(\"swPolisInfoWindow\").show({Person_id:' || PS.Person_id || '});''>'|| case when PLS.PolisType_id = 4 and coalesce(PS.Person_EdNum, '') != '' then PS.Person_EdNum else coalesce(PS.Polis_Ser, '') || ' ' || coalesce(PS.Polis_Num, '') end ||'</a>','') as \"Person_PolisInfo\",
			case
			    when dbo.getPersonPhones(PS.Person_id, '<br />') != '' then coalesce('<a href=''#'' onClick=''getWnd(\"swPhoneInfoWindow\").show({Person_id:' || PS.Person_id || '});''>'|| dbo.getPersonPhones(PS.Person_id, '<br />') ||'</a>','')
			    else '<a href=''#'' onClick=''getWnd(\"swPhoneInfoWindow\").show({Person_id:' || PS.Person_id || '});''>'|| 'Отсутствует' ||'</a>'
			end as \"Person_Phone\",
			case when PS.Person_id is not null then dbo.Age2(PS.Person_BirthDay, (select dt from mv)) end as \"Person_Age\",
			coalesce(AttachLpu.Lpu_Nick, 'Не прикреплен') as \"AttachLpu_Name\",
			to_char(PersonCard.PersonCard_begDate, '{$callObject->dateTimeForm104}') as \"PersonCard_begDate\",
			to_char(PersonCard.PersonCard_endDate, '{$callObject->dateTimeForm104}') as \"PersonCard_endDate\",
			PersonCard.LpuAttachType_Name as \"LpuAttachType_Name\",
			PersonCard.LpuRegionType_Name as \"LpuRegionType_Name\",
			LR.LpuRegion_Name as \"LpuRegion_Name\",
			coalesce(LR_Fap.LpuRegion_Name,'') as \"LpuRegion_FapName\",
			NA.NewslatterAccept_id as \"NewslatterAccept_id\",
			coalesce(to_char(NA.NewslatterAccept_begDate, '{$callObject->dateTimeForm104}'), 'Отсутствует') as \"NewslatterAccept\",
			CASE
				WHEN PersonCard.PersonCard_IsAttachCondit = 1 then 'false'
				WHEN PersonCard.PersonCard_IsAttachCondit = 2 then 'true'
				ELSE null
			end as \"PersonCard_IsAttachCondit\",
			CASE WHEN PersonCard.PersonCardAttach_id IS NULL then 'false' else 'true' end as \"PersonCardAttach\",
			CASE WHEN PS.Person_IsRefuse = 1 THEN 'true' ELSE 'false' END as \"Person_IsRefuse\",
			CASE WHEN PRef.PersonRefuse_IsRefuse = 2 THEN 'true' ELSE 'false' END as \"Person_NextYearRefuse\",
			CASE WHEN PS.Person_IsFedLgot = 1 THEN 'true' ELSE 'false' END as \"Person_IsFedLgot\",
			CASE WHEN PS.Person_IsRegLgot = 1 THEN 'true' ELSE 'false' END as \"Person_IsRegLgot\",
			CASE WHEN disp.OwnLpu = 1 THEN 'true' WHEN disp.OwnLpu is not null THEN 'gray' ELSE 'false' END as \"Person_Is7Noz\",
			{$isBDZ}
			coalesce(paddr.Address_Nick, paddr.Address_Address) as \"Person_PAddress\",
			coalesce(uaddr.Address_Nick, uaddr.Address_Address) as \"Person_UAddress\"
		";
		if (!empty($data["dontShowUnknowns"])) {
			$filter .= " and coalesce(PS.Person_IsUnknown,1) != 2 ";
		}
		if (allowPersonEncrypHIV($data["session"])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') else null end as \"Person_deadDT\"
			";
		} else {
			$query .= "
				,RTRIM(PS.Person_SurName) as \"Person_Surname\"
				,RTRIM(PS.Person_FirName) as \"Person_Firname\"
				,RTRIM(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
				,to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') as \"Person_deadDT\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_PersonCardStateDetail(Search_model $callObject)
	{
		return "
			PCSD.PersonCard_Code as \"PersonCard_Code\",
			PCSD.PersonCard_id as \"PersonCard_id\",
			PCSD.Person_id as \"Person_id\",
			PCSD.Server_id as \"Server_id\",
			rtrim(pcc.Person_SurName) as \"Person_Surname\",
			rtrim(pcc.Person_FirName) as \"Person_Firname\",
			rtrim(pcc.Person_SecName) as \"Person_Secname\",
			to_char(pcc.Person_BirthDay, '{$callObject->dateTimeForm104}') as \"Person_BirthDay\",
			to_char(PCSD.PersonCard_begDate, '{$callObject->dateTimeForm104}') as \"PersonCard_begDate\",
			to_char(PCSD.PersonCard_endDate, '{$callObject->dateTimeForm104}') as \"PersonCard_endDate\",
			lrt.LpuRegionType_Name as \"LpuRegionType_Name\",
			lr.LpuRegion_Name as \"LpuRegion_Name\",
			coalesce(ccc.CardCloseCause_Name, '') as \"CardCloseCause_Name\",
			case when coalesce(PCSD.PersonCard_IsAttachCondit, 1) = 1 then 'false' else 'true' end as \"PersonCard_IsAttachCondit\",
			coalesce(attcard.LpuRegion_Name, '') as \"ActiveLpuRegion_Name\",
			coalesce(rtrim(attcard.Lpu_Nick), '') as \"ActiveLpu_Nick\",
			coalesce(rtrim(Address.Address_Nick), '') as \"PAddress_Address\",
			coalesce(rtrim(Address1.Address_Nick), '') as \"UAddress_Address\",
			coalesce(rtrim(dmsOrgSmo.OrgSMO_Nick), '') as \"dmsOrgSmo_Nick\",
			coalesce(rtrim(omsOrgSmo.OrgSMO_Nick), '') as \"omsOrgSmo_Nick\",
			case when ps.Server_pid = 0 and Pols.Polis_id is not null then 'true' else 'false' end as \"isBDZ\"
		";
	}

	public static function searchData_SearchFormType_PersonDisp(Search_model $callObject, $data)
	{
		$query = "
			PD.PersonDisp_id as \"PersonDisp_id\",
			PD.Person_id as \"Person_id\",
			PD.Server_id as \"Server_id\",
			to_char(PD.PersonDisp_begDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_begDate\",
			to_char(PD.PersonDisp_endDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_endDate\",
			to_char(coalesce(oapdv.PersonDispVizit_NextDate, PD.PersonDisp_NextDate), '{$callObject->dateTimeForm104}') as \"PersonDisp_NextDate\",
			to_char(
				case
when LD.PersonDisp_LastDate is not null and lapdv.PersonDispVizit_NextFactDate is not null
then case when LD.PersonDisp_LastDate > lapdv.PersonDispVizit_NextFactDate then LD.PersonDisp_LastDate else lapdv.PersonDispVizit_NextFactDate end
else coalesce(lapdv.PersonDispVizit_NextFactDate,LD.PersonDisp_LastDate)
				end, '{$callObject->dateTimeForm104}') as \"PersonDisp_LastDate\",
			dg1.Diag_Code as \"Diag_Code\",
			mp1.Person_Fio as \"MedPersonal_FIO\",
			mph_last.MedPersonal_FIO_last as \"MedPersonalHist_FIO\",
			lpus1.LpuSection_Name as \"LpuSection_Name\",
			scks.Sickness_Name as \"Sickness_Name\",
			rtrim(lpu1.Lpu_Nick) as \"Lpu_Nick\",
			CASE WHEN PD.Lpu_id = :Lpu_id THEN 2 ELSE 1 END as \"IsOurLpu\",
			CASE WHEN noz.isnoz = 1
				THEN 'true'
				ELSE CASE WHEN noz.isnoz is not null
	THEN 'gray'
	ELSE 'false'
END
			END as \"Is7Noz\",
			coalesce(PCA.LpuRegion_Name,'') as \"LpuRegion_Name\"
		";
		if (allowPersonEncrypHIV($data['session'])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
			";
		} else {
			$query .= "
				,RTRIM(PS.Person_SurName) as \"Person_Surname\"
				,RTRIM(PS.Person_FirName) as \"Person_Firname\"
				,RTRIM(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_PersonPrivilege(Search_model $callObject, $data, $isBDZ)
	{
		$query = "
			PP.Lpu_id as \"Lpu_id\",
			PS.Server_id as \"Server_id\",
			PS.Lpu_id as \"Lpu_did\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			PS.Person_id as \"Person_id\",
			PP.PersonPrivilege_id as \"PersonPrivilege_id\",
			PT.PrivilegeType_id as \"PrivilegeType_id\",
			RF.ReceptFinance_id as \"ReceptFinance_id\",
			RF.ReceptFinance_Code as \"ReceptFinance_Code\",
			PT.PrivilegeType_Code as \"PrivilegeType_Code\",
			RTRIM(coalesce(PT.PrivilegeType_VCode, PT.PrivilegeType_Code::varchar)) as \"PrivilegeType_VCode\",
			to_char(PP.PersonPrivilege_begDate, '{$callObject->dateTimeForm104}') as \"Privilege_begDate\",
			to_char(PP.PersonPrivilege_endDate, '{$callObject->dateTimeForm104}') as \"Privilege_endDate\",
			CASE WHEN PS.Person_IsRefuse = 1 and PT_WDCIT.WhsDocumentCostItemType_Nick = 'fl' THEN 'true' ELSE 'false' END as \"Person_IsRefuse\",
			CASE WHEN PS.Person_IsFedLgot = 1 THEN 'true' ELSE 'false' END as \"Person_IsFedLgot\",
			CASE WHEN PS.Person_IsRegLgot = 1 THEN 'true' ELSE 'false' END as \"Person_IsRegLgot\",
			CASE WHEN PS.Person_Is7Noz = 1 THEN 'true' ELSE 'false' END as \"Person_Is7Noz\",
			{$isBDZ}
			coalesce(PUAdd.Address_Nick, PUAdd.Address_Address) as \"Person_Address\",
			to_char(PP.PersonPrivilege_delDT, '{$callObject->dateTimeForm104} {$callObject->dateTimeForm108}')||rtrim(UserDel.PMUser_Login)||', '||rtrim(UserDel.PMUser_Name) as \"PersonPrivilege_deletedInfo\",
			coalesce(PrivCT.PrivilegeCloseType_Name, '') as \"PrivilegeCloseType_Name\",
			coalesce(DocPriv.DocumentPrivilege_Data, '') as \"DocumentPrivilege_Data\"
		";
		$query .= ($data["session"]["region"]["nick"] == "krym")
			? ",PCardChecks.cntPC as \"cntPC\""
			: ",0 as \"cntPC\"";
		$query .= ($callObject->regionNick == "kz")
			? ",RTRIM(PT.PrivilegeType_Name) || coalesce('<br>' || SCPT.SubCategoryPrivType_Name, '') as \"PrivilegeType_Name\""
			: ",RTRIM(PT.PrivilegeType_Name) || coalesce('<br>Диагноз: ' || Diag.Diag_Code || ' ' || Diag.Diag_Name, '') as \"PrivilegeType_Name\"";
		if (allowPersonEncrypHIV($data["session"])) {
			$query .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as \"Person_Firname\"
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as \"Person_Secname\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') else null end as \"Person_Birthday\"
				,case when PEH.PersonEncrypHIV_id is null then dbo.Age2(PS.Person_BirthDay, (select dt from mv)) else null end as \"Person_Age\"
				,case when PEH.PersonEncrypHIV_id is null then to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') else null end as \"Person_deadDT\"
			";
		} else {
			$query .= "
				,RTRIM(PS.Person_SurName) as \"Person_Surname\"
				,RTRIM(PS.Person_FirName) as \"Person_Firname\"
				,RTRIM(PS.Person_SecName) as \"Person_Secname\"
				,to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
				,dbo.Age2(PS.Person_BirthDay, (select dt from mv)) as \"Person_Age\"
				,to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') as \"Person_deadDT\"
			";
		}
		return $query;
	}

	public static function searchData_SearchFormType_PersonPrivilegeWOW(Search_model $callObject)
	{
		return "
			PPW.PersonPrivilegeWOW_id as \"PersonPrivilegeWOW_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			rtrim(PS.Person_SurName) as \"Person_Surname\",
			rtrim(PS.Person_FirName) as \"Person_Firname\",
			rtrim(PS.Person_SecName) as \"Person_Secname\",
			UAdd.Address_Nick as \"ua_name\",
			PAdd.Address_Nick as \"pa_name\",
			Sex.Sex_Name as \"Sex_Name\",
			PS.Polis_Ser as \"Polis_Ser\",
			PS.Polis_Num as \"Polis_Num\",
			PTW.PrivilegeTypeWow_id as \"PrivilegeTypeWow_id\",
			PTW.PrivilegeTypeWOW_Name as \"PrivilegeTypeWOW_Name\",
			to_char(PS.Person_BirthDay, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
		";
	}

	public static function searchData_SearchFormType_RegisterSixtyPlus(Search_model $callObject)
	{
		return "
			RPlus.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			PS.Sex_id as \"Sex_id\",
			rtrim(PS.Person_SurName) as \"Person_Surname\",
			rtrim(PS.Person_FirName) as \"Person_Firname\",
			rtrim(PS.Person_SecName) as \"Person_Secname\",
			date_part('year', (select dt from mv) - PS.Person_BirthDay) as \"Person_Age\",
			RPlus.RegisterSixtyPlus_IMTMeasure as \"RegisterSixtyPlus_IMTMeasure\",
			RPlus.RegisterSixtyPlus_CholesterolMeasure as \"RegisterSixtyPlus_CholesterolMeasure\",
			RPlus.RegisterSixtyPlus_GlucoseMeasure as \"RegisterSixtyPlus_GlucoseMeasure\",
			to_char(RPlus.RegisterSixtyPlus_OAKsetDate, '{$callObject->dateTimeForm104}') as \"RegisterSixtyPlus_OAKsetDate\",
			to_char(RPlus.RegisterSixtyPlus_OAMsetDate, '{$callObject->dateTimeForm104}') as \"RegisterSixtyPlus_OAMsetDate\",
			to_char(RPlus.RegisterSixtyPlus_FluorographysetDate, '{$callObject->dateTimeForm104}') as \"RegisterSixtyPlus_FluorographysetDate\",
			to_char(RPlus.RegisterSixtyPlus_EKGsetDate, '{$callObject->dateTimeForm104}') as \"RegisterSixtyPlus_EKGsetDate\",
			to_char(RPlus.RegisterSixtyPlus_OnkoProfileDtBeg, '{$callObject->dateTimeForm104}') as \"RegisterSixtyPlus_OnkoProfileDtBeg\",
			RPlus.RegisterSixtyPlus_OnkoControlIsNeeded as \"RegisterSixtyPlus_OnkoControlIsNeeded\",
			RPlus.RegisterSixtyPlus_isSetProfileBSK as \"RegisterSixtyPlus_isSetProfileBSK\",
			RPlus.RegisterSixtyPlus_isSetProfileONMK as \"RegisterSixtyPlus_isSetProfileONMK\",
			RPlus.RegisterSixtyPlus_isSetProfileOKS as \"RegisterSixtyPlus_isSetProfileOKS\",
			RPlus.RegisterSixtyPlus_isSetProfileZNO as \"RegisterSixtyPlus_isSetProfileZNO\",
			RPlus.RegisterSixtyPlus_isSetProfileDiabetes as \"RegisterSixtyPlus_isSetProfileDiabetes\",
			RPlus.RegisterSixtyPlus_isSetPersonDisp as \"RegisterSixtyPlus_isSetPersonDisp\",
			IGT.InvalidGroupType_Name as \"InvalidGroupType_Name\",
			RPlus.Measure_id as \"Measure_id\",
			coalesce(rg.LpuRegionType_Name || ' №' || repeat(' ',2 - length(rg.LpuRegion_Name)) || rg.LpuRegion_Name, ' ') as \"uch\",
			LpuRegionFap.LpuRegion_Name as \"LpuRegion_fapid\",
			LpuRegionFap.LpuRegion_Descr as \"LpuRegion_Descr\"
		";
	}

	public static function searchData_SearchFormType_EvnPLWOW(Search_model $callObject)
	{
		return "
			EPW.EvnPLWOW_id as \"EvnPLWOW_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			rtrim(PS.Person_SurName) as \"Person_Surname\",
			rtrim(PS.Person_FirName) as \"Person_Firname\",
			rtrim(PS.Person_SecName) as \"Person_Secname\",
			PLS.Polis_Ser as \"Polis_Ser\",
			PLS.Polis_Num as \"Polis_Num\",
			PTW.PrivilegeTypeWow_id as \"PrivilegeTypeWow_id\",
			PTW.PrivilegeTypeWOW_Name as \"PrivilegeTypeWOW_Name\",
			to_char(PS.Person_BirthDay, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			to_char(EPW.EvnPLWOW_setDate, '{$callObject->dateTimeForm104}') as \"EvnPLWOW_setDate\",
			to_char(EPW.EvnPLWOW_disDate, '{$callObject->dateTimeForm104}') as \"EvnPLWOW_disDate\",
			EPW.EvnPLWOW_VizitCount as \"EvnPLWOW_VizitCount\",
			YesNo_Name as \"EvnPLWOW_IsFinish\"
		";
	}

	public static function searchData_SearchFormType_EvnDtpWound(Search_model $callObject)
	{
		return "
			EDW.EvnDtpWound_id as \"EvnDtpWound_id\",
			EDW.Person_id as \"Person_id\",
			EDW.PersonEvn_id as \"PersonEvn_id\",
			EDW.Server_id as \"Server_id\",
			to_char(EDW.EvnDtpWound_setDate, '{$callObject->dateTimeForm104}') as \"EvnDtpWound_setDate\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\"
		";
	}

	public static function searchData_SearchFormType_EvnDtpDeath(Search_model $callObject)
	{
		return "
			EDD.EvnDtpDeath_id as \"EvnDtpDeath_id\",
			EDD.Person_id as \"Person_id\",
			EDD.PersonEvn_id as \"PersonEvn_id\",
			EDD.Server_id as \"Server_id\",
			to_char(EDD.EvnDtpDeath_setDate, '{$callObject->dateTimeForm104}') as \"EvnDtpDeath_setDate\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			RTRIM(LTRIM(coalesce(PS.Person_SurName, '')|| ' ' || coalesce(PS.Person_FirName, '')|| ' ' || coalesce(PS.Person_SecName, ''))) as \"Person_Fio\",
			sex.Sex_Name as \"Person_Sex\",
			diag.Diag_Name as \"DiagDeath_Name\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			to_char(EDD.EvnDtpDeath_DeathDate, '{$callObject->dateTimeForm104}') as \"EvnDtpDeath_DeathDate\"
		";
	}

	public static function searchData_SearchFormType_OrphanRegistry(Search_model $callObject)
	{
		return "
			PR.PersonRegister_id as \"PersonRegister_id\",
			PR.Diag_id as \"Diag_id\",
			PR.Lpu_iid as \"Lpu_iid\",
			PR.MedPersonal_iid as \"MedPersonal_iid\",
			EN.EvnNotifyOrphan_id as \"EvnNotifyBase_id\",
			M.Morbus_id as \"Morbus_id\",
			MO.MorbusOrphan_id as \"MorbusOrphan_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			Diag.diag_FullName as \"Diag_Name\",
			PROUT.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
			PROUT.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
			to_char(PR.PersonRegister_setDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			to_char(PR.PersonRegister_disDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_disDate\"
		";
	}


	public static function searchData_SearchFormType_ACSRegistry(Search_model $callObject)
	{
		return "
			PR.PersonRegister_id as \"PersonRegister_id\",
			PR.Diag_id as \"Diag_id\",
			PR.Lpu_iid as \"Lpu_iid\",
			PR.MedPersonal_iid as \"MedPersonal_iid\",
			EN.EvnNotifyBase_id as \"EvnNotifyBase_id\",
			M.Morbus_id as \"Morbus_id\",
			MA.MorbusACS_id as \"MorbusACS_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			LpuAdd.Lpu_Nick as \"LpuAdd_Nick\",
			Diag.diag_FullName as \"Diag_Name\",
			PROUT.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
			PROUT.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
			to_char(PR.PersonRegister_setDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			to_char(PR.PersonRegister_disDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_disDate\"
		";
	}

	public static function searchData_SearchFormType_CrazyRegistry(Search_model $callObject)
	{
		$region = ($callObject->getRegionNick() == "ufa") ? "(1=1)" : "(1=2)";
		return "
			PR.PersonRegister_id as \"PersonRegister_id\",
			PR.Diag_id as \"Diag_id\",
			PR.Lpu_iid as \"Lpu_iid\",
			PR.MedPersonal_iid as \"MedPersonal_iid\",
			EN.EvnNotifyCrazy_id as \"EvnNotifyCrazy_id\",
			coalesce(EN.Morbus_id,PR.Morbus_id) as \"Morbus_id\",
			MO.MorbusCrazy_id as \"MorbusCrazy_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu2.Lpu_Nick as \"Lpu2_Nick\",
			coalesce(Diag.diag_FullName, PRDiag.diag_FullName) as \"Diag_Name\",
			case when {$region} then PROUT.PersonRegisterOutCause_id else CCEST.CrazyCauseEndSurveyType_id end as \"PersonRegisterOutCause_id\",
			case when {$region} then PROUT.PersonRegisterOutCause_Name else CCEST.CrazyCauseEndSurveyType_Name end as \"PersonRegisterOutCause_Name\",
			to_char(PR.PersonRegister_setDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			to_char(PR.PersonRegister_disDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_disDate\",
			Lpu.Lpu_Nick as \"Lpu_Nick\"
		";
	}

	public static function searchData_SearchFormType_NarkoRegistry(Search_model $callObject)
	{
		return "
			PR.PersonRegister_id as \"PersonRegister_id\",
			PR.Diag_id as \"Diag_id\",
			PR.Lpu_iid as \"Lpu_iid\",
			PR.MedPersonal_iid as \"MedPersonal_iid\",
			coalesce(EN.EvnNotifyNarco_id,EC.EvnNotifyCrazy_id) as \"EvnNotifyCrazy_id\",
			coalesce(coalesce(EN.Morbus_id,EC.Morbus_id),MO.Morbus_id) as \"Morbus_id\",
			MO.MorbusCrazy_id as \"MorbusCrazy_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu2.Lpu_Nick as \"Lpu2_Nick\",
			coalesce(Diag.diag_FullName, PRDiag.diag_FullName) as \"Diag_Name\",
			CCEST.CrazyCauseEndSurveyType_id as \"PersonRegisterOutCause_id\",
			CCEST.CrazyCauseEndSurveyType_Name as \"PersonRegisterOutCause_Name\",
			to_char(PR.PersonRegister_setDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			to_char(PR.PersonRegister_disDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_disDate\",
			Lpu.Lpu_Nick as \"Lpu_Nick\"
		";
	}

	public static function searchData_SearchFormType_PersonRegisterBase(Search_model $callObject)
	{
		return "
			PR.PersonRegister_id as \"PersonRegister_id\",
			PRT.PersonRegisterType_SysNick as \"PersonRegisterType_SysNick\",
			rtrim(MT.MorbusType_SysNick) as \"MorbusType_SysNick\",
			PR.Diag_id as \"Diag_id\",
			PR.Lpu_iid as \"Lpu_iid\",
			PR.MedPersonal_iid as \"MedPersonal_iid\",
			PR.EvnNotifyBase_id as \"EvnNotifyBase_id\",
			PR.Morbus_id as \"Morbus_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			LpuIns.Lpu_Nick as \"Lpu_insNick\",
			Diag.diag_FullName as \"Diag_Name\",
			PROUT.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
			PROUT.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
			to_char(PR.PersonRegister_setDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			to_char(PR.PersonRegister_disDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_disDate\"
		";
	}

	public static function searchData_SearchFormType_PalliatRegistry(Search_model $callObject)
	{
		return "
			PR.PersonRegister_id as \"PersonRegister_id\",
			MO.MorbusPalliat_id as \"MorbusPalliat_id\",
			PRT.PersonRegisterType_SysNick as \"PersonRegisterType_SysNick\",
			rtrim(MT.MorbusType_SysNick) as \"MorbusType_SysNick\",
			PR.Diag_id as \"Diag_id\",
			PR.Lpu_iid as \"Lpu_iid\",
			PR.MedPersonal_iid as \"MedPersonal_iid\",
			PR.EvnNotifyBase_id as \"EvnNotifyBase_id\",
			PR.Morbus_id as \"Morbus_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			to_char(PS.Person_closeDT, '{$callObject->dateTimeForm104}') as \"Person_closeDT\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			LpuIns.Lpu_Nick as \"Lpu_insNick\",
			Diag.diag_FullName as \"Diag_Name\",
			PROUT.PersonRegisterOutCause_id as\"PersonRegisterOutCause_id\",
			PROUT.PersonRegisterOutCause_Name as\"PersonRegisterOutCause_Name\",
			to_char(PR.PersonRegister_setDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			to_char(PR.PersonRegister_disDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_disDate\"
		";
	}

	public static function searchData_SearchFormType_NephroRegistry(Search_model $callObject)
	{
		return "
			PR.PersonRegister_id as \"PersonRegister_id\",
			PR.Diag_id as \"Diag_id\",
			COALESCE(DiabEvnDiagSpec.Diag_Code,DiabEvnSection.Diag_Code,DiabEvnVizitPL.Diag_Code,DiabEvnDiagPLSop.Diag_Code,
				DiabEvnDiagPS.Diag_Code,DiabEvnUslugaDispDop.Diag_Code,DiabEvnDiagDopDisp.Diag_Code) as \"Diab_Diag_Code\",
			to_char(lastVizitDate.EvnVizitPL_setDate, '{$callObject->dateTimeForm104}') as \"lastVizitNefroDate\",
			PR.Lpu_iid as \"Lpu_iid\",
			PR.MedPersonal_iid as \"MedPersonal_iid\",
			EN.EvnNotifyNephro_id as \"EvnNotifyBase_id\",
			coalesce(EN.Morbus_id,PR.Morbus_id) as \"Morbus_id\",
			MO.MorbusNephro_id as \"MorbusNephro_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			Diag.diag_FullName as \"Diag_Name\",
			PROUT.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
			PROUT.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
			to_char(PR.PersonRegister_setDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			to_char(PR.PersonRegister_disDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_disDate\"
		";
	}

	public static function searchData_SearchFormType_EndoRegistry(Search_model $callObject)
	{
		return "
			PRE.PersonRegisterEndo_id as \"PersonRegisterEndo_id\",
			PR.PersonRegister_id as \"PersonRegister_id\",
			PR.PersonRegister_Code as \"PersonRegister_Code\",
			PS.Person_id as \"Person_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			PS.Server_id as \"Server_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			dbo.Age2(PS.Person_BirthDay, (select dt from mv)) as \"Person_Age\",
			Diag.diag_FullName as \"Diag_Name\",
			CLDT.CategoryLifeDegreeType_Name as \"CategoryLifeDegreeType_Name\",
			PT.ProsthesType_Name as \"ProsthesType_Name\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			MP.Person_Fio as \"MedPersonal_Fio\",
			to_char(PRE.PersonRegisterEndo_obrDate, '{$callObject->dateTimeForm104}') as \"PersonRegisterEndo_obrDate\",
			to_char(PR.PersonRegister_setDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			to_char(PRE.PersonRegisterEndo_callDate, '{$callObject->dateTimeForm104}') as \"PersonRegisterEndo_callDate\",
			to_char(PRE.PersonRegisterEndo_hospDate, '{$callObject->dateTimeForm104}') as \"PersonRegisterEndo_hospDate\",
			to_char(PRE.PersonRegisterEndo_operDate, '{$callObject->dateTimeForm104}') as \"PersonRegisterEndo_operDate\",
			PRE.PersonRegisterEndo_Contacts as \"PersonRegisterEndo_Contacts\",
			PRE.PersonRegisterEndo_Comment as \"PersonRegisterEndo_Comment\"
		";
	}

	public static function searchData_SearchFormType_IBSRegistry(Search_model $callObject)
	{
		return "
			PR.PersonRegister_id as \"PersonRegister_id\",
			PR.Diag_id as \"Diag_id\",
			PR.Lpu_iid as \"Lpu_iid\",
			PR.MedPersonal_iid as \"MedPersonal_iid\",
			EN.EvnNotifyBase_id as \"EvnNotifyBase_id\",
			coalesce(EN.Morbus_id,PR.Morbus_id) as \"Morbus_id\",
			MO.MorbusIBS_id as \"MorbusIBS_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			IBSType.IBSType_Name as \"IBSType_Name\",
			CASE WHEN coalesce(MO.MorbusIBS_IsKGFinished, 1) = 2 THEN 'true' ELSE 'false' END as \"MorbusIBS_IsKGFinished\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			Diag.diag_FullName as \"Diag_Name\",
			PROUT.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
			PROUT.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
			to_char(PR.PersonRegister_setDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			to_char(PR.PersonRegister_disDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_disDate\"
		";
	}

	public static function searchData_SearchFormType_ProfRegistry(Search_model $callObject)
	{
		return "
			PR.PersonRegister_id as \"PersonRegister_id\",
			PR.Diag_id as \"Diag_id\",
			PR.Lpu_iid as \"Lpu_iid\",
			PR.MedPersonal_iid as \"MedPersonal_iid\",
			EN.EvnNotifyProf_id as \"EvnNotifyBase_id\",
			coalesce(EN.Morbus_id,PR.Morbus_id) as \"Morbus_id\",
			MO.MorbusProf_id as \"MorbusProf_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			mpd.MorbusProfDiag_Name as \"MorbusProfDiag_Name\",
			o.Org_Name as \"Org_Name\",
			to_char(PS.Person_DeadDT, '{$callObject->dateTimeForm104}') as \"Person_deadDT\",
			case when pcs.CardCloseCause_id = 5 then 1 else 0 end as \"Person_DeRegister\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			Diag.diag_FullName as \"Diag_Name\",
			PROUT.PersonRegisterOutCause_id as \".PersonRegisterOutCause_id\",
			PROUT.PersonRegisterOutCause_Name as \".PersonRegisterOutCause_Name\",
			to_char(PR.PersonRegister_setDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			to_char(PR.PersonRegister_disDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_disDate\"
		";
	}

	public static function searchData_SearchFormType_TubRegistry(Search_model $callObject)
	{
		return "
			PR.PersonRegister_id as \"PersonRegister_id\",
			PR.Diag_id as \"Diag_id\",
			PR.Lpu_iid as \"Lpu_iid\",
			PR.MedPersonal_iid as \"MedPersonal_iid\",
			EN.EvnNotifyTub_id as \"EvnNotifyTub_id\",
			coalesce(EN.Morbus_id,PR.Morbus_id) as \"Morbus_id\",
			MO.MorbusTub_id as \"MorbusTub_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			Diag.diag_FullName as \"Diag_Name\",
			PROUT.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
			PROUT.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
			to_char(PR.PersonRegister_setDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			to_char(PR.PersonRegister_disDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_disDate\"
		";
	}

	public static function searchData_SearchFormType_LargeFamilyRegistry(Search_model $callObject)
	{
		return "
			PR.PersonRegister_id as \"PersonRegister_id\",
			PR.Diag_id as \"Diag_id\",
			PR.Lpu_iid as \"Lpu_iid\",
			PR.MedPersonal_iid as \"MedPersonal_iid\",
			v_MorbusType.MorbusType_id as \"MorbusType_id\",
			v_MorbusType.MorbusType_SysNick as \"MorbusType_SysNick\",
			Drug.Request as \"DrugRequest\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_id as \"Lpu_id\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			Diag.diag_FullName as \"Diag_Name\",
			PROUT.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
			PROUT.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
			to_char(PR.PersonRegister_setDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			to_char(PR.PersonRegister_disDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_disDate\"
		";
	}

	public static function searchData_SearchFormType_FmbaRegistry(Search_model $callObject)
	{
		return "
			PR.PersonRegister_id as \"PersonRegister_id\",
			PR.Diag_id as \"Diag_id\",
			PR.Lpu_iid as \"Lpu_iid\",
			PR.MedPersonal_iid as \"MedPersonal_iid\",
			Drug.Request as \"DrugRequest\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_id as \"Lpu_id\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			Diag.diag_FullName as \"Diag_Name\",
			PROUT.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
			PROUT.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
			to_char(PR.PersonRegister_setDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			to_char(PR.PersonRegister_disDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_disDate\"
		";
	}

	public static function searchData_SearchFormType_HIVRegistry(Search_model $callObject)
	{
		return "
			PR.PersonRegister_id as \"PersonRegister_id\",
			PR.Diag_id as \"Diag_id\",
			PR.Lpu_iid as \"Lpu_iid\",
			PR.MedPersonal_iid as \"MedPersonal_iid\",
			EN.EvnNotifyHIV_id as \"EvnNotifyHIV_id\",
			MH.MorbusHIV_NumImmun as \"MorbusHIV_NumImmun\",
			MH.Morbus_id as \"Morbus_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			Diag.diag_FullName as \"Diag_Name\",
			PROUT.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
			PROUT.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
			to_char(PR.PersonRegister_setDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			to_char(PR.PersonRegister_disDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_disDate\"
		";
	}

	public static function searchData_SearchFormType_VenerRegistry(Search_model $callObject)
	{
		return "
			PR.PersonRegister_id as \"PersonRegister_id\",
			PR.Diag_id as \"Diag_id\",
			PR.Lpu_iid as \"Lpu_iid\",
			PR.MedPersonal_iid as \"MedPersonal_iid\",
			EN.EvnNotifyVener_id as \"EvnNotifyVener_id\",
			coalesce(EN.Morbus_id,PR.Morbus_id) as \"Morbus_id\",
			MO.MorbusVener_id as \"MorbusVener_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			Diag.diag_FullName as \"Diag_Name\",
			PROUT.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
			PROUT.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
			to_char(PR.PersonRegister_setDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			to_char(PR.PersonRegister_disDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_disDate\"
		";
	}

	public static function searchData_SearchFormType_HepatitisRegistry(Search_model $callObject)
	{
		return "
			PR.PersonRegister_id as \"PersonRegister_id\",
			PR.Diag_id as \"Diag_id\",
			PR.Lpu_iid as \"Lpu_iid\",
			PR.MedPersonal_iid as \"MedPersonal_iid\",
			ENH.EvnNotifyHepatitis_id as \"EvnNotifyBase_id\",
			coalesce(ENH.Morbus_id,PR.Morbus_id) as \"Morbus_id\",
			MH.MorbusHepatitis_id as \"MorbusHepatitis_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			Diag.diag_FullName as \"Diag_Name\",
			PROUT.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
			PROUT.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
			HDT.HepatitisDiagType_Name as \"HepatitisDiagType_Name\",
			HQT.HepatitisQueueType_Name as \"HepatitisQueueType_Name\",
			MHQ.MorbusHepatitisQueue_Num as \"MorbusHepatitisQueue_Num\",
			IsCure.YesNo_Name as \"MorbusHepatitisQueue_IsCure\",
			to_char(PR.PersonRegister_setDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			to_char(PR.PersonRegister_disDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_disDate\"
		";
	}

	public static function searchData_SearchFormType_OnkoRegistry(Search_model $callObject)
	{
		return "
			PR.PersonRegister_id as \"PersonRegister_id\",
			PR.Lpu_iid as \"Lpu_iid\",
			PR.MedPersonal_iid as \"MedPersonal_iid\",
			PR.EvnNotifyBase_id as \"EvnNotifyBase_id\",
			EONN.EvnOnkoNotifyNeglected_id as \"EvnOnkoNotifyNeglected_id\",
			MOV.MorbusOnkoVizitPLDop_id as \"MorbusOnkoVizitPLDop_id\",
			MOL.MorbusOnkoLeave_id as \"MorbusOnkoLeave_id\",
			MO.MorbusOnko_id as \"MorbusOnko_id\",
			M.Morbus_id as \"Morbus_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			Diag.Diag_id as \"Diag_id\",
			Diag.diag_FullName as \"Diag_Name\",
			PROUT.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
			PROUT.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
			CASE WHEN OnkoDiag.OnkoDiag_Name is null THEN '' ELSE OnkoDiag.OnkoDiag_Code || '. ' || OnkoDiag.OnkoDiag_Name END as \"OnkoDiag_Name\",
			CASE WHEN (MO.MorbusOnko_IsMainTumor is null OR MO.MorbusOnko_IsMainTumor < 2) THEN 'Нет' ELSE 'Да' END as \"MorbusOnko_IsMainTumor\",
			TumorStage.TumorStage_Name as \"TumorStage_Name\",
			to_char(MO.MorbusOnko_setDiagDT, '{$callObject->dateTimeForm104}') as \"MorbusOnko_setDiagDT\",
			to_char(PR.PersonRegister_setDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			to_char(PR.PersonRegister_disDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_disDate\"
		";
	}

	public static function searchData_SearchFormType_GeriatricsRegistry(Search_model $callObject)
	{
		return "
			PR.PersonRegister_id as \"PersonRegister_id\",
			MG.MorbusGeriatrics_id as \"MorbusGeriatrics_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			PR.EvnNotifyBase_id as \"EvnNotifyBase_id\",
			M.Morbus_id as \"Morbus_id\",
			D.Diag_id as \"Diag_id\",
			D.Diag_FullName as \"Diag_Name\",
			PROUT.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
			PROUT.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			SS.SocStatus_Name as \"SocStatus_Name\",
			to_char(PR.PersonRegister_setDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			to_char(PR.PersonRegister_disDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_disDate\",
			ANH.AgeNotHindrance_Name as \"AgeNotHindrance_Name\",
			IsKGO.YesNo_Name as \"MorbusGeriatrics_IsKGO\",
			IsWheelChair.YesNo_Name as \"MorbusGeriatrics_IsWheelChair\",
			IsFallDown.YesNo_Name as \"MorbusGeriatrics_IsFallDown\",
			IsWeightDecrease.YesNo_Name as \"MorbusGeriatrics_IsWeightDecrease\",
			IsCapacityDecrease.YesNo_Name as \"MorbusGeriatrics_IsCapacityDecrease\",
			IsCognitiveDefect.YesNo_Name as \"MorbusGeriatrics_IsCognitiveDefect\",
			IsMelancholia.YesNo_Name as \"MorbusGeriatrics_IsMelancholia\",
			IsEnuresis.YesNo_Name as \"MorbusGeriatrics_IsEnuresis\",
			IsPolyPragmasy.YesNo_Name as \"MorbusGeriatrics_IsPolyPragmasy\",
			Lpu.Lpu_Nick as \"Lpu_Nick\"
		";
	}

	public static function searchData_SearchFormType_IPRARegistry(Search_model $callObject)
	{
		$query = "
			IR.IPRARegistry_id as \"IPRARegistry_id\",
			IR.IPRARegistry_Number as \"IPRARegistry_Number\",
			to_char(IR.IPRARegistry_issueDate, '{$callObject->dateTimeForm104}') as \"IPRARegistry_issueDate\",
			to_char(IR.IPRARegistry_EndDate, '{$callObject->dateTimeForm104}') as \"IPRARegistry_EndDate\",
			IR.IPRARegistry_FGUMCEnumber as \"IPRARegistry_FGUMCEnumber\",
			IR.IPRARegistry_Protocol as \"IPRARegistry_Protocol\",
			to_char(IR.IPRARegistry_ProtocolDate, '{$callObject->dateTimeForm104}') as \"IPRARegistry_ProtocolDate\",
			to_char(IR.IPRARegistry_DevelopDate, '{$callObject->dateTimeForm104}') as \"IPRARegistry_DevelopDate\",
			IR.IPRARegistry_isFirst as \"IPRARegistry_isFirst\",
			IR.IPRARegistry_Confirm as \"IPRARegistry_Confirm\",
			IR.IPRARegistry_DirectionLPU_id as \"IPRARegistry_DirectionLPU_id\",
			IR.Lpu_id as \"Lpu_id\",
			IR.IPRARegistry_insDT as \"IPRARegistry_insDT\",
			IR.IPRARegistry_updDT as \"IPRARegistry_updDT\",
			IR.pmUser_insID as \"pmUser_insID\",
			IR.pmUser_updID as \"pmUser_updID\",
			DirLpu.Lpu_Nick as \"IPRARegistry_DirectionLPU_Name\",
			ConfLpu.Lpu_Nick as \"LpuConfirm_Name\",
			PR.PersonRegister_id as \"PersonRegister_id\",
			PR.Lpu_iid as \"Lpu_iid\",
			PR.MedPersonal_iid as \"MedPersonal_iid\",
			PR.MorbusType_id as \"MorbusType_id\",
			PR.EvnNotifyBase_id as \"EvnNotifyBase_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			case
				when length(ps.Person_Snils) = 11
					then left(ps.Person_Snils, 3)
						|| '-' || substring(ps.Person_Snils from 4 for 3)
						|| '-' || substring(ps.Person_Snils from 7 for 3)
						|| ' ' || right(ps.Person_Snils, 2)
					else ps.Person_Snils
			end as \"Person_Snils\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			PROUT.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
			PROUT.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
			to_char(PR.PersonRegister_setDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			to_char(PR.PersonRegister_disDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_disDate\",
			pmUser.pmUser_name || ', ' || pmUser.pmUser_Login as \"pmUser_name\"
		";
		if ($callObject->getRegionNick() != "ufa") {
			$query .= ",IsMeasuresComplete.Value as \"IsMeasuresComplete\"";
		}
		return $query;
	}

	public static function searchData_SearchFormType_ECORegistry(Search_model $callObject, $data, &$queryParams)
	{
		if (isset($data["isRegion"]) && $data["isRegion"] == 1) {
			$query = "
				(
					SELECT vER.Result
					FROM dbo.v_ECORegistry vER
					WHERE vER.PersonRegisterEco_AddDate IN (
                        select max(vER1.PersonRegisterEco_AddDate)
                        from dbo.v_ECORegistry vER1 
                        where vER1.PersonRegister_id = PR.PersonRegister_id
					) AND vER.PersonRegister_id = PR.PersonRegister_id
				) as \"ResEco\",
				(
					SELECT vER.ds_osn_name
					FROM dbo.v_ECORegistry vER
					WHERE vER.PersonRegisterEco_AddDate IN (
						select max(vER1.PersonRegisterEco_AddDate)
						from dbo.v_ECORegistry vER1 
						where vER1.PersonRegister_id = PR.PersonRegister_id
					) AND vER.PersonRegister_id = PR.PersonRegister_id
				) as \"ds_name\",
				(
					SELECT vER.opl_name
					FROM dbo.v_ECORegistry vER
					WHERE vER.PersonRegisterEco_AddDate IN (
						select max(vER1.PersonRegisterEco_AddDate)
						from dbo.v_ECORegistry vER1 
						where vER1.PersonRegister_id = PR.PersonRegister_id
					) AND vER.PersonRegister_id = PR.PersonRegister_id
				) as \"opl_name\",
				(
					SELECT pn.Person_Fio
					FROM
						dbo.v_ECORegistry vER
						LEFT JOIN persis.MedWorker mw ON mw.id = vER.MedPersonal_id
						LEFT JOIN v_Person_bdz pn ON pn.Person_id = mw.Person_id
					WHERE vER.PersonRegisterEco_AddDate IN (
						select max(vER1.PersonRegisterEco_AddDate)
						from dbo.v_ECORegistry vER1 
						where vER1.PersonRegister_id = PR.PersonRegister_id
					) AND vER.PersonRegister_id = PR.PersonRegister_id
					limit 1
				) as \"MedPersonal_name\",
			";
		} else {
			$query = "
				(
					SELECT vER.Result
					FROM dbo.v_ECORegistry vER
					WHERE vER.PersonRegisterEco_AddDate IN (
						select max(vER1.PersonRegisterEco_AddDate)
						from dbo.v_ECORegistry vER1 
						where vER1.PersonRegister_id=PR.PersonRegister_id and vER1.lpu_id = :PersonRegister_Lpu_iid
					) and vER.PersonRegister_id = PR.PersonRegister_id
				) as \"ResEco\",
				(
					SELECT vER.ds_osn_name
					FROM dbo.v_ECORegistry vER
					WHERE vER.PersonRegisterEco_AddDate IN (
						select max(vER1.PersonRegisterEco_AddDate)
                    	from dbo.v_ECORegistry vER1 
                    	where vER1.PersonRegister_id=PR.PersonRegister_id and vER1.lpu_id = :PersonRegister_Lpu_iid
                    ) and vER.PersonRegister_id = PR.PersonRegister_id
				) as \"ds_name\",
				(
					SELECT vER.opl_name
					FROM dbo.v_ECORegistry vER
					WHERE vER.PersonRegisterEco_AddDate IN (
						select max(vER1.PersonRegisterEco_AddDate)
						from dbo.v_ECORegistry vER1 
						where vER1.PersonRegister_id=PR.PersonRegister_id and vER1.lpu_id = :PersonRegister_Lpu_iid
					) and vER.PersonRegister_id = PR.PersonRegister_id
				) as \"opl_name\",
				(
					SELECT pn.Person_Fio
					FROM
						dbo.v_ECORegistry vER
						LEFT JOIN persis.MedWorker mw ON mw.id = vER.MedPersonal_id
						LEFT JOIN v_Person_bdz pn ON pn.Person_id = mw.Person_id
					WHERE vER.PersonRegisterEco_AddDate IN (
						select max(vER1.PersonRegisterEco_AddDate)
                        from dbo.v_ECORegistry vER1 
                        where vER1.PersonRegister_id=PR.PersonRegister_id and vER1.lpu_id = :PersonRegister_Lpu_iid
					) and vER.PersonRegister_id = PR.PersonRegister_id
					limit 1
				) as \"MedPersonal_name\",
			";
			$queryParams["PersonRegister_Lpu_iid"] = $data["EcoRegistryData_lpu_id"];
		};
		$query .= "
			PR.PersonRegister_id as \"PersonRegister_id\",
			PR.Lpu_iid as \"Lpu_iid\",
			PR.MorbusType_id as \"MorbusType_id\",
			PS.Person_id as \"Person_id\",
            PS.Server_id as \"Server_id\",
            ER.Result as \"ResEco1\",
            RTRIM(PS.Person_SurName) AS \"Person_Surname\",
            RTRIM(PS.Person_FirName) AS \"Person_Firname\",
            RTRIM(PS.Person_SecName) AS \"Person_Secname\",
            to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') AS \"Person_Birthday\",
            Lpu.Lpu_Nick AS \"Lpu_Nick\",
            LpuUch.Lpu_Nick AS \"Lpu_NickUch\",
            to_char(PR.PersonRegister_setDate, '{$callObject->dateTimeForm104}') AS \"PersonRegister_setDate\",
            coalesce (PA.Address_Nick, PA.Address_Address) AS \"Person_PAddress\",
            (case when PPR.PregnancyResult_Name is not null
                then PPR.PregnancyResult_Name when PPR1.PregnancyResult_Name is not null
                then PPR1.PregnancyResult_Name
                else ''
			end) as \"IsxBer\",
			(case when (BSS.BirthSpecStac_CountChild is null and BSS1.BirthSpecStac_CountChild is null and ER.EcoChildCountType_Name is null)
				then ER.EmbrionCount_Name
			when (BSS.BirthSpecStac_CountChild is null and BSS1.BirthSpecStac_CountChild is null and ER.EcoChildCountType_Name is not null)
				then ER.EcoChildCountType_Name
				else (case when BSS.BirthSpecStac_CountChild is not NULL
					then CAST(BSS.BirthSpecStac_CountChild as varchar(10))
					else CAST(BSS1.BirthSpecStac_CountChild as varchar(10))
				end)
			end) as \"Count_plod\"
		";
		return $query;
	}

	public static function searchData_SearchFormType_BskRegistry(Search_model $callObject)
	{
		return "
			PR.PersonRegister_id as \"PersonRegister_id\",
			PR.Lpu_iid as \"Lpu_iid\",
			PR.MedPersonal_iid as \"MedPersonal_iid\",
			PR.MorbusType_id as \"MorbusType_id\",
			PR.EvnNotifyBase_id as \"EvnNotifyBase_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			coalesce(to_char(R.BSKRegistry_nextDate,'YYYY-MM-DD'),case when PR.MorbusType_id = 84 and BSKRegistry_riskGroup = 1 then to_char((dateadd('MONTH', 18, R.BSKRegistry_setDate)), 'YYYY-MM-DD')
					 when PR.MorbusType_id = 84 and BSKRegistry_riskGroup = 2 then to_char((dateadd('MONTH', 12, R.BSKRegistry_setDate)), 'YYYY-MM-DD')
					 when PR.MorbusType_id = 84 and BSKRegistry_riskGroup = 3 then to_char((dateadd('MONTH', 6, R.BSKRegistry_setDate)), 'YYYY-MM-DD')
					 when PR.MorbusType_id = 50 then to_char((dateadd('MONTH', 6, R.BSKRegistry_setDate)), 'YYYY-MM-DD')
					 when PR.MorbusType_id = 89 then to_char((dateadd('MONTH', 6, R.BSKRegistry_setDate)), 'YYYY-MM-DD')
					 when PR.MorbusType_id = 88 then to_char((dateadd('MONTH', 6, R.BSKRegistry_setDate)), 'YYYY-MM-DD')
					 end) as \"BSKRegistry_setDateNext\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			to_char(ps.Person_deadDT , '{$callObject->dateTimeForm104}') as \"Person_deadDT\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			BSKLpuGospital.BSKLpuGospital_data as \"Lpu_Gospital\",
			BSKTLT.TLT_data as \"isTLT\",
			case when BSKTLT.TLT_data is not null then '' else dbo.getbsktimefortlt(PS.Person_id) end as \"TimeBeforeTlt\",
			case when BSKCKV.CHKV_data = 'нет' then '' else BSKCKV.CHKV_data end as \"isCKV\",
			dbo.getbsktimeforckvkag(PS.Person_id, 0) as \"CKVduringHour\",
			BSKKAG.KAG_data as \"isKAG\",
			dbo.getbsktimeforckvkag(PS.Person_id, 1) as \"KAGduringHour\",
			DP.Diag_FullName as \"Diag_Name\",
			PROUT.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
			PROUT.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
			to_char(PR.PersonRegister_setDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			to_char(PR.PersonRegister_disDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_disDate\",
			to_char(R.BSKRegistry_setDate, '{$callObject->dateTimeForm104}') as \"BSKRegistry_setDate\",
			MP.PMUser_Name as \"PMUser_Name\",
			MP.pmUser_id as \"pmUser_id\",
			R.BSKRegistry_isBrowsed as \"BSKRegistry_isBrowsed\"
		";
	}

	public static function searchData_SearchFormType_ReabRegistry(Search_model $callObject)
	{
		return "
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			to_char(ps.Person_deadDT , '{$callObject->dateTimeForm104}') as \"Person_deadDT\",
			Lpu.Lpu_Nick as \"Lpu_Nick\"
		";
	}

	public static function searchData_SearchFormType_AdminVIPPerson(Search_model $callObject)
	{
		return "
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			to_char(R.VIPPerson_setDate , '{$callObject->dateTimeForm104}') as \"VIPPerson_setDate\",
			to_char(R.VIPPerson_disDate , '{$callObject->dateTimeForm104}') as \"VIPPerson_disDate\",
			R.VIPPerson_id as \"VIPPerson_id\",
			R.VIPPerson_deleted as \"VIPPerson_deleted\",
			Lpu1.Lpu_Nick as \"Lpu_Nick\",
			Lpu1.Lpu_id as \"Lpu_id\",
			pmUser.PMUser_Name as \"PMUser_Name\"
		";
	}

	public static function searchData_SearchFormType_ZNOSuspectRegistry(Search_model $callObject)
	{
		return "
			ZNORout.ZNOSuspectRout_id as \"ZNORout_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			to_char(ps.Person_deadDT , '{$callObject->dateTimeForm104}') as \"Person_deadDT\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			lpu.Lpu_id as \"Lpu_iid\",
			d.Diag_Code as \"Diag_CodeFirst\",
			ZNORout.ZNOSuspectRout_IsTerms as \"ZNOSuspectRout_IsTerms\",
			case
				when ZNOSuspectRout_IsTerms is null then null
				when ZNORout.ZNOSuspectRout_IsTerms = 1 then 'V'
				else '!'
			end as \"Terms\",
			ZNORout.ZNOSuspectRout_IsBiopsy as \"ZNOSuspectRout_IsBiopsy\",
			case
				when ZNORout.ZNOSuspectRout_IsBiopsy is null then null
				when ZNORout.ZNOSuspectRout_IsBiopsy = 1 then 'V'
				else '!'
			end as \"Biopsy\",
			dd.Diag_id as \"Finish\",
			case
			  when ZNORout.Diag_Fid > 0 then dd.Diag_Code
			  when ZNORout.Diag_Fid is null then null
			  else '!'
			end as \"Diag_CodeFinish\",
			to_char(ZNOReg.ZNOSuspectRegistry_setDate, '{$callObject->dateTimeForm104}') as \"Registry_setDate\"
		";
	}

	public static function searchData_SearchFormType_ReanimatRegistry(Search_model $callObject)
	{
		return "
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			RR.Lpu_iid as \"Lpu_iid\",
			RR.MedPersonal_iid as \"MedPersonal_iid\",
			RR.MorbusType_id as \"MorbusType_id\",
			to_char(RR.ReanimatRegister_setDate, '{$callObject->dateTimeForm104}') as \"ReanimatRegister_setDate\",
			to_char(RR.ReanimatRegister_disDate, '{$callObject->dateTimeForm104}') as \"ReanimatRegister_disDate\",
			PROUT.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
			PROUT.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			MP.PMUser_Name as \"PMUser_Name\",
			MP.pmUser_id as \"pmUser_id\",
			RR.ReanimatRegister_id as \"ReanimatRegister_id\",
			RR.EvnReanimatPeriod_id as \"EvnReanimatPeriod_id\",
			RR.ReanimatRegister_IsPeriodNow as \"ReanimatRegister_IsPeriodNow\",
			coalesce(ERP.selrow, 0) as \"selrow\",
			D.Diag_FullName as \"Diag\",
			Lpu2.Lpu_Nick as \"Lpu_Nick_Curr\"
		";

	}

	public static function searchData_SearchFormType_EvnInfectNotify(Search_model $callObject)
	{
		return "
			EIN.EvnInfectNotify_id as \"EvnInfectNotify_id\",
			to_char(EIN.EvnInfectNotify_insDT, '{$callObject->dateTimeForm104}') as \"EvnInfectNotify_insDT\",
			PS.Person_id as \"Person_id\",
			pc.Lpu_id as \"Lpu_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			coalesce ( Diag.diag_FullName, Diag1.diag_FullName ) as \"Diag_Name\"
		";
	}

	public static function searchData_SearchFormType_EvnNotifyHepatitis(Search_model $callObject)
	{
		return "
			ENH.EvnNotifyHepatitis_id as \"EvnNotifyHepatitis_id\",
			ENH.EvnNotifyHepatitis_pid as \"EvnNotifyHepatitis_pid\",
			ENH.Morbus_id as \"Morbus_id\",
			ENH.MedPersonal_id as \"MedPersonal_id\",
			ENH.pmUser_updId as \"pmUser_updId\",
			to_char(ENH.EvnNotifyHepatitis_setDT, '{$callObject->dateTimeForm104}') as \"EvnNotifyHepatitis_setDT\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			Diag.Diag_id as \"Diag_id\",
			Diag.diag_FullName as \"Diag_Name\",
			to_char(coalesce(ENH.EvnNotifyHepatitis_niDate,PR.PersonRegister_setDate), '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\"
		";
	}

	public static function searchData_SearchFormType_EvnOnkoNotify(Search_model $callObject)
	{
		return "
			EON.EvnOnkoNotify_id as \"EvnOnkoNotify_id\",
			EONN.EvnOnkoNotifyNeglected_id as \"EvnOnkoNotifyNeglected_id\",
			EON.EvnOnkoNotify_pid as \"EvnOnkoNotify_pid\",
			EON.Morbus_id as \"Morbus_id\",
			EON.MedPersonal_id as \"MedPersonal_id\",
			EON.pmUser_updId as \"pmUser_updId\",
			to_char(EON.EvnOnkoNotify_setDT, '{$callObject->dateTimeForm104}') as \"EvnOnkoNotify_setDT\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			EON.Lpu_sid as \"Lpu_sid\",
			Lpu1.Lpu_Nick as \"Lpu_sid_Nick\",
			Diag.Diag_id as \"Diag_id\",
			Diag.diag_FullName as \"Diag_Name\",
			OnkoDiag.OnkoDiag_Name as \"OnkoDiag_Name\",
			TumorStage.TumorStage_Name as \"TumorStage_Name\",
			CASE WHEN EONN.EvnOnkoNotifyNeglected_id IS NOT NULL THEN 'true' ELSE 'false' END as \"isNeglected\",
			to_char(coalesce(EON.EvnOnkoNotify_niDate,PR.PersonRegister_setDate), '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			case 
				when EON.EvnOnkoNotify_niDate is null and PR.PersonRegister_setDate is not null then 'Да'
				when EON.EvnOnkoNotify_niDate is not null then 'Нет'
				else ''
			end as \"IsIncluded\",
			case 
				when EON.EvnOnkoNotify_niDate is null and PR.PersonRegister_setDate is not null then 'Включено в регистр'
				when EON.EvnOnkoNotify_niDate is not null and EON.PersonRegisterFailIncludeCause_id = 1 then 'Отклонено (ошибка в Извещении)'
				when EON.EvnOnkoNotify_niDate is not null and EON.PersonRegisterFailIncludeCause_id = 2 then 'Отклонено (решение оператора)'
				else 'Отправлено'
			end as \"EvnNotifyStatus_Name\",
			EON.EvnOnkoNotify_Comment as \"EvnOnkoNotify_Comment\",
			null as \"EvnOnkoNotify_CommentLink\"
		";
	}

	public static function searchData_SearchFormType_EvnNotifyRegister(Search_model $callObject)
	{
		return "
			EN.EvnNotifyRegister_id as \"EvnNotifyRegister_id\",
			E.Evn_pid as \"EvnNotifyRegister_pid\",
			EN.EvnNotifyRegister_Num as \"EvnNotifyRegister_Num\",
			to_char(E.Evn_setDT, '{$callObject->dateTimeForm104}') as \"EvnNotifyRegister_setDT\",
			EN.NotifyType_id as \"NotifyType_id\",
			NT.NotifyType_Name as \"NotifyType_Name\",
			PRT.PersonRegisterType_SysNick as \"PersonRegisterType_SysNick\",
			MT.MorbusType_SysNick as \"MorbusType_SysNick\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			E.Morbus_id as \"Morbus_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			AttachLpu.Lpu_Nick as \"AttachLpu_Nick\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			E.Lpu_id as \"Lpu_did\",
			Diag.Diag_id as \"Diag_id\",
			Diag.diag_FullName as \"Diag_Name\",
			to_char(coalesce(ENB.EvnNotifyBase_niDate,PR.PersonRegister_setDate), '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			ENB.MedPersonal_id as \"MedPersonal_id\",
			MP.Person_Fio as \"MedPersonal_Name\",
			E.pmUser_updId as \"pmUser_updId\"
		";
	}

	public static function searchData_SearchFormType_EvnNotifyOrphan(Search_model $callObject)
	{
		return "
			ENO.EvnNotifyOrphan_id as \"EvnNotifyOrphan_id\",
			ENO.EvnNotifyOrphan_pid as \"EvnNotifyOrphan_pid\",
			to_char(ENO.EvnNotifyOrphan_setDT, '{$callObject->dateTimeForm104}') as \"EvnNotifyOrphan_setDT\",
			ENO.EvnNotifyType_Name as \"EvnNotifyType_Name\",
			ENO.EvnNotifyType_SysNick as \"EvnNotifyType_SysNick\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			ENO.Morbus_id as \"Morbus_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			Diag.Diag_id as \"Diag_id\",
			Diag.diag_FullName as \"Diag_Name\",
			to_char(coalesce(ENO.EvnNotifyOrphan_niDate,PR.PersonRegister_setDate), '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			ENO.MedPersonal_id as \"MedPersonal_id\",
			MP.Person_Fio as \"MedPersonal_Name\",
			ENO.pmUser_updId as \"pmUser_updId\"
		";
	}

	public static function searchData_SearchFormType_EvnNotifyCrazy(Search_model $callObject)
	{
		return "
			ENC.EvnNotifyCrazy_id as \"EvnNotifyCrazy_id\",
			ENC.EvnNotifyCrazy_pid as \"EvnNotifyCrazy_pid\",
			to_char(ENC.EvnNotifyCrazy_setDT, '{$callObject->dateTimeForm104}') as \"EvnNotifyCrazy_setDT\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			ENC.Morbus_id as \"Morbus_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			Diag.Diag_id as \"Diag_id\",
			Diag.diag_FullName as \"Diag_Name\",
			to_char(coalesce(ENC.EvnNotifyCrazy_niDate,PR.PersonRegister_setDate), '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			ENC.MedPersonal_id as \"MedPersonal_id\",
			ENC.pmUser_updId as \"pmUser_updId\"
		";
	}

	public static function searchData_SearchFormType_EvnNotifyNarko(Search_model $callObject)
	{
		return "
			ENC.EvnNotifyNarco_id as \"EvnNotifyNarco_id\",
			ENC.EvnNotifyNarco_pid as \"EvnNotifyNarco_pid\",
			to_char(ENC.EvnNotifyNarco_setDT, '{$callObject->dateTimeForm104}') as \"EvnNotifyNarco_setDT\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			ENC.Morbus_id as \"Morbus_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			Lpu.Lpu_id as \"Lpu_id\",
			Diag.Diag_id as \"Diag_id\",
			Diag.diag_FullName as \"Diag_Name\",
			to_char(coalesce(ENC.EvnNotifyNarco_niDate,PR.PersonRegister_setDate), '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			ENC.MedPersonal_id as \"MedPersonal_id\",
			ENC.pmUser_updId as \"pmUser_updId\"
		";
	}

	public static function searchData_SearchFormType_EvnNotifyTub(Search_model $callObject)
	{
		return "
			ENC.EvnNotifyTub_id as \"EvnNotifyTub_id\",
			ENC.EvnNotifyTub_pid as \"EvnNotifyTub_pid\",
			to_char(ENC.EvnNotifyTub_setDT, '{$callObject->dateTimeForm104}') as \"EvnNotifyTub_setDT\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			ENC.Morbus_id as \"Morbus_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			coalesce(DiagENC.Diag_id,Diag.Diag_id) as \"Diag_id\",
			coalesce(DiagENC.diag_FullName,Diag.diag_FullName) as \"Diag_Name\",
			to_char(coalesce(ENC.EvnNotifyTub_niDate,PR.PersonRegister_setDate), '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			PR.PersonRegister_id as \"PersonRegister_id\",
			ENC.MedPersonal_id as \"MedPersonal_id\",
			ENC.pmUser_updId as \"pmUser_updId\"
		";
	}

	public static function searchData_SearchFormType_EvnNotifyNephro(Search_model $callObject)
	{
		return "
			ENC.EvnNotifyNephro_id as \"EvnNotifyNephro_id\",
			ENC.EvnNotifyNephro_pid as \"EvnNotifyNephro_pid\",
			to_char(ENC.EvnNotifyNephro_setDT, '{$callObject->dateTimeForm104}') as \"EvnNotifyNephro_setDT\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			ENC.Morbus_id as \"Morbus_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			Diag.Diag_id as \"Diag_id\",
			Diag.diag_FullName as \"Diag_Name\",
			to_char(coalesce(ENC.EvnNotifyNephro_niDate,PR.PersonRegister_setDate), '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			ENC.MedPersonal_id as \"MedPersonal_id\",
			ENC.pmUser_updId as \"pmUser_updId\"
		";
	}

	public static function searchData_SearchFormType_EvnNotifyProf(Search_model $callObject)
	{
		return "
			ENC.EvnNotifyProf_id as \"EvnNotifyProf_id\",
			ENC.EvnNotifyProf_pid as \"EvnNotifyProf_pid\",
			to_char(ENC.EvnNotifyProf_setDT, '{$callObject->dateTimeForm104}') as \"EvnNotifyProf_setDT\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			ENC.Morbus_id as \"Morbus_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			Diag.Diag_id as \"Diag_id\",
			Diag.diag_FullName as \"Diag_Name\",
			to_char(coalesce(ENC.EvnNotifyProf_niDate,PR.PersonRegister_setDate), '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			ENC.MedPersonal_id as \"MedPersonal_id\",
			ENC.pmUser_updId as \"pmUser_updId\"
		";
	}

	public static function searchData_SearchFormType_EvnNotifyHIV(Search_model $callObject)
	{
		return "
			ENB.EvnNotifyBase_id as \"EvnNotifyBase_id\",
			ENB.EvnNotifyBase_pid as \"EvnNotifyBase_pid\",
			to_char(ENB.EvnNotifyBase_setDT, '{$callObject->dateTimeForm104}') as \"EvnNotifyBase_setDT\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			ENB.Morbus_id as \"Morbus_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			Diag.Diag_id as \"Diag_id\",
			Diag.diag_FullName as \"Diag_Name\",
			to_char(coalesce(ENB.EvnNotifyBase_niDate,PR.PersonRegister_setDate), '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			ENB.MedPersonal_id as \"MedPersonal_id\",
			EvnClass.EvnClass_Name as \"EvnClass_Name\",
			EvnClass.EvnClass_SysNick as \"EvnClass_SysNick\",
			ENB.pmUser_updId as \"pmUser_updId\"
		";
	}

	public static function searchData_SearchFormType_EvnNotifyVener(Search_model $callObject)
	{
		return "
			ENC.EvnNotifyVener_id as \"EvnNotifyVener_id\",
			ENC.EvnNotifyVener_pid as \"EvnNotifyVener_pid\",
			to_char(ENC.EvnNotifyVener_setDT, '{$callObject->dateTimeForm104}') as \"EvnNotifyVener_setDT\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			ENC.Morbus_id as \"Morbus_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			Diag.Diag_id as \"Diag_id\",
			Diag.diag_FullName as \"Diag_Name\",
			to_char(coalesce(ENC.EvnNotifyVener_niDate,PR.PersonRegister_setDate), '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			ENC.MedPersonal_id as \"MedPersonal_id\",
			ENC.pmUser_updId as \"pmUser_updId\"
		";
	}

	public static function searchData_SearchFormType_PalliatNotify(Search_model $callObject)
	{
		return "
			PN.PalliatNotify_id as \"PalliatNotify_id\",
			ENB.EvnNotifyBase_id as \"EvnNotifyBase_id\",
			ENB.Morbus_id as \"Morbus_id\",
			ENB.MedPersonal_id as \"MedPersonal_id\",
			ENB.pmUser_updId as \"pmUser_updId\",
			1 as \"NotifyType_id\",
			to_char(ENB.EvnNotifyBase_setDT, '{$callObject->dateTimeForm104}') as \"EvnNotifyBase_setDate\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			Lpu.Lpu_id as \"Lpu_did\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			AttachLpu.Lpu_Nick as \"AttachLpu_Nick\",
			Diag.Diag_id as \"Diag_id\",
			Diag.diag_FullName as \"Diag_Name\",
			to_char(coalesce(ENB.EvnNotifyBase_niDate,PR.PersonRegister_setDate), '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			PRT.PersonRegisterType_SysNick as \"PersonRegisterType_SysNick\",
			case
				when ENB.EvnNotifyBase_niDate is not null then 1
				when PR.PersonRegister_id is not null then 2
			end as \"isInclude\"
		";
	}

	public static function searchData_SearchFormType_PersonDopDispPlan(Search_model $callObject)
	{
		return "
			PS.Person_id as \"Person_id\",
			RTRIM(PS.Person_SurName) || ' ' || coalesce(PS.Person_FirName, '') || ' ' || coalesce(PS.Person_SecName, '') as \"Person_FIO\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			sex.Sex_Name as \"Person_Sex\",
			to_char(IsPersonDopDispPassed.EvnPLDisp_setDate, '{$callObject->dateTimeForm104}') as \"EvnPLDisp_setDate\",
			to_char(IsPersonDopDispPassed.EvnPLDisp_disDate, '{$callObject->dateTimeForm104}') as \"EvnPLDisp_disDate\",
			2 as \"IsChecked\"
		";
	}

	public static function searchData_SearchFormType_RzhdRegistry(Search_model $callObject)
	{
		return "
			R.Register_id as \"Register_id\",
			RR.RzhdRegistry_id as \"RzhdRegistry_id\",
			R.Person_id as \"Person_id\",
			to_char(R.Register_setDate, '{$callObject->dateTimeForm104}') as \"Register_setDate\",
			to_char(R.Register_disDate, '{$callObject->dateTimeForm104}') as \"Register_disDate\",
			PS.Person_SurName as \"Person_Surname\",
			PS.Person_FirName as \"Person_FirstName\",
			PS.Person_SecName as \"Person_SecondName\",
			to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') as \"Person_deadDT\",
			PS.Polis_Num as \"Person_PolisNum\",
			PS.Server_id as \"Server_id\",
			case
				when length(PS.Person_Snils) = 11 then left(PS.Person_Snils, 3) || '-' || substring(PS.Person_Snils, 4, 3) || '-' || 
					substring(PS.Person_Snils, 7, 3) || ' ' || right(PS.Person_Snils, 2)
				else PS.Person_Snils
			end as \"Person_Snils\",
			to_char(PS.Person_BirthDay, '{$callObject->dateTimeForm104}') as \"Person_BirthDay\",
			R.RegisterDisCause_id as \"RegisterDisCause_id\",
			RDC.RegisterDisCause_name as \"RegisterDisCause_name\",
			Lpu.Lpu_id as \"Lpu_id\",
			Org.Org_Nick as \"Lpu_Nick\"
		";
	}

	public static function searchData_SearchFormType_ONMKRegistry(Search_model $callObject)
	{
		return "
			ROW_NUMBER () over (order by ONMKR.ONMKRegistry_id ) as \"vID\",
			ONMKR.ONMKRegistry_id as \"ONMKRegistry_id\",
			PS.Person_id as \"Person_id\",
			ONMKR.ONMKRegistry_IsNew as \"ONMKRegistry_IsNew\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			dbo.Age(PS.Person_BirthDay, getdate()) as \"Person_Age\",
			to_char(ONMKR.ONMKRegistry_EvnDT, '{$callObject->dateTimeForm104}') as \"ONMKRegistry_Evn_DTDesease\",
			substring(TO_CHAR(ONMKR.ONMKRegistry_EvnDT, 'HH24:MI:SS') from 1 for 6) as \"ONMKRegistry_Evn_DTDesease_Time\",
			case when ONMKR.ONMKRegistry_EvnDT = null
				then '' when ONMKR.ONMKRegistry_EvnDTDesease = null
					then ''
				else dbo.GetPeriodName(ONMKR.ONMKRegistry_EvnDTDesease, ONMKR.ONMKRegistry_EvnDT)
			end as \"TimeBeforeStac\",
			Dg.Diag_Code || '' || Dg.Diag_Name as \"Diag_Name\",
			RS.RankinScale_code as \"Renkin\",
			to_char(ONMKR.ONMKRegistry_TLTDT, '{$callObject->dateTimeForm104}') || ' ' || substring(TO_CHAR(ONMKR.ONMKRegistry_TLTDT, 'HH24:MI:SS') from 1 for 6) as \"TLTDT\",
			case when ONMKR.ONMKRegistry_TLTDT is null
				then dbo.GetONMKTimeForTLT(ONMKR.ONMKRegistry_EvnDTDesease)
				else ''
			end as \"TimeBeforeTlt\",
			ONMKR.ONMKRegistry_InsultScale as \"Nihss\",
			ONMKR.ONMKRegistry_NIHSSAfterTLT as \"ONMKRegistry_NIHSSAfterTLT\",
			to_char(ONMKR.ONMKRegistry_KTDT, '{$callObject->dateTimeForm104}') || ' ' || substring(TO_CHAR(ONMKR.ONMKRegistry_KTDT, 'HH24:MI:SS') from 1 for 6) as \"KTDT\",
			to_char(ONMKR.ONMKRegistry_MRTDT, '{$callObject->dateTimeForm104}') || ' ' || substring(TO_CHAR(ONMKR.ONMKRegistry_MRTDT, 'HH24:MI:SS') from 1 for 6) as \"MRTDT\",
			dbo.GetReanimat (ONMKR.EvnPS_id, 1) as \"ConsciousType_Name\",
			dbo.GetReanimat (ONMKR.EvnPS_id, 2) as \"BreathingType_Name\",
			Lp.Lpu_Nick as \"Lpu_Nick\",
			Lp.Lpu_id as \"Lpu_id\",
			dbo.GetONMKMO (Lp.Lpu_id) as \"MO_OK\",
			ONMKR.ONMKRegistry_IsIteration as \"HasDiag\",
			--ONMKR.ONMKRegistry_NumKVS as \"Lpu_Nick\",
			to_char(ONMKR.ONMKRegistry_insDT, '{$callObject->dateTimeForm104}') as \"ONMKRegistry_SetDate\",
			case when ONMKR.ONMKRegistry_IsConfirmed = 2
				then 'не подтвержден'
				else LT.LeaveType_Name
			end as \"LeaveType_Name\"
		";
	}

	public static function searchData_SearchFormType_SportRegistry(Search_model $callObject)
	{
		return "
			SRUMO.SportRegisterUMO_id as \"SportRegisterUMO_id\",
			SRUMO.SportRegister_id as \"SportRegister_id\",
			OC.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
			OC.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
			SRUMO.Lpu_id as \"Lpu_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.Person_SurName as \"Person_SurName\",
			PS.Person_FirName as \"Person_FirName\",
			PS.Person_SecName as \"Person_SecName\",
			to_char(PS.Person_BirthDay, '{$callObject->dateTimeForm104}') as \"Person_BirthDay\",
			ST.SportType_name as \"SportType_name\",
			SS.SportStage_name as \"SportStage_name\",
			to_char(SRUMO.SportRegisterUMO_UMODate, '{$callObject->dateTimeForm104}') as \"SportRegisterUMO_UMODate\",
			UR.UMOResult_name as \"UMOResult_name\",
			SC.SportCategory_name as \"SportCategory_name\",
			IGT.InvalidGroupType_Name as \"InvalidGroupType_Name\",
			SPG.SportParaGroup_name as \"SportParaGroup_name\",
			SRUMO.SportRegisterUMO_IsTeamMember as \"SportRegisterUMO_IsTeamMember\",
			to_char(SRUMO.SportRegisterUMO_AdmissionDtBeg, '{$callObject->dateTimeForm104}') as \"SportRegisterUMO_AdmissionDtBeg\",
			to_char(SRUMO.SportRegisterUMO_AdmissionDtEnd, '{$callObject->dateTimeForm104}') as \"SportRegisterUMO_AdmissionDtEnd\",
			to_char(SR.SportRegister_insDT, '{$callObject->dateTimeForm104}') as \"SportRegister_insDT\",
			to_char(SR.SportRegister_updDT, '{$callObject->dateTimeForm104}') as \"SportRegister_updDT\",
			to_char(SR.SportRegister_delDT, '{$callObject->dateTimeForm104}') as \"SportRegister_delDT\",
			OC.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
			RTRIM (MSFPSp.PersonSurName_SurName)|| ' ' || RTRIM (MSFPSp.PersonFirName_FirName)|| ' ' || RTRIM (MSFPSp.PersonSecName_SecName) AS \"MedPersonal_pname\",
			SO.SportOrg_name as \"SportOrg_name\",
			RTRIM (PSTr.PersonSurName_SurName)|| ' '|| RTRIM (PSTr.PersonFirName_FirName)|| ' ' || RTRIM (PSTr.PersonSecName_SecName) AS \"SportTrainer_name\"
		";
	}

	public static function searchData_SearchFormType_HTMRegister(Search_model $callObject)
	{
		return "
			HR.HTMRegister_id as \"HTMRegister_id\",
			HR.HTMRegister_IsSigned as \"HTMRegister_IsSigned\",
			HR.QueueNumber as \"QueueNumber\",
			R.Register_id as \"Register_id\",
			to_char(R.Register_setDate, '{$callObject->dateTimeForm104}') as \"Register_setDate\",
			to_char(PS.Person_deadDT, '{$callObject->dateTimeForm104}') as \"Register_disDate\",
			R.Person_id as \"Person_id\",
			PS.Person_SurName as \"Person_SurName\",
			PS.Person_FirName as \"Person_FirName\",
			PS.Person_SecName as \"Person_SecName\",
			PS.Server_id as \"Server_id\",
			to_char(PS.Person_BirthDay, '{$callObject->dateTimeForm104}') as \"Person_BirthDay\",
			Lpu.Lpu_id as \"Lpu_id\",
			Lpu.Lpu_Nick as \"Lpu_Nick\",
			LpuEDH.Lpu_id as \"Lpu_sid\",
			LpuEDH.Org_id as \"Org_sid\",
			LpuEDH.Lpu_Nick as \"Lpu_Nick2\",
			PS.Person_isDead as \"Person_isDead\",
			EDH.LpuSectionProfile_id as \"LpuSectionProfile_id\",
			LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
		";
	}

	public static function searchData_SearchFormType_GibtRegistry(Search_model $callObject)
	{
		return "
			PR.PersonRegister_id as \"PersonRegister_id\",
			MG.MorbusGEBT_id as \"MorbusGEBT_id\",
			PS.Person_id as \"Person_id\",
			PS.Server_id as \"Server_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			M.Morbus_id as \"Morbus_id\",
			D.Diag_id as \"Diag_id\",
			D.Diag_FullName as \"Diag_Name\",
			PROUT.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
			PROUT.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			to_char(PR.PersonRegister_setDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_setDate\",
			to_char(PR.PersonRegister_disDate, '{$callObject->dateTimeForm104}') as \"PersonRegister_disDate\",
			Lpu.Lpu_Nick as \"Lpu_Nick\"
		";
	}

	public static function searchData_SearchFormType_EvnERSBirthCertificate(Search_model $callObject)
	{
		return "
			ERS.EvnERSBirthCertificate_id as \"EvnERSBirthCertificate_id\",
			ERS.ERSStatus_id as \"ERSStatus_id\",
			ERS.ErsRequest_id as \"ErsRequest_id\",
			ERS.Person_id as \"Person_id\",
			ERS.Lpu_id as \"Lpu_id\",
			ERSt.ErsRequestStatus_id as \"ErsRequestStatus_id\",
			ERS.EvnERSBirthCertificate_Number as \"EvnERSBirthCertificate_Number\",
			RTRIM(PS.Person_SurName) as \"Person_Surname\",
			RTRIM(PS.Person_FirName) as \"Person_Firname\",
			RTRIM(PS.Person_SecName) as \"Person_Secname\",
			CCT.ERSCloseCauseType_Name as \"ERSCloseCauseType_Name\",
			ER.ERSRequest_ERSNumber as \"ERSRequest_ERSNumber\",
			ES.ERSStatus_Name as \"ERSStatus_Name\",
			to_char(ERS.EvnERSBirthCertificate_CreateDate, '{$callObject->dateTimeForm104}') as \"EvnERSBirthCertificate_CreateDate\",
			ERT.ErsRequestType_Name as \"ErsRequestType_Name\",
			ERSt.ErsRequestStatus_Name as \"ErsRequestStatus_Name\",
			substring(ERE.ERSRequestError, 1, length(ERE.ERSRequestError)-1) as \"ErsRequestError\",
			ticket1.ERSStatus_Name as \"EvnERSTicket1\",
			ticket2.ERSStatus_Name as \"EvnERSTicket2\",
			ticket31.ERSStatus_Name as \"EvnERSTicket31\",
			ticket32.ERSStatus_Name as \"EvnERSTicket32\"
		";
	}
}
