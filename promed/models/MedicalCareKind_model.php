<?php defined('BASEPATH') or die ('No direct script access allowed');
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
 */
class MedicalCareKind_model extends swModel {
	private $MedicalCareKind_id;//идентификатор
	private $MedicalCareKind_Code;//код
	private $MedicalCareKind_Name;//наименование
	private $MedicalCareKind_begDate;//MedicalCareKind_begDate
	private $MedicalCareKind_endDate;//MedicalCareKind_endDate
	private $pmUser_id;//Идентификатор пользователя системы Промед

	/**
	 * Возвращает идентификатор вида медицинсокй помощи
	 */
	public function getMedicalCareKind_id() { return $this->MedicalCareKind_id;}
	/**
	 * Присваивает идентификатор вида медицинсокй помощи
	 */
	public function setMedicalCareKind_id($value) { $this->MedicalCareKind_id = $value; }

	/**
	 * Возвращает код вида медицинсокй помощи
	 */
	public function getMedicalCareKind_Code() { return $this->MedicalCareKind_Code;}
	/**
	 * Присваивает код вида медицинсокй помощи
	 */
	public function setMedicalCareKind_Code($value) { $this->MedicalCareKind_Code = $value; }

	/**
	 * Возвращает наименование вида медицинсокй помощи
	 */
	public function getMedicalCareKind_Name() { return $this->MedicalCareKind_Name;}
	/**
	 * Присваивает наименование вида медицинсокй помощи
	 */
	public function setMedicalCareKind_Name($value) { $this->MedicalCareKind_Name = $value; }

	/**
	 * Возвращает дату начала действия вида медицинсокй помощи
	 */
	public function getMedicalCareKind_begDate() { return $this->MedicalCareKind_begDate;}
	/**
	 * Присваивает дату начала действия вида медицинсокй помощи
	 */
	public function setMedicalCareKind_begDate($value) { $this->MedicalCareKind_begDate = $value; }

	/**
	 * Возвращает дату окончания действия вида медицинсокй помощи
	 */
	public function getMedicalCareKind_endDate() { return $this->MedicalCareKind_endDate;}
	/**
	 * Присваивает дату окончания действия вида медицинсокй помощи
	 */
	public function setMedicalCareKind_endDate($value) { $this->MedicalCareKind_endDate = $value; }

