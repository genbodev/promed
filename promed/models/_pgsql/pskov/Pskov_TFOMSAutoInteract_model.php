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

require_once(APPPATH.'models/_pgsql/TFOMSAutoInteract_model.php');

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
				'date' => $this->currentDT->format('Y-m-d'),
				'region' => $this->regionNumber,
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
					select
						-- select
						PL.Lpu_id as \"Lpu_id\",
						PL.LPU as \"CODE_MO\",
						dL.Lpu_f003mcod as \"CODE_MO_TO\",
						PL.ObjectID as \"ObjectID\",
						PL.ID as \"HR_ID\",
						PL.GUID as \"GUID\",
						'Delete' as \"OPERATIONTYPE\",
						cast(dbo.tzgetdate() as date) as \"DATA\"
						-- end select
					from
						-- from
						{$tmpTable} PL
						inner join EvnDirection ED on ED.EvnDirection_id = PL.ObjectID
						left join v_Lpu dL on dL.Lpu_id = ED.Lpu_did
						left join v_Evn_del EDel on EDel.Evn_id = ED.EvnDirection_id
						-- end from
					where
						-- where
						PL.PACKAGE_TYPE = 'HOSPITALISATION_REFERRAL'
						and ED.DirType_id in (1,5)
						and coalesce(ED.EvnDirection_failDT, EDel.Evn_delDT) > PL.DATE
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
					select
						-- select
						L.Lpu_id as \"Lpu_id\",
						L.Lpu_f003mcod as \"CODE_MO\",
						dL.Lpu_f003mcod as \"CODE_MO_TO\",
						ED.EvnDirection_id as \"ObjectID\",
						ED.EvnDirection_id as \"HR_ID\",
						coalesce(HOSPITALISATION_REFERRAL.GUID, newid()) as \"GUID\",
						ProcDataType.Value as \"OPERATIONTYPE\",
						cast(dbo.tzgetdate() as date) as \"DATA\",
						ED.EvnDirection_Num as \"REFERRAL_NUMBER\",
						(
							cast(L.Lpu_f003mcod as varchar) ||
							cast(date_part('year', ED.EvnDirection_setDT) as varchar) ||
							right('000000' || cast(ED.EvnDirection_Num as varchar), 6)
						) as \"NOM_NAP\",
						to_char(ED.EvnDirection_setDT, 'YYYY-MM-DD') as \"REFERRAL_DATE\",
						to_char(coalesce(
							TTS.TimeTableStac_setDate, ED.EvnDirection_desDT, ED.EvnDirection_setDT
						), 'YYYY-MM-DD') as \"HOSPITALISATION_DATE\",
						case
							when :region = 10 and DirType.DirType_Code = 1 then 1
							when :region = 10 and DirType.DirType_Code = 5 then 3
							when DirType.DirType_Code = 1 then 0
							when DirType.DirType_Code = 5 then 1
						end as \"HOSPITALISATION_TYPE\",
						DirType.DirType_Code as \"FRM_MP\",
						-- coalesce(dBuildingCode.Value, dLB.LpuBuilding_Code) as \"BRANCH_TO\",
						-- coalesce(dSectionCode.Value, dLS.LpuSection_Code) as \"DIVISION_TO\",
						dL.Lpu_f003mcod || LEFT(dLS.LpuSection_Code, 3) as \"BRANCH_TO\",
						dL.Lpu_f003mcod || dLS.LpuSection_Code as \"DIVISION_TO\",
						fLSBP.LpuSectionBedProfile_Code as \"BEDPROFIL\",
						LSP.LpuSectionProfile_Code as \"STRUCTURE_BED\",
						LSBS.LpuSectionBedState_id as \"DLSB\",
						case
							when :region <> 10 then null
							when dLU.LpuUnitType_SysNick like 'stac' then 1
							when dLU.LpuUnitType_SysNick like 'dstac' then 21
							when dLU.LpuUnitType_SysNick like 'pstac' then 22
							when dLU.LpuUnitType_SysNick like 'hstac' then 23
						end as \"CARETYPE\",
						-- LB.LpuBuilding_Code as \"BRANCH_FROM\",
						L.Lpu_f003mcod || LEFT(LS.LpuSection_Code, 3) as \"BRANCH_FROM\",
						D.Diag_Code as \"MKB\",
						D.Diag_Name as \"DIAGNOSIS\",
						to_char(coalesce(
							TTS.TimeTableStac_setDate, ED.EvnDirection_desDT, ED.EvnDirection_setDT
						), 'YYYY-MM-DD') as \"PLANNED_DATE\",
						MP.Person_Snils as \"DOC_CODE\",
						(
							left(MP.Person_Snils, 3) || '-' || substring(MP.Person_Snils, 4, 3) || '-' ||
							substring(MP.Person_Snils, 7, 3) || ' ' || right(MP.Person_Snils, 2)
						) as \"DOC_CODE_14\",
						PT.PolisType_CodeF008 as \"POLICY_TYPE\",
						nullif(Polis.Polis_Ser, '') as \"POLIS_SERIAL\",
						case when PT.PolisType_CodeF008 = 3
							then PS.Person_EdNum else Polis.Polis_Num
						end as \"POLIS_NUMBER\",
						Smo.OrgSmo_Name as \"SMO\",
						Smo.OrgSmo_f002smocod as \"SMO_CODE\",
						SmoRgn.KLAdr_Ocatd as \"SMO_OKATO\",
						left(SmoRgn.KLAdr_Ocatd, 5) as \"ST_OKATO\",
						rtrim(PS.Person_SurName) as \"LAST_NAME\",
						rtrim(PS.Person_FirName) as \"FIRST_NAME\",
						rtrim(PS.Person_SecName) as \"FATHER_NAME\",
						case when Sex.Sex_fedid = 1 then 10301 else 10302 end as \"SEX\",
						Sex.Sex_fedid as \"W\",
						to_char(PS.Person_BirthDay, 'YYYY-MM-DD') as \"BIRTHDAY\",
						coalesce(nullif(PS.PersonPhone_Phone, ''), 'не указан') as \"PHONE\",
						PS.Person_id as \"PATIENT\",
						0 as \"ANOTHER_REGION\"
						-- end select
					from
						-- from
						v_EvnDirection ED
						inner join v_Lpu L on L.Lpu_id = ED.Lpu_id
						left join v_DirType DirType on DirType.DirType_id = ED.DirType_id
						left join v_LpuSection LS on LS.LpuSection_id = ED.LpuSection_id
						-- left join v_LpuBuilding LB on LB.LpuBuilding_id = LS.LpuBuilding_id
						inner join v_Lpu dL on dL.Lpu_id = ED.Lpu_did
						inner join v_LpuSection dLS on dLS.LpuSection_id = ED.LpuSection_did
						inner join v_LpuUnit dLU on dLU.LpuUnit_id = dLS.LpuUnit_id
						-- inner join v_LpuBuilding dLB on dLB.LpuBuilding_id = dLS.LpuBuilding_id
						inner join v_LpuSectionProfile LSP on LSP.LpuSectionProfile_id = coalesce(dLS.LpuSectionProfile_id, ED.LpuSectionProfile_id)
						inner join v_LpuSectionBedProfile LSBP on LSBP.LpuSectionBedProfile_id = dLS.LpuSectionBedProfile_id
						inner join fed.LpuSectionBedProfileLink LSBPL on LSBPL.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
						inner join fed.LpuSectionBedProfile fLSBP on fLSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
						inner join v_Diag D on D.Diag_id = ED.Diag_id
						inner join v_Person_all PS on PS.PersonEvn_id = ED.PersonEvn_id
						inner join v_Polis Polis on Polis.Polis_id = PS.Polis_id
						left join v_PolisType PT on PT.PolisType_id = Polis.PolisType_id
						left join v_OrgSmo Smo on Smo.OrgSmo_id = Polis.OrgSmo_id
						left join v_KLArea SmoRgn on SmoRgn.KLArea_id = Smo.KLRgn_id
						left join v_Sex Sex on Sex.Sex_id = PS.Sex_id
						left join v_TimeTableStac TTS on TTS.TimeTableStac_id = ED.TimeTableStac_id
						left join lateral (
							select LSBS.*
							from v_LpuSectionBedState LSBS
							where LSBS.LpuSection_id = dLS.LpuSection_id
							--and LSBS.LpuSectionProfile_id = LSP.LpuSectionProfile_id	--todo: check
							and ED.EvnDirection_setDate between LSBS.LpuSectionBedState_begDate and coalesce(LSBS.LpuSectionBedState_endDate, ED.EvnDirection_setDate)
							order by LSBS.LpuSectionBedState_begDate desc
							limit 1
						) LSBS on true
						left join lateral (
							select MP.*
							from v_MedPersonal MP
							where MP.MedPersonal_id = ED.MedPersonal_id
							limit 1
						) MP on true
						left join lateral (
							select ASV.AttributeSignValue_id
							from v_AttributeSignValue ASV
							inner join v_AttributeSign ASign on ASign.AttributeSign_id = ASV.AttributeSign_id
							where ASign.AttributeSign_Code = 1
							and ASV.AttributeSignValue_TablePKey = dLS.LpuSection_id
							and cast(dbo.tzgetdate() as date) between ASV.AttributeSignValue_begDate and coalesce(ASV.AttributeSignValue_endDate, cast(dbo.tzgetdate() as date))
							order by ASV.AttributeSignValue_begDate desc, ASV.AttributeSignValue_insDT desc
							limit 1
						) dASV on true
						left join lateral (
							select AV.AttributeValue_ValueString as Value
							from v_AttributeValue AV
							inner join v_Attribute A on A.Attribute_id = AV.Attribute_id
							where AV.AttributeSignValue_id = dASV.AttributeSignValue_id
							and A.Attribute_SysNick like 'Section_Code'
							limit 1
						) dSectionCode on true
						left join lateral (
							select AV.AttributeValue_ValueString as Value
							from v_AttributeValue AV
							inner join v_Attribute A on A.Attribute_id = AV.Attribute_id
							where AV.AttributeSignValue_id = dASV.AttributeSignValue_id
							and A.Attribute_SysNick like 'Building_Code'
							limit 1
						) dBuildingCode on true
						left join {$tmpTable} HOSPITALISATION_REFERRAL on HOSPITALISATION_REFERRAL.PACKAGE_TYPE = 'HOSPITALISATION_REFERRAL'
							and HOSPITALISATION_REFERRAL.ObjectID = ED.EvnDirection_id
						left join lateral (
							select case
								when HOSPITALISATION_REFERRAL.ID is null then 'Insert'
								when HOSPITALISATION_REFERRAL.ID is not null and HOSPITALISATION_REFERRAL.DATE <= ED.EvnDirection_updDT then 'Update'
							end as Value
						) ProcDataType on true
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
