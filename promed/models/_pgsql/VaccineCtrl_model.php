<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Класс модели для работы по иммунопрофилактики
 *
 * @author    ArslanovAZ
 * @version   17.04.2012
 */
class VaccineCtrl_model extends SwPgModel
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
			SELECT 
			    List_Journals_Id as \"Journal_id\", 
			    Name as \"Name\"
			FROM vac.Vac_List_Journals  
			ORDER BY sort
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
			SELECT
			-- select
				PK.Person_id as \"Person_id\"
				,PK.FirName as \"FirName\"
				,PK.SurName as \"SurName\"
				,PK.SecName as \"SecName\"
				,to_char(PK.BirthDay, 'DD.MM.YYYY') as \"BirthDay\"

				,PK.vac_Person_Sex_id as \"Sex_id\"
				,PK.vac_Person_sex as \"sex\"
				,PK.Address as \"Address\"
				--,PK.vac_Person_uch [uch]
				,PK.SocStatus_Name as \"SocStatus_Name\"
				,PK.Lpu_id as \"Lpu_id\"
				,PK.Lpu_Name as \"Lpu_Name\"
				,PK.group_risk as \"group_risk\"
				,PK.vacPersonKart_Age as \"Age\"
				,PK.Server_id as \"Server_id\"
				,PK.PersonEvn_id as \"PersonEvn_id\"
				,PK.LpuRegion_id as \"LpuRegion_id\"
				,PK.Org_id as \"Org_id\"
				,PK.Person_dead as \"Person_dead\"
				,L2.Lpu_Nick as \"Lpu_Nick\"
				,PCard.LpuRegion_Name as \"uch\"
				,PCard.Lpu_atNick as \"Lpu_atNick\"
			-- end select
			FROM
			-- from
				vac.v_vacPersonKart PK 

				{$join}
				INNER JOIN LATERAL (

					select 
						PC.PersonCard_id, 
						PC.Lpu_id,
						PC.LpuRegion_id,
						COALESCE(LR.LpuRegionType_Name,'') || ' №' || COALESCE(LR.LpuRegion_Name,'') as LpuRegion_Name,

						COALESCE(L.Lpu_Nick,L.Lpu_Name) as Lpu_atNick

					from v_PersonCard PC 

					left join v_LpuRegion LR  on LR.LpuRegion_id = PC.LpuRegion_id

					left join v_Lpu L  on L.Lpu_id = PC.Lpu_id

					where PC.Person_id = PK.Person_id
						and LpuAttachType_id = 1
                    limit 1
				) PCard ON true
				left join v_Lpu L2  on L2.Lpu_id = PK.Lpu_id
			-- end from
			" . ImplodeWherePH($filters) . "
			ORDER BY
			-- order by
			PK.SurName, PK.FirName, PK.SecName
			-- end order by
			LIMIT 100
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
								VR.VaccineRisk_id as \"VaccineRisk_id\",
								VT.VaccineType_NameIm as \"VaccineType_NameIm\"
							from vac.v_VaccineRisk VR 

							inner join vac.v_VaccineType VT  on VT.VaccineType_id = VR.VaccineType_id

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
            $filters[] = "vac_PersonPlanFinal_DatePlan <= :Date_Plan1";
            $queryParams['Date_Plan1'] = $data['Date_Plan'][1];
        }
        
        $this->genSearchFilters(TYPE_JOURNAL::VAC_PLAN, $data, $filters, $queryParams, $join, $CardJoin);

		$sql = "
            SELECT 
            -- select
			FromTable.vac_PersonPlanFinal_id as \"planTmp_id\"
			,FromTable.NationalCalendarVac_Scheme_Num as \"Scheme_num\"
			,to_char(FromTable.vac_PersonPlanFinal_DatePlan, 'DD.MM.YYYY') as \"Date_Plan\"

			,FromTable.Person_id as \"Person_id\"
			,FromTable.SurName as \"SurName\"
			,FromTable.FirName as \"FirName\"
			,FromTable.SecName as \"SecName\"
			,FromTable.vac_Person_sex as \"sex\"
			,to_char(FromTable.vac_Person_BirthDay, 'DD.MM.YYYY') as \"BirthDay\"

			,FromTable.vac_Person_group_risk as \"group_risk\"
			,FromTable.Lpu_id as \"Lpu_id\"
			,FromTable.Lpu_Name as \"Lpu_Name\"
			,FromTable.vac_PersonPlanFinal_Age as \"Age\"
			,FromTable.typeName as \"type_name\"
			,FromTable.VaccineType_Name as \"Name\"
			,FromTable.NationalCalendarVac_SequenceVac as \"SequenceVac\"
			,to_char(FromTable.vac_PersonPlanFinal_dateS, 'DD.MM.YYYY') as \"date_S\"

			,to_char(FromTable.vac_PersonPlanFinal_dateE, 'DD.MM.YYYY') as \"date_E\"

			--,FromTable.vac_PersonPlanFinal_uch [uch]
			,FromTable.VaccineType_id as \"VaccineType_id\"
            ,to_char(FromTable.DateSave, 'DD.MM.YYYY') as \"DateSave\"

            ,FromTable.Server_id as \"Server_id\"
            ,FromTable.PersonEvn_id as \"PersonEvn_id\"
            ,FromTable.Address as \"Address\"
            ,FromTable.Org_id as \"Org_id\"
            ,FromTable.Person_dead as \"Person_dead\"
            ,L2.Lpu_Nick as \"Lpu_Nick\"
            ,PCard.Lpu_atNick as \"Lpu_atNick\"
            --,PCard.LpuRegion_Name as \"uch\" --одинаковые псевдонимы и значения с полем vacJournalAccount_uch вызывает ошибку БД
            -- end select
            FROM 
              -- from
                vac.v_PersonPlanFinal FromTable  

                LEFT JOIN LATERAL(

                	select 
                		PC.PersonCard_id,
                		PC.Person_id,
                		PC.Lpu_id,
                		PC.LpuRegion_id,
                		COALESCE(L.Lpu_Nick,L.Lpu_Name) as Lpu_atNick,

                		COALESCE(LR.LpuRegionType_Name,'') || ' №' || LR.LpuRegion_Name as LpuRegion_Name

                	from v_PersonCard PC 

                	left join v_Lpu L  on L.Lpu_id = PC.Lpu_id

                	left join v_LpuRegion LR  on LR.LpuRegion_id = PC.LpuRegion_id

					{$CardJoin}
                	where PC.Person_id = FromTable.Person_id
                    limit 1
                ) PCard ON true
                left join v_Lpu L2  on L2.Lpu_id = FromTable.Lpu_id

				left join v_PersonState PK  on PK.Person_id = PCard.Person_id

				left outer join dbo.v_Address adr on adr.Address_id = PK.PAddress_id

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

			$count_sql = " {$Declare} SELECT count(1) AS cnt FROM  vac.v_PersonPlanFinalTrunc   " . ImplodeWherePH($filters);

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
			FromTable.JournalVacFixed_id as \"JournalVacFixed_id\"
			,FromTable.vacJournalAccount_Purpose_MedPersonal_id as \"Purpose_MedPersonal_id\"
			,FromTable.Person_id as \"Person_id\"
			,to_char(FromTable.vacJournalAccount_DatePurpose, 'DD.MM.YYYY') as \"Date_Purpose\"

			,FromTable.vacJournalAccount_uch as \"uch\"
			,FromTable.vacJournalAccount_fio as \"fio\"
			,FromTable.SurName as \"SurName\"
			,FromTable.FirName as \"FirName\"
			,FromTable.SecName as \"SecName\"
			,to_char(FromTable.vac_Person_BirthDay, 'DD.MM.YYYY') as \"BirthDay\"

			,FromTable.vac_Person_sex as \"sex\"
			,FromTable.vacJournalAccount_Age as \"age\"
			,FromTable.Lpu_id as \"Lpu_id\"
			,FromTable.Lpu_Name as \"Lpu_Name\"
			,FromTable.Vaccine_name as \"vac_name\"
			,FromTable.Vaccine_id as \"Vaccine_id\"
			,FromTable.vacJournalAccount_Infection as \"NAME_TYPE_VAC\"
			,FromTable.vacJournalAccount_Dose as \"VACCINE_DOZA\"
			,FromTable.vacJournalAccount_WayPlace as \"WAY_PLACE\"
			,to_char(FromTable.vacJournalAccount_VacDateSave, 'DD.MM.YYYY') as \"VacDateSave\"

			,to_char(FromTable.vacJournalAccount_DateSave, 'DD.MM.YYYY') as \"DateSave\"

			,FromTable.Server_id as \"Server_id\"
			,FromTable.PersonEvn_id as \"PersonEvn_id\"
			,FromTable.Org_id as \"Org_id\"
			,FromTable.Person_dead as \"Person_dead\"
			,L2.Lpu_Nick as \"Lpu_Nick\"
            ,PCard.Lpu_atNick as \"Lpu_atNick\"
            --,PCard.LpuRegion_Name as \"uch\" --одинаковые псевдонимы и значения с полем vacJournalAccount_uch вызывает ошибку БД
			-- end select
			FROM
			-- from
			vac.v_JournalVacFixed FromTable
			LEFT JOIN LATERAL(
				select 
					PC.PersonCard_id,
					PC.Person_id,
					PC.Lpu_id,
					PC.LpuRegion_id,
					COALESCE(L.Lpu_Nick,L.Lpu_Name) as Lpu_atNick,
					COALESCE(LR.LpuRegionType_Name,'') || ' №' || LR.LpuRegion_Name as LpuRegion_Name
				from v_PersonCard PC
				left join v_Lpu L  on L.Lpu_id = PC.Lpu_id
				left join v_LpuRegion LR  on LR.LpuRegion_id = PC.LpuRegion_id
				{$CardJoin}
				where PC.Person_id = FromTable.Person_id
                limit 1
			) PCard ON true
			left join v_Lpu L2  on L2.Lpu_id = FromTable.Lpu_id
			left join v_PersonState PK  on PK.Person_id = PCard.Person_id
			left outer join dbo.v_Address adr on adr.Address_id = PK.PAddress_id
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
				FromTable.vacJournalAccount_id as \"vacJournalAccount_id\"
				,to_char(FromTable.vacJournalAccount_DatePurpose, 'DD.MM.YYYY') as \"Date_Purpose\"

				,to_char(FromTable.vacJournalAccount_DateVac, 'DD.MM.YYYY') as \"Date_Vac\"

				--,FromTable.vacJournalAccount_uch uch
				,FromTable.Person_id as \"Person_id\"
				,FromTable.Server_id as \"Server_id\"
				,FromTable.PersonEvn_id as \"PersonEvn_id\"
				,FromTable.SurName as \"SurName\"
				,FromTable.FirName as \"FirName\"
				,FromTable.SecName as \"SecName\"
				,to_char(FromTable.vac_Person_BirthDay, 'DD.MM.YYYY') as \"BirthDay\"

				,FromTable.vacJournalAccount_Age as \"age\"
				,FromTable.Vaccine_name as \"vac_name\"
				,FromTable.Vaccine_id as \"Vaccine_id\"
				,FromTable.vacJournalAccount_Infection as \"NAME_TYPE_VAC\"
				,FromTable.vacJournalAccount_Dose as \"VACCINE_DOZA\"
				--,FromTable.vacJournalAccount_WayPlace \"WAY_PLACE\"
				,case 
					when p.VaccinePlace_Name is null and wp.VaccinePlace_Name is null and  FromTable.VaccinePlace_id is not null then
						wp.VaccineWay_Name
					when p.VaccinePlace_Name is null and FromTable.VaccinePlace_id is not null then 
						wp.VaccinePlace_Name||': '||wp.VaccineWay_Name 
					else p.VaccinePlace_Name||': '||w.VaccineWay_Name 
				end as \"WAY_PLACE\"
				,FromTable.Lpu_id as \"Lpu_id\"
				,FromTable.Lpu_Name as \"Lpu_Name\"
				,FromTable.vac_Person_sex as \"sex\"
				,FromTable.vacJournalAccount_fio as \"fio\"
				,to_char(FromTable.vacJournalAccount_VacDateSave, 'DD.MM.YYYY') as \"VacDateSave\"

				,to_char(FromTable.vacJournalAccount_DateSave, 'DD.MM.YYYY') as \"DateSave\"

				,FromTable.vacJournalAccount_Seria as \"Seria\"
				,FromTable.Org_id as \"Org_id\"
				,FromTable.Person_dead as \"Person_dead\"
				,L2.Lpu_Nick as \"Lpu_Nick\"
				,PCard.Lpu_atNick as \"Lpu_atNick\"
				,PCard.LpuRegion_Name as \"uch\"
				,NR.NotifyReaction_id as \"NotifyReaction_id\"
				,COALESCE(to_char(NR.NotifyReaction_createDate, 'DD.MM.YYYY'), '') as \"NotifyReaction_createDate\"
			-- end select
			FROM
			-- from
			vac.v_JournalAccountAll FromTable  

			LEFT JOIN LATERAL(

				select 
					PC.PersonCard_id,
					PC.Person_id,
					PC.Lpu_id,
					PC.LpuRegion_id,
					COALESCE(L.Lpu_Nick,L.Lpu_Name) as Lpu_atNick,

					COALESCE(LR.LpuRegionType_Name,'') || ' №' || LR.LpuRegion_Name as LpuRegion_Name

				from v_PersonCard PC 

				left join v_Lpu L  on L.Lpu_id = PC.Lpu_id

				left join v_LpuRegion LR  on LR.LpuRegion_id = PC.LpuRegion_id

				{$CardJoin}
				where PC.Person_id = FromTable.Person_id
					and LpuAttachType_id = 1
                limit 1
			) PCard ON true
			left join v_Lpu L2 on L2.Lpu_id = FromTable.Lpu_id
			left join vac.v_NotifyReaction NR ON NR.vacJournalAccount_id = FromTable.vacJournalAccount_id
			LEFT JOIN vac.S_VaccineWay w ON FromTable.VaccineWay_id = w.VaccineWay_id
			LEFT JOIN vac.v_VaccinePlace p ON FromTable.VaccinePlace_id = p.VaccinePlace_id
			LEFT JOIN LATERAL (
				SELECT
					p2.VaccinePlace_Name, w2.VaccineWay_Name
				FROM vac.v_VaccineWayPlace wp2
					LEFT JOIN vac.v_VaccinePlace p2 ON wp2.VaccinePlace_id = p2.VaccinePlace_id
					LEFT JOIN vac.S_VaccineWay w2 ON wp2.VaccineWay_id = w2.VaccineWay_id
				WHERE FromTable.VaccinePlace_id = wp2.VaccineWayPlace_id
				LIMIT 1
			) wp on true

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
				FromTable.PlanTuberkulin_id as \"PlanTuberkulin_id\"
				,to_char(FromTable.PlanTuberkulin_DatePlan, 'DD.MM.YYYY') as \"Date_Plan\"

				,FromTable.SurName as \"SurName\"
				,FromTable.FirName as \"FirName\"
				,FromTable.SecName as \"SecName\"
				,FromTable.vac_Person_sex as \"sex\"
				,to_char(FromTable.vac_Person_BirthDay, 'DD.MM.YYYY') as \"BirthDay\"

				,FromTable.PlanTuberkulin_Age as \"Age\"
				--,FromTable.PlanTuberkulin_uch [uch]
				,FromTable.Address as \"Address\"
				,FromTable.Lpu_id as \"Lpu_id\"
				,FromTable.Lpu_Name as \"Lpu_Name\"
				,FromTable.Person_id as \"Person_id\"
				,FromTable.Server_id as \"Server_id\"
				,FromTable.PersonEvn_id as \"PersonEvn_id\"
				,FromTable.Org_id as \"Org_id\"
				,FromTable.Person_dead as \"Person_dead\"
				,L2.Lpu_Nick as \"Lpu_Nick\"
				,PCard.Lpu_atNick as \"Lpu_atNick\"
				,PCard.LpuRegion_Name as \"uch\"
			-- end select
			FROM
			-- from
			  vac.v_PlanTuberkulin FromTable 

			  LEFT JOIN LATERAL(

				select 
					PC.PersonCard_id,
					PC.Person_id,
					PC.Lpu_id,
					PC.LpuRegion_id,
					COALESCE(L.Lpu_Nick,L.Lpu_Name) as Lpu_atNick,

					COALESCE(LR.LpuRegionType_Name,'') || ' №' || LR.LpuRegion_Name as LpuRegion_Name

				from v_PersonCard PC 

				left join v_Lpu L  on L.Lpu_id = PC.Lpu_id

				left join v_LpuRegion LR  on LR.LpuRegion_id = PC.LpuRegion_id

				{$CardJoin}
				where PC.Person_id = FromTable.Person_id
                limit 1
			) PCard ON true
			left join v_Lpu L2  on L2.Lpu_id = FromTable.Lpu_id

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
				FromTable.JournalMantuFixed_id as \"JournalMantuFixed_id\"
				,to_char(FromTable.JournalMantu_DatePurpose, 'DD.MM.YYYY') as \"Date_Purpose\"

				,FromTable.SurName as \"SurName\"
				,FromTable.FirName as \"FirName\"
				,FromTable.SecName as \"SecName\"
				,FromTable.sex as \"sex\"
				,to_char(FromTable.BirthDay, 'DD.MM.YYYY') as \"BirthDay\"

				,FromTable.JournalMantu_age as \"age\"
				--,FromTable.JournalMantu_uch [uch]
				,FromTable.Lpu_id as \"Lpu_id\"
				,FromTable.Lpu_Name as \"Lpu_Name\"
				,FromTable.Person_id as \"Person_id\"
				,FromTable.Server_id as \"Server_id\"
				,FromTable.PersonEvn_id as \"PersonEvn_id\"
				,FromTable.Org_id as \"Org_id\"
				,FromTable.Person_dead as \"Person_dead\"
				,L2.Lpu_Nick as \"Lpu_Nick\"
				,PCard.Lpu_atNick as \"Lpu_atNick\"
				,PCard.LpuRegion_Name as \"uch\"
			-- end select
			FROM
			-- from
				vac.v_JournalMantuFixed FromTable 

				LEFT JOIN LATERAL(

					select 
						PC.PersonCard_id,
						PC.Person_id,
						PC.Lpu_id,
						PC.LpuRegion_id,
						COALESCE(L.Lpu_Nick,L.Lpu_Name) as Lpu_atNick,

						COALESCE(LR.LpuRegionType_Name,'') || ' №' || LR.LpuRegion_Name as LpuRegion_Name

					from v_PersonCard PC 

					left join v_Lpu L  on L.Lpu_id = PC.Lpu_id

					left join v_LpuRegion LR  on LR.LpuRegion_id = PC.LpuRegion_id

					{$CardJoin}
					where PC.Person_id = FromTable.Person_id
                    limit 1
				) PCard ON true
				left join v_Lpu L2  on L2.Lpu_id = FromTable.Lpu_id

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
			 FromTable.JournalMantu_id as \"JournalMantuFixed_id\"
				,to_char(FromTable.JournalMantu_DatePurpose, 'DD.MM.YYYY') as \"Date_Purpose\"

			  ,to_char(FromTable.JournalMantu_DateVac, 'DD.MM.YYYY') as \"date_Vac\"

			  ,FromTable.SurName as \"SurName\"
			  ,FromTable.FirName as \"FirName\"
			  ,FromTable.SecName as \"SecName\"
			  ,FromTable.vac_Person_sex as \"sex\"
			  ,to_char(FromTable.vac_Person_BirthDay, 'DD.MM.YYYY') as \"BirthDay\"

			  ,FromTable.JournalMantu_age as \"age\"
			  --,FromTable.JournalMantu_uch [uch]
			  ,FromTable.Lpu_id as \"Lpu_id\"
			  ,FromTable.Lpu_Name as \"Lpu_Name\"
			  ,FromTable.Person_id as \"Person_id\"
				,FromTable.Server_id as \"Server_id\"
				,FromTable.PersonEvn_id as \"PersonEvn_id\"
				,FromTable.JournalMantu_ReactDescription as \"ReactDescription\"
				,to_char(FromTable.JournalMantu_DateReact, 'DD.MM.YYYY') as \"DateReact\"

				,FromTable.Org_id as \"Org_id\"
					,FromTable.Person_dead as \"Person_dead\"
					,FromTable.TubDiagnosisType_id as \"TubDiagnosisType_id\"
				,FromTable.TubDiagnosisType_Name as \"TubDiagnosisType_Name\"
			,L2.Lpu_Nick as \"Lpu_Nick\"
			,PCard.Lpu_atNick as \"Lpu_atNick\"
			,PCard.LpuRegion_Name as \"uch\"
			-- end select
			FROM
			-- from
			  vac.v_JournalMantuAccount FromTable 

			  LEFT JOIN LATERAL(

				select 
					PC.PersonCard_id,
					PC.Person_id,
					PC.Lpu_id,
					PC.LpuRegion_id,
					COALESCE(L.Lpu_Nick,L.Lpu_Name) as Lpu_atNick,

					COALESCE(LR.LpuRegionType_Name,'') || ' №' || LR.LpuRegion_Name as LpuRegion_Name

				from v_PersonCard PC 

				left join v_Lpu L  on L.Lpu_id = PC.Lpu_id

				left join v_LpuRegion LR  on LR.LpuRegion_id = PC.LpuRegion_id

				{$CardJoin}
				where PC.Person_id = FromTable.Person_id
                limit 1
			) PCard ON true
			left join v_Lpu L2  on L2.Lpu_id = FromTable.Lpu_id

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
			  FromTable.vacJournalMedTapRefusal_id as \"vacJournalMedTapRefusal_id\"
				,FromTable.Person_id as \"Person_id\"
			  --,FromTable.vacJournalMedTapRefusal_uch [uch]
			  ,FromTable.SurName as \"SurName\"
			  ,FromTable.FirName as \"FirName\"
			  ,FromTable.SecName as \"SecName\"
			  ,FromTable.sex as \"sex\"
			  ,to_char(FromTable.BirthDay, 'DD.MM.YYYY') as \"BirthDay\"

			  ,to_char(FromTable.vacJournalMedTapRefusal_DateBegin, 'DD.MM.YYYY') as \"DateBegin\"

			  ,to_char(FromTable.vacJournalMedTapRefusal_DateEnd, 'DD.MM.YYYY') as \"DateEnd\"

			  ,FromTable.vacJournalMedTapRefusal_Reason as \"Reason\"
			  ,FromTable.vacJournalMedTapRefusal_NameTypeRec as \"type_rec\"
			  ,FromTable.Lpu_id as \"Lpu_id\"
			  ,FromTable.Lpu_Name as \"Lpu_Name\"
			  ,tp.VaccineType_Name as \"VaccineType_Name\"
			  ,CASE
				WHEN FromTable.VaccineType_id = 1000 OR FromTable.vacJournalMedTapRefusal_VaccineTypeAll = 1
				 THEN 'Все прививки'
				ELSE tp.VaccineType_Name
			   END as \"VaccineType_Name\"
				,FromTable.Server_id as \"Server_id\"
				,FromTable.PersonEvn_id as \"PersonEvn_id\"
				,to_char(FromTable.vacJournalMedTapRefusal__insDT, 'DD.MM.YYYY') as \"DateRefusalSave\"

				,FromTable.Org_id as \"Org_id\"
					,FromTable.Person_dead as \"Person_dead\"
					,L2.Lpu_Nick as \"Lpu_Nick\"
				,PCard.Lpu_atNick as \"Lpu_atNick\"
				,PCard.LpuRegion_Name as \"uch\"
			-- end select
			FROM
			-- from
			  vac.v_JournalMedTapRefusal FromTable  

			  LEFT JOIN vac.S_VaccineType tp   ON FromTable.VaccineType_id = tp.VaccineType_id

			  LEFT JOIN LATERAL(

					select
						PC.PersonCard_id,
						PC.Person_id,
						PC.Lpu_id,
						PC.LpuRegion_id,
						COALESCE(L.Lpu_Nick,L.Lpu_Name) as Lpu_atNick,

						COALESCE(LR.LpuRegionType_Name,'') || ' №' || LR.LpuRegion_Name as LpuRegion_Name

					from v_PersonCard PC 

					left join v_Lpu L  on L.Lpu_id = PC.Lpu_id

					left join v_LpuRegion LR  on LR.LpuRegion_id = PC.LpuRegion_id

					{$CardJoin}
					where PC.Person_id = FromTable.Person_id
                    limit 1
				) PCard ON true
				left join v_Lpu L2  on L2.Lpu_id = FromTable.Lpu_id

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
			$filters[] = "PK." . $this->getFieldName($type, 'SurName') . " iLIKE :SurName";

			$queryParams['SurName'] = $data['SurName'] . '%';
		}
		If (ArrayVal($data, 'FirName') != '') {
			$filters[] = "PK." . $this->getFieldName($type, 'FirName') . " iLIKE :FirName";

			$queryParams['FirName'] = $data['FirName'] . '%';
		}
		If (ArrayVal($data, 'SecName') != '') {
			$filters[] = "PK." . $this->getFieldName($type, 'SecName') . " iLIKE :SecName";

			$queryParams['SecName'] = $data['SecName'] . '%';
		}
		If (ArrayVal($data, 'age') != '') {
			$filters[] = "PK." . $this->getFieldName($type, 'age') . " iLIKE :age";

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
			$filters[] = "PK.VacDateSave < CAST(:Date_Change1 as date) + interval '1 day'";
			$queryParams['Date_Change1'] = $data['Date_Change'][1];
		}
		if (isset($data['Date_Change'][0])) {
			$filters[] = "PK.VacDateSave >= :Date_Change0";
			$queryParams['Date_Change0'] = $data['Date_Change'][0];
		}

		if (ArrayVal($data, 'ImplVacOnly') == 'on') {
			$filters[] = "PK.VacDateSave IS NOT NULL";
		}
		
		//  Возраст с
		if (ArrayVal($data, 'PersonAge_AgeFrom') != '') {
			$filters[] = "PK.BirthDay <= getDate() - interval '1  years' * :PersonAge_AgeFrom::integer";
			$queryParams['PersonAge_AgeFrom'] = $data['PersonAge_AgeFrom'];
		}
		//  Возраст по
		if (ArrayVal($data, 'PersonAge_AgeTo') != '') {
			$filters[] = "PK.BirthDay > getDate() - interval '1 years' * :PersonAge_AgeTo::integer";
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
				inner join v_PersonState PS on PS.Person_id = PK.Person_id
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
					inner join v_PersonState_all PS on PS.Person_id = PK.Person_id
					inner join v_Address_all A on PS.PAddress_id = A.Address_id
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
			//      $filters[] = "SurName iLIKE :SurName";

			$filters[] = "FromTable.".$this->getFieldName($type, 'SurName') . " iLIKE :SurName";

			$queryParams['SurName'] = $data['SurName'] . '%';
		}
		If (ArrayVal($data, 'FirName') != '') {
			//      $filters[] = "FirName iLIKE :FirName";

			$filters[] = "FromTable.".$this->getFieldName($type, 'FirName') . " iLIKE :FirName";

			$queryParams['FirName'] = $data['FirName'] . '%';
		}
		If (ArrayVal($data, 'SecName') != '') {
			//      $filters[] = "SecName iLIKE :SecName";

			$filters[] = "FromTable.".$this->getFieldName($type, 'SecName') . " iLIKE :SecName";

			$queryParams['SecName'] = $data['SecName'] . '%';
		}
		If (ArrayVal($data, 'age') != '') {
			//      $filters[] = "vacJournalAccount_Age iLIKE :age";

			$filters[] = "FromTable.".$this->getFieldName($type, 'age') . " iLIKE :age";

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
			$filters[] = "{$fldBirthDay} <= getDate() - interval '1 year' * :PersonAge_AgeFrom::integer";
			$queryParams['PersonAge_AgeFrom'] = $data['PersonAge_AgeFrom'];
		}
		//  Возраст по
		if (ArrayVal($data, 'PersonAge_AgeTo') != '') {
			$filters[] = "{$fldBirthDay} > getDate() - interval '1 year' * :PersonAge_AgeTo::integer";
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
			$CardJoin = "inner join v_PersonState PK  on PK.Person_id = PC.Person_id

						inner join dbo.v_Address adr on adr.Address_id = PK.PAddress_id

							";
			//echo 'test... '. $CardJoin;
			if (isset($data ['PersonNoAddress']) && $data ['PersonNoAddress'] == 1){
					$CardJoin = "
						inner join v_PersonState PK  on PK.Person_id = PC.Person_id

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
					$filters[] = "VacDateSave < :Date_Change1 + interval '1 day'";
					$queryParams['Date_Change1'] = $data['Date_Change'][1];
				}
				if (isset($data['Date_Change'][0])) {
					$filters[] = "VacDateSave >= :Date_Change0";
					$queryParams['Date_Change0'] = $data['Date_Change'][0];
				}
				//				if (ArrayVal($data, 'checkbox_ImplVacOnly') != '')
				if (ArrayVal($data, 'ImplVacOnly') == 'on') {
					$filters[] = "VacDateSave IS NOT NULL";
				}
				break;
			case TYPE_JOURNAL::VAC_PLAN:
				if (isset($data['Date_Plan'][1])) {
					//$filters[] = "[vac_PersonPlanFinal_DatePlan] <= :Date_Plan1";
					$filters[] = "FromTable.vac_PersonPlanFinal_DatePlan <= :Date_Plan1";
					$queryParams['Date_Plan1'] = $data['Date_Plan'][1];
				}
				if (isset($data['Date_Plan'][0])) {
					//$filters[] = "[vac_PersonPlanFinal_DatePlan] >= :Date_Plan0";
					$filters[] = "FromTable.vac_PersonPlanFinal_DatePlan >= :Date_Plan0";
					$queryParams['Date_Plan0'] = $data['Date_Plan'][0];
				}
				If (ArrayVal($data, 'Name') != '') {
					$filters[] = "FromTable.VaccineType_Name iLIKE :Name";

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
					//$filters[] = "vac_name iLIKE :vac_name";

					$queryParams['vac_name'] = '%' . $data['vac_name'] . '%';
					$filters[] = "FromTable.Vaccine_name iLIKE :vac_name";

					//$queryParams['vac_name'] = '%' . $data['vac_name'] . '%';
				}
				If (ArrayVal($data, 'NAME_TYPE_VAC') != '') {
					//$filters[] = "NAME_TYPE_VAC iLIKE :NAME_TYPE_VAC";

					$queryParams['NAME_TYPE_VAC'] = '%' . $data['NAME_TYPE_VAC'] . '%';

					$filters[] = "FromTable.vacJournalAccount_Infection iLIKE :NAME_TYPE_VAC";

					//$queryParams['NAME_TYPE_VAC'] = '%' . $data['NAME_TYPE_VAC'] . '%';
				}
				If (ArrayVal($data, 'vacJournalAccount_id') != '') {
					$filters[] = "FromTable.vacJournalAccount_id = :vacJournalAccount_id";
					$queryParams['vacJournalAccount_id'] = $data['vacJournalAccount_id'];
				}
				If (ArrayVal($data, 'VaccineType_id') != '') {//Тип прививки
					$join = "
						inner join (Select distinct vacJournalAccount_id from vac.Inoculation   

							where VaccineType_id = :VaccineType_id
						) t on t.vacJournalAccount_id = FromTable.JournalVacFixed_id
					";
					$queryParams['VaccineType_id'] = $data['VaccineType_id'];
				}
				break;
			case TYPE_JOURNAL::VAC_REGISTR:
				if (isset($data['Date_Vac'][1])) {
					$filters[] = "FromTable.vacJournalAccount_DateVac <= :Date_Vac1";
					$queryParams['Date_Vac1'] = $data['Date_Vac'][1];
				}
				if (isset($data['Date_Vac'][0])) {
					$filters[] = "FromTable.vacJournalAccount_DateVac >= :Date_Vac0";
					$queryParams['Date_Vac0'] = $data['Date_Vac'][0];
				}
				If (ArrayVal($data, 'vac_name') != '') {
					$filters[] = "FromTable.Vaccine_name iLIKE :vac_name";

					$queryParams['vac_name'] = '%' . $data['vac_name'] . '%';
				}
				If (ArrayVal($data, 'NAME_TYPE_VAC') != '') {
					$filters[] = "FromTable.vacJournalAccount_Infection iLIKE :NAME_TYPE_VAC";

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
							SELECT DISTINCT vacJournalAccount_id FROM vac.Inoculation  

							WHERE VaccineType_id = :VaccineType_id
						) t ON t.vacJournalAccount_id = FromTable.vacJournalAccount_id
					";
					$queryParams['VaccineType_id'] = $data['VaccineType_id'];
				}
				break;
			case TYPE_JOURNAL::TUB_PLAN:
				if (isset($data['Date_Plan'][1])) {
					$filters[] = "FromTable.PlanTuberkulin_DatePlan <= :Date_Plan1";
					$queryParams['Date_Plan1'] = $data['Date_Plan'][1];
				}
				if (isset($data['Date_Plan'][0])) {
					$filters[] = "FromTable.PlanTuberkulin_DatePlan >= :Date_Plan0";
					$queryParams['Date_Plan0'] = $data['Date_Plan'][0];
				}
				//        //вытаскиваем запланированные:
				//        $filters[] = "[Fixed] IS NOT NULL";
				break;
			case TYPE_JOURNAL::TUB_ASSIGNED:
				if (isset($data['Date_Purpose'][1])) {
					$filters[] = "FromTable.JournalMantu_DatePurpose <= :Date_Purpose1";
					$queryParams['Date_Purpose1'] = $data['Date_Purpose'][1];
				}
				if (isset($data['Date_Purpose'][0])) {
					$filters[] = "FromTable.JournalMantu_DatePurpose >= :Date_Purpose0";
					$queryParams['Date_Purpose0'] = $data['Date_Purpose'][0];
				}
				break;

			case TYPE_JOURNAL::VAC_REFUSE:
				break;
			case TYPE_JOURNAL::VAC_4CabVac:
				//const VAC_4CabVac = 'Vac4CabVac';

				log_message('debug', 'Search_BirthDay=' . $data['Search_BirthDay']);
				If (ArrayVal($data, 'Search_SurName') != '') {
					$filters[] = "FromTable.".$this->getFieldName($type, 'SurName') . " iLIKE :SurName";

					$queryParams['SurName'] = $data['Search_SurName'] . '%';
				}

				If (ArrayVal($data, 'Search_FirName') != '') {
					$filters[] = "FromTable.".$this->getFieldName($type, 'FirName') . " iLIKE :FirName";

					$queryParams['FirName'] = $data['Search_FirName'] . '%';
				}
				If (ArrayVal($data, 'Search_SecName') != '') {
					$filters[] = "FromTable.".$this->getFieldName($type, 'SecName') . " iLIKE :SecName";

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
     * @throws Exception
     */
	public function GetVaccineWay($data)
	{
		$query = "
			SELECT
			 --wp.VaccineWayPlace_id,
			 COALESCE(wp.VaccineWayPlace_id::varchar, pl.VaccinePlace_id::varchar) || coalesce(pl.VaccinePlace_id::varchar, pl_tmp.VaccinePlace_id::varchar, '') AS \"id_VaccineWayPlace_VaccinePlace\",
			 COALESCE(wp.VaccineWayPlace_id, pl.VaccinePlace_id) as \"VaccineWayPlace_id\",

			 case when  pl.VaccinePlace_id is not null then wp.VaccineWay_id else null end as \"VaccineWay_id\",
			 COALESCE(pl.VaccinePlace_id, pl_tmp.VaccinePlace_id) as \"VaccinePlace_id\"-- Исправили справочник и вынуждены подставить VaccineWayPlace_id

			  ,
			  case
				when pl.VaccinePlace_id is not null then
					 pl.VaccinePlace_Name||': ' || w.VaccineWay_Name
				else
					 pl_tmp.VaccinePlace_Name||': ' || w.VaccineWay_Name
			  end  as \"VaccineWayPlace_Name\"

			FROM vac.v_VaccineWayPlace wp  

			LEFT OUTER JOIN vac.S_VaccineWay w  ON w.VaccineWay_id = wp.VaccineWay_id

			--  Это по новому
			LEFT OUTER JOIN vac.v_VaccinePlace pl  ON pl.VaccineWay_id = wp.VaccineWay_id

				  --  and pl.VaccineWay_id = COALESCE(wp.VaccinePlace_id, pl.VaccineWay_id)

					 and pl.VaccinePlace_id = COALESCE(wp.VaccinePlace_id, pl.VaccinePlace_id)

					 and   pl.vaccinePlace_Status = 1
			 --  Это по старому
			 LEFT OUTER JOIN vac.v_VaccinePlace pl_tmp  ON pl_tmp.VaccinePlace_id = wp.VaccinePlace_id
			  WHERE
				wp.vaccine_id = :vaccineId
				AND VaccineWayPlace_AgeS <= DATEDIFF('m', cast('1900-01-01' as timestamp), cast('1900-01-01' as timestamp) + ((:datePurp::timestamp) - (:birthday::timestamp) )::interval)
				AND VaccineWayPlace_AgeE >= DATEDIFF('m', cast('1900-01-01' as timestamp), cast('1900-01-01' as timestamp) + ((:datePurp::timestamp) - (:birthday::timestamp) )::interval)
			--        AND vaccinePlace_Status = 1
			order by pl.VaccinePlaceName_Name, pl.VaccinePlace_PlaseSide,  w.VaccineWay_Name
        ";

		$queryParams = [];
		$queryParams['vaccineId'] = $this->nvl($data['vaccine_id']);
		$queryParams['birthday'] = $this->nvl($data['birthday']);
		$queryParams['datePurp'] = $this->nvl($data['date_purpose']);

		if(!$queryParams['datePurp']) {
		    throw new Exception('Пожалуйста введит Дата исполнения');
        }
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
			SELECT
					VaccineDose_id as \"VaccineDose_id\", 
                    VaccineDose_Name as \"VaccineDose_Name\"
			FROM vac.v_VaccineDose  

			WHERE Vaccine_id = :vaccineId
			AND VaccineDose_AgeS <= date_part('year',:datePurp::timestamp)::integer - date_part('year',:birthday::timestamp)::integer
			AND VaccineDose_AgeE >= date_part('year',:datePurp::timestamp)::integer - date_part('year',:birthday::timestamp)::integer
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
		DoseType_id as \"DoseType_id\"
		,DoseType_Name as \"DoseType_Name\"
		FROM vac.S_DoseType  

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
		VaccineWay_id as \"VaccineWay_id\"
		,VaccineWay_Name as \"VaccineWay_Name\"
		FROM vac.S_VaccineWay  

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
			  VaccinePlaceHome_id as \"VaccinePlaceHome_id\"
			  ,VaccinePlaceName_Name as \"VaccinePlace_Name\"
					,VaccineWay_id as \"VaccineWay_id\"
			FROM vac.v_VaccinePlaceHome  

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
                    SELECT VaccineWay_id as \"VaccineWay_id\"
                      ,VaccineWay_Name as \"Name\"
                    FROM vac.S_VaccineWay  

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
        select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
        from vac.p_vac_JournalAccount_ins(
            KeyList					:= :KeyList,           -- Строка идентификаторов записей плана через запятую
            Date_Vac				:= :Date_Purpose,      -- Дата назначения
            lpu_id					:= :lpuId,             --  ЛПУ  
            Vaccine_id				:= :Vaccine_id,        -- Идентификатор вакцины  
            Seria					:= :Seria,  -- Серия вакцины
            Period					:= :Period,	-- Срок годности вакцины
            Doza					:= :Doza,              -- Доза препарата
            VaccineWayPlace_id		:= :VaccineWayPlace_id,  --Идентификатор способа и места введения
            Name_Vac				:= :Name_Vac,          -- Наименование вакцины, если заполнение ведется не через Справочник
            MedService_id			:= :MedService_id,  --  ID службы, куда направлен пациент для вакцинации
            MedPersonal_id			:= :Purpose_MedPersonal_id, -- ID врача, назначившего вакцину
            PmUser_id				:= :Purpose_User_id,   -- ID пользователя, который назначил прививку
			Parent                	:= :Parent --объект, на основании которого необходимо создать запись; 0 - v_vacPersonPlan0, 1 -  v_PersonPlanFinal
			EvnVizitPL_id			:= :EvnVizitPL_id			
        );";

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
		$queryParams = [];
		$query = "
			select
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from vac.p_vac_JournalMantu_ins (
                Person_id              := :Person_id, -- Идентификатор пациента
                Lpu_id                 := :Lpu_id,  -- Идентификатор ЛПУ
                PlanTuberkulin_id      := :PlanTuberkulin_id, -- идентификатор плана
                Date_Purpose           := :Date_Purpose, -- Дата назначения/исполнения (зависит от StatusType_id)
                DateReact              := :DateReact,  --  Дата описания реакции
                VacPresence_id         := :VacPresence_id, -- Идентификатор таблицы VacPresence
                Doza                   := :Doza, -- Доза препарата
                VaccineWayPlace_id     := :VaccineWayPlace_id, -- Идентификатор способа и места введения
                MedService_id          := :MedService_id,  --  ID службы, куда направлен пациент для вакцинации
                StatusType_id          := :StatusType_id, -- статус записи: 0 := назначена, 1 - исполнена
                Purpose_MedPersonal_id := :Purpose_MedPersonal_id, -- ID врача, назначившего вакцину
                Purpose_User_id        := :Purpose_User_id, -- ID пользователя, который назначил прививку
                MantuReactionType_id   := :MantuReactionType_id,  --  Идентификатор типа реакции
                ReactionSize           := :ReactionSize,  -- Реакция Манту, [мм]
                Reaction30min          := :Reaction30min::varchar,  -- Реакция на прививку (ч/з 30 мин)
                DiagnosisType          := :DiagnosisType,  -- Метод диагностики
                DiaskinTypeReaction    := :DiaskinTypeReaction,  -- Степень выраженности
                ReactDescription       := :JournalMantu_ReactDescription  -- Описание реакции
            );
        ";
		
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
		$queryParams['Lpu_id'] = $this->nvl($data['lpu_id']);
		$queryParams['DiagnosisType'] = $this->nvl($data['diagnosis_type']);
		$queryParams['DiaskinTypeReaction'] = $this->nvl($data['diaskin_type_reaction']);
		$queryParams['JournalMantu_ReactDescription'] = $this->nvl($data['JournalMantu_ReactDescription']);


		log_message('debug', 'Lpu_id=' . $queryParams['Lpu_id']);
		log_message('debug', 'Date_Purpose=' . $queryParams['Date_Purpose']);

		$result = $this->db->query($query, $queryParams);

		if (!is_object($result)) {
			throw new Exception('Ошибка при выполнении запроса к базе данных (назначение прививки)');
		}

        return $result->result('array');
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
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from vac.p_vac_JournalMantu_upd(
				JournalMantu_id        := :JournalMantuId,  -- Идентификатор  записи
				Person_id              := :Person_id,  -- Идентификатор пациента
				Lpu_id                 := :Lpu_id,  -- Идентификатор ЛПУ
				Pmuser_id              := :Pmuser_id, -- ID пользователя
				Date_Vac               := :DateVac, -- Дата вакцинации
				Vac_MedPersonal_id     := :Vac_MedPersonal_id, --  ID врача, исполнившего вакцинацию
				VacPresence_id         := :VacPresence_id,  --  Идентификатор таблицы VacPresence
				Mantu_Seria        := :Mantu_Seria,
				Mantu_Period       := :Mantu_Period,
				Doza                   := :Doza, -- Доза препарата
				VaccineWayPlace_id     := :VaccineWayPlace_id,  --Идентификатор способа и места введения
				VaccinePlace_id	   := :VaccinePlace_id,		--Идентификатор места введения
				StatusType_id          := :StatusType_id,  --  статус записи 0 := назначена, 1 - исполнена
				MantuReactionType_id   := :MantuReactionType_id,  --  Идентификатор типа реакции
				ReactionSize    := :ReactionSize,  -- Реакция Манту, [мм]
				Reaction30min   := :Reaction30min::varchar,  -- Реакция на прививку (ч/з 30 мин)
				DiagnosisType   := :DiagnosisType,  -- Метод диагностики
				DiaskinTypeReaction   := :DiaskinTypeReaction,  -- Степень выраженности
				ReactDescription	   := :JournalMantu_ReactDescription,  -- Описание реакции
				DateReact              := :DateReact  --  Дата описания реакции
			);
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
                select
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
                from vac.p_vac_JournalAccount_del
                (
                    vacJournalAccount_id := :vacJournalAccountOld_id, -- идентификатор записи
                    PmUser_id            := :Vac_User_id -- ID пользователя, который удалил прививку
                );
            ";
		};

		$query = $queryDelete . "
            Select
                vacJournalAccount_id as \"vacJournalAccount_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from vac.p_vac_JournalAccount_ins
			(
				KeyList                            := :KeyList,           -- Строка идентификаторов записей плана через запятую
				KeyListPlan                        := :KeyListPlan,           -- Строка идентификаторов записей плана через запятую (для функции изменения вакцин)
				Date_Vac                           := :dateVac,      -- Дата назначения / исполнения
				MedService_id                      := :medservice_id,  -- Идентификатор службы
				MedPersonal_id                     := :medStaffImplId,  -- ID врача, назначившего/исполнившего прививку
				LPU_ID                             := :lpuId,           --    Идентификатор ЛПУ
				StatusType_id                      := :statustype_id,
				Vaccine_id                         := :Vaccine_id,        -- Идентификатор вакцины
				Seria                              := :Seria,  -- Серия вакцины
				Period                             := :Period,	-- Срок годности вакцины
				Doza                               := :Doza,              -- Доза препарата
				VaccineWayPlace_id                 := :VaccineWayPlace_id,  --Идентификатор способа и места введения
				Name_Vac                           := :Name_Vac,          -- Наименование вакцины, если заполнение ведется не через Справочник
				PmUser_id                          := :Vac_User_id, -- ID пользователя, который назначил/исполнил прививку
				Parent                             := :Parent, --объект, на основании которого необходимо создать запись; 0 - v_vacPersonPlan0, 1 -  v_PersonPlanFinal
				vacJournalAccount_vacOther         := :vacOther, -- Признак прочей прививки (1-'прочие прививки')
				Person_id                          := :person_id, --  идентификатор пациента
				vacJournalAccount_id               := :vacJournalAccount_id -- id назначенной прививки
            );
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
     * @param array $data
     * @return array|bool
     */
	public function loadSimilarRecords($data)
	{
		$queryParams = [];
		$queryUnion = "";

		if (isset($data['plan_id'])) {
			if ($data['plan_id'] == -2) {
				$queryUnion = "
                    where not exists (SElect   
	                        from vac.v_Inoculation_All i , vac.S_VaccineRelType rel 

                        where vacJournalAccount_id = :vacJournalAccount_id 
                            and rel.Vaccine_id = :Vaccine_id
                            and i.VaccineType_id = rel.VaccineType_id
                            and i.VaccineType_id = t.VaccineType_id)                    
                     union 
                        SElect 'Нет' as selRow, Person_id::text || '_' || NationalCalendarVac_Scheme_id PlanView_id, vacJournalAccount_id PlanFinal_id,to_char(vacJournalAccount_DatePurpose, 'DD.MM.YYYY') Date_Plan, VaccineType_Name as Name, typeName type_name

                        from vac.v_Inoculation_All i , vac.S_VaccineRelType rel 

                        where vacJournalAccount_id = :vacJournalAccount_id 
                            and rel.Vaccine_id = :Vaccine_id
                            and i.VaccineType_id = rel.VaccineType_id";
			};
		};

        $query = "
			with PPF as (
                /* 
                    по логике запроса  надо найти минимальное значение  Min(NationalCalendarVac_Scheme_id)
                    но поскольку это строковые значения, то при их сравнении получается что например 4.21.1 < 4.9.1
                    потому создаю такую таблицу с двумя числами по NationalCalendarVac_Scheme_id
                    по этим числам сгруппируем результат и получим нужное значение
                */
                Select VaccineType_id,
                       NationalCalendarVac_Scheme_id,
                       SUBSTRING(NationalCalendarVac_Scheme_id, 1, strpos('.', NationalCalendarVac_Scheme_id)) as Section,
                       SUBSTRING(NationalCalendarVac_Scheme_id, strpos('.', NationalCalendarVac_Scheme_id) + 1, 10) as TownShip,
                       pl.vac_PersonPlanFinal_DatePlan
                from vac.v_PersonPlanFinal pl
                where pl.Person_id = :Person_id
                )
           
            Select 'Нет' as \"selRow\",
                   PlanView_id as \"PlanView_id\",
                   PlanFinal_id as \"PlanFinal_id\",
                   to_char(Date_Plan, 'dd.mm.yyyy') as \"Date_Plan\",
                   Name as \"Name\",
                   type_name as \"type_name\"
            from (
                   Select t.MinDate as MinDate,
                          pl.PersonPlan_id as PlanView_id,
                          null as PlanFinal_id,
                          pl.vacPersonPlan0_DateS as Date_Plan,
                          pl.NationalCalendarVac_vaccineTypeName as Name,
                          pl.NationalCalendarVac_typeName as type_name,
                          pl.NationalCalendarVac_SequenceVac as SequenceVac,
                          t.VaccineType_id as VaccineType_id
                   from vac.v_vacPersonPlan0 pl,
                        vac.S_VaccineRelType rel,
                        (
                          Select VaccineType_id,
                                 Scheme_id,
                                 Dgt.Date_plan as MinDate
                          from vac.v_vacPersonPlan0 pl0
                               left join lateral
                               (
                                 Select cast (Person_id as varchar) || '_' || d.Scheme_id id,
                                        d.Scheme_id,
                                        d.Date_plan
                                 from vac.fn_getPersonVacDigest(:Person_id, null) d
                                 where StatusType_id = - 1 and
                                       Scheme_num = coalesce(:Scheme_num, Scheme_num) and
                                       VaccineType_id in (
                                                           select VaccineType_id
                                                           from vac.S_VaccineRelType
                                                           where Vaccine_id = :Vaccine_id
                                       )
                               ) Dgt on true
                          where pl0.Person_id = :Person_id and
                                pl0.PersonPlan_id = Dgt.id
                        ) t
                   where pl.Person_id = :Person_id and
                         1 = - 1 * :statusType_id and
                         rel.Vaccine_id = :Vaccine_id and
                         pl.VaccineType_id = rel.VaccineType_id and
                         t.VaccineType_id = rel.VaccineType_id and
                         pl.NationalCalendarVac_Scheme_id = t.Scheme_id and
                         (pl.NationalCalendarVac_SignPurpose ilike '%0' or
                         pl.NationalCalendarVac_SignPurpose ilike '%' || pl.group_risk4Query)
                   Union
                   Select t.MinDate as MinDate,
                          null as PlanView_id,
                          pl.vac_PersonPlanFinal_id as PlanFinal_id,
                          pl.vac_PersonPlanFinal_DatePlan as Date_Plan,
                          pl.VaccineType_Name as Name,
                          pl.typeName as type_name,
                          pl.NationalCalendarVac_SequenceVac as SequenceVac,
                          t.VaccineType_id
                   from vac.v_PersonPlanFinal pl,
                        vac.S_VaccineRelType rel
                        inner join lateral
                        (
                          SELECT VaccineType_id as VaccineType_id,
                                 NationalCalendarVac_Scheme_id AS Scheme_id,
                                 vac_PersonPlanFinal_DatePlan AS MinDate
                          FROM PPF
                          WHERE PPF.VaccineType_id = rel.VaccineType_id
                          ORDER BY VaccineType_id,
                                   Section,
                                   PPF.TownShip
                          limit 1
                        ) t on true
                   where pl.Person_id = :Person_id and
                         1 = case
                               when :statusType_id = - 1 
                               then 0
                               else 1
                             end and
                         rel.Vaccine_id = :Vaccine_id and
                         pl.VaccineType_id = rel.VaccineType_id and
                         pl.NationalCalendarVac_Scheme_id = t.Scheme_id
                 ) t
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
        SELECT 
               Vaccine_id as \"Vaccine_id\", 
               VaccineType_id as \"VaccineType_id\", 
               to_char(GETDATE (), 'DD.MM.YYYY') as \"Date_Plan\", 
               VaccineType_Name as \"VaccineType_Name\", 
               Type_Name as \"Type_Name\"

		FROM vac.v_VaccineRelType 
        where Vaccine_id = :Vaccine_id";

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
			SELECT 
            	v.Vaccine_id as \"Vaccine_id\", 
                v.Vaccine_FullName as \"GRID_NAME_VAC\"
			FROM 
            	vac.v_Vaccine v  , 
            	vac.S_VaccineRelType rel  

			WHERE ($filter)
			--rel.VaccineType_id = @VaccineType_id AND
			and v.Vaccine_id = rel.Vaccine_id
			AND (v.Vaccine_AgeBegin IS NULL OR
			(v.Vaccine_AgeBegin <= vac.GetAge(:BirthDay, :Date_Plan) AND Vaccine_AgeEnd >=vac.GetAge(:BirthDay, :Date_Plan)))
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
/*
			$query4del = "
                  exec vac.p_vac_JournalAccount_del
				@vacJournalAccount_id = :vacJournalAccountOld_id, -- идентификатор записи для удаления
				@PmUser_id            = :PmUser_id, -- ID пользователя, который удалил прививку
				@Error_Code           = @ErrCode output,    -- Код ошибки
				@Error_Message        = @ErrMessage output -- Текст ошибки
            
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg
            ";
*/
		}

		$query = "
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from vac.p_vac_JournalAccount_upd(
						vacJournalAccount_id		:= :vac_jaccount_id,  -- идентификатор записи
						dateVac					:= :date_vac, -- Дата исполнения
						LPU_ID                     := :lpuId,           --    Идентификатор ЛПУ
						MedService_id              := :medservice_id,   --  ID службы вакцинации
						Seria 						:= :vac_seria, -- Серия вакцины
						Period						:= :vac_period,	-- Срок годности вакцины
						VaccineWayPlace_id			:= :VaccineWayPlace_id,  --Идентификатор способа и места введения
						MedPersonal_id 			:= :med_staff_impl_id, --  ID врача, исполнившего вакцину
						PmUser_id					:= :Vac_User_id, -- ID пользователя, который внес вседения об исполнении прививки
						reactLocalDesc				:= :reactLocalDesc, -- местная реакция описание
						reactGeneralDesc			:= :reactGeneralDesc -- общая реакция описание
                        );
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
		  select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
		  from vac.p_vac_JournalMedTapRefusal_ins(
				Person_id              := :Person_id,
				VaccineType_id         := :VaccineType_id,
				TypeRecord             := :TypeRecord,
				DateBegin              := :DateBegin,
				DateEnd                := :DateEnd,
				Reason                 := :Reason,
				pmUser_id              := :pmUser_id,
				MedPersonal_id         := :MedPersonal_id,
				VaccineTypeAll         := :VaccineTypeAll,
							refuse_id              := :refuse_id);

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
                select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
                    from vac.p_Vaccine_ins(
                                VaccineName  := :VaccineName,
                                TypeInfections := :TypeInfections,
				VaccineNick := :VaccineNick,
				VaccineAgeBegin := :VaccineAgeBegin,
				VaccineAgeEnd := :VaccineAgeEnd,
				VaccineSignComb := :VaccineSignComb,
                                VaccineId := :VaccineId,
				DozaAge := :DozaAge,
				Doza1 := :DozaVal1,
				Doza2 := :DozaVal2,				
                                DozeType1:= :DozeType1,
				DozeType2 := :DozeType2,
                                WayAge := :WayAge,
				placeType1 := :placeType1,
				placeType2 := :placeType2,
				wayType1 := :wayType1,
				wayType2 := :wayType2);
                                
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
                select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
                    from vac.p_S_OtherVacScheme_ins(
                                Vaccine_id  := :Vaccine_id,
                                AgeTypeS1 := :AgeTypeS1,
				AgeS1 := :AgeS1,
				AgeTypeS2 := :AgeTypeS2,
				AgeS2 := :AgeS2,
				AgeE1 := :AgeE1,
                                AgeE2 := :AgeE2,
				Multiplicity1 := :Multiplicity1,
				Multiplicity2 := :Multiplicity2,
				MultiplicityRisk1 := :MultiplicityRisk1,				
                                MultiplicityRisk2 := :MultiplicityRisk2,
				Interval1 := :Interval1,
                                Interval2 := :Interval2,
				IntervalRisk1 := :IntervalRisk1,
				IntervalRisk2 := :IntervalRisk2,
				PmUser_id := :PmUser_id);
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
/*
 CREATE OR REPLACE FUNCTION vac.p_s_vaccine_upins (
  in_vaccinename varchar,
  in_vaccinenick varchar,
  in_vaccineagebegin integer,
  in_vaccineageend integer,
  in_vaccinesigncomb integer,
  in_dozaage integer,
  in_dozaval1 numeric,
  in_dozaval2 numeric,
  in_dozetype1 integer,
  in_dozetype2 integer,
  in_wayage integer,
  in_placetype1 integer,
  in_placetype2 integer,
  in_waytype1 integer,
  in_waytype2 integer,
  in_typeinfections bigint,
  inout vaccine_id bigint = NULL::bigint,
  inout error_code bigint = NULL::bigint,
  inout error_message text = NULL::text
)
RETURNS record AS
$body$
DECLARE
BEGIN

  IF p_s_vaccine_upins.vaccine_id IS NOT NULL
  THEN
      DELETE FROM vac.s_vaccinereltype WHERE vaccine_id = p_s_vaccine_upins.vaccine_id;

      UPDATE vac.s_vaccine
      SET
          vaccine_name = in_vaccinename,
          vaccine_nick = in_vaccinenick,
          vaccine_signcomb = in_vaccinesignComb,
          vaccine_agebegin = in_vaccineagebegin,
          vaccine_ageend = in_vaccineageend
      WHERE vaccine_id = p_s_vaccine_upins.vaccine_id;
  ELSE
      INSERT INTO vac.s_vaccine(vaccine_name, vaccine_signcomb, vaccine_nick, vaccine_agebegin, vaccine_ageend)
      SELECT in_vaccinename,
             in_vaccinesigncomb,
             in_vaccinenick,
             in_vaccineagebegin,
             in_vaccineageend
      RETURNING vaccine_id INTO p_s_vaccine_upins.vaccine_id;
  END IF;

  INSERT INTO vac.s_vaccinereltype
  SELECT io_vaccine_id,
         SUBSTRING(a.paramval, 1, CASE
                                    WHEN (strpos(a.paramval, ':') > 0) THEN (strpos(a.paramval, ':') - 1)
                                    ELSE length(a.paramval)
                                  END),
         NULL,
         NULL,
         NULL,
         NULL
  FROM vac.list2tableDelim(in_typeinfections, ',') a;

  --Доза (begin)
  UPDATE vac.s_vaccinedose
	  SET dose_status = 0 --удаляем запись
  WHERE vaccine_id = p_s_vaccine_upins.vaccine_id;
  IF in_dozaage=1000
  THEN
      INSERT INTO vac.s_vaccinedose (vaccine_id, vaccinedose_ages, vaccinedose_agee, vaccinedose_dose, vaccinedose_dosetype, dose_status)
      SELECT p_s_vaccine_upins.vaccine_id,
             0,
             in_sozaage,
             in_sozaval1,
             in_sozetype1,
             1;
  ELSE
    INSERT INTO vac.s_vaccinedose
          (vaccine_id, vaccinedose_ages, vaccinedose_agee, vaccinedose_dose, vaccinedose_dosetype, dose_status)
          SELECT p_s_vaccine_upins.vaccine_id, 0, in_dozaage, in_dozaval1, in_dozetype1, 1
          UNION
          SELECT p_s_vaccine_upins.vaccine_id, in_dozaage, 1000, in_dozaval2, in_dozetype2, 1;
  END IF;
  --Доза (end)
  --Способ ввода (begin)
  UPDATE vac.s_vaccinewayplace
	  SET wayplace_status = 0 --удаляем запись
  WHERE vaccine_id = p_s_vaccine_upins.vaccine_id;

  IF in_WayAge=10000
  THEN
      INSERT INTO vac.s_vaccinewayplace
          (vaccine_id, vaccinewayplace_ages, vaccinewayplace_agee, vaccineway_id, vaccineplacehome_id, wayplace_status)
          SELECT p_s_vaccine_upins.vaccine_id, 0, in_wayage, in_waytype1, in_placetype1, 1;
  ELSE
    INSERT INTO vac.s_vaccinewayplace
          (Vaccine_id, vaccinewayplace_ages, vaccinewayplace_agee, vaccineway_id, vaccineplacehome_id, wayplace_status)
          SELECT p_s_vaccine_upins.vaccine_id, 0, in_wayage, in_waytype1, in_placetype1, 1
          UNION
          SELECT p_s_vaccine_upins.vaccine_id, in_wayage, 10000, in_waytype2, in_placetype2, 1;
   END IF;
   --Способ ввода (end)

EXCEPTION
	WHEN others THEN error_code:=SQLSTATE; error_message:=SQLERRM;

END;
$body$
LANGUAGE 'plpgsql'
VOLATILE
CALLED ON NULL INPUT
SECURITY DEFINER
COST 100;
 */

		$query = "SELECT vaccine_id AS \"Vaccine_id\", error_code AS \"Error_Code\", error_message AS \"Error_Msg\"
                  FROM vac.p_s_vaccine_upins (
                      in_vaccinename 		:= :VaccineName,
                      in_vaccinenick 		:= :VaccineNick,
                      in_vaccineagebegin 	:= :VaccineAgeBegin,
                      in_vaccineageend 		:= :VaccineAgeEnd,
                      in_vaccinesigncomb 	:= :VaccineSignComb,
                      in_dozaage			:= :DozaAge,
                      in_dozaval1			:= :DozaVal1,
                      in_dozaval2 			:= :DozaVal2,
                      in_dozetype1 			:= :DozeType1,
                      in_dozetype2 			:= :DozeType2,
                      in_wayage 			:= :WayAge,
                      in_placetype1 		:= :placeType1,
                      in_placetype2 		:= :placeType2,
                      in_waytype1 			:= :wayType1,
                      in_waytype2 			:= :wayType2,
                      in_typeinfections 	:= :TypeInfections,
                      vaccine_id 			:= :VaccineId
                  );
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
		  select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
		  from vac.p_vac_JournalMedTapRefusal_del(
							refuse_id              := :refuse_id);
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
				update vac.s_vaccine
					Set StatusType_id = 100,
						  Del_PmUser_id = :PmUser_id,
						  DelDateSave = GETDATE()
					where Vaccine_id = :Vaccine_id
			    returning null as \"Error_Code\", null as \"Error_Msg\"
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
		  select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
		  from vac.p_vac_JournalAccount_del(
					vacJournalAccount_id := :vacJournalAccount_id, -- идентификатор записи
					PmUser_id            := :PmUser_id -- ID пользователя, который удалил прививку
                    );
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
		  select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
		  from vac.p_vac_interval_exceeded(
					Inoculation_id := :Inoculation_id, -- идентификатор записи
					PmUser_id            := :PmUser_id -- ID пользователя, который удалил прививку
                    );
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
      select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
      from vac.p_vac_JournalMantu_del(
				JournalMantu_id := :JournalMantu_id, -- идентификатор записи
				PmUser_id            := :PmUser_id -- ID пользователя, который удалил прививку
                );
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
	////  LEFT JOIN vac.S_VaccineWay w  ON ac.VaccineWay_id = w.VaccineWay_id

	////  LEFT JOIN vac.S_VaccinePlace p  ON ac.VaccinePlace_id = p.VaccinePlace_id

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
	//  to_char(ac.vacJournalAccount_DateVac, 'DD.MM.YYYY') Date_impl,

	//  ac.vacJournalAccount_Vac_MedPersonal_id MedPers_id_impl,
	//  ac.vacJournalAccount_ReactlocaDescription ReactLocalDesc,
	//  ac.vacJournalAccount_ReactGeneralDescription ReactGeneralDesc
	//	,to_char(ac.vacJournalAccount_DatePurpose, 'DD.MM.YYYY') [Date_Purpose]

	//	,ac.vacJournalAccount_Infection NAME_TYPE_VAC
	//	,to_char(ac.vac_Person_BirthDay, 'DD.MM.YYYY') [BirthDay]

	//	,ac.Vaccine_name vac_name
	//	,ac.Vaccine_id
	//	,ac.Person_id
	//-- FROM [vac].[vac_JournalAccount] ac
	// FROM [vac].[v_JournalAccount] ac
	//-- FROM [vac].v_Inoculation_All ac
	// LEFT JOIN vac.S_VaccineWay w  ON ac.VaccineWay_id = w.VaccineWay_id

	// LEFT JOIN vac.S_VaccinePlace p  ON ac.VaccinePlace_id = p.VaccinePlace_id

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
		$cte = "
            WITH cte AS (
                SELECT
                    s.MedStaffFact_id as MED_PERS_ID
            FROM
                v_pmUser u
                LEFT JOIN dbo.v_MedStaffFact s ON u.pmUser_Medpersonal_id = s.MedPersonal_id
            WHERE
                PMUser_id = :userId
			LIMIT 1
            )
        ";
		
        $query = $cte."
                SELECT 
                    (SELECT MED_PERS_ID FROM cte) as \"MedPers_id_user\",
                    ac.vacJournalAccount_id as \"vacJournalAccount_id\",
                    case 
                        when
                            p.VaccinePlace_Name is null and p2.VaccinePlace_Name is null and  ac.VaccinePlace_id is not null 
                        then
                            w2.VaccineWay_Name
                        when
                            p.VaccinePlace_Name is null and ac.VaccinePlace_id is not null
                        then 
                            p2.VaccinePlace_Name||': '||w2.VaccineWay_Name 
                    else
                        p.VaccinePlace_Name||': '||w.VaccineWay_Name 
                    end as \"VaccineWay_name\",
                    ac.vacJournalAccount_Dose as \"Doza\",
                    ac.vacJournalAccount_StatusType_id as \"StatusType_id\",
                    ac.vacJournalAccount_Purpose_MedPersonal_id as \"MedPers_id_purp\",
                    to_char(ac.vacJournalAccount_DateVac, 'DD.MM.YYYY') as \"Date_impl\",
                    ac.vacJournalAccount_Vac_MedPersonal_id as \"MedPers_id_impl\",
                    ac.vacJournalAccount_ReactlocaDescription as \"ReactLocalDesc\",
                    ac.vacJournalAccount_ReactGeneralDescription as \"ReactGeneralDesc\",
                    to_char(ac.vacJournalAccount_DatePurpose, 'DD.MM.YYYY') as \"Date_Purpose\",
                    replace (ac.vacJournalAccount_Infection, '<br />', '')  as \"NAME_TYPE_VAC\",
                    to_char(ac.vac_Person_BirthDay, 'DD.MM.YYYY') as \"BirthDay\",
                    ac.Vaccine_name as \"vac_name\",
                    ac.Vaccine_id as \"Vaccine_id\",
                    ac.Person_id as \"Person_id\",
                    ac.vacJournalAccount_Seria as \"Seria\",
                    to_char(ac.vacJournalAccount_Period, 'DD.MM.YYYY') as \"vacPeriod\",
                    v.Vaccine_AgeBegin as \"Vaccine_AgeBegin\",
                    v.Vaccine_AgeEnd as \"Vaccine_AgeEnd\",
                    ac.Lpu_id as \"Lpu_id\",
                    ac.Medservice_id as \"Medservice_id\",
                    ac.vacJournalAccount_vacOther as \"vacOther\",
                    MS.LpuBuilding_id as \"LpuBuilding_id\"
                FROM
                    vac.v_journalaccountall ac
                    LEFT JOIN vac.s_vaccineway w  ON ac.VaccineWay_id = w.VaccineWay_id            
                    LEFT JOIN vac.v_vaccineplace p  ON ac.VaccinePlace_id = p.VaccinePlace_id            
                    LEFT JOIN vac.v_vaccinewayplace wp2  ON ac.VaccinePlace_id = wp2.VaccineWayPlace_id            
                    LEFT JOIN vac.v_vaccineplace p2  ON wp2.VaccinePlace_id = p2.VaccinePlace_id            
                    LEFT JOIN vac.s_vaccineway w2  ON wp2.VaccineWay_id = w2.VaccineWay_id            
                    LEFT JOIN vac.v_vaccine v  ON v.Vaccine_id = ac.Vaccine_id            
                    LEFT JOIN v_medservice MS on MS.MedService_id = ac.MedService_id
                WHERE
                    ac.vacJournalAccount_id = :vacJaccountId
                
            UNION
            
            SELECT 
                (SELECT MED_PERS_ID FROM cte) as \"MedPers_id_user\",
                ac.vacJournalAccount_id as \"vacJournalAccount_id\",
                p.VaccinePlace_Name||': '||w.VaccineWay_Name as \"VaccineWay_name\",
                ac.vacJournalAccount_Dose as \"Doza\",
                ac.vacJournalAccount_StatusType_id as \"StatusType_id\",
                ac.vacJournalAccount_Purpose_MedPersonal_id as \"MedPers_id_purp\",
                to_char(ac.vacJournalAccount_DateVac, 'DD.MM.YYYY') as \"Date_impl\",
                
                ac.vacJournalAccount_Vac_MedPersonal_id as \"MedPers_id_impl\",
                ac.vacJournalAccount_ReactlocaDescription as \"ReactLocalDesc\",
                ac.vacJournalAccount_ReactGeneralDescription as \"ReactGeneralDesc\",
                to_char(ac.vacJournalAccount_DatePurpose, 'DD.MM.YYYY') as \"Date_Purpose\",
                
                replace (ac.vacJournalAccount_Infection, '<br />', '')  as \"NAME_TYPE_VAC\",
                to_char(ac.vac_Person_BirthDay, 'DD.MM.YYYY') as \"BirthDay\",
                
                ac.Vaccine_name as \"vac_name\",
                ac.Vaccine_id as \"Vaccine_id\",
                ac.Person_id as \"Person_id\",
                ac.vacJournalAccount_Seria as \"Seria\",
                to_char(ac.vacJournalAccount_Period, 'DD.MM.YYYY') as \"vacPeriod\",
                
                v.Vaccine_AgeBegin as \"Vaccine_AgeBegin\",
                v.Vaccine_AgeEnd as \"Vaccine_AgeEnd\",
                ac.Lpu_id as \"Lpu_id\",
                ac.Medservice_id  as \"Medservice_id\",
                ac.vacJournalAccount_vacOther as \"vacOther\",
                MS.LpuBuilding_id as \"LpuBuilding_id\"
            FROM
                vac.v_JournalVacFixed ac
                LEFT JOIN vac.S_VaccineWay w  ON ac.VaccineWay_id = w.VaccineWay_id
                LEFT JOIN vac.v_VaccinePlace p  ON ac.VaccinePlace_id = p.VaccinePlace_id
                LEFT JOIN vac.v_Vaccine v   ON v.Vaccine_id = ac.Vaccine_id
                LEFT JOIN v_MedService MS on MS.MedService_id = ac.MedService_id
            WHERE
                ac.vacJournalAccount_id = :vacJaccountId
            LIMIT 1
        ";

		$queryParams['vacJaccountId'] = $this->nvl($data['vac_jaccount_id']);
		$queryParams['userId'] = $this->nvl($data['user_id']);

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
SELECT v.Vaccine_id as \"Vaccine_id\", v.Vaccine_AgeBegin as \"Vaccine_AgeBegin\", v.Vaccine_AgeEnd as \"Vaccine_AgeEnd\"
 FROM vac.v_Vaccine v 

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
        $planTmpId = $this->nvl($data['plan_id']);


		$query = "
            with cte as (
                select
                    s.MedStaffFact_id as MED_PERS_ID
                from
                    dbo.v_pmUser u 
                    left join dbo.v_MedStaffFact s
                on
                    u.pmUser_Medpersonal_id = s.MedPersonal_id
                where
                    PMUser_id = :userId
                limit 1
            )
		";


		if($planTmpId == -2) {
		    $query .= "
                select
                    :planTmpId as \"Plan_id\",
                    :planTmpId as \"planTmp_id\",
                    vacJournalAccount_id as \"vacJournalAccount_id\",
                    (select MED_PERS_ID  from cte ) as \"MedPers_id\",
                    Lpu_id as \"Lpu_id\",
                    MedService_id as \"MedService_id\", 
                    Vaccine_id as \"Vaccine_id\",
                    to_char(vacJournalAccount_DatePurpose, 'dd.mm.yyyy') as \"Date_Plan\",
                    null \"type_name\",
                    null \"Name\",
                    null \"SequenceVac\",
                    null \"VaccineType_id\",
                    to_char(vac_Person_BirthDay, 'dd.mm.yyyy') as \"BirthDay\",
                    Person_id as \"Person_id\",
                    vacJournalAccount_KeyList as \"StoreKeyList\",
                    VaccinePlace_id as \"VaccinePlace_id\", 
                    vacJournalAccount_Dose as \"Dose\",
                    vacJournalAccount_Seria as \"Seria\"
                from
                    vac.v_JournalVacFixed --vac.vac_JournalAccount 
                where
                    vacJournalAccount_id = :vacJournalAccount_id
                limit 1
          ";
        } elseif ($planTmpId != -1) {
		    $query .= "
                SELECT
                    :planTmpId as \"Plan_id\",
                    (select MED_PERS_ID  from cte ) as \"MedPers_id\",
                    vac_PersonPlanFinal_id as \"planTmp_id\",
                    to_char(vac_PersonPlanFinal_DatePlan, 'dd.mm.yyyy') as \"Date_Plan\",
                    Person_PlanFinal_typeName as \"type_name\",
                    VaccineType_Name as \"Name\",
                    NationalCalendarVac_SequenceVac as \"SequenceVac\",
                    VaccineType_id as \"VaccineType_id\",
                    to_char(vac_Person_BirthDay, 'dd.mm.yyyy') as \"BirthDay\",
                    Person_id as \"Person_id\"
                 FROM 
                    vac.v_PersonPlanFinal
                 WHERE
                    vac_PersonPlanFinal_id = :planTmpId
            ";
        } else {
		    $query .= "
                SELECT
                    :planTmpId as \"Plan_id\",
                    (select MED_PERS_ID  from cte ) as  \"MedPers_id\",
                    :planTmpId as \"planTmp_id\",
                    to_char(vacPersonPlan0_DateS, 'dd.mm.yyyy') as \"Date_Plan\",
                    NationalCalendarVac_typeName as \"type_name\",
                    NationalCalendarVac_vaccineTypeName as \"Name\",
                    NationalCalendarVac_SequenceVac as \"SequenceVac\",
                    vaccineType_id as \"VaccineType_id\",
                    to_char(BirthDay, 'dd.mm.yyyy') as \"BirthDay\",
                    Person_id as \"Person_id\"
                FROM 
                    vac.v_vacPersonPlan0
                WHERE
                    Person_id = :Person_id
                and
                    NationalCalendarVac_Scheme_id = :Vac_Scheme_id
            ";
        }

		$queryParams['planTmpId'] = $planTmpId;
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
            SELECT 
                pt.PlanTuberkulin_id as \"PlanTuberkulin_id\", 
                pt.Person_id as \"Person_id\", 
                (SELECT s.MedStaffFact_id 
                  FROM v_pmUser u 
                      LEFT JOIN dbo.v_MedStaffFact s  
                      ON u.pmUser_Medpersonal_id = s.MedPersonal_id
                  WHERE PMUser_id = :userId
                  LIMIT 1
                ) as \"MedPers_id\",
                to_char(pers.BirthDay, 'DD.MM.YYYY') as \"person_BirthDay\"
            FROM vac.vac_PlanTuberkulin pt 
                LEFT OUTER JOIN vac.v_vac_Person pers  on pers.Person_id = pt.Person_id
            WHERE pt.PlanTuberkulin_id = :planTuberkulinId
            LIMIT 1
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
		//,to_char(j.JournalMantu_DatePurpose, 'DD.MM.YYYY') [DatePurpose]

		$query = "
  SELECT JournalMantu_id as \"JournalMantu_id\",
       j.Person_id as \"Person_id\",
       j.VacPresence_id as \"VacPresence_id\",
       j.JournalMantu_Seria as \"JournalMantu_Seria\",
       j.JournalMantu_Dose as \"Dose\",
       coalesce(j.VaccineWayPlace_id, wp.VaccineWayPlace_id) as \"WayPlace_id\",
       --j.VaccinePlace_id as \"WayPlace_id\",
       j.MantuReactionType_id as \"React_id\",
       j.JournalMantu_ReactDescription as \"JournalMantu_ReactDescription\",
       j.JournalMantu_ReactionSize as \"ReactionSize\",
       j.JournalMantu_Reaction_30min as \"Reaction30min\",
       to_char(j.JournalMantu_DatePurpose, 'DD.MM.YYYY') as \"DatePurpose\",
       to_char(j.JournalMantu_DateVac, 'DD.MM.YYYY') as \"DateVac\",
       to_char(j.JournalMantu_DateReact, 'DD.MM.YYYY') as \"DateReact\",
       j.JournalMantu_Lpu_id as \"Lpu_id\",
       j.JournalMantu_vacMedPersonal_id as \"MedPersonal_id\",
       j.JournalMantu_StatusType_id as \"StatusType_id\",
       pr.VacPresence_Seria as \"VacPresence_Seria\",
       to_char(pr.VacPresence_Period, 'DD.MM.YYYY') as \"VacPresence_Period\",
       pr.VacPresence_Manufacturer as \"VacPresence_Manufacturer\",
       to_char(pers.BirthDay, 'DD.MM.YYYY') as \"person_BirthDay\",
       TubDiagnosisType_id as \"TubDiagnosisType_id\",
       DiaskinTestReactionType_id as \"DiaskinTestReactionType_id\",
       coalesce(j.VaccineWayPlace_id::varchar, '') || coalesce(j.VaccinePlace_id::varchar, '') AS \"id_VaccineWayPlace_VaccinePlace\"
FROM vac.vac_JournalMantu j
     LEFT JOIN vac.v_VaccineWayPlace wp ON j.VaccinePlace_id = wp.VaccinePlace_id AND j.VaccineWay_id = wp.VaccineWay_id
     LEFT JOIN vac.Vac_Presence pr ON j.VacPresence_id = pr.VacPresence_id
     LEFT OUTER JOIN vac.v_vac_Person pers on pers.Person_id = j.Person_id
WHERE j.JournalMantu_id =:JournalMantuId
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
			WITH cte1 AS (
			      SELECT d.VaccineDose_AgeS AS Dose_AgeS1,
			             d.VaccineDose_AgeE AS Dose_AgeE1,
			             w.VaccineWayPlace_AgeS AS Way_AgeS1,
			             w.VaccineWayPlace_AgeE AS Way_AgeE1,
			             d.VaccineDose_Dose::text AS DoseVal1,
			             d.VaccineDose_DoseType AS DoseType1,
			             w.VaccineWay_id AS WayType1,
			             w.VaccinePlaceHome_id AS PlaceType1,
			             COALESCE((
			                        Select 1
			                        from vac.S_VaccineRelType vt
			                             JOIN vac.S_VaccineType priv On priv.VaccineType_id = vt.VaccineType_id and VaccineType_SignNatCal = 0
			                        where vt.Vaccine_id = v.Vaccine_id
			                        limit 1
			             ), 0) AS Gripp
			      FROM vac.S_Vaccine v
			           LEFT JOIN vac.S_VaccineDose d ON d.Vaccine_id = v.Vaccine_id AND d.VaccineDose_AgeE < 1000 AND d.dose_status = 1
			           LEFT JOIN vac.S_VaccineWayPlace w ON v.Vaccine_id = w.Vaccine_id AND w.VaccineWayPlace_AgeE < 10000 AND w.wayPlace_status = 1
			      WHERE v.Vaccine_id = :Vaccine_id
				  LIMIT 1),
			      cte2 AS (
			      SELECT d.VaccineDose_AgeS AS Dose_AgeS2,
			             d.VaccineDose_AgeE AS Dose_AgeE2,
			             w.VaccineWayPlace_AgeS AS Way_AgeS2,
			             w.VaccineWayPlace_AgeE AS Way_AgeE2,
			             d.VaccineDose_Dose::text AS DoseVal2,
			             d.VaccineDose_DoseType AS DoseType2,
			             w.VaccineWay_id AS WayType2,
			             w.VaccinePlaceHome_id AS PlaceType2,
			             COALESCE((Select 1
			      from vac.S_VaccineRelType vt
			           JOIN vac.S_VaccineType priv On priv.VaccineType_id = vt.VaccineType_id and VaccineType_SignScheme = 2
			      where vt.Vaccine_id = v.Vaccine_id
			      limit 1), 0) AS Gripp
			      FROM vac.S_Vaccine v
			           LEFT JOIN vac.S_VaccineDose d ON d.Vaccine_id = v.Vaccine_id AND d.VaccineDose_AgeE = 1000 AND d.dose_status = 1
			           LEFT JOIN vac.S_VaccineWayPlace w ON w.Vaccine_id = v.Vaccine_id AND w.VaccineWayPlace_AgeE = 10000 AND w.wayPlace_status = 1
			      WHERE v.Vaccine_id =:Vaccine_id
				  LIMIT 1
			      )
			
			
			      --Доза и способ ввода
			
			      SELECT 
			            Vaccine_id as \"Vaccine_id\",
			            Vaccine_Name as \"Vaccine_Name\",
			            Vaccine_SignComb as \"Vaccine_SignComb\",
			            Vaccine_Nick as \"Vaccine_Nick\",
			            Vaccine_FullName as \"Vaccine_FullName\",
			            Vaccine_NameInfection as \"Vaccine_NameInfection\",
			            Vaccine_WayPlaceAge as \"Vaccine_WayPlaceAge\",
			            Vaccine_WayPlace as \"Vaccine_WayPlace\",
			            Vaccine_doseAge as \"Vaccine_doseAge\",
			            Vaccine_dose as \"Vaccine_dose\",
			            Vaccine_AgeBegin as \"Vaccine_AgeBegin\",
			            Vaccine_AgeEnd as \"Vaccine_AgeEnd\",
			            Vaccine_AgeRange2Sim as \"Vaccine_AgeRange2Sim\",
			            comment as \"comment\",
			            pmUser_insID as \"pmUser_insID\",
			            Vaccine_insDT as \"Vaccine_insDT\",
			            pmUser_updID as \"pmUser_updID\",
			            Vaccine_updDT as \"Vaccine_updDT\",
			             vac.GetVacTypes(:Vaccine_id) AS \"VacTypeIds\",
			             (
			               SELECT Dose_AgeS1
			               FROM cte1
			             ) AS \"Dose_AgeS1\",
			             (
			               SELECT Dose_AgeE1
			               FROM cte1
			             ) AS \"Dose_AgeE1\",
			             (
			               SELECT Way_AgeS1
			               FROM cte1
			             ) AS \"Way_AgeS1\",
			             (
			               SELECT Way_AgeE1
			               FROM cte1
			             ) AS \"Way_AgeE1\",
			             (
			               SELECT DoseVal1
			               FROM cte1
			             ) AS \"DoseVal1\",
			             (
			               SELECT DoseType1
			               FROM cte1
			             ) AS \"DoseType1\",
			             (
			               SELECT WayType1
			               FROM cte1
			             ) AS \"WayType1\",
			             (
			               SELECT PlaceType1
			               FROM cte1
			             ) AS \"PlaceType1\",
			             (
			               SELECT Dose_AgeS2
			               FROM cte2
			             ) AS \"Dose_AgeS2\",
			             (
			               SELECT Dose_AgeE2
			               FROM cte2
			             ) AS \"Dose_AgeE2\",
			             (
			               SELECT Way_AgeS2
			               FROM cte2
			             ) AS \"Way_AgeS2\",
			             (
			               SELECT Way_AgeE2
			               FROM cte2
			             ) AS \"Way_AgeE2\",
			             (
			               SELECT DoseVal2
			               FROM cte2
			             ) AS \"DoseVal2\",
			             (
			               SELECT DoseType2
			               FROM cte2
			             ) AS \"DoseType2\",
			             (
			               SELECT WayType2
			               FROM cte2
			             ) AS \"WayType2\",
			             (
			               SELECT PlaceType2
			               FROM cte2
			             ) AS \"PlaceType2\",
			             (
			               SELECT Gripp
			               FROM cte2
			             ) AS \"OnGripp\" --  Признак наличия прививки от гриппа
			      FROM vac.v_vaccine vac
			      WHERE vac.vaccine_id =:Vaccine_id
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
            SELECT Vaccine_id as \"Vaccine_id\"
              ,Vaccine_Name as \"Vaccine_Name\"
              ,Vaccine_Nick as \"Vaccine_Nick\"
              ,AgeTypeS1 as \"AgeTypeS1\"
              ,AgeS1 as \"AgeS1\"
              ,AgeTypeS2 as \"AgeTypeS2\"
              ,AgeS2 as \"AgeS2\"
              ,AgeTypeE1 as \"AgeTypeE1\"
              ,AgeE1 as \"AgeE1\"
              ,AgeTypeE2 as \"AgeTypeE2\"
              ,AgeE2 as \"AgeE2\"
              ,multiplicity1 as \"multiplicity1\"
              ,multiplicity2 as \"multiplicity2\"
              ,multiplicityRisk1 as \"multiplicityRisk1\"
              ,multiplicityRisk2 as \"multiplicityRisk2\"
              ,PeriodTypeInterval1 as \"PeriodTypeInterval1\"
              ,Interval1 as \"Interval1\"
              ,PeriodTypeInterval2 as \"PeriodTypeInterval2\"
              ,Interval2 as \"Interval2\"
              ,PeriodTypeIntervalRisk1 as \"PeriodTypeIntervalRisk1\"
              ,IntervalRisk1 as \"IntervalRisk1\"
              ,PeriodTypeIntervalRisk2 as \"PeriodTypeIntervalRisk2\"
              ,IntervalRisk2 as \"IntervalRisk2\"
          FROM vac.v_OtherVacScheme_Brief s 
          where s.Vaccine_id = :Vaccine_id 
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
  SELECT  
       VaccineType_id as \"VaccineType_id\"
      , NationalCalendarVac_Scheme_id as \"Scheme_id\"
      , Type_id as \"Type_id\"
      , NationalCalendarVac_SequenceVac as \"SequenceVac\"
      , NationalCalendarVac_Scheme_Num as \"Scheme_Num\"
      , NationalCalendarVac_SignPurpose as \"SignPurpose\"
  
      , VaccineAgeBorders_AgeTypeS as \"AgeTypeS\"
      , VaccineAgeBorders_AgeS as \"AgeS\"
      , VaccineAgeBorders_AgeTypeE as \"AgeTypeE\"
      , VaccineAgeBorders_AgeE as \"AgeE\"
      
      ,VaccineAgeBorders_PeriodVac as \"PeriodVac\"
      ,VaccineAgeBorders_PeriodVacType as \"PeriodVacType\"
      , COALESCE(NationalCalendarVac_Additional, 0) as \"Additional\"

      , NationalCalendarVac_SequenceVac as \"SequenceVac\"
      , max_SequenceVac as \"max_SequenceVac\"
      , COALESCE(max_Additional, 0) as \"max_Additional\"

  FROM vac.v_NationalCalendarVac 

	WHERE   {$filter} 
    LIMIT 1
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
                 Select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
                    from vac.p_S_NationalCalendarVac_del(
                         NationalCalendarVac_id := :NationalCalendarVac_id,     -- Идентификатор записи
                         pmUser_id   := :user_id      --  ID пользователя, который удалил запись
                         );
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
		//  ,to_char(r.BirthDay, 'DD.MM.YYYY') [BirthDay]

		//  ,to_char(r.vacJournalMedTapRefusal_DateBegin, 'DD.MM.YYYY') DateBegin

		//  ,to_char(r.vacJournalMedTapRefusal_DateEnd, 'DD.MM.YYYY') DateEnd

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
		//	  LEFT JOIN vac.S_VaccineType tp  ON r.VaccineType_id = tp.VaccineType_id


		$query = "
SELECT r.vacJournalMedTapRefusal_id as \"refuse_id\",
       r.VaccineType_id as \"VaccineType_id\",
       r.vacJournalMedTapRefusal_MedPersonal_id as \"MedPersonal_id\",
       r.vacJournalMedTapRefusal_Reason as \"Reason\",
       r.vacJournalMedTapRefusal_TypeRecord as \"TypeRecord\",
       to_char(r.vacJournalMedTapRefusal_DateBegin, 'DD.MM.YYYY') as \"RefuseDateBegin\",
       to_char(r.vacJournalMedTapRefusal_DateEnd, 'DD.MM.YYYY') as \"RefuseDateEnd\",
       r.BirthDay as \"BirthDay\"
FROM vac.v_JournalMedTapRefusal r
WHERE r.vacJournalMedTapRefusal_id =:refuse_id    ";
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
        $query = "
            SELECT VaccineRisk_id as \"VaccineRisk_id\",
                   VaccineType_id as \"VaccineType_id\",
                   Person_id as \"Person_id\"
            FROM vac.v_VaccineRisk
            WHERE person_id = :person_id
        ";

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
        Select
            VaccineRisk_id as \"VaccineRisk_id\",
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\"
        from vac.p_VaccineRisk_ins
        (
            Person_id      := :person_id,       -- id пациента
            VaccineType_id := :vaccine_type_id,     --  Идентификатор инфекции
            pmUser_insID   := :user_id  --  ID пользователя, который создал запись
        );
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
            Select
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
            from vac.p_VaccineRisk_del(
                VaccineRisk_id := :vaccine_risk_id,     -- Идентификатор записи
                pmUser_delID   := :user_id      --  ID пользователя, который удалил запись
            );
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
                RefusalType_id as \"RefusalType_id\",
                RefusalType_name as \"RefusalType_name\"
            FROM
                vac.S_RefusalType 
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
  MantuReactionType_id as \"reaction_id\"
  ,MantuReactionType_name as \"reaction_name\"
FROM vac.S_MantuReactionType 

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
              DiaskinTestReactionType_id as \"DiaskinTestReactionType_id\"
              ,DiaskinTestReactionType_name as \"DiaskinTestReactionType_name\"
            FROM vac.S_DiaskinTestReactionType 

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
            Select distinct 
                    Lpu_id as \"Lpu_id\",
                    Lpu_Nick as \"Lpu_Name\"
                from (
                   select ms.Lpu_id,
                          l.Lpu_Nick,
                          l.Lpu_Name,
                          ms.LpuBuilding_id,
                          lb.LpuBuilding_Name,
                          ms.MedService_Nick
                   from v_MedService ms
                        left join v_LpuBuilding lb on ms.LpuBuilding_id = lb.LpuBuilding_id
                        left join v_Lpu l on ms.Lpu_id = l.Lpu_id
                   where 
                         ms.MedServiceType_id = 31 and
                         ms.MedService_begDT <= NOW() and
                         (ms.MedService_endDT is null or
                         ms.MedService_endDT >= NOW()) and
                         (ms.MedService_endDT is null or
                         ms.MedService_endDT >= NOW())
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
            Select distinct MedService_id as \"MedService_id\",
                   MedService_Nick as \"MedService_Nick\",
                   Lpu_id as \"Lpu_id\",
                   LpuBuilding_id as \"LpuBuilding_id\"
            from (
                   select ms.Lpu_id,
                          l.Lpu_Nick,
                          l.Lpu_Name,
                          ms.LpuBuilding_id,
                          lb.LpuBuilding_Name,
                          ms.MedService_id,
                          ms.MedService_Nick
                   from v_MedService ms
                        left join v_LpuBuilding lb on ms.LpuBuilding_id = lb.LpuBuilding_id
                        left join v_Lpu l on ms.Lpu_id = l.Lpu_id
                   where 
                         ms.MedServiceType_id = 31 and
                         ms.MedService_begDT <= GETDATE() and
                         (ms.MedService_endDT is null or
                         ms.MedService_endDT >= GETDATE()) and
                         (ms.MedService_endDT is null or
                         ms.MedService_endDT >= GETDATE()) 
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
            SElect *
            from (
                   Select distinct MedService_id as \"MedService_id\",
                          MedService_Nick as \"MedService_Nick\",
                          Lpu_id as \"Lpu_id\",
                          LpuBuilding_id as \"LpuBuilding_id\"
                   from (
                          select ms.Lpu_id,
                                 l.Lpu_Nick,
                                 l.Lpu_Name,
                                 ms.LpuBuilding_id,
                                 lb.LpuBuilding_Name,
                                 ms.MedService_id,
                                 ms.MedService_Nick
                                 --lb.Lpu_id, lb.LpuBuilding_id, ms.LpuUnit_id,  ms.LpuSection_id
                          from v_MedService ms
                               left join v_LpuBuilding lb on ms.LpuBuilding_id = lb.LpuBuilding_id
                               left join v_Lpu l on ms.Lpu_id = l.Lpu_id
                          where --ms.MedService_id=50
                                ms.MedServiceType_id = 31 and
                                ms.MedService_begDT <= GETDATE() and
                                (ms.MedService_endDT is null or
                                ms.MedService_endDT >= GETDATE()) and
                                (ms.MedService_endDT is null or
                                ms.MedService_endDT >= GETDATE())
                                " . $filter . "
                        ) t
                   union
                   Select 0 MedService_id,
                          ' Все службы'              MedService_Nick,
                          null lpu_id,
                          null LpuBuilding_id
                   union
                   Select - 1 MedService_id,
                          ' Не определено'              MedService_Nick,
                          null lpu_id,
                          null LpuBuilding_id
                 ) t
            order by \"MedService_Nick\"
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
                SElect MedStaffFact_id as \"MedStaffFact_id\",
                       Person_Fin as \"Person_Fin\",
                       post.name as \"Post_Name\",
                       Lpu_id as \"Lpu_id\"
                from v_MedStaffFact med
                     left outer join persis.v_Post post on med.Post_id = post.id
                where WorkData_begDate <= GetDate() and
                      (WorkData_endDate >= GetDate() or
                      WorkData_endDate is null)
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
			$filter .= " AND EXISTS(SELECT * FROM dbo.v_MedStaffFact MSF WHERE MSF.MedPersonal_id = VMP.MedPersonal_id AND MSF.PostKind_id = 6 AND MSF.Lpu_id = VMP.Lpu_id)";
		}

		$query = "
                SELECT MedServiceMedPersonal_id as \"MedServiceMedPersonal_id\"
                  ,Lpu_id as \"Lpu_id\"
                  ,LpuBuilding_id as \"LpuBuilding_id\"
                  ,MedService_id as \"MedService_id\"
                  ,MedPersonal_id as \"MedPersonal_id\"
                  ,MedPersonal_Name as \"MedPersonal_Name\"
                  ,Person_Fin as \"Person_Fin\"
                  ,MedServiceMedPersonal_begDT as \"MedServiceMedPersonal_begDT\"
                  ,MedServiceMedPersonal_endDT as \"MedServiceMedPersonal_endDT\"
                  , to_char(GetDate(), 'DD.MM.YYYY') as \"dd\"

              FROM vac.v_VacMedPersonal VMP
                    where VMP.MedServiceMedPersonal_begDT <= GetDate()
                    and (VMP.MedServiceMedPersonal_endDT >=  GetDate()
                            or VMP.MedServiceMedPersonal_endDT is null)
                    " . $filter . "         
                ORDER BY VMP.MedPersonal_Name 
            ";

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
						SELECT
							MSMP.MedServiceMedPersonal_id as \"MedServiceMedPersonal_id\",
							MP.Lpu_id as \"Lpu_id\",
							MS.LpuBuilding_id as \"LpuBuilding_id\",
							MSMP.MedService_id as \"MedService_id\",
							MP.MedPersonal_id as \"MedPersonal_id\",
							MS.MedServiceType_id as \"MedServiceType_id\",
							PS.Person_SurName || ' ' || PS.Person_FirName || coalesce(' ' || PS.Person_SecName,'') as \"MedPersonal_Name\",
							PS.Person_SurName + ' ' + left(PS.Person_FirName,1) + isnull(left(PS.Person_SecName, 1),'') as \"Person_Fin\",
							MSMP.MedServiceMedPersonal_begDT as \"MedServiceMedPersonal_begDT\",
							MSMP.MedServiceMedPersonal_endDT as \"MedServiceMedPersonal_endDT\",
							to_char(GetDate(), 'DD.MM.YYYY') as \"dd\"
						FROM MedPersonalCache MP
							INNER join dbo.v_PersonState PS on MP.Person_id = PS.Person_id
							LEFT JOIN v_MedServiceMedPersonal MSMP ON MSMP.MedPersonal_id = MP.MedPersonal_id
							LEFT JOIN MedService MS ON MS.MedService_id = MSMP.MedService_id
						WHERE
							MP.MedPersonal_id = :MedPersonal_id
						ORDER BY MSMP.MedServiceMedPersonal_endDT
						LIMIT 1
					";
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
		//			FROM LpuBuilding LB 

		//			WHERE " . $filter . "
		//			ORDER by LB.LpuBuilding_Code, LB.LpuBuilding_Name
		//		";

		$query = "
			Select distinct LpuBuilding_id as \"LpuBuilding_id\",
                   LpuBuilding_Code as \"LpuBuilding_Code\",
                   LpuBuilding_Name as \"LpuBuilding_Name\"
            from (
                   select ms.Lpu_id,
                          l.Lpu_Nick,
                          l.Lpu_Name,
                          ms.LpuBuilding_id,
                          lb.LpuBuilding_Name,
                          ms.MedService_id,
                          lb.LpuBuilding_Code,
                          ms.MedService_Nick
                          --lb.Lpu_id, lb.LpuBuilding_id, ms.LpuUnit_id,  ms.LpuSection_id
                   from v_MedService ms
                        left join v_LpuBuilding lb on ms.LpuBuilding_id = lb.LpuBuilding_id
                        left join v_Lpu l on ms.Lpu_id = l.Lpu_id
                   where ms.MedServiceType_id = 31 and
                         ms.MedService_begDT <= GETDATE() and
                         (ms.MedService_endDT is null or
                         ms.MedService_endDT >= GETDATE()) and
                         (ms.MedService_endDT is null or
                         ms.MedService_endDT >= GETDATE()) " . $filter . "
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
			$sqlDeclare = "
              WITH cte AS (
                select Vaccine_id
                from vac.v_Vaccine
                where Vaccine_Nick ilike 'диаскинтест'
                limit 1
              )			
			";

		}else{
			$sqlDeclare = "
              WITH cte AS (
                select $vacineID AS Vaccine_id
              )	";		
		}
		
		$query = $sqlDeclare."
            SELECT
                VacPresence_id as \"VacPresence_id\",
                Vaccine_id as \"Vaccine_id\",
                VacPresence_Seria as \"Seria\",
                to_char(VacPresence_Period, 'DD.MM.YYYY')  as \"Period\",
                VacPresence_Seria || ' - ' || to_char(VacPresence_Period, 'DD.MM.YYYY')  as \"vacSeria\",
                VacPresence_Manufacturer as \"Manufacturer\"
            FROM vac.v_VacPresence
            WHERE
                VacPresence_toHave = 1
            AND
                Vaccine_id =
                (
                    select Vaccine_id from cte
                ) 
            AND
                lpu_id = :lpu_id
        ";

		$queryParams = [];
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
            Select
            	LpuRegion_id as \"LpuRegion_id\",
				LpuRegion_Name as \"LpuRegion_Name\"
            from vac.fn_GetRegion4LPU (
                Lpu_id			:= :lpu_id, 
                LpuBuilding_id 	:= :LpuBuilding_id, 
                LpuUnit_id		:= :LpuUnit_id, 
                LpuSection_id	:= :LpuSection_id) 
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
			Select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from vac.p_vacData4Card063_cntrl(
				Person_id := :Person_id);
		";
		$queryParams['Person_id'] = $this->nvl($data['Person_id']);
		//echo getDebugSQL($query, $queryParams);die;
		$result =  $this->db->query($query, $queryParams);
		$query = "
			SELECT
				DC.row_num as \"row_num\",
				DC.Inoculation_id as \"Inoculation_id\",
				DC.Inoculation_StatusType_id as \"Inoculation_StatusType_id\",
				DC.idInCurrentTable as \"idInCurrentTable\",
				DC.PersonPlan_id as \"PersonPlan_id\",
				DC.Lpu_id as \"Lpu_id\",
				DC.Scheme_num as \"Scheme_num\",
				DC.Person_id as \"Person_id\",
				DC.Scheme_id as \"Scheme_id\",
				DC.vaccineType_id as \"vaccineType_id\",
				Risks.risks as \"risks\",
				DC.StatusType_Name as \"StatusType_Name\",
				DC.VaccineType_Name as \"VaccineType_Name\",
				NCV.NationalCalendarVac_vaccineTypeName as \"VaccineType_FullName\",
				DC.typeName as \"typeName\",
				to_char(DC.date_vac::timestamp, 'DD.MM.YYYY') as \"date_vac\",

				to_char(DC.date_plan::timestamp, 'DD.MM.YYYY') as \"date_plan\",

				to_char(DC.Date_Purpose::timestamp, 'DD.MM.YYYY') as \"date_purpose\",

				DC.Age as \"Age\",
				DC.vac_name as \"vac_name\",
				DC.vacJournalAccount_Dose as \"vacJournalAccount_Dose\",
				DC.vacJournalAccount_Seria as \"vacJournalAccount_Seria\",
				DC.ReactGeneralDescription as \"ReactGeneralDescription\",
				DC.ReactlocaDescription as \"ReactlocaDescription\",
				DC.StatusType_id as \"StatusType_id\",
				DC.StatusSrok_id as \"StatusSrok_id\",
				DC.Tap_DateBeg as \"Tap_DateBeg\",
				DC.Tap_DateEnd as \"Tap_DateEnd\",
				DC.vacData4Card063_id as \"vacData4Card063_id\",
				--  По задаче #72923
				vacJournalAccount_Vac_PmUser_id as \"pmUser_updID\",
				-- vacJournalAccount_VacDateSave - Дата записи сведений о вакцинации, vacJournalAccount_DateSave - Дата внесения сведений о назначении
				COALESCE(ac.vacJournalAccount_VacDateSave, vacJournalAccount_DateSave) as \"vacJournalAccount_updDT\"

			from vac.vacData4Card063 DC 

			left join vac.v_NationalCalendarVac NCV  on NCV.NationalCalendarVac_Scheme_id = DC.Scheme_id

			--  По задаче #72923
			left join vac.Inoculation i   on i.Inoculation_id = DC.Inoculation_id

			left join vac.vac_JournalAccount ac   on ac.vacJournalAccount_id = i.vacJournalAccount_id

			LEFT JOIN LATERAL (
				select case when (
					VR.VaccineType_id = DC.vaccineType_id and
					RIGHT(NCV.NationalCalendarVac_SignPurpose,1) = '1'
				) then 1 else 0 end as risks
				from vac.VaccineRisk VR
				where VR.Person_id = DC.Person_id
				and VR.VaccineType_delDT is null
				order by 1 desc
                limit 1
			) as Risks ON true
			where DC.Person_id = :Person_id
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
					select distinct VR.VaccineType_id as \"VaccineType_id\"
					from vac.VaccineRisk VR 
					where VR.Person_id = :Person_id
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
			SELECT * FROM vac.fn_getPersonVacDigest(:person_id, dbo.tzGetDate())
			ORDER BY Date_plan_Sort
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

			
	Select Inoculation_id as \"Inoculation_id\",
           vacJournalAccount_id as \"vacJournalAccount_id\",
           Person_id as \"Person_id\",
           'V'       as \"typeName\",
           to_char(COALESCE(vacJournalAccount_DateVac, vacJournalAccount_DatePurpose), 'DD.MM.YYYY') as \"date_vac\",
           vacJournalAccount_age as \"age\",
           vacJournalAccount_uch as \"vacJournalAccount_uch\",
           Vaccine_Name as \"Vaccine_Name\",
           vacJournalAccount_Dose as \"VACCINE_DOZA\",
           vacJournalAccount_WayPlace as \"WAY_PLACE\",
           vacJournalAccount_Seria as \"Seria\",
           vacJournalAccount_Period as \"vacJournalAccount_Period\",
           ReactlocaDescription as \"ReactlocaDescription\",
           ReactGeneralDescription as \"ReactGeneralDescription\",
           vacJournalAccount_StatusType_id as \"StatusType_id\",
           vacJournalAccount_StatusType_Name as \"StatusType_Name\",
           MedService_id as \"MedService_id\",
           vac_Person_sex as \"vac_Person_sex\",
           vac_Person_BirthDay as \"vac_Person_BirthDay\",
           vac_Person_group_risk as \"group_risk\",
           Lpu_id as \"Lpu_id\",
           Lpu_Name as \"Lpu_Name\",
           VaccineType_id as \"VaccineType_id\",
           VaccineType_Name as \"VaccineType_Name\",
           Type_id as \"Type_id\",
           vacJournalAccount_DateSave as \"vacJournalAccount_DateSave\",
           vacJournalAccount_VacDateSave as \"vacJournalAccount_VacDateSave\",
           Scheme_id_after as \"Scheme_id_after\",
           DateVac_Old as \"DateVac_Old\",
           Start_DateVac as \"Start_DateVac\",
           Organized as \"Organized\",
           Inoculation_StatusType_id as \"Inoculation_StatusType_id\"
    from vac.v_InoculationOther
    where {$filter}
    order by vacJournalAccount_DateVac,
             vacJournalAccount_DatePurpose,
             Vaccine_Name
		";
		$queryParams['Person_id'] = $this->nvl($data['Person_id']);

		$result = $this->db->query($query, $queryParams);

		log_message('debug', 'getPersonVacOther=' . $query);
		log_message('debug', 'Person_id=' . $data['Person_id']);


		if (!is_object($result)) {
			return array(array('Error_Msg' => $errorMsg));
		}
		
        return $result->result('array');
    }

	/**
	 * Получаем список типов инфекций
	 */
	public function getVaccineTypeInfection()
	{
		$query = "
			 SELECT VaccineType_id as \"VaccineType_id\",
                   VaccineType_Name as \"VaccineType_Name\",
                   VaccineType_NameIm as \"VaccineType_NameIm\",
                   VaccineType_SignNatCal as \"VaccineType_SignNatCal\"
            FROM vac.S_VaccineType
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
/*
CREATE OR REPLACE FUNCTION vac.p_settings_sel (
)
RETURNS TABLE (
  id bigint,
  parent_id integer,
  name varchar,
  value varchar,
  description varchar
) AS
$body$
DECLARE
BEGIN


IF EXISTS (
SELECT * FROM information_schema.tables WHERE table_name = 'settings' AND table_schema = 'vac'
) THEN

    RETURN QUERY SELECT
      s.id,
      s.parent_id,
      s.name,
      s.value,
      s.description
    FROM
      vac.settings s;


END IF;


--EXCEPTION
--	when others then error_code:=SQLSTATE; error_message:=SQLERRM;


END;
$body$
LANGUAGE 'plpgsql'
VOLATILE
CALLED ON NULL INPUT
SECURITY DEFINER
COST 100 ROWS 1000;
*/
		$query = "
            SELECT 
                id as \"id\",
                parent_id as \"parent_id\",
                name as \"name\",
                value as \"value\",
                description as \"description\"
            FROM vac.p_settings_sel();
		";

		$result = $this->db->query($query);

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
			Select 0 as \"Org_id\",
                   'Все организации'                   as \"Org_Nick\"
            Union
            Select Org_id as \"Org_id\",
                   Org_Nick as \"Org_Nick\"
            from vac.v_VacOrgJob2LpuState
            where Lpu_id =:lpu_id
            order by \"Org_Nick\"
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
					$joinMore[] = ' ' . $this->getNameColumn($col) . ' in(' . $temp . ')';
				else
					$whereMore[] = ' ' . $this->getNameColumn($col) . ' in(' . $temp . ')';
				/*
					$joinMore[] = ' ['.$this->getNameColumn($col).'] in('.iconv('utf-8','windows-1251',$temp).')';
				else
						$whereMore[] = ' ['.$this->getNameColumn($col).'] in('.iconv('utf-8','windows-1251',$temp).')';
				*/
			}

			$where = (isset($whereMore)) ? ' and ' . implode(' and ', $whereMore) : $where;

			if (isset($joinMore)) {
				$join = "
					join vac.Inoculation i   on FromTable.vacJournalAccount_id = i.vacJournalAccount_id

					join vac.S_VaccineType tp   on i.VaccineType_id = tp.VaccineType_id and " . implode(' and ', $joinMore);

			}
			//$join = (isset($whereMore)) ? ' and ' .implode(' and ', $whereMore) : $join;


		}

		//$filter = ImplodeWherePH($filters).$where;

		$filter = "" . Implode(' and ', $filters);
		if(in_array(getRegionNick(), array('perm','penza','krym','astra'))){
			$join .= "
				left join lateral (
					SELECT VJA.EvnVizitPL_id, VJA.DocumentUcStr_id FROM vac.vac_JournalAccount VJA WHERE VJA.vacJournalAccount_id = FromTable.vacJournalAccount_id limit 1
				) VJA on true";
			$fields .= "
				,VJA.DocumentUcStr_id as \"DocumentUcStr_id\"
			";
		}else{
			$fields .= ",'' as \"DocumentUcStr_id\" ";
		}

		$sql = "
            SELECT  
				-- select 
				FromTable.JournalVac_id as \"JournalVac_id\"
				,FromTable.type_rec as \"type_rec\"
				,FromTable.vacJournalAccount_id as \"vacJournalAccount_id\"
				,FromTable.JournalMantu_id as \"fix_tub_id\"
				,to_char(FromTable.DatePurpose, 'DD.MM.YYYY') as \"DatePurpose\"
				,to_char(FromTable.DateVac, 'DD.MM.YYYY') as \"DateVac\"
				/*,FromTable.uch*/
				,FromTable.Person_id as \"Person_id\"
				,FromTable.Server_id as \"Server_id\"
				,FromTable.PersonEvn_id as \"PersonEvn_id\"
				,FromTable.SurName as \"SurName\"
				,FromTable.FirName as \"FirName\"
				,FromTable.SecName as \"SecName\"
				,FromTable.fio as \"fio\"
				,FromTable.Vaccine_Name as \"Vaccine_Name\"
				,FromTable.Vaccine_id as \"Vaccine_id\"
				,FromTable.Seria as \"Seria\"
				,FromTable.Period as \"Period\"
				,FromTable.Infection as \"Infection\"
				,FromTable.Dose as \"Dose\"
				,FromTable.WayPlace as \"WayPlace\"
				,FromTable.vac_Person_Sex as \"Sex\"
				,to_char(FromTable.BirthDay, 'DD.MM.YYYY') as \"BirthDay\"
				,FromTable.Age as \"Age\"
				,FromTable.StatusSrok_id as \"StatusSrok_id\"
				,FromTable.StatusType_id as \"StatusType_id\"
				,FromTable.StatusType_Name as \"StatusType_Name\"
				,FromTable.Lpu_id as \"Lpu_id\"
				,FromTable.Lpu_Name as \"Lpu_Name\"
				,FromTable.MedService_Nick as \"MedService_Nick\"
				,L2.Lpu_Nick as \"Lpu_Nick\"
				,PCard.Lpu_atNick as \"Lpu_atNick\"
				,PCard.LpuRegion_Name as \"uch\"
				{$fields}
              -- end select    
              FROM 
                -- from
                vac.v_JournalVac FromTable
                LEFT JOIN LATERAL(
					select 
						PC.PersonCard_id,
						PC.Person_id,
						PC.Lpu_id,
						PC.LpuRegion_id,
						COALESCE(L.Lpu_Nick,L.Lpu_Name) as Lpu_atNick,
						COALESCE(LR.LpuRegionType_Name,'') || ' №' || LR.LpuRegion_Name as LpuRegion_Name
					from v_PersonCard PC
					left join v_Lpu L  on L.Lpu_id = PC.Lpu_id
					left join v_LpuRegion LR  on LR.LpuRegion_id = PC.LpuRegion_id
					{$CardJoin}
					where PC.Person_id = FromTable.Person_id
                    limit 1
				) PCard ON true
				left join v_Lpu L2  on L2.Lpu_id = FromTable.Lpu_id
                {$join}
                 -- end from  
                 
                 Where
                 -- where
                  " . $filter . "
                     {$where} 
                       -- end where   
                       
                     order by 
                    -- order by                     
                    \"DatePurpose\"

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
                    lpu_id as \"lpu_id\", 
                    Lpu_Nick as \"Lpu_Nick\", 
                    Lpu_Name as \"Lpu_Name\", 
                    UAddress_Address  as \"UAddress_Address\"
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
                           t.Lpu_id as \"Lpu_id\",
                           LpuRegion_Name as \"LpuRegion_Name\",
                           LpuBuilding_name as \"UAddress_Address\",
                           reg.kol0 as \"kol0\",
                           a.kol as \"kol\"
                           -- end select                          
                    from
                         -- from
                         (
                           SELECT LpuRegion_id Lpu_id,
                                  LpuRegionType_Name || ' №' || LpuRegion_Name LpuRegion_Name,
                                  LpuRegionType_Name || ' №' || LpuRegion_Name Lpu_Name,
                                  LpuBuilding_name
                           FROM vac.v_Region2Section
                                --where Lpu_id = @Lpu_id
                                {$filter}
                           union
                           SElect - 1 as \"Lpu_id\",
                                  ' Не определен'              as \"Lpu_Nick\",
                                  'Не определен'              as \"Lpu_Name\",
                                  null as \"UAddress_Address\"
                         ) t
                         left join 
                         (
                           Select LpuRegion_id as lpu_id,
                                  COUNT(8) as kol0
                           from vac.v_vac_PersonRegion reg
                                --where  lpu_id  = @Lpu_id
                                {$filter}
                           group by LpuRegion_id
                         ) reg on t.lpu_id = reg.lpu_id
                         left join 
                         (
                           SElect lpu_id,
                                  kol
                           from (
                                  Select LpuRegion_id as Lpu_id,
                                         count(8) as kol
                                  from (
                                         Select LpuRegion_id,
                                                Person_id
                                         from (
                                                SElect case
                                                         when Lpu_id <> lpu_id_attach then - 1
                                                         else COALESCE(LpuRegion_id, - 1)
                                                       end LpuRegion_id,
                                                       Person_id
                                                from vac.v_JournalAccount a
                                                     --where  lpu_id  = @Lpu_id
                    --                                 {$filter}
                                              ) t0
                                         group by LpuRegion_id,
                                                  Person_id
                                       ) t
                                  group by LpuRegion_id
                                ) a
                         ) a on a.Lpu_id = t.Lpu_id
                         -- end from         
                    order by
                             -- order by  
                             LpuRegion_Name -- end order by
                    ";
			} else {
				$sql = "
                    Select
                           -- select    
                           t.Lpu_id as \"Lpu_id\",
                           LpuRegion_Name as \"LpuRegion_Name\",
                           LpuBuilding_name as \"UAddress_Address\"
                           -- end select     
                    from
                         -- from
                         (
                           SELECT LpuRegion_id Lpu_id,
                                  LpuRegionType_Name || ' №' || LpuRegion_Name LpuRegion_Name,
                                  LpuRegionType_Name || ' №' || LpuRegion_Name Lpu_Name,
                                  LpuBuilding_name
                           FROM vac.v_Region2Section
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
                                t.Lpu_id as \"Lpu_id\", 
                                l.Lpu_Nick as \"Lpu_Nick\",  
                                l.Lpu_Name as \"Lpu_Name\", 
                                UAddress_Address as \"UAddress_Address\", 
                                t2.kol0 as \"kol0\",
                                COALESCE(t.kol, 0) as \"kol\"

                                -- end select   
                            from 
                               -- from
                               vac.fn_GetLpu4Report (null, {$is_ufa}) l
                                    left join (SElect lpu_id,
                                                      COUNT(8) kol
                                               from (
                                                      Select a.Lpu_id,
                                                             Person_id
                                                      from vac.v_JournalAccount a,
                                                           vac.fn_GetLpu4Report(null, {$is_ufa}) l
                                                      where a.Lpu_id = l.lpu_id
                                                      group by a.Lpu_id,
                                                               Person_id
                                                    ) t
                                               group by lpu_id) t on  l.Lpu_id = t.Lpu_id
                                     left join    (Select l.Lpu_id,
                                                          COUNT(8) kol0
                                                   from vac.v_vac_Person pers,
                                                        vac.fn_GetLpu4Report(null, {$is_ufa}) l
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
                        lpu_id as \"lpu_id\", 
                        Lpu_Nick as \"Lpu_Nick\", 
                        Lpu_Name as \"Lpu_Name\", 
                        UAddress_Address  as \"UAddress_Address\"
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
		//                              FROM vac.v_Region2Section 

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
			Select vac.Date_Add(:Type, :Add_Num, :BaseDate)::date as \"Result_Date\"
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
        $tempIn = [];
		if (isset($data['Filter'])) {
			log_message('debug', '$json=' . $json);
			log_message('debug', '$filter_mode=' . $json);


			foreach ($filter_mode as $col => $val) {

				if (is_array($val)) {
                    foreach ($val as $v) {
                        $tempIn[] = "'" . $v . "'";
                    }
                }

				if (!empty($tempIn)) {
                    $temp = implode(',', $tempIn);

                    //$whereMore[] = ' ['.$this->getNameColumn($col).'] in('.iconv('utf-8','windows-1251',$temp).')';
                    $whereMore[] = ' ' . $this->getNameColumn($col) . ' in(' . $temp . ')';
                }
			}

			$where = (isset($whereMore)) ? ' and ' . implode(' and ', $whereMore) : $where;

		}

		if (isset($lpu_id)) {
			$filter = " Where Lpu_id = " . $lpu_id . $where;

		};

		$sql = "
                           SELECT  VacPresence_id as \"VacPresence_id\"
                                ,Vaccine_id as \"Vaccine_id\"
                                ,Vaccine_Name as \"Vaccine_Name\"
                                ,VacPresence_Seria as \"Seria\"
                                ,to_char(VacPresence_Period, 'DD.MM.YYYY') as \"Period\"

                                ,VacPresence_Manufacturer as \"Manufacturer\"
                                ,VacPresence_toHave as \"toHave\"
                                ,VacPresence_NameToHave as \"Name_toHave\"
                                ,lpu_id
                            FROM vac.v_VacPresence  

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
         SELECT VaccineType_id as \"VaccineType_id\",
                VaccineType_Name as \"VaccineType_Name\"
         FROM vac.S_VaccineType priv
         where (priv.VaccineType_SignEmergency = 1 or
               priv.VaccineType_SignEpidem = 1)
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
        SELECT TubDiagnosisType_id as \"TubDiagnosisType_id\",
               TubDiagnosisType_Name as \"TubDiagnosisType_Name\"
        FROM vac.S_TubDiagnosisType
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
					$joinMore[] = ' ' . $this->getNameColumn($col) . ' in(' . $temp . ')';
				else
					$whereMore[] = ' ' . $this->getNameColumn($col) . ' in(' . $temp . ')';
			}

			$where = (isset($whereMore)) ? ' where ' . implode(' and ', $whereMore) : $where;

			if (isset($joinMore)) {
				$join = "
					join	vac.S_VaccineRelType rel    on vac.Vaccine_id = rel.Vaccine_id

					join vac.S_VaccineType tp   on rel.VaccineType_id = tp.VaccineType_id and " . implode(' and ', $joinMore);


			}
		}

		$sql = "
			SELECT vac.Vaccine_id as \"Vaccine_id\"
                              ,Vaccine_Name as \"Vaccine_Name\"
                              ,Vaccine_SignComb as \"Vaccine_SignComb\"
                              ,Vaccine_Nick as \"Vaccine_Nick\"
                              ,Vaccine_FullName as \"Vaccine_FullName\"
                              ,Vaccine_NameInfection as \"Vaccine_NameInfection\"
                              ,Vaccine_AgeRange2Sim as \"Vaccine_AgeRange2Sim\"
                              ,Vaccine_WayPlace as \"Vaccine_WayPlace\"
                              ,Vaccine_dose as \"Vaccine_dose\"
                       FROM vac.v_Vaccine vac 

                          {$join}
                              {$where}
                            order by Vaccine_FullName ;
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
        SElect OtherVacAgeBorders_id as \"OtherVacAgeBorders_id\",
               Vaccine_id as \"Vaccine_id\",
               Sort_id as \"Sort_id\",
               Vaccine_AgeRange2Sim as \"Vaccine_AgeRange2Sim\",
               OtherVacAgeBorders_4GroupRisk as \"OtherVacAgeBorders_4GroupRisk\",
               GroupRisk_Name as \"GroupRisk_Name\",
               multiplicity_Name as \"multiplicity_Name\",
               interval as \"interval\"
        from vac.v_OtherVacScheme v
        where 
        Vaccine_id = : Vaccine_id and
              COALESCE(StatusType_id, 0) = 0
        order by sort_id,
                 OtherVacAgeBorders_4GroupRisk
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
            SELECT VaccineType_id as \"VaccineType_id\",
                   VaccineType_Name as \"VaccineType_Name\",
                   VaccineType_NameIm as \"VaccineType_NameIm\"
                   --,VaccineType_SignNatCal
        ,
                   case
                     when COALESCE(VaccineType_SignNatCal, 2) = 1 then 'true'
                     else 'false'
                   end as \"VaccineType_SignNatCal\",
                   VaccineType_SignNatCalName as \"VaccineType_SignNatCalName\"
                   --,VaccineType_SignScheme
        ,
                   case
                     when COALESCE(VaccineType_SignScheme, 2) = 1 then 'true'
                     else 'false'
                   end as \"VaccineType_SignScheme\",
                   VaccineType_SignSchemeName as \"VaccineType_SignSchemeName\"
                   --,VaccineType_SignEmergency
        ,
                   case
                     when COALESCE(VaccineType_SignEmergency, 2) = 1 then 'true'
                     else 'false'
                   end as VaccineType_SignEmergency,
                   VaccineType_SignEmergencyName as \"VaccineType_SignEmergencyName\"
                   --,VaccineType_SignEpidem
        ,
                   case
                     when COALESCE(VaccineType_SignEpidem, 2) = 1 then 'true'
                     else 'false'
                   end as \"VaccineType_SignEpidem\",
                   VaccineType_SignEmergencyName as \"VaccineType_SignEmergencyName\",
                   VaccineType_SignEpidemName as \"VaccineType_SignEpidemName\"
            FROM vac.v_VaccineType4interface
            order by \"VaccineType_SignNatCal\" desc,
                     \"VaccineType_Name\"
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
     * @param $data
     * @return array
     */
    public function saveVaccinationNotice($data)
    {
        $data['NotifyReaction_id'] = (!empty($data['NotifyReaction_id'])) ? $data['NotifyReaction_id'] : null;
        if (empty($data['NotifyReaction_id'])) {
            $procedure = "vac.p_NotifyReaction_ins";
        } else {
            $procedure = "vac.p_NotifyReaction_upd";
        }
        
        $query = "
            select 
                NotifyReaction_id as \"NotifyReaction_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\";
			from {$procedure}
			(
				:NotifyReaction_id := :NotifyReaction_id,
				:vacJournalAccount_id := :vacJournalAccount_id,
				:MedPersonal_id := :MedPersonal_id,
				:NotifyReaction_createDate := :NotifyReaction_createDate,
				:NotifyReaction_confirmDate := :NotifyReaction_confirmDate,
				:NotifyReaction_Descr := :NotifyReaction_Descr,
				:pmUser_id := :pmUser_id
            )
		";
        
        $result = $this->db->query($query, $data);
        
        if (!is_object($result)) {
            return [['Error_Msg' => 'Ошибка при сохранении извещения']];
        }

        return $result->result('array');
    }

    /**
     * Проф прививки. Получить извещения
     * @param $data
     * @return array|bool
     */
    public function loadVaccinationNotice($data)
    {
        $result = [];
        
        if (empty($data['vacJournalAccount_id']) && empty($data['NotifyReaction_id'])) {
            return $result;
        }
        
        $where = '';
        if (!empty($data['vacJournalAccount_id'])) {
            $where .= ' and FromTable.vacJournalAccount_id = :vacJournalAccount_id';
        }
        if (!empty($data['NotifyReaction_id'])) {
            $where .= ' and NR.NotifyReaction_id = :NotifyReaction_id';
        }
        $query = "
			SELECT
				FromTable.vacJournalAccount_id as \"vacJournalAccount_id\",
				to_char(FromTable.vacJournalAccount_DateVac, 'dd.mm.yyyy') as \"Date_Vac\",
				FromTable.Vaccine_name as \"vac_name\",
				FromTable.vacJournalAccount_Seria as \"Seria\",
				FromTable.vacJournalAccount_Dose as \"VACCINE_DOZA\",
				case 
					when p.VaccinePlace_Name is null and wp.VaccinePlace_Name is null and  FromTable.VaccinePlace_id is not null then
						wp.VaccineWay_Name
					when p.VaccinePlace_Name is null and FromTable.VaccinePlace_id is not null then 
						wp.VaccinePlace_Name||': '||wp.VaccineWay_Name 
					else p.VaccinePlace_Name||': '||w.VaccineWay_Name 
				end as \"WAY_PLACE\",
				L2.Lpu_Nick as \"Lpu_Nick\",
				coalesce (to_char(NR.NotifyReaction_confirmDate, 'dd.mm.yyyy'), '') as \"NotifyReaction_confirmDate\",
				coalesce (to_char(NR.NotifyReaction_createDate, 'dd.mm.yyyy'), '') as \"NotifyReaction_createDate\",
				FromTable.vacJournalAccount_Vac_MedPersonal_id as \"MedPersonal_id\",
				MP.MedPersonal_Name as \"MedPersonal_Name\",
				NR.NotifyReaction_Descr as \"NotifyReaction_Descr\"
			FROM  
				vac.v_JournalAccountAll FromTable
				left join v_Lpu L2 on L2.Lpu_id = FromTable.Lpu_id
				left join vac.S_VaccineWay w on FromTable.VaccineWay_id = w.VaccineWay_id
				left join vac.v_VaccinePlace p  on FromTable.VaccinePlace_id = p.VaccinePlace_id
				cross join lateral (
					SELECT
						p2.VaccinePlace_Name, w2.VaccineWay_Name
					FROM
					    vac.v_VaccineWayPlace wp2
						left join vac.v_VaccinePlace p2 on wp2.VaccinePlace_id = p2.VaccinePlace_id
						left join vac.S_VaccineWay w2 on wp2.VaccineWay_id = w2.VaccineWay_id
					WHERE
					    FromTable.VaccinePlace_id = wp2.VaccineWayPlace_id
					limit 1
				) wp on true
				left join vac.v_NotifyReaction NR on NR.vacJournalAccount_id = FromTable.vacJournalAccount_id
				left join vac.v_VacMedPersonal MP on MP.MedPersonal_id = FromTable.vacJournalAccount_Vac_MedPersonal_id
			WHERE 1=1
				{$where}
            limit 1
		";
        //echo getDebugSQL($query, $data); die();
        $result = $this->db->query($query, $data);

        if (! is_object($result) ) {
            return false;
        }
        
        return $result->result('array');
    }

    /**
     * Проф прививки. Удалить извещения
     * @param $data
     * @return bool
     */
    public function deleteVaccinationNotice($data)
    {
        $query = "
            select 
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from vac.p_NotifyReaction_del
			(
				NotifyReaction_id := :NotifyReaction_id
			)
		";

        $result = $this->db->query($query, $data);
        if ( !is_object($result) ) {
            return false;
        }

        return $result->result('array');
    }

    /**
     * Получить извещения проф прививок
     * @param $data
     * @return mixed
     */
    function loadGridVaccinationNotice($data)
    {
        $filter = '';
        if (!empty($data['Person_SurName'])) {
            $data['Person_SurName'] = rtrim($data['Person_SurName']);
            $filter .= ' and FromTable.SurName ilike :Person_SurName||\'%\'';
        }

        if (!empty($data['Person_FirName'])) {
            $data['Person_FirName'] = rtrim($data['Person_FirName']);
            $filter .= ' and FromTable.FirName ilike :Person_FirName||\'%\'';
        }

        if (!empty($data['Person_SecName'])) {
            $data['Person_SecName'] = rtrim($data['Person_SecName']);
            $filter .= ' and FromTable.SecName ilike :Person_SecName||\'%\'';
        }

        if (!empty($data['Lpu_id']))
            $filter .= ' and FromTable.Lpu_id = :Lpu_id';

        if (!empty($data['vac_name'])) {
            $filter .= ' and FromTable.Vaccine_name ilike :vac_name||\'%\'';
        }

        if (!empty($data['Seria'])) {
            $filter .= ' and FromTable.vacJournalAccount_Seria ilike :Seria||\'%\'';
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
			SELECT
			-- select
				NR.NotifyReaction_id,
				to_char(FromTable.vacJournalAccount_DateVac, 'dd.mm.yyyy') AS NotifyReaction_confirmDate, --Дата исполнения
				to_char(NR.NotifyReaction_createDate, 'dd.mm.yyyy') AS NotifyReaction_createDate,
				FromTable.SurName as \"Person_SurName\",
				FromTable.FirName as \"Person_FirName\",
				FromTable.SecName as \"Person_SecName\",
				FromTable.Vaccine_name as  \"vac_name\",
				FromTable.vacJournalAccount_Seria as \"Seria\",
				FromTable.vacJournalAccount_Dose as \"VACCINE_DOZA\",
				MP.MedPersonal_Name as \"Executed_MedPersonal_Name\", --Исполнивший врач
				MP2.MedPersonal_Name as \"CreateNotification_MedPersonal_Name\" --Врач, создавший извещение
			-- end select
			FROM
			-- from
				vac.v_NotifyReaction NR
				LEFT JOIN vac.v_JournalAccountAll FromTable on NR.vacJournalAccount_id = FromTable.vacJournalAccount_id
				LEFT JOIN vac.v_VacMedPersonal MP on MP.MedPersonal_id = FromTable.vacJournalAccount_Vac_MedPersonal_id
				LEFT JOIN vac.v_VacMedPersonal MP2 on MP2.MedPersonal_id = NR.MedPersonal_id
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
			limit 100
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
				RTRIM(coalesce(DrugPrep.DrugPrep_Name, '')) as \"DrugPrep_Name\",
				DrugPrep.DrugPrep_id as \"DrugPrep_id\",
				DrugPrep.DrugPrepFas_id as \"DrugPrepFas_id\",
				ED.EvnDrug_KolvoEd as \"EvnDrug_KolvoEd\", --Кол-во единиц списания
				GU.GoodsUnit_Nick as \"GoodsUnit_Nick\", --ед. списания
				DuS.DocumentUcStr_id as \"DocumentUcStr_id\",
				ED.EvnDrug_id as \"EvnDrug_id\",
				Dus.DocumentUcStr_Ser as \"DocumentUcStr_Ser\",
				PS.PrepSeries_Ser as \"PrepSeries_Ser\",
				cast(cast(ED.EvnDrug_KolvoEd as numeric) as varchar(20)) || ' ' || GU.GoodsUnit_Nick AS \"Doza\"
			FROM v_DocumentUcStr DuS
				INNER JOIN v_EvnDrug ED ON ED.DocumentUcStr_id = DuS.DocumentUcStr_id
				INNER JOIN rls.v_Drug Drug on Drug.Drug_id = ED.Drug_id
				INNER JOIN rls.v_DrugPrep DrugPrep ON Drug.DrugPrepFas_id = DrugPrep.DrugPrepFas_id
				LEFT JOIN v_GoodsUnit GU ON GU.GoodsUnit_id = DuS.GoodsUnit_id
				LEFT JOIN rls.v_PrepSeries PS ON PS.PrepSeries_id = DuS.PrepSeries_id
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
			update vac.vac_JournalAccount set DocumentUcStr_id = :DocumentUcStr_id where vacJournalAccount_id = :vacJournalAccount_id
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
            SELECT vacJournalAccount_id as \"vacJournalAccount_id\"
			FROM vac.vac_JournalAccount  VJA
				INNER JOIN DocumentUcStr DUS ON DUS.DocumentUcStr_id = VJA.DocumentUcStr_id
			WHERE DUS.EvnDrug_id = :EvnDrug_id AND VJA.DocumentUcStr_id = :DocumentUcStr_id
			ORDER BY \"vacJournalAccount_id\" DESC
			LIMIT 1
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
				PPF.Person_id as \"Person_id\",
				PPF.vac_PersonPlanFinal_id AS \"plan_id\",
				'1' AS \"row_plan_parent\",
				PPF.NationalCalendarVac_Scheme_Num AS \"scheme_num\"
			FROM vac.v_PersonPlanFinal PPF
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
				--COUNT(ac.vacJournalAccount_id) AS \"cnt\"
				v.Vaccine_Name as \"Vaccine_Name\",
				CASE 
					WHEN ac.vacJournalAccount_StatusType_id = 0 THEN 'Назначено'
					WHEN ac.vacJournalAccount_StatusType_id = 1 THEN 'Исполнено'
					ELSE ''
				END as \"StatusType_Name\",	--статус
				ac.MedService_id as \"MedService_id\",
				MS.MedService_Nick as \"MedService_Nick\",		--служба
				to_char(ac.vacJournalAccount_DatePurpose, 108) as \"Date_Purpose\",  --Дата исполнения
				ac.*
			from vac.vac_JournalAccount ac
				left join  vac.S_Vaccine v on  v.Vaccine_id = ac.vaccine_id
				LEFT JOIN dbo.v_MedService MS ON MS.MedService_id = ac.MedService_id
				--left OUTER JOIN vac.v_vac_Person pers ON pers.Person_id = ac.Person_id
				--left outer join vac.S_VaccineWay way on way.VaccineWay_id = ac.VaccineWay_id
				--left outer join vac.v_VaccinePlace pl on pl.VaccinePlace_id = ac.VaccinePlace_id
				--left outer join dbo.v_Lpu lpu on lpu.Lpu_id = ac.vacJournalAccount_Lpu_id
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
		$filter = " and LB.lpu_id = :lpu_id";

		$query = "
			SELECT DISTINCT
				medservice_id as \"MedService_id\",
				medservice_nick as \"MedService_Nick\",
				lpu_id as \"Lpu_id\",
				lpubuilding_id as \"LpuBuilding_id\",
				lpuunit_id as \"LpuUnit_id\",
				lpuunittype_id as \"LpuUnitType_id\",
				lpusection_id as \"LpuSection_id\" from (
									select   ms.lpu_id, ms.lpubuilding_id, ms.medservice_id,  ms.medservice_nick,
											ms.lpuunit_id, ms.lpusection_id, ms.lpuunittype_id
									from dbo.v_MedService ms 											
										left join dbo.v_LpuBuilding lb on ms.lpubuilding_id=lb.lpubuilding_id
										left join dbo.v_Lpu l on ms.lpu_id=l.lpu_id
									where ms.MedServiceType_id = 31
									and ms.MedService_begDT <= GETDATE()
									and (ms.MedService_endDT is null or ms.MedService_endDT >= GETDATE() )
									and (ms.MedService_endDT is null or ms.MedService_endDT >= GETDATE() )
									" . $filter . "  
								) t
		";
		$queryParams = array(
			'lpu_id' => $data['Lpu_id']
		);

		// echo getDebugSql($query, $queryParams);die();
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (Список ЛПУ)'));
		}
	}

	/**
	 * getVaccinesDosesVaccine_List - список доступных вакцин
	 */
	public function getVaccinesDosesVaccine_List ($data) {
		if(empty($data['Org_id'])) return false;
		if(empty($data['Person_id'])) return false;
		if(empty($data['MedService_id'])) return false;

		$filter = '';
		$join = '';

		// сортировка по нац календарю
		if(!empty($data['NatCalendar'])){
			$filter .= ' and VAC.vaccination_isnaccal = 2';
		}

		// сортировка по Эпидпоказаниям
		if(!empty($data['Vaccination_isEpidemic'])){
			$filter .= ' and VAC.Vaccination_isEpidemic = 2';
		}
		
		// обязательные параметры
		$filter .= ' and FTG.ftggrls_id = 380'; // вхождение в группу МИБП-вакцина!
		$filter .= ' and VTP.vaccinationtypeprep_enddate is null'; // Препарат не имеет записи об окончании
		$filter .= ' and PDR.persondrugreaction_id is null'; // отсутствуют реакции и поствакцинальные осложнения пациента
		$filter .= ' and DROST.drugostatregistry_kolvo is not null'; // есть на остатках (есть запись об остатках)


		$query = "
			SELECT DISTINCT
			-- select
                VTP.prep_id as \"Prep_id\"
                  -- торговое наименование
                  , TN.name as \"TN_NAME\"
                  , TN.tradenames_id as \"TRADENAMES_ID\"
                  -- end торговое наименование
                  -- остатки
                  , DROST.drugostatregistry_kolvo as \"DrugOstatRegistry_Kolvo\"
                  , DROST.storage_id as \"Storage_id\"
                  -- end остатки
                  -- ответственные лица склада
                  , MOL.mol_id as \"Mol_id\"
                  -- end ответственные лица склада
                  -- группировочные торговые наименования
                  , DR.drugprepfas_id as \"DrugPrepFas_id\"
                  -- end группировочные торговые наименования
              -- end select

			FROM vc.v_VaccinationTypePrep VTP
			-- from
				-- из ФТГ ГРЛС
					LEFT JOIN rls.v_PREP_FTGGRLS FTG ON FTG.prep_id = VTP.prep_id
				-- end из ФТГ ГРЛС
				-- торговое наименование
					LEFT JOIN rls.v_PREP PR ON PR.prep_id = VTP.prep_id
					LEFT JOIN rls.v_TRADENAMES TN ON TN.tradenames_id = PR.tradenameid
				-- end торговое наименование
				-- группировочные торговые наименования
					LEFT JOIN rls.v_Drug DR ON DR.drug_id = VTP.prep_id
				-- end группировочные торговые наименования
				-- остатки
					-- склад
						LEFT JOIN dbo.v_StorageStructLevel SSL ON SSL.medservice_id = :MedService_id
						LEFT JOIN dbo.v_DrugOstatRegistry DROST ON
							DROST.drug_id = VTP.prep_id
							AND DROST.subaccounttype_id = 1
							AND DROST.storage_id = SSL.storage_id
					-- end склад
					-- ответственные лица склада
						LEFT JOIN dbo.v_Mol MOL ON (MOL.storage_id = SSL.storage_id AND MOL.medpersonal_id = :MedPersonal_id)
					-- end ответственные лица склада
				-- end остатки
				-- отношения препарата к вакцинациям
					LEFT JOIN vc.v_Vaccination VAC ON VAC.vaccinationtype_id = VTP.vaccinationtype_id
				-- end отношения препарата к вакцинациям
				-- реакции и поствакцинальные осложнения пациента
					LEFT JOIN vc.PersonDrugReaction PDR ON (PDR.tradenames_id = PR.tradenameid AND PDR.person_id = :Person_id)
				-- end реакции и поствакцинальные осложнения пациента
			-- end from

			WHERE
			-- where
				1=1
				{$filter}
			-- end where
			GROUP BY
				VTP.prep_id
				, TN.name
				, TN.tradenames_id
				, DROST.storage_id
				, DR.drugprepfas_id
				, drugostatregistry_kolvo
				, mol_id
		";

		// echo getDebugSql($query, $data);die();
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
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
		$filter =" and VTP.prep_id = :Prep_ID";
		$query = "
		SELECT DISTINCT
		--select
			-- типы вакцинаций с данным препаратом
				VTP.vaccinationtype_id as \"VaccinationType_id\"
				, VTP.prep_id as \"Prep_id\"
				-- код вакцинации
				, VAC.vaccination_code as \"Vaccination_Code\"
				-- end код вакцинации
				-- id вакцинации
				, VAC.vaccination_id as \"Vaccination_id\"
				-- end id вакцинации
				-- название вакцинации
				, VAC.vaccination_name as \"Vaccination_Name\"
				-- end название вакцинации
			-- end типы вакцинаций с данным препаратом
			-- данные по вакцинациям
				-- дата прошлой вакцинации по данной вакцинации
					, CAST((
						SELECT DISTINCT
						EVV.evnvaccination_setdate
						from vc.v_VaccinationData VD
							LEFT JOIN dbo.v_EvnVaccination EVV ON EVV.person_id = :Person_id and EVV.evnvaccination_id = VD.evnvaccination_id
						where VD.vaccination_id = vaccination_id
						ORDER BY EVV.evnvaccination_setdate DESC
						limit 1
					) AS DATE ) as \"Vaccination_last_event_date\"
				-- end дата прошлой вакцинации по данной вакцинации
				-- id прошлой вакцинации по схеме
					, VAC.Vaccination_pid as \"Vaccination_pid\"
				-- end id прошлой вакцинации по схеме
				-- дата прошлой вакцинации по схеме
					, CAST((
						SELECT  DISTINCT
							EVV.evnvaccination_setdate
							from vc.v_VaccinationData VD
								LEFT JOIN dbo.v_EvnVaccination EVV ON EVV.person_id = :Person_id and EVV.evnvaccination_id = VD.evnvaccination_id
							where VD.vaccination_id = vaccination_pid
							ORDER BY EVV.evnvaccination_setdate DESC
							limit 1
					) AS DATE ) as \"Vaccination_pid_event_date\"
				-- end дата прошлой вакцинации по схеме
				-- минимальные сроки вакцинации
					, CAST((CASE VAC.vaccinationagetype_bid
							WHEN '1' THEN getDate()::TIMESTAMP - (VAC.vaccination_begage * INTERVAL '1 day')
							WHEN '2' THEN getDate()::TIMESTAMP - (VAC.vaccination_begage * INTERVAL '1 month')
							WHEN '3' THEN getDate()::TIMESTAMP - (VAC.vaccination_begage * INTERVAL '1 year')
					END ) AS DATE ) as \"Vaccine_MinAge\"
				-- end минимальные сроки вакцинации
				-- максимальный возраст для вакцинации
					,CAST((CASE VAC.vaccinationagetype_eid
						WHEN '1' THEN getDate()::TIMESTAMP - (VAC.vaccination_endage * INTERVAL '1 day')
						WHEN '2' THEN getDate()::TIMESTAMP - (VAC.vaccination_endage * INTERVAL '1 month')
						WHEN '3' THEN getDate()::TIMESTAMP - (VAC.vaccination_endage * INTERVAL '1 year')
					END) AS DATE ) as \"Vaccine_MaxAge\"
				-- end максимальный возраст для вакцинации
			-- end данные по вакцинациям
			-- отводы пациента
				, PersVacREF.personvaccinationrefuse_id as \"PersonVaccinationRefuse_id\"
				, CAST(PersVacREF.personvaccinationrefuse_enddt AS DATE) as \"PersonVaccinationRefuse_endDT\"
			-- end отводы пациента
			-- дата рождения пацинета
				, CAST(PersBD.personbirthday_birthday AS DATE) as \"PersonBirthDay_BirthDay\"
			-- end дата рождения пациента
			-- группы риска пациента
				, (SELECT VRGL.vaccinationriskgrouplink_insdt
					from vc.v_VaccinationRiskGroupLink VRGL
					LEFT JOIN vc.v_PersonVaccinationRiskGroup PersVRG ON PersVRG.person_id = :Person_id
					where
					VRGL.vaccinationriskgroup_id = PersVRG.vaccinationriskgroup_id
					and VRGL.vaccinationtype_id = VTP.vaccinationtype_id
					) as \"PersonVaccinationRiskGroup_insDT\"
			-- end группы риска пациента
		-- end select
		FROM
			-- from
			-- типы вакцинаций с данным препаратом
			vc.v_VaccinationTypePrep VTP 
			-- end типы вакцинаций с данным препаратом
			-- данные по пациенту
				LEFT JOIN dbo.v_PersonBirthDay PersBD ON PersBD.person_id = :Person_id
						-- end данные по пациенту
						-- данные по вакцинациям
							LEFT JOIN vc.v_Vaccination VAC ON VAC.vaccinationtype_id = VTP.vaccinationtype_id
						-- end данные по вакцинациям
						-- отводы пациента
							LEFT JOIN vc.v_PersonVaccinationRefuse PersVacREF ON PersVacREF.person_id = :Person_id and PersVacREF.vaccinationtype_id = VAC.vaccinationtype_id
						-- end отводы пациента
					-- end from
		WHERE
			-- where
			1=1
			{$filter}
			and VTP.vaccinationtype_id is not null
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