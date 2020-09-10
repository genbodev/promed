<?php
defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Class MobileBrig_model
 *
 * @property-read CI_DB_active_record $db
 */
class MobileBrig_model extends SwPgModel
{

    protected $dateTimeForm103 = "'dd/mm/yyyy'";
    protected $dateTimeForm104 = "'dd.mm.yyyy'";
    protected $dateTimeForm108 = "'hh24.mi.ss'";
    protected $dateTimeForm113 = "'dd.mm.yyyy hh24:mi:ss:mmm'";


    /**
     * Получение информации о бригаде: ФИО состава, номер бригады
     *
     * @param $data
     * @return bool|array
     */
    public function setOnlineStatus($data)
    {
        $query = "
            select
                EmergencyTeam_id as \"CmpCallCard_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_EmergencyTeam_setOnline
			(
				EmergencyTeam_id := :EmergencyTeam_id,
				EmergencyTeam_IsOnline := :isOnline,
				pmUser_id := :pmUser_id
			)
		";
        $result = $this->db->query($query, $data);
        if (!is_object($result))
            return false;

        return $result->result('array');
    }

    /**
     * desc
     *
     * @param $data
     * @return bool|array
     */
    public function getEmergencyTeamData($data)
    {
        $queryParams = array();
        if (!empty($data['session']['medpersonal_id'])) {
            $queryParams['MedPersonal_id'] = $data['session']['medpersonal_id'];
        }
        $query = "
			select
				ET.EmergencyTeam_id as \"EmergencyTeam_id\",
				MSF.MedStaffFact_id as \"MedStaffFact_id\"
			from
				v_EmergencyTeam ET,
				v_MedStaffFact MSF 
			where
				EmergencyTeam_HeadShift = :MedPersonal_id
			and
				MSF.MedPersonal_id = :MedPersonal_id
			order by EmergencyTeam_insDT desc
			limit 1
		";
        $result = $this->db->query($query, $queryParams);
        if (!is_object($result))
            return false;

        return $result->result('array');
    }

    /**
     * desc
     *
     * @param $data
     * @return bool|array
     */
    function isSavedCallCard($data)
    {
        $query = "
			select 
				count(CCC.CmpCloseCard_id) as count
			from
				v_CmpCloseCard CCC
			where
				CCC.CmpCallCard_id = :CmpCallCard_id
		";
        $result = $this->db->query($query, $data);
        if (!is_object($result))
            return false;

        return $result->result('array');
    }

    /**
     * desc
     * @param $data
     * @return bool
     */
    public function getBrigInfo($data)
    {
        $queryParams = [];
        if (!empty($data['session']['medpersonal_id'])) {
            $queryParams['MedPersonal_id'] = $data['session']['medpersonal_id'];
        }
        if (!isset($queryParams['MedPersonal_id']) || $queryParams['MedPersonal_id'] == null) {
            return false;
        }
        $query = "
			select
				-- select
				ET.EmergencyTeam_id as \"EmergencyTeam_id\",
				ET.EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\",
				ET.EmergencyTeam_Driver as \"EmergencyTeam_Driver\",
				ET.EmergencyTeam_Assistant1 as \"EmergencyTeam_Assistant1\",
				ET.EmergencyTeam_Assistant2 as \"EmergencyTeam_Assistant2\",
				ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				HeadBrig_FIO.Person_Fio as \"HeadBrig_FIO\",
				HeadBrig_FIO.Person_ShortFio as \"HeadBrig_ShortFIO\",
				Assistant1_FIO.Person_Fio as \"Assistant1_FIO\",
				Assistant2_FIO.Person_Fio as \"Assistant2_FIO\",
				Driver_FIO.Person_Fio as \"Driver_FIO\"
				-- end select
			from
				-- from
				v_EmergencyTeam ET

				left join lateral (
					select
						Person_FirName || ' ' || Person_SecName || ' ' || Person_SurName as Person_Fio,
						Person_SurName || ' ' || SUBSTRING(Person_FirName, 1, 1) || '.' || SUBSTRING(Person_SecName, 1, 1) || '.' as Person_ShortFio
					from
						v_MedPersonal MP
					where
						MP.MedPersonal_id = :MedPersonal_id
				) HeadBrig_FIO on true
				left join lateral (
					select
						Person_Fio
					from
						v_MedPersonal MP
					where
						MP.MedPersonal_id = ET.EmergencyTeam_Assistant1
				) Assistant1_FIO on true
				
				left join lateral (
					select
						Person_Fio
					from
						v_MedPersonal MP
					where
						MP.MedPersonal_id = ET.EmergencyTeam_Assistant2
				) Assistant2_FIO on true
				
				left join lateral (
					select
						Person_Fio
					from
						v_MedPersonal MP
					where
						MP.MedPersonal_id = ET.EmergencyTeam_Driver
				) Driver_FIO on true
				-- end from
				where
					EmergencyTeam_HeadShift = :MedPersonal_id
				order by EmergencyTeam_insDT desc
				limit 1
		";

        $result = $this->db->query($query, $queryParams);
        if (!is_object($result))
            return false;

        return $result->result('array');
    }

