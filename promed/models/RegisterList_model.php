<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * TODO: complete explanation, preamble and describing
 * Модель для объектов Таблица регистров/справочников доступных для загрузки
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010-2011 Swan Ltd.
 * @author       gabdushev
 * @version
 */
class RegisterList_model extends swModel {
	private $RegisterList_id;//RegisterList_id
	private $RegisterList_Name;//название основной таблицы в БД
	private $RegisterList_Schema;//схема БД
	private $RegisterList_Descr;//Описание справочника
	private $Region_id;//Идентификатор региона справочника территорий
	private $pmUser_id;//Идентификатор пользователя системы Промед

	/**
	 * @return mixed
	 */
	public function getRegisterList_id() { return $this->RegisterList_id;}

	/**
	 * @param $value
	 */
	public function setRegisterList_id($value) { $this->RegisterList_id = $value; }

	/**
	 * @return mixed
	 */
	public function getRegisterList_Name() { return $this->RegisterList_Name;}

	/**
	 * @param $value
	 */
	public function setRegisterList_Name($value) { $this->RegisterList_Name = $value; }

	/**
	 * @return mixed
	 */
	public function getRegisterList_Schema() { return $this->RegisterList_Schema;}

	/**
	 * @param $value
	 */
	public function setRegisterList_Schema($value) { $this->RegisterList_Schema = $value; }

	/**
	 * @return mixed
	 */
	public function getRegisterList_Descr() { return $this->RegisterList_Descr;}

	/**
	 * @param $value
	 */
	public function setRegisterList_Descr($value) { $this->RegisterList_Descr = $value; }

	/**
	 * @return mixed
	 */
	public function getRegion_id() { return $this->Region_id;}

	/**
	 * @param $value
	 */
	public function setRegion_id($value) { $this->Region_id = $value; }

	/**
	 * @return mixed
	 */
	public function getpmUser_id() { return $this->pmUser_id;}

	/**
	 * @param $value
	 */
	public function setpmUser_id($value) { $this->pmUser_id = $value; }

	/**
	 * RegisterList_model constructor.
	 */
	function __construct(){
		if (isset($_SESSION['pmuser_id'])) {
			$this->setpmUser_id($_SESSION['pmuser_id']);
		} else {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
		}
	}

	/**
	 * @return bool|CI_DB_sqlsrv_result|mixed|void
	 */
	function load() {
		$q = "
			select
				RegisterList_id, RegisterList_Name, RegisterList_Schema, RegisterList_Descr, Region_id
			from
				stg.v_RegisterList with(nolock)
			where
				RegisterList_id = :RegisterList_id
		";
		$r = $this->db->query($q, array('RegisterList_id' => $this->RegisterList_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->RegisterList_id = $r[0]['RegisterList_id'];
				$this->RegisterList_Name = $r[0]['RegisterList_Name'];
				$this->RegisterList_Schema = $r[0]['RegisterList_Schema'];
				$this->RegisterList_Descr = $r[0]['RegisterList_Descr'];
				$this->Region_id = $r[0]['Region_id'];
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
	 * @param $filter
	 * @return bool|mixed
	 */
	function loadList($filter) {
		$where = array();
		$p = array();
		if (isset($filter['RegisterList_id']) && $filter['RegisterList_id']) {
			$where[] = 'v_RegisterList.RegisterList_id = :RegisterList_id';
			$p['RegisterList_id'] = $filter['RegisterList_id'];
		}
		if (isset($filter['RegisterList_Name']) && $filter['RegisterList_Name'] == 'AttachJournal') {
			$where[] = 'v_RegisterList.RegisterList_id in (34,35,36,37)';
		}
		elseif (isset($filter['RegisterList_Name']) && $filter['RegisterList_Name']) {
			$where[] = 'v_RegisterList.RegisterList_Name = :RegisterList_Name';
			$p['RegisterList_Name'] = $filter['RegisterList_Name'];
		}
		if (isset($filter['RegisterList_Schema']) && $filter['RegisterList_Schema']) {
			$where[] = 'v_RegisterList.RegisterList_Schema = :RegisterList_Schema';
			$p['RegisterList_Schema'] = $filter['RegisterList_Schema'];
		}
		if (isset($filter['RegisterList_Descr']) && $filter['RegisterList_Descr']) {
			$where[] = 'v_RegisterList.RegisterList_Descr = :RegisterList_Descr';
			$p['RegisterList_Descr'] = $filter['RegisterList_Descr'];
		}
		if (isset($filter['Region_id']) && $filter['Region_id']) {
			$where[] = 'v_RegisterList.Region_id = :Region_id';
			$p['Region_id'] = $filter['Region_id'];
		}
		if ($this->getRegionNick() == 'vologda' && strpos($filter['session']['groups'], 'ExportAttachedPopulation') === false) {
			$where[] = 'v_RegisterList.RegisterList_id not in (34,35,36,37)';
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		$q = "
		SELECT
			
				v_RegisterList.RegisterList_id, v_RegisterList.RegisterList_Name, v_RegisterList.RegisterList_Schema, v_RegisterList.RegisterList_Descr, v_RegisterList.Region_id
				,Region_id_ref.KLArea_Name Region_id_Name
		FROM
			
				stg.v_RegisterList WITH (NOLOCK)
				LEFT JOIN dbo.v_KLArea Region_id_ref WITH (NOLOCK) ON Region_id_ref.KLArea_id = v_RegisterList.Region_id
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
	 * @return array|mixed
	 */
	function save() {
		$procedure = 'p_RegisterList_ins';
		if ( $this->RegisterList_id > 0 ) {
			$procedure = 'p_RegisterList_upd';
		}
		$q = "
			declare
				@RegisterList_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @RegisterList_id = :RegisterList_id;
			exec stg." . $procedure . "
				@RegisterList_id = @RegisterList_id output,
				@RegisterList_Name = :RegisterList_Name,
				@RegisterList_Schema = :RegisterList_Schema,
				@RegisterList_Descr = :RegisterList_Descr,
				@Region_id = :Region_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @RegisterList_id as RegisterList_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'RegisterList_id' => $this->RegisterList_id,
			'RegisterList_Name' => $this->RegisterList_Name,
			'RegisterList_Schema' => $this->RegisterList_Schema,
			'RegisterList_Descr' => $this->RegisterList_Descr,
			'Region_id' => $this->Region_id,
			'pmUser_id' => $this->pmUser_id,
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
			$result = $r->result('array');
			$this->RegisterList_id = $result[0]['RegisterList_id'];
		}
		else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * @return bool|mixed
	 */
	function delete() {
		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec stg.p_RegisterList_del
				@RegisterList_id = :RegisterList_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'RegisterList_id' => $this->RegisterList_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}
}