	/**
	 * Возвращает идентификатор пользователя
	 */
	public function getpmUser_id() { return $this->pmUser_id;}
	/**
	 * Присваивает идентификатор пользователя
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
	 * Возвращает список
	 */
	function loadList($filter) {
		$where = array();
		$p = array();
		if (isset($filter['MedicalCareKind_id']) && $filter['MedicalCareKind_id']) {
			$where[] = 'v_MedicalCareKind.MedicalCareKind_id = :MedicalCareKind_id';
			$p['MedicalCareKind_id'] = $filter['MedicalCareKind_id'];
		}
		if (isset($filter['MedicalCareKind_Code']) && $filter['MedicalCareKind_Code']) {
			$where[] = 'v_MedicalCareKind.MedicalCareKind_Code = :MedicalCareKind_Code';
			$p['MedicalCareKind_Code'] = $filter['MedicalCareKind_Code'];
		}
		if (isset($filter['MedicalCareKind_Name']) && $filter['MedicalCareKind_Name']) {
			$where[] = 'v_MedicalCareKind.MedicalCareKind_Name = :MedicalCareKind_Name';
			$p['MedicalCareKind_Name'] = $filter['MedicalCareKind_Name'];
		}
		if (isset($filter['MedicalCareKind_begDate']) && $filter['MedicalCareKind_begDate']) {
			$where[] = 'v_MedicalCareKind.MedicalCareKind_begDate = :MedicalCareKind_begDate';
			$p['MedicalCareKind_begDate'] = $filter['MedicalCareKind_begDate'];
		}
		if (isset($filter['MedicalCareKind_endDate']) && $filter['MedicalCareKind_endDate']) {
			$where[] = 'v_MedicalCareKind.MedicalCareKind_endDate = :MedicalCareKind_endDate';
			$p['MedicalCareKind_endDate'] = $filter['MedicalCareKind_endDate'];
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		$q = "
			SELECT
				v_MedicalCareKind.MedicalCareKind_id, v_MedicalCareKind.MedicalCareKind_Code, v_MedicalCareKind.MedicalCareKind_Name, v_MedicalCareKind.MedicalCareKind_begDate, v_MedicalCareKind.MedicalCareKind_endDate
				,'folder' as cls
				,'uslugacomplex-16' as iconCls
				,v_MedicalCareKind.MedicalCareKind_id as id
				,1 as leaf
				,v_MedicalCareKind.MedicalCareKind_Name as text
		        ,case when v_MedicalCareKind.MedicalCareKind_Code=6 then 1 else 0 end as stomat
			FROM
				dbo.v_MedicalCareKind WITH (NOLOCK)
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
	 * Возвращает список видов медицинской помощи
	 */
	function loadFedMedicalCareKindList($filter) {
		$where = array();
		$p = array();
		if (isset($filter['MedicalCareKind_id']) && $filter['MedicalCareKind_id']) {
			$where[] = 'MCK.MedicalCareKind_id = :MedicalCareKind_id';
			$p['MedicalCareKind_id'] = $filter['MedicalCareKind_id'];
		}
		if (isset($filter['MedicalCareKind_Code']) && $filter['MedicalCareKind_Code']) {
			$where[] = 'MCK.MedicalCareKind_Code = :MedicalCareKind_Code';
			$p['MedicalCareKind_Code'] = $filter['MedicalCareKind_Code'];
		}
		if (isset($filter['MedicalCareKind_Name']) && $filter['MedicalCareKind_Name']) {
			$where[] = 'MCK.MedicalCareKind_Name = :MedicalCareKind_Name';
			$p['MedicalCareKind_Name'] = $filter['MedicalCareKind_Name'];
		}
		if (isset($filter['MedicalCareKind_begDate']) && $filter['MedicalCareKind_begDate']) {
			$where[] = 'MCK.MedicalCareKind_begDate = :MedicalCareKind_begDate';
			$p['MedicalCareKind_begDate'] = $filter['MedicalCareKind_begDate'];
		}
		if (isset($filter['MedicalCareKind_endDate']) && $filter['MedicalCareKind_endDate']) {
			$where[] = 'MCK.MedicalCareKind_endDate = :MedicalCareKind_endDate';
			$p['MedicalCareKind_endDate'] = $filter['MedicalCareKind_endDate'];
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		$q = "
			SELECT
				MCK.MedicalCareKind_id,
				MCK.MedicalCareKind_Code,
				MCK.MedicalCareKind_Name
			FROM
				nsi.MedicalCareKind MCK with(nolock)
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
	 * Получение списка видов медицинской помощи
	 */
	function loadMedicalCareKindList($filter) {
		$where = "";
		$params = array();
		if (!empty($filter['MedicalCareKind_Code'])) {
			$where .= " and MedicalCareKind_Code = :MedicalCareKind_Code";
		}
		$query = "
			select
				MCK.MedicalCareKind_id,
				MCK.MedicalCareKind_Code,
				MCK.MedicalCareKind_Name,
				MCK.MedicalCareKind_SysNick
			from
				v_MedicalCareKind MCK with(nolock)
			where
				(MCK.MedicalCareKind_endDate is null or MCK.MedicalCareKind_endDate < dbo.tzGetDate())
				{$where}
		";
		$result = $this->db->query($query, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение списка форм оказания мед. помощи
	 */
	function loadMedicalCareFormTypeList() {
		return $this->queryResult("
		SELECT
			MedicalCareFormType_id,
			MedicalCareFormType_Name,
			MedicalCareFormType_Code
		FROM fed.v_MedicalCareFormType");
	}
}