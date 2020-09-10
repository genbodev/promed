<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Соответствия конкретных ответов конкретному референсному значению качественного теста
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010-2013 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version
 */
class QualitativeTestAnswerReferValue_model extends swModel {
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
				QualitativeTestAnswerReferValue_id as \"QualitativeTestAnswerReferValue_id\",
				AnalyzerTestRefValues_id as \"AnalyzerTestRefValues_id\",
				QualitativeTestAnswerAnalyzerTest_id as \"QualitativeTestAnswerAnalyzerTest_id\"
			from
				lis.v_QualitativeTestAnswerReferValue with(nolock)
			where
				QualitativeTestAnswerReferValue_id = :QualitativeTestAnswerReferValue_id
		";
		$r = $this->db->query($q, array('QualitativeTestAnswerReferValue_id' => $data['QualitativeTestAnswerReferValue_id']));
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
		if (!empty($filter['QualitativeTestAnswerReferValue_id'])) {
			$where[] = 'qtarv.QualitativeTestAnswerReferValue_id = :QualitativeTestAnswerReferValue_id';
			$p['QualitativeTestAnswerReferValue_id'] = $filter['QualitativeTestAnswerReferValue_id'];
		}
		if (!empty($filter['AnalyzerTestRefValues_id'])) {
			$where[] = 'qtarv.AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id';
			$p['AnalyzerTestRefValues_id'] = $filter['AnalyzerTestRefValues_id'];
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		} else {
			return false;
		}
		$q = "
			SELECT
				qtarv.QualitativeTestAnswerReferValue_id as \"QualitativeTestAnswerReferValue_id\",
				qtarv.AnalyzerTestRefValues_id as \"AnalyzerTestRefValues_id\",
				qtarv.QualitativeTestAnswerAnalyzerTest_id as \"QualitativeTestAnswerAnalyzerTest_id\",
				qtaat.QualitativeTestAnswerAnalyzerTest_Answer as \"QualitativeTestAnswerAnalyzerTest_Answer\"
			FROM
				lis.v_QualitativeTestAnswerReferValue qtarv with(nolock)
				LEFT JOIN lis.v_QualitativeTestAnswerAnalyzerTest qtaat with(nolock) ON qtaat.QualitativeTestAnswerAnalyzerTest_id = qtarv.QualitativeTestAnswerAnalyzerTest_id
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
	 * Сохранение
	 */
	function save($data) {
		$procedure = 'p_QualitativeTestAnswerReferValue_ins';
		if ( !empty($data['QualitativeTestAnswerReferValue_id']) ) {
			$procedure = 'p_QualitativeTestAnswerReferValue_upd';
		}
		
		// проверка на дубли
		$query = "
			select top 1
				QualitativeTestAnswerReferValue_id as \"QualitativeTestAnswerReferValue_id\"
			from
				lis.v_QualitativeTestAnswerReferValue with(nolock)
			where
				AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id
				and QualitativeTestAnswerAnalyzerTest_id = :QualitativeTestAnswerAnalyzerTest_id
				and (QualitativeTestAnswerReferValue_id <> :QualitativeTestAnswerReferValue_id OR :QualitativeTestAnswerReferValue_id IS NULL)
		";
		
		$result = $this->db->query($query, array(
			'QualitativeTestAnswerReferValue_id' => $data['QualitativeTestAnswerReferValue_id'],
			'AnalyzerTestRefValues_id' => $data['AnalyzerTestRefValues_id'],
			'QualitativeTestAnswerAnalyzerTest_id' => $data['QualitativeTestAnswerAnalyzerTest_id']
		));
		
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return array('Error_Msg' => 'Указанное значение уже добавлено к референсному значению');
			}
		}

		$q = "
			declare
				@QualitativeTestAnswerReferValue_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @QualitativeTestAnswerReferValue_id = :QualitativeTestAnswerReferValue_id;
			exec lis." . $procedure . "
				@QualitativeTestAnswerReferValue_id = @QualitativeTestAnswerReferValue_id output,
				@AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id,
				@QualitativeTestAnswerAnalyzerTest_id = :QualitativeTestAnswerAnalyzerTest_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @QualitativeTestAnswerReferValue_id as QualitativeTestAnswerReferValue_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'QualitativeTestAnswerReferValue_id' => $data['QualitativeTestAnswerReferValue_id'],
			'AnalyzerTestRefValues_id' => $data['AnalyzerTestRefValues_id'],
			'QualitativeTestAnswerAnalyzerTest_id' => $data['QualitativeTestAnswerAnalyzerTest_id'],
			'pmUser_id' => $data['pmUser_id'],
		);
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
		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec lis.p_QualitativeTestAnswerReferValue_del
				@QualitativeTestAnswerReferValue_id = :QualitativeTestAnswerReferValue_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'QualitativeTestAnswerReferValue_id' => $data['QualitativeTestAnswerReferValue_id']
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}
}