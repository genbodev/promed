<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * AccessRightsLpu_model - модель для работы c правами доступа к МО
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

class AccessRightsLpu_model extends AccessRights_model {
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
				ARN.AccessRightsName_id as \"AccessRightsName_id\",
				ARN.AccessRightsName_Code as \"AccessRightsName_Code\",
				ARN.AccessRightsName_Name as \"AccessRightsName_Name\"
			from v_AccessRightsName ARN
			where ARN.AccessRightsType_id = 2
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$response = $result->result('array');

		$query = "
			select
				ARO.AccessRightsName_id as \"AccessRightsName_id\",
				Lpu.Lpu_id as \"Lpu_id\",
				Lpu.Lpu_Nick as \"Lpu_Nick\",
				ARO.AccessRightsOrg_id as \"AccessRightsOrg_id\"
			from
				v_AccessRightsOrg ARO
				inner join v_Lpu Lpu on Lpu.Org_id = ARO.Org_id
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result('array');
		$lpu_arr = array();
		$lpuBuilding_arr = array();
		$span = "<span style='display:block; float:left; position:relative; left: -10px; cursor: pointer;width:10px;'
 onclick=\"javascript: var div=this.nextElementSibling; if(div.style.display == 'none'){div.style.display = 'block';this.innerHTML='-';}else{div.style.display = 'none';this.innerHTML='+';}\">+</span>";
		$spanZ = "<span style='display:block; float:left; position:relative; left: -10px; cursor: pointer;width:10px;'>&nbsp;</span>";
		foreach($resp as $item) {
			$key = $item['AccessRightsName_id'];
			$hint = '';
			//$lpu_arr[$key][] = $item['Lpu_Nick'];
			//получим подразделения, если есть
			$lpuBuildings = $this->getAccessRightsLpuBuildingOrg(array('AccessRightsOrg_id' => $item['AccessRightsOrg_id']));
			if(count($lpuBuildings)>0){
				foreach($lpuBuildings as $val) {
					if(!empty($val['LpuBuilding_Name'])) $lpuBuilding_arr[$key][] = $val['LpuBuilding_Name'];
				}
				$hint = $span.'<div style="display: none;padding-left: 20px">'.implode(', ', $lpuBuilding_arr[$key]).'</div>';
			}else{
				$hint = $spanZ;
			}
			$lpu_arr[$key][] = $item['Lpu_Nick'].$hint;
		}

