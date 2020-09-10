<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * HeredityDiag_model - модель для работы с записями в 'Наследственность по заболеваниям'
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Valery Bondarev
 * @version      15.12.2019
 */
class HeredityDiag_model extends swPgModel
{
	/**
	 *    Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 *    Загрузка грида
	 */
	function loadHeredityDiagGrid($data)
	{
		if (!empty($data['EvnPLDisp_id'])) {
			$filter = " and HD.EvnPLDisp_id = :EvnPLDisp_id";
		}

		if (!empty($data['HeredityDiag_id'])) {
			$filter = " and HD.HeredityDiag_id = :HeredityDiag_id";
		}

		$selectString = "
            HD.EvnPLDisp_id as \"EvnPLDisp_id\", 
            HD.HeredityDiag_id as \"HeredityDiag_id\", 
            HD.Diag_id as \"Diag_id\",
            D.Diag_Name as \"Diag_Name\",
            HD.HeredityType_id as \"HeredityType_id\",
            HT.HeredityType_Name as \"HeredityType_Name\"
        ";

		$query = "
			select 
				{$selectString}
			from
				v_HeredityDiag HD
				left join v_Diag D on D.Diag_id = HD.Diag_id
				left join v_HeredityType HT on HT.HeredityType_id = HD.HeredityType_id
			where
				(1=1) {$filter}
			order by
				HD.HeredityDiag_id
		";
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение данных для ЭМК
	 */
	function getHeredityDiagViewData($data)
	{
		if (empty($data['HeredityDiag_pid'])) {
			return array();
		}

		$filter = "";
		$queryParams = array(
			'EvnPLDisp_id' => $data['HeredityDiag_pid']
		);

		$selectString = "
            HD.HeredityDiag_id as \"HeredityDiag_id\", 
            HD.EvnPLDisp_id as \"EvnPLDisp_id\", 
            HD.Diag_id as \"Diag_id\",
            D.Diag_Code as \"Diag_Code\",
            D.Diag_Name as \"Diag_Name\",
            HT.HeredityType_Name as \"HeredityType_Name\"
        ";

		$query = "
			select
				{$selectString}
			from
				v_HeredityDiag HD
				left join v_Diag D on D.Diag_id = HD.Diag_id
				left join v_HeredityType HT on HT.HeredityType_id = HD.HeredityType_id
			where
				HD.EvnPLDisp_id = :EvnPLDisp_id
				{$filter}
		";

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return swFilterResponse::filterNotViewDiag($result->result('array'), $data);
		} else {
			return false;
		}
	}

	/**
	 *    Проверка существования
	 */
	function checkHeredityDiagExists($data)
	{

		$selectString = "hereditydiag_id as \"HeredityDiag_id\"";
		$queryParams = array(
			'EvnPLDisp_id' => $data['EvnPLDisp_id'],
			'Diag_id' => $data['Diag_id'],
			'HeredityDiag_id' => $data['HeredityDiag_id'],
		);

		$query = "
			select
				{$selectString}
			from
				v_HeredityDiag
			where
				EvnPLDisp_id = :EvnPLDisp_id
				and Diag_id = :Diag_id
				and HeredityDiag_id <> coalesce(CAST(:HeredityDiag_id as bigint),0)
			limit 1	
		";

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return false;
			}
		}

		return true;
	}

	/**
	 *    Сохранение
	 */
	function saveHeredityDiag($data)
	{
		if (!empty($data['HeredityDiag_id']) && $data['HeredityDiag_id'] > 0) {
			$proc = "p_HeredityDiag_upd";
		} else {
			$proc = "p_HeredityDiag_ins";
		}


		$selectString = "
            hereditydiag_id as \"HeredityDiag_id\", 
            error_code as \"Error_Code\", 
            error_message as \"Error_Msg\"
        ";

		$queryParams = array(
			'HeredityDiag_id' => $data['HeredityDiag_id'],
			'EvnPLDisp_id' => $data['EvnPLDisp_id'],
			'Diag_id' => $data['Diag_id'],
			'HeredityType_id' => $data['HeredityType_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "
			select {$selectString}
			from {$proc}(
			    hereditydiag_id := :HeredityDiag_id,
			    evnpldisp_id := :EvnPLDisp_id,
			    diag_id := :Diag_id,
			    hereditytype_id := :HeredityType_id,
			    pmuser_id := :pmUser_id
			);
		";

		$res = $this->db->query($query, $queryParams);
		if (is_object($res)) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 *    Добавление
	 */
	function addHeredityDiag($data, $diag_id, $hereditytype_id)
	{
		// проверяем есть ли такой диагноз уже, если нет, то добавляем
		if (!empty($diag_id)) {
			$data['Diag_id'] = $diag_id;
			$query = "
				select
					hereditydiag_id as \"HeredityDiag_id\" 
				from v_HeredityDiag
				where EvnPLDisp_id = :EvnPLDisp_id and Diag_id = :Diag_id
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					return false;
				}
			}

			$params = array(
				'HeredityDiag_id' => null,
				'EvnPLDisp_id' => $data['EvnPLDisp_id'],
				'Diag_id' => (is_null($data['Diag_id']) ? null : $data['Diag_id']),
				'HeredityType_id' => (is_null($hereditytype_id) ? null : $hereditytype_id),
				'pmUser_id' => $data['pmUser_id']
			);
			$this->saveHeredityDiag($params);
		}
	}

	/**
	 *    Удаление
	 */
	function delHeredityDiag($data)
	{

		$process = 'p_HeredityDiag_del';
		$queryParams = array(
			'HeredityDiag_id' => $data['HeredityDiag_id']
		);

		$selectString = "
            error_code as \"Error_Code\", 
            error_message as \"Error_Msg\"
        ";
		$query = "
			select {$selectString}
            from {$process}(
                hereditydiag_id := :HeredityDiag_id
            );
        ";

		$res = $this->db->query($query, $queryParams);
		if (is_object($res)) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 *    Удаление по диагнозу
	 */
	function delHeredityDiagByDiag($data, $diag_id)
	{
		// проверяем есть ли такой диагноз уже, если есть, то удаляем
		if (!empty($diag_id)) {
			$data['Diag_id'] = $diag_id;
			$query = "
				select
					hereditydiag_id as \"HeredityDiag_id\"
				from v_HeredityDiag
				where EvnPLDisp_id = :EvnPLDisp_id and Diag_id = :Diag_id
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0 && !empty($resp[0]['HeredityDiag_id'])) {
					$params = array(
						'HeredityDiag_id' => $resp[0]['HeredityDiag_id'],
						'pmUser_id' => $data['pmUser_id']
					);
					$this->delHeredityDiag($params);
				}
			}
		}
	}
}

?>