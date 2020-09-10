<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Markoff Andrew
* @version      27.09.2009
*/

class DrugRequest_model extends swPgModel
{

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Проверка
	 */
	function checkDrugRequestLimitExceed($data, $options) {
		$queryParams = array(
			'PersonRegisterType_id' => $data['PersonRegisterType_id'],
			'DrugRequestPeriod_id' => $data['DrugRequestPeriod_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Fed_Koef' => isset($options['koef_fed_lgot']) ? $options['koef_fed_lgot'] : 1,
			'Fed_Normativ' => isset($options['normativ_fed_lgot']) ? $options['normativ_fed_lgot'] : 0,
			'Reg_Koef' => isset($options['koef_reg_lgot']) ? $options['koef_reg_lgot'] : 1,
			'Reg_Normativ' => isset($options['normativ_reg_lgot']) ? $options['normativ_reg_lgot'] : 0
		);

		//если тип регистра и период известен, ищем данные о лимитах в таблице DrugRequestQuota
		if($queryParams['PersonRegisterType_id'] > 0 && $queryParams['DrugRequestPeriod_id']) {
			$query = "
				select (
				 select DrugRequestQuota_Person
				 from DrugRequestQuota
				 where PersonRegisterType_id = :PersonRegisterType_id 
				 and
					   DrugRequestPeriod_id = :DrugRequestPeriod_id and
					   DrugFinance_id =
					   (
						 select DrugFinance_id
						 from DrugFinance
						 where DrugFinance_SysNick = 'fed'
						 limit 1
					   )
				 limit 1
			   ) as \"Fed_Normativ\",
			   (
				 select DrugRequestQuota_Person
				 from DrugRequestQuota
				 where PersonRegisterType_id = :PersonRegisterType_id and
					   DrugRequestPeriod_id = :DrugRequestPeriod_id and
					   DrugFinance_id =
					   (
						 select DrugFinance_id
						 from DrugFinance
						 where DrugFinance_SysNick = 'reg'
						 limit 1
					   )
				 limit 1
			   ) as \"Reg_Normativ\"
			";
			$result = $this->db->query($query, $queryParams);
			if (is_object($result)) {
				$res = $result->result('array');
				if (is_array($res) && count($res) > 0) {
					$queryParams['Fed_Normativ'] = $res[0]['Fed_Normativ'];
					$queryParams['Reg_Normativ'] = $res[0]['Reg_Normativ'];
				}
			}
		}

		$query = "
			select SUM(case
                         when DRR.DrugRequestType_id = 1 then DRR.DrugRequestRow_Summa
                         else null
                       end) - Fed.FedLgot * :Fed_Normativ * 3 * :Fed_Koef as \"FedLgotExceed_Sum\",
                   SUM(case
                         when DRR.DrugRequestType_id = 2 then DRR.DrugRequestRow_Summa
                         else null
                       end) - Reg.RegLgot * :Reg_Normativ * 3 * :Reg_Koef as \"RegLgotExceed_Sum\"
            from DrugRequestRow DRR
                 inner join DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id and DR.Lpu_id = :Lpu_id
                 inner join DrugRequestPeriod DRP on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id and DRP.DrugRequestPeriod_id = :DrugRequestPeriod_id
                 LEFT JOIN LATERAL
                 (
                   select COUNT(DISTINCT PC.Person_id) as FedLgot
                   from v_PersonCard_all PC
                        inner join PersonPrivilege PP on PP.Person_id = PC.Person_id
                        inner join PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id and PT.ReceptFinance_id = 1 and isnumeric(PT.PrivilegeType_Code) = 1
                        left join v_PersonRefuse PR on PR.Person_id = PP.Person_id and PR.PersonRefuse_Year = date_part('YEAR', DRP.DrugRequestPeriod_begDate) and PR.PersonRefuse_IsRefuse = 2
                   where (1 = 1) and
                         PC.Lpu_id = :Lpu_id and
                         PC.PersonCard_begDate <= DRP.DrugRequestPeriod_begDate and
                         (PC.PersonCard_endDate is null or
                         PC.PersonCard_endDate > DRP.DrugRequestPeriod_begDate) and
                         PP.PersonPrivilege_begDate <= dbo.tzGetDate() and
                         (PP.PersonPrivilege_endDate is null or
                         PP.PersonPrivilege_endDate >= dbo.tzGetDate()) and
                         COALESCE(PR.PersonRefuse_IsRefuse, 1) = 1
                 ) Fed ON true
                 LEFT JOIN LATERAL
                 (
                   select COUNT(DISTINCT PC.Person_id) as RegLgot
                   from v_PersonCard_all PC
                        inner join PersonPrivilege PP on PP.Person_id = PC.Person_id
                        inner join PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id and PT.ReceptFinance_id = 2 and isnumeric(PT.PrivilegeType_Code) = 1
                        LEFT JOIN LATERAL
                        (
                          select 1 as IsFedLgot
                          from v_PersonPrivilege PP
                               inner join PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id and PT.ReceptFinance_id = 1 and isnumeric(PT.PrivilegeType_Code) = 1
                          where PP.Person_id = PC.Person_id and
                                PP.PersonPrivilege_begDate <= dbo.tzGetDate() and
                                (PP.PersonPrivilege_endDate is null or
                                PP.PersonPrivilege_endDate >= dbo.tzGetDate())
                          limit 1
                        ) FedLgot ON true
                   where (1 = 1) and
                         PC.Lpu_id = :Lpu_id and
                         PC.PersonCard_begDate <= DRP.DrugRequestPeriod_begDate and
                         (PC.PersonCard_endDate is null or
                         PC.PersonCard_endDate > DRP.DrugRequestPeriod_begDate) and
                         PP.PersonPrivilege_begDate <= dbo.tzGetDate() and
                         (PP.PersonPrivilege_endDate is null or
                         PP.PersonPrivilege_endDate >= dbo.tzGetDate()) and
                         FedLgot.IsFedLgot is null
                 ) Reg ON true
            where (1 = 1) and
                  (DRR.DrugRequestRow_delDT is null or
                  DRR.DrugRequestRow_delDT > dbo.tzGetDate()) and
                  DRR.DrugRequestRow_insDT <= dbo.tzGetDate()
            group by Fed.FedLgot,
                     Reg.RegLgot
		";
		//echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');

			if ( is_array($response) ) {
				if ( count($response) > 0 ) {
					return array(
						'Error_Msg' => '',
						'FedLgotExceed_Sum' => $response[0]['FedLgotExceed_Sum'],
						'RegLgotExceed_Sum' => $response[0]['RegLgotExceed_Sum'],
						'success' => true
					);
				}
				else {
					return array(
						'Error_Msg' => '',
						'FedLgotExceed_Sum' => 0,
						'RegLgotExceed_Sum' => 0,
						'success' => true
					);
				}
			}
			else {
				return array(
					'Error_Msg' => 'Ошибка при проверке лимитов по заявке',
					'FedLgotExceed_Sum' => 0,
					'RegLgotExceed_Sum' => 0,
					'success' => false
				);
			}
		}
		else {
			return array(
				'Error_Msg' => 'Ошибка при выполнении запроса к БД (проверка лимитов по заявке)',
				'FedLgotExceed_Sum' => 0,
				'RegLgotExceed_Sum' => 0,
				'success' => false
			);
		}
	}

	/**
	 * Функция
	 */
	function getDrugRequestLast($data) {
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'MedPersonal_id' => $data['MedPersonal_id']
		);

		$query = "
			select case
					 when MP.WorkData_IsDlo is not null and MP.WorkData_IsDlo = 2 then 1
					 else 0
				   end as \"is_dlo\",
				   MP.Person_Fin as \"MedPersonal_Fin\",
				   COALESCE(DR_last.DrugRequest_id, 0) as \"last_DrugRequest_id\",
				   COALESCE(DR_last.DrugRequestPeriod_id, 0) as \"last_DrugRequestPeriod_id\",
				   COALESCE(DR_last.DrugRequestStatus_id, 0) as \"last_DrugRequestStatus_id\",
				   COALESCE(DRP_next.DrugRequestPeriod_id, 0) as \"next_DrugRequestPeriod_id\",
				   COALESCE(to_char(DRP_next.DrugRequestPeriod_begDate, 'DD.MM.YYYY'), '') || ' - ' || COALESCE(to_char(DRP_next.DrugRequestPeriod_endDate, 'DD.MM.YYYY'), '') as \"next_DrugRequestPeriod\",
				   COALESCE(DR_next.DrugRequest_id, 0) as \"next_DrugRequest_id\",
				   COALESCE(DR_next.DrugRequestStatus_id, 0) as \"next_DrugRequestStatus_id\"
			from v_MedPersonal MP
				 left join DrugRequestPeriod DRP_next on cast (DRP_next.DrugRequestPeriod_begDate as DATE) >= cast (dbo.tzGetDate() as DATE)
				 left join DrugRequest DR_next on MP.MedPersonal_id = DR_next.MedPersonal_id and (DR_next.LpuSection_id = :LpuSection_id or DR_next.LpuSection_id is null) and DR_next.DrugRequestPeriod_id = DRP_next.DrugRequestPeriod_id
				 LEFT JOIN LATERAL
				 (
				   select t1.DrugRequest_id,
						  t1.DrugRequestPeriod_id,
						  t1.DrugRequestStatus_id
				   from v_DrugRequest t1
				   where t1.MedPersonal_id = :MedPersonal_id AND
						 (t1.LpuSection_id = :LpuSection_id or
						 t1.LpuSection_id is null)
				   order by t1.DrugRequest_insDT desc,
							t1.DrugRequestPeriod_id desc
				   limit 1
				 ) as DR_last ON true
			where MP.MedPersonal_id =:MedPersonal_id AND
				  MP.Lpu_id =:Lpu_id
		";

				/*
				COALESCE(DR_cur.DrugRequest_id,0) as last_DrugRequest_id,

				COALESCE(DR_cur.DrugRequestPeriod_id,0) as last_DrugRequestPeriod_id,

				COALESCE(DR_cur.DrugRequestStatus_id,0) as last_DrugRequestStatus_id,

				--left join DrugRequestPeriod DRP_cur  on cast(DRP_cur.DrugRequestPeriod_begDate as DATE) <= cast(dbo.tzGetDate() as DATE) AND cast(DRP_cur.DrugRequestPeriod_endDate as DATE) >= cast(dbo.tzGetDate() as DATE)

				--left join DrugRequest DR_cur  on MP.MedPersonal_id = DR_cur.MedPersonal_id and DR_cur.LpuSection_id = :LpuSection_id and DR_cur.DrugRequestPeriod_id = DRP_cur.DrugRequestPeriod_id

				*/

