<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Вид медицинской помощи
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010-2011 Swan Ltd.
 * @author       gabdushev
 * @version
 *
 * @property CI_DB_driver $db
 */
class MedicalCareKind_model extends swPgModel
{
	private $MedicalCareKind_id;//идентификатор
	private $MedicalCareKind_Code;//код
	private $MedicalCareKind_Name;//наименование
	private $MedicalCareKind_begDate;//MedicalCareKind_begDate
	private $MedicalCareKind_endDate;//MedicalCareKind_endDate
	private $pmUser_id;//Идентификатор пользователя системы Промед

	/**
	 * Возвращает идентификатор вида медицинсокй помощи
	 * @return mixed
	 */
	public function getMedicalCareKind_id()
	{
		return $this->MedicalCareKind_id;
	}

	/**
	 * Присваивает идентификатор вида медицинсокй помощ
	 * @param $value
	 */
	public function setMedicalCareKind_id($value)
	{
		$this->MedicalCareKind_id = $value;
	}

	/**
	 * Возвращает код вида медицинсокй помощи
	 * @return mixed
	 */
	public function getMedicalCareKind_Code()
	{
		return $this->MedicalCareKind_Code;
	}

	/**
	 * Присваивает код вида медицинсокй помощи
	 * @param $value
	 */
	public function setMedicalCareKind_Code($value)
	{
		$this->MedicalCareKind_Code = $value;
	}

	/**
	 * Возвращает наименование вида медицинсокй помощи
	 * @return mixed
	 */
	public function getMedicalCareKind_Name()
	{
		return $this->MedicalCareKind_Name;
	}

	/**
	 * Присваивает наименование вида медицинсокй помощи
	 * @param $value
	 */
	public function setMedicalCareKind_Name($value)
	{
		$this->MedicalCareKind_Name = $value;
	}

	/**
	 * Возвращает дату начала действия вида медицинсокй помощи
	 * @return mixed
	 */
	public function getMedicalCareKind_begDate()
	{
		return $this->MedicalCareKind_begDate;
	}

	/**
	 * Присваивает дату начала действия вида медицинсокй помощи
	 * @param $value
	 */
	public function setMedicalCareKind_begDate($value)
	{
		$this->MedicalCareKind_begDate = $value;
	}

	/**
	 * Возвращает дату окончания действия вида медицинсокй помощи
	 * @return mixed
	 */
	public function getMedicalCareKind_endDate()
	{
		return $this->MedicalCareKind_endDate;
	}

	/**
	 * Присваивает дату окончания действия вида медицинсокй помощи
	 * @param $value
	 */
	public function setMedicalCareKind_endDate($value)
	{
		$this->MedicalCareKind_endDate = $value;
	}

	/**
	 * Возвращает идентификатор пользователя
	 * @return mixed
	 */
	public function getpmUser_id()
	{
		return $this->pmUser_id;
	}

	/**
	 * Присваивает идентификатор пользователя
	 * @param $value
	 */
	public function setpmUser_id($value)
	{
		$this->pmUser_id = $value;
	}

