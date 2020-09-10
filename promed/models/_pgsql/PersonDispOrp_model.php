<?php
/**
 * PersonDispOrp_model - модель, для работы с таблицей PersonDispOrp
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Марков Андрей
 * @version      май 2010
 */

class PersonDispOrp_model extends SwPgModel
{
    /**
     * @param $data
     * @return bool|mixed
     */
    public function loadPersonDispOrpListForDbf($data)
    {
        $sql = "
			select
				rtrim(coalesce(og.Org_OGRN, '')) as \"ORG_OGRN\",
				rtrim(coalesce(og.Org_name, '')) as \"LPU_NAME\",
				rtrim(ps.Person_SurName) as \"FAM\",
				rtrim(ps.Person_FirName) as \"IM\",
				rtrim(coalesce(ps.Person_SecName, '')) as \"OT\",
				rtrim(coalesce(to_char(cast(ps.Person_BirthDay as timestamp), 'dd.mm.yyyy'),'')) as \"DR\",
				rtrim(coalesce(og1.Org_OGRN, '')) as \"LPU_OGRN\"
			from
				v_PersonDispOrp pdd
				inner join v_PersonState ps on ps.Person_id = pdd.Person_id
				left join Job jb on jb.Job_id = ps.Job_id
				left join Org og on og.Org_id = jb.Org_id
				left join Okved okvd on okvd.Okved_id = og.Okved_id
				left join Lpu lp on lp.Lpu_id = pdd.Lpu_id
				left join Org og1 on og1.Org_id = lp.Org_id
			where
				PersonDispOrp_Year = :PersonDispOrp_Year
			order by 
				ps.Person_SurName,
				ps.Person_FirName,
				ps.Person_SecName,
				ps.Person_BirthDay
		";
        $res = $this->db->query($sql, $data);
        if (!is_object($res))
            return false;

        return $res->result('array');

    }

    /**
     * @param $data
     * @return bool|array
     */
    public function loadPersonDispOrpLpuReportForDbf($data)
    {
        $sql = "
			select
				lp.Lpu_Name as \"Lpu_Name\",
				count(PersonDispOrp_id) as \"cnt\"
			from
				v_PersonDispOrp pdd				
				inner join v_Lpu lp on lp.Lpu_id = pdd.Lpu_id				
			where
				PersonDispOrp_Year = :PersonDispOrp_Year
			group by lp.Lpu_Name
			order by lp.Lpu_Name
		";
        $res = $this->db->query($sql, $data);
        if (!is_object($res))
            return false;

        return $res->result('array');
    }

