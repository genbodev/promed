<?php
defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'models/_pgsql/TFOMSAutoInteract_model.php');

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
	 * Kareliya_TFOMSAutoInteract_model constructor.
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
	function package_HOSPITALISATION($tmpTable, $procDataType, $data, $returnType = 'data', $start = 0, $limit = 500) {
		if ($procDataType != 'Delete') {
			return parent::package_HOSPITALISATION($tmpTable, $procDataType, $data, $returnType, $start, $limit);
		}
		
		$params = array(
			'ProcDataType' => $procDataType,
			'date' => $this->currentDT->format('Y-m-d'),
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
		
		$query = "
			select
				-- select
				L.Lpu_id as \"Lpu_id\",
				L.Lpu_f003mcod as \"CODE_MO\",
				EPS.EvnPS_id as \"ObjectID\",
				EPS.EvnPS_id as \"H_ID\",
				coalesce(HOSPITALISATION.GUID, newid()) as \"GUID\",
				'Delete' as \"OPERATIONTYPE\",
				to_char(:date, 'YYYY-MM-DD') as \"DATA\",
				coalesce(ED.EvnDirection_Num, EPS.EvnDirection_Num) as \"REFERRAL_NUMBER\",
				(
					cast(sL.Lpu_f003mcod as varchar)||
					cast(date_part('year', coalesce(ED.EvnDirection_setDT, EPS.EvnDirection_setDT)) as varchar)||
					right('000000'||cast(coalesce(ED.EvnDirection_Num, EPS.EvnDirection_Num) as varchar), 6)
				) as \"NOM_NAP\",
				to_char(coalesce(ED.EvnDirection_setDT, EPS.EvnDirection_setDT), 'YYYY-MM-DD') as \"REFERRAL_DATE\",
				sL.Lpu_f003mcod as \"REFERRAL_MO\",
				sLB.LpuBuilding_Code as \"REFERRAL_BRANCH\",
				L.Lpu_f003mcod as \"MO\",
				LB.LpuBuilding_Code as \"BRANCH\",
				LS.LpuSection_Code as \"DIVISION\",
				case 
					when getRegion() = 10 and PT.PrehospType_Code = 1 then 1
					when getRegion() = 10 then 2
					when PT.PrehospType_Code = 1 then 0 
					else 1 
				end as \"FORM_MEDICAL_CARE\",
				to_char(EPS.EvnPS_setDT, 'YYYY-MM-DD') as \"HOSPITALISATION_DATE\",
				to_char(varchar(19), EPS.EvnPS_setDT, 'YYYY-MM-DDTHH24:MI:SS') as \"HOSPITALISATION_TIME\",
				(
					right('0'||cast(date_part('hour', EPS.EvnPS_setDT) as varchar), 2)||'-'||
					right('0'||cast(date_part('minute', EPS.EvnPS_setDT) as varchar), 2)
				) as \"HOSPITALISATION_TIME_STR\",
				PolisType.PolisType_CodeF008 as \"POLICY_TYPE\",
				nullif(Polis.Polis_Ser, '') as \"POLIS_SERIAL\",
				case when PolisType.PolisType_CodeF008 = 3
					then PS.Person_EdNum else Polis.Polis_Num
				end as \"POLIS_NUMBER\",
				Smo.Orgsmo_f002smocod as \"SMO\",
				PS.Person_SurName as \"LAST_NAME\",
				PS.Person_FirName as \"FIRST_NAME\",
				PS.Person_SecName as \"FATHER_NAME\",
				case when Sex.Sex_fedid = 1 then 10301 else 10302 end as \"SEX\",
				Sex.Sex_fedid as \"W\",
				to_char(PS.Person_BirthDay, 'YYYY-MM-DD') as \"BIRTHDAY\",
				LSP.LpuSectionProfile_Code as \"STRUCTURE_BED\",
				fLSBP.LpuSectionBedProfile_Code as \"BEDPROFIL\",
				LSBS.LpuSectionBedState_id as \"DLSB\",
				case
					when dbo.getRegion() <> 10 then null
					when LU.LpuUnitType_SysNick like 'stac' then 1
					when LU.LpuUnitType_SysNick like 'dstac' then 21
					when LU.LpuUnitType_SysNick like 'pstac' then 22
					when LU.LpuUnitType_SysNick like 'hstac' then 23
				end as \"CARETYPE\",
				EPS.EvnPS_NumCard as \"MED_CARD_NUMBER\",
				D.Diag_Code as \"MKB\",
				D.Diag_Name as \"DIAGNOSIS\",
				EPS.Person_id as \"PATIENT\"
				-- end select
			from
				-- from
				{$tmpTable} HOSPITALISATION
				inner join v_EvnPS_del EPS on EPS.EvnPS_id = HOSPITALISATION.ObjectID
				inner join v_Lpu L on L.Lpu_id = EPS.Lpu_id
				inner join v_LpuSection LS on LS.LpuSection_id = EPS.LpuSection_id
				inner join v_LpuSectionProfile LSP on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
				inner join v_LpuSectionBedProfile LSBP on LSBP.LpuSectionBedProfile_id = LS.LpuSectionBedProfile_id
				inner join fed.LpuSectionBedProfileLink LSBPL on LSBPL.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
				inner join fed.LpuSectionBedProfile fLSBP on fLSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
				inner join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
				inner join v_LpuBuilding LB on LB.LpuBuilding_id = LS.LpuBuilding_id
				inner join v_PrehospType PT on PT.PrehospType_id = EPS.PrehospType_id
				inner join v_Diag D on D.Diag_id = EPS.Diag_id
				inner join v_Person_all PS on PS.PersonEvn_id = EPS.PersonEvn_id
				left join v_Sex Sex on Sex.Sex_id = PS.Sex_id
				left join v_Polis Polis on Polis.Polis_id = PS.Polis_id
				left join v_PolisType PolisType on PolisType.PolisType_id = Polis.PolisType_id
				left join v_OrgSmo Smo on Smo.OrgSmo_id = Polis.OrgSmo_id
				left join v_EvnDirection ED on  ED.EvnDirection_id = EPS.EvnDirection_id
				left join v_LpuSection sLS on sLS.LpuSection_id = coalesce(ED.LpuSection_id, EPS.LpuSection_did)
				left join v_Lpu sL on sL.Lpu_id = coalesce(ED.Lpu_sid, EPS.Lpu_did, sLS.Lpu_id)
				left join v_LpuBuilding sLB on sLB.LpuBuilding_id = sLS.LpuBuilding_id
				left join lateral (
					select LSBS.*
					from v_LpuSectionBedState LSBS
					where LSBS.LpuSection_id = EPS.LpuSection_id
					and EPS.EvnPS_setDate between LSBS.LpuSectionBedState_begDate and coalesce(LSBS.LpuSectionBedState_endDate, EPS.EvnPS_setDate)
					order by LSBS.LpuSectionBedState_begDate desc
					limit 1
				) LSBS on true
				-- end from
			where
				-- where
				HOSPITALISATION.PACKAGE_TYPE = 'HOSPITALISATION'
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
	}

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
			'date' => $this->currentDT->format('Y-m-d'),
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

        if ($procDataType == 'Insert'){
			$filters .= "
				and exists (
					select
						dsd.DispSickDiag_id
					from
						r10.v_DispSickDiag dsd
					where
						dsd.Diag_id = PD.Diag_id
						and coalesce(dsd.DispSickDiag_begDT, dbo.tzGetDate()) <= dbo.tzGetDate()
						and coalesce(dsd.DispSickDiag_endDT, dbo.tzGetDate()) >= dbo.tzGetDate()
					limit 1
				)
				and D.Diag_Code between 'C00' and 'C97'
			";
		}

		if ($procDataType == 'Delete') {
			$query = "
				select
					-- select
					PL.Lpu_id as \"Lpu_id\",
					PL.LPU as \"CODE_MO\",
					PL.ObjectID as \"ObjectID\",
					PL.ID as \"ID\",
					PL.GUID as \"GUID\",
					'Delete' as \"TYPE\",
					:date as \"DATA\"
					-- end select
				from
					-- from
					{$tmpTable} PL
					left join v_PersonDISP PD on PD.PersonDisp_id = PL.ObjectID
					-- end from
				where
					-- where
					PL.PACKAGE_TYPE = 'DISP'
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
				select
					-- select
					L.Lpu_id as \"Lpu_id\",
					L.Lpu_f003mcod as \"CODE_MO\",
					PD.PersonDisp_id as \"ObjectID\",
					PD.PersonDisp_id as \"ID\",
					coalesce(DISP.GUID, newid()) as \"GUID\",
					ProcDataType.Value as \"TYPE\",
					:date as \"DATA\",
					P.Person_id as \"ID_PAC\",
					rtrim(PS.Person_SurName) as \"FAM\",
					rtrim(PS.Person_FirName) as \"IM\",
					rtrim(PS.Person_SecName) as \"OT\",
					Sex.Sex_fedid as \"W\",
					to_char(PS.Person_BirthDay, 'YYYY-MM-DD') as \"DR\",
					PT.PolisType_CodeF008 as \"VPOLIS\",
					Polis.Polis_Ser as \"SPOLIS\",
					case
						when PT.PolisType_CodeF008 = 3
						then PS.Person_EdNum 
						else Polis.Polis_Num
					end  as \"NPOLIS\",
					coalesce(nullif(PS.Person_Phone, ''), 'не указан') as \"PHONE\",
					to_char(PD.PersonDisp_begDate, 'YYYY-MM-DD') as \"DATE_IN\",
					case when RIGHT(D.Diag_Code, 1) = '.' then LEFT(D.Diag_Code, length(D.Diag_Code)-1) else D.Diag_Code end AS \"DS_DISP\",
					MPPS.Person_Snils as \"SNILS_VR\",
					null as \"KRAT\",
					null as \"DN_MONTH1\",
					null as \"DN_MONTH2\",
					null as \"DN_MONTH3\",
					null as \"DN_MONTH4\",
					null as \"DN_MONTH5\",
					null as \"DN_MONTH6\",
					null as \"DN_MONTH7\",
					null as \"DN_MONTH8\",
					null as \"DN_MONTH9\",
					null as \"DN_MONTH10\",
					null as \"DN_MONTH11\",
					null as \"DN_MONTH12\",
					1 as \"DN_PLACE\"
					-- end select
				from
					-- from
					v_PersonDisp PD
					inner join v_Lpu L on L.Lpu_id = PD.Lpu_id
					inner join Person P on P.Person_id = PD.Person_id
					inner join v_PersonState PS on PS.Person_id = P.Person_id
					left join v_Sex Sex on Sex.Sex_id = PS.Sex_id
					left join v_Polis Polis on Polis.Polis_id = PS.Polis_id
					left join v_PolisType PT on PT.PolisType_id = Polis.PolisType_id
					left join v_Document Document on Document.Document_id = PS.Document_id
					left join v_DocumentType DT on DT.DocumentType_id = Document.DocumentType_id
					left join v_Diag D on D.Diag_id = PD.Diag_id
					left join v_DiagDetectType Detect on Detect.DiagDetectType_id = PD.DiagDetectType_id
					left join v_DeseaseDispType DetectType on DetectType.DeseaseDispType_id = PD.DeseaseDispType_id
					left join v_DispOutType DOT on DOT.DispOutType_id = PD.DispOutType_id
					left join lateral (
						select MP.*
						from v_MedPersonal MP
						where MP.MedPersonal_id = PD.MedPersonal_id
						limit 1
					) MP on true
					left join v_PersonState MPPS on MPPS.Person_id = MP.Person_id
					left join {$tmpTable} DISP on DISP.PACKAGE_TYPE = 'DISP' and DISP.ObjectID = PD.PersonDisp_id
					left join lateral (
						select case
							when DISP.ID is null then 'Insert'
							when DISP.ID is not null and DISP.DATE <= PD.PersonDisp_updDT then 'Update'
						end as Value
					) ProcDataType on true
					-- end from 
				where
					-- where
					ProcDataType.Value = :ProcDataType
					and nullif(nullif(L.Lpu_f003mcod,'0'),'') is not null
					and PD.PersonDisp_endDate is null
					and exists(
						select * from v_PersonDispVizit PDV
						where PDV.PersonDisp_id = PD.PersonDisp_id
						and PDV.PersonDispVizit_NextDate is not null
						and PDV.PersonDispVizit_NextFactDate is null
					)
					and (
						dbo.Age2(PS.Person_BirthDay, :date) >= 18 or
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
			if (!is_array($resp)) return false;

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
						PDV.PersonDisp_id as \"ObjectID\",
						date_part('month', PDV.PersonDispVizit_NextDate) as \"DN_MONTH\"
					from
						v_PersonDispVizit PDV
					where
						PDV.PersonDisp_id in ({$ids_str})
						and PDV.PersonDispVizit_NextDate is not null
						and PDV.PersonDispVizit_NextFactDate is null
					order by
						PDV.PersonDisp_id,
						PDV.PersonDispVizit_NextDate
				";
				$resp = $this->queryResult($query);
				if (!is_array($resp)) return false;

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
}