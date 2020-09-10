<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       Dmitry Storozhev
 * @version      20.07.2011
 */

class ClinExWork_model extends swPgModel
{
	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 *	Поиск
	 */
	function searchData($data, $getCount = false)
	{
		$filter = "(1 = 1)";
		$main_alias  = "";
		$queryParams = array();
		array_walk($data, 'ConvertFromUTF8ToWin1251');

		//print_r($data);

		$filter .= " and EVK.Lpu_id = :Lpu_id";
		$queryParams['Lpu_id'] = $data['Lpu_id'];

		if(!empty($data['ExpertiseDateRange'][0]) && !empty($data['ExpertiseDateRange'][1])) {
			$filter .= " and (EVK.EvnVK_setDT >= cast(:exp_startdate as date) and EVK.EvnVK_setDT <= cast(:exp_enddate as date))";
			$queryParams['exp_startdate'] = $data['ExpertiseDateRange'][0];
			$queryParams['exp_enddate'] = $data['ExpertiseDateRange'][1];
		}

		/*if(!empty($data['LpuSection_id'])) {
			$filter .= " and EVK.LpuSection_id = :LpuSection_id";
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}

		if(!empty($data['MedStaffFact_id'])) {
			$filter .= " and EVK.MedStaffFact_id = :MedStaffFact_id";
			$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
		}
		*/

		if(!empty($data['MedService_id'])){
			$filter .= " and MS.MedService_id = :MedService_id";
			$queryParams['MedService_id'] = $data['MedService_id'];
		}

		if(!empty($data['ExpertiseNameType_id'])) {
			$filter .= " and EVK.ExpertiseNameType_id = :ExpertiseNameType_id";
			$queryParams['ExpertiseNameType_id'] = $data['ExpertiseNameType_id'];
		}

		if(!empty($data['ExpertiseEventType_id'])) {
			$filter .= " and EVK.ExpertiseEventType_id = :ExpertiseEventType_id";
			$queryParams['ExpertiseEventType_id'] = $data['ExpertiseEventType_id'];
		}

		if(!empty($data['Person_SurName'])) {
			$filter .= " and Person_all.Person_SurName ilike :Person_SurName || '%'";
			$queryParams['Person_SurName'] = rtrim($data['Person_SurName']);
		}

		if(!empty($data['Person_FirName'])) {
			$filter .= " and Person_all.Person_FirName ilike :Person_FirName || '%'";
			$queryParams['Person_FirName'] = rtrim($data['Person_FirName']);
		}

		if(!empty($data['Person_SecName'])) {
			$filter .= " and Person_all.Person_SecName ilike :Person_SecName || '%'";
			$queryParams['Person_SecName'] = rtrim($data['Person_SecName']);
		}

		if(!empty($data['Person_BirthDay'])) {
			$filter .= " and Person_all.Person_BirthDay = cast(:Person_BirthDay as date)";
			$queryParams['Person_BirthDay'] = $data['Person_BirthDay'];
		}

		if(!empty($data['PatientStatusType_id'])) {
			//$filter .= " and EVK.PatientStatusType_id = :PatientStatusType_id";
			$queryParams['PatientStatusType_id'] = $data['PatientStatusType_id'];

			//Добавим еще и учет записей в EvnVKPatientStatusType
			$filter .= "
				and (
					EVK.PatientStatusType_id = :PatientStatusType_id
					or
					exists (
						select
							PST.PatientStatusType_id
						from v_EvnVKPatientStatusType PST
						where PST.EvnVK_id = EVK.EvnVK_id
							and PST.PatientStatusType_id = :PatientStatusType_id
						limit 1
					)
				)
			";
		}

		if(!empty($data['Diag_id'])) {
			$filter .= " and EVK.Diag_id = :Diag_id";
			$queryParams['Diag_id'] = $data['Diag_id'];
		}

		if(!empty($data['EvnVK_DirectionDate'][0]) && !empty($data['EvnVK_DirectionDate'][1])) {
			if ($this->getRegionNick() == 'perm') {
				$filter .= " and (EPM.EvnPrescrMse_issueDT >= cast(:mse_to_startdate as date) and EPM.EvnPrescrMse_setDT <= cast(:mse_to_enddate as date))";
			} else {
				$filter .= " and (EPM.EvnPrescrMse_setDT >= cast(:mse_to_startdate as date) and EPM.EvnPrescrMse_setDT <= cast(:mse_to_enddate as date))";
			}
			$queryParams['mse_to_startdate'] = $data['EvnVK_DirectionDate'][0];
			$queryParams['mse_to_enddate'] = $data['EvnVK_DirectionDate'][1];
		}

		if(!empty($data['EvnVK_ConclusionDate'][0]) && !empty($data['EvnVK_ConclusionDate'][1])) {
			$filter .= " and (EM.EvnMse_setDT >= cast(:mse_with_startdate as date) and EM.EvnMse_setDT <= cast(:mse_with_enddate as date))";
			$queryParams['mse_with_startdate'] = $data['EvnVK_ConclusionDate'][0];
			$queryParams['mse_with_enddate'] = $data['EvnVK_ConclusionDate'][1];
		}

		if(!empty($data['EvnVK_isUseStandard'])) {
			$filter .= " and coalesce(EVK.EvnVK_isUseStandard,1) = :EvnVK_isUseStandard";
			$queryParams['EvnVK_isUseStandard'] = $data['EvnVK_isUseStandard'];
		}

		if(!empty($data['EvnVK_isAberration'])) {
			$filter .= " and EVK.EvnVK_isAberration = :EvnVK_isAberration";
			$queryParams['EvnVK_isAberration'] = $data['EvnVK_isAberration'];
		}

		if(!empty($data['EvnVK_isErrors'])) {
			$filter .= " and EVK.EvnVK_isErrors = :EvnVK_isErrors";
			$queryParams['EvnVK_isErrors'] = $data['EvnVK_isErrors'];
		}

		if(!empty($data['EvnVK_isResult'])) {
			$filter .= " and EVK.EvnVK_isResult = :EvnVK_isResult";
			$queryParams['EvnVK_isResult'] = $data['EvnVK_isResult'];
		}

		if(!empty($data['EvnStatus_id'])) {
			$filter .= " and EPM.EvnStatus_id = :EvnStatus_id";
			$queryParams['EvnStatus_id'] = $data['EvnStatus_id'];
		}

		if($data['EvnVK_isControl'] == 1) {
			$filter .= " and EVK.EvnVK_isControl = 2";
		}
		// Ищем службы только с типом ВК
		$filter .= " and MS.MedServiceType_id = 1";
		$field = "EVK.EvnVK_id";
		$query = "
			select
				-- select
				EVK.EvnVK_id as \"EvnVK_id\",
				EPM.EvnPrescrMse_id as \"EvnPrescrMse_id\",
				EPM.Person_id as \"Person_id\",
				EPM.Server_id as \"Server_id\",
				to_char (cast(EVK.EvnVK_setDT as timestamp), 'dd.mm.yyyy') as \"EvnVK_ExpertiseDate\",
				to_char (cast(EVK.EvnVK_didDT as timestamp), 'dd.mm.yyyy') as \"EvnVK_ControlDate\",  -- не обязательно
				--MSF.Person_FIO as MSFPerson_FIO,
				--LS.LpuSection_FullName as LpuSection_FullName,
				MS.MedService_id as \"MedService_id\",
				MS.MedService_Name as \"MedService_Name\",
				to_char (cast(Person_all.Person_BirthDay as timestamp), 'dd.mm.yyyy') as \"Person_BirthDay\",
				Person_all.Person_Fio as \"Person_Fio\",
				Diag.Diag_FullName as \"Diag_Name\",
				ExpertiseEventType.ExpertiseEventType_Name as \"ExpertiseEventType_Name\",
				ExpertiseEventType.ExpertiseEventType_Code as \"ExpertiseEventType_Code\",
				ExpertiseNameType.ExpertiseNameType_Name as \"ExpertiseNameType_Name\",
				case when EVK.EvnVK_isAberration = 2 then 'true' else 'false' end as \"EvnVK_isAberration\",
				case when EVK.EvnVK_isErrors = 2 then 'true' else 'false' end as \"EvnVK_isErrors\",
				case when EVK.EvnVK_isResult = 2 then 'true' else 'false' end as \"EvnVK_isResult\",
				to_char (cast(EPM.EvnPrescrMse_setDT as timestamp), 'dd.mm.yyyy') as \"EvnVK_DirectionDate\",
				case when EM.EvnMse_id is not null
					then '№' || cast(EM.EvnMse_NumAct as varchar(10)) || ' от ' || to_char (cast(EM.EvnMse_setDT as timestamp), 'dd.mm.yyyy')
					else ''
				end as \"EvnVK_ConclusionDate\",
				case when EVK.EvnVK_isControl = 2 then 'true' else 'false' end as \"EvnVK_isControl\",
				case when EVK.EvnVK_isReserve = 2 then 'true' else 'false' end as \"EvnVK_isReserve\",
				EVK.EvnVK_NumProtocol as \"num\",
				EPMS.EvnStatus_id as \"EvnStatus_id\",
				EPMS.EvnStatus_Name as \"EvnStatus_Name\",
				to_char (EM.EvnMse_setDT, 'dd.mm.yyyy') as \"EvnMse_setDT\"
				-- end select
			from
				-- from
				v_EvnVK EVK
				LEFT JOIN v_ExpertiseEventType ExpertiseEventType on ExpertiseEventType.ExpertiseEventType_id = EVK.ExpertiseEventType_id
				LEFT JOIN v_ExpertiseNameType ExpertiseNameType on ExpertiseNameType.ExpertiseNameType_id = EVK.ExpertiseNameType_id
				LEFT JOIN v_Person_all Person_all on Person_all.Person_id = EVK.Person_id
					and EVK.Server_id = Person_all.Server_id
					and EVK.PersonEvn_id = Person_all.PersonEvn_id
				LEFT JOIN v_Diag Diag on Diag.Diag_id = EVK.Diag_id
				LEFT JOIN v_EvnPrescrMse EPM on EPM.EvnVK_id = EVK.EvnVK_id
				LEFT JOIN v_EvnMse EM on EM.EvnPrescrMse_id = EPM.EvnPrescrMse_id
				left join v_MedService MS on MS.MedService_id = EVK.MedService_id
				left join v_EvnStatus EPMS on EPM.EvnStatus_id = EPM.EvnStatus_id
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				EVK.EvnVK_NumProtocol
				-- end order by
		";

		$response =  $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);

