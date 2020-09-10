<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * IPSessionCount_model - модель для работы с исключениями для количества параллельных сессий (?кто придумает лучше, можете поменять?)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Yavorskiy Maksim (m.yavorskiy@swan.perm.ru)
 * @version			16.10.2019
 */

class IPSessionCount_model extends SwPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Возвращает список коэффициентов индексации
	 */
   function loadIPSessionCountGrid($data)
	{
		$params = array();

		$query = "
			select
				ISC.IPSessionCount_id as \"IPSessionCount_id\",
				ISC.IPSessionCount_IP as \"IPSessionCount_IP\",
				ISC.IPSessionCount_Max as \"IPSessionCount_Max\"
			from
				v_IPSessionCount ISC
		";

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			$response['data'] = $result->result('array');
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Возвращает данные для редактирования коэффициента индексации
	 */
	function loadIPSessionCountEditForm($data)
	{
		$params = array('IPSessionCount_id' => $data['IPSessionCount_id']);

		$query = "
			select 
				ISC.IPSessionCount_id as \"IPSessionCount_id\",
				ISC.IPSessionCount_IP as \"IPSessionCount_IP\",
				ISC.IPSessionCount_Max as \"IPSessionCount_Max\"
			from
				v_IPSessionCount ISC
			where ISC.IPSessionCount_id = :IPSessionCount_id
            limit 1
		";

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	* Сохранение коэффициента индексации
	*/
   function saveIPSessionCount($data)
	{
 
        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\",
            IPSessionCount_id as \"IPSessionCount_id\"
        from p_IPSessionCount_" . (!empty($data['IPSessionCount_id']) && $data['IPSessionCount_id'] > 0 ? "upd" : "ins") . "
            (
 				IPSessionCount_id := :IPSessionCount_id,
				IPSessionCount_IP := :IPSessionCount_IP,
				IPSessionCount_Max := :IPSessionCount_Max,
				pmUser_id := :pmUser_id
            )";


		$params = array(
			'IPSessionCount_id' => (isset($data['IPSessionCount_id']) ? $data['IPSessionCount_id'] : null),
			'IPSessionCount_IP' => $data['IPSessionCount_IP'],
			'IPSessionCount_Max' => $data['IPSessionCount_Max'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Удаление ip-адреса из исключений
	 */
	function deleteIPSessionCount($data)
	{
 
        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\"
        from p_IPSessionCount_del
            (
                IPSessionCount_id := :IPSessionCount_id
            )";


		$params = array(
			'IPSessionCount_id' => $data['IPSessionCount_id']
		);

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

   function checkIPonExist($ip) {
		$params = array('IPSessionCount_ip' => $ip);

		$query = "
			select 
				ISC.IPSessionCount_Max as \"IPSessionCount_Max\"
			from
				v_IPSessionCount ISC 
			where ISC.IPSessionCount_IP = :IPSessionCount_ip
            limit 1
		";

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}
}