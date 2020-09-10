<?php

/**
 * EvnInfectNotify_model - модель, для работы с таблицей EvnInfectNotify
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Alexander Chebukin
 * @version      07.2012
 *
 * @property CI_DB_driver $db
 */
class EvnInfectNotify_model extends SwPgModel
{
	private $dateTimeForm104 = "DD.MM.YYYY";
	private $dateTimeForm108 = "HH24:MI:SS";

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function isIsset($data)
	{
		$query = "
			select 1
			from v_EvnInfectNotify
			where EvnInfectNotify_pid = :EvnInfectNotify_pid
		";
		$queryParams = ["EvnInfectNotify_pid" => $data["EvnInfectNotify_pid"]];
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
	function load($data)
	{
		$query = "
			select 
				EvnInfectNotify_id as \"EvnInfectNotify_id\",
				EvnInfectNotify_pid as \"EvnInfectNotify_pid\",
				EvnInfectNotify_IsLabDiag as \"EvnInfectNotify_IsLabDiag\",
				to_char(EvnInfectNotify_DiseaseDate, '{$this->dateTimeForm104}') as \"EvnInfectNotify_DiseaseDate\",
				to_char(EvnInfectNotify_FirstTreatDate, '{$this->dateTimeForm104}') as \"EvnInfectNotify_FirstTreatDate\",
				to_char(EvnInfectNotify_SetDiagDate, '{$this->dateTimeForm104}') as \"EvnInfectNotify_SetDiagDate\",
				to_char(EvnInfectNotify_NextVizitDate, '{$this->dateTimeForm104}') as \"EvnInfectNotify_NextVizitDate\",
				Lpu_id as \"Lpu_id\",
				Server_id as \"Server_id\",
				PersonEvn_id as \"PersonEvn_id\",
				EvnInfectNotify_PoisonDescr as \"EvnInfectNotify_PoisonDescr\",
				EvnInfectNotify_FirstMeasures as \"EvnInfectNotify_FirstMeasures\",
				to_char(EvnInfectNotify_FirstSESDT, '{$this->dateTimeForm104}') as \"EvnInfectNotify_FirstSESDT_Date\",
				substring(to_char(EvnInfectNotify_FirstSESDT, '{$this->dateTimeForm108}'), 1, 5) as \"EvnInfectNotify_FirstSESDT_Time\",
				MedPersonal_id as \"MedPersonal_id\",
				EvnInfectNotify_ReceiverMessage as \"EvnInfectNotify_ReceiverMessage\"
			from v_EvnInfectNotify
			where EvnInfectNotify_id = :EvnInfectNotify_id
		";
		$queryParams = ["EvnInfectNotify_id" => $data["EvnInfectNotify_id"]];
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
	function save($data)
	{
		$procedure_action = (!isset($data["EvnInfectNotify_id"])) ? "ins" : "upd";
		if (!empty($data["EvnInfectNotify_FirstSESDT_Date"])) {
			$data["EvnInfectNotify_FirstSESDT_Time"] = !empty($data["EvnInfectNotify_FirstSESDT_Time"]) ? $data["EvnInfectNotify_FirstSESDT_Time"] : "00:00";
			$data["EvnInfectNotify_FirstSESDT"] = $data["EvnInfectNotify_FirstSESDT_Date"] . " " . $data["EvnInfectNotify_FirstSESDT_Time"] . ":00";
		} else {
			$data["EvnInfectNotify_FirstSESDT"] = null;
		}
		$selectString = "
			evninfectnotify_id as \"EvnInfectNotify_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from p_EvnInfectNotify_{$procedure_action}(
			    evninfectnotify_id := :EvnInfectNotify_id,
			    evninfectnotify_pid := :EvnInfectNotify_pid,
			    lpu_id := :Lpu_id,
			    server_id := :Server_id,
			    personevn_id := :PersonEvn_id,
			    medpersonal_id := :MedPersonal_id,
			    evnsection_id := :EvnSection_id,
			    evninfectnotify_islabdiag := :EvnInfectNotify_IsLabDiag,
			    evninfectnotify_diseasedate := :EvnInfectNotify_DiseaseDate,
			    evninfectnotify_firsttreatdate := :EvnInfectNotify_FirstTreatDate,
			    evninfectnotify_setdiagdate := :EvnInfectNotify_SetDiagDate,
			    evninfectnotify_nextvizitdate := :EvnInfectNotify_NextVizitDate,
			    evninfectnotify_poisondescr := :EvnInfectNotify_PoisonDescr,
			    evninfectnotify_firstmeasures := :EvnInfectNotify_FirstMeasures,
			    evninfectnotify_firstsesdt := :EvnInfectNotify_FirstSESDT,
			    evninfectnotify_receivermessage := :EvnInfectNotify_ReceiverMessage,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"EvnInfectNotify_id" => $data["EvnInfectNotify_id"],
			"EvnInfectNotify_pid" => $data["EvnInfectNotify_pid"],
			"EvnInfectNotify_IsLabDiag" => $data["EvnInfectNotify_IsLabDiag"],
			"EvnInfectNotify_DiseaseDate" => $data["EvnInfectNotify_DiseaseDate"],
			"EvnInfectNotify_FirstTreatDate" => $data["EvnInfectNotify_FirstTreatDate"],
			"EvnInfectNotify_SetDiagDate" => $data["EvnInfectNotify_SetDiagDate"],
			"EvnInfectNotify_NextVizitDate" => $data["EvnInfectNotify_NextVizitDate"],
			"Lpu_id" => $data["Lpu_id"],
			"Server_id" => $data["Server_id"],
			"PersonEvn_id" => $data["PersonEvn_id"],
			"EvnInfectNotify_PoisonDescr" => $data["EvnInfectNotify_PoisonDescr"],
			"EvnInfectNotify_FirstMeasures" => $data["EvnInfectNotify_FirstMeasures"],
			"EvnInfectNotify_FirstSESDT" => $data["EvnInfectNotify_FirstSESDT"],
			"MedPersonal_id" => $data["MedPersonal_id"],
			"EvnInfectNotify_ReceiverMessage" => $data["EvnInfectNotify_ReceiverMessage"],
			"EvnSection_id" => (!empty($data["EvnSection_id"])) ? $data["EvnSection_id"] : null,
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
	 * Метод для API. Получение извещения об инфекционном заболевании (ВИЧ)
	 * @param $data
	 * @return array|bool
	 */
	function getEvnInfectNotifyAPI($data)
	{
		if (empty($data["Person_id"]) && empty($data["EvnInfectNotify_id"])) {
			return false;
		}
		$where = "";
		if (!empty($data["EvnInfectNotify_id"])) {
			$where .= "EIN.EvnInfectNotify_id = :EvnInfectNotify_id";
		}
		if (!empty($data["Person_id"])) {
			$where .= "Evn.Person_id = :Person_id";
		}
		$query = "
			select
				Evn.Person_id as \"Person_id\",
				ENB.Evn_id as \"EvnNotifyBase_id\",
				to_char(EIN.EvnInfectNotify_DiseaseDate, '{$this->dateTimeForm104}') as \"EvnInfectNotify_DiseaseDate\",
				EIN.EvnInfectNotify_FirstMeasures as \"EvnInfectNotify_FirstMeasures\",
				to_char(EIN.EvnInfectNotify_FirstSESDT, '{$this->dateTimeForm104}') as \"EvnInfectNotify_FirstSESDT_Date\",
				to_char(EIN.EvnInfectNotify_FirstTreatDate, '{$this->dateTimeForm104}') as \"EvnInfectNotify_FirstTreatDate\",
				case when coalesce(EIN.EvnInfectNotify_IsLabDiag, 2) = 2
					then 'нет'
					else 'да'
				end as \"EvnInfectNotify_IsLabDiag\",
				to_char(EIN.EvnInfectNotify_NextVizitDate, '{$this->dateTimeForm104}') as \"EvnInfectNotify_NextVizitDate\",
				EVN.Lpu_id as \"Lpu_id\",
				EIN.EvnInfectNotify_PoisonDescr as \"EvnInfectNotify_PoisonDescr\",
				EIN.EvnInfectNotify_ReceiverMessage as \"EvnInfectNotify_ReceiverMessage\",
				to_char(EIN.EvnInfectNotify_SetDiagDate, '{$this->dateTimeForm104}') as EvnInfectNotify_SetDiagDate,
				EIN.EvnSection_id as \"EvnSection_id\",
				ES.Diag_id as \"Diag_id\",
				ES.MedPersonal_id as \"MedPersonal_id\"
			from
				Evn
				inner join EvnNotifyBase ENB on Evn.Evn_id = ENB.Evn_id
				inner join EvnInfectNotify EIN on ENB.Evn_id = EIN.Evn_id
				left join v_EvnSection ES on ES.EvnSection_id = EIN.EvnSection_id
			where {$where}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}
}