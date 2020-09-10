<?php
defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'models/TFOMSAutoInteract_model.php');

/**
 * TFOMSAutoInteract_model - модель для автоматического взаимодействия с ТФОМС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			TFOMSAutoInteract
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan-it.ru)
 * @version			11.2018
 */
class Kareliya_TFOMSAutoInteract_model extends TFOMSAutoInteract_model {
	protected $allowSaveGUID = true;

	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @return array
	 */
	function getPackageTypeMap() {
		return array_merge(parent::getPackageTypeMap(), array(
			'DISPPLAN' => 'PROF',
			'PERSONATTACHDISTRICT' => 'ATTACH_DATA',
			'DISP' => 'DN_IN',
			'DISPOUT' => 'DN_OUT',
			'HOSPITALISATION' => 'PLAN_HOSPITALISATION',
			'EXTRHOSPITALISATION' => 'EXTR_HOSPITALISATION',
			'DISTRICT' => 'AREA_DATA',
		));
	}

	/**
	 * @return array
	 */
	function getPackageFieldsMap() {
		$map = parent::getPackageFieldsMap();

		$map['DISPPLAN'] = array(
			'BODY' => 'PERS',
			'OPERATIONTYPE' => 'TYPE',
			'MESSAGE_ID' => 'ID',
			'GUID' => 'ID',
			'TEL1' => 'PHONE1',
			'TEL2' => 'PHONE2',
			'SMOCOD' => 'SMO',
			'DOC_SNILS' => 'SNILS_VR',
			'PERSONATTACHDATE' => 'DATE',
			'PERSONATTACHMETHOD' => 'SP_PRIK',
			'PERSONATTACHTYPE' => 'T_PRIK',
			'LPUREGION_NAME' => 'NUM_UCH',
			'LPUREGIONTYPE' => 'TIP_UCH',
			'LPUBUILDING_CODETFOMS' => 'KOD_PODR',
			'PERIOD' => 'DISP_MONTH',
		);

		$map['PERSONATTACHDISTRICT'] = array(
			'ATTACH_TYPE' => 'SP_PRIK',
			'PODR' => 'KOD_PODR',
			'UCH' => 'NUM_UCH',
		);

		$map['DISP'] = array(
			'OPERATIONTYPE' => 'TYPE',
			'GUID' => 'ID',
			'DS' => 'DS_DISP',
		);

		$map['DISPOUT'] = array(
			'OPERATIONTYPE' => 'TYPE',
			'GUID' => 'ID',
			'DS' => 'DS_DISP',
		);

		$map['HOSPITALISATION_REFERRAL'] = array(
			'OPERATIONTYPE' => 'TYPE',
			'GUID' => 'ID',
			'CODE_MO' => 'MCOD_NAP',
			'CODE_MO_TO' => 'MCOD_STC',
			'REFERRAL_DATE' => 'DTA_NAP',
			'BRANCH_TO' => 'MPODR_STC',
			'STRUCTURE_BED' => 'KOD_PFO',
			'BEDPROFIL' => 'KOD_PFK',
			'CARETYPE' => 'USL_OK',
			'BRANCH_FROM' => 'MPODR_NAP',
			'MKB' => 'DS',
			'PLANNED_DATE' => 'DTA_PLN',
			'DOC_CODE_14' => 'KOD_DCT',
			'POLICY_TYPE' => 'VPOLIS',
			'POLIS_SERIAL' => 'SPOLIS',
			'POLIS_NUMBER' => 'NPOLIS',
			'FIRST_NAME' => 'IM',
			'LAST_NAME' => 'FAM',
			'FATHER_NAME' => 'OT',
			'BIRTHDAY' => 'DR',
			'PHONE' => 'TLF',
			'HOSPITALISATION_TYPE' => 'FRM_MP',
		);

		$map['CANCEL_HOSPITALISATION_REFERRAL'] = array(
			'OPERATIONTYPE' => 'TYPE',
			'GUID' => 'ID',
			'DATE' => 'DTA_NAP',
			'REFERRAL_LPU' => 'MCOD_NAP',
			'BRANCH' => 'MPODR_ANL',
			'REASON' => 'PR_ANL',
			'CANCEL_SOURSE' => 'IST_ANL',
			'CANCEL_CODE' => 'ACOD',
		);

		$map['HOSPITALISATION'] = array(
			'OPERATIONTYPE' => 'TYPE',
			'GUID' => 'ID',
			'REFERRAL_DATE' => 'DTA_NAP',
			'REFERRAL_MO' => 'MCOD_NAP',
			'REFERRAL_BRANCH' => 'MPODR_NAP',
			'MO' => 'MCOD_STC',
			'BRANCH' => 'MPODR_STC',
			'STRUCTURE_BED' => 'KOD_PFO',
			'BEDPROFIL' => 'KOD_PFK',
			'FORM_MEDICAL_CARE' => 'FRM_MP',
			'HOSPITALISATION_DATE' => 'DTA_FKT',
			'HOSPITALISATION_TIME_STR' => 'TIM_FKT',
			'POLICY_TYPE' => 'VPOLIS',
			'POLIS_SERIAL' => 'SPOLIS',
			'POLIS_NUMBER' => 'NPOLIS',
			'SMO' => 'SMO_CODE',
			'FIRST_NAME' => 'IM',
			'LAST_NAME' => 'FAM',
			'FATHER_NAME' => 'OT',
			'BIRTHDAY' => 'DR',
			'CARETYPE' => 'USL_OK',
			'MED_CARD_NUMBER' => 'NHISTORY',
			'MKB' => 'DS',
		);

		$map['EXTRHOSPITALISATION'] = array(
			'OPERATIONTYPE' => 'TYPE',
			'GUID' => 'ID',
			'REFERRAL_DATE' => 'DTA_NAP',
			'MO' => 'MCOD_STC',
			'BRANCH' => 'MPODR_STC',
			'STRUCTURE_BED' => 'KOD_PFO',
			'BEDPROFIL' => 'KOD_PFK',
			'HOSPITALISATION_DATE' => 'DTA_FKT',
			'HOSPITALISATION_TIME_STR' => 'TIM_FKT',
			'POLICY_TYPE' => 'VPOLIS',
			'POLIS_SERIAL' => 'SPOLIS',
			'POLIS_NUMBER' => 'NPOLIS',
			'SMO' => 'SMO_CODE',
			'FIRST_NAME' => 'IM',
			'LAST_NAME' => 'FAM',
			'FATHER_NAME' => 'OT',
			'BIRTHDAY' => 'DR',
			'CARETYPE' => 'USL_OK',
			'MED_CARD_NUMBER' => 'NHISTORY',
			'MKB' => 'DS',
		);

		$map['MOTION_IN_HOSPITAL'] = array(
			'OPERATIONTYPE' => 'TYPE',
			'CODE_MO' => 'MCOD_STC',
			'GUID' => 'ID',
			'REFERRAL_DATE' => 'DTA_NAP',
			'HOSPITALISATION_TYPE' => 'FRM_MP',
			'BRANCH' => 'MPODR_STC',
			'STRUCTURE_BED' => 'KOD_PFO',
			'BEDPROFIL' => 'KOD_PFK',
			'CARETYPE' => 'USL_OK',
			'DATE_IN' => 'DTA_FKT',
			'DATE_OUT' => 'DTA_END',
			'MED_CARD_NUMBER' => 'NHISTORY',
			'POLICY_TYPE' => 'VPOLIS',
			'POLIS_SERIAL' => 'SPOLIS',
			'POLIS_NUMBER' => 'NPOLIS',
			'SMO' => 'SMO_CODE',
			'FIRST_NAME' => 'IM',
			'LAST_NAME' => 'FAM',
			'FATHER_NAME' => 'OT',
			'BIRTHDAY' => 'DR',
		);

		$map['FREE_BEDS_INFORMATION'] = array(
			'OPERATIONTYPE' => 'TYPE',
			'CODE_MO' => 'MCOD_STC',
			'GUID' => 'ID',
			'ACTUAL_DATE' => 'DTA_RAB',
			'BRANCH' => 'MPODR_STC',
			'BEDPROFIL' => 'KOD_PFK',
			'CARETYPE' => 'USL_OK',
			'BEDOCCUPIED' => 'KOL_PAC',
			'BEDOCCUPIEDTODAY' => 'KOL_IN',
			'BEDCLEARTODAY' => 'KOL_OUT',
			'BEDPLANNED' => 'KOL_PLN',
			'BEDFREE' => 'KOL_PUS',
			'BEDFREEADULT' => 'KOL_PUS_V',
			'BEDFREECHILD' => 'KOL_PUS_D',
		);

		$map['DISTRICT'] = array(
			'OPERATIONTYPE' => 'OPER_TYPE',
			'LPUREGION_ID' => 'ID_AREA',
			'LPUREGION_NAME' => 'NUM_UCH',
			'LPUREGIONTYPE' => 'TIP_UCH',
			'DOC_SNILS' => 'SNILS_VR',
		);
		
		return $map;
	}
	
