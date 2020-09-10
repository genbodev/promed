<?php
/**
 * Pskov_TFOMSAutoInteract_model - модель для автоматического взаимодействия с ТФОМС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      TFOMSAutoInteract
 * @access       public
 * @copyright    Copyright (c) 2020 Swan Ltd.
 * @author
 * @version
 */

defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'models/TFOMSAutoInteract_model.php');

class Pskov_TFOMSAutoInteract_model extends TFOMSAutoInteract_model
 {
/**
 * Конструктор
 */
  function __construct()
   {
    parent::__construct();
   }

		function package_HOSPITALISATION_REFERRAL($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
			$params = array(
				'ProcDataType' => $procDataType,
			);

			$filters = "";
			$filters_del = "";
			if (!empty($data['exportId'])) {
				$exportIds_str = implode(',', $data['exportId']);
				$filters .= " and ED.EvnDirection_id in ({$exportIds_str})";
				$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
			} else {
				if (!empty($this->init_date)) {
					$filters .= " and ED.EvnDirection_insDT >= :InitDate";
					$params['InitDate'] = $this->init_date;
				}
				if (count($this->allowed_lpus) > 0) {
					$allowed_lpus_str = implode(",", $this->allowed_lpus);
					$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
				}
			}

			if ($procDataType == 'Delete') {
				$query = "
					-- variables
					declare @dt datetime = dbo.tzGetDate();
					declare @date date = cast(@dt as date);
					-- end variables
					select
						-- select
						PL.Lpu_id,
						PL.LPU as CODE_MO,
						dL.Lpu_f003mcod as CODE_MO_TO,
						PL.ObjectID,
						PL.ID as HR_ID,
						PL.GUID,
						'Delete' as OPERATIONTYPE,
						convert(varchar(10), @date, 120) as DATA
						-- end select
					from
						-- from
						{$tmpTable} PL
						inner join EvnDirection ED with(nolock) on ED.EvnDirection_id = PL.ObjectID
						left join v_Lpu dL with(nolock) on dL.Lpu_id = ED.Lpu_did
						left join v_Evn_del EDel with(nolock) on EDel.Evn_id = ED.EvnDirection_id
						-- end from
					where
						-- where
						PL.PACKAGE_TYPE = 'HOSPITALISATION_REFERRAL'
						and ED.DirType_id in (1,5)
						and isnull(ED.EvnDirection_failDT, EDel.Evn_delDT) > PL.DATE
						and nullif(nullif(PL.LPU,'0'),'') is not null
						{$filters_del}
						-- end where
					order by
						-- order by
						PL.Lpu_id
						-- end order by
				";
			} else {
				$query = "
					-- variables
					declare @dt datetime = dbo.tzGetDate();
					declare @date date = cast(@dt as date);
					declare @region int = dbo.getRegion();
					-- end variables
					select
						-- select
						L.Lpu_id,
						L.Lpu_f003mcod as CODE_MO,
						dL.Lpu_f003mcod as CODE_MO_TO,
						ED.EvnDirection_id as ObjectID,
						ED.EvnDirection_id as HR_ID,
						isnull(HOSPITALISATION_REFERRAL.GUID, newid()) as GUID,
						ProcDataType.Value as OPERATIONTYPE,
						convert(varchar(10), @date, 120) as DATA,
						ED.EvnDirection_Num as REFERRAL_NUMBER,
						(
							cast(L.Lpu_f003mcod as varchar)+
							cast(year(ED.EvnDirection_setDT) as varchar)+
							right('000000'+cast(ED.EvnDirection_Num as varchar), 6)
						) as NOM_NAP,
						convert(varchar(10), ED.EvnDirection_setDT, 120) as REFERRAL_DATE,
						convert(varchar(10), coalesce(
							TTS.TimeTableStac_setDate, ED.EvnDirection_desDT, ED.EvnDirection_setDT
						), 120) as HOSPITALISATION_DATE,
						case
							when @region = 10 and DirType.DirType_Code = 1 then 1
							when @region = 10 and DirType.DirType_Code = 5 then 3
							when DirType.DirType_Code = 1 then 0
							when DirType.DirType_Code = 5 then 1
						end as HOSPITALISATION_TYPE,
						DirType.DirType_Code as FRM_MP,
						-- coalesce(dBuildingCode.Value, dLB.LpuBuilding_Code) as BRANCH_TO,
						-- coalesce(dSectionCode.Value, dLS.LpuSection_Code) as DIVISION_TO,
						dL.Lpu_f003mcod + LEFT(dLS.LpuSection_Code, 3) as BRANCH_TO,
						dL.Lpu_f003mcod + dLS.LpuSection_Code as DIVISION_TO,
						fLSBP.LpuSectionBedProfile_Code as BEDPROFIL,
						LSP.LpuSectionProfile_Code as STRUCTURE_BED,
						LSBS.LpuSectionBedState_id as DLSB,
						case
							when @region <> 10 then null
							when dLU.LpuUnitType_SysNick like 'stac' then 1
							when dLU.LpuUnitType_SysNick like 'dstac' then 21
							when dLU.LpuUnitType_SysNick like 'pstac' then 22
							when dLU.LpuUnitType_SysNick like 'hstac' then 23
						end as CARETYPE,
						-- LB.LpuBuilding_Code as BRANCH_FROM,
						L.Lpu_f003mcod + LEFT(LS.LpuSection_Code, 3) as BRANCH_FROM,
						D.Diag_Code as MKB,
						D.Diag_Name as DIAGNOSIS,
						convert( varchar(10), coalesce(
							TTS.TimeTableStac_setDate, ED.EvnDirection_desDT, ED.EvnDirection_setDT
						), 120) as PLANNED_DATE,
						MP.Person_Snils as DOC_CODE,
						(
							left(MP.Person_Snils, 3) + '-' + substring(MP.Person_Snils, 4, 3) + '-' +
							substring(MP.Person_Snils, 7, 3) + ' ' + right(MP.Person_Snils, 2)
						) as DOC_CODE_14,
						PT.PolisType_CodeF008 as POLICY_TYPE,
						nullif(Polis.Polis_Ser, '') as POLIS_SERIAL,
						case when PT.PolisType_CodeF008 = 3
							then PS.Person_EdNum else Polis.Polis_Num
						end as POLIS_NUMBER,
						Smo.OrgSmo_Name as SMO,
						Smo.OrgSmo_f002smocod as SMO_CODE,
						SmoRgn.KLAdr_Ocatd as SMO_OKATO,
						left(SmoRgn.KLAdr_Ocatd, 5) as ST_OKATO,
						rtrim(PS.Person_SurName) as LAST_NAME,
						rtrim(PS.Person_FirName) as FIRST_NAME,
						rtrim(PS.Person_SecName) as FATHER_NAME,
						case when Sex.Sex_fedid = 1 then 10301 else 10302 end as SEX,
						Sex.Sex_fedid as W,
						convert(varchar(10), PS.Person_BirthDay, 120) as BIRTHDAY,
						isnull(nullif(PS.PersonPhone_Phone, ''), 'не указан') as PHONE,
						PS.Person_id as PATIENT,
						0 as ANOTHER_REGION
						-- end select
					from
						-- from
						v_EvnDirection ED with(nolock)
						inner join v_Lpu L with(nolock) on L.Lpu_id = ED.Lpu_id
						left join v_DirType DirType with(nolock) on DirType.DirType_id = ED.DirType_id
						left join v_LpuSection LS with(nolock) on LS.LpuSection_id = ED.LpuSection_id
						-- left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LS.LpuBuilding_id
						inner join v_Lpu dL with(nolock) on dL.Lpu_id = ED.Lpu_did
						inner join v_LpuSection dLS with(nolock) on dLS.LpuSection_id = ED.LpuSection_did
						inner join v_LpuUnit dLU with(nolock) on dLU.LpuUnit_id = dLS.LpuUnit_id
						-- inner join v_LpuBuilding dLB with(nolock) on dLB.LpuBuilding_id = dLS.LpuBuilding_id
						inner join v_LpuSectionProfile LSP with(nolock) on LSP.LpuSectionProfile_id = isnull(dLS.LpuSectionProfile_id, ED.LpuSectionProfile_id)
						inner join v_LpuSectionBedProfile LSBP with(nolock) on LSBP.LpuSectionBedProfile_id = dLS.LpuSectionBedProfile_id
						inner join fed.LpuSectionBedProfileLink LSBPL with(nolock) on LSBPL.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
						inner join fed.LpuSectionBedProfile fLSBP with(nolock) on fLSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
						inner join v_Diag D with(nolock) on D.Diag_id = ED.Diag_id
						inner join v_Person_all PS with(nolock) on PS.PersonEvn_id = ED.PersonEvn_id
						inner join v_Polis Polis with(nolock) on Polis.Polis_id = PS.Polis_id
						left join v_PolisType PT with(nolock) on PT.PolisType_id = Polis.PolisType_id
						left join v_OrgSmo Smo with(nolock) on Smo.OrgSmo_id = Polis.OrgSmo_id
						left join v_KLArea SmoRgn with(nolock) on SmoRgn.KLArea_id = Smo.KLRgn_id
						left join v_Sex Sex with(nolock) on Sex.Sex_id = PS.Sex_id
						left join v_TimeTableStac TTS with(nolock) on TTS.TimeTableStac_id = ED.TimeTableStac_id
						outer apply (
							select top 1 LSBS.*
							from v_LpuSectionBedState LSBS with(nolock)
							where LSBS.LpuSection_id = dLS.LpuSection_id
							--and LSBS.LpuSectionProfile_id = LSP.LpuSectionProfile_id	--todo: check
							and ED.EvnDirection_setDate between LSBS.LpuSectionBedState_begDate and isnull(LSBS.LpuSectionBedState_endDate, ED.EvnDirection_setDate)
							order by LSBS.LpuSectionBedState_begDate desc
						) LSBS
						outer apply (
							select top 1 MP.*
							from v_MedPersonal MP with(nolock)
							where MP.MedPersonal_id = ED.MedPersonal_id
						) MP
						outer apply (
							select top 1 ASV.AttributeSignValue_id
							from v_AttributeSignValue ASV with(nolock)
							inner join v_AttributeSign ASign with(nolock) on ASign.AttributeSign_id = ASV.AttributeSign_id
							where ASign.AttributeSign_Code = 1
							and ASV.AttributeSignValue_TablePKey = dLS.LpuSection_id
							and @date between ASV.AttributeSignValue_begDate and isnull(ASV.AttributeSignValue_endDate, @date)
							order by ASV.AttributeSignValue_begDate desc, ASV.AttributeSignValue_insDT desc
						) dASV
						outer apply (
							select top 1 AV.AttributeValue_ValueString as Value
							from v_AttributeValue AV with(nolock)
							inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
							where AV.AttributeSignValue_id = dASV.AttributeSignValue_id
							and A.Attribute_SysNick like 'Section_Code'
						) dSectionCode
						outer apply (
							select top 1 AV.AttributeValue_ValueString as Value
							from v_AttributeValue AV with(nolock)
							inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
							where AV.AttributeSignValue_id = dASV.AttributeSignValue_id
							and A.Attribute_SysNick like 'Building_Code'
						) dBuildingCode
						left join {$tmpTable} HOSPITALISATION_REFERRAL on HOSPITALISATION_REFERRAL.PACKAGE_TYPE = 'HOSPITALISATION_REFERRAL'
							and HOSPITALISATION_REFERRAL.ObjectID = ED.EvnDirection_id
						outer apply (
							select case
								when HOSPITALISATION_REFERRAL.ID is null then 'Insert'
								when HOSPITALISATION_REFERRAL.ID is not null and HOSPITALISATION_REFERRAL.DATE <= ED.EvnDirection_updDT then 'Update'
							end as Value
						) ProcDataType
						-- end from
					where
						-- where
						ProcDataType.Value = :ProcDataType
						and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
						and nullif(nullif(dL.Lpu_f003mcod,'0'),'') is not null
						and nullif(Smo.Orgsmo_f002smocod, '') is not null
						and DirType.DirType_Code in (1,5)
						and ED.EvnDirection_failDT is null
						{$filters}
						-- end where
					order by
						-- order by
						L.Lpu_id
						-- end order by
				";
			}

			if ($returnType == 'count') {
				$countResult = $this->queryResult(getCountSQLPH($query), $params);
				return $countResult[0]['cnt'];
			} else {
				return $this->queryResult(getLimitSQLPH($query, $start, $limit), $params);
			}
		}
 }