    /**
     * desc
     * @return bool|array
     */
    public function getDiags()
    {
        $query = "
			select
				Diag_code as code,
				Diag_name as name,
				Diag_id as id
			from
				v_Diag
			where
				DiagLevel_id = 4
		";
        $result = $this->db->query($query);
        if (!is_object($result))
            return false;

        return $result->result('array');
    }

    /**
     * desc
     * @return bool|array
     */
    public function getDiagsControlNumber()
    {
        $query = "
			select
				count(Diag_id) as \"controlNumber\"
			from
				v_Diag
			where
				DiagLevel_id = 4
		";

        $result = $this->db->query($query);
        if (!is_object($result))
            return false;

        return $result->result('array');

    }

    /**
     * desc
     * @return bool|array
     */
    public function getFormFieldLabels()
    {

        $query = "						
			SELECT 
				CCCG.ComboName as \"GroupName\",
				CCCG.ComboSys as \"ComboSys\",
				CCCF.ComboName as \"ComboName\",
				CCCF.isLoc as \"isLoc\",
				CCCF.CmpCloseCardCombo_id as \"id\",
				CCCE.ComboName as \"secondLevelComboName\",
				CCCE.CmpCloseCardCombo_id as \"secondLevelId\"
			FROM
				CmpCloseCardCombo CCCG
				LEFT OUTER JOIN CmpCloseCardCombo CCCF ON (CCCG.CmpCloseCardCombo_id = CCCF.Parent_id)
				LEFT OUTER JOIN CmpCloseCardCombo CCCE ON (CCCF.CmpCloseCardCombo_id = CCCE.Parent_id)
			WHERE
				CCCG.Parent_id = '0'
		";


        $result = $this->db->query($query);

        if (!is_object($result))
            return false;

        $result = $result->result('array');
        $res = [];
        //var_dump($result);
        foreach ($result as /*$key =>*/ $value) {
            $res["{$value['ComboSys']}"][] = [
                'name' => $value['ComboName'],
                'loc' => $value['isLoc'],
                'id' => $value['id'],
                'legend' => $value['GroupName'],
                'secondLevelComboName' => $value['secondLevelComboName'],
                'secondLevelId' => $value['secondLevelId']
            ];
        }
        return $res;
    }


    /**
     * Установка статуса для бригады
     * @param $data
     * @return bool|array
     */
    function setBrigStatus($data)
    {
        $query = "
            select
                EmergencyTeam_id as \"EmergencyTeam_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_EmergencyTeam_setStatus
			(
				EmergencyTeam_id := :EmergencyTeam_id,
				EmergencyTeamStatus_id := :EmergencyTeamStatus_id,
				pmUser_id := :pmUser_id
			)
		";
        $result = $this->db->query($query, $data);
        if (!is_object($result))
            return false;

        return $result->result("array");
    }

