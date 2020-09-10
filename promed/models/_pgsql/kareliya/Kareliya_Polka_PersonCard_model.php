<?php

require_once(APPPATH.'models/_pgsql/Polka_PersonCard_model.php');

class Kareliya_Polka_PersonCard_model extends Polka_PersonCard_model {
    /**
     * Конструктор
     */
    /*function __construct()
    {
        parent::__construct();
    }*/


    /**
     *	Список прикрепленного населения к указанной СМО на указанную дату
     */
    function loadAttachedList($data)
    {
        $filterList = array();
        $queryParams = array(
            'Lpu_id' => $data['AttachLpu_id'],
            'Date_upload' => $data['Date_upload']
        );

        $query = "
			select
				SMO.Org_id as \"Org_id\",
				SMO.Orgsmo_f002smocod as \"SMO\",
				PS.Person_id as \"ID_PAC\", -- Идентификатор пациента
				rtrim(upper(PS.Person_SurName)) as \"FAM\", -- Фамилия
				rtrim(upper(PS.Person_FirName)) as \"IM\", -- Имя
				COALESCE(rtrim(Upper(case when Replace(PS.Person_Secname,' ','') = '---' then '' else PS.Person_Secname end)), '') as \"OT\", -- Отчество
				PS.Sex_id as \"W\", -- Пол застрахованного
				to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as \"DR\", -- Дата рождения застрахованного
				PT.PolisType_CodeF008 as \"VPOLIS\",
				rtrim(case when PLS.PolisType_id = 4 then '' else PLS.Polis_Ser end) as \"SPOLIS\",
				rtrim(case when PLS.PolisType_id = 4 then PS.Person_EdNum else PLS.Polis_Num end) as \"NPOLIS\",
				to_char(PC.PersonCard_begDate, 'yyyy-mm-dd') as \"DATE\",
				/*case
					when OST.OrgServiceTerr_id is not null then 1
					when PC.PersonCardAttach_id is not null then 2
					else 0
				end as SP_PRIK,*/
				case
					when (PC.PersonCardAttach_id is not null) then 2
					when (PC.PersonCardAttach_id is null and COALESCE(PC.PersonCard_IsAttachCondit,1) = 2) then 1
					else 0
				end as \"SP_PRIK\",
				case
					when CCC.CardCloseCause_Code is null then 1
					when CCC.CardCloseCause_Code = 1 then 2
					when CCC.CardCloseCause_Code = 3 then 5
					when CCC.CardCloseCause_Code = 7 then 4
					when ADDRESSCHANGE.PersonUAddress_id IS NOT NULL then 3 -- Выгружать, если с момента прикрепления к предыдущей МО адрес изменялся
					else 0
				end as \"T_PRIK\",
				right('00' || COALESCE(left(LPS.LpuSection_Code, 2), ''), 2) as \"KOD_PODR\",
				PC.LpuRegion_Name  as \"NUM_UCH\",
				case
					when LRT.LpuRegionType_SysNick = 'ter' then 1
					when LRT.LpuRegionType_SysNick = 'ped' then 2
					when LRT.LpuRegionType_SysNick = 'vop' then 3
					when LRT.LpuRegionType_SysNick = 'feld' then 3
					else null
				end as \"TIP_UCH\",
				MEDSnils.Person_Snils as \"SNILS_VR\",
				COALESCE(DD.DISP, 0) as \"DISP\",
				case
					when DD.DISP IS NULL then NULL
					when dbo.Age2(PS.Person_BirthDay, dbo.tzGetdate()) < 3 then 
                        case when EXTRACT(MONTH FROM dbo.tzGetdate()) BETWEEN 1 AND 3 then 1
                        when EXTRACT(MONTH FROM dbo.tzGetdate()) BETWEEN 4 AND 6 then 2
                        when EXTRACT(MONTH FROM dbo.tzGetdate()) BETWEEN 7 AND 9 then 3 else 4 end
					when EXTARCT(MONTH FROM PS.Person_BirthDay) BETWEEN 1 AND 3 then 1
					when EXTARCT(MONTH FROM PS.Person_BirthDay) BETWEEN 4 AND 6 then 2
					when EXTARCT(MONTH FROM PS.Person_BirthDay) BETWEEN 7 AND 9 then 3
					when EXTARCT(MONTH FROM PS.Person_BirthDay) BETWEEN 10 AND 12 then 4
				end as \"DISP_KV\",
				case
					when DDCard.DispClass_id = 1 and PP.PersonPrivilege_id is not null then 2
					when DDCard.DispClass_id = 1 then 1
					when DDCard.DispClass_id = 2 and PP.PersonPrivilege_id is not null then 5
					when DDCard.DispClass_id = 2 then 4
					when DDCard.DispClass_id = 5 then 3
					when DDCard.DispClass_id in (6, 9, 10) then 6
					when DDCard.DispClass_id in (11, 12) then 7
					else null
				end as \"DISP_FAKT\",
				to_char(DDCard.EvnPLDisp_setDT, 'yyyy-mm-dd') as \"DATE_NPM\",
				to_char(DDCard.EvnPLDisp_disDT, 'yyyy-mm-dd') as \"DATE_OPM\",
				PI.PersonInfo_InternetPhone as \"PHONE1\",
				null as \"PHONE2\"
			from
				v_PersonCard_all PC
				left join lateral (
					select Person_id, Server_pid, Polis_id, Person_EdNum, UAddress_id, Person_SurName, Person_FirName, Person_Secname, Sex_id, Person_BirthDay, Person_deadDT, dbo.Age2(Person_BirthDay, cast(EXTRACT(YEAR FROM :Date_upload) as varchar) || '-12-31') as Person_Age
					from v_Person_all P
					where P.Person_id = PC.Person_id
						and cast(P.PersonEvn_insDT as date) <= :Date_upload
					order by P.PersonEvn_insDT desc, P.PersonEvn_id desc
					limit 1
				) PS on true
				left join lateral (
					select PersonInfo_InternetPhone
					from v_PersonInfo
					where Person_id = PC.Person_id
						and PersonInfo_InternetPhone is not null
					limit 1
				) PI on true
				left join lateral (
					select 
						case
							when exists (select PersonPrivilegeWOW_id from v_PersonPrivilegeWOW where Person_id = PS.Person_id limit 1) then 2
							when (PS.Person_Age >= 21 and PS.Person_Age % 3 = 0) then 1
							when PS.Person_Age >= 18 and not exists (
								select
									epldp.EvnPLDispProf_id
								from
									v_EvnPLDispProf epldp
								where
									epldp.Person_id = PS.Person_id
									and EXTRACT(YEAR FROM epldp.EvnPLDispProf_consDT) = EXTRACT(YEAR FROM :Date_upload) - 1
								limit 1
							) then 3
							when PS.Person_Age < 18 then 4
						end as DISP
					from
						v_PersonState ps1 
					where
						ps1.Person_id = PS.Person_id
					limit 1
				) DD on true
				left join lateral (
					select  DispClass_id, EvnPLDisp_setDT, EvnPLDisp_disDT
					from v_EvnPLDisp
					where EXTRACT(YEAR FROM EvnPLDisp_setDT) = EXTRACT(YEAR FROM :Date_upload)
						and Person_id = PS.Person_id
						and DispClass_id in (1, 2, 5, 6, 9, 10, 11, 12)
					order by DispClass_id desc
					limit 1
				) DDCard on true
				left join lateral (
					select t1.PersonPrivilege_id
					from v_PersonPrivilege t1
					where t1.Person_id = PS.Person_id
						and t1.PrivilegeType_Code in ('10','11','20','60','50','140','150')
					limit 1	
				) PP on true
				inner join v_Lpu L on L.Lpu_id = PC.Lpu_id
				inner join v_Polis PLS on PLS.Polis_id = PS.Polis_id
				inner join v_PolisType PT on PT.PolisType_id = PLS.PolisType_id
				inner join v_OrgSMO SMO on SMO.OrgSMO_id = PLS.OrgSmo_id and SMO.KLRgn_id = 10
				left join lateral (
					select  CardCloseCause_id, PersonCard_begDate
					from v_PersonCard_all t 
					where t.Person_id = PC.Person_id
						and t.PersonCard_id != PC.PersonCard_id
						and t.PersonCard_endDate = PC.PersonCard_begDate
					order by t.PersonCard_begDate desc
					limit 1
				) PCL on true
				left join lateral (
					select
						pua.PersonUAddress_id
					from
						v_PersonUAddress pua
					where
						pua.Person_id = pc.Person_id
						and pua.PersonUAddress_insDate >= PCL.PersonCard_begDate
						and pua.PersonUAddress_insDate <= :Date_upload
					limit 1
				) ADDRESSCHANGE on true
				left join v_CardCloseCause CCC on CCC.CardCloseCause_id = PCL.CardCloseCause_id
				left join Address A on A.Address_id = PS.UAddress_id
				left join lateral (
					select MedPers.Person_Snils
					from v_MedStaffRegion MSR
						inner join v_MedPersonal MedPers on MedPers.MedPersonal_id = MSR.MedPersonal_id
						inner join v_MedStaffFact msf on msf.MedPersonal_id = MedPers.MedPersonal_id
					where MSR.LpuRegion_id = PC.LpuRegion_id
						and MedPers.Person_Snils is not null
						and msf.Lpu_id = :Lpu_id
						and (msf.WorkData_begDate is null or cast(msf.WorkData_begDate as date) <= :Date_upload)
						and (msf.WorkData_endDate is null or cast(msf.WorkData_endDate as date) >= :Date_upload)
						and (MSR.MedStaffRegion_begDate is null or cast(MSR.MedStaffRegion_begDate as date) <= :Date_upload)
						and (MSR.MedStaffRegion_endDate is null or cast(MSR.MedStaffRegion_endDate as date) >= :Date_upload)
					order by MSR.MedStaffRegion_isMain desc
					limit 1
				) as MEDSnils on true
				left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
				left join v_LpuRegionType LRT on LRT.LpuRegionType_id = PC.LpuRegionType_id
				left join v_LpuSection LPS on LPS.LpuSection_id = LR.LpuSection_id
				left join lateral (
					select OrgServiceTerr_id
					from v_OrgServiceTerr
					where Org_id = L.Org_id
						and (KLCountry_id is null or KLCountry_id = A.KLCountry_id)
						and (KLRGN_id is null or KLRGN_id = A.KLRGN_id)
						and (KLSubRGN_id is null or KLSubRGN_id = A.KLSubRGN_id)
						and (KLCity_id is null or KLCity_id = A.KLCity_id)
						and (KLTown_id is null or KLTown_id = A.KLTown_id)
					limit 1
				) OST on true
			where PC.LpuAttachType_id = 1
				and PS.Server_pid = 0
				--and (PC.CardCloseCause_id is null or PC.CardCloseCause_id <> 4)
				and (PLS.Polis_endDate is null or PLS.Polis_endDate >= :Date_upload)
				and (
					(PLS.PolisType_id = 4 and PS.Person_EdNum is not null)
					or (PLS.PolisType_id <> 4 and PLS.Polis_Num is not null)
				)
				and PT.PolisType_CodeF008 is not null
				and PC.Lpu_id = :Lpu_id
				and pc.PersonCard_begDate <= :Date_upload
				and (pc.PersonCard_endDate > :Date_uploador pc.PersonCard_endDate is null)
				and (PS.Person_deadDT is null or PS.Person_deadDT > :Date_upload)
		";
        //echo getDebugSQL($query, $queryParams); die();
        $result = $this->db->query($query, $queryParams);

        if ( !is_object($result) ) {
            return false;
        }

        $PERS = $result->result('array');
        if ( !is_array($PERS) || count($PERS) == 0) {
            return array(
                'Error_Code' => 1, 'Error_Msg' => 'Список выгрузки пуст!'
            );
        }

        $data = array();
        $data['Error_Code'] = 0;
        $data['Smo_Pers'] = array();
        $data['Errors'] = array();
        $org_errors = array();

        // Получаем данные МО
        $query = "
			select
				L.Org_id as \"Org_id\",
				L.Lpu_f003mcod as \"CODE_MO\",
				PassT.PassportToken_tid as \"ID_MO\"
			from v_Lpu L
				left join fed.v_PassportToken PassT on PassT.Lpu_id = L.Lpu_id
			where L.Lpu_id = :Lpu_id
			limit 1
		";
        $result = $this->db->query($query, $queryParams);

        if ( !is_object($result) ) {
            return false;
        }

        $ZGLV = $result->result('array');

        if ( !is_array($ZGLV) || count($ZGLV) == 0) {
            return array(
                'Error_Code' => 1, 'Error_Msg' => 'Ошибка при получении кода МО!'
            );
        }

        if (empty($ZGLV[0]['CODE_MO'])) {
            $org_errors[] = $ZGLV[0]['Org_id'];
        } else {
            $smo = array();
            $smo_pers = array();
            for($i=0; $i<count($PERS); $i++) {
                $org_id = $PERS[$i]['Org_id'];
                $smo_code = $PERS[$i]['SMO'];

                if ( !empty($PERS[$i]['PHONE1']) ) {
                    $PERS[$i]['PHONE1'] = substr(trim($PERS[$i]['PHONE1'], '+'), 0, 11);
                }

                if (empty($smo_code)) {
                    if (!in_array($org_id, $org_errors)) {
                        $org_errors[] = $PERS[$i]['Org_id'];
                    }
                } else {
                    if (!in_array($smo_code, $smo)) {
                        $smo[] = $smo_code;
                    }
                    $smo_pers[$smo_code][] = $PERS[$i];
                }
            }
            for($i=0; $i<count($smo); $i++) {
                $smo_code = $smo[$i];
                $item = array();
                $itemZGLV = $ZGLV;

                $itemZGLV[0]['ZAP'] = count($smo_pers[$smo_code]);
                $itemZGLV[0]['SMO'] = $smo_code;

                $item['ZGLV'] = $itemZGLV;
                $item['PERS'] = $smo_pers[$smo_code];

                $data['Smo_Pers'][$i] = $item;
            }
        }

        $org_errors_str = implode(',',$org_errors);
        if (count($org_errors) > 0 && strlen($org_errors_str) > 0) {
            $query = "
				select
					Org_INN as \"Org_INN\", Org_OGRN as \"Org_OGRN\", Org_Nick as \"Org_Nick\", A.Address_Address as \"Address_Address\"
				from v_Org O 
					left join v_Address A on A.Address_id = O.PAddress_id
				where O.Org_id in ({$org_errors_str})
			";
            $result = $this->db->query($query);
            if ( !is_object($result) ) {
                return false;
            }
            $data['Errors'] = $result->result('array');
        }

        return $data;
    }
}