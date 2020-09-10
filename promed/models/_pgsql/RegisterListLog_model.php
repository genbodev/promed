<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * TODO: complete explanation, preamble and describing
 * Модель для объектов Таблица логов запуска загрузок
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010-2011 Swan Ltd.
 * @author       gabdushev
 * @version
 */
class RegisterListLog_model extends SwPgModel {
	private $RegisterListLog_id;//Строка лога с запуском загрузки
	private $RegisterListLog_begDT;//название основной таблицы в БД
	private $RegisterListLog_endDT;//схема БД
	private $RegisterListRunType_id;//Тип запуска
	private $RegisterListLog_AllCount;//Количество записей в файле
	private $RegisterListLog_UploadCount;//Количество загруженных записей
	private $RegisterListResultType_id;//Результат загрузки
    private $Lpu_id;//МО
	private $RegisterList_id;//Загружаемый регистр/справочник
	private $pmUser_id;//Идентификатор пользователя системы Промед
	private $RegisterListLog_NameFile;//Имя файла импорта 
	private $server_id;

	/**
	 * @g
	 */
	public function getRegisterListLog_id() { return $this->RegisterListLog_id;}

	/**
	 *
	 * @param type $value 
	 */
	public function setRegisterListLog_id($value) { $this->RegisterListLog_id = $value; }

	/**
	 * @g
	 */
	public function getRegisterListLog_begDT() { return $this->RegisterListLog_begDT;}

	/**
	 * @g
	 */
	public function setRegisterListLog_begDT($value) { $this->RegisterListLog_begDT = $value; }
	/**
	 *
	 * @param type $value 
	 */
	public function getRegisterListLog_endDT() { return $this->RegisterListLog_endDT;}
	/**
	 *
	 * @param type $value 
	 */
	public function setRegisterListLog_endDT($value) { $this->RegisterListLog_endDT = $value; }

