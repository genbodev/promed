<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * AccessRightsDiag_model - модель для работы c правами доступа к диагнозам
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			28.08.2014
 */
require_once('AccessRights_model.php');

class AccessRightsDiag_model extends AccessRights_model {
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
			where ARN.AccessRightsType_id = 1
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$response = $result->result('array');

		$query = "
			select
				ARD.AccessRightsName_id,
				fD.Diag_Code as Diag_fCode,
				tD.Diag_Code as Diag_tCode
			from
				v_AccessRightsDiag ARD with(nolock)
				left join v_Diag fD with(nolock) on fD.Diag_id = ARD.Diag_fid
				left join v_Diag tD with(nolock) on tD.Diag_id = ARD.Diag_tid
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result('array');
		$code_arr = array();

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
			$item['AccessRightsDiag_Codes'] = isset($code_arr[$key]) ? implode(', ', $code_arr[$key]) : '';
		}

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
					$resp = $this->saveAccessRightsDiag($AccessRights);
					break;
				case 3:
					$resp = $this->deleteAccessRightsDiag($AccessRights);
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
	 * Проверка на пересечениями с другими группами диагнозов
	 */
	function checkAccessRightsIntersection($data) {
		$params = array(
			'Diag_fid' => $data['Diag_fid'],
			'Diag_tid' => $data['Diag_tid']
		);

		$query = "
			declare @fid bigint = :Diag_fid
			declare @tid bigint = :Diag_tid

			declare @fdiag varchar(5) = (select top 1 t.Diag_Code from v_Diag t with(nolock) where t.Diag_id = @fid)
			declare @tdiag varchar(5) = ''
			if @tid is not null set @tdiag = (select top 1 t.Diag_Code from v_Diag t with(nolock) where t.Diag_id = @tid)

			select top 1 ARN.AccessRightsName_Name
			from v_AccessRightsDiag ARD with(nolock)
				left join v_Diag fD with(nolock) on fD.Diag_id = ARD.Diag_fid
				left join v_Diag tD with(nolock) on tD.Diag_id = ARD.Diag_tid
				left join v_AccessRightsName ARN with(nolock) on ARN.AccessRightsName_id = ARD.AccessRightsName_id
			where
				1=(case
					when tD.Diag_id is null
						then case when fD.Diag_Code >= @fdiag and fD.Diag_Code <= @tdiag then 1 else 0 end
					when fD.Diag_Code > @fdiag and fD.Diag_Code > @tdiag or tD.Diag_Code < @fdiag and (tD.Diag_Code < @tdiag or @tdiag = '')
						then 0 else 1
				end)
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при поиске пересечений диагнозов');
		}
		$resp = $result->result('array');
		if (count($resp) == 0){
			$resp = array(array());
		}
		return $resp[0];
	}

	/**
	 * Сохранение диагноза/диапозона диагнозов в группе для ограничения доступа
	 */
	function saveAccessRightsDiag($data) {
		if (empty($data['allowIntersection']) || !$data['allowIntersection']) {
			$check = $this->checkAccessRightsIntersection($data);
			if (!empty($check['Error_Msg'])) {
				return array($check);
			}
			if (!empty($check['AccessRightsName_Name'])) {
				return array(array(
					'Alert_Msg' => "Указанный диагноз (группа диагнозов) уже имеется в группе диагнозов '{$check['AccessRightsName_Name']}'.",
					'Alert_Code' => 1
				));
			}
		}

		$params = array(
			'AccessRightsDiag_id' => $data['AccessRightsDiag_id'],
			'AccessRightsName_id' => $data['AccessRightsName_id'],
			'Diag_fid' => $data['Diag_fid'],
			'Diag_tid' => $data['Diag_tid'],
			'pmUser_id' => $data['pmUser_id']
		);

		if (!empty($params['AccessRightsDiag_id']) && $params['AccessRightsDiag_id'] > 0) {
			$procedure = 'p_AccessRightsDiag_upd';
		} else {
			$params['AccessRightsDiag_id'] = null;
			$procedure = 'p_AccessRightsDiag_ins';
		}

		$query = "
			declare
				@AccessRightsDiag_id bigint = :AccessRightsDiag_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@AccessRightsDiag_id = @AccessRightsDiag_id output,
				@AccessRightsName_id = :AccessRightsName_id,
				@Diag_fid = :Diag_fid,
				@Diag_tid = :Diag_tid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @AccessRightsDiag_id as AccessRightsDiag_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка при сохранении диагнозов в группе'));
		}
		$response = $result->result('array');

		return $response;
	}

	/**
	 * Удаление диагноза/диапозона диагнозов из группы для ограничения доступа
	 */
	function deleteAccessRightsDiag($data) {
		$params = array('AccessRightsDiag_id' => $data['AccessRightsDiag_id']);

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_AccessRightsDiag_del
				@AccessRightsDiag_id = :AccessRightsDiag_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		$response = $this->getFirstRowFromQuery($query, $params);
		if (!$response) {
			$response = array('Error_Msg' => 'Ошибка при удалении ограничения доступа к диагнозам');
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
			return array(array('Error_Msg' => 'Ошибка удаления доступа к группе диагнозов'));
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
			select ARD.AccessRightsDiag_id
			from v_AccessRightsDiag ARD with(nolock)
			where ARD.AccessRightsName_id = :AccessRightsName_id
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Ошибка при удалении диагнозов в группе'));
		}
		$diags = $result->result('array');
		foreach($diags as $item) {
			$resp = $this->deleteAccessRightsDiag($item);
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
			return array(array('Error_Msg' => 'Ошибка получения наименования группы диагнозов'));
		}
		$response = $result->result('array');

		$query = "
			select
				ARD.AccessRightsDiag_id,
				ARD.Diag_fid,
				ARD.Diag_tid,
				1 as RecordStatus_Code
			from v_AccessRightsDiag ARD with(nolock)
			where ARD.AccessRightsName_id = :AccessRightsName_id
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка получения диагнозов группы'));
		}
		$diag_arr = $result->result('array');

		$response[0]['AccessRightsData'] = json_encode($diag_arr);

		return $response;
	}
}