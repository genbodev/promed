<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Наборы референсных значений тестов
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version
 */
class RefValuesSet_model extends swModel {
	/**
	 * Загрузка единицы измерения
	 */
	function loadRefValuesSet($data) {
		$data['AnalyzerTest_id'] = $this->getFirstResultFromQuery("SELECT AnalyzerTest_id FROM v_RefValuesSet (nolock) WHERE RefValuesSet_id = :RefValuesSet_id", $data);
		if (empty($data['AnalyzerTest_id'])) {
			return false;
		}
		
		// 1. проверяем AnalyzerTest_IsTest
		if (empty($data['AnalyzerTest_IsTest']) || $data['AnalyzerTest_IsTest'] != 2) {
			$AnalyzerTest_IsTest = $this->getFirstResultFromQuery("SELECT AnalyzerTest_IsTest FROM lis.v_AnalyzerTest (nolock) WHERE AnalyzerTest_id = :AnalyzerTest_id", $data);
			if ($AnalyzerTest_IsTest == 1) {
				// обновить поле в AnalyzerTest
				$this->saveLinkToRefValuesSet($data);
				
				$query = "
					select
						rvs.RefValuesSet_id
					from
						v_RefValuesSet rvs (nolock)
					where
						rvs.RefValuesSet_pid = :RefValuesSet_id
				";
				$result = $this->db->query($query, $data);
				if ( is_object($result) ) {
					$resp = $result->result('array');
					foreach ($resp as $resp_one) {
						$data['RefValuesSet_id'] = $resp_one['RefValuesSet_id'];
						$data['AnalyzerTest_IsTest'] = 2;
						$this->loadRefValuesSet($data);
					}
				}
				return array('Error_Msg' => '');
			}
		}
		
		$this->load->model('AnalyzerTestRefValues_model', 'AnalyzerTestRefValues_model');
		// удаляем все текущие референсные значения из AnalyzerTestRefValues
		$query = "
			select
				atrv.AnalyzerTestRefValues_id
			from
				lis.v_AnalyzerTestRefValues atrv (nolock)
			where
				atrv.AnalyzerTest_id = :AnalyzerTest_id
		";
		$result_rv = $this->db->query($query, $data);
		if ( is_object($result_rv) ) {
			$resp_rv = $result_rv->result('array');
			foreach ($resp_rv as $resp_rvone) {
				$this->AnalyzerTestRefValues_model->delete(array(
					'AnalyzerTestRefValues_id' => $resp_rvone['AnalyzerTestRefValues_id']
				));
			}
		}
					
		// загружаем все текущие референсные значения из RefValuesSetRefValues
		$query = "
			select
				*
			from
				v_RefValuesSetRefValues (nolock)
			where
				RefValuesSet_id = :RefValuesSet_id
		";
		$result_rv = $this->db->query($query, $data);
		if ( is_object($result_rv) ) {
			$resp_rv = $result_rv->result('array');
			foreach ($resp_rv as $resp_rvone) {
				$resp_rvone['AnalyzerTest_id'] = $data['AnalyzerTest_id'];
				$resp_rvone['pmUser_id'] = $data['pmUser_id'];
				$resp_rvone['RefValues_id'] = null;
				
				$query = "
					declare
						@RefValues_id bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @RefValues_id = NULL;
					exec p_RefValues_ins
						@RefValues_id = @RefValues_id output,
						@Lpu_id = :Lpu_id,
						@RefValues_Code = :RefValues_Code,
						@RefValues_OPMUCode = :RefValues_OPMUCode,
						@RefValues_LocalCode = :RefValues_LocalCode,
						@RefValues_Name = :RefValues_Name,
						@RefValues_Nick = :RefValues_Nick,
						@RefValuesType_id = :RefValuesType_id,
						@RefValuesUnit_id = :RefValuesUnit_id,
						@RefValues_LowerLimit = :RefValues_LowerLimit,
						@RefValues_UpperLimit = :RefValues_UpperLimit,
						@RefValuesGroup_id = :RefValuesGroup_id,
						@RefValues_LowerAge = :RefValues_LowerAge,
						@RefValues_UpperAge = :RefValues_UpperAge,
						@AgeUnit_id = :AgeUnit_id,
						@RefCategory_id = :RefCategory_id,
						@HormonalPhaseType_id = :HormonalPhaseType_id,
						@TimeOfDay_id = :TimeOfDay_id,
						@RefMaterial_id = :RefMaterial_id,
						@RefValues_Cost = :RefValues_Cost,
						@RefValues_UET = :RefValues_UET,
						@RefValues_Method = :RefValues_Method,
						@RefValues_Description = :RefValues_Description,
						@RefValues_BotCritValue = :RefValues_BotCritValue,
						@RefValues_TopCritValue = :RefValues_TopCritValue,
						@Sex_id = :Sex_id,
						@RefValues_PregnancyFrom = :RefValues_PregnancyFrom,
						@RefValues_PregnancyTo = :RefValues_PregnancyTo,
						@RefValues_TimeOfDayFrom = :RefValues_TimeOfDayFrom,
						@RefValues_TimeOfDayTo = :RefValues_TimeOfDayTo,
						@PregnancyUnitType_id = :PregnancyUnitType_id,
						@Unit_id = :Unit_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @RefValues_id as RefValues_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";						
				
				$result = $this->db->query($query, $resp_rvone);
				
				if ( is_object($result) ) {
					$resp = $result->result('array');
					if (!empty($resp[0]['RefValues_id'])) {
						$resp_rvone['RefValues_id'] = $resp[0]['RefValues_id'];
					}
				}
				
				if (!empty($resp_rvone['RefValues_id'])) {
					$query = "
						declare
							@AnalyzerTestRefValues_id bigint,
							@ErrCode int,
							@ErrMessage varchar(4000);
						set @AnalyzerTestRefValues_id = NULL;
						exec lis.p_AnalyzerTestRefValues_ins
							@AnalyzerTestRefValues_id = @AnalyzerTestRefValues_id output,
							@AnalyzerTest_id = :AnalyzerTest_id,
							@RefValues_id = :RefValues_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @AnalyzerTestRefValues_id as AnalyzerTestRefValues_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$result_save = $this->db->query($query, $resp_rvone);
					if (is_object($result_save)) {
						$resp_save = $result_save->result('array');
						if (!empty($resp_save[0]['AnalyzerTestRefValues_id'])) {
							// для каждого копируем ещё и QualitativeTestAnswerReferValue.
							$query = "
								select
									qtarv.*
								from
									lis.v_QualitativeTestAnswerReferValue qtarv (nolock)
								where
									qtarv.RefValuesSetRefValues_id = :RefValuesSetRefValues_id
							";
							$result_qtarv = $this->db->query($query, $resp_rvone);
							if ( is_object($result_qtarv) ) {
								$resp_qtarv = $result_qtarv->result('array');
								foreach ($resp_qtarv as $resp_qtarvone) {
									$resp_qtarvone['AnalyzerTestRefValues_id'] = $resp_save[0]['AnalyzerTestRefValues_id'];
									$resp_qtarvone['pmUser_id'] = $data['pmUser_id'];
									
									$query = "
										declare
											@QualitativeTestAnswerReferValue_id bigint,
											@ErrCode int,
											@ErrMessage varchar(4000);
										set @QualitativeTestAnswerReferValue_id = NULL;
										exec lis.p_QualitativeTestAnswerReferValue_ins
											@QualitativeTestAnswerReferValue_id = @QualitativeTestAnswerReferValue_id output,
											@AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id,
											@QualitativeTestAnswerAnalyzerTest_id = :QualitativeTestAnswerAnalyzerTest_id,
											@RefValuesSetRefValues_id = null,
											@pmUser_id = :pmUser_id,
											@Error_Code = @ErrCode output,
											@Error_Message = @ErrMessage output;
										select @QualitativeTestAnswerReferValue_id as QualitativeTestAnswerReferValue_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
									";						
									
									$this->db->query($query, $resp_qtarvone);
								}					
							}
							
							// для каждого копируем ещё и Limit.
							$query = "
								select
									l.*
								from
									v_Limit l (nolock)
								where
									l.RefValuesSetRefValues_id = :RefValuesSetRefValues_id
							";
							$result_limit = $this->db->query($query, $resp_rvone);
							if ( is_object($result_limit) ) {
								$resp_limit = $result_limit->result('array');
								foreach ($resp_limit as $resp_limitone) {
									$resp_limitone['RefValues_id'] = $resp_rvone['RefValues_id'];
									$resp_limitone['pmUser_id'] = $data['pmUser_id'];
									
									$query = "
										declare
											@Limit_id bigint,
											@ErrCode int,
											@ErrMessage varchar(4000);
										set @Limit_id = NULL;
										exec p_Limit_ins
											@Limit_id = @Limit_id output,
											@RefValues_id = :RefValues_id,
											@LimitType_id = :LimitType_id,
											@Limit_Values = :Limit_Values,
											@Limit_ValuesFrom = :Limit_ValuesFrom,
											@Limit_ValuesTo = :Limit_ValuesTo,
											@Limit_IsActiv = :Limit_IsActiv,
											@RefValuesSetRefValues_id = null,
											@pmUser_id = :pmUser_id,
											@Error_Code = @ErrCode output,
											@Error_Message = @ErrMessage output;
										select @Limit_id as Limit_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
									";						
									
									$this->db->query($query, $resp_limitone);
								}					
							}
						}
					}
				}
			}
		}
		return array('Error_Msg' => '');
	}

