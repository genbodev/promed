<?php
defined('BASEPATH') or die('No direct script access allowed');

/**
 *  model Регистра ИПРА (индивидуальная программа реабилитации и абилитации)
 *
 * @package            IPRA
 * @author            Васинский Игорь
 * @version            24.02.2016
 *
 * class IPRARegister_model
 *
 * @property-read CI_DB_driver $db
 */
class IPRARegister_model extends SwPgModel
{

    protected $dateTimeForm120 = "'yyyy-mm-dd'";

    protected $scheme = 'dbo';

    /**
     * Comment
     */
    var $useTest = false;

    /**
     * получения списка всех ИПРА с ошибками
     *
     * @param $data
     * @return array|bool
     */
    public function getIPRARegistryErrors($data)
    {
        $params = [
            'sort' => isset($data['sort']) ? ($data['sort'] == 'Person_FIO' ? 'Person_SurName' : $data['sort']) : 'IPRARegistry_insDT',
            'dir' => isset($data['dir']) ? $data['dir'] : 'DESC',
            'alias' => in_array($data['sort'], ['Person_Snils', 'Person_FIO', 'Person_BirthDay']) ? 'PS' : 'IR'
        ];
        $filters = "";

        if (getRegionNick() != 'ufa' && !havingGroup('IPRARegistryEdit')) {
            $filters .= "and (Lpu.Lpu_id = :Lpu_id or IR.IPRARegistry_DirectionLPU_id = :Lpu_id)";
            $params['Lpu_id'] = $data['Lpu_id'];
        }

        $query = "
            select 
                -- select
                IR.IPRARegistry_id as \"IPRARegistry_id\",
                Lpu.Lpu_id as \"LpuAttach_id\",
                L.Lpu_Nick as \"Lpu_Nick\",
                case
                    when length(PS.Person_Snils) = 11
                        then left(PS.Person_Snils, 3) || '-' || substring(PS.Person_Snils, 4, 3) || '-' || 
                            substring(PS.Person_Snils, 7, 3) || ' ' || right(PS.Person_Snils, 2)
                        else
                            PS.Person_Snils
                end as \"Person_Snils\",
                case when IR.Person_id is not null 
					then
					    coalesce(PS.Person_SurName,'') || coalesce(' '||PS.Person_FirName,'') || coalesce(' ' || PS.Person_SecName,'')
					else
					    coalesce(IRE.IPRARegistryError_SurName,'') || coalesce(' ' || IRE.IPRARegistryError_FirName,'') || coalesce(' ' || IRE.IPRARegistryError_SecName,'')
                end as \"Person_FIO\",
                case when IR.Person_id is not null 
					then PS.Person_SurName else IRE.IPRARegistryError_SurName
                end as \"Person_SurName\",
                case when IR.Person_id is not null 
					then PS.Person_FirName else IRE.IPRARegistryError_FirName
                end as \"Person_FirName\",
                case when IR.Person_id is not null 
					then PS.Person_SecName else IRE.IPRARegistryError_SecName
                end as \"Person_SecName\",
                case when IR.Person_id is not null 
					then
					    to_char(PS.Person_BirthDay::timestamp, {$this->dateTimeForm120}) 
					else
					    to_char(IRE.IPRARegistryError_BirthDay::timestamp, {$this->dateTimeForm120})
                end as \"Person_BirthDay\",
                IPRARegistry_FGUMCE.LpuBuilding_Name as \"IPRARegistry_FGUMCE\",      

                IR.IPRARegistry_IPRAident as \"IPRARegistry_IPRAident\",
                IR.IPRARegistry_Number as \"IPRARegistry_Number\",
                IR.IPRARegistry_RecepientType as \"IPRARegistry_RecepientType\",
                to_char(IPRARegistry_issueDate, {$this->dateTimeForm120}) as \"IPRARegistry_issueDate\",
                to_char(IPRARegistry_EndDate, {$this->dateTimeForm120}) as \"IPRARegistry_EndDate\",
                cast(IR.IPRARegistry_FGUMCEnumber as int) as \"IPRARegistry_FGUMCEnumber\",
                IR.IPRARegistry_Protocol as \"IPRARegistry_Protocol\",
                to_char(IPRARegistry_ProtocolDate, {$this->dateTimeForm120}) as IPRARegistry_ProtocolDate,
                to_char(IPRARegistry_DevelopDate, {$this->dateTimeForm120}) as IPRARegistry_DevelopDate,
                IR.IPRARegistry_isFirst as \"IPRARegistry_isFirst\",
                IR.IPRARegistry_Confirm as \"IPRARegistry_Confirm\",
                IR.IPRARegistry_DirectionLPU_id as \"IPRARegistry_DirectionLPU_id\",
                IR.Lpu_id as \"Lpu_id\",
                IR.Person_id as \"Person_id\",
                to_char(IR.IPRARegistry_insDT, 'dd.mm.yyyy hh24:mi') as \"IPRARegistry_insDT\",
                IR.IPRARegistry_FileName as \"IPRARegistry_FileName\",
                IR.IPRARegistry_updDT as \"IPRARegistry_updDT\",

                IRE.IPRARegistryError_id as \"IPRARegistryError_id\",
                IRE.IPRARegistryError_SelfService as \"IPRARegistryError_SelfService\",
                IRE.IPRARegistryError_Move as \"IPRARegistryError_Move\",
                IRE.IPRARegistryError_Orientation as \"IPRARegistryError_Orientation\",
                IRE.IPRARegistryError_Communicate as \"IPRARegistryError_Communicate\",
                IRE.IPRARegistryError_Learn as \"IPRARegistryError_Learn\",
                IRE.IPRARegistryError_Work as \"IPRARegistryError_Work\",
                IRE.IPRARegistryError_Behavior as \"IPRARegistryError_Behavior\",
                IRE.IPRARegistryError_MedRehab as \"IPRARegistryError_MedRehab\",
                to_char(IRE.IPRARegistryError_MedRehab_begDate, {$this->dateTimeForm120}) as \"IPRARegistryError_MedRehab_begDate\",
                to_char(IRE.IPRARegistryError_MedRehab_endDate, {$this->dateTimeForm120}) as \"IPRARegistryError_MedRehab_endDate\",
                IRE.IPRARegistryError_Orthotics as \"IPRARegistryError_Orthotics\",
                to_char(IRE.IPRARegistryError_Orthotics_begDate, {$this->dateTimeForm120}) as \"IPRARegistryError_Orthotics_begDate\",
                to_char(IRE.IPRARegistryError_Orthotics_endDate, {$this->dateTimeForm120}) as \"IPRARegistryError_Orthotics_endDate\",
                IRE.IPRARegistryError_ReconstructSurg as \"IPRARegistryError_ReconstructSurg\",
                to_char(IRE.IPRARegistryError_ReconstructSurg_begDate, {$this->dateTimeForm120}) as \"IPRARegistryError_ReconstructSurg_begDate\",
                to_char(IRE.IPRARegistryError_ReconstructSurg_endDate, {$this->dateTimeForm120}) as \"IPRARegistryError_ReconstructSurg_endDate\",
                IRE.IPRARegistryError_Restoration as \"IPRARegistryError_Restoration\",
                IRE.IPRARegistryError_Compensate as \"IPRARegistryError_Compensate\",
                IRE.IPRARegistryError_insDT as \"IPRARegistryError_insDT\",
                IRE.IPRARegistryError_updDT as \"IPRARegistryError_updDT\",
                IRE.IPRARegistryError_PrimaryProfession as \"IPRARegistryError_PrimaryProfession\",
                IRE.IPRARegistryError_PrimaryProfessionExperience as \"IPRARegistryError_PrimaryProfessionExperience\",	
                IRE.IPRARegistryError_Qualification as \"IPRARegistryError_Qualification\",   
                IRE.IPRARegistryError_CurrentJob as \"IPRARegistryError_CurrentJob\",
                IRE.IPRARegistryError_NotWorkYears as \"IPRARegistryError_NotWorkYears\",
                IRE.IPRARegistryError_ExistEmploymentOrientation as \"IPRARegistryError_ExistEmploymentOrientation\",
                IRE.IPRARegistryError_isRegInEmplService as \"IPRARegistryError_isRegInEmplService\",
                IRE.IPRARegistryError_IsDisabilityGroupPrimary as \"IPRARegistryError_IsDisabilityGroupPrimary\",
                IRE.IPRARegistryError_IsIntramural as \"IPRARegistryError_IsIntramural\",
                to_char(IRE.IPRARegistryError_DisabilityGroupDate, {$this->dateTimeForm120}) as \"IPRARegistryError_DisabilityGroupDate\", 
                to_char(IRE.IPRARegistryError_DisabilityEndDate, {$this->dateTimeForm120}) as \"IPRARegistryError_DisabilityEndDate\",
                IRE.IPRARegistryError_DisabilityGroup as \"IPRARegistryError_DisabilityGroup\",
                IRE.IPRARegistryError_DisabilityCause as \"IPRARegistryError_DisabilityCause\",
                IRE.IPRARegistryError_DisabilityCauseOther as \"IPRARegistryError_DisabilityCauseOther\",
                IRE.IPRARegistryError_RehabPotential as \"IPRARegistryError_RehabPotential\",
                IRE.IPRARegistryError_RehabPrognoz as \"IPRARegistryError_RehabPrognoz\",
                IRE.IPRARegistryError_PrognozResult_SelfService as \"IPRARegistryError_PrognozResult_SelfService\",
                IRE.IPRARegistryError_PrognozResult_Independently as \"IPRARegistryError_PrognozResult_Independently\",
                IRE.IPRARegistryError_PrognozResult_Orientate as \"IPRARegistryError_PrognozResult_Orientate\",
                IRE.IPRARegistryError_PrognozResult_Communicate as \"IPRARegistryError_PrognozResult_Communicate\",
                IRE.IPRARegistryError_PrognozResult_BehaviorControl as \"IPRARegistryError_PrognozResult_BehaviorControl\",
                IRE.IPRARegistryError_PrognozResult_Learning as \"IPRARegistryError_PrognozResult_Learning\",
                IRE.IPRARegistryError_PrognozResult_Work as \"IPRARegistryError_PrognozResult_Work\",
                IRE.IPRARegistryError_RepPerson_LastName as \"IPRARegistryError_RepPerson_LastName\",
                IRE.IPRARegistryError_RepPerson_FirstName as \"IPRARegistryError_RepPerson_FirstName\",
                IRE.IPRARegistryError_RepPerson_SecondName as \"IPRARegistryError_RepPerson_SecondName\",
                case
                    when length(IRE.IPRARegistryError_RepPerson_SNILS) = 11
                        then
                            left(IRE.IPRARegistryError_RepPerson_SNILS, 3) || '-' || substring(IRE.IPRARegistryError_RepPerson_SNILS, 4, 3) || '-' || 
                            substring(IRE.IPRARegistryError_RepPerson_SNILS, 7, 3) || ' ' || right(IRE.IPRARegistryError_RepPerson_SNILS, 2)
                    else
                        IRE.IPRARegistryError_RepPerson_SNILS
                end as \"IPRARegistryError_RepPerson_SNILS\",
                IRE.IPRARegistryError_RepPerson_AuthorityDocType as \"IPRARegistryError_RepPerson_AuthorityDocType\",
                IRE.IPRARegistryError_RepPerson_AuthorityDocNum as \"IPRARegistryError_RepPerson_AuthorityDocNum\",
                IRE.IPRARegistryError_RepPerson_AuthorityDocSeries as \"IPRARegistryError_RepPerson_AuthorityDocSeries\",
                to_char(IRE.IPRARegistryError_RepPerson_AuthorityDocDate, {$this->dateTimeForm120}) as \"IPRARegistryError_RepPerson_AuthorityDocDate\",
                IRE.IPRARegistryError_RepPerson_AuthorityDocDep as \"IPRARegistryError_RepPerson_AuthorityDocDep\",
                IRE.IPRARegistryError_RepPerson_IdentifyDocType as \"IPRARegistryError_RepPerson_IdentifyDocType\",
                IRE.IPRARegistryError_RepPerson_IdentifyDocNum as \"IPRARegistryError_RepPerson_IdentifyDocNum\",
                IRE.IPRARegistryError_RepPerson_IdentifyDocSeries as \"IPRARegistryError_RepPerson_IdentifyDocSeries\",
                to_char(IRE.IPRARegistryError_RepPerson_IdentifyDocDate, {$this->dateTimeForm120}) as \"IPRARegistryError_RepPerson_IdentifyDocDate\",
                IRE.IPRARegistryError_RepPerson_IdentifyDocDep as \"IPRARegistryError_RepPerson_IdentifyDocDep\",
                IR.IPRARegistry_Version as \"IPRARegistry_Version\"
                -- end select
            from 
				-- from            
				{$this->scheme}.v_IPRARegistry IR
				inner join {$this->scheme}.v_IPRARegistryError IRE on IR.IPRARegistry_id = IRE.IPRARegistry_id
				left join v_Lpu L on l.Lpu_id = IR.Lpu_id
				left join v_PersonState PS on IR.Person_id = PS.Person_id
				left join lateral
				(
					select
                        PC.Lpu_id as Lpu_id
					from
					    dbo.v_PersonCard PC
                        inner join dbo.Lpu on Lpu.Lpu_id = PC.Lpu_id 
                        inner join dbo.Org on Org.Org_id = Lpu.Org_id
					where
					    PC.Person_id = IR.Person_id
					and
					    PC.LpuAttachType_id = 1
					limit 1 
				) as Lpu on true   
				left join lateral(
					select
						LpuBuilding_Name 
					from
					    v_LpuBuilding 
					where
					    Lpu_id = 13026012                
					and
					    LpuBuilding_Code::bigint = IPRARegistry_FGUMCEnumber
					limit 1 
				) IPRARegistry_FGUMCE on true
            	-- end from
            order by 
				-- order by
				{$params['alias']}.{$params['sort']} {$params['dir']}
				-- end order by
        ";

        $data['start'] = isset($data['start']) ? $data['start'] : 0;

        $result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
        $result_count = $this->db->query(getCountSQLPH($query), $params);

        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        } else {
            $count = 0;
        }