    /**
     * Возвращает список людей в регистре ДД по заданным фильтрам для поточного ввода
     * @param $data
     * @return bool|array
     */
    public function getPersonDispOrpStreamInputList($data)
    {
        $sql = "
			SELECT DISTINCT
				v_PersonDispOrp.PersonDispOrp_id as \"PersonDispOrp_id\",
				v_PersonState.Person_id as \"Person_id\",
				v_PersonState.Server_id as \"Server_id\",
				rtrim(v_PersonState.Person_SurName) as \"Person_SurName\",
				rtrim(v_PersonState.Person_FirName) as \"Person_FirName\",
				rtrim(v_PersonState.Person_SecName) as \"Person_SecName\",
				Sex.Sex_Name as \"Sex_Name\",
				v_PersonState.Polis_Ser as \"Polis_Ser\",
				v_PersonState.Polis_Num as \"Polis_Num\",
				okved1.Okved_Name as \"PersonOrg_Okved\",
				org1.Org_OGRN as \"PersonOrg_OGRN\",
				astat1.KLArea_Name as \"Person_KLAreaStat_Name\",
				astat2.KLArea_Name as \"PersonOrg_KLAreaStat_Name\",
				rtrim(addr1.Address_Address) as \"UAddress_Address\",
				to_char(cast(v_PersonState.Person_BirthDay as timestamp), 'dd.mm.yyyy') as \"Person_BirthDay\"
			FROM
			    v_PersonDispOrp
			    INNER JOIN v_PersonState on v_PersonDispOrp.Person_id=v_PersonState.Person_id
			    LEFT JOIN Sex on v_PersonState.Sex_id = Sex.Sex_id
			    LEFT JOIN v_Job as job1 ON v_PersonState.Job_id=job1.Job_id
			    LEFT JOIN v_Org as org1 ON job1.Org_id=org1.Org_id
			    LEFT JOIN v_Okved as okved1 ON okved1.Okved_id=org1.Okved_id
			    LEFT JOIN v_Address as addr1 ON v_PersonState.UAddress_id=addr1.Address_id
                LEFT JOIN v_KLAreaStat as astat1 ON (
                    ((addr1.KLCountry_id = astat1.KLCountry_id) or (astat1.KLCountry_id is null)) and
                    ((addr1.KLRGN_id = astat1.KLRGN_id) or (astat1.KLRGN_id is null)) and
                    ((addr1.KLSubRGN_id = astat1.KLSubRGN_id) or (astat1.KLSubRGN_id is null)) and
                    ((addr1.KLCity_id = astat1.KLCity_id) or (astat1.KLCity_id is null)) and
                    ((addr1.KLTown_id = astat1.KLTown_id) or (astat1.KLTown_id is null))
                ) 
                LEFT JOIN v_Address as addr2 ON org1.UAddress_id=addr2.Address_id 
                LEFT JOIN v_KLAreaStat as astat2 ON (
                    ((addr2.KLCountry_id = astat2.KLCountry_id) or (astat2.KLCountry_id is null)) and
                    ((addr2.KLRGN_id = astat2.KLRGN_id) or (astat2.KLRGN_id is null)) and
                    ((addr2.KLSubRGN_id = astat2.KLSubRGN_id) or (astat2.KLSubRGN_id is null)) and
                    ((addr2.KLCity_id = astat2.KLCity_id) or (astat2.KLCity_id is null)) and
                    ((addr2.KLTown_id = astat2.KLTown_id) or (astat2.KLTown_id is null))
                )
                LEFT JOIN v_PersonCard ON v_PersonCard.Person_id = v_PersonState.Person_id
                LEFT JOIN v_LpuRegion ON v_LpuRegion.LpuRegion_id = v_PersonCard.LpuRegion_id
			WHERE
				v_PersonDispOrp.pmUser_updID = :pmUser_id and
				v_PersonDispOrp.Lpu_id = :Lpu_id and
				PersonDispOrp_updDT >= :BegDate
		";

        $params = [
            'pmUser_id' => $data['pmUser_id'],
            'Lpu_id' => $data['Lpu_id'],
            'BegDate' => ($data['beg_date'] . " " . $data['beg_time'])
        ];
        $res = $this->db->query($sql, $params);
        if (!is_object($res))
            return false;

        return $res->result('array');
    }

