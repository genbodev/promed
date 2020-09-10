<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * TODO: complete explanation, preamble and describing
 * Модель для объектов Связь тестов с типами рабочего списка
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010-2011 Swan Ltd.
 * @author       gabdushev
 * @version
 */
class AnalyzerTestWorksheetType_model extends SwPgModel {
	private $AnalyzerTestWorksheetType_id;//AnalyzerTestWorksheetType_id
	private $AnalyzerTest_id;//Тесты анализаторов
	private $AnalyzerWorksheetType_id;//Тип рабочего списка
	private $pmUser_id;//Идентификатор пользователя системы Промед

	/**
	 * TO-DO описать
	 */
	public function getAnalyzerTestWorksheetType_id() { return $this->AnalyzerTestWorksheetType_id;}
	/**
	 * TO-DO описать
	 */
	public function setAnalyzerTestWorksheetType_id($value) { $this->AnalyzerTestWorksheetType_id = $value; }

	/**
	 * TO-DO описать
	 */
	public function getAnalyzerTest_id() { return $this->AnalyzerTest_id;}
	/**
	 * TO-DO описать
	 */
	public function setAnalyzerTest_id($value) { $this->AnalyzerTest_id = $value; }

	/**
	 * TO-DO описать
	 */
	public function getAnalyzerWorksheetType_id() { return $this->AnalyzerWorksheetType_id;}
	/**
	 * TO-DO описать
	 */
	public function setAnalyzerWorksheetType_id($value) { $this->AnalyzerWorksheetType_id = $value; }

	/**
	 * TO-DO описать
	 */
	public function getpmUser_id() { return $this->pmUser_id;}
	/**
	 * TO-DO описать
	 */
	public function setpmUser_id($value) { $this->pmUser_id = $value; }

	/**
	 * Конструктор
	 */
	function __construct(){
		if (isset($_SESSION['pmuser_id'])) {
			$this->setpmUser_id($_SESSION['pmuser_id']);
		} else {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
		}
	}

	/**
	 * Загрузка
	 */
	function load() {
		$q = "
			select
				AnalyzerTestWorksheetType_id, AnalyzerTest_id, AnalyzerWorksheetType_id
			from
				lis.v_AnalyzerTestWorksheetType
			where
				AnalyzerTestWorksheetType_id = :AnalyzerTestWorksheetType_id
		";
		$r = $this->db->query($q, array('AnalyzerTestWorksheetType_id' => $this->AnalyzerTestWorksheetType_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->AnalyzerTestWorksheetType_id = $r[0]['AnalyzerTestWorksheetType_id'];
				$this->AnalyzerTest_id = $r[0]['AnalyzerTest_id'];
				$this->AnalyzerWorksheetType_id = $r[0]['AnalyzerWorksheetType_id'];
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
	 * Загрузка списка
	 */
	function loadList($filter) {
		$where = array();
		$p = array();
		if (isset($filter['AnalyzerTestWorksheetType_id']) && $filter['AnalyzerTestWorksheetType_id']) {
			$where[] = 'v_AnalyzerTestWorksheetType.AnalyzerTestWorksheetType_id = :AnalyzerTestWorksheetType_id';
			$p['AnalyzerTestWorksheetType_id'] = $filter['AnalyzerTestWorksheetType_id'];
		}
		if (isset($filter['AnalyzerTest_id']) && $filter['AnalyzerTest_id']) {
			$where[] = 'v_AnalyzerTestWorksheetType.AnalyzerTest_id = :AnalyzerTest_id';
			$p['AnalyzerTest_id'] = $filter['AnalyzerTest_id'];
		}
		if (isset($filter['AnalyzerWorksheetType_id']) && $filter['AnalyzerWorksheetType_id']) {
			$where[] = 'v_AnalyzerTestWorksheetType.AnalyzerWorksheetType_id = :AnalyzerWorksheetType_id';
			$p['AnalyzerWorksheetType_id'] = $filter['AnalyzerWorksheetType_id'];
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		$q = "
			SELECT
				v_AnalyzerTestWorksheetType.AnalyzerTestWorksheetType_id, v_AnalyzerTestWorksheetType.AnalyzerTest_id, v_AnalyzerTestWorksheetType.AnalyzerWorksheetType_id
				,ISNULL(AnalyzerTest_id_ref.AnalyzerTest_Name, uc.UslugaComplex_Name) as AnalyzerTest_id_Name, AnalyzerWorksheetType_id_ref.AnalyzerWorksheetType_Name AnalyzerWorksheetType_id_Name,
				uc.UslugaComplex_Code as AnalyzerTest_id_Code

			FROM
				lis.v_AnalyzerTestWorksheetType
				LEFT JOIN lis.v_AnalyzerTest AnalyzerTest_id_ref ON AnalyzerTest_id_ref.AnalyzerTest_id = v_AnalyzerTestWorksheetType.AnalyzerTest_id
				left join v_UslugaComplex uc on uc.UslugaComplex_id = AnalyzerTest_id_ref.UslugaComplex_id
				LEFT JOIN lis.v_AnalyzerWorksheetType AnalyzerWorksheetType_id_ref ON AnalyzerWorksheetType_id_ref.AnalyzerWorksheetType_id = v_AnalyzerTestWorksheetType.AnalyzerWorksheetType_id
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
	function save() {
		$procedure = 'p_AnalyzerTestWorksheetType_ins';
		if ( $this->AnalyzerTestWorksheetType_id > 0 ) {
			$procedure = 'p_AnalyzerTestWorksheetType_upd';
		}
		$q = "
			declare
				@AnalyzerTestWorksheetType_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @AnalyzerTestWorksheetType_id = :AnalyzerTestWorksheetType_id;
			exec lis." . $procedure . "
				@AnalyzerTestWorksheetType_id = @AnalyzerTestWorksheetType_id output,
				@AnalyzerTest_id = :AnalyzerTest_id,
				@AnalyzerWorksheetType_id = :AnalyzerWorksheetType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @AnalyzerTestWorksheetType_id as AnalyzerTestWorksheetType_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'AnalyzerTestWorksheetType_id' => $this->AnalyzerTestWorksheetType_id,
			'AnalyzerTest_id' => $this->AnalyzerTest_id,
			'AnalyzerWorksheetType_id' => $this->AnalyzerWorksheetType_id,
			'pmUser_id' => $this->pmUser_id,
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
			$result = $r->result('array');
			$this->AnalyzerTestWorksheetType_id = $result[0]['AnalyzerTestWorksheetType_id'];
		}
		else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Удаление
	 */
	function delete() {
		if (isSuperadmin()) {
			$procedure = 'p_AnalyzerTestWorksheetType_del';
		} else {
			// создатель, это ты?
			if ($this->pmUser_id == $this->getFirstResultFromQuery('SELECT pmUser_InsID FROM AnalyzerWorksheetType WHERE AnalyzerWorksheetType_id = :AnalyzerWorksheetType_id', array('AnalyzerWorksheetType_id'=>$this->AnalyzerWorksheetType_id))) {
				$procedure = 'p_AnalyzerTestWorksheetType_del';
			} else {
				throw new Exception('Удалить/изменить тип рабочего списка может либо создатель, либо администратор');
			}
		}
		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec lis.$procedure
				@AnalyzerTestWorksheetType_id = :AnalyzerTestWorksheetType_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'AnalyzerTestWorksheetType_id' => $this->AnalyzerTestWorksheetType_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}
}