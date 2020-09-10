<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Соответствия конкретных ответов конкретному качественному тесту
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010-2011 Swan Ltd.
 * @author       gabdushev
 * @version
 */
class QualitativeTestAnswerAnalyzerTest_model extends SwPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Загрузка
	 */
	function load($data) {
		$q = "
			select
				QualitativeTestAnswerAnalyzerTest_id as \"QualitativeTestAnswerAnalyzerTest_id\",
				QualitativeTestAnswerAnalyzerTest_Answer as \"QualitativeTestAnswerAnalyzerTest_Answer\",
				QualitativeTestAnswerAnalyzerTest_SortCode as \"QualitativeTestAnswerAnalyzerTest_SortCode\",
				AnalyzerTest_id as \"AnalyzerTest_id\"
			from
				lis.v_QualitativeTestAnswerAnalyzerTest
			where
				QualitativeTestAnswerAnalyzerTest_id = :QualitativeTestAnswerAnalyzerTest_id
		";
		$r = $this->db->query($q, array('QualitativeTestAnswerAnalyzerTest_id' => $data['QualitativeTestAnswerAnalyzerTest_id']));
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
	function loadList($filter) {
		$where = array();
		$p = array();

		if (!empty($filter['UslugaTest_id'])) {
			// получаем необходимые данные из услуги
			$query = "
				select
					els.MedService_id as \"MedService_id\",
					eupp.UslugaComplex_id as \"UslugaComplexTarget_id\",
					ut.UslugaComplex_id as \"UslugaComplexTest_id\",
					els.Analyzer_id as \"Analyzer_id\",
					atrv_an.Analyzer_id as \"RefValuesAnalyzer_id\"
				from
					v_UslugaTest ut
					left join v_EvnUslugaPar eupp on eupp.EvnUslugaPar_id = ut.UslugaTest_pid
					left join v_EvnLabSample els on els.EvnLabSample_id = ut.EvnLabSample_id
					left join v_EvnLabRequest elr on elr.EvnLabRequest_id = els.EvnLabRequest_id
					left join lateral(
						select
							at.Analyzer_id
						from
							lis.v_AnalyzerTest at
							inner join v_UslugaComplexMedService ucms on ucms.UslugaComplexMedService_id = at.UslugaComplexMedService_id
							inner join lis.v_AnalyzerTestRefValues atrv on atrv.AnalyzerTest_id = at.AnalyzerTest_id
						where
							ucms.UslugaComplex_id = ut.UslugaComplex_id
							and atrv.RefValues_id = ut.RefValues_id
						limit 1	
					) atrv_an on true
				where
					ut.UslugaTest_id = :UslugaTest_id
				limit 1
			";

			$result = $this->db->query($query, array(
				'UslugaTest_id' => $filter['UslugaTest_id']
			));
			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$filter['MedService_id'] = $resp[0]['MedService_id'];
					$filter['UslugaComplexTarget_id'] = $resp[0]['UslugaComplexTarget_id'];
					$filter['UslugaComplexTest_id'] = $resp[0]['UslugaComplexTest_id'];
					$filter['Analyzer_id'] = $resp[0]['Analyzer_id'];
					if (!empty($resp[0]['RefValuesAnalyzer_id'])) {
						// если референсные значения выбраны с другого анализатора, то и единицы измерения с него должны быть
						$filter['Analyzer_id'] = $resp[0]['RefValuesAnalyzer_id'];
					}

					$filter_at = "";
					if (!empty($filter['Analyzer_id'])) {
						$filter_at .= " and a.Analyzer_id = :Analyzer_id";
					}

					// фильтрация по исследованию, которое может выполняться на анализаторе
					$filter_at .= ' and ucms.UslugaComplex_id = :UslugaComplexTest_id';
					$filter_at .= ' and ucms_parent.UslugaComplex_id = :UslugaComplexTarget_id';

					// фильтры по услуге
					$where[] = "v_QualitativeTestAnswerAnalyzerTest.AnalyzerTest_id IN (
						select
							at.AnalyzerTest_id
						from
							lis.v_AnalyzerTest at
							inner join v_UslugaComplexMedService ucms on ucms.UslugaComplexMedService_id = at.UslugaComplexMedService_id
							inner join v_UslugaComplexMedService ucms_parent on ucms_parent.UslugaComplexMedService_id = coalesce(ucms.UslugaComplexMedService_pid, ucms.UslugaComplexMedService_id)
							inner join lis.v_Analyzer a on a.Analyzer_id = at.Analyzer_id
						where
							a.MedService_id = :MedService_id
							and at.AnalyzerTestType_id = 2
							{$filter_at}
					)";
				}
			}
		}
		
		if (!empty($filter['AnalyzerTestRefValues_id'])) {
			$filter['AnalyzerTest_id'] = $this->getFirstResultFromQuery("
				SELECT
					AnalyzerTest_id
				FROM
					lis.AnalyzerTestRefValues
				where
					AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id
				limit 1	
			", array('AnalyzerTestRefValues_id' => $filter['AnalyzerTestRefValues_id']));
		}
		if (!empty($filter['QualitativeTestAnswerAnalyzerTest_id'])) {
			$where[] = 'v_QualitativeTestAnswerAnalyzerTest.QualitativeTestAnswerAnalyzerTest_id = :QualitativeTestAnswerAnalyzerTest_id';
			$p['QualitativeTestAnswerAnalyzerTest_id'] = $filter['QualitativeTestAnswerAnalyzerTest_id'];
		}
		if (!empty($filter['QualitativeTestAnswerAnalyzerTest_Answer'])) {
			$where[] = 'v_QualitativeTestAnswerAnalyzerTest.QualitativeTestAnswerAnalyzerTest_Answer = :QualitativeTestAnswerAnalyzerTest_Answer';
			$p['QualitativeTestAnswerAnalyzerTest_Answer'] = $filter['QualitativeTestAnswerAnalyzerTest_Answer'];
		}
		if (!empty($filter['AnalyzerTest_id'])) {
			$where[] = 'v_QualitativeTestAnswerAnalyzerTest.AnalyzerTest_id = :AnalyzerTest_id';
			$p['AnalyzerTest_id'] = $filter['AnalyzerTest_id'];
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		} else {
			return false;
		}
		$q = "
			SELECT
				v_QualitativeTestAnswerAnalyzerTest.QualitativeTestAnswerAnalyzerTest_id as \"QualitativeTestAnswerAnalyzerTest_id\",
				v_QualitativeTestAnswerAnalyzerTest.QualitativeTestAnswerAnalyzerTest_Answer as \"QualitativeTestAnswerAnalyzerTest_Answer\",
				v_QualitativeTestAnswerAnalyzerTest.QualitativeTestAnswerAnalyzerTest_SortCode as \"QualitativeTestAnswerAnalyzerTest_SortCode\",
				v_QualitativeTestAnswerAnalyzerTest.AnalyzerTest_id as \"AnalyzerTest_id\",
				AnalyzerTest_id_ref.AnalyzerTest_Name as \"AnalyzerTest_id_Name\"
			FROM
				lis.v_QualitativeTestAnswerAnalyzerTest
				LEFT JOIN lis.v_AnalyzerTest AnalyzerTest_id_ref ON AnalyzerTest_id_ref.AnalyzerTest_id = v_QualitativeTestAnswerAnalyzerTest.AnalyzerTest_id
				$where_clause
			ORDER BY 
				CASE WHEN QualitativeTestAnswerAnalyzerTest_SortCode is not null THEN 0 
					ELSE 1 
				END,
				QualitativeTestAnswerAnalyzerTest_SortCode,
				QualitativeTestAnswerAnalyzerTest_Answer
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
	 * Сохранение
	 */
	function save($data) {
		$procedure = 'p_QualitativeTestAnswerAnalyzerTest_ins';
		if ( !empty($data['QualitativeTestAnswerAnalyzerTest_id']) ) {
			$procedure = 'p_QualitativeTestAnswerAnalyzerTest_upd';
		}
		
		// проверка на дубли
		$query = "
			select
				QualitativeTestAnswerAnalyzerTest_id as \"QualitativeTestAnswerAnalyzerTest_id\"
			from
				lis.v_QualitativeTestAnswerAnalyzerTest
			where
				AnalyzerTest_id = :AnalyzerTest_id
				and QualitativeTestAnswerAnalyzerTest_Answer = :QualitativeTestAnswerAnalyzerTest_Answer
				and (QualitativeTestAnswerAnalyzerTest_id <> :QualitativeTestAnswerAnalyzerTest_id OR :QualitativeTestAnswerAnalyzerTest_id IS NULL)
			limit 1	
		";
		
		$result = $this->db->query($query, array(
			'QualitativeTestAnswerAnalyzerTest_id' => $data['QualitativeTestAnswerAnalyzerTest_id'],
			'QualitativeTestAnswerAnalyzerTest_Answer' => $data['QualitativeTestAnswerAnalyzerTest_Answer'],
			'AnalyzerTest_id' => $data['AnalyzerTest_id']
		));
		
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return array('Error_Msg' => 'Указанный ответ уже добавлен к тесту');
			}
		}

		$queryAddSortCode = '';

		if(!empty($data['QualitativeTestAnswerAnalyzerTest_SortCode'])) {
			$queryAddSortCode = 'QualitativeTestAnswerAnalyzerTest_SortCode := :QualitativeTestAnswerAnalyzerTest_SortCode,';
		}

		$q = "
		    select 
		        QualitativeTestAnswerAnalyzerTest_id as \"QualitativeTestAnswerAnalyzerTest_id\",
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
			from lis.{$procedure} (
				QualitativeTestAnswerAnalyzerTest_id := :QualitativeTestAnswerAnalyzerTest_id,
				QualitativeTestAnswerAnalyzerTest_Answer := :QualitativeTestAnswerAnalyzerTest_Answer,
				{$queryAddSortCode}
				AnalyzerTest_id := :AnalyzerTest_id,
				QualitativeTestAnswer_id := NULL,
				pmUser_id := :pmUser_id
			)
		";
		$p = array(
			'QualitativeTestAnswerAnalyzerTest_id' => $data['QualitativeTestAnswerAnalyzerTest_id'],
			'QualitativeTestAnswerAnalyzerTest_Answer' => $data['QualitativeTestAnswerAnalyzerTest_Answer'],
			'AnalyzerTest_id' => $data['AnalyzerTest_id'],
			'pmUser_id' => $data['pmUser_id'],
		);

		if(!empty($data['QualitativeTestAnswerAnalyzerTest_SortCode'])) {
			$p['QualitativeTestAnswerAnalyzerTest_SortCode'] = $data['QualitativeTestAnswerAnalyzerTest_SortCode'];
		}

		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
			return $r->result('array');
		}

		return false;
	}

	/**
	 * Удаление
	 */
	function delete($data) {
		// проверка, а не используется ли вариант ответа в референсных значениях данного теста
		$query = "
			select
				qtaat.QualitativeTestAnswerAnalyzerTest_id as \"QualitativeTestAnswerAnalyzerTest_id\"
			from
				lis.v_QualitativeTestAnswerAnalyzerTest qtaat
				inner join lis.v_AnalyzerTestRefValues atrv on atrv.AnalyzerTest_id = qtaat.AnalyzerTest_id
				inner join lis.v_QualitativeTestAnswerReferValue qtarv on qtarv.AnalyzerTestRefValues_id = atrv.AnalyzerTestRefValues_id
					and qtarv.QualitativeTestAnswerAnalyzerTest_id = qtaat.QualitativeTestAnswerAnalyzerTest_id
			where
				qtaat.QualitativeTestAnswerAnalyzerTest_id = :QualitativeTestAnswerAnalyzerTest_id
		";
		
		$result = $this->db->query($query, array(
			'QualitativeTestAnswerAnalyzerTest_id' => $data['QualitativeTestAnswerAnalyzerTest_id']
		));
		
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return array('Error_Msg' => 'Нельзя удалить вариант ответа, т.к. он используется в референсных значениях');
			}
		}

		$q = "
		    select 
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
			from lis.p_QualitativeTestAnswerAnalyzerTest_del(
				QualitativeTestAnswerAnalyzerTest_id := :QualitativeTestAnswerAnalyzerTest_id
			)
		";
		$r = $this->db->query($q, array(
			'QualitativeTestAnswerAnalyzerTest_id' => $data['QualitativeTestAnswerAnalyzerTest_id']
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}
}