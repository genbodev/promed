<?php
/**
 * FundHolding_model - модель, для работы с таблицей Personcard
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 - 2011 Swan Ltd.
 * @author       Valery Bondarev
 * @version      18.12.2019
 */

class FundHolding_model extends SwPgModel
{
	/**
	 * FundHolding_model constructor.
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Функция для проверки существования данных для выбранных условий расчета
	 */
	function checkIfReestrDataExists($data)
	{
		$queryParams = array();
		/*
		 * фильтры по датам
		 */
		$beg_date = $data['Year'] . '-' . (strlen($data['Month']) == 1 ? '0' . $data['Month'] : $data['Month']) . '-01 00:00:00';
		$end_day = date('t', strtotime($beg_date));
		$end_date = $data['Year'] . '-' . (strlen($data['Month']) == 1 ? '0' . $data['Month'] : $data['Month']) . '-' . $end_day . ' 23:59:59';

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['BegDT'] = $beg_date;
		$queryParams['EndDT'] = $end_date;
		$queryParams['TheFarDate'] = '2030-01-01 00:00:00';
		$queryParams['UKL_EndDT'] = '2011-07-01 00:00:00';

		// фильтры по структурным элементам ЛПУ
		$struct_filters = "";
		switch ($data['CalcType']) {
			case 'all_regions':
				$struct_filters .= " and lr.LpuRegion_id is not null ";
				break;
			case 'region_types':
				$reg_types = json_decode($data['RegionTypes']);
				$struct_filters .= "
					and lr.LpuRegion_id is not null
					and lr.LpuRegionType_id in (" . join(',', $reg_types) . ")
				";
				break;
			case 'regions':
				$regions = json_decode($data['Regions']);
				$struct_filters .= "
					and lr.LpuRegion_id in (" . join(',', $regions) . ")
				";
				break;
		};
		$sql = "			
			(select
				rd.RegistryData_id as \"RegistryData_id\"
			from
				fh.RegistryData rd
				left join v_LpuRegion lr on lr.LpuRegion_id = rd.LpuRegion_id
			where
				rd.Lpu_fid = :Lpu_id
				and dkl	between :BegDT and :EndDT
				and rd.xl = '1'
				and rd.ei1 = 1
				and coalesce(pot, '0') not in ('1200', '1210', '1220', '2500', '1004', '1700', '610', '630', '620', '1803', '1801', '1850', '1840', '1811', '1802', '1830', '1812', '1810', '1820', '1860', '1800', '1870')
            limit 1)

			union
			-- стационары
			(select
				rd.RegistryData_id as \"RegistryData_id\"
			from
				fh.RegistryData rd
				left join v_LpuRegion lr on lr.LpuRegion_id = rd.LpuRegion_id
			where
				rd.Lpu_fid = :Lpu_id
				and dkl	between :BegDT and :EndDT
				and dnl <= :EndDT
				and rd.xl in ('2', '7', '8', '9')
				and coalesce(pot, '0') not in ('1200', '1210', '1220', '2500', '1004', '1700', '610', '630', '620', '1803', '1801', '1850', '1840', '1811', '1802', '1830', '1812', '1810', '1820', '1860', '1800', '1870')
            limit 1)
		";
		$result = $this->db->query($sql, $queryParams);
		if (is_object($result)) {
			$sel = $result->result('array');
			if (count($sel) > 0)
				return 1;
			else
				return 0;
		} else
			return false;
	}

	/**
	 *  Метод получения данных для построения графика
	 */
	function getChartData($data)
	{
		$queryParams = array();
		/*
		 * фильтры по датам
		 */
		$beg_date = $data['Year'] . '-' . (strlen($data['Month']) == 1 ? '0' . $data['Month'] : $data['Month']) . '-01 00:00:00';
		$end_day = date('t', strtotime($beg_date));
		$end_date = $data['Year'] . '-' . (strlen($data['Month']) == 1 ? '0' . $data['Month'] : $data['Month']) . '-' . $end_day . ' 23:59:59';

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['BegDT'] = $beg_date;
		$queryParams['EndDT'] = $end_date;
		$queryParams['TheFarDate'] = '2030-01-01 00:00:00';
		$queryParams['UKL_EndDT'] = '2011-07-01 00:00:00';

		// фильтры по структурным элементам ЛПУ
		$struct_filters = "";
		switch ($data['CalcType']) {
			case 'all_regions':
				$struct_filters .= " and lr.LpuRegion_id is not null ";
				break;
			case 'region_types':
				$reg_types = json_decode($data['RegionTypes']);
				$struct_filters .= "
					and lr.LpuRegion_id is not null
					and lr.LpuRegionType_id in (" . join(',', $reg_types) . ")
				";
				break;
			case 'regions':
				$regions = json_decode($data['Regions']);
				$struct_filters .= "
					and lr.LpuRegion_id in (" . join(',', $regions) . ")
				";
				break;
		};

		set_time_limit(0);

		$plan_num = 0;
		// график плановый
		$sql = "
			-- расчетное прикрепленное население
			WITH People (Person_id, Person_Surname, Person_Firname, Person_Secname, Person_BirthDay, Sex_id)
			AS
			(
				SELECT
					distinct pc.Person_id as \"Person_id\", 
					ps.Person_Surname as \"Person_Surname\", 
					ps.Person_Firname as \"Person_Firname\",  
					ps.Person_Secname as \"Person_Secname\", 
					ps.Person_BirthDay as \"Person_BirthDay\", 
					ps.Sex_id as \"Sex_id\"
				FROM
					v_PersonCard_all pc								
					inner join	v_Person_all pall on
						pc.Person_id = pall.Person_id
					inner join Polis on 
						pall.Polis_id = Polis.Polis_id								
					left join v_LpuRegion lr on lr.LpuRegion_id = pc.LpuRegion_id
					inner join v_PersonState ps on ps.Person_id = pc.Person_id and ps.Server_pid = 0
					left join LpuRegionType rt on rt.LpuRegionType_id = pc.LpuRegionType_id
				WHERE
					pc.Lpu_id = :Lpu_id						
					and pc.PersonCard_begDate < :EndDT
					and coalesce(pc.PersonCard_endDate, :TheFarDate) > :EndDT						
					and Polis.Polis_begDate < :EndDT
					and coalesce(Polis.Polis_endDate, :TheFarDate) > :EndDT
					" . $struct_filters . "
			)			
			select
				sum(coalesce(Factor.Value, 0)) *
				(
					select
						LpuFondHolderFactor_PlanFactorCost * LpuFondHolderFactor_PersonCost
					from
						LpuFondHolderFactor
					where
						LpuFondHolderFactor_Year <= EXTRACT(YEAR FROM :EndDT)
						and LpuFondHolderFactor_Month <= EXTRACT(MONTH FROM :EndDT)
					order by
						LpuFondHolderFactor_Year desc, LpuFondHolderFactor_Month desc
					limit 1
				) as \"value\"
			from
				People
				left join lateral (
					select
						case when People.Sex_id = 1 THEN f.LpuFondHolderSexBirthFactor_Man ELSE f.LpuFondHolderSexBirthFactor_Woman end as Value
					from
						LpuFondHolderSexBirthFactor f
					where
						f.LpuFondHolderSexBirthFactor_Year = EXTRACT(YEAR FROM :EndDT)
						and	(datediff('month',Person_BirthDay, :EndDT)
				+ case when (day(Person_BirthDay) > day(:EndDT)) 
				 then -1 else 0 end) >=
						case
							when LpuFondHolderSexBirthFactor_AgeBeg = 0 then 0
							when LpuFondHolderSexBirthFactor_AgeBeg = 0.26 then 4
							when LpuFondHolderSexBirthFactor_AgeBeg >= 1 then LpuFondHolderSexBirthFactor_AgeBeg * 12					
						end
						and (datediff('month',Person_BirthDay, :EndDT)
						+ case when (EXTRACT(DAY FROM Person_BirthDay) > EXTRACT(DAY FROM :EndDT)) 
						then -1 else 0 end)  <= 
						case
							when LpuFondHolderSexBirthFactor_AgeEnd = 0.25 then 3
							when LpuFondHolderSexBirthFactor_AgeEnd = 0.99 then 11
							when LpuFondHolderSexBirthFactor_AgeEnd >= 1 then LpuFondHolderSexBirthFactor_AgeEnd * 12 + 11
						end
				limit 1
				) as Factor on true
		";
		//die(getDebugSQL($sql, $queryParams));
		$result = $this->db->query($sql, $queryParams);
		if (is_object($result)) {
			$sel = $result->result('array');
			if (isset($sel[0]) && $sel[0]['value'] > 0)
				$plan_num = $sel[0]['value'];
		}

		// график по законченным случаям
		$sql = "
			select
				EXTRACT(DAY FROM dkl) as \"dkl\", sum(itog) as \"itog\"
			from (
			-- поликлинические
			select
				dkl, itog
			from
				fh.RegistryData rd
				left join v_LpuRegion lr on lr.LpuRegion_id = rd.LpuRegion_id
			where
				rd.Lpu_fid = :Lpu_id
				and dkl	between :BegDT and :EndDT
				and rd.xl = '1'
				and rd.ei1 = 1
				and coalesce(pot, '0') not in ('1200', '1210', '1220', '2500', '1004', '1700', '610', '630', '620', '1803', '1801', '1850', '1840', '1811', '1802', '1830', '1812', '1810', '1820', '1860', '1800', '1870')
				" . $struct_filters . "
			union all
			-- стационары
			select
				dkl, itog
			from
				fh.RegistryData rd
				left join v_LpuRegion lr on lr.LpuRegion_id = rd.LpuRegion_id
			where
				rd.Lpu_fid = :Lpu_id
				and dkl	between :BegDT and :EndDT
				and dnl <= :EndDT
				and rd.xl in ('2', '7', '8', '9')
				and coalesce(pot, '0') not in ('1200', '1210', '1220', '2500', '1004', '1700', '610', '630', '620', '1803', '1801', '1850', '1840', '1811', '1802', '1830', '1812', '1810', '1820', '1860', '1800', '1870')
				" . $struct_filters . "
			) as dt
			group by
				EXTRACT(DAY FROM dkl)
			order by
				dkl
		";
		//die(getDebugSQL($sql, $queryParams));
		$result = $this->db->query($sql, $queryParams);
		if (is_object($result)) {
			$sel = $result->result('array');
			$data_array = array();
			$data_array[0] = $plan_num;
			$last_day = 0;
			$last_day_itog = 0;
			foreach ($sel as $row) {
				// добиваем недостающие дни
				if (($last_day + 1) != $row['dkl']) {
					for ($i = $last_day + 1; $i < $row['dkl']; $i++)
						$data_array[$i] = $last_day_itog;
				}
				$data_array[$row['dkl']] = $last_day_itog + $row['itog'];
				$last_day_itog += $row['itog'];
				$last_day = $row['dkl'];
			}
			$end_day = date('t', strtotime($beg_date));
			if ($last_day < $end_day)
				for ($i = $last_day + 1; $i <= $end_day; $i++)
					$data_array[$i] = $last_day_itog;
			return $data_array;
		} else
			return false;
	}

