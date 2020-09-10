<?php
defined('BASEPATH') or die ('No direct script access allowed');

/**
 * MiacExport_model - модель для работы с данными для выгрузки для МИАЦ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009 - 2011 Swan Ltd.
 * @author       Pshenitcyn Ivan
 * @version      28.03.2011
 *
 * @property-read CI_DB_active_record $db
 */
class MiacExport_model extends SwPgModel
{

    protected $dateTimeForm120 = "'yyyy-mm-dd hh24:mi:ss'";

    /**
     * Метод сохраняет настройки автоматической выгрузки для МИАЦ
     * @param $data
     * @return bool|int
     */
    public function saveMiacExportSheduleOptions($data)
    {
        if (isset($data['DataStorage_id']) and (int)$data['DataStorage_id'] > 0)
            $p_name = 'p_DataStorage_upd';
        else
            $p_name = 'p_DataStorage_ins';

        $sql = "
		    select 
		        Error_Message as \"Error_Msg\",
		        Error_Code as \"Error_Code\",
		        DataStorage_id as \"DataStorage_id\"
			from " . $p_name . "
			(
                DataStorage_id := :DataStorage_id,
                Lpu_id := :Lpu_id,
                DataStorage_Name := 'miac_export_shedule',
                DataStorage_Value := :SaveData,
                pmUser_id := :pmUser_id
			)
		";
        $res = $this->db->query($sql, $data);

        if (!is_object($res))
            return false;

        $sel = $res->result('array');

        if (!count($sel) && (int)$sel[0]['DataStorage_id'] > 0)
            return (int)$sel[0]['DataStorage_id'];

        return false;
    }

    /**
     * Получение текущих настроек, если не удается получить из БД, то возвращается false
     */
    public function getMiacExportSheduleOptions($data)
    {
        $sql = "
			select
				DataStorage_id as \"DataStorage_id\",
				DataStorage_Value as \"DataStorage_Value\"
			from
				DataStorage
			where
				Lpu_id = :Lpu_id
            and
                DataStorage_Name = 'miac_export_shedule'
			order by
				DataStorage_updDT desc
			limit 1
		";
        $res = $this->db->query($sql, $data);
        if (!is_object($res))
            return false;

        $sel = $res->result('array');
        if (count($sel) == 0)
            return false;

        $ret = json_decode($sel[0]['DataStorage_Value'], true);
        $ret['DataStorage_id'] = $sel[0]['DataStorage_id'];
        return $ret;
    }

    /**
     * Получение параметров автоматической выгрузки для МИАЦ
     *
     * @return array
     */
    function getMiacExportOptions()
    {
        $sql = "
			SELECT
				DataStorage_Value as \"DataStorage_Value\",
				Lpu_id as \"Lpu_id\"
			FROM
				DataStorage	
			WHERE
				DataStorage_Name = 'miac_export_shedule'
		";
        $res = $this->db->query($sql);
        return $res->result('array');
    }

    /**
     * Получение данных о текущей ЛПУ
     * @param $data
     * @return bool|array
     */
    public function getCurrentLpudata($data)
    {
        $params = [];
        $params['Lpu_id'] = $data['Lpu_id'];
        $sql = "
			select
				rtrim(og.Org_INN) as \"Lpu_Inn\",
				rtrim(og.Org_Kpp) as \"Lpu_Kpp\"
			from
				Lpu lp
				inner join Org og on og.Org_id = lp.Org_id
			where
				Lpu_id = :Lpu_id
		";

        $result = $this->db->query($sql, $params);

        if (!is_object($result))
            return false;

        $sel = $result->result('array');
        if (count($sel))
            return $sel[0];
        else
            return false;
    }

