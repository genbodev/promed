<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Тип рабочих списков
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010-2011 Swan Ltd.
 * @author       gabdushev
 * @version
 */
class AnalyzerWorksheetType_model extends swModel {
	private $AnalyzerWorksheetType_id;//AnalyzerWorksheetType_id
	private $AnalyzerWorksheetType_Code;//AnalyzerWorksheetType_Code
	private $AnalyzerWorksheetType_Name;//AnalyzerWorksheetType_Name
	private $AnalyzerModel_id;//модель анализатора
	private $pmUser_id;//Идентификатор пользователя системы Промед

	/**
	 * Comment
	 */
	public function getAnalyzerWorksheetType_id() { return $this->AnalyzerWorksheetType_id;}
	/**
	 * Comment
	 */
	public function setAnalyzerWorksheetType_id($value) { $this->AnalyzerWorksheetType_id = $value; }

	/**
	 * Comment
	 */
	public function getAnalyzerWorksheetType_Code() { return $this->AnalyzerWorksheetType_Code;}
	/**
	 * Comment
	 */
	public function setAnalyzerWorksheetType_Code($value) { $this->AnalyzerWorksheetType_Code = $value; }

	/**
	 * Comment
	 */
	public function getAnalyzerWorksheetType_Name() { return $this->AnalyzerWorksheetType_Name;}
	/**
	 * Comment
	 */
	public function setAnalyzerWorksheetType_Name($value) { $this->AnalyzerWorksheetType_Name = $value; }

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
				AnalyzerWorksheetType_id, AnalyzerWorksheetType_Code, AnalyzerWorksheetType_Name, AnalyzerModel_id
			from
				lis.v_AnalyzerWorksheetType with (nolock)
			where
				AnalyzerWorksheetType_id = :AnalyzerWorksheetType_id
		";
		$r = $this->db->query($q, array('AnalyzerWorksheetType_id' => $this->AnalyzerWorksheetType_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->AnalyzerWorksheetType_id = $r[0]['AnalyzerWorksheetType_id'];
				$this->AnalyzerWorksheetType_Code = $r[0]['AnalyzerWorksheetType_Code'];
				$this->AnalyzerWorksheetType_Name = $r[0]['AnalyzerWorksheetType_Name'];
				$this->AnalyzerModel_id = $r[0]['AnalyzerModel_id'];
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
		if (isset($filter['AnalyzerWorksheetType_id']) && $filter['AnalyzerWorksheetType_id']) {
			$where[] = 'v_AnalyzerWorksheetType.AnalyzerWorksheetType_id = :AnalyzerWorksheetType_id';
			$p['AnalyzerWorksheetType_id'] = $filter['AnalyzerWorksheetType_id'];
		}
		if (isset($filter['AnalyzerWorksheetType_Code']) && $filter['AnalyzerWorksheetType_Code']) {
			$where[] = 'v_AnalyzerWorksheetType.AnalyzerWorksheetType_Code = :AnalyzerWorksheetType_Code';
			$p['AnalyzerWorksheetType_Code'] = $filter['AnalyzerWorksheetType_Code'];
		}
		if (isset($filter['AnalyzerWorksheetType_Name']) && $filter['AnalyzerWorksheetType_Name']) {
			$where[] = 'v_AnalyzerWorksheetType.AnalyzerWorksheetType_Name = :AnalyzerWorksheetType_Name';
			$p['AnalyzerWorksheetType_Name'] = $filter['AnalyzerWorksheetType_Name'];
		}
		if (isset($filter['AnalyzerModel_id']) && $filter['AnalyzerModel_id']) {
			$where[] = 'v_AnalyzerWorksheetType.AnalyzerModel_id = :AnalyzerModel_id';
			$p['AnalyzerModel_id'] = $filter['AnalyzerModel_id'];
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		} else {
			return false;
		}
		$q = "
			SELECT
				v_AnalyzerWorksheetType.AnalyzerWorksheetType_id, v_AnalyzerWorksheetType.AnalyzerWorksheetType_Code, v_AnalyzerWorksheetType.AnalyzerWorksheetType_Name, v_AnalyzerWorksheetType.AnalyzerModel_id
				,AnalyzerModel_id_ref.AnalyzerModel_Name AnalyzerModel_id_Name
			FROM
				lis.v_AnalyzerWorksheetType WITH (NOLOCK)
				LEFT JOIN lis.v_AnalyzerModel AnalyzerModel_id_ref WITH (NOLOCK) ON AnalyzerModel_id_ref.AnalyzerModel_id = v_AnalyzerWorksheetType.AnalyzerModel_id
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
		$procedure = 'p_AnalyzerWorksheetType_ins';
		if ( $this->AnalyzerWorksheetType_id > 0 ) {
			//редактировать может только создатель и админ
			//админ?
			if (isSuperadmin()) {
				$procedure = 'p_AnalyzerWorksheetType_upd';
			} else {
				// создатель, это ты?
				if ($this->pmUser_id == $this->getFirstResultFromQuery('SELECT pmUser_InsID FROM AnalyzerWorksheetType with (nolock) WHERE AnalyzerWorksheetType_id = :AnalyzerWorksheetType_id', array('AnalyzerWorksheetType_id'=>$this->AnalyzerWorksheetType_id))) {
					$procedure = 'p_AnalyzerWorksheetType_upd';
				} else {
					throw new Exception('Удалить/изменить тип рабочего списка может либо создатель, либо администратор');
				}
			}
		}
		$q = "
			declare
				@AnalyzerWorksheetType_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @AnalyzerWorksheetType_id = :AnalyzerWorksheetType_id;
			exec lis." . $procedure . "
				@AnalyzerWorksheetType_id = @AnalyzerWorksheetType_id output,
				@AnalyzerWorksheetType_Code = :AnalyzerWorksheetType_Code,
				@AnalyzerWorksheetType_Name = :AnalyzerWorksheetType_Name,
				@AnalyzerModel_id = :AnalyzerModel_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @AnalyzerWorksheetType_id as AnalyzerWorksheetType_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'AnalyzerWorksheetType_id' => $this->AnalyzerWorksheetType_id,
			'AnalyzerWorksheetType_Code' => $this->AnalyzerWorksheetType_Code,
			'AnalyzerWorksheetType_Name' => $this->AnalyzerWorksheetType_Name,
			'AnalyzerModel_id' => $this->AnalyzerModel_id,
			'pmUser_id' => $this->pmUser_id,
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
			$result = $r->result('array');
			$this->AnalyzerWorksheetType_id = $result[0]['AnalyzerWorksheetType_id'];
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
		if (isSuperadmin()) {
			$procedure = 'p_AnalyzerWorksheetType_del';
		} else {
			// создатель, это ты?
			if ($this->pmUser_id == $this->getFirstResultFromQuery('SELECT pmUser_InsID FROM AnalyzerWorksheetType with (nolock) WHERE AnalyzerWorksheetType_id = :AnalyzerWorksheetType_id', array('AnalyzerWorksheetType_id'=>$this->AnalyzerWorksheetType_id))) {
				$procedure = 'p_AnalyzerWorksheetType_del';
			} else {
				throw new Exception('Удалить/изменить тип рабочего списка может либо создатель, либо администратор');
			}
		}
		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec lis.$procedure
				@AnalyzerWorksheetType_id = :AnalyzerWorksheetType_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'AnalyzerWorksheetType_id' => $this->AnalyzerWorksheetType_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}
}