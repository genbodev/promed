<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Datamatrix_model - модель для получение штрих-кода формата DataMatrix
 * @author       Khorev Sergey (sergey.khorev@yandex.ru)
 * @version      30.10.2013
 */

class Datamatrix_model extends CI_Model{
	public $log_file = 'barcodesearch.log';
	public $log_file_access_type = 'a';

	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @param $data
	 * @return bool
	 * Получение полей для формирования матричного кода. На входе - id-шникх ЛВНа
	 */
	function GetDatamatrixFields($data){
		$query ="
				select distinct
					ISNULL(O.Org_Nick,'') as LN_EMPLOYER,
					case when ISNULL(ES.StickWorkType_id,0)=1 then 1 else 0 end as LN_EMPL_FLAG,
					ISNULL(ES.EvnStick_Num,'') as LN_CODE,
					ISNULL(ES_PREV.EvnStick_Num,'') as PREV_LN_CODE,
					case when ISNULL(SO.StickOrder_Code,0) = 1 then 1 else 0 end as PRIMARY_FLAG,
					case when ISNULL(ES.EvnStick_IsOriginal,0)=1 then 0 else 1 end as DUPLICATE_FLAG,
					ISNULL(CONVERT(varchar(10), ES.EvnStick_setDate,20),'') as LN_DATE,
					ISNULL(L.Lpu_Nick,'') as LPU_NAME,
					ISNULL(L.PAddress_Address,'') as LPU_ADDRESS,
					ISNULL(L.Lpu_OGRN,'') as LPU_OGRN,
					ISNULL(PS.Person_SurName,'') as LN_LAST_NAME,
					ISNULL(PS.Person_FirName,'') as LN_FIRST_NAME,
					ISNULL(PS.Person_SecName,'') as LN_PATRONYMIC,
					ISNULL(CONVERT(varchar(10), PS.Person_BirthDay,20),'') as BIRTHDAY,
					case when ISNULL(S.Sex_Code,0)=1 then 0 else 1 end as GENDER,
					ISNULL(SC.StickCause_Code,'') as REASON1,
					ISNULL(SCDT.StickCauseDopType_Code,'') as REASON2,
					ISNULL(SC2.StickCause_Code,'') as REASON3,
					'' as PARENT_CODE,
					ISNULL(CONVERT(varchar(10),ES.EvnStick_BirthDate,20),ISNULL(CONVERT(varchar(10),ES.EvnStick_sstBegDate,20),'')) as DATE1,
					ISNULL(CONVERT(varchar(10),ES.EvnStick_sstEndDate,20),'') as DATE2,
					ISNULL(ES.EvnStick_sstNum,'') as VOUCHER_NO,
					ISNULL(Org.Org_OGRN,'') as VOUCHER_OGRN,
					ISNULL(dbo.Age2(PSSCP1.Person_BirthDay,ES.EvnStick_setDate),'') as SERV1_AGE,
					ISNULL((DATEDIFF(MONTH,PSSCP1.Person_BirthDay,ES.EvnStick_setDate)-dbo.Age2(PSSCP1.Person_BirthDay,ES.EvnStick_setDate)*12),'') as SERV1_MM,
					ISNULL(RLT1.RelatedLinkType_Code,'') as SERV1_RELATION_CODE,
					ISNULL(PSSCP1.Person_SurName,'')+' '+ISNULL(PSSCP1.Person_FirName,'')+' '+ISNULL(PSSCP1.Person_SecName,'') as SERV1_FIO,
					ISNULL(dbo.Age2(PSSCP2.Person_BirthDay,ES.EvnStick_setDate),'') as SERV2_AGE,
					ISNULL((DATEDIFF(MONTH,PSSCP2.Person_BirthDay,ES.EvnStick_setDate)-dbo.Age2(PSSCP2.Person_BirthDay,ES.EvnStick_setDate)*12),'') as SERV2_MM,
					ISNULL(RLT2.RelatedLinkType_Code,'') as SERV2_RELATION_CODE,
					ISNULL(PSSCP2.Person_SurName,'')+' '+ISNULL(PSSCP2.Person_FirName,'')+' '+ISNULL(PSSCP2.Person_SecName,'') as SERV2_FIO,
					'' as PREGN12W_FLAG,
					'' as BOZ_FLAG,
					ISNULL(CONVERT(varchar(10), ES.EvnStick_stacBegDate,20),'') as HOSPITAL_DT1,
					ISNULL(CONVERT(varchar(10), ES.EvnStick_stacEndDate,20),'') as HOSPITAL_DT2,
					SI.StickIrregularity_Code as HOSPITAL_BREACH_CODE,
					ISNULL(CONVERT(varchar(10), ES.EvnStick_irrDT,20),'') as HOSPITAL_BREACH_DT,
					ISNULL(CONVERT(varchar(10), ES.EvnStick_mseDT,20),'') as MSE_DT1,
					ISNULL(CONVERT(varchar(10), ES.EvnStick_mseRegDT,20),'') as MSE_DT2,
					ISNULL(CONVERT(varchar(10), ES.EvnStick_mseExamDT,20),'') as MSE_DT3,
					'' as MSE_INVALID_GROUP,
					ISNULL(SLT.StickLeaveType_Code,'') as MSE_RESULT,
					ISNULL(CONVERT(varchar(10),ESWR1.evnStickWorkRelease_begDT,20),'') as TREAT1_DT1,
					ISNULL(CONVERT(varchar(10),ESWR1.evnStickWorkRelease_endDT,20),'') as TREAT1_DT2,
					SUBSTRING(ISNULL(MPWR1.Dolgnost_Name,''),0,50) as TREAT1_DOCTOR_ROLE,
					ISNULL(MPWR1.Person_Fio,'') as TREAT1_DOCTOR,
					ISNULL((select top 1 MedPersonal_TabCode from v_MedPersonal with (nolock) where MedPersonal_id = MPWR1.MedPersonal_id),'') as TREAT1_DOC_ID,
					'' as TREAT1_DOCTOR2_ROLE,
					'' as TREAT1_CHAIRMAN_VK,
					'' as TREAT1_DOC2_ID,
					ISNULL(CONVERT(varchar(10),ESWR2.evnStickWorkRelease_begDT,20),'') as TREAT2_DT1,
					ISNULL(CONVERT(varchar(10),ESWR2.evnStickWorkRelease_endDT,20),'') as TREAT2_DT2,
					SUBSTRING(ISNULL(MPWR2.Dolgnost_Name,''),0,50) as TREAT2_DOCTOR_ROLE,
					ISNULL(MPWR2.Person_Fio,'') as TREAT2_DOCTOR,
					ISNULL((select top 1 MedPersonal_TabCode from v_MedPersonal with (nolock) where MedPersonal_id = MPWR2.MedPersonal_id),'') as TREAT2_DOC_ID,
					'' as TREAT2_DOCTOR2_ROLE,
					'' as TREAT2_CHAIRMAN_VK,
					'' as TREAT2_DOC2_ID,
					ISNULL(CONVERT(varchar(10),ESWR3.evnStickWorkRelease_begDT,20),'') as TREAT3_DT1,
					ISNULL(CONVERT(varchar(10),ESWR3.evnStickWorkRelease_endDT,20),'') as TREAT3_DT2,
					SUBSTRING(ISNULL(MPWR3.Dolgnost_Name,''),0,50) as TREAT3_DOCTOR_ROLE,
					ISNULL(MPWR3.Person_Fio,'') as TREAT3_DOCTOR,
					ISNULL((select top 1 MedPersonal_TabCode from v_MedPersonal with (nolock) where MedPersonal_id = MPWR3.MedPersonal_id),'') as TREAT3_DOC_ID,
					'' as TREAT3_DOCTOR2_ROLE,
					'' as TREAT3_CHAIRMAN_VK,
					'' as TREAT3_DOC2_ID,
					'' as OTHER_STATE_DT,
					ISNULL(CONVERT(varchar(10),ES.EvnStick_disDate,20),'') as RETURN_DATE_LPU,
					ISNULL(ES_NEXT.EvnStick_Num,'') as NEXT_LN_CODE
				from v_EvnStick ES with (nolock)
				left join Org O with(nolock) on O.Org_id = ES.Org_id
				outer apply (
					select top 1 
						ES_TEMP.EvnStick_Num
					from v_EvnStick ES_TEMP with (nolock)
					where ES_TEMP.EvnStick_setDate <= ES.EvnStick_setDate
						and ES_TEMP.EvnStick_id <> ES.EvnStick_id
						and ES_TEMP.EvnStick_pid = ES.EvnStick_pid
					order by ES_TEMP.EvnStick_setDate desc
				) ES_PREV
				left join v_StickOrder SO with(nolock) on SO.StickOrder_id = ES.StickOrder_id
				left join v_Lpu L with(nolock) on L.Lpu_id = ES.Lpu_id
				left join v_PersonState PS with(nolock) on PS.Person_id = ES.Person_id
				left join v_Sex S with(nolock) on S.Sex_id = PS.Sex_id
				left join v_StickCause SC with(nolock) on SC.StickCause_id=ES.StickCause_id
				left join v_StickCauseDopType SCDT with(nolock) on SCDT.StickCauseDopType_id = ES.StickCauseDopType_id
				left join v_StickCause SC2 with(nolock) on SC2.StickCause_id = ES.StickCause_did
				left join v_Org Org with(nolock) on Org.Org_id = ES.Org_did
				outer apply (
					select top 1
						ESCP_Temp.EvnStickCarePerson_id,
						ESCP_Temp.RelatedLinkType_id,
						ESCP_Temp.Person_id
                    from v_EvnStickCarePerson ESCP_Temp with (nolock)
                    where ESCP_Temp.Evn_id = ES.EvnStick_id
                    order by ESCP_Temp.EvnStickCarePerson_id desc
				) ESCP1
				outer apply (
					select top 1 
						ESCP_Temp.EvnStickCarePerson_id,
						ESCP_Temp.RelatedLinkType_id,
						ESCP_Temp.Person_id
                    from v_EvnStickCarePerson ESCP_Temp with (nolock)
                    where ESCP_Temp.Evn_id = ES.EvnStick_id
                    and ESCP_Temp.EvnStickCarePerson_id <> ESCP1.EvnStickCarePerson_id
                    order by ESCP_Temp.EvnStickCarePerson_id desc
				) ESCP2

				left join v_PersonState PSSCP1 with(nolock) on PSSCP1.Person_id = ESCP1.Person_id
				left join v_PersonState PSSCP2 with(nolock) on PSSCP2.Person_id = ESCP2.Person_id
				left join v_RelatedLinkType RLT1 with(nolock) on RLT1.RelatedLinkType_id=ESCP1.RelatedLinkType_id
				left join v_RelatedLinkType RLT2 with(nolock) on RLT2.RelatedLinkType_id=ESCP2.RelatedLinkType_id
				left join v_StickIrregularity SI with(nolock) on SI.StickIrregularity_id=ES.StickIrregularity_id
				left join v_StickLeaveType SLT with (nolock) on SLT.StickLeaveType_id=ES.StickLeaveType_id
				outer apply (
					select top 1 
						ESWR_Temp.EvnStickWorkRelease_id,
						ESWR_Temp.MedPersonal_id,
						ESWR_Temp.evnStickWorkRelease_begDT,
						ESWR_Temp.evnStickWorkRelease_endDT
                    from v_EvnStickWorkRelease ESWR_Temp with (nolock)
                    where ESWR_Temp.EvnStickBase_id = ES.EvnStick_id
                    order by ESWR_Temp.evnStickWorkRelease_begDT
				) ESWR1
				outer apply (
					select top 1 
						ESWR_Temp.EvnStickWorkRelease_id,
						ESWR_Temp.MedPersonal_id,
						ESWR_Temp.evnStickWorkRelease_begDT,
						ESWR_Temp.evnStickWorkRelease_endDT
                    from v_EvnStickWorkRelease ESWR_Temp with (nolock)
                    where ESWR_Temp.EvnStickBase_id = ES.EvnStick_id
                    and ESWR_Temp.EvnStickWorkRelease_id <> ESWR1.EvnStickWorkRelease_id
                    order by ESWR_Temp.evnStickWorkRelease_begDT
				) ESWR2
				outer apply (
					select top 1 
						ESWR_Temp.EvnStickWorkRelease_id,
						ESWR_Temp.MedPersonal_id,
						ESWR_Temp.evnStickWorkRelease_begDT,
						ESWR_Temp.evnStickWorkRelease_endDT
                    from v_EvnStickWorkRelease ESWR_Temp with (nolock)
                    where ESWR_Temp.EvnStickBase_id = ES.EvnStick_id
                    and ESWR_Temp.EvnStickWorkRelease_id <> ESWR1.EvnStickWorkRelease_id
                    and ESWR_Temp.EvnStickWorkRelease_id <> ESWR2.EvnStickWorkRelease_id
                    order by ESWR_Temp.evnStickWorkRelease_begDT
				) ESWR3
				outer apply (
					select top 1 
						MP_Temp.MedPersonal_id,
						MP_Temp.Dolgnost_Name,
						MP_Temp.Person_Fio
					from v_MedPersonal MP_Temp with (nolock)
					where MP_Temp.MedPersonal_id = ESWR1.MedPersonal_id
				) MPWR1
				outer apply (
					select top 1 
						MP_Temp.MedPersonal_id,
						MP_Temp.Dolgnost_Name,
						MP_Temp.Person_Fio
                    from v_MedPersonal MP_Temp with (nolock)
                    where MP_Temp.MedPersonal_id = ESWR2.MedPersonal_id
				) MPWR2
				outer apply (
					select top 1 
						MP_Temp.MedPersonal_id,
						MP_Temp.Dolgnost_Name,
						MP_Temp.Person_Fio
                    from v_MedPersonal MP_Temp with (nolock)
                    where MP_Temp.MedPersonal_id = ESWR3.MedPersonal_id
				) MPWR3
				outer apply (
					select top 1 
						ES_TEMP.EvnStick_id,
						ES_TEMP.EvnStick_Num
                    from v_EvnStick ES_TEMP with (nolock)
                    where ES_TEMP.EvnStick_setDate > ES.EvnStick_setDate
                    and ES_TEMP.EvnStick_id <> ES.EvnStick_id
                    and ES_TEMP.EvnStick_pid = ES.EvnStick_pid
                    order by ES_TEMP.EvnStick_setDate
				) ES_NEXT
				where ES.EvnStick_id = :EvnStick_id


				union all

				select distinct
					ISNULL(O.Org_Nick,'') as LN_EMPLOYER,
					case when ISNULL(ESD.StickWorkType_id,0)=1 then 1 else 0 end as LN_EMPL_FLAG,
					ISNULL(ESD.EvnStickDop_Num,'') as LN_CODE,
					ISNULL(ES_PREV.EvnStick_Num,'') as PREV_LN_CODE,
					case when ISNULL(SO.StickOrder_Code,0) = 1 then 1 else 0 end as PRIMARY_FLAG,
					case when ISNULL(ES.EvnStick_IsOriginal,0)=1 then 0 else 1 end as DUPLICATE_FLAG,
					ISNULL(CONVERT(varchar(10), ES.EvnStick_setDate,20),'') as LN_DATE,
					ISNULL(L.Lpu_Nick,'') as LPU_NAME,
					ISNULL(L.PAddress_Address,'') as LPU_ADDRESS,
					ISNULL(L.Lpu_OGRN,'') as LPU_OGRN,
					ISNULL(PS.Person_SurName,'') as LN_LAST_NAME,
					ISNULL(PS.Person_FirName,'') as LN_FIRST_NAME,
					ISNULL(PS.Person_SecName,'') as LN_PATRONYMIC,
					ISNULL(CONVERT(varchar(10), PS.Person_BirthDay,20),'') as BIRTHDAY,
					case when ISNULL(S.Sex_Code,0)=1 then 0 else 1 end as GENDER,
					ISNULL(SC.StickCause_Code,'') as REASON1,
					ISNULL(SCDT.StickCauseDopType_Code,'') as REASON2,
					ISNULL(SC2.StickCause_Code,'') as REASON3,
					'' as PARENT_CODE,
					ISNULL(CONVERT(varchar(10),ES.EvnStick_BirthDate,20),ISNULL(CONVERT(varchar(10),ES.EvnStick_sstBegDate,20),'')) as DATE1,
					ISNULL(CONVERT(varchar(10),ES.EvnStick_sstEndDate,20),'') as DATE2,
					ISNULL(ES.EvnStick_sstNum,'') as VOUCHER_NO,
					ISNULL(Org.Org_OGRN,'') as VOUCHER_OGRN,
					ISNULL(dbo.Age2(PSSCP1.Person_BirthDay,ES.EvnStick_setDate),'') as SERV1_AGE,
					ISNULL((DATEDIFF(MONTH,PSSCP1.Person_BirthDay,ES.EvnStick_setDate)-dbo.Age2(PSSCP1.Person_BirthDay,ES.EvnStick_setDate)*12),'') as SERV1_MM,
					ISNULL(RLT1.RelatedLinkType_Code,'') as SERV1_RELATION_CODE,
					ISNULL(PSSCP1.Person_SurName,'')+' '+ISNULL(PSSCP1.Person_FirName,'')+' '+ISNULL(PSSCP1.Person_SecName,'') as SERV1_FIO,
					ISNULL(dbo.Age2(PSSCP2.Person_BirthDay,ES.EvnStick_setDate),'') as SERV2_AGE,
					ISNULL((DATEDIFF(MONTH,PSSCP2.Person_BirthDay,ES.EvnStick_setDate)-dbo.Age2(PSSCP2.Person_BirthDay,ES.EvnStick_setDate)*12),'') as SERV2_MM,
					ISNULL(RLT2.RelatedLinkType_Code,'') as SERV2_RELATION_CODE,
					ISNULL(PSSCP2.Person_SurName,'')+' '+ISNULL(PSSCP2.Person_FirName,'')+' '+ISNULL(PSSCP2.Person_SecName,'') as SERV2_FIO,
					'' as PREGN12W_FLAG,
					'' as BOZ_FLAG,
					ISNULL(CONVERT(varchar(10), ES.EvnStick_stacBegDate,20),'') as HOSPITAL_DT1,
					ISNULL(CONVERT(varchar(10), ES.EvnStick_stacEndDate,20),'') as HOSPITAL_DT2,
					SI.StickIrregularity_Code as HOSPITAL_BREACH_CODE,
					ISNULL(CONVERT(varchar(10), ES.EvnStick_irrDT,20),'') as HOSPITAL_BREACH_DT,
					ISNULL(CONVERT(varchar(10), ES.EvnStick_mseDT,20),'') as MSE_DT1,
					ISNULL(CONVERT(varchar(10), ES.EvnStick_mseRegDT,20),'') as MSE_DT2,
					ISNULL(CONVERT(varchar(10), ES.EvnStick_mseExamDT,20),'') as MSE_DT3,
					'' as MSE_INVALID_GROUP,
					ISNULL(SLT.StickLeaveType_Code,'') as MSE_RESULT,
					ISNULL(CONVERT(varchar(10),ESWR1.evnStickWorkRelease_begDT,20),'') as TREAT1_DT1,
					ISNULL(CONVERT(varchar(10),ESWR1.evnStickWorkRelease_endDT,20),'') as TREAT1_DT2,
					SUBSTRING(ISNULL(MPWR1.Dolgnost_Name,''),0,50) as TREAT1_DOCTOR_ROLE,
					ISNULL(MPWR1.Person_Fio,'') as TREAT1_DOCTOR,
					ISNULL((select top 1 MedPersonal_TabCode from v_MedPersonal with (nolock) where MedPersonal_id = MPWR1.MedPersonal_id),'') as TREAT1_DOC_ID,
					'' as TREAT1_DOCTOR2_ROLE,
					'' as TREAT1_CHAIRMAN_VK,
					'' as TREAT1_DOC2_ID,
					ISNULL(CONVERT(varchar(10),ESWR2.evnStickWorkRelease_begDT,20),'') as TREAT2_DT1,
					ISNULL(CONVERT(varchar(10),ESWR2.evnStickWorkRelease_endDT,20),'') as TREAT2_DT2,
					SUBSTRING(ISNULL(MPWR2.Dolgnost_Name,''),0,50) as TREAT2_DOCTOR_ROLE,
					ISNULL(MPWR2.Person_Fio,'') as TREAT2_DOCTOR,
					ISNULL((select top 1 MedPersonal_TabCode from v_MedPersonal with (nolock) where MedPersonal_id = MPWR2.MedPersonal_id),'') as TREAT2_DOC_ID,
					'' as TREAT2_DOCTOR2_ROLE,
					'' as TREAT2_CHAIRMAN_VK,
					'' as TREAT2_DOC2_ID,
					ISNULL(CONVERT(varchar(10),ESWR3.evnStickWorkRelease_begDT,20),'') as TREAT3_DT1,
					ISNULL(CONVERT(varchar(10),ESWR3.evnStickWorkRelease_endDT,20),'') as TREAT3_DT2,
					SUBSTRING(ISNULL(MPWR3.Dolgnost_Name,''),0,50) as TREAT3_DOCTOR_ROLE,
					ISNULL(MPWR3.Person_Fio,'') as TREAT3_DOCTOR,
					ISNULL((select top 1 MedPersonal_TabCode from v_MedPersonal with (nolock) where MedPersonal_id = MPWR3.MedPersonal_id),'') as TREAT3_DOC_ID,
					'' as TREAT3_DOCTOR2_ROLE,
					'' as TREAT3_CHAIRMAN_VK,
					'' as TREAT3_DOC2_ID,
					'' as OTHER_STATE_DT,
					ISNULL(CONVERT(varchar(10),ES.EvnStick_disDate,20),'') as RETURN_DATE_LPU,
					ISNULL(ES_NEXT.EvnStick_Num,'') as NEXT_LN_CODE
				from v_EvnStickDop ESD with (nolock)
				left join v_EvnStick ES with (nolock) on ES.EvnStick_id = ESD.EvnStickDop_pid
				left join Org O with(nolock) on O.Org_id = ESD.Org_id
				outer apply (
					select top 1 
						ES_TEMP.EvnStick_Num
					from v_EvnStick ES_TEMP with (nolock)
					where ES_TEMP.EvnStick_setDate <= ES.EvnStick_setDate
						and ES_TEMP.EvnStick_id <> ES.EvnStick_id
						and ES_TEMP.EvnStick_pid = ES.EvnStick_pid
					order by ES_TEMP.EvnStick_setDate desc
				) ES_PREV
				left join v_StickOrder SO with(nolock) on SO.StickOrder_id = ES.StickOrder_id
				left join v_Lpu L with(nolock) on L.Lpu_id = ESD.Lpu_id
				left join v_PersonState PS with(nolock) on PS.Person_id = ESD.Person_id
				left join v_Sex S with(nolock) on S.Sex_id = PS.Sex_id
				left join v_StickCause SC with(nolock) on SC.StickCause_id=ES.StickCause_id
				left join v_StickCauseDopType SCDT with(nolock) on SCDT.StickCauseDopType_id = ES.StickCauseDopType_id
				left join v_StickCause SC2 with(nolock) on SC2.StickCause_id = ES.StickCause_did
				left join v_Org Org with(nolock) on Org.Org_id = ES.Org_did
				outer apply (
					select top 1
						ESCP_Temp.EvnStickCarePerson_id,
						ESCP_Temp.RelatedLinkType_id,
						ESCP_Temp.Person_id
                    from v_EvnStickCarePerson ESCP_Temp with (nolock)
                    where ESCP_Temp.Evn_id = ES.EvnStick_id
                    order by ESCP_Temp.EvnStickCarePerson_id desc
				) ESCP1
				outer apply (
					select top 1 
						ESCP_Temp.EvnStickCarePerson_id,
						ESCP_Temp.RelatedLinkType_id,
						ESCP_Temp.Person_id
                    from v_EvnStickCarePerson ESCP_Temp with (nolock)
                    where ESCP_Temp.Evn_id = ES.EvnStick_id
                    and ESCP_Temp.EvnStickCarePerson_id <> ESCP1.EvnStickCarePerson_id
                    order by ESCP_Temp.EvnStickCarePerson_id desc
				) ESCP2

				left join v_PersonState PSSCP1 with(nolock) on PSSCP1.Person_id = ESCP1.Person_id
				left join v_PersonState PSSCP2 with(nolock) on PSSCP2.Person_id = ESCP2.Person_id
				left join v_RelatedLinkType RLT1 with(nolock) on RLT1.RelatedLinkType_id=ESCP1.RelatedLinkType_id
				left join v_RelatedLinkType RLT2 with(nolock) on RLT2.RelatedLinkType_id=ESCP2.RelatedLinkType_id
				left join v_StickIrregularity SI with(nolock) on SI.StickIrregularity_id=ES.StickIrregularity_id
				left join v_StickLeaveType SLT with (nolock) on SLT.StickLeaveType_id=ES.StickLeaveType_id
				outer apply (
					select top 1 
						ESWR_Temp.EvnStickWorkRelease_id,
						ESWR_Temp.MedPersonal_id,
						ESWR_Temp.evnStickWorkRelease_begDT,
						ESWR_Temp.evnStickWorkRelease_endDT
                    from v_EvnStickWorkRelease ESWR_Temp with (nolock)
                    where ESWR_Temp.EvnStickBase_id = ES.EvnStick_id
                    order by ESWR_Temp.evnStickWorkRelease_begDT
				) ESWR1
				outer apply (
					select top 1 
						ESWR_Temp.EvnStickWorkRelease_id,
						ESWR_Temp.MedPersonal_id,
						ESWR_Temp.evnStickWorkRelease_begDT,
						ESWR_Temp.evnStickWorkRelease_endDT
                    from v_EvnStickWorkRelease ESWR_Temp with (nolock)
                    where ESWR_Temp.EvnStickBase_id = ES.EvnStick_id
                    and ESWR_Temp.EvnStickWorkRelease_id <> ESWR1.EvnStickWorkRelease_id
                    order by ESWR_Temp.evnStickWorkRelease_begDT
				) ESWR2
				outer apply (
					select top 1 
						ESWR_Temp.EvnStickWorkRelease_id,
						ESWR_Temp.MedPersonal_id,
						ESWR_Temp.evnStickWorkRelease_begDT,
						ESWR_Temp.evnStickWorkRelease_endDT
                    from v_EvnStickWorkRelease ESWR_Temp with (nolock)
                    where ESWR_Temp.EvnStickBase_id = ES.EvnStick_id
                    and ESWR_Temp.EvnStickWorkRelease_id <> ESWR1.EvnStickWorkRelease_id
                    and ESWR_Temp.EvnStickWorkRelease_id <> ESWR2.EvnStickWorkRelease_id
                    order by ESWR_Temp.evnStickWorkRelease_begDT
				) ESWR3
				outer apply (
					select top 1 
						MP_Temp.MedPersonal_id,
						MP_Temp.Dolgnost_Name,
						MP_Temp.Person_Fio
					from v_MedPersonal MP_Temp with (nolock)
					where MP_Temp.MedPersonal_id = ESWR1.MedPersonal_id
				) MPWR1
                outer apply (
					select top 1 
						MP_Temp.MedPersonal_id,
						MP_Temp.Dolgnost_Name,
						MP_Temp.Person_Fio
                    from v_MedPersonal MP_Temp with (nolock)
                    where MP_Temp.MedPersonal_id = ESWR2.MedPersonal_id
				) MPWR2
                outer apply (
					select top 1 
						MP_Temp.MedPersonal_id,
						MP_Temp.Dolgnost_Name,
						MP_Temp.Person_Fio
                    from v_MedPersonal MP_Temp with (nolock)
                    where MP_Temp.MedPersonal_id = ESWR3.MedPersonal_id
				) MPWR3
                outer apply (
					select top 1 
						ES_TEMP.EvnStick_id,
						ES_TEMP.EvnStick_Num
                    from v_EvnStick ES_TEMP with (nolock)
                    where ES_TEMP.EvnStick_setDate > ES.EvnStick_setDate
                    and ES_TEMP.EvnStick_id <> ES.EvnStick_id
                    and ES_TEMP.EvnStick_pid = ES.EvnStick_pid
                    order by ES_TEMP.EvnStick_setDate
				) ES_NEXT
				where ESD.EvnStickDop_id = :EvnStick_id
		";
		$result = $this->db->query($query,
			array(
				'EvnStick_id' => $data['EvnStick_id']
			)
		);
		if ( is_object($result) ) {
			$res = $result->result('array');
			if ( is_array($res) && count($res) > 0 ) {
				return $res[0];
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Формирование изображения штрих-кода
	 */
	function getDatamatrixImage($s) {
		$this->load->library('Datamatrix_Image');
		if ((!isset($s)) || (strlen(trim($s)) == 0)) {
			exit();
		}
		//Добавил для определения условия, при котором нужно менять масштаб.
		$dataCodeWords = array();
		$n = 0;
		$len = strlen($s);
		for ($i=0; $i<$len; $i++){
			$c = ord($s[$i]);
			if ($c > 127) {
				$dataCodeWords[$n] = 235;
				$c -= 127;
				$n++;
			} else if (($c>=48 && $c<=57) && ($i+1<$len) && (preg_match('`[0-9]`', $s[$i+1]))) {
				$c = (($c - 48) * 10) + intval($s[$i+1]);
				$c += 130;
				$i++;
			} else $c++;
			$dataCodeWords[$n] = $c;
			$n++;
		}
		//var_dump(count($dataCodeWords));die;
		if(count($dataCodeWords)<=816)
		{
			$center = 300;
			$width = 5.5;
			$size = 600;
		}
		else{
			$center = 300;
			$width = 4.2;
			$size = 600;
		}

		@header('Content-type: image/gif');
		@header('Pragma: no-cache');
        $angle = 0; // rotation in degrees
        $type = 'datamatrix';
        $code = $s;
        $im = imagecreatetruecolor(601, 601);
        $black = ImageColorAllocate($im,0x00,0x00,0x00);
        $white = ImageColorAllocate($im,0xff,0xff,0xff);
        imagefilledrectangle($im, 0, 0, $size, $size, $white);
        $data = Barcode::gd($im, $black, $center, $center, $angle, $type,   array('code'=>$code), $width);

        $im150=ImageCreateTrueColor(150,150);

        imagecopyresampled($im150, $im, 0,0,0,0,150,150,$size,$size);
        imagegif($im150);
        imagedestroy($im150);
	}
}