		return $response;
	}

	/**
	 *	Метод
	 */
	function getEvnVKStickPeriod($data)
	{
		$query = '
			select coalesce( extract( day from (max(EvnStick_endDate) - min(EvnStick_begDate)) ), 0) as "EvnVKStickPeriod"
                from v_EvnStick 
                    where Person_id = :Person_id and
                        EvnStick_workDT is null
		';

		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Еще один метод
	 */
	function getNewEvnVKNumber($data = array())
	{
		$query = "select COALESCE(max(cast (COALESCE(EvnVK_NumProtocol, CAST(0 as varchar(10))) as bigint)), 0) as \"EvnVK_NumProtocol\"
                    from v_EvnVK 
                    where IsNumeric(EvnVK_NumProtocol || 'e0') = 1 and
      dbo.\"patindex\"('%.%', EvnVK_NumProtocol) = 0";
		$res = $this->db->query($query);
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 *	Метод
	 */
	function getEvnVK($data)
	{
		$queryParams = array();
		$where = 'EVK.EvnVK_id = :EvnVK_id ' . PHP_EOL;
		$queryParams['EvnVK_id'] = $data['EvnVK_id'];
		$groups = '|' . $data['session']['groups'] . '|';
		$pos = strpos($groups, '|minzdravdlo|');

		if ($pos === false) {
			$where .= ' and EVK.Lpu_id = :Lpu_id ';
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

        $query = "
			select 
				EVK.EvnVK_id as \"EvnVK_id\",
				to_char (cast(EVK.EvnVK_setDate as timestamp), 'dd.mm.yyyy') as \"EvnVK_setDT\",
				to_char (cast(EVK.EvnVK_didDate as timestamp), 'dd.mm.yyyy') as \"EvnVK_didDT\",
				PS.Person_id as \"Person_id\",
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\",
				to_char (cast(PS.Person_BirthDay as timestamp), 'dd.mm.yyyy') as \"Person_BirthDay\",
				EVK.Server_id as \"Server_id\",
				EVK.CauseTreatmentType_id as \"CauseTreatmentType_id\",
				EVK.Diag_id as \"Diag_id\",
				EVK.Diag_sid as \"Diag_sid\",
				EVK.EvnStickBase_id as \"EvnStickBase_id\",
				EVK.EvnStickWorkRelease_id as \"EvnStickWorkRelease_id\",
				EVK.EvnVK_UseStandard as \"EvnVK_UseStandard\",
				EVK.EvnVK_AberrationDescr as \"EvnVK_AberrationDescr\",
				EM.EvnMse_InvalidCause as \"EvnVK_AddInfo\",
				to_char (cast(EM.EvnMse_setDT as timestamp), 'dd.mm.yyyy') as \"EvnVK_ConclusionDate\",
				case
					when EM.InvalidGroupType_id = 1 then EM.EvnMse_InvalidCauseDeni
					else IGT.InvalidGroupType_Name
				end as \"EvnVK_ConclusionDescr\",
				EPM.EvnPrescrMse_id as \"EvnPrescrMse_id\",
				to_char (cast(EM.EvnMse_ReExamDate as timestamp), 'dd.mm.yyyy') as \"EvnVK_ConclusionPeriodDate\",
				to_char (cast(EPM.EvnPrescrMse_setDT as timestamp), 'dd.mm.yyyy') as \"EvnVK_DirectionDate\",
				EVK.EvnVK_ErrorsDescr as \"EvnVK_ErrorsDescr\",
				EVK.EvnVK_ExpertiseStickNumber as \"EvnVK_ExpertiseStickNumber\",
				COALESCE(EVK.EvnVK_isUseStandard,1) as \"EvnVK_isUseStandard\",
				EVK.EvnVK_isAberration as \"EvnVK_isAberration\",
				(case when (EVK.EvnVK_isControl = 2) then 1 else 0 end) as \"EvnVK_isControl\",
				EVK.EvnVK_isErrors as \"EvnVK_isErrors\",
				(case when (EVK.EvnVK_isReserve = 2) then 1 else 0 end) as \"EvnVK_isReserve\",
				EVK.EvnVK_isResult as \"EvnVK_isResult\",
				EVK.EvnVK_NumCard as \"EvnVK_NumCard\",
				EVK.EvnVK_NumProtocol as \"EvnVK_NumProtocol\",
				EVK.EvnVK_ResultDescr as \"EvnVK_ResultDescr\",
				EVK.EvnVK_StickDuration as \"EvnVK_StickDuration\",
				EVK.EvnVK_StickPeriod as \"EvnVK_StickPeriod\",
				EVK.ExpertiseEventType_id as \"ExpertiseEventType_id\",
				EVK.ExpertiseNameSubjectType_id as \"ExpertiseNameSubjectType_id\",
				EVK.ExpertiseNameType_id as \"ExpertiseNameType_id\",
				--EVK.LpuSection_id as \"LpuSection_id\",
				--EVK.MedStaffFact_id as \"MedStaffFact_id\",
				EVK.MedService_id as \"MedService_id\",
				--EVK.Okved_id as \"Okved_id\",
				EVK.EvnVK_Prof as \"EvnVK_Prof\",
				COALESCE(EVK.PatientStatusType_id,0) as \"PatientStatusType_id\",
				EVK.PersonEvn_id as \"PersonEvn_id\",
				(select Diag_Name from v_Diag  where Diag_id = EVK.Diag_id) AS \"Diag_Name1\",
				(select Diag_Name from v_Diag  where Diag_id = EVK.Diag_sid) AS \"Diag_Name2\",
				(case when (EVK.EvnVK_isAutoFill = 2) then 1 else 0 end) AS \"EvnVK_isAutoFill\",
				EPVK.EvnPrescrVK_id as \"EvnPrescrVK_id\",
				case when EVK.MedPersonal_id is not null then EVK.MedPersonal_id else EPVK.MedPersonal_sid end as \"MedPersonal_id\",
				EVK.EvnVK_ExpertDescr as \"EvnVK_ExpertDescr\",
				EVK.EvnVK_DecisionVK as \"EvnVK_DecisionVK\",
				EVK.EvnVK_LVN as \"EvnVK_LVN\",
				EVK.EvnVK_Note as \"EvnVK_Note\",
				EVK.EvnVK_WorkReleasePeriod as \"EvnVK_WorkReleasePeriod\",
				EDH.EvnDirectionHTM_id as \"EvnDirectionHTM_id\",
				EVK.EvnVK_MainDisease as \"EvnVK_MainDisease\",
				PEVK.PalliatEvnVK_IsPMP as \"PalliatEvnVK_IsPMP\",
				PEVK.PalliativeType_id as \"PalliativeType_id\",
				PEVK.PalliatEvnVK_IsIVL as \"PalliatEvnVK_IsIVL\",
				PEVK.PalliatEvnVK_IsSpecMedHepl as \"PalliatEvnVK_IsSpecMedHepl\",
				PEVK.PalliatEvnVK_VolumeMedHepl as \"PalliatEvnVK_VolumeMedHepl\",
				PEVK.ConditMedCareType_id as \"ConditMedCareType_id\",
				PEVK.PalliatEvnVK_IsSurvey as \"PalliatEvnVK_IsSurvey\",
				PEVK.PalliatEvnVK_VolumeSurvey as \"PalliatEvnVK_VolumeSurvey\",
				PEVK.PalliatEvnVK_DirSocialProt as \"PalliatEvnVK_DirSocialProt\",
				PEVK.PalliatEvnVK_IsInfoDiag as \"PalliatEvnVK_IsInfoDiag\",
				PEVK.PalliatEvnVK_TextTIR as \"PalliatEvnVK_TextTIR\",
				PEVKD.PalliatEvnVKMainSyndrome as \"PalliatEvnVKMainSyndrome\",
				PEVKD.PalliatEvnVKTechnicInstrumRehab as \"PalliatEvnVKTechnicInstrumRehab\",
				substring(EvnVKPatientStatusTypeStr.EvnVKPatientStatusType_Items, 1, length(EvnVKPatientStatusTypeStr.EvnVKPatientStatusType_Items)) as \"PatientStatusType_List\"
			from
				v_EvnVK EVK
				left join v_PersonState PS on PS.Person_id = EVK.Person_id
				left join v_EvnPrescrMse EPM on EPM.EvnVK_id = EVK.EvnVK_id
				left join v_EvnMse EM on EM.EvnPrescrMse_id = EPM.EvnPrescrMse_id
				left join v_InvalidGroupType IGT on IGT.InvalidGroupType_id = EM.InvalidGroupType_id
				left join v_EvnPrescrVK EPVK on EPVK.EvnPrescrVK_id = EVK.EvnPrescrVK_id
				left join v_EvnDirectionHTM EDH on EDH.EvnDirectionHTM_pid = EVK.EvnVK_id
				LEFT JOIN LATERAL (
					select string_agg(CAST(EVKPST.PatientStatusType_id as varchar),', ') as EvnVKPatientStatusType_Items
					from v_EvnVKPatientStatusType EVKPST
					where EVKPST.EvnVK_id = EVK.EvnVK_id
				) EvnVKPatientStatusTypeStr ON true
				left join PalliatEvnVK PEVK  on PEVK.EvnVK_id = EVK.EvnVK_id
				LEFT JOIN LATERAL (
					select
						(
							select string_agg(cast(MainSyndrome_id as varchar),',')
							from PalliatEvnVKMainSyndromeLink
							where PalliatEvnVK_id = PEVK.PalliatEvnVK_id
						) as PalliatEvnVKMainSyndrome,
						(
							select string_agg(cast(TechnicInstrumRehab_id as varchar),',')
							from PalliatEvnVKTechnicInstrumRehabLink
							where PalliatEvnVK_id = PEVK.PalliatEvnVK_id
						) as PalliatEvnVKTechnicInstrumRehab
					limit 1
                ) PEVKD ON true
			where
				{$where}
            limit 1
		";

		//echo getDebugSQL($query, $queryParams);exit;
		$res = $this->db->query($query, $queryParams);
		if ( is_object($res) )
		{
			$res = $res->result('array');
			$res[0]['SopDiagList'] = $this->getDiagList($data, 1);
			$res[0]['OslDiagList'] = $this->getDiagList($data, 2);
			$res[0]['PalliatFamilyCare'] = $this->queryResult("
				select 
				    PalliatFamilyCare_id as \"PalliatFamilyCare_id\",
				    FamilyRelationType_id as \"FamilyRelationType_id\",
				    PalliatFamilyCare_Age as \"PalliatFamilyCare_Age\",
				    PalliatFamilyCare_Phone as \"PalliatFamilyCare_Phone\"
				from 
				    PalliatFamilyCare
				where 
				    EvnVK_id = ?
			", [$data['EvnVK_id']]);
			return $res;
		}
		else
		{
			return false;
		}
	}

	/**
	 *	Method description
	 */
	function getDiagList($data, $type)
	{
		//var_dump($data);die;
		$field = ($type == 1) ? 'Diag_id' : 'Diag_oid';
		$descr = ($type == 1) ? " ,coalesce(EvnVKDiagLink_DescriptDiag,'') as \"DescriptDiag\"" : '';
		$query = "
			select {$field} as \"Diag_id\" {$descr}
			from EvnVKDiagLink
			where EvnVK_id = :EvnVK_id and {$field} is not null
		";
		if($type == 1)
		{
			$result = $this->queryResult($query, $data);
			if(is_array($result))
			{
				return $result;
			}
			return false;
		}
		if($type == 2)
		{
			return $this->queryList($query, $data);
		}
		//return $this->queryList($query, $data);
	}

	/**
	 *	Method description
	 */
	function getEvnVKSopDiagViewData($data)
	{
		$query = "
			select 
			    EvnVK_id as \"EvnVK_id\", 
			    EvnVKDiagLink_id as \"EvnVKDiagLink_id\", 
			    d.Diag_id as \"Diag_id\", 
			    d.Diag_Code as \"Diag_Code\", 
			    d.Diag_Name as \"Diag_Name\"
			from EvnVKDiagLink
				inner join v_Diag d on d.Diag_id = EvnVKDiagLink.Diag_id
			where EvnVK_id = :EvnVK_id
		";

		return $this->queryResult($query, $data);
	}

	/**
	 *	Method description
	 */
	function getEvnVKOslDiagViewData($data)
	{
		$query = "
			select 
			    EvnVK_id as \"EvnVK_id\", 
			    EvnVKDiagLink_id as \"EvnVKDiagLink_id\", 
			    d.Diag_id as \"Diag_id\", 
			    d.Diag_Code as \"Diag_Code\", 
			    d.Diag_Name as \"Diag_Name\"
			from EvnVKDiagLink
				inner join v_Diag d on d.Diag_id = EvnVKDiagLink.Diag_oid
			where EvnVK_id = :EvnVK_id
		";

		return $this->queryResult($query, $data);
	}

	/**
	 *	Палка
	 */
	function getEvnVKStick($data)
	{
		$filter = '1=1';
		$union_all = '';

		if( !empty($data['Evn_id']) ) {
			$filter .= ' and ES.EvnStick_rid = :Evn_id --and coalesce(EvnPL.EvnPL_id, EvnPS.EvnPS_id) is not null';
			$union_all .= "
				union all
			
				select distinct
					ESB.EvnStickBase_id as \"EvnStickBase_id\",
					(
						coalesce(ES.EvnStick_Ser, '')
							||' '||coalesce(ES.EvnStick_Num, '')
							||(case when (ES.EvnStick_begDate IS NOT NULL) then ' выдан: '
								||to_char (cast(ES.EvnStick_begDate as timestamp), 'dd.mm.yyyy') else '' end)
							||(case when (ES.EvnStick_endDate IS NOT NULL) then ' по '
								||to_char (cast(ES.EvnStick_endDate as timestamp), 'dd.mm.yyyy') else '' end)
					) as \"EvnStick_all\"
				FROM
					EvnLink EL
					inner join v_EvnStickBase ESB on ESB.EvnStickBase_id = EL.Evn_lid
					inner join v_EvnStick ES on ES.EvnStick_id = ESB.EvnStickBase_id
				WHERE 1=1
					and EL.Evn_id = :Evn_id
			";
		} else if ( !empty($data['EvnStickBase_id']) ) {
			$filter .= ' and ES.EvnStick_id = :EvnStickBase_id';
		}
		$query = "
			select
				ES.EvnStick_id as \"EvnStickBase_id\",
				(
					coalesce(ES.EvnStick_Ser, '')||' '||coalesce(ES.EvnStick_Num, '')||
					(case when (ES.EvnStick_begDate IS NOT NULL) then ' выдан: '||to_char (cast(ES.EvnStick_begDate as timestamp), 'dd.mm.yyyy') else '' end)||
					(case when (ES.EvnStick_endDate IS NOT NULL) then ' по '||to_char (cast(ES.EvnStick_endDate as timestamp), 'dd.mm.yyyy') else '' end)
				) as \"EvnStick_all\"
			from
				v_EvnStick ES
				left join v_EvnPL EvnPL on ES.EvnStick_rid = EvnPL.EvnPL_id
				left join v_EvnPS EvnPS on ES.EvnStick_rid = EvnPS.EvnPS_id
			where
				{$filter}
				{$union_all}
		";

		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Проверка уникальности номера протокола ВК
	 */
	public function checkEvnVKNumProtocol($data) {
		$filterList = array();
		$params = array(
			'EvnVK_NumProtocol' => $data['EvnVK_NumProtocol'],
			'EvnVK_setDT' => $data['EvnVK_setDT'],
			'Lpu_id' => $data['Lpu_id'],
		);

		if ( !empty($data['EvnVK_id']) ) {
			$filterList[] = "EVK.EvnVK_id <> :EvnVK_id";
			$params['EvnVK_id'] = $data['EvnVK_id'];
		}

		if ( $this->getRegionNick() == 'ufa' && !empty($data['ExpertiseNameType_id']) ) {
			$filterList[] = "EVK.ExpertiseNameType_id = :ExpertiseNameType_id";
			$params['ExpertiseNameType_id'] = $data['ExpertiseNameType_id'];
		}

		$query = "
			select
				EVK.EvnVK_id as \"EvnVK_id\"
			from
				v_EvnVK EVK
			where
				EVK.EvnVK_NumProtocol = :EvnVK_NumProtocol
				and date_part('year', EVK.EvnVK_setDate) = date_part('year', cast(:EvnVK_setDT as date))
				and Lpu_id = :Lpu_id
				" . (count($filterList) > 0 ? "and " . implode(" and ", $filterList) : "") . "
		    limit 1
		";

		$response = array(array('success' => true, 'Error_Msg' => '', 'Alert_Msg' => ''));
		//echo getDebugSQL($query,$params);exit;
		$checkResult = $this->getFirstResultFromQuery($query, $params, true);

		if ( $checkResult === false ) {
			$response[0]['success'] = false;
			$response[0]['Error_msg'] = 'Ошибка при проверке номера протокола ВК';
		}
		else if ( !empty($checkResult) ) {
			$msg = "Номер протокола ВК должен быть уникален в рамках года " . substr($data['EvnVK_setDT'], 0, 4) . ".";
			$response[0]['Alert_Msg'] = $msg;
		}

		return $response;
	}

	/**
	 *	Сохранение
	 */
	function saveEvnVK($data)
	{
		$this->beginTransaction();
		$procedure = 'p_EvnVK_'.$data['action'];
		if (!empty($data['EvnVK_id'])) {
			$with = "
				with mv as (
					select
			  			case when EvnVK_IsSigned = 2 then 1 else EvnVK_IsSigned end as EvnVK_IsSigned,
						pmUser_signID,
						EvnVK_signDT
					from
						v_EvnVK
					where
						EvnVK_id = :EvnVK_id
				)
			";
		} else {
			$with = "
				with mv as (
					select
			  			null::bigint as EvnVK_IsSigned,
						null::bigint as pmUser_signID,
						null::timestamp as EvnVK_signDT
				)
			";
		}

		$query = "
			{$with}
			select
				EvnVK_id as \"EvnVK_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from " . $procedure . "(
				EvnVK_id := :EvnVK_id,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				EvnVK_setDT := :EvnVK_setDT,
				EvnVK_isReserve := :EvnVK_isReserve,
				MedPersonal_id := :MedPersonal_id,
				EvnVK_didDT := :EvnVK_didDT,
				EvnVK_isControl := :EvnVK_isControl,
				MedService_id := :MedService_id,
				EvnVK_NumCard := :EvnVK_NumCard,
				PatientStatusType_id := NULL,
				EvnVK_Prof := :EvnVK_Prof,
				CauseTreatmentType_id := :CauseTreatmentType_id,
				Diag_id := :Diag_id,
				Diag_sid := :Diag_sid,
				ExpertiseNameType_id := :ExpertiseNameType_id,
				ExpertiseEventType_id := :ExpertiseEventType_id,
				ExpertiseNameSubjectType_id := :ExpertiseNameSubjectType_id,
				EvnStickBase_id := :EvnStickBase_id,
				EvnStickWorkRelease_id := :EvnStickWorkRelease_id,
				EvnVK_ExpertiseStickNumber := :EvnVK_ExpertiseStickNumber,
				EvnVK_StickPeriod := :EvnVK_StickPeriod,
				EvnVK_StickDuration := :EvnVK_StickDuration,
				EvnVK_DirectionDate := :EvnVK_DirectionDate,
				EvnVK_ConclusionDate := :EvnVK_ConclusionDate,
				EvnVK_ConclusionPeriodDate := :EvnVK_ConclusionPeriodDate,
				EvnVK_ConclusionDescr := :EvnVK_ConclusionDescr,
				EvnVK_AddInfo := :EvnVK_AddInfo,
				EvnVK_isUseStandard := :EvnVK_isUseStandard,
				EvnVK_isAberration := :EvnVK_isAberration,
				EvnVK_isErrors := :EvnVK_isErrors,
				EvnVK_isResult := :EvnVK_isResult,
				EvnVK_UseStandard := :EvnVK_UseStandard,
				EvnVK_AberrationDescr := :EvnVK_AberrationDescr,
				EvnVK_ErrorsDescr := :EvnVK_ErrorsDescr,
				EvnVK_ResultDescr := :EvnVK_ResultDescr,
				EvnVK_ExpertDescr := :EvnVK_ExpertDescr,
				EvnVK_DecisionVK := :EvnVK_DecisionVK,
				PersonEvn_id := :PersonEvn_id,
				EvnVK_NumProtocol := :EvnVK_NumProtocol,
				EvnVK_isAutoFill := :EvnVK_isAutoFill,
				EvnPrescrVK_id := :EvnPrescrVK_id,
				EvnVK_LVN := :EvnVK_LVN,
				EvnVk_Note := :EvnVK_Note,
				EvnVK_WorkReleasePeriod := :EvnVK_WorkReleasePeriod,
				EvnVK_MainDisease := :EvnVK_MainDisease,
				EvnVK_IsSigned := (select EvnVK_IsSigned from mv),
				pmUser_signID := (select pmUser_signID from mv),
				EvnVK_signDT := (select EvnVK_signDT from mv),
				pmUser_id := :pmUser_id
			)
		";
		$queryParams = $data;

		//echo getDebugSQL($query, $queryParams);
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$result = $result->result('array');
			if (!$this->isSuccessful($result)) {
				return $result;
			}

			$EvnVK_id = $data['EvnVK_id'] = $result[0]['EvnVK_id'];
			if ($data['isEmk'] != 1) {
				$this->saveEvnVKDiag($data);
			}

			$this->savePalliatEvnVK($data);

			$EvnVK_id = $result[0]['EvnVK_id'];

			//Сохраняем статус пациента.
			if(!empty($data['PatientStatusType_List']))
			{
				//Сначала очистим всё в EvnVKPatientStatusType.
				$params_get_EvnVKPatientStatusType = array(
					'EvnVK_id'	=> $EvnVK_id
				);
				$query_get_EvnVKPatientStatusType = "
					select EvnVKPatientStatusType_id as \"EvnVKPatientStatusType_id\"
					from v_EvnVKPatientStatusType
					where EvnVK_id = :EvnVK_id;
				";
				$result_get_EvnVKPatientStatusType = $this->db->query($query_get_EvnVKPatientStatusType,$params_get_EvnVKPatientStatusType);
				if(is_object($result_get_EvnVKPatientStatusType))
				{
					$result_get_EvnVKPatientStatusType = $result_get_EvnVKPatientStatusType->result('array');
					for($i=0;$i<count($result_get_EvnVKPatientStatusType);$i++)
					{
						$params_remove_EvnVKPatientStatusType = array(
							'EvnVKPatientStatusType_id'	=> $result_get_EvnVKPatientStatusType[$i]['EvnVKPatientStatusType_id'],
						);
						$query_remove_EvnVKPatientStatusType = "
							SELECT 
                                Error_Code as \"Error_Code\", 
                                error_message as \"Error_Msg\"
                            FROM dbo.p_EvnVKPatientStatusType_del(
                            	EvnVKPatientStatusType_id := :EvnVKPatientStatusType_id
                            );							
						";
						$result_remove_EvnVKPatientStatusType = $this->db->query($query_remove_EvnVKPatientStatusType,$params_remove_EvnVKPatientStatusType);
					}
				}
				$query_add_EvnVKPatientStatusType = "
                	SELECT 
                	    EvnVKPatientStatusType_id as \"EvnVKPatientStatusType_id\",
                	    error_code as \"Error_Code\",
                	    error_message as \"Error_Msg\"
                	FROM dbo.p_EvnVKPatientStatusType_ins(
						EvnVK_id := :EvnVK_id,
						PatientStatusType_id := :PatientStatusType_id,
						pmUser_id := :pmUser_id
        			);
				";
				$PatientStatusType = explode(",",$data['PatientStatusType_List']);
				for($i=0;$i<count($PatientStatusType);$i++)
				{
					$PatientStatusType_item = $PatientStatusType[$i];
					$params_add_EvnVKPatientStatusType = array(
						'EvnVK_id'	=> $EvnVK_id,
						'PatientStatusType_id'	=> (int) $PatientStatusType_item,
						'pmUser_id' => $data['pmUser_id']
					);
					$result_add_EvnVKPatientStatusType = $this->db->query($query_add_EvnVKPatientStatusType,$params_add_EvnVKPatientStatusType);
				}
			}

			//Сохраним значение нумератора
			//Numerator_id
			if(isset($data['Numerator_id']) && $data['Numerator_id'] > 0 && is_numeric($data['EvnVK_NumProtocol']))
			{
				$need_num_update = false;
				$params_upd_numerator = array(
					'Numerator_id'	=> $data['Numerator_id'],
					'Numerator_Num'	=> $data['EvnVK_NumProtocol']
				);
				$query_check_numerator = "
					select N.Numerator_id as \"Numerator_id\"
					from v_NumeratorRezerv NR
					inner join v_Numerator N on N.Numerator_id = NR.Numerator_id
					where NR.NumeratorRezerv_From <= :Numerator_Num
					and (NR.NumeratorRezerv_To >= :Numerator_Num or NR.NumeratorRezerv_To is null)
					and N.Numerator_Num <= :Numerator_Num
					and NR.Numerator_id = :Numerator_id
				";
				//echo getDebugSQL($query_check_numerator,$params_upd_numerator);die;
				$result_check_numerator = $this->db->query($query_check_numerator,$params_upd_numerator);

				if(is_object($result_check_numerator))
				{
					$result_check_numerator = $result_check_numerator->result('array');
					if(is_array($result_check_numerator) && count($result_check_numerator) > 0)
					{
						$query_upd_numerator = "
							update Numerator set Numerator_Num = :Numerator_Num where Numerator_id = :Numerator_id
						";
						$this->db->query($query_upd_numerator,$params_upd_numerator);
					}
				}
			}

			if($data['EvnPrescrMse_id']){
				//сохраним значение EvnVK в направление на МСЭ
				// в направление на МСЭ должен сохранится идентификатор с наибольшей датой #138131

				$params = array();
				$setParamVK = false;
				$sql = "SELECT coalesce(EVK.EvnVK_id, 0) as \"EvnVK_id\",
                               coalesce(EPM.EvnVK_id, 0) as \"mseEvnVK_id\",
                               EPVK.EvnPrescrMse_id as \"EvnPrescrMse_id\",
                               to_char(EVK.EvnVK_setDT, 'YYYY-MM-DD') as \"EvnVK_setDT\",
                               to_char(EVKmse.EvnVK_setDT, 'YYYY-MM-DD') as \"mseEvnVK_setDT\",
                               EPVK.EvnPrescrVK_id as \"EvnPrescrVK_id\",
                               EPM.EvnStatus_id as \"EvnStatus_id\"
                        FROM v_EvnPrescrVK EPVK
                             left join v_EvnPrescrMse EPM on EPM.EvnPrescrMse_id = EPVK.EvnPrescrMse_id
                             left join v_EvnVK EVKmse on EVKmse.EvnVK_id = EPM.EvnVK_id
                             left join v_EvnVK EVK on EVK.EvnPrescrVK_id = EPVK.EvnPrescrVK_id
                        WHERE EPVK.EvnPrescrMse_id = :EvnPrescrMse_id
                        ORDER BY EVK.EvnVK_setDT DESC
                        LIMIT 1";
				$res = $this->dbmodel->getFirstRowFromQuery($sql, $data);

				if(empty($res['mseEvnVK_id'])){
					$setParamVK = $EvnVK_id;
				}else if($res && count($res)>0 && $res['EvnStatus_id'] == 27 && $res['mseEvnVK_id']!=$res['EvnVK_id']){
					if(empty($res['EvnVK_setDT']) || strtotime($res['EvnVK_setDT'])>strtotime($res['mseEvnVK_setDT']) ){
						$setParamVK = $res['EvnVK_id'];
					}
				}

				if($setParamVK){
					$params['EvnPrescrMse_id'] = $data['EvnPrescrMse_id'];
					$params['EvnVK_id'] = $setParamVK;
					$params['pmUser_id'] = $data['pmUser_id'];
					$q = "
						update EvnPrescrMse
						set
                        	EvnVK_id = :EvnVK_id
                        where Evn_id = :EvnPrescrMse_id
                        RETURNING :EvnPrescrMse_id AS \"RequestData_id\", null AS \"Error_Code\", NULL AS \"Error_Msg\";
					 ";
					$res = $this->db->query($q, $params, false);
				}
			}

			$this->commitTransaction();

			if (!empty($data['EvnPrescrMse_id'])) {
				$this->load->model('ApprovalList_model');
				$this->ApprovalList_model->saveApprovalList(array(
					'ApprovalList_ObjectName' => 'EvnPrescrMse',
					'ApprovalList_ObjectId' => $data['EvnPrescrMse_id'],
					'pmUser_id' => $data['pmUser_id']
				));
			}

			return $result;
		}
		else {
			return false;
		}
	}

	/**
	 *	Сохранение
	 */
	function savePalliatEvnVK($data) {

		if ($data['isPalliat'] != 1) return false;

		$data['PalliatEvnVK_id'] = $this->getFirstResultFromQuery("
			select PalliatEvnVK_id as \"PalliatEvnVK_id\"
			from PalliatEvnVK
			where EvnVK_id = ?
		", [$data['EvnVK_id']]);

		if (empty($data['PalliatEvnVK_id'])) {
			$procedure = 'p_PalliatEvnVK_ins';
			$data['PalliatEvnVK_id'] = null;
		} else {
			$procedure = 'p_PalliatEvnVK_upd';
		}

		$sql = "SELECT
                    palliatevnvk_id as \"PalliatEvnVK_id\",
                    error_code as \"Error_Code\",
                    error_message as \"Error_Msg\"
                FROM dbo.{$procedure}(
                    PalliatEvnVK_id := :PalliatEvnVK_id,
                    EvnVK_id := :EvnVK_id,
                    PalliatEvnVK_IsPMP := :PalliatEvnVK_IsPMP,
                    PalliativeType_id := :PalliativeType_id,
                    PalliatEvnVK_IsIVL := :PalliatEvnVK_IsIVL,
                    PalliatEvnVK_IsSpecMedHepl := :PalliatEvnVK_IsSpecMedHepl,
                    PalliatEvnVK_VolumeMedHepl := :PalliatEvnVK_VolumeMedHepl,
                    ConditMedCareType_id := :ConditMedCareType_id,
                    PalliatEvnVK_IsSurvey := :PalliatEvnVK_IsSurvey,
                    PalliatEvnVK_VolumeSurvey := :PalliatEvnVK_VolumeSurvey,
                    PalliatEvnVK_DirSocialProt := :PalliatEvnVK_DirSocialProt,
                    PalliatEvnVK_IsInfoDiag := :PalliatEvnVK_IsInfoDiag,
                    PalliatEvnVK_TextTIR := :PalliatEvnVK_TextTIR,
                    pmUser_id := :pmUser_id
                );
		";

		$result = $this->queryResult($sql, $data);
		$data['PalliatEvnVK_id'] = $result[0]['PalliatEvnVK_id'];

		$this->savePalliatEvnVKMainSyndromeLink($data);
		$this->savePalliatEvnVKTechnicInstrumRehabLink($data);
		$this->savePalliatFamilyCare($data);
	}

	/**
	 *	Сохранение
	 */
	function savePalliatEvnVKMainSyndromeLink($data) {
		$tmp = $this->queryList("
			select
				PalliatEvnVKMainSyndromeLink_id as \"PalliatEvnVKMainSyndromeLink_id\"
			from PalliatEvnVKMainSyndromeLink
			where PalliatEvnVK_id = ?
		", [$data['PalliatEvnVK_id']]);
		foreach($tmp as $row) {
			$this->db->query("
                SELECT 
                    error_code as \"Error_Code\", 
                    error_message AS \"Error_Msg\"
                FROM dbo.p_PalliatEvnVKMainSyndromeLink_del(
                    PalliatEvnVKMainSyndromeLink_id := ?
                );
			", array($row));
		}

		if(empty($data['PalliatEvnVKMainSyndrome'])) return false;
		$data['PalliatEvnVKMainSyndrome'] = explode(',', $data['PalliatEvnVKMainSyndrome']);

		foreach($data['PalliatEvnVKMainSyndrome'] as $row) {
			$sql = "
                SELECT 
                	PalliatEvnVKMainSyndromeLink_id as \"PalliatEvnVKMainSyndromeLink_id\", 
                    Error_Code as \"Error_Code\", 
                    Error_Message as \"Error_Msg\"
                FROM dbo.p_PalliatEvnVKMainSyndromeLink_ins(
					PalliatEvnVKMainSyndromeLink_id := :PalliatEvnVKMainSyndromeLink_id,
					PalliatEvnVK_id := :PalliatEvnVK_id,
					MainSyndrome_id := :MainSyndrome_id,
					pmUser_id := :pmUser_id
                );
			";

			$this->queryResult($sql, array(
				'PalliatEvnVKMainSyndromeLink_id' => null,
				'PalliatEvnVK_id' => $data['PalliatEvnVK_id'],
				'MainSyndrome_id' => $row,
				'pmUser_id' => $data['pmUser_id'],
			));
		}
	}

	/**
	 *	Сохранение
	 */
	function savePalliatEvnVKTechnicInstrumRehabLink($data) {
		$tmp = $this->queryList("
			select
				PalliatEvnVKTechnicInstrumRehabLink_id as \"PalliatEvnVKTechnicInstrumRehabLink_id\"
			from PalliatEvnVKTechnicInstrumRehabLink
			where PalliatEvnVK_id = ?
		", [$data['PalliatEvnVK_id']]);
		foreach($tmp as $row) {
			$this->db->query("
				select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
                FROM dbo.p_PalliatEvnVKTechnicInstrumRehabLink_del(
					PalliatEvnVKTechnicInstrumRehabLink_id := ?
                );", array($row));
		}

		if(empty($data['PalliatEvnVKTechnicInstrumRehab'])) return false;
		$data['PalliatEvnVKTechnicInstrumRehab'] = explode(',', $data['PalliatEvnVKTechnicInstrumRehab']);

		foreach($data['PalliatEvnVKTechnicInstrumRehab'] as $row) {
			if ($row > 9) continue;
			$sql = "
                select
                	PalliatEvnVKTechnicInstrumRehabLink_id as \"PalliatEvnVKTechnicInstrumRehabLink_id\",
                	Error_Code as \"Error_Code\",
                	Error_Message as \"Error_Msg\"
                FROM dbo.p_PalliatEvnVKTechnicInstrumRehabLink_ins(
                    PalliatEvnVKTechnicInstrumRehabLink_id := :PalliatEvnVKTechnicInstrumRehabLink_id,
                    PalliatEvnVK_id := :PalliatEvnVK_id,
                    TechnicInstrumRehab_id := :TechnicInstrumRehab_id,
                    pmUser_id := :pmUser_id
                );";
			$this->queryResult($sql, array(
				'PalliatEvnVKTechnicInstrumRehabLink_id' => null,
				'PalliatEvnVK_id' => $data['PalliatEvnVK_id'],
				'TechnicInstrumRehab_id' => $row,
				'pmUser_id' => $data['pmUser_id'],
			));
		}
	}

	/**
	 *	Сохранение
	 */
	function savePalliatFamilyCare($data) {

		$pfc_list = array();
		$PalliatFamilyCare = $this->queryList("
			select
				PalliatFamilyCare_id  as \"PalliatFamilyCare_id\"
			from PalliatFamilyCare
			where EvnVK_id = ?
		", array($data['EvnVK_id']));
		$data['PalliatFamilyCare'] = (array)$data['PalliatFamilyCare'];

		// добавляем/обновляем
		foreach($data['PalliatFamilyCare'] as $pfc) {
			$procedure = empty($pfc->PalliatFamilyCare_id) ? 'p_PalliatFamilyCare_ins' : 'p_PalliatFamilyCare_upd';
			$sql = "
				select 
                	PalliatFamilyCare_id as \"PalliatFamilyCare_id\", 
                    Error_Code as \"Error_Code\", 
                    Error_Message as \"Error_Msg\"
                FROM dbo.{$procedure}(
					PalliatFamilyCare_id := :PalliatFamilyCare_id,
					FamilyRelationType_id := :FamilyRelationType_id,
					PalliatFamilyCare_Age := :PalliatFamilyCare_Age,
					PalliatFamilyCare_Phone := :PalliatFamilyCare_Phone,
					MorbusPalliat_id := :MorbusPalliat_id,
					EvnVK_id := :EvnVK_id,
					pmUser_id := :pmUser_id
                );
			";
			$this->queryResult($sql, array(
				'PalliatFamilyCare_id' => $pfc->PalliatFamilyCare_id,
				'FamilyRelationType_id' => $pfc->FamilyRelationType_id,
				'PalliatFamilyCare_Age' => !empty($pfc->PalliatFamilyCare_Age) ? $pfc->PalliatFamilyCare_Age : null,
				'PalliatFamilyCare_Phone' => $pfc->PalliatFamilyCare_Phone,
				'MorbusPalliat_id' => null,
				'EvnVK_id' => $data['EvnVK_id'],
				'pmUser_id' => $data['pmUser_id'],
			));

			if (!empty($pfc->PalliatFamilyCare_id)) {
				$pfc_list[] = $pfc->PalliatFamilyCare_id;
			}
		}

		// то, что было в БД, но уже нет на форме - удаляем
		$delpfc = array_diff($PalliatFamilyCare, $pfc_list);
		foreach($delpfc as $pfc) {
			$sql = "
                select 
                	Error_Code as \"Error_Code\", 
                    Error_Message as \"Error_Msg\"
				FROM dbo.p_PalliatFamilyCare_del(
					PalliatFamilyCare_id := :PalliatFamilyCare_id
				);
			";

			$this->queryResult($sql, array(
				'PalliatFamilyCare_id' => $pfc,
			));
		}
	}

	/**
	 *	Сохранение диагнозов
	 */
	function saveEvnVKDiag($data)
	{

		$SopDiagList = array();
		$SopDiagArr = array();
		//var_dump($data['SopDiagList']);die;
		foreach($data['SopDiagList'] as $diag_id) {
			if (!empty($diag_id[0]) && !is_numeric($diag_id[0])) {
				throw new Exception('Некоректный идентификатор сопутствующего заболевания: ' . $diag_id[0], 500);
			}
			if (in_array($diag_id[0], $SopDiagArr)) {
				throw new Exception('Ввод одинаковых сопутствующих заболеваний не допускается', 500);
			}
			$SopDiagArr[] = $diag_id[0];
			$SopDiagList[] = $diag_id;
		}
		$OslDiagList = array();
		foreach($data['OslDiagList'] as $diag_id) {
			if (!empty($diag_id) && !is_numeric($diag_id)) {
				throw new Exception('Некоректный идентификатор осложнения основного заболевания: ' . $diag_id, 500);
			}
			if (in_array($diag_id, $OslDiagList)) {
				throw new Exception('Ввод одинаковых осложнений основного заболевания не допускается', 500);
			}
			$OslDiagList[] = $diag_id;
		}

		$this->deleteEvnVKDiag($data);

		foreach($data['SopDiagList'] as $diag_id) {
			$resp = $this->getFirstRowFromQuery("
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				FROM dbo.p_EvnVKDiagLink_ins(
					EvnVK_id := :EvnVK_id,
					Diag_id := :Diag_id,
					EvnVKDiagLink_DescriptDiag := :EvnVKDiagLink_DescriptDiag,
					Diag_oid := :Diag_oid,
					pmUser_id := :pmUser_id
                );
			", array(
				'EvnVK_id' => $data['EvnVK_id'],
				'pmUser_id' => $data['pmUser_id'],
				'Diag_id' => $diag_id[0],
				'EvnVKDiagLink_DescriptDiag' => $diag_id[1],
				'Diag_oid' => null
			));

			if ( $resp === false || !is_array($resp) || count($resp) == 0 ) {
				throw new Exception('Ошибка при добавлении сопутствующего заболевания', 500);
			}
			else if ( !empty($resp['Error_Msg']) ) {
				throw new Exception($resp['Error_Msg'], 500);
			}
		}

		foreach($data['OslDiagList'] as $diag_id) {
			$resp = $this->getFirstRowFromQuery("
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				FROM dbo.p_EvnVKDiagLink_ins(
					EvnVK_id := :EvnVK_id,
					Diag_id := :Diag_id,
					Diag_oid := :Diag_oid,
					pmUser_id := :pmUser_id
                );
			", array(
				'EvnVK_id' => $data['EvnVK_id'],
				'pmUser_id' => $data['pmUser_id'],
				'Diag_id' => null,
				'Diag_oid' => $diag_id
			));

			if ( $resp === false || !is_array($resp) || count($resp) == 0 ) {
				throw new Exception('Ошибка при добавлении осложнения основного заболевания', 500);
			}
			else if ( !empty($resp['Error_Msg']) ) {
				throw new Exception($resp['Error_Msg'], 500);
			}
		}
	}

	/**
	 *	Удаление диагнозов
	 */
	function deleteEvnVKDiag($data)
	{
		$resp = $this->queryResult("
			select EvnVKDiagLink_id  as \"EvnVKDiagLink_id\" from EvnVKDiagLink where EvnVK_id = :EvnVK_id
		", $data);

		foreach($resp as $item) {
			$this->queryResult("
				select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				FROM dbo.p_EvnVKDiagLink_del(
					EvnVKDiagLink_id := :EvnVKDiagLink_id)
			", array(
				'EvnVKDiagLink_id' => $item['EvnVKDiagLink_id']
			));
		}
	}

	/**
	 *	Сохранение диагнозов
	 */
	function saveEvnVKDiagOne($data)
	{
		$field = $data['DiagType'] == 'sop' ? 'Diag_id' : 'Diag_oid';
		$check = $this->getFirstResultFromQuery("select EvnVKDiagLink_id  as \"EvnVKDiagLink_id\" from EvnVKDiagLink where EvnVK_id = :EvnVK_id and {$field} = :Diag_id ", $data);

		if (!empty($check)) {
			if ($data['DiagType'] == 'sop') {
				throw new Exception('Ввод одинаковых сопутствующих заболеваний не допускается', 500);
			} else {
				throw new Exception('Ввод одинаковых осложнений основного заболевания не допускается', 500);
			}
		}

		$params = array(
			'EvnVK_id' => $data['EvnVK_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Diag_id' => null,
			'Diag_oid' => null
		);

		$params[$field] = $data['Diag_id'];

		return $this->queryResult("
			select
            	Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from dbo.p_EvnVKDiagLink_ins(
				EvnVKDiagLink_id := null,
				EvnVK_id := :EvnVK_id,
				Diag_id := :Diag_id,
				Diag_oid := :Diag_oid,
				pmUser_id := :pmUser_id
			);
		", $params);

	}

	/**
	 *	Удаление диагнозов
	 */
	function deleteEvnVKDiagOne($data)
	{
		return $this->queryResult("
			select 
            	Error_Code as \"Error_Code\", 
                Error_Message as \"Error_Msg\"
			from dbo.p_EvnVKDiagLink_del(EvnVKDiagLink_id := :EvnVKDiagLink_id);
		", array(
			'EvnVKDiagLink_id' => $data['EvnVKDiagLink_id']
		));
	}

	/**
	 *	Удаление
	 */
	function deleteEvnVK($data)
	{
		$this->deleteEvnVKDiag($data);

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo.p_EvnVK_del(
				EvnVK_id := :EvnVK_id,
				pmUser_id := :pmUser_id
			);
		";

		$queryParams = array(
			'EvnVK_id' => $data['EvnVK_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Родители и дети для данной палки
	 */
	function getParentsAndChildsforGivenStick($data, $searchType)
	{
		if($searchType == 'parents') {
			$query = "
				SELECT
					coalesce(EvnStick_prid, 0) as \"EvnStick_prid\"
				FROM
					v_EvnStick
				WHERE
					EvnStick_id = :EvnStick_id
			";
		} else {
			$query = "
				SELECT
					coalesce(EvnStick_id, 0) as \"EvnStick_id\"
				FROM
					v_EvnStick
				WHERE
					EvnStick_prid = :EvnStick_id
			";
		}

		$result = $this->db->query($query, array(
			'EvnStick_id' => $data['EvnStick_id']
		));

		if ( is_object($result) )
		{
			$result->result('array');
			if(isset($result->result_array[0]))
			{
				return $result->result_array[0];
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 *	Похабщина какая-то. Проверка, входит ли палка куда-то (VK)
	 */
	function CheckStickToVK($id)
	{
		$query = "
			SELECT
				EvnStickBase_id as \"EvnStickBase_id\"
			FROM
				v_EvnVK
			WHERE
				EvnStickBase_id = ?
		";

		$result = $this->db->query($query, array($id));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Метод
	 */
	function getEvnVKExpert($data)
	{
		$filter = '';
		if (!empty($data['newEvnVK'])) {
			$query = "
				SELECT dbo.p_EvnVKExpert_copy( :EvnVK_id, :Lpu_id, :pmUser_id) AS \"p_EvnVKExpert_copy\";
			";
			//echo getDebugSql($query, $queryParams);
			$res = $this->db->query($query, $data);
			if (!is_object($res) ) {
				return false;
			}
		}

		if(isset($data['EvnVK_id'])) {
			$filter .= ' and EVK.EvnVK_id = :EvnVK_id';
		}

		$query = "
			select
				EVKE.EvnVKExpert_id as \"EvnVKExpert_id\",
				EVKE.EvnVK_id as \"EvnVK_id\",
				EVK.MedService_id as \"MedService_id\",
				EVKE.MedServiceMedPersonal_id as \"MedServiceMedPersonal_id\",
				EVKE.MedStaffFact_id as \"MedStaffFact_id\",
				MP.Person_Fio as \"MF_Person_FIO\",
				EMSF.ExpertMedStaffType_Name as \"ExpertMedStaffType_Name\",
				EVKE.MedServiceMedPersonal_id as \"MedServiceMedPersonal_id\",
				case when EVKE.ExpertMedStaffType_id = 1 then 2 else 1 end as \"EvnVKExpert_IsChairman\",
				EVKE.ExpertMedStaffType_id as \"ExpertMedStaffType_id\",
				coalesce(LS.LpuSection_Name, '')
					|| coalesce(', ' || pm.PostMed_Name, '')
					|| coalesce(', ' || to_char( msf.WorkData_begDate, 'DD.MM.YYYY'), '')
					|| ' - '
					|| coalesce(to_char(msf.WorkData_endDate, 'DD.MM.YYYY'), '') as \"MedStaffFact_Info\"
			from
				v_EvnVKExpert EVKE
				left join v_ExpertMedStaffType EMSF on EMSF.ExpertMedStaffType_id = EVKE.ExpertMedStaffType_id
				left join v_EvnVK EVK on EVK.EvnVK_id = EVKE.EvnVK_id
				left join v_MedServiceMedPersonal MSMP on MSMP.MedServiceMedPersonal_id = EVKE.MedServiceMedPersonal_id
				left join v_MedPersonal MP on MP.MedPersonal_id = MSMP.MedPersonal_id
				left join v_MedStaffFact MSF on MSF.MedStaffFact_id = EVKE.MedStaffFact_id
				left join v_LpuSection LS on LS.LpuSection_id = MSF.LpuSection_id
				left join v_PostMed pm on pm.PostMed_id = msf.Post_id
			where
				EVK.Lpu_id = :Lpu_id
				and MP.Lpu_id = :Lpu_id
				{$filter}
		";
		//echo getDebugSQL($query, $queryParams);

		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}


	/**
	 *	Проверка на существование председателя ВК в списке или данного врача в списке
	 */
	function checkExistEvnVKExpert($data)
	{
		// Сначала проверим прикреплен ли к данному протоколу этот врач
		$query1 = "
			select
				EvnVKExpert_id as \"EvnVKExpert_id\"
			from
				v_EvnVKExpert
			where
				EvnVK_id = :EvnVK_id
		";
        $query2 = ' and MedServiceMedPersonal_id = :MedServiceMedPersonal_id and EvnVKExpert_id <> COALESCE(:EvnVKExpert_id::bigint, 0)';
		$res = $this->db->query($query1 . $query2.' limit 1', $data);
		if ( !is_object($res) )
			return false;

		$response = $res->result('array');
		if ( count($response) > 0 ) {
			return array(
				'Error_Msg' => 'Этот врач уже указан в списке врачей экспертов протокола!'
			);
		}

		// Если добавляют председателя ВК, то проверяем прикреплен ли председатель ВК к данному протоколу поскольку председатель ВК у протокола может быть только 1
		if ( $data['ExpertMedStaffType_id'] == 1 ) {
			$query = $query1 . " and ExpertMedStaffType_id = :ExpertMedStaffType_id and EvnVKExpert_id <> COALESCE(cast(:EvnVKExpert_id as bigint), 0) limit 1";
			//echo getDebugSQL($query, $data); exit();
			$res = $this->db->query($query, $data);
			if ( !is_object($res) )
				return false;

			$res = $res->result('array');
			//print_r($res); exit();
			if(count($res)>0) {
				return array(
					'Error_Msg' => 'Председатель ВК уже указан!'
				);
			}
		}
		return true;
	}


	/**
	 *	Еще одно сохранение
	 */
	function saveEvnVKExpert($data)
	{
		$check = $this->checkExistEvnVKExpert($data);
		if(!$check) {
			return array(
				array(
					'Error_Msg' => toUTF('Ошибка БД!'),
					'success' => false
				)
			);
		}
		if(is_array($check) && !empty($check['Error_Msg'])) {
			return array(
				array(
					'Error_Msg' => $check['Error_Msg'],
					'success' => false
				)
			);
		}

		if( !empty($data['EvnVKExpert_id']) )
			$action = 'upd';
		else
			$action = 'ins';

		$query = "
			select
			    EvnVKExpert_id as \"EvnVKExpert_id\",
			    Error_Code as \"Error_Code\", 
			    Error_Message as \"Error_Msg\"
			from dbo.p_EvnVKExpert_{$action}(
				EvnVKExpert_id := :EvnVKExpert_id,
				EvnVK_id := :EvnVK_id,
				pmUser_id := :pmUser_id,
				MedServiceMedPersonal_id := :MedServiceMedPersonal_id,
				MedStaffFact_id := :MedStaffFact_id,
				ExpertMedStaffType_id := :ExpertMedStaffType_id
			);
		";
		//echo getDebugSQL($query, $queryParams);
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	И еще одно удаление
	 */
	function deleteEvnVKExpert($data)
	{
		$query = "
            select 
                Error_Code as \"Error_Code\", 
                Error_Message as \"Error_Msg\"
			from dbo.p_EvnVKExpert_del(EvnVKExpert_id := :EvnVKExpert_id);
		";

		//echo getDebugSQL($query, $data);
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Печать
	 */
	function printEvnVK($data)
	{
		$query = "
			select
				EVK.EvnVK_NumProtocol as \"EvnVK_NumProtocol\",
				to_char (cast(EVK.EvnVK_setDT as timestamp), 'dd.mm.yyyy') as \"EvnVK_setDT\",
				PA.Person_Fio as \"Person_Fio\",
				to_char (cast(PA.Person_BirthDay as timestamp), 'dd.mm.yyyy') as \"Person_BirthDay\",
				case when PA.Sex_id = 1 then 'М' else 'Ж' end as \"Person_Sex\",
				coalesce(UAddr.Address_Address, PAddr.Address_Address) as \"Person_Address\",
				EvnVKPatientStatusTypeNameStr.PatientStatusType_Name as \"PatientStatusType_Name\",
				EVK.EvnVK_Prof as \"EvnVK_Prof\",
				CTT.CauseTreatmentType_Name as \"CauseTreatmentType_Name\",
				D.diag_FullName as \"diag1\",
				DP.diag_FullName as \"diag2\",
				ENT.ExpertiseNameType_Name as \"ExpertiseNameType_Name\",
				EET.ExpertiseEventType_Name as \"ExpertiseEventType_Name\",
				ENST.ExpertiseNameSubjectType_Name as \"ExpertiseNameSubjectType_Name\",
				(case when EVK.EvnStickBase_id is not null then
				(coalesce(ES.EvnStick_Ser, '')||' '||coalesce(ES.EvnStick_Num, '')||
					(case when (ES.EvnStick_begDate IS NOT NULL) then ' выдан: '||to_char (cast(ES.EvnStick_begDate as timestamp), 'dd.mm.yyyy') else '' end)||
					(case when (ES.EvnStick_endDate IS NOT NULL) then ' по '||to_char (cast(ES.EvnStick_endDate as timestamp), 'dd.mm.yyyy') else '' end)
				) else  EVK.EvnVK_LVN||'(ручной ввод)' end) as \"EvnStick_all\",
				(case when EVK.EvnStickBase_id is not null then
				('с ' || to_char (cast(ESWR.evnStickWorkRelease_begDT as timestamp), 'dd.mm.yyyy') ||
					(case when ESWR.EvnStickWorkRelease_endDT is not null
						then ' по ' || to_char (cast(ESWR.EvnStickWorkRelease_endDT as timestamp), 'dd.mm.yyyy')
						else ''
					end)
				) else EvnVK_WorkReleasePeriod||'(ручной ввод)' end)as \"EvnStickWorkRelease_all\",
				EvnVK_WorkReleasePeriod as \"EvnVK_WorkReleasePeriod\",
				EVK.EvnVK_ExpertiseStickNumber as \"EvnVK_ExpertiseStickNumber\",
				EVK.EvnVK_StickPeriod as \"EvnVK_StickPeriod\",
				EVK.EvnVK_StickDuration as \"EvnVK_StickDuration\",
				to_char (cast(EPM.EvnPrescrMse_setDT as timestamp), 'dd.mm.yyyy') as \"EvnVK_DirectionDate\",
				to_char (cast(EM.EvnMse_setDT as timestamp), 'dd.mm.yyyy') as \"EvnVK_ConclusionDate\",
				to_char (cast(EM.EvnMse_ReExamDate as timestamp), 'dd.mm.yyyy') as \"EvnVK_ConclusionPeriodDate\",
				case when EM.InvalidGroupType_id = 1 then EM.EvnMse_InvalidCauseDeni else IGT.InvalidGroupType_Name	end as \"EvnVK_ConclusionDescr\",
				EM.EvnMse_InvalidCause as \"EvnVK_AddInfo\",
				EVK.EvnVK_ExpertDescr as \"EvnVK_ExpertDescr\",
				EVK.EvnVK_AberrationDescr as \"EvnVK_AberrationDescr\",
				EVK.EvnVK_ErrorsDescr as \"EvnVK_ErrorsDescr\",
				EVK.EvnVK_ResultDescr as \"EvnVK_ResultDescr\",
				EVK.EvnVK_DecisionVK as \"EvnVK_DecisionVK\",
				case EVK.EvnVK_isAberration when 2 then 'да' else 'нет' end as \"EvnVK_isAberration\",
				case EVK.EvnVK_isErrors when 2 then 'да' else 'нет' end as \"EvnVK_isErrors\",
				case EVK.EvnVK_isResult when 2 then 'да' else 'нет' end as \"EvnVK_isResult\"
			from v_EvnVK EVK
				left join v_Person_all PA on PA.Person_id = EVK.Person_id
					and PA.PersonEvn_id = EVK.PersonEvn_id
				left join Address UAddr on UAddr.Address_id = PA.UAddress_id
				left join Address PAddr on PAddr.Address_id = PA.PAddress_id
				left join PatientStatusType PST on PST.PatientStatusType_id = EVK.PatientStatusType_id
				left join v_CauseTreatmentType CTT on CTT.CauseTreatmentType_id = EVK.CauseTreatmentType_id
				left join v_Diag D on D.Diag_id = EVK.Diag_id
				left join v_Diag DP on DP.Diag_id = EVK.Diag_sid
				left join ExpertiseNameType ENT on ENT.ExpertiseNameType_id = EVK.ExpertiseNameType_id
				left join ExpertiseEventType EET on EET.ExpertiseEventType_id = EVK.ExpertiseEventType_id
				left join v_ExpertiseNameSubjectType ENST on ENST.ExpertiseNameSubjectType_id = EVK.ExpertiseNameSubjectType_id
				left join v_EvnStick ES on ES.EvnStick_id = EVK.EvnStickBase_id
				left join v_EvnStickWorkRelease ESWR on ESWR.EvnStickWorkRelease_id = EVK.EvnStickWorkRelease_id
				left join v_EvnPrescrMse EPM on EPM.EvnVK_id = EVK.EvnVK_id
				left join v_EvnMse EM on EM.EvnPrescrMse_id = EPM.EvnPrescrMse_id
				left join v_InvalidGroupType IGT on IGT.InvalidGroupType_id = EM.InvalidGroupType_id
				left join lateral(
						select (
							select string_agg(PST.PatientStatusType_Name,',') AS PatientStatusType_Name
							from v_EvnVKPatientStatusType EVKPST
							inner join v_PatientStatusType PST on PST.PatientStatusType_id = EVKPST.PatientStatusType_id
							where EVKPST.EvnVK_id = EVK.EvnVK_id
                            limit 1
						) as PatientStatusType_Name
				) as EvnVKPatientStatusTypeNameStr on true
			where
				EVK.EvnVK_id = :EvnVK_id
			limit 1
		";
		$res = $this->db->query($query, $data);

		if ( !is_object($res) )
			return false;

		$res = $res->result('array');
		$result = $res[0];
		$result['vkexperts'] = array();
		$result['vkchairman'] = '';

		$query = "							
			select
				EVKE.ExpertMedStaffType_id as \"ExpertMedStaffType_id\",
				MP.Person_Fio as \"Person_Fio\"
			from
				v_EvnVKExpert EVKE
				left join MedServiceMedPersonal MSMP  on MSMP.MedServiceMedPersonal_id = EVKE.MedServiceMedPersonal_id
				LEFT JOIN LATERAL (
					select t.MedPersonal_id,t.Person_Fio
					from v_MedPersonal t 
					where t.MedPersonal_id = MSMP.MedPersonal_id
					order by t.WorkData_begDate
                    limit 1
				) MP ON true
			where
				EVKE.EvnVK_id = :EvnVK_id
				and EVKE.MedServiceMedPersonal_id is not null
		";

		$res = $this->db->query($query, $data);
		if ( !is_object($res) )
			return false;

		$res = $res->result('array');
		foreach($res as $r) {
			if($r['ExpertMedStaffType_id'] == 1)
				$result['vkchairman'] = $r['Person_Fio'];
			else
				$result['vkexperts'][] = array('MP_Person_Fio' => $r['Person_Fio']);
		}

		return $result;
	}

	/**
	 * Печать
	 */
	function printEvnVK_Perm($data)
	{
		$query = "
			select
				EVK.EvnVK_NumProtocol as \"EvnVK_NumProtocol\",
				to_char (cast(EVK.EvnVK_setDT as timestamp), 'dd.mm.yyyy') as \"EvnVK_setDT\",
				PA.Person_Fio as \"Person_Fio\",
				to_char (cast(PA.Person_BirthDay as timestamp), 'dd.mm.yyyy') as \"Person_BirthDay\",
				case when PA.Sex_id = 1 then 'М' else 'Ж' end as \"Person_Sex\",
				coalesce(UAddr.Address_Address, PAddr.Address_Address) as \"Person_Address\",
				PST.PatientStatusType_Name as \"PatientStatusType_Name\",
				EVK.EvnVK_Prof as \"EvnVK_Prof\",
				CTT.CauseTreatmentType_Name as \"CauseTreatmentType_Name\",
				D.Diag_Name as \"Diag1_Name\",
				D.Diag_Code as \"Diag1_Code\",
				substring(Diag2.Diag2_Name, 1, length(Diag2.Diag2_Name)-1) as \"Diag2_Name\",
				substring(Diag3.Diag3_Name, 1, length(Diag3.Diag3_Name)-1) as \"Diag3_Name\",
				ENT.ExpertiseNameType_Name as \"ExpertiseNameType_Name\",
				EET.ExpertiseEventType_Name as \"ExpertiseEventType_Name\",
				ENST.ExpertiseNameSubjectType_Name as \"ExpertiseNameSubjectType_Name\",
				(case when EVK.EvnStickBase_id is not null then
					(coalesce(ES.EvnStick_Ser, '')||' '||coalesce(ES.EvnStick_Num, '')||
						(case when (ES.EvnStick_begDate IS NOT NULL) then ' выдан: '||to_char (cast(ES.EvnStick_begDate as timestamp), 'dd.mm.yyyy') else '' end)||
						(case when (ES.EvnStick_endDate IS NOT NULL) then ' по '||to_char (cast(ES.EvnStick_endDate as timestamp), 'dd.mm.yyyy') else '' end)
					)
				else  EVK.EvnVK_LVN end) as \"EvnStick_all\",
				('с ' || to_char (cast(ESWR.evnStickWorkRelease_begDT as timestamp), 'dd.mm.yyyy') ||
					(case when ESWR.EvnStickWorkRelease_endDT is not null
						then ' по ' || to_char (cast(ESWR.EvnStickWorkRelease_endDT as timestamp), 'dd.mm.yyyy')
						else ''
					end)
				) as \"EvnStickWorkRelease_all\",
				coalesce(ES.EvnStick_Ser, '') as \"EvnStick_Ser\",
				coalesce(ES.EvnStick_Num, '') as \"EvnStick_Num\",
				to_char (ES.EvnStick_begDate, 'dd.mm.yyyy') as \"EvnStick_begDate\",
				to_char (ES.EvnStick_endDate, 'dd.mm.yyyy') as \"EvnStick_endDate\",
				to_char (ESWR.EvnStickWorkRelease_begDT, 'dd.mm.yyyy') as \"EvnStickWorkRelease_begDate\",
				to_char (ESWR.EvnStickWorkRelease_endDT, 'dd.mm.yyyy') as \"EvnStickWorkRelease_endDate\",
				EVK.EvnVK_ExpertiseStickNumber as \"EvnVK_ExpertiseStickNumber\",
				EVK.EvnVK_StickPeriod as \"EvnVK_StickPeriod\",
				EVK.EvnVK_StickDuration as \"EvnVK_StickDuration\",
				to_char (cast(EPM.EvnPrescrMse_setDT as timestamp), 'dd.mm.yyyy') as \"EvnVK_DirectionDate\",
				to_char (cast(EM.EvnMse_setDT as timestamp), 'dd.mm.yyyy') as \"EvnVK_ConclusionDate\",
				to_char (cast(EM.EvnMse_ReExamDate as timestamp), 'dd.mm.yyyy') as \"EvnVK_ConclusionPeriodDate\",
				case when EM.InvalidGroupType_id = 1 then EM.EvnMse_InvalidCauseDeni else IGT.InvalidGroupType_Name	end as \"EvnVK_ConclusionDescr\",
				EM.EvnMse_InvalidCause as \"EvnVK_AddInfo\",
				EVK.EvnVK_AberrationDescr as \"EvnVK_AberrationDescr\",
				EVK.EvnVK_ErrorsDescr as \"EvnVK_ErrorsDescr\",
				EVK.EvnVK_ResultDescr as \"EvnVK_ResultDescr\",
				EVK.EvnVK_DecisionVK as \"EvnVK_DecisionVK\",
				L.Lpu_Name as \"Lpu_Name\",
				Job.Org_Name as \"Job_Name\",
				Post.Post_Name as \"Post_Name\",
				EVK.EvnVK_MainDisease as \"EvnVK_MainDisease\"
			from
				v_EvnVK EVK
				left join v_Person_all PA on PA.Person_id = EVK.Person_id
					and PA.PersonEvn_id = EVK.PersonEvn_id
				left join Address UAddr on UAddr.Address_id = PA.UAddress_id
				left join Address PAddr on PAddr.Address_id = PA.PAddress_id
				left join PatientStatusType PST on PST.PatientStatusType_id = EVK.PatientStatusType_id
				left join v_CauseTreatmentType CTT on CTT.CauseTreatmentType_id = EVK.CauseTreatmentType_id
				left join v_Lpu L on L.Lpu_id = EVK.Lpu_id
				left join v_Diag D on D.Diag_id = EVK.Diag_id
				left join ExpertiseNameType ENT on ENT.ExpertiseNameType_id = EVK.ExpertiseNameType_id
				left join ExpertiseEventType EET on EET.ExpertiseEventType_id = EVK.ExpertiseEventType_id
				left join v_ExpertiseNameSubjectType ENST on ENST.ExpertiseNameSubjectType_id = EVK.ExpertiseNameSubjectType_id
				left join v_EvnStick ES on ES.EvnStick_id = EVK.EvnStickBase_id
				left join v_EvnStickWorkRelease ESWR on ESWR.EvnStickWorkRelease_id = EVK.EvnStickWorkRelease_id
				left join v_EvnPrescrMse EPM on EPM.EvnVK_id = EVK.EvnVK_id
				left join v_EvnMse EM on EM.EvnPrescrMse_id = EPM.EvnPrescrMse_id
				left join v_InvalidGroupType IGT on IGT.InvalidGroupType_id = EM.InvalidGroupType_id
				left join v_PersonJob PJob on PJob.Job_id = PA.Job_id
				left join v_Org Job on Job.Org_id = PJob.Org_id
				left join v_Post Post on Post.Post_id = PJob.Post_id
				LEFT JOIN LATERAL (
					Select (
						select string_agg(d.Diag_Name, ', ') as data
						from EvnVKDiagLink dl
						inner join v_Diag d on d.Diag_id = dl.Diag_id
						where dl.EvnVK_id = EVK.EvnVK_id and dl.Diag_id is not null
					) as Diag2_Name
				) as Diag2 ON true
				LEFT JOIN LATERAL (
					select string_agg(d.Diag_Name, ', ') as Diag3_Name
					from EvnVKDiagLink dl
						inner join v_Diag d on d.Diag_id = dl.Diag_oid
					where dl.EvnVK_id = EVK.EvnVK_id and dl.Diag_oid is not null
				) as Diag3 on true
			where
				EVK.EvnVK_id = :EvnVK_id
			limit 1
		";
		$res = $this->db->query($query, $data);

		if ( !is_object($res) )
			return false;

		$res = $res->result('array');
		$result = $res[0];
		$result['vkexperts'] = array();
		$result['vkchairman'] = '';

		$query = "
			select
				EVKE.ExpertMedStaffType_id as \"ExpertMedStaffType_id\",
				MP.Person_Fio as \"Person_Fio\"
			from
				v_EvnVKExpert EVKE
				left join MedServiceMedPersonal MSMP on MSMP.MedServiceMedPersonal_id = EVKE.MedServiceMedPersonal_id
				LEFT JOIN LATERAL (
					select t.MedPersonal_id,t.Person_Fio
					from v_MedPersonal t
					where t.MedPersonal_id = MSMP.MedPersonal_id
					order by t.WorkData_begDate
                    limit 1
				) MP ON true
			where
				EVKE.EvnVK_id = :EvnVK_id
				and EVKE.MedServiceMedPersonal_id is not null
		";

		$res = $this->db->query($query, $data);
		if ( !is_object($res) )
			return false;

		$res = $res->result('array');
		foreach($res as $r) {
			if($r['ExpertMedStaffType_id'] == 1)
				$result['vkchairman'] = $r['Person_Fio'];
			else
				$result['vkexperts'][] = array('MP_Person_Fio' => $r['Person_Fio']);
		}

		$this->load->library('parser');
		$view = 'evn_vk_blank_perm';

		$str1 = wordwrap($result['EvnVK_DecisionVK'], 190, "\n", true);
		$arr = explode("\n", $str1);
		$count = count($arr);
		if ($count < 7) {
			for($i=0; $i<(7-$count); $i++) {
				$arr[] = '&nbsp;';
			}
		}
		$str2 = "";
		foreach ($arr as $s) {
			$str2 .= '<tr><td class="underline">'.$s.'</td></tr>'."\n";
		}
		$result['EvnVK_DecisionVK'] = $str2;

		$printData = array();
		foreach($result as $key => $value) {
			if (is_array($value)) {
				$printData[$key] = $value;
			} else {
				$printData[$key] = empty($value) ? '&nbsp;' : $value;
			}
		}

		$printData['vkexpert1'] = '&nbsp';
		$vkexpert = array_shift($printData['vkexperts']);
		$printData['vkexpert1'] = $vkexpert['MP_Person_Fio'];

		$html = $this->parser->parse($view, $printData, !empty($data['returnString']));
		if (!empty($data['returnString'])) {
			return array('html' => $html);
		} else {
			return array('Error_Msg' => '');
		}
	}

	/**
	 *	Печать всего и вся!
	 */
	function printEvnVK_all($data)
	{
		$filter = '1=1';
		$queryParams = array();
		$queryParams['Lpu_id'] = $data['Lpu_id'];
		array_walk($data, 'ConvertFromUTF8ToWin1251');

		if(!empty($data['ExpertiseDateRange'][0]) && !empty($data['ExpertiseDateRange'][1]))
		{
			$filter .= " and (EVK.EvnVK_setDT >= cast(:exp_startdate as date) and EVK.EvnVK_setDT <= cast(:exp_enddate as date))";
			$queryParams['exp_startdate'] = $data['ExpertiseDateRange'][0];
			$queryParams['exp_enddate'] = $data['ExpertiseDateRange'][1];
		}

		if(!empty($data['LpuSection_id']))
		{
			$filter .= " and EVK.LpuSection_id = :LpuSection_id";
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}

		if(!empty($data['MedStaffFact_id']))
		{
			$filter .= " and EVK.MedStaffFact_id = :MedStaffFact_id";
			$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
		}

		if(!empty($data['ExpertiseNameType_id']))
		{
			$filter .= " and EVK.ExpertiseNameType_id = :ExpertiseNameType_id";
			$queryParams['ExpertiseNameType_id'] = $data['ExpertiseNameType_id'];
		}

		if(!empty($data['ExpertiseEventType_id']))
		{
			$filter .= " and EVK.ExpertiseEventType_id = :ExpertiseEventType_id";
			$queryParams['ExpertiseEventType_id'] = $data['ExpertiseEventType_id'];
		}

		if(!empty($data['Person_SurName']))
		{
			$filter .= " and Person_all.Person_SurName ilike :Person_SurName || '%'";
			$queryParams['Person_SurName'] = $data['Person_SurName'];
		}

		if(!empty($data['Person_FirName']))
		{
			$filter .= " and Person_all.Person_FirName ilike :Person_FirName || '%'";
			$queryParams['Person_FirName'] = $data['Person_FirName'];
		}

		if(!empty($data['Person_SecName']))
		{
			$filter .= " and Person_all.Person_SecName ilike :Person_SecName || '%'";
			$queryParams['Person_SecName'] = $data['Person_SecName'];
		}

		if(!empty($data['Person_BirthDay']))
		{
			$filter .= " and Person_all.Person_BirthDay = cast(:Person_BirthDay as date)";
			$queryParams['Person_BirthDay'] = $data['Person_BirthDay'];
		}

		if(!empty($data['PatientStatusType_id']))
		{
			$filter .= " and EVK.PatientStatusType_id = :PatientStatusType_id";
			$queryParams['PatientStatusType_id'] = $data['PatientStatusType_id'];
		}

		if(!empty($data['Diag_id']))
		{
			$filter .= " and EVK.Diag_id = :Diag_id";
			$queryParams['Diag_id'] = $data['Diag_id'];
		}

		if(!empty($data['EvnVK_DirectionDate'][0]) && !empty($data['EvnVK_DirectionDate'][1]))
		{
			$filter .= " and (EPM.EvnPrescrMse_setDT >= cast(:mse_to_startdate as date) and EPM.EvnPrescrMse_setDT <= cast(:mse_to_enddate as date))";
			$queryParams['mse_to_startdate'] = $data['EvnVK_DirectionDate'][0];
			$queryParams['mse_to_enddate'] = $data['EvnVK_DirectionDate'][1];
		}

		if(!empty($data['EvnVK_ConclusionDate'][0]) && !empty($data['EvnVK_ConclusionDate'][1]))
		{
			$filter .= " and (EM.EvnMse_setDT >= cast(:mse_with_startdate as date) and EM.EvnMse_setDT <= cast(:mse_with_enddate as date))";
			$queryParams['mse_with_startdate'] = $data['EvnVK_ConclusionDate'][0];
			$queryParams['mse_with_enddate'] = $data['EvnVK_ConclusionDate'][1];
		}

		if(!empty($data['EvnVK_isAberration']))
		{
			$filter .= " and EVK.EvnVK_isAberration = :EvnVK_isAberration";
			$queryParams['EvnVK_isAberration'] = $data['EvnVK_isAberration'];
		}

		if(!empty($data['EvnVK_isErrors']))
		{
			$filter .= " and EVK.EvnVK_isErrors = :EvnVK_isErrors";
			$queryParams['EvnVK_isErrors'] = $data['EvnVK_isErrors'];
		}

		if(!empty($data['EvnVK_isResult']))
		{
			$filter .= " and EVK.EvnVK_isResult = :EvnVK_isResult";
			$queryParams['EvnVK_isResult'] = $data['EvnVK_isResult'];
		}

		if($data['EvnVK_isControl'] == 1)
		{
			$filter .= " and EVK.EvnVK_isControl = 2";
		}

		$query = "
			select
				EVK.EvnVK_id as \"EvnVK_id\",
				to_char (EVK.EvnVK_setDT, 'dd.mm.yyyy') as \"EvnVK_ExpertiseDate\",
				to_char (EVK.EvnVK_didDT, 'dd.mm.yyyy') as \"EvnVK_ControlDate\",
				(
					select string_agg(MP.Person_Fio, ', ')
					from
						v_EvnVKExpert EVKE 
						left join v_MedServiceMedPersonal MSMP  on MSMP.MedServiceMedPersonal_id = EVKE.MedServiceMedPersonal_id
						LEFT JOIN LATERAL (
							select (COALESCE(Person_Surname,'') || ' ' || COALESCE(left(Person_Firname,1),'') || '.' || COALESCE(left(Person_Secname,1),'')) as Person_Fio
							from v_MedPersonal 
							where MedPersonal_id = MSMP.MedPersonal_id
							limit 1
						) MP ON true
					where
						EVKE.EvnVK_id = EVK.EvnVK_id
				) as \"MF_Person_FIO\",
				to_char (Person_all.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				Person_all.Person_Fio as \"Person_Fio\",
				COALESCE(Person_all.Person_SurName,'') || ' ' || COALESCE(left(Person_all.Person_FirName,1),'') || '.' || COALESCE(left(Person_all.Person_SecName,1),'') as \"Person_Fin\",
				coalesce(Diag.Diag_Name, '-') as \"Diag_Name\",
				coalesce(ExpertiseEventType.ExpertiseEventType_Name, '-') as \"ExpertiseEventType_Name\",
				coalesce(ExpertiseEventType.ExpertiseEventType_SysNick, 'Др. случаи') as \"ExpertiseEventType_SysNick\",
				coalesce(ExpertiseNameType.ExpertiseNameType_Name, '-') as \"ExpertiseNameType_Name\",
				coalesce(to_char (EPM.EvnPrescrMse_setDT, 'dd.mm.yyyy'), '') as \"EvnVK_DirectionDate\",
				case when EM.EvnMse_id is not null
					then '№' || cast(EM.EvnMse_NumAct as varchar(10)) || ' от ' || to_char (EM.EvnMse_setDT, 'dd.mm.yyyy')
					else ''
				end as \"EvnVK_ConclusionDate\",
				case EVK.EvnVK_isControl when 2 then 'да' else 'нет' end as \"EvnVK_isControl\",
				case EVK.EvnVK_isReserve when 2 then 'да' else 'нет' end as \"EvnVK_isReserve\",
				EVK.EvnVK_NumProtocol as \"num\",
				COALESCE(PST.PatientStatusType_SysNick,'') || ' ' || COALESCE(EVK.EvnVK_Prof,'') as \"PatientStatusType_Prof\",
				COALESCE(M.Person_Fin,'') as \"MedPersonal_Fin\",
				COALESCE(L.Lpu_Nick,'') as \"Lpu_Nick\",
				CASE WHEN P.Polis_Num is not null and P.Polis_Num <> ''
					then 'стр. пол. ' || COALESCE(P.Polis_Ser,'') || ' №' || P.Polis_Num
					else COALESCE(A.Address_Nick,'')
				end as \"Person_Polis_Addr\",
				case when S.Sex_Code = 1 then 'М' else case when S.Sex_Code = 2 then 'Ж' end end as \"Person_Sex\",
				case when Diag.Diag_id is not null then 'Осн. ' || Diag.Diag_Code else '' end as \"Person_Diag\",
				case when Diag_s.Diag_id is not null then ' ,соп. ' || Diag_s.Diag_Code else '' end as \"Person_Diag_s\",
				COALESCE(CTT.CauseTreatmentType_Name,'') as \"CauseTreatmentType_Name\",
				COALESCE(ExpertiseNameType.ExpertiseNameType_SysNick,'') as \"ExpertiseNameType\",
				COALESCE(ExpertiseNameSubjectType.ExpertiseNameSubjectType_SysNick,'') as \"ExpertiseNameSubjectType\",
				case when ESB.EvnStickbase_Num is not null and length(ESB.EvnStickBase_Num) > 0
					then '№ Л/Н ' || ESB.EvnStickBase_Num
					else
						case when EVK.EvnVK_LVN is not null and length(EVK.EvnVK_LVN) > 0
						then '№ Л/Н ' || EVK.EvnVK_LVN
						else ''
				    end
				end as \"EvnVK_LVN\",
				CASE WHEN ESWR.EvnStickWorkRelease_id is not null
				then cast(EXTRACT(day from ESWR.EvnStickWorkRelease_endDT-ESWR.EvnStickWorkRelease_begDT) as varchar) || ' дней'
				else
					case when EVK.EvnVK_WorkReleasePeriod is not null /*and EVK.EvnVK_WorkReleasePeriod <> 0*/
						then cast(EVK.EvnVK_WorkReleasePeriod as varchar) /*+ ' дней'*/
					else ''
					end
				end as \"EvnVK_WorkReleasePeriod\",
				case when EVK.EvnVK_StickDuration is not null and EVK.EvnVK_StickDuration <> 0
				then cast(EVK.EvnVK_StickDuration as varchar) || ' дней'
				else ''
				end as \"EvnVK_StickDuration\",
				case when EVK.EvnVK_isUseStandard = 2 then 'Да' else 'Нет' end as \"EvnVK_isUseStandard\",
				case when EVK.EvnVK_isAberration = 2 then 'Да' else 'Нет' end as \"EvnVK_isAberration\",
				COALESCE(EVK.EvnVK_AberrationDescr,'') as \"EvnVK_AberrationDescr\",
				case when EVK.EvnVK_isErrors = 2 then 'Да' else 'Нет' end as \"EvnVK_isErrors\",
				COALESCE(EVK.EvnVK_ErrorsDescr,'') as \"EvnVK_ErrorsDescr\",
				case when EVK.EvnVK_isResult = 2 then 'Да' else 'Нет' end as \"EvnVK_isResult\",
				COALESCE(EVK.EvnVK_ResultDescr,'') as \"EvnVK_ResultDescr\",
				COALESCE(EVK.EvnVK_ExpertDescr,'') || COALESCE(EPM.EvnPrescrMse_Recomm,'') as \"EvnVK_ExpertDescr\",
				COALESCE(EVK.EvnVK_ConclusionDescr,'') as \"EvnVK_ConclusionDescr\",
				COALESCE(EVK.EvnVK_ConclusionDescr,'') as \"EvnVK_AddInfo\"
			from
				v_EvnVK EVK
				LEFT JOIN v_ExpertiseEventType ExpertiseEventType on ExpertiseEventType.ExpertiseEventType_id = EVK.ExpertiseEventType_id
				LEFT JOIN v_ExpertiseNameType ExpertiseNameType on ExpertiseNameType.ExpertiseNameType_id = EVK.ExpertiseNameType_id
				LEFT JOIN v_ExpertiseNameSubjectType ExpertiseNameSubjectType on ExpertiseNameSubjectType.ExpertiseNameSubjectType_id = EVK.ExpertiseNameSubjectType_id
				LEFT JOIN v_Person_all Person_all on Person_all.Person_id = EVK.Person_id
					and EVK.Server_id = Person_all.Server_id and EVK.PersonEvn_id = Person_all.PersonEvn_id
				LEFT JOIN v_Polis P on P.Polis_id = Person_all.Polis_id
				LEFT JOIN v_Address A on A.Address_id = coalesce(Person_all.UAddress_id, PAddress_id)
				LEFT JOIN v_Sex S on S.Sex_id = Person_all.Sex_id
				left join v_EvnPrescrVK EPVK on EPVK.EvnPrescrVK_id = EVK.EvnPrescrVK_id
				left join lateral(
					select Person_Fin
					from v_MedPersonal M
					where MedPersonal_id = coalesce(EVK.MedPersonal_id, EPVK.MedPersonal_sid)
					limit 1
				) M on true
				LEFT JOIN v_Lpu L on L.Lpu_id = EVK.Lpu_id
				LEFT JOIN v_EvnStickBase ESB on ESB.EvnStickBase_id = EVK.EvnStickBase_id
				left join lateral(
					select
						EvnStickWorkRelease_id,
						EvnStickWorkRelease_begDT,
						EvnStickWorkRelease_endDT
					from v_EvnStickWorkRelease
					where EvnStickBase_id = EVK.EvnStickBase_id
					order by
						 EvnStickWorkRelease_IsPredVK desc
						,EvnStickWorkRelease_begDT desc
					limit 1
				) ESWR on true
				LEFT JOIN v_PatientStatusType PST on PST.PatientStatusType_id = EVK.PatientStatusType_id
				LEFT JOIN v_Diag Diag on Diag.Diag_id = EVK.Diag_id
				LEFT JOIN v_Diag Diag_s on Diag_s.Diag_id = EVK.Diag_sid
				LEFT JOIN v_CauseTreatmentType CTT on CTT.CauseTreatmentType_id = EVK.CauseTreatmentType_id
				LEFT JOIN v_EvnPrescrMse EPM on EPM.EvnVK_id = EVK.EvnVK_id
				LEFT JOIN v_EvnMse EM on EM.EvnPrescrMse_id = EPM.EvnPrescrMse_id
			where
				{$filter}
				and EVK.Lpu_id = :Lpu_id
			order by
				EVK.EvnVK_NumProtocol
		";
		//echo getDebugSQL($query, $queryParams);die;
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$result = $result->result('array');
			if($this->getRegionNick() != 'kz'){
				for($i=0;$i<count($result);$i++)
				{
					$result[$i]['EvnVK_isResult'] = '';
					if($result[$i]['EvnVK_isUseStandard'] == 'Нет')
					{
						$result[$i]['EvnVK_isAberration'] = 'Не исп.';
						$result[$i]['EvnVK_AberrationDescr'] = '';
					}
				}
			}
			return $result;
			//return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение палки, которая освобождает от работы?
	 */
	function getEvnStickWorkRelease($data)
	{
		$filter = "1=1";
		if( !empty($data['EvnStickWorkRelease_id']) ) {
			$filter .= " and ESWR.EvnStickWorkRelease_id = :EvnStickWorkRelease_id";
		} else if( !empty($data['EvnStick_id']) ) {
			$filter .= " and ESWR.EvnStickBase_id = :EvnStick_id";
		}

		$query = "
			select
				ESWR.EvnStickWorkRelease_id as \"EvnStickWorkRelease_id\",
				('с ' || to_char (cast(ESWR.evnStickWorkRelease_begDT as timestamp), 'dd.mm.yyyy') ||
				(case when ESWR.EvnStickWorkRelease_endDT is not null
					then ' по ' || to_char (cast(ESWR.EvnStickWorkRelease_endDT as timestamp), 'dd.mm.yyyy')
					else ''
				end) || '/' || coalesce(LPU.Lpu_Nick, '') || '/' || coalesce(MP.Person_Fio, '')
				) as \"EvnStickWorkRelease_info\"
			from
				v_EvnStickWorkRelease ESWR
				left join v_MedPersonal MP on MP.MedPersonal_id = ESWR.MedPersonal_id
				left join v_EvnStickBase ESB on ESB.EvnStickBase_id = ESWR.EvnStickBase_id
				left join v_Lpu_all LPU on LPU.Lpu_id = ESB.Lpu_id
			where
				{$filter}
		";
		//echo getDebugSQL($query, $data);exit;
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Получение списка номеров карт
	 */
	function getEvnNumCardList($data)
	{
		$filter = "1=1";
		$queryParams = array(
			'Person_id' => $data['Person_id']
		);
		if (!empty($data['Evn_id'])) {
			$filter .= " and coalesce(EPL.EvnPL_id, EPLS.EvnPLStom_id, EPS.EvnPS_id) = :Evn_id";
			$queryParams['Evn_id'] = $data['Evn_id'];
		} else if (!empty($data['EvnDirection_pid'])) {
			$filter .= " and (
				coalesce(EPL.EvnPL_id, EPLS.EvnPLStom_id, EPS.EvnPS_id) = :EvnDirection_pid
				or :EvnDirection_pid in (select EvnVizitPL_id from v_EvnVizitPL where EvnVizitPL_pid = EPL.EvnPL_id)
				or :EvnDirection_pid in (select EvnVizitPLStom_id from v_EvnVizitPLStom where EvnVizitPLStom_pid = EPLS.EvnPLStom_id)
				or :EvnDirection_pid in (select EvnSection_id from v_EvnSection where EvnSection_pid = EPS.EvnPS_id and coalesce(EvnSection_IsPriem, 1) = 1)
			)";
			$queryParams['EvnDirection_pid'] = $data['EvnDirection_pid'];
		}

		$query = "
			select
				coalesce(EPL.EvnPL_id, EPLS.EvnPLStom_id, EPS.EvnPS_id) as \"Evn_id\",
				coalesce(EPL.EvnPL_NumCard, EPLS.EvnPLStom_NumCard, EPS.EvnPS_NumCard) as \"Evn_NumCard\",
				coalesce(EPL.Diag_id, EPLS.Diag_id, EPS.Diag_id) as \"Diag_id\"
			from
				v_Evn E
				left join v_EvnPL EPL on EPL.EvnPL_id = E.Evn_id
				left join v_EvnPLStom EPLS on EPLS.EvnPLStom_id = E.Evn_id
				left join v_EvnPS EPS on EPS.EvnPS_id = E.Evn_id
				inner join v_Lpu L on L.Lpu_id = E.Lpu_id
			where
				{$filter}
				and E.Person_id = :Person_id
				and E.EvnClass_id in (3, 6, 30)
		";
		//echo getDebugSQL($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка шаблонов для решения ВК
	 */
	function loadDecisionVKTemplateList($data) {
		$params = array();
		$filters = "(1=1)";

		if (!empty($data['ExpertiseNameType_id'])) {
			$filters .= " and DVKT.ExpertiseNameType_id = :ExpertiseNameType_id";
			$params['ExpertiseNameType_id'] = $data['ExpertiseNameType_id'];
		}

		$query = "
			select
				DVKT.DecisionVKTemplate_id as \"DecisionVKTemplate_id\",
				DVKT.DecisionVKTemplate_Code as \"DecisionVKTemplate_Code\",
				DVKT.DecisionVKTemplate_Name as \"DecisionVKTemplate_Name\",
				DVKT.ExpertiseNameType_id as \"ExpertiseNameType_id\"
			from v_DecisionVKTemplate DVKT
			where
				{$filters}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных для интерактивного документа
	 */
	function getEvnVKViewData($data) {
		$params = array(
			'EvnVK_id' => $data['EvnVK_id'],
			'MedService_id' => $data['session']['CurMedService_id']
		);

		$query = "
			select
				case when EVK.MedService_id = :MedService_id then 'edit' else 'view' end as \"accessType\",
				EVK.EvnVK_id as \"EvnVK_id\",
				to_char (cast(EVK.EvnVK_setDate as timestamp), 'dd.mm.yyyy') as \"EvnVK_setDate\",
				to_char (cast(EVK.EvnVK_didDate as timestamp), 'dd.mm.yyyy') as \"EvnVK_didDate\",
				PS.Person_id as \"Person_id\",
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\",
				to_char (cast(PS.Person_BirthDay as timestamp), 'dd.mm.yyyy') as \"Person_BirthDay\",
				L.Lpu_id as \"Lpu_id\",
				L.Lpu_Nick as \"Lpu_Nick\",
				EVK.Server_id as \"Server_id\",
				EVK.CauseTreatmentType_id as \"CauseTreatmentType_id\",
				CTT.CauseTreatmentType_Name as \"CauseTreatmentType_Name\",
				EVK.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				EVK.Diag_sid as \"Diag_sid\",
				sD.Diag_Code as \"Diag_sCode\",
				sD.Diag_Name as \"Diag_sName\",
				EVK.EvnVK_MainDisease as \"EvnVK_MainDisease\",
				EVK.EvnStickBase_id as \"EvnStickBase_id\",
				ltrim(
					coalesce(ES.EvnStick_Ser, '')||' '||coalesce(ES.EvnStick_Num, '')||
					(case when (ES.EvnStick_begDate IS NOT NULL) then ' выдан: '||to_char (cast(ES.EvnStick_begDate as timestamp), 'dd.mm.yyyy') else '' end)||
					(case when (ES.EvnStick_endDate IS NOT NULL) then ' по '||to_char (cast(ES.EvnStick_endDate as timestamp), 'dd.mm.yyyy') else '' end)
				) as \"EvnStick_all\",
				EVK.EvnStickWorkRelease_id as \"EvnStickWorkRelease_id\",
				('с ' || to_char (cast(ESWR.evnStickWorkRelease_begDT as timestamp), 'dd.mm.yyyy') ||
				(case when ESWR.EvnStickWorkRelease_endDT is not null
					then ' по ' || to_char (cast(ESWR.EvnStickWorkRelease_endDT as timestamp), 'dd.mm.yyyy')
					else ''
				end) || '/' || coalesce(esLPU.Lpu_Nick, '') || '/' || coalesce(wrMP.Person_Fio, '')
				) as \"EvnStickWorkRelease_info\",
				EVK.EvnVK_AberrationDescr as \"EvnVK_AberrationDescr\",
				EM.EvnMse_InvalidCause as \"EvnVK_AddInfo\",
				to_char (cast(EM.EvnMse_setDT as timestamp), 'dd.mm.yyyy') as \"EvnVK_ConclusionDate\",
				case
					when EM.InvalidGroupType_id = 1 then EM.EvnMse_InvalidCauseDeni
					else IGT.InvalidGroupType_Name
				end as \"EvnVK_ConclusionDescr \",
				EPM.EvnPrescrMse_id as \"EvnPrescrMse_id\",
				to_char (cast(EM.EvnMse_ReExamDate as timestamp), 'dd.mm.yyyy') as \"EvnVK_ConclusionPeriodDate\",
				to_char (cast(EPM.EvnPrescrMse_setDT as timestamp), 'dd.mm.yyyy') as \"EvnVK_DirectionDate\",
				EVK.EvnVK_ErrorsDescr as \"EvnVK_ErrorsDescr\",
				EVK.EvnVK_ExpertiseStickNumber as \"EvnVK_ExpertiseStickNumber\",
				EVK.EvnVK_isAberration as \"EvnVK_isAberration\",
				isAberration.YesNo_Name as \"EvnVK_isAberrationYN\",
			    (case when (EVK.EvnVK_isControl = 2) then 1 else 0 end) as \"EvnVK_isControl\",
				EVK.EvnVK_isErrors as \"EvnVK_isErrors\",
				isErrors.YesNo_Name as \"EvnVK_isErrorsYN\",
				coalesce(EvnVK_isReserve,1) as \"EvnVK_isReserve\",
				isReserve.YesNo_Name as \"EvnVK_isReserveYN\",
				EVK.EvnVK_isResult as \"EvnVK_isResult\",
				isResult.YesNo_Name as \"EvnVK_isResultYN\",
				EVK.EvnVK_isUseStandard as \"EvnVK_isUseStandard\",
				isUseStandard.YesNo_Name as \"EvnVK_isUseStandardYN\",
				EVK.EvnVK_NumCard as \"EvnVK_NumCard\",
				EVK.EvnVK_NumProtocol as \"EvnVK_NumProtocol\",
				EVK.EvnVK_ResultDescr as \"EvnVK_ResultDescr\",
				EVK.EvnVK_StickDuration as \"EvnVK_StickDuration\",
				EVK.EvnVK_StickPeriod as \"EvnVK_StickPeriod\",
				EVK.ExpertiseEventType_id as \"ExpertiseEventType_id\",
				EETL.ExpertiseEventTypeLink_id as \"ExpertiseEventTypeLink_id\",
				EET.ExpertiseEventType_Name as \"ExpertiseEventTypeLink_Name\",
				EVK.ExpertiseNameSubjectType_id as \"ExpertiseNameSubjectType_id\",
				ENST.ExpertiseNameSubjectType_Name as \"ExpertiseNameSubjectType_Name\",
				EVK.ExpertiseNameType_id as \"ExpertiseNameType_id\",
				ENT.ExpertiseNameType_Name as \"ExpertiseNameType_Name\",
				ENT.ExpertiseNameType_Code as \"ExpertiseNameType_Code\",
				--EVK.LpuSection_id as LpuSection_id,
				--EVK.MedStaffFact_id as MedStaffFact_id,
				EVK.MedService_id as \"MedService_id\",
				--EVK.Okved_id as Okved_id,
				EVK.EvnVK_Prof as \"EvnVK_Prof\",
				--EVK.PatientStatusType_id as PatientStatusType_id,
				--PST.PatientStatusType_Name,
				COALESCE(EVK.PatientStatusType_id, 0) as \"PatientStatusType_id\",
				--coalesce(PST.PatientStatusType_Name,'') as PatientStatusType_Name,
				EVK.PersonEvn_id as \"PersonEvn_id\",
			    (case when (EVK.EvnVK_isAutoFill = 2) then 1 else 0 end) as \"EvnVK_isAutoFill\",
				EPVK.EvnPrescrVK_id as \"EvnPrescrVK_id\",
				coalesce(EVK.MedPersonal_id, EPVK.MedPersonal_sid) as \"MedPersonal_id\",
				rtrim(mp.Person_Fio) as \"MedPersonal_Fio\",
				EVK.EvnVK_ExpertDescr as \"EvnVK_ExpertDescr\",
				EVK.EvnVK_DecisionVK as \"EvnVK_DecisionVK\",
				EPVK.EvnPrescrVK_pid as \"Evn_id\",
				EVK.EvnVK_LVN as \"EvnVK_LVN\",
				EVK.EvnVK_Note as \"EvnVK_Note\",
				EVK.EvnVK_WorkReleasePeriod as \"EvnVK_WorkReleasePeriod\",
				EDH.EvnDirectionHTM_id as \"EvnDirectionHTM_id\",
				substring(EvnVKPatientStatusTypeStr.EvnVKPatientStatusType_Items, 1, length(EvnVKPatientStatusTypeStr.EvnVKPatientStatusType_Items)-1) as \"PatientStatusType_List\",
				substring(EvnVKPatientStatusTypeNameStr.EvnVKPatientStatusTypeName_Items, 1, length(EvnVKPatientStatusTypeNameStr.EvnVKPatientStatusTypeName_Items)-1) as \"PatientStatusType_Name\"
			from
				v_EvnVK EVK
				left join v_Lpu_all L on L.Lpu_id = EVK.Lpu_id
				left join v_PersonState PS on PS.Person_id = EVK.Person_id
				left join v_EvnPrescrVK EPVK on EPVK.EvnPrescrVK_id = EVK.EvnPrescrVK_id
				left join v_EvnPrescrMse EPM on EPM.EvnVK_id = EVK.EvnVK_id or EPM.EvnPrescrMse_id = EPVK.EvnPrescrMse_id
				left join v_EvnMse EM on EM.EvnPrescrMse_id = EPM.EvnPrescrMse_id
				left join v_InvalidGroupType IGT on IGT.InvalidGroupType_id = EM.InvalidGroupType_id
				left join v_EvnDirectionHTM EDH on EDH.EvnDirectionHTM_pid = EVK.EvnVK_id
				left join v_EvnStick ES on ES.EvnStick_id = EVK.EvnStickBase_id
				left join v_Lpu_all esLPU on esLPU.Lpu_id = ES.Lpu_id
				left join v_EvnStickWorkRelease ESWR on ESWR.EvnStickWorkRelease_id = EVK.EvnStickWorkRelease_id
				left join v_MedPersonal wrMP on wrMP.MedPersonal_id = ESWR.MedPersonal_id
				left join v_MedPersonal mp on mp.MedPersonal_id = coalesce(EVK.MedPersonal_id,EPVK.MedPersonal_sid)
				left join v_YesNo isReserve on isReserve.YesNo_id = coalesce(EVK.EvnVK_isReserve,1)
				left join v_YesNo isAberration on isAberration.YesNo_id = EVK.EvnVK_isAberration
				left join v_YesNo isErrors on isErrors.YesNo_id = EVK.EvnVK_isErrors
				left join v_YesNo isResult on isResult.YesNo_id = EVK.EvnVK_isResult
				left join v_YesNo isUseStandard on isUseStandard.YesNo_id = EVK.EvnVK_isUseStandard

				left join v_PatientStatusType PST on PST.PatientStatusType_id = EVK.PatientStatusType_id
				left join v_CauseTreatmentType CTT on CTT.CauseTreatmentType_id = EVK.CauseTreatmentType_id
				left join v_Diag D on D.Diag_id = EVK.Diag_id
				left join v_Diag sD on sD.Diag_id = EVK.Diag_sid
				left join v_ExpertiseNameSubjectType ENST on ENST.ExpertiseNameSubjectType_id = EVK.ExpertiseNameSubjectType_id
				left join v_ExpertiseNameType ENT on ENT.ExpertiseNameType_id = EVK.ExpertiseNameType_id
				left join v_ExpertiseEventType EET on EET.ExpertiseEventType_id = EVK.ExpertiseEventType_id
				left join v_ExpertiseEventTypeLink EETL on EETL.ExpertiseEventType_id = EVK.ExpertiseEventType_id and EETL.ExpertiseNameType_id = EVK.ExpertiseNameType_id
				left join lateral (
					select
						string_agg(CAST(EVKPST.PatientStatusType_id as varchar), ', ') as EvnVKPatientStatusType_Items
					from v_EvnVKPatientStatusType EVKPST
					where EVKPST.EvnVK_id = EVK.EvnVK_id
				) as EvnVKPatientStatusTypeStr on true
				left join lateral (
					select
						string_agg(CAST(PatientStatusType_Name as varchar), ', ') as EvnVKPatientStatusTypeName_Items
					from (
						select
							PST.PatientStatusType_Name
						from v_EvnVKPatientStatusType EVKPST
							inner join v_PatientStatusType PST on PST.PatientStatusType_id = EVKPST.PatientStatusType_id
						where EVKPST.EvnVK_id = EVK.EvnVK_id
						order by EVKPST.PatientStatusType_id
					) t
				) as EvnVKPatientStatusTypeNameStr on true
				
			where
				EVK.EvnVK_id = :EvnVK_id
            limit 1
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка экспертов для отображения в ЭМК
	 */
	function getEvnVKExpertViewData($data) {
		$params = array('EvnVK_id' => $data['EvnVK_id']);

		$query = "
            select
				EVKE.EvnVK_id as \"EvnVK_id\",
				EVKE.EvnVKExpert_id as \"EvnVKExpert_id\",
				EVKE.MedServiceMedPersonal_id as \"MedServiceMedPersonal_id\",
				EVKE.MedStaffFact_id as \"MedStaffFact_id\",
				MP.MedPersonal_id as \"MedPersonal_id\",
				MP.Person_Fio as \"MedPersonal_Fio\",
				case when EVKE.ExpertMedStaffType_id = 1 then 2 else 1 end as \"ExpertMedStaffType_id\",
				EMST.ExpertMedStaffType_Name as \"ExpertMedStaffType_Name\"
			from v_EvnVKExpert EVKE
				left join v_MedServiceMedPersonal MSMP  on MSMP.MedServiceMedPersonal_id = EVKE.MedServiceMedPersonal_id
				left join v_ExpertMedStaffType EMST  on EMST.ExpertMedStaffType_id = EVKE.ExpertMedStaffType_id
				LEFT JOIN LATERAL (
					select t.MedPersonal_id,t.Person_Fio
					from v_MedPersonal t 
					where t.MedPersonal_id = MSMP.MedPersonal_id
					order by t.WorkData_begDate
                    limit 1
				) MP ON true
			where EVKE.EvnVK_id = :EvnVK_id
			and EVKE.MedServiceMedPersonal_id is not null
		";

		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Возвращает номер активного нумератора
	 */
	function getEvnVKNum($data, $numerator = null)
	{
		$params = array(
			'NumeratorObject_SysName' => 'EvnVK',
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id'],
			'onDate' => $data['onDate'],
			'Numerator_id' => $data['Numerator_id']
		);
		$name = 'Протокол ВК';
		$this->load->model('Numerator_model');

		$resp = $this->Numerator_model->getNumeratorNum($params, $numerator);

		if (!empty($resp['Numerator_Num']) && !isset($resp['Error_Msg'])) {
			return $resp;
		} else {
			if (!empty($resp['Error_Msg'])) {
				return array('Error_Msg' => $resp['Error_Msg'], 'success' => false);
			}
			return array('Error_Msg' => 'Не задан активный нумератор для "' . $name . '". Обратитесь к администратору системы.', 'Error_Code' => 'numerator404', 'success' => false);
		}
	}

	/**
	 * Получение списка эпикризов
	 */
	function getEvnXmlList($data) {
		$filter = '';
		$join = '';
		if(!empty($data['Evn_id'])) {
			$join .= ' inner join v_Evn evnall on evnall.Evn_rid = evn.Evn_rid ';
			$filter .= ' and evnall.Evn_id = :Evn_id ';
		}

		$sql = "
			select 
				ex.EvnXml_id as \"EvnXml_id\",
				'Эпикриз при направлении на ВК от ' || to_char(evn.Evn_setDate,'dd.mm.yyyy') \"EvnXml_Name\"
			from v_EvnXml ex 
				inner join v_Evn evn on evn.Evn_id = ex.Evn_id
				{$join}
			where 
				ex.XmlType_id = 10 and 
				evn.Person_id = :Person_id 
				{$filter}
		";

		return $this->queryResult($sql, $data);
	}

	/**
	 * Получение списка анкет
	 */
	function getPalliatQuestionList($data) {

		$sql = "
			select 
				p.PalliatQuestion_id as \"PalliatQuestion_id\",
				'Анкета от ' || to_char(p.PalliatQuestion_setDate,'dd.mm.yyyy') \"PalliatQuestion_Name\"
			from PalliatQuestion p
			where 
				p.Person_id = :Person_id 
			order by 
				p.PalliatQuestion_setDate desc
		";

		return $this->queryResult($sql, $data);
	}

	/**
	 * Проверка прав на подписание карты мед освидетельствования водителя
	 */
	function checkSignAccess($data) {
		// Права на подписание имеет врач председатель ВК, указанный в протоколе
		$resp_epldd = $this->queryResult("
			select
				evk.EvnVK_id as \"EvnVK_id\",
				msmp.MedPersonal_id as \"MedPersonal_id\"
			from v_EvnVK evk
				left join lateral(
					select
						EVKE.MedServiceMedPersonal_id
					from
						v_EvnVKExpert EVKE
					where EVKE.EvnVK_id = EVK.EvnVK_id
						and EVKE.ExpertMedStaffType_id = 1	
					limit 1
				) evke on true
				left join v_MedServiceMedPersonal msmp on msmp.MedServiceMedPersonal_id = evke.MedServiceMedPersonal_id 
			where
				evk.EvnVK_id = :EvnVK_id
		", [
			'EvnVK_id' => $data['EvnVK_id']
		]);

		if (empty($resp_epldd[0]['EvnVK_id'])) {
			throw new Exception('Подписание невозможно, т.к. не найден подписываемый протокол ВК');
		}

		if (empty($resp_epldd[0]['MedPersonal_id'])) {
			throw new Exception('Подписание невозможно, т.к. не указан председатель ВК');
		}

		if (empty($data['session']['medpersonal_id']) || $resp_epldd[0]['MedPersonal_id'] != $data['session']['medpersonal_id']) {
			throw new Exception('Подписание невозможно, права на подписание проотокола ВК имеет только председатель ВК.');
		}

		return true;
	}
}
