<?php
defined('BASEPATH') or die('No direct script access allowed');

/**
 * AdminVIPPerson_User_model - молеь для работы с VIP пациентами
 * пользовательская  часть 
 * 
 * @package			 
 * @author			 
 * @version			25.02.2019
 */
class AdminVIPPerson_User_model extends swModel {
	
	/**
	 *  * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	 *  Поиск пациента в регистре
	 */
	function checkrecordVIP($params) {
		$params = array(
			'Person_id' => $params['Person_id'],
			'Lpu_id' => $params['Lpu_id']
		);

		$query = "SELECT VIPPerson_id as VIPPerson_id
				  FROM dbo.VIPPerson with (nolock)
				  where Person_id = :Person_id  and Lpu_id = :Lpu_id ";

		$result = $this->db->query($query, $params);
		sql_log_message('error', 'checkrecordVIP: ', getDebugSql($query, $params));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Работа с регистром (insert,update)
	 */
	function saveVIPPerson($params) {


		if ($params['Operation'] === 'ins') {
			$aparams = array(
				'Person_id' => $params['Person_id'],
				'Lpu_id' => $params['Lpu_id'],
				'pmUser_id' => $params['pmUser_id']
			);
			$query = "declare 
						@Person_id bigint,
						@Lpu_id bigint,
						@pmUser_id bigint,
						@Error_Code int,
						@Error_Message varchar(4000)
				 exec dbo.p_VIPPerson_ins
						@Person_id = :Person_id,
						@Lpu_id = :Lpu_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output 
            select @Error_Code as Error_Code, @Error_Message as Error_Message; 
        ";
		};
		if ($params['Operation'] === 'upd') {
			$aparams = array(
				'Person_id' => $params['Person_id'],
				'Lpu_id' => $params['Lpu_id'],
				'pmUser_id' => $params['pmUser_id'],
				'VIPPerson_id' => $params['VIPPerson_id'],
				'VIPPerson_setDate' => $params['VIPPerson_setDate']
			);
			//echo '<pre>' . print_r($aparams, 1) . '</pre>';
			$query = "declare 
						@Person_id bigint,
						@Lpu_id bigint,
						@VIPPerson_id bigint,
						@VIPPerson_disDate datetime,
						@VIPPerson_setDate datetime,
						@pmUser_id bigint,
						@Error_Code int,
						@Error_Message varchar(4000)
				 exec dbo.p_VIPPerson_upd
						@VIPPerson_id = :VIPPerson_id,
						@Person_id = :Person_id,
						@Lpu_id = :Lpu_id,
						@VIPPerson_setDate = :VIPPerson_setDate,
						@VIPPerson_disDate = null,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output 
            select @Error_Code as Error_Code, @Error_Message as Error_Message ";
		};
		if ($params['Operation'] === 'del') {
			$aparams = array(
				'VIPPerson_disDate' => $params['VIPPerson_disDate'],
				'pmUser_id' => $params['pmUser_id'],
				'VIPPerson_id' => $params['VIPPerson_id']
			);
			$query = "declare 
						@VIPPerson_id bigint,
						@VIPPerson_disDate datetime,
						@VIPPerson_setDate datetime,
						@VIPPerson_deleted bigint,
						@pmUser_id bigint,
						@Error_Code int,
						@Error_Message varchar(4000)
				 exec dbo.p_VIPPerson_upd
						@VIPPerson_id = :VIPPerson_id,
						@VIPPerson_setDate = null,
						@VIPPerson_disDate = :VIPPerson_disDate,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output 
            select @Error_Code as Error_Code, @Error_Message as Error_Message ";
		}
		$result = $this->db->query($query, $aparams);
		sql_log_message('error', 'SaveVIP: ', getDebugSql($query, $params));
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

}

