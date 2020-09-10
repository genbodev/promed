<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Референсные значения тестов
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version
 */
class AnalyzerTestRefValues_model extends swModel {
	/**
	 * Загрузка единицы измерения
	 */
	function load($data) {
		$query = "
			select
				atrv.AnalyzerTestRefValues_id,
				atrv.AnalyzerTest_id,
				atrv.RefValues_id,
				rv.RefValues_Name,
				rv.Unit_id,
				rv.RefValues_LowerLimit,
				rv.RefValues_UpperLimit,
				rv.RefValues_BotCritValue,
				rv.RefValues_TopCritValue,
				rv.RefValues_Description
			from
				lis.v_AnalyzerTestRefValues atrv (nolock)
				inner join v_RefValues rv (nolock) on rv.RefValues_id = atrv.RefValues_id
				left join lis.v_QualitativeTestAnswerReferValue qtarv (nolock) on qtarv.AnalyzerTestRefValues_id = atrv.AnalyzerTestRefValues_id
			where
				atrv.AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id
		";

		$r = $this->db->query($query, array('AnalyzerTestRefValues_id' => $data['AnalyzerTestRefValues_id']));
		if ( is_object($r) ) {
			return $r->result('array');
		}

		return false;
	}

	/**
	 * Получение нормальных значений для качественного текста
	 */
	function getRefValuesForQualitativeTest($AnalyzerTestRefValues_id)
	{
		$norms = '';
		$query = "
			select
				qtaat.QualitativeTestAnswerAnalyzerTest_Answer
			from
				lis.v_QualitativeTestAnswerReferValue qtarv (nolock)
				inner join lis.v_QualitativeTestAnswerAnalyzerTest qtaat (nolock) on qtaat.QualitativeTestAnswerAnalyzerTest_id = qtarv.QualitativeTestAnswerAnalyzerTest_id
			where
				qtarv.AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id
		";

		$result = $this->db->query($query, array(
			'AnalyzerTestRefValues_id' => $AnalyzerTestRefValues_id
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
	 * Получение нормальных значений для качественного текста
	 */
	function getRefValuesForQualitativeTestJSON($AnalyzerTestRefValues_id)
	{
		$array = array();

		$query = "
			select
				qtaat.QualitativeTestAnswerAnalyzerTest_Answer
			from
				lis.v_QualitativeTestAnswerReferValue qtarv (nolock)
				inner join lis.v_QualitativeTestAnswerAnalyzerTest qtaat (nolock) on qtaat.QualitativeTestAnswerAnalyzerTest_id = qtarv.QualitativeTestAnswerAnalyzerTest_id
			where
				qtarv.AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id
		";

		$result = $this->db->query($query, array(
			'AnalyzerTestRefValues_id' => $AnalyzerTestRefValues_id
		));
		if ( is_object($result) ) {
			$resp = $result->result('array');
			foreach ($resp as $respone) {
				$array[] = toUtf($respone['QualitativeTestAnswerAnalyzerTest_Answer']);
			}
		}

		return json_encode($array);
	}

	/**
	 * Загрузка списка
	 */
	function loadList($filter) {
		$query = "
			SELECT
				atrv.AnalyzerTestRefValues_id,
				atrv.RefValues_id,
				rv.RefValues_Name,
				at.AnalyzerTestType_id,
				case when att.AnalyzerTestType_Code IN (1,3) then
					case when rv.RefValues_LowerLimit is null and rv.RefValues_UpperLimit is null then '' else ISNULL(cast(rv.RefValues_LowerLimit as varchar),'...') + ' - ' + ISNULL(cast(rv.RefValues_UpperLimit as varchar),'...') end
				else
					''
				end as RefValues_Limit,
				case when att.AnalyzerTestType_Code IN (1,3) then
					case when rv.RefValues_BotCritValue is null and rv.RefValues_TopCritValue is null then '' else ISNULL(cast(rv.RefValues_BotCritValue as varchar),'...') + ' - ' + ISNULL(cast(rv.RefValues_TopCritValue as varchar),'...') end
				else
					''
				end as RefValues_CritValue,
				u.Unit_Name,
				rv.RefValues_Description,
				sex.Sex_Name,
				age.RefValues_Age,
				phaza.HormonalPhaseType_Name,
				berem.RefValues_Pregnancy,
				vrem.RefValues_TimeOfDay
			FROM
				lis.v_AnalyzerTestRefValues atrv (nolock)
				inner join lis.v_AnalyzerTest at (nolock) on at.AnalyzerTest_id = atrv.AnalyzerTest_id
				inner join lis.v_AnalyzerTestType att (nolock) on att.AnalyzerTestType_id = at.AnalyzerTestType_id
				inner join v_RefValues rv (nolock) on rv.RefValues_id = atrv.RefValues_id
				outer apply(
					select top 1
						s.Sex_Name
					from
						v_Sex s (nolock)
						inner join v_Limit l (nolock) on l.Limit_Values = s.Sex_id
						inner join v_LimitType lt (nolock) on lt.LimitType_id = l.LimitType_id
					where
						lt.LimitType_SysNick = 'Sex'
						and l.RefValues_id = atrv.RefValues_id
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
						and l.RefValues_id = atrv.RefValues_id
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
						and l.RefValues_id = atrv.RefValues_id
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
						and l.RefValues_id = atrv.RefValues_id
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
						and l.RefValues_id = atrv.RefValues_id
				) vrem
				left join lis.v_Unit u (nolock) on u.Unit_id = rv.Unit_id
			WHERE
				atrv.AnalyzerTest_id = :AnalyzerTest_id
		";
		$result = $this->db->query($query, $filter);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			foreach ($resp as &$respone) {
				// для качественных подгружаем нормальные значения через запятую
				if ($respone['AnalyzerTestType_id'] == 2) {
					$respone['RefValues_Limit'] = $this->getRefValuesForQualitativeTest($respone['AnalyzerTestRefValues_id']);
				}
			}
			return $resp;
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение
	 */
	function save($data) {
		// проверка на дубли
		$query = "
			select
				rv.RefValues_Name
			from
				lis.v_AnalyzerTestRefValues atrv (nolock)
				inner join v_RefValues rv (nolock) on rv.RefValues_id = atrv.RefValues_id
			where
				ISNULL(rv.Unit_id,0) = ISNULL(:Unit_id,0)
				and ISNULL(rv.RefValues_LowerLimit,0) = ISNULL(:RefValues_LowerLimit,0)
				and ISNULL(rv.RefValues_UpperLimit,0) = ISNULL(:RefValues_UpperLimit,0)
				and ISNULL(rv.RefValues_BotCritValue,0) = ISNULL(:RefValues_BotCritValue,0)
				and ISNULL(rv.RefValues_TopCritValue,0) = ISNULL(:RefValues_TopCritValue,0)
				and (atrv.AnalyzerTestRefValues_id <> :AnalyzerTestRefValues_id OR :AnalyzerTestRefValues_id IS NULL)
				and atrv.AnalyzerTest_id = :AnalyzerTest_id
		";

		// + проверка по ограничениям
		if (!empty($data['LimitData'])) {
			$data['LimitData'] = toUtf($data['LimitData']);
			$limitdata = json_decode($data['LimitData'], true);

			$k = 1;
			foreach($limitdata as $limit) {
				if (!empty($limit['LimitType_id'])) {
					$limit['LimitType_IsCatalog'] = 1;

					$query_lt = "
						select top 1
							ISNULL(lt.LimitType_IsCatalog, 1) as LimitType_IsCatalog
						from
							v_LimitType (nolock) lt
						where
							lt.LimitType_id = :LimitType_id
					";

					$result = $this->db->query($query_lt, $limit);
					if ( is_object($result) ) {
						$resp = $result->result('array');
						if (!empty($resp[0]['LimitType_IsCatalog'])) {
							$limit['LimitType_IsCatalog'] = $resp[0]['LimitType_IsCatalog'];
						}
					}

					if (!isset($limit['Limit_ValuesTo']) || !is_numeric($limit['Limit_ValuesTo'])) { $limit['Limit_ValuesTo'] = null;	}
					if (!isset($limit['Limit_ValuesFrom']) || !is_numeric($limit['Limit_ValuesFrom'])) { $limit['Limit_ValuesFrom'] = null;	}
					if (empty($limit['Limit_Values'])) { $limit['Limit_Values'] = null;	}
					if (empty($limit['Limit_Unit'])) { $limit['Limit_Unit'] = null;	}

					if ($limit['LimitType_IsCatalog'] == 1) {
						$limit['Limit_Values'] = $limit['Limit_Unit'];
					}

					$k++;

					$query .= "
						and exists( select top 1 Limit_id from v_Limit with (nolock) where 
							RefValues_id = atrv.RefValues_id
							and ISNULL(LimitType_id, 0) = ISNULL(:Limit{$k}_LimitType_id, 0)
							and ISNULL(Limit_Values, 0) = ISNULL(:Limit{$k}_Limit_Values, 0)
							and ISNULL(Limit_ValuesFrom, 0) = ISNULL(:Limit{$k}_Limit_ValuesFrom, 0)
							and ISNULL(Limit_ValuesTo, 0) = ISNULL(:Limit{$k}_Limit_ValuesTo, 0)
						)
					";

					$data["Limit{$k}_LimitType_id"] = $limit['LimitType_id'];
					$data["Limit{$k}_Limit_Values"] = $limit['Limit_Values'];
					$data["Limit{$k}_Limit_ValuesFrom"] = $limit['Limit_ValuesFrom'];
					$data["Limit{$k}_Limit_ValuesTo"] = $limit['Limit_ValuesTo'];
				}
			}
		}

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return array('Error_Msg' => 'Такое референсное значение уже существует: "'.$resp[0]['RefValues_Name'].'"');
			}
		}

		$data['RefValues_id'] = null;
		// если задан AnalyzerTestRefValues_id то ищем RefValues_id
		if (!empty($data['AnalyzerTestRefValues_id'])) {
			$query = "
				select top 1
					RefValues_id
				from
					lis.v_AnalyzerTestRefValues (nolock)
				where
					AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id
			";

			$result = $this->db->query($query, $data);
			if ( is_object($result) ) {
				$resp = $result->result('array');
				if (!empty($resp[0]['RefValues_id'])) {
					$data['RefValues_id'] = $resp[0]['RefValues_id'];
				}
			}
		}

		$procedure = 'p_RefValues_ins';
		if ( !empty($data['RefValues_id']) ) {
			$procedure = 'p_RefValues_upd';
		}

		$query = "
			declare
				@RefValues_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @RefValues_id = :RefValues_id;
			exec " . $procedure . "
				@RefValues_id = @RefValues_id output,
				@RefValues_Name = :RefValues_Name,
				@RefValues_Nick = :RefValues_Name,
				@RefValuesType_id = NULL,
				@Unit_id = :Unit_id,
				@RefValues_LowerLimit = :RefValues_LowerLimit,
				@RefValues_UpperLimit = :RefValues_UpperLimit,
				@RefValues_BotCritValue = :RefValues_BotCritValue,
				@RefValues_TopCritValue = :RefValues_TopCritValue,
				@RefValues_Description = :RefValues_Description,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @RefValues_id as RefValues_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (!empty($resp[0]['RefValues_id'])) {
				$data['RefValues_id'] = $resp[0]['RefValues_id'];
			}
		}

		if (empty($data['RefValues_id'])) {
			return array('success' => false, 'Error_Msg' => 'Ошибка сохранения референсного значения');
		}

		if (!empty($data['LimitData'])) {
			$data['LimitData'] = toUtf($data['LimitData']);
			$limitdata = json_decode($data['LimitData'], true);

			foreach($limitdata as $limit) {
				$limit['Limit_id'] = null;
				$limit['LimitType_IsCatalog'] = 1;
				$limit['RefValues_id'] = $data['RefValues_id'];
				$limit['pmUser_id'] = $data['pmUser_id'];

				if (!empty($limit['LimitType_id'])) {
					// 1. ищем запись для соответвующего LimitType_id и RefValues_id
					$query = "
						select top 1
							l.Limit_id,
							ISNULL(lt.LimitType_IsCatalog, 1) as LimitType_IsCatalog
						from
							v_LimitType (nolock) lt
							left join v_Limit (nolock) l on l.LimitType_id = lt.LimitType_id and l.RefValues_id = :RefValues_id
						where
							lt.LimitType_id = :LimitType_id
					";

					$result = $this->db->query($query, $limit);
					if ( is_object($result) ) {
						$resp = $result->result('array');
						if (!empty($resp[0]['Limit_id'])) {
							$limit['Limit_id'] = $resp[0]['Limit_id'];
						}
						if (!empty($resp[0]['LimitType_IsCatalog'])) {
							$limit['LimitType_IsCatalog'] = $resp[0]['LimitType_IsCatalog'];
						}
					}

					// 2. сохраняем
					$procedure = 'p_Limit_ins';
					if ( !empty($limit['Limit_id']) ) {
						$procedure = 'p_Limit_upd';
					}

					if ($limit['Limit_IsActiv'] === 'true' || $limit['Limit_IsActiv'] == 1) {
						$limit['Limit_IsActiv'] = 2;
					} else {
						$limit['Limit_IsActiv'] = 1;
					}

					if (!isset($limit['Limit_ValuesTo']) || !is_numeric($limit['Limit_ValuesTo'])) { $limit['Limit_ValuesTo'] = null;	}
					if (!isset($limit['Limit_ValuesFrom']) || !is_numeric($limit['Limit_ValuesFrom'])) { $limit['Limit_ValuesFrom'] = null;	}
					if (empty($limit['Limit_Values'])) { $limit['Limit_Values'] = null;	}
					if (empty($limit['Limit_Unit'])) { $limit['Limit_Unit'] = null;	}

					if ($limit['LimitType_IsCatalog'] == 1) {
						$limit['Limit_Values'] = $limit['Limit_Unit'];
					}

					$query = "
						declare
							@Limit_id bigint,
							@ErrCode int,
							@ErrMessage varchar(4000);
						set @Limit_id = :Limit_id;
						exec " . $procedure . "
							@Limit_id = @Limit_id output,
							@LimitType_id = :LimitType_id,
							@Limit_Values = :Limit_Values,
							@RefValues_id = :RefValues_id,
							@Limit_ValuesFrom = :Limit_ValuesFrom,
							@Limit_ValuesTo = :Limit_ValuesTo,
							@Limit_IsActiv = :Limit_IsActiv,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @Limit_id as Limit_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$this->db->query($query, $limit);
				}
			}
		}

		$procedure = 'p_AnalyzerTestRefValues_ins';
		if ( !empty($data['AnalyzerTestRefValues_id']) ) {
			$procedure = 'p_AnalyzerTestRefValues_upd';
		}

		$query = "
			declare
				@AnalyzerTestRefValues_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id;
			exec lis." . $procedure . "
				@AnalyzerTestRefValues_id = @AnalyzerTestRefValues_id output,
				@AnalyzerTest_id = :AnalyzerTest_id,
				@RefValues_id = :RefValues_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @AnalyzerTestRefValues_id as AnalyzerTestRefValues_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Удаление
	 */
	function delete($data) {
		// сначала удаляем связанные записи качественных референсных значений
		$query = "
			select
				QualitativeTestAnswerReferValue_id
			from
				lis.v_QualitativeTestAnswerReferValue (nolock)
			where
				AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			foreach($resp as $respone) {
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
					'QualitativeTestAnswerReferValue_id' => $respone['QualitativeTestAnswerReferValue_id']
				));
			}
		}

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec lis.p_AnalyzerTestRefValues_del
				@AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($query, $data);
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение списка референсных значений
	 */
	function loadRefValuesList($data)
	{
		if (!empty($data['UslugaTest_id'])) {
			// получаем необходимые данные из услуги
			$query = "
				select top 1
					elr.MedService_id,
					euinp.UslugaComplex_id as UslugaComplexTarget_id,
					ut.UslugaComplex_id as UslugaComplexTest_id,
					els.Analyzer_id,
					elr.Person_id,
					convert(varchar(10), els.EvnLabSample_setDT, 120) as EvnLabSample_setDT
				from
					v_UslugaTest ut (nolock)
					left join v_EvnLabSample els (nolock) on els.EvnLabSample_id = ut.EvnLabSample_id
					left join v_EvnLabRequest elr (nolock) on elr.EvnLabRequest_id = els.EvnLabRequest_id
					inner join v_EvnUslugaPar euinp (nolock) on euinp.EvnUslugaPar_id = ut.UslugaTest_pid -- корневая услуга
				where
					ut.UslugaTest_id = :UslugaTest_id
			";
			//echo getDebugSQL($query, $data);

			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$data['MedService_id'] = $resp[0]['MedService_id'];
					$data['UslugaComplexTarget_id'] = $resp[0]['UslugaComplexTarget_id'];
					$data['UslugaComplexTest_id'] = $resp[0]['UslugaComplexTest_id'];
					$data['Analyzer_id'] = $resp[0]['Analyzer_id'];
					$data['EvnLabSample_setDT'] = $resp[0]['EvnLabSample_setDT'];
					$data['Person_id'] = $resp[0]['Person_id'];
				}
			}
		}

		$resp = array();

		$filter = "";
		$outer = "";
		$orderby = "";

		if (!empty($data['Analyzer_id'])) {
			// пусть с текущего анализатора выводятся первыми.
			$orderby .= "case when at.Analyzer_id = :Analyzer_id then 0 else 1 end, ";
		}

		if (!empty($data['OrderByLimit'])) {
			$outer = "
				outer apply(
					select 
						count(*) as cnt
					from
						v_Limit l (nolock)
						inner join v_LimitType lt (nolock) on lt.LimitType_id = l.LimitType_id
					where
						l.RefValues_id = rv.RefValues_id
						and (
							(l.Limit_Values IS NOT NULL AND lt.LimitType_IsCatalog = 2)
							OR
							((l.Limit_ValuesTo IS NOT NULL OR l.Limit_ValuesFrom IS NOT NULL) AND lt.LimitType_IsCatalog = 1)
						)
				) LIMIT
			";

			$orderby .= "LIMIT.cnt desc, ";
		}

		// фильтрация по исследованию, которое может выполняться на анализаторе
		$filter .= ' and ucms_at.UslugaComplex_id = :UslugaComplexTest_id';

		if ($data['UslugaComplexTarget_id'] != $data['UslugaComplexTest_id']) {
			$filter .= " and exists(select top 1 AnalyzerTest_id from lis.v_AnalyzerTest at_parent (nolock) inner join v_UslugaComplexMedService ucms_at_parent (nolock) on ucms_at_parent.UslugaComplexMedService_id = at_parent.UslugaComplexMedService_id where at_parent.AnalyzerTest_id = at.AnalyzerTest_pid and ucms_at_parent.UslugaComplex_id = :UslugaComplexTarget_id)";
		}

		$query = "
			select
				at.AnalyzerTestType_id,
				atrv.AnalyzerTestRefValues_id,
				rv.RefValues_id,
				rv.Unit_id,
				rv.RefValues_Name + ISNULL(' (' + a.Analyzer_Name + ')','') as RefValues_Name,
				'' as UslugaTest_ResultQualitativeNorms,
				case when att.AnalyzerTestType_Code IN (1,3) then cast(rv.RefValues_LowerLimit as varchar) else '' end as UslugaTest_ResultLower,
				case when att.AnalyzerTestType_Code IN (1,3) then cast(rv.RefValues_UpperLimit as varchar) else '' end as UslugaTest_ResultUpper,
				case when att.AnalyzerTestType_Code IN (1,3) then cast(rv.RefValues_BotCritValue as varchar) else '' end as UslugaTest_ResultLowerCrit,
				case when att.AnalyzerTestType_Code IN (1,3) then cast(rv.RefValues_TopCritValue as varchar) else '' end as UslugaTest_ResultUpperCrit,
				u.Unit_Name as UslugaTest_ResultUnit,
				rv.RefValues_Description as UslugaTest_Comment
			from
				lis.v_AnalyzerTest at (nolock)
				inner join v_UslugaComplexMedService ucms_at (nolock) on ucms_at.UslugaComplexMedService_id = at.UslugaComplexMedService_id
				inner join lis.v_AnalyzerTestType att (nolock) on att.AnalyzerTestType_id = at.AnalyzerTestType_id
				inner join lis.v_Analyzer a (nolock) on a.Analyzer_id = at.Analyzer_id
				inner join lis.v_AnalyzerTestRefValues atrv (nolock) on atrv.AnalyzerTest_id = at.AnalyzerTest_id
				inner join v_RefValues rv (nolock) on rv.RefValues_id = atrv.RefValues_id
				left join lis.v_Unit u (nolock) on u.Unit_id = rv.Unit_id
				{$outer}
			where
				a.MedService_id = :MedService_id
				
				{$filter}
			order by
				{$orderby}
				rv.RefValues_Name
		";
		//echo getDebugSql($query, $data); die();
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');
			foreach ($resp as &$respone) {
				if ($respone['AnalyzerTestType_id'] == 2) {
					$respone['UslugaTest_ResultQualitativeNorms'] = $this->getRefValuesForQualitativeTestJSON($respone['AnalyzerTestRefValues_id']);
				}
			}
		}
		return $resp;
	}
}