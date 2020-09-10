<?php

/**
 * ZnoSuspectRegister_User_model - молеь для работы с данными регистра подозреваемых на ЗНО
 * пользовательская  часть
 *
 * @author
 * @version			06.11.2018
 * @property-read CI_DB_driver $db
 */
class ZnoSuspectRegister_User_model extends SwPgModel
{

    protected $dateTimeForm104 = "'dd.mm.yyyy'";
    protected $dateTimeForm120 = "'dd.mm.yyyy hh24:mi'";

    /**
     * Получение списка Случаев подозрений на ЗНО для конкретного пациента
     * @param array $data
     * @return bool
     */
    public function getListZnoSuspectUser($data)
    {
        $params = array(
            'Person_id' => $data['Person_id']
        );


        $query = "
            select 
                ZnoReg.Person_id as \"Person_id\",
                ZNORout.ZNOSuspectRout_id as \"ZNOSuspectRout_id\",
                ZNORout.Diag_Fid as \"Diag_Fid\",
                SUBSTRING(d.Diag_FullName, 0, 25) || '...' as \"Diag_Name\", 
                d.Diag_FullName as \"Diag_FullName\",
                to_char(ZNORout.ZNOSuspectRout_setDate, {$this->dateTimeForm104}) as \"ZNOSuspect_setDate\",
                case 
                    when dd.Diag_Code is null
                        then 1
                    when dd.Diag_Code ilike'D0%' or dd.Diag_Code ilike'C%'
                        then 2
                    else 3
                end as \"ZNOSuspect_happening\",
                to_char(ZNORout.ZNOSuspectRout_disDate, {$this->dateTimeForm104}) as \"ZNOSuspect_disDate\"
            from
                dbo.ZNOSuspectRegistry ZnoReg
                inner join dbo.ZNOSuspectRout ZNORout on ZnoReg.ZNOSuspectRegistry_id = ZNORout.ZNOSuspectRegistry_id
                inner join v_Diag d on d.Diag_id = ZNORout.Diag_id
                left join v_Diag dd on dd.Diag_id = ZNORout.Diag_Fid
            where 
                ZnoReg.ZNOSuspectRegistry_deleted = 1 and ZNORout.ZNOSuspectRout_deleted = 1
            and
                ZnoReg.Person_id = :Person_id
            order by ZNORout.ZNOSuspectRout_setDate desc
        ";


        $result = $this->db->query($query, $params);

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     * Получение маршрута пациента по ЗНО
     * @param $data
     * @return bool
     */
    public function getListZnoRoutPerson($data)
    {

        $params = [
            'Person_id' => $data['Person_id'],
            'ZNOSuspectRout_id' => $data['ZNOSuspectRout_id']
        ];
        // По требованию Влада и Марьиной
        $query = "
            select 
                ROW_NUMBER () over (order by svod.VizitPL_setDT ) as \"vID\",
                to_char(svod.VizitPL_setDT, {$this->dateTimeForm120})  as \"VizitPL_setDT\",
                svod.lpuIn as \"lpuIn\",
                LpuIn.Lpu_Nick as \"LpuInName\",  
                svod.Diag_spid as \"Diag_spid\" ,
                d.Diag_Code as  \"DiagCode_spid\" , 
                LpuOut.lpu_id as \"lpu_id\", 
                LpuOut.Lpu_Nick as \"LpuOutName\", 
                dd.Diag_Code as \"Diag_Code\",
                case 
                    when dd.Diag_Code = 'Z03.1'
                        then null
                    else
                        dd.Diag_Code
                end as \"Diag_CodeFin\",
                case 
                    when  svod.EvnPL_id is not null and  (dd.Diag_Code ilike'D0%' or dd.Diag_Code ilike'C%')
                        then 'Подтвержден'
                    when  svod.EvnPL_id is not null and (dd.Diag_Code not ilike'D0%' and dd.Diag_Code not ilike'C%' and dd.Diag_Code <> 'Z03.1')
                        then 'Не подтвержден'
                    else '---'
                end as \"confirm\"
            from 
                (
                        select
                            vpl.EvnVizitPL_setDT as VizitPL_setDT,
                            null  as EvnPL_id,
                            vpl.lpu_id as lpuIn, 
                            vpl.Diag_spid as Diag_spid ,
                            vpl.Diag_id as Diag_id ,
                            vpl.EvnVizitPL_id
                        from
                            v_EvnVizitPL vpl
                            inner join v_EvnDirection_all edIN on edIN.EvnDirection_pid = vpl.EvnVizitPL_id and edIN.EvnDirection_failDT is null 
                            inner join v_DirType dr on dr.dirtype_id = edIN.DirType_id 
                        where vpl.EvnVizitPL_id in (
                             select RoutAll.EvnVizitPL_id
                             from dbo.ZNOSuspectRoutAll RoutAll
                             inner join dbo.ZNOSuspectRout  Rout on RoutAll.ZNOSuspectRout_id = Rout.ZNOSuspectRout_id and Rout.ZNOSuspectRout_deleted = 1
                             inner join dbo.ZNOSuspectRegistry reg on reg.ZNOSuspectRegistry_id = Rout.ZNOSuspectRegistry_id and reg.ZNOSuspectRegistry_deleted = 1
                             where reg.Person_id = :Person_id and RoutAll.EvnPL_id is null and Rout.ZNOSuspectRout_id = :ZNOSuspectRout_id and RoutAll.ZNOSuspectRoutAll_deleted = 1
                         )
                        and dr.DirType_Code in (3,12)
                    union
                        select  vpl.EvnVizitPL_setDT as VizitPL_setDT, 
                                RoutAll.EvnPL_id as EvnPL_id,
                                vpl.lpu_id as lpuIn,
                                vpl.Diag_spid as Diag_spid, 
                                vpl.Diag_id as Diag_id,
                                vpl.EvnVizitPL_id as EvnVizitPL_id
                        from
                            v_EvnPL pl
                            inner join v_EvnVizitPL vpl on vpl.EvnVizitPL_pid = pl.EvnPL_id
                            left join dbo.ZNOSuspectRoutAll RoutAll on RoutAll.EvnVizitPL_id = vpl.EvnVizitPL_id
                                and  RoutAll.ZNOSuspectRoutAll_deleted = 1
                                and RoutAll.ZNOSuspectRout_id = :ZNOSuspectRout_id
                        where
                            vpl.Person_id = :Person_id
                        and
                            pl.EvnDirection_id in 
                                (
                                    select
                                        edIN1.EvnDirection_id 
                                    from
                                        v_EvnPL pl1  
                                        inner join v_EvnVizitPL vpl1 on vpl1.EvnVizitPL_pid = pl1.EvnPL_id
                                        inner join v_EvnDirection_all edIN1 on edIN1.EvnDirection_pid = vpl1.EvnVizitPL_id and edIN1.EvnDirection_failDT is null 
                                        inner join v_DirType dr1 on dr1.dirtype_id = edIN1.DirType_id 
                                    where
                                        vpl1.EvnVizitPL_id in 
                                            (
                                                 select RoutAll.EvnVizitPL_id
                                                 from dbo.ZNOSuspectRoutAll RoutAll
                                                 inner join dbo.ZNOSuspectRout  Rout on RoutAll.ZNOSuspectRout_id = Rout.ZNOSuspectRout_id and Rout.ZNOSuspectRout_deleted = 1
                                                 inner join dbo.ZNOSuspectRegistry reg on reg.ZNOSuspectRegistry_id = Rout.ZNOSuspectRegistry_id and reg.ZNOSuspectRegistry_deleted = 1
                                                 where reg.Person_id = :Person_id and RoutAll.EvnPL_id is null and Rout.ZNOSuspectRout_id = :ZNOSuspectRout_id and   RoutAll.ZNOSuspectRoutAll_deleted = 1
                                            )
                                    and dr1.DirType_Code in (3,12)
                            )
                    union
                        select
                            vpl.EvnVizitPL_setDT as VizitPL_setDT,
                            RoutAll.EvnPL_id as EvnPL_id,
                            vpl.lpu_id as lpuIn, 
                            vpl.Diag_spid as Diag_spid,
                            vpl.Diag_id as Diag_id,
                            vpl.EvnVizitPL_id as EvnVizitPL_id
                        from
                            v_EvnVizitPL vpl
                            inner join dbo.ZNOSuspectRoutAll RoutAll on RoutAll.EvnVizitPL_id = vpl.EvnVizitPL_id and  RoutAll.ZNOSuspectRoutAll_deleted = 1
                            inner join dbo.ZNOSuspectRout Rout on Rout.ZNOSuspectRout_id  = RoutAll.ZNOSuspectRout_id and  Rout.ZNOSuspectRout_deleted = 1
                            inner join dbo.ZNOSuspectRegistry reg on reg.ZNOSuspectRegistry_id = Rout.ZNOSuspectRegistry_id and reg.ZNOSuspectRegistry_deleted = 1
                        where
                            reg.Person_id = :Person_id
                        and
                            RoutAll.EvnPL_id is not null
                        and
                            Rout.ZNOSuspectRout_id = :ZNOSuspectRout_id
                )  svod
                inner join v_EvnVizitPL vpl1 on vpl1.EvnVizitPL_id = svod.EvnVizitPL_id
                inner join v_Lpu LpuIn on LpuIn.lpu_id = svod.lpuIn
                left join v_EvnDirection_all edIN1 on edIN1.EvnDirection_pid = vpl1.EvnVizitPL_id and edIN1.EvnDirection_failDT is null 
                left join v_DirType dr on dr.dirtype_id = edIN1.DirType_id
                left join v_Lpu LpuOut on LpuOut.lpu_id = edIN1.lpu_did
                left join v_Diag d on d.Diag_id = svod.Diag_spid
                left join v_Diag dd on dd.Diag_id = svod.Diag_id
            where
                dr.DirType_Code is null or dr.DirType_Code in (3,12)
            order by
                svod.VizitPL_setDT,
                (case when edIN1.lpu_did is null then - 10 else edIN1.lpu_did end)
            asc
        ";


        $result = $this->db->query($query, $params);

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     * Получение Исследований пациента по ЗНО
     * @param $data
     * @return bool
     */
    public function getListZnoResearchPerson($data)
    {

        $query = "
            select  
                ROW_NUMBER () over (order by UCAT.UslugaComplexAttributeType_Code ) as \"vID\", 
                UCAT.UslugaComplexAttributeType_SysNick as \"UslugaComplexAttributeType_SysNick\",
                to_char(eu.EvnUsluga_setDT, {$this->dateTimeForm120}) as \"EvnUsluga_setDT\",
                Lpu.Lpu_Nick as \"Lpu_Nick\",
                MP.Person_FIO as \"MedPerson_FIO\", 
                uc.UslugaComplex_Name as \"UslugaComplex_Name\", 
                uc.UslugaComplex_Code as \"UslugaComplex_Code\",
                UCAT.UslugaComplexAttributeType_Code as \"UslugaComplexAttributeType_Code\"
            from 
                v_EvnUsluga EU
                inner join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
                inner join v_UslugaComplexAttribute UCA on UCA.UslugaComplex_id = UC.UslugaComplex_id
                inner join v_UslugaComplexAttributeType UCAT on UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
                inner join v_MedPersonal MP on MP.MedPersonal_id = EU.MedPersonal_id
                inner join v_Lpu Lpu on Lpu.lpu_id = eu.Lpu_id
            where
                UCAT.UslugaComplexAttributeType_SysNick in 
                (
                    'endoscop','laser','kriogen','oper',
                    'ray','func','lab','xray','manproc',
                    'ivl','XimLech','LuchLech',
                    'GormImunTerLech','XirurgLech'
                )
            and
                eu.EvnUsluga_setDate is not null
        ";

        if ($data['ZNOSuspect_disDate'] != null) {
            $params = [
                'Person_id' => $data['Person_id'],
                'ZNOSuspect_setDate' => $data['ZNOSuspect_setDate'],
                'ZNOSuspect_disDate' => $data['ZNOSuspect_disDate']
            ];

            $query .= " and  EU.Person_id = :Person_id 
					    and eu.EvnUsluga_setDate >= :ZNOSuspect_setDate
					    and eu.EvnUsluga_setDate <= :ZNOSuspect_disDate ";
        } else {
            $params = [
                'Person_id' => $data['Person_id'],
                'ZNOSuspect_setDate' => $data['ZNOSuspect_setDate']
            ];
            $query .= "
			    and EU.Person_id = :Person_id 
                and eu.EvnUsluga_setDate >= :ZNOSuspect_setDate
		    ";
        }

        $query .= "
		    group by
		        UCAT.UslugaComplexAttributeType_SysNick,
		        eu.EvnUsluga_setDT,
		        Lpu.Lpu_Nick,
		        MP.Person_FIO,
		        uc.UslugaComplex_Name, 
                uc.UslugaComplex_Code,
                UCAT.UslugaComplexAttributeType_Code
            order by
                eu.EvnUsluga_setDT desc;
        ";



        $result = $this->db->query($query, $params);

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     * Получение случаев лечения пациента по ЗНО без направления на Консультацию
     * @param $data
     * @return bool
     */
    public function getListPersonZnoWithoutDirect($data)
    {

        $params = [
            'Person_id' => $data['Person_id'],
            'ZNOSuspect_setDate' => $data['ZNOSuspect_setDate'],
            'ZNOSuspect_disDate' => $data['ZNOSuspect_disDate']
        ];
        $query = "
            select 
                ROW_NUMBER () over (order by vpl.EvnVizitPL_setDT ) as \"vID\",
                to_char(vpl.EvnVizitPL_setDT, {$this->dateTimeForm120}) as \"EvnVizitPL_setDT\",
                Lpu.Lpu_Nick as \"Lpu_Nick\", 
                mp.Person_Fio as \"MedPerson_FIO\", 
                d.Diag_Code as \"Diag_Code\"
            from
                v_EvnPL pl 
                inner join v_EvnVizitPL vpl on vpl.EvnVizitPL_pid = pl.EvnPL_id 
                inner join v_Diag d on vpl.Diag_spid = d.Diag_id
                inner join v_MedPersonal MP on MP.MedPersonal_id = vpl.MedPersonal_id
                inner join v_Lpu Lpu on Lpu.lpu_id = vpl.Lpu_id
                left join v_EvnDirection_all edIN on edIN.EvnDirection_pid = vpl.EvnVizitPL_id and edIN.EvnDirection_failDT is null 
                left join v_DirType dr on dr.dirtype_id = edIN.DirType_id 
                inner join v_MedStaffFact MedStaf on MedStaf.MedStaffFact_id = vpl.MedStaffFact_id
            where
                vpl.Person_id = :Person_id
            and
                vpl.EvnVizitPL_IsZNO = 2
            and
                pl.Diag_id = (select s_diag_id from cte) 
            and
                vpl.Diag_spid is not null
            and 
                (dr.DirType_Code is null or  dr.DirType_Code not in(3,2,12) )
            and
                vpl.EvnVizitPL_setDate >= :ZNOSuspect_setDate
        ";

        $exists = "
		    and vpl.EvnVizitPL_pid not in (
                select
                    vpl1.EvnVizitPL_pid 
                from 
                    v_EvnPL pl1
                    inner join v_EvnVizitPL vpl1 on vpl1.EvnVizitPL_pid = pl1.EvnPL_id 
                    inner join v_EvnDirection_all edIN1 on edIN1.EvnDirection_pid = vpl1.EvnVizitPL_id and edIN1.EvnDirection_failDT is null 
                    inner join v_DirType dr1 on dr1.dirtype_id = edIN1.DirType_id
                where
                    vpl1.Person_id = :Person_id and dr1.DirType_Code in(3,2,12) and vpl1.Diag_spid is not null
                and
                    vpl1.EvnVizitPL_IsZNO = 2 and pl1.Diag_id = (select s_diag_id from cte)
                and
                    vpl1.EvnVizitPL_setDate >= :ZNOSuspect_setDate ";

        if ($data['ZNOSuspect_disDate'] != null) {
            $query .= " and vpl.EvnVizitPL_setDate <= :ZNOSuspect_disDate ";
            $exists .= " and vpl1.EvnVizitPL_setDate <= :ZNOSuspect_disDate ) ";
        } else {
            $exists .= "  ) ";
        };

        $query = "
		    with cte as (
                select
                    gg.Diag_id as s_diag_id
                from
                    dbo.v_Diag gg
                where
                    gg.Diag_Code = 'Z03.1'
		    )
		". $query . $exists . "
            group by
                vpl.EvnVizitPL_setDT,
                Lpu.Lpu_Nick,
                mp.Person_Fio,
                d.Diag_Code
            order by
                vpl.EvnVizitPL_setDT desc
		";

        sql_log_message('error', 'model getListPersonZnoWithoutDirect: ', getDebugSql($query, $params));

        $result = $this->db->query($query, $params);

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     * Получение списка запусков процедур по обновлению регистра
     */
    public function getListZNOSuspectAdmin()
    {

        $params = [];
        $query = "
            SELECT  
                ROW_NUMBER () over (order by ZNOadmin.ZNOSuspectAdmin_setDT ) as \"vID\",
                ZNOadmin.ZNOSuspectAdmin_id as \"id\",
                to_char(ZNOadmin.ZNOSuspectAdmin_setDT, {$this->dateTimeForm120}) as \"ZNOSuspectAdmin_setDT\",
                ZNOadmin.ZNOSuspectAdmin_Name as \"ZNOSuspectAdmin_Name\",
                to_char(ZNOadmin.ZNOSuspectAdmin_disDT, {$this->dateTimeForm120}) as \"ZNOSuspectAdmin_disDT\",
                ZNOadmin.ZNOSuspectAdmin_ErrMessage as \"ZNOSuspectAdmin_ErrMessage\",
                ZNOadmin.ZNOSuspectAdmin_ErrCode as \"ZNOSuspectAdmin_ErrCode\"
            FROM
                dbo.ZNOSuspectAdmin ZNOadmin
            where
                ZNOadmin.ZNOSuspectAdmin_deleted = 1
            order by ZNOadmin.ZNOSuspectAdmin_setDT desc
        ";



        sql_log_message('error', 'model getListZNOSuspectAdmin: ', getDebugSql($query, $params));

        $result = $this->db->query($query, $params);

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     * Анализ наличия первоначальной загрузки регистра
     */
    public function getListZNOSuspectAdmin1()
    {

        $params = [];
        $query = "
            SELECT
                count(*) as \"nKol\"
            FROM
                dbo.ZNOSuspectAdmin tt
            where
                tt.ZNOSuspectAdmin_Name = 'p_ZNOSuspectAdmin_1'
            and
                tt.ZNOSuspectAdmin_deleted = 1
            and
                tt.ZNOSuspectAdmin_ErrMessage is null
            and
                tt.ZNOSuspectAdmin_ErrCode is null
        ";

        $result = $this->db->query($query, $params);

        if (!is_object($result)) {
            return false;
        }
        return $result->result('array');
    }

    /**
     * Формирование диапазога дат с анализом
     */
    public function getListZNOSuspectAdmin2()
    {

        $params = [];
        $query = "
            SELECT
                max(ZNOSuspectAdmin_setDT) as \"SetDate\" 
            from
                dbo.ZNOSuspectAdmin tt
            where
                tt.ZNOSuspectAdmin_Name in ('p_ZNOSuspectAdmin_1','p_ZNOSuspectAdmin_2')
            and
                tt.ZNOSuspectAdmin_deleted = 1
        ";

        $result = $this->db->query($query, $params);

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     * Запуск процедуры p_ZNOSuspectAdmin1
     * @param $InParam
     * @return bool
     */
    public function made_p_ZNOSuspectAdmin1($InParam)
    {
        $params = [
            'Person_id' => $InParam['Person_id'],
            'SetDate1' => $InParam['SetDate1'],
            'SetDate2' => $InParam['SetDate2'],
            'pmUser_id' => $InParam['pmUser_id']
        ];

        $query = "
            select
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\" ; 
            from dbo.p_ZNOSuspectAdmin_1
            (
				Person_id := :Person_id,
				pmUser_id := :pmUser_id,
				ZNOSuspectRegistry_Date1 :=	:SetDate1,
				ZNOSuspectRegistry_Date2 := :SetDate2
			) 
        ";

        $result = $this->db->query($query, $params);
        sql_log_message('error', 'p_ZNOSuspectAdmin_1: ', getDebugSql($query, $params));
        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     * Запуск процедуры p_ZNOSuspectAdmin2
     * @param $InParam
     * @return bool
     */
    public function made_p_ZNOSuspectAdmin2($InParam) {
        $params = array(
            'Person_id' => $InParam['Person_id'],
            'SetDate1' => $InParam['SetDate1'],
            'SetDate2' => $InParam['SetDate2'],
            'pmUser_id' => $InParam['pmUser_id']
        );

        $query = "
            select
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
            from dbo.p_ZNOSuspectAdmin_1
            (
				Person_id := :Person_id,
				pmUser_id := :pmUser_id,
				ZNOSuspectRegistry_Date1 :=	:SetDate1,
				ZNOSuspectRegistry_Date2 := :SetDate2 
            ) 
        ";

        $result = $this->db->query($query, $params);
        sql_log_message('error', 'p_ZNOSuspectAdmin_1: ', getDebugSql($query, $params));
        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

}