	/**
	 * Загрузка списка
	 */
	function loadList($filter) {
		$query = "
			SELECT
				rvs.RefValuesSet_id,
				rvs.RefValuesSet_Name,
				convert(varchar(10),rvs.RefValuesSet_insDT,104) as RefValuesSet_insDT,
				convert(varchar(10),rvs.RefValuesSet_updDT,104) as RefValuesSet_updDT,
				pu.pmUser_Name
			FROM
				v_RefValuesSet rvs (nolock)
				left join v_pmUserCache pu (nolock) on pu.pmUser_id = rvs.pmUser_updID
			WHERE
				rvs.AnalyzerTest_id = :AnalyzerTest_id and rvs.RefValuesSet_pid IS NULL
		";
		$result = $this->db->query($query, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Получение нормальных значений для качественного текста
	 */
	function getRefValuesForQualitativeTest($RefValuesSetRefValues_id)
	{
		$norms = '';
		$query = "
			select
				qtaat.QualitativeTestAnswerAnalyzerTest_Answer
			from
				lis.v_QualitativeTestAnswerReferValue qtarv (nolock)
				inner join lis.v_QualitativeTestAnswerAnalyzerTest qtaat (nolock) on qtaat.QualitativeTestAnswerAnalyzerTest_id = qtarv.QualitativeTestAnswerAnalyzerTest_id
			where
				qtarv.RefValuesSetRefValues_id = :RefValuesSetRefValues_id
		";
		
		$result = $this->db->query($query, array(
			'RefValuesSetRefValues_id' => $RefValuesSetRefValues_id
		));
		if ( is_object($result) ) {
			$resp = $result->result('array');
			foreach ($resp as $respone) {
				if (!empty($norms)) {
					$norms .= ', ';
				}
				
				$norms .= $respone['QualitativeTestAnswerAnalyzerTest_Answer'];
			}
		}
		
		return $norms;
	}
	
	/**
	 * Загрузка списка
	 */
	function loadRefValuesSetRefValues($filter) {
		$AnalyzerTest_IsTest = $this->getFirstResultFromQuery("
			SELECT
				at.AnalyzerTest_IsTest
			FROM
				lis.v_AnalyzerTest at (nolock)
				inner join v_RefValuesSet rvs (nolock) on rvs.AnalyzerTest_id = at.AnalyzerTest_id
			WHERE
				rvs.RefValuesSet_id = :RefValuesSet_id", $filter);
		
		if ($AnalyzerTest_IsTest == 1) {
			$query = "
				SELECT
					rvsrv.RefValuesSetRefValues_id,
					rvsrv.RefValuesSet_id,
					rvsrv.RefValues_id,
					ISNULL(at.AnalyzerTest_Name, uc.UslugaComplex_Name) as AnalyzerTest_Name,
					rvsrv.RefValues_Name,
					at.AnalyzerTestType_id,
					case when att.AnalyzerTestType_Code IN (1,3) then
						case when rvsrv.RefValues_LowerLimit is null and rvsrv.RefValues_UpperLimit is null then '' else ISNULL(cast(rvsrv.RefValues_LowerLimit as varchar),'...') + ' - ' + ISNULL(cast(rvsrv.RefValues_UpperLimit as varchar),'...') end
					else
						''
					end as RefValues_Limit,
					case when att.AnalyzerTestType_Code IN (1,3) then
						case when rvsrv.RefValues_BotCritValue is null and rvsrv.RefValues_TopCritValue is null then '' else ISNULL(cast(rvsrv.RefValues_BotCritValue as varchar),'...') + ' - ' + ISNULL(cast(rvsrv.RefValues_TopCritValue as varchar),'...') end
					else
						''
					end as RefValues_CritValue,
					u.Unit_Name,
					rvsrv.RefValues_Description,
					sex.Sex_Name,
					age.RefValues_Age,
					phaza.HormonalPhaseType_Name,
					berem.RefValues_Pregnancy,
					vrem.RefValues_TimeOfDay
				FROM
					v_RefValuesSetRefValues rvsrv (nolock)
					inner join v_RefValuesSet rvs (nolock) on rvs.RefValuesSet_id = rvsrv.RefValuesSet_id 
					inner join lis.v_AnalyzerTest at (nolock) on at.AnalyzerTest_id = rvs.AnalyzerTest_id
					inner join lis.v_AnalyzerTestType att (nolock) on att.AnalyzerTestType_id = at.AnalyzerTestType_id
					left join v_UslugaComplex (nolock) uc on uc.UslugaComplex_id = at.UslugaComplex_id
					outer apply(
						select top 1
							s.Sex_Name
						from
							v_Sex s (nolock)
							inner join v_Limit l (nolock) on l.Limit_Values = s.Sex_id
							inner join v_LimitType lt (nolock) on lt.LimitType_id = l.LimitType_id
						where
							lt.LimitType_SysNick = 'Sex'
							and l.RefValuesSetRefValues_id = rvsrv.RefValuesSetRefValues_id
					) sex
					outer apply(
						select top 1
							hpt.HormonalPhaseType_Name
						from
							v_HormonalPhaseType hpt (nolock)
							inner join v_Limit l (nolock) on l.Limit_Values = hpt.HormonalPhaseType_id
							inner join v_LimitType lt (nolock) on lt.LimitType_id = l.LimitType_id
						where
							lt.LimitType_SysNick = 'HormonalPhaseType'
							and l.RefValuesSetRefValues_id = rvsrv.RefValuesSetRefValues_id
					) phaza
					outer apply(
						select top 1
							case when Limit_ValuesFrom is null and Limit_ValuesTo is null then '' else ISNULL(cast(Limit_ValuesFrom as varchar),'...') + ' - ' + ISNULL(cast(Limit_ValuesTo as varchar),'...') + ISNULL(' ('+AU.AgeUnit_Name+')','') end as RefValues_Age
						from
							v_Limit l (nolock)
							inner join v_LimitType lt (nolock) on lt.LimitType_id = l.LimitType_id
							left join v_AgeUnit au (nolock) on au.AgeUnit_id = l.Limit_Values
						where
							lt.LimitType_SysNick = 'AgeUnit'
							and l.RefValuesSetRefValues_id = rvsrv.RefValuesSetRefValues_id
					) age
					outer apply(
						select top 1
							case when Limit_ValuesFrom is null and Limit_ValuesTo is null then '' else ISNULL(cast(Limit_ValuesFrom as varchar),'...') + ' - ' + ISNULL(cast(Limit_ValuesTo as varchar),'...') + ISNULL(' ('+PUT.PregnancyUnitType_Name+')','') end as RefValues_Pregnancy
						from
							v_Limit l (nolock)
							inner join v_LimitType lt (nolock) on lt.LimitType_id = l.LimitType_id
							left join v_PregnancyUnitType put (nolock) on put.PregnancyUnitType_id = l.Limit_Values
						where
							lt.LimitType_SysNick = 'PregnancyUnitType'
							and l.RefValuesSetRefValues_id = rvsrv.RefValuesSetRefValues_id
					) berem
					outer apply(
						select top 1
							case when Limit_ValuesFrom is null and Limit_ValuesTo is null then '' else ISNULL(cast(Limit_ValuesFrom as varchar),'...') + ' - ' + ISNULL(cast(Limit_ValuesTo as varchar),'...') end as RefValues_TimeOfDay
						from
							v_Limit l (nolock)
							inner join v_LimitType lt (nolock) on lt.LimitType_id = l.LimitType_id
							left join v_PregnancyUnitType put (nolock) on put.PregnancyUnitType_id = l.Limit_Values
						where
							lt.LimitType_id = 7
							and l.RefValuesSetRefValues_id = rvsrv.RefValuesSetRefValues_id
					) vrem
					left join lis.v_Unit u (nolock) on u.Unit_id = rvsrv.Unit_id
				WHERE
					rvs.RefValuesSet_pid = :RefValuesSet_id
				ORDER BY
					at.AnalyzerTest_Name
			";
		} else {
			$query = "
				SELECT
					rvsrv.RefValuesSetRefValues_id,
					rvsrv.RefValuesSet_id,
					rvsrv.RefValues_id,
					rvsrv.RefValues_Name,
					at.AnalyzerTestType_id,
					case when att.AnalyzerTestType_Code IN (1,3) then
						case when rvsrv.RefValues_LowerLimit is null and rvsrv.RefValues_UpperLimit is null then '' else ISNULL(cast(rvsrv.RefValues_LowerLimit as varchar),'...') + ' - ' + ISNULL(cast(rvsrv.RefValues_UpperLimit as varchar),'...') end
					else
						''
					end as RefValues_Limit,
					case when att.AnalyzerTestType_Code IN (1,3) then
						case when rvsrv.RefValues_BotCritValue is null and rvsrv.RefValues_TopCritValue is null then '' else ISNULL(cast(rvsrv.RefValues_BotCritValue as varchar),'...') + ' - ' + ISNULL(cast(rvsrv.RefValues_TopCritValue as varchar),'...') end
					else
						''
					end as RefValues_CritValue,
					u.Unit_Name,
					rvsrv.RefValues_Description,
					sex.Sex_Name,
					age.RefValues_Age,
					phaza.HormonalPhaseType_Name,
					berem.RefValues_Pregnancy,
					vrem.RefValues_TimeOfDay
				FROM
					v_RefValuesSetRefValues rvsrv (nolock)
					inner join v_RefValuesSet rvs (nolock) on rvs.RefValuesSet_id = rvsrv.RefValuesSet_id 
					inner join lis.v_AnalyzerTest at (nolock) on at.AnalyzerTest_id = rvs.AnalyzerTest_id
					inner join lis.v_AnalyzerTestType att (nolock) on att.AnalyzerTestType_id = at.AnalyzerTestType_id
					outer apply(
						select top 1
							s.Sex_Name
						from
							v_Sex s (nolock)
							inner join v_Limit l (nolock) on l.Limit_Values = s.Sex_id
							inner join v_LimitType lt (nolock) on lt.LimitType_id = l.LimitType_id
						where
							lt.LimitType_SysNick = 'Sex'
							and l.RefValuesSetRefValues_id = rvsrv.RefValuesSetRefValues_id
					) sex
					outer apply(
						select top 1
							hpt.HormonalPhaseType_Name
						from
							v_HormonalPhaseType hpt (nolock)
							inner join v_Limit l (nolock) on l.Limit_Values = hpt.HormonalPhaseType_id
							inner join v_LimitType lt (nolock) on lt.LimitType_id = l.LimitType_id
						where
							lt.LimitType_SysNick = 'HormonalPhaseType'
							and l.RefValuesSetRefValues_id = rvsrv.RefValuesSetRefValues_id
					) phaza
					outer apply(
						select top 1
							case when Limit_ValuesFrom is null and Limit_ValuesTo is null then '' else ISNULL(cast(Limit_ValuesFrom as varchar),'...') + ' - ' + ISNULL(cast(Limit_ValuesTo as varchar),'...') + ISNULL(' ('+AU.AgeUnit_Name+')','') end as RefValues_Age
						from
							v_Limit l (nolock)
							inner join v_LimitType lt (nolock) on lt.LimitType_id = l.LimitType_id
							left join v_AgeUnit au (nolock) on au.AgeUnit_id = l.Limit_Values
						where
							lt.LimitType_SysNick = 'AgeUnit'
							and l.RefValuesSetRefValues_id = rvsrv.RefValuesSetRefValues_id
					) age
					outer apply(
						select top 1
							case when Limit_ValuesFrom is null and Limit_ValuesTo is null then '' else ISNULL(cast(Limit_ValuesFrom as varchar),'...') + ' - ' + ISNULL(cast(Limit_ValuesTo as varchar),'...') + ISNULL(' ('+PUT.PregnancyUnitType_Name+')','') end as RefValues_Pregnancy
						from
							v_Limit l (nolock)
							inner join v_LimitType lt (nolock) on lt.LimitType_id = l.LimitType_id
							left join v_PregnancyUnitType put (nolock) on put.PregnancyUnitType_id = l.Limit_Values
						where
							lt.LimitType_SysNick = 'PregnancyUnitType'
							and l.RefValuesSetRefValues_id = rvsrv.RefValuesSetRefValues_id
					) berem
					outer apply(
						select top 1
							case when Limit_ValuesFrom is null and Limit_ValuesTo is null then '' else ISNULL(cast(Limit_ValuesFrom as varchar),'...') + ' - ' + ISNULL(cast(Limit_ValuesTo as varchar),'...') end as RefValues_TimeOfDay
						from
							v_Limit l (nolock)
							inner join v_LimitType lt (nolock) on lt.LimitType_id = l.LimitType_id
							left join v_PregnancyUnitType put (nolock) on put.PregnancyUnitType_id = l.Limit_Values
						where
							lt.LimitType_id = 7
							and l.RefValuesSetRefValues_id = rvsrv.RefValuesSetRefValues_id
					) vrem
					left join lis.v_Unit u (nolock) on u.Unit_id = rvsrv.Unit_id
				WHERE
					rvsrv.RefValuesSet_id = :RefValuesSet_id
			";
		}
		$result = $this->db->query($query, $filter);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			foreach ($resp as &$respone) {
				// для качественных подгружаем нормальные значения через запятую
				if ($respone['AnalyzerTestType_id'] == 2) {
					$respone['RefValues_Limit'] = $this->getRefValuesForQualitativeTest($respone['RefValuesSetRefValues_id']);
				}
			}
			return $resp;
		}
		else {
			return false;
		}
	}

	/**
	 * Пересохранение
	 */
	function resaveRefValuesSet($data) {
		$data['AnalyzerTest_id'] = $this->getFirstResultFromQuery("SELECT AnalyzerTest_id FROM v_RefValuesSet (nolock) WHERE RefValuesSet_id = :RefValuesSet_id", $data);
		if (empty($data['AnalyzerTest_id'])) {
			return false;
		}
		
		// 1. проверяем AnalyzerTest_IsTest
		if (empty($data['AnalyzerTest_IsTest']) || $data['AnalyzerTest_IsTest'] != 2) {
			$AnalyzerTest_IsTest = $this->getFirstResultFromQuery("SELECT AnalyzerTest_IsTest FROM lis.v_AnalyzerTest (nolock) WHERE AnalyzerTest_id = :AnalyzerTest_id", $data);
			if ($AnalyzerTest_IsTest == 1) {
				// обновить поле в AnalyzerTest
				$this->saveLinkToRefValuesSet($data);
				
				// сначала удаляем вложенные наборы
				$this->deleteChildRefValuesSet($data);
				
				$data['RefValuesSet_Name'] = $this->getFirstResultFromQuery("SELECT RefValuesSet_Name FROM v_RefValuesSet (nolock) WHERE RefValuesSet_id = :RefValuesSet_id", $data);
				// сохраняем вложенные наборы
				$this->saveChildRefValuesSet($data);
				
				return array('Error_Msg' => '');
			}
		}
		
		$this->clearRefValuesSet($data);
		$this->saveAnalyzerTestRefValuesIntoRefValuesSet($data);
		
		return array('Error_Msg' => '');
	}
	
	/**
	 * Очистка набора референсных значений
	 */
	function clearRefValuesSet($data) {
		$query = "
			select
				RefValuesSetRefValues_id
			from
				v_RefValuesSetRefValues (nolock)
			where
				RefValuesSet_id = :RefValuesSet_id
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			foreach ($resp as $respone) {
				// сначала удаляем связанные записи качественных референсных значений
				$query = "
					select
						QualitativeTestAnswerReferValue_id
					from
						lis.v_QualitativeTestAnswerReferValue (nolock)
					where
						RefValuesSetRefValues_id = :RefValuesSetRefValues_id
				";
				$result = $this->db->query($query, array(
					'RefValuesSetRefValues_id' => $respone['RefValuesSetRefValues_id']
				));
				if ( is_object($result) ) {
					$resp_qtarv = $result->result('array');
					foreach($resp_qtarv as $respone_qtarv) {
						$query = "
							declare
								@ErrCode int,
								@ErrMessage varchar(4000);
							exec lis.p_QualitativeTestAnswerReferValue_del
								@QualitativeTestAnswerReferValue_id = :QualitativeTestAnswerReferValue_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;
							select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						";
						$this->db->query($query, array(
							'QualitativeTestAnswerReferValue_id' => $respone_qtarv['QualitativeTestAnswerReferValue_id']
						));
					}
				}
				
				// сначала удаляем связанные записи ограничений
				$query = "
					select
						Limit_id
					from
						v_Limit (nolock)
					where
						RefValuesSetRefValues_id = :RefValuesSetRefValues_id
				";
				$result = $this->db->query($query, array(
					'RefValuesSetRefValues_id' => $respone['RefValuesSetRefValues_id']
				));
				if ( is_object($result) ) {
					$resp_limit = $result->result('array');
					foreach($resp_limit as $respone_limit) {
						$query = "
							declare
								@ErrCode int,
								@ErrMessage varchar(4000);
							exec p_Limit_del
								@Limit_id = :Limit_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;
							select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						";
						$this->db->query($query, array(
							'Limit_id' => $respone_limit['Limit_id']
						));
					}
				}
				
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec p_RefValuesSetRefValues_del
						@RefValuesSetRefValues_id = :RefValuesSetRefValues_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$this->db->query($query, array(
					'RefValuesSetRefValues_id' => $respone['RefValuesSetRefValues_id']
				));
			}		
		}
	}
	
	/**
	 * сохраняем все текущие референсные значения в RefValuesSetRefValues
	 */
	function saveAnalyzerTestRefValuesIntoRefValuesSet($data) {
		$query = "
			select
				atrv.AnalyzerTestRefValues_id,
				rv.*
			from
				v_RefValues rv (nolock)
				inner join lis.v_AnalyzerTestRefValues atrv (nolock) on atrv.RefValues_id = rv.RefValues_id
			where
				atrv.AnalyzerTest_id = :AnalyzerTest_id
		";
		$result_rv = $this->db->query($query, $data);
		if ( is_object($result_rv) ) {
			$resp_rv = $result_rv->result('array');
			foreach ($resp_rv as $resp_rvone) {
				$resp_rvone['RefValuesSet_id'] = $data['RefValuesSet_id'];
				$resp_rvone['pmUser_id'] = $data['pmUser_id'];

				if ( !empty($resp_rvone['RefValues_LowerLimit']) ) {
					$resp_rvone['RefValues_LowerLimit'] = str_replace(',', '.', $resp_rvone['RefValues_LowerLimit']);
				}

				if ( !empty($resp_rvone['RefValues_UpperLimit']) ) {
					$resp_rvone['RefValues_UpperLimit'] = str_replace(',', '.', $resp_rvone['RefValues_UpperLimit']);
				}
				
				$query = "
					declare
						@RefValuesSetRefValues_id bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @RefValuesSetRefValues_id = NULL;
					exec p_RefValuesSetRefValues_ins
						@RefValuesSetRefValues_id = @RefValuesSetRefValues_id output,
						@RefValuesSet_id = :RefValuesSet_id,
						@RefValues_id = null,
						@Lpu_id = :Lpu_id,
						@RefValues_Code = :RefValues_Code,
						@RefValues_OPMUCode = :RefValues_OPMUCode,
						@RefValues_LocalCode = :RefValues_LocalCode,
						@RefValues_Name = :RefValues_Name,
						@RefValues_Nick = :RefValues_Nick,
						@RefValuesType_id = :RefValuesType_id,
						@RefValuesUnit_id = :RefValuesUnit_id,
						@RefValues_LowerLimit = :RefValues_LowerLimit,
						@RefValues_UpperLimit = :RefValues_UpperLimit,
						@RefValuesGroup_id = :RefValuesGroup_id,
						@RefValues_LowerAge = :RefValues_LowerAge,
						@RefValues_UpperAge = :RefValues_UpperAge,
						@AgeUnit_id = :AgeUnit_id,
						@RefCategory_id = :RefCategory_id,
						@HormonalPhaseType_id = :HormonalPhaseType_id,
						@TimeOfDay_id = :TimeOfDay_id,
						@RefMaterial_id = :RefMaterial_id,
						@RefValues_Cost = :RefValues_Cost,
						@RefValues_UET = :RefValues_UET,
						@RefValues_Method = :RefValues_Method,
						@RefValues_Description = :RefValues_Description,
						@RefValues_BotCritValue = :RefValues_BotCritValue,
						@RefValues_TopCritValue = :RefValues_TopCritValue,
						@Sex_id = :Sex_id,
						@RefValues_PregnancyFrom = :RefValues_PregnancyFrom,
						@RefValues_PregnancyTo = :RefValues_PregnancyTo,
						@RefValues_TimeOfDayFrom = :RefValues_TimeOfDayFrom,
						@RefValues_TimeOfDayTo = :RefValues_TimeOfDayTo,
						@PregnancyUnitType_id = :PregnancyUnitType_id,
						@Unit_id = :Unit_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @RefValuesSetRefValues_id as RefValuesSetRefValues_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";						
				
				$result_save = $this->db->query($query, $resp_rvone);
				if (is_object($result_save)) {
					$resp_save = $result_save->result('array');
					if (!empty($resp_save[0]['RefValuesSetRefValues_id'])) {
						// для каждого копируем ещё и QualitativeTestAnswerReferValue.
						$query = "
							select
								qtarv.*
							from
								lis.v_QualitativeTestAnswerReferValue qtarv (nolock)
							where
								qtarv.AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id
						";
						$result_qtarv = $this->db->query($query, $resp_rvone);
						if ( is_object($result_qtarv) ) {
							$resp_qtarv = $result_qtarv->result('array');
							foreach ($resp_qtarv as $resp_qtarvone) {
								$resp_qtarvone['RefValuesSetRefValues_id'] = $resp_save[0]['RefValuesSetRefValues_id'];
								$resp_qtarvone['pmUser_id'] = $data['pmUser_id'];
								
								$query = "
									declare
										@QualitativeTestAnswerReferValue_id bigint,
										@ErrCode int,
										@ErrMessage varchar(4000);
									set @QualitativeTestAnswerReferValue_id = NULL;
									exec lis.p_QualitativeTestAnswerReferValue_ins
										@QualitativeTestAnswerReferValue_id = @QualitativeTestAnswerReferValue_id output,
										@AnalyzerTestRefValues_id = null,
										@QualitativeTestAnswerAnalyzerTest_id = :QualitativeTestAnswerAnalyzerTest_id,
										@RefValuesSetRefValues_id = :RefValuesSetRefValues_id,
										@pmUser_id = :pmUser_id,
										@Error_Code = @ErrCode output,
										@Error_Message = @ErrMessage output;
									select @QualitativeTestAnswerReferValue_id as QualitativeTestAnswerReferValue_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
								";						
								
								$this->db->query($query, $resp_qtarvone);
							}					
						}
						
						// для каждого копируем ещё и ограничения Limit
						$query = "
							select
								l.*
							from
								v_Limit l (nolock)
							where
								l.RefValues_id = :RefValues_id
						";
						$result_limit = $this->db->query($query, $resp_rvone);
						if ( is_object($result_limit) ) {
							$resp_limit = $result_limit->result('array');
							foreach ($resp_limit as $resp_limitone) {
								$resp_limitone['RefValuesSetRefValues_id'] = $resp_save[0]['RefValuesSetRefValues_id'];
								$resp_limitone['pmUser_id'] = $data['pmUser_id'];
								
								$query = "
									declare
										@Limit_id bigint,
										@ErrCode int,
										@ErrMessage varchar(4000);
									set @Limit_id = NULL;
									exec p_Limit_ins
										@Limit_id = @Limit_id output,
										@RefValues_id = null,
										@LimitType_id = :LimitType_id,
										@Limit_Values = :Limit_Values,
										@Limit_ValuesFrom = :Limit_ValuesFrom,
										@Limit_ValuesTo = :Limit_ValuesTo,
										@Limit_IsActiv = :Limit_IsActiv,
										@RefValuesSetRefValues_id = :RefValuesSetRefValues_id,
										@pmUser_id = :pmUser_id,
										@Error_Code = @ErrCode output,
										@Error_Message = @ErrMessage output;
									select @Limit_id as Limit_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
								";						
								
								$this->db->query($query, $resp_limitone);
							}					
						}
					}
				}
			}					
		}
	}
	
	/**
	 * Сохранение ссылки на набор в исследовании
	 */
	function saveLinkToRefValuesSet($data) {
		$query = "
			update
				lis.AnalyzerTest with (rowlock)
			set
				RefValuesSet_id = :RefValuesSet_id
			where
				AnalyzerTest_id = :AnalyzerTest_id
		";
		$this->db->query($query, $data);
	}
	
	/**
	 * Сохранение вложеных наборов
	 */
	function saveChildRefValuesSet($data) {
		$query = "
			select
				at.AnalyzerTest_id
			from
				lis.v_AnalyzerTest at (nolock)
			where
				at.AnalyzerTest_pid = :AnalyzerTest_id
		";
		$result_rvs = $this->db->query($query, $data);
		if ( is_object($result_rvs) ) {
			$resp_rvs = $result_rvs->result('array');
			foreach ($resp_rvs as $resp_rvsone) {
				$data['RefValuesSet_pid'] = $data['RefValuesSet_id'];
				$data['AnalyzerTest_id'] = $resp_rvsone['AnalyzerTest_id'];
				$data['AnalyzerTest_IsTest'] = 2;
				$this->saveRefValuesSet($data);
			}
		}
	}
	
	/**
	 * Сохранение
	 */
	function saveRefValuesSet($data) {
		if (empty($data['RefValuesSet_pid'])) {
			$data['RefValuesSet_pid'] = null;

			// проверка на уникальность названия набора.
			$query = "
				select
					RefValuesSet_id
				from
					v_RefValuesSet (nolock)
				where	
					RefValuesSet_Name = :RefValuesSet_Name
					and AnalyzerTest_id = :AnalyzerTest_id
					and RefValuesSet_pid IS NULL
			";
			$result = $this->db->query($query, $data);
			if ( is_object($result) ) {
				$resp = $result->result('array');
				if (!empty($resp[0]['RefValuesSet_id'])) {
					return array('Error_Msg' => 'Уже существует набор референсных значений с таким названием');
				}
			}
		}
		
		$query = "
			declare
				@RefValuesSet_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @RefValuesSet_id = NULL;
			exec p_RefValuesSet_ins
				@RefValuesSet_id = @RefValuesSet_id output,
				@RefValuesSet_pid = :RefValuesSet_pid,
				@AnalyzerTest_id = :AnalyzerTest_id,
				@RefValuesSet_Name = :RefValuesSet_Name,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @RefValuesSet_id as RefValuesSet_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (!empty($resp[0]['RefValuesSet_id'])) {
				if (empty($data['AnalyzerTest_IsTest']) || $data['AnalyzerTest_IsTest'] != 2) {
					$AnalyzerTest_IsTest = $this->getFirstResultFromQuery("SELECT AnalyzerTest_IsTest FROM lis.v_AnalyzerTest (nolock) WHERE AnalyzerTest_id = :AnalyzerTest_id", $data);
					if ($AnalyzerTest_IsTest == 1) {
						$data['RefValuesSet_id'] = $resp[0]['RefValuesSet_id'];
						
						// обновить поле в AnalyzerTest
						$this->saveLinkToRefValuesSet($data);

						// сохраняем вложенные наборы
						$this->saveChildRefValuesSet($data);
						
						return $resp;
					}
				}
				
				$data['RefValuesSet_id'] = $resp[0]['RefValuesSet_id'];
				$this->saveAnalyzerTestRefValuesIntoRefValuesSet($data);
			}
			return $resp;
		}

		return false;
	}
	
