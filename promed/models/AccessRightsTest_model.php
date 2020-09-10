<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * AccessRightsTest_model - модель для работы c правами доступа к тестам
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Abakhri Samir
 * @version			09.07.2015
 */
require_once('AccessRights_model.php');

class AccessRightsTest_model extends AccessRights_model {
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

		$query  = "
			select
				ARN.AccessRightsName_id,
				ARN.AccessRightsName_Code,
				ARN.AccessRightsName_Name
			from v_AccessRightsName ARN with(nolock)
			where ARN.AccessRightsType_id = 4
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$response = $result->result('array');

		$query = "
			select
				ART.AccessRightsName_id,
				ARN.AccessRightsName_Name,
				max(Tests.AccessRightsTest_Codes) as AccessRightsTest_Codes
			from
				v_AccessRightsTest ART with (nolock)
				left join v_AccessRightsName ARN (nolock) on ARN.AccessRightsName_id = ART.AccessRightsName_id
				outer apply (
					SELECT STUFF(
					(
					 select
							', ' + cast(UC.UslugaComplex_Code as varchar)
						from
							v_UslugaComplex UC (nolock)
							left join v_AccessRightsTest (nolock) ARTU on UC.UslugaComplex_id = ARTU.UslugaComplex_id
						where
							UC.UslugaComplex_id = ARTU.UslugaComplex_id
							and ARTU.AccessRightsName_id = ART.AccessRightsName_id
					FOR XML PATH ('')),1,1,'') as AccessRightsTest_Codes
				) as Tests
			group by ART.AccessRightsName_id, ARN.AccessRightsName_Name
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$response = $result->result('array');
		/*$code_arr = array();

		foreach($resp as $item) {
			$key = $item['AccessRightsName_id'];
			if (empty($item['Diag_tCode'])) {
				$code_arr[$key][] = $item['Diag_fCode'];
			} else {
				$code_arr[$key][] = $item['Diag_fCode'].' - '.$item['Diag_tCode'];
			}
		}

		foreach($response as &$item) {
			$key = $item['AccessRightsName_id'];
			$item['AccessRightsTest_Codes'] = isset($code_arr[$key]) ? implode(', ', $code_arr[$key]) : '';
		}*/

		return array('data' => $response);
	}

	/**
	 * Сохранение группы для ограничения доступа
	 */
	function saveAccessRights($data) {
		$this->beginTransaction();

		$response = $this->saveAccessRightsName($data);
		if (!empty($response[0]['Error_Msg'])) {
			$this->rollbackTransaction();
			return $response;
		}

		$AccessRightsData = json_decode($data['AccessRightsData'], true);
		foreach($AccessRightsData as $AccessRights) {
			$AccessRights['AccessRightsName_id'] = $response[0]['AccessRightsName_id'];
			$AccessRights['pmUser_id'] = $data['pmUser_id'];
			$AccessRights['allowIntersection'] = $data['allowIntersection'];
			switch($AccessRights['RecordStatus_Code']) {
				case 1:
					$resp = true;
					break;
				case 0:
				case 2:
					$resp = $this->saveAccessRightsTest($AccessRights);
					break;
				case 3:
					$resp = $this->deleteAccessRightsTest($AccessRights);
			}
			if (!empty($resp[0]['Error_Msg']) || !empty($resp[0]['Alert_Msg'])) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$this->commitTransaction();
		return $response;
	}

	/**
	 * Проверка на пересечениями с другими группами тестов
	 */
	function checkAccessRightsIntersection($data) {

		$query = "
			select
				top 1
					ARN.AccessRightsName_Name
				from
					v_AccessRightsName ARN (nolock)
					inner join v_AccessRightsTest ART (nolock) on ARN.AccessRightsName_id = ART.AccessRightsName_id
				where
					ART.AccessRightsName_id != :AccessRightsName_id
					and ART.UslugaComplex_id = :UslugaComplex_id
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при поиске пересечений тестов');
		}
		$resp = $result->result('array');
		if (count($resp) == 0){
			$resp = array(array());
		}
		return $resp[0];
	}

	/**
	 * Сохранение тестов в группе для ограничения доступа
	 */
	function saveAccessRightsTest($data) {
		if (empty($data['allowIntersection']) || !$data['allowIntersection']) {
			$check = $this->checkAccessRightsIntersection($data);
			if (!empty($check['Error_Msg'])) {
				return array($check);
			}
			if (!empty($check['AccessRightsName_Name'])) {
				return array(array(
					'Alert_Msg' => "Указанный тест заведен в группе тестов '{$check['AccessRightsName_Name']}'.",
					'Alert_Code' => 1
				));
			}
		}

		$params = array(
			'AccessRightsTest_id' => $data['AccessRightsTest_id'],
			'AccessRightsName_id' => $data['AccessRightsName_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		if (!empty($params['AccessRightsTest_id']) && $params['AccessRightsTest_id'] > 0) {
			$procedure = 'p_AccessRightsTest_upd';
		} else {
			$params['AccessRightsTest_id'] = null;
			$procedure = 'p_AccessRightsTest_ins';
		}

		$query = "
			declare
				@AccessRightsTest_id bigint = :AccessRightsTest_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@AccessRightsTest_id = @AccessRightsTest_id output,
				@AccessRightsName_id = :AccessRightsName_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @AccessRightsTest_id as AccessRightsTest_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка при сохранении тестов в группе'));
		}
		$response = $result->result('array');

		return $response;
	}

	/**
	 * Удаление тестов из группы для ограничения доступа
	 */
	function deleteAccessRightsTest($data) {
		$params = array('AccessRightsTest_id' => $data['AccessRightsTest_id']);

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_AccessRightsTest_del
				@AccessRightsTest_id = :AccessRightsTest_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		$response = $this->getFirstRowFromQuery($query, $params);
		if (!$response) {
			$response = array('Error_Msg' => 'Ошибка при удалении ограничения доступа к тестам');
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
			return array(array('Error_Msg' => 'Ошибка удаления доступа к группе тестов'));
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
			select ART.AccessRightsTest_id
			from v_AccessRightsTest ART with(nolock)
			where ART.AccessRightsName_id = :AccessRightsName_id
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Ошибка при удалении диагнозов в группе'));
		}
		$tests = $result->result('array');
		foreach($tests as $item) {
			$resp = $this->deleteAccessRightsTest($item);
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
			select top 1
				ARN.AccessRightsName_id,
				ARN.AccessRightsName_Name
			from v_AccessRightsName ARN with(nolock)
			where ARN.AccessRightsName_id = :AccessRightsName_id
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка получения наименования группы тестов'));
		}
		$response = $result->result('array');

		$query = "
			select
				ART.AccessRightsTest_id,
				ART.UslugaComplex_id,
				1 as RecordStatus_Code
			from v_AccessRightsTest ART with(nolock)
			where ART.AccessRightsName_id = :AccessRightsName_id
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка получения тестов группы'));
		}
		$diag_arr = $result->result('array');

		$response[0]['AccessRightsData'] = json_encode($diag_arr);

		return $response;
	}
}