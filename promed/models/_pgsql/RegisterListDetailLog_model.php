<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * TODO: complete explanation, preamble and describing
 * Модель для объектов Таблица детальных логов
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010-2011 Swan Ltd.
 * @author       gabdushev
 * @version
 */
class RegisterListDetailLog_model extends SwPgModel {
	private $RegisterListDetailLog_id;//RegisterListDetailLog_id
	private $RegisterListDetailLog_setDT;//Дата, время записи
	private $RegisterListLogType_id;//Тип сообщения лога
	private $RegisterListDetailLog_Message;//Текст сообщения
	private $RegisterListLog_id;//RegisterListLog_id
	private $pmUser_id;//Идентификатор пользователя системы Промед

	/**
	 * @return mixed
	 */
	public function getRegisterListDetailLog_id() { return $this->RegisterListDetailLog_id;}

	/**
	 * @param $value
	 */
	public function setRegisterListDetailLog_id($value) { $this->RegisterListDetailLog_id = $value; }

	/**
	 * @return mixed
	 */
	public function getRegisterListDetailLog_setDT() { return $this->RegisterListDetailLog_setDT;}

	/**
	 * @param $value
	 */
	public function setRegisterListDetailLog_setDT($value) { $this->RegisterListDetailLog_setDT = $value; }

	/**
	 * @return mixed
	 */
	public function getRegisterListLogType_id() { return $this->RegisterListLogType_id;}

	/**
	 * @param $value
	 */
	public function setRegisterListLogType_id($value) { $this->RegisterListLogType_id = $value; }

	/**
	 * @return mixed
	 */
	public function getRegisterListDetailLog_Message() { return $this->RegisterListDetailLog_Message;}

	/**
	 * @param $value
	 */
	public function setRegisterListDetailLog_Message($value) { $this->RegisterListDetailLog_Message = $value; }

	/**
	 * @return mixed
	 */
	public function getRegisterListLog_id() { return $this->RegisterListLog_id;}

	/**
	 * @param $value
	 */
	public function setRegisterListLog_id($value) { $this->RegisterListLog_id = $value; }

	/**
	 * @return mixed
	 */
	public function getpmUser_id() { return $this->pmUser_id;}

	/**
	 * @param $value
	 */
	public function setpmUser_id($value) { $this->pmUser_id = $value; }

	/**
	 * RegisterListDetailLog_model constructor.
	 */
	function __construct(){
		if (isset($_SESSION['pmuser_id'])) {
			$this->setpmUser_id($_SESSION['pmuser_id']);
		} else {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
		}
	}

	/**
	 * @param Datetime $RegisterListDetailLog_setDT
	 * @param Int    $RegisterListLogType_id
	 * @param string $RegisterListDetailLog_Message
	 * @param Int    $RegisterListLog_id
	 * @param Int    $pmUser_id
	 */
	public static function createLogMessage(
		$RegisterListDetailLog_setDT,//Дата, время записи
		$RegisterListLogType_id,//Тип сообщения лога
		$RegisterListDetailLog_Message,//Текст сообщения
		$RegisterListLog_id,//RegisterListLog_id
		$pmUser_id//Идентификатор пользователя системы Промед
	) {
		$l = new RegisterListDetailLog_model();
		$l->setRegisterListDetailLog_setDT  ($RegisterListDetailLog_setDT  );//Дата, время записи
		$l->setRegisterListLogType_id       ($RegisterListLogType_id       );//Тип сообщения лога
		$l->setRegisterListDetailLog_Message($RegisterListDetailLog_Message);//Текст сообщения
		$l->setRegisterListLog_id           ($RegisterListLog_id           );//RegisterListLog_id
		$l->setpmUser_id                    ($pmUser_id                    ); //Идентификатор пользователя системы Промед
		$l->save();
	}