    /**
     * получение данных о выписанных рецептах
     *
     * @param $data
     * @return bool|array
     */
    public function getReceptsData($data)
    {
        $params = [];
        $params['Lpu_id'] = $data['Lpu_id'];
        $params['Date1'] = $data['range1'];
        $params['Date2'] = $data['range2'];

        $sql = "
			select
				rtrim(og.Org_INN) as \"Lpu_Inn\",
				rtrim(og.Org_Kpp) as \"Lpu_Kpp\",
				rtrim(mp.MedPersonal_TabCode) as \"MedPersonal_TabCode\",
				rtrim(pers.PersonSocCardNum_SocCardNum) as \"SocCardNum\",
				to_char(pers.Person_BirthDay, {$this->dateTimeForm120}) as \"Person_BirthDay\",
				rtrim(case when RIGHT(rtrim(dg.Diag_Code), 1) = '.' THEN LEFT(rtrim(dg.Diag_Code), length(rtrim(dg.Diag_Code)) - 1) ELSE dg.Diag_Code END) as \"Diag_Code\",
				rtrim(rc.EvnRecept_Ser) as \"EvnRecept_Ser\",
				rtrim(rc.EvnRecept_Num) as \"EvnRecept_Num\",
				to_char(rc.EvnRecept_setDT, {$this->dateTimeForm120}) as \"EvnRecept_setDT\",
				CASE
					WHEN rf.ReceptFinance_Code = 1 or rf.ReceptFinance_Code = 3 THEN 1
					WHEN rf.ReceptFinance_Code = 2 THEN 2				 					
				END as \"ReceptFinance_Code\",
				CASE
					WHEN rf.ReceptFinance_Code = 1 THEN 1
					WHEN rf.ReceptFinance_Code = 2 THEN 3
					WHEN rf.ReceptFinance_Code = 3 THEN 2
				END as \"Const_Privilege\",
				CASE
					WHEN pt.ReceptFinance_id = 1 THEN
						CASE WHEN
								length(pt.PrivilegeType_Code::varchar) = 2
							THEN
								CAST('0' AS varchar(1)) || CAST(pt.PrivilegeType_Code AS varchar(2))
							ELSE
								CAST(pt.PrivilegeType_Code as varchar)
							END
					WHEN pt.ReceptFinance_id = 2 THEN CAST(pt.PrivilegeType_Code AS varchar)					
					WHEN CAST(pt.PrivilegeType_Code as varchar) = '501' THEN '3'
					WHEN CAST(pt.PrivilegeType_Code as varchar) = '502' THEN '2'
					WHEN CAST(pt.PrivilegeType_Code as varchar) = '503' THEN '5'
					WHEN CAST(pt.PrivilegeType_Code as varchar) = '504' THEN '6'
					WHEN CAST(pt.PrivilegeType_Code as varchar) = '505' THEN '1'
					WHEN CAST(pt.PrivilegeType_Code as varchar) = '506' THEN '4'
					WHEN CAST(pt.PrivilegeType_Code as varchar) = '507' THEN '7'
				END as \"PrivilegeReason_Code\",
				CASE
					WHEN rdt.ReceptDelayType_Code = '0' THEN '3'
					WHEN rdt.ReceptDelayType_Code = '1' THEN '2'
					WHEN rdt.ReceptDelayType_Code = '2' THEN '5'
					ELSE '1'
				END AS \"ReceptDelayType\",
				drg.Drug_CodeG as \"Drug_Code\",
				ROUND(rc.EvnRecept_Kolvo, 0) as \"EvnRecept_Kolvo\",
				ogfarm.Org_INN as \"OFarmacy_Inn\",
				ogfarm.Org_KPP as \"OFarmacy_Kpp\",
				to_char(rc.EvnRecept_obrDT, {$this->dateTimeForm120}) as \"ObrDate\",
				to_char(rc.EvnRecept_otpDT, {$this->dateTimeForm120}) as \"OtpDate\",
				CASE WHEN rc.EvnRecept_oKolvo > 0 THEN drg.Drug_CodeG END as \"Drug_OtpCode\",
				ROUND(rc.EvnRecept_oKolvo::integer, 0) as OtpKolvo,
				cast(coalesce(rc.EvnRecept_oPrice, 0.00) as numeric(18,2)) as \"OtpPrice\"
			from
				v_EvnRecept rc
				inner join v_Person_all pers on pers.PersonEvn_id = rc.PersonEvn_id and pers.Server_id = rc.Server_id
				inner join Lpu lp on lp.Lpu_id = rc.Lpu_id
				inner join Org og on og.Org_id = lp.Org_id
				inner join v_MedPersonal mp on mp.MedPersonal_id = rc.MedPersonal_id and mp.Lpu_id = rc.Lpu_id
				inner join Diag dg on dg.Diag_id = rc.Diag_id
				left join ReceptFinance rf on rf.ReceptFinance_id = rc.ReceptFinance_id
				left join ReceptDelayType rdt on rdt.ReceptDelayType_id = rc.ReceptDelayType_id
				left join PrivilegeType pt on pt.PrivilegeType_id = rc.PrivilegeType_id
				left join Drug drg on drg.Drug_id = rc.Drug_id
				left join v_OrgFarmacy ofarm on ofarm.OrgFarmacy_id = rc.OrgFarmacy_oid
				left join v_Org ogfarm on ogfarm.Org_id = ofarm.Org_id
			where
				rc.Lpu_id = :Lpu_id
            and
                rc.EvnRecept_setDT >= :Date1
            and
                rc.EvnRecept_setDT < :Date2
			and
			    pt.ReceptFinance_id != 2
            and
                pers.PersonSocCardNum_SocCardNum is not null
		";

        $result = $this->db->query($sql, $params);

        if (!is_object($result))
            return false;

        return $result->result('array');
    }