    /**
     * Возвращает список людей в регистре ДД по заданным фильтрам
     * @param $data
     * @return bool|array
     */
    public function getPersonDispOrpList($data)
    {
        $this->load->helper('Text');
        $this->load->helper('Main');
        $this->load->helper('Date');

        $join = '';
        $filters = [];
        $filters[] = "v_PersonDispOrp.Lpu_id = {$data['session']['lpu_id']} and v_PersonDispOrp.PersonDispOrp_Year = " . ArrayVal($data, 'PersonDispOrp_Year', 'null');
        // 1. Основной фильтр
        if (ArrayVal($data, 'Person_SurName') != '')
            $filters[] = "v_PersonState.Person_SurName ilike '{$data['Person_SurName']}%'";
        if (ArrayVal($data, 'Person_FirName') != '')
            $filters[] = "v_PersonState.Person_FirName ilike '{$data['Person_FirName']}%'";
        if (ArrayVal($data, 'Person_SecName') != '')
            $filters[] = "v_PersonState.Person_SecName ilike '{$data['Person_SecName']}%'";
        if (ArrayVal($data, 'Person_BirthDay') != '') {
            $ar = ExplodeTwinDate($data['Person_BirthDay']);
            $filters[] = "v_PersonState.Person_BirthDay between '{$ar[0]}' and '{$ar[1]}'";
        }
        if (ArrayVal($data, 'PersonAge_From') != '' && ArrayVal($data, 'PersonAge_To') != '') {
            $filters[] = "
            (
                (
                    datediff('year', v_PersonState.Person_BirthDay, dbo.tzGetDate())
                    + 
                    case 
                        when date_part('month', v_PersonState.Person_BirthDay) > date_part('month', dbo.tzGetDate())
                        or (
                            date_part('month', v_PersonState.Person_BirthDay) = date_part('month', dbo.tzGetDate()) 
                        and 
                            date_part('day', v_PersonState.Person_BirthDay) > date_part('day', dbo.tzGetDate())
                        )
                    then -1 else 0 end
                ) between '{$data['PersonAge_From']}' and '{$data['PersonAge_To']}'
            )";
        }
        if (ArrayVal($data, 'Person_Snils') != '') {
            $filters[] = "v_PersonState.Person_Snils = '{$data['Person_Snils']}'";
        }

        if (ArrayVal($data, 'PersonCard_begDate') != '') {
            $ar = ExplodeTwinDate($data['PersonCard_begDate']);
            $filters[] = "v_PersonCard.PersonCard_begDate between '{$ar[0]}' and '{$ar[1]}'";
        }

        if (ArrayVal($data, 'PersonCard_begDate') != '') {
            $ar = ExplodeTwinDate($data['PersonCard_endDate']);
            $filters[] = "v_PersonCard.PersonCard_endDate between '{$ar[0]}' and '{$ar[1]}'";
        }

        if (ArrayVal($data, 'LpuRegionType_id') != '') {
            $filters[] = "v_PersonCard.LpuRegionType_id = {$data['LpuRegionType_id']}";
        }

        if (ArrayVal($data, 'LpuRegion_id') != '') {
            $filters[] = "v_PersonCard.LpuRegion_id = {$data['LpuRegion_id']}";
        }

        if (ArrayVal($data, 'PersonCard_Code') != '') {
            $filters[] = "v_PersonCard.PersonCard_Code = '{$data['PersonCard_Code']}'";
        }

        if (ArrayVal($data, 'LpuUnit_id') != '') {
            $filters[] = "(v_PersonCard.LpuUnit_id = {$data['LpuUnit_id']} or v_LpuRegion.LpuUnit_id = {$data['LpuUnit_id']})";
        }

        // Вкладка Пациент
        if (ArrayVal($data, 'Sex_id') != '') {
            $filters[] = "v_PersonState.Sex_id = {$data['Sex_id']}";
        }
        if (ArrayVal($data, 'SocStatus_id') != '') {
            $filters[] = "v_PersonState.SocStatus_id = {$data['SocStatus_id']}";
        }

        if (ArrayVal($data, 'DocumentType_id') != '' || ArrayVal($data, 'OrgDep_id') != '') {
            $join .= "inner join v_Document ON v_PersonState.Document_id=v_Document.Document_id ";
            if (ArrayVal($data, 'DocumentType_id') != '')
                $join .= "and v_Document.DocumentType_id = {$data['DocumentType_id']} ";
            if (ArrayVal($data, 'OrgDep_id') != '')
                $join .= "and v_Document.OrgDep_id = {$data['OrgDep_id']} ";
        }

        if (ArrayVal($data, 'OrgSmo_id') != '' || ArrayVal($data, 'PolisType_id') != '' || ArrayVal($data, 'OmsSprTerr_id') != '') {
            $join .= "INNER JOIN v_Polis ON v_PersonState.Polis_id=v_Polis.Polis_id ";
            if (ArrayVal($data, 'OrgSmo_id') != '')
                $join .= "and v_Polis.OrgSmo_id = {$data['OrgSmo_id']} ";
            if (ArrayVal($data, 'PolisType_id') != '')
                $join .= "and v_Polis.PolisType_id = {$data['PolisType_id']} ";
            if (ArrayVal($data, 'OmsSprTerr_id') != '')
                $join .= "and v_Polis.OmsSprTerr_id = {$data['OmsSprTerr_id']} ";
        }
        if (ArrayVal($data, 'Post_id') != '') {
            $join .= "INNER JOIN v_Job ON v_PersonState.Job_id=v_Job.Job_id and v_Job.Post_id = {$data['Post_id']} ";
        }

        if (ArrayVal($data, 'PrivilegeType_id') != '') {
            $join .= "
             INNER JOIN v_PersonPrivilege ON v_PersonState.Person_id=v_PersonPrivilege.Person_id
                and v_PersonPrivilege.Lpu_id = {$data['session']['lpu_id']}
                and v_PersonPrivilege.PrivilegeType_id = {$data['PrivilegeType_id']}
            ";
        }

        if (ArrayVal($data, 'Org_id') != '')
            $filters[] = "v_PersonState.Org_id = {$data['Org_id']}";

        if (ArrayVal($data, 'PersonRefuse_IsRefuse') != '') {
            $filters[] = "PR.PersonRefuse_IsRefuse = {$data['PersonRefuse_IsRefuse']}";
        }

        if (ArrayVal($data, 'IsRefuseNextYear') != '') {
            $year = 1 + date('Y');
            $filters[] = "PR.PersonRefuse_IsRefuse = 2 and PR.PersonRefuse_Year = {$year}";
        } else {
            $filters[] = "PR.PersonRefuse_IsRefuse = 2 and PR.PersonRefuse_Year = date_part('year', dbo.tzGetDate())";
        }

        // Вкладка Адрес
        if (ArrayVal($data, 'KLCountry_id') != '' || ArrayVal($data, 'KLRgn_id') != '' || ArrayVal($data, 'KLSubRgn_id') != '' || ArrayVal($data, 'KLCity_id') != '' || ArrayVal($data, 'KLTown_id') != '' || ArrayVal($data, 'KLStreet_id') != '' || ArrayVal($data, 'Address_House') != '' || ArrayVal($data, 'KLAreaType_id') != '') {
            $join .= "INNER JOIN v_Address ON v_PersonState.UAddress_id=v_Address.Address_id ";
            if (ArrayVal($data, 'KLCountry_id') != '') {
                $join .= "and v_Address.KLCountry_id = {$data['KLCountry_id']} ";
            }

            if (ArrayVal($data, 'KLRgn_id') != ''){
                $join .= "and v_Address.KLRgn_id = {$data['KLRgn_id']} ";
            }
            if (ArrayVal($data, 'KLSubRgn_id') != '') {
                $join .= "and v_Address.KLSubRgn_id = {$data['KLSubRgn_id']} ";
            }
            if (ArrayVal($data, 'KLCity_id') != '') {
                $join .= "and v_Address.KLCity_id={$data['KLCity_id']} ";
            }
            if (ArrayVal($data, 'KLTown_id') != '') {
                $join .= "and v_Address.KLTown_id={$data['KLTown_id']} ";
            }
            if (ArrayVal($data, 'KLStreet_id') != '') {
                $join .= "and v_Address.KLStreet_id={$data['KLStreet_id']} ";
            }
            if (ArrayVal($data, 'Address_House') != '') {
                $join .= "and v_Address.Address_House='{$data['Address_House']}' ";
            }

            if (ArrayVal($data, 'KLAreaType_id') != '') {
                $join .= "and v_Address.KLAreaType_id={$data['KLAreaType_id']} ";
            }
        }

        $sql = "
            SELECT
                DISTINCT 
                v_PersonDispOrp.PersonDispOrp_id as \"PersonDispOrp_id\",
                v_PersonState.Person_id as \"Person_id\",
                v_PersonState.Server_id as \"Server_id\",
                rtrim(v_PersonState.Person_SurName) as \"Person_SurName\",
                rtrim(v_PersonState.Person_FirName)as \"Person_FirName\",
                rtrim(v_PersonState.Person_SecName) as \"Person_SecName\",
                Sex.Sex_Name as \"Sex_Name\",
                v_PersonState.Polis_Ser as \"Polis_Ser\",
                v_PersonState.Polis_Num as \"Polis_Num\",
                okved1.Okved_Name as \"PersonOrg_Okved\",
                org1.Org_OGRN as \"PersonOrg_OGRN\",
                astat1.KLArea_Name as \"Person_KLAreaStat_Name\",
                astat2.KLArea_Name as \"PersonOrg_KLAreaStat_Name\",
                rtrim(addr1.Address_Address) as \"UAddress_Address\",
                to_char(cast(v_PersonState.Person_BirthDay as timestamp), 'dd.mm.yyyy') as \"Person_BirthDay\"
			FROM
                v_PersonDispOrp
                INNER JOIN v_PersonState on v_PersonDispOrp.Person_id=v_PersonState.Person_id
                LEFT JOIN v_PersonRefuse PR on PR.Person_id = v_PersonDispOrp.Person_id 
                LEFT JOIN Sex on v_PersonState.Sex_id = Sex.Sex_id 
                LEFT JOIN v_Job as job1 ON v_PersonState.Job_id=job1.Job_id 
                LEFT JOIN v_Org as org1 ON job1.Org_id=org1.Org_id 
                LEFT JOIN v_Okved as okved1 ON okved1.Okved_id=org1.Okved_id 
                LEFT JOIN v_Address as addr1 ON v_PersonState.UAddress_id=addr1.Address_id 
                LEFT JOIN v_KLAreaStat as astat1 ON (
                    ((addr1.KLCountry_id = astat1.KLCountry_id) or (astat1.KLCountry_id is null)) and
                    ((addr1.KLRGN_id = astat1.KLRGN_id) or (astat1.KLRGN_id is null)) and
                    ((addr1.KLSubRGN_id = astat1.KLSubRGN_id) or (astat1.KLSubRGN_id is null)) and
                    ((addr1.KLCity_id = astat1.KLCity_id) or (astat1.KLCity_id is null)) and
                    ((addr1.KLTown_id = astat1.KLTown_id) or (astat1.KLTown_id is null))
                )
                LEFT JOIN v_Address as addr2 ON org1.UAddress_id=addr2.Address_id
                LEFT JOIN v_KLAreaStat as astat2 ON (
                    ((addr2.KLCountry_id = astat2.KLCountry_id) or (astat2.KLCountry_id is null)) and
                    ((addr2.KLRGN_id = astat2.KLRGN_id) or (astat2.KLRGN_id is null)) and
                    ((addr2.KLSubRGN_id = astat2.KLSubRGN_id) or (astat2.KLSubRGN_id is null)) and
                    ((addr2.KLCity_id = astat2.KLCity_id) or (astat2.KLCity_id is null)) and
                    ((addr2.KLTown_id = astat2.KLTown_id) or (astat2.KLTown_id is null))
                )
                LEFT JOIN v_PersonCard ON v_PersonCard.Person_id = v_PersonState.Person_id
                LEFT JOIN v_LpuRegion ON v_LpuRegion.LpuRegion_id = v_PersonCard.LpuRegion_id
                {$join}
		";
        $sql .= ImplodeWhere($filters) .' limit 101';
        $res = $this->db->query($sql);
        if (is_object($res))
            return $res->result('array');
        else
            return false;
    }

