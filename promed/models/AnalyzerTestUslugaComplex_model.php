<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Связь тестов с услугами
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010-2011 Swan Ltd.
 * @author       gabdushev
 * @version
 */
class AnalyzerTestUslugaComplex_model extends swModel {
	private $AnalyzerTestUslugaComplex_id;//AnalyzerTestUslugaComplex_id
	private $AnalyzerTest_id;//Тесты анализаторов
	private $UslugaComplex_id;//Комплексная услуга
	private $UslugaCategory_SysNick;//Категория услуги, RO
	private $AnalyzerTestUslugaComplex_Deleted;//AnalyzerTestUslugaComplex_Deleted
	private $pmUser_id;//Идентификатор пользователя системы Промед

	/**
	 * Comment
	 */
	public function getAnalyzerTestUslugaComplex_id() { return $this->AnalyzerTestUslugaComplex_id;}
	/**
	 * Comment
	 */
	public function setAnalyzerTestUslugaComplex_id($value) { $this->AnalyzerTestUslugaComplex_id = $value; }

	/**
	 * Comment
	 */
	public function getAnalyzerTest_id() { return $this->AnalyzerTest_id;}
	/**
	 * Comment
	 */
	public function setAnalyzerTest_id($value) { $this->AnalyzerTest_id = $value; }

	/**
	 * Comment
	 */
	public function getUslugaComplex_id() { return $this->UslugaComplex_id;}
	/**
	 * Comment
	 */
	public function setUslugaComplex_id($value) { $this->UslugaComplex_id = $value; }

	/**
	 * Comment
	 */
	public function getUslugaCategory_sysNick() { return $this->UslugaCategory_SysNick;}

