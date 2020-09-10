<?php

/**
 * Class OrgStruct_model
 * @property CI_DB_driver $db
 */
class OrgStruct_model extends swPgModel
{
	private $dateTimeForm104 = "DD.MM.YYYY";
	
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Читает список расчётных счетов организации
	 * @param $data
	 * @return array|bool
	 */
	function loadOrgRSchetGrid($data)
	{
		$query = "
			select
			-- select
				ORS.OrgRSchet_id as \"OrgRSchet_id\",
				ORS.OrgRSchet_RSchet as \"OrgRSchet_RSchet\",
				ORST.OrgRSchetType_Name as \"OrgRSchetType_Name\",
				OB.OrgBank_Name as \"OrgBank_Name\",
				to_char(ORS.OrgRSchet_begDate, '{$this->dateTimeForm104}') as \"OrgRSchet_begDate\",
				to_char(ORS.OrgRSchet_endDate, '{$this->dateTimeForm104}') as \"OrgRSchet_endDate\",
				OKV.Okv_Nick as \"Okv_Nick\",
				ORS.OrgRSchet_Name as \"OrgRSchet_Name\"
			-- end select
			from
			-- from
				v_OrgRSchet ORS
				left join v_OrgBank OB on OB.OrgBank_id = ORS.OrgBank_id
				left join v_OrgRSchetType ORST on ORST.OrgRSchetType_id = ORS.OrgRSchetType_id
				left join v_Okv OKV on OKV.Okv_id = ORS.Okv_id
			-- end from
			where
			-- where
				ORS.Org_id = :Org_id
			-- end where
			order by
			-- order by
				ORS.OrgRSchet_RSchet
			-- end order by
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_count
		 */
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

		if (is_object($result_count)) {
			$cnt_arr = $result_count->result("array");
			$count = $cnt_arr[0]["cnt"];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		if (!is_object($result)) {
			return false;
		}
		$response = [];
		$response["data"] = $result->result("array");
		$response["totalCount"] = $count;
		return $response;
	}

	/**
	 * Читает список контактных лиц организации
	 * @param $data
	 * @return array|bool
	 */
	function loadOrgHeadGrid($data)
	{
		$query = "
			select
			-- select
				OH.OrgHead_id as \"OrgHead_id\",
				rtrim(ltrim(coalesce(PS.Person_Surname, '')||' '||coalesce(PS.Person_Firname, '')||' '||coalesce(PS.Person_Secname, ''))) as \"OrgHead_Fio\",
				OHP.OrgHeadPost_Name as \"OrgHeadPost_Name\",
				OH.OrgHead_Phone as \"OrgHead_Phone\",
				OH.OrgHead_Mobile as \"OrgHead_Mobile\"
			-- end select
			from
			-- from
				v_OrgHead OH
				left join v_PersonState PS on PS.Person_id = OH.Person_id
				left join v_OrgHeadPost OHP on OHP.OrgHeadPost_id = OH.OrgHeadPost_id
			-- end from
			where
			-- where
				OH.Org_id = :Org_id
			-- end where
			order by
			-- order by
				OHP.OrgHeadPost_Name
			-- end order by
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_count
		 */
		$result = $this->db->query(getLimitSQLPH($query, $data["start"], $data["limit"]), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);
		if (is_object($result_count)) {
			$cnt_arr = $result_count->result("array");
			$count = $cnt_arr[0]["cnt"];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		if (!is_object($result)) {
			return false;
		}
		$response = [];
		$response["data"] = $result->result("array");
		$response["totalCount"] = $count;
		return $response;
	}

	/**
	 * Читает список лицензий организации
	 * @param $data
	 * @return array|bool
	 */
	function loadOrgLicenceGrid($data)
	{
		$query = "
			select
			-- select
				OL.OrgLicence_id as \"OrgLicence_id\",
				OL.OrgLicence_Num as \"OrgLicence_Num\",
				to_char(OL.OrgLicence_setDate, '{$this->dateTimeForm104}') as \"OrgLicence_setDate\",
				OL.OrgLicence_RegNum as \"OrgLicence_RegNum\",
				to_char(OL.OrgLicence_begDate, '{$this->dateTimeForm104}') as \"OrgLicence_begDate\",
				to_char(OL.OrgLicence_endDate, '{$this->dateTimeForm104}') as \"OrgLicence_endDate\"
			-- end select
			from
			-- from
				v_OrgLicence OL
			-- end from
			where
			-- where
				OL.Org_id = :Org_id
			-- end where
			order by
			-- order by
				OL.OrgLicence_Num
			-- end order by
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_count
		 */
		$result = $this->db->query(getLimitSQLPH($query, $data["start"], $data["limit"]), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);
		if (is_object($result_count)) {
			$cnt_arr = $result_count->result("array");
			$count = $cnt_arr[0]["cnt"];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		if (!is_object($result)) {
			return false;
		}
		$response = [];
		$response["data"] = $result->result("array");
		$response["totalCount"] = $count;
		return $response;
	}

	/**
	 * Проверка является ли организация филиалом другой организации
	 * @param $data
	 * @return bool
	 */
	function checkOrgFilialExist($data)
	{
		$query = "
			select O.Org_id as \"Org_id\"
			from v_Org O
			where O.Org_id = :OrgFilial_id
			  and (O.Org_pid = :Org_id or O.Org_pid is null)
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result("array");
			if (count($resp) > 0) {
				// Org_pid заполнено редактируемой организацией или пустое.
				return false;
			}
		}
		// Org_pid заполнено не редактируемой организацией
		return true;
	}

	/**
	 * Сохранение филиала организации
	 * @param $data
	 * @return array
	 */
	function saveOrgFilial($data)
	{
		$query = "
			update Org
			set Org_pid = :Org_id
			where Org_id = :OrgFilial_id
		";
		/**@var CI_DB_result $result */
		$this->db->query($query, $data);
		return [["OrgFilial_id" => $data["OrgFilial_id"], "Error_Code" => "", "Error_Msg" => ""]];
	}

	/**
	 * Читает список филиалов организации
	 * @param $data
	 * @return array|bool
	 */
	function loadOrgFilialGrid($data)
	{
		$query = "
			select
			-- select
				O.Org_id as \"OrgFilial_id\",
				O.Org_Name as \"OrgFilial_Name\"
			-- end select
			from
			-- from
				v_Org O
			-- end from
			where
			-- where
				O.Org_pid = :Org_id
			-- end where
			order by
			-- order by
				O.Org_Name
			-- end order by
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_count
		 */
		$result = $this->db->query(getLimitSQLPH($query, $data["start"], $data["limit"]), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);
		if (is_object($result_count)) {
			$cnt_arr = $result_count->result("array");
			$count = $cnt_arr[0]["cnt"];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		if (!is_object($result)) {
			return false;
		}
		$response = [];
		$response["data"] = $result->result("array");
		$response["totalCount"] = $count;
		return $response;
	}

	/**
	 * Сохранение лицензии организации
	 * @param $data
	 * @return array|bool
	 */
	function saveOrgLicence($data)
	{
		$procedure = (empty($data["OrgLicence_id"])) ? "p_OrgLicence_ins" : "p_OrgLicence_upd";
		$selectString = "
		    orglicence_id as \"OrgLicence_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    server_id := :Server_id,
			    orglicence_id := :OrgLicence_id,
			    org_id := :Org_id,
			    orglicence_ser := :OrgLicence_Ser,
			    orglicence_num := :OrgLicence_Num,
			    orglicence_setdate := :OrgLicence_setDate,
			    orglicence_regnum := :OrgLicence_RegNum,
			    orglicence_begdate := :OrgLicence_begDate,
			    orglicence_enddate := :OrgLicence_endDate,
			    org_did := :Org_did,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Сохранение контактного лица
	 * @param $data
	 * @return array|bool
	 */
	function saveOrgHead($data)
	{
		$procedure = (empty($data['OrgHead_id'])) ? "p_OrgHead_ins" : "p_OrgHead_upd";
		$selectString = "
		    orghead_id as \"OrgHead_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    server_id := :Server_id,
			    orghead_id := :OrgHead_id,
			    lpu_id := null,
			    person_id := :Person_id,
			    orgheadpost_id := :OrgHeadPost_id,
			    orghead_phone := :OrgHead_Phone,
			    orghead_mobile := :OrgHead_Mobile,
			    orghead_fax := :OrgHead_Fax,
			    orghead_email := :OrgHead_Email,
			    orghead_commissdate := :OrgHead_CommissDate,
			    orghead_commissnum := :OrgHead_CommissNum,
			    orghead_address := :OrgHead_Address,
			    org_id := :Org_id,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Сохранение структурного уровня
	 * @param $data
	 * @return array|bool
	 */
	function saveOrgStruct($data)
	{
		$procedure = (empty($data["OrgStruct_id"])) ? "p_OrgStruct_ins" : "p_OrgStruct_upd";
		$selectString = "
		    orgstruct_id as \"OrgStruct_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    orgstruct_id := :OrgStruct_id,
			    org_id := :Org_id,
			    orgstruct_pid := :OrgStruct_pid,
			    orgstruct_numlevel := :OrgStruct_NumLevel,
			    orgstruct_code := :OrgStruct_Code,
			    orgstruct_name := :OrgStruct_Name,
			    orgstruct_nick := :OrgStruct_Nick,
			    orgstruct_begdt := :OrgStruct_begDT,
			    orgstruct_enddt := :OrgStruct_endDT,
			    orgstruct_leftnum := :OrgStruct_LeftNum,
			    orgstruct_rightnum := :OrgStruct_RightNum,
			    orgstructleveltype_id := :OrgStructLevelType_id,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Чтение формы контактного лица
	 * @param $data
	 * @return array|bool
	 */
	function loadOrgHeadEditForm($data)
	{
		$query = "
			select
				OH.OrgHead_id as \"OrgHead_id\",
				OH.Person_id as \"Person_id\",
				OH.OrgHeadPost_id as \"OrgHeadPost_id\",
				OH.OrgHead_Phone as \"OrgHead_Phone\",
				OH.OrgHead_Mobile as \"OrgHead_Mobile\",
				OH.OrgHead_Fax as \"OrgHead_Fax\",
				OH.OrgHead_Email as \"OrgHead_Email\",
				OH.OrgHead_CommissNum as \"OrgHead_CommissNum\",
				OH.OrgHead_Address as \"OrgHead_Address\",
				OH.Org_id as \"Org_id\",
				to_char(OH.OrgHead_CommissDate, '{$this->dateTimeForm104}') as \"OrgHead_CommissDate\"
			from
				v_OrgHead OH
			where OH.OrgHead_id = :OrgHead_id
			limit 1
		";
		$queryParams = ["OrgHead_id" => $data["OrgHead_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Чтение формы лицензии организации
	 * @param $data
	 * @return array|bool
	 */
	function loadOrgLicenceEditForm($data)
	{
		$query = "
			select
				OL.OrgLicence_id as \"OrgLicence_id\",
				OL.Org_id as \"Org_id\",
				OL.OrgLicence_Ser as \"OrgLicence_Ser\",
				OL.OrgLicence_Num as \"OrgLicence_Num\",
				OL.OrgLicence_RegNum as \"OrgLicence_RegNum\",
				OL.Org_did as \"Org_did\",
				to_char(OL.OrgLicence_setDate, '{$this->dateTimeForm104}') as \"OrgLicence_setDate\",
				to_char(OL.OrgLicence_begDate, '{$this->dateTimeForm104}') as \"OrgLicence_begDate\",
				to_char(OL.OrgLicence_endDate, '{$this->dateTimeForm104}') as \"OrgLicence_endDate\"
			from
				v_OrgLicence OL
			where OL.OrgLicence_id = :OrgLicence_id
			limit 1
		";
		$queryParams = ["OrgLicence_id" => $data["OrgLicence_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Чтение формы структурного уровня
	 * @param $data
	 * @return array|bool
	 */
	function loadOrgStructEditForm($data)
	{
		$query = "
			select
				OS.OrgStruct_id as \"OrgStruct_id\",
				OS.Org_id as \"Org_id\",
				OS.OrgStruct_pid as \"OrgStruct_pid\",
				OS.OrgStruct_NumLevel as \"OrgStruct_NumLevel\",
				OS.OrgStruct_Name as \"OrgStruct_Name\",
				OS.OrgStruct_Nick as \"OrgStruct_Nick\",
				OS.OrgStruct_Code as \"OrgStruct_Code\",
				OS.OrgStructLevelType_id as \"OrgStructLevelType_id\",
				OS.OrgStruct_LeftNum as \"OrgStruct_LeftNum\",
				OS.OrgStruct_RightNum as \"OrgStruct_RightNum\",
				to_char(OS.OrgStruct_begDT, '{$this->dateTimeForm104}') as \"OrgStruct_begDT\",
				to_char(OS.OrgStruct_endDT, '{$this->dateTimeForm104}') as \"OrgStruct_endDT\"
			from v_OrgStruct OS
			where OS.OrgStruct_id = :OrgStruct_id
			limit 1
		";
		$queryParams = ["OrgStruct_id" => $data["OrgStruct_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Чтение формы типа службы
	 * @param $data
	 * @return array|bool
	 */
	function loadOrgServiceTypeEditForm($data)
	{
		$query = "
			select
				OST.OrgServiceType_id as \"OrgServiceType_id\",
				OST.OrgServiceType_Name as \"OrgServiceType_Name\",
				OST.OrgServiceType_Nick as \"OrgServiceType_Nick\",
				OST.OrgServiceType_Code as \"OrgServiceType_Code\",
				OST.OrgServiceType_SysNick as \"OrgServiceType_SysNick\",
				OST.MedServiceType_id as \"MedServiceType_id\",
				OST.OrgType_id as \"OrgType_id\",
				to_char(OST.OrgServiceType_begDT, '{$this->dateTimeForm104}') as \"OrgServiceType_begDT\",
				to_char(OST.OrgServiceType_endDT, '{$this->dateTimeForm104}') as \"OrgServiceType_endDT\"
			from v_OrgServiceType OST
			where OST.OrgServiceType_id = :OrgServiceType_id
			limit 1
		";
		$queryParams = ["OrgServiceType_id" => $data["OrgServiceType_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Сохранение типа службы
	 * @param $data
	 * @return array|bool
	 */
	function saveOrgServiceType($data)
	{
		$procedure = (empty($data["OrgServiceType_id"])) ? "p_OrgServiceType_ins" : "p_OrgServiceType_upd";
		$selectString = "
		    orgservicetype_id as \"OrgServiceType_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    orgservicetype_id := :OrgServiceType_id,
			    orgtype_id := :OrgType_id,
			    orgservicetype_code := :OrgServiceType_Code,
			    orgservicetype_name := :OrgServiceType_Name,
			    orgservicetype_nick := :OrgServiceType_Nick,
			    orgservicetype_sysnick := :OrgServiceType_SysNick,
			    orgservicetype_begdt := :OrgServiceType_begDT,
			    orgservicetype_enddt := :OrgServiceType_endDT,
			    medservicetype_id := :MedServiceType_id,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Чтение формы типа структурного уровня
	 * @param $data
	 * @return array|bool
	 */
	function loadOrgStructLevelTypeEditForm($data)
	{
		$query = "
			select
				OSLT.OrgStructLevelType_id as \"OrgStructLevelType_id\",
				OSLT.OrgType_id as \"OrgType_id\",
				OSLT.OrgStructLevelType_Name as \"OrgStructLevelType_Name\",
				OSLT.OrgStructLevelType_Nick as \"OrgStructLevelType_Nick\",
				OSLT.OrgStructLevelType_SysNick as \"OrgStructLevelType_SysNick\",
				OSLT.OrgStructLevelType_Code as \"OrgStructLevelType_Code\",
				to_char(OSLT.OrgStructLevelType_begDT, '{$this->dateTimeForm104}') as \"OrgStructLevelType_begDT\",
				to_char(OSLT.OrgStructLevelType_endDT, '{$this->dateTimeForm104}') as \"OrgStructLevelType_endDT\",
				OSLT.OrgStructLevelType_LevelNumber as \"OrgStructLevelType_LevelNumber\"
			from
				v_OrgStructLevelType OSLT
			where OSLT.OrgStructLevelType_id = :OrgStructLevelType_id
			limit 1
		";
		$queryParams = ["OrgStructLevelType_id" => $data["OrgStructLevelType_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Чтение формы типа организации
	 * @param $data
	 * @return array|bool
	 */
	function loadOrgTypeEditForm($data)
	{
		$query = "
			select
				OT.OrgType_id as \"OrgType_id\",
				OT.OrgType_Name as \"OrgType_Name\",
				OT.OrgType_Nick as \"OrgType_Nick\",
				OT.OrgType_SysNick as \"OrgType_SysNick\",
				OT.OrgType_Code as \"OrgType_Code\",
				to_char(OT.OrgType_begDT, '{$this->dateTimeForm104}') as \"OrgType_begDT\",
				to_char(OT.OrgType_endDT, '{$this->dateTimeForm104}') as \"OrgType_endDT\"
			from v_OrgType OT
			where OT.OrgType_id = :OrgType_id
			limit 1
		";
		$queryParams = ["OrgType_id" => $data["OrgType_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Сохранение типа организации
	 * @param $data
	 * @return array|bool
	 */
	function saveOrgType($data)
	{
		$procedure = (empty($data["OrgType_id"])) ? "p_OrgType_ins" : "p_OrgType_upd";
		$selectString = "
		    orgtype_id as \"OrgType_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    orgtype_id := :OrgType_id,
			    orgtype_code := :OrgType_Code,
			    orgtype_name := :OrgType_Name,
			    orgtype_sysnick := :OrgType_SysNick,
			    orgtype_nick := :OrgType_Nick,
			    orgtype_begdt := :OrgType_begDT,
			    orgtype_enddt := :OrgType_endDT,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Сохранение типа структурного уровня
	 * @param $data
	 * @return array|bool
	 */
	function saveOrgStructLevelType($data)
	{
		$procedure = (empty($data["OrgStructLevelType_id"])) ? "p_OrgStructLevelType_ins" : "p_OrgStructLevelType_upd";
		$selectString = "
		    orgstructleveltype_id as \"OrgStructLevelType_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    orgstructleveltype_id := :OrgStructLevelType_id,
			    orgtype_id := :OrgType_id,
			    orgstructleveltype_code := :OrgStructLevelType_Code,
			    orgstructleveltype_name := :OrgStructLevelType_Name,
			    orgstructleveltype_nick := :OrgStructLevelType_Nick,
			    orgstructleveltype_sysnick := :OrgStructLevelType_SysNick,
			    orgstructleveltype_begdt := :OrgStructLevelType_begDT,
			    orgstructleveltype_enddt := :OrgStructLevelType_endDT,
			    orgstructleveltype_levelnumber := :OrgStructLevelType_LevelNumber,
			    pmuser_id := :pmUser_id
			);
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Читает грид структурных уровней
	 * @param $data
	 * @return array|bool
	 */
	function loadOrgStructGrid($data)
	{
		$filters = (empty($data["OrgStruct_pid"]))?"and OS.OrgStruct_pid is null":"and OS.OrgStruct_pid = :OrgStruct_pid";
		$query = "
			select
				-- select 
				OS.OrgStruct_id as \"OrgStruct_id\",
				OS.OrgStruct_Code as \"OrgStruct_Code\",
				OS.OrgStruct_Name as \"OrgStruct_Name\",
				OS.OrgStruct_Nick as \"OrgStruct_Nick\",
				to_char(OS.OrgStruct_begDT, '{$this->dateTimeForm104}') as \"OrgStruct_begDT\",
				to_char(OS.OrgStruct_endDT, '{$this->dateTimeForm104}') as \"OrgStruct_endDT\",
				OSLT.OrgStructLevelType_Name as \"OrgStructLevelType_Name\"
				-- end select
			from
			-- from
				v_OrgStruct OS
				left join v_OrgStructLevelType OSLT on OSLT.OrgStructLevelType_id = OS.OrgStructLevelType_id
			-- end from
			where
			-- where
				OS.Org_id = :Org_id {$filters}
			-- end where
			order by
			-- order by
				OS.OrgStruct_Code
			-- end order by
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_count
		 */
		$result = $this->db->query(getLimitSQLPH($query, $data["start"], $data["limit"]), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);
		if (is_object($result_count)) {
			$cnt_arr = $result_count->result("array");
			$count = $cnt_arr[0]["cnt"];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		if (!is_object($result)) {
			return false;
		}
		$response = [];
		$response["data"] = $result->result("array");
		$response["totalCount"] = $count;
		return $response;
	}

	/**
	 * Читает грид типов организаций
	 * @param $data
	 * @return array|bool
	 */
	function loadOrgTypeGrid($data)
	{
		$query = "
			select
				OT.OrgType_id as \"OrgType_id\",
				OT.OrgType_Name as \"OrgType_Name\",
				OT.OrgType_Nick as \"OrgType_Nick\",
				OT.OrgType_Code as \"OrgType_Code\",
				to_char(OT.OrgType_begDT, '{$this->dateTimeForm104}') as \"OrgType_begDT\",
				to_char(OT.OrgType_endDT, '{$this->dateTimeForm104}') as \"OrgType_endDT\"
			from v_OrgType OT
			order by OT.OrgType_Code
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Читает грид типов служб
	 * @param $data
	 * @return array|bool
	 */
	function loadOrgServiceTypeGrid($data)
	{
		$query = "
			select
			-- select
				OST.OrgServiceType_id as \"OrgServiceType_id\",
				OST.OrgServiceType_Name as \"OrgServiceType_Name\",
				OST.OrgServiceType_Nick as \"OrgServiceType_Nick\",
				OST.OrgServiceType_Code as \"OrgServiceType_Code\",
				to_char(OST.OrgServiceType_begDT, '{$this->dateTimeForm104}') as \"OrgServiceType_begDT\",
				to_char(OST.OrgServiceType_endDT, '{$this->dateTimeForm104}') as \"OrgServiceType_endDT\"
			-- end select
			from
			-- from
				v_OrgServiceType OST
			-- end from
			where
			-- where
				OST.OrgType_id = :OrgType_id
			-- end where
			order by
			-- order by
				OST.OrgServiceType_Code
			-- end order by
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_count
		 */
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);
		if (is_object($result_count)) {
			$cnt_arr = $result_count->result("array");
			$count = $cnt_arr[0]["cnt"];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		if (!is_object($result)) {
			return false;
		}
		$response = [];
		$response["data"] = $result->result("array");
		$response["totalCount"] = $count;
		return $response;
	}

	/**
	 * Читает комбо типов структурных уровней
	 * @param $data
	 * @return array|bool
	 */
	function loadOrgStructLevelTypeList($data)
	{
		$filterArray = [];
		if (!empty($data["OrgStructLevelType_id"])) {
			$filterArray[] = "OSLT.OrgStructLevelType_id = :OrgStructLevelType_id";
		} else {
			$filterArray[] = "OSLT.OrgType_id = O.OrgType_id";
			if (!empty($data["OrgStructLevelType_LevelNumber"])) {
				$filterArray[] = "OSLT.OrgStructLevelType_LevelNumber = :OrgStructLevelType_LevelNumber";
			}
		}
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$query = "
			select
				OSLT.OrgStructLevelType_id as \"OrgStructLevelType_id\",
				OSLT.OrgStructLevelType_Name as \"OrgStructLevelType_Name\",
				OSLT.OrgStructLevelType_Nick as \"OrgStructLevelType_Nick\",
				OSLT.OrgStructLevelType_Code as \"OrgStructLevelType_Code\",
				to_char(OSLT.OrgStructLevelType_begDT, '{$this->dateTimeForm104}') as \"OrgStructLevelType_begDT\",
				to_char(OSLT.OrgStructLevelType_endDT, '{$this->dateTimeForm104}') as \"OrgStructLevelType_endDT\",
				OSLT.OrgStructLevelType_LevelNumber as \"OrgStructLevelType_LevelNumber\"
			from
				v_OrgStructLevelType OSLT
				left join lateral (
					select OrgType_id
					from v_Org
					where Org_id = :Org_id
				    limit 1
				) as O on true
			{$whereString}
			order by
				OSLT.OrgStructLevelType_LevelNumber,
				OSLT.OrgStructLevelType_Code
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Читает грид типов структурных уровней
	 * @param $data
	 * @return array|bool
	 */
	function loadOrgStructLevelTypeGrid($data)
	{
		$query = "
			select
			-- select
				OSLT.OrgStructLevelType_id as \"OrgStructLevelType_id\",
				OSLT.OrgStructLevelType_Name as \"OrgStructLevelType_Name\",
				OSLT.OrgStructLevelType_Nick as \"OrgStructLevelType_Nick\",
				OSLT.OrgStructLevelType_Code as \"OrgStructLevelType_Code\",
				to_char(OSLT.OrgStructLevelType_begDT, '{$this->dateTimeForm104}') as \"OrgStructLevelType_begDT\",
				to_char(OSLT.OrgStructLevelType_endDT, '{$this->dateTimeForm104}') as \"OrgStructLevelType_endDT\",
				OSLT.OrgStructLevelType_LevelNumber as \"OrgStructLevelType_LevelNumber\"
			-- end select
			from
			-- from
				v_OrgStructLevelType OSLT
			-- end from
			where
			-- where
				OSLT.OrgType_id = :OrgType_id
			-- end where
			order by
			-- order by
				OSLT.OrgStructLevelType_LevelNumber,
			    OSLT.OrgStructLevelType_Code
			-- end order by
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_count
		 */
		$result = $this->db->query(getLimitSQLPH($query, $data["start"], $data["limit"]), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);
		if (is_object($result_count)) {
			$cnt_arr = $result_count->result("array");
			$count = $cnt_arr[0]["cnt"];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		if (!is_object($result)) {
			return false;
		}
		$response = [];
		$response["data"] = $result->result("array");
		$response["totalCount"] = $count;
		return $response;
	}

	/**
	 * Получение доступных типов служб для организации
	 * @param $data
	 * @return array|bool
	 */
	function getAllowedMedServiceTypes($data)
	{
		$query = "
			select MST.MedServiceType_id as \"MedServiceType_id\"
			from
				v_OrgServiceType OST
				inner join MedServiceType MST on MST.MedServiceType_id = OST.MedServiceType_id
				left join lateral (
					select OrgType_id
					from v_Org
					where Org_id = :Org_id
				    limit 1
				) as O on true
			where OST.OrgType_id = O.OrgType_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result("array");
		$ret = [];
		// избавляемся от ключей, чтобы отдать просто список значений.
		foreach ($resp as $oneresp) {
			$ret[] = $oneresp["MedServiceType_id"];
		}
		return $ret;
	}

	/**
	 * Читает грид служб
	 * @param $data
	 * @return array|bool
	 */
	function loadMedServiceGrid($data)
	{
		$filters = (empty($data["OrgStruct_pid"])) ? "and MS.OrgStruct_id is null" : "and MS.OrgStruct_id = :OrgStruct_pid";
		$query = "
			select
			-- select
				MS.MedService_id as \"MedService_id\",
				MST.MedServiceType_Name as \"MedServiceType_Name\",
				MS.MedService_Name as \"MedService_Name\",
				MS.MedService_Nick as \"MedService_Nick\",
				to_char(MS.MedService_begDT, '{$this->dateTimeForm104}') as \"MedService_begDT\",
				to_char(MS.MedService_endDT, '{$this->dateTimeForm104}') as \"MedService_endDT\"
			-- end select
			from
			-- from
				v_MedService MS
				left join v_MedServiceType MST on MST.MedServiceType_id = MS.MedServiceType_id
			-- end from
			where
			-- where
				MS.Org_id = :Org_id {$filters}
			-- end where
			order by
			-- order by
				MS.MedService_Nick
			-- end order by
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_count
		 */
		$result = $this->db->query(getLimitSQLPH($query, $data["start"], $data["limit"]), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

		if (is_object($result_count)) {
			$cnt_arr = $result_count->result("array");
			$count = $cnt_arr[0]["cnt"];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		if (!is_object($result)) {
			return false;
		}
		$response = [];
		$response["data"] = $result->result("array");
		$response["totalCount"] = $count;
		return $response;
	}

	/**
	 * Читает дерево стркутуры организации
	 * @param $data
	 * @return array|bool
	 */
	function loadOrgStructureTree($data)
	{
		$queryParams = [
			"Org_id" => $data["Org_id"],
			"OrgStruct_pid" => $data["OrgStruct_pid"]
		];
		$filter = (empty($data["OrgStruct_pid"])) ? "and os.OrgStruct_pid is null" : "and os.OrgStruct_pid = :OrgStruct_pid";
		switch ($data["level"]) {
			case 0:
				$query = "
					select
						'org'||o.Org_id::varchar as \"id\",
					    null as \"code\",
					    o.Org_Nick as \"name\",
					    'Org' as \"object\",
					    0 as \"OrgStruct_NumLevel\",
					    case when ucc.cnt = 0 then 1 else 0 end as \"leaf\"
					from
						v_Org o
						left join lateral (
							select count(OrgStruct_id) as cnt
							from v_OrgStruct
							where Org_id = o.Org_id
							  and OrgStruct_pid is null
						) as ucc on true
					where o.Org_id = :Org_id
					order by o.Org_Nick
				";
				break;
			default:
				$query = "
					select
						'orgstruct'||os.OrgStruct_id::varchar as \"id\",
					    null as \"code\",
					    os.OrgStruct_Nick as \"name\",
					    'OrgStruct' as \"object\",
					    os.OrgStruct_NumLevel as \"OrgStruct_NumLevel\",
					    case when ucc.cnt = 0 then 1 else 0 end as \"leaf\"
					from
						v_OrgStruct os
						left join lateral (
							select count(OrgStruct_id) as cnt
							from v_OrgStruct
							where OrgStruct_pid = os.OrgStruct_id
						) as ucc on true
					where  os.Org_id = :Org_id
						{$filter}
					order by os.OrgStruct_Nick
				";
				break;
		}
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Удаление типа структурного уровня
	 * @param $data
	 * @return array|bool
	 */
	function deleteOrgStructLevelType($data)
	{
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_orgstructleveltype_del(orgstructleveltype_id := :OrgStructLevelType_id);
		";
		$queryParams = ["OrgStructLevelType_id" => $data["OrgStructLevelType_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Удаление типа службы
	 * @param $data
	 * @return array|bool
	 */
	function deleteOrgServiceType($data)
	{
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_orgservicetype_del(orgservicetype_id := :OrgServiceType_id);
		";
		$queryParams = ["OrgServiceType_id" => $data["OrgServiceType_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Проверка, используется ли тип структурного уровня
	 * @param $data
	 * @return bool
	 */
	function checkOrgStructLevelTypeIsUsed($data)
	{
		$query = "
			select OS.OrgStruct_id as \"OrgStruct_id\"
			from  v_OrgStruct OS
			where OS.OrgStructLevelType_id = :OrgStructLevelType_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result("array");
			if (count($resp) > 0) {
				// используется
				return true;
			}
		}
		// не используется
		return false;
	}

	/**
	 * Проверка, есть ли уже с таким кодом
	 * @param $field
	 * @param $data
	 * @return bool
	 */
	function checkCodeExist($field, $data)
	{
		$query = "
			select
				F.{$field}_id as \"{$field}_id\"
			from
				v_{$field} F
			where
				F.{$field}_Code = :{$field}_Code AND (F.{$field}_id != :{$field}_id OR :{$field}_id IS NULL)
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result("array");
			if (count($resp) > 0) {
				// используется
				return true;
			}
		}
		// не используется
		return false;
	}

	/**
	 * Проверка, есть ли уже с таким номером уровня
	 * @param $data
	 * @return bool
	 */
	function checkOrgStructLevelTypeNumber($data)
	{
		$query = "
			select OSLT.OrgStructLevelType_id as \"OrgStructLevelType_id\"
			from  v_OrgStructLevelType OSLT
			where OSLT.OrgStructLevelType_LevelNumber = :OrgStructLevelType_LevelNumber
			  and OSLT.OrgType_id = :OrgType_id
			  and OSLT.OrgStructLevelType_id != :OrgStructLevelType_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result("array");
			if (count($resp) > 0) {
				// используется
				return true;
			}
		}
		// не используется
		return false;
	}

	/**
	 * Проверка, используется ли тип службы // TO-DO, пока неясно.
	 * @param $data
	 * @return bool
	 */
	function checkOrgServiceTypeIsUsed($data)
	{
		return false; // не используется
	}

	/**
	 * Проверка, используется ли тип организации
	 * @param $data
	 * @return bool
	 */
	function checkOrgTypeIsUsed($data)
	{
		return true; // используется (добавление/удаление типов организаций только через бд)
	}

	/**
	 * Получение списка структурных уровней организации
	 */
	function loadOrgStructList($data)
	{
		$params = ["Org_id" => $data["Org_id"]];
		$query = "
			select
				OS.Org_id as \"Org_id\",
				OS.OrgStruct_id as \"OrgStruct_id\",
				OS.OrgStruct_Code as \"OrgStruct_Code\",
				OS.OrgStruct_Nick as \"OrgStruct_Nick\",
				OS.OrgStruct_Name as \"OrgStruct_Name\"
			from v_OrgStruct OS
			where OS.Org_id = :Org_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}
}