    /**
     * @param $data
     * @return bool|array
     */
    public function getPersonDispOrpYearsCombo($data)
    {
        $sql = "
			select
				count(PersonDispOrp_id) as count,
				PersonDispOrp_Year as \"PersonDispOrp_Year\"
			from
				v_PersonDispOrp
			where
				Lpu_id = :Lpu_id and PersonDispOrp_Year <= 2012
			GROUP BY
				PersonDispOrp_Year
			ORDER BY
				PersonDispOrp_Year
		";

        $res = $this->db->query($sql, $data);
        if (!is_object($res))
            return false;

        return $res->result('array');
    }

    /**
     * @param $data
     * @return bool|array
     */
    public function getPersonData($data)
    {
        $sql = "
			SELECT
				pstate.Person_SurName as \"Person_SurName\",
				pstate.Person_FirName as \"Person_FirName\",
				pstate.Person_BirthDay as \"Person_BirthDay\",
				pstate.SocStatus_id as \"SocStatus_id\",
				pstate.Sex_id as \"Sex_id\",
				pstate.UAddress_id as \"UAddress_id\",
				dbo.Age2(Person_BirthDay, dbo.tzGetDate()) as \"Person_Age\",
				polis.Polis_Ser as \"Polis_Ser\",
				polis.Polis_Num as \"Polis_Num\",
				polis.OrgSmo_id as \"OrgSmo_id\",
				dbo.CheckINN(og.Org_INN) as \"Check_INN\",
				og.Org_id as \"Org_id\",
				og.Org_INN as \"Org_INN\",
				dbo.CheckOGRN(og.Org_OGRN) as \"Check_OGRN\",
				og.Org_OGRN as \"Org_OGRN\",
				og.Okved_id as \"Okved_id\",
				og.UAddress_id as \"OrgUAddress_id\"
			FROM
			    v_PersonState pstate
			    LEFT JOIN Polis as polis on polis.Polis_id = pstate.Polis_id
			    LEFT JOIN Job on Job.Job_id=pstate.Job_id
			    LEFT JOIN v_Org as og on og.Org_id = Job.Org_id
			WHERE
				pstate.Person_id = :Person_id
		";
        $res = $this->db->query($sql, $data);
        if (!is_object($res))
            return false;

        return $res->result('array');
    }

