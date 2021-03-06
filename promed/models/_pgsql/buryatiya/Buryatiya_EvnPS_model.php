<?php	defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'models/_pgsql/EvnPS_model.php');

class Buryatiya_EvnPS_model extends EvnPS_model {
	/**
	 * Msk_EvnPS_model constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @return array
	 */
	protected function _getSaveInputRules() {
		$all = parent::_getSaveInputRules();

		return $all;
	}

	/**
	 * @return array
	 * @description Возвращает список всех используемых ключей атрибутов объекта
	 */
	static function defAttributes() {
		$arr = parent::defAttributes();

		return $arr;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	public function getEvnPSFields($data = []) {
		$query = "
			select
				 coalesce(EPS.EvnPS_NumCard, '') as \"EvnPS_NumCard\"
				,RTRIM(coalesce(Lpu.Lpu_Name, '')) as \"Lpu_Name\"
				,RTRIM(coalesce(PLST.PolisType_Name, '')) as \"PolisType_Name\"
				,CASE WHEN PLST.PolisType_Code = 4 then '' ELSE RTRIM(coalesce(PLS.Polis_Ser, '')) END as \"Polis_Ser\"
				,CASE WHEN PLST.PolisType_Code = 4 then coalesce(RTRIM(PS.Person_EdNum), '') ELSE RTRIM(coalesce(PLS.Polis_Num, '')) END as \"Polis_Num\"
				,coalesce('код терр. ' || cast(OST.OMSSprTerr_Code as varchar(5)), '') as \"OMSSprTerr_Code\"
				,coalesce('выдан ' || OrgSmo.OrgSMO_Nick, '') as \"OrgSmo_Name\"
				,coalesce(OS.Org_OKATO, '') as \"OrgSmo_OKATO\"
				,PS.Person_id as \"Person_id\"
				,RTRIM(RTRIM(coalesce(PS.Person_Surname, '')) || ' ' || RTRIM(coalesce(PS.Person_Firname, '')) || ' ' || RTRIM(coalesce(PS.Person_Secname, ''))) as \"Person_Fio\"
				,LEFT(coalesce(SX.Sex_Name, ''), 3) as \"Sex_Name\"
				,to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\"
				,dbo.Age2(PS.Person_Birthday, EPS.EvnPS_setDT) as \"Person_AgeYears\"
				,RTRIM(coalesce(D.Document_Num, '')) as \"Document_Num\"
				,RTRIM(coalesce(D.Document_Ser, '')) as \"Document_Ser\"
				,RTRIM(coalesce(DT.DocumentType_Name, '')) as \"DocumentType_Name\"
				,RTRIM(coalesce(KLAT.KLAreaType_Name, '')) as \"KLAreaType_Name\"
				,RTRIM(coalesce(KLAT.KLAreaType_id, '')) as \"KLAreaType_id\"
				,RTRIM(coalesce(PS.Person_Phone, '')) as \"Person_Phone\"
				,RTRIM(coalesce(PAddr.Address_Address, '')) as \"PAddress_Name\"
				,RTRIM(coalesce(UAddr.Address_Address, '')) as \"UAddress_Name\"
				,coalesce(MSF.MedPersonal_TabCode, '') as \"MedPersonalPriem_Code\"
				,coalesce(MSF.Person_Fio, '') as \"MedPersonalPriem_FIO\"
				,RTRIM(coalesce(PT.PayType_Name, '')) as \"PayType_Name\"
				,RTRIM(coalesce(PT.PayType_Code, '')) as \"PayType_Code\"
				,RTRIM(coalesce(SS.SocStatus_Name, '')) as \"SocStatus_Name\"
				,RTRIM(coalesce(SS.SocStatus_Code, '')) as \"SocStatus_Code\"
				,RTRIM(coalesce(SS.SocStatus_SysNick, '')) as \"SocStatus_SysNick\"
				,IT.PrivilegeType_id as \"PrivilegeType_id\"
				,coalesce(IT2.PrivilegeType_Code, '') as \"PrivilegeType_Code\"
				,CASE
					WHEN street.KLStreet_id is not null and street.KLAdr_Ocatd is not null THEN street.KLAdr_Ocatd
					WHEN town.KLArea_id is not null and town.KLAdr_Ocatd is not null THEN town.KLAdr_Ocatd
					WHEN city.KLArea_id is not null and city.KLAdr_Ocatd is not null THEN city.KLAdr_Ocatd
					WHEN srgn.KLArea_id is not null and srgn.KLAdr_Ocatd is not null THEN srgn.KLAdr_Ocatd
					WHEN rgn.KLArea_id is not null and rgn.KLAdr_Ocatd is not null THEN rgn.KLAdr_Ocatd
					WHEN country.KLArea_id is not null and country.KLAdr_Ocatd is not null THEN country.KLAdr_Ocatd
					ELSE ''
				END as \"Person_OKATO\"
				,RTRIM(COALESCE(PHLS.LpuSection_Name, PreHospLpu.Lpu_Name, PHOM.OrgMilitary_Name, PHO.Org_Name,PD.PrehospDirect_Name, '')) as \"PrehospOrg_Name\"
				,RTRIM(coalesce(PA.PrehospArrive_Name, '')) as \"PrehospArrive_Name\"
				,coalesce(DiagH.Diag_Code, '') as \"PrehospDiag_Code\"
				,coalesce(DiagH.Diag_Name, '') as \"PrehospDiag_Name\"
				,coalesce(DiagP.Diag_Code, '') as \"AdmitDiag_Code\"
				,RTRIM(coalesce(PHTX.PrehospToxic_Name, '')) as \"PrehospToxic_Name\"
				,RTRIM(coalesce(PHTX.PrehospToxic_Code, '')) as \"PrehospToxic_Code\"
				,RTRIM(coalesce(LSTT.LpuSectionTransType_Name, '')) as \"LpuSectionTransType_Name\"
				,RTRIM(coalesce(LSTT.LpuSectionTransType_Code, '')) as \"LpuSectionTransType_Code\"
				,case
					when PHT.PrehospType_Code is null then null
					when PHT.PrehospType_Code = 1 then 4
					else 3
				 end as \"PrehospType_Code\"
				,case
					when PHT.PrehospType_Code is null then ''
					when PHT.PrehospType_Code = 1 then 'в плановом порядке'
					else 'по экстренным показаниям'
				 end as \"PrehospType_Name\"
				,case when coalesce(EPS.EvnPS_HospCount, 1) = 1 then 1 else 2 end as \"EvnPS_HospCountCode\"
				,case when coalesce(EPS.EvnPS_HospCount, 1) = 1 then 'первично' else 'повторно' end as \"EvnPS_HospCountName\"
				,EPS.EvnPS_TimeDesease as \"EvnPS_TimeDesease\"
				,coalesce(ED.EvnDirection_Num, EPS.EvnDirection_Num) as \"EvnDirection_Num \"
				,to_char(coalesce(ED.EvnDirection_setDT, EPS.EvnDirection_setDT), 'dd.mm.yyyy') as \"EvnDirection_setDate \"
				,EPS.EvnPS_CodeConv as \"EvnPS_CodeConv\"
				,EPS.EvnPS_NumConv as \"EvnPS_NumConv\"
				,to_char(EPS.EvnDirection_SetDT, 'dd.mm.yyyy') as \"EvnDirection_SetDT\"
				,RTRIM(PC.PersonCard_Code) as \"PersonCard_Code\"
				,RTRIM(coalesce(PHTR.PrehospTrauma_Name, '')) as \"PrehospTrauma_Name\"
				,RTRIM(coalesce(PHTR.PrehospTrauma_Code, '')) as \"PrehospTrauma_Code\"
				,to_char(EPS.EvnPS_setDate, 'dd.mm.yyyy') as \"EvnPS_setDate\"
				,EPS.EvnPS_setTime as \"EvnPS_setTime\"
				,coalesce(LSFirst.LpuSection_Code, '') as \"LpuSectionFirst_Code\"
				,coalesce(LSFirst.LpuSection_Name, '') as \"LpuSectionFirst_Name\"
				,RTRIM(coalesce(LSBPFirst.LpuSectionBedProfile_Name, '')) as \"LpuSectionBedProfile_Name\"
				,RTRIM(coalesce(MPFirst.MedPersonal_TabCode, '')) as \"MedPersonal_TabCode\"
				,RTRIM(coalesce(MPFirst.MedPersonal_Code, '')) as \"MPFirst_Code\"
				,RTRIM(coalesce(MPFirst.Person_Fio, '')) as \"MedPerson_FIO\"
				,RTRIM(coalesce(OHMP.Person_Fio,'')) as \"OrgHead_FIO\"
				,RTRIM(coalesce(OHMP.MedPersonal_TabCode,'')) as \"OrgHead_Code\"
				,to_char(ESFirst.EvnSection_setDT, 'dd.mm.yyyy') as \"EvnSectionFirst_setDate\"
				,ESFirst.EvnSection_setTime as \"EvnSectionFirst_setTime\"
				,to_char(EPS.EvnPS_disDate, 'dd.mm.yyyy') as \"EvnPS_disDate\"
				,EPS.EvnPS_disTime as \"EvnPS_disTime\"
				,LUTLast.LpuUnitType_SysNick as \"LpuUnitType_SysNick\"
				,case when LUTLast.LpuUnitType_SysNick = 'stac'
					then datediff('day', EPS.EvnPS_setDate, EPS.EvnPS_disDate) + abs(sign(datediff('day', EPS.EvnPS_setDate, EPS.EvnPS_disDate)) - 1) -- круглосуточные
				  	else (datediff('day', EPS.EvnPS_setDate, EPS.EvnPS_disDate) + 1) -- дневные
				 end as \"EvnPS_KoikoDni\"
				,RTRIM(coalesce(LT.LeaveType_Name, '')) as \"LeaveType_Name\"
				,LT.LeaveType_Code as \"LeaveType_Code\"
				,LT.LeaveType_SysNick as \"LeaveType_SysNick\"
				,RTRIM(coalesce(RD.ResultDesease_Name, '')) as \"ResultDesease_Name\"
				,RD.ResultDesease_Code as \"ResultDesease_Code\"
				,RD.ResultDesease_Code as \"ResultDesease_sCode\"
				,case
					when LT.LeaveType_SysNick = 'die' then 6
					when RD.ResultDesease_SysNick in ('kszdor','dszdor') then 1
					when RD.ResultDesease_SysNick in ('dsuluc','ksuluc') then 2
					when RD.ResultDesease_SysNick in ('dsbper','ksbper','noteff') then 3
					when RD.ResultDesease_SysNick in ('dsuchud','ksuchud') then 4
					when RD.ResultDesease_SysNick in ('dszdor','kszdor') then 5
					else null
				end as \"ResultDesease_aCode\"
				,to_char(EST.EvnStick_setDT, 'dd.mm.yyyy') as \"EvnStick_setDate\"
				,to_char(EST.EvnStick_disDT, 'dd.mm.yyyy') as \"EvnStick_disDate\"
				,ESTCP.Person_Age as \"PersonCare_Age\"
				,ESTCP.Sex_Name as \"PersonCare_SexName\"
				,ESTCP.Sex_id as \"PersonCare_SexId\"
				,DG.Diag_Code as \"LeaveDiag_Code\"
				,DG.Diag_Name as \"LeaveDiag_Name\"
				,DGA.Diag_Code as \"LeaveDiagAgg_Code\"
				,DGA.Diag_Name as \"LeaveDiagAgg_Name\"
				,DGS.Diag_Code as \"LeaveDiagSop_Code\"
				,DGS.Diag_Name as \"LeaveDiagSop_Name\"
				,PAD.Diag_Code as \"AnatomDiag_Code\"
				,PAD.Diag_Name as \"AnatomDiag_Name\"
				,PADA.Diag_Code as \"AnatomDiagAgg_Code\"
				,PADA.Diag_Name as \"AnatomDiagAgg_Name\"
				,PADS.Diag_Code as \"AnatomDiagSop_Code\"
				,PADS.Diag_Name as \"AnatomDiagSop_Name\"
				,case when EPS.EvnPS_IsImperHosp = 2 then '1; ' else null end as \"EvnPS_IsImperHosp\"
				,case when EPS.EvnPS_IsShortVolume = 2 then '2; ' else null end as \"EvnPS_IsShortVolume\"
				,case when EPS.EvnPS_IsWrongCure = 2 then '3; ' else null end as \"EvnPS_IsWrongCure\"
				,case when EPS.EvnPS_IsDiagMismatch = 2 then '4; ' else null end as \"EvnPS_IsDiagMismatch\"
				,LC.LeaveCause_Code as \"LeaveCause_Code\"
				,EPS.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\"
				,IsRW.YesNo_Name as \"IsRW\"
				,IsAIDS.YesNo_Name as \"IsAIDS\"
				,PEH.PersonEncrypHIV_Encryp as \"PersonEncrypHIV_Encryp\"
			from dbo.v_EvnPS as EPS
				inner join dbo.v_Lpu as Lpu on Lpu.Lpu_id = EPS.Lpu_id
				inner join dbo.v_PersonState as PS on PS.Person_id = EPS.Person_id
				left join dbo.v_EvnDirection as ED on ED.EvnDirection_id = EPS.EvnDirection_id
				left join dbo.v_EvnSection as ESLast on ESLast.EvnSection_pid = EPS.EvnPS_id
					and ESLast.EvnSection_Index = ESLast.EvnSection_Count - 1
				left join dbo.v_EvnSection as ESFirst on ESFirst.EvnSection_pid = EPS.EvnPS_id
					and ESFirst.EvnSection_Index = 1
				left join dbo.v_LpuSection as LSLast on LSLast.LpuSection_id = ESLast.LpuSection_id
				left join dbo.LpuUnit as LULast on LULast.LpuUnit_id = LSLast.LpuUnit_id
				left join dbo.LpuUnitType as LUTLast on LUTLast.LpuUnitType_id = LULast.LpuUnitType_id
				left join dbo.v_PrehospDirect as PD on EPS.PrehospDirect_id = PD.PrehospDirect_id
				left join dbo.v_EvnLeave as ELeave on ELeave.EvnLeave_pid = ESLast.EvnSection_id
				left join dbo.LeaveCause as LC on LC.LeaveCause_id = ELeave.LeaveCause_id
				left join dbo.v_Polis as PLS on PLS.Polis_id = PS.Polis_id
				left join dbo.v_OmsSprTerr as OST on OST.OmsSprTerr_id = PLS.OmsSprTerr_id
				left join dbo.v_PolisType as PLST on PLST.PolisType_id = PLS.PolisType_id
				left join dbo.v_OrgSmo as OrgSmo on OrgSmo.OrgSmo_id = PLS.OrgSmo_id
				left join dbo.v_Org as OS on OS.Org_id = OrgSmo.Org_id
				left join dbo.v_Address as UAddr on UAddr.Address_id = PS.UAddress_id
				left join dbo.KLArea as country on country.KLArea_id = UAddr.KLCountry_id
				left join dbo.KLArea as rgn on rgn.KLArea_id = UAddr.KLRgn_id
				left join dbo.KLArea as srgn on srgn.KLArea_id = UAddr.KLSubRgn_id
				left join dbo.KLArea as city on city.KLArea_id = UAddr.KLCity_id
				left join dbo.KLArea as town on town.KLArea_id = UAddr.KLSubRgn_id
				left join dbo.KLStreet as street on street.KLStreet_id = UAddr.KLStreet_id
				left join dbo.v_Address as PAddr on PAddr.Address_id = PS.PAddress_id
				left join dbo.v_KLAreaType as KLAT on KLAT.KLAreaType_id = PAddr.KLAreaType_id
				left join dbo.v_Document as D on D.Document_id = PS.Document_id
				left join dbo.v_DocumentType as DT on DT.DocumentType_id = D.DocumentType_id
				left join dbo.v_Sex as SX on SX.Sex_id = PS.Sex_id
				left join dbo.v_PayType as PT on PT.PayType_id = EPS.PayType_id
				left join dbo.v_SocStatus as SS on SS.SocStatus_id = PS.SocStatus_id
				left join lateral(
					select
						PrivilegeType_id,
						PrivilegeType_Code,
						PrivilegeType_Name
					from dbo.v_PersonPrivilege
					where PrivilegeType_Code in ('81', '82', '83')
						and Person_id = PS.Person_id
					order by PersonPrivilege_begDate desc
					limit 1
				) as IT on true
				left join lateral(
					select
						PrivilegeType_id,
						PrivilegeType_Code,
						PrivilegeType_Name
					from dbo.v_PersonPrivilege
					where PrivilegeType_Code in ('11', '20', '91', '81', '82', '83', '84')
						and Person_id = PS.Person_id
					order by PersonPrivilege_begDate desc
					limit 1
				) as IT2 on true
				left join dbo.v_PersonCard as PC on PC.Person_id = PS.Person_id
					and PC.PersonCard_begDate is not null
					and PC.PersonCard_begDate <= EPS.EvnPS_insDT
					and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > EPS.EvnPS_insDT)
					and PC.Lpu_id = EPS.Lpu_id
				left join dbo.v_LpuSection as PHLS on PHLS.LpuSection_id = EPS.LpuSection_did
				left join dbo.v_OrgHead as OH on OH.LpuUnit_id = PHLS.LpuUnit_id and OH.OrgHeadPost_id = 13
				left join dbo.v_MedPersonal as OHMP on OHMP.Person_id = OH.Person_id
				left join dbo.v_Lpu as PreHospLpu on PreHospLpu.Lpu_id = EPS.Lpu_did
				left join dbo.v_MedStaffFact as MSF on MSF.MedStaffFact_id = EPS.MedStaffFact_pid
				left join dbo.v_OrgMilitary as PHOM on PHOM.OrgMilitary_id = EPS.OrgMilitary_did
				left join dbo.v_Org as PHO on PHO.Org_id = EPS.Org_did
				left join dbo.v_PrehospArrive as PA on PA.PrehospArrive_id = EPS.PrehospArrive_id
				left join dbo.v_Diag as DiagH on DiagH.Diag_id = EPS.Diag_did
				left join dbo.v_Diag as DiagP on DiagP.Diag_id = EPS.Diag_pid
				left join dbo.v_PrehospToxic as PHTX on PHTX.PrehospToxic_id = EPS.PrehospToxic_id
				left join dbo.v_LpuSectionTransType as LSTT on LSTT.LpuSectionTransType_id = EPS.LpuSectionTransType_id
				left join dbo.v_PrehospType as PHT on PHT.PrehospType_id = EPS.PrehospType_id
				left join dbo.v_PrehospTrauma as PHTR on PHTR.PrehospTrauma_id = EPS.PrehospTrauma_id
				left join dbo.v_MedPersonal as MPFirst on EPS.MedPersonal_pid = MPFirst.MedPersonal_id
				left join dbo.v_LpuSection as LSFirst on LSFirst.LpuSection_id = ESFirst.LpuSection_id
				left join dbo.v_LpuSectionBedProfile as LSBPFirst on LSBPFirst.LpuSectionBedProfile_id = LSFirst.LpuSectionBedProfile_id
				left join dbo.v_LeaveType as LT on LT.LeaveType_id = EPS.LeaveType_id
				left join dbo.v_EvnLeave as EL on EL.EvnLeave_pid = ESLast.EvnSection_id
				left join dbo.v_EvnDie as EDie on EDie.EvnDie_pid = ESLast.EvnSection_id
				left join dbo.v_EvnOtherLpu as EOL on EOL.EvnOtherLpu_pid = ESLast.EvnSection_id
				left join dbo.v_EvnOtherStac as EOST on EOST.EvnOtherStac_pid = ESLast.EvnSection_id
				left join dbo.v_ResultDesease as RD on RD.ResultDesease_id = COALESCE(EL.ResultDesease_id, EOL.ResultDesease_id, EOST.ResultDesease_id, EDie.ResultDesease_id)
				left join dbo.v_PersonEncrypHIV as PEH on PEH.Person_id = PS.Person_id
				left join lateral(
					select
						 EvnStick_id
						,EvnStick_setDT
						,EvnStick_disDT
					from
						dbo.v_EvnStick
					where
						EvnStick_pid = EPS.EvnPS_id
					order by
						EvnStick_setDT
					limit 1
				) as EST on true
				left join lateral(
					select
						 dbo.Age2(t2.Person_Birthday, EPS.EvnPS_setDT) as Person_Age
						,t3.Sex_Name
						,t3.Sex_id
					from
						dbo.v_EvnStickCarePerson as t1
						left join dbo.v_PersonState as t2 on t2.Person_id = t1.Person_id
						left join dbo.v_Sex as t3 on t3.Sex_id = t2.Sex_id
					where
						t1.Evn_id = EST.EvnStick_id
					limit 1
				) as ESTCP on true
				left join dbo.v_Diag as DG on DG.Diag_id = ESLast.Diag_id and coalesce(ESLast.LeaveType_id, 0) != 5
				left join dbo.v_Diag as PAD on PAD.Diag_id = EDie.Diag_aid
				left join lateral(
					select Diag_id
					from dbo.v_EvnDiagPS
					where EvnDiagPS_pid = ESLast.EvnSection_id
						and DiagSetClass_id = 2
					limit 1
				) as TDGA on true
				left join dbo.v_Diag as DGA on DGA.Diag_id = TDGA.Diag_id and coalesce(ESLast.LeaveType_id, 0) != 5
				left join lateral(
					select Diag_id
					from dbo.v_EvnDiagPS
					where EvnDiagPS_pid = ESLast.EvnSection_id
						and DiagSetClass_id = 3
					limit 1
				) as TDGS on true
				left join dbo.v_Diag as DGS on DGS.Diag_id = TDGS.Diag_id and coalesce(ESLast.LeaveType_id, 0) != 5
				left join lateral(
					select Diag_id
					from dbo.v_EvnDiagPS
					where EvnDiagPS_pid = EDie.EvnDie_id
						and DiagSetClass_id = 2
					limit 1
				) as TPADA on true
				left join dbo.v_Diag as PADA on PADA.Diag_id = TPADA.Diag_id
				left join lateral(
					select Diag_id
					from dbo.v_EvnDiagPS
					where EvnDiagPS_pid = EDie.EvnDie_id
						and DiagSetClass_id = 3
					limit 1
				) as TPADS on true
				left join dbo.v_Diag as PADS on PADS.Diag_id = TPADS.Diag_id
				left join dbo.v_LpuUnitType as oLUT on oLUT.LpuUnitType_id = EOST.LpuUnitType_oid
				left join lateral(
					select t3.YesNo_Name
					from dbo.v_EvnUsluga as t1
						inner join dbo.v_UslugaComplex as t2 on t2.UslugaComplex_id = t1.UslugaComplex_id
						inner join dbo.v_YesNo as t3 on t3.YesNo_Code = 1
					where t1.EvnUsluga_rid = EPS.EvnPS_id
						and t2.UslugaComplex_Code = 'A12.06.011'
						and t1.EvnUsluga_SetDT is not null
					limit 1
				) as IsRW on true
				left join lateral(
					select t3.YesNo_Name
					from dbo.v_EvnUsluga t1
						inner join dbo.v_UslugaComplex t2 on t2.UslugaComplex_id = t1.UslugaComplex_id
						inner join dbo.v_YesNo as t3 on t3.YesNo_Code = 1
					where
						t1.EvnUsluga_rid = EPS.EvnPS_id
						and t2.UslugaComplex_Code = 'A09.05.228'
						and t1.EvnUsluga_setDT is not null
					limit 1
				) as IsAIDS on true
			where
				EPS.EvnPS_id = :EvnPS_id
		";

		if( !isTFOMSUser() && empty($data['session']['medpersonal_id']) ) {
			$query.=' and EPS.Lpu_id = :Lpu_id';
		}

		$response = $this->queryResult($query, [
			'EvnPS_id' => $data['EvnPS_id'],
			'Lpu_id' => $data['Lpu_id'],
		]);

		if ( !is_array($response) || count($response) == 0 ) {
			return false;
		}

		//Отдельно получим сопутствующие диагнозы и осложнения
		$response_temp = [];

		$response_temp[0] = [
			'LeaveDiagSop_Name' => '',
			'LeaveDiagSop_Code' => '',
			'LeaveDiagAgg_Name' => '',
			'LeaveDiagAgg_Code' => '',
		];

		$response_diag_sop = $this->queryResult("
			select
				DGS.Diag_Code as \"LeaveDiagSop_Code\",
				DGS.Diag_Name as \"LeaveDiagSop_Name\"
			from
				v_EvnDiagPS as EDPS
				inner join v_EvnSection as ESLast on ESLast.EvnSection_id = EDPS.EvnDiagPS_pid
				inner join v_Diag as DGS on DGS.Diag_id = EDPS.Diag_id
			where
				EDPS.DiagSetClass_id = 3
				and EDPS.EvnDiagPS_rid = :EvnPS_id
				and EDPS.Lpu_id = :Lpu_id
				and ESLast.EvnSection_Index = ESLast.EvnSection_Count - 1
		", [
			'EvnPS_id' => $data['EvnPS_id'],
			'Lpu_id' => $data['Lpu_id'],
		]);

