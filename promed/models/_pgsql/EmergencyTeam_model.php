<?php
defined("BASEPATH") or die ("No direct script access allowed");

/**
 * Class EmergencyTeam_model
 *
 * @property CI_DB_driver $db
 */
class EmergencyTeam_model extends swPgModel
{

	/**
	 * Сохранение бригады СМП
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	public function saveEmergencyTeam($data)
	{
		if (!array_key_exists("Lpu_id", $data) || !$data["Lpu_id"]) {
			throw new Exception("Не указан идентификатор ЛПУ");
		}
		$queryParams = [
			"EmergencyTeam_Num" => $data["EmergencyTeam_Num"],
			"EmergencyTeam_CarNum" => $data["EmergencyTeam_CarNum"],
			"EmergencyTeam_CarBrand" => $data["EmergencyTeam_CarBrand"],
			"EmergencyTeam_CarModel" => $data["EmergencyTeam_CarModel"],
			"EmergencyTeam_PortRadioNum" => $data["EmergencyTeam_PortRadioNum"],
			"EmergencyTeam_GpsNum" => $data["EmergencyTeam_GpsNum"],
			"LpuBuilding_id" => $data["LpuBuilding_id"],
			"EmergencyTeam_BaseStationNum" => $data["EmergencyTeam_BaseStationNum"],
			"EmergencyTeamSpec_id" => $data["EmergencyTeamSpec_id"],
			"EmergencyTeam_HeadShift" => $data["EmergencyTeam_HeadShift"],
			"EmergencyTeam_Driver" => $data["EmergencyTeam_Driver"],
			"EmergencyTeam_Assistant1" => $data["EmergencyTeam_Assistant1"],
			"EmergencyTeam_Assistant2" => $data["EmergencyTeam_Assistant2"],
			"Lpu_id" => $data["Lpu_id"],
			"pmUser_id" => $data["pmUser_id"],
		];
		$additionalParamForcheckExistetETQuery = "";
		if (!array_key_exists("EmergencyTeam_id", $data) || !$data["EmergencyTeam_id"]) {
			$procedure = "p_EmergencyTeam_ins";
			$queryParams["EmergencyTeam_id"] = null;
		} else {
			$procedure = "p_EmergencyTeam_upd";
			$queryParams["EmergencyTeam_id"] = $data["EmergencyTeam_id"];
			$additionalParamForcheckExistetETQuery = " AND ET.EmergencyTeam_id != :EmergencyTeam_id";
		}
		$checkExistetETQuery = "
			select ET.EmergencyTeam_id as \"EmergencyTeam_id\"
			from v_EmergencyTeam ET
			where ET.EmergencyTeam_Num = :EmergencyTeam_Num
			  and ET.Lpu_id = :Lpu_id
			  {$additionalParamForcheckExistetETQuery}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($checkExistetETQuery, $queryParams);
		if (is_object($result)) {
			$result = $result->result_array();
			if (sizeof($result)) {
				throw new Exception("Бригада с таким номером уже существует в данном ЛПУ. Пожалуйста, отредактируйте или удалите бригаду с таким номером.");
			}
		}
		$selectString = "
	        emergencyteam_id as \"EmergencyTeam_id\", 
	        Error_Code as \"Error_Code\", 
	        Error_Message as \"Error_Msg\"
		";
		$query = "
		    select {$selectString}
			from {$procedure}(
				emergencyteam_id := :EmergencyTeam_id,
				EmergencyTeam_Num := :EmergencyTeam_Num,
				EmergencyTeam_CarNum := :EmergencyTeam_CarNum,
				EmergencyTeam_CarBrand := :EmergencyTeam_CarBrand,
				EmergencyTeam_CarModel := :EmergencyTeam_CarModel,
				EmergencyTeam_PortRadioNum := :EmergencyTeam_PortRadioNum,
				EmergencyTeam_GpsNum := :EmergencyTeam_GpsNum,
				LpuBuilding_id := :LpuBuilding_id,
				EmergencyTeam_BaseStationNum := :EmergencyTeam_BaseStationNum,
				EmergencyTeamSpec_id := :EmergencyTeamSpec_id,
				EmergencyTeam_HeadShift := :EmergencyTeam_HeadShift,
				EmergencyTeam_Driver := :EmergencyTeam_Driver,
				EmergencyTeam_Assistant1 := :EmergencyTeam_Assistant1,
				EmergencyTeam_Assistant2 := :EmergencyTeam_Assistant2,
				Lpu_id := :Lpu_id,
				pmUser_id := :pmUser_id
            )
		";
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$result = $result->result_array();
			$EmergencyTeam_id = $result[0]["EmergencyTeam_id"];
			$query = "
				update EmergencyTeam
				set EmergencyTeam_Deleted = 2,
					pmUser_delID = :pmUser_id,
					EmergencyTeam_delDT = dbo.tzGetDate()
				where EmergencyTeam_id != :EmergencyTeam_id
				  and EmergencyTeam_Num = :EmergencyTeam_Num
				  and Lpu_id = :Lpu_id
			";
			$queryParams = [
				"EmergencyTeam_id" => $EmergencyTeam_id,
				"EmergencyTeam_Num" => $data["EmergencyTeam_Num"],
				"Lpu_id" => $data["Lpu_id"],
				"pmUser_id" => $data["pmUser_id"],
			];
			$this->db->query($query, $queryParams);
			return $result;
		}
		return false;
	}


	/**
	 * Список бригад для комбобокса
	 * @param $data
	 * @return array|bool
	 */
	function getEmergencyTeamCombo($data)
	{
		/**@var CI_DB_result $result */
		$query = "
			select
				ET.EmergencyTeam_id as \"EmergencyTeam_id\",
				ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				MP.Person_Fin as \"Person_Fin\"
			from
				v_EmergencyTeam  ET
				left join v_MedPersonal as MP on MP.MedPersonal_id = ET.EmergencyTeam_HeadShift
		";
		$result = $this->db->query($query);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение оперативной обстановки по бригадам СМП
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function loadEmergencyTeamOperEnv($data)
	{
		if (!array_key_exists("Lpu_id", $data) || !$data["Lpu_id"]) {
			throw new Exception("Не указан идентификатор ЛПУ");
		}
		$filter = "1 = 1";
		$sqlArr = [];
		$joins = "";
		$data["closeHide"] = isset($data["closeHide"]) ? $data["closeHide"] : 1;
		if ((array_key_exists("closeHide", $data) && ($data["closeHide"] == 1)) && (isset($data["CmpCallCard"]) && $data["CmpCallCard"] > 0)) {
			$sqlArr = ["CmpCallCard" => $data["CmpCallCard"]];
			$joins = " left join v_CmpCallCard as CC ON CC.CmpCallCard_id = :CmpCallCard ";
			$filter .= " AND (ETD.EmergencyTeamDuty_DTStart <  CC.CmpCallCard_prmDT AND (ETD.EmergencyTeamDuty_DTFinish >  CC.CmpCallCard_prmDT))";
		}
		$filter .= " AND COALESCE(ET.EmergencyTeam_isTemplate,1)=1";
		// Вышел на смену
		if ((getRegionNick() == "buryatiya") && !empty($data["session"]) && !empty($data["session"]["CurArmType"]) && ($data["session"]["CurArmType"] != "smpheadduty")) {
			$filter .= " AND ETD.EmergencyTeamDuty_isComesToWork = 2";
		}
		$filter .= " AND ET.Lpu_id = '{$data["Lpu_id"]}'";
		if (isset($data["teamTime"])) {
			$filter .= "
				AND etd.EmergencyTeamDuty_DTStart <= :teamTime::timestamp
				AND etd.EmergencyTeamDuty_DTFinish >= :teamTime::timestamp
			";
			$sqlArr["teamTime"] = DateTime::createFromFormat("d.m.Y H:i:s", $data["teamTime"]);
		}
		if (isset($data["LpuBuilding_id"])) {
			$filter .= " AND ET.LpuBuilding_id = :LpuBuilding_id";
			$sqlArr["LpuBuilding_id"] = $data["LpuBuilding_id"];
		}
		$query = "
			SELECT DISTINCT
			    -- select
				ET.EmergencyTeam_id as \"EmergencyTeam_id\",
				ET.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
				ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				ET.EmergencyTeam_CarNum as \"EmergencyTeam_CarNum\",
				ET.EmergencyTeam_CarBrand as \"EmergencyTeam_CarBrand\",
				ET.EmergencyTeam_CarModel as \"EmergencyTeam_CarModel\",
				ET.EmergencyTeam_PortRadioNum as \"EmergencyTeam_PortRadioNum\",
				ET.EmergencyTeam_GpsNum as \"EmergencyTeam_GpsNum\",
				ET.EmergencyTeam_BaseStationNum as \"EmergencyTeam_BaseStationNum\",
				ET.LpuBuilding_id as \"LpuBuilding_id\",
				MP.MedPersonal_id as \"MedPersonal_id\",
				MSF.MedStaffFact_id as \"MedStaffFact_id\",
				MP.Person_Fin||'. '||ETSpec.EmergencyTeamSpec_Name as \"EmergencyTeamSpec_Name\",
				ETSpec.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\",
				case when coalesce(ET.EmergencyTeam_isOnline, 1) = 2
				    then 'online'
					else 'offline'
				end as \"EmergencyTeam_isOnline\",
				ETS.EmergencyTeamStatus_Name as \"EmergencyTeamStatus_Name\",
				MP.Person_Fin as \"Person_Fin\"
				-- end select
			FROM
			    -- from
				v_EmergencyTeam as ET
				left join v_EmergencyTeamDuty ETD on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
				left join v_LpuBuilding LB ON LB.LpuBuilding_id=ET.LpuBuilding_id
				left join v_EmergencyTeamSpec as ETSpec ON ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id
				left join v_EmergencyTeamStatus AS ETS ON ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id
				left join v_MedPersonal as MP ON MP.MedPersonal_id=ET.EmergencyTeam_HeadShift
				left join v_MedStaffFact MSF on MSF.MedPersonal_id = ET.EmergencyTeam_HeadShift
				{$joins}
				-- end from
			WHERE
				-- where
				{$filter}
				-- end where
			ORDER BY
				-- order by
				EmergencyTeam_Num
				-- end order by
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $sqlArr);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function loadEmergencyTeamCCC($data)
	{
		if (empty($data["Lpu_id"])) {
			throw new Exception("Не указан идентификатор ЛПУ");
		}
		$EmergencyID = false;
		$sqlArr = [];
		$filter = "
				1 = 1
			and COALESCE(ET.EmergencyTeam_isTemplate, 1) = 1
			and ETD.EmergencyTeamDuty_isComesToWork = 2
			and ET.Lpu_id = '{$data["Lpu_id"]}'
			and MP.MedPersonal_id is not null
		";
		if (isset($data["AcceptTime"])) {
			if ($data["CmpCallCard_id"]) {
				// узнаем есть ли назначенная бригада и включим ее в выборку #116341
				$EmergencyID = $this->getAppointedBrigadeForTheCall($data);
			}
			$filter .= ($EmergencyID)
				? " 
					and (
						(
							etd.EmergencyTeamDuty_factToWorkDT <= :AcceptTime::timestamp and
							(etd.EmergencyTeamDuty_factEndWorkDT is null or etd.EmergencyTeamDuty_factEndWorkDT >= :AcceptTime::timestamp)
						) or
						ET.EmergencyTeam_id = {$EmergencyID}
					)
				"
				: " 
					and etd.EmergencyTeamDuty_factToWorkDT <= :AcceptTime::timestamp
					and (etd.EmergencyTeamDuty_factEndWorkDT is null or etd.EmergencyTeamDuty_factEndWorkDT >= :AcceptTime::timestamp)
				";
			$sqlArr["AcceptTime"] = DateTime::createFromFormat('d.m.Y H:i:s', $data["AcceptTime"]);
		}
		$query = "
			SELECT DISTINCT
			    -- select
				ET.EmergencyTeam_id as \"EmergencyTeam_id\",
				ET.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
				ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				ET.EmergencyTeam_CarNum as \"EmergencyTeam_CarNum\",
				ET.EmergencyTeam_CarBrand as \"EmergencyTeam_CarBrand\",
				ET.EmergencyTeam_CarModel as \"EmergencyTeam_CarModel\",
				ET.EmergencyTeam_PortRadioNum as \"EmergencyTeam_PortRadioNum\",
				ET.EmergencyTeam_GpsNum as \"EmergencyTeam_GpsNum\",
				ET.EmergencyTeam_BaseStationNum as \"EmergencyTeam_BaseStationNum\",
				ET.LpuBuilding_id as \"LpuBuilding_id\",
				MP.MedPersonal_id as \"MedPersonal_id\",
				MSF.MedStaffFact_id as \"MedStaffFact_id\",
				MP.Person_Fin||'. '||ETSpec.EmergencyTeamSpec_Name as \"EmergencyTeamSpec_Name\",
				ETSpec.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\",
				case when coalesce(ET.EmergencyTeam_isOnline, 1) = 2
				    then 'online'
					else 'offline'
				end as \"EmergencyTeam_isOnline\",
				ETS.EmergencyTeamStatus_Name as \"EmergencyTeamStatus_Name\",
				etd.EmergencyTeamDuty_factToWorkDT as \"EmergencyTeamDuty_factToWorkDT\",
				etd.EmergencyTeamDuty_factEndWorkDT as \"EmergencyTeamDuty_factEndWorkDT\",
				MP.Person_Fin as \"Person_Fin\"
				-- end select
			FROM
			    -- from
				v_EmergencyTeam as ET
				LEFT JOIN v_EmergencyTeamDuty ETD on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
				LEFT JOIN v_LpuBuilding LB ON LB.LpuBuilding_id=ET.LpuBuilding_id
				LEFT JOIN v_EmergencyTeamSpec as ETSpec ON ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id
				LEFT JOIN v_EmergencyTeamStatus AS ETS ON ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id
				LEFT JOIN v_MedPersonal as MP ON MP.MedPersonal_id=ET.EmergencyTeam_HeadShift
				/*LEFT JOIN LATERAL (
					select  MedStaffFact_id
					from v_MedStaffFact
					where MedPersonal_id = MP.MedPersonal_id
					  and WorkData_begDate <= dbo.tzGetDate()
					  and (WorkData_endDate > dbo.tzGetDate() or WorkData_endDate  is null)
					order by PostOccupationType_id
					limit 1
				) as MSF on true*/
				LEFT JOIN v_MedStaffFact MSF on (MSF.MedStaffFact_id = ET.EmergencyTeam_HeadShiftWorkPlace)
				-- end from
			WHERE
				-- where
				{$filter}
				-- end where
			ORDER BY
				-- order by
				\"EmergencyTeam_Num\"
				-- end order by
		";
        $result = $this->db->query( $query, $sqlArr );

        if ( is_object( $result ) ) {

            return $result->result('array');
        }

        return false;
	}

	/**
	 * Сохраняет заданную дату и время начала и окончания смены
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function saveEmergencyTeamDutyTime($data)
	{
		if (!array_key_exists("EmergencyTeam_id", $data) ||
			!array_key_exists("EmergencyTeamDuty_DateStart", $data) ||
			!array_key_exists("EmergencyTeamDuty_DateFinish", $data)
		) {
			throw new Exception("Отсутствуют необходимые данные. Возможно вы не указали ни одной смены или не выбрали бригаду.");
		}
		$procedure = (!array_key_exists("EmergencyTeamDuty_id", $data) || !$data["EmergencyTeamDuty_id"]) ? "p_EmergencyTeamDuty_ins" : "p_EmergencyTeamDuty_upd";
		$return = false;
		foreach ($data["EmergencyTeamDuty_DateStart"] as $k => &$v) {
			if (trim($data["EmergencyTeamDuty_DateStart"][$k]) == "" || trim($data["EmergencyTeamDuty_DateFinish"][$k] == "")) {
				continue;
			}
			$date_start = preg_replace("#[\s]?\(.*\)[\s]*$#", "", $data["EmergencyTeamDuty_DateStart"][$k]);
			$date_finish = preg_replace("#[\s]?\(.*\)[\s]*$#", "", $data["EmergencyTeamDuty_DateFinish"][$k]);
			$date_start = DateTime::createFromFormat("??? M d Y H:i:s ????????", $date_start);
			$date_finish = DateTime::createFromFormat("??? M d Y H:i:s ????????", $date_finish);

			$selectString = "
		    	emergencyteamduty_id as \"EmergencyTeamDuty_id\",
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
			";
			$query = "
				select {$selectString}
				from {$procedure}(
				    emergencyteamduty_id := :EmergencyTeamDuty_id,
					EmergencyTeamDuty_DTStart := :EmergencyTeamDuty_DTStart,
					EmergencyTeamDuty_DTFinish := :EmergencyTeamDuty_DTFinish,
					EmergencyTeam_id := :EmergencyTeam_id,
					pmUser_id := :pmUser_id
				)
			";
			$sqlArr = [
				"EmergencyTeamDuty_id" => !array_key_exists("EmergencyTeamDuty_id", $data) || !$data["EmergencyTeamDuty_id"] ? 0 : $data["EmergencyTeamDuty_id"],
				"EmergencyTeamDuty_DTStart" => $date_start,
				"EmergencyTeamDuty_DTFinish" => $date_finish,
				"EmergencyTeam_id" => $data["EmergencyTeam_id"],
				"pmUser_id" => $data["pmUser_id"],
			];
			/**@var CI_DB_result $result */
			$result = $this->db->query($query, $sqlArr);
			if (is_object($result)) {
				$return = $result->result_array();
			}
		}
		if (!$return) {
			throw new Exception("Вы не указали ни одной смены или данные были введены не корректно.");
		}
		return $return;
	}

	/**
	 * Получение оперативной обстановки по диспетчерам СМП
	 * @param $data
	 * @return array|bool
	 */
	function loadDispatchOperEnv($data)
	{
		// Получаем список диспетчеров
		$query = "
			SELECT
				-- select
				pmUser_id as \"pmUser_id\",
				pmUser_name as \"pmUser_name\",
				Lpu_Name as \"Lpu_Name\"
				-- end select
			FROM
				-- from
				v_pmUserCache u
				INNER JOIN v_Lpu as l ON l.Lpu_id=u.Lpu_id
				-- end from
			WHERE
				-- where
				pmUser_groups ILIKE '%{\"name\":\"SMPCallDispath\"}%'
				AND pmUser_groups ILIKE '%{\"name\":\"SMPDispatchDirections\"}%'
				AND l.Lpu_id = :Lpu_id
				-- end where
		";
		$sqlArr = ["Lpu_id" => $data["Lpu_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $sqlArr);
		return (is_object($result)) ? $result->result_array() : false;
	}


	/**
	 * Возвращает данные указанной бригады
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function loadEmergencyTeam($data)
	{
		if (!array_key_exists("EmergencyTeam_id", $data) || !$data["EmergencyTeam_id"]) {
			return false;
		}
		if (!array_key_exists("Lpu_id", $data) || !$data["Lpu_id"]) {
			throw new Exception("Не указан идентификатор ЛПУ");
		}
		$query = "
			SELECT 
				ET.EmergencyTeam_id as \"EmergencyTeam_id\",
				ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				ET.EmergencyTeam_CarNum as \"EmergencyTeam_CarNum\",
				ET.EmergencyTeam_CarBrand as \"EmergencyTeam_CarBrand\",
				ET.EmergencyTeam_CarModel as \"EmergencyTeam_CarModel\",
				ET.EmergencyTeam_PortRadioNum as \"EmergencyTeam_PortRadioNum\",
				ET.EmergencyTeam_GpsNum as \"EmergencyTeam_GpsNum\",
				ET.EmergencyTeam_BaseStationNum as \"EmergencyTeam_BaseStationNum\",
				ET.LpuBuilding_id as \"LpuBuilding_id\",
				ET.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
				ET.EmergencyTeam_HeadShift as \"EmergencyTeam_HeadShift\",
				ET.EmergencyTeam_Driver as \"EmergencyTeam_Driver\",
				ET.EmergencyTeam_Assistant1 as \"EmergencyTeam_Assistant1\",
				ET.EmergencyTeam_Assistant2 as \"EmergencyTeam_Assistant2\",
				ET.EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\",
				ET.EmergencyTeam_IsOnline as \"EmergencyTeam_IsOnline\",
				ET.Lpu_id as \"Lpu_id\"
			FROM v_EmergencyTeam ET
			WHERE ET.EmergencyTeam_id = :EmergencyTeam_id
			  AND ET.Lpu_id = :Lpu_id
			limit 1
		";
		$sqlArr = [
			"EmergencyTeam_id" => $data["EmergencyTeam_id"],
			"Lpu_id" => $data["Lpu_id"],
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $sqlArr);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Удаляет бригаду СМП
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function deleteEmergencyTeam($data)
	{
		if (!array_key_exists("EmergencyTeam_id", $data) || !$data["EmergencyTeam_id"]) {
			return false;
		}
		if (!array_key_exists("Lpu_id", $data) || !$data["Lpu_id"]) {
			throw new Exception("Не указан идентификатор ЛПУ");
		}
		$selectString = "
			Error_Code as \"Error_Code\", 
			Error_Message as \"Error_Msg\"
		";
		$query = "
		    select {$selectString}
			from p_EmergencyTeam_setdel (
				EmergencyTeam_id := :EmergencyTeam_id,
				Lpu_id := :Lpu_id,
				pmUser_id := :pmUser_id
            )
		";
		$queryParams = [
			"EmergencyTeam_id" => $data["EmergencyTeam_id"],
			"Lpu_id" => $data["Lpu_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Во время удаления бригады СМП произошла ошибка в базе данных.");
		}
		return $result->result_array();
	}

	/**
	 * Изменяет статус бригады СМП
	 * @param $data
	 * @return array|bool
	 */
	public function setEmergencyTeamStatus($data)
	{
		if (!array_key_exists("EmergencyTeam_id", $data) || !$data["EmergencyTeam_id"] ||
			!array_key_exists("EmergencyTeamStatus_id", $data) || !$data["EmergencyTeamStatus_id"]
		) {
			return false;
		}
		$query = "
		    select 
		        Error_Code as \"Error_Code\", 
		        Error_Message as \"Error_Msg\"
			from p_EmergencyTeam_setStatus(
			    EmergencyTeam_id := :EmergencyTeam_id,
			    emergencyteamstatus_id := :EmergencyTeamStatus_id,
			    pmUser_id := :pmUser_id
            )
		";
		$queryParams = [
			"EmergencyTeam_id" => $data["EmergencyTeam_id"],
			"EmergencyTeamStatus_id" => $data["EmergencyTeamStatus_id"],
			"pmUser_id" => $data["pmUser_id"],
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение списка смен указанной бригады для графика нарядов
	 * @param $data
	 * @return array|bool
	 */
	public function loadEmergencyTeamDutyTimeGrid($data)
	{
		if (!array_key_exists("EmergencyTeam_id", $data) || !$data["EmergencyTeam_id"]) {
			return false;
		}
		$sqlArr = ["EmergencyTeam_id" => $data["EmergencyTeam_id"]];
		$filter = "";
		if (!array_key_exists("dateStart", $data) || empty($data["dateStart"]) ||
			!array_key_exists("dateFinish", $data) || empty($data["dateFinish"])) {
			$data["dateStart"] = date("Y.m.d", time() - 7 * 24 * 60 * 60) . " 00:00:00";
			$data["dateFinish"] = date("Y.m.d") . " 00:00:00";
		}
		$filter .= "
			and etd.EmergencyTeamDuty_DTStart::date >= :dateStart::date
			and etd.EmergencyTeamDuty_DTStart::date <= :dateFinish::date
		";
		$sqlArr["dateStart"] = $data["dateStart"];
		$sqlArr["dateFinish"] = $data["dateFinish"];
		$query = "
			SELECT
				etd.EmergencyTeamDuty_id as \"EmergencyTeamDuty_id\",
				etd.EmergencyTeam_id as \"EmergencyTeam_id\",
				to_char(etd.EmergencyTeamDuty_DTStart, 'yyyy-mm-dd')  as \"EmergencyTeamDuty_DTStart\",
                to_char(etd.EmergencyTeamDuty_DTFinish, 'yyyy-mm-dd') as \"EmergencyTeamDuty_DTFinish\",
				CASE
					WHEN etd.EmergencyTeamDuty_isComesToWork=2 THEN 'Да'
					WHEN etd.EmergencyTeamDuty_isComesToWork=1 THEN 'Нет'
					ELSE ''
				END as \"ComesToWork\"
			FROM v_EmergencyTeamDuty etd
			WHERE etd.EmergencyTeam_id = :EmergencyTeam_id {$filter}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $sqlArr);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		return [
			"data" => $result,
			"totalCount" => sizeof($result)
		];
	}

	/**
	 * Отмечает выход на смену бригады СМП по врачу
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function setEmergencyTeamWorkComingMedPersonal($data)
	{
		if (!array_key_exists("MedPersonal_id", $data) || !$data["MedPersonal_id"]) {
			throw new Exception("Не указан врач");
		}
		$query = "
        	SELECT
				etd.EmergencyTeamDuty_id as \"EmergencyTeamDuty_id\",
				etd.EmergencyTeam_id as \"EmergencyTeam_id\",
				etd.EmergencyTeamDuty_DTStart as \"EmergencyTeamDuty_DTStart\",
                etd.EmergencyTeamDuty_DTFinish as \"EmergencyTeamDuty_DTFinish\",
				etd.EmergencyTeamDuty_isComesToWork as \"EmergencyTeamDuty_isComesToWork\"
			FROM
				v_EmergencyTeamDuty etd
				LEFT JOIN v_EmergencyTeam et ON et.EmergencyTeam_id=etd.EmergencyTeam_id
			WHERE et.EmergencyTeam_HeadShift = :EmergencyTeam_HeadShift
			  and coalesce(etd.EmergencyTeamDuty_isClose,1) != '2'
			  and etd.EmergencyTeamDuty_factToWorkDT > '1900-01-01'
			  and dbo.tzGetDate() BETWEEN etd.EmergencyTeamDuty_factToWorkDT AND
			      CASE WHEN  etd.EmergencyTeamDuty_factEndWorkDT is null OR etd.EmergencyTeamDuty_factEndWorkDT = '1900-01-01'
			          THEN '2030-01-01'
			          ELSE etd.EmergencyTeamDuty_factEndWorkDT
			      END
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, ["EmergencyTeam_HeadShift" => $data["MedPersonal_id"]]);
		if (is_object($result)) {
			$result = $result->result_array();
		}
		if (count($result) > 1) {
			$duty_cnt = 0;
			foreach ($result as $res) {
				if ($res["EmergencyTeamDuty_isComesToWork"] == "2") {
					$duty_cnt++;
				}
			}
			return ($duty_cnt == 1)
				? [["success" => true, "Code" => 3, "Msg" => "Вы на смене"]]
				: [["success" => true, "Code" => 1, "Msg" => "Есть доступные смены"]];
		}
		if (count($result) == 0) {
			return [["success" => true, "Code" => 2, "Msg" => "На ближайший час смен нет"]];
		}
		if (count($result) == 1 && $result[0]["EmergencyTeamDuty_isComesToWork"] == "2") {
			return [["success" => true, "Code" => 3, "Msg" => "Вы на смене"]];
		}
		if (count($result) == 1 && $result[0]["EmergencyTeamDuty_isComesToWork"] != "2") {
			return [["success" => true, "Code" => 4, "Msg" => "Нужно отметиться о выходе на смену"]];
		}
		return false;
	}


	/**
	 * Отмечает выход на смену бригады СМП по врачу
	 * @param $data
	 * @return array|bool
	 */
	function loadEmergencyTeamByMedPersonal($data)
	{
		$sqlArr = ["EmergencyTeam_HeadShift" => $data["session"]["medpersonal_id"]];
		if (!array_key_exists("dateStart", $data) || empty($data["dateStart"]) ||
			!array_key_exists("dateFinish", $data) || empty($data["dateFinish"])) {
			$data["dateStart"] = date("Y.m.d", time() - 7 * 24 * 60 * 60) . " 00:00:00";
			$data["dateFinish"] = date("Y.m.d") . " 00:00:00";
		}
		$sqlArr["dateStart"] = $data["dateStart"];
		$sqlArr["dateFinish"] = $data["dateFinish"];
		$query = "
        	SELECT
				etd.EmergencyTeamDuty_id as \"EmergencyTeamDuty_id\",
				etd.EmergencyTeam_id as \"EmergencyTeam_id\",
				to_char(etd.EmergencyTeamDuty_DTStart, 'yyyy-mm-dd')  as \"EmergencyTeamDuty_DTStart\",
				to_char(etd.EmergencyTeamDuty_DTFinish, 'yyyy-mm-dd') as \"EmergencyTeamDuty_DTFinish\",      
				to_char (etd.EmergencyTeamDuty_DTStart, 'dd.mm.yyyy hh24:mi:ss') as \"EmergencyTeamDuty_DTStartVis\",
				to_char (etd.EmergencyTeamDuty_DTFinish, 'dd.mm.yyyy hh24:mi:ss') as \"EmergencyTeamDuty_DTFinishVis\",
				etd.EmergencyTeamDuty_isComesToWork as \"EmergencyTeamDuty_isComesToWork\",
				et.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				et.EmergencyTeam_CarNum as \"EmergencyTeam_CarNum\",
				et.EmergencyTeam_CarBrand as \"EmergencyTeam_CarBrand\",
				et.EmergencyTeam_CarModel as \"EmergencyTeam_CarModel\",
				et.EmergencyTeam_PortRadioNum as \"EmergencyTeam_PortRadioNum\",
				et.EmergencyTeam_GpsNum as \"EmergencyTeam_GpsNum\",
				et.EmergencyTeam_BaseStationNum as \"EmergencyTeam_BaseStationNum\",
				et.LpuBuilding_id as \"LpuBuilding_id\",
				et.EmergencyTeam_HeadShift as \"EmergencyTeam_HeadShift\",
				ETSpec.EmergencyTeamSpec_Name as \"EmergencyTeamSpec_Name\",
				ETSpec.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\",
				ETS.EmergencyTeamStatus_Name as \"EmergencyTeamStatus_Name\",
				CASE WHEN COALESCE(etd.EmergencyTeamDuty_isComesToWork,1) = 1 THEN 'false' ELSE 'true' END AS \"EmergencyTeamDuty_isComesToWork\",
				CASE WHEN COALESCE(etd.EmergencyTeamDuty_isClose,1) = 1 THEN 'false' ELSE 'true' END AS \"EmergencyTeamDuty_isClose\"
			from
				v_EmergencyTeam et
				left join v_EmergencyTeamDuty etd ON et.EmergencyTeam_id = etd.EmergencyTeam_id
				left join v_EmergencyTeamStatus AS ETS ON ETS.EmergencyTeamStatus_id = et.EmergencyTeamStatus_id
				left join v_EmergencyTeamSpec as ETSpec on et.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id
			where et.EmergencyTeam_HeadShift = :EmergencyTeam_HeadShift
			  and etd.EmergencyTeamDuty_DTStart::date >= :dateStart::date
			  and etd.EmergencyTeamDuty_DTStart::date <= :dateFinish::date
    	";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $sqlArr);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		return [
			"data" => $result,
			"totalCount" => sizeof($result)
		];
	}

	/**
	 * Отмечает выход на смену бригады СМП
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function setEmergencyTeamWorkComing($data)
	{
		if (!array_key_exists("EmergencyTeamDuty_id", $data) || !$data["EmergencyTeamDuty_id"]) {
			throw new Exception("Не указана смена бригады.");
		}
		if (!array_key_exists("EmergencyTeam_id", $data) || !$data["EmergencyTeam_id"]) {
			throw new Exception("Не указана бригада.");
		}
		if (!array_key_exists("EmergencyTeamDuty_isComesToWork", $data) || !$data["EmergencyTeamDuty_isComesToWork"]) {
			throw new Exception("Не указан флаг выхода на смену бригады.");
		}
		$query = "
			select
				Error_Code as \"Error_Code\"
			from p_EmergencyTeamDuty_setWorkComing(
			    EmergencyTeamDuty_id := :EmergencyTeamDuty_id,
				EmergencyTeam_id := :EmergencyTeam_id,
				EmergencyTeamDuty_isComesToWork := :EmergencyTeamDuty_isComesToWork,
				pmUser_id := :pmUser_id
			)
		";
		$queryParams = [
			"EmergencyTeamDuty_id" => $data["EmergencyTeamDuty_id"],
			"EmergencyTeam_id" => $data["EmergencyTeam_id"],
			"EmergencyTeamDuty_isComesToWork" => $data["EmergencyTeamDuty_isComesToWork"] == 2 ? 2 : 1,
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Во время попытки отметить выход на смену для бригады СМП, произошла ошибка в базе данных.");
		}
		return $result->result_array();
	}

	/**
	 * Возращает список для справочника списка бригад СМП
	 * @param $data
	 * @return mixed
	 * @throws Exception
	 */
	function loadEmergencyTeamCombo($data)
	{
		$where = [];
		$sqlArr = [];
		// Выводим только бригады состоящих в ЛПУ пользователя
		if (!array_key_exists("Lpu_id", $data) || !$data["Lpu_id"]) {
			throw new Exception("Не указан идентификатор ЛПУ");
		}
		$where[] = "et.Lpu_id = :Lpu_id";
		if (!empty($data["begDate"]) && !empty($data["endDate"])) {
			$where[] = "(
					(
							 (:begDate BETWEEN etd.EmergencyTeamDuty_factToWorkDT and etd.EmergencyTeamDuty_factEndWorkDT) or
							 (:begDate > etd.EmergencyTeamDuty_factToWorkDT and etd.EmergencyTeamDuty_factEndWorkDT is null) or
							 (etd.EmergencyTeamDuty_factToWorkDT IS NULL) OR
							 (etd.EmergencyTeamDuty_factToWorkDT BETWEEN :begDate and :endDate)
					) and
					(
						(etd.EmergencyTeamDuty_factEndWorkDT IS NULL) OR
						(:endDate BETWEEN etd.EmergencyTeamDuty_factToWorkDT and etd.EmergencyTeamDuty_factEndWorkDT) or
						(etd.EmergencyTeamDuty_factEndWorkDT BETWEEN :begDate and :endDate)
					)
				)
			";
			$sqlArr["begDate"] = "{$data["begDate"]} 00:00:00";
			$sqlArr["endDate"] = "{$data["endDate"]} 23:59:59";
		}
		$where[] = "etd.EmergencyTeamDuty_isComesToWork = 2";
		if (!empty($data["LpuBuilding_id"])) {
			$where[] = "ET.LpuBuilding_id = :LpuBuilding_id";
			$sqlArr["LpuBuilding_id"] = $data["LpuBuilding_id"];
		}
		$whereString = ImplodeWherePH($where);
		$query = "
        	SELECT distinct
				et.EmergencyTeam_id as \"EmergencyTeam_id\",
				et.LpuBuilding_id as \"LpuBuilding_id\",
				et.EmergencyTeam_Num as \"EmergencyTeam_Code\",
				trim(mp.Person_FIO) as \"EmergencyTeam_Name\"
			FROM
				v_EmergencyTeam et
				LEFT JOIN v_EmergencyTeamDuty ETD on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
				INNER JOIN v_MedPersonal mp on mp.MedPersonal_id=et.EmergencyTeam_HeadShift
			{$whereString}
			ORDER BY et.EmergencyTeam_Num
    	";
		$sqlArr["Lpu_id"] = $data["Lpu_id"];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $sqlArr);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function getEmergencyTeamProposalLogic($data)
	{
		if (!array_key_exists("Lpu_id", $data) || !$data["Lpu_id"]) {
			throw new Exception("Не указан идентификатор ЛПУ");
		}
		$filter = "ETPL.Lpu_id = :Lpu_id";
		if (!empty($data["EmergencyTeamProposalLogic_id"])) {
			$filter .= " and ETPL.EmergencyTeamProposalLogic_id = :EmergencyTeamProposalLogic_id";
		}
		$query = "
        	SELECT
				ETPL.EmergencyTeamProposalLogic_id as \"EmergencyTeamProposalLogic_id\",
				ETPL.CmpReason_id as \"CmpReason_id\",
				ETPL.Sex_id as \"Sex_id\",
				CR.CmpReason_Code as \"CmpReason_Code\",
				COALESCE(S.Sex_Name, 'Все') as \"Sex_Name\",
				COALESCE(ETPL.EmergencyTeamProposalLogic_AgeFrom::varchar(10), '') as \"EmergencyTeamProposalLogic_AgeFrom\",
				COALESCE(ETPL.EmergencyTeamProposalLogic_AgeTo::varchar(10), '') as \"EmergencyTeamProposalLogic_AgeTo\",
				Codes.Codes as \"EmergencyTeamProposalLogic_Sequence\"
			FROM
				v_EmergencyTeamProposalLogic ETPL
				LEFT JOIN v_Sex S ON ETPL.Sex_id = S.Sex_id
				INNER JOIN v_CmpReason CR ON ETPL.CmpReason_id = CR.CmpReason_id
				LEFT JOIN LATERAL (
					SELECT DISTINCT
					(
						SELECT string_agg(ETS2.EmergencyTeamSpec_Code, ' ')
						FROM 
							v_EmergencyTeamProposalLogicRule ETPLR
							INNER JOIN v_EmergencyTeamSpec ETS2 on ETPLR.EmergencyTeamSpec_id = ETS2.EmergencyTeamSpec_id
						WHERE ETPLR.EmergencyTeamProposalLogic_id = ETPL.EmergencyTeamProposalLogic_id
						ORDER BY ETPLR.EmergencyTeamProposalLogicRule_SequenceNum
					) AS Codes
				) as Codes
			WHERE
				{$filter}
    	";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		return [
			"data" => $result,
			"totalCount" => sizeof($result)
		];
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getEmergencyTeamProposalLogicRuleSpecSequence($data)
	{
		$filter = "(1=1)";
		if (!isset($data["EmergencyTeamProposalLogic_id"]) || $data["EmergencyTeamProposalLogic_id"] == 0) {
			$query = "
				SELECT
					0 as \"EmergencyTeamProposalLogicRule_id\",
					ROW_NUMBER() OVER(ORDER BY ETS.EmergencyTeamSpec_id) as \"EmergencyTeamProposalLogicRule_SequenceNum\",
					ETS.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
					ETS.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\",
					ETS.EmergencyTeamSpec_Name as \"EmergencyTeamSpec_Name\"
				FROM v_EmergencyTeamSpec ETS
			";
		} else {
			$filter .= " and ETPLR.EmergencyTeamProposalLogic_id = :EmergencyTeamProposalLogic_id";
			$query = "
				SELECT
					ETPLR.EmergencyTeamProposalLogicRule_id as \"EmergencyTeamProposalLogicRule_id\",
					ETPLR.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
					ETPLR.EmergencyTeamProposalLogicRule_SequenceNum as \"EmergencyTeamProposalLogicRule_SequenceNum\",
					ETS.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\",
					ETS.EmergencyTeamSpec_Name as \"EmergencyTeamSpec_Name\"
				FROM
					v_EmergencyTeamProposalLogicRule ETPLR
					INNER JOIN v_EmergencyTeamSpec ETS ON ETS.EmergencyTeamSpec_id = ETPLR.EmergencyTeamSpec_id
				WHERE
					{$filter}
			";
		}
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		return [
			"data" => $result,
			"totalCount" => sizeof($result)
		];
	}

	/**
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function saveEmergencyTeamProposalLogicRule($data)
	{
		$procedure = (!array_key_exists("EmergencyTeamProposalLogic_id", $data) || !$data["EmergencyTeamProposalLogic_id"])
			? "p_EmergencyTeamProposalLogic_ins"
			: "p_EmergencyTeamProposalLogic_upd";
		if (!array_key_exists("EmergencyTeamProposalLogic_id", $data) || !$data["EmergencyTeamProposalLogic_id"]) {
			$data["EmergencyTeamProposalLogic_id"] = 0;
		}
		if (!array_key_exists("CmpReason_id", $data) || !$data["CmpReason_id"]) {
			throw new Exception("Не указан повод.");
		}
		if (!isset($data["EmergencyTeamProposalLogic_AgeFrom"]) && (!isset($data["EmergencyTeamProposalLogic_AgeTo"]))) {
			throw new Exception("Хотя бы одно из полей (Возраст С, Возраст ПО) должно быть заполнено.");
		}
		if (isset($data["EmergencyTeamProposalLogic_AgeFrom"]) && isset($data["EmergencyTeamProposalLogic_AgeTo"]) && ($data["EmergencyTeamProposalLogic_AgeFrom"] > $data["EmergencyTeamProposalLogic_AgeTo"])) {
			throw new Exception("Значение поля \"Возраст С\" не может быть больше значения поля \"Возраст ПО\"");
		}
		//Проверка непротиворечивости правила
		$consistencyQueryParams = [
			"CmpReason_id" => $data["CmpReason_id"],
			"Sex_id" => (isset($data["Sex_id"])) ? $data["Sex_id"] : null,
			"EmergencyTeamProposalLogic_AgeFrom" => (isset($data["EmergencyTeamProposalLogic_AgeFrom"])) ? $data["EmergencyTeamProposalLogic_AgeFrom"] : 120,
			"EmergencyTeamProposalLogic_AgeTo" => (isset($data["EmergencyTeamProposalLogic_AgeTo"])) ? $data["EmergencyTeamProposalLogic_AgeTo"] : 0,
			"Lpu_id" => $data["Lpu_id"]
		];
		$consistencyCheckQuery = "
			select ETPL.EmergencyTeamProposalLogic_id as \"EmergencyTeamProposalLogic_id\"
			from v_EmergencyTeamProposalLogic ETPL
			where not (coalesce(:EmergencyTeamProposalLogic_AgeFrom, 0) > coalesce(ETPL.EmergencyTeamProposalLogic_AgeTo, 120) or coalesce(:EmergencyTeamProposalLogic_AgeTo, 120) < coalesce(ETPL.EmergencyTeamProposalLogic_AgeFrom, 0))
			  and ETPL.CmpReason_id = :CmpReason_id
			  and (coalesce(ETPL.Sex_id, 0) = 0 or coalesce(:Sex_id, 0) = 0 or coalesce(ETPL.Sex_id, 0 = coalesce(:Sex_id, 0)))
			  and ETPL.Lpu_id = :Lpu_id
		";
		/**@var CI_DB_result $resultConsistencyCheckQuery */
		$resultConsistencyCheckQuery = $this->db->query($consistencyCheckQuery, $consistencyQueryParams);
		if (!is_object($resultConsistencyCheckQuery)) {
			throw new Exception("Введенное правило противоречит одному из существующих правил с соответствующим поводом вызова.");
		}
		$resultConsistencyCheckQueryArray = $resultConsistencyCheckQuery->result_array();
		if (count($resultConsistencyCheckQueryArray) > 0) {
			throw new Exception("Введенное правило противоречит одному из существующих правил с соответствующим поводом вызова.");
		}
		$selectString = "
	        emergencyteamproposallogic_id as \"EmergencyTeamProposalLogic_id\", 
	        Error_Code as \"Error_Code\", 
	        Error_Message as \"Error_Msg\"
		";
		$query = "
		    select {$selectString}
			from {$procedure}(
				emergencyteamproposallogic_id := :EmergencyTeamProposalLogic_id,
				CmpReason_id := :CmpReason_id,
				Sex_id := :Sex_id,
				Lpu_id := :Lpu_id,
				EmergencyTeamProposalLogic_AgeFrom := :EmergencyTeamProposalLogic_AgeFrom,
				EmergencyTeamProposalLogic_AgeTo := :EmergencyTeamProposalLogic_AgeTo,
				pmUser_id := :pmUser_id
			)
		";
		$queryParams = [
			"EmergencyTeamProposalLogic_id" => $data["EmergencyTeamProposalLogic_id"],
			"CmpReason_id" => $data["CmpReason_id"],
			"Sex_id" => isset($data["Sex_id"]) ? $data["Sex_id"] : null,
			"Lpu_id" => $data["Lpu_id"],
			"EmergencyTeamProposalLogic_AgeFrom" => isset($data["EmergencyTeamProposalLogic_AgeFrom"]) ? $data["EmergencyTeamProposalLogic_AgeFrom"] : null,
			"EmergencyTeamProposalLogic_AgeTo" => isset($data["EmergencyTeamProposalLogic_AgeTo"]) ? $data["EmergencyTeamProposalLogic_AgeTo"] : null,
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Во время попытки отметить выход на смену для бригады СМП, произошла ошибка в базе данных.");
		}
		return $result->result_array();
	}

	/**
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function saveEmergencyTeamProposalLogicRuleSequence($data)
	{
		if (!array_key_exists("EmergencyTeamProposalLogic_id", $data) || !$data["EmergencyTeamProposalLogic_id"]) {
			throw new Exception("Не указан идентификатор правила.");
		}
		if (!array_key_exists("EmergencyTeamSpec_id", $data) || !$data["EmergencyTeamSpec_id"]) {
			throw new Exception("Не указан идентификатор профиля бригады.");
		}
		if (!array_key_exists("EmergencyTeamProposalLogicRule_SequenceNum", $data)) {
			throw new Exception("Не указан порядок профиля");
		}
		$procedure = (isset($data["EmergencyTeamProposalLogicRule_id"]) && $data["EmergencyTeamProposalLogicRule_id"] != 0)
			? "p_EmergencyTeamProposalLogicRule_upd"
			: "p_EmergencyTeamProposalLogicRule_ins";
		if (!(isset($data["EmergencyTeamProposalLogicRule_id"]) && $data["EmergencyTeamProposalLogicRule_id"] != 0)) {
			$data["EmergencyTeamProposalLogicRule_id"] = 0;
		}
		$selectString = "
	        emergencyteamproposallogicrule_id as \"EmergencyTeamProposalLogicRule_id\",
	        Error_Code as \"Error_Code\",
	        Error_Message as \"Error_Msg\"
		";
		$query = "
		    select {$selectString}
			from {$procedure}(
				emergencyteamproposallogicrule_id := :EmergencyTeamProposalLogicRule_id,
				EmergencyTeamProposalLogic_id := :EmergencyTeamProposalLogic_id,
				EmergencyTeamSpec_id := :EmergencyTeamSpec_id,
				EmergencyTeamProposalLogicRule_SequenceNum := :EmergencyTeamProposalLogicRule_SequenceNum,
				pmUser_id := :pmUser_id
            )
		";
		$queryParams = [
			"EmergencyTeamProposalLogicRule_id" => $data["EmergencyTeamProposalLogicRule_id"],
			"EmergencyTeamProposalLogic_id" => $data["EmergencyTeamProposalLogic_id"],
			"EmergencyTeamSpec_id" => $data["EmergencyTeamSpec_id"],
			"EmergencyTeamProposalLogicRule_SequenceNum" => $data["EmergencyTeamProposalLogicRule_SequenceNum"],
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Во время попытки отметить выход на смену для бригады СМП, произошла ошибка в базе данных.");
		}
		return $result->result_array();
	}


	/**
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function deleteEmergencyTeamProposalLogicRule($data)
	{
		if (!array_key_exists("EmergencyTeamProposalLogic_id", $data) || !$data["EmergencyTeamProposalLogic_id"]) {
			throw new Exception("Не указан идентификатор правила.");
		}
		if (@$data["EmergencyTeamProposalLogic_id"] == null || @$data["EmergencyTeamProposalLogic_id"] == 0) {
			throw new Exception("Не задан идентификатор правила");
		}
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result1
		 */
		$query = "
			select count(ETPLR.EmergencyTeamProposalLogicRule_id) as EmergencyTeamProposalLogicRule_id
			from v_EmergencyTeamProposalLogicRule ETPLR
			where ETPLR.EmergencyTeamProposalLogic_id = :EmergencyTeamProposalLogic_id
		";
		$queryParams = ["EmergencyTeamProposalLogic_id" => $data["EmergencyTeamProposalLogic_id"]];
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		if ($result[0]["EmergencyTeamProposalLogicRule_id"] > 0) {
			$work = true;
			$countBlock = 0;
			while ($work) {
				$countBlock++;
				$query1 = "
					select distinct
						ETPLR.EmergencyTeamProposalLogicRule_id
					from v_EmergencyTeamProposalLogicRule ETPLR
					where ETPLR.EmergencyTeamProposalLogic_id = :EmergencyTeamProposalLogic_id
				";
				$result1 = $this->db->query($query1, $queryParams);
				$result1 = $result1->result_array();
				$EmergencyTeamProposalLogic_id = $result1[0]["EmergencyTeamProposalLogic_id"];
				$query1 = "
					select
					    error_code as \"Error_Code\",
					    error_message as \"Error_Message\"
					from p_emergencyteamproposallogicrule_del(emergencyteamproposallogicrule_id := {$EmergencyTeamProposalLogic_id});
				";
				$result1 = $this->db->query($query1);
				$result1 = $result1->result_array();
				if ($countBlock > 50 || trim($result1[0]["Error_Message"]) != "") {
					$work = false;
				} else {
					$result = $this->db->query($query, $queryParams);
					if (!is_object($result)) {
						return false;
					}
					$result = $result->result("array");
					if ($result[0]["EmergencyTeamProposalLogicRule_id"] <= 0) {
						$work = false;
					}
				}
			}
		}
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_emergencyteamproposallogic_del(emergencyteamproposallogic_id := :EmergencyTeamProposalLogic_id);
		";
		$queryParams = ["EmergencyTeamProposalLogic_id" => $data["EmergencyTeamProposalLogic_id"]];
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}


	/**
	 * @param $data
	 * @throws Exception
	 */
	function deleteEmergencyTeamProposalLogicRuleSequence($data)
	{
		if (!array_key_exists("EmergencyTeamProposalLogic_id", $data) || !$data["EmergencyTeamProposalLogic_id"]) {
			throw new Exception("Не указан идентификатор правила.");
		}
	}

	/**
	 * Получение назанченной бригады на вызов
	 */
	function getAppointedBrigadeForTheCall($data)
	{
		if (empty($data["CmpCallCard_id"])) {
			return false;
		}
		$queryParams = ["CmpCallCard_id" => $data["CmpCallCard_id"]];
		$query = "
			select EmergencyTeam_id as \"EmergencyTeam_id\"
			from v_CmpCallCard
			where CmpCallCard_id = :CmpCallCard_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$res = $result->result_array();
			if (isset($res[0]["EmergencyTeam_id"])) {
				return $res[0]["EmergencyTeam_id"];
			}
		}
		return false;
	}

	/**
	 * Возвращает Вид должности старшего бригады
	 * @param $data
	 * @return array|bool
	 */
	public function getEmergencyTeamPostKind($data)
	{
		if (empty($data["EmergencyTeam_id"])) {
			return false;
		}
		$query = "
			SELECT 
				et.EmergencyTeam_id as \"EmergencyTeam_id\",
				et.LpuBuilding_id as \"LpuBuilding_id\",
				et.EmergencyTeam_Num as \"EmergencyTeam_Code\",
				et.EmergencyTeam_HeadShift as \"EmergencyTeam_HeadShift\",
				trim(mp.Person_FIO) as \"EmergencyTeam_HeadShift_Name\",
				PostKind.code as \"PostKindHeadShift_Code\",
				PostKind.name as \"PostKindHeadShift_Name\",
				et.EmergencyTeam_HeadShift2 as \"EmergencyTeam_HeadShift2\",
				et.EmergencyTeam_Assistant1 as \"EmergencyTeam_Assistant1\",
				et.EmergencyTeam_Assistant2 as \"EmergencyTeam_Assistant2\",
				et.EmergencyTeam_Driver as \"EmergencyTeam_Driver\"
			FROM
				v_EmergencyTeam et
				INNER JOIN v_MedPersonal mp ON mp.MedPersonal_id = et.EmergencyTeam_HeadShift
				left join persis.v_Post Post on Post.id = MP.Dolgnost_id
				left join persis.v_PostKind PostKind on PostKind.id = Post.PostKind_id
			WHERE et.EmergencyTeam_id = :EmergencyTeam_id
			limit 1
		";
		$result = $this->getFirstRowFromQuery($query, $data);
		return (is_array($result) && count($result) > 0) ? [$result] : false;
	}
}