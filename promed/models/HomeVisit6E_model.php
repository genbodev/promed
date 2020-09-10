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
class HomeVisit6E_model extends swModel {
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
			$lpuJoin = " left join v_Lpu l (nolock) on hv.Lpu_id = l.Lpu_id ";
		} else {
			$lpuJoin = " inner join v_Lpu l (nolock) on hv.Lpu_id = l.Lpu_id ";
		}
		if (!empty($data['Person_Surname']))
		{
			$filter .= " and p.Person_SurName like (:Person_Surname+'%')";
			$params['Person_Surname'] = rtrim($data['Person_Surname']);
		}
		if (!empty($data['Person_Firname']))
		{
			$filter .= " and p.Person_FirName like (:Person_Firname+'%')";
			$params['Person_Firname'] = rtrim($data['Person_Firname']);
		}
		if (!empty($data['Person_Secname']))
		{
			$filter .= " and p.Person_SecName like (:Person_Secname+'%')";
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
				//$filter .= " and isnull(lr.LpuRegion_id, hv.LpuRegion_cid) = :LpuRegion_id";
				$filter .= " and hv.LpuRegion_cid = :LpuRegion_id";
				$exists_filter .= " and t3.LpuSection_id = lr.LpuSection_id";
			}
			if (!empty($data['LpuBuilding_id'])) {
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
				$exists_filter .= " and t3.LpuBuilding_id = :LpuBuilding_id";
			}

			$main_exists_filter = "exists (
				select top 1
					t1.MedStaffRegion_id
				from
					v_MedStaffRegion t1 with (nolock)
					inner join v_LpuRegion t2 with (nolock) on t2.LpuRegion_id = t1.LpuRegion_id
					inner join v_LpuSection t3 with(nolock) on t3.LpuSection_id = t2.LpuSection_id
					left join v_MedStaffFact t4 with (nolock) on t4.MedStaffFact_id = t1.MedStaffFact_id
				where
					ISNULL(t1.MedPersonal_id, t4.MedPersonal_id) = hv.MedPersonal_id
					and (t1.MedStaffRegion_endDate is null or t1.MedStaffRegion_endDate > dbo.tzGetDate())
					{$exists_filter}
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
				hv.HomeVisit_id,
				hv.Person_id,
				convert(varchar(10), hv.HomeVisit_setDT, 104) as HomeVisit_setDate,
				p.PersonEvn_id,
				p.Server_id,
				rtrim(rtrim(p.Person_Surname) + ' ' + isnull(rtrim(p.Person_Firname),'') + ' ' + isnull(rtrim(p.Person_Secname),'')) as Person_FIO,
				rtrim(p.Person_Surname) as Person_Surname,
				rtrim(p.Person_Firname) as Person_Firname,
				rtrim(p.Person_Secname) as Person_Secname,
				convert(varchar(10), p.Person_BirthDay, 104) as Person_BirthDay,
				rtrim(hv.Address_Address) as Address_Address,
				isnull(lrc.LpuRegion_Name,'') as LpuRegion_Name,
				case
					when hv.HomeVisitStatus_id is null then 'Требует подтверждения'
					when hv.HomeVisitStatus_id = 1 and ccc.CmpCallCard_id is not null then 'Актив из СМП'
					else hvs.HomeVisitStatus_Name
				end as HomeVisitStatus_Name,
				hv.HomeVisit_Phone,
				hvct.HomeVisitCallType_Name,
				hv.HomeVisit_Symptoms,
				hv.HomeVisitStatus_id,
				evpl.EvnVizitPL_pid
			from
				v_HomeVisit hv with (nolock)
				{$lpuJoin}
				left join v_PersonState p with (nolock) on hv.Person_id = p.Person_id
				outer apply(
					select top 1 *
					from v_PersonCard_all with(nolock)
					where Person_id = hv.Person_id
						and LpuAttachType_id = 1 
						and hv.HomeVisit_insdt >= PersonCard_begDate
						and (hv.HomeVisit_insdt <= PersonCard_endDate or PersonCard_endDate IS NULL)
				) pc
				outer apply(
					select top 1 *
					from v_MedStaffFact with(nolock)
					where MedStaffFact_id = hv.MedStaffFact_id
				) msf
				left join v_LpuRegion lr with (nolock) on lr.LpuRegion_id = hv.LpuRegion_id and lr.Lpu_id = l.Lpu_id
				left join v_LpuRegion lrc with (nolock) on lrc.LpuRegion_id = hv.LpuRegion_cid
				left join v_HomeVisitStatus hvs with (nolock) on hv.HomeVisitStatus_id = hvs.HomeVisitStatus_id
				left join v_HomeVisitCallType hvct with (nolock) on hv.HomeVisitCallType_id = hvct.HomeVisitCallType_id
				left join v_CmpCallCard ccc with (nolock) on ccc.CmpCallCard_id = hv.CmpCallCard_id
				left join v_EvnVizitPL evpl with (nolock) on evpl.HomeVisit_id=hv.HomeVisit_id
			where
				{$filter}
		";

		//echo getDebugSQL($sql, $params);die;

		return $this->queryResult($sql, $params);
	}
}