	/**
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	// убрали в задаче #197576
	/*
	function package_HOSPITALISATION($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		if ($procDataType != 'Delete') {
			return parent::package_HOSPITALISATION($tmpTable, $procDataType, $data, $returnType, $start, $limit);
		}
		
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$filters_del = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters_del .= " and HOSPITALISATION.ObjectID in ({$exportIds_str})";
		} else {
			if (!empty($this->init_date)) {
				$filters_del .= " and EPS.EvnPS_insDT >= :InitDate";
				$params['InitDate'] = $this->init_date;
			}
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters_del .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}

		$packageTypesInSQL = $this->getPackageTypes(['HOSPITALISATION'], true, true);
		
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
				EPS.EvnPS_id as ObjectID,
				EPS.EvnPS_id as H_ID,
				isnull(HOSPITALISATION.GUID, newid()) as GUID,
				'Delete' as OPERATIONTYPE,
				convert(varchar(10), @date, 120) as DATA,
				isnull(ED.EvnDirection_Num, EPS.EvnDirection_Num) as REFERRAL_NUMBER,
				(
					cast(sL.Lpu_f003mcod as varchar)+
					cast(year(isnull(ED.EvnDirection_setDT, EPS.EvnDirection_setDT)) as varchar)+
					right('000000'+cast(isnull(ED.EvnDirection_Num, EPS.EvnDirection_Num) as varchar), 6)
				) as NOM_NAP,
				convert(varchar(10), isnull(ED.EvnDirection_setDT, EPS.EvnDirection_setDT), 120) as REFERRAL_DATE,
				sL.Lpu_f003mcod as REFERRAL_MO,
				sLB.LpuBuilding_Code as REFERRAL_BRANCH,
				L.Lpu_f003mcod as MO,
				LB.LpuBuilding_Code as BRANCH,
				LS.LpuSection_Code as DIVISION,
				case 
					when @region = 10 and PT.PrehospType_Code = 1 then 1
					when @region = 10 then 2
					when PT.PrehospType_Code = 1 then 0 
					else 1 
				end as FORM_MEDICAL_CARE,
				convert(varchar(10), EPS.EvnPS_setDT, 120) as HOSPITALISATION_DATE,
				convert(varchar(19), EPS.EvnPS_setDT, 126) as HOSPITALISATION_TIME,
				(
					right('0'+cast(datepart(hour, EPS.EvnPS_setDT) as varchar), 2)+'-'+
					right('0'+cast(datepart(minute, EPS.EvnPS_setDT) as varchar), 2)
				) as HOSPITALISATION_TIME_STR,
				PolisType.PolisType_CodeF008 as POLICY_TYPE,
				nullif(Polis.Polis_Ser, '') as POLIS_SERIAL,
				case when PolisType.PolisType_CodeF008 = 3
					then PS.Person_EdNum else Polis.Polis_Num
				end as POLIS_NUMBER,
				Smo.Orgsmo_f002smocod as SMO,
				PS.Person_SurName as LAST_NAME,
				PS.Person_FirName as FIRST_NAME,
				PS.Person_SecName as FATHER_NAME,
				case when Sex.Sex_fedid = 1 then 10301 else 10302 end as SEX,
				Sex.Sex_fedid as W,
				convert(varchar(10), PS.Person_BirthDay, 120) as BIRTHDAY,
				LSP.LpuSectionProfile_Code as STRUCTURE_BED,
				fLSBP.LpuSectionBedProfile_Code as BEDPROFIL,
				LSBS.LpuSectionBedState_id as DLSB,
				case
					when dbo.getRegion() <> 10 then null
					when LU.LpuUnitType_SysNick like 'stac' then 1
					when LU.LpuUnitType_SysNick like 'dstac' then 21
					when LU.LpuUnitType_SysNick like 'pstac' then 22
					when LU.LpuUnitType_SysNick like 'hstac' then 23
				end as CARETYPE,
				EPS.EvnPS_NumCard as MED_CARD_NUMBER,
				D.Diag_Code as MKB,
				D.Diag_Name as DIAGNOSIS,
				EPS.Person_id as PATIENT
				-- end select
			from
				-- from
				{$tmpTable} HOSPITALISATION
				inner join v_EvnPS_del EPS with(nolock) on EPS.EvnPS_id = HOSPITALISATION.ObjectID
				inner join v_Lpu L with(nolock) on L.Lpu_id = EPS.Lpu_id
				inner join v_LpuSection LS with(nolock) on LS.LpuSection_id = EPS.LpuSection_id
				inner join v_LpuSectionProfile LSP with(nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
				inner join v_LpuSectionBedProfile LSBP with(nolock) on LSBP.LpuSectionBedProfile_id = LS.LpuSectionBedProfile_id
				inner join fed.LpuSectionBedProfileLink LSBPL with(nolock) on LSBPL.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
				inner join fed.LpuSectionBedProfile fLSBP with(nolock) on fLSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
				inner join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				inner join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LS.LpuBuilding_id
				inner join v_PrehospType PT with(nolock) on PT.PrehospType_id = EPS.PrehospType_id
				inner join v_Diag D with(nolock) on D.Diag_id = EPS.Diag_id
				inner join v_Person_all PS with(nolock) on PS.PersonEvn_id = EPS.PersonEvn_id
				left join v_Sex Sex with(nolock) on Sex.Sex_id = PS.Sex_id
				left join v_Polis Polis with(nolock) on Polis.Polis_id = PS.Polis_id
				left join v_PolisType PolisType with(nolock) on PolisType.PolisType_id = Polis.PolisType_id
				left join v_OrgSmo Smo with(nolock) on Smo.OrgSmo_id = Polis.OrgSmo_id
				left join v_EvnDirection ED with(nolock) on  ED.EvnDirection_id = EPS.EvnDirection_id
				left join v_LpuSection sLS with(nolock) on sLS.LpuSection_id = isnull(ED.LpuSection_id, EPS.LpuSection_did)
				left join v_Lpu sL with(nolock) on sL.Lpu_id = coalesce(ED.Lpu_sid, EPS.Lpu_did, sLS.Lpu_id)
				left join v_LpuBuilding sLB with(nolock) on sLB.LpuBuilding_id = sLS.LpuBuilding_id
				outer apply (
					select top 1 LSBS.*
					from v_LpuSectionBedState LSBS with(nolock)
					where LSBS.LpuSection_id = EPS.LpuSection_id
					and EPS.EvnPS_setDate between LSBS.LpuSectionBedState_begDate and isnull(LSBS.LpuSectionBedState_endDate, EPS.EvnPS_setDate)
					order by LSBS.LpuSectionBedState_begDate desc
				) LSBS
				-- end from
			where
				-- where
				HOSPITALISATION.PACKAGE_TYPE in ({$packageTypesInSQL})
				and EPS.Evn_deleted = 2
				and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
				and (dbo.getRegion() != 10 or PT.PrehospType_Code = 1)
				and EPS.PrehospWaifRefuseCause_id is null
				{$filters_del}
				-- end where
			order by
				-- order by
				L.Lpu_id
				-- end order by
		";
		
		if ($returnType == 'count') {
			$countResult = $this->queryResult(getCountSQLPH($query), $params);
			return $countResult[0]['cnt'];
		} else {
			return $this->queryResult(getLimitSQLPH($query, $start, $limit), $params);
		}
	}*/