	/**
	 *
	 * @param type $value 
	 */
	public function getRegisterListRunType_id() { return $this->RegisterListRunType_id;}
	/**
	 *
	 * @param type $value 
	 */
	public function setRegisterListRunType_id($value) { $this->RegisterListRunType_id = $value; }
	/**
	 *
	 * @param type $value 
	 */
	public function getRegisterListLog_AllCount() { return $this->RegisterListLog_AllCount;}
	/**
	 *
	 * @param type $value 
	 */	
	public function setRegisterListLog_AllCount($value) { $this->RegisterListLog_AllCount = $value; }
	/**
	 *
	 * @param type $value 
	 */
	public function getRegisterListLog_UploadCount() { return $this->RegisterListLog_UploadCount;}
	/**
	 *
	 * @param type $value 
	 */
	public function setRegisterListLog_UploadCount($value) { $this->RegisterListLog_UploadCount = $value; }
	/**
	 *
	 * @param type $value 
	 */
	public function getRegisterListResultType_id() { return $this->RegisterListResultType_id;}
	/**
	 *
	 * @param type $value 
	 */
	public function setRegisterListResultType_id($value) { $this->RegisterListResultType_id = $value; }
	/**
	 *
	 * @param type $value 
	 */
    public function getLpu_id() { return $this->Lpu_id;}
    /**
     *
     * @param type $value
     */
    public function setLpu_id($value) { $this->Lpu_id = $value; }
    /**
     *
     * @param type $value
     */
	public function getRegisterList_id() { return $this->RegisterList_id;}
	/**
	 *
	 * @param type $value 
	 */
	public function setRegisterList_id($value) { $this->RegisterList_id = $value; }
	/**
	 *
	 * @param type $value 
	 */
	public function getserver_id() { return $this->server_id;}
	/**
	 *
	 * @param type $value 
	 */
	public function setserver_id($value) { $this->server_id = $value; }
	/**
	 *
	 * @param type $value 
	 */
	public function getpmUser_id() { return $this->pmUser_id;}
	/**
	 *
	 * @param type $value 
	 */
	public function setpmUser_id($value) { $this->pmUser_id = $value; }
	/**
	 *
	 * @param type $value 
	 */
	public function setRegisterListLog_NameFile($value) { $this->RegisterListLog_NameFile = $value; }
	/**
	 *
	 * @param type $value 
	 */
	function __construct(){
		if (isset($_SESSION['pmuser_id'])) {
			$this->setpmUser_id($_SESSION['pmuser_id']);
			
		} else {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
		}
		if (isset($_SESSION['server_id'])) {
			//echo $_SESSION['server_id'];
			$this->setserver_id($_SESSION['server_id']);
		}
	}
	/**
	 *
	 * @param type $value 
	 */
	function load() {
		$q = "
			select
				RegisterListLog_id as \"RegisterListLog_id\",
				RegisterListLog_begDT as \"RegisterListLog_begDT\",
				RegisterListLog_endDT as \"RegisterListLog_endDT\",
				RegisterListRunType_id as \"RegisterListRunType_id\",
				RegisterListLog_AllCount as \"RegisterListLog_AllCount\",
				RegisterListLog_UploadCount as \"RegisterListLog_UploadCount\",
				RegisterListResultType_id as \"RegisterListResultType_id\",
				Lpu_id as \"Lpu_id\",
				RegisterList_id as \"RegisterList_id\"
			from
				stg.v_RegisterListLog
			where
				RegisterListLog_id = :RegisterListLog_id
		";
		$r = $this->db->query($q, array('RegisterListLog_id' => $this->RegisterListLog_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->RegisterListLog_id = $r[0]['RegisterListLog_id'];
				$this->RegisterListLog_begDT = $r[0]['RegisterListLog_begDT'];
				$this->RegisterListLog_endDT = $r[0]['RegisterListLog_endDT'];
				$this->RegisterListRunType_id = $r[0]['RegisterListRunType_id'];
				$this->RegisterListLog_AllCount = $r[0]['RegisterListLog_AllCount'];
				$this->RegisterListLog_UploadCount = $r[0]['RegisterListLog_UploadCount'];
				$this->RegisterListResultType_id = $r[0]['RegisterListResultType_id'];
                $this->Lpu_id = $r[0]['Lpu_id'];
				$this->RegisterList_id = $r[0]['RegisterList_id'];
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
	 *
	 * @param type $value 
	 */
	function loadList($filter) {
		$join = array();
		$where = array();
		$p = array();
		if (isset($filter['RegisterListLog_id']) && $filter['RegisterListLog_id']) {
			$where[] = 'v_RegisterListLog.RegisterListLog_id = :RegisterListLog_id';
			$p['RegisterListLog_id'] = $filter['RegisterListLog_id'];
		}
		if (isset($filter['RegisterListLog_begDT']) && $filter['RegisterListLog_begDT']) {
			$where[] = 'cast(v_RegisterListLog.RegisterListLog_begDT as date) >= cast(:RegisterListLog_begDT as date)';
			$p['RegisterListLog_begDT'] = $filter['RegisterListLog_begDT'];
		}
		if (isset($filter['RegisterListLog_endDT']) && $filter['RegisterListLog_endDT']) {
			$where[] = 'cast(v_RegisterListLog.RegisterListLog_begDT as date) <= cast(:RegisterListLog_endDT as date)';
			$p['RegisterListLog_endDT'] = $filter['RegisterListLog_endDT'];
		}
		if (isset($filter['RegisterListRunType_id']) && $filter['RegisterListRunType_id']) {
			$where[] = 'v_RegisterListLog.RegisterListRunType_id = :RegisterListRunType_id';
			$p['RegisterListRunType_id'] = $filter['RegisterListRunType_id'];
		}
		if (isset($filter['RegisterListLog_AllCount']) && $filter['RegisterListLog_AllCount']) {
			$where[] = 'v_RegisterListLog.RegisterListLog_AllCount = :RegisterListLog_AllCount';
			$p['RegisterListLog_AllCount'] = $filter['RegisterListLog_AllCount'];
		}
		if (isset($filter['RegisterListLog_UploadCount']) && $filter['RegisterListLog_UploadCount']) {
			$where[] = 'v_RegisterListLog.RegisterListLog_UploadCount = :RegisterListLog_UploadCount';
			$p['RegisterListLog_UploadCount'] = $filter['RegisterListLog_UploadCount'];
		}
		if (isset($filter['RegisterListResultType_id']) && $filter['RegisterListResultType_id']) {
			$where[] = 'v_RegisterListLog.RegisterListResultType_id = :RegisterListResultType_id';
			$p['RegisterListResultType_id'] = $filter['RegisterListResultType_id'];
		}
		if (isset($filter['RegisterList_id']) && $filter['RegisterList_id']) {
			$where[] = 'v_RegisterListLog.RegisterList_id = :RegisterList_id';
			$p['RegisterList_id'] = $filter['RegisterList_id'];
		}
		if ($this->getRegionNick() == 'kareliya' && isset($filter['Lpu_id']) && $filter['Lpu_id']) {
			$join[] = 'left join v_pmUserCache puc on puc.PMUser_id = v_RegisterListLog.pmUser_insID';
			$where[] = 'COALESCE(v_RegisterListLog.Server_id, puc.Lpu_id) = :Lpu_id';
			//echo $filter['Lpu_id'];
			$p['Lpu_id'] = $filter['Lpu_id'];
		}elseif ($this->getRegionNick() == 'vologda' && isset($filter['Lpu_id']) && $filter['Lpu_id']) {
            $where[] = 'v_RegisterListLog.Lpu_id = :Lpu_id';
            $p['Lpu_id'] = $filter['Lpu_id'];
        }
        $where_clause = implode(' AND ', $where);
		if (!empty($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		$q = "
			SELECT
					v_RegisterListLog.RegisterListLog_id as \"RegisterListLog_id\",
					v_RegisterListLog.RegisterListLog_begDT as \"RegisterListLog_begDT\",
					v_RegisterListLog.RegisterListLog_endDT as \"RegisterListLog_endDT\",
					v_RegisterListLog.RegisterListRunType_id as \"RegisterListRunType_id\",
					v_RegisterListLog.RegisterListLog_AllCount as \"RegisterListLog_AllCount\",
					v_RegisterListLog.RegisterListLog_UploadCount as \"RegisterListLog_UploadCount\",
					v_RegisterListLog.RegisterListResultType_id as \"RegisterListResultType_id\",
					v_RegisterListLog.RegisterList_id as \"RegisterList_id\",
					RegisterListRunType_id_ref.RegisterListRunType_Name as \"RegisterListRunType_id_Name\",
					RegisterListResultType_id_ref.RegisterListResultType_Name as \"RegisterListResultType_id_Name\",
					RegisterList_id_ref.RegisterList_Name as \"RegisterList_id_Name\",
					l.Lpu_Nick as \"Lpu_Nick\",
					v_RegisterListLog.RegisterListLog_NameFile as \"RegisterListLog_NameFile\"
			FROM
				stg.v_RegisterListLog
				LEFT JOIN stg.v_RegisterListRunType RegisterListRunType_id_ref ON RegisterListRunType_id_ref.RegisterListRunType_id = v_RegisterListLog.RegisterListRunType_id
				LEFT JOIN stg.v_RegisterListResultType RegisterListResultType_id_ref ON RegisterListResultType_id_ref.RegisterListResultType_id = v_RegisterListLog.RegisterListResultType_id
				LEFT JOIN stg.v_RegisterList RegisterList_id_ref ON RegisterList_id_ref.RegisterList_id = v_RegisterListLog.RegisterList_id
				LEFT JOIN v_Lpu l ON l.Lpu_id = v_RegisterListLog.Lpu_id
				" . (count($join) > 0 ? implode(' ', $join) : "") . "
			{$where_clause}
			ORDER BY v_RegisterListLog.RegisterListLog_id desc
			LIMIT 100
		";
		//echo getDebugSQL($q, $filter);exit();
		$result = $this->queryResult($q, $filter);

		if (is_array($result) && $filter['RegisterList_id'] == 34) {
			foreach($result as &$row) {
				if ($row['RegisterListResultType_id'] != 2 || $row['RegisterListLog_AllCount'] == 0) {
					continue;
				}

				$cnt = $this->getFirstResultFromQuery("select count(*) cnt from stg.RegisterListDetailLog where RegisterListLog_id = ?", [
						$row['RegisterListLog_id']
					]) - 1;

				if ($cnt <= 0) {
					$percent = 0;
				} elseif ($cnt >= $row['RegisterListLog_AllCount']) {
					$percent = 99;
				} else {
					$percent = floor($cnt / $row['RegisterListLog_AllCount'] * 100);
				}

				$row['RegisterListResultType_id_Name'] .= " ($percent%)";
			}
		}
		return $result;
	}
	/**
	 *
	 * @param type $value 
	 */
	function save() {
		$procedure = 'p_RegisterListLog_ins';
		if ( $this->RegisterListLog_id > 0 ) {
			$procedure = 'p_RegisterListLog_upd';
		}
		$q = "
			select 
			    RegisterListLog_id as \"RegisterListLog_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from stg." . $procedure . " (
				RegisterListLog_id := :RegisterListLog_id,
				RegisterListLog_begDT := :RegisterListLog_begDT,
				RegisterListLog_endDT := :RegisterListLog_endDT,
				RegisterListRunType_id := :RegisterListRunType_id,
				RegisterListLog_AllCount := :RegisterListLog_AllCount,
				RegisterListLog_UploadCount := :RegisterListLog_UploadCount,
				RegisterListResultType_id := :RegisterListResultType_id,
				RegisterList_id := :RegisterList_id,
				RegisterListLog_NameFile := :RegisterListLog_NameFile,
				Lpu_id := :Lpu_id,
				pmUser_id := :pmUser_id,
				Server_id := :server_id
				)
		";
		$p = array(
			'RegisterListLog_id' => (int)$this->RegisterListLog_id,
			'RegisterListLog_begDT' => $this->RegisterListLog_begDT,
			'RegisterListLog_endDT' => $this->RegisterListLog_endDT,
			'RegisterListRunType_id' => (int)$this->RegisterListRunType_id,
			'RegisterListLog_AllCount' => $this->RegisterListLog_AllCount,
			'RegisterListLog_UploadCount' => (int)$this->RegisterListLog_UploadCount,
			'RegisterListResultType_id' => (int)$this->RegisterListResultType_id,
			'RegisterList_id' => (int)$this->RegisterList_id,
			'RegisterListLog_NameFile' => (!empty($this->RegisterListLog_NameFile)) ? $this->RegisterListLog_NameFile : null,
            'Lpu_id' => (int)$this->Lpu_id,
			'pmUser_id' => (int)$this->pmUser_id,
			'server_id' => (int)$this->server_id,
		);
		//echo getDebugSQL($q, $p); die();
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
			$result = $r->result('array');
			$this->RegisterListLog_id = $result[0]['RegisterListLog_id'];
		}
		else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}
	/**
	 *
	 * @param type $value 
	 */
	function delete() {
		$q = "
		    select 
		        Error_Code as \"Error_Code\", 
		        Error_Message as \"Error_Msg\"
			from stg.p_RegisterListLog_del (
				RegisterListLog_id := :RegisterListLog_id
				)
		";
		$r = $this->db->query($q, array(
			'RegisterListLog_id' => $this->RegisterListLog_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}
}