	/**
	 *  Метод получения данных для построения годового графика
	 */
	function getYearChartData($data)
	{
		$queryParams = array();
		/*
		 * фильтры по датам
		 */
		$beg_date = $data['Year'] . '-' . (strlen($data['Month']) == 1 ? '0' . $data['Month'] : $data['Month']) . '-01 00:00:00';
		$end_day = date('t', strtotime($beg_date));
		$end_date = $data['Year'] . '-' . (strlen($data['Month']) == 1 ? '0' . $data['Month'] : $data['Month']) . '-' . $end_day . ' 23:59:59';

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['BegDT'] = $beg_date;
		$queryParams['EndDT'] = $end_date;
		$queryParams['TheFarDate'] = '2030-01-01 00:00:00';
		$queryParams['UKL_EndDT'] = '2011-07-01 00:00:00';

		// фильтры по структурным элементам ЛПУ
		$struct_filters = "";
		switch ($data['CalcType']) {
			case 'all_regions':
				$struct_filters .= " and lr.LpuRegion_id is not null ";
				break;
			case 'region_types':
				$reg_types = json_decode($data['RegionTypes']);
				$struct_filters .= "
					and lr.LpuRegion_id is not null
					and lr.LpuRegionType_id in (" . join(',', $reg_types) . ")
				";
				break;
			case 'regions':
				$regions = json_decode($data['Regions']);
				$struct_filters .= "
					and lr.LpuRegion_id in (" . join(',', $regions) . ")
				";
				break;
		};

		set_time_limit(0);

		$plan_num = 0;
		// график плановый
		$sql = "
			-- расчетное прикрепленное население
			WITH People (Person_id, Person_Surname, Person_Firname, Person_Secname, Person_BirthDay, Sex_id)
			AS
			(
				SELECT
					distinct pc.Person_id, ps.Person_Surname, ps.Person_Firname,  ps.Person_Secname, ps.Person_BirthDay, ps.Sex_id
				FROM
					v_PersonCard_all pc							
					inner join	v_Person_all pall on
						pc.Person_id = pall.Person_id
					inner join Polis on 
						pall.Polis_id = Polis.Polis_id								
					left join v_LpuRegion lr  on lr.LpuRegion_id = pc.LpuRegion_id
					inner join v_PersonState ps  on ps.Person_id = pc.Person_id and ps.Server_pid = 0
					left join LpuRegionType rt on rt.LpuRegionType_id = pc.LpuRegionType_id
				WHERE
					pc.Lpu_id = :Lpu_id						
					and pc.PersonCard_begDate < :EndDT
					and coalesce(pc.PersonCard_endDate, :TheFarDate) > :EndDT						
					and Polis.Polis_begDate < :EndDT
					and coalesce(Polis.Polis_endDate, :TheFarDate) > :EndDT
					" . $struct_filters . "
			)			
			select
				sum(coalesce(Factor.Value, 0)) *
				(
					select
						LpuFondHolderFactor_PlanFactorCost * LpuFondHolderFactor_PersonCost
					from
						LpuFondHolderFactor
					where
						LpuFondHolderFactor_Year <= YEAR(:EndDT)
						and LpuFondHolderFactor_Month <= month(:EndDT)
					order by
						LpuFondHolderFactor_Year desc, LpuFondHolderFactor_Month desc
					limit 1
				) as \"value\"
			from
				People
				left join lateral  (
					select
						case when People.Sex_id = 1 THEN f.LpuFondHolderSexBirthFactor_Man ELSE f.LpuFondHolderSexBirthFactor_Woman end as Value
					from
						LpuFondHolderSexBirthFactor f
					where
						f.LpuFondHolderSexBirthFactor_Year = EXTRACT(YEAR FROM :EndDT)
						and	(datediff('month',Person_BirthDay, :EndDT)
				+ case when (EXTRACT(DAY FROM Person_BirthDay) > EXTRACT(DAY FROM :EndDT)) 
				 then -1 else 0 end) >=
						case
							when LpuFondHolderSexBirthFactor_AgeBeg = 0 then 0
							when LpuFondHolderSexBirthFactor_AgeBeg = 0.26 then 4
							when LpuFondHolderSexBirthFactor_AgeBeg >= 1 then LpuFondHolderSexBirthFactor_AgeBeg * 12					
						end
						and (datediff('month',Person_BirthDay, :EndDT)
						+ case when (EXTRACT(DAY FROM Person_BirthDay) > EXTRACT(DAY FROM :EndDT)) 
						then -1 else 0 end)  <= 
						case
							when LpuFondHolderSexBirthFactor_AgeEnd = 0.25 then 3
							when LpuFondHolderSexBirthFactor_AgeEnd = 0.99 then 11
							when LpuFondHolderSexBirthFactor_AgeEnd >= 1 then LpuFondHolderSexBirthFactor_AgeEnd * 12 + 11
						end
						LIMIT 1
				) as Factor on true
		";
		//die(getDebugSQL($sql, $queryParams));
		$result = $this->db->query($sql, $queryParams);
		if (is_object($result)) {
			$sel = $result->result('array');
			if (isset($sel[0]) && $sel[0]['value'] > 0)
				$plan_num = $sel[0]['value'];
		}

		// график по законченным случаям
		$sql = "
			select
				EXTRACT(DAY FROM dkl) as \"dkl\", sum(itog) as \"itog\"
			from (
			-- поликлинические
			select
				dkl, itog
			from
				fh.RegistryData rd
				left join v_LpuRegion lr on lr.LpuRegion_id = rd.LpuRegion_id
			where
				rd.Lpu_fid = :Lpu_id
				and dkl	between :BegDT and :EndDT
				and rd.xl = '1'
				and rd.ei1 = 1
				and coalesce(pot, '0') not in ('1200', '1210', '1220', '2500', '1004', '1700', '610', '630', '620', '1803', '1801', '1850', '1840', '1811', '1802', '1830', '1812', '1810', '1820', '1860', '1800', '1870')
				" . $struct_filters . "
			union all
			-- стационары
			select
				dkl, itog
			from
				fh.RegistryData rd 
				left join v_LpuRegion lr on lr.LpuRegion_id = rd.LpuRegion_id
			where
				rd.Lpu_fid = :Lpu_id
				and dkl	between :BegDT and :EndDT
				and dnl <= :EndDT
				and rd.xl in ('2', '7', '8', '9')
				and coalesce(pot, '0') not in ('1200', '1210', '1220', '2500', '1004', '1700', '610', '630', '620', '1803', '1801', '1850', '1840', '1811', '1802', '1830', '1812', '1810', '1820', '1860', '1800', '1870')
				" . $struct_filters . "
			) as dt
			group by
				EXTRACT(DAY FROM dkl)
			order by
				dkl
		";
		//die(getDebugSQL($sql, $queryParams));
		$result = $this->db->query($sql, $queryParams);
		if (is_object($result)) {
			$sel = $result->result('array');
			$data_array = array();
			$data_array[0] = $plan_num;
			$last_day = 0;
			$last_day_itog = 0;
			foreach ($sel as $row) {
				// добиваем недостающие дни
				if (($last_day + 1) != $row['dkl']) {
					for ($i = $last_day + 1; $i < $row['dkl']; $i++)
						$data_array[$i] = $last_day_itog;
				}
				$data_array[$row['dkl']] = $last_day_itog + $row['itog'];
				$last_day_itog += $row['itog'];
				$last_day = $row['dkl'];
			}
			$end_day = date('t', strtotime($beg_date));
			if ($last_day < $end_day)
				for ($i = $last_day + 1; $i <= $end_day; $i++)
					$data_array[$i] = $last_day_itog;
			return $data_array;
		} else
			return false;
	}

