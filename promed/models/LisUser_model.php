<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Lis
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Markoff Andrew <markov@swan.perm.ru>
* @version      06.2013
*/

class LisUser_model extends swModel
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Получение данных для авторизации
	 */	
	function getLisRequestData($data) {
		$params = array('pmUser_id'=>$data['pmUser_id'], 'login'=>$data['session']['login']);
		if ($data['pmUser_id']>0) {
			
			$query = "
				select top 1
					User_Login as login,
					User_Password as password,
					User_ClientId as clientId,
					IsNull(lpu.Lpu_nick, 'company') as company,
					IsNull(MedService_Name, 'lab') as lab,
					IsNull(:login, 'clienmachine') as machine,  -- не понятно, что передавать в это поле
					0 as instanceCount
				from lis.v_User u (nolock)
				left join v_MedService ms with(nolock) on ms.MedService_id = u.MedService_id
				left join v_Lpu lpu with(nolock) on lpu.Lpu_id = ms.Lpu_id
				where u.pmUser_insID = :pmUser_id -- todo: изменить на просто pmUser_id 
			";
			//echo getDebugSql($query, $params);die();
			$res = $this->db->query($query, $params);
			
			if (is_object($res)) {
				$resp = $res->result('array');
				if (count($resp) > 0) {
					$response = $resp[0];
					$response['sessionCode'] = rand(10000,20000); // код сессии для ЛИС-системы
					$response['password'] = md5($response['password']); // для рабочей версии пароль передается в зашифрованном виде
					return $response;
				}
			}
		}
		return false;
	}
	
	/**
	 * Возвращает пару логин-пароль и Id клиента ЛИС для редактирования
	 */	
	function get($data) {
		$params = array('pmUser_id'=>$data['pmUser_id']);
		$query = "
			select top 1
				User_id,
				User_Login,
				User_Password,
				User_ClientId,
				MedService_id
			from lis.v_User (nolock)
			where pmUser_insID = :pmUser_id
		";
		$res = $this->db->query($query, $params); // для пользователя
		
		if (is_object($res)) {
			return $res->result('array');
		}
		return false;
	}
	
	/**
	 * Сохраняет данные 
	 */
	function save($data) {
		if ( isset($data['User_id']) && $data['User_id'] > 0 ) {
			$proc = 'p_User_upd';
		}
		else {
			$proc = 'p_User_ins';
			$data['User_id'] = NULL;
		}
		$query = "
			declare
				@ResId bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @ResId = :User_id;
			exec lis." . $proc . "
				@User_id = @ResId output,
				@User_Login = :User_Login,
				@User_Password = :User_Password,
				@User_ClientId = :User_ClientId,
				@MedService_id = :MedService_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ResId as EvnDie_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$clientId = (!empty($data['User_ClientId']))?$data['User_ClientId']:((defined('LIS_CLIENTID'))?LIS_CLIENTID:'');
		if ( empty($clientId) ) {
			return array(array('Error_Msg' => 'В конфиг-файле отсутствует указание ClientID.'));
		}
		
		$queryParams = array(
			'User_id' => $data['User_id'],
			'User_Login' => $data['User_Login'],
			'User_Password' => $data['User_Password'],
			// https://redmine.swan.perm.ru/issues/22525
			'User_ClientId' => $clientId,
			'MedService_id' => $data['MedService_id'],
			'pmUser_id' => $data['pmUser_id'],
		);

		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}

		$response = $result->result('array');

		if ( is_array($response) || count($response) > 0 ) {
			return $response[0];
		} else {
			// todo: Тут надо сохранять информацию о невозможности сохранить в лог
			return false;
		}
	}
}
