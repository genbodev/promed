<?php
defined("BASEPATH") or die ("No direct script access allowed");
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
 *
 * @property CI_DB_driver $db
 */
class AnalyzerTestRefValues_model extends SwPgModel
{
	/**
	 * Загрузка единицы измерения
	 * @param $data
	 * @return array|bool
	 */
	function load($data)
	{
		$query = "
			select
				atrv.AnalyzerTestRefValues_id as \"AnalyzerTestRefValues_id\",
				atrv.AnalyzerTest_id as \"AnalyzerTest_id\",
				atrv.RefValues_id as \"RefValues_id\",
				rv.RefValues_Name as \"RefValues_Name\",
				rv.Unit_id as \"Unit_id\",
				rv.RefValues_LowerLimit as \"RefValues_LowerLimit\",
				rv.RefValues_UpperLimit as \"RefValues_UpperLimit\",
				rv.RefValues_BotCritValue as \"RefValues_BotCritValue\",
				rv.RefValues_TopCritValue as \"RefValues_TopCritValue\",
				rv.RefValues_Description as \"RefValues_Description\"
			from
				lis.v_AnalyzerTestRefValues atrv
				inner join v_RefValues rv on rv.RefValues_id = atrv.RefValues_id
				left join lis.v_QualitativeTestAnswerReferValue qtarv on qtarv.AnalyzerTestRefValues_id = atrv.AnalyzerTestRefValues_id
			where atrv.AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, ["AnalyzerTestRefValues_id" => $data["AnalyzerTestRefValues_id"]]);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение нормальных значений для качественного текста
	 * @param $AnalyzerTestRefValues_id
	 * @return string
	 */
	function getRefValuesForQualitativeTest($AnalyzerTestRefValues_id)
	{
		$norms = "";
		$query = "
			select qtaat.QualitativeTestAnswerAnalyzerTest_Answer as \"QualitativeTestAnswerAnalyzerTest_Answer\"
			from
				lis.v_QualitativeTestAnswerReferValue qtarv
				inner join lis.v_QualitativeTestAnswerAnalyzerTest qtaat on qtaat.QualitativeTestAnswerAnalyzerTest_id = qtarv.QualitativeTestAnswerAnalyzerTest_id
			where qtarv.AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, ["AnalyzerTestRefValues_id" => $AnalyzerTestRefValues_id]);
		if (is_object($result)) {
			$resp = $result->result_array();
			foreach ($resp as $respone) {
				if (!empty($norms)) {
					$norms .= ", ";
				}
				$norms .= $respone["QualitativeTestAnswerAnalyzerTest_Answer"];
			}
		}
		return $norms;
	}

	/**
	 * Получение нормальных значений для качественного текста
	 * @param $AnalyzerTestRefValues_id
	 * @return false|string
	 */
	function getRefValuesForQualitativeTestJSON($AnalyzerTestRefValues_id)
	{
		$array = [];
		$query = "
			select qtaat.QualitativeTestAnswerAnalyzerTest_Answer as \"QualitativeTestAnswerAnalyzerTest_Answer\"
			from
				lis.v_QualitativeTestAnswerReferValue qtarv
				inner join lis.v_QualitativeTestAnswerAnalyzerTest qtaat on qtaat.QualitativeTestAnswerAnalyzerTest_id = qtarv.QualitativeTestAnswerAnalyzerTest_id
			where qtarv.AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, ["AnalyzerTestRefValues_id" => $AnalyzerTestRefValues_id]);
		if (is_object($result)) {
			$resp = $result->result_array();
			foreach ($resp as $respone) {
				$array[] = toUtf($respone["QualitativeTestAnswerAnalyzerTest_Answer"]);
			}
		}
		return json_encode($array);
	}

	/**
	 * Загрузка списка
	 * @param $filter
	 * @return array|bool
	 */
	function loadList($filter)
	{
		$query = "
            select
                atrv.AnalyzerTestRefValues_id as \"AnalyzerTestRefValues_id\",
                atrv.RefValues_id as \"RefValues_id\",
                rv.RefValues_Name as \"RefValues_Name\",
                at.AnalyzerTestType_id as \"AnalyzerTestType_id\",
                case when att.AnalyzerTestType_Code in ('1','3') then
                    case when rv.RefValues_LowerLimit is null and rv.RefValues_UpperLimit is null
                        then ''
                        else COALESCE(rv.RefValues_LowerLimit::varchar, '...')||' - '||COALESCE(rv.RefValues_UpperLimit::varchar, '...')
                    end
                else ''
                end as \"RefValues_Limit\",
                case when att.AnalyzerTestType_Code in ('1','3') then
                    case when rv.RefValues_BotCritValue is null and rv.RefValues_TopCritValue is null
                        then ''
                        else COALESCE(rv.RefValues_BotCritValue::varchar, '...')||' - '||COALESCE(rv.RefValues_TopCritValue::varchar, '...')
                    end
                else ''
                end as \"RefValues_CritValue\",
                u.Unit_Name as \"Unit_Name\",
                rv.RefValues_Description as \"RefValues_Description\",
                sex.Sex_Name as \"Sex_Name\",
                age.RefValues_Age as \"RefValues_Age\",
                phaza.HormonalPhaseType_Name as \"HormonalPhaseType_Name\",
                berem.RefValues_Pregnancy as \"RefValues_Pregnancy\",
                vrem.RefValues_TimeOfDay as \"RefValues_TimeOfDay\"
            from
                lis.v_AnalyzerTestRefValues atrv
                inner join lis.v_AnalyzerTest at on at.AnalyzerTest_id = atrv.AnalyzerTest_id
                inner join lis.v_AnalyzerTestType att on att.AnalyzerTestType_id = at.AnalyzerTestType_id
                inner join v_RefValues rv on rv.RefValues_id = atrv.RefValues_id
                left join lateral (
                    select s.Sex_Name
                    from
                        v_Sex s
                        inner join v_Limit l on l.Limit_Values = s.Sex_id
                        inner join v_LimitType lt on lt.LimitType_id = l.LimitType_id
                    where lt.LimitType_SysNick = 'Sex'
                      and l.RefValues_id = atrv.RefValues_id
                    limit 1
                ) as sex on true
                left join lateral (
                    select hpt.HormonalPhaseType_Name
                    from
                        v_HormonalPhaseType hpt
                        inner join v_Limit l on l.Limit_Values = hpt.HormonalPhaseType_id
                        inner join v_LimitType lt on lt.LimitType_id = l.LimitType_id
                    where lt.LimitType_SysNick = 'HormonalPhaseType'
                      and l.RefValues_id = atrv.RefValues_id
                    limit 1
                ) as phaza on true
                left join lateral (
                    select
                        case when Limit_ValuesFrom is null and Limit_ValuesTo is null
                            then ''
                            else COALESCE(Limit_ValuesFrom::varchar, '...')||' - '||COALESCE(Limit_ValuesTo::varchar, '...')||COALESCE(' ('||AU.AgeUnit_Name||')','')
                        end as RefValues_Age
                    from
                        v_Limit l
                        inner join v_LimitType lt on lt.LimitType_id = l.LimitType_id
                        left join v_AgeUnit au on au.AgeUnit_id = l.Limit_Values
                    where lt.LimitType_SysNick = 'AgeUnit'
                      and l.RefValues_id = atrv.RefValues_id
                    limit 1
                ) as age on true
                left join lateral (
                    select
                        case when Limit_ValuesFrom is null and Limit_ValuesTo is null
                            then ''
                            else COALESCE(Limit_ValuesFrom::varchar, '...')||' - '||COALESCE(Limit_ValuesTo::varchar, '...')||COALESCE(' ('||PUT.PregnancyUnitType_Name||')','')
                        end as RefValues_Pregnancy
                    from
                        v_Limit l
                        inner join v_LimitType lt on lt.LimitType_id = l.LimitType_id
                        left join v_PregnancyUnitType put on put.PregnancyUnitType_id = l.Limit_Values
                    where lt.LimitType_SysNick = 'PregnancyUnitType'
                      and l.RefValues_id = atrv.RefValues_id
                    limit 1
                ) as berem on true
                left join lateral (
                    select
                        case when Limit_ValuesFrom is null and Limit_ValuesTo is null
                            then ''
                            else COALESCE(Limit_ValuesFrom::varchar, '...')||' - '||COALESCE(Limit_ValuesTo::varchar, '...')
                        end as RefValues_TimeOfDay
                    from
                        v_Limit l
                        inner join v_LimitType lt on lt.LimitType_id = l.LimitType_id
                        left join v_PregnancyUnitType put on put.PregnancyUnitType_id = l.Limit_Values
                    where lt.LimitType_id = 7
                      and l.RefValues_id = atrv.RefValues_id
                    limit 1
                ) as vrem on true
                left join lis.v_Unit u on u.Unit_id = rv.Unit_id
            where atrv.AnalyzerTest_id = :AnalyzerTest_id
        ";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $filter);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result_array();
		foreach ($resp as &$respone) {
			// для качественных подгружаем нормальные значения через запятую
			if ($respone["AnalyzerTestType_id"] == 2) {
				$respone["RefValues_Limit"] = $this->getRefValuesForQualitativeTest($respone["AnalyzerTestRefValues_id"]);
			}
		}
		return $resp;
	}

	/**
	 * Сохранение
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function save($data)
	{
		/**@var CI_DB_result $result */
		// проверка на дубли
		$query = "
            select rv.RefValues_Name as \"RefValues_Name\"
            from
                lis.v_AnalyzerTestRefValues atrv
                inner join v_RefValues rv on rv.RefValues_id = atrv.RefValues_id
            where COALESCE(rv.Unit_id,0) = COALESCE(:Unit_id,0)
              and COALESCE(rv.RefValues_LowerLimit,'0') = COALESCE(:RefValues_LowerLimit,'0')
              and COALESCE(rv.RefValues_UpperLimit,'0') = COALESCE(:RefValues_UpperLimit,'0')
              and COALESCE(rv.RefValues_BotCritValue,'0') = COALESCE(:RefValues_BotCritValue,'0')
              and COALESCE(rv.RefValues_TopCritValue,'0') = COALESCE(:RefValues_TopCritValue,'0')
              and (atrv.AnalyzerTestRefValues_id <> :AnalyzerTestRefValues_id OR :AnalyzerTestRefValues_id IS NULL)
              and atrv.AnalyzerTest_id = :AnalyzerTest_id
        ";
		// + проверка по ограничениям
		if (!empty($data["LimitData"])) {
			$data["LimitData"] = toUtf($data["LimitData"]);
			$limitdata = json_decode($data["LimitData"], true);

			$k = 1;
			foreach ($limitdata as $limit) {
				if (!empty($limit["LimitType_id"])) {
					$limit["LimitType_IsCatalog"] = 1;
					$query_lt = "
                        select COALESCE(lt.LimitType_IsCatalog, 1) as \"LimitType_IsCatalog\"
                        from v_LimitType  lt
                        where lt.LimitType_id = :LimitType_id
                        limit 1
                    ";
					$result = $this->db->query($query_lt, $limit);
					if (is_object($result)) {
						$resp = $result->result("array");
						if (!empty($resp[0]["LimitType_IsCatalog"])) {
							$limit["LimitType_IsCatalog"] = $resp[0]["LimitType_IsCatalog"];
						}
					}
					if (!isset($limit["Limit_ValuesTo"]) || !is_numeric($limit["Limit_ValuesTo"])) {
						$limit["Limit_ValuesTo"] = null;
					}
					if (!isset($limit["Limit_ValuesFrom"]) || !is_numeric($limit["Limit_ValuesFrom"])) {
						$limit["Limit_ValuesFrom"] = null;
					}
					if (empty($limit["Limit_Values"])) {
						$limit["Limit_Values"] = null;
					}
					if (empty($limit["Limit_Unit"])) {
						$limit["Limit_Unit"] = null;
					}
					if ($limit["LimitType_IsCatalog"] == 1) {
						$limit["Limit_Values"] = $limit["Limit_Unit"];
					}
					$k++;
					$query .= "
                        and exists(
                            select Limit_id
                            from v_Limit
                            where RefValues_id = atrv.RefValues_id
                              and COALESCE(LimitType_id, 0) = COALESCE(:Limit{$k}_LimitType_id, 0)
                              and COALESCE(Limit_Values, 0) = COALESCE(:Limit{$k}_Limit_Values, 0)
                              and COALESCE(Limit_ValuesFrom, 0) = COALESCE(:Limit{$k}_Limit_ValuesFrom, 0)
                              and COALESCE(Limit_ValuesTo, 0) = COALESCE(:Limit{$k}_Limit_ValuesTo, 0)
                            limit 1
                        )
                    ";
					$data["Limit{$k}_LimitType_id"] = $limit["LimitType_id"];
					$data["Limit{$k}_Limit_Values"] = $limit["Limit_Values"];
					$data["Limit{$k}_Limit_ValuesFrom"] = $limit["Limit_ValuesFrom"];
					$data["Limit{$k}_Limit_ValuesTo"] = $limit["Limit_ValuesTo"];
				}
			}
		}
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result_array();
			if (count($resp) > 0) {
				throw new Exception("Такое референсное значение уже существует: \"{$resp[0]["RefValues_Name"]}\"");
			}
		}
		$data["RefValues_id"] = null;
		// если задан AnalyzerTestRefValues_id то ищем RefValues_id
		if (!empty($data["AnalyzerTestRefValues_id"])) {
			$query = "
                select RefValues_id as \"RefValues_id\"
                from lis.v_AnalyzerTestRefValues
                where AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id
                limit 1
            ";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result_array();
				if (!empty($resp[0]["RefValues_id"])) {
					$data["RefValues_id"] = $resp[0]["RefValues_id"];
				}
			}
		}
		$procedure = (!empty($data["RefValues_id"])) ? "p_RefValues_upd" : "p_RefValues_ins";
		$selectString = "
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\",
			RefValues_id as \"RefValues_id\"
		";
		$query = "
	        select {$selectString}
			from {$procedure} (
				RefValues_Name := :RefValues_Name,
                RefValues_Nick := :RefValues_Name,
                RefValuesType_id := null,
                Unit_id := :Unit_id,
                RefValues_LowerLimit := :RefValues_LowerLimit,
                RefValues_UpperLimit := :RefValues_UpperLimit,
                RefValues_BotCritValue := :RefValues_BotCritValue,
                RefValues_TopCritValue := :RefValues_TopCritValue,
                RefValues_Description := :RefValues_Description,
                pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result_array();
			if (!empty($resp[0]["RefValues_id"])) {
				$data["RefValues_id"] = $resp[0]["RefValues_id"];
			}
		}
		if (empty($data["RefValues_id"])) {
			throw new Exception("Ошибка сохранения референсного значения");
		}
		if (!empty($data["LimitData"])) {
			$data["LimitData"] = toUtf($data["LimitData"]);
			$limitdata = json_decode($data["LimitData"], true);
			foreach ($limitdata as $limit) {
				$limit["Limit_id"] = null;
				$limit["LimitType_IsCatalog"] = 1;
				$limit["RefValues_id"] = $data["RefValues_id"];
				$limit["pmUser_id"] = $data["pmUser_id"];
				if (!empty($limit["LimitType_id"])) {
					// 1. ищем запись для соответвующего LimitType_id и RefValues_id
					$query = "
                        select
                            l.Limit_id as \"Limit_id\",
                            COALESCE(lt.LimitType_IsCatalog, 1) as \"LimitType_IsCatalog\"
                        from
                            v_LimitType  lt
                            left join v_Limit  l on l.LimitType_id = lt.LimitType_id and l.RefValues_id = :RefValues_id
                        where lt.LimitType_id = :LimitType_id
                        limit 1
                    ";
					$result = $this->db->query($query, $limit);
					if (is_object($result)) {
						$resp = $result->result_array();
						if (!empty($resp[0]["Limit_id"])) {
							$limit["Limit_id"] = $resp[0]["Limit_id"];
						}
						if (!empty($resp[0]["LimitType_IsCatalog"])) {
							$limit["LimitType_IsCatalog"] = $resp[0]["LimitType_IsCatalog"];
						}
					}
					// 2. сохраняем
					$procedure = (!empty($limit["Limit_id"])) ? "p_Limitvalues_upd" : "p_Limitvalues_ins";
					$limit["Limit_IsActiv"] = ($limit["Limit_IsActiv"] === "true" || $limit["Limit_IsActiv"] == 1) ? 2 : 1;
					if (!isset($limit["Limit_ValuesTo"]) || !is_numeric($limit["Limit_ValuesTo"])) {
						$limit["Limit_ValuesTo"] = null;
					}
					if (!isset($limit["Limit_ValuesFrom"]) || !is_numeric($limit["Limit_ValuesFrom"])) {
						$limit["Limit_ValuesFrom"] = null;
					}
					if (empty($limit["Limit_Values"])) {
						$limit["Limit_Values"] = null;
					}
					if (empty($limit["Limit_Unit"])) {
						$limit["Limit_Unit"] = null;
					}
					if ($limit["LimitType_IsCatalog"] == 1) {
						$limit["Limit_Values"] = $limit["Limit_Unit"];
					}
					$selectString = "
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\",
						Limit_id as \"Limit_id\"
					";
					$query = "
				        select {$selectString}
						from {$procedure} (
							Limit_id := :Limit_id,
			                LimitType_id := :LimitType_id,
			                Limit_Values := :Limit_Values,
			                RefValues_id := :RefValues_id,
			                Limit_ValuesFrom := :Limit_ValuesFrom,
			                Limit_ValuesTo := :Limit_ValuesTo,
			                Limit_IsActiv := :Limit_IsActiv,
			                pmUser_id := :pmUser_id
						)
					";
					$this->db->query($query, $limit);
				}
			}
		}
		$procedure = (!empty($data["AnalyzerTestRefValues_id"])) ? "p_AnalyzerTestRefValues_upd" : "p_AnalyzerTestRefValues_ins";
		$selectString = "
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\",
			AnalyzerTestRefValues_id as \"AnalyzerTestRefValues_id\"
		";
		$query = "
	        select {$selectString}
			from lis.{$procedure} (
				AnalyzerTestRefValues_id := :AnalyzerTestRefValues_id,
                AnalyzerTest_id := :AnalyzerTest_id,
                RefValues_id := :RefValues_id,
                pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($query, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Удаление
	 * @param $data
	 * @return array|bool
	 */
	function delete($data)
	{
		// сначала удаляем связанные записи качественных референсных значений
		$query = "
            select QualitativeTestAnswerReferValue_id as \"QualitativeTestAnswerReferValue_id\"
            from lis.v_QualitativeTestAnswerReferValue
            where AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id
        ";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result_array();
			foreach ($resp as $respone) {
				$query = "
		            select
		                Error_Code as \"Error_Code\",
			            Error_Message as \"Error_Msg\"
				    from lis.p_QualitativeTestAnswerReferValue_del(QualitativeTestAnswerReferValue_id := :QualitativeTestAnswerReferValue_id)
				";
				$this->db->query($query, ["QualitativeTestAnswerReferValue_id" => $respone["QualitativeTestAnswerReferValue_id"]]);
			}
		}
		$query = "
			select
				Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from lis.p_AnalyzerTestRefValues_del(AnalyzerTestRefValues_id := :AnalyzerTestRefValues_id)
		";
		$result = $this->db->query($query, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение списка референсных значений
	 * @param $data
	 * @return array
	 */
	function loadRefValuesList($data)
	{
		/**@var CI_DB_result $result */
		if (!empty($data["UslugaTest_id"])) {
			// получаем необходимые данные из услуги
			$query = "
                select
                    elr.MedService_id as \"MedService_id\",
                    euinp.UslugaComplex_id as \"UslugaComplexTarget_id\",
                    ut.UslugaComplex_id as \"UslugaComplexTest_id\",
                    els.Analyzer_id as \"Analyzer_id\",
                    elr.Person_id as \"Person_id\",
                    to_char(els.EvnLabSample_setDT, 'yyyy-mm-dd hh:mi:ss') as \"EvnLabSample_setDT\"
                from
                    v_UslugaTest ut
                    left join v_EvnLabSample els on els.EvnLabSample_id = ut.EvnLabSample_id
                    left join v_EvnLabRequest elr on elr.EvnLabRequest_id = els.EvnLabRequest_id
                    inner join v_EvnUslugaPar euinp  on euinp.EvnUslugaPar_id = ut.UslugaTest_pid -- корневая услуга
                where ut.UslugaTest_id = :UslugaTest_id
                limit 1
            ";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result_array();
				if (count($resp) > 0) {
					$data["MedService_id"] = $resp[0]["MedService_id"];
					$data["UslugaComplexTarget_id"] = $resp[0]["UslugaComplexTarget_id"];
					$data["UslugaComplexTest_id"] = $resp[0]["UslugaComplexTest_id"];
					$data["Analyzer_id"] = $resp[0]["Analyzer_id"];
					$data["EvnLabSample_setDT"] = $resp[0]["EvnLabSample_setDT"];
					$data["Person_id"] = $resp[0]["Person_id"];
				}
			}
		}
		$resp = [];
		$filter = "";
		$outer = "";
		$orderby = "";
		if (!empty($data["Analyzer_id"])) {
			// пусть с текущего анализатора выводятся первыми.
			$orderby .= "case when at.Analyzer_id = :Analyzer_id then 0 else 1 end, ";
		}
		if (!empty($data["OrderByLimit"])) {
			$outer = "
                LEFT JOIN LATERAL(
                    select count(*) as cnt
                    from
                        v_Limit l
                        inner join v_LimitType lt  on lt.LimitType_id = l.LimitType_id
                    where l.RefValues_id = rv.RefValues_id
                      and (
                        (l.Limit_Values IS NOT NULL AND lt.LimitType_IsCatalog = 2) OR
                        ((l.Limit_ValuesTo IS NOT NULL OR l.Limit_ValuesFrom IS NOT NULL) AND lt.LimitType_IsCatalog = 1)
                      )
                ) as tbLIMIT on true
            ";
			$orderby .= "tbLIMIT.cnt desc, ";
		}
		// фильтрация по исследованию, которое может выполняться на анализаторе
		$filter .= " and ucms_at.UslugaComplex_id = :UslugaComplexTest_id";
		if ($data["UslugaComplexTarget_id"] != $data["UslugaComplexTest_id"]) {
			$filter .= "
				and exists(
					select AnalyzerTest_id
					from
						lis.v_AnalyzerTest at_parent
						inner join v_UslugaComplexMedService ucms_at_parent on ucms_at_parent.UslugaComplexMedService_id = at_parent.UslugaComplexMedService_id
					where at_parent.AnalyzerTest_id = at.AnalyzerTest_pid
					  and ucms_at_parent.UslugaComplex_id = :UslugaComplexTarget_id
				)
			";
		}
		$query = "
            select
                at.AnalyzerTestType_id as \"AnalyzerTestType_id\",
                atrv.AnalyzerTestRefValues_id as \"AnalyzerTestRefValues_id\",
                rv.RefValues_id as \"RefValues_id\",
                rv.Unit_id as \"Unit_id\",
                rv.RefValues_Name || COALESCE(' (' || a.Analyzer_Name || ')','') as \"RefValues_Name\",
                '' as \"UslugaTest_ResultQualitativeNorms\",
                case when att.AnalyzerTestType_Code in ('1','3') then rv.RefValues_LowerLimit::varchar else '' end as \"UslugaTest_ResultLower\",
                case when att.AnalyzerTestType_Code in ('1','3') then rv.RefValues_UpperLimit::varchar else '' end as \"UslugaTest_ResultUpper\",
                case when att.AnalyzerTestType_Code in ('1','3') then rv.RefValues_BotCritValue::varchar else '' end as \"UslugaTest_ResultLowerCrit\",
                case when att.AnalyzerTestType_Code in ('1','3') then rv.RefValues_TopCritValue::varchar else '' end as \"UslugaTest_ResultUpperCrit\",
                u.Unit_Name as \"UslugaTest_ResultUnit\",
                rv.RefValues_Description as \"UslugaTest_Comment\"
            from
                lis.v_AnalyzerTest at
                inner join v_UslugaComplexMedService ucms_at  on ucms_at.UslugaComplexMedService_id = at.UslugaComplexMedService_id
                inner join lis.v_AnalyzerTestType att  on att.AnalyzerTestType_id = at.AnalyzerTestType_id
                inner join lis.v_Analyzer a  on a.Analyzer_id = at.Analyzer_id
                inner join lis.v_AnalyzerTestRefValues atrv  on atrv.AnalyzerTest_id = at.AnalyzerTest_id
                inner join v_RefValues rv  on rv.RefValues_id = atrv.RefValues_id
                left join lis.v_Unit u  on u.Unit_id = rv.Unit_id
                {$outer}
            where a.MedService_id = :MedService_id
              {$filter}
            order by
                {$orderby}
                rv.RefValues_Name
        ";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result_array();
			foreach ($resp as &$respone) {
				if ($respone["AnalyzerTestType_id"] == 2) {
					$respone["UslugaTest_ResultQualitativeNorms"] = $this->getRefValuesForQualitativeTestJSON($respone["AnalyzerTestRefValues_id"]);
				}
			}
		}
		return $resp;
	}
}