	/**
	 * Функция для расчета Фин. результата
	 */
	function getTotalsCalculationReestr($data)
	{
		$queryParams = array();
		/*
		 * фильтры по датам
		 */
		$beg_date = $data['Year'] . '-' . (strlen($data['Month']) == 1 ? '0' . $data['Month'] : $data['Month']) . '-01 00:00:00';
		$end_day = date('t', strtotime($beg_date));
		$end_date = $data['Year'] . '-' . (strlen($data['Month']) == 1 ? '0' . $data['Month'] : $data['Month']) . '-' . $end_day . ' 23:59:59';

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['BegDT'] = $beg_date;
		$queryParams['EndDT'] = $end_date;
		$queryParams['TheFarDate'] = '2030-01-01 00:00:00';
		$queryParams['UKL_EndDT'] = '2011-07-01 00:00:00';

		// фильтры по структурным элементам ЛПУ
		$struct_filters = "";
		switch ($data['CalcType']) {
			case 'all_regions':
				$struct_filters .= " and lr.LpuRegion_id is not null ";
				break;
			case 'region_types':
				$reg_types = json_decode($data['RegionTypes']);
				$struct_filters .= "
					and lr.LpuRegion_id is not null
					and lr.LpuRegionType_id in (" . join(',', $reg_types) . ")
				";
				break;
			case 'regions':
				$regions = json_decode($data['Regions']);
				$struct_filters .= "
					and lr.LpuRegion_id in (" . join(',', $regions) . ")
				";
				break;
		};

		set_time_limit(0);

		$plan_num = 0;
		// график плановый
		$sql = "
			-- расчетное прикрепленное население
			WITH People (Person_id, Person_Surname, Person_Firname, Person_Secname, Person_BirthDay, Sex_id)
			AS
			(
				SELECT
					distinct pc.Person_id, ps.Person_Surname, ps.Person_Firname,  ps.Person_Secname, ps.Person_BirthDay, ps.Sex_id
				FROM
					v_PersonCard_all pc								
					inner join	v_Person_all pall on
						pc.Person_id = pall.Person_id
					inner join Polis on 
						pall.Polis_id = Polis.Polis_id								
					left join v_LpuRegion lr on lr.LpuRegion_id = pc.LpuRegion_id
					inner join v_PersonState ps on ps.Person_id = pc.Person_id and ps.Server_pid = 0
					left join LpuRegionType rt on rt.LpuRegionType_id = pc.LpuRegionType_id
				WHERE
					pc.Lpu_id = :Lpu_id						
					and pc.PersonCard_begDate < :EndDT
					and coalesce(pc.PersonCard_endDate, :TheFarDate) > :EndDT						
					and Polis.Polis_begDate < :EndDT
					and coalesce(Polis.Polis_endDate, :TheFarDate) > :EndDT
					" . $struct_filters . "
			)
			select	
				'people_count' as \"name\", COUNT(People.Person_id) as \"value\"
			from
				People
			union all
			select
				'plan_volume' as \"name\", sum(coalesce(Factor.Value, 0)) *
				(
					select
						LpuFondHolderFactor_PlanFactorCost * LpuFondHolderFactor_PersonCost
					from
						LpuFondHolderFactor
					where
						LpuFondHolderFactor_Year <= EXTRACT(YEAR FROM :EndDT)
						and LpuFondHolderFactor_Month <= EXTRACT(MONTH FROM :EndDT)
					order by
						LpuFondHolderFactor_Year desc, LpuFondHolderFactor_Month desc
					limit 1
				) as \"value\"
			from
				People
				left join lateral (
					select
						
						case when People.Sex_id = 1 THEN f.LpuFondHolderSexBirthFactor_Man ELSE f.LpuFondHolderSexBirthFactor_Woman end as Value
					from
						LpuFondHolderSexBirthFactor f
					where
						f.LpuFondHolderSexBirthFactor_Year = EXTRACT(YEAR FROM :EndDT)
						and	(datediff('month',Person_BirthDay, :EndDT)
				+ case when (EXTRACT(DAY FROM Person_BirthDay) > EXTRACT(DAY FROM :EndDT)) 
				 then -1 else 0 end) >=
						case
							when LpuFondHolderSexBirthFactor_AgeBeg = 0 then 0
							when LpuFondHolderSexBirthFactor_AgeBeg = 0.26 then 4
							when LpuFondHolderSexBirthFactor_AgeBeg >= 1 then LpuFondHolderSexBirthFactor_AgeBeg * 12					
						end
						and (datediff('month',Person_BirthDay, :EndDT)
						+ case when (EXTRACT(DAY FROM Person_BirthDay) > EXTRACT(DAY FROM :EndDT)) 
						then -1 else 0 end)  <= 
						case
							when LpuFondHolderSexBirthFactor_AgeEnd = 0.25 then 3
							when LpuFondHolderSexBirthFactor_AgeEnd = 0.99 then 11
							when LpuFondHolderSexBirthFactor_AgeEnd >= 1 then LpuFondHolderSexBirthFactor_AgeEnd * 12 + 11
						end
						limit 1
				) as Factor on true
			union all
			-- поликлинические
			select
				'polka_itog' as \"name\", sum(itog) as \"value\"
			from
				fh.RegistryData rd
				left join v_LpuRegion lr on lr.LpuRegion_id = rd.LpuRegion_id
			where
				rd.Lpu_fid = :Lpu_id
				and dkl	between :BegDT and :EndDT
				and rd.xl = '1'
				and ei1 = 1
				and coalesce(pot, '0') not in ('1200', '1210', '1220', '2500', '1004', '1700', '610', '630', '620', '1803', '1801', '1850', '1840', '1811', '1802', '1830', '1812', '1810', '1820', '1860', '1800', '1870')
				" . $struct_filters . "
			union all
			-- стационары
			select
				'stac_itog' as \"value\", sum(itog) as \"value\"
			from
				fh.RegistryData rd 
				left join v_LpuRegion lr on lr.LpuRegion_id = rd.LpuRegion_id
			where
				rd.Lpu_fid = :Lpu_id
				and dkl	between :BegDT and :EndDT
				and dnl <= :EndDT
				and rd.xl in ('2', '7', '8', '9')
				and coalesce(pot, '0') not in ('1200', '1210', '1220', '2500', '1004', '1700', '610', '630', '620', '1803', '1801', '1850', '1840', '1811', '1802', '1830', '1812', '1810', '1820', '1860', '1800', '1870')
				" . $struct_filters . "
		";
		//die(getDebugSQL($sql, $queryParams));
		$attach_count = 0;
		$finance_plan = 0;
		$usluga_sum = 0;
		$fin_res = 0;
		$usluga_sum_end = 0;
		$fin_res_end = 0;
		$result = $this->db->query($sql, $queryParams);
		if (is_object($result)) {
			$sel = $result->result('array');
			foreach ($sel as $val) {
				switch ($val['name']) {
					case 'people_count':
						$attach_count = $val['value'];
						break;
					case 'plan_volume':
						$finance_plan = $val['value'];
						break;
					case 'polka_itog':
						$usluga_sum_end += $val['value'];
						break;
					case 'stac_itog':
						$usluga_sum_end += $val['value'];
						break;
				}
				$fin_res_end = $finance_plan - $usluga_sum_end;
			}
		} else {
			return false;
		}

		$attach_count = number_format($attach_count, 0, '', ' ');
		$finance_plan = number_format($finance_plan, 2, '.', ' ');
		$usluga_sum_end = number_format($usluga_sum_end, 2, '.', ' ');
		$fin_res_end = number_format($fin_res_end, 2, '.', ' ');

		$response = "
			Расчетное прикрепленное население: <b>{$attach_count}</b><br/>
			Плановый объем финансовых средств: <b>{$finance_plan}</b><br/>			
			Суммарная стоимость оказанных медицинских услуг: <b>{$usluga_sum_end}</b><br/>
			Финансовый результат: <b>{$fin_res_end}</b><br/>
		";
		return $response;
	}

