<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package	  Lis
* @access	   public
* @copyright	Copyright (c) 2011 Swan Ltd.
*/

class LisSpr_model extends swModel
{
	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Скрипт перехода на новые "услуги анализаторов" на лабораторной службе
	 */
	function convertUslugaComplexMedServiceToAnalyzerTest()
	{
		// 1. ищем все анализаторы связанные с ЛИС
		$query = "
			select
				a.MedService_id as \"MedService_id\",
				a.Analyzer_id as \"Analyzer_id\",
				l.lis_id as \"equipment_id\"
			from
				lis.v_Link l with(nolock)
				inner join lis.v_Analyzer a with(nolock) on a.Analyzer_id = l.object_id
			where
				l.link_object = 'Analyzer'
		";
		
		$result_analyzers = $this->db->query($query);
		if (is_object($result_analyzers)) {
			$analyzers = $result_analyzers->result('array');
			foreach($analyzers as $analyzer) {
				// 2. достаём по анализатору все его услуги
				$query = "
					select distinct
						ta.code as target_code,
						t.code as test_code
					from
						lis._test t with(nolock)
						inner join lis._test_targets tt with(nolock) on tt.test_id = t.id
						inner join lis._target ta with(nolock) on ta.id = tt.target_id
						inner join lis._test_equipments e with(nolock) on t.id = e.test_id
					where
						e.equipment_id = :equipment_id
					order by
						ta.code,
						t.code
				";
				
				$result_tests = $this->db->query($query, array('equipment_id' => $analyzer['equipment_id']));
				$targets = array();
				if (is_object($result_tests)) {
					$tests = $result_tests->result('array');
					foreach($tests as $test) {
						$targets[$test['target_code']][] = $test['test_code'];
					}
				}
				
				foreach($targets as $target => $tests) {
					// 3. ищем по каждой из услуг такую услугу на службе анализатора, но не связанную с AnalyzerTest.
					$query = "
						select
							ucms.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
							convert(varchar(19), ucms.UslugaComplexMedService_begDT, 120) as \"UslugaComplexMedService_begDT\",
							convert(varchar(19), ucms.UslugaComplexMedService_endDT, 120) as \"UslugaComplexMedService_endDT\",
							ucms.UslugaComplex_id as \"UslugaComplex_id\",
							at.AnalyzerTest_id as \"AnalyzerTest_id\"
						from
							v_UslugaComplexMedService ucms with(nolock)
							inner join v_UslugaComplex uc with(nolock) on uc.UslugaComplex_id = ucms.UslugaComplex_id
							inner join v_UslugaComplex ucgost with(nolock) on ucgost.UslugaComplex_id = uc.UslugaComplex_2011id
							inner join v_UslugaCategory ucat with(nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
							outer apply(
								select top 1 AnalyzerTest_id 
								from lis.v_AnalyzerTest with(nolock) 
								where UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
							) at
						where
							ucgost.UslugaComplex_Code = :UslugaComplex_Code
							and ucms.UslugaComplexMedService_pid IS NULL
							and ucms.MedService_id = :MedService_id
							and ucat.UslugaCategory_SysNick = 'lpu'
					";
					$result_ucmss = $this->db->query($query, array('UslugaComplex_Code' => $target, 'MedService_id' => $analyzer['MedService_id']));
					if (is_object($result_ucmss)) {
						$ucmss = $result_ucmss->result('array');
						foreach($ucmss as $ucms) {
							// 4. достаём её состав и сравниваем с составом в лис
							$query = "
								select
									ucms.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
									convert(varchar(19), ucms.UslugaComplexMedService_begDT, 120) as \"UslugaComplexMedService_begDT\",
									convert(varchar(19), ucms.UslugaComplexMedService_endDT, 120) as \"UslugaComplexMedService_endDT\",
									ucgost.UslugaComplex_Code as \"UslugaComplex_Code\",
									ucms.UslugaComplex_id as \"UslugaComplex_id\"
								from
									v_UslugaComplexMedService ucms with(nolock)
									inner join v_UslugaComplex uc with(nolock) on uc.UslugaComplex_id = ucms.UslugaComplex_id
									inner join v_UslugaComplex ucgost with(nolock) on ucgost.UslugaComplex_id = uc.UslugaComplex_2011id
									inner join v_UslugaCategory ucat with(nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
								where
									ucms.UslugaComplexMedService_pid = :UslugaComplexMedService_pid
									and ucms.MedService_id = :MedService_id
									and ucat.UslugaCategory_SysNick = 'lpu'
									and not exists(
										select * from lis.v_AnalyzerTest with(nolock)
										where UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
									)
							";
							$result_ucmss_childs = $this->db->query($query, array('UslugaComplexMedService_pid' => $ucms['UslugaComplexMedService_id'], 'MedService_id' => $analyzer['MedService_id']));
							$ucmss_childs = $result_ucmss_childs->result('array');
							$compare_result = true;
							if (count($ucmss_childs) == 0 && count($tests) > 1) {
								$compare_result = false;
							} elseif (count($ucmss_childs) > 0 && count($tests) != count($ucmss_childs)) {
								$compare_result = false;
							}
							foreach($ucmss_childs as $ucmss_child) {
								if (!in_array($ucmss_child['UslugaComplex_Code'], $tests)) {
									$compare_result = false;
								}
							}							
							if ($compare_result) {
								// 5. если состав совпадает -> связываем услугу с анализатором
								if (empty($ucms['AnalyzerTest_id'])) {
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
										'Analyzer_id' => $analyzer['Analyzer_id'],
										'pmUser_id' => 1
									));
									if ( !is_object($result_saveat) ) {
										die('Ошибка запроса');
									}
									$resp_saveat = $result_saveat->result('array');
									if (empty($resp_saveat[0]['AnalyzerTest_id'])) {
										die('Ошибка сохранения AnalyzerTest');
									}
									
									$ucms['AnalyzerTest_id'] = $resp_saveat[0]['AnalyzerTest_id'];
								}
								
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
										'Analyzer_id' => $analyzer['Analyzer_id'],
										'AnalyzerTest_pid' => $ucms['AnalyzerTest_id'],
										'pmUser_id' => 1
									));
									if ( !is_object($result_saveat_child) ) {
										die('Ошибка запроса');
									}
									$resp_saveat_child = $result_saveat_child->result('array');
									if (empty($resp_saveat_child[0]['AnalyzerTest_id'])) {
										die('Ошибка сохранения AnalyzerTest');
									}
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Получение списка анализаторов
	 */
	function loadEquipmentsGrid($data)
	{
		$query = "
			Select distinct
				ee.id as equipment_id,
				ee.name as equipment_name,
				ee.code as equipment_code,
				dd.name as department_name
			from lis._test t (nolock)
				inner join lis._test_equipments e with(nolock) on t.id = e.test_id
				inner join lis._equipment ee with(nolock) on ee.id = e.equipment_id
				left join lis._equipment_departments ed with(nolock) on ed.equipment_id = e.equipment_id
				left join lis._department dd with(nolock) on dd.id = ed.department_id
				inner join lis._workPlace_equipments we with(nolock) on we.equipment_id = e.equipment_id
				inner join lis._workPLace wp with(nolock) on wp.id = we.workPLace_id
				inner join lis._organization o with(nolock) on o.id = wp.organization_id
				inner join lis.v_Organization lisorg with(nolock) on lisorg.Organization_id = o.id
				inner join v_Lpu lpu with(nolock) on lpu.Org_id = lisorg.Org_id
				inner join v_MedService ms with(nolock) on ms.Lpu_id = lpu.Lpu_id
			where
				(1 = 1)
				and ms.MedService_id = :MedService_id
				and not exists(
					select * from lis.v_Link with(nolock) 
					where lis_id = ee.id and link_object = 'Analyzer'
				)
		";
		
		$res = $this->db->query($query, $data);
		
		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Получение списка тестов
	 */
	function loadTestsGrid($data)
	{
		$query = "
			with myvar1 as (
				select top 1 
                	uc.UslugaComplex_Code
                from v_UslugaComplex uc with(nolock)
                	inner join v_UslugaComplexMedService ucms with(nolock) on ucms.UslugaComplex_id = uc.UslugaComplex_id 
                where
                	ucms.UslugaComplexMedService_id = :UslugaComplexMedService_pid
			),
			myvar2 as (
				select top 1
                	MedService_id
                from v_UslugaComplexMedService with(nolock)
                where
                	UslugaComplexMedService_id = :UslugaComplexMedService_pid
			)
			
			Select distinct
				t.id as test_id,
				t.name as test_name,
				t.code as test_code
			from lis._test t with(nolock)
				inner join lis._test_targets tt with(nolock) on tt.test_id = t.id
				inner join lis._target ta with(nolock) on ta.id = tt.target_id
				inner join lis._test_equipments e with(nolock) on t.id = e.test_id
			where
				ta.code = (select UslugaComplex_Code from myvar1)
				and t.code <> ta.code
				and e.equipment_id IN (
					select
						l.lis_id
					from lis.v_Link l with(nolock)
						inner join lis.v_Analyzer a with(nolock) on a.Analyzer_id = l.object_id
					where
						l.link_object = 'Analyzer'
						and a.MedService_id = (select MedService_id from myvar1)
				) 
		";
		
		$res = $this->db->query($query, $data);
		
		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение списка тестов анализатора ЛИС
	 */
	function loadEquipmentTestsGrid($data)
	{
		$query = "
			select
				ta.id + '_' + t.id as target_test_id,
				ta.id as target_id,
				ta.code as target_code,
				ta.name as target_name,
				t.id as test_id,
				t.code as test_code,
				t.name as test_name,
				tm.code as test_sysnick
			from
				lis._test t with(nolock)
				inner join lis._test_targets tt with(nolock) on tt.test_id = t.id
				inner join lis._target ta with(nolock) on ta.id = tt.target_id
				inner join lis._test_equipments e with(nolock) on t.id = e.test_id
				-- // это все по задаче #26031
				inner join lis._workPlace_equipments we with(nolock) on we.equipment_id = e.equipment_id
				inner join lis._workPLace wp with(nolock) on wp.id = we.workPLace_id
				inner join lis._organization o with(nolock) on o.id = wp.organization_id
				-- только связанные с регформой исследования
				-- inner join lis._requestForm rf with(nolock) on IsNumeric(rf.code)=1 and cast(rf.code as bigint) = o.code // коды теперь соответствуют полностью
				inner join lis._requestForm rf with(nolock) on rf.code = o.code
				inner join lis._requestForm_targets rft with(nolock) on ta.id = rft.target_id and rf.id = rft.requestForm_id
				-- // end #26031
				left join lis._testMappings tm with(nolock) on tm.test_id = t.id and tm.equipment_id = e.equipment_id
			where e.equipment_id = :equipment_id
		";
		
		$res = $this->db->query($query, $data);
		
		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Получение списка единиц измерения теста
	 */
	function loadTestUnitList($data)
	{
		if (!empty($data['UslugaTest_id'])) {
			// получаем необходимые данные из услуги
			$query = "
				select top 1
					els.MedService_id as \"MedService_id\",
					eupp.UslugaComplex_id as \"UslugaComplexTarget_id\",
					ut.UslugaComplex_id as \"UslugaComplexTest_id\",
					els.Analyzer_id as \"Analyzer_id\",
					ut.Unit_id as \"Unit_id\",
					atrv_an.Analyzer_id as \"RefValuesAnalyzer_id\"
				from
					v_UslugaTest ut with(nolock)
					left join v_EvnUslugaPar eupp with(nolock) on eupp.EvnUslugaPar_id = ut.UslugaTest_pid
					left join v_EvnLabSample els with(nolock) on els.EvnLabSample_id = ut.EvnLabSample_id
					left join v_EvnLabRequest elr with(nolock) on elr.EvnLabRequest_id = els.EvnLabRequest_id
					outer apply(
						select top 1
							at.Analyzer_id
						from
							lis.v_AnalyzerTest at with(nolock)
							inner join v_UslugaComplexMedService ucms with(nolock) on ucms.UslugaComplexMedService_id = at.UslugaComplexMedService_id
							inner join lis.v_AnalyzerTestRefValues atrv with(nolock) on atrv.AnalyzerTest_id = at.AnalyzerTest_id
						where
							ucms.UslugaComplex_id = ut.UslugaComplex_id
							and atrv.RefValues_id = ut.RefValues_id
					) atrv_an
				where
					ut.UslugaTest_id = :UslugaTest_id
			";
			
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$data['MedService_id'] = $resp[0]['MedService_id'];
					$data['UslugaComplexTarget_id'] = $resp[0]['UslugaComplexTarget_id'];
					$data['UslugaComplexTest_id'] = $resp[0]['UslugaComplexTest_id'];
					$data['Analyzer_id'] = $resp[0]['Analyzer_id'];
					if (!empty($resp[0]['RefValuesAnalyzer_id'])) {
						// если референсные значения выбраны с другого анализатора, то и единицы измерения с него должны быть
						$data['Analyzer_id'] = $resp[0]['RefValuesAnalyzer_id'];
					}
					$data['UnitOld_id'] = $resp[0]['Unit_id'];
				}
			}
		}
		
		$filter = "";
		if (!empty($data['Analyzer_id'])) {
			$filter .= " and a.Analyzer_id = :Analyzer_id";
		}
		
		// фильтрация по исследованию, которое может выполняться на анализаторе
		$filter .= ' and ucms.UslugaComplex_id = :UslugaComplexTest_id';
		
		if ($data['UslugaComplexTarget_id'] != $data['UslugaComplexTest_id']) {
			$filter .= " and exists(
			select *
			from lis.v_AnalyzerTest at_parent  with(nolock)
				inner join v_UslugaComplexMedService ucms_at_parent with(nolock) on ucms_at_parent.UslugaComplexMedService_id = at_parent.UslugaComplexMedService_id 
			where 
				at_parent.AnalyzerTest_id = at.AnalyzerTest_pid 
				and ucms_at_parent.UslugaComplex_id = :UslugaComplexTarget_id
				)
				";
		}
		
		if (!empty($data['RefValues_id'])) {
			// только единицы измерения теста, для которого выбрано референсное значение
			$filter .= " and at.AnalyzerTest_id IN (
				select AnalyzerTest_id from lis.v_AnalyzerTestRefValues where RefValues_id = :RefValues_id
			)";
		}

		if (!empty($data['QuantitativeTestUnit_IsBase'])) {
			$filter .= " and qtu.QuantitativeTestUnit_IsBase = :QuantitativeTestUnit_IsBase";
		}
		
		$query = "
			select
				u.Unit_id as \"Unit_id\",
				u.Unit_Code as \"Unit_Code\",
				u.Unit_Name as \"Unit_Name\",
				case
					when COEF.QuantitativeTestUnit_CoeffEnum IS NOT NULL
					then qtu.QuantitativeTestUnit_CoeffEnum / COEF.QuantitativeTestUnit_CoeffEnum else 1
				end as \"Unit_Coeff\"
			from
				lis.v_Unit u with(nolock)
				inner join lis.v_QuantitativeTestUnit qtu with(nolock) on u.Unit_id = qtu.Unit_id
				inner join lis.v_AnalyzerTest at with(nolock) on qtu.AnalyzerTest_id = at.AnalyzerTest_id
				inner join v_UslugaComplexMedService ucms with(nolock) on ucms.UslugaComplexMedService_id = at.UslugaComplexMedService_id
				inner join lis.v_Analyzer a with(nolock) on a.Analyzer_id = at.Analyzer_id
				outer apply(
					select top 1
						QuantitativeTestUnit_CoeffEnum
					from
						lis.v_QuantitativeTestUnit qtu2 with(nolock)
					where
						qtu2.Unit_id = :UnitOld_id
						and qtu2.AnalyzerTest_id = qtu.AnalyzerTest_id
				) COEF
			where
				a.MedService_id = :MedService_id
				{$filter}
		";
		
		$res = $this->db->query($query, $data);
		
		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Получение списка единиц измерения теста
	 */
	function loadUnitList($data)
	{
		if (!empty($data['EvnUslugaPar_id'])) {
			// получаем необходимые данные из услуги
			$query = "
				select top 1
					elr.MedService_id as \"MedService_id\",
					eup.UslugaComplex_id as \"UslugaComplex_id\",
					els.Analyzer_id as \"Analyzer_id\"
				from
					v_EvnUslugaPar eup with(nolock)
					left join v_EvnLabSample els with(nolock) on els.EvnLabSample_id = eup.EvnLabSample_id
					left join v_EvnLabRequest elr with(nolock) on elr.EvnLabRequest_id = els.EvnLabRequest_id
				where
					eup.EvnUslugaPar_id = :EvnUslugaPar_id
			";
			
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$data['MedService_id'] = $resp[0]['MedService_id'];
					$data['UslugaComplex_id'] = $resp[0]['UslugaComplex_id'];
					$data['Analyzer_id'] = $resp[0]['Analyzer_id'];
				}
			}
		}
		
		$filter = "";
		if (!empty($data['Analyzer_id'])) {
			$filter .= " and a.Analyzer_id = :Analyzer_id";
		}
		
		if (!empty($data['RefValues_id'])) {
			// только единицы измерения теста, для которого выбрано референсное значение
			$filter .= " and at.AnalyzerTest_id IN (
				select AnalyzerTest_id from lis.v_AnalyzerTestRefValues where RefValues_id = :RefValues_id
			)";
		}
		
		$query = "
			select
				u.Unit_id as \"Unit_id\",
				u.Unit_Code as \"Unit_Code\",
				u.Unit_Name as \"Unit_Name\",
			from
				lis.v_Unit u with(nolock)
				inner join lis.v_QuantitativeTestUnit qtu with(nolock) on u.Unit_id = qtu.Unit_id
				inner join lis.v_AnalyzerTest at with(nolock) on qtu.AnalyzerTest_id = at.AnalyzerTest_id
				inner join lis.v_Analyzer a with(nolock) on a.Analyzer_id = at.Analyzer_id
			where
				at.UslugaComplex_id = :UslugaComplex_id
				and a.MedService_id = :MedService_id
				{$filter}
		";
		
		$res = $this->db->query($query, $data);
		
		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}
}
