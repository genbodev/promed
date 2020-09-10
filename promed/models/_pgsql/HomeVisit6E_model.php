<?php

/**
 * HomeVisit - модель для вызовов врачей на дом в ExtJS 6
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
 * @version      20.09.2013
 */
class HomeVisit6E_model extends swPgModel {
	/**
	 * Получение вызовов
	 */
	function getHomeVisitList( $data, $OnlyPlan = false ) {
		$params = array();
		$filter = "(1=1)";

		if(isset($data['begDate']) && !empty($data['begDate'])){
			$filter .= " and cast(hv.HomeVisit_setDT as date) >= cast(:begDate as date)";
			$params['begDate'] = $data['begDate'];
		}

		if(isset($data['endDate']) && !empty($data['endDate'])){
			$filter .= " and cast(hv.HomeVisit_setDT as date) <= cast(:endDate as date)";
			$params['endDate'] = $data['endDate'];
		}

		if (!empty($data['Lpu_id']) && empty($data['allLpu']))
		{
			$filter .= " and hv.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
			$lpuJoin = " left join v_Lpu l on hv.Lpu_id = l.Lpu_id ";
		} else {
			$lpuJoin = " inner join v_Lpu l on hv.Lpu_id = l.Lpu_id ";
		}
		if (!empty($data['Person_Surname']))
		{
			$filter .= " and p.Person_SurName ilike (:Person_Surname||'%')";
			$params['Person_Surname'] = rtrim($data['Person_Surname']);
		}
		if (!empty($data['Person_Firname']))
		{
			$filter .= " and p.Person_FirName ilike (:Person_Firname||'%')";
			$params['Person_Firname'] = rtrim($data['Person_Firname']);
		}
		if (!empty($data['Person_Secname']))
		{
			$filter .= " and p.Person_SecName ilike (:Person_Secname||'%')";
			$params['Person_Secname'] = rtrim($data['Person_Secname']);
		}
		if (!empty($data['Person_BirthDay']))
		{
			$filter .= " and p.Person_BirthDay = :Person_BirthDay";
			$params['Person_BirthDay'] = $data['Person_BirthDay'];
		}
		if (!empty($data['HomeVisitStatus_id']))
		{
			if ($data['HomeVisitStatus_id'] == -1) {
				$filter .= " and hv.HomeVisitStatus_id = 1 and hv.CmpCallCard_id is not null";
			} elseif ($data['HomeVisitStatus_id'] == -2) {
				$filter .= " and hv.HomeVisitStatus_id is null";
			} elseif($data['HomeVisitStatus_id'] == 1) {
				$filter .= " and hv.HomeVisitStatus_id = 1 and hv.CmpCallCard_id is null";
			}
			else {
				$filter .= " and hv.HomeVisitStatus_id = :HomeVisitStatus_id";
				$params['HomeVisitStatus_id'] = $data['HomeVisitStatus_id'];
			}
		}
		if (!empty($data['HomeVisitCallType_id']))
		{
			$filter .= " and hv.HomeVisitCallType_id = :HomeVisitCallType_id";
			$params['HomeVisitCallType_id'] = $data['HomeVisitCallType_id'];
		}
		if (!empty($data['HomeVisit_setTimeFrom']))
		{
			$filter .= " and cast(hv.HomeVisit_setDT as time) >= cast(:HomeVisit_setTimeFrom as time)";
			$params['HomeVisit_setTimeFrom'] = $data['HomeVisit_setTimeFrom'];
		}
		if (!empty($data['HomeVisit_setTimeTo']))
		{
			$filter .= " and cast(hv.HomeVisit_setDT as time) <= cast(:HomeVisit_setTimeTo as time)";
			$params['HomeVisit_setTimeTo'] = $data['HomeVisit_setTimeTo'];
		}


		if ( ! empty($data['MedStaffFact_id']))
		{

			$tmpFilterMedPersonal_id = '';
			if ( ! empty($data['MedPersonal_id']) && $data['MedPersonal_id'] != -1) {
				$tmpFilterMedPersonal_id = " OR (msf.MedStaffFact_id IS NULL and mp.MedPersonal_id = :MedPersonal_id)";
				$params['MedPersonal_id'] = $data['MedPersonal_id'];
			}

			$filter .= " and ( msf.MedStaffFact_id = :MedStaffFact_id $tmpFilterMedPersonal_id)";
			$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
		}
		else if ( ! empty($data['MedPersonal_id'])) {
			if ($data['MedPersonal_id'] == -1) {
				$filter .= " and mp.MedPersonal_id is null";
			} else {
				$filter .= " and mp.MedPersonal_id = :MedPersonal_id";
				$params['MedPersonal_id'] = $data['MedPersonal_id'];
			}
		}


		/*if (!empty($data['LpuRegion_id'])) {
			$params['LpuRegion_id'] = $data['LpuRegion_id'];
			$filter .= " and HV.LpuRegion_id = :LpuRegion_id";
		}*/

		//Подразделение - врач из вызова на дом должен быть с выбранного участка
		if (!empty($data['LpuRegion_id']) || !empty($data['LpuBuilding_id'])) {
			$exists_filter = "";

			if (!empty($data['LpuRegion_id'])) {
				$params['LpuRegion_id'] = $data['LpuRegion_id'];
				//$filter .= " and coalesce(lr.LpuRegion_id, hv.LpuRegion_cid) = :LpuRegion_id";
				$filter .= " and hv.LpuRegion_cid = :LpuRegion_id";
				$exists_filter .= " and t3.LpuSection_id = lr.LpuSection_id";
			}
			if (!empty($data['LpuBuilding_id'])) {
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
				$exists_filter .= " and t3.LpuBuilding_id = :LpuBuilding_id";
			}

			$main_exists_filter = "exists (
				select
					t1.MedStaffRegion_id
				from
					v_MedStaffRegion t1
					inner join v_LpuRegion t2 on t2.LpuRegion_id = t1.LpuRegion_id
					inner join v_LpuSection t3 on t3.LpuSection_id = t2.LpuSection_id
					left join v_MedStaffFact t4 on t4.MedStaffFact_id = t1.MedStaffFact_id
				where
					coalesce(t1.MedPersonal_id, t4.MedPersonal_id) = hv.MedPersonal_id
					and (t1.MedStaffRegion_endDate is null or t1.MedStaffRegion_endDate > dbo.tzGetDate())
					{$exists_filter}
				limit 1
			)";

			if (!empty($data['LpuBuilding_id'])) {
				$filter .= " and {$main_exists_filter}";
			} else {
				$filter .= " and (hv.MedPersonal_id is null or lr.LpuSection_id is null or ({$main_exists_filter}))";
			}
		}

