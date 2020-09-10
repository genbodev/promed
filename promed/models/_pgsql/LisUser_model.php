<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * Class LisUser_model
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Lis
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       Markoff Andrew <markov@swan.perm.ru>
 * @version      06.2013
 *
 * @property CI_DB_driver $db
 */
class LisUser_model extends swPgModel
{
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение данных для авторизации
	 * @param $data
	 * @return bool
	 */
	function getLisRequestData($data)
	{
		$params = [
			"pmUser_id" => $data["pmUser_id"],
			"login" => $data["session"]["login"]
		];
		if ($data["pmUser_id"] == 0) {
			return false;
		}
		$query = "
			select
				User_Login as \"login\",
				User_Password as \"password\",
				User_ClientId as \"clientId\",
				coalesce(lpu.Lpu_nick, 'company') as \"company\",
				coalesce(MedService_Name, 'lab') as \"lab\",
				coalesce(:login, 'clienmachine') as \"machine\",
				0 as \"instanceCount\"
			from
				lis.v_User u
				left join v_MedService ms on ms.MedService_id = u.MedService_id
				left join v_Lpu lpu on lpu.Lpu_id = ms.Lpu_id
			where u.pmUser_insID = :pmUser_id
			limit 1 
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result_array();
		if (count($resp) == 0) {
			return false;
		}
		$response = $resp[0];
		$response["sessionCode"] = rand(10000, 20000); // код сессии для ЛИС-системы
		$response["password"] = md5($response["password"]); // для рабочей версии пароль передается в зашифрованном виде
		return $response;
	}

	/**
	 * Возвращает пару логин-пароль и Id клиента ЛИС для редактирования
	 * @param $data
	 * @return array|bool
	 */
	function get($data)
	{
		$params = ["pmUser_id" => $data["pmUser_id"]];
		$query = "
			select
				User_id as \"User_id\",
				User_Login as \"User_Login\",
				User_Password as \"User_Password\",
				User_ClientId as \"User_ClientId\",
				MedService_id as \"MedService_id\"
			from lis.v_User
			where pmUser_insID = :pmUser_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params); // для пользователя
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Сохраняет данные
	 * @param $data
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	function save($data)
	{
		$procedure = (isset($data["User_id"]) && $data["User_id"] > 0) ? "p_User_upd" : "p_User_ins";
		if (!(isset($data["User_id"]) && $data["User_id"] > 0)) {
			$data["User_id"] = null;
		}
		$selectString = "
		    user_id as \"EvnDie_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from lis.{$procedure}(
			    user_id := :User_id,
			    medservice_id := :MedService_id,
			    user_clientid := :User_ClientId,
			    user_login := :User_Login,
			    user_password := :User_Password,
			    pmuser_id := :pmUser_id
			);
		";
		$clientId = (!empty($data["User_ClientId"]))
			? $data["User_ClientId"]
			: (defined("LIS_CLIENTID")
				? LIS_CLIENTID
				: "");
		if (empty($clientId)) {
			throw new Exception("В конфиг-файле отсутствует указание ClientID.");
		}
		$queryParams = [
			"User_id" => $data["User_id"],
			"User_Login" => $data["User_Login"],
			"User_Password" => $data["User_Password"],
			"User_ClientId" => $clientId,
			"MedService_id" => $data["MedService_id"],
			"pmUser_id" => $data["pmUser_id"],
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$response = $result->result_array();
		if (!(is_array($response) || count($response) > 0)) {
			return false;
		}
		return $response[0];
	}
}