	/**
	 * Получение данных о дисп.наблюдении
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	function package_DISP($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$filters = "";
		$filters_del = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and PD.PersonDisp_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		} else {
			if (!empty($this->init_date)) {
				$filters .= " and PD.PersonDisp_insDT >= :InitDate";
				$params['InitDate'] = $this->init_date;
			}
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}

		$packageTypesInSQL = $this->getPackageTypes(['DISP'], true, true);

		if ($procDataType == 'Insert'){
			$filters .= "
				and (
					exists (
						select top 1 
							dsd.DispSickDiag_id
						from
							r10.v_DispSickDiag dsd (nolock)
						where
							dsd.Diag_id = PD.Diag_id
							and ISNULL(dsd.DispSickDiag_begDT,@dt)<=  @dt
							and ISNULL(dsd.DispSickDiag_endDT,  @dt) >=  @dt
					)
					or D.Diag_Code between 'C00' and 'C97'
				)
			";
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
					PL.ObjectID,
					PL.ID,
					PL.GUID,
					'Delete' as TYPE,
					convert(varchar(10), @date, 120) as DATA
					-- end select
				from
					-- from
					{$tmpTable} PL
					left join v_PersonDISP PD with(nolock) on PD.PersonDisp_id = PL.ObjectID
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE in ({$packageTypesInSQL})
					and PD.PersonDisp_id is null
					and nullif(nullif(PL.LPU,'0'),'') is not null
					and PD.PersonDisp_endDate is null
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
				-- end variables
				select
					-- select
					L.Lpu_id,
					L.Lpu_f003mcod as CODE_MO,
					PD.PersonDisp_id as ObjectID,
					PD.PersonDisp_id as ID,
					isnull(DISP.GUID, newid()) as GUID,
					ProcDataType.Value as TYPE,
					convert(varchar(10), @date, 120) as DATA,
					P.Person_id as ID_PAC,
					rtrim(PS.Person_SurName) as FAM,
					rtrim(PS.Person_FirName) as IM,
					rtrim(PS.Person_SecName) as OT,
					Sex.Sex_fedid as W,
					convert(varchar(10), PS.Person_BirthDay, 120) as DR,
					PT.PolisType_CodeF008 as VPOLIS,
					Polis.Polis_Ser as SPOLIS,
					case
						when PT.PolisType_CodeF008 = 3
						then PS.Person_EdNum 
						else Polis.Polis_Num
					end  as NPOLIS,
					isnull(nullif(PS.Person_Phone, ''), 'не указан') as PHONE,
					convert(varchar(10), PD.PersonDisp_begDate, 120) as DATE_IN,
					case when RIGHT( D.Diag_Code ,1) = '.'  then LEFT(D.Diag_Code, LEN(D.Diag_Code)-1) else D.Diag_Code end AS DS_DISP,
					MPPS.Person_Snils as SNILS_VR,
					null as KRAT,
					null as DN_MONTH1,
					null as DN_MONTH2,
					null as DN_MONTH3,
					null as DN_MONTH4,
					null as DN_MONTH5,
					null as DN_MONTH6,
					null as DN_MONTH7,
					null as DN_MONTH8,
					null as DN_MONTH9,
					null as DN_MONTH10,
					null as DN_MONTH11,
					null as DN_MONTH12,
					1 as DN_PLACE
					-- end select
				from
					-- from
					v_PersonDisp PD with(nolock)
					inner join v_Lpu L with(nolock) on L.Lpu_id = PD.Lpu_id
					inner join Person P with(nolock) on P.Person_id = PD.Person_id
					inner join v_PersonState PS with(nolock) on PS.Person_id = P.Person_id
					left join v_Sex Sex with(nolock) on Sex.Sex_id = PS.Sex_id
					left join v_Polis Polis with(nolock) on Polis.Polis_id = PS.Polis_id
					left join v_PolisType PT with(nolock) on PT.PolisType_id = Polis.PolisType_id
					left join v_Document Document with(nolock) on Document.Document_id = PS.Document_id
					left join v_DocumentType DT with(nolock) on DT.DocumentType_id = Document.DocumentType_id
					left join v_Diag D with(nolock) on D.Diag_id = PD.Diag_id
					left join v_DiagDetectType Detect with(nolock) on Detect.DiagDetectType_id = PD.DiagDetectType_id
					left join v_DeseaseDispType DetectType with(nolock) on DetectType.DeseaseDispType_id = PD.DeseaseDispType_id
					left join v_DispOutType DOT with(nolock) on DOT.DispOutType_id = PD.DispOutType_id
					outer apply (
						select top 1 MP.*
						from v_MedPersonal MP with(nolock)
						where MP.MedPersonal_id = PD.MedPersonal_id
					) MP
					left join v_PersonState MPPS with(nolock) on MPPS.Person_id = MP.Person_id
					left join {$tmpTable} DISP on DISP.PACKAGE_TYPE in ({$packageTypesInSQL}) and DISP.ObjectID = PD.PersonDisp_id
					outer apply (
						select case
							when DISP.ID is null then 'Insert'
							when DISP.ID is not null and DISP.DATE <= PD.PersonDisp_updDT then 'Update'
						end as Value
					) ProcDataType
					-- end from 
				where
					-- where
					ProcDataType.Value = :ProcDataType
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					and PD.PersonDisp_endDate is null
					and exists(
						select * from v_PersonDispVizit PDV with(nolock)
						where PDV.PersonDisp_id = PD.PersonDisp_id
						and PDV.PersonDispVizit_NextDate is not null
						and PDV.PersonDispVizit_NextFactDate is null
					)
					and (
						dbo.Age2(PS.Person_BirthDay, @date) >= 18 or
						substring(D.Diag_Code, 1, 1) = 'C'
					)
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
			$resp = $this->queryResult(getLimitSQLPH($query, $start, $limit), $params);
			if (!is_array($resp) || empty($resp)) return false;

			$ids = array();
			$result = array();
			foreach($resp as $item) {
				$id = $item['ObjectID'];
				$ids[] = $id;
				$result[$id] = $item;
			}
			
			if ($procDataType != 'Delete') {
				$ids_str = implode(',', $ids);

				$query = "
					select
						PDV.PersonDisp_id as ObjectID,
						month(PDV.PersonDispVizit_NextDate) as DN_MONTH
					from
						v_PersonDispVizit PDV with(nolock)
					where
						PDV.PersonDisp_id in ({$ids_str})
						and PDV.PersonDispVizit_NextDate is not null
						and PDV.PersonDispVizit_NextFactDate is null
					order by
						PDV.PersonDisp_id,
						PDV.PersonDispVizit_NextDate
				";
				$resp = $this->queryResult($query);
				if (!is_array($resp) || empty($resp)) return false;

				foreach($resp as $item) {
					$result[$item['ObjectID']]['DN_MONTH'][] = $item['DN_MONTH'];
				}
				foreach($result as $ObjectID => &$item) {
					if (empty($item['DN_MONTH'])) {
						continue;
					}
					$dn_month_count = count($item['DN_MONTH']);
					$item['KRAT'] = ($dn_month_count < 12) ? $dn_month_count : 12;
					for ($i = 1; $i <= $item['KRAT']; $i++) {
						$item['DN_MONTH'.$i] = $item['DN_MONTH'][$i-1];
					}
					unset($item['DN_MONTH']);
				}
			}

			return array_values($result);
		}
	}

	/**
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	// убрали в задаче #197576
	/*function package_HOSPITALISATION_REFERRAL($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		if ($procDataType != 'Delete') {
			return parent::package_HOSPITALISATION_REFERRAL($tmpTable, $procDataType, $data, $returnType, $start, $limit);
		}
		
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$filters_del = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		} 
		
		$query = "
			-- variables
			declare @dt datetime = dbo.tzGetDate();
			declare @date date = cast(@dt as date);
			declare @region int = dbo.getRegion();
			-- end variables
			select
				-- select
				PL.Lpu_id,
				
				-- MCOD_NAP
				PL.LPU as CODE_MO,
				
				-- MCOD_STC
				dL.Lpu_f003mcod as CODE_MO_TO,
				PL.ObjectID,
				PL.ID as HR_ID,
				isnull(PL.GUID, newid()) as GUID,
				'Delete' as OPERATIONTYPE,
				convert(varchar(10), @date, 120) as DATA,
				(
					cast(L.Lpu_f003mcod as varchar)+
					cast(year(ED.EvnDirection_setDT) as varchar)+
					right('000000'+cast(ED.EvnDirection_Num as varchar), 6)
				) as NOM_NAP,
				
				-- DTA_NAP
				convert(varchar(10), ED.EvnDirection_setDT, 120) as REFERRAL_DATE,
				
				-- FRM_MP
				case 
					when DirType.DirType_Code = 1 then 3
					when DirType.DirType_Code = 5 then 1
				end as HOSPITALISATION_TYPE,
				
				-- MPODR_NAP
				--  #195757 - по требованию Макаровой Елены (Карелия)
				left(LS.LpuSection_Code, 2) as BRANCH_FROM,
				
				-- MPODR_STC
				--  #195757 - по требованию Макаровой Елены (Карелия)
				left(dLS.LpuSection_Code, 2) as BRANCH_TO,
				
				-- USL_OK
				case
					when @region <> 10 then null
					when dLU.LpuUnitType_SysNick like 'stac' then 1
					when dLU.LpuUnitType_SysNick like 'dstac' then 21
					when dLU.LpuUnitType_SysNick like 'pstac' then 22
					when dLU.LpuUnitType_SysNick like 'hstac' then 23
				end as CARETYPE,
				
				-- VPOLIS
				PT.PolisType_CodeF008 as POLICY_TYPE,
				
				-- SPOLIS
				nullif(Polis.Polis_Ser, '') as POLIS_SERIAL,
				
				-- NPOLIS
				case when PT.PolisType_CodeF008 = 3 
					then PS.Person_EdNum else Polis.Polis_Num
				end as POLIS_NUMBER,
				
				Smo.OrgSmo_f002smocod as SMO_CODE,
				left(SmoRgn.KLAdr_Ocatd, 5) as ST_OKATO,
				
				-- FAM
				rtrim(PS.Person_SurName) as LAST_NAME,
				-- IM
				rtrim(PS.Person_FirName) as FIRST_NAME,
				-- OT
				rtrim(PS.Person_SecName) as FATHER_NAME,
				
				Sex.Sex_fedid as W,
				
				-- DR
				convert(varchar(10), PS.Person_BirthDay, 120) as BIRTHDAY,
				
				-- TLF
				isnull(nullif(PS.PersonPhone_Phone, ''), 'не указан') as PHONE,
				
				-- DS
				D.Diag_Code as MKB,
				
				-- KOD_PFO
				LSP.LpuSectionProfile_Code as STRUCTURE_BED,
				
				-- KOD_PFK
				fLSBP.LpuSectionBedProfile_Code as BEDPROFIL,
				
				-- KOD_DCT
				(
					left(MP.Person_Snils, 3) + '-' + substring(MP.Person_Snils, 4, 3) + '-' + 
					substring(MP.Person_Snils, 7, 3) + ' ' + right(MP.Person_Snils, 2)
				) as DOC_CODE_14,
				
				-- DTA_PLN
				convert( varchar(10), coalesce(
					TTS.TimeTableStac_setDate, ED.EvnDirection_desDT, ED.EvnDirection_setDT
				), 120) as PLANNED_DATE					
				-- end select
			from
				-- from
				{$tmpTable} PL
				inner join v_EvnDirection ED with(nolock) on ED.EvnDirection_id = PL.ObjectID
				left join v_Lpu L with(nolock) on L.Lpu_id = ED.Lpu_id
				left join v_Lpu dL with(nolock) on dL.Lpu_id = ED.Lpu_did
				left join v_Evn_del EDel with(nolock) on EDel.Evn_id = ED.EvnDirection_id
				left join v_DirType DirType with(nolock) on DirType.DirType_id = ED.DirType_id
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = ED.LpuSection_id
				left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LS.LpuBuilding_id
				left join v_LpuSection dLS with(nolock) on dLS.LpuSection_id = ED.LpuSection_did
				left join v_LpuUnit dLU with(nolock) on dLU.LpuUnit_id = dLS.LpuUnit_id
				left join v_LpuSectionProfile LSP with(nolock) on LSP.LpuSectionProfile_id = isnull(dLS.LpuSectionProfile_id, ED.LpuSectionProfile_id)
				left join v_LpuSectionBedProfile LSBP with(nolock) on LSBP.LpuSectionBedProfile_id = dLS.LpuSectionBedProfile_id
				left join fed.LpuSectionBedProfileLink LSBPL with(nolock) on LSBPL.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
				left join fed.LpuSectionBedProfile fLSBP with(nolock) on fLSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
				left join v_Diag D with(nolock) on D.Diag_id = ED.Diag_id
				left join v_Person_all PS with(nolock) on PS.PersonEvn_id = ED.PersonEvn_id
				left join v_Polis Polis with(nolock) on Polis.Polis_id = PS.Polis_id
				left join v_PolisType PT with(nolock) on PT.PolisType_id = Polis.PolisType_id
				left join v_OrgSmo Smo with(nolock) on Smo.OrgSmo_id = Polis.OrgSmo_id
				left join v_KLArea SmoRgn with(nolock) on SmoRgn.KLArea_id = Smo.KLRgn_id
				left join v_Sex Sex with(nolock) on Sex.Sex_id = PS.Sex_id
				left join v_TimeTableStac TTS with(nolock) on TTS.TimeTableStac_id = ED.TimeTableStac_id
				outer apply (
					select top 1 MP.*
					from v_MedPersonal MP with(nolock)
					where MP.MedPersonal_id = ED.MedPersonal_id
				) MP					
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
		
		if ($returnType == 'count') {
			$countResult = $this->queryResult(getCountSQLPH($query), $params);
			return $countResult[0]['cnt'];
		} else {
			return $this->queryResult(getLimitSQLPH($query, $start, $limit), $params);
		}
	}*/

