<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* NeedConsult_model - модель для работы с записями в 'Показания к консультации врача-специалиста'
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      02.07.2013
*/

class NeedConsult_model extends SwPgModel
{
	/**
	 * NeedConsult_model constructor.
	 */
    function __construct()
    {
        parent::__construct();
    }

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function loadNeedConsultGrid($data)
	{
		$filter = " and NC.EvnPLDisp_id = :EvnPLDisp_id";
		
		if (!empty($data['NeedConsult_id'])) {
			$filter = " and NC.NeedConsult_id = :NeedConsult_id";
		}
		
		$query = "
			select 
				NC.EvnPLDisp_id as \"EvnPLDisp_id\",
				NC.NeedConsult_id as \"NeedConsult_id\",
				NC.ConsultationType_id as \"ConsultationType_id\",
				NC.Post_id as \"Post_id\",
				P.name as \"Post_Name\",
				CT.ConsultationType_Name as \"ConsultationType_Name\"
			from
				v_NeedConsult NC
				left join v_ConsultationType CT on CT.ConsultationType_id = NC.ConsultationType_id
				left join persis.v_Post P on P.id = NC.Post_id
			where
				(1=1) {$filter}
			order by
				NC.NeedConsult_id
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
	function getNeedConsultViewData($data) {
		if (empty($data['NeedConsult_pid'])) {
			return array();
		}

		$filter = "";
		$queryParams = array(
			'EvnPLDisp_id' => $data['NeedConsult_pid']
		);

		$query = "
			select
				NC.NeedConsult_id as \"NeedConsult_id\",
				NC.EvnPLDisp_id as \"EvnPLDisp_id\",
				P.name as \"Post_Name\",
				CT.ConsultationType_Name as \"ConsultationType_Name\"
			from
				v_NeedConsult NC
				left join v_ConsultationType CT on CT.ConsultationType_id = NC.ConsultationType_id
				left join persis.v_Post P on P.id = NC.Post_id
			where
				NC.EvnPLDisp_id = :EvnPLDisp_id
				{$filter}
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
	function checkNeedConsultExists($data) {
		$query = "
			select
				NeedConsult_id as \"NeedConsult_id\"
			from
				v_NeedConsult
			where
				EvnPLDisp_id = :EvnPLDisp_id
				and Post_id = :Post_id
				and ConsultationType_id = :ConsultationType_id
				and NeedConsult_id <> coalesce(CAST(:NeedConsult_id as bigint),0)
			limit 1
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
	function saveNeedConsult($data) {
		if (!empty($data['NeedConsult_id']) && $data['NeedConsult_id'] > 0) {
			$proc = "p_NeedConsult_upd";
		} else {
			$proc = "p_NeedConsult_ins";
		}
		
		$sql = "
			select
				NeedConsult_id as \"NeedConsult_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc}(
				NeedConsult_id := :NeedConsult_id,
				EvnPLDisp_id := :EvnPLDisp_id,
				Post_id := :Post_id,
				ConsultationType_id := :ConsultationType_id,
				pmUser_id := :pmUser_id
			);
		";
   		$res = $this->db->query($sql, $data);
		if ( is_object($res) ) {
 	    	return $res->result('array');
		} else {
 	    	return false;
		}
	}

	/**
	 *	Добавление
	 */
	function addNeedConsult($data, $Post_id, $ConsultationType_id) {
		// проверяем есть ли такой, если нет, то добавляем
		if (!empty($Post_id) && !empty($ConsultationType_id)) {
			$data['Post_id'] = $Post_id;
			$data['ConsultationType_id'] = $ConsultationType_id;
			$query = "
				select
					NeedConsult_id as \"NeedConsult_id\"
				from v_NeedConsult
				where EvnPLDisp_id = :EvnPLDisp_id
					and Post_id = :Post_id
					and ConsultationType_id = :ConsultationType_id
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
				'NeedConsult_id' => null,
				'EvnPLDisp_id' => $data['EvnPLDisp_id'],
				'Post_id' => $data['Post_id'],
				'ConsultationType_id' => $data['ConsultationType_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			$this->saveNeedConsult($params);
		}
	}

	/**
	 *	Удаление
	 */
	function delNeedConsult($data, $Post_id, $ConsultationType_id) {
		// проверяем есть ли такой, если есть, то удаляем
		if (!empty($Post_id) && !empty($ConsultationType_id)) {
			$data['Post_id'] = $Post_id;
			$data['ConsultationType_id'] = $ConsultationType_id;
			$query = "
				select
					NeedConsult_id as \"NeedConsult_id\"
				from v_NeedConsult
				where EvnPLDisp_id = :EvnPLDisp_id
					and Post_id = :Post_id
					and ConsultationType_id = :ConsultationType_id
				limit 1
			";

			$result = $this->db->query($query, $data);

			if (is_object($result))
			{
				$resp = $result->result('array');
				if (count($resp) > 0 && !empty($resp[0]['NeedConsult_id'])) {
					$params = array(
						'NeedConsult_id' => $resp[0]['NeedConsult_id'],
						'pmUser_id' => $data['pmUser_id']
					);

					$sql = "
						select
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_NeedConsult_del(
							NeedConsult_id := :NeedConsult_id
						)
					";
					$res = $this->db->query($sql, $params);
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