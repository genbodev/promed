<?php
defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Datamatrix_model - модель для получение штрих-кода формата DataMatrix
 * @author       Khorev Sergey (sergey.khorev@yandex.ru)
 * @version      30.10.2013
 */
class Datamatrix_model extends SwPgModel
{
    protected $dateTimeForm20 = "'yyyy-mm-dd hh24:mi:ss'";

    public $log_file = 'barcodesearch.log';
    public $log_file_access_type = 'a';

    /**
     * @param $data
     * @return bool
     * Получение полей для формирования матричного кода. На входе - id-шникх ЛВНа
     */
    public function GetDatamatrixFields($data)
    {
        $query = "
                    select distinct
                        coalesce(O.Org_Nick, '') as \"LN_EMPLOYER\",
                        case when coalesce(ES.StickWorkType_id,0) = 1 then 1 else 0 end as \"LN_EMPL_FLAG\",
                        coalesce(ES.EvnStick_Num, '') as \"LN_CODE\",
                        coalesce(ES_PREV.EvnStick_Num, '') as \"PREV_LN_CODE\",
                        case when coalesce(SO.StickOrder_Code, 0) = 1 then 1 else 0 end as \"PRIMARY_FLAG\",
                        case when coalesce(ES.EvnStick_IsOriginal, 0) = 1 then 0 else 1 end as \"DUPLICATE_FLAG\",
                        coalesce(to_char(ES.EvnStick_setDate, {$this->dateTimeForm20}),'') as \"LN_DATE\",
                        coalesce(L.Lpu_Nick,'') as \"LPU_NAME\",
                        coalesce(L.PAddress_Address, '') as \"LPU_ADDRESS\",
                        coalesce(L.Lpu_OGRN, '') as \"LPU_OGRN\",
                        coalesce(PS.Person_SurName, '') as \"LN_LAST_NAME\",
                        coalesce(PS.Person_FirName, '') as \"LN_FIRST_NAME\",
                        coalesce(PS.Person_SecName, '') as \"LN_PATRONYMIC\",
                        coalesce(to_char(PS.Person_BirthDay, {$this->dateTimeForm20}), '') as \"BIRTHDAY\",
                        case when coalesce(S.Sex_Code,0) = 1 then 0 else 1 end as \"GENDER\",
                        coalesce(SC.StickCause_Code,'') as \"REASON1\",
                        coalesce(SCDT.StickCauseDopType_Code,'') as \"REASON2\",
                        coalesce(SC2.StickCause_Code, '') as \"REASON3\",
                        '' as \"PARENT_CODE\",
                        coalesce(to_char(ES.EvnStick_BirthDate, {$this->dateTimeForm20})::varchar, coalesce(to_char(ES.EvnStick_sstBegDate, {$this->dateTimeForm20})::varchar, '')) as \"DATE1\",
                        coalesce(to_char(ES.EvnStick_sstEndDate, {$this->dateTimeForm20}),'') as \"DATE2\",
                        coalesce(ES.EvnStick_sstNum, '') as \"VOUCHER_NO\",
                        coalesce(Org.Org_OGRN, '') as \"VOUCHER_OGRN\",
                        coalesce(dbo.Age2(PSSCP1.Person_BirthDay,ES.EvnStick_setDate), 0) as \"SERV1_AGE\",
                        coalesce((DATEDIFF('MONTH', PSSCP1.Person_BirthDay,ES.EvnStick_setDate) - dbo.Age2(PSSCP1.Person_BirthDay, ES.EvnStick_setDate) * 12), 0) as \"SERV1_MM\",
                        coalesce(RLT1.RelatedLinkType_Code, '') as \"SERV1_RELATION_CODE\",
                        coalesce(PSSCP1.Person_SurName, '') || ' ' || coalesce(PSSCP1.Person_FirName,'') || ' ' || coalesce(PSSCP1.Person_SecName, '') as \"SERV1_FIO\",
                        coalesce(dbo.Age2(PSSCP2.Person_BirthDay,ES.EvnStick_setDate), 0) as \"SERV2_AGE\",
                        coalesce((DATEDIFF('MONTH', PSSCP2.Person_BirthDay, ES.EvnStick_setDate) - dbo.Age2(PSSCP2.Person_BirthDay,ES.EvnStick_setDate) * 12), 0) as \"SERV2_MM\",
                        coalesce(RLT2.RelatedLinkType_Code, '') as \"SERV2_RELATION_CODE\",
                        coalesce(PSSCP2.Person_SurName,'') || ' ' || coalesce(PSSCP2.Person_FirName, '') || ' ' || coalesce(PSSCP2.Person_SecName, '') as \"SERV2_FIO\",
                        '' as \"PREGN12W_FLAG\",
                        '' as \"BOZ_FLAG\",
                        coalesce(to_char(ES.EvnStick_stacBegDate, {$this->dateTimeForm20}), '') as \"HOSPITAL_DT1\",
                        coalesce(to_char(ES.EvnStick_stacEndDate, {$this->dateTimeForm20}), '') as \"HOSPITAL_DT2\",
                        SI.StickIrregularity_Code as \"HOSPITAL_BREACH_CODE\",
                        coalesce(to_char(ES.EvnStick_irrDT, {$this->dateTimeForm20}), '') as \"HOSPITAL_BREACH_DT\",
                        coalesce(to_char(ES.EvnStick_mseDT, {$this->dateTimeForm20}), '') as \"MSE_DT1\",
                        coalesce(to_char(ES.EvnStick_mseRegDT, {$this->dateTimeForm20}), '') as \"MSE_DT2\",
                        coalesce(to_char(ES.EvnStick_mseExamDT, {$this->dateTimeForm20}), '') as \"MSE_DT3\",
                        '' as \"MSE_INVALID_GROUP\",
                        coalesce(SLT.StickLeaveType_Code,'') as \"MSE_RESULT\",
                        coalesce(to_char(ESWR1.evnStickWorkRelease_begDT, {$this->dateTimeForm20}), '') as \"TREAT1_DT1\",
                        coalesce(to_char(ESWR1.evnStickWorkRelease_endDT, {$this->dateTimeForm20}), '') as \"TREAT1_DT2\",
                        SUBSTRING(coalesce(MPWR1.Dolgnost_Name, ''), 0, 50) as \"TREAT1_DOCTOR_ROLE\",
                        coalesce(MPWR1.Person_Fio,'') as \"TREAT1_DOCTOR\",
                        coalesce((select MedPersonal_TabCode from v_MedPersonal where MedPersonal_id = MPWR1.MedPersonal_id limit 1), '') as \"TREAT1_DOC_ID\",
                        '' as \"TREAT1_DOCTOR2_ROLE\",
                        '' as \"TREAT1_CHAIRMAN_VK\",
                        '' as \"TREAT1_DOC2_ID\",
                        coalesce(to_char(ESWR2.evnStickWorkRelease_begDT, {$this->dateTimeForm20}), '') as \"TREAT2_DT1\",
                        coalesce(to_char(ESWR2.evnStickWorkRelease_endDT, {$this->dateTimeForm20}), '') as \"TREAT2_DT2\",
                        SUBSTRING(coalesce(MPWR2.Dolgnost_Name, ''), 0, 50) as \"TREAT2_DOCTOR_ROLE\",
                        coalesce(MPWR2.Person_Fio,'') as \"TREAT2_DOCTOR\",
                        coalesce((select MedPersonal_TabCode from v_MedPersonal where MedPersonal_id = MPWR2.MedPersonal_id limit 1), '') as \"TREAT2_DOC_ID\",
                        '' as \"TREAT2_DOCTOR2_ROLE\",
                        '' as \"TREAT2_CHAIRMAN_VK\",
                        '' as \"TREAT2_DOC2_ID\",
                        coalesce(to_char(ESWR3.evnStickWorkRelease_begDT, {$this->dateTimeForm20}),'') as \"TREAT3_DT1\",
                        coalesce(to_char(ESWR3.evnStickWorkRelease_endDT, {$this->dateTimeForm20}),'') as \"TREAT3_DT2\",
                        SUBSTRING(coalesce(MPWR3.Dolgnost_Name,''),0,50) as \"TREAT3_DOCTOR_ROLE\",
                        coalesce(MPWR3.Person_Fio,'') as \"TREAT3_DOCTOR\",
                        coalesce((select MedPersonal_TabCode from v_MedPersonal where MedPersonal_id = MPWR3.MedPersonal_id limit 1), '') as \"TREAT3_DOC_ID\",
                        '' as \"TREAT3_DOCTOR2_ROLE\",
                        '' as \"TREAT3_CHAIRMAN_VK\",
                        '' as \"TREAT3_DOC2_ID\",
                        '' as \"OTHER_STATE_DT\",
                        coalesce(to_char(ES.EvnStick_disDate, {$this->dateTimeForm20}), '') as \"RETURN_DATE_LPU\",
                        coalesce(ES_NEXT.EvnStick_Num, '') as \"NEXT_LN_CODE\"
                    from
                        v_EvnStick ES
                        left join Org O on O.Org_id = ES.Org_id
                        left join lateral (
                            select
                                ES_TEMP.EvnStick_Num
                            from v_EvnStick ES_TEMP
                            where ES_TEMP.EvnStick_setDate <= ES.EvnStick_setDate
                                and ES_TEMP.EvnStick_id <> ES.EvnStick_id
                                and ES_TEMP.EvnStick_pid = ES.EvnStick_pid
                            order by ES_TEMP.EvnStick_setDate desc
                            limit 1
                        ) ES_PREV on true
                        left join v_StickOrder SO on SO.StickOrder_id = ES.StickOrder_id
                        left join v_Lpu L on L.Lpu_id = ES.Lpu_id
                        left join v_PersonState PS on PS.Person_id = ES.Person_id
                        left join v_Sex S on S.Sex_id = PS.Sex_id
                        left join v_StickCause SC on SC.StickCause_id=ES.StickCause_id
                        left join v_StickCauseDopType SCDT on SCDT.StickCauseDopType_id = ES.StickCauseDopType_id
                        left join v_StickCause SC2 on SC2.StickCause_id = ES.StickCause_did
                        left join v_Org Org on Org.Org_id = ES.Org_did
                        left join lateral (
                            select
                                ESCP_Temp.EvnStickCarePerson_id,
                                ESCP_Temp.RelatedLinkType_id,
                                ESCP_Temp.Person_id
                            from
                                v_EvnStickCarePerson ESCP_Temp
                            where
                                ESCP_Temp.Evn_id = ES.EvnStick_id
                            order by
                                ESCP_Temp.EvnStickCarePerson_id desc
                            limit 1
                        ) ESCP1 on true
                        left join lateral (
                            select 
                                ESCP_Temp.EvnStickCarePerson_id,
                                ESCP_Temp.RelatedLinkType_id,
                                ESCP_Temp.Person_id
                            from
                                v_EvnStickCarePerson ESCP_Temp
                            where
                                ESCP_Temp.Evn_id = ES.EvnStick_id
                            and
                                ESCP_Temp.EvnStickCarePerson_id <> ESCP1.EvnStickCarePerson_id
                            order by ESCP_Temp.EvnStickCarePerson_id desc
                            limit 1
                        ) ESCP2 on true
        
                        left join v_PersonState PSSCP1 on PSSCP1.Person_id = ESCP1.Person_id
                        left join v_PersonState PSSCP2 on PSSCP2.Person_id = ESCP2.Person_id
                        left join v_RelatedLinkType RLT1 on RLT1.RelatedLinkType_id=ESCP1.RelatedLinkType_id
                        left join v_RelatedLinkType RLT2 on RLT2.RelatedLinkType_id=ESCP2.RelatedLinkType_id
                        left join v_StickIrregularity SI on SI.StickIrregularity_id=ES.StickIrregularity_id
                        left join v_StickLeaveType SLT on SLT.StickLeaveType_id=ES.StickLeaveType_id
                        left join lateral (
                            select 
                                ESWR_Temp.EvnStickWorkRelease_id,
                                ESWR_Temp.MedPersonal_id,
                                ESWR_Temp.evnStickWorkRelease_begDT,
                                ESWR_Temp.evnStickWorkRelease_endDT
                            from
                                v_EvnStickWorkRelease ESWR_Temp
                            where
                                ESWR_Temp.EvnStickBase_id = ES.EvnStick_id
                            order by
                                ESWR_Temp.evnStickWorkRelease_begDT
                            limit 1
                        ) ESWR1 on true
                        left join lateral (
                            select
                                ESWR_Temp.EvnStickWorkRelease_id,
                                ESWR_Temp.MedPersonal_id,
                                ESWR_Temp.evnStickWorkRelease_begDT,
                                ESWR_Temp.evnStickWorkRelease_endDT
                            from v_EvnStickWorkRelease ESWR_Temp
                            where ESWR_Temp.EvnStickBase_id = ES.EvnStick_id
                            and ESWR_Temp.EvnStickWorkRelease_id <> ESWR1.EvnStickWorkRelease_id
                            order by ESWR_Temp.evnStickWorkRelease_begDT
                            limit 1
                        ) ESWR2 on true
                        left join lateral (
                            select
                                ESWR_Temp.EvnStickWorkRelease_id,
                                ESWR_Temp.MedPersonal_id,
                                ESWR_Temp.evnStickWorkRelease_begDT,
                                ESWR_Temp.evnStickWorkRelease_endDT
                            from
                                v_EvnStickWorkRelease ESWR_Temp
                            where
                                ESWR_Temp.EvnStickBase_id = ES.EvnStick_id
                            and
                                ESWR_Temp.EvnStickWorkRelease_id <> ESWR1.EvnStickWorkRelease_id
                            and
                                ESWR_Temp.EvnStickWorkRelease_id <> ESWR2.EvnStickWorkRelease_id
                            order by ESWR_Temp.evnStickWorkRelease_begDT
                            limit 1
                        ) ESWR3 on true
                        left join lateral (
                            select
                                MP_Temp.MedPersonal_id,
                                MP_Temp.Dolgnost_Name,
                                MP_Temp.Person_Fio
                            from
                                v_MedPersonal MP_Temp
                            where
                                MP_Temp.MedPersonal_id = ESWR1.MedPersonal_id
                            limit 1
                        ) MPWR1 on true
                        left join lateral (
                            select
                                MP_Temp.MedPersonal_id,
                                MP_Temp.Dolgnost_Name,
                                MP_Temp.Person_Fio
                            from
                                v_MedPersonal MP_Temp
                            where
                                MP_Temp.MedPersonal_id = ESWR2.MedPersonal_id
                            limit 1
                        ) MPWR2 on true
                        left join lateral (
                            select
                                MP_Temp.MedPersonal_id,
                                MP_Temp.Dolgnost_Name,
                                MP_Temp.Person_Fio
                            from
                                v_MedPersonal MP_Temp
                            where
                                MP_Temp.MedPersonal_id = ESWR3.MedPersonal_id
                            limit 1
                        ) MPWR3 on true
                        left join lateral (
                            select
                                ES_TEMP.EvnStick_id,
                                ES_TEMP.EvnStick_Num
                            from
                                v_EvnStick ES_TEMP
                            where
                                ES_TEMP.EvnStick_setDate > ES.EvnStick_setDate
                            and
                                ES_TEMP.EvnStick_id <> ES.EvnStick_id
                            and
                                ES_TEMP.EvnStick_pid = ES.EvnStick_pid
                            order by ES_TEMP.EvnStick_setDate
                            limit 1
                        ) ES_NEXT on true
                    where ES.EvnStick_id = :EvnStick_id


				union all

                    select distinct
                        coalesce(O.Org_Nick, '') as \"LN_EMPLOYER\",
                        case when coalesce(ESD.StickWorkType_id, 0) = 1 then 1 else 0 end as \"LN_EMPL_FLAG\",
                        coalesce(ESD.EvnStickDop_Num,'') as \"LN_CODE\",
                        coalesce(ES_PREV.EvnStick_Num, '') as \"PREV_LN_CODE\",
                        case when coalesce(SO.StickOrder_Code, 0) = 1 then 1 else 0 end as \"PRIMARY_FLAG\",
                        case when coalesce(ES.EvnStick_IsOriginal,0)=1 then 0 else 1 end as \"DUPLICATE_FLAG\",
                        coalesce(to_char(ES.EvnStick_setDate, {$this->dateTimeForm20}),'') as \"LN_DATE\",
                        coalesce(L.Lpu_Nick, '') as \"LPU_NAME\",
                        coalesce(L.PAddress_Address,'') as \"LPU_ADDRESS\",
                        coalesce(L.Lpu_OGRN,'') as \"LPU_OGRN\",
                        coalesce(PS.Person_SurName, '') as \"LN_LAST_NAME\",
                        coalesce(PS.Person_FirName, '') as \"LN_FIRST_NAME\",
                        coalesce(PS.Person_SecName, '') as \"LN_PATRONYMIC\",
                        coalesce(to_char(PS.Person_BirthDay, {$this->dateTimeForm20}), '') as \"BIRTHDAY\",
                        case when coalesce(S.Sex_Code, 0) = 1 then 0 else 1 end as \"GENDER\",
                        coalesce(SC.StickCause_Code, '') as \"REASON1\",
                        coalesce(SCDT.StickCauseDopType_Code, '') as \"REASON2\",
                        coalesce(SC2.StickCause_Code, '') as \"REASON3\",
                        '' as \"PARENT_CODE\",
                        coalesce(to_char(ES.EvnStick_BirthDate, {$this->dateTimeForm20})::varchar, coalesce(to_char(ES.EvnStick_sstBegDate, {$this->dateTimeForm20})::varchar, '')) as \"DATE1\",
                        coalesce(to_char(ES.EvnStick_sstEndDate, {$this->dateTimeForm20}), '') as \"DATE2\",
                        coalesce(ES.EvnStick_sstNum, '') as \"VOUCHER_NO\",
                        coalesce(Org.Org_OGRN, '') as \"VOUCHER_OGRN\",
                        coalesce(dbo.Age2(PSSCP1.Person_BirthDay,ES.EvnStick_setDate), 0) as \"SERV1_AGE\",
                        coalesce((DATEDIFF('MONTH',PSSCP1.Person_BirthDay, ES.EvnStick_setDate) - dbo.Age2(PSSCP1.Person_BirthDay, ES.EvnStick_setDate) * 12), 0) as \"SERV1_MM\",
                        coalesce(RLT1.RelatedLinkType_Code, '') as \"SERV1_RELATION_CODE\",
                        coalesce(PSSCP1.Person_SurName,'') ||' ' || coalesce(PSSCP1.Person_FirName, '') || ' ' || coalesce(PSSCP1.Person_SecName, '') as \"SERV1_FIO\",
                        coalesce(dbo.Age2(PSSCP2.Person_BirthDay, ES.EvnStick_setDate), 0) as \"SERV2_AGE\",
                        coalesce((DATEDIFF('MONTH', PSSCP2.Person_BirthDay, ES.EvnStick_setDate) - dbo.Age2(PSSCP2.Person_BirthDay, ES.EvnStick_setDate) * 12), 0) as \"SERV2_MM\",
                        coalesce(RLT2.RelatedLinkType_Code, '') as \"SERV2_RELATION_CODE\",
                        coalesce(PSSCP2.Person_SurName,'') ||' ' || coalesce(PSSCP2.Person_FirName, '') || ' ' || coalesce(PSSCP2.Person_SecName,'') as \"SERV2_FIO\",
                        '' as \"PREGN12W_FLAG\",
                        '' as \"BOZ_FLAG\",
                        coalesce(to_char(ES.EvnStick_stacBegDate, {$this->dateTimeForm20}), '') as \"HOSPITAL_DT1\",
                        coalesce(to_char(ES.EvnStick_stacEndDate, {$this->dateTimeForm20}), '') as \"HOSPITAL_DT2\",
                        SI.StickIrregularity_Code as \"HOSPITAL_BREACH_CODE\",
                        coalesce(to_char(ES.EvnStick_irrDT, {$this->dateTimeForm20}), '') as \"HOSPITAL_BREACH_DT\",
                        coalesce(to_char(ES.EvnStick_mseDT, {$this->dateTimeForm20}), '') as \"MSE_DT1\",
                        coalesce(to_char(ES.EvnStick_mseRegDT, {$this->dateTimeForm20}), '') as \"MSE_DT2\",
                        coalesce(to_char(ES.EvnStick_mseExamDT, {$this->dateTimeForm20}), '') as \"MSE_DT3\",
                        '' as \"MSE_INVALID_GROUP\",
                        coalesce(SLT.StickLeaveType_Code,'') as \"MSE_RESULT\",
                        coalesce(to_char(ESWR1.evnStickWorkRelease_begDT, {$this->dateTimeForm20}), '') as \"TREAT1_DT1\",
                        coalesce(to_char(ESWR1.evnStickWorkRelease_endDT, {$this->dateTimeForm20}), '') as \"TREAT1_DT2\",
                        SUBSTRING(coalesce(MPWR1.Dolgnost_Name, ''), 0, 50) as \"TREAT1_DOCTOR_ROLE\",
                        coalesce(MPWR1.Person_Fio, '') as \"TREAT1_DOCTOR\",
                        coalesce((select MedPersonal_TabCode from v_MedPersonal where MedPersonal_id = MPWR1.MedPersonal_id limit 1),'') as \"TREAT1_DOC_ID\",
                        '' as \"TREAT1_DOCTOR2_ROLE\",
                        '' as \"TREAT1_CHAIRMAN_VK\",
                        '' as \"TREAT1_DOC2_ID\",
                        coalesce(to_char(ESWR2.evnStickWorkRelease_begDT, {$this->dateTimeForm20}), '') as \"TREAT2_DT1\",
                        coalesce(to_char(ESWR2.evnStickWorkRelease_endDT, {$this->dateTimeForm20}), '') as \"TREAT2_DT2\",
                        SUBSTRING(coalesce(MPWR2.Dolgnost_Name, ''), 0, 50) as \"TREAT2_DOCTOR_ROLE\",
                        coalesce(MPWR2.Person_Fio,'') as \"TREAT2_DOCTOR\",
                        coalesce((select MedPersonal_TabCode from v_MedPersonal where MedPersonal_id = MPWR2.MedPersonal_id limit 1),'') as \"TREAT2_DOC_ID\",
                        '' as \"TREAT2_DOCTOR2_ROLE\",
                        '' as \"TREAT2_CHAIRMAN_VK\",
                        '' as \"TREAT2_DOC2_ID\",
                        coalesce(to_char(ESWR3.evnStickWorkRelease_begDT, {$this->dateTimeForm20}), '') as \"TREAT3_DT1\",
                        coalesce(to_char(ESWR3.evnStickWorkRelease_endDT, {$this->dateTimeForm20}), '') as \"TREAT3_DT2\",
                        SUBSTRING(coalesce(MPWR3.Dolgnost_Name, ''), 0, 50) as \"TREAT3_DOCTOR_ROLE\",
                        coalesce(MPWR3.Person_Fio, '') as \"TREAT3_DOCTOR\",
                        coalesce((select MedPersonal_TabCode from v_MedPersonal where MedPersonal_id = MPWR3.MedPersonal_id limit 1),'') as \"TREAT3_DOC_ID\",
                        '' as \"TREAT3_DOCTOR2_ROLE\",
                        '' as \"TREAT3_CHAIRMAN_VK\",
                        '' as \"TREAT3_DOC2_ID\",
                        '' as \"OTHER_STATE_DT\",
                        coalesce(to_char(ES.EvnStick_disDate, {$this->dateTimeForm20}), '') as \"RETURN_DATE_LPU\",
                        coalesce(ES_NEXT.EvnStick_Num,'') as \"NEXT_LN_CODE\"
                    from
                        v_EvnStickDop ESD
                        left join v_EvnStick ES on ES.EvnStick_id = ESD.EvnStickDop_pid
                        left join Org O on O.Org_id = ESD.Org_id
                        left join lateral (
                            select 
                                ES_TEMP.EvnStick_Num
                            from
                                v_EvnStick ES_TEMP
                            where
                                ES_TEMP.EvnStick_setDate <= ES.EvnStick_setDate
                            and
                                ES_TEMP.EvnStick_id <> ES.EvnStick_id
                            and
                                ES_TEMP.EvnStick_pid = ES.EvnStick_pid
                            order by
                                ES_TEMP.EvnStick_setDate desc
                            limit 1
                        ) ES_PREV on true
                        left join v_StickOrder SO on SO.StickOrder_id = ES.StickOrder_id
                        left join v_Lpu L on L.Lpu_id = ESD.Lpu_id
                        left join v_PersonState PS on PS.Person_id = ESD.Person_id
                        left join v_Sex S on S.Sex_id = PS.Sex_id
                        left join v_StickCause SC on SC.StickCause_id=ES.StickCause_id
                        left join v_StickCauseDopType SCDT on SCDT.StickCauseDopType_id = ES.StickCauseDopType_id
                        left join v_StickCause SC2 on SC2.StickCause_id = ES.StickCause_did
                        left join v_Org Org on Org.Org_id = ES.Org_did
                        left join lateral (
                            select
                                ESCP_Temp.EvnStickCarePerson_id,
                                ESCP_Temp.RelatedLinkType_id,
                                ESCP_Temp.Person_id
                            from
                                v_EvnStickCarePerson ESCP_Temp
                            where
                                ESCP_Temp.Evn_id = ES.EvnStick_id
                            order by ESCP_Temp.EvnStickCarePerson_id desc
                            limit 1
                        ) ESCP1 on true
                        left join lateral (
                            select 
                                ESCP_Temp.EvnStickCarePerson_id,
                                ESCP_Temp.RelatedLinkType_id,
                                ESCP_Temp.Person_id
                            from
                                v_EvnStickCarePerson ESCP_Temp
                            where
                                ESCP_Temp.Evn_id = ES.EvnStick_id
                            and
                                ESCP_Temp.EvnStickCarePerson_id <> ESCP1.EvnStickCarePerson_id
                            order by ESCP_Temp.EvnStickCarePerson_id desc
                            limit 1
                        ) ESCP2 on true
        
                        left join v_PersonState PSSCP1 on PSSCP1.Person_id = ESCP1.Person_id
                        left join v_PersonState PSSCP2 on PSSCP2.Person_id = ESCP2.Person_id
                        left join v_RelatedLinkType RLT1 on RLT1.RelatedLinkType_id=ESCP1.RelatedLinkType_id
                        left join v_RelatedLinkType RLT2 on RLT2.RelatedLinkType_id=ESCP2.RelatedLinkType_id
                        left join v_StickIrregularity SI on SI.StickIrregularity_id=ES.StickIrregularity_id
                        left join v_StickLeaveType SLT on SLT.StickLeaveType_id=ES.StickLeaveType_id
                        left join lateral (
                            select
                                ESWR_Temp.EvnStickWorkRelease_id,
                                ESWR_Temp.MedPersonal_id,
                                ESWR_Temp.evnStickWorkRelease_begDT,
                                ESWR_Temp.evnStickWorkRelease_endDT
                            from
                                v_EvnStickWorkRelease ESWR_Temp
                            where
                                ESWR_Temp.EvnStickBase_id = ES.EvnStick_id
                            order by ESWR_Temp.evnStickWorkRelease_begDT
                            limit 1
                        ) ESWR1 on true
                        left join lateral (
                            select
                                ESWR_Temp.EvnStickWorkRelease_id,
                                ESWR_Temp.MedPersonal_id,
                                ESWR_Temp.evnStickWorkRelease_begDT,
                                ESWR_Temp.evnStickWorkRelease_endDT
                            from
                                v_EvnStickWorkRelease ESWR_Temp
                            where
                                ESWR_Temp.EvnStickBase_id = ES.EvnStick_id
                            and
                                ESWR_Temp.EvnStickWorkRelease_id <> ESWR1.EvnStickWorkRelease_id
                            order by ESWR_Temp.evnStickWorkRelease_begDT
                            limit 1
                        ) ESWR2 on true
                        left join lateral (
                            select
                                ESWR_Temp.EvnStickWorkRelease_id,
                                ESWR_Temp.MedPersonal_id,
                                ESWR_Temp.evnStickWorkRelease_begDT,
                                ESWR_Temp.evnStickWorkRelease_endDT
                            from
                                v_EvnStickWorkRelease ESWR_Temp
                            where
                                ESWR_Temp.EvnStickBase_id = ES.EvnStick_id
                            and
                                ESWR_Temp.EvnStickWorkRelease_id <> ESWR1.EvnStickWorkRelease_id
                            and
                                ESWR_Temp.EvnStickWorkRelease_id <> ESWR2.EvnStickWorkRelease_id
                            order by ESWR_Temp.evnStickWorkRelease_begDT
                            limit 1
                        ) ESWR3 on true
                        left join lateral (
                            select
                                MP_Temp.MedPersonal_id,
                                MP_Temp.Dolgnost_Name,
                                MP_Temp.Person_Fio
                            from
                                v_MedPersonal MP_Temp
                            where MP_Temp.MedPersonal_id = ESWR1.MedPersonal_id
                            limit 1
                        ) MPWR1 on true
                        left join lateral (
                            select
                                MP_Temp.MedPersonal_id,
                                MP_Temp.Dolgnost_Name,
                                MP_Temp.Person_Fio
                            from
                                v_MedPersonal MP_Temp
                            where
                                MP_Temp.MedPersonal_id = ESWR2.MedPersonal_id
                            limit 1
                        ) MPWR2 on true
                        left join lateral (
                            select
                                MP_Temp.MedPersonal_id,
                                MP_Temp.Dolgnost_Name,
                                MP_Temp.Person_Fio
                            from
                                v_MedPersonal MP_Temp
                            where
                                MP_Temp.MedPersonal_id = ESWR3.MedPersonal_id
                            limit 1
                        ) MPWR3 on true
                        left join lateral (
                            select
                                ES_TEMP.EvnStick_id,
                                ES_TEMP.EvnStick_Num
                            from
                                v_EvnStick ES_TEMP
                            where
                                ES_TEMP.EvnStick_setDate > ES.EvnStick_setDate
                            and
                                ES_TEMP.EvnStick_id <> ES.EvnStick_id
                            and
                                ES_TEMP.EvnStick_pid = ES.EvnStick_pid
                            order by ES_TEMP.EvnStick_setDate
                            limit 1
                        ) ES_NEXT on true
                    where ESD.EvnStickDop_id = :EvnStick_id
		";
        $result = $this->db->query($query,
            [
                'EvnStick_id' => $data['EvnStick_id']
            ]
        );
        if (!is_object($result)) {
            return false;
        }

        $res = $result->result('array');
        if (is_array($res) && count($res) > 0) {
            return $res[0];
        } else {
            return false;
        }
    }

