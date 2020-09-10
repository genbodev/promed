<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * AccessRightsPrivilegeType_model - модель для работы c правами доступа к льготам
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 */
require_once('AccessRights_model.php');

class AccessRightsPrivilegeType_model extends AccessRights_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение списка групп с ограничением доступа
	 */
	function loadAccessRightsGrid($data) {
		$params = array();

		$query = "
			select
				ARPT.AccessRightsName_id,
				PT.PrivilegeType_Name as AccessRightsName_Name,
				PT.PrivilegeType_Code,
				PT.PrivilegeType_Name
			from
				v_AccessRightsPrivilegeType ARPT with (nolock)
				inner join v_AccessRightsName ARN (nolock) on ARN.AccessRightsName_id = ARPT.AccessRightsName_id
				inner join v_PrivilegeType PT with(nolock) on PT.PrivilegeType_id = ARPT.PrivilegeType_id
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$response = $result->result('array');
		return array('data' => $response);
	}

	/**
	 * Сохранение группы для ограничения доступа
	 */
	function saveAccessRights($data) {
		$this->beginTransaction();

		$data['AccessRightsType_id'] = 5;
		$response = $this->saveAccessRightsName($data);
		if (!empty($response[0]['Error_Msg'])) {
			$this->rollbackTransaction();
			return $response;
		}
		
		$data['AccessRightsName_id'] = $response[0]['AccessRightsName_id'];
	
		$check = $this->checkAccessRightsIntersection($data);
		if (!empty($check['Error_Msg'])) {
			return array($check);
		}
		if (!empty($check['AccessRightsPrivilegeType_id'])) {
			return array(array(
				'Error_Msg' => "Указанная льгота уже есть в списке"
			));
		}

		$params = array(
			'AccessRightsPrivilegeType_id' => $data['AccessRightsPrivilegeType_id'],
			'AccessRightsName_id' => $data['AccessRightsName_id'],
			'PrivilegeType_id' => $data['PrivilegeType_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		if (!empty($params['AccessRightsPrivilegeType_id']) && $params['AccessRightsPrivilegeType_id'] > 0) {
			$procedure = 'p_AccessRightsPrivilegeType_upd';
		} else {
			$params['AccessRightsPrivilegeType_id'] = null;
			$procedure = 'p_AccessRightsPrivilegeType_ins';
		}

		$query = "
			declare
				@AccessRightsPrivilegeType_id bigint = :AccessRightsPrivilegeType_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@AccessRightsPrivilegeType_id = @AccessRightsPrivilegeType_id output,
				@AccessRightsName_id = :AccessRightsName_id,
				@PrivilegeType_id = :PrivilegeType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @AccessRightsPrivilegeType_id as AccessRightsPrivilegeType_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка при сохранении тестов в группе'));
		}

		$this->commitTransaction();
		return $response;
	}

	/**
	 * Проверка на пересечениями с другими льготами
	 */
	function checkAccessRightsIntersection($data) {

		$query = "
			select
				top 1
					ART.AccessRightsPrivilegeType_id
				from
					v_AccessRightsPrivilegeType ART (nolock)
				where
					ART.AccessRightsName_id != :AccessRightsName_id
					and ART.PrivilegeType_id = :PrivilegeType_id
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при поиске дублей льгот');
		}
		$resp = $result->result('array');
		if (count($resp) == 0){
			$resp = array(array());
		}
		return $resp[0];
	}

	/**
	 * Удаление тестов из группы для ограничения доступа
	 */
	function deleteAccessRightsPrivilegeType($data) {
		$params = array('AccessRightsPrivilegeType_id' => $data['AccessRightsPrivilegeType_id']);

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_AccessRightsPrivilegeType_del
				@AccessRightsPrivilegeType_id = :AccessRightsPrivilegeType_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		$response = $this->getFirstRowFromQuery($query, $params);
		if (!$response) {
			$response = array('Error_Msg' => 'Ошибка при удалении ограничения доступа к льготам');
		}

		return array($response);
	}

	/**
	 * Удаление группы для ограничения доступа
	 */
	function deleteAccessRights($data) {
		$params = array('AccessRightsName_id' => $data['AccessRightsName_id']);

		$this->beginTransaction();

		$query = "
			select ARL.AccessRightsLimit_id
			from v_AccessRightsLimit ARL with(nolock)
			where ARL.AccessRightsName_id = :AccessRightsName_id
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Ошибка удаления доступа к льготе'));
		}
		$limits = $result->result('array');
		foreach($limits as $item) {
			$resp = $this->deleteAccessRightsLimit($item);
			if (!empty($resp[0]['Error_Msg'])) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$query = "
			select ART.AccessRightsPrivilegeType_id
			from v_AccessRightsPrivilegeType ART with(nolock)
			where ART.AccessRightsName_id = :AccessRightsName_id
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Ошибка при удалении доступа к льготе'));
		}
		$tests = $result->result('array');
		foreach($tests as $item) {
			$resp = $this->deleteAccessRightsPrivilegeType($item);
			if (!empty($resp[0]['Error_Msg'])) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$response = $this->deleteAccessRightsName($data);
		if (!empty($response[0]['Error_Msg'])) {
			$this->rollbackTransaction();
		} else {
			$this->commitTransaction();
		}

		return $response;
	}

	/**
	 * Получение данных для редактирования группы ограничения доступа
	 */
	function loadAccessRightsForm($data) {
		$params = array('AccessRightsName_id' => $data['AccessRightsName_id']);

		$query = "
			select
				ART.AccessRightsPrivilegeType_id,
				ART.AccessRightsName_id,
				ART.PrivilegeType_id
			from v_AccessRightsPrivilegeType ART with(nolock)
			where ART.AccessRightsName_id = :AccessRightsName_id
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка получения льготы'));
		}
		return $result->result('array');
	}
}