    /**
     * получение данных о поликлинических посещениях
     * @param $data
     * @return bool|array
     */
    public function getVisitsData($data)
    {
        $params = [];
        $params['Lpu_id'] = $data['Lpu_id'];
        $params['Date1'] = $data['range1'];
        $params['Date2'] = $data['range2'];

        $sql = "
			select
				rtrim(pers.PersonSocCardNum_SocCardNum) as \"SocCardNum\",
				to_char(pers.Person_BirthDay, {$this->dateTimeForm120}) as \"Person_BirthDay\",
				case when pers.Sex_id = 1 then 'М' else 'Ж' end as \"Person_Sex\",
				CASE
					WHEN Privilege.PrivilegeType_Code = '81' THEN 3
					WHEN Privilege.PrivilegeType_Code = '82' THEN 2
					WHEN Privilege.PrivilegeType_Code = '83' THEN 1
					WHEN Privilege.PrivilegeType_Code = '84' THEN 4
					ELSE 0						
				END as \"Person_DisabilityGroup\",
				CASE
					WHEN street.KLStreet_id is not null and street.KLAdr_Ocatd is not null THEN street.KLAdr_Ocatd
					WHEN town.KLArea_id is not null and town.KLAdr_Ocatd is not null THEN town.KLAdr_Ocatd
					WHEN city.KLArea_id is not null and city.KLAdr_Ocatd is not null THEN city.KLAdr_Ocatd
					WHEN srgn.KLArea_id is not null and srgn.KLAdr_Ocatd is not null THEN srgn.KLAdr_Ocatd
					WHEN rgn.KLArea_id is not null and rgn.KLAdr_Ocatd is not null THEN rgn.KLAdr_Ocatd
					WHEN country.KLArea_id is not null and country.KLAdr_Ocatd is not null THEN country.KLAdr_Ocatd
					ELSE ''
				END as \"Person_OKATO\",
				CASE WHEN addr.KLAreaType_id = 2 THEN 'С' ELSE 'Г' END as \"KLAreaType\",
				CASE
					WHEN PrivilegeReason.ReceptFinance_Code = 1 THEN 1
					WHEN PrivilegeReason.ReceptFinance_Code = 2 THEN 3
					WHEN PrivilegeReason.ReceptFinance_Code = 3 THEN 2
					ELSE 0
				END as \"PrivilegeType_Code\",
				CASE
					WHEN PrivilegeReason.ReceptFinance_id = 1 THEN
						CASE WHEN
								length(PrivilegeReason.PrivilegeType_Code::varchar) = 2
							THEN
								CAST('0' AS varchar(1)) || CAST(PrivilegeReason.PrivilegeType_Code AS varchar(2))
							ELSE
								CAST(PrivilegeReason.PrivilegeType_Code as varchar)
							END
					WHEN PrivilegeReason.ReceptFinance_id = 2 THEN CAST(PrivilegeReason.PrivilegeType_Code AS varchar)
					WHEN CAST(PrivilegeReason.PrivilegeType_Code as varchar) = '501' THEN '3'
					WHEN CAST(PrivilegeReason.PrivilegeType_Code as varchar) = '502' THEN '2'
					WHEN CAST(PrivilegeReason.PrivilegeType_Code as varchar) = '503' THEN '5'
					WHEN CAST(PrivilegeReason.PrivilegeType_Code as varchar) = '504' THEN '6'
					WHEN CAST(PrivilegeReason.PrivilegeType_Code as varchar) = '505' THEN '1'
					WHEN CAST(PrivilegeReason.PrivilegeType_Code as varchar) = '506' THEN '4'
					WHEN CAST(PrivilegeReason.PrivilegeType_Code as varchar) = '507' THEN '7'
					ELSE '0'
				END as \"PrivilegeReason\",
				to_char(v.EvnVizitPL_SetDT, {$this->dateTimeForm120}) as \"Vizit_SetDT\",
				CASE
					WHEN  PrivilegeReason.ReceptFinance_Code = 1 or PrivilegeReason.ReceptFinance_Code = 3 THEN 1
					WHEN  PrivilegeReason.ReceptFinance_Code = 2 THEN 2
					ELSE 0
				END as \"FinanceSource\",
				MSpec.MedSpec_Code as \"MedPersonal_Profile\",
				coalesce (StomatDiag.Diag_Code, rtrim(case when RIGHT(rtrim(dg.Diag_Code), 1) = '.' THEN LEFT(rtrim(dg.Diag_Code), length(rtrim(dg.Diag_Code)) - 1) ELSE dg.Diag_Code END)) as \"Diag_CodeOsn\",
				coalesce (StomatDiagZakl.Diag_Code, rtrim(case when RIGHT(rtrim(dgz.Diag_Code), 1) = '.' THEN LEFT(rtrim(dgz.Diag_Code), length(rtrim(dgz.Diag_Code)) - 1) ELSE dgz.Diag_Code END)) as \"Diag_CodeZakl\"
			from
				v_EvnVizitPL v
				inner join v_Person_all pers on pers.PersonEvn_id = v.PersonEvn_id and pers.Server_id = v.Server_id
				left join lateral (
					select
						PrivilegeType_Code
					from
						v_PersonPrivilege
					where
						Person_id = v.Person_id						
                    and
                        (PersonPrivilege_endDate is null or PersonPrivilege_endDate >= v.EvnVizitPL_SetDT)
                    and
                        PrivilegeType_Code in ('81', '82', '83', '84')
					limit 1
				) Privilege on true
				left join lateral (
					select
						ppr.PrivilegeType_Code,
						refc.ReceptFinance_Code,
						prt.ReceptFinance_id
					from
						v_PersonPrivilege ppr
						left join PrivilegeType prt on ppr.PrivilegeType_id = prt.PrivilegeType_id
						left join ReceptFinance refc on refc.ReceptFinance_id = prt.ReceptFinance_id
					where
						Person_id = v.Person_id						
						and (PersonPrivilege_endDate is null or PersonPrivilege_endDate >= v.EvnVizitPL_SetDT)
					limit 1
				) PrivilegeReason on true				
				left join v_Address addr on addr.Address_id = pers.PAddress_id
				left join KLArea country on country.KLArea_id = addr.KLCountry_id
				left join KLArea rgn on rgn.KLArea_id = addr.KLRgn_id
				left join KLArea srgn on srgn.KLArea_id = addr.KLSubRgn_id
				left join KLArea city on city.KLArea_id = addr.KLCity_id
				left join KLArea town on town.KLArea_id = addr.KLSubRgn_id
				left join KLStreet street on street.KLStreet_id = addr.KLStreet_id
				left join Diag dg on dg.Diag_id = v.Diag_id
				left join v_EvnPL pl on pl.EvnPL_id = v.EvnVizitPL_pid
				left join Diag dgz on pl.Diag_id = dgz.Diag_id
				left join lateral (
					select
						msc.MedSpec_Code
					from
						v_MedStaffFact msf
						left join MedSpec msc on msf.MedSpecOMS_id = msc.MedSpec_id
					where	
						msf.MedPersonal_id = v.MedPersonal_id
                    and
                        msf.Lpu_id = v.Lpu_id
                    and
                        msf.LpuSection_id = v.LpuSection_id
                    limit 1
				) MSpec on true
				left join lateral (
					select
						rtrim(case when RIGHT(rtrim(dgs.Diag_Code), 1) = '.' THEN LEFT(rtrim(dgs.Diag_Code), length(rtrim(dgs.Diag_Code)) - 1) ELSE dgs.Diag_Code END) as Diag_Code
					from
						v_EvnDiagPLStom	ds
						inner join Diag dgs on dgs.Diag_id = ds.Diag_id
					where
						v.EvnClass_id = 13
                    and
                        ds.EvnDiagPLStom_pid = v.EvnVizitPL_id
					limit 1
				) StomatDiag on true
				left join lateral (
					select
						rtrim(case when RIGHT(rtrim(dgs.Diag_Code), 1) = '.' THEN LEFT(rtrim(dgs.Diag_Code), length(rtrim(dgs.Diag_Code)) - 1) ELSE dgs.Diag_Code END) as Diag_Code
					from
						v_EvnPLStom spl
						inner join v_EvnVizitPLStom sv on sv.EvnVizitPLStom_pid = spl.EvnPLStom_id
						left join v_EvnDiagPLStom ds on ds.EvnDiagPLStom_pid = sv.EvnVizitPLStom_id
						left join Diag dgs on dgs.Diag_id = ds.Diag_id
					where
						v.EvnClass_id = 13
						and spl.EvnPLStom_id = v.EvnVizitPL_pid
					order by
						sv.EvnVizitPLStom_setDT desc
					limit 1
				) StomatDiagZakl on true
			where
				v.Lpu_id = :Lpu_id
            and
                v.EvnVizitPL_setDT >= :Date1
			and
			    v.EvnVizitPL_setDT < :Date2
            and
                pers.PersonSocCardNum_SocCardNum is not null
		";
        $result = $this->db->query($sql, $params);

        if (!is_object($result))
            return false;

        return $result->result('array');
    }