    /**
     * Формирование изображения штрих-кода
     */
    public function getDatamatrixImage($s)
    {
        $this->load->library('Datamatrix_Image');
        if ((!isset($s)) || (strlen(trim($s)) == 0)) {
            exit();
        }
        //Добавил для определения условия, при котором нужно менять масштаб.
        $dataCodeWords = array();
        $n = 0;
        $len = strlen($s);
        for ($i = 0; $i < $len; $i++) {
            $c = ord($s[$i]);
            if ($c > 127) {
                $dataCodeWords[$n] = 235;
                $c -= 127;
                $n++;
            } else if (($c >= 48 && $c <= 57) && ($i + 1 < $len) && (preg_match('`[0-9]`', $s[$i + 1]))) {
                $c = (($c - 48) * 10) + intval($s[$i + 1]);
                $c += 130;
                $i++;
            } else $c++;
            $dataCodeWords[$n] = $c;
            $n++;
        }
        //var_dump(count($dataCodeWords));die;
        if (count($dataCodeWords) <= 816) {
            $center = 300;
            $width = 5.5;
            $size = 600;
        } else {
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
        $black = ImageColorAllocate($im, 0x00, 0x00, 0x00);
        $white = ImageColorAllocate($im, 0xff, 0xff, 0xff);
        imagefilledrectangle($im, 0, 0, $size, $size, $white);
        $data = Barcode::gd($im, $black, $center, $center, $angle, $type, array('code' => $code), $width);

        $im150 = ImageCreateTrueColor(150, 150);

        imagecopyresampled($im150, $im, 0, 0, 0, 0, 150, 150, $size, $size);
        imagegif($im150);
        imagedestroy($im150);
    }
}