        if (!is_object($result)) {
            return false;
        }

        $response = [];
        $response['data'] = $result->result('array');
        $response['totalCount'] = $count;
        return $response;
    }

    /**
     *  Получение списка бюро, которые проводят МСЭ
     * @param $data
     * @return bool
     */
    function getAllBureau($data)
    {
        if ($this->getRegionNick() == 'ufa') {
            $like = '%ГБ МСЭ по РБ%';
        } else {
            $like = '%ГБ МСЭ%';
        }
        $params = [];
        $query = "
            select 
    			Lpu_id as \"Lpu_id\",
    			LpuBuilding_id as \"LpuBuilding_id\",
    			LpuBuilding_Name as \"LpuBuilding_Name\",
                LpuBuilding_Code as \"LpuBuilding_Code\",
    			LpuBuildingType_id as \"LpuBuildingType_id\"
			from
			    v_LpuBuilding LpuBuilding
			where
			    Lpu_id in (select Lpu_id from v_Lpu where Lpu_Nick ilike '{$like}') 
			order by right(LpuBuilding_Name,2)        
        ";

        //Для рабочего сервера 150076
        $result = $this->db->query($query, $params);

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     *  Сохранение регистра ИПРА (полное)
     * @param $data
     * @return array|bool
     */
    function IPRARegistry_ins($data)
    {
        $query = "
			select
				IPRARegistry_id as \"IPRARegistry_id\",
				case when Err.IPRARegistryError_id is null then 0 else 1 end as \"isErrorForEdit\"
			from 
				{$this->scheme}.v_IPRARegistry IR
				left join lateral (
					select
					    IPRARegistryError_id 
					from
					    {$this->scheme}.v_IPRARegistryError
					where
					    IPRARegistry_id = IR.IPRARegistry_id
					order by
					    IPRARegistryError_updDT
					limit 1
				) Err on true
			where
				IR.Person_id = :Person_id
            and
                IR.IPRARegistry_Number = :IPRARegistry_Number
            and
                IR.IPRARegistry_issueDate = :IPRARegistry_issueDate
            limit 1
    	";
        //echo getDebugSQL($query, $data);exit;
        $resp = $this->getFirstRowFromQuery($query, $data, true);
        if ($resp === false) {
            return false;
        }
        if (!empty($resp)) {
            if ($resp['isErrorForEdit']) {
                $data['IPRARegistryEditError_id'] = $resp['IPRARegistry_id'];
            } else {
                return [[
                    'Error_Code' => 309,
                    'Error_Message' => 'Уже существует запись в регистре ИПРА'
                ]];
            }
        }
        $procedure_name = $this->scheme . '.p_IPRARegistry_ins';
        $params = [
            'IPRARegistryEditError_id' => $data['IPRARegistryEditError_id'] ?? null,
            'IPRARegistry_IPRAident' => $data['IPRARegistry_IPRAident'] ?? null,
            'IPRARegistry_Number' => $data['IPRARegistry_Number'],
            'IPRARegistry_issueDate' => $data['IPRARegistry_issueDate'],
            'IPRARegistry_EndDate' => $data['IPRARegistry_EndDate'],
            'IPRARegistry_FGUMCEnumber' => (int)$data['IPRARegistry_FGUMCEnumber'],
            'IPRARegistry_RecepientType' => $data['IPRARegistry_RecepientType'] ?? null,
            'IPRARegistry_Protocol' => $data['IPRARegistry_Protocol'],
            'IPRARegistry_ProtocolDate' => $data['IPRARegistry_ProtocolDate'],
            'IPRARegistry_DevelopDate' => $data['IPRARegistry_DevelopDate'],
            'IPRARegistry_isFirst' => (int)$data['IPRARegistry_isFirst'],
            'IPRARegistry_Confirm' => $data['IPRARegistry_Confirm'],
            'IPRARegistry_DirectionLPU_id' => $data['IPRARegistry_DirectionLPU_id'],
            'IPRARegistry_FileName' => $data['IPRARegistry_FileName'],
            //хранимка ловит NULL и EMPTY
            'Lpu_id' => (int)$data['Lpu_id'] == 0 ? null : (int)$data['Lpu_id'],
            'Person_id' => (int)$data['Person_id'] == 0 ? null : (int)$data['Person_id'],
            'IPRARegistryData_SelfService' => !empty($data['IPRARegistryData_SelfService']) ? $data['IPRARegistryData_SelfService'] : 0,
            'IPRARegistryData_Move' => !empty($data['IPRARegistryData_Move']) ? $data['IPRARegistryData_Move'] : 0,
            'IPRARegistryData_Orientation' => !empty($data['IPRARegistryData_Orientation']) ? $data['IPRARegistryData_Orientation'] : 0,
            'IPRARegistryData_Communicate' => !empty($data['IPRARegistryData_Communicate']) ? $data['IPRARegistryData_Communicate'] : 0,
            'IPRARegistryData_Learn' => !empty($data['IPRARegistryData_Learn']) ? $data['IPRARegistryData_Learn'] : 0,
            'IPRARegistryData_Work' => !empty($data['IPRARegistryData_Work']) ? $data['IPRARegistryData_Work'] : 0,
            'IPRARegistryData_Behavior' => !empty($data['IPRARegistryData_Behavior']) ? $data['IPRARegistryData_Behavior'] : 0,
            'IPRARegistryData_MedRehab' => $data['IPRARegistryData_MedRehab'],
            'IPRARegistryData_MedRehab_begDate' => $data['IPRARegistryData_MedRehab_begDate'],
            'IPRARegistryData_MedRehab_endDate' => $data['IPRARegistryData_MedRehab_endDate'],
            'IPRARegistryData_Orthotics' => (int)$data['IPRARegistryData_Orthotics'],
            'IPRARegistryData_Orthotics_begDate' => $data['IPRARegistryData_Orthotics_begDate'],
            'IPRARegistryData_Orthotics_endDate' => $data['IPRARegistryData_Orthotics_endDate'],
            'IPRARegistryData_ReconstructSurg' => (int)$data['IPRARegistryData_ReconstructSurg'],
            'IPRARegistryData_ReconstructSurg_begDate' => $data['IPRARegistryData_ReconstructSurg_begDate'],
            'IPRARegistryData_ReconstructSurg_endDate' => $data['IPRARegistryData_ReconstructSurg_endDate'],
            'IPRARegistryData_Restoration' => $data['IPRARegistryData_Restoration'],
            'IPRARegistryData_Compensate' => $data['IPRARegistryData_Compensate'],
            'IPRARegistryError_SurName' => !empty($data['IPRARegistryError_SurName']) ? $data['IPRARegistryError_SurName'] : null,
            'IPRARegistryError_FirName' => !empty($data['IPRARegistryError_FirName']) ? $data['IPRARegistryError_FirName'] : null,
            'IPRARegistryError_SecName' => !empty($data['IPRARegistryError_SecName']) ? $data['IPRARegistryError_SecName'] : null,
            'IPRARegistryError_BirthDay' => !empty($data['IPRARegistryError_BirthDay']) ? $data['IPRARegistryError_BirthDay'] : null,
            'pmUser_id' => $data['pmUser_id'],
            'IPRAData_isValid' => (int)$data['IPRAData_isValid'],
            'IPRARegistry_Version' => !empty($data['IPRARegistry_Version']) ? $data['IPRARegistry_Version'] : null
        ];
        $query_newParams = '';

        if (isset($data['IPRARegistry_Version']) && (float)$data['IPRARegistry_Version'] >= 2.0) {
            $procedure_name = $this->scheme . '.p_IPRARegistry_NewFormat_ins';
            $newParams = [
                'IPRARegistryData_PrimaryProfession' => !empty($data['IPRARegistryData_PrimaryProfession']) ? $data['IPRARegistryData_PrimaryProfession'] : null,
                'IPRARegistryData_PrimaryProfessionExperience' => !empty($data['IPRARegistryData_PrimaryProfessionExperience']) ? $data['IPRARegistryData_PrimaryProfessionExperience'] : null,
                'IPRARegistryData_Qualification' => !empty($data['IPRARegistryData_Qualification']) ? $data['IPRARegistryData_Qualification'] : null,
                'IPRARegistryData_CurrentJob' => !empty($data['IPRARegistryData_CurrentJob']) ? $data['IPRARegistryData_CurrentJob'] : null,
                'IPRARegistryData_NotWorkYears' => !empty($data['IPRARegistryData_NotWorkYears']) ? $data['IPRARegistryData_NotWorkYears'] : null,
                'IPRARegistryData_ExistEmploymentOrientation' => !empty($data['IPRARegistryData_EmploymentOrientationExists']) ? $data['IPRARegistryData_EmploymentOrientationExists'] : null,
                'IPRARegistryData_IsRegisteredInEmploymentService' => !empty($data['IPRARegistryData_IsRegisteredInEmploymentService']) ? $data['IPRARegistryData_IsRegisteredInEmploymentService'] : null,
                'IPRARegistryData_IsDisabilityGroupPrimary' => !empty($data['IPRARegistryData_IsDisabilityGroupPrimary']) ? $data['IPRARegistryData_IsDisabilityGroupPrimary'] : null,
                'IPRARegistryData_IsIntramural' => !empty($data['IPRARegistryData_IsIntramural']) ? $data['IPRARegistryData_IsIntramural'] : null,
                'IPRARegistryData_DisabilityGroupDate' => !empty($data['IPRARegistryData_DisabilityGroupDate']) ? $data['IPRARegistryData_DisabilityGroupDate'] : null,
                'IPRARegistryData_DisabilityEndDate' => !empty($data['IPRARegistryData_DisabilityEndDate']) ? $data['IPRARegistryData_DisabilityEndDate'] : null,
                'IPRARegistryData_DisabilityGroup' => !empty($data['IPRARegistryData_DisabilityGroup']) ? $data['IPRARegistryData_DisabilityGroup'] : null,
                'IPRARegistryData_DisabilityCause' => !empty($data['IPRARegistryData_DisabilityCause']) ? $data['IPRARegistryData_DisabilityCause'] : null,
                'IPRARegistryData_RehabPotential' => !empty($data['IPRARegistryData_RehabPotential']) ? $data['IPRARegistryData_RehabPotential'] : null,
                'IPRARegistryData_RehabPrognoz' => !empty($data['IPRARegistryData_RehabPrognoz']) ? $data['IPRARegistryData_RehabPrognoz'] : null,
                'IPRARegistryData_PrognozResult_SelfService' => !empty($data['IPRARegistryData_PrognozSelfService']) ? $data['IPRARegistryData_PrognozSelfService'] : null,
                'IPRARegistryData_PrognozResult_Independetly' => !empty($data['IPRARegistryData_PrognozMoveIndependetly']) ? $data['IPRARegistryData_PrognozMoveIndependetly'] : null,
                'IPRARegistryData_PrognozResult_Orientate' => !empty($data['IPRARegistryData_PrognozOrientate']) ? $data['IPRARegistryData_PrognozOrientate'] : null,
                'IPRARegistryData_PrognozResult_Communicate' => !empty($data['IPRARegistryData_PrognozCommunicate']) ? $data['IPRARegistryData_PrognozCommunicate'] : null,
                'IPRARegistryData_PrognozResult_BehaviorControl' => !empty($data['IPRARegistryData_PrognozBehaviorControl']) ? $data['IPRARegistryData_PrognozBehaviorControl'] : null,
                'IPRARegistryData_PrognozResult_Learning' => !empty($data['IPRARegistryData_PrognozLearning']) ? $data['IPRARegistryData_PrognozLearning'] : null,
                'IPRARegistryData_PrognozResult_Work' => !empty($data['IPRARegistryData_PrognozWork']) ? $data['IPRARegistryData_PrognozWork'] : null,
                'IPRARegistryData_RepPerson_LastName' => !empty($data['IPRARegistryData_RepPerson_LastName']) ? $data['IPRARegistryData_RepPerson_LastName'] : null,
                'IPRARegistryData_RepPerson_FirstName' => !empty($data['IPRARegistryData_RepPerson_FirstName']) ? $data['IPRARegistryData_RepPerson_FirstName'] : null,
                'IPRARegistryData_RepPerson_SecondName' => !empty($data['IPRARegistryData_RepPerson_SecondName']) ? $data['IPRARegistryData_RepPerson_SecondName'] : null,
                'IPRARegistryData_RepPerson_AuthorityDocType' => !empty($data['IPRARegistryData_RepPersonAD_Title']) ? $data['IPRARegistryData_RepPersonAD_Title'] : null,
                'IPRARegistryData_RepPerson_AuthorityDocSeries' => !empty($data['IPRARegistryData_RepPersonAD_Series']) ? $data['IPRARegistryData_RepPersonAD_Series'] : null,
                'IPRARegistryData_RepPerson_AuthorityDocNum' => !empty($data['IPRARegistryData_RepPersonAD_Number']) ? $data['IPRARegistryData_RepPersonAD_Number'] : null,
                'IPRARegistryData_RepPerson_AuthorityDocDep' => !empty($data['IPRARegistryData_RepPersonAD_Issuer']) ? $data['IPRARegistryData_RepPersonAD_Issuer'] : null,
                'IPRARegistryData_RepPerson_AuthorityDocDate' => !empty($data['IPRARegistryData_RepPersonAD_IssueDate']) ? $data['IPRARegistryData_RepPersonAD_IssueDate'] : null,
                'IPRARegistryData_RepPerson_IdentifyDocType' => !empty($data['IPRARegistryData_RepPersonID_Title']) ? $data['IPRARegistryData_RepPersonID_Title'] : null,
                'IPRARegistryData_RepPerson_IdentifyDocSeries' => !empty($data['IPRARegistryData_RepPersonID_Series']) ? $data['IPRARegistryData_RepPersonID_Series'] : null,
                'IPRARegistryData_RepPerson_IdentifyDocNum' => !empty($data['IPRARegistryData_RepPersonID_Number']) ? $data['IPRARegistryData_RepPersonID_Number'] : null,
                'IPRARegistryData_RepPerson_IdentifyDocDep' => !empty($data['IPRARegistryData_RepPersonID_Issuer']) ? $data['IPRARegistryData_RepPersonID_Issuer'] : null,
                'IPRARegistryData_RepPerson_IdentifyDocDate' => !empty($data['IPRARegistryData_RepPersonID_IssueDate']) ? $data['IPRARegistryData_RepPersonID_IssueDate'] : null,
                'IPRARegistryData_RepPerson_SNILS' => !empty($data['IPRARegistryData_RepPerson_SNILS']) ? $data['IPRARegistryData_RepPerson_SNILS'] : null,
                'IPRARegistryData_DisabilityCauseOther' => !empty($data['IPRARegistryData_DisabilityCauseOther']) ? $data['IPRARegistryData_DisabilityCauseOther'] : null
            ];

            $params = $params + $newParams;

            $query_newParams = ",
                IPRARegistryData_PrimaryProfession                     := :IPRARegistryData_PrimaryProfession,
                IPRARegistryData_PrimaryProfessionExperience           := :IPRARegistryData_PrimaryProfessionExperience,
                IPRARegistryData_Qualification                         := :IPRARegistryData_Qualification,
                IPRARegistryData_CurrentJob                            := :IPRARegistryData_CurrentJob,
                IPRARegistryData_NotWorkYears                          := :IPRARegistryData_NotWorkYears,
                IPRARegistryData_ExistEmploymentOrientation            := :IPRARegistryData_ExistEmploymentOrientation,
                IPRARegistryData_IsRegInEmplService                    := :IPRARegistryData_IsRegisteredInEmploymentService,
                IPRARegistryData_IsDisabilityGroupPrimary              := :IPRARegistryData_IsDisabilityGroupPrimary,
                IPRARegistryData_IsIntramural                          := :IPRARegistryData_IsIntramural,
                IPRARegistryData_DisabilityGroupDate                   := :IPRARegistryData_DisabilityGroupDate,
                IPRARegistryData_DisabilityEndDate                     := :IPRARegistryData_DisabilityEndDate,
                IPRARegistryData_DisabilityGroup                       := :IPRARegistryData_DisabilityGroup,
                IPRARegistryData_DisabilityCause                       := :IPRARegistryData_DisabilityCause,
                IPRARegistryData_RehabPotential                        := :IPRARegistryData_RehabPotential,
                IPRARegistryData_RehabPrognoz                          := :IPRARegistryData_RehabPrognoz,
                IPRARegistryData_PrognozResult_SelfService             := :IPRARegistryData_PrognozResult_SelfService,
                IPRARegistryData_PrognozResult_Independently           := :IPRARegistryData_PrognozResult_Independetly,
                IPRARegistryData_PrognozResult_Orientate               := :IPRARegistryData_PrognozResult_Orientate,
                IPRARegistryData_PrognozResult_Communicate             := :IPRARegistryData_PrognozResult_Communicate,
                IPRARegistryData_PrognozResult_BehaviorControl         := :IPRARegistryData_PrognozResult_BehaviorControl,
                IPRARegistryData_PrognozResult_Learning                := :IPRARegistryData_PrognozResult_Learning,
                IPRARegistryData_PrognozResult_Work                    := :IPRARegistryData_PrognozResult_Work,
                IPRARegistryData_RepPerson_LastName			           := :IPRARegistryData_RepPerson_LastName,
                IPRARegistryData_RepPerson_FirstName			       := :IPRARegistryData_RepPerson_FirstName,
                IPRARegistryData_RepPerson_SecondName			       := :IPRARegistryData_RepPerson_SecondName,
                IPRARegistryData_RepPerson_SNILS			           := :IPRARegistryData_RepPerson_SNILS,
                IPRARegistryData_RepPerson_IdentifyDocType		       := :IPRARegistryData_RepPerson_IdentifyDocType,
                IPRARegistryData_RepPerson_IdentifyDocDep		       := :IPRARegistryData_RepPerson_IdentifyDocDep,
                IPRARegistryData_RepPerson_IdentifyDocSeries		   := :IPRARegistryData_RepPerson_IdentifyDocSeries,
                IPRARegistryData_RepPerson_IdentifyDocNum		       := :IPRARegistryData_RepPerson_IdentifyDocNum,
                IPRARegistryData_RepPerson_IdentifyDocDate		       := :IPRARegistryData_RepPerson_IdentifyDocDate,
                IPRARegistryData_RepPerson_AuthorityDocType            := :IPRARegistryData_RepPerson_AuthorityDocType,
                IPRARegistryData_RepPerson_AuthorityDocNum             := :IPRARegistryData_RepPerson_AuthorityDocNum,
                IPRARegistryData_RepPerson_AuthorityDocSeries          := :IPRARegistryData_RepPerson_AuthorityDocSeries,
                IPRARegistryData_RepPerson_AuthorityDocDep             := :IPRARegistryData_RepPerson_AuthorityDocDep,
                IPRARegistryData_RepPerson_AuthorityDocDate            := :IPRARegistryData_RepPerson_AuthorityDocDate,
                IPRARegistryData_DisabilityCauseOther                  := :IPRARegistryData_DisabilityCauseOther
            ";
        }

        $query = "
            select
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
            from {$procedure_name}
            (
                IPRARegistryEditError_id := :IPRARegistryEditError_id,
                IPRARegistry_IPRAident := :IPRARegistry_IPRAident,
                IPRARegistry_Number := :IPRARegistry_Number,
                IPRARegistry_issueDate :=:IPRARegistry_issueDate,
                IPRARegistry_EndDate := :IPRARegistry_EndDate,
                IPRARegistry_FGUMCEnumber := :IPRARegistry_FGUMCEnumber ,
                IPRARegistry_RecepientType := :IPRARegistry_RecepientType ,
                IPRARegistry_Protocol := :IPRARegistry_Protocol,
                IPRARegistry_ProtocolDate := :IPRARegistry_ProtocolDate, 
                IPRARegistry_DevelopDate := :IPRARegistry_DevelopDate, 
                IPRARegistry_isFirst := :IPRARegistry_isFirst,
                IPRARegistry_Confirm := :IPRARegistry_Confirm,
                IPRARegistry_DirectionLPU_id := :IPRARegistry_DirectionLPU_id,
                IPRARegistry_FileName := :IPRARegistry_FileName ,
                Lpu_id := :Lpu_id,
                Person_id := :Person_id,
                IPRARegistryData_SelfService := :IPRARegistryData_SelfService,
                IPRARegistryData_Move := :IPRARegistryData_Move,
                IPRARegistryData_Orientation := :IPRARegistryData_Orientation ,
                IPRARegistryData_Communicate := :IPRARegistryData_Communicate ,
                IPRARegistryData_Learn := :IPRARegistryData_Learn ,
                IPRARegistryData_Work := :IPRARegistryData_Work ,
                IPRARegistryData_Behavior := :IPRARegistryData_Behavior ,
                IPRARegistryData_MedRehab := :IPRARegistryData_MedRehab ,
                IPRARegistryData_MedRehab_begDate := :IPRARegistryData_MedRehab_begDate ,
                IPRARegistryData_MedRehab_endDate := :IPRARegistryData_MedRehab_endDate ,
                IPRARegistryData_Orthotics := :IPRARegistryData_Orthotics ,
                IPRARegistryData_Orthotics_begDate := :IPRARegistryData_Orthotics_begDate ,
                IPRARegistryData_Orthotics_endDate := :IPRARegistryData_Orthotics_endDate ,
                IPRARegistryData_ReconstructSurg := :IPRARegistryData_ReconstructSurg ,
                IPRARegistryData_ReconstructSurg_begDate := :IPRARegistryData_ReconstructSurg_begDate ,
                IPRARegistryData_ReconstructSurg_endDate := :IPRARegistryData_ReconstructSurg_endDate ,
                IPRARegistryData_Restoration := :IPRARegistryData_Restoration ,
                IPRARegistryData_Compensate := :IPRARegistryData_Compensate ,
                IPRARegistryError_SurName := :IPRARegistryError_SurName ,
                IPRARegistryError_FirName := :IPRARegistryError_FirName ,
                IPRARegistryError_SecName := :IPRARegistryError_SecName ,
                IPRARegistryError_BirthDay := :IPRARegistryError_BirthDay ,
                pmUser_id := :pmUser_id ,
                IPRAData_isValid := :IPRAData_isValid,
                IPRARegistry_Version := :IPRARegistry_Version
                $query_newParams
            )
	    ";
        sql_log_message('info', 'IPRARegistry_ins exec query: ', getDebugSql($query, $params));
        //exit;

        $result = $this->db->query($query, $params);

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     *  Получение списка номеров ИПРА по пациенту
     * @param $data
     * @return bool
     */
    public function getAllIPRARegistry($data)
    {
        $params = [
            'Person_id' => $data['Person_id']
        ];

        $query = "
			select
			    r.IPRARegistry_id as \"IPRARegistry_id\",
			    r.IPRARegistry_Number as \"IPRARegistry_Number\" 
			from
			    {$this->scheme}.v_IPRARegistry r
			    inner join {$this->scheme}.v_IPRARegistryData rd ON rd.IPRARegistry_id = r.IPRARegistry_id
			where
			    r.Person_id = :Person_id 
			order by r.IPRARegistry_issueDate DESC
        ";

        $result = $this->db->query($query, $params);


        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     *  Получение выписки ИПРА по пациенту
     * @param $data
     * @return bool
     */
    public function getIPRARegistry($data)
    {

        $params = [
            'IPRARegistry_id' => $data['IPRARegistry_id']
        ];

        $query = "
            select 
                IR.IPRARegistry_id as \"IPRARegistry_id\",
                IR.IPRARegistry_IPRAident as \"IPRARegistry_IPRAident\",
                IR.IPRARegistry_Number as \"IPRARegistry_Number\",
                to_char(IPRARegistry_issueDate, {$this->dateTimeForm120}) as \"IPRARegistry_issueDate\",
                to_char(IPRARegistry_EndDate, {$this->dateTimeForm120}) as \"IPRARegistry_EndDate\",
                cast(IR.IPRARegistry_FGUMCEnumber as int) as \"IPRARegistry_FGUMCEnumber\",
                IR.IPRARegistry_RecepientType as \"IPRARegistry_RecepientType\",
                IR.IPRARegistry_Protocol as \"IPRARegistry_Protocol\",
                to_char(IPRARegistry_ProtocolDate, {$this->dateTimeForm120}) as \"IPRARegistry_ProtocolDate\",
                to_char(IPRARegistry_DevelopDate, {$this->dateTimeForm120}) as \"IPRARegistry_DevelopDate\",
                IR.IPRARegistry_isFirst as \"IPRARegistry_isFirst\",
                IR.IPRARegistry_Confirm as \"IPRARegistry_Confirm\",
                IR.IPRARegistry_DirectionLPU_id as \"IPRARegistry_DirectionLPU_id\",
                IR.Lpu_id as \"Lpu_id\",
                IR.Person_id as \"Person_id\",
                IR.IPRARegistry_insDT as \"IPRARegistry_insDT\",
                IR.IPRARegistry_updDT as \"IPRARegistry_updDT\",

                IRD.IPRARegistryData_id as \"IPRARegistryData_id\",
                --IRD.IPRARegistry_id,
                coalesce(IRD.IPRARegistryData_SelfService, 0) as \"IPRARegistryData_SelfService\",
                coalesce(IRD.IPRARegistryData_Move, 0) as \"IPRARegistryData_Move\",
                coalesce(IRD.IPRARegistryData_Orientation, 0) as \"IPRARegistryData_Orientation\",
                coalesce(IRD.IPRARegistryData_Communicate, 0) as \"IPRARegistryData_Communicate\",
                coalesce(IRD.IPRARegistryData_Learn, 0) as \"IPRARegistryData_Learn\",
                coalesce(IRD.IPRARegistryData_Work, 0) as \"IPRARegistryData_Work\",
                coalesce(IRD.IPRARegistryData_Behavior, 0) as \"IPRARegistryData_Behavior\",
                IRD.IPRARegistryData_MedRehab as \"IPRARegistryData_MedRehab\",
                to_char(IRD.IPRARegistryData_MedRehab_begDate, {$this->dateTimeForm120}) as \"IPRARegistryData_MedRehab_begDate\",
                to_char(IRD.IPRARegistryData_MedRehab_endDate, {$this->dateTimeForm120}) as \"IPRARegistryData_MedRehab_endDate\",
                IRD.IPRARegistryData_Orthotics as \"IPRARegistryData_Orthotics\",
                to_char(IRD.IPRARegistryData_Orthotics_begDate, {$this->dateTimeForm120}) as \"IPRARegistryData_Orthotics_begDate\",
                to_char(IRD.IPRARegistryData_Orthotics_endDate, {$this->dateTimeForm120}) as \"IPRARegistryData_Orthotics_endDate\",
                IRD.IPRARegistryData_ReconstructSurg as \"IPRARegistryData_ReconstructSurg\",
                to_char(IPRARegistryData_ReconstructSurg_begDate, {$this->dateTimeForm120}) as \"IPRARegistryData_ReconstructSurg_begDate\",
                to_char(IPRARegistryData_ReconstructSurg_endDate, {$this->dateTimeForm120}) as \"IPRARegistryData_ReconstructSurg_endDate\",
                IRD.IPRARegistryData_Restoration as \"IPRARegistryData_Restoration\",
                IRD.IPRARegistryData_Compensate as \"IPRARegistryData_Compensate\",
                IRD.IPRARegistryData_insDT as \"IPRARegistryData_insDT\",
                IRD.IPRARegistryData_updDT as \"IPRARegistryData_updDT\",
                IRD.IPRARegistryData_PrimaryProfession as \"IPRARegistryData_PrimaryProfession\",
                IRD.IPRARegistryData_PrimaryProfessionExperience as \"IPRARegistryData_PrimaryProfessionExperience\",	
                IRD.IPRARegistryData_Qualification as \"IPRARegistryData_Qualification\",   
                IRD.IPRARegistryData_CurrentJob as \"IPRARegistryData_CurrentJob\",
                IRD.IPRARegistryData_NotWorkYears as \"IPRARegistryData_NotWorkYears\",
                IRD.IPRARegistryData_ExistEmploymentOrientation as \"IPRARegistryData_ExistEmploymentOrientation\",
                IRD.IPRARegistryData_isRegInEmplService as \"IPRARegistryData_isRegInEmplService\",
                IRD.IPRARegistryData_IsDisabilityGroupPrimary as \"IPRARegistryData_IsDisabilityGroupPrimary\",
                IRD.IPRARegistryData_IsIntramural as \"IPRARegistryData_IsIntramural\",
                to_char(IRD.IPRARegistryData_DisabilityGroupDate, {$this->dateTimeForm120}) as \"IPRARegistryData_DisabilityGroupDate\", 
                to_char(IRD.IPRARegistryData_DisabilityEndDate, {$this->dateTimeForm120}) as \"IPRARegistryData_DisabilityEndDate\",
                IRD.IPRARegistryData_DisabilityGroup as \"IPRARegistryData_DisabilityGroup\",
                IRD.IPRARegistryData_DisabilityCause as \"IPRARegistryData_DisabilityCause\",
                IRD.IPRARegistryData_DisabilityCauseOther as \"IPRARegistryData_DisabilityCauseOther\",
                IRD.IPRARegistryData_RehabPotential as \"IPRARegistryData_RehabPotential\",
                IRD.IPRARegistryData_RehabPrognoz as \"IPRARegistryData_RehabPrognoz\",
                IRD.IPRARegistryData_PrognozResult_SelfService as \"IPRARegistryData_PrognozResult_SelfService\",
                IRD.IPRARegistryData_PrognozResult_Independently as \"IPRARegistryData_PrognozResult_Independently\",
                IRD.IPRARegistryData_PrognozResult_Orientate as \"IPRARegistryData_PrognozResult_Orientate\",
                IRD.IPRARegistryData_PrognozResult_Communicate as \"IPRARegistryData_PrognozResult_Communicate\",
                IRD.IPRARegistryData_PrognozResult_BehaviorControl as \"IPRARegistryData_PrognozResult_BehaviorControl\",
                IRD.IPRARegistryData_PrognozResult_Learning as \"IPRARegistryData_PrognozResult_Learning\",
                IRD.IPRARegistryData_PrognozResult_Work as \"IPRARegistryData_PrognozResult_Work\",
                IRD.IPRARegistryData_RepPerson_LastName as \"IPRARegistryData_RepPerson_LastName\",
                IRD.IPRARegistryData_RepPerson_FirstName as \"IPRARegistryData_RepPerson_FirstName\",
                IRD.IPRARegistryData_RepPerson_SecondName as \"IPRARegistryData_RepPerson_SecondName\",
                case
                    when length(IRD.IPRARegistryData_RepPerson_SNILS) = 11
                        then
                            left(IRD.IPRARegistryData_RepPerson_SNILS, 3) || '-' || substring(IRD.IPRARegistryData_RepPerson_SNILS, 4, 3) || '-' || 
                            substring(IRD.IPRARegistryData_RepPerson_SNILS, 7, 3) || ' ' || right(IRD.IPRARegistryData_RepPerson_SNILS, 2)
                        else
                            IRD.IPRARegistryData_RepPerson_SNILS
                end as \"IPRARegistryData_RepPerson_SNILS\",
                IRD.IPRARegistryData_RepPerson_AuthorityDocType as \"IPRARegistryData_RepPerson_AuthorityDocType\",
                IRD.IPRARegistryData_RepPerson_AuthorityDocNum as \"IPRARegistryData_RepPerson_AuthorityDocNum\",
		        IRD.IPRARegistryData_RepPerson_AuthorityDocSeries as \"IPRARegistryData_RepPerson_AuthorityDocSeries\",
		        to_char(IRD.IPRARegistryData_RepPerson_AuthorityDocDate, {$this->dateTimeForm120}) as \"IPRARegistryData_RepPerson_AuthorityDocDate\",
		        IRD.IPRARegistryData_RepPerson_AuthorityDocDep as \"IPRARegistryData_RepPerson_AuthorityDocDep\",
                IRD.IPRARegistryData_RepPerson_IdentifyDocType as \"IPRARegistryData_RepPerson_IdentifyDocType\",
                IRD.IPRARegistryData_RepPerson_IdentifyDocNum as \"IPRARegistryData_RepPerson_IdentifyDocNum\",
		        IRD.IPRARegistryData_RepPerson_IdentifyDocSeries as \"IPRARegistryData_RepPerson_IdentifyDocSeries\",
		        to_char(IRD.IPRARegistryData_RepPerson_IdentifyDocDate, {$this->dateTimeForm120}) as \"IPRARegistryData_RepPerson_IdentifyDocDate\",
		        IRD.IPRARegistryData_RepPerson_IdentifyDocDep as \"IPRARegistryData_RepPerson_IdentifyDocDep\",
                IR.IPRARegistry_Version as \"IPRARegistry_Version\"
            from
                {$this->scheme}.v_IPRARegistry IR
                left join {$this->scheme}.v_IPRARegistryData IRD on IR.IPRARegistry_id = IRD.IPRARegistry_id
            where
                IR.IPRARegistry_id = :IPRARegistry_id
        ";

        $result = $this->db->query($query, $params);

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     *  Редактирование регистра ИПРА
     * @param $data
     * @return bool
     */
    public function IPRARegistry_upd($data)
    {
        $params = [
            'IPRARegistry_id' => $data['IPRARegistry_id'],
            'IPRARegistry_IPRAident' => $data['IPRARegistry_IPRAident'],
            'IPRARegistry_Number' => $data['IPRARegistry_Number'],
            'IPRARegistry_issueDate' => $data['IPRARegistry_issueDate'],
            'IPRARegistry_EndDate' => $data['IPRARegistry_EndDate'],
            'IPRARegistry_FGUMCEnumber' => $data['IPRARegistry_FGUMCEnumber'],
            'IPRARegistry_RecepientType' => $data['IPRARegistry_RecepientType'],
            'IPRARegistry_Protocol' => $data['IPRARegistry_Protocol'],
            'IPRARegistry_ProtocolDate' => $data['IPRARegistry_ProtocolDate'],
            'IPRARegistry_DevelopDate' => $data['IPRARegistry_DevelopDate'],
            'IPRARegistry_isFirst' => $data['IPRARegistry_isFirst'],
            'IPRARegistry_Confirm' => $data['IPRARegistry_Confirm'],
            'IPRARegistry_DirectionLPU_id' => $data['IPRARegistry_DirectionLPU_id'],
            'IPRARegistry_FileName' => $data['IPRARegistry_FileName'],
            'Lpu_id' => $data['Lpu_id'],
            'Person_id' => $data['Person_id'],
            'IPRARegistryData_SelfService' => !empty($data['IPRARegistryData_SelfService']) ? $data['IPRARegistryData_SelfService'] : 0,
            'IPRARegistryData_Move' => !empty($data['IPRARegistryData_Move']) ? $data['IPRARegistryData_Move'] : 0,
            'IPRARegistryData_Orientation' => !empty($data['IPRARegistryData_Orientation']) ? $data['IPRARegistryData_Orientation'] : 0,
            'IPRARegistryData_Communicate' => !empty($data['IPRARegistryData_Communicate']) ? $data['IPRARegistryData_Communicate'] : 0,
            'IPRARegistryData_Learn' => !empty($data['IPRARegistryData_Learn']) ? $data['IPRARegistryData_Learn'] : 0,
            'IPRARegistryData_Work' => !empty($data['IPRARegistryData_Work']) ? $data['IPRARegistryData_Work'] : 0,
            'IPRARegistryData_Behavior' => !empty($data['IPRARegistryData_Behavior']) ? $data['IPRARegistryData_Behavior'] : 0,
            'IPRARegistryData_MedRehab' => $data['IPRARegistryData_MedRehab'],
            'IPRARegistryData_MedRehab_begDate' => $data['IPRARegistryData_MedRehab_begDate'],
            'IPRARegistryData_MedRehab_endDate' => $data['IPRARegistryData_MedRehab_endDate'],
            'IPRARegistryData_Orthotics' => $data['IPRARegistryData_Orthotics'],
            'IPRARegistryData_Orthotics_begDate' => $data['IPRARegistryData_Orthotics_begDate'],
            'IPRARegistryData_Orthotics_endDate' => $data['IPRARegistryData_Orthotics_endDate'],
            'IPRARegistryData_ReconstructSurg' => $data['IPRARegistryData_ReconstructSurg'],
            'IPRARegistryData_ReconstructSurg_begDate' => $data['IPRARegistryData_ReconstructSurg_begDate'],
            'IPRARegistryData_ReconstructSurg_endDate' => $data['IPRARegistryData_ReconstructSurg_endDate'],
            'IPRARegistryData_Restoration' => $data['IPRARegistryData_Restoration'],
            'IPRARegistryData_Compensate' => $data['IPRARegistryData_Compensate'],
            'pmUser_id' => $data['pmUser_id'],
            'IPRARegistryData_PrimaryProfession' => $data['IPRARegistryData_PrimaryProfession'] ?? null,
            'IPRARegistryData_PrimaryProfessionExperience' => $data['IPRARegistryData_PrimaryProfessionExperience'] ?? null,
            'IPRARegistryData_Qualification' => $data['IPRARegistryData_Qualification'] ?? null,
            'IPRARegistryData_CurrentJob' => $data['IPRARegistryData_CurrentJob'] ?? null,
            'IPRARegistryData_NotWorkYears' => $data['IPRARegistryData_NotWorkYears'] ?? null,
            'IPRARegistryData_ExistEmploymentOrientation' => $data['IPRARegistryData_ExistEmploymentOrientation'] ?? null,
            'IPRARegistryData_isRegisteredInEmploymentService' => $data['IPRARegistryData_isRegInEmplService'] ?? null,
            'IPRARegistryData_IsDisabilityGroupPrimary' => $data['IPRARegistryData_IsDisabilityGroupPrimary'] ?? null,
            'IPRARegistryData_IsIntramural' => $data['IPRARegistryData_IsIntramural'] ?? null,
            'IPRARegistryData_DisabilityGroupDate' => $data['IPRARegistryData_DisabilityGroupDate'] ?? null,
            'IPRARegistryData_DisabilityEndDate' => $data['IPRARegistryData_DisabilityEndDate'] ?? null,
            'IPRARegistryData_DisabilityGroup' => $data['IPRARegistryData_DisabilityGroup'] ?? null,
            'IPRARegistryData_DisabilityCause' => $data['IPRARegistryData_DisabilityCause'] ?? null,
            'IPRARegistryData_RehabPotential' => $data['IPRARegistryData_RehabPotential'] ?? null,
            'IPRARegistryData_RehabPrognoz' => $data['IPRARegistryData_RehabPrognoz'] ?? null,
            'IPRARegistryData_PrognozResult_SelfService' => $data['IPRARegistryData_PrognozResult_SelfService'] ?? null,
            'IPRARegistryData_PrognozResult_Orientate' => $data['IPRARegistryData_PrognozResult_Orientate'] ?? null,
            'IPRARegistryData_PrognozResult_Communicate' => $data['IPRARegistryData_PrognozResult_Communicate'] ?? null,
            'IPRARegistryData_PrognozResult_BehaviorControl' => $data['IPRARegistryData_PrognozResult_BehaviorControl'] ?? null,
            'IPRARegistryData_PrognozResult_Learning' => $data['IPRARegistryData_PrognozResult_Learning'] ?? null,
            'IPRARegistryData_PrognozResult_Work' => $data['IPRARegistryData_PrognozResult_Work'] ?? null,
            'IPRARegistryData_RepPerson_LastName' => $data['IPRARegistryData_RepPerson_LastName'] ?? null,
            'IPRARegistryData_RepPerson_FirstName' => $data['IPRARegistryData_RepPerson_FirstName'] ?? null,
            'IPRARegistryData_RepPerson_SecondName' => $data['IPRARegistryData_RepPerson_SecondName'] ?? null,
            'IPRARegistryData_RepPerson_SNILS' => $data['IPRARegistryData_RepPerson_SNILS'] ?? null,
            'IPRARegistryData_RepPerson_IdentifyDocType' => $data['IPRARegistryData_RepPerson_IdentifyDocType'] ?? null,
            'IPRARegistryData_RepPerson_IdentifyDocDep' => $data['IPRARegistryData_RepPerson_IdentifyDocDep'] ?? null,
            'IPRARegistryData_RepPerson_IdentifyDocSeries' => $data['IPRARegistryData_RepPerson_IdentifyDocSeries'] ?? null,
            'IPRARegistryData_RepPerson_IdentifyDocNum' => $data['IPRARegistryData_RepPerson_IdentifyDocNum'] ?? null,
            'IPRARegistryData_RepPerson_IdentifyDocDate' => $data['IPRARegistryData_RepPerson_IdentifyDocDate'] ?? null,
            'IPRARegistryData_RepPerson_AuthorityDocType' => $data['IPRARegistryData_RepPerson_AuthorityDocType'] ?? null,
            'IPRARegistryData_RepPerson_AuthorityDocDep' => $data['IPRARegistryData_RepPerson_AuthorityDocDep'] ?? null,
            'IPRARegistryData_RepPerson_AuthorityDocSeries' => $data['IPRARegistryData_RepPerson_AuthorityDocSeries'] ?? null,
            'IPRARegistryData_RepPerson_AuthorityDocNum' => $data['IPRARegistryData_RepPerson_AuthorityDocNum'] ?? null,
            'IPRARegistryData_RepPerson_AuthorityDocDate' => $data['IPRARegistryData_RepPerson_AuthorityDocDate'] ?? null,
            'IPRARegistryData_DisabilityCauseOther' => $data['IPRARegistryData_DisabilityCauseOther'] ?? null,
            'IPRARegistryData_PrognozResult_Independently' => $data['IPRARegistryData_PrognozResult_Independently'] ?? null
        ];

        $query = "
            select
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
            from {$this->scheme}.p_IPRARegistry_upd
            (
                IPRARegistry_id := :IPRARegistry_id,
                IPRARegistry_IPRAident := :IPRARegistry_IPRAident,
                IPRARegistry_Number := :IPRARegistry_Number,
                IPRARegistry_issueDate := :IPRARegistry_issueDate,
                IPRARegistry_EndDate := :IPRARegistry_EndDate,
                IPRARegistry_FGUMCEnumber := :IPRARegistry_FGUMCEnumber ,
                IPRARegistry_RecepientType := :IPRARegistry_RecepientType ,
                IPRARegistry_Protocol := :IPRARegistry_Protocol,
                IPRARegistry_ProtocolDate := :IPRARegistry_ProtocolDate, 
                IPRARegistry_DevelopDate := :IPRARegistry_DevelopDate, 
                IPRARegistry_isFirst := :IPRARegistry_isFirst,
                IPRARegistry_Confirm  := :IPRARegistry_Confirm,
                IPRARegistry_DirectionLPU_id := :IPRARegistry_DirectionLPU_id,
                IPRARegistry_FileName := :IPRARegistry_FileName,
                Lpu_id := :Lpu_id,
                Person_id := :Person_id,
                IPRARegistryData_SelfService := :IPRARegistryData_SelfService,
                IPRARegistryData_Move := :IPRARegistryData_Move,
                IPRARegistryData_Orientation := :IPRARegistryData_Orientation ,
                IPRARegistryData_Communicate := :IPRARegistryData_Communicate ,
                IPRARegistryData_Learn := :IPRARegistryData_Learn ,
                IPRARegistryData_Work := :IPRARegistryData_Work ,
                IPRARegistryData_Behavior := :IPRARegistryData_Behavior ,
                IPRARegistryData_MedRehab := :IPRARegistryData_MedRehab ,
                IPRARegistryData_MedRehab_begDate := :IPRARegistryData_MedRehab_begDate ,
                IPRARegistryData_MedRehab_endDate := :IPRARegistryData_MedRehab_endDate ,
                IPRARegistryData_Orthotics := :IPRARegistryData_Orthotics ,
                IPRARegistryData_Orthotics_begDate := :IPRARegistryData_Orthotics_begDate ,
                IPRARegistryData_Orthotics_endDate := :IPRARegistryData_Orthotics_endDate ,
                IPRARegistryData_ReconstructSurg := :IPRARegistryData_ReconstructSurg ,
                IPRARegistryData_ReconstructSurg_begDate := :IPRARegistryData_ReconstructSurg_begDate ,
                IPRARegistryData_ReconstructSurg_endDate := :IPRARegistryData_ReconstructSurg_endDate ,
                IPRARegistryData_Restoration := :IPRARegistryData_Restoration ,
                IPRARegistryData_Compensate := :IPRARegistryData_Compensate ,
                pmUser_id := :pmUser_id,
                IPRARegistryData_PrimaryProfession                     := :IPRARegistryData_PrimaryProfession,
                IPRARegistryData_PrimaryProfessionExperience           := :IPRARegistryData_PrimaryProfessionExperience,
                IPRARegistryData_Qualification                         := :IPRARegistryData_Qualification,
                IPRARegistryData_CurrentJob                            := :IPRARegistryData_CurrentJob,
                IPRARegistryData_NotWorkYears                          := :IPRARegistryData_NotWorkYears,
                IPRARegistryData_ExistEmploymentOrientation            := :IPRARegistryData_ExistEmploymentOrientation,
                IPRARegistryData_isRegInEmplService                    := :IPRARegistryData_isRegisteredInEmploymentService,
                IPRARegistryData_IsDisabilityGroupPrimary              := :IPRARegistryData_IsDisabilityGroupPrimary,
                IPRARegistryData_DisabilityGroupDate                   := :IPRARegistryData_DisabilityGroupDate,
                IPRARegistryData_DisabilityEndDate                     := :IPRARegistryData_DisabilityEndDate,
                IPRARegistryData_DisabilityGroup                       := :IPRARegistryData_DisabilityGroup,
                IPRARegistryData_DisabilityCause                       := :IPRARegistryData_DisabilityCause,
                IPRARegistryData_IsIntramural                          := :IPRARegistryData_IsIntramural,
                IPRARegistryData_RehabPotential                        := :IPRARegistryData_RehabPotential,
                IPRARegistryData_RehabPrognoz                          := :IPRARegistryData_RehabPrognoz,
                IPRARegistryData_PrognozResult_Independently           := :IPRARegistryData_PrognozResult_Independently,
                IPRARegistryData_PrognozResult_SelfService             := :IPRARegistryData_PrognozResult_SelfService,
                IPRARegistryData_PrognozResult_Orientate               := :IPRARegistryData_PrognozResult_Orientate,
                IPRARegistryData_PrognozResult_Communicate             := :IPRARegistryData_PrognozResult_Communicate,
                IPRARegistryData_PrognozResult_BehaviorControl         := :IPRARegistryData_PrognozResult_BehaviorControl,
                IPRARegistryData_PrognozResult_Learning                := :IPRARegistryData_PrognozResult_Learning,
                IPRARegistryData_PrognozResult_Work                    := :IPRARegistryData_PrognozResult_Work,
                IPRARegistryData_RepPerson_LastName                    := :IPRARegistryData_RepPerson_LastName,
                IPRARegistryData_RepPerson_FirstName                   := :IPRARegistryData_RepPerson_FirstName,
                IPRARegistryData_RepPerson_SecondName                  := :IPRARegistryData_RepPerson_SecondName,
                IPRARegistryData_RepPerson_SNILS                       := :IPRARegistryData_RepPerson_SNILS,
                IPRARegistryData_RepPerson_IdentifyDocType             := :IPRARegistryData_RepPerson_IdentifyDocType,
                IPRARegistryData_RepPerson_IdentifyDocDep              := :IPRARegistryData_RepPerson_IdentifyDocDep,
                IPRARegistryData_RepPerson_IdentifyDocSeries           := :IPRARegistryData_RepPerson_IdentifyDocSeries,
                IPRARegistryData_RepPerson_IdentifyDocNum              := :IPRARegistryData_RepPerson_IdentifyDocNum,
                IPRARegistryData_RepPerson_IdentifyDocDate             := :IPRARegistryData_RepPerson_IdentifyDocDate,
                IPRARegistryData_RepPerson_AuthorityDocType            := :IPRARegistryData_RepPerson_AuthorityDocType,
                IPRARegistryData_RepPerson_AuthorityDocDep             := :IPRARegistryData_RepPerson_AuthorityDocDep,
                IPRARegistryData_RepPerson_AuthorityDocSeries          := :IPRARegistryData_RepPerson_AuthorityDocSeries,
                IPRARegistryData_RepPerson_AuthorityDocNum             := :IPRARegistryData_RepPerson_AuthorityDocNum,
                IPRARegistryData_RepPerson_AuthorityDocDate            := :IPRARegistryData_RepPerson_AuthorityDocDate,
                IPRARegistryData_DisabilityCauseOther                  := :IPRARegistryData_DisabilityCauseOther
            )
        ";

        $result = $this->db->query($query, $params);

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     * Получение ОГРН и адреса МО
     * @param $params
     * @return bool
     */
    public function getMOAddressOgrn($params)
    {
        $query = "
            SELECT
                UAddress_Address as \"UAddress_Address\",
                Lpu_OGRN as \"Lpu_OGRN\"
            FROM
                dbo.v_Lpu
            WHERE
                Lpu_id = {$params["DirectionLpu_id"]}
        ";
        $result = $this->db->query($query);
        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     *  Проверка наличия пациента в регистре по предмету наблюдения
     * @param $params
     * @return bool
     */
    public function checkPersonInRegister($params)
    {
        $params = [
            'Person_id' => $params['Person_id'],
            'MorbusType_id' => $params['MorbusType_id']
        ];

        $query = "
			select 
				Person_id as \"Person_id\", 
				MorbusType_id as \"MorbusType_id\"
			from
			    v_PersonRegister
			where
			    Person_id = :Person_id
			and
			    MorbusType_id = :MorbusType_id 
        ";

        $result = $this->db->query($query, $params);

        if (!is_object($result)) {
            return false;
        }

        $dataInDB = $result->result('array');

        if (!empty($dataInDB)) {
            return false;
        }

        return true;
    }

    /**
     * Добавления пациента в PersonRegister
     * @param $data
     * @return bool
     */
    public function saveInPersonRegister($data)
    {

        $params = [
            'PersonRegister_id' => $data['PersonRegister_id'],
            'Person_id' => $data['Person_id'],
            'MorbusType_id' => $data['MorbusType_id'],
            'Diag_id' => $data['Diag_id'],
            'PersonRegister_Code' => $data['PersonRegister_Code'],
            'PersonRegister_setDate' => $data['PersonRegister_setDate'],
            'PersonRegister_disDate' => $data['PersonRegister_disDate'],
            'Morbus_id' => $data['Morbus_id'],
            'PersonRegisterOutCause_id' => $data['PersonRegisterOutCause_id'],
            'MedPersonal_iid' => $data['MedPersonal_iid'],
            'Lpu_iid' => $data['Lpu_iid'],
            'MedPersonal_did' => $data['MedPersonal_did'],
            'Lpu_did' => $data['Lpu_did'],
            'EvnNotifyBase_id' => $data['EvnNotifyBase_id'],
            'pmUser_id' => $data['pmUser_id']
        ];

        $query = "
            select 
                PersonRegister_id as \"PersonRegister_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Message\"
			from {$this->scheme}.p_PersonRegister_ins
			(
				PersonRegister_id := :PersonRegister_id,
				Person_id := :Person_id,
				MorbusType_id := :MorbusType_id,
				Diag_id := :Diag_id,
				PersonRegister_Code := :PersonRegister_Code,
				PersonRegister_setDate := :PersonRegister_setDate,
				PersonRegister_disDate := :PersonRegister_disDate,
				MedPersonal_iid := :MedPersonal_iid,
				Lpu_iid := :Lpu_iid,
				EvnNotifyBase_id := :EvnNotifyBase_id,
				pmUser_id := :pmUser_id,
				MedPersonal_did := :MedPersonal_did,
				Lpu_did := :Lpu_did,
				Morbus_id := :Morbus_id,
				PersonRegisterOutCause_id := :PersonRegisterOutCause_id 
			)
		";
        //echo getDebugSql($query, $params);exit;

        $result = $this->db->query($query, $params);

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     *  Актуальный метод идентификации пациента и получения МО прикрепления
     * @param $data
     * @return array|bool
     */
    function getIdentityPacient($data)
    {
        set_time_limit(0);
        $params = [
            'idx' => $data['idx'],
            'Person_FirName' => preg_replace('/[ё]/iu', 'Е', trim($data['Person_FirName'])),
            'Person_SecName' => preg_replace('/[ё]/iu', 'Е', trim($data['Person_SecName'])),
            'Person_Snils' => preg_replace("/\D+/", "", $data['Person_Snils']),
            'Document_Num' => $data['Document_Num'],
            'Person_BirthDate' => $data['Person_BirthDate'],
            'IPRAData_DirectionLPU_id' => $data['IPRAData_DirectionLPU_id'] == null ? '-' : $data['IPRAData_DirectionLPU_id'] . '%'
        ];

        $query = "
			select
				indx.idx as \"idx\", 
				pers.Person_id as \"Person_id\",
				pers.Server_pid as \"Server_pid\",
				LpuAttach.Lpu_id as \"Lpu_id\",
				LpuAttach.LpuAttachName as \"LpuAttachName\",
				LpuDirection.LpuDirection_id as \"LpuDirection_id\",
				LpuDirection.LpuDirection_Name as \"LpuDirection_Name\"
			from 
			    (select :idx as idx) indx
				left join lateral (
					select
						PS.Person_id,
						PS.Server_pid
					from
					    dbo.v_PersonState PS
					where 
						PS.Person_FirNameR = :Person_FirName
                    and
                        PS.Person_SecNameR = :Person_SecName
                    and
                        ((COALESCE(PS.Person_Snils, '') <> '' AND replace(replace(Person_Snils, '-', ''),' ','') = :Person_Snils)
                    or
                        (COALESCE(PS.Document_Num, '') <> '' AND Document_Num = :Document_Num))
				) AS pers on true
				left join lateral (
					select 
						PC.Lpu_id as \"Lpu_id\",
						Lpu.Org_Nick as \"LpuAttachName\",
						PC.PersonCard_endDate as \"PersonCard_endDate\"
					from
					    dbo.v_PersonCard_all PC
						inner join dbo.v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
						inner join dbo.Org on Org.Org_id = Lpu.Org_id
					where
					    PC.Person_id = pers.Person_id
                    and
                        PC.LpuAttachType_id = 1 
					order by pc.PersonCard_begDate desc
					limit 1
				) as LpuAttach on true
				inner join lateral (
					select
						Lpu.Lpu_id as LpuDirection_id,
						Lpu.Lpu_Nick as LpuDirection_Name
					from
					    dbo.v_Lpu Lpu
						LEFT join {$this->scheme}.IPRALpu IPRALpu on IPRALpu.Lpu_id = Lpu.Lpu_id
					where
					    IPRALpu.IPRALpu_Name ilike UPPER(:IPRAData_DirectionLPU_id)
                    OR
                        Lpu.Lpu_Nick iLIKE UPPER(:IPRAData_DirectionLPU_id)
                    OR
                        Lpu.Lpu_Name iLIKE UPPER(:IPRAData_DirectionLPU_id)
                    limit 1
				) as LpuDirection on true
		";


        if ($this->useTest === true) {
            $test = $this->load->database('bdtest', true);
            $test->query_timeout = 3600;
            $result = $test->query($query, $params);
        } else {
            $result = $this->db->query($query, $params);
        }

        $this->load->database();

        if (!is_object($result)) {
            return false;
        }

        $array = [
            'idx' => $data['idx'],
            'Person_id' => null,
            'Lpu_id' => null,
            'LpuAttachName' => null,
            'LpuDirection_id' => null,
            'LpuDirection_Name' => null
        ];
        $response = $result->result('array');

        if (empty($response)) {
            return $array;
        }

        // В целях оптимизации убрал из запроса сортировку по Server_pid и вынес ее в PHP
        // @task https://redmine.swan.perm.ru/issues/94106
        $Server_pid = -1;

        foreach ($response as $row) {
            if (-1 == $Server_pid || $Server_pid > $row['Server_pid']) {
                $Server_pid = $row['Server_pid'];
                $array = $row;
            }
        }

        return $array;
    }

    /**
     * Обновление доп. полей в БД
     * @param $data
     */
    public function updateIPRARegisterDopFields($data)
    {
        $this->db->query("
			update
				{$this->scheme}.IPRARegistry
			set
				IPRARegistry_RecepientType = :IPRARegistry_RecepientType,
				IPRARegistry_IPRAident = :IPRARegistry_IPRAident
			where
				IPRARegistry_Number = :IPRARegistry_Number
            and
                IPRARegistry_issueDate = :IPRARegistry_issueDate
            and
                IPRARegistry_Protocol = :IPRARegistry_Protocol
		", [
            'IPRARegistry_Number' => $data['IPRARegistry_Number'],
            'IPRARegistry_issueDate' => $data['IPRARegistry_issueDate'],
            'IPRARegistry_Protocol' => $data['IPRARegistry_Protocol'],
            'IPRARegistry_RecepientType' => !empty($data['IPRARegistry_RecepientType']) ? $data['IPRARegistry_RecepientType'] : null,
            'IPRARegistry_IPRAident' => !empty($data['IPRARegistry_IPRAident']) ? $data['IPRARegistry_IPRAident'] : null
        ]);
    }

    /**
     *  Определение ЛПУ прикрепления пациента
     * @param $data
     * @return array|bool|void
     */
    public function getLpuAttachData($data)
    {
        return;
        $params = [
            'Person_id' => $data['Person_id']

        ];

        $query = "
            select
                Lpu_id as \"Lpu_id\",
                (select Lpu_Nick from v_Lpu where Lpu_id = Lpu_id limit 1)  as LpuAttachName
            from
                dbo.v_PersonCard PC
            where
                PC.Person_id = :Person_id
            and
                PC.LpuAttachType_id = 1
            limit 1        
        ";

        if ($this->useTest == true) {
            $test = $this->load->database('bdtest', true);
            $tets->query_timeout = 3600;

            $result = $test->query($query, $params);
        } else {
            $test->query_timeout = 3600;
            $result = $this->db->query($query, $params);
        }

        $this->load->database();

        if (!is_object($result)) {
            return false;
        }

        $array = $result->result('array');
        if (empty($array)) {
            return [['Lpu_id' => null, 'LpuAttachName' => null]];
        } else {
            return $array;
        }
    }

    /**
     *  Определение пациента в РМИАС
     * @param $data
     * @return array|bool
     */
    public function getPerson_id($data)
    {
        $params = [
            'Person_FirName' => $data['Person_FirName'],
            'Person_surName' => $data['Person_surName'],
            'Person_secName' => $data['Person_secName'],
            'Person_BirthDay' => $data['Person_BirthDay'],
            'Person_Snils' => $data['Person_Snils'],
            'Document_Num' => $data['Document_Num'],
        ];

        $query = "
            select
                Person_id as \"Person_id\"
            from
                dbo.v_PersonState
            where 
                (
                    replace(Person_FirName, 'Ё', 'Е') = replace(:Person_FirName, 'Ё', 'Е')
                and 
                    replace(Person_secName, 'Ё', 'Е') = replace(:Person_secName, 'Ё', 'Е')                           
                and
                    to_char(Person_BirthDay, {$this->dateTimeForm120}) = :Person_BirthDay
                and
                    (Person_Snils is not null or Document_Num is not null)
                )
            and 
                (
                    Person_Snils = :Person_Snils 
                or
                    Document_Num = :Document_Num
                )   
            order by Server_pid  
        ";
        //echo getDebugSql($query, $params);
        //exit;

        $work = $this->load->database('bdwork', true);
        $work->query_timeout = 3600;
        $result = $work->query($query, $params);
        $this->load->database();

        if (!is_object($result)) {
            return false;
        }

        $array = $result->result('array');
        
        if (empty($array)) {
            return [['Person_id' => null]];
        } else {
            return $array;
        }
    }

    /**
     * Количество записей в регистре ИПРА
     */
    public function getIpraCount()
    {
        $query = "
            SELECT
                count(*) as \"IpraCount\"
            FROM
                IPRARegistry
            WHERE
                IPRARegistry_Deleted = 1
        ";
        $result = $this->db->query($query);

        if (!is_object($result)) {
            return false;
        }
        
        return $result->result('array');
    }
}    