	/**
	 * Функция для расчета Фин. результата
	 */
	function getTotalsCalculation($data)
	{
		$queryParams = array();
		/*
		 * Расчетное прикрепленное население
		 */
		$beg_date = $data['Year'] . '-' . (strlen($data['Month']) == 1 ? '0' . $data['Month'] : $data['Month']) . '-01 00:00:00';
		$end_day = date('t', strtotime($beg_date));
		$end_date = $data['Year'] . '-' . (strlen($data['Month']) == 1 ? '0' . $data['Month'] : $data['Month']) . '-' . $end_day . ' 23:59:59';

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['BegDT'] = $beg_date;
		$queryParams['EndDT'] = $end_date;
		$queryParams['TheFarDate'] = '2030-01-01 00:00:00';
		$queryParams['UKL_EndDT'] = '2011-07-01 00:00:00';

		// фильтры по структурным элементам ЛПУ
		$struct_filters = "";
		switch ($data['CalcType']) {
			case 'all_regions':
				$struct_filters .= " and pc.LpuRegion_id is not null ";
				break;
			case 'region_types':
				$reg_types = json_decode($data['RegionTypes']);
				$struct_filters .= "
					and pc.LpuRegion_id is not null
					and rt.LpuRegionType_id in (" . join(',', $reg_types) . ")
				";
				break;
			case 'regions':
				$regions = json_decode($data['Regions']);
				$struct_filters .= "
					and pc.LpuRegion_id in (" . join(',', $regions) . ")
				";
				break;
		};

		set_time_limit(0);

		$where = "";
		$sql = "
				-- расчетное прикрепленное население
				WITH People (Person_id, Person_Surname, Person_Firname, Person_Secname, Person_BirthDay, Sex_id)
				AS
				(
					SELECT
						distinct pc.Person_id, ps.Person_Surname, ps.Person_Firname, ps.Person_Secname, ps.Person_BirthDay, ps.Sex_id
					FROM
						v_PersonCard_all pc
						inner join lateral (
							SELECT 
								Polis.Polis_id 
							FROM 
								v_Person_all pall
								inner join Polis on pall.Polis_id = Polis.Polis_id
							WHERE 
								pc.Person_id = pall.Person_id
								and Polis.Polis_begDate < :EndDT
								and coalesce(Polis.Polis_endDate, :TheFarDate) > :EndDT limit 1
							) polis on true
						inner join v_PersonState ps on ps.Person_id = pc.Person_id and ps.Server_pid = 0
					WHERE
						pc.Lpu_id = :Lpu_id
						and pc.PersonCard_begDate < :EndDT
						and coalesce(pc.PersonCard_endDate, :TheFarDate) > :EndDT
						" . $struct_filters . "
				)
				select	
					COUNT(People.Person_id) as \"value\", 'people_count' as \"name\",
					0 as \"value1\", 'nothing' as \"name1\"
				from
					People
				union all
				select
					sum(coalesce(Factor.Value, 0)) *
					(
						select
							LpuFondHolderFactor_PlanFactorCost * LpuFondHolderFactor_PersonCost
						from
							LpuFondHolderFactor
						where
							LpuFondHolderFactor_Year <= EXTRACT(YEAR FROM cast(:EndDT as timestamp))
							and LpuFondHolderFactor_Month <= EXTRACT(MONTH FROM cast(:EndDT as timestamp))
						order by
							LpuFondHolderFactor_Year desc, LpuFondHolderFactor_Month desc
						limit 1
					) as \"value\", 'plan_volume' as \"name\",
					0 as \"value1\", 'nothing' as \"name1\"
				from
					People
					left join lateral (
						select
							case when People.Sex_id = 1 THEN f.LpuFondHolderSexBirthFactor_Man ELSE f.LpuFondHolderSexBirthFactor_Woman end as Value
						from
							LpuFondHolderSexBirthFactor f
						where
							f.LpuFondHolderSexBirthFactor_Year = extract(YEAR FROM cast(:EndDT as timestamp))
							and	(datediff('month',Person_BirthDay, cast(:EndDT as timestamp))
					+ case when (EXTRACT(DAY FROM cast(Person_BirthDay as timestamp)) > EXTRACT(DAY FROM cast(:EndDT as timestamp))) 
					 then -1 else 0 end) >=
							case
								when LpuFondHolderSexBirthFactor_AgeBeg = 0 then 0
								when LpuFondHolderSexBirthFactor_AgeBeg = 0.26 then 4
								when LpuFondHolderSexBirthFactor_AgeBeg >= 1 then LpuFondHolderSexBirthFactor_AgeBeg * 12					
							end
							and (datediff('month',Person_BirthDay, cast(:EndDT as timestamp))
							+ case when (EXTRACT(DAY FROM cast(Person_BirthDay as timestamp)) > EXTRACT(DAY FROM cast(:EndDT as timestamp))) 
							then -1 else 0 end)  <= 
							case
								when LpuFondHolderSexBirthFactor_AgeEnd = 0.25 then 3
								when LpuFondHolderSexBirthFactor_AgeEnd = 0.99 then 11
								when LpuFondHolderSexBirthFactor_AgeEnd >= 1 then LpuFondHolderSexBirthFactor_AgeEnd * 12 + 11
							end
							limit 1
					) as Factor on true
				union all
				SELECT
					sum(coalesce(vizits.FactSumUKL, 0)) as \"value\", 'polka2' as \"name\",
					sum(case when coalesce(EvnPL.EvnPL_IsFinish, 1) = 2 then coalesce(vizits.FactSumUKL, 0) else 0 end) as \"value1\", 'polka6' as \"name1\"
				FROM	
					v_EvnPL EvnPL 
					inner join People on People.Person_id = EvnPL.Person_id
					inner join v_Lpu Lpu on Lpu.Lpu_id = EvnPL.Lpu_id
					inner join v_LpuSection LpuSect  on LpuSect.LpuSection_id = EvnPL.LpuSection_id
					inner join v_Diag Diag on Diag.Diag_id = EvnPL.Diag_id
					left JOIN lateral (
						select
							count(ev.EvnVizitPL_id) as kd,			
							sum(coalesce(tarif.LpuSectionTariff_Tariff, 0)) as PlanSum,
							sum(
								LpuSectionTariff_Tariff_UKL
							) as FactSumUKL,
							sum(
								LpuSectionTariff_Tariff
							) as FactSum
						from
							v_EvnVizitPL ev
							left join lateral (
								select
									-- коррекция по УКЛ
									case
										-- если случай не закончен
										when
											coalesce(EvnPL.EvnPL_IsFinish, 1) = 1
										then
											coalesce(LpuSectionTariff_Tariff, 0)
										when
											EvnPL.EvnPL_disDate < cast(:BegDT as timestamp)	and coalesce(EvnPL.EvnPL_UKL, 0) between 0.3 and 0.84
										then
											coalesce(LpuSectionTariff_Tariff, 0) * coalesce(EvnPL.EvnPL_UKL, 0)
										when
											EvnPL.EvnPL_disDate < cast(:BegDT as timestamp)	and coalesce(EvnPL.EvnPL_UKL, 0) < 0.3
										then
											0
										else
											coalesce(LpuSectionTariff_Tariff, 0)
									end as LpuSectionTariff_Tariff_UKL,
									LpuSectionTariff_Tariff as LpuSectionTariff_Tariff
								from
									LpuSectionTariff
								where
									LpuSection_id = ev.LpuSection_id
									and LpuSectionTariff_setDate <= EvnPL.EvnPL_disDate
									and TariffClass_id = 1
								order by
									LpuSectionTariff_setDate desc
								limit 1
							) as tarif on true
						where
							ev.EvnVizitPL_pid = EvnPL.EvnPL_id
							--and ev.Lpu_id = '10010833'
							and ev.EvnVizitPL_setDT between :BegDT and :EndDT
					) as vizits on true
				WHERE
					 --EvnPL.Lpu_id = '10010833'
					 --and 
					 exists (
						select
							ev1.EvnVizitPL_id
						from
							v_EvnVizitPL ev1			
							inner join v_LpuSection ls1 on ls1.LpuSection_id = ev1.LpuSection_id and coalesce(ls1.LpuSectionProfile_Code, '0') not in ('1200', '1210', '1220', '2500', '1004', '1700', '610', '630', '620', '1803', '1801', '1850', '1840', '1811', '1802', '1830', '1812', '1810', '1820', '1860', '1800', '1870')
						where
							ev1.EvnVizitPL_pid = EvnPL.EvnPL_id
							--and ev1.Lpu_id = '10010833'
							and ev1.EvnVizitPL_setDT between :BegDT and :EndDT			
					 )
				union all
				-- случаи лечения в стационаре	 
				-- расчетное прикрепленное население
				SELECT
					sum(coalesce(evnsections.FactSum, 0)) as \"value\", 'stac2' as \"name\",
					sum(case when EvnPS.EvnPS_disDate is not null then coalesce(evnsections.FactSum, 0) else 0 end) as \"value1\", 'stac6' as \"name1\"
				FROM	
					 v_EvnPS EvnPS
					inner join People on People.Person_id = EvnPS.Person_id
					inner join v_Lpu Lpu on Lpu.Lpu_id = EvnPS.Lpu_id
					left join v_EvnLeaveBase elb on elb.EvnLeaveBase_pid = EvnPS.EvnPS_id
					left join v_EvnSection EPSLastES on EPSLastES.EvnSection_pid = EvnPS.EvnPS_id and EPSLastES.EvnSection_Index = EPSLastES.EvnSection_Count-1 
					left join LpuSection LpuSect  on LpuSect.LpuSection_id = EPSLastES.LpuSection_id
					left join v_Diag Diag  on Diag.Diag_id = EPSLastES.Diag_id
					left join LpuUnit  on LpuUnit.LpuUnit_id = LpuSect.LpuUnit_id 
					left join LpuUnitType on LpuUnitType.LpuUnitType_id = LpuUnit.LpuUnitType_id 
					left join lateral (
						select
							max(coalesce(ms.Mes_id, 0)) as Mes_id,
							sum(
								case when
									lut.LpuUnitType_SysNick = 'stac'
								then
									datediff('day', case when es.EvnSection_setDT < cast(:BegDT as timestamp) then cast(:BegDT as timestamp) else es.EvnSection_setDT end, case when coalesce(es.EvnSection_disDT, dbo.tzGetDate()) > cast(:EndDT as timestamp) then cast(:EndDT as timestamp) else coalesce(es.EvnSection_disDT, dbo.tzGetDate()) end ) + abs(sign(datediff('day', case when es.EvnSection_setDT < cast(:BegDT as timestamp) then cast(:BegDT as timestamp) else es.EvnSection_setDT end, case when coalesce(es.EvnSection_disDT, dbo.tzGetDate()) > cast(:EndDT as timestamp) then cast(:EndDT as timestamp) else coalesce(es.EvnSection_disDT, dbo.tzGetDate()) end)) - 1)
								else
									(datediff('day', case when es.EvnSection_setDT < cast(:BegDT as timestamp) then cast(:BegDT as timestamp) else es.EvnSection_setDT end, case when coalesce(es.EvnSection_disDT, dbo.tzGetDate()) > cast(:EndDT as timestamp) then cast(:EndDT as timestamp) else coalesce(es.EvnSection_disDT, dbo.tzGetDate()) end) + 1)
								end
							) as KoikoDni,
							sum(
								coalesce(tarif.LpuSectionTariff_Tariff, 0) * 
								-- если есть МЭС, то по нему берем
								CASE WHEN
									ms.Mes_id is not null
								THEN
									ms.Mes_KoikoDni				
								-- если МЭСа нет, то по фактическому (с) Андрей Борматов
								ELSE
									case when
										lut.LpuUnitType_SysNick = 'stac'
									then
										datediff('day', case when es.EvnSection_setDT < cast(:BegDT as timestamp) then cast(:BegDT as timestamp) else es.EvnSection_setDT end, case when coalesce(es.EvnSection_disDT, dbo.tzGetDate()) > cast(:EndDT as timestamp) then :EndDT else coalesce(es.EvnSection_disDT, dbo.tzGetDate()) end ) + abs(sign(datediff('day', case when es.EvnSection_setDT < cast(:BegDT as timestamp) then cast(:BegDT as timestamp) else es.EvnSection_setDT end, case when coalesce(es.EvnSection_disDT, dbo.tzGetDate()) > cast(:EndDT as timestamp) then cast(:EndDT as timestamp) else coalesce(es.EvnSection_disDT, dbo.tzGetDate()) end)) - 1)
									else
										(datediff('day', case when es.EvnSection_setDT < cast(:BegDT as timestamp) then cast(:BegDT as timestamp) else es.EvnSection_setDT end, case when coalesce(es.EvnSection_disDT, dbo.tzGetDate()) > :EndDT then cast(:EndDT as timestamp) else coalesce(es.EvnSection_disDT, dbo.tzGetDate()) end) + 1)
									end
								END
							) as PlanSum,
							sum(
								coalesce(tarif.LpuSectionTariff_Tariff, 0) *
								case when
									lut.LpuUnitType_SysNick = 'stac'
								then
									datediff('day', case when es.EvnSection_setDT < cast(:BegDT as timestamp) then cast(:BegDT as timestamp) else es.EvnSection_setDT end, case when coalesce(es.EvnSection_disDT, dbo.tzGetDate()) > cast(:EndDT as timestamp) then cast(:EndDT as timestamp) else coalesce(es.EvnSection_disDT, dbo.tzGetDate()) end ) + abs(sign(datediff('day', case when es.EvnSection_setDT < cast(:BegDT as timestamp) then cast(:BegDT as timestamp) else es.EvnSection_setDT end, case when coalesce(es.EvnSection_disDT, dbo.tzGetDate()) > cast(:EndDT as timestamp) then cast(:EndDT as timestamp) else coalesce(es.EvnSection_disDT, dbo.tzGetDate()) end)) - 1)
								else
									(datediff('day', case when es.EvnSection_setDT < cast(:BegDT as timestamp) then cast(:BegDT as timestamp) else es.EvnSection_setDT end, case when coalesce(es.EvnSection_disDT, dbo.tzGetDate()) > cast(:EndDT as timestamp) then cast(:EndDT as timestamp) else coalesce(es.EvnSection_disDT, dbo.tzGetDate()) end) + 1)
								end
							) as FactSum
						from
							v_EvnSection es
							left join v_LpuSection ls1 on ls1.LpuSection_id = es.LpuSection_id
							left join v_LpuSection ls2 on ls2.LpuSection_id = ls1.LpuSection_pid
							left join LpuUnit lu on lu.LpuUnit_id = ls1.LpuUnit_id 
							left join LpuUnitType lut on lut.LpuUnitType_id = lu.LpuUnitType_id

							left join MesOld ms on ms.Mes_id = es.Mes_id
							left join lateral (
								select 
									-- как брать УКЛ из выписки, но мы ведь не только по законченным случаям берем, для незакрытых движений принимаем текущую дату
									-- коррекция по УКЛ
									case
										-- если случай не закончен
										when
											EvnPS.EvnPS_disDate is null
										then
											coalesce(LpuSectionTariff_Tariff, 0)
										when
											EvnPS.EvnPS_disDate < :BegDT and coalesce(elb.EvnLeaveBase_UKL, 0) between 0.3 and 0.84
										then
											coalesce(LpuSectionTariff_Tariff, 0) * coalesce(elb.EvnLeaveBase_UKL, 0)
										when
											EvnPS.EvnPS_disDate < cast(:BegDT as timestamp) and coalesce(elb.EvnLeaveBase_UKL, 0) < 0.3
										then
											0
										else
											coalesce(LpuSectionTariff_Tariff, 0)
									end as LpuSectionTariff_Tariff
								from
									LpuSectionTariff tar
								where
									tar.LpuSection_id = CASE WHEN ls1.LpuSection_pid is null then ls1.LpuSection_id else ls1.LpuSection_pid end
									--and LpuSectionTariff_setDate <= es.EvnSection_disDate
									and TariffClass_id = 7
								order by
									LpuSectionTariff_setDate desc
								limit 1
							) as tarif on true 
						where
							es.EvnSection_pid = EvnPS.EvnPS_id
							and	es.EvnSection_setDT <= cast(:EndDT as timestamp)
							--and es.Lpu_id = '10010833'
							and coalesce(es.EvnSection_disDT, dbo.tzGetDate()) >= cast(:BegDT as timestamp)
							and coalesce(ls1.LpuSectionProfile_Code, '0') not in ('1200', '1210', '1220', '2500', '1004', '1700', '610', '630', '620', '1803', '1801', '1850', '1840', '1811', '1802', '1830', '1812', '1810', '1820', '1860', '1800', '1870')
							and coalesce(ls2.LpuSectionProfile_Code, '0') not in ('1200', '1210', '1220', '2500', '1004', '1700', '610', '630', '620', '1803', '1801', '1850', '1840', '1811', '1802', '1830', '1812', '1810', '1820', '1860', '1800', '1870')
							--and lut.LpuUnitType_SysNick = 'stac'
					) as evnsections on true
				WHERE
					 --EvnPS.Lpu_id = '10010833'
					 --and
					 exists (
						select
							EvnSection_id
						from
							v_EvnSection es1
							left join v_LpuSection ls1 on es1.LpuSection_id = ls1.LpuSection_id
							left join v_LpuSection ls2 on ls2.LpuSection_id = ls1.LpuSection_pid
						where
							es1.EvnSection_pid = EvnPS.EvnPS_id
							and	es1.EvnSection_setDT <= cast(:EndDT as timestamp)
							--and es1.Lpu_id = '10010833'
							and coalesce(es1.EvnSection_disDT, dbo.tzGetDate()) >= cast(:BegDT as timestamp)
							and coalesce(ls1.LpuSectionProfile_Code, '0') not in ('1200', '1210', '1220', '2500', '1004', '1700', '610', '630', '620', '1803', '1801', '1850', '1840', '1811', '1802', '1830', '1812', '1810', '1820', '1860', '1800', '1870')
							and coalesce(ls2.LpuSectionProfile_Code, '0') not in ('1200', '1210', '1220', '2500', '1004', '1700', '610', '630', '620', '1803', '1801', '1850', '1840', '1811', '1802', '1830', '1812', '1810', '1820', '1860', '1800', '1870')
					 )
					 and evnsections.KoikoDni is not null
		";
		//die(getDebugSQL($sql, $queryParams));
		$attach_count = 0;
		$finance_plan = 0;
		$usluga_sum = 0;
		$fin_res = 0;
		$usluga_sum_end = 0;
		$fin_res_end = 0;
		$result = $this->db->query($sql, $queryParams);
		if (is_object($result)) {
			$sel = $result->result('array');
			foreach ($sel as $val) {
				switch ($val['name']) {
					case 'people_count':
						$attach_count = $val['value'];
						break;
					case 'plan_volume':
						$finance_plan = $val['value'];
						break;
					case 'polka2':
						$usluga_sum += $val['value'];
						$usluga_sum_end += $val['value1'];
						break;
					case 'stac2':
						$usluga_sum += $val['value'];
						$usluga_sum_end += $val['value1'];
						break;
				}
				$fin_res = $finance_plan - $usluga_sum;
				$fin_res_end = $finance_plan - $usluga_sum_end;
			}
		} else {
			return false;
		}

		$attach_count = number_format($attach_count, 0, '', ' ');
		$finance_plan = number_format($finance_plan, 2, '.', ' ');
		$usluga_sum = number_format($usluga_sum, 2, '.', ' ');
		$fin_res = number_format($fin_res, 2, '.', ' ');
		$usluga_sum_end = number_format($usluga_sum_end, 2, '.', ' ');
		$fin_res_end = number_format($fin_res_end, 2, '.', ' ');

		$response = "
			Расчетное прикрепленное население: <b>{$attach_count}</b><br/>
			Плановый объем финансовых средств: <b>{$finance_plan}</b><br/>
			Суммарная стоимость оказанных медицинских услуг: <b>{$usluga_sum}</b><br/>
			Финансовый результат: <b>{$fin_res}</b><br/>
			Суммарная стоимость оказанных  медицинских услуг по законченным случаям лечения: <b>{$usluga_sum_end}</b><br/>
			Финансовый результат  по законченным случаям лечения: <b>{$fin_res_end}</b><br/>
		";
		return $response;
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getFundHoldingGrid($data)
	{
		$queryParams = array();
		/*
		 * Расчетное прикрепленное население
		 */
		$beg_date = $data['Year'] . '-' . (strlen($data['Month']) == 1 ? '0' . $data['Month'] : $data['Month']) . '-01 00:00:00';
		$end_day = date('t', strtotime($beg_date));
		$end_date = $data['Year'] . '-' . (strlen($data['Month']) == 1 ? '0' . $data['Month'] : $data['Month']) . '-' . $end_day . ' 23:59:59';

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['BegDT'] = $beg_date;
		$queryParams['EndDT'] = $end_date;
		$queryParams['TheFarDate'] = '2030-01-01 00:00:00';

		// фильтры по структурным элементам ЛПУ
		$struct_filters = "";
		switch ($data['CalcType']) {
			case 'all_regions':
				$struct_filters .= " and pc.LpuRegion_id is not null ";
				break;
			case 'region_types':
				$reg_types = json_decode($data['RegionTypes']);
				$struct_filters .= "
					and pc.LpuRegion_id is not null
					and rt.LpuRegionType_id in (" . join(',', $reg_types) . ")
				";
				break;
			case 'regions':
				$regions = json_decode($data['Regions']);
				$struct_filters .= "
					and pc.LpuRegion_id in (" . join(',', $regions) . ")
				";
				break;
		};

		$filters = "";
		// фильтр по фондодержанию
		if (isset($data['FundHolder']) && $data['FundHolder'] > 0) {
			if ($data['FundHolder'] == 1)
				$filters .= " and \"Lpu_id\" = :Lpu_id ";
			else
				$filters .= " and \"Lpu_id\" != :Lpu_id ";
		}

		// фильтр по закончености случая
		if (isset($data['EvnIsFinish']) && $data['EvnIsFinish'] > 0) {
			if ($data['EvnIsFinish'] == 1)
				$filters .= " and \"Evn_endDate\" is null ";
			else
				$filters .= " and \"Evn_endDate\" is not null ";
		}

		// фильтр по ФИО
		if (isset($data['Person_Surname']) && strlen($data['Person_Surname']) > 0) {
			$filters .= " and \"Person_SurName\" ilike :Person_Surname ";
			$queryParams['Person_Surname'] = $data['Person_Surname'] . '%';
		}

		if (isset($data['Person_Firname']) && strlen($data['Person_Firname']) > 0) {
			$filters .= " and \"Person_FirName\" ilike :Person_Firname ";
			$queryParams['Person_Firname'] = $data['Person_Firname'] . '%';
		}

		if (isset($data['Person_Secname']) && strlen($data['Person_Secname']) > 0) {
			$filters .= " and \"Person_SecName\" ilike :Person_Secname ";
			$queryParams['Person_Secname'] = $data['Person_Secname'] . '%';
		}

		set_time_limit(0);

		$polka_events = "
			-- поликлинические случаи			
			SELECT
				EvnPL.EvnPL_id as \"Evn_id\",
				EvnPL.EvnClass_id as \"EvnClass_id\",
				People.Person_id as \"Person_id\",
				EvnPL.Server_id as \"Server_id\",
				People.Person_SurName as \"Person_SurName\",
				People.Person_FirName as \"Person_FirName\",
				People.Person_SecName as \"Person_SecName\",
				to_char(cast(EvnPL.EvnPL_setDT as timestamp),'dd.mm.yyyy') as \"Evn_begDate\",
				case when coalesce(EvnPL_IsFinish, 1) = 2 then to_char(cast(EvnPL.EvnPL_disDate as timestamp),'dd.mm.yyyy') end as \"Evn_endDate\",
				Lpu.Lpu_Nick as \"Lpu_Nick\",
				Lpu.Lpu_id as \"Lpu_id\",
				LpuSect.LpuSection_Name as \"LpuSection_Name\",
				vizits.kd as \"KoikoDni\",
				Diag.Diag_Name as \"Diag_Name\",
				vizits.PlanSum as \"PlanSum\",
				vizits.FactSum as \"FactSum\"
			FROM	
				v_EvnPL EvnPL
				inner join People on People.Person_id = EvnPL.Person_id
				inner join v_Lpu Lpu on Lpu.Lpu_id = EvnPL.Lpu_id
				inner join v_LpuSection LpuSect on LpuSect.LpuSection_id = EvnPL.LpuSection_id
				inner join v_Diag Diag on Diag.Diag_id = EvnPL.Diag_id
				left join lateral(
					select
						count(ev.EvnVizitPL_id) as kd,
						sum(coalesce(tarif.LpuSectionTariff_Tariff, 0)) as PlanSum,
						sum(coalesce(tarif.LpuSectionTariff_Tariff, 0)) as FactSum
					from
						v_EvnVizitPL ev
						left join lateral(
							select 
								LpuSectionTariff_Tariff
							from
								LpuSectionTariff
							where
								LpuSection_id = ev.LpuSection_id
								and LpuSectionTariff_setDate <= EvnPL.EvnPL_disDate
								and TariffClass_id = 1
							order by
								LpuSectionTariff_setDate desc
							limit 1
						) as tarif on true
					where
						ev.EvnVizitPL_pid = EvnPL.EvnPL_id
						and ev.EvnVizitPL_setDT between :BegDT and :EndDT 			
				) as vizits on true
			WHERE				 
				exists (
					select
						ev1.EvnVizitPL_id
					from
						v_EvnVizitPL ev1
						inner join v_LpuSection ls1 on ls1.LpuSection_id = ev1.LpuSection_id and coalesce(ls1.LpuSectionProfile_Code, '0') not in ('1200', '1210', '1220', '2500', '1004', '1700', '610', '630', '620', '1803', '1801', '1850', '1840', '1811', '1802', '1830', '1812', '1810', '1820', '1860', '1800', '1870')
					where
						ev1.EvnVizitPL_pid = EvnPL.EvnPL_id
						and ev1.EvnVizitPL_setDT between :BegDT and :EndDT			
				)
		";

		$stac_events = "
			-- случаи лечения в стационаре	 
			-- расчетное прикрепленное население
			SELECT
				EvnPS.EvnPS_id as \"Evn_id\",
				EvnPS.EvnClass_id as \"EvnClass_id\",
				People.Person_id as \"Person_id\",
				EvnPS.Server_id as \"Server_id\",
				People.Person_SurName as \"Person_SurName\",
				People.Person_FirName as \"Person_FirName\",
				People.Person_SecName as \"Person_SecName\",
				to_char(cast(EvnPS.EvnPS_setDT as timestamp),'dd.mm.yyyy') as \"Evn_begDate\",
				case when EvnPS.EvnPS_disDate is not null then to_char(cast(EvnPS.EvnPS_disDate as timestamp),'dd.mm.yyyy') end as \"Evn_endDate\",
				Lpu.Lpu_Nick as \"Lpu_Nick\",
				Lpu.Lpu_id as \"Lpu_id\",
				LpuSect.LpuSection_Name as \"LpuSection_Name\",
				/*case when LpuUnitType.LpuUnitType_SysNick = 'stac' 
					then datediff('day', EvnPS.EvnPS_setDate, coalesce(EvnPS.EvnPS_disDate, dbo.tzGetDate())) + abs(sign(datediff('day', EvnPS.EvnPS_setDate, coalesce(EvnPS.EvnPS_disDate, dbo.tzGetDate()))) - 1) -- круглосуточные 
					else (datediff('day', EvnPS.EvnPS_setDate, coalesce(EvnPS.EvnPS_disDate, dbo.tzGetDate())) + 1) -- дневные 
				end as KoikoDni,*/
				evnsections.KoikoDni as \"KoikoDni\",
				Diag.Diag_Name as \"Diag_Name\",
				evnsections.PlanSum as \"PlanSum\",
				evnsections.FactSum as \"FactSum\"
			FROM	
				 v_EvnPS EvnPS 
				inner join People on People.Person_id = EvnPS.Person_id
				inner join v_Lpu Lpu  on Lpu.Lpu_id = EvnPS.Lpu_id
				left join v_EvnSection EPSLastES on EPSLastES.EvnSection_pid = EvnPS.EvnPS_id and EPSLastES.EvnSection_Index = EPSLastES.EvnSection_Count-1 
				left join LpuSection LpuSect on LpuSect.LpuSection_id = EPSLastES.LpuSection_id
				left join v_Diag Diag on Diag.Diag_id = EPSLastES.Diag_id
				left join LpuUnit on LpuUnit.LpuUnit_id = LpuSect.LpuUnit_id 
				left join LpuUnitType on LpuUnitType.LpuUnitType_id = LpuUnit.LpuUnitType_id 
				left join lateral(
					select
						max(coalesce(ms.Mes_id, 0)) as Mes_id,
						sum(
							case when
								lut.LpuUnitType_SysNick = 'stac'
							then
								datediff('day', case when es.EvnSection_setDT < :BegDT then :BegDT else es.EvnSection_setDT end, case when coalesce(es.EvnSection_disDT, dbo.tzGetDate()) > :EndDT then :EndDT else coalesce(es.EvnSection_disDT, dbo.tzGetDate()) end ) + abs(sign(datediff('day', case when es.EvnSection_setDT < :BegDT then :BegDT else es.EvnSection_setDT end, case when coalesce(es.EvnSection_disDT, dbo.tzGetDate()) > :EndDT then :EndDT else coalesce(es.EvnSection_disDT, dbo.tzGetDate()) end)) - 1)
							else
								(datediff('day', case when es.EvnSection_setDT < :BegDT then :BegDT else es.EvnSection_setDT end, case when coalesce(es.EvnSection_disDT, dbo.tzGetDate()) > :EndDT then :EndDT else coalesce(es.EvnSection_disDT, dbo.tzGetDate()) end) + 1)
							end
						) as KoikoDni,
						sum(
							coalesce(tarif.LpuSectionTariff_Tariff, 0) * 
							-- если есть МЭС, то по нему берем
							CASE WHEN
								ms.Mes_id is not null
							THEN
								ms.Mes_KoikoDni				
							-- если МЭСа нет, то по фактическому (с) Андрей Борматов
							ELSE
								case when
									lut.LpuUnitType_SysNick = 'stac'
								then
									datediff('day', case when es.EvnSection_setDT < :BegDT then :BegDT else es.EvnSection_setDT end, case when coalesce(es.EvnSection_disDT, dbo.tzGetDate()) > :EndDT then :EndDT else coalesce(es.EvnSection_disDT, dbo.tzGetDate()) end ) + abs(sign(datediff('day', case when es.EvnSection_setDT < :BegDT then :BegDT else es.EvnSection_setDT end, case when coalesce(es.EvnSection_disDT, dbo.tzGetDate()) > :EndDT then :EndDT else coalesce(es.EvnSection_disDT, dbo.tzGetDate()) end)) - 1)
								else
									(datediff('day', case when es.EvnSection_setDT < :BegDT then :BegDT else es.EvnSection_setDT end, case when coalesce(es.EvnSection_disDT, dbo.tzGetDate()) > :EndDT then :EndDT else coalesce(es.EvnSection_disDT, dbo.tzGetDate()) end) + 1)
								end
							END
						) as PlanSum,
						sum(
							coalesce(tarif.LpuSectionTariff_Tariff, 0) *
							case when
								lut.LpuUnitType_SysNick = 'stac'
							then
								datediff('day', case when es.EvnSection_setDT < :BegDT then :BegDT else es.EvnSection_setDT end, case when coalesce(es.EvnSection_disDT, dbo.tzGetDate()) > :EndDT then :EndDT else coalesce(es.EvnSection_disDT, dbo.tzGetDate()) end ) + abs(sign(datediff('day', case when es.EvnSection_setDT < :BegDT then :BegDT else es.EvnSection_setDT end, case when coalesce(es.EvnSection_disDT, dbo.tzGetDate()) > :EndDT then :EndDT else coalesce(es.EvnSection_disDT, dbo.tzGetDate()) end)) - 1)
							else
								(datediff('day', case when es.EvnSection_setDT < :BegDT then :BegDT else es.EvnSection_setDT end, case when coalesce(es.EvnSection_disDT, dbo.tzGetDate()) > :EndDT then :EndDT else coalesce(es.EvnSection_disDT, dbo.tzGetDate()) end) + 1)
							end
						) as FactSum
					from
						v_EvnSection es
						left join v_LpuSection ls1 on ls1.LpuSection_id = es.LpuSection_id
						left join v_LpuSection ls2 on ls2.LpuSection_id = ls1.LpuSection_pid
						left join LpuUnit lu on lu.LpuUnit_id = ls1.LpuUnit_id 
						left join LpuUnitType lut on lut.LpuUnitType_id = lu.LpuUnitType_id
						left join MesOld ms on ms.Mes_id = es.Mes_id
						left join lateral (
							select 
								LpuSectionTariff_Tariff
							from
								LpuSectionTariff tar
							where
								tar.LpuSection_id = CASE WHEN ls1.LpuSection_pid is null then ls1.LpuSection_id else ls1.LpuSection_pid end
								--and LpuSectionTariff_setDate <= es.EvnSection_disDate
								and TariffClass_id = 7
							order by
								LpuSectionTariff_setDate desc
							limit 1
						) as tarif on true
					where
						es.EvnSection_pid = EvnPS.EvnPS_id
						and	es.EvnSection_setDT <= :EndDT
						and coalesce(es.EvnSection_disDT, dbo.tzGetDate()) >= :BegDT
						and coalesce(ls1.LpuSectionProfile_Code, '0') not in ('1200', '1210', '1220', '2500', '1004', '1700', '610', '630', '620', '1803', '1801', '1850', '1840', '1811', '1802', '1830', '1812', '1810', '1820', '1860', '1800', '1870')
						and coalesce(ls2.LpuSectionProfile_Code, '0') not in ('1200', '1210', '1220', '2500', '1004', '1700', '610', '630', '620', '1803', '1801', '1850', '1840', '1811', '1802', '1830', '1812', '1810', '1820', '1860', '1800', '1870')
						--stac_filter
				) as evnsections on true
			WHERE
				 exists (
					select
						EvnSection_id
					from
						v_EvnSection es1 
						left join v_LpuSection ls1 on es1.LpuSection_id = ls1.LpuSection_id
						left join v_LpuSection ls2 on ls2.LpuSection_id = ls1.LpuSection_pid
					where
						es1.EvnSection_pid = EvnPS.EvnPS_id
						and	es1.EvnSection_setDT <= :EndDT
						and coalesce(es1.EvnSection_disDT, dbo.tzGetDate()) >= :BegDT
						and coalesce(ls1.LpuSectionProfile_Code, '0') not in ('1200', '1210', '1220', '2500', '1004', '1700', '610', '630', '620', '1803', '1801', '1850', '1840', '1811', '1802', '1830', '1812', '1810', '1820', '1860', '1800', '1870')
						and coalesce(ls2.LpuSectionProfile_Code, '0') not in ('1200', '1210', '1220', '2500', '1004', '1700', '610', '630', '620', '1803', '1801', '1850', '1840', '1811', '1802', '1830', '1812', '1810', '1820', '1860', '1800', '1870')
				 )
				and evnsections.KoikoDni is not null
		";

		// фильтр по фондодержанию
		if (isset($data['EvnType']) && $data['EvnType'] > 0) {
			switch ($data['EvnType']) {
				case 1:
					$body =
						$polka_events;
					break;
				case 2:
					$body =
						str_replace('--stac_filter', " and lut.LpuUnitType_SysNick = 'stac' ", $stac_events);
					break;
				case 3:
					$body =
						str_replace('--stac_filter', " and lut.LpuUnitType_SysNick != 'stac' ", $stac_events);
					break;
			}
		} else
			$body =
				$polka_events . "
					union
				" . $stac_events;

		set_time_limit(0);

		$query = "
			-- расчетное прикрепленное население
			-- addit with
			WITH People (Person_id, Server_id, Person_Surname, Person_Firname, Person_Secname)
			AS
			(
				SELECT
					distinct pc.Person_id, ps.Server_id, ps.Person_Surname, ps.Person_Firname,  ps.Person_Secname
				FROM
					v_PersonCard_all pc							
					inner join	v_Person_all pall on
						pc.Person_id = pall.Person_id
					inner join Polis  on 
						pall.Polis_id = Polis.Polis_id							
					inner join v_PersonState ps  on ps.Person_id = pc.Person_id and ps.Server_pid = 0
					left join LpuRegionType rt  on rt.LpuRegionType_id = pc.LpuRegionType_id
				WHERE
					pc.Lpu_id = :Lpu_id							
					and pc.PersonCard_begDate < :EndDT
					and coalesce(pc.PersonCard_endDate, :TheFarDate) > :EndDT					
					and Polis.Polis_begDate < :EndDT
					and coalesce(Polis.Polis_endDate, :TheFarDate) > :EndDT
					{$struct_filters}
			)
			-- end addit with
			select 
			-- select
			*
			-- end select
			from
			-- from
			(			
				{$body}
			) as subsel
			-- end from
			where 
			-- where
			(1=1)
				{$filters}
			-- end where
			order by
			-- order by
				\"Person_SurName\",
				\"Person_FirName\",
				\"Person_SecName\",
				\"Evn_endDate\"
			-- end order by
		";

		$response = array();

		$get_count_query = getCountSQLPH($query);

		$get_count_result = $this->db->query($get_count_query, $queryParams);

		if (is_object($get_count_result)) {
			$response['totalCount'] = $get_count_result->result('array');
			$response['totalCount'] = $response['totalCount'][0]['cnt'];
		} else {
			return false;
		}

		if ($data['start'] >= 0 && $data['limit'] >= 0) {
			$limit_query = getLimitSQLPH($query, $data['start'], $data['limit']);
			//die(getDebugSQL($limit_query, $queryParams));
			$result = $this->db->query($limit_query, $queryParams);
		} else {
			$result = $this->db->query($query, $queryParams);
		}

		if (is_object($result)) {
			$res = $result->result('array');
			if (is_array($res)) {
				if ($data['start'] == 0 && count($res) < $data['limit']) {
					$response['data'] = $res;
					$response['totalCount'] = count($res);
				} else {
					$response['data'] = $res;
					$get_count_query = getCountSQLPH($query);

					$get_count_result = $this->db->query($get_count_query, $queryParams);


					if (is_object($get_count_result)) {
						$response['totalCount'] = $get_count_result->result('array');
						$response['totalCount'] = $response['totalCount'][0]['cnt'];
					} else {
						return false;
					}
				}
			} else {
				return false;
			}
		} else
			return false;
		return $response;
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getFundHoldingGridReestr($data)
	{
		$queryParams = array();
		/*
		 * фильтры по датам
		 */
		$beg_date = $data['Year'] . '-' . (strlen($data['Month']) == 1 ? '0' . $data['Month'] : $data['Month']) . '-01 00:00:00';
		$end_day = date('t', strtotime($beg_date));
		$end_date = $data['Year'] . '-' . (strlen($data['Month']) == 1 ? '0' . $data['Month'] : $data['Month']) . '-' . $end_day . ' 23:59:59';

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['BegDT'] = $beg_date;
		$queryParams['EndDT'] = $end_date;
		$queryParams['TheFarDate'] = '2030-01-01 00:00:00';
		$queryParams['UKL_EndDT'] = '2011-07-01 00:00:00';

		// фильтры по структурным элементам ЛПУ
		$struct_filters = "";
		switch ($data['CalcType']) {
			case 'all_regions':
				$struct_filters .= " and lr.LpuRegion_id is not null ";
				break;
			case 'region_types':
				$reg_types = json_decode($data['RegionTypes']);
				$struct_filters .= "
					and lr.LpuRegion_id is not null
					and lr.LpuRegionType_id in (" . join(',', $reg_types) . ")
				";
				break;
			case 'regions':
				$regions = json_decode($data['Regions']);
				$struct_filters .= "
					and lr.LpuRegion_id in (" . join(',', $regions) . ")
				";
				break;
		};

		set_time_limit(0);

		$filters = "";
		// фильтр по фондодержанию
		if (isset($data['FundHolder']) && $data['FundHolder'] > 0) {
			if ($data['FundHolder'] == 1)
				$filters .= " and \"Lpu_id\" = Lpu_fid ";
			else
				$filters .= " and \"Lpu_id\" != Lpu_fid ";
		}

		// фильтр по ФИО
		if (isset($data['Person_Surname']) && strlen($data['Person_Surname']) > 0) {
			$filters .= " and \"Person_SurName\" ilike :Person_Surname ";
			$queryParams['Person_Surname'] = $data['Person_Surname'] . '%';
		}

		if (isset($data['Person_Firname']) && strlen($data['Person_Firname']) > 0) {
			$filters .= " and \"Person_FirName\" ilike :Person_Firname ";
			$queryParams['Person_Firname'] = $data['Person_Firname'] . '%';
		}

		if (isset($data['Person_Secname']) && strlen($data['Person_Secname']) > 0) {
			$filters .= " and \"Person_SecName\" ilike :Person_Secname ";
			$queryParams['Person_Secname'] = $data['Person_Secname'] . '%';
		}

		$polka_events = "
			select
				0 as \"Evn_id\",
				0 as \"EvnClass\",
				rd.Person_id as \"Person_id\",
				0 as \"Server_id\",
				ps.Person_SurName as \"Person_SurName\",
				ps.Person_FirName as \"Person_FirName\",
				ps.Person_SecName as \"Person_SecName\",
				to_char(cast(dnl as timestamp),'dd.mm.yyyy') as \"Evn_begDate\",
				to_charcast(dkl as timestamp),'dd.mm.yyyy') as \"Evn_endDate\",				
				lp.Lpu_Nick as \"Lpu_Nick\",
				lp.Lpu_id as \"Lpu_id\",
				rd.Lpu_fid as \"Lpu_fid\",
				lsp.LpuSectionProfile_Code || '. ' || lsp.LpuSectionProfile_Name as \"LpuSection_Name\",
				1 as \"KoikoDni\",
				mkb1 || '.' || mku1 as \"Diag_Name\",
				'' as \"PlanSum\",
				itog as \"FactSum\"
			from
				fh.RegistryData rd
				left join v_Lpu lp on lp.Lpu_id = rd.Lpu_id
				left join v_LpuRegion lr  on lr.LpuRegion_id = rd.LpuRegion_id
				left join v_PersonState ps  on ps.Person_id = rd.Person_id
				left join v_LpuSectionProfile lsp  on lsp.LpuSectionProfile_Code = rd.pot
			where
				rd.Lpu_fid = :Lpu_id
				and dkl	between :BegDT and :EndDT
				and rd.xl = '1'
				and rd.ei1 = 1
				and coalesce(pot, '0') not in ('1200', '1210', '1220', '2500', '1004', '1700', '610', '630', '620', '1803', '1801', '1850', '1840', '1811', '1802', '1830', '1812', '1810', '1820', '1860', '1800', '1870')
				" . $struct_filters . "
		";

		$stac_events = "
			select
				0 as \"Evn_id\",
				0 as \"EvnClass\",
				rd.Person_id as \"Person_id\",
				0 as \"Server_id\",
				ps.Person_SurName as \"Person_SurName\",
				ps.Person_FirName as \"Person_FirName\",
				ps.Person_SecName as \"Person_SecName\",
				to_char(cast(dnl as timestamp),'dd.mm.yyyy') as \"Evn_begDate\",
				to_char(cast(dkl as timestamp),'dd.mm.yyyy') as \"Evn_endDate\",
				lp.Lpu_Nick as \"Lpu_Nick\",
				lp.Lpu_id as \"Lpu_id\",
				rd.Lpu_fid as \"Lpu_fid\",
				lsp.LpuSectionProfile_Code || '. ' || lsp.LpuSectionProfile_Name as \"LpuSection_Name\",
				case when rd.xl = 2 
					then datediff('day', dnl, coalesce(dkl, dbo.tzGetDate())) + abs(sign(datediff('day', dnl, coalesce(dkl, dbo.tzGetDate()))) - 1) -- круглосуточные 
					else (datediff('day', dnl, coalesce(dkl, dbo.tzGetDate())) + 1) -- дневные 
				end as \"KoikoDni\",
				mkb1 || '.' || mku1 as \"Diag_Name\",
				'' as \"PlanSum\",
				itog as \"FactSum\"
			from
				fh.RegistryData rd
				left join v_Lpu lp on lp.Lpu_id = rd.Lpu_id
				left join v_LpuRegion lr  on lr.LpuRegion_id = rd.LpuRegion_id				
				left join v_PersonState ps on ps.Person_id = rd.Person_id
				left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_Code = rd.pot
			where
				rd.Lpu_fid = :Lpu_id
				and dkl	between :BegDT and :EndDT
				and dnl <= :EndDT
				-- stac filter
				and coalesce(pot, '0') not in ('1200', '1210', '1220', '2500', '1004', '1700', '610', '630', '620', '1803', '1801', '1850', '1840', '1811', '1802', '1830', '1812', '1810', '1820', '1860', '1800', '1870')
				" . $struct_filters . "
		";

		// фильтр по фондодержанию
		if (isset($data['EvnType']) && $data['EvnType'] > 0) {
			switch ($data['EvnType']) {
				case 1:
					$body =
						$polka_events;
					break;
				case 2:
					$body =
						str_replace('-- stac filter', " and xl in (2) ", $stac_events);
					break;
				case 3:
					$body =
						str_replace('-- stac filter', " and xl in (7, 8, 9) ", $stac_events);
					break;
			}
		} else {
			$stac_events = str_replace('-- stac filter', " and xl in (2, 7, 8, 9) ", $stac_events);
			$body =
				$polka_events . "
					union
				" . $stac_events;
		}

		set_time_limit(0);

		$query = "
			select
			-- select
			*
			-- end select
			from
			-- from
			(			
				{$body}
			) as subsel
			-- end from
			where
			-- where
			(1=1)
			{$filters}
			-- end where
			order by
			-- order by
			\"Person_SurName\",
			\"Person_FirName\",
			\"Person_SecName\",
			\"Evn_endDate\"
			-- end order by
		";

		$response = array();

		$get_count_query = getCountSQLPH($query);

		//die(getDebugSQL($get_count_query, $queryParams));

		$get_count_result = $this->db->query($get_count_query, $queryParams);

		if (is_object($get_count_result)) {
			$response['totalCount'] = $get_count_result->result('array');
			$response['totalCount'] = $response['totalCount'][0]['cnt'];
		} else {
			return false;
		}

		if ($data['start'] >= 0 && $data['limit'] >= 0) {
			$limit_query = getLimitSQLPH($query, $data['start'], $data['limit']);
			//die(getDebugSQL($limit_query, $queryParams));
			$result = $this->db->query($limit_query, $queryParams);
		} else {
			$result = $this->db->query($query, $queryParams);
		}

		if (is_object($result)) {
			$res = $result->result('array');
			$i = 1;
			foreach ($res as $key => $value) {
				$res[$key]['Evn_id'] = $i;
				$i++;
			}
			if (is_array($res)) {
				if ($data['start'] == 0 && count($res) < $data['limit']) {
					$response['data'] = $res;
					$response['totalCount'] = count($res);
				} else {
					$response['data'] = $res;
					$get_count_query = getCountSQLPH($query);

					$get_count_result = $this->db->query($get_count_query, $queryParams);


					if (is_object($get_count_result)) {
						$response['totalCount'] = $get_count_result->result('array');
						$response['totalCount'] = $response['totalCount'][0]['cnt'];
					} else {
						return false;
					}
				}
			} else {
				return false;
			}
		} else
			return false;
		return $response;
	}
}