	/**
	 * @return bool|CI_DB_sqlsrv_result|mixed|void
	 */
    function load() {
		$q = "
			select
				RegisterListDetailLog_id as \"RegisterListDetailLog_id\", 
                RegisterListDetailLog_setDT as \"RegisterListDetailLog_setDT\", 
                RegisterListLogType_id as \"RegisterListLogType_id\", 
                RegisterListDetailLog_Message as \"RegisterListDetailLog_Message\", 
                RegisterListLog_id as \"RegisterListLog_id\"
			from
				stg.v_RegisterListDetailLog
			where
				RegisterListDetailLog_id = :RegisterListDetailLog_id
		";
		$r = $this->db->query($q, array('RegisterListDetailLog_id' => $this->RegisterListDetailLog_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->RegisterListDetailLog_id = $r[0]['RegisterListDetailLog_id'];
				$this->RegisterListDetailLog_setDT = $r[0]['RegisterListDetailLog_setDT'];
				$this->RegisterListLogType_id = $r[0]['RegisterListLogType_id'];
				$this->RegisterListDetailLog_Message = $r[0]['RegisterListDetailLog_Message'];
				$this->RegisterListLog_id = $r[0]['RegisterListLog_id'];
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
		if (isset($filter['RegisterListDetailLog_id']) && $filter['RegisterListDetailLog_id']) {
			$where[] = 'v_RegisterListDetailLog.RegisterListDetailLog_id = :RegisterListDetailLog_id';
			$p['RegisterListDetailLog_id'] = $filter['RegisterListDetailLog_id'];
		}
		if (isset($filter['RegisterListDetailLog_setDT']) && $filter['RegisterListDetailLog_setDT']) {
			$where[] = 'v_RegisterListDetailLog.RegisterListDetailLog_setDT = :RegisterListDetailLog_setDT';
			$p['RegisterListDetailLog_setDT'] = $filter['RegisterListDetailLog_setDT'];
		}
		if (isset($filter['RegisterListLogType_id']) && $filter['RegisterListLogType_id']) {
			$where[] = 'v_RegisterListDetailLog.RegisterListLogType_id = :RegisterListLogType_id';
			$p['RegisterListLogType_id'] = $filter['RegisterListLogType_id'];
		}
		if (isset($filter['RegisterListDetailLog_Message']) && $filter['RegisterListDetailLog_Message']) {
			$where[] = 'v_RegisterListDetailLog.RegisterListDetailLog_Message = :RegisterListDetailLog_Message';
			$p['RegisterListDetailLog_Message'] = $filter['RegisterListDetailLog_Message'];
		}
		if (isset($filter['RegisterListLog_id']) && $filter['RegisterListLog_id']) {
			$where[] = 'v_RegisterListDetailLog.RegisterListLog_id = :RegisterListLog_id';
			$p['RegisterListLog_id'] = $filter['RegisterListLog_id'];
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
            $where_clause = "
				WHERE 
				-- where 
				{$where_clause}
				-- end where
			";
		}
		$q = "
		SELECT
		    -- select
                v_RegisterListDetailLog.RegisterListDetailLog_id as \"RegisterListDetailLog_id\", 
                v_RegisterListDetailLog.RegisterListDetailLog_setDT as \"RegisterListDetailLog_setDT\", 
                v_RegisterListDetailLog.RegisterListLogType_id as \"RegisterListLogType_id\", 
                v_RegisterListDetailLog.RegisterListDetailLog_Message as \"RegisterListDetailLog_Message\", 
                v_RegisterListDetailLog.RegisterListLog_id as \"RegisterListLog_id\",
                RegisterListLogType_id_ref.RegisterListLogType_Name  as \"RegisterListLogType_id_Name\"
            -- end select
		FROM
			-- from
				stg.v_RegisterListDetailLog 
				LEFT JOIN stg.v_RegisterListLogType RegisterListLogType_id_ref  ON RegisterListLogType_id_ref.RegisterListLogType_id = v_RegisterListDetailLog.RegisterListLogType_id
				LEFT JOIN stg.v_RegisterListLog RegisterListLog_id_ref  ON RegisterListLog_id_ref.RegisterListLog_id = v_RegisterListDetailLog.RegisterListLog_id
			-- end from
			$where_clause
		ORDER BY
			-- order by
		        v_RegisterListDetailLog.RegisterListDetailLog_id DESC
		    -- end order by
		";
        return $this->getPagingResponse($q, $filter, $filter['start'], $filter['limit'], true);
    }
	/**
	 * @return array|mixed
	 */
	function save() {
		$procedure = 'p_RegisterListDetailLog_ins';
		if ( $this->RegisterListDetailLog_id > 0 ) {
			$procedure = 'p_RegisterListDetailLog_upd';
		}
		$q = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\",
            RegisterListDetailLog_id as \"RegisterListDetailLog_id\"
        from stg.{$procedure}
            (
 				RegisterListDetailLog_id := :RegisterListDetailLog_id,
				RegisterListDetailLog_setDT := :RegisterListDetailLog_setDT,
				RegisterListLogType_id := :RegisterListLogType_id,
				RegisterListDetailLog_Message := :RegisterListDetailLog_Message,
				RegisterListLog_id := :RegisterListLog_id,
				pmUser_id := :pmUser_id
            )"

;
		$p = array(
			'RegisterListDetailLog_id' => $this->RegisterListDetailLog_id,
			'RegisterListDetailLog_setDT' => $this->RegisterListDetailLog_setDT,
			'RegisterListLogType_id' => $this->RegisterListLogType_id,
			'RegisterListDetailLog_Message' => $this->RegisterListDetailLog_Message,
			'RegisterListLog_id' => $this->RegisterListLog_id,
			'pmUser_id' => $this->pmUser_id,
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
			$result = $r->result('array');
			$this->RegisterListDetailLog_id = $result[0]['RegisterListDetailLog_id'];
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
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\"
        from stg.p_RegisterListDetailLog_del
            (
                RegisterListDetailLog_id := :RegisterListDetailLog_id
            )";
		$r = $this->db->query($q, array(
			'RegisterListDetailLog_id' => $this->RegisterListDetailLog_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}
}