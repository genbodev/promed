<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Связь между службами
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010-2011 Swan Ltd.
 * @author       gabdushev
 * @version
 *
 * @property CI_DB_driver $db
 */
class MedServiceLink_model extends swPgModel
{
	private $MedServiceLink_id;
	private $MedServiceLinkType_id;
	private $MedService_id;
	private $MedService_lid;
	private $pmUser_id;

	/**
	 * @return mixed
	 */
	public function getMedServiceLink_id()
	{
		return $this->MedServiceLink_id;
	}

	/**
	 * @param $value
	 */
	public function setMedServiceLink_id($value)
	{
		$this->MedServiceLink_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getMedServiceLinkType_id()
	{
		return $this->MedServiceLinkType_id;
	}

	/**
	 * @param $value
	 */
	public function setMedServiceLinkType_id($value)
	{
		$this->MedServiceLinkType_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getMedService_id()
	{
		return $this->MedService_id;
	}

	/**
	 * @param $value
	 */
	public function setMedService_id($value)
	{
		$this->MedService_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getMedService_lid()
	{
		return $this->MedService_lid;
	}

	/**
	 * @param $value
	 */
	public function setMedService_lid($value)
	{
		$this->MedService_lid = $value;
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
	 * MedServiceLink_model constructor.
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
				MedServiceLink_id as \"MedServiceLink_id\",
			    MedServiceLinkType_id as \"MedServiceLinkType_id\",
			    MedService_id as \"MedService_id\",
			    MedService_lid as \"MedService_lid\"
			from v_MedServiceLink
			where MedServiceLink_id = :MedServiceLink_id
		";
		$queryParams = ["MedServiceLink_id" => $this->MedServiceLink_id];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		if (!isset($result[0])) {
			return false;
		}
		$this->MedServiceLink_id = $result[0]["MedServiceLink_id"];
		$this->MedServiceLinkType_id = $result[0]["MedServiceLinkType_id"];
		$this->MedService_id = $result[0]["MedService_id"];
		$this->MedService_lid = $result[0]["MedService_lid"];
		return $result;
	}

	/**
	 * @param $filter
	 * @return array|bool
	 */
	function loadList($filter)
	{
		$whereArray = [];
		$queryParams = [];
		if (isset($filter["MedServiceLink_id"]) && $filter["MedServiceLink_id"]) {
			$whereArray[] = "msl.MedServiceLink_id = :MedServiceLink_id";
			$queryParams["MedServiceLink_id"] = $filter["MedServiceLink_id"];
		}
		if (isset($filter["MedServiceLinkType_id"]) && $filter["MedServiceLinkType_id"]) {
			$whereArray[] = "msl.MedServiceLinkType_id = :MedServiceLinkType_id";
			$queryParams["MedServiceLinkType_id"] = $filter["MedServiceLinkType_id"];
		}
		if (isset($filter["MedService_id"]) && $filter["MedService_id"]) {
			$whereArray[] = "msl.MedService_id = :MedService_id";
			$queryParams["MedService_id"] = $filter["MedService_id"];
		}
		// нужно отображать все пункты забора связанные со связанными службами %) (refs #15921)
		if (isset($filter["MedServiceType_SysNick"]) && isset($filter["MedServiceLinkType_id"]) && $filter["MedServiceType_SysNick"] == "reglab" && $filter["MedServiceLinkType_id"] == "1") {
			if (isset($filter["MedService_lid"]) && $filter["MedService_lid"]) {
				$whereArray[] = "
					exists(
						select MedServiceLink_id
						from v_MedServiceLink msl2
						where msl2.MedService_lid = msl.MedService_lid
						  and msl2.MedService_id = :MedService_lid
						  and msl2.MedServiceLinkType_id = 2
						limit 1
					)
				";
				$queryParams["MedService_lid"] = $filter["MedService_lid"];
			}
		} else {
			// иначе просто связанные с теущей службой.
			if (isset($filter["MedService_lid"]) && $filter["MedService_lid"]) {
				$whereArray[] = "msl.MedService_lid = :MedService_lid";
				$queryParams["MedService_lid"] = $filter["MedService_lid"];
			}
		}
		$limit1 = "";
		if (isset($filter["top1"]) && $filter["top1"]) {
			$limit1 = "limit 1";
		}
		$whereString = (count($whereArray) != 0) ? "where " . implode(" and ", $whereArray) : "";
		$selectString = "
			msl.MedServiceLink_id as \"MedServiceLink_id\",
			msl.MedServiceLinkType_id as \"MedServiceLinkType_id\",
			msl.MedService_id as \"MedService_id\",
			msl.MedService_lid as \"MedService_lid\",
			MedServiceLinkType_id_ref.MedServiceLinkType_Name as \"MedServiceLinkType_id_Name\",
			MedService_id_ref.MedService_Name as \"MedService_id_Name\",
			MedService_lid_ref.MedService_Name as \"MedService_lid_Name\",
			lab_lpu.Lpu_Nick as \"lab_lpu_Lpu_Nick\",
			pz_lpu.Lpu_Nick as \"pz_lpu_Lpu_Nick\",
			lab_type.MedServiceType_Name as \"lab_MedServiceType_Name\",
			pz_type.MedServiceType_Name as \"pz_MedServiceType_Name\",
			lab_Address.Address_Address as \"lab_Address_Address\",
			pz_Address.Address_Address as \"pz_Address_Address\"
		";
		$fromString = "
			v_MedServiceLink msl
			inner join v_MedServiceLinkType MedServiceLinkType_id_ref ON MedServiceLinkType_id_ref.MedServiceLinkType_id = msl.MedServiceLinkType_id
			inner join v_MedService MedService_id_ref ON MedService_id_ref.MedService_id = msl.MedService_id
			inner join MedServiceType pz_type ON pz_type.MedServiceType_id = MedService_id_ref.MedServiceType_id
			left join v_Lpu pz_lpu ON pz_lpu.Lpu_id = MedService_id_ref.Lpu_id
			left join v_LpuBuilding pz_build ON pz_build.LpuBuilding_id = MedService_id_ref.LpuBuilding_id
			left join v_Address pz_Address ON pz_Address.Address_id = pz_build.Address_id
			inner join v_MedService MedService_lid_ref ON MedService_lid_ref.MedService_id = msl.MedService_lid
			inner join MedServiceType lab_type ON lab_type.MedServiceType_id = MedService_lid_ref.MedServiceType_id
			left join v_Lpu lab_lpu ON lab_lpu.Lpu_id = MedService_lid_ref.Lpu_id
			left join v_LpuBuilding lab_build ON lab_build.LpuBuilding_id = MedService_lid_ref.LpuBuilding_id
			left join v_Address lab_Address ON lab_Address.Address_id = lab_build.Address_id
		";
		$query = "
			select {$selectString}
			from {$fromString}
			{$whereString}
			{$limit1}
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
		$procedure = ($this->MedServiceLink_id == 0) ? "p_MedServiceLink_ins" : "p_MedServiceLink_upd";
		$selectString = "
		    medservicelink_id as \"MedServiceLink_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    medservicelink_id := :MedServiceLink_id,
			    medservicelinktype_id := :MedServiceLinkType_id,
			    medservice_id := :MedService_id,
			    medservice_lid := :MedService_lid,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"MedServiceLink_id" => $this->MedServiceLink_id,
			"MedServiceLinkType_id" => $this->MedServiceLinkType_id,
			"MedService_id" => $this->MedService_id,
			"MedService_lid" => $this->MedService_lid,
			"pmUser_id" => $this->pmUser_id,
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$result = $result->result("array");
		$this->MedServiceLink_id = $result[0]["MedServiceLink_id"];
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
			    error_message as \"Error_Msg\"
			from p_medservicelink_del(medservicelink_id := :MedServiceLink_id);
		";
		$queryParams = ["MedServiceLink_id" => $this->MedServiceLink_id];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}
}