	/**
	 * Comment
	 */
	public function getAnalyzerTestUslugaComplex_Deleted() { return $this->AnalyzerTestUslugaComplex_Deleted;}
	/**
	 * Comment
	 */
	public function setAnalyzerTestUslugaComplex_Deleted($value) { $this->AnalyzerTestUslugaComplex_Deleted = $value; }

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
	function loadAlowedUslugaCategory_SysNicks($AnalyzerTest_id){
		/*
		 * Связанные услуги: добавляются связанные услуги категорий gost2004, gost2011,
		 * tfoms, syslabprofile, promed. Можно связать только с одной услугой каждой категории.
		 * При вызове формы на добавление в списке категорий показывать только те, для которых еще нет связанных с тестом услуг.
		 * При вызове формы на изменение можно выбрать услугу только той же категории.
		 *
		 * */
		$q = <<<q
SELECT
    UslugaCategory_SysNick
FROM
    UslugaCategory with (nolock)
WHERE
    UslugaCategory_SysNick IN ( 'gost2011', 'tfoms',
                                'syslabprofile', 'promed' )
    AND UslugaCategory_id NOT IN (
    SELECT
        UslugaCategory_id
    FROM
        dbo.UslugaComplex with (nolock)
    WHERE
        UslugaComplex_id IN ( SELECT
                                UslugaComplex_id
                             FROM
                                lis.AnalyzerTestUslugaComplex with (nolock)
                             WHERE
                                AnalyzerTest_id = :AnalyzerTest_id
                           ) )
q;
		$r = $this->db->query($q, array('AnalyzerTest_id' => $AnalyzerTest_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			return $r;
		}
		else {
			return false;
		}
	}

	/**
	 * Comment
	 */
	function load() {
		$q = "
			select
				AnalyzerTestUslugaComplex_id, AnalyzerTest_id, uc.UslugaComplex_id, AnalyzerTestUslugaComplex_Deleted,
				c.UslugaCategory_SysNick
			from
				lis.v_AnalyzerTestUslugaComplex atuc with (nolock)
				LEFT JOIN v_UslugaComplex uc with (nolock) ON uc.UslugaComplex_id = atuc.UslugaComplex_id
				LEFT JOIN v_UslugaCategory c with (nolock) ON c.UslugaCategory_id = uc.UslugaCategory_id
			where
				AnalyzerTestUslugaComplex_id = :AnalyzerTestUslugaComplex_id
		";
		$r = $this->db->query($q, array('AnalyzerTestUslugaComplex_id' => $this->AnalyzerTestUslugaComplex_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->AnalyzerTestUslugaComplex_id = $r[0]['AnalyzerTestUslugaComplex_id'];
				$this->AnalyzerTest_id = $r[0]['AnalyzerTest_id'];
				$this->UslugaComplex_id = $r[0]['UslugaComplex_id'];
				$this->UslugaCategory_SysNick = $r[0]['UslugaCategory_SysNick'];
				$this->AnalyzerTestUslugaComplex_Deleted = $r[0]['AnalyzerTestUslugaComplex_Deleted'];
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
		if (isset($filter['AnalyzerTestUslugaComplex_id']) && $filter['AnalyzerTestUslugaComplex_id']) {
			$where[] = 'ATUC.AnalyzerTestUslugaComplex_id = :AnalyzerTestUslugaComplex_id';
			$p['AnalyzerTestUslugaComplex_id'] = $filter['AnalyzerTestUslugaComplex_id'];
		}
		if (isset($filter['AnalyzerTest_id']) && $filter['AnalyzerTest_id']) {
			$where[] = 'ATUC.AnalyzerTest_id = :AnalyzerTest_id';
			$p['AnalyzerTest_id'] = $filter['AnalyzerTest_id'];
		}
		if (isset($filter['UslugaComplex_id']) && $filter['UslugaComplex_id']) {
			$where[] = 'ATUC.UslugaComplex_id = :UslugaComplex_id';
			$p['UslugaComplex_id'] = $filter['UslugaComplex_id'];
		}
		if (isset($filter['AnalyzerTestUslugaComplex_Deleted']) && $filter['AnalyzerTestUslugaComplex_Deleted']) {
			$where[] = 'ATUC.AnalyzerTestUslugaComplex_Deleted = :AnalyzerTestUslugaComplex_Deleted';
			$p['AnalyzerTestUslugaComplex_Deleted'] = $filter['AnalyzerTestUslugaComplex_Deleted'];
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		$q = "
			SELECT
				ATUC.AnalyzerTestUslugaComplex_id,
				ATUC.AnalyzerTest_id,
				ATUC.UslugaComplex_id,
				ATUC.AnalyzerTestUslugaComplex_Deleted,
				AnalyzerTest_id_ref.AnalyzerTest_Name as AnalyzerTest_id_Name,
				UslugaComplex_id_ref.UslugaComplex_Name as UslugaComplex_id_Name,
				c.UslugaCategory_Name as UslugaComplex_id_UslugaCategory_Name
			FROM
				lis.v_AnalyzerTestUslugaComplex ATUC WITH (NOLOCK)
				LEFT JOIN lis.v_AnalyzerTest AnalyzerTest_id_ref WITH (NOLOCK) ON AnalyzerTest_id_ref.AnalyzerTest_id = ATUC.AnalyzerTest_id
				LEFT JOIN dbo.v_UslugaComplex UslugaComplex_id_ref WITH (NOLOCK) ON UslugaComplex_id_ref.UslugaComplex_id = ATUC.UslugaComplex_id
				LEFT JOIN dbo.v_UslugaCategory c WITH (NOLOCK) ON c.UslugaCategory_id = UslugaComplex_id_ref.UslugaCategory_id
			$where_clause
		";
		//echo getDebugSql($q, $filter);
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
		$procedure = 'p_AnalyzerTestUslugaComplex_ins';
		if ( $this->AnalyzerTestUslugaComplex_id > 0 ) {
			$procedure = 'p_AnalyzerTestUslugaComplex_upd';
		}
		$q = "
			declare
				@AnalyzerTestUslugaComplex_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @AnalyzerTestUslugaComplex_id = :AnalyzerTestUslugaComplex_id;
			exec lis." . $procedure . "
				@AnalyzerTestUslugaComplex_id = @AnalyzerTestUslugaComplex_id output,
				@AnalyzerTest_id = :AnalyzerTest_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@AnalyzerTestUslugaComplex_Deleted = :AnalyzerTestUslugaComplex_Deleted,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @AnalyzerTestUslugaComplex_id as AnalyzerTestUslugaComplex_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'AnalyzerTestUslugaComplex_id' => $this->AnalyzerTestUslugaComplex_id,
			'AnalyzerTest_id' => $this->AnalyzerTest_id,
			'UslugaComplex_id' => $this->UslugaComplex_id,
			'AnalyzerTestUslugaComplex_Deleted' => $this->AnalyzerTestUslugaComplex_Deleted,
			'pmUser_id' => $this->pmUser_id,
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
			$result = $r->result('array');
			$this->AnalyzerTestUslugaComplex_id = $result[0]['AnalyzerTestUslugaComplex_id'];
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec lis.p_AnalyzerTestUslugaComplex_del
				@AnalyzerTestUslugaComplex_id = :AnalyzerTestUslugaComplex_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'AnalyzerTestUslugaComplex_id' => $this->AnalyzerTestUslugaComplex_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}

}