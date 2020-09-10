<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Класс модели для работы по иммунопрофилактики
 *
 * @author    ArslanovAZ
 * @version   17.04.2012
 */
class VaccineCtrl_model extends CI_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение списка журналов
	 */
	public function loadJournals()
	{//todo
		$query = "
			SELECT [List_Journals_Id] Journal_id, [Name] Name
			FROM [vac].[Vac_List_Journals]  WITH (NOLOCK)
			ORDER BY [sort]
		";
		$res = $this->db->query($query);

		if (is_object($res)) {
			return $res->result('array');
		} else {
			//            return false;
			echo 'Ошибка';
		}
	}

	/**
	 * Список карт проф. прививок (Поиск)
	 */
	public function searchVacMap($data)
	{ //todo
		$filters = array();
		$join = '';
		$queryParams = array();

		$this->getVacMapFilters(TYPE_JOURNAL::VAC_MAP, $data, $filters, $queryParams, $join);

		$sql = "
			SELECT top (100)
			-- select
				PK.Person_id
				,PK.FirName
				,PK.SurName
				,PK.SecName
				,convert(varchar, PK.BirthDay, 104) [BirthDay]
				,PK.vac_Person_Sex_id [Sex_id]
				,PK.vac_Person_sex [sex]
				,PK.Address
				--,PK.vac_Person_uch [uch]
				,PK.SocStatus_Name
				,PK.[Lpu_id]
				,PK.[Lpu_Name]
				,PK.group_risk
				,PK.vacPersonKart_Age [Age]
				,PK.Server_id
				,PK.PersonEvn_id
				,PK.LpuRegion_id
				,PK.Org_id
				,PK.Person_dead
				,L2.Lpu_Nick
				,PCard.LpuRegion_Name as uch
				,PCard.Lpu_atNick
			-- end select
			FROM
			-- from
				vac.v_vacPersonKart PK WITH (NOLOCK)
				{$join}
				cross apply (
					select top 1 
						PC.PersonCard_id, 
						PC.Lpu_id,
						PC.LpuRegion_id,
						ISNULL(LR.LpuRegionType_Name,'') + ' №' + ISNULL(LR.LpuRegion_Name,'') as LpuRegion_Name,
						ISNULL(L.Lpu_Nick,L.Lpu_Name) as Lpu_atNick
					from v_PersonCard PC (nolock)
					left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
					left join v_Lpu L (nolock) on L.Lpu_id = PC.Lpu_id
					where PC.Person_id = PK.Person_id
						and LpuAttachType_id = 1
				) PCard
				left join v_Lpu L2 (nolock) on L2.Lpu_id = PK.Lpu_id
			-- end from
			" . ImplodeWherePH($filters) . "
			--  ORDER BY
			-- order by
			PK.SurName, PK.FirName, PK.SecName
			-- end order by

			";


		// замена функции преобразования запроса в запрос для получения количества записей

		$lpu_id = $data['Lpu_id'];


		//     Оптимизация подсчета общего количества записей         
		/*if (isset($data['SurName']) or isset($data['FirName']) or isset($data['SecName']) or isset($data['BirthDayRange'][0]) or isset($data['BirthDayRange'][1]) or (isset($data['uch_id']) and $data['uch_id'] != -1) or (isset($data['Org_id']) and $data['Org_id'] != 0) or ($data['OrgType_id'] != 0) or ($data['AttachMethod_id'] != 0)
		) {

			$count_sql = getCountSQLPH($sql);
		} else {
			$count_sql = " Select count (1) cnt
            from vac.fn_PersonKart ($lpu_id) PK
        " . ImplodeWherePH($filters);
		}*/


		if (!empty($data['getCountOnly']) && $data['getCountOnly'] == 1) {
			// подсчет только количества строк
			$get_count_query = getCountSQLPH($sql);

			$get_count_result = $this->db->query($get_count_query, $queryParams);

			if (is_object($get_count_result)) {
				$response['totalCount'] = $get_count_result->result('array');
				$response['totalCount'] = $response['totalCount'][0]['cnt'];
			}
		} else {
			if (isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0) {
				$limit_query = getLimitSQLPH($sql, $data['start'], $data['limit']);
				$result = $this->db->query($limit_query, $queryParams);
			} else {
				$result = $this->db->query($sql, $queryParams);
			}

			if (is_object($result)) {
				$res = $result->result('array');
				if (is_array($res)) {
				
				
					//Получим группу риска:
					//group_risk
					for ($i=0; $i<count($res)-1;$i++){
						$rist_str = "";
						$risk_params = array(
							'Person_id' => $res[$i]['Person_id']
						);
						$risk_query = "
							select
								VR.VaccineRisk_id,
								VT.VaccineType_NameIm
							from vac.v_VaccineRisk VR (nolock)
							inner join vac.v_VaccineType VT (nolock) on VT.VaccineType_id = VR.VaccineType_id
							where VR.Person_id = :Person_id
						";
						$risk_result = $this->db->query($risk_query,$risk_params);
						if(is_object($risk_result)){
							$risk_result = $risk_result->result('array');
							for($j=0;$j<count($risk_result); $j++){
								$rist_str .= $risk_result[$j]['VaccineType_NameIm']."<br>";
							}
						}
						$res[$i]['group_risk'] = $rist_str;
					}
			
				
					$response['data'] = $res;
					$response['totalCount'] = $data['start'] + count($res);
					if (count($res) >= $data['limit']) {
						$response['overLimit'] = true; // лимит весь вошел на страницу, а значит реальный каунт может отличаться от totalCount и пусть юезр запросит его сам, если он ему нужен
					}
				} else {
					return false;
				}
			} else {
				return false;
			}
		}

		return $response;

	}

	/**
	 * План прививок (Поиск)
	 */
	public function searchVacPlan($data)
	{
		$filters = array();
		$join = '';
		$CardJoin = '';
		$queryParams = array();
		$Declare = "";

		if (isset($data['Date_Plan'][1])) {
			$Declare .= "Declare @Date_Plan1 date = :Date_Plan1;
                ";
			$filters[] = "[vac_PersonPlanFinal_DatePlan] <= :Date_Plan1";
			$queryParams['Date_Plan1'] = $data['Date_Plan'][1];
		}
		if (isset($data['Date_Plan'][0])) {
			$Declare .= "Declare @Date_Plan0 date = :Date_Plan0;
                ";
		}

		$this->genSearchFilters(TYPE_JOURNAL::VAC_PLAN, $data, $filters, $queryParams, $join, $CardJoin);

		$sql = "
             {$Declare}
            SELECT 
            -- select
			FromTable.vac_PersonPlanFinal_id [planTmp_id]
			,FromTable.NationalCalendarVac_Scheme_Num [Scheme_num]
			,convert(varchar, FromTable.vac_PersonPlanFinal_DatePlan, 104) [Date_Plan]
			,FromTable.Person_id
			,FromTable.SurName
			,FromTable.FirName
			,FromTable.SecName
			,FromTable.vac_Person_sex [sex]
			,convert(varchar, FromTable.vac_Person_BirthDay, 104) [BirthDay]
			,FromTable.vac_Person_group_risk [group_risk]
			,FromTable.Lpu_id
			,FromTable.Lpu_Name
			,FromTable.vac_PersonPlanFinal_Age [Age]
			,FromTable.typeName [type_name]
			,FromTable.VaccineType_Name [Name]
			,FromTable.NationalCalendarVac_SequenceVac [SequenceVac]
			,convert(varchar, FromTable.vac_PersonPlanFinal_dateS, 104) date_S
			,convert(varchar, FromTable.vac_PersonPlanFinal_dateE, 104) date_E
			--,FromTable.vac_PersonPlanFinal_uch [uch]
			,FromTable.VaccineType_id
            ,convert(varchar, FromTable.DateSave, 104) DateSave
            ,FromTable.Server_id
            ,FromTable.PersonEvn_id
            ,FromTable.Address
            ,FromTable.Org_id
            ,FromTable.Person_dead
            ,L2.Lpu_Nick
            ,PCard.Lpu_atNick
            ,PCard.LpuRegion_Name as uch
            -- end select
            FROM 
              -- from
                vac.v_PersonPlanFinal FromTable  WITH (NOLOCK)
                outer apply(
                	select top 1
                		PC.PersonCard_id,
                		PC.Person_id,
                		PC.Lpu_id,
                		PC.LpuRegion_id,
                		ISNULL(L.Lpu_Nick,L.Lpu_Name) as Lpu_atNick,
                		ISNULL(LR.LpuRegionType_Name,'') + ' №' + LR.LpuRegion_Name as LpuRegion_Name
                	from v_PersonCard PC (nolock)
                	left join v_Lpu L (nolock) on L.Lpu_id = PC.Lpu_id
                	left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
					{$CardJoin}
                	where PC.Person_id = FromTable.Person_id
                ) PCard
                left join v_Lpu L2 (nolock) on L2.Lpu_id = FromTable.Lpu_id
				left join v_PersonState PK (nolock) on PK.Person_id = PCard.Person_id
				left outer join dbo.v_Address adr(nolock) on adr.Address_id = PK.PAddress_id
              -- end from
              " . ImplodeWherePH($filters) . "          
             ORDER BY 
              -- order by
            --   FromTable.vac_PersonPlanFinal_DatePlan, 
                FromTable.SurName, FromTable.FirName, FromTable.SecName
              -- end order by 
";

		// замена функции преобразования запроса в запрос для получения количества записей
		//     Оптимизация подсчета общего количества записей
		/*if (isset($data['SurName']) or isset($data['FirName']) or isset($data['SecName']) or isset($data['BirthDayRange'][0]) or isset($data['BirthDayRange'][1]) or (isset($data['uch_id']) and $data['uch_id'] != -1) or (isset($data['Org_id']) and $data['Org_id'] != 0) or ($data['OrgType_id'] != 0) or ($data['AttachMethod_id'] != 0)
		) {

			$count_sql = getCountSQLPH($sql);
		} else {

			$count_sql = " {$Declare} SELECT count(1) AS cnt FROM  vac.v_PersonPlanFinalTrunc  WITH (NOLOCK) " . ImplodeWherePH($filters);
		};*/

		if (!empty($data['getCountOnly']) && $data['getCountOnly'] == 1) {
			// подсчет только количества строк
			$get_count_query = getCountSQLPH($sql);

			$get_count_result = $this->db->query($get_count_query, $queryParams);

			if (is_object($get_count_result)) {
				$response['totalCount'] = $get_count_result->result('array');
				$response['totalCount'] = $response['totalCount'][0]['cnt'];
			}
		} else {		 
			if (isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0) {
				$limit_query = getLimitSQLPH($sql, $data['start'], $data['limit']);
				$result = $this->db->query(" {$Declare} {$limit_query} ", $queryParams);
			} else {
				$result = $this->db->query(" {$Declare} {$sql} ", $queryParams);
			}
			if (is_object($result)) {
				$res = $result->result('array');
				if (is_array($res)) {
					$response['data'] = $res;
					$response['totalCount'] = $data['start'] + count($res);
					if (count($res) >= $data['limit']) {
						$response['overLimit'] = true; // лимит весь вошел на страницу, а значит реальный каунт может отличаться от totalCount и пусть юезр запросит его сам, если он ему нужен
					}
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
		
		return $response;
	}

	/**
	 * Журнал назначенных прививок (Поиск)
	 */

	public function searchVacAssigned($data)
	{
		$filters = array();
		$join = '';
		$CardJoin = '';
		$queryParams = array();

		$this->genSearchFilters(TYPE_JOURNAL::VAC_ASSIGNED, $data, $filters, $queryParams, $join, $CardJoin);

		$sql = "
			SELECT
			-- select
			FromTable.JournalVacFixed_id
			,FromTable.vacJournalAccount_Purpose_MedPersonal_id Purpose_MedPersonal_id
			,FromTable.Person_id
			,convert(varchar, FromTable.vacJournalAccount_DatePurpose, 104) Date_Purpose
			,FromTable.vacJournalAccount_uch uch
			,FromTable.vacJournalAccount_fio fio
			,FromTable.SurName
			,FromTable.FirName
			,FromTable.SecName
			,convert(varchar, FromTable.vac_Person_BirthDay, 104) [BirthDay]
			,FromTable.vac_Person_sex sex
			,FromTable.vacJournalAccount_Age age
			,FromTable.Lpu_id
			,FromTable.Lpu_Name
			,FromTable.Vaccine_name vac_name
			,FromTable.Vaccine_id
			,FromTable.vacJournalAccount_Infection NAME_TYPE_VAC
			,FromTable.vacJournalAccount_Dose VACCINE_DOZA
			,FromTable.vacJournalAccount_WayPlace WAY_PLACE
			,convert(varchar, FromTable.vacJournalAccount_VacDateSave, 104) [VacDateSave]
			,convert(varchar, FromTable.vacJournalAccount_DateSave, 104) [DateSave]
			,FromTable.Server_id
			,FromTable.PersonEvn_id
			,FromTable.Org_id
			,FromTable.Person_dead
			,L2.Lpu_Nick
            ,PCard.Lpu_atNick
            --,PCard.LpuRegion_Name as uch --одинаковые псевдонимы и значения с полем vacJournalAccount_uch вызывает ошибку БД
			-- end select
			FROM
			-- from
			vac.v_JournalVacFixed FromTable   WITH (NOLOCK)
			outer apply(
				select top 1
					PC.PersonCard_id,
					PC.Person_id,
					PC.Lpu_id,
					PC.LpuRegion_id,
					ISNULL(L.Lpu_Nick,L.Lpu_Name) as Lpu_atNick,
					ISNULL(LR.LpuRegionType_Name,'') + ' №' + LR.LpuRegion_Name as LpuRegion_Name
				from v_PersonCard PC (nolock)
				left join v_Lpu L (nolock) on L.Lpu_id = PC.Lpu_id
				left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
				{$CardJoin}
				where PC.Person_id = FromTable.Person_id
			) PCard
			left join v_Lpu L2 (nolock) on L2.Lpu_id = FromTable.Lpu_id
			left join v_PersonState PK (nolock) on PK.Person_id = PCard.Person_id
			left outer join dbo.v_Address adr(nolock) on adr.Address_id = PK.PAddress_id
			{$join}
			-- end from
			  " . ImplodeWherePH($filters) . "
			ORDER BY
			-- order by
				FromTable.SurName, FromTable.FirName, FromTable.SecName
			-- end order by
		";

		// замена функции преобразования запроса в запрос для получения количества записей
		$count_sql = getCountSQLPH($sql);
		log_message('debug', 'getCountSQLPH=' . $count_sql);
		log_message('debug', 'SQLsearchVacAssigned=' . $sql);
		log_message('debug', 'start=' . $data['start']);
		log_message('debug', 'limit=' . $data['limit']);
		log_message('debug', 'Person_id=' . $data['Person_id']);
		if (isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0) {
			$sql = getLimitSQLPH($sql, $data['start'], $data['limit']);
			log_message('debug', 'getLimitSQLPH=' . $sql);
		}
		$res = $this->db->query($sql, $queryParams);
		// определение общего количества записей
		$count_res = $this->db->query($count_sql, $queryParams);
		if (is_object($count_res)) {
			$cnt_arr = $count_res->result('array');
			$count = $cnt_arr[0]['cnt'];
			log_message('debug', 'countSQL=' . $count);
		} else
			return false;

		if (is_object($res)) {
			$response = $res->result('array');
			$response[] = array('__countOfAllRows' => $count);
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Журнал учета проф прививок (Поиск)
	 */

	public function searchVacRegistr($data)
	{
		$filters = array();
		$join = '';
		$CardJoin = '';
		$queryParams = array();

		$this->genSearchFilters(TYPE_JOURNAL::VAC_REGISTR, $data, $filters, $queryParams, $join, $CardJoin);

		$sql = "
			SELECT
			-- select
				FromTable.vacJournalAccount_id
				,convert(varchar, FromTable.vacJournalAccount_DatePurpose, 104) Date_Purpose
				,convert(varchar, FromTable.vacJournalAccount_DateVac, 104) Date_Vac
				--,FromTable.vacJournalAccount_uch uch
				,FromTable.Person_id
				,FromTable.Server_id
				,FromTable.PersonEvn_id
				,FromTable.SurName
				,FromTable.FirName
				,FromTable.SecName
				,convert(varchar, FromTable.vac_Person_BirthDay, 104) [BirthDay]
				,FromTable.vacJournalAccount_Age age
				,FromTable.Vaccine_name vac_name
				,FromTable.Vaccine_id
				,FromTable.vacJournalAccount_Infection NAME_TYPE_VAC
				,FromTable.vacJournalAccount_Dose VACCINE_DOZA
				--,FromTable.vacJournalAccount_WayPlace WAY_PLACE
				,case 
					when p.VaccinePlace_Name is null and wp.VaccinePlace_Name is null and  FromTable.VaccinePlace_id is not null then
						wp.VaccineWay_Name
					when p.VaccinePlace_Name is null and FromTable.VaccinePlace_id is not null then 
						wp.VaccinePlace_Name+': '+wp.VaccineWay_Name 
					else p.VaccinePlace_Name+': '+w.VaccineWay_Name 
				end as WAY_PLACE
				,FromTable.Lpu_id
				,FromTable.Lpu_Name
				,FromTable.vac_Person_sex sex
				,FromTable.vacJournalAccount_fio fio
				,convert(varchar, FromTable.vacJournalAccount_VacDateSave, 104) [VacDateSave]
				,convert(varchar, FromTable.vacJournalAccount_DateSave, 104) [DateSave]
				,FromTable.vacJournalAccount_Seria Seria
				,FromTable.Org_id
				,FromTable.Person_dead
				,L2.Lpu_Nick
				,PCard.Lpu_atNick
				,PCard.LpuRegion_Name as uch
				,NR.NotifyReaction_id
				,ISNULL(convert(varchar, NR.NotifyReaction_createDate, 104), '') as NotifyReaction_createDate
			-- end select
			FROM
			-- from
			vac.v_JournalAccountAll FromTable  WITH (NOLOCK)
			outer apply(
				select top 1
					PC.PersonCard_id,
					PC.Person_id,
					PC.Lpu_id,
					PC.LpuRegion_id,
					ISNULL(L.Lpu_Nick,L.Lpu_Name) as Lpu_atNick,
					ISNULL(LR.LpuRegionType_Name,'') + ' №' + LR.LpuRegion_Name as LpuRegion_Name
				from v_PersonCard PC (nolock)
				left join v_Lpu L (nolock) on L.Lpu_id = PC.Lpu_id
				left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
				{$CardJoin}
				where PC.Person_id = FromTable.Person_id
					and LpuAttachType_id = 1
			) PCard
			left join v_Lpu L2 (nolock) on L2.Lpu_id = FromTable.Lpu_id
			left join vac.v_NotifyReaction NR (nolock) ON NR.vacJournalAccount_id = FromTable.vacJournalAccount_id
			LEFT JOIN vac.S_VaccineWay w WITH (NOLOCK) ON FromTable.VaccineWay_id = w.VaccineWay_id
			LEFT JOIN vac.v_VaccinePlace p WITH (NOLOCK) ON FromTable.VaccinePlace_id = p.VaccinePlace_id
			OUTER APPLY(
				SELECT TOP 1
					p2.VaccinePlace_Name, w2.VaccineWay_Name
				FROM vac.v_VaccineWayPlace wp2  WITH (NOLOCK)
					LEFT JOIN vac.v_VaccinePlace p2 WITH (NOLOCK) ON wp2.VaccinePlace_id = p2.VaccinePlace_id
					LEFT JOIN vac.S_VaccineWay w2 WITH (NOLOCK) ON wp2.VaccineWay_id = w2.VaccineWay_id
				WHERE FromTable.VaccinePlace_id = wp2.VaccineWayPlace_id
			) wp
			
			{$join}
			-- end from
			  " . ImplodeWherePH($filters) . "
			  ORDER BY
			-- order by
				FromTable.SurName, FromTable.FirName, FromTable.SecName
			-- end order by
		";
		//echo getDebugSQL($sql, $queryParams); die();
		if (!empty($data['getCountOnly']) && $data['getCountOnly'] == 1) {
			// подсчет только количества строк
			$get_count_query = getCountSQLPH($sql);

			$get_count_result = $this->db->query($get_count_query, $queryParams);

			if (is_object($get_count_result)) {
				$response['totalCount'] = $get_count_result->result('array');
				$response['totalCount'] = $response['totalCount'][0]['cnt'];
			}
		} else {		 
			if (isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0) {
				$limit_query = getLimitSQLPH($sql, $data['start'], $data['limit']);
				$result = $this->db->query($limit_query, $queryParams);
			} else {
				$result = $this->db->query($sql, $queryParams);
			}
			if (is_object($result)) {
				$res = $result->result('array');
				if (is_array($res)) {
					$response['data'] = $res;
					$response['totalCount'] = $data['start'] + count($res);
					if (count($res) >= $data['limit']) {
						$response['overLimit'] = true; // лимит весь вошел на страницу, а значит реальный каунт может отличаться от totalCount и пусть юезр запросит его сам, если он ему нужен
					}
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
		
		return $response;
			
	}

	/**
	 * Журнал Планирование туберкулинодиагностики (Поиск)
	 */

	public function searchTubPlan($data)
	{
		$filters = array();
		$join = '';
		$CardJoin = '';
		$queryParams = array();

		$this->genSearchFilters(TYPE_JOURNAL::TUB_PLAN, $data, $filters, $queryParams, $join, $CardJoin);

		$sql = "
			SELECT
			-- select
				FromTable.PlanTuberkulin_id
				,convert(varchar, FromTable.PlanTuberkulin_DatePlan, 104) [Date_Plan]
				,FromTable.SurName
				,FromTable.FirName
				,FromTable.SecName
				,FromTable.vac_Person_sex [sex]
				,convert(varchar, FromTable.vac_Person_BirthDay, 104) [BirthDay]
				,FromTable.PlanTuberkulin_Age [Age]
				--,FromTable.PlanTuberkulin_uch [uch]
				,FromTable.Address
				,FromTable.Lpu_id
				,FromTable.Lpu_Name
				,FromTable.Person_id
				,FromTable.Server_id
				,FromTable.PersonEvn_id
				,FromTable.Org_id
				,FromTable.Person_dead
				,L2.Lpu_Nick
				,PCard.Lpu_atNick
				,PCard.LpuRegion_Name as uch
			-- end select
			FROM
			-- from
			  vac.v_PlanTuberkulin FromTable WITH (NOLOCK)
			  outer apply(
				select top 1
					PC.PersonCard_id,
					PC.Person_id,
					PC.Lpu_id,
					PC.LpuRegion_id,
					ISNULL(L.Lpu_Nick,L.Lpu_Name) as Lpu_atNick,
					ISNULL(LR.LpuRegionType_Name,'') + ' №' + LR.LpuRegion_Name as LpuRegion_Name
				from v_PersonCard PC (nolock)
				left join v_Lpu L (nolock) on L.Lpu_id = PC.Lpu_id
				left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
				{$CardJoin}
				where PC.Person_id = FromTable.Person_id
			) PCard
			left join v_Lpu L2 (nolock) on L2.Lpu_id = FromTable.Lpu_id
			-- end from
			  " . ImplodeWherePH($filters) . "
			  ORDER BY
			-- order by
				FromTable.SurName, FromTable.FirName, FromTable.SecName
			-- end order by
		";

		if (!empty($data['getCountOnly']) && $data['getCountOnly'] == 1) {
			// подсчет только количества строк
			$get_count_query = getCountSQLPH($sql);

			$get_count_result = $this->db->query($get_count_query, $queryParams);

			if (is_object($get_count_result)) {
				$response['totalCount'] = $get_count_result->result('array');
				$response['totalCount'] = $response['totalCount'][0]['cnt'];
			}
		} else {		 
			if (isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0) {
				$limit_query = getLimitSQLPH($sql, $data['start'], $data['limit']);
				$result = $this->db->query($limit_query, $queryParams);
			} else {
				$result = $this->db->query($sql, $queryParams);
			}
			if (is_object($result)) {
				$res = $result->result('array');
				if (is_array($res)) {
					$response['data'] = $res;
					$response['totalCount'] = $data['start'] + count($res);
					if (count($res) >= $data['limit']) {
						$response['overLimit'] = true; // лимит весь вошел на страницу, а значит реальный каунт может отличаться от totalCount и пусть юезр запросит его сам, если он ему нужен
					}
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
		
		return $response;
		
		
	}

	/**
	 * Журнал Манту-назначено (Поиск)
	 */

	public function searchTubAssigned($data)
	{
		$filters = array();
		$join = '';
		$CardJoin = '';
		$queryParams = array();

		$this->genSearchFilters(TYPE_JOURNAL::TUB_ASSIGNED, $data, $filters, $queryParams, $join, $CardJoin);

		$sql = "
			SELECT
			-- select
				FromTable.JournalMantuFixed_id
				,convert(varchar, FromTable.JournalMantu_DatePurpose, 104) [Date_Purpose]
				,FromTable.SurName
				,FromTable.FirName
				,FromTable.SecName
				,FromTable.sex
				,convert(varchar, FromTable.BirthDay, 104) [BirthDay]
				,FromTable.JournalMantu_age [age]
				--,FromTable.JournalMantu_uch [uch]
				,FromTable.Lpu_id
				,FromTable.Lpu_Name
				,FromTable.Person_id
				,FromTable.Server_id
				,FromTable.PersonEvn_id
				,FromTable.Org_id
				,FromTable.Person_dead
				,L2.Lpu_Nick
				,PCard.Lpu_atNick
				,PCard.LpuRegion_Name as uch
			-- end select
			FROM
			-- from
				vac.v_JournalMantuFixed FromTable WITH (NOLOCK)
				outer apply(
					select top 1
						PC.PersonCard_id,
						PC.Person_id,
						PC.Lpu_id,
						PC.LpuRegion_id,
						ISNULL(L.Lpu_Nick,L.Lpu_Name) as Lpu_atNick,
						ISNULL(LR.LpuRegionType_Name,'') + ' №' + LR.LpuRegion_Name as LpuRegion_Name
					from v_PersonCard PC (nolock)
					left join v_Lpu L (nolock) on L.Lpu_id = PC.Lpu_id
					left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
					{$CardJoin}
					where PC.Person_id = FromTable.Person_id
				) PCard
				left join v_Lpu L2 (nolock) on L2.Lpu_id = FromTable.Lpu_id
			-- end from
			  " . ImplodeWherePH($filters) . "
			  ORDER BY
			-- order by
				FromTable.SurName, FromTable.FirName, FromTable.SecName
			-- end order by
		";

		// замена функции преобразования запроса в запрос для получения количества записей
		$count_sql = getCountSQLPH($sql);
		if (isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0) {
			$sql = getLimitSQLPH($sql, $data['start'], $data['limit']);
			log_message('debug', 'getLimitSQLPH(searchTubPlan)=' . $sql);
		}
		$res = $this->db->query($sql, $queryParams);
		// определение общего количества записей
		$count_res = $this->db->query($count_sql, $queryParams);
		if (is_object($count_res)) {
			$cnt_arr = $count_res->result('array');
			$count = $cnt_arr[0]['cnt'];
		} else
			return false;

		if (is_object($res)) {
			$response = $res->result('array');
			$response[] = array('__countOfAllRows' => $count);
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Журнал Манту-реакция (Поиск)
	 */

	public function searchTubReaction($data)
	{
		$filters = array();
		$join = '';
		$CardJoin = '';
		$queryParams = array();

		$this->genSearchFilters(TYPE_JOURNAL::TUB_REACTION, $data, $filters, $queryParams, $join, $CardJoin);

		$sql = "
			SELECT
			-- select
			 FromTable. JournalMantu_id [JournalMantuFixed_id]
				,convert(varchar, FromTable.JournalMantu_DatePurpose, 104) [Date_Purpose]
			  ,convert(varchar, FromTable.JournalMantu_DateVac, 104) [date_Vac]
			  ,FromTable.SurName
			  ,FromTable.FirName
			  ,FromTable.SecName
			  ,FromTable.vac_Person_sex [sex]
			  ,convert(varchar, FromTable.vac_Person_BirthDay, 104) [BirthDay]
			  ,FromTable.JournalMantu_age [age]
			  --,FromTable.JournalMantu_uch [uch]
			  ,FromTable.Lpu_id
			  ,FromTable.Lpu_Name
			  ,FromTable.Person_id
				,FromTable.Server_id
				,FromTable.PersonEvn_id
				,FromTable.JournalMantu_ReactDescription ReactDescription
				,convert(varchar, FromTable.JournalMantu_DateReact, 104) DateReact
				,FromTable.Org_id
					,FromTable.Person_dead
					,FromTable.TubDiagnosisType_id
				,FromTable.TubDiagnosisType_Name
			,L2.Lpu_Nick
			,PCard.Lpu_atNick
			,PCard.LpuRegion_Name as uch
			-- end select
			FROM
			-- from
			  vac.v_JournalMantuAccount FromTable WITH (NOLOCK)
			  outer apply(
				select top 1
					PC.PersonCard_id,
					PC.Person_id,
					PC.Lpu_id,
					PC.LpuRegion_id,
					ISNULL(L.Lpu_Nick,L.Lpu_Name) as Lpu_atNick,
					ISNULL(LR.LpuRegionType_Name,'') + ' №' + LR.LpuRegion_Name as LpuRegion_Name
				from v_PersonCard PC (nolock)
				left join v_Lpu L (nolock) on L.Lpu_id = PC.Lpu_id
				left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
				{$CardJoin}
				where PC.Person_id = FromTable.Person_id
			) PCard
			left join v_Lpu L2 (nolock) on L2.Lpu_id = FromTable.Lpu_id
			-- end from
			  " . ImplodeWherePH($filters) . "
			  ORDER BY
			-- order by
				FromTable.SurName, FromTable.FirName, FromTable.SecName
			-- end order by
		";

		// замена функции преобразования запроса в запрос для получения количества записей
		$count_sql = getCountSQLPH($sql);
		if (isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0) {
			$sql = getLimitSQLPH($sql, $data['start'], $data['limit']);
			log_message('debug', 'getLimitSQLPH(searchTubPlan)=' . $sql);
		}
		$res = $this->db->query($sql, $queryParams);
		// определение общего количества записей
		$count_res = $this->db->query($count_sql, $queryParams);
		if (is_object($count_res)) {
			$cnt_arr = $count_res->result('array');
			$count = $cnt_arr[0]['cnt'];
		} else
			return false;

		if (is_object($res)) {
			$response = $res->result('array');
			$response[] = array('__countOfAllRows' => $count);
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Журнал мед отводов/отказов (Поиск)
	 */

	public function searchVacRefuse($data)
	{//todo
		$filters = array();
		$join = '';
		$CardJoin = '';
		$queryParams = array();

		$this->genSearchFilters(TYPE_JOURNAL::VAC_REFUSE, $data, $filters, $queryParams, $join, $CardJoin);

		$sql = "
			SELECT
			-- select
			  FromTable.vacJournalMedTapRefusal_id
				,FromTable.Person_id
			  --,FromTable.vacJournalMedTapRefusal_uch [uch]
			  ,FromTable.SurName
			  ,FromTable.FirName
			  ,FromTable.SecName
			  ,FromTable.[sex]
			  ,convert(varchar, FromTable.BirthDay, 104) [BirthDay]
			  ,convert(varchar, FromTable.vacJournalMedTapRefusal_DateBegin, 104) DateBegin
			  ,convert(varchar, FromTable.vacJournalMedTapRefusal_DateEnd, 104) DateEnd
			  ,FromTable.vacJournalMedTapRefusal_Reason [Reason]
			  ,FromTable.vacJournalMedTapRefusal_NameTypeRec [type_rec]
			  ,FromTable.[Lpu_id]
			  ,FromTable.[Lpu_Name]
			  ,tp.VaccineType_Name
			  ,CASE
				WHEN FromTable.VaccineType_id = 1000 OR FromTable.vacJournalMedTapRefusal_VaccineTypeAll = 1
				 THEN 'Все прививки'
				ELSE tp.VaccineType_Name
			   END VaccineType_Name
				,FromTable.Server_id
				,FromTable.PersonEvn_id
				,convert(varchar, FromTable.vacJournalMedTapRefusal__insDT, 104) [DateRefusalSave]
				,FromTable.Org_id
					,FromTable.Person_dead
					,L2.Lpu_Nick
				,PCard.Lpu_atNick
				,PCard.LpuRegion_Name as uch
			-- end select
			FROM
			-- from
			  vac.v_JournalMedTapRefusal FromTable  WITH (NOLOCK)
			  LEFT JOIN vac.S_VaccineType tp  WITH (NOLOCK) ON FromTable.VaccineType_id = tp.VaccineType_id
			  outer apply(
					select top 1
						PC.PersonCard_id,
						PC.Person_id,
						PC.Lpu_id,
						PC.LpuRegion_id,
						ISNULL(L.Lpu_Nick,L.Lpu_Name) as Lpu_atNick,
						ISNULL(LR.LpuRegionType_Name,'') + ' №' + LR.LpuRegion_Name as LpuRegion_Name
					from v_PersonCard PC (nolock)
					left join v_Lpu L (nolock) on L.Lpu_id = PC.Lpu_id
					left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
					{$CardJoin}
					where PC.Person_id = FromTable.Person_id
				) PCard
				left join v_Lpu L2 (nolock) on L2.Lpu_id = FromTable.Lpu_id
			-- end from
			  " . ImplodeWherePH($filters) . "
			  ORDER BY
			-- order by
				FromTable.SurName, FromTable.FirName, FromTable.SecName
			-- end order by
		";

		// замена функции преобразования запроса в запрос для получения количества записей
		$count_sql = getCountSQLPH($sql);
		if (isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0) {
			$sql = getLimitSQLPH($sql, $data['start'], $data['limit']);
			log_message('debug', 'getLimitSQLPH(searchVacRefuse)=' . $sql);
		}
		$res = $this->db->query($sql, $queryParams);
		// определение общего количества записей
		$count_res = $this->db->query($count_sql, $queryParams);
		if (is_object($count_res)) {
			$cnt_arr = $count_res->result('array');
			$count = $cnt_arr[0]['cnt'];
			log_message('debug', 'countSQL=' . $count);
		} else
			return false;

		if (is_object($res)) {
			$response = $res->result('array');
			$response[] = array('__countOfAllRows' => $count);
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * ФУНКЦМЯ ВОЗВРАЩЕНИЯ ИМЕН ПОЛЕЙ
	 */
	function getFieldName($type, $param)
	{
		$resVal = '';
		$params = array(
			'default' => array(
				'age' => 'vacJournalAccount_Age',
				'uch' => 'vacJournalAccount_uch',
				'uch_id' => 'LpuRegion_id',
				//        'Lpu_id' => 'vac_Person_Lpu_id'
				'Lpu_id' => 'Lpu_id'
				, 'Org_id' => 'Org_id'
				, 'BirthDay' => 'BirthDay'
				, 'Sex'	=> 'Sex'
			),
			TYPE_JOURNAL::VAC_MAP => array(
				'age' => 'vacPersonKart_Age',
				'uch' => 'vac_Person_uch',
				//        'Lpu_id' => 'vac_Person_Lpu_id'
				'Lpu_id' => 'Lpu_id'
			),
			TYPE_JOURNAL::VAC_PLAN => array(
				'age' => 'vac_PersonPlanFinal_Age',
				'BirthDay' => 'vac_Person_BirthDay',
				'uch' => 'vac_PersonPlanFinal_uch',
				'Sex'	=> 'vac_Person_sex'
			),
			TYPE_JOURNAL::VAC_ASSIGNED => array(
				'BirthDay' => 'vac_Person_BirthDay',
				'Sex'	=> 'vac_Person_sex'
			),
			TYPE_JOURNAL::VAC_REFUSE => array(
				'uch' => 'vacJournalMedTapRefusal_uch',
				'Lpu_id' => 'Lpu_id'
			),
			TYPE_JOURNAL::VAC_REGISTR => array(
				'BirthDay' => 'vac_Person_BirthDay',
				'Sex'	=> 'vac_Person_sex'
			),
			TYPE_JOURNAL::TUB_PLAN => array(
				'age' => 'PlanTuberkulin_Age',
				'BirthDay' => 'vac_Person_BirthDay',
				'uch' => 'PlanTuberkulin_uch',
				'Sex'	=> 'vac_Person_sex'
			),
			TYPE_JOURNAL::TUB_ASSIGNED => array(
				'age' => 'JournalMantu_age',
				'uch' => 'JournalMantu_uch',
				'Lpu_id' => 'Lpu_id'
			),
			TYPE_JOURNAL::TUB_REACTION => array(
				'age' => 'JournalMantu_age',
				'BirthDay' => 'vac_Person_BirthDay',
				'uch' => 'JournalMantu_uch',
				'Sex'	=> 'vac_Person_sex'
			)
		);

		if (isset($params[$type])) {
			$arr = $params[$type];
			if (isset($arr[$param])) {
				$resVal = $arr[$param];
			} else {
				$arr = $params['default'];
				if (isset($arr[$param])) {
					$resVal = $arr[$param];
				}
			}
		}
		if ($resVal == '')
			$resVal = $param;
		return $resVal;
	}

	/**
	 * Параметры для загрузки журнала вакцинации
	 */
	function getVacMapFilters($type, $data, &$filters, &$queryParams, &$join)
	{
		If (ArrayVal($data, 'SurName') != '') {
			$filters[] = "PK." . $this->getFieldName($type, 'SurName') . " like :SurName";
			$queryParams['SurName'] = $data['SurName'] . '%';
		}
		If (ArrayVal($data, 'FirName') != '') {
			$filters[] = "PK." . $this->getFieldName($type, 'FirName') . " like :FirName";
			$queryParams['FirName'] = $data['FirName'] . '%';
		}
		If (ArrayVal($data, 'SecName') != '') {
			$filters[] = "PK." . $this->getFieldName($type, 'SecName') . " like :SecName";
			$queryParams['SecName'] = $data['SecName'] . '%';
		}
		If (ArrayVal($data, 'age') != '') {
			$filters[] = "PK." . $this->getFieldName($type, 'age') . " like :age";
			$queryParams['age'] = $data['age'] . '%';
		}
		/*If (ArrayVal($data, 'uch_id') != '') {
			switch (ArrayVal($data, 'uch_id')) {
				case -1:
					break;

				case 0:
					$filters[] = "(PK." . $this->getFieldName($type, 'uch_id') . " IS NULL or lpu_id <> lpu_id_attach)";
					break;

				default:
					$filters[] = "PK." . $this->getFieldName($type, 'uch_id') . " = :uch_id";
					break;
			}
			$queryParams['uch_id'] = $data['uch_id'];
		}*/

		
		If (ArrayVal($data, 'AttachMethod_id') != '') {
			switch (ArrayVal($data, 'AttachMethod_id')) {
				case 0:
					if (ArrayVal($data, 'OrgType_id') == 1)
						$filters[] = "PK." . $this->getFieldName($type, 'Org_id') . " IS NOT NULL";
					else if (ArrayVal($data, 'OrgType_id') == 2)
						$filters[] = "PK." . $this->getFieldName($type, 'Org_id') . " IS NULL";
					break;

				case 1:
					if ((ArrayVal($data, 'Org_id') != '') && (ArrayVal($data, 'Org_id') != 0))
						$filters[] = "PK." . $this->getFieldName($type, 'Org_id') . " = :Org_id";
					else
						$filters[] = "PK." . $this->getFieldName($type, 'Org_id') . " IS NOT NULL";
					break;
				default:
					break;
			}
			$queryParams['Org_id'] = $data['Org_id'];
		}

		if(ArrayVal($data,'Lpu_atid') != ''){
			$filters[] = "PCard.Lpu_id = :Lpu_atid";
			$queryParams['Lpu_atid'] = $data['Lpu_atid'];
		}
		If (ArrayVal($data, 'uch_id') != '') {
			switch (ArrayVal($data, 'uch_id')) {
				case -1:
					break;

				case 0:
					$filters[] = "(PCard.LpuRegion_id IS NULL)";
					break;

				default:
					$filters[] = "PCard.LpuRegion_id = :uch_id";
					break;
			}
			$queryParams['uch_id'] = $data['uch_id'];
		}

		log_message('debug', '$queryParams lpuId 1 =' . $data['Lpu_id']);
		log_message('debug', '$queryParams lpuId =' . $data['Lpu_id']);
		If ((ArrayVal($data, 'Lpu_id') != '') && (ArrayVal($data, 'SearchFormType') != 'Card63')) {
			$filters[] = "PK." . $this->getFieldName($type, 'Lpu_id') . " = :lpuId";
			$queryParams['lpuId'] = $data['Lpu_id'];

		}
		If (ArrayVal($data, 'Person_id') != '') {
			$filters[] = "PK." . $this->getFieldName($type, 'Person_id') . " = :personId";
			$queryParams['personId'] = $data['Person_id'];
		}

		if (isset($data['BirthDayRange'][1])) {
			$filters[] = "PK." . $this->getFieldName($type, 'BirthDay') . " <= :BirthDayRange1";
			$queryParams['BirthDayRange1'] = $data['BirthDayRange'][1];
		}
		if (isset($data['BirthDayRange'][0])) {
			$filters[] = "PK." . $this->getFieldName($type, 'BirthDay') . " >= :BirthDayRange0";
			$queryParams['BirthDayRange0'] = $data['BirthDayRange'][0];
		}

		if (isset($data['Date_Change'][1])) {
			//$filters[] = "[VacDateSave] <= :Date_Change1";
			$filters[] = "PK.[VacDateSave] < dateadd(day, 1,  :Date_Change1 )";
			$queryParams['Date_Change1'] = $data['Date_Change'][1];
		}
		if (isset($data['Date_Change'][0])) {
			$filters[] = "PK.[VacDateSave] >= :Date_Change0";
			$queryParams['Date_Change0'] = $data['Date_Change'][0];
		}

		if (ArrayVal($data, 'ImplVacOnly') == 'on') {
			$filters[] = "PK.[VacDateSave] IS NOT NULL";
		}
		
		//  Возраст с
		if (ArrayVal($data, 'PersonAge_AgeFrom') != '') {
			$filters[] = "PK.BirthDay <= dateadd(yyyy, -convert(integer, :PersonAge_AgeFrom), getDate())";
			$queryParams['PersonAge_AgeFrom'] = $data['PersonAge_AgeFrom'];
		}
		//  Возраст по
		if (ArrayVal($data, 'PersonAge_AgeTo') != '') {
			$filters[] = "PK.BirthDay > dateadd(yyyy, -convert(integer,:PersonAge_AgeTo), getDate())";
			$queryParams['PersonAge_AgeTo'] = $data['PersonAge_AgeTo'];
		}
		
		//  Пол
		if (ArrayVal($data, 'PersonSex_id') != '') {
			$filters[] = "PK.vac_Person_Sex_id = :PersonSex_id";
			$queryParams['PersonSex_id'] = $data['PersonSex_id'];
		}	
		
		// Адрес	
		if (isset($data ['PersonNoAddress']) && $data ['PersonNoAddress'] == 1){
			$filters[] = 'PS.PAddress_id is null';
			$join .= " 
				inner join v_PersonState PS (nolock) on PS.Person_id = PK.Person_id
			";
		} else {
			$countFilterAddress = 0;
			If (ArrayVal($data, 'KLCity_id') != '') {	
				$filters[] = 'A.KLCity_id = :KLCity_id';
				$queryParams['KLCity_id'] = $data['KLCity_id'];	
				$countFilterAddress++;
			}		
			If (ArrayVal($data, 'KLTown_id') != '') {	
				$filters[] = 'A.KLTown_id = :KLTown_id';
				$queryParams['KLTown_id'] = $data['KLTown_id'];
				$countFilterAddress++;
			}
			If (ArrayVal($data, 'KLStreet_id') != '') {
				$filters[] = 'A.KLStreet_id = :KLStreet_id';
				$queryParams['KLStreet_id'] = $data['KLStreet_id'];
				$countFilterAddress++;
			}
			If (ArrayVal($data, 'Address_House') != '') {
				$filters[] = 'A.Address_House = :Address_House';
				$queryParams['Address_House'] = $data['Address_House'];
				$countFilterAddress++;
			}
			if($countFilterAddress > 0){
				$join = " 
					inner join v_PersonState_all PS with (nolock) on PS.Person_id = PK.Person_id
					inner join v_Address_all A with(nolock) on PS.PAddress_id = A.Address_id
				";
			}
		}
	}

	/**
	 * Генерирует по переданным данным набор фильтров и джойнов
	 */

	function genSearchFilters($type, $data, &$filters, &$queryParams, &$join, &$CardJoin)
	{
		// 1. Основной фильтр
		$fldBirthDay = 'FromTable.vac_Person_BirthDay';
		
		if ($type == 'VacRefuse' || $type == 'TubAssigned')
			$fldBirthDay = 'FromTable.BirthDay';
		
		If (ArrayVal($data, 'SurName') != '') {
			//      $filters[] = "SurName like :SurName";
			$filters[] = "FromTable.".$this->getFieldName($type, 'SurName') . " like :SurName";
			$queryParams['SurName'] = $data['SurName'] . '%';
		}
		If (ArrayVal($data, 'FirName') != '') {
			//      $filters[] = "FirName like :FirName";
			$filters[] = "FromTable.".$this->getFieldName($type, 'FirName') . " like :FirName";
			$queryParams['FirName'] = $data['FirName'] . '%';
		}
		If (ArrayVal($data, 'SecName') != '') {
			//      $filters[] = "SecName like :SecName";
			$filters[] = "FromTable.".$this->getFieldName($type, 'SecName') . " like :SecName";
			$queryParams['SecName'] = $data['SecName'] . '%';
		}
		If (ArrayVal($data, 'age') != '') {
			//      $filters[] = "vacJournalAccount_Age like :age";
			$filters[] = "FromTable.".$this->getFieldName($type, 'age') . " like :age";
			$queryParams['age'] = $data['age'] . '%';
		}

		If (ArrayVal($data, 'uch_id') != '') {
			switch (ArrayVal($data, 'uch_id')) {
				case -1:
					break;

				case 0:
					$filters[] = "(PCard.LpuRegion_id IS NULL)";
					break;

				default:
					$filters[] = "PCard.LpuRegion_id = :uch_id";
					break;
			}
			$queryParams['uch_id'] = $data['uch_id'];
		}

		if(ArrayVal($data,'Lpu_atid') != '') {
			$filters['Lpu_atid'] = "(PCard.Lpu_id = :Lpu_atid)";
			$queryParams['Lpu_atid'] = $data['Lpu_atid'];
		}

		log_message('debug', 'AttachMethod_id=' . $data['AttachMethod_id']);
		If (ArrayVal($data, 'AttachMethod_id') != '') {
			switch (ArrayVal($data, 'AttachMethod_id')) {
				case 0:
					if (ArrayVal($data, 'OrgType_id') == 1)
						$filters[] = "FromTable.".$this->getFieldName($type, 'Org_id') . " IS NOT NULL";
					else if (ArrayVal($data, 'OrgType_id') == 2)
						$filters[] = "FromTable.".$this->getFieldName($type, 'Org_id') . " IS NULL";
					break;

				case 1:
					if ((ArrayVal($data, 'Org_id') != '') && (ArrayVal($data, 'Org_id') != 0))
						$filters[] = "FromTable.".$this->getFieldName($type, 'Org_id') . " = :Org_id";
					else
						$filters[] = "FromTable.".$this->getFieldName($type, 'Org_id') . " IS NOT NULL";
					break;
				default:
					break;
			}
			$queryParams['Org_id'] = $data['Org_id'];
		}

		log_message('debug', '$queryParams lpuId 1 =' . $data['Lpu_id']);
		//         log_message('debug', '$type =' . $type);
		//        if ( $type == TYPE_JOURNAL::VAC_4CabVac) {
		//            $data['Lpu_id'] = '';
		//        }
		log_message('debug', '$queryParams lpuId =' . $data['Lpu_id']);
		If ((ArrayVal($data, 'Lpu_id') != '') && (ArrayVal($data, 'SearchFormType') != 'Card63')) {
			//      $filters[] = "vac_Person_Lpu_id = :lpuId";
			$filters[] = "FromTable.".$this->getFieldName($type, 'Lpu_id') . " = :lpuId";
			$queryParams['lpuId'] = $data['Lpu_id'];

		}
		If (ArrayVal($data, 'Person_id') != '') {
			$filters[] = "FromTable.".$this->getFieldName($type, 'Person_id') . " = :personId";
			$queryParams['personId'] = $data['Person_id'];
		}

		if (isset($data['BirthDayRange'][1])) {
			$filters[] = "FromTable.".$this->getFieldName($type, 'BirthDay') . " <= :BirthDayRange1";
			$queryParams['BirthDayRange1'] = $data['BirthDayRange'][1];
		}
		if (isset($data['BirthDayRange'][0])) {
			$filters[] = "FromTable.".$this->getFieldName($type, 'BirthDay') . " >= :BirthDayRange0";
			$queryParams['BirthDayRange0'] = $data['BirthDayRange'][0];
		}
		//  Возраст с
		if (ArrayVal($data, 'PersonAge_AgeFrom') != '') {
			$filters[] = "{$fldBirthDay} <= dateadd(yyyy, -convert(integer, :PersonAge_AgeFrom), getDate())";
			$queryParams['PersonAge_AgeFrom'] = $data['PersonAge_AgeFrom'];
		}
		//  Возраст по
		if (ArrayVal($data, 'PersonAge_AgeTo') != '') {
			$filters[] = "{$fldBirthDay} > dateadd(yyyy, -convert(integer,:PersonAge_AgeTo), getDate())";
			$queryParams['PersonAge_AgeTo'] = $data['PersonAge_AgeTo'];
		}

		//  Пол
		if (ArrayVal($data, 'PersonSex_id') != '') { 
			$filters[] = "FromTable.".$this->getFieldName($type, 'Sex') . " = :PersonSex";  //and FromTable.vac_Person_sex = 'М'
			$queryParams['PersonSex'] = ($data['PersonSex_id'] == 1) ? 'М' : (($data['PersonSex_id'] == 2) ? 'Ж' : '');
		}
		
		// Адрес
		if (!empty($data ['PersonNoAddress']) && ($data ['PersonNoAddress'] == 1 || ArrayVal($data, 'KLCity_id') != '' || ArrayVal($data, 'KLTown_id') != '' || 
				ArrayVal($data, 'KLStreet_id') != '' || ArrayVal($data, 'Address_House') != ''))
		{
			$CardJoin = "inner join v_PersonState PK (nolock) on PK.Person_id = PC.Person_id
						inner join dbo.v_Address adr(nolock) on adr.Address_id = PK.PAddress_id
							";
			//echo 'test... '. $CardJoin;
			if (isset($data ['PersonNoAddress']) && $data ['PersonNoAddress'] == 1){
					$CardJoin = "
						inner join v_PersonState PK (nolock) on PK.Person_id = PC.Person_id
						and PK.PAddress_id is null";
			} else {
				If (ArrayVal($data, 'KLCity_id') != '') {	
					$CardJoin .=  " and KLCity_id = {$data['KLCity_id']}";			
				}	

				If (ArrayVal($data, 'KLTown_id') != '') {	
					$CardJoin .=  " and KLTown_id = {$data['KLTown_id']}";
				}
				If (ArrayVal($data, 'KLStreet_id') != '') {
					$CardJoin .=  " and KLStreet_id = {$data['KLStreet_id']}";
				}
				If (ArrayVal($data, 'Address_House') != '') {
					$CardJoin .=  " and Address_House = {$data['Address_House']}";
				}
			}
		}

		//доп. фильтры в завис-ти от типа журнала
		switch ($type) {
			case TYPE_JOURNAL::VAC_MAP:
				if (isset($data['Date_Change'][1])) {
					//$filters[] = "[VacDateSave] <= :Date_Change1";
					$filters[] = "[VacDateSave] < dateadd(day, 1,  :Date_Change1 )";
					$queryParams['Date_Change1'] = $data['Date_Change'][1];
				}
				if (isset($data['Date_Change'][0])) {
					$filters[] = "[VacDateSave] >= :Date_Change0";
					$queryParams['Date_Change0'] = $data['Date_Change'][0];
				}
				//				if (ArrayVal($data, 'checkbox_ImplVacOnly') != '')
				if (ArrayVal($data, 'ImplVacOnly') == 'on') {
					$filters[] = "[VacDateSave] IS NOT NULL";
				}
				break;
			case TYPE_JOURNAL::VAC_PLAN:
				if (isset($data['Date_Plan'][1])) {
					//$filters[] = "[vac_PersonPlanFinal_DatePlan] <= :Date_Plan1";
					$filters[] = "FromTable.vac_PersonPlanFinal_DatePlan <= @Date_Plan1";
					$queryParams['Date_Plan1'] = $data['Date_Plan'][1];
				}
				if (isset($data['Date_Plan'][0])) {
					//$filters[] = "[vac_PersonPlanFinal_DatePlan] >= :Date_Plan0";
					$filters[] = "FromTable.vac_PersonPlanFinal_DatePlan >= @Date_Plan0";
					$queryParams['Date_Plan0'] = $data['Date_Plan'][0];
				}
				If (ArrayVal($data, 'Name') != '') {
					$filters[] = "FromTable.VaccineType_Name like :Name";
					$queryParams['Name'] = '%' . $data['Name'] . '%';
				}
				If (ArrayVal($data, 'VaccineType_id') != '') {//Тип прививки
					$filters[] = "FromTable.VaccineType_id = :VaccineType_id";
					$queryParams['VaccineType_id'] = $data['VaccineType_id'];
				}
				break;
			case TYPE_JOURNAL::VAC_ASSIGNED:
				if (isset($data['Date_Purpose'][1])) {
					$filters[] = "FromTable.vacJournalAccount_DatePurpose <= :Date_Purpose1";
					$queryParams['Date_Purpose1'] = $data['Date_Purpose'][1];
				}
				if (isset($data['Date_Purpose'][0])) {
					$filters[] = "FromTable.vacJournalAccount_DatePurpose >= :Date_Purpose0";
					$queryParams['Date_Purpose0'] = $data['Date_Purpose'][0];
				}
				If (ArrayVal($data, 'vac_name') != '') {
					//$filters[] = "vac_name like :vac_name";
					$queryParams['vac_name'] = '%' . $data['vac_name'] . '%';
					$filters[] = "FromTable.Vaccine_name like :vac_name";
					//$queryParams['vac_name'] = '%' . $data['vac_name'] . '%';
				}
				If (ArrayVal($data, 'NAME_TYPE_VAC') != '') {
					//$filters[] = "NAME_TYPE_VAC like :NAME_TYPE_VAC";
					$queryParams['NAME_TYPE_VAC'] = '%' . $data['NAME_TYPE_VAC'] . '%';

					$filters[] = "FromTable.vacJournalAccount_Infection like :NAME_TYPE_VAC";
					//$queryParams['NAME_TYPE_VAC'] = '%' . $data['NAME_TYPE_VAC'] . '%';
				}
				If (ArrayVal($data, 'vacJournalAccount_id') != '') {
					$filters[] = "FromTable.vacJournalAccount_id = :vacJournalAccount_id";
					$queryParams['vacJournalAccount_id'] = $data['vacJournalAccount_id'];
				}
				If (ArrayVal($data, 'VaccineType_id') != '') {//Тип прививки
					$join = "
						inner join (Select distinct vacJournalAccount_id from vac.Inoculation  WITH (NOLOCK) 
							where VaccineType_id = :VaccineType_id
						) t on t.vacJournalAccount_id = FromTable.JournalVacFixed_id
					";
					$queryParams['VaccineType_id'] = $data['VaccineType_id'];
				}
				break;
			case TYPE_JOURNAL::VAC_REGISTR:
				if (isset($data['Date_Vac'][1])) {
					$filters[] = "FromTable.[vacJournalAccount_DateVac] <= :Date_Vac1";
					$queryParams['Date_Vac1'] = $data['Date_Vac'][1];
				}
				if (isset($data['Date_Vac'][0])) {
					$filters[] = "FromTable.[vacJournalAccount_DateVac] >= :Date_Vac0";
					$queryParams['Date_Vac0'] = $data['Date_Vac'][0];
				}
				If (ArrayVal($data, 'vac_name') != '') {
					$filters[] = "FromTable.Vaccine_name like :vac_name";
					$queryParams['vac_name'] = '%' . $data['vac_name'] . '%';
				}
				If (ArrayVal($data, 'NAME_TYPE_VAC') != '') {
					$filters[] = "FromTable.vacJournalAccount_Infection like :NAME_TYPE_VAC";
					$queryParams['NAME_TYPE_VAC'] = '%' . $data['NAME_TYPE_VAC'] . '%';
				}
				If (ArrayVal($data, 'vacJournalAccount_id') != '') {
					$filters[] = "FromTable.vacJournalAccount_id = :vacJournalAccount_id";
					$queryParams['vacJournalAccount_id'] = $data['vacJournalAccount_id'];
				}
				//Тип прививки
				If (ArrayVal($data, 'VaccineType_id') != '') {
					$join .= "
						INNER JOIN (
							SELECT DISTINCT vacJournalAccount_id FROM vac.Inoculation  WITH (NOLOCK)
							WHERE VaccineType_id = :VaccineType_id
						) t ON t.vacJournalAccount_id = FromTable.vacJournalAccount_id
					";
					$queryParams['VaccineType_id'] = $data['VaccineType_id'];
				}
				break;
			case TYPE_JOURNAL::TUB_PLAN:
				if (isset($data['Date_Plan'][1])) {
					$filters[] = "FromTable.[PlanTuberkulin_DatePlan] <= :Date_Plan1";
					$queryParams['Date_Plan1'] = $data['Date_Plan'][1];
				}
				if (isset($data['Date_Plan'][0])) {
					$filters[] = "FromTable.[PlanTuberkulin_DatePlan] >= :Date_Plan0";
					$queryParams['Date_Plan0'] = $data['Date_Plan'][0];
				}
				//        //вытаскиваем запланированные:
				//        $filters[] = "[Fixed] IS NOT NULL";
				break;
			case TYPE_JOURNAL::TUB_ASSIGNED:
				if (isset($data['Date_Purpose'][1])) {
					$filters[] = "FromTable.[JournalMantu_DatePurpose] <= :Date_Purpose1";
					$queryParams['Date_Purpose1'] = $data['Date_Purpose'][1];
				}
				if (isset($data['Date_Purpose'][0])) {
					$filters[] = "FromTable.[JournalMantu_DatePurpose] >= :Date_Purpose0";
					$queryParams['Date_Purpose0'] = $data['Date_Purpose'][0];
				}
				break;

			case TYPE_JOURNAL::VAC_REFUSE:
				break;
			case TYPE_JOURNAL::VAC_4CabVac:
				//const VAC_4CabVac = 'Vac4CabVac';

				log_message('debug', 'Search_BirthDay=' . $data['Search_BirthDay']);
				If (ArrayVal($data, 'Search_SurName') != '') {
					$filters[] = "FromTable.".$this->getFieldName($type, 'SurName') . " like :SurName";
					$queryParams['SurName'] = $data['Search_SurName'] . '%';
				}

				If (ArrayVal($data, 'Search_FirName') != '') {
					$filters[] = "FromTable.".$this->getFieldName($type, 'FirName') . " like :FirName";
					$queryParams['FirName'] = $data['Search_FirName'] . '%';
				}
				If (ArrayVal($data, 'Search_SecName') != '') {
					$filters[] = "FromTable.".$this->getFieldName($type, 'SecName') . " like :SecName";
					$queryParams['SecName'] = $data['Search_SecName'] . '%';
				}

				if (isset($data['Search_BirthDay'])) {
					$filters[] = "FromTable.".$this->getFieldName($type, 'BirthDay') . " = :BirthDay";
					$queryParams['BirthDay'] = $data['Search_BirthDay'];
					log_message('debug', 'Search_Birthday03=' . $queryParams['BirthDay']);
				}
				if (isset($data['begDate']) & isset($data['endDate'])) {
					log_message('debug', 'begDate=' . $data['begDate']);
					//$filters[] = $this->getFieldName($type, 'DatePurpose') . " >= :begDate";
					$filters[] = "((" . "FromTable.".$this->getFieldName($type, 'DatePurpose') . " >= :begDate and " . "FromTable.".$this->getFieldName($type, 'DatePurpose') . " <= :endDate) or
                            (" . "FromTable.".$this->getFieldName($type, 'DateVac') . " >= :begDate and " . "FromTable.".$this->getFieldName($type, 'DateVac') . " <= :endDate))";
					$queryParams['begDate'] = $data['begDate'];
					$queryParams['endDate'] = $data['endDate'];
				} else {
					$filters[] = " 1 > 2 ";   //  Такого быть гне должно
				}
				//}
				//if (isset($data['endDate'])) {
				//$filters[] = $this->getFieldName($type, 'DatePurpose') . " <= :endDate";
				//$filters[] = "(" .$this->getFieldName($type, 'DatePurpose') . " <= :endDate or ". $this->getFieldName($type, 'DateVac') . " <= :endDate";
				//$queryParams['endDate'] = $data['endDate'];
				//}
				/*
				if (isset($data['begDate'])) {
					   log_message('debug', 'begDate=' .$data['begDate']);
					 //$filters[] = $this->getFieldName($type, 'DatePurpose') . " >= :begDate";
					 $filters[] = "(" .$this->getFieldName($type, 'DatePurpose'). " >= :begDate or ". $this->getFieldName($type, 'DateVac'). " >= :begDate)";
					  $queryParams['begDate'] = $data['begDate'];
				}
				if (isset($data['endDate'])) {
					  //$filters[] = $this->getFieldName($type, 'DatePurpose') . " <= :endDate";
					$filters[] = "(" .$this->getFieldName($type, 'DatePurpose') . " <= :endDate or ". $this->getFieldName($type, 'DateVac') . " <= :endDate";
					$queryParams['endDate'] = $data['endDate'];
				 }
				 */


				if (isset($data['MedService_id'])) {
					if ($data['MedService_id'] == -1) {
						//  служба не определена
						$filters[] = "FromTable.".$this->getFieldName($type, 'MedService_id') . " is null";
					} elseif ($data['MedService_id'] != 0) {
						//  Указана служба
						$filters[] = "FromTable.".$this->getFieldName($type, 'MedService_id') . " = :MedService_id";
						$queryParams['MedService_id'] = $data['MedService_id'];
					}
				}
				break;

		}
	}

	/**
	 * Получаем список "Способы и место введения вакцины"
	 */
	public function GetVaccineWay($data)
	{

		//        $query = "
		//DECLARE @d1 datetime, @d2 datetime
		//SET @d1 = :birthday
		//SET @d2 = :datePurp
		//
		//SELECT pl.VaccinePlace_id VaccineWayPlace_id  -- Исправили справочник и вынуждены подставить VaccineWayPlace_id
		// -- wp.VaccineWayPlace_id
		//  , pl.VaccinePlace_Name+': ' + w.VaccineWay_Name VaccineWayPlace_Name
		//FROM vac.v_VaccineWayPlace wp with(nolock)
		//LEFT OUTER JOIN vac.S_VaccineWay w with(nolock) ON w.VaccineWay_id = wp.VaccineWay_id
		//LEFT OUTER JOIN vac.v_VaccinePlace pl with(nolock) ON pl.VaccinePlace_id = wp.VaccinePlace_id
		//  WHERE 
		//    wp.vaccine_id = :vaccineId
		//    AND VaccineWayPlace_AgeS <= DATEDIFF(m, 0, @d2 - @d1)
		//    AND VaccineWayPlace_AgeE >= DATEDIFF(m, 0, @d2 - @d1)
		//  --  AND vaccinePlace_Status = 1
		//    ";

		$query = "
			DECLARE @d1 datetime, @d2 datetime
			SET @d1 = :birthday
			SET @d2 = :datePurp

			 SELECT
			 --wp.VaccineWayPlace_id,
			 CONVERT(VARCHAR(10),ISNULL(wp.VaccineWayPlace_id, pl.VaccinePlace_id)) + ISNULL(CONVERT(VARCHAR(10),ISNULL(pl.VaccinePlace_id, pl_tmp.VaccinePlace_id)), '') AS id_VaccineWayPlace_VaccinePlace,
			 isnull(wp.VaccineWayPlace_id, pl.VaccinePlace_id) VaccineWayPlace_id,
			 --isnull(wp.VaccinePlace_id, pl.VaccinePlace_id) VaccineWayPlace_id,
			 case when  pl.VaccinePlace_id is not null then wp.VaccineWay_id else null end VaccineWay_id,
			 isnull(pl.VaccinePlace_id, pl_tmp.VaccinePlace_id) VaccinePlace_id  -- Исправили справочник и вынуждены подставить VaccineWayPlace_id
			  ,
			  case
				when pl.VaccinePlace_id is not null then
					 pl.VaccinePlace_Name+': ' + w.VaccineWay_Name
				else
					 pl_tmp.VaccinePlace_Name+': ' + w.VaccineWay_Name
			  end  VaccineWayPlace_Name

			FROM vac.v_VaccineWayPlace wp  WITH (NOLOCK)
			LEFT OUTER JOIN vac.S_VaccineWay w with(nolock) ON w.VaccineWay_id = wp.VaccineWay_id
			--  Это по новому
			LEFT OUTER JOIN vac.v_VaccinePlace pl with(nolock) ON pl.VaccineWay_id = wp.VaccineWay_id
				  --  and pl.VaccineWay_id = ISNULL(wp.VaccinePlace_id, pl.VaccineWay_id)
					 and pl.VaccinePlace_id = ISNULL(wp.VaccinePlace_id, pl.VaccinePlace_id)
					 and   pl.vaccinePlace_Status = 1
			 --  Это по старому
			 LEFT OUTER JOIN vac.v_VaccinePlace pl_tmp with(nolock) ON pl_tmp.VaccinePlace_id = wp.VaccinePlace_id
			  WHERE
				wp.vaccine_id = :vaccineId
				AND VaccineWayPlace_AgeS <= DATEDIFF(m, 0, @d2 - @d1)
				AND VaccineWayPlace_AgeE >= DATEDIFF(m, 0, @d2 - @d1)
			--        AND vaccinePlace_Status = 1
			order by pl.VaccinePlaceName_Name, pl.VaccinePlace_PlaseSide,  w.VaccineWay_Name
        ";

		$queryParams = array();
		$queryParams['vaccineId'] = $this->nvl($data['vaccine_id']);
		$queryParams['birthday'] = $this->nvl($data['birthday']);
		$queryParams['datePurp'] = $this->nvl($data['date_purpose']);
		//echo getDebugSQL($query, $queryParams);die;
		$result = $this->db->query($query, $queryParams);
		//    $result = $this->db->query($query);

		log_message('debug', 'GetVaccineWay=' . $query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получаем список "Доза введения вакцины"
	 */
	public function GetVaccineDoze($data)
	{
		$query = "
			DECLARE @d1 datetime, @d2 datetime
			DECLARE @Vaccine_id BIGINT

			SET @d1 = :birthday
			SET @d2 = :datePurp
			SET @Vaccine_id = :vaccineId

			SELECT
			VaccineDose_id, VaccineDose_Name
			FROM vac.v_VaccineDose  WITH (NOLOCK)
			WHERE Vaccine_id = @Vaccine_id
			AND VaccineDose_AgeS <= datename(yyyy, @d2 - CONVERT (varchar (10), @d1, 101))-1900
			AND VaccineDose_AgeE >= datename(yyyy, @d2 - CONVERT (varchar (10), @d1, 101))-1900
		";

		$queryParams = array();
		$queryParams['vaccineId'] = $this->nvl($data['vaccine_id']);
		$queryParams['birthday'] = $this->nvl($data['birthday']);
		$queryParams['datePurp'] = $this->nvl($data['date_purpose']);
		$result = $this->db->query($query, $queryParams);
		//    $result = $this->db->query($query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получаем список "Типов дозирования вакцины"
	 */
	public function GetDozeType()
	{
		$query = "
		SELECT
		DoseType_id
		,DoseType_Name
		FROM vac.S_DoseType  WITH (NOLOCK)
		";

		$result = $this->db->query($query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получаем список "Типов способов введения вакцины"
	 */
	public function GetWayType()
	{
		$query = "
		SELECT
		VaccineWay_id
		,VaccineWay_Name
		FROM vac.S_VaccineWay  WITH (NOLOCK)
		";

		$result = $this->db->query($query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получаем список "Типов мест введения вакцины"
	 */
	public function GetPlaceType($data)
	{

		$filter = " (1=1)";
		$filter = " VaccinePlace_status = 1";
		$filter = " VaccinePlaceHome_status = 1";
		if (isset($data['VaccineWay_id'])) {
			$filter .= " and VaccineWay_id = " . $data['VaccineWay_id'];
		}


		$query = "
			SELECT 
			  VaccinePlaceHome_id
			  ,VaccinePlaceName_Name VaccinePlace_Name
					,VaccineWay_id
			FROM vac.v_VaccinePlaceHome  WITH (NOLOCK)
				where {$filter}                
					order by VaccinePlaceName_Name
			";

		$result = $this->db->query($query);

		log_message('debug', '$query=' . $query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}


	/**
	 * Получаем список "Способы и место введения вакцины"
	 */
	public function GetVacPurpose()
	{
		$query = "
                    SELECT [VaccineWay_id]
                      ,VaccineWay_Name [Name]
                    FROM [vac].[S_VaccineWay]  WITH (NOLOCK)
                    ORDER BY VaccineWay_Name
                ";

		$result = $this->db->query($query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение назначенной прививки
	 */
	public function savePriviv($data)
	{
		$queryParams = array();
		$query = "
        declare
            @ErrCode int,
            @ErrMessage varchar(4000);
            
        exec vac.p_vac_JournalAccount_ins
            @KeyList                = :KeyList,           -- Строка идентификаторов записей плана через запятую
            @Date_Vac           = :Date_Purpose,      -- Дата назначения
            @lpu_id                 = :lpuId,             --  ЛПУ  
            @Vaccine_id             = :Vaccine_id,        -- Идентификатор вакцины  
            @Seria                  = :Seria,  -- Серия вакцины
            @Period                 = :Period,	-- Срок годности вакцины
            @Doza                   = :Doza,              -- Доза препарата
            @VaccineWayPlace_id     = :VaccineWayPlace_id,  --Идентификатор способа и места введения
            @Name_Vac               = :Name_Vac,          -- Наименование вакцины, если заполнение ведется не через Справочник
            @MedService_id          = :MedService_id,  --  ID службы, куда направлен пациент для вакцинации
            @MedPersonal_id         = :Purpose_MedPersonal_id, -- ID врача, назначившего вакцину
            @PmUser_id              = :Purpose_User_id,   -- ID пользователя, который назначил прививку
			@Parent                 = :Parent, --объект, на основании которого необходимо создать запись; 0 - v_vacPersonPlan0, 1 -  v_PersonPlanFinal
			@EvnVizitPL_id			= :EvnVizitPL_id,
            @Error_Code             = @ErrCode output,    -- Код ошибки
            @Error_Message          = @ErrMessage output -- Тект ошибки
            
        select @ErrCode as Error_Code, @ErrMessage as Error_Msg
    ";

		//    SELECT 777 as rescode
		//    $query = "
		//    SELECT 777 as Error_Code, 'Test' as Error_Msg
		//    ";
		//    $queryParams['lpuId'] = $this->nvl( (isset($data['Lpu_id'])) ? $data['Lpu_id'] : $_SESSION['lpu_id'] );
		$queryParams['lpuId'] = $this->nvl($_SESSION['lpu_id']);

		$queryParams['KeyList'] = $this->nvl($data['key_list']);
		$queryParams['Date_Purpose'] = $this->nvl($data['date_purpose']);
		$queryParams['Vaccine_id'] = $this->nvl($data['vaccine_id']);
		$queryParams['Seria'] = $this->nvl($data['vac_seria']);
		$queryParams['Period'] = $this->nvl($data['vac_period']);
		// 	   $queryParams['Doza']            = $this->nvl($data['doze_id']);
		$queryParams['Doza'] = $this->nvl($data['vac_doze']);
		//    $queryParams['VaccineWay_id']   = $this->nvl($data['vaccine_way_id']);
		//    $queryParams['VaccinePlace_id'] = $this->nvl($data['VaccinePlace_id']);
		$queryParams['VaccineWayPlace_id'] = $this->nvl($data['vaccine_way_place_id']);
		$queryParams['Name_Vac'] = $this->nvl($data['Name_Vac']);
		//    $queryParams['Purpose_MedPersonal_id'] = $this->nvl($data['Purpose_MedPersonal_id']);
		$queryParams['Purpose_MedPersonal_id'] = $this->nvl($data['med_staff_fact_id']);
		//    $queryParams['Purpose_User_id'] = $this->nvl($data['Purpose_User_id']);
		$queryParams['Purpose_User_id'] = $this->nvl($_SESSION['pmuser_id']);
		$queryParams['Parent'] = $this->nvl($data['row_plan_parent']);
		$queryParams['MedService_id'] = $this->nvl($data['medService_id']);
		$queryParams['EvnVizitPL_id'] = (!empty($data['EvnVizitPL_id'])) ? $data['EvnVizitPL_id'] : null;
		//    $queryParams['Error_Code']      = $this->nvl($data['Error_Code']);
		//    $queryParams['Error_Message']   = $this->nvl($data['Error_Message']);
		//    log_message('debug', 'date_purpose='.$this->nvl($queryParams['Doza']));
		log_message('debug', 'Doza=' . $queryParams['Doza']);
		
		//echo getDebugSQL($query, $queryParams); die();
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
			//return true;
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (назначение прививки)'));
			//    return false;
		}
	}

	/**
	 * Сохранение манту
	 */
	public function saveMantu($data)
	{
		$queryParams = array();
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000),
				@JournalMantu_id BIGINT;

			exec [vac].[p_vac_JournalMantu_ins]
			  @Person_id              = :Person_id, -- Идентификатор пациента
			  @Lpu_id                 = :Lpu_id,  -- Идентификатор ЛПУ
			  @PlanTuberkulin_id      = :PlanTuberkulin_id, -- идентификатор плана
			  @Date_Purpose           = :Date_Purpose, -- Дата назначения/исполнения (зависит от StatusType_id)
			  @DateReact              = :DateReact,  --  Дата описания реакции
			  @VacPresence_id         = :VacPresence_id, -- Идентификатор таблицы VacPresence
			  @Doza                   = :Doza, -- Доза препарата
			  @VaccineWayPlace_id     = :VaccineWayPlace_id, -- Идентификатор способа и места введения
			  @MedService_id          = :MedService_id,  --  ID службы, куда направлен пациент для вакцинации
			  @StatusType_id          = :StatusType_id, -- статус записи: 0 = назначена, 1 - исполнена
			  @Purpose_MedPersonal_id = :Purpose_MedPersonal_id, -- ID врача, назначившего вакцину
			  @Purpose_User_id        = :Purpose_User_id, -- ID пользователя, который назначил прививку
			  @MantuReactionType_id   = :MantuReactionType_id,  --  Идентификатор типа реакции
			@ReactionSize    = :ReactionSize,  -- Реакция Манту, [мм]
			@Reaction30min   = :Reaction30min,  -- Реакция на прививку (ч/з 30 мин)
			  @DiagnosisType   = :DiagnosisType,  -- Метод диагностики
			  @DiaskinTypeReaction   = :DiaskinTypeReaction,  -- Степень выраженности
			  @ReactDescription   = :JournalMantu_ReactDescription,  -- Описание реакции

			  @Error_Code             = @ErrCode output,    -- Код ошибки
			  @Error_Message          = @ErrMessage output, -- Тект ошибки
			  @JournalMantu_id        = @JournalMantu_id output  --  Идентификатор записи

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg
    	";
		//          @MantuReactionType_id   = :MantuReactionType_id,  --  Идентификатор типа реакции
		//          @ReactDescription       = :ReactDescription,  --  Описание реакции Манту
		//					@LocalReactDescription  = :LocalReactDescription,  --  Описание реакции на прививку

		$queryParams['Person_id'] = $this->nvl($data['person_id']);
		$queryParams['PlanTuberkulin_id'] = $this->nvl($data['plan_tub_id']);
		$queryParams['Date_Purpose'] = $this->nvl($data['date_purpose']);
		$queryParams['VacPresence_id'] = $this->nvl($data['vac_presence_id']);
		$queryParams['Doza'] = $this->nvl($data['vac_doze']);
		$queryParams['VaccineWayPlace_id'] = $this->nvl($data['vaccine_way_place_id']);
		$queryParams['StatusType_id'] = $this->nvl($data['status_type_id']);
		$queryParams['Purpose_MedPersonal_id'] = $this->nvl($data['med_staff_fact_id']);
		$queryParams['Purpose_User_id'] = $this->nvl($_SESSION['pmuser_id']);
		$queryParams['DateReact'] = $this->nvl($data['date_react']);
		$queryParams['MantuReactionType_id'] = $this->nvl($data['reaction_type']);
		$queryParams['ReactionSize'] = $this->nvl($data['reaction_size']);
		$queryParams['Reaction30min'] = $this->nvl($data['checkbox_reaction30min']);
		$queryParams['MedService_id'] = $this->nvl($data['medService_id']);
		//    $queryParams['ReactDescription']        = $this->nvl($data['reaction_desc']);
		//		$queryParams['LocalReactDescription']   = $this->nvl($data['local_reaction_desc']);
		$queryParams['Lpu_id'] = $this->nvl($data['lpu_id']);
		$queryParams['DiagnosisType'] = $this->nvl($data['diagnosis_type']);
		$queryParams['DiaskinTypeReaction'] = $this->nvl($data['diaskin_type_reaction']);
		$queryParams['JournalMantu_ReactDescription'] = $this->nvl($data['JournalMantu_ReactDescription']);


		log_message('debug', 'Lpu_id=' . $queryParams['Lpu_id']);
		log_message('debug', 'Date_Purpose=' . $queryParams['Date_Purpose']);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
			//return true;
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (назначение прививки)'));
			//    return false;
		}
	}

	/**
	 * Сохранение манту (исполнение и редактирование исполненных манту)
	 */
	public function saveMantuFixed($data)
	{
		$queryParams = array();

		//@Purpose_MedPersonal_id = :Purpose_MedPersonal_id, --  ID врача, назначившего вакцину
		//@Date_Purpose date = null, -- Дата назначения
		log_message('debug', 'saveMantuFixed: diagnosis_type = ' . $data['diagnosis_type']);

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec [vac].[p_vac_JournalMantu_upd]
				@JournalMantu_id        = :JournalMantuId,		-- Идентификатор  записи
				@Person_id              = :Person_id,			-- Идентификатор пациента
				@Lpu_id                 = :Lpu_id,				-- Идентификатор ЛПУ
				@Pmuser_id              = :Pmuser_id,			-- ID пользователя
				@Date_Vac               = :DateVac,				-- Дата вакцинации
				@Vac_MedPersonal_id     = :Vac_MedPersonal_id,	--  ID врача, исполнившего вакцинацию
				@VacPresence_id         = :VacPresence_id,		--  Идентификатор таблицы VacPresence
				@Mantu_Seria			= :Mantu_Seria,
				@Mantu_Period			= :Mantu_Period,
				@Doza                   = :Doza, -- Доза препарата
				@VaccineWayPlace_id     = :VaccineWayPlace_id,  --Идентификатор способа и места введения
				@VaccinePlace_id		= :VaccinePlace_id,		--Идентификатор места введения
				@StatusType_id          = :StatusType_id,		--  статус записи 0 = назначена, 1 - исполнена
				@MantuReactionType_id   = :MantuReactionType_id,--  Идентификатор типа реакции
				@ReactionSize			= :ReactionSize,		-- Реакция Манту, [мм]
				@Reaction30min			= :Reaction30min,		-- Реакция на прививку (ч/з 30 мин)
				@DiagnosisType			= :DiagnosisType,		-- Метод диагностики
				@DiaskinTypeReaction	= :DiaskinTypeReaction,	-- Степень выраженности
				@ReactDescription		= :JournalMantu_ReactDescription,  -- Описание реакции
				@DateReact              = :DateReact,			--  Дата описания реакции
				@Error_Code             = @ErrCode output,		-- Код ошибки
				@Error_Message          = @ErrMessage output	-- Тект ошибки

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg
    	";
		//          @ReactDescription       = :ReactDescription,  --  Описание реакции Манту
		//					@LocalReactDescription  = :LocalReactDescription,  --  Описание реакции на прививку

		$queryParams['JournalMantuId'] = $this->nvl($data['fix_tub_id']);
		$queryParams['Person_id'] = $this->nvl($data['person_id']);
		$queryParams['Lpu_id'] = $this->nvl($data['lpu_id']);
		$queryParams['Pmuser_id'] = $this->nvl($_SESSION['pmuser_id']);
		$queryParams['DateVac'] = $this->nvl($data['date_impl']);
		$queryParams['Vac_MedPersonal_id'] = $this->nvl($data['med_staff_fact_id']);
		$queryParams['VacPresence_id'] = $this->nvl($data['vac_presence_id']);
		$queryParams['Mantu_Seria'] = $this->nvl($data['vac_seria']);
		$queryParams['Mantu_Period'] = $this->nvl($data['vac_period']);
		$queryParams['Doza'] = $this->nvl($data['vac_doze']);
		$queryParams['VaccineWayPlace_id'] = $this->nvl($data['vaccine_way_place_id']);
		$queryParams['VaccinePlace_id'] = $this->nvl($data['vaccine_place_id']);
		$queryParams['StatusType_id'] = $this->nvl($data['status_type_id']);
		$queryParams['MantuReactionType_id'] = $this->nvl($data['reaction_type']);
		$queryParams['ReactionSize'] = $this->nvl($data['reaction_size']);
		$queryParams['Reaction30min'] = $this->nvl($data['checkbox_reaction30min']);
		//    $queryParams['ReactDescription']     = $this->nvl($data['reaction_desc']);
		//		$queryParams['LocalReactDescription'] = $this->nvl($data['local_reaction_desc']);
		$queryParams['DateReact'] = $this->nvl($data['date_react']);
		$queryParams['DiagnosisType'] = $this->nvl($data['diagnosis_type']);
		$queryParams['DiaskinTypeReaction'] = $this->nvl($data['diaskin_type_reaction']);
		$queryParams['JournalMantu_ReactDescription'] = $this->nvl($data['JournalMantu_ReactDescription']);


		//    log_message('debug', 'Date_Purpose='.$queryParams['Date_Purpose']);
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
			//return true;
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (исполнение и редактирование исполненных манту)'));
			//    return false;
		}
	}

	/**
	 * Сохранение Исполнения прививки минуя назначение
	 */
	public function saveImplWithoutPurp($data)
	{
		log_message('debug', 'saveImplWithoutPurp...');
		$queryParams = array();
		//log_message('debug', 'vacJournalAccountOld_id = '.$data['vacJournalAccountOld_id']);
		//return false;
		//        declare
		//            @ErrCode int,
		//            @ErrMessage varchar(4000);

		$queryDelete = "";

		if (isset($data['vacJournalAccountOld_id'])) {
			$queryDelete = "
                  exec vac.p_vac_JournalAccount_del
                            @vacJournalAccount_id = :vacJournalAccountOld_id, -- идентификатор записи
                            @PmUser_id            = :Vac_User_id, -- ID пользователя, который удалил прививку
                            @Error_Code           = @Error_Code output,    -- Код ошибки
                            @Error_Message        = @Error_Message output -- Текст ошибки

                    --select @Error_Code as Error_Code, @Error_Message as Error_Msg;
                 ";
		};

		$query = "
			Declare
			@vacJournalAccount_id BIGINT = :vacJournalAccount_id, -- = 0,
			@Error_Code bigint = 0,
			@Error_Message varchar(4000) = '';
			set nocount on
			begin try" . $queryDelete . "
			exec vac.p_vac_JournalAccount_ins
				@KeyList                            = :KeyList,           -- Строка идентификаторов записей плана через запятую
				@KeyListPlan                        = :KeyListPlan,           -- Строка идентификаторов записей плана через запятую (для функции изменения вакцин)
				@Date_Vac                           = :dateVac,      -- Дата назначения / исполнения
				@MedService_id                      = :medservice_id,  -- Идентификатор службы
				@MedPersonal_id                     = :medStaffImplId,  -- ID врача, назначившего/исполнившего прививку
				@LPU_ID                             = :lpuId,           --    Идентификатор ЛПУ
				@StatusType_id                      = :statustype_id,
				@Vaccine_id                         = :Vaccine_id,        -- Идентификатор вакцины
				@Seria                              = :Seria,  -- Серия вакцины
				@Period                             = :Period,	-- Срок годности вакцины
				@Doza                               = :Doza,              -- Доза препарата
				@VaccineWayPlace_id                 = :VaccineWayPlace_id,  --Идентификатор способа и места введения
				@Name_Vac                           = :Name_Vac,          -- Наименование вакцины, если заполнение ведется не через Справочник
				@PmUser_id                          = :Vac_User_id, -- ID пользователя, который назначил/исполнил прививку
				@Parent                             = :Parent, --объект, на основании которого необходимо создать запись; 0 - v_vacPersonPlan0, 1 -  v_PersonPlanFinal
				@vacJournalAccount_vacOther         = :vacOther, -- Признак прочей прививки (1-'прочие прививки')
				@Person_id                          = :person_id, --  идентификатор пациента
				@Error_Code                         = @Error_Code output,   -- Код ошибки
				@Error_Message                      = @Error_Message output, -- Тект ошибки
				@vacJournalAccount_id               = @vacJournalAccount_id output -- id назначенной прививки


			end try
			begin catch
			  set @Error_Code = error_number()
			  set @Error_Message = error_message()
			end catch
			set nocount off
			Select @vacJournalAccount_id as vacJournalAccount_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		//        select @Error_Code as Error_Code, @Error_Message as Error_Msg

		$queryParams['KeyList'] = $this->nvl($data['key_list']);
		$queryParams['KeyListPlan'] = $this->nvl($data['key_list_plan']);
		$queryParams['Date_Purpose'] = $this->nvl($data['date_purpose']);
		$queryParams['Vaccine_id'] = $this->nvl($data['vaccine_id']);
		$queryParams['Seria'] = $this->nvl($data['vac_seria']);
		$queryParams['Period'] = $this->nvl($data['vac_period']);
		$queryParams['Doza'] = $this->nvl($data['vac_doze']);
		$queryParams['VaccineWayPlace_id'] = $this->nvl($data['vaccine_way_place_id']);
		$queryParams['Name_Vac'] = $this->nvl($data['Name_Vac']);
		$queryParams['Purpose_MedPersonal_id'] = $this->nvl($data['med_staff_fact_id']);
		//    $queryParams['Purpose_User_id'] = $this->nvl($data['Purpose_User_id']);
		$queryParams['Purpose_User_id'] = null;
		$queryParams['Vac_User_id'] = $this->nvl($_SESSION['pmuser_id']);
		$queryParams['Parent'] = $this->nvl($data['row_plan_parent']);
		$queryParams['dateVac'] = $this->nvl($data['date_vac']);
		//    $queryParams['vacJaccountId'] = $this->nvl($data['vac_jaccount_id']);
		$queryParams['medStaffImplId'] = $this->nvl($data['med_staff_impl_id']);
		$queryParams['medservice_id'] = $this->nvl($data['medservice_id']);
		$queryParams['lpuId'] = $this->nvl($data['Lpu_id']);
		$queryParams['vacJournalAccountOld_id'] = $this->nvl($data['vacJournalAccountOld_id']);
		$queryParams['vacJournalAccount_id'] = $this->nvl($data['vacJournalAccount_id']);
		$queryParams['vacOther'] = $this->nvl($data['vacOther']);
		$queryParams['person_id'] = $this->nvl($data['person_id']);
		if (isset($data['statustype_id'])) {
			$queryParams['statustype_id'] = $this->nvl($data['statustype_id']);
		} else {
			$queryParams['statustype_id'] = 1;
		}


		$result = $this->db->query($query, $queryParams);

		// log_message('debug', 'saveImplWithoutPurp=' . $this->nvl($data['key_list']));
		log_message('debug', 'saveImplWithoutPurp: statustype_id =' . $data['statustype_id']);
		//log_message('debug', 'Vaccine_id???=' . $queryParams['Vaccine_id']);
		//log_message('debug', 'saveImplWithoutPurp(data)='.implode(" ",$data));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (Исполнение прививки минуя назначение)'));
		}
	}

	/**
	 * поиск прививок для выбранной вакцины (назначение прививки)
	 */

	public function loadSimilarRecords($data)
	{
		$queryParams = array();


		$queryUnion = "";
		$querySchemeNum = "";
		if (isset($data['plan_id'])) {
			if ($data['plan_id'] == -2) {
				$querySchemeNum = "
                    Select Top (1) @Scheme_num = NationalCalendarVac_Scheme_Num 
                    from  vac.v_Inoculation_All WITH (NOLOCK)
					where vacJournalAccount_id = :vacJournalAccount_id
        		";

				$queryUnion = "
                    where not exists (SElect 1  
	                        from vac.v_Inoculation_All i WITH (NOLOCK), vac.S_VaccineRelType rel WITH (NOLOCK)
                        where vacJournalAccount_id = :vacJournalAccount_id 
                            and rel.Vaccine_id = @Vaccine_id
                            and i.VaccineType_id = rel.VaccineType_id
                            and i.VaccineType_id = t.VaccineType_id)                    
                     union 
                        SElect 'Нет' as selRow, convert (varchar, Person_id) + '_' + NationalCalendarVac_Scheme_id PlanView_id, vacJournalAccount_id PlanFinal_id,convert(varchar, vacJournalAccount_DatePurpose, 104) Date_Plan, VaccineType_Name Name, typeName type_name
                        from vac.v_Inoculation_All i WITH (NOLOCK), vac.S_VaccineRelType rel WITH (NOLOCK)
                        where vacJournalAccount_id = :vacJournalAccount_id 
                            and rel.Vaccine_id = @Vaccine_id
                            and i.VaccineType_id = rel.VaccineType_id";
			};
		};
		/*
		$query = "
			Declare
				@Person_id bigint,
				@Vaccine_id bigint,
				@statusType_id BIGINT,
				@Scheme_num int,
				@plan_id int;

			Set @Person_id = :Person_id
			Set @Vaccine_id = :Vaccine_id
			Set @statusType_id = :statusType_id
			Set @Scheme_num = :Scheme_num
			Set @plan_id = :plan_id
			" . $querySchemeNum . "
			SELECT 'Нет' as selRow,
			PlanView_id, PlanFinal_id, convert(varchar, Date_Plan, 104) Date_Plan, Name, type_name
			from [vac].[fn_VacSimilarRecords] (@Person_id, @Vaccine_id, @statusType_id, @Scheme_num) s
		" . $queryUnion;
		*/
		//if($_SESSION['region']['nick'] == 'kareliya') //https://redmine.swan.perm.ru/issues/97543
		$query = "
			Declare
			@Person_id bigint,
			@Vaccine_id bigint,
			@status_type_id int,
			@Scheme_num int,
			@VaccineType_id int;


			Set @Person_id = :Person_id;
			Set @Vaccine_id = :Vaccine_id;
			Set @status_type_id = :statusType_id;
			Set @Scheme_num = :Scheme_num;
			Set @VaccineType_id = :VaccineType_id;

			with PPF as (
				/* 
					по логике запроса  надо найти минимальное значение  Min(NationalCalendarVac_Scheme_id)
					но поскольку это строковые значения, то при их сравнении получается что например 4.21.1 < 4.9.1
					потому создаю такую таблицу с двумя числами по NationalCalendarVac_Scheme_id
					по этим числам сгруппируем результат и получим нужное значение
				*/
				Select 
					VaccineType_id, 
					NationalCalendarVac_Scheme_id,
					--CONVERT(INT, SUBSTRING(NationalCalendarVac_Scheme_id,0,charindex('.',NationalCalendarVac_Scheme_id)) ) as Section,
					SUBSTRING(NationalCalendarVac_Scheme_id,0,charindex('.',NationalCalendarVac_Scheme_id)) as Section,
					CONVERT(FLOAT, SUBSTRING( NationalCalendarVac_Scheme_id,charindex('.',NationalCalendarVac_Scheme_id)+1, 10) ) as TownShip,
					pl.vac_PersonPlanFinal_DatePlan
				from vac.v_PersonPlanFinal pl  
				where  
					pl.Person_id = @Person_id
			)
			
			Select 
				'Нет' as selRow,

				PlanView_id, 
				PlanFinal_id, 

				convert(varchar(10), Date_Plan, 104) as Date_Plan,
				Name, 
				type_name
			from ( 
				Select
					t.MinDate, 
					pl.PersonPlan_id PlanView_id, 
					null PlanFinal_id,
					pl.vacPersonPlan0_DateS [Date_Plan], 
					pl.NationalCalendarVac_vaccineTypeName [Name], 
					pl.NationalCalendarVac_typeName [type_name], 
					pl.NationalCalendarVac_SequenceVac [SequenceVac],
					t.VaccineType_id
				from vac.v_vacPersonPlan0 pl, vac.S_VaccineRelType rel, 
					(
						Select 
							VaccineType_id,  
							Scheme_id,

							Dgt.Date_plan as MinDate

						from vac.v_vacPersonPlan0 pl0, 
						(
							Select 
								cast (Person_id as varchar) +  '_' + d.Scheme_id id, 
								d.Scheme_id, d.Date_plan 
							from vac.fn_getPersonVacDigest (@Person_id, null) d
							where 
								StatusType_id = -1 
								and Scheme_num = isnull(@Scheme_num, Scheme_num)
								/*and VaccineType_id = :VaccineType_id*/
								and VaccineType_id in(
									select VaccineType_id from vac.S_VaccineRelType where Vaccine_id = @Vaccine_id
								)
						) Dgt  
						where  
							pl0.Person_id = @Person_id 
							and pl0.PersonPlan_id = Dgt.id

					) t               
				where 
					pl.Person_id = @Person_id 
					and 1 = -1 * @status_type_id
					and rel.Vaccine_id = @Vaccine_id
					and pl.VaccineType_id = rel.VaccineType_id
					and t.VaccineType_id = rel.VaccineType_id
					and pl.NationalCalendarVac_Scheme_id = t.Scheme_id
					and (pl.NationalCalendarVac_SignPurpose like '%0' or pl.NationalCalendarVac_SignPurpose like '%' + pl.group_risk4Query)
				
				Union
				
				Select 
					t.MinDate,
					null PlanView_id, 
					pl.vac_PersonPlanFinal_id PlanFinal_id, 
					pl.vac_PersonPlanFinal_DatePlan [Date_Plan], 
					pl.VaccineType_Name [Name], 
					pl.typeName [type_name], 
					pl.NationalCalendarVac_SequenceVac [SequenceVac],
					t.VaccineType_id
				from vac.v_PersonPlanFinal pl, vac.S_VaccineRelType rel 
					/* ,(
						Select 
							VaccineType_id, 
							Min(NationalCalendarVac_Scheme_id) [Scheme_id], 
							min(pl.vac_PersonPlanFinal_DatePlan) MinDate
						from vac.v_PersonPlanFinal pl  
						where  
							pl.Person_id = @Person_id
							--and  pl.NationalCalendarVac_Scheme_Num = isnull(@Scheme_num, pl.NationalCalendarVac_Scheme_Num)
						group by VaccineType_id 
					) t */
					OUTER APPLY(
						SELECT TOP 1
							VaccineType_id, 
							NationalCalendarVac_Scheme_id AS Scheme_id,
							vac_PersonPlanFinal_DatePlan AS MinDate
						FROM
							PPF
						WHERE
							PPF.VaccineType_id = rel.VaccineType_id
						ORDER BY VaccineType_id, Section,  PPF.TownShip
					) t
				where pl.Person_id = @Person_id 
					and 1 =  case when @status_type_id = -1 then 0 else 1 end
					and rel.Vaccine_id = @Vaccine_id
					and pl.VaccineType_id = rel.VaccineType_id
					--and t.VaccineType_id = rel.VaccineType_id
					and pl.NationalCalendarVac_Scheme_id = t.Scheme_id
			) t
			/*where not exists (SElect 1  
                        from vac.v_Inoculation_All i WITH (NOLOCK), vac.S_VaccineRelType rel WITH (NOLOCK)
                    where vacJournalAccount_id = :vacJournalAccount_id 
                        and rel.Vaccine_id = @Vaccine_id
                        and i.VaccineType_id = rel.VaccineType_id
                        and i.VaccineType_id = t.VaccineType_id)                    
                 union 
                    SElect 'Нет' as selRow, convert (varchar, Person_id) + '_' + NationalCalendarVac_Scheme_id PlanView_id, vacJournalAccount_id PlanFinal_id,convert(varchar, vacJournalAccount_DatePurpose, 104) Date_Plan, VaccineType_Name Name, typeName type_name
                    from vac.v_Inoculation_All i WITH (NOLOCK), vac.S_VaccineRelType rel WITH (NOLOCK)
                    where vacJournalAccount_id = :vacJournalAccount_id 
                        and rel.Vaccine_id = @Vaccine_id
                        and i.VaccineType_id = rel.VaccineType_id*/
		".$queryUnion;

		$queryParams['Person_id'] = $this->nvl($data['person_id']);
		$queryParams['Vaccine_id'] = $this->nvl($data['vaccine_id']);
		$queryParams['statusType_id'] = $this->nvl($data['status_type_id']);
		$queryParams['Scheme_num'] = $this->nvl($data['scheme_num']);
		$queryParams['plan_id'] = $this->nvl($data['plan_id']);
		$queryParams['vacJournalAccount_id'] = $this->nvl($data['vacJournalAccount_id']);
		$queryParams['VaccineType_id'] = $this->nvl($data['vac_type_id']);


		log_message('debug', 'loadSimilarRecords: $query=' . $query);
		//echo getDebugSQL($query, $queryParams);die;
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			//var_dump($result->result('array'));die;
			return $result->result('array');
			//return true;
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (назначение прививки)'));
			//    return false;
		}
	}

	/**
	 * поиск похожих записей для выбранной прививки (назначение прививки)
	 */
	public function loadVaccine4Other($data)
	{
		$queryParams = array();
		$errorMsg = "Ошибка при выполнении запроса к базе данных (Поиск записей для выбранной прививки)";

		$query = "
        Declare
            @Vaccine_id bigint = :Vaccine_id,
            @Person_id bigint = :Person_id;

        SELECT 
               Vaccine_id, VaccineType_id, convert(varchar, GETDATE (), 104) Date_Plan, VaccineType_Name, Type_Name
                    FROM vac.v_VaccineRelType WITH (NOLOCK)
                where Vaccine_id = @Vaccine_id";

		$queryParams['Vaccine_id'] = $this->nvl($data['vaccine_id']);
		$queryParams['Person_id'] = $this->nvl($data['person_id']);

		$result = $this->db->query($query, $queryParams);

		log_message('debug', 'loadVaccine4Other: $query=' . $query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (Поиск записей для выбранной прививки)'));
		}

	} //  end loadVaccine4Other

	/**
	 * Список вакцин
	 */

	public function loadVaccineList($data)
	{
		$queryParams = array();

		log_message('debug', 'loadVaccineList: birthday=' . $data['birthday']);

		log_message('debug', 'loadVaccineList: StoreKeyList=' . $data['StoreKeyList']);

		if (isset($data['StoreKeyList'])) {
			$filter = "VaccineType_id in (" . $data['StoreKeyList'] . ") ";
		} else if(!empty($data["vac_type_id"])) {
			$filter = "VaccineType_id = " . $data["vac_type_id"];
		} else {
			return false;
		}
		//SET @d1 = '20110503' --:BirthDay

		$query = "
			Declare
			  @VaccineType_id bigint, -- Идентификатор прививки
			  @Age int,     -- возраст
			  @d1 datetime, -- Дата рождения
			  @d2 datetime  -- Дата планирования

			  SET @VaccineType_id = :VaccineType_id  -- Присвоим для теста

			  SET @d1 = :BirthDay
			  SET @d2 = :Date_Plan

			  SET @Age = vac.GetAge(@d1, @d2)

			SELECT v.Vaccine_id, v.Vaccine_FullName [GRID_NAME_VAC]
			FROM vac.v_Vaccine v  WITH (NOLOCK), vac.S_VaccineRelType rel  WITH (NOLOCK)
			WHERE ($filter)
			--rel.VaccineType_id = @VaccineType_id AND
			and v.Vaccine_id = rel.Vaccine_id
			AND (v.Vaccine_AgeBegin IS NULL OR
			(v.Vaccine_AgeBegin <= @Age AND Vaccine_AgeEnd >=@Age))
			group by  v.Vaccine_id, v.Vaccine_FullName
		";

		$queryParams['VaccineType_id'] = $this->nvl($data['vac_type_id']);
		$queryParams['BirthDay'] = $this->nvl($data['birthday']);
		$queryParams['Date_Plan'] = $this->nvl($data['date_purpose']);

		$result = $this->db->query($query, $queryParams);

		log_message('debug', 'loadVaccineList: $query=' . $query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (Список вакцин)'));
		}
	}

	//  /**
	//   * Сохранение Исполненной прививки (OLD)
	//   */
	//  public function savePrivivImplement_old($data) {
	//    $queryParams = array();
	//
	//    $query = "
	//      Declare
	//        @vacJaccountId INT = :vacJaccountId,
	//        @Error_Code bigint = 0,
	//        @Error_Message varchar(4000) = '';
	//      set nocount on
	//      begin try
	//        UPDATE vac.vac_JournalAccount
	//        SET
	//          vacJournalAccount_DateVac = :dateVac,
	//          vacJournalAccount_Vac_MedPersonal_id = :medStaffImplId,
	//          vacJournalAccount_StatusType_id = 1,
	//          vacJournalAccount_vac_PmUser_id = :Vac_User_id,
	//          vacJournalAccount_vacDateSave = dbo.tzGetDate(),
	//          vacJournalAccount_ReactlocaDescription = :reactLocalDesc,
	//          vacJournalAccount_ReactGeneralDescription = :reactGeneralDesc
	//        WHERE vacJournalAccount_id = @vacJaccountId
	//      end try
	//        begin catch
	//          set @Error_Code = error_number()
	//          set @Error_Message = error_message()
	//        end catch
	//      set nocount off
	//      Select @vacJaccountId as vacJournalAccount_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
	//    ";
	//
	////    $query = "
	////      Declare
	////        @vacJaccountId INT = :vacJaccountId,
	////        @Error_Code bigint = 0,
	////        @Error_Message varchar(4000) = '';
	////      set nocount on
	////      begin try
	////        SELECT 1
	////      end try
	////      begin catch
	////        set @Error_Code = error_number()
	////        set @Error_Message = error_message()
	////      end catch
	////      set nocount off
	////      Select @Error_Code as Error_Code, @Error_Message as Error_Msg
	////    ";
	//
	//    $queryParams['dateVac'] = $this->nvl($data['date_vac']);
	//    $queryParams['vacJaccountId'] = $this->nvl($data['vac_jaccount_id']);
	//    $queryParams['medStaffImplId'] = $this->nvl($data['med_staff_impl_id']);
	//    $queryParams['reactLocalDesc'] = $this->nvl($data['react_local_desc']);
	//    $queryParams['reactGeneralDesc'] = $this->nvl($data['react_general_desc']);
	//    $queryParams['Vac_User_id'] = $this->nvl($_SESSION['pmuser_id']);
	//    log_message('debug', 'medStaffImplId='.$queryParams['medStaffImplId']);
	//
	//    $result = $this->db->query($query, $queryParams);
	//
	//    if ( is_object($result) ) {
	//      return $result->result('array');
	//    }
	//    else {
	//      return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (Исполнение прививки)'));
	//    }
	//  }

	/**
	 * Сохранение и редактирование Исполненной прививки (TODO - добавить реакцию)
	 */
	public function savePrivivImplement($data)
	{
		$queryParams = array();
		$errorMsg = "Ошибка при выполнении запроса к базе данных (Исполнение прививки)";
		$query4del = "";
		if (isset($data['vacJournalAccountOld_id'])) {

			$query4del = "
                  exec vac.p_vac_JournalAccount_del
				@vacJournalAccount_id = :vacJournalAccountOld_id, -- идентификатор записи для удаления
				@PmUser_id            = :PmUser_id, -- ID пользователя, который удалил прививку
				@Error_Code           = @ErrCode output,    -- Код ошибки
				@Error_Message        = @ErrMessage output -- Текст ошибки
            
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg
            ";

		}

		$query = "
			declare
			@ErrCode int,
			@ErrMessage varchar(4000);

			exec [vac].[p_vac_JournalAccount_upd]
						@vacJournalAccount_id		= :vac_jaccount_id,  -- идентификатор записи
						@dateVac					= :date_vac, -- Дата исполнения
						@LPU_ID                     = :lpuId,           --    Идентификатор ЛПУ
						@MedService_id              = :medservice_id,   --  ID службы вакцинации
						@Seria 						= :vac_seria, -- Серия вакцины
						@Period						= :vac_period,	-- Срок годности вакцины
						@VaccineWayPlace_id			= :VaccineWayPlace_id,  --Идентификатор способа и места введения
						@MedPersonal_id 			= :med_staff_impl_id, --  ID врача, исполнившего вакцину
						@PmUser_id					= :Vac_User_id, -- ID пользователя, который внес вседения об исполнении прививки
						@reactLocalDesc				= :reactLocalDesc, -- местная реакция описание
						@reactGeneralDesc			= :reactGeneralDesc, -- общая реакция описание
			  			@Error_Code             	= @ErrCode output,    -- Код ошибки
			  			@Error_Message          	= @ErrMessage output -- Тект ошибки

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg 
    	";
		//					@Person_id							= ,  -- Идентификатор пациента
		//					@LPU_ID									= ,  -- Идентификатор ЛПУ
		//					@Vaccine_id							= ,  --  Идентификатор вакцины  
		//					@VaccineWayPlace_id 		= ,  --Идентификатор способа и места введения
		//					@Name_Vac								= ,  -- Наименование вакцины, если заполнение ведется не через Справочник
		//					@Seria 									= ,  -- Серия вакцины
		//					@Period									= ,	-- Срок годности вакцины
		//					@Doza 									= , -- Доза препарата
		//	@VaccinePlace_id			--Идентификатор места введения

		$queryParams['date_vac'] = $this->nvl($data['date_vac']);
		$queryParams['vac_jaccount_id'] = $this->nvl($data['vac_jaccount_id']);
		$queryParams['vac_seria'] = $this->nvl($data['vac_seria']);
		$queryParams['vac_period'] = $this->nvl($data['vac_period']);
		$queryParams['med_staff_impl_id'] = $this->nvl($data['med_staff_impl_id']);
		$queryParams['reactLocalDesc'] = $this->nvl($data['react_local_desc']);
		$queryParams['reactGeneralDesc'] = $this->nvl($data['react_general_desc']);
		$queryParams['Vac_User_id'] = $this->nvl($_SESSION['pmuser_id']);
		$queryParams['lpuId'] = $this->nvl($data['Lpu_id']);
		$queryParams['medservice_id'] = $this->nvl($data['medservice_id']);
		$queryParams['vacJournalAccountOld_id'] = $this->nvl($data['vacJournalAccountOld_id']); 
		$queryParams['VaccineWayPlace_id'] = $this->nvl($data['vaccine_way_place_id']);
		
		$result = $this->db->query($query, $queryParams);
		log_message('debug', 'med_staff_impl_id=' . $queryParams['med_staff_impl_id']);
		log_message('debug', 'vac_seria=' . $queryParams['vac_seria']);
		log_message('debug', 'vac_seria=' . $queryParams['Vac_User_id']);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => $errorMsg, 'success' => false));
		}
	}

	/**
	 * Сохранение медотвода
	 */
	public function savePrivivRefuse($data)
	{
		//$errorCode = 111;
		$errorMsg = 'Ошибка при выполнении запроса к базе данных (Сохранение медотвода)';
		//global $_USER;
		//    if ($_SESSION['pmuser_id'] != $this->nvl($data['user_id']))
		//      return array(array('Error_Msg' => $errorMsg, 'success' => false));

		$queryParams = array();

		/*
		  $query = "
		  Declare
		  @Error_Code bigint = 0,
		  @Error_Message varchar(4000) = '';
		  set nocount on
		  begin try

		  INSERT INTO [vac].[vac_JournalMedTapRefusal] (
		  [Person_id]
		  ,[VaccineType_id]
		  ,[TypeRecord]
		  ,[vacJournalMedTapRefusal_DateBegin]
		  ,[vacJournalMedTapRefusal_DateEnd]
		  ,[vacJournalMedTapRefusal_Reason]
		  ,[pmUser_insID]
		  ,[vacJournalMedTapRefusal_MedPersonal_id]
		  ,[VaccineType_All]
		  )
		  VALUES (
		  :person_id
		  ,:vaccine_type_id
		  ,:refusal_type_id
		  ,:date_refuse_range0
		  ,:date_refuse_range1
		  ,:vac_refuse_cause
		  ,:user_id
		  ,:med_staff_refuse_id
		  ,:vaccine_type_all
		  )

		  end try
		  begin catch
		  set @Error_Code = error_number()
		  set @Error_Message = error_message()
		  end catch
		  set nocount off
		  Select @Error_Code as Error_Code, @Error_Message as Error_Msg
		  ";
		 */

		$query = "
		  Declare
			 @ErrCode bigint = 0,
			 @ErrMessage varchar(4000) = '';


		  exec vac.p_vac_JournalMedTapRefusal_ins
				@Person_id              = :Person_id,
				@VaccineType_id         = :VaccineType_id,
				@TypeRecord             = :TypeRecord,
				@DateBegin              = :DateBegin,
				@DateEnd                = :DateEnd,
				@Reason                 = :Reason,
				@pmUser_id              = :pmUser_id,
				@MedPersonal_id         = :MedPersonal_id,
				@VaccineTypeAll         = :VaccineTypeAll,
							@refuse_id              = :refuse_id,

				@Error_Code             = @ErrCode output,    -- Код ошибки
				@Error_Message          = @ErrMessage output -- Тект ошибки

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg

		 ";


		$queryParams['Person_id'] = $this->nvl($data['person_id']);
		$queryParams['VaccineType_id'] = $this->nvl($data['vaccine_type_id']);
		$queryParams['DateBegin'] = $this->nvl($data['date_refuse_range'][0]);
		$queryParams['DateEnd'] = $this->nvl($data['date_refuse_range'][1]);
		$queryParams['Reason'] = $this->nvl($data['vac_refuse_cause']);
		$queryParams['TypeRecord'] = $this->nvl($data['refusal_type_id']);
		$queryParams['pmUser_id'] = $this->nvl($data['user_id']);
		$queryParams['VaccineTypeAll'] = $this->nvl($data['vaccine_type_all']);
		$queryParams['refuse_id'] = $this->nvl($data['refuse_id']);
		log_message('debug', 'refuse_id=' . $queryParams['refuse_id']);
		//    $queryParams['user_id'] = $this->nvl($_SESSION['pmuser_id']);
		//    $queryParams['refuse_date'] = $this->nvl($data['refuse_date']);
		$queryParams['MedPersonal_id'] = $this->nvl($data['med_staff_refuse_id']);
		//log_message('debug', 'user_id='.$queryParams['medStaffImplId']);
		//log_message('debug', 'user_id='.$_SESSION['pmuser_id']);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => $errorMsg, 'success' => false));
		}
	}

	/**
	 * Сохранение вакцины (справочник вакцин)
	 */

	public function saveSprVaccine($data)
	{
		$queryParams = array();
		$errorMsg = "Ошибка при выполнении запроса к базе данных (Сохранение вакцины)";

		$query = "
                     declare
                        @Error_Code int,
                        @Error_Message varchar(4000)
                        
                    exec vac.p_Vaccine_ins
                                @VaccineName  = :VaccineName,
                                @TypeInfections = :TypeInfections,
				@VaccineNick = :VaccineNick,
				@VaccineAgeBegin = :VaccineAgeBegin,
				@VaccineAgeEnd = :VaccineAgeEnd,
				@VaccineSignComb = :VaccineSignComb,
                                @VaccineId = :VaccineId,
				@DozaAge = :DozaAge,
				@Doza1 = :DozaVal1,
				@Doza2 = :DozaVal2,				
                                @DozeType1= :DozeType1,
				@DozeType2 = :DozeType2,
                                @WayAge = :WayAge,
				@placeType1 = :placeType1,
				@placeType2 = :placeType2,
				@wayType1 = :wayType1,
				@wayType2 = :wayType2,
                                @Error_Code             = @Error_Code output,    -- Код ошибки
                                @Error_Message          = @Error_Message output -- Тект ошибки
                                
                      select @Error_Code as Error_Code, @Error_Message as Error_Msg 
                                
        ";

		$queryParams['VaccineId'] = $this->nvl($data['Vaccine_id']);
		$queryParams['VaccineName'] = $this->nvl($data['Vaccine_Name']);
		$queryParams['VaccineSignComb'] = $this->nvl($data['VaccineSignComb']);
		$queryParams['VaccineNick'] = $this->nvl($data['Vaccine_Nick']);
		$queryParams['VaccineAgeBegin'] = $this->nvl($data['AgeRange1']);
		$queryParams['VaccineAgeEnd'] = $this->nvl($data['AgeRange2']);
		$queryParams['TypeInfections'] = $this->nvl($data['TypeInfections']);
		$queryParams['DozaAge'] = $this->nvl($data['DozaAge']);
		$queryParams['DozaVal1'] = $this->nvl($data['DozaVal1']);
		$queryParams['DozaVal2'] = $this->nvl($data['DozaVal2']);
		$queryParams['DozeType1'] = $this->nvl($data['DozeType1']);
		$queryParams['DozeType2'] = $this->nvl($data['DozeType2']);
		$queryParams['WayAge'] = $this->nvl($data['WayAge']);
		$queryParams['placeType1'] = $this->nvl($data['placeType1']);
		$queryParams['placeType2'] = $this->nvl($data['placeType2']);
		$queryParams['wayType1'] = $this->nvl($data['wayType1']);
		$queryParams['wayType2'] = $this->nvl($data['wayType2']);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => $errorMsg, 'success' => false));
		}

	}


	//*********

	/**
	 * Сохранение схем для дополнительных прививок
	 */

	public function saveSprOtherVacScheme($data)
	{
		$queryParams = array();
		$errorMsg = "Ошибка при выполнении запроса к базе данных (Сохранение схем для дополнительных прививок)";

		$query = "
                     declare
                        @Error_Code int,
                        @Error_Message varchar(4000)
                        
                    exec vac.p_S_OtherVacScheme_ins
                                @Vaccine_id  = :Vaccine_id,
                                @AgeTypeS1 = :AgeTypeS1,
				@AgeS1 = :AgeS1,
				@AgeTypeS2 = :AgeTypeS2,
				@AgeS2 = :AgeS2,
				@AgeE1 = :AgeE1,
                                @AgeE2 = :AgeE2,
				@Multiplicity1 = :Multiplicity1,
				@Multiplicity2 = :Multiplicity2,
				@MultiplicityRisk1 = :MultiplicityRisk1,				
                                @MultiplicityRisk2 = :MultiplicityRisk2,
				@Interval1 = :Interval1,
                                @Interval2 = :Interval2,
				@IntervalRisk1 = :IntervalRisk1,
				@IntervalRisk2 = :IntervalRisk2,
				@PmUser_id = :PmUser_id,
				@Error_Code             = @Error_Code output,    -- Код ошибки
                                @Error_Message          = @Error_Message output -- Тект ошибки
                                
                      select @Error_Code as Error_Code, @Error_Message as Error_Msg
                                
                                ";

		$queryParams['PmUser_id'] = $this->nvl($_SESSION['pmuser_id']);
		$queryParams['Vaccine_id'] = $this->nvl($data['Vaccine_id']);
		$queryParams['AgeTypeS1'] = $this->nvl($data['AgeTypeS1']);
		$queryParams['AgeS1'] = $this->nvl($data['AgeS1']);
		$queryParams['AgeTypeS2'] = $this->nvl($data['AgeTypeS2']);
		$queryParams['AgeS2'] = $this->nvl($data['AgeS2']);
		$queryParams['AgeE1'] = $this->nvl($data['AgeE1']);
		$queryParams['AgeE2'] = $this->nvl($data['AgeE2']);
		$queryParams['Multiplicity1'] = $this->nvl($data['Multiplicity1']);
		$queryParams['Multiplicity2'] = $this->nvl($data['Multiplicity2']);
		$queryParams['MultiplicityRisk1'] = $this->nvl($data['MultiplicityRisk1']);
		$queryParams['MultiplicityRisk2'] = $this->nvl($data['MultiplicityRisk2']);
		$queryParams['Interval1'] = $this->nvl($data['Interval1']);
		$queryParams['Interval2'] = $this->nvl($data['Interval2']);
		$queryParams['IntervalRisk1'] = $this->nvl($data['IntervalRisk1']);
		$queryParams['IntervalRisk2'] = $this->nvl($data['IntervalRisk2']);
		$queryParams['wayType1'] = $this->nvl($data['wayType1']);
		$queryParams['wayType2'] = $this->nvl($data['wayType2']);


		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => $errorMsg, 'success' => false));
		}

	}


	/**
	 * Сохранение вакцины (справочник вакцин)  СТАРОЕ
	 */
	public function saveSprVaccine2($data)
	{
		$queryParams = array();
		$errorMsg = "Ошибка при выполнении запроса к базе данных (Сохранение вакцины)";


		$query = "
			Declare
			@VaccineName varchar(200) = :VaccineName,
			@VaccineNick varchar(10) = :VaccineNick,
			@VaccineAgeBegin INT = :VaccineAgeBegin,
			@VaccineAgeEnd INT = :VaccineAgeEnd,
			@VaccineSignComb INT = :VaccineSignComb,
			@VaccineId INT = :VaccineId,
			@DozaAge INT = :DozaAge,
			@DozaVal1 DECIMAL(7,3) = :DozaVal1,
			@DozaVal2 DECIMAL(7,3) = :DozaVal2,
			@DozeType1 INT = :DozeType1,
			@DozeType2 INT = :DozeType2,
			@WayAge INT = :WayAge,
			@placeType1 INT = :placeType1,
			@placeType2 INT = :placeType2,
			@wayType1 INT = :wayType1,
			@wayType2 INT = :wayType2,
			@Error_Code bigint = 0,
			@Error_Message varchar(4000) = '';

		  set nocount on
		  begin try
				 begin tran

			IF @VaccineId IS NOT NULL
					BEGIN
					DELETE FROM [vac].[S_VaccineRelType] WHERE [Vaccine_id] = @VaccineId

						UPDATE vac.S_Vaccine
						SET
						Vaccine_Name = @VaccineName,
						Vaccine_Nick = @VaccineNick,
						Vaccine_SignComb = @VaccineSignComb,
						Vaccine_AgeBegin = @VaccineAgeBegin,
						Vaccine_AgeEnd = @VaccineAgeEnd
						WHERE Vaccine_id = @VaccineId
					END
					ELSE
					BEGIN
					  INSERT vac.S_Vaccine
						([Vaccine_Name] ,[Vaccine_SignComb] ,[Vaccine_Nick] ,[Vaccine_AgeBegin] ,[Vaccine_AgeEnd])
						SELECT @VaccineName, @VaccineSignComb, @VaccineNick, @VaccineAgeBegin, @VaccineAgeEnd

			  SELECT @VaccineId = scope_identity()
					END

			INSERT [vac].[S_VaccineRelType]
					 SELECT
					  @VaccineId
						,SUBSTRING(a.paramval, 1,
								CASE
								 WHEN (CHARINDEX(':', a.paramval) > 0) THEN (CHARINDEX(':', a.paramval) - 1)
								 ELSE LEN(a.paramval)
								END
							)
						--,SUBSTRING(a.paramval, CHARINDEX(':', a.paramval) + 1, LEN(a.paramval) - CHARINDEX(':', a.paramval)) val
						,NULL
						,NULL
						,NULL
						,NULL
					 FROM vac.list2tableDelim(:TypeInfections, ',') a

		  --Доза (begin)
			UPDATE vac.[S_VaccineDose]
					SET
					 dose_status = 0 --удаляем запись
					WHERE Vaccine_id = @VaccineId

			IF @DozaAge=1000
					BEGIN
						INSERT vac.[S_VaccineDose]
							([Vaccine_id], [VaccineDose_AgeS], [VaccineDose_AgeE], VaccineDose_Dose, [VaccineDose_DoseType], [dose_status])
							SELECT @VaccineId, 0, @DozaAge, @DozaVal1, @DozeType1, 1
					END
					ELSE
					BEGIN
					  INSERT vac.[S_VaccineDose]
							([Vaccine_id], [VaccineDose_AgeS], [VaccineDose_AgeE], VaccineDose_Dose, [VaccineDose_DoseType], [dose_status])
							SELECT @VaccineId, 0, @DozaAge, @DozaVal1, @DozeType1, 1
							UNION
							SELECT @VaccineId, @DozaAge, 1000, @DozaVal2, @DozeType2, 1
					END
				--Доза (end)

		  --Способ ввода (begin)
			UPDATE vac.[S_VaccineWayPlace]
					SET
					 wayPlace_status = 0 --удаляем запись
					WHERE Vaccine_id = @VaccineId

			IF @WayAge=10000
					BEGIN
						INSERT vac.[S_VaccineWayPlace]
							([Vaccine_id], [VaccineWayPlace_AgeS], [VaccineWayPlace_AgeE], VaccineWay_id, [VaccinePlaceHome_id], [wayPlace_status])
							SELECT @VaccineId, 0, @WayAge, @wayType1, @placeType1, 1
					END
					ELSE
					BEGIN
					  INSERT vac.[S_VaccineWayPlace]
							([Vaccine_id], [VaccineWayPlace_AgeS], [VaccineWayPlace_AgeE], VaccineWay_id, [VaccinePlaceHome_id], [wayPlace_status])
							SELECT @VaccineId, 0, @WayAge, @wayType1, @placeType1, 1
							UNION
							SELECT @VaccineId, @WayAge, 10000, @wayType2, @placeType2, 1
					END
				--Способ ввода (end)

				 commit tran
		  end try

		  begin catch
			set @Error_Code = error_number()
			set @Error_Message = error_message()
		  end catch
		  set nocount off

		  Select @VaccineId as Vaccine_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
    	";

		$queryParams['VaccineId'] = $this->nvl($data['Vaccine_id']);
		$queryParams['VaccineName'] = $this->nvl($data['Vaccine_Name']);
		$queryParams['VaccineSignComb'] = $this->nvl($data['VaccineSignComb']);
		$queryParams['VaccineNick'] = $this->nvl($data['Vaccine_Nick']);
		$queryParams['VaccineAgeBegin'] = $this->nvl($data['AgeRange1']);
		$queryParams['VaccineAgeEnd'] = $this->nvl($data['AgeRange2']);
		$queryParams['TypeInfections'] = $this->nvl($data['TypeInfections']);
		$queryParams['DozaAge'] = $this->nvl($data['DozaAge']);
		$queryParams['DozaVal1'] = $this->nvl($data['DozaVal1']);
		$queryParams['DozaVal2'] = $this->nvl($data['DozaVal2']);
		$queryParams['DozeType1'] = $this->nvl($data['DozeType1']);
		$queryParams['DozeType2'] = $this->nvl($data['DozeType2']);
		$queryParams['WayAge'] = $this->nvl($data['WayAge']);
		$queryParams['placeType1'] = $this->nvl($data['placeType1']);
		$queryParams['placeType2'] = $this->nvl($data['placeType2']);
		$queryParams['wayType1'] = $this->nvl($data['wayType1']);
		$queryParams['wayType2'] = $this->nvl($data['wayType2']);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => $errorMsg, 'success' => false));
		}
	}

	/**
	 * Удаление медотвода
	 */
	public function deletePrivivRefuse($data)
	{
		$errorMsg = 'Ошибка при выполнении запроса к базе данных (Удаление медотвода)';

		$queryParams = array();

		$query = "
		  Declare
			 @ErrCode bigint = 0,
			 @ErrMessage varchar(4000) = '';

		  exec vac.p_vac_JournalMedTapRefusal_del
							@refuse_id              = :refuse_id,

				@Error_Code             = @ErrCode output,    -- Код ошибки
				@Error_Message          = @ErrMessage output -- Тект ошибки

				select @ErrCode as Error_Code, @ErrMessage as Error_Msg
     	";

		$queryParams['refuse_id'] = $this->nvl($data['refuse_id']);
		log_message('debug', 'deletePrivivRefuse refuse_id=' . $queryParams['refuse_id']);
		//    $queryParams['MedPersonal_id'] = $this->nvl($data['med_staff_refuse_id']);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => $errorMsg, 'success' => false));
		}
	}

	/**
	 * Удаление вакцины из справочника вакцин
	 */
	public function deleteSprVaccine($data)
	{
		$errorMsg = 'Ошибка при выполнении запроса к базе данных (Удаление вакцины)';

		$queryParams = array();

		//    @PmUser_id = :PmUser_id,
		//		@Vaccine_id = :Vaccine_id,
		$query = "
			Declare
					@StatusType_id BIGINT = 100,
					@Error_Code bigint = 0, -- Код ошибки
					@Error_Message varchar(4000) = ''; -- Текст ошибки
			set nocount on
			begin try
			begin tran

				update vac.S_Vaccine
					Set StatusType_id = @StatusType_id,
						  Del_PmUser_id = :PmUser_id,
						  DelDateSave = GETDATE()
					where [Vaccine_id] = :Vaccine_id;

			commit tran
			END TRY
			BEGIN CATCH
				set @Error_Code = error_number()
				set @Error_Message = error_message()
				if @@trancount>0
					rollback tran
			END CATCH
			set nocount off
			  SELECT @Error_Code as Error_Code, @Error_Message as Error_Msg
     	";

		$queryParams['PmUser_id'] = $this->nvl($_SESSION['pmuser_id']);
		$queryParams['Vaccine_id'] = $this->nvl($data['vaccine_id']);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => $errorMsg, 'success' => false));
		}
	}

	/**
	 * Удаление исполненной прививки
	 */
	public function deletePrivivImplement($data)
	{
		$errorMsg = 'Ошибка при выполнении запроса к базе данных (Удаление исполненной прививки)';

		$queryParams = array();

		$query = "
		  Declare
			 @ErrCode bigint = 0,
			 @ErrMessage varchar(4000) = '';

		  exec vac.p_vac_JournalAccount_del
					@vacJournalAccount_id = :vacJournalAccount_id, -- идентификатор записи
					@PmUser_id            = :PmUser_id, -- ID пользователя, который удалил прививку
					@Error_Code           = @ErrCode output,    -- Код ошибки
					@Error_Message        = @ErrMessage output -- Текст ошибки

				select @ErrCode as Error_Code, @ErrMessage as Error_Msg
     	";

		$queryParams['PmUser_id'] = $this->nvl($_SESSION['pmuser_id']);
		$queryParams['vacJournalAccount_id'] = $this->nvl($data['vacJournalAccount_id']);
		log_message('debug', 'PmUser_id=' . $queryParams['PmUser_id']);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => $errorMsg, 'success' => false));
		}
	}

	/**
	 * Превышен интерал вакцинации
	 */
	public function vac_interval_exceeded($data)
	{
		$errorMsg = 'Ошибка при выполнении запроса к базе данных (Удаление исполненной прививки)';

		$queryParams = array();

		$query = "
		  Declare
			 @ErrCode bigint = 0,
			 @ErrMessage varchar(4000) = '';

		  exec vac.p_vac_interval_exceeded
					@Inoculation_id = :Inoculation_id, -- идентификатор записи
					@PmUser_id            = :PmUser_id, -- ID пользователя, который удалил прививку
					@Error_Code           = @ErrCode output,    -- Код ошибки
					@Error_Message        = @ErrMessage output -- Текст ошибки

				select @ErrCode as Error_Code, @ErrMessage as Error_Msg
     	";

		$queryParams['PmUser_id'] = $this->nvl($_SESSION['pmuser_id']);
		$queryParams['Inoculation_id'] = $this->nvl($data['Inoculation_id']);
		log_message('debug', 'PmUser_id=' . $queryParams['PmUser_id']);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => $errorMsg, 'success' => false));
		}
	}


	/**
	 * Удаление исполненной прививки манту
	 */
	public function deleteMantu($data)
	{
		$errorMsg = 'Ошибка при выполнении запроса к базе данных (Удаление прививки манту)';

		$queryParams = array();

		$query = "
      Declare
         @ErrCode bigint = 0,
         @ErrMessage varchar(4000) = '';
     
      exec vac.p_vac_JournalMantu_del
				@JournalMantu_id = :JournalMantu_id, -- идентификатор записи
				@PmUser_id            = :PmUser_id, -- ID пользователя, который удалил прививку
				@Error_Code           = @ErrCode output,    -- Код ошибки
				@Error_Message        = @ErrMessage output -- Текст ошибки
            
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg
     ";

		$queryParams['PmUser_id'] = $this->nvl($_SESSION['pmuser_id']);
		$queryParams['JournalMantu_id'] = $this->nvl($data['JournalMantu_id']);
		//		log_message('debug', 'PmUser_id='.$queryParams['PmUser_id']);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => $errorMsg, 'success' => false));
		}
	}

	//  /*****************************************************************************
	//   * Загрузка доп инфы для формы исполнения прививки (режим редактирования)
	//   */
	//  public function loadImplFormInfoEdit($data) {
	//    $queryParams = array();
	//    
	////    $query = "
	////  SELECT ac.vacJournalAccount_id, p.Name+': '+w.Name VaccineWay_name, ac.Doza, ac.StatusType_id
	////  FROM [vac].[vac_JournalAccount] ac
	////  LEFT JOIN vac.S_VaccineWay w with(nolock) ON ac.VaccineWay_id = w.VaccineWay_id
	////  LEFT JOIN vac.S_VaccinePlace p with(nolock) ON ac.VaccinePlace_id = p.VaccinePlace_id
	////  WHERE ac.vacJournalAccount_id = :vacJaccountId
	////    ";
	//    
	//    $query = "
	//DECLARE
	// @MED_PERS_ID INT;
	//
	//SELECT TOP 1
	//  @MED_PERS_ID = s.MedStaffFact_id
	// FROM [dbo].[v_pmUser] u
	// LEFT JOIN [dbo].v_MedStaffFact s
	//  ON u.pmUser_Medpersonal_id = s.MedPersonal_id
	// WHERE PMUser_id = :userId
	//
	//SELECT TOP 1
	//  @MED_PERS_ID MedPers_id_user,
	//  ac.vacJournalAccount_id,
	//  p.VaccinePlace_Name+': '+w.VaccineWay_Name VaccineWay_name,
	//  ac.vacJournalAccount_Dose Doza,
	//  ac.vacJournalAccount_StatusType_id StatusType_id,
	//  ac.vacJournalAccount_Purpose_MedPersonal_id MedPers_id_purp,
	//  convert(varchar, ac.vacJournalAccount_DateVac, 104) Date_impl,
	//  ac.vacJournalAccount_Vac_MedPersonal_id MedPers_id_impl,
	//  ac.vacJournalAccount_ReactlocaDescription ReactLocalDesc,
	//  ac.vacJournalAccount_ReactGeneralDescription ReactGeneralDesc
	//	,convert(varchar, ac.vacJournalAccount_DatePurpose, 104) [Date_Purpose]
	//	,ac.vacJournalAccount_Infection NAME_TYPE_VAC
	//	,convert(varchar, ac.vac_Person_BirthDay, 104) [BirthDay]
	//	,ac.Vaccine_name vac_name
	//	,ac.Vaccine_id
	//	,ac.Person_id
	//-- FROM [vac].[vac_JournalAccount] ac
	// FROM [vac].[v_JournalAccount] ac
	//-- FROM [vac].v_Inoculation_All ac
	// LEFT JOIN vac.S_VaccineWay w with(nolock) ON ac.VaccineWay_id = w.VaccineWay_id
	// LEFT JOIN vac.S_VaccinePlace p with(nolock) ON ac.VaccinePlace_id = p.VaccinePlace_id
	// WHERE ac.vacJournalAccount_id = :vacJaccountId
	//    ";
	//
	//    $queryParams['vacJaccountId'] = $this->nvl($data['vac_jaccount_id']);
	//    $queryParams['userId'] = $this->nvl($data['user_id']);
	//
	//    $result = $this->db->query($query, $queryParams);
	//
	//    if (is_object($result)) {
	//      return $result->result('array');
	//    } else {
	//      return array(array('Error_Msg' => 'Ошаибка при выполнении запроса к базе данных (доп инфа для формы исполнения прививки)'));
	//    }
	//  }

	/**
	 * Загрузка доп инфы для формы исполнения прививки
	 */
	public function loadImplFormInfo($data)
	{
		$queryParams = array();

		$query = "
			DECLARE
				@MED_PERS_ID BIGINT;

			SELECT TOP 1
				@MED_PERS_ID = s.MedStaffFact_id
			FROM [dbo].[v_pmUser] u WITH (NOLOCK)
				LEFT JOIN [dbo].v_MedStaffFact s WITH (NOLOCK)
				ON u.pmUser_Medpersonal_id = s.MedPersonal_id
			WHERE PMUser_id = :userId

			SELECT TOP 1
				@MED_PERS_ID MedPers_id_user,
				ac.vacJournalAccount_id,
				case 
					when p.VaccinePlace_Name is null and p2.VaccinePlace_Name is null and  ac.VaccinePlace_id is not null then
						w2.VaccineWay_Name
					when p.VaccinePlace_Name is null and ac.VaccinePlace_id is not null then 
						p2.VaccinePlace_Name+': '+w2.VaccineWay_Name 
					else p.VaccinePlace_Name+': '+w.VaccineWay_Name 
				end as VaccineWay_name,
				ac.vacJournalAccount_Dose Doza,
				ac.vacJournalAccount_StatusType_id StatusType_id,
				ac.vacJournalAccount_Purpose_MedPersonal_id MedPers_id_purp,
				convert(varchar, ac.vacJournalAccount_DateVac, 104) Date_impl,
				ac.vacJournalAccount_Vac_MedPersonal_id MedPers_id_impl,
				ac.vacJournalAccount_ReactlocaDescription ReactLocalDesc,
				ac.vacJournalAccount_ReactGeneralDescription ReactGeneralDesc
				,convert(varchar, ac.vacJournalAccount_DatePurpose, 104) [Date_Purpose]
				,replace (ac.vacJournalAccount_Infection, '<br />', '')  NAME_TYPE_VAC
				,convert(varchar, ac.vac_Person_BirthDay, 104) [BirthDay]
				,ac.Vaccine_name vac_name
				,ac.Vaccine_id
				,ac.Person_id
				,ac.vacJournalAccount_Seria Seria
				,convert(varchar, ac.vacJournalAccount_Period, 104) vacPeriod
				,v.Vaccine_AgeBegin
				,v.Vaccine_AgeEnd
				,ac.Lpu_id
				,ac.Medservice_id   
				, ac.vacJournalAccount_vacOther vacOther
				,MS.LpuBuilding_id
				,VJA.EvnVizitPL_id
				,VJA.DocumentUcStr_id
			FROM [vac].[v_JournalAccountAll] ac WITH (NOLOCK)
				LEFT JOIN vac.S_VaccineWay w WITH (NOLOCK) ON ac.VaccineWay_id = w.VaccineWay_id
				LEFT JOIN vac.v_VaccinePlace p WITH (NOLOCK) ON ac.VaccinePlace_id = p.VaccinePlace_id
				LEFT JOIN vac.v_VaccineWayPlace wp2 WITH (NOLOCK) ON ac.VaccinePlace_id = wp2.VaccineWayPlace_id
				LEFT JOIN vac.v_VaccinePlace p2 WITH (NOLOCK) ON wp2.VaccinePlace_id = p2.VaccinePlace_id
				LEFT JOIN vac.S_VaccineWay w2 WITH (NOLOCK) ON wp2.VaccineWay_id = w2.VaccineWay_id
				LEFT JOIN vac.v_Vaccine v WITH (NOLOCK) ON v.Vaccine_id = ac.Vaccine_id
				LEFT JOIN v_MedService MS on MS.MedService_id = ac.MedService_id
				OUTER APPLY(
					SELECT TOP 1 VJA.EvnVizitPL_id, VJA.DocumentUcStr_id FROM vac.vac_JournalAccount VJA WHERE VJA.vacJournalAccount_id = ac.vacJournalAccount_id
				) VJA
			WHERE ac.vacJournalAccount_id = :vacJaccountId

			UNION

			SELECT TOP 1
				@MED_PERS_ID MedPers_id_user,
				ac.vacJournalAccount_id,
				p.VaccinePlace_Name+': '+w.VaccineWay_Name VaccineWay_name,
				ac.vacJournalAccount_Dose Doza,
				ac.vacJournalAccount_StatusType_id StatusType_id,
				ac.vacJournalAccount_Purpose_MedPersonal_id MedPers_id_purp,
				convert(varchar, ac.vacJournalAccount_DateVac, 104) Date_impl,
				ac.vacJournalAccount_Vac_MedPersonal_id MedPers_id_impl,
				ac.vacJournalAccount_ReactlocaDescription ReactLocalDesc,
				ac.vacJournalAccount_ReactGeneralDescription ReactGeneralDesc
				,convert(varchar, ac.vacJournalAccount_DatePurpose, 104) [Date_Purpose]
				,replace (ac.vacJournalAccount_Infection, '<br />', '')  NAME_TYPE_VAC
				,convert(varchar, ac.vac_Person_BirthDay, 104) [BirthDay]
				,ac.Vaccine_name vac_name
				,ac.Vaccine_id
				,ac.Person_id
				,ac.vacJournalAccount_Seria Seria
				,convert(varchar, ac.vacJournalAccount_Period, 104) vacPeriod
				,v.Vaccine_AgeBegin
				,v.Vaccine_AgeEnd
				,ac.Lpu_id
				,ac.Medservice_id 
				,ac.vacJournalAccount_vacOther vacOther
				,MS.LpuBuilding_id
				,VJA.EvnVizitPL_id
				,VJA.DocumentUcStr_id
			FROM [vac].v_JournalVacFixed ac WITH (NOLOCK)
				LEFT JOIN vac.S_VaccineWay w WITH (NOLOCK) ON ac.VaccineWay_id = w.VaccineWay_id
				LEFT JOIN vac.v_VaccinePlace p WITH (NOLOCK) ON ac.VaccinePlace_id = p.VaccinePlace_id
				LEFT JOIN vac.v_Vaccine v  WITH (NOLOCK) ON v.Vaccine_id = ac.Vaccine_id
				LEFT JOIN v_MedService MS on MS.MedService_id = ac.MedService_id
				OUTER APPLY(
					SELECT TOP 1 VJA.EvnVizitPL_id, VJA.DocumentUcStr_id FROM vac.vac_JournalAccount VJA WHERE VJA.vacJournalAccount_id = ac.vacJournalAccount_id
				) VJA
			WHERE ac.vacJournalAccount_id = :vacJaccountId  
		";

		$queryParams['vacJaccountId'] = $this->nvl($data['vac_jaccount_id']);
		$queryParams['userId'] = $this->nvl($data['user_id']);
		//echo getDebugSQL($query, $queryParams); die();
		$result = $this->db->query($query, $queryParams);

		log_message('debug', ' loadImplFormInfo:  $query=' . $query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (доп инфа для формы исполнения прививки)'));
		}
	}

	/**
	 * Загрузка доп инфы для формы исполнения прививки минуя назначение
	 */

	public function loadImplVacNoPurpFormInfo($data)
	{
		$queryParams = array();

		$query = "
SELECT v.Vaccine_id, v.Vaccine_AgeBegin, v.Vaccine_AgeEnd
 FROM vac.v_Vaccine v WITH (NOLOCK)
WHERE v.Vaccine_id = :Vaccine_id
    ";

		$queryParams['Vaccine_id'] = $this->nvl($data['vaccine_id']);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (доп инфа для формы исполнения прививки минуя назначение)'));
		}
	}

	/**
	 * Загрузка доп инфы для формы назначен прививки
	 */

	public function loadPurpFormInfo($data)
	{
		$queryParams = array();

		//    $query = "
		//  SELECT pf.PlanTmp_id
		//  FROM vac.v_Person_PlanFinal pf with(nolock)
		//  WHERE pf.PlanTmp_id = :planTmpId
		//    ";
		//SELECT TOP 1 @MED_PERS_ID = pmUser_Medpersonal_id
		// FROM [dbo].[v_pmUser]
		// WHERE PMUser_id = :userId 
		//    $query = "
		//DECLARE
		// @MED_PERS_ID INT,
		// @PLAN_ID INT;
		// 
		//SELECT TOP 1
		// @MED_PERS_ID = s.MedStaffFact_id
		// FROM [dbo].[v_pmUser] u
		// LEFT JOIN [dbo].v_MedStaffFact s
		//  ON u.pmUser_Medpersonal_id = s.MedPersonal_id
		// WHERE PMUser_id = :userId
		// 
		//SELECT TOP 1 @PLAN_ID = pf.vac_PersonPlanFinal_id
		// FROM vac.v_PersonPlanFinal pf with(nolock)
		// WHERE pf.vac_PersonPlanFinal_id = :planTmpId
		//  
		//SELECT @PLAN_ID Plan_id, @MED_PERS_ID MedPers_id
		//  ,vac_PersonPlanFinal_id [planTmp_id]
		//  ,convert(varchar, vac_PersonPlanFinal_DatePlan, 104) [Date_Plan]
		//  ,Person_PlanFinal_typeName [type_name]
		//  ,VaccineType_Name [Name]
		//  ,NationalCalendarVac_SequenceVac [SequenceVac]
		//  ,VaccineType_id
		//  ,convert(varchar, vac_Person_BirthDay, 104) [BirthDay]
		//FROM 
		//    vac.v_PersonPlanFinal
		//WHERE vac_PersonPlanFinal_id = @PLAN_ID
		//    ";

		$query = "
DECLARE
 @MED_PERS_ID BIGINT,
 @Person_id BIGINT,
 @Vac_Scheme_id varchar(8),
 @PLAN_ID BIGINT,
 @vac_jaccount_id bigint;
SET @PLAN_ID = :planTmpId
SET @Person_id = :Person_id
SET @Vac_Scheme_id = :Vac_Scheme_id
Set @vac_jaccount_id = :vacJournalAccount_id
 
SELECT TOP 1
 @MED_PERS_ID = s.MedStaffFact_id
 FROM [dbo].[v_pmUser] u WITH (NOLOCK)
 LEFT JOIN [dbo].v_MedStaffFact s WITH (NOLOCK)
  ON u.pmUser_Medpersonal_id = s.MedPersonal_id
 WHERE PMUser_id = :userId
 
--SELECT TOP 1 @PLAN_ID = pf.vac_PersonPlanFinal_id
-- FROM vac.v_PersonPlanFinal pf with(nolock)
-- WHERE pf.vac_PersonPlanFinal_id = @PLAN_ID

IF @PLAN_ID = -2
 Select top (1) 
 @PLAN_ID Plan_id, -2 planTmp_id, vacJournalAccount_id, @MED_PERS_ID MedPers_id, 
	  Lpu_id, MedService_id, 
	 Vaccine_id,
	convert(varchar, vacJournalAccount_DatePurpose, 104) [Date_Plan],
	null type_name, null Name, null SequenceVac, null VaccineType_id,
	convert(varchar, vac_Person_BirthDay, 104) [BirthDay],
	Person_id, vacJournalAccount_KeyList StoreKeyList, VaccinePlace_id, 
        vacJournalAccount_Dose Dose, vacJournalAccount_Seria Seria
  from vac.v_JournalVacFixed WITH (NOLOCK) --vac.vac_JournalAccount 
  where vacJournalAccount_id = @vac_jaccount_id;
else IF @PLAN_ID <> -1
BEGIN 
 SELECT @PLAN_ID Plan_id, @MED_PERS_ID MedPers_id
  ,vac_PersonPlanFinal_id [planTmp_id]
  ,convert(varchar, vac_PersonPlanFinal_DatePlan, 104) [Date_Plan]
  ,Person_PlanFinal_typeName [type_name]
  ,VaccineType_Name [Name]
  ,NationalCalendarVac_SequenceVac [SequenceVac]
  ,VaccineType_id
  ,convert(varchar, vac_Person_BirthDay, 104) [BirthDay]
	,Person_id
 FROM 
    vac.v_PersonPlanFinal WITH (NOLOCK)
 WHERE vac_PersonPlanFinal_id = @PLAN_ID
END
ELSE
BEGIN 
 SELECT @PLAN_ID Plan_id, @MED_PERS_ID MedPers_id
  ,-1 [planTmp_id]
  ,convert(varchar, [vacPersonPlan0_DateS], 104) [Date_Plan]
  ,[NationalCalendarVac_typeName] [type_name]
  ,[NationalCalendarVac_vaccineTypeName] [Name]
  ,NationalCalendarVac_SequenceVac [SequenceVac]
  ,vaccineType_id VaccineType_id
  ,convert(varchar, BirthDay, 104) [BirthDay]
	,Person_id
 FROM 
    vac.v_vacPersonPlan0 WITH (NOLOCK)
 WHERE [Person_id] = @Person_id AND [NationalCalendarVac_Scheme_id] = @Vac_Scheme_id
END
    ";

		$queryParams['planTmpId'] = $this->nvl($data['plan_id']);
		$queryParams['userId'] = $this->nvl($data['user_id']);
		//		$queryParams['PersonPlan_id'] = $this->nvl($data['PersonPlan_id']);
		$queryParams['Person_id'] = $this->nvl($data['Person_id']);
		$queryParams['Vac_Scheme_id'] = $this->nvl($data['Vac_Scheme_id']);
		$queryParams['vacJournalAccount_id'] = $this->nvl($data['vacJournalAccount_id']);

		$result = $this->db->query($query, $queryParams);

		log_message('debug', ' loadPurpFormInfo:  $query=' . $query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (доп инфа для формы назначен прививки)'));
		}
	}

	/**
	 * Загрузка доп инфы для формы назначен манту
	 */

	public function loadMantuFormInfo($data)
	{
		$queryParams = array();
		$query = "
DECLARE
 @MED_PERS_ID BIGINT,
 @PLAN_ID BIGINT;
 
SELECT TOP 1
 @MED_PERS_ID = s.MedStaffFact_id
 FROM [dbo].[v_pmUser] u  WITH (NOLOCK)
 LEFT JOIN [dbo].v_MedStaffFact s  WITH (NOLOCK)
  ON u.pmUser_Medpersonal_id = s.MedPersonal_id
 WHERE PMUser_id = :userId
 
--SELECT TOP 1 @PLAN_ID = pt.PlanTuberkulin_id
-- FROM vac.vac_PlanTuberkulin pt with(nolock)
-- WHERE pt.PlanTuberkulin_id = :planTuberkulinId
  
--SELECT @PLAN_ID PlanTuberkulin_id, @MED_PERS_ID MedPers_id

SELECT TOP 1 pt.PlanTuberkulin_id, pt.Person_id, @MED_PERS_ID MedPers_id,
  convert(varchar, pers.BirthDay, 104) [person_BirthDay]
 FROM vac.vac_PlanTuberkulin pt WITH (NOLOCK)
 LEFT OUTER JOIN vac.v_vac_Person pers with(nolock) on pers.Person_id = pt.Person_id
 WHERE pt.PlanTuberkulin_id = :planTuberkulinId

    ";
		$queryParams['planTuberkulinId'] = $this->nvl($data['plan_tub_id']);
		$queryParams['userId'] = $this->nvl($data['user_id']);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (доп инфа для формы назначен манту)'));
		}
	}

	/**
	 * Загрузка доп инфы для формы исполнения манту
	 */

	public function loadJournalMantuFormInfo($data)
	{
		$queryParams = array();
		//,j.JournalMantu_DatePurpose [DatePurpose]
		//,j.JournalMantu_DateVac [DateVac]
		//,convert(varchar, j.JournalMantu_DatePurpose, 104) [DatePurpose]
		$query = "
			SELECT 
				JournalMantu_id
				,j.Person_id
				,j.VacPresence_id
				,j.JournalMantu_Seria
				,j.JournalMantu_Dose [Dose]
				,isnull(j.VaccineWayPlace_id, wp.VaccineWayPlace_id) [WayPlace_id]
				--, j.VaccinePlace_id  [WayPlace_id]
				,j.MantuReactionType_id [React_id]
				,j.JournalMantu_ReactDescription
				--,j.JournalMantu_LocalReactDescription [LocalReactDesc]
				,j.[JournalMantu_ReactionSize] [ReactionSize]
				--,j.[JournalMantu_Reaction30min] [Reaction30min]
				,j.[JournalMantu_Reaction_30min] [Reaction30min]
				,convert(varchar, j.JournalMantu_DatePurpose, 104) [DatePurpose]
				,convert(varchar, j.JournalMantu_DateVac, 104) [DateVac]
				,convert(varchar, j.JournalMantu_DateReact, 104) [DateReact]
				,j.JournalMantu_Lpu_id [Lpu_id]
				,j.JournalMantu_vacMedPersonal_id [MedPersonal_id]
				,j.JournalMantu_StatusType_id [StatusType_id]
				,pr.VacPresence_Seria
				,convert(varchar, pr.VacPresence_Period, 104) [VacPresence_Period]
				,pr.VacPresence_Manufacturer
				,convert(varchar, pers.BirthDay, 104) [person_BirthDay]
				, TubDiagnosisType_id
				, DiaskinTestReactionType_id
				,CONVERT(VARCHAR(10), isnull(j.VaccineWayPlace_id, '')) + ISNULL(CONVERT(VARCHAR(10),j.VaccinePlace_id), '') AS id_VaccineWayPlace_VaccinePlace
			FROM vac.vac_JournalMantu j with(nolock)
				LEFT JOIN vac.v_VaccineWayPlace wp with(nolock)
				 ON j.VaccinePlace_id = wp.VaccinePlace_id
				 AND j.VaccineWay_id = wp.VaccineWay_id
				LEFT JOIN vac.Vac_Presence pr with(nolock)
				 ON j.VacPresence_id = pr.VacPresence_id
				LEFT OUTER JOIN vac.v_vac_Person pers with(nolock) on pers.Person_id = j.Person_id
			WHERE j.JournalMantu_id = :JournalMantuId
		";
		$queryParams['JournalMantuId'] = $this->nvl($data['fix_tub_id']);
		//echo getDebugSQL($query, $queryParams);die();
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (доп инфа для формы исполнен манту)'));
		}
	}

	/**
	 * Загрузка доп инфы для формы просмотра и редактирования справочника вакцин
	 */

	public function loadSprVacFormInfo($data)
	{
		$queryParams = array();

		$query = "
DECLARE @TypesVac varchar(100)
DECLARE @Vaccine_id BIGINT
--SET @TypesVac = vac.GetVacTypeIds(17)
SET @Vaccine_id = :Vaccine_id
SET @TypesVac = vac.GetVacTypes(@Vaccine_id)

--Доза и способ ввода
DECLARE @Dose_AgeS1 DECIMAL(7,2)
DECLARE @Dose_AgeE1 DECIMAL(7,2)
DECLARE @Way_AgeS1 DECIMAL(7,2)
DECLARE @Way_AgeE1 DECIMAL(7,2)
DECLARE @DoseVal1 varchar(10)--DECIMAL(7,3)
DECLARE @DoseType1 INT
DECLARE @WayType1 INT
DECLARE @PlaceType1 INT

DECLARE @Dose_AgeS2 DECIMAL(7,2)
DECLARE @Dose_AgeE2 DECIMAL(7,2)
DECLARE @Way_AgeS2 DECIMAL(7,2)
DECLARE @Way_AgeE2 DECIMAL(7,2)
DECLARE @DoseVal2 varchar(10)--DECIMAL(7,3)
DECLARE @DoseType2 INT
DECLARE @WayType2 INT
DECLARE @PlaceType2 INT
DECLARE @Gripp INT


SELECT
 @Dose_AgeS1 = d.VaccineDose_AgeS,
 @Dose_AgeE1 = d.VaccineDose_AgeE,
 @Way_AgeS1 = w.VaccineWayPlace_AgeS,
 @Way_AgeE1 = w.VaccineWayPlace_AgeE,
 @DoseVal1 = CONVERT (varchar, d.VaccineDose_Dose),
 --d.VaccineDose_Dose,
 @DoseType1 = d.VaccineDose_DoseType
 ,@WayType1 = w.VaccineWay_id
 ,@PlaceType1 = w.VaccinePlaceHome_id
 --,@Gripp = isnull((Select vt.VaccineType_id from vac.S_VaccineRelType vt with(nolock) where vt.Vaccine_id = v.Vaccine_id and VaccineType_SignNatCal = 0 ), 0)/100
  ,@Gripp = isnull((Select Top (1)  1 from vac.S_VaccineRelType vt with(nolock)
	 JOIN vac.S_VaccineType priv WITH (NOLOCK)  On priv.VaccineType_id = vt.VaccineType_id and VaccineType_SignNatCal = 0
	  where vt.Vaccine_id = v.Vaccine_id ), 0)

FROM vac.S_Vaccine v WITH (NOLOCK)
LEFT JOIN vac.S_VaccineDose d WITH (NOLOCK)
  ON d.Vaccine_id = v.Vaccine_id AND d.VaccineDose_AgeE < 1000 AND d.dose_status = 1
LEFT JOIN vac.S_VaccineWayPlace w WITH (NOLOCK)
  ON v.Vaccine_id = w.Vaccine_id AND w.VaccineWayPlace_AgeE < 10000 AND w.wayPlace_status = 1
WHERE v.Vaccine_id = @Vaccine_id

SELECT
 @Dose_AgeS2 = d.VaccineDose_AgeS, 
 @Dose_AgeE2 = d.VaccineDose_AgeE, 
 @Way_AgeS2 = w.VaccineWayPlace_AgeS, 
 @Way_AgeE2 = w.VaccineWayPlace_AgeE,
 @DoseVal2 = CONVERT (varchar, d.VaccineDose_Dose), --d.VaccineDose_Dose,
 @DoseType2 = d.VaccineDose_DoseType
 ,@WayType2 = w.VaccineWay_id
 ,@PlaceType2 = w.VaccinePlaceHome_id
 ,@Gripp = isnull((Select Top (1)  1 from vac.S_VaccineRelType vt with(nolock)
	 JOIN vac.S_VaccineType priv WITH (NOLOCK)  On priv.VaccineType_id = vt.VaccineType_id and VaccineType_SignScheme = 2
	  where vt.Vaccine_id = v.Vaccine_id ), 0)
FROM vac.S_Vaccine v WITH (NOLOCK)
LEFT JOIN vac.S_VaccineDose d WITH (NOLOCK)
  ON d.Vaccine_id = v.Vaccine_id AND d.VaccineDose_AgeE = 1000 AND d.dose_status = 1
LEFT JOIN vac.S_VaccineWayPlace w WITH (NOLOCK)
  ON w.Vaccine_id = v.Vaccine_id AND w.VaccineWayPlace_AgeE = 10000 AND w.wayPlace_status = 1
WHERE v.Vaccine_id = @Vaccine_id
--Доза и способ ввода

SELECT *, @TypesVac VacTypeIds
 ,@Dose_AgeS1 Dose_AgeS1
 ,@Dose_AgeE1 Dose_AgeE1
 ,@Way_AgeS1 Way_AgeS1
 ,@Way_AgeE1 Way_AgeE1
 ,@DoseVal1 DoseVal1
 ,@DoseType1 DoseType1
 ,@WayType1 WayType1
 ,@PlaceType1 PlaceType1
 ,@Dose_AgeS2 Dose_AgeS2
 ,@Dose_AgeE2 Dose_AgeE2
 ,@Way_AgeS2 Way_AgeS2
 ,@Way_AgeE2 Way_AgeE2
 ,@DoseVal2 DoseVal2
 ,@DoseType2 DoseType2
 ,@WayType2 WayType2
 ,@PlaceType2 PlaceType2
 , @Gripp OnGripp  --  Признак наличия прививки от гриппа
FROM vac.v_Vaccine vac WITH (NOLOCK)
WHERE vac.Vaccine_id = @Vaccine_id 

    ";

		$queryParams['Vaccine_id'] = $this->nvl($data['Vaccine_id']);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			//var_dump( $result->result('array'));
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (доп инфа для формы просмотра и редактирования справочника вакцин)'));
		}
	}


	/**
	 * Загрузка доп инфы для формы редактирования справочника Схема вакцинации
	 */

	public function loadSprOtherVacFormInfo($data)
	{
		$queryParams = array();

		$query = "
            DECLARE @Vaccine_id BIGINT
            SET @Vaccine_id = :Vaccine_id
            SELECT  [Vaccine_id]
              ,[Vaccine_Name]
              ,[Vaccine_Nick]
              ,[AgeTypeS1]
              ,[AgeS1]
              ,[AgeTypeS2]
              ,[AgeS2]
              ,[AgeTypeE1]
              ,[AgeE1]
              ,[AgeTypeE2]
              ,[AgeE2]
              ,[multiplicity1]
              ,[multiplicity2]
              ,[multiplicityRisk1]
              ,[multiplicityRisk2]
              ,[PeriodTypeInterval1]
              ,[Interval1]
              ,[PeriodTypeInterval2]
              ,[Interval2]
              ,[PeriodTypeIntervalRisk1]
              ,[IntervalRisk1]
              ,[PeriodTypeIntervalRisk2]
              ,[IntervalRisk2]
          FROM [vac].[v_OtherVacScheme_Brief] s WITH (NOLOCK)
                where s.Vaccine_id = @Vaccine_id 
                     --   and ISNULL(s.StatusType_id, 0) = 0
      
    ";

		$queryParams['Vaccine_id'] = $this->nvl($data['Vaccine_id']);
		log_message('debug', ' loadSprOtherVacFormInfo:  $query=' . $query);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			//var_dump( $result->result('array'));
			return $result->result('array');
		} else {
			return false;
			//array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (доп инфа для формы просмотра и редактирования справочника вакцин)'));
		}
	}

	/**
	 * Загрузка доп инфы для формы редактирования нац. календаря
	 * Тагир 24.06.2013
	 */
	public function loadSprNCFormInfo($data)
	{
		$queryParams = array();
		$filter = " (1=1)";
		if (isset($data['NationalCalendarVac_id'])) {
			$filter .= " and NationalCalendarVac_id = " . $data['NationalCalendarVac_id'];
		} else if (isset($data['Scheme_id'])) {
			$filter .= " and NationalCalendarVac_Scheme_id = '" . $data["Scheme_id"] . "'";
		};


		$query = "
Declare @NationalCalendarVac_id bigint,
	@VaccineType_id bigint,
	@Scheme_id varchar(8),
	@Type_id varchar(4),
	@SequenceVac int,
	@Scheme_Num int,
	@SignPurpose varchar(8),
        @max_SequenceVac int;
        
  --      Set @NationalCalendarVac_id = :NationalCalendarVac_id;
 /*     
  SELECT TOP 1 
      @Scheme_id =  NationalCalendarVac_Scheme_id
      , @VaccineType_id = VaccineType_id
      , @Type_id = Type_id
      , @SequenceVac = NationalCalendarVac_SequenceVac
      , @SignPurpose = NationalCalendarVac_SignPurpose
      , @Scheme_Num = NationalCalendarVac_Scheme_Num
      , @SequenceVac = NationalCalendarVac_SequenceVac
      , @max_SequenceVac = max_SequenceVac
  FROM vac.v_NationalCalendarVac WITH (NOLOCK)
	where NationalCalendarVac_id = :NationalCalendarVac_id
     
*/
  SELECT TOP 1 
       VaccineType_id
      , NationalCalendarVac_Scheme_id Scheme_id
      , Type_id
      , NationalCalendarVac_SequenceVac SequenceVac
      , NationalCalendarVac_Scheme_Num Scheme_Num
      , NationalCalendarVac_SignPurpose SignPurpose
  
      , VaccineAgeBorders_AgeTypeS AgeTypeS
      , VaccineAgeBorders_AgeS AgeS
      , VaccineAgeBorders_AgeTypeE AgeTypeE
      , VaccineAgeBorders_AgeE AgeE
      
      ,VaccineAgeBorders_PeriodVac PeriodVac
      ,VaccineAgeBorders_PeriodVacType PeriodVacType
      , isnull(NationalCalendarVac_Additional, 0) Additional
      , NationalCalendarVac_SequenceVac SequenceVac
      , max_SequenceVac
      , isnull(max_Additional, 0) max_Additional
  FROM vac.v_NationalCalendarVac WITH (NOLOCK)
	WHERE   {$filter} 
    
    ";

		//    $queryParams['NationalCalendarVac_id'] = $this->nvl($data['NationalCalendarVac_id']);
		//    $queryParams['Scheme_id'] = $this->nvl($data['Scheme_id']);
		//     log_message('debug', 'query='.$query);
		//      log_message('debug', '  $queryParams='.$queryParams['NationalCalendarVac_id']);
		//    $result = $this->db->query($query, $queryParams);

		$result = $this->db->query($query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (доп инфа для формы просмотра и редактирования нац. календаря)'));
		}
	}

	/**
	 * Удаление записи из нац. календаря
	 */
	public function deleteSprNC($data)
	{
		$queryParams = array();
		$query = "
                 Declare
                    @Error_Code bigint = 0,
                    @Error_Message varchar(4000) = '';
                  set nocount on
                  begin try

                    exec vac.p_S_NationalCalendarVac_del
                         @NationalCalendarVac_id = :NationalCalendarVac_id,     -- Идентификатор записи
                         @pmUser_id   = :user_id,      --  ID пользователя, который удалил запись
                        @Error_Code     = @Error_Code output,   -- Код ошибки
                        @Error_Message  = @Error_Message output -- Тект ошибки

                  end try
                    begin catch
                      set @Error_Code = error_number()
                      set @Error_Message = error_message()
                    end catch
                  set nocount off
                  Select @Error_Code as Error_Code, @Error_Message as Error_Msg
                ";

		$queryParams['NationalCalendarVac_id'] = $this->nvl($data['NationalCalendarVac_id']);
		$queryParams['user_id'] = $this->nvl($_SESSION['pmuser_id']);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление записи из нац. календаря'));
		}
	}

	//  End deleteSprVaccine


	/**
	 * Загрузка доп инфы для формы медотвода/отказа от прививки
	 */

	public function loadRefuseFormInfo($data)
	{
		$queryParams = array();

		//    $query = "
		//SELECT 
		//  r.vacJournalMedTapRefusal_id
		//	,Person_id
		//  ,r.vacJournalMedTapRefusal_uch [uch]
		//  ,r.SurName
		//  ,r.FirName
		//  ,r.SecName
		//  ,r.[sex]
		//  ,convert(varchar, r.BirthDay, 104) [BirthDay]
		//  ,convert(varchar, r.vacJournalMedTapRefusal_DateBegin, 104) DateBegin
		//  ,convert(varchar, r.vacJournalMedTapRefusal_DateEnd, 104) DateEnd
		//  ,r.vacJournalMedTapRefusal_Reason [Reason]
		//  ,r.vacJournalMedTapRefusal_NameTypeRec [type_rec]
		//  ,r.[Lpu_id]
		//  ,r.[Lpu_Name]
		//  ,tp.VaccineType_Name
		//  ,CASE 
		//    WHEN r.VaccineType_id = 1000 OR r.vacJournalMedTapRefusal_VaccineTypeAll = 1
		//     THEN 'Все прививки'
		//    ELSE tp.VaccineType_Name
		//   END VaccineType_Name
		//	,r.VaccineType_id
		//	,r.vacJournalMedTapRefusal_MedPersonal_id		MedPersonal_id
		//	,r.vacJournalMedTapRefusal_TypeRecord				TypeRecord
		//FROM 
		//  vac.v_JournalMedTapRefusal r
		//WHERE
		//	r.vacJournalMedTapRefusal_id = :vacJournalMedTapRefusal_id
		//    ";
		//	  LEFT JOIN vac.S_VaccineType tp with(nolock) ON r.VaccineType_id = tp.VaccineType_id

		$query = "
SELECT
	r.vacJournalMedTapRefusal_id              refuse_id
 ,r.VaccineType_id                         VaccineType_id
 ,r.vacJournalMedTapRefusal_MedPersonal_id MedPersonal_id
 ,r.vacJournalMedTapRefusal_Reason         Reason
 ,r.vacJournalMedTapRefusal_TypeRecord     TypeRecord
 ,convert(varchar, r.vacJournalMedTapRefusal_DateBegin, 104) RefuseDateBegin
 ,convert(varchar, r.vacJournalMedTapRefusal_DateEnd, 104)   RefuseDateEnd
 ,r.BirthDay
--FROM vac.vac_JournalMedTapRefusal r with(nolock)
FROM vac.v_JournalMedTapRefusal r WITH (NOLOCK)
WHERE r.vacJournalMedTapRefusal_id = :refuse_id
    ";
		//		 ,r.vacJournalMedTapRefusal_DateBegin RefuseDate_Begin

		$queryParams['refuse_id'] = $this->nvl($data['refuse_id']);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (доп инфа для формы медотводов)'));
		}
	}

	/**
	 * Загрузка доп инфы для вкладки "Группа риска" на Карте проф прививок
	 */

	public function loadVaccineRiskInfo($data)
	{
		$queryParams = array();
		$query = '
SELECT 
 VaccineRisk_id,
 VaccineType_id,
 Person_id
FROM vac.v_VaccineRisk WITH (NOLOCK)
WHERE person_id = :person_id
    ';

		$queryParams['person_id'] = $this->nvl($data['person_id']);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (доп инфа для формы Карта проф прививок)'));
		}
	}

	/**
	 * сохранение информации о группе риска пациента
	 */

	public function saveVaccineRisk($data)
	{
		$queryParams = array();
		$query = "
     Declare
	@VaccineRisk_id BIGINT = 0,
        @Error_Code BIGINT = 0,
        @Error_Message varchar(4000) = '';
      set nocount on
      begin try
      
        exec vac.p_VaccineRisk_ins
            @Person_id      = :person_id,       -- id пациента
            @VaccineType_id = :vaccine_type_id,     --  Идентификатор инфекции
            @pmUser_insID   = :user_id,  --  ID пользователя, который создал запись
            @Error_Code     = @Error_Code output,    -- Код ошибки
            @Error_Message  = @Error_Message output, -- Тект ошибки
						@VaccineRisk_id = @VaccineRisk_id output -- id добавленной записи

      end try
        begin catch
          set @Error_Code = error_number()
          set @Error_Message = error_message()
        end catch
      set nocount off
      Select @VaccineRisk_id as VaccineRisk_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
    ";

		$queryParams['person_id'] = $this->nvl($data['person_id']);
		$queryParams['vaccine_type_id'] = $this->nvl($data['vaccine_type_id']);
		$queryParams['user_id'] = $this->nvl($_SESSION['pmuser_id']);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение информации о группах риска пациента)'));
		}
	}

	/**
	 * удаление информации о группе риска пациента
	 */

	public function deleteVaccineRisk($data)
	{
		$queryParams = array();
		$query = "
     Declare
        @Error_Code bigint = 0,
        @Error_Message varchar(4000) = '';
      set nocount on
      begin try
      
        exec vac.p_VaccineRisk_del
				    @VaccineRisk_id = :vaccine_risk_id,     -- Идентификатор записи
						@pmUser_delID   = :user_id,      --  ID пользователя, который удалил запись
            @Error_Code     = @Error_Code output,   -- Код ошибки
            @Error_Message  = @Error_Message output -- Тект ошибки

      end try
        begin catch
          set @Error_Code = error_number()
          set @Error_Message = error_message()
        end catch
      set nocount off
      Select @Error_Code as Error_Code, @Error_Message as Error_Msg
    ";

		$queryParams['vaccine_risk_id'] = $this->nvl($data['vaccine_risk_id']);
		$queryParams['user_id'] = $this->nvl($_SESSION['pmuser_id']);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление информации о группе риска пациента)'));
		}
	}

	/**
	 * список статусов вакцин
	 *
	 * НЕ ИСПОЛЬЗУЕТСЯ!!!
	 */

	public function getVaccineStatusList($data)
	{
		$queryParams = array();

		$query = "
    ";

		$queryParams['VaccineType_id'] = $this->nvl($data['vac_type_id']);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (Список статусов вакцин)'));
		}
	}

	/**
	 * Получаем список типов отказов
	 */

	public function getVaccineRefusalTypeList()
	{
		//    $queryParams = array();

		$query = "
SELECT 
  RefusalType_id
  ,RefusalType_name
FROM vac.S_RefusalType WITH (NOLOCK)
    ";

		$result = $this->db->query($query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (Список типов отказов)'));
		}
	}

	/**
	 * Получаем список типов реакции манту
	 */

	public function getTypeReactionList()
	{
		//    $queryParams = array();

		$query = "
SELECT 
  MantuReactionType_id [reaction_id]
  ,MantuReactionType_name [reaction_name]
FROM vac.S_MantuReactionType WITH (NOLOCK)
ORDER BY order_val
    ";

		$result = $this->db->query($query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (Список типов реакции манту)'));
		}
	}

	/**
	 * Получаем список типов реакции диаскинтеста
	 */

	public function getDiaskinTypeReactionList()
	{
		//    $queryParams = array();

		$query = "
            SELECT 
              DiaskinTestReactionType_id
              ,DiaskinTestReactionType_name
            FROM vac.S_DiaskinTestReactionType WITH (NOLOCK)
            ORDER BY DiaskinTestReactionType_id
    ";

		$result = $this->db->query($query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
	}

	/**
	 * Получаем список ЛПУ, в которых есть служба "Кабинет вакцинации"
	 */

	public function getLpuListServiceVac()
	{

		$query = "
            Select distinct Lpu_id, Lpu_Nick Lpu_Name from (
             select ms.Lpu_id, l.Lpu_Nick, l.Lpu_Name, ms.LpuBuilding_id, lb.LpuBuilding_Name, ms.MedService_Nick
             --lb.Lpu_id, lb.LpuBuilding_id, ms.LpuUnit_id,  ms.LpuSection_id
            from v_MedService ms with(nolock) 
            left join v_LpuBuilding lb with(nolock) on ms.LpuBuilding_id=lb.LpuBuilding_id
            left join v_Lpu l with(nolock) on ms.Lpu_id=l.Lpu_id
            where --ms.MedService_id=50
                    ms.MedServiceType_id = 31
                    and ms.MedService_begDT <= GETDATE()
                    and (ms.MedService_endDT is null or ms.MedService_endDT >= GETDATE() )
                    and (ms.MedService_endDT is null or ms.MedService_endDT >= GETDATE() )
                    ) t
    ";

		$result = $this->db->query($query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (Список ЛПУ)'));
		}
	}

	/**
	 * Получаем Cписок служб "Кабинет вакцинации"
	 */
	public function getMedServiceVac($data = array())
	{
		$filter = "";
		$queryParams = array();
		if ($data['Lpu_id'] > 0) {
			$filter .= " and LB.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if ($data['LpuBuilding_id'] > 0) {
			$filter .= " and LB.LpuBuilding_id = :LpuBuilding_id";
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}

		$query = "
            Select distinct  MedService_id, MedService_Nick, Lpu_id, LpuBuilding_id from (
             select   ms.Lpu_id, l.Lpu_Nick, l.Lpu_Name, ms.LpuBuilding_id, lb.LpuBuilding_Name, ms.MedService_id,  ms.MedService_Nick
             --lb.Lpu_id, lb.LpuBuilding_id, ms.LpuUnit_id,  ms.LpuSection_id
            from v_MedService ms with(nolock) 
            left join v_LpuBuilding lb with(nolock) on ms.LpuBuilding_id=lb.LpuBuilding_id
            left join v_Lpu l with(nolock) on ms.Lpu_id=l.Lpu_id
            where --ms.MedService_id=50
                    ms.MedServiceType_id = 31
                    and ms.MedService_begDT <= GETDATE()
                    and (ms.MedService_endDT is null or ms.MedService_endDT >= GETDATE() )
                    and (ms.MedService_endDT is null or ms.MedService_endDT >= GETDATE() )
             " . $filter . "                    
            ) t
    ";

		$result = $this->db->query($query, $queryParams);
		log_message('debug', 'getMedServiceVac:  query=' . $query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (Список ЛПУ)'));
		}
	}


	/**
	 * Получаем Расширенный Cписок служб "Кабинет вакцинации"
	 */
	public function getMedServiceVacExtended($data = array())
	{
		$filter = "";
		$queryParams = array();
		if ($data['Lpu_id'] > 0) {
			$filter .= " and LB.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if ($data['LpuBuilding_id'] > 0) {
			$filter .= " and LB.LpuBuilding_id = :LpuBuilding_id";
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}

		$query = "
            SElect * from (
                        Select distinct  MedService_id, MedService_Nick, Lpu_id, LpuBuilding_id from (
                                     select   ms.Lpu_id, l.Lpu_Nick, l.Lpu_Name, ms.LpuBuilding_id, lb.LpuBuilding_Name, ms.MedService_id,  ms.MedService_Nick
                                     --lb.Lpu_id, lb.LpuBuilding_id, ms.LpuUnit_id,  ms.LpuSection_id
                                    from v_MedService ms with(nolock) 
                                    left join v_LpuBuilding lb with(nolock) on ms.LpuBuilding_id=lb.LpuBuilding_id
                                    left join v_Lpu l with(nolock) on ms.Lpu_id=l.Lpu_id
                                    where --ms.MedService_id=50
                                            ms.MedServiceType_id = 31
                                            and ms.MedService_begDT <= GETDATE()
                                            and (ms.MedService_endDT is null or ms.MedService_endDT >= GETDATE() )
                                            and (ms.MedService_endDT is null or ms.MedService_endDT >= GETDATE() )
                                            " . $filter . "
                                                )t                                         
                        union
                        Select 0 MedService_id, ' Все службы' MedService_Nick, null lpu_id, null LpuBuilding_id
                        union
                        Select -1 MedService_id, ' Не определено' MedService_Nick, null lpu_id, null LpuBuilding_id  
                        ) t
                        order by  MedService_Nick
    ";

		$result = $this->db->query($query, $queryParams);
		log_message('debug', 'getMedServiceVacExtended:  query=' . $query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (Список ЛПУ)'));
		}
	}


	/**
	 * Получаем Список медперсонала
	 */
	public function geComboVacMedPersonalFull($data = array())
	{
		$filter = "";
		$queryParams = array();

		if ($data['Lpu_id'] > 0) {
			$filter .= " and Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}


		$query = "
                SElect MedStaffFact_id, Person_Fin, post.name Post_Name, Lpu_id  from v_MedStaffFact med with(nolock)
                left outer join persis.v_Post post with(nolock) on med.Post_id = post.id
	where  WorkData_begDate <= GetDate()
                    and (WorkData_endDate >=  GetDate()
                            or WorkData_endDate is null)
                    " . $filter . "         
                ORDER BY Person_Fin 
            ";

		$result = $this->db->query($query, $queryParams);
		log_message('debug', 'geComboVacMedPersonalFull:  query=' . $query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (Список медперсонала)'));
		}
	}


	/**
	 * Получаем Список сотрудников службы "Кабинет вакцинации"
	 */
	public function geComboVacMedPersonal($data = array())
	{
		$filter = "";
		$queryParams = array();

		if ($data['Lpu_id'] > 0) {
			$filter .= " and Lpu_id = :Lpu_id";
			//$filter .= " and Lpu_id = " .$data['Lpu_id'];
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if ($data['LpuBuilding_id'] > 0) {
			$filter .= " and LpuBuilding_id = :LpuBuilding_id";
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}

		if ($data['MedService_id'] > 0) {
			$filter .= " and MedService_id = :MedService_id";
			$queryParams['MedService_id'] = $data['MedService_id'];
		}
		
		if(!empty($data['isMidMedPersonalOnly'])){
			// Только средний мед. персонал
			$filter .= " AND EXISTS(SELECT * FROM dbo.v_MedStaffFact MSF with(nolock) WHERE MSF.MedPersonal_id = VMP.MedPersonal_id AND MSF.PostKind_id = 6 AND MSF.Lpu_id = VMP.Lpu_id)";
		}

		$query = "
                SELECT MedServiceMedPersonal_id
                  ,Lpu_id
                  ,LpuBuilding_id
                  ,MedService_id
                  ,MedPersonal_id
                  ,MedPersonal_Name
                  ,Person_Fin
                  ,MedServiceMedPersonal_begDT
                  ,MedServiceMedPersonal_endDT
                  , convert(varchar, GetDate(), 104) dd
              FROM vac.v_VacMedPersonal VMP WITH (NOLOCK)
                    where VMP.MedServiceMedPersonal_begDT <= GetDate()
                    and (VMP.MedServiceMedPersonal_endDT >=  GetDate()
                            or VMP.MedServiceMedPersonal_endDT is null)
                    " . $filter . "         
                ORDER BY VMP.MedPersonal_Name 
            ";
		//echo getDebugSQL($query, $queryParams); die();		
		$result = $this->db->query($query, $queryParams);
		log_message('debug', 'geComboVacMedPersonal:  query=' . $query);
		
		

		if (is_object($result)) {
			//return $result->result('array');
			$resArr = $result->result('array');
			
			if(!empty($data['form']) && $data['form'] == 'amm_ImplVacForm' && !empty($data['MedPers_id_impl'])){
				//проверим есть ли в результате запроса MedPersonal_id = MedPers_id_impl
				$emptyMedPers_id_impl = true;
				foreach ($resArr as $value) {
					if($value['MedPersonal_id'] == $data['MedPers_id_impl']){
						$emptyMedPers_id_impl = false;
						break;
					}
				}
				if($emptyMedPers_id_impl){
					//если не нашли такого врача, то найдем его и добавим к существующему массиву
					$sql = "
						SELECT TOP 1
							MSMP.MedServiceMedPersonal_id,
							MP.Lpu_id,
							MS.LpuBuilding_id,
							MSMP.MedService_id,
							MP.MedPersonal_id,
							MS.MedServiceType_id,
							PS.Person_SurName + ' ' + PS.Person_FirName + isnull(' ' + PS.Person_SecName,'') as MedPersonal_Name,
							PS.Person_SurName + ' ' + left(PS.Person_FirName,1) + isnull(left(PS.Person_SecName, 1),'') as Person_Fin,
							MSMP.MedServiceMedPersonal_begDT,
							MSMP.MedServiceMedPersonal_endDT,
							convert(varchar, GetDate(), 104) dd
						FROM MedPersonalCache MP  WITH(nolock)
							INNER join dbo.v_PersonState PS on MP.Person_id = PS.Person_id
							LEFT JOIN v_MedServiceMedPersonal MSMP WITH(NOLOCK) ON MSMP.MedPersonal_id = MP.MedPersonal_id
							LEFT JOIN MedService MS WITH(nolock) ON MS.MedService_id = MSMP.MedService_id
						WHERE
							MP.MedPersonal_id = :MedPersonal_id
						ORDER BY MSMP.MedServiceMedPersonal_endDT";
					$resultMedPers = $this->db->query($sql, array('MedPersonal_id' => $data['MedPers_id_impl']));
					if(is_object($resultMedPers)){
						$resultMedPers = $resultMedPers->result('array');
						if(count($resultMedPers)>0) $resArr[] = $resultMedPers[0];
					}
				}
			}
			return $resArr;
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (Список сотрудников службы "Кабинет вакцинации")'));
		}
	}


	/**
	 * Получение списка подразделений по заданными фильтрами
	 * @param array $data Фильтры
	 * @return array Ассоциативный массив с данными подразделений
	 */
	function getLpuBuildingServiceVac($data = array())
	{
		$filter = "";
		$queryParams = array();

		if ($data['Lpu_id'] > 0) {
			$filter .= " and LB.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}


		//		$query = "
		//			SELECT
		//				LB.LpuBuilding_id as LpuBuilding_id,
		//				RTRIM(LB.LpuBuilding_Code) as LpuBuilding_Code,
		//				RTRIM(LB.LpuBuilding_Name) as LpuBuilding_Name
		//			FROM LpuBuilding LB with(nolock)
		//			WHERE " . $filter . "
		//			ORDER by LB.LpuBuilding_Code, LB.LpuBuilding_Name
		//		";

		$query = "
			Select distinct  LpuBuilding_id,  LpuBuilding_Code, LpuBuilding_Name from (
                         select   ms.Lpu_id, l.Lpu_Nick, l.Lpu_Name, ms.LpuBuilding_id, lb.LpuBuilding_Name, ms.MedService_id,  lb.LpuBuilding_Code, ms.MedService_Nick
                         --lb.Lpu_id, lb.LpuBuilding_id, ms.LpuUnit_id,  ms.LpuSection_id
                        from v_MedService ms with(nolock) 
                        left join v_LpuBuilding lb with(nolock) on ms.LpuBuilding_id=lb.LpuBuilding_id
                        left join v_Lpu l with(nolock) on ms.Lpu_id=l.Lpu_id
                        where 
                                ms.MedServiceType_id = 31
                                and ms.MedService_begDT <= GETDATE()
                                and (ms.MedService_endDT is null or ms.MedService_endDT >= GETDATE() )
                                and (ms.MedService_endDT is null or ms.MedService_endDT >= GETDATE() )
                                " . $filter . "
                                ) t2
		";

		$result = $this->db->query($query, $queryParams);

		log_message('debug', 'getLpuBuildingServiceVac:  query=' . $query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}


	/**
	 * Получаем список доступных серий вакцин
	 */

	public function getVaccineSeriaList($data)
	{
		if(empty($data['vaccine_id'])) return false;
		$vacineID = $this->nvl($data['vaccine_id']);
		if($vacineID == 27){
			//диаскинтест ИД сменился. решили брать id по Vaccine_Nick
			$sqlDeclare = "declare @vaccine_id int = (select top 1 Vaccine_id from vac.v_Vaccine where Vaccine_Nick like 'диаскинтест')";
		}else{
			$sqlDeclare = "declare @vaccine_id int = ".$vacineID;
		}
		
		$query = "
		{$sqlDeclare}
		
        SELECT 
         VacPresence_id
         , Vaccine_id
         , VacPresence_Seria [Seria]
         , convert(varchar, VacPresence_Period, 104) [Period]
         , VacPresence_Seria + ' - ' + convert(varchar, VacPresence_Period, 104) [vacSeria]
         , VacPresence_Manufacturer [Manufacturer]
         FROM
        vac.v_VacPresence WITH (NOLOCK)
        WHERE VacPresence_toHave = 1
        AND Vaccine_id = @vaccine_id
        AND lpu_id = :lpu_id
            ";

		$queryParams = array();
		$queryParams['vaccineId'] = $this->nvl($data['vaccine_id']);
		$queryParams['lpu_id'] = $this->nvl($_SESSION['lpu_id']);
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (список доступных серий)'));
		}
	}

	/**
	 * Получаем список участков
	 */

	public function getUchList($data)
	{

		$query = "
Declare	
    @LpuId as bigint = :lpu_id,
    @LpuBuilding_id as bigint = :LpuBuilding_id,
    @LpuUnit_id as bigint = :LpuUnit_id,
    @LpuSection_id as bigint = :LpuSection_id;
    
Select * from vac.fn_GetRegion4LPU (@LpuId, @LpuBuilding_id, @LpuUnit_id, @LpuSection_id) 
--Select * from vac.fn_GetRegion4LPU (@LpuId)
	order by  LpuRegion_Name
		";

		$queryParams = array();
		//    $queryParams['vaccineId'] = $this->nvl($data['vaccine_id']);
		//		$queryParams['lpu_id'] = $this->nvl($_SESSION['lpu_id'] );
		$queryParams['lpu_id'] = $this->nvl($data['lpu_id']);
		$queryParams['LpuBuilding_id'] = $this->nvl($data['LpuBuilding_id']);
		$queryParams['LpuUnit_id'] = $this->nvl($data['LpuUnit_id']);
		$queryParams['LpuSection_id'] = $this->nvl($data['LpuSection_id']);
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (список участков)'));
		}
	}

	/**
	 * Просмотр "Карта 063 (Обзор прививок новый)"
	 */

	public function getPersonVac063($data)
	{

		$queryParams = array();
		$errorMsg = 'Ошибка при выполнении запроса к базе данных ("Обзор прививок новый")';

		$query = "
			declare
				@Person_id bigint,
				@Error_Code int = null,
				@Error_Message varchar(4000) = null
			Set @Person_id = :person_id;
			exec vac.p_vacData4Card063_cntrl 
				@Person_id = @Person_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			Select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$queryParams['person_id'] = $this->nvl($data['Person_id']);
		//echo getDebugSQL($query, $queryParams);die;
		$result =  $this->db->query($query, $queryParams);
		$query = "
			Declare @Person_id bigint
			Set @Person_id = :person_id
			
			SELECT
				DC.row_num,
				DC.Inoculation_id,
				DC.Inoculation_StatusType_id,
				DC.idInCurrentTable,
				DC.PersonPlan_id,
				DC.Lpu_id,
				DC.Scheme_num,
				DC.Person_id,
				DC.Scheme_id,
				DC.vaccineType_id,
				Risks.risks,
				DC.StatusType_Name,
				DC.VaccineType_Name,
				NCV.NationalCalendarVac_vaccineTypeName as VaccineType_FullName,
				DC.typeName,
				convert(varchar, DC.date_vac, 104) date_vac,
				convert(varchar, DC.date_plan, 104) date_plan,
				convert(varchar, DC.Date_Purpose, 104) date_purpose,
				DC.Age,
				DC.vac_name,
				DC.vacJournalAccount_Dose,
				DC.vacJournalAccount_Seria,
				DC.ReactGeneralDescription,
				DC.ReactlocaDescription,
				DC.StatusType_id,
				DC.StatusSrok_id,
				DC.Tap_DateBeg,
				DC.Tap_DateEnd,
				DC.vacData4Card063_id,
				--  По задаче #72923
				vacJournalAccount_Vac_PmUser_id pmUser_updID,
				-- vacJournalAccount_VacDateSave - Дата записи сведений о вакцинации, vacJournalAccount_DateSave - Дата внесения сведений о назначении
				isnull(ac.vacJournalAccount_VacDateSave, vacJournalAccount_DateSave) vacJournalAccount_updDT
			from vac.vacData4Card063 DC with (nolock)
			left join vac.v_NationalCalendarVac NCV (nolock) on NCV.NationalCalendarVac_Scheme_id = DC.Scheme_id
			--  По задаче #72923
			left join vac.Inoculation i with (nolock)  on i.Inoculation_id = DC.Inoculation_id
			left join vac.vac_JournalAccount ac  with (nolock) on ac.vacJournalAccount_id = i.vacJournalAccount_id
			outer apply (
				select top 1 case when (
					VR.VaccineType_id = DC.vaccineType_id and
					RIGHT(NCV.NationalCalendarVac_SignPurpose,1) = '1'
				) then 1 else 0 end as risks
				from vac.VaccineRisk VR
				where VR.Person_id = DC.Person_id
				and VR.VaccineType_delDT is null
				order by 1 desc
			) as Risks
				where DC.Person_id = @Person_id;
	
		";
		//echo getDebugSQL($query, $queryParams);die();
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			$result = $result->result('array');
			if($_SESSION['region']['nick']=='kareliya') //Костылина для https://redmine.swan.perm.ru/issues/98742
			{
				//Узнаем, имеется ли у пациента группа риска
				/**
				* 1  - против вирусного гепатита В
				* 2  - против туберкулеза
				* 10 - против гемофильной инфекции
				*/
				$query_get_risk = "
					select distinct VR.VaccineType_id
					from vac.VaccineRisk VR 
					where VR.Person_id = :person_id
					and VR.VaccineType_delDT is null
					and VR.Status_id = 1
				";
				$result_get_risk = $this->db->query($query_get_risk,$queryParams);
				$risks = array();
				if(is_object($result_get_risk))
				{
					$result_get_risk = $result_get_risk->result('array');
					for ($i=0;$i<count($result_get_risk);$i++)
					{
						$risks[] = $result_get_risk[$i]['VaccineType_id'];
					}
				}
				$res = array();
				foreach($result as $r)
				{
					if(in_array($r['vaccineType_id'],$risks) && /*$r['idInCurrentTable'] == -1*/ $r['StatusType_id'] != 1 && $r['risks'] == 0)
					{
						//Ничо не делаем
					}
					else
					{
						$res[] = $r;
					}
				}
				$result = $res;
			}
			return $result;
		} else {
			return array(array('Error_Msg' => $errorMsg));
		}
	}

	/**
	 * Просмотр "Обзор прививок"
	 */

	public function getPersonVacDigest($data)
	{
		$queryParams = array();
		$errorMsg = 'Ошибка при выполнении запроса к базе данных ("Обзор прививок")';

		$query = "
			Declare @Person_id bigint
			Declare @D datetime
			Set @Person_id = :person_id
			Set @D = dbo.tzGetDate()
			SELECT * FROM vac.fn_getPersonVacDigest(@Person_id, @D)
			ORDER BY [Date_plan_Sort]
		";


		$queryParams['person_id'] = $this->nvl($data['Person_id']);
		log_message('debug', 'person_id=' . $queryParams['person_id']);
		log_message('debug', 'query=' . $query);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => $errorMsg));
		}
	}

	/**
	 * Просмотр прочих прививок, включая грипп
	 */

	public function getPersonVacOther($data)
	{
		$queryParams = array();

		$filter = "";
		$queryParams = array();

		if ($data['Person_id'] > 0) {
			$filter .= "  Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}
		$errorMsg = 'Ошибка при выполнении запроса к базе данных ("Обзор прививок новый")';


		$query = "

			
	Select 
	 Inoculation_id
	 ,vacJournalAccount_id
	 ,Person_id
	 ,'V' typeName
	 --,convert(varchar, vacJournalAccount_DatePurpose, 104) DatePurpose 
	 ,convert(varchar, isnull(vacJournalAccount_DateVac, vacJournalAccount_DatePurpose), 104) date_vac 
         ,vacJournalAccount_age age
	 ,vacJournalAccount_uch 
         ,Vaccine_Name
          ,vacJournalAccount_Dose VACCINE_DOZA
          ,vacJournalAccount_WayPlace WAY_PLACE
          ,vacJournalAccount_Seria Seria
          ,vacJournalAccount_Period
       
      ,ReactlocaDescription
      ,ReactGeneralDescription
      ,vacJournalAccount_StatusType_id StatusType_id
      ,vacJournalAccount_StatusType_Name StatusType_Name
      ,MedService_id
      ,vac_Person_sex
      ,vac_Person_BirthDay
      ,vac_Person_group_risk group_risk
      ,Lpu_id
      ,Lpu_Name      
      ,VaccineType_id
      ,VaccineType_Name
      ,Type_id
      ,vacJournalAccount_DateSave
      ,vacJournalAccount_VacDateSave
      ,Scheme_id_after
      ,DateVac_Old
      ,Start_DateVac
      ,Organized
      ,Inoculation_StatusType_id 
     
from vac.v_InoculationOther  WITH (NOLOCK)     
where {$filter}
    order by vacJournalAccount_DateVac, vacJournalAccount_DatePurpose, Vaccine_Name
		";
		$queryParams['person_id'] = $this->nvl($data['Person_id']);

		$result = $this->db->query($query, $queryParams);

		log_message('debug', 'getPersonVacOther=' . $query);
		log_message('debug', 'Person_id=' . $data['Person_id']);


		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => $errorMsg));
		}
		/*
		if (is_object($result)) {
			$response = $result->result('array');
		   // $response[] = array('__countOfAllRows' => $count);
			return $response;
		} else {
			return false;
		}
		*/
	}

	/**
	 * Получаем список типов инфекций
	 */
	public function getVaccineTypeInfection()
	{
		$query = "
			 SELECT [VaccineType_id]
			 ,[VaccineType_Name]
			 ,[VaccineType_NameIm]
			 ,[VaccineType_SignNatCal]
				FROM [vac].[S_VaccineType] WITH (NOLOCK)
			";

		$result = $this->db->query($query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (типы инфекций)'));
		}
	}

	/**
	 * получаем начальные установки из таблицы БД settings
	 */

	public function getVacSettings()
	{
		//    $queryParams = array();

		$query = "
		set nocount on;
		
DECLARE 
  @Error_Code bigint = 0,
  @Error_Message varchar(4000) = '';
BEGIN TRY
 IF EXISTS(SELECT TABLE_CATALOG, TABLE_SCHEMA, TABLE_NAME
           FROM INFORMATION_SCHEMA.TABLES WITH (NOLOCK)
           WHERE TABLE_NAME = N'settings' AND TABLE_SCHEMA = 'vac')
 BEGIN
	SELECT [id]
		,[parent_id]
		,[name]
		,[value]
		,[description]
	FROM [vac].[settings] WITH (NOLOCK)
 END
END TRY
BEGIN CATCH
	set @Error_Code = error_number()
	set @Error_Message = error_message()
	Select @Error_Code as Error_Code, @Error_Message as Error_Msg
END CATCH
		";

		$result = $this->db->query($query, [], true);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (настройки)'));
		}
	}

	/**
	 * Получаем список организаций, обслуживаемых ЛПУ
	 * Нигматуллин Тагир  08.05.2013
	 */
	public function getOrgJob2Lpu($data)
	{


		$query = "
			DECLARE @lpu_id bigint
			SET @lpu_id = :lpu_id
			Select 0 Org_id, 'Все организации' Org_Nick  
				Union
			 Select Org_id, Org_Nick  from  vac.v_VacOrgJob2LpuState WITH (NOLOCK)
				where Lpu_id = @lpu_id
				order by Org_Nick
    ";

		$queryParams = array();
		$queryParams['lpu_id'] = $this->nvl($data['lpu_id']);

		$result = $this->db->query($query, $queryParams);
		//    $result = $this->db->query($query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}


	/**
	 *  "Журнал назначенных прививок" для АРМ Кабинет вакцинации
	 */
	function GetVacAssigned4CabVac($data)
	{
		$filters = array();
		$join = '';
		$CardJoin = '';
		$queryParams = array();
		//$queryParams2 = array();
		//log_message('debug', 'Search_Birthday01=' . $data['Search_Birthday']);
		$this->genSearchFilters(TYPE_JOURNAL::VAC_4CabVac, $data, $filters, $queryParams, $join, $CardJoin);

		//Фильтр грида
		//$json = isset($data['Filter']) ? iconv('windows-1251', 'utf-8', trim($data['Filter'],'"')) : false;
		$json = isset($data['Filter']) ? trim($data['Filter'], '"') : false;

		$filter_mode = isset($data['Filter']) ? json_decode($json, 1) : false;

		$where = '';
		$join = '';
		$fields = '';
		if (isset($data['Filter'])) {
			log_message('debug', '$json=' . $json);
			log_message('debug', '$filter_mode=' . $filter_mode);


			//$field = $filter_mode['cell'];

			foreach ($filter_mode as $col => $val) {
				// log_message('debug', 'getNameColumn($col)='.getNameColumn($col));
				if ($this->getNameColumn($col) == 'Infection') {
					echo '<pre>' . print_r($filter_mode, 1) . '</pre>';
				}
				//echo '<pre>' . print_r($col, 1) . '</pre>';
				foreach ($val as $v) {
					$tempIn[] = "'" . $v . "'";
				}

				$temp = implode(',', $tempIn);

				if ($col == 'Infection')
					$joinMore[] = ' [' . $this->getNameColumn($col) . '] in(' . $temp . ')';
				else
					$whereMore[] = ' [' . $this->getNameColumn($col) . '] in(' . $temp . ')';
				/*
					$joinMore[] = ' ['.$this->getNameColumn($col).'] in('.iconv('utf-8','windows-1251',$temp).')';
				else
						$whereMore[] = ' ['.$this->getNameColumn($col).'] in('.iconv('utf-8','windows-1251',$temp).')';
				*/
			}

			$where = (isset($whereMore)) ? ' and ' . implode(' and ', $whereMore) : $where;

			if (isset($joinMore)) {
				$join = "
					join vac.Inoculation i  WITH (NOLOCK) on FromTable.vacJournalAccount_id = i.vacJournalAccount_id
					join vac.S_VaccineType tp  WITH (NOLOCK) on i.VaccineType_id = tp.VaccineType_id and " . implode(' and ', $joinMore);
			}
			//$join = (isset($whereMore)) ? ' and ' .implode(' and ', $whereMore) : $join;


		}

		//$filter = ImplodeWherePH($filters).$where;

		$filter = "" . Implode(' and ', $filters);
		if(in_array(getRegionNick(), array('perm','penza','krym','astra'))){
			$join .= "
				OUTER APPLY(
					SELECT TOP 1 VJA.EvnVizitPL_id, VJA.DocumentUcStr_id FROM vac.vac_JournalAccount VJA WHERE VJA.vacJournalAccount_id = FromTable.vacJournalAccount_id
				) VJA";
			$fields .= "
				,VJA.DocumentUcStr_id
			";
		}else{
			$fields .= ",'' as DocumentUcStr_id ";
		}

		$sql = "
            SELECT  
				-- select 
				FromTable.JournalVac_id                  
				,FromTable.type_rec                   
				,FromTable.vacJournalAccount_id
				,FromTable.JournalMantu_id fix_tub_id
				,convert(varchar, FromTable.DatePurpose, 104) DatePurpose
				,convert(varchar, FromTable.DateVac, 104) DateVac
				/*,FromTable.uch*/
				,FromTable.Person_id
				,FromTable.Server_id
				,FromTable.PersonEvn_id
				,FromTable.SurName
				,FromTable.FirName
				,FromTable.SecName
				,FromTable.fio
				,FromTable.Vaccine_Name
				,FromTable.Vaccine_id
				,FromTable.Seria
				,FromTable.Period
				,FromTable.Infection
				,FromTable.Dose
				,FromTable.WayPlace
				,FromTable.vac_Person_Sex Sex
				,convert(varchar,FromTable.BirthDay, 104) BirthDay
				,FromTable.Age
				,FromTable.StatusSrok_id
				,FromTable.StatusType_id
				,FromTable.StatusType_Name
				,FromTable.Lpu_id
				,FromTable.Lpu_Name
				,FromTable.MedService_Nick
				,L2.Lpu_Nick
				,PCard.Lpu_atNick
				,PCard.LpuRegion_Name as uch
				{$fields}
              -- end select    
              FROM 
                -- from
                vac.v_JournalVac FromTable  WITH (NOLOCK)
                outer apply(
					select top 1
						PC.PersonCard_id,
						PC.Person_id,
						PC.Lpu_id,
						PC.LpuRegion_id,
						ISNULL(L.Lpu_Nick,L.Lpu_Name) as Lpu_atNick,
						ISNULL(LR.LpuRegionType_Name,'') + ' №' + LR.LpuRegion_Name as LpuRegion_Name
					from v_PersonCard PC (nolock)
					left join v_Lpu L (nolock) on L.Lpu_id = PC.Lpu_id
					left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
					{$CardJoin}
					where PC.Person_id = FromTable.Person_id
				) PCard
				left join v_Lpu L2 (nolock) on L2.Lpu_id = FromTable.Lpu_id
                {$join}
                 -- end from  
                 Where
                 -- where
                  " . $filter . "
                     {$where} 
                       -- end where   
                     order by 
                    -- order by                     
                    DatePurpose

                    -- end order by";


		log_message('debug', 'GetVacAssigned4CabVac: $sql =' . $sql);

		//         замена функции преобразования запроса в запрос для получения количества записей
		$count_sql = getCountSQLPH($sql);


		if (isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0) {
			$sql = getLimitSQLPH($sql, $data['start'], $data['limit']);
			log_message('debug', 'getLimitSQLPH=' . $sql);
		}

		$res = $this->db->query($sql, $queryParams);

		log_message('debug', '$count_sql =' . $count_sql);

		// определение общего количества записей
		$count_res = $this->db->query($count_sql, $queryParams);
		if (is_object($count_res)) {
			$cnt_arr = $count_res->result('array');
			$count = $cnt_arr[0]['cnt'];
			log_message('debug', 'countSQL=' . $count);
		} else
			return false;

		if (is_object($res)) {
			$response = $res->result('array');
			$response[] = array('__countOfAllRows' => $count);
			return $response;
		} else {
			return false;
		}

	}

	/**
	 * Список подотчетных Уфе мед. организаций
	 */
	public function GetLpu4Report($data)
	{

		$filters = array();
		$join = '';
		$queryParams = array();
		$is_ufa = ($_SESSION['region']['nick']=='ufa')?1:0;
		$sql = "
                Select  
                    -- select     
                    lpu_id, 
                    Lpu_Nick, 
                    Lpu_Name, 
                    UAddress_Address 
                    -- end select   
                from 
                   -- from
                    vac.fn_GetLpu4Report (null, {$is_ufa}) 
                    -- end from  
                    order by 
                    -- order by  
                    Lpu_Nick
                    -- end order by
                    ";


		$count_sql = getCountSQLPH($sql);


		if (isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0) {
			$sql = getLimitSQLPH($sql, $data['start'], $data['limit']);
			//log_message('debug', 'getLimitSQLPH=' . $sql);
		}

		$res = $this->db->query($sql, $queryParams);

		// определение общего количества записей
		$count_res = $this->db->query($count_sql, $queryParams);
		if (is_object($count_res)) {
			$cnt_arr = $count_res->result('array');
			$count = $cnt_arr[0]['cnt'];
			log_message('debug', 'countSQL=' . $count);
		} else
			return false;

		if (is_object($res)) {
			$response = $res->result('array');
			$response[] = array('__countOfAllRows' => $count);
			return $response;
		} else {
			return false;
		}

	}

	/**
	 * Список подотчетных Уфе мед. организаций c указанием количества введенных карт
	 */
	public function GeCountingKartVac($data)
	{


		$filter = "";
		$lpu_id = $this->nvl($_SESSION['lpu_id']);
		if (isset($lpu_id)) {
			$filter = " Where Lpu_id = " . $lpu_id;

		} else return false;
        $is_ufa = ($_SESSION['region']['nick']=='ufa')?1:0;
		$join = '';
		$queryParams = array();
		log_message('debug', 'ARMType=' . $data['ARMType']);
		if ($data['ARMType'] == 'epidem_mo') {
			$lpu_id = $this->nvl($_SESSION['lpu_id']);

			if (isset($data['calc']) && $data['calc'] > 0) {
				$sql = "
                    Select 
                        -- select  
                            t.Lpu_id, LpuRegion_Name, LpuBuilding_name UAddress_Address, reg.kol0, a.kol  
                        -- end select                          
                        from
                        -- from
                            (
                            SELECT  LpuRegion_id Lpu_id
                          ,LpuRegionType_Name + ' №' + LpuRegion_Name LpuRegion_Name
                          ,LpuRegionType_Name + ' №' + LpuRegion_Name Lpu_Name
                          , LpuBuilding_name 
                      FROM vac.v_Region2Section WITH (NOLOCK)
                            --where Lpu_id = @Lpu_id
                            {$filter} 
                            union
                            SElect -1 Lpu_id, ' Не определен' Lpu_Nick, 'Не определен' Lpu_Name, null UAddress_Address
                             ) t
                              left join (Select LpuRegion_id lpu_id, COUNT(8) kol0 from vac.v_vac_PersonRegion reg  WITH (NOLOCK) 
                                            --where  lpu_id  = @Lpu_id
                                            {$filter} 
                                            group by LpuRegion_id ) reg on t.lpu_id = reg.lpu_id
                             left join (SElect lpu_id, kol from (
                                                        Select LpuRegion_id Lpu_id, count(8) kol from (Select LpuRegion_id,  Person_id  
                                                            from (SElect 
                                                                            case
                                                                            when Lpu_id <> lpu_id_attach then	
                                                                                    -1
                                                                            else
                                                                                    isnull(LpuRegion_id, -1)	
                                                                            end LpuRegion_id, 
                                                                            Person_id from vac.v_JournalAccount a WITH (NOLOCK)
                                                                    --where  lpu_id  = @Lpu_id
                                                                    {$filter}                                                                     
                                                                    ) t0
                                                                group by LpuRegion_id, Person_id) t
                                                                group by LpuRegion_id
                                                                ) a
                                                                )a on  a.Lpu_id = t.Lpu_id
                          -- end from         
                          order by 
                          -- order by  
                          LpuRegion_Name 
                          -- end order by
                    ";
			} else {
				$sql = "
                    Select 
                            -- select    
                            t.Lpu_id, LpuRegion_Name, LpuBuilding_name UAddress_Address
                          -- end select     
                          from 
                          -- from
                            (
                            SELECT  LpuRegion_id Lpu_id
                                  ,LpuRegionType_Name + ' №' + LpuRegion_Name LpuRegion_Name
                                  ,LpuRegionType_Name + ' №' + LpuRegion_Name Lpu_Name
                                  , LpuBuilding_name 
                              FROM vac.v_Region2Section WITH (NOLOCK)
                                   -- where Lpu_id = @Lpu_id
                                    {$filter} 
                                    ) t     
                              -- end from         
                               order by 
                               -- order by  
                               LpuRegion_Name
                               -- end order by";
			}
		} else {
			if (isset($data['calc']) && $data['calc'] > 0) {
				$sql = "
                            Select  
                                -- select     
                                t.Lpu_id, 
                                l.Lpu_Nick,  
                                l.Lpu_Name, 
                                UAddress_Address, 
                                t2.kol0,
                                isnull(t.kol, 0) kol
                                -- end select   
                            from 
                               -- from
                               vac.fn_GetLpu4Report (null, {$is_ufa}) l
                                    left join (SElect lpu_id, COUNT(8) kol from (
                                            Select a.Lpu_id,  Person_id  from vac.v_JournalAccount a WITH (NOLOCK)
                                                    ,vac.fn_GetLpu4Report (null, {$is_ufa}) l
                                                    where a.Lpu_id = l.lpu_id
                                                    group by a.Lpu_id, Person_id) t
                                                    group by lpu_id ) t on  l.Lpu_id = t.Lpu_id
                                     left join    (Select l.Lpu_id, COUNT(8) kol0
                                                    from vac.v_vac_Person pers WITH (NOLOCK), vac.fn_GetLpu4Report (null, {$is_ufa}) l
                                                    where pers.Lpu_id = l.lpu_id
                                                    group by l.Lpu_id) t2 on l.Lpu_id = t2.Lpu_id                 
                                -- end from  
                                order by 
                                -- order by  
                                kol desc
                                -- end order by
                                ";
			} else {
				$sql = "
                    Select  
                        -- select     
                        lpu_id, 
                        Lpu_Nick, 
                        Lpu_Name, 
                        UAddress_Address 
                        -- end select   
                    from 
                       -- from
                        vac.fn_GetLpu4Report (null, {$is_ufa}) 
                        -- end from  
                        order by 
                        -- order by  
                        Lpu_Nick
                        -- end order by
                        ";
			}
		}
		$queryParams['lpu_id'] = $this->nvl($_SESSION['lpu_id']);
		log_message('debug', '$sql=' . $sql);

		$count_sql = getCountSQLPH($sql);
		//        //*********
		//        $count_sql = "
		//            Declare
		//                     @lpu_id bigint  = :lpu_id;
		//            
		//                        Select Count (8) cnt
		//     
		//                          from 
		//
		//                            (
		//                            SELECT  LpuRegion_id Lpu_id
		//                                  ,LpuRegionType_Name + ' №' + LpuRegion_Name Lpu_Nick
		//                                  ,LpuRegionType_Name + ' №' + LpuRegion_Name Lpu_Name
		//                                  , LpuBuilding_name UAddress_Address
		//                              FROM vac.v_Region2Section with(nolock)
		//                                    where Lpu_id = @Lpu_id
		//                                    ) t  ;   
		//";
		//        //************


		if (isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0) {
			$sql = getLimitSQLPH($sql, $data['start'], $data['limit']);
			//log_message('debug', 'getLimitSQLPH=' . $sql);
		}

		$res = $this->db->query($sql, $queryParams);

		// определение общего количества записей
		$count_res = $this->db->query($count_sql, $queryParams);
		if (is_object($count_res)) {
			$cnt_arr = $count_res->result('array');
			$count = $cnt_arr[0]['cnt'];
			log_message('debug', 'countSQL=' . $count);
		} else
			return false;

		if (is_object($res)) {
			$response = $res->result('array');
			$response[] = array('__countOfAllRows' => $count);
			return $response;
		} else {
			return false;
		}

	}


	/**
	 * Запускает функцию Date_Add
	 * Нигматуллин Тагир  22.05.1014
	 */
	public function VacDateAdd($data)
	{


		$query = "
			DECLARE 
                            @BaseDate date,
                            @Type int,
                            @Add_Num int;
                            
                         Set @BaseDate = :BaseDate;
                         Set @Type = :Type;
                         Set @Add_Num = :Add_Num ;
                            
                        
                        Select convert(date, vac.Date_Add (@Type, @Add_Num, @BaseDate)) Result_Date
			--Select convert(varchar, vac.Date_Add (@Type, @Add_Num, @BaseDate), 104) Result_Date
    ";

		$queryParams = array();
		$queryParams['BaseDate'] = $this->nvl($data['BaseDate']);
		$queryParams['Type'] = $this->nvl($data['Type']);
		$queryParams['Add_Num'] = $this->nvl($data['Add_Num']);

		$result = $this->db->query($query, $queryParams);
		//    $result = $this->db->query($query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * конвертер dataIndex в name column DB
	 */
	public function getNameColumn($dataIndex)
	{
		switch ($dataIndex) {
			case 'Vaccine_Name':
				$column = 'Vaccine_Name';
				break;
			case 'Seria':
				$column = 'VacPresence_Seria';
				break;
			case 'Manufacturer':
				$column = 'VacPresence_Manufacturer';
				break;
			case 'Name_toHave':
				$column = 'VacPresence_NameToHave';
				break;
			case 'Name_toHave':
				$column = 'VacPresence_NameToHave';
				break;
			case 'MedService_Nick':
				$column = 'MedService_Nick';
				break;
			case 'StatusType_Name':
				$column = 'StatusType_Name';
				break;
			case 'Infection':
				$column = 'VaccineType_Name';
				break;
			case 'SurName':
				$column = 'SurName';
				break;
			case 'FirName':
				$column = 'FirName';
				break;
			case 'SecName':
				$column = 'SecName';
				break;
			case 'Vaccine_FullName':
				$column = 'Vaccine_FullName';
				break;
			case 'Vaccine_NameInfection':
				$column = 'VaccineType_Name';
				break;

		}
		return $column;
	}    //  end getNameColumn

	/**
	 * Получение списка вакцин справочника "Наличие вакцин"
	 */

	public function GetVacPresence($data)
	{

		$filter = "";
		$lpu_id = $this->nvl($_SESSION['lpu_id']);

		//Фильтр грида
		//if (isset($data['Filter'])) {
		log_message('debug', 'Filter=' . $data['Filter']);
		//}
		//$json = isset($data['Filter']) ? iconv('windows-1251', 'utf-8', trim($data['Filter'],'"')) : false;

		$json = isset($data['Filter']) ? trim($data['Filter'], '"') : false;

		$filter_mode = isset($data['Filter']) ? json_decode($json, 1) : false;

		$where = '';
		if (isset($data['Filter'])) {
			log_message('debug', '$json=' . $json);
			log_message('debug', '$filter_mode=' . $json);


			foreach ($filter_mode as $col => $val) {

				foreach ($val as $v) {
					$tempIn[] = "'" . $v . "'";
				}

				$temp = implode(',', $tempIn);

				//$whereMore[] = ' ['.$this->getNameColumn($col).'] in('.iconv('utf-8','windows-1251',$temp).')';
				$whereMore[] = ' [' . $this->getNameColumn($col) . '] in(' . $temp . ')';
			}

			$where = (isset($whereMore)) ? ' and ' . implode(' and ', $whereMore) : $where;

		}

		if (isset($lpu_id)) {
			$filter = " Where Lpu_id = " . $lpu_id . $where;

		};

		$sql = "
                            SELECT  VacPresence_id
                                ,Vaccine_id
                                ,Vaccine_Name
                                ,VacPresence_Seria Seria
                                ,convert(Varchar, VacPresence_Period, 104) Period
                                ,VacPresence_Manufacturer Manufacturer
                                ,VacPresence_toHave toHave
                                ,VacPresence_NameToHave Name_toHave
                                ,lpu_id
                            FROM vac.v_VacPresence  WITH (NOLOCK)
							 {$filter} 
                            order by Vaccine_Name";

		log_message('debug', 'GetVacPresence=' . $sql);

		$result = $this->db->query($sql);

		if (is_object($result)) {
			return $result->result('array');
		} else
			return false;

	} //end GetVacPresence

	/**
	 * Получаем список прививок (прочих)
	 */

	public function GetListVaccineTypeOther()
	{
		$queryParams = array();

		$query = "
        SELECT  VaccineType_id
            ,VaccineType_Name
        FROM vac.S_VaccineType priv WITH (NOLOCK)
	where (priv.VaccineType_SignEmergency = 1 or priv.VaccineType_SignEpidem = 1)
            order by VaccineType_Name; 
            ";
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
	}

	/**
	 * Получаем методы диакностики туберкулеза (манту, диаскинтест)
	 */

	public function GetTubDiagnosisTypeCombo()
	{
		$queryParams = array();

		$query = "
        SELECT  TubDiagnosisType_id
              ,TubDiagnosisType_Name    
          FROM vac.S_TubDiagnosisType WITH (NOLOCK)
                order by TubDiagnosisType_Name 
            ";
		$result = $this->db->query($query, $queryParams);

		log_message('debug', 'GetTubDiagnosisTypeCombo: $query=' . $query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
	}


	/**
	 * Получение списка Вакцин из справочника вакцин
	 * Используется: окно просмотра и редактирования справочника вакцин
	 */

	public function getVaccineGridDetail($data)
	{

		//Фильтр грида
		$json = isset($data['Filter']) ? trim($data['Filter'], '"') : false;

		$filter_mode = isset($data['Filter']) ? json_decode($json, 1) : false;

		$where = '';
		$join = '';

		if (isset($data['Filter'])) {
			log_message('debug', '$json=' . $json);
			log_message('debug', '$filter_mode=' . $filter_mode);

			foreach ($filter_mode as $col => $val) {

				foreach ($val as $v) {
					$tempIn[] = "'" . $v . "'";
				}

				$temp = implode(',', $tempIn);
				log_message('debug', 'getNameColumn($col)=' . getNameColumn($col));

				if ($col == 'Vaccine_NameInfection')
					$joinMore[] = ' [' . $this->getNameColumn($col) . '] in(' . $temp . ')';
				else
					$whereMore[] = ' [' . $this->getNameColumn($col) . '] in(' . $temp . ')';
			}

			$where = (isset($whereMore)) ? ' where ' . implode(' and ', $whereMore) : $where;

			if (isset($joinMore)) {
				$join = "
					join	vac.S_VaccineRelType rel   WITH (NOLOCK) on vac.Vaccine_id = rel.Vaccine_id
					join vac.S_VaccineType tp  WITH (NOLOCK) on rel.VaccineType_id = tp.VaccineType_id and " . implode(' and ', $joinMore);

			}
		}

		$sql = "
			SELECT vac.Vaccine_id
                              ,Vaccine_Name
                              ,Vaccine_SignComb
                              ,Vaccine_Nick
                              ,Vaccine_FullName
                              ,Vaccine_NameInfection
                              ,Vaccine_AgeRange2Sim
                              ,Vaccine_WayPlace
                              ,Vaccine_dose
                       FROM vac.v_Vaccine vac  WITH (NOLOCK)
                          {$join}
                              {$where}
                            order by Vaccine_FullName .;
                          ";

		$result = $this->db->query($sql);

		if (is_object($result)) {
			return $result->result('array');
		} else
			return false;

	} //end getVaccineGridDetail()

	/**
	 * Получаем список схем (прочих)
	 */
	public function GetOtherVacScheme($data)
	{
		$queryParams = array();
		//log_message('debug', 'GetOtherVacScheme2');
		$sql = "
        Declare
            @Vaccine_id bigint = :Vaccine_id;
        SElect  
                OtherVacAgeBorders_id, 
                
		Vaccine_id,
                Sort_id,
		Vaccine_AgeRange2Sim,
                OtherVacAgeBorders_4GroupRisk,
                GroupRisk_Name,
                multiplicity_Name,      
                interval 
               
        from vac.v_OtherVacScheme v WITH (NOLOCK)
                where Vaccine_id = @Vaccine_id
                    and ISNULL(StatusType_id, 0) = 0
                order by sort_id, OtherVacAgeBorders_4GroupRisk
            ";

		log_message('debug', 'GetOtherVacScheme :Vaccine_id =' . $data['Vaccine_id']);
		$queryParams['Vaccine_id'] = $this->nvl($data['Vaccine_id']);
		log_message('debug', 'GetOtherVacScheme :$sql =' . $sql);
		$result = $this->db->query($sql, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}

	}

	/**
	 * Получаем список прививок для вывода на экран
	 */
	public function GetVaccineTypeGrid()
	{
		$queryParams = array();
		log_message('debug', 'GetVaccineTypeGrid1');
		$query = "
            SELECT VaccineType_id
            
              ,VaccineType_Name
            
              ,VaccineType_NameIm
              --,VaccineType_SignNatCal
              ,case
                    when isnull(VaccineType_SignNatCal, 2) = 1
                        then 'true'
                    else 'false'  
                end VaccineType_SignNatCal
              ,VaccineType_SignNatCalName
             
              --,VaccineType_SignScheme
              ,case
                    when isnull(VaccineType_SignScheme, 2) = 1
                        then 'true'
                    else 'false'  
                end VaccineType_SignScheme
              ,VaccineType_SignSchemeName
               
              --,VaccineType_SignEmergency
              ,case
                    when isnull(VaccineType_SignEmergency, 2) = 1
                        then 'true'
                    else 'false'  
                end VaccineType_SignEmergency
              ,VaccineType_SignEmergencyName
             
              --,VaccineType_SignEpidem
              ,case
                    when isnull(VaccineType_SignEpidem, 2) = 1
                        then 'true'
                    else 'false'  
                end VaccineType_SignEpidem
              ,VaccineType_SignEmergencyName
              ,VaccineType_SignEpidemName
             
            FROM vac.v_VaccineType4interface WITH (NOLOCK)
                order by VaccineType_SignNatCal desc, VaccineType_Name
        ";
		log_message('debug', 'GetVaccineTypeGrid: '.$query);
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
	}


	/**
	 * ф-ция проверки значения
	 */
	public function nvl(&$var)
	{
		if (isset($var)) {
			return $var;
		} else {
			return null;
		}
	}

	/**
	 * Проф прививки. Сохранение извещения
	 */
	public function saveVaccinationNotice($data){
		$data['NotifyReaction_id'] = (!empty($data['NotifyReaction_id'])) ? $data['NotifyReaction_id'] : null;
		if (empty($data['NotifyReaction_id'])) {
			$procedure = "vac.p_NotifyReaction_ins";
		} else {
			$procedure = "vac.p_NotifyReaction_upd";
		}
		//$data['NotifyReaction_Descr'] = (!empty($data['NotifyReaction_Descr'])) ? $data['NotifyReaction_Descr'] : '';
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :NotifyReaction_id;
			exec {$procedure}
				@NotifyReaction_id = @Res OUTPUT,
				@vacJournalAccount_id = :vacJournalAccount_id,
				@MedPersonal_id = :MedPersonal_id,
				@NotifyReaction_createDate = :NotifyReaction_createDate,
				@NotifyReaction_confirmDate = :NotifyReaction_confirmDate,
				@NotifyReaction_Descr = :NotifyReaction_Descr,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as NotifyReaction_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//echo getDebugSQL($query, $data);exit;
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при сохранении извещения'));
		}
	}
	
	/**
	 * Проф прививки. Получить извещения
	 */
	public function loadVaccinationNotice($data){
		$result = array();
		if(empty($data['vacJournalAccount_id']) && empty($data['NotifyReaction_id'])) return $result;
		$where = '';
		if(!empty($data['vacJournalAccount_id'])){
			$where .= ' and FromTable.vacJournalAccount_id = :vacJournalAccount_id';
		}
		if(!empty($data['NotifyReaction_id'])){
			$where .= ' and NR.NotifyReaction_id = :NotifyReaction_id';
		}
		$query = "
			SELECT  TOP 1
				FromTable.vacJournalAccount_id
				,convert(varchar, FromTable.vacJournalAccount_DateVac, 104) Date_Vac
				,FromTable.Vaccine_name vac_name
				,FromTable.vacJournalAccount_Seria Seria
				,FromTable.vacJournalAccount_Dose VACCINE_DOZA
				,case 
					when p.VaccinePlace_Name is null and wp.VaccinePlace_Name is null and  FromTable.VaccinePlace_id is not null then
						wp.VaccineWay_Name
					when p.VaccinePlace_Name is null and FromTable.VaccinePlace_id is not null then 
						wp.VaccinePlace_Name+': '+wp.VaccineWay_Name 
					else p.VaccinePlace_Name+': '+w.VaccineWay_Name 
				end as WAY_PLACE
				,L2.Lpu_Nick
				,ISNULL(convert(varchar, NR.NotifyReaction_confirmDate, 104), '') as NotifyReaction_confirmDate
				,ISNULL(convert(varchar, NR.NotifyReaction_createDate, 104), '') as NotifyReaction_createDate
				,FromTable.vacJournalAccount_Vac_MedPersonal_id AS MedPersonal_id
				,MP.MedPersonal_Name
				,NR.NotifyReaction_Descr
			FROM  
				vac.v_JournalAccountAll FromTable  WITH (NOLOCK)
				left join v_Lpu L2 (nolock) on L2.Lpu_id = FromTable.Lpu_id
				LEFT JOIN vac.S_VaccineWay w WITH (NOLOCK) ON FromTable.VaccineWay_id = w.VaccineWay_id
				LEFT JOIN vac.v_VaccinePlace p WITH (NOLOCK) ON FromTable.VaccinePlace_id = p.VaccinePlace_id
				OUTER APPLY(
					SELECT TOP 1
						p2.VaccinePlace_Name, w2.VaccineWay_Name
					FROM vac.v_VaccineWayPlace wp2  WITH (NOLOCK)
						LEFT JOIN vac.v_VaccinePlace p2 WITH (NOLOCK) ON wp2.VaccinePlace_id = p2.VaccinePlace_id
						LEFT JOIN vac.S_VaccineWay w2 WITH (NOLOCK) ON wp2.VaccineWay_id = w2.VaccineWay_id
					WHERE FromTable.VaccinePlace_id = wp2.VaccineWayPlace_id
				) wp
				LEFT JOIN vac.v_NotifyReaction NR WITH(NOLOCK) ON NR.vacJournalAccount_id = FromTable.vacJournalAccount_id
				LEFT JOIN vac.v_VacMedPersonal MP (nolock) ON MP.MedPersonal_id = FromTable.vacJournalAccount_Vac_MedPersonal_id
			WHERE 1=1
				{$where}
		";
		//echo getDebugSQL($query, $data); die();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Проф прививки. Удалить извещения
	 */
	public function deleteVaccinationNotice($data){
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec vac.p_NotifyReaction_del
				@NotifyReaction_id = :NotifyReaction_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
     * Получить извещения проф прививок
     */
    function loadGridVaccinationNotice($data) {
		$filter = '';
		if(!empty($data['Person_SurName'])){
			$data['Person_SurName'] = rtrim($data['Person_SurName']);
			$filter .= ' and FromTable.SurName like :Person_SurName+\'%\'';
		}
		
		if(!empty($data['Person_FirName'])){
			$data['Person_FirName'] = rtrim($data['Person_FirName']);
			$filter .= ' and FromTable.FirName like :Person_FirName+\'%\'';
		}
			
		if(!empty($data['Person_SecName'])){
			$data['Person_SecName'] = rtrim($data['Person_SecName']);
			$filter .= ' and FromTable.SecName like :Person_SecName+\'%\'';
		}
			
		if(!empty($data['Lpu_id']))
			$filter .= ' and FromTable.Lpu_id = :Lpu_id';
		
		if(!empty($data['vac_name'])) {
			//$filter .= ' and FromTable.Vaccine_name = :vac_name';
			$filter .= ' and FromTable.Vaccine_name like :vac_name+\'%\'';
		}
		
		if(!empty($data['Seria'])){
			//$filter .= ' and FromTable.vacJournalAccount_Seria = :Seria';
			$filter .= ' and FromTable.vacJournalAccount_Seria like :Seria+\'%\'';
		}
		
		
		if( !empty($data['CreateMotification_datePeriod_beg']) ) {
			$filter .= " and NR.NotifyReaction_createDate >= :CreateMotification_datePeriod_beg";
		}
		if( !empty($data['CreateMotification_datePeriod_end']) ) {
			$filter .= " and NR.NotifyReaction_createDate <= :CreateMotification_datePeriod_end";
		}
		if( !empty($data['VaccinationPerformance_datePeriod_beg']) ) {
			$filter .= " and FromTable.vacJournalAccount_DateVac >= :VaccinationPerformance_datePeriod_beg";
		}
		if( !empty($data['VaccinationPerformance_datePeriod_end']) ) {
			$filter .= " and  FromTable.vacJournalAccount_DateVac <= :VaccinationPerformance_datePeriod_end";
		}
		
		$query = "
			SELECT top (100)
			-- select
				NR.NotifyReaction_id
				,convert(varchar, FromTable.vacJournalAccount_DateVac, 104) AS NotifyReaction_confirmDate --Дата исполнения
				,convert(varchar, NR.NotifyReaction_createDate, 104) AS NotifyReaction_createDate
				,FromTable.SurName as Person_SurName
				,FromTable.FirName as Person_FirName
				,FromTable.SecName as Person_SecName
				,FromTable.Vaccine_name vac_name
				,FromTable.vacJournalAccount_Seria Seria
				,FromTable.vacJournalAccount_Dose VACCINE_DOZA
				,MP.MedPersonal_Name AS Executed_MedPersonal_Name --Исполнивший врач
				,MP2.MedPersonal_Name AS CreateNotification_MedPersonal_Name --Врач, создавший извещение
			-- end select
			FROM
			-- from
				vac.v_NotifyReaction NR WITH(NOLOCK)
				LEFT JOIN vac.v_JournalAccountAll FromTable WITH (NOLOCK) on NR.vacJournalAccount_id = FromTable.vacJournalAccount_id
				LEFT JOIN vac.v_VacMedPersonal MP (nolock) ON MP.MedPersonal_id = FromTable.vacJournalAccount_Vac_MedPersonal_id
				LEFT JOIN vac.v_VacMedPersonal MP2 (nolock) ON MP2.MedPersonal_id = NR.MedPersonal_id
			-- end from
			WHERE 
			-- where
				1=1
				{$filter}
			-- end where
			ORDER BY 
			-- order by
				NR.NotifyReaction_createDate
			-- end order by
		";
		//		echo getDebugSql($query, $data);die();
		if (isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0) {
			$limit_query = getLimitSQLPH($query, $data['start'], $data['limit']);
			$result = $this->db->query($limit_query, $data);
		} else {
			$result = $this->db->query($query, $data);
			$data['start'] = 0;
		}
		
		$response['data'] = $result->result('array');
		$response['totalCount'] = $data['start'] + count($response['data']);
		
		return $response;
    }
	
	/** 
	 * Получение медикамента в форме исполнения прививки
	 */
	function loadContainerMedicinesViewGrid($data) {
		if(empty($data['DocumentUcStr_id'])) return false;
		$sql = "
			SELECT 
				RTRIM(ISNULL(DrugPrep.DrugPrep_Name, '')) as DrugPrep_Name,
				DrugPrep.DrugPrep_id as DrugPrep_id,
				DrugPrep.DrugPrepFas_id as DrugPrepFas_id,
				ED.EvnDrug_KolvoEd, --Кол-во единиц списания
				GU.GoodsUnit_Nick, --ед. списания
				DuS.DocumentUcStr_id,
				ED.EvnDrug_id,
				Dus.DocumentUcStr_Ser,
				PS.PrepSeries_Ser,
				convert(varchar(20),convert(float,ED.EvnDrug_KolvoEd)) + ' ' + GU.GoodsUnit_Nick AS Doza
			FROM v_DocumentUcStr DuS with (nolock)
				INNER JOIN v_EvnDrug ED with (nolock) ON ED.DocumentUcStr_id = DuS.DocumentUcStr_id
				INNER JOIN rls.v_Drug Drug with (nolock) on Drug.Drug_id = ED.Drug_id
				INNER JOIN rls.v_DrugPrep DrugPrep with (nolock) ON Drug.DrugPrepFas_id = DrugPrep.DrugPrepFas_id
				LEFT JOIN v_GoodsUnit GU WITH(nolock) ON GU.GoodsUnit_id = DuS.GoodsUnit_id
				LEFT JOIN rls.v_PrepSeries PS WITH(NOLOCK) ON PS.PrepSeries_id = DuS.PrepSeries_id
			WHERE DuS.DocumentUcStr_id = :DocumentUcStr_id
		";
		
		$result = $this->db->query($sql, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}
	
	/** 
	 * Сохранение идентификатор строки документа учета медикаментов в vacJournalAccount
	 */
	function saveDocumentUcStrIDforJournalAccount($data) {
		if(empty($data['vacJournalAccount_id'])) return false;
		$data['DocumentUcStr_id'] = (!empty($data['DocumentUcStr_id'])) ? $data['DocumentUcStr_id'] : null;
		$query = "
			update vac.vac_JournalAccount with (rowlock) set DocumentUcStr_id = :DocumentUcStr_id where vacJournalAccount_id = :vacJournalAccount_id
		";
		
		$result = $this->db->query($query, $data);
		return array(array('success' => $result, 'DocumentUcStr_id' => $data['DocumentUcStr_id'], 'vacJournalAccount_id' => $data['vacJournalAccount_id']));
	}  
	
	/**
	 * Удаление идентификатора строки документа учета медикаментов в vacJournalAccount
	 */
	function deleteDocumentUcStr($data){
		if(empty($data['DocumentUcStr_id']) || empty($data['EvnDrug_id'])) return false;
		$query = "
            SELECT TOP 1 vacJournalAccount_id
			FROM vac.vac_JournalAccount  VJA WITH(nolock)
				INNER JOIN DocumentUcStr DUS WITH(nolock) ON DUS.DocumentUcStr_id = VJA.DocumentUcStr_id
			WHERE DUS.EvnDrug_id = :EvnDrug_id AND VJA.DocumentUcStr_id = :DocumentUcStr_id
			ORDER BY vacJournalAccount_id DESC
        ";
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			$res = $result->result('array');
			if($res[0]['vacJournalAccount_id']){
				$res = $this->saveDocumentUcStrIDforJournalAccount(array('vacJournalAccount_id' => $res[0]['vacJournalAccount_id']));
			}
		}
		return true;
	}
	
	/** 
	 * Получение медикамента в форме исполнения прививки
	 */
	function getVaccinePlan($data) {
		if(empty($data['Person_id'])) return false;
		$sql = "
			SELECT 
				PPF.Person_id,
				PPF.vac_PersonPlanFinal_id AS plan_id,
				'1' AS row_plan_parent,
				PPF.NationalCalendarVac_Scheme_Num AS scheme_num
			FROM vac.v_PersonPlanFinal PPF  WITH (NOLOCK)
			WHERE PPF.Person_id = :Person_id
		";
		
		$result = $this->db->query($sql, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}
	
	/**
	 * Получение данных для панели просмотра ЭМК и правой панели формы добавления назначений
	 * @param $section
	 * @param $evn_pid
	 * @param $sessionParams
	 * @return array|bool
	 */
	public function doLoadViewData($section, $evn_pid, $sessionParams)
	{
		if(empty($evn_pid)) return false;
		$sql = "
			SELECT 
				--COUNT(ac.vacJournalAccount_id) AS cnt
				v.Vaccine_Name,
				CASE 
					WHEN ac.vacJournalAccount_StatusType_id = 0 THEN 'Назначено'
					WHEN ac.vacJournalAccount_StatusType_id = 1 THEN 'Исполнено'
					ELSE ''
				END StatusType_Name,	--статус
				ac.MedService_id,
				MS.MedService_Nick,		--служба
				convert(varchar, ac.vacJournalAccount_DatePurpose, 104) Date_Purpose,  --Дата исполнения
				ac.*
			from vac.vac_JournalAccount ac WITH (NOLOCK)
				left join  vac.S_Vaccine v WITH (NOLOCK) on  v.Vaccine_id = ac.vaccine_id
				LEFT JOIN dbo.v_MedService MS WITH(NOLOCK) ON MS.MedService_id = ac.MedService_id
				--left OUTER JOIN vac.v_vac_Person pers  WITH (NOLOCK) ON pers.Person_id = ac.Person_id
				--left outer join vac.S_VaccineWay way WITH (NOLOCK) on way.VaccineWay_id = ac.VaccineWay_id 
				--left outer join vac.v_VaccinePlace pl WITH (NOLOCK) on pl.VaccinePlace_id = ac.VaccinePlace_id
				--left outer join dbo.v_Lpu lpu WITH (NOLOCK) on lpu.Lpu_id = ac.vacJournalAccount_Lpu_id
			WHERE  1=1
				AND ac.vacJournalAccount_StatusType_id IN (0,1)
				AND EvnVizitPL_id = :EvnVizitPL_id
		";
		
		$result = $this->db->query($sql, array('EvnVizitPL_id' => $evn_pid));
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получаем Cписок служб "Кабинет вакцинации" со всеми данными кабинета
	 */
	public function getMedServiceVac_allData($data = array())
	{
		if(empty($data['Lpu_id'])) return false;
		$filter = " and LB.Lpu_id = :Lpu_id";

		$query = "
			SELECT DISTINCT
				MedService_id,
				MedService_Nick,
				Lpu_id,
				LpuBuilding_id,
				LpuUnit_id,
				LpuUnitType_id,
				LpuSection_id from (
									select   ms.Lpu_id, ms.LpuBuilding_id, ms.MedService_id,  ms.MedService_Nick,
											ms.LpuUnit_id, ms.LpuSection_id, ms.LpuUnitType_id
											--, l.Lpu_Nick
											--, lb.LpuBuilding_Name
											--, l.Lpu_Name
									from dbo.v_MedService ms with(nolock)
											left join dbo.v_LpuBuilding lb with(nolock) on ms.LpuBuilding_id=lb.LpuBuilding_id
											left join dbo.v_Lpu l with(nolock) on ms.Lpu_id=l.Lpu_id
									where ms.MedServiceType_id = 31
									and ms.MedService_begDT <= GETDATE()
									and (ms.MedService_endDT is null or ms.MedService_endDT >= GETDATE() )
									and (ms.MedService_endDT is null or ms.MedService_endDT >= GETDATE() )
									" . $filter . "  
								) t
		";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);

		// echo getDebugSql($query, $queryParams);die();
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (Список ЛПУ)'));
		}
	}

	public function getVaccinesDosesVaccine_List ($data) {
		if(empty($data['Org_id'])) return false;
		if(empty($data['Person_id'])) return false;
		if(empty($data['MedService_id'])) return false;

		$filter = '';
		$join = '';

		// сортировка по нац календарю
		if(!empty($data['NatCalendar'])){
			$filter .= ' and VAC.Vaccination_isNacCal = 2';
		}

		// сортировка по Эпидпоказаниям
		if(!empty($data['Vaccination_isEpidemic'])){
			$filter .= ' and VAC.Vaccination_isEpidemic = 2';
		}
		
		// обязательные параметры
		$filter .= ' and FTG.FTGGRLS_ID = 380'; // вхождение в группу МИБП-вакцина!
		$filter .= ' and VTP.VaccinationTypePrep_endDate is null'; // Препарат не имеет записи об окончании
		$filter .= ' and PDR.PersonDrugReaction_id is null'; // отсутствуют реакции и поствакцинальные осложнения пациента
		$filter .= ' and DROST.DrugOstatRegistry_Kolvo is not null'; // есть на остатках (есть запись об остатках)

		$query = "
			SELECT DISTINCT
			-- select
					[VTP].[Prep_id] as Prep_id
				-- из ФТГ ГРЛС
--					,[FTG].[PREP_FTGGRLS_ID] as FTG_PREP_FTGGRLS_ID
--					,[FTG].[PREP_ID] as FTG_PREP_ID
--					,[FTG].[FTGGRLS_ID] as FTG_FTGGRLS_ID
				-- end из ФТГ ГРЛС
				-- торговое наименование
					,[TN].[NAME] as TN_NAME
					,[TN].TRADENAMES_ID
				-- end торговое наименование
				-- остатки
					, DROST.DrugOstatRegistry_Kolvo as DrugOstatRegistry_Kolvo
					--, DROST.DrugOstatRegistry_id
					, DROST.Storage_id
--					, SSL.MedService_id
				-- end остатки
				-- ответственные лица склада
					,[MOL].Mol_id
				-- end ответственные лица склада
				-- тип вакцинации
--					,[VAC].VaccinationType_id
				-- end тип вакцинации
				-- вакцина в нац календаре
--					,[VAC].[Vaccination_isNacCal]
				-- end вакцина в нац календаре
				-- эпидпоказания 
--					,[VAC].[Vaccination_isEpidemic]
				-- end эпидпоказания
				-- группировочные торговые наименования
					, [DR].DrugPrepFas_id
				-- end группировочные торговые наименования
			-- end select

			FROM vc.v_VaccinationTypePrep [VTP]
			-- from
				-- из ФТГ ГРЛС
					LEFT JOIN rls.v_PREP_FTGGRLS FTG WITH(NOLOCK) ON FTG.PREP_ID = VTP.Prep_id
				-- end из ФТГ ГРЛС
				-- торговое наименование
						LEFT JOIN rls.v_PREP [PR] WITH (NOLOCK) ON [PR].Prep_id = [VTP].Prep_id
						LEFT JOIN rls.v_TRADENAMES [TN] WITH (NOLOCK) ON [TN].TRADENAMES_ID = [PR].TRADENAMEID
				-- end торговое наименование
				-- группировочные торговые наименования
						LEFT JOIN rls.v_Drug [DR] WITH (NOLOCK) ON [DR].Drug_id = [VTP].Prep_id
				-- end группировочные торговые наименования
				-- остатки
					-- склад
						LEFT JOIN [dbo].v_StorageStructLevel SSL WITH (NOLOCK) ON SSL.MedService_id = :MedService_id
						LEFT JOIN [dbo].v_DrugOstatRegistry DROST WITH (NOLOCK) ON 
							DROST.Drug_id = VTP.Prep_id 
							AND DROST.SubAccountType_id = 1
							AND DROST.Storage_id = SSL.Storage_id
					-- end склад
					-- ответственные лица склада
						LEFT JOIN [dbo].v_Mol MOL WITH (NOLOCK) ON (MOL.Storage_id = SSL.Storage_id AND MOL.MedPersonal_id = :MedPersonal_id)
					-- end ответственные лица склада
				-- end остатки
				-- отношения препарата к вакцинациям
				LEFT JOIN vc.v_Vaccination VAC WITH (NOLOCK) ON VAC.VaccinationType_id = VTP.VaccinationType_id
				-- end отношения препарата к вакцинациям
				-- реакции и поствакцинальные осложнения пациента
					LEFT JOIN vc.PersonDrugReaction PDR WITH(NOLOCK) ON (PDR.Tradenames_id = PR.TRADENAMEID AND PDR.Person_id = :Person_id)
				-- end реакции и поствакцинальные осложнения пациента
			-- end from

			WHERE
			-- where
				1=1
				{$filter}
			-- end where
			GROUP BY
				[VTP].[Prep_id]
				, [TN].[NAME]
				, [TN].TRADENAMES_ID
				--, [VAC].VaccinationType_id
				, DROST.Storage_id
				--,[VAC].[Vaccination_isNacCal]
				--,[VAC].[Vaccination_isEpidemic]
				,[DR].DrugPrepFas_id
				,DrugOstatRegistry_Kolvo
				,Mol_id
		";

		// echo getDebugSql($query, $data);die();
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");


		// -- остатки
			// LEFT JOIN (SELECT [DROST].DrugOstatRegistry_insDT
			// 		,[DROST].Drug_id
			// 		,[DROST].DrugOstatRegistry_Kolvo
			// 		,[DROST].DrugOstatRegistry_id
			// 	FROM dbo.v_DrugOstatRegistry [DROST]
			// 	-- с фильтрацией последней записи
			// 	WHERE 
			// 		1=1
			// 		AND [DROST].SubAccountType_id = 1  
			// 		AND [DROST].DrugOstatRegistry_insDT = (SELECT MAX(DrugOstatRegistry_insDT) FROM dbo.v_DrugOstatRegistry WHERE 1=1 and SubAccountType_id = 1 and Drug_id = [DROST].Drug_id)
			// 	-- end фильтрацией последней записи
			// ) DR ON DR.Drug_id = VTP.Prep_id
		
		// -- end остатки
	}


	/**
	 * Проверка возможности проведения вакцины в день обращения
	 * проверки на соответствие:
	 * - Минимальный возраст проведения
	 * - Максимальный возраст проведения 
	 * - Период с прошлой вакцинации по схеме
	 * 
	 * - Проверка вакцины на наличие в отводах пациента
	 */
	public function checkVaccination_AvailableToday($data) {

		//обязаталениые параметры фильтра
		$filter =" and VTP.[Prep_id] = :Prep_ID";
		$query = "
			SELECT DISTINCT
			--select
				-- типы вакцинаций с данным препаратом
					VTP.[VaccinationType_id] as VaccinationType_id
					, VTP.[Prep_id] as Prep_id
					-- код вакцинации
					, VAC.[Vaccination_Code] as Vaccination_Code
					-- end код вакцинации
					-- id вакцинации
					, VAC.[Vaccination_id] as Vaccination_id
					-- end id вакцинации
					-- название вакцинации
					, VAC.[Vaccination_Name] as Vaccination_Name
					-- end название вакцинации
				-- end типы вакцинаций с данным препаратом
				-- данные по вакцинациям
					-- дата прошлой вакцинации по данной вакцинации
						, convert(varchar, (
							SELECT TOP(1)
							EVV.EvnVaccination_setDate
							from vc.v_VaccinationData VD
								LEFT JOIN dbo.v_EvnVaccination EVV WITH(NOLOCK) ON EVV.[Person_id] = :Person_id and EVV.EvnVaccination_id = VD.EvnVaccination_id
							where VD.Vaccination_id = Vaccination_id
							ORDER BY EVV.[EvnVaccination_setDT] DESC
						), 104) as Vaccination_last_event_date
					-- end дата прошлой вакцинации по данной вакцинации
					-- id прошлой вакцинации по схеме 
						, VAC.[Vaccination_pid] as Vaccination_pid
					-- end id прошлой вакцинации по схеме 
					-- дата прошлой вакцинации по схеме
						, convert(varchar, (
							SELECT TOP(1)
							EVV.EvnVaccination_setDate
							from vc.v_VaccinationData VD
								LEFT JOIN dbo.v_EvnVaccination EVV WITH(NOLOCK) ON EVV.[Person_id] = :Person_id and EVV.EvnVaccination_id = VD.EvnVaccination_id
							where VD.Vaccination_id = Vaccination_pid
							ORDER BY EVV.[EvnVaccination_setDT] DESC
						), 104) as Vaccination_pid_event_date
					-- end дата прошлой вакцинации по схеме
					-- минимальные сроки вакцинации
						, convert(varchar, (CASE VAC.[VaccinationAgeType_bid]
							WHEN '1' THEN dateadd(dd,-VAC.[Vaccination_begAge],getDate())
							WHEN '2' THEN dateadd(mm,-VAC.[Vaccination_begAge],getDate())
							WHEN '3' THEN dateadd(YYYY,-VAC.[Vaccination_begAge],getDate())
						END ), 104) as Vaccine_MinAge
					-- end минимальные сроки вакцинации
					-- максимальный возраст для вакцинации
						,convert(varchar, (CASE VAC.[VaccinationAgeType_eid]
							WHEN '1' THEN dateadd(dd,-VAC.[Vaccination_endAge], getDate())
							WHEN '2' THEN dateadd(mm,-VAC.[Vaccination_endAge], getDate())
							WHEN '3' THEN dateadd(YYYY,-VAC.[Vaccination_endAge], getDate())
							ELSE null
						END), 104) as Vaccine_MaxAge
					-- end максимальный возраст для вакцинации
				-- end данные по вакцинациям
				-- отводы пациента
					, PersVacREF.PersonVaccinationRefuse_id as PersonVaccinationRefuse_id
					, convert(varchar, PersVacREF.PersonVaccinationRefuse_endDT, 104) as PersonVaccinationRefuse_endDT
				-- end отводы пациента
				-- дата рождения пацинета
					, convert(varchar, PersBD.[PersonBirthDay_BirthDay], 104) as PersonBirthDay_BirthDay
				-- end дата рождения пациента
				-- группы риска пациента
					, (SELECT VRGL.VaccinationRiskGroupLink_insDT
						from [vc].[v_VaccinationRiskGroupLink] VRGL
						LEFT JOIN [vc].[v_PersonVaccinationRiskGroup] PersVRG WITH(NOLOCK) ON PersVRG.[Person_id] = :Person_id
						where 
						VRGL.[VaccinationRiskGroup_id] = PersVRG.VaccinationRiskGroup_id
						and VRGL.[VaccinationType_id] = VTP.[VaccinationType_id]
						) as PersonVaccinationRiskGroup_insDT
				-- end группы риска пациента
			-- end select
			FROM
			-- from
				-- типы вакцинаций с данным препаратом
					[vc].[v_VaccinationTypePrep] VTP
				-- end типы вакцинаций с данным препаратом
				-- данные по пациенту
					LEFT JOIN [dbo].[v_PersonBirthDay] PersBD WITH(NOLOCK) ON PersBD.[Person_id] = :Person_id
				-- end данные по пациенту
				-- данные по вакцинациям
					LEFT JOIN [vc].[v_Vaccination] VAC WITH(NOLOCK) ON VAC.[VaccinationType_id] = VTP.[VaccinationType_id]
				-- end данные по вакцинациям
				-- отводы пациента
					LEFT JOIN [vc].[v_PersonVaccinationRefuse] PersVacREF WITH(NOLOCK) ON PersVacREF.[Person_id] = :Person_id and PersVacREF.VaccinationType_id = VAC.[VaccinationType_id]
				-- end отводы пациента
			-- end from	
			WHERE
			-- where
				1=1
				{$filter}
			-- end where	
		";

		// echo getDebugSql($query, $data);die();
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}


}

/*
 * Константы "Типы журналов"
 */

final class TYPE_JOURNAL
{

	const VAC_MAP = 'VacMap';
	const VAC_REFUSE = 'VacRefuse';
	const VAC_ASSIGNED = 'VacAssigned';
	const VAC_REGISTR = 'VacRegistr';
	const VAC_PLAN = 'VacPlan';
	const TUB_PLAN = 'TubPlan';
	const TUB_ASSIGNED = 'TubAssigned';
	const TUB_REACTION = 'TubReaction';
	const VAC_4CabVac = 'Vac4CabVac';
}


?>