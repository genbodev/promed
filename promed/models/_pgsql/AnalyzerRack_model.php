<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Штативы
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010-2011 Swan Ltd.
 * @author       gabdushev
 * @version
 */
class AnalyzerRack_model extends SwPgModel {
	private $AnalyzerRack_id;//AnalyzerRack_id
	private $AnalyzerModel_id;//Модель анализатора
	private $AnalyzerRack_DimensionX;//Размерность по Х
	private $AnalyzerRack_DimensionY;//Размерность по Y
	private $AnalyzerRack_IsDefault;//По умолчанию
	private $AnalyzerRack_Deleted;//AnalyzerRack_Deleted
	private $pmUser_id;//Идентификатор пользователя системы Промед

	/**
	 * Comment
	 */
	public function getAnalyzerRack_id() { return $this->AnalyzerRack_id;}
	/**
	 * Comment
	 */
	public function setAnalyzerRack_id($value) { $this->AnalyzerRack_id = $value; }

	/**
	 * Comment
	 */
	public function getAnalyzerModel_id() { return $this->AnalyzerModel_id;}
	/**
	 * Comment
	 */
	public function setAnalyzerModel_id($value) { $this->AnalyzerModel_id = $value; }

	/**
	 * Comment
	 */
	public function getAnalyzerRack_DimensionX() { return $this->AnalyzerRack_DimensionX;}
	/**
	 * Comment
	 */
	public function setAnalyzerRack_DimensionX($value) { $this->AnalyzerRack_DimensionX = $value; }

	/**
	 * Comment
	 */
	public function getAnalyzerRack_DimensionY() { return $this->AnalyzerRack_DimensionY;}
	/**
	 * Comment
	 */
	public function setAnalyzerRack_DimensionY($value) { $this->AnalyzerRack_DimensionY = $value; }

	/**
	 * Comment
	 */
	public function getAnalyzerRack_IsDefault() { return $this->AnalyzerRack_IsDefault;}
	/**
	 * Comment
	 */
	public function setAnalyzerRack_IsDefault($value) { $this->AnalyzerRack_IsDefault = $value; }

	/**
	 * Comment
	 */
	public function getAnalyzerRack_Deleted() { return $this->AnalyzerRack_Deleted;}
	/**
	 * Comment
	 */
	public function setAnalyzerRack_Deleted($value) { $this->AnalyzerRack_Deleted = $value; }

	/**
	 * Comment
	 */
	public function getpmUser_id() { return $this->pmUser_id;}
	/**
	 * Comment
	 */
	public function setpmUser_id($value) { $this->pmUser_id = $value; }

	/**
	 * Comment
	 */
	function __construct(){
		if (isset($_SESSION['pmuser_id'])) {
			$this->setpmUser_id($_SESSION['pmuser_id']);
		} else {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
		}
	}