    /**
     * получение данных о временной нетрудоспособности
     *
     * @param $data
     * @return bool|array
     */
    public function getSticksData($data)
    {
        $params = [];
        $params['Lpu_id'] = $data['Lpu_id'];
        $params['Date1'] = $data['range1'];
        $params['Date2'] = $data['range2'];

        $sql = "
			select
				rtrim(pers.PersonSocCardNum_SocCardNum) as \"SocCardNum\",
				to_char(pers.Person_BirthDay, {$this->dateTimeForm120}) as \"Person_BirthDay\",
				case when pers.Sex_id=1 then 'М' else 'Ж' end as \"Person_Sex\",
				CASE
					WHEN Privilege.PrivilegeType_Code = '81' THEN 3
					WHEN Privilege.PrivilegeType_Code = '82' THEN 2
					WHEN Privilege.PrivilegeType_Code = '83' THEN 1
					WHEN Privilege.PrivilegeType_Code = '84' THEN 4
					ELSE 0						
				END as \"Person_DisabilityGroup\",
				CASE
					WHEN street.KLStreet_id is not null and street.KLAdr_Ocatd is not null THEN street.KLAdr_Ocatd
					WHEN town.KLArea_id is not null and town.KLAdr_Ocatd is not null THEN town.KLAdr_Ocatd
					WHEN city.KLArea_id is not null and city.KLAdr_Ocatd is not null THEN city.KLAdr_Ocatd
					WHEN srgn.KLArea_id is not null and srgn.KLAdr_Ocatd is not null THEN srgn.KLAdr_Ocatd
					WHEN rgn.KLArea_id is not null and rgn.KLAdr_Ocatd is not null THEN rgn.KLAdr_Ocatd
					WHEN country.KLArea_id is not null and country.KLAdr_Ocatd is not null THEN country.KLAdr_Ocatd
					ELSE ''
				END as \"Person_OKATO\",
				CASE WHEN addr.KLAreaType_id = 2 THEN 'С' ELSE 'Г' END as \"KLAreaType\",
				CASE
					WHEN PrivilegeReason.ReceptFinance_Code = 1 THEN 1
					WHEN PrivilegeReason.ReceptFinance_Code = 2 THEN 3
					WHEN PrivilegeReason.ReceptFinance_Code = 3 THEN 2
					ELSE 0
				END as \"PrivilegeType_Code\",
				CASE
					WHEN PrivilegeReason.ReceptFinance_id = 1 THEN
						CASE WHEN
								length(PrivilegeReason.PrivilegeType_Code::varchar) = 2
							THEN
								CAST('0' AS varchar(1)) || CAST(PrivilegeReason.PrivilegeType_Code AS varchar(2))
							ELSE
								CAST(PrivilegeReason.PrivilegeType_Code as varchar)
							END
					WHEN PrivilegeReason.ReceptFinance_id = 2 THEN CAST(PrivilegeReason.PrivilegeType_Code AS varchar)
					WHEN CAST(PrivilegeReason.PrivilegeType_Code as varchar) = '501' THEN '3'
					WHEN CAST(PrivilegeReason.PrivilegeType_Code as varchar) = '502' THEN '2'
					WHEN CAST(PrivilegeReason.PrivilegeType_Code as varchar) = '503' THEN '5'
					WHEN CAST(PrivilegeReason.PrivilegeType_Code as varchar) = '504' THEN '6'
					WHEN CAST(PrivilegeReason.PrivilegeType_Code as varchar) = '505' THEN '1'
					WHEN CAST(PrivilegeReason.PrivilegeType_Code as varchar) = '506' THEN '4'
					WHEN CAST(PrivilegeReason.PrivilegeType_Code as varchar) = '507' THEN '7'
					ELSE '0'
				END as \"PrivilegeReason\",
				to_char(st.EvnStick_begDate, {$this->dateTimeForm120}) as \"Stick_begDate\",
				to_char(st.EvnStick_endDate, {$this->dateTimeForm120}) as \"Stick_endDate\",
				CASE
					WHEN stc.StickCause_Code = '1' THEN 1
					WHEN stc.StickCause_Code = '5' THEN 2
					WHEN stc.StickCause_Code = '6' THEN 3
					WHEN stc.StickCause_Code = '8' THEN 4
					WHEN stc.StickCause_Code = '9' THEN 5
					WHEN stc.StickCause_Code = '10' THEN 4
					WHEN stc.StickCause_Code = '2' THEN 1
					WHEN stc.StickCause_Code = '3' THEN 1
					WHEN stc.StickCause_Code = '11' THEN 4
					WHEN stc.StickCause_Code = '12' THEN 4
					WHEN stc.StickCause_Code = '13' THEN 1
					WHEN stc.StickCause_Code = '4' THEN 1
					WHEN stc.StickCause_Code = '7' THEN 1
					WHEN stc.StickCause_Code = '14' THEN 5
				END as \"StickReason\",
				rtrim(case when RIGHT(rtrim(dg.Diag_Code), 1) = '.' THEN LEFT(rtrim(dg.Diag_Code), length(rtrim(dg.Diag_Code)) - 1) ELSE dg.Diag_Code END) as \"Diag_Code\"
			from
				v_EvnStick st
				inner join v_Person_all pers on pers.PersonEvn_id = st.PersonEvn_id and pers.Server_id = st.Server_id
				left join lateral (
					select
						PrivilegeType_Code
					from
						v_PersonPrivilege
					where
						Person_id = st.Person_id						
                    and
                        (PersonPrivilege_endDate is null or PersonPrivilege_endDate >= st.EvnStick_SetDT)
                    and
                        PrivilegeType_Code in ('81', '82', '83', '84')
					limit 1
				) Privilege on true
				left join lateral (
					select
						ppr.PrivilegeType_Code,
                        refc.ReceptFinance_Code,
                        prt.ReceptFinance_id
					from
						v_PersonPrivilege ppr
						left join PrivilegeType prt on ppr.PrivilegeType_id = prt.PrivilegeType_id
						left join ReceptFinance refc on refc.ReceptFinance_id = prt.ReceptFinance_id
					where
						Person_id = st.Person_id						
                    and
                        (PersonPrivilege_endDate is null or PersonPrivilege_endDate >= st.EvnStick_SetDT)
					limit 1
				) PrivilegeReason on true
				left join v_Address addr on addr.Address_id = pers.PAddress_id
				left join KLArea country on country.KLArea_id = addr.KLCountry_id
				left join KLArea rgn on rgn.KLArea_id = addr.KLRgn_id
				left join KLArea srgn on srgn.KLArea_id = addr.KLSubRgn_id
				left join KLArea city on city.KLArea_id = addr.KLCity_id
				left join KLArea town on town.KLArea_id = addr.KLSubRgn_id
				left join KLStreet street on street.KLStreet_id = addr.KLStreet_id
				left join v_EvnPL epl on st.EvnStick_rid=epl.EvnPL_id
					and epl.Lpu_id=st.Lpu_id
					and epl.EvnPL_IsFinish=2
				left join Diag dg on dg.Diag_id=epl.Diag_id
				left join StickCause stc on stc.StickCause_id = st.StickCause_id
			where
				st.Lpu_id = :Lpu_id
				and st.EvnStick_begDate >= :Date1
				and st.EvnStick_begDate < :Date2
				and st.EvnStick_endDate is not null
				and pers.PersonSocCardNum_SocCardNum is not null
		";

        $result = $this->db->query($sql, $params);

        if (!is_object($result))
            return false;

        return $result->result('array');
    }
}