	/**
	 * MedicalCareKind_model constructor.
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
	 * Возвращает список
	 * @param $filter
	 * @return array|bool
	 */
	function loadList($filter)
	{
		$where = [];
		$queryParams = [];
		if (isset($filter["MedicalCareKind_id"]) && $filter["MedicalCareKind_id"]) {
			$where[] = "v_MedicalCareKind.MedicalCareKind_id = :MedicalCareKind_id";
			$queryParams["MedicalCareKind_id"] = $filter["MedicalCareKind_id"];
		}
		if (isset($filter["MedicalCareKind_Code"]) && $filter["MedicalCareKind_Code"]) {
			$where[] = "v_MedicalCareKind.MedicalCareKind_Code = :MedicalCareKind_Code";
			$queryParams["MedicalCareKind_Code"] = $filter["MedicalCareKind_Code"];
		}
		if (isset($filter["MedicalCareKind_Name"]) && $filter["MedicalCareKind_Name"]) {
			$where[] = "v_MedicalCareKind.MedicalCareKind_Name = :MedicalCareKind_Name";
			$queryParams["MedicalCareKind_Name"] = $filter["MedicalCareKind_Name"];
		}
		if (isset($filter["MedicalCareKind_begDate"]) && $filter["MedicalCareKind_begDate"]) {
			$where[] = "v_MedicalCareKind.MedicalCareKind_begDate = :MedicalCareKind_begDate";
			$queryParams["MedicalCareKind_begDate"] = $filter["MedicalCareKind_begDate"];
		}
		if (isset($filter["MedicalCareKind_endDate"]) && $filter["MedicalCareKind_endDate"]) {
			$where[] = "v_MedicalCareKind.MedicalCareKind_endDate = :MedicalCareKind_endDate";
			$queryParams["MedicalCareKind_endDate"] = $filter["MedicalCareKind_endDate"];
		}
		$whereString = (count($where) != 0) ? "where " . implode(" and ", $where) : "";
		$query = "
			select
				v_MedicalCareKind.MedicalCareKind_id as \"MedicalCareKind_id\",
				v_MedicalCareKind.MedicalCareKind_Code as \"MedicalCareKind_Code\",
			    v_MedicalCareKind.MedicalCareKind_Name as \"MedicalCareKind_Name\",
			    v_MedicalCareKind.MedicalCareKind_begDate as \"MedicalCareKind_begDate\",
			    v_MedicalCareKind.MedicalCareKind_endDate as \"MedicalCareKind_endDate\",
				'folder' as \"cls\",
				'uslugacomplex-16' as \"iconCls\",
				v_MedicalCareKind.MedicalCareKind_id as \"id\",
				1 as \"leaf\",
				v_MedicalCareKind.MedicalCareKind_Name as \"text\",
		        case when v_MedicalCareKind.MedicalCareKind_Code=6 then 1 else 0 end as \"stomat\"
			from v_MedicalCareKind
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Возвращает список видов медицинской помощи
	 * @param $filter
	 * @return array|bool
	 */
	function loadFedMedicalCareKindList($filter)
	{
		$where = [];
		$queryParams = [];
		if (isset($filter["MedicalCareKind_id"]) && $filter["MedicalCareKind_id"]) {
			$where[] = "MCK.MedicalCareKind_id = :MedicalCareKind_id";
			$queryParams["MedicalCareKind_id"] = $filter["MedicalCareKind_id"];
		}
		if (isset($filter["MedicalCareKind_Code"]) && $filter["MedicalCareKind_Code"]) {
			$where[] = "MCK.MedicalCareKind_Code = :MedicalCareKind_Code";
			$queryParams["MedicalCareKind_Code"] = $filter["MedicalCareKind_Code"];
		}
		if (isset($filter["MedicalCareKind_Name"]) && $filter["MedicalCareKind_Name"]) {
			$where[] = "MCK.MedicalCareKind_Name = :MedicalCareKind_Name";
			$queryParams["MedicalCareKind_Name"] = $filter["MedicalCareKind_Name"];
		}
		if (isset($filter["MedicalCareKind_begDate"]) && $filter["MedicalCareKind_begDate"]) {
			$where[] = "MCK.MedicalCareKind_begDate = :MedicalCareKind_begDate";
			$queryParams["MedicalCareKind_begDate"] = $filter["MedicalCareKind_begDate"];
		}
		if (isset($filter["MedicalCareKind_endDate"]) && $filter["MedicalCareKind_endDate"]) {
			$where[] = "MCK.MedicalCareKind_endDate = :MedicalCareKind_endDate";
			$queryParams["MedicalCareKind_endDate"] = $filter["MedicalCareKind_endDate"];
		}
		$whereString = (count($where) != 0) ? "where " . implode(" and ", $where) : "";
		$query = "
			select
				MCK.MedicalCareKind_id as \"MedicalCareKind_id\",
				MCK.MedicalCareKind_Code as \"MedicalCareKind_Code\",
				MCK.MedicalCareKind_Name as \"MedicalCareKind_Name\"
			from nsi.MedicalCareKind MCK
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Получение списка видов медицинской помощи
	 * @param $filter
	 * @return array|bool
	 */
	function loadMedicalCareKindList($filter)
	{
		$where = "";
		if (!empty($filter["MedicalCareKind_Code"])) {
			$where .= " and MedicalCareKind_Code = :MedicalCareKind_Code";
		}
		$query = "
			select
				MCK.MedicalCareKind_id as \"MedicalCareKind_id\",
				MCK.MedicalCareKind_Code as \"MedicalCareKind_Code\",
				MCK.MedicalCareKind_Name as \"MedicalCareKind_Name\",
				MCK.MedicalCareKind_SysNick as \"MedicalCareKind_SysNick\"
			from v_MedicalCareKind MCK
			where (MCK.MedicalCareKind_endDate is null or MCK.MedicalCareKind_endDate < tzgetdate())
			  {$where}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $filter);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Получение списка форм оказания мед. помощи
	 * @return array|false
	 */
	function loadMedicalCareFormTypeList()
	{
		$query = "
			select
				MedicalCareFormType_id as \"MedicalCareFormType_id\",
				MedicalCareFormType_Name as \"MedicalCareFormType_Name\",
				MedicalCareFormType_Code as \"MedicalCareFormType_Code\"
			from fed.v_MedicalCareFormType
		";
		return $this->queryResult($query);
	}
}