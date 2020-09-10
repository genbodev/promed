<?php

require_once(APPPATH.'models/Search_model.php');

class Samara_Search_model extends Search_model {
	
	/**
	 * Method description
	 */
	function __construct() {
		parent::__construct();
	}  

	/**
	 * Method description
	 */
	function getPersonCardFilters($data, &$filter, &$queryParams) {
		if ( $data['AttachLpu_id'] > 0 ) {
            $filter .= " and PS.Lpu_id = :AttachLpu_id ";
			$queryParams['AttachLpu_id'] = $data['AttachLpu_id'];
		}
	}    

	/**
	 *	Формирование и выполнение поискового запроса
	 */
	function searchData($data, $getCount = false, $print = false, $dbf = false) {
		$filter      = "(1 = 1)";
		$main_alias  = "";
		$queryParams = array();

		$query = "
			select
				-- select
		";
		
		if (($data['SearchFormType'] == 'EvnDiag' || substr($data['SearchFormType'],0,3) == 'Kvs') && $dbf === true)
			$query .= "
				distinct
			";
		
		$query .= "
				
		";

		$isFarmacy = (isset($data['session']['OrgFarmacy_id']));
		$queryParams['Lpu_id'] = $data['Lpu_id'];

		$PS_prefix = 'PS'; //таблица для выборки данных по застрахованным
		if (isset($data['kvs_date_type']))
			if ((in_array($data['PersonPeriodicType_id'], array(2, 3)) && $data['kvs_date_type'] == 1) || ($data['PersonPeriodicType_id'] == 1 && $data['kvs_date_type'] == 2)) //требуется дополнительная таблица для выгрузки данных по застрахованному
				$PS_prefix = 'PS2';
		
		if (isset($data['and_kvsperson']) && $data['and_kvsperson']) {
			if ( $dbf === true ) {
				$query .= "
					{$PS_prefix}.PersonEvn_id as PCT_ID,
					{$PS_prefix}.Person_id as P_ID,
					{$PS_prefix}.Person_SurName as SURNAME,
					{$PS_prefix}.Person_FirName as FIRNAME,
					{$PS_prefix}.Person_SecName as SECNAME,
					rtrim(isnull(convert(varchar(10), {$PS_prefix}.Person_BirthDay, 104),'')) as BIRTHDAY,
					{$PS_prefix}.Person_Snils as SNILS,
					PrsIsInv.YesNo_Code as INV_N,
					PrsInvD.Diag_Code as INV_DZ,
					rtrim(isnull(convert(varchar(10), PrsPCh.PersonChild_invDate, 104),'')) as INV_DATA,
					PrsSex.Sex_Name as SEX,
					PrsSoc.SocStatus_Name as SOC,
					PrsOMSST.OMSSprTerr_Code as P_TERK,
					PrsOMSST.OMSSprTerr_Name as P_TER,
					PrsPolTp.PolisType_Name as P_NAME,
					PrsPol.Polis_Ser as P_SER,
					PrsPol.Polis_Num as P_NUM,
					{$PS_prefix}.Person_EdNum as P_NUMED,
					rtrim(isnull(convert(varchar(10), PrsPol.Polis_begDate, 104),'')) as P_DATA,
					PrsOSO.Org_Code as SMOK,
					PrsOS.OrgSmo_Name as SMO,
					0 as AR_TP,
					PrsUA.Address_Zip as AR_IDX,
					PrsUA.KLCountry_Name as AR_LND,
					PrsUA.KLRGN_Name as AR_RGN,
					PrsUA.KLSubRGN_Name as AR_RN,
					PrsUA.KLCity_Name as AR_CTY,
					PrsUA.KLTown_Name as AR_NP,
					PrsUA.KLStreet_Name as AR_STR,
					PrsUA.Address_House as AR_DOM,
					PrsUA.Address_Corpus as AR_K,
					PrsUA.Address_Flat as AR_KV,
					0 as AP_TP,
					PrsPA.Address_Zip as AP_IDX,
					PrsPA.KLCountry_Name as AP_LND,
					PrsPA.KLRGN_Name as AP_RGN,
					PrsPA.KLSubRGN_Name as AP_RN,
					PrsPA.KLCity_Name as AP_CTY,
					PrsPA.KLTown_Name as AP_NP,
					PrsPA.KLStreet_Name as AP_STR,
					PrsPA.Address_House as AP_DOM,
					PrsPA.Address_Corpus as AP_K,
					PrsPA.Address_Flat as AP_KV,
					PrsDocTp.DocumentType_Name as D_TIP,
					PrsDoc.Document_Ser as D_SER,
					PrsDoc.Document_Num as D_NOM,
					PrsOrgD.Org_id as D_OUT,
					rtrim(isnull(convert(varchar(10), PrsDoc.Document_begDate, 104),'')) as D_DATA,
				";
			}
		}
		
		switch ( $data['SearchFormType'] ) {
			case 'CmpCallCard':
				$main_alias = "CCC";
				$query .= "
					'' as accessType,
					CCC.CmpCallCard_id,
					PS.Person_id,
					PS.PersonEvn_id,
					PS.Server_id,
					convert(varchar(10), CCC.CmpCallCard_prmDT, 104) as CmpCallCard_prmDate,
					convert(varchar(5), CCC.CmpCallCard_prmDT, 108) as CmpCallCard_prmTime,
					CCC.CmpCallCard_Numv,
					case when ISNULL(CClC.CmpCloseCard_id,0)=0 then 0 else CClC.CmpCloseCard_id end as CmpCloseCard_id,
					RTRIM(ISNULL(PS.Person_Surname, CCC.Person_SurName)) as Person_Surname,
					RTRIM(ISNULL(PS.Person_Firname, CCC.Person_FirName)) as Person_Firname,
					RTRIM(ISNULL(PS.Person_Secname, CCC.Person_SecName)) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					ISNULL(dbo.Age2(PS.Person_BirthDay, dbo.tzGetDate()), CCC.Person_Age) as Person_Age,
					case when PS.Person_id is not null then 'true' else 'false' end as Person_IsIdentified,
					RTRIM(ISNULL(CR.CmpReason_Name, '')) as CmpReason_Name,
					RTRIM(COALESCE(L.Lpu_Nick, replace(replace(CL.CmpLpu_Name, '=', ''), '_+', ' '), '')) as CmpLpu_Name,
					RTRIM(ISNULL(CD.CmpDiag_Name, '')) as CmpDiag_Name,
					RTRIM(ISNULL(D.Diag_Name, '')) as StacDiag_Name,
					COALESCE(PAddr.Address_Address, RTRIM(replace(CCC.CmpCallCard_PCity, '=', '')) + ', ' + RTRIM(CCC.CmpCallCard_PUlic) + ', ' + RTRIM(CCC.CmpCallCard_PDom) + ', ' + RTRIM(CCC.CmpCallCard_PKvar), '') as Person_Address
				";
			break;

			case 'PersonDopDisp':
				$main_alias = "DD";
				$query .= "
					DD.PersonDopDisp_id,
					PS.Person_id,
					IsNull(epldd.Server_id, PS.Server_id) as Server_id, -- проблема при редактировании талона по ДД (#6670) - с регистра передается текущая, с поиска талона - та которая в талоне.
					IsNull(epldd.PersonEvn_id, PS.PersonEvn_id) as PersonEvn_id, -- сделал так, что если есть талон, то бралась периодика с талона
					rtrim(PS.Person_SurName) as Person_Surname,
					rtrim(PS.Person_FirName) as Person_Firname,
					rtrim(PS.Person_SecName) as Person_Secname,
					convert(varchar,cast(PS.Person_BirthDay as datetime),104) as Person_Birthday,
					Sex.Sex_Name,
					PS.Polis_Ser,
					PS.Polis_Num,
					okved1.Okved_Name as PersonOrg_Okved,
					org1.Org_OGRN as PersonOrg_OGRN,
					astat1.KLArea_Name as Person_KLAreaStat_Name,
					astat2.KLArea_Name as PersonOrg_KLAreaStat_Name,
					rtrim(addr1.Address_Address) as UAddress_Address,
					isnull(rtrim(otherddlpu.Lpu_Nick), '') as OnDispInOtherLpu,
					max(epldd.EvnPLDispDop_id) as EvnPLDispDop_id,
					CASE WHEN max(epldd.EvnPLDispDop_id) is null THEN 'false' ELSE 'true' END as ExistsDDPL
				";
			break;
			
			case 'PersonDispOrp':
				$main_alias = "DO";
				$query .= "
					DO.PersonDispOrp_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					rtrim(PS.Person_SurName) as Person_Surname,
					rtrim(PS.Person_FirName) as Person_Firname,
					rtrim(PS.Person_SecName) as Person_Secname,
					UAdd.Address_Address as ua_name,
					PAdd.Address_Address as pa_name,
					Sex.Sex_Name,
					PS.Polis_Ser,
					PS.Polis_Num,
					okved1.Okved_Name as PersonOrg_Okved,
					org1.Org_OGRN as PersonOrg_OGRN,
					astat1.KLArea_Name as Person_KLAreaStat_Name,
					astat2.KLArea_Name as PersonOrg_KLAreaStat_Name,
					rtrim(addr1.Address_Address) as UAddress_Address,
					convert(varchar,cast(PS.Person_BirthDay as datetime),104) as Person_Birthday,
					case when DO.Org_id IS NOT NULL then 'Да' else 'Нет' end as OrgExist,
					isnull(rtrim(ODL.Lpu_Nick), '') as OnDispInOtherLpu,
					EPLDO.EvnPLDispOrp_id,
					CASE WHEN EPLDO.EvnPLDispOrp_id is null THEN 'false' ELSE 'true' END as ExistsDOPL
				";
			break;
			
			case 'PersonDispOrpPeriod':
				$main_alias = "DO";
				$query .= "
					DO.PersonDispOrp_id,
					DO.EducationInstitutionType_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					rtrim(PS.Person_SurName) as Person_Surname,
					rtrim(PS.Person_FirName) as Person_Firname,
					rtrim(PS.Person_SecName) as Person_Secname,
					UAdd.Address_Address as ua_name,
					PAdd.Address_Address as pa_name,
					convert(varchar,cast(PS.Person_BirthDay as datetime),104) as Person_Birthday,
					Sex.Sex_Name,
					LATT.Lpu_Nick as Lpu_Nick,
					EIT.EducationInstitutionType_Name,
					EPLDTI.EvnPLDispTeenInspection_id,
					CASE WHEN EPLDTI.EvnPLDispTeenInspection_id is null THEN 'false' ELSE 'true' END as ExistsDirection,
					CASE WHEN EPLDTI.EvnPLDispTeenInspection_id is null THEN 'false' ELSE 'true' END as ExistsDOPL
				";
			break;
			
			case 'PersonDispOrpPred':
				$main_alias = "DO";
				$query .= "
					DO.PersonDispOrp_id,
					DO.EducationInstitutionType_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					rtrim(PS.Person_SurName) as Person_Surname,
					rtrim(PS.Person_FirName) as Person_Firname,
					rtrim(PS.Person_SecName) as Person_Secname,
					convert(varchar,cast(PS.Person_BirthDay as datetime),104) as Person_Birthday,
					Sex.Sex_Name,
					LATT.Lpu_Nick as Lpu_Nick,
					EIT.EducationInstitutionType_Name,
					EPLDTI.EvnPLDispTeenInspection_id,
					CASE WHEN EPLDTI.EvnPLDispTeenInspection_id is null THEN 'false' ELSE 'true' END as ExistsDOPL
				";
			break;
			
			case 'PersonDispOrpProf':
				$main_alias = "DO";
				$query .= "
					DO.PersonDispOrp_id,
					DO.AgeGroupDisp_id,
					DO.Org_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					rtrim(PS.Person_SurName) as Person_Surname,
					rtrim(PS.Person_FirName) as Person_Firname,
					rtrim(PS.Person_SecName) as Person_Secname,
					convert(varchar,cast(PS.Person_BirthDay as datetime),104) as Person_Birthday,
					Sex.Sex_Name,
					AGD.AgeGroupDisp_Name,
					convert(varchar,cast(DO.PersonDispOrp_begDate as datetime),104) as PersonDispOrp_begDate,
					EPLDTI.EvnPLDispTeenInspection_id,
					CASE WHEN DO.Org_id is null THEN 'false' ELSE 'true' END as OrgExist,
					CASE WHEN EPLDTI.EvnPLDispTeenInspection_id is null THEN 'false' ELSE 'true' END as ExistsDOPL
				";
			break;
			
			case 'PersonDispOrpOld':
				$main_alias = "DO";
				$query .= "
					DO.PersonDispOrp_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					rtrim(PS.Person_SurName) as Person_Surname,
					rtrim(PS.Person_FirName) as Person_Firname,
					rtrim(PS.Person_SecName) as Person_Secname,
					Sex.Sex_Name,
					PS.Polis_Ser,
					PS.Polis_Num,
					okved1.Okved_Name as PersonOrg_Okved,
					org1.Org_OGRN as PersonOrg_OGRN,
					astat1.KLArea_Name as Person_KLAreaStat_Name,
					astat2.KLArea_Name as PersonOrg_KLAreaStat_Name,
					rtrim(addr1.Address_Address) as UAddress_Address,
					convert(varchar,cast(PS.Person_BirthDay as datetime),104) as Person_Birthday,
					isnull(rtrim(ODL.Lpu_Nick), '') as OnDispInOtherLpu,
					EPLDO.EvnPLDispOrp_id,
					CASE WHEN EPLDO.EvnPLDispOrp_id is null THEN 'false' ELSE 'true' END as ExistsDOPL
				";
			break;
			
			case 'EvnPLDispDop13':
				$main_alias = "EPLDD13";
				$query .= "
					[EPLDD13].[EvnPLDispDop13_id] as [EvnPLDispDop13_id],
					[PS].[Person_id] as [Person_id],
					[PS].[Server_id] as [Server_id],
					[EPLDD13].[PersonEvn_id] as [PersonEvn_id],
					RTRIM([PS].[Person_Surname]) as [Person_Surname],
					RTRIM([PS].[Person_Firname]) as [Person_Firname],
					RTRIM([PS].[Person_Secname]) as [Person_Secname],
					UAdd.Address_Address as ua_name,
					PAdd.Address_Address as pa_name,
					convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
					[IsFinish].[YesNo_Name] as [EvnPLDispDop13_IsEndStage],
					[IsFinishSecond].[YesNo_Name] as [EvnPLDispDop13Second_IsEndStage],
					convert(varchar(10), [EPLDD13].[EvnPLDispDop13_consDT], 104) as [EvnPLDispDop13_setDate],
					convert(varchar(10), [EPLDD13].[EvnPLDispDop13_disDate], 104) as [EvnPLDispDop13_disDate],
					HK.HealthKind_Name as [EvnPLDispDop13_HealthKind_Name],
					case when [EPLDD13].EvnPLDispDop13_IsEndStage = 2 and EPLDD13.EvnPLDispDop13_IsTwoStage = 2 then convert(varchar(10), [EPLDD13].[EvnPLDispDop13_disDate], 104) else null end as EvnPLDispDop13Second_napDate,
					DopDispSecond.HealthKind_Name as EvnPLDispDop13Second_HealthKind_Name,
					case when DDICData.DopDispInfoConsent_IsAgree = 1 then convert(varchar(10), [EPLDD13].[EvnPLDispDop13_consDT], 104) else null end as EvnPLDispDop13_rejDate,
					case when exists (select top 1 EvnPLDispDop13_id from v_EvnPLDispDop13 (nolock) where Person_id = PS.Person_id and YEAR(EvnPLDispDop13_setDate) = :PersonDopDisp_Year and Lpu_id <> :Lpu_id) then 4 else 0 end as AccessType_Code,
					DopDispSecond.EvnPLDispDop13_id as EvnPLDispDop13Second_id,
					convert(varchar(10), DopDispSecond.EvnPLDispDop13_consDT, 104) as EvnPLDispDop13Second_setDate,
					convert(varchar(10), DopDispSecond.EvnPLDispDop13_disDate, 104) as EvnPLDispDop13Second_disDate,
					case when DDICDataSecond.DopDispInfoConsent_IsAgree = 1 then convert(varchar(10), DopDispSecond.EvnPLDispDop13_consDT, 104) else null end as EvnPLDispDop13Second_rejDate
				";
			break;
			
			case 'EvnPLDispProf':
				$main_alias = "EPLDP";
				$query .= "
					[EPLDP].[EvnPLDispProf_id] as [EvnPLDispProf_id],
					[PS].[Person_id] as [Person_id],
					[PS].[Server_id] as [Server_id],
					[EPLDP].[PersonEvn_id] as [PersonEvn_id],
					RTRIM([PS].[Person_Surname]) as [Person_Surname],
					RTRIM([PS].[Person_Firname]) as [Person_Firname],
					RTRIM([PS].[Person_Secname]) as [Person_Secname],
					UAdd.Address_Address as ua_name,
					PAdd.Address_Address as pa_name,
					convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
					[IsFinish].[YesNo_Name] as [EvnPLDispProf_IsEndStage],
					convert(varchar(10), [EPLDP].[EvnPLDispProf_consDT], 104) as [EvnPLDispProf_setDate],
					convert(varchar(10), [EPLDP].[EvnPLDispProf_disDate], 104) as [EvnPLDispProf_disDate],
					HK.HealthKind_Name as [EvnPLDispProf_HealthKind_Name],
					case when DDICData.DopDispInfoConsent_IsAgree = 1 then convert(varchar(10), [EPLDP].[EvnPLDispProf_consDT], 104) else null end as EvnPLDispProf_rejDate,
					case when exists (select top 1 EvnPLDispProf_id from v_EvnPLDispProf (nolock) where Person_id = PS.Person_id and YEAR(EvnPLDispProf_setDate) = :PersonDopDisp_Year and Lpu_id <> :Lpu_id) then 4 else 0 end as AccessType_Code
				";
			break;
			
			case 'EvnPLDispDop':
				$main_alias = "EPLDD";
				$query .= "
					[EPLDD].[EvnPLDispDop_id] as [EvnPLDispDop_id],
					[EPLDD].[Person_id] as [Person_id],
					[EPLDD].[Server_id] as [Server_id],
					[EPLDD].[PersonEvn_id] as [PersonEvn_id],
					RTRIM([PS].[Person_Surname]) as [Person_Surname],
					RTRIM([PS].[Person_Firname]) as [Person_Firname],
					RTRIM([PS].[Person_Secname]) as [Person_Secname],
					convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
					[EPLDD].[EvnPLDispDop_VizitCount] as [EvnPLDispDop_VizitCount],
					[IsFinish].[YesNo_Name] as [EvnPLDispDop_IsFinish],
					convert(varchar(10), [EPLDD].[EvnPLDispDop_setDate], 104) as [EvnPLDispDop_setDate],
					convert(varchar(10), [EPLDD].[EvnPLDispDop_disDate], 104) as [EvnPLDispDop_disDate]
				";
			break;
		
			case 'EvnPLDispTeen14':
				$main_alias = "EPLDT14";
				$query .= "
					[EPLDT14].[EvnPLDispTeen14_id] as [EvnPLDispTeen14_id],
					[EPLDT14].[Person_id] as [Person_id],
					[EPLDT14].[Server_id] as [Server_id],
					[EPLDT14].[PersonEvn_id] as [PersonEvn_id],
					RTRIM([PS].[Person_Surname]) as [Person_Surname],
					RTRIM([PS].[Person_Firname]) as [Person_Firname],
					RTRIM([PS].[Person_Secname]) as [Person_Secname],
					convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
					[EPLDT14].[EvnPLDispTeen14_VizitCount] as [EvnPLDispTeen14_VizitCount],
					[IsFinish].[YesNo_Name] as [EvnPLDispTeen14_IsFinish],
					convert(varchar(10), [EPLDT14].[EvnPLDispTeen14_setDate], 104) as [EvnPLDispTeen14_setDate],
					convert(varchar(10), [EPLDT14].[EvnPLDispTeen14_disDate], 104) as [EvnPLDispTeen14_disDate]
				";
			break;
			
			case 'EvnPLDispOrp':
				$main_alias = "EPLDO";
				$query .= "
					[EPLDO].[EvnPLDispOrp_id] as [EvnPLDispOrp_id],
					[EPLDO].[Person_id] as [Person_id],
					[EPLDO].[Server_id] as [Server_id],
					[EPLDO].[PersonEvn_id] as [PersonEvn_id],
					RTRIM([PS].[Person_Surname]) as [Person_Surname],
					RTRIM([PS].[Person_Firname]) as [Person_Firname],
					RTRIM([PS].[Person_Secname]) as [Person_Secname],
					Sex.Sex_Name,
					convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
					[EPLDO].[EvnPLDispOrp_VizitCount] as [EvnPLDispOrp_VizitCount],
					[EPLDO].[DispClass_id] as [DispClass_id],
					[IsFinish].[YesNo_Name] as [EvnPLDispOrp_IsFinish],
					[IsTwoStage].[YesNo_Name] as [EvnPLDispOrp_IsTwoStage],
					convert(varchar(10), [EPLDO].[EvnPLDispOrp_setDate], 104) as [EvnPLDispOrp_setDate],
					convert(varchar(10), [EPLDO].[EvnPLDispOrp_disDate], 104) as [EvnPLDispOrp_disDate]
				";
			break;
			
			case 'EvnPLDispOrpOld':
				$main_alias = "EPLDO";
				$query .= "
					[EPLDO].[EvnPLDispOrp_id] as [EvnPLDispOrp_id],
					[EPLDO].[Person_id] as [Person_id],
					[EPLDO].[Server_id] as [Server_id],
					[EPLDO].[PersonEvn_id] as [PersonEvn_id],
					RTRIM([PS].[Person_Surname]) as [Person_Surname],
					RTRIM([PS].[Person_Firname]) as [Person_Firname],
					RTRIM([PS].[Person_Secname]) as [Person_Secname],
					convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
					[EPLDO].[EvnPLDispOrp_VizitCount] as [EvnPLDispOrp_VizitCount],
					[IsFinish].[YesNo_Name] as [EvnPLDispOrp_IsFinish],
					convert(varchar(10), [EPLDO].[EvnPLDispOrp_setDate], 104) as [EvnPLDispOrp_setDate],
					convert(varchar(10), [EPLDO].[EvnPLDispOrp_disDate], 104) as [EvnPLDispOrp_disDate]
				";
			break;

			case 'EvnPLDispOrpSec':
				$main_alias = "EPLDO";
				$query .= "
					[EPLDO].[EvnPLDispOrp_id] as [EvnPLDispOrp_id],
					[EPLDO].[Person_id] as [Person_id],
					[EPLDO].[Server_id] as [Server_id],
					[EPLDO].[PersonEvn_id] as [PersonEvn_id],
					RTRIM([PS].[Person_Surname]) as [Person_Surname],
					RTRIM([PS].[Person_Firname]) as [Person_Firname],
					RTRIM([PS].[Person_Secname]) as [Person_Secname],
					Sex.Sex_Name,
					convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
					[EPLDO].[EvnPLDispOrp_VizitCount] as [EvnPLDispOrp_VizitCount],
					[EPLDO].[DispClass_id] as [DispClass_id],
					[IsFinish].[YesNo_Name] as [EvnPLDispOrp_IsFinish],
					[IsTwoStage].[YesNo_Name] as [EvnPLDispOrp_IsTwoStage],
					convert(varchar(10), [EPLDO].[EvnPLDispOrp_setDate], 104) as [EvnPLDispOrp_setDate],
					convert(varchar(10), [EPLDO].[EvnPLDispOrp_disDate], 104) as [EvnPLDispOrp_disDate]
				";
			break;
			
			case 'EvnPLDispTeenInspectionPeriod':
			case 'EvnPLDispTeenInspectionProf':
			case 'EvnPLDispTeenInspectionPred':
				$main_alias = "EPLDTI";
				$query .= "
					[EPLDTI].[EvnPLDispTeenInspection_id] as [EvnPLDispTeenInspection_id],
					[EPLDTI].[EvnPLDispTeenInspection_fid] as [EvnPLDispTeenInspection_fid],
					[EPLDTI].[Person_id] as [Person_id],
					[EPLDTI].[Server_id] as [Server_id],
					[EPLDTI].[PersonEvn_id] as [PersonEvn_id],
					RTRIM([PS].[Person_Surname]) as [Person_Surname],
					RTRIM([PS].[Person_Firname]) as [Person_Firname],
					RTRIM([PS].[Person_Secname]) as [Person_Secname],
					UAdd.Address_Address as ua_name,
					PAdd.Address_Address as pa_name,
					Sex.Sex_Name,
					AGD.AgeGroupDisp_Name,
					case when ISNULL(EPLDTI.Org_id,PDORP.Org_id) IS NOT NULL then 'true' else 'false' end as OrgExist,
					convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
					[EPLDTI].[EvnPLDispTeenInspection_VizitCount] as [EvnPLDispTeenInspection_VizitCount],
					[IsFinish].[YesNo_Name] as [EvnPLDispTeenInspection_IsFinish],
					[IsTwoStage].[YesNo_Name] as [EvnPLDispTeenInspection_IsTwoStage],
					case when PDORP.PersonDispOrp_id is not null then 'true' else 'false' end as EvnPLDispTeenInspection_hasDirection,
					convert(varchar(10), [EPLDTI].[EvnPLDispTeenInspection_setDate], 104) as [EvnPLDispTeenInspection_setDate],
					convert(varchar(10), [EPLDTI].[EvnPLDispTeenInspection_disDate], 104) as [EvnPLDispTeenInspection_disDate]
				";
			break;
			
			case 'EvnPLDispDopStream':
				$main_alias = "EPLDD";
				$query .= "
					[EPLDD].[EvnPLDispDop_id] as [EvnPLDispDop_id],
					[EPLDD].[Person_id] as [Person_id],
					[EPLDD].[Server_id] as [Server_id],
					[EPLDD].[PersonEvn_id] as [PersonEvn_id],
					RTRIM([PS].[Person_Surname]) as [Person_Surname],
					RTRIM([PS].[Person_Firname]) as [Person_Firname],
					RTRIM([PS].[Person_Secname]) as [Person_Secname],
					convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
					[EPLDD].[EvnPLDispDop_VizitCount] as [EvnPLDispDop_VizitCount],
					[IsFinish].[YesNo_Name] as [EvnPLDispDop_IsFinish],
					convert(varchar(10), [EPLDD].[EvnPLDispDop_setDate], 104) as [EvnPLDispDop_setDate],
					convert(varchar(10), [EPLDD].[EvnPLDispDop_disDate], 104) as [EvnPLDispDop_disDate]
				";
			break;
		
			case 'EvnPLDispTeen14Stream':
				$main_alias = "EPLDT14";
				$query .= "
					[EPLDT14].[EvnPLDispTeen14_id] as [EvnPLDispTeen14_id],
					[EPLDT14].[Person_id] as [Person_id],
					[EPLDT14].[Server_id] as [Server_id],
					[EPLDT14].[PersonEvn_id] as [PersonEvn_id],
					RTRIM([PS].[Person_Surname]) as [Person_Surname],
					RTRIM([PS].[Person_Firname]) as [Person_Firname],
					RTRIM([PS].[Person_Secname]) as [Person_Secname],
					convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
					[EPLDT14].[EvnPLDispTeen14_VizitCount] as [EvnPLDispTeen14_VizitCount],
					[IsFinish].[YesNo_Name] as [EvnPLDispTeen14_IsFinish],
					convert(varchar(10), [EPLDT14].[EvnPLDispTeen14_setDate], 104) as [EvnPLDispTeen14_setDate],
					convert(varchar(10), [EPLDT14].[EvnPLDispTeen14_disDate], 104) as [EvnPLDispTeen14_disDate]
				";
			break;
			
			case 'EvnPLDispOrpStream':
				$main_alias = "EPLDO";
				$query .= "
					[EPLDO].[EvnPLDispOrp_id] as [EvnPLDispOrp_id],
					[EPLDO].[Person_id] as [Person_id],
					[EPLDO].[Server_id] as [Server_id],
					[EPLDO].[PersonEvn_id] as [PersonEvn_id],
					RTRIM([PS].[Person_Surname]) as [Person_Surname],
					RTRIM([PS].[Person_Firname]) as [Person_Firname],
					RTRIM([PS].[Person_Secname]) as [Person_Secname],
					convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
					[EPLDO].[EvnPLDispOrp_VizitCount] as [EvnPLDispOrp_VizitCount],
					[EPLDO].[DispClass_id] as [DispClass_id],
					[IsFinish].[YesNo_Name] as [EvnPLDispOrp_IsFinish],
					[IsTwoStage].[YesNo_Name] as [EvnPLDispOrp_IsTwoStage],
					convert(varchar(10), [EPLDO].[EvnPLDispOrp_setDate], 104) as [EvnPLDispOrp_setDate],
					convert(varchar(10), [EPLDO].[EvnPLDispOrp_disDate], 104) as [EvnPLDispOrp_disDate]
				";
			break;		
			
			case 'EvnUsluga':
				$main_alias = "EvnUsluga";
				if ( $dbf === true ) {
					$query .= "
						EPL.EvnPL_id as EPL_ID,
						EvnUsluga.EvnUsluga_pid as EPZ_ID,
						EvnUsluga.EvnUsluga_id as EUS_ID,
						EvnUsluga.EvnClass_SysNick as EU_CLASS,
						RTRIM(ISNULL(convert(varchar(10), EvnUsluga.EvnUsluga_setDT, 104), '')) as SETDATE,
						EvnUsluga.EvnUsluga_setTime as SETTIME,
						dbfusluga.UslugaComplex_Code as USL_CODE,
						EvnUsluga.EvnUsluga_Kolvo as KOLVO,
						dbfup.UslugaPlace_Code as UP_CODE,
						dbfmp.MedPersonal_TabCode as MP_CODE,
						dbfpt.PayType_Code as PT_CODE
					";
				}
			break;
			
			case 'EvnSection':
				$main_alias = "ESEC";
				if ( $dbf === true ) {
					$query .= "
						ESEC.EvnSection_id as ESEC_COD,
						ESEC.EvnSection_pid as EPS_COD,
						RTRIM(ISNULL(convert(varchar(10), ESEC.EvnSection_setDate, 104), '')) as SETDATE,
						ESEC.EvnSection_setTime as SETTIME,
						RTRIM(ISNULL(convert(varchar(10), ESEC.EvnSection_disDate, 104), '')) as DISDATE,
						ESEC.EvnSection_disTime as DISTIME,
						dbfsec.LpuSection_Code as LS_COD,
						dbfpay.PayType_Code as PAY_COD,
						dbftar.TariffClass_Code as TFCLS_COD,
						dbfmp.MedPersonal_TabCode as MP_COD
					";
				}
				else {
					$query .= "
						 ESEC.EvnSection_id
						,ESEC.EvnSection_pid
						,EPS.Person_id as Person_id
						,EPS.PersonEvn_id as PersonEvn_id
						,EPS.Server_id as Server_id
						,RTRIM(ISNULL(EPS.EvnPS_NumCard, '')) as EvnPS_NumCard
						,RTRIM(PS.Person_SurName) as Person_Surname
						,RTRIM(PS.Person_FirName) as Person_Firname
						,RTRIM(PS.Person_SecName) as Person_Secname
						,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
						,convert(varchar(10), PS.Person_DeadDT, 104) as Person_deadDT
						,ISNULL(LS.LpuSection_Name, '') as LpuSection_Name
						,ISNULL(LSW.LpuSectionWard_Name, '') as LpuSectionWard_Name
						,ISNULL(Dtmp.Diag_FullName, '') as Diag_Name
						,convert(varchar(10), ESEC.EvnSection_setDate, 104) as EvnSection_setDate
						,convert(varchar(10), ESEC.EvnSection_disDate, 104) as EvnSection_disDate
						,ISNULL(LT.LeaveType_Name, '') as LeaveType_Name
						,PT.PayType_Name as PayType_Name
						,RTRIM(ISNULL(MP.Person_Fio, '')) as MedPersonal_Fio
						,MP.MedStaffFact_id AS MedStaffFact_id
						,case when ESEC.EvnSection_disDate is not null
							then
								case
									when DATEDIFF(DAY, ESEC.EvnSection_setDate, ESEC.EvnSection_disDate) + 1 > 1
									then DATEDIFF(DAY, ESEC.EvnSection_setDate, ESEC.EvnSection_disDate)
									else DATEDIFF(DAY, ESEC.EvnSection_setDate, ESEC.EvnSection_disDate) + 1
								end
							else null
						  end as EvnSection_KoikoDni
						 ,MES.Mes_Code
						 ,CASE WHEN PS.Server_pid = 0 THEN 'true' ELSE 'false' END as Person_IsBDZ
						 ,CASE WHEN ESEC.EvnSection_Index = ESEC.EvnSection_Count-1 THEN 1 ELSE 0 END as EvnSection_isLast
					";
				}
			break;
			
			case 'EvnDiag':
				$main_alias = "EPSD";
				if ( $dbf === true ) {
					$query .= "
						EDPS.EvnDiagPS_id as EPSDZ_COD,
						EDPS.EvnDiagPS_pid as EVN_COD,
						RTRIM(ISNULL(convert(varchar(10), EDPS.EvnDiagPS_setDate, 104), '')) as SETDATE,
						EDPS.EvnDiagPS_setTime as SETTIME,
						EDPS.Diag_id as DZ_COD,
						EDPS.DiagSetClass_id as DZCLS_COD,
						EDPS.DiagSetType_id as DZTYP_COD,
						dbfmes.Mes_id as MES_COD
					";
				}
			break;
			
			case 'EvnLeave':
				$main_alias = "ELV";
				if ( $dbf === true ) {
					$query .= "
						ISNULL(ELV.EvnLeave_id, ISNULL(dbfeol.EvnOtherLpu_id, ISNULL(dbfed.EvnDie_id, ISNULL(dbfeost.EvnOtherStac_id, dbfeos.EvnOtherSection_id)))) as ELV_COD,
						EPS.EvnPS_id as EPS_COD,
						EPS.LeaveType_id as LVTYP_COD,	
						ISNULL(ELV.LeaveCause_id, ISNULL(dbfeol.LeaveCause_id, ISNULL(dbfeost.LeaveCause_id, dbfeos.LeaveCause_id))) as LVCS_COD,
						ISNULL(ELV.ResultDesease_id, ISNULL(dbfeol.ResultDesease_id, ISNULL(dbfeost.ResultDesease_id, dbfeos.ResultDesease_id))) as RSTDSS_COD,
						ELV.EvnLeave_IsAmbul as ISAMBUL,
						ISNULL(ELV.EvnLeave_UKL, ISNULL(dbfeol.EvnOtherLpu_UKL, ISNULL(dbfed.EvnDie_UKL, ISNULL(dbfeost.EvnOtherStac_UKL, dbfeos.EvnOtherSection_UKL)))) as UKL,	
						dbflu.LpuUnitType_id as LSTYP_COD,	
						dbfeost.LeaveCause_id as OPSCS_COD,	
						dbfls.LpuSection_id as LS_COD,	
						dbfeol.Lpu_id as OLPU_OGRN,
						dbfeol.LeaveCause_id as OLPUCS_COD,
						dbfeos.LeaveCause_id as OLSCS_COD,
						dbfed.MedPersonal_id as MP_COD,
						dbfed.IsWait as ISWAIT,
						dbfed.EvnDie_IsAnatom as ISANATOM,
						null as ISBIRTH,
						dbfed.MedPersonal_aid as AMP_COD,
						dbfed.AnatomWhere_id as ANWHR_COD
					";
				}
			break;
			
			case 'EvnStick':
				$main_alias = "EST";
				if ( $dbf === true ) {
					$query .= "
						EST.EvnStick_id as EST_COD,
						EST.EvnStick_pid as EPS_COD,
						RTRIM(ISNULL(convert(varchar(10), EPS.EvnPS_setDate, 104), '')) as SETDATE,
						EST.StickType_id as STKTYP_COD,
						EST.EvnStick_Ser as SER,
						EST.EvnStick_Num as NUM,
						EST.StickOrder_id as ISCONT,
						EST.EvnStick_Age as AGE,
						RTRIM(ISNULL(convert(varchar(10), EST.EvnStick_begDate, 104), '')) as BEGDATE,
						RTRIM(ISNULL(convert(varchar(10), EST.EvnStick_endDate, 104), '')) as ENDDATE,
						EST.Sex_id as SEX_COD,
						EST.StickCause_id as STKCS_COD
					";
				}
			break;
			
			case 'KvsPerson':
				$main_alias = "PS";
				if ( $dbf === true ) {
					$query .= "
						{$PS_prefix}.PersonEvn_id as PCT_ID,
						{$PS_prefix}.Person_id as P_ID,
						{$PS_prefix}.Person_SurName as SURNAME,
						{$PS_prefix}.Person_FirName as FIRNAME,
						{$PS_prefix}.Person_SecName as SECNAME,
						rtrim(isnull(convert(varchar(10), {$PS_prefix}.Person_BirthDay, 104),'')) as BIRTHDAY,
						{$PS_prefix}.Person_Snils as SNILS,
						IsInv.YesNo_Code as INV_N,
						InvD.Diag_Code as INV_DZ,
						rtrim(isnull(convert(varchar(10), PCh.PersonChild_invDate, 104),'')) as INV_DATA,
						Sex.Sex_Name as SEX,
						Soc.SocStatus_Name as SOC,
						OMSST.OMSSprTerr_Code as P_TERK,
						OMSST.OMSSprTerr_Name as P_TER,
						PolTp.PolisType_Name as P_NAME,
						Pol.Polis_Ser as P_SER,
						Pol.Polis_Num as P_NUM,
						{$PS_prefix}.Person_EdNum as P_NUMED,
						rtrim(isnull(convert(varchar(10), Pol.Polis_begDate, 104),'')) as P_DATA,
						OSO.Org_Code as SMOK,
						OS.OrgSmo_Name as SMO,
						0 as AR_TP,
						UA.Address_Zip as AR_IDX,
						UA.KLCountry_Name as AR_LND,
						UA.KLRGN_Name as AR_RGN,
						UA.KLSubRGN_Name as AR_RN,
						UA.KLCity_Name as AR_CTY,
						UA.KLTown_Name as AR_NP,
						UA.KLStreet_Name as AR_STR,
						UA.Address_House as AR_DOM,
						UA.Address_Corpus as AR_K,
						UA.Address_Flat as AR_KV,
						0 as AP_TP,
						PA.Address_Zip as AP_IDX,
						PA.KLCountry_Name as AP_LND,
						PA.KLRGN_Name as AP_RGN,
						PA.KLSubRGN_Name as AP_RN,
						PA.KLCity_Name as AP_CTY,
						PA.KLTown_Name as AP_NP,
						PA.KLStreet_Name as AP_STR,
						PA.Address_House as AP_DOM,
						PA.Address_Corpus as AP_K,
						PA.Address_Flat as AP_KV,
						DocTp.DocumentType_Name as D_TIP,
						Doc.Document_Ser as D_SER,
						Doc.Document_Num as D_NOM,
						OrgD.Org_id as D_OUT,
						rtrim(isnull(convert(varchar(10), Doc.Document_begDate, 104),'')) as D_DATA
					";
				}
			break;
			
			case 'KvsPersonCard':
				$main_alias = "PC";
				if ( $dbf === true ) {
					$query .= "
						PC.PersonCard_id as REG_ID,
						".(isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,")."
						PC.Person_id as P_ID,
						PC.PersonCard_Code as PR_AK,
						PC.LpuAttachType_Name as PR_TP,
						rtrim(isnull(convert(varchar(10), PC.PersonCard_begDate, 104),'')) as PR_DATA,
						Lpu.Org_Code as LPUK,
						Lpu.Lpu_Name as LPU,
						PC.LpuRegionType_Name as TPLOT,
						PC.LpuRegion_Name as LOT
					";
				}
			break;
			
			case 'KvsEvnDiag':
				$main_alias = "EDPS";
				if ( $dbf === true ) {
					$query .= "
						EDPS.EvnDiagPS_id as DZ_ID,
						EPS.EvnPS_id as GSP_ID,
						".(isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,")."
						PS.Person_id as P_ID,
						(case 
							when EDPS.DiagSetType_id = 1 then 1
							when EDPS.DiagSetType_id = 2 then 2
							when EDPS.DiagSetType_id = 3 then 3
							else 0
						end) as KTO,
						Lpu.Org_Code as LPUK,
						Lpu.Lpu_Name as LPU,
						LS.LpuSection_Code as OTDK,
						LS.LpuSection_Name as OTD,
						rtrim(isnull(convert(varchar(10), EDPS.EvnDiagPS_setDT, 104),'')) as DZ_DATA,
						DSC.DiagSetClass_Name as DZ_W,
						DST.DiagSetType_Name as DZ_T,
						Diag.Diag_Code as DZ_DZ	
					";
				}
			break;
			
			case 'KvsEvnPS':
				$main_alias = "EPS";
				if ( $dbf === true ) {
					$query .= "
						EPS.EvnPs_id as GSP_ID,
						".(isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,")."
						PS.Person_id as P_ID,
						IsCont.YesNo_Code as KARTPR,
						EPS.EvnPS_NumCard as KART,
						PT.PayType_Name as WOPL,
						rtrim(isnull(convert(varchar(10), EPS.EvnPS_setDate, 104),'')) as DATAPOST,
						EPS.EvnPS_setTime as TIMEPOST,
						dbo.Age(PS.Person_BirthDay, EPS.EvnPS_setDate) as AGEPOST,
						PD.PrehospDirect_Name as KN_KT,
						LS.LpuSection_Code as KN_OTDLK,
						LS.LpuSection_Name as KN_OTDL,
						Org.Org_Code as KN_ORGK,
						Org.Org_Name as KN_ORG,
						IsFond.YesNo_Code as KN_FD,
						EPS.EvnDirection_Num as KN_N,
						rtrim(isnull(convert(varchar(10), EPS.EvnDirection_setDT, 104),'')) as KN_DATA,
						PA.PrehospArrive_Name as KD_KT,
						EPS.EvnPS_CodeConv as KD_KOD,
						EPS.EvnPS_NumConv as KD_NN,
						DiagD.Diag_Code as DZGOSP,
						IsImperHosp.YesNo_Code as DEF_NG,
						IsShortVolume.YesNo_Code as DEF_NOO,
						IsWrongCure.YesNo_Code as DEF_NTL,
						IsDiagMismatch.YesNo_Code as DEF_ND,
						Toxic.PrehospToxic_Name as ALKO,
						PType.PrehospType_Name as PR_GP,
						EPS.EvnPS_HospCount as PR_N,
						EPS.EvnPS_TimeDesease as PR_W,
						Trauma.TraumaType_Name as TR_T,
						IsUnLaw.YesNo_Code as TR_P,
						IsUnport.YesNo_Code as TR_N,
						pLS.LpuSection_Code as PRO_NK,
						pLS.LpuSection_Name as PRO_N,
						MP.MedPersonal_TabCode as PRO_DOCK,
						MP.Person_FIO as PRO_DOC,
						Diag.Diag_Code as PRO_DZ
					";
				}
			break;
			
			case 'KvsEvnSection':
				$main_alias = "ESEC";
				if ( $dbf === true ) {
					$query .= "
						ESEC.EvnSection_id as HSTRY_ID,
						EPS.EvnPS_id as GSP_ID,
						".(isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,")."
						PS.Person_id as P_ID,
						rtrim(isnull(convert(varchar(10), ESEC.EvnSection_setDate, 104),'')) as DATAP,
						ESEC.EvnSection_setTime as TIMEP,
						rtrim(isnull(convert(varchar(10), ESEC.EvnSection_disDate, 104),'')) as DATAW,
						ESEC.EvnSection_disTime as TIMEW,
						LS.LpuSection_Code as OTDLK,
						LS.LpuSection_Name as OTDL,
						PT.PayType_Name as WO,
						TC.TariffClass_Name as WT,
						MP.MedPersonal_TabCode as DOCK,
						MP.Person_Fio as DOC,
						Diag.Diag_Code as DZ,
						Mes.Mes_Code as MES,
						Mes.Mes_KoikoDni as NORM,
						case when ESEC.EvnSection_disDate is not null
							then
								case
									when LUT.LpuUnitType_Code = 2 and DATEDIFF(DAY, ESEC.EvnSection_setDate, ESEC.EvnSection_disDate) + 1 > 1
									then DATEDIFF(DAY, ESEC.EvnSection_setDate, ESEC.EvnSection_disDate)
									else DATEDIFF(DAY, ESEC.EvnSection_setDate, ESEC.EvnSection_disDate) + 1
								end
							else null
						end as KDN
					";
				}
			break;

			case 'KvsNarrowBed':
				$main_alias = "ESNB";
				if ( $dbf === true ) {
					$query .= "
						ESNB.EvnSectionNarrowBed_id as UK_ID,
						".(isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,")."
						PS.Person_id as P_ID,
						EPS.EvnPS_id as GSP_ID,
						ESEC.EvnSection_id as HSTRY_ID,
						rtrim(isnull(convert(varchar(10), ESNB.EvnSectionNarrowBed_setDT, 104),'')) as DATAP,
						rtrim(isnull(convert(varchar(10), ESNB.EvnSectionNarrowBed_disDT, 104),'')) as DATAW,
						LS.LpuSection_Code as OTDLK,
						LS.LpuSection_Name as OTDL
					";
				}
			break;

			case 'KvsEvnUsluga':
				$main_alias = "EU";
				if ( $dbf === true ) {
					$query .= "
						EU.EvnUsluga_id as U_ID,
						".(isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,")."
						PS.Person_id as P_ID,
						EPS.EvnPS_id as GSP_ID,
						UC.UslugaClass_Name as U_TIP,
						rtrim(isnull(convert(varchar(10), EU.EvnUsluga_setDate, 104),'')) as U_DATA,
						EU.EvnUsluga_setTime as U_TIME,
						UP.UslugaPlace_Name as U_MESTO,
						LS.LpuSection_Code as U_OTELK,
						LS.LpuSection_Name as U_OTEL,
						Lpu.Org_Code as U_LPUK,
						Lpu.Lpu_Name as U_LPU,
						Org.Org_Code as U_ORGK,
						Org.Org_Name as U_ORG,
						MP.MedPersonal_TabCode as U_DOCK,
						MP.Person_Fio as U_DOC,
						U.UslugaComplex_Code as U_USLKOD,
						U.UslugaComplex_Name as U_USL,
						PT.Paytype_Name as U_WO,
						OT.OperType_Name as U_TIPOP,
						OD.OperDiff_Name as U_KATSLOJ,
						IsEndoskop.YesNo_Code as U_PREND,
						IsLazer.YesNo_Code as U_PRLAS,
						IsKriogen.YesNo_Code as U_PRKRI,						
						EU.EvnUsluga_Kolvo as U_KOL
					";
				}
			break;

			case 'KvsEvnUslugaOB':
				$main_alias = "EU";
				if ( $dbf === true ) {
					$query .= "
						EU.EvnUsluga_id as U_ID,
						".(isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,")."
						PS.Person_id as P_ID,
						EPS.EvnPS_id as GSP_ID,
						ST.SurgType_Name as U_WID,
						MP.MedPersonal_TabCode as U_DOCK,
						MP.Person_Fio as U_DOC
					";
				}
			break;

			case 'KvsEvnUslugaAn':
				$main_alias = "EU";
				if ( $dbf === true ) {
					$query .= "
						EU.EvnUsluga_id as U_ID,
						".(isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,")."
						PS.Person_id as P_ID,
						EPS.EvnPS_id as GSP_ID,
						AC.AnesthesiaClass_Name as U_ANEST
					";
				}
			break;

			case 'KvsEvnUslugaOsl':
				$main_alias = "EU";
				if ( $dbf === true ) {
					$query .= "
						EU.EvnUsluga_id as U_ID,
						".(isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,")."
						PS.Person_id as P_ID,
						EPS.EvnPS_id as GSP_ID,
						rtrim(isnull(convert(varchar(10), EA.EvnAgg_setDate, 104),'')) as U_DATA,
						EA.EvnAgg_setTime as U_TIME,
						AT.AggType_Name as U_WID,
						AW.AggWhen_Name as U_KONT
					";
				}
			break;

			case 'KvsEvnDrug':
				$main_alias = "ED";
				if ( $dbf === true ) {
					$query .= "
						ED.EvnDrug_id as MED_ID,
						".(isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,")."
						PS.Person_id as P_ID,
						EPS.EvnPS_id as GSP_ID,
						rtrim(isnull(convert(varchar(10), ED.EvnDrug_setDate, 104),'')) as M_DATA,
						LS.LpuSection_Code as M_OTDLK,
						LS.LpuSection_Name as M_OTDL,
						(Mol.Person_SurName + ' ' + Mol.Person_FirName + ' ' + Mol.Person_SecName) as M_MOL,
						Drug.Drug_Code as MEDK,
						Drug.Drug_Name as MED,
						(
							'годн. ' + IsNull(convert(varchar(10), Part.DocumentUcStr_godnDate, 104),'отсут.') + 
							', цена ' + cast(ROUND(IsNull(Part.DocumentUcStr_PriceR,0), 2) as varchar(20)) + 
							', ост. ' + cast(Round(IsNull(Part.DocumentUcStr_Ost,0),4) as varchar(20)) + 
							', фин. ' + RTRIM(RTRIM(ISNULL(Part.DrugFinance_Name, 'отсут.'))) + 
							', серия ' + RTRIM(ISNULL(Part.DocumentUcStr_Ser, ''))
						) as M_PART,
						Drug.Drug_Fas as M_KOL,						
						Drug.Drug_PackName as M_EU,
						DUS.DocumentUcStr_Count as M_EU_OCT,
						ED.EvnDrug_Kolvo as M_EU_KOL,						
						Drug.DrugForm_Name as M_ED,
						DUS.DocumentUcStr_EdCount as M_ED_OCT,
						ED.EvnDrug_KolvoEd as M_ED_KOL,
						ED.EvnDrug_Price as M_CENA,
						ED.EvnDrug_Sum as M_SUM
					";
				}
			break;

			case 'KvsEvnLeave':
				$main_alias = "ELV";
				if ( $dbf === true ) {
					$query .= "
						(case
							when ESEC.LeaveType_id = 1 then ELV.EvnLeave_id
							when ESEC.LeaveType_id = 2 then EOLpu.EvnOtherLpu_id
							when ESEC.LeaveType_id = 3 then EDie.EvnDie_id
							when ESEC.LeaveType_id = 4 then EOStac.EvnOtherStac_id
							when ESEC.LeaveType_id = 5 then EOSect.EvnOtherSection_id
						end) as ISCH_ID,						
						".(isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,")."
						PS.Person_id as P_ID,
						EPS.EvnPS_id as GSP_ID,
						LType.LeaveType_Name as IG_W,
						rtrim(isnull(convert(varchar(10), (case
							when ESEC.LeaveType_id = 1 then ELV.EvnLeave_setDate
							when ESEC.LeaveType_id = 2 then EOLpu.EvnOtherLpu_setDate
							when ESEC.LeaveType_id = 3 then EDie.EvnDie_setDate
							when ESEC.LeaveType_id = 4 then EOStac.EvnOtherStac_setDate
							when ESEC.LeaveType_id = 5 then EOSect.EvnOtherSection_setDate
						end), 104),'')) as IS_DATA,
						(case
							when ESEC.LeaveType_id = 1 then ELV.EvnLeave_setTime
							when ESEC.LeaveType_id = 2 then EOLpu.EvnOtherLpu_setTime
							when ESEC.LeaveType_id = 3 then EDie.EvnDie_setTime
							when ESEC.LeaveType_id = 4 then EOStac.EvnOtherStac_setTime
							when ESEC.LeaveType_id = 5 then EOSect.EvnOtherSection_setTime
						end) as IS_TIME,
						(case
							when ESEC.LeaveType_id = 1 then ELV.EvnLeave_UKL
							when ESEC.LeaveType_id = 2 then EOLpu.EvnOtherLpu_UKL
							when ESEC.LeaveType_id = 3 then EDie.EvnDie_UKL
							when ESEC.LeaveType_id = 4 then EOStac.EvnOtherStac_UKL
							when ESEC.LeaveType_id = 5 then EOSect.EvnOtherSection_UKL
						end) as IS_URUW,
						RD.ResultDesease_Name as IS_BOL,
						LC.LeaveCause_Name as IS_PR,
						IsAmbul.YesNo_Code as IS_NAPR,
						EOLpuL.Org_Code as IS_LPUK,
						EOLpuL.Org_Name as IS_LPU,
						EOStacLUT.LpuUnitType_Name as IS_TS,
						LS.LpuSection_Code as IS_STACK,
						LS.LpuSection_Name as IS_STAC,
						MP.MedPersonal_TabCode as IS_DOCK,
						MP.Person_Fio as IS_DOC,
						DieDiag.Diag_Code as IS_DZ
					";
				}
			break;

			case 'KvsEvnStick':
				$main_alias = "EST";
				if ( $dbf === true ) {
					$query .= "
						EST.EvnStick_id as LWN_ID,
						".(isset($data['and_kvsperson']) && $data['and_kvsperson'] ? "" : "PS.PersonEvn_id as PCT_ID,")."
						PS.Person_id as P_ID,
						EPS.EvnPS_id as GSP_ID,
						SO.StickOrder_Name as PORYAD,
						ISNULL(NULLIF(LTRIM(RTRIM(ISNULL(EST.EvnStick_Ser, '') + ' ' + ISNULL(EST.EvnStick_Num, '') + ', ' + rtrim(isnull(convert(varchar(10), EST.EvnStick_begDate, 104),'')))), 'от'), '') as LWNOLD,
						EST.EvnStick_Ser as LWN_S,
						EST.EvnStick_Num as LWN_N,
						rtrim(isnull(convert(varchar(10), EST.EvnStick_begDate, 104),'')) as LWN_D,
						SC.StickCause_Name as LWN_PR,
						(ISNULL(PS.Person_SurName, '') + ' ' + ISNULL(PS.Person_FirName, '') + ' ' + ISNULL(PS.Person_SecName, '')) as ROD_FIO,
						dbo.Age(PS.Person_BirthDay, CURRENT_TIMESTAMP) as ROD_W,
						Sex.Sex_Name as ROD_POL,
						rtrim(isnull(convert(varchar(10), EST.EvnStick_sstBegDate, 104),'')) as SKL_DN,
						rtrim(isnull(convert(varchar(10), EST.EvnStick_sstEndDate, 104),'')) as SKL_DK,
						EST.EvnStick_sstNum as SKL_NOM,
						EST.EvnStick_sstPlace as SKL_LPU,
						SR.StickRegime_Name as LWN_R,
						SLT.StickLeaveType_Name as LWN_ISCH,
						EST.EvnStick_SerCont as LWN_SP,
						EST.EvnStick_NumCont as LWN_NP,
						rtrim(isnull(convert(varchar(10), EST.EvnStick_workDT, 104),'')) as LWN_DR,
						MP.MedPersonal_TabCode as LWN_DOCK,
						MP.Person_Fio as LWN_DOC,
						Lpu.Org_Code as LWN_LPUK,
						Lpu.Lpu_Name as LWN_LPU,
						D1.Diag_Code as LWN_DZ1,
						'' as LWN_DZ2
						-- D2.Diag_Code as LWN_DZ2
					";
				}
			break;
			
			case 'EvnAgg':
				$main_alias = "EvnAgg";
				if ( $dbf === true ) {
					$query .= "
						EvnAgg.EvnAgg_pid as EUS_ID,
						EvnAgg.EvnAgg_id as EAGG_ID,
						RTRIM(ISNULL(convert(varchar(10), EvnAgg.EvnAgg_setDT, 104), '')) as SETDATE,
						EvnAgg.EvnAgg_setTime as SETTIME,
						dbfaw.AggWhen_Code as AW_CODE,
						dbfat.AggType_Code as AT_CODE
					";
				}
			break;

			case 'EvnVizitPL':
				$main_alias = "EVizitPL";
				if ( $dbf === true ) {
					$query .= "
						EVizitPL.EvnVizitPL_pid as EPL_ID,
						EVizitPL.EvnVizitPL_id as EVZ_ID,
						EPL.EvnPL_NumCard as NUMCARD,
						rtrim(isnull(convert(varchar, cast(EVizitPL.EvnVizitPL_setDate as datetime),104),'')) as SETDATE,
						EVizitPL.EvnVizitPL_setTime as SETTIME,
						dbfvc.VizitClass_Code as PERVVTOR,
						dbfls.LpuSection_Code as LS_COD,
						rtrim(dbfls.LpuSection_Name) as LS_NAM,
						dbfmp.MedPersonal_TabCode as MP_COD,
						rtrim(dbfmp.Person_FIO) as MP_FIO,
						dbfpt.PayType_Code as PAY_COD,
						dbfvt.VizitType_Code as VZT_COD,
						dbfst.ServiceType_Code as SRT_COD,
						dbfpg.ProfGoal_code AS PRG_COD,
						dbfdiag.Diag_Code as DZ_COD,
						rtrim(dbfdiag.Diag_Name) as DZ_NAM,
						dbfdt.DeseaseType_Code as DST_COD
					";
				}
				else
				{
					$query .= "
						EVizitPL.EvnVizitPL_id as EvnVizitPL_id,
						EVizitPL.EvnVizitPL_pid as EvnPL_id,
						EPL.Person_id as Person_id,
						EPL.PersonEvn_id as PersonEvn_id,
						EPL.Server_id as Server_id,
						RTRIM(EPL.EvnPL_NumCard) as EvnPL_NumCard,
						RTRIM(PS.Person_SurName) as Person_Surname,
						RTRIM(PS.Person_FirName) as Person_Firname,
						RTRIM(PS.Person_SecName) as Person_Secname,
						convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
						convert(varchar(10), PS.Person_deadDT, 104) as Person_deadDT,
						RTRIM(evpldiag.Diag_Code) + '. ' + RTRIM(evpldiag.Diag_Name) as Diag_Name,
						RTRIM(evplls.LpuSection_Name) as LpuSection_Name,--отделение
						RTRIM(evplmp.Person_Fio) as MedPersonal_Fio,
						-- Услуга
						" . (in_array($data['session']['region']['nick'], array('ufa')) 
							? "EU.UslugaComplex_Code, " 
							: "NULL as UslugaComplex_Code,") . "
						convert(varchar(10), cast(EVizitPL.EvnVizitPL_setDate as datetime), 104) as EvnVizitPL_setDate,--Дата посещения,
						RTRIM(evplst.ServiceType_Name) as ServiceType_Name,--Место обслуживания,
						RTRIM(evplvt.VizitType_Name) as VizitType_Name, --Цель посещения,
						RTRIM(evplpt.PayType_Name) as PayType_Name,--Вид оплаты,
						CASE WHEN PS.Server_pid = 0 THEN 'true' ELSE 'false' END as Person_IsBDZ
					";
				}
			break;

			case 'EvnVizitPLStom':
				$main_alias = "EVPLS";
				$query .= "
					EVPLS.EvnVizitPLStom_id as EvnVizitPLStom_id,
					EVPLS.EvnVizitPLStom_pid as EvnPLStom_id,
					EPLS.Person_id as Person_id,
					EPLS.PersonEvn_id as PersonEvn_id,
					EPLS.Server_id as Server_id,
					RTRIM(EPLS.EvnPLStom_NumCard) as EvnPLStom_NumCard,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					ISNULL(RTRIM(PS.Person_SecName),'') as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					convert(varchar(10), PS.Person_DeadDT, 104) as Person_deadDT,
					ISNULL((RTRIM(evpldiag.Diag_Code) + '. ' + RTRIM(evpldiag.Diag_Name)),'') as Diag_Name,
					RTRIM(evplls.LpuSection_Name) as LpuSection_Name,
					RTRIM(MP.Person_Fio) as MedPersonal_Fio,
					convert(varchar(10), cast(EVPLS.EvnVizitPLStom_setDate as datetime), 104) as EvnVizitPLStom_setDate,
					RTRIM(evplst.ServiceType_Name) as ServiceType_Name,
					RTRIM(evplvt.VizitType_Name) as VizitType_Name,
					RTRIM(evplpt.PayType_Name) as PayType_Name,
					EVPLS.EvnVizitPLStom_Uet as EvnVizitPLStom_Uet,
					CASE WHEN PS.Server_pid = 0 THEN 'true' ELSE 'false' END as Person_IsBDZ
				";
			break;

			case 'EvnPL':
				$main_alias = "EPL";
				if ( $dbf === true )
				{
					$query .= "
						EPL.EvnPL_id as EPL_ID,
						dbfpd.PrehospDirect_Code as DIR_CODE,
						case
							when dbfpd.PrehospDirect_Code = 1 then ISNULL(NULLIF(RTRIM(dbflsd.LpuSection_Code), ''), '')
							when dbfpd.PrehospDirect_Code = 2 then ISNULL(NULLIF(RTRIM(dbfprehosplpu.Lpu_Ouz), ''), '')
							else '' end
						as PDO_CODE,
						rtrim(isnull(convert(varchar, cast(EPL.EvnPL_setDate as datetime),104),'')) as SETDATE,
						EPL.EvnPL_setTime as SETTIME,
						rtrim(isnull(convert(varchar, cast(EPL.EvnPL_disDate as datetime),104),'')) as DISDATE,
						EPL.EvnPl_disTime as DISTIME,
						EPL.EvnPL_NumCard as NUMCARD,
						dbfift.YesNo_Code as VPERV,
						ISNULL(EPL.EvnPL_Complexity, 0) as KATEGOR,
						dbflpu.Lpu_OGRN as OGRN,
						PS.Person_SurName as SURNAME,
						PS.Person_FirName as FIRNAME,
						PS.Person_SecName as SECNAME,
						rtrim(isnull(convert(varchar, cast(PS.Person_BirthDay as datetime),104),'')) as BIRTHDAY,
						dbfsex.Sex_Code as POL_COD,
						dbfss.SocStatus_code as SOC_COD,
						ISNULL(dbfkls.Kladr_Code, dbfkla.Kladr_Code) as KOD_TER,
						PS.person_Snils as SNILS,
						case when EPL.EvnPL_IsFinish = 2 then '1' else '0' end as FINISH_ID,
						dbfrc.ResultClass_Code as RSC_COD,
						dbfdiag.Diag_Code as DZ_COD,
						dbfdiag.Diag_Name as DZ_NAM,
						dbfdt.DeseaseType_code as DST_COD,
						cast(EPL.EvnPL_UKL as float) as UKL,
						ISNULL(dbfdc.DirectClass_Code, 0) as CODE_NAP,
						case
							when dbfdc.DirectClass_Code = 1 then ISNULL(NULLIF(RTRIM(dbflsdir.LpuSection_Code), ''), '')
							when dbfdc.DirectClass_Code = 2 then ISNULL(NULLIF(RTRIM(dbflpudir.Lpu_Ouz), ''), '')
							else '' end
						as KUDA_NAP,
						dbfinv.PersonChild_IsInvalid_Code as INVALID,
						dbfinv.PermRegion_Code as REG_PERM,
						CASE WHEN PS.Server_pid = 0 THEN 'Да' ELSE 'Нет' END as BDZ
					";
				}
				else
				{
					$query .= "
						EPL.EvnPL_id as EvnPL_id,
						EPL.Person_id as Person_id,
						EPL.PersonEvn_id as PersonEvn_id,
						EPL.Server_id as Server_id,
						RTRIM(EPL.EvnPL_NumCard) as EvnPL_NumCard,
						RTRIM(PS.Person_SurName) as Person_Surname,
						RTRIM(PS.Person_FirName) as Person_Firname,
						ISNULL(RTRIM(PS.Person_SecName),'') as Person_Secname,
						convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
						convert(varchar(10), PS.Person_deadDT, 104) as Person_deadDT,
						EPL.EvnPL_VizitCount as EvnPL_VizitCount,
						IsFinish.YesNo_Name as EvnPL_IsFinish,
						RTRIM(EVPLD.Diag_Code) + '. ' + RTRIM(EVPLD.Diag_Name) as Diag_Name,
						RTRIM(MP.Person_Fio) as MedPersonal_Fio,
						convert(varchar(10), EPL.EvnPL_setDate, 104) as EvnPL_setDate,
						convert(varchar(10), EPL.EvnPL_disDate, 104) as EvnPL_disDate,
						CASE WHEN PS.Server_pid = 0 THEN 'true' ELSE 'false' END as Person_IsBDZ
					";
				}
			break;
			
			case 'EvnPLStom':
				$main_alias = "EPLS";
				$query .= "
					EPLS.EvnPLStom_id as EvnPLStom_id,
					EPLS.Person_id as Person_id,
					EPLS.PersonEvn_id as PersonEvn_id,
					EPLS.Server_id as Server_id,
					RTRIM(EPLS.EvnPLStom_NumCard) as EvnPLStom_NumCard,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					convert(varchar(10), PS.Person_DeadDT, 104) as Person_deadDT,
					EPLS.EvnPLStom_VizitCount as EvnPLStom_VizitCount,
					IsFinish.YesNo_Name as EvnPLStom_IsFinish,
					RTRIM(MP.Person_Fio) as MedPersonal_Fio,
					convert(varchar(10), EPLS.EvnPLStom_setDate, 104) as EvnPLStom_setDate,
					convert(varchar(10), EPLS.EvnPLStom_disDate, 104) as EvnPLStom_disDate,
					CASE WHEN PS.Server_pid = 0 THEN 'true' ELSE 'false' END as Person_IsBDZ
				";
			break;

			case 'EvnPS':
				$main_alias = "EPS";
				if ( $dbf === true )
				{
					$query .= "
						EPS.EvnPS_id as EPS_COD,
						EPS.EvnPS_IsCont as IS_CONT,						
						RTRIM(ISNULL(convert(varchar(10), EPS.EvnPS_setDate, 104), '')) as SETDATE,
						EPS.EvnPS_setTime as SETTIME,
						RTRIM(ISNULL(convert(varchar(10), EPS.EvnPS_disDate, 104), '')) as DISDATE,
						EPS.EvnPS_disTime as DISTIME,						
						EPS.Person_id as PERS_COD,
						dbflpu.Lpu_OGRN as LPU_OGRN,
						RTRIM(EPS.EvnPS_NumCard) as NUMBER,
						dbfpa.PrehospArrive_Code as PRHARR_COD,
						dbfpd.PrehospDirect_Code as PRHDIR_COD,
						dbfpt.PrehospToxic_Code as PRHTOX_COD,
						dbfpayt.PayType_Code as PAY_COD,
						dbfprtr.PrehospTrauma_Code as PRHTRV_COD,
						dbfprtype.PrehospType_Code as PRHTYP_COD,
						dbfdorg.Org_OGRN as ORG_OGRN,
						dbflsd.LpuSection_Code as LS_COD,
						EPS.EvnDirection_Num as NUMORD,
						RTRIM(ISNULL(convert(varchar(10), EPS.EvnDirection_setDT, 104), '')) as DATEORD,
						EPS.EvnPS_CodeConv as CODCON,
						EPS.EvnPS_NumConv as NUMCON,
						EPS.EvnPS_TimeDesease as DESSTIME,
						EPS.EvnPS_HospCount as HOSPCOUNT,
						dbfoorg.Org_OGRN as OLPU_OGRN,
						EPS.EvnPS_IsUnlaw as ISUNLAW,
						EPS.EvnPS_IsUnport as ISUNPORT,
						dbfmp.MedPersonal_TabCode as MP_COD
					";
				}
				else
				{
					$query .= "
						EPS.EvnPS_id as EvnPS_id,
						EPS.Person_id as Person_id,
						EPS.PersonEvn_id as PersonEvn_id,
						EPS.Server_id as Server_id,
						RTRIM(EPS.EvnPS_NumCard) as EvnPS_NumCard,
						RTRIM(PS.Person_SurName) as Person_Surname,
						RTRIM(PS.Person_FirName) as Person_Firname,
						RTRIM(PS.Person_SecName) as Person_Secname,
						convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
						convert(varchar(10), PS.Person_DeadDT, 104) as Person_deadDT,
						convert(varchar(10), EPS.EvnPS_setDate, 104) as EvnPS_setDate,
						CASE WHEN EPS.PrehospWaifRefuseCause_id > 0 
							THEN convert(varchar(10), EPS.EvnPS_setDate, 104) 
							ELSE convert(varchar(10), EPS.EvnPS_disDate, 104) 
						END as EvnPS_disDate,
						ISNULL(LStmp.LpuSection_Name, '') as LpuSection_Name,
						ISNULL(Dtmp.Diag_FullName, DP.Diag_FullName) as Diag_Name,
						-- поскольку в одном КВС не может быть движений по круглосуточным и дневным стационарам вместе (поскольку это делается через перевод и создание новой карты)
						-- то подсчет количества койкодней реализуем так (с) Night, 2011-06-22 
						case when LpuUnitType.LpuUnitType_SysNick = 'stac' 
							then datediff(day, EPS.EvnPS_setDate, EPS.EvnPS_disDate) + abs(sign(datediff(day, EPS.EvnPS_setDate, EPS.EvnPS_disDate)) - 1) -- круглосуточные 
							else (datediff(day, EPS.EvnPS_setDate, EPS.EvnPS_disDate) + 1) -- дневные 
						end as EvnPS_KoikoDni, 
						CASE WHEN PS.Server_pid = 0 THEN 'true' ELSE 'false' END as Person_IsBDZ,
						dbfpayt.PayType_Name as PayType_Name, --Вид оплаты
						CASE 
							WHEN LT.LeaveType_Name is not null THEN LT.LeaveType_Name
							WHEN EPS.LpuSection_id is not null THEN LS.LpuSection_Name
							WHEN EPS.PrehospWaifRefuseCause_id > 0 THEN pwrc.PrehospWaifRefuseCause_Name
							ELSE ''
						END as LeaveType_Name
					";
				}
			break;

			case 'EvnRecept':
				$main_alias = "ER";
				$query .= "
					TOP 10000
					ER.EvnRecept_id,
					ER.Person_id,
					ER.PersonEvn_id,
					ER.Server_id,
					ER.Drug_id,
					ER.Drug_rlsid,
					(select top 1 ReceptDelayType_Name from ReceptDelayType ER_RDT where ER_RDT.ReceptDelayType_id = ER.ReceptDelayType_id) as ReceptDelayType_Name,
					(select top 1 Org_Name from v_OrgFarmacy ER_OF where ER_OF.OrgFarmacy_id = ER.OrgFarmacy_oid) as OrgFarmacy_oName,
					(
						case when
							ER.ReceptDelayType_id  > 0
						then
							(select top 1 ReceptDelayType_Name from ReceptDelayType ER_RDT where ER_RDT.ReceptDelayType_id = ER.ReceptDelayType_id) + ' ' + (select top 1 Org_Name from v_OrgFarmacy ER_OF where ER_OF.OrgFarmacy_id = ER.OrgFarmacy_oid)
						else
							''
						end
					) as Delay_info,
					RTRIM(PS.Person_Surname) as Person_Surname,
					RTRIM(PS.Person_Firname) as Person_Firname,
					RTRIM(PS.Person_Secname) as Person_Secname,
					convert(varchar(10), PS.Person_BirthDay, 104) as Person_Birthday,
					convert(varchar(10), PS.Person_DeadDT, 104) as Person_deadDT,
					convert(varchar(10), ER.EvnRecept_setDate,104) as EvnRecept_setDate,
					RTRIM(ER.EvnRecept_Ser) as EvnRecept_Ser,
					RTRIM(ER.EvnRecept_Num) as EvnRecept_Num,
					ROUND(ER.EvnRecept_Kolvo, 3) as EvnRecept_Kolvo,
					RTRIM(ERMP.Person_Fio) as MedPersonal_Fio,
					RTRIM(COALESCE(ERDrugRls.Drug_Name, ERDrug.Drug_Name, ER.EvnRecept_ExtempContents)) as Drug_Name,
					CASE WHEN PS.Server_pid = 0 THEN 'true' ELSE 'false' END as Person_IsBDZ,
					CASE WHEN ER.EvnRecept_IsSigned = 2 THEN 'true' ELSE 'false' END as EvnRecept_IsSigned,
					CASE WHEN ER.EvnRecept_IsPrinted = 2 THEN 'true' ELSE 'false' END as EvnRecept_IsPrinted,
					ER.ReceptRemoveCauseType_id,
					isnull(wdcit.MorbusType_id, 1) as MorbusType_id
				";
			break;

			case 'EvnUslugaPar':
				$main_alias = "EUP";
				$query .= "
					EUP.EvnUslugaPar_id,
					EUP.Person_id,
					EUP.PersonEvn_id,
					EUP.Server_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					convert(varchar(10), PS.Person_DeadDT, 104) as Person_deadDT,
					convert(varchar(10), EUP.EvnUslugaPar_setDate, 104) as EvnUslugaPar_setDate,
					RTRIM(LS.LpuSection_Name) as LpuSection_Name,
					RTRIM(MP.Person_Fio) as MedPersonal_Fio,
					CASE
					WHEN EUP.UslugaComplex_id IS NULL THEN RTRIM(Usluga.Usluga_Code)
					ELSE RTRIM(UslugaComplex.UslugaComplex_Code)
					END as Usluga_Code,
					CASE
					WHEN EUP.UslugaComplex_id IS NULL THEN RTRIM(Usluga.Usluga_Name)
					ELSE RTRIM(UslugaComplex.UslugaComplex_Name)
					END as Usluga_Name,
					RTRIM(PT.PayType_Name) as PayType_Name,
					isnull(EUP.EvnUslugaPar_Kolvo, '') as EvnUslugaPar_Kolvo,
					isnull(PostMed.PostMed_Name, '') as PostMed_Name,
					LSD = (			-- это отделение, направившее пациента
						select
							LpuSection_Name
						from
							v_LpuSection (nolock)
						where
							LpuSection_id = EUP.LpuSection_did
					)
				";
			break;

			case 'WorkPlacePolkaReg':
			case 'PersonCard':
				$main_alias = "PC";
				$query .= "
					PC.PersonCard_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					CASE WHEN PS.Person_IsDead = 2 THEN 'true' ELSE 'false' END as Person_IsDead,
					PC.PersonCard_Code as PersonCard_Code,
					[dbo].[getPersonPhones](PS.Person_id, '<br />') as Person_Phone,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), cast(PS.Person_BirthDay as datetime), 104) as Person_Birthday,
					convert(varchar(10), cast(PS.Person_DeadDT as datetime), 104) as Person_deadDT,
					ISNULL(AttachLpu.Lpu_Nick, 'Не прикреплен') as AttachLpu_Name,
					convert(varchar(10), cast(PC.PersonCard_begDate as datetime), 104) as PersonCard_begDate,
					convert(varchar(10), cast(PC.PersonCard_endDate as datetime), 104) as PersonCard_endDate,
					PC.LpuAttachType_Name,
					PC.LpuRegionType_Name,
					LR.LpuRegion_Name,
					CASE WHEN ISNULL(PC.PersonCard_IsAttachCondit, 1) = 2 then 'true' else 'false' end as PersonCard_IsAttachCondit,
					CASE WHEN PC.PersonCardAttach_id IS NULL then 'false' else 'true' end as PersonCardAttach,
					CASE WHEN PS.Person_IsRefuse = 1 THEN 'true' ELSE 'false' END as Person_IsRefuse,
					CASE WHEN PRef.PersonRefuse_IsRefuse = 2 THEN 'true' ELSE 'false' END as Person_NextYearRefuse,
					CASE WHEN PS.Person_IsFedLgot = 1 THEN 'true' ELSE 'false' END as Person_IsFedLgot,
					CASE WHEN PS.Person_IsRegLgot = 1 THEN 'true' ELSE 'false' END as Person_IsRegLgot,
					--CASE WHEN (PS.Person_Is7Noz = 1 and PD.Sickness_id IN (1,3,4,5,6,7,8)) THEN 'true' ELSE 'false' END as Person_Is7Noz,

					CASE WHEN disp.OwnLpu = 1 THEN 'true' ELSE
						CASE WHEN disp.OwnLpu is not null THEN 'gray'
										  ELSE 'false'
						END
					END as Person_Is7Noz,

					CASE WHEN PS.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
					isnull(paddr.Address_Address, '') as Person_PAddress,
					isnull(uaddr.Address_Address, '') as Person_UAddress
				";
			break;

			/*@todo: Журнал движения РПН. Просмотр Деталей. от select до from
			 *
			 */
			case 'PersonCardStateDetail':
				$main_alias = "PCSD";
				$query .= "
					PCSD.PersonCard_Code,
					PCSD.PersonCard_id,
					PCSD.Person_id,
					PCSD.Server_id,
					rtrim(pcc.Person_SurName) as Person_Surname,
					rtrim(pcc.Person_FirName) as Person_Firname,
					rtrim(pcc.Person_SecName) as Person_Secname,
					convert(varchar,cast(pcc.Person_BirthDay as datetime),104) as Person_BirthDay,
					convert(varchar,cast(PCSD.PersonCard_begDate as datetime),104) as PersonCard_begDate,
					convert(varchar,cast(PCSD.PersonCard_endDate as datetime),104) as PersonCard_endDate,
					lrt.LpuRegionType_Name,
					lr.LpuRegion_Name,
					isnull(ccc.CardCloseCause_Name, '') as CardCloseCause_Name,
					case when isnull(PCSD.PersonCard_IsAttachCondit, 1) = 1 then 'false' else 'true' end as PersonCard_IsAttachCondit,
					isnull(attcard.LpuRegion_Name, '') as ActiveLpuRegion_Name,
					isnull(rtrim(attcard.Lpu_Nick), '') as ActiveLpu_Nick,
					isnull(rtrim(Address.Address_Address), '') as PAddress_Address,
					isnull(rtrim(Address1.Address_Address), '') as UAddress_Address,
					isnull(rtrim(dmsOrgSmo.OrgSMO_Nick), '') as dmsOrgSmo_Nick,
					isnull(rtrim(omsOrgSmo.OrgSMO_Nick), '') as omsOrgSmo_Nick,
					case
					    when ps.Server_pid = 0 and Pols.Polis_id is not null then 'true'
					    else 'false'
					end as isBDZ
				";
			break;
			
			case 'PersonDisp':
				$main_alias = "PD";
				$query .= "
					PD.PersonDisp_id,
					PD.Person_id,
					PD.Server_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), cast(PS.Person_BirthDay as datetime), 104) as Person_Birthday,
					convert(varchar(10), cast(PD.PersonDisp_begDate as datetime), 104) as PersonDisp_begDate,
					convert(varchar(10), cast(PD.PersonDisp_endDate as datetime), 104) as PersonDisp_endDate,
					convert(varchar(10), cast(PD.PersonDisp_NextDate as datetime), 104) as PersonDisp_NextDate,
					dg1.Diag_Code,
					mp1.Person_Fio as MedPersonal_FIO,
					lpus1.LpuSection_Name as LpuSection_Name,
					scks.Sickness_Name,
					rtrim(lpu1.Lpu_Nick) as Lpu_Nick,
					CASE WHEN PD.Lpu_id = :Lpu_id THEN 2 ELSE 1 END as IsOurLpu,
					CASE WHEN noz.isnoz = 1 
					THEN 'true' ELSE
						 CASE WHEN noz.isnoz is not null 
							THEN 'gray'
							ELSE 'false'
						 END
					END as Is7Noz
				";
			break;

			case 'PersonPrivilege':
				$main_alias = "PP";
				$query .= "
					PP.Lpu_id,
					PS.Server_id,
					PP.PersonEvn_id,
					PS.Person_id,
					PP.PersonPrivilege_id,
					PT.PrivilegeType_id,
					PT.ReceptFinance_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_BirthDay, 104) as Person_Birthday,
					convert(varchar(10), PS.Person_DeadDT, 104) as Person_deadDT,
					RTRIM(PT.PrivilegeType_Code) as PrivilegeType_Code,
					RTRIM(PT.PrivilegeType_Name) as PrivilegeType_Name,
					convert(varchar(10), PP.PersonPrivilege_begDate, 104) as Privilege_begDate,
					convert(varchar(10), PP.PersonPrivilege_endDate, 104) as Privilege_endDate,
					CASE WHEN PS.Person_IsRefuse = 1 THEN 'true' ELSE 'false' END as Person_IsRefuse,
					CASE WHEN PS.Person_IsFedLgot = 1 THEN 'true' ELSE 'false' END as Person_IsFedLgot,
					CASE WHEN PS.Person_IsRegLgot = 1 THEN 'true' ELSE 'false' END as Person_IsRegLgot,
					CASE WHEN PS.Person_Is7Noz = 1 THEN 'true' ELSE 'false' END as Person_Is7Noz,
					CASE WHEN PS.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ
				";
			break;

			// Регистр ветеранчегов
			case 'PersonPrivilegeWOW':
				$main_alias = "PPW";
				$query .= "
					PPW.PersonPrivilegeWOW_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					rtrim(PS.Person_SurName) as Person_Surname,
					rtrim(PS.Person_FirName) as Person_Firname,
					rtrim(PS.Person_SecName) as Person_Secname,
					UAdd.Address_Address as ua_name,
					PAdd.Address_Address as pa_name,
					Sex.Sex_Name,
					PS.Polis_Ser,
					PS.Polis_Num,
					PTW.PrivilegeTypeWow_id,
					PTW.PrivilegeTypeWOW_Name,
					convert(varchar,cast(PS.Person_BirthDay as datetime),104) as Person_Birthday
					--isnull(rtrim(otherddlpu.Lpu_Nick), '') as OnDispInOtherLpu,
					--epldd1.EvnPLDispDop_id,
					--CASE WHEN epldd1.EvnPLDispDop_id is null THEN 'false' ELSE 'true' END as ExistsDDPL
				";
			break;
		
			case 'EvnPLWOW':
				$main_alias = "EPW";
				$query .= "
					EPW.EvnPLWOW_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					rtrim(PS.Person_SurName) as Person_Surname,
					rtrim(PS.Person_FirName) as Person_Firname,
					rtrim(PS.Person_SecName) as Person_Secname,
					PS.Polis_Ser,
					PS.Polis_Num,
					PTW.PrivilegeTypeWow_id,
					PTW.PrivilegeTypeWOW_Name,
					convert(varchar,cast(PS.Person_BirthDay as datetime),104) as Person_Birthday,
					convert(varchar(10), EPW.EvnPLWOW_setDate, 104) as EvnPLWOW_setDate,
					convert(varchar(10), EPW.EvnPLWOW_disDate, 104) as EvnPLWOW_disDate,
					EPW.EvnPLWOW_VizitCount as EvnPLWOW_VizitCount, 
					YesNo_Name as EvnPLWOW_IsFinish
				";
			break;
			
			case 'EvnDtpWound':
				$main_alias = "EDW";
				$query .= "
					EDW.EvnDtpWound_id as EvnDtpWound_id,
					EDW.Person_id as Person_id,
					EDW.PersonEvn_id as PersonEvn_id,
					EDW.Server_id as Server_id,
					convert(varchar(10), EDW.EvnDtpWound_setDate, 104) as EvnDtpWound_setDate,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
				";
			break;
			
			case 'OrphanRegistry':
				$main_alias = 'PR';
				$query .= '
					PR.PersonRegister_id,
					PR.Diag_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					PR.MorbusType_id,
					EN.EvnNotifyOrphan_id as EvnNotifyBase_id,
					M.Morbus_id,
					MO.MorbusOrphan_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
				';
			break;

			case 'CrazyRegistry':
				$main_alias = 'PR';
				$query .= '
					PR.PersonRegister_id,
					PR.Diag_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					PR.MorbusType_id,
					EN.EvnNotifyCrazy_id as EvnNotifyCrazy_id,
					isnull(EN.Morbus_id,PR.Morbus_id) as Morbus_id,
					MO.MorbusCrazy_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
				';
				break;
			case 'NarkoRegistry':
				$main_alias = 'PR';
				$query .= '
					PR.PersonRegister_id,
					PR.Diag_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					PR.MorbusType_id,
					EN.EvnNotifyCrazy_id as EvnNotifyCrazy_id,
					isnull(EN.Morbus_id,PR.Morbus_id) as Morbus_id,
					MO.MorbusCrazy_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
				';
				break;
			case 'TubRegistry':
				$main_alias = 'PR';
				$query .= '
					PR.PersonRegister_id,
					PR.Diag_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					PR.MorbusType_id,
					EN.EvnNotifyTub_id as EvnNotifyTub_id,
					isnull(EN.Morbus_id,PR.Morbus_id) as Morbus_id,
					MO.MorbusTub_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
				';
				break;
			case 'VznRegistry':
			case 'DiabetesRegistry':
			case 'LargeFamilyRegistry':
				$main_alias = 'PR';
				$query .= '
					PR.PersonRegister_id,
					PR.Diag_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					PR.MorbusType_id,
					Drug.Request as DrugRequest,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_id,
					Lpu.Lpu_Nick,
					Diag.diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
				';
				break;
			case 'HIVRegistry':
				$main_alias = 'PR';
				$query .= '
					PR.PersonRegister_id,
					PR.Diag_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					PR.MorbusType_id,
					EN.EvnNotifyHIV_id as EvnNotifyHIV_id,
					MH.MorbusHIV_NumImmun,
					MH.Morbus_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
				';
				break;
			case 'VenerRegistry':
				$main_alias = 'PR';
				$query .= '
					PR.PersonRegister_id,
					PR.Diag_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					PR.MorbusType_id,
					EN.EvnNotifyVener_id as EvnNotifyVener_id,
					isnull(EN.Morbus_id,PR.Morbus_id) as Morbus_id,
					MO.MorbusVener_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
				';
				break;
			case 'HepatitisRegistry':
				$main_alias = 'PR';
				$query .= '
					PR.PersonRegister_id,
					PR.Diag_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					PR.MorbusType_id,
					ENH.EvnNotifyHepatitis_id as EvnNotifyBase_id,
					isnull(ENH.Morbus_id,PR.Morbus_id) as Morbus_id,
					MH.MorbusHepatitis_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					HDT.HepatitisDiagType_Name,
					HQT.HepatitisQueueType_Name,
					MHQ.MorbusHepatitisQueue_Num,
					IsCure.YesNo_Name as MorbusHepatitisQueue_IsCure,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
				';
			break;
		
			case 'OnkoRegistry':
				$main_alias = 'PR';
				$query .= "
					PR.PersonRegister_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					PR.MorbusType_id,
					PR.EvnNotifyBase_id,
					MO.MorbusOnko_id,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.Diag_id,
					Diag.diag_FullName as Diag_Name,
					PROUT.PersonRegisterOutCause_id,
					PROUT.PersonRegisterOutCause_Name,
					CASE WHEN OnkoDiag.OnkoDiag_Name is null THEN '' ELSE OnkoDiag.OnkoDiag_Code + '. ' + OnkoDiag.OnkoDiag_Name END as OnkoDiag_Name,
					CASE WHEN (MO.MorbusOnko_IsMainTumor is null OR MO.MorbusOnko_IsMainTumor < 2)  THEN 'Нет' ELSE 'Да' END as MorbusOnko_IsMainTumor,
					TumorStage.TumorStage_Name as TumorStage_Name,
					convert(varchar(10), MO.MorbusOnko_setDiagDT, 104) as MorbusOnko_setDiagDT,
					convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
					convert(varchar(10), PR.PersonRegister_disDate, 104) as PersonRegister_disDate
				";
			break;
			
			case 'EvnInfectNotify':
				$main_alias = "EIN";
				$query .= "
					EIN.EvnInfectNotify_id,
					convert(varchar(10), EIN.EvnInfectNotify_insDT, 104) as EvnInfectNotify_insDT,
					PS.Person_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					ISNULL ( Diag.diag_FullName, Diag1.diag_FullName ) as Diag_Name
				";
			break;
			
			case 'EvnNotifyHepatitis':
				$main_alias = "ENH";
				$query .= "
					ENH.EvnNotifyHepatitis_id,
					ENH.EvnNotifyHepatitis_pid,
					ENH.Morbus_id,
					ENH.MorbusType_id,
					ENH.MedPersonal_id,
					ENH.pmUser_updId,
					convert(varchar(10), ENH.EvnNotifyHepatitis_setDT, 104) as EvnNotifyHepatitis_setDT,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.Diag_id as Diag_id,
					Diag.diag_FullName as Diag_Name,
					convert(varchar(10), isnull(ENH.EvnNotifyHepatitis_niDate,PR.PersonRegister_setDate), 104) as PersonRegister_setDate
				";
			break;
		
			case 'EvnOnkoNotify':
				$main_alias = "EON";
				$query .= "
					EON.EvnOnkoNotify_id,
					EONN.EvnOnkoNotifyNeglected_id,
					EON.EvnOnkoNotify_pid,
					EON.Morbus_id,
					EON.MorbusType_id,
					EON.MedPersonal_id,
					EON.pmUser_updId,
					convert(varchar(10), EON.EvnOnkoNotify_setDT, 104) as EvnOnkoNotify_setDT,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					EON.Lpu_sid,
					Lpu1.Lpu_Nick as Lpu_sid_Nick,
					Diag.Diag_id as Diag_id,
					Diag.diag_FullName as Diag_Name,
					OnkoDiag.OnkoDiag_Name as OnkoDiag_Name,
					TumorStage.TumorStage_Name as TumorStage_Name,
					CASE WHEN EONN.EvnOnkoNotifyNeglected_id IS NOT NULL THEN 'true' ELSE 'false' END as isNeglected,
					convert(varchar(10), isnull(EON.EvnOnkoNotify_niDate,PR.PersonRegister_setDate), 104) as PersonRegister_setDate
				";
			break;
		
			case 'EvnNotifyOrphan':
				$main_alias = 'ENO';
				$query .= '
					ENO.EvnNotifyOrphan_id,
					ENO.EvnNotifyOrphan_pid,
					convert(varchar(10), ENO.EvnNotifyOrphan_setDT, 104) as EvnNotifyOrphan_setDT,
					ENO.EvnNotifyType_Name,
					ENO.EvnNotifyType_SysNick,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					ENO.Morbus_id,
					ENO.MorbusType_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.Diag_id as Diag_id,
					Diag.diag_FullName as Diag_Name,
					convert(varchar(10), isnull(ENO.EvnNotifyOrphan_niDate,PR.PersonRegister_setDate), 104) as PersonRegister_setDate,
					ENO.MedPersonal_id,
					MP.Person_Fio as MedPersonal_Name,
					ENO.pmUser_updId
				';
			break;

			case 'EvnNotifyCrazy': // Психиатрия
				$main_alias = 'ENC';
				$query .= '
					ENC.EvnNotifyCrazy_id,
					ENC.EvnNotifyCrazy_pid,
					convert(varchar(10), ENC.EvnNotifyCrazy_setDT, 104) as EvnNotifyCrazy_setDT,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					ENC.Morbus_id,
					ENC.MorbusType_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.Diag_id as Diag_id,
					Diag.diag_FullName as Diag_Name,
					convert(varchar(10), isnull(ENC.EvnNotifyCrazy_niDate,PR.PersonRegister_setDate), 104) as PersonRegister_setDate,
					ENC.MedPersonal_id,
					ENC.pmUser_updId
				';
				break;
			case 'EvnNotifyNarko': // Психиатрия
				$main_alias = 'ENC';
				$query .= '
					ENC.EvnNotifyCrazy_id,
					ENC.EvnNotifyCrazy_pid,
					convert(varchar(10), ENC.EvnNotifyCrazy_setDT, 104) as EvnNotifyCrazy_setDT,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					ENC.Morbus_id,
					ENC.MorbusType_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.Diag_id as Diag_id,
					Diag.diag_FullName as Diag_Name,
					convert(varchar(10), isnull(ENC.EvnNotifyCrazy_niDate,PR.PersonRegister_setDate), 104) as PersonRegister_setDate,
					ENC.MedPersonal_id,
					ENC.pmUser_updId
				';
				break;
			case 'EvnNotifyTub': // Туберкулез
				$main_alias = 'ENC';
				$query .= '
					ENC.EvnNotifyTub_id,
					ENC.EvnNotifyTub_pid,
					convert(varchar(10), ENC.EvnNotifyTub_setDT, 104) as EvnNotifyTub_setDT,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					ENC.Morbus_id,
					ENC.MorbusType_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.Diag_id as Diag_id,
					Diag.diag_FullName as Diag_Name,
					convert(varchar(10), isnull(ENC.EvnNotifyTub_niDate,PR.PersonRegister_setDate), 104) as PersonRegister_setDate,
					ENC.MedPersonal_id,
					ENC.pmUser_updId
				';
				break;
			case 'EvnNotifyHIV': // ВИЧ
				$main_alias = 'ENB';
				$query .= '
					ENB.EvnNotifyBase_id,
					ENB.EvnNotifyBase_pid,
					convert(varchar(10), ENB.EvnNotifyBase_setDT, 104) as EvnNotifyBase_setDT,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					ENB.Morbus_id,
					ENB.MorbusType_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.Diag_id as Diag_id,
					Diag.diag_FullName as Diag_Name,
					convert(varchar(10), isnull(ENB.EvnNotifyBase_niDate,PR.PersonRegister_setDate), 104) as PersonRegister_setDate,
					ENB.MedPersonal_id,
					EvnClass.EvnClass_Name,
					EvnClass.EvnClass_SysNick,
					ENB.pmUser_updId
				';
				break;
			case 'EvnNotifyVener': // Туберкулез
				$main_alias = 'ENC';
				$query .= '
					ENC.EvnNotifyVener_id,
					ENC.EvnNotifyVener_pid,
					convert(varchar(10), ENC.EvnNotifyVener_setDT, 104) as EvnNotifyVener_setDT,
					PS.Person_id,
					PS.Server_id,
					PS.PersonEvn_id,
					ENC.Morbus_id,
					ENC.MorbusType_id,
					RTRIM(PS.Person_SurName) as Person_Surname,
					RTRIM(PS.Person_FirName) as Person_Firname,
					RTRIM(PS.Person_SecName) as Person_Secname,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					Lpu.Lpu_Nick as Lpu_Nick,
					Diag.Diag_id as Diag_id,
					Diag.diag_FullName as Diag_Name,
					convert(varchar(10), isnull(ENC.EvnNotifyVener_niDate,PR.PersonRegister_setDate), 104) as PersonRegister_setDate,
					ENC.MedPersonal_id,
					ENC.pmUser_updId
				';
				break;
			default:
				return array('success' => false, 'Error_Msg' => 'Необходимо задать цель поиска.');
			break;
		}
		
		$query .= "
			-- end select
			from
				-- from
		";

		switch ( $data['SearchFormType'] ) {
			case 'PersonPrivilegeWOW':
				$query .= "
					v_PersonState PS with (nolock)
				";
			break;

			case 'EvnAgg':
			case 'EvnPL':
			case 'EvnPLDispDop':
			case 'EvnPLDispDop13':
			case 'EvnPLDispProf':
			case 'EvnPLDispTeen14':
			case 'EvnPLDispDopStream':
			case 'EvnPLDispTeen14Stream':
			case 'EvnPLDispOrp':
			case 'EvnPLDispOrpOld':
			case 'EvnPLDispOrpSec':
			case 'EvnPLDispTeenInspectionPeriod':
			case 'EvnPLDispTeenInspectionProf':
			case 'EvnPLDispTeenInspectionPred':
			case 'EvnPLDispOrpStream':
			case 'EvnPLStom':
			case 'EvnVizitPLStom':
			case 'EvnPS':
			case 'EvnSection':
			case 'EvnDiag':
			case 'EvnLeave':
			case 'EvnStick':
			case 'KvsPerson':
			case 'KvsPersonCard':
			case 'KvsEvnDiag':
			case 'KvsEvnPS':
			case 'KvsEvnSection':
			case 'KvsNarrowBed':
			case 'KvsEvnUsluga':
			case 'KvsEvnUslugaOB':
			case 'KvsEvnUslugaAn':
			case 'KvsEvnUslugaOsl':
			case 'KvsEvnDrug':
			case 'KvsEvnLeave':
			case 'KvsEvnStick':
			case 'EvnRecept':
			case 'EvnPLWOW':
			case 'EvnDtpWound':
			case 'EvnUsluga':
			case 'EvnUslugaPar':
			case 'EvnVizitPL':
			case 'EvnInfectNotify':
			case 'EvnNotifyHepatitis':
			case 'EvnOnkoNotify':
			case 'EvnNotifyOrphan':
			case 'EvnNotifyCrazy':
			case 'EvnNotifyNarko':
			case 'EvnNotifyTub':
			case 'EvnNotifyHIV':
			case 'EvnNotifyVener':
			case 'HepatitisRegistry':
			case 'OnkoRegistry':
			case 'OrphanRegistry':
			case 'CrazyRegistry':
			case 'NarkoRegistry':
			case 'TubRegistry':
			case 'VznRegistry':
			case 'DiabetesRegistry':
			case 'LargeFamilyRegistry':
			case 'HIVRegistry':
			case 'VenerRegistry':
				if ( in_array($data['PersonPeriodicType_id'], array(2)) ) {
					$query .= "
						v_Person_all PS with (nolock)
					";
				}
				else {
					if ( !isset($data['Refuse_id']) ) { // данные по отказу есть только в v_PersonState_all
						$query .= "
							v_PersonState PS with (nolock)
						";
					} else {
						$query .= "
							v_PersonState_all PS with (nolock)
						";
					}
				}
			break;
			
			case 'PersonDopDisp':
				if ( isset($data['Refuse_id']) )
					$query .= "
							v_PersonState_All PS with (nolock)
					";
				else
					$query .= "
							v_PersonState PS with (nolock)
					";
			break;

			case 'WorkPlacePolkaReg':			
			case 'PersonCard':			
			case 'PersonDispOrp':
			case 'PersonDispOrpPeriod':
			case 'PersonDispOrpPred':
			case 'PersonDispOrpProf':
			case 'PersonDispOrpOld':
			case 'PersonDisp':
			case 'PersonCardStateDetail':
			case 'PersonPrivilege':
					$query .= "
						v_PersonState_All PS with (nolock)
					";
			break;
		}

		if ( isset($data['soc_card_id']) && strlen($data['soc_card_id']) >= 25  )
		{	
			$filter .= " and LEFT(ps.Person_SocCardNum, 19) = :SocCardNum ";
			$queryParams['SocCardNum'] = substr($data['soc_card_id'], 0, 19);
		}
		
		switch ( $data['SearchFormType'] ) {
			case 'CmpCallCard':
				$query .= "
					v_CmpCallCard CCC with (nolock)
					left join v_CmpReason CR with (nolock) on CR.CmpReason_id = CCC.CmpReason_id
					left join v_CmpResult CRES with (nolock) on CRES.CmpResult_id = CCC.CmpResult_id
					left join CmpLpu CL with (nolock) on CL.CmpLpu_id = CCC.CmpLpu_id
					left join v_Lpu L with (nolock) on L.Lpu_id = CL.Lpu_id
					left join CmpDiag CD with (nolock) on CD.CmpDiag_id = CCC.CmpDiag_oid
					left join Diag D with (nolock) on D.Diag_id = CCC.Diag_sid
					left join Diag UD with (nolock) on UD.Diag_id = CCC.Diag_uid
					left join v_PersonState PS with (nolock) on CCC.Person_id = PS.Person_id
					left join [Address] PAddr with (nolock) on PAddr.Address_id = PS.PAddress_id
					left join v_CmpCloseCard CClC with (nolock) on CCC.CmpCallCard_id = CClC.CmpCallCard_id
				";
				
				// Фильтр по ЛПУ 
				if ( !empty($data['Lpu_id']) ) {
					$filter .= " and CCC.Lpu_id = :Lpu_id";
					$queryParams['Lpu_id'] = $data['Lpu_id'];
				}
				
				// Пациент (карта)
				if ( !empty($data['CmpArea_gid']) ) {
					$filter .= " and CCC.CmpArea_gid = :CmpArea_gid";
					$queryParams['CmpArea_gid'] = $data['CmpArea_gid'];
				}

				// Пациент (карта)
				if ( isset($data['CmpCallCard_Expo']) ) {
					$filter .= " and CCC.CmpCallCard_Expo = :CmpCallCard_Expo";
					$queryParams['CmpCallCard_Expo'] = $data['CmpCallCard_Expo'];
				}

				// Пациент (карта)
				if ( !empty($data['CmpCallCard_IsAlco']) ) {
					$filter .= " and CCC.CmpCallCard_IsAlco = :CmpCallCard_IsAlco";
					$queryParams['CmpCallCard_IsAlco'] = $data['CmpCallCard_IsAlco'];
				}

				// Пациент (карта)
				if ( !empty($data['CmpCallCard_IsPoli']) ) {
					$filter .= " and CCC.CmpCallCard_IsPoli = :CmpCallCard_IsPoli";
					$queryParams['CmpCallCard_IsPoli'] = $data['CmpCallCard_IsPoli'];
				}
				
				if ( !empty($data['CmpCallCard_isPaid']) ) {
					$filter .= " and ISNULL(CCC.CmpCallCard_isPaid,1) = :CmpCallCard_isPaid";
					$queryParams['CmpCallCard_isPaid'] = $data['CmpCallCard_isPaid'];
				}

				// Пациент (карта)
				if ( !empty($data['CmpDiag_aid']) ) {
					$filter .= " and CCC.CmpDiag_aid = :CmpDiag_aid";
					$queryParams['CmpDiag_aid'] = $data['CmpDiag_aid'];
				}

				// Пациент (карта)
				if ( !empty($data['CmpDiag_oid']) ) {
					$filter .= " and CCC.CmpDiag_oid = :CmpDiag_oid";
					$queryParams['CmpDiag_oid'] = $data['CmpDiag_oid'];
				}

				// Пациент (карта)
				if ( !empty($data['CmpTalon_id']) ) {
					$filter .= " and CCC.CmpTalon_id = :CmpTalon_id";
					$queryParams['CmpTalon_id'] = $data['CmpTalon_id'];
				}

				// Пациент (карта)
				if ( !empty($data['CmpTrauma_id']) ) {
					$filter .= " and CCC.CmpTrauma_id = :CmpTrauma_id";
					$queryParams['CmpTrauma_id'] = $data['CmpTrauma_id'];
				}

				// Пациент (карта)
				if ( !empty($data['Diag_sid']) ) {
					$filter .= " and CCC.Diag_sid = :Diag_sid";
					$queryParams['Diag_sid'] = $data['Diag_sid'];
				}

				// Пациент (карта)
				if ( !empty($data['Diag_uCode_From']) ) {
					$filter .= " and UD.Diag_Code >= :Diag_uCode_From";
					$queryParams['Diag_uCode_From'] = $data['Diag_uCode_From'];
				}

				// Пациент (карта)
				if ( !empty($data['Diag_uCode_To']) ) {
					$filter .= " and UD.Diag_Code <= :Diag_uCode_To";
					$queryParams['Diag_uCode_To'] = $data['Diag_uCode_To'];
				}

				// Пациент (карта)
				if ( !empty($data['Lpu_oid']) ) {
					$filter .= " and CL.Lpu_id = :Lpu_oid";
					$queryParams['Lpu_oid'] = $data['Lpu_oid'];
				}

				// Вызов
				if ( !empty($data['CmpArea_id']) ) {
					$filter .= " and CCC.CmpArea_id = :CmpArea_id";
					$queryParams['CmpArea_id'] = $data['CmpArea_id'];
				}

				// Вызов
				if ( !empty($data['CmpCallCard_City']) ) {
					$filter .= " and CCC.CmpCallCard_City like :CmpCallCard_City";
					$queryParams['CmpCallCard_City'] = $data['CmpCallCard_City'] . '%';
				}

				// Вызов
				if ( !empty($data['CmpCallCard_Dom']) ) {
					$filter .= " and CCC.CmpCallCard_Dom = :CmpCallCard_Dom";
					$queryParams['CmpCallCard_Dom'] = $data['CmpCallCard_Dom'];
				}

				// Вызов
				if ( !empty($data['CmpCallCard_Ktov']) ) {
					$filter .= " and CCC.CmpCallCard_Ktov = :CmpCallCard_Ktov";
					$queryParams['CmpCallCard_Ktov'] = $data['CmpCallCard_Ktov'];
				}
				
				// Вызов
				if (!empty($data['CmpResult_Code_From']) || !empty($data['CmpResult_Code_To'])) {
					$filter .= " and isnumeric(RTRIM(LTRIM(CRES.CmpResult_Code)) + 'e0') = 1";

					if ( !empty($data['CmpResult_Code_From']) ) {
						$filter .= " and (CAST(RTRIM(LTRIM(CRES.CmpResult_Code)) as bigint) >= :CmpResult_Code_From)";
						$queryParams['CmpResult_Code_From'] = $data['CmpResult_Code_From'];
					}
					
					if ( !empty($data['CmpResult_Code_To']) ) {
						$filter .= " and (CAST(RTRIM(LTRIM(CRES.CmpResult_Code)) as bigint)<= :CmpResult_Code_To)";
						$queryParams['CmpResult_Code_To'] = $data['CmpResult_Code_To'];
					}
				}
				if ( !empty($data['ResultDeseaseType_id']) ) {
					$filter .= " and CCC.ResultDeseaseType_id = :ResultDeseaseType_id";
					$queryParams['ResultDeseaseType_id'] = $data['ResultDeseaseType_id'];
				}
				
				// Вызов
				if ( !empty($data['CmpCallCard_Kvar']) ) {
					$filter .= " and CCC.CmpCallCard_Kvar = :CmpCallCard_Kvar";
					$queryParams['CmpCallCard_Kvar'] = $data['CmpCallCard_Kvar'];
				}

				// Вызов
				if ( !empty($data['CmpCallCard_Line']) ) {
					$filter .= " and CCC.CmpCallCard_Line = :CmpCallCard_Line";
					$queryParams['CmpCallCard_Line'] = $data['CmpCallCard_Line'];
				}

				// Вызов
				if ( !empty($data['CmpCallCard_Ngod']) ) {
					$filter .= " and CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod";
					$queryParams['CmpCallCard_Ngod'] = $data['CmpCallCard_Ngod'];
				}

				// Вызов
				if ( isset($data['CmpCallCard_Prty']) ) {
					$filter .= " and CCC.CmpCallCard_Prty = :CmpCallCard_Prty";
					$queryParams['CmpCallCard_Prty'] = $data['CmpCallCard_Prty'];
				}

				// Вызов
				if ( !empty($data['CmpCallCard_Numv']) ) {
					$filter .= " and CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
					$queryParams['CmpCallCard_Numv'] = $data['CmpCallCard_Numv'];
				}

				// Вызов
				if ( isset($data['CmpCallCard_prmDate_Range'][0]) ) {
					$filter .= " and cast(CCC.CmpCallCard_prmDT as date) >= cast(:CmpCallCard_prmDate_Range_0 as datetime)";
					$queryParams['CmpCallCard_prmDate_Range_0'] = $data['CmpCallCard_prmDate_Range'][0];
				}

				// Вызов
				if ( isset($data['CmpCallCard_prmDate_Range'][1]) ) {
					$filter .= " and cast(CCC.CmpCallCard_prmDT as date) <= cast(:CmpCallCard_prmDate_Range_1 as datetime)";
					$queryParams['CmpCallCard_prmDate_Range_1'] = $data['CmpCallCard_prmDate_Range'][1];
				}

				// Вызов
				if ( !empty($data['CmpCallCard_Numv']) ) {
					$filter .= " and CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
					$queryParams['CmpCallCard_Numv'] = $data['CmpCallCard_Numv'];
				}

				// Вызов
				if ( isset($data['CmpCallCard_Sect']) ) {
					$filter .= " and CCC.CmpCallCard_Sect = :CmpCallCard_Sect";
					$queryParams['CmpCallCard_Sect'] = $data['CmpCallCard_Sect'];
				}

				// Вызов
				if ( isset($data['CmpCallCard_Stan']) ) {
					$filter .= " and CCC.CmpCallCard_Stan = :CmpCallCard_Stan";
					$queryParams['CmpCallCard_Stan'] = $data['CmpCallCard_Stan'];
				}
				if ( isset($data['CmpCallCard_InRegistry'])) { //В рамках задачи http://redmine.swan.perm.ru/issues/17135
					$CmpCallCard_InRegistry = $data['CmpCallCard_InRegistry'];
					if($CmpCallCard_InRegistry == 1) { //Только карты, не вошедшие в реестр
						$filter .= " and (CCC.CmpCallCard_IsInReg = 1 or CCC.CmpCallCard_IsInReg is null)";
					}
					if($CmpCallCard_InRegistry == 2) { //Только карты, вошедшие в реестр
						$filter .= " and CCC.CmpCallCard_IsInReg = 2";
					}
				}
				// Вызов
				if ( !empty($data['CmpCallCard_Ulic']) ) {
					$filter .= " and CCC.CmpCallCard_Ulic like :CmpCallCard_Ulic";
					$queryParams['CmpCallCard_Ulic'] = $data['CmpCallCard_Ulic'] . '%';
				}

				// Вызов
				if ( !empty($data['CmpCallType_id']) ) {
					$filter .= " and CCC.CmpCallType_id = :CmpCallType_id";
					$queryParams['CmpCallType_id'] = $data['CmpCallType_id'];
				}

				// Вызов
				if ( !empty($data['CmpPlace_id']) ) {
					$filter .= " and CCC.CmpPlace_id = :CmpPlace_id";
					$queryParams['CmpPlace_id'] = $data['CmpPlace_id'];
				}

				// Вызов
				if ( !empty($data['CmpProfile_cid']) ) {
					$filter .= " and CCC.CmpProfile_cid = :CmpProfile_cid";
					$queryParams['CmpProfile_cid'] = $data['CmpProfile_cid'];
				}

				// Вызов
				if ( !empty($data['CmpReason_id']) ) {
					$filter .= " and CCC.CmpReason_id = :CmpReason_id";
					$queryParams['CmpReason_id'] = $data['CmpReason_id'];
				}

				// Вызов
				if ( !empty($data['CmpResult_id']) ) {
					$filter .= " and CCC.CmpResult_id = :CmpResult_id";
					$queryParams['CmpResult_id'] = $data['CmpResult_id'];
				}

				// Бригада СМП
				if ( !empty($data['CmpCallCard_Dokt']) ) {
					$filter .= " and CCC.CmpCallCard_Dokt like :CmpCallCard_Dokt";
					$queryParams['CmpCallCard_Dokt'] = $data['CmpCallCard_Dokt'] . '%';
				}

				// Бригада СМП
				if ( isset($data['CmpCallCard_Kakp']) ) {
					$filter .= " and CCC.CmpCallCard_Kakp = :CmpCallCard_Kakp";
					$queryParams['CmpCallCard_Kakp'] = $data['CmpCallCard_Kakp'];
				}

				// Бригада СМП
				if ( !empty($data['CmpCallCard_Kilo']) ) {
					$filter .= " and CCC.CmpCallCard_Kilo = :CmpCallCard_Kilo";
					$queryParams['CmpCallCard_Kilo'] = $data['CmpCallCard_Kilo'];
				}

				// Бригада СМП
				if ( !empty($data['CmpCallCard_Ncar']) ) {
					$filter .= " and CCC.CmpCallCard_Ncar = :CmpCallCard_Ncar";
					$queryParams['CmpCallCard_Ncar'] = $data['CmpCallCard_Ncar'];
				}

				// Бригада СМП
				if ( isset($data['CmpCallCard_Numb']) ) {
					$filter .= " and CCC.CmpCallCard_Numb = :CmpCallCard_Numb";
					$queryParams['CmpCallCard_Numb'] = $data['CmpCallCard_Numb'];
				}

				// Бригада СМП
				if ( !empty($data['CmpCallCard_Smpb']) ) {
					$filter .= " and CCC.CmpCallCard_Smpb = :CmpCallCard_Smpb";
					$queryParams['CmpCallCard_Smpb'] = $data['CmpCallCard_Smpb'];
				}

				// Бригада СМП
				if ( isset($data['CmpCallCard_Stbb']) ) {
					$filter .= " and CCC.CmpCallCard_Stbb = :CmpCallCard_Stbb";
					$queryParams['CmpCallCard_Stbb'] = $data['CmpCallCard_Stbb'];
				}

				// Бригада СМП
				if ( !empty($data['CmpCallCard_Stbr']) ) {
					$filter .= " and CCC.CmpCallCard_Stbr = :CmpCallCard_Stbr";
					$queryParams['CmpCallCard_Stbr'] = $data['CmpCallCard_Stbr'];
				}

				// Бригада СМП
				if ( !empty($data['CmpCallCard_Tab2']) ) {
					$filter .= " and CCC.CmpCallCard_Tab2 = :CmpCallCard_Tab2";
					$queryParams['CmpCallCard_Tab2'] = $data['CmpCallCard_Tab2'];
				}

				// Бригада СМП
				if ( !empty($data['CmpCallCard_Tab3']) ) {
					$filter .= " and CCC.CmpCallCard_Tab3 = :CmpCallCard_Tab3";
					$queryParams['CmpCallCard_Tab3'] = $data['CmpCallCard_Tab3'];
				}

				// Бригада СМП
				if ( !empty($data['CmpCallCard_Tab4']) ) {
					$filter .= " and CCC.CmpCallCard_Tab4 = :CmpCallCard_Tab4";
					$queryParams['CmpCallCard_Tab4'] = $data['CmpCallCard_Tab4'];
				}

				// Бригада СМП
				if ( !empty($data['CmpCallCard_Tabn']) ) {
					$filter .= " and CCC.CmpCallCard_Tabn = :CmpCallCard_Tabn";
					$queryParams['CmpCallCard_Tabn'] = $data['CmpCallCard_Tabn'];
				}

				// Бригада СМП
				if ( !empty($data['CmpProfile_bid']) ) {
					$filter .= " and CCC.CmpProfile_bid = :CmpProfile_bid";
					$queryParams['CmpProfile_bid'] = $data['CmpProfile_bid'];
				}

				// Управление вызовом
				if ( !empty($data['CmpCallCard_D201']) ) {
					$filter .= " and SUBSTRING(CCC.CmpCallCard_D201, 1, PATINDEX('% %', CCC.CmpCallCard_D201)) = :CmpCallCard_D201";
					$queryParams['CmpCallCard_D201'] = $data['CmpCallCard_D201'];
				}

				// Управление вызовом
				if ( !empty($data['CmpCallCard_Dlit']) ) {
					$filter .= " and CCC.CmpCallCard_Dlit = :CmpCallCard_Dlit";
					$queryParams['CmpCallCard_Dlit'] = $data['CmpCallCard_Dlit'];
				}

				// Управление вызовом
				if ( !empty($data['CmpCallCard_Dsp1']) ) {
					$filter .= " and SUBSTRING(CCC.CmpCallCard_Dsp1, 1, PATINDEX('% %', CCC.CmpCallCard_Dsp1)) = :CmpCallCard_Dsp1";
					$queryParams['CmpCallCard_Dsp1'] = $data['CmpCallCard_Dsp1'];
				}

				// Управление вызовом
				if ( !empty($data['CmpCallCard_Dsp2']) ) {
					$filter .= " and SUBSTRING(CCC.CmpCallCard_Dsp2, 1, PATINDEX('% %', CCC.CmpCallCard_Dsp2)) = :CmpCallCard_Dsp2";
					$queryParams['CmpCallCard_Dsp2'] = $data['CmpCallCard_Dsp2'];
				}

				// Управление вызовом
				if ( !empty($data['CmpCallCard_Dsp3']) ) {
					$filter .= " and SUBSTRING(CCC.CmpCallCard_Dsp3, 1, PATINDEX('% %', CCC.CmpCallCard_Dsp3)) = :CmpCallCard_Dsp3";
					$queryParams['CmpCallCard_Dsp3'] = $data['CmpCallCard_Dsp3'];
				}

				// Управление вызовом
				if ( !empty($data['CmpCallCard_Dspp']) ) {
					$filter .= " and SUBSTRING(CCC.CmpCallCard_Dspp, 1, PATINDEX('% %', CCC.CmpCallCard_Dspp)) = :CmpCallCard_Dspp";
					$queryParams['CmpCallCard_Dspp'] = $data['CmpCallCard_Dspp'];
				}

				// Управление вызовом
				if ( !empty($data['CmpCallCard_Prdl']) ) {
					$filter .= " and CCC.CmpCallCard_Prdl = :CmpCallCard_Prdl";
					$queryParams['CmpCallCard_Prdl'] = $data['CmpCallCard_Prdl'];
				}

				// Управление вызовом
				if ( !empty($data['CmpCallCard_Smpp']) ) {
					$filter .= " and CCC.CmpCallCard_Smpp = :CmpCallCard_Smpp";
					$queryParams['CmpCallCard_Smpp'] = $data['CmpCallCard_Smpp'];
				}

				// Управление вызовом
				if ( !empty($data['CmpCallCard_Vr51']) ) {
					$filter .= " and SUBSTRING(CCC.CmpCallCard_Vr51, 1, PATINDEX('% %', CCC.CmpCallCard_Vr51)) = :CmpCallCard_Vr51";
					$queryParams['CmpCallCard_Vr51'] = $data['CmpCallCard_Vr51'];
				}
			break;

			case 'PersonPrivilegeWOW':
				// Хотя здесь должен быть inner - пока к сожалению на тестовой базе нет регистра (с) Night
				$query .= " inner join PersonPrivilegeWOW PPW with (nolock) on PPW.Person_id = PS.Person_id";
				$query .= " left join PrivilegeTypeWOW PTW with (nolock) on PTW.PrivilegeTypeWow_id = PPW.PrivilegeTypeWow_id";
				$query .= " left join Sex with (nolock) on Sex.Sex_id = PS.Sex_id";
				$query .= " left join v_Address UAdd (nolock) on UAdd.Address_id = ps.UAddress_id";
				$query .= " left join v_Address PAdd (nolock) on PAdd.Address_id = ps.PAddress_id";
				if ( isset( $data['PrivilegeTypeWow_id'] ) )
				{
					$filter .= " and PTW.PrivilegeTypeWow_id = :PrivilegeTypeWow_id ";
					$queryParams['PrivilegeTypeWow_id'] = $data['PrivilegeTypeWow_id'];	
				}
			break;
			
			case 'EvnPLWOW':
				if ( $data['PersonPeriodicType_id'] == 2 ) {
					$query .= " inner join v_EvnPLWOW EPW with (nolock) on EPW.Server_id = PS.Server_id and EPW.PersonEvn_id = PS.PersonEvn_id and EPW.Lpu_id = :Lpu_id";
				}
				else {
					$query .= " inner join v_EvnPLWOW EPW with (nolock) on EPW.Person_id = PS.Person_id and EPW.Lpu_id = :Lpu_id";
				}

				// $query .= " inner join v_EvnPLWOW EPW on EPW.Person_id = PS.Person_id and EPW.Lpu_id = :Lpu_id";
				//$query .= " left join PersonPrivilegeWOW PPW with (nolock) on PPW.Person_id = PS.Person_id";
				$query .= " outer apply ( select top 1 PrivilegeTypeWow_id from PersonPrivilegeWOW with (nolock) where Person_id = PS.Person_id ) as PPW";
				$query .= " left join PrivilegeTypeWOW PTW with (nolock) on PTW.PrivilegeTypeWow_id = PPW.PrivilegeTypeWow_id";
				$query .= " left join YesNo with (nolock) on YesNo.YesNo_id = EPW.EvnPLWOW_IsFinish";
				if ( isset( $data['PrivilegeTypeWow_id'] ) )
				{
					$filter .= " and PTW.PrivilegeTypeWow_id = :PrivilegeTypeWow_id ";
					$queryParams['PrivilegeTypeWow_id'] = $data['PrivilegeTypeWow_id'];	
				}
				
				break;

			case 'EvnDtpWound':
				if ( $data['PersonPeriodicType_id'] == 2 ) {
					$query .= " inner join v_EvnDtpWound EDW with (nolock) on EDW.Server_id = PS.Server_id and EDW.PersonEvn_id = PS.PersonEvn_id and EDW.Lpu_id = :Lpu_id";
				}
				else {
					$query .= " inner join v_EvnDtpWound EDW with (nolock) on EDW.Person_id = PS.Person_id and EDW.Lpu_id = :Lpu_id";
				}

				// $query .= " inner join v_EvnDtpWound EDW on EDW.Person_id = PS.Person_id and EDW.Lpu_id = :Lpu_id";
				if ( isset($data['EvnDtpWound_setDate_Range'][0]) ) {
					$filter .= " and EDW.EvnDtpWound_setDate >= :EvnDtpWound_setDate_Range_0";
					$queryParams['EvnDtpWound_setDate_Range_0'] = $data['EvnDtpWound_setDate_Range'][0];
				}
				if ( isset($data['EvnDtpWound_setDate_Range'][1]) ) {
					$filter .= " and EDW.EvnDtpWound_setDate <= :EvnDtpWound_setDate_Range_1";
					$queryParams['EvnDtpWound_setDate_Range_1'] = $data['EvnDtpWound_setDate_Range'][1];
				}
			break;
				
			case 'PersonDopDisp':
				$query .= " inner join PersonDopDisp DD with (nolock) on DD.Person_id = PS.Person_id and DD.Lpu_id = :Lpu_id";
				$query .= " left join Sex with (nolock) on PS.Sex_id = Sex.Sex_id";
				$query .= " 
					left join v_Job as job1 with (nolock) ON PS.Job_id=job1.Job_id
					left join v_Org as org1 with (nolock) ON job1.Org_id=org1.Org_id
					left join v_Okved as okved1 with (nolock) ON okved1.Okved_id=org1.Okved_id";
				$query .= " 
					left join v_Address as addr1 with (nolock) ON PS.UAddress_id=addr1.Address_id
					left join v_KLAreaStat as astat1 with (nolock) ON (
					((addr1.KLCountry_id = astat1.KLCountry_id) or (astat1.KLCountry_id is null)) and
					((addr1.KLRGN_id = astat1.KLRGN_id) or (astat1.KLRGN_id is null)) and
					((addr1.KLSubRGN_id = astat1.KLSubRGN_id) or (astat1.KLSubRGN_id is null)) and
					((addr1.KLCity_id = astat1.KLCity_id) or (astat1.KLCity_id is null)) and
					((addr1.KLTown_id = astat1.KLTown_id) or (astat1.KLTown_id is null))
					)
					left join v_Address as addr2 with (nolock) ON org1.UAddress_id=addr2.Address_id
					left join v_KLAreaStat as astat2 with (nolock) ON (
					((addr2.KLCountry_id = astat2.KLCountry_id) or (astat2.KLCountry_id is null)) and
					((addr2.KLRGN_id = astat2.KLRGN_id) or (astat2.KLRGN_id is null)) and
					((addr2.KLSubRGN_id = astat2.KLSubRGN_id) or (astat2.KLSubRGN_id is null)) and
					((addr2.KLCity_id = astat2.KLCity_id) or (astat2.KLCity_id is null)) and
					((addr2.KLTown_id = astat2.KLTown_id) or (astat2.KLTown_id is null))
					)";
					
				$query .= "
					outer apply (
						select top 1
							Lpu_Nick 
						from
							PersonDopDisp pdd with (nolock)
							inner join v_Lpu vlp with (nolock) on vlp.Lpu_id = pdd.Lpu_id
						where
							pdd.Person_id = PS.Person_id
							and pdd.Lpu_id <> :Lpu_id
							and pdd.PersonDopDisp_Year = :PersonDopDisp_Year
					) as otherddlpu ";
					
				$query .= "
					left join v_EvnPLDispDop epldd with (nolock) on
						epldd.Person_id = PS.Person_id
						and epldd.Lpu_id = :Lpu_id
						and epldd.EvnPLDispDop_setDate between :PersonDopDisp_Year_Start and :PersonDopDisp_Year_End
				";
				
				if ( isset($data['dop_disp_reg_beg_date']) && isset($data['dop_disp_reg_beg_time']) )
				{
					$filter .= " and DD.PersonDopDisp_updDT >= :DDR_BegDate ";
					$queryParams['DDR_BegDate'] = $data['dop_disp_reg_beg_date']." ".$data['dop_disp_reg_beg_time'];
				}
				
				$filter .= " and DD.PersonDopDisp_Year = :PersonDopDisp_Year ";
				if ( isset($data['PersonDopDisp_Year']) )
				{
					//как показало тестирование в реальных условиях
					//and epldd.EvnPLDispDop_setDate between '2010-01-01' and '2010-12-31'
					//работает на порядок быстрее, чем
					//and year(epldd.EvnPLDispDop_setDate) = 2010
					$queryParams['PersonDopDisp_Year_Start'] = $data['PersonDopDisp_Year'].'-01-01';
					$queryParams['PersonDopDisp_Year_End'] = $data['PersonDopDisp_Year'].'-12-31';
					$queryParams['PersonDopDisp_Year'] = $data['PersonDopDisp_Year'];
				}
				else
				{
					$queryParams['PersonDopDisp_Year_Start'] = date('Y').'-01-01';
					$queryParams['PersonDopDisp_Year_End'] = date('Y').'-12-31';
					$queryParams['PersonDopDisp_Year'] = date('Y');
				}
				
				
			break;
			
			case 'PersonDispOrpPeriod':
				$query .= " inner join v_PersonDispOrp DO with (nolock) on DO.Person_id = PS.Person_id and DO.Lpu_id = :Lpu_id";
				$query .= " left join Sex with (nolock) on PS.Sex_id = Sex.Sex_id";
				$query .= " left join v_Address UAdd (nolock) on UAdd.Address_id = ps.UAddress_id
							left join v_Address PAdd (nolock) on PAdd.Address_id = ps.PAddress_id";
				$query .= " left join v_EvnPLDispTeenInspection EPLDTI with (nolock) on EPLDTI.PersonDispOrp_id = DO.PersonDispOrp_id";
				$query .= "
					outer apply (select top 1
							pc.Person_id as PersonCard_Person_id,
							pc.Lpu_id
						from v_PersonCard pc with (nolock)
						where
							pc.Person_id = ps.Person_id
							and LpuAttachType_id = 1
						order by PersonCard_begDate desc
						) as pcard
					left join v_Lpu LATT with (nolock) on pcard.Lpu_id=LATT.Lpu_id
					left join v_EducationInstitutionType EIT (nolock) on EIT.EducationInstitutionType_id = DO.EducationInstitutionType_id
				";
				
				if ( isset($data['reg_beg_date']) && isset($data['reg_beg_time']) )
				{
					$filter .= " and DO.PersonDispOrp_updDT >= :DDR_BegDate ";
					$queryParams['DDR_BegDate'] = $data['reg_beg_date']." ".$data['reg_beg_time'];
				}
				
				$filter .= " and DO.PersonDispOrp_Year = :PersonDispOrp_Year ";
				if ( isset($data['PersonDispOrp_Year']) )
				{
					$queryParams['PersonDispOrp_Year'] = $data['PersonDispOrp_Year'];
				}
				else
				{
					$queryParams['PersonDispOrp_Year'] = date('Y');
				}

				if ( !empty($data['EducationInstitutionType_id']) ) {
					$filter .= " and DO.EducationInstitutionType_id = :EducationInstitutionType_id ";
					$queryParams['EducationInstitutionType_id'] = $data['EducationInstitutionType_id'];
				}
				
				$filter .= " and DO.CategoryChildType_id IN (8)";		
				
			break;
			
			case 'PersonDispOrpPred':
				$query .= " inner join v_PersonDispOrp DO with (nolock) on DO.Person_id = PS.Person_id and DO.Lpu_id = :Lpu_id";
				$query .= " left join Sex with (nolock) on PS.Sex_id = Sex.Sex_id";
				$query .= " left join v_EvnPLDispTeenInspection EPLDTI with (nolock) on EPLDTI.PersonDispOrp_id = DO.PersonDispOrp_id";
				$query .= "
					outer apply (select top 1
							pc.Person_id as PersonCard_Person_id,
							pc.Lpu_id
						from v_PersonCard pc with (nolock)
						where
							pc.Person_id = ps.Person_id
							and LpuAttachType_id = 1
						order by PersonCard_begDate desc
						) as pcard
					left join v_Lpu LATT with (nolock) on pcard.Lpu_id=LATT.Lpu_id
					left join v_EducationInstitutionType EIT (nolock) on EIT.EducationInstitutionType_id = DO.EducationInstitutionType_id
				";
				
				if ( isset($data['reg_beg_date']) && isset($data['reg_beg_time']) )
				{
					$filter .= " and DO.PersonDispOrp_updDT >= :DDR_BegDate ";
					$queryParams['DDR_BegDate'] = $data['reg_beg_date']." ".$data['reg_beg_time'];
				}
				
				$filter .= " and DO.PersonDispOrp_Year = :PersonDispOrp_Year ";
				if ( isset($data['PersonDispOrp_Year']) )
				{
					$queryParams['PersonDispOrp_Year'] = $data['PersonDispOrp_Year'];
				}
				else
				{
					$queryParams['PersonDispOrp_Year'] = date('Y');
				}

				if ( !empty($data['EducationInstitutionType_id']) ) {
					$filter .= " and DO.EducationInstitutionType_id = :EducationInstitutionType_id ";
					$queryParams['EducationInstitutionType_id'] = $data['EducationInstitutionType_id'];
				}
				
				$filter .= " and DO.CategoryChildType_id IN (9)";
				
			break;
			
			case 'PersonDispOrpProf':
				$query .= " inner join v_PersonDispOrp DO with (nolock) on DO.Person_id = PS.Person_id and DO.Lpu_id = :Lpu_id";
				$query .= " left join Sex with (nolock) on PS.Sex_id = Sex.Sex_id";
				$query .= " left join v_EvnPLDispTeenInspection EPLDTI with (nolock) on EPLDTI.PersonDispOrp_id = DO.PersonDispOrp_id";
				$query .= " left join v_AgeGroupDisp AGD (nolock) on AGD.AgeGroupDisp_id = DO.AgeGroupDisp_id";
				
				if ( isset($data['reg_beg_date']) && isset($data['reg_beg_time']) )
				{
					$filter .= " and DO.PersonDispOrp_updDT >= :DDR_BegDate ";
					$queryParams['DDR_BegDate'] = $data['reg_beg_date']." ".$data['reg_beg_time'];
				}
				
				$filter .= " and DO.PersonDispOrp_Year = :PersonDispOrp_Year ";
				if ( isset($data['PersonDispOrp_Year']) )
				{
					$queryParams['PersonDispOrp_Year'] = $data['PersonDispOrp_Year'];
				}
				else
				{
					$queryParams['PersonDispOrp_Year'] = date('Y');
				}

				if ( !empty($data['AgeGroupDisp_id']) ) {
					$filter .= " and DO.AgeGroupDisp_id = :AgeGroupDisp_id ";
					$queryParams['AgeGroupDisp_id'] = $data['AgeGroupDisp_id'];
				}
				
				if ( !empty($data['OrgExist']) ) {
					if ($data['OrgExist'] == 2) {
						$filter .= " and DO.Org_id IS NOT NULL";
					} else {
						$filter .= " and DO.Org_id IS NULL";
					}
				}
				
				$filter .= " and DO.CategoryChildType_id IN (10)";
				
			break;
			
			case 'PersonDispOrp':
				$query .= " inner join v_PersonDispOrp DO with (nolock) on DO.Person_id = PS.Person_id and DO.Lpu_id = :Lpu_id";
				$query .= " left join Sex with (nolock) on PS.Sex_id = Sex.Sex_id";
				$query .= " 
					left join v_Job as job1 with (nolock) ON PS.Job_id=job1.Job_id
					left join v_Org as org1 with (nolock) ON job1.Org_id=org1.Org_id
					left join v_Okved as okved1 with (nolock) ON okved1.Okved_id=org1.Okved_id";
				$query .= "
					left join v_Address UAdd (nolock) on UAdd.Address_id = ps.UAddress_id
					left join v_Address PAdd (nolock) on PAdd.Address_id = ps.PAddress_id
					left join v_Address as addr1 with (nolock) ON PS.UAddress_id=addr1.Address_id
					left join v_KLAreaStat as astat1 with (nolock) ON (
					((addr1.KLCountry_id = astat1.KLCountry_id) or (astat1.KLCountry_id is null)) and
					((addr1.KLRGN_id = astat1.KLRGN_id) or (astat1.KLRGN_id is null)) and
					((addr1.KLSubRGN_id = astat1.KLSubRGN_id) or (astat1.KLSubRGN_id is null)) and
					((addr1.KLCity_id = astat1.KLCity_id) or (astat1.KLCity_id is null)) and
					((addr1.KLTown_id = astat1.KLTown_id) or (astat1.KLTown_id is null))
					)
					left join v_Address as addr2 with (nolock) ON org1.UAddress_id=addr2.Address_id
					left join v_KLAreaStat as astat2 with (nolock) ON (
					((addr2.KLCountry_id = astat2.KLCountry_id) or (astat2.KLCountry_id is null)) and
					((addr2.KLRGN_id = astat2.KLRGN_id) or (astat2.KLRGN_id is null)) and
					((addr2.KLSubRGN_id = astat2.KLSubRGN_id) or (astat2.KLSubRGN_id is null)) and
					((addr2.KLCity_id = astat2.KLCity_id) or (astat2.KLCity_id is null)) and
					((addr2.KLTown_id = astat2.KLTown_id) or (astat2.KLTown_id is null))
					)";
				
				$query .= "
					outer apply (
						select
							Lpu_Nick 
						from
							PersonDispOrp pdd with (nolock)
							inner join v_Lpu vlp with (nolock) on vlp.Lpu_id = pdd.Lpu_id
						where
							pdd.Person_id = PS.Person_id
							and pdd.Lpu_id <> :Lpu_id
							and pdd.PersonDispOrp_Year = :PersonDispOrp_Year
					) as ODL ";

				$query .= "
					outer apply (
						select 
							top 1 EvnPLDispOrp_id
						from
							v_EvnPLDispOrp epldd with (nolock)
						where
							epldd.Person_id=PS.Person_id and
							epldd.Lpu_id = :Lpu_id
							and year(epldd.EvnPLDispOrp_setDate) = :PersonDispOrp_Year
					) as EPLDO ";
				
				if ( isset($data['reg_beg_date']) && isset($data['reg_beg_time']) )
				{
					$filter .= " and DO.PersonDispOrp_updDT >= :DDR_BegDate ";
					$queryParams['DDR_BegDate'] = $data['reg_beg_date']." ".$data['reg_beg_time'];
				}
				
				$filter .= " and DO.PersonDispOrp_Year = :PersonDispOrp_Year ";
				if ( isset($data['PersonDispOrp_Year']) )
				{
					$queryParams['PersonDispOrp_Year'] = $data['PersonDispOrp_Year'];
				}
				else
				{
					$queryParams['PersonDispOrp_Year'] = date('Y');
				}
				
				if ( empty($data['CategoryChildType']) ) {
					$data['CategoryChildType'] = 'orp';
				}

				if ( !empty($data['EducationInstitutionType_id']) ) {
					$filter .= " and DO.EducationInstitutionType_id = :EducationInstitutionType_id ";
					$queryParams['EducationInstitutionType_id'] = $data['EducationInstitutionType_id'];
				}
				
				switch($data['CategoryChildType'])
				{
					case 'orp':
						$filter .= " and DO.CategoryChildType_id IN (1,2,3,4)";
					break;
					case 'orpadopted':
						$filter .= " and DO.CategoryChildType_id IN (5,6,7)";
					break;
				}				
				
			break;
			
			case 'PersonDispOrpOld':
				$query .= " inner join v_PersonDispOrp DO with (nolock) on DO.Person_id = PS.Person_id and DO.Lpu_id = :Lpu_id";
				$query .= " left join Sex with (nolock) on PS.Sex_id = Sex.Sex_id";
				$query .= " 
					left join v_Job as job1 with (nolock) ON PS.Job_id=job1.Job_id
					left join v_Org as org1 with (nolock) ON job1.Org_id=org1.Org_id
					left join v_Okved as okved1 with (nolock) ON okved1.Okved_id=org1.Okved_id";
				$query .= " 
					left join v_Address as addr1 with (nolock) ON PS.UAddress_id=addr1.Address_id
					left join v_KLAreaStat as astat1 with (nolock) ON (
					((addr1.KLCountry_id = astat1.KLCountry_id) or (astat1.KLCountry_id is null)) and
					((addr1.KLRGN_id = astat1.KLRGN_id) or (astat1.KLRGN_id is null)) and
					((addr1.KLSubRGN_id = astat1.KLSubRGN_id) or (astat1.KLSubRGN_id is null)) and
					((addr1.KLCity_id = astat1.KLCity_id) or (astat1.KLCity_id is null)) and
					((addr1.KLTown_id = astat1.KLTown_id) or (astat1.KLTown_id is null))
					)
					left join v_Address as addr2 with (nolock) ON org1.UAddress_id=addr2.Address_id
					left join v_KLAreaStat as astat2 with (nolock) ON (
					((addr2.KLCountry_id = astat2.KLCountry_id) or (astat2.KLCountry_id is null)) and
					((addr2.KLRGN_id = astat2.KLRGN_id) or (astat2.KLRGN_id is null)) and
					((addr2.KLSubRGN_id = astat2.KLSubRGN_id) or (astat2.KLSubRGN_id is null)) and
					((addr2.KLCity_id = astat2.KLCity_id) or (astat2.KLCity_id is null)) and
					((addr2.KLTown_id = astat2.KLTown_id) or (astat2.KLTown_id is null))
					)";
				
				$query .= "
					outer apply (
						select
							Lpu_Nick 
						from
							PersonDispOrp pdd with (nolock)
							inner join v_Lpu vlp with (nolock) on vlp.Lpu_id = pdd.Lpu_id
						where
							pdd.Person_id = PS.Person_id
							and pdd.Lpu_id <> :Lpu_id
							and pdd.PersonDispOrp_Year = :PersonDispOrp_Year
					) as ODL ";
					
				$query .= "
					outer apply (
						select 
							top 1 EvnPLDispOrp_id
						from
							v_EvnPLDispOrp epldd with (nolock)
						where
							epldd.Person_id=PS.Person_id and
							epldd.Lpu_id = :Lpu_id
							and year(epldd.EvnPLDispOrp_setDate) = :PersonDispOrp_Year
					) as EPLDO ";
				
				if ( isset($data['reg_beg_date']) && isset($data['reg_beg_time']) )
				{
					$filter .= " and DO.PersonDispOrp_updDT >= :DDR_BegDate ";
					$queryParams['DDR_BegDate'] = $data['reg_beg_date']." ".$data['reg_beg_time'];
				}
				
				$filter .= " and DO.PersonDispOrp_Year = :PersonDispOrp_Year ";
				
				// до 2013 года
				$filter .= " and DO.PersonDispOrp_Year <= 2012 ";
				
				if ( isset($data['PersonDispOrp_Year']) )
				{
					$queryParams['PersonDispOrp_Year'] = $data['PersonDispOrp_Year'];
				}
				else
				{
					$queryParams['PersonDispOrp_Year'] = date('Y');
				}
				
				
			break;
			
			case 'EvnPLDispDop13':
				// https://redmine.swan.perm.ru/issues/37296
				$this->load->model('EvnPLDispDop13_model', 'EvnPLDispDop13_model');

				if ( isset($data['EvnPLDispDop13_setDate']) ) {
					$filter .= " and [EPLDD13].EvnPLDispDop13_setDate = cast(:EvnPLDispDop13_setDate as datetime) ";
					$queryParams['EvnPLDispDop13_setDate'] = $data['EvnPLDispDop13_setDate'];
				}
				if ( isset($data['EvnPLDispDop13_setDate_Range'][0]) ) {
					$filter .= " and [EPLDD13].EvnPLDispDop13_setDate >= cast(:EvnPLDispDop13_setDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispDop13_setDate_Range_0'] = $data['EvnPLDispDop13_setDate_Range'][0];
				}
				if ( isset($data['EvnPLDispDop13_setDate_Range'][1]) ) {
					$filter .= " and [EPLDD13].EvnPLDispDop13_setDate <= cast(:EvnPLDispDop13_setDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispDop13_setDate_Range_1'] = $data['EvnPLDispDop13_setDate_Range'][1];
				}
				if ( isset($data['EvnPLDispDop13_disDate']) ) {
					$filter .= " and [EPLDD13].EvnPLDispDop13_disDate = cast(:EvnPLDispDop13_disDate as datetime) ";
					$queryParams['EvnPLDispDop13_disDate'] = $data['EvnPLDispDop13_disDate'];
				}
				if ( isset($data['EvnPLDispDop13_disDate_Range'][0]) ) {
					$filter .= " and [EPLDD13].EvnPLDispDop13_disDate >= cast(:EvnPLDispDop13_disDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispDop13_disDate_Range_0'] = $data['EvnPLDispDop13_disDate_Range'][0];
				}
				if ( isset($data['EvnPLDispDop13_disDate_Range'][1]) ) {
					$filter .= " and [EPLDD13].EvnPLDispDop13_disDate <= cast(:EvnPLDispDop13_disDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispDop13_disDate_Range_1'] = $data['EvnPLDispDop13_disDate_Range'][1];
				}
				if ( isset($data['EvnPLDispDop13_IsFinish']) ) {
					$filter .= " and isnull([EPLDD13].EvnPLDispDop13_IsEndStage, 1) = :EvnPLDispDop13_IsFinish ";
					$queryParams['EvnPLDispDop13_IsFinish'] = $data['EvnPLDispDop13_IsFinish'];
				}
				if ( isset($data['EvnPLDispDop13_Cancel']) ) {
					if ($data['EvnPLDispDop13_Cancel'] == 2) {
						$data['DopDispInfoConsent_IsAgree'] = 1;
					} else {
						$data['DopDispInfoConsent_IsAgree'] = 2;
					}
					$filter .= " and ISNULL(DDICData.DopDispInfoConsent_IsAgree,2) = :DopDispInfoConsent_IsAgree ";
					$queryParams['DopDispInfoConsent_IsAgree'] = $data['DopDispInfoConsent_IsAgree'];
				}
				if ( isset($data['EvnPLDispDop13_IsTwoStage']) ) {
					$filter .= " and isnull([EPLDD13].EvnPLDispDop13_IsTwoStage, 1) = :EvnPLDispDop13_IsTwoStage ";
					$queryParams['EvnPLDispDop13_IsTwoStage'] = $data['EvnPLDispDop13_IsTwoStage'];
				}
				
				if ( isset($data['EvnPLDispDop13_HealthKind_id']) ) {
					$filter .= " and [EPLDD13].HealthKind_id = :EvnPLDispDop13_HealthKind_id ";
					$queryParams['EvnPLDispDop13_HealthKind_id'] = $data['EvnPLDispDop13_HealthKind_id'];
				}
				
				if ( isset($data['EvnPLDispDop13_isPaid']) ) {
					$filter .= " and ISNULL([EPLDD13].EvnPLDispDop13_isPaid,1) = :EvnPLDispDop13_isPaid ";
					$queryParams['EvnPLDispDop13_isPaid'] = $data['EvnPLDispDop13_isPaid'];
				}
				
				if ( isset($data['EvnPLDispDop13Second_isPaid']) ) {
					$filter .= " and ISNULL([DopDispSecond].EvnPLDispDop13_isPaid,1) = :EvnPLDispDop13Second_isPaid ";
					$queryParams['EvnPLDispDop13Second_isPaid'] = $data['EvnPLDispDop13Second_isPaid'];
				}

				if ( isset($data['EvnPLDispDop13_isMobile']) ) {
					$filter .= " and ISNULL([EPLDD13].EvnPLDispDop13_isMobile,1) = :EvnPLDispDop13_isMobile ";
					$queryParams['EvnPLDispDop13_isMobile'] = $data['EvnPLDispDop13_isMobile'];
				}

				if ( isset($data['EvnPLDispDop13Second_isMobile']) ) {
					$filter .= " and ISNULL([DopDispSecond].EvnPLDispDop13_isMobile,1) = :EvnPLDispDop13Second_isMobile ";
					$queryParams['EvnPLDispDop13Second_isMobile'] = $data['EvnPLDispDop13Second_isMobile'];
				}
				
				if ( isset($data['EvnPLDispDop13Second_setDate']) ) {
					$filter .= " and [DopDispSecond].EvnPLDispDop13_setDate = cast(:EvnPLDispDop13Second_setDate as datetime) ";
					$queryParams['EvnPLDispDop13Second_setDate'] = $data['EvnPLDispDop13Second_setDate'];
				}
				if ( isset($data['EvnPLDispDop13Second_setDate_Range'][0]) ) {
					$filter .= " and [DopDispSecond].EvnPLDispDop13_setDate >= cast(:EvnPLDispDop13Second_setDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispDop13Second_setDate_Range_0'] = $data['EvnPLDispDop13Second_setDate_Range'][0];
				}
				if ( isset($data['EvnPLDispDop13Second_setDate_Range'][1]) ) {
					$filter .= " and [DopDispSecond].EvnPLDispDop13_setDate <= cast(:EvnPLDispDop13Second_setDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispDop13Second_setDate_Range_1'] = $data['EvnPLDispDop13Second_setDate_Range'][1];
				}
				if ( isset($data['EvnPLDispDop13Second_disDate']) ) {
					$filter .= " and [DopDispSecond].EvnPLDispDop13_disDate = cast(:EvnPLDispDop13Second_disDate as datetime) ";
					$queryParams['EvnPLDispDop13Second_disDate'] = $data['EvnPLDispDop13Second_disDate'];
				}
				if ( isset($data['EvnPLDispDop13Second_disDate_Range'][0]) ) {
					$filter .= " and [DopDispSecond].EvnPLDispDop13_disDate >= cast(:EvnPLDispDop13Second_disDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispDop13Second_disDate_Range_0'] = $data['EvnPLDispDop13Second_disDate_Range'][0];
				}
				if ( isset($data['EvnPLDispDop13Second_disDate_Range'][1]) ) {
					$filter .= " and [DopDispSecond].EvnPLDispDop13_disDate <= cast(:EvnPLDispDop13Second_disDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispDop13Second_disDate_Range_1'] = $data['EvnPLDispDop13Second_disDate_Range'][1];
				}
				
				if ( isset($data['EvnPLDispDop13Second_IsFinish']) ) {
					$filter .= " and isnull([DopDispSecond].EvnPLDispDop13_IsEndStage, 1) = :EvnPLDispDop13Second_IsFinish ";
					$queryParams['EvnPLDispDop13Second_IsFinish'] = $data['EvnPLDispDop13Second_IsFinish'];
				}
				
				if ( isset($data['EvnPLDispDop13Second_HealthKind_id']) ) {
					$filter .= " and [DopDispSecond].HealthKind_id = :EvnPLDispDop13Second_HealthKind_id ";
					$queryParams['EvnPLDispDop13Second_HealthKind_id'] = $data['EvnPLDispDop13Second_HealthKind_id'];
				}
				
				if ( isset($data['PersonDopDisp_Year']) ) {
					// $filter .= " and DD.PersonDopDisp_Year = :PersonDopDisp_Year ";
					$queryParams['PersonDopDisp_Year'] = $data['PersonDopDisp_Year'];
				}
				else {
					$queryParams['PersonDopDisp_Year'] = 2013;
				}

				$queryParams['Lpu_id'] = $data['Lpu_id'];

				// начиная с 2013 года
				// $filter .= " and DD.PersonDopDisp_Year >= 2013 ";
				
				//$query .= " inner join PersonDopDisp DD with (nolock) on DD.Person_id = PS.Person_id and DD.Lpu_id = :Lpu_id";

				$add_filter = "";
				if ( in_array($data['session']['region']['nick'], array('ufa')) ) {
					$add_filter = " or exists (select top 1 PersonPrivilegeWOW_id from v_PersonPrivilegeWOW (nolock) where Person_id = PS.Person_id)";
				}
				
				// https://redmine.swan.perm.ru/issues/19835
				// Если пациент, состоит в регистре ВОВ, то на данной форме его отображать независимо от возраста http://redmine.swan.perm.ru/issues/22014
				$filter .= "
					and (
						(dbo.Age2(PS.Person_BirthDay, cast(:PersonDopDisp_Year as varchar) + '-12-31') - 21 >= 0 and (dbo.Age2(PS.Person_BirthDay, cast(:PersonDopDisp_Year as varchar) + '-12-31') - 21)%3 = 0)
						or
						((dbo.Age2(PS.Person_BirthDay, cast(:PersonDopDisp_Year as varchar) + '-12-31') BETWEEN 18 AND 99) and exists (select top 1 pp.PersonPrivilege_id from v_PersonPrivilege pp (nolock) inner join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id where pt.PrivilegeType_Code IN ('" . implode("','", $this->EvnPLDispDop13_model->getPersonPrivilegeCodeList($queryParams['PersonDopDisp_Year'] . '-01-01')) . "') and pp.Person_id = PS.Person_id and pp.PersonPrivilege_begDate <= cast(:PersonDopDisp_Year as varchar) + '-12-31' and (pp.PersonPrivilege_endDate >= cast(:PersonDopDisp_Year as varchar) + '-12-31' or pp.PersonPrivilege_endDate is null))) -- refs #23044
						{$add_filter}
					) and dbo.Age2(PS.Person_BirthDay, cast(:PersonDopDisp_Year as varchar) + '-12-31')<=99 
				";

				// https://redmine.swan.perm.ru/issues/20209
				// http://redmine.swan.perm.ru/issues/21510 убрать совсем этот контроль
				/* if ( in_array($data['session']['region']['nick'], array('perm')) ) {
					$filter .= "
						and not exists (select top 1 EvnPLDispDop_id from v_EvnPLDispDop (nolock) where Person_id = PS.Person_id and YEAR(EvnPLDispDop_setDate) IN (2011, 2012))
					";
				}*/

				if ( $data['PersonPeriodicType_id'] == 2 ) {
					$query .= " 
						left join [v_EvnPLDispDop13] [EPLDD13] with (nolock) on EPLDD13.Server_id = PS.Server_id and EPLDD13.PersonEvn_id = PS.PersonEvn_id and [EPLDD13].Lpu_id = :Lpu_id and ISNULL(DispClass_id,1) = 1 and YEAR(EvnPLDispDop13_setDate) = :PersonDopDisp_Year
					";
				}
				else {
					$query .= " 
						left join [v_EvnPLDispDop13] [EPLDD13] with (nolock) on [PS].[Person_id] = [EPLDD13].[Person_id] and [EPLDD13].Lpu_id = :Lpu_id and ISNULL(DispClass_id,1) = 1 and YEAR(EvnPLDispDop13_setDate) = :PersonDopDisp_Year
					";
				}
				
				$query .= " 
					left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = ISNULL([EPLDD13].[EvnPLDispDop13_IsEndStage], 1)
					left join v_HealthKind HK with (nolock) on HK.HealthKind_id = EPLDD13.HealthKind_id
					left join v_Address UAdd (nolock) on UAdd.Address_id = PS.UAddress_id
					left join v_Address PAdd (nolock) on PAdd.Address_id = PS.PAddress_id
					outer apply(
						select top 1
							DDIC.DopDispInfoConsent_IsAgree
						from
							v_DopDispInfoConsent DDIC (nolock) 
							left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
							left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
						where
							DDIC.EvnPLDisp_id = EPLDD13.EvnPLDispDop13_id
							and ST.SurveyType_Code = 1
					) DDICData
					outer apply(
						select top 1
							EPLDD13_SEC.EvnPLDispDop13_id,
							EPLDD13_SEC.EvnPLDispDop13_isPaid,
							EPLDD13_SEC.EvnPLDispDop13_setDate,
							EPLDD13_SEC.EvnPLDispDop13_disDate,
							EPLDD13_SEC.EvnPLDispDop13_consDT,
							EPLDD13_SEC.HealthKind_id,
							ISNULL(EPLDD13_SEC.EvnPLDispDop13_IsEndStage, 1) as EvnPLDispDop13_IsEndStage,
							HK_SEC.HealthKind_Name
						from
							v_EvnPLDispDop13 (nolock) EPLDD13_SEC
							left join v_HealthKind HK_SEC with (nolock) on HK_SEC.HealthKind_id = EPLDD13_SEC.HealthKind_id
						where
							EPLDD13_SEC.EvnPLDispDop13_fid = EPLDD13.EvnPLDispDop13_id
					) DopDispSecond -- данные по 2 этапу
					left join [YesNo] [IsFinishSecond] with (nolock) on [IsFinishSecond].[YesNo_id] = [DopDispSecond].[EvnPLDispDop13_IsEndStage]
					outer apply(
						select top 1
							DDIC.DopDispInfoConsent_IsAgree
						from
							v_DopDispInfoConsent DDIC (nolock) 
							left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
							left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
						where
							DDIC.EvnPLDisp_id = DopDispSecond.EvnPLDispDop13_id
							and ST.SurveyType_Code = 48
					) DDICDataSecond
				";
				
			break;

			case 'EvnPLDispProf':
				if ( isset($data['EvnPLDispProf_setDate']) ) {
					$filter .= " and [EPLDP].EvnPLDispProf_setDate = cast(:EvnPLDispProf_setDate as datetime) ";
					$queryParams['EvnPLDispProf_setDate'] = $data['EvnPLDispProf_setDate'];
				}
				if ( isset($data['EvnPLDispProf_setDate_Range'][0]) ) {
					$filter .= " and [EPLDP].EvnPLDispProf_setDate >= cast(:EvnPLDispProf_setDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispProf_setDate_Range_0'] = $data['EvnPLDispProf_setDate_Range'][0];
				}
				if ( isset($data['EvnPLDispProf_setDate_Range'][1]) ) {
					$filter .= " and [EPLDP].EvnPLDispProf_setDate <= cast(:EvnPLDispProf_setDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispProf_setDate_Range_1'] = $data['EvnPLDispProf_setDate_Range'][1];
				}
				if ( isset($data['EvnPLDispProf_disDate']) ) {
					$filter .= " and [EPLDP].EvnPLDispProf_disDate = cast(:EvnPLDispProf_disDate as datetime) ";
					$queryParams['EvnPLDispProf_disDate'] = $data['EvnPLDispProf_disDate'];
				}
				if ( isset($data['EvnPLDispProf_disDate_Range'][0]) ) {
					$filter .= " and [EPLDP].EvnPLDispProf_disDate >= cast(:EvnPLDispProf_disDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispProf_disDate_Range_0'] = $data['EvnPLDispProf_disDate_Range'][0];
				}
				if ( isset($data['EvnPLDispProf_disDate_Range'][1]) ) {
					$filter .= " and [EPLDP].EvnPLDispProf_disDate <= cast(:EvnPLDispProf_disDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispProf_disDate_Range_1'] = $data['EvnPLDispProf_disDate_Range'][1];
				}
				if ( isset($data['EvnPLDispProf_IsFinish']) ) {
					$filter .= " and isnull([EPLDP].EvnPLDispProf_IsEndStage, 1) = :EvnPLDispProf_IsFinish ";
					$queryParams['EvnPLDispProf_IsFinish'] = $data['EvnPLDispProf_IsFinish'];
				}
				if ( isset($data['EvnPLDispProf_isPaid']) ) {
					$filter .= " and isnull([EPLDP].EvnPLDispProf_isPaid, 1) = :EvnPLDispProf_isPaid ";
					$queryParams['EvnPLDispProf_isPaid'] = $data['EvnPLDispProf_isPaid'];
				}
				if ( isset($data['EvnPLDispProf_isMobile']) ) {
					$filter .= " and ISNULL([EPLDP].EvnPLDispProf_isMobile,1) = :EvnPLDispProf_isMobile ";
					$queryParams['EvnPLDispProf_isMobile'] = $data['EvnPLDispProf_isMobile'];
				}
				if ( isset($data['EvnPLDispProf_Cancel']) ) {
					if ($data['EvnPLDispProf_Cancel'] == 2) {
						$data['DopDispInfoConsent_IsAgree'] = 1;
					} else {
						$data['DopDispInfoConsent_IsAgree'] = 2;
					}
					$filter .= " and ISNULL(DDICData.DopDispInfoConsent_IsAgree,2) = :DopDispInfoConsent_IsAgree ";
					$queryParams['DopDispInfoConsent_IsAgree'] = $data['DopDispInfoConsent_IsAgree'];
				}
			
				if ( isset($data['EvnPLDispProf_HealthKind_id']) ) {
					$filter .= " and [EPLDP].HealthKind_id = :EvnPLDispProf_HealthKind_id ";
					$queryParams['EvnPLDispProf_HealthKind_id'] = $data['EvnPLDispProf_HealthKind_id'];
				}
				
				if ( isset($data['PersonDopDisp_Year']) ) {
					// $filter .= " and DD.PersonDopDisp_Year = :PersonDopDisp_Year ";
					$queryParams['PersonDopDisp_Year'] = $data['PersonDopDisp_Year'];
				}
				else {
					$queryParams['PersonDopDisp_Year'] = 2013;
				}

				$queryParams['Lpu_id'] = $data['Lpu_id'];

				if ( $data['PersonPeriodicType_id'] == 2 ) {
					$query .= " 
						inner join [v_EvnPLDispProf] [EPLDP] with (nolock) on EPLDP.Server_id = PS.Server_id and EPLDP.PersonEvn_id = PS.PersonEvn_id and [EPLDP].Lpu_id = :Lpu_id and YEAR(EvnPLDispProf_setDate) = :PersonDopDisp_Year
					";
				}
				else {
					$query .= " 
						inner join [v_EvnPLDispProf] [EPLDP] with (nolock) on [PS].[Person_id] = [EPLDP].[Person_id] and [EPLDP].Lpu_id = :Lpu_id and YEAR(EvnPLDispProf_setDate) = :PersonDopDisp_Year
					";
				}

				$query .= " 
					left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = ISNULL([EPLDP].[EvnPLDispProf_IsEndStage], 1)
					left join v_HealthKind HK with (nolock) on HK.HealthKind_id = EPLDP.HealthKind_id
					left join v_Address UAdd (nolock) on UAdd.Address_id = ps.UAddress_id
					left join v_Address PAdd (nolock) on PAdd.Address_id = ps.PAddress_id
					outer apply(
						select top 1
							DDIC.DopDispInfoConsent_IsAgree
						from
							v_DopDispInfoConsent DDIC (nolock) 
							left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
							left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
						where
							DDIC.EvnPLDisp_id = EPLDP.EvnPLDispProf_id
							and ST.SurveyType_Code = 49 -- профосмотр вцелом
					) DDICData
				";
				
			break;
			
			case 'EvnPLDispDop':
				if ( isset($data['EvnPLDispDop_setDate']) ) {
					$filter .= " and [EPLDD].EvnPLDispDop_setDate = cast(:EvnPLDispDop_setDate as datetime) ";
					$queryParams['EvnPLDispDop_setDate'] = $data['EvnPLDispDop_setDate'];
				}
				if ( isset($data['EvnPLDispDop_setDate_Range'][0]) ) {
					$filter .= " and [EPLDD].EvnPLDispDop_setDate >= cast(:EvnPLDispDop_setDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispDop_setDate_Range_0'] = $data['EvnPLDispDop_setDate_Range'][0];
				}
				if ( isset($data['EvnPLDispDop_setDate_Range'][1]) ) {
					$filter .= " and [EPLDD].EvnPLDispDop_setDate <= cast(:EvnPLDispDop_setDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispDop_setDate_Range_1'] = $data['EvnPLDispDop_setDate_Range'][1];
				}
				if ( isset($data['EvnPLDispDop_disDate']) ) {
					$filter .= " and [EPLDD].EvnPLDispDop_disDate = cast(:EvnPLDispDop_disDate as datetime) ";
					$queryParams['EvnPLDispDop_disDate'] = $data['EvnPLDispDop_disDate'];
				}
				if ( isset($data['EvnPLDispDop_disDate_Range'][0]) ) {
					$filter .= " and [EPLDD].EvnPLDispDop_disDate >= cast(:EvnPLDispDop_disDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispDop_disDate_Range_0'] = $data['EvnPLDispDop_disDate_Range'][0];
				}
				if ( isset($data['EvnPLDispDop_disDate_Range'][1]) ) {
					$filter .= " and [EPLDD].EvnPLDispDop_disDate <= cast(:EvnPLDispDop_disDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispDop_disDate_Range_1'] = $data['EvnPLDispDop_disDate_Range'][1];
				}
				if ( isset($data['EvnPLDispDop_VizitCount']) ) {
					$filter .= " and [EPLDD].EvnPLDispDop_VizitCount = :EvnPLDispDop_VizitCount ";
					$queryParams['EvnPLDispDop_VizitCount'] = $data['EvnPLDispDop_VizitCount'];
				}
				if ( isset($data['EvnPLDispDop_VizitCount_From']) ) {
					$filter .= " and [EPLDD].EvnPLDispDop_VizitCount >= :EvnPLDispDop_VizitCount_From ";
					$queryParams['EvnPLDispDop_VizitCount_From'] = $data['EvnPLDispDop_VizitCount_From'];
				}
				if ( isset($data['EvnPLDispDop_VizitCount_To']) ) {
					$filter .= " and [EPLDD].EvnPLDispDop_VizitCount <= :EvnPLDispDop_VizitCount_To ";
					$queryParams['EvnPLDispDop_VizitCount_To'] = $data['EvnPLDispDop_VizitCount_To'];
				}
				if ( isset($data['EvnPLDispDop_IsFinish']) ) {
					$filter .= " and isnull([EPLDD].EvnPLDispDop_IsFinish, 1) = :EvnPLDispDop_IsFinish ";
					$queryParams['EvnPLDispDop_IsFinish'] = $data['EvnPLDispDop_IsFinish'];
				}				
				$queryParams['Lpu_id'] = $data['Lpu_id'];

				if ( $data['PersonPeriodicType_id'] == 2 ) {
					$query .= " 
						inner join [v_EvnPLDispDop] [EPLDD] with (nolock) on EPLDD.Server_id = PS.Server_id and EPLDD.PersonEvn_id = PS.PersonEvn_id and [EPLDD].Lpu_id = :Lpu_id
					";
				}
				else {
					$query .= " 
						inner join [v_EvnPLDispDop] [EPLDD] with (nolock) on [PS].[Person_id] = [EPLDD].[Person_id] and [EPLDD].Lpu_id = :Lpu_id
					";
				}
				// группа здоровья
				if ( isset($data['EvnPLDispDop_HealthKind_id']) ) {
					
					$queryParams['EvnPLDispDop_HealthKind_id'] = $data['EvnPLDispDop_HealthKind_id'];
					$query .= " 
						inner join [v_EvnVizitDispDop] [EVPLDD] with (nolock) on [EVPLDD].[EvnVizitDispDop_pid] = [EPLDD].[EvnPLDispDop_id] and isnull([EPLDD].EvnPLDispDop_IsFinish, 1) = 2 and [EVPLDD].[DopDispSpec_id] = 1 and [EVPLDD].[HealthKind_id] = :EvnPLDispDop_HealthKind_id and EVPLDD.Lpu_id = :Lpu_id
					";
				}				

				$query .= " 
					left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = [EPLDD].[EvnPLDispDop_IsFinish]
				";
				/*
				$query .= " 
					inner join [v_EvnPLDispDop] [EPLDD] on [PS].[Person_id] = [EPLDD].[Person_id] and [EPLDD].Lpu_id = :Lpu_id
					left join [YesNo] [IsFinish] on [IsFinish].[YesNo_id] = [EPLDD].[EvnPLDispDop_IsFinish]
				";
				*/
			break;
			
			case 'EvnPLDispTeen14':
				if ( isset($data['EvnPLDispTeen14_setDate']) ) {
					$filter .= " and [EPLDT14].EvnPLDispTeen14_setDate = cast(:EvnPLDispTeen14_setDate as datetime) ";
					$queryParams['EvnPLDispTeen14_setDate'] = $data['EvnPLDispTeen14_setDate'];
				}
				if ( isset($data['EvnPLDispTeen14_setDate_Range'][0]) ) {
					$filter .= " and [EPLDT14].EvnPLDispTeen14_setDate >= cast(:EvnPLDispTeen14_setDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispTeen14_setDate_Range_0'] = $data['EvnPLDispTeen14_setDate_Range'][0];
				}
				if ( isset($data['EvnPLDispTeen14_setDate_Range'][1]) ) {
					$filter .= " and [EPLDT14].EvnPLDispTeen14_setDate <= cast(:EvnPLDispTeen14_setDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispTeen14_setDate_Range_1'] = $data['EvnPLDispTeen14_setDate_Range'][1];
				}
				if ( isset($data['EvnPLDispTeen14_disDate']) ) {
					$filter .= " and [EPLDT14].EvnPLDispTeen14_disDate = cast(:EvnPLDispTeen14_disDate as datetime) ";
					$queryParams['EvnPLDispTeen14_disDate'] = $data['EvnPLDispTeen14_disDate'];
				}
				if ( isset($data['EvnPLDispTeen14_disDate_Range'][0]) ) {
					$filter .= " and [EPLDT14].EvnPLDispTeen14_disDate >= cast(:EvnPLDispTeen14_disDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispTeen14_disDate_Range_0'] = $data['EvnPLDispTeen14_disDate_Range'][0];
				}
				if ( isset($data['EvnPLDispTeen14_disDate_Range'][1]) ) {
					$filter .= " and [EPLDT14].EvnPLDispTeen14_disDate <= cast(:EvnPLDispTeen14_disDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispTeen14_disDate_Range_1'] = $data['EvnPLDispTeen14_disDate_Range'][1];
				}
				if ( isset($data['EvnPLDispTeen14_VizitCount']) ) {
					$filter .= " and [EPLDT14].EvnPLDispTeen14_VizitCount = :EvnPLDispTeen14_VizitCount ";
					$queryParams['EvnPLDispTeen14_VizitCount'] = $data['EvnPLDispTeen14_VizitCount'];
				}
				if ( isset($data['EvnPLDispTeen14_VizitCount_From']) ) {
					$filter .= " and [EPLDT14].EvnPLDispTeen14_VizitCount >= :EvnPLDispTeen14_VizitCount_From ";
					$queryParams['EvnPLDispTeen14_VizitCount_From'] = $data['EvnPLDispTeen14_VizitCount_From'];
				}
				if ( isset($data['EvnPLDispTeen14_VizitCount_To']) ) {
					$filter .= " and [EPLDT14].EvnPLDispTeen14_VizitCount <= :EvnPLDispTeen14_VizitCount_To ";
					$queryParams['EvnPLDispTeen14_VizitCount_To'] = $data['EvnPLDispTeen14_VizitCount_To'];
				}
				if ( isset($data['EvnPLDispTeen14_IsFinish']) ) {
					$filter .= " and isnull([EPLDT14].EvnPLDispTeen14_IsFinish, 1) = :EvnPLDispTeen14_IsFinish ";
					$queryParams['EvnPLDispTeen14_IsFinish'] = $data['EvnPLDispTeen14_IsFinish'];
				}				
				$queryParams['Lpu_id'] = $data['Lpu_id'];

				if ( $data['PersonPeriodicType_id'] == 2 ) {
					$query .= " 
						inner join [v_EvnPLDispTeen14] [EPLDT14] with (nolock) on EPLDT14.Server_id = PS.Server_id and EPLDT14.PersonEvn_id = PS.PersonEvn_id and [EPLDT14].Lpu_id = :Lpu_id
					";
				}
				else {
					$query .= " 
						inner join [v_EvnPLDispTeen14] [EPLDT14] with (nolock) on [PS].[Person_id] = [EPLDT14].[Person_id] and [EPLDT14].Lpu_id = :Lpu_id
					";
				}
				// группа здоровья
				if ( isset($data['EvnPLDispTeen14_HealthKind_id']) ) {
					
					$queryParams['EvnPLDispTeen14_HealthKind_id'] = $data['EvnPLDispTeen14_HealthKind_id'];
					$query .= " 
						inner join [v_EvnVizitDispTeen14] [EVPLDT14] with (nolock) on [EVPLDT14].[EvnVizitDispTeen14_pid] = [EPLDT14].[EvnPLDispTeen14_id] and isnull([EPLDT14].EvnPLDispTeen14_IsFinish, 1) = 2 and [EVPLDT14].[Teen14DispSpecType_id] = 1 and [EVPLDT14].[HealthKind_id] = :EvnPLDispTeen14_HealthKind_id and EVPLDT14.Lpu_id = :Lpu_id
					";
				}				

				$query .= " 
					left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = [EPLDT14].[EvnPLDispTeen14_IsFinish]
				";
				/*
				$query .= " 
					inner join [v_EvnPLDispTeen14] [EPLDT14] on [PS].[Person_id] = [EPLDT14].[Person_id] and [EPLDT14].Lpu_id = :Lpu_id
					left join [YesNo] [IsFinish] on [IsFinish].[YesNo_id] = [EPLDT14].[EvnPLDispTeen14_IsFinish]
				";
				*/
			break;
			
			case 'EvnPLDispOrp':
				if ( isset($data['EvnPLDispOrp_setDate']) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_setDate = cast(:EvnPLDispOrp_setDate as datetime) ";
					$queryParams['EvnPLDispOrp_setDate'] = $data['EvnPLDispOrp_setDate'];
				}
				if ( isset($data['EvnPLDispOrp_setDate_Range'][0]) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_setDate >= cast(:EvnPLDispOrp_setDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispOrp_setDate_Range_0'] = $data['EvnPLDispOrp_setDate_Range'][0];
				}
				if ( isset($data['EvnPLDispOrp_setDate_Range'][1]) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_setDate <= cast(:EvnPLDispOrp_setDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispOrp_setDate_Range_1'] = $data['EvnPLDispOrp_setDate_Range'][1];
				}
				if ( isset($data['EvnPLDispOrp_disDate']) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_disDate = cast(:EvnPLDispOrp_disDate as datetime) ";
					$queryParams['EvnPLDispOrp_disDate'] = $data['EvnPLDispOrp_disDate'];
				}
				if ( isset($data['EvnPLDispOrp_disDate_Range'][0]) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_disDate >= cast(:EvnPLDispOrp_disDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispOrp_disDate_Range_0'] = $data['EvnPLDispOrp_disDate_Range'][0];
				}
				if ( isset($data['EvnPLDispOrp_disDate_Range'][1]) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_disDate <= cast(:EvnPLDispOrp_disDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispOrp_disDate_Range_1'] = $data['EvnPLDispOrp_disDate_Range'][1];
				}
				if ( isset($data['EvnPLDispOrp_VizitCount']) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_VizitCount = :EvnPLDispOrp_VizitCount ";
					$queryParams['EvnPLDispOrp_VizitCount'] = $data['EvnPLDispOrp_VizitCount'];
				}
				if ( isset($data['EvnPLDispOrp_VizitCount_From']) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_VizitCount >= :EvnPLDispOrp_VizitCount_From ";
					$queryParams['EvnPLDispOrp_VizitCount_From'] = $data['EvnPLDispOrp_VizitCount_From'];
				}
				if ( isset($data['EvnPLDispOrp_VizitCount_To']) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_VizitCount <= :EvnPLDispOrp_VizitCount_To ";
					$queryParams['EvnPLDispOrp_VizitCount_To'] = $data['EvnPLDispOrp_VizitCount_To'];
				}
				if ( isset($data['EvnPLDispOrp_IsFinish']) ) {
					$filter .= " and isnull([EPLDO].EvnPLDispOrp_IsFinish, 1) = :EvnPLDispOrp_IsFinish ";
					$queryParams['EvnPLDispOrp_IsFinish'] = $data['EvnPLDispOrp_IsFinish'];
				}
				if ( isset($data['EvnPLDispOrp_isPaid']) ) {
					$filter .= " and isnull([EPLDO].EvnPLDispOrp_isPaid, 1) = :EvnPLDispOrp_isPaid ";
					$queryParams['EvnPLDispOrp_isPaid'] = $data['EvnPLDispOrp_isPaid'];
				}
				if ( isset($data['EvnPLDispOrp_isMobile']) ) {
					$filter .= " and ISNULL([EPLDO].EvnPLDispOrp_isMobile,1) = :EvnPLDispOrp_isMobile ";
					$queryParams['EvnPLDispOrp_isMobile'] = $data['EvnPLDispOrp_isMobile'];
				}
				if ( isset($data['EvnPLDispOrp_IsTwoStage']) ) {
					$filter .= " and isnull([EPLDO].EvnPLDispOrp_IsTwoStage, 1) = :EvnPLDispOrp_IsTwoStage ";
					$queryParams['EvnPLDispOrp_IsTwoStage'] = $data['EvnPLDispOrp_IsTwoStage'];
				}				
				$queryParams['Lpu_id'] = $data['Lpu_id'];

				if ( $data['PersonPeriodicType_id'] == 2 ) {
					$query .= " 
						inner join [v_EvnPLDispOrp] [EPLDO] with (nolock) on EPLDO.Server_id = PS.Server_id and EPLDO.PersonEvn_id = PS.PersonEvn_id and [EPLDO].Lpu_id = :Lpu_id and [EPLDO].DispClass_id IN (3,7)
					";
				}
				else {
					$query .= " 
						inner join [v_EvnPLDispOrp] [EPLDO] with (nolock) on [PS].[Person_id] = [EPLDO].[Person_id] and [EPLDO].Lpu_id = :Lpu_id and [EPLDO].DispClass_id IN (3,7)
					";
				}
				// группа здоровья
				if ( isset($data['EvnPLDispOrp_HealthKind_id']) ) {
					
					$queryParams['EvnPLDispOrp_HealthKind_id'] = $data['EvnPLDispOrp_HealthKind_id'];
					$query .= " 
						inner join [v_EvnVizitDispDop] [EVPLDD] with (nolock) on [EVPLDD].[EvnVizitDispDop_pid] = [EPLDO].[EvnPLDispOrp_id] and isnull([EPLDO].EvnPLDispOrp_IsFinish, 1) = 2 and [EVPLDD].[DopDispSpec_id] = 1 and [EVPLDD].[HealthKind_id] = :EvnPLDispOrp_HealthKind_id and EVPLDD.Lpu_id = :Lpu_id
					";
				}				

				$query .= " 
					left join v_Sex Sex with (nolock) on Sex.Sex_id = PS.Sex_id
					left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = [EPLDO].[EvnPLDispOrp_IsFinish]
					left join [YesNo] [IsTwoStage] with (nolock) on [IsTwoStage].[YesNo_id] = [EPLDO].[EvnPLDispOrp_IsTwoStage]
				";
			break;
			
			case 'EvnPLDispOrpOld':
				if ( isset($data['EvnPLDispOrp_setDate']) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_setDate = cast(:EvnPLDispOrp_setDate as datetime) ";
					$queryParams['EvnPLDispOrp_setDate'] = $data['EvnPLDispOrp_setDate'];
				}
				if ( isset($data['EvnPLDispOrp_setDate_Range'][0]) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_setDate >= cast(:EvnPLDispOrp_setDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispOrp_setDate_Range_0'] = $data['EvnPLDispOrp_setDate_Range'][0];
				}
				if ( isset($data['EvnPLDispOrp_setDate_Range'][1]) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_setDate <= cast(:EvnPLDispOrp_setDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispOrp_setDate_Range_1'] = $data['EvnPLDispOrp_setDate_Range'][1];
				}
				if ( isset($data['EvnPLDispOrp_disDate']) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_disDate = cast(:EvnPLDispOrp_disDate as datetime) ";
					$queryParams['EvnPLDispOrp_disDate'] = $data['EvnPLDispOrp_disDate'];
				}
				if ( isset($data['EvnPLDispOrp_disDate_Range'][0]) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_disDate >= cast(:EvnPLDispOrp_disDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispOrp_disDate_Range_0'] = $data['EvnPLDispOrp_disDate_Range'][0];
				}
				if ( isset($data['EvnPLDispOrp_disDate_Range'][1]) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_disDate <= cast(:EvnPLDispOrp_disDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispOrp_disDate_Range_1'] = $data['EvnPLDispOrp_disDate_Range'][1];
				}
				if ( isset($data['EvnPLDispOrp_VizitCount']) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_VizitCount = :EvnPLDispOrp_VizitCount ";
					$queryParams['EvnPLDispOrp_VizitCount'] = $data['EvnPLDispOrp_VizitCount'];
				}
				if ( isset($data['EvnPLDispOrp_VizitCount_From']) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_VizitCount >= :EvnPLDispOrp_VizitCount_From ";
					$queryParams['EvnPLDispOrp_VizitCount_From'] = $data['EvnPLDispOrp_VizitCount_From'];
				}
				if ( isset($data['EvnPLDispOrp_VizitCount_To']) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_VizitCount <= :EvnPLDispOrp_VizitCount_To ";
					$queryParams['EvnPLDispOrp_VizitCount_To'] = $data['EvnPLDispOrp_VizitCount_To'];
				}
				if ( isset($data['EvnPLDispOrp_IsFinish']) ) {
					$filter .= " and isnull([EPLDO].EvnPLDispOrp_IsFinish, 1) = :EvnPLDispOrp_IsFinish ";
					$queryParams['EvnPLDispOrp_IsFinish'] = $data['EvnPLDispOrp_IsFinish'];
				}				
				$queryParams['Lpu_id'] = $data['Lpu_id'];

				if ( $data['PersonPeriodicType_id'] == 2 ) {
					$query .= " 
						inner join [v_EvnPLDispOrp] [EPLDO] with (nolock) on EPLDO.Server_id = PS.Server_id and EPLDO.PersonEvn_id = PS.PersonEvn_id and [EPLDO].Lpu_id = :Lpu_id
					";
				}
				else {
					$query .= " 
						inner join [v_EvnPLDispOrp] [EPLDO] with (nolock) on [PS].[Person_id] = [EPLDO].[Person_id] and [EPLDO].Lpu_id = :Lpu_id
					";
				}
				// группа здоровья
				if ( isset($data['EvnPLDispOrp_HealthKind_id']) ) {
					
					$queryParams['EvnPLDispOrp_HealthKind_id'] = $data['EvnPLDispOrp_HealthKind_id'];
					$query .= " 
						inner join [v_EvnVizitDispDop] [EVPLDD] with (nolock) on [EVPLDD].[EvnVizitDispDop_pid] = [EPLDO].[EvnPLDispOrp_id] and isnull([EPLDO].EvnPLDispOrp_IsFinish, 1) = 2 and [EVPLDD].[DopDispSpec_id] = 1 and [EVPLDD].[HealthKind_id] = :EvnPLDispOrp_HealthKind_id and EVPLDD.Lpu_id = :Lpu_id
					";
				}				

				$query .= " 
					left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = [EPLDO].[EvnPLDispOrp_IsFinish]
				";
			break;
			
			case 'EvnPLDispOrpSec':
				if ( isset($data['EvnPLDispOrp_setDate']) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_setDate = cast(:EvnPLDispOrp_setDate as datetime) ";
					$queryParams['EvnPLDispOrp_setDate'] = $data['EvnPLDispOrp_setDate'];
				}
				if ( isset($data['EvnPLDispOrp_setDate_Range'][0]) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_setDate >= cast(:EvnPLDispOrp_setDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispOrp_setDate_Range_0'] = $data['EvnPLDispOrp_setDate_Range'][0];
				}
				if ( isset($data['EvnPLDispOrp_setDate_Range'][1]) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_setDate <= cast(:EvnPLDispOrp_setDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispOrp_setDate_Range_1'] = $data['EvnPLDispOrp_setDate_Range'][1];
				}
				if ( isset($data['EvnPLDispOrp_disDate']) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_disDate = cast(:EvnPLDispOrp_disDate as datetime) ";
					$queryParams['EvnPLDispOrp_disDate'] = $data['EvnPLDispOrp_disDate'];
				}
				if ( isset($data['EvnPLDispOrp_disDate_Range'][0]) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_disDate >= cast(:EvnPLDispOrp_disDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispOrp_disDate_Range_0'] = $data['EvnPLDispOrp_disDate_Range'][0];
				}
				if ( isset($data['EvnPLDispOrp_disDate_Range'][1]) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_disDate <= cast(:EvnPLDispOrp_disDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispOrp_disDate_Range_1'] = $data['EvnPLDispOrp_disDate_Range'][1];
				}
				if ( isset($data['EvnPLDispOrp_VizitCount']) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_VizitCount = :EvnPLDispOrp_VizitCount ";
					$queryParams['EvnPLDispOrp_VizitCount'] = $data['EvnPLDispOrp_VizitCount'];
				}
				if ( isset($data['EvnPLDispOrp_VizitCount_From']) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_VizitCount >= :EvnPLDispOrp_VizitCount_From ";
					$queryParams['EvnPLDispOrp_VizitCount_From'] = $data['EvnPLDispOrp_VizitCount_From'];
				}
				if ( isset($data['EvnPLDispOrp_VizitCount_To']) ) {
					$filter .= " and [EPLDO].EvnPLDispOrp_VizitCount <= :EvnPLDispOrp_VizitCount_To ";
					$queryParams['EvnPLDispOrp_VizitCount_To'] = $data['EvnPLDispOrp_VizitCount_To'];
				}
				if ( isset($data['EvnPLDispOrp_IsFinish']) ) {
					$filter .= " and isnull([EPLDO].EvnPLDispOrp_IsFinish, 1) = :EvnPLDispOrp_IsFinish ";
					$queryParams['EvnPLDispOrp_IsFinish'] = $data['EvnPLDispOrp_IsFinish'];
				}
				if ( isset($data['EvnPLDispOrp_isPaid']) ) {
					$filter .= " and isnull([EPLDO].EvnPLDispOrp_isPaid, 1) = :EvnPLDispOrp_isPaid ";
					$queryParams['EvnPLDispOrp_isPaid'] = $data['EvnPLDispOrp_isPaid'];
				}
				if ( isset($data['EvnPLDispOrp_IsTwoStage']) ) {
					$filter .= " and isnull([EPLDO].EvnPLDispOrp_IsTwoStage, 1) = :EvnPLDispOrp_IsTwoStage ";
					$queryParams['EvnPLDispOrp_IsTwoStage'] = $data['EvnPLDispOrp_IsTwoStage'];
				}				
				$queryParams['Lpu_id'] = $data['Lpu_id'];

				if ( $data['PersonPeriodicType_id'] == 2 ) {
					$query .= " 
						inner join [v_EvnPLDispOrp] [EPLDO] with (nolock) on EPLDO.Server_id = PS.Server_id and EPLDO.PersonEvn_id = PS.PersonEvn_id and [EPLDO].Lpu_id = :Lpu_id and [EPLDO].DispClass_id IN (4,8)
					";
				}
				else {
					$query .= " 
						inner join [v_EvnPLDispOrp] [EPLDO] with (nolock) on [PS].[Person_id] = [EPLDO].[Person_id] and [EPLDO].Lpu_id = :Lpu_id and [EPLDO].DispClass_id IN (4,8)
					";
				}
				// группа здоровья
				if ( isset($data['EvnPLDispOrp_HealthKind_id']) ) {
					
					$queryParams['EvnPLDispOrp_HealthKind_id'] = $data['EvnPLDispOrp_HealthKind_id'];
					$query .= " 
						inner join [v_EvnVizitDispDop] [EVPLDD] with (nolock) on [EVPLDD].[EvnVizitDispDop_pid] = [EPLDO].[EvnPLDispOrp_id] and isnull([EPLDO].EvnPLDispOrp_IsFinish, 1) = 2 and [EVPLDD].[DopDispSpec_id] = 1 and [EVPLDD].[HealthKind_id] = :EvnPLDispOrp_HealthKind_id and EVPLDD.Lpu_id = :Lpu_id
					";
				}				

				$query .= "
					left join v_Sex Sex with (nolock) on Sex.Sex_id = PS.Sex_id
					left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = [EPLDO].[EvnPLDispOrp_IsFinish]
					left join [YesNo] [IsTwoStage] with (nolock) on [IsTwoStage].[YesNo_id] = [EPLDO].[EvnPLDispOrp_IsTwoStage]
				";
			break;
			
			case 'EvnPLDispTeenInspectionPeriod':
			case 'EvnPLDispTeenInspectionProf':
			case 'EvnPLDispTeenInspectionPred':
				if ( isset($data['EvnPLDispTeenInspection_setDate']) ) {
					$filter .= " and [EPLDTI].EvnPLDispTeenInspection_setDate = cast(:EvnPLDispTeenInspection_setDate as datetime) ";
					$queryParams['EvnPLDispTeenInspection_setDate'] = $data['EvnPLDispTeenInspection_setDate'];
				}
				if ( isset($data['EvnPLDispTeenInspection_setDate_Range'][0]) ) {
					$filter .= " and [EPLDTI].EvnPLDispTeenInspection_setDate >= cast(:EvnPLDispTeenInspection_setDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispTeenInspection_setDate_Range_0'] = $data['EvnPLDispTeenInspection_setDate_Range'][0];
				}
				if ( isset($data['EvnPLDispTeenInspection_setDate_Range'][1]) ) {
					$filter .= " and [EPLDTI].EvnPLDispTeenInspection_setDate <= cast(:EvnPLDispTeenInspection_setDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispTeenInspection_setDate_Range_1'] = $data['EvnPLDispTeenInspection_setDate_Range'][1];
				}
				if ( isset($data['EvnPLDispTeenInspection_disDate']) ) {
					$filter .= " and [EPLDTI].EvnPLDispTeenInspection_disDate = cast(:EvnPLDispTeenInspection_disDate as datetime) ";
					$queryParams['EvnPLDispTeenInspection_disDate'] = $data['EvnPLDispTeenInspection_disDate'];
				}
				if ( isset($data['EvnPLDispTeenInspection_disDate_Range'][0]) ) {
					$filter .= " and [EPLDTI].EvnPLDispTeenInspection_disDate >= cast(:EvnPLDispTeenInspection_disDate_Range_0 as datetime) ";
					$queryParams['EvnPLDispTeenInspection_disDate_Range_0'] = $data['EvnPLDispTeenInspection_disDate_Range'][0];
				}
				if ( isset($data['EvnPLDispTeenInspection_disDate_Range'][1]) ) {
					$filter .= " and [EPLDTI].EvnPLDispTeenInspection_disDate <= cast(:EvnPLDispTeenInspection_disDate_Range_1 as datetime) ";
					$queryParams['EvnPLDispTeenInspection_disDate_Range_1'] = $data['EvnPLDispTeenInspection_disDate_Range'][1];
				}
				if ( isset($data['EvnPLDispTeenInspection_IsFinish']) ) {
					$filter .= " and isnull([EPLDTI].EvnPLDispTeenInspection_IsFinish, 1) = :EvnPLDispTeenInspection_IsFinish ";
					$queryParams['EvnPLDispTeenInspection_IsFinish'] = $data['EvnPLDispTeenInspection_IsFinish'];
				}
				if ( isset($data['EvnPLDispTeenInspection_isMobile']) ) {
					$filter .= " and isnull([EPLDTI].EvnPLDispTeenInspection_isMobile, 1) = :EvnPLDispTeenInspection_isMobile ";
					$queryParams['EvnPLDispTeenInspection_isMobile'] = $data['EvnPLDispTeenInspection_isMobile'];
				}
				if ( isset($data['EvnPLDispTeenInspection_IsTwoStage']) ) {
					$filter .= " and isnull([EPLDTI].EvnPLDispTeenInspection_IsTwoStage, 1) = :EvnPLDispTeenInspection_IsTwoStage ";
					$queryParams['EvnPLDispTeenInspection_IsTwoStage'] = $data['EvnPLDispTeenInspection_IsTwoStage'];
				}
				if ( isset($data['AgeGroupDisp_id']) ) {
					$filter .= " and isnull([EPLDTI].AgeGroupDisp_id, 0) = :AgeGroupDisp_id ";
					$queryParams['AgeGroupDisp_id'] = $data['AgeGroupDisp_id'];
				}
				if ( isset($data['DispClass_id']) ) {
					$filter .= " and isnull([EPLDTI].DispClass_id, 6) = :DispClass_id ";
					$queryParams['DispClass_id'] = $data['DispClass_id'];
				}
				$queryParams['Lpu_id'] = $data['Lpu_id'];

				if ( $data['PersonPeriodicType_id'] == 2 ) {
					$query .= " 
						inner join [v_EvnPLDispTeenInspection] [EPLDTI] with (nolock) on EPLDTI.Server_id = PS.Server_id and EPLDTI.PersonEvn_id = PS.PersonEvn_id and [EPLDTI].Lpu_id = :Lpu_id
					";
				}
				else {
					$query .= " 
						inner join [v_EvnPLDispTeenInspection] [EPLDTI] with (nolock) on [PS].[Person_id] = [EPLDTI].[Person_id] and [EPLDTI].Lpu_id = :Lpu_id
					";
				}
				
				$query .= " 
					left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = ISNULL([EPLDTI].[EvnPLDispTeenInspection_IsFinish],1)
					left join [YesNo] [IsTwoStage] with (nolock) on [IsTwoStage].[YesNo_id] = ISNULL([EPLDTI].[EvnPLDispTeenInspection_IsTwoStage],1)
					left join v_Sex Sex with (nolock) on Sex.Sex_id = PS.Sex_id
					left join v_Address UAdd (nolock) on UAdd.Address_id = ps.UAddress_id
					left join v_Address PAdd (nolock) on PAdd.Address_id = ps.PAddress_id
					left join v_PersonDispOrp PDORP (nolock) on PDORP.PersonDispOrp_id = EPLDTI.PersonDispOrp_id
					left join v_AgeGroupDisp AGD (nolock) on AGD.AgeGroupDisp_id = ISNULL(EPLDTI.AgeGroupDisp_id, PDORP.AgeGroupDisp_id)
				";
			break;
			
			
			case 'EvnPLDispDopStream':
				$filter .= "
					and [EPLDD].EvnPLDispDop_updDT >= :EvnPLDispDop_date_time and [EPLDD].pmUser_updID = :pmUser_id 
				";
				$queryParams['EvnPLDispDop_date_time'] = $data['EvnPLDispDopStream_begDate']." ".$data['EvnPLDispDopStream_begTime'];
				$queryParams['pmUser_id'] = $data['pmUser_id'];
				$queryParams['Lpu_id'] = $data['Lpu_id'];

				if ( $data['PersonPeriodicType_id'] == 2 ) {
					$query .= " 
						inner join [v_EvnPLDispDop] [EPLDD] with (nolock) on EPLDD.Server_id = PS.Server_id and EPLDD.PersonEvn_id = PS.PersonEvn_id and [EPLDD].Lpu_id = :Lpu_id
					";
				}
				else {
					$query .= " 
						inner join [v_EvnPLDispDop] [EPLDD] with (nolock) on [PS].[Person_id] = [EPLDD].[Person_id] and [EPLDD].Lpu_id = :Lpu_id
					";
				}

				$query .= " 
					left join [YesNo] [IsFinish]  with (nolock) on [IsFinish].[YesNo_id] = [EPLDD].[EvnPLDispDop_IsFinish]
				";
				/*
				$query .= " 
					inner join [v_EvnPLDispDop] [EPLDD] on [PS].[Person_id] = [EPLDD].[Person_id] and Lpu_id = :Lpu_id
					left join [YesNo] [IsFinish] on [IsFinish].[YesNo_id] = [EPLDD].[EvnPLDispDop_IsFinish]
				";
				*/
			break;
			
			case 'EvnPLDispTeen14Stream':
				$filter .= "
					and [EPLDT14].EvnPLDispTeen14_updDT >= :EvnPLDispTeen14_date_time and [EPLDT14].pmUser_updID = :pmUser_id 
				";
				$queryParams['EvnPLDispTeen14_date_time'] = $data['EvnPLDispTeen14Stream_begDate']." ".$data['EvnPLDispTeen14Stream_begTime'];
				$queryParams['pmUser_id'] = $data['pmUser_id'];
				$queryParams['Lpu_id'] = $data['Lpu_id'];

				if ( $data['PersonPeriodicType_id'] == 2 ) {
					$query .= " 
						inner join [v_EvnPLDispTeen14] [EPLDT14] with (nolock) on EPLDT14.Server_id = PS.Server_id and EPLDT14.PersonEvn_id = PS.PersonEvn_id and [EPLDT14].Lpu_id = :Lpu_id
					";
				}
				else {
					$query .= " 
						inner join [v_EvnPLDispTeen14] [EPLDT14] with (nolock) on [PS].[Person_id] = [EPLDT14].[Person_id] and [EPLDT14].Lpu_id = :Lpu_id
					";
				}

				$query .= " 
					left join [YesNo] [IsFinish]  with (nolock) on [IsFinish].[YesNo_id] = [EPLDT14].[EvnPLDispTeen14_IsFinish]
				";
				/*
				$query .= " 
					inner join [v_EvnPLDispTeen14] [EPLDT14] on [PS].[Person_id] = [EPLDT14].[Person_id] and Lpu_id = :Lpu_id
					left join [YesNo] [IsFinish] on [IsFinish].[YesNo_id] = [EPLDT14].[EvnPLDispTeen14_IsFinish]
				";
				*/
			break;
			
			case 'EvnPLDispOrpStream':
				$filter .= "
					and [EPLDO].EvnPLDispOrp_updDT >= :EvnPLDispOrp_date_time and [EPLDO].pmUser_updID = :pmUser_id 
				";
				$queryParams['EvnPLDispOrp_date_time'] = $data['EvnPLDispOrpStream_begDate']." ".$data['EvnPLDispOrpStream_begTime'];
				$queryParams['pmUser_id'] = $data['pmUser_id'];
				$queryParams['Lpu_id'] = $data['Lpu_id'];

				if ( $data['PersonPeriodicType_id'] == 2 ) {
					$query .= " 
						inner join [v_EvnPLDispOrp] [EPLDO] with (nolock) on EPLDO.Server_id = PS.Server_id and EPLDO.PersonEvn_id = PS.PersonEvn_id and [EPLDO].Lpu_id = :Lpu_id
					";
				}
				else {
					$query .= " 
						inner join [v_EvnPLDispOrp] [EPLDO] with (nolock) on [PS].[Person_id] = [EPLDO].[Person_id] and [EPLDO].Lpu_id = :Lpu_id
					";
				}

				$query .= " 
					left join [YesNo] [IsFinish]  with (nolock) on [IsFinish].[YesNo_id] = [EPLDO].[EvnPLDispOrp_IsFinish]
					left join [YesNo] [IsTwoStage]  with (nolock) on [IsTwoStage].[YesNo_id] = [EPLDO].[EvnPLDispOrp_IsTwoStage]
				";
			break;
					
			// для выгрузки данных в dbf, используется контроллером Search.php, методом exportSearchResultsToDbf. Для подгрузки посещений талона, оставлена обработка фильтров.
			case 'EvnAgg':
			case 'EvnPL':
			case 'EvnUsluga':
			case 'EvnVizitPL':
				if ( $data['PersonPeriodicType_id'] == 2 ) {
					if ( 'EvnVizitPL' == $data['SearchFormType'])
					{
						$query .= " inner join v_EvnVizitPL EVizitPL with (nolock) on EVizitPL.Server_id = PS.Server_id and EVizitPL.PersonEvn_id = PS.PersonEvn_id and EVizitPL.Lpu_id = :Lpu_id";
						$query .= " inner join v_EvnPL EPL with (nolock) on EPL.EvnPL_id=EVizitPL.EvnVizitPL_pid and EPL.Lpu_id = :Lpu_id and EPL.EvnClass_id = 3";
					}
					else
					{
						$query .= " inner join v_EvnPL EPL with (nolock) on EPL.Server_id = PS.Server_id and EPL.PersonEvn_id = PS.PersonEvn_id and EPL.Lpu_id = :Lpu_id and EPL.EvnClass_id = 3";
					}
				}
				else {
					if ( 'EvnVizitPL' == $data['SearchFormType'])
					{
						$query .= " inner join v_EvnVizitPL EVizitPL with (nolock) on EVizitPL.Person_id = PS.Person_id and EVizitPL.Lpu_id = :Lpu_id";
						$query .= " inner join v_EvnPL EPL with (nolock) on EPL.EvnPL_id=EVizitPL.EvnVizitPL_pid and EPL.Lpu_id = :Lpu_id and EPL.EvnClass_id = 3";
					}
					else
					{
						$query .= " inner join v_EvnPL EPL with (nolock) on EPL.Person_id = PS.Person_id and EPL.Lpu_id = :Lpu_id and EPL.EvnClass_id = 3";
					}
				}

				// $query .= " inner join v_EvnPL EPL on EPL.Person_id = PS.Person_id and EPL.Lpu_id = :Lpu_id and EPL.EvnClass_id = 3";
				// $query .= " left join Diag EVPLD with (nolock) on EVPLD.Diag_id = EPL.Diag_id";
				if ( $dbf !== true ) {
					if ( 'EvnVizitPL' == $data['SearchFormType'] ) {
						$query .= "
							left join v_Diag as evpldiag with (nolock) on evpldiag.Diag_id=EVizitPL.Diag_id
							left join v_LpuSection as evplls with (nolock) on evplls.LpuSection_id=EVizitPL.LpuSection_id
							left join v_MedPersonal as evplmp with (nolock) on evplmp.MedPersonal_id=EVizitPL.MedPersonal_id and evplmp.Lpu_id = EPL.Lpu_id
							left join v_PayType as evplpt with (nolock) on evplpt.PayType_id=EVizitPL.PayType_id
							left join v_VizitType as evplvt with (nolock) on evplvt.VizitType_id=EVizitPL.VizitType_id
							left join v_ServiceType as evplst with (nolock) on evplst.ServiceType_id=EVizitPL.ServiceType_id
						";
						if ( in_array($data['session']['region']['nick'], array('ufa')) ) {
							$query .= "
								outer apply (
									select top 1
										t1.EvnUslugaCommon_id,
										t1.Usluga_id,
										t1.UslugaComplex_id as UslugaComplex_uid,
										t3.UslugaComplex_Code
									from
										v_EvnUslugaCommon t1 with (nolock)
										left join v_Usluga t2 with (nolock) on t2.Usluga_id = t1.Usluga_id
										left join v_UslugaComplex t3 with (nolock) on t3.UslugaComplex_id = t1.UslugaComplex_id
										left join v_UslugaCategory t4 with (nolock) on t4.UslugaCategory_id = isnull(t2.UslugaCategory_id, t3.UslugaCategory_id)
									where
										t1.EvnUslugaCommon_pid = EVizitPL.EvnVizitPL_id
										and t4.UslugaCategory_SysNick in ('tfoms', 'lpusection')
									order by
										t1.EvnUslugaCommon_setDT desc
								) EU
							";
						}
					}
					else {
						$query .= "
							left join Diag EVPLD with (nolock) on EVPLD.Diag_id = EPL.Diag_id
							left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EPL.MedPersonal_id and MP.Lpu_id = :Lpu_id
							left join YesNo IsFinish with (nolock) on IsFinish.YesNo_id = EPL.EvnPL_IsFinish
						";
					}
				}
				else {
					switch ( $data['SearchFormType'] ) {
						case 'EvnAgg':
							$query .= "
								inner join v_EvnUsluga EvnUsluga with (nolock) on EvnUsluga.EvnUsluga_rid = EPL.EvnPL_id and EvnUsluga.Lpu_id = :Lpu_id
								inner join v_EvnAgg EvnAgg with (nolock) on EvnAgg.EvnAgg_pid = EvnUsluga.EvnUsluga_id and EvnAgg.Lpu_id = :Lpu_id
								left join AggType as dbfat with (nolock) on dbfat.AggType_id = EvnAgg.AggType_id
								left join AggWhen as dbfaw with (nolock) on dbfaw.AggWhen_id = EvnAgg.AggWhen_id
							";
						break;
						case 'EvnPL':
							$query .= "
								left join Diag as dbfdiag with (nolock) on dbfdiag.Diag_id = EPL.Diag_id
								left join v_Lpu as dbflpu with (nolock) on dbflpu.Lpu_id = EPL.Lpu_id
								left join SocStatus as dbfss with (nolock) on dbfss.SocStatus_id = PS.SocStatus_id
								left join ResultClass dbfrc with (nolock) on dbfrc.ResultClass_id = EPL.ResultClass_id
								left join DeseaseType dbfdt with (nolock) on dbfdt.DeseaseType_id = EPL.DeseaseType_id
								left join PrehospDirect dbfpd with (nolock) on dbfpd.PrehospDirect_id = EPL.PrehospDirect_id
								left join Sex dbfsex with (nolock) on dbfsex.Sex_id = PS.Sex_id
								left join Address dbfaddr with (nolock) on dbfaddr.Address_id = ISNULL(PS.PAddress_id, PS.UAddress_id)
								left join KLStreet dbfkls with (nolock) on dbfkls.KLStreet_id = dbfaddr.KLStreet_id
								left join KLArea dbfkla with (nolock) on dbfkla.KLArea_id = COALESCE(dbfaddr.KLTown_id, dbfaddr.KLCity_id, dbfaddr.KLSubRgn_id, dbfaddr.KLRgn_id)
									and dbfkls.KLStreet_id is null
								left join v_Lpu dbfprehosplpu with (nolock) on dbfprehosplpu.Lpu_id = EPL.Lpu_did
								left join LpuSection dbflsd with (nolock) on dbflsd.LpuSection_id = EPL.LpuSection_did
								left join YesNo dbfift with (nolock) on dbfift.YesNo_id = EPL.EvnPL_IsFirstTime
								left join DirectClass dbfdc with (nolock) on dbfdc.DirectClass_id = EPL.DirectClass_id
								left join v_Lpu dbflpudir with (nolock) on dbflpudir.Lpu_id = EPL.Lpu_oid
								left join LpuSection dbflsdir with (nolock) on dbflsdir.LpuSection_id = EPL.LpuSection_oid
								outer apply (
									select top 1
										YesNo.YesNo_Code as PersonChild_IsInvalid_Code,
										PersonSprTerrDop.PersonSprTerrDop_Code as PermRegion_Code
									from PersonChild with (nolock)
										left join YesNo with (nolock) on YesNo.YesNo_id = PersonChild.PersonChild_IsInvalid
										left join PersonSprTerrDop with (nolock) on PersonSprTerrDop.PersonSprTerrDop_id = PersonChild.PersonSprTerrDop_id
									where PersonChild.Person_id = EPL.Person_id
									order by PersonChild.PersonChild_insDT desc
								) dbfinv
							";
						break;

						case 'EvnUsluga':
							$query .= "
								inner join v_EvnUsluga EvnUsluga with (nolock) on EvnUsluga.EvnUsluga_rid = EPL.EvnPL_id and EvnUsluga.Lpu_id = :Lpu_id
								left join v_PayType as dbfpt with (nolock) on dbfpt.PayType_id = EvnUsluga.PayType_id
								left join v_UslugaComplex as dbfusluga with (nolock) on dbfusluga.UslugaComplex_id = EvnUsluga.UslugaComplex_id
								left join v_UslugaPlace as dbfup with (nolock) on dbfup.UslugaPlace_id = EvnUsluga.UslugaPlace_id
								left join v_MedPersonal dbfmp with (nolock) on dbfmp.MedPersonal_id = EvnUsluga.MedPersonal_id
									and dbfmp.Lpu_id = EPL.Lpu_id
							";
						break;

						case 'EvnVizitPL':
							$query .= "
								left join Diag as dbfdiag with (nolock) on dbfdiag.Diag_id=EVizitPL.Diag_id
								left join LpuSection as dbfls with (nolock) on dbfls.LpuSection_id=EVizitPL.LpuSection_id
								left join v_MedPersonal as dbfmp with (nolock) on dbfmp.MedPersonal_id=EVizitPL.MedPersonal_id and dbfmp.Lpu_id = EPL.Lpu_id
								left join PayType as dbfpt with (nolock) on dbfpt.PayType_id=EVizitPL.PayType_id
								left join VizitClass as dbfvc with (nolock) on dbfvc.VizitClass_id=EVizitPL.VizitClass_id
								left join VizitType as dbfvt with (nolock) on dbfvt.VizitType_id=EVizitPL.VizitType_id
								left join DeseaseType as dbfdt with (nolock) on dbfdt.DeseaseType_id=EVizitPL.DeseaseType_id
								left join ServiceType as dbfst with (nolock) on dbfst.ServiceType_id=EVizitPL.ServiceType_id
								left join ProfGoal as dbfpg with (nolock) on dbfpg.ProfGoal_id=EVizitPL.ProfGoal_id
							";
						break;
					}
				}

				// Диагноз и услуги
				if ( !empty($data['UslugaCategory_id']) || !empty($data['UslugaComplex_Code_From']) || !empty($data['UslugaComplex_Code_To']) ) {
					// по задаче #10719
					if ( 'EvnVizitPL' == $data['SearchFormType'] ) {
						$filter_evnvizit = "EU.EvnUsluga_pid = EVizitPL.EvnVizitPL_id";
					}
					else {
						$filter_evnvizit = "EU.EvnUsluga_rid = EPL.EvnPL_id";
					}

					$filter .= " and exists (
						select
							uc.UslugaComplex_id
						from v_EvnUsluga EU with (nolock)
							inner join UslugaComplex uc with (nolock) on uc.UslugaComplex_id = EU.UslugaComplex_id
						where
							 " . $filter_evnvizit . "
							 and EU.Lpu_id = :Lpu_id
							 and EU.EvnClass_SysNick in ('EvnUslugaCommon')
					";

					if ( !empty($data['UslugaCategory_id']) ) {
						$filter .= " and uc.UslugaCategory_id = :UslugaCategory_id ";
						$queryParams['UslugaCategory_id'] = $data['UslugaCategory_id'];
					}
					
					if ( !empty($data['UslugaComplex_Code_From']) ) {
						$filter .= " and uc.UslugaComplex_Code >= :UslugaComplex_Code_From ";
						$queryParams['UslugaComplex_Code_From'] = $data['UslugaComplex_Code_From'];
					}
					
					if ( !empty($data['UslugaComplex_Code_To']) ) {
						$filter .= " and uc.UslugaComplex_Code <= :UslugaComplex_Code_To ";
						$queryParams['UslugaComplex_Code_To'] = $data['UslugaComplex_Code_To'];
					}

					$filter .= ")";
				}

				if ( !empty($data['UslugaComplex_uid']) || !empty($data['UslugaComplex_Code']) ) {
					if ( 'EvnVizitPL' == $data['SearchFormType'] ) {
						$filter_evnvizit = "EU.EvnUsluga_pid = EVizitPL.EvnVizitPL_id";
					}
					else {
						$filter_evnvizit = "EU.EvnUsluga_rid = EPL.EvnPL_id";
					}

					if ( !empty($data['UslugaComplex_uid']) ) {
						$filter_evnvizit .= " and uc.UslugaComplex_id = :UslugaComplex_uid";
						$queryParams['UslugaComplex_uid'] = $data['UslugaComplex_uid'];
					}

					if ( !empty($data['UslugaComplex_Code']) ) {
						$data['UslugaComplex_Code'] = str_replace('%', '', $data['UslugaComplex_Code']);

						$filter_evnvizit .= " and uc.UslugaComplex_Code like :UslugaComplexCode";
						$queryParams['UslugaComplexCode'] = '%' . $data['UslugaComplex_Code'];
					}

					$filter .= " and exists (
						select
							uc.UslugaComplex_id
						from v_EvnUsluga EU with (nolock)
							inner join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = EU.UslugaComplex_id
						where
							 " . $filter_evnvizit . "
							 and EU.EvnClass_SysNick in ('EvnUslugaCommon')
						)
					";
				}

				// Посещение
				if ( isset($data['EvnPL_IsUnlaw']) ) {
					$filter .= " and EPL.EvnPL_IsUnlaw = :EvnPL_IsUnlaw";
					$queryParams['EvnPL_IsUnlaw'] = $data['EvnPL_IsUnlaw'];
				}

				// Посещение
				if ( isset($data['EvnPL_IsUnport']) ) {
					$filter .= " and EPL.EvnPL_IsUnport = :EvnPL_IsUnport";
					$queryParams['EvnPL_IsUnport'] = $data['EvnPL_IsUnport'];
				}

				// Посещение
				if ( isset($data['EvnPL_NumCard']) ) {
					$filter .= " and EPL.EvnPL_NumCard = :EvnPL_NumCard";
					$queryParams['EvnPL_NumCard'] = $data['EvnPL_NumCard'];
				}

				// Посещение
				if ( isset($data['EvnPL_setDate_Range'][0]) ) {
					$filter .= " and EPL.EvnPL_setDate >= :EvnPL_setDate_Range_0";
					$queryParams['EvnPL_setDate_Range_0'] = $data['EvnPL_setDate_Range'][0];
				}
				
				// Посещение
				if ( isset($data['EvnPL_setDate_Range'][1]) ) {
					$filter .= " and EPL.EvnPL_setDate <= :EvnPL_setDate_Range_1";
					$queryParams['EvnPL_setDate_Range_1'] = $data['EvnPL_setDate_Range'][1];
				}

				if (($data['SearchFormType'] == 'EvnPL') && (isset($data['VizitClass_id'])))
				{
					$query .= " inner join v_EvnVizitPL EVizitPL2 with (nolock) on EVizitPL2.EvnVizitPL_pid = EPL.EvnPL_id";
					$filter .= " and EVizitPL2.VizitClass_id = :VizitClass_id";
					$queryParams['VizitClass_id'] = $data['VizitClass_id'];
				}
				if(($data['SearchFormType'] == 'EvnVizitPL') && (isset($data['VizitClass_id'])))
				{
					$filter .= " and EVizitPL.VizitClass_id = :VizitClass_id";
					$queryParams['VizitClass_id'] = $data['VizitClass_id'];
				}

				// Посещение
				if ( isset($data['EvnPL_disDate_Range'][0]) ) {
					$filter .= " and EPL.EvnPL_disDate >= :EvnPL_disDate_Range_0";
					$queryParams['EvnPL_disDate_Range_0'] = $data['EvnPL_disDate_Range'][0];
				}
				
				// Посещение
				if ( isset($data['EvnPL_disDate_Range'][1]) ) {
					$filter .= " and EPL.EvnPL_disDate <= :EvnPL_disDate_Range_1";
					$queryParams['EvnPL_disDate_Range_1'] = $data['EvnPL_disDate_Range'][1];
				}
				//var_dump($data['VizitClass_id']);
				//die;
				if ( isset($data['LpuSectionViz_id']) || isset($data['EvnVizitPL_isPaid']) || isset($data['LpuBuildingViz_id']) || isset($data['MedPersonalViz_id']) || isset($data['MedPersonalViz_sid']) ||
					isset($data['PayType_id']) || isset($data['ServiceType_id']) || isset($data['Vizit_Date_Range'][0]) ||
					isset($data['Vizit_Date_Range'][1]) || isset($data['VizitType_id']) ||
					isset($data['DeseaseType_id']) || isset($data['Diag_Code_From']) ||
					isset($data['Diag_Code_To'])
				) {
					$filter .= " and exists (
						select top 1 1
						from v_EvnVizitPL EVPL2 with (nolock)
						left join Diag (nolock) on Diag.Diag_id = EVPL2.Diag_id
						where
							EVPL2.Lpu_id = :Lpu_id
					";

					if ( 'EvnVizitPL' == $data['SearchFormType'] ) {
						$filter .= " and EVPL2.EvnVizitPL_id = EVizitPL.EvnVizitPL_id";
					}
					else {
						$filter .= " and EVPL2.EvnVizitPL_pid = EPL.EvnPL_id";
					}

					// Диагноз и услуги
					if ( isset($data['DeseaseType_id']) ) {
						$filter .= " and EVPL2.DeseaseType_id = :DeseaseType_id";
						$queryParams['DeseaseType_id'] = $data['DeseaseType_id'];
					}

					// Диагноз и услуги
					if ( isset($data['Diag_Code_From']) ) {
						$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
						$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
					}

					// Диагноз и услуги
					if ( isset($data['Diag_Code_To']) ) {
						$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
						$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
					}
					
					// Посещение
					if ( isset($data['LpuSectionViz_id']) ) {
						$filter .= " and EVPL2.LpuSection_id = :LpuSectionViz_id";
						$queryParams['LpuSectionViz_id'] = $data['LpuSectionViz_id'];
					} elseif ( isset($data['LpuBuildingViz_id']) ) {
						$filter .= " and exists (
							select
								1
							from LpuSection LS with (nolock)
								left join LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
							where
								LU.LpuBuilding_id = :LpuBuildingViz_id AND
								LS.LpuSection_id = EVPL2.LpuSection_id
							)";
						$queryParams['LpuBuildingViz_id'] = $data['LpuBuildingViz_id'];
					}

					// Посещение
					if ( isset($data['MedPersonalViz_id']) ) {
						$filter .= " and EVPL2.MedPersonal_id = :MedPersonalViz_id";
						$queryParams['MedPersonalViz_id'] = $data['MedPersonalViz_id'];
					}
					
					// Посещение
					if ( isset($data['EvnVizitPL_isPaid']) ) {
						$filter .= " and ISNULL(EVPL2.EvnVizitPL_isPaid,1) = :EvnVizitPL_isPaid";
						$queryParams['EvnVizitPL_isPaid'] = $data['EvnVizitPL_isPaid'];
					}

					// Посещение
					if ( isset($data['MedPersonalViz_sid']) ) {
						$filter .= " and EVPL2.MedPersonal_sid = :MedPersonalViz_sid";
						$queryParams['MedPersonalViz_sid'] = $data['MedPersonalViz_sid'];
					}

					// Посещение
					if ( isset($data['PayType_id']) ) {
						$filter .= " and EVPL2.PayType_id = :PayType_id";
						$queryParams['PayType_id'] = $data['PayType_id'];
					}

					// Посещение
					if ( isset($data['ServiceType_id']) ) {
						$filter .= " and EVPL2.ServiceType_id = :ServiceType_id";
						$queryParams['ServiceType_id'] = $data['ServiceType_id'];
					}

					// Посещение
					if ( isset($data['Vizit_Date_Range'][0]) ) {
						$filter .= " and EVPL2.EvnVizitPL_setDate >= :Vizit_Date_Range_0";
						$queryParams['Vizit_Date_Range_0'] = $data['Vizit_Date_Range'][0];
					}

					// Посещение
					if ( isset($data['Vizit_Date_Range'][1]) ) {
						$filter .= " and EVPL2.EvnVizitPL_setDate <= :Vizit_Date_Range_1";
						$queryParams['Vizit_Date_Range_1'] = $data['Vizit_Date_Range'][1];
					}

					// Посещение
					if ( isset($data['VizitType_id']) ) {
						$filter .= " and EVPL2.VizitType_id = :VizitType_id";
						$queryParams['VizitType_id'] = $data['VizitType_id'];
					}

					$filter .= ")";
				}

				// Посещение
				if ( isset($data['PrehospTrauma_id']) ) {
					$filter .= " and EPL.PrehospTrauma_id = :PrehospTrauma_id";
					$queryParams['PrehospTrauma_id'] = $data['PrehospTrauma_id'];
				}

				// Результаты
				if ( isset($data['DirectClass_id']) ) {
					$filter .= " and EPL.DirectClass_id = :DirectClass_id";
					$queryParams['DirectClass_id'] = $data['DirectClass_id'];
				}

				// Результаты
				if ( isset($data['DirectType_id']) ) {
					$filter .= " and EPL.DirectType_id = :DirectType_id";
					$queryParams['DirectType_id'] = $data['DirectType_id'];
				}

				// Результаты
				if ( isset($data['EvnPL_IsFinish']) ) {
					$filter .= " and EPL.EvnPL_IsFinish = :EvnPL_IsFinish";
					$queryParams['EvnPL_IsFinish'] = $data['EvnPL_IsFinish'];
				}

				// Результаты
				if ( isset($data['Lpu_oid']) ) {
					$filter .= " and EPL.Lpu_oid = :Lpu_oid";
					$queryParams['Lpu_oid'] = $data['Lpu_oid'];
				}

				// Результаты
				if ( isset($data['LpuSection_oid']) ) {
					$filter .= " and EPL.LpuSection_oid = :LpuSection_oid";
					$queryParams['LpuSection_oid'] = $data['LpuSection_oid'];
				}

				// Результаты
				if ( isset($data['ResultClass_id']) ) {
					$filter .= " and EPL.ResultClass_id = :ResultClass_id";
					$queryParams['ResultClass_id'] = $data['ResultClass_id'];
				}

				if ( isset($data['StickCause_id']) || isset($data['StickType_id']) ||
					isset($data['EvnStick_begDate_Range'][0]) || isset($data['EvnStick_begDate_Range'][1]) ||
					isset($data['EvnStick_endDate_Range'][0]) || isset($data['EvnStick_endDate_Range'][1])
				)
				{
					$evn_stick_filter = '';

					// Результаты
					if ( isset($data['EvnStick_begDate_Range'][0]) ) {
						$evn_stick_filter .= " and ESB.EvnStickBase_setDT >= :EvnStick_begDate_Range_0";
						$queryParams['EvnStick_begDate_Range_0'] = $data['EvnStick_begDate_Range'][0];
					}

					// Результаты
					if ( isset($data['EvnStick_begDate_Range'][1]) ) {
						$evn_stick_filter .= " and ESB.EvnStickBase_setDT <= :EvnStick_begDate_Range_1";
						$queryParams['EvnStick_begDate_Range_1'] = $data['EvnStick_begDate_Range'][1];
					}

					// Результаты
					if ( isset($data['EvnStick_endDate_Range'][0]) ) {
						$evn_stick_filter .= " and (
							(ESB.StickType_id = 1 and ESB.EvnStickBase_disDT >= :EvnStick_endDate_Range_0)
							or (ESB.StickType_id = 2 and exists (select EvnStickWorkRelease_id from v_EvnStickWorkRelease with (nolock) where EvnStickBase_id = ESB.EvnStickBase_id and EvnStickWorkRelease_endDT >= :EvnStick_endDate_Range_0))
						)";
						$queryParams['EvnStick_endDate_Range_0'] = $data['EvnStick_endDate_Range'][0];
					}

					// Результаты
					if ( isset($data['EvnStick_endDate_Range'][1]) ) {
						$evn_stick_filter .= " and (
							(ESB.StickType_id = 1 and ESB.EvnStickBase_disDT <= :EvnStick_endDate_Range_1)
							or (ESB.StickType_id = 2 and exists (select EvnStickWorkRelease_id from v_EvnStickWorkRelease with (nolock) where EvnStickBase_id = ESB.EvnStickBase_id and EvnStickWorkRelease_endDT <= :EvnStick_endDate_Range_1))
						)";
						$queryParams['EvnStick_endDate_Range_1'] = $data['EvnStick_endDate_Range'][1];
					}

					// Результаты
					if ( isset($data['StickCause_id']) ) {
						$evn_stick_filter .= " and ESB.StickCause_id = :StickCause_id";
						$queryParams['StickCause_id'] = $data['StickCause_id'];
					}

					// Результаты
					if ( isset($data['StickType_id']) ) {
						$evn_stick_filter .= " and ESB.StickType_id = :StickType_id";
						$queryParams['StickType_id'] = $data['StickType_id'];
					}

					// ЛВН должны быть созданы в рамках учетного документа (первый селект из v_EvnStickBase)
					// либо присоединены ЛВН из других учетных документов (второй селект из v_EvnLink)
					$filter .= "
						and exists (
							select EvnStickBase_id
							from v_EvnStickBase ESB with (nolock)
							where ESB.EvnStickBase_mid = EPL.EvnPL_id
								" . $evn_stick_filter . "
							union all
							select Evn_id as EvnStickBase_id
							from v_EvnLink EL with (nolock)
								inner join v_EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = EL.Evn_lid
							where EL.Evn_id = EPL.EvnPL_id
								" . $evn_stick_filter . "
						)
					";
				}
			break;

			case 'EvnPLStom':
			case 'EvnVizitPLStom':
				if ( $data['PersonPeriodicType_id'] == 2 ) {
					if ( 'EvnVizitPLStom' == $data['SearchFormType'])
					{
						$query .= " inner join v_EvnVizitPLStom as EVPLS with (nolock) on EVPLS.Server_id = PS.Server_id and EVPLS.PersonEvn_id = PS.PersonEvn_id and EVPLS.Lpu_id = :Lpu_id";
						$query .= " inner join v_EvnPLStom EPLS with (nolock) on EPLS.EvnPLStom_id = EVPLS.EvnVizitPLStom_pid and EPLS.Lpu_id = :Lpu_id and EPLS.EvnClass_id = 6";
					}
					else
					{
						$query .= " inner join v_EvnPLStom EPLS with (nolock) on EPLS.Server_id = PS.Server_id and EPLS.PersonEvn_id = PS.PersonEvn_id and EPLS.Lpu_id = :Lpu_id and EPLS.EvnClass_id = 6";
					}
				}
				else {
					if ( 'EvnVizitPLStom' == $data['SearchFormType'])
					{
						$query .= " inner join v_EvnVizitPLStom as EVPLS with (nolock) on EVPLS.Person_id = PS.Person_id and EVPLS.Lpu_id = :Lpu_id";
						$query .= " inner join v_EvnPLStom EPLS with (nolock) on EPLS.EvnPLStom_id = EVPLS.EvnVizitPLStom_pid and EPLS.Lpu_id = :Lpu_id and EPLS.EvnClass_id = 6";
					}
					else
					{
						$query .= " inner join v_EvnPLStom EPLS with (nolock) on EPLS.Person_id = PS.Person_id and EPLS.Lpu_id = :Lpu_id and EPLS.EvnClass_id = 6";
					}
				}

				// $query .= " inner join v_EvnPLStom EPLS on EPLS.Person_id = PS.Person_id and EPLS.Lpu_id = :Lpu_id and EPLS.EvnClass_id = 6";
				if('EvnPLStom' == $data['SearchFormType'])
				{
					$query .= " left join v_EvnVizitPLStom EVPLS with (nolock) on EVPLS.EvnVizitPLStom_pid = EPLS.EvnPLStom_id and EVPLS.EvnVizitPLStom_Index = EVPLS.EvnVizitPLStom_Count - 1 and EVPLS.Lpu_id = :Lpu_id";
					$query .= " left join YesNo IsFinish with (nolock) on IsFinish.YesNo_id = EPLS.EvnPLStom_IsFinish";

					// https://redmine.swan.perm.ru/issues/16145
					if ( !empty($data['DeseaseType_id']) || !empty($data['Diag_Code_From']) ||
						!empty($data['Diag_Code_To'])
					)
					{
						$filter .= " and exists (
							select t1.EvnVizitPLStom_id
							from v_EvnVizitPLStom t1 with (nolock)
								inner join v_Diag t2 with (nolock) on t2.Diag_id = t1.Diag_id
							where t1.EvnVizitPLStom_pid = EPLS.EvnPLStom_id
						";

						// Диагноз и услуги
						if ( !empty($data['DeseaseType_id']) ) {
							$filter .= " and t1.DeseaseType_id = :DeseaseType_id";
							$queryParams['DeseaseType_id'] = $data['DeseaseType_id'];
						}

						// Диагноз и услуги
						if ( !empty($data['Diag_Code_From']) ) {
							$filter .= " and t2.Diag_Code >= :Diag_Code_From";
							$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
						}

						// Диагноз и услуги
						if ( !empty($data['Diag_Code_To']) ) {
							$filter .= " and t2.Diag_Code <= :Diag_Code_To";
							$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
						}

						$filter .= ")";
					}
				}
				if('EvnVizitPLStom' == $data['SearchFormType'])
				{
					$query .= "
						left join v_LpuSection as evplls with (nolock) on evplls.LpuSection_id=EVPLS.LpuSection_id
						left join v_PayType as evplpt with (nolock) on evplpt.PayType_id=EVPLS.PayType_id
						left join v_VizitType as evplvt with (nolock) on evplvt.VizitType_id=EVPLS.VizitType_id
						left join v_ServiceType as evplst with (nolock) on evplst.ServiceType_id=EVPLS.ServiceType_id
						left join v_Diag as evpldiag with (nolock) on evpldiag.Diag_id = EVPLS.Diag_id
					";

					// https://redmine.swan.perm.ru/issues/16145
					if ( !empty($data['DeseaseType_id']) || !empty($data['Diag_Code_From']) ||
						!empty($data['Diag_Code_To'])
					)
					{
						// Диагноз и услуги
						if ( !empty($data['DeseaseType_id']) ) {
							$filter .= " and EVPLS.DeseaseType_id = :DeseaseType_id";
							$queryParams['DeseaseType_id'] = $data['DeseaseType_id'];
						}

						// Диагноз и услуги
						if ( !empty($data['Diag_Code_From']) ) {
							$filter .= " and evpldiag.Diag_Code >= :Diag_Code_From";
							$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
						}

						// Диагноз и услуги
						if ( !empty($data['Diag_Code_To']) ) {
							$filter .= " and evpldiag.Diag_Code <= :Diag_Code_To";
							$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
						}
					}
				}
				$query .= " left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EVPLS.MedPersonal_id and MP.Lpu_id = :Lpu_id";

				if ( !empty($data['UslugaCategory_id']) || !empty($data['UslugaComplex_Code_From']) || !empty($data['UslugaComplex_Code_To']) ) {
					if ( 'EvnVizitPLStom' == $data['SearchFormType'] ) {
						$filter_evnvizit = "EU.EvnUsluga_pid = EVPLS.EvnVizitPLStom_id";
					}
					else {
						$filter_evnvizit = "EU.EvnUsluga_rid = EPLS.EvnPLStom_id";
					}

					$filter .= " and exists (
						select
							uc.UslugaComplex_id
						from v_EvnUsluga EU with (nolock)
							inner join UslugaComplex uc with (nolock) on uc.UslugaComplex_id = EU.UslugaComplex_id
						where
							" . $filter_evnvizit . "
							and EU.Lpu_id = :Lpu_id
							and EU.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaStom')
					";

					if ( !empty($data['UslugaCategory_id']) ) {
						$filter .= " and uc.UslugaCategory_id = :UslugaCategory_id ";
						$queryParams['UslugaCategory_id'] = $data['UslugaCategory_id'];
					}
					
					if ( !empty($data['UslugaComplex_Code_From']) ) {
						$filter .= " and uc.UslugaComplex_Code >= :UslugaComplex_Code_From ";
						$queryParams['UslugaComplex_Code_From'] = $data['UslugaComplex_Code_From'];
					}
					
					if ( !empty($data['UslugaComplex_Code_To']) ) {
						$filter .= " and uc.UslugaComplex_Code <= :UslugaComplex_Code_To ";
						$queryParams['UslugaComplex_Code_To'] = $data['UslugaComplex_Code_To'];
					}

					$filter .= ")";
				}

				if ( !empty($data['UslugaComplex_uid']) || !empty($data['UslugaComplex_Code']) ) {
					if ( 'EvnVizitPLStom' == $data['SearchFormType'] ) {
						$filter_evnvizit = "EU.EvnUsluga_pid = EVPLS.EvnVizitPLStom_id";
					}
					else {
						$filter_evnvizit = "EU.EvnUsluga_rid = EPLS.EvnPLStom_id";
					}

					if ( !empty($data['UslugaComplex_uid']) ) {
						$filter_evnvizit .= " and uc.UslugaComplex_id = :UslugaComplex_uid";
						$queryParams['UslugaComplex_uid'] = $data['UslugaComplex_uid'];
					}

					if ( !empty($data['UslugaComplex_Code']) ) {
						$data['UslugaComplex_Code'] = str_replace('%', '', $data['UslugaComplex_Code']);

						$filter_evnvizit .= " and uc.UslugaComplex_Code like :UslugaComplexCode";
						$queryParams['UslugaComplexCode'] = '%' . $data['UslugaComplex_Code'];
					}

					$filter .= " and exists (
						select
							uc.UslugaComplex_id
						from v_EvnUsluga EU with (nolock)
							inner join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = EU.UslugaComplex_id
						where
							 " . $filter_evnvizit . "
							 and EU.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaStom')
						)
					";
				}

				// Посещение
				if ( isset($data['EvnPL_IsUnlaw']) ) {
					$filter .= " and EPLS.EvnPLStom_IsUnlaw = :EvnPLStom_IsUnlaw";
					$queryParams['EvnPLStom_IsUnlaw'] = $data['EvnPL_IsUnlaw'];
				}

				// Посещение
				if ( isset($data['EvnPL_IsUnport']) ) {
					$filter .= " and EPLS.EvnPLStom_IsUnport = :EvnPLStom_IsUnport";
					$queryParams['EvnPLStom_IsUnport'] = $data['EvnPL_IsUnport'];
				}

				// Посещение
				if ( isset($data['EvnPL_NumCard']) ) {
					$filter .= " and EPLS.EvnPLStom_NumCard = :EvnPLStom_NumCard";
					$queryParams['EvnPLStom_NumCard'] = $data['EvnPL_NumCard'];
				}
				
				// Посещение
				if ( isset($data['EvnPL_setDate_Range'][0]) ) {
					$filter .= " and EPLS.EvnPLStom_setDate >= :EvnPLStom_setDate_Range_0";
					$queryParams['EvnPLStom_setDate_Range_0'] = $data['EvnPL_setDate_Range'][0];
				}
				
				// Посещение
				if ( isset($data['EvnPL_setDate_Range'][1]) ) {
					$filter .= " and EPLS.EvnPLStom_setDate <= :EvnPLStom_setDate_Range_1";
					$queryParams['EvnPLStom_setDate_Range_1'] = $data['EvnPL_setDate_Range'][1];
				}
				
				// Посещение
				if ( isset($data['EvnPL_disDate_Range'][0]) ) {
					$filter .= " and EPLS.EvnPLStom_disDate >= :EvnPLStom_disDate_Range_0";
					$queryParams['EvnPLStom_disDate_Range_0'] = $data['EvnPL_disDate_Range'][0];
				}
				
				// Посещение
				if ( isset($data['EvnPL_disDate_Range'][1]) ) {
					$filter .= " and EPLS.EvnPLStom_disDate <= :EvnPLStom_disDate_Range_1";
					$queryParams['EvnPLStom_disDate_Range_1'] = $data['EvnPL_disDate_Range'][1];
				}

				// Посещение
				if ( isset($data['PrehospTrauma_id']) ) {
					$filter .= " and EPLS.PrehospTrauma_id = :PrehospTrauma_id";
					$queryParams['PrehospTrauma_id'] = $data['PrehospTrauma_id'];
				}
				
				if ( isset($data['LpuSectionViz_id']) || isset($data['EvnVizitPLStom_isPaid']) || isset($data['LpuBuildingViz_id']) || isset($data['MedPersonalViz_id']) || isset($data['MedPersonalViz_sid']) ||
					isset($data['PayType_id']) || isset($data['ServiceType_id']) || isset($data['Vizit_Date_Range'][0]) ||
					isset($data['Vizit_Date_Range'][1]) || isset($data['VizitType_id']) || isset($data['EvnVizitPLStom_IsPrimaryVizit'])
				) {
					$filter .= " and exists (
						select 1
						from v_EvnVizitPLStom EVPLS2 with (nolock)
						where (1 = 1) and EVPLS2.Lpu_id = :Lpu_id
							
					";

					if ( 'EvnVizitPLStom' == $data['SearchFormType'] ) {
						$filter .= " and EVPLS2.EvnVizitPLStom_id = EVPLS.EvnVizitPLStom_id";
					}
					else {
						$filter .= " and EVPLS2.EvnVizitPLStom_pid = EPLS.EvnPLStom_id";
					}

					// Посещение
					if ( isset($data['LpuSectionViz_id']) ) {
						$filter .= " and EVPLS2.LpuSection_id = :LpuSectionViz_id";
						$queryParams['LpuSectionViz_id'] = $data['LpuSectionViz_id'];
					} elseif ( isset($data['LpuBuildingViz_id']) ) {
						$filter .= " and exists (
							select
								1
							from LpuSection LS with (nolock)
								left join LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
							where
								LU.LpuBuilding_id = :LpuBuildingViz_id AND
								LS.LpuSection_id = EVPLS2.LpuSection_id
							)";
						$queryParams['LpuBuildingViz_id'] = $data['LpuBuildingViz_id'];
					}

					// Посещение
					if ( isset($data['MedPersonalViz_id']) ) {
						$filter .= " and EVPLS2.MedPersonal_id = :MedPersonalViz_id";
						$queryParams['MedPersonalViz_id'] = $data['MedPersonalViz_id'];
					}
					
					// Посещение
					if ( isset($data['EvnVizitPLStom_isPaid']) ) {
						$filter .= " and ISNULL(EVPLS2.EvnVizitPLStom_isPaid, 1) = :EvnVizitPLStom_isPaid";
						$queryParams['EvnVizitPLStom_isPaid'] = $data['EvnVizitPLStom_isPaid'];
					}

					// Посещение
					if ( isset($data['MedPersonalViz_sid']) ) {
						$filter .= " and EVPLS2.MedPersonal_sid = :MedPersonalViz_sid";
						$queryParams['MedPersonalViz_sid'] = $data['MedPersonalViz_sid'];
					}

					// Посещение
					if ( isset($data['PayType_id']) ) {
						$filter .= " and EVPLS2.PayType_id = :PayType_id";
						$queryParams['PayType_id'] = $data['PayType_id'];
					}
					
					if ( isset($data['EvnVizitPLStom_IsPrimaryVizit']) ) {
						$filter .= " and EVPLS2.EvnVizitPLStom_IsPrimaryVizit = :EvnVizitPLStom_IsPrimaryVizit";
						$queryParams['EvnVizitPLStom_IsPrimaryVizit'] = $data['EvnVizitPLStom_IsPrimaryVizit'];
					}

					// Посещение
					if ( isset($data['ServiceType_id']) ) {
						$filter .= " and EVPLS2.ServiceType_id = :ServiceType_id";
						$queryParams['ServiceType_id'] = $data['ServiceType_id'];
					}

					// Посещение
					if ( isset($data['Vizit_Date_Range'][0]) ) {
						$filter .= " and EVPLS2.EvnVizitPLStom_setDate >= :Vizit_Date_Range_0";
						$queryParams['Vizit_Date_Range_0'] = $data['Vizit_Date_Range'][0];
					}

					// Посещение
					if ( isset($data['Vizit_Date_Range'][1]) ) {
						$filter .= " and EVPLS2.EvnVizitPLStom_setDate <= :Vizit_Date_Range_1";
						$queryParams['Vizit_Date_Range_1'] = $data['Vizit_Date_Range'][1];
					}

					// Посещение
					if ( isset($data['VizitType_id']) ) {
						$filter .= " and EVPLS2.VizitType_id = :VizitType_id";
						$queryParams['VizitType_id'] = $data['VizitType_id'];
					}

					$filter .= ")";
				}

				// Результаты
				if ( isset($data['DirectClass_id']) ) {
					$filter .= " and EPLS.DirectClass_id = :DirectClass_id";
					$queryParams['DirectClass_id'] = $data['DirectClass_id'];
				}

				// Результаты
				if ( isset($data['DirectType_id']) ) {
					$filter .= " and EPLS.DirectType_id = :DirectType_id";
					$queryParams['DirectType_id'] = $data['DirectType_id'];
				}

				// Результаты
				if ( isset($data['EvnPL_IsFinish']) ) {
					$filter .= " and EPLS.EvnPLStom_IsFinish = :EvnPLStom_IsFinish";
					$queryParams['EvnPLStom_IsFinish'] = $data['EvnPL_IsFinish'];
				}

				// Результаты
				if ( isset($data['Lpu_oid']) ) {
					$filter .= " and EPLS.Lpu_oid = :Lpu_oid";
					$queryParams['Lpu_oid'] = $data['Lpu_oid'];
				}

				// Результаты
				if ( isset($data['LpuSection_oid']) ) {
					$filter .= " and EPLS.LpuSection_oid = :LpuSection_oid";
					$queryParams['LpuSection_oid'] = $data['LpuSection_oid'];
				}

				// Результаты
				if ( isset($data['ResultClass_id']) ) {
					$filter .= " and EPLS.ResultClass_id = :ResultClass_id";
					$queryParams['ResultClass_id'] = $data['ResultClass_id'];
				}

				if ( isset($data['StickCause_id']) || isset($data['StickType_id']) ||
					isset($data['EvnStick_begDate_Range'][0]) || isset($data['EvnStick_begDate_Range'][1]) ||
					isset($data['EvnStick_endDate_Range'][0]) || isset($data['EvnStick_endDate_Range'][1])
				)
				{
					$evn_stick_filter = '';

					// Результаты
					if ( isset($data['EvnStick_begDate_Range'][0]) ) {
						$evn_stick_filter .= " and ESB.EvnStickBase_setDT >= :EvnStick_begDate_Range_0";
						$queryParams['EvnStick_begDate_Range_0'] = $data['EvnStick_begDate_Range'][0];
					}

					// Результаты
					if ( isset($data['EvnStick_begDate_Range'][1]) ) {
						$evn_stick_filter .= " and ESB.EvnStickBase_setDT <= :EvnStick_begDate_Range_1";
						$queryParams['EvnStick_begDate_Range_1'] = $data['EvnStick_begDate_Range'][1];
					}

					// Результаты
					if ( isset($data['EvnStick_endDate_Range'][0]) ) {
						$evn_stick_filter .= " and (
							(ESB.StickType_id = 1 and ESB.EvnStickBase_disDT >= :EvnStick_endDate_Range_0)
							or (ESB.StickType_id = 2 and exists (select EvnStickWorkRelease_id from v_EvnStickWorkRelease with (nolock) where EvnStickBase_id = ESB.EvnStickBase_id and EvnStickWorkRelease_endDT >= :EvnStick_endDate_Range_0))
						)";
						$queryParams['EvnStick_endDate_Range_0'] = $data['EvnStick_endDate_Range'][0];
					}

					// Результаты
					if ( isset($data['EvnStick_endDate_Range'][1]) ) {
						$evn_stick_filter .= " and (
							(ESB.StickType_id = 1 and ESB.EvnStickBase_disDT <= :EvnStick_endDate_Range_1)
							or (ESB.StickType_id = 2 and exists (select EvnStickWorkRelease_id from v_EvnStickWorkRelease with (nolock) where EvnStickBase_id = ESB.EvnStickBase_id and EvnStickWorkRelease_endDT <= :EvnStick_endDate_Range_1))
						)";
						$queryParams['EvnStick_endDate_Range_1'] = $data['EvnStick_endDate_Range'][1];
					}

					// Результаты
					if ( isset($data['StickCause_id']) ) {
						$evn_stick_filter .= " and ESB.StickCause_id = :StickCause_id";
						$queryParams['StickCause_id'] = $data['StickCause_id'];
					}

					// Результаты
					if ( isset($data['StickType_id']) ) {
						$evn_stick_filter .= " and ESB.StickType_id = :StickType_id";
						$queryParams['StickType_id'] = $data['StickType_id'];
					}
					
					$filter .= "
						and exists (
							select EvnStickBase_id
							from v_EvnStickBase ESB with (nolock)
							where ESB.EvnStickBase_mid = EPLS.EvnPLStom_id
								" . $evn_stick_filter . "
							union all
							select Evn_id as EvnStickBase_id
							from v_EvnLink EL with (nolock)
								inner join v_EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = EL.Evn_lid
							where EL.Evn_id = EPLS.EvnPLStom_id
								" . $evn_stick_filter . "
						)
					";
				}
			break;

			case 'EvnPS':
			case 'EvnSection':
			case 'EvnDiag':
			case 'EvnLeave':
			case 'EvnStick':
			case 'KvsPerson':
			case 'KvsPersonCard':
			case 'KvsEvnDiag':
			case 'KvsEvnPS':
			case 'KvsEvnSection':
			case 'KvsNarrowBed':
			case 'KvsEvnUsluga':
			case 'KvsEvnUslugaOB':
			case 'KvsEvnUslugaAn':
			case 'KvsEvnUslugaOsl':
			case 'KvsEvnDrug':
			case 'KvsEvnLeave':
			case 'KvsEvnStick':
				if ( $data['PersonPeriodicType_id'] == 2 ) {
					$query .= " inner join v_EvnPS EPS with (nolock) on EPS.Server_id = PS.Server_id and EPS.PersonEvn_id = PS.PersonEvn_id and EPS.Lpu_id = :Lpu_id";
				}
				else {
					$query .= " inner join v_EvnPS EPS with (nolock) on EPS.Person_id = PS.Person_id and EPS.Lpu_id = :Lpu_id";
				}
				
				// $query .= " left join PayType dbfpayt on dbfpayt.PayType_id = EPS.PayType_id ";

				if ( $dbf === true ) {
					switch ( $data['SearchFormType'] ) {
						case 'EvnPS':
							$query .= "
								left join v_Lpu as dbflpu with (nolock) on dbflpu.Lpu_id = EPS.Lpu_id
								left join PrehospArrive dbfpa (nolock) on dbfpa.PrehospArrive_id = EPS.PrehospArrive_id
								left join PrehospDirect dbfpd (nolock) on dbfpd.PrehospDirect_id = EPS.PrehospDirect_id
								left join PrehospToxic dbfpt (nolock) on dbfpt.PrehospToxic_id = EPS.PrehospToxic_id
								left join PayType dbfpayt (nolock) on dbfpayt.PayType_id = EPS.PayType_id
								left join PrehospTrauma dbfprtr (nolock) on dbfprtr.PrehospTrauma_id = EPS.PrehospTrauma_id
								left join PrehospType dbfprtype (nolock) on dbfprtype.PrehospType_id = EPS.PrehospType_id
								left join Org dbfdorg (nolock) on dbfdorg.Org_id = EPS.Org_did
								left join LpuSection dbflsd (nolock) on dbflsd.LpuSection_id = EPS.LpuSection_did
								left join Lpu dbfdlpu (nolock) on dbfdlpu.Lpu_id = EPS.Lpu_did
								left join Org dbfoorg (nolock) on dbfoorg.Org_id = dbfdlpu.Org_id
								left join v_MedPersonal dbfmp (nolock) on dbfmp.MedPersonal_id = EPS.MedPersonal_pid and dbfmp.Lpu_id = EPS.Lpu_id
							";
						break;
						case 'EvnSection':
							$query .= "
								inner join v_EvnSection as ESEC with (nolock) on ESEC.EvnSection_pid = EPS.EvnPS_id and ESEC.Lpu_id = :Lpu_id
								left join LpuSection as dbfsec with (nolock) on dbfsec.LpuSection_id = ESEC.LpuSection_id
								left join PayType as dbfpay with (nolock) on dbfpay.PayType_id = ESEC.PayType_id
								left join TariffClass as dbftar with (nolock) on dbftar.TariffClass_id = ESEC.TariffClass_id
								left join v_MedPersonal dbfmp (nolock) on dbfmp.MedPersonal_id = EPS.MedPersonal_pid and dbfmp.Lpu_id = EPS.Lpu_id
							";
						break;
						case 'EvnDiag':
							$query .= "
								left join v_EvnSection sect (nolock) on sect.EvnSection_pid = EPS.EvnPS_id and sect.Lpu_id = :Lpu_id
								left join v_EvnLeave leav (nolock) on leav.EvnLeave_pid = EPS.EvnPS_id and leav.Lpu_id = :Lpu_id
								inner join v_EvnDiagPS EDPS (nolock) on EDPS.EvnDiagPS_pid = EPS.EvnPS_id 
									or EDPS.EvnDiagPS_pid = sect.EvnSection_id
									or EDPS.EvnDiagPS_pid = leav.EvnLeave_id
								outer apply 
									GetMesForEvnDiagPS(
										EDPS.Diag_id,
										EDPS.Person_id,
										EPS.Lpu_id,
										EPS.LpuSection_did,
										EDPS.EvnDiagPS_setDate
									) as dbfmes
							";
						break;
						case 'EvnLeave':
							$query .= "
								left join v_EvnLeave ELV (nolock) on ELV.EvnLeave_pid = EPS.EvnPS_id and EPS.LeaveType_id = 1 and ELV.Lpu_id = :Lpu_id
								left join v_EvnOtherLpu dbfeol (nolock) on dbfeol.EvnOtherLpu_pid = EPS.EvnPS_id and EPS.LeaveType_id = 2 and dbfeol.Lpu_id = :Lpu_id
								left join v_EvnDie dbfed (nolock) on dbfed.EvnDie_pid = EPS.EvnPS_id and EPS.LeaveType_id = 3 and dbfed.Lpu_id = :Lpu_id
								left join v_EvnOtherStac dbfeost (nolock) on dbfeost.EvnOtherStac_pid = EPS.EvnPS_id and EPS.LeaveType_id = 4 and dbfeost.Lpu_id = :Lpu_id
								left join v_EvnOtherSection dbfeos (nolock) on dbfeos.EvnOtherSection_pid = EPS.EvnPS_id and EPS.LeaveType_id = 5  and dbfeos.Lpu_id = :Lpu_id
								inner join v_LeaveType dbflt (nolock) on dbflt.LeaveType_id = EPS.LeaveType_id and dbflt.Lpu_id = :Lpu_id
									and (
										ELV.EvnLeave_pid = EPS.EvnPS_id
										or dbfeol.EvnOtherLpu_pid = EPS.EvnPS_id
										or dbfed.EvnDie_pid = EPS.EvnPS_id
										or dbfeost.EvnOtherStac_pid = EPS.EvnPS_id
										or dbfeos.EvnOtherSection_pid = EPS.EvnPS_id
									)
								left join v_LpuSection dbfls (nolock) on dbfls.LpuSection_id = EPS.LpuSection_did
								left join v_LpuUnit dbflu (nolock) on dbflu.LpuUnit_id = dbfls.LpuUnit_id
							";
						break;
						case 'EvnStick':
							$query .= "
								inner join v_EvnStick EST (nolock) on EST.EvnStick_pid = EPS.EvnPS_id and EST.Lpu_id = :Lpu_id
							";
						break;
						case 'KvsPerson':
							if ($PS_prefix == 'PS2') { //требуется использование дополнительной таблицы
								if ($data['kvs_date_type'] == 2) {
									$query .= "
										inner join v_Person_all PS2 with (nolock) on PS2.Server_id = EPS.Server_id and PS2.PersonEvn_id = EPS.PersonEvn_id
									";
								} else {
									$query .= "
										inner join v_PersonState PS2 with (nolock) on PS2.Person_id = EPS.Person_id
									";
								}
							}
							$query .= "
								left join Sex with (nolock) on Sex.Sex_id = {$PS_prefix}.Sex_id
								left join SocStatus Soc with (nolock) on Soc.SocStatus_id = {$PS_prefix}.SocStatus_id
								left join PersonChild PCh with (nolock) on PCh.Person_id = {$PS_prefix}.Person_id
								left join YesNo IsInv with (nolock) on IsInv.YesNo_id = PCh.PersonChild_IsInvalid
								left join Diag InvD with (nolock) on InvD.Diag_id = PCh.Diag_id
								left join Polis Pol with (nolock) on Pol.Polis_id = {$PS_prefix}.Polis_id
								left join OMSSprTerr OMSST with (nolock) on OMSST.OMSSprTerr_id = Pol.OMSSprTerr_id
								left join PolisType PolTp with (nolock) on PolTp.PolisType_id = Pol.PolisType_id
								left join v_OrgSmo OS with (nolock) on OS.OrgSmo_id = Pol.OrgSmo_id
								left join v_Org OSO with (nolock) on OSO.Org_id = OS.Org_id
								left join v_Address_all UA with (nolock) on UA.Address_id = {$PS_prefix}.UAddress_id 
								left join v_Address_all PA with (nolock) on PA.Address_id = {$PS_prefix}.PAddress_id
								left join Document Doc with (nolock) on Doc.Document_id = {$PS_prefix}.Document_id 
								left join DocumentType DocTp with (nolock) on DocTp.DocumentType_id = Doc.DocumentType_id
								left join v_OrgDep OrgD with (nolock) on OrgD.OrgDep_id = Doc.OrgDep_id
							";
						break;
						case 'KvsPersonCard':
							$query .= "
								inner join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id
								left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
							";
						break;
						case 'KvsEvnDiag':
							$query .= "
								left join v_EvnSection ESEC with (nolock) on ESEC.EvnSection_pid = EPS.EvnPS_id and ESEC.Lpu_id = :Lpu_id
								inner join v_EvnDiagPS EDPS with (nolock) on EDPS.EvnDiagPS_pid = EPS.EvnPS_id 
									or EDPS.EvnDiagPS_pid = ESEC.EvnSection_id
								left join DiagSetClass DSC with (nolock) on DSC.DiagSetClass_id = EDPS.DiagSetClass_id
								left join DiagSetType DST with (nolock) on DST.DiagSetType_id = EDPS.DiagSetType_id 
								left join Diag with (nolock) on Diag.Diag_id = EDPS.Diag_id
								left join v_Lpu Lpu with (nolock) on (
									(Lpu.Lpu_id = EPS.Lpu_did and EDPS.DiagSetType_id = 1) or
									(Lpu.Lpu_id = EPS.Lpu_id and EDPS.DiagSetType_id = 2) or
									(Lpu.Lpu_id = ESEC.Lpu_id and EDPS.DiagSetType_id = 3)
								)
								left join v_LpuSection LS with (nolock) on (
									(LS.LpuSection_id = EPS.LpuSection_did and EDPS.DiagSetType_id = 1) or
									(LS.LpuSection_id = EPS.LpuSection_pid and EDPS.DiagSetType_id = 2) or
									(LS.LpuSection_id = ESEC.LpuSection_id and EDPS.DiagSetType_id = 3)
								)
							";
						break;
						case 'KvsEvnPS':
							$query .= "
								left join Diag with (nolock) on Diag.Diag_id = EPS.Diag_pid
								left join YesNo IsCont with (nolock) on IsCont.YesNo_id = EPS.EvnPS_IsCont
								left join PayType PT with (nolock) on PT.PayType_id = EPS.PayType_id
								left join PrehospDirect PD with (nolock) on PD.PrehospDirect_id = EPS.PrehospDirect_id
								left join LpuSection LS with (nolock) on LS.LpuSection_id = EPS.LpuSection_did
								left join Lpu with (nolock) on EPS.Lpu_did = Lpu.Lpu_id
								left join Org with (nolock) on coalesce(Lpu.Org_id, EPS.Org_did, EPS.OrgMilitary_did) = Org.Org_id
								/*
								outer apply (
									Select Org.Org_Code, Org.Org_Name from Org with (nolock)
									left join Lpu with (nolock) on Lpu.Org_id = Org.Org_id 
									where 
									coalesce(EPS.Org_did, EPS.OrgMilitary_did) = Org.Org_id
									or EPS.Lpu_did = Lpu.Lpu_id
								) Org
								*/
								left join PrehospArrive PA with (nolock) on PA.PrehospArrive_id = EPS.PrehospArrive_id
								left join Lpu LpuF with (nolock) on LpuF.Org_id = EPS.Org_did
								left join YesNo IsFond with (nolock) on IsFond.YesNo_id = LpuF.Lpu_IsOMS
								left join Diag DiagD with (nolock) on DiagD.Diag_id = EPS.Diag_did
								left join PrehospToxic Toxic with (nolock) on Toxic.PrehospToxic_id = EPS.PrehospToxic_id
								left join v_PrehospTrauma Trauma with (nolock) on Trauma.PrehospTrauma_id = EPS.PrehospTrauma_id
								left join PrehospType PType with (nolock) on PType.PrehospType_id = EPS.PrehospType_id
								left join YesNo IsUnlaw with (nolock) on IsUnlaw.YesNo_id = EPS.EvnPS_IsUnlaw
								left join YesNo IsUnport with (nolock) on IsUnport.YesNo_id = EPS.EvnPS_IsUnport
								left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EPS.MedPersonal_pid
									and MP.Lpu_id = EPS.Lpu_id
								left join v_LpuSection pLS with (nolock) on pLS.LpuSection_id = EPS.LpuSection_pid
								left join YesNo IsImperHosp with (nolock) on IsImperHosp.YesNo_id = EPS.EvnPS_IsImperHosp
								left join YesNo IsShortVolume with (nolock) on IsShortVolume.YesNo_id = EPS.EvnPS_IsShortVolume
								left join YesNo IsWrongCure with (nolock) on IsWrongCure.YesNo_id = EPS.EvnPS_IsWrongCure
								left join YesNo IsDiagMismatch with (nolock) on IsDiagMismatch.YesNo_id = EPS.EvnPS_IsDiagMismatch
							";
						break;
						case 'KvsEvnSection':
							$query .= "
								inner join v_EvnSection as ESEC with (nolock) on ESEC.EvnSection_pid = EPS.EvnPS_id and ESEC.Lpu_id = :Lpu_id
								inner join LpuSection LS with (nolock) on LS.LpuSection_id = ESEC.LpuSection_id
								inner join LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
									-- данное условие не нужно, расхождение может быть только на тестовой, поскольку данные изначально кривые - на рабочей все отлично 
									-- or LU.LpuUnit_id = (select top 1 LS1.LpuUnit_id from LpuSection LS1 with (nolock) where LS1.LpuSection_id = LS.LpuSection_pid)
								inner join LpuUnitType LUT with (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
								inner join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = ESEC.MedPersonal_id
									and MP.Lpu_id = EPS.Lpu_id
								left join Diag Diag with (nolock) on Diag.Diag_id = ESEC.Diag_id
								left join PayType PT with (nolock) on PT.PayType_id = ESEC.PayType_id
								left join TariffClass TC with (nolock) on TC.TariffClass_id = ESEC.TariffClass_id
								left join v_MesOld Mes with (nolock) on Mes.Mes_id = ESEC.Mes_id
							";
						break;
						case 'KvsNarrowBed':
							$query .= "
								inner join v_EvnSection as ESEC with (nolock) on ESEC.EvnSection_pid = EPS.EvnPS_id and ESEC.Lpu_id = :Lpu_id
								inner join v_EvnSectionNarrowBed as ESNB with (nolock) on ESNB.EvnSectionNarrowBed_pid = ESEC.EvnSection_id and ESNB.Lpu_id = :Lpu_id
								left join LpuSection LS with (nolock) on LS.LpuSection_id = ESNB.LpuSection_id
							";
						break;
						case 'KvsEvnUsluga':
							$query .= "
								inner join v_EvnUsluga as EU with (nolock) on EU.EvnUsluga_rid = EPS.EvnPS_id and EU.Lpu_id = :Lpu_id
								left join v_EvnUslugaOper EUO with (nolock) on EUO.EvnUslugaOper_id = EU.EvnUsluga_id
								left join OperType OT with (nolock) on OT.OperType_id = EUO.OperType_id
								left join OperDiff OD with (nolock) on OD.OperDiff_id = EUO.OperDiff_id
								left join YesNo IsEndoskop with (nolock) on IsEndoskop.YesNo_id = EUO.EvnUslugaOper_IsEndoskop
								left join YesNo IsLazer with (nolock) on IsLazer.YesNo_id = EUO.EvnUslugaOper_IsLazer
								left join YesNo IsKriogen with (nolock) on IsKriogen.YesNo_id = EUO.EvnUslugaOper_IsKriogen
								left join v_PayType as PT with (nolock) on PT.PayType_id = EU.PayType_id
								left join v_UslugaComplex as U with (nolock) on U.UslugaComplex_id = EU.UslugaComplex_id
								left join v_UslugaPlace as UP with (nolock) on UP.UslugaPlace_id = EU.UslugaPlace_id
								outer apply (
									select top 1 MedPersonal_TabCode, Person_Fio
									from v_MedPersonal with (nolock)
									where MedPersonal_id = EU.MedPersonal_id
										and Lpu_id = EPS.Lpu_id
								) MP
								left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EU.LpuSection_uid
								left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EU.Lpu_uid
								left join v_Org Org with (nolock) on Org.Org_id = EU.Org_uid
								left join v_UslugaClass UC with (nolock) on UC.UslugaClass_SysNick = EU.EvnClass_SysNick
							";
						break;
						case 'KvsEvnUslugaOB':
							$query .= "
								left join v_EvnUsluga as EU with (nolock) on EU.EvnUsluga_rid = EPS.EvnPS_id and EU.Lpu_id = :Lpu_id
								left join v_EvnUslugaOper EUO with (nolock) on EUO.EvnUslugaOper_id = EU.EvnUsluga_id
								inner join v_EvnUslugaOperBrig EUOB with (nolock) on EUOB.EvnUslugaOper_id = EUO.EvnUslugaOper_id
								left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EUOB.MedPersonal_id
									and MP.Lpu_id = EPS.Lpu_id
								left join v_SurgType ST with (nolock) on ST.SurgType_id = EUOB.SurgType_id
							";
						break;
						case 'KvsEvnUslugaAn':
							$query .= "
								left join v_EvnUsluga as EU with (nolock) on EU.EvnUsluga_rid = EPS.EvnPS_id and EU.Lpu_id = :Lpu_id
								left join v_EvnUslugaOper EUO with (nolock) on EUO.EvnUslugaOper_id = EU.EvnUsluga_id
								inner join v_EvnUslugaOperAnest EUOA with (nolock) on EUOA.EvnUslugaOper_id = EUO.EvnUslugaOper_id
								left join v_AnesthesiaClass AC with (nolock) on AC.AnesthesiaClass_id = EUOA.AnesthesiaClass_id
							";
						break;
						case 'KvsEvnUslugaOsl':
							$query .= "
								left join v_EvnUsluga as EU with (nolock) on EU.EvnUsluga_rid = EPS.EvnPS_id and EU.Lpu_id = :Lpu_id
								inner join v_EvnAgg EA with (nolock) on EA.EvnAgg_pid = EU.EvnUsluga_id and EA.Lpu_id = :Lpu_id
								left join v_AggType AT with (nolock) on AT.AggType_id = EA.AggType_id
								left join v_AggWhen AW with (nolock) on AW.AggWhen_id = EA.AggWhen_id
							";
						break;
						case 'KvsEvnDrug':
							$query .= "
								inner join v_EvnDrug as ED with (nolock) on ED.EvnDrug_rid = EPS.EvnPS_id and ED.Lpu_id = :Lpu_id
								inner join v_DocumentUcOstat_Lite Part with (nolock) on Part.DocumentUcStr_id = ED.DocumentUcStr_oid
								inner join DocumentUcStr DUS with (nolock) on DUS.DocumentUcStr_id = ED.DocumentUcStr_id
								left join v_LpuSection LS with (nolock) on LS.LpuSection_id = ED.LpuSection_id
								left join rls.v_Drug Drug with (nolock) on Drug.Drug_id = ED.Drug_id
								left join v_Mol Mol with (nolock) on Mol.Mol_id = ED.Mol_id
							";
						break;
						case 'KvsEvnLeave':
							$query .= "
								inner join v_EvnSection as ESEC with (nolock) on ESEC.EvnSection_pid = EPS.EvnPS_id and ESEC.Lpu_id = :Lpu_id
								left join v_EvnLeave ELV with (nolock) on ELV.EvnLeave_pid = ESEC.EvnSection_id and ESEC.LeaveType_id = 1 and ELV.Lpu_id = :Lpu_id
								left join v_EvnOtherLpu EOLpu with (nolock) on EOLpu.EvnOtherLpu_pid = ESEC.EvnSection_id and ESEC.LeaveType_id = 2 and EOLpu.Lpu_id = :Lpu_id
								left join v_EvnDie EDie with (nolock) on EDie.EvnDie_pid = ESEC.EvnSection_id and ESEC.LeaveType_id = 3 and EDie.Lpu_id = :Lpu_id
								left join v_EvnOtherStac EOStac with (nolock) on EOStac.EvnOtherStac_pid = ESEC.EvnSection_id and ESEC.LeaveType_id = 4 and EOStac.Lpu_id = :Lpu_id
								left join v_EvnOtherSection EOSect with (nolock) on EOSect.EvnOtherSection_pid = ESEC.EvnSection_id and ESEC.LeaveType_id = 5 and EOSect.Lpu_id = :Lpu_id
								inner join v_LeaveType LType with (nolock) on LType.LeaveType_id = ESEC.LeaveType_id 
									and (
										ELV.EvnLeave_pid = ESEC.EvnSection_id
										or EOLpu.EvnOtherLpu_pid = ESEC.EvnSection_id
										or EDie.EvnDie_pid = ESEC.EvnSection_id
										or EOStac.EvnOtherStac_pid = ESEC.EvnSection_id
										or EOSect.EvnOtherSection_pid = ESEC.EvnSection_id
									)									
								left join ResultDesease RD with (nolock) on (
									RD.ResultDesease_id = ELV.ResultDesease_id or
									RD.ResultDesease_id = EOLpu.ResultDesease_id or
									RD.ResultDesease_id = EOStac.ResultDesease_id or
									RD.ResultDesease_id = EOSect.ResultDesease_id
								)
								left join LeaveCause LC with (nolock) on (
									LC.LeaveCause_id = ELV.LeaveCause_id or
									LC.LeaveCause_id = EOLpu.LeaveCause_id or
									LC.LeaveCause_id = EOStac.LeaveCause_id or
									LC.LeaveCause_id = EOSect.LeaveCause_id
								)
								left join YesNo IsAmbul with (nolock) on IsAmbul.YesNo_id = ELV.EvnLeave_IsAmbul
								left join v_Org EOLpuL with (nolock) on EOLpuL.Org_id = EOLpu.Org_oid
								left join v_LpuUnitType EOStacLUT with (nolock) on EOStacLUT.LpuUnitType_id = EOStac.LpuUnitType_oid
								left join v_LpuSection LS with (nolock) on (
									LS.LpuSection_id = EOStac.LpuSection_oid or
									LS.LpuSection_id = EOSect.LpuSection_oid
								)
								left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EDie.MedPersonal_aid
									and MP.Lpu_id = EPS.Lpu_id
								left join Diag DieDiag with (nolock) on DieDiag.Diag_id = EDie.Diag_aid
							";
						break;
						case 'KvsEvnStick':
							$query .= "
								inner join v_EvnStick EST with (nolock) on EST.EvnStick_pid = EPS.EvnPS_id and EST.Lpu_id = :Lpu_id
								left join StickOrder SO with (nolock) on SO.StickOrder_id = EST.StickOrder_id
								left join StickCause SC with (nolock) on SC.StickCause_id = EST.StickCause_id
								left join Sex with (nolock) on Sex.Sex_id = EST.Sex_id
								left join StickRegime SR with (nolock) on SR.StickRegime_id = EST.StickRegime_id
								left join StickLeaveType SLT with (nolock) on SLT.StickLeaveType_id = EST.StickLeaveType_id
								left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EST.MedPersonal_id and MP.Lpu_id = EST.Lpu_id
								left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EST.Lpu_id
								left join Diag D1 with (nolock) on D1.Diag_id = EST.Diag_pid
								-- left join Diag D2 with (nolock) on D2.Diag_id = EST.Diag_id
							";
						break;
					}
					
					if (isset($data['and_kvsperson']) && $data['and_kvsperson']) { //при комбинировании с таблицой персон добавляем дополнительные поля
						if ($PS_prefix == 'PS2') { //требуется использование дополнительной таблицы
							if ($data['kvs_date_type'] == 2) {
								$query .= "
									left join v_Person_all PS2 with (nolock) on PS2.Server_id = EPS.Server_id and PS2.PersonEvn_id = EPS.PersonEvn_id
								";
							} else {
								$query .= "
									left join v_PersonState PS2 with (nolock) on PS2.Person_id = EPS.Person_id
								";
							}
						}
						$query .= "
							left join Sex PrsSex with (nolock) on PrsSex.Sex_id = {$PS_prefix}.Sex_id
							left join SocStatus PrsSoc with (nolock) on PrsSoc.SocStatus_id = {$PS_prefix}.SocStatus_id
							left join PersonChild PrsPCh with (nolock) on PrsPCh.Person_id = {$PS_prefix}.Person_id
							left join YesNo PrsIsInv with (nolock) on PrsIsInv.YesNo_id = PrsPCh.PersonChild_IsInvalid
							left join Diag PrsInvD with (nolock) on PrsInvD.Diag_id = PrsPCh.Diag_id
							left join Polis PrsPol with (nolock) on PrsPol.Polis_id = {$PS_prefix}.Polis_id
							left join OMSSprTerr PrsOMSST with (nolock) on PrsOMSST.OMSSprTerr_id = PrsPol.OMSSprTerr_id
							left join PolisType PrsPolTp with (nolock) on PrsPolTp.PolisType_id = PrsPol.PolisType_id
							left join v_OrgSmo PrsOS with (nolock) on PrsOS.OrgSmo_id = PrsPol.OrgSmo_id
							left join v_Org PrsOSO with (nolock) on PrsOSO.Org_id = PrsOS.Org_id
							left join v_Address_all PrsUA with (nolock) on PrsUA.Address_id = {$PS_prefix}.UAddress_id 
							left join v_Address_all PrsPA with (nolock) on PrsPA.Address_id = {$PS_prefix}.PAddress_id
							left join Document PrsDoc with (nolock) on PrsDoc.Document_id = {$PS_prefix}.Document_id 
							left join DocumentType PrsDocTp with (nolock) on PrsDocTp.DocumentType_id = PrsDoc.DocumentType_id
							left join v_OrgDep PrsOrgD with (nolock) on PrsOrgD.OrgDep_id = PrsDoc.OrgDep_id
						";
					}
				}
				else {
					// Обычный поиск, не выгрузка DBF
					switch ( $data['SearchFormType'] ) {
						case 'EvnPS':
							$query .= "
								-- этот джойн не нужен видимо (с) Night, 2011-06-22 
								--left join v_Lpu LpuD with (nolock) on LpuD.Lpu_id = EPS.Lpu_did
								-- Тарас сказал что Index и Count - это почти всегда правильно (с) Night, 2011-06-22 
								left join v_EvnSection EPSLastES with (nolock) on EPSLastES.EvnSection_pid = EPS.EvnPS_id and EPSLastES.EvnSection_Index = EPSLastES.EvnSection_Count-1 and EPSLastES.Lpu_id = :Lpu_id
								left join LpuSection LStmp with (nolock) on LStmp.LpuSection_id = EPSLastES.LpuSection_id
								left join v_Diag Dtmp with (nolock) on Dtmp.Diag_id = EPSLastES.Diag_id
								left join LeaveType LT with (nolock) on LT.LeaveType_id = EPSLastES.LeaveType_id
								left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EPSLastES.MedPersonal_id
									and MP.Lpu_id = :Lpu_id
								left join v_Diag DP with (nolock) on DP.Diag_id = EPS.Diag_pid
								left join LpuSection LS with (nolock) on LS.LpuSection_id = EPS.LpuSection_id
								left join v_PrehospWaifRefuseCause pwrc on pwrc.PrehospWaifRefuseCause_id = EPS.PrehospWaifRefuseCause_id
								left join LpuUnit with (nolock) on LpuUnit.LpuUnit_id = LStmp.LpuUnit_id 
								left join LpuUnitType with (nolock) on LpuUnitType.LpuUnitType_id = LpuUnit.LpuUnitType_id 
								left join PayType dbfpayt (nolock) on dbfpayt.PayType_id = EPS.PayType_id
							";
						break;

						case 'EvnSection':
							$query .= "
								inner join v_EvnSection as ESEC with (nolock) on ESEC.EvnSection_pid = EPS.EvnPS_id
								left join v_Diag Dtmp with (nolock) on Dtmp.Diag_id = ESEC.Diag_id
								left join LpuSection LS with (nolock) on LS.LpuSection_id = ESEC.LpuSection_id
								left join LpuSectionWard LSW with (nolock) on LSW.LpuSectionWard_id = ESEC.LpuSectionWard_id
								left join PayType as PT with (nolock) on PT.PayType_id = ESEC.PayType_id
								outer apply (
									select top 1 v_MedPersonal.Person_Fio,MedStaffFact.MedStaffFact_id
									from v_MedPersonal with (nolock)
                                    inner join v_MedStaffFact MedStaffFact (nolock) on MedStaffFact.MedPersonal_id = v_MedPersonal.MedPersonal_id and MedStaffFact.LpuSection_id = ESEC.LpuSection_id
									where v_MedPersonal.MedPersonal_id = ESEC.MedPersonal_id
								) MP
								left join v_MesOld as MES with (nolock) on MES.Mes_id = ESEC.Mes_id
								left join LeaveType LT with (nolock) on LT.LeaveType_id = ESEC.LeaveType_id
							";
						break;
					}

					if ( $data['PersonPeriodicType_id'] == 3 ) {
						$query .= "
							outer apply (
								select top 1 PersonEvn_id, Server_id
								from v_PersonState with (nolock)
								where Person_id = PS.Person_id
							) CPS
						";

						switch ( $data['SearchFormType'] ) {
							case 'EvnPS':
								$filter .= "
									and PS.PersonEvn_id = ISNULL(EPSLastES.PersonEvn_id, CPS.PersonEvn_id)
									and PS.Server_id = ISNULL(EPSLastES.Server_id, CPS.Server_id)
								";
							break;

							case 'EvnSection':
								$filter .= "
									and PS.PersonEvn_id = ISNULL(ESEC.PersonEvn_id, CPS.PersonEvn_id)
									and PS.Server_id = ISNULL(ESEC.Server_id, CPS.Server_id)
								";
							break;
						}
					}
				}
				// $query .= " inner join v_EvnPS EPS on EPS.Person_id = PS.Person_id and EPS.Lpu_id = :Lpu_id";

				// Госпитализация
				if ( isset($data['EvnDirection_Num']) ) {
					$filter .= " and EPS.EvnDirection_Num = :EvnDirection_Num";
					$queryParams['EvnDirection_Num'] = $data['EvnDirection_Num'];
				}

				// Госпитализация
				if ( isset($data['EvnDirection_setDate_Range'][0]) ) {
					$filter .= " and EPS.EvnDirection_setDT >= cast(:EvnDirection_setDate_Range_0 as datetime)";
					$queryParams['EvnDirection_setDate_Range_0'] = $data['EvnDirection_setDate_Range'][0];
				}

				// Госпитализация
				if ( isset($data['EvnDirection_setDate_Range'][1]) ) {
					$filter .= " and EPS.EvnDirection_setDT <= cast(:EvnDirection_setDate_Range_1 as datetime)";
					$queryParams['EvnDirection_setDate_Range_1'] = $data['EvnDirection_setDate_Range'][1];
				}

				// Госпитализация
				if ( isset($data['EvnPS_disDate_Range'][0]) ) {
					$filter .= " and EPS.EvnPS_disDate >= :EvnPS_disDate_Range_0";
					$queryParams['EvnPS_disDate_Range_0'] = $data['EvnPS_disDate_Range'][0];
				}

				// Госпитализация
				if ( isset($data['EvnPS_disDate_Range'][1]) ) {
					$filter .= " and EPS.EvnPS_disDate <= :EvnPS_disDate_Range_1";
					$queryParams['EvnPS_disDate_Range_1'] = $data['EvnPS_disDate_Range'][1];
				}

				// Госпитализация
				if ( isset($data['EvnPS_HospCount_Max']) ) {
					$filter .= " and EPS.EvnPS_HospCount <= :EvnPS_HospCount_Max";
					$queryParams['EvnPS_HospCount_Max'] = $data['EvnPS_HospCount_Max'];
				}

				// Госпитализация
				if ( isset($data['EvnPS_HospCount_Min']) ) {
					$filter .= " and EPS.EvnPS_HospCount >= :EvnPS_HospCount_Min";
					$queryParams['EvnPS_HospCount_Min'] = $data['EvnPS_HospCount_Min'];
				}

				// Госпитализация
				if ( isset($data['EvnPS_IsUnlaw']) ) {
					$filter .= " and EPS.EvnPS_IsUnlaw = :EvnPS_IsUnlaw";
					$queryParams['EvnPS_IsUnlaw'] = $data['EvnPS_IsUnlaw'];
				}

				// Госпитализация
				if ( isset($data['EvnPS_IsUnport']) ) {
					$filter .= " and EPS.EvnPS_IsUnport = :EvnPS_IsUnport";
					$queryParams['EvnPS_IsUnport'] = $data['EvnPS_IsUnport'];
				}

				// Госпитализация
				if ( !empty($data['EvnPS_IsWithoutDirection']) ) {
					$filter .= " and ISNULL(EvnDirection_id, 0) = 0";
				}

				// Госпитализация
				if ( isset($data['EvnPS_NumCard']) ) {
					$filter .= " and EPS.EvnPS_NumCard = :EvnPS_NumCard";
					$queryParams['EvnPS_NumCard'] = $data['EvnPS_NumCard'];
				}

				// Госпитализация
				if ( isset($data['EvnPS_setDate_Range'][0]) ) {
					$filter .= " and EPS.EvnPS_setDate >= :EvnPS_setDate_Range_0";
					$queryParams['EvnPS_setDate_Range_0'] = $data['EvnPS_setDate_Range'][0];
				}

				// Госпитализация
				if ( isset($data['EvnPS_setDate_Range'][1]) ) {
					$filter .= " and EPS.EvnPS_setDate <= :EvnPS_setDate_Range_1";
					$queryParams['EvnPS_setDate_Range_1'] = $data['EvnPS_setDate_Range'][1];
				}

				// Госпитализация
				if ( isset($data['Lpu_IsFondHolder']) ) {
					$filter .= " and exists(select top 1 1 from LpuFondHolder with (nolock) where Lpu_id = EPS.Lpu_did and ISNULL(LpuFondHolder_IsEnabled, 2) = :Lpu_IsFondHolder)";
					$queryParams['Lpu_IsFondHolder'] = $data['Lpu_IsFondHolder'];
				}

				// Госпитализация
				if ( isset($data['LpuSection_did']) ) {
					$filter .= " and EPS.LpuSection_did = :LpuSection_did";
					$queryParams['LpuSection_did'] = $data['LpuSection_did'];
				}

				// Госпитализация
				if ( isset($data['Lpu_did']) ) {
					$filter .= " and EPS.Lpu_did = :Lpu_did";
					$queryParams['Lpu_did'] = $data['Lpu_did'];
				}
				else if ( isset($data['OrgMilitary_did']) ) {
					$filter .= " and EPS.OrgMilitary_did = :OrgMilitary_did";
					$queryParams['OrgMilitary_did'] = $data['OrgMilitary_did'];
				}
				else if ( isset($data['Org_did']) ) {
					$filter .= " and EPS.Org_did = :Org_did";
					$queryParams['Org_did'] = $data['Org_did'];
				}

				// Госпитализация
				if ( isset($data['PayType_id']) ) {
					switch ( $data['SearchFormType'] ) {
						case 'EvnPS':
							$filter .= " and EPS.PayType_id = :PayType_id";
						break;

						case 'EvnSection':
							$filter .= " and ESEC.PayType_id = :PayType_id";
						break;
					}

					$queryParams['PayType_id'] = $data['PayType_id'];
				}

				// Госпитализация
				if ( isset($data['PrehospArrive_id']) ) {
					$filter .= " and EPS.PrehospArrive_id = :PrehospArrive_id";
					$queryParams['PrehospArrive_id'] = $data['PrehospArrive_id'];
				}

				// Госпитализация
				if ( isset($data['PrehospDirect_id']) ) {
					if ( $data['PrehospDirect_id'] > 0 ) {
						$filter .= " and EPS.PrehospDirect_id = :PrehospDirect_id";
						$queryParams['PrehospDirect_id'] = $data['PrehospDirect_id'];
					}
					else if ( $data['PrehospDirect_id'] == -1 ) {
						$filter .= " and EPS.PrehospDirect_id is null";
					}
				}

				// Госпитализация
				if ( isset($data['PrehospToxic_id']) ) {
					$filter .= " and EPS.PrehospToxic_id = :PrehospToxic_id";
					$queryParams['PrehospToxic_id'] = $data['PrehospToxic_id'];
				}

				// Госпитализация
				if ( isset($data['PrehospTrauma_id']) ) {
					$filter .= " and EPS.PrehospTrauma_id = :PrehospTrauma_id";
					$queryParams['PrehospTrauma_id'] = $data['PrehospTrauma_id'];
				}

				// Госпитализация
				if ( isset($data['PrehospType_id']) ) {
					$filter .= " and EPS.PrehospType_id = :PrehospType_id";
					$queryParams['PrehospType_id'] = $data['PrehospType_id'];
				}

				// Госпитализация
				if ( isset($data['EvnPS_TimeDesease_Max']) ) {
					$filter .= " and EPS.EvnPS_TimeDesease <= :EvnPS_TimeDesease_Max";
					$queryParams['EvnPS_TimeDesease_Max'] = $data['EvnPS_TimeDesease_Max'];
				}

				// Госпитализация
				if ( isset($data['EvnPS_TimeDesease_Min']) ) {
					$filter .= " and EPS.EvnPS_TimeDesease >= :EvnPS_TimeDesease_Min";
					$queryParams['EvnPS_TimeDesease_Min'] = $data['EvnPS_TimeDesease_Min'];
				}

				// Госпитализация
				if ( isset($data['LpuSection_hid']) ) {
					$filter .= " and EPS.LpuSection_id = :LpuSection_hid";
					$queryParams['LpuSection_hid'] = $data['LpuSection_hid'];
				}

				// Госпитализация
				if ( isset($data['PrehospWaifRefuseCause_id']) ) {
					$filter .= " and EPS.PrehospWaifRefuseCause_id = :PrehospWaifRefuseCause_id";
					$queryParams['PrehospWaifRefuseCause_id'] = $data['PrehospWaifRefuseCause_id'];
				}
				
				// Госпитализация
				if ( isset($data['LpuUnitType_did']) ) {
					$query .= "
						outer apply (
							select top 1 LU_tmp.LpuUnitType_id
							from v_EvnSection ES_tmp with (nolock)
								inner join LpuSection LS_tmp with (nolock) on LS_tmp.LpuSection_id = ES_tmp.LpuSection_id
								inner join LpuUnit LU_tmp with (nolock) on LU_tmp.LpuUnit_id = LS_tmp.LpuUnit_id
							where ES_tmp.EvnSection_rid = EPS.EvnPS_id
								and ES_tmp.Lpu_id = :Lpu_id
							order by ES_tmp.EvnSection_setDT
						) ESHosp
					";
					$filter .= " and ESHosp.LpuUnitType_id = :LpuUnitType_did";
					$queryParams['LpuUnitType_did'] = $data['LpuUnitType_did'];
				}

				if ( isset($data['LpuSection_cid']) || isset($data['LpuBuilding_cid']) || isset($data['MedPersonal_cid']) || isset($data['EvnSection_disDate_Range'][0]) ||
					isset($data['EvnSection_isPaid']) ||
					isset($data['EvnSection_disDate_Range'][1]) || isset($data['EvnSection_setDate_Range'][0]) ||
					isset($data['EvnSection_setDate_Range'][1]) || isset($data['DiagSetClass_id']) || isset($data['DiagSetType_id']) ||
					isset($data['Diag_Code_From']) || isset($data['Diag_Code_To']) || isset($data['MedStaffFact_id'])
				) {
					switch ( $data['SearchFormType'] ) {
						case 'EvnPS':
							$filter .= " and exists (
								select top 1 1
								from v_EvnSection ES with (nolock)
								where ES.EvnSection_rid = EPS.EvnPS_id
									and ES.Lpu_id = :Lpu_id
							";
                            // Добавление фильтра по фио врача, выписавшего пациента
                            // Oplachko
                            if ( !empty($data['MedStaffFact_id']) ) {
                                $filter .= " and MP.MedStaffFact_id = :MedStaffFact_id";
                                $queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
                            }
							// Лечение
							if ( isset($data['EvnSection_disDate_Range'][0]) ) {
								$filter .= " and ES.EvnSection_disDate >= :EvnSection_disDate_Range_0";
								$queryParams['EvnSection_disDate_Range_0'] = $data['EvnSection_disDate_Range'][0];
							}

							// Лечение
							if ( isset($data['EvnSection_disDate_Range'][1]) ) {
								$filter .= " and ES.EvnSection_disDate <= :EvnSection_disDate_Range_1";
								$queryParams['EvnSection_disDate_Range_1'] = $data['EvnSection_disDate_Range'][1];
							}
							
							if ( !empty($data['EvnSection_isPaid']) ) {
								$filter .= " and ISNULL(ES.EvnSection_isPaid,1) = :EvnSection_isPaid";
								$queryParams['EvnSection_isPaid'] = $data['EvnSection_isPaid'];
							}

							// Лечение
							if ( isset($data['EvnSection_setDate_Range'][0]) ) {
								$filter .= " and ES.EvnSection_setDate >= :EvnSection_setDate_Range_0";
								$queryParams['EvnSection_setDate_Range_0'] = $data['EvnSection_setDate_Range'][0];
							}

							// Лечение
							if ( isset($data['EvnSection_setDate_Range'][1]) ) {
								$filter .= " and ES.EvnSection_setDate <= :EvnSection_setDate_Range_1";
								$queryParams['EvnSection_setDate_Range_1'] = $data['EvnSection_setDate_Range'][1];
							}

							// Лечение
							if ( isset($data['LpuSection_cid']) ) {
								$filter .= " and ES.LpuSection_id = :LpuSection_id";
								$queryParams['LpuSection_id'] = $data['LpuSection_cid'];
							} elseif ( isset($data['LpuBuilding_cid']) ) {
								$filter .= " and exists (
									select
										1
									from LpuSection LS with (nolock)
										left join LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
									where
										LU.LpuBuilding_id = :LpuBuilding_cid AND
										LS.LpuSection_id = ES.LpuSection_id
									)";
								$queryParams['LpuBuilding_cid'] = $data['LpuBuilding_cid'];
							}

							// Лечение
							if ( isset($data['MedPersonal_cid']) ) {
								$filter .= " and ES.MedPersonal_id = :MedPersonal_id";
								$queryParams['MedPersonal_id'] = $data['MedPersonal_cid'];
							}

							$filter .= ")";

							if ( isset($data['DiagSetClass_id']) || isset($data['DiagSetType_id']) || isset($data['Diag_Code_From']) || isset($data['Diag_Code_To']) ) {
								$filter .= " and exists (
									select
										1
									from v_EvnSection ES2 with (nolock)
										left join v_EvnDiagPS EDPS with (nolock) on EDPS.EvnDiagPS_pid = ES2.EvnSection_id
										left join Diag DiagES with (nolock) on DiagES.Diag_id = ES2.Diag_id
										left join Diag DiagEDPS with (nolock) on DiagEDPS.Diag_id = EDPS.Diag_id
									where
										ES2.EvnSection_rid = EPS.EvnPS_id
										 and ES2.Lpu_id = :Lpu_id
								";

								if ( isset($data['DiagSetClass_id']) ) {
									if ( $data['DiagSetClass_id'] == 1 ) {
										if ( isset($data['Diag_Code_From']) ) {
											$filter .= " and DiagES.Diag_Code >= :Diag_Code_From";
											$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
										}

										if ( isset($data['Diag_Code_To']) ) {
											$filter .= " and DiagES.Diag_Code <= :Diag_Code_To";
											$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
										}

										//if ( isset($data['DiagSetType_id']) && $data['DiagSetType_id'] != 3 ) {
										//	$filter .= " and (1 = 0)";
										//}
									}
									else {
										$filter .= " and EDPS.DiagSetClass_id = :DiagSetClass_id";
										$queryParams['DiagSetClass_id'] = $data['DiagSetClass_id'];

										if ( isset($data['DiagSetType_id']) ) {
											$filter .= " and EDPS.DiagSetType_id = :DiagSetType_id";
											$queryParams['DiagSetType_id'] = $data['DiagSetType_id'];
										}

										if ( isset($data['Diag_Code_From']) ) {
											$filter .= " and DiagEDPS.Diag_Code >= :Diag_Code_From";
											$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
										}

										if ( isset($data['Diag_Code_To']) ) {
											$filter .= " and DiagEDPS.Diag_Code <= :Diag_Code_To";
											$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
										}
									}
								}
								else {
									if ( isset($data['DiagSetType_id']) ) {
										$filter .= " and EDPS.DiagSetType_id = :DiagSetType_id";
										$queryParams['DiagSetType_id'] = $data['DiagSetType_id'];
									}

									$filter .= " and (((1 = 1)";

									if ( isset($data['Diag_Code_From']) ) {
										$filter .= " and DiagES.Diag_Code >= :Diag_Code_From";
										$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
									}

									if ( isset($data['Diag_Code_To']) ) {
										$filter .= " and DiagES.Diag_Code <= :Diag_Code_To";
										$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
									}

									$filter .= ") or ((1 = 1)";

									if ( isset($data['Diag_Code_From']) ) {
										$filter .= " and DiagEDPS.Diag_Code >= :Diag_Code_From";
										$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
									}

									if ( isset($data['Diag_Code_To']) ) {
										$filter .= " and DiagEDPS.Diag_Code <= :Diag_Code_To";
										$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
									}

									$filter .= "))";
								}

								$filter .= ")";
							}
						break;

						case 'EvnSection':

                            // Добавление фильтра по фио врача, выписавшего пациента
                            // Oplachko
                            if ( !empty($data['MedStaffFact_id']) ) {
                                $filter .= " and MP.MedStaffFact_id = :MedStaffFact_id";
                                $queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
                            }

							// Лечение
							if ( !empty($data['EvnSection_disDate_Range'][0]) ) {
								$filter .= " and ESEC.EvnSection_disDate >= :EvnSection_disDate_Range_0";
								$queryParams['EvnSection_disDate_Range_0'] = $data['EvnSection_disDate_Range'][0];
							}
							
							// Лечение
							if ( !empty($data['EvnSection_disDate_Range'][1]) ) {
								$filter .= " and ESEC.EvnSection_disDate <= :EvnSection_disDate_Range_1";
								$queryParams['EvnSection_disDate_Range_1'] = $data['EvnSection_disDate_Range'][1];
							}

							if ( !empty($data['EvnSection_isPaid']) ) {
								$filter .= " and ISNULL(ESEC.EvnSection_isPaid,1) = :EvnSection_isPaid";
								$queryParams['EvnSection_isPaid'] = $data['EvnSection_isPaid'];
							}
							
							// Лечение
							if ( !empty($data['EvnSection_setDate_Range'][0]) ) {
								$filter .= " and ESEC.EvnSection_setDate >= :EvnSection_setDate_Range_0";
								$queryParams['EvnSection_setDate_Range_0'] = $data['EvnSection_setDate_Range'][0];
							}

							// Лечение
							if ( !empty($data['EvnSection_setDate_Range'][1]) ) {
								$filter .= " and ESEC.EvnSection_setDate <= :EvnSection_setDate_Range_1";
								$queryParams['EvnSection_setDate_Range_1'] = $data['EvnSection_setDate_Range'][1];
							}

							// Лечение
							if ( !empty($data['LpuSection_cid']) ) {
								$filter .= " and ESEC.LpuSection_id = :LpuSection_id";
								$queryParams['LpuSection_id'] = $data['LpuSection_cid'];
							}
							else if ( !empty($data['LpuBuilding_cid']) ) {
								$filter .= " and exists (
									select
										1
									from LpuSection LStmp with (nolock)
										left join LpuUnit LUtmp with (nolock) on LUtmp.LpuUnit_id = LStmp.LpuUnit_id
									where
										LUtmp.LpuBuilding_id = :LpuBuilding_cid
										and LStmp.LpuSection_id = ESEC.LpuSection_id
								)";
								$queryParams['LpuBuilding_cid'] = $data['LpuBuilding_cid'];
							}

							// Лечение
							if ( !empty($data['MedPersonal_cid']) ) {
								$filter .= " and ESEC.MedPersonal_id = :MedPersonal_id";
								$queryParams['MedPersonal_id'] = $data['MedPersonal_cid'];
							}

							if ( !empty($data['DiagSetClass_id']) || !empty($data['DiagSetType_id']) || !empty($data['Diag_Code_From']) || !empty($data['Diag_Code_To']) ) {
								if ( !empty($data['DiagSetClass_id']) ) {
									if ( $data['DiagSetClass_id'] == 1 ) {
										if ( !empty($data['Diag_Code_From']) ) {
											$filter .= " and Dtmp.Diag_Code >= :Diag_Code_From";
											$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
										}

										if ( !empty($data['Diag_Code_To']) ) {
											$filter .= " and Dtmp.Diag_Code <= :Diag_Code_To";
											$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
										}
									}
									else {
										$filter .= " and exists (
											select
												EvnDiagPS_id
											from v_EvnDiagPS EDPS with (nolock)
												left join v_Diag DiagEDPS with (nolock) on DiagEDPS.Diag_id = EDPS.Diag_id
											where
												EDPS.EvnDiagPS_pid = ESEC.EvnSection_id
												and EDPS.DiagSetClass_id = :DiagSetClass_id
										";
										$queryParams['DiagSetClass_id'] = $data['DiagSetClass_id'];

										if ( !empty($data['DiagSetType_id']) ) {
											$filter .= " and EDPS.DiagSetType_id = :DiagSetType_id";
											$queryParams['DiagSetType_id'] = $data['DiagSetType_id'];
										}

										if ( !empty($data['Diag_Code_From']) ) {
											$filter .= " and DiagEDPS.Diag_Code >= :Diag_Code_From";
											$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
										}

										if ( !empty($data['Diag_Code_To']) ) {
											$filter .= " and DiagEDPS.Diag_Code <= :Diag_Code_To";
											$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
										}

										$filter .= ")";
									}
								}
								else {
									$filter .= " and exists (
										select
											EvnDiagPS_id
										from v_EvnDiagPS EDPS with (nolock)
											left join v_Diag DiagEDPS with (nolock) on DiagEDPS.Diag_id = EDPS.Diag_id
										where
											EDPS.EvnDiagPS_pid = ESEC.EvnSection_id
									";

									$filter .= " and (((1 = 1)";

									if ( !empty($data['DiagSetType_id']) ) {
										$filter .= " and EDPS.DiagSetType_id = :DiagSetType_id";
										$queryParams['DiagSetType_id'] = $data['DiagSetType_id'];
									}

									if ( !empty($data['Diag_Code_From']) ) {
										$filter .= " and DiagEDPS.Diag_Code >= :Diag_Code_From";
										$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
									}

									if ( !empty($data['Diag_Code_To']) ) {
										$filter .= " and DiagEDPS.Diag_Code <= :Diag_Code_To";
										$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
									}

									$filter .= ") or ((1 = 1)";

									if ( !empty($data['Diag_Code_From']) ) {
										$filter .= " and Dtmp.Diag_Code >= :Diag_Code_From";
										$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
									}

									if ( !empty($data['Diag_Code_To']) ) {
										$filter .= " and Dtmp.Diag_Code <= :Diag_Code_To";
										$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
									}

									$filter .= ")))";
								}
							}
						break;
					}
				}
				//var_dump(isset($data['EvnUsluga_setDate_Range']));
				//return false;
				if ( !empty($data['UslugaCategory_id']) || !empty($data['UslugaComplex_Code_From']) || !empty($data['UslugaComplex_Code_To'])
					|| (isset($data['EvnUsluga_setDate_Range']) && is_array($data['EvnUsluga_setDate_Range']) && (!empty($data['EvnUsluga_setDate_Range'][0]) || !empty($data['EvnUsluga_setDate_Range'][1])))
				) {
					$filter .= " and exists (
						select
							t2.UslugaComplex_id
						from v_EvnUsluga t1 with (nolock)
							inner join UslugaComplex t2 with (nolock) on t2.UslugaComplex_id = t1.UslugaComplex_id
						where
							t1.EvnUsluga_rid = EPS.EvnPS_id
							and t1.Lpu_id = :Lpu_id
							and t1.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper')
					";

					// Лечение
					if ( !empty($data['EvnUsluga_setDate_Range'][0]) ) {
						$filter .= " and cast(t1.EvnUsluga_setDT as date) >= cast(:EvnUsluga_setDate_Range_0 as date)";
						$queryParams['EvnUsluga_setDate_Range_0'] = $data['EvnUsluga_setDate_Range'][0];
					}

					// Лечение
					if ( !empty($data['EvnUsluga_setDate_Range'][1]) ) {
						$filter .= " and cast(t1.EvnUsluga_setDT as date) <= cast(:EvnUsluga_setDate_Range_1 as date)";
						$queryParams['EvnUsluga_setDate_Range_1'] = $data['EvnUsluga_setDate_Range'][1];
					}

					// Лечение
					if ( !empty($data['UslugaCategory_id']) ) {
						$filter .= " and t2.UslugaCategory_id = :UslugaCategory_id ";
						$queryParams['UslugaCategory_id'] = $data['UslugaCategory_id'];
					}
					
					if ( !empty($data['UslugaComplex_Code_From']) ) {
						$filter .= " and t2.UslugaComplex_Code >= :UslugaComplex_Code_From ";
						$queryParams['UslugaComplex_Code_From'] = $data['UslugaComplex_Code_From'];
					}
					
					if ( !empty($data['UslugaComplex_Code_To']) ) {
						$filter .= " and t2.UslugaComplex_Code <= :UslugaComplex_Code_To ";
						$queryParams['UslugaComplex_Code_To'] = $data['UslugaComplex_Code_To'];
					}

					$filter .= ")";
				}

				if ( isset($data['LeaveType_id']) ) {
					switch ( $data['SearchFormType'] ) {
						case 'EvnPS':
							$filter .= " and EPS.LeaveType_id = :LeaveType_id";
						break;

						case 'EvnSection':
							$filter .= " and ESEC.LeaveType_id = :LeaveType_id";
						break;
					}

					$queryParams['LeaveType_id'] = $data['LeaveType_id'];
				}

				if ( isset($data['LeaveCause_id']) || isset($data['ResultDesease_id']) ||
					isset($data['Org_oid']) || isset($data['LpuUnitType_oid']) ||
					isset($data['LpuSection_oid']) || isset($data['EvnLeaveBase_UKL']) ||
					isset($data['EvnLeave_IsAmbul'])
				)
				{
					switch ( $data['SearchFormType'] ) {
						case 'EvnPS':
							$query .= " left join v_EvnLeave EL with (nolock) on EL.EvnLeave_pid = EPSLastES.EvnSection_id and EPS.LeaveType_id = 1 and EL.Lpu_id = :Lpu_id";
							$query .= " left join v_EvnOtherLpu EOL with (nolock) on EOL.EvnOtherLpu_pid = EPSLastES.EvnSection_id and EPS.LeaveType_id = 2 and EOL.Lpu_id = :Lpu_id";
							$query .= " left join v_EvnDie ED with (nolock) on ED.EvnDie_pid = EPSLastES.EvnSection_id and EPS.LeaveType_id = 3 and ED.Lpu_id = :Lpu_id";
							$query .= " left join v_EvnOtherStac EOST with (nolock) on EOST.EvnOtherStac_pid = EPSLastES.EvnSection_id and EPS.LeaveType_id = 4 and EOST.Lpu_id = :Lpu_id";
							$query .= " left join v_EvnOtherSection EOS with (nolock) on EOS.EvnOtherSection_pid = EPSLastES.EvnSection_id and EPS.LeaveType_id = 5 and EOS.Lpu_id = :Lpu_id";
						break;

						case 'EvnSection':
							$query .= " left join v_EvnLeave EL with (nolock) on EL.EvnLeave_pid = ESEC.EvnSection_id and ESEC.LeaveType_id = 1";
							$query .= " left join v_EvnOtherLpu EOL with (nolock) on EOL.EvnOtherLpu_pid = ESEC.EvnSection_id and ESEC.LeaveType_id = 2";
							$query .= " left join v_EvnDie ED with (nolock) on ED.EvnDie_pid = ESEC.EvnSection_id and ESEC.LeaveType_id = 3";
							$query .= " left join v_EvnOtherStac EOST with (nolock) on EOST.EvnOtherStac_pid = ESEC.EvnSection_id and ESEC.LeaveType_id = 4";
							$query .= " left join v_EvnOtherSection EOS with (nolock) on EOS.EvnOtherSection_pid = ESEC.EvnSection_id and ESEC.LeaveType_id = 5";
						break;
					}

					// Результат лечения
					if ( isset($data['LeaveCause_id']) ) {
						$filter .= " and COALESCE(EL.LeaveCause_id, EOL.LeaveCause_id, EOST.LeaveCause_id, EOS.LeaveCause_id) = :LeaveCause_id";
						$queryParams['LeaveCause_id'] = $data['LeaveCause_id'];
					}

					// Результат лечения
					if ( isset($data['ResultDesease_id']) ) {
						$filter .= " and ISNULL(EL.ResultDesease_id, ISNULL(EOL.ResultDesease_id, ISNULL(EOST.ResultDesease_id, EOS.ResultDesease_id))) = :ResultDesease_id";
						$queryParams['ResultDesease_id'] = $data['ResultDesease_id'];
					}

					// Результат лечения
					if ( isset($data['Org_oid']) ) {
						$filter .= " and EOL.Org_oid = :Org_oid";
						$queryParams['Org_oid'] = $data['Org_oid'];
					}

					// Результат лечения
					if ( isset($data['LpuUnitType_oid']) ) {
						$filter .= " and EOST.LpuUnitType_oid = :LpuUnitType_oid";
						$queryParams['LpuUnitType_oid'] = $data['LpuUnitType_oid'];
					}

					// Результат лечения
					if ( isset($data['LpuSection_oid']) ) {
						$filter .= " and ISNULL(EOS.LpuSection_oid, EOST.LpuSection_oid) = :LpuSection_oid";
						$queryParams['LpuSection_oid'] = $data['LpuSection_oid'];
					}

					// Результат лечения
					if ( isset($data['EvnLeaveBase_UKL']) ) {
						$filter .= " and COALESCE(ED.EvnDie_UKL, EL.EvnLeave_UKL, EOL.EvnOtherLpu_UKL, EOST.EvnOtherStac_UKL, EOS.EvnOtherSection_UKL) = :EvnLeaveBase_UKL";
						$queryParams['EvnLeaveBase_UKL'] = $data['EvnLeaveBase_UKL'];
					}
					
					// Результат лечения
					if ( isset($data['EvnLeave_IsAmbul']) ) {
						$filter .= " and ISNULL(EL.EvnLeave_IsAmbul, 1) = :EvnLeave_IsAmbul";
						$queryParams['EvnLeave_IsAmbul'] = $data['EvnLeave_IsAmbul'];
					}
				}

				if ( isset($data['StickCause_id']) || isset($data['StickType_id']) ||
					isset($data['EvnStick_begDate_Range'][0]) || isset($data['EvnStick_begDate_Range'][1]) ||
					isset($data['EvnStick_endDate_Range'][0]) || isset($data['EvnStick_endDate_Range'][1])
				)
				{
					$evn_stick_filter = '';

					// Результаты
					if ( isset($data['EvnStick_begDate_Range'][0]) ) {
						$evn_stick_filter .= " and ESB.EvnStickBase_setDT >= :EvnStick_begDate_Range_0";
						$queryParams['EvnStick_begDate_Range_0'] = $data['EvnStick_begDate_Range'][0];
					}

					// Результаты
					if ( isset($data['EvnStick_begDate_Range'][1]) ) {
						$evn_stick_filter .= " and ESB.EvnStickBase_setDT <= :EvnStick_begDate_Range_1";
						$queryParams['EvnStick_begDate_Range_1'] = $data['EvnStick_begDate_Range'][1];
					}

					// Результаты
					if ( isset($data['EvnStick_endDate_Range'][0]) ) {
						$evn_stick_filter .= " and (
							(ESB.StickType_id = 1 and ESB.EvnStickBase_disDT >= :EvnStick_endDate_Range_0)
							or (ESB.StickType_id = 2 and exists (select EvnStickWorkRelease_id from v_EvnStickWorkRelease with (nolock) where EvnStickBase_id = ESB.EvnStickBase_id and EvnStickWorkRelease_endDT >= :EvnStick_endDate_Range_0))
						)";
						$queryParams['EvnStick_endDate_Range_0'] = $data['EvnStick_endDate_Range'][0];
					}

					// Результаты
					if ( isset($data['EvnStick_endDate_Range'][1]) ) {
						$evn_stick_filter .= " and (
							(ESB.StickType_id = 1 and ESB.EvnStickBase_disDT <= :EvnStick_endDate_Range_1)
							or (ESB.StickType_id = 2 and exists (select EvnStickWorkRelease_id from v_EvnStickWorkRelease with (nolock) where EvnStickBase_id = ESB.EvnStickBase_id and EvnStickWorkRelease_endDT <= :EvnStick_endDate_Range_1))
						)";
						$queryParams['EvnStick_endDate_Range_1'] = $data['EvnStick_endDate_Range'][1];
					}

					// Результаты
					if ( isset($data['StickCause_id']) ) {
						$evn_stick_filter .= " and ESB.StickCause_id = :StickCause_id";
						$queryParams['StickCause_id'] = $data['StickCause_id'];
					}

					// Результаты
					if ( isset($data['StickType_id']) ) {
						$evn_stick_filter .= " and ESB.StickType_id = :StickType_id";
						$queryParams['StickType_id'] = $data['StickType_id'];
					}
					
					$filter .= "
						and exists (
							select EvnStickBase_id
							from v_EvnStickBase ESB with (nolock)
							where ESB.EvnStickBase_mid = EPS.EvnPS_id
								" . $evn_stick_filter . "
							union all
							select Evn_id as EvnStickBase_id
							from v_EvnLink EL with (nolock)
								inner join v_EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = EL.Evn_lid
							where EL.Evn_id = EPS.EvnPS_id
								" . $evn_stick_filter . "
						)
					";
				}
			break;

			case 'EvnRecept':
				if ( $data['PersonPeriodicType_id'] == 2 ) {
					$query .= " inner join v_EvnRecept ER with (nolock) on ER.Server_id = PS.Server_id and ER.PersonEvn_id = PS.PersonEvn_id";
				}
				else {
					$query .= " inner join v_EvnRecept ER with (nolock) on ER.Person_id = PS.Person_id";
				}

				if ( !isMinZdrav() && !$isFarmacy && $data['Lpu_id'] > 0 ) {
					$query .= " and ER.Lpu_id = :Lpu_id";
				}

				// $query .= " inner join v_EvnRecept ER on ER.Person_id = PS.Person_id and ER.Lpu_id = :Lpu_id";
				$query .= " left join v_Drug ERDrug with (nolock) on ERDrug.Drug_id = ER.Drug_id";
				$query .= " left join rls.v_Drug ERDrugRls with (nolock) on ERDrugRls.Drug_id = ER.Drug_rlsid";
				$query .= " left join dbo.v_WhsDocumentCostItemType wdcit with (nolock) on wdcit.WhsDocumentCostItemType_id = ER.WhsDocumentCostItemType_id";
				$query .= " left join v_MedPersonal ERMP with (nolock) on ERMP.MedPersonal_id = ER.MedPersonal_id";
				if (!$isFarmacy && $data['Lpu_id'] > 0 )
					$query .= " and ERMP.Lpu_id = :Lpu_id";

				if ( (strlen($data['ER_Diag_Code_From']) > 0) || (strlen($data['ER_Diag_Code_To']) > 0) ) {
					$query .= " inner join Diag ERDiag with (nolock) on ERDiag.Diag_id = ER.Diag_id";

					if ( strlen($data['ER_Diag_Code_From']) > 0 ) {
						$filter .= " and ERDiag.Diag_Code >= :ER_Diag_Code_From";
						$queryParams['ER_Diag_Code_From'] = $data['ER_Diag_Code_From'];
					}

					if ( strlen($data['ER_Diag_Code_To']) > 0 ) {
						$filter .= " and ERDiag.Diag_Code <= :ER_Diag_Code_To";
						$queryParams['ER_Diag_Code_To'] = $data['ER_Diag_Code_To'];
					}
				}

				$filter .= " and ER.ReceptRemoveCauseType_id is null";

				// Рецепт
				if ( $data['Drug_id'] > 0 ) {
					$filter .= " and ERDrug.Drug_id = :Drug_id";
					$queryParams['Drug_id'] = $data['Drug_id'];
				}

				// Рецепт
				if ($data['DrugMnn_id'] > 0) {
					$filter .= " and ERDrug.DrugMnn_id = :DrugMnn_id";
					$queryParams['DrugMnn_id'] = $data['DrugMnn_id'];
				}

				// Рецепт
				if ( $data['ER_MedPersonal_id'] > 0 ) {
					$filter .= " and ERMP.MedPersonal_id = :ER_MedPersonal_id";
					$queryParams['ER_MedPersonal_id'] = $data['ER_MedPersonal_id'];
				}

				// Рецепт
				if ( isset($data['ER_PrivilegeType_id']) ) {
					$filter .= " and ER.PrivilegeType_id = :ER_PrivilegeType_id";
					$queryParams['ER_PrivilegeType_id'] = $data['ER_PrivilegeType_id'];
				}
				
				// Рецепт
				if ( isset($data['EvnRecept_Is7Noz']) ) {
					$filter .= " and ISNULL(ER.EvnRecept_Is7Noz, 1) = :EvnRecept_Is7Noz";
					$queryParams['EvnRecept_Is7Noz'] = $data['EvnRecept_Is7Noz'];
				}

				// Рецепт
				if ( $data['EvnRecept_IsKEK'] > 0 ) {
					$filter .= " and ER.EvnRecept_IsKEK = :EvnRecept_IsKEK";
					$queryParams['EvnRecept_IsKEK'] = $data['EvnRecept_IsKEK'];
				}
				
				// Рецепт
				if ( $data['EvnRecept_IsNotOstat'] > 0 ) {
					$filter .= " and ISNULL(ER.EvnRecept_IsNotOstat, 1) = :EvnRecept_IsNotOstat";
					$queryParams['EvnRecept_IsNotOstat'] = $data['EvnRecept_IsNotOstat'];
				}

				// Рецепт
				if ( strlen($data['EvnRecept_Num']) > 0 ) {
					$filter .= " and ER.EvnRecept_Num = :EvnRecept_Num";
					$queryParams['EvnRecept_Num'] = $data['EvnRecept_Num'];
				}

				// Рецепт
				if ( strlen($data['EvnRecept_Ser']) > 0 ) {
					$filter .= " and ER.EvnRecept_Ser = :EvnRecept_Ser";
					$queryParams['EvnRecept_Ser'] = $data['EvnRecept_Ser'];
				}

				// Рецепт
				if ( isset($data['EvnRecept_setDate']) ) {
					$filter .= " and ER.EvnRecept_setDate = cast(:EvnRecept_setDate as datetime)";
					$queryParams['EvnRecept_setDate'] = $data['EvnRecept_setDate'];
				}

				// Рецепт
				if ( isset($data['EvnRecept_setDate_Range'][0]) ) {
					$filter .= " and ER.EvnRecept_setDate >= cast(:EvnRecept_setDate_Range_0 as datetime)";
					$queryParams['EvnRecept_setDate_Range_0'] = $data['EvnRecept_setDate_Range'][0];
				}

				// Рецепт
				if ( isset($data['EvnRecept_setDate_Range'][1]) ) {
					$filter .= " and ER.EvnRecept_setDate <= cast(:EvnRecept_setDate_Range_1 as datetime)";
					$queryParams['EvnRecept_setDate_Range_1'] = $data['EvnRecept_setDate_Range'][1];
				}

				// Рецепт (доп.)
				if ( isset($data['OrgFarmacy_id']) ) {
					$queryParams['OrgFarmacy_id'] = $data['OrgFarmacy_id'];

					if ( $data['OrgFarmacy_id'] == -1) {
						$filter .= " and ER.OrgFarmacy_id is null";
					}
					else {
						$filter .= " and ER.OrgFarmacy_id = :OrgFarmacy_id";
					}
				}

				// Рецепт (доп.)
				if ( $data['ReceptDiscount_id'] > 0 ) {
					$filter .= " and ER.ReceptDiscount_id = :ReceptDiscount_id";
					$queryParams['ReceptDiscount_id'] = $data['ReceptDiscount_id'];
				}

				// Рецепт (доп.)
				if ( $data['ReceptFinance_id'] > 0 ) {
					$filter .= " and ER.ReceptFinance_id = :ReceptFinance_id";
					$queryParams['ReceptFinance_id'] = $data['ReceptFinance_id'];
				}

				// Рецепт (доп.)
				if ( $data['ReceptType_id'] > 0 ) {
					$filter .= " and ER.ReceptType_id = :ReceptType_id";
					$queryParams['ReceptType_id'] = $data['ReceptType_id'];
				}

				// Рецепт (доп.)
				if ( $data['ReceptValid_id'] > 0 ) {
					$filter .= " and ER.ReceptValid_id = :ReceptValid_id";
					$queryParams['ReceptValid_id'] = $data['ReceptValid_id'];
				}

				// Рецепт (доп.)
				if ( isset($data['EvnRecept_IsExtemp']) ) {
					$filter .= " and ISNULL(ER.EvnRecept_IsExtemp, 1) = :EvnRecept_IsExtemp";
					$queryParams['EvnRecept_IsExtemp'] = $data['EvnRecept_IsExtemp'];
				}
			break;
			
			case 'EvnUslugaPar':
				if ( $data['PersonPeriodicType_id'] == 2 ) {
					$query .= " inner join v_EvnUslugaPar EUP with (nolock) on EUP.Server_id = PS.Server_id and EUP.PersonEvn_id = PS.PersonEvn_id and EUP.Lpu_id = :Lpu_id";
				}
				else {
					$query .= " inner join v_EvnUslugaPar EUP with (nolock) on EUP.Person_id = PS.Person_id and EUP.Lpu_id = :Lpu_id";
				}
				$query .= " inner join LpuSection LS with (nolock) on LS.LpuSection_id = EUP.LpuSection_uid";
				$query .= " inner join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EUP.MedPersonal_id and MP.Lpu_id = EUP.Lpu_id";
				$query .= " left join Usluga with (nolock) on Usluga.Usluga_id = EUP.Usluga_id";
				$query .= " left join UslugaComplex with (nolock) on UslugaComplex.UslugaComplex_id = EUP.UslugaComplex_id";
				$query .= " inner join PayType PT with (nolock) on PT.PayType_id = EUP.PayType_id";
				$query .= " inner join v_MedStaffFact MedStaffFact (nolock) on MedStaffFact.MedPersonal_id = MP.MedPersonal_id and MedStaffFact.LpuSection_id = LS.LpuSection_id";
				$query .= " left join v_PostMed PostMed (nolock) on PostMed.PostMed_id = MedStaffFact.Post_id";
				$query .= " left join v_EvnDirection_all ED with (nolock) on EUP.EvnDirection_id = ED.EvnDirection_id";
				
				
				// Услуга
				if ( isset($data['EvnDirection_Num']) ) {
					$filter .= " and ED.EvnDirection_Num = :EvnDirection_Num";
					$queryParams['EvnDirection_Num'] = $data['EvnDirection_Num'];
				}

				// Услуга
				if ( isset($data['EvnDirection_setDate']) ) {
					$filter .= " and ED.EvnDirection_setDT = :EvnDirection_setDate";
					$queryParams['EvnDirection_setDate'] = $data['EvnDirection_setDate'];
				}
				
				// Услуга
				if ( isset($data['EvnUslugaPar_setDate_Range'][0]) ) {
					$filter .= " and EUP.EvnUslugaPar_setDate >= cast(:EvnUslugaPar_setDate_Range_0 as datetime)";
					$queryParams['EvnUslugaPar_setDate_Range_0'] = $data['EvnUslugaPar_setDate_Range'][0];
				}

				// Услуга
				if ( isset($data['EvnUslugaPar_setDate_Range'][1]) ) {
					$filter .= " and EUP.EvnUslugaPar_setDate <= cast(:EvnUslugaPar_setDate_Range_1 as datetime)";
					$queryParams['EvnUslugaPar_setDate_Range_1'] = $data['EvnUslugaPar_setDate_Range'][1];
				}

				// Услуга
				if ( isset($data['LpuSection_uid']) ) {
					$filter .= " and EUP.LpuSection_uid = :LpuSection_uid";
					$queryParams['LpuSection_uid'] = $data['LpuSection_uid'];
				}

				// Услуга
				if ( isset($data['MedPersonal_did']) ) {
					$filter .= " and EUP.MedPersonal_did = :MedPersonal_did";
					$queryParams['MedPersonal_did'] = $data['MedPersonal_did'];
				}

				// Услуга
				if ( isset($data['MedPersonal_uid']) ) {
					$filter .= " and EUP.MedPersonal_id = :MedPersonal_uid";
					$queryParams['MedPersonal_uid'] = $data['MedPersonal_uid'];
				}

				// Услуга
				if ( isset($data['PayType_id']) ) {
					$filter .= " and EUP.PayType_id = :PayType_id";
					$queryParams['PayType_id'] = $data['PayType_id'];
				}

				// Услуга
				if ( isset($data['PrehospDirect_id']) ) {
					$filter .= " and EUP.PrehospDirect_id = :PrehospDirect_id";
					$queryParams['PrehospDirect_id'] = $data['PrehospDirect_id'];
				}

				// Услуга
				if ( isset($data['Usluga_id']) ) {
					$filter .= " and EUP.Usluga_id = :Usluga_id";
					$queryParams['Usluga_id'] = $data['Usluga_id'];
				}

				// Комплексная слуга
				if ( isset($data['UslugaComplex_id']) ) {
					$filter .= " and EUP.UslugaComplex_id = :UslugaComplex_id";
					$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
				}
				
				
				/*
				// Услуга
				if ( isset($data['UslugaPlace_id']) ) {
					$filter .= " and EUP.UslugaPlace_id = :UslugaPlace_id";
					$queryParams['UslugaPlace_id'] = $data['UslugaPlace_id'];
				}
				*/
			break;

			case 'PersonCardStateDetail':
				// полный пиздец и содомия с 4441 по 4461 строки
				$query .= "
					-- смысл в том что v_PersonCard_All возвращает не те данные... пришлось сделать как в исходном запросе
					cross apply (
						select
							max(Personcard_id) as Personcard_id
						from v_PersonCard_all pc with (nolock)
						left join LpuRegion lr (nolock) on lr.LpuRegion_id = pc.LpuRegion_id
						WHERE pc.Lpu_id = :Lpu_id and pc.Person_id = PS.Person_id
						group by
							pc.Personcard_Code,
							pc.LpuRegion_id,
							pc.Person_id,
							pc.Server_id,
							pc.Lpu_id,-
							pc.LpuAttachType_id,
							pc.PersonCard_begDate,
							pc.PersonCard_endDate
					) as PCSD1
					inner join PersonCard PCSD with (nolock) on PCSD1.PersonCard_id = PCSD.PersonCard_id
					left join v_PersonState pcc (nolock) on pcc.Person_id = PCSD.Person_id
					--участок по ОМС
					left join LpuRegion lr (nolock) on lr.LpuRegion_id = PCSD.LpuRegion_id
					left join LpuRegionType lrt (nolock) on lrt.LpuRegionType_id = lr.LpuRegionType_id
					--inner join v_PersonCard_All PCSD with (nolock) on PCSD.Person_id = PS.Person_id
					left join CardCloseCause ccc with (nolock) on ccc.CardCloseCause_id = PCSD.CardCloseCause_id
					-- заменил на outer apply, потому что двоилось из-за беспорядка в базе
					outer apply (
						select top 1
							pc1.LpuRegion_Name,
							lp.Lpu_Nick
						from
							v_PersonCard pc1 with (nolock)
							inner join v_Lpu lp with (nolock) on pc1.Lpu_id=lp.Lpu_id
						where
							PCSD.Person_id=pc1.Person_id and PCSD.LpuAttachType_id=pc1.LpuAttachType_id
						order by
							pc1.PersonCard_Begdate
					) as attcard
					--left join v_PersonCard pc1 with (nolock) on PCSD.Person_id=pc1.Person_id and PCSD.LpuAttachType_id=pc1.LpuAttachType_id
					--left join v_Lpu lp with (nolock) on pc1.Lpu_id=lp.Lpu_id
					--left join PersonState ps1 with (nolock) on ps1.Person_id = PCSD.Person_id
					left join Address with (nolock) on ps.PAddress_id = Address.Address_id
					left join Address Address1 with (nolock) on ps.UAddress_id = Address1.Address_id
					-- текущий полис
					left join v_Polis pols with (nolock) on pols.Polis_id = PS.Polis_id and cast(convert(char(10), pols.Polis_begDate, 112) as datetime) < cast(convert(char(10), dbo.tzGetDate(), 112) as datetime) and ( pols.Polis_EndDate is null or cast(convert(char(10), Pols.Polis_endDate, 112) as datetime) > cast(convert(char(10), dbo.tzGetDate(), 112) as datetime) )
					left join v_OrgSmo omsOrgSmo with (nolock) on pols.PolisType_id in (1,4) and pols.OrgSmo_id = omsOrgSmo.OrgSmo_id
					--для СМО по ДМС
					left join v_PersonCard dmspc with (nolock) on dmspc.Lpu_id = :Lpu_id 
						and dmspc.Person_id = PS.Person_id 
						and  dmspc.LpuAttachType_id = 5 
						and cast(convert(char(10), dmspc.PersonCard_begDate, 112) as datetime) < cast(convert(char(10), dbo.tzGetDate(), 112) as datetime) 
						and ( dmspc.PersonCard_endDate is null or cast(convert(char(10), dmspc.PersonCard_endDate, 112) as datetime) > cast(convert(char(10), dbo.tzGetDate(), 112) as datetime) )
					left join v_OrgSmo dmsOrgSmo with (nolock) on dmspc.OrgSmo_id = dmsOrgSmo.OrgSmo_id
					  outer apply (
                            Select top 1
                                Polis.Polis_id
                            from
                                v_Person_all Person with (nolock)
                                left join v_Polis Polis with (nolock) on Person.Polis_id = Polis.Polis_id
                            where
                                Person.Person_id = PCSD.Person_id
								and Person.Server_pid = 0
								and cast(convert(varchar(10), Polis.Polis_begDate, 112) as datetime) < :PCSD_begDate
								and ( Polis.Polis_endDate is null or (cast(convert(varchar(10), Polis.Polis_endDate, 112) as datetime) >= :PCSD_begDate) )
                        ) as PolisBeg
                        outer apply (
                            Select top 1
                                Polis.Polis_id
                            from
                                v_Person_all Person with (nolock)
                                left join v_Polis Polis with (nolock) on Person.Polis_id = Polis.Polis_id
                            where
                                Person.Person_id = PCSD.Person_id
								and Person.Server_pid = 0
								and cast(convert(varchar(10), Polis.Polis_begDate, 112) as datetime) <= :PCSD_endDate
								and ( Polis.Polis_endDate is null or (cast(convert(varchar(10), Polis.Polis_endDate, 112) as datetime) > :PCSD_endDate) )
                        ) as PolisEnd
					 outer apply (
							select top 1
                                pclast.PersonCard_id,
								pclast.Lpu_id
                            from
                                PersonCard pclast with (nolock)
							where
								PCSD.Person_id = pclast.Person_id and
								pclast.PersonCard_id < PCSD.PersonCard_id and
								pclast.LpuAttachType_id = PCSD.LpuAttachType_id
							order by
								pclast.PersonCard_id desc
                     ) as LastCard
					 outer apply (
							select top 1
                                pclast.PersonCard_id,
								pclast.Lpu_id,
								pclast.PersonCard_begDate
                            from
                                PersonCard pclast with (nolock)
							where
								PCSD.Person_id = pclast.Person_id and
								pclast.PersonCard_id > PCSD.PersonCard_id and
								pclast.LpuAttachType_id = PCSD.LpuAttachType_id
							order by
								pclast.PersonCard_id asc
                     ) as NextCard
				";

				if ( $data['PCSD_LpuMotion_id'] == 2 )
					$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id != PCSD.Lpu_id ";
				if ( $data['PCSD_LpuMotion_id'] == 3 )
					$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id = PCSD.Lpu_id ";

				if ( isset($data['PCSD_FromLpu_id']) && (int)$data['PCSD_FromLpu_id'] > 0 )
				{
					$queryParams['FromLpu_id'] = (int)$data['PCSD_FromLpu_id'];
					$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id = :FromLpu_id ";
				}

				if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
				{
					$queryParams['ToLpu_id'] = (int)$data['PCSD_ToLpu_id'];
					$filter .= " and NextCard.Lpu_id is not NULL and NextCard.Lpu_id = :ToLpu_id ";
				}
				
				if ( isset($data['PCSD_LpuRegion_id']) ) {
					$queryParams['PCSD_LpuRegion_id'] = $data['PCSD_LpuRegion_id'];
					$filter .= " and PCSD.LpuRegion_id = :PCSD_LpuRegion_id ";
				}
				else
				{
					$filter .= " and PCSD.LpuRegion_id is null and PCSD.Lpu_id = :Lpu_id ";
				}
				
				if ( isset($data['PCSD_LpuAttachType_id']) && $data['PCSD_LpuAttachType_id'] > 0 ) {
					$queryParams['PCSD_LpuAttachType_id'] = $data['PCSD_LpuAttachType_id'];
					$filter .= " and PCSD.LpuAttachType_id = :PCSD_LpuAttachType_id ";
				}
				
				/*
				 * @todo: Фильтры, Журнал движения РПН, Просмотр Деталей.
				 */
				switch ( $data['PCSD_mode'] )
				{
					case 'BegCount':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) < :PCSD_begDate
							and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'BegCountBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) < :PCSD_begDate
							and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
							and (PolisBeg.Polis_id is not null )
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'BegCountNotInBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) < :PCSD_begDate
							and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
							and (PolisBeg.Polis_id is null )
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'EndCount':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) <= :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) > :PCSD_endDate)
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'EndCountBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) <= :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) > :PCSD_endDate)
							and (PolisEnd.Polis_id is not null)
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'EndCountNotInBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) <= :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) > :PCSD_endDate)
							and (PolisEnd.Polis_id is null)
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'AttachCount':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'AttachCountBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate
							and (Polis.Polis_id is not null )
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'AttachIncomeBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) between '1970-01-01' and :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) >= :PCSD_begDate)
							and PS.Server_pid = 0
							and hasPolis.cnt is not null
							and notHasPolis.cnt is null
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'AttachOutcomeBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) between '1970-01-01' and :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) >= :PCSD_begDate)
							and PS.Server_pid = 0
							and HasPolisBefore.cnt is not null
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'DettachCount':
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= "
								and cast(convert(varchar(10), NextCard.PersonCard_begDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate and NextCard.Lpu_id = :ToLpu_id
							";
						}
						else
						{
							$filter .= "
								and cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate
							";
						}
					break;
					case 'DettachCountBDZ':
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= "
								and cast(convert(varchar(10), NextCard.PersonCard_begDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate and NextCard.Lpu_id = :ToLpu_id
								and (Polis.Polis_id is not null)
							";
						}
						else
						{
							$filter .= "
								and cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate
								and (Polis.Polis_id is not null )
							";
						}
					break;

				}
				$queryParams['PCSD_begDate'] = $data['PCSD_StartDate'];
				$queryParams['PCSD_endDate'] = $data['PCSD_EndDate'];
			break;
			case 'PersonCardStateDetail_old':
				$query .= "
					inner join v_PersonCard_All PCSD with (nolock) on PCSD.Person_id = PS.Person_id
					left join CardCloseCause ccc with (nolock) on ccc.CardCloseCause_id = PCSD.CardCloseCause_id
					-- заменил на outer apply, потому что двоилось из-за беспорядка в базе
					outer apply (
						select top 1
							pc1.LpuRegion_Name,
							lp.Lpu_Nick
						from
							v_PersonCard pc1 with (nolock)
							inner join v_Lpu lp with (nolock) on pc1.Lpu_id=lp.Lpu_id
						where
							PCSD.Person_id=pc1.Person_id and PCSD.LpuAttachType_id=pc1.LpuAttachType_id
						order by
							pc1.PersonCard_Begdate
					) as attcard
					--left join v_PersonCard pc1 with (nolock) on PCSD.Person_id=pc1.Person_id and PCSD.LpuAttachType_id=pc1.LpuAttachType_id
					--left join v_Lpu lp with (nolock) on pc1.Lpu_id=lp.Lpu_id
					--left join PersonState ps1 with (nolock) on ps1.Person_id = PCSD.Person_id
					left join Address with (nolock) on ps.PAddress_id = Address.Address_id
					left join Address Address1 with (nolock) on ps.UAddress_id = Address1.Address_id
					-- текущий полис
					left join v_Polis pols on pols.Polis_id = PS.Polis_id and cast(convert(char(10), pols.Polis_begDate, 112) as datetime) < cast(convert(char(10), dbo.tzGetDate(), 112) as datetime) and ( pols.Polis_EndDate is null or cast(convert(char(10), Pols.Polis_endDate, 112) as datetime) > cast(convert(char(10), dbo.tzGetDate(), 112) as datetime) )
					  outer apply (
                            Select top 1
                                Polis.Polis_id
                            from
                                v_Person_all Person with (nolock)
                                left join v_Polis Polis with (nolock) on Person.Polis_id = Polis.Polis_id
                            where
                                Person.Person_id = PCSD.Person_id
								and Person.Server_id = 0
								and cast(convert(varchar(10), Polis.Polis_begDate, 112) as datetime) < :PCSD_begDate
								and ( Polis.Polis_endDate is null or (cast(convert(varchar(10), Polis.Polis_endDate, 112) as datetime) >= :PCSD_begDate) )
                        ) as PolisBeg
                        outer apply (
                            Select top 1
                                Polis.Polis_id
                            from
                                v_Person_all Person with (nolock)
                                left join v_Polis Polis with (nolock) on Person.Polis_id = Polis.Polis_id
                            where
                                Person.Person_id = PCSD.Person_id
								and Person.Server_id = 0
								and cast(convert(varchar(10), Polis.Polis_begDate, 112) as datetime) <= :PCSD_endDate
								and ( Polis.Polis_endDate is null or (cast(convert(varchar(10), Polis.Polis_endDate, 112) as datetime) > :PCSD_endDate) )
                        ) as PolisEnd
					 outer apply (
							select top 1
                                pclast.PersonCard_id,
								pclast.Lpu_id
                            from
                                PersonCard pclast with (nolock)
							where
								PCSD.Person_id = pclast.Person_id and
								pclast.PersonCard_id < PCSD.PersonCard_id and
								pclast.LpuAttachType_id = PCSD.LpuAttachType_id
							order by
								pclast.PersonCard_id desc
                     ) as LastCard
					 outer apply (
							select top 1
                                pclast.PersonCard_id,
								pclast.Lpu_id,
								pclast.PersonCard_begDate
                            from
                                PersonCard pclast with (nolock)
							where
								PCSD.Person_id = pclast.Person_id and
								pclast.PersonCard_id > PCSD.PersonCard_id and
								pclast.LpuAttachType_id = PCSD.LpuAttachType_id
							order by
								pclast.PersonCard_id asc
                     ) as NextCard
				";

				if ( $data['PCSD_LpuMotion_id'] == 2 )
					$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id != PCSD.Lpu_id ";
				if ( $data['PCSD_LpuMotion_id'] == 3 )
					$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id = PCSD.Lpu_id ";

				if ( isset($data['PCSD_FromLpu_id']) && (int)$data['PCSD_FromLpu_id'] > 0 )
				{
					$queryParams['FromLpu_id'] = (int)$data['PCSD_FromLpu_id'];
					$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id = :FromLpu_id ";
				}

				if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
				{
					$queryParams['ToLpu_id'] = (int)$data['PCSD_ToLpu_id'];
					$filter .= " and NextCard.Lpu_id is not NULL and NextCard.Lpu_id = :ToLpu_id ";
				}
				
				if ( isset($data['PCSD_LpuRegion_id']) ) {
					$queryParams['PCSD_LpuRegion_id'] = $data['PCSD_LpuRegion_id'];
					$filter .= " and PCSD.LpuRegion_id = :PCSD_LpuRegion_id ";
				}
				else
				{
					$filter .= " and PCSD.LpuRegion_id is null and PCSD.Lpu_id = :Lpu_id ";
				}
				/*
				 * @todo: Фильтры, Журнал движения РПН, Просмотр Деталей.
				 */
				switch ( $data['PCSD_mode'] )
				{
					case 'BegCount':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) < :PCSD_begDate
							and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'BegCountBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) < :PCSD_begDate
							and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
							and (PolisBeg.Polis_id is not null )
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'EndCount':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) <= :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) > :PCSD_endDate)
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'EndCountBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) <= :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) > :PCSD_endDate)
							and (PolisEnd.Polis_id is not null)
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'AttachCount':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'AttachCountBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate
							and (Polis.Polis_id is not null )
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'AttachIncomeBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) between '1970-01-01' and :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) >= :PCSD_begDate)
							and PS.Server_pid = 0
							and hasPolis.cnt is not null
							and notHasPolis.cnt is null
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'AttachOutcomeBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) between '1970-01-01' and :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) >= :PCSD_begDate)
							and PS.Server_pid = 0
							and HasPolisBefore.cnt is not null
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'DettachCount':
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= "
								and cast(convert(varchar(10), NextCard.PersonCard_begDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate and NextCard.Lpu_id = :ToLpu_id
							";
						}
						else
						{
							$filter .= "
								and cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate
							";
						}
					break;
					case 'DettachCountBDZ':
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= "
								and cast(convert(varchar(10), NextCard.PersonCard_begDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate and NextCard.Lpu_id = :ToLpu_id
								and (Polis.Polis_id is not null)
							";
						}
						else
						{
							$filter .= "
								and cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate
								and (Polis.Polis_id is not null )
							";
						}
					break;

				}
				$queryParams['PCSD_begDate'] = $data['PCSD_StartDate'];
				$queryParams['PCSD_endDate'] = $data['PCSD_EndDate'];
			break;
			case 'PersonCardStateDetail_old':
				$query .= "
					inner join v_PersonCard_All PCSD with (nolock) on PCSD.Person_id = PS.Person_id
					left join CardCloseCause ccc with (nolock) on ccc.CardCloseCause_id = PCSD.CardCloseCause_id
					-- заменил на outer apply, потому что двоилось из-за беспорядка в базе
					outer apply (
						select top 1
							pc1.LpuRegion_Name,
							lp.Lpu_Nick
						from
							v_PersonCard pc1 with (nolock)
							inner join v_Lpu lp with (nolock) on pc1.Lpu_id=lp.Lpu_id
						where
							PCSD.Person_id=pc1.Person_id and PCSD.LpuAttachType_id=pc1.LpuAttachType_id
						order by
							pc1.PersonCard_Begdate
					) as attcard
					--left join v_PersonCard pc1 with (nolock) on PCSD.Person_id=pc1.Person_id and PCSD.LpuAttachType_id=pc1.LpuAttachType_id
					--left join v_Lpu lp with (nolock) on pc1.Lpu_id=lp.Lpu_id
					--left join PersonState ps1 with (nolock) on ps1.Person_id = PCSD.Person_id
					left join Address with (nolock) on ps.PAddress_id = Address.Address_id
					left join Address Address1 with (nolock) on ps.UAddress_id = Address1.Address_id
					outer apply (
						select
							top 1 pall.Person_id as cnt
						from
							v_Person_all  pall  with (nolock)
							inner join v_Polis pol  with (nolock) on pall.Polis_id = pol.Polis_id
						where
							pall.Person_id = ps.Person_id
							and pall.Server_id = ps.Server_id
							and pol.Polis_begDate < :PCSD_begDate
							and ( pol.Polis_endDate is null or pol.Polis_endDate > :PCSD_begDate )
					) as notHasPolis
					-- полис открылся внутри периода
					outer apply (
						-- есть открытие полиса в рамках периода
						select
							top 1 pall.Person_id as cnt
						from
							v_Person_all pall  with (nolock)
							inner join v_Polis pol  with (nolock) on pall.Polis_id = pol.Polis_id
						where
							pall.Person_id = ps.Person_id
							and pall.Server_id = ps.Server_id
							and pol.Polis_begDate >= :PCSD_begDate
							and pol.Polis_begDate <= :PCSD_endDate
					) as hasPolis
					-- был полис, но до начала периода и он закрылся
					outer apply (
						select
							top 1 pall.Person_id as cnt
						from
							v_Person_all  pall  with (nolock)
							inner join v_Polis pol  with (nolock) on pall.Polis_id = pol.Polis_id
						where
							pall.Person_id = ps.Person_id
							and pall.Server_id = ps.Server_id
							and pol.Polis_begDate < :PCSD_begDate
							and pol.Polis_endDate >= :PCSD_begDate
							and pol.Polis_endDate <= :PCSD_endDate
					) as HasPolisBefore
					outer apply (
					   Select top 1 Polis.Polis_id
					   from
						v_Person_all Person (nolock)
						left join v_Polis Polis (nolock) on Person.Polis_id = Polis.Polis_id
					   where
						Person.Person_id = PS.Person_id
						and Person.Server_id = 0
						and ( cast(convert(varchar(10), Polis.Polis_begDate, 112) as datetime) < :PCSD_endDate
						and (Polis.Polis_endDate is null or cast(convert(varchar(10), Polis.Polis_endDate, 112) as datetime) > :PCSD_endDate))
					  ) as Polis
					 outer apply (
							select top 1
                                pclast.PersonCard_id,
								pclast.Lpu_id
                            from
                                PersonCard pclast with (nolock)
							where
								PCSD.Person_id = pclast.Person_id and
								pclast.PersonCard_id < PCSD.PersonCard_id and
								pclast.LpuAttachType_id = PCSD.LpuAttachType_id
							order by
								pclast.PersonCard_id desc
                     ) as LastCard
					 outer apply (
							select top 1
                                pclast.PersonCard_id,
								pclast.Lpu_id,
								pclast.PersonCard_begDate
                            from
                                PersonCard pclast with (nolock)
							where
								PCSD.Person_id = pclast.Person_id and
								pclast.PersonCard_id > PCSD.PersonCard_id and
								pclast.LpuAttachType_id = PCSD.LpuAttachType_id
							order by
								pclast.PersonCard_id asc
                     ) as NextCard
				";

				if ( $data['PCSD_LpuMotion_id'] == 2 )
					$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id != PCSD.Lpu_id ";
				if ( $data['PCSD_LpuMotion_id'] == 3 )
					$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id = PCSD.Lpu_id ";

				if ( isset($data['PCSD_FromLpu_id']) && (int)$data['PCSD_FromLpu_id'] > 0 )
				{
					$queryParams['FromLpu_id'] = (int)$data['PCSD_FromLpu_id'];
					$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id = :FromLpu_id ";
				}

				if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
				{
					$queryParams['ToLpu_id'] = (int)$data['PCSD_ToLpu_id'];
					$filter .= " and NextCard.Lpu_id is not NULL and NextCard.Lpu_id = :ToLpu_id ";
				}
				
				if ( isset($data['PCSD_LpuRegion_id']) ) {
					$queryParams['PCSD_LpuRegion_id'] = $data['PCSD_LpuRegion_id'];
					$filter .= " and PCSD.LpuRegion_id = :PCSD_LpuRegion_id ";
				}
				else
				{
					$filter .= " and PCSD.LpuRegion_id is null and PCSD.Lpu_id = :Lpu_id ";
				}
				/*
				 * @todo: Фильтры, Журнал движения РПН, Просмотр Деталей.
				 */
				switch ( $data['PCSD_mode'] )
				{
					case 'BegCount':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) < :PCSD_begDate
							and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'BegCountBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) < :PCSD_begDate
							and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
							and (Polis.Polis_id is not null )
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'EndCount':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) <= :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) > :PCSD_endDate)
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'EndCountBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) <= :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) > :PCSD_endDate)
							and (Polis.Polis_id is not null )
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'AttachCount':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'AttachCountBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate
							and (Polis.Polis_id is not null )
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'AttachIncomeBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) between '1970-01-01' and :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) >= :PCSD_begDate)
							and PS.Server_pid = 0
							and hasPolis.cnt is not null
							and notHasPolis.cnt is null
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'AttachOutcomeBDZ':
						$filter .= "
							and cast(convert(varchar(10), PCSD.PersonCard_begDate, 112) as datetime) between '1970-01-01' and :PCSD_endDate
							and (PCSD.PersonCard_endDate is null or cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) >= :PCSD_begDate)
							and PS.Server_pid = 0
							and HasPolisBefore.cnt is not null
						";
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= ' and 1 = 2 ';
						}
					break;
					case 'DettachCount':
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= "
								and cast(convert(varchar(10), NextCard.PersonCard_begDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate and NextCard.Lpu_id = :ToLpu_id
							";
						}
						else
						{
							$filter .= "
								and cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate
							";
						}
					break;
					case 'DettachCountBDZ':
						if ( isset($data['PCSD_ToLpu_id']) && (int)$data['PCSD_ToLpu_id'] > 0 )
						{
							$filter .= "
								and cast(convert(varchar(10), NextCard.PersonCard_begDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate and NextCard.Lpu_id = :ToLpu_id
								and (Polis.Polis_id is not null)
							";
						}
						else
						{
							$filter .= "
								and cast(convert(varchar(10), PCSD.PersonCard_endDate, 112) as datetime) between :PCSD_begDate and :PCSD_endDate
								and (Polis.Polis_id is not null )
							";
						}
					break;

				}
				$queryParams['PCSD_begDate'] = $data['PCSD_StartDate'];
				$queryParams['PCSD_endDate'] = $data['PCSD_EndDate'];
			break;
			case 'PersonDisp':
				$query .= " inner join v_PersonDisp PD with (nolock) on PD.Person_id = PS.Person_id ";
				$query .= " left join Diag dg1 with (nolock) on PD.Diag_id = dg1.Diag_id ";
				$query .= " left join Diag dg2 with (nolock) on PD.Diag_pid = dg2.Diag_id ";
				$query .= " left join Diag dg3 with (nolock) on PD.Diag_nid = dg3.Diag_id ";
				$query .= " left join v_MedPersonal mp1 with (nolock) on PD.MedPersonal_id = mp1.MedPersonal_id and PD.Lpu_id = mp1.Lpu_id ";
				$query .= " left join LpuSection lpus1 with (nolock) on PD.LpuSection_id = lpus1.LpuSection_id ";
				$query .= " left join Sickness scks with (nolock) on PD.Sickness_id = scks.Sickness_id ";
				$query .= " left join v_Lpu lpu1 with (nolock) on PD.Lpu_id = lpu1.Lpu_id ";
				$query .= " outer apply (
					select
						max(
							case when 
								convert(varchar(10), PersonDispMedicament_begDate, 112) <= convert(varchar(10), dbo.tzGetDate(), 112)
								and (
									convert(varchar(10), PersonDispMedicament_endDate, 112) >= convert(varchar(10), dbo.tzGetDate(), 112)
									or PersonDispMedicament_endDate is null
									)
							then 1 
							else 
								case when 
									convert(varchar(10), PersonDispMedicament_begDate, 112) > convert(varchar(10), dbo.tzGetDate(), 112)
								then 
									0 
								else 
									NULL 
								end
							end
						) as isnoz
					from
						PersonDispMedicament pdm  with (nolock)
					where
						pdm.PersonDisp_id = PD.PersonDisp_id
						and pdm.PersonDispMedicament_begDate is not null
				) as noz ";
				
				$filter .= " and PD.Lpu_id = ".$data['session']['lpu_id']." ";
				
				// Отображать карты ДУ
				if ( isset($data['ViewAll_id']) && $data['ViewAll_id'] == 1 )
					$filter .= " and ( PD.PersonDisp_endDate is null or PD.PersonDisp_endDate > dbo.tzGetDate() ) ";
				// Отделение
				if ( isset($data['DispLpuSection_id']) )
				{
					$filter .= " and PD.LpuSection_id = :DispLpuSection_id ";
					$queryParams['DispLpuSection_id'] = $data['DispLpuSection_id'];
				}
				// Врач
				if ( isset($data['DispMedPersonal_id']) )
				{
					$filter .= " and PD.MedPersonal_id = :DispMedPersonal_id ";
					$queryParams['DispMedPersonal_id'] = $data['DispMedPersonal_id'];
				}
				// Дата постановки
				if ( isset($data['PersonDisp_begDate']) ) {
					$filter .= " and PD.PersonDisp_begDate = cast(:PersonDisp_begDate as datetime) ";
					$queryParams['PersonDisp_begDate'] = $data['PersonDisp_begDate'];
				}
				if ( isset($data['PersonDisp_begDate_Range'][0]) ) {
					$filter .= " and PD.PersonDisp_begDate >= cast(:PersonDisp_begDate_Range_0 as datetime) ";
					$queryParams['PersonDisp_begDate_Range_0'] = $data['PersonDisp_begDate_Range'][0];
				}
				if ( isset($data['PersonDisp_begDate_Range'][1]) ) {
					$filter .= " and PD.PersonDisp_begDate <= cast(:PersonDisp_begDate_Range_1 as datetime) ";
					$queryParams['PersonDisp_begDate_Range_1'] = $data['PersonDisp_begDate_Range'][1];
				}
				// Дата снятия
				if ( isset($data['PersonDisp_endDate']) ) {
					$filter .= " and PD.PersonDisp_endDate = cast(:PersonDisp_endDate as datetime) ";
					$queryParams['PersonDisp_endDate'] = $data['PersonDisp_endDate'];
				}
				if ( isset($data['PersonDisp_endDate_Range'][0]) ) {
					$filter .= " and PD.PersonDisp_endDate >= cast(:PersonDisp_endDate_Range_0 as datetime) ";
					$queryParams['PersonDisp_endDate_Range_0'] = $data['PersonDisp_endDate_Range'][0];
				}
				if ( isset($data['PersonDisp_endDate_Range'][1]) ) {
					$filter .= " and PD.PersonDisp_endDate <= cast(:PersonDisp_endDate_Range_1 as datetime) ";
					$queryParams['PersonDisp_endDate_Range_1'] = $data['PersonDisp_endDate_Range'][1];
				}
				// Дата следующего посещения
				if ( isset($data['PersonDisp_NextDate']) ) {
					$filter .= " and PD.PersonDisp_NextDate = cast(:PersonDisp_NextDate as datetime) ";
					$queryParams['PersonDisp_NextDate'] = $data['PersonDisp_NextDate'];
				}
				if ( isset($data['PersonDisp_NextDate_Range'][0]) ) {
					$filter .= " and PD.PersonDisp_NextDate >= cast(:PersonDisp_NextDate_Range_0 as datetime) ";
					$queryParams['PersonDisp_NextDate_Range_0'] = $data['PersonDisp_NextDate_Range'][0];
				}
				if ( isset($data['PersonDisp_NextDate_Range'][1]) ) {
					$filter .= " and PD.PersonDisp_NextDate <= cast(:PersonDisp_NextDate_Range_1 as datetime) ";
					$queryParams['PersonDisp_NextDate_Range_1'] = $data['PersonDisp_NextDate_Range'][1];
				}
				// Причина снятия
				if ( isset($data['DispOutType_id']) )
				{
					$filter .= " and PD.DispOutType_id = :DispOutType_id ";
					$queryParams['DispOutType_id'] = $data['DispOutType_id'];
				}
				// По результатам доп. дисп.
				if ( isset($data['PersonDisp_IsDop']) )
				{
					$filter .= " and isnull(PD.PersonDisp_IsDop, 1) = :PersonDisp_IsDop ";
					$queryParams['PersonDisp_IsDop'] = $data['PersonDisp_IsDop'];
				}
				// диагнозы
				if ( isset($data['Sickness_id']) )
				{
					$filter .= " and PD.Sickness_id = :Sickness_id ";
					$queryParams['Sickness_id'] = $data['Sickness_id'];
				}
				if ( isset($data['Disp_Diag_id']) )
				{
					$filter .= " and PD.Diag_id = :Disp_Diag_id ";
					$queryParams['Disp_Diag_id'] = $data['Disp_Diag_id'];
				}
				if ( strlen($data['Disp_Diag_Code_From']) > 0 ) {
					$filter .= " and dg1.Diag_Code >= :Disp_Diag_Code_From";
					$queryParams['Disp_Diag_Code_From'] = $data['Disp_Diag_Code_From'];
				}
				if ( strlen($data['Disp_Diag_Code_To']) > 0 ) {
					$filter .= " and dg1.Diag_Code <= :Disp_Diag_Code_To";
					$queryParams['Disp_Diag_Code_To'] = $data['Disp_Diag_Code_To'];
				}
				
				if ( isset($data['Disp_Diag_pid']) )
				{
					$filter .= " and PD.Diag_pid = :Disp_Diag_pid ";
					$queryParams['Disp_Diag_pid'] = $data['Disp_Diag_pid'];
				}
				if ( strlen($data['Disp_PredDiag_Code_From']) > 0 ) {
					$filter .= " and dg2.Diag_Code >= :Disp_PredDiag_Code_From";
					$queryParams['Disp_PredDiag_Code_From'] = $data['Disp_PredDiag_Code_From'];
				}
				if ( strlen($data['Disp_PredDiag_Code_To']) > 0 ) {
					$filter .= " and dg2.Diag_Code <= :Disp_PredDiag_Code_To";
					$queryParams['Disp_PredDiag_Code_To'] = $data['Disp_PredDiag_Code_To'];
				}
				
				if ( isset($data['Disp_Diag_nid']) )
				{
					$filter .= " and PD.Diag_nid = :Disp_Diag_nid ";
					$queryParams['Disp_Diag_nid'] = $data['Disp_Diag_nid'];
				}
				if ( strlen($data['Disp_NewDiag_Code_From']) > 0 ) {
					$filter .= " and dg3.Diag_Code >= :Disp_NewDiag_Code_From";
					$queryParams['Disp_NewDiag_Code_From'] = $data['Disp_NewDiag_Code_From'];
				}
				if ( strlen($data['Disp_NewDiag_Code_To']) > 0 ) {
					$filter .= " and dg3.Diag_Code <= :Disp_NewDiag_Code_To";
					$queryParams['Disp_NewDiag_Code_To'] = $data['Disp_NewDiag_Code_To'];
				}
			break;
			
			case 'EvnInfectNotify':
				$query .= " inner join v_EvnInfectNotify EIN with (nolock) on ". ( ($data['PersonPeriodicType_id'] == 2) ? 'EIN.PersonEvn_id = PS.PersonEvn_id and EIN.Server_id = PS.Server_id' : 'EIN.Person_id = PS.Person_id' ) ;
				$query .= " left join v_EvnVizitPL EVPL with (nolock) on EIN.EvnInfectNotify_pid = EVPL.EvnVizitPL_id ";
				$query .= " left join v_EvnSection ES with (nolock) on EIN.EvnInfectNotify_pid = ES.EvnSection_id ";
				$query .= " 					
					outer apply (
						select TOP 1
							Lpu_id
						from 
							v_PersonCard pc with (nolock)
						WHERE 
							pc.Person_id = PS.Person_id
					) as PC ";
				$query .= " left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id ";
				$query .= " left join v_Diag Diag with (nolock) on Diag.Diag_id = EVPL.Diag_id ";
				$query .= " left join v_Diag Diag1 with (nolock) on Diag1.Diag_id = ES.Diag_id ";
				
				if ( isset($data['Diag_Code_From']) ) {
					$filter .= " and ( Diag.Diag_Code >= :Diag_Code_From or Diag1.Diag_Code >= :Diag_Code_From ) ";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if ( isset($data['Diag_Code_To']) ) {
					$filter .= " and ( Diag.Diag_Code <= :Diag_Code_To or Diag1.Diag_Code <= :Diag_Code_To ) ";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}
				
				if ( isset($data['EvnNotifyBase_setDT_Range'][0]) ) {
					$filter .= " and EIN.EvnInfectNotify_insDT >= cast(:EvnInfectNotify_insDT_Range_0 as datetime) ";
					$queryParams['EvnInfectNotify_insDT_Range_0'] = $data['EvnNotifyBase_setDT_Range'][0].' 00:00:00';
				}
				
				if ( isset($data['EvnNotifyBase_setDT_Range'][1]) ) {
					$filter .= " and EIN.EvnInfectNotify_insDT <= cast(:EvnInfectNotify_insDT_Range_1 as datetime) ";
					$queryParams['EvnInfectNotify_insDT_Range_1'] = $data['EvnNotifyBase_setDT_Range'][1].' 23:59:59';
				}
				
			break;

			case 'EvnNotifyHepatitis':				
				$query .= '
					inner join v_EvnNotifyHepatitis ENH with (nolock) on '. ( ($data['PersonPeriodicType_id'] == 2) ? 'ENH.PersonEvn_id = PS.PersonEvn_id and ENH.Server_id = PS.Server_id' : 'ENH.Person_id = PS.Person_id' ) .'
					inner join v_MorbusHepatitis MH with (nolock) on MH.Morbus_id = ENH.Morbus_id 
					outer apply (
						select TOP 1
							Lpu_id
						from 
							v_PersonCard pc with (nolock)
						WHERE 
							pc.Person_id = PS.Person_id
					) as PC
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id 
					left join v_PersonRegister PR with (nolock) on ENH.EvnNotifyHepatitis_id = PR.EvnNotifyBase_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = isnull(MH.Diag_id,PR.Diag_id)
				';

				if ( empty($data['AttachLpu_id']) && (empty($data['session']['groups']) || strpos($data['session']['groups'], 'HepatitisRegistry') < 0) ) {
					$filter .= " and ( PC.Lpu_id  = :AttachLpu_id ) ";
					$queryParams['AttachLpu_id'] = $data['session']['lpu_id'];
				}
				
				if ( isset($data['Diag_Code_From']) ) {
					$filter .= " and ( Diag.Diag_Code >= :Diag_Code_From ) ";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if ( isset($data['Diag_Code_To']) ) {
					$filter .= " and ( Diag.Diag_Code <= :Diag_Code_To ) ";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}
				
				
				if ( isset($data['EvnNotifyBase_setDT_Range'][0]) ) {
					$filter .= " and ENH.EvnNotifyHepatitis_setDT >= cast(:EvnNotifyHepatitis_setDT_Range_0 as datetime) ";
					$queryParams['EvnNotifyHepatitis_setDT_Range_0'] = $data['EvnNotifyBase_setDT_Range'][0];
				}
				
				if ( isset($data['EvnNotifyBase_setDT_Range'][1]) ) {
					$filter .= " and ENH.EvnNotifyHepatitis_setDT <= cast(:EvnNotifyHepatitis_setDT_Range_1 as datetime) ";
					$queryParams['EvnNotifyHepatitis_setDT_Range_1'] = $data['EvnNotifyBase_setDT_Range'][1];
				}
								
				if ( isset($data['isNotifyProcessed']) )
				{
					if ( $data['isNotifyProcessed'] == 1 ) {
						$filter .= '
					and ENH.EvnNotifyHepatitis_niDate is null
					and PR.PersonRegister_id is null
						';
					} elseif ( $data['isNotifyProcessed'] == 2 ) {
						$filter .= '
					and (ENH.EvnNotifyHepatitis_niDate is not null or PR.PersonRegister_id is not null)
						';
					}
				}
				
			break;

			case 'EvnOnkoNotify':				
				$query .= '
					inner join v_EvnOnkoNotify EON with (nolock) on '. ( ($data['PersonPeriodicType_id'] == 2) ? 'EON.PersonEvn_id = PS.PersonEvn_id and EON.Server_id = PS.Server_id' : 'EON.Person_id = PS.Person_id' ) .'
					inner join v_Morbus M with (nolock) on M.Morbus_id = EON.Morbus_id 
					inner join v_MorbusOnko MO with (nolock) on MO.Morbus_id = M.Morbus_id 
					outer apply (
						select TOP 1
							Lpu_id
						from 
							v_PersonCard pc with (nolock)
						WHERE 
							pc.Person_id = PS.Person_id
					) as PC
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id 
					left join v_Lpu Lpu1 with (nolock) on Lpu1.Lpu_id = EON.Lpu_sid 
					left join v_Diag Diag with (nolock) on Diag.Diag_id = M.Diag_id 
					left join v_OnkoDiag OnkoDiag with (nolock) on OnkoDiag.OnkoDiag_id = MO.OnkoDiag_mid 
					left join v_TumorStage TumorStage with (nolock) on TumorStage.TumorStage_id = MO.TumorStage_id 
					left join v_EvnOnkoNotifyNeglected EONN with (nolock) on EONN.EvnOnkoNotify_id = EON.EvnOnkoNotify_id 
					left join v_PersonRegister PR with (nolock) on EON.EvnOnkoNotify_id = PR.EvnNotifyBase_id
				';

				if ( empty($data['isOnlyTheir']) && (empty($data['session']['groups']) || strpos($data['session']['groups'], 'OnkoRegistry') < 0) ) {
					$filter .= " and ( EON.pmUser_insID  = :pmUser_id OR EONN.pmUser_insID  = :pmUser_id ) ";
					$queryParams['pmUser_id'] = $data['pmUser_id'];
				}

				if ( isset($data['Diag_Code_From']) ) {
					$filter .= " and ( Diag.Diag_Code >= :Diag_Code_From ) ";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if ( isset($data['Diag_Code_To']) ) {
					$filter .= " and ( Diag.Diag_Code <= :Diag_Code_To ) ";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}
				
				if ( isset($data['OnkoDiag_Code_From']) ) {
					$filter .= " and ( OnkoDiag.OnkoDiag_id >= :OnkoDiag_Code_From ) ";
					$queryParams['OnkoDiag_Code_From'] = $data['OnkoDiag_Code_From'];
				}

				if ( isset($data['OnkoDiag_Code_To']) ) {
					$filter .= " and ( OnkoDiag.OnkoDiag_id <= :OnkoDiag_Code_To ) ";
					$queryParams['OnkoDiag_Code_To'] = $data['OnkoDiag_Code_To'];
				}
				
				if ( isset($data['Lpu_sid']) )
				{
					$filter .= " and EON.Lpu_sid = :Lpu_sid ";
					$queryParams['Lpu_sid'] = $data['Lpu_sid'];
				}
				
				if ( isset($data['EvnNotifyBase_setDT_Range'][0]) ) {
					$filter .= " and EON.EvnOnkoNotify_setDT >= cast(:EvnOnkoNotify_setDT_Range_0 as datetime) ";
					$queryParams['EvnOnkoNotify_setDT_Range_0'] = $data['EvnNotifyBase_setDT_Range'][0];
				}
				
				if ( isset($data['EvnNotifyBase_setDT_Range'][1]) ) {
					$filter .= " and EON.EvnOnkoNotify_setDT <= cast(:EvnOnkoNotify_setDT_Range_1 as datetime) ";
					$queryParams['EvnOnkoNotify_setDT_Range_1'] = $data['EvnNotifyBase_setDT_Range'][1];
				}
				
				if ( isset($data['isNeglected']) )
				{
					if ( $data['isNeglected'] == 1 ) {
						$filter .= " and EONN.EvnOnkoNotifyNeglected_id IS NULL ";
					} elseif ( $data['isNeglected'] == 2 ) {
						$filter .= " and EONN.EvnOnkoNotifyNeglected_id IS NOT NULL ";
					}
				}
				
				if ( isset($data['TumorStage_id']) )
				{
					$filter .= " and MO.TumorStage_id = :TumorStage_id ";
					$queryParams['TumorStage_id'] = $data['TumorStage_id'];
				}
				
				if ( isset($data['TumorCircumIdentType_id']) )
				{
					$filter .= " and MO.TumorCircumIdentType_id = :TumorCircumIdentType_id ";
					$queryParams['TumorCircumIdentType_id'] = $data['TumorCircumIdentType_id'];
				}
				
				if ( isset($data['isNotifyProcessed']) )
				{
					if ( $data['isNotifyProcessed'] == 1 ) {
						$filter .= '
					and EON.EvnOnkoNotify_niDate is null
					and PR.PersonRegister_id is null
						';
					} elseif ( $data['isNotifyProcessed'] == 2 ) {
						$filter .= '
					and (EON.EvnOnkoNotify_niDate is not null or PR.PersonRegister_id is not null)
						';
					}
				}
				
			break;
						
			case 'OnkoRegistry':
				$query .= '
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.MorbusType_id = 3
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_EvnOnkoNotify EON with (nolock) on EON.EvnOnkoNotify_id = PR.EvnNotifyBase_id
					left join v_Morbus M with (nolock) on M.Morbus_id = isnull(EON.Morbus_id,PR.Morbus_id)
					left join v_MorbusOnko MO with (nolock) on MO.Morbus_id = M.Morbus_id
					left join v_MorbusOnkoBase MOB with (nolock) on MOB.MorbusBase_id = M.MorbusBase_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = PR.Diag_id
					left join v_OnkoDiag OnkoDiag with (nolock) on OnkoDiag.OnkoDiag_id = MO.OnkoDiag_mid
					left join v_TumorStage TumorStage with (nolock) on TumorStage.TumorStage_id = MO.TumorStage_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
				';

				// Регистр
				if ( !empty($data['PersonRegisterType_id']) )
				{
					if ( $data['PersonRegisterType_id'] == 2 ) {
						$filter .= " and PR.PersonRegister_disDate is null ";
					} elseif ( $data['PersonRegisterType_id'] == 3 ) {
						$filter .= " and PR.PersonRegister_disDate is not null ";
					}
				}


				switch ( $data['PersonRegisterRecordType_id'] ) {
					case 1: // все
						break;
					case 2: // все, состоящие на учете
						$filter .= " and MOB.OnkoRegType_id is not null and MOB.OnkoRegOutType_id is null ";
						break;
					case 3: // все выехавшие
						$filter .= " and MOB.OnkoRegOutType_id = 1 ";
						break;
					case 4: // все, у которых диагноз не подтвердился
						$filter .= " and MOB.OnkoRegOutType_id = 2 ";
						break;
					case 5: // все, «снятые по базалиоме»
						$filter .= " and MOB.OnkoRegOutType_id = 3 ";
						break;
					case 6: // все умершие
						$filter .= " and MOB.OnkoRegOutType_id in (4,5,6) ";
						break;
				}

				if ( isset($data['PersonRegister_setDate_Range'][0]) ) {
					$filter .= " and PR.PersonRegister_setDate >= cast(:PersonRegister_setDate_Range_0 as datetime) ";
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
				}
				if ( isset($data['PersonRegister_setDate_Range'][1]) ) {
					$filter .= " and PR.PersonRegister_setDate <= cast(:PersonRegister_setDate_Range_1 as datetime) ";
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
				}
				
				if ( isset($data['PersonRegister_disDate_Range'][0]) ) {
					$filter .= " and PR.PersonRegister_disDate >= cast(:PersonRegister_disDate_Range_0 as datetime) ";
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
				}
				if ( isset($data['PersonRegister_disDate_Range'][1]) ) {
					$filter .= " and PR.PersonRegister_disDate <= cast(:PersonRegister_disDate_Range_1 as datetime) ";
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
				}
				
				// диагноз
				if ( isset($data['Diag_Code_From']) ) {
					$filter .= " and ( Diag.Diag_Code >= :Diag_Code_From ) ";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}
				if ( isset($data['Diag_Code_To']) ) {
					$filter .= " and ( Diag.Diag_Code <= :Diag_Code_To ) ";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}
				if ( !empty($data['MorbusOnko_setDiagDT_Range'][0]) ) {
					$filter .= " and MO.MorbusOnko_setDiagDT >= cast(:MorbusOnko_setDiagDT_Range_0 as datetime) ";
					$queryParams['MorbusOnko_setDiagDT_Range_0'] = $data['MorbusOnko_setDiagDT_Range'][0];
				}
				if ( !empty($data['MorbusOnko_setDiagDT_Range'][1]) ) {
					$filter .= " and MO.MorbusOnko_setDiagDT <= cast(:MorbusOnko_setDiagDT_Range_1 as datetime) ";
					$queryParams['MorbusOnko_setDiagDT_Range_1'] = $data['MorbusOnko_setDiagDT_Range'][1];
				}
				if ( !empty($data['MorbusOnko_IsMainTumor']) )
				{
					$filter .= " and isnull(MO.MorbusOnko_IsMainTumor,1) = :MorbusOnko_IsMainTumor ";
					$queryParams['MorbusOnko_IsMainTumor'] = $data['MorbusOnko_IsMainTumor'];
				}
				if ( !empty($data['Diag_mid']) )
				{
					$filter .= " and MO.OnkoDiag_mid = :Diag_mid ";
					$queryParams['Diag_mid'] = $data['Diag_mid'];
				}
				if ( !empty($data['TumorStage_id']) )
				{
					$filter .= " and MO.TumorStage_id = :TumorStage_id ";
					$queryParams['TumorStage_id'] = $data['TumorStage_id'];
				}
				
				// Спец. лечение
				if ( !empty($data['TumorPrimaryTreatType_id']) )
				{
					$filter .= ' and MO.TumorPrimaryTreatType_id = :TumorPrimaryTreatType_id ';
					$queryParams['TumorPrimaryTreatType_id'] = $data['TumorPrimaryTreatType_id'];
				}
				if ( !empty($data['TumorRadicalTreatIncomplType_id']) )
				{
					$filter .= ' and MO.TumorRadicalTreatIncomplType_id = :TumorRadicalTreatIncomplType_id ';
					$queryParams['TumorRadicalTreatIncomplType_id'] = $data['TumorRadicalTreatIncomplType_id'];
				}
				if ( isset($data['MorbusOnkoSpecTreat_begDate_Range'][0]) && isset($data['MorbusOnkoSpecTreat_begDate_Range'][1]) ) {
					$filter .= ' and MO.MorbusOnko_specSetDT between cast(:MorbusOnkoSpecTreat_begDate_Range_0 as datetime) and cast(:MorbusOnkoSpecTreat_begDate_Range_1 as datetime) ';
					$queryParams['MorbusOnkoSpecTreat_begDate_Range_0'] = $data['MorbusOnkoSpecTreat_begDate_Range'][0];
					$queryParams['MorbusOnkoSpecTreat_begDate_Range_1'] = $data['MorbusOnkoSpecTreat_begDate_Range'][1];
				}
				if ( isset($data['MorbusOnkoSpecTreat_endDate_Range'][0]) && isset($data['MorbusOnkoSpecTreat_endDate_Range'][1]) ) {
					$filter .= ' and MO.MorbusOnko_specDisDT between cast(:MorbusOnkoSpecTreat_endDate_Range_0 as datetime) and cast(:MorbusOnkoSpecTreat_endDate_Range_1 as datetime) ';
					$queryParams['MorbusOnkoSpecTreat_endDate_Range_0'] = $data['MorbusOnkoSpecTreat_endDate_Range'][0];
					$queryParams['MorbusOnkoSpecTreat_endDate_Range_1'] = $data['MorbusOnkoSpecTreat_endDate_Range'][1];
				}
				
				/*
				if(  
				)
				{
					$filterDop = '';
					$query .= '
						inner join v_MorbusOnkoSpecTreat MOST with (nolock) on MOST.MorbusOnko_id = MO.MorbusOnko_id '. $filterDop ;
				}
				*/

				// Контроль состояния
				if(  
					!empty($data['OnkoTumorStatusType_id'])
					|| !empty($data['OnkoPersonStateType_id'])
				)
				{
					$filterDop = '';
					if ( !empty($data['OnkoTumorStatusType_id']) )
					{
						$filterDop .= ' and MOBPS.OnkoTumorStatusType_id = :OnkoTumorStatusType_id ';
						$queryParams['OnkoTumorStatusType_id'] = $data['OnkoTumorStatusType_id'];
					}
					if ( !empty($data['OnkoPersonStateType_id']) )
					{
						$filterDop .= ' and MOBPS.OnkoPersonStateType_id = :OnkoPersonStateType_id ';
						$queryParams['OnkoPersonStateType_id'] = $data['OnkoPersonStateType_id'];
					}
					$query .= '
						inner join v_MorbusOnkoBasePersonState MOBPS with (nolock) on MOBPS.MorbusOnkoBase_id = MOB.MorbusOnkoBase_id '. $filterDop ;
				}
				if ( !empty($data['OnkoStatusYearEndType_id']) )
				{
					$filterDop = ' and MOBSYE.OnkoStatusYearEndType_id = :OnkoStatusYearEndType_id ';
					$queryParams['OnkoStatusYearEndType_id'] = $data['OnkoStatusYearEndType_id'];
					$query .= '
						inner join v_MorbusOnkoBaseStatusYearEnd MOBSYE with (nolock) on MOBSYE.MorbusOnkoBase_id = MOB.MorbusOnkoBase_id '. $filterDop ;
				}
				
			break;				
			
			case 'HepatitisRegistry':
				$query .= '
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.MorbusType_id = 5
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_EvnNotifyHepatitis ENH with (nolock) on ENH.EvnNotifyHepatitis_id = PR.EvnNotifyBase_id
					left join v_MorbusHepatitis MH with (nolock) on MH.Morbus_id = isnull(ENH.Morbus_id,PR.Morbus_id)
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = PR.Diag_id  
					outer apply (
						select TOP 1
							HepatitisDiagType_id
							,MorbusHepatitisDiag_setDT
							,HepatitisDiagActiveType_id
							,HepatitisFibrosisType_id
						from 
							v_MorbusHepatitisDiag MHD with (nolock)
						WHERE 
							MH.MorbusHepatitis_id = MHD.MorbusHepatitis_id
						ORDER BY
							MorbusHepatitisDiag_setDT DESC
					) as MHD
					left join v_HepatitisDiagType HDT with (nolock) on HDT.HepatitisDiagType_id = MHD.HepatitisDiagType_id  
					outer apply (
						select TOP 1
							MorbusHepatitisQueue_Num,
							HepatitisQueueType_id,
							MorbusHepatitisQueue_IsCure
						from 
							v_MorbusHepatitisQueue MHQ with (nolock)
						WHERE 
							MH.MorbusHepatitis_id = MHQ.MorbusHepatitis_id
						ORDER BY
							MorbusHepatitisQueue_IsCure ASC
					) as MHQ
					left join v_HepatitisQueueType HQT with (nolock) on HQT.HepatitisQueueType_id = MHQ.HepatitisQueueType_id  
					left join v_YesNo IsCure with (nolock) on IsCure.YesNo_id = ISNULL(MHQ.MorbusHepatitisQueue_IsCure,1) 
				';

				if ( empty($data['AttachLpu_id']) && (empty($data['session']['groups']) || strpos($data['session']['groups'], 'HepatitisRegistry') < 0) ) {
					$filter .= " and ( PC.Lpu_id  = :AttachLpu_id ) ";
					$queryParams['AttachLpu_id'] = $data['session']['lpu_id'];
				}

				if ( isset($data['Diag_Code_From']) ) {
					$filter .= " and ( Diag.Diag_Code >= :Diag_Code_From ) ";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if ( isset($data['Diag_Code_To']) ) {
					$filter .= " and ( Diag.Diag_Code <= :Diag_Code_To ) ";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				if ( isset($data['Diag_id']) ) {
					$filter .= " and ( PR.Diag_id = :Diag_id ) ";
					$queryParams['Diag_id'] = $data['Diag_id'];
				}
				
				// Диспучёт
				/*				
				if ( isset($data['DispLpu_id']) )
				{
					$filter .= " and PD.Lpu_id = :DispLpu_id ";
					$queryParams['DispLpu_id'] = $data['DispLpu_id'];
				}
				if ( isset($data['DispLpuSection_id']) )
				{
					$filter .= " and PD.LpuSection_id = :DispLpuSection_id ";
					$queryParams['DispLpuSection_id'] = $data['DispLpuSection_id'];
				}
				if ( isset($data['DispMedPersonal_id']) )
				{
					$filter .= " and PD.MedPersonal_id = :DispMedPersonal_id ";
					$queryParams['DispMedPersonal_id'] = $data['DispMedPersonal_id'];
				}
				if ( isset($data['PersonDisp_begDate_Range'][0]) ) {
					$filter .= " and PD.PersonDisp_begDate >= cast(:PersonDisp_begDate_Range_0 as datetime) ";
					$queryParams['PersonDisp_begDate_Range_0'] = $data['PersonDisp_begDate_Range'][0];
				}
				if ( isset($data['PersonDisp_begDate_Range'][1]) ) {
					$filter .= " and PD.PersonDisp_begDate <= cast(:PersonDisp_begDate_Range_1 as datetime) ";
					$queryParams['PersonDisp_begDate_Range_1'] = $data['PersonDisp_begDate_Range'][1];
				}
				if ( isset($data['PersonDisp_endDate_Range'][0]) ) {
					$filter .= " and PD.PersonDisp_endDate >= cast(:PersonDisp_endDate_Range_0 as datetime) ";
					$queryParams['PersonDisp_endDate_Range_0'] = $data['PersonDisp_endDate_Range'][0];
				}
				if ( isset($data['PersonDisp_endDate_Range'][1]) ) {
					$filter .= " and PD.PersonDisp_endDate <= cast(:PersonDisp_endDate_Range_1 as datetime) ";
					$queryParams['PersonDisp_endDate_Range_1'] = $data['PersonDisp_endDate_Range'][1];
				}
				if ( isset($data['DispOutType_id']) )
				{
					$filter .= " and PD.DispOutType_id = :DispOutType_id ";
					$queryParams['DispOutType_id'] = $data['DispOutType_id'];
				}	

				if ( !empty($data['isDispAttachAddress']) )
				{
					if ( $data['isDispAttachAddress'] == 1 ) {
						$filter .= " and PD.Lpu_id <> PC.Lpu_id ";
					} elseif ( $data['isDispAttachAddress'] == 2 ) {
						$filter .= " and PD.Lpu_id = PC.Lpu_id ";
					}
				}
				*/
				// регистр
				if ( !empty($data['PersonRegisterType_id']) )
				{
					if ( $data['PersonRegisterType_id'] == 2 )
					{
						// Включенные в регистр
						$filter .= ' and PR.PersonRegister_disDate is null ';
					}
					if ( $data['PersonRegisterType_id'] == 3 )
					{
						// Исключенные из регистра
						$filter .= ' and PR.PersonRegister_disDate is not null ';
					}
				}
				
				if ( isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1]) ) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}
			
				if ( isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1]) ) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				// диагноз
				if ( !empty($data['MorbusHepatitisDiag_setDT_Range'][0]) ) {
					$filter .= " and [MHD].MorbusHepatitisDiag_setDT >= cast(:MorbusHepatitisDiag_setDT_Range_0 as datetime) ";
					$queryParams['MorbusHepatitisDiag_setDT_Range_0'] = $data['MorbusHepatitisDiag_setDT_Range'][0];
				}
				if ( !empty($data['MorbusHepatitisDiag_setDT_Range'][1]) ) {
					$filter .= " and [MHD].MorbusHepatitisDiag_setDT <= cast(:MorbusHepatitisDiag_setDT_Range_1 as datetime) ";
					$queryParams['MorbusHepatitisDiag_setDT_Range_1'] = $data['MorbusHepatitisDiag_setDT_Range'][1];
				}
				
				if ( !empty($data['HepatitisDiagType_id']) )
				{
					$filter .= " and MHD.HepatitisDiagType_id = :HepatitisDiagType_id ";
					$queryParams['HepatitisDiagType_id'] = $data['HepatitisDiagType_id'];
				}
				
				if ( !empty($data['HepatitisDiagActiveType_id']) )
				{
					$filter .= " and MHD.HepatitisDiagActiveType_id = :HepatitisDiagActiveType_id ";
					$queryParams['HepatitisDiagActiveType_id'] = $data['HepatitisDiagActiveType_id'];
				}
				
				if ( !empty($data['HepatitisFibrosisType_id']) )
				{
					$filter .= " and MHD.HepatitisFibrosisType_id = :HepatitisFibrosisType_id ";
					$queryParams['HepatitisFibrosisType_id'] = $data['HepatitisFibrosisType_id'];
				}
				
				if ( !empty($data['HepatitisEpidemicMedHistoryType_id']) )
				{
					$filter .= " and MH.HepatitisEpidemicMedHistoryType_id = :HepatitisEpidemicMedHistoryType_id ";
					$queryParams['HepatitisEpidemicMedHistoryType_id'] = $data['HepatitisEpidemicMedHistoryType_id'];
				}
				
				if ( !empty($data['MorbusHepatitis_EpidNum']) )
				{
					$filter .= " and MH.MorbusHepatitis_EpidNum = :MorbusHepatitis_EpidNum ";
					$queryParams['MorbusHepatitis_EpidNum'] = $data['MorbusHepatitis_EpidNum'];
				}
				
				// Лаб. подтверждения
				if( (isset($data['MorbusHepatitisLabConfirm_setDT_Range'][0]) && isset($data['MorbusHepatitisLabConfirm_setDT_Range'][1])) || !empty($data['HepatitisLabConfirmType_id']) || !empty($data['MorbusHepatitisLabConfirm_Result']) ) {
					$filterDop = '';
					if ( isset($data['MorbusHepatitisLabConfirm_setDT_Range'][0]) && isset($data['MorbusHepatitisLabConfirm_setDT_Range'][1]) ) {
						$queryParams['MorbusHepatitisLabConfirm_setDT_Range_0'] = $data['MorbusHepatitisLabConfirm_setDT_Range'][0];
						$queryParams['MorbusHepatitisLabConfirm_setDT_Range_1'] = $data['MorbusHepatitisLabConfirm_setDT_Range'][1];
						$filterDop .= ' and cast(MHLC.MorbusHepatitisLabConfirm_setDT as date) between :MorbusHepatitisLabConfirm_setDT_Range_0 and :MorbusHepatitisLabConfirm_setDT_Range_1 ';
					}
					
					if ( !empty($data['HepatitisLabConfirmType_id']) )
					{
						$queryParams['HepatitisLabConfirmType_id'] = $data['HepatitisLabConfirmType_id'];
						$filterDop .= ' and MHLC.HepatitisLabConfirmType_id = :HepatitisLabConfirmType_id ';
					}
					
					if ( !empty($data['MorbusHepatitisLabConfirm_Result']) )
					{
						$queryParams['MorbusHepatitisLabConfirm_Result'] = '%' . $data['MorbusHepatitisLabConfirm_Result'] . '%';
						$filterDop .= ' and MHLC.MorbusHepatitisLabConfirm_Result LIKE :MorbusHepatitisLabConfirm_Result ';
					}
					$query .= '
						inner join v_MorbusHepatitisLabConfirm MHLC with (nolock) on MH.MorbusHepatitis_id = MHLC.MorbusHepatitis_id '. $filterDop ;
				}
				
				// Инстр. подтверждения
				if( (isset($data['MorbusHepatitisFuncConfirm_setDT_Range'][0]) && isset($data['MorbusHepatitisFuncConfirm_setDT_Range'][1])) || !empty($data['HepatitisFuncConfirmType_id']) || !empty($data['MorbusHepatitisFuncConfirm_Result']) ) {
					$filterDop = '';
					if ( isset($data['MorbusHepatitisFuncConfirm_setDT_Range'][0]) && isset($data['MorbusHepatitisFuncConfirm_setDT_Range'][1]) ) {
						$queryParams['MorbusHepatitisFuncConfirm_setDT_Range_0'] = $data['MorbusHepatitisFuncConfirm_setDT_Range'][0];
						$queryParams['MorbusHepatitisFuncConfirm_setDT_Range_1'] = $data['MorbusHepatitisFuncConfirm_setDT_Range'][1];
						$filterDop .= ' and cast(MHFC.MorbusHepatitisLabConfirm_setDT as date) between :MorbusHepatitisFuncConfirm_setDT_Range_0 and :MorbusHepatitisFuncConfirm_setDT_Range_1 ';
					}
					
					if ( !empty($data['HepatitisFuncConfirmType_id']) )
					{
						$queryParams['HepatitisFuncConfirmType_id'] = $data['HepatitisFuncConfirmType_id'];
						$filterDop .= ' and MHFC.HepatitisFuncConfirmType_id = :HepatitisFuncConfirmType_id ';
					}
					
					if ( !empty($data['MorbusHepatitisFuncConfirm_Result']) )
					{
						$queryParams['MorbusHepatitisFuncConfirm_Result'] = '%' . $data['MorbusHepatitisFuncConfirm_Result'] . '%';
						$filterDop .= ' and MHFC.MorbusHepatitisFuncConfirm_Result LIKE :MorbusHepatitisFuncConfirm_Result ';
					}
					$query .= '
						inner join v_MorbusHepatitisFuncConfirm MHFC with (nolock) on MH.MorbusHepatitis_id = MHFC.MorbusHepatitis_id '. $filterDop ;
				}
				
				// Лечение
				if( isset($data['MorbusHepatitisCure_begDT']) || isset($data['MorbusHepatitisCure_endDT']) || !empty($data['HepatitisResultClass_id']) || !empty($data['HepatitisSideEffectType_id']) || !empty($data['MorbusHepatitisCure_Drug']) ) {
					$filterDop = '';
					if ( isset($data['MorbusHepatitisCure_begDT']) ) {
						$queryParams['MorbusHepatitisCure_begDT'] = $data['MorbusHepatitisCure_begDT'];
						$filterDop .= ' and cast(MHC.MorbusHepatitisCure_begDT as date) >= :MorbusHepatitisCure_begDT and MHC.MorbusHepatitisCure_begDT is not null ';
					}
					
					if ( isset($data['MorbusHepatitisCure_endDT']) ) {
						$queryParams['MorbusHepatitisCure_endDT'] = $data['MorbusHepatitisCure_endDT'];
						$filterDop .= ' and cast(MHC.MorbusHepatitisCure_endDT as date) <= :MorbusHepatitisCure_endDT and MHC.MorbusHepatitisCure_endDT is not null ';
					}

					if ( !empty($data['HepatitisResultClass_id']) )
					{
						$queryParams['HepatitisResultClass_id'] = $data['HepatitisResultClass_id'];
						$filterDop .= ' and MHC.HepatitisResultClass_id = :HepatitisResultClass_id ';
					}
					
					if ( !empty($data['HepatitisSideEffectType_id']) )
					{
						$queryParams['HepatitisSideEffectType_id'] = $data['HepatitisSideEffectType_id'];
						$filterDop .= ' and MHC.HepatitisSideEffectType_id = :HepatitisSideEffectType_id ';
					}
					
					if ( !empty($data['MorbusHepatitisCure_Drug']) )
					{
						$queryParams['MorbusHepatitisCure_Drug'] = '%' . $data['MorbusHepatitisCure_Drug'] . '%';
						$filterDop .= ' and MHC.MorbusHepatitisCure_Drug LIKE :MorbusHepatitisCure_Drug ';
					}
					$query .= '
						inner join v_MorbusHepatitisCure MHC with (nolock) on MH.MorbusHepatitis_id = MHC.MorbusHepatitis_id '. $filterDop ;
				}
									
				// Очередь
				
				if ( !empty($data['HepatitisQueueType_id']) )
				{
					$filter .= " and MHQ.HepatitisQueueType_id = :HepatitisQueueType_id ";
					$queryParams['HepatitisQueueType_id'] = $data['HepatitisQueueType_id'];
				}
				
				if ( !empty($data['MorbusHepatitisQueue_Num']) )
				{
					$filter .= " and MHQ.MorbusHepatitisQueue_Num = :MorbusHepatitisQueue_Num ";
					$queryParams['MorbusHepatitisQueue_Num'] = $data['MorbusHepatitisQueue_Num'];
				}
				
				if ( !empty($data['MorbusHepatitisQueue_IsCure']) )
				{
					$filter .= " and isnull(MHQ.MorbusHepatitisQueue_IsCure,1) = :MorbusHepatitisQueue_IsCure ";
					$queryParams['MorbusHepatitisQueue_IsCure'] = $data['MorbusHepatitisQueue_IsCure'];
				}
				
				
				//print_r($data); exit;
				
			break;
			
			case 'EvnNotifyOrphan':
				$query .= "
					outer apply (
						select
							EvnNotifyOrphan.EvnNotifyOrphan_id,
							EvnNotifyOrphan.EvnNotifyOrphan_pid,
							EvnNotifyOrphan.EvnNotifyOrphan_setDT,
							'Направление на включение в регистр' as EvnNotifyType_Name,
							'EvnNotifyOrphan' as EvnNotifyType_SysNick,
							EvnNotifyOrphan.Morbus_id,
							6 as MorbusType_id,
							EvnNotifyOrphan.EvnNotifyOrphan_niDate,
							EvnNotifyOrphan.Lpu_oid,
							EvnNotifyOrphan.Lpu_id,
							EvnNotifyOrphan.MedPersonal_id,
							EvnNotifyOrphan.pmUser_updId,
							PersonRegister.PersonRegister_id
						from v_EvnNotifyOrphan EvnNotifyOrphan with (nolock) 
						left join PersonRegister with (nolock) on EvnNotifyOrphan.EvnNotifyOrphan_id = PersonRegister.EvnNotifyBase_id
						where ". ( ($data['PersonPeriodicType_id'] == 2) ? 'EvnNotifyOrphan.PersonEvn_id = PS.PersonEvn_id and EvnNotifyOrphan.Server_id = PS.Server_id' : 'EvnNotifyOrphan.Person_id = PS.Person_id' ) ."
						union all
						select
							EvnNotifyOrphanOut.EvnNotifyOrphanOut_id as EvnNotifyOrphan_id,
							EvnNotifyOrphanOut.EvnNotifyOrphanOut_pid as EvnNotifyOrphan_pid,
							EvnNotifyOrphanOut.EvnNotifyOrphanOut_setDT as EvnNotifyOrphan_setDT,
							' Извещение на исключение из регистра' as EvnNotifyType_Name,
							'EvnNotifyOrphanOut' as EvnNotifyType_SysNick,
							EvnNotifyOrphanOut.Morbus_id,
							6 as MorbusType_id,
							EvnNotifyOrphanOut.EvnNotifyOrphanOut_niDate as EvnNotifyOrphan_niDate,
							null as Lpu_oid,
							EvnNotifyOrphanOut.Lpu_id,
							EvnNotifyOrphanOut.MedPersonal_id,
							EvnNotifyOrphanOut.pmUser_updId,
							PersonRegister.PersonRegister_id
						from v_EvnNotifyOrphanOut EvnNotifyOrphanOut with (nolock) 
						inner join PersonRegister with (nolock) on EvnNotifyOrphanOut.Morbus_id = PersonRegister.Morbus_id
						where ". ( ($data['PersonPeriodicType_id'] == 2) ? 'EvnNotifyOrphanOut.PersonEvn_id = PS.PersonEvn_id and EvnNotifyOrphanOut.Server_id = PS.Server_id' : 'EvnNotifyOrphanOut.Person_id = PS.Person_id' ) ."
						union all
						select
							EvnDirectionOrphan.EvnDirectionOrphan_id as EvnNotifyOrphan_id,
							EvnDirectionOrphan.EvnDirectionOrphan_pid as EvnNotifyOrphan_pid,
							EvnDirectionOrphan.EvnDirectionOrphan_setDT as EvnNotifyOrphan_setDT,
							'Направление на внесение изменений в регистр' as EvnNotifyType_Name,
							'EvnDirectionOrphan' as EvnNotifyType_SysNick,
							EvnDirectionOrphan.Morbus_id,
							6 as MorbusType_id,
							null as EvnNotifyOrphan_niDate,
							null as Lpu_oid,
							EvnDirectionOrphan.Lpu_id,
							EvnDirectionOrphan.MedPersonal_id,
							EvnDirectionOrphan.pmUser_updId,
							PersonRegister.PersonRegister_id
						from v_EvnDirectionOrphan EvnDirectionOrphan with (nolock) 
						inner join PersonRegister with (nolock) on EvnDirectionOrphan.Morbus_id = PersonRegister.Morbus_id
						where ". ( ($data['PersonPeriodicType_id'] == 2) ? 'EvnDirectionOrphan.PersonEvn_id = PS.PersonEvn_id and EvnDirectionOrphan.Server_id = PS.Server_id' : 'EvnDirectionOrphan.Person_id = PS.Person_id' ) ."
					) ENO
					inner join v_Morbus MO with (nolock) on MO.Morbus_id = ENO.Morbus_id 
					left join v_PersonRegister PR with (nolock) on ENO.PersonRegister_id = PR.PersonRegister_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = isnull(MO.Diag_id,PR.Diag_id)
					left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = ENO.MedPersonal_id and MP.Lpu_id = ENO.Lpu_id
				";

				switch($data['EvnNotifyType_SysNick']) {
					case 'EvnNotifyOrphan':
						$filter .= '
					and ENO.EvnNotifyType_SysNick = \'EvnNotifyOrphan\'
					';
						break;
					case 'EvnNotifyOrphanOut':
						$filter .= '
					and ENO.EvnNotifyType_SysNick = \'EvnNotifyOrphanOut\'
					';
						break;
					case 'EvnDirectionOrphan':
						$filter .= '
					and ENO.EvnNotifyType_SysNick = \'EvnDirectionOrphan\'
					';
						break;
					default: //all
						break;
				}
				if ( isset($data['Diag_Code_From']) ) {
					$filter .= ' and ( Diag.Diag_Code >= :Diag_Code_From ) ';
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if ( isset($data['Diag_Code_To']) ) {
					$filter .= ' and ( Diag.Diag_Code <= :Diag_Code_To ) ';
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}
				
				
				if ( isset($data['EvnNotifyBase_setDT_Range'][0]) ) {
					$filter .= ' and ENO.EvnNotifyOrphan_setDT >= cast(:EvnNotifyBase_setDT_Range_0 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_0'] = $data['EvnNotifyBase_setDT_Range'][0];
				}
				
				if ( isset($data['EvnNotifyBase_setDT_Range'][1]) ) {
					$filter .= ' and ENO.EvnNotifyOrphan_setDT <= cast(:EvnNotifyBase_setDT_Range_1 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_1'] = $data['EvnNotifyBase_setDT_Range'][1];
				}
								
				if ( isset($data['Lpu_sid']) ) {
					$filter .= ' and ENO.Lpu_oid = :Lpu_oid ';
					$queryParams['Lpu_oid'] = $data['Lpu_sid'];
				}
								
				if ( isset($data['isNotifyProcessed']) )
				{
					if ( $data['isNotifyProcessed'] == 1 ) {
						$filter .= '
					and ENO.EvnNotifyType_SysNick = \'EvnNotifyOrphan\'
					and ENO.EvnNotifyOrphan_niDate is null
					and PR.PersonRegister_id is null
						';
					} elseif ( $data['isNotifyProcessed'] == 2 ) {
						$filter .= '
					and ENO.EvnNotifyType_SysNick = \'EvnNotifyOrphan\'
					and (ENO.EvnNotifyOrphan_niDate is not null or PR.PersonRegister_id is not null)
						';
					}
				}
			break;
			
			case 'EvnNotifyCrazy': // Психиатрия
				$query .= '
					inner join v_EvnNotifyCrazy ENC with (nolock) on '. ( ($data['PersonPeriodicType_id'] == 2) ? 'ENC.PersonEvn_id = PS.PersonEvn_id and ENC.Server_id = PS.Server_id' : 'ENC.Person_id = PS.Person_id' ) .'
					inner join v_Morbus MO with (nolock) on MO.Morbus_id = ENC.Morbus_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_PersonRegister PR with (nolock) on ENC.EvnNotifyCrazy_id = PR.EvnNotifyBase_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = isnull(MO.Diag_id,PR.Diag_id)
				';
				// ограничение по группе диагнозов по психиатрии
				$filter .= ' and ( Diag_pid not in (705,706,707,708,709,710,711,712,713,714) ) ';

				if ( isset($data['Diag_Code_From']) ) {
					$filter .= ' and ( Diag.Diag_Code >= :Diag_Code_From ) ';
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if ( isset($data['Diag_Code_To']) ) {
					$filter .= ' and ( Diag.Diag_Code <= :Diag_Code_To ) ';
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}


				if ( isset($data['EvnNotifyBase_setDT_Range'][0]) ) {
					$filter .= ' and ENC.EvnNotifyCrazy_setDT >= cast(:EvnNotifyBase_setDT_Range_0 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_0'] = $data['EvnNotifyBase_setDT_Range'][0];
				}

				if ( isset($data['EvnNotifyBase_setDT_Range'][1]) ) {
					$filter .= ' and ENC.EvnNotifyCrazy_setDT <= cast(:EvnNotifyBase_setDT_Range_1 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_1'] = $data['EvnNotifyBase_setDT_Range'][1];
				}

				/*if ( isset($data['Lpu_sid']) ) {
					$filter .= ' and ENC.Lpu_oid = :Lpu_oid ';
					$queryParams['Lpu_oid'] = $data['Lpu_sid'];
				}*/

				if ( isset($data['isNotifyProcessed']) )
				{
					if ( $data['isNotifyProcessed'] == 1 ) {
						$filter .= '
					and ENC.EvnNotifyCrazy_niDate is null
					and PR.PersonRegister_id is null
						';
					} elseif ( $data['isNotifyProcessed'] == 2 ) {
						$filter .= '
					and (ENC.EvnNotifyCrazy_niDate is not null or PR.PersonRegister_id is not null)
						';
					}
				}
				break;
			case 'EvnNotifyNarko': // Психиатрия
				$query .= '
					inner join v_EvnNotifyCrazy ENC with (nolock) on '. ( ($data['PersonPeriodicType_id'] == 2) ? 'ENC.PersonEvn_id = PS.PersonEvn_id and ENC.Server_id = PS.Server_id' : 'ENC.Person_id = PS.Person_id' ) .'
					inner join v_Morbus MO with (nolock) on MO.Morbus_id = ENC.Morbus_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_PersonRegister PR with (nolock) on ENC.EvnNotifyCrazy_id = PR.EvnNotifyBase_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = isnull(MO.Diag_id,PR.Diag_id)
				';
				// ограничение по группе диагнозов по наркологии
				$filter .= ' and ( Diag_pid in (705,706,707,708,709,710,711,712,713,714) ) ';

				if ( isset($data['Diag_Code_From']) ) {
					$filter .= ' and ( Diag.Diag_Code >= :Diag_Code_From ) ';
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if ( isset($data['Diag_Code_To']) ) {
					$filter .= ' and ( Diag.Diag_Code <= :Diag_Code_To ) ';
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				if ( isset($data['EvnNotifyBase_setDT_Range'][0]) ) {
					$filter .= ' and ENC.EvnNotifyCrazy_setDT >= cast(:EvnNotifyBase_setDT_Range_0 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_0'] = $data['EvnNotifyBase_setDT_Range'][0];
				}

				if ( isset($data['EvnNotifyBase_setDT_Range'][1]) ) {
					$filter .= ' and ENC.EvnNotifyCrazy_setDT <= cast(:EvnNotifyBase_setDT_Range_1 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_1'] = $data['EvnNotifyBase_setDT_Range'][1];
				}

				/*if ( isset($data['Lpu_sid']) ) {
					$filter .= ' and ENC.Lpu_oid = :Lpu_oid ';
					$queryParams['Lpu_oid'] = $data['Lpu_sid'];
				}*/

				if ( isset($data['isNotifyProcessed']) )
				{
					if ( $data['isNotifyProcessed'] == 1 ) {
						$filter .= '
					and ENC.EvnNotifyCrazy_niDate is null
					and PR.PersonRegister_id is null
						';
					} elseif ( $data['isNotifyProcessed'] == 2 ) {
						$filter .= '
					and (ENC.EvnNotifyCrazy_niDate is not null or PR.PersonRegister_id is not null)
						';
					}
				}
				break;
			case 'EvnNotifyTub': // Туберкулез
				$query .= '
					inner join v_EvnNotifyTub ENC with (nolock) on '. ( ($data['PersonPeriodicType_id'] == 2) ? 'ENC.PersonEvn_id = PS.PersonEvn_id and ENC.Server_id = PS.Server_id' : 'ENC.Person_id = PS.Person_id' ) .'
					inner join v_Morbus MO with (nolock) on MO.Morbus_id = ENC.Morbus_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_PersonRegister PR with (nolock) on ENC.EvnNotifyTub_id = PR.EvnNotifyBase_id and PR.PersonRegister_disDate is null
					left join v_Diag Diag with (nolock) on Diag.Diag_id = isnull(PR.Diag_id, MO.Diag_id)
				';

				if ( isset($data['Diag_Code_From']) ) {
					$filter .= ' and ( Diag.Diag_Code >= :Diag_Code_From ) ';
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if ( isset($data['Diag_Code_To']) ) {
					$filter .= ' and ( Diag.Diag_Code <= :Diag_Code_To ) ';
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}


				if ( isset($data['EvnNotifyBase_setDT_Range'][0]) ) {
					$filter .= ' and ENC.EvnNotifyTub_setDT >= cast(:EvnNotifyBase_setDT_Range_0 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_0'] = $data['EvnNotifyBase_setDT_Range'][0];
				}

				if ( isset($data['EvnNotifyBase_setDT_Range'][1]) ) {
					$filter .= ' and ENC.EvnNotifyTub_setDT <= cast(:EvnNotifyBase_setDT_Range_1 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_1'] = $data['EvnNotifyBase_setDT_Range'][1];
				}

				/*if ( isset($data['Lpu_sid']) ) {
					$filter .= ' and ENC.Lpu_oid = :Lpu_oid ';
					$queryParams['Lpu_oid'] = $data['Lpu_sid'];
				}*/

				if ( isset($data['isNotifyProcessed']) )
				{
					if ( $data['isNotifyProcessed'] == 1 ) {
						$filter .= '
					and ENC.EvnNotifyTub_niDate is null
					and PR.PersonRegister_id is null
						';
					} elseif ( $data['isNotifyProcessed'] == 2 ) {
						$filter .= '
					and (ENC.EvnNotifyTub_niDate is not null or PR.PersonRegister_id is not null)
						';
					}
				}
				break;
			case 'EvnNotifyHIV': // Извещения ВИЧ
				$query .= '
					inner join v_EvnNotifyBase ENB with (nolock) on ENB.MorbusType_id = 9 and '. ( ($data['PersonPeriodicType_id'] == 2) ? 'ENB.PersonEvn_id = PS.PersonEvn_id and ENB.Server_id = PS.Server_id' : 'ENB.Person_id = PS.Person_id' ) .'
					inner join v_Morbus MO with (nolock) on MO.Morbus_id = ENB.Morbus_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_PersonRegister PR with (nolock) on ENB.EvnNotifyBase_id = PR.EvnNotifyBase_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = isnull(MO.Diag_id,PR.Diag_id)
					left join EvnClass with (nolock) on ENB.EvnClass_id = EvnClass.EvnClass_id
				';

				if ( isset($data['Diag_Code_From']) ) {
					$filter .= ' and ( Diag.Diag_Code >= :Diag_Code_From ) ';
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if ( isset($data['Diag_Code_To']) ) {
					$filter .= ' and ( Diag.Diag_Code <= :Diag_Code_To ) ';
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}


				if ( isset($data['EvnNotifyBase_setDT_Range'][0]) ) {
					$filter .= ' and ENB.EvnNotifyBase_setDT >= cast(:EvnNotifyBase_setDT_Range_0 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_0'] = $data['EvnNotifyBase_setDT_Range'][0];
				}

				if ( isset($data['EvnNotifyBase_setDT_Range'][1]) ) {
					$filter .= ' and ENB.EvnNotifyBase_setDT <= cast(:EvnNotifyBase_setDT_Range_1 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_1'] = $data['EvnNotifyBase_setDT_Range'][1];
				}

				if ( isset($data['HIVNotifyType_id']) )
				{
					$query .= '
					inner join v_HIVNotifyType HIVNotifyType with (nolock) on EvnClass.EvnClass_SysNick = HIVNotifyType.HIVNotifyType_SysNick
					';
					$filter .= ' and HIVNotifyType.HIVNotifyType_id = :HIVNotifyType_id ';
					$queryParams['HIVNotifyType_id'] = $data['HIVNotifyType_id'];
				}

				if ( isset($data['isNotifyProcessed']) )
				{
					if ( $data['isNotifyProcessed'] == 1 ) {
						$filter .= '
					and ENB.EvnNotifyBase_niDate is null
					and PR.PersonRegister_id is null
						';
					} elseif ( $data['isNotifyProcessed'] == 2 ) {
						$filter .= '
					and (ENB.EvnNotifyBase_niDate is not null or PR.PersonRegister_id is not null)
						';
					}
				}
				break;
			case 'EvnNotifyVener': // Венер
				$query .= '
					inner join v_EvnNotifyVener ENC with (nolock) on '. ( ($data['PersonPeriodicType_id'] == 2) ? 'ENC.PersonEvn_id = PS.PersonEvn_id and ENC.Server_id = PS.Server_id' : 'ENC.Person_id = PS.Person_id' ) .'
					inner join v_Morbus MO with (nolock) on MO.Morbus_id = ENC.Morbus_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_PersonRegister PR with (nolock) on ENC.EvnNotifyVener_id = PR.EvnNotifyBase_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = isnull(MO.Diag_id,PR.Diag_id)
				';

				if ( isset($data['Diag_Code_From']) ) {
					$filter .= ' and ( Diag.Diag_Code >= :Diag_Code_From ) ';
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if ( isset($data['Diag_Code_To']) ) {
					$filter .= ' and ( Diag.Diag_Code <= :Diag_Code_To ) ';
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}


				if ( isset($data['EvnNotifyBase_setDT_Range'][0]) ) {
					$filter .= ' and ENC.EvnNotifyVener_setDT >= cast(:EvnNotifyBase_setDT_Range_0 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_0'] = $data['EvnNotifyBase_setDT_Range'][0];
				}

				if ( isset($data['EvnNotifyBase_setDT_Range'][1]) ) {
					$filter .= ' and ENC.EvnNotifyVener_setDT <= cast(:EvnNotifyBase_setDT_Range_1 as datetime) ';
					$queryParams['EvnNotifyBase_setDT_Range_1'] = $data['EvnNotifyBase_setDT_Range'][1];
				}

				/*if ( isset($data['Lpu_sid']) ) {
					$filter .= ' and ENC.Lpu_oid = :Lpu_oid ';
					$queryParams['Lpu_oid'] = $data['Lpu_sid'];
				}*/

				if ( isset($data['isNotifyProcessed']) )
				{
					if ( $data['isNotifyProcessed'] == 1 ) {
						$filter .= '
					and ENC.EvnNotifyVener_niDate is null
					and PR.PersonRegister_id is null
						';
					} elseif ( $data['isNotifyProcessed'] == 2 ) {
						$filter .= '
					and (ENC.EvnNotifyVener_niDate is not null or PR.PersonRegister_id is not null)
						';
					}
				}
				break;
			case 'OrphanRegistry':
				$query .= '
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.MorbusType_id = 6
					inner join v_Morbus M with (nolock) on M.Morbus_id = PR.Morbus_id
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_EvnNotifyOrphan EN with (nolock) on EN.EvnNotifyOrphan_id = PR.EvnNotifyBase_id
					left join v_MorbusOrphan MO with (nolock) on MO.Morbus_id = M.Morbus_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = M.Diag_id
				';

				// регистр
				if ( !empty($data['PersonRegisterType_id']) )
				{
					if ( $data['PersonRegisterType_id'] == 2 )
					{
						// Включенные в регистр
						$filter .= ' and PR.PersonRegister_disDate is null ';
					}
					if ( $data['PersonRegisterType_id'] == 3 )
					{
						// Исключенные из регистра
						$filter .= ' and PR.PersonRegister_disDate is not null ';
					}
				}

				if ( isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1]) ) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				if ( isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1]) ) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				// диагноз
				if ( isset($data['Diag_Code_From']) ) {
					$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if ( isset($data['Diag_Code_To']) ) {
					$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

			break;

			case 'CrazyRegistry':
				$query .= '
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.MorbusType_id = 4
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_EvnNotifyCrazy EN with (nolock) on EN.EvnNotifyCrazy_id = PR.EvnNotifyBase_id
					left join v_MorbusCrazy MO with (nolock) on MO.Morbus_id = isnull(EN.Morbus_id,PR.Morbus_id)
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = PR.Diag_id  
				';
				// ограничение по группе диагнозов по психиатрии
				$filter .= ' and ( Diag_pid not in (705,706,707,708,709,710,711,712,713,714) ) ';
				// регистр
				if ( !empty($data['PersonRegisterType_id']) )
				{
					if ( $data['PersonRegisterType_id'] == 2 )
					{
						// Включенные в регистр
						$filter .= ' and PR.PersonRegister_disDate is null ';
					}
					if ( $data['PersonRegisterType_id'] == 3 )
					{
						// Исключенные из регистра
						$filter .= ' and PR.PersonRegister_disDate is not null ';
					}
				}

				if ( isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1]) ) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				if ( isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1]) ) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				// диагноз
				if ( isset($data['Diag_Code_From']) ) {
					$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if ( isset($data['Diag_Code_To']) ) {
					$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				break;
			case 'NarkoRegistry':
				$query .= '
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.MorbusType_id = 4
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_EvnNotifyCrazy EN with (nolock) on EN.EvnNotifyCrazy_id = PR.EvnNotifyBase_id
					left join v_MorbusCrazy MO with (nolock) on MO.Morbus_id = isnull(EN.Morbus_id,PR.Morbus_id)
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = PR.Diag_id
				';
				// ограничение по группе диагнозов по наркогии
				$filter .= ' and ( Diag_pid in (705,706,707,708,709,710,711,712,713,714) ) ';
				// регистр
				if ( !empty($data['PersonRegisterType_id']) )
				{
					if ( $data['PersonRegisterType_id'] == 2 )
					{
						// Включенные в регистр
						$filter .= ' and PR.PersonRegister_disDate is null ';
					}
					if ( $data['PersonRegisterType_id'] == 3 )
					{
						// Исключенные из регистра
						$filter .= ' and PR.PersonRegister_disDate is not null ';
					}
				}

				if ( isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1]) ) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				if ( isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1]) ) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				// диагноз
				if ( isset($data['Diag_Code_From']) ) {
					$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if ( isset($data['Diag_Code_To']) ) {
					$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				break;
			case 'TubRegistry':
				$query .= '
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.MorbusType_id = 7
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_EvnNotifyTub EN with (nolock) on EN.EvnNotifyTub_id = PR.EvnNotifyBase_id
					left join v_MorbusTub MO with (nolock) on MO.Morbus_id = isnull(EN.Morbus_id,PR.Morbus_id)
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = PR.Diag_id  
				';

				// регистр
				if ( !empty($data['PersonRegisterType_id']) )
				{
					if ( $data['PersonRegisterType_id'] == 2 )
					{
						// Включенные в регистр
						$filter .= ' and PR.PersonRegister_disDate is null ';
					}
					if ( $data['PersonRegisterType_id'] == 3 )
					{
						// Исключенные из регистра
						$filter .= ' and PR.PersonRegister_disDate is not null ';
					}
				}

				if ( isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1]) ) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				if ( isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1]) ) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				// диагноз
				if ( isset($data['Diag_Code_From']) ) {
					$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if ( isset($data['Diag_Code_To']) ) {
					$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				break;
			case 'VznRegistry':
			case 'DiabetesRegistry':
			case 'LargeFamilyRegistry':
				$registers = array (
					'VznRegistry' => 10,
					'DiabetesRegistry' => 11,
					'LargeFamilyRegistry' => 12
				);
				$query .= '
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.MorbusType_id = '. $registers[$data['SearchFormType']] .'
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = PR.Diag_id
					outer apply (
						select count(*) Request
						from DrugRequestRow (nolock) 
						where Person_id = PC.Person_id
					) as Drug
				';

				// регистр
				if ( !empty($data['PersonRegisterType_id']) )
				{
					if ( $data['PersonRegisterType_id'] == 2 )
					{
						// Включенные в регистр
						$filter .= ' and PR.PersonRegister_disDate is null ';
					}
					if ( $data['PersonRegisterType_id'] == 3 )
					{
						// Исключенные из регистра
						$filter .= ' and PR.PersonRegister_disDate is not null ';
					}
				}

				if ( isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1]) ) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				if ( isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1]) ) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				// диагноз
				if ( isset($data['Diag_Code_From']) ) {
					$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if ( isset($data['Diag_Code_To']) ) {
					$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				break;
			case 'HIVRegistry':
				$query .= '
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.MorbusType_id = 9
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_EvnNotifyHIV EN with (nolock) on EN.EvnNotifyHIV_id = PR.EvnNotifyBase_id
					inner join v_Morbus M with (nolock) on M.Morbus_id = isnull(EN.Morbus_id,PR.Morbus_id)
					inner join v_MorbusHIV MH with (nolock) on MH.Morbus_id = M.Morbus_id
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = PR.Diag_id  
				';

				// регистр
				if ( !empty($data['PersonRegisterType_id']) )
				{
					if ( $data['PersonRegisterType_id'] == 2 )
					{
						// Включенные в регистр
						$filter .= ' and PR.PersonRegister_disDate is null ';
					}
					if ( $data['PersonRegisterType_id'] == 3 )
					{
						// Исключенные из регистра
						$filter .= ' and PR.PersonRegister_disDate is not null ';
					}
				}

				if ( isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1]) ) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				if ( isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1]) ) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				// диагноз
				if ( isset($data['Diag_Code_From']) ) {
					$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if ( isset($data['Diag_Code_To']) ) {
					$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				// № иммуноблота
				if ( isset($data['MorbusHIV_NumImmun']) ) {
					$filter .= " and MH.MorbusHIV_NumImmun = :MorbusHIV_NumImmun";
					$queryParams['MorbusHIV_NumImmun'] = $data['MorbusHIV_NumImmun'];
				}

				break;
			case 'VenerRegistry':
				$query .= '
					inner join v_PersonRegister PR with (nolock) on PR.Person_id = PS.Person_id and PR.MorbusType_id = 8
					left join v_PersonRegisterOutCause PROUT with (nolock) on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_EvnNotifyVener EN with (nolock) on EN.EvnNotifyVener_id = PR.EvnNotifyBase_id
					left join v_MorbusVener MO with (nolock) on MO.Morbus_id = isnull(EN.Morbus_id,PR.Morbus_id)
					left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
					left join v_Diag Diag with (nolock) on Diag.Diag_id = PR.Diag_id  
				';

				// регистр
				if ( !empty($data['PersonRegisterType_id']) )
				{
					if ( $data['PersonRegisterType_id'] == 2 )
					{
						// Включенные в регистр
						$filter .= ' and PR.PersonRegister_disDate is null ';
					}
					if ( $data['PersonRegisterType_id'] == 3 )
					{
						// Исключенные из регистра
						$filter .= ' and PR.PersonRegister_disDate is not null ';
					}
				}

				if ( isset($data['PersonRegister_setDate_Range'][0]) && isset($data['PersonRegister_setDate_Range'][1]) ) {
					$queryParams['PersonRegister_setDate_Range_0'] = $data['PersonRegister_setDate_Range'][0];
					$queryParams['PersonRegister_setDate_Range_1'] = $data['PersonRegister_setDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_setDate as date) between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ';
				}

				if ( isset($data['PersonRegister_disDate_Range'][0]) && isset($data['PersonRegister_disDate_Range'][1]) ) {
					$queryParams['PersonRegister_disDate_Range_0'] = $data['PersonRegister_disDate_Range'][0];
					$queryParams['PersonRegister_disDate_Range_1'] = $data['PersonRegister_disDate_Range'][1];
					$filter .= ' and cast(PR.PersonRegister_disDate as date) between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ';
				}

				// диагноз
				if ( isset($data['Diag_Code_From']) ) {
					$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				}

				if ( isset($data['Diag_Code_To']) ) {
					$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}

				break;
		}
		
		if ( $data['PersonPeriodicType_id'] == 3 ) {
			$filter .= " and exists(
				select top 1 1
				from v_Person_all PStmp (nolock)
				where PStmp.Person_id = PS.Person_id
			";

			$this->getPersonPeriodicFilters($data, $filter, $queryParams, $main_alias, 'PStmp');
			
			$filter .= ") ";
		}
		else {
			$this->getPersonPeriodicFilters($data, $filter, $queryParams, $main_alias);
		}

		// Подключаем PersonPrivilege, если поиск вызван с формы поиска льготников
		if ( ($data['SearchFormType'] == 'PersonPrivilege')) {
			$query .= " inner join v_PersonPrivilege PP with (nolock) on PP.Person_id = PS.Person_id";
			$query .= " inner join v_PrivilegeType PT with (nolock) on PT.PrivilegeType_id = PP.PrivilegeType_id";
			$query .= " left join v_PrivilegeAccessRights PAR with (nolock) on PAR.PrivilegeType_id = PP.PrivilegeType_id";
			//$query .= " inner join v_Lpu LPU with (nolock) on LPU.Lpu_id = PP.Lpu_id";

			$this->getPrivilegeFilters($data, $filter, $queryParams);
			
		}
		// Подключаем PersonPrivilege, если заданы фильтры на вкладке "Льгота"
		else if ( ( isset($data['RegisterSelector_id']) ) ||
			( isset($data['PrivilegeType_id']) ) || ( isset($data['Privilege_begDate']) ) || ( isset($data['Privilege_begDate_Range'][0]) ) ||
			( isset($data['Privilege_begDate_Range'][1]) ) || ( isset($data['Privilege_endDate']) ) ||
			( isset($data['Privilege_endDate_Range'][0]) ) || ( isset($data['Privilege_endDate_Range'][1]) || 
			( isset($data['Refuse_id']) ) || ( isset($data['RefuseNextYear_id'])) )
		) {
			$filter .= " and exists (select personprivilege_id from v_PersonPrivilege PP with (nolock) ";
			$filter .= " inner join v_PrivilegeType PT with (nolock) on PT.PrivilegeType_id = PP.PrivilegeType_id";
			$filter .= " left join v_PrivilegeAccessRights PAR with (nolock) on PAR.PrivilegeType_id = PP.PrivilegeType_id";
			$filter .= " WHERE PP.Person_id = PS.Person_id";
			$this->getPrivilegeFilters($data, $filter, $queryParams);
			
			$filter .= ") ";
			
		}

		if ( ( isset($data['Refuse_id']) ) || ( isset($data['RefuseNextYear_id']) ) ) {
			// Отказ
			if ( isset($data['Refuse_id']) ) {
				$filter .= " and (exists (
					select 1
					from v_PersonPrivilege PP with (nolock)
						inner join v_PrivilegeType PT with (nolock) on PT.PrivilegeType_id = PP.PrivilegeType_id
							/*and PT.PrivilegeType_Code <= 249*/ and PT.ReceptFinance_id = 1
					where PP.Person_id = PS.Person_id
						and ISNULL(PS.Person_IsRefuse2, 1) = :Refuse_id
						and PP.PersonPrivilege_begDate <= dbo.tzGetDate()
						and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime))
				";
				$queryParams['Refuse_id'] = $data['Refuse_id'];
				$filter .= ") ";
				/*
				if ($data['Refuse_id'] == 1 ) {
					$filter .= " or
						not exists ( select PersonRefuse_id from v_PersonRefuse PR where PR.Person_id = PS.Person_id and PR.PersonRefuse_Year = YEAR(dbo.tzGetDate()) )
					";
				}
				*/
				$filter .= ")";
			}

			// Отказ
			if ( isset($data['RefuseNextYear_id']) ) {
				$filter .= " and (exists (
					select 1
					from v_PersonPrivilege PPN with (nolock)
						inner join v_PrivilegeType PTN (nolock) on PTN.PrivilegeType_id = PPN.PrivilegeType_id
							and PTN.ReceptFinance_id = 1
						outer apply (
							select top 1 ISNULL(PRN.PersonRefuse_IsRefuse, 1) as PersonRefuse_IsRefuse
							from v_PersonRefuse PRN with (nolock)
							where PRN.Person_id = PPN.Person_id
								and ISNULL(PRN.PersonRefuse_Year, YEAR(dbo.tzGetDate())) = YEAR(dbo.tzGetDate()) + 1
						) PRefN
					where PPN.Person_id = PS.Person_id
						and ISNULL(PRefN.PersonRefuse_IsRefuse, 1) = :RefuseNextYear_id
						and PPN.PersonPrivilege_begDate <= dbo.tzGetDate()
						and (PPN.PersonPrivilege_endDate is null or PPN.PersonPrivilege_endDate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime))
				";
				$queryParams['RefuseNextYear_id'] = $data['RefuseNextYear_id'];
				$filter .= ") ";
				/*
				$filter .= " and (exists (
					select top 1 PRN.PersonRefuse_id
					from PersonPrivilege PPN
						inner join PrivilegeType PTN on PTN.PrivilegeType_id = PPN.PrivilegeType_id
							and PTN.PrivilegeType_Code <= 249
						left join PersonEvn PEN on PEN.Person_id = PPN.Person_id
						left join PersonEvnClass PECN on PECN.PersonEvnClass_id = PEN.PersonEvnClass_id
						left join PersonRefuse PRN on PRN.Server_id = PEN.Server_id
							and PRN.PersonRefuse_id = PEN.PersonEvn_id
					where PPN.Person_id = PS.Person_id
						and ISNULL(PRN.PersonRefuse_IsRefuse, 1) = :RefuseNextYear_id
						and PPN.PersonPrivilege_begDate <= dbo.tzGetDate()
						and (PPN.PersonPrivilege_endDate is null or PPN.PersonPrivilege_endDate > dbo.tzGetDate())
						and ISNULL(PRN.PersonRefuse_Year, YEAR(dbo.tzGetDate()) + 1) = YEAR(dbo.tzGetDate()) + 1
				";
				$queryParams['RefuseNextYear_id'] = $data['RefuseNextYear_id'];
				$filter .= ") ";

				if ($data['RefuseNextYear_id'] == 1 ) {
					$filter .= " or
						not exists (select PersonRefuse_id from v_PersonRefuse PR where PR.Person_id = PS.Person_id and PR.PersonRefuse_Year = YEAR(dbo.tzGetDate()) + 1 )";
				}
				*/
				$filter .= ")";
			}
		}
		/*
		if ( $data['SearchFormType'] == 'PersonCard' ) {
			
			if ( $data['PersonCard_IsDms'] > 0 ) {
				$exists = "";
				if ( $data['PersonCard_IsDms'] == 1 )
					$exists = " not ";
				$filter .= " and " . $exists . " exists(
					select
						PersonCard_id
					from
						v_PersonCard  (nolock)
					where
						Person_id = PC.Person_id
						and LpuAttachType_id = 5
						and PersonCard_endDate >= dbo.tzGetDate()
						and CardCloseCause_id is null
				) ";
				
				$exists = " = ";
				if ( $data['PersonCard_IsDms'] == 1 )
					$exists = " != ";
				$filter .= " and pc.LpuAttachType_id " . $exists . " 5 ";
			}
			
			if ( $data['PersonCardStateType_id'] == 1 ) {
				$query .= " inner join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id";
				$filter .= " and (PC.PersonCard_endDate is null or cast(PC.PersonCard_endDate as datetime) > dbo.tzGetDate())";
			}
			else {
				$query .= " inner join v_PersonCard_all PC with (nolock) on PC.Person_id = PS.Person_id";
			}

			$query .= " left join LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id";
			$query .= " left join v_Lpu AttachLpu with (nolock) on AttachLpu.Lpu_id = PC.Lpu_id";
			$query .= " 
				left join Address uaddr with (nolock) on uaddr.Address_id = ps.UAddress_id
				left join Address paddr with (nolock) on paddr.Address_id = ps.PAddress_id
			";

			$this->getPersonCardFilters($data, $filter, $queryParams);
			
		}*/
		if ( $data['SearchFormType'] == 'PersonCard' ) {
			
			if ( $data['PersonCard_IsDms'] > 0 ) {
				$exists = "";
				if ( $data['PersonCard_IsDms'] == 1 )
					$exists = " not ";
				$filter .= " and " . $exists . " exists(
					select
						PersonCard_id
					from
						v_PersonCard (nolock)
					where
						Person_id = PC.Person_id
						and LpuAttachType_id = 5
						and PersonCard_endDate >= dbo.tzGetDate()
						and CardCloseCause_id is null
				) ";
				
				$exists = " = ";
				if ( $data['PersonCard_IsDms'] == 1 )
					$exists = " != ";
				$filter .= " and pc.LpuAttachType_id " . $exists . " 5 ";
			}
			
			if ( $data['PersonCardStateType_id'] == 1 ) {
                if ($data['session']['region']['nick'] == 'khak') {
                    $query .= " left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id";
                } else {
                    $query .= " inner join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id";
                }
                //$query .= " inner join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id";
				$filter .= " and (PC.PersonCard_endDate is null or cast(PC.PersonCard_endDate as datetime) > dbo.tzGetDate())";
			}
			else {
				$query .= " inner join v_PersonCard_all PC with (nolock) on PC.Person_id = PS.Person_id";
			}

			$query .= " left join LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id";
			$query .= " left join v_Lpu AttachLpu with (nolock) on AttachLpu.Lpu_id = PC.Lpu_id";
			$query .= " 
				left join Address uaddr with (nolock) on uaddr.Address_id = ps.UAddress_id
				left join Address paddr with (nolock) on paddr.Address_id = ps.PAddress_id
			";
			$query .= " left join PersonRefuse PRef with (nolock) on (PRef.Person_id = ps.Person_id and PRef.PersonRefuse_Year=YEAR([dbo].tzGetDate())+1)";
			//$query .= " left join PersonDisp PD on PD.Person_id = ps.Person_id";
			$query .= " outer apply (
					select max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
					from PersonDisp with (nolock)
					where Person_id = ps.Person_id
					and (PersonDisp_endDate is null or PersonDisp_endDate > dbo.tzGetDate())
					and Sickness_id IN (1,3,4,5,6,7,8)
				) as disp";
			$this->getPersonCardFilters($data, $filter, $queryParams);
			
		}
		else if ( $data['SearchFormType'] == 'WorkPlacePolkaReg' ) {
			if ( $data['PersonCardStateType_id'] == 1 ) {
				$query .= "
				outer apply (
					select top 1
					coalesce(PC_ALT.PersonCard_id,PC_SERV.PersonCard_id,PC_MAIN.PersonCard_id) as PersonCard_id
					from Person with (nolock)
					left join v_PersonCard PC_MAIN with (nolock) on PC_MAIN.Person_id = PS.Person_id and PC_MAIN.LpuAttachType_id = 1
					-- Если у пациента нет основного прикрепления, то показывать гинекологическое, стоматологическое прикрепление
					left join v_PersonCard PC_ALT with (nolock) on PC_ALT.Lpu_id = :Lpu_id and PC_ALT.Person_id = PS.Person_id and PC_ALT.LpuAttachType_id in (2,3)
					-- Служебное прикрепление к МО показывать только в той же МО и только если нет активного прикрепления другого типа к этой МО.
					left join v_PersonCard PC_SERV with (nolock) on PC_SERV.Person_id = PS.Person_id and PC_SERV.LpuAttachType_id = 4 and PC_SERV.Lpu_id = :Lpu_id and (coalesce(PC_MAIN.Lpu_id,PC_ALT.Lpu_id,0) != PC_SERV.Lpu_id)
					where Person.Person_id = PS.Person_id
				) as PersonCard
				left join v_PersonCard PC with (nolock) on PC.PersonCard_id = PersonCard.PersonCard_id";
			}
			else {
				$query .= "
				outer apply (
					select top 1
					coalesce(PC_SERV.PersonCard_id,PC_MAIN.PersonCard_id,PC_ALT.PersonCard_id) as PersonCard_id
					from Person with (nolock)
					left join v_PersonCard_all PC_MAIN with (nolock) on PC_MAIN.Person_id = PS.Person_id and PC_MAIN.LpuAttachType_id = 1
					-- Если у пациента нет основного прикрепления, то показывать гинекологическое, стоматологическое прикрепление
					left join v_PersonCard_all PC_ALT with (nolock) on PC_MAIN.PersonCard_id is null and PC_ALT.Person_id = PS.Person_id and PC_ALT.LpuAttachType_id in (2,3)
					-- Служебное прикрепление к МО показывать только в той же МО и только если нет активного прикрепления другого типа к этой МО.
					left join v_PersonCard_all PC_SERV with (nolock) on PC_SERV.Person_id = PS.Person_id and PC_SERV.LpuAttachType_id = 4 and PC_SERV.Lpu_id = :Lpu_id and (coalesce(PC_MAIN.Lpu_id,PC_ALT.Lpu_id,0) != PC_SERV.Lpu_id)
					where Person.Person_id = PS.Person_id
				) as PersonCard
				left join v_PersonCard_all PC with (nolock) on PC.PersonCard_id = PersonCard.PersonCard_id";
			}

			$query .= " left join LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id";
			$query .= " left join v_Lpu AttachLpu with (nolock) on AttachLpu.Lpu_id = PC.Lpu_id";
			$query .= " 
				left join Address uaddr with (nolock) on uaddr.Address_id = ps.UAddress_id
				left join Address paddr with (nolock) on paddr.Address_id = ps.PAddress_id
			";
			$query .= " left join PersonRefuse PRef with (nolock) on (PRef.Person_id = ps.Person_id and PRef.PersonRefuse_Year=YEAR([dbo].tzGetDate())+1)";
			//$query .= " left join PersonDisp PD on PD.Person_id = ps.Person_id";
			$query .= " outer apply (
					select max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
					from PersonDisp with (nolock)
					where Person_id = ps.Person_id
					and (PersonDisp_endDate is null or PersonDisp_endDate > dbo.tzGetDate())
					and Sickness_id IN (1,3,4,5,6,7,8)
				) as disp";
			$this->getPersonCardFilters($data, $filter, $queryParams);
			
		}
		else
		{
            //if ( ($data['AttachLpu_id'] > 0) || (strlen($data['PersonCard_Code']) > 0) ||
            //    ( isset($data['PersonCard_begDate']) ) || ( isset($data['PersonCard_begDate_Range'][0]) ) || ( isset($data['PersonCard_begDate_Range'][1]) ) || 
            //    ( isset($data['PersonCard_endDate']) ) || ( isset($data['PersonCard_endDate_Range'][0]) ) || ( isset($data['PersonCard_endDate_Range'][1]) ) || ($data['LpuAttachType_id'] > 0) || 
            //    (isset($data['LpuRegion_id'])) || ($data['LpuRegionType_id'] > 0) || ($data['MedPersonal_id'] > 0) ||
            //    ($data['PersonCard_IsDms'] > 0) || $data['PersonCard_IsDms'] > 0
            //) {
            //    if ( $data['PersonCardStateType_id'] == 1 ) {
            //        $filter .= " and exists (select top 1 personcard_id from v_PersonCard PC with (nolock) ";
            //        $filter .= " left join LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id";
            //        $filter .= " WHERE PC.Person_id = PS.Person_id";
            //        $filter .= " and (PC.PersonCard_endDate is null or cast(PC.PersonCard_endDate as datetime) > dbo.tzGetDate())";
            //    }
            //    else {
            //        $filter .= " and exists (select top 1 personcard_id from v_PersonCard_all PC with (nolock) ";
            //        $filter .= " left join LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id";
            //        $filter .= " WHERE PC.Person_id = PS.Person_id";
            //    }

			//	$this->getPersonCardFilters($data, $filter, $queryParams);
				
            //    $filter .= ") ";
            //}
            
            $this->getPersonCardFilters($data, $filter, $queryParams);
		}
		// Пользователь
		if ( $data['pmUser_insID'] > 0 ) {
			if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
				$filter .= " and " . $main_alias . ".pmUserBeg_insID = :pmUser_insID";
			} else {
				$filter .= " and " . $main_alias . ".pmUser_insID = :pmUser_insID";
			}
			$queryParams['pmUser_insID'] = $data['pmUser_insID'];
		}

		// Пользователь
		if ( $data['pmUser_updID'] > 0 ) {
			if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
				$filter .= " and " . $main_alias . ".pmUserEnd_insID = :pmUser_updID";
			} else {
				$filter .= " and " . $main_alias . ".pmUser_updID = :pmUser_updID";
			}
			$queryParams['pmUser_updID'] = $data['pmUser_updID'];
		}

		if (substr($data['SearchFormType'],0,3) == 'Kvs') { //для выгрузки квс в дбф
			$fld_name = substr($data['SearchFormType'],3);
			switch($data['SearchFormType']) {
				case 'KvsPerson':
					$fld_name = 'EvnPS';
				break;
				case 'KvsEvnDiag': $fld_name = 'EvnDiagPS'; break;
				case 'KvsNarrowBed': $fld_name = 'EvnSectionNarrowBed'; break;
			}
			// для Person фильтр по дате делаем по КВС
			$fld_name = ($data['SearchFormType']!='KvsPerson')?$main_alias.".".$fld_name:"EPS.".$fld_name;
			$is_pc = ($data['SearchFormType'] == 'KvsPersonCard' && $data['PersonCardStateType_id'] != 1);			
			
			// Пользователь
			if ( isset($data['InsDate']) ) {
				if ($data['SearchFormType'] == 'KvsEvnLeave')
					$filter .= " and (
						(EPS.LeaveType_id = 1 and cast(convert(varchar(10), ELV.EvnLeave_insDT, 112) as datetime) = cast(:InsDate as datetime))
						or (EPS.LeaveType_id = 2 and cast(convert(varchar(10), EOLpu.EvnOtherLpu_insDT, 112) as datetime) = cast(:InsDate as datetime))
						or (EPS.LeaveType_id = 3 and cast(convert(varchar(10), EDie.EvnDie_insDT, 112) as datetime) = cast(:InsDate as datetime))
						or (EPS.LeaveType_id = 4 and cast(convert(varchar(10), EOStac.EvnOtherStac_insDT, 112) as datetime) = cast(:InsDate as datetime))
						or (EPS.LeaveType_id = 5 and cast(convert(varchar(10), EOSect.EvnOtherSection_insDT, 112) as datetime) = cast(:InsDate as datetime))
					)";
				else
					$filter .= " and cast(convert(varchar(10), ".$fld_name.($is_pc ? "Beg_insDT" : "_insDT").", 112) as datetime) = cast(:InsDate as datetime)";
				$queryParams['InsDate'] = $data['InsDate'];
			}
			// Пользователь
			if ( isset($data['InsDate_Range'][0]) ) {
				if ($data['SearchFormType'] == 'KvsEvnLeave')
					$filter .= " and (
						(EPS.LeaveType_id = 1 and cast(convert(varchar(10), ELV.EvnLeave_insDT, 112) as datetime) >= cast(:InsDate_Range_0 as datetime))
						or (EPS.LeaveType_id = 2 and cast(convert(varchar(10), EOLpu.EvnOtherLpu_insDT, 112) as datetime) >= cast(:InsDate_Range_0 as datetime))
						or (EPS.LeaveType_id = 3 and cast(convert(varchar(10), EDie.EvnDie_insDT, 112) as datetime) >= cast(:InsDate_Range_0 as datetime))
						or (EPS.LeaveType_id = 4 and cast(convert(varchar(10), EOStac.EvnOtherStac_insDT, 112) as datetime) >= cast(:InsDate_Range_0 as datetime))
						or (EPS.LeaveType_id = 5 and cast(convert(varchar(10), EOSect.EvnOtherSection_insDT, 112) as datetime) >= cast(:InsDate_Range_0 as datetime))
					)";
				else
					$filter .= " and cast(convert(varchar(10), ".$fld_name.($is_pc ? "Beg_insDT" : "_insDT").", 112) as datetime) >= cast(:InsDate_Range_0 as datetime)";
				$queryParams['InsDate_Range_0'] = $data['InsDate_Range'][0];
			}
			// Пользователь
			if ( isset($data['InsDate_Range'][1]) ) {
				if ($data['SearchFormType'] == 'KvsEvnLeave')
					$filter .= " and (
						(EPS.LeaveType_id = 1 and cast(convert(varchar(10), ELV.EvnLeave_insDT, 112) as datetime) <= cast(:InsDate_Range_1 as datetime))
						or (EPS.LeaveType_id = 2 and cast(convert(varchar(10), EOLpu.EvnOtherLpu_insDT, 112) as datetime) <= cast(:InsDate_Range_1 as datetime))
						or (EPS.LeaveType_id = 3 and cast(convert(varchar(10), EDie.EvnDie_insDT, 112) as datetime) <= cast(:InsDate_Range_1 as datetime))
						or (EPS.LeaveType_id = 4 and cast(convert(varchar(10), EOStac.EvnOtherStac_insDT, 112) as datetime) <= cast(:InsDate_Range_1 as datetime))
						or (EPS.LeaveType_id = 5 and cast(convert(varchar(10), EOSect.EvnOtherSection_insDT, 112) as datetime) <= cast(:InsDate_Range_1 as datetime))
					)";
				else
					$filter .= " and cast(convert(varchar(10), ".$fld_name.($is_pc ? "Beg_insDT" : "_insDT").", 112) as datetime) <= cast(:InsDate_Range_1 as datetime)";
				$queryParams['InsDate_Range_1'] = $data['InsDate_Range'][1];
			}

			// Пользователь
			if ( isset($data['UpdDate']) ) {
				if ($data['SearchFormType'] == 'KvsEvnLeave')
					$filter .= " and (
						(EPS.LeaveType_id = 1 and cast(convert(varchar(10), ELV.EvnLeave_updDT, 112) as datetime) = cast(:UpdDate as datetime))
						or (EPS.LeaveType_id = 2 and cast(convert(varchar(10), EOLpu.EvnOtherLpu_updDT, 112) as datetime) = cast(:UpdDate as datetime))
						or (EPS.LeaveType_id = 3 and cast(convert(varchar(10), EDie.EvnDie_updDT, 112) as datetime) = cast(:UpdDate as datetime))
						or (EPS.LeaveType_id = 4 and cast(convert(varchar(10), EOStac.EvnOtherStac_updDT, 112) as datetime) = cast(:UpdDate as datetime))
						or (EPS.LeaveType_id = 5 and cast(convert(varchar(10), EOSect.EvnOtherSection_updDT, 112) as datetime) = cast(:UpdDate as datetime))
					)";
				else
					$filter .= " and cast(convert(varchar(10), ".$fld_name.($is_pc ? "Beg_insDT" : "_updDT").", 112) as datetime) = cast(:UpdDate as datetime)";
				$queryParams['UpdDate'] = $data['UpdDate'];
			}
			// Пользователь
			if ( isset($data['UpdDate_Range'][0]) ) {
				if ($data['SearchFormType'] == 'KvsEvnLeave')
					$filter .= " and (
						(EPS.LeaveType_id = 1 and cast(convert(varchar(10), ELV.EvnLeave_updDT, 112) as datetime) >= cast(:UpdDate_Range_0 as datetime))
						or (EPS.LeaveType_id = 2 and cast(convert(varchar(10), EOLpu.EvnOtherLpu_updDT, 112) as datetime) >= cast(:UpdDate_Range_0 as datetime))
						or (EPS.LeaveType_id = 3 and cast(convert(varchar(10), EDie.EvnDie_updDT, 112) as datetime) >= cast(:UpdDate_Range_0 as datetime))
						or (EPS.LeaveType_id = 4 and cast(convert(varchar(10), EOStac.EvnOtherStac_updDT, 112) as datetime) >= cast(:UpdDate_Range_0 as datetime))
						or (EPS.LeaveType_id = 5 and cast(convert(varchar(10), EOSect.EvnOtherSection_updDT, 112) as datetime) >= cast(:UpdDate_Range_0 as datetime))
					)";
				else
					$filter .= " and cast(convert(varchar(10), ".$fld_name.($is_pc ? "Beg_insDT" : "_updDT").", 112) as datetime) >= cast(:UpdDate_Range_0 as datetime)";
				$queryParams['UpdDate_Range_0'] = $data['UpdDate_Range'][0];
			}
			// Пользователь
			if ( isset($data['UpdDate_Range'][1]) ) {
				if ($data['SearchFormType'] == 'KvsEvnLeave')
					$filter .= " and (
						(EPS.LeaveType_id = 1 and cast(convert(varchar(10), ELV.EvnLeave_updDT, 112) as datetime) <= cast(:UpdDate_Range_1 as datetime))
						or (EPS.LeaveType_id = 2 and cast(convert(varchar(10), EOLpu.EvnOtherLpu_updDT, 112) as datetime) <= cast(:UpdDate_Range_1 as datetime))
						or (EPS.LeaveType_id = 3 and cast(convert(varchar(10), EDie.EvnDie_updDT, 112) as datetime) <= cast(:UpdDate_Range_1 as datetime))
						or (EPS.LeaveType_id = 4 and cast(convert(varchar(10), EOStac.EvnOtherStac_updDT, 112) as datetime) <= cast(:UpdDate_Range_1 as datetime))
						or (EPS.LeaveType_id = 5 and cast(convert(varchar(10), EOSect.EvnOtherSection_updDT, 112) as datetime) <= cast(:UpdDate_Range_1 as datetime))
					)";
				else
					$filter .= " and cast(convert(varchar(10), ".$fld_name.($is_pc ? "Beg_insDT" : "_updDT").", 112) as datetime) <= cast(:UpdDate_Range_1 as datetime)";
				$queryParams['UpdDate_Range_1'] = $data['UpdDate_Range'][1];
			}
		} else {
			$fld_name = $data['SearchFormType'];

			switch($data['SearchFormType']) {
				case 'EvnPLDispTeenInspectionPeriod':
				case 'EvnPLDispTeenInspectionPred':
				case 'EvnPLDispTeenInspectionProf':
					$fld_name = 'EvnPLDispTeenInspection';
				break;

				case 'PersonDispOrpPeriod':
				case 'PersonDispOrpPred':
				case 'PersonDispOrpProf':
					$fld_name = 'PersonDispOrp';
				break;
			}

			// Пользователь
			if ( isset($data['InsDate']) ) {
				if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
					$filter .= " and cast(convert(varchar(10), " . $main_alias . "." . $data['SearchFormType'] . "Beg_insDT, 112) as datetime) = cast(:InsDate as datetime)";
				} else {
					$filter .= " and cast(convert(varchar(10), " . $main_alias . "." . $fld_name . "_insDT, 112) as datetime) = cast(:InsDate as datetime)";
				}			
				$queryParams['InsDate'] = $data['InsDate'];
			}

			// Пользователь
			if ( isset($data['InsDate_Range'][0]) ) {
				if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
					$filter .= " and cast(convert(varchar(10), " . $main_alias . "." . $data['SearchFormType'] . "Beg_insDT, 112) as datetime) >= cast(:InsDate_Range_0 as datetime)";
				} else {
					$filter .= " and cast(convert(varchar(10), " . $main_alias . "." . $fld_name . "_insDT, 112) as datetime) >= cast(:InsDate_Range_0 as datetime)"; 
				}
				$queryParams['InsDate_Range_0'] = $data['InsDate_Range'][0];
			}

			// Пользователь
			if ( isset($data['InsDate_Range'][1]) ) {
				if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
					$filter .= " and cast(convert(varchar(10), " . $main_alias . "." . $data['SearchFormType'] . "Beg_insDT, 112) as datetime) <= cast(:InsDate_Range_1 as datetime)";
				} else {
					$filter .= " and cast(convert(varchar(10), " . $main_alias . "." . $fld_name . "_insDT, 112) as datetime) <= cast(:InsDate_Range_1 as datetime)";
				}
				$queryParams['InsDate_Range_1'] = $data['InsDate_Range'][1];
			}

			// Пользователь
			if ( isset($data['UpdDate']) ) {
				if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
					$filter .= " and cast(convert(varchar(10), " . $main_alias . "." . $data['SearchFormType'] . "Beg_insDT, 112) as datetime) = cast(:UpdDate  as datetime)";
				} else {
					$filter .= " and cast(convert(varchar(10), " . $main_alias . "." . $fld_name . "_updDT, 112) as datetime) = cast(:UpdDate as datetime)";
				}
				$queryParams['UpdDate'] = $data['UpdDate'];
			}

			// Пользователь
			if ( isset($data['UpdDate_Range'][0]) ) {
				if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
					$filter .= " and cast(convert(varchar(10), " . $main_alias . "." . $data['SearchFormType'] . "Beg_insDT, 112) as datetime) >= cast(:UpdDate_Range_0 as datetime)";
				} else {
					$filter .= " and cast(convert(varchar(10), " . $main_alias . "." . $fld_name . "_updDT, 112) as datetime) >= cast(:UpdDate_Range_0 as datetime)";
				}
				$queryParams['UpdDate_Range_0'] = $data['UpdDate_Range'][0];
			}

			// Пользователь
			if ( isset($data['UpdDate_Range'][1]) ) {
				if ($data['SearchFormType'] == 'PersonCard' && $data['PersonCardStateType_id'] != 1) {
					$filter .= " and cast(convert(varchar(10), " . $main_alias . "." . $data['SearchFormType'] . "Beg_insDT, 112) as datetime) <= cast(:UpdDate_Range_1 as datetime)";
				} else {
					$filter .= " and cast(convert(varchar(10), " . $main_alias . "." . $fld_name . "_updDT, 112) as datetime) <= cast(:UpdDate_Range_1 as datetime)";
				}
				$queryParams['UpdDate_Range_1'] = $data['UpdDate_Range'][1];
			}
		}
		/*
		if ( count($queryParams) <= 2 ) {
			return array('success' => false, 'Error_Msg' => 'Необходимо задать хотя бы 1 параметр');
		}
		*/
		$query .= "
				-- end from
			where
				-- where
				" . $filter . "
				-- end where
		";

		switch ( $data['SearchFormType'] ) {
			case 'CmpCallCard':
				$query .= "
					order by
						-- order by
						PS.Person_Surname,
						CCC.Person_SurName,
						PS.Person_Firname,
						CCC.Person_FirName,
						PS.Person_Secname,
						CCC.Person_SecName
						-- end order by
				";
			break;

			case 'EvnPL':
				$query .= "
					order by
						-- order by
						EPL.EvnPL_id
						-- end order by
				";
			break;

			case 'EvnVizitPL':
				$query .= "
					order by
						-- order by
						EVizitPL.EvnVizitPL_setDate DESC
						-- end order by
				";
			break;

			case 'EvnPLStom':
				$query .= "
					order by
						-- order by
						EPLS.EvnPLStom_id
						-- end order by
				";
			break;

			case 'EvnVizitPLStom':
				$query .= "
					order by
						-- order by
						EVPLS.EvnVizitPLStom_setDate DESC
						-- end order by
				";
			break;

			case 'EvnPS':
				if ( $print === true ) {
					$query .= "
						order by
							-- order by
							PS.Person_SurName,
							PS.Person_FirName,
							PS.Person_SecName
							-- end order by
					";
				}
				else {
					$query .= "
						order by
							-- order by
							EPS.EvnPS_id
							-- end order by
					";
				}
			break;

			case 'EvnSection':
				if ( $print === true ) {
					$query .= "
						order by
							-- order by
							PS.Person_SurName,
							PS.Person_FirName,
							PS.Person_SecName
							-- end order by
					";
				}
				else {
					$query .= "
						order by
							-- order by
							EPS.EvnPS_id
							-- end order by
					";
				}
			break;

			case 'EvnDtpWound':
				$query .= "
					order by
						-- order by
						EDW.EvnDtpWound_id
						-- end order by
				";
			break;

			case 'EvnUslugaPar':
				$query .= "
					order by
						-- order by
						EUP.EvnUslugaPar_id
						-- end order by
				";
			break;

			case 'EvnRecept':
				$query .= "
					order by
						-- order by
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName,
						ER.EvnRecept_Num
						-- end order by
				";
			break;

			case 'PersonDopDisp':
				$query .= "
					group by
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName,
						DD.PersonDopDisp_id,
						PS.Person_id,
						PS.Server_id,
						epldd.Server_id,
						PS.PersonEvn_id,
						epldd.PersonEvn_id,
						Person_Birthday,
						Sex.Sex_Name,
						PS.Polis_Ser,
						PS.Polis_Num,
						okved1.Okved_Name,
						org1.Org_OGRN,
						astat1.KLArea_Name,
						astat2.KLArea_Name,
						addr1.Address_Address,
						otherddlpu.Lpu_Nick
					-- end where
					order by
						-- order by
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName
						-- end order by
				";
			break;
		
			case 'PersonDispOrp':
			case 'PersonDispOrpPeriod':
			case 'PersonDispOrpPred':
			case 'PersonDispOrpProf':
			case 'PersonDispOrpOld':
			case 'EvnPLDispDop':
			case 'EvnPLDispDop13':
			case 'EvnPLDispProf':
			case 'EvnPLDispDopStream':
			case 'EvnPLDispTeen14':
			case 'EvnPLDispTeen14Stream':
			case 'EvnPLDispOrp':
			case 'EvnPLDispOrpOld':
			case 'EvnPLDispOrpSec':
			case 'EvnPLDispTeenInspectionPeriod':
			case 'EvnPLDispTeenInspectionProf':
			case 'EvnPLDispTeenInspectionPred':
			case 'EvnPLDispOrpStream':
			case 'PersonDisp':
			case 'PersonCardStateDetail':
			case 'WorkPlacePolkaReg':
			case 'PersonCard':
			case 'PersonPrivilegeWOW':
			case 'EvnPLWOW':
				$query .= "
					order by
						-- order by
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName
						-- end order by
				";			   
			break;
		
			case 'EvnNotifyHepatitis':
				$query .= "
					order by
						-- order by
						ENH.EvnNotifyHepatitis_setDT DESC
						-- end order by
				";			   
			break;
			case 'EvnOnkoNotify':
				$query .= "
					order by
						-- order by
						EON.EvnOnkoNotify_setDT DESC
						-- end order by
				";			   
			break;
			case 'EvnNotifyOrphan':
				$query .= "
					order by
						-- order by
						ENO.EvnNotifyOrphan_setDT DESC
						-- end order by
				";			   
			break;
			case 'EvnNotifyCrazy':
				$query .= "
					order by
						-- order by
						ENC.EvnNotifyCrazy_setDT DESC
						-- end order by
				";
				break;
			case 'EvnNotifyNarko':
				$query .= "
					order by
						-- order by
						ENC.EvnNotifyCrazy_setDT DESC
						-- end order by
				";
				break;
			case 'EvnNotifyTub':
				$query .= "
					order by
						-- order by
						ENC.EvnNotifyTub_setDT DESC
						-- end order by
				";
				break;
			case 'EvnNotifyHIV':
				$query .= "
					order by
						-- order by
						ENB.EvnNotifyBase_setDT DESC
						-- end order by
				";
				break;
			case 'EvnNotifyVener':
				$query .= "
					order by
						-- order by
						ENC.EvnNotifyVener_setDT DESC
						-- end order by
				";
				break;

			case 'OnkoRegistry':	
			case 'HepatitisRegistry':
			case 'OrphanRegistry':
			case 'CrazyRegistry':
			case 'NarkoRegistry':
			case 'TubRegistry':
			case 'VznRegistry':
			case 'DiabetesRegistry':
			case 'LargeFamilyRegistry':
			case 'HIVRegistry':
			case 'VenerRegistry':
				$query .= "
					order by
						-- order by
						PR.PersonRegister_setDate DESC
						-- end order by
				";			   
			break;
		
			case 'EvnInfectNotify':
				$query .= "
					group by
						EIN.EvnInfectNotify_id,
						EIN.EvnInfectNotify_insDT,
						PS.Person_id,
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName,
						PS.Person_Birthday,
						Lpu.Lpu_Nick,
						Diag.diag_FullName,
						Diag1.diag_FullName
					-- end where
					order by
						-- order by
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName
						-- end order by
				";			   
			break;

			case 'PersonPrivilege':
				$query .= "
					order by
						-- order by
						Person_Surname,
						Person_Firname,
						Person_Secname,
						PP.PersonPrivilege_id
						-- end order by
				";
			break;
		}
		$response = array();

		//if($data['SearchFormType'] == 'KvsPerson') { echo getDebugSQL($query, $queryParams); return false; }
		//echo getDebugSQL($query, $queryParams); return false;
		if (strtolower($data['onlySQL']) == 'on' && isSuperAdmin())
		{
			echo getDebugSQL($query, $queryParams); return false;
		}

		//echo getDebugSQL($query, $queryParams); exit;//test


		if ( $getCount == true ) {
			$get_count_query = getCountSQLPH($query);
					
			$get_count_result = $this->db->query($get_count_query, $queryParams);

			if ( is_object($get_count_result) ) {
				$response['totalCount'] = $get_count_result->result('array');
				$response['totalCount'] = $response['totalCount'][0]['cnt'];
			}
			else {
				return false;
			}
		}
		else if ( $print === true ) {
			//echo "<pre>".getDebugSQL($query, $queryParams); exit();
			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				$response['data'] = $result->result('array');
			}
			else {
				return false;
			}
		}
		else if ( $dbf === true )
		{
			if ($data['SearchFormType'] == "EvnDiag" || substr($data['SearchFormType'],0,3) == 'Kvs') {
				$fld_name = 'EvnDiagPS_id';
				switch ( $data['SearchFormType'] ) {
					case 'KvsPerson': $fld_name = $PS_prefix.($data['kvs_date_type'] == 2 ? '.PersonEvn_id' : '.Person_id'); break;
					case 'KvsPersonCard': $fld_name = 'PC.PersonCard_id'; break;
					case 'KvsEvnDiag': $fld_name = 'EvnDiagPS_id'; break;
					case 'KvsEvnPS': $fld_name = 'EPS.EvnPS_id'; break;
					case 'KvsEvnSection': $fld_name = 'ESEC.EvnSection_id'; break;
					case 'KvsNarrowBed': $fld_name = 'ESNB.EvnSectionNarrowBed_id'; break;
					case 'KvsEvnUsluga': $fld_name = 'EU.EvnUsluga_id'; break;
					case 'KvsEvnUslugaOB': $fld_name = 'EUOB.EvnUslugaOperBrig_id'; break;
					case 'KvsEvnUslugaAn': $fld_name = 'EUOA.EvnUslugaOperAnest_id'; break;
					case 'KvsEvnUslugaOsl': $fld_name = 'EA.EvnAgg_id'; break;
					case 'KvsEvnDrug': $fld_name = 'ED.EvnDrug_id'; break;
					case 'KvsEvnLeave': $fld_name = 'ESEC.EvnSection_id'; break;
					case 'KvsEvnStick': $fld_name = 'EST.EvnStick_id'; break;
					default: $fld_name = 'EPS.EvnPs_id';
				}
				$get_count_query = getCountSQLPH($query, '', 'distinct '.$fld_name);
			} else {
				$get_count_query = getCountSQLPH($query);
			}

			$get_count_result = $this->db->query($get_count_query, $queryParams);
			if ( !is_object($get_count_result) ) {
				return false;
			}

			$records_count = $get_count_result->result('array');
			$cnt = $records_count[0]['cnt'];

			/*if ($data['SearchFormType'] == 'KvsEvnLeave') {
				echo "<pre>".getDebugSQL($query, $queryParams);
				die;
			}*/
			//echo "<pre>".getDebugSQL($get_count_query, $queryParams);
			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				return array('data' => $result, 'totalCount' => $cnt);
			}
			else {
				return false;
			}
		}
		else {
			if ( $data['start'] >= 0 && $data['limit'] >= 0 ) {
				$limit_query = getLimitSQLPH($query, $data['start'], $data['limit']);
				// die(getDebugSQL($limit_query, $queryParams));
				$result = $this->db->query($limit_query, $queryParams);
			}
			else {
				$result = $this->db->query($query, $queryParams);
			}

			if ( is_object($result) ) {
				$res = $result->result('array');
				if ( is_array($res) ) {
					if ( $data['start'] == 0 && count($res) < $data['limit'] ) {
						$response['data'] = $res;
						$response['totalCount'] = count($res);
					}
					else {
						$response['data'] = $res;
						$get_count_query = getCountSQLPH($query);
						
						// приходится так делать из-за группировки
						if ( $data['SearchFormType'] == "PersonDopDisp" )
						{
							$get_count_query = "select count(*) as cnt from (" . $get_count_query . ") as pdd";
						}
						
						$get_count_result = $this->db->query($get_count_query, $queryParams);
						

						if ( is_object($get_count_result) ) {
							$response['totalCount'] = $get_count_result->result('array');
							$response['totalCount'] = $response['totalCount'][0]['cnt'];
						}
						else {
							return false;
						}
					}
				}
				else {
					return false;
				}
			}
			else {
				return false;
			}
		}

        /*echo "<pre>";
        print_r($data);
        echo "</pre>";
        echo "<pre>".getDebugSQL($query, $queryParams)."</pre>";
        die();*/

		return $response;
	}
    
    
}
?>
