<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Тесты анализаторов
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010-2011 Swan Ltd.
 * @author       gabdushev
 * @version
 *
 * @property AnalyzerTestUslugaComplex_model AnalyzerTestUslugaComplex_model
 * @property QualitativeTestAnswerAnalyzerTest_model QualitativeTestAnswerAnalyzerTest_model
 */
class AnalyzerTest_model extends swPgModel {
	/**
	 * Получение количества несвязанных услуг на службе с анализаторами
	 */
	function getUnlinkedUslugaComplexMedServiceCount($data)
	{
		$q = "
			select
				count(ucms.UslugaComplexMedService_id) as cnt,
				'' as \"Error_Msg\"
			from
				v_UslugaComplexMedService ucms
				inner join v_UslugaComplex uc on uc.UslugaComplex_id = ucms.UslugaComplex_id
			where
				ucms.MedService_id = :MedService_id
				and ucms.UslugaComplexMedService_pid is null
				and not exists(select AnalyzerTest_id from lis.v_AnalyzerTest where UslugaComplexMedService_id = ucms.UslugaComplexMedService_id limit 1)
				and not exists(select ucms2.UslugaComplexMedService_id from v_UslugaComplexMedService ucms2 where ucms2.UslugaComplex_id = uc.UslugaComplex_2011id limit 1) 
				-- если на службе уже есть услуга ГОСТ-2011, то данная услуга не нужна.
		";
		$r = $this->db->query($q, $data);
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Связь услуг с анализатором
	 */
	function linkUslugaComplexMedService($data) {
		$ucms_ids = json_decode($data['UslugaComplexMedService_ids'], true);
		if (count($ucms_ids) < 1) {
			return false;
		}

		$q = "
			select
				ucms.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
				to_char (ucms.UslugaComplexMedService_begDT, 'YYYY-MM-DD HH24:MI:SS') as \"UslugaComplexMedService_begDT\",
				to_char (ucms.UslugaComplexMedService_endDT, 'YYYY-MM-DD HH24:MI:SS') as \"UslugaComplexMedService_endDT\",
				ucms.UslugaComplex_id as \"UslugaComplex_id\"
			from
				v_UslugaComplexMedService ucms
				inner join v_UslugaComplex uc on uc.UslugaComplex_id = ucms.UslugaComplex_id
			where
				ucms.MedService_id = :MedService_id
				and not exists(select AnalyzerTest_id from lis.v_AnalyzerTest where UslugaComplexMedService_id = ucms.UslugaComplexMedService_id limit 1)
				and not exists(select ucms2.UslugaComplexMedService_id from v_UslugaComplexMedService ucms2 where ucms2.UslugaComplex_id = uc.UslugaComplex_2011id limit 1) -- если на службе уже есть услуга ГОСТ-2011, то данная услуга не нужна.
				and ucms.UslugaComplexMedService_pid is null
				and ucms.UslugaComplexMedService_id in (".implode(',',$ucms_ids).")
		";
		$result_ucmss = $this->db->query($q, $data);
		if ( is_object($result_ucmss) ) {
			$ucmss = $result_ucmss->result('array');
			foreach($ucmss as $ucms) {
				// достаём состав
				$query = "
					select
						ucms.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
						to_char (ucms.UslugaComplexMedService_begDT, 'YYYY-MM-DD HH24:MI:SS') as \"UslugaComplexMedService_begDT\",
						to_char (ucms.UslugaComplexMedService_endDT, 'YYYY-MM-DD HH24:MI:SS') as \"UslugaComplexMedService_endDT\",
						ucms.UslugaComplex_id as \"UslugaComplex_id\"
					from
						v_UslugaComplexMedService ucms
					where
						ucms.UslugaComplexMedService_pid = :UslugaComplexMedService_pid
						and ucms.MedService_id = :MedService_id
						and not exists(select AnalyzerTest_id from lis.v_AnalyzerTest where UslugaComplexMedService_id = ucms.UslugaComplexMedService_id limit 1)
				";
				$result_ucmss_childs = $this->db->query($query, array('UslugaComplexMedService_pid' => $ucms['UslugaComplexMedService_id'], 'MedService_id' => $data['MedService_id']));
				$ucmss_childs = $result_ucmss_childs->result('array');

				// создаём AnalyzerTest
				$query = "
					with myvars as (
						select
						  UslugaComplex_Name as analyzertest_name
						from
						  v_UslugaComplex
						where
						  UslugaComplex_id = :UslugaComplex_id
						limit 1	
					)
					select 
						analyzertest_id as \"AnalyzerTest_id\",
					    error_code as \"Error_Code\", 
						error_message as \"Error_Msg\"
					from lis.p_AnalyzerTest_ins(
						AnalyzerTest_pid := NULL,
						AnalyzerModel_id := NULL,
						Analyzer_id := :Analyzer_id,
						UslugaComplex_id := :UslugaComplex_id,
						AnalyzerTest_isTest := :AnalyzerTest_isTest,
						AnalyzerTestType_id := :AnalyzerTestType_id,
						Unit_id := NULL,
						AnalyzerTest_Code := NULL,
						AnalyzerTest_Name := (select analyzertest_name from myvars),
						AnalyzerTest_SysNick := NULL,
						AnalyzerTest_begDT := :AnalyzerTest_begDT,
						AnalyzerTest_endDT := :AnalyzerTest_endDT,
						UslugaComplexMedService_id := :UslugaComplexMedService_id,
						pmUser_id := :pmUser_id
					)
				";

				$result_saveat = $this->db->query($query, array(
					'UslugaComplex_id' => $ucms['UslugaComplex_id'],
					'AnalyzerTestType_id' => (count($ucmss_childs) == 0)?1:null,
					'AnalyzerTest_isTest' => (count($ucmss_childs) == 0)?2:1,
					'AnalyzerTest_begDT' => $ucms['UslugaComplexMedService_begDT'],
					'AnalyzerTest_endDT' => $ucms['UslugaComplexMedService_endDT'],
					'UslugaComplexMedService_id' => $ucms['UslugaComplexMedService_id'],
					'Analyzer_id' => $data['Analyzer_id'],
					'pmUser_id' => $data['pmUser_id']
				));
				if (is_object($result_saveat)) {
					$resp_saveat = $result_saveat->result('array');
					if (!empty($resp_saveat[0]['AnalyzerTest_id'])) {
						// связываем состав
						foreach($ucmss_childs as $ucmss_child) {
							$query = "
								with myvars as (
									select
										UslugaComplex_Name as analyzertest_name
									from
										v_UslugaComplex
									where
										UslugaComplex_id = :UslugaComplex_id
									limit 1
								)
								select
									analyzertest_id as \"AnalyzerTest_id\",
									error_code as \"Error_Code\",
									error_message as \"Error_Msg\"
								from lis.p_AnalyzerTest_ins(
									AnalyzerTest_pid := :AnalyzerTest_pid,
									AnalyzerModel_id := NULL,
									Analyzer_id := :Analyzer_id,
									UslugaComplex_id := :UslugaComplex_id,
									AnalyzerTest_isTest := :AnalyzerTest_isTest,
									AnalyzerTestType_id := 1,
									Unit_id := NULL,
									AnalyzerTest_Code := NULL,
									AnalyzerTest_Name := (select analyzertest_name from myvars),
									AnalyzerTest_SysNick := NULL,
									AnalyzerTest_begDT := :AnalyzerTest_begDT,
									AnalyzerTest_endDT := :AnalyzerTest_endDT,
									UslugaComplexMedService_id := :UslugaComplexMedService_id,
									pmUser_id := :pmUser_id
								)
							";

							$result_saveat_child = $this->db->query($query, array(
								'UslugaComplex_id' => $ucmss_child['UslugaComplex_id'],
								'AnalyzerTest_isTest' => 2,
								'AnalyzerTest_begDT' => $ucmss_child['UslugaComplexMedService_begDT'],
								'AnalyzerTest_endDT' => $ucmss_child['UslugaComplexMedService_endDT'],
								'UslugaComplexMedService_id' => $ucmss_child['UslugaComplexMedService_id'],
								'Analyzer_id' => $data['Analyzer_id'],
								'AnalyzerTest_pid' => $resp_saveat[0]['AnalyzerTest_id'],
								'pmUser_id' => 1
							));
						}
					}
				}
			}
		}
	}

	/**
	 * Получение несвязанных услуг на службе с анализаторами
	 */
	function getUnlinkedUslugaComplexMedServiceGrid($data)
	{
		$filter = " and ucms.UslugaComplexMedService_pid is null";
		if (!empty($data['UslugaComplexMedService_pid'])) {
			$filter = " and ucms.UslugaComplexMedService_pid = :UslugaComplexMedService_pid";
		}

		$q = "
			select
				ucms.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
				uc.UslugaComplex_Code as \"UslugaComplex_Code\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\"
			from
				v_UslugaComplexMedService ucms
				left join v_UslugaComplex uc on uc.UslugaComplex_id = ucms.UslugaComplex_id
			where
				ucms.MedService_id = :MedService_id
				and not exists(select AnalyzerTest_id from lis.v_AnalyzerTest where UslugaComplexMedService_id = ucms.UslugaComplexMedService_id limit 1)
				{$filter}
		";
		$r = $this->db->query($q, $data);
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение реагента, привязанного к тесту анализатора
	 */
	function getAnalyzerTestReagent($data) {
		$query = "
			SELECT
				ReagentNormRate_id as \"ReagentNormRate_id\"
			FROM
				lis.v_AnalyzerReagent
			WHERE
				Analyzer_id = :Analyzer_id
				AND UslugaComplex_Code = :UslugaComplex_Code
			limit 1	
		";

		$result = $this->db->query($query, $data);
		/*
		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0])) {
				return array('Error_Msg' => '', 'count' => $resp[0]['count']);
			}
		}
		*/
		if ( is_object($result) ) {
			return  $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Сохранение признака неактивности анализатора
	 */
	function saveAnalyzerTestNotActive($data)
	{
		$query = "
			update
				lis.AnalyzerTest
			set
				AnalyzerTest_IsNotActive = :AnalyzerTest_IsNotActive
			where
				AnalyzerTest_id = :AnalyzerTest_id
		";

		$this->db->query($query, $data);

		return ['success' => true];
	}

	/**
	 * Загрузка
	 */
	function load($data) {
		$q = "
			select
				at.AnalyzerTest_id as \"AnalyzerTest_id\",
				at.AnalyzerTest_SortCode as \"AnalyzerTest_SortCode\",
				at.AnalyzerTest_pid as \"AnalyzerTest_pid\",
				at.AnalyzerModel_id as \"AnalyzerModel_id\",
				uc.UslugaCategory_id as \"UslugaCategory_id\",
				at.UslugaComplex_id as \"UslugaComplex_id\",
				at.AnalyzerTestType_id as \"AnalyzerTestType_id\",
				at.Unit_id as \"Unit_id\",
				at.AnalyzerTest_Name as \"AnalyzerTest_Name\",
				at.AnalyzerTest_SysNick as \"AnalyzerTest_SysNick\",
				rvs.RefValuesSet_Name as \"RefValuesSet_Name\",
				COALESCE(at.AnalyzerTest_isTest, 1) as \"AnalyzerTest_isTest\",
				to_char(at.AnalyzerTest_begDT,'dd.mm.yyyy') as \"AnalyzerTest_begDT\",
				to_char(at.AnalyzerTest_endDT,'dd.mm.yyyy') as \"AnalyzerTest_endDT\",
				mst.MedServiceType_SysNick as \"MedServiceType_SysNick\",
				at.LabTest_id as \"LabTest_id\"
			from
				lis.v_AnalyzerTest at
				left join v_UslugaComplex uc on uc.UslugaComplex_id = at.UslugaComplex_id
				left join v_RefValuesSet rvs on rvs.RefValuesSet_id = at.RefValuesSet_id
				left join dbo.v_UslugaComplexMedService ucms on ucms.UslugaComplexMedService_id = at.UslugaComplexMedService_id
				left join dbo.v_MedService ms on ms.MedService_id = ucms.MedService_id
				left join dbo.v_MedServiceType mst on mst.MedServiceType_id = ms.MedServiceType_id
			where
				at.AnalyzerTest_id = :AnalyzerTest_id
		";
		$r = $this->db->query($q, array('AnalyzerTest_id' => $data['AnalyzerTest_id']));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadAnalyzerTestGrid($filter) {
		$where = array();
		$p = array();

		if (isset($filter['AnalyzerTest_id']) && $filter['AnalyzerTest_id']) {
			$where[] = 'at.AnalyzerTest_id = :AnalyzerTest_id';
			$p['AnalyzerTest_id'] = $filter['AnalyzerTest_id'];
		}
		if (isset($filter['AnalyzerTest_pid']) && $filter['AnalyzerTest_pid']) {
			$where[] = 'at.AnalyzerTest_pid = :AnalyzerTest_pid';
			$p['AnalyzerTest_pid'] = $filter['AnalyzerTest_pid'];
		} else {
			$where[] = 'at.AnalyzerTest_pid IS NULL';
		}
		if (isset($filter['AnalyzerModel_id']) && $filter['AnalyzerModel_id']) {
			$where[] = 'at.AnalyzerModel_id = :AnalyzerModel_id';
			$p['AnalyzerModel_id'] = $filter['AnalyzerModel_id'];
		}
		if (isset($filter['Analyzer_id']) && $filter['Analyzer_id']) {
			$where[] = 'at.Analyzer_id = :Analyzer_id';
			$p['Analyzer_id'] = $filter['Analyzer_id'];
		}
		if (isset($filter['AnalyzerTestType_id']) && $filter['AnalyzerTestType_id']) {
			$where[] = 'at.AnalyzerTestType_id = :AnalyzerTestType_id';
			$p['AnalyzerTestType_id'] = $filter['AnalyzerTestType_id'];
		}
		if (isset($filter['UslugaComplex_id']) && $filter['UslugaComplex_id']) {
			$where[] = 'at.UslugaComplex_id = :UslugaComplex_id';
			$p['UslugaComplex_id'] = $filter['UslugaComplex_id'];
		}
		if (isset($filter['Unit_id']) && $filter['Unit_id']) {
			$where[] = 'at.Unit_id = :Unit_id';
			$p['Unit_id'] = $filter['Unit_id'];
		}
		if (isset($filter['AnalyzerWorksheetType_id']) && $filter['AnalyzerWorksheetType_id']) {
			$where[] = 'at.AnalyzerTest_id IN (SELECT AnalyzerTest_id FROM lis.v_AnalyzerTest WHERE at.AnalyzerModel_id in (SELECT AnalyzerModel_id FROM lis.v_AnalyzerWorksheetType WHERE AnalyzerWorksheetType_id = :AnalyzerWorksheetType_id))';
			$p['AnalyzerWorksheetType_id'] = $filter['AnalyzerWorksheetType_id'];
		}
		if(!empty($filter['MedService_id'])) {
			$where[] = 'A.MedService_id = :MedService_id';
			$p['MedService_id'] = $filter['MedService_id'];
		}
		if(!empty($filter['IsActive'])) {
			$where[] = 'coalesce(at.AnalyzerTest_IsNotActive, 1) = 1';
			$where[] = 'coalesce(a.Analyzer_IsNotActive, 1) = 1';
			$where[] = '(at.AnalyzerTest_endDT >= dbo.tzGetDate() or at.AnalyzerTest_endDT is null)';
		}
		$where_clause = implode(' AND ', $where);
		if (empty($where_clause)) {
			$where_clause = "(1=1)";
		}
		$orderBy = "order by
				coalesce(at.AnalyzerTest_isTest, 1), coalesce(at.AnalyzerTest_SortCode, 999999999), at.AnalyzerTest_Code";

		$q = "
			SELECT
				at.AnalyzerTest_id as \"AnalyzerTest_id\",
				at.AnalyzerTest_pid as \"AnalyzerTest_pid\",
				at.AnalyzerModel_id as \"AnalyzerModel_id\",
				at.AnalyzerTestType_id as \"AnalyzerTestType_id\",
				uc.UslugaComplex_Code as \"AnalyzerTest_Code\",
				COALESCE(at.AnalyzerTest_Name, uc.UslugaComplex_Name) as \"AnalyzerTest_Name\",
			    uc.UslugaComplex_id as \"UslugaComplex_id\",
				at.AnalyzerTest_SysNick as \"AnalyzerTest_SysNick\",
				AnalyzerTest_pid_ref.AnalyzerTest_Name as \"AnalyzerTest_pid_Name\",
				AnalyzerModel_id_ref.AnalyzerModel_Name as \"AnalyzerModel_id_Name\",
				AnalyzerTestType_id_ref.AnalyzerTestType_Name as \"AnalyzerTestType_id_Name\",
				COALESCE(at.AnalyzerTest_isTest, 1) as \"AnalyzerTest_isTest\",
				to_char(AT.AnalyzerTest_begDT, 'dd.mm.yyyy') as \"AnalyzerTest_begDT\",
				to_char(AT.AnalyzerTest_endDT, 'dd.mm.yyyy') as \"AnalyzerTest_endDT\",
				at.AnalyzerTest_SortCode as \"AnalyzerTest_SortCode\",
				case when at.AnalyzerTest_IsNotActive = 2 then 1 else 0 end as \"AnalyzerTest_IsNotActive\",
				un.Unit_Name as \"Unit_Name\",
				row_number() over(
					order by
						coalesce(at.AnalyzerTest_isTest, 1),
						coalesce(at.AnalyzerTest_SortCode, 999999999),
						at.AnalyzerTest_Code
				) as row_num
			FROM
				lis.v_AnalyzerTest at
				LEFT JOIN lis.v_AnalyzerTest AnalyzerTest_pid_ref ON AnalyzerTest_pid_ref.AnalyzerTest_id = at.AnalyzerTest_pid
				LEFT JOIN lis.v_AnalyzerModel AnalyzerModel_id_ref ON AnalyzerModel_id_ref.AnalyzerModel_id = at.AnalyzerModel_id
				LEFT JOIN lis.v_AnalyzerTestType AnalyzerTestType_id_ref ON AnalyzerTestType_id_ref.AnalyzerTestType_id = at.AnalyzerTestType_id
				left join v_UslugaComplex uc on uc.UslugaComplex_id = at.UslugaComplex_id
				LEFT JOIN lis.v_Analyzer A on A.Analyzer_id = at.Analyzer_id
				left join lateral (
					select
						u.Unit_Name
					from lis.v_QuantitativeTestUnit qtu
						inner join lis.v_Unit u on u.Unit_id = qtu.Unit_id
					where
						qtu.QuantitativeTestUnit_IsBase = 2
						and qtu.AnalyzerTest_id = at.AnalyzerTest_id
					limit 1	
				) un on true
			WHERE
				{$where_clause}
			";

		if (!isset($filter['start']) && !isset($filter['limit'])) {
			$resQ =
				$q . "
			" . $orderBy;


			$result = $this->db->query($resQ, $p);
			$result = $result->result('array');
			$totalCount = count($result);

			return array(
				'data' => $result,
				'totalCount' => $totalCount
			);
		} else {
			if (empty($filter['start']))
				$p['start'] = 0;
			else $p['start'] = intval($filter['start']);
			if (empty($filter['limit']))
				$p['limit'] = 100;
			else $p['limit'] = intval($filter['limit']);

			$p['btwStart'] = $p['start'] + 1;//индекс начальной записи
			$p['btwLimit'] = $p['start'] + $p['limit'];//индекс конечной записи

			$resQ = "
			with LimitRows as (
			" . $q . $orderBy . "
			limit 1000)
			select *
			from LimitRows
			where row_num between {$p['btwStart']} and {$p['btwLimit']}";

			if (!empty($_REQUEST['getCountOnly'])) {
				$countQ = "
					with LimitRows as (
						{$q})
					select *
					from LimitRows
					order by row_num desc
					limit 1
				";
				$count = $this->db->query($countQ, $p);
				$response['data'] = array();
				if (is_object($count)) {
					$count = $count->result('array');
					$response['totalCount'] = $count[0]['row_num'];
				}

				return $response;
			}

			$result = $this->db->query($resQ, $p);
			$result = $result->result('array');
			$totalCount = $p['start'] + count($result);

			return array(
				'data' => $result,
				'totalCount' => $totalCount,
				'overLimit' => (count($result) >= $p['btwLimit'])
			);
		}
	}

	/**
	 * Удаление дублей UslugaComplexMedService
	 */
	function deleteUslugaComplexMedServiceDouble($data) {
		if (!isSuperAdmin()) {
			return array('Error_Msg' => 'Функционал только для суперадмина');
		}

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);

		$filter = "";
		if (!empty($data['MedService_id'])) {
			$queryParams['MedService_id'] = $data['MedService_id'];
			$filter .= " and ms.MedService_id = :MedService_id";
		}

		$resp = $this->queryResult("
			select
				min(ucms.UslugaComplexMedService_id) as \"UslugaComplexMedService_minid\",
				max(ucms.UslugaComplexMedService_id) as \"UslugaComplexMedService_maxid\",
				ucms.MedService_id as \"MedService_id\",
				ucms.UslugaComplexMedService_pid as \"UslugaComplexMedService_pid\",
				ucms.UslugaComplex_id as \"UslugaComplex_id\"
			from
				v_UslugaComplexMedService ucms
				inner join v_MedService ms on ms.MedService_id = ucms.MedService_id
			where
				ms.Lpu_id = :Lpu_id
				and ms.MedServiceType_id = 6
				{$filter}
			group by
				ucms.MedService_id,
				ucms.UslugaComplexMedService_pid,
				ucms.UslugaComplex_id
			having
				count(ucms.UslugaComplexMedService_id) > 1
		", $queryParams);

		foreach($resp as $respone) {
			$this->db->query("
				update
					UslugaComplexMedService
				set
					UslugaComplexMedService_pid = :UslugaComplexMedService_minid
				where
					UslugaComplexMedService_pid = :UslugaComplexMedService_maxid
			", array(
				'UslugaComplexMedService_minid' => $respone['UslugaComplexMedService_minid'],
				'UslugaComplexMedService_maxid' => $respone['UslugaComplexMedService_maxid']
			));

			$this->db->query("
				update
					lis.AnalyzerTest
				set
					UslugaComplexMedService_id = :UslugaComplexMedService_minid
				where
					UslugaComplexMedService_id = :UslugaComplexMedService_maxid
			", array(
				'UslugaComplexMedService_minid' => $respone['UslugaComplexMedService_minid'],
				'UslugaComplexMedService_maxid' => $respone['UslugaComplexMedService_maxid']
			));

			$this->db->query("
				update
					UslugaComplexResource
				set
					UslugaComplexMedService_id = :UslugaComplexMedService_minid
				where
					UslugaComplexMedService_id = :UslugaComplexMedService_maxid
			", array(
				'UslugaComplexMedService_minid' => $respone['UslugaComplexMedService_minid'],
				'UslugaComplexMedService_maxid' => $respone['UslugaComplexMedService_maxid']
			));

			$this->db->query("
				delete from
					UslugaComplexMedService
				where
					UslugaComplexMedService_id = :UslugaComplexMedService_maxid
			", array(
				'UslugaComplexMedService_maxid' => $respone['UslugaComplexMedService_maxid']
			));
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Сохранение теста/исследования
	 */
	function saveAnalyzerTest($data) {
		$procedure = 'p_AnalyzerTest_ins';

		$analyzerReagentId = null;
		if (!empty($data['Analyzer_id']) && !empty($data['ReagentNormRate_id']) && !empty($data['UslugaComplex_Code'])) {
			$analyzerReagentId = $this->getFirstResultFromQuery("
				select
					AnalyzerReagent_id as \"AnalyzerReagent_id\"
				from
					lis.v_AnalyzerReagent
				where
					Analyzer_id = :Analyzer_id
					AND UslugaComplex_Code = :UslugaComplex_Code
				limit 1	
			", $data, true);

			$queryParams = array(
				'Analyzer_id' => $data['Analyzer_id'],
				'UslugaComplex_Code' => $data['UslugaComplex_Code'],
				'ReagentNormRate_id' => $data['ReagentNormRate_id'],
				'pmUser_id' => $data['pmUser_id'],
				'AnalyzerReagent_id' => $analyzerReagentId
			);

			$result = null;
			if (empty($analyzerReagentId)) {//Новая запись
				$query = "
					insert into lis.AnalyzerReagent 
						(Analyzer_id, UslugaComplex_Code, ReagentNormRate_id, AnalyzerReagent_insDT, AnalyzerReagent_updDT, pmUser_insID, pmUser_updID)
					values	(:Analyzer_id, :UslugaComplex_Code, :ReagentNormRate_id, dbo.tzGetDate(), dbo.tzGetDate(), :pmUser_id, :pmUser_id)
				";
				$result = $this->db->query($query, $queryParams);

			} else {

				$query = "
					UPDATE lis.AnalyzerReagent 
					SET
						ReagentNormRate_id = :ReagentNormRate_id,
						AnalyzerReagent_updDT = dbo.tzGetDate(),
						pmUser_updID = :pmUser_id
					WHERE
						AnalyzerReagent_id = :AnalyzerReagent_id
				";
				$result = $this->db->query($query, $queryParams);
			}

			if (is_array($result) && !empty($result['Error_Msg'])) {
				return $result;
			}
		}

		$data['UslugaComplexMedService_id'] = null;
		if ( !empty($data['AnalyzerTest_id']) ) {
			$procedure = 'p_AnalyzerTest_upd';
		}

		if (!empty($data['Analyzer_id'])) {
			$data['MedService_id'] = $this->getFirstResultFromQuery("
				select
					MedService_id as \"MedService_id\"
				from
					lis.v_Analyzer
				where
					Analyzer_id = :Analyzer_id
				limit 1	
			", $data, true);

			$data['UslugaComplexMedService_pid'] = $this->getFirstResultFromQuery("
				select
					UslugaComplexMedService_id as \"UslugaComplexMedService_id\"
				from
					lis.v_AnalyzerTest
				where
					AnalyzerTest_id = :AnalyzerTest_pid
				limit 1		
			", $data, true);

			if (empty($data['MedService_id'])) {
				$data['MedService_id'] = null;
			}

			if (empty($data['UslugaComplexMedService_pid'])) {
				$data['UslugaComplexMedService_pid'] = null;
			}

			// сначала услугу превращаем в связанную гостовскую услугу!
			$data['UslugaComplexToAdd_id'] = $data['UslugaComplex_id'];

			$data['UslugaComplexMedService_id'] = $this->getFirstResultFromQuery("
				select
					ucm.UslugaComplexMedService_id as \"UslugaComplexMedService_id\"
				from
					v_UslugaComplexMedService ucm
				where
					ucm.UslugaComplex_id = :UslugaComplexToAdd_id
					and ucm.MedService_id = :MedService_id
					and COALESCE(ucm.UslugaComplexMedService_pid, 0) = COALESCE(:UslugaComplexMedService_pid::bigint, 0)
			", $data, true);

			if (empty($data['UslugaComplexMedService_id'])) {
				$data['UslugaComplexMedService_id'] = null;
			}

			// определяем название для услуги. (refs #59432)
			$data['UslugaComplex_Name'] = null;
			$query = "
				select distinct
					COALESCE(at.AnalyzerTest_Name, uc.UslugaComplex_Name) as \"UslugaComplex_Name\"
				from
					lis.v_AnalyzerTest at
					inner join v_UslugaComplex uc on uc.UslugaComplex_id = at.UslugaComplex_id
				where
					at.UslugaComplexMedService_id = :UslugaComplexMedService_id
					and (at.AnalyzerTest_id != :AnalyzerTest_id OR :AnalyzerTest_id IS NULL)
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($data['AnalyzerTest_Name']) && (count($resp) == 0 || (count($resp) == 1 && $resp[0]['UslugaComplex_Name'] == $data['AnalyzerTest_Name']))) {
					// если задано название с формы и не нашлось других или нашлось только одно с тем же названием
					$data['UslugaComplex_Name'] = $data['AnalyzerTest_Name'];
				} else if (empty($data['AnalyzerTest_Name']) && (count($resp) == 1)) {
					// если не задано название и одно название в тестах
					$data['UslugaComplex_Name'] = $resp[0]['UslugaComplex_Name'];
				} else if (empty($data['AnalyzerTest_Name']) && (count($resp) == 0)) {
					// название услуги ЛПУ
					$data['UslugaComplex_Name'] = $this->getFirstResultFromQuery("
						select
							UslugaComplex_Name as \"UslugaComplex_Name\"
						from
							v_UslugaComplex
						where
							UslugaComplex_id = :UslugaComplex_id
					", $data, true);
					if (empty($data['UslugaComplex_Name'])) {
						$data['UslugaComplex_Name'] = null;
					}
				} else {
					// название гостовской услуги
					$data['UslugaComplex_Name'] = $this->getFirstResultFromQuery("
						select
							UslugaComplex_Name as \"UslugaComplex_Name\"
						from
							v_UslugaComplex
						where
							UslugaComplex_id = :UslugaComplexToAdd_id
					", $data, true);

					if (empty($data['UslugaComplex_Name'])) {
						$data['UslugaComplex_Name'] = null;
					}
				}
			}

			$data['UslugaComplexMedService_begDT'] = $data['AnalyzerTest_begDT'];
			$data['UslugaComplexMedService_endDT'] = $data['AnalyzerTest_endDT'];
			$data['RefSample_id'] = null;
			if (!empty($data['UslugaComplexMedService_id'])) {
				$resp = $this->queryResult("
					select
						to_char (min(at.AnalyzerTest_begDT), 'YYYY-MM-DD HH24:MI:SS') as \"AnalyzerTest_begDT\",
						case
							when max(case when at.AnalyzerTest_endDT is null then 1 else 0 end) = 1 then
								null
							else
								to_char (max(at.AnalyzerTest_endDT), 'YYYY-MM-DD HH24:MI:SS')
						end as \"AnalyzerTest_endDT\",
						min(ucms.RefSample_id) as \"RefSample_id\"
					from
						v_UslugaComplexMedService ucms
						inner join lis.AnalyzerTest at on at.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
					where
						ucms.UslugaComplexMedService_id = :UslugaComplexMedService_id
				", array(
					'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id']
				));

				if (!empty($resp[0])) {
					if (!empty($resp[0]['AnalyzerTest_begDT']) && $resp[0]['AnalyzerTest_begDT'] < $data['UslugaComplexMedService_begDT']) {
						$data['UslugaComplexMedService_begDT'] = $resp[0]['AnalyzerTest_begDT'];
					}
					if (empty($resp[0]['AnalyzerTest_endDT']) || $resp[0]['AnalyzerTest_endDT'] > $data['UslugaComplexMedService_endDT']) {
						$data['UslugaComplexMedService_endDT'] = $resp[0]['AnalyzerTest_endDT'];
					}
					if (empty($resp[0]['RefSample_id'])) {
						$data['RefSample_id'] = $resp[0]['RefSample_id'];
					}
				}
			}

			$this->load->model('Lis_UslugaComplexMedService_model');
			$resp = $this->Lis_UslugaComplexMedService_model->doSaveUslugaComplexMedService(
				array(
					'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id'],
					'MedService_id' => $data['MedService_id'],
					'UslugaComplex_id' => $data['UslugaComplexToAdd_id'],
					'UslugaComplexMedService_pid' => $data['UslugaComplexMedService_pid'],
					'UslugaComplexMedService_begDT' => $data['UslugaComplexMedService_begDT'],
					'UslugaComplexMedService_endDT' => $data['UslugaComplexMedService_endDT'],
					'RefSample_id' => $data['RefSample_id'],
					'UslugaComplex_Name' => $data['UslugaComplex_Name'],
					'LpuEquipment_id' => null,
					'session' => $data['session'],
					'pmUser_id' => $data['pmUser_id']
				)
			);

			if (!empty($resp['UslugaComplexMedService_id'])) {
				$data['UslugaComplexMedService_id'] = $resp['UslugaComplexMedService_id'];
			}

			if (empty($data['UslugaComplexMedService_id'])) {
				return array('Error_Msg' => 'Ошибка сохранения услуги на службе');
			}


			if (!empty($data['AnalyzerTest_id'])) {
				// апдейтим родительскую услугу составу
				$query = "
					with myvars as (
						select
							UslugaComplexMedService_id
						from
							lis.v_AnalyzerTest
						where
							AnalyzerTest_pid = :AnalyzerTest_id
					)
					
					update
						UslugaComplexMedService
					set
						UslugaComplexMedService_pid = :UslugaComplexMedService_id
					where
						UslugaComplexMedService_id in (select uslugacomplexmedservice_id from myvars)
				";

				$this->db->query($query, $data);
			}
		}

		if (!empty($data['AnalyzerTest_id'])) {
			// чистим варианты ответов или единицы измерения в зависимости от типа теста
			if ($data['AnalyzerTestType_id'] == 2) {
				$this->clearQuantitativeTestUnit($data);
			} else {
				$this->clearQualitativeTestAnswerAnalyzerTest($data);
			}
		}

		$q = "
			with myvars as (
				select
					refvaluesset_id
				from
					lis.v_AnalyzerTest
				where
					AnalyzerTest_id = :AnalyzerTest_id		
			)
			
			select
				analyzertest_id as \"AnalyzerTest_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from lis.".$procedure."(
				AnalyzerTest_id := :AnalyzerTest_id,
				AnalyzerTest_pid := :AnalyzerTest_pid,
				AnalyzerModel_id := :AnalyzerModel_id,
				Analyzer_id := :Analyzer_id,
				UslugaComplex_id := :UslugaComplex_id,
				AnalyzerTest_isTest := :AnalyzerTest_isTest,
				AnalyzerTestType_id := :AnalyzerTestType_id,
				Unit_id := :Unit_id,
				AnalyzerTest_Code := NULL,
				AnalyzerTest_Name := :AnalyzerTest_Name,
				AnalyzerTest_SysNick := :AnalyzerTest_SysNick,
				AnalyzerTest_begDT := :AnalyzerTest_begDT,
				AnalyzerTest_endDT := :AnalyzerTest_endDT,
				AnalyzerTest_SortCode := :AnalyzerTest_SortCode,
				UslugaComplexMedService_id := :UslugaComplexMedService_id,
				RefValuesSet_id := (select refvaluesset_id from myvars),
				LabTest_id := :LabTest_id,
				pmUser_id := :pmUser_id
			)
		";
		$r = $this->db->query($q, $data);
		if ( is_object($r) ) {
			return $r->result('array');
		}

		return false;
	}

	/**
	 * Получение мнемоники
	 */
	function getSysNickForAnalyzerTest($data) {
		$response = array('Error_Msg' => '', 'test_sysnick' => '');

		$query = "
			select
				tm.code as \"Test_SysNick\"
			from
				lis.v__test t
				inner join lis.v__testMappings tm on tm.test_id = t.id
				inner join lis.v_Link l on cast(l.lis_id as varchar) = tm.equipment_id and l.link_object = 'Analyzer'
			where
				l.object_id = :Analyzer_id and
				t.code = (select
							uc.UslugaComplex_Code
						from
							v_UslugaComplex uc
						where
							uc.UslugaComplex_id = :UslugaComplex_id
						limit 1
						)
			limit 1	
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (!empty($resp[0]['Test_SysNick'])) {
				$response['Test_SysNick'] = $resp[0]['Test_SysNick'];
			}
		}

		return $response;
	}

	/**
	 * Фикс тестов анализаторов
	 */
	function fixAnalyzerTest($data) {
		if (!isSuperAdmin()) {
			return array('Error_Msg' => 'Функционал только для суперадмина');
		}

		if (!empty($data['MedService_id'])) {
			$filter = "a.MedService_id = :MedService_id";
			$queryParams = array('MedService_id' => $data['MedService_id']);
		} else if (!empty($data['Lpu_id'])) {
			$filter = "ms.Lpu_id = :Lpu_id";
			$queryParams = array('Lpu_id' => $data['Lpu_id']);
		} else {
			return array('Error_Msg' => 'Необходимо указать МО или службу');
		}

		$resp_at = $this->queryResult("
			select
				at.AnalyzerTest_id as \"AnalyzerTest_id\",
				at.AnalyzerTest_Name as \"AnalyzerTest_Name\",
				at.AnalyzerTest_SysNick as \"AnalyzerTest_SysNick\",
				at.AnalyzerTest_pid as \"AnalyzerTest_pid\",
				at.AnalyzerModel_id as \"AnalyzerModel_id\",
				at.Analyzer_id as \"Analyzer_id\",
				at.AnalyzerTest_isTest as \"AnalyzerTest_isTest\",
				at.UslugaComplex_id as \"UslugaComplex_id\",
				at.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
				to_char (at.AnalyzerTest_begDT, 'YYYY-MM-DD HH24:MI:SS') as \"AnalyzerTest_begDT\",
				to_char (at.AnalyzerTest_endDT, 'YYYY-MM-DD HH24:MI:SS') as \"AnalyzerTest_endDT\",
				at.AnalyzerTest_SortCode as \"AnalyzerTest_SortCode\",
				at.AnalyzerTestType_id as \"AnalyzerTestType_id\",
				at.Unit_id as \"Unit_id\",
				null as ReagentNormRate_id as \"null as ReagentNormRate_id\",
				null as UslugaComplex_Code as \"null as UslugaComplex_Code\"
			from
				lis.v_AnalyzerTest at
				inner join lis.v_Analyzer a on a.Analyzer_id = at.Analyzer_id
				inner join v_MedService ms on ms.MedService_id = a.MedService_id
			where
				{$filter}
		", $queryParams);

		foreach($resp_at as $one_at) {
			$one_at['pmUser_id'] = $data['pmUser_id'];
			$resp_save = $this->saveAnalyzerTest($one_at);
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Сохранение
	 */
	function save($data) {
		$savedata = $this->saveAnalyzerTest($data);

		if (!empty($data['AnalyzerTest_id']) && !empty($data['AnalyzerTest_endDT'])) {
			// при закрытии закрываем и все дочерние
			$query = "
				update
					lis.AnalyzerTest
				set
					AnalyzerTest_endDT = :AnalyzerTest_endDT
				where
					AnalyzerTest_pid = :AnalyzerTest_id and AnalyzerTest_endDT IS NULL
			";

			$this->db->query($query, $data);
		}

		if (!empty($savedata[0]['AnalyzerTest_id']) && empty($data['AnalyzerTest_id'])
			&& $data['AnalyzerTest_isTest'] == 1 ) {
			// при добавлении исследования копируем состав услуги
			$query = "
				select
					ucc.UslugaComplex_id as \"UslugaComplex_id\",
					uc.UslugaComplex_Name as \"UslugaComplex_Name\",
					atest.code as \"AnalyzerTest_SysNick\"
				from
					v_UslugaComplexComposition ucc
					inner join v_UslugaComplex uc on ucc.UslugaComplex_id = uc.UslugaComplex_id
					left join lateral(
						select
							tm.code
						from
							lis.v__testMappings tm
							inner join lis.v__test t on t.id = tm.test_id
							inner join lis.v_Link l on l.link_object = 'Analyzer' and l.lis_id = tm.equipment_id::bigint
						where
							t.code = uc.UslugaComplex_Code
							and l.object_id = :Analyzer_id
						limit 1	
					) atest on true -- пробуем получить мнемонику
				where
					ucc.UslugaComplex_pid = :UslugaComplex_id
			";

			$resultContent = $this->db->query($query, $data);

			if ( is_object($resultContent) ) {
				$resp = $resultContent->result('array');
				foreach ($resp as $respone) {
					$data['AnalyzerTest_pid'] = $savedata[0]['AnalyzerTest_id'];
					$data['AnalyzerTestType_id'] = 1;
					$data['AnalyzerTest_isTest'] = 2;
					$data['AnalyzerTest_Name'] = $respone['UslugaComplex_Name'];
					$data['AnalyzerTest_SysNick'] = $respone['AnalyzerTest_SysNick'];
					$data['UslugaComplex_id'] = $respone['UslugaComplex_id'];
					$this->saveAnalyzerTest($data);
				}
			}
		}

		return $savedata;
	}

	/**
	 * Очистка связанных с тестом вариантов ответов
	 */
	function clearQualitativeTestAnswerAnalyzerTest($data)
	{
		$query = "
			select
				QualitativeTestAnswerAnalyzerTest_id as \"QualitativeTestAnswerAnalyzerTest_id\"
			from
				lis.v_QualitativeTestAnswerAnalyzerTest
			where
				AnalyzerTest_id = :AnalyzerTest_id
		";
		
		$this->load->model('QualitativeTestAnswerAnalyzerTest_model');
		
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');
			foreach($resp as $respone) {
				$this->QualitativeTestAnswerAnalyzerTest_model->delete(array(
					'QualitativeTestAnswerAnalyzerTest_id' => $respone['QualitativeTestAnswerAnalyzerTest_id']
				));
			}
		}
	}

	/**
	 * Очистка связанных с тестом единиц измерения
	 */
	function clearQuantitativeTestUnit($data)
	{
		$query = "
			select
				QuantitativeTestUnit_id as \"QuantitativeTestUnit_id\"
			from
				lis.v_QuantitativeTestUnit
			where
				AnalyzerTest_id = :AnalyzerTest_id
		";
		
		$this->load->model('QuantitativeTestUnit_model');
		
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');
			foreach($resp as $respone) {
				$this->QuantitativeTestUnit_model->delete(array(
					'QuantitativeTestUnit_id' => $respone['QuantitativeTestUnit_id']
				));
			}
		}
	}

	/**
	 * Удаление
	 */
	function delete($data) {
		$this->beginTransaction();
		// проверяем наличие услуг в составе
		$query = "
			select
				AnalyzerTest_id as \"AnalyzerTest_id\"
			from
				lis.v_AnalyzerTest
			where
				AnalyzerTest_pid = :AnalyzerTest_id
			limit 1	
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['AnalyzerTest_id'])) {
				return array('Error_Msg' => 'Нельзя удалить исследование содержащее в себе услуги');
			}
		}

		// получаем связанную услугу на службе
		$data['UslugaComplexMedService_id'] = null;
		$query = "
			select
				UslugaComplexMedService_id as \"UslugaComplexMedService_id\"
			from
				lis.v_AnalyzerTest
			where
				AnalyzerTest_id = :AnalyzerTest_id
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['UslugaComplexMedService_id'])) {
				$data['UslugaComplexMedService_id'] = $resp[0]['UslugaComplexMedService_id'];
			}
		}

		// удаляем тест анализатора
		$q = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from lis.p_AnalyzerTest_del(
				AnalyzerTest_id := :AnalyzerTest_id
			)
		";
		$r = $this->db->query($q, array(
			'AnalyzerTest_id' => $data['AnalyzerTest_id']
		));
		if ( is_object($r) ) {
			$resp = $r->result('array');
			if (!empty($resp[0]['Error_Code'])) {
				$this->rollbackTransaction();
				return $resp;
			}
		}


		if (!empty($data['UslugaComplexMedService_id'])) {
			// чистим ссылку и удаляем комплексную услугу на службе
			$query = "
				update
					lis.AnalyzerTest
				set
					UslugaComplexMedService_id = NULL
				where
					AnalyzerTest_id = :AnalyzerTest_id
			";
			$this->db->query($query, $data);

			$needDeleteUslugaComplexMedService = true;
			// проверяем что больше нет тестов с ссылкой на данную услугу
			$query = "
				select
					AnalyzerTest_id as \"AnalyzerTest_id\"
				from
					lis.v_AnalyzerTest
				where
					UslugaComplexMedService_id = :UslugaComplexMedService_id
				limit 1	
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['AnalyzerTest_id'])) {
					// если нашли ещё ссылки, то удалять услугу на службе не надо
					$needDeleteUslugaComplexMedService = false;
				}
			}

			if ($needDeleteUslugaComplexMedService) {
				$ret = $this->deleteUslugaComplexMedService(array(
					'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id']
				));

				if (!empty($ret[0]['Error_Msg'])) {
					$this->rollbackTransaction();
					return $ret;
				}
			}
		}

		$this->commitTransaction();
		return array('Error_Msg' => '');
	}

	/**
	 * Удаление услуги на службе для postgre
	*/
	function deleteUslugaComplexMedService($data) {
		$this->load->model('UslugaComplex_model', 'UslugaComplex_model');
		$this->load->swapi('common');
		// Получаем состав входящих услуг и проверяем возможность удаления каждой услуги
		$query = "
			Select
				UslugaComplexMedService_id as \"UslugaComplexMedService_id\"
			from
				v_UslugaComplexMedService
			where
				UslugaComplexMedService_pid = :UslugaComplexMedService_id
		";
		$composition = array();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$composition = $result->result('array');
			if (is_array($composition) && count($composition)>0) {
				foreach ($composition as $item) {
					$res = $this->common->GET('UslugaComplex/checkUslugaComplexMedServiceIsUsed', [
						'UslugaComplexMedService_id' => $item['UslugaComplexMedService_id'],
						'tablesToPass' => []
					], 'single');
					if (!$this->isSuccessful($res)) {
						return array(array('Error_Msg' => $res['Error_Msg']));
					}
				}
				// если мы пришли сюда, значит можем удалить состав, но предварительно надо проверить сможем ли мы удалить саму услугу
			}
		}

		// Проверка наличия ссылок на услугу в других таблицах
		$res = $this->common->GET('UslugaComplex/checkUslugaComplexMedServiceIsUsed', [
			'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id'],
			'tablesToPass' => array('UslugaComplexMedService')
		], 'single');
		if (!$this->isSuccessful($res)) {
			return array(array('Error_Msg' => $res['Error_Msg']));
		}

		$query = "
			select
				Error_code as \"Error_Code\",
				Error_message as \"Error_Msg\"
			from p_UslugaComplexMedService_del(
				UslugaComplexMedService_id := :UslugaComplexMedService_id
			)	
		";
		if (is_array($composition) && count($composition)>0) { // Если у данной услуги есть состав
			foreach ($composition as $item) { // то удаляем состав
				$result = $this->db->query($query, $item);
				if ( !is_object($result) ) {
					return false;
				}
			}
		}
		// удаляем саму услугу
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка комбо тестов
	 */
	function loadList($filter) {
		$where = array();
		$p = array();
        $join = '';
		if (isset($filter['AnalyzerTest_id']) && $filter['AnalyzerTest_id']) {
			$where[] = 'v_AnalyzerTest.AnalyzerTest_id = :AnalyzerTest_id';
			$p['AnalyzerTest_id'] = $filter['AnalyzerTest_id'];
		}
		if (isset($filter['AnalyzerTest_pid']) && $filter['AnalyzerTest_pid']) {
			$where[] = 'v_AnalyzerTest.AnalyzerTest_pid = :AnalyzerTest_pid';
			$p['AnalyzerTest_pid'] = $filter['AnalyzerTest_pid'];
		}
		if (isset($filter['AnalyzerModel_id']) && $filter['AnalyzerModel_id']) {
			$where[] = 'v_AnalyzerTest.AnalyzerModel_id = :AnalyzerModel_id';
			$p['AnalyzerModel_id'] = $filter['AnalyzerModel_id'];
		}
		if (isset($filter['AnalyzerTest_Code']) && $filter['AnalyzerTest_Code']) {
			$where[] = 'v_AnalyzerTest.AnalyzerTest_Code = :AnalyzerTest_Code';
			$p['AnalyzerTest_Code'] = $filter['AnalyzerTest_Code'];
		}
		if (isset($filter['AnalyzerTest_Name']) && $filter['AnalyzerTest_Name']) {
			$where[] = 'v_AnalyzerTest.AnalyzerTest_Name = :AnalyzerTest_Name';
			$p['AnalyzerTest_Name'] = $filter['AnalyzerTest_Name'];
		}
		if (isset($filter['AnalyzerTest_SysNick']) && $filter['AnalyzerTest_SysNick']) {
			$where[] = 'v_AnalyzerTest.AnalyzerTest_SysNick = :AnalyzerTest_SysNick';
			$p['AnalyzerTest_SysNick'] = $filter['AnalyzerTest_SysNick'];
		}
		if (isset($filter['AnalyzerTestType_id']) && $filter['AnalyzerTestType_id']) {
			$where[] = 'v_AnalyzerTest.AnalyzerTestType_id = :AnalyzerTestType_id';
			$p['AnalyzerTestType_id'] = $filter['AnalyzerTestType_id'];
		}
		if (isset($filter['Unit_id']) && $filter['Unit_id']) {
			$where[] = 'v_AnalyzerTest.Unit_id = :Unit_id';
			$p['Unit_id'] = $filter['Unit_id'];
		}
		if (isset($filter['AnalyzerWorksheetType_id']) && $filter['AnalyzerWorksheetType_id']) {
			$where[] = 'v_AnalyzerTest.AnalyzerTest_id IN (SELECT AnalyzerTest_id FROM lis.v_AnalyzerTest at WHERE at.AnalyzerModel_id in (SELECT AnalyzerModel_id FROM lis.v_AnalyzerWorksheetType WHERE AnalyzerWorksheetType_id = :AnalyzerWorksheetType_id))';
			$p['AnalyzerWorksheetType_id'] = $filter['AnalyzerWorksheetType_id'];
		}
        if(!empty($filter['UslugaComplex_id'])) {
            $where[] = 'v_AnalyzerTest.UslugaComplex_id = :UslugaComplex_id';
            $p['UslugaComplex_id'] = $filter['UslugaComplex_id'];
        }
        if(!empty($filter['Analyzer_id'])) {
            $where[] = 'v_AnalyzerTest.Analyzer_id = :Analyzer_id';
            $p['Analyzer_id'] = $filter['Analyzer_id'];
        }
        if(!empty($filter['AnalyzerTest_isTest'])) {
            $where[] = 'v_AnalyzerTest.AnalyzerTest_isTest = :AnalyzerTest_isTest';
            $p['AnalyzerTest_isTest'] = $filter['AnalyzerTest_isTest'];
        }
        if(!empty($filter['MedService_id'])) {
            $where[] = 'A.MedService_id = :MedService_id';
            $join .= 'LEFT JOIN lis.v_Analyzer A on A.Analyzer_id = v_AnalyzerTest.Analyzer_id';
            $p['MedService_id'] = $filter['MedService_id'];
        }
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		$q = "
			SELECT
				v_AnalyzerTest.AnalyzerTest_id as \"AnalyzerTest_id\",
				v_AnalyzerTest.AnalyzerTest_pid as \"AnalyzerTest_pid\",
				v_AnalyzerTest.AnalyzerModel_id as \"AnalyzerModel_id\",
				uc.UslugaComplex_Code as \"AnalyzerTest_Code\",
				COALESCE(v_AnalyzerTest.AnalyzerTest_Name, uc.UslugaComplex_Name) as \"AnalyzerTest_Nam\",
				v_AnalyzerTest.AnalyzerTest_SysNick as \"AnalyzerTest_SysNick\",
				v_AnalyzerTest.AnalyzerTestType_id as \"AnalyzerTestType_id\",
				v_AnalyzerTest.Unit_id as \"Unit_id\",
				AnalyzerTest_pid_ref.AnalyzerTest_Name as \"AnalyzerTest_pid_Name\",
				AnalyzerModel_id_ref.AnalyzerModel_Name as \"AnalyzerModel_id_Name\",
				AnalyzerTestType_id_ref.AnalyzerTestType_Name as \"AnalyzerTestType_id_Name\",
				Unit_id_ref.Unit_Name as \"Unit_id_Name\"
			FROM
				lis.v_AnalyzerTest
				LEFT JOIN lis.v_AnalyzerTest AnalyzerTest_pid_ref ON AnalyzerTest_pid_ref.AnalyzerTest_id = v_AnalyzerTest.AnalyzerTest_pid
				LEFT JOIN lis.v_AnalyzerModel AnalyzerModel_id_ref ON AnalyzerModel_id_ref.AnalyzerModel_id = v_AnalyzerTest.AnalyzerModel_id
				LEFT JOIN lis.v_AnalyzerTestType AnalyzerTestType_id_ref ON AnalyzerTestType_id_ref.AnalyzerTestType_id = v_AnalyzerTest.AnalyzerTestType_id
				LEFT JOIN lis.v_Unit Unit_id_ref ON Unit_id_ref.Unit_id = v_AnalyzerTest.Unit_id
				left join v_UslugaComplex uc on uc.UslugaComplex_id = v_AnalyzerTest.UslugaComplex_id
				$join
			$where_clause
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Проверка даты начала теста на соответствие дате завершения единиц измерения для теста
	 */
	function checkAnalyzerTestBegDate($data)
	{
		$params = array(
			'AnalyzerTest_id' => $data['AnalyzerTest_id'],
			'AnalyzerTest_begDT' => $data['AnalyzerTest_begDT']
		);
		$query = "
			select
				to_char (u.Unit_endDate, 'dd.mm.yyyy') as \"endDT\"
			from
				lis.v_AnalyzerTest at 
				left join lis.v_QuantitativeTestUnit qtu on qtu.AnalyzerTest_id = at.AnalyzerTest_id
				left join lis.v_Unit u on u.Unit_id = qtu.Unit_id
			where
				at.AnalyzerTest_id = :AnalyzerTest_id
				and u.Unit_endDate < CAST(:AnalyzerTest_begDT as date)
			order by 
				u.Unit_endDate
			limit 1	
		";
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Проверка теста на дубликат в списке тестов для модели или для экземпляра анализатора
	 */
	function checkAnalyzerTestIsExists($data)
	{
		$where = array();
		$p = array();
		$join = "";

		if (isset($data['AnalyzerModel_id']) && $data['AnalyzerModel_id']) {
			$where[] = 'at.AnalyzerModel_id = :AnalyzerModel_id';
			$p['AnalyzerModel_id'] = $data['AnalyzerModel_id'];
		}
		if (isset($data['Analyzer_id']) && $data['Analyzer_id']) {
			$where[] = 'at.Analyzer_id = :Analyzer_id';
			$p['Analyzer_id'] = $data['Analyzer_id'];
		}
		if (isset($data['UslugaComplex_id']) && $data['UslugaComplex_id']) {
			//$where[] = 'uc.UslugaComplex_id = :UslugaComplex_id';
			$where[] = 'uc.UslugaComplex_2011id = :UslugaComplex_id';
			$p['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}

		if (!empty($data['AnalyzerTest_pid'])) {
			$where[] = 'at.AnalyzerTest_pid = :AnalyzerTest_pid';
			$p['AnalyzerTest_pid'] = $data['AnalyzerTest_pid'];
		} else {
			$where[] = 'at.AnalyzerTest_pid is null';
		}

		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = ' WHERE '.$where_clause;
		}
		$query = "
		SELECT
			at.Analyzer_id as \"Analyzer_id\",
			at.AnalyzerModel_id as \"AnalyzerModel_id\",
			uc.UslugaComplex_Code as \"UslugaComplex_Code\"
		FROM lis.v_AnalyzerTest at
		LEFT JOIN v_UslugaComplex uc on uc.UslugaComplex_id = at.UslugaComplex_id
		{$join}
		{$where_clause}
		";
		$result = $this->db->query($query, $p);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
     * Получение списка тестов НСИ, связанных с выбранной услугой
     */
    function loadLabTestList($data) {
		if (!empty($data['UslugaComplex_id'])) {
			$cwhere = "";
			if (!empty($data['query'])) {
				$filter = $data['query'];

				$cwhere = " and (lt.LabTest_Code  ilike '{$filter}%'
				 or lt.LabTest_ShortName ilike '%{$filter}%') ";
			};

			$q = "
				Select
					lt.LabTest_id as \"LabTest_id\",
					lt.LabTest_Code as \"LabTest_Code\",
					lt.LabTest_ShortName as \"LabTest_Name\"
				from UslugaComplex uc
				left join UslugaComplex ucg  on uc.UslugaComplex_2011id = ucg.UslugaComplex_id
				left join nsi.NomenMedUsluga nmu  on cast(nmu.NomenMedUsluga_Code as bigint) = COALESCE(uc.UslugaComplex_oid, ucg.UslugaComplex_oid)
				left join nsi.LabTestLink ltl  on ltl.NomenMedUsluga_id = nmu.NomenMedUsluga_id
				inner join nsi.LabTest lt  on lt.LabTest_id = ltl.LabTest_id
				where uc.UslugaComplex_id = :UslugaComplex_id
			" . $cwhere;
			$r = $this->db->query($q, $data);
			if ( is_object($r) ) {
				return $r->result('array');
			}
		}
		return false;
	}
	/**
	 * Получение списка тестов НСИ, не связанных с выбранной услугой
	 *  А.И.Г. 24.03.2020 Поддержка #194077
	 */
	function loadLabTestNoList($data)
	{
		$cwhere = " ";
		$cSql = "limit 40 ";
		if (!empty($data['query'])) {
			$filter = $data['query'];

			$cwhere = " and (lt.LabTest_Code  ilike '{$filter}%'
				 or lt.LabTest_ShortName ilike '%{$filter}%') ";
			$cSql = "";
		}
		$q = "select lt.LabTest_id as \"LabTest_id\",
					lt.LabTest_Code as \"LabTest_Code\",
					lt.LabTest_ShortName as \"LabTest_Name\"
				from nsi.LabTest lt 
				where (1=1)
			" . $cwhere . $cSql;
		if (!empty($data['LabTest_id'])) {
			$q = " select lt.LabTest_id as \"LabTest_id\",
						  lt.LabTest_Code as \"LabTest_Code\",
						  lt.LabTest_ShortName as \"LabTest_Name\"
				   from nsi.LabTest lt 
				   where (1=1) and lt.LabTest_id = :LabTest_id
			";
		}

		$r = $this->db->query($q, $data);
		if (is_object($r)) {
			return $r->result('array');
		}
	}

	function loadAnalyzerTestType ($params) {
		$with = ""; $join = "";
		if ($params['Analyzer_id']) {
			$with = "with MST as (
				select ms.MedServiceType_id
				from lis.v_Analyzer a
				inner join dbo.v_MedService ms on ms.MedService_id = a.MedService_id
				where a.Analyzer_id = :Analyzer_id
			)";
			$join = "inner join MST on MST.MedServiceType_id = att.MedServiceType_id";
		}
		$query = "{$with}
			select
				att.AnalyzerTestType_id as \"AnalyzerTestType_id\",
				att.AnalyzerTestType_Code as \"AnalyzerTestType_Code\",
				att.AnalyzerTestType_Name as \"AnalyzerTestType_Name\"
			from lis.v_AnalyzerTestType att
			{$join}
		";
		try { return $this->queryResult($query, $params);
		} catch (Exception $e) { throw $e; }

	}
}