		foreach($response as &$item) {
			$key = $item['AccessRightsName_id'];
			$item['AccessRightsLpu_Nicks'] = isset($lpu_arr[$key]) ? implode("<br/>", $lpu_arr[$key]) : '';
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
					$this->addAccessRightsLpuBuildingArr($AccessRights);
					break;
				case 0:
				case 2:
					$resp = $this->saveAccessRightsLpu($AccessRights);
					if(!empty($resp[0]['AccessRightsLpu_id'])){
						$AccessRights['AccessRightsLpu_id'] = $resp[0]['AccessRightsLpu_id']; 
						$this->addAccessRightsLpuBuildingArr($AccessRights);
					}
					break;
				case 3:
					$resp = $this->deleteAccessRightsLpu($AccessRights);
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
	 * получаем все записи подразделений найденые по AccessRightsOrg_id в LpuBuildingAccessRightsLink
	 */
	function getAccessRightsLpuBuildingOrg($data){
		if(empty($data['AccessRightsOrg_id'])) return false;
		$params = array('AccessRightsOrg_id' => $data['AccessRightsOrg_id']);

		$query  = "
			select
				LBAR.LpuBuildingAccessRightsLink_id as \"LpuBuildingAccessRightsLink_id\",
				LBAR.AccessRightsOrg_id as \"AccessRightsOrg_id\",
				LBAR.LpuBuilding_id as \"LpuBuilding_id\",
				LB.LpuBuilding_Name as \"LpuBuilding_Name\"
			from 
				v_LpuBuildingAccessRightsLink LBAR
				left join v_LpuBuilding LB on LB.LpuBuilding_id = LBAR.LpuBuilding_id
			where LBAR.AccessRightsOrg_id = :AccessRightsOrg_id
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}
	
	/**
	 * удаляем все записи подразделений найденые по AccessRightsOrg_id из LpuBuildingAccessRightsLink
	 */
	function deleteAccessRightsLpuBuildingOrg($data){
		if(empty($data['AccessRightsOrg_id'])) return false;
		$result = $this->getAccessRightsLpuBuildingOrg($data);
		if(is_array($result)){
			if(count($result)>0){
				$LBARightsLink = $result;
				foreach ($LBARightsLink as $key => $value) {
					if(!empty($value['LpuBuildingAccessRightsLink_id'])){
						$params = array('LpuBuildingAccessRightsLink_id' => $value['LpuBuildingAccessRightsLink_id']);
						$resp = $this->deleteLpuBuildingAccessRightsLink($params);
						if (!empty($resp[0]['Error_Msg']) || !empty($resp[0]['Alert_Msg'])) {						
							return $resp;
						}
					}
				}
			}
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * сохраняем записи подразделений в LpuBuildingAccessRightsLink
	 */
	function addAccessRightsLpuBuildingArr($data){
		if(empty($data['AccessRightsLpu_id'])) return false;
		
		$params = array(
			'AccessRightsOrg_id' => $data['AccessRightsLpu_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$AccessRightsLpuBuilding = json_decode($data['AccessRightsLpuBuildingData'], true);
		// удаляем все прежние записи и добавляем новые
		$resDel = $this->deleteAccessRightsLpuBuildingOrg($params);
		
		if (!empty($resDel[0]['Error_Msg']) || !empty($resDel[0]['Alert_Msg'])) return $resDel;
		$LBARightsLinkAdd=array();
		
		if(!empty($AccessRightsLpuBuilding) && is_array($AccessRightsLpuBuilding) && count($AccessRightsLpuBuilding)>0){
			foreach ($AccessRightsLpuBuilding as $key => $value) {
				$params = array(
					'AccessRightsOrg_id' => $data['AccessRightsLpu_id'],
					'pmUser_id' => $data['pmUser_id'],
					'LpuBuilding_id' => $value
				);
				$result = $this->saveAccessRightsLpuBuilding($params);
				if($result[0]['Error_Msg']) return $result;
				$LBARightsLinkAdd[] = $result[0]['LpuBuildingAccessRightsLink_id'];
			}
		}
		return $LBARightsLinkAdd;
	}
	
	/**
	 * сохраняем запись подразделения в LpuBuildingAccessRightsLink
	 */
	function saveAccessRightsLpuBuilding($data){
		if(empty($data['AccessRightsOrg_id']) || empty($data['LpuBuilding_id'])) return false;
		
		$params = array(
			'AccessRightsOrg_id' => $data['AccessRightsOrg_id'],
			'pmUser_id' => $data['pmUser_id'],
			'LpuBuilding_id' => $data['LpuBuilding_id']
		);
		
		if (!empty($data['LpuBuildingAccessRightsLink_id']) && $data['LpuBuildingAccessRightsLink_id'] > 0) {
			$procedure = 'p_LpuBuildingAccessRightsLink_upd';
			$params['LpuBuildingAccessRightsLink_id'] = $data['LpuBuildingAccessRightsLink_id'];
		} else {
			$params['LpuBuildingAccessRightsLink_id'] = null;
			$procedure = 'p_LpuBuildingAccessRightsLink_ins';
		}
		$query = "
		    select
		        LpuBuildingAccessRightsLink_id as \"LpuBuildingAccessRightsLink_id\",
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
			from {$procedure}(
				LpuBuildingAccessRightsLink_id = :LpuBuildingAccessRightsLink_id,
				AccessRightsOrg_id := :AccessRightsOrg_id,
				LpuBuilding_id := :LpuBuilding_id,
				pmUser_id := :pmUser_id
				)
				";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка при сохранении в группе'));
		}
		$response = $result->result('array');

		return $response;
	}

	/**
	 * Проверка на пересечениями с другими группами МО
	 */
	function checkAccessRightsIntersection($data) {
		$params = array('Lpu_id' => $data['Lpu_id']);

		$query = "
			select ARN.AccessRightsName_Name as \"AccessRightsName_Name\"
			from v_AccessRightsOrg ARO
				inner join v_Lpu Lpu on Lpu.Org_id = ARO.Org_id
				left join v_AccessRightsName ARN on ARN.AccessRightsName_id = ARO.AccessRightsName_id
			where Lpu.Lpu_id = :Lpu_id
			limit 1
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при поиске пересечений МО');
		}
		$resp = $result->result('array');
		if (count($resp) == 0){
			$resp = array(array());
		}
		return $resp[0];
	}

	/**
	 * Сохранение МО в группе для ограничения доступа (работает с объектом AccessRightsOrg)
	 */
	function saveAccessRightsLpu($data) {
		if (empty($data['allowIntersection']) || !$data['allowIntersection']) {
			$check = $this->checkAccessRightsIntersection($data);
			if (!empty($check['Error_Msg'])) {
				return array($check);
			}
			if (!empty($check['AccessRightsName_Name'])) {
				return array(array(
					'Alert_Msg' => "Указанное МО уже имеется в группе '{$check['AccessRightsName_Name']}'.",
					'Alert_Code' => 1
				));
			}
		}

		$params = array(
			'AccessRightsOrg_id' => $data['AccessRightsLpu_id'],
			'AccessRightsName_id' => $data['AccessRightsName_id'],
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		if (!empty($params['AccessRightsOrg_id']) && $params['AccessRightsOrg_id'] > 0) {
			$procedure = 'p_AccessRightsOrg_upd';
		} else {
			$params['AccessRightsOrg_id'] = null;
			$procedure = 'p_AccessRightsOrg_ins';
		}

		$query = "
		    with myvar as (select Org_id from v_Lpu where Lpu_id = :Lpu_id limit 1)
		    select
		        AccessRightsOrg_id as \"AccessRightsLpu_id\",
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
			from {$procedure} (
				AccessRightsOrg_id := :AccessRightsOrg_id,
				AccessRightsName_id := :AccessRightsName_id,
				Org_id := (select Org_id from myvar),
				pmUser_id := :pmUser_id
			)
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка при сохранении диагнозов в группе'));
		}
		$response = $result->result('array');

		return $response;
	}

	/**
	 * Удаление МО из группы для ограничения доступа
	 */
	function deleteAccessRightsLpu($data) {
		$params = array('AccessRightsOrg_id' => $data['AccessRightsLpu_id']);
		//удаляем подразделения
		$resp = $this->deleteAccessRightsLpuBuildingOrg($params);
		if (!$resp) {
			$response = array('Error_Msg' => 'Ошибка при удалении ограничения доступа к диагнозам');
			return array($response);
		}
		
		$query = "
		    select
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
			
			from p_AccessRightsOrg_del(
				AccessRightsOrg_id := :AccessRightsOrg_id
			)
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
			select ARL.AccessRightsLimit_id as \"AccessRightsLimit_id\"
			from v_AccessRightsLimit ARL
			where ARL.AccessRightsName_id = :AccessRightsName_id
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Ошибка при удалении доступа к группе МО'));
		}
		$lpu_arr = $result->result('array');
		foreach($lpu_arr as $item) {
			$resp = $this->deleteAccessRightsLimit($item);
			if (!empty($resp[0]['Error_Msg'])) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$query = "
			select ARO.AccessRightsOrg_id as \"AccessRightsLpu_id\"
			from v_AccessRightsOrg ARO
			where ARO.AccessRightsName_id = :AccessRightsName_id
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Ошибка при удалении МО в группе'));
		}
		$lpu_arr = $result->result('array');
		foreach($lpu_arr as $item) {
			$resp = $this->deleteAccessRightsLpu($item);
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
				ARN.AccessRightsName_id as \"AccessRightsName_id\",
				ARN.AccessRightsName_Name as \"AccessRightsName_Name\"
			from v_AccessRightsName ARN
			where ARN.AccessRightsName_id = :AccessRightsName_id
			limit 1
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка получения наименования группы МО'));
		}
		$response = $result->result('array');

		$query = "
			select
				ARO.AccessRightsOrg_id as \"AccessRightsLpu_id\",
				Lpu.Lpu_id as \"Lpu_id\",
				1 as \"RecordStatus_Code\"
			from v_AccessRightsOrg ARO
				inner join v_Lpu Lpu on Lpu.Org_id = ARO.Org_id
			where ARO.AccessRightsName_id = :AccessRightsName_id
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка получения МО группы'));
		}
		$lpu_arr = $result->result('array');
		
		//получим подразделения
		$query = "
			select 
				LBR.LpuBuilding_id as \"LpuBuilding_id\",
				LBR.AccessRightsOrg_id as \"AccessRightsOrg_id\"
			from  
				v_LpuBuildingAccessRightsLink LBR
				left join v_AccessRightsOrg ARO on LBR.AccessRightsOrg_id=ARO.AccessRightsOrg_id
			where ARO.AccessRightsName_id =  :AccessRightsName_id
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка получения подразделений группы'));
		}
		$lpuBuilding_arr = $result->result('array');
		foreach ($lpuBuilding_arr as $value) {
			$accessOrg = $value['AccessRightsOrg_id'];
			foreach ($lpu_arr as $key => $lpu) {
				if($lpu['AccessRightsLpu_id'] == $accessOrg){
					$lpu_arr[$key]['AccessRightsLpuBuildingData'][] = $value['LpuBuilding_id'];
					break;
				}
			}
		}

		$response[0]['AccessRightsData'] = json_encode($lpu_arr);

		return $response;
	}
}