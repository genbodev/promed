<?php
/**
 * Class EvnAgg_model
 *
 * @property CI_DB_driver $db
 * @property SwLoader $load
 */
class EvnAgg_model extends CI_Model
{
	private $dateTimeForm104 = "DD.MM.YYYY";

	public $inputRules = [
		"loadEvnAggList" => [
			[
				"field" => "Evn_id",
				"label" => "Идентификатор случая услуги-родителя",
				"rules" => "required",
				"type" => "id"
			]
		],
		"loadEvnAgg" => [
			[
				"field" => "Evn_id",
				"label" => "Идентификатор случая услуги-родителя",
				"rules" => "",
				"type" => "id"
			],
			[
				"field" => "EvnAgg_id",
				"label" => "Идентификатор осложнения",
				"rules" => "",
				"type" => "id"
			],
			[
				"field" => "AggType_id",
				"label" => "Тип осложнения",
				"rules" => "",
				"type" => "id"
			]
		],
		"createEvnAgg" => [
			[
				"field" => "Evn_id",
				"label" => "Идентификатор случая услуги-родителя",
				"rules" => "required",
				"type" => "id",
				"checklpu" => true
			],
			[
				"field" => "AggWhen_id",
				"label" => "Период, в котором произошло осложнение",
				"rules" => "required",
				"type" => "id"
			],
			[
				"field" => "AggType_id",
				"label" => "Тип осложнения",
				"rules" => "required",
				"type" => "id"
			]
		],
		"updateEvnAgg" => [
			[
				"field" => "EvnAgg_id",
				"label" => "Идентификатор осложнения",
				"rules" => "required",
				"type" => "id"
			],
			[
				"field" => "Evn_id",
				"label" => "Идентификатор случая услуги-родителя",
				"rules" => "",
				"type" => "id"
			],
			[
				"field" => "AggWhen_id",
				"label" => "Период, в котором произошло осложнение",
				"rules" => "",
				"type" => "id"
			],
			[
				"field" => "AggType_id",
				"label" => "Тип осложнения",
				"rules" => "",
				"type" => "id"
			]
		]
	];

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @param $data
	 * @return mixed
	 *
	 * @throws Exception
	 */
	function deleteEvnAgg($data)
	{
		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_evnagg_del(
				evnagg_id := :EvnAgg_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"EvnAgg_id" => $data["EvnAgg_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (удаление осложнения)");
		}
		return $result->result("array");
	}
	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnAggEditForm($data)
	{
		$this->load->helper("MedStaffFactLink");
		$med_personal_list = getMedPersonalListWithLinks();
		if (count($data["session"]["linkedLpuIdList"]) > 1) {
			$linkedLpuIdListString = implode(",", $data["session"]["linkedLpuIdList"]);
			$accessType = "
				case
					when EA.Lpu_id = :Lpu_id then 1
					when EA.Lpu_id in ({$linkedLpuIdListString}) and coalesce(EA.EvnAgg_IsTransit, 1) = 2 then 1
					else 0
				end = 1
			";
		} else {
			$accessType = "
				case
					when EA.Lpu_id = :Lpu_id then 1
					else 0
				end = 1
			";
		}
		$query = "
			select
				case when {$accessType} " . ($data['session']['isMedStatUser'] == false && count($med_personal_list) > 0 ? "and EU.MedPersonal_id in (" . implode(',', $med_personal_list) . ")" : "") . " then 'edit' else 'view' end as \"accessType\",
				EA.EvnAgg_id as \"EvnAgg_id\",
				EA.EvnAgg_pid as \"EvnAgg_pid\",
				EA.Person_id as \"Person_id\",
				EA.PersonEvn_id as \"PersonEvn_id\",
				EA.Server_id as \"Server_id\",
				EA.AggType_id as \"AggType_id\",
				EA.AggWhen_id as \"AggWhen_id\",
				to_char(EA.EvnAgg_setDate, '{$this->dateTimeForm104}') as \"EvnAgg_setDate\",
				to_char(EA.EvnAgg_setTime, 'HH24:MI')  as \"EvnAgg_setTime\"
			from
				v_EvnAgg EA
				inner join v_EvnUsluga EU on EU.EvnUsluga_id = EA.EvnAgg_pid
			where EA.EvnAgg_id = :EvnAgg_id
			  and (EA.Lpu_id = :Lpu_id or " . (!empty($data['session']['medpersonal_id']) ? 1 : 0) . " = 1)
			limit 1
		";
		$queryParams = [
			"EvnAgg_id" => $data["EvnAgg_id"],
			"Lpu_id" => $data["Lpu_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnAggGrid($data)
	{
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();
		if (count($data['session']['linkedLpuIdList']) > 1) {
			$linkedLpuIdListString = implode(',', $data['session']['linkedLpuIdList']);
			$accessType = "
				case
					when EA.Lpu_id = :Lpu_id then 1
					when EA.Lpu_id in ({$linkedLpuIdListString}) and coalesce(EA.EvnAgg_IsTransit, 1) = 2 then 1
					else 0
				end = 1
			";
		} else {
			$accessType = "
				case
					when EA.Lpu_id = :Lpu_id then 1
					else 0
				end = 1
			";
		}
		$query = "
			select
				case when {$accessType} " . ($data['session']['isMedStatUser'] == false && count($med_personal_list) > 0 ? "and EU.MedPersonal_id in (" . implode(',', $med_personal_list) . ")" : "") . " then 'edit' else 'view' end as \"accessType\",
				EA.EvnAgg_id as \"EvnAgg_id\",
				EA.EvnAgg_pid as \"EvnAgg_pid\",
				EA.Person_id as \"Person_id\",
				EA.PersonEvn_id as \"PersonEvn_id\",
				EA.Server_id as \"Server_id\",
				AT.AggType_id as \"AggType_id\",
				AW.AggWhen_id as \"AggWhen_id\",
				to_char(EA.EvnAgg_setDate, '{$this->dateTimeForm104}') as \"EvnAgg_setDate\",
				to_char(EA.EvnAgg_setTime, 'HH24:MI') as \"EvnAgg_setTime\",
				rtrim(coalesce(AT.AggType_Name, '')) as \"AggType_Name\",
				rtrim(coalesce(AW.AggWhen_Name, '')) as \"AggWhen_Name\"
			from
				v_EvnAgg EA
				inner join v_EvnUsluga EU on EU.EvnUsluga_id = EA.EvnAgg_pid
				left join AggType AT on AT.AggType_id = EA.AggType_id
				left join AggWhen AW on AW.AggWhen_id = EA.AggWhen_id
			where EA.EvnAgg_pid = :EvnAgg_pid
			  and EA.Lpu_id = :Lpu_id
		";
		$queryParams = [
			"EvnAgg_pid" => $data["EvnAgg_pid"],
			"Lpu_id" => $data["Lpu_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function saveEvnAgg($data)
	{
		if (empty($data["EvnAgg_id"]) || $data["EvnAgg_id"] <= 0) {
			$procedure = 'p_EvnAgg_ins';
		} else {
			$procedure = 'p_EvnAgg_upd';
		}
		$data['EvnAgg_setDT'] = trim($data["EvnAgg_setDate"] . " " . $data["EvnAgg_setTime"]);
		$selectString = "
			evnagg_id as \"EvnAgg_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
				evnagg_id := :EvnAgg_id,
			    evnagg_pid := :EvnAgg_pid,
			    lpu_id := :Lpu_id,
			    server_id := :Server_id,
			    personevn_id := :PersonEvn_id,
			    evnagg_setdt := :EvnAgg_setDT,
			    aggtype_id := :AggType_id,
			    aggwhen_id := :AggWhen_id,
				pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"EvnAgg_id" => $data["EvnAgg_id"],
			"EvnAgg_pid" => $data["EvnAgg_pid"],
			"Lpu_id" => $data["Lpu_id"],
			"Server_id" => $data["Server_id"],
			"PersonEvn_id" => $data["PersonEvn_id"],
			"EvnAgg_setDT" => !empty($data['EvnAgg_setDT'])?$data['EvnAgg_setDT']:null,
			"AggType_id" => $data["AggType_id"],
			"AggWhen_id" => $data["AggWhen_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 *  Получение информации по осложнению услуги
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnAgg($data)
	{
		$where = "Lpu_id = :Lpu_id";
		if (!empty($data["Evn_id"])) {
			$where .= " and  EvnAgg_pid = :Evn_id";
		}
		if (!empty($data["EvnAgg_id"])) {
			$where .= " and  EvnAgg_id = :EvnAgg_id";
		}
		if (!empty($data["AggType_id"])) {
			$where .= " and  AggType_id = :AggType_id";
		}
		$query = "
			select
				EvnAgg_id as \"EvnAgg_id\",
				EvnAgg_pid as \"Evn_id\",
				AggType_id as \"AggType_id\",
				AggWhen_id as \"AggWhen_id\"
			from v_EvnAgg
			where  {$where}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnAggList($data)
	{
		$params = ["EvnAgg_pid" => $data["EvnAgg_pid"]];
		$where = "";
		if (!empty($data["AggWhen_id"])) {
			$params["AggWhen_id"] = $data["AggWhen_id"];
			$where = " and  EA.AggWhen_id = :AggWhen_id";
		}
		$query = "
			select
				EA.EvnAgg_id as \"EvnAgg_id\",
				EA.AggType_id as \"AggType_id\"
			from v_EvnAgg EA
			where EA.EvnAgg_pid = :EvnAgg_pid {$where}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}
}