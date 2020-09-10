<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Таблица регистров/справочников доступных для загрузки
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010-2011 Swan Ltd.
 * @author       gabdushev
 * @version
 *
 * @property CI_DB_driver $db
 */
class RegisterList_model extends swPgModel
{
	private $RegisterList_id;//RegisterList_id
	private $RegisterList_Name;//название основной таблицы в БД
	private $RegisterList_Schema;//схема БД
	private $RegisterList_Descr;//Описание справочника
	private $Region_id;//Идентификатор региона справочника территорий
	private $pmUser_id;//Идентификатор пользователя системы Промед

	/**
	 * @return mixed
	 */
	public function getRegisterList_id()
	{
		return $this->RegisterList_id;
	}

	/**
	 * @param $value
	 */
	public function setRegisterList_id($value)
	{
		$this->RegisterList_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getRegisterList_Name()
	{
		return $this->RegisterList_Name;
	}

	/**
	 * @param $value
	 */
	public function setRegisterList_Name($value)
	{
		$this->RegisterList_Name = $value;
	}

	/**
	 * @return mixed
	 */
	public function getRegisterList_Schema()
	{
		return $this->RegisterList_Schema;
	}

	/**
	 * @param $value
	 */
	public function setRegisterList_Schema($value)
	{
		$this->RegisterList_Schema = $value;
	}

	/**
	 * @return mixed
	 */
	public function getRegisterList_Descr()
	{
		return $this->RegisterList_Descr;
	}

	/**
	 * @param $value
	 */
	public function setRegisterList_Descr($value)
	{
		$this->RegisterList_Descr = $value;
	}

	/**
	 * @return mixed
	 */
	public function getRegion_id()
	{
		return $this->Region_id;
	}

	/**
	 * @param $value
	 */
	public function setRegion_id($value)
	{
		$this->Region_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getpmUser_id()
	{
		return $this->pmUser_id;
	}

	/**
	 * @param $value
	 */
	public function setpmUser_id($value)
	{
		$this->pmUser_id = $value;
	}

	/**
	 * @throws Exception
	 */
	function __construct()
	{
		parent::__construct();
		if (!isset($_SESSION["pmuser_id"])) {
			throw new Exception("Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)");
		}
		$this->setpmUser_id($_SESSION["pmuser_id"]);
	}

	/**
	 * @return array|bool|CI_DB_result
	 */
	function load()
	{
		$query = "
			select
				RegisterList_id as \"RegisterList_id\",
			    RegisterList_Name as \"RegisterList_Name\",
			    RegisterList_Schema as \"RegisterList_Schema\",
			    RegisterList_Descr as \"RegisterList_Descr\",
			    Region_id as \"Region_id\"
			from stg.v_RegisterList
			where RegisterList_id = :RegisterList_id
		";
		$queryParams = ["RegisterList_id" => $this->RegisterList_id];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		if (!isset($result[0])) {
			return false;
		}
		$this->RegisterList_id = $result[0]["RegisterList_id"];
		$this->RegisterList_Name = $result[0]["RegisterList_Name"];
		$this->RegisterList_Schema = $result[0]["RegisterList_Schema"];
		$this->RegisterList_Descr = $result[0]["RegisterList_Descr"];
		$this->Region_id = $result[0]["Region_id"];
		return $result;
	}

	/**
	 * @param $filter
	 * @return array|bool
	 */
	function loadList($filter)
	{
		$where = [];
		$queryParams = [];
		if (isset($filter["RegisterList_id"]) && $filter["RegisterList_id"]) {
			$where[] = "v_RegisterList.RegisterList_id = :RegisterList_id";
			$queryParams["RegisterList_id"] = $filter["RegisterList_id"];
		}
		if (isset($filter["RegisterList_Name"]) && $filter["RegisterList_Name"]) {
			$where[] = "v_RegisterList.RegisterList_Name = :RegisterList_Name";
			$queryParams["RegisterList_Name"] = $filter["RegisterList_Name"];
		}
		if (isset($filter["RegisterList_Schema"]) && $filter["RegisterList_Schema"]) {
			$where[] = "v_RegisterList.RegisterList_Schema = :RegisterList_Schema";
			$queryParams["RegisterList_Schema"] = $filter["RegisterList_Schema"];
		}
		if (isset($filter["RegisterList_Descr"]) && $filter["RegisterList_Descr"]) {
			$where[] = "v_RegisterList.RegisterList_Descr = :RegisterList_Descr";
			$queryParams["RegisterList_Descr"] = $filter["RegisterList_Descr"];
		}
		if (isset($filter["Region_id"]) && $filter["Region_id"]) {
			$where[] = "v_RegisterList.Region_id = :Region_id";
			$queryParams["Region_id"] = $filter["Region_id"];
		}
		$whereString = (count($where) != 0)?"where ".implode(" and ", $where) : "";
		$query = "
			select
				v_RegisterList.RegisterList_id as \"RegisterList_id\",
			    v_RegisterList.RegisterList_Name as \"RegisterList_Name\",
			    v_RegisterList.RegisterList_Schema as \"RegisterList_Schema\",
			    v_RegisterList.RegisterList_Descr as \"RegisterList_Descr\",
			    v_RegisterList.Region_id as \"Region_id\",
			    Region_id_ref.KLArea_Name as \"Region_id_Name\"
			from
				stg.v_RegisterList
				left join v_KLArea Region_id_ref on Region_id_ref.KLArea_id = v_RegisterList.Region_id
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @return array|CI_DB_result
	 * @throws Exception
	 */
	function save()
	{
		$procedure = ($this->RegisterList_id > 0) ? "p_RegisterList_upd" : "p_RegisterList_ins";
		$selectString = "
		    registerlist_id as \"RegisterList_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from stg.{$procedure}(
			    registerlist_id := :RegisterList_id,
			    registerlist_name := :RegisterList_Name,
			    registerlist_schema := :RegisterList_Schema,
			    registerlist_descr := :RegisterList_Descr,
			    region_id := :Region_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"RegisterList_id" => $this->RegisterList_id,
			"RegisterList_Name" => $this->RegisterList_Name,
			"RegisterList_Schema" => $this->RegisterList_Schema,
			"RegisterList_Descr" => $this->RegisterList_Descr,
			"Region_id" => $this->Region_id,
			"pmUser_id" => $this->pmUser_id,
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$result = $result->result("array");
		$this->RegisterList_id = $result[0]["RegisterList_id"];
		return $result;
	}

	/**
	 * @return array|bool
	 */
	function delete()
	{
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from stg.p_registerlist_del(registerlist_id := :RegisterList_id);
		";
		$queryParams = ["RegisterList_id" => $this->RegisterList_id];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}
}