	/**
	 * Удаление дочерних наборов
	 */
	function deleteChildRefValuesSet($data)
	{
		$query = "
			select
				rvs.RefValuesSet_id
			from
				v_RefValuesSet rvs (nolock)
			where
				rvs.RefValuesSet_pid = :RefValuesSet_id
		";
		$result_rvs = $this->db->query($query, $data);
		if ( is_object($result_rvs) ) {
			$resp_rvs = $result_rvs->result('array');
			foreach ($resp_rvs as $resp_rvsone) {
				$item = $data;
				$item['RefValuesSet_id'] = $resp_rvsone['RefValuesSet_id'];
				$item['AnalyzerTest_IsTest'] = 2;
				$this->delete($item);
			}
		}
	}
	
	/**
	 * Удаление
	 */
	function delete($data) {
		if (empty($data['AnalyzerTest_IsTest']) || $data['AnalyzerTest_IsTest'] != 2) {
			$AnalyzerTest_IsTest = $this->getFirstResultFromQuery("
				SELECT
					at.AnalyzerTest_IsTest
				FROM
					lis.v_AnalyzerTest at (nolock)
					inner join v_RefValuesSet rvs (nolock) on rvs.AnalyzerTest_id = at.AnalyzerTest_id
				WHERE
					rvs.RefValuesSet_id = :RefValuesSet_id
			", $data);
			
			if ($AnalyzerTest_IsTest == 1) {
				// проверяем, а не используется ли данный набор
				$query = "
					select
						AnalyzerTest_id
					from
						lis.AnalyzerTest (nolock)
					where
						RefValuesSet_id = :RefValuesSet_id
				";
				$result = $this->db->query($query, $data);
				if ( is_object($result) ) {
					$resp = $result->result('array');
					if (!empty($resp[0]['AnalyzerTest_id'])) {
						return array('Error_Msg' => 'Нельзя удалить данный набор, т.к. он используется');
					}
				}
				
				// сначала удаляем вложенные наборы
				$this->deleteChildRefValuesSet($data);
			}
		}
				
		// сначала удаляем значения из набора
		$this->clearRefValuesSet($data);
		
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_RefValuesSet_del
				@RefValuesSet_id = :RefValuesSet_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($query, array(
			'RefValuesSet_id' => $data['RefValuesSet_id']
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}
}