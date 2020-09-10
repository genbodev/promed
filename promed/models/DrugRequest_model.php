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

class DrugRequest_model extends swModel
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
				select
				(
					select top 1
						DrugRequestQuota_Person
					from
						DrugRequestQuota with (nolock)
					where
						PersonRegisterType_id = :PersonRegisterType_id and DrugRequestPeriod_id = :DrugRequestPeriod_id and DrugFinance_id = (select top 1 DrugFinance_id from DrugFinance with (nolock) where DrugFinance_SysNick = 'fed')
				) as Fed_Normativ,
				(
					select top 1
						DrugRequestQuota_Person
					from
						DrugRequestQuota with (nolock)
					where
						PersonRegisterType_id = :PersonRegisterType_id and DrugRequestPeriod_id = :DrugRequestPeriod_id and DrugFinance_id = (select top 1 DrugFinance_id from DrugFinance with (nolock) where DrugFinance_SysNick = 'reg')
				) as Reg_Normativ
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
			declare @getdate datetime = cast(dbo.tzGetDate() as date);

			select
				SUM(case when DRR.DrugRequestType_id = 1 then DRR.DrugRequestRow_Summa else null end) - Fed.FedLgot * :Fed_Normativ * 3 * :Fed_Koef as FedLgotExceed_Sum,
				SUM(case when DRR.DrugRequestType_id = 2 then DRR.DrugRequestRow_Summa else null end) - Reg.RegLgot * :Reg_Normativ * 3 * :Reg_Koef as RegLgotExceed_Sum
			from
				DrugRequestRow DRR WITH (NOLOCK)
				inner join DrugRequest DR with (nolock) on DR.DrugRequest_id = DRR.DrugRequest_id
					and DR.Lpu_id = :Lpu_id
				inner join DrugRequestPeriod DRP with (nolock) on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
					and DRP.DrugRequestPeriod_id = :DrugRequestPeriod_id
				outer apply (
					select
						COUNT(DISTINCT PC.Person_id) as FedLgot
					from v_PersonCard_all PC WITH (NOLOCK)
						inner join PersonPrivilege PP with (nolock) on PP.Person_id = PC.Person_id
						inner join PrivilegeType PT with (nolock) on PT.PrivilegeType_id = PP.PrivilegeType_id
							and PT.ReceptFinance_id = 1 and isnumeric(PT.PrivilegeType_Code) = 1
						left join v_PersonRefuse PR with (nolock) on PR.Person_id = PP.Person_id
							and PR.PersonRefuse_Year = YEAR(DRP.DrugRequestPeriod_begDate)
							and PR.PersonRefuse_IsRefuse = 2 
					where (1 = 1)
						and PC.Lpu_id = :Lpu_id
						and PC.PersonCard_begDate <= DRP.DrugRequestPeriod_begDate
						and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > DRP.DrugRequestPeriod_begDate)
						and PP.PersonPrivilege_begDate <= @getdate
						and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate >= @getdate)
						and ISNULL(PR.PersonRefuse_IsRefuse, 1) = 1
				) Fed
				outer apply (
					select COUNT(DISTINCT PC.Person_id) as RegLgot
					from v_PersonCard_all PC WITH (NOLOCK)
						inner join PersonPrivilege PP with (nolock) on PP.Person_id = PC.Person_id
						inner join PrivilegeType PT with (nolock) on PT.PrivilegeType_id = PP.PrivilegeType_id
							and PT.ReceptFinance_id = 2 and isnumeric(PT.PrivilegeType_Code) = 1
						outer apply (
							select top 1 1 as IsFedLgot
							from v_PersonPrivilege PP WITH (NOLOCK)
								inner join PrivilegeType PT with (nolock) on PT.PrivilegeType_id = PP.PrivilegeType_id
									and PT.ReceptFinance_id = 1 and isnumeric(PT.PrivilegeType_Code) = 1
							where PP.Person_id = PC.Person_id
								and PP.PersonPrivilege_begDate <= @getdate
								and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate >= @getdate)
						) FedLgot
					where (1 = 1)
						and PC.Lpu_id = :Lpu_id
						and PC.PersonCard_begDate <= DRP.DrugRequestPeriod_begDate
						and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > DRP.DrugRequestPeriod_begDate)
						and PP.PersonPrivilege_begDate <= @getdate
						and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate >= @getdate)
						and FedLgot.IsFedLgot is null
				) Reg
			where
				(1 = 1)
				and (DRR.DrugRequestRow_delDT is null or DRR.DrugRequestRow_delDT > @getdate)
				and DRR.DrugRequestRow_insDT <= @getdate
			group by
				Fed.FedLgot,
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
			select
				case when MP.WorkData_IsDlo is not null and	MP.WorkData_IsDlo = 2 then 1 else 0 end as is_dlo,
				MP.Person_Fin  as MedPersonal_Fin,
				ISNULL(DR_last.DrugRequest_id,0) as last_DrugRequest_id,
				ISNULL(DR_last.DrugRequestPeriod_id,0) as last_DrugRequestPeriod_id,
				ISNULL(DR_last.DrugRequestStatus_id,0) as last_DrugRequestStatus_id,

				ISNULL(DRP_next.DrugRequestPeriod_id,0) as next_DrugRequestPeriod_id,
				ISNULL(convert(varchar(10), DRP_next.DrugRequestPeriod_begDate, 104),'') +' - '+ ISNULL(convert(varchar(10), DRP_next.DrugRequestPeriod_endDate, 104),'') as next_DrugRequestPeriod,

				ISNULL(DR_next.DrugRequest_id,0) as next_DrugRequest_id,
				ISNULL(DR_next.DrugRequestStatus_id,0) as next_DrugRequestStatus_id
			from
				v_MedPersonal MP WITH (NOLOCK)
				left join DrugRequestPeriod DRP_next WITH (NOLOCK) on cast(DRP_next.DrugRequestPeriod_begDate as DATE) >= cast(dbo.tzGetDate() as DATE)
				left join DrugRequest DR_next WITH (NOLOCK) on MP.MedPersonal_id = DR_next.MedPersonal_id 
					and (DR_next.LpuSection_id = :LpuSection_id or DR_next.LpuSection_id is null)
					and DR_next.DrugRequestPeriod_id = DRP_next.DrugRequestPeriod_id
				outer apply(
					select top 1
						DrugRequest_id
						,DrugRequestPeriod_id
						,DrugRequestStatus_id
					from
						DrugRequest WITH (NOLOCK)
					where
						MedPersonal_id = :MedPersonal_id
						AND (LpuSection_id = :LpuSection_id or LpuSection_id is null)
					order by DrugRequest_insDT desc, DrugRequestPeriod_id desc
				) as DR_last
			where
				MP.MedPersonal_id = :MedPersonal_id
				AND MP.Lpu_id = :Lpu_id
		";

				/*
				ISNULL(DR_cur.DrugRequest_id,0) as last_DrugRequest_id,
				ISNULL(DR_cur.DrugRequestPeriod_id,0) as last_DrugRequestPeriod_id,
				ISNULL(DR_cur.DrugRequestStatus_id,0) as last_DrugRequestStatus_id,
				--left join DrugRequestPeriod DRP_cur WITH (NOLOCK) on cast(DRP_cur.DrugRequestPeriod_begDate as DATE) <= cast(dbo.tzGetDate() as DATE) AND cast(DRP_cur.DrugRequestPeriod_endDate as DATE) >= cast(dbo.tzGetDate() as DATE)
				--left join DrugRequest DR_cur WITH (NOLOCK) on MP.MedPersonal_id = DR_cur.MedPersonal_id and DR_cur.LpuSection_id = :LpuSection_id and DR_cur.DrugRequestPeriod_id = DRP_cur.DrugRequestPeriod_id
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
			RTrim(IsNull(PS.Person_SurName,''))+' '+RTrim(IsNull(PS.Person_FirName,''))+' '+RTrim(IsNull(PS.Person_SecName,'')) as Person_FIO,
			convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
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
			convert(varchar(10), DrugRequestPerson_insDT, 104) as DrugRequestPerson_insDT,
			convert(varchar(10), DrugRequestPerson_updDT, 104) as DrugRequestPerson_updDT,
			DrrOutCount.DrugRequestRow_Count
		from DrugRequestPerson DRP WITH (NOLOCK)
		left join DrugRequestPeriod DRPr WITH (NOLOCK) on DRPr.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id 
		left join v_PersonState PS WITH (NOLOCK) on PS.Person_id = DRP.Person_id 
		-- это для подсветки персонов синеньким, тех персонов на которые есть позиции заявок других ЛПУ
		outer apply (select count(*) as DrugRequestRow_Count
							from DrugRequestRow DRR WITH (NOLOCK)
							left join DrugRequest WITH (NOLOCK) on DrugRequest.DrugRequest_id = DRR.DrugRequest_id
							where DRR.Person_id = DRP.Person_id and DrugRequest.Lpu_id != DRP.Lpu_id 
							and DrugRequest.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
							) as DrrOutCount
		outer apply (select top 1
								pc.Person_id as PersonCard_Person_id,
								pc.Lpu_id,
								pc.LpuRegion_id,
								pc.LpuRegion_Name
							from v_PersonCard pc WITH (NOLOCK)
							where pc.Person_id = ps.Person_id and LpuAttachType_id = 1
							order by PersonCard_begDate desc
							) as pcard
		left join v_Lpu Lpu WITH (NOLOCK) on pcard.Lpu_id=Lpu.Lpu_id
		left join v_LpuRegion LpuRegion WITH (NOLOCK) on LpuRegion.LpuRegion_id = pcard.LpuRegion_id
		
		left join v_PersonRefuse PRYear WITH (NOLOCK) ON PRYear.Person_id=ps.Person_id and PRYear.PersonRefuse_IsRefuse=2 and PRYear.PersonRefuse_Year = YEAR(dbo.tzGetDate())
		left join v_PersonRefuse PRNextYear WITH (NOLOCK) on PRNextYear.Person_id=ps.Person_id and PRNextYear.PersonRefuse_IsRefuse=2 and PRNextYear.PersonRefuse_Year = (YEAR(dbo.tzGetDate())+1)
		left join v_PersonRefuse PRPeriodYear WITH (NOLOCK) on PRPeriodYear.Person_id=ps.Person_id and PRPeriodYear.PersonRefuse_IsRefuse=2 and PRPeriodYear.PersonRefuse_Year = YEAR(DRPr.DrugRequestPeriod_begDate)
		
			outer apply (
					select top 1 Person_id
					from v_personprivilege reg WITH (NOLOCK)
					where
					reg.person_id = ps.person_id
					and reg.privilegetype_id <= 249
					and reg.personprivilege_begdate <= dbo.tzGetDate()
					and (reg.personprivilege_enddate is null or reg.personprivilege_enddate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime))
				) as fedl
			outer apply (
					select top 1 Person_id
					from v_personprivilege reg WITH (NOLOCK)
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
				outer apply (
					select max(case when Lpu_id = " . $Lpu_id . " then 1 else 0 end) as OwnLpu
					from v_personprivilege reg WITH (NOLOCK)
					where
					reg.person_id = ps.person_id
					and reg.privilegetype_id > 249
					and reg.personprivilege_begdate <= dbo.tzGetDate()
					and (reg.personprivilege_enddate is null or reg.personprivilege_enddate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime))
				) as regl
				outer apply (
					select max(case when Lpu_id = " . $Lpu_id . " then 1 else 0 end) as OwnLpu
					from v_personprivilege reg WITH (NOLOCK)
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
				outer apply (
					select max(case when Lpu_id = " . $Lpu_id . " then 1 else 0 end) as OwnLpu
					from [v_PersonDisp] WITH (NOLOCK)
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
			$person = "PS.Person_SurName like :Person_SurName";
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
			DrugRequestPerson_id, 
			PS.Server_id,
			PS.PersonEvn_id,
			DRP.Person_id, 
			RTrim(PS.Person_SurName) as Person_SurName,
			RTrim(PS.Person_FirName) as Person_FirName,
			RTrim(PS.Person_SecName) as Person_SecName,
			RTrim(IsNull(PS.Person_SurName,''))+' '+RTrim(IsNull(PS.Person_FirName,''))+' '+RTrim(IsNull(PS.Person_SecName,'')) as Person_FIO,
			convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
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
			convert(varchar(10), DrugRequestPerson_insDT, 104) as DrugRequestPerson_insDT,
			convert(varchar(10), DrugRequestPerson_updDT, 104) as DrugRequestPerson_updDT,
			DrrOutCount.DrugRequestRow_Count
			-- end select
		from 
			-- from
			DrugRequestPerson DRP WITH (NOLOCK)
			
		left join DrugRequestPeriod DRPr WITH (NOLOCK) on DRPr.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id 
		left join v_PersonState PS WITH (NOLOCK) on PS.Person_id = DRP.Person_id 
		-- это для подсветки персонов синеньким, тех персонов на которые есть позиции заявок других ЛПУ
		outer apply (select count(*) as DrugRequestRow_Count
							from DrugRequestRow DRR WITH (NOLOCK)
							left join DrugRequest WITH (NOLOCK) on DrugRequest.DrugRequest_id = DRR.DrugRequest_id
							where DRR.Person_id = DRP.Person_id and DrugRequest.Lpu_id != DRP.Lpu_id 
							and DrugRequest.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
							) as DrrOutCount
		outer apply (select top 1
								pc.Person_id as PersonCard_Person_id,
								pc.Lpu_id,
								pc.LpuRegion_id,
								pc.LpuRegion_Name
							from v_PersonCard pc WITH (NOLOCK)
							where pc.Person_id = ps.Person_id and LpuAttachType_id = 1
							order by PersonCard_begDate desc
							) as pcard
		left join v_Lpu Lpu WITH (NOLOCK) on pcard.Lpu_id=Lpu.Lpu_id
		left join v_LpuRegion LpuRegion WITH (NOLOCK) on LpuRegion.LpuRegion_id = pcard.LpuRegion_id
		
		left join v_PersonRefuse PRYear WITH (NOLOCK) ON PRYear.Person_id=ps.Person_id and PRYear.PersonRefuse_IsRefuse=2 and PRYear.PersonRefuse_Year = YEAR(dbo.tzGetDate())
		left join v_PersonRefuse PRNextYear WITH (NOLOCK) on PRNextYear.Person_id=ps.Person_id and PRNextYear.PersonRefuse_IsRefuse=2 and PRNextYear.PersonRefuse_Year = (YEAR(dbo.tzGetDate())+1)
		left join v_PersonRefuse PRPeriodYear WITH (NOLOCK) on PRPeriodYear.Person_id=ps.Person_id and PRPeriodYear.PersonRefuse_IsRefuse=2 and PRPeriodYear.PersonRefuse_Year = YEAR(DRPr.DrugRequestPeriod_begDate)
		
			outer apply (
					select top 1 Person_id
					from v_personprivilege reg WITH (NOLOCK)
					left join PrivilegeType pt with (nolock) on pt.PrivilegeType_id = reg.PrivilegeType_id
					where
					reg.person_id = ps.person_id
					--and reg.privilegetype_id <= 249
					and pt.ReceptFinance_id = 1
					and reg.personprivilege_begdate <= dbo.tzGetDate()
					and (reg.personprivilege_enddate is null or reg.personprivilege_enddate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime))
				) as fedl
			outer apply (
					select top 1 Person_id
					from v_personprivilege reg WITH (NOLOCK)
					left join PrivilegeType pt with (nolock) on pt.PrivilegeType_id = reg.PrivilegeType_id
					where
					reg.person_id = ps.person_id
					--and reg.privilegetype_id <= 249
					and pt.ReceptFinance_id = 1
					-- and reg.personprivilege_begdate <= DRPr.DrugRequestPeriod_endDate
					and reg.personprivilege_begdate <= dbo.tzGetDate()
					-- на дату заявки 
					--and (reg.personprivilege_enddate is null or reg.personprivilege_enddate >= DRPr.DrugRequestPeriod_begDate)
					-- на текущую дату 
					and (reg.personprivilege_enddate is null or reg.personprivilege_enddate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime))
				) as fed2
				outer apply (
					select max(case when Lpu_id = " . $Lpu_id . " then 1 else 0 end) as OwnLpu
					from v_personprivilege reg WITH (NOLOCK)
					left join PrivilegeType pt with (nolock) on pt.PrivilegeType_id = reg.PrivilegeType_id
					where
					reg.person_id = ps.person_id
					--and reg.privilegetype_id > 249
					and pt.ReceptFinance_id = 2
					and reg.personprivilege_begdate <= dbo.tzGetDate()
					and (reg.personprivilege_enddate is null or reg.personprivilege_enddate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime))
				) as regl
				outer apply (
					select max(case when Lpu_id = " . $Lpu_id . " then 1 else 0 end) as OwnLpu
					from v_personprivilege reg WITH (NOLOCK)
					left join PrivilegeType pt with (nolock) on pt.PrivilegeType_id = reg.PrivilegeType_id
					where
					reg.person_id = ps.person_id
					--and reg.privilegetype_id > 249
					and pt.ReceptFinance_id = 2
					-- and reg.personprivilege_begdate <= DRPr.DrugRequestPeriod_endDate
					and reg.personprivilege_begdate <= dbo.tzGetDate()
					-- на дату заявки 
					--and (reg.personprivilege_enddate is null or reg.personprivilege_enddate >= DRPr.DrugRequestPeriod_begDate)
					-- на текущую дату
					and (reg.personprivilege_enddate is null or reg.personprivilege_enddate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime))
				) as reg2
				outer apply (
					select max(case when Lpu_id = " . $Lpu_id . " then 1 else 0 end) as OwnLpu
					from [v_PersonDisp] WITH (NOLOCK)
					where Person_id = ps.Person_id
					and (PersonDisp_endDate is null or PersonDisp_endDate > dbo.tzGetDate())
					and Sickness_id is not null
				) as disp
			-- end from
		where
			-- where
			{$medpersonal}
			and DRP.DrugRequestPeriod_id = {$data['DrugRequestPeriod_id']}
			and {$lpu}
			and {$person}
			and {$person_register_type}
			-- end where
		order by 
			-- order by
			ps.Person_SurName ASC, ps.Person_FirName ASC, ps.Person_SecName ASC
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
			$medpersonal_field = "(select top 1 RTrim(Person_FIO) from v_MedPersonal MP WITH (NOLOCK) where MP.MedPersonal_id = DR.MedPersonal_id and MP.Lpu_id = DR.Lpu_id) as MedPersonal_FIO";
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
			$medpersonal_field = "RTrim(Person_FIO) as MedPersonal_FIO";
			$join = "left join v_MedPersonal MP with(nolock) on MP.MedPersonal_id = DR.MedPersonal_id and MP.Lpu_id = DR.lpu_id";
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
			Select distinct
				DRR.DrugRequestRow_id,
				DRR.DrugRequest_id,
				DRR.DrugProtoMnn_id,
				DRR.DrugComplexMnn_id,
				DRR.TRADENAMES_id,
				DRR.Drug_id,
				(case when (DRR.DrugProtoMnn_id is null) and (DRR.Drug_id is not null) then 1 else 0 end) as IsDrug,
				RTrim(
					case
						when (DRR.Drug_id is not null) then Drug.Drug_Name
						when (DRR.DrugProtoMnn_id is not null) then DrugProtoMnn.DrugProtoMnn_Name
						else DCMN.DrugComplexMnnName_Name
					end
				) as DrugRequestRow_Name,
				(case when (DRR.Drug_id is not null) then Drug.Drug_Code else DrugProtoMnn.DrugProtoMnn_Code end) as DrugRequestRow_Code,
				DRR.DrugRequestRow_Kolvo,
				RTrim(
					case
						when (DRR.Drug_id is not null) then Round(DrugPrice.DrugState_Price,2)
						when (DRR.DrugProtoMnn_id is not null) then Round(DrugState.DrugState_Price,2)
						else DrugListRequest_Data.Price
					end
				) as DrugRequestRow_Price,
				/*
				DrugState.DrugState_Price as DrugRequestRow_Price,
				(DRR.DrugRequestRow_Kolvo * DrugState.DrugState_Price) as DrugRequestRow_Summa,
				*/
				DrugRequestRow_Summa,
				DRR.DrugRequestType_id,
				RTrim(DrugRequestType_Name) as DrugRequestType_Name,
				DR.MedPersonal_id,
				{$medpersonal_field},
				DR.Lpu_id,
				RTrim(Lpu_Nick) as Lpu_Nick,
				Null as DrugRequest_Zakup,
				convert(varchar(10), DrugRequestRow_insDT, 104) as DrugRequestRow_insDT,
				convert(varchar(10), DrugRequestRow_updDT, 104) as DrugRequestRow_updDT,
				convert(varchar(10), isnuLL(DRR.DrugRequestRow_delDT, DR.DrugRequest_delDT), 104) as DrugRequestRow_delDT,
				isnull(DRR.DrugRequestRow_Deleted, DR.DrugRequest_Deleted) as DrugRequestRow_Deleted,
				DRR.DrugRequestRow_DoseOnce,
				DRR.DrugRequestRow_DoseDay,
				DRR.DrugRequestRow_DoseCource,
				DRR.Okei_oid,
				OkeiO.Okei_NationSymbol as Okei_oid_NationSymbol,
				DrugListRequest_Data.isProblem,
				CDF.NAME as ClsDrugForms_Name,
				DCMD.DrugComplexMnnDose_Name,
				DCMF.DrugComplexMnnFas_Name,
				replace(replace((
					select distinct
						SUBSTRING(CA.NAME, 1, CHARINDEX(' ',CA.NAME)-1)+', '
					from
						rls.PREP_ACTMATTERS PAM with (nolock)
						left join rls.PREP_ATC PA with (nolock) on PA.PREPID = PAM.PREPID
						inner join rls.CLSATC CA with (nolock) on CA.CLSATC_ID = PA.UNIQID
					where
						PAM.MATTERID = DCMN.ActMatters_id
					for xml path('')
				)+',,', ', ,,', ''), ',,', '') as ATX_Code,
				(
					select top 1
						CN.NAME
					from
						rls.v_Drug D with(nolock)
						left join rls.v_prep P with(nolock) on P.Prep_id = D.DrugPrep_id
						left join rls.CLSNTFR CN with(nolock) on CN.CLSNTFR_ID = P.NTFRID
					where
						D.DrugComplexMnn_id = DCM.DrugComplexMnn_id
				) as NTFR_Name
			from
				DrugRequestRow DRR WITH (NOLOCK)
				inner join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id
				left join DrugProtoMnn WITH (NOLOCK) on DrugProtoMnn.DrugProtoMnn_id = DRR.DrugProtoMnn_id
				left join rls.DrugComplexMnn DCM WITH (NOLOCK) on DCM.DrugComplexMnn_id = DRR.DrugComplexMnn_id
				left join rls.DrugComplexMnnName DCMN with (nolock) on DCMN.DrugComplexMnnName_id = DCM.DrugComplexMnnName_id
				left join rls.DrugComplexMnnDose DCMD with (nolock) on DCMD.DrugComplexMnnDose_id = DCM.DrugComplexMnnDose_id
				left join rls.DrugComplexMnnFas DCMF with (nolock) on DCMF.DrugComplexMnnFas_id = DCM.DrugComplexMnnFas_id
				left join rls.CLSDRUGFORMS CDF  with(nolock) on CDF.CLSDRUGFORMS_ID = DCM.CLSDRUGFORMS_ID
				outer apply (
					select top 1
						DrugState_Price,
						DrugProto.DrugRequestPeriod_id
					from DrugState WITH (NOLOCK)
					left join DrugProto WITH (NOLOCK) on DrugState.DrugProto_id = DrugProto.DrugProto_id
					where
						DrugState.DrugProtoMnn_id = DrugProtoMnn.DrugProtoMnn_id
						and (DR.DrugRequestPeriod_id = DrugProto.DrugRequestPeriod_id or DRR.DrugProtoMnn_id is null)
					order by DrugState_Price desc
				) as DrugState
				{$join}
				left join v_Lpu Lpu WITH (NOLOCK) on Lpu.Lpu_id = DR.Lpu_id
				left join DrugRequestType WITH (NOLOCK) on DrugRequestType.DrugRequestType_id = DRR.DrugRequestType_id
				-- Торговые
				left join Drug WITH (NOLOCK) on Drug.Drug_id = DRR.Drug_id and Drug.DrugClass_id = 1
				left join v_DrugPrice DrugPrice WITH (NOLOCK) on DrugPrice.Drug_id = Drug.Drug_id and DRR.DrugRequestType_id = DrugPrice.ReceptFinance_id
					and DrugPrice.DrugProto_begDate =
					(
						select max(DrugProto_begDate)
						from v_DrugPrice DP WITH (NOLOCK)
						where Drug_id = Drug.Drug_id and DP.ReceptFinance_id = DrugPrice.ReceptFinance_id and
						DP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
					)
				outer apply (
					select top 1
						(case
							when DLRT.DrugRequest_Price > 0 then DLRT.DrugRequest_Price
							else DLR.DrugListRequest_Price
						end) as Price,
						(case
							when isProblem.YesNo_Code > 0 or isProblemTorg.YesNo_Code > 0 then 1
							else 0
						end) as isProblem
					from
						v_DrugListRequest DLR with (nolock)
						left join v_DrugListRequestTorg DLRT with (nolock) on DLRT.DrugListRequest_id = DLR.DrugListRequest_id and DLRT.TRADENAMES_id = DRR.TRADENAMES_id
						left join dbo.v_YesNo isProblem with (nolock) on isProblem.YesNo_id = DLR.DrugListRequest_IsProblem
						left join dbo.v_YesNo isProblemTorg with (nolock) on isProblemTorg.YesNo_id = DLRT.DrugListRequestTorg_IsProblem
					where
						DRR.DrugComplexMnn_id is not null and
						DRR.DrugRequestType_id is not null and
						DLR.DrugComplexMnn_id = DRR.DrugComplexMnn_id and
						(
							(DRR.DrugRequestType_id = 1 and DLR.DrugRequestProperty_id = :DrugRequestPropertyFed_id) or
							(DRR.DrugRequestType_id = 2 and DLR.DrugRequestProperty_id = :DrugRequestPropertyReg_id)
						)
				) as DrugListRequest_Data
				left join v_Okei OkeiO with (nolock) on OkeiO.Okei_id = DRR.Okei_oid
			where 
				{$person} and

				DR.DrugRequestPeriod_id = :DrugRequestPeriod_id and
				{$medpersonal} and 
				{$drugrequesttype} and
				{$drugrequest} and
				{$person_register_type} and
				{$lpu} and 
				(DR.DrugRequestPeriod_id = DrugState.DrugRequestPeriod_id or DRR.DrugProtoMnn_id is null)
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
		Select 
			DR.DrugRequest_id,
			DR.PersonRegisterType_id,
			RTrim(PersonRegisterType.PersonRegisterType_Name) as PersonRegisterType_Name,
			DR.DrugRequestPeriod_id,
			RTrim(DrugRequestPeriod.DrugRequestPeriod_Name) as DrugRequestPeriod_Name,
			DR.DrugRequestStatus_id,
			RTrim(DrugRequestStatus.DrugRequestStatus_Code) as DrugRequestStatus_Code,
			RTrim(DrugRequestStatus.DrugRequestStatus_Name) as DrugRequestStatus_Name,
			RTrim(DrugRequestKind.DrugRequestKind_Name) as DrugRequestKind_Name,
			RTrim(DR.DrugRequest_Name) as DrugRequest_Name,
			--DR.DrugRequest_Summa as DrugRequest_Summa,
			-- как только в самой заявке потребуются суммы , это надо будет разрэмить и групбай тоже 
			--Sum(case when DRR.DrugRequestType_id=1 then DRR.DrugRequestRow_Summa else 0 end) as DrugRequest_SummaFed,
			--Sum(case when DRR.DrugRequestType_id=2 then DRR.DrugRequestRow_Summa else 0 end) as DrugRequest_SummaReg,
			--IsNull(Sum(DRR.DrugRequestRow_Summa),0) as DrugRequest_Summa,
			DR.DrugRequest_YoungChildCount, 
			DR.Lpu_id,
			RTrim(Lpu.Lpu_Name) as Lpu_Name,
			RTrim(LpuUnit.LpuUnit_Name) as LpuUnit_Name,
			DR.MedPersonal_id,
			RTrim(Person_FIO) as MedPersonal_FIO,
			DR.LpuSection_id,
			RTrim(LpuSection_Name) as LpuSection_Name,
			convert(varchar(10), DrugRequest_insDT, 104) as DrugRequest_insDT,
			convert(varchar(10), DrugRequest_updDT, 104) as DrugRequest_updDT,
			convert(varchar(10), Null, 104) as DrugRequest_delDT,
			DRTS.DrugRequestTotalStatus_IsClose
			from v_DrugRequest DR WITH (NOLOCK)
			left join DrugRequestStatus WITH (NOLOCK) on DrugRequestStatus.DrugRequestStatus_id = DR.DrugRequestStatus_id
			left join DrugRequestPeriod WITH (NOLOCK) on DrugRequestPeriod.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
			left join DrugRequestKind WITH (NOLOCK) on DrugRequestKind.DrugRequestKind_id = DR.DrugRequestKind_id
			left join PersonRegisterType WITH (NOLOCK) on PersonRegisterType.PersonRegisterType_id = DR.PersonRegisterType_id
			left join v_MedPersonal MP WITH (NOLOCK)  on MP.MedPersonal_id = DR.MedPersonal_id and MP.Lpu_id = DR.lpu_id
			left join v_Lpu Lpu WITH (NOLOCK) on Lpu.Lpu_id = DR.Lpu_id
			left join v_LpuSection LpuSection WITH (NOLOCK) on LpuSection.LpuSection_id = DR.LpuSection_id
			left join v_LpuUnit LpuUnit WITH (NOLOCK) on LpuUnit.LpuUnit_id = LpuSection.LpuUnit_id
			left join DrugRequestTotalStatus DRTS WITH (NOLOCK) on DRTS.Lpu_id = DR.Lpu_id and DR.DrugRequestPeriod_id = DRTS.DrugRequestPeriod_id
			
			left join DrugRequestRow DRR WITH (NOLOCK) on DRR.DrugRequest_id = DR.DrugRequest_id
			--left join v_Lpu Lpu WITH (NOLOCK) on Lpu.Lpu_id = DR.Lpu_id
			where 
				{$drugrequest} and 
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
                select
                    year(drp.DrugRequestPeriod_begDate) as yr
                from
                    v_DrugRequestPeriod drp with (nolock)
                where
                    drp.DrugRequestPeriod_id = :DrugRequestPeriod_id;
            ";
            $year = $this->getFirstResultFromQuery($query, $data);

            //проверяем наличие данных по отказам на год рабочего пероиода, если данных нет, то берем данные по отказам за прошлый год
            if ($year > 0) {
                $query = "
                    select top 1
                        PersonRefuse_id
                    from
                        v_PersonRefuse pr with (nolock)
                    where
                        pr.PersonRefuse_Year = :PersonRefuse_Year;
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
            $year = "Year(DRP.DrugRequestPeriod_begDate)";
        }

		$sql = "
            Select
                count(distinct case when PersonRefuse_id is null then fed.Person_id else null end) as FedLgotCount,
                count(distinct case when PersonRefuse_id is null and fed.Person_id is null then reg.Person_id else null end) as RegLgotCount
            from
                PersonCardState PC WITH (NOLOCK)
                left join DrugRequestPeriod DRP WITH (NOLOCK) on DRP.DrugRequestPeriod_id = :DrugRequestPeriod_id
                --and (PP.PersonPrivilege_endDate>=DrugRequestPeriod_begDate or PP.PersonPrivilege_endDate is null)
                -- поменяли на текущую льготу, согласно (#1519)
                outer apply (
                    select top 1 reg.Person_id, PersonRefuse_id
                    from v_personprivilege reg WITH (NOLOCK)
                    left join v_PersonRefuse PR WITH (NOLOCK) on PR.Person_id = reg.Person_id and {$year} = PR.PersonRefuse_Year and PR.PersonRefuse_IsRefuse=2
                    left join PrivilegeType pt with (nolock) on pt.PrivilegeType_id = reg.PrivilegeType_id
                    where
                    reg.person_id = PC.person_id
                    --and reg.privilegetype_id <= 249
                    and pt.ReceptFinance_id = 1
                    and reg.personprivilege_begdate <= dbo.tzGetDate()
                    and (reg.personprivilege_enddate is null or reg.personprivilege_enddate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime))
                ) as fed
                outer apply (
                    select top 1 Person_id
                    from v_personprivilege reg WITH (NOLOCK)
                    left join PrivilegeType pt with (nolock) on pt.PrivilegeType_id = reg.PrivilegeType_id
                    where
                    reg.person_id = PC.person_id
                    --and reg.privilegetype_id > 249
                    and pt.ReceptFinance_id = 2
                    and reg.personprivilege_begdate <= dbo.tzGetDate()
                    and (reg.personprivilege_enddate is null or reg.personprivilege_enddate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime))
                ) as reg
            where
                PC.Lpu_id = :Lpu_id
                and PC.PersonCardState_begDate <= DRP.DrugRequestPeriod_endDate
                and (PC.PersonCardState_endDate is null or PC.PersonCardState_endDate > DRP.DrugRequestPeriod_begDate)
                and PC.LpuAttachType_id = 1
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
            select top 1
                DRTS.DrugRequestTotalStatus_id,
                IsNull(DRTS.DrugRequestTotalStatus_IsClose,1) as DrugRequestTotalStatus_IsClose
            from
                DrugRequestTotalStatus DRTS WITH (NOLOCK)
            where
                DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id and
                DRTS.Lpu_id = :Lpu_id
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
			DR.DrugRequestStatus_id
			from v_DrugRequest DR WITH (NOLOCK)
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
            select top 1
                dr.DrugRequestStatus_id
            from
                v_DrugRequest dr with (nolock)
                left join v_DrugRequestStatus drs with(nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
            where
                dr.MedPersonal_id is not null and
                dr.DrugGroup_id is null and
                dr.PersonRegisterType_id is null and
                drs.DrugRequestStatus_Code = 5 and --5 - Перераспределение
                dr.DrugRequestPeriod_id = :DrugRequestPeriod_id and
                dr.Lpu_id = :Lpu_id
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
				set @normativ_fed_lgot = isnull((
					select top 1 DrugRequestQuota_Person from DrugRequestQuota with (nolock)
					where PersonRegisterType_id = {$data['PersonRegisterType_id']} and DrugRequestPeriod_id = {$data['DrugRequestPeriod_id']} and DrugFinance_id = (select top 1 DrugFinance_id from v_DrugFinance with (nolock) where DrugFinance_SysNick = 'fed')
				), 0);
				set @normativ_reg_lgot = isnull((
					select top 1 DrugRequestQuota_Person from DrugRequestQuota with (nolock)
					where PersonRegisterType_id = {$data['PersonRegisterType_id']} and DrugRequestPeriod_id = {$data['DrugRequestPeriod_id']} and DrugFinance_id = (select top 1 DrugFinance_id from v_DrugFinance with (nolock) where DrugFinance_SysNick = 'reg')
				), 0);
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
				select
					count(DR.DrugRequest_id) as cnt
				from
					DrugRequest DR WITH (NOLOCK)
					left join v_LpuSection LpuSection WITH (NOLOCK) on LpuSection.LpuSection_id = DR.LpuSection_id
				where
					{$where};
			";
		} else {
			if ($mode == "with_lgot_count") {
				$lgot_count_with = "with filtered_drugrequest as (
					select
						DR.Lpu_id,
						DR.DrugRequest_id,
						DR.MedPersonal_id
					from
						DrugRequest DR with (nolock)
					where
                       {$where}
				), lgot_count as (
					select
						dr.DrugRequest_id,
						count(case when pt.ReceptFinance_id = 1 then pc.Person_id end) as fed_cnt,
						count(case when pt.ReceptFinance_id = 2 then pc.Person_id end) as reg_cnt
					from
						MedStaffRegion msr with (nolock)
						left join PersonCard pc with (nolock) on pc.LpuRegion_id = msr.LpuRegion_id
						left join PersonPrivilege pp with (nolock) on pp.Person_id = pc.Person_id
						left join PrivilegeType pt with (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
						left join v_DrugRequest dr with (nolock) on dr.DrugRequest_id in (select DrugRequest_id from filtered_drugrequest with(nolock)) and dr.MedPersonal_id = msr.MedPersonal_id
						left join v_DrugRequestPeriod drp with (nolock) on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
					where
						msr.MedPersonal_id in (select MedPersonal_id from filtered_drugrequest with(nolock)) and
						pp.PrivilegeType_id is not null and
						pp.PersonPrivilege_begDate <= drp.DrugRequestPeriod_begDate and
						(
							pp.PersonPrivilege_endDate is null or
							pp.PersonPrivilege_endDate >= drp.DrugRequestPeriod_begDate
						)
					group by
						dr.DrugRequest_id
				)";
				$lgot_count_select = "
					lgot_count.fed_cnt as FedPrivilegePerson_Count,
					lgot_count.reg_cnt as RegPrivilegePerson_Count,
				";
				$lgot_count_join = "left join lgot_count with(nolock) on lgot_count.DrugRequest_id = DrugRequest.DrugRequest_id";
				$lgot_count_group = "
					lgot_count.fed_cnt,
					lgot_count.reg_cnt,
				";
			}

			$sql = "
				declare @normativ_fed_lgot numeric = ".(isset($options['normativ_fed_lgot']) ? $options['normativ_fed_lgot'] : "0").",
					@koef_fed_lgot numeric = 1,
					@normativ_reg_lgot numeric = ".(isset($options['normativ_reg_lgot']) ? $options['normativ_reg_lgot'] : "0").",
					@koef_reg_lgot numeric = 1;

				{$set_normativ}

				{$lgot_count_with}
				Select
					DrugRequest.DrugRequest_id,
					DrugRequest.DrugRequest_Version,
					DrugRequestPeriod_id,
					DrugRequestPeriod_Name,
					DrugRequestStatus_id,
					DrugRequestStatus_Name,
					DrugRequest_Name,
					{$lgot_count_select}
					Sum(DrugRequest_SummaFed) as DrugRequest_SummaFed,
					Sum(DrugRequest_SummaReg) as DrugRequest_SummaReg,
					Sum(DrugRequest_SummaFedReserve) as DrugRequest_SummaFedReserve,
					Sum(DrugRequest_SummaRegReserve) as DrugRequest_SummaRegReserve,
					IsNull(DRRLpu.DRRSumFed,0)+Sum(DrugRequest_SummaFed) as DrugRequest_SummaFedAll,
					IsNull(DRRLpu.DRRSumReg,0)+Sum(DrugRequest_SummaReg) as DrugRequest_SummaRegAll,
					Sum(DrugRequest_SummaFedLimit) as DrugRequest_SummaFedLimit,
					Sum(DrugRequest_SummaRegLimit)+(DrugRequest_YoungChildCount*@normativ_reg_lgot*3*@koef_reg_lgot) as DrugRequest_SummaRegLimit,
					Lpu_id,
					Lpu_Nick as Lpu_Name,
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
				from
					(Select
						DR.DrugRequest_id,
						DR.DrugRequest_Version,
						DR.DrugRequestPeriod_id,
						RTrim(DrugRequestPeriod.DrugRequestPeriod_Name) as DrugRequestPeriod_Name,
						DrugRequestPeriod.DrugRequestPeriod_begDate,
						DrugRequestPeriod.DrugRequestPeriod_endDate,
						DR.DrugRequestStatus_id,
						RTrim(DrugRequestStatus.DrugRequestStatus_Name) as DrugRequestStatus_Name,
						RTrim(DR.DrugRequest_Name) as DrugRequest_Name,
						Sum(case when DRR.DrugRequestType_id=1 and (IsNull(DRR.DrugRequestRow_Deleted,1)=1) then DRR.DrugRequestRow_Summa else 0 end) as DrugRequest_SummaFed,
						Sum(case when DRR.DrugRequestType_id=2 and (IsNull(DRR.DrugRequestRow_Deleted,1)=1) then DRR.DrugRequestRow_Summa else 0 end) as DrugRequest_SummaReg,
						Sum(case when DRR.DrugRequestType_id=1 and (IsNull(DRR.DrugRequestRow_Deleted,1)=1) and DRR.Person_id is null then DRR.DrugRequestRow_Summa else 0 end) as DrugRequest_SummaFedReserve,
						Sum(case when DRR.DrugRequestType_id=2 and (IsNull(DRR.DrugRequestRow_Deleted,1)=1) and DRR.Person_id is null then DRR.DrugRequestRow_Summa else 0 end) as DrugRequest_SummaRegReserve,
						DRR.Person_id,
						IsNull(DR.DrugRequest_YoungChildCount,0) as DrugRequest_YoungChildCount,
						count(distinct case when DRR.DrugRequestType_id = 1 then DRR.Person_id else Null end)*@normativ_fed_lgot*3*@koef_fed_lgot as DrugRequest_SummaFedLimit,
						count(distinct case when DRR.DrugRequestType_id = 2 then DRR.Person_id else Null end)*@normativ_reg_lgot*3*@koef_reg_lgot as DrugRequest_SummaRegLimit,
						DR.Lpu_id,
						LP.Lpu_Nick,
						DR.MedPersonal_id,
						RTrim(Person_FIO) as MedPersonal_FIO,
						DR.LpuSection_id,
						RTrim(LpuSection_Name) as LpuSection_Name,
						DR.PersonRegisterType_id,
						MT.PersonRegisterType_Name,
						DRK.DrugRequestKind_Name,
						convert(varchar(10), DR.DrugRequest_insDT, 104) as DrugRequest_insDT,
						convert(varchar(10), DR.DrugRequest_updDT, 104) as DrugRequest_updDT,
						convert(varchar(10), Null, 104) as DrugRequest_delDT
						from v_DrugRequest DR WITH (NOLOCK)
						left join DrugRequestStatus WITH (NOLOCK) on DrugRequestStatus.DrugRequestStatus_id = DR.DrugRequestStatus_id
						left join DrugRequestPeriod WITH (NOLOCK) on DrugRequestPeriod.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
						left join v_MedPersonal MP WITH (NOLOCK) on MP.MedPersonal_id = DR.MedPersonal_id and MP.Lpu_id = DR.lpu_id
						left join v_Lpu LP WITH (NOLOCK) on LP.Lpu_id = DR.lpu_id
						left join v_LpuSection LpuSection WITH (NOLOCK) on LpuSection.LpuSection_id = DR.LpuSection_id
						left join v_PersonRegisterType MT WITH (NOLOCK) on MT.PersonRegisterType_id = DR.PersonRegisterType_id
						left join DrugRequestKind DRK WITH (NOLOCK) on DRK.DrugRequestKind_id = DR.DrugRequestKind_id
						left join DrugRequestRow DRR WITH (NOLOCK) on DRR.DrugRequest_id = DR.DrugRequest_id
						where
							{$where}
						group by
						DR.DrugRequest_id,
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
					outer apply (select distinct DrugRequest.DrugRequest_id,
						Sum(case when DrrAllLpu.DrugRequestType_id=1 then DrrAllLpu.DrugRequestRow_Summa else 0 end) as DRRSumFed,
						Sum(case when DrrAllLpu.DrugRequestType_id=2 then DrrAllLpu.DrugRequestRow_Summa else 0 end) as DRRSumReg
						from v_DrugRequestRow DrrAllLpu WITH (NOLOCK)
						inner join DrugRequest DRLpu WITH (NOLOCK) on DRLpu.DrugRequest_id = DrrAllLpu.DrugRequest_id and
						DRLpu.DrugRequestPeriod_id = DrugRequest.DrugRequestPeriod_id
						where DRLpu.Lpu_id != DrugRequest.Lpu_id
						and DrrAllLpu.Person_id in ( Select DRR.Person_id from DrugRequestRow DRR with (nolock) where DRR.DrugRequest_id = DrugRequest.DrugRequest_id and (IsNull(DRR.DrugRequestRow_Deleted,1)=1) )
					) as DRRLpu
					{$lgot_count_join}
				group by
					DrugRequest.DrugRequest_id,
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
					IsNull(DRRLpu.DRRSumFed,0),
					IsNull(DRRLpu.DRRSumReg,0),
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
				order by
					DrugRequest.DrugRequest_id
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
				select
					count(DrugRequestPerson_id) as cnt
				from
					v_DrugRequestPerson with (nolock)
				where
					DrugRequestPeriod_id = :DrugRequestPeriod_id and
					Person_id = :Person_id and
					Lpu_id = :Lpu_id and
					(MedPersonal_id = :MedPersonal_id OR :MedPersonal_id IS NULL) and
					(PersonRegisterType_id = :PersonRegisterType_id OR :PersonRegisterType_id IS NULL)
			";
			$result = $this->db->query($query, $data);
			$res = $result->result('array');
			if ($res && $res[0]['cnt'] > 0) {
				return array(array('Error_Code' => '999', 'Error_Msg' => 'Выбранный человек уже присутствует в заявке.'));
			}
		}
		
		$query = "
			Declare @DrugRequestPerson_id bigint;
			Declare @Error_Code int;
			Declare @Error_Message varchar(4000);
			Set @DrugRequestPerson_id = :DrugRequestPerson_id;
			Set @Error_Code = 0;
			Set @Error_Message = '';
			exec {$proc} @DrugRequestPerson_id = @DrugRequestPerson_id output,
			@Server_id = :Server_id,
			@Lpu_id = :Lpu_id,
			@Person_id = :Person_id,
			@MedPersonal_id = :MedPersonal_id,
			@DrugRequestPeriod_id = :DrugRequestPeriod_id,
			@PersonRegisterType_id = :PersonRegisterType_id,
			@pmUser_id = :pmUser_id,
			@Error_Code = @Error_Code output,
			@Error_Message = @Error_Message output;
			select @DrugRequestPerson_id as DrugRequestPerson_id, @Error_Code as Error_Code, @Error_Message as Error_Message;";
		
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
					select
						(case
							when dlrt.DrugRequest_Price > 0 then dlrt.DrugRequest_Price
							else dlr.DrugListRequest_Price
						end) as price
					from
						v_DrugListRequest dlr with (nolock)
						left join v_DrugListRequestTorg dlrt with (nolock) on dlrt.DrugListRequest_id = dlr.DrugListRequest_id and dlrt.TRADENAMES_id = :TRADENAMES_id
					where
						dlr.DrugComplexMnn_id = :DrugComplexMnn_id and
						dlr.DrugRequestProperty_id = :DrugRequestProperty_id;
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
		Declare @DrugRequestRow_id bigint;
		Declare @DrugRequestType_id bigint;
		Declare @DrugRequestPeriod_begDate datetime;
		Declare @DrugRequestPeriod_endDate datetime;
		Declare @DrugFinance_id bigint;
		Declare @Error_Code int;
		Declare @Error_Message varchar(4000);

		Set @DrugRequestRow_id = :DrugRequestRow_id;
		Set @DrugRequestType_id = :DrugRequestType_id;
		Set @Error_Code = 0;
		Set @Error_Message = '';

		select
			@DrugRequestPeriod_begDate = DrugRequestPeriod_begDate,
			@DrugRequestPeriod_endDate = DrugRequestPeriod_endDate
		from
			v_DrugRequestPeriod with (nolock)
		where
			DrugRequestPeriod_id = (
				select
					DrugRequestPeriod_id
				from
					v_DrugRequest with (nolock)
				where
					DrugRequest_id = :DrugRequest_id
			);

		Set @DrugFinance_id = (
			select top 1 DrugFinance_id
			from v_DrugFinance with (nolock)
			where
				((DrugFinance_SysNick = 'fed' and @DrugRequestType_id=1) or (DrugFinance_SysNick = 'reg' and @DrugRequestType_id=2)) and
				(DrugFinance_begDate is null or DrugFinance_begDate <= @DrugRequestPeriod_endDate) and
				(DrugFinance_endDate is null or DrugFinance_endDate >= @DrugRequestPeriod_begDate)
		);

		exec {$proc} @DrugRequestRow_id = @DrugRequestRow_id output,
		@DrugRequest_id = :DrugRequest_id,
		@DrugRequestType_id = @DrugRequestType_id,
		@Person_id = :Person_id,
		@DrugProtoMnn_id = :DrugProtoMnn_id,
		@Drug_id = :Drug_id,
		@DrugRequestRow_Kolvo = :DrugRequestRow_Kolvo,
		@DrugRequestRow_Summa = {$summa},
		@DrugRequestRow_KolDrugBuy = :DrugRequestRow_Kolvo,
		@DrugRequestRow_SumBuy = {$summa},
		@pmUser_id = :pmUser_id,
		@DrugRequestRow_DoseOnce = :DrugRequestRow_DoseOnce,
 		@Okei_oid = :Okei_oid,
		@DrugRequestRow_DoseDay = :DrugRequestRow_DoseDay,
		@Okei_did = null,
		@DrugRequestRow_DoseCource = :DrugRequestRow_DoseCource,
		@Okei_cid = null,
		@DrugComplexMnn_id = :DrugComplexMnn_id,
		@TRADENAMES_id = :TRADENAMES_id,
		@DrugFinance_id = @DrugFinance_id,
		@Error_Code = @Error_Code output,
		@Error_Message = @Error_Message output;
		select @DrugRequestRow_id as DrugRequestRow_id, @Error_Code as Error_Code, @Error_Message as Error_Message;";
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
		Declare @DrugRequest_id bigint;
		Declare @DrugRequestCategory_id bigint;
		Declare @Error_Code int;
		Declare @Error_Message varchar(4000);

		Set @DrugRequest_id = {$data['DrugRequest_id']};
		Set @Error_Code = 0;
		Set @Error_Message = '';
		Set @DrugRequestCategory_id = (select top 1 DrugRequestCategory_id from v_DrugRequestCategory with (nolock) where DrugRequestCategory_SysNick = '{$data['DrugRequestCategory_SysNick']}');

		exec {$proc} @DrugRequest_id = @DrugRequest_id output,
		@Server_id = {$data['Server_id']},
		@DrugRequestPeriod_id = {$data['DrugRequestPeriod_id']},
		@DrugRequestStatus_id = {$data['DrugRequestStatus_id']},
		@DrugRequest_Name = '{$data['DrugRequest_Name']}',
		@DrugRequest_YoungChildCount = '{$data['DrugRequest_YoungChildCount']}',
		@Lpu_id = {$data['Lpu_id']},
		@LpuSection_id = {$data['LpuSection_id']},
		@MedPersonal_id = {$data['MedPersonal_id']},
		@PersonRegisterType_id = {$data['PersonRegisterType_id']},
		@DrugRequestCategory_id = @DrugRequestCategory_id,
		@pmUser_id = {$data['pmUser_id']},
		@Error_Code = @Error_Code output,
		@Error_Message = @Error_Message output;
		select @DrugRequest_id as DrugRequest_id, @Error_Code as Error_Code, @Error_Message as Error_Message;";
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
			from DrugRequest DR WITH (NOLOCK)
			where 
				DR.DrugRequestPeriod_id = ? and 
				DR.Lpu_id = ?
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
			Select top 1
				DrugRequest.DrugRequestStatus_id,
				DrugRequest.Lpu_id
			from v_DrugRequest DrugRequest WITH (NOLOCK)
			inner join DrugRequestRow WITH (NOLOCK) on DrugRequestRow.DrugRequest_id = DrugRequest.DrugRequest_id
			where 
				{$filter}
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
			Declare
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '',
				@DrugRequestRow_id bigint =  :DrugRequestRow_id
			set nocount on
			begin try
				Update DrugRequestRow 
				set 
					DrugRequestRow_Deleted = 2,
					DrugRequestRow_delDT = dbo.tzGetDate(),
					pmUser_delID = :pmUser_id
				from DrugRequestRow DRR WITH (NOLOCK)
				where 
					DRR.DrugRequestRow_id = @DrugRequestRow_id
			end try
				begin catch
					set @Error_Code = error_number()
					set @Error_Message = error_message()
				end catch
			set nocount off
			Select @DrugRequestRow_id as DrugRequestRow_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
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
		Declare @DrugRequestTotalStatus_id bigint;
		Declare @Error_Code int;
		Declare @Error_Message varchar(4000);
		Declare @DrugRequestTotalStatus_closeDT datetime;
		Set @DrugRequestTotalStatus_id = {$data['DrugRequestTotalStatus_id']};
		Set @Error_Code = 0;
		Set @Error_Message = '';
		Set @DrugRequestTotalStatus_closeDT = {$data['DrugRequestTotalStatus_closeDT']};
		exec {$proc} @DrugRequestTotalStatus_id = @DrugRequestTotalStatus_id output,
		@Server_id = {$data['Server_id']},
		@DrugRequestPeriod_id = {$data['DrugRequestPeriod_id']},
		@DrugRequestTotalStatus_IsClose = {$data['DrugRequestTotalStatus_IsClose']},
		@DrugRequestTotalStatus_closeDT = @DrugRequestTotalStatus_closeDT,
		@Lpu_id = {$data['Lpu_id']},
		@pmUser_id = {$data['pmUser_id']},
		@Error_Code = @Error_Code output,
		@Error_Message = @Error_Message output;
		select @DrugRequestTotalStatus_id as DrugRequestTotalStatus_id, @Error_Code as Error_Code, @Error_Message as Error_Message;";
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
		Declare @DrugRequestTotalStatus_id bigint;
		Declare @Error_Code int;
		Declare @Error_Message varchar(4000);
		Set @DrugRequestTotalStatus_id = {$data['DrugRequestTotalStatus_id']};
		Set @Error_Code = 0;
		Set @Error_Message = '';
		exec {$proc} @DrugRequestTotalStatus_id = @DrugRequestTotalStatus_id output,
		@Server_id = {$data['Server_id']},
		@DrugRequestPeriod_id = {$data['DrugRequestPeriod_id']},
		@Lpu_id = {$data['Lpu_id']},
		@DrugRequestTotalStatus_FedLgotCount = {$data['DrugRequestTotalStatus_FedLgotCount']},
		@DrugRequestTotalStatus_RegLgotCount = {$data['DrugRequestTotalStatus_RegLgotCount']},
		@pmUser_id = {$data['pmUser_id']},
		@Error_Code = @Error_Code output,
		@Error_Message = @Error_Message output;
		select @DrugRequestTotalStatus_id as DrugRequestTotalStatus_id, @Error_Code as Error_Code, @Error_Message as Error_Message;";
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
                declare
                    @DrugRequestStatus2_id bigint,
                    @DrugRequestStatus3_id bigint,
                    @DrugRequestStatus5_id bigint;

                set @DrugRequestStatus2_id = (select top 1 DrugRequestStatus_id from v_DrugRequestStatus with(nolock) where DrugRequestStatus_Code = 2); -- 2 - Сформированная
                set @DrugRequestStatus3_id = (select top 1 DrugRequestStatus_id from v_DrugRequestStatus with(nolock) where DrugRequestStatus_Code = 3); -- 3 - Утвержденная
                set @DrugRequestStatus5_id = (select top 1 DrugRequestStatus_id from v_DrugRequestStatus with(nolock) where DrugRequestStatus_Code = 5); -- 5 - Перераспределение

                update
                    v_DrugRequest
                set
                    DrugRequestStatus_id = @DrugRequestStatus5_id
                where
                    MedPersonal_id is not null and
                    DrugGroup_id is null and
                    PersonRegisterType_id is null and
                    DrugRequestStatus_id in (@DrugRequestStatus2_id, @DrugRequestStatus3_id) and
                    DrugRequestPeriod_id = :DrugRequestPeriod_id and
                    Lpu_id = :Lpu_id;
            ";
            $status_id = $this->getFirstResultFromQuery("select top 1 DrugRequestStatus_id from v_DrugRequestStatus with(nolock) where DrugRequestStatus_Code = 5");
        } else {
            $query = "
                declare
                    @DrugRequestStatus2_id bigint,
                    @DrugRequestStatus3_id bigint,
                    @DrugRequestStatus5_id bigint;

                set @DrugRequestStatus2_id = (select top 1 DrugRequestStatus_id from v_DrugRequestStatus with(nolock) where DrugRequestStatus_Code = 2); -- 2 - Сформированная
                set @DrugRequestStatus3_id = (select top 1 DrugRequestStatus_id from v_DrugRequestStatus with(nolock) where DrugRequestStatus_Code = 3); -- 3 - Утвержденная
                set @DrugRequestStatus5_id = (select top 1 DrugRequestStatus_id from v_DrugRequestStatus with(nolock) where DrugRequestStatus_Code = 5); -- 5 - Перераспределение

                update
                    v_DrugRequest
                set
                    DrugRequestStatus_id = @DrugRequestStatus2_id
                where
                    MedPersonal_id is not null and
                    DrugGroup_id is null and
                    PersonRegisterType_id is null and
                    DrugRequestStatus_id in (@DrugRequestStatus5_id, @DrugRequestStatus3_id) and
                    DrugRequestPeriod_id = :DrugRequestPeriod_id and
                    Lpu_id = :Lpu_id;
            ";
            $status_id = $this->getFirstResultFromQuery("select top 1 DrugRequestStatus_id from v_DrugRequestStatus with(nolock) where DrugRequestStatus_Code = 2");
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
                declare
                    @DrugRequestTotalStatus_IsClose bigint;

                set @DrugRequestTotalStatus_IsClose = (select top 1 YesNo_id from v_YesNo with(nolock) where YesNo_Code = 0); -- 0 - Нет

                update
                    DrugRequestTotalStatus
                set
                    DrugRequestTotalStatus_IsClose = @DrugRequestTotalStatus_IsClose
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
                select
					drts.DrugRequestTotalStatus_id,
					is_close.YesNo_Code as isClose
                from
                    v_DrugRequestTotalStatus drts with (nolock)
					left join v_YesNo is_close with(nolock) on is_close.YesNo_id = drts.DrugRequestTotalStatus_IsClose
                where
                    drts.DrugRequestPeriod_id = :DrugRequestPeriod_id and
                    drts.Lpu_id = :Lpu_id
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
                        declare
                            @DrugRequestTotalStatus_IsClose bigint,
                            @DrugRequestTotalStatus_closeDT datetime;

                        set @DrugRequestTotalStatus_IsClose = (select top 1 YesNo_id from v_YesNo with(nolock) where YesNo_Code = 1); -- 1 - Да
                        set @DrugRequestTotalStatus_closeDT = dbo.tzGetDate();

                        update
                            DrugRequestTotalStatus
                        set
                            DrugRequestTotalStatus_IsClose = @DrugRequestTotalStatus_IsClose,
                            DrugRequestTotalStatus_closeDT = @DrugRequestTotalStatus_closeDT
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
                    declare
                        @DrugRequestTotalStatus_id bigint,
                        @DrugRequestTotalStatus_IsClose bigint,
                        @DrugRequestTotalStatus_closeDT datetime,
                        @Error_Code int = 0,
                        @Error_Message varchar(4000) = '';

                    set @DrugRequestTotalStatus_IsClose = (select top 1 YesNo_id from v_YesNo with(nolock) where YesNo_Code = 1); -- 1 - Да
                    set @DrugRequestTotalStatus_closeDT = dbo.tzGetDate();

                    exec dbo.p_DrugRequestTotalStatus_ins
                        @DrugRequestTotalStatus_id = @DrugRequestTotalStatus_id output,
                        @Server_id = :Server_id,
                        @DrugRequestPeriod_id = :DrugRequestPeriod_id,
                        @DrugRequestTotalStatus_IsClose = @DrugRequestTotalStatus_IsClose,
                        @DrugRequestTotalStatus_closeDT = @DrugRequestTotalStatus_closeDT,
                        @Lpu_id = :Lpu_id,
                        @pmUser_id = :pmUser_id,
                        @Error_Code = @Error_Code output,
                        @Error_Message = @Error_Message output;

                    select @DrugRequestTotalStatus_id as DrugRequestTotalStatus_id, @Error_Code as Error_Code, @Error_Message as Error_Message;
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

		$filter = $drugrequest_isonko; //"((DrugProtoMnn_IsCommon=2) or (DrugProtoMnn_IsCommon is null and isnull(DrugProtoMnn_IsOnko,0)!=2 and isnull(DrugProtoMnn_IsCrazy,0) != 2 and isnull(DrugProtoMnn_IsRA,0) != 2))";
		/*$filter .= "
		  OR (((isnull(DrugProtoMnn_IsCrazy,0) = 2) AND
				EXISTS (
					SELECT 1 FROM DrugRequestLpuGroup g
					WHERE g.DrugProtoMnn_id = DrugProtoMnn.DrugProtoMnn_id
						AND g.Lpu_id = :Lpu_id
						AND (IsNull(g.medPersonal_id,:MedPersonal_id) = :MedPersonal_id)
					)
				)
		  OR ((isnull(DrugProtoMnn_IsOnko,0) = 2) AND
				EXISTS (
					SELECT 1 FROM DrugRequestLpuGroup g
					WHERE g.DrugProtoMnn_id = DrugProtoMnn.DrugProtoMnn_id
						AND g.Lpu_id = :Lpu_id
						AND (IsNull(g.medPersonal_id,:MedPersonal_id) = :MedPersonal_id)
					)
				)
		  OR ((isnull(DrugProtoMnn_IsRA,0) = 2) AND
				EXISTS (
				SELECT 1 FROM DrugRequestLpuGroup g
					WHERE g.DrugProtoMnn_id = DrugProtoMnn.DrugProtoMnn_id
						AND g.Lpu_id = :Lpu_id
						AND (IsNull(g.medPersonal_id,:MedPersonal_id) = :MedPersonal_id)
					)
				))";*/
		$filter .= "
		  OR (
			EXISTS (
				SELECT 1 FROM DrugRequestLpuGroup g with (nolock)
				WHERE g.DrugProtoMnn_id = DrugProtoMnn.DrugProtoMnn_id
					AND g.Lpu_id = :Lpu_id
					AND (IsNull(g.medPersonal_id,:MedPersonal_id) = :MedPersonal_id)
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
				$where .= " and Drug.Drug_Name Like :Drug_Name";
			}
			
			if ($data['DrugProtoMnn_id'] > 0)
			{
				$where .= " and DrugProtoMnn.DrugProtoMnn_id = :Drug_id" ;
				$params['Drug_id'] = $data['DrugProtoMnn_id'];
			}
			$params['ReceptFinance_id'] = $data['ReceptFinance_id'];

			$query = "
				Select distinct top 50
					Drug.Drug_id as DrugProtoMnn_id,
					Drug.Drug_Code as DrugProtoMnn_Code,
					RTRIM(Drug.Drug_Name) as DrugProtoMnn_Name,
					:ReceptFinance_id as  ReceptFinance_id,
					ROUND(DrugPrice.DrugState_Price,2) as DrugProtoMnn_Price
				From  $table  Drug WITH (NOLOCK)
					left join v_DrugPrice DrugPrice WITH (NOLOCK) on DrugPrice.Drug_id = Drug.Drug_id
						and DrugPrice.DrugProto_begDate = 
						(
							select max(DrugProto_begDate)
							from v_DrugPrice WITH (NOLOCK)
							where Drug_id = Drug.Drug_id
						)
				Where (1=1)
					 $where
				Order by DrugProtoMnn_Name
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
				$where .= " and DrugProtoMnn.DrugProtoMnn_Name Like :DrugProtoMnn_Name";
				$params['DrugProtoMnn_Name'] = $data['query'] . "%";
			}
			else 
			if (strlen($data['DrugProtoMnn_Name']) > 0)
			{
				$where .= " and DrugProtoMnn.DrugProtoMnn_Name Like :DrugProtoMnn_Name";
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
				$and_ostat = " and ISNULL(Ostat.DrugOstat_Kolvo, 0) = 0";
			}
			$query = "
				Select distinct top 50
					DrugProtoMnn.DrugProtoMnn_id,
					--DrugProtoMnn.DrugProtoMnn_Code,
					DrugProtoMnn.DrugProtoMnn_Name + ' - ' + ISNULL(cast(DrugProtoMnn.DrugProtoMnn_id as varchar), 'empty') as DrugProtoMnn_Name,
					--DrugProtoMnn.DrugMnn_id,
					DrugProtoMnn.ReceptFinance_id,
					ROUND(DrugState.DrugState_Price,2) as DrugProtoMnn_Price
				From v_DrugProtoMnn DrugProtoMnn (nolock)
				left join v_DrugState DrugState (nolock) on DrugState.DrugProtoMnn_id = DrugProtoMnn.DrugProtoMnn_id
				inner join v_DrugProto DrugProto (nolock) on DrugState.DrugProto_id = DrugProto.DrugProto_id {$wt}
				/*
				left join DrugProtoRelation DPR on DPR.DrugProtoMnn_id = DrugProtoMnn.DrugProtoMnn_id
				left join DrugState on DrugState.Drug_id = DPR.Drug_id 
				*/
				Where (1=1) $where and DrugProtoMnn_Name<>'~' and $filter
				Order by DrugProtoMnn_Name
			";
            if(isset($data['loadMnnList']) && $data['loadMnnList']=='2'){
                $query = "
					Select distinct
						Ostat.DrugOstat_Kolvo,
						DrugProtoMnn.ReceptFinance_id,
						DrugProtoMnn.DrugProtoMnn_id,
						DM.DrugMnn_id,
						--DM.DrugMnn_Name
						DrugProtoMnn.DrugProtoMnn_Name as DrugMnn_Name
					From
						v_DrugProtoMnn DrugProtoMnn (nolock)
						left join v_DrugState DrugState (nolock) on DrugState.DrugProtoMnn_id = DrugProtoMnn.DrugProtoMnn_id
						inner join v_DrugProto DrugProto (nolock) on DrugState.DrugProto_id = DrugProto.DrugProto_id {$wt}
						inner join v_DrugMnn DM (nolock) on DM.DrugMnn_id = DrugProtoMnn.DrugMnn_id
						inner join v_Drug D (nolock) on D.DrugMnn_id = DM.DrugMnn_id
						outer apply (
							select
								SUM(DO.DrugOstat_Kolvo) as DrugOstat_Kolvo
							from
								v_Drug D (nolock)
								inner join v_DrugOstat DO (nolock) on DO.Drug_id = D.Drug_id
							where
								D.DrugMnn_id = DrugProtoMnn.DrugMnn_id
								and DO.ReceptFinance_id = DrugProtoMnn.ReceptFinance_id
						) Ostat
					Where
						(1=1)
						{$where}
						and DM.DrugMnn_Name<>'~'
						{$and_ostat}
						--and ISNULL(Ostat.DrugOstat_Kolvo, 0) = 0
						and {$filter}
					Order by
						DrugProtoMnn.DrugProtoMnn_Name --DM.DrugMnn_Name
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
			Select top 1
				DrugProtoMnn.DrugProtoMnn_id,
				DrugProtoMnn.DrugProtoMnn_Code,
				DrugProtoMnn.DrugProtoMnn_Name,
				DrugProtoMnn.DrugMnn_id,
				DrugProtoMnn.ReceptFinance_id,
				Round(DrugState.DrugState_Price,2) as DrugProtoMnn_Price
			From DrugProtoMnn WITH (NOLOCK)
			outer apply (
				select top 1
					DrugState_Price,
					DrugProto.DrugRequestPeriod_id
				from DrugState WITH (NOLOCK)
				left join DrugProto WITH (NOLOCK) on DrugState.DrugProto_id = DrugProto.DrugProto_id
				where 
					DrugState.DrugProtoMnn_id = DrugProtoMnn.DrugProtoMnn_id
					{$wt}
				order by DrugState_Price desc
			) as DrugState
			Where (1=1) $where and {$filter}
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
			Select top 1
				Drug.Drug_id,
				Drug.Drug_Code,
				RTRIM(Drug.Drug_Name) as Drug_Name,
				Drug.DrugMnn_id,
				cast(DrugPrice.DrugState_Price as numeric(18, 2)) as DrugState_Price
				From " . $table . " Drug WITH (NOLOCK)
					left join v_DrugPrice DrugPrice WITH (NOLOCK) on DrugPrice.Drug_id = Drug.Drug_id
						and DrugPrice.DrugProto_begDate = 
						(
							select max(DrugProto_begDate)
							from v_DrugPrice WITH (NOLOCK)
							where Drug_id = Drug.Drug_id
						)
			Where (1=1) ".$where;
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
			sum(DrugRequestRow_Summa) as DrugRequest_Summa
			from DrugRequestRow WITH (NOLOCK)
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
			count(*) as record_count
			from DrugRequestPerson DRP WITH (NOLOCK)
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
				count(DRR.DrugRequestRow_id) as record_count
			from
				v_DrugRequestPerson DRP (nolock)
				inner join v_DrugRequestRow DRR (nolock) on DRR.Person_id = DRP.Person_id
				inner join v_DrugRequest DR (nolock) on DR.DrugRequest_id = DRR.DrugRequest_id 
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
			Delete from DrugRequestRow WITH (ROWLOCK)
			from DrugRequestRow DRR WITH (ROWLOCK)
			left join DrugRequestPerson DRP WITH (NOLOCK) on DRR.Person_id = DRP.Person_id 
			left join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id 
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
			count(DRR.DrugRequestRow_id) as record_count
			from DrugRequestPerson DRP WITH (NOLOCK)
			left join DrugRequestRow DRR WITH (NOLOCK) on DRR.Person_id = DRP.Person_id 
			left join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id 
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
			DR.DrugRequest_id,
			DR.DrugRequestStatus_id,
			DRS.DrugRequestStatus_Name
			from v_DrugRequest DR WITH (NOLOCK)
			left join DrugRequestStatus DRS WITH (NOLOCK) on DRS.DrugRequestStatus_id = DR.DrugRequestStatus_id 
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
		Select top 1
			--count(*) as record_count
			DR.DrugRequest_id, 
			DR.DrugRequestStatus_id,
			DRTS.DrugRequestTotalStatus_IsClose
			from v_DrugRequest DR WITH (NOLOCK)
			left join DrugRequestTotalStatus DRTS WITH (NOLOCK) on DRTS.Lpu_id = DR.Lpu_id and DR.DrugRequestPeriod_id = DRTS.DrugRequestPeriod_id
			where
				DR.DrugRequestPeriod_id = {$data['DrugRequestPeriod_id']} and 
				{$medpersonal} and 
				{$lpu} and 
				{$id} and
				{$person_register}
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
			count(*) as record_count,
			DR.DrugRequestStatus_id
			from v_DrugRequestRow DRR WITH (NOLOCK)
			inner join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id
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
				count(DrugRequestTotalStatus_id) as record_count
			from
				DrugRequestTotalStatus with(nolock)
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
                count(dr.DrugRequest_id) as record_count
            from
                v_DrugRequest dr with (nolock)
                left join v_DrugRequestStatus drs with(nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
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
			count(*) as record_count
			from v_DrugRequestRow DRR WITH (NOLOCK)
			inner join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id
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
			count(*) as record_count
			from DrugRequestRow DRR WITH (NOLOCK)
			inner join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id
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
				sum(case when Lpu_id is null then 1 end) as region_request_count,
				sum(case
					when
						Lpu_id is null and
						DrugRequestStatus_Code = 1
					then 1
				end) as region_correct_status,
				sum(case when Lpu_id = :Lpu_id then 1 end) as mo_request_count,
				sum(case
					when
						Lpu_id = :Lpu_id and
						DrugRequestStatus_Code in (1, 2)
					then 1
				end) as mo_correct_status
			from
				DrugRequest dr with (nolock)
				left join DrugRequestStatus drs with (nolock) on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
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
			Declare @Error_Code bigint;
			Declare @Error_Message varchar(4000);
			exec p_{$object}_del
				:id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Message;
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
			Declare @Error_Code bigint;
			Declare @Error_Message varchar(4000);
			exec p_{$object}_del
				:id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Message;
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
			Declare @Error_Code bigint;
			Declare @Error_Message varchar(4000);
			exec p_DrugRequestRow_del :id, :pmUser_id, 2, @Error_Code = @Error_Code output, @Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Message;
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
			ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
		*/
		$query = "
			select
				RTRIM(COALESCE(DPM.DrugProtoMnn_Code, Drug.Drug_Code, '')) as DrugRequestRow_Code,
				RTRIM(COALESCE(DPM.DrugProtoMnn_Name, Drug.Drug_Name, '')) as DrugRequestRow_Name,
				SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)) as DrugRequestRow_Kolvo,
				ROUND(SUM(DRR.DrugRequestRow_Summa) / SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)), 2) as DrugRequestRow_Price,
				SUM(DRR.DrugRequestRow_Summa) as DrugRequestRow_Summa
			from DrugRequestRow DRR WITH (NOLOCK)
				inner join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id
					and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
				left join DrugProtoMnn DPM WITH (NOLOCK) on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
				left join Drug WITH (NOLOCK) on Drug.Drug_id = DRR.Drug_id
				left join LpuSection LS WITH (NOLOCK) on LS.LpuSection_id = DR.LpuSection_id
			where " . $filter . "
				and DRR.DrugRequestType_id = :DrugRequestType_id
				and (DRR.DrugRequestRow_delDT is null or DRR.DrugRequestRow_delDT > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
				and ISNULL(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
			group by
				DPM.DrugProtoMnn_Code,
				DPM.DrugProtoMnn_Name,
				Drug.Drug_Code,
				Drug.Drug_Name
			order by
				DrugRequestRow_Name
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
			select
				RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' '+ RTRIM(ISNULL(PS.Person_Secname, '')) as Person_Fio,
				SUM(DRR.DrugRequestRow_Summa) as DrugRequestRow_Summa
			from DrugRequestRow DRR WITH (NOLOCK)
				inner join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id
					and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
				-- inner join DrugProtoMnn DPM WITH (NOLOCK) on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
				left join LpuSection LS WITH (NOLOCK) on LS.LpuSection_id = DR.LpuSection_id
				left join v_PersonState PS WITH (NOLOCK) on PS.Person_id = DRR.Person_id
			where " . $filter . "
				and DRR.DrugRequestType_id = :DrugRequestType_id
				and (DRR.DrugRequestRow_delDT is null or DRR.DrugRequestRow_delDT > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
				and ISNULL(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
			group by
				PS.Person_id,
				PS.Person_Surname,
				PS.Person_Firname,
				PS.Person_Secname
			order by
				PS.Person_Surname,
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
			select
				ISNULL(PS.Person_id, 0) as Person_id,
				RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' '+ RTRIM(ISNULL(PS.Person_Secname, '')) as Person_Fio,
				RTRIM(COALESCE(DPM.DrugProtoMnn_Code, Drug.Drug_Code, '')) as DrugRequestRow_Code,
				RTRIM(COALESCE(DPM.DrugProtoMnn_Name, Drug.Drug_Name, '')) as DrugRequestRow_Name,
				SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)) as DrugRequestRow_Kolvo,
				ROUND(SUM(DRR.DrugRequestRow_Summa) / SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)), 2) as DrugRequestRow_Price,
				SUM(ISNULL(DRR.DrugRequestRow_Summa, 0)) as DrugRequestRow_Summa
			from DrugRequestRow DRR WITH (NOLOCK)
				inner join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id
					and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
				left join DrugProtoMnn DPM WITH (NOLOCK) on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
				left join Drug Drug WITH (NOLOCK) on Drug.Drug_id = DRR.Drug_id
				left join LpuSection LS WITH (NOLOCK) on LS.LpuSection_id = DR.LpuSection_id
				left join v_PersonState PS WITH (NOLOCK) on PS.Person_id = DRR.Person_id
			where " . $filter . "
				and DRR.DrugRequestType_id = :DrugRequestType_id
				and (DRR.DrugRequestRow_delDT is null or DRR.DrugRequestRow_delDT > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
				and ISNULL(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
			group by
				PS.Person_id,
				PS.Person_Surname,
				PS.Person_Firname,
				PS.Person_Secname,
				DPM.DrugProtoMnn_Code,
				DPM.DrugProtoMnn_Name,
				Drug.Drug_Code,
				Drug.Drug_Name
			order by
				Person_Fio
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
						select top 1 1
						from v_PersonRefuse pr2 with (nolock)
						where pr2.PersonRefuse_Year = RIGHT(:DrugRequestPeriod_id, 4)
							and pr2.personrefuse_isrefuse = 2
							and PersonCard.person_id = pr2.person_id
					)        
				";
				$privilege_filter = "and PersonPrivilege.PrivilegeType_id between 0 and 150";
			break;

			case 2:
				$params['Koef'] = $options['koef_reg_lgot'];
				$params['Normativ'] = $options['normativ_reg_lgot'];
				$privilege_filter = "and PersonPrivilege.PrivilegeType_id between 151 and 500";

				$join_str .= "left join YoungChild with(nolock) on YoungChild.Lpu_id = RequestPersonInfo.Lpu_id";

				$select_fields .= "ISNULL(YoungChild.YoungChild_Count, 0) as YoungChild_Count, ";

				$young_child_set = "
					YoungChild(
						Lpu_id,
						YoungChild_Count
					) as (
						select
							DR.Lpu_id,
							SUM(DR.DrugRequest_YoungChildCount) as YoungChild_Count
						from v_DrugRequest DR WITH (NOLOCK)				
						where " . $filter . "
							and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
						group by DR.Lpu_id
					),
				";
			break;
		}

		$query = "
			with " . $young_child_set . "
			RequestPersonInfo(
				Lpu_id,
				DrugRequestRow_PersonCount,
				DrugRequestRow_SummaPerson,
				DrugRequestRow_SummaReserve
			) as (
				select
					Lpu_id,
					COUNT(distinct DRR.Person_id) as DrugRequestRow_PersonCount,
					SUM(case when DRR.Person_id is not null then DRR.DrugRequestRow_Summa else 0 end) as DrugRequestRow_SummaPerson,
					SUM(case when DRR.Person_id is null then DRR.DrugRequestRow_Summa else 0 end) as DrugRequestRow_SummaReserve
				from DrugRequestRow DRR WITH (NOLOCK)
					inner join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id
						and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
					outer apply(
						select top 1 DRTS.DrugRequestTotalStatus_closeDT as DrugRequestTotalStatus_closeDT
						from DrugRequestTotalStatus DRTS WITH (NOLOCK)
						where DRTS.Lpu_id = DR.Lpu_id
							and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id
					) drts
				where " . $filter . "
					and DRR.DrugRequestType_id = :DrugRequestType_id	
					and (DRR.DrugRequestRow_delDT is null
						or DRR.DrugRequestRow_delDT > ISNULL(
							dbo.mindate(ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()),CAST(:DrugRequestRow_actDate as datetime)),
							dbo.MinDate(CAST(:DrugRequestRow_actDate as datetime), dbo.tzGetDate())
						)
					)
					and DRR.DrugRequestRow_updDT <= ISNULL(
						dbo.mindate(ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()), CAST(:DrugRequestRow_actDate as datetime)),
						dbo.MinDate(CAST(:DrugRequestRow_actDate as datetime), dbo.tzGetDate())
					)				
				group by Lpu_id
			),

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
				select
					Lpu.Lpu_id,			
					sum(case when DR.Lpu_id = PC.Lpu_id
						and DR.LpuSection_id != 337
						and DRR.Person_id = PC.Person_id
					then DRR.DrugRequestRow_Summa end) as DrugRequestRow_SummaPerson,
					sum(case when DR.Lpu_id <> PC.Lpu_id
						and MinZdravList.id is not null
						and DRR.Person_id = PC.Person_id
					then DRR.DrugRequestRow_Summa end) as DrugRequestRow_SummaMinZdrav,
					sum(case when DR.Lpu_id <> PC.Lpu_id
						and OnkoList.id is not null
						and DRR.Person_id = PC.Person_id
					then DRR.DrugRequestRow_Summa end) as DrugRequestRow_SummaOnkoDisp,
					sum(case when -- DR.Lpu_id <> PC.Lpu_id and
						DR.LpuSection_id = 337
						and DRR.Person_id = PC.Person_id
					then DRR.DrugRequestRow_Summa end) as DrugRequestRow_SummaOnkoGemat,
					sum(case when DR.Lpu_id <> PC.Lpu_id
						and PsychoList.id is not null
						and DRR.Person_id = PC.Person_id
					then DRR.DrugRequestRow_Summa end)  as DrugRequestRow_SummaPsycho,
					sum(case when DR.Lpu_id <> PC.Lpu_id
						and DR.LpuSection_id = 1659
						and DRR.Person_id = PC.Person_id
					then DRR.DrugRequestRow_Summa end) as DrugRequestRow_SummaRevmat,
					sum(case when DR.Lpu_id <> PC.Lpu_id
						and MinZdravList.id is null
						and OnkoList.id is null
						and PsychoList.id is null
						and DR.LpuSection_id not in (337, 1659)
						and DRR.Person_id = PC.Person_id
					then DRR.DrugRequestRow_Summa end) as DrugRequestRow_SummaOtherLpu
				from DrugRequestRow DRR WITH (NOLOCK) 
					cross apply (
						select top 1 Person_id, Lpu_id
						from v_PersonCard_all PC with (nolock)
						where 
							(PC.Lpu_id = :Lpu_id or NULLIF(:Lpu_id, 0) is null)
							and DRR.Person_id = PC.Person_id
							and PC.LpuAttachType_id = 1
							and PC.PersonCard_begDate <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
							and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
						order by PersonCard_id desc
					) PC
					inner join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id
						and DRR.DrugRequestType_id = :DrugRequestType_id
					inner join DrugRequestPeriod DRP WITH (NOLOCK) on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
						and DRP.DrugRequestPeriod_id = :DrugRequestPeriod_id
					inner join v_Lpu Lpu WITH (NOLOCK) on Lpu.Lpu_id = PC.Lpu_id																			
					outer apply (select id from MinZdravList() where id = Dr.Lpu_id) MinZdravList
					outer apply (select id from OnkoList() where id = Dr.Lpu_id) OnkoList			
					outer apply (select id from PsychoList() where id = Dr.Lpu_id) PsychoList					
				where 
					DRR.Person_id is not null
					and (DRR.DrugRequestRow_delDT is null or DRR.DrugRequestRow_delDT > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
					and ISNULL(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)			
				group by Lpu.Lpu_id
			),

			AttachPersonCount(
				Lpu_id,
				AttachPerson_Count
			) as (
				select 
					PersonCard.Lpu_id,
					count (distinct PersonCard.Person_id) as AttachPerson_Count
				from v_PersonCard_all PersonCard with (nolock)
					outer apply (
						select top 1 DrugRequestTotalStatus_CloseDt actualDate
						from DrugRequestTotalStatus with (nolock)
						where Lpu_id = PersonCard.Lpu_id
							and DrugRequestPeriod_id = :DrugRequestPeriod_id
					) DRTS
					cross apply (
						select min(PrivilegeType_id) PrivilegeType_id
						from v_PersonPrivilege as PersonPrivilege with (nolock)
						where PersonPrivilege.Person_id = PersonCard.Person_id
							and (PersonPrivilege.PersonPrivilege_endDate is null
								or PersonPrivilege.PersonPrivilege_endDate > ISNULL(
									dbo.mindate(ISNULL(DRTS.actualdate, dbo.tzGetDate()),CAST(:DrugRequestRow_actDate as datetime)),
									dbo.MinDate(CAST(:DrugRequestRow_actDate as datetime), dbo.tzGetDate())
								)
							)		
							and PersonPrivilege.PersonPrivilege_begDate <= ISNULL(
								dbo.mindate(ISNULL(DRTS.actualdate, dbo.tzGetDate()),CAST(:DrugRequestRow_actDate as datetime)),
								dbo.MinDate(CAST(:DrugRequestRow_actDate as datetime), dbo.tzGetDate())
							)
					) PersonPrivilege
				where (PersonCard.Lpu_id = :Lpu_id or NULLIF(:Lpu_id, 0) is null)
					" . $privilege_filter . "
					" . $person_refuse_filter . "
					and PersonCard.Lpu_id not in (100, 101)
					and PersonCard.LpuAttachType_id = 1
					and (PersonCard.PersonCard_endDate is null
						or PersonCard.PersonCard_endDate > ISNULL(
							dbo.mindate(ISNULL(DRTS.actualdate, dbo.tzGetDate()),CAST(:DrugRequestRow_actDate as datetime)),
							dbo.MinDate(CAST(:DrugRequestRow_actDate as datetime), dbo.tzGetDate())
						)
					)
					and PersonCard.PersonCard_begDate <= ISNULL(
						dbo.mindate(ISNULL(DRTS.actualdate, dbo.tzGetDate()),CAST(:DrugRequestRow_actDate as datetime)),
						dbo.MinDate(CAST(:DrugRequestRow_actDate as datetime), dbo.tzGetDate())
					)
				group by PersonCard.Lpu_id
			)

			select
				" . $select_fields . "
				ISNULL(RequestPersonInfo.Lpu_id, 0) as Lpu_id,	
				ISNULL(Lpu.Lpu_Name, '-') as Lpu_Name,
				ISNULL(RequestPersonInfo.DrugRequestRow_PersonCount, 0) as Request_PersonCount,
				ISNULL(AttachPersonCount.AttachPerson_Count, 0) as Attach_PersonCount,
				ISNULL(AttachPersonCount.AttachPerson_Count * :Normativ * 3 * :Koef, 0) as Attach_SummaLimit,
				ISNULL(RequestPersonInfo.DrugRequestRow_SummaPerson, 0) as Request_SummaPerson,
				ISNULL(RequestPersonInfo.DrugRequestRow_SummaReserve, 0) as Request_SummaReserve,
				ISNULL(AttachPersonInfo.DrugRequestRow_SummaPerson, 0) as Attach_SummaPerson,
				ISNULL(AttachPersonInfo.DrugRequestRow_SummaMinZdrav, 0) as Attach_SummaMinZdrav,
				ISNULL(AttachPersonInfo.DrugRequestRow_SummaOnkoDisp, 0) as Attach_SummaOnkoDisp,
				ISNULL(AttachPersonInfo.DrugRequestRow_SummaOnkoGemat, 0) as Attach_SummaOnkoGemat,
				ISNULL(AttachPersonInfo.DrugRequestRow_SummaPsycho, 0) as Attach_SummaPsycho,
				ISNULL(AttachPersonInfo.DrugRequestRow_SummaRevmat, 0) as Attach_SummaRevmat,
				ISNULL(AttachPersonInfo.DrugRequestRow_SummaOtherLpu, 0) as Attach_SummaOtherLpu
			from RequestPersonInfo with(nolock)
				left join v_Lpu Lpu with (nolock) on RequestPersonInfo.Lpu_id = Lpu.Lpu_id
				left join AttachPersonInfo with(nolock) on AttachPersonInfo.Lpu_id = RequestPersonInfo.Lpu_id
				left join AttachPersonCount with(nolock) on AttachPersonCount.Lpu_id = RequestPersonInfo.Lpu_id
				" . $join_str . "
			order by Lpu_Name
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
				select distinct
					Person_id
				from (
					select
						DRP.Person_id
					from DrugRequestPerson DRP WITH (NOLOCK)
					where (1 = 1)
						and Lpu_id = :Lpu_id
						and MedPersonal_id = :MedPersonal_id
						and DrugRequestPeriod_id = :DrugRequestPeriod_id
					union all
					select
						PC.Person_id
					from v_PersonCard_all PC WITH (NOLOCK)
						inner join v_LpuRegion LR WITH (NOLOCK) on LR.LpuRegion_id = PC.LpuRegion_id
						inner join v_MedStaffRegion MSR WITH (NOLOCK) on MSR.LpuRegion_id = LR.LpuRegion_id
							and MSR.MedPersonal_id = :MedPersonal_id
					where (1 = 1)
						and PC.LpuAttachType_id = 1
						and PC.PersonCard_begDate <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
						and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
				) Person
			)

			select
				ISNULL(PS.Person_id, 0) as Person_id,
				RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' '+ RTRIM(ISNULL(PS.Person_Secname, '')) as Person_Fio,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_Birthday,
				ISNULL(DPM.DrugProtoMnn_Code, Drug.Drug_Code) as DrugRequestRow_Code,
				ISNULL(DPM.DrugProtoMnn_Name, Drug.Drug_Name) as DrugRequestRow_Name,
				RTRIM(ISNULL(Lpu.Lpu_Nick, '')) as  Lpu_Name,
				RTRIM(ISNULL(MP.Person_Fio, '')) as  MedPersonal_Fio,
				SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)) as DrugRequestRow_Kolvo,
				ROUND(SUM(DRR.DrugRequestRow_Summa) / SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)), 2) as DrugRequestRow_Price,
				SUM(ISNULL(DRR.DrugRequestRow_Summa, 0)) as DrugRequestRow_Summa
			from PersonList PL WITH (NOLOCK)
				inner join v_PersonState PS WITH (NOLOCK) on PS.Person_id = PL.Person_id
				left join DrugRequestRow DRR WITH (NOLOCK) on DRR.Person_id = PL.Person_id
					and DRR.DrugRequestType_id = :DrugRequestType_id
				left join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id
				left join v_MedPersonal MP WITH (NOLOCK) on MP.MedPersonal_id = DR.MedPersonal_id
					and MP.Lpu_id = DR.Lpu_id
				left join v_Lpu Lpu WITH (NOLOCK) on Lpu.Lpu_id = DR.Lpu_id
				left join DrugProtoMnn DPM WITH (NOLOCK) on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
				left join Drug WITH (NOLOCK) on Drug.Drug_id = DRR.Drug_id
				left join DrugRequestPeriod DRP2 WITH (NOLOCK) on DRP2.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
			where " . $filter . "
				and (DRR.DrugRequestRow_id is null
					or (DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
					and (DRR.DrugRequestRow_delDT is null or DRR.DrugRequestRow_delDT > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
					and ISNULL(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
				)
			group by
				PS.Person_id,
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
			order by
				Person_Fio,
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
			select
				ISNULL(MP.MedPersonal_id, 0) as MedPersonal_id,
				RTRIM(ISNULL(MP.Person_Fio, '-')) as MedPersonal_Fio,
				ISNULL(PS.Person_id, 0) as Person_id,
				RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' '+ RTRIM(ISNULL(PS.Person_Secname, '')) as Person_Fio,
				RTRIM(COALESCE(DPM.DrugProtoMnn_Code, Drug.Drug_Code, '')) as DrugRequestRow_Code,
				RTRIM(COALESCE(DPM.DrugProtoMnn_Name, Drug.Drug_Name, '')) as DrugRequestRow_Name,
				SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)) as DrugRequestRow_Kolvo,
				ROUND(SUM(DRR.DrugRequestRow_Summa) / SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)), 2) as DrugRequestRow_Price,
				SUM(DRR.DrugRequestRow_Summa) as DrugRequestRow_Summa
			from DrugRequestRow DRR WITH (NOLOCK)
				inner join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id
					and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
				left join DrugProtoMnn DPM WITH (NOLOCK) on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
				left join Drug WITH (NOLOCK) on Drug.Drug_id = DRR.Drug_id
				left join v_MedPersonal MP WITH (NOLOCK) on MP.MedPersonal_id = DR.MedPersonal_id
					and MP.Lpu_id = DR.Lpu_id
				left join LpuSection LS WITH (NOLOCK) on LS.LpuSection_id = DR.LpuSection_id
				left join v_PersonState PS WITH (NOLOCK) on PS.Person_id = DRR.Person_id
			where " . $filter . "
				and DRR.DrugRequestType_id = :DrugRequestType_id
				and (DRR.DrugRequestRow_delDT is null or DRR.DrugRequestRow_delDT > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
				and ISNULL(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
			group by
				MP.MedPersonal_id,
				MP.Person_Fio,
				PS.Person_id,
				PS.Person_Surname,
				PS.Person_Firname,
				PS.Person_Secname,
				DPM.DrugProtoMnn_Code,
				DPM.DrugProtoMnn_Name,
				Drug.Drug_Code,
				Drug.Drug_Name
			order by
				MedPersonal_Fio,
				Person_Fio
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
			select
				ISNULL(DPM.DrugProtoMnn_Code, Drug.Drug_Code) as DrugRequestRow_Code,
				ISNULL(DPM.DrugProtoMnn_Name, Drug.Drug_Name) as DrugRequestRow_Name,
				SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)) as DrugRequestRow_Kolvo,
				ROUND(SUM(DRR.DrugRequestRow_Summa) / SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)), 2) as DrugRequestRow_Price,
				SUM(DRR.DrugRequestRow_Summa) as DrugRequestRow_Summa
			from DrugRequestRow DRR WITH (NOLOCK)
				inner join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id
				inner join DrugRequestPeriod DRP WITH (NOLOCK) on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
					and DRP.DrugRequestPeriod_id = :DrugRequestPeriod_id
				left join DrugProtoMnn DPM WITH (NOLOCK) on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
				left join Drug WITH (NOLOCK) on Drug.Drug_id = DRR.Drug_id
					-- and Drug.DrugClass_id = 1
				outer apply (
					select top 1
						PC.Lpu_id
					from
						v_PersonCard_all PC WITH (NOLOCK)
					where
						PC.Person_id = DRR.Person_id
						and (PC.Lpu_id = :Lpu_id or NULLIF(:Lpu_id, 0) is null)
						and PC.LpuAttachType_id = 1
						and PC.PersonCard_begDate <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
						and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
					order by PC.PersonCard_begDate desc
				) AttLpu
				outer apply (
					select top 1
						DrugRequest.DrugRequest_id
					from v_DrugRequest DrugRequest WITH (NOLOCK)
					where (DrugRequest.Lpu_id = :Lpu_id or NULLIF(:Lpu_id, 0) is null)
						and DrugRequest.DrugRequest_id = DR.DrugRequest_id
				) OurDrugRequest
			where (1 = 1)
				-- Медикамент указан
				and (DPM.DrugProtoMnn_id is not null or Drug.Drug_id is not null)
				-- Тип заявки
				and DRR.DrugRequestType_id = :DrugRequestType_id
				-- Заявка не удалена либо удалена позже даты актуальности
				and (DRR.DrugRequestRow_delDT is null or DRR.DrugRequestRow_delDT > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
				-- Заявка актуальна на выбранную дату
				and ISNULL(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
				-- Пациент имеет прикрепление к ЛПУ и является льготником или резерв из 'своей' заявки
				and (AttLpu.Lpu_id is not null or (DRR.Person_id is null and OurDrugRequest.DrugRequest_id is not null))
			group by
				DPM.DrugProtoMnn_id,
				DPM.DrugProtoMnn_Code,
				DPM.DrugProtoMnn_Name,
				Drug.Drug_id,
				Drug.Drug_Code,
				Drug.Drug_Name
			order by DrugRequestRow_Name
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
			select
				ISNULL(PS.Person_id, 0) as Person_id,
				RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' '+ RTRIM(ISNULL(PS.Person_Secname, '')) as Person_Fio,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				RTRIM(COALESCE(DPM.DrugProtoMnn_Code, Drug.Drug_Code, '')) as DrugRequestRow_Code,
				RTRIM(COALESCE(DPM.DrugProtoMnn_Name, Drug.Drug_Name, '')) as DrugRequestRow_Name,
				SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)) as DrugRequestRow_Kolvo,
				ROUND(SUM(DRR.DrugRequestRow_Summa) / SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)), 2) as DrugRequestRow_Price,
				SUM(DRR.DrugRequestRow_Summa) as DrugRequestRow_Summa,
				AttLpu.AttachLpu_Name,
				RTRIM(UAddr.Address_Address) as UAddress_Name
			from DrugRequestRow DRR WITH (NOLOCK)
				inner join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id
					and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
				left join DrugProtoMnn DPM WITH (NOLOCK) on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
				left join Drug WITH (NOLOCK) on Drug.Drug_id = DRR.Drug_id
				left join LpuSection LS WITH (NOLOCK) on LS.LpuSection_id = DR.LpuSection_id
				left join v_PersonState PS WITH (NOLOCK) on PS.Person_id = DRR.Person_id
				left join [Address] UAddr WITH (NOLOCK) on UAddr.Address_id = PS.UAddress_id
				outer apply (
					select top 1
						RTRIM(Lpu.Lpu_Nick) as AttachLpu_Name
					from v_PersonCard_all PC WITH (NOLOCK)
						inner join v_Lpu Lpu WITH (NOLOCK) on Lpu.Lpu_id = PC.Lpu_id
					where PC.LpuAttachType_id = 1
						and PC.Person_id = DRR.Person_id
						and PC.PersonCard_begDate <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
						and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
					order by PC.PersonCard_begDate desc
				) AttLpu
			where " . $filter . "
				and DRR.DrugRequestType_id = :DrugRequestType_id
				and (DRR.DrugRequestRow_delDT is null or DRR.DrugRequestRow_delDT > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
				and ISNULL(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
			group by
				PS.Person_id,
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
			order by
				PS.Person_Surname,
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
				$person_refuse_filter .= " and ISNULL(PR.PersonRefuse_IsRefuse, 1) = 1";
				$person_refuse_join = "
					left join v_PersonRefuse PR with(nolock) on PR.Person_id = PP.Person_id
						and PR.PersonRefuse_Year = YEAR(DRP.DrugRequestPeriod_begDate)
						and PR.PersonRefuse_IsRefuse = 2 
				";
				$privilege_code_filter = " and PT.ReceptFinance_id = 1 and isnumeric(PT.PrivilegeType_Code) = 1";
			break;

			case 2:
				$params['Koef'] = $options['koef_reg_lgot'];
				$params['Normativ'] = $options['normativ_reg_lgot'];
				$privilege_code_filter = " and PT.ReceptFinance_id = 2 and isnumeric(PT.PrivilegeType_Code) = 1";

				$join_str .= "left join YoungChild with(nolock) on YoungChild.Lpu_id = RequestPersonInfo.Lpu_id";

				$select_fields .= "ISNULL(YoungChild.YoungChild_Count, 0) as YoungChild_Count, ";
				$select_fields .= "ISNULL(YoungChild.YoungChild_SummaLimit, 0) as YoungChild_SummaLimit, ";

				$fed_lgot_check_join = "
					outer apply (
						select top 1 1 as IsFedLgot
						from v_PersonPrivilege PP2 WITH (NOLOCK)
							inner join PrivilegeType PT2 WITH (NOLOCK) on PT2.PrivilegeType_id = PP2.PrivilegeType_id
								and PT2.ReceptFinance_id = 1 and isnumeric(PT2.PrivilegeType_Code) = 1
						where PP2.Person_id = PC.Person_id
							and PP2.PersonPrivilege_begDate <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
							and (PP2.PersonPrivilege_endDate is null or PP2.PersonPrivilege_endDate >= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
					) FedLgot
				";
				$fed_lgot_check_filter = " and FedLgot.IsFedLgot is null";

				$young_child_set = "
					YoungChild(
						Lpu_id,
						YoungChild_Count,
						YoungChild_SummaLimit
					) as (
						select
							DR.Lpu_id,
							SUM(DR.DrugRequest_YoungChildCount) as YoungChild_Count,
							SUM(DR.DrugRequest_YoungChildCount) * :Normativ * 3 * :Koef as YoungChild_SummaLimit
						from v_DrugRequest DR WITH (NOLOCK)
							inner join DrugRequestPeriod DRP WITH (NOLOCK) on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
								and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
						where (DR.Lpu_id = :Lpu_id or NULLIF(:Lpu_id, 0) is null)
						group by
							DR.Lpu_id
					),
				";
			break;
		}

		$query = "
			with " . $young_child_set . "
			RequestPersonInfo(
				Lpu_id,
				Lpu_Name,
				DrugRequestRow_PersonCount,
				DrugRequestRow_SummaLimit
			) as (
				select
					Lpu.Lpu_id,
					RTRIM(LTRIM(Lpu.Lpu_Nick)) as Lpu_Name,
					count(distinct DRR.Person_id) as DrugRequestRow_PersonCount,
					count(distinct DRR.Person_id) * :Normativ * 3 * :Koef as DrugRequestRow_SummaLimit
				from DrugRequestRow DRR WITH (NOLOCK)
					inner join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id
						and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
					inner join v_Lpu Lpu WITH (NOLOCK) on Lpu.Lpu_id = DR.Lpu_id
				where (DR.Lpu_id = :Lpu_id or NULLIF(:Lpu_id, 0) is null)
					and DRR.Person_id is not null
					and DRR.DrugRequestType_id = :DrugRequestType_id
					and (DRR.DrugRequestRow_delDT is null or DRR.DrugRequestRow_delDT > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
					and ISNULL(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
				group by
					Lpu.Lpu_id,
					Lpu.Lpu_Nick
			),
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
				select
					Lpu_id,
					Lpu_Name,
					SUM(LpuRequest_Summa) as DrugRequestRow_SummaPerson,
					LpuReserve_Summa,
					SUM(OtherLpuRequest_Summa) as DrugRequestRow_SummaOtherLpu,
					SUM(MinZdravRequest_Summa) as DrugRequestRow_SummaMinZdrav,
					SUM(OnkoDispRequest_Summa) as DrugRequestRow_SummaOnkoDisp,
					SUM(OnkoGematRequest_Summa) as DrugRequestRow_SummaOnkoGemat
				from (
					select
						PC.Person_id,
						Lpu.Lpu_id,
						RTRIM(LTRIM(Lpu.Lpu_Nick)) as Lpu_Name,
						ISNULL(LpuRequest.DrugRequestRow_Summa, 0) as LpuRequest_Summa,
						ISNULL(LpuReserve.DrugRequestRow_Summa, 0) as LpuReserve_Summa,
						ISNULL(OtherLpuRequest.DrugRequestRow_Summa, 0) as OtherLpuRequest_Summa,
						ISNULL(MinZdravRequest.DrugRequestRow_Summa, 0) as MinZdravRequest_Summa,
						ISNULL(OnkoDispRequest.DrugRequestRow_Summa, 0) as OnkoDispRequest_Summa,
						ISNULL(OnkoGematRequest.DrugRequestRow_Summa, 0) as OnkoGematRequest_Summa
					from v_PersonCard_all PC WITH (NOLOCK)
						inner join DrugRequestRow DRR with (nolock) on DRR.Person_id = PC.Person_id
							and DRR.DrugRequestType_id = :DrugRequestType_id
						inner join DrugRequest DR with (nolock) on DR.DrugRequest_id = DRR.DrugRequest_id
						inner join DrugRequestPeriod DRP with (nolock) on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
							and DRP.DrugRequestPeriod_id = :DrugRequestPeriod_id
						inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = PC.Lpu_id
						outer apply (
							select
								SUM(DRR2.DrugRequestRow_Summa) as DrugRequestRow_Summa
							from
								DrugRequestRow DRR2 WITH (NOLOCK)
								inner join DrugRequest DR2 WITH (NOLOCK) on DR2.DrugRequest_id = DRR2.DrugRequest_id
									and DR2.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
								-- inner join DrugProtoMnn DPM WITH (NOLOCK) on DPM.DrugProtoMnn_id = DRR2.DrugProtoMnn_id
							where (1 = 1)
								and DR2.Lpu_id = PC.Lpu_id
								and DRR2.Person_id = PC.Person_id
								and DRR2.DrugRequestType_id = DRR.DrugRequestType_id
								and (DRR2.DrugRequestRow_delDT is null or DRR2.DrugRequestRow_delDT > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR2.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
								and ISNULL(DRR2.DrugRequestRow_updDT, DRR2.DrugRequestRow_insDT) <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR2.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
						) LpuRequest
						outer apply (
							select
								SUM(DRR2.DrugRequestRow_Summa) as DrugRequestRow_Summa
							from
								DrugRequestRow DRR2 WITH (NOLOCK)
								inner join DrugRequest DR2 WITH (NOLOCK) on DR2.DrugRequest_id = DRR2.DrugRequest_id
									and DR2.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
							where (1 = 1)
								and DR2.Lpu_id = PC.Lpu_id
								and DRR2.Person_id is null
								and DRR2.DrugRequestType_id = DRR.DrugRequestType_id
								and (DRR2.DrugRequestRow_delDT is null or DRR2.DrugRequestRow_delDT > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR2.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
								and ISNULL(DRR2.DrugRequestRow_updDT, DRR2.DrugRequestRow_insDT) <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR2.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
						) LpuReserve
						outer apply (
							select
								SUM(DRR2.DrugRequestRow_Summa) as DrugRequestRow_Summa
							from
								DrugRequestRow DRR2 WITH (NOLOCK)
								inner join DrugRequest DR2 WITH (NOLOCK) on DR2.DrugRequest_id = DRR2.DrugRequest_id
									and DR2.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
							where (1 = 1)
								and DR2.Lpu_id <> PC.Lpu_id
								and DR2.Lpu_id not in (select id from MinZdravList())
								and DR2.Lpu_id not in (select id from OnkoList())
								and DR2.LpuSection_id <> 337
								and DRR2.Person_id = PC.Person_id
								and DRR2.DrugRequestType_id = DRR.DrugRequestType_id
								and (DRR2.DrugRequestRow_delDT is null or DRR2.DrugRequestRow_delDT > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR2.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
								and ISNULL(DRR2.DrugRequestRow_updDT, DRR2.DrugRequestRow_insDT) <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR2.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate()() end)
						) OtherLpuRequest
						outer apply (
							select
								SUM(DRR2.DrugRequestRow_Summa) as DrugRequestRow_Summa
							from
								DrugRequestRow DRR2 WITH (NOLOCK)
								inner join DrugRequest DR2 WITH (NOLOCK) on DR2.DrugRequest_id = DRR2.DrugRequest_id
									and DR2.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
							where (1 = 1)
								and DR2.Lpu_id in (select id from MinZdravList())
								and DRR2.Person_id = PC.Person_id
								and DRR2.DrugRequestType_id = DRR.DrugRequestType_id
								and (DRR2.DrugRequestRow_delDT is null or DRR2.DrugRequestRow_delDT > ISNULL(CAST(:DrugRequestRow_actDate as datetime), dbo.tzGetDate()))
								and ISNULL(DRR2.DrugRequestRow_updDT, DRR2.DrugRequestRow_insDT) <= ISNULL(CAST(:DrugRequestRow_actDate as datetime), dbo.tzGetDate())
						) MinZdravRequest
						outer apply (
							select
								SUM(DRR2.DrugRequestRow_Summa) as DrugRequestRow_Summa
							from
								DrugRequestRow DRR2 WITH (NOLOCK)
								inner join DrugRequest DR2 WITH (NOLOCK) on DR2.DrugRequest_id = DRR2.DrugRequest_id
									and DR2.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
							where (1 = 1)
								and DR2.Lpu_id in (select id from OnkoList())
								and DRR2.Person_id = PC.Person_id
								and DRR2.DrugRequestType_id = DRR.DrugRequestType_id
								and (DRR2.DrugRequestRow_delDT is null or DRR2.DrugRequestRow_delDT > ISNULL(CAST(:DrugRequestRow_actDate as datetime), dbo.tzGetDate()))
								and ISNULL(DRR2.DrugRequestRow_updDT, DRR2.DrugRequestRow_insDT) <= ISNULL(CAST(:DrugRequestRow_actDate as datetime), dbo.tzGetDate())
						) OnkoDispRequest
						outer apply (
							select
								SUM(DRR2.DrugRequestRow_Summa) as DrugRequestRow_Summa
							from
								DrugRequestRow DRR2 WITH (NOLOCK)
								inner join DrugRequest DR2 WITH (NOLOCK) on DR2.DrugRequest_id = DRR2.DrugRequest_id
									and DR2.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
							where (1 = 1)
								and DR2.LpuSection_id = 337
								and DRR2.Person_id = PC.Person_id
								and DRR2.DrugRequestType_id = DRR.DrugRequestType_id
								and (DRR2.DrugRequestRow_delDT is null or DRR2.DrugRequestRow_delDT > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR2.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
								and ISNULL(DRR2.DrugRequestRow_updDT, DRR2.DrugRequestRow_insDT) <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR2.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
						) OnkoGematRequest
					where (PC.Lpu_id = :Lpu_id or NULLIF(:Lpu_id, 0) is null)
						and PC.LpuAttachType_id = 1
						and PC.PersonCard_begDate <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
						and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
					group by
						Lpu.Lpu_id,
						Lpu.Lpu_Nick,
						PC.Person_id,
						LpuRequest.DrugRequestRow_Summa,
						LpuReserve.DrugRequestRow_Summa,
						OtherLpuRequest.DrugRequestRow_Summa,
						MinZdravRequest.DrugRequestRow_Summa,
						OnkoDispRequest.DrugRequestRow_Summa,
						OnkoGematRequest.DrugRequestRow_Summa
				) DrugRequest
				group by
					Lpu_id,
					Lpu_Name,
					LpuReserve_Summa
			),
			AttachPersonCount(
				Lpu_id,
				AttachPerson_Count,
				AttachPerson_SummaLimit
			) as (
				select
					PC.Lpu_id,
					COUNT(DISTINCT PC.Person_id) as AttachPerson_Count,
					COUNT(DISTINCT PC.Person_id) * :Normativ * 3 * :Koef as AttachPerson_SummaLimit
				from v_PersonCard_all PC WITH (NOLOCK)
					inner join DrugRequestPeriod DRP WITH (NOLOCK) on DRP.DrugRequestPeriod_id = :DrugRequestPeriod_id
					outer apply (
						select top 1
							1 as IsLgot
						from
							PersonPrivilege PP WITH (NOLOCK)
							inner join PrivilegeType PT WITH (NOLOCK) on PT.PrivilegeType_id = PP.PrivilegeType_id
								" . $privilege_code_filter . "
							" . $person_refuse_join . "
							" . $fed_lgot_check_join . "
						where PP.Person_id = PC.Person_id
							and PP.PersonPrivilege_begDate <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
							and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate >= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
							" . $person_refuse_filter . "
							" . $fed_lgot_check_filter . "
					) Lgot
				where (1 = 1)
					and (PC.Lpu_id = :Lpu_id or NULLIF(:Lpu_id, 0) is null)
					and PC.LpuAttachType_id = 1
					and PC.PersonCard_begDate <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
					and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
					and Lgot.IsLgot = 1
				group by
					PC.Lpu_id
			)

			select
				" . $select_fields . "
				ISNULL(RequestPersonInfo.Lpu_id, 0) as Lpu_id,
				ISNULL(RequestPersonInfo.Lpu_Name, '-') as Lpu_Name,
				ISNULL(RequestPersonInfo.DrugRequestRow_PersonCount, 0) as Request_PersonCount,
				ISNULL(RequestPersonInfo.DrugRequestRow_SummaLimit, 0) as Request_SummaLimit,
				ISNULL(AttachPersonCount.AttachPerson_Count, 0) as Attach_PersonCount,
				ISNULL(AttachPersonCount.AttachPerson_SummaLimit, 0) as Attach_SummaLimit,
				ISNULL(AttachPersonInfo.DrugRequestRow_SummaPerson, 0) as Attach_SummaPerson,
				ISNULL(AttachPersonInfo.DrugRequestRow_SummaReserve, 0) as Attach_SummaReserve,
				ISNULL(AttachPersonInfo.DrugRequestRow_SummaMinZdrav, 0) as Attach_SummaMinZdrav,
				ISNULL(AttachPersonInfo.DrugRequestRow_SummaOnkoDisp, 0) as Attach_SummaOnkoDisp,
				ISNULL(AttachPersonInfo.DrugRequestRow_SummaOnkoGemat, 0) as Attach_SummaOnkoGemat,
				ISNULL(AttachPersonInfo.DrugRequestRow_SummaOtherLpu, 0) as Attach_SummaOtherLpu
			from RequestPersonInfo WITH (NOLOCK)
				left join AttachPersonInfo WITH (NOLOCK) on AttachPersonInfo.Lpu_id = RequestPersonInfo.Lpu_id
				left join AttachPersonCount WITH (NOLOCK) on AttachPersonCount.Lpu_id = RequestPersonInfo.Lpu_id
				" . $join_str . "
			order by Lpu_Name
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
			select
				DPM.DrugProtoMnn_id,
				RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' '+ RTRIM(ISNULL(PS.Person_Secname, '')) as Person_Fio,
				RTRIM(DPM.DrugProtoMnn_Name) as DrugProtoMnn_Name,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				RTRIM(ISNULL(AttLpu.Lpu_Nick, '')) as AttachLpu_Name,
				RTRIM(ISNULL(Lpu.Lpu_Nick, '')) as Lpu_Name,
				SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)) as DrugRequestRow_Kolvo,
				ROUND(SUM(DRR.DrugRequestRow_Summa) / SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)), 2) as DrugRequestRow_Price,
				SUM(DRR.DrugRequestRow_Summa) as DrugRequestRow_Summa
			from DrugRequestRow DRR WITH (NOLOCK)
				inner join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id
					and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
				inner join DrugRequestPeriod DRP WITH (NOLOCK) on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
				inner join v_Lpu Lpu WITH (NOLOCK) on Lpu.Lpu_id = DR.Lpu_id
				inner join v_MedPersonal MP WITH (NOLOCK) on MP.MedPersonal_id = DR.MedPersonal_id
					and MP.Lpu_id = DR.Lpu_id
				inner join v_PersonState PS WITH (NOLOCK) on PS.Person_id = DRR.Person_id
				inner join DrugProtoMnn DPM WITH (NOLOCK) on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
				outer apply (
					select top 1
						PC.Lpu_id,
						Lpu2.Lpu_Nick
					from
						v_PersonCard_all PC WITH (NOLOCK)
						inner join v_Lpu Lpu2 WITH (NOLOCK) on Lpu2.Lpu_id = PC.Lpu_id
					where
						PC.Person_id = DRR.Person_id
						and (PC.Lpu_id = :Lpu_id or NULLIF(:Lpu_id, 0) is null)
						and PC.LpuAttachType_id = 1
						and PC.PersonCard_begDate <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
						and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
				) AttLpu
			where (1 = 1)
				and NULLIF(DR.Lpu_id, :Lpu_id) is not null
				and DR.Lpu_id not in (select id from MinZdravList())
				and DR.Lpu_id not in (select id from OnkoList())
				and DR.LpuSection_id <> 337
				and AttLpu.Lpu_id is not null
				and DRR.DrugRequestType_id = :DrugRequestType_id
				and (DRR.DrugRequestRow_delDT is null or DRR.DrugRequestRow_delDT > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
				and ISNULL(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
			group by
				DPM.DrugProtoMnn_id,
				PS.Person_Surname,
				PS.Person_Firname,
				PS.Person_Secname,
				DPM.DrugProtoMnn_Name,
				MP.Person_Fio,
				Lpu.Lpu_Nick,
				AttLpu.Lpu_Nick
			order by
				DrugProtoMnn_Name,
				Person_Fio
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
			select
				ISNULL(DPM.DrugProtoMnn_id, Drug.Drug_id) as DrugProtoMnn_id,
				RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' '+ RTRIM(ISNULL(PS.Person_Secname, '')) as Person_Fio,
				RTRIM(COALESCE(DPM.DrugProtoMnn_Name, Drug.Drug_Name, '')) as DrugProtoMnn_Name,
				'' as MedPersonal_Fio,
				RTRIM(ISNULL(AttLpu.Lpu_Nick, '')) as AttachLpu_Name,
				RTRIM(ISNULL(Lpu.Lpu_Nick, '')) as Lpu_Name,
				SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)) as DrugRequestRow_Kolvo,
				ROUND(SUM(DRR.DrugRequestRow_Summa) / SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)), 2) as DrugRequestRow_Price,
				SUM(DRR.DrugRequestRow_Summa) as DrugRequestRow_Summa
			from DrugRequestRow DRR WITH (NOLOCK)
				inner join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id
					and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
				inner join DrugRequestPeriod DRP WITH (NOLOCK) on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
				inner join v_Lpu Lpu WITH (NOLOCK) on Lpu.Lpu_id = DR.Lpu_id
				inner join v_PersonState PS WITH (NOLOCK) on PS.Person_id = DRR.Person_id
				left join DrugProtoMnn DPM WITH (NOLOCK) on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
				left join Drug WITH (NOLOCK) on Drug.Drug_id = DRR.Drug_id
				outer apply (
					select top 1
						PC.Lpu_id,
						Lpu2.Lpu_Nick
					from
						v_PersonCard_all PC WITH (NOLOCK)
						inner join v_Lpu Lpu2 with (nolock) on Lpu2.Lpu_id = PC.Lpu_id
					where
						PC.Person_id = DRR.Person_id
						and (PC.Lpu_id = :Lpu_id or NULLIF(:Lpu_id, 0) is null)
						and PC.LpuAttachType_id = 1
						and PC.PersonCard_begDate <= ISNULL(CAST(:DrugRequestRow_actDate as datetime), dbo.tzGetDate())
						and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > ISNULL(CAST(:DrugRequestRow_actDate as datetime), dbo.tzGetDate()))
				) AttLpu
			where (1 = 1)
				and DR.Lpu_id in (select id from MinZdravList())
				and AttLpu.Lpu_id is not null
				and DRR.DrugRequestType_id = :DrugRequestType_id
				and (DRR.DrugRequestRow_delDT is null or DRR.DrugRequestRow_delDT > ISNULL(CAST(:DrugRequestRow_actDate as datetime), dbo.tzGetDate()))
				and ISNULL(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= ISNULL(CAST(:DrugRequestRow_actDate as datetime), dbo.tzGetDate())
			group by
				DPM.DrugProtoMnn_id,
				Drug.Drug_id,
				PS.Person_Surname,
				PS.Person_Firname,
				PS.Person_Secname,
				DPM.DrugProtoMnn_Name,
				Drug.Drug_Name,
				Lpu.Lpu_Nick,
				AttLpu.Lpu_Nick
			order by
				DrugProtoMnn_Name,
				Person_Fio
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
			select
				DPM.DrugProtoMnn_id,
				RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' '+ RTRIM(ISNULL(PS.Person_Secname, '')) as Person_Fio,
				RTRIM(DPM.DrugProtoMnn_Name) as DrugProtoMnn_Name,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				RTRIM(ISNULL(AttLpu.Lpu_Nick, '')) as AttachLpu_Name,
				RTRIM(ISNULL(Lpu.Lpu_Nick, '')) as Lpu_Name,
				SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)) as DrugRequestRow_Kolvo,
				ROUND(SUM(DRR.DrugRequestRow_Summa) / SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)), 2) as DrugRequestRow_Price,
				SUM(DRR.DrugRequestRow_Summa) as DrugRequestRow_Summa
			from DrugRequestRow DRR WITH (NOLOCK)
				inner join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id
					and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
				inner join DrugRequestPeriod DRP WITH (NOLOCK) on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
				inner join v_Lpu Lpu WITH (NOLOCK) on Lpu.Lpu_id = DR.Lpu_id
				inner join v_MedPersonal MP WITH (NOLOCK) on MP.MedPersonal_id = DR.MedPersonal_id
					and MP.Lpu_id = DR.Lpu_id
				inner join v_PersonState PS WITH (NOLOCK) on PS.Person_id = DRR.Person_id
				inner join DrugProtoMnn DPM WITH (NOLOCK) on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
				outer apply (
					select top 1
						PC.Lpu_id,
						Lpu2.Lpu_Nick
					from
						v_PersonCard_all PC WITH (NOLOCK)
						inner join v_Lpu Lpu2 WITH (NOLOCK) on Lpu2.Lpu_id = PC.Lpu_id
					where
						PC.Person_id = DRR.Person_id
						and (PC.Lpu_id = :Lpu_id or NULLIF(:Lpu_id, 0) is null)
						and PC.LpuAttachType_id = 1
						and PC.PersonCard_begDate <= ISNULL(CAST(:DrugRequestRow_actDate as datetime), dbo.tzGetDate())
						and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > ISNULL(CAST(:DrugRequestRow_actDate as datetime), dbo.tzGetDate()))
				) AttLpu
			where (1 = 1)
				and DR.Lpu_id in (select id from OnkoList())
				and AttLpu.Lpu_id is not null
				and DRR.DrugRequestType_id = :DrugRequestType_id
				and (DRR.DrugRequestRow_delDT is null or DRR.DrugRequestRow_delDT > ISNULL(CAST(:DrugRequestRow_actDate as datetime), dbo.tzGetDate()))
				and ISNULL(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= ISNULL(CAST(:DrugRequestRow_actDate as datetime), dbo.tzGetDate())
			group by
				DPM.DrugProtoMnn_id,
				PS.Person_Surname,
				PS.Person_Firname,
				PS.Person_Secname,
				DPM.DrugProtoMnn_Name,
				MP.Person_Fio,
				Lpu.Lpu_Nick,
				AttLpu.Lpu_Nick
			order by
				DrugProtoMnn_Name,
				Person_Fio
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
			select
				DPM.DrugProtoMnn_id,
				RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' '+ RTRIM(ISNULL(PS.Person_Secname, '')) as Person_Fio,
				RTRIM(DPM.DrugProtoMnn_Name) as DrugProtoMnn_Name,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				RTRIM(ISNULL(AttLpu.Lpu_Nick, '')) as AttachLpu_Name,
				'ОНКОГЕМАТОЛОГИЯ' as Lpu_Name,
				SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)) as DrugRequestRow_Kolvo,
				ROUND(SUM(DRR.DrugRequestRow_Summa) / SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)), 2) as DrugRequestRow_Price,
				SUM(DRR.DrugRequestRow_Summa) as DrugRequestRow_Summa
			from DrugRequestRow DRR WITH (NOLOCK)
				inner join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id
					and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
				inner join DrugRequestPeriod DRP WITH (NOLOCK) on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
				inner join v_Lpu Lpu WITH (NOLOCK) on Lpu.Lpu_id = DR.Lpu_id
				inner join v_MedPersonal MP WITH (NOLOCK) on MP.MedPersonal_id = DR.MedPersonal_id
					and MP.Lpu_id = DR.Lpu_id
				inner join v_PersonState PS WITH (NOLOCK) on PS.Person_id = DRR.Person_id
				inner join DrugProtoMnn DPM WITH (NOLOCK) on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
				outer apply (
					select top 1
						PC.Lpu_id,
						Lpu2.Lpu_Nick
					from
						v_PersonCard_all PC WITH (NOLOCK)
						inner join v_Lpu Lpu2 WITH (NOLOCK) on Lpu2.Lpu_id = PC.Lpu_id
					where
						PC.Person_id = DRR.Person_id
						and (PC.Lpu_id = :Lpu_id or NULLIF(:Lpu_id, 0) is null)
						and PC.LpuAttachType_id = 1
						and PC.PersonCard_begDate <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
						and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
				) AttLpu
			where (1 = 1)
				and DR.LpuSection_id = 337
				and AttLpu.Lpu_id is not null
				and DRR.DrugRequestType_id = :DrugRequestType_id
				and (DRR.DrugRequestRow_delDT is null or DRR.DrugRequestRow_delDT > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
				and ISNULL(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
			group by
				DPM.DrugProtoMnn_id,
				PS.Person_Surname,
				PS.Person_Firname,
				PS.Person_Secname,
				DPM.DrugProtoMnn_Name,
				MP.Person_Fio,
				Lpu.Lpu_Nick,
				AttLpu.Lpu_Nick
			order by
				DrugProtoMnn_Name,
				Person_Fio
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
				select
					DRR.Person_id,
					RTRIM(PS.Person_SurName) + ' ' + RTRIM(PS.Person_FirName) + ' ' + RTRIM(PS.Person_SecName) as Person_Fio,
					convert(varchar(10), PS.Person_BirthDay, 104) as Person_Birthday,
					ISNULL(UAddr.Address_Address, '') as UAddress_Name,
					DRR.DrugRequestRow_id,
					RTRIM(Lpu.Lpu_Nick) as Lpu_Nick,
					ISNULL(LS.LpuSection_Name, '') as LpuSection_Name,
					ISNULL(MP.Person_Fio, '') as MedPersonal_Fio,
					ISNULL(DPM.DrugProtoMnn_Code, Drug.Drug_Code) as DrugRequestRow_Code,
					ISNULL(DPM.DrugProtoMnn_Name, Drug.Drug_Name) as DrugRequestRow_Name,
					ISNULL(DRR.DrugRequestRow_Kolvo, 0) as DrugRequestRow_Kolvo,
					case when ISNULL(DRR.DrugRequestRow_Kolvo, 0) > 0 then ROUND(ISNULL(DRR.DrugRequestRow_Summa, 0) / ISNULL(DRR.DrugRequestRow_Kolvo, 0), 2) else 0 end as DrugRequestRow_Price,
					ISNULL(DRR.DrugRequestRow_Summa, 0) as DrugRequestRow_Summa,
					ISNULL(ERLpu.Lpu_Nick, '') as ER_Lpu_Nick,
					ISNULL(ERLS.LpuSection_Name, '') as ER_LpuSection_Name,
					ISNULL(ERMP.Person_Fio, '') as ER_MedPersonal_Fio,
					case
						when COALESCE(CAST(DrugData.Drug_DoseUEQ as float), CAST(Drug.Drug_DoseUEQ as float), 0) > 0
							and COALESCE(DrugData.DrugFormGroup_id, DrugForm.DrugFormGroup_id, 0) = ISNULL(ERDF.DrugFormGroup_id, 0)
							then ISNULL(CAST(ERD.Drug_DoseUEQ as float), 0) / COALESCE(CAST(DrugData.Drug_DoseUEQ as float), CAST(Drug.Drug_DoseUEQ as float)) * ISNULL(ER.EvnRecept_Kolvo, 0)
						else 0
					end as EvnRecept_Kolvo,
					ISNULL(ER_DrugData.DrugState_Price, 0) as Drug_Price,
					ISNULL(ER.EvnRecept_Kolvo * ER_DrugData.DrugState_Price, 0) as EvnRecept_Summa
				from DrugRequestRow DRR WITH (NOLOCK)
					inner join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id
					inner join DrugRequestPeriod DRP WITH (NOLOCK) on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
						and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
					inner join v_PersonState PS WITH (NOLOCK) on PS.Person_id = DRR.Person_id
					left join [Address] UAddr WITH (NOLOCK) on UAddr.Address_id = PS.UAddress_id
					outer apply (
						select top 1 DRR2.Person_id
						from DrugRequestRow DRR2 WITH (NOLOCK)
							inner join DrugRequest DR2 WITH (NOLOCK) on DR2.DrugRequest_id = DRR2.DrugRequest_id
								and DR2.MedPersonal_id = :MedPersonal_id
								and DR2.DrugRequestPeriod_id = :DrugRequestPeriod_id
						where DRR2.Person_id = DRR.Person_id
							and DRR2.DrugRequestType_id = :DrugRequestType_id
							and (DRR2.DrugRequestRow_delDT is null or DRR2.DrugRequestRow_delDT > DRP.DrugRequestPeriod_endDate)
					) MPP
					inner join v_Lpu Lpu WITH (NOLOCK) on Lpu.Lpu_id = DR.Lpu_id
					left join LpuSection LS WITH (NOLOCK) on LS.LpuSection_id = DR.LpuSection_id
					left join v_MedPersonal MP WITH (NOLOCK) on MP.MedPersonal_id = DR.MedPersonal_id
						and MP.Lpu_id = DR.Lpu_id
					left join DrugProtoMnn DPM WITH (NOLOCK) on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
					left join Drug WITH (NOLOCK) on Drug.Drug_id = DRR.Drug_id
					left join DrugForm WITH (NOLOCK) on DrugForm.DrugForm_id = Drug.DrugForm_id
					outer apply (
						select top 1
							DF.DrugFormGroup_id,
							D.Drug_DoseUEQ,
							D.Drug_DoseUEEi
						from
							DrugState DS WITH (NOLOCK)
							inner join DrugProto DP WITH (NOLOCK) on DP.DrugProto_id = DS.DrugProto_id
								and DP.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
							inner join Drug D WITH (NOLOCK) on D.Drug_id = DS.Drug_id
							inner join DrugForm DF WITH (NOLOCK) on DF.DrugForm_id = D.DrugForm_id
						where DS.DrugProtoMnn_id = DPM.DrugProtoMnn_id
					) DrugData
					left join v_EvnRecept ER WITH (NOLOCK) on ER.DrugRequestRow_id = DRR.DrugRequestRow_id
						and ER.Person_id = DRR.Person_id
					left join Drug ERD with(nolock) on ERD.Drug_id = ER.Drug_id
						and ISNULL(ERD.Drug_DoseUEEi, '') = ISNULL(DrugData.Drug_DoseUEEi, '')
						and ISNULL(ERD.Drug_DoseUEQ, 0) = ISNULL(DrugData.Drug_DoseUEQ, 0)
					left join DrugForm ERDF WITH (NOLOCK) on ERDF.DrugForm_id = ERD.DrugForm_id
						and ISNULL(ERDF.DrugFormGroup_id, 0) = ISNULL(DrugData.DrugFormGroup_id, 0)
					left join v_Lpu ERLpu WITH (NOLOCK) on ERLpu.Lpu_id = ER.Lpu_id
					left join LpuSection ERLS WITH (NOLOCK) on ERLS.LpuSection_id = ER.LpuSection_id
					left join v_MedPersonal ERMP WITH (NOLOCK) on ERMP.MedPersonal_id = ER.MedPersonal_id
						and ERMP.Lpu_id = ER.Lpu_id
					outer apply (
						select top 1
							DS.DrugState_Price
						from
							DrugState DS WITH (NOLOCK)
							inner join DrugProto DP WITH (NOLOCK) on DP.DrugProto_id = DS.DrugProto_id
								and DP.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
						where DS.Drug_id = ERD.Drug_id
					) ER_DrugData
				where " . $filter . "
					and DRR.DrugRequestType_id = :DrugRequestType_id
					and MPP.Person_id is not null
					and (DRR.DrugRequestRow_delDT is null or DRR.DrugRequestRow_delDT > DRP.DrugRequestPeriod_endDate)
			),
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
				select
					ER.Person_id,
					RTRIM(PS.Person_SurName) + ' ' + RTRIM(PS.Person_FirName) + ' ' + RTRIM(PS.Person_SecName) as Person_Fio,
					convert(varchar(10), PS.Person_BirthDay, 104) as Person_Birthday,
					ISNULL(UAddr.Address_Address, '') as UAddress_Name,
					0 as DrugRequestRow_id,
					'=== ВНЕ ЗАЯВКИ ===' as Lpu_Nick,
					'' as LpuSection_Name,
					'' as MedPersonal_Fio,
					ISNULL(ERD.Drug_Code, '') as DrugRequestRow_Code,
					ISNULL(ERD.Drug_Name, '') as DrugRequestRow_Name,
					0 as DrugRequestRow_Kolvo,
					0 as DrugRequestRow_Price,
					0 as DrugRequestRow_Summa,
					ISNULL(ERLpu.Lpu_Nick, '') as ER_Lpu_Nick,
					ISNULL(ERLS.LpuSection_Name, '') as ER_LpuSection_Name,
					ISNULL(ERMP.Person_Fio, '') as ER_MedPersonal_Fio,
					ISNULL(ER.EvnRecept_Kolvo, 0) as EvnRecept_Kolvo,
					ISNULL(ER_DrugData.DrugState_Price, 0) as Drug_Price,
					ISNULL(ER.EvnRecept_Kolvo * ER_DrugData.DrugState_Price, 0) as EvnRecept_Summa
				from v_EvnRecept ER WITH (NOLOCK)
					inner join DrugRequestPeriod DRP WITH (NOLOCK) on DRP.DrugRequestPeriod_id = :DrugRequestPeriod_id
						and ER.EvnRecept_setDT between DRP.DrugRequestPeriod_begDate and DRP.DrugRequestPeriod_endDate
					inner join Drug ERD WITH (NOLOCK) on ERD.Drug_id = ER.Drug_id
					left join v_Lpu ERLpu WITH (NOLOCK) on ERLpu.Lpu_id = ER.Lpu_id
					left join LpuSection ERLS WITH (NOLOCK) on ERLS.LpuSection_id = ER.LpuSection_id
					left join v_MedPersonal ERMP WITH (NOLOCK) on ERMP.MedPersonal_id = ER.MedPersonal_id
						and ERMP.Lpu_id = ER.Lpu_id
					inner join v_PersonState PS WITH (NOLOCK) on PS.Person_id = ER.Person_id
					left join [Address] UAddr WITH (NOLOCK) on UAddr.Address_id = PS.UAddress_id
					outer apply (
						select top 1 DRR2.Person_id
						from DrugRequestRow DRR2 WITH (NOLOCK)
							inner join DrugRequest DR2 WITH (NOLOCK) on DR2.DrugRequest_id = DRR2.DrugRequest_id
								and DR2.MedPersonal_id = :MedPersonal_id
						where DRR2.Person_id = ER.Person_id
							and DRR2.DrugRequestType_id = :DrugRequestType_id
							and (DRR2.DrugRequestRow_delDT is null or DRR2.DrugRequestRow_delDT > DRP.DrugRequestPeriod_endDate)
					) MPP
					outer apply (
						select top 1
							DS.DrugState_Price
						from
							DrugState DS WITH (NOLOCK)
							inner join DrugProto DP WITH (NOLOCK) on DP.DrugProto_id = DS.DrugProto_id
								and DP.DrugRequestPeriod_id = DRP.DrugRequestPeriod_id
						where DS.Drug_id = ERD.Drug_id
					) ER_DrugData
				where (1 = 1)
					and ER.ReceptFinance_id = :ReceptFinance_id
					and ER.DrugRequestRow_id is null
					and MPP.Person_id is not null
			)

			select
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
			from MedPersonalRequest with (nolock)
			union all
			select
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
			from OutOfRequest with (nolock)
			order by Person_Fio, DrugRequestRow_id desc, Lpu_Nick
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
			select
				RTRIM(ISNULL(Lpu.Lpu_Nick, '')) as Lpu_Name,
				RTRIM(ISNULL(MP.Person_Fio, '')) as MedPersonal_Fio,
				RTRIM(RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Secname, ''))) as Person_Fio,
				case
					when FedLgot.IsFedLgot = 1 then 'Федеральная'
					when RegLgot.IsRegLgot = 1 then 'Региональная'
					else 'Нет'
				end as PrivilegeFinance_Name,
				RTRIM(ISNULL(DRT.DrugRequestType_Name, '')) as DrugRequestType_Name,
				RTRIM(ISNULL(DPM.DrugProtoMnn_Name, '')) as DrugRequestRow_Name,
				SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)) as DrugRequestRow_Kolvo,
				ROUND(SUM(DRR.DrugRequestRow_Summa) / SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)), 2) as DrugRequestRow_Price,
				SUM(DRR.DrugRequestRow_Summa) as DrugRequestRow_Summa
			from DrugRequestRow DRR WITH (NOLOCK)
				inner join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id
				inner join DrugRequestType DRT WITH (NOLOCK) on DRT.DrugRequestType_id = DRR.DrugRequestType_id
				inner join DrugRequestPeriod DRP WITH (NOLOCK) on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
					and DRP.DrugRequestPeriod_id = :DrugRequestPeriod_id
				inner join DrugProtoMnn DPM WITH (NOLOCK) on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
				inner join v_PersonState PS WITH (NOLOCK) on PS.Person_id = DRR.Person_id
				inner join v_Lpu Lpu WITH (NOLOCK) on Lpu.Lpu_id = DR.Lpu_id
				inner join v_MedPersonal MP WITH (NOLOCK) on MP.MedPersonal_id = DR.MedPersonal_id
					and MP.Lpu_id = Lpu.Lpu_id
				left join LpuSection LS WITH (NOLOCK) on LS.LpuSection_id = DR.LpuSection_id
				outer apply (
					select top 1
						1 as IsFedLgot
					from
						PersonPrivilege PP WITH (NOLOCK)
						inner join PrivilegeType PT WITH (NOLOCK) on PT.PrivilegeType_id = PP.PrivilegeType_id
							and PT.ReceptFinance_id = 1
							and isnumeric(PT.PrivilegeType_Code) = 1
						left join v_PersonRefuse PR WITH (NOLOCK) on PR.Person_id = PP.Person_id
							and PR.PersonRefuse_Year = YEAR(DRP.DrugRequestPeriod_begDate)
							and PR.PersonRefuse_IsRefuse = 2 
					where PP.Person_id = DRR.Person_id
						and PP.PersonPrivilege_begDate <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
						and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate >= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
						and ISNULL(PR.PersonRefuse_IsRefuse, 1) = 1
				) FedLgot
				outer apply (
					select top 1
						1 as IsRegLgot
					from
						PersonPrivilege PP WITH (NOLOCK)
						inner join PrivilegeType PT WITH (NOLOCK) on PT.PrivilegeType_id = PP.PrivilegeType_id
							and PT.ReceptFinance_id = 2
							and isnumeric(PT.PrivilegeType_Code) = 1
						outer apply (
							select top 1 1 as IsFedLgot
							from v_PersonPrivilege PP2 WITH (NOLOCK)
								inner join PrivilegeType PT2 WITH (NOLOCK) on PT2.PrivilegeType_id = PP2.PrivilegeType_id
									and PT2.ReceptFinance_id = 1
									and isnumeric(PT2.PrivilegeType_Code) = 1
							where PP2.Person_id = PP.Person_id
								and PP2.PersonPrivilege_begDate <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
								and (PP2.PersonPrivilege_endDate is null or PP2.PersonPrivilege_endDate >= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
						) FL
					where PP.Person_id = DRR.Person_id
						and PP.PersonPrivilege_begDate <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
						and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate >= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
						and FL.IsFedLgot is null
				) RegLgot
			where " . $filter . "
				-- Тип заявки
				and (DRR.DrugRequestRow_delDT is null or DRR.DrugRequestRow_delDT > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
				-- Заявка актуальна на выбранную дату
				and ISNULL(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
				-- Несоответствие типа финансирования медикамента и льготы
				and (
					(FedLgot.IsFedLgot = 1 and DRT.DrugRequestType_id <> 1)
					or (RegLgot.IsRegLgot = 1 and DRT.DrugRequestType_id <> 2)
					or (FedLgot.IsFedLgot is null and RegLgot.IsRegLgot is null)
				)
			group by
				Lpu.Lpu_Nick,
				MP.Person_Fio,
				PS.Person_Surname,
				PS.Person_Firname,
				PS.Person_Secname,
				FedLgot.IsFedLgot,
				RegLgot.IsRegLgot,
				DPM.DrugProtoMnn_id,
				DPM.DrugProtoMnn_Name,
				DRT.DrugRequestType_Name
			order by
				DrugRequestRow_Name
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
			select
				DPM.DrugProtoMnn_id,
				RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' '+ RTRIM(ISNULL(PS.Person_Secname, '')) as Person_Fio,
				RTRIM(DPM.DrugProtoMnn_Name) as DrugProtoMnn_Name,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				RTRIM(Lpu.Lpu_Nick) as Lpu_Name,
				ISNULL(AttLpu.AttachLpu_Name, '') as AttachLpu_Name,
				SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)) as DrugRequestRow_Kolvo,
				ROUND(SUM(DRR.DrugRequestRow_Summa) / SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)), 2) as DrugRequestRow_Price,
				SUM(DRR.DrugRequestRow_Summa) as DrugRequestRow_Summa
			from DrugRequestRow DRR WITH (NOLOCK)
				inner join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id
					and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
				inner join DrugRequestPeriod DRP WITH (NOLOCK) on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
				inner join v_Lpu Lpu WITH (NOLOCK) on Lpu.Lpu_id = DR.Lpu_id
				inner join v_MedPersonal MP WITH (NOLOCK) on MP.MedPersonal_id = DR.MedPersonal_id
					and MP.Lpu_id = DR.Lpu_id
				inner join v_PersonState PS WITH (NOLOCK) on PS.Person_id = DRR.Person_id
				inner join DrugProtoMnn DPM WITH (NOLOCK) on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
				outer apply (
					select top 1
						PC.Lpu_id
					from
						v_PersonCard_all PC WITH (NOLOCK)
					where
						PC.Person_id = DRR.Person_id
						and PC.Lpu_id = DR.Lpu_id
						and PC.LpuAttachType_id = 1
						and PC.PersonCard_begDate <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
						and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
				) AttLpuDR
				outer apply (
					select top 1
						RTRIM(ISNULL(Lpu2.Lpu_Nick, '')) as AttachLpu_Name
					from
						v_PersonCard_all PC WITH (NOLOCK)
						inner join v_Lpu Lpu2 WITH (NOLOCK) on Lpu2.Lpu_id = PC.Lpu_id
					where
						PC.Person_id = DRR.Person_id
						and PC.LpuAttachType_id = 1
						and PC.PersonCard_begDate <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
						and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
					order by PC.PersonCard_begDate desc
				) AttLpu
			where (1 = 1)
				and ISNULL(:Lpu_id, DR.Lpu_id) = DR.Lpu_id
				and DR.Lpu_id not in (select id from MinZdravList())
				and DR.Lpu_id not in (select id from OnkoList())
				and DR.LpuSection_id <> 337
				and AttLpuDR.Lpu_id is null
				and DRR.DrugRequestType_id = :DrugRequestType_id
				and (DRR.DrugRequestRow_delDT is null or DRR.DrugRequestRow_delDT > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
				and ISNULL(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
			group by
				DPM.DrugProtoMnn_id,
				PS.Person_Surname,
				PS.Person_Firname,
				PS.Person_Secname,
				DPM.DrugProtoMnn_Name,
				MP.Person_Fio,
				Lpu.Lpu_Nick,
				AttLpu.AttachLpu_Name
			order by
				DrugProtoMnn_Name,
				Person_Fio
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

		$filter .= " and ISNULL(NULLIF(:Lpu_id, 0), DR.Lpu_id) = DR.Lpu_id";

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
			select
				RTRIM(COALESCE(DPM.DrugProtoMnn_Code, Drug.Drug_Code, '')) as DrugRequestRow_Code,
				RTRIM(COALESCE(DPM.DrugProtoMnn_Name, Drug.Drug_Name, '')) as DrugRequestRow_Name,
				SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)) as DrugRequestRow_Kolvo,
				ROUND(SUM(DRR.DrugRequestRow_Summa) / SUM(ISNULL(DRR.DrugRequestRow_Kolvo, 0)), 2) as DrugRequestRow_Price,
				SUM(DRR.DrugRequestRow_Summa) as DrugRequestRow_Summa
			from DrugRequestRow DRR WITH (NOLOCK)
				inner join DrugRequest DR WITH (NOLOCK) on DR.DrugRequest_id = DRR.DrugRequest_id
					and DR.DrugRequestPeriod_id = :DrugRequestPeriod_id
				left join DrugProtoMnn DPM WITH (NOLOCK) on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
				left join Drug WITH (NOLOCK) on Drug.Drug_id = DRR.Drug_id
				left join LpuSection LS WITH (NOLOCK) on LS.LpuSection_id = DR.LpuSection_id
				outer apply (
					select top 1
						PC.Lpu_id
					from v_PersonCard_all PC WITH (NOLOCK)
					where PC.LpuAttachType_id = 1
						and ISNULL(NULLIF(:Lpu_id, 0), PC.Lpu_id) = PC.Lpu_id
						and PC.Person_id = DRR.Person_id
						and PC.PersonCard_begDate <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
						and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = PC.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
					order by PC.PersonCard_begDate desc
				) AttLpu
			where (1 = 1)
				and (
					(" . $filter . " and DR.LpuSection_id != 337)
					or
					(
						(DR.Lpu_id in (select id from MinZdravList()) or DR.Lpu_id in (select id from OnkoList()) or DR.LpuSection_id = 337)
						and (AttLpu.Lpu_id is not null or NULLIF(:Lpu_id, 0) is null)
					)
				)
				and DRR.DrugRequestType_id = :DrugRequestType_id
				and (DRR.DrugRequestRow_delDT is null or DRR.DrugRequestRow_delDT > ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end))
				and ISNULL(DRR.DrugRequestRow_updDT, DRR.DrugRequestRow_insDT) <= ISNULL((select top 1 case when ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) < CAST(:DrugRequestRow_actDate as datetime) then ISNULL(DRTS.DrugRequestTotalStatus_closeDT, dbo.tzGetDate()) else CAST(:DrugRequestRow_actDate as datetime) end as actualDate from DrugRequestTotalStatus DRTS with (nolock) where DRTS.Lpu_id = DR.Lpu_id and DRTS.DrugRequestPeriod_id = :DrugRequestPeriod_id), case when CAST(:DrugRequestRow_actDate as datetime) < dbo.tzGetDate() then CAST(:DrugRequestRow_actDate as datetime) else dbo.tzGetDate() end)
			group by
				DPM.DrugProtoMnn_Code,
				DPM.DrugProtoMnn_Name,
				Drug.Drug_Code,
				Drug.Drug_Name
			order by
				DrugRequestRow_Name
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
			select
				RTRIM(ISNULL(DRP.DrugRequestPeriod_Name, '')) as DrugRequestPeriod_Name,
				RTRIM(ISNULL(DRT.DrugRequestType_Name, '')) as DrugRequestType_Name,
				RTRIM(ISNULL(Lpu.Lpu_Name, '')) as Lpu_Name,
				RTRIM(ISNULL(LS.LpuSection_Name, '')) as LpuSection_Name,
				RTRIM(ISNULL(LU.LpuUnit_Name, '')) as LpuUnit_Name,
				RTRIM(ISNULL(MP.Person_Fio, '')) as MedPersonal_Fio
			from DrugRequestPeriod DRP WITH (NOLOCK)
				left join DrugRequestType DRT WITH (NOLOCK) on DRT.DrugRequestType_id = :DrugRequestType_id
				left join v_Lpu Lpu WITH (NOLOCK) on Lpu.Lpu_id = :Lpu_id
				left join LpuSection LS WITH (NOLOCK) on LS.LpuSection_id = :LpuSection_id
				left join LpuUnit LU WITH (NOLOCK) on LU.LpuUnit_id = :LpuUnit_id
				left join v_MedPersonal MP WITH (NOLOCK) on MP.MedPersonal_id = :MedPersonal_id
			where DRP.DrugRequestPeriod_id = :DrugRequestPeriod_id
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
			$where[] = 'DATEPART(year,DrugRequestPeriod_id_ref.DrugRequestPeriod_begDate) = :Year';
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
			SELECT top 1000
				v_DrugRequest.Server_id, v_DrugRequest.DrugRequest_id, v_DrugRequest.DrugRequestPeriod_id, v_DrugRequest.DrugRequestStatus_id, v_DrugRequest.DrugRequest_Name, v_DrugRequest.Lpu_id, v_DrugRequest.LpuSection_id, v_DrugRequest.MedPersonal_id, v_DrugRequest.DrugRequest_Summa, v_DrugRequest.DrugRequest_YoungChildCount
				,DrugRequestPeriod_id_ref.DrugRequestPeriod_Name DrugRequestPeriod_id_Name, DrugRequestStatus_id_ref.DrugRequestStatus_Name DrugRequestStatus_id_Name, LpuSection_id_ref.LpuSection_Name LpuSection_id_Name
			FROM
				dbo.v_DrugRequest WITH (NOLOCK)
				LEFT JOIN dbo.v_DrugRequestPeriod DrugRequestPeriod_id_ref WITH (NOLOCK) ON DrugRequestPeriod_id_ref.DrugRequestPeriod_id = v_DrugRequest.DrugRequestPeriod_id
				LEFT JOIN dbo.v_DrugRequestStatus DrugRequestStatus_id_ref WITH (NOLOCK) ON DrugRequestStatus_id_ref.DrugRequestStatus_id = v_DrugRequest.DrugRequestStatus_id
				LEFT JOIN dbo.v_Lpu Lpu_id_ref WITH (NOLOCK) ON Lpu_id_ref.Lpu_id = v_DrugRequest.Lpu_id
				LEFT JOIN dbo.v_LpuSection LpuSection_id_ref WITH (NOLOCK) ON LpuSection_id_ref.LpuSection_id = v_DrugRequest.LpuSection_id
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
			select
				DrugRequestPeriod_id, DrugRequestPeriod_begDate, DrugRequestPeriod_endDate, DrugRequestPeriod_Name
			from
				dbo.v_DrugRequestPeriod with (nolock)
			where
				DrugRequestPeriod_id = :DrugRequestPeriod_id
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
			SELECT
				v_DrugRequestPeriod.DrugRequestPeriod_id,
				convert(varchar(10), v_DrugRequestPeriod.DrugRequestPeriod_begDate, 104) DrugRequestPeriod_begDate,
				convert(varchar(10), v_DrugRequestPeriod.DrugRequestPeriod_endDate, 104) DrugRequestPeriod_endDate,
				cast(v_DrugRequestPeriod.DrugRequestPeriod_begDate as bigint) DrugRequestPeriod_Sort,
				(convert(varchar(10), v_DrugRequestPeriod.DrugRequestPeriod_begDate, 104) + ' - ' + convert(varchar(10), v_DrugRequestPeriod.DrugRequestPeriod_endDate, 104)) as DrugRequestPeriod_TimeRange,
				v_DrugRequestPeriod.DrugRequestPeriod_Name				
			FROM
				dbo.v_DrugRequestPeriod WITH (NOLOCK)
			where
				:DrugRequestPeriod_id is null or v_DrugRequestPeriod.DrugRequestPeriod_id = :DrugRequestPeriod_id
			order by
				v_DrugRequestPeriod.DrugRequestPeriod_begDate
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
			declare
				@DrugRequestPeriod_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @DrugRequestPeriod_id = :DrugRequestPeriod_id;
			exec dbo." . $procedure . "
				@DrugRequestPeriod_id = @DrugRequestPeriod_id output,
				@DrugRequestPeriod_begDate = :DrugRequestPeriod_begDate,
				@DrugRequestPeriod_endDate = :DrugRequestPeriod_endDate,
				@DrugRequestPeriod_Name = :DrugRequestPeriod_Name,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @DrugRequestPeriod_id as DrugRequestPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			declare @DrugRequestPeriod_id int = :DrugRequestPeriod_id;
			select (
				(select count(*) From DrugProto with (nolock) Where DrugRequestPeriod_id = @DrugRequestPeriod_id) + 
				(select count(*) From DrugProtoMnnGroup with (nolock) Where DrugRequestPeriod_id = @DrugRequestPeriod_id) + 
				(select count(*) From DrugRequest with (nolock) Where DrugRequestPeriod_id = @DrugRequestPeriod_id) + 
				(select count(*) From DrugRequestBuy with (nolock) Where DrugRequestPeriod_id = @DrugRequestPeriod_id) + 
				(select count(*) From DrugRequestLpuGroup with (nolock) Where DrugRequestPeriod_id = @DrugRequestPeriod_id) + 
				(select count(*) From DrugRequestPerson with (nolock) Where DrugRequestPeriod_id = @DrugRequestPeriod_id) + 
				(select count(*) From DrugRequestProperty with (nolock) Where DrugRequestPeriod_id = @DrugRequestPeriod_id) +
				(select count(*) From DrugRequestTotalStatus with (nolock) Where DrugRequestPeriod_id = @DrugRequestPeriod_id)
			) as record_count;
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
				DrugRequestPlanPeriod with(rowlock)
			where
				DrugRequestPeriod_id = :DrugRequestPeriod_id;
		";
		$r = $this->db->query($q, array(
			'DrugRequestPeriod_id' => $data['id']
		));
		
		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec dbo.p_DrugRequestPeriod_del
				@DrugRequestPeriod_id = :DrugRequestPeriod_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
		$q = "select convert(varchar(10), MAX(DrugRequestPeriod_endDate), 104) as max_date from DrugRequestPeriod with (nolock)";
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
			declare
				@DrugRequest_id bigint = :DrugRequest_id,
				@PersonRegisterType_id bigint = :PersonRegisterType_id,
				@DrugRequestPeriod_id bigint = :DrugRequestPeriod_id;

			if (@DrugRequest_id is not null and @DrugRequest_id > 0)
				select @PersonRegisterType_id = PersonRegisterType_id, @DrugRequestPeriod_id = DrugRequestPeriod_id from v_DrugRequest with (nolock) where DrugRequest_id = @DrugRequest_id;

			select
				DrugRequestPropertyFed_id,
				DrugRequestPropertyReg_id
			from
				DrugRequest with (nolock)
			where
				DrugRequest_Version is null
				and DrugRequestCategory_id = (select DrugRequestCategory_id from DrugRequestCategory with (nolock) where DrugRequestCategory_SysNick = 'region')
				and DrugRequestPeriod_id = @DrugRequestPeriod_id
				and PersonRegisterType_id = @PersonRegisterType_id;
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
			select top 1
				DrugRequestRow_id,
				DrugRequestRow_Kolvo
			from
				v_DrugRequestRow with (nolock)
			where
				DrugRequest_id = :DrugRequest_id and
				DrugRequestType_id = :DrugRequestType_id and
				isnull(Person_id,0) = isnull(:Person_id,0) and
				DrugComplexMnn_id = :DrugComplexMnn_id;
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
			select
				count(p.LpuRegion_id) as cnt
			from (
				select distinct
				msr.LpuRegion_id
			from
				v_DrugRequest dr with (nolock)
				left join DrugRequestPeriod drp with (nolock) on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
				left join v_MedStaffRegion msr with (nolock) on msr.MedPersonal_id = dr.MedPersonal_id
				left join v_LpuRegion lr with (nolock) on lr.LpuRegion_id = msr.LpuRegion_id
			where
				DrugRequest_id = :DrugRequest_id and
				lr.LpuRegion_begDate <= drp.DrugRequestPeriod_begDate and
				(
					lr.LpuRegion_endDate is null or
					lr.LpuRegion_endDate >= drp.DrugRequestPeriod_endDate
				)
			) p;
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
			select
				MedPersonal_id,
				Lpu_id,
				DrugRequestPeriod_id
			from
				v_DrugRequest with (nolock)
			where
				DrugRequest_id = :DrugRequest_id;
		";
		$request_data = $this->getFirstRowFromQuery($query, array(
			'DrugRequest_id' => $data['DrugRequest_id']
		));
		if (count($request_data) == 0) {
			return array(array('Error_Msg' => 'Не удалось получить данные заявки'));
		}

		//получение списка необходимых льготников, спика льготников в строках заявки, списка льготников в заявке, а также получение информации о необходимых действиях над этими списками
		$query = "
			declare
				@DrugRequest_id bigint = :DrugRequest_id,
				@MedPersonal_id bigint = :MedPersonal_id,
				@Lpu_id bigint = :Lpu_id,
				@DrugRequestPeriod_id bigint = :DrugRequestPeriod_id,
				@DrugRequestPeriod_begDate date,
				@DrugRequestPeriod_endDate date,
				@Current_Date date,
				@Current_Year int;

			set @Current_Date = dbo.tzGetDate();
			set @Current_Year = datepart(year, @Current_Date);
			set @DrugRequestPeriod_begDate = (select top 1 DrugRequestPeriod_begDate from v_DrugRequestPeriod with (nolock) where DrugRequestPeriod_id = @DrugRequestPeriod_id);
			set @DrugRequestPeriod_endDate = (select top 1 DrugRequestPeriod_endDate from v_DrugRequestPeriod with (nolock) where DrugRequestPeriod_id = @DrugRequestPeriod_id);

			with request_person as (
				select
					replace(replace((
						select
							cast(DrugRequestRow_id as varchar(max))+','
						from
							DrugRequestRow with (nolock)
						where
							DrugRequest_id = @DrugRequest_id and
							Person_id = pp.Person_id
						for xml path('')
					)+',,', ',,,', ''), ',,', '') as DrugRequestRow_List,
					pp.Person_id
				from
				(
					select distinct
						Person_id
					from
						DrugRequestRow with (nolock)
					where
						DrugRequest_id = @DrugRequest_id
				) pp
			),
			list_person as (
				select
					DrugRequestPerson_id,
					Person_id
				from
					DrugRequestPerson with (nolock)
				where
					DrugRequestPeriod_id = @DrugRequestPeriod_id and
					Lpu_id = @Lpu_id and
					MedPersonal_id = @MedPersonal_id
			)
			select
				coalesce(p.Person_id, rp.Person_id, lp.Person_id) as Person_id,
				rp.DrugRequestRow_List,
				lp.DrugRequestPerson_id,
				(
					case
						when p.Person_id is null and rp.Person_id is not null then 'delete'
						else null
					end
				) as req_action,
				(
					case
						when p.Person_id is null and lp.Person_id is not null then 'delete'
						when p.Person_id is not null and lp.Person_id is null then 'add'
						else null
					end
				) as list_action
			from (
					select distinct
						priv.Person_id
					from
						PersonPrivilege priv with (nolock)
						left join PrivilegeType pt with (nolock) on pt.PrivilegeType_id = priv.PrivilegeType_id
						left join v_PersonState ps with (nolock) on ps.Person_id = priv.Person_id
						outer apply (
							select top 1
								p_ref.PersonRefuse_id
							from
								v_PersonRefuse p_ref with (nolock)
							where
								p_ref.Person_id = priv.Person_id and
								p_ref.PersonRefuse_IsRefuse = 2 and
								p_ref.PersonRefuse_Year = YEAR(@DrugRequestPeriod_begDate)
						) refuse
						outer apply (
							select
								count(EvnRecept_id) as cnt
							from
								v_EvnRecept er with (nolock)
							where
								er.Person_id = priv.Person_id and
								datediff(day,er.EvnRecept_setDate, @Current_Date) <= 93 and
								er.ReceptFinance_id = pt.ReceptFinance_id
						) recept
					where
						ps.Person_deadDT is null and
						refuse.PersonRefuse_id is null and
						(PersonPrivilege_endDate is null or PersonPrivilege_endDate >= @DrugRequestPeriod_begDate) and
						(PersonPrivilege_begDate is null or PersonPrivilege_begDate <= @DrugRequestPeriod_begDate) and
						(
							:SourceDrugRequest_id is not null or
							priv.Person_id in (
								select
									Person_id
								from
									v_PersonCard with (nolock)
								where
									LpuRegion_id in (
										select
											msr.LpuRegion_id
										from
											MedStaffRegion msr with (nolock)
											left join v_LpuRegion lr with (nolock) on lr.LpuRegion_id = msr.LpuRegion_id
										where
											lr.Lpu_id = @Lpu_id and
											msr.MedPersonal_id = @MedPersonal_id and
											lr.LpuRegion_begDate <= @DrugRequestPeriod_begDate and
											(
												lr.LpuRegion_endDate is null or
												lr.LpuRegion_endDate >= @DrugRequestPeriod_endDate
											)
									)
							)
						) and
						recept.cnt > 0 and
						(
							:SourceDrugRequest_id is null or
							priv.Person_id in (
								select
									drp1.Person_id
								from
									v_DrugRequest dr1 with (nolock)
									left join v_DrugRequestPerson drp1 with (nolock) on
										drp1.DrugRequestPeriod_id = dr1.DrugRequestPeriod_id and
										drp1.Lpu_id = dr1.Lpu_id and
										drp1.MedPersonal_id = dr1.MedPersonal_id
								where
									dr1.DrugRequest_id = :SourceDrugRequest_id
							)
						)
				) p
				full outer join request_person rp with(nolock) on rp.Person_id = p.Person_id
				full outer join list_person lp with(nolock) on lp.Person_id = p.Person_id or lp.Person_id = rp.Person_id
			where
				p.Person_id is not null or
				rp.Person_id is not null or
				lp.Person_id is not null;
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
						declare
							@DrugRequestPerson_id bigint,
							@Error_Code bigint,
							@Error_Message varchar(4000);

						exec dbo.p_DrugRequestPerson_ins
							@Server_id = :Server_id,
							@DrugRequestPerson_id = @DrugRequestPerson_id output,
							@DrugRequestPeriod_id = :DrugRequestPeriod_id,
							@Person_id = :Person_id,
							@Lpu_id = :Lpu_id,
							@MedPersonal_id = :MedPersonal_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code output,
							@Error_Message = @Error_Message output;

						select @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
						declare
							@Error_Code bigint,
							@Error_Message varchar(4000);

						exec dbo.p_DrugRequestPerson_del
							@DrugRequestPerson_id = :DrugRequestPerson_id,
							@Error_Code = @Error_Code output,
							@Error_Message = @Error_Message output;

						select @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
							declare
								@Error_Code bigint,
								@Error_Message varchar(4000);

							exec dbo.p_DrugRequestRow_del
								@DrugRequestRow_id = :DrugRequestRow_id,
								@Error_Code = @Error_Code output,
								@Error_Message = @Error_Message output;

							select @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
			declare
				@DrugRequestPeriod_id bigint,
				@Lpu_id bigint,
				@MedPersonal_id bigint,
				@DrugRequestPeriod_begDate date,
				@Current_Date date;

			set @Current_Date = dbo.tzGetDate();;

			select
				@DrugRequestPeriod_id = dr.DrugRequestPeriod_id,
				@Lpu_id = dr.Lpu_id,
				@MedPersonal_id = dr.MedPersonal_id,
				@DrugRequestPeriod_begDate = drp.DrugRequestPeriod_begDate
			from
				v_DrugRequest dr with (nolock)
				left join v_DrugRequestPeriod drp with (nolock) on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
			where
				dr.DrugRequest_id = :DrugRequest_id;

			with person_list as (
				select
					Person_id
				from
					v_DrugRequestPerson with (nolock)
				where
					DrugRequestPeriod_id = @DrugRequestPeriod_id and
					Lpu_id = @Lpu_id and
					MedPersonal_id = @MedPersonal_id
			)
			select
				drr.DrugRequestRow_id,
				price.Price,
				'copy' as action
			from
				v_DrugRequestRow drr with (nolock)
				left join v_DrugRequest dr with (nolock) on dr.DrugRequest_id = drr.DrugRequest_id
				left join v_DrugRequestRow current_drr with (nolock) on
					current_drr.DrugRequest_id = :DrugRequest_id and
					isnull(current_drr.Person_id, 0) = isnull(drr.Person_id, 0) and
					(
						current_drr.DrugProtoMnn_id = drr.DrugProtoMnn_id or
						current_drr.Drug_id = drr.Drug_id
					)
                left join v_DrugProtoMnn dpm with (nolock) on dpm.DrugProtoMnn_id = drr.DrugProtoMnn_id
            	left join v_YesNo IsCommon with(nolock) on isCommon.YesNo_id = dpm.DrugProtoMnn_IsCommon
				outer apply (
					select
						max(DrugState_Price) as Price
					from
						v_DrugState ds with (nolock)
						inner join v_DrugProto dp with (nolock) on ds.DrugProto_id = dp.DrugProto_id
					where
						(Drug_id = drr.Drug_id or DrugProtoMnn_id=drr.DrugProtoMnn_id) and
						dp.ReceptFinance_id = drr.DrugRequestType_id and
						dp.DrugRequestPeriod_id = @DrugRequestPeriod_id
				) price
				outer apply (
					select
						count(EvnRecept_id) as cnt
					from
						v_EvnRecept er with (nolock)
					where
						er.Person_id = drr.Person_id and
						datediff(day,er.EvnRecept_setDate, @Current_Date) <= 93 and
						er.ReceptFinance_id = drr.DrugRequestType_id
				) recept
			where
				current_drr.DrugRequestRow_id is null and
				price.Price is not null and
				(
					drr.Person_id is null or
					(
						drr.Person_id in (select Person_id from person_list  with(nolock)) and
						recept.cnt > 0
					)
				) and
				(
                    IsCommon.YesNo_Code = 1 or
                    exists(
                        select
                            1
                        from
                            DrugRequestLpuGroup drlg with (nolock)
                        where
                            drlg.DrugProtoMnn_id = drr.DrugProtoMnn_id and
                            drlg.Lpu_id = @Lpu_id and
                            (isnull(drlg.medPersonal_id, @MedPersonal_id) = @MedPersonal_id) and
                            drlg.DrugRequestPeriod_id = @DrugRequestPeriod_id
                    )
                ) and
				drr.DrugRequest_id = :SourceDrugRequest_id;
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
					declare
						@Error_Code bigint,
						@Error_Message varchar(4000);

					exec dbo.p_DrugRequestRow_del
						@DrugRequestRow_id = :DrugRequestRow_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;

					select @Error_Code as Error_Code, @Error_Message as Error_Msg;
				";
			}
			if ($row['action'] == 'copy') {
				$query = "
					declare
						@DrugRequestRow_id bigint,
						@DrugRequestType_id bigint,
						@Person_id bigint,
						@DrugProtoMnn_id bigint,
						@DrugRequestRow_Kolvo float,
						@DrugRequestRow_Summa money,
						@Drug_id bigint,
						@DrugRequestRow_KolvoUe numeric(18,6),
						@DrugRequestRow_RashUe numeric(18,6),
						@ACTMATTERS_ID bigint,
						@DrugRequestRow_DoseOnce varchar(30),
						@Okei_oid bigint,
						@DrugRequestRow_DoseDay varchar(30),
						@Okei_did bigint,
						@DrugRequestRow_DoseCource varchar(30),
						@Okei_cid bigint,
						@DrugComplexMnn_id bigint,
						@TRADENAMES_id int,
						@DrugFinance_id bigint,
						@DrugRequestRow_KolDrugBuy numeric(18,2),
						@DrugRequestRow_SumBuy money,
						@Error_Code int,
						@Error_Message varchar(4000),
						@Price float = :Price;

					select
						@DrugRequestType_id = DrugRequestType_id,
						@Person_id = Person_id,
						@DrugProtoMnn_id = DrugProtoMnn_id,
						@DrugRequestRow_Kolvo = DrugRequestRow_Kolvo,
						@DrugRequestRow_Summa = isnull(DrugRequestRow_Kolvo*@Price,0),
						@Drug_id = Drug_id,
						@DrugRequestRow_KolvoUe = DrugRequestRow_KolvoUe,
						@DrugRequestRow_RashUe = DrugRequestRow_RashUe,
						@ACTMATTERS_ID = ACTMATTERS_ID,
						@DrugRequestRow_DoseOnce = DrugRequestRow_DoseOnce,
						@Okei_oid = Okei_oid,
						@DrugRequestRow_DoseDay = DrugRequestRow_DoseDay,
						@Okei_did = Okei_did,
						@DrugRequestRow_DoseCource = DrugRequestRow_DoseCource,
						@Okei_cid = Okei_cid,
						@DrugComplexMnn_id = DrugComplexMnn_id,
						@TRADENAMES_id = TRADENAMES_id,
						@DrugFinance_id = DrugFinance_id
					from
						v_DrugRequestRow with (nolock)
					where
						DrugRequestRow_id = :DrugRequestRow_id;

					execute dbo.p_DrugRequestRow_ins
						@DrugRequestRow_id = @DrugRequestRow_id output,
						@DrugRequest_id = :DrugRequest_id,
						@DrugRequestType_id = @DrugRequestType_id,
						@Person_id = @Person_id,
						@DrugProtoMnn_id = @DrugProtoMnn_id,
						@DrugRequestRow_Kolvo = @DrugRequestRow_Kolvo,
						@DrugRequestRow_Summa = @DrugRequestRow_Summa,
						@Drug_id = @Drug_id,
						@DrugRequestRow_KolvoUe = @DrugRequestRow_KolvoUe,
						@DrugRequestRow_RashUe = @DrugRequestRow_RashUe,
						@ACTMATTERS_ID = @ACTMATTERS_ID,
						@DrugRequestRow_DoseOnce = @DrugRequestRow_DoseOnce,
						@Okei_oid = @Okei_oid,
						@DrugRequestRow_DoseDay = @DrugRequestRow_DoseDay,
						@Okei_did = @Okei_did,
						@DrugRequestRow_DoseCource = @DrugRequestRow_DoseCource,
						@Okei_cid = @Okei_cid,
						@DrugComplexMnn_id = @DrugComplexMnn_id,
						@TRADENAMES_id = @TRADENAMES_id,
						@DrugFinance_id = @DrugFinance_id,
						@DrugRequestRow_KolDrugBuy = @DrugRequestRow_Kolvo,
						@DrugRequestRow_SumBuy = @DrugRequestRow_Summa,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;

					select @DrugRequestRow_id as DrugRequestRow_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
			select
				ps.[name]
			from
				sys.all_parameters ps with(nolock)
				left join sys.types t with(nolock) on t.system_type_id = ps.system_type_id and t.user_type_id = ps.user_type_id
			where
				ps.[object_id] = (
					select
						top 1 [object_id]
					from
						sys.objects with(nolock)
					where
						[type_desc] = 'SQL_STORED_PROCEDURE' and
						[name] = :name and
						(
							:schema is null or
							[schema_id] = (select top 1 [schema_id] from sys.schemas with(nolock) where [name] = :schema)
						)
				) and
				ps.[name] not in ('@pmUser_id', '@Error_Code', '@Error_Message', '@isReloadCount') and
				t.[is_user_defined] = 0;
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
					{$schema}.{$object_name} with (nolock)
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
                $query_part .= "@{$key} = :{$key}, ";
            }
        }

        $save_data['pmUser_id'] = isset($data['pmUser_id']) ? $data['pmUser_id'] : null;

        $query = "
			declare
				@{$key_field} bigint = :{$key_field},
				@Error_Code int,
				@Error_Message varchar(4000);

			execute {$schema}.{$proc_name}
				@{$key_field} = @{$key_field} output,
				{$query_part}
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @{$key_field} as {$key_field}, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
				{$schema}.{$object_name} with (nolock)
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
                $query_part .= "@{$key} = :{$key}, ";
            }
        }

        $save_data['pmUser_id'] = isset($data['pmUser_id']) ? $data['pmUser_id'] : null;

        $query = "
			declare
				@{$key_field} bigint = null,
				@Error_Code int,
				@Error_Message varchar(4000);

			execute {$schema}.{$proc_name}
				@{$key_field} = @{$key_field} output,
				{$query_part}
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @{$key_field} as {$key_field}, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
			declare
				@Error_Code int,
				@Error_Message varchar(4000);

			execute dbo.p_{$object_name}_del
				@{$object_name}_id = :{$object_name}_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @Error_Code as Error_Code, @Error_Message as Error_Message;
		";

        if (!empty($data['force_delete'])) {
            $query = "
                delete from
                    {$object_name}
                where
                    {$object_name}_id = :{$object_name}_id;

                select null as Error_Code, null as Error_Message;
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
            select
                drr.DrugRequestRow_Kolvo,
                drr.Drug_id,
                drr.DrugProtoMnn_id,
                drr.DrugRequestType_id,
                dr.DrugRequestPeriod_id,
                dr.Lpu_id,
                dr.MedPersonal_id
            from
                v_DrugRequestRow drr with (nolock)
                left join v_DrugRequest dr with (nolock) on dr.DrugRequest_id = drr.DrugRequest_id
            where
                drr.DrugRequestRow_id = :DrugRequestRow_id;
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
            select top 1
                drr2.DrugRequestRow_id,
                drr2.DrugRequestRow_Kolvo
            from
                v_DrugRequestRow drr with (nolock)
                left join v_DrugRequestRow drr2 with (nolock) on
                    drr2.DrugRequest_id = drr.DrugRequest_id and
                    isnull(drr2.Person_id, 0) = isnull(:Person_id, 0) and
                    isnull(drr2.DrugRequestType_id, 0) = isnull(drr.DrugRequestType_id, 0) and
                    isnull(drr2.DrugProtoMnn_id, 0) = isnull(drr.DrugProtoMnn_id, 0) and
                    isnull(drr2.Drug_id, 0) = isnull(drr.Drug_id, 0) and
                    isnull(drr2.DrugComplexMnn_id, 0) = isnull(drr.DrugComplexMnn_id, 0)
            where
                drr.DrugRequestRow_id = :DrugRequestRow_id;
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
            select
                drp.Person_id
            from
                v_DrugRequestPerson drp with (nolock)
            where
                drp.DrugRequestPerson_id = :DrugRequestPerson_id;
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
                select
                    drr.DrugRequestRow_id
                from
                    v_DrugRequestRow drr with (nolock)
                where
                    drr.DrugRequest_id = :DrugRequest_id and
                    drr.Person_id = :Person_id
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
        	select top 500
        		drr.DrugRequestRow_id,
        		rtrim(coalesce(dpm.DrugProtoMnn_Name, d.Drug_Name, '')) as DrugRequestRow_Name,
        		drr.DrugRequestRow_Kolvo,
        		drt.DrugRequestType_Name
			from
			    v_DrugRequestRow drr with (nolock)
				left join v_DrugProtoMnn dpm with (nolock) on dpm.DrugProtoMnn_id = drr.DrugProtoMnn_id
				left join v_Drug d with (nolock) on d.Drug_id = drr.Drug_id
				left join v_DrugRequestType drt with (nolock) on drt.DrugRequestType_id = drr.DrugRequestType_id
				{$where}
			order by
				DrugRequestRow_Name
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
