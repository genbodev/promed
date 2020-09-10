<?php

require_once(APPPATH.'models/_pgsql/MedService_model.php');

class Samara_MedService_model extends MedService_model {
	/**
	 * __construct
	 */ 
	function __construct() {
		parent::__construct();
	}  
	/**
	 * getLpuMedServiceTypes
	 */ 
	function getLpuMedServiceTypes($data)
	{
		$params = array();
		$sql = "
			select
				MedService_id as \"MedService_id\",
				MedService_Name as \"MedService_Name\"
			from MedService ms
			where MedServiceType_id = :MedServiceType_id
				and Lpu_id = :Lpu_id
		";
		//print_r($data);
		$result = $this->db->query($sql, $data);
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
	 * getUslugaComplexSelectList
	 */ 	
	function getUslugaComplexSelectList($data)
	{
		$filter = '';
		$filter_ms = '';
		$join_ms = '';
		$filter_mst = '';
		$params = array(
				'LpuSection_id' => $data['userLpuSection_id'],
				'pmUser_id' => $data['pmUser_id'],
		);
	
		if ( !empty($data['allowedUslugaComplexAttributeList']) ) {
			$allowedUslugaComplexAttributeList = json_decode($data['allowedUslugaComplexAttributeList'], true);
	
			if ( is_array($allowedUslugaComplexAttributeList) && count($allowedUslugaComplexAttributeList) > 0 ) {
				$filter .= " and exists (
					select t1.UslugaComplexAttribute_id
					from v_UslugaComplexAttribute t1
						inner join v_UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
					where t1.UslugaComplex_id = uc11.UslugaComplex_id
						and t2.UslugaComplexAttributeType_SysNick in ('" . implode("', '", $allowedUslugaComplexAttributeList) . "')
				)";
				if (count($allowedUslugaComplexAttributeList) == 1) {
					switch (true) {
						case (in_array('manproc',$allowedUslugaComplexAttributeList)):
							$filter_mst = ' and ms.MedServiceType_id = 13';
							break;
						case (in_array('oper',$allowedUslugaComplexAttributeList)):
							$filter_mst = ' and ms.MedServiceType_id = 5';
							break;
						case (in_array('lab',$allowedUslugaComplexAttributeList)):
							$join_ms = '
								inner join lis.v_AnalyzerTest at on at.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
								inner join lis.v_Analyzer a on a.Analyzer_id = at.Analyzer_id
							';
							$filter_mst = ' and ms.MedServiceType_id = 6 and coalesce(at.AnalyzerTest_IsNotActive, 1) = 1 and coalesce(a.Analyzer_IsNotActive, 1) = 1';
							break;
						case (in_array('func',$allowedUslugaComplexAttributeList)):
							$filter_mst = ' and ms.MedServiceType_id = 8';
							break;
						case (in_array('consult',$allowedUslugaComplexAttributeList)):
							$filter_mst = ' and ms.MedServiceType_id = 29';
							break;
					}
				}
			}
		}
	
		if (!empty($data['filterByLpu_id'])) {
			// Фильтруем места оказания по ЛПУ
			$filter_ms .= ' and ms.Lpu_id = :Lpu_id';
			$filter .= ' and ms.MedService_id is not null';
			$params['Lpu_id'] = $data['filterByLpu_id'];
		}
	
	
		if (!empty($data['MedService_id'])) {
			// Фильтруем места оказания по ЛПУ
			$filter_ms .= ' and ms.MedService_id = :MedService_id';
			$params['MedService_id'] = $data['MedService_id'];
		}
	
		if (!empty($data['filterByLpu_str'])) {
			// Фильтруем места оказания по ЛПУ Lpu_Nick или Lpu_Name ?
			$filter .= ' and l.Lpu_Nick ilike :lpuQuery';
			$params['lpuQuery'] = '%'.$data['filterByLpu_str'].'%';
		}
	
		if (!empty($data['filterByUslugaComplex_id'])) {
			// будем показывать только услуги с совпадающим UslugaComplex_2011id
			$filter .= " and uc11.UslugaComplex_id = :UslugaComplex_2011id";
			$params['UslugaComplex_2011id'] = $data['filterByUslugaComplex_id'];
		}
	
		if (!empty($data['filterByUslugaComplex_str'])) {
			$filter .= ' and (coalesce(uc.UslugaComplex_Code, uc11.UslugaComplex_Code) ilike :ucQuery or coalesce(uc.UslugaComplex_Name, uc11.UslugaComplex_Name) ilike :ucQuery)';
			$params['ucQuery'] = '%'.$data['filterByUslugaComplex_str'].'%';
		}
	
		$sql = "
		select
		-- select
			uc11.UslugaComplex_id as \"UslugaComplex_2011id\",
			coalesce(uc.UslugaComplex_id, uc11.UslugaComplex_id) as \"UslugaComplex_id\",
			coalesce(uc.UslugaComplex_Code, uc11.UslugaComplex_Code) as \"UslugaComplex_Code\",
			coalesce(uc.UslugaComplex_Name, uc11.UslugaComplex_Name) as \"UslugaComplex_Name\",
			coalesce(( 
				select
					COUNT(ms.MedService_id)
				from v_MedService ms
					inner join v_UslugaComplexMedService ucms on ms.MedService_id = ucms.MedService_id
					{$join_ms}
					inner join v_Lpu tl on tl.Lpu_id = ms.Lpu_id
				where
				--показываем службы только уровня отделения, для корректного направления
					ms.LpuSection_id is not null
					and ucms.UslugaComplexMedService_pid IS NULL
					{$filter_mst}
					{$filter_ms}
					and exists (
						select
							uc.UslugaComplex_id
						from v_UslugaComplex uc
						where uc.UslugaComplex_2011id = uc11.UslugaComplex_id
							and uc.UslugaComplex_id = ucms.UslugaComplex_id
						limit 1
					)
					and cast(ms.MedService_begDT as date) <= cast(GETDATE() as date)
					and (ms.MedService_endDT is null OR cast(ms.MedService_endDT as date) > cast(GETDATE() as date))
					and cast(ucms.UslugaComplexMedService_begDT as date) <= cast(GETDATE() as date)
					and (ucms.UslugaComplexMedService_endDT is null OR cast(ucms.UslugaComplexMedService_endDT as date) > cast(GETDATE() as date))
			), 0) as \"MedService_cnt\",
			ms.MedService_id as \"MedService_id\",
			ms.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
			ms.MedService_Name as \"MedService_Name\",
			ms.MedService_Nick as \"MedService_Nick\",
			mst.MedServiceType_id as \"MedServiceType_id\",
			mst.MedServiceType_SysNick as \"MedServiceType_SysNick\",
			l.Lpu_id as \"Lpu_id\",
			l.Lpu_Nick as \"Lpu_Nick\",
			lu.LpuBuilding_id as \"LpuBuilding_id\",
			lu.LpuBuilding_Name as \"LpuBuilding_Name\",
			lu.LpuUnit_id as \"LpuUnit_id\",
			lu.LpuUnit_Name as \"LpuUnit_Name\",
			lua.Address_Address as \"LpuUnit_Address\",
			lu.LpuUnitType_id as \"LpuUnitType_id\",
			lu.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
			ls.LpuSection_id as \"LpuSection_id\",
			ls.LpuSection_Name as \"LpuSection_Name\",
			ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
			ls.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
			ttms.MedService_id as \"ttms_MedService_id\"
			,ttms.TimetableMedService_id as \"TimetableMedService_id\"
			,to_char(ttms.TimetableMedService_begTime, 'dd.mm.yyyy hh24:mi') as \"TimetableMedService_begTime\"
			,case
				when ms.MedService_id is not null and exists(
					select
						UslugaComplexMedService_id
					from v_UslugaComplexMedService
					where UslugaComplexMedService_pid = ms.UslugaComplexMedService_id
					limit 1
				) then 1
				when ms.MedService_id is null and exists(
					select
						UslugaComplex_id
					from v_UslugaComplexComposition
					where UslugaComplex_pid = uc11.UslugaComplex_id
					limit 1
				) then 1
				else 0
			 end as \"isComposite\"
			,pzm.MedService_id as \"pzm_MedService_id\"
			,pzm.Lpu_id as \"pzm_Lpu_id\"
			,pzm.MedServiceType_id as \"pzm_MedServiceType_id\"
			,pzm.MedServiceType_SysNick as \"pzm_MedServiceType_SysNick\"
			,pzm.MedService_Name as \"pzm_MedService_Name\"
			,pzm.MedService_Nick as \"pzm_MedService_Nick\"
		-- end select
		from
		-- from
			v_UslugaComplex uc11
			inner join v_LpuSection user_ls on user_ls.LpuSection_id = :LpuSection_id
			inner join v_LpuUnit user_lu on user_lu.LpuUnit_id = user_ls.LpuUnit_id
			left join lateral(
				select
					ms.MedService_id,
					ms.Lpu_id,
					ms.LpuBuilding_id,
					ms.LpuUnit_id,
					ms.LpuSection_id,
					ms.MedServiceType_id,
					ms.MedService_Name,
					ms.MedService_Nick,
					ucms.UslugaComplexMedService_id,
					ucms.UslugaComplex_id
				from v_MedService ms
					inner join v_UslugaComplexMedService ucms on ms.MedService_id = ucms.MedService_id
					{$join_ms}
					inner join v_Lpu tl on tl.Lpu_id = ms.Lpu_id -- https://redmine.swan.perm.ru/issues/25958
				where
				--показываем службы только уровня отделения, для корректного направления
					ms.LpuSection_id is not null
					and ucms.UslugaComplexMedService_pid IS NULL
					{$filter_mst}
					{$filter_ms}
					and exists (
						select
							uc.UslugaComplex_id
						from v_UslugaComplex uc
						where uc.UslugaComplex_2011id = uc11.UslugaComplex_id
							and uc.UslugaComplex_id = ucms.UslugaComplex_id
						limit 1
					)
					and cast(ms.MedService_begDT as date) <= cast(GETDATE() as date)
					and (ms.MedService_endDT is null OR cast(ms.MedService_endDT as date) > cast(GETDATE() as date))
					and cast(ucms.UslugaComplexMedService_begDT as date) <= cast(GETDATE() as date)
					and (ucms.UslugaComplexMedService_endDT is null OR cast(ucms.UslugaComplexMedService_endDT as date) > cast(GETDATE() as date))
					--ищем службы сначала в своем отделении, в своей группа отделений, в нашем подразделение, потом в своем МО, в противном случае берем первое попавшееся
				order by
					case when ms.Lpu_id=user_ls.Lpu_id then 1 else 2 end,
					case when ms.LpuBuilding_id=user_lu.LpuBuilding_id then 1 else 2 end,
					case when ms.LpuUnit_id=user_ls.LpuUnit_id then 1 else 2 end,
					case when ms.LpuSection_id=user_ls.LpuSection_id then 1 else 2 end
				limit 1
			) ms on true
			left join v_UslugaComplex uc on uc.UslugaComplex_id = ms.UslugaComplex_id
			left join v_UslugaCategory cat on cat.UslugaCategory_id = uc.UslugaCategory_id
			left join lateral(
				select
					pzm.MedService_id,
					pzm.Lpu_id,
					pzm.LpuBuilding_id,
					pzm.LpuUnit_id,
					pzm.LpuSection_id,
					pzm.MedServiceType_id,
					'pzm' as MedServiceType_SysNick,
					pzm.MedService_Name,
					pzm.MedService_Nick,
					ucpzm.UslugaComplexMedService_id
				from v_MedServiceLink msl
					left join v_MedService pzm on pzm.MedServiceType_id = 7
						and msl.MedService_id = pzm.MedService_id
						and (pzm.MedService_endDT is null OR cast(pzm.MedService_endDT as date) > cast(GETDATE() as date))
					left join v_UslugaComplexMedService ucpzm on ucpzm.MedService_id = pzm.MedService_id
						and ucpzm.UslugaComplex_id = uc.UslugaComplex_id
				where msl.MedService_lid = ms.MedService_id
					and msl.MedServiceLinkType_id = 1
					{$filter_ms}
				limit 1
			) pzm on true
			left join v_LpuUnit lu on lu.LpuUnit_id = ms.LpuUnit_id
			left join v_Lpu l on ms.Lpu_id = l.Lpu_id
			left join v_LpuSection ls on ms.LpuSection_id = ls.LpuSection_id
			left join v_MedServiceType mst on ms.MedServiceType_id = mst.MedServiceType_id
			left join v_Address lua on lua.Address_id = coalesce(lu.Address_id,l.UAddress_id)
			left join lateral(
				select
					ttms.TimetableMedService_id,
					ttms.TimetableMedService_begTime,
					ttms.MedService_id
				from v_TimetableMedService_lite ttms
				where ttms.Person_id is null
					and (
					ttms.UslugaComplexMedService_id = pzm.UslugaComplexMedService_id
					OR ttms.MedService_id = pzm.MedService_id
					OR ttms.UslugaComplexMedService_id = ms.UslugaComplexMedService_id
					OR ttms.MedService_id = ms.MedService_id
					)
					and ttms.TimetableMedService_begTime >= GETDATE()
				limit 1
			) ttms on true
			left join lateral(
				select
					EU.EvnUsluga_id
				from v_EvnUsluga EU
					inner join v_UslugaComplex UC on EU.UslugaComplex_id = UC.UslugaComplex_id
				where EU.pmUser_insID = :pmUser_id
					and UC.UslugaComplex_2011id = uc11.UslugaComplex_id
				limit 1
			) EU on true
		-- end from
		WHERE
		-- where
			uc11.UslugaCategory_id = 4 -- ГОСТ-2011
			and coalesce(uc.UslugaComplex_id, uc11.UslugaComplex_id) not in (
				select t1.UslugaComplex_id
				from v_UslugaComplexAttribute t1
				inner join v_UslugaComplexAttributeType t2 on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
				where t2.UslugaComplexAttributeType_SysNick in ('noprescr')
			)
			{$filter}
		-- end where
		ORDER BY
		-- order by
			case when EU.EvnUsluga_id is null then 2 else 1 end,
			case when ms.MedService_id is null then 2 else 1 end,
			case when ms.Lpu_id=user_ls.Lpu_id then '' else l.Lpu_Nick end,
			case when ms.LpuBuilding_id=user_lu.LpuBuilding_id then '' else lu.LpuBuilding_Name end,
			case when ms.LpuUnit_id=user_ls.LpuUnit_id then '' else lu.LpuUnit_Name end,
			case when ms.LpuSection_id=user_ls.LpuSection_id then '' else ls.LpuSection_Name end,
			coalesce(uc.UslugaComplex_Name, uc11.UslugaComplex_Name)
		-- end order by
		";
	
		/*
		Порядок отображения услуг:
		Последние N услуг-служб либо услуг без служб, на которые данный врач создавал направления.
		Наше отделение
		Наша группа отделений
		Наше подразделение
		Наше ЛПУ
		Услуги в других ЛПУ
		Прочие услуги из справочника, которые не оказывается в других ЛПУ.
		*/
	
		/*
		,user_ls.Lpu_id as user_Lpu_id
		,user_lu.LpuBuilding_id as user_LpuBuilding_id
		,user_ls.LpuUnit_id as user_LpuUnit_id
		,user_ls.LpuSection_id as user_LpuSection_id
		*/
	
		//echo getDebugSQL(getLimitSQLPH($sql, $data['start'], $data['limit']), $params); die;
		$result = $this->db->query(getLimitSQLPH($sql, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($sql), $params);
		// echo getDebugSQL(getCountSQLPH($sql), $params); die;
	
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

		
}