	/**
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	// убрали в задаче #197576
/*	function package_CANCEL_HOSPITALISATION_REFERRAL($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		if ($procDataType != 'Delete') {
			return parent::package_CANCEL_HOSPITALISATION_REFERRAL($tmpTable, $procDataType, $data, $returnType, $start, $limit);
		}
		
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
				PL.ID as CHR_ID,
				PL.GUID,
				'Delete' as OPERATIONTYPE,
				
				-- {DTA_NAP}
				convert(varchar(10), @date, 120) as DATA,
				
				-- {NOM_NAP}
				(
					cast(L.Lpu_f003mcod as varchar)+
					cast(year(ED.EvnDirection_setDT) as varchar)+
					right('000000'+cast(ED.EvnDirection_Num as varchar), 6)
				) as NOM_NAP,				
				
				-- {MCOD_NAP}
				L.Lpu_f003mcod as REFERRAL_LPU,
				
				-- {IST_ANL}
				case when dbo.getRegion() = 10
					then 2 else 1
				end as CANCEL_SOURSE,
					
				-- {ACOD}
				fL.Lpu_f003mcod as CANCEL_CODE,
				
				-- {MPODR_ANL}
				coalesce(fBuildingCode.Value, fLB.LpuBuilding_Code) as BRANCH,
				
				-- {PR_ANL}
				case 
					when ESC.EvnStatusCause_Code = 18 then 0
					when ESC.EvnStatusCause_Code = 22 then 1
					when ESC.EvnStatusCause_Code = 1 then 2
					when ESC.EvnStatusCause_Code = 5 then 3
					else 5
				end as REASON
				-- end select
			from
				-- from
				{$tmpTable} PL
				inner join v_EvnDirection ED with(nolock) on ED.EvnDirection_id = PL.ObjectID
				left join v_Lpu L with(nolock) on L.Lpu_id = ED.Lpu_id
				left join v_Lpu dL with(nolock) on dL.Lpu_id = ED.Lpu_did
				left join v_MedStaffFact fMSF with(nolock) on fMSF.MedStaffFact_id = isnull(ED.MedStaffFact_fid, ED.MedStaffFact_id)
				left join v_Lpu fL with(nolock) on fL.Lpu_id = coalesce(fMSF.Lpu_id, ED.Lpu_cid, ED.Lpu_id)
				left join v_LpuSection fLS with(nolock) on fLS.LpuSection_id = fMSF.LpuSection_id
				left join v_LpuBuilding fLB with(nolock) on fLB.LpuBuilding_id = fMSF.LpuBuilding_id
				outer apply (
					select top 1 ASV.AttributeSignValue_id
					from v_AttributeSignValue ASV with(nolock)
					inner join v_AttributeSign ASign with(nolock) on ASign.AttributeSign_id = ASV.AttributeSign_id
					where ASign.AttributeSign_Code = 1
					and ASV.AttributeSignValue_TablePKey = fLS.LpuSection_id
					and @date between ASV.AttributeSignValue_begDate and isnull(ASV.AttributeSignValue_endDate, @date)
					order by ASV.AttributeSignValue_begDate desc, ASV.AttributeSignValue_insDT desc
				) fASV
				outer apply (
					select top 1 AV.AttributeValue_ValueString as Value
					from v_AttributeValue AV with(nolock)
					inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
					where AV.AttributeSignValue_id = fASV.AttributeSignValue_id
					and A.Attribute_SysNick like 'Building_Code'
				) fBuildingCode
				outer apply (
					select top 1 ESH.*
					from v_EvnStatusHistory ESH with(nolock)
					where ESH.Evn_id = ED.EvnDirection_id
					order by ESH.EvnStatusHistory_begDate desc
				) ESH
				outer apply (
					select top 1 ESCL.EvnStatusCause_id
					from v_EvnStatusCauseLink ESCL with(nolock)
					left join v_EvnStatusCause ESC with(nolock) on ESC.EvnStatusCause_id = ESCL.EvnStatusCause_id
					where ESCL.EvnStatusCauseLink_id = ED.DirFailType_id
					and ESC.EvnStatusCause_Code in (1,5,18,22)
					order by ESCL.EvnStatusCauseLink_id
				) ESCL
				left join v_EvnStatusCause ESC with(nolock) on ESC.EvnStatusCause_id = isnull(ESH.EvnStatusCause_id, ESCL.EvnStatusCause_id)
				-- end from
			where
				-- where
				PL.PACKAGE_TYPE = 'CANCEL_HOSPITALISATION_REFERRAL'
				and ED.DirType_id in (1,5)
				and ED.EvnDirection_failDT is null
				and ED.EvnDirection_updDT > PL.DATE
				and nullif(nullif(PL.LPU,'0'),'') is not null
				{$filters_del}
				-- end where
			order by
				-- order by
				PL.Lpu_id
				-- end order by
		";
		
		if ($returnType == 'count') {
			$countResult = $this->queryResult(getCountSQLPH($query), $params);
			return $countResult[0]['cnt'];
		} else {
			return $this->queryResult(getLimitSQLPH($query, $start, $limit), $params);
		}
	}*/