		//echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/*
	function DrugRequestPersonGrid($data)
	{
		$this->load->helper('Options');
		$medpersonal = "(1=1)";
		if ((isset($data['session']['lpu_id'])) && ($data['session']['lpu_id']>0))
		{
			$lpu = "DRP.Lpu_id = ".$data['session']['lpu_id'];
			$Lpu_id = $data['session']['lpu_id'];
		}
		else 
			return false;
				
		if (!isMinZdrav())
		{
			if ((!isset($data['MedPersonal_id'])) || (empty($data['MedPersonal_id'])))
				return false;
			$medpersonal = "DRP.Medpersonal_id = ".$data['MedPersonal_id'];
		}
		else 
		{
			if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
			{
				$lpu = "DRP.Lpu_id = ".$data['Lpu_id'];
				$Lpu_id = $data['Lpu_id'];
				if ($data['session']['lpu_id']==$data['Lpu_id'])
					$medpersonal = "DRP.MedPersonal_id is null";
				else 
				{
					if ((!isset($data['MedPersonal_id'])) || (empty($data['MedPersonal_id'])))
						return false;
					$medpersonal = "DRP.Medpersonal_id = ".$data['MedPersonal_id'];
				}
			}
		}
		
		if ((!isset($data['DrugRequestPeriod_id'])) || (empty($data['DrugRequestPeriod_id'])))
			return false;
		
		$sql = "
		Select 
			DrugRequestPerson_id, 
			PS.Server_id,
			PS.PersonEvn_id,
			DRP.Person_id, 
			RTrim(PS.Person_SurName) as Person_SurName,
			RTrim(PS.Person_FirName) as Person_FirName,
			RTrim(PS.Person_SecName) as Person_SecName,
			RTrim(COALESCE(PS.Person_SurName,''))+' '+RTrim(COALESCE(PS.Person_FirName,''))+' '+RTrim(COALESCE(PS.Person_SecName,'')) as Person_FIO,

			to_char(PS.Person_BirthDay, 'DD.MM.YYYY') as Person_BirthDay,

			pcard.Lpu_id,
			RTrim(Lpu.Lpu_Nick) as Lpu_Nick, 
			RTrim(pcard.LpuRegion_Name) as LpuRegion_Name,
			CASE WHEN PRYear.PersonRefuse_id is not null THEN 'true' ELSE 'false' END as [Person_IsRefuse],
			CASE WHEN PRNextYear.PersonRefuse_id is not null THEN 'true' ELSE 'false' END as [Person_IsRefuseNext],
			CASE WHEN PRPeriodYear.PersonRefuse_id is not null THEN 'true' ELSE 'false' END as [Person_IsRefuseCurr],
			
			CASE WHEN fedl.Person_id is not null then 'true' else 'false' end as [Person_IsFedLgot],
			CASE WHEN fed2.Person_id is not null and PRPeriodYear.PersonRefuse_id is null then 'true' else 'false' end as [Person_IsFedLgotCurr],
			CASE WHEN regl.OwnLpu = 1 THEN 'true' ELSE CASE WHEN regl.OwnLpu is not null THEN 'gray' ELSE 'false' END END as [Person_IsRegLgot],
			
			-- региональная льгота для текущего периода заявки 
			--CASE WHEN reg2.OwnLpu = 1 THEN 'true' ELSE CASE WHEN reg2.OwnLpu is not null THEN 'gray' ELSE 'false' END END as [Person_IsRegLgotCurr],
			-- региональная льгота для текущего периода - новая по текущей дате и учитывая федералку 
			CASE WHEN reg2.OwnLpu = 1 and not (fed2.Person_id is not null and PRPeriodYear.PersonRefuse_id is null) THEN 'true' ELSE CASE WHEN reg2.OwnLpu is not null and not (fed2.Person_id is not null and PRPeriodYear.PersonRefuse_id is null) THEN 'gray' ELSE 'false' END END as [Person_IsRegLgotCurr],
			
			CASE WHEN disp.OwnLpu = 1 THEN 'true' ELSE CASE WHEN disp.OwnLpu is not null THEN 'gray' ELSE 'false' END END as [Person_Is7Noz],
			CASE WHEN ps.Server_pid = 0 THEN 'true' ELSE 'false' END as [Person_IsBDZ],
			to_char(DrugRequestPerson_insDT, 'DD.MM.YYYY') as DrugRequestPerson_insDT,

			to_char(DrugRequestPerson_updDT, 'DD.MM.YYYY') as DrugRequestPerson_updDT,

			DrrOutCount.DrugRequestRow_Count
		from DrugRequestPerson DRP 

		left join DrugRequestPeriod DRPr  on DRPr.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id 

		left join v_PersonState PS  on PS.Person_id = DRP.Person_id 

		-- это для подсветки персонов синеньким, тех персонов на которые есть позиции заявок других ЛПУ
		LEFT JOIN LATERAL (select count(*) as DrugRequestRow_Count

							from DrugRequestRow DRR 

							left join DrugRequest  on DrugRequest.DrugRequest_id = DRR.DrugRequest_id

							where DRR.Person_id = DRP.Person_id and DrugRequest.Lpu_id != DRP.Lpu_id 
							and DrugRequest.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
							) as DrrOutCount
		LEFT JOIN LATERAL (select top 1

								pc.Person_id as PersonCard_Person_id,
								pc.Lpu_id,
								pc.LpuRegion_id,
								pc.LpuRegion_Name
							from v_PersonCard pc 

							where pc.Person_id = ps.Person_id and LpuAttachType_id = 1
							order by PersonCard_begDate desc
							) as pcard
		left join v_Lpu Lpu  on pcard.Lpu_id=Lpu.Lpu_id

		left join v_LpuRegion LpuRegion  on LpuRegion.LpuRegion_id = pcard.LpuRegion_id

		
		left join v_PersonRefuse PRYear  ON PRYear.Person_id=ps.Person_id and PRYear.PersonRefuse_IsRefuse=2 and PRYear.PersonRefuse_Year = YEAR(dbo.tzGetDate())

		left join v_PersonRefuse PRNextYear  on PRNextYear.Person_id=ps.Person_id and PRNextYear.PersonRefuse_IsRefuse=2 and PRNextYear.PersonRefuse_Year = (YEAR(dbo.tzGetDate())+1)

		left join v_PersonRefuse PRPeriodYear  on PRPeriodYear.Person_id=ps.Person_id and PRPeriodYear.PersonRefuse_IsRefuse=2 and PRPeriodYear.PersonRefuse_Year = YEAR(DRPr.DrugRequestPeriod_begDate)

		
			LEFT JOIN LATERAL (

					select top 1 Person_id
					from v_personprivilege reg 

					where
					reg.person_id = ps.person_id
					and reg.privilegetype_id <= 249
					and reg.personprivilege_begdate <= dbo.tzGetDate()
					and (reg.personprivilege_enddate is null or reg.personprivilege_enddate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime))
				) as fedl
			LEFT JOIN LATERAL (

					select top 1 Person_id
					from v_personprivilege reg 

					where
					reg.person_id = ps.person_id
					and reg.privilegetype_id <= 249
					-- and reg.personprivilege_begdate <= DRPr.DrugRequestPeriod_endDate
					and reg.personprivilege_begdate <= dbo.tzGetDate()
					-- на дату заявки 
					--and (reg.personprivilege_enddate is null or reg.personprivilege_enddate >= DRPr.DrugRequestPeriod_begDate)
					-- на текущую дату 
					and (reg.personprivilege_enddate is null or reg.personprivilege_enddate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime))
				) as fed2
				LEFT JOIN LATERAL (

					select max(case when Lpu_id = " . $Lpu_id . " then 1 else 0 end) as OwnLpu
					from v_personprivilege reg 

					where
					reg.person_id = ps.person_id
					and reg.privilegetype_id > 249
					and reg.personprivilege_begdate <= dbo.tzGetDate()
					and (reg.personprivilege_enddate is null or reg.personprivilege_enddate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime))
				) as regl
				LEFT JOIN LATERAL (

					select max(case when Lpu_id = " . $Lpu_id . " then 1 else 0 end) as OwnLpu
					from v_personprivilege reg 

					where
					reg.person_id = ps.person_id
					and reg.privilegetype_id > 249
					-- and reg.personprivilege_begdate <= DRPr.DrugRequestPeriod_endDate
					and reg.personprivilege_begdate <= dbo.tzGetDate()
					-- на дату заявки 
					--and (reg.personprivilege_enddate is null or reg.personprivilege_enddate >= DRPr.DrugRequestPeriod_begDate)
					-- на текущую дату
					and (reg.personprivilege_enddate is null or reg.personprivilege_enddate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime))
				) as reg2
				LEFT JOIN LATERAL (

					select max(case when Lpu_id = " . $Lpu_id . " then 1 else 0 end) as OwnLpu
					from [v_PersonDisp] 

					where Person_id = ps.Person_id
					and (PersonDisp_endDate is null or PersonDisp_endDate > dbo.tzGetDate())
					and Sickness_id is not null
				) as disp
		where
			{$medpersonal}
			and DRP.DrugRequestPeriod_id = {$data['DrugRequestPeriod_id']}
			and {$lpu}
		order by ps.Person_SurName ASC, ps.Person_FirName ASC, ps.Person_SecName ASC
		";
		//print $sql;
		$res = $this->db->query($sql);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}
	*/

	/**
	 * Функция
	 */
	function DrugRequestPersonGrid($data)
	{
		$this->load->helper('Options');
		$medpersonal = "(1=1)";

		if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0)) {
			$lpu = "DRP.Lpu_id = ".$data['Lpu_id'];
			$Lpu_id = $data['Lpu_id'];
		} elseif ((isset($data['session']['lpu_id'])) && ($data['session']['lpu_id']>0)) {
			$lpu = "DRP.Lpu_id = ".$data['session']['lpu_id'];
			$Lpu_id = $data['session']['lpu_id'];
		} else {
			return false;
		}

		if (!isMinZdrav())
		{
			if ((!isset($data['MedPersonal_id'])) || (empty($data['MedPersonal_id'])))
				return false;
			$medpersonal = "DRP.Medpersonal_id = ".$data['MedPersonal_id'];
		}
		else 
		{
			if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
			{
				$lpu = "DRP.Lpu_id = ".$data['Lpu_id'];
				$Lpu_id = $data['Lpu_id'];
				if ($data['session']['lpu_id']==$data['Lpu_id'])
					$medpersonal = "DRP.MedPersonal_id is null";
				else 
				{
					if ((!isset($data['MedPersonal_id'])) || (empty($data['MedPersonal_id'])))
						return false;
					$medpersonal = "DRP.Medpersonal_id = ".$data['MedPersonal_id'];
				}
			}
		}
		$params = array();
		$person = "(1=1)";
		if (strlen($data['Person_SurName'])>0)
		{
			$person = "lower(PS.Person_SurName) LIKE lower(:Person_SurName)";

			$params['Person_SurName'] = "".$data['Person_SurName']."%";
		}

		$person_register_type = "(1=1)";
		if ($data['PersonRegisterType_id'] > 0) {
			$person_register_type = "DRP.PersonRegisterType_id = :PersonRegisterType_id";
			$params['PersonRegisterType_id'] = $data['PersonRegisterType_id'];
		}

		if ((!isset($data['DrugRequestPeriod_id'])) || (empty($data['DrugRequestPeriod_id'])))
			return false;
		
		$sql = "
		Select
			   -- select
			   DrugRequestPerson_id as \"DrugRequestPerson_id\",
			   PS.Server_id as \"Server_id\",
			   PS.PersonEvn_id as \"PersonEvn_id\",
			   DRP.Person_id as \"Person_id\",
			   RTrim(PS.Person_SurName) as \"Person_SurName\",
			   RTrim(PS.Person_FirName) as \"Person_FirName\",
			   RTrim(PS.Person_SecName) as \"Person_SecName\",
			   RTrim(COALESCE(PS.Person_SurName, '')) || ' ' || RTrim(COALESCE(PS.Person_FirName, '')) || ' ' || RTrim(COALESCE(PS.Person_SecName, '')) as \"Person_FIO\",
			   to_char(PS.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\",
			   pcard.Lpu_id as \"Lpu_id\",
			   RTrim(Lpu.Lpu_Nick) as \"Lpu_Nick\",
			   RTrim(pcard.LpuRegion_Name) as \"LpuRegion_Name\",
			   CASE
				 WHEN PRYear.PersonRefuse_id is not null THEN 'true'
				 ELSE 'false'
			   END as \"Person_IsRefuse\",
			   CASE
				 WHEN PRNextYear.PersonRefuse_id is not null THEN 'true'
				 ELSE 'false'
			   END as \"Person_IsRefuseNext\",
			   CASE
				 WHEN PRPeriodYear.PersonRefuse_id is not null THEN 'true'
				 ELSE 'false'
			   END as \"Person_IsRefuseCurr\",
			   CASE
				 WHEN fedl.Person_id is not null then 'true'
				 else 'false'
			   end as \"Person_IsFedLgot\",
			   CASE
				 WHEN fed2.Person_id is not null and PRPeriodYear.PersonRefuse_id is null then 'true'
				 else 'false'
			   end as \"Person_IsFedLgotCurr\",
			   CASE
				 WHEN regl.OwnLpu = 1 THEN 'true'
				 ELSE CASE
						WHEN regl.OwnLpu is not null THEN 'gray'
						ELSE 'false'
					  END
			   END as \"Person_IsRegLgot\",
			   -- региональная льгота для текущего периода заявки 
			   --CASE WHEN reg2.OwnLpu = 1 THEN 'true' ELSE CASE WHEN reg2.OwnLpu is not null THEN 'gray' ELSE 'false' END END as [Person_IsRegLgotCurr],
			   -- региональная льгота для текущего периода - новая по текущей дате и учитывая федералку 
			   CASE
				 WHEN reg2.OwnLpu = 1 and not (fed2.Person_id is not null and PRPeriodYear.PersonRefuse_id is null) THEN 'true'
				 ELSE CASE
						WHEN reg2.OwnLpu is not null and not (fed2.Person_id is not null and PRPeriodYear.PersonRefuse_id is null) THEN 'gray'
						ELSE 'false'
					  END
			   END as \"Person_IsRegLgotCurr\",
			   CASE
				 WHEN disp.OwnLpu = 1 THEN 'true'
				 ELSE CASE
						WHEN disp.OwnLpu is not null THEN 'gray'
						ELSE 'false'
					  END
			   END as \"Person_Is7Noz\",
			   CASE
				 WHEN ps.Server_pid = 0 THEN 'true'
				 ELSE 'false'
			   END as \"Person_IsBDZ\",
			   to_char(DrugRequestPerson_insDT, 'DD.MM.YYYY') as \"DrugRequestPerson_insDT\",
			   to_char(DrugRequestPerson_updDT, 'DD.MM.YYYY') as \"DrugRequestPerson_updDT\",
			   DrrOutCount.DrugRequestRow_Count as \"DrugRequestRow_Count\"
			   -- end select
		from
			 -- from
			 DrugRequestPerson DRP
			 left join DrugRequestPeriod DRPr on DRPr.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
			 left join v_PersonState PS on PS.Person_id = DRP.Person_id
			 -- это для подсветки персонов синеньким, тех персонов на которые есть позиции заявок других ЛПУ
			 LEFT JOIN LATERAL
			 (
			   select count(*) as DrugRequestRow_Count
			   from DrugRequestRow DRR
					left join DrugRequest on DrugRequest.DrugRequest_id = DRR.DrugRequest_id
			   where DRR.Person_id = DRP.Person_id and
					 DrugRequest.Lpu_id != DRP.Lpu_id and
					 DrugRequest.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
			 ) as DrrOutCount ON true
			 LEFT JOIN LATERAL
			 (
			   select pc.Person_id as PersonCard_Person_id,
					  pc.Lpu_id,
					  pc.LpuRegion_id,
					  pc.LpuRegion_Name
			   from v_PersonCard pc
			   where pc.Person_id = ps.Person_id and
					 LpuAttachType_id = 1
			   order by PersonCard_begDate desc
			   limit 1
			 ) as pcard ON true
			 left join v_Lpu Lpu on pcard.Lpu_id = Lpu.Lpu_id
			 left join v_LpuRegion LpuRegion on LpuRegion.LpuRegion_id = pcard.LpuRegion_id
			 left join v_PersonRefuse PRYear ON PRYear.Person_id = ps.Person_id and PRYear.PersonRefuse_IsRefuse = 2 and PRYear.PersonRefuse_Year = date_part('YEAR', dbo.tzGetDate())
			 left join v_PersonRefuse PRNextYear on PRNextYear.Person_id = ps.Person_id and PRNextYear.PersonRefuse_IsRefuse = 2 and PRNextYear.PersonRefuse_Year =(date_part('YEAR', dbo.tzGetDate()) + 1)
			 left join v_PersonRefuse PRPeriodYear on PRPeriodYear.Person_id = ps.Person_id and PRPeriodYear.PersonRefuse_IsRefuse = 2 and PRPeriodYear.PersonRefuse_Year = date_part('YEAR', DRPr.DrugRequestPeriod_begDate)
			 LEFT JOIN LATERAL
			 (
			   select Person_id
			   from v_personprivilege reg
					left join PrivilegeType pt on pt.PrivilegeType_id = reg.PrivilegeType_id
			   where reg.person_id = ps.person_id
					 --and reg.privilegetype_id <= 249
					 and
					 pt.ReceptFinance_id = 1 and
					 reg.personprivilege_begdate <= dbo.tzGetDate() and
					 (reg.personprivilege_enddate is null or
					 reg.personprivilege_enddate >= cast (to_char(dbo.tzGetDate(), 'YYYYMMDD') as timestamp))
			   limit 1      
			 ) as fedl ON true
			 LEFT JOIN LATERAL
			 (
			   select Person_id
			   from v_personprivilege reg
					left join PrivilegeType pt on pt.PrivilegeType_id = reg.PrivilegeType_id
			   where reg.person_id = ps.person_id
					 --and reg.privilegetype_id <= 249
					 and
					 pt.ReceptFinance_id = 1
					 -- and reg.personprivilege_begdate <= DRPr.DrugRequestPeriod_endDate
					 and
					 reg.personprivilege_begdate <= dbo.tzGetDate()
					 -- на дату заявки 
					 --and (reg.personprivilege_enddate is null or reg.personprivilege_enddate >= DRPr.DrugRequestPeriod_begDate)
					 -- на текущую дату 
					 and
					 (reg.personprivilege_enddate is null or
					 reg.personprivilege_enddate >= cast (to_char(dbo.tzGetDate(), 'YYYYMMDD') as timestamp))
			   limit 1
			 ) as fed2 ON true
			 LEFT JOIN LATERAL
			 (
			   select max(case
							when Lpu_id = " . $Lpu_id . " then 1
							else 0
						  end) as OwnLpu
			   from v_personprivilege reg
					left join PrivilegeType pt on pt.PrivilegeType_id = reg.PrivilegeType_id
			   where reg.person_id = ps.person_id
					 --and reg.privilegetype_id > 249
					 and
					 pt.ReceptFinance_id = 2 and
					 reg.personprivilege_begdate <= dbo.tzGetDate() and
					 (reg.personprivilege_enddate is null or
					 reg.personprivilege_enddate >= cast (to_char(dbo.tzGetDate(), 'YYYYMMDD') as timestamp))
			 ) as regl ON true
			 LEFT JOIN LATERAL
			 (
			   select max(case
							when Lpu_id = " . $Lpu_id . " then 1
							else 0
						  end) as OwnLpu
			   from v_personprivilege reg
					left join PrivilegeType pt on pt.PrivilegeType_id = reg.PrivilegeType_id
			   where reg.person_id = ps.person_id
					 --and reg.privilegetype_id > 249
					 and
					 pt.ReceptFinance_id = 2
					 -- and reg.personprivilege_begdate <= DRPr.DrugRequestPeriod_endDate
					 and
					 reg.personprivilege_begdate <= dbo.tzGetDate()
					 -- на дату заявки 
					 --and (reg.personprivilege_enddate is null or reg.personprivilege_enddate >= DRPr.DrugRequestPeriod_begDate)
					 -- на текущую дату
					 and
					 (reg.personprivilege_enddate is null or
					 reg.personprivilege_enddate >= cast (to_char(dbo.tzGetDate(), 'YYYYMMDD') as timestamp))
			 ) as reg2 ON true
			 LEFT JOIN LATERAL
			 (
			   select max(case
							when Lpu_id = " . $Lpu_id . " then 1
							else 0
						  end) as OwnLpu
			   from v_PersonDisp
			   where Person_id = ps.Person_id and
					 (PersonDisp_endDate is null or
					 PersonDisp_endDate > dbo.tzGetDate()) and
					 Sickness_id is not null
			 ) as disp ON true
			 -- end from
		where
			  -- where
			  {$medpersonal} and
			  DRP.DrugRequestPeriod_id = {$data['DrugRequestPeriod_id']} and
			  {$lpu} and
			  {$person} and
			  {$person_register_type}
			  -- end where
		order by
				 -- order by
				 ps.Person_SurName ASC,
				 ps.Person_FirName ASC,
				 ps.Person_SecName ASC
				 -- end order by
		";
		/*
		echo getDebugSql(getLimitSQLPH($sql, $data['start'], $data['limit']), $params);
		exit;
		*/
		$result = $this->db->query(getLimitSQLPH($sql, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($sql), $params);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Получает список медикаментов по заявке (Персонифицированная заявка)
	 */
	function DrugRequestRowGrid($data)
	{
		// Фильтры на заявке 
		// Person_id = обязательное поле для персонифицированной заявки 
		// MedPersonal_id = поле не обязательно , при указании запрос выполняется только по врачу 
		// Lpu_id = поле не обязательно, при указании запрос выполняется только по своей ЛПУ
		// DrugRequestPeriod_id = обязательное поле для заявки - период по которому заводятся медикаменты
		// DrugRequest_id = заявка
		$this->load->helper('Options');
		
		$person = "(1=1)";
		$lpu = "(1=1)";
		$medpersonal = "(1=1)";
		$drugrequesttype = "(1=1)";
		$drugrequest = "(1=1)";
		$person_register_type = "(1=1)";

		if ((isset($data['PersonRegisterType_id'])) && ($data['PersonRegisterType_id']>0)) {
			$person_register_type = "DR.PersonRegisterType_id = :PersonRegisterType_id";
		}

		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$person = "DRR.Person_id = :Person_id";
			$medpersonal_field = "(select RTrim(Person_FIO) from v_MedPersonal MP  where MP.MedPersonal_id = DR.MedPersonal_id and MP.Lpu_id = DR.Lpu_id limit 1) as \"MedPersonal_FIO\"";

			$join = "";

			if ((isset($data['DrugRequest_id'])) && ($data['DrugRequest_id']>0)) {
				$drugrequest = "DRR.DrugRequest_id = :DrugRequest_id";
			}
			if ((isset($data['MedPersonal_id'])) && ($data['MedPersonal_id']>0)) {
				$medpersonal = "DR.MedPersonal_id = :MedPersonal_id";
			}
		}
		else 
		{
			$medpersonal_field = "RTrim(Person_FIO) as \"MedPersonal_FIO\"";
			$join = "left join v_MedPersonal MP  on MP.MedPersonal_id = DR.MedPersonal_id and MP.Lpu_id = DR.lpu_id";

			if (isset($data['session']['lpu_id']) && $data['session']['lpu_id'] > 0)
				$lpu = "DR.Lpu_id = :Lpu_id";
			$person = "DRR.Person_id is null";
			if ((isset($data['DrugRequest_id'])) && ($data['DrugRequest_id']>0)) {
				$drugrequest = "DRR.DrugRequest_id = :DrugRequest_id";
				$person_register_type = "(1=1)";
			}
			if ((isset($data['MedPersonal_id'])) && ($data['MedPersonal_id']>0)) {
				$medpersonal = "DR.MedPersonal_id = :MedPersonal_id";
				if (isMinZdrav())
					$lpu = "(1=1)";
			} else {
				$medpersonal = "(1=1)";
			}
		}

		//В случае если в pfzdrt есть медикаменты из справочника rls, для определения цены нам понадобятся списки медикаментов
		$dr_property_array = array(
			'DrugRequestPropertyFed_id' => null,
			'DrugRequestPropertyReg_id' => null
		);
		if (isset($data['DrugRequest_id']) && $data['DrugRequest_id'] > 0) {
			$res = $this->getDrugRequestProperty($data['DrugRequest_id']);
			if (is_array($res))
				$dr_property_array = $res;
		} elseif (isset($data['PersonRegisterType_id']) && $data['PersonRegisterType_id'] > 0 && isset($data['DrugRequestPeriod_id']) && $data['DrugRequestPeriod_id'] > 0) {
			$res = $this->getDrugRequestProperty(null, $data['PersonRegisterType_id'], $data['DrugRequestPeriod_id']);
			if (is_array($res))
				$dr_property_array = $res;
		}

		if ((isset($data['DrugRequestType_id'])) && ($data['DrugRequestType_id']>0))
			$drugrequesttype = "DRR.DrugRequestType_id = :DrugRequestType_id";

		if ((!isset($data['DrugRequestPeriod_id'])) || (empty($data['DrugRequestPeriod_id'])))
			return false;
		
		$data['Server_id'] = $data['session']['server_id'];

		if (((!isset($data['Person_id'])) || ($data['Person_id']==0)) && 
				((!isset($data['MedPersonal_id'])) || ($data['MedPersonal_id']==0)) && 
				((!isset($data['DrugRequest_id'])) || ($data['DrugRequest_id']==0)))
			return false;
		
		$sql = "
			-- Этот дистинкт надо убрать, как только Тарас поправит все данные
            Select distinct DRR.DrugRequestRow_id as \"DrugRequestRow_id\",
                   DRR.DrugRequest_id as \"DrugRequest_id\",
                   DRR.DrugProtoMnn_id as \"DrugProtoMnn_id\",
                   DRR.DrugComplexMnn_id as \"DrugComplexMnn_id\",
                   DRR.TRADENAMES_id as \"TRADENAMES_id\",
                   DRR.Drug_id as \"Drug_id\",
                   (case
                      when (DRR.DrugProtoMnn_id is null) and (DRR.Drug_id is not null) then 1
                      else 0
                    end) as \"IsDrug\",
                   RTrim(case
                           when (DRR.Drug_id is not null) then Drug.Drug_Name
                           when (DRR.DrugProtoMnn_id is not null) then DrugProtoMnn.DrugProtoMnn_Name
                           else DCMN.DrugComplexMnnName_Name
                         end) as \"DrugRequestRow_Name\",
                   (case
                      when (DRR.Drug_id is not null) then Drug.Drug_Code
                      else DrugProtoMnn.DrugProtoMnn_Code
                    end) as \"DrugRequestRow_Code\",
                   DRR.DrugRequestRow_Kolvo as \"DrugRequestRow_Kolvo\",
                   RTrim(CAST (case
                                 when (DRR.Drug_id is not null) then Round(CAST (DrugPrice.DrugState_Price as numeric), 2)
                                 when (DRR.DrugProtoMnn_id is not null) then Round(CAST (DrugState.DrugState_Price as numeric), 2)
                                 else DrugListRequest_Data.Price
                               end as varchar)) as \"DrugRequestRow_Price\", 
                /*
                DrugState.DrugState_Price as \"DrugRequestRow_Price\",
                (DRR.DrugRequestRow_Kolvo * DrugState.DrugState_Price) as \"DrugRequestRow_Summa\",
                */
                   DrugRequestRow_Summa as \"DrugRequestRow_Summa\",
                   DRR.DrugRequestType_id as \"DrugRequestType_id\",
                   RTrim(DrugRequestType_Name) as \"DrugRequestType_Name\",
                   DR.MedPersonal_id as \"MedPersonal_id\",
                   {$medpersonal_field},
                   DR.Lpu_id as \"Lpu_id\",
                   RTrim(Lpu_Nick) as \"Lpu_Nick\",
                   Null as \"DrugRequest_Zakup\",
                   to_char(DrugRequestRow_insDT, 'DD.MM.YYYY') as \"DrugRequestRow_insDT\",
                   to_char(DrugRequestRow_updDT, 'DD.MM.YYYY') as \"DrugRequestRow_updDT\",
                   to_char(coalesce(DRR.DrugRequestRow_delDT, DR.DrugRequest_delDT), 'DD.MM.YYYY') as \"DrugRequestRow_delDT\",
                   coalesce(DRR.DrugRequestRow_Deleted, DR.DrugRequest_Deleted) as \"DrugRequestRow_Deleted\",
                   DRR.DrugRequestRow_DoseOnce as \"DrugRequestRow_DoseOnce\",
                   DRR.DrugRequestRow_DoseDay as \"DrugRequestRow_DoseDay\",
                   DRR.DrugRequestRow_DoseCource as \"DrugRequestRow_DoseCource\",
                   DRR.Okei_oid as \"Okei_oid\",
                   OkeiO.Okei_NationSymbol as \"Okei_oid_NationSymbol\",
                   DrugListRequest_Data.isProblem as \"isProblem\",
                   CDF.NAME as \"ClsDrugForms_Name\",
                   DCMD.DrugComplexMnnDose_Name as \"DrugComplexMnnDose_Name\",
                   DCMF.DrugComplexMnnFas_Name as \"DrugComplexMnnFas_Name\",
                   (
                     SELECT string_agg(name, ', ')
                     FROM (
                            select distinct SUBSTRING(CA.NAME, 1, strpos(CA.NAME, ' ') - 1) as name
                            from rls.PREP_ACTMATTERS PAM
                                 left join rls.PREP_ATC PA on PA.PREPID = PAM.PREPID
                                 inner join rls.CLSATC CA on CA.CLSATC_ID = PA.UNIQID
                            where PAM.MATTERID = DCMN.ActMatters_id
                          ) t
                   ) as \"ATX_Code\",
                   (
                     select CN.NAME
                     from rls.v_Drug D
                          left join rls.v_prep P on P.Prep_id = D.DrugPrep_id
                          left join rls.CLSNTFR CN on CN.CLSNTFR_ID = P.NTFRID
                     where D.DrugComplexMnn_id = DCM.DrugComplexMnn_id
                     limit 1
                   ) as \"NTFR_Name\"
            from DrugRequestRow DRR
                 inner join DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id
                 left join DrugProtoMnn on DrugProtoMnn.DrugProtoMnn_id = DRR.DrugProtoMnn_id
                 left join rls.DrugComplexMnn DCM on DCM.DrugComplexMnn_id = DRR.DrugComplexMnn_id
                 left join rls.DrugComplexMnnName DCMN on DCMN.DrugComplexMnnName_id = DCM.DrugComplexMnnName_id
                 left join rls.DrugComplexMnnDose DCMD on DCMD.DrugComplexMnnDose_id = DCM.DrugComplexMnnDose_id
                 left join rls.DrugComplexMnnFas DCMF on DCMF.DrugComplexMnnFas_id = DCM.DrugComplexMnnFas_id
                 left join rls.CLSDRUGFORMS CDF on CDF.CLSDRUGFORMS_ID = DCM.CLSDRUGFORMS_ID
                 LEFT JOIN LATERAL
                 (
                   select DrugState_Price,
                          DrugProto.DrugRequestPeriod_id
                   from DrugState
                        left join DrugProto on DrugState.DrugProto_id = DrugProto.DrugProto_id
                   where DrugState.DrugProtoMnn_id = DrugProtoMnn.DrugProtoMnn_id and
                         (DR.DrugRequestPeriod_id = DrugProto.DrugRequestPeriod_id or
                         DRR.DrugProtoMnn_id is null)
                   order by DrugState_Price desc
                   limit 1
                 ) as DrugState ON true 
                 {$join}
                 left join v_Lpu Lpu on Lpu.Lpu_id = DR.Lpu_id
                 left join DrugRequestType on DrugRequestType.DrugRequestType_id = DRR.DrugRequestType_id
                 -- Торговые
                 left join Drug on Drug.Drug_id = DRR.Drug_id and Drug.DrugClass_id = 1
                 left join v_DrugPrice DrugPrice on DrugPrice.Drug_id = Drug.Drug_id and DRR.DrugRequestType_id = DrugPrice.ReceptFinance_id and DrugPrice.DrugProto_begDate =
                 (
                   select max(DrugProto_begDate)
                   from v_DrugPrice DP
                   where Drug_id = Drug.Drug_id and
                         DP.ReceptFinance_id = DrugPrice.ReceptFinance_id and
                         DP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
                 )
                 LEFT JOIN LATERAL
                 (
                   select (case
                             when DLRT.DrugRequest_Price > 0 then DLRT.DrugRequest_Price
                             else DLR.DrugListRequest_Price
                           end) as Price,
                          (case
                             when isProblem.YesNo_Code > 0 or isProblemTorg.YesNo_Code > 0 then 1
                             else 0
                           end) as isProblem
                   from v_DrugListRequest DLR
                        left join v_DrugListRequestTorg DLRT on DLRT.DrugListRequest_id = DLR.DrugListRequest_id and DLRT.TRADENAMES_id = DRR.TRADENAMES_id
                        left join dbo.v_YesNo isProblem on isProblem.YesNo_id = DLR.DrugListRequest_IsProblem
                        left join dbo.v_YesNo isProblemTorg on isProblemTorg.YesNo_id = DLRT.DrugListRequestTorg_IsProblem
                   where DRR.DrugComplexMnn_id is not null and
                         DRR.DrugRequestType_id is not null and
                         DLR.DrugComplexMnn_id = DRR.DrugComplexMnn_id and
                         ((DRR.DrugRequestType_id = 1 and
                         DLR.DrugRequestProperty_id =:DrugRequestPropertyFed_id) or
                         (DRR.DrugRequestType_id = 2 and
                         DLR.DrugRequestProperty_id =:DrugRequestPropertyReg_id))
                   limit 1
                 ) as DrugListRequest_Data ON true
                 left join v_Okei OkeiO on OkeiO.Okei_id = DRR.Okei_oid
            where {$person} and
                  DR.DrugRequestPeriod_id =:DrugRequestPeriod_id and
                  {$medpersonal} and
                  {$drugrequesttype} and
                  {$drugrequest} and
                  {$person_register_type} and
                  {$lpu} and
                  (DR.DrugRequestPeriod_id = DrugState.DrugRequestPeriod_id or
                  DRR.DrugProtoMnn_id is null)
		";
		//print $sql;
		//print getDebugSql(
		$res = $this->db->query(
			$sql,
			array(
				'Person_id' => isset($data['Person_id']) ? $data['Person_id'] : '',
				'PersonRegisterType_id' => isset($data['PersonRegisterType_id']) ? $data['PersonRegisterType_id'] : '',
				'DrugRequestPeriod_id' => isset($data['DrugRequestPeriod_id']) ? $data['DrugRequestPeriod_id'] : '',
				'DrugRequestType_id' => isset($data['DrugRequestType_id']) ? $data['DrugRequestType_id'] : '',
				'DrugRequest_id' => isset($data['DrugRequest_id']) ? $data['DrugRequest_id'] : '',
				'MedPersonal_id' => isset($data['MedPersonal_id']) ? $data['MedPersonal_id'] : '',
				'Lpu_id' => $data['session']['lpu_id'],
				'DrugRequestPropertyFed_id' => $dr_property_array['DrugRequestPropertyFed_id'],
				'DrugRequestPropertyReg_id' => $dr_property_array['DrugRequestPropertyReg_id']
			)
		);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Функция
	 */
	function DrugRequestGrid($data)
	{
		$this->load->helper('Options');
		$lpu = "(1=1)";
		$medpersonal = "(1=1)";
		$drugrequestperiod = "(1=1)";
		$drugrequeststatus = "(1=1)";
		$drugrequesttype = "(1=1)";
		$lpusection = "(1=1)";
		$drugrequest = "(1=1)";

		if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0)) {
			$lpu = "DR.Lpu_id = ".$data['Lpu_id'];
		} elseif ((isset($data['session']['lpu_id'])) && ($data['session']['lpu_id']>0)) {
			$lpu = "DR.Lpu_id = ".$data['session']['lpu_id'];
		}

		if (!isMinZdrav())
		{
			if ((isset($data['MedPersonal_id'])) && ($data['MedPersonal_id']>0))
				$medpersonal = "DR.MedPersonal_id = ".$data['MedPersonal_id'];
		}
		else 
		{
			if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
			{
				$lpu = "DR.Lpu_id = ".$data['Lpu_id'];
				if ($data['session']['lpu_id']==$data['Lpu_id'])
					$medpersonal = "DR.MedPersonal_id is null";
			}
		}
		/*
		if ((isset($data['DrugRequestType_id'])) && ($data['DrugRequestType_id']>0))
			$drugrequesttype = "DRR.DrugRequestType_id = ".$data['DrugRequestType_id'];
		*/
		if ((isset($data['DrugRequestType_id'])) && ($data['DrugRequestType_id']>0))
			$drugrequesttype = "DRR.DrugRequestType_id = ".$data['DrugRequestType_id'];
		if ((isset($data['DrugRequestPeriod_id'])) && ($data['DrugRequestPeriod_id']>0))
			$drugrequestperiod = "DR.DrugRequestPeriod_id = ".$data['DrugRequestPeriod_id'];
		if ((isset($data['DrugRequestStatus_id'])) && ($data['DrugRequestStatus_id']>0))
			$drugrequeststatus = "DR.DrugRequestStatus_id = ".$data['DrugRequestStatus_id'];
		if ((isset($data['LpuSection_id'])) && ($data['LpuSection_id']>0))
			$lpusection = "DR.LpuSection_id = ".$data['LpuSection_id'];
		if ((isset($data['DrugRequest_id'])) && ($data['DrugRequest_id']>0))
			$drugrequest = "DR.DrugRequest_id = ".$data['DrugRequest_id'];
		$sql = "
		Select DR.DrugRequest_id as \"DrugRequest_id\",
			   DR.PersonRegisterType_id as \"PersonRegisterType_id\",
			   RTrim(PersonRegisterType.PersonRegisterType_Name) as \"PersonRegisterType_Name\",
			   DR.DrugRequestPeriod_id as \"DrugRequestPeriod_id\",
			   RTrim(DrugRequestPeriod.DrugRequestPeriod_Name) as \"DrugRequestPeriod_Name\",
			   DR.DrugRequestStatus_id as \"DrugRequestStatus_id\",
			   RTrim(CAST (DrugRequestStatus.DrugRequestStatus_Code as varchar)) as \"DrugRequestStatus_Code\",
			   RTrim(DrugRequestStatus.DrugRequestStatus_Name) as \"DrugRequestStatus_Name\",
			   RTrim(DrugRequestKind.DrugRequestKind_Name) as \"DrugRequestKind_Name\",
			   RTrim(DR.DrugRequest_Name) as \"DrugRequest_Name\",
			   --DR.DrugRequest_Summa as DrugRequest_Summa,
			   -- как только в самой заявке потребуются суммы , это надо будет разрэмить и групбай тоже 
			   --Sum(case when DRR.DrugRequestType_id=1 then DRR.DrugRequestRow_Summa else 0 end) as DrugRequest_SummaFed,
			   --Sum(case when DRR.DrugRequestType_id=2 then DRR.DrugRequestRow_Summa else 0 end) as DrugRequest_SummaReg,
			   --COALESCE(Sum(DRR.DrugRequestRow_Summa),0) as DrugRequest_Summa,
			   DR.DrugRequest_YoungChildCount as \"DrugRequest_YoungChildCount\",
			   DR.Lpu_id as \"Lpu_id\",
			   RTrim(Lpu.Lpu_Name) as \"Lpu_Name\",
			   RTrim(LpuUnit.LpuUnit_Name) as \"LpuUnit_Name\",
			   DR.MedPersonal_id as \"MedPersonal_id\",
			   RTrim(Person_FIO) as \"MedPersonal_FIO\",
			   DR.LpuSection_id as \"LpuSection_id\",
			   RTrim(LpuSection_Name) as \"LpuSection_Name\",
			   to_char(DrugRequest_insDT, 'DD.MM.YYYY') as \"DrugRequest_insDT\",
			   to_char(DrugRequest_updDT, 'DD.MM.YYYY') as \"DrugRequest_updDT\",
			   Null as \"DrugRequest_delDT\",
			   DRTS.DrugRequestTotalStatus_IsClose as \"DrugRequestTotalStatus_IsClose\"
		from v_DrugRequest DR
			 left join DrugRequestStatus on DrugRequestStatus.DrugRequestStatus_id = DR.DrugRequestStatus_id
			 left join DrugRequestPeriod on DrugRequestPeriod.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
			 left join DrugRequestKind on DrugRequestKind.DrugRequestKind_id = DR.DrugRequestKind_id
			 left join PersonRegisterType on PersonRegisterType.PersonRegisterType_id = DR.PersonRegisterType_id
			 left join v_MedPersonal MP on MP.MedPersonal_id = DR.MedPersonal_id and MP.Lpu_id = DR.lpu_id
			 left join v_Lpu Lpu on Lpu.Lpu_id = DR.Lpu_id
			 left join v_LpuSection LpuSection on LpuSection.LpuSection_id = DR.LpuSection_id
			 left join v_LpuUnit LpuUnit on LpuUnit.LpuUnit_id = LpuSection.LpuUnit_id
			 left join DrugRequestTotalStatus DRTS on DRTS.Lpu_id = DR.Lpu_id and DR.DrugRequestPeriod_id = DRTS.DrugRequestPeriod_id
			 left join DrugRequestRow DRR on DRR.DrugRequest_id = DR.DrugRequest_id
			 --left join v_Lpu Lpu  on Lpu.Lpu_id = DR.Lpu_id
		where {$drugrequest} and
			  {$drugrequeststatus} and
			  {$drugrequestperiod} and
			  {$medpersonal} and
			  {$drugrequesttype} and
			  {$lpusection} and
			  {$lpu} 
			  /*
					group by
					DR.DrugRequest_id,
					DR.PersonRegisterType_id,
					DR.DrugRequestPeriod_id,
					DrugRequestPeriod.DrugRequestPeriod_Name,
					DR.DrugRequestStatus_id,
					DrugRequestStatus.DrugRequestStatus_Name,
					DR.DrugRequest_Name,
					DR.DrugRequest_YoungChildCount, 
					DR.Lpu_id,
					DR.MedPersonal_id,
					Person_FIO,
					DR.LpuSection_id,
					LpuSection_Name,
					DrugRequest_insDT,
					DrugRequest_updDT,
					--DrugRequest_delDT
					DRTS.DrugRequestTotalStatus_IsClose
			  */
		";
		//print $sql;
		$res = $this->db->query($sql);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Функция
	 */
	function getDrugRequestLpuClose($data)
	{
		$this->load->helper('Options');
		$params = array();
		$lpu = "(1=1)";
		$drugrequestperiod = "(1=1)";

		if (isMinZdrav() && ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))) {
			// То берем пришедшее Lpu_id
			$params['Lpu_id'] = $data['Lpu_id'];
		} elseif ((isset($data['session']['lpu_id'])) && ($data['session']['lpu_id']>0)) {
			$params['Lpu_id'] = $data['session']['lpu_id'];
		} else {
			return false;
		}

		if (!((isset($data['DrugRequestPeriod_id'])) && ($data['DrugRequestPeriod_id']>0))) {
            return false;
        }
		
		$params['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];

        //определение года для получения данных по отказам
		if (in_array($data['DrugRequestPeriod_id'], array(62035, 62036))) {
			$year = "2015";
		} else {
            //получаем год рабочего периода
            $query = "
                select date_part('year', drp.DrugRequestPeriod_begDate) as \"yr\"
				from v_DrugRequestPeriod drp
				where drp.DrugRequestPeriod_id = :DrugRequestPeriod_id;
            ";
            $year = $this->getFirstResultFromQuery($query, $data);

            //проверяем наличие данных по отказам на год рабочего пероиода, если данных нет, то берем данные по отказам за прошлый год
            if ($year > 0) {
                $query = "
                    select PersonRefuse_id as \"PersonRefuse_id\"
					from v_PersonRefuse pr
					where pr.PersonRefuse_Year =:PersonRefuse_Year
					limit 1
                ";
                $res = $this->getFirstResultFromQuery($query, array(
                    'PersonRefuse_Year' => $year
                ));
                if (empty($res)) {
                    $year = $year - 1;
                }
            }
        }

        if (empty($year)) {
            $year = "date_part('Year',DRP.DrugRequestPeriod_begDate)";
        }

		$sql = "
            Select count(distinct case
									when PersonRefuse_id is null then fed.Person_id
									else null
								  end) as \"FedLgotCount\",
				   count(distinct case
									when PersonRefuse_id is null and fed.Person_id is null then reg.Person_id
									else null
								  end) as \"RegLgotCount\"
			from PersonCardState PC
				 left join DrugRequestPeriod DRP on DRP.DrugRequestPeriod_id =:DrugRequestPeriod_id
				 --and (PP.PersonPrivilege_endDate>=DrugRequestPeriod_begDate or PP.PersonPrivilege_endDate is null)
				 -- поменяли на текущую льготу, согласно (#1519)
				 LEFT JOIN LATERAL
				 (
				   select reg.Person_id,
						  PersonRefuse_id
				   from v_personprivilege reg
						left join v_PersonRefuse PR on PR.Person_id = reg.Person_id and {$year} = PR.PersonRefuse_Year and PR.PersonRefuse_IsRefuse = 2
						left join PrivilegeType pt on pt.PrivilegeType_id = reg.PrivilegeType_id
				   where reg.person_id = PC.person_id
						 --and reg.privilegetype_id <= 249
						 and
						 pt.ReceptFinance_id = 1 and
						 reg.personprivilege_begdate <= dbo.tzGetDate() and
						 (reg.personprivilege_enddate is null or
						 reg.personprivilege_enddate >= dbo.tzGetDate())
				   limit 1
				 ) as fed ON true
				 LEFT JOIN LATERAL
				 (
				   select Person_id
				   from v_personprivilege reg
						left join PrivilegeType pt on pt.PrivilegeType_id = reg.PrivilegeType_id
				   where reg.person_id = PC.person_id
						 --and reg.privilegetype_id > 249
						 and
						 pt.ReceptFinance_id = 2 and
						 reg.personprivilege_begdate <= dbo.tzGetDate() and
						 (reg.personprivilege_enddate is null or
						 reg.personprivilege_enddate >= dbo.tzGetDate())
				   limit 1
				 ) as reg ON true
			where PC.Lpu_id =:Lpu_id and
				  PC.PersonCardState_begDate <= DRP.DrugRequestPeriod_endDate and
				  (PC.PersonCardState_endDate is null or
				  PC.PersonCardState_endDate > DRP.DrugRequestPeriod_begDate) and
				  PC.LpuAttachType_id = 1
		";
		$fedlgotcount = 0;
		$reglgotcount = 0;
		$pcresult = $this->db->query($sql, $params);
		if (is_object($pcresult)) {
			$dt = $pcresult->result('array');
			if (count($dt) > 0) {
				$fedlgotcount = $dt[0]['FedLgotCount'];
				$reglgotcount = $dt[0]['RegLgotCount'];
			}
		}
		
		$sql = "
            select DRTS.DrugRequestTotalStatus_id as \"DrugRequestTotalStatus_id\",
				   COALESCE(DRTS.DrugRequestTotalStatus_IsClose, 1) as \"DrugRequestTotalStatus_IsClose\"
			from DrugRequestTotalStatus DRTS
			where DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id and
				  DRTS.Lpu_id =:Lpu_id
			limit 1
		";

		$res = $this->db->query($sql, $params);
		if (is_object($res)) {
			$dt = $res->result('array');
			if (count($dt) == 0) {
				$dt[0]['DrugRequestTotalStatus_id'] = '';
				$dt[0]['DrugRequestTotalStatus_IsClose'] = 1;
			}
			$dt[0]['FedLgotCount'] = $fedlgotcount;
			$dt[0]['RegLgotCount'] = $reglgotcount;
			return $dt;
		} else {
            return false;
        }
	}

	/**
	 * Функция
	 */
	function getDrugRequestLpuUt($data)
	{
		$this->load->helper('Options');
		$lpu = "(1=1)";
		$drugrequestperiod = "(1=1)";
		
		if ((isset($data['session']['lpu_id'])) && ($data['session']['lpu_id']>0))
			$lpu = "DR.Lpu_id = ".$data['session']['lpu_id'];
		if (isMinZdrav())
		{
			if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
			{
				$lpu = "DR.Lpu_id = ".$data['Lpu_id'];
			}
		}
		if ((isset($data['DrugRequestPeriod_id'])) && ($data['DrugRequestPeriod_id']>0))
			$drugrequestperiod = "DR.DrugRequestPeriod_id = ".$data['DrugRequestPeriod_id'];
		else 
			return false;

		$sql = "
		Select 
			DR.DrugRequestStatus_id as \"DrugRequestStatus_id\"
			from v_DrugRequest DR 
			where 
				{$drugrequestperiod} and 
				DR.DrugRequestStatus_id = 3 and 
				{$lpu}
		";
		//print $sql;
		$res = $this->db->query($sql);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Функция
	 */
	function getDrugRequestLpuReallocated($data) {
		$this->load->helper('Options');
		$params = array();

		if (isMinZdrav() && !empty($data['Lpu_id'])) {
            $params['Lpu_id'] = $data['Lpu_id'];
		} else if (!empty($data['session']['lpu_id'])) {
            $params['Lpu_id'] = $data['session']['lpu_id'];
        }

		if (!empty($data['DrugRequestPeriod_id'])) {
			$params['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];
        }

        if (empty($params['Lpu_id']) || empty($data['DrugRequestPeriod_id'])) {
			return false;
        }

		$sql = "
            select dr.DrugRequestStatus_id as \"DrugRequestStatus_id\"
			from v_DrugRequest dr
				 left join v_DrugRequestStatus drs on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
			where dr.MedPersonal_id is not null and
				  dr.DrugGroup_id is null and
				  dr.PersonRegisterType_id is null and
				  drs.DrugRequestStatus_Code = 5 and --5 - Перераспределение
				  dr.DrugRequestPeriod_id =:DrugRequestPeriod_id and
				  dr.Lpu_id =:Lpu_id
			limit 1
		";
		$res = $this->db->query($sql, $params);
		if (is_object($res)) {
            return $res->result('array');
        } else {
            return false;
        }
	}

	/**
	 * Функция
	 */
	function DrugRequestGridSum($data, $options)
	{
		$this->load->helper('Options');
		$mode = null;
		$sql = "";
		$lpu = "(1=1)";
		$medpersonal = "(1=1)";
		$drugrequestperiod = "(1=1)";
		$drugrequeststatus = "(1=1)";
		$drugrequesttype = "(1=1)";
		$lpuunit = "(1=1)";
		$lpusection = "(1=1)";
		$drugrequest = "(1=1)";
		$person_register_type = "(1=1)";
		$drugrequestkind = "(1=1)";
		$druggroup = "DR.DrugGroup_id is null";

		$set_normativ = "";
		$lgot_count_with = "";
		$lgot_count_select = "";
		$lgot_count_join = "";
		$lgot_count_group = "";

		if (isset($data['mode']) && !empty($data['mode'])) {
            $mode = $data['mode'];
        }

		if (isset($data['Lpu_id']) && $data['Lpu_id'] > 0) {
			$lpu = "DR.Lpu_id = ".$data['Lpu_id'];
		} else {
			if ((isset($data['session']['lpu_id'])) && ($data['session']['lpu_id']>0))
				$lpu = "DR.Lpu_id = ".$data['session']['lpu_id'];			
		}

		//не осилил зачем это сделано поэтому закомментировал (Salakhov)

		// 2013-05-20, savage:
		// https://redmine.swan.perm.ru/issues/19362
		// для минздрава должна быть возможность просмотреть свою заявку, а она не привязана к какому-либо врачу
		// раскомментировал обратно
		if (!isMinZdrav())
		{
			if ((isset($data['MedPersonal_id'])) && ($data['MedPersonal_id']>0))
				$medpersonal = "DR.MedPersonal_id = ".$data['MedPersonal_id'];
		} else {
			if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0)) {
				$lpu = "DR.Lpu_id = ".$data['Lpu_id'];
				if ($data['session']['lpu_id']==$data['Lpu_id'])
					$medpersonal = "DR.MedPersonal_id is null";
			}
		}
		// добавил вместо предыдущего закомментированного блока
		// 2013-05-20, savage: а это закомментировал
		/*
		$lpu = "DR.Lpu_id = ".$data['Lpu_id'];
		$medpersonal = "DR.MedPersonal_id is not null"; // губительный фильтр для Минздрава
		*/

		/*
		if ((isset($data['DrugRequestType_id'])) && ($data['DrugRequestType_id']>0))
			$drugrequesttype = "DRR.DrugRequestType_id = ".$data['DrugRequestType_id'];
		*/
		if ((isset($data['DrugRequestType_id'])) && ($data['DrugRequestType_id']>0))
			$drugrequesttype = "DRR.DrugRequestType_id = ".$data['DrugRequestType_id'];
		if ((isset($data['DrugRequestPeriod_id'])) && ($data['DrugRequestPeriod_id']>0))
			$drugrequestperiod = "DR.DrugRequestPeriod_id = ".$data['DrugRequestPeriod_id'];
		if ((isset($data['DrugRequestStatus_id'])) && ($data['DrugRequestStatus_id']>0))
			$drugrequeststatus = "DR.DrugRequestStatus_id = ".$data['DrugRequestStatus_id'];
		if ((isset($data['LpuUnit_id'])) && ($data['LpuUnit_id']>0))
			$lpuunit = "LpuSection.LpuUnit_id = ".$data['LpuUnit_id'];
		if ((isset($data['LpuSection_id'])) && ($data['LpuSection_id']>0))
			$lpusection = "DR.LpuSection_id = ".$data['LpuSection_id'];
		if ((isset($data['DrugRequest_id'])) && ($data['DrugRequest_id']>0))
			$drugrequest = "DR.DrugRequest_id = ".$data['DrugRequest_id'];
		if ((isset($data['PersonRegisterType_id'])) && ($data['PersonRegisterType_id']>0)) {
            $person_register_type = "DR.PersonRegisterType_id = ".$data['PersonRegisterType_id'];
        } else {
            $person_register_type = "DR.PersonRegisterType_id is null";
        }
		if ((!isset($data['DrugRequestPeriod_id'])) || ($data['DrugRequestPeriod_id']==0))
			return false;
		if (isset($data['PersonRegisterType_id']) && $data['PersonRegisterType_id']>0 && isset($data['DrugRequestPeriod_id']) && $data['DrugRequestPeriod_id']>0) {
			$set_normativ = "
				,cte2 AS (
					SELECT COALESCE((
									  select DrugRequestQuota_Person
									  from DrugRequestQuota
									  where
											PersonRegisterType_id = {$data['PersonRegisterType_id']} and DrugRequestPeriod_id = {$data['DrugRequestPeriod_id']} and 
											DrugFinance_id =
											(
											  select DrugFinance_id
											  from v_DrugFinance
											  where DrugFinance_SysNick = 'fed'
											  limit 1
											)
									  limit 1
						   ), 0) as normativ_fed_lgot,
						   COALESCE((
									  select DrugRequestQuota_Person
									  from DrugRequestQuota
									  where
											PersonRegisterType_id = {$data['PersonRegisterType_id']} and DrugRequestPeriod_id = {$data['DrugRequestPeriod_id']} and 
											DrugFinance_id =
											(
											  select DrugFinance_id
											  from v_DrugFinance
											  where DrugFinance_SysNick = 'reg'
											  limit 1
											)
									  limit 1
						   ), 0) as normativ_reg_lgot)
			";
		} else
		{
			$set_normativ = "
				,cte2 AS ( SELECT normativ_fed_lgot, normativ_reg_lgot FROM cte1)
			";
		}

		$where = "
				DR.DrugRequest_Version is null and
				{$drugrequest} and
				{$drugrequeststatus} and
				{$drugrequestperiod} and
				{$medpersonal} and
				{$drugrequesttype} and
				{$lpuunit} and
				{$lpusection} and
				{$lpu} and
				{$person_register_type} and
				{$drugrequestkind} and
				{$druggroup}
		";

		if ($mode == "only_count") {
			$sql = "
				select count(DR.DrugRequest_id) as \"cnt\"
				from v_DrugRequest DR
					 left join v_LpuSection LpuSection on LpuSection.LpuSection_id = DR.LpuSection_id
				where 
					{$where}
			";
		} else {
			if ($mode == "with_lgot_count") {
				$lgot_count_with = "
				,filtered_drugrequest as (
                    select DR.Lpu_id,
                           DR.DrugRequest_id,
                           DR.MedPersonal_id
                    from v_DrugRequest DR
                    where 
                    {$where})
                ,lgot_count as (
                    select dr.DrugRequest_id,
                           count(case
                                   when pt.ReceptFinance_id = 1 then pc.Person_id
                                 end) as fed_cnt,
                           count(case
                                   when pt.ReceptFinance_id = 2 then pc.Person_id
                                 end) as reg_cnt
                    from MedStaffRegion msr
                         left join PersonCard pc on pc.LpuRegion_id = msr.LpuRegion_id
                         left join PersonPrivilege pp on pp.Person_id = pc.Person_id
                         left join PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
                         left join v_DrugRequest dr on dr.DrugRequest_id in (
                                                                              select DrugRequest_id
                                                                              from filtered_drugrequest
                         ) and dr.MedPersonal_id = msr.MedPersonal_id
                         left join v_DrugRequestPeriod drp on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
                    where msr.MedPersonal_id in (
                                                  select MedPersonal_id
                                                  from filtered_drugrequest
                          ) and
                          pp.PrivilegeType_id is not null and
                          pp.PersonPrivilege_begDate <= drp.DrugRequestPeriod_begDate and
                          (pp.PersonPrivilege_endDate is null or
                          pp.PersonPrivilege_endDate >= drp.DrugRequestPeriod_begDate)
                    group by dr.DrugRequest_id) 
                 ";
				$lgot_count_select = "
					lgot_count.fed_cnt as \"FedPrivilegePerson_Count\",
					lgot_count.reg_cnt as \"RegPrivilegePerson_Count\",
				";
				$lgot_count_join = "left join lgot_count  on lgot_count.DrugRequest_id = DrugRequest.DrugRequest_id";

				$lgot_count_group = "
					lgot_count.fed_cnt,
					lgot_count.reg_cnt,
				";
			}

			$sql = "
				WITH cte1 AS (
					SELECT CAST(".(isset($options['normativ_fed_lgot']) ? $options['normativ_fed_lgot'] : " 0") . " as numeric) as normativ_fed_lgot,
						   CAST(1 as numeric) as koef_fed_lgot,
						   CAST(".( isset($options [ 'normativ_reg_lgot' ]) ? $options [ 'normativ_reg_lgot' ]:"0")." as numeric) as normativ_reg_lgot,
						   CAST(1 as numeric) as koef_reg_lgot
				)

				{$set_normativ}
                {$lgot_count_with}
               
                Select DrugRequest.DrugRequest_id as \"DrugRequest_id\",
                       DrugRequest.DrugRequest_Version as \"DrugRequest_Version\",
                       DrugRequestPeriod_id as \"DrugRequestPeriod_id\",
                       DrugRequestPeriod_Name as \"DrugRequestPeriod_Name\",
                       DrugRequestStatus_id as \"DrugRequestStatus_id\",
                       DrugRequestStatus_Name as \"DrugRequestStatus_Name\",
                       DrugRequest_Name as \"DrugRequest_Name\",
                       {$lgot_count_select} 
                       Sum(DrugRequest_SummaFed) as \"DrugRequest_SummaFed\",
                       Sum(DrugRequest_SummaReg) as \"DrugRequest_SummaReg\",
                       Sum(DrugRequest_SummaFedReserve) as \"DrugRequest_SummaFedReserve\",
                       Sum(DrugRequest_SummaRegReserve) as \"DrugRequest_SummaRegReserve\",
                       COALESCE(DRRLpu.DRRSumFed, 0) + Sum(DrugRequest_SummaFed) as \"DrugRequest_SummaFedAll\",
                       COALESCE(DRRLpu.DRRSumReg, 0) + Sum(DrugRequest_SummaReg) as \"DrugRequest_SummaRegAll\",
                       Sum(DrugRequest_SummaFedLimit) as \"DrugRequest_SummaFedLimit\",
                       Sum(DrugRequest_SummaRegLimit) +(DrugRequest_YoungChildCount *
                       (
                         SELECT normativ_reg_lgot
                         FROM cte2
                       ) * 3 *
                       (
                         SELECT koef_reg_lgot
                         FROM cte1
                       )) as \"DrugRequest_SummaRegLimit\",
                       Lpu_id as \"Lpu_id\",
                       Lpu_Nick as \"Lpu_Name\",
                       MedPersonal_id as \"MedPersonal_id\",
                       MedPersonal_FIO as \"MedPersonal_FIO\",
                       LpuSection_id as \"LpuSection_id\",
                       LpuSection_Name as \"LpuSection_Name\",
                       DrugRequest.PersonRegisterType_id as \"PersonRegisterType_id\",
                       PersonRegisterType_Name as \"PersonRegisterType_Name\",
                       DrugRequestKind_Name as \"DrugRequestKind_Name\",
                       DrugRequest_insDT as \"DrugRequest_insDT\",
                       DrugRequest_updDT as \"DrugRequest_updDT\",
                       DrugRequest_delDT as \"DrugRequest_delDT\"
                from (
                       Select DR.DrugRequest_id,
                              DR.DrugRequest_Version,
                              DR.DrugRequestPeriod_id,
                              RTrim(DrugRequestPeriod.DrugRequestPeriod_Name) as DrugRequestPeriod_Name,
                              DrugRequestPeriod.DrugRequestPeriod_begDate,
                              DrugRequestPeriod.DrugRequestPeriod_endDate,
                              DR.DrugRequestStatus_id,
                              RTrim(DrugRequestStatus.DrugRequestStatus_Name) as DrugRequestStatus_Name,
                              RTrim(DR.DrugRequest_Name) as DrugRequest_Name,
                              Sum(case
                                    when DRR.DrugRequestType_id = 1 and (COALESCE(DRR.DrugRequestRow_Deleted, 1) = 1) then DRR.DrugRequestRow_Summa
                                    else 0
                                  end) as DrugRequest_SummaFed,
                              Sum(case
                                    when DRR.DrugRequestType_id = 2 and (COALESCE(DRR.DrugRequestRow_Deleted, 1) = 1) then DRR.DrugRequestRow_Summa
                                    else 0
                                  end) as DrugRequest_SummaReg,
                              Sum(case
                                    when DRR.DrugRequestType_id = 1 and (COALESCE(DRR.DrugRequestRow_Deleted, 1) = 1) and DRR.Person_id is null then DRR.DrugRequestRow_Summa
                                    else 0
                                  end) as DrugRequest_SummaFedReserve,
                              Sum(case
                                    when DRR.DrugRequestType_id = 2 and (COALESCE(DRR.DrugRequestRow_Deleted, 1) = 1) and DRR.Person_id is null then DRR.DrugRequestRow_Summa
                                    else 0
                                  end) as DrugRequest_SummaRegReserve,
                              DRR.Person_id,
                              COALESCE(DR.DrugRequest_YoungChildCount, 0) as DrugRequest_YoungChildCount,
                              count(distinct case
                                               when DRR.DrugRequestType_id = 1 then DRR.Person_id
                                               else Null
                                             end) *
                              (
                                SELECT normativ_fed_lgot
                                FROM cte2
                              ) * 3 *
                              (
                                SELECT koef_fed_lgot
                                FROM cte1
                              ) as DrugRequest_SummaFedLimit,
                              count(distinct case
                                               when DRR.DrugRequestType_id = 2 then DRR.Person_id
                                               else Null
                                             end) *
                              (
                                SELECT normativ_reg_lgot
                                FROM cte2
                              ) * 3 *
                              (
                                SELECT koef_reg_lgot
                                FROM cte1
                              ) as DrugRequest_SummaRegLimit,
                              DR.Lpu_id,
                              LP.Lpu_Nick,
                              DR.MedPersonal_id,
                              RTrim(Person_FIO) as MedPersonal_FIO,
                              DR.LpuSection_id,
                              RTrim(LpuSection_Name) as LpuSection_Name,
                              DR.PersonRegisterType_id,
                              MT.PersonRegisterType_Name,
                              DRK.DrugRequestKind_Name,
                              to_char(DR.DrugRequest_insDT, 'DD.MM.YYYY') as DrugRequest_insDT,
                              to_char(DR.DrugRequest_updDT, 'DD.MM.YYYY') as DrugRequest_updDT,
                              Null as DrugRequest_delDT
                       from v_DrugRequest DR
                            left join DrugRequestStatus on DrugRequestStatus.DrugRequestStatus_id = DR.DrugRequestStatus_id
                            left join DrugRequestPeriod on DrugRequestPeriod.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
                            left join v_MedPersonal MP on MP.MedPersonal_id = DR.MedPersonal_id and MP.Lpu_id = DR.lpu_id
                            left join v_Lpu LP on LP.Lpu_id = DR.lpu_id
                            left join v_LpuSection LpuSection on LpuSection.LpuSection_id = DR.LpuSection_id
                            left join v_PersonRegisterType MT on MT.PersonRegisterType_id = DR.PersonRegisterType_id
                            left join DrugRequestKind DRK on DRK.DrugRequestKind_id = DR.DrugRequestKind_id
                            left join DrugRequestRow DRR on DRR.DrugRequest_id = DR.DrugRequest_id
                       where 
                       		{$where}
                       group by DR.DrugRequest_id,
                                DR.DrugRequest_Version,
                                DR.DrugRequest_YoungChildCount,
                                DRR.Person_id,
                                DR.DrugRequestPeriod_id,
                                DrugRequestPeriod.DrugRequestPeriod_Name,
                                DrugRequestPeriod.DrugRequestPeriod_begDate,
                                DrugRequestPeriod.DrugRequestPeriod_endDate,
                                DR.DrugRequestStatus_id,
                                DrugRequestStatus.DrugRequestStatus_Name,
                                DR.DrugRequest_Name,
                                DR.Lpu_id,
                                LP.Lpu_Nick,
                                DR.MedPersonal_id,
                                Person_FIO,
                                DR.LpuSection_id,
                                LpuSection_Name,
                                DR.PersonRegisterType_id,
                                PersonRegisterType_Name,
                                DrugRequestKind_Name,
                                DR.DrugRequest_insDT,
                                DR.DrugRequest_updDT
                     ) as DrugRequest
                     LEFT JOIN LATERAL
                     (
                       select distinct DrugRequest.DrugRequest_id,
                              Sum(case
                                    when DrrAllLpu.DrugRequestType_id = 1 then DrrAllLpu.DrugRequestRow_Summa
                                    else 0
                                  end) as DRRSumFed,
                              Sum(case
                                    when DrrAllLpu.DrugRequestType_id = 2 then DrrAllLpu.DrugRequestRow_Summa
                                    else 0
                                  end) as DRRSumReg
                       from v_DrugRequestRow DrrAllLpu
                            inner join DrugRequest DRLpu on DRLpu.DrugRequest_id = DrrAllLpu.DrugRequest_id and DRLpu.DrugRequestPeriod_id = DrugRequest.DrugRequestPeriod_id
                       where DRLpu.Lpu_id != DrugRequest.Lpu_id and
                             DrrAllLpu.Person_id in (
                                                      Select DRR.Person_id
                                                      from DrugRequestRow DRR
                                                      where DRR.DrugRequest_id = DrugRequest.DrugRequest_id and
                                                            (COALESCE(DRR.DrugRequestRow_Deleted, 1) = 1)
                             )
                     ) as DRRLpu ON true 
                     {$lgot_count_join}
                group by DrugRequest.DrugRequest_id,
                         DrugRequest.DrugRequest_Version,
                         DrugRequestPeriod_id,
                         DrugRequestPeriod_Name,
                         DrugRequestStatus_id,
                         DrugRequestStatus_Name,
                         DrugRequest_Name,
                         {$lgot_count_group} 
                         DrugRequest_YoungChildCount,
                         Lpu_id,
                         Lpu_Nick,
                         COALESCE(DRRLpu.DRRSumFed, 0),
                         COALESCE(DRRLpu.DRRSumReg, 0),
                         MedPersonal_id,
                         MedPersonal_FIO,
                         LpuSection_id,
                         LpuSection_Name,
                         DrugRequest.PersonRegisterType_id,
                         PersonRegisterType_Name,
                         DrugRequestKind_Name,
                         DrugRequest_insDT,
                         DrugRequest_updDT,
                         DrugRequest_delDT
                order by DrugRequest.DrugRequest_id
				";
		}

		//print $sql;
		$res = $this->db->query($sql);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}
	/*
	
	function saveObjectRecord($data)
	{
		
		if ((isset($data['id'])) && ($data['id']>0))
		{
			// Update записи ... ну тут понятно парамсы и прочее 
			sql = "Update {$data['object']} set {} where {$data['object']}_id = $data['id']";
		}
	}
	*/

	/**
	 * Сохранение
	 */
	function SaveDrugRequestPerson($data)
	{
		$this->load->helper('Date');
		$this->load->helper('Options');
		if (!((isset($data['DrugRequestPerson_id'])) && (is_numeric($data['DrugRequestPerson_id'])) && ($data['DrugRequestPerson_id'] > 0)))
		{
			$data['DrugRequestPerson_id']=null;
			$proc = 'p_DrugRequestPerson_ins';
		}
		else
		{
			$proc = 'p_DrugRequestPerson_upd';
		}
		if (!empty($data['session']['server_id']))
		{
			$data['Server_id'] = $data['session']['server_id'];
		}
		elseif (!((isset($data['Server_id'])) && (is_numeric($data['Server_id'])) && ($data['Server_id'] >= 0)))
		{
			$data['Server_id'] = 0;
		}

		if (!empty($data['session']['pmuser_id']))
		{
			$data['pmUser_id'] = $data['session']['pmuser_id'];
		}
		elseif (!((isset($data['pmUser_id'])) && (is_numeric($data['pmUser_id'])) && ($data['pmUser_id'] >= 0)))
		{
			$data['pmUser_id'] = 0;
		}
		if (!empty($data['session']['lpu_id']))
		{
			$data['Lpu_id'] = $data['session']['lpu_id'];
		}
		elseif (!((isset($data['Lpu_id'])) && (is_numeric($data['Lpu_id'])) && ($data['Lpu_id'] >= 0)))
		{
			$data['Lpu_id'] = 0;
		}
		if (!isMinZdrav())
		{
			if (!((isset($data['MedPersonal_id'])) && (is_numeric($data['MedPersonal_id'])) && ($data['MedPersonal_id'] >= 0)))
			{
				return false;
			}
		}
		else 
		{
			if ((!isset($data['MedPersonal_id'])) && ($data['MedPersonal_id']==0))
				$data['MedPersonal_id'] = null;
		}
		if (!((isset($data['DrugRequestPeriod_id'])) && (is_numeric($data['DrugRequestPeriod_id'])) && ($data['DrugRequestPeriod_id'] >= 0)))
		{
			return false;
		}
		if (!((isset($data['Person_id'])) && (is_numeric($data['Person_id'])) && ($data['Person_id'] >= 0)))
		{
			return false;
		}
		
		if ($proc == 'p_DrugRequestPerson_ins') {
			$query = "
				select count(DrugRequestPerson_id) as \"cnt\"
				from v_DrugRequestPerson
				where DrugRequestPeriod_id =:DrugRequestPeriod_id and
					  Person_id =:Person_id and
					  Lpu_id =:Lpu_id and
					  (MedPersonal_id =:MedPersonal_id OR :MedPersonal_id IS NULL) and
					  (PersonRegisterType_id =:PersonRegisterType_id OR :PersonRegisterType_id IS NULL)
			";
			$result = $this->db->query($query, $data);
			$res = $result->result('array');
			if ($res && $res[0]['cnt'] > 0) {
				return array(array('Error_Code' => '999', 'Error_Msg' => 'Выбранный человек уже присутствует в заявке.'));
			}
		}
		
		$query = "
			select DrugRequestPerson_id as \"DrugRequestPerson_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Message\"
			from {$proc} (
            DrugRequestPerson_id := :DrugRequestPerson_id,
			Server_id := :Server_id,
			Lpu_id := :Lpu_id,
			Person_id := :Person_id,
			MedPersonal_id := :MedPersonal_id,
			DrugRequestPeriod_id := :DrugRequestPeriod_id,
			PersonRegisterType_id := :PersonRegisterType_id,
			pmUser_id := :pmUser_id);";
		
		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Сохранение позиции/позиций заявки медикамента
	 */
	function saveDrugRequestRow($data)
	{
		$this->load->helper('Date');
		$IsDrug = ((isset($data['IsDrug'])) && ($data['IsDrug']==1))?$data['IsDrug']:0;

		if ($data['Merge'] == 'true' && $data['DrugRequestRow_id'] <= 0) {
			$this->autoMergeDrugRequestRow($data);
		}

		if (!((isset($data['DrugRequestRow_id'])) && (is_numeric($data['DrugRequestRow_id'])) && ($data['DrugRequestRow_id'] > 0)))
		{
			$data['DrugRequestRow_id']= null;
			$proc = 'p_DrugRequestRow_ins';
		}
		else
		{
			$proc = 'p_DrugRequestRow_upd';
		}
		if (!((isset($data['DrugRequest_id'])) && (is_numeric($data['DrugRequest_id'])) && ($data['DrugRequest_id'] >= 0)))
			return false;
		if (!((isset($data['DrugRequestType_id'])) && (is_numeric($data['DrugRequestType_id'])) && ($data['DrugRequestType_id'] >= 0)))
			return false;
		if (!((isset($data['Person_id'])) && (is_numeric($data['Person_id'])) && ($data['Person_id'] > 0)))
			$data['Person_id'] = null;
		if (!((isset($data['DrugProtoMnn_id'])) && (is_numeric($data['DrugProtoMnn_id'])) && ($data['DrugProtoMnn_id'] >= 0)) && !((isset($data['DrugComplexMnn_id'])) && (is_numeric($data['DrugComplexMnn_id'])) && ($data['DrugComplexMnn_id'] >= 0)))
			return false;
		if (!((isset($data['DrugRequestRow_Kolvo'])) && (is_numeric($data['DrugRequestRow_Kolvo'])) && ($data['DrugRequestRow_Kolvo'] >= 0)))
			return false;

		if (!isset($data['DrugRequestRow_DoseOnce']) || empty($data['DrugRequestRow_DoseOnce'])) $data['DrugRequestRow_DoseOnce'] = null;
		if (!isset($data['DrugRequestRow_DoseDay']) || empty($data['DrugRequestRow_DoseDay'])) $data['DrugRequestRow_DoseDay'] = null;
		if (!isset($data['DrugRequestRow_DoseCource']) || empty($data['DrugRequestRow_DoseCource'])) $data['DrugRequestRow_DoseCource'] = null;
		if (!isset($data['Okei_oid']) || $data['Okei_oid'] <= 0) $data['Okei_oid'] = null;
		if (!isset($data['TRADENAMES_id']) || $data['TRADENAMES_id'] <= 0) $data['TRADENAMES_id'] = null;

		if (isset($data['DrugComplexMnn_id']) && $data['DrugComplexMnn_id'] > 0) {
			$price = 0;
			$pr_array = $this->getDrugRequestProperty($data['DrugRequest_id']);
			if (is_array($pr_array)) {
				$q = "
					select (case
							  when dlrt.DrugRequest_Price > 0 then dlrt.DrugRequest_Price
							  else dlr.DrugListRequest_Price
							end) as \"price\"
					from v_DrugListRequest dlr
						 left join v_DrugListRequestTorg dlrt on dlrt.DrugListRequest_id = dlr.DrugListRequest_id and dlrt.TRADENAMES_id =:TRADENAMES_id
					where dlr.DrugComplexMnn_id =:DrugComplexMnn_id and
						  dlr.DrugRequestProperty_id =:DrugRequestProperty_id
				";
				$r = $this->db->query($q, array(
					'DrugComplexMnn_id' => $data['DrugComplexMnn_id'],
					'TRADENAMES_id' => $data['TRADENAMES_id'],
					'DrugRequestProperty_id' => $data['DrugRequestType_id'] == 1 ? $pr_array['DrugRequestPropertyFed_id'] : $pr_array['DrugRequestPropertyReg_id']
				));
				if (is_object($r)) {
					$r = $r->result('array');
					if (isset($r[0]))
						$price = $r[0]['price'];
				}
			}
			$data['Drug_id'] = null;
			$data['DrugProtoMnn_id'] = null;

			$summa = Round($price*$data['DrugRequestRow_Kolvo'],2);
			if ($summa <= 0)
				$summa = 'Null';
		} else {
			$data['DrugComplexMnn_id'] = null;

			if ($IsDrug!=1) {
				// Здесь надо брать цену на данный медикамент МНН
				$data['Drug_id'] = null;
				$result = $this->getDrugProtoMnn($data);
				if ($result != false && count($result) > 0) {
					$summa = Round($result[0]['DrugProtoMnn_Price']*$data['DrugRequestRow_Kolvo'],2);
				} else {
					return false;
				}
			} else {
				// Здесь берем цену торгового наименования
				$data['Drug_id'] = $data['DrugProtoMnn_id'];
				$data['DrugProtoMnn_id'] = null;
				$result = $this->getDrug($data);
				if ($result != false && count($result) > 0) {
					$summa = Round($result[0]['DrugState_Price']*$data['DrugRequestRow_Kolvo'],2);
				} else {
					return false;
				}
			}
		}

		if (!empty($data['session']['pmuser_id'])) {
			$data['pmUser_id'] = $data['session']['pmuser_id'];
		} elseif (!((isset($data['pmUser_id'])) && (is_numeric($data['pmUser_id'])) && ($data['pmUser_id'] >= 0))) {
			$data['pmUser_id'] = 0;
		}

		$query = "
		WITH 
			cte1 AS (
				select DrugRequestPeriod_begDate,
					   DrugRequestPeriod_endDate
				from v_DrugRequestPeriod
				where DrugRequestPeriod_id =
					  (
						select DrugRequestPeriod_id
						from v_DrugRequest
						where DrugRequest_id =:DrugRequest_id
					  )),
			cte2 AS (            
				select DrugFinance_id
				from v_DrugFinance
				where ((DrugFinance_SysNick = 'fed' and
						  :DrugRequestType_id = 1) or
						  (DrugFinance_SysNick = 'reg' and
						  :DrugRequestType_id = 2)) and
						  (DrugFinance_begDate is null or
						  DrugFinance_begDate <=
						  (
							SELECT DrugRequestPeriod_endDate
							FROM cte1
						  )) and
						  (DrugFinance_endDate is null or
						  DrugFinance_endDate >=
						  (
							SELECT DrugRequestPeriod_begDate
							FROM cte1
						  ))
					limit 1)
		select DrugRequestRow_id as \"DrugRequestRow_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Message\"
		from {$proc} (
				DrugRequestRow_id := :DrugRequestRow_id,
				DrugRequest_id := :DrugRequest_id,
				DrugRequestType_id := :DrugRequestType_id,
				Person_id := :Person_id,
				DrugProtoMnn_id := :DrugProtoMnn_id,
				Drug_id := :Drug_id,
				DrugRequestRow_Kolvo := :DrugRequestRow_Kolvo,
				DrugRequestRow_Summa := {$summa},
				DrugRequestRow_KolDrugBuy := :DrugRequestRow_Kolvo,
				DrugRequestRow_SumBuy := {$summa},
				pmUser_id := :pmUser_id,
				DrugRequestRow_DoseOnce := :DrugRequestRow_DoseOnce,
				Okei_oid := :Okei_oid,
				DrugRequestRow_DoseDay := :DrugRequestRow_DoseDay,
				Okei_did := null,
				DrugRequestRow_DoseCource := :DrugRequestRow_DoseCource,
				Okei_cid := null,
				DrugComplexMnn_id := :DrugComplexMnn_id,
				TRADENAMES_id := :TRADENAMES_id,
				DrugFinance_id := (SELECT DrugFinance_id FROM cte2));";
		//print $query;
		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Сохранение заявки по врачу
	 */
	function SaveDrugRequest($data)
	{
		$this->load->helper('Date');
		$this->load->helper('Options');
		if (!((isset($data['DrugRequest_id'])) && (is_numeric($data['DrugRequest_id'])) && ($data['DrugRequest_id'] > 0)))
		{
			$drresult = $this->checkUniDrugRequest($data);
			if (is_array($drresult) && (count($drresult) == 1))
			{
				$data['DrugRequest_id'] = $drresult[0]['DrugRequest_id'];
				$data['DrugRequestStatus_id'] = $drresult[0]['DrugRequestStatus_id'];
				$data['DrugRequestTotalStatus_IsClose'] = $drresult[0]['DrugRequestTotalStatus_IsClose'];
			}
			else 
			{
				$data['DrugRequestTotalStatus_IsClose'] = 1;
				$data['DrugRequestStatus_id'] = 1;
			}
		}
		else 
		{
			$data['DrugRequestTotalStatus_IsClose'] = 1;
		}
		if (!((isset($data['DrugRequest_id'])) && (is_numeric($data['DrugRequest_id'])) && ($data['DrugRequest_id'] > 0)))
		{
			$data['DrugRequest_id']='Null';
			$proc = 'p_DrugRequest_ins';
		}
		else
		{
			$proc = 'p_DrugRequest_upd';
		}
		if (!isset($data['DrugRequest_YoungChildCount']))
			$data['DrugRequest_YoungChildCount'] = 0;

		if (!((isset($data['DrugRequestPeriod_id'])) && (is_numeric($data['DrugRequestPeriod_id'])) && ($data['DrugRequestPeriod_id'] >= 0)))
			return false;
		if (!((isset($data['DrugRequestStatus_id'])) && (is_numeric($data['DrugRequestStatus_id'])) && ($data['DrugRequestStatus_id'] >= 0)))
			return false;
		
		if (!empty($data['session']['server_id']))
		{
			$data['Server_id'] = $data['session']['server_id'];
		}
		elseif (!((isset($data['Server_id'])) && (is_numeric($data['Server_id'])) && ($data['Server_id'] >= 0)))
		{
			$data['Server_id'] = 0;
		}
		if (!empty($data['session']['lpu_id']))
		{
			$data['Lpu_id'] = $data['session']['lpu_id'];
		}
		elseif (!((isset($data['Lpu_id'])) && (is_numeric($data['Lpu_id'])) && ($data['Lpu_id'] >= 0)))
		{
			$data['Lpu_id'] = 0;
		}
		if (!isMinZdrav())
		{
			if (!((isset($data['MedPersonal_id'])) && (is_numeric($data['MedPersonal_id'])) && ($data['MedPersonal_id'] >= 0)))
				return false;
			if (!((isset($data['LpuSection_id'])) && (is_numeric($data['LpuSection_id'])) && ($data['LpuSection_id'] >= 0)))
				return false;
		}
		else 
		{
			if ((!isset($data['MedPersonal_id'])) || ($data['MedPersonal_id']==0))
				$data['MedPersonal_id'] = 'Null';
			if ((!isset($data['LpuSection_id'])) || ($data['LpuSection_id']==0))
				$data['LpuSection_id'] = 'Null';
		}

		if (!empty($data['session']['pmuser_id']))
		{
			$data['pmUser_id'] = $data['session']['pmuser_id'];
		}
		elseif (!((isset($data['pmUser_id'])) && (is_numeric($data['pmUser_id'])) && ($data['pmUser_id'] >= 0)))
		{
			$data['pmUser_id'] = 0;
		}
		
		if (!isset($data['PersonRegisterType_id']) || $data['PersonRegisterType_id'] <= 0)
			$data['PersonRegisterType_id'] = 'null';

		//Определяем категорию заявки
		$data['DrugRequestCategory_SysNick'] = null;
		if (isset($data['MedPersonal_id']) && $data['MedPersonal_id'] > 0) {
			$data['DrugRequestCategory_SysNick'] = 'vrach';
		} else if (isset($data['Lpu_id']) && $data['Lpu_id'] > 0) {
			$data['DrugRequestCategory_SysNick'] = 'mo';
		}
		
		$query = "
		select DrugRequest_id as \"DrugRequest_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Message\"
		from {$proc}( 
			DrugRequest_id := {$data['DrugRequest_id']},
			Server_id := {$data['Server_id']},
			DrugRequestPeriod_id := {$data['DrugRequestPeriod_id']},
			DrugRequestStatus_id := {$data['DrugRequestStatus_id']},
			DrugRequest_Name := '{$data['DrugRequest_Name']}',
			DrugRequest_YoungChildCount := '{$data['DrugRequest_YoungChildCount']}',
			Lpu_id := {$data['Lpu_id']},
			LpuSection_id := {$data['LpuSection_id']},
			MedPersonal_id := {$data['MedPersonal_id']},
			PersonRegisterType_id := {$data['PersonRegisterType_id']},
			DrugRequestCategory_id := (select DrugRequestCategory_id from v_DrugRequestCategory  where DrugRequestCategory_SysNick = '{$data['DrugRequestCategory_SysNick']}' limit 1),
			pmUser_id := {$data['pmUser_id']});";
		$result = $this->db->query($query);
		if (is_object($result))
		{
			$res = $result->result('array');
			$res[0]['DrugRequestStatus_id'] = $data['DrugRequestStatus_id'];
			$res[0]['DrugRequestTotalStatus_IsClose'] = $data['DrugRequestTotalStatus_IsClose'];
			return $res;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Обновление статуса заявки
	 */
	function updateDrugRequestStatus($data)
	{
		if ((!isset($data['DrugRequestTotalStatus_IsClose'])) && (isset($data['DrugRequestStatus_id'])))
		{
			$data['DrugRequestTotalStatus_IsClose'] = $data['DrugRequestStatus_id'];
		}
		$query = "
			Update DrugRequest 
				set DrugRequestStatus_id = ? --Подтвержденная
			where 
				DrugRequestPeriod_id = ? and 
				Lpu_id = ?
		";
		$params = array(
			$data['DrugRequestTotalStatus_IsClose'],
			$data['DrugRequestPeriod_id'],
			$data['Lpu_id']
		);

		$result = $this->db->query($query, $params);
		if (is_object($result))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Получение статуса заявки
	 */
	function getDrugRequestStatus($id, $data)
	{
		$params = array();
		$filter = "(1=1)";
		$filter = $filter." and DrugRequestRow.DrugRequestRow_id = :DrugRequestRow_id";
		$params['DrugRequestRow_id'] = $id;
		
		if (!isMinzdrav()) 
		{
			$filter = $filter." and DrugRequest.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['session']['lpu_id'];
		}
		
		$query = "
			Select DrugRequest.DrugRequestStatus_id as \"DrugRequestStatus_id\",
				   DrugRequest.Lpu_id as \"Lpu_id\"
			from v_DrugRequest DrugRequest
				 inner join DrugRequestRow on DrugRequestRow.DrugRequest_id = DrugRequest.DrugRequest_id
			where 
				{$filter}
			limit 1
		";
		$result = $this->db->query($query, $params);
		if (is_object($result))
		{
			$res = $result->result('array');
			if (count($res)>0)
			{
				// Запись есть
				return $res;
			}
			else 
				return array(array('DrugRequestStatus_id'=>null, 'Lpu_id'=>$data['session']['lpu_id']));
		}
		else
		{
			return array(array('DrugRequestStatus_id'=>null, 'Lpu_id'=>$data['session']['lpu_id']));
		}
	}

	/**
	 * Функция
	 */
	function updateDrugRequestRowDelete($id, $data)
	{
		$query = "
			Update DrugRequestRow
                set DrugRequestRow_Deleted = 2,
                    DrugRequestRow_delDT = dbo.tzGetDate(),
                    pmUser_delID =:pmUser_id
                where DrugRequestRow_id =:DrugRequestRow_id 
                returning 
                		DrugRequestRow_id as \"DrugRequestRow_id\",
                      	0 as \"Error_Code\",
                      	'' as \"Error_Msg\"
		";
		
		$result = $this->db->query($query, array(
			'DrugRequestRow_id' => $id,
			'pmUser_id' => $data['session']['pmuser_id']
		));
			
		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}
	}

	/**
	 * Функция
	 */
	function setDrugRequestLpuClose($data)
	{
		$this->load->helper('Date');
		$this->load->helper('Options');
		
		if (!((isset($data['DrugRequestTotalStatus_id'])) && (is_numeric($data['DrugRequestTotalStatus_id'])) && ($data['DrugRequestTotalStatus_id'] > 0)))
		{
			$drresult = $this->getDrugRequestLpuClose($data);
			if (is_array($drresult) && (count($drresult) == 1))
			{
				$data['DrugRequestTotalStatus_id'] = $drresult[0]['DrugRequestTotalStatus_id'];
				//$data['DrugRequestTotalStatus_IsClose'] = $drresult[0]['DrugRequestTotalStatus_IsClose'];
			}
		}
		
		if (!((isset($data['DrugRequestTotalStatus_id'])) && (is_numeric($data['DrugRequestTotalStatus_id'])) && ($data['DrugRequestTotalStatus_id'] > 0)))
		{
			$data['DrugRequestTotalStatus_id']='Null';
			$proc = 'p_DrugRequestTotalStatus_ins';
		}
		else
		{
			$proc = 'p_DrugRequestTotalStatus_upd';
		}
		
		if (!((isset($data['DrugRequestPeriod_id'])) && (is_numeric($data['DrugRequestPeriod_id'])) && ($data['DrugRequestPeriod_id'] >= 0)))
			return false;
		if (!((isset($data['DrugRequestTotalStatus_IsClose'])) && (is_numeric($data['DrugRequestTotalStatus_IsClose'])) && ($data['DrugRequestTotalStatus_IsClose'] >= 0)))
			return false;
		if (!empty($data['session']['server_id']))
		{
			$data['Server_id'] = $data['session']['server_id'];
		}
		elseif (!((isset($data['Server_id'])) && (is_numeric($data['Server_id'])) && ($data['Server_id'] >= 0)))
		{
			$data['Server_id'] = 0;
		}
		if (!((isset($data['Lpu_id'])) && (is_numeric($data['Lpu_id'])) && ($data['Lpu_id'] >= 0)))
		{
			if (!empty($data['session']['lpu_id']))
				$data['Lpu_id'] = $data['session']['lpu_id'];
			else 
				return false;
		}
		
		
		/*
		if (isMinZdrav() && ($data['Lpu_id']==$data['session']['lpu_id']))
		{
			return false;
		}
		*/
		
		if (!empty($data['session']['pmuser_id']))
		{
			$data['pmUser_id'] = $data['session']['pmuser_id'];
		}
		elseif (!((isset($data['pmUser_id'])) && (is_numeric($data['pmUser_id'])) && ($data['pmUser_id'] >= 0)))
		{
			$data['pmUser_id'] = 0;
		}
		$data['DrugRequestTotalStatus_closeDT'] = "Null";
		if ($data['DrugRequestTotalStatus_IsClose']==2)
		{
			$data['DrugRequestTotalStatus_closeDT'] = "dbo.tzGetDate()";
		}
		
		$query = "
		select DrugRequestTotalStatus_id as \"DrugRequestTotalStatus_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Message\"
		from {$proc} (
			DrugRequestTotalStatus_id := {$data['DrugRequestTotalStatus_id']},
			Server_id := {$data['Server_id']},
			DrugRequestPeriod_id := {$data['DrugRequestPeriod_id']},
			DrugRequestTotalStatus_IsClose := {$data['DrugRequestTotalStatus_IsClose']},
			DrugRequestTotalStatus_closeDT := {$data['DrugRequestTotalStatus_closeDT']},
			Lpu_id := {$data['Lpu_id']},
			pmUser_id := {$data['pmUser_id']})
		";
		$result = $this->db->query($query);
		if (is_object($result))
		{
			$res = $result->result('array');
			if ((count($res)>0) && ($res[0]['Error_Code']==0))
			{
				// Update статуса записей по заявкам 
				$result = $this->updateDrugRequestStatus($data);
			}
			$res[0]['DrugRequestTotalStatus_IsClose'] = $data['DrugRequestTotalStatus_IsClose'];
			return $res;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Сохранение
	 */
	function saveDrugRequestLpu($data)
	{
		$this->load->helper('Date');
		$this->load->helper('Options');
		
		if (!((isset($data['DrugRequestTotalStatus_id'])) && (is_numeric($data['DrugRequestTotalStatus_id'])) && ($data['DrugRequestTotalStatus_id'] > 0)))
		{
			$drresult = $this->getDrugRequestLpuClose($data);
			if (is_array($drresult) && (count($drresult) == 1))
			{
				$data['DrugRequestTotalStatus_id'] = $drresult[0]['DrugRequestTotalStatus_id'];
				//$data['DrugRequestTotalStatus_IsClose'] = $drresult[0]['DrugRequestTotalStatus_IsClose'];
			}
		}
		if (!((isset($data['DrugRequestTotalStatus_id'])) && (is_numeric($data['DrugRequestTotalStatus_id'])) && ($data['DrugRequestTotalStatus_id'] > 0)))
		{
			$data['DrugRequestTotalStatus_id']='Null';
			$proc = 'p_DrugRequestTotalStatus_ins';
		}
		else
		{
			$proc = 'p_DrugRequestTotalStatus_upd';
		}
		
		if (!((isset($data['DrugRequestPeriod_id'])) && (is_numeric($data['DrugRequestPeriod_id'])) && ($data['DrugRequestPeriod_id'] >= 0)))
			return false;

		if (!empty($data['session']['server_id']))
		{
			$data['Server_id'] = $data['session']['server_id'];
		}
		elseif (!((isset($data['Server_id'])) && (is_numeric($data['Server_id'])) && ($data['Server_id'] >= 0)))
		{
			$data['Server_id'] = 0;
		}
		if (!((isset($data['Lpu_id'])) && (is_numeric($data['Lpu_id'])) && ($data['Lpu_id'] >= 0)))
		{
			if (!empty($data['session']['lpu_id']))
				$data['Lpu_id'] = $data['session']['lpu_id'];
			else 
				return false;
		}

		if (!empty($data['session']['pmuser_id']))
		{
			$data['pmUser_id'] = $data['session']['pmuser_id'];
		}
		elseif (!((isset($data['pmUser_id'])) && (is_numeric($data['pmUser_id'])) && ($data['pmUser_id'] >= 0)))
		{
			$data['pmUser_id'] = 0;
		}
		
		$query = "
		select DrugRequestTotalStatus_id as \"DrugRequestTotalStatus_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Message\"
		from {$proc} (
			DrugRequestTotalStatus_id := {$data['DrugRequestTotalStatus_id']},
			Server_id := {$data['Server_id']},
			DrugRequestPeriod_id := {$data['DrugRequestPeriod_id']},
			Lpu_id := {$data['Lpu_id']},
			DrugRequestTotalStatus_FedLgotCount := {$data['DrugRequestTotalStatus_FedLgotCount']},
			DrugRequestTotalStatus_RegLgotCount := {$data['DrugRequestTotalStatus_RegLgotCount']},
			pmUser_id := {$data['pmUser_id']});";
		$result = $this->db->query($query);
		if (is_object($result))
		{
			$res = $result->result('array');
			if ((count($res)>0) && ($res[0]['Error_Code']==0))
			{
				// Update статуса записей по заявкам 
				$result = $this->updateDrugRequestStatus($data);
			}
			$res[0]['DrugRequestTotalStatus_IsClose'] = $data['DrugRequestTotalStatus_IsClose'];
			return $res;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Функция
	 */
	function setDrugRequestLpuUt($data)
	{
		$this->load->helper('Date');
		$this->load->helper('Options');
		if (!isMinZdrav())
		{
			return false;
		}
		if (!((isset($data['DrugRequestPeriod_id'])) && (is_numeric($data['DrugRequestPeriod_id'])) && ($data['DrugRequestPeriod_id'] >= 0)))
			return false;
		if (!((isset($data['DrugRequestStatus_id'])) && (is_numeric($data['DrugRequestStatus_id'])) && ($data['DrugRequestStatus_id'] >= 0)))
			return false;
		if (!empty($data['session']['server_id']))
		{
			$data['Server_id'] = $data['session']['server_id'];
		}
		elseif (!((isset($data['Server_id'])) && (is_numeric($data['Server_id'])) && ($data['Server_id'] >= 0)))
		{
			$data['Server_id'] = 0;
		}
		if (!((isset($data['Lpu_id'])) && (is_numeric($data['Lpu_id'])) && ($data['Lpu_id'] >= 0)))
		{
			if (!empty($data['session']['lpu_id']))
				$data['Lpu_id'] = $data['session']['lpu_id'];
			else 
				return false;
		}
		
		if (!empty($data['session']['pmuser_id']))
		{
			$data['pmUser_id'] = $data['session']['pmuser_id'];
		}
		elseif (!((isset($data['pmUser_id'])) && (is_numeric($data['pmUser_id'])) && ($data['pmUser_id'] >= 0)))
		{
			$data['pmUser_id'] = 0;
		}
		
		$result = $this->updateDrugRequestStatus($data);
		$result[0]['DrugRequestStatus_id'] = $data['DrugRequestStatus_id'];
		$result[0]['Error_Code'] = 0;
		$result[0]['Error_Message'] = '';
		return $result;
	}

	/**
	 * Функция
	 */
	function setDrugRequestLpuReallocated($data) {
        $reallocated = !empty($data['reallocated']);
        $status_id = null;

		$this->load->helper('Date');
		$this->load->helper('Options');

		if (!isMinZdrav()) {
			return false;
		}

		if (!((isset($data['Lpu_id'])) && (is_numeric($data['Lpu_id'])) && ($data['Lpu_id'] >= 0))) {
			if (!empty($data['session']['lpu_id'])) {
                $data['Lpu_id'] = $data['session']['lpu_id'];
            } else {
                return false;
            }
		}

		if (!empty($data['session']['pmuser_id'])) {
			$data['pmUser_id'] = $data['session']['pmuser_id'];
		} elseif (!((isset($data['pmUser_id'])) && (is_numeric($data['pmUser_id'])) && ($data['pmUser_id'] >= 0))) {
			$data['pmUser_id'] = 0;
		}

        if ($reallocated) {
            $query = "
                update
                    v_DrugRequest
                set
                    DrugRequestStatus_id = (select DrugRequestStatus_id from v_DrugRequestStatus  where DrugRequestStatus_Code = 5 limit 1) -- 5 - Перераспределение
                where
                    MedPersonal_id is not null and
                    DrugGroup_id is null and
                    PersonRegisterType_id is null and
                    DrugRequestStatus_id in ((select DrugRequestStatus_id from v_DrugRequestStatus  where DrugRequestStatus_Code = 2 limit 1), (select DrugRequestStatus_id from v_DrugRequestStatus  where DrugRequestStatus_Code = 3 limit 1)) and
                    DrugRequestPeriod_id = :DrugRequestPeriod_id and
                    Lpu_id = :Lpu_id
            ";
            $status_id = $this->getFirstResultFromQuery("select DrugRequestStatus_id  as \"DrugRequestStatus_id\" from v_DrugRequestStatus where DrugRequestStatus_Code = 5 limit 1");

        } else {
            $query = "
                update
                    v_DrugRequest
                set
                    DrugRequestStatus_id = (select DrugRequestStatus_id from v_DrugRequestStatus  where DrugRequestStatus_Code = 2 limit 1) -- 2 - Сформированная
                where
                    MedPersonal_id is not null and
                    DrugGroup_id is null and
                    PersonRegisterType_id is null and
                    DrugRequestStatus_id in ((select DrugRequestStatus_id from v_DrugRequestStatus  where DrugRequestStatus_Code = 5 limit 1), (select DrugRequestStatus_id from v_DrugRequestStatus  where DrugRequestStatus_Code = 3 limit 1) ) and
                    DrugRequestPeriod_id = :DrugRequestPeriod_id and
                    Lpu_id = :Lpu_id
            ";
            $status_id = $this->getFirstResultFromQuery("select DrugRequestStatus_id from v_DrugRequestStatus  where DrugRequestStatus_Code = 2 limit 1");

        }
        $params = array(
            'DrugRequestPeriod_id' => $data['DrugRequestPeriod_id'],
            'Lpu_id' => $data['Lpu_id']
        );
        $result = $this->db->query($query, $params);
        if (!is_object($result)) {
            return array(array('Error_Code' => null, 'Error_Message' => 'При смене статуса заявки произошла ошибка.'));
        }

        if ($reallocated) {
            //открываем заявку если нужно
            $query = "
                update
                    DrugRequestTotalStatus
                set
                    DrugRequestTotalStatus_IsClose = (select YesNo_id from v_YesNo  where YesNo_Code = 0 limit 1)
                where
                    DrugRequestPeriod_id = :DrugRequestPeriod_id and
                    Lpu_id = :Lpu_id
            ";
            $params = array(
                'DrugRequestPeriod_id' => $data['DrugRequestPeriod_id'],
                'Lpu_id' => $data['Lpu_id']
            );

            $result = $this->db->query($query, $params);
            if (!is_object($result)) {
                return array(array('Error_Code' => null, 'Error_Message' => 'При открытии заявки произошла ошибка.'));
            }
        } else {
            //ищем информацию о закрытии заявки
            $query = "
                select drts.DrugRequestTotalStatus_id as \"DrugRequestTotalStatus_id\",
					   is_close.YesNo_Code as \"isClose\"
				from v_DrugRequestTotalStatus drts
					 left join v_YesNo is_close on is_close.YesNo_id = drts.DrugRequestTotalStatus_IsClose
				where drts.DrugRequestPeriod_id =:DrugRequestPeriod_id and
					  drts.Lpu_id =:Lpu_id
            ";
            $params = array(
                'DrugRequestPeriod_id' => $data['DrugRequestPeriod_id'],
                'Lpu_id' => $data['Lpu_id']
            );
            $close_data = $this->getFirstRowFromQuery($query, $params);

            //закрываем заявку если нужно
            if (!empty($close_data['DrugRequestTotalStatus_id'])) {
                if ($close_data['isClose'] == 0) {
                    $query = "
                        update
                            DrugRequestTotalStatus
                        set
                            DrugRequestTotalStatus_IsClose = (select YesNo_id from v_YesNo  where YesNo_Code = 1 limit 1),
                            DrugRequestTotalStatus_closeDT = dbo.tzGetDate()
                        where
                            DrugRequestTotalStatus_id = :DrugRequestTotalStatus_id
                    ";
                    $params = array(
                        'DrugRequestTotalStatus_id' => $close_data['DrugRequestTotalStatus_id']
                    );
                    $result = $this->db->query($query, $params);
                    if (!is_object($result)) {
                        return array(array('Error_Code' => null, 'Error_Message' => 'При закрытии заявки произошла ошибка.'));
                    }
                }
            } else {
                $query = "
                    select DrugRequestTotalStatus_id as \"DrugRequestTotalStatus_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Message\"
                    from dbo.p_DrugRequestTotalStatus_ins (
                        DrugRequestTotalStatus_id := null,
                        Server_id := :Server_id,
                        DrugRequestPeriod_id := :DrugRequestPeriod_id,
                        DrugRequestTotalStatus_IsClose := (select YesNo_id from v_YesNo  where YesNo_Code = 1 limit 1),
                        DrugRequestTotalStatus_closeDT := dbo.tzGetDate(),
                        Lpu_id := :Lpu_id,
                        pmUser_id := :pmUser_id);
                ";
                $params = array(
                    'Server_id' => $data['Server_id'],
                    'DrugRequestPeriod_id' => $data['DrugRequestPeriod_id'],
                    'Lpu_id' => $data['Lpu_id'],
                    'pmUser_id' => $data['pmUser_id']
                );
                $result = $this->getFirstRowFromQuery($query, $params);
                if (!$result || !empty($result['Error_Message'])) {
                    return array(array('Error_Code' => $result['Error_Code'], 'Error_Message' => $result['Error_Message']));
                }
            }
        }

		$result = array();
		$result[0]['DrugRequestStatus_id'] = $status_id;
		$result[0]['Error_Code'] = 0;
		$result[0]['Error_Message'] = '';
		return $result;
	}

	/**
	 * Функция
	 */
	function getDrugProtoMnnFilter($data) {
		if (isOnko())
			$drugrequest_isonko = "(DrugProtoMnn_IsOnko=2)";
		elseif (isOnkoGem())
		{
			// и только определенный список медикаментов
			$drugrequest_isonko = "((DrugProtoMnn_IsOnko=2 and DrugProtoMnn.DrugProtoMnn_id in (2088,5350)) or (DrugProtoMnn_IsCommon=2))"; // Золедроновая кислота концентрат д/приг.р-ра д/инф. 4мг мл5
		}
		elseif (isRA())
			$drugrequest_isonko = "(DrugProtoMnn_IsRA=2)";
		elseif (isPsih())
			$drugrequest_isonko = "((DrugProtoMnn_IsCommon=2) or (DrugProtoMnn_IsCommon is null and DrugProtoMnn_IsOnko!=2) or (DrugProtoMnn_IsCrazy=2))";
		else 
			$drugrequest_isonko = "((DrugProtoMnn_IsCommon=2) or (DrugProtoMnn_IsCommon is null and DrugProtoMnn_IsOnko!=2))";

		$filter = $drugrequest_isonko; //"((DrugProtoMnn_IsCommon=2) or (DrugProtoMnn_IsCommon is null and COALESCE(DrugProtoMnn_IsOnko,0)!=2 and COALESCE(DrugProtoMnn_IsCrazy,0) != 2 and COALESCE(DrugProtoMnn_IsRA,0) != 2))";

		/*$filter .= "
		  OR (((COALESCE(DrugProtoMnn_IsCrazy,0) = 2) AND

				EXISTS (
					SELECT 1 FROM DrugRequestLpuGroup g
					WHERE g.DrugProtoMnn_id = DrugProtoMnn.DrugProtoMnn_id
						AND g.Lpu_id = :Lpu_id
						AND (COALESCE(g.medPersonal_id,:MedPersonal_id) = :MedPersonal_id)

					)
				)
		  OR ((COALESCE(DrugProtoMnn_IsOnko,0) = 2) AND

				EXISTS (
					SELECT 1 FROM DrugRequestLpuGroup g
					WHERE g.DrugProtoMnn_id = DrugProtoMnn.DrugProtoMnn_id
						AND g.Lpu_id = :Lpu_id
						AND (COALESCE(g.medPersonal_id,:MedPersonal_id) = :MedPersonal_id)

					)
				)
		  OR ((COALESCE(DrugProtoMnn_IsRA,0) = 2) AND

				EXISTS (
				SELECT 1 FROM DrugRequestLpuGroup g
					WHERE g.DrugProtoMnn_id = DrugProtoMnn.DrugProtoMnn_id
						AND g.Lpu_id = :Lpu_id
						AND (COALESCE(g.medPersonal_id,:MedPersonal_id) = :MedPersonal_id)

					)
				))";*/
		$filter .= "
		  OR (
			EXISTS (
				SELECT 1 FROM DrugRequestLpuGroup g 
				WHERE g.DrugProtoMnn_id = DrugProtoMnn.DrugProtoMnn_id
					AND g.Lpu_id = :Lpu_id
					AND (COALESCE(g.medPersonal_id,:MedPersonal_id) = :MedPersonal_id)
				)
			)";
				
		return "(".$filter.")";
	}

	/**
	 * Загрузка списка МНН
	 */
	function loadDrugProtoMnnList($data)
	{
		$where = '';
		$params = array();
		// Только для ОНКО
		// ??? может ли Минздрав выписывать онкопрепараты (скорее всего, да, уточнить)
		// Для Минздрава и Онко тоже
		if (isMinZdrav()) {
			$filter = "(1=1)";
		} else {
			$filter = $this->getDrugProtoMnnFilter($data);
			$params['Lpu_id'] = $data['Lpu_id'];
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}
		// Если запрос пустой, то не имеет смысла гонять данные
		/*if (((strlen($data['query']) == 0) && ($data['DrugProtoMnn_id'] == 0)) && (strlen($data['DrugProtoMnn_Name']) == 0))
		{
			return false;
		}*/

		/*
		if ($data['DrugMnn_id'] > 0)
		{
			$where .= " and DrugProtoMnn.DrugMnn_id = :DrugMnn_id ";
			$params['DrugMnn_id'] = $data['DrugMnn_id'];
		}
		if ($data['DrugProtoMnnType_id'] > 0)
		{
			$where .= " and DrugProtoMnn.DrugProtoMnnType_id = :DrugProtoMnnType_id";
			$params['DrugProtoMnnType_id'] = $data['DrugProtoMnnType_id'];
		}
		*/
		// Выписка по торговым наименованиям только для минздрава 
		
		if (($data['IsDrug']==1) && (isMinZdrav()))
		{
			switch ($data['ReceptFinance_id'])
			{
				case 1:
					$table = "v_DrugFed";
					break;
				case 2:
					$table = "v_DrugReg";
					break;
				default:
					return false;
					break;
			}
			
			if (strlen($data['query']) > 0)
			{
				$params['Drug_Name'] = $data['query'] . "%";
				$where .= " and Drug.Drug_Name iLIKE :Drug_Name";

			}
			
			if ($data['DrugProtoMnn_id'] > 0)
			{
				$where .= " and DrugProtoMnn.DrugProtoMnn_id = :Drug_id" ;
				$params['Drug_id'] = $data['DrugProtoMnn_id'];
			}
			$params['ReceptFinance_id'] = $data['ReceptFinance_id'];

			$query = "
				Select distinct Drug.Drug_id as \"DrugProtoMnn_id\",
					   Drug.Drug_Code as \"DrugProtoMnn_Code\",
					   RTRIM(Drug.Drug_Name) as \"DrugProtoMnn_Name\",
					   :ReceptFinance_id as \"ReceptFinance_id\",
					   ROUND(CAST (DrugPrice.DrugState_Price as numeric), 2) as \"DrugProtoMnn_Price\"
				From $table Drug
					 left join v_DrugPrice DrugPrice on DrugPrice.Drug_id = Drug.Drug_id and DrugPrice.DrugProto_begDate =
					 (
					   select max(DrugProto_begDate)
					   from v_DrugPrice
					   where Drug_id = Drug.Drug_id
					 )
				Where (1 = 1) 
					$where
				Order by \"DrugProtoMnn_Name\"
				limit 50
			";
		}
		else 
		{
			if ($data['DrugProtoMnn_id'] > 0)
			{
				$where .= " and DrugProtoMnn.DrugProtoMnn_id = :DrugProtoMnn_id" ;
				$params['DrugProtoMnn_id'] = $data['DrugProtoMnn_id'];
			}
			if ($data['ReceptFinance_id'] > 0)
			{
				$where .= " and DrugProtoMnn.ReceptFinance_id = :ReceptFinance_id";
				$params['ReceptFinance_id'] = $data['ReceptFinance_id'];
			}
			if (strlen($data['query']) > 0)
			{
				$where .= " and DrugProtoMnn.DrugProtoMnn_Name iLIKE :DrugProtoMnn_Name";

				$params['DrugProtoMnn_Name'] = $data['query'] . "%";
			}
			else 
			if (strlen($data['DrugProtoMnn_Name']) > 0)
			{
				$where .= " and DrugProtoMnn.DrugProtoMnn_Name iLIKE :DrugProtoMnn_Name";

				$params['DrugProtoMnn_Name'] = $data['DrugProtoMnn_Name'] . "%";
			}
			$wt = "";
			if ($data['DrugRequestPeriod_id'] > 0)
			{
				$wt = " and DrugProto.DrugRequestPeriod_id = :DrugRequestPeriod_id";
				$params['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];
			}
			if($data['ignoreOstat'] == 1)
			{
				$and_ostat = "";
			}
			else
			{
				$and_ostat = " and COALESCE(Ostat.DrugOstat_Kolvo, 0) = 0";

			}
			$query = "
				Select distinct DrugProtoMnn.DrugProtoMnn_id as \"DrugProtoMnn_id\",
					   --DrugProtoMnn.DrugProtoMnn_Code,
					   DrugProtoMnn.DrugProtoMnn_Name || ' - ' || COALESCE(cast (DrugProtoMnn.DrugProtoMnn_id as varchar), 'empty') as \"DrugProtoMnn_Name\",
					   --DrugProtoMnn.DrugMnn_id,
					   DrugProtoMnn.ReceptFinance_id as \"ReceptFinance_id\",
					   ROUND(CAST (DrugState.DrugState_Price as numeric), 2) as \"DrugProtoMnn_Price\"
				From v_DrugProtoMnn DrugProtoMnn
					 left join v_DrugState DrugState on DrugState.DrugProtoMnn_id = DrugProtoMnn.DrugProtoMnn_id
					 inner join v_DrugProto DrugProto on DrugState.DrugProto_id = DrugProto.DrugProto_id 
					 {$wt} 
					 /*
								left join DrugProtoRelation DPR on DPR.DrugProtoMnn_id = DrugProtoMnn.DrugProtoMnn_id
								left join DrugState on DrugState.Drug_id = DPR.Drug_id 
					 */
				Where (1 = 1) 
					$where and
					DrugProtoMnn_Name <> '~' and
					$filter
				Order by \"DrugProtoMnn_Name\"
				limit 50
			";
            if(isset($data['loadMnnList']) && $data['loadMnnList']=='2'){
                $query = "
					Select distinct Ostat.DrugOstat_Kolvo as \"DrugOstat_Kolvo\",
						   DrugProtoMnn.ReceptFinance_id as \"ReceptFinance_id\",
						   DrugProtoMnn.DrugProtoMnn_id as \"DrugProtoMnn_id\",
						   DM.DrugMnn_id as \"DrugMnn_id\",
						   --DM.DrugMnn_Name
						   DrugProtoMnn.DrugProtoMnn_Name as \"DrugMnn_Name\"
					From v_DrugProtoMnn DrugProtoMnn
						 left join v_DrugState DrugState on DrugState.DrugProtoMnn_id = DrugProtoMnn.DrugProtoMnn_id
						 inner join v_DrugProto DrugProto on DrugState.DrugProto_id = DrugProto.DrugProto_id 
						 {$wt}
						 inner join v_DrugMnn DM on DM.DrugMnn_id = DrugProtoMnn.DrugMnn_id
						 inner join v_Drug D on D.DrugMnn_id = DM.DrugMnn_id
						 LEFT JOIN LATERAL
						 (
						   select sum(D1.drugostat_kolvo) as DrugOstat_Kolvo
						   from v_Drug D
								inner join v_DrugOstat D1 on D1.Drug_id = D.Drug_id
						   where D.DrugMnn_id = DrugProtoMnn.DrugMnn_id and
								 D1.ReceptFinance_id = DrugProtoMnn.ReceptFinance_id
						 ) Ostat ON true
					Where (1 = 1) 
						  {$where} and
						  DM.DrugMnn_Name <> '~' {$and_ostat}
						  --and COALESCE(Ostat.DrugOstat_Kolvo, 0) = 0
						  and {$filter}
					Order by DrugProtoMnn.DrugProtoMnn_Name --DM.DrugMnn_Name
			";
            }
		}
		//
		/*
		if (isSuperAdmin() && ($data['session']['login']=='night')) {
			echo getDebugSql($query); 
			exit;
		}
		*/
        //echo getDebugSQL($query, $params);die;
		$result = $this->db->query($query, $params);
		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 * Получение цены медикамента МНН
	 */
	function getDrugProtoMnn($data)
	{
		$where = '';
		$params = array();
		
		if (isMinZdrav()) {
			$filter = "(1=1)";
		} else {
			$filter = $this->getDrugProtoMnnFilter($data);
			$params['Lpu_id'] = $data['session']['lpu_id'];
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}
		
		if ($data['DrugProtoMnn_id'] > 0)
		{
			$where .= " and DrugProtoMnn.DrugProtoMnn_id = " . $data['DrugProtoMnn_id'];
		}
		else 
			return false;

		if ($data['DrugRequestType_id'] > 0)
		{
			$where .= " and DrugProtoMnn.ReceptFinance_id = " . $data['DrugRequestType_id'];
		}
		
		$wt = "";
		if ($data['DrugRequestPeriod_id'] > 0)
		{
			$wt = " and DrugProto.DrugRequestPeriod_id = :DrugRequestPeriod_id";
			$params['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];
		}
			
		$query = "
			Select DrugProtoMnn.DrugProtoMnn_id as \"DrugProtoMnn_id\",
				   DrugProtoMnn.DrugProtoMnn_Code as \"DrugProtoMnn_Code\",
				   DrugProtoMnn.DrugProtoMnn_Name as \"DrugProtoMnn_Name\",
				   DrugProtoMnn.DrugMnn_id as \"DrugMnn_id\",
				   DrugProtoMnn.ReceptFinance_id as \"ReceptFinance_id\",
				   Round(CAST (DrugState.DrugState_Price as numeric), 2) as \"DrugProtoMnn_Price\"
			From DrugProtoMnn
				 LEFT JOIN LATERAL
				 (
				   select DrugState_Price,
						  DrugProto.DrugRequestPeriod_id
				   from DrugState
						left join DrugProto on DrugState.DrugProto_id = DrugProto.DrugProto_id
				   where DrugState.DrugProtoMnn_id = DrugProtoMnn.DrugProtoMnn_id 
				   {$wt}
				   order by DrugState_Price desc
				   limit 1
				 ) as DrugState ON true
			Where (1 = 1) $where and
				  {$filter}
			limit 1
		";
		
		$result = $this->db->query($query, $params);
		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}
	
	
	/**
	 * Получение цены медикамента торгового
	 */
	function getDrug($data)
	{
		$where = '';
		if ($data['Drug_id'] > 0)
		{
			$where .= " and Drug.Drug_id = " . $data['Drug_id'];
		}
		else 
			return false;

		switch ($data['DrugRequestType_id'])
		{
			case 1:
				$table = "v_DrugFed";
				break;
			case 2:
				$table = "v_DrugReg";
				break;
			default:
				return false;
				break;
		}
		
		$query = "
			Select Drug.Drug_id as \"Drug_id\",
				   Drug.Drug_Code as \"Drug_Code\",
				   RTRIM(Drug.Drug_Name) as \"Drug_Name\",
				   Drug.DrugMnn_id as \"DrugMnn_id\",
				   cast (DrugPrice.DrugState_Price as numeric (18, 2)) as \"DrugState_Price\"
			From " . $table . " Drug
				 left join v_DrugPrice DrugPrice on DrugPrice.Drug_id = Drug.Drug_id and DrugPrice.DrugProto_begDate =
				 (
				   select max(DrugProto_begDate)
				   from v_DrugPrice
				   where Drug_id = Drug.Drug_id
				 )
			Where (1 = 1) ".$where."
			limit 1";
		$result = $this->db->query($query);
		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Получение суммы заявки
	 */
	function getDrugRequestSum($data)
	{
		$where = '';
		if (!($data['DrugRequest_id'] > 0))
		{
			return false;
		}
		$query = "
			Select 
			sum(DrugRequestRow_Summa) as \"DrugRequest_Summa\"
			from DrugRequestRow 
			where DrugRequest_id = {$data['DrugRequest_id']}
		";
		$result = $this->db->query($query);
		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Проверка на уникальность пациента
	 */
	function checkUniPerson($data)
	{
		$this->load->helper('Options');
		$lpu = "(1=1)";
		$id = "(1=1)";
		$medpersonal = "(1=1)";
		$person_register_type = "(1=1)";
		if ((!isset($data['Person_id'])) || ($data['Person_id']==0))
			return false;
		if ((!isset($data['DrugRequestPeriod_id'])) || ($data['DrugRequestPeriod_id']==0))
			return false;
		if (!isMinZdrav())
		{
			if ((!isset($data['MedPersonal_id'])) || ($data['MedPersonal_id']==0))
				return false;
			$medpersonal = "DRP.MedPersonal_id = ".$data['MedPersonal_id'];
		}
		else 
		{
			$medpersonal = "DRP.MedPersonal_id is null";
		}
		if ((isset($data['DrugRequestPerson_id'])) && ($data['DrugRequestPerson_id']>0))
			$id = "DRP.DrugRequestPerson_id != ".$data['DrugRequestPerson_id'];
		if ((isset($data['session']['lpu_id'])) && ($data['session']['lpu_id']>0))
			$lpu = "DRP.Lpu_id = ".$data['session']['lpu_id'];
		if (isset($data['PersonRegisterType_id']) && $data['PersonRegisterType_id'] > 0) {
			$person_register_type = "DRP.PersonRegisterType_id = {$data['PersonRegisterType_id']}";
		}
		
		$sql = "
		Select 
			count(*) as \"record_count\"
			from DrugRequestPerson DRP 
			where
				DRP.Person_id = {$data['Person_id']} and 
				DRP.DrugRequestPeriod_id = {$data['DrugRequestPeriod_id']} and 
				{$medpersonal} and 
				{$person_register_type} and
				{$lpu} and
				{$id}
		";
		$res = $this->db->query($sql);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Проверка возможности удаление пациента (можно удалить только если на пациента нет медикаментов добавленных другими врачами)
	 */
	function checkDeletePersonByMedPersonal($data)
	{
		if ((!isset($data['id'])) || ($data['id']==0))
		{
			return false;
		}
		
		$sql = "
			Select 
				count(DRR.DrugRequestRow_id) as \"record_count\"
			from
				v_DrugRequestPerson DRP 
				inner join v_DrugRequestRow DRR  on DRR.Person_id = DRP.Person_id
				inner join v_DrugRequest DR  on DR.DrugRequest_id = DRR.DrugRequest_id 
			where
				DRP.DrugRequestPerson_id = :DrugRequestPerson_id and 
				DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id and 
				DR.MedPersonal_id <> :MedPersonal_id
			";
		$res = $this->db->query($sql, array(
			'DrugRequestPerson_id' => $data['id'],
			'MedPersonal_id' => $data['session']['medpersonal_id']
		));
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}
	
	/**
	 * Проверка
	 */
	function checkDeletePerson($data, $isremove)
	{
		$this->load->helper('Options');
		$lpu = "(1=1)";
		$medpersonal = "(1=1)";
		if ((!isset($data['id'])) || ($data['id']==0))
		{
			return false;
		}
		$id = $data['id'];
		if (!isMinZdrav())
		{
			$medpersonal = "DRP.MedPersonal_id = DR.MedPersonal_id";
		}
		if ((isset($data['session']['lpu_id'])) && ($data['session']['lpu_id']>0))
			$lpu = "DRP.Lpu_id = ".$data['session']['lpu_id'];
		// Как бы еще думать надо  .... это не лучший вариант
		if ($isremove)
		{
			$sql = "
			Delete from DrugRequestRow 
			using DrugRequestRow DRR
					left join DrugRequestPerson DRP  on DRR.Person_id = DRP.Person_id 
					left join DrugRequest DR  on DR.DrugRequest_id = DRR.DrugRequest_id 
					where
						DRP.DrugRequestPerson_id = {$id} and 
						DRP.Person_id = DRR.Person_id and 
						DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id and 
						{$medpersonal} and 
						DRR.DrugRequestRow_id is not null and
						{$lpu}";
			$res = $this->db->query($sql);
		}
		
		$sql = "
		Select 
			count(DRR.DrugRequestRow_id) as \"record_count\"
			from DrugRequestPerson DRP 
			left join DrugRequestRow DRR  on DRR.Person_id = DRP.Person_id 
			left join DrugRequest DR  on DR.DrugRequest_id = DRR.DrugRequest_id 
			where
				DRP.DrugRequestPerson_id = {$id} and 
				DRP.Person_id = DRR.Person_id and 
				DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id and 
				{$medpersonal} and 
				DRR.DrugRequestRow_id is not null and
				{$lpu}";
		$res = $this->db->query($sql);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Проверка
	 */
	function checkDeleteDrugRequest($data)
	{
		$this->load->helper('Options');
		$lpu = "(1=1)";
		if ((!isset($data['id'])) || ($data['id']==0))
		{
			return false;
		}
		$id = $data['id'];
		if (!isMinZdrav())
		{
			if ((isset($data['session']['lpu_id'])) && ($data['session']['lpu_id']>0))
				$lpu = "DR.Lpu_id = ".$data['session']['lpu_id'];
		}
		
		$sql = "
		Select 
			DR.DrugRequest_id as \"DrugRequest_id\",
			DR.DrugRequestStatus_id as \"DrugRequestStatus_id\",
			DRS.DrugRequestStatus_Name as \"DrugRequestStatus_Name\"
			from v_DrugRequest DR 
			left join DrugRequestStatus DRS  on DRS.DrugRequestStatus_id = DR.DrugRequestStatus_id 
			where
				DR.DrugRequest_id = {$id} and 
				{$lpu}";
		$res = $this->db->query($sql);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}
	
	/**
	 * Проверка на уникальность заявки
	 */
	function checkUniDrugRequest($data)
	{
		$this->load->helper('Options');
		$lpu = "(1=1)";
		$id = "(1=1)";
		$medpersonal = "(1=1)";
		$person_register = "(1=1)";
		if ((!isset($data['DrugRequestPeriod_id'])) || ($data['DrugRequestPeriod_id']==0))
			return false;
		if (!isMinZdrav())
		{
			if ((!isset($data['MedPersonal_id'])) || ($data['MedPersonal_id']==0))
				return false;
			$medpersonal = "DR.MedPersonal_id = ".$data['MedPersonal_id'];
		}
		else 
		{
			$medpersonal = "DR.MedPersonal_id is null";
		}
		
		if ((isset($data['session']['lpu_id'])) && ($data['session']['lpu_id']>0))
			$lpu = "DR.Lpu_id = ".$data['session']['lpu_id'];
		if ((isset($data['DrugRequest_id'])) && ($data['DrugRequest_id']>0))
			$id = "DR.DrugRequest_id != ".$data['DrugRequest_id'];
		if ((isset($data['PersonRegisterType_id'])) && ($data['PersonRegisterType_id']>0))
			$person_register = "DR.PersonRegisterType_id = ".$data['PersonRegisterType_id'];
			
		$sql = "
		Select
			--count(*) as record_count
			DR.DrugRequest_id as \"DrugRequest_id\", 
			DR.DrugRequestStatus_id as \"DrugRequestStatus_id\",
			DRTS.DrugRequestTotalStatus_IsClose as \"DrugRequestTotalStatus_IsClose\"
		from v_DrugRequest DR 
			left join DrugRequestTotalStatus DRTS  on DRTS.Lpu_id = DR.Lpu_id and DR.DrugRequestPeriod_id = DRTS.DrugRequestPeriod_id
		where
				DR.DrugRequestPeriod_id = {$data['DrugRequestPeriod_id']} and 
				{$medpersonal} and 
				{$lpu} and 
				{$id} and
				{$person_register}
			limit 1
		";
		$res = $this->db->query($sql);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}
	
	/**
	 * Проверка на уникальность медикамента в заявке
	 */
	function checkUniDrugRequestRow($data)
	{
		$lpu = "(1=1)";
		$id = "(1=1)";
		$person = "(1=1)";
		if ((!isset($data['DrugProtoMnn_id']) || $data['DrugProtoMnn_id']==0) && (!isset($data['DrugComplexMnn_id']) || $data['DrugComplexMnn_id']==0))
			return false;
		if ((!isset($data['DrugRequest_id'])) || ($data['DrugRequest_id']==0))
			return false;
		if ((!isset($data['Person_id'])) || ($data['Person_id']==0))
		{	
			$person = "DRR.Person_id is null";
			if ((isset($data['session']['lpu_id'])) && ($data['session']['lpu_id']>0))
				$lpu = "DR.Lpu_id = ".$data['session']['lpu_id'];
		}
		else 
		{
			$person = "DRR.Person_id = ".$data['Person_id'];
		}
		if ((!isset($data['DrugRequestType_id'])) || ($data['DrugRequestType_id']==0))
			return false;
		if ((isset($data['DrugRequestRow_id'])) && ($data['DrugRequestRow_id']>0))
			$id = "DRR.DrugRequestRow_id != ".$data['DrugRequestRow_id'];

		if (empty($data['DrugProtoMnn_id'])) {
			$data['DrugProtoMnn_id'] = 0;
		}
		if (empty($data['DrugComplexMnn_id'])) {
			$data['DrugComplexMnn_id'] = 0;
		}
		
		$sql = "
		Select 
			count(*) as \"record_count\",
			DR.DrugRequestStatus_id as \"DrugRequestStatus_id\"
		from v_DrugRequestRow DRR 
			inner join DrugRequest DR  on DR.DrugRequest_id = DRR.DrugRequest_id
		where
				(
					DRR.DrugProtoMnn_id = '{$data['DrugProtoMnn_id']}' or
					DRR.DrugComplexMnn_id = '{$data['DrugComplexMnn_id']}'
				) and
				DRR.DrugRequest_id = {$data['DrugRequest_id']} and
				DRR.DrugRequestType_id = {$data['DrugRequestType_id']} and
				{$person} and
				{$lpu} and
				{$id}
		group by
				DR.DrugRequestStatus_id
		";

		$res = $this->db->query($sql);
		if (is_object($res))
			return $res->result('array');
		else
		{
			return false;
		}
	}

	/**
	 * Проверка на наличие записей о закрытии заявки ЛПУ
	 */
	function checkDrugRequestLpuClosed($data) {
		$sql = "
			select
				count(DrugRequestTotalStatus_id) as \"record_count\"
			from
				DrugRequestTotalStatus 
			where
				DrugRequestPeriod_id = :DrugRequestPeriod_id
				and Lpu_id = :Lpu_id
				and DrugRequestTotalStatus_IsClose = 2
		";

		$res = $this->db->query($sql, $data);
		if (is_object($res)) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Проверка на наличие заявок врачей со статусом "Перераспределение" в рамках заявки ЛПУ
	 */
	function checkDrugRequestLpuReallocated($data) {
		$sql = "
            select
                count(dr.DrugRequest_id) as \"record_count\"
            from
                v_DrugRequest dr 
                left join v_DrugRequestStatus drs  on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
            where
                dr.MedPersonal_id is not null and
                dr.DrugGroup_id is null and
                dr.PersonRegisterType_id is null and
                drs.DrugRequestStatus_Code = 5 and --5 - Перераспределение
                dr.DrugRequestPeriod_id = {$data['DrugRequestPeriod_id']} and
                dr.Lpu_id = {$data['Lpu_id']}
		";

		$res = $this->db->query($sql);
		if (is_object($res)) {
            return $res->result('array');
        } else {
			return false;
		}
	}

	/**
	 * Проверка
	 */
	function checkUniAllLpuDrugRequestRow($data)
	{
		// Проверка на введенный медикамент на этого человека в этот период
		if ((!isset($data['DrugProtoMnn_id'])) || ($data['DrugProtoMnn_id']==0))
			return false;
		if ((!isset($data['Person_id'])) || ($data['Person_id']==0))
			return false;
		if ((!isset($data['DrugRequestPeriod_id'])) || ($data['DrugRequestPeriod_id']==0))
			return false;
		$filters = "(1=1) ";
		if ((isset($data['DrugRequestRow_id'])) && ($data['DrugRequestRow_id']>0))
			$id = "DRR.DrugRequestRow_id != ".$data['DrugRequestRow_id'];
		
		$params['DrugProtoMnn_id'] = $data['DrugProtoMnn_id'];
		$params['DrugRequestRow_id'] = ($data['DrugRequestRow_id']==null)?0:$data['DrugRequestRow_id'];
		$params['Person_id'] = $data['Person_id'];
		$params['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];
		
		$sql = "
		Select 
			count(*) as \"record_count\"
			from v_DrugRequestRow DRR 
			inner join DrugRequest DR  on DR.DrugRequest_id = DRR.DrugRequest_id
			where
				DRR.DrugProtoMnn_id = :DrugProtoMnn_id and
				DRR.DrugRequestRow_id != :DrugRequestRow_id and 
				DRR.Person_id = :Person_id and 
				DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
		";
		/*
		echo getDebugSql($sql, $params);
		exit;
		*/
		$res = $this->db->query($sql, $params);
		if (is_object($res))
			return $res->result('array');
		else
		{
			return false;
		}
	}
	
	
	/**
	 * Проверка на запись именно медикамента ЛПУ
	 */
	function checkSelfDrugRequestRow($data)
	{
		$lpu = "(1=1)";
		$id = "(1=1)";
		if ((isset($data['DrugRequestRow_id'])) && ($data['DrugRequestRow_id']>0))
			$id = "DRR.DrugRequestRow_id = ".$data['DrugRequestRow_id'];
		
		if ((isset($data['session']['lpu_id'])) && ($data['session']['lpu_id']>0))
			$lpu = "DR.Lpu_id != ".$data['session']['lpu_id'];
		
		$sql = "
		Select 
			count(*) as \"record_count\"
			from DrugRequestRow DRR 
			inner join DrugRequest DR  on DR.DrugRequest_id = DRR.DrugRequest_id
			where
				{$lpu} and {$id}
		";
		$res = $this->db->query($sql);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}
	
	
	/**
	 * Проверка на наличие открытой заявки региона и заявки МО для сохраняемой заявки врача
	 */
	function checkExistParentDrugRequest($data) {		
		$q = "
			select
				sum(case when Lpu_id is null then 1 end) as \"region_request_count\",
				sum(case
					when
						Lpu_id is null and
						DrugRequestStatus_Code = 1
					then 1
				end) as \"region_correct_status\",
				sum(case when Lpu_id = :Lpu_id then 1 end) as \"mo_request_count\",
				sum(case
					when
						Lpu_id = :Lpu_id and
						DrugRequestStatus_Code in (1, 2)
					then 1
				end) as \"mo_correct_status\"
			from
				DrugRequest dr 
				left join DrugRequestStatus drs  on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
			where
				DrugRequestPeriod_id = :DrugRequestPeriod_id and
				PersonRegisterType_id = :PersonRegisterType_id and
				MedPersonal_id is null and
				DrugRequest_Version is null
		";
		$result = $this->db->query($q, $data);
		if (is_object($result))
			return $result->result('array');
		else
			return false;
	}

	/**
	 * Удаление
	 */
	function deleteDrugRequestPerson($data)
	{
		if (isset($data['object']))
			$object = $data['object'];
		else 
			return false;
		if ($data['id'] <= 0)
		{
			return false;
		}
		
		$query = "
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Message\"
			from p_{$object}_del(:id);
		";
		$res = $this->db->query(
			$query,
			array(
				'id' => $data['id']
			)
		);
		if (is_object($res))
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 * Удаление заявки
	 */
	function deleteDrugRequest($data)
	{
		if (isset($data['object']))
			$object = $data['object'];
		else 
			return false;
		if ($data['id'] <= 0)
		{
			return false;
		}
		if (!isSuperAdmin())
		{
			$res = array(
				array(
					'Error_Code'=>'100010',
					'Error_Message'=>'У вас недостаточно прав для удаления заявки!'
				)
			);
			return $res;
		}
		$query = "
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Message\"
			from p_{$object}_del(:id);
		";
		$res = $this->db->query(
			$query,
			array(
				'id' => $data['id']
			)
		);
		if (is_object($res))
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 * Удаление строки заявки
	 */
	function deleteDrugRequestRow($data)
	{
		$id = $data['id'];
		if (isset($data['object']))
			$object = $data['object'];
		else 
			return false;
		if ($id <= 0)
		{
			return false;
		}
		$getdata = $this->getDrugRequestStatus($id, $data);
		if ((isset($getdata[0]['DrugRequestStatus_id'])) && ($getdata[0]['DrugRequestStatus_id']==3))
		{
			// Ставим пометку на удаление
			return $this->updateDrugRequestRowDelete($id, $data);
		}
		else 
		{
			// ID ЛПУ позиции медикамента 
			if (($getdata[0]['Lpu_id'] != $data['session']['lpu_id']) && (!isMinzdrav()))
			{
				$res = array(array('Error_Code'=>100010, 'Error_Message'=>'У вас нет прав для удаления данного медикамента!'));
				return $res;
			}
			if (($getdata[0]['DrugRequestStatus_id']!= 1) && (!isMinzdrav()))
			{
				$res = array(array('Error_Code'=>100010, 'Error_Message'=>'У вас нет прав для удаления данного медикамента!'));
				return $res;
			}
			
			$query = "
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Message\"
			from p_DrugRequestRow_del(:id, :pmUser_id, 2);
			";
			$res = $this->db->query($query, array('id'=>$id, 'pmUser_id'=>$data['pmUser_id']));
			if (is_object($res))
			{
				return $res->result('array');
			}
			else
			{
				return false;
			}
		}
	}

	/**
	* Получение данных для печати заявки "Печать с группировкой по медикаментам, заявленным врачами ЛПУ"
	*/
	function getPrintDrugRequestData1($data) {
		$filter = "(1 = 1)";
		$params = array();

		$params['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];
		$params['DrugRequestRow_actDate'] = $data['DrugRequestRow_actDate'] . ' 23:59:59.000';
		$params['DrugRequestType_id'] = $data['DrugRequestType_id'];

		if ( $data['Lpu_id'] > 0 ) {
			$filter .= " and DR.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		if ( $data['LpuSection_id'] > 0 ) {
			$filter .= " and LS.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ( $data['LpuUnit_id'] > 0 ) {
			$filter .= " and LS.LpuUnit_id = :LpuUnit_id";
			$params['LpuUnit_id'] = $data['LpuUnit_id'];
		}

		if ( $data['MedPersonal_id'] > 0 ) {
			$filter .= " and DR.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}
		/*
			COALESCE((select top 1 case when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)

		*/
		$query = "
			select RTRIM(COALESCE(CAST(DPM.DrugProtoMnn_Code as varchar), CAST(Drug.Drug_Code as varchar), '')) as \"DrugRequestRow_Code\",
                 RTRIM(COALESCE(DPM.DrugProtoMnn_Name, Drug.Drug_Name, '')) as \"DrugRequestRow_Name\",
                 SUM(COALESCE(DRR.DrugRequestRow_Kolvo, 0)) as \"DrugRequestRow_Kolvo\",
                 ROUND(SUM(CAST(DRR.DrugRequestRow_Summa as numeric)) / SUM(COALESCE(CAST(DRR.DrugRequestRow_Kolvo as numeric), 0)), 2) as \"DrugRequestRow_Price\",
                 SUM(DRR.DrugRequestRow_Summa) as \"DrugRequestRow_Summa\"
          from DrugRequestRow DRR
               inner join DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
               left join DrugProtoMnn DPM on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
               left join Drug on Drug.Drug_id = DRR.Drug_id
               left join LpuSection LS on LS.LpuSection_id = DR.LpuSection_id
          where 
	          " . $filter . " and
                DRR.DrugRequestType_id = :DrugRequestType_id and
                (DRR.DrugRequestRow_delDT is null or
                DRR.DrugRequestRow_delDT > COALESCE((
                                                      select case
                                                               when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                               else CAST (:DrugRequestRow_actDate as timestamp)
                                                             end as actualDate
                                                      from DrugRequestTotalStatus DRTS
                                                      where DRTS.Lpu_id = DR.Lpu_id and
                                                            DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
                                                      limit 1
                ), case
                     when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                     else dbo.tzGetDate()
                   end)) and
                COALESCE(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= COALESCE((
                                                                                           select case
                                                                                                    when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                                    else CAST (:DrugRequestRow_actDate as timestamp)
                                                                                                  end as actualDate
                                                                                           from DrugRequestTotalStatus DRTS
                                                                                           where DRTS.Lpu_id = DR.Lpu_id and
                                                                                                 DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
                                                                                           limit 1
                ), case
                     when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                     else dbo.tzGetDate()
                   end)
          group by DPM.DrugProtoMnn_Code,
                   DPM.DrugProtoMnn_Name,
                   Drug.Drug_Code,
                   Drug.Drug_Name
          order by \"DrugRequestRow_Name\"
		";
		// echo getDebugSQL($query, $params); exit();
		$res = $this->db->query($query, $params);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	* Получение данных для печати заявки "Печать с группировкой по пациентам из заявок врачей ЛПУ"
	*/
	function getPrintDrugRequestData2($data) {
		$filter = "(1 = 1)";
		$params = array();

		$params['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];
		$params['DrugRequestRow_actDate'] = $data['DrugRequestRow_actDate'] . ' 23:59:59.000';
		$params['DrugRequestType_id'] = $data['DrugRequestType_id'];

		if ( $data['Lpu_id'] > 0 ) {
			$filter .= " and DR.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		if ( $data['LpuSection_id'] > 0 ) {
			$filter .= " and LS.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ( $data['LpuUnit_id'] > 0 ) {
			$filter .= " and LS.LpuUnit_id = :LpuUnit_id";
			$params['LpuUnit_id'] = $data['LpuUnit_id'];
		}

		if ( $data['MedPersonal_id'] > 0 ) {
			$filter .= " and DR.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		$query = "
			select RTRIM(COALESCE(PS.Person_Surname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Firname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Secname, '')) as \"Person_Fio\",
               SUM(DRR.DrugRequestRow_Summa) as \"DrugRequestRow_Summa\"
        from DrugRequestRow DRR
             inner join DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
             -- inner join DrugProtoMnn DPM  on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
             left join LpuSection LS on LS.LpuSection_id = DR.LpuSection_id
             left join v_PersonState PS on PS.Person_id = DRR.Person_id
        where 
        " . $filter . " and
              DRR.DrugRequestType_id = :DrugRequestType_id and
              (DRR.DrugRequestRow_delDT is null or
              DRR.DrugRequestRow_delDT > COALESCE((
                                                    select case
                                                                   when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                   else CAST (:DrugRequestRow_actDate as timestamp)
                                                                 end as actualDate
                                                    from DrugRequestTotalStatus DRTS
                                                    where DRTS.Lpu_id = DR.Lpu_id and
                                                          DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
                                                    limit 1
              ), case
                   when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                   else dbo.tzGetDate()
                 end)) and
              COALESCE(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= COALESCE((
                                                                                         select case
                                                                                                        when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                                        else CAST (:DrugRequestRow_actDate as timestamp)
                                                                                                      end as actualDate
                                                                                         from DrugRequestTotalStatus DRTS
                                                                                         where DRTS.Lpu_id = DR.Lpu_id and
                                                                                               DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
                                                                                         limit 1
              ), case
                   when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                   else dbo.tzGetDate()
                 end)
        group by PS.Person_id,
                 PS.Person_Surname,
                 PS.Person_Firname,
                 PS.Person_Secname
        order by PS.Person_Surname,
                 PS.Person_Firname,
                 PS.Person_Secname
		";
		// echo getDebugSQL($query, $params); exit();
		$res = $this->db->query($query, $params);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	* Получение данных для печати заявки "Печать с группировкой по пациенту и со списком медикаментов"
	*/
	function getPrintDrugRequestData3($data) {
		$filter = "(1 = 1)";
		$params = array();

		$params['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];
		$params['DrugRequestRow_actDate'] = $data['DrugRequestRow_actDate'] . ' 23:59:59.000';
		$params['DrugRequestType_id'] = $data['DrugRequestType_id'];

		if ( $data['Lpu_id'] > 0 ) {
			$filter .= " and DR.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		if ( $data['LpuSection_id'] > 0 ) {
			$filter .= " and LS.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ( $data['LpuUnit_id'] > 0 ) {
			$filter .= " and LS.LpuUnit_id = :LpuUnit_id";
			$params['LpuUnit_id'] = $data['LpuUnit_id'];
		}

		if ( $data['MedPersonal_id'] > 0 ) {
			$filter .= " and DR.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		$query = "
			select COALESCE(PS.Person_id, 0) as \"Person_id\",
				   RTRIM(COALESCE(PS.Person_Surname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Firname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Secname, '')) as \"Person_Fio\",
				   RTRIM(COALESCE(CAST(DPM.DrugProtoMnn_Code as varchar), CAST(Drug.Drug_Code as varchar), '')) as \"DrugRequestRow_Code\",
				   RTRIM(COALESCE(DPM.DrugProtoMnn_Name, Drug.Drug_Name, '')) as \"DrugRequestRow_Name\",
				   SUM(COALESCE(DRR.DrugRequestRow_Kolvo, 0)) as \"DrugRequestRow_Kolvo\",
				   ROUND(SUM(CAST(DRR.DrugRequestRow_Summa as numeric)) / SUM(COALESCE(CAST(DRR.DrugRequestRow_Kolvo as numeric), 0)), 2) as \"DrugRequestRow_Price\",
				   SUM(COALESCE(DRR.DrugRequestRow_Summa, 0)) as \"DrugRequestRow_Summa\"
			from DrugRequestRow DRR
				 inner join DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
				 left join DrugProtoMnn DPM on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
				 left join Drug Drug on Drug.Drug_id = DRR.Drug_id
				 left join LpuSection LS on LS.LpuSection_id = DR.LpuSection_id
				 left join v_PersonState PS on PS.Person_id = DRR.Person_id
			where 
			" . $filter . " and
				  DRR.DrugRequestType_id = :DrugRequestType_id and
				  (DRR.DrugRequestRow_delDT is null or
				  DRR.DrugRequestRow_delDT > COALESCE((
														select case
																	   when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																	   else CAST (:DrugRequestRow_actDate as timestamp)
																	 end as actualDate
														from DrugRequestTotalStatus DRTS
														where DRTS.Lpu_id = DR.Lpu_id and
															  DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
														limit 1
				  ), case
					   when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
					   else dbo.tzGetDate()
					 end)) and
				  COALESCE(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= COALESCE((
																							 select case
																											when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																											else CAST (:DrugRequestRow_actDate as timestamp)
																										  end as actualDate
																							 from DrugRequestTotalStatus DRTS
																							 where DRTS.Lpu_id = DR.Lpu_id and
																								   DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
																							 limit 1
				  ), case
					   when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
					   else dbo.tzGetDate()
					 end)
			group by PS.Person_id,
					 PS.Person_Surname,
					 PS.Person_Firname,
					 PS.Person_Secname,
					 DPM.DrugProtoMnn_Code,
					 DPM.DrugProtoMnn_Name,
					 Drug.Drug_Code,
					 Drug.Drug_Name
			order by \"Person_Fio\"
		";
		// echo getDebugSQL($query, $params); exit();
		$res = $this->db->query($query, $params);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	* Получение данных для печати заявки "Итоговые сведения по заявке ЛПУ"
	*/
	function getPrintDrugRequestData4($data, $options) {
		$filter = "(1 = 1)";
		$join_str = "";
		$params = array();
		$person_refuse_filter = "";
		$privilege_filter = "";
		$select_fields = "";
		$young_child_set = "";

		$params['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];
		$params['DrugRequestRow_actDate'] = $data['DrugRequestRow_actDate'] . ' 23:59:59.000';
		$params['DrugRequestType_id'] = $data['DrugRequestType_id'];
		$params['Lpu_id'] = $data['Lpu_id'];

		if ( $data['Lpu_id'] > 0 ) {
			$filter .= " and (DR.Lpu_id = :Lpu_id or NULLIF(:Lpu_id, 0) is null)";
		}

		switch ( $data['DrugRequestType_id'] ) {
			case 1:
				$params['Koef'] = $options['koef_fed_lgot'];
				$params['Normativ'] = $options['normativ_fed_lgot'];
				$person_refuse_filter .= "
					and not exists (
						select 1
						from v_PersonRefuse pr2 
						where pr2.PersonRefuse_Year = RIGHT(:DrugRequestPeriod_id, 4)
							and pr2.personrefuse_isrefuse = 2
							and PersonCard.person_id = pr2.person_id
						limit 1
					)        
				";
				$privilege_filter = "and PersonPrivilege.PrivilegeType_id between 0 and 150";
			break;

			case 2:
				$params['Koef'] = $options['koef_reg_lgot'];
				$params['Normativ'] = $options['normativ_reg_lgot'];
				$privilege_filter = "and PersonPrivilege.PrivilegeType_id between 151 and 500";

				$join_str .= "left join YoungChild  on YoungChild.Lpu_id = RequestPersonInfo.Lpu_id";


				$select_fields .= "COALESCE(YoungChild.YoungChild_Count, 0) as \"YoungChild_Count\", ";


				$young_child_set = "
					YoungChild(
						Lpu_id,
						YoungChild_Count
					) as (
						select
							DR.Lpu_id,
							SUM(DR.DrugRequest_YoungChildCount) as YoungChild_Count
						from v_DrugRequest DR 				
						where " . $filter . "
							and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
						group by DR.Lpu_id
					),
				";
			break;
		}

		$query = "
			with 
" . $young_child_set . "
            RequestPersonInfo(
                Lpu_id,
                DrugRequestRow_PersonCount,
                DrugRequestRow_SummaPerson,
                DrugRequestRow_SummaReserve
            ) as (
                select Lpu_id,
                       COUNT(distinct DRR.Person_id) as DrugRequestRow_PersonCount,
                       SUM(case
                             when DRR.Person_id is not null then DRR.DrugRequestRow_Summa
                             else 0
                           end) as DrugRequestRow_SummaPerson,
                       SUM(case
                             when DRR.Person_id is null then DRR.DrugRequestRow_Summa
                             else 0
                           end) as DrugRequestRow_SummaReserve
                from DrugRequestRow DRR
                     inner join DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
                     LEFT JOIN LATERAL
                     (
                       select DRTS.DrugRequestTotalStatus_closeDT as DrugRequestTotalStatus_closeDT
                       from DrugRequestTotalStatus DRTS
                       where DRTS.Lpu_id = DR.Lpu_id and
                             DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
					   limit 1
                     ) drts ON true
                where 
                " . $filter . " and
                      DRR.DrugRequestType_id = :DrugRequestType_id and
                      (DRR.DrugRequestRow_delDT is null or
                      DRR.DrugRequestRow_delDT > COALESCE(dbo.mindate(COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()), CAST (:DrugRequestRow_actDate as timestamp)), dbo.MinDate(CAST (:DrugRequestRow_actDate as timestamp), dbo.tzGetDate()))) and
                      DRR.DrugRequestRow_updDT <= COALESCE(dbo.mindate(COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()), CAST (:DrugRequestRow_actDate as timestamp)), dbo.MinDate(CAST (:DrugRequestRow_actDate as timestamp), dbo.tzGetDate()))
                group by Lpu_id),

            AttachPersonInfo(
                Lpu_id,
                DrugRequestRow_SummaPerson,
                DrugRequestRow_SummaMinZdrav,
                DrugRequestRow_SummaOnkoDisp,
                DrugRequestRow_SummaOnkoGemat,
                DrugRequestRow_SummaPsycho,
                DrugRequestRow_SummaRevmat,
                DrugRequestRow_SummaOtherLpu
            ) as (
                select Lpu.Lpu_id,
                       sum(case
                             when DR.Lpu_id = PC.Lpu_id and DR.LpuSection_id != 337 and DRR.Person_id = PC.Person_id then DRR.DrugRequestRow_Summa
                           end) as DrugRequestRow_SummaPerson,
                       sum(case
                             when DR.Lpu_id <> PC.Lpu_id and MinZdravList.id is not null and DRR.Person_id = PC.Person_id then DRR.DrugRequestRow_Summa
                           end) as DrugRequestRow_SummaMinZdrav,
                       sum(case
                             when DR.Lpu_id <> PC.Lpu_id and OnkoList.id is not null and DRR.Person_id = PC.Person_id then DRR.DrugRequestRow_Summa
                           end) as DrugRequestRow_SummaOnkoDisp,
                       sum(case                             when 
                       -- DR.Lpu_id <> PC.Lpu_id and
                       DR.LpuSection_id = 337 and DRR.Person_id = PC.Person_id then DRR.DrugRequestRow_Summa
                           end) as DrugRequestRow_SummaOnkoGemat,
                       sum(case
                             when DR.Lpu_id <> PC.Lpu_id and PsychoList.id is not null and DRR.Person_id = PC.Person_id then DRR.DrugRequestRow_Summa
                           end) as DrugRequestRow_SummaPsycho,
                       sum(case
                             when DR.Lpu_id <> PC.Lpu_id and DR.LpuSection_id = 1659 and DRR.Person_id = PC.Person_id then DRR.DrugRequestRow_Summa
                           end) as DrugRequestRow_SummaRevmat,
                       sum(case
                             when DR.Lpu_id <> PC.Lpu_id and MinZdravList.id is null and OnkoList.id is null and PsychoList.id is null and DR.LpuSection_id not in (337, 1659) and DRR.Person_id = PC.Person_id then DRR.DrugRequestRow_Summa
                           end) as DrugRequestRow_SummaOtherLpu
                from DrugRequestRow DRR
                     INNER JOIN LATERAL
                     (
                       select Person_id,
                              Lpu_id
                       from v_PersonCard_all PC
                       where (PC.Lpu_id = :Lpu_id or
                             CASE WHEN :Lpu_id = 0 THEN TRUE ELSE FALSE END) and
                             DRR.Person_id = PC.Person_id and
                             PC.LpuAttachType_id = 1 and
                             PC.PersonCard_begDate <= COALESCE((
                                                                 select case
                                                                                when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                else CAST (:DrugRequestRow_actDate as timestamp)
                                                                              end as actualDate
                                                                 from DrugRequestTotalStatus DRTS
                                                                 where DRTS.Lpu_id = PC.Lpu_id and
                                                                       DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
                                                                 limit 1
                             ), case
                                  when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                                  else dbo.tzGetDate()
                                end) and
                             (PC.PersonCard_endDate is null or
                             PC.PersonCard_endDate > COALESCE((
                                                                select case
                                                                               when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                               else CAST (:DrugRequestRow_actDate as timestamp)
                                                                             end as actualDate
                                                                from DrugRequestTotalStatus DRTS
                                                                where DRTS.Lpu_id = PC.Lpu_id and
                                                                      DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
                                                                limit 1
                             ), case
                                  when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                                  else dbo.tzGetDate()
                                end))
                       order by PersonCard_id desc
                       limit 1
                     ) PC ON true
                     inner join DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id and DRR.DrugRequestType_id = :DrugRequestType_id
                     inner join DrugRequestPeriod DRP on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id and DRP.DrugRequestPeriod_id = :DrugRequestPeriod_id
                     inner join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
                     LEFT JOIN LATERAL
                     (
                       select id
                       from MinZdravList()
                       where id = Dr.Lpu_id
                     ) MinZdravList ON true
                     LEFT JOIN LATERAL
                     (
                       select id
                       from OnkoList()
                       where id = Dr.Lpu_id
                     ) OnkoList ON true
                     LEFT JOIN LATERAL
                     (
                       select id
                       from PsychoList()
                       where id = Dr.Lpu_id
                     ) PsychoList ON true
                where DRR.Person_id is not null and
                      (DRR.DrugRequestRow_delDT is null or
                      DRR.DrugRequestRow_delDT > COALESCE((
                                                            select case
                                                                           when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                           else CAST (:DrugRequestRow_actDate as timestamp)
                                                                         end as actualDate
                                                            from DrugRequestTotalStatus DRTS
                                                            where DRTS.Lpu_id = DR.Lpu_id and
                                                                  DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
                                                            limit 1
                      ), case
                           when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                           else dbo.tzGetDate()
                         end)) and
                      COALESCE(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= COALESCE((
                                                                                                 select case
                                                                                                                when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                                                else CAST (:DrugRequestRow_actDate as timestamp)
                                                                                                              end as actualDate
                                                                                                 from DrugRequestTotalStatus DRTS
                                                                                                 where DRTS.Lpu_id = DR.Lpu_id and
                                                                                                       DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
                                                                                                 limit 1
                      ), case
                           when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                           else dbo.tzGetDate()
                         end)
                group by Lpu.Lpu_id),

            AttachPersonCount(
                Lpu_id,
                AttachPerson_Count
            ) as (
                select PersonCard.Lpu_id,
                       count(distinct PersonCard.Person_id) as AttachPerson_Count
                from v_PersonCard_all PersonCard
                     LEFT JOIN LATERAL
                     (
                       select DrugRequestTotalStatus_CloseDt actualDate
                       from DrugRequestTotalStatus
                       where Lpu_id = PersonCard.Lpu_id and
                             DrugRequestPeriod_id = :DrugRequestPeriod_id
                       limit 1
                     ) DRTS ON true
                     INNER JOIN LATERAL
                     (
                       select min(PrivilegeType_id) PrivilegeType_id
                       from v_PersonPrivilege as PersonPrivilege
                       where PersonPrivilege.Person_id = PersonCard.Person_id and
                             (PersonPrivilege.PersonPrivilege_endDate is null or
                             PersonPrivilege.PersonPrivilege_endDate > COALESCE(dbo.mindate(COALESCE(DRTS.actualdate, dbo.tzGetDate()), CAST (:DrugRequestRow_actDate as timestamp)), dbo.MinDate(CAST (:DrugRequestRow_actDate as timestamp), dbo.tzGetDate()))) and
                             PersonPrivilege.PersonPrivilege_begDate <= COALESCE(dbo.mindate(COALESCE(DRTS.actualdate, dbo.tzGetDate()), CAST (:DrugRequestRow_actDate as timestamp)), dbo.MinDate(CAST (:DrugRequestRow_actDate as timestamp), dbo.tzGetDate()))
                     ) PersonPrivilege ON true
                where (PersonCard.Lpu_id = :Lpu_id or
                      CASE WHEN :Lpu_id = 0 THEN TRUE ELSE FALSE END) 
                      " . $privilege_filter . " " . $person_refuse_filter . " and
                      PersonCard.Lpu_id not in (100, 101) and
                      PersonCard.LpuAttachType_id = 1 and
                      (PersonCard.PersonCard_endDate is null or
                      PersonCard.PersonCard_endDate > COALESCE(dbo.mindate(COALESCE(DRTS.actualdate, dbo.tzGetDate()), CAST (:DrugRequestRow_actDate as timestamp)), dbo.MinDate(CAST (:DrugRequestRow_actDate as timestamp), dbo.tzGetDate()))) and
                      PersonCard.PersonCard_begDate <= COALESCE(dbo.mindate(COALESCE(DRTS.actualdate, dbo.tzGetDate()), CAST (:DrugRequestRow_actDate as timestamp)), dbo.MinDate(CAST (:DrugRequestRow_actDate as timestamp), dbo.tzGetDate()))
                group by PersonCard.Lpu_id)

            select 
            " . $select_fields . " 
            	   COALESCE(RequestPersonInfo.Lpu_id, 0) as \"Lpu_id\",
                   COALESCE(Lpu.Lpu_Name, '-') as \"Lpu_Name\",
                   COALESCE(RequestPersonInfo.DrugRequestRow_PersonCount, 0) as \"Request_PersonCount\",
                   COALESCE(AttachPersonCount.AttachPerson_Count, 0) as \"Attach_PersonCount\",
                   COALESCE(AttachPersonCount.AttachPerson_Count * :Normativ * 3 * :Koef, 0) as \"Attach_SummaLimit\",
                   COALESCE(RequestPersonInfo.DrugRequestRow_SummaPerson, 0) as \"Request_SummaPerson\",
                   COALESCE(RequestPersonInfo.DrugRequestRow_SummaReserve, 0) as \"Request_SummaReserve\",
                   COALESCE(AttachPersonInfo.DrugRequestRow_SummaPerson, 0) as \"Attach_SummaPerson\",
                   COALESCE(AttachPersonInfo.DrugRequestRow_SummaMinZdrav, 0) as \"Attach_SummaMinZdrav\",
                   COALESCE(AttachPersonInfo.DrugRequestRow_SummaOnkoDisp, 0) as \"Attach_SummaOnkoDisp\",
                   COALESCE(AttachPersonInfo.DrugRequestRow_SummaOnkoGemat, 0) as \"Attach_SummaOnkoGemat\",
                   COALESCE(AttachPersonInfo.DrugRequestRow_SummaPsycho, 0) as \"Attach_SummaPsycho\",
                   COALESCE(AttachPersonInfo.DrugRequestRow_SummaRevmat, 0) as \"Attach_SummaRevmat\",
                   COALESCE(AttachPersonInfo.DrugRequestRow_SummaOtherLpu, 0) as \"Attach_SummaOtherLpu\"
            from RequestPersonInfo
                 left join v_Lpu Lpu on RequestPersonInfo.Lpu_id = Lpu.Lpu_id
                 left join AttachPersonInfo on AttachPersonInfo.Lpu_id = RequestPersonInfo.Lpu_id
                 left join AttachPersonCount on AttachPersonCount.Lpu_id = RequestPersonInfo.Lpu_id 
                 " . $join_str . "
            order by \"Lpu_Name\"
		";
		//echo getDebugSQL($query, $params); exit();
		$res = $this->db->query($query, $params);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	* Получение данных для печати заявки "Полная заявка на пациентов"
	*/
	function getPrintDrugRequestData5($data) {
		$filter = "(1 = 1)";
		$params = array();

		$params['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];
		$params['DrugRequestRow_actDate'] = $data['DrugRequestRow_actDate'] . ' 23:59:59.000';
		$params['DrugRequestType_id'] = $data['DrugRequestType_id'];
		$params['Lpu_id'] = $data['Lpu_id'];
		$params['MedPersonal_id'] = $data['MedPersonal_id'];

		$query = "
			with PersonList(
                Person_id
            ) as (
                select distinct Person_id
                from (
                       select DRP.Person_id
                       from DrugRequestPerson DRP
                       where (1 = 1) and
                             Lpu_id = :Lpu_id and
                             MedPersonal_id = :MedPersonal_id and
                             DrugRequestPeriod_id = :DrugRequestPeriod_id
                       union all
                       select PC.Person_id
                       from v_PersonCard_all PC
                            inner join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
                            inner join v_MedStaffRegion MSR on MSR.LpuRegion_id = LR.LpuRegion_id and MSR.MedPersonal_id = :MedPersonal_id
                       where (1 = 1) and
                             PC.LpuAttachType_id = 1 and
                             PC.PersonCard_begDate <= COALESCE((
                                                                 select case
                                                                                when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                else CAST (:DrugRequestRow_actDate as timestamp)
                                                                              end as actualDate
                                                                 from DrugRequestTotalStatus DRTS
                                                                 where DRTS.Lpu_id = PC.Lpu_id and
                                                                       DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
																limit 1
                             ), case
                                  when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                                  else dbo.tzGetDate()
                                end) and
                             (PC.PersonCard_endDate is null or
                             PC.PersonCard_endDate > COALESCE((
                                                                select case
                                                                               when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                               else CAST (:DrugRequestRow_actDate as timestamp)
                                                                             end as actualDate
                                                                from DrugRequestTotalStatus DRTS
                                                                where DRTS.Lpu_id = PC.Lpu_id and
                                                                      DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
                                                                limit 1
                             ), case
                                  when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                                  else dbo.tzGetDate()
                                end))
                     ) Person)

            select COALESCE(PS.Person_id, 0) as \"Person_id\",
                   RTRIM(COALESCE(PS.Person_Surname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Firname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Secname, '')) as \"Person_Fio\",
                   to_char(PS.Person_BirthDay, 'DD.MM.YYYY') as \"Person_Birthday\",
                   COALESCE(DPM.DrugProtoMnn_Code, Drug.Drug_Code) as \"DrugRequestRow_Code\",
                   COALESCE(DPM.DrugProtoMnn_Name, Drug.Drug_Name) as \"DrugRequestRow_Name\",
                   RTRIM(COALESCE(Lpu.Lpu_Nick, '')) as \"Lpu_Name\",
                   RTRIM(COALESCE(MP.Person_Fio, '')) as \"MedPersonal_Fio\",
                   SUM(COALESCE(DRR.DrugRequestRow_Kolvo, 0)) as \"DrugRequestRow_Kolvo\",
                   ROUND(SUM(CAST(DRR.DrugRequestRow_Summa as numeric)) / SUM(COALESCE(CAST(DRR.DrugRequestRow_Kolvo as numeric), 0)), 2) as \"DrugRequestRow_Price\",
                   SUM(COALESCE(DRR.DrugRequestRow_Summa, 0)) as \"DrugRequestRow_Summa\"
            from PersonList PL
                 inner join v_PersonState PS on PS.Person_id = PL.Person_id
                 left join DrugRequestRow DRR on DRR.Person_id = PL.Person_id and DRR.DrugRequestType_id = :DrugRequestType_id
                 left join DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id
                 left join v_MedPersonal MP on MP.MedPersonal_id = DR.MedPersonal_id and MP.Lpu_id = DR.Lpu_id
                 left join v_Lpu Lpu on Lpu.Lpu_id = DR.Lpu_id
                 left join DrugProtoMnn DPM on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
                 left join Drug on Drug.Drug_id = DRR.Drug_id
                 left join DrugRequestPeriod DRP2 on DRP2.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
            where 
            	" . $filter . " and
                  (DRR.DrugRequestRow_id is null or
                  (DR.DrugRequestPeriod_id = :DrugRequestPeriod_id and
                  (DRR.DrugRequestRow_delDT is null or
                  DRR.DrugRequestRow_delDT > COALESCE((
                                                        select case
                                                                       when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                       else CAST (:DrugRequestRow_actDate as timestamp)
                                                                     end as actualDate
                                                        from DrugRequestTotalStatus DRTS
                                                        where DRTS.Lpu_id = DR.Lpu_id and
                                                              DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
                                                        limit 1
                  ), case
                       when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                       else dbo.tzGetDate()
                     end)) and
                  COALESCE(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= COALESCE((
                                                                                             select case
                                                                                                            when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                                            else CAST (:DrugRequestRow_actDate as timestamp)
                                                                                                          end as actualDate
                                                                                             from DrugRequestTotalStatus DRTS
                                                                                             where DRTS.Lpu_id = DR.Lpu_id and
                                                                                                   DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
                                                                                             limit 1
                  ), case
                       when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                       else dbo.tzGetDate()
                     end)))
            group by PS.Person_id,
                     PS.Person_Surname,
                     PS.Person_Firname,
                     PS.Person_Secname,
                     PS.Person_BirthDay,
                     DPM.DrugProtoMnn_Code,
                     DPM.DrugProtoMnn_Name,
                     Drug.Drug_Code,
                     Drug.Drug_Name,
                     Lpu.Lpu_Nick,
                     MP.Person_Fio
            order by \"Person_Fio\",
                     PS.Person_id
		";
		// echo getDebugSQL($query, $params); exit();
		$res = $this->db->query($query, $params);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	* Получение данных для печати заявки "Заявки врачей"
	*/
	function getPrintDrugRequestData6($data) {
		$filter = "(1 = 1)";
		$params = array();

		$params['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];
		$params['DrugRequestRow_actDate'] = $data['DrugRequestRow_actDate'] . ' 23:59:59.000';
		$params['DrugRequestType_id'] = $data['DrugRequestType_id'];

		if ( $data['Lpu_id'] > 0 ) {
			$filter .= " and DR.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		if ( $data['LpuSection_id'] > 0 ) {
			$filter .= " and LS.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ( $data['LpuUnit_id'] > 0 ) {
			$filter .= " and LS.LpuUnit_id = :LpuUnit_id";
			$params['LpuUnit_id'] = $data['LpuUnit_id'];
		}

		if ( $data['MedPersonal_id'] > 0 ) {
			$filter .= " and DR.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		$query = "
			select COALESCE(MP.MedPersonal_id, 0) as \"MedPersonal_id\",
             RTRIM(COALESCE(MP.Person_Fio, '-')) as \"MedPersonal_Fio\",
             COALESCE(PS.Person_id, 0) as \"Person_id\",
             RTRIM(COALESCE(PS.Person_Surname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Firname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Secname, '')) as \"Person_Fio\",
             RTRIM(COALESCE(CAST(DPM.DrugProtoMnn_Code as varchar), CAST(Drug.Drug_Code as varchar), '')) as \"DrugRequestRow_Code\",
             RTRIM(COALESCE(DPM.DrugProtoMnn_Name, Drug.Drug_Name, '')) as \"DrugRequestRow_Name\",
             SUM(COALESCE(DRR.DrugRequestRow_Kolvo, 0)) as \"DrugRequestRow_Kolvo\",
             ROUND(SUM(CAST(DRR.DrugRequestRow_Summa as numeric)) / SUM(COALESCE(CAST(DRR.DrugRequestRow_Kolvo as numeric), 0)), 2) as \"DrugRequestRow_Price\",
             SUM(DRR.DrugRequestRow_Summa) as \"DrugRequestRow_Summa\"
      from DrugRequestRow DRR
           inner join DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
           left join DrugProtoMnn DPM on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
           left join Drug on Drug.Drug_id = DRR.Drug_id
           left join v_MedPersonal MP on MP.MedPersonal_id = DR.MedPersonal_id and MP.Lpu_id = DR.Lpu_id
           left join LpuSection LS on LS.LpuSection_id = DR.LpuSection_id
           left join v_PersonState PS on PS.Person_id = DRR.Person_id
      where 
      " . $filter . " and
            DRR.DrugRequestType_id =:DrugRequestType_id and
            (DRR.DrugRequestRow_delDT is null or
            DRR.DrugRequestRow_delDT > COALESCE((
                                                  select case
                                                                 when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                 else CAST (:DrugRequestRow_actDate as timestamp)
                                                               end as actualDate
                                                  from DrugRequestTotalStatus DRTS
                                                  where DRTS.Lpu_id = DR.Lpu_id and
                                                        DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
                                                  limit 1
            ), case
                 when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                 else dbo.tzGetDate()
               end)) and
            COALESCE(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= COALESCE((
                                                                                       select case
                                                                                                      when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                                      else CAST (:DrugRequestRow_actDate as timestamp)
                                                                                                    end as actualDate
                                                                                       from DrugRequestTotalStatus DRTS
                                                                                       where DRTS.Lpu_id = DR.Lpu_id and
                                                                                             DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
                                                                                       limit 1
            ), case
                 when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                 else dbo.tzGetDate()
               end)
      group by MP.MedPersonal_id,
               MP.Person_Fio,
               PS.Person_id,
               PS.Person_Surname,
               PS.Person_Firname,
               PS.Person_Secname,
               DPM.DrugProtoMnn_Code,
               DPM.DrugProtoMnn_Name,
               Drug.Drug_Code,
               Drug.Drug_Name
      order by \"MedPersonal_Fio\",
               \"Person_Fio\"
		";
		$res = $this->db->query($query, $params);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	* Получение данных для печати заявки "Сводная заявка на прикрепленных к ЛПУ пациентов и отчет для Минздрава"
	*/
	function getPrintDrugRequestData7($data) {
		$filter = "(1 = 1)";
		$params = array();

		$params['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];
		$params['DrugRequestRow_actDate'] = $data['DrugRequestRow_actDate'] . ' 23:59:59.000';
		$params['DrugRequestType_id'] = $data['DrugRequestType_id'];
		$params['Lpu_id'] = $data['Lpu_id'];

		$query = "
			select COALESCE(DPM.DrugProtoMnn_Code, Drug.Drug_Code) as \"DrugRequestRow_Code\",
             COALESCE(DPM.DrugProtoMnn_Name, Drug.Drug_Name) as \"DrugRequestRow_Name\",
             SUM(COALESCE(DRR.DrugRequestRow_Kolvo, 0)) as \"DrugRequestRow_Kolvo\",
             ROUND(SUM(CAST(DRR.DrugRequestRow_Summa as numeric)) / SUM(COALESCE(CAST(DRR.DrugRequestRow_Kolvo as numeric), 0)), 2) as \"DrugRequestRow_Price\",
             SUM(DRR.DrugRequestRow_Summa) as \"DrugRequestRow_Summa\"
      from DrugRequestRow DRR
           inner join DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id
           inner join DrugRequestPeriod DRP on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id and DRP.DrugRequestPeriod_id =:DrugRequestPeriod_id
           left join DrugProtoMnn DPM on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
           left join Drug on Drug.Drug_id = DRR.Drug_id
           -- and Drug.DrugClass_id = 1
           LEFT JOIN LATERAL
           (
             select PC.Lpu_id
             from v_PersonCard_all PC
             where PC.Person_id = DRR.Person_id and
                   (PC.Lpu_id = :Lpu_id or :Lpu_id = 0) and
                   PC.LpuAttachType_id = 1 and
                   PC.PersonCard_begDate <= COALESCE((
                                                       select case
                                                                      when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                      else CAST (:DrugRequestRow_actDate as timestamp)
                                                                    end as actualDate
                                                       from DrugRequestTotalStatus DRTS
                                                       where DRTS.Lpu_id = PC.Lpu_id and
                                                             DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
                                                       limit 1
                   ), case
                        when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                        else dbo.tzGetDate()
                      end) and
                   (PC.PersonCard_endDate is null or
                   PC.PersonCard_endDate > COALESCE((
                                                      select case
                                                                     when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                     else CAST (:DrugRequestRow_actDate as timestamp)
                                                                   end as actualDate
                                                      from DrugRequestTotalStatus DRTS
                                                      where DRTS.Lpu_id = PC.Lpu_id and
                                                            DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
                                                      limit 1
                   ), case
                        when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                        else dbo.tzGetDate()
                      end))
             order by PC.PersonCard_begDate desc
             limit 1
           ) AttLpu ON true
           LEFT JOIN LATERAL
           (
             select DrugRequest.DrugRequest_id
             from v_DrugRequest DrugRequest
             where (DrugRequest.Lpu_id =:Lpu_id or Lpu_id = 0 is null) and
                   DrugRequest.DrugRequest_id = DR.DrugRequest_id
             limit 1
           ) OurDrugRequest ON true
      where (1 = 1)
            -- Медикамент указан
            and
            (DPM.DrugProtoMnn_id is not null or
            Drug.Drug_id is not null)
            -- Тип заявки
            and
            DRR.DrugRequestType_id =:DrugRequestType_id
            -- Заявка не удалена либо удалена позже даты актуальности
            and
            (DRR.DrugRequestRow_delDT is null or
            DRR.DrugRequestRow_delDT > COALESCE((
                                                  select case
                                                                 when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                 else CAST (:DrugRequestRow_actDate as timestamp)
                                                               end as actualDate
                                                  from DrugRequestTotalStatus DRTS
                                                  where DRTS.Lpu_id = DR.Lpu_id and
                                                        DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
                                                  limit 1
            ), case
                 when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                 else dbo.tzGetDate()
               end))
            -- Заявка актуальна на выбранную дату
            and
            COALESCE(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= COALESCE((
                                                                                       select case
                                                                                                      when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                                      else CAST (:DrugRequestRow_actDate as timestamp)
                                                                                                    end as actualDate
                                                                                       from DrugRequestTotalStatus DRTS
                                                                                       where DRTS.Lpu_id = DR.Lpu_id and
                                                                                             DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
                                                                                       limit 1
            ), case
                 when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                 else dbo.tzGetDate()
               end)
            -- Пациент имеет прикрепление к ЛПУ и является льготником или резерв из 'своей' заявки
            and
            (AttLpu.Lpu_id is not null or
            (DRR.Person_id is null and
            OurDrugRequest.DrugRequest_id is not null))
      group by DPM.DrugProtoMnn_id,
               DPM.DrugProtoMnn_Code,
               DPM.DrugProtoMnn_Name,
               Drug.Drug_id,
               Drug.Drug_Code,
               Drug.Drug_Name
      order by \"DrugRequestRow_Name\"
		";
		//echo getDebugSQL($query, $params); exit();
		$res = $this->db->query($query, $params);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	* Получение данных для печати заявки "Печать заявки с данными пациента"
	*/
	function getPrintDrugRequestData8($data) {
		$filter = "(1 = 1)";
		$params = array();

		$params['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];
		$params['DrugRequestRow_actDate'] = $data['DrugRequestRow_actDate'] . ' 23:59:59.000';
		$params['DrugRequestType_id'] = $data['DrugRequestType_id'];

		if ( $data['Lpu_id'] > 0 ) {
			$filter .= " and DR.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		if ( $data['LpuSection_id'] > 0 ) {
			$filter .= " and LS.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ( $data['LpuUnit_id'] > 0 ) {
			$filter .= " and LS.LpuUnit_id = :LpuUnit_id";
			$params['LpuUnit_id'] = $data['LpuUnit_id'];
		}

		if ( $data['MedPersonal_id'] > 0 ) {
			$filter .= " and DR.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		$query = "
			select COALESCE(PS.Person_id, 0) as \"Person_id\",
             RTRIM(COALESCE(PS.Person_Surname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Firname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Secname, '')) as \"Person_Fio\",
             to_char(PS.Person_Birthday, 'DD.MM.YYYY') as \"Person_Birthday\",
             RTRIM(COALESCE(CAST(DPM.DrugProtoMnn_Code as varchar), CAST(Drug.Drug_Code as varchar), '')) as \"DrugRequestRow_Code\",
             RTRIM(COALESCE(DPM.DrugProtoMnn_Name, Drug.Drug_Name, '')) as \"DrugRequestRow_Name\",
             SUM(COALESCE(DRR.DrugRequestRow_Kolvo, 0)) as \"DrugRequestRow_Kolvo\",
             ROUND(CAST(SUM(DRR.DrugRequestRow_Summa) / SUM(COALESCE(DRR.DrugRequestRow_Kolvo, 0)) as numeric), 2) as \"DrugRequestRow_Price\",
             SUM(DRR.DrugRequestRow_Summa) as \"DrugRequestRow_Summa\",
             AttLpu.AttachLpu_Name as \"AttachLpu_Name\",
             RTRIM(UAddr.Address_Address) as \"UAddress_Name\"
      from DrugRequestRow DRR
           inner join DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id and DR.DrugRequestPeriod_id =:DrugRequestPeriod_id
           left join DrugProtoMnn DPM on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
           left join Drug on Drug.Drug_id = DRR.Drug_id
           left join LpuSection LS on LS.LpuSection_id = DR.LpuSection_id
           left join v_PersonState PS on PS.Person_id = DRR.Person_id
           left join Address UAddr on UAddr.Address_id = PS.UAddress_id
           LEFT JOIN LATERAL
           (
             select RTRIM(Lpu.Lpu_Nick) as AttachLpu_Name
             from v_PersonCard_all PC
                  inner join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
             where PC.LpuAttachType_id = 1 and
                   PC.Person_id = DRR.Person_id and
                   PC.PersonCard_begDate <= COALESCE((
                                                       select case
                                                                      when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                      else CAST (:DrugRequestRow_actDate as timestamp)
                                                                    end as actualDate
                                                       from DrugRequestTotalStatus DRTS
                                                       where DRTS.Lpu_id = PC.Lpu_id and
                                                             DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
                                                       limit 1
                   ), case
                        when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                        else dbo.tzGetDate()
                      end) and
                   (PC.PersonCard_endDate is null or
                   PC.PersonCard_endDate > COALESCE((
                                                      select case
                                                                     when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                     else CAST (:DrugRequestRow_actDate as timestamp)
                                                                   end as actualDate
                                                      from DrugRequestTotalStatus DRTS
                                                      where DRTS.Lpu_id = PC.Lpu_id and
                                                            DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
													  limit 1
                   ), case
                        when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                        else dbo.tzGetDate()
                      end))
             order by PC.PersonCard_begDate desc
             limit 1
           ) AttLpu ON true
      where 
      " . $filter . " and
            DRR.DrugRequestType_id = :DrugRequestType_id and
            (DRR.DrugRequestRow_delDT is null or
            DRR.DrugRequestRow_delDT > COALESCE((
                                                  select case
                                                                 when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                 else CAST (:DrugRequestRow_actDate as timestamp)
                                                               end as actualDate
                                                  from DrugRequestTotalStatus DRTS
                                                  where DRTS.Lpu_id = DR.Lpu_id and
                                                        DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
                                                  limit 1
            ), case
                 when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                 else dbo.tzGetDate()
               end)) and
            COALESCE(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= COALESCE((
                                                                                       select case
                                                                                                      when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                                      else CAST (:DrugRequestRow_actDate as timestamp)
                                                                                                    end as actualDate
                                                                                       from DrugRequestTotalStatus DRTS
                                                                                       where DRTS.Lpu_id = DR.Lpu_id and
                                                                                             DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
                                                                                       limit 1
            ), case
                 when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                 else dbo.tzGetDate()
               end)
      group by PS.Person_id,
               PS.Person_Surname,
               PS.Person_Firname,
               PS.Person_Secname,
               PS.Person_Birthday,
               UAddr.Address_Address,
               DPM.DrugProtoMnn_Code,
               DPM.DrugProtoMnn_Name,
               AttLpu.AttachLpu_Name,
               Drug.Drug_Code,
               Drug.Drug_Name
      order by PS.Person_Surname,
               PS.Person_Firname,
               PS.Person_Secname
		";
		//echo getDebugSQL($query, $params); exit();
		$res = $this->db->query($query, $params);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	* Получение данных для печати заявки "Превышение лимита"
	*/
	function getPrintDrugRequestData9($data, $options) {
		$fed_lgot_check_filter = "";
		$fed_lgot_check_join = "";
		$join_str = "";
		$params = array();
		$person_refuse_join = "";
		$person_refuse_filter = "";
		$privilege_code_filter = "";
		$select_fields = "";
		$young_child_set = "";

		$params['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];
		$params['DrugRequestRow_actDate'] = $data['DrugRequestRow_actDate'] . ' 23:59:59.000';
		$params['DrugRequestType_id'] = $data['DrugRequestType_id'];
		$params['Lpu_id'] = $data['Lpu_id'];

		switch ( $data['DrugRequestType_id'] ) {
			case 1:
				$params['Koef'] = $options['koef_fed_lgot'];
				$params['Normativ'] = $options['normativ_fed_lgot'];
				$person_refuse_filter .= " and COALESCE(PR.PersonRefuse_IsRefuse, 1) = 1";

				$person_refuse_join = "
					left join v_PersonRefuse PR  on PR.Person_id = PP.Person_id
						and PR.PersonRefuse_Year = date_part('YEAR',DRP.DrugRequestPeriod_begDate)
						and PR.PersonRefuse_IsRefuse = 2 
				";
				$privilege_code_filter = " and PT.ReceptFinance_id = 1 and isnumeric(PT.PrivilegeType_Code) = 1";
			break;

			case 2:
				$params['Koef'] = $options['koef_reg_lgot'];
				$params['Normativ'] = $options['normativ_reg_lgot'];
				$privilege_code_filter = " and PT.ReceptFinance_id = 2 and isnumeric(PT.PrivilegeType_Code) = 1";

				$join_str .= "left join YoungChild  on YoungChild.Lpu_id = RequestPersonInfo.Lpu_id";


				$select_fields .= "COALESCE(YoungChild.YoungChild_Count, 0) as \"YoungChild_Count\", ";

				$select_fields .= "COALESCE(YoungChild.YoungChild_SummaLimit, 0) as \"YoungChild_SummaLimit\", ";


				$fed_lgot_check_join = "
					LEFT JOIN LATERAL (
                        select 1 as IsFedLgot
                        from v_PersonPrivilege PP2
                             inner join PrivilegeType PT2 on PT2.PrivilegeType_id = PP2.PrivilegeType_id and PT2.ReceptFinance_id = 1 and isnumeric(PT2.PrivilegeType_Code) = 1
                        where PP2.Person_id = PC.Person_id and
                              PP2.PersonPrivilege_begDate <= COALESCE((
                                                                        select case
                                                                                       when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as datetime) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                       else CAST (:DrugRequestRow_actDate as datetime)
                                                                                     end as actualDate
                                                                        from DrugRequestTotalStatus DRTS
                                                                        where DRTS.Lpu_id = PC.Lpu_id and
                                                                              DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
                                                                        limit 1
                              ), case
                                   when CAST (:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as datetime)
                                   else dbo.tzGetDate()
                                 end) and
                              (PP2.PersonPrivilege_endDate is null or
                              PP2.PersonPrivilege_endDate >= COALESCE((
                                                                        select case
                                                                                       when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as datetime) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                       else CAST (:DrugRequestRow_actDate as datetime)
                                                                                     end as actualDate
                                                                        from DrugRequestTotalStatus DRTS
                                                                        where DRTS.Lpu_id = PC.Lpu_id and
                                                                              DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
                                                                        limit 1
                              ), case
                                   when CAST (:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as datetime)
                                   else dbo.tzGetDate()
                                 end))
                        limit 1) FedLgot ON true
				";
				$fed_lgot_check_filter = " and FedLgot.IsFedLgot is null";

				$young_child_set = "
					YoungChild(
                        Lpu_id,
                        YoungChild_Count,
                        YoungChild_SummaLimit
                    ) as (
                        select DR.Lpu_id,
                               SUM(DR.DrugRequest_YoungChildCount) as YoungChild_Count,
                               SUM(DR.DrugRequest_YoungChildCount) * :Normativ  * 3 * :Koef as YoungChild_SummaLimit
                        from v_DrugRequest DR
                             inner join DrugRequestPeriod DRP on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
                        where (DR.Lpu_id = :Lpu_id or :Lpu_id = 0)
                        group by DR.Lpu_id),
				";
			break;
		}

		$query = "
			with 
      " . $young_child_set . "
            RequestPersonInfo(
                Lpu_id,
                Lpu_Name,
                DrugRequestRow_PersonCount,
                DrugRequestRow_SummaLimit
            ) as (
                select Lpu.Lpu_id,
                       RTRIM(LTRIM(Lpu.Lpu_Nick)) as Lpu_Name,
                       count(distinct DRR.Person_id) as DrugRequestRow_PersonCount,
                       count(distinct DRR.Person_id) * :Normativ * 3 * :Koef as DrugRequestRow_SummaLimit
                from DrugRequestRow DRR
                     inner join DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
                     inner join v_Lpu Lpu on Lpu.Lpu_id = DR.Lpu_id
                where (DR.Lpu_id = :Lpu_id or :Lpu_id = 0) and
                      DRR.Person_id is not null and
                      DRR.DrugRequestType_id = :DrugRequestType_id 
                      and
                      (DRR.DrugRequestRow_delDT is null or
                      DRR.DrugRequestRow_delDT > COALESCE((
                                                            select case
                                                                           when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                           else CAST (:DrugRequestRow_actDate as timestamp)
                                                                         end as actualDate
                                                            from DrugRequestTotalStatus DRTS
                                                            where DRTS.Lpu_id = DR.Lpu_id and
                                                                  DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
                                                            limit 1
                      ), case
                           when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                           else dbo.tzGetDate()
                         end)) and
                      COALESCE(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= COALESCE((
                                                                                                 select case
                                                                                                                when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                                                else CAST (:DrugRequestRow_actDate as timestamp)
                                                                                                              end as actualDate
                                                                                                 from DrugRequestTotalStatus DRTS
                                                                                                 where DRTS.Lpu_id = DR.Lpu_id and
                                                                                                       DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
                                                                                                 limit 1
                      ), case
                           when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                           else dbo.tzGetDate()
                         end)
                group by Lpu.Lpu_id,
                         Lpu.Lpu_Nick),
            AttachPersonInfo(
                Lpu_id,
                Lpu_Name,
                DrugRequestRow_SummaPerson,
                DrugRequestRow_SummaReserve,
                DrugRequestRow_SummaOtherLpu,
                DrugRequestRow_SummaMinZdrav,
                DrugRequestRow_SummaOnkoDisp,
                DrugRequestRow_SummaOnkoGemat
            ) as (
                select Lpu_id,
                       Lpu_Name,
                       SUM(LpuRequest_Summa) as DrugRequestRow_SummaPerson,
                       LpuReserve_Summa,
                       SUM(OtherLpuRequest_Summa) as DrugRequestRow_SummaOtherLpu,
                       SUM(MinZdravRequest_Summa) as DrugRequestRow_SummaMinZdrav,
                       SUM(OnkoDispRequest_Summa) as DrugRequestRow_SummaOnkoDisp,
                       SUM(OnkoGematRequest_Summa) as DrugRequestRow_SummaOnkoGemat
                from (
                       select PC.Person_id,
                              Lpu.Lpu_id,
                              RTRIM(LTRIM(Lpu.Lpu_Nick)) as Lpu_Name,
                              COALESCE(LpuRequest.DrugRequestRow_Summa, 0) as LpuRequest_Summa,
                              COALESCE(LpuReserve.DrugRequestRow_Summa, 0) as LpuReserve_Summa,
                              COALESCE(OtherLpuRequest.DrugRequestRow_Summa, 0) as OtherLpuRequest_Summa,
                              COALESCE(MinZdravRequest.DrugRequestRow_Summa, 0) as MinZdravRequest_Summa,
                              COALESCE(OnkoDispRequest.DrugRequestRow_Summa, 0) as OnkoDispRequest_Summa,
                              COALESCE(OnkoGematRequest.DrugRequestRow_Summa, 0) as OnkoGematRequest_Summa
                       from v_PersonCard_all PC
                            inner join DrugRequestRow DRR on DRR.Person_id = PC.Person_id and DRR.DrugRequestType_id = :DrugRequestType_id
                            inner join DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id
                            inner join DrugRequestPeriod DRP on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id and DRP.DrugRequestPeriod_id = :DrugRequestPeriod_id
                            inner join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
                            LEFT JOIN LATERAL
                            (
                              select SUM(DRR2.DrugRequestRow_Summa) as DrugRequestRow_Summa
                              from DrugRequestRow DRR2
                                   inner join DrugRequest DR2 on DR2.DrugRequest_id = DRR2.DrugRequest_id and DR2.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
                                   -- inner join DrugProtoMnn DPM  on DPM.DrugProtoMnn_id = DRR2.DrugProtoMnn_id
                              where (1 = 1) and
                                    DR2.Lpu_id = PC.Lpu_id and
                                    DRR2.Person_id = PC.Person_id and
                                    DRR2.DrugRequestType_id = DRR.DrugRequestType_id and
                                    (DRR2.DrugRequestRow_delDT is null or
                                    DRR2.DrugRequestRow_delDT > COALESCE((
                                                                           select case
                                                                                          when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                          else CAST (:DrugRequestRow_actDate as timestamp)
                                                                                        end as actualDate
                                                                           from DrugRequestTotalStatus DRTS
                                                                           where DRTS.Lpu_id = DR2.Lpu_id and
                                                                                 DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
                                                                           limit 1
                                    ), case
                                         when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                                         else dbo.tzGetDate()
                                       end)) and
                                    COALESCE(DRR2.DrugRequestRow_updDT, DRR2.DrugRequestRow_insDT) <= COALESCE((
                                                                                                                 select case
                                                                                                                                when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                                                                else CAST (:DrugRequestRow_actDate as timestamp)
                                                                                                                              end as actualDate
                                                                                                                 from DrugRequestTotalStatus DRTS
                                                                                                                 where DRTS.Lpu_id = DR2.Lpu_id and
                                                                                                                       DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
                                                                                                                 limit 1
                                    ), case
                                         when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                                         else dbo.tzGetDate()
                                       end)
                            ) LpuRequest ON true
                            LEFT JOIN LATERAL
                            (
                              select SUM(DRR2.DrugRequestRow_Summa) as DrugRequestRow_Summa
                              from DrugRequestRow DRR2
                                   inner join DrugRequest DR2 on DR2.DrugRequest_id = DRR2.DrugRequest_id and DR2.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
                              where (1 = 1) and
                                    DR2.Lpu_id = PC.Lpu_id and
                                    DRR2.Person_id is null and
                                    DRR2.DrugRequestType_id = DRR.DrugRequestType_id and
                                    (DRR2.DrugRequestRow_delDT is null or
                                    DRR2.DrugRequestRow_delDT > COALESCE((
                                                                           select case
                                                                                          when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                          else CAST (:DrugRequestRow_actDate as timestamp)
                                                                                        end as actualDate
                                                                           from DrugRequestTotalStatus DRTS
                                                                           where DRTS.Lpu_id = DR2.Lpu_id and
                                                                                 DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
                                                                           limit 1
                                    ), case
                                         when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                                         else dbo.tzGetDate()
                                       end)) and
                                    COALESCE(DRR2.DrugRequestRow_updDT, DRR2.DrugRequestRow_insDT) <= COALESCE((
                                                                                                                 select case
                                                                                                                                when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                                                                else CAST (:DrugRequestRow_actDate as timestamp)
                                                                                                                              end as actualDate
                                                                                                                 from DrugRequestTotalStatus DRTS
                                                                                                                 where DRTS.Lpu_id = DR2.Lpu_id and
                                                                                                                       DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
                                                                                                                 limit 1
                                    ), case
                                         when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                                         else dbo.tzGetDate()
                                       end)
                            ) LpuReserve ON true
                            LEFT JOIN LATERAL
                            (
                              select SUM(DRR2.DrugRequestRow_Summa) as DrugRequestRow_Summa
                              from DrugRequestRow DRR2
                                   inner join DrugRequest DR2 on DR2.DrugRequest_id = DRR2.DrugRequest_id and DR2.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
                              where (1 = 1) and
                                    DR2.Lpu_id <> PC.Lpu_id and
                                    DR2.Lpu_id not in (
                                                        select id
                                                        from MinZdravList()
                                    ) and
                                    DR2.Lpu_id not in (
                                                        select id
                                                        from OnkoList()
                                    ) and
                                    DR2.LpuSection_id <> 337 and
                                    DRR2.Person_id = PC.Person_id and
                                    DRR2.DrugRequestType_id = DRR.DrugRequestType_id and
                                    (DRR2.DrugRequestRow_delDT is null or
                                    DRR2.DrugRequestRow_delDT > COALESCE((
                                                                           select case
                                                                                          when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                          else CAST (:DrugRequestRow_actDate as timestamp)
                                                                                        end as actualDate
                                                                           from DrugRequestTotalStatus DRTS
                                                                           where DRTS.Lpu_id = DR2.Lpu_id and
                                                                                 DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
                                                                           limit 1
                                    ), case
                                         when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                                         else dbo.tzGetDate()
                                       end)) and
                                    COALESCE(DRR2.DrugRequestRow_updDT, DRR2.DrugRequestRow_insDT) <= COALESCE((
                                                                                                                 select case
                                                                                                                                when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                                                                else CAST (:DrugRequestRow_actDate as timestamp)
                                                                                                                              end as actualDate
                                                                                                                 from DrugRequestTotalStatus DRTS
                                                                                                                 where DRTS.Lpu_id = DR2.Lpu_id and
                                                                                                                       DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
                                    ), case
                                         when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                                         else dbo.tzGetDate()
                                       end)
                            ) OtherLpuRequest ON true
                            LEFT JOIN LATERAL
                            (
                              select SUM(DRR2.DrugRequestRow_Summa) as DrugRequestRow_Summa
                              from DrugRequestRow DRR2
                                   inner join DrugRequest DR2 on DR2.DrugRequest_id = DRR2.DrugRequest_id and DR2.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
                              where (1 = 1) and
                                    DR2.Lpu_id in (
                                                    select id
                                                    from MinZdravList()
                                    ) and
                                    DRR2.Person_id = PC.Person_id and
                                    DRR2.DrugRequestType_id = DRR.DrugRequestType_id and
                                    (DRR2.DrugRequestRow_delDT is null or
                                    DRR2.DrugRequestRow_delDT > COALESCE(CAST (:DrugRequestRow_actDate as timestamp), dbo.tzGetDate())) and
                                    COALESCE(DRR2.DrugRequestRow_updDT, DRR2.DrugRequestRow_insDT) <= COALESCE(CAST (:DrugRequestRow_actDate as timestamp), dbo.tzGetDate())
                            ) MinZdravRequest ON true
                            LEFT JOIN LATERAL
                            (
                              select SUM(DRR2.DrugRequestRow_Summa) as DrugRequestRow_Summa
                              from DrugRequestRow DRR2
                                   inner join DrugRequest DR2 on DR2.DrugRequest_id = DRR2.DrugRequest_id and DR2.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
                              where (1 = 1) and
                                    DR2.Lpu_id in (
                                                    select id
                                                    from OnkoList()
                                    ) and
                                    DRR2.Person_id = PC.Person_id and
                                    DRR2.DrugRequestType_id = DRR.DrugRequestType_id and
                                    (DRR2.DrugRequestRow_delDT is null or
                                    DRR2.DrugRequestRow_delDT > COALESCE(CAST (:DrugRequestRow_actDate as timestamp), dbo.tzGetDate())) and
                                    COALESCE(DRR2.DrugRequestRow_updDT, DRR2.DrugRequestRow_insDT) <= COALESCE(CAST (:DrugRequestRow_actDate as timestamp), dbo.tzGetDate())
                            ) OnkoDispRequest ON true
                            LEFT JOIN LATERAL
                            (
                              select SUM(DRR2.DrugRequestRow_Summa) as DrugRequestRow_Summa
                              from DrugRequestRow DRR2
                                   inner join DrugRequest DR2 on DR2.DrugRequest_id = DRR2.DrugRequest_id and DR2.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
                              where (1 = 1) and
                                    DR2.LpuSection_id = 337 and
                                    DRR2.Person_id = PC.Person_id and
                                    DRR2.DrugRequestType_id = DRR.DrugRequestType_id and
                                    (DRR2.DrugRequestRow_delDT is null or
                                    DRR2.DrugRequestRow_delDT > COALESCE((
                                                                           select case
                                                                                          when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                          else CAST (:DrugRequestRow_actDate as timestamp)
                                                                                        end as actualDate
                                                                           from DrugRequestTotalStatus DRTS
                                                                           where DRTS.Lpu_id = DR2.Lpu_id and
                                                                                 DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
                                                                           limit 1
                                    ), case
                                         when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                                         else dbo.tzGetDate()
                                       end)) and
                                    COALESCE(DRR2.DrugRequestRow_updDT, DRR2.DrugRequestRow_insDT) <= COALESCE((
                                                                                                                 select case
                                                                                                                                when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                                                                else CAST (:DrugRequestRow_actDate as timestamp)
                                                                                                                              end as actualDate
                                                                                                                 from DrugRequestTotalStatus DRTS
                                                                                                                 where DRTS.Lpu_id = DR2.Lpu_id and
                                                                                                                       DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
                                                                                                                 limit 1
                                    ), case
                                         when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                                         else dbo.tzGetDate()
                                       end)
                            ) OnkoGematRequest ON true
                       where (PC.Lpu_id = :Lpu_id or :Lpu_id = 0) and
                             PC.LpuAttachType_id = 1 and
                             PC.PersonCard_begDate <= COALESCE((
                                                                 select case
                                                                                when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                else CAST (:DrugRequestRow_actDate as timestamp)
                                                                              end as actualDate
                                                                 from DrugRequestTotalStatus DRTS
                                                                 where DRTS.Lpu_id = PC.Lpu_id and
                                                                       DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
                                                                 limit 1
                             ), case
                                  when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                                  else dbo.tzGetDate()
                                end) and
                             (PC.PersonCard_endDate is null or
                             PC.PersonCard_endDate > COALESCE((
                                                                select case
                                                                               when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                               else CAST (:DrugRequestRow_actDate as timestamp)
                                                                             end as actualDate
                                                                from DrugRequestTotalStatus DRTS
                                                                where DRTS.Lpu_id = PC.Lpu_id and
                                                                      DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
                                                                limit 1
                             ), case
                                  when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                                  else dbo.tzGetDate()
                                end))
                       group by Lpu.Lpu_id,
                                Lpu.Lpu_Nick,
                                PC.Person_id,
                                LpuRequest.DrugRequestRow_Summa,
                                LpuReserve.DrugRequestRow_Summa,
                                OtherLpuRequest.DrugRequestRow_Summa,
                                MinZdravRequest.DrugRequestRow_Summa,
                                OnkoDispRequest.DrugRequestRow_Summa,
                                OnkoGematRequest.DrugRequestRow_Summa
                     ) DrugRequest
                group by Lpu_id,
                         Lpu_Name,
                         LpuReserve_Summa),
            AttachPersonCount(
                Lpu_id,
                AttachPerson_Count,
                AttachPerson_SummaLimit
            ) as (
                select PC.Lpu_id,
                       COUNT(DISTINCT PC.Person_id) as AttachPerson_Count,
                       COUNT(DISTINCT PC.Person_id) * :Normativ * 3 * :Koef as AttachPerson_SummaLimit
                from v_PersonCard_all PC
                     inner join DrugRequestPeriod DRP on DRP.DrugRequestPeriod_id =:DrugRequestPeriod_id
                     LEFT JOIN LATERAL
                     (
                       select 1 as IsLgot
                       from PersonPrivilege PP
                            inner join PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id 
                            " . $privilege_code_filter . " " . $person_refuse_join . " " . $fed_lgot_check_join . "
                       where PP.Person_id = PC.Person_id and
                             PP.PersonPrivilege_begDate <= COALESCE((
                                                                      select case
                                                                                     when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                     else CAST (:DrugRequestRow_actDate as timestamp)
                                                                                   end as actualDate
                                                                      from DrugRequestTotalStatus DRTS
                                                                      where DRTS.Lpu_id = PC.Lpu_id and
                                                                            DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
																	  limit 1
                             ), case
                                  when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                                  else dbo.tzGetDate()
                                end) and
                             (PP.PersonPrivilege_endDate is null or
                             PP.PersonPrivilege_endDate >= COALESCE((
                                                                      select case
                                                                                     when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                     else CAST (:DrugRequestRow_actDate as timestamp)
                                                                                   end as actualDate
                                                                      from DrugRequestTotalStatus DRTS
                                                                      where DRTS.Lpu_id = PC.Lpu_id and
                                                                            DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
																	  limit 1
                             ), case
                                  when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                                  else dbo.tzGetDate()
                                end)) 
                                " . $person_refuse_filter . " " . $fed_lgot_check_filter . "
                         limit 1
                     ) Lgot ON true
                where (1 = 1) and
                      (PC.Lpu_id = :Lpu_id or :Lpu_id = 0) and
                      PC.LpuAttachType_id = 1 and
                      PC.PersonCard_begDate <= COALESCE((
                                                          select case
                                                                         when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                         else CAST (:DrugRequestRow_actDate as timestamp)
                                                                       end as actualDate
                                                          from DrugRequestTotalStatus DRTS
                                                          where DRTS.Lpu_id = PC.Lpu_id and
                                                                DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
                                                          limit 1
                      ), case
                           when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                           else dbo.tzGetDate()
                         end) and
                      (PC.PersonCard_endDate is null or
                      PC.PersonCard_endDate > COALESCE((
                                                         select case
                                                                        when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                        else CAST (:DrugRequestRow_actDate as timestamp)
                                                                      end as actualDate
                                                         from DrugRequestTotalStatus DRTS
                                                         where DRTS.Lpu_id = PC.Lpu_id and
                                                               DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
                                                         limit 1
                      ), case
                           when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                           else dbo.tzGetDate()
                         end)) and
                      Lgot.IsLgot = 1
                group by PC.Lpu_id)

            select 
            	" . $select_fields . " 
                   COALESCE(RequestPersonInfo.Lpu_id, 0) as \"Lpu_id\",
                   COALESCE(RequestPersonInfo.Lpu_Name, '-') as \"Lpu_Name\",
                   COALESCE(RequestPersonInfo.DrugRequestRow_PersonCount, 0) as \"Request_PersonCount\",
                   COALESCE(RequestPersonInfo.DrugRequestRow_SummaLimit, 0) as \"Request_SummaLimit\",
                   COALESCE(AttachPersonCount.AttachPerson_Count, 0) as \"Attach_PersonCount\",
                   COALESCE(AttachPersonCount.AttachPerson_SummaLimit, 0) as \"Attach_SummaLimit\",
                   COALESCE(AttachPersonInfo.DrugRequestRow_SummaPerson, 0) as \"Attach_SummaPerson\",
                   COALESCE(AttachPersonInfo.DrugRequestRow_SummaReserve, 0) as \"Attach_SummaReserve\",
                   COALESCE(AttachPersonInfo.DrugRequestRow_SummaMinZdrav, 0) as \"Attach_SummaMinZdrav\",
                   COALESCE(AttachPersonInfo.DrugRequestRow_SummaOnkoDisp, 0) as \"Attach_SummaOnkoDisp\",
                   COALESCE(AttachPersonInfo.DrugRequestRow_SummaOnkoGemat, 0) as \"Attach_SummaOnkoGemat\",
                   COALESCE(AttachPersonInfo.DrugRequestRow_SummaOtherLpu, 0) as \"Attach_SummaOtherLpu\"
            from RequestPersonInfo
                 left join AttachPersonInfo on AttachPersonInfo.Lpu_id = RequestPersonInfo.Lpu_id
                 left join AttachPersonCount on AttachPersonCount.Lpu_id = RequestPersonInfo.Lpu_id 
                 " . $join_str . "
            order by \"Lpu_Name\"
		";
		//echo getDebugSQL($query, $params); exit();
		$res = $this->db->query($query, $params);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	* Получение данных для печати заявки "Заявка других ЛПУ на прикрепленных"
	*/
	function getPrintDrugRequestData10($data) {
		$filter = "(1 = 1)";
		$params = array();

		$params['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];
		$params['DrugRequestRow_actDate'] = $data['DrugRequestRow_actDate'] . ' 23:59:59.000';
		$params['DrugRequestType_id'] = $data['DrugRequestType_id'];
		$params['Lpu_id'] = $data['Lpu_id'];

		$query = "
			select DPM.DrugProtoMnn_id as \"DrugProtoMnn_id\",
             RTRIM(COALESCE(PS.Person_Surname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Firname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Secname, '')) as \"Person_Fio\",
             RTRIM(DPM.DrugProtoMnn_Name) as \"DrugProtoMnn_Name\",
             RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
             RTRIM(COALESCE(AttLpu.Lpu_Nick, '')) as \"AttachLpu_Name\",
             RTRIM(COALESCE(Lpu.Lpu_Nick, '')) as \"Lpu_Name\",
             SUM(COALESCE(DRR.DrugRequestRow_Kolvo, 0)) as \"DrugRequestRow_Kolvo\",
             ROUND(CAST(SUM(DRR.DrugRequestRow_Summa) / SUM(COALESCE(DRR.DrugRequestRow_Kolvo, 0)) as numeric),2) as \"DrugRequestRow_Price\",
             SUM(DRR.DrugRequestRow_Summa) as \"DrugRequestRow_Summa\"
      from DrugRequestRow DRR
           inner join DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id and DR.DrugRequestPeriod_id =:DrugRequestPeriod_id
           inner join DrugRequestPeriod DRP on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
           inner join v_Lpu Lpu on Lpu.Lpu_id = DR.Lpu_id
           inner join v_MedPersonal MP on MP.MedPersonal_id = DR.MedPersonal_id and MP.Lpu_id = DR.Lpu_id
           inner join v_PersonState PS on PS.Person_id = DRR.Person_id
           inner join DrugProtoMnn DPM on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
           LEFT JOIN LATERAL
           (
             select PC.Lpu_id,
                    Lpu2.Lpu_Nick
             from v_PersonCard_all PC
                  inner join v_Lpu Lpu2 on Lpu2.Lpu_id = PC.Lpu_id
             where PC.Person_id = DRR.Person_id and
                   (PC.Lpu_id =:Lpu_id or :Lpu_id = 0) and
                   PC.LpuAttachType_id = 1 and
                   PC.PersonCard_begDate <= COALESCE((
                                                       select case
                                                                      when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                      else CAST (:DrugRequestRow_actDate as timestamp)
                                                                    end as actualDate
                                                       from DrugRequestTotalStatus DRTS
                                                       where DRTS.Lpu_id = PC.Lpu_id and
                                                             DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
                                                       limit 1
                   ), case
                        when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                        else dbo.tzGetDate()
                      end) and
                   (PC.PersonCard_endDate is null or
                   PC.PersonCard_endDate > COALESCE((
                                                      select case
                                                                     when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                     else CAST (:DrugRequestRow_actDate as timestamp)
                                                                   end as actualDate
                                                      from DrugRequestTotalStatus DRTS
                                                      where DRTS.Lpu_id = PC.Lpu_id and
                                                            DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
                                                      limit 1
                   ), case
                        when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                        else dbo.tzGetDate()
                      end))
              limit 1
           ) AttLpu ON true
      where (1 = 1) and
            DR.Lpu_id <> :Lpu_id and
            DR.Lpu_id not in (
                               select id
                               from MinZdravList()
            ) and
            DR.Lpu_id not in (
                               select id
                               from OnkoList()
            ) and
            DR.LpuSection_id <> 337 and
            AttLpu.Lpu_id is not null and
            DRR.DrugRequestType_id =:DrugRequestType_id 
            and
            (DRR.DrugRequestRow_delDT is null or
            DRR.DrugRequestRow_delDT > COALESCE((
                                                  select case
                                                                 when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                 else CAST (:DrugRequestRow_actDate as timestamp)
                                                               end as actualDate
                                                  from DrugRequestTotalStatus DRTS
                                                  where DRTS.Lpu_id = DR.Lpu_id and
                                                        DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
                                                  limit 1
            ), case
                 when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                 else dbo.tzGetDate()
               end)) and
            COALESCE(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= COALESCE((
                                                                                       select case
                                                                                                      when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
                                                                                                      else CAST (:DrugRequestRow_actDate as timestamp)
                                                                                                    end as actualDate
                                                                                       from DrugRequestTotalStatus DRTS
                                                                                       where DRTS.Lpu_id = DR.Lpu_id and
                                                                                             DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
                                                                                       limit 1
            ), case
                 when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
                 else dbo.tzGetDate()
               end)
      group by DPM.DrugProtoMnn_id,
               PS.Person_Surname,
               PS.Person_Firname,
               PS.Person_Secname,
               DPM.DrugProtoMnn_Name,
               MP.Person_Fio,
               Lpu.Lpu_Nick,
               AttLpu.Lpu_Nick
      order by \"DrugProtoMnn_Name\",
               \"Person_Fio\"
		";
		$res = $this->db->query($query, $params);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	* Получение данных для печати заявки "Заявка МЗ на прикрепленных"
	*/
	function getPrintDrugRequestData11($data) {
		$filter = "(1 = 1)";
		$params = array();

		$params['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];
		$params['DrugRequestRow_actDate'] = $data['DrugRequestRow_actDate'] . ' 23:59:59.000';
		$params['DrugRequestType_id'] = $data['DrugRequestType_id'];
		$params['Lpu_id'] = $data['Lpu_id'];

		$query = "
			select COALESCE(DPM.DrugProtoMnn_id, Drug.Drug_id) as \"DrugProtoMnn_id\",
             RTRIM(COALESCE(PS.Person_Surname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Firname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Secname, '')) as \"Person_Fio\",
             RTRIM(COALESCE(DPM.DrugProtoMnn_Name, Drug.Drug_Name, '')) as \"DrugProtoMnn_Name\",
             ''             as \"MedPersonal_Fio\",
             RTRIM(COALESCE(AttLpu.Lpu_Nick, '')) as \"AttachLpu_Name\",
             RTRIM(COALESCE(Lpu.Lpu_Nick, '')) as \"Lpu_Name\",
             SUM(COALESCE(DRR.DrugRequestRow_Kolvo, 0)) as \"DrugRequestRow_Kolvo\",
             ROUND(CAST(SUM(DRR.DrugRequestRow_Summa) / SUM(COALESCE(DRR.DrugRequestRow_Kolvo, 0)) as numeric), 2) as \"DrugRequestRow_Price\",
             SUM(DRR.DrugRequestRow_Summa) as \"DrugRequestRow_Summa\"
      from DrugRequestRow DRR
           inner join DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id and DR.DrugRequestPeriod_id =:DrugRequestPeriod_id
           inner join DrugRequestPeriod DRP on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
           inner join v_Lpu Lpu on Lpu.Lpu_id = DR.Lpu_id
           inner join v_PersonState PS on PS.Person_id = DRR.Person_id
           left join DrugProtoMnn DPM on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
           left join Drug on Drug.Drug_id = DRR.Drug_id
           LEFT JOIN LATERAL
           (
             select PC.Lpu_id,
                    Lpu2.Lpu_Nick
             from v_PersonCard_all PC
                  inner join v_Lpu Lpu2 on Lpu2.Lpu_id = PC.Lpu_id
             where PC.Person_id = DRR.Person_id and
                   (PC.Lpu_id = :Lpu_id or :Lpu_id = 0) and
                   PC.LpuAttachType_id = 1 and
                   PC.PersonCard_begDate <= COALESCE(CAST (:DrugRequestRow_actDate as timestamp), dbo.tzGetDate()) and
                   (PC.PersonCard_endDate is null or
                   PC.PersonCard_endDate > COALESCE(CAST (:DrugRequestRow_actDate as timestamp), dbo.tzGetDate()))
             limit 1      
           ) AttLpu ON true
      where (1 = 1) and
            DR.Lpu_id in (
                           select id
                           from MinZdravList()
            ) and
            AttLpu.Lpu_id is not null and
            DRR.DrugRequestType_id =:DrugRequestType_id and
            (DRR.DrugRequestRow_delDT is null or
            DRR.DrugRequestRow_delDT > COALESCE(CAST (:DrugRequestRow_actDate as timestamp), dbo.tzGetDate())) and
            COALESCE(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= COALESCE(CAST (:DrugRequestRow_actDate as timestamp), dbo.tzGetDate())
      group by DPM.DrugProtoMnn_id,
               Drug.Drug_id,
               PS.Person_Surname,
               PS.Person_Firname,
               PS.Person_Secname,
               DPM.DrugProtoMnn_Name,
               Drug.Drug_Name,
               Lpu.Lpu_Nick,
               AttLpu.Lpu_Nick
      order by \"DrugProtoMnn_Name\",
               \"Person_Fio\"
		";
		// echo getDebugSQL($query, $params); exit();
		$res = $this->db->query($query, $params);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	* Получение данных для печати заявки "Заявка онкодиспансером на прикрепленных"
	*/
	function getPrintDrugRequestData12($data) {
		$filter = "(1 = 1)";
		$params = array();

		$params['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];
		$params['DrugRequestRow_actDate'] = $data['DrugRequestRow_actDate'] . ' 23:59:59.000';
		$params['DrugRequestType_id'] = $data['DrugRequestType_id'];
		$params['Lpu_id'] = $data['Lpu_id'];

		$query = "
			  select DPM.DrugProtoMnn_id as \"DrugProtoMnn_id\",
					 RTRIM(COALESCE(PS.Person_Surname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Firname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Secname, '')) as \"Person_Fio\",
					 RTRIM(DPM.DrugProtoMnn_Name) as \"DrugProtoMnn_Name\",
					 RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
					 RTRIM(COALESCE(AttLpu.Lpu_Nick, '')) as \"AttachLpu_Name\",
					 RTRIM(COALESCE(Lpu.Lpu_Nick, '')) as \"Lpu_Name\",
					 SUM(COALESCE(DRR.DrugRequestRow_Kolvo, 0)) as \"DrugRequestRow_Kolvo\",
					 ROUND(CAST(SUM(DRR.DrugRequestRow_Summa) / SUM(COALESCE(DRR.DrugRequestRow_Kolvo, 0)) as numeric), 2) as \"DrugRequestRow_Price\",
					 SUM(DRR.DrugRequestRow_Summa) as \"DrugRequestRow_Summa\"
			  from DrugRequestRow DRR
				   inner join DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
				   inner join DrugRequestPeriod DRP on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
				   inner join v_Lpu Lpu on Lpu.Lpu_id = DR.Lpu_id
				   inner join v_MedPersonal MP on MP.MedPersonal_id = DR.MedPersonal_id and MP.Lpu_id = DR.Lpu_id
				   inner join v_PersonState PS on PS.Person_id = DRR.Person_id
				   inner join DrugProtoMnn DPM on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
				   LEFT JOIN LATERAL
				   (
					 select PC.Lpu_id,
							Lpu2.Lpu_Nick
					 from v_PersonCard_all PC
						  inner join v_Lpu Lpu2 on Lpu2.Lpu_id = PC.Lpu_id
					 where PC.Person_id = DRR.Person_id and
						   (PC.Lpu_id = :Lpu_id or :Lpu_id = 0) and
						   PC.LpuAttachType_id = 1 and
						   PC.PersonCard_begDate <= COALESCE(CAST (:DrugRequestRow_actDate as timestamp), dbo.tzGetDate()) and
						   (PC.PersonCard_endDate is null or
						   PC.PersonCard_endDate > COALESCE(CAST (:DrugRequestRow_actDate as timestamp), dbo.tzGetDate()))
					 limit 1
				   ) AttLpu ON true
			  where (1 = 1) and
					DR.Lpu_id in (
								   select id
								   from OnkoList()
					) and
					AttLpu.Lpu_id is not null and
					DRR.DrugRequestType_id = :DrugRequestType_id and
					(DRR.DrugRequestRow_delDT is null or
					DRR.DrugRequestRow_delDT > COALESCE(CAST (:DrugRequestRow_actDate as timestamp), dbo.tzGetDate())) and
					COALESCE(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= COALESCE(CAST (:DrugRequestRow_actDate as timestamp), dbo.tzGetDate())
			  group by DPM.DrugProtoMnn_id,
					   PS.Person_Surname,
					   PS.Person_Firname,
					   PS.Person_Secname,
					   DPM.DrugProtoMnn_Name,
					   MP.Person_Fio,
					   Lpu.Lpu_Nick,
					   AttLpu.Lpu_Nick
			  order by \"DrugProtoMnn_Name\",
					   \"Person_Fio\"
		";
		$res = $this->db->query($query, $params);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	* Получение данных для печати заявки "Заявка онкогематологией на прикрепленных"
	*/
	function getPrintDrugRequestData13($data) {
		$filter = "(1 = 1)";
		$params = array();

		$params['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];
		$params['DrugRequestRow_actDate'] = $data['DrugRequestRow_actDate'] . ' 23:59:59.000';
		$params['DrugRequestType_id'] = $data['DrugRequestType_id'];
		$params['Lpu_id'] = $data['Lpu_id'];

		$query = "
			  select DPM.DrugProtoMnn_id as \"DrugProtoMnn_id\",
					 RTRIM(COALESCE(PS.Person_Surname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Firname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Secname, '')) as \"Person_Fio\",
					 RTRIM(DPM.DrugProtoMnn_Name) as \"DrugProtoMnn_Name\",
					 RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
					 RTRIM(COALESCE(AttLpu.Lpu_Nick, '')) as \"AttachLpu_Name\",
					 'ОНКОГЕМАТОЛОГИЯ' as \"Lpu_Name\",
					 SUM(COALESCE(DRR.DrugRequestRow_Kolvo, 0)) as \"DrugRequestRow_Kolvo\",
					 ROUND(CAST(SUM(DRR.DrugRequestRow_Summa) / SUM(COALESCE(DRR.DrugRequestRow_Kolvo, 0)) as numeric), 2) as \"DrugRequestRow_Price\",
					 SUM(DRR.DrugRequestRow_Summa) as \"DrugRequestRow_Summa\"
			  from DrugRequestRow DRR
				   inner join DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
				   inner join DrugRequestPeriod DRP on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
				   inner join v_Lpu Lpu on Lpu.Lpu_id = DR.Lpu_id
				   inner join v_MedPersonal MP on MP.MedPersonal_id = DR.MedPersonal_id and MP.Lpu_id = DR.Lpu_id
				   inner join v_PersonState PS on PS.Person_id = DRR.Person_id
				   inner join DrugProtoMnn DPM on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
				   LEFT JOIN LATERAL
				   (
					 select PC.Lpu_id,
							Lpu2.Lpu_Nick
					 from v_PersonCard_all PC
						  inner join v_Lpu Lpu2 on Lpu2.Lpu_id = PC.Lpu_id
					 where PC.Person_id = DRR.Person_id and
						   (PC.Lpu_id = :Lpu_id or :Lpu_id =  0 is null) and
						   PC.LpuAttachType_id = 1 and
						   PC.PersonCard_begDate <= COALESCE((
															   select case
																			  when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																			  else CAST (:DrugRequestRow_actDate as timestamp)
																			end as actualDate
															   from DrugRequestTotalStatus DRTS
															   where DRTS.Lpu_id = PC.Lpu_id and
																	 DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
															   limit 1
						   ), case
								when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
								else dbo.tzGetDate()
							  end) and
						   (PC.PersonCard_endDate is null or
						   PC.PersonCard_endDate > COALESCE((
															  select case
																			 when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																			 else CAST (:DrugRequestRow_actDate as timestamp)
																		   end as actualDate
															  from DrugRequestTotalStatus DRTS
															  where DRTS.Lpu_id = PC.Lpu_id and
																	DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
															  limit 1
						   ), case
								when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
								else dbo.tzGetDate()
							  end))
					   limit 1
				   ) AttLpu ON true
			  where (1 = 1) and
					DR.LpuSection_id = 337 and
					AttLpu.Lpu_id is not null and
					DRR.DrugRequestType_id =:DrugRequestType_id and
					(DRR.DrugRequestRow_delDT is null or
					DRR.DrugRequestRow_delDT > COALESCE((
														  select case
																		 when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																		 else CAST (:DrugRequestRow_actDate as timestamp)
																	   end as actualDate
														  from DrugRequestTotalStatus DRTS
														  where DRTS.Lpu_id = DR.Lpu_id and
																DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
														  limit 1
					), case
						 when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
						 else dbo.tzGetDate()
					   end)) and
					COALESCE(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= COALESCE((
																							   select case
																											  when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																											  else CAST (:DrugRequestRow_actDate as timestamp)
																											end as actualDate
																							   from DrugRequestTotalStatus DRTS
																							   where DRTS.Lpu_id = DR.Lpu_id and
																									 DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
																							   limit 1
					), case
						 when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
						 else dbo.tzGetDate()
					   end)
			  group by DPM.DrugProtoMnn_id,
					   PS.Person_Surname,
					   PS.Person_Firname,
					   PS.Person_Secname,
					   DPM.DrugProtoMnn_Name,
					   MP.Person_Fio,
					   Lpu.Lpu_Nick,
					   AttLpu.Lpu_Nick
			  order by \"DrugProtoMnn_Name\",
					   \"Person_Fio\"
		";
		$res = $this->db->query($query, $params);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	* Получение данных для печати заявки "Соответствие выписки и заявки с группировкой по пациенту и со списком медикаментов"
	*/
	function getPrintDrugRequestData14($data) {
		$filter = "(1 = 1)";
		$params = array();

		$params['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];
		$params['DrugRequestRow_actDate'] = $data['DrugRequestRow_actDate'] . ' 23:59:59.000';
		$params['DrugRequestType_id'] = $data['DrugRequestType_id'];

		if ( $data['DrugRequestType_id'] == 1 ) {
			$params['ReceptFinance_id'] = 1;
		}
		else if ( $data['DrugRequestType_id'] == 2 ) {
			$params['ReceptFinance_id'] = 2;
		}
		else {
			$params['ReceptFinance_id'] = 0;
		}
		/*
		if ( $data['Lpu_id'] > 0 ) {
			$filter .= " and DR.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		if ( $data['LpuSection_id'] > 0 ) {
			$filter .= " and LS.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ( $data['LpuUnit_id'] > 0 ) {
			$filter .= " and LS.LpuUnit_id = :LpuUnit_id";
			$params['LpuUnit_id'] = $data['LpuUnit_id'];
		}
		*/
		if ( $data['MedPersonal_id'] > 0 ) {
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}
		else {
			$params['MedPersonal_id'] = 0;
		}

		$query = "
			with MedPersonalRequest(
                Person_id,
                Person_Fio,
                Person_Birthday,
                UAddress_Name,
                DrugRequestRow_id,
                Lpu_Nick,
                LpuSection_Name,
                MedPersonal_Fio,
                DrugRequestRow_Code,
                DrugRequestRow_Name,
                DrugRequestRow_Kolvo,
                DrugRequestRow_Price,
                DrugRequestRow_Summa,
                ER_Lpu_Nick,
                ER_LpuSection_Name,
                ER_MedPersonal_Fio,
                EvnRecept_Kolvo,
                Drug_Price,
                EvnRecept_Summa
            ) as (
                select DRR.Person_id,
                       RTRIM(PS.Person_SurName) || ' ' || RTRIM(PS.Person_FirName) || ' ' || RTRIM(PS.Person_SecName) as Person_Fio,
                       to_char(PS.Person_BirthDay, 'DD.MM.YYYY') as Person_Birthday,
                       COALESCE(UAddr.Address_Address, '') as UAddress_Name,
                       DRR.DrugRequestRow_id,
                       RTRIM(Lpu.Lpu_Nick) as Lpu_Nick,
                       COALESCE(LS.LpuSection_Name, '') as LpuSection_Name,
                       COALESCE(MP.Person_Fio, '') as MedPersonal_Fio,
                       COALESCE(DPM.DrugProtoMnn_Code, Drug.Drug_Code) as DrugRequestRow_Code,
                       COALESCE(DPM.DrugProtoMnn_Name, Drug.Drug_Name) as DrugRequestRow_Name,
                       COALESCE(DRR.DrugRequestRow_Kolvo, 0) as DrugRequestRow_Kolvo,
                       case
                         when COALESCE(DRR.DrugRequestRow_Kolvo, 0) > 0 then ROUND(CAST(COALESCE(DRR.DrugRequestRow_Summa, 0) / COALESCE(DRR.DrugRequestRow_Kolvo, 0) as numeric), 2)
                         else 0
                       end as DrugRequestRow_Price,
                       COALESCE(DRR.DrugRequestRow_Summa, 0) as DrugRequestRow_Summa,
                       COALESCE(ERLpu.Lpu_Nick, '') as ER_Lpu_Nick,
                       COALESCE(ERLS.LpuSection_Name, '') as ER_LpuSection_Name,
                       COALESCE(ERMP.Person_Fio, '') as ER_MedPersonal_Fio,
                       case
                         when COALESCE(CAST (DrugData.Drug_DoseUEQ as decimal), CAST (Drug.Drug_DoseUEQ as decimal), 0) > 0 and COALESCE(DrugData.DrugFormGroup_id, DrugForm.DrugFormGroup_id, 0) = COALESCE(ERDF.DrugFormGroup_id, 0) then COALESCE(CAST (ERD.Drug_DoseUEQ as decimal), 0) / COALESCE(CAST (DrugData.Drug_DoseUEQ as decimal), CAST (Drug.Drug_DoseUEQ as decimal)) * COALESCE(ER.EvnRecept_Kolvo, 0)
                         else 0
                       end as EvnRecept_Kolvo,
                       COALESCE(CAST(ER_DrugData.DrugState_Price as numeric), 0) as Drug_Price,
                       COALESCE(CAST(ER.EvnRecept_Kolvo * ER_DrugData.DrugState_Price as numeric), 0) as EvnRecept_Summa
                from DrugRequestRow DRR
                     inner join DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id
                     inner join DrugRequestPeriod DRP on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id and DR.DrugRequestPeriod_id =:DrugRequestPeriod_id
                     inner join v_PersonState PS on PS.Person_id = DRR.Person_id
                     left join Address UAddr on UAddr.Address_id = PS.UAddress_id
                     LEFT JOIN LATERAL
                     (
                       select DRR2.Person_id
                       from DrugRequestRow DRR2
                            inner join DrugRequest DR2 on DR2.DrugRequest_id = DRR2.DrugRequest_id and DR2.MedPersonal_id =:MedPersonal_id 
                            and DR2.DrugRequestPeriod_id =:DrugRequestPeriod_id
                       where DRR2.Person_id = DRR.Person_id and
                             DRR2.DrugRequestType_id =:DrugRequestType_id and
                             (DRR2.DrugRequestRow_delDT is null or
                             DRR2.DrugRequestRow_delDT > DRP.DrugRequestPeriod_endDate)
                       limit 1
                     ) MPP ON true
                     inner join v_Lpu Lpu on Lpu.Lpu_id = DR.Lpu_id
                     left join LpuSection LS on LS.LpuSection_id = DR.LpuSection_id
                     left join v_MedPersonal MP on MP.MedPersonal_id = DR.MedPersonal_id and MP.Lpu_id = DR.Lpu_id
                     left join DrugProtoMnn DPM on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
                     left join Drug on Drug.Drug_id = DRR.Drug_id
                     left join DrugForm on DrugForm.DrugForm_id = Drug.DrugForm_id
                     LEFT JOIN LATERAL
                     (
                       select DF.DrugFormGroup_id,
                              D.Drug_DoseUEQ,
                              D.Drug_DoseUEEi
                       from DrugState DS
                            inner join DrugProto DP on DP.DrugProto_id = DS.DrugProto_id and DP.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
                            inner join Drug D on D.Drug_id = DS.Drug_id
                            inner join DrugForm DF on DF.DrugForm_id = D.DrugForm_id
                       where DS.DrugProtoMnn_id = DPM.DrugProtoMnn_id
                       limit 1
                     ) DrugData ON true
                     left join v_EvnRecept ER on ER.DrugRequestRow_id = DRR.DrugRequestRow_id and ER.Person_id = DRR.Person_id
                     left join Drug ERD on ERD.Drug_id = ER.Drug_id and COALESCE(ERD.Drug_DoseUEEi, '') = COALESCE(DrugData.Drug_DoseUEEi, '') and COALESCE(ERD.Drug_DoseUEQ, 0) = COALESCE(DrugData.Drug_DoseUEQ, 0)
                     left join DrugForm ERDF on ERDF.DrugForm_id = ERD.DrugForm_id and COALESCE(ERDF.DrugFormGroup_id, 0) = COALESCE(DrugData.DrugFormGroup_id, 0)
                     left join v_Lpu ERLpu on ERLpu.Lpu_id = ER.Lpu_id
                     left join LpuSection ERLS on ERLS.LpuSection_id = ER.LpuSection_id
                     left join v_MedPersonal ERMP on ERMP.MedPersonal_id = ER.MedPersonal_id and ERMP.Lpu_id = ER.Lpu_id
                     LEFT JOIN LATERAL
                     (
                       select DS.DrugState_Price
                       from DrugState DS
                            inner join DrugProto DP on DP.DrugProto_id = DS.DrugProto_id and DP.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
                       where DS.Drug_id = ERD.Drug_id
                       limit 1
                     ) ER_DrugData ON true
                where 
                " . $filter . " and
                      DRR.DrugRequestType_id = :DrugRequestType_id and
                      MPP.Person_id is not null and
                      (DRR.DrugRequestRow_delDT is null or
                      DRR.DrugRequestRow_delDT > DRP.DrugRequestPeriod_endDate)),
            OutOfRequest(
                Person_id,
                Person_Fio,
                Person_Birthday,
                UAddress_Name,
                DrugRequestRow_id,
                Lpu_Nick,
                LpuSection_Name,
                MedPersonal_Fio,
                DrugRequestRow_Code,
                DrugRequestRow_Name,
                DrugRequestRow_Kolvo,
                DrugRequestRow_Price,
                DrugRequestRow_Summa,
                ER_Lpu_Nick,
                ER_LpuSection_Name,
                ER_MedPersonal_Fio,
                EvnRecept_Kolvo,
                Drug_Price,
                EvnRecept_Summa
            ) as (
                select ER.Person_id,
                       RTRIM(PS.Person_SurName) || ' ' || RTRIM(PS.Person_FirName) || ' ' || RTRIM(PS.Person_SecName) as Person_Fio,
                       to_char(PS.Person_BirthDay, 'DD.MM.YYYY') as Person_Birthday,
                       COALESCE(UAddr.Address_Address, '') as UAddress_Name,
                       0 as DrugRequestRow_id,
                       '=== ВНЕ ЗАЯВКИ ==='                       as Lpu_Nick,
                       ''                       as LpuSection_Name,
                       ''                       as MedPersonal_Fio,
                       COALESCE(CAST(ERD.Drug_Code as varchar), '') as DrugRequestRow_Code,
                       COALESCE(ERD.Drug_Name, '') as DrugRequestRow_Name,
                       0 as DrugRequestRow_Kolvo,
                       0 as DrugRequestRow_Price,
                       0 as DrugRequestRow_Summa,
                       COALESCE(ERLpu.Lpu_Nick, '') as ER_Lpu_Nick,
                       COALESCE(ERLS.LpuSection_Name, '') as ER_LpuSection_Name,
                       COALESCE(ERMP.Person_Fio, '') as ER_MedPersonal_Fio,
                       COALESCE(ER.EvnRecept_Kolvo, 0) as EvnRecept_Kolvo,
                       COALESCE(CAST(ER_DrugData.DrugState_Price as numeric), 0) as Drug_Price,
                       COALESCE(CAST(ER.EvnRecept_Kolvo * ER_DrugData.DrugState_Price as numeric), 0) as EvnRecept_Summa
                from v_EvnRecept ER
                     inner join DrugRequestPeriod DRP on DRP.DrugRequestPeriod_id = :DrugRequestPeriod_id 
                     and ER.EvnRecept_setDT between DRP.DrugRequestPeriod_begDate and DRP.DrugRequestPeriod_endDate
                     inner join Drug ERD on ERD.Drug_id = ER.Drug_id
                     left join v_Lpu ERLpu on ERLpu.Lpu_id = ER.Lpu_id
                     left join LpuSection ERLS on ERLS.LpuSection_id = ER.LpuSection_id
                     left join v_MedPersonal ERMP on ERMP.MedPersonal_id = ER.MedPersonal_id and ERMP.Lpu_id = ER.Lpu_id
                     inner join v_PersonState PS on PS.Person_id = ER.Person_id
                     left join Address UAddr on UAddr.Address_id = PS.UAddress_id
                     LEFT JOIN LATERAL
                     (
                       select DRR2.Person_id
                       from DrugRequestRow DRR2
                            inner join DrugRequest DR2 on DR2.DrugRequest_id = DRR2.DrugRequest_id and DR2.MedPersonal_id = :MedPersonal_id
                       where DRR2.Person_id = ER.Person_id and
                             DRR2.DrugRequestType_id =:DrugRequestType_id and
                             (DRR2.DrugRequestRow_delDT is null or
                             DRR2.DrugRequestRow_delDT > DRP.DrugRequestPeriod_endDate)
                       limit 1
                     ) MPP ON true
                     LEFT JOIN LATERAL
                     (
                       select DS.DrugState_Price
                       from DrugState DS
                            inner join DrugProto DP on DP.DrugProto_id = DS.DrugProto_id and DP.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
                       where DS.Drug_id = ERD.Drug_id
                       limit 1
                     ) ER_DrugData ON true
                where (1 = 1) and
                      ER.ReceptFinance_id =:ReceptFinance_id and
                      ER.DrugRequestRow_id is null and
                      MPP.Person_id is not null)

            select Person_id as \"Person_id\",
                   Person_Fio as \"Person_Fio\",
                   Person_Birthday as \"Person_Birthday\",
                   UAddress_Name as \"UAddress_Name\",
                   DrugRequestRow_id as \"DrugRequestRow_id\",
                   Lpu_Nick as \"Lpu_Nick\",
                   LpuSection_Name as \"LpuSection_Name\",
                   MedPersonal_Fio as \"MedPersonal_Fio\",
                   CAST(DrugRequestRow_Code as varchar) as \"DrugRequestRow_Code\",
                   DrugRequestRow_Name as \"DrugRequestRow_Name\",
                   DrugRequestRow_Kolvo as \"DrugRequestRow_Kolvo\",
                   DrugRequestRow_Price as \"DrugRequestRow_Price\",
                   DrugRequestRow_Summa as \"DrugRequestRow_Summa\",
                   ER_Lpu_Nick as \"ER_Lpu_Nick\",
                   ER_LpuSection_Name as \"ER_LpuSection_Name\",
                   ER_MedPersonal_Fio as \"ER_MedPersonal_Fio\",
                   EvnRecept_Kolvo as \"EvnRecept_Kolvo\",
                   Drug_Price as \"Drug_Price\",
                   EvnRecept_Summa as \"EvnRecept_Summa\"
            from MedPersonalRequest
            union all
            select Person_id as \"Person_id\",
                   Person_Fio as \"Person_Fio\",
                   Person_Birthday as \"Person_Birthday\",
                   UAddress_Name as \"UAddress_Name\",
                   DrugRequestRow_id as \"DrugRequestRow_id\",
                   Lpu_Nick as \"Lpu_Nick\",
                   LpuSection_Name as \"LpuSection_Name\",
                   MedPersonal_Fio as \"MedPersonal_Fio\",
                   DrugRequestRow_Code as \"DrugRequestRow_Code\",
                   DrugRequestRow_Name as \"DrugRequestRow_Name\",
                   DrugRequestRow_Kolvo as \"DrugRequestRow_Kolvo\",
                   DrugRequestRow_Price as \"DrugRequestRow_Price\",
                   DrugRequestRow_Summa as \"DrugRequestRow_Summa\",
                   ER_Lpu_Nick as \"ER_Lpu_Nick\",
                   ER_LpuSection_Name as \"ER_LpuSection_Name\",
                   ER_MedPersonal_Fio as \"ER_MedPersonal_Fio\",
                   EvnRecept_Kolvo as \"EvnRecept_Kolvo\",
                   Drug_Price as \"Drug_Price\",
                   EvnRecept_Summa as \"EvnRecept_Summa\"
            from OutOfRequest
            order by \"Person_Fio\",
                     \"DrugRequestRow_id\" desc,
                     \"Lpu_Nick\"
		";
		// echo getDebugSQL($query, $params); exit();
		$res = $this->db->query($query, $params);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	* Получение данных для печати заявки "Несоответствие типа льготы и типа заявки"
	*/
	function getPrintDrugRequestData15($data) {
		$filter = "(1 = 1)";
		$params = array();

		$params['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];
		$params['DrugRequestRow_actDate'] = $data['DrugRequestRow_actDate'] . ' 23:59:59.000';

		if ( $data['Lpu_id'] > 0 ) {
			$filter .= " and DR.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		if ( $data['LpuSection_id'] > 0 ) {
			$filter .= " and LS.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ( $data['LpuUnit_id'] > 0 ) {
			$filter .= " and LS.LpuUnit_id = :LpuUnit_id";
			$params['LpuUnit_id'] = $data['LpuUnit_id'];
		}

		if ( $data['MedPersonal_id'] > 0 ) {
			$filter .= " and DR.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		$query = "
			  select RTRIM(COALESCE(Lpu.Lpu_Nick, '')) as \"Lpu_Name\",
					 RTRIM(COALESCE(MP.Person_Fio, '')) as \"MedPersonal_Fio\",
					 RTRIM(RTRIM(COALESCE(PS.Person_Surname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Firname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Secname, ''))) as \"Person_Fio\",
					 case
					   when FedLgot.IsFedLgot = 1 then 'Федеральная'
					   when RegLgot.IsRegLgot = 1 then 'Региональная'
					   else 'Нет'
					 end as \"PrivilegeFinance_Name\",
					 RTRIM(COALESCE(DRT.DrugRequestType_Name, '')) as \"DrugRequestType_Name\",
					 RTRIM(COALESCE(DPM.DrugProtoMnn_Name, '')) as \"DrugRequestRow_Name\",
					 SUM(COALESCE(DRR.DrugRequestRow_Kolvo, 0)) as \"DrugRequestRow_Kolvo\",
					 ROUND(CAST(SUM(DRR.DrugRequestRow_Summa) / SUM(COALESCE(DRR.DrugRequestRow_Kolvo, 0)) as numeric), 2) as \"DrugRequestRow_Price\",
					 SUM(DRR.DrugRequestRow_Summa) as \"DrugRequestRow_Summa\"
			  from DrugRequestRow DRR
				   inner join DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id
				   inner join DrugRequestType DRT on DRT.DrugRequestType_id = DRR.DrugRequestType_id
				   inner join DrugRequestPeriod DRP on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id and DRP.DrugRequestPeriod_id =:DrugRequestPeriod_id
				   inner join DrugProtoMnn DPM on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
				   inner join v_PersonState PS on PS.Person_id = DRR.Person_id
				   inner join v_Lpu Lpu on Lpu.Lpu_id = DR.Lpu_id
				   inner join v_MedPersonal MP on MP.MedPersonal_id = DR.MedPersonal_id and MP.Lpu_id = Lpu.Lpu_id
				   left join LpuSection LS on LS.LpuSection_id = DR.LpuSection_id
				   LEFT JOIN LATERAL
				   (
					 select 1 as IsFedLgot
					 from PersonPrivilege PP
						  inner join PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id and PT.ReceptFinance_id = 1 and isnumeric(PT.PrivilegeType_Code) = 1
						  left join v_PersonRefuse PR on PR.Person_id = PP.Person_id and PR.PersonRefuse_Year = date_part('YEAR', DRP.DrugRequestPeriod_begDate) and PR.PersonRefuse_IsRefuse = 2
					 where PP.Person_id = DRR.Person_id and
						   PP.PersonPrivilege_begDate <= COALESCE((
																	select case
																				   when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																				   else CAST (:DrugRequestRow_actDate as timestamp)
																				 end as actualDate
																	from DrugRequestTotalStatus DRTS
																	where DRTS.Lpu_id = DR.Lpu_id and
																		  DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
																	limit 1
						   ), case
								when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
								else dbo.tzGetDate()
							  end) and
						   (PP.PersonPrivilege_endDate is null or
						   PP.PersonPrivilege_endDate >= COALESCE((
																	select case
																				   when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																				   else CAST (:DrugRequestRow_actDate as timestamp)
																				 end as actualDate
																	from DrugRequestTotalStatus DRTS
																	where DRTS.Lpu_id = DR.Lpu_id and
																		  DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
																	limit 1
						   ), case
								when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
								else dbo.tzGetDate()
							  end)) and
						   COALESCE(PR.PersonRefuse_IsRefuse, 1) = 1
					  limit 1
				   ) FedLgot ON true
				   LEFT JOIN LATERAL
				   (
					 select 1 as IsRegLgot
					 from PersonPrivilege PP
						  inner join PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id and PT.ReceptFinance_id = 2 and isnumeric(PT.PrivilegeType_Code) = 1
						  LEFT JOIN LATERAL
						  (
							select 1 as IsFedLgot
							from v_PersonPrivilege PP2
								 inner join PrivilegeType PT2 on PT2.PrivilegeType_id = PP2.PrivilegeType_id and PT2.ReceptFinance_id = 1 and isnumeric(PT2.PrivilegeType_Code) = 1
							where PP2.Person_id = PP.Person_id and
								  PP2.PersonPrivilege_begDate <= COALESCE((
																			select case
																						   when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																						   else CAST (:DrugRequestRow_actDate as timestamp)
																						 end as actualDate
																			from DrugRequestTotalStatus DRTS
																			where DRTS.Lpu_id = DR.Lpu_id and
																				  DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
																			limit 1
								  ), case
									   when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
									   else dbo.tzGetDate()
									 end) and
								  (PP2.PersonPrivilege_endDate is null or
								  PP2.PersonPrivilege_endDate >= COALESCE((
																			select case
																						   when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																						   else CAST (:DrugRequestRow_actDate as timestamp)
																						 end as actualDate
																			from DrugRequestTotalStatus DRTS
																			where DRTS.Lpu_id = DR.Lpu_id and
																				  DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
																			limit 1
								  ), case
									   when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
									   else dbo.tzGetDate()
									 end))
							 limit 1
						  ) FL ON true
					 where PP.Person_id = DRR.Person_id and
						   PP.PersonPrivilege_begDate <= COALESCE((
																	select case
																				   when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																				   else CAST (:DrugRequestRow_actDate as timestamp)
																				 end as actualDate
																	from DrugRequestTotalStatus DRTS
																	where DRTS.Lpu_id = DR.Lpu_id and
																		  DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
																	limit 1
						   ), case
								when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
								else dbo.tzGetDate()
							  end) and
						   (PP.PersonPrivilege_endDate is null or
						   PP.PersonPrivilege_endDate >= COALESCE((
																	select case
																				   when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																				   else CAST (:DrugRequestRow_actDate as timestamp)
																				 end as actualDate
																	from DrugRequestTotalStatus DRTS
																	where DRTS.Lpu_id = DR.Lpu_id and
																		  DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
																	limit 1
						   ), case
								when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
								else dbo.tzGetDate()
							  end)) and
						   FL.IsFedLgot is null
					  limit 1
				   ) RegLgot ON true
			  where 
			  " . $filter . "
					-- Тип заявки
					and
					(DRR.DrugRequestRow_delDT is null or
					DRR.DrugRequestRow_delDT > COALESCE((
														  select case
																		 when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																		 else CAST (:DrugRequestRow_actDate as timestamp)
																	   end as actualDate
														  from DrugRequestTotalStatus DRTS
														  where DRTS.Lpu_id = DR.Lpu_id and
																DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
														  limit 1
					), case
						 when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
						 else dbo.tzGetDate()
					   end))
					-- Заявка актуальна на выбранную дату
					and
					COALESCE(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= COALESCE((
																							   select case
																											  when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																											  else CAST (:DrugRequestRow_actDate as timestamp)
																											end as actualDate
																							   from DrugRequestTotalStatus DRTS
																							   where DRTS.Lpu_id = DR.Lpu_id and
																									 DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
																							   limit 1
					), case
						 when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
						 else dbo.tzGetDate()
					   end)
					-- Несоответствие типа финансирования медикамента и льготы
					and
					((FedLgot.IsFedLgot = 1 and
					DRT.DrugRequestType_id <> 1) or
					(RegLgot.IsRegLgot = 1 and
					DRT.DrugRequestType_id <> 2) or
					(FedLgot.IsFedLgot is null and
					RegLgot.IsRegLgot is null))
			  group by Lpu.Lpu_Nick,
					   MP.Person_Fio,
					   PS.Person_Surname,
					   PS.Person_Firname,
					   PS.Person_Secname,
					   FedLgot.IsFedLgot,
					   RegLgot.IsRegLgot,
					   DPM.DrugProtoMnn_id,
					   DPM.DrugProtoMnn_Name,
					   DRT.DrugRequestType_Name
			  order by \"DrugRequestRow_Name\"
		";
		// echo getDebugSQL($query, $params); exit();
		$res = $this->db->query($query, $params);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	* Получение данных для печати заявки "Заявка на прикрепленных к другим ЛПУ"
	*/
	function getPrintDrugRequestData16($data) {
		$filter = "(1 = 1)";
		$params = array();

		$params['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];
		$params['DrugRequestRow_actDate'] = $data['DrugRequestRow_actDate'] . ' 23:59:59.000';
		$params['DrugRequestType_id'] = $data['DrugRequestType_id'];
		$params['Lpu_id'] = $data['Lpu_id'];

		$query = "
			  select DPM.DrugProtoMnn_id as \"DrugProtoMnn_id\",
					 RTRIM(COALESCE(PS.Person_Surname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Firname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Secname, '')) as \"Person_Fio\",
					 RTRIM(DPM.DrugProtoMnn_Name) as \"DrugProtoMnn_Name\",
					 RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
					 RTRIM(Lpu.Lpu_Nick) as \"Lpu_Name\",
					 COALESCE(AttLpu.AttachLpu_Name, '') as \"AttachLpu_Name\",
					 SUM(COALESCE(DRR.DrugRequestRow_Kolvo, 0)) as \"DrugRequestRow_Kolvo\",
					 ROUND(CAST(SUM(DRR.DrugRequestRow_Summa) / SUM(COALESCE(DRR.DrugRequestRow_Kolvo, 0)) as numeric), 2) as \"DrugRequestRow_Price\",
					 SUM(DRR.DrugRequestRow_Summa) as \"DrugRequestRow_Summa\"
			  from DrugRequestRow DRR
				   inner join DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id and DR.DrugRequestPeriod_id =:DrugRequestPeriod_id
				   inner join DrugRequestPeriod DRP on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
				   inner join v_Lpu Lpu on Lpu.Lpu_id = DR.Lpu_id
				   inner join v_MedPersonal MP on MP.MedPersonal_id = DR.MedPersonal_id and MP.Lpu_id = DR.Lpu_id
				   inner join v_PersonState PS on PS.Person_id = DRR.Person_id
				   inner join DrugProtoMnn DPM on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
				   LEFT JOIN LATERAL
				   (
					 select PC.Lpu_id
					 from v_PersonCard_all PC
					 where PC.Person_id = DRR.Person_id and
						   PC.Lpu_id = DR.Lpu_id and
						   PC.LpuAttachType_id = 1 and
						   PC.PersonCard_begDate <= COALESCE((
															   select case
																			  when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																			  else CAST (:DrugRequestRow_actDate as timestamp)
																			end as actualDate
															   from DrugRequestTotalStatus DRTS
															   where DRTS.Lpu_id = PC.Lpu_id and
																	 DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
															   limit 1
						   ), case
								when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
								else dbo.tzGetDate()
							  end) and
						   (PC.PersonCard_endDate is null or
						   PC.PersonCard_endDate > COALESCE((
															  select case
																			 when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																			 else CAST (:DrugRequestRow_actDate as timestamp)
																		   end as actualDate
															  from DrugRequestTotalStatus DRTS
															  where DRTS.Lpu_id = PC.Lpu_id and
																	DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
															  limit 1
						   ), case
								when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
								else dbo.tzGetDate()
							  end))
					   limit 1 
				   ) AttLpuDR ON true
				   LEFT JOIN LATERAL
				   (
					 select RTRIM(COALESCE(Lpu2.Lpu_Nick, '')) as AttachLpu_Name
					 from v_PersonCard_all PC
						  inner join v_Lpu Lpu2 on Lpu2.Lpu_id = PC.Lpu_id
					 where PC.Person_id = DRR.Person_id and
						   PC.LpuAttachType_id = 1 and
						   PC.PersonCard_begDate <= COALESCE((
															   select case
																			  when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																			  else CAST (:DrugRequestRow_actDate as timestamp)
																			end as actualDate
															   from DrugRequestTotalStatus DRTS
															   where DRTS.Lpu_id = PC.Lpu_id and
																	 DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
						   ), case
								when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
								else dbo.tzGetDate()
							  end) and
						   (PC.PersonCard_endDate is null or
						   PC.PersonCard_endDate > COALESCE((
															  select case
																			 when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																			 else CAST (:DrugRequestRow_actDate as timestamp)
																		   end as actualDate
															  from DrugRequestTotalStatus DRTS
															  where DRTS.Lpu_id = PC.Lpu_id and
																	DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
															  limit 1
						   ), case
								when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
								else dbo.tzGetDate()
							  end))
					 order by PC.PersonCard_begDate desc
					 limit 1
				   ) AttLpu ON true
			  where (1 = 1) and
					COALESCE(:Lpu_id, DR.Lpu_id) = DR.Lpu_id and
					DR.Lpu_id not in (
									   select id
									   from MinZdravList()
					) and
					DR.Lpu_id not in (
									   select id
									   from OnkoList()
					) and
					DR.LpuSection_id <> 337 and
					AttLpuDR.Lpu_id is null and
					DRR.DrugRequestType_id =:DrugRequestType_id and
					(DRR.DrugRequestRow_delDT is null or
					DRR.DrugRequestRow_delDT > COALESCE((
														  select case
																		 when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																		 else CAST (:DrugRequestRow_actDate as timestamp)
																	   end as actualDate
														  from DrugRequestTotalStatus DRTS
														  where DRTS.Lpu_id = DR.Lpu_id and
																DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
														  limit 1
					), case
						 when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
						 else dbo.tzGetDate()
					   end)) and
					COALESCE(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= COALESCE((
																							   select case
																											  when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																											  else CAST (:DrugRequestRow_actDate as timestamp)
																											end as actualDate
																							   from DrugRequestTotalStatus DRTS
																							   where DRTS.Lpu_id = DR.Lpu_id and
																									 DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
																							   limit 1
					), case
						 when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
						 else dbo.tzGetDate()
					   end)
			  group by DPM.DrugProtoMnn_id,
					   PS.Person_Surname,
					   PS.Person_Firname,
					   PS.Person_Secname,
					   DPM.DrugProtoMnn_Name,
					   MP.Person_Fio,
					   Lpu.Lpu_Nick,
					   AttLpu.AttachLpu_Name
			  order by \"DrugProtoMnn_Name\",
					   \"Person_Fio\"
		";
		$res = $this->db->query($query, $params);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	* Получение данных для печати заявки "Сводная заявка по медикаментам (отчет для МЗ)"
	*/
	function getPrintDrugRequestData17($data) {
		$filter = "(1 = 1)";
		$params = array();

		$params['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];
		$params['DrugRequestRow_actDate'] = $data['DrugRequestRow_actDate'] . ' 23:59:59.000';
		$params['DrugRequestType_id'] = $data['DrugRequestType_id'];
		$params['Lpu_id'] = $data['Lpu_id'];

		$filter .= " and COALESCE((CASE WHEN :Lpu_id = 0 THEN true ELSE false END), DR.Lpu_id) = DR.Lpu_id";


		if ( $data['LpuSection_id'] > 0 ) {
			$filter .= " and LS.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ( $data['LpuUnit_id'] > 0 ) {
			$filter .= " and LS.LpuUnit_id = :LpuUnit_id";
			$params['LpuUnit_id'] = $data['LpuUnit_id'];
		}

		if ( $data['MedPersonal_id'] > 0 ) {
			$filter .= " and DR.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		$query = "
			  select RTRIM(COALESCE(CAST(DPM.DrugProtoMnn_Code as varchar), CAST(Drug.Drug_Code as varchar), '')) as \"DrugRequestRow_Code\",
					 RTRIM(COALESCE(DPM.DrugProtoMnn_Name, Drug.Drug_Name, '')) as \"DrugRequestRow_Name\",
					 SUM(COALESCE(DRR.DrugRequestRow_Kolvo, 0)) as \"DrugRequestRow_Kolvo\",
					 ROUND(CAST(SUM(DRR.DrugRequestRow_Summa) / SUM(COALESCE(DRR.DrugRequestRow_Kolvo, 0)) as numeric), 2) as \"DrugRequestRow_Price\",
					 SUM(DRR.DrugRequestRow_Summa) as \"DrugRequestRow_Summa\"
			  from DrugRequestRow DRR
				   inner join DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id and DR.DrugRequestPeriod_id =:DrugRequestPeriod_id
				   left join DrugProtoMnn DPM on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
				   left join Drug on Drug.Drug_id = DRR.Drug_id
				   left join LpuSection LS on LS.LpuSection_id = DR.LpuSection_id
				   LEFT JOIN LATERAL
				   (
					 select PC.Lpu_id
					 from v_PersonCard_all PC
					 where PC.LpuAttachType_id = 1 and
						   (CASE WHEN :Lpu_id = 0 THEN PC.Lpu_id ELSE :Lpu_id END) = PC.Lpu_id and
						   PC.Person_id = DRR.Person_id and
						   PC.PersonCard_begDate <= COALESCE((
															   select case
																			  when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																			  else CAST (:DrugRequestRow_actDate as timestamp)
																			end as actualDate
															   from DrugRequestTotalStatus DRTS
															   where DRTS.Lpu_id = PC.Lpu_id and
																	 DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
															   limit 1
						   ), case
								when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
								else dbo.tzGetDate()
							  end) and
						   (PC.PersonCard_endDate is null or
						   PC.PersonCard_endDate > COALESCE((
															  select case
																			 when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																			 else CAST (:DrugRequestRow_actDate as timestamp)
																		   end as actualDate
															  from DrugRequestTotalStatus DRTS
															  where DRTS.Lpu_id = PC.Lpu_id and
																	DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
															  limit 1
						   ), case
								when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
								else dbo.tzGetDate()
							  end))
					 order by PC.PersonCard_begDate desc
					 limit 1
				   ) AttLpu ON true
			  where (1 = 1) and
					((
		" . $filter . " and
					DR.LpuSection_id != 337) or
					((DR.Lpu_id in (
									 select id
									 from MinZdravList()
					) or
					DR.Lpu_id in (
								   select id
								   from OnkoList()
					) or
					DR.LpuSection_id = 337) and
					(AttLpu.Lpu_id is not null or :Lpu_id = 0))) and
					DRR.DrugRequestType_id =:DrugRequestType_id and
					(DRR.DrugRequestRow_delDT is null or
					DRR.DrugRequestRow_delDT > COALESCE((
														  select case
																		 when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																		 else CAST (:DrugRequestRow_actDate as timestamp)
																	   end as actualDate
														  from DrugRequestTotalStatus DRTS
														  where DRTS.Lpu_id = DR.Lpu_id and
																DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
														  limit 1
					), case
						 when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
						 else dbo.tzGetDate()
					   end)) and
					COALESCE(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= COALESCE((
																							   select case
																											  when COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST (:DrugRequestRow_actDate as timestamp) then COALESCE(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate())
																											  else CAST (:DrugRequestRow_actDate as timestamp)
																											end as actualDate
																							   from DrugRequestTotalStatus DRTS
																							   where DRTS.Lpu_id = DR.Lpu_id and
																									 DRTS.DrugRequestPeriod_id =:DrugRequestPeriod_id
																							   limit 1
					), case
						 when CAST (:DrugRequestRow_actDate as timestamp) < dbo.tzGetDate() then CAST (:DrugRequestRow_actDate as timestamp)
						 else dbo.tzGetDate()
					   end)
			  group by DPM.DrugProtoMnn_Code,
					   DPM.DrugProtoMnn_Name,
					   Drug.Drug_Code,
					   Drug.Drug_Name
			  order by \"DrugRequestRow_Name\"
		";
		// echo getDebugSQL($query, $params); exit();
		$res = $this->db->query($query, $params);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Функция
	 */
	function getDrugRequestHeaderData($data) {
		$query = "
  			  select RTRIM(COALESCE(DRP.DrugRequestPeriod_Name, '')) as \"DrugRequestPeriod_Name\",
					 RTRIM(COALESCE(DRT.DrugRequestType_Name, '')) as \"DrugRequestType_Name\",
					 RTRIM(COALESCE(Lpu.Lpu_Name, '')) as \"Lpu_Name\",
					 RTRIM(COALESCE(LS.LpuSection_Name, '')) as \"LpuSection_Name\",
					 RTRIM(COALESCE(LU.LpuUnit_Name, '')) as \"LpuUnit_Name\",
					 RTRIM(COALESCE(MP.Person_Fio, '')) as \"MedPersonal_Fio\"
			  from DrugRequestPeriod DRP
				   left join DrugRequestType DRT on DRT.DrugRequestType_id =:DrugRequestType_id
				   left join v_Lpu Lpu on Lpu.Lpu_id =:Lpu_id
				   left join LpuSection LS on LS.LpuSection_id =:LpuSection_id
				   left join LpuUnit LU on LU.LpuUnit_id =:LpuUnit_id
				   left join v_MedPersonal MP on MP.MedPersonal_id =:MedPersonal_id
			  where DRP.DrugRequestPeriod_id =:DrugRequestPeriod_id
		";
		$params = array(
			'DrugRequestType_id' => $data['DrugRequestType_id'],
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'LpuUnit_id' => $data['LpuUnit_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'DrugRequestPeriod_id' => $data['DrugRequestPeriod_id']
		);

		$res = $this->db->query($query, $params);

		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Загрузка списка заявок
	 */
	function loadList($filter) {
		$where = array();
		$p = array();
		
		if (isset($filter['Year']) && $filter['Year']) {			
			$where[] = 'DATE_PART(\'year\',DrugRequestPeriod_id_ref.DrugRequestPeriod_begDate) = :Year';
			$p['Year'] = $filter['Year'];
		}
		if (isset($filter['DrugRequestPeriod_id']) && $filter['DrugRequestPeriod_id']) {
			$where[] = 'v_DrugRequest.DrugRequestPeriod_id = :DrugRequestPeriod_id';
			$p['DrugRequestPeriod_id'] = $filter['DrugRequestPeriod_id'];
		}
			
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		$q = "
			  SELECT v_DrugRequest.Server_id as \"Server_id\",
					 v_DrugRequest.DrugRequest_id as \"DrugRequest_id\",
					 v_DrugRequest.DrugRequestPeriod_id as \"DrugRequestPeriod_id\",
					 v_DrugRequest.DrugRequestStatus_id as \"DrugRequestStatus_id\",
					 v_DrugRequest.DrugRequest_Name as \"DrugRequest_Name\",
					 v_DrugRequest.Lpu_id as \"Lpu_id\",
					 v_DrugRequest.LpuSection_id as \"LpuSection_id\",
					 v_DrugRequest.MedPersonal_id as \"MedPersonal_id\",
					 v_DrugRequest.DrugRequest_Summa as \"DrugRequest_Summa\",
					 v_DrugRequest.DrugRequest_YoungChildCount as \"DrugRequest_YoungChildCount\",
					 DrugRequestPeriod_id_ref.DrugRequestPeriod_Name as \"DrugRequestPeriod_id_Name\",
					 DrugRequestStatus_id_ref.DrugRequestStatus_Name as \"DrugRequestStatus_id_Name\",
					 LpuSection_id_ref.LpuSection_Name as \"LpuSection_id_Name\"
			  FROM dbo.v_DrugRequest
				   LEFT JOIN dbo.v_DrugRequestPeriod DrugRequestPeriod_id_ref ON DrugRequestPeriod_id_ref.DrugRequestPeriod_id = v_DrugRequest.DrugRequestPeriod_id
				   LEFT JOIN dbo.v_DrugRequestStatus DrugRequestStatus_id_ref ON DrugRequestStatus_id_ref.DrugRequestStatus_id = v_DrugRequest.DrugRequestStatus_id
				   LEFT JOIN dbo.v_Lpu Lpu_id_ref ON Lpu_id_ref.Lpu_id = v_DrugRequest.Lpu_id
				   LEFT JOIN dbo.v_LpuSection LpuSection_id_ref ON LpuSection_id_ref.LpuSection_id = v_DrugRequest.LpuSection_id
			  LIMIT 1000
			$where_clause
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка данных конкртеного рабочего периода
	 */
	function loadDrugRequestPeriod($data) {
		$q = "
			  select DrugRequestPeriod_id as \"DrugRequestPeriod_id\",
					 DrugRequestPeriod_begDate as \"DrugRequestPeriod_begDate\",
					 DrugRequestPeriod_endDate as \"DrugRequestPeriod_endDate\",
					 DrugRequestPeriod_Name as \"DrugRequestPeriod_Name\"
			  from dbo.v_DrugRequestPeriod
			  where DrugRequestPeriod_id =:DrugRequestPeriod_id
		";
		$r = $this->db->query($q, array('DrugRequestPeriod_id' => $data['DrugRequestPeriod_id']));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка рабочих периодов
	 */
	function loadDrugRequestPeriodList($data) {
		$q = "
 			  SELECT v_DrugRequestPeriod.DrugRequestPeriod_id as \"DrugRequestPeriod_id\",
					 to_char(v_DrugRequestPeriod.DrugRequestPeriod_begDate, 'DD.MM.YYYY') as \"DrugRequestPeriod_begDate\",
					 to_char(v_DrugRequestPeriod.DrugRequestPeriod_endDate, 'DD.MM.YYYY') as \"DrugRequestPeriod_endDate\",
					 v_DrugRequestPeriod.DrugRequestPeriod_begDate as \"DrugRequestPeriod_Sort\",
					 (to_char(v_DrugRequestPeriod.DrugRequestPeriod_begDate, 'DD.MM.YYYY') || ' - ' || to_char(v_DrugRequestPeriod.DrugRequestPeriod_endDate, 'DD.MM.YYYY')) as \"DrugRequestPeriod_TimeRange\",
					 v_DrugRequestPeriod.DrugRequestPeriod_Name as \"DrugRequestPeriod_Name\"
			  FROM dbo.v_DrugRequestPeriod
			  where
					:DrugRequestPeriod_id is null or
					v_DrugRequestPeriod.DrugRequestPeriod_id =:DrugRequestPeriod_id
			  order by v_DrugRequestPeriod.DrugRequestPeriod_begDate
		";
		$result = $this->db->query($q, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение рабочего периода
	 */
	function saveDrugRequestPeriod($data) {
		$procedure = 'p_DrugRequestPeriod_ins';
		if ( $data['DrugRequestPeriod_id'] > 0 ) {
			$procedure = 'p_DrugRequestPeriod_upd';
		}
		$q = "
			select DrugRequestPeriod_id as \"DrugRequestPeriod_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"            
			from dbo." . $procedure . "(
				DrugRequestPeriod_id := :DrugRequestPeriod_id,
				DrugRequestPeriod_begDate := :DrugRequestPeriod_begDate,
				DrugRequestPeriod_endDate := :DrugRequestPeriod_endDate,
				DrugRequestPeriod_Name := :DrugRequestPeriod_Name,
				pmUser_id := :pmUser_id);
		";
		$p = array(
			'DrugRequestPeriod_id' => $data['DrugRequestPeriod_id'],
			'DrugRequestPeriod_begDate' => $data['DrugRequestPeriod_begDate'],
			'DrugRequestPeriod_endDate' => $data['DrugRequestPeriod_endDate'],
			'DrugRequestPeriod_Name' => $data['DrugRequestPeriod_Name'],
			'pmUser_id' => $data['pmUser_id'],
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
		    $result = $r->result('array');
		    $this->DrugRequestPeriod_id = $result[0]['DrugRequestPeriod_id'];
		} else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Удаление рабочего периода
	 */
	function deleteDrugRequestPeriod($data) {
		//перед удалением записи, нужно проверить не используется ли данное значение справочника в бд
		$q = "
			select ((
                      select count(*)
                      From DrugProto
                      Where DrugRequestPeriod_id = :DrugRequestPeriod_id
                   ) +
                   (
                     select count(*)
                     From DrugProtoMnnGroup
                     Where DrugRequestPeriod_id = :DrugRequestPeriod_id
                   ) +
                   (
                     select count(*)
                     From DrugRequest
                     Where DrugRequestPeriod_id = :DrugRequestPeriod_id
                   ) +
                   (
                     select count(*)
                     From DrugRequestBuy
                     Where DrugRequestPeriod_id = :DrugRequestPeriod_id
                   ) +
                   (
                     select count(*)
                     From DrugRequestLpuGroup
                     Where DrugRequestPeriod_id = :DrugRequestPeriod_id
                   ) +
                   (
                     select count(*)
                     From DrugRequestPerson
                     Where DrugRequestPeriod_id = :DrugRequestPeriod_id
                   ) +
                   (
                     select count(*)
                     From DrugRequestProperty
                     Where DrugRequestPeriod_id = :DrugRequestPeriod_id
                   ) +
                   (
                     select count(*)
                     From DrugRequestTotalStatus
                     Where DrugRequestPeriod_id = :DrugRequestPeriod_id
                   )) as \"record_count\";
		";
		$r = $this->db->query($q, array(
			'DrugRequestPeriod_id' => $data['id']
		));
		if ( is_object($r) ) {
			$res = $r->result('array');
			if (isset($res[0]) && isset($res[0]['record_count']) && $res[0]['record_count'] > 0)
				return array(array('Error_Msg' => 'Удаление рабочего периода невозможно, так как он уже используется'));
		} else {
			return false;
		}

		//удаляем планово-отчетные периоды
		$q = "
			delete from
				DrugRequestPlanPeriod 
			where
				DrugRequestPeriod_id = :DrugRequestPeriod_id;
		";
		$r = $this->db->query($q, array(
			'DrugRequestPeriod_id' => $data['id']
		));
		
		$q = "
		select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                
			from dbo.p_DrugRequestPeriod_del(
				DrugRequestPeriod_id := :DrugRequestPeriod_id);
		";
		$r = $this->db->query($q, array(
			'DrugRequestPeriod_id' => $data['id']
		));
		if ( is_object($r) ) {
			return $r->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение максимальной конечной даты в справочнике рабочих периодов
	 */
	function getDrugRequestPeriodMaxDate() {
		$q = "select to_char(MAX(DrugRequestPeriod_endDate), 'DD.MM.YYYY') as \"max_date\" from DrugRequestPeriod ";


		$r = $this->db->query($q, array());
		if (is_object($r)) {
			$r = $r->result('array');
			if (isset($r[0])) {
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Функция возвращает идентификаторы списка медикамента (для федеральной и региональной льготы)
	 */
	function getDrugRequestProperty($DrugRequest_id, $PersonRegisterType_id = null, $DrugRequestPeriod_id = null) {
		$q = "
			with cte AS (
				select t1.PersonRegisterType_id,
					   t1.DrugRequestPeriod_id
				from v_DrugRequest t1
				where t1.DrugRequest_id =:DrugRequest_id)
			select DR.DrugRequestPropertyFed_id as \"DrugRequestPropertyFed_id\",
				   DR.DrugRequestPropertyReg_id as \"DrugRequestPropertyReg_id\"
			from v_DrugRequest DR
			where DR.DrugRequest_Version is null and
				DR.DrugRequestCategory_id =
				(
					select DrugRequestCategory_id
					from DrugRequestCategory
					where DrugRequestCategory_SysNick = 'region'
				)
				and DR.DrugRequestPeriod_id = CASE
					WHEN CAST(:DrugRequest_id as bigint) is not null and CAST(:DrugRequest_id as bigint) > 0
						THEN (
							select DrugRequestPeriod_id
							from cte
						)
						ELSE CAST(:DrugRequestPeriod_id as bigint)
					END
				and DR.PersonRegisterType_id = CASE
					WHEN CAST(:DrugRequest_id as bigint) is not null and CAST(:DrugRequest_id as bigint) > 0
						THEN (
							select PersonRegisterType_id
							from cte
						)
						ELSE CAST(:PersonRegisterType_id as bigint)
					END
		";
		$r = $this->db->query($q, array(
			'DrugRequest_id' => $DrugRequest_id,
			'PersonRegisterType_id' => $PersonRegisterType_id,
			'DrugRequestPeriod_id' => $DrugRequestPeriod_id
		));
		if (is_object($r)) {
			$r = $r->result('array');
			if (isset($r[0])) {
				return $r[0];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Автоматическое обьединение строк заявки с одинаковыми свойствами.
	 */
	function autoMergeDrugRequestRow(&$data) {
		$q = "
			  select DrugRequestRow_id as \"DrugRequestRow_id\",
					 DrugRequestRow_Kolvo as \"DrugRequestRow_Kolvo\"
			  from v_DrugRequestRow
			  where DrugRequest_id =:DrugRequest_id and
					DrugRequestType_id =:DrugRequestType_id and
					COALESCE(Person_id, 0) = COALESCE(:Person_id, 0) and
					DrugComplexMnn_id =:DrugComplexMnn_id
			  limit 1
		";
		$r = $this->db->query($q, array(
			'DrugRequest_id' => $data['DrugRequest_id'],
			'DrugRequestType_id' => $data['DrugRequestType_id'],
			'Person_id' => $data['Person_id'],
			'DrugComplexMnn_id' => $data['DrugComplexMnn_id']
		));
		if (is_object($r)) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$data['DrugRequestRow_id'] = $r[0]['DrugRequestRow_id'];
				$data['DrugRequestRow_Kolvo'] += $r[0]['DrugRequestRow_Kolvo'];
			}
		}
		return true;
	}

	/**
	 * Подсчет количества участков у врача соответствующего заявке
	 */
	function getLpuRegionCountByDrugRequestId($filter) {
		$q = "
			  select count(p.LpuRegion_id) as \"cnt\"
			  from (
					 select distinct msr.LpuRegion_id
					 from v_DrugRequest dr
						  left join DrugRequestPeriod drp on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
						  left join v_MedStaffRegion msr on msr.MedPersonal_id = dr.MedPersonal_id
						  left join v_LpuRegion lr on lr.LpuRegion_id = msr.LpuRegion_id
					 where DrugRequest_id = :DrugRequest_id and
						   lr.LpuRegion_begDate <= drp.DrugRequestPeriod_begDate and
						   (lr.LpuRegion_endDate is null or
						   lr.LpuRegion_endDate >= drp.DrugRequestPeriod_endDate)
				   ) p
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Создание копии заявки
	 */
	function createDrugRequestCopy($data) {
		//отменяем транзакции внутри промежуточных функций
		$data['no_trans'] = true;

		//старт транзакции
		$this->db->trans_begin();

		//создаем список льготников
		$result = $this->createDrugRequestPersonList($data);
		if (!$result || !empty($result[0]['Error_Msg'])) {
			$this->db->trans_rollback();
			return $result;
		}

		//копируем медикаменты
		$result = $this->createDrugRequestDrugCopy($data);
		if (!$result || !empty($result[0]['Error_Msg'])) {
			$this->db->trans_rollback();
			return $result;
		}

		//коммит транзакции
		$this->db->trans_commit();

		return array(array('Error_Msg' => null));
	}

	/**
	 * Создание списка пациентов для заявки
	 */
	function createDrugRequestPersonList($data) {
		//получение данных заявки
		$query= "
 			  select MedPersonal_id as \"MedPersonal_id\",
					 Lpu_id as \"Lpu_id\",
					 DrugRequestPeriod_id as \"DrugRequestPeriod_id\"
			  from v_DrugRequest
			  where DrugRequest_id = :DrugRequest_id
		";
		$request_data = $this->getFirstRowFromQuery($query, array(
			'DrugRequest_id' => $data['DrugRequest_id']
		));
		if (count($request_data) == 0) {
			return array(array('Error_Msg' => 'Не удалось получить данные заявки'));
		}

		//получение списка необходимых льготников, спика льготников в строках заявки, списка льготников в заявке, а также получение информации о необходимых действиях над этими списками
		$query = "
			WITH cte AS (  
              SELECT (
                       select DrugRequestPeriod_begDate
                       from v_DrugRequestPeriod
                       where DrugRequestPeriod_id = :DrugRequestPeriod_id
                       limit 1
                     ) as DrugRequestPeriod_begDate,
                     (
                       select DrugRequestPeriod_endDate
                       from v_DrugRequestPeriod
                       where DrugRequestPeriod_id = :DrugRequestPeriod_id
                       limit 1
                     ) as DrugRequestPeriod_endDate),
      request_person as (
                      select (
                                                 select string_agg(cast (DrugRequestRow_id as varchar),  ',')
                                                 from DrugRequestRow
                                                 where DrugRequest_id = :DrugRequest_id and
                                                       Person_id = pp.Person_id 
                                                       
                             ) as DrugRequestRow_List,
                             pp.Person_id
                      from (
                             select distinct Person_id
                             from DrugRequestRow
                             where DrugRequest_id = :DrugRequest_id
                           ) pp),
      list_person as (
          select DrugRequestPerson_id,
                 Person_id
          from DrugRequestPerson
          where DrugRequestPeriod_id = :DrugRequestPeriod_id and
                Lpu_id = :Lpu_id and
                MedPersonal_id = :MedPersonal_id
                )
      select coalesce(p.Person_id, rp.Person_id, lp.Person_id) as \"Person_id\",
             rp.DrugRequestRow_List as \"DrugRequestRow_List\",
             lp.DrugRequestPerson_id as \"DrugRequestPerson_id\",
             (case
                when p.Person_id is null and rp.Person_id is not null then 'delete'
                else null
              end) as \"req_action\",
             (case
                when p.Person_id is null and lp.Person_id is not null then 'delete'
                when p.Person_id is not null and lp.Person_id is null then 'add'
                else null
              end) as \"list_action\"
      from (
             select distinct priv.Person_id
             from PersonPrivilege priv
                  left join PrivilegeType pt on pt.PrivilegeType_id = priv.PrivilegeType_id
                  left join v_PersonState ps on ps.Person_id = priv.Person_id
                  LEFT JOIN LATERAL
                  (
                    select p_ref.PersonRefuse_id
                    from v_PersonRefuse p_ref
                    where p_ref.Person_id = priv.Person_id and
                          p_ref.PersonRefuse_IsRefuse = 2 and
                          p_ref.PersonRefuse_Year = date_part('YEAR', (SELECT DrugRequestPeriod_begDate FROM cte))
                    limit 1
                  ) refuse ON true
                  LEFT JOIN LATERAL
                  (
                    select count(EvnRecept_id) as cnt
                    from v_EvnRecept er
                    where er.Person_id = priv.Person_id and
                          datediff('day', er.EvnRecept_setDate, dbo.tzGetDate()) <= 93 and
                          er.ReceptFinance_id = pt.ReceptFinance_id
                  ) recept ON true
             where ps.Person_deadDT is null and
                   refuse.PersonRefuse_id is null and
                   (PersonPrivilege_endDate is null or
                   PersonPrivilege_endDate >= (SELECT DrugRequestPeriod_begDate FROM cte)) and
                   (PersonPrivilege_begDate is null or
                   PersonPrivilege_begDate <= (SELECT DrugRequestPeriod_begDate FROM cte)) and
                   (:SourceDrugRequest_id is not null or
                   priv.Person_id in (
                                       select Person_id
                                       from v_PersonCard
                                       where LpuRegion_id in (
                                                               select msr.LpuRegion_id
                                                               from MedStaffRegion msr
                                                                    left join v_LpuRegion lr on lr.LpuRegion_id = msr.LpuRegion_id
                                                               where lr.Lpu_id = :Lpu_id and
                                                                     msr.MedPersonal_id = :MedPersonal_id and
                                                                     lr.LpuRegion_begDate <= (SELECT DrugRequestPeriod_begDate FROM cte) and
                                                                     (lr.LpuRegion_endDate is null or
                                                                     lr.LpuRegion_endDate >= (SELECT DrugRequestPeriod_endDate FROM cte))
                                             )
                   )) and
                   recept.cnt > 0 and
                   (:SourceDrugRequest_id is null or
                   priv.Person_id in (
                                       select drp1.Person_id
                                       from v_DrugRequest dr1
                                            left join v_DrugRequestPerson drp1 on drp1.DrugRequestPeriod_id = dr1.DrugRequestPeriod_id and drp1.Lpu_id = dr1.Lpu_id and drp1.MedPersonal_id = dr1.MedPersonal_id
                                       where dr1.DrugRequest_id = :SourceDrugRequest_id
                   ))
           ) p
           full outer join request_person rp on rp.Person_id = p.Person_id
           left outer join list_person lp on lp.Person_id = p.Person_id or lp.Person_id = rp.Person_id
      where p.Person_id is not null or
            rp.Person_id is not null or
            lp.Person_id is not null
		";
		$result = $this->db->query($query, array(
			'DrugRequest_id' => $data['DrugRequest_id'],
			'MedPersonal_id' => $request_data['MedPersonal_id'],
			'Lpu_id' => $request_data['Lpu_id'],
			'DrugRequestPeriod_id' => $request_data['DrugRequestPeriod_id'],
			'SourceDrugRequest_id' => isset($data['SourceDrugRequest_id']) && $data['SourceDrugRequest_id'] > 0 ? $data['SourceDrugRequest_id'] : null
		));

		if ( is_object($result) ) {
			$person_arr = $result->result('array');

			//старт транзакции
			if (!isset($data['no_trans'])) {
				$this->db->trans_begin();
			}
			foreach($person_arr as $person) {
				//обработка списка пациентов
				if ($person['list_action'] == 'add') {
					$query = "
						select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
						from dbo.p_DrugRequestPerson_ins(
							Server_id := :Server_id,
							DrugRequestPerson_id := null,
							DrugRequestPeriod_id := :DrugRequestPeriod_id,
							Person_id := :Person_id,
							Lpu_id := :Lpu_id,
							MedPersonal_id := :MedPersonal_id,
							pmUser_id := :pmUser_id);
					";
					$result = $this->getFirstRowFromQuery($query, array(
						'Server_id' => $data['Server_id'],
						'DrugRequestPeriod_id' => $request_data['DrugRequestPeriod_id'],
						'Person_id' => $person['Person_id'],
						'Lpu_id' => $request_data['Lpu_id'],
						'MedPersonal_id' => $request_data['MedPersonal_id'],
						'pmUser_id' => $data['pmUser_id'],
					));
					if (!empty($result['Error_Msg'])) {
						if (!isset($data['no_trans'])) {
							$this->db->trans_rollback();
						}
						return array($result);
					}
				}

				if ($person['list_action'] == 'delete' && $person['DrugRequestPerson_id'] > 0) {
					$query = "
						select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
						from dbo.p_DrugRequestPerson_del(
							DrugRequestPerson_id := :DrugRequestPerson_id);
					";
					$result = $this->getFirstRowFromQuery($query, array(
						'DrugRequestPerson_id' => $person['DrugRequestPerson_id']
					));
					if (!empty($result['Error_Msg'])) {
						if (!isset($data['no_trans'])) {
							$this->db->trans_rollback();
						}
						return array($result);
					}
				}

				//удаление лишних строк заявки
				if ($person['req_action'] == 'delete') {
					$row_arr = explode(',', $person['DrugRequestRow_List']);
					foreach($row_arr as $row_id) {
						$query = "
							select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
							from dbo.p_DrugRequestRow_del(
								DrugRequestRow_id := :DrugRequestRow_id);
						";
						$result = $this->getFirstRowFromQuery($query, array(
							'DrugRequestRow_id' => $row_id
						));
						if (!empty($result['Error_Msg'])) {
							if (!isset($data['no_trans'])) {
								$this->db->trans_rollback();
							}
							return array($result);
						}
					}
				}
			}

			//коммит транзакции
			if (!isset($data['no_trans'])) {
				$this->db->trans_commit();
			}
		}
		return array(array('Error_Msg' => null));
	}

	/**
	 * Копирование списка медикаментов из одной заявки в другую
	 */
	function createDrugRequestDrugCopy($data) {
		$row_arr = array();
		$query = "
			WITH cte AS (
            select dr.DrugRequestPeriod_id as DrugRequestPeriod_id,
                   dr.Lpu_id as Lpu_id,
                   dr.MedPersonal_id as MedPersonal_id,
                   drp.DrugRequestPeriod_begDate as DrugRequestPeriod_begDate
            from v_DrugRequest dr
                 left join v_DrugRequestPeriod drp on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
            where dr.DrugRequest_id = :DrugRequest_id
            ),
            person_list as (
                select Person_id
                from v_DrugRequestPerson
                where DrugRequestPeriod_id = (SELECT DrugRequestPeriod_id FROM cte) and
                      Lpu_id = (SELECT Lpu_id FROM cte) and
                      MedPersonal_id = (SELECT MedPersonal_id FROM cte))
                      
            select drr.DrugRequestRow_id as \"DrugRequestRow_id\",
                   price.Price as \"Price\",
                   'copy' as \"action\"
            from v_DrugRequestRow drr
                 left join v_DrugRequest dr on dr.DrugRequest_id = drr.DrugRequest_id
                 left join v_DrugRequestRow current_drr on current_drr.DrugRequest_id = :DrugRequest_id 
                 and COALESCE(current_drr.Person_id, 0) = COALESCE(drr.Person_id, 0) and (current_drr.DrugProtoMnn_id = drr.DrugProtoMnn_id or current_drr.Drug_id = drr.Drug_id)
                 left join v_DrugProtoMnn dpm on dpm.DrugProtoMnn_id = drr.DrugProtoMnn_id
                 left join v_YesNo IsCommon on isCommon.YesNo_id = dpm.DrugProtoMnn_IsCommon
                 LEFT JOIN LATERAL
                 (
                   select max(DrugState_Price) as Price
                   from v_DrugState ds
                        inner join v_DrugProto dp on ds.DrugProto_id = dp.DrugProto_id
                   where (Drug_id = drr.Drug_id or
                         DrugProtoMnn_id = drr.DrugProtoMnn_id) and
                         dp.ReceptFinance_id = drr.DrugRequestType_id and
                         dp.DrugRequestPeriod_id = (SELECT DrugRequestPeriod_id FROM cte)
                 ) price ON true
                 LEFT JOIN LATERAL
                 (
                   select count(EvnRecept_id) as cnt
                   from v_EvnRecept er
                   where er.Person_id = drr.Person_id and
                         datediff('day', er.EvnRecept_setDate, dbo.tzGetDate()) <= 93 and
                         er.ReceptFinance_id = drr.DrugRequestType_id
                 ) recept ON true
            where current_drr.DrugRequestRow_id is null and
                  price.Price is not null and
                  (drr.Person_id is null or
                  (drr.Person_id in (
                                      select Person_id
                                      from person_list
                  ) and
                  recept.cnt > 0)) and
                  (IsCommon.YesNo_Code = 1 or
                  exists (
                           select 1
                           from DrugRequestLpuGroup drlg
                           where drlg.DrugProtoMnn_id = drr.DrugProtoMnn_id and
                                 drlg.Lpu_id = (SELECT Lpu_id FROM cte) and
                                 (COALESCE(drlg.medPersonal_id, (SELECT MedPersonal_id FROM cte)) = (SELECT MedPersonal_id FROM cte)) and
                                 drlg.DrugRequestPeriod_id = (SELECT DrugRequestPeriod_id FROM cte)
                  )) and
                  drr.DrugRequest_id = :SourceDrugRequest_id
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$row_arr = $result->result('array');
		} else {
			return array(array('Error_Msg' => 'При получении списка строк заявки произошла ошибка.'));
		}

		//старт транзакции
		if (!isset($data['no_trans'])) {
			$this->db->trans_begin();
		}

		foreach($row_arr as $row) {
			$query = "";
			if ($row['action'] == 'delete') {
				$query = "
					select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
					from dbo.p_DrugRequestRow_del(
						DrugRequestRow_id := :DrugRequestRow_id);
				";
			}
			if ($row['action'] == 'copy') {
				$query = "
					WITH cte AS (
					select
						DrugRequestType_id as DrugRequestType_id,
						Person_id as Person_id,
						DrugProtoMnn_id as DrugProtoMnn_id,
						DrugRequestRow_Kolvo as DrugRequestRow_Kolvo,
						COALESCE(DrugRequestRow_Kolvo*:Price,0) as DrugRequestRow_Summa,
						Drug_id as Drug_id,
						DrugRequestRow_KolvoUe as DrugRequestRow_KolvoUe,
						DrugRequestRow_RashUe as DrugRequestRow_RashUe,
						ACTMATTERS_ID as ACTMATTERS_ID,
						DrugRequestRow_DoseOnce as DrugRequestRow_DoseOnce,
						Okei_oid as Okei_oid,
						DrugRequestRow_DoseDay as DrugRequestRow_DoseDay,
						Okei_did as Okei_did,
						DrugRequestRow_DoseCource as DrugRequestRow_DoseCource,
						Okei_cid as Okei_cid,
						DrugComplexMnn_id as DrugComplexMnn_id,
						TRADENAMES_id as TRADENAMES_id,
						DrugFinance_id as DrugFinance_id
					from
						v_DrugRequestRow 
					where
						DrugRequestRow_id = :DrugRequestRow_id
                        )
					select DrugRequestRow_id as \"DrugRequestRow_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
					from dbo.p_DrugRequestRow_ins(
						DrugRequestRow_id := :DrugRequestRow_id,
						DrugRequest_id := :DrugRequest_id,
						DrugRequestType_id := (SELECT DrugRequestType_id FROM cte),
						Person_id := (SELECT Person_id FROM cte),
						DrugProtoMnn_id := (SELECT DrugProtoMnn_id FROM cte),
						DrugRequestRow_Kolvo := (SELECT DrugRequestRow_Kolvo FROM cte),
						DrugRequestRow_Summa := (SELECT DrugRequestRow_Summa FROM cte),
						Drug_id := (SELECT Drug_id FROM cte),
						DrugRequestRow_KolvoUe := (SELECT DrugRequestRow_KolvoUe FROM cte),
						DrugRequestRow_RashUe := (SELECT DrugRequestRow_RashUe FROM cte),
						ACTMATTERS_ID := (SELECT ACTMATTERS_ID FROM cte),
						DrugRequestRow_DoseOnce := (SELECT DrugRequestRow_DoseOnce FROM cte),
						Okei_oid := (SELECT Okei_oid FROM cte),
						DrugRequestRow_DoseDay := (SELECT DrugRequestRow_DoseDay FROM cte),
						Okei_did := Okei_did(SELECT Person_id FROM cte),
						DrugRequestRow_DoseCource := (SELECT DrugRequestRow_DoseCource FROM cte),
						Okei_cid := (SELECT Okei_cid FROM cte),
						DrugComplexMnn_id := (SELECT DrugComplexMnn_id FROM cte),
						TRADENAMES_id := (SELECT TRADENAMES_id FROM cte),
						DrugFinance_id := (SELECT DrugFinance_id FROM cte),
						DrugRequestRow_KolDrugBuy := (SELECT DrugRequestRow_Kolvo FROM cte),
						DrugRequestRow_SumBuy := (SELECT DrugRequestRow_Summa FROM cte),
						pmUser_id := :pmUser_id);
				";
			}

			$result = $this->getFirstRowFromQuery($query, array(
				'DrugRequestRow_id' => $row['DrugRequestRow_id'],
				'DrugRequest_id' => $data['DrugRequest_id'],
				'Price' => $row['Price'],
				'pmUser_id' => $data['pmUser_id'],
			));
			if (!empty($result['Error_Msg'])) {
				if (!isset($data['no_trans'])) {
					$this->db->trans_rollback();
				}
				return array($result);
			}
		}

		//коммит транзакции
		if (!isset($data['no_trans'])) {
			$this->db->trans_commit();
		}

		return array(array('Error_Msg' => null));
	}

    /**
     *	Получение списка параметров хранимой процедуры
     */
    function getStoredProcedureParamsList($sp, $schema) {
        $query = "
			SELECT
                name as \"name\"
            FROM (
            SELECT
                   unnest(proargnames) as name
            FROM pg_proc p
                 LEFT OUTER JOIN pg_description ds ON ds.objoid = p.oid
                 INNER JOIN pg_namespace n ON p.pronamespace = n.oid
            WHERE p.proname = :name AND
                  n.nspname = :schema
            ) t
            WHERE t.name not in ('pmUser_id', 'Error_Code', 'Error_Message', 'isReloadCount')
		";

        $queryParams = array(
            'name' => $sp,
            'schema' => $schema
        );

        $result = $this->db->query($query, $queryParams);

        if ( !is_object($result) ) {
            return false;
        }

        $outputData = array();
        $response = $result->result('array');

        foreach ( $response as $row ) {
            $outputData[] = str_replace('@', '', $row['name']);
        }

        return $outputData;
    }

    /**
     * Сохранение произвольного обьекта (без повреждения предыдущих данных).
     */
    function saveObject($object_name, $data) {
        $schema = "dbo";

        //при необходимости выделяем схему из имени обьекта
        $name_arr = explode('.', $object_name);
        if (count($name_arr) > 1) {
            $schema = $name_arr[0];
            $object_name = $name_arr[1];
        }

        $key_field = !empty($data['key_field']) ? $data['key_field'] : "{$object_name}_id";

        if (!isset($data[$key_field])) {
            $data[$key_field] = null;
        }

        $action = $data[$key_field] > 0 ? "upd" : "ins";
        $proc_name = "p_{$object_name}_{$action}";
        $params_list = $this->getStoredProcedureParamsList($proc_name, $schema);
        $save_data = array();
        $query_part = "";

        //получаем существующие данные если апдейт
        if ($action == "upd") {
            $query = "
				select
					*
				from
					{$schema}.{$object_name} 

				where
					{$key_field} = :id;
			";
            $result = $this->getFirstRowFromQuery($query, array(
                'id' => $data[$key_field]
            ));
            if (is_array($result)) {
                foreach($result as $key => $value) {
                    if (in_array($key, $params_list)) {
                        $save_data[$key] = $value;
                    }
                }
            }
        }

        foreach($data as $key => $value) {
            if (in_array($key, $params_list)) {
                $save_data[$key] = $value;
            }
        }

        foreach($save_data as $key => $value) {
            if (in_array($key, $params_list) && $key != $key_field) {
                //перобразуем даты в строки
                if (is_object($save_data[$key]) && get_class($save_data[$key]) == 'DateTime') {
                    $save_data[$key] = $save_data[$key]->format('Y-m-d H:i:s');
                }
                $query_part .= "{$key} := :{$key}, ";
            }
        }

        $save_data['pmUser_id'] = isset($data['pmUser_id']) ? $data['pmUser_id'] : null;

        $query = "
			select {$key_field} as \"{$key_field}\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from {$schema}.{$proc_name}(
				{$key_field} := :{$key_field},
				{$query_part}
				pmUser_id := :pmUser_id);
		";

        if (isset($data['debug_query'])) {
            print getDebugSQL($query, $save_data);
        }
        $result = $this->getFirstRowFromQuery($query, $save_data);
        if ($result && is_array($result)) {
            if($result[$key_field] > 0) {
                $result['success'] = true;
            }
            return $result;
        } else {
            return array('Error_Msg' => 'При сохранении произошла ошибка');
        }
    }

    /**
     * Копирование произвольного обьекта.
     */
    function copyObject($object_name, $data) {
        $schema = "dbo";

        //при необходимости выделяем схему из имени обьекта
        $name_arr = explode('.', $object_name);
        if (count($name_arr) > 1) {
            $schema = $name_arr[0];
            $object_name = $name_arr[1];
        }

        $key_field = !empty($data['key_field']) ? $data['key_field'] : "{$object_name}_id";

        if (!isset($data[$key_field])) {
            return array('Error_Message' => 'Не указано значение ключевого поля');
        }

        $proc_name = "p_{$object_name}_ins";
        $params_list = $this->getStoredProcedureParamsList($proc_name, $schema);
        $save_data = array();
        $query_part = "";

        //получаем данные оригинала
        $query = "
			select
				*
			from
				{$schema}.{$object_name} 

			where
				{$key_field} = :id;
		";
        $result = $this->getFirstRowFromQuery($query, array(
            'id' => $data[$key_field]
        ));
        if (is_array($result)) {
            foreach($result as $key => $value) {
                if (in_array($key, $params_list)) {
                    $save_data[$key] = $value;
                }
            }
        }


        foreach($data as $key => $value) {
            if (in_array($key, $params_list)) {
                $save_data[$key] = $value;
            }
        }

        foreach($save_data as $key => $value) {
            if (in_array($key, $params_list) && $key != $key_field) {
                //перобразуем даты в строки
                if (is_object($save_data[$key]) && get_class($save_data[$key]) == 'DateTime') {
                    $save_data[$key] = $save_data[$key]->format('Y-m-d H:i:s');
                }
                $query_part .= "{$key} := :{$key}, ";
            }
        }

        $save_data['pmUser_id'] = isset($data['pmUser_id']) ? $data['pmUser_id'] : null;

        $query = "
			select {$key_field} as \"{$key_field}\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from {$schema}.{$proc_name}(
				{$key_field} := :{$key_field},
				{$query_part}
				pmUser_id := :pmUser_id);
		";

        if (isset($data['debug_query'])) {
            print getDebugSQL($query, $save_data);
        }
        $result = $this->getFirstRowFromQuery($query, $save_data);
        if ($result && is_array($result)) {
            if($result[$key_field] > 0) {
                $result['success'] = true;
            }
            return $result;
        } else {
            return array('Error_Msg' => 'При копировании произошла ошибка');
        }
    }

    /**
     * Удаление произвольного обьекта.
     */
    function deleteObject($object_name, $data) {
        $query = "
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Message\"
			from dbo.p_{$object_name}_del(
				{$object_name}_id := :{$object_name}_id);
		";

        if (!empty($data['force_delete'])) {
            $query = "
                delete from
                    {$object_name}
                where
                    {$object_name}_id = :{$object_name}_id;

                select null as \"Error_Code\", null as \"Error_Message\";
            ";
        }

        $result = $this->getFirstRowFromQuery($query, $data);
        if ($result && is_array($result)) {
            if(empty($result['Error_Message'])) {
                $result['success'] = true;
            }
            return $result;
        } else {
            return array('Error_Message' => 'При удалении произошла ошибка');
        }
    }

    /**
     * Функция переноса (полного или частичного) строки заявки
     */
    function moveDrugRequestRow($data) {
        $result = array();
        $error = array();

        //получение данных строки
        $query = "
            select drr.DrugRequestRow_Kolvo as \"DrugRequestRow_Kolvo\",
				   drr.Drug_id as \"Drug_id\",
				   drr.DrugProtoMnn_id as \"DrugProtoMnn_id\",
				   drr.DrugRequestType_id as \"DrugRequestType_id\",
				   dr.DrugRequestPeriod_id as \"DrugRequestPeriod_id\",
				   dr.Lpu_id as \"Lpu_id\",
				   dr.MedPersonal_id as \"MedPersonal_id\"
			from v_DrugRequestRow drr
				 left join v_DrugRequest dr on dr.DrugRequest_id = drr.DrugRequest_id
			where drr.DrugRequestRow_id =:DrugRequestRow_id
        ";
        $sender_row_data = $this->getFirstRowFromQuery($query, array(
            'DrugRequestRow_id' => $data['DrugRequestRow_id']
        ));


        //получение цены
        $price = 0;
        if (!empty($sender_row_data['Drug_id'])) {
            // Здесь берем цену торгового наименования
            $result = $this->getDrug(array(
                'Drug_id' => $sender_row_data['Drug_id'],
                'DrugRequestType_id' => $sender_row_data['DrugRequestType_id']
            ));
            if ($result != false && count($result) > 0) {
                $price = $result[0]['DrugState_Price'];
            }
        } else if (!empty($sender_row_data['DrugProtoMnn_id'])) {
            // Здесь надо брать цену на данный медикамент МНН
            $result = $this->getDrugProtoMnn(array(
                'DrugProtoMnn_id' => $sender_row_data['DrugProtoMnn_id'],
                'DrugRequestType_id' => $sender_row_data['DrugRequestType_id'],
                'DrugRequestPeriod_id' => $sender_row_data['DrugRequestPeriod_id'],
                'session' => array('lpu_id' => $sender_row_data['Lpu_id']),
                'MedPersonal_id' => $sender_row_data['MedPersonal_id']
            ));
            if ($result != false && count($result) > 0) {
                $price = $result[0]['DrugProtoMnn_Price'];
            }
        }


        //поиск подходящей строки для слияния
        $query = "
            select drr2.DrugRequestRow_id as \"DrugRequestRow_id\",
				   drr2.DrugRequestRow_Kolvo as \"DrugRequestRow_Kolvo\"
			from v_DrugRequestRow drr
				 left join v_DrugRequestRow drr2 on drr2.DrugRequest_id = drr.DrugRequest_id and COALESCE(drr2.Person_id, 0) = COALESCE(:Person_id, 0) and COALESCE(drr2.DrugRequestType_id, 0) = COALESCE(drr.DrugRequestType_id, 0) and COALESCE(drr2.DrugProtoMnn_id, 0) = COALESCE(drr.DrugProtoMnn_id, 0) and COALESCE(drr2.Drug_id, 0) = COALESCE(drr.Drug_id, 0) and COALESCE(drr2.DrugComplexMnn_id, 0) = COALESCE(drr.DrugComplexMnn_id, 0)
			where drr.DrugRequestRow_id =:DrugRequestRow_id
			limit 1
        ";
        $recepient_row_data = $this->getFirstRowFromQuery($query, array(
            'DrugRequestRow_id' => $data['DrugRequestRow_id'],
            'Person_id' => $data['Person_id']
        ));

        $sender_id = $data['DrugRequestRow_id'];
        $recepient_id = !empty($recepient_row_data['DrugRequestRow_id']) ? $recepient_row_data['DrugRequestRow_id'] : null;

        //если нет строки для слияния и переносится все количество, то переносим строку целиком
        $full_kolvo_move = (empty($data['DrugRequestRow_Kolvo']) || $data['DrugRequestRow_Kolvo'] == $sender_row_data['DrugRequestRow_Kolvo']);
        $full_row_move = (empty($recepient_id) && $full_kolvo_move);

        if ($full_row_move) {
            $recepient_id = $sender_id;
        }

        //проверка перемещаемого количества
        if ($data['DrugRequestRow_Kolvo'] > $sender_row_data['DrugRequestRow_Kolvo']) {
            $error[] = "Перемещаемое количество медикамента превышает исходное.";
        }

        if (!isset($data['no_trans'])) {
            $this->beginTransaction();
        }

        //сохранение данных в итоговой строке
        if (count($error) <= 0) {
            if (!empty($recepient_id)) {
                $save_data = array(
                    'DrugRequestRow_id' => $recepient_id,
                    'pmUser_id' => $data['pmUser_id']
                );
                if ($full_row_move) {
                    $save_data['Person_id'] = $data['Person_id'];
                } else {
                    $save_data['DrugRequestRow_Kolvo'] = $recepient_row_data['DrugRequestRow_Kolvo'] + ($full_kolvo_move ? $sender_row_data['DrugRequestRow_Kolvo'] : $data['DrugRequestRow_Kolvo']);
                    $save_data['DrugRequestRow_Summa'] = Round($price * $save_data['DrugRequestRow_Kolvo'], 2);
                }
                $response = $this->saveObject('DrugRequestRow', $save_data);
                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                }
            } else {
                $kolvo = $full_kolvo_move ? $sender_row_data['DrugRequestRow_Kolvo'] : $data['DrugRequestRow_Kolvo'];
                $summa = Round($price * $kolvo, 2);
                $response = $this->copyObject('DrugRequestRow', array(
                    'DrugRequestRow_id' => $sender_id,
                    'Person_id' => $data['Person_id'],
                    'DrugRequestRow_Kolvo' => $kolvo,
                    'DrugRequestRow_Summa' => $summa,
                    'pmUser_id' => $data['pmUser_id']
                ));
                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                }
            }
        }

        //удаление или редактирование исходной строки
        if (count($error) <= 0 && !$full_row_move) {
            if ($full_kolvo_move) { //если переносится все количество удаляем исходную строку
                $response = $this->deleteObject('DrugRequestRow', array(
                    'DrugRequestRow_id' => $sender_id,
                    'pmUser_id' => $data['pmUser_id']
                ));
                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                }
            } else { //иначе редактируем
                $kolvo = $sender_row_data['DrugRequestRow_Kolvo'] - $data['DrugRequestRow_Kolvo'];
                $summa = Round($price * $kolvo, 2);
                $response = $this->saveObject('DrugRequestRow', array(
                    'DrugRequestRow_id' => $sender_id,
                    'DrugRequestRow_Kolvo' => $kolvo,
                    'DrugRequestRow_Summa' => $summa,
                    'pmUser_id' => $data['pmUser_id']
                ));
                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                }
            }
        }

        //$error[] = 'Отладка.';

        if (count($error) <= 0) {
            if (!isset($data['no_trans'])) {
                $this->commitTransaction();
            }
            $result['success'] = true;
        } else {
            if (!isset($data['no_trans'])) {
                $this->rollbackTransaction();
            }
            $result['Error_Msg'] = $error[0];
        }

        return $result;
    }

    /**
     * Исключение пациента из заявки с переносом медикаментов в резерв
     */
    function excludeDrugRequestPerson($data) {
        $result = array();
        $error = array();
        $row_array = array();

        //получение данных записи пациента
        $query = "
            select drp.Person_id as \"Person_id\"
			from v_DrugRequestPerson drp
			where drp.DrugRequestPerson_id =:DrugRequestPerson_id
        ";
        $drp_data = $this->getFirstRowFromQuery($query, array(
            'DrugRequestPerson_id' => $data['DrugRequestPerson_id']
        ));
        if (!is_array($drp_data) || count($drp_data) < 1) {
            $error[] = "Не удалось получить данные о пациенте.";
        }

        //получение строк заявки связанных с данным пациентом
        if (count($error) <= 0) {
            $query = "
                select drr.DrugRequestRow_id as \"DrugRequestRow_id\"
				from v_DrugRequestRow drr
				where drr.DrugRequest_id =:DrugRequest_id and
					  drr.Person_id =:Person_id
            ";
            $response = $this->db->query($query, array(
                'DrugRequest_id' => $data['DrugRequest_id'],
                'Person_id' => $drp_data['Person_id']
            ));
            if ( is_object($response) ) {
                $row_array = $response->result('array');
            } else {
                $error[] = "Не удалось получить данные о медикаментах.";
            }
        }

        $this->beginTransaction();

        //перенос медикаментов в резерв
        if (count($error) <= 0) {
            foreach($row_array as $row_data) {
                $response = $this->saveObject('DrugRequestRow', array(
                    'DrugRequestRow_id' => $row_data['DrugRequestRow_id'],
                    'Person_id' => null,
                    'pmUser_id' => $data['pmUser_id'],
                    'no_trans' => true
                ));
                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                }
            }
        }

        //удаление пациента из списка
        if (count($error) <= 0) {
            $response = $this->deleteDrugRequestPerson(array(
                'object' => 'DrugRequestPerson',
                'id' => $data['DrugRequestPerson_id']
            ));
            if ($response) {
                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                }
            } else {
                $error[] = 'При удалении произошла ошибка.';
            }
        }

        if (count($error) <= 0) {
            $this->commitTransaction();
            $result['success'] = true;
        } else {
            $this->rollbackTransaction();
            $result['Error_Msg'] = $error[0];
        }

        return $result;
    }

    /**
     * Загрузка списка строк из резерва заявки для комбо
     */
    function loadReservedDrugRequestRowCombo($data) {
        $filters = array();
        $params = array();
        $where = "";

        $filters[] = "drr.Person_id is null";
        $filters[] = "(drr.Drug_id is not null or drr.DrugProtoMnn_id is not null)";

        if (!empty($data['DrugRequest_id'])) {
            $filters[] = "drr.DrugRequest_id = :DrugRequest_id";
            $params['DrugRequest_id'] = $data['DrugRequest_id'];
        }

        if (!empty($data['DrugRequestType_id'])) {
            $filters[] = "drr.DrugRequestType_id = :DrugRequestType_id";
            $params['DrugRequestType_id'] = $data['DrugRequestType_id'];
        }

        if (count($filters) > 0) {
            $where = "where ".join(" and ", $filters);
        }

        $query = "
        	select drr.DrugRequestRow_id as \"DrugRequestRow_id\",
				   rtrim(coalesce(dpm.DrugProtoMnn_Name, d.Drug_Name, '')) as \"DrugRequestRow_Name\",
				   drr.DrugRequestRow_Kolvo as \"DrugRequestRow_Kolvo\",
				   drt.DrugRequestType_Name as \"DrugRequestType_Name\"
			from v_DrugRequestRow drr
				 left join v_DrugProtoMnn dpm on dpm.DrugProtoMnn_id = drr.DrugProtoMnn_id
				 left join v_Drug d on d.Drug_id = drr.Drug_id
				 left join v_DrugRequestType drt on drt.DrugRequestType_id = drr.DrugRequestType_id
			{$where}
			order by \"DrugRequestRow_Name\"
			limit 500
    	";

        $result = $this->db->query($query, $params);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }
}
?>
