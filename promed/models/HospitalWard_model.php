<?php
/**
* Модель - Больничные палаты
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      All
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Александр Пермяков 
* @version      02.02.2011
*/

class HospitalWard_model extends swModel
{
	/**
	 * Конструируюсь!
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	* Возвращает палаты отделения действующие и свободные на данный момент времени
	* 
	*/
	function getLpuSectionWardList($data)
{
	$query = "
			Declare @curdate as datetime = cast(dbo.tzGetDate() as DATE);
			with busyCount as (
				select
					EvnSection.LpuSectionWard_id,
					count(EvnSection.EvnSection_id) as busy
				from
					v_EvnSection EvnSection with (nolock)
					LEFT JOIN v_EvnPS EvnPS with (nolock) on EvnPS.EvnPS_id = EvnSection.EvnSection_pid
				where
					EvnSection.Lpu_id = :Lpu_id
					and EvnSection.LpuSection_id = :LpuSection_id
					and EvnPS.EvnPS_id is not null
					and EvnSection.LpuSectionWard_id is not null
					and cast(EvnSection_setDate as DATE) <= @curdate
					and (EvnSection_disDate > @curdate or EvnSection_disDate is null)
				group by
					EvnSection.LpuSectionWard_id
			)
			select
				LSW.LpuSectionWard_id,
				LSW.LpuSection_id,
				LSW.LpuSectionWard_Name,
				LSW.LpuWardType_id,
				LSW.Sex_id
			from
				v_LpuSectionWard LSW with (nolock)
				left join busyCount with(nolock) on busyCount.LpuSectionWard_id = LSW.LpuSectionWard_id
			where
				LSW.LpuSection_id = :LpuSection_id
				and (cast(:date as date) between cast(LSW.LpuSectionWard_setDate as date) and cast(isnull(LSW.LpuSectionWard_disDate,:date) as date))
				and (LSW.LpuSectionWard_BedCount - LSW.LpuSectionWard_BedRepair - isnull(busyCount.busy, 0)) > 0
			order by
				LSW.LpuSectionWard_Name
		";

	$queryParams = array(
		'Lpu_id' => $data['Lpu_id'],
		'LpuSection_id' => $data['LpuSection_id'],
		'date' => $data['date']
	);

	$result = $this->db->query($query, $queryParams);

	if ( ! is_object($result) )
	{
		//echo getDebugSQL($query, $queryParams);
		return false;
	}

	return $result->result('array');
}
	/**
	 * Возвращает палаты отделения действующие и свободные на данный момент времени
	 *
	 */
	function getLpuSectionWardSelectList($data)
	{
		$query = "
			select top 1
				LU.LpuUnitType_SysNick
			from
				v_LpuUnit LU with(nolock)
				inner join v_LpuSection LS with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
			where
				LS.LpuSection_id = :LpuSection_uid
		";
		$queryParams = array(
			'LpuSection_uid' => $data['LpuSection_uid']
		);
		$result = $this->db->query($query, $queryParams)->result('array')[0];

		$arrayLpuUnitType = "";
		$LpuUnitType_SysNick = $result['LpuUnitType_SysNick'];
		switch ($LpuUnitType_SysNick) {
			case 'priem':
				$arrayLpuUnitType = "('2', '3', '4', '5')";
				break;
			case 'stac':
			case 'dstac':
				$arrayLpuUnitType = "('2', '3')";
				break;
			case 'polka':
			case 'hstac':
			case 'pstac':
				$arrayLpuUnitType = "('4', '5')";
				break;
		}
		$where = '';
		if ($data['WithoutChildLpuSectionAge']){
			$where = 'and isnull(LS.LpuSectionAge_id, 1) <> 2';
		}		
		$query = "
			Declare @curdate as datetime = cast(dbo.tzGetDate() as DATE);
			with busyCount as (
				select
					EvnSection.LpuSectionWard_id,
					count(EvnSection.EvnSection_id) as busy
				from
					v_EvnSection EvnSection with (nolock)
					LEFT JOIN v_EvnPS EvnPS with (nolock) on EvnPS.EvnPS_id = EvnSection.EvnSection_pid
				where
					EvnSection.Lpu_id = :Lpu_id
					and (:LpuSection_id is null or EvnSection.LpuSection_id = :LpuSection_id)
					and EvnPS.EvnPS_id is not null
					and EvnSection.LpuSectionWard_id is not null
					and cast(EvnSection_setDate as DATE) <= @curdate
					and (EvnSection_disDate > @curdate or EvnSection_disDate is null)
				group by
					EvnSection.LpuSectionWard_id
			)
			select
				LSW.LpuSectionWard_id,
				LSW.LpuSection_id as Group_id,
				LS.LpuSection_Name as Group_Name,
				LSW.LpuSectionWard_Name as Ward_Num,
				(LSW.LpuSectionWard_BedCount - LSW.LpuSectionWard_BedRepair - isnull(busyCount.busy, 0)) as WardCount_Free,
				LSW.LpuSectionWard_BedCount as WardCount_All,
				lswc.LpuSectionWardCount_Free as LpuSectionWardCount_Free,
				lswc.LpuSectionWardCount_All as LpuSectionWardCount_All,
				case when (LSW.Sex_id is null) or (LSW.Sex_id = 3)
					then '3'
					else case when LSW.Sex_id = 1 then '1' else '2' end
				end as Ward_Type,
				case when ((lswf.Common > 0) or (lswf.Male > 0 and lswf.Female > 0))
					then '3'
					else case when (lswf.Male > 0) then '1' else '2' end
				end as Group_Type
			from
				v_LpuSectionWard LSW with (nolock)
				left join busyCount with(nolock) on busyCount.LpuSectionWard_id = LSW.LpuSectionWard_id
				inner join v_LpuSection LS with(nolock) on LS.LpuSection_id = LSW.LpuSection_id
				inner join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				inner join v_LpuUnitType LUT with (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
				inner join (
					select distinct
						LS.LpuSection_id,
						sum(LSW.LpuSectionWard_BedCount) - isnull(sum(LSW.LpuSectionWard_BedRepair),0) - isnull(sum(busyCount.busy),0) as LpuSectionWardCount_Free,
						sum(LSW.LpuSectionWard_BedCount) as LpuSectionWardCount_All
					from
						v_LpuSectionWard LSW with (nolock)
						left join busyCount with(nolock) on busyCount.LpuSectionWard_id = LSW.LpuSectionWard_id
						inner join v_LpuSection LS with(nolock) on LS.LpuSection_id = LSW.LpuSection_id
					where
						(LSW.LpuSectionWard_BedCount - LSW.LpuSectionWard_BedRepair - isnull(busyCount.busy, 0)) > 0
					group by
						LS.LpuSection_id
				) lswc on lswc.LpuSection_id = LSW.LpuSection_id
				inner join (
					select distinct
						LpuSection_id,
						sum(case when (Sex_id is null) or (Sex_id = 3) then 1 else 0 end) as Common,
						sum(case when (Sex_id = 1) then 1 else 0 end) as Male,
						sum(case when (Sex_id = 2) then 1 else 0 end) as Female
					from
						v_LpuSectionWard with (nolock)
					group by
						LpuSection_id
				) lswf on lswf.LpuSection_id = LSW.LpuSection_id
				left join v_PersonState PS with (nolock) on PS.Person_id = :Person_id
			where
				(:LpuSection_id is null or LSW.LpuSection_id = :LpuSection_id)
				and LS.Lpu_id = :Lpu_id
				and ( (LSW.Sex_id is null) or (PS.Sex_id is null) or (LSW.Sex_id in (PS.Sex_id, 3)) )
				$where
				and (cast(@curdate as date) between cast(LSW.LpuSectionWard_setDate as date) and cast(isnull(LSW.LpuSectionWard_disDate,@curdate) as date))
				and (LSW.LpuSectionWard_BedCount - LSW.LpuSectionWard_BedRepair - isnull(busyCount.busy, 0)) > 0
				and LUT.LpuUnitType_Code in $arrayLpuUnitType
			order by
				LS.LpuSection_Name
		";

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'Person_id' => $data['Person_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) )
		{
			return false;
		}

		return $result->result('array');
	}
	/**
	 * МАРМ-версия \ MSSQL \ POSTGRE
	 * Возвращает палаты отделения действующие и свободные на данный момент времени
	 *
	 */
	function mGetLpuSectionWardList($data)
	{
		$filter = "";
		if(!empty($data['EvnPS_id'])) {
			$filter = "union all 
			select distinct
				case when busyCount.EvnPS_id = :EvnPS_id then 'true'  else 'false' end as CurrentLpuSectionWard,
				LSW.LpuSectionWard_id,
				LSW.LpuSection_id,
				LSW.LpuSectionWard_Name,
				LSW.LpuWardType_id,
				lwp.LpuWardType_Name,
				LSW.Sex_id,
				LSW.LpuSectionWard_BedCount as LpuSectionWard_TotalBedCount,
				LSW.LpuSectionWard_BedCount - LSW.LpuSectionWard_BedRepair - isnull(busyCount.busy, 0) as LpuSectionWard_FreeBedCount
			from
				v_LpuSectionWard LSW with (nolock)
				left join busyCount with(nolock) on busyCount.LpuSectionWard_id = LSW.LpuSectionWard_id
				left join LpuWardType lwp with (nolock) on lwp.LpuWardType_id = LSW.LpuWardType_id
			where
				LSW.LpuSection_id = :LpuSection_id
				and busyCount.EvnPS_id = :EvnPS_id
				and @curdate between isnull(LSW.LpuSectionWard_setDate, '2030-01-01') and isnull(LSW.LpuSectionWard_disDate, '2030-01-01') ";
		}
		$query = "
			Declare @curdate as datetime = cast(dbo.tzGetDate() as DATE);
			with busyCount as (
				select
					EvnSection.LpuSectionWard_id,
					EvnPS.EvnPS_id,
					count(*) over (partition by EvnSection.LpuSectionWard_id ) as busy
				from
					v_EvnSection EvnSection with (nolock)
					LEFT JOIN v_EvnPS EvnPS with (nolock) on EvnPS.EvnPS_id = EvnSection.EvnSection_pid
				where
					EvnSection.Lpu_id = :Lpu_id
					and EvnSection.LpuSection_id = :LpuSection_id
					and EvnPS.EvnPS_id is not null
					and EvnSection.LpuSectionWard_id is not null
					and cast(EvnSection_setDate as DATE) <= @curdate
					and (EvnSection_disDate > @curdate or EvnSection_disDate is null)
				group by
					EvnSection.LpuSectionWard_id,
					EvnPS.EvnPS_id
			)
			select distinct * from ( --чтобы избежать дублей при юнионе
			select distinct
				case when busyCount.EvnPS_id = :EvnPS_id then 'true'  else 'false' end as CurrentLpuSectionWard,
				LSW.LpuSectionWard_id,
				LSW.LpuSection_id,
				LSW.LpuSectionWard_Name,
				LSW.LpuWardType_id,
				lwp.LpuWardType_Name,
				LSW.Sex_id,
				LSW.LpuSectionWard_BedCount as LpuSectionWard_TotalBedCount,
				LSW.LpuSectionWard_BedCount - LSW.LpuSectionWard_BedRepair - isnull(busyCount.busy, 0) as LpuSectionWard_FreeBedCount
			from
				v_LpuSectionWard LSW with (nolock)
				left join busyCount with(nolock) on busyCount.LpuSectionWard_id = LSW.LpuSectionWard_id
				left join LpuWardType lwp with (nolock) on lwp.LpuWardType_id = LSW.LpuWardType_id
			where
				LSW.LpuSection_id = :LpuSection_id
				and @curdate between isnull(LSW.LpuSectionWard_setDate, '2030-01-01') and isnull(LSW.LpuSectionWard_disDate, '2030-01-01') 
			$filter
			) as t1
		";

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'EvnPS_id' => $data['EvnPS_id']
		);

		$result = $this->db->query($query, $queryParams)->result('array');

		return $result;
	}


	
	/**
	* Возвращает палаты отделения
	* 
	*/
	function getHospitalWardList($data)
	{
		$filter = '';
		if (isset($data['notCloseWard']) && $data['notCloseWard']) {
			$filter = ' and (LpuSectionWard_disDate is null or LpuSectionWard_disDate >= dbo.tzGetDate())';
		}

		$query = "
			select
				LpuSectionWard_id,
				LpuSection_id,
				LpuSectionWard_Name,
				LpuWardType_id,
				Sex_id
			from
				v_LpuSectionWard with(nolock)
			where
				LpuSection_id = :LpuSection_id
				{$filter}
			order by
				LpuSectionWard_Name
		";

		$queryParams = array(
			'LpuSection_id' => $data['LpuSection_id'],
		);

		$result = $this->db->query($query, $queryParams);
		if ( ! is_object($result) )
		{
			//echo getDebugSQL($query, $queryParams);
			return false;
		}
		return $result->result('array');
	}

	/**
	* Возвращает количество коек в отделении (всего и занятых на текущий момент)
	*/
	function getCountBedLpuSection($data)
	{
		$query = "
			select
				isnull(SUM(LpuSectionWard_BedCount), 0) as LpuSection_BedCount,
				LpuSection_BedCount_men = (
					select
						isnull(SUM(LpuSectionWard_BedCount), 0) as LpuSection_BedCount_men
					from
						v_LpuSectionWard with(nolock)
					where
						LpuSection_id = :LpuSection_id
						and Sex_id = 1
				),
				LpuSection_BedCount_women = (
					select
						isnull(SUM(LpuSectionWard_BedCount), 0) as LpuSection_BedCount_women
					from
						v_LpuSectionWard with(nolock)
					where
						LpuSection_id = :LpuSection_id
						and Sex_id = 2
				),
				Evn_cnt = (
					select
						count(*) as Evn_cnt
					from
						v_EvnSection with(nolock)
					where
						LpuSection_id = :LpuSection_id
						and EvnSection_disDate is null
						and LpuSectionWard_id is not null
				),
				Evn_cnt_men = (
					select
						count(*)
					from
						v_EvnSection EvnSection with(nolock)
						LEFT JOIN v_PersonSex PersonSex with(nolock) on PersonSex.Person_id = EvnSection.Person_id
					where
						EvnSection.LpuSection_id = :LpuSection_id
						and EvnSection_disDate is null
						and LpuSectionWard_id is not null
						and PersonSex.Sex_id = 1
				),
				Evn_cnt_women = (
					select
						count(*)
					from
						v_EvnSection EvnSection with(nolock)
						LEFT JOIN v_PersonSex PersonSex with(nolock) on PersonSex.Person_id = EvnSection.Person_id
					where
						EvnSection.LpuSection_id = :LpuSection_id
						and EvnSection_disDate is null
						and LpuSectionWard_id is not null
						and PersonSex.Sex_id = 2
				)
			from
				v_LpuSectionWard with(nolock)
			where
				LpuSection_id = :LpuSection_id
		";
		
		$queryParams = array(
			'LpuSection_id' => $data['LpuSection_id'],
			'date'			=> $data['date']
		);
		
		$result = $this->db->query($query, $queryParams);
		
		if ( ! is_object($result) )
		{
			return false;
		}
		return $result->result('array');
	}
	
	/**
	* Возвращает количество коек в палате (всего и свободных на текущий момент)
	*/
	function getCountBedLpuSectionWard($data, $date)
	{
		$query = "
			declare @date datetime = cast(dbo.tzGetDate() as date);

			select
				LpuSectionWard.LpuSectionWard_BedCount as onlyBed_cnt,
				LpuSectionWard.LpuSectionWard_BedCount - es.Evn_cnt as freedomBed_cnt
			from
				v_LpuSectionWard LpuSectionWard with (nolock)
				outer apply (
					select
						count(EvnSection_id) as Evn_cnt
					from
						v_EvnSection with (nolock)
					where
						Lpu_id = :Lpu_id
						and LpuSection_id = :LpuSection_id
						and LpuSectionWard_id = :LpuSectionWard_id
						and EvnSection_setDate <= @date
						and IsNull(EvnSection_disDate, @date + 1) > @date
				) es
			where
				LpuSectionWard.LpuSection_id = :LpuSection_id
				and LpuSectionWard.LpuSectionWard_id = :LpuSectionWard_id
		";
	
	
		$queryParams = array(
			'Lpu_id'			=> $data['Lpu_id'],
			'LpuSection_id'		=> $data['LpuSection_id'],
			'LpuSectionWard_id'	=> $data['LpuSectionWard_id'],
			'date' => $date
		);
		
		$result = $this->db->query($query, $queryParams);
		
		if ( ! is_object($result) )
		{
			return false;
		}
		return $result->result('array');
	}

	/**
	* Возвращает количество коек в палате
	*/
	function getLpuSectionWardBedCount($data)
	{
		if(!empty($data['EvnSection_id']) && empty($data['EvnPS_id'])) 
		{
			// считаем койки для движения 
			//and (EvnSection.EvnSection_disDate is null or cast(EvnSection.EvnSection_disDate as DATE) >= cast(:date as DATE))

			$query = "
				select
					LpuSectionWard.Sex_id,
					LpuSectionWard.LpuSectionWard_BedCount - LpuSectionWard.LpuSectionWard_BedRepair as cnt,
					busyCount.busy
				from
					LpuSectionWard with (nolock)
					outer apply (
						select
							count(EvnSection.EvnSection_id) as busy
						from
							v_EvnSection EvnSection with (nolock)
							LEFT JOIN v_EvnPS EvnPS with (nolock) on EvnPS.EvnPS_id = EvnSection.EvnSection_pid	
						where
							EvnSection.LpuSection_id = :LpuSection_id
							and EvnSection.LpuSectionWard_id = :LpuSectionWard_id
							and EvnPS.EvnPS_id is not null
							and cast(EvnSection_setDate as DATE) <= cast(:date as DATE)
							and EvnSection_disDate is null
					) busyCount
				where
					LpuSectionWard.LpuSection_id = :LpuSection_id
					and LpuSectionWard.LpuSectionWard_id = :LpuSectionWard_id
			";
		} else if(!empty($data['EvnPS_id'])) {
			// считаем койки для приёмного отделения
			$query = "
				select
					LpuSectionWard.Sex_id,
					LpuSectionWard.LpuSectionWard_BedCount - LpuSectionWard.LpuSectionWard_BedRepair as cnt,
					busyCount.busy
				from
					LpuSectionWard with (nolock)
					outer apply (
						select
							count(EvnPS.EvnPS_id) as busy
						from
							v_EvnPS EvnPS with (nolock)
						where
							EvnPS.LpuSection_pid = :LpuSection_id
							and EvnPS.LpuSectionWard_id = :LpuSectionWard_id
							and EvnPS.LpuSection_id is null
							and EvnPS.PrehospWaifRefuseCause_id is null
							and cast(EvnPS.EvnPS_setDate as DATE) <= cast(:date as DATE)
							and EvnPS.EvnPS_disDate is null
					) busyCount
				where
					LpuSectionWard.LpuSection_id = :LpuSection_id
					and LpuSectionWard.LpuSectionWard_id = :LpuSectionWard_id
			";
		} else 
			return false;
	
	
		$queryParams = array(
			'LpuSection_id'		=> $data['LpuSection_id'],
			'LpuSectionWard_id'	=> $data['LpuSectionWard_id'],
			'date' => $data['date']
		);
		
		$result = $this->db->query($query, $queryParams);
		
		if ( ! is_object($result) )
		{
			return false;
		}
		return $result->result('array');
	}

	/**
	 * МАРМ-версия \ MSSQL \ POSTGRE
	 * Возвращает информацию по палатам отделения
	 */
	function mGetHospitalWardList($data)
	{
		$filter = '';
		if (isset($data['notCloseWard']) && $data['notCloseWard']) {
			$filter = ' and (LpuSectionWard_disDate is null or LpuSectionWard_disDate >= dbo.tzGetDate())';
		}

		$queryParams = array('LpuSection_id'=> $data['LpuSection_id']);

		$query = "
			declare @date datetime = cast(dbo.tzGetDate() as date);

			select
				lsw.LpuSectionWard_id,
				lsw.LpuSection_id,
				lsw.LpuSectionWard_Name,
				lsw.LpuWardType_id,
				case when lsw.LpuWardType_id = 2 then 'true' else 'false' end as isComfortable,
				lsw.Sex_id,
				beds.TotalBeds_Count,
				beds.FreeBeds_Count
			from v_LpuSectionWard lsw with(nolock)
			outer apply (
				select top 1
				lsw2.LpuSectionWard_BedCount as TotalBeds_Count,
				lsw2.LpuSectionWard_BedCount - es.Evn_cnt as FreeBeds_Count
				from v_LpuSectionWard lsw2 with (nolock)
				outer apply (
					select
						count(EvnSection_id) as Evn_cnt
					from
						v_EvnSection es with (nolock)
					where
						es.LpuSection_id = lsw.LpuSection_id
						and es.LpuSectionWard_id = lsw.LpuSectionWard_id
						and es.EvnSection_setDate <= @date
						and IsNull(es.EvnSection_disDate, @date + 1) > @date
				) es
				where
					lsw2.LpuSection_id = lsw.LpuSection_id
					and lsw2.LpuSectionWard_id = lsw.LpuSectionWard_id
			) beds
			where 
				lsw.LpuSection_id = :LpuSection_id
				{$filter}
			order by lsw.LpuSectionWard_Name
		";

		return $this->queryResult($query, $queryParams);
	}
}