	/**
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	// убрали в задаче #197576
	/*function package_MOTION_IN_HOSPITAL($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		if ($procDataType != 'Delete') {
			return parent::package_MOTION_IN_HOSPITAL($tmpTable, $procDataType, $data, $returnType, $start, $limit);
		}
		
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$filters = "";
		$filters_del = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and ES.EvnSection_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		} else {
			if (!empty($this->init_date)) {
				$filters .= " and ES.EvnSection_insDT >= :InitDate";
				$params['InitDate'] = $this->init_date;
			}
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}

		$query = "
			-- variables
			declare @dt datetime = dbo.tzGetDate();
			declare @date date = cast(@dt as date);
			declare @region int = dbo.getRegion();
			-- end variables
			select
				-- select
				PL.Lpu_id,
				
				-- {MCOD_STC}
				PL.LPU as CODE_MO,
				PL.ObjectID,
				PL.ID as MIH_ID,
				PL.GUID,
				'Delete' as OPERATIONTYPE,
				convert(varchar(10), @date, 120) as DATA,
				
				-- {NOM_NAP}
				(
					cast(sL.Lpu_f003mcod as varchar)+
					cast(year(isnull(ED.EvnDirection_setDT, EPS.EvnDirection_setDT)) as varchar)+
					right('000000'+cast(isnull(ED.EvnDirection_Num, EPS.EvnDirection_Num) as varchar), 6)
				) as NOM_NAP,
				
				-- {DTA_NAP}
				convert(varchar(10), isnull(ED.EvnDirection_setDT, EPS.EvnDirection_setDT), 120) as REFERRAL_DATE,
								
				-- {FRM_MP}
				case 
					when @region = 10 and PT.PrehospType_Code = 1 then 1
					when @region = 10 then 2
					when PT.PrehospType_Code = 1 then 0 
					else 1
				end as HOSPITALISATION_TYPE,
					
				
				-- {MPODR_STC}
				LB.LpuBuilding_Code as BRANCH,
				
				-- {DTA_FKT}
				convert(varchar(10), EPS.EvnPS_setDT, 120) as HOSPITALISATION_DATE,
				
				-- {DTA_END}
				convert(varchar(10), ES.EvnSection_disDT, 120) as DATE_OUT,
				
				-- {SMO_CODE}
				Smo.Orgsmo_f002smocod as SMO,
				
				-- {FAM}
				PS.Person_SurName as LAST_NAME,
				
				-- {IM}
				PS.Person_FirName as FIRST_NAME,
				
				-- {OT}
				PS.Person_SecName as FATHER_NAME,
				
				-- {W}
				Sex.Sex_fedid as W,
				
				-- {DR}
				convert(varchar(10), PS.Person_BirthDay, 120) as BIRTHDAY,
				
				-- {VPOLIS}
				PolisType.PolisType_CodeF008 as POLICY_TYPE,
				
				-- {SPOLIS}
				nullif(Polis.Polis_Ser, '') as POLIS_SERIAL,
				
				-- {NPOLIS}
				case when PolisType.PolisType_CodeF008 = 3
					then PS.Person_EdNum else Polis.Polis_Num
				end as POLIS_NUMBER,
					
				-- {USL_OK}
				case 
					when dbo.getRegion() <> 10 then null
					when LU.LpuUnitType_SysNick like 'stac' then 1
					when LU.LpuUnitType_SysNick like 'dstac' then 21
					when LU.LpuUnitType_SysNick like 'pstac' then 22
					when LU.LpuUnitType_SysNick like 'hstac' then 23
				end as CARETYPE,
					
				-- {KOD_PFO}
				LSP.LpuSectionProfile_Code as STRUCTURE_BED,
				
				-- {KOD_PFK}
				fLSBP.LpuSectionBedProfile_Code as BEDPROFIL,
				
				-- {NHISTORY}
				EPS.EvnPS_NumCard as MED_CARD_NUMBER
				-- end select
			from
				-- from
				{$tmpTable} PL
				inner join v_EvnSection ES with(nolock) on ES.EvnSection_id = PL.ObjectID
				left join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = ES.EvnSection_pid
				left join v_Lpu L with(nolock) on L.Lpu_id = ES.Lpu_id
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = ES.LpuSection_id
				left join v_LpuSectionProfile LSP with(nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
				left join v_LpuSectionBedProfile LSBP with(nolock) on LSBP.LpuSectionBedProfile_id = LS.LpuSectionBedProfile_id
				left join fed.LpuSectionBedProfileLink LSBPL with(nolock) on LSBPL.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
				left join fed.LpuSectionBedProfile fLSBP with(nolock) on fLSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
				left join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LS.LpuBuilding_id
				left join v_PrehospType PT with(nolock) on PT.PrehospType_id = EPS.PrehospType_id
				left join v_Person_all PS with(nolock) on PS.PersonEvn_id = EPS.PersonEvn_id
				left join v_Sex Sex with(nolock) on Sex.Sex_id = PS.Sex_id
				left join v_Polis Polis with(nolock) on Polis.Polis_id = PS.Polis_id
				left join v_PolisType PolisType with(nolock) on PolisType.PolisType_id = Polis.PolisType_id
				left join v_OrgSmo Smo with(nolock) on Smo.OrgSmo_id = Polis.OrgSmo_id
				left join v_EvnDirection ED with(nolock) on ED.EvnDirection_id = EPS.EvnDirection_id
				left join v_LpuSection sLS with(nolock) on sLS.LpuSection_id = isnull(ED.LpuSection_id, EPS.LpuSection_did)
				left join v_Lpu sL with(nolock) on sL.Lpu_id = coalesce(ED.Lpu_sid, EPS.Lpu_did, sLS.Lpu_id)
				outer apply (
					select top 1 LSBS.*
					from v_LpuSectionBedState LSBS with(nolock)
					where LSBS.LpuSection_id = EPS.LpuSection_id
					and EPS.EvnPS_setDate between LSBS.LpuSectionBedState_begDate and isnull(LSBS.LpuSectionBedState_endDate, EPS.EvnPS_setDate)
					order by LSBS.LpuSectionBedState_begDate desc
				) LSBS
				-- end from
			where
				-- where
				PL.PACKAGE_TYPE = 'MOTION_IN_HOSPITAL'
				and ES.EvnSection_id is null
				and nullif(nullif(PL.LPU,'0'),'') is not null
				{$filters_del}
				-- end where
			order by
				-- order by
				PL.Lpu_id
				-- end order by
		";
		

		if ($returnType == 'count') {
			$countResult = $this->queryResult(getCountSQLPH($query), $params);
			return $countResult[0]['cnt'];
		} else {
			return $this->queryResult(getLimitSQLPH($query, $start, $limit), $params);
		}
	}*/