    /**
     * @param $data
     * @return bool|mixed
     */
    public function getYearInOldRegistry($data)
    {
        $sql = "
			select
				PersonDispOrp_Year as \"PersonDispOrp_Year\"
			from
			    PersonDispOrpRegOld
			where
				Person_id = :Person_id
			and
			    not (PersonDispOrp_Year = 2006 and HealthGroup_id in (1,2))
		";
        $res = $this->db->query($sql, $data);
        if (!is_object($res))
            return false;

        $sel = $res->result('array');
        if (count($sel) == 0)
            $sel[0]['PersonDispOrp_Year'] = 0;
        return $sel;
    }

    /**
     * @param $data
     * @return bool|array
     */
    public function addPersonDispOrp($data)
    {
        $params = [
            'Person_id' => $data['Person_id'],
            'lpu_id' => $data['session']['lpu_id'],
            'PersonDispOrp_Year' => $data['PersonDispOrp_Year']
        ];
        // проверка на присутствие человека в регистре
        $sql = "
			select
				count(Person_id) as count
			from
				v_PersonDispOrp
			where
				Person_id = :Person_id
			and
				Lpu_id = :lpu_id
			and
				PersonDispOrp_Year = :PersonDispOrp_Year
		";
        $res = $this->db->query($sql, $params);

        if (!is_object($res)) {
            $sel[0]['Error_Code'] = 1;
            $sel[0]['Error_Msg'] = 'Не удалось проверить наличие человека в регистре';
            return $sel;
        }

        $sel = $res->result('array');
        if ($sel[0]['count'] > 0) {
            $sel[0]['Error_Code'] = 666;
            $sel[0]['Error_Msg'] = 'Данный пациент уже внесен в регистр по ДД в вашем ЛПУ';
            return $sel;
        }

        // проверка на присутствие человека в регистре другого ЛПУ
        if (!isset($data['cancel_check_other_lpu'])) {
            $sql = "
				select
					count(Person_id) as count
				from
					v_PersonDispOrp
				where
					Person_id = :Person_id
				and
					Lpu_id <> :lpu_id
				and
					PersonDispOrp_Year = :PersonDispOrp_Year
			";
            $res = $this->db->query($sql, $params);
            if (!is_object($res)) {
                $sel = $res->result('array');
                if ($sel[0]['count'] > 0) {
                    $sel[0]['Error_Code'] = '666';
                    $sel[0]['Error_Msg'] = 'Данный пациент добавлен в регистр другого ЛПУ.';
                    return $sel;
                }
            } else {
                $sel[0]['Error_Code'] = 1;
                $sel[0]['Error_Msg'] = 'Не удалось проверить наличие человека в регистре';
                return $sel;
            }
        }

        $sql = "
			select
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_PersonDispOrp_ins
			(
				Server_id := {$data['Server_id']},
				Person_id := {$data['Person_id']},
				Lpu_id := {$data['session']['lpu_id']},
				PersonDispOrp_Year := {$data['PersonDispOrp_Year']},
				pmUser_id := {$data['session']['pmuser_id']}
			)
		";

        $res = $this->db->query($sql);
        if (!is_object($res))
            return false;

        return $res->result('array');
    }
}