    /**
     * desc
     * @param $data
     * @return bool|array
     */
    public function getUnclosedCards($data)
    {
        $query = "
			select
				CCC.CmpCallCard_id as \"CmpCallCard_id\",
				coalesce (PS.Person_id, 0) as \"Person_id\",
				to_char(cast(CCC.CmpCallCard_prmDT as timestamp), {$this->dateTimeForm113}) as \"CmpCallCard_prmDate\",
				coalesce (PS.Person_Surname, CCC.Person_SurName, '') || ' ' || coalesce (PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') || ' ' || coalesce(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as \"Person_FIO\",
				coalesce (PS.Person_Surname, CCC.Person_SurName) as \"Person_Surname\",
				coalesce (PS.Person_Firname, CCC.Person_FirName) as \"Person_Firname\",
				coalesce (PS.Person_Secname, CCC.Person_SecName) as \"Person_Secname\",
				to_char(coalesce (PS.Person_BirthDay, CCC.Person_BirthDay), {$this->dateTimeForm104}) as \"Person_Birthday\",
				RTRIM(coalesce (CR.CmpReason_Name, '')) as \"CmpReason_Name\",
				RTRIM(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code || '. ' else '' end || coalesce (CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\",
				to_char(cast(CCC.CmpCallCard_prmDT as timestamp), {$this->dateTimeForm103}) as \"CmpCallCard_prmDate\",
				to_char(cast(CCC.CmpCallCard_prmDT as timestamp ), {$this->dateTimeForm108}) as \"CmpCallCard_prmTime\",
				coalesce (CCC.Sex_id, 0) as \"Sex_id\",
				case 
				    when
				        DATEDIFF('yy', coalesce(PS.Person_BirthDay, coalesce (CCC.Person_BirthDay, '01.01.2000')), dbo.tzGetDate()) > 1
                    then AgeTypeValue.CmpCloseCardCombo_id
                    else
                        case
                            when DATEDIFF('mm', coalesce (PS.Person_BirthDay, CCC.Person_BirthDay), dbo.tzGetDate()) > 1
                        then AgeTypeValue.CmpCloseCardCombo_id + 1
					else
					    AgeTypeValue.CmpCloseCardCombo_id + 2
					end
				end as \"AgeType_value\",
				case
				    when
				        DATEDIFF('yy', coalesce(PS.Person_BirthDay, coalesce(CCC.Person_BirthDay, '01.01.2000')), dbo.tzGetDate()) > 1
                    then 
                        case
                            when
                                coalesce (PS.Person_BirthDay::varchar, coalesce (CCC.Person_BirthDay::varchar, '0')) = '0'
                            then null
                        else
                            DATEDIFF('yy', coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), dbo.tzGetDate())
                        end
				    else
				        case
				            when
				                DATEDIFF('mm', coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), dbo.tzGetDate()) > 1
				            then
				                DATEDIFF('mm', coalesce (PS.Person_BirthDay, CCC.Person_BirthDay), dbo.tzGetDate())
							else
							    DATEDIFF('dd', coalesce (PS.Person_BirthDay, CCC.Person_BirthDay), dbo.tzGetDate())
						end
				end as \"Age\",
				
				coalesce ( RGN.KLRgn_FullName,'')
					|| case when SRGN.KLSubRgn_FullName is not null then ', ' || SRGN.KLSubRgn_FullName else ', г.' || City.KLCity_Name end
					|| case when Town.KLTown_FullName is not null then ', ' || Town.KLTown_FullName else '' end
					|| case when Street.KLStreet_FullName is not null then ', ул.' || Street.KLStreet_Name else '' end
					|| case when CCC.CmpCallCard_Dom is not null then ', д.' || CCC.CmpCallCard_Dom else '' end
					|| case when CCC.CmpCallCard_Kvar is not null then ', кв.' || CCC.CmpCallCard_Kvar else ''
				end as \"Adress_Name\",

				CASE
					WHEN CCrT.CmpCallerType_id IS NOT NULL THEN 'Вызывает: ' || CCrT.CmpCallerType_Name
					WHEN CCC.CmpCallCard_Ktov IS NOT NULL THEN 'Вызывает: ' || CCC.CmpCallCard_Ktov
					ELSE ''
				END
				||
				CASE WHEN CCC.CmpCallCard_Telf IS NOT NULL THEN 'Телефон: ' || CCC.CmpCallCard_Telf ELSE '' END as \"CallerInfo\"
			FROM
				-- from
				v_CmpCallCard CCC

				left join v_PersonState PS on PS.Person_id = CCC.Person_id
				left join v_CmpReason CR on CR.CmpReason_id = CCC.CmpReason_id
				left join v_CmpCallType CCT on CCT.CmpCallType_id = CCC.CmpCallType_id
				LEFT JOIN v_CmpCallerType CCrT on CCrT.CmpCallerType_id=CCC.CmpCallerType_id
				
				left join v_EmergencyTeam ET on CCC.EmergencyTeam_id = ET.EmergencyTeam_id
				left join v_KLRgn RGN on RGN.KLRgn_id = CCC.KLRgn_id
				
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = CCC.KLStreet_id
				left join lateral (
					select
						CCCC.CmpCloseCardCombo_id
					from
						v_CmpCloseCardCombo CCCC
					where
						CCCC.Parent_id = 218
					order by
						CCCC.CmpCloseCardCombo_id asc
					limit 1
				) as AgeTypeValue on true
				-- end from
			where
				-- where
					CCC.EmergencyTeam_id = :EmergencyTeam_id
                and
                    DATEDIFF('hh', cast(CCC.CmpCallCard_prmDT as date), dbo.tzGetDate()) < 25
                and
                    CCC.CmpCallCardStatusType_id = 2
                and
                    CCC.CmpCallCard_IsOpen != 1
				-- end where
			order by
				-- order by
				CCC.CmpCallCard_prmDT desc
				-- end order by
			";

        $result = $this->db->query($query, $data);
        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     * Получение профилей стационаров с экстренными койками
     * @param $data
     * @return bool
     */
    function getClosedCards($data)
    {
        $query = "
			select
				CCC.CmpCallCard_id as \"CmpCallCard_id\",
				coalesce(PS.Person_id,0) as \"Person_id\",
				to_char(cast(CCC.CmpCallCard_prmDT as timestamp), {$this->dateTimeForm113}) as \"CmpCallCard_prmDate\",
				COALESCE(PS.Person_Surname, CCC.Person_SurName, '') || ' ' || COALESCE(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') || ' ' || COALESCE(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as \"Person_FIO\",
				coalesce(PS.Person_Surname, CCC.Person_SurName) as \"Person_Surname\",
				coalesce(PS.Person_Firname, CCC.Person_FirName) as \"Person_Firname\",
				coalesce(PS.Person_Secname, CCC.Person_SecName) as \"Person_Secname\",
				to_char(coalesce (PS.Person_BirthDay, CCC.Person_BirthDay), {$this->dateTimeForm104}) as \"Person_Birthday\",
				RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code || '. ' else '' end || coalesce (CR.CmpReason_Name, '')) as \"CmpReason_Name\",
				RTRIM(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code || '. ' else '' end || coalesce (CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\",
				to_char(cast(CCC.CmpCallCard_prmDT as timestamp), {$this->dateTimeForm103}) as \"CmpCallCard_prmDate\",
				to_char(cast(CCC.CmpCallCard_prmDT as timestamp), {$this->dateTimeForm108}) as \"CmpCallCard_prmTime\",
				coalesce (CCC.Sex_id, 0) as \"Sex_id\",
				case
				    when 
				        DATEDIFF('yy', coalesce (PS.Person_BirthDay, coalesce (CCC.Person_BirthDay, '01.01.2000')), dbo.tzGetDate()) > 1
				    then
				        AgeTypeValue.CmpCloseCardCombo_id
				    else
				        case
				            when DATEDIFF('mm', coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), dbo.tzGetDate()) > 1
				        then
				            AgeTypeValue.CmpCloseCardCombo_id + 1
					    else
					        AgeTypeValue.CmpCloseCardCombo_id + 2
				        end
				end as \"AgeType_value\",
				case
				    when
				        DATEDIFF('yy', coalesce (PS.Person_BirthDay, coalesce(CCC.Person_BirthDay, '01.01.2000')), dbo.tzGetDate()) > 1
                    then 
					    case when coalesce(PS.Person_BirthDay::varchar, coalesce(CCC.Person_BirthDay::varchar, '0')) = '0'
					then
					    null
					else
					    DATEDIFF('yy', coalesce (PS.Person_BirthDay, CCC.Person_BirthDay), dbo.tzGetDate())
					end
				else
				    case
				        when
				           DATEDIFF('mm', coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), dbo.tzGetDate()) > 1
				        then
				            DATEDIFF('mm', coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), dbo.tzGetDate())
                        else
                            DATEDIFF('dd', coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), dbo.tzGetDate())
                        end
				end as \"Age\",
				
				coalesce( RGN.KLRgn_FullName, '')
					|| case when SRGN.KLSubRgn_FullName is not null then ', ' || SRGN.KLSubRgn_FullName else ', г.' || City.KLCity_Name end
					|| case when Town.KLTown_FullName is not null then ', ' || Town.KLTown_FullName else '' end
					|| case when Street.KLStreet_FullName is not null then ', ул.' || Street.KLStreet_Name else '' end
					|| case when CCC.CmpCallCard_Dom is not null then ', д.' || CCC.CmpCallCard_Dom else '' end
					|| case when CCC.CmpCallCard_Kvar is not null then ', кв.' || CCC.CmpCallCard_Kvar else ''
				end as \"Adress_Name\",

				CASE
					WHEN CCrT.CmpCallerType_id IS NOT NULL THEN 'Вызывает: ' || CCrT.CmpCallerType_Name
					WHEN CCC.CmpCallCard_Ktov IS NOT NULL THEN 'Вызывает: ' || CCC.CmpCallCard_Ktov
					ELSE ''
				END
				||
				CASE WHEN CCC.CmpCallCard_Telf IS NOT NULL THEN 'Телефон: ' || CCC.CmpCallCard_Telf ELSE '' END as \"CallerInfo\"
			from
				-- from
				v_CmpCallCard CCC
				left join v_PersonState PS on PS.Person_id = CCC.Person_id
				left join v_CmpReason CR on CR.CmpReason_id = CCC.CmpReason_id
				left join v_CmpCallType CCT on CCT.CmpCallType_id = CCC.CmpCallType_id
				LEFT JOIN v_CmpCallerType CCrT on CCrT.CmpCallerType_id=CCC.CmpCallerType_id
				left join v_EmergencyTeam ET on CCC.EmergencyTeam_id = ET.EmergencyTeam_id
				left join v_KLRgn RGN on RGN.KLRgn_id = CCC.KLRgn_id	
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = CCC.KLStreet_id
				left join lateral (
					select
						CCCC.CmpCloseCardCombo_id
					from
						v_CmpCloseCardCombo CCCC 
					where
						CCCC.Parent_id = 218
					order by
						CCCC.CmpCloseCardCombo_id asc
					limit 1
				) AgeTypeValue on true
				-- end from
			where
				-- where
					CCC.EmergencyTeam_id = :EmergencyTeam_id
				and
				    DATEDIFF('hh', cast(CCC.CmpCallCard_prmDT as date), dbo.tzGetDate()) < 25
				and
				    CCC.CmpCallCardStatusType_id = 6
				-- end where
			order by
				-- order by
				CCC.CmpCallCard_prmDT desc
				-- end order by
			";

        $result = $this->db->query($query, $data);
        if (!is_object($result)) {
            return false;
        }
        return $result->result('array');
    }