	/**
	 * @param string $tmpTable
	 * @param string $procDataType
	 * @param array $data
	 * @param string $returnType
	 * @param int $start
	 * @param int $limit
	 * @return array|false
	 */
	// убрали в задаче #197576
	/*
	function package_EXTRHOSPITALISATION($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		if ($procDataType != 'Delete') {
			return parent::package_EXTRHOSPITALISATION($tmpTable, $procDataType, $data, $returnType, $start, $limit);
		}		
		
		$params = array(
			'ProcDataType' => $procDataType,
		);

		$filters = "";
		$filters_del = "";
		if (!empty($data['exportId'])) {
			$exportIds_str = implode(',', $data['exportId']);
			$filters .= " and EPS.EvnPS_id in ({$exportIds_str})";
			$filters_del .= " and PL.ObjectID in ({$exportIds_str})";
		} else {
			if (!empty($this->init_date)) {
				$filters .= " and EPS.EvnPS_insDT >= :InitDate";
				$params['InitDate'] = $this->init_date;
			}
			if (count($this->allowed_lpus) > 0) {
				$allowed_lpus_str = implode(",", $this->allowed_lpus);
				$filters .= " and L.Lpu_id in ({$allowed_lpus_str})";
			}
		}

		$query = "
			-- variables
			declare @dt datetime = dbo.tzGetDate();
			declare @date date = cast(@dt as date);
			-- end variables
			select
				-- select
				PL.Lpu_id,
				PL.LPU as CODE_MO,
				PL.ObjectID,
				PL.ID as H_ID,
				PL.GUID,
				'Delete' as OPERATIONTYPE,
				convert(varchar(10), @date, 120) as DATA,
				
				-- {NOM_NAP}
				(
					cast(sL.Lpu_f003mcod as varchar)+
					cast(year(isnull(ED.EvnDirection_setDT, EPS.EvnDirection_setDT)) as varchar)+
					right('000000'+cast(isnull(ED.EvnDirection_Num, EPS.EvnDirection_Num) as varchar), 6)
				) as NOM_NAP,
				
				-- {DTA_NAP}
				convert(varchar(10), isnull(ED.EvnDirection_setDT, EPS.EvnDirection_setDT), 120) as REFERRAL_DATE,

				-- {MCOD_STC}
				L.Lpu_f003mcod as MO,
				
				-- {MPODR_STC}
				LB.LpuBuilding_Code as BRANCH,
				
				-- {DTA_FKT}
				convert(varchar(10), EPS.EvnPS_setDT, 120) as HOSPITALISATION_DATE,
				
				-- {TIM_FKT}
				(
					right('0'+cast(datepart(hour, EPS.EvnPS_setDT) as varchar), 2)+'-'+
					right('0'+cast(datepart(minute, EPS.EvnPS_setDT) as varchar), 2)
				) as HOSPITALISATION_TIME_STR,
					
				-- {VPOLIS}
				PolisType.PolisType_CodeF008 as POLICY_TYPE,
				
				-- {SPOLIS}
				nullif(Polis.Polis_Ser, '') as POLIS_SERIAL,
				
				-- {NPOLIS}
				case when PolisType.PolisType_CodeF008 = 3
					then PS.Person_EdNum else Polis.Polis_Num
				end as POLIS_NUMBER,
				
				-- {SMO_CODE}
				Smo.Orgsmo_f002smocod as SMO,
				
				-- {ST_OKATO}
				left(SmoRgn.KLAdr_Ocatd, 5) as ST_OKATO,
				
				-- {FAM}
				PS.Person_SurName as LAST_NAME,
				
				-- {IM}
				PS.Person_FirName as FIRST_NAME,
				
				-- {OT}
				PS.Person_SecName as FATHER_NAME,
				
				-- {W}
				Sex.Sex_fedid as W,
				
				-- {DR}
				convert(varchar(10), PS.Person_BirthDay, 120) as BIRTHDAY,
				
				-- {USL_OK}
				case
					when dbo.getRegion() <> 10 then null
					when LU.LpuUnitType_SysNick like 'stac' then 1
					when LU.LpuUnitType_SysNick like 'dstac' then 21
					when LU.LpuUnitType_SysNick like 'pstac' then 22
					when LU.LpuUnitType_SysNick like 'hstac' then 23
				end as CARETYPE,
					
				-- {KOD_PFO}
				LSP.LpuSectionProfile_Code as STRUCTURE_BED,
				
				-- {KOD_PFK}
				fLSBP.LpuSectionBedProfile_Code as BEDPROFIL,
				
				-- {NHISTORY}
				EPS.EvnPS_NumCard as MED_CARD_NUMBER,
				
				-- {DS}
				D.Diag_Code as MKB				
				-- end select
			from
				-- from
				{$tmpTable} PL
				inner join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = PL.ObjectID
				left join v_Lpu L with(nolock) on L.Lpu_id = EPS.Lpu_id
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = EPS.LpuSection_id
				left join v_LpuSectionProfile LSP with(nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
				left join v_LpuSectionBedProfile LSBP with(nolock) on LSBP.LpuSectionBedProfile_id = LS.LpuSectionBedProfile_id
				left join fed.LpuSectionBedProfileLink LSBPL with(nolock) on LSBPL.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
				left join fed.LpuSectionBedProfile fLSBP with(nolock) on fLSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
				left join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LS.LpuBuilding_id
				left join v_PrehospType PT with(nolock) on PT.PrehospType_id = EPS.PrehospType_id
				left join v_Diag D with(nolock) on D.Diag_id = EPS.Diag_id
				left join v_Person_all PS with(nolock) on PS.PersonEvn_id = EPS.PersonEvn_id
				left join v_Sex Sex with(nolock) on Sex.Sex_id = PS.Sex_id
				left join v_Polis Polis with(nolock) on Polis.Polis_id = PS.Polis_id
				left join v_PolisType PolisType with(nolock) on PolisType.PolisType_id = Polis.PolisType_id
				left join v_OrgSmo Smo with(nolock) on Smo.OrgSmo_id = Polis.OrgSmo_id
				left join v_KLArea SmoRgn with(nolock) on SmoRgn.KLArea_id = Smo.KLRgn_id
				left join v_EvnDirection ED with(nolock) on  ED.EvnDirection_id = EPS.EvnDirection_id
				left join v_LpuSection sLS with(nolock) on sLS.LpuSection_id = isnull(ED.LpuSection_id, EPS.LpuSection_did)
				left join v_Lpu sL with(nolock) on sL.Lpu_id = coalesce(ED.Lpu_sid, EPS.Lpu_did, sLS.Lpu_id)
				left join v_LpuBuilding sLB with(nolock) on sLB.LpuBuilding_id = sLS.LpuBuilding_id
				outer apply (
					select top 1 LSBS.*
					from v_LpuSectionBedState LSBS with(nolock)
					where LSBS.LpuSection_id = EPS.LpuSection_id
					and EPS.EvnPS_setDate between LSBS.LpuSectionBedState_begDate and isnull(LSBS.LpuSectionBedState_endDate, EPS.EvnPS_setDate)
					order by LSBS.LpuSectionBedState_begDate desc
				) LSBS
				-- end from
			where
				-- where
				PL.PACKAGE_TYPE = 'EXTRHOSPITALISATION'
				and EPS.EvnPS_id is null
				and nullif(nullif(PL.LPU,'0'),'') is not null
				{$filters_del}
				-- end where
			order by
				-- order by
				PL.Lpu_id
				-- end order by
		";

		if ($returnType == 'count') {
			$countResult = $this->queryResult(getCountSQLPH($query), $params);
			return $countResult[0]['cnt'];
		} else {
			return $this->queryResult(getLimitSQLPH($query, $start, $limit), $params);
		}
	}
	*/
	
}
