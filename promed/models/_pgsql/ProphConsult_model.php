<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ProphConsult_model - модель для работы с записями в 'Показания к углубленному профилактическому консультированию'
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Valery Bondarev
 * @version      11.12.2019
 */

class ProphConsult_model extends swPgModel
{
	/**
	 * ProphConsult_model constructor.
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function loadProphConsultGrid($data)
	{
		$filter = " and PC.EvnPLDisp_id = :EvnPLDisp_id";

		if (!empty($data['ProphConsult_id'])) {
			$filter = " and PC.ProphConsult_id = :ProphConsult_id";
		}

		$query = "
			select 
				PC.EvnPLDisp_id as \"EvnPLDisp_id\",
				PC.ProphConsult_id as \"ProphConsult_id\",
				PC.RiskFactorType_id as \"RiskFactorType_id\",
				RFT.RiskFactorType_Name as \"RiskFactorType_Name\"
			from
				v_ProphConsult PC
				left join v_RiskFactorType RFT on RFT.RiskFactorType_id = PC.RiskFactorType_id
			where
				(1=1) {$filter}
			order by
				PC.ProphConsult_id
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных для ЭМК
	 */
	function getProphConsultViewData($data) {
		if (empty($data['ProphConsult_pid'])) {
			return array();
		}

		$queryParams = array(
			'EvnPLDisp_id' => $data['ProphConsult_pid']
		);

		$filters = "PC.EvnPLDisp_id = :EvnPLDisp_id";

		$query = "
			select
				PC.ProphConsult_id as \"ProphConsult_id\",
				PC.EvnPLDisp_id as \"EvnPLDisp_id\",
				RFT.RiskFactorType_Name as \"RiskFactorType_id\"
			from
				v_ProphConsult PC
				left join v_RiskFactorType RFT on RFT.RiskFactorType_id = PC.RiskFactorType_id
			where
				{$filters}
		";

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function checkProphConsultExists($data) {
		$query = "
			select
				ProphConsult_id  as \"ProrhConsult_id\"
			from
				v_ProphConsult
			where
				EvnPLDisp_id = :EvnPLDisp_id
				and RiskFactorType_id = :RiskFactorType_id
				and ProphConsult_id <> coalesce(CAST(:ProphConsult_id as bigint), 0)
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function saveProphConsult($data) {
		if (!empty($data['ProphConsult_id']) && $data['ProphConsult_id'] > 0) {
			$proc = "p_ProphConsult_upd";
		} else {
			$proc = "p_ProphConsult_ins";
		}

		$selectString = "
            prophconsult_id as \"ProphConsult_id\", 
            error_code as \"Error_Code\", 
            error_message as \"Error_Msg\"
        ";

		$params = [
			"ProphConsult_id" => !empty($data["ProphConsult_id"]) ? $data["ProphConsult_id"] : null,
			"EvnPLDisp_id" => $data["EvnPLDisp_id"],
			"RiskFactorType_id" => $data["RiskFactorType_id"],
			"pmUser_id" => $data["pmUser_id"],
		];

		$query = "
			select {$selectString}
			from {$proc}(
			    prophconsult_id := :ProphConsult_id,
			    evnpldisp_id := :EvnPLDisp_id,
			    riskfactortype_id := :RiskFactorType_id,
			    pmuser_id := :pmUser_id
			);
		";

		$res = $this->db->query($query, $params);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Добавление
	 */
	function addProphConsult($data, $RiskFactorType_id) {
		// проверяем есть ли такой, если нет, то добавляем
		if (!empty($RiskFactorType_id)) {
			$data['RiskFactorType_id'] = $RiskFactorType_id;
			$query = "
				select 
					ProphConsult_id as \"ProphConsult_id\"
				from v_ProphConsult
				where EvnPLDisp_id = :EvnPLDisp_id and RiskFactorType_id = :RiskFactorType_id
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result))
			{
				$resp = $result->result('array');
				if (count($resp) > 0) {
					return false;
				}
			}

			$params = array(
				'ProphConsult_id' => null,
				'EvnPLDisp_id' => $data['EvnPLDisp_id'],
				'RiskFactorType_id' => $data['RiskFactorType_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			$this->saveProphConsult($params);
		}
	}

	/**
	 *	Удаление
	 */
	function delProphConsult($data, $RiskFactorType_id) {
		// проверяем есть ли такой, если есть, то удаляем
		if (!empty($RiskFactorType_id)) {
			$data['RiskFactorType_id'] = $RiskFactorType_id;
			$query = "
				select 
					ProphConsult_id as \"ProphConsult_id\"
				from v_ProphConsult
				where EvnPLDisp_id = :EvnPLDisp_id and RiskFactorType_id = :RiskFactorType_id
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result))
			{
				$resp = $result->result('array');
				if (count($resp) > 0 && !empty($resp[0]['ProphConsult_id'])) {
					$params = array(
						'ProphConsult_id' => $resp[0]['ProphConsult_id'],
					);

					$proc = 'p_ProphConsult_del';
					$selectString = "
                        error_code as \"Error_Code\", 
                        error_message as \"Error_Msg\"
                    ";
					$query = "
			            select {$selectString}
                        from {$proc}(
                            prophconsult_id := :ProphConsult_id
                        );
                    ";

					$res = $this->db->query($query, $params);
					if ( is_object($res) ) {
						return $res->result('array');
					} else {
						return false;
					}
				}
			}
		}
	}
}
?>