	/**
	 * Comment
	 */
	function load() {
		$q = "
			select
				AnalyzerRack_id as \"AnalyzerRack_id\",
				AnalyzerModel_id as \"AnalyzerModel_id\",
				AnalyzerRack_DimensionX as \"AnalyzerRack_DimensionX\",
				AnalyzerRack_DimensionY as \"AnalyzerRack_DimensionY\",
				AnalyzerRack_IsDefault as \"AnalyzerRack_IsDefault\",
				AnalyzerRack_Deleted as \"AnalyzerRack_Deleted\"
			from
				lis.v_AnalyzerRack
			where
				AnalyzerRack_id = :AnalyzerRack_id
		";
		$r = $this->db->query($q, array('AnalyzerRack_id' => $this->AnalyzerRack_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->AnalyzerRack_id = $r[0]['AnalyzerRack_id'];
				$this->AnalyzerModel_id = $r[0]['AnalyzerModel_id'];
				$this->AnalyzerRack_DimensionX = $r[0]['AnalyzerRack_DimensionX'];
				$this->AnalyzerRack_DimensionY = $r[0]['AnalyzerRack_DimensionY'];
				$this->AnalyzerRack_IsDefault = $r[0]['AnalyzerRack_IsDefault'];
				$this->AnalyzerRack_Deleted = $r[0]['AnalyzerRack_Deleted'];
				return $r;
			} else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Comment
	 */
	function loadList($filter) {
		$where = array();
		$p = array();
		if (isset($filter['AnalyzerRack_id']) && $filter['AnalyzerRack_id']) {
			$where[] = 'ar.AnalyzerRack_id = :AnalyzerRack_id';
			$p['AnalyzerRack_id'] = $filter['AnalyzerRack_id'];
		}
		if (isset($filter['AnalyzerModel_id']) && $filter['AnalyzerModel_id']) {
			$where[] = 'ar.AnalyzerModel_id = :AnalyzerModel_id';
			$p['AnalyzerModel_id'] = $filter['AnalyzerModel_id'];
		}
		if (isset($filter['AnalyzerRack_DimensionX']) && $filter['AnalyzerRack_DimensionX']) {
			$where[] = 'ar.AnalyzerRack_DimensionX = :AnalyzerRack_DimensionX';
			$p['AnalyzerRack_DimensionX'] = $filter['AnalyzerRack_DimensionX'];
		}
		if (isset($filter['AnalyzerRack_DimensionY']) && $filter['AnalyzerRack_DimensionY']) {
			$where[] = 'ar.AnalyzerRack_DimensionY = :AnalyzerRack_DimensionY';
			$p['AnalyzerRack_DimensionY'] = $filter['AnalyzerRack_DimensionY'];
		}
		if (isset($filter['AnalyzerRack_IsDefault']) && $filter['AnalyzerRack_IsDefault']) {
			$where[] = 'ar.AnalyzerRack_IsDefault = :AnalyzerRack_IsDefault';
			$p['AnalyzerRack_IsDefault'] = $filter['AnalyzerRack_IsDefault'];
		}
		if (isset($filter['AnalyzerRack_Deleted']) && $filter['AnalyzerRack_Deleted']) {
			$where[] = 'ar.AnalyzerRack_Deleted = :AnalyzerRack_Deleted';
			$p['AnalyzerRack_Deleted'] = $filter['AnalyzerRack_Deleted'];
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		} else {
			return [];
		}
		$q = "
			SELECT
				ar.AnalyzerRack_id as \"AnalyzerRack_id\",
				ar.AnalyzerModel_id as \"AnalyzerModel_id\",
				ar.AnalyzerRack_DimensionX as \"AnalyzerRack_DimensionX\",
				ar.AnalyzerRack_DimensionY as \"AnalyzerRack_DimensionY\",
				ar.AnalyzerRack_IsDefault as \"AnalyzerRack_IsDefault\",
				AnalyzerModel_id_ref.AnalyzerModel_Name as \"AnalyzerModel_id_Name\",
				AnalyzerRack_IsDefault_ref.YesNo_Name as \"AnalyzerRack_IsDefault_Name\",
				to_char(floor(ar.AnalyzerRack_DimensionX), '99999') || ' x ' || to_char(floor(ar.AnalyzerRack_DimensionY), '99999') as \"AnalyzerRack_Name\"
			FROM
				lis.v_AnalyzerRack ar
				LEFT JOIN lis.v_AnalyzerModel AnalyzerModel_id_ref ON AnalyzerModel_id_ref.AnalyzerModel_id = ar.AnalyzerModel_id
				LEFT JOIN dbo.v_YesNo AnalyzerRack_IsDefault_ref ON AnalyzerRack_IsDefault_ref.YesNo_id = ar.AnalyzerRack_IsDefault
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
	 * Comment
	 */
	function save() {
		$procedure = 'p_AnalyzerRack_ins';
		if ( $this->AnalyzerRack_id > 0 ) {
			$procedure = 'p_AnalyzerRack_upd';
		}
		$q = "
			select
				AnalyzerRack_id as \"AnalyzerRack_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from lis.{$procedure}(
				AnalyzerRack_id := :AnalyzerRack_id,
				AnalyzerModel_id := :AnalyzerModel_id,
				AnalyzerRack_DimensionX := :AnalyzerRack_DimensionX,
				AnalyzerRack_DimensionY := :AnalyzerRack_DimensionY,
				AnalyzerRack_IsDefault := :AnalyzerRack_IsDefault,
				pmUser_id := :pmUser_id
			)
		";
		$p = array(
			'AnalyzerRack_id' => $this->AnalyzerRack_id,
			'AnalyzerModel_id' => $this->AnalyzerModel_id,
			'AnalyzerRack_DimensionX' => $this->AnalyzerRack_DimensionX,
			'AnalyzerRack_DimensionY' => $this->AnalyzerRack_DimensionY,
			'AnalyzerRack_IsDefault' => $this->AnalyzerRack_IsDefault,
			'AnalyzerRack_Deleted' => $this->AnalyzerRack_Deleted,
			'pmUser_id' => $this->pmUser_id,
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
			$result = $r->result('array');
			$this->AnalyzerRack_id = $result[0]['AnalyzerRack_id'];
		}
		else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Comment
	 */
	function delete() {
		$q = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from lis.p_AnalyzerRack_del(
				AnalyzerRack_id := :AnalyzerRack_id
			)	
		";
		$r = $this->db->query($q, array(
			'AnalyzerRack_id' => $this->AnalyzerRack_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}
}