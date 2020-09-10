<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package	  Lis
 * @access	   public
 * @copyright	Copyright (c) 2011 Swan Ltd.
 *
 * @property CI_DB_driver $db
*/

class LisSpr_model extends SwPgModel
{
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Скрипт перехода на новые "услуги анализаторов" на лабораторной службе
	 * @throws Exception
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
				lis.v_Link l
				inner join lis.v_Analyzer a on a.Analyzer_id = l.object_id
			where l.link_object = 'Analyzer'
		";
		/**@var CI_DB_result $result_analyzers */
		$result_analyzers = $this->db->query($query);
		if (is_object($result_analyzers)) {
			$analyzers = $result_analyzers->result_array();
			foreach ($analyzers as $analyzer) {
				// 2. достаём по анализатору все его услуги
				$query = "
					select distinct
						ta.code as target_code,
						t.code as test_code
					from
						lis.v__test t
						inner join lis.v__test_targets tt on tt.test_id = t.id
						inner join lis.v__target ta on ta.id = tt.target_id
						inner join lis.v__test_equipments e on t.id = e.test_id
					where e.equipment_id = :equipment_id
					order by
						ta.code,
						t.code
				";
				/**@var CI_DB_result $result_tests */
				$result_tests = $this->db->query($query, ["equipment_id" => $analyzer["equipment_id"]]);
				$targets = [];
				if (is_object($result_tests)) {
					$tests = $result_tests->result_array();
					foreach ($tests as $test) {
						$targets[$test["target_code"]][] = $test["test_code"];
					}
				}
				foreach ($targets as $target => $tests) {
					// 3. ищем по каждой из услуг такую услугу на службе анализатора, но не связанную с AnalyzerTest.
					$query = "
						select
							ucms.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
							to_char (ucms.UslugaComplexMedService_begDT, 'YYYY-MM-DD HH24:MI:SS') as \"UslugaComplexMedService_begDT\",
							to_char (ucms.UslugaComplexMedService_endDT, 'YYYY-MM-DD HH24:MI:SS') as \"UslugaComplexMedService_endDT\",
							ucms.UslugaComplex_id as \"UslugaComplex_id\",
							at.AnalyzerTest_id as \"AnalyzerTest_id\"
						from
							v_UslugaComplexMedService ucms
							inner join v_UslugaComplex uc on uc.UslugaComplex_id = ucms.UslugaComplex_id
							inner join v_UslugaComplex ucgost on ucgost.UslugaComplex_id = uc.UslugaComplex_2011id
							inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id
							left join lateral(
								select AnalyzerTest_id
								from lis.v_AnalyzerTest
								where UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
								limit 1
							) at on true
						where ucgost.UslugaComplex_Code = :UslugaComplex_Code
						  and ucms.UslugaComplexMedService_pid is null
						  and ucms.MedService_id = :MedService_id
						  and ucat.UslugaCategory_SysNick = 'lpu'
					";
					/**@var CI_DB_result $result_ucmss */
					$result_ucmss = $this->db->query($query, ["UslugaComplex_Code" => $target, "MedService_id" => $analyzer["MedService_id"]]);
					if (is_object($result_ucmss)) {
						$ucmss = $result_ucmss->result_array();
						foreach ($ucmss as $ucms) {
							// 4. достаём её состав и сравниваем с составом в лис
							$query = "
								select
									ucms.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
									to_char (ucms.UslugaComplexMedService_begDT, 'YYYY-MM-DD HH24:MI:SS') as \"UslugaComplexMedService_begDT\",
									to_char (ucms.UslugaComplexMedService_endDT, 'YYYY-MM-DD HH24:MI:SS') as \"UslugaComplexMedService_endDT\",
									ucgost.UslugaComplex_Code as \"UslugaComplex_Code\",
									ucms.UslugaComplex_id as \"UslugaComplex_id\"
								from
									v_UslugaComplexMedService ucms
									inner join v_UslugaComplex uc on uc.UslugaComplex_id = ucms.UslugaComplex_id
									inner join v_UslugaComplex ucgost on ucgost.UslugaComplex_id = uc.UslugaComplex_2011id
									inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id
								where ucms.UslugaComplexMedService_pid = :UslugaComplexMedService_pid
								  and ucms.MedService_id = :MedService_id
								  and ucat.UslugaCategory_SysNick = 'lpu'
								  and not exists(
								      select AnalyzerTest_id
								      from lis.v_AnalyzerTest
								      where UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
								  )
							";
							/**@var CI_DB_result $result_ucmss_childs */
							$result_ucmss_childs = $this->db->query($query, ["UslugaComplexMedService_pid" => $ucms["UslugaComplexMedService_id"], "MedService_id" => $analyzer["MedService_id"]]);
							$ucmss_childs = $result_ucmss_childs->result_array();
							$compare_result = true;
							if (count($ucmss_childs) == 0 && count($tests) > 1) {
								$compare_result = false;
							} elseif (count($ucmss_childs) > 0 && count($tests) != count($ucmss_childs)) {
								$compare_result = false;
							}
							foreach ($ucmss_childs as $ucmss_child) {
								if (!in_array($ucmss_child["UslugaComplex_Code"], $tests)) {
									$compare_result = false;
								}
							}
							if ($compare_result) {
								// 5. если состав совпадает -> связываем услугу с анализатором
								if (empty($ucms["AnalyzerTest_id"])) {
									$query = "
										select 
											analyzertest_id as \"AnalyzerTest_id\",
										    error_code as \"Error_Code\", 
											error_message as \"Error_Msg\"
										from lis.p_AnalyzerTest_ins(
											AnalyzerTest_pid := null,
											AnalyzerModel_id := null,
											Analyzer_id := :Analyzer_id,
											UslugaComplex_id := :UslugaComplex_id,
											AnalyzerTest_IsTest := :AnalyzerTest_IsTest,
											AnalyzerTestType_id := :AnalyzerTestType_id,
											Unit_id := null,
											AnalyzerTest_Code := null,
											AnalyzerTest_Name := (select UslugaComplex_Name from v_UslugaComplex where UslugaComplex_id = :UslugaComplex_id limit 1),
											AnalyzerTest_SysNick := null,
											AnalyzerTest_begDT := :AnalyzerTest_begDT,
											AnalyzerTest_endDT := :AnalyzerTest_endDT,
											UslugaComplexMedService_id := :UslugaComplexMedService_id,
											pmUser_id := :pmUser_id
										)
									";
									$queryParams = [
										"UslugaComplex_id" => $ucms["UslugaComplex_id"],
										"AnalyzerTestType_id" => (count($ucmss_childs) == 0) ? 1 : null,
										"AnalyzerTest_IsTest" => (count($ucmss_childs) == 0) ? 2 : 1,
										"AnalyzerTest_begDT" => $ucms["UslugaComplexMedService_begDT"],
										"AnalyzerTest_endDT" => $ucms["UslugaComplexMedService_endDT"],
										"UslugaComplexMedService_id" => $ucms["UslugaComplexMedService_id"],
										"Analyzer_id" => $analyzer["Analyzer_id"],
										"pmUser_id" => 1
									];
									/**@var CI_DB_result $result_saveat */
									$result_saveat = $this->db->query($query, $queryParams);
									if (!is_object($result_saveat)) {
										throw new Exception("Ошибка запроса");
									}
									$resp_saveat = $result_saveat->result_array();
									if (empty($resp_saveat[0]["AnalyzerTest_id"])) {
										throw new Exception("Ошибка сохранения AnalyzerTest");
									}
									$ucms["AnalyzerTest_id"] = $resp_saveat[0]["AnalyzerTest_id"];
								}
								// связываем состав
								foreach ($ucmss_childs as $ucmss_child) {
									$query = "
										select 
											analyzertest_id as \"AnalyzerTest_id\",
										    error_code as \"Error_Code\", 
											error_message as \"Error_Msg\"
										from lis.p_AnalyzerTest_ins(
											AnalyzerTest_pid := null,
											AnalyzerModel_id := null,
											Analyzer_id := :Analyzer_id,
											UslugaComplex_id := :UslugaComplex_id,
											AnalyzerTest_IsTest := :AnalyzerTest_IsTest,
											AnalyzerTestType_id := :AnalyzerTestType_id,
											Unit_id := null,
											AnalyzerTest_Code := null,
											AnalyzerTest_Name := (select UslugaComplex_Name from v_UslugaComplex where UslugaComplex_id = :UslugaComplex_id limit 1),
											AnalyzerTest_SysNick := null,
											AnalyzerTest_begDT := :AnalyzerTest_begDT,
											AnalyzerTest_endDT := :AnalyzerTest_endDT,
											UslugaComplexMedService_id := :UslugaComplexMedService_id,
											pmUser_id := :pmUser_id
										)
									";
									$queryParams = [
										"UslugaComplex_id" => $ucmss_child["UslugaComplex_id"],
										"AnalyzerTest_IsTest" => 2,
										"AnalyzerTest_begDT" => $ucmss_child["UslugaComplexMedService_begDT"],
										"AnalyzerTest_endDT" => $ucmss_child["UslugaComplexMedService_endDT"],
										"UslugaComplexMedService_id" => $ucmss_child["UslugaComplexMedService_id"],
										"Analyzer_id" => $analyzer["Analyzer_id"],
										"AnalyzerTest_pid" => $ucms["AnalyzerTest_id"],
										"pmUser_id" => 1
									];
									/**@var CI_DB_result $result_saveat_child */
									$result_saveat_child = $this->db->query($query, $queryParams);
									if (!is_object($result_saveat_child)) {
										throw new Exception("Ошибка запроса");
									}
									$resp_saveat_child = $result_saveat_child->result_array();
									if (empty($resp_saveat_child[0]["AnalyzerTest_id"])) {
										throw new Exception("Ошибка сохранения AnalyzerTest");
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
	 * @param $data
	 * @return array|bool
	 */
	function loadEquipmentsGrid($data)
	{
		$query = "
			select distinct
				ee.id as equipment_id,
				ee.name as equipment_name,
				ee.code as equipment_code,
				dd.name as department_name
			from
			    lis.v__test t
				inner join lis.v__test_equipments e on t.id = e.test_id
				inner join lis.v__equipment ee on ee.id = e.equipment_id
				left join lis.v__equipment_departments ed on ed.equipment_id = e.equipment_id
				left join lis.v__department dd on dd.id = ed.department_id
				inner join lis.v__workPlace_equipments we on we.equipment_id = e.equipment_id
				inner join lis.v__workPLace wp on wp.id = we.workPLace_id
				inner join lis.v__organization o on o.id = wp.organization_id
				inner join lis.v_Organization lisorg on lisorg.Organization_id::text = o.id
				inner join v_Lpu lpu on lpu.Org_id = lisorg.Org_id
				inner join v_MedService ms on ms.Lpu_id = lpu.Lpu_id
			where ms.MedService_id = :MedService_id
			  and not exists(
			      select Link_id
			      from lis.v_Link
			      where lis_id::text = ee.id
			        and link_object = 'Analyzer'
			  )
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение списка тестов
	 * @param $data
	 * @return array|bool
	 */
	function loadTestsGrid($data)
	{
		$query = "
			select distinct
				t.id as test_id,
				t.name as test_name,
				t.code as test_code
			from
				lis.v__test t
				inner join lis.v__test_targets tt on tt.test_id = t.id
				inner join lis.v__target ta on ta.id = tt.target_id
				inner join lis.v__test_equipments e on t.id = e.test_id
			where ta.code = (
					select uc.UslugaComplex_Code
	                from
	                    v_UslugaComplex uc 
	                    inner join v_UslugaComplexMedService ucms on ucms.UslugaComplex_id = uc.UslugaComplex_id 
	                where ucms.UslugaComplexMedService_id = :UslugaComplexMedService_pid
	                limit 1
			    )
			  and t.code <> ta.code
			  and e.equipment_id in (
					select l.lis_id::text
					from
					    lis.v_Link l
						inner join lis.v_Analyzer a on a.Analyzer_id = l.object_id
					where l.link_object = 'Analyzer'
					  and a.MedService_id = (
					      select MedService_id
					      from v_UslugaComplexMedService 
					      where UslugaComplexMedService_id = :UslugaComplexMedService_pid
					      limit 1
					  )
			  ) 
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение списка тестов анализатора ЛИС
	 * @param $data
	 * @return array|bool
	 */
	function loadEquipmentTestsGrid($data)
	{
		$query = "
			select
				ta.id || '_' || t.id as target_test_id,
				ta.id as target_id,
				ta.code as target_code,
				ta.name as target_name,
				t.id as test_id,
				t.code as test_code,
				t.name as test_name,
				tm.code as test_sysnick
			from
				lis.v__test t
				inner join lis.v__test_targets tt on tt.test_id = t.id
				inner join lis.v__target ta on ta.id = tt.target_id
				inner join lis.v__test_equipments e on t.id = e.test_id
				-- // это все по задаче #26031
				inner join lis.v__workPlace_equipments we on we.equipment_id = e.equipment_id
				inner join lis.v__workPLace wp on wp.id = we.workPLace_id
				inner join lis.v__organization o on o.id = wp.organization_id
				-- только связанные с регформой исследования
				-- inner join lis._requestForm rf on IsNumeric(rf.code)=1 and cast(rf.code as bigint) = o.code // коды теперь соответствуют полностью
				inner join lis.v__requestForm rf on rf.code = o.code
				inner join lis.v__requestForm_targets rft on ta.id = rft.target_id and rf.id = rft.requestForm_id
				-- // end #26031
				left join lis.v__testMappings tm on tm.test_id = t.id and tm.equipment_id = e.equipment_id
			where e.equipment_id = :equipment_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение списка единиц измерения теста
	 * @param $data
	 * @return array|bool
	 */
	function loadTestUnitList($data)
	{
		if (!empty($data["UslugaTest_id"])) {
			// получаем необходимые данные из услуги
			$query = "
				select
					els.MedService_id as \"MedService_id\",
					eupp.UslugaComplex_id as \"UslugaComplexTarget_id\",
					ut.UslugaComplex_id as \"UslugaComplexTest_id\",
					els.Analyzer_id as \"Analyzer_id\",
					ut.Unit_id as \"Unit_id\",
					atrv_an.Analyzer_id as \"RefValuesAnalyzer_id\"
				from
					v_UslugaTest ut
					left join v_EvnUslugaPar eupp on eupp.EvnUslugaPar_id = ut.UslugaTest_pid
					left join v_EvnLabSample els on els.EvnLabSample_id = ut.EvnLabSample_id
					left join v_EvnLabRequest elr on elr.EvnLabRequest_id = els.EvnLabRequest_id
					left join lateral(
						select at.Analyzer_id
						from
							lis.v_AnalyzerTest at
							inner join v_UslugaComplexMedService ucms on ucms.UslugaComplexMedService_id = at.UslugaComplexMedService_id
							inner join lis.v_AnalyzerTestRefValues atrv on atrv.AnalyzerTest_id = at.AnalyzerTest_id
						where ucms.UslugaComplex_id = ut.UslugaComplex_id
						  and atrv.RefValues_id = ut.RefValues_id
						limit 1	
					) as atrv_an on true
				where ut.UslugaTest_id = :UslugaTest_id
				limit 1	
			";
			/**@var CI_DB_result $result */
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result_array();
				if (count($resp) > 0) {
					$data["MedService_id"] = $resp[0]["MedService_id"];
					$data["UslugaComplexTarget_id"] = $resp[0]["UslugaComplexTarget_id"];
					$data["UslugaComplexTest_id"] = $resp[0]["UslugaComplexTest_id"];
					$data["Analyzer_id"] = $resp[0]["Analyzer_id"];
					if (!empty($resp[0]["RefValuesAnalyzer_id"])) {
						// если референсные значения выбраны с другого анализатора, то и единицы измерения с него должны быть
						$data["Analyzer_id"] = $resp[0]["RefValuesAnalyzer_id"];
					}
					$data["UnitOld_id"] = $resp[0]["Unit_id"];
				}
			}
		}
		$filter = "";
		if (!empty($data["Analyzer_id"])) {
			$filter .= " and a.Analyzer_id = :Analyzer_id";
		}
		// фильтрация по исследованию, которое может выполняться на анализаторе
		$filter .= " and ucms.UslugaComplex_id = :UslugaComplexTest_id";
		if ($data["UslugaComplexTarget_id"] != $data["UslugaComplexTest_id"]) {
			$filter .= "
				and exists(
					select AnalyzerTest_id 
					from
						lis.v_AnalyzerTest at_parent 
						inner join v_UslugaComplexMedService ucms_at_parent on ucms_at_parent.UslugaComplexMedService_id = at_parent.UslugaComplexMedService_id 
					where at_parent.AnalyzerTest_id = at.AnalyzerTest_pid 
					  and ucms_at_parent.UslugaComplex_id = :UslugaComplexTarget_id
					limit 1	
				)
			";
		}
		if (!empty($data["RefValues_id"])) {
			// только единицы измерения теста, для которого выбрано референсное значение
			$filter .= "
				and at.AnalyzerTest_id in (
					select AnalyzerTest_id
					from lis.v_AnalyzerTestRefValues
					where RefValues_id = :RefValues_id
				)
			";
		}
		if (!empty($data["QuantitativeTestUnit_IsBase"])) {
			$filter .= " and qtu.QuantitativeTestUnit_IsBase = :QuantitativeTestUnit_IsBase";
		}
		$query = "
			select
				u.Unit_id as \"Unit_id\",
				u.Unit_Code as \"Unit_Code\",
				u.Unit_Name as \"Unit_Name\",
				case when COEF.QuantitativeTestUnit_CoeffEnum is not null
					then qtu.QuantitativeTestUnit_CoeffEnum / COEF.QuantitativeTestUnit_CoeffEnum
				    else 1
				end as \"Unit_Coeff\"
			from
				lis.v_Unit u
				inner join lis.v_QuantitativeTestUnit qtu on u.Unit_id = qtu.Unit_id
				inner join lis.v_AnalyzerTest at on qtu.AnalyzerTest_id = at.AnalyzerTest_id
				inner join v_UslugaComplexMedService ucms on ucms.UslugaComplexMedService_id = at.UslugaComplexMedService_id
				inner join lis.v_Analyzer a on a.Analyzer_id = at.Analyzer_id
				left join lateral(
					select QuantitativeTestUnit_CoeffEnum
					from lis.v_QuantitativeTestUnit qtu2
					where qtu2.Unit_id = :UnitOld_id
					  and qtu2.AnalyzerTest_id = qtu.AnalyzerTest_id
					limit 1	
				) as COEF on true
			where a.MedService_id = :MedService_id
			  {$filter}
		";
		$result = $this->db->query($query, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение списка единиц измерения теста
	 * @param $data
	 * @return array|bool
	 */
	function loadUnitList($data)
	{
		/**@var CI_DB_result $result */
		if (!empty($data["EvnUslugaPar_id"])) {
			// получаем необходимые данные из услуги
			$query = "
				select
					elr.MedService_id as \"MedService_id\",
					eup.UslugaComplex_id as \"UslugaComplex_id\",
					els.Analyzer_id as \"Analyzer_id\"
				from
					v_EvnUslugaPar eup
					left join v_EvnLabSample els on els.EvnLabSample_id = eup.EvnLabSample_id
					left join v_EvnLabRequest elr on elr.EvnLabRequest_id = els.EvnLabRequest_id
				where eup.EvnUslugaPar_id = :EvnUslugaPar_id
				limit 1	
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result_array();
				if (count($resp) > 0) {
					$data["MedService_id"] = $resp[0]["MedService_id"];
					$data["UslugaComplex_id"] = $resp[0]["UslugaComplex_id"];
					$data["Analyzer_id"] = $resp[0]["Analyzer_id"];
				}
			}
		}
		$filter = "";
		if (!empty($data["Analyzer_id"])) {
			$filter .= " and a.Analyzer_id = :Analyzer_id";
		}
		if (!empty($data["RefValues_id"])) {
			// только единицы измерения теста, для которого выбрано референсное значение
			$filter .= "
				and at.AnalyzerTest_id in (
					select AnalyzerTest_id
					from lis.v_AnalyzerTestRefValues
					where RefValues_id = :RefValues_id
				)
			";
		}
		$query = "
			select
				u.Unit_id as \"Unit_id\",
				u.Unit_Code as \"Unit_Code\",
				u.Unit_Name as \"Unit_Name\",
			from
				lis.v_Unit u
				inner join lis.v_QuantitativeTestUnit qtu on u.Unit_id = qtu.Unit_id
				inner join lis.v_AnalyzerTest at on qtu.AnalyzerTest_id = at.AnalyzerTest_id
				inner join lis.v_Analyzer a on a.Analyzer_id = at.Analyzer_id
			where at.UslugaComplex_id = :UslugaComplex_id
			  and a.MedService_id = :MedService_id
			  {$filter}
		";
		$result = $this->db->query($query, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}
}