		if (!empty($data['LpuRegion_cid'])) {
			$params['LpuRegion_cid'] = $data['LpuRegion_cid'];
			$filter .= " and hv.LpuRegion_cid = :LpuRegion_cid ";
		}

		if (!empty($data['CallProfType_id'])) {
			$params['CallProfType_id'] = $data['CallProfType_id'];
			$filter .= " and hv.CallProfType_id = :CallProfType_id ";
		}

		$sql = "
			select
				hv.HomeVisit_id as \"HomeVisit_id\",
				hv.Person_id as \"Person_id\",
				to_char(hv.HomeVisit_setDT, 'dd.mm.yyyy') as \"HomeVisit_setDate\",
				p.PersonEvn_id as \"PersonEvn_id\",
				p.Server_id as \"Server_id\",
				rtrim(rtrim(p.Person_Surname) || ' ' || coalesce(rtrim(p.Person_Firname),'') || ' ' || coalesce(rtrim(p.Person_Secname),'')) as \"Person_FIO\",
				rtrim(p.Person_Surname) as \"Person_Surname\",
				rtrim(p.Person_Firname) as \"Person_Firname\",
				rtrim(p.Person_Secname) as \"Person_Secname\",
				to_char(p.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				rtrim(hv.Address_Address) as \"Address_Address\",
				coalesce(lrc.LpuRegion_Name,'') as \"LpuRegion_Name\",
				case
					when hv.HomeVisitStatus_id is null then 'Требует подтверждения'
					when hv.HomeVisitStatus_id = 1 and ccc.CmpCallCard_id is not null then 'Актив из СМП'
					else hvs.HomeVisitStatus_Name
				end as \"HomeVisitStatus_Name\",
				hv.HomeVisit_Phone as \"HomeVisit_Phone\",
				hvct.HomeVisitCallType_Name as \"HomeVisitCallType_Name\",
				hv.HomeVisit_Symptoms as \"HomeVisit_Symptoms\",
				hv.HomeVisitStatus_id as \"HomeVisitStatus_id\"
			from
				v_HomeVisit hv
				{$lpuJoin}
				left join v_PersonState p on hv.Person_id = p.Person_id
				left join lateral(
					select
						*
					from v_PersonCard_all
					where Person_id = hv.Person_id
						and LpuAttachType_id = 1 
						and hv.HomeVisit_insdt >= PersonCard_begDate
						and (hv.HomeVisit_insdt <= PersonCard_endDate or PersonCard_endDate IS NULL)
					limit 1
				) pc on true
				left join lateral(
					select
						*
					from v_MedStaffFact
					where MedStaffFact_id = hv.MedStaffFact_id
					limit 1
				) msf on true
				left join v_LpuRegion lr on lr.LpuRegion_id = hv.LpuRegion_id and lr.Lpu_id = l.Lpu_id
				left join v_LpuRegion lrc on lrc.LpuRegion_id = hv.LpuRegion_cid
				left join v_HomeVisitStatus hvs on hv.HomeVisitStatus_id = hvs.HomeVisitStatus_id
				left join v_HomeVisitCallType hvct on hv.HomeVisitCallType_id = hvct.HomeVisitCallType_id
				left join v_CmpCallCard ccc on ccc.CmpCallCard_id = hv.CmpCallCard_id
			where
				{$filter}
		";

		//echo getDebugSQL($sql, $params);die;

		return $this->queryResult($sql, $params);
	}
}