    /**
     * Получение списка подстанций СМП
     *
     * @return false|array
     */
    public function loadLpu()
    {
        $sql = "
            SELECT
                L.Lpu_id as \"Lpu_id\",
                L.Lpu_Nick as \"Lpu_Nick\"
            FROM
                dbo.v_Lpu L
            ORDER BY 
                L.Lpu_Nick asc
        ";

        $query = $this->db->query($sql, array());
        if (!is_object($query))
            return false;

        return $query->result_array();

    }

    /**
     * Получение профилей стационаров с экстренными койками
     *
     * @return array
     */
    function getProfileList()
    {
        $sql = "
			SELECT DISTINCT 
				LS.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				LS.LpuSectionProfile_OMSCode as \"LpuSectionProfile_OMSCode\"
			FROM
				v_LpuSection LS
			WHERE
				coalesce(LS.LpuSectionHospType_id, 1) IN (6,7)
		";
        $result = $this->db->query($sql);
        $val = [
            'success' => false,
            'identity' => 0,
            'Error_Code' => '',
            'Error_Msg' => 'Ошибка при получении списка профилей'
        ];

        if (!is_object($result)) {
            return [$val];
        }

        $res = $result->result('array');
        if (count($res) > 0) {
            $r = [];
            foreach ($res as $row) {
                if (is_array($row))
                    array_walk($row, 'ConvertFromWin1251ToUTF8');
                $r[] = $row;
            }
            $val = [
                'success' => true,
                'Error_Code' => 0,
                'Error_Msg' => '',
                'data' => $r
            ];
        } else {
            $val = [
                'success' => true,
                'Error_Code' => 0,
                'Error_Msg' => '',
                'data' => []
            ];
        }

        return [$val];
    }

    /**
     * Получение всех возможных статусов
     *
     * @return bool|array
     */
    function getStatuses()
    {
        $query = "
			select 
				EmergencyTeamStatus_Name as \"EmergencyTeamStatus_Name\",
				EmergencyTeamStatus_Code as \"EmergencyTeamStatus_Code\",
				EmergencyTeamStatus_Id as \"EmergencyTeamStatus_Id\"
			from
			    v_EmergencyTeamStatus
        ";
        $result = $this->db->query($query);
        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }


    /**
     * desc
     * @param $data
     * @return bool|array
     */
    function callAccepted($data)
    {
        $queryParams['CmpCallCard_id'] = $data['CmpCallCard_id'];
        //Здесь будет запрос на установку времени в таблицу закртыия карты вызова
        $query = "
				select 1
			";
        $result = $this->db->query($query, $queryParams);
        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }
}