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
class AnalyzerTest_model extends swModel {
	/**
	 * Получение количества несвязанных услуг на службе с анализаторами
	 */
	function getUnlinkedUslugaComplexMedServiceCount($data)
	{
		$q = "
			select
				count(ucms.UslugaComplexMedService_id) as cnt,
				'' as Error_Msg
			from
				v_UslugaComplexMedService ucms (nolock)
				inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = ucms.UslugaComplex_id
			where
				ucms.MedService_id = :MedService_id
				and ucms.UslugaComplexMedService_pid is null
				and not exists(select top 1 AnalyzerTest_id from lis.v_AnalyzerTest (nolock) where UslugaComplexMedService_id = ucms.UslugaComplexMedService_id)
				and not exists(select top 1 ucms2.UslugaComplexMedService_id from v_UslugaComplexMedService ucms2 (nolock) where ucms2.UslugaComplex_id = uc.UslugaComplex_2011id) -- если на службе уже есть услуга ГОСТ-2011, то данная услуга не нужна.
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
				ucms.UslugaComplexMedService_id,
				convert(varchar(10), ucms.UslugaComplexMedService_begDT, 120) as UslugaComplexMedService_begDT,
				convert(varchar(10), ucms.UslugaComplexMedService_endDT, 120) as UslugaComplexMedService_endDT,
				ucms.UslugaComplex_id
			from
				v_UslugaComplexMedService ucms (nolock)
				inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = ucms.UslugaComplex_id
			where
				ucms.MedService_id = :MedService_id
				and not exists(select top 1 AnalyzerTest_id from lis.v_AnalyzerTest (nolock) where UslugaComplexMedService_id = ucms.UslugaComplexMedService_id)
				and not exists(select top 1 ucms2.UslugaComplexMedService_id from v_UslugaComplexMedService ucms2 (nolock) where ucms2.UslugaComplex_id = uc.UslugaComplex_2011id) -- если на службе уже есть услуга ГОСТ-2011, то данная услуга не нужна.
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
						ucms.UslugaComplexMedService_id,
						convert(varchar(10), ucms.UslugaComplexMedService_begDT, 120) as UslugaComplexMedService_begDT,
						convert(varchar(10), ucms.UslugaComplexMedService_endDT, 120) as UslugaComplexMedService_endDT,
						ucms.UslugaComplex_id
					from
						v_UslugaComplexMedService ucms (nolock)
					where
						ucms.UslugaComplexMedService_pid = :UslugaComplexMedService_pid
						and ucms.MedService_id = :MedService_id
						and not exists(select top 1 AnalyzerTest_id from lis.v_AnalyzerTest (nolock) where UslugaComplexMedService_id = ucms.UslugaComplexMedService_id)
				";
				$result_ucmss_childs = $this->db->query($query, array('UslugaComplexMedService_pid' => $ucms['UslugaComplexMedService_id'], 'MedService_id' => $data['MedService_id']));
				$ucmss_childs = $result_ucmss_childs->result('array');

				// создаём AnalyzerTest
				$query = "
					declare
						@AnalyzerTest_id bigint,
						@AnalyzerTest_Name varchar(500),
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @AnalyzerTest_Name = (
						select top 1
							UslugaComplex_Name
						from
							v_UslugaComplex (nolock)
						where
							UslugaComplex_id = :UslugaComplex_id
					);
					exec lis.p_AnalyzerTest_ins
						@AnalyzerTest_id = @AnalyzerTest_id output,
						@AnalyzerTest_pid = NULL,
						@AnalyzerModel_id = NULL,
						@Analyzer_id = :Analyzer_id,
						@UslugaComplex_id = :UslugaComplex_id,
						@AnalyzerTest_IsTest = :AnalyzerTest_IsTest,
						@AnalyzerTestType_id = :AnalyzerTestType_id,
						@Unit_id = NULL,
						@AnalyzerTest_Code = NULL,
						@AnalyzerTest_Name = @AnalyzerTest_Name,
						@AnalyzerTest_SysNick = NULL,
						@AnalyzerTest_begDT = :AnalyzerTest_begDT,
						@AnalyzerTest_endDT = :AnalyzerTest_endDT,
						@UslugaComplexMedService_id = :UslugaComplexMedService_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @AnalyzerTest_id as AnalyzerTest_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";

				$result_saveat = $this->db->query($query, array(
					'UslugaComplex_id' => $ucms['UslugaComplex_id'],
					'AnalyzerTestType_id' => (count($ucmss_childs) == 0)?1:null,
					'AnalyzerTest_IsTest' => (count($ucmss_childs) == 0)?2:1,
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
								declare
									@AnalyzerTest_id bigint,
									@AnalyzerTest_Name varchar(500),
									@ErrCode int,
									@ErrMessage varchar(4000);
								set @AnalyzerTest_Name = (
									select top 1
										UslugaComplex_Name
									from
										v_UslugaComplex (nolock)
									where
										UslugaComplex_id = :UslugaComplex_id
								);
								exec lis.p_AnalyzerTest_ins
									@AnalyzerTest_id = @AnalyzerTest_id output,
									@AnalyzerTest_pid = :AnalyzerTest_pid,
									@AnalyzerModel_id = NULL,
									@Analyzer_id = :Analyzer_id,
									@UslugaComplex_id = :UslugaComplex_id,
									@AnalyzerTest_IsTest = :AnalyzerTest_IsTest,
									@AnalyzerTestType_id = 1,
									@Unit_id = NULL,
									@AnalyzerTest_Code = NULL,
									@AnalyzerTest_Name = @AnalyzerTest_Name,
									@AnalyzerTest_SysNick = NULL,
									@AnalyzerTest_begDT = :AnalyzerTest_begDT,
									@AnalyzerTest_endDT = :AnalyzerTest_endDT,
									@UslugaComplexMedService_id = :UslugaComplexMedService_id,
									@pmUser_id = :pmUser_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMessage output;
								select @AnalyzerTest_id as AnalyzerTest_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
							";

							$result_saveat_child = $this->db->query($query, array(
								'UslugaComplex_id' => $ucmss_child['UslugaComplex_id'],
								'AnalyzerTest_IsTest' => 2,
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
				ucms.UslugaComplexMedService_id,
				uc.UslugaComplex_Code,
				uc.UslugaComplex_Name
			from
				v_UslugaComplexMedService (nolock) ucms
				left join v_UslugaComplex (nolock) uc on uc.UslugaComplex_id = ucms.UslugaComplex_id
			where
				ucms.MedService_id = :MedService_id
				and not exists(select top 1 AnalyzerTest_id from lis.v_AnalyzerTest (nolock) where UslugaComplexMedService_id = ucms.UslugaComplexMedService_id)
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
			SELECT TOP 1
				ReagentNormRate_id
			FROM
				[lis].[AnalyzerReagent] (nolock)
			WHERE
				Analyzer_id = :Analyzer_id
				AND UslugaComplex_Code = :UslugaComplex_Code
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
				lis.AnalyzerTest with (rowlock)
			set
				AnalyzerTest_IsNotActive = :AnalyzerTest_IsNotActive
			where
				AnalyzerTest_id = :AnalyzerTest_id
		";

		$this->db->query($query, $data);

		return true;
	}

	/**
	 * Загрузка
	 */
	function load($data) {
		$q = "
			select
				at.AnalyzerTest_id,
				at.AnalyzerTest_SortCode,
				at.AnalyzerTest_pid,
				at.AnalyzerModel_id,
				uc.UslugaCategory_id,
				at.UslugaComplex_id,
				at.AnalyzerTestType_id,
				at.Unit_id,
				at.AnalyzerTest_Name,
				at.AnalyzerTest_SysNick,
				rvs.RefValuesSet_Name,
				ISNULL(at.AnalyzerTest_isTest, 1) as AnalyzerTest_isTest,
				convert(varchar(10),at.AnalyzerTest_begDT,104) as AnalyzerTest_begDT,
				convert(varchar(10),at.AnalyzerTest_endDT,104) as AnalyzerTest_endDT,
				mst.MedServiceType_SysNick,
				at.LabTest_id
			from
				lis.v_AnalyzerTest at with(nolock)
				left join v_UslugaComplex (nolock) uc on uc.UslugaComplex_id = at.UslugaComplex_id
				left join v_RefValuesSet (nolock) rvs on rvs.RefValuesSet_id = at.RefValuesSet_id
				left join dbo.v_UslugaComplexMedService (nolock) ucms on ucms.UslugaComplexMedService_id = at.UslugaComplexMedService_id
				left join dbo.v_MedService (nolock) ms on ms.MedService_id = ucms.MedService_id
				left join dbo.v_MedServiceType (nolock) mst on mst.MedServiceType_id = ms.MedServiceType_id
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
			$where[] = 'at.AnalyzerTest_id IN (SELECT AnalyzerTest_id FROM lis.v_AnalyzerTest at with(nolock) WHERE at.AnalyzerModel_id in (SELECT AnalyzerModel_id FROM lis.v_AnalyzerWorksheetType WHERE AnalyzerWorksheetType_id = :AnalyzerWorksheetType_id))';
			$p['AnalyzerWorksheetType_id'] = $filter['AnalyzerWorksheetType_id'];
		}
		if(!empty($filter['MedService_id'])) {
			$where[] = 'A.MedService_id = :MedService_id';
			$p['MedService_id'] = $filter['MedService_id'];
		}
		if(!empty($filter['IsActive'])) {
			$where[] = 'ISNULL(at.AnalyzerTest_IsNotActive, 1) = 1';
			$where[] = 'ISNULL(a.Analyzer_IsNotActive, 1) = 1';
			$where[] = '(at.AnalyzerTest_endDT >= dbo.tzGetDate() or at.AnalyzerTest_endDT is null)';
		}
		$where_clause = implode(' AND ', $where);
		if (empty($where_clause)) {
			$where_clause = "(1=1)";
		}

		$orderBy = "order by
				ISNULL(at.AnalyzerTest_isTest, 1), ISNULL(at.AnalyzerTest_SortCode, 999999999), at.AnalyzerTest_Code";

		$q = "
				at.AnalyzerTest_id,
				at.AnalyzerTest_pid,
				at.AnalyzerModel_id,
				at.AnalyzerTestType_id,
				uc.UslugaComplex_Code as AnalyzerTest_Code,
				ISNULL(at.AnalyzerTest_Name, uc.UslugaComplex_Name) as AnalyzerTest_Name,
				uc.UslugaComplex_id as UslugaComplex_id,
				at.AnalyzerTest_SysNick,
				AnalyzerTest_pid_ref.AnalyzerTest_Name AnalyzerTest_pid_Name,
				AnalyzerModel_id_ref.AnalyzerModel_Name AnalyzerModel_id_Name,
				AnalyzerTestType_id_ref.AnalyzerTestType_Name AnalyzerTestType_id_Name,
				ISNULL(at.AnalyzerTest_isTest, 1) as AnalyzerTest_isTest,
				convert(varchar(10),AT.AnalyzerTest_begDT,104) as AnalyzerTest_begDT,
				convert(varchar(10),AT.AnalyzerTest_endDT,104) as AnalyzerTest_endDT,
				at.AnalyzerTest_SortCode,
				case when at.AnalyzerTest_IsNotActive = 2 then 1 else 0 end as AnalyzerTest_IsNotActive,
				un.Unit_Name,
				row_number() over (
					order by
						isnull(at.AnalyzerTest_isTest, 1), isnull(at.AnalyzerTest_SortCode, 999999999),
						at.AnalyzerTest_Code) as row_num
			FROM
				lis.v_AnalyzerTest at WITH (NOLOCK)
				LEFT JOIN lis.v_AnalyzerTest AnalyzerTest_pid_ref WITH (NOLOCK) ON AnalyzerTest_pid_ref.AnalyzerTest_id = at.AnalyzerTest_pid
				LEFT JOIN lis.v_AnalyzerModel AnalyzerModel_id_ref WITH (NOLOCK) ON AnalyzerModel_id_ref.AnalyzerModel_id = at.AnalyzerModel_id
				LEFT JOIN lis.v_AnalyzerTestType AnalyzerTestType_id_ref WITH (NOLOCK) ON AnalyzerTestType_id_ref.AnalyzerTestType_id = at.AnalyzerTestType_id
				left join v_UslugaComplex (nolock) uc on uc.UslugaComplex_id = at.UslugaComplex_id
				LEFT JOIN lis.v_Analyzer A WITH(NOLOCK) on A.Analyzer_id = at.Analyzer_id
				outer apply (
					select top 1
						u.Unit_Name
					from lis.v_QuantitativeTestUnit qtu (nolock)
						inner join lis.v_Unit u (nolock) on u.Unit_id = qtu.Unit_id
					where
						qtu.QuantitativeTestUnit_IsBase = 2
						and qtu.AnalyzerTest_id = at.AnalyzerTest_id
				) un
			WHERE
				{$where_clause}
		";

		if (!isset($filter['start']) && !isset($filter['limit'])) {
			$resQ = "
				select
			" . $q . "
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
			with LimitRows as (select top 1000
			" . $q . $orderBy . ")
			select *
			from LimitRows
			where row_num between {$p['btwStart']} and {$p['btwLimit']}";

			if (!empty($_REQUEST['getCountOnly'])) {
				$countQ = "
					with LimitRows as (select
						{$q})
					select top 1 *
					from LimitRows
					order by row_num desc
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
				min(ucms.UslugaComplexMedService_id) as UslugaComplexMedService_minid,
				max(ucms.UslugaComplexMedService_id) as UslugaComplexMedService_maxid,
				ucms.MedService_id,
				ucms.UslugaComplexMedService_pid,
				ucms.UslugaComplex_id
			from
				v_UslugaComplexMedService ucms (nolock)
				inner join v_MedService ms (nolock) on ms.MedService_id = ucms.MedService_id
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
					UslugaComplexMedService with (rowlock)
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
					lis.AnalyzerTest with (rowlock)
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
					UslugaComplexResource with (rowlock)
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
					UslugaComplexMedService with (rowlock)
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
				select top 1
					AnalyzerReagent_id
				from
					[lis].[AnalyzerReagent] (nolock)
				where
					Analyzer_id = :Analyzer_id
					AND UslugaComplex_Code = :UslugaComplex_Code
			", $data);

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
					SET NOCOUNT ON

					DECLARE
						@Error_Code bigint,
						@Error_Message varchar(4000);
						
					Begin TRY

						insert into lis.AnalyzerReagent with (rowlock) 
							(Analyzer_id, UslugaComplex_Code, ReagentNormRate_id, AnalyzerReagent_insDT, AnalyzerReagent_updDT, pmUser_insID, pmUser_updID)
						values	(:Analyzer_id, :UslugaComplex_Code, :ReagentNormRate_id, dbo.tzGetDate(), dbo.tzGetDate(), :pmUser_id, :pmUser_id)

					End TRY
					Begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
					End catch

					SELECT
						@Error_Code as Error_Code,
						@Error_Message as Error_Msg;

					SET NOCOUNT OFF
				";
				$result = $this->db->query($query, $queryParams);

			} else {

				$query = "
					SET NOCOUNT ON

					DECLARE
						@Error_Code bigint,
						@Error_Message varchar(4000);
						
					Begin TRY

						UPDATE lis.AnalyzerReagent with (rowlock) 
						SET
							ReagentNormRate_id = :ReagentNormRate_id,
							AnalyzerReagent_updDT = dbo.tzGetDate(),
							pmUser_updID = :pmUser_id
						WHERE
							AnalyzerReagent_id = :AnalyzerReagent_id

					End TRY
					Begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
					End catch

					SELECT
						@Error_Code as Error_Code,
						@Error_Message as Error_Msg;

					SET NOCOUNT OFF
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
				select top 1
					MedService_id
				from
					lis.v_Analyzer (nolock)
				where
					Analyzer_id = :Analyzer_id
			", $data);

			$data['UslugaComplexMedService_pid'] = $this->getFirstResultFromQuery("
				select top 1
					UslugaComplexMedService_id
				from
					lis.v_AnalyzerTest (nolock)
				where
					AnalyzerTest_id = :AnalyzerTest_pid
			", $data);

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
					ucm.UslugaComplexMedService_id
				from
					v_UslugaComplexMedService ucm (nolock)
				where
					ucm.UslugaComplex_id = :UslugaComplexToAdd_id
					and ucm.MedService_id = :MedService_id
					and ISNULL(ucm.UslugaComplexMedService_pid, 0) = ISNULL(:UslugaComplexMedService_pid, 0)
			", $data);

			if (empty($data['UslugaComplexMedService_id'])) {
				$data['UslugaComplexMedService_id'] = null;
			}

			// определяем название для услуги. (refs #59432)
			$data['UslugaComplex_Name'] = null;
			$query = "
				select distinct
					ISNULL(at.AnalyzerTest_Name, uc.UslugaComplex_Name) as UslugaComplex_Name
				from
					lis.v_AnalyzerTest at (nolock)
					inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = at.UslugaComplex_id
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
							UslugaComplex_Name
						from
							v_UslugaComplex (nolock)
						where
							UslugaComplex_id = :UslugaComplex_id
					", $data);

					if (empty($data['UslugaComplex_Name'])) {
						$data['UslugaComplex_Name'] = null;
					}
				} else {
					// название гостовской услуги
					$data['UslugaComplex_Name'] = $this->getFirstResultFromQuery("
						select
							UslugaComplex_Name
						from
							v_UslugaComplex (nolock)
						where
							UslugaComplex_id = :UslugaComplexToAdd_id
					", $data);

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
						convert(varchar(10), min(at.AnalyzerTest_begDT), 120) as AnalyzerTest_begDT,
						case
							when max(case when at.AnalyzerTest_endDT is null then 1 else 0 end) = 1 then
								null
							else
								convert(varchar(10), max(at.AnalyzerTest_endDT), 120)
						end as AnalyzerTest_endDT,
						min(ucms.RefSample_id) as RefSample_id
					from
						v_UslugaComplexMedService ucms (nolock)
						inner join lis.AnalyzerTest (nolock) at on at.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
					where
						ucms.UslugaComplexMedService_id = :UslugaComplexMedService_id
				", array(
					'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id']
				));

				if (!empty($resp[0])) {
					if ($resp[0]['AnalyzerTest_begDT'] < $data['UslugaComplexMedService_begDT']) {
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

			$this->load->model('UslugaComplexMedService_model');
			$resp = $this->UslugaComplexMedService_model->doSave(array(
				'scenario' => self::SCENARIO_DO_SAVE,
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
			));

			if (!empty($resp['UslugaComplexMedService_id'])) {
				$data['UslugaComplexMedService_id'] = $resp['UslugaComplexMedService_id'];
			}

			if (empty($data['UslugaComplexMedService_id'])) {
				return array('Error_Msg' => 'Ошибка сохранения услуги на службе');
			}


			if (!empty($data['AnalyzerTest_id'])) {
				// апдейтим родительскую услугу составу
				$query = "
					update
						ucms with (rowlock)
					set
						ucms.UslugaComplexMedService_pid = :UslugaComplexMedService_id
					from
						lis.v_AnalyzerTest at (nolock)
						inner join UslugaComplexMedService ucms on ucms.UslugaComplexMedService_id = at.UslugaComplexMedService_id
					where
						at.AnalyzerTest_pid = :AnalyzerTest_id
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
			declare
				@AnalyzerTest_id bigint,
				@RefValuesSet_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @RefValuesSet_id = (
				select top 1
					RefValuesSet_id
				from
					lis.v_AnalyzerTest (nolock)
				where
					AnalyzerTest_id = :AnalyzerTest_id
			);
			set @AnalyzerTest_id = :AnalyzerTest_id;
			exec lis." . $procedure . "
				@AnalyzerTest_id = @AnalyzerTest_id output,
				@AnalyzerTest_pid = :AnalyzerTest_pid,
				@AnalyzerModel_id = :AnalyzerModel_id,
				@Analyzer_id = :Analyzer_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@AnalyzerTest_isTest = :AnalyzerTest_isTest,
				@AnalyzerTestType_id = :AnalyzerTestType_id,
				@Unit_id = :Unit_id,
				@AnalyzerTest_Code = NULL,
				@AnalyzerTest_Name = :AnalyzerTest_Name,
				@AnalyzerTest_SysNick = :AnalyzerTest_SysNick,
				@AnalyzerTest_begDT = :AnalyzerTest_begDT,
				@AnalyzerTest_endDT = :AnalyzerTest_endDT,
				@AnalyzerTest_SortCode = :AnalyzerTest_SortCode,
				@UslugaComplexMedService_id = :UslugaComplexMedService_id,
				@RefValuesSet_id = @RefValuesSet_id,
				@LabTest_id = :LabTest_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @AnalyzerTest_id as AnalyzerTest_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			declare
				@UslugaComplex_Code varchar(50);
				
			set @UslugaComplex_Code = (
				select top 1
					uc.UslugaComplex_Code
				from
					v_UslugaComplex uc (nolock)
				where
					uc.UslugaComplex_id = :UslugaComplex_id
			);
			
			select top 1
				tm.code as test_sysnick
			from
				lis._test t (nolock)
				inner join lis._testMappings tm (nolock) on tm.test_id = t.id
				inner join lis.v_Link l (nolock) on l.lis_id = tm.equipment_id and l.link_object = 'Analyzer'
			where
				l.object_id = :Analyzer_id and
				t.code = @UslugaComplex_Code
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (!empty($resp[0]['test_sysnick'])) {
				$response['test_sysnick'] = $resp[0]['test_sysnick'];
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
				at.AnalyzerTest_id,
				at.AnalyzerTest_Name,
				at.AnalyzerTest_SysNick,
				at.AnalyzerTest_pid,
				at.AnalyzerModel_id,
				at.Analyzer_id,
				at.AnalyzerTest_isTest,
				at.UslugaComplex_id,
				at.UslugaComplexMedService_id,
				convert(varchar(10), at.AnalyzerTest_begDT, 120) as AnalyzerTest_begDT,
				convert(varchar(10), at.AnalyzerTest_endDT, 120) as AnalyzerTest_endDT,
				at.AnalyzerTest_SortCode,
				at.AnalyzerTestType_id,
				at.Unit_id,
				null as ReagentNormRate_id,
				null as UslugaComplex_Code
			from
				lis.AnalyzerTest at (nolock)
				inner join lis.Analyzer a (nolock) on a.Analyzer_id = at.Analyzer_id
				inner join v_MedService ms (nolock) on ms.MedService_id = a.MedService_id
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
					lis.AnalyzerTest with (rowlock)
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
					ucc.UslugaComplex_id,
					uc.UslugaComplex_Name,
					atest.code as AnalyzerTest_SysNick
				from
					v_UslugaComplexComposition ucc (nolock)
					inner join v_UslugaComplex uc (nolock) on ucc.UslugaComplex_id = uc.UslugaComplex_id
					outer apply(
						select top 1
							tm.code
						from
							lis._testMappings tm (nolock)
							inner join lis._test t (nolock) on t.id = tm.test_id
							inner join lis.v_Link l (nolock) on l.link_object = 'Analyzer' and l.lis_id = tm.equipment_id
						where
							t.code = uc.UslugaComplex_Code
							and l.object_id = :Analyzer_id
					) atest -- пробуем получить мнемонику
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
				QualitativeTestAnswerAnalyzerTest_id
			from
				lis.QualitativeTestAnswerAnalyzerTest with (nolock)
			where
				AnalyzerTest_id = :AnalyzerTest_id
		";

		$this->load->model('QualitativeTestAnswerAnalyzerTest_model', 'QualitativeTestAnswerAnalyzerTest_model');

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
				QuantitativeTestUnit_id
			from
				lis.QuantitativeTestUnit with (nolock)
			where
				AnalyzerTest_id = :AnalyzerTest_id
		";

		$this->load->model('QuantitativeTestUnit_model', 'QuantitativeTestUnit_model');

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
			select top 1
				AnalyzerTest_id
			from
				lis.v_AnalyzerTest (nolock)
			where
				AnalyzerTest_pid = :AnalyzerTest_id
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
				UslugaComplexMedService_id
			from
				lis.v_AnalyzerTest (nolock)
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec lis.p_AnalyzerTest_del
				@AnalyzerTest_id = :AnalyzerTest_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
					lis.AnalyzerTest with (rowlock)
				set
					UslugaComplexMedService_id = NULL
				where
					AnalyzerTest_id = :AnalyzerTest_id;
			";
			$this->db->query($query, $data);

			$needDeleteUslugaComplexMedService = true;
			// проверяем что больше нет тестов с ссылкой на данную услугу
			$query = "
				select top 1
					AnalyzerTest_id
				from
					lis.v_AnalyzerTest (nolock)
				where
					UslugaComplexMedService_id = :UslugaComplexMedService_id
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
				$this->load->model('UslugaComplex_model', 'UslugaComplex_model');
				$ret = $this->UslugaComplex_model->deleteUslugaComplexMedService(array(
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
	 * Загрузка комбо тестов
	 */
	function loadList($filter) {
		$where = array();
		$p = array();
		$join = '';
		$fields = '';
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
			$where[] = 'v_AnalyzerTest.AnalyzerTest_id IN (SELECT AnalyzerTest_id FROM lis.v_AnalyzerTest at (nolock) WHERE at.AnalyzerModel_id in (SELECT AnalyzerModel_id FROM lis.v_AnalyzerWorksheetType (nolock) WHERE AnalyzerWorksheetType_id = :AnalyzerWorksheetType_id))';
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
			$join .= 'LEFT JOIN lis.v_Analyzer A WITH(NOLOCK) on A.Analyzer_id = v_AnalyzerTest.Analyzer_id';
			$p['MedService_id'] = $filter['MedService_id'];
		}
		if (!empty($filter['mode']) && $filter['mode'] == 'ifalab') {
			$fields .= ",MI.MethodsIFA_id
				,MI.MethodsIFA_Name
				,MIAT.MethodsIFAAnalyzerTest_id";
			$join .= " INNER JOIN v_MethodsIFAAnalyzerTest MIAT with(nolock) on MIAT.AnalyzerTest_id = v_AnalyzerTest.AnalyzerTest_id";
			$join .= " INNER JOIN v_MethodsIFA MI with(nolock) on MI.MethodsIFA_id = MIAT.MethodsIFA_id";
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		$q = "
			SELECT
				v_AnalyzerTest.AnalyzerTest_id, v_AnalyzerTest.AnalyzerTest_pid, v_AnalyzerTest.AnalyzerModel_id, uc.UslugaComplex_Code as AnalyzerTest_Code, ISNULL(v_AnalyzerTest.AnalyzerTest_Name, uc.UslugaComplex_Name) as AnalyzerTest_Name, v_AnalyzerTest.AnalyzerTest_SysNick, v_AnalyzerTest.AnalyzerTestType_id, v_AnalyzerTest.Unit_id
				,AnalyzerTest_pid_ref.AnalyzerTest_Name AnalyzerTest_pid_Name, AnalyzerModel_id_ref.AnalyzerModel_Name AnalyzerModel_id_Name, AnalyzerTestType_id_ref.AnalyzerTestType_Name AnalyzerTestType_id_Name, Unit_id_ref.Unit_Name Unit_id_Name
				{$fields}
			FROM
				lis.v_AnalyzerTest WITH (NOLOCK)
				LEFT JOIN lis.v_AnalyzerTest AnalyzerTest_pid_ref WITH (NOLOCK) ON AnalyzerTest_pid_ref.AnalyzerTest_id = v_AnalyzerTest.AnalyzerTest_pid
				LEFT JOIN lis.v_AnalyzerModel AnalyzerModel_id_ref WITH (NOLOCK) ON AnalyzerModel_id_ref.AnalyzerModel_id = v_AnalyzerTest.AnalyzerModel_id
				LEFT JOIN lis.v_AnalyzerTestType AnalyzerTestType_id_ref WITH (NOLOCK) ON AnalyzerTestType_id_ref.AnalyzerTestType_id = v_AnalyzerTest.AnalyzerTestType_id
				LEFT JOIN lis.v_Unit Unit_id_ref WITH (NOLOCK) ON Unit_id_ref.Unit_id = v_AnalyzerTest.Unit_id
				left join v_UslugaComplex (nolock) uc on uc.UslugaComplex_id = v_AnalyzerTest.UslugaComplex_id
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
			select top 1
				convert(varchar(10), u.Unit_endDate, 104) as endDT
			from
				lis.v_AnalyzerTest at with (nolock) 
				left join lis.v_QuantitativeTestUnit qtu with (nolock) on qtu.AnalyzerTest_id = at.AnalyzerTest_id
				left join lis.v_Unit u with (nolock) on u.Unit_id = qtu.Unit_id
			where
				at.AnalyzerTest_id = :AnalyzerTest_id
				and u.Unit_endDate < :AnalyzerTest_begDT
			order by 
				u.Unit_endDate
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
		SELECT at.Analyzer_id, at.AnalyzerModel_id, uc.UslugaComplex_Code
			FROM lis.v_AnalyzerTest at
			LEFT JOIN v_UslugaComplex (nolock) uc on uc.UslugaComplex_id = at.UslugaComplex_id
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
	function loadLabTestList($data)
	{
		if (!empty($data['UslugaComplex_id'])) {
			$cwhere = "";
			if (!empty($data['query'])) {
				$filter = $data['query'];

				$cwhere = " and (cast(lt.LabTest_Code as varchar(50)) like '{$filter}%'
				or cast(lt.LabTest_Code as varchar(50)) like '{$filter}%' or (lt.LabTest_ShortName like '%{$filter}%')) ";
			}
			$q = "
				Select
					lt.LabTest_id,
					lt.LabTest_Code,
					lt.LabTest_ShortName as LabTest_Name
				from UslugaComplex uc (nolock) 
				left join UslugaComplex ucg (nolock) on uc.UslugaComplex_2011id = ucg.UslugaComplex_id
				left join nsi.NomenMedUsluga nmu (nolock) on nmu.NomenMedUsluga_Code = IsNull(uc.UslugaComplex_oid, ucg.UslugaComplex_oid)
				left join nsi.LabTestLink ltl (nolock) on ltl.NomenMedUsluga_id = nmu.NomenMedUsluga_id
				inner join nsi.LabTest lt (nolock) on lt.LabTest_id = ltl.LabTest_id
				where uc.UslugaComplex_id = :UslugaComplex_id
			" . $cwhere;
			$r = $this->db->query($q, $data);
			if (is_object($r)) {
				return $r->result('array');
			}
		}
		return false;
	}
	/**
	 * Получение списка тестов НСИ, не связанных с выбранной услугой
	 *  А.И.Г. 25.02.2020 Поддержка #194077
	 */
	function loadLabTestNoList($data)
	{
		$cwhere = "";
		$cSql = "Select top 40 ";
		if (!empty($data['query'])) {
			$filter = $data['query'];

			$cwhere = " and (cast(lt.LabTest_Code as varchar(50)) like '{$filter}%'
				or cast(lt.LabTest_Code as varchar(50)) like '{$filter}%' or (lt.LabTest_ShortName like '%{$filter}%')) ";
			$cSql = "Select ";
		}
		$q = $cSql . "lt.LabTest_id,
					lt.LabTest_Code,
					lt.LabTest_ShortName as LabTest_Name
				from nsi.LabTest lt (nolock)
				where (1=1)
			" . $cwhere;
		if (!empty($data['LabTest_id'])) {
			$q = " select lt.LabTest_id,
					lt.LabTest_Code,
					lt.LabTest_ShortName as LabTest_Name
				from nsi.LabTest lt (nolock)
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
				from lis.v_Analyzer (nolock) a
				inner join dbo.v_MedService (nolock) ms on ms.MedService_id = a.MedService_id
				where a.Analyzer_id = :Analyzer_id
			)";
			$join = "inner join MST on MST.MedServiceType_id = att.MedServiceType_id";
		}
		$query = "{$with}
			select
				att.AnalyzerTestType_id as \"AnalyzerTestType_id\",
				att.AnalyzerTestType_Code as \"AnalyzerTestType_Code\",
				att.AnalyzerTestType_Name as \"AnalyzerTestType_Name\"
			from lis.v_AnalyzerTestType (nolock) att
			{$join}
		";
		try { return $this->queryResult($query, $params);
		} catch (Exception $e) { throw $e; }

	}
}
