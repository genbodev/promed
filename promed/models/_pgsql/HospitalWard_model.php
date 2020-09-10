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
class HospitalWard_model extends swPgModel
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
			with busyCount as (
				select
					EvnSection.LpuSectionWard_id as LpuSectionWard_id,
					count(EvnSection.EvnSection_id) as busy
				from
					v_EvnSection EvnSection
					LEFT JOIN v_EvnPS EvnPS on EvnPS.EvnPS_id = EvnSection.EvnSection_pid
				where
					EvnSection.Lpu_id = :Lpu_id
					and EvnSection.LpuSection_id = :LpuSection_id
					and EvnPS.EvnPS_id is not null
					and EvnSection.LpuSectionWard_id is not null
					and cast(EvnSection_setDate as DATE) <= cast(dbo.tzGetDate() as DATE)::timestamp(3)
					and (EvnSection_disDate > cast(dbo.tzGetDate() as DATE)::timestamp(3) or EvnSection_disDate is null)
				group by
					EvnSection.LpuSectionWard_id
			)
			select
				LSW.LpuSectionWard_id as \"LpuSectionWard_id\",
				LSW.LpuSection_id as \"LpuSection_id\",
				LSW.LpuSectionWard_Name as \"LpuSectionWard_Name\",
				LSW.LpuWardType_id as \"LpuWardType_id\",
				LSW.Sex_id as \"Sex_id\"
			from
				v_LpuSectionWard LSW
				left join busyCount on busyCount.LpuSectionWard_id = LSW.LpuSectionWard_id
			where
				LSW.LpuSection_id = :LpuSection_id
				and (cast(:date as date) between cast(LSW.LpuSectionWard_setDate as date) and cast(coalesce(LSW.LpuSectionWard_disDate,:date) as date))
				and (LSW.LpuSectionWard_BedCount - LSW.LpuSectionWard_BedRepair - coalesce(busyCount.busy, 0)) > 0
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
			select
				LU.LpuUnitType_SysNick as \"LpuUnitType_SysNick\"
			from
				v_LpuUnit LU
				inner join v_LpuSection LS on LU.LpuUnit_id = LS.LpuUnit_id
			where
				LS.LpuSection_id = :LpuSection_uid
			limit 1
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
			$where = 'and coalesce(LS.LpuSectionAge_id, 1) <> 2';
		}
		
		$query = "
			with busyCount as (
				select
					EvnSection.LpuSectionWard_id as LpuSectionWard_id,
					count(EvnSection.EvnSection_id) as busy
				from
					v_EvnSection EvnSection
					LEFT JOIN v_EvnPS EvnPS on EvnPS.EvnPS_id = EvnSection.EvnSection_pid
				where
					EvnSection.Lpu_id = :Lpu_id
					and (:LpuSection_id is null or EvnSection.LpuSection_id = :LpuSection_id)
					and EvnPS.EvnPS_id is not null
					and EvnSection.LpuSectionWard_id is not null
					and cast(EvnSection_setDate as DATE) <= cast(dbo.tzGetDate() as DATE)
					and (EvnSection_disDate > cast(dbo.tzGetDate() as DATE) or EvnSection_disDate is null)
				group by
					EvnSection.LpuSectionWard_id
			)
			select
				LSW.LpuSectionWard_id as \"LpuSectionWard_id\" ,
				LSW.LpuSection_id as \"Group_id\",
				LS.LpuSection_Name as \"Group_Name\",
				LSW.LpuSectionWard_Name as \"Ward_Num\",
				(LSW.LpuSectionWard_BedCount - LSW.LpuSectionWard_BedRepair - coalesce(busyCount.busy, 0)) as \"WardCount_Free\",
				LSW.LpuSectionWard_BedCount as \"WardCount_All\",
				lswc.LpuSectionWardCount_Free as \"LpuSectionWardCount_Free\",
				lswc.LpuSectionWardCount_All as \"LpuSectionWardCount_All\",
				case when (LSW.Sex_id is null) or (LSW.Sex_id = 3)
					then '3'
					else case when LSW.Sex_id = 1 then '1' else '2' end
				end as \"Ward_Type\",
				case when ((lswf.Common > 0) or (lswf.Male > 0 and lswf.Female > 0))
					then '3'
					else case when (lswf.Male > 0) then '1' else '2' end
				end as \"Group_Type\"
			from
				v_LpuSectionWard LSW
				left join busyCount on busyCount.LpuSectionWard_id = LSW.LpuSectionWard_id
				inner join v_LpuSection LS on LS.LpuSection_id = LSW.LpuSection_id
				inner join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
				inner join v_LpuUnitType LUT on LUT.LpuUnitType_id = LU.LpuUnitType_id
				inner join (
					select distinct
						LS.LpuSection_id as LpuSection_id,
						sum(LSW.LpuSectionWard_BedCount) - coalesce(sum(LSW.LpuSectionWard_BedRepair),0) - coalesce(sum(busyCount.busy),0) as LpuSectionWardCount_Free,
						sum(LSW.LpuSectionWard_BedCount) as LpuSectionWardCount_All
					from
						v_LpuSectionWard LSW
						left join busyCount on busyCount.LpuSectionWard_id = LSW.LpuSectionWard_id
						inner join v_LpuSection LS on LS.LpuSection_id = LSW.LpuSection_id
					where
						(LSW.LpuSectionWard_BedCount - LSW.LpuSectionWard_BedRepair - coalesce(busyCount.busy, 0)) > 0
					group by
						LS.LpuSection_id
				) lswc on lswc.LpuSection_id = LSW.LpuSection_id
				inner join (
					select distinct
						LpuSection_id as LpuSection_id,
						sum(case when (Sex_id is null) or (Sex_id = 3) then 1 else 0 end) as Common,
						sum(case when (Sex_id = 1) then 1 else 0 end) as Male,
						sum(case when (Sex_id = 2) then 1 else 0 end) as Female
					from
						v_LpuSectionWard
					group by
						LpuSection_id
				) lswf on lswf.LpuSection_id = LSW.LpuSection_id
				left join v_PersonState PS on PS.Person_id = :Person_id
			where
				(:LpuSection_id is null or LSW.LpuSection_id = :LpuSection_id)
				and LS.Lpu_id = :Lpu_id
				and ( (LSW.Sex_id is null) or (PS.Sex_id is null) or (LSW.Sex_id in (PS.Sex_id, 3)) )
				$where
				and (cast(dbo.tzGetDate() as DATE) between cast(LSW.LpuSectionWard_setDate as date) and cast(coalesce(LSW.LpuSectionWard_disDate,cast(dbo.tzGetDate() as DATE)) as date))
				and (LSW.LpuSectionWard_BedCount - LSW.LpuSectionWard_BedRepair - coalesce(busyCount.busy, 0)) > 0
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
				case when busyCount.EvnPS_id = :EvnPS_id then 'true'  else 'false' end as \"CurrentLpuSectionWard\",
				LSW.LpuSectionWard_id as \"LpuSectionWard_id\",
				LSW.LpuSection_id as \"LpuSection_id\",
				LSW.LpuSectionWard_Name as \"LpuSectionWard_Name\",
				LSW.LpuWardType_id as \"LpuWardType_id\",
				lwp.LpuWardType_Name as \"LpuWardType_Name\",
				LSW.Sex_id as \"Sex_id\",
				LSW.LpuSectionWard_BedCount as \"LpuSectionWard_TotalBedCount\",
				LSW.LpuSectionWard_BedCount - LSW.LpuSectionWard_BedRepair - coalesce(busyCount.busy, 0) as \"LpuSectionWard_FreeBedCount\"
			from
				v_LpuSectionWard LSW
				left join busyCount on busyCount.LpuSectionWard_id = LSW.LpuSectionWard_id
				left join LpuWardType lwp on lwp.LpuWardType_id = LSW.LpuWardType_id
			where
				LSW.LpuSection_id = :LpuSection_id
				and busyCount.EvnPS_id = :EvnPS_id
				and cast(dbo.tzGetDate() as DATE) between coalesce(LSW.LpuSectionWard_setDate, '2030-01-01') and coalesce(LSW.LpuSectionWard_disDate, '2030-01-01') ";
        }
        $query = "
			with busyCount as (
				select
					EvnSection.LpuSectionWard_id as LpuSectionWard_id,
					EvnPS.EvnPS_id as EvnPS_id,
					count(*) over (partition by EvnSection.LpuSectionWard_id ) as busy
				from
					v_EvnSection EvnSection
					LEFT JOIN v_EvnPS EvnPS on EvnPS.EvnPS_id = EvnSection.EvnSection_pid
				where
					EvnSection.Lpu_id = :Lpu_id
					and EvnSection.LpuSection_id = :LpuSection_id
					and EvnPS.EvnPS_id is not null
					and EvnSection.LpuSectionWard_id is not null
					and cast(EvnSection_setDate as DATE) <= cast(dbo.tzGetDate() as DATE)
					and (EvnSection_disDate > cast(dbo.tzGetDate() as DATE) or EvnSection_disDate is null)
				group by
					EvnSection.LpuSectionWard_id,
					EvnPS.EvnPS_id
			)
			select distinct * from ( --чтобы избежать дублей при юнионе
			select distinct
				case when busyCount.EvnPS_id = :EvnPS_id then 'true'  else 'false' end as \"CurrentLpuSectionWard\",
				LSW.LpuSectionWard_id as \"LpuSectionWard_id\",
				LSW.LpuSection_id as \"LpuSection_id\",
				LSW.LpuSectionWard_Name as \"LpuSectionWard_Name\",
				LSW.LpuWardType_id as \"LpuWardType_id\",
				lwp.LpuWardType_Name as \"LpuWardType_Name\",
				LSW.Sex_id as \"Sex_id\",
				LSW.LpuSectionWard_BedCount as \"LpuSectionWard_TotalBedCount\",
				LSW.LpuSectionWard_BedCount - LSW.LpuSectionWard_BedRepair - coalesce(busyCount.busy, 0) as \"LpuSectionWard_FreeBedCount\"
			from
				v_LpuSectionWard LSW
				left join busyCount on busyCount.LpuSectionWard_id = LSW.LpuSectionWard_id
				left join LpuWardType lwp on lwp.LpuWardType_id = LSW.LpuWardType_id
			where
				LSW.LpuSection_id = :LpuSection_id
				and cast(dbo.tzGetDate() as DATE) between coalesce(LSW.LpuSectionWard_setDate, '2030-01-01') and coalesce(LSW.LpuSectionWard_disDate, '2030-01-01') 
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
				LpuSectionWard_id as \"LpuSectionWard_id\",
				LpuSection_id as \"LpuSection_id\",
				LpuSectionWard_Name as \"LpuSectionWard_Name\",
				LpuWardType_id as \"LpuWardType_id\",
				Sex_id as \"Sex_id\"
			from
				v_LpuSectionWard
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
				coalesce(SUM(LpuSectionWard_BedCount), 0) as \"LpuSection_BedCount\",

				(
					select
				        coalesce(SUM(LpuSectionWard_BedCount), 0)
				    from
				        v_LpuSectionWard
				    where
				        LpuSection_id = :LpuSection_id
				        and Sex_id = 1
				) as \"LpuSection_BedCount_men\",

				(
					select
						coalesce(SUM(LpuSectionWard_BedCount), 0)
					from
						v_LpuSectionWard
					where
						LpuSection_id = :LpuSection_id
						and Sex_id = 2
				) as \"LpuSection_BedCount_women\",

				(
					select
						count(*)
					from
						v_EvnSection
					where
						LpuSection_id = :LpuSection_id
						and EvnSection_disDate is null
						and LpuSectionWard_id is not null
				) as \"Evn_cnt\",

				(
					select
						count(*)
					from
						v_EvnSection EvnSection
						LEFT JOIN v_PersonSex PersonSex on PersonSex.Person_id = EvnSection.Person_id
					where
						EvnSection.LpuSection_id = :LpuSection_id
						and EvnSection_disDate is null
						and LpuSectionWard_id is not null
						and PersonSex.Sex_id = 1
				) as \"Evn_cnt_men\",

				(
					select
						count(*)
					from
						v_EvnSection EvnSection
						LEFT JOIN v_PersonSex PersonSex on PersonSex.Person_id = EvnSection.Person_id
					where
						EvnSection.LpuSection_id = :LpuSection_id
						and EvnSection_disDate is null
						and LpuSectionWard_id is not null
						and PersonSex.Sex_id = 2
				) as \"Evn_cnt_women\"
			from
				v_LpuSectionWard
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
			select
				LpuSectionWard.LpuSectionWard_BedCount as \"onlyBed_cnt\",
				LpuSectionWard.LpuSectionWard_BedCount - es.Evn_cnt as \"freedomBed_cnt\"
			from
				v_LpuSectionWard LpuSectionWard 
				left join lateral (
					select
						count(EvnSection_id) as Evn_cnt
					from
						v_EvnSection
					where
						Lpu_id = :Lpu_id
						and LpuSection_id = :LpuSection_id
						and LpuSectionWard_id = :LpuSectionWard_id
						and EvnSection_setDate <= cast(dbo.tzGetDate() as date)::timestamp(3)
						and COALESCE(EvnSection_disDate, cast(dbo.tzGetDate() as date)::timestamp(3) + interval '1 day') > cast(dbo.tzGetDate() as date)::timestamp(3)
				) es on true
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
					LpuSectionWard.Sex_id as \"Sex_id\",
					LpuSectionWard.LpuSectionWard_BedCount - LpuSectionWard.LpuSectionWard_BedRepair as \"cnt\",
					busyCount.busy as \"busy\"
				from
					LpuSectionWard
					left join lateral (
						select
							count(EvnSection.EvnSection_id) as busy
						from
							v_EvnSection EvnSection
							LEFT JOIN v_EvnPS EvnPS on EvnPS.EvnPS_id = EvnSection.EvnSection_pid	
						where
							EvnSection.LpuSection_id = :LpuSection_id
							and EvnSection.LpuSectionWard_id = :LpuSectionWard_id
							and EvnPS.EvnPS_id is not null
							and cast(EvnSection_setDate as DATE) <= cast(:date as DATE)
							and EvnSection_disDate is null
					) busyCount on true
				where
					LpuSectionWard.LpuSection_id = :LpuSection_id
					and LpuSectionWard.LpuSectionWard_id = :LpuSectionWard_id
			";
		} else if(!empty($data['EvnPS_id'])) {
			// считаем койки для приёмного отделения
			$query = "
				select
					LpuSectionWard.Sex_id as \"Sex_id\",
					LpuSectionWard.LpuSectionWard_BedCount - LpuSectionWard.LpuSectionWard_BedRepair as \"cnt\",
					busyCount.busy as \"busy\"
				from
					LpuSectionWard
					left join lateral (
						select
							count(EvnPS.EvnPS_id) as busy
						from
							v_EvnPS EvnPS 
						where
							EvnPS.LpuSection_pid = :LpuSection_id
							and EvnPS.LpuSectionWard_id = :LpuSectionWard_id
							and EvnPS.LpuSection_id is null
							and EvnPS.PrehospWaifRefuseCause_id is null
							and cast(EvnPS.EvnPS_setDate as DATE) <= cast(:date as DATE)
							and EvnPS.EvnPS_disDate is null
					) busyCount on true
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
			select
				lsw.LpuSectionWard_id as \"LpuSectionWard_id\",
				lsw.LpuSection_id as \"LpuSection_id\",
				lsw.LpuSectionWard_Name as \"LpuSectionWard_Name\",
				lsw.LpuWardType_id as \"LpuWardType_id\",
				case when lsw.LpuWardType_id = 2 then 'true' else 'false' end as \"isComfortable\",
				lsw.Sex_id as \"Sex_id\",
				beds.TotalBeds_Count as \"TotalBeds_Count\",
				beds.FreeBeds_Count as \"FreeBeds_Count\"
			from v_LpuSectionWard lsw
			LEFT JOIN LATERAL (
				select 
				lsw2.LpuSectionWard_BedCount as TotalBeds_Count,
				(lsw2.LpuSectionWard_BedCount - es.Evn_cnt) as FreeBeds_Count
				from v_LpuSectionWard lsw2
				LEFT JOIN LATERAL (
					select
						count(EvnSection_id) as Evn_cnt
					from v_EvnSection
					where
						LpuSection_id = lsw.LpuSection_id
						and LpuSectionWard_id = lsw.LpuSectionWard_id
						and EvnSection_setDate <= cast(dbo.tzGetDate() as date)
						and COALESCE(EvnSection_disDate, cast(dbo.tzGetDate() as date) + interval '1 day') > cast(dbo.tzGetDate() as date)
				) es ON TRUE
				where
					lsw2.LpuSection_id = lsw.LpuSection_id
					and lsw2.LpuSectionWard_id = lsw.LpuSectionWard_id
                limit 1
			) beds ON TRUE
			where 
				lsw.LpuSection_id = :LpuSection_id
				{$filter}
			order by lsw.LpuSectionWard_Name
		";

        return $this->queryResult($query, $queryParams);
    }

}