		if ( is_array($response_diag_sop) ) {
			foreach ( $response_diag_sop as $row ) {
				$response_temp[0]['LeaveDiagSop_Name'] .= $row['LeaveDiagSop_Name'];
				$response_temp[0]['LeaveDiagSop_Code'] .= $row['LeaveDiagSop_Code'];
			}
		}

		$response_diag_osl = $this->queryResult("
			select
				DGA.Diag_Code as \"LeaveDiagAgg_Code\",
				DGA.Diag_Name as \"LeaveDiagAgg_Name\"
			from
				v_EvnDiagPS as EDPS
				inner join v_EvnSection as ESLast on ESLast.EvnSection_id = EDPS.EvnDiagPS_pid
				inner join v_Diag as DGA on DGA.Diag_id = EDPS.Diag_id
			where
				EDPS.DiagSetClass_id = 2
				and EDPS.EvnDiagPS_rid = :EvnPS_id
				and EDPS.Lpu_id = :Lpu_id
				and ESLast.EvnSection_Index = ESLast.EvnSection_Count - 1
		", [
			'EvnPS_id' => $data['EvnPS_id'],
			'Lpu_id' => $data['Lpu_id'],
		]);

		if ( is_array($response_diag_osl) ) {
			foreach ( $response_diag_osl as $row ) {
				$response_temp[0]['LeaveDiagAgg_Name'] .= $row['LeaveDiagAgg_Name'];
				$response_temp[0]['LeaveDiagAgg_Code'] .= $row['LeaveDiagAgg_Code'];
			}
		}

		$response[0]['LeaveDiagSop_Name'] = $response_temp[0]['LeaveDiagSop_Name'];
		$response[0]['LeaveDiagSop_Code'] = $response_temp[0]['LeaveDiagSop_Code'];
		$response[0]['LeaveDiagAgg_Name'] = $response_temp[0]['LeaveDiagAgg_Name'];
		$response[0]['LeaveDiagAgg_Code'] = $response_temp[0]['LeaveDiagAgg_Code'];

		return $response;
	}

	/**
	 * @param array $data
	 * @param array $response
	 * @return array|string
	 */
	protected function _printEvnPS($data = [], $response = []) {
		$template = 'evn_ps_template_list_a4_buryatiya';

		switch ( $response[0]['PayType_Code'] ) {
			case 1:
				$response[0]['PayType_Code'] = 1; // ОМС-1
				break;

			case 2:
				$response[0]['PayType_Code'] = 2; // ДМС-4
				break;

			case 3:
				$response[0]['PayType_Code'] = 3; // Бюджет-2
				break;

			case 4:
				$response[0]['PayType_Code'] = 4; // Бюджет-2
				break;
			
			case 4:
				$response[0]['PayType_Code'] = 5; // Платные услуги-3
				break;

			default:
				$response[0]['PayType_Code'] = 6; // другое
				break;
		}
		$evn_section_data = [];
		$evn_usluga_oper_data = [];

		$response_temp = $this->getEvnSectionData($data);

		if ( is_array($response_temp) ) {

			foreach($response_temp as $j => $value) {

				$query = "
					select  LS.LpuSection_Code as \"LpuSection_Code\",
							CONCAT(LS.LpuSection_Name, ' [Реанимация]') as \"LpuSection_Name\",
							ERP.EvnReanimatPeriod_pid as \"EvnReanimatPeriod_pid\",
							to_char(ERP.EvnReanimatPeriod_setDT, 'dd.mm.yyyy hh24:mi') as \"EvnSection_setDT\",
							to_char(ERP.EvnReanimatPeriod_disDT, 'dd.mm.yyyy hh24:mi') as \"EvnSection_disDT\",
							ES.Diag_id as \"Diag_id\",
							RTRIM(coalesce(D.Diag_Code, '')) as \"EvnSectionDiagOsn_Code\",
							RTRIM(coalesce(D.Diag_Name, '')) as \"EvnSectionDiagOsn_Name\",
							'Основной' as \"EvnSectionDiagSetClassOsn_Name\",
							EPS.EvnPS_id as \"EvnReanimatPeriod_rid\",
							MP.MedPersonal_TabCode as \"MedPersonal_Code\",
							RTRIM(coalesce(Mes.Mes_Code, '')) as \"EvnSectionMesOsn_Code\",
							MP.MedPersonal_Code as \"MedPersonal_Code\",
							RTRIM(coalesce(PT.PayType_Name, '')) as \"EvnSectionPayType_Name\",
							LSBP.LpuSectionBedProfile_Code as \"LpuSectionBedProfile_Code\",
							LSBP.LpuSectionBedProfile_Name as \"LpuSectionBedProfile_Name\"
					  from  dbo.v_EvnReanimatPeriod ERP
							inner join v_EvnSection ES on ES.EvnSection_id = ERP.EvnReanimatPeriod_pid
							inner join v_EvnPS EPS on EPS.EvnPS_id = ES.EvnSection_pid
							left join v_LpuSection LS on LS.LpuSection_id = ERP.LpuSection_id
							left join v_MedService MS on MS.MedService_id = ERP.MedService_id
							left join v_MesOld Mes on Mes.Mes_id = ES.Mes_id
							inner join LpuSectionProfile LSP on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
							left join fed.LpuSectionBedProfileLink LSBPLink on  LSBPLink.LpuSectionBedProfileLink_id = ES.LpuSectionBedProfileLink_fedid
							left join dbo.v_LpuSectionBedProfile LSBP on LSBP.LpuSectionBedProfile_id = LSBPLink.LpuSectionBedProfile_id
							inner join lateral(
								select
									 MedPersonal_TabCode
									,MedPersonal_Code
									,Person_Fio
								from v_MedPersonal
								where MedPersonal_id = ES.MedPersonal_id
								limit 1
							) MP on true
							left join dbo.Diag D on D.Diag_id = coalesce(ES.Diag_id, EPS.Diag_pid)
							inner join v_PayType PT on PT.PayType_id = ES.PayType_id
					  where ERP.EvnReanimatPeriod_pid = :EvnSection_id
				";

				$result = $this -> db -> query($query, array('EvnSection_id' => $value['EvnSection_id']));

				if (is_object($result)) {
					$erp_data = $result -> result('array');
					array_splice($response_temp, $j + 1, 0, $erp_data);
				}
				else{
					return false;
				}

			}

			$evn_section_data = $response_temp;

			for ( $i = 0; $i < (count($evn_section_data) < 6 ? 6 : count($evn_section_data)); $i++ ) {
				if ( $i >= count($evn_section_data) ) {
					$evn_section_data[$i] = [
						'Index' => $i + 1,
						'LpuSection_Code' => '&nbsp;',
						'EvnSection_setDT' => '&nbsp;',
						'EvnSection_disDT' => '&nbsp;',
						'EvnSectionDiagOsn_Code' => '&nbsp;',
						'EvnSectionMesOsn_Code' => '&nbsp;',
						'EvnSection_UKL' => '&nbsp;',
						'EvnSectionPayType_Name' => '&nbsp;',
						'LpuSectionBedProfile_Code' => '&nbsp;',
						'MedPersonal_Code' => '&nbsp;'
					];
				}
				else {
					$evn_section_data[$i]['Index'] = $i + 1;

					if ( !empty($evn_section_data[$i]['PayType_Name']) ) {
						$evn_section_data[$i]['EvnSectionPayType_Name'] = $evn_section_data[$i]['PayType_Name'];
					}
				}
			}
		}

		$response_temp = $this->getEvnUslugaOperData($data);

		if ( is_array($response_temp) ) {
			for ( $i = 0; $i < count($response_temp); $i++ ) {
				$evn_usluga_oper_data[] = [
					'EvnUslugaOper_setDT' => $response_temp[$i]['EvnUslugaOper_setDT'],
					'EvnUslugaOperMedPersonal_Code' => $response_temp[$i]['MedPersonal_Code'],
					'EvnUslugaOperLpuSection_Code' => $response_temp[$i]['LpuSection_Code'],
					'EvnUslugaOper_Name' => $response_temp[$i]['Usluga_Name'],
					'EvnUslugaOper_Code' => $response_temp[$i]['Usluga_Code'],
					'EvnUslugaOperAnesthesiaClass_Name' => $response_temp[$i]['AnesthesiaClass_Name'],
					'EvnUslugaOper_IsEndoskop' => $response_temp[$i]['EvnUslugaOper_IsEndoskop'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOper_IsLazer' => $response_temp[$i]['EvnUslugaOper_IsLazer'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOper_IsKriogen' => $response_temp[$i]['EvnUslugaOper_IsKriogen'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOper_IsRadGraf' => $response_temp[$i]['EvnUslugaOper_IsRadGraf'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOperPayType_Name' => $response_temp[$i]['PayType_Name']
				];
			}

			// https://redmine.swan.perm.ru/issues/6484
			// savage: Добавляем пустые строки в таблицу с хирургическими операциями, если количество операций меньше двух
			for ( $j = $i; $j < 3; $j++ ) {
				$evn_usluga_oper_data[] = [
					'EvnUslugaOper_setDT' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOperMedPersonal_Code' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOperLpuSection_Code' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOper_Name' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOper_Code' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOperAnesthesiaClass_Name' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOper_IsEndoskop' => '&nbsp;',
					'EvnUslugaOper_IsLazer' => '&nbsp;',
					'EvnUslugaOper_IsKriogen' => '&nbsp;',
					'EvnUslugaOper_IsRadGraf' => '&nbsp;',
					'EvnUslugaOperPayType_Name' => '&nbsp;<br />&nbsp;'
				];
			}
		}

		if ( !empty($response[0]['PrivilegeType_Code']) ) {
			switch ( $response[0]['PrivilegeType_Code'] ) {
				case 10:
					$response[0]['PrivilegeType_Code'] = 1; // инвалид ВОВ
					break;

				case 11:
				case 20:
					$response[0]['PrivilegeType_Code'] = 2; // участник ВОВ
					break;

				case 12:
				case 30:
				case 40:
					$response[0]['PrivilegeType_Code'] = 3; // воин-интернационалист
					break;

				case 111:
				case 112:
					$response[0]['PrivilegeType_Code'] = 4; // лицо, подвергш. радиационному облуч.
					break;

				case 91:
				case 92:
				case 93:
				case 94:
				case 98:
				case 101:
				case 102:
					$response[0]['PrivilegeType_Code'] = 5; // в т.ч. в Чернобыле
					break;

				case 83:
					$response[0]['PrivilegeType_Code'] = 6; // инв. Iгр
					break;

				case 82:
					$response[0]['PrivilegeType_Code'] = 7; // инв. IIгр
					break;

				case 81:
					$response[0]['PrivilegeType_Code'] = 8; // инв. IIIгр
					break;

				case 84:
				//case 101:
					$response[0]['PrivilegeType_Code'] = 9; // ребенок-инвалид
					break;

				case 84:
					$response[0]['PrivilegeType_Code'] = 10; // инвалид с детства
					break;

				default:
					$response[0]['PrivilegeType_Code'] = 11; // прочие
					break;
			}
		}

		$LeaveType_Code = '';

		if ( in_array($response[0]['LeaveType_SysNick'], [ 'ksleave', 'dsleave' ]) ) {
			$LeaveType_Code = 1; // выписан
		}
		else if ( in_array($response[0]['LeaveType_SysNick'], [ 'ksstac' ]) ) {
			$LeaveType_Code = 2; // в т.ч. в дневной стационар
		}
		else if ( in_array($response[0]['LeaveType_SysNick'], [ 'dsstac' ]) ) {
			$LeaveType_Code = 3; // в круглосуточный стационар
		}
		else if ( in_array($response[0]['LeaveType_SysNick'], [ 'ksother', 'ksper', 'dsother', 'dsper' ]) ) {
			$LeaveType_Code = 4; // переведен в другой стационар
		}

		$print_data = [
			'EvnPSTemplateTitle' => 'Печать карты выбывшего из стационара'
			,'EvnPS_NumCard' => returnValidHTMLString($response[0]['EvnPS_NumCard'])
			,'PolisType_Name' => returnValidHTMLString($response[0]['PolisType_Name'])
			,'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num'])
			,'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser'])
			,'OMSSprTerr_Code' => returnValidHTMLString($response[0]['OMSSprTerr_Code'])
			,'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name'])
			,'Person_Fio' => returnValidHTMLString($response[0]['Person_Fio'])
			,'Person_AgeYears' => returnValidHTMLString($response[0]['Person_AgeYears'])
			,'Person_AgeMonths' => ''
			,'Person_AgeDays' => ''
			,'Person_OKATO' => returnValidHTMLString($response[0]['Person_OKATO'])
			,'Sex_Name' => returnValidHTMLString($response[0]['Sex_Name'])
			,'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday'])
			,'DocumentType_Name' => returnValidHTMLString($response[0]['DocumentType_Name'])
			,'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser'])
			,'Document_Num' => returnValidHTMLString($response[0]['Document_Num'])
			,'KLAreaType_Name' => returnValidHTMLString($response[0]['KLAreaType_Name'])
			,'KLAreaType_id' => returnValidHTMLString($response[0]['KLAreaType_id'])
			,'Person_Phone' => returnValidHTMLString($response[0]['Person_Phone'])
			,'MedPersonalPriem_Code' => returnValidHTMLString($response[0]['MedPersonalPriem_Code'])
			,'MedPersonalPriem_FIO' => returnValidHTMLString($response[0]['MedPersonalPriem_FIO'])
			,'PAddress_Name' => returnValidHTMLString($response[0]['PAddress_Name'])
			,'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name'])
			,'PayType_Code' => returnValidHTMLString($response[0]['PayType_Code'])
			,'PayType_Name' => returnValidHTMLString($response[0]['PayType_Name'])
			,'SocStatus_Code' => returnValidHTMLString($response[0]['SocStatus_Code'])
			,'SocStatus_Name' => returnValidHTMLString($response[0]['SocStatus_Name'])
			,'PrehospOrg_Name' => returnValidHTMLString($response[0]['PrehospOrg_Name'])
			,'PrehospArrive_Name' => returnValidHTMLString($response[0]['PrehospArrive_Name'])
			,'PersonCard_Code' => returnValidHTMLString($response[0]['PersonCard_Code'])
			,'PrivilegeType_Code' => returnValidHTMLString($response[0]['PrivilegeType_Code'])
			,'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name'])
			,'PrehospDiag_Code' => returnValidHTMLString($response[0]['PrehospDiag_Code'])
			,'PrehospDiag_Name' => returnValidHTMLString($response[0]['PrehospDiag_Name'])
			,'AdmitDiag_Code' => returnValidHTMLString($response[0]['AdmitDiag_Code'])
			,'PrehospToxic_Code' => returnValidHTMLString($response[0]['PrehospToxic_Code'])
			,'LpuSectionTransType_Code' => returnValidHTMLString($response[0]['LpuSectionTransType_Code'])
			,'PrehospType_Code' => returnValidHTMLString($response[0]['PrehospType_Code'])
			,'PrehospType_Name' => returnValidHTMLString($response[0]['PrehospType_Name'])
			,'EvnPS_HospCountCode' => returnValidHTMLString($response[0]['EvnPS_HospCountCode'])
			,'EvnPS_HospCountName' => returnValidHTMLString($response[0]['EvnPS_HospCountName'])
			,'EvnPS_TimeDesease' => (returnValidHTMLString($response[0]['EvnPS_TimeDesease']))==''?'0':((returnValidHTMLString($response[0]['EvnPS_TimeDesease']))<=6?'1':(returnValidHTMLString($response[0]['EvnPS_TimeDesease'])>24?'3':2))
			,'PrehospTrauma_Code' => returnValidHTMLString($response[0]['PrehospTrauma_Code'])
			,'EvnPS_setDate' => returnValidHTMLString($response[0]['EvnPS_setDate'])
			,'EvnPS_setTime' => returnValidHTMLString($response[0]['EvnPS_setTime'])
			,'LpuSectionFirst_Code' => returnValidHTMLString($response[0]['LpuSectionFirst_Code'])
			,'LpuSectionFirst_Name' => returnValidHTMLString($response[0]['LpuSectionFirst_Name'])
			,'EvnSectionFirst_setDate' => returnValidHTMLString($response[0]['EvnSectionFirst_setDate'])
			,'EvnSectionFirst_setTime' => returnValidHTMLString($response[0]['EvnSectionFirst_setTime'])
			,'EvnPS_disDate' => returnValidHTMLString($response[0]['EvnPS_disDate'])
			,'EvnPS_disTime' => returnValidHTMLString($response[0]['EvnPS_disTime'])
			,'EvnPS_KoikoDni' => returnValidHTMLString($response[0]['EvnPS_KoikoDni'])
			,'LeaveType_Code' => $LeaveType_Code
			,'ResultDesease_aCode' => returnValidHTMLString($response[0]['ResultDesease_aCode'])
			,'EvnStick_setDate' => returnValidHTMLString($response[0]['EvnStick_setDate'])
			,'EvnStick_disDate' => returnValidHTMLString($response[0]['EvnStick_disDate'])
			,'PersonCare_Age' => returnValidHTMLString($response[0]['PersonCare_Age'])
			,'PersonCare_SexName' => returnValidHTMLString($response[0]['PersonCare_SexName'])
			,'EvnSectionData' => $evn_section_data
			,'EvnUslugaOperData' => $evn_usluga_oper_data
			,'LeaveDiag_Code' => returnValidHTMLString($response[0]['LeaveDiag_Code'])
			,'LeaveDiag_Name' => returnValidHTMLString($response[0]['LeaveDiag_Name'])
			,'LeaveDiagAgg_Code' => returnValidHTMLString($response[0]['LeaveDiagAgg_Code'])
			,'LeaveDiagAgg_Name' => returnValidHTMLString($response[0]['LeaveDiagAgg_Name'])
			,'LeaveDiagSop_Code' => returnValidHTMLString($response[0]['LeaveDiagSop_Code'])
			,'LeaveDiagSop_Name' => returnValidHTMLString($response[0]['LeaveDiagSop_Name'])
			,'AnatomDiag_Code' => returnValidHTMLString($response[0]['AnatomDiag_Code'])
			,'AnatomDiag_Name' => returnValidHTMLString($response[0]['AnatomDiag_Name'])
			,'AnatomDiagAgg_Code' => returnValidHTMLString($response[0]['AnatomDiagAgg_Code'])
			,'AnatomDiagAgg_Name' => returnValidHTMLString($response[0]['AnatomDiagAgg_Name'])
			,'AnatomDiagSop_Code' => returnValidHTMLString($response[0]['AnatomDiagSop_Code'])
			,'AnatomDiagSop_Name' => returnValidHTMLString($response[0]['AnatomDiagSop_Name'])
			,'EvnPS_IsDiagMismatch' => returnValidHTMLString($response[0]['EvnPS_IsDiagMismatch'])
			,'EvnPS_IsImperHosp' => returnValidHTMLString($response[0]['EvnPS_IsImperHosp'])
			,'EvnPS_IsShortVolume' => returnValidHTMLString($response[0]['EvnPS_IsShortVolume'])
			,'EvnPS_IsWrongCure' => returnValidHTMLString($response[0]['EvnPS_IsWrongCure'])
			,'EvnDirection_Num' => returnValidHTMLString($response[0]['EvnDirection_Num'])
			,'EvnDirection_setDate' => returnValidHTMLString($response[0]['EvnDirection_setDate'])
		];

		if (allowPersonEncrypHIV($data['session']) && !empty($response[0]['PersonEncrypHIV_Encryp'])) {
			$print_data['Person_Fio'] = returnValidHTMLString($response[0]['PersonEncrypHIV_Encryp']);

			$person_fields = [ 'PolisType_Name', 'Polis_Num', 'Polis_Ser', 'OMSSprTerr_Code', 'OrgSmo_Name',
				'Person_OKATO', 'Sex_Name', 'Person_Birthday', 'Person_AgeYears', 'Person_AgeMonths', 'Person_AgeDays',
				'DocumentType_Name', 'Document_Ser', 'Document_Num', 'KLAreaType_Name', 'KLAreaType_id', 'Person_Phone',
				'PAddress_Name', 'UAddress_Name', 'SocStatus_Code', 'InvalidType_Name', 'PersonCard_Code',
				'PrivilegeType_Code'
			];

			foreach($person_fields as $field) {
				$print_data[$field] = '';
			}
		}

		$html = $this->parser->parse($template, $print_data, !empty($data['returnString']));

		if ( !empty($data['returnString']) ) {
			return [ 'html' => $html ];
		}
		else {
			return $html;
		}
	}
}
