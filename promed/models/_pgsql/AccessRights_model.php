<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * AccessRights_model - модель для работы c правами доступа
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

class AccessRights_model extends SwPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение списка групп с ограничением доступа
	 */
	function loadAccessRightsGrid($data) {return false;}

	/**
	 * Сохранение группы для ограничения доступа
	 */
	function saveAccessRights($data) {
		return false;
	}

	/**
	 * Удаление группы для ограничения доступа
	 */
	function deleteAccessRights($data) {
		return false;
	}

	/**
	 * Получение данных для редактирования группы ограничения доступа
	 */
	function loadAccessRightsForm($data) {
		return false;
	}

	/**
	 * Сохранение наименования группы для ограничения доступа
	 */
	function saveAccessRightsName($data) {
		$params = array(
			'AccessRightsName_id' => $data['AccessRightsName_id'],
			'AccessRightsName_Name' => $data['AccessRightsName_Name'],
			'AccessRightsName_Code' => $data['AccessRightsName_Code'],
			'AccessRightsType_id' => $data['AccessRightsType_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$procedure = 'p_AccessRightsName_ins';
		if (!empty($params['AccessRightsName_id'])) {
			$procedure = 'p_AccessRightsName_upd';
		}

		$query = "
		    SELECT 
		        AccessRightsName_id as \"AccessRightsName_id\",
		        error_code as \"Error_Code\",
                error_message as \"Error_Msg\"
            FROM {$procedure} (
                AccessRightsName_id => :AccessRightsName_id,
				AccessRightsName_Name => :AccessRightsName_Name,
				AccessRightsName_Code => :AccessRightsName_Code,
				AccessRightsType_id => :AccessRightsType_id,
				pmUser_id => :pmUser_id
            )		        
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка сохранения наименования группы для ограничения прав доступа'));
		}
		return $result->result('array');
	}

	/**
	 * Удаление наименования группы для ограничения доступа
	 */
	function deleteAccessRightsName($data) {
		$params = array('AccessRightsName_id' => $data['AccessRightsName_id']);

		$query = "
		    SELECT
		        error_code as \"Error_Code\",
		        error_message as \"Error_Msg\"
		    FROM p_AccessRightsName_del (
		        AccessRightsName_id => :AccessRightsName_id
		    )
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка при удалении наименования группы для ограничения прав доступа'));
		}
		return $result->result('array');
	}

	/**
	 * Полученение списка объектов с разрешенным доступом к группе
	 */
	function loadAccessRightsLimitGrid($data) {
		$filter = '';
		$params = array('AccessRightsName_id' => $data['AccessRightsName_id']);

		if (!empty($data['AccessRightsLimitType_SysNick'])) {
			$limit_type_fields = array(
				'post' => 'Post_id',
				'lpu' => 'Lpu_id',
				'usergroups' => 'AccessRightsType_UserGroups',
				'user' => 'AccessRightsType_User',
				'lpuBuilding' => 'LpuBuildingAccessRightsLink_id'
			);
			$lpuBuilding = ($data['AccessRightsLimitType_SysNick'] == 'lpuBuilding') ? true : false;
			foreach($limit_type_fields as $type => $field) {
				if($type=='lpuBuilding'){
					$a = 'LBAR';
				}else{
					if($type=='lpu' && $lpuBuilding) continue;
					$a = ($type=='lpu') ? 'L' : 'ARL';
				}
				$not = ($type == $data['AccessRightsLimitType_SysNick']) ? 'not' : '';
				$filter .= " and {$a}.{$field} is {$not} null";
			}
		}

		$query = "
			select
				ROW_NUMBER() OVER(ORDER BY ARL.AccessRightsLimit_id ASC) as id,
				LBAR.LpuBuildingAccessRightsLink_id as \"LpuBuildingAccessRightsLink_id\",
				ARL.AccessRightsLimit_id as \"AccessRightsLimit_id\",
				case
					when ARL.Post_id is not null then 'Должность врача'
					when LBAR.AccessRightsLimit_id is not null then 'Подразделение'
					when L.Lpu_id is not null then 'МО'
					when ARL.AccessRightsType_User is not null then 'Пользователь'
					when ARL.AccessRightsType_UserGroups is not null then 'Группа пользователей'
				end as \"AccessRightsLimitType_Name\",
				case
					when ARL.Post_id is not null then PM.PostMed_Name
					when LBAR.AccessRightsLimit_id is not null then LB.LpuBuilding_Name
					when L.Lpu_id is not null then L.Lpu_Nick
					when ARL.AccessRightsType_User is not null then rtrim(pmUser.pmUser_Name) || ' (' || rtrim(pmUser.pmUser_Login) || ')'
					when ARL.AccessRightsType_UserGroups is not null then ARL.AccessRightsType_UserGroups
				end as \"AccessRightsLimit_Value\"
			from v_AccessRightsLimit ARL 

				left join v_PostMed PM  on PM.PostMed_id = ARL.Post_id

				left join v_Lpu L  on L.Org_id = ARL.Org_id

				left join v_pmUserCache pmUser  on pmUser.pmUser_id = ARL.AccessRightsType_User

				
				left join v_LpuBuildingAccessRightsLink LBAR  on ARL.AccessRightsLimit_id = LBAR.AccessRightsLimit_id

				left join v_LpuBuilding LB  on LB.LpuBuilding_id = LBAR.LpuBuilding_id

			where
				ARL.AccessRightsName_id = :AccessRightsName_id
				{$filter}
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return array('data' => $result->result('array'));
	}

	/**
	 * Получение списка пользователей для проставления доступа к группе
	 */
	function loadAccessRightsLimitUsersGrid($data) {
		$params = array(
			'AccessRightsName_id' => $data['AccessRightsName_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		$query = "
			select
				pmUser.pmUser_id as \"pmUser_id\",
				ARL.AccessRightsLimit_id as \"AccessRightsLimit_id\",
				case when ARL.AccessRightsLimit_id is null then 3 else 1 end as \"RecordStatus_Code\",
				pmUser.pmUser_surName as \"Person_SurName\",
				pmUser.pmUser_firName as \"Person_FirName\",
				pmUser.pmUser_secName as \"Person_SecName\",
				pmUser.pmUser_Login as \"pmUser_Login\"
			from v_pmUserCache pmUser 

			left join v_AccessRightsLimit ARL 

				on ARL.AccessRightsType_User = pmUser.PMUser_id and ARL.AccessRightsName_id = :AccessRightsName_id
			where pmUser.Lpu_id = :Lpu_id and COALESCE(pmUser.pmUser_deleted, 1) = 1

		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return array('data' => $result->result('array'));
	}

	/**
	 * Сохранение изменений доступа пользователей к группе
	 */
	function saveAccessRightsLimitUsers($data) {
		$LimitUsersData = json_decode($data['AccessRightsLimitUsersData'], true);

		foreach($LimitUsersData as $user) {
			$user['AccessRightsName_id'] = $data['AccessRightsName_id'];
			$user['pmUser_id'] = $data['pmUser_id'];
			$resp = array();

			switch($user['RecordStatus_Code']) {
				case 0:
					$resp = $this->saveAccessRightsLimit($user);
					break;

				case 3:
					$resp = $this->deleteAccessRightsLimit($user);
					break;
			}
			if (!empty($resp['Error_Msg'])) {
				return $resp;
			}
		}
		return array('success' => true);
	}

	/**
	 * Сохранение доступа объекта к группе
	 */
	function saveAccessRightsLimit($data) {
		$params = array(
			'AccessRightsLimit_id' => !empty($data['AccessRightsLimit_id']) ? $data['AccessRightsLimit_id'] : null,
			'AccessRightsName_id' => $data['AccessRightsName_id'],
			'Post_id' => !empty($data['LimitPost_id']) ? $data['LimitPost_id'] : null,
			'Lpu_id' => !empty($data['LimitLpu_id']) ? $data['LimitLpu_id'] : null,
			'AccessRightsType_UserGroups' => !empty($data['AccessRightsType_UserGroups']) ? $data['AccessRightsType_UserGroups'] : null,
			'AccessRightsType_User' => !empty($data['AccessRightsType_User']) ? $data['AccessRightsType_User'] : null,
			'pmUser_id' => $data['pmUser_id']
		);

		$procedure = 'p_AccessRightsLimit_ins';
		if (!empty($params['AccessRightsLimit_id'])) {
			$procedure = 'p_AccessRightsLimit_upd';
		}

		$query = "select Org_id as \"Org_id\" from v_Lpu  where Lpu_id = :Lpu_id LIMIT 1";
		$result = $this->db->query($query, $params);
        if (!is_object($result)) {
            return array('Error_Msg' => 'Ошибка при сохранении доступа');
        }
        $orgRes = $result->result('array');

        $params['Org_id'] = (is_array($orgRes) && key_exists(0, $orgRes)) ? $orgRes[0]['Org_id'] : null;

		$query = "
		    SELECT
		        AccessRightsLimit_id as \"AccessRightsLimit_id\", 
		        error_code as \"Error_Code\", 
		        error_message as \"Error_Msg\"
		    FROM {$procedure} (
		        AccessRightsLimit_id => :AccessRightsLimit_id,
				AccessRightsName_id => :AccessRightsName_id,
				Post_id => :Post_id,
				Org_id => :Org_id,
				AccessRightsType_UserGroups => :AccessRightsType_UserGroups,
				AccessRightsType_User => :AccessRightsType_User,
				pmUser_id => :pmUser_id
		    )
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при сохранении доступа');
		}
		
		$resp = $result->result('array');
		
		if(!empty($data['LimitLpu_id']) && !empty($data['LpuBuildings'])){
			$LpuBuildingsData = json_decode($data['LpuBuildings'], true);
			$params = array(
				'AccessRightsLimit_id' => $resp[0]['AccessRightsLimit_id'],
				'LpuBuildings' => $LpuBuildingsData,
				'pmUser_id' => $data['pmUser_id']
			);
			$resLB = $this->saveLpuBuildings($params);
		}
		return $result->result('array');
	}
	
	/**
	 * сохраняем массив подразделений в LpuBuildingAccessRightsLink
	 */
	function saveLpuBuildings($data){
		if(empty($data['AccessRightsLimit_id']) || empty($data['LpuBuildings'])) return false;
		$LpuBuildingsAdd = array();
		
		foreach ($data['LpuBuildings'] as $key => $value) {
			$params = array(
				'AccessRightsLimit_id' => $data['AccessRightsLimit_id'],
				'pmUser_id' => $data['pmUser_id'],
				'LpuBuilding_id' => $value
			);
			$result = $this->saveLpuBuildingAccessRightsLinkLimit($params);
			if($result[0]['Error_Msg']) return $result;
			$LpuBuildingsAdd[] = $result[0]['LpuBuildingAccessRightsLink_id'];
		}
		
		return $LpuBuildingsAdd;
	}
	
	/**
	 * сохраняем запись подразделения в LpuBuildingAccessRightsLink
	 */
	function saveLpuBuildingAccessRightsLinkLimit($data){
		if(empty($data['AccessRightsLimit_id']) || empty($data['LpuBuilding_id']) || empty($data['pmUser_id'])) return false;
		
		$params = array(
			'AccessRightsLimit_id' => $data['AccessRightsLimit_id'],
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
		    SELECT
		        LpuBuildingAccessRightsLink_id as \"LpuBuildingAccessRightsLink_id\",
		        error_code as \"Error_Code\",
		        error_message as \"Error_Msg\"
		    FROM {$procedure} (
		        LpuBuildingAccessRightsLink_id => :LpuBuildingAccessRightsLink_id,
				AccessRightsLimit_id => :AccessRightsLimit_id,
				LpuBuilding_id => :LpuBuilding_id,
				pmUser_id => :pmUser_id
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
	 * Удаление доступа подразделения либо объекта к группе
	 */
	function deleteLpuBuildingOrObjectAccessRightsLimit($data){
		if(empty($data['AccessRightsLimit_id'])) return false;
		if(!empty($data['LpuBuildingAccessRightsLink_id'])){
			//удаляем подразделение (не я придумал такую карусель)
			$res = $this->deleteLpuBuildingAccessRightsLink($data);
			if(isset($res['Error_Msg'])) return $res;
			
			//проверим есть ли еще записи и если это была последняя, то удалим и МО из AccessRightsLimit
			$lpuB = $this->getAccessRightsLpuBuildingLimit($data);
			if(count($lpuB) == 0){
				$res = $this->deleteAccessRightsLimit($data);
			}
		}else{
			$res = $this->deleteAccessRightsLimit($data);
		}
		return $res;
	}
	
	/**
	 * удаляем запись из LpuBuildingAccessRightsLink
	 */
	function deleteLpuBuildingAccessRightsLink($data){
		if(empty($data['LpuBuildingAccessRightsLink_id'])) return false;
		$params = array('LpuBuildingAccessRightsLink_id' => $data['LpuBuildingAccessRightsLink_id']);
		$query = "
		    SELECT
		        error_code as \"Error_Code\",
		        error_message as \"Error_Msg\"
		    FROM p_LpuBuildingAccessRightsLink_del (
		        LpuBuildingAccessRightsLink_id => :LpuBuildingAccessRightsLink_id
		    )    
		";
		/*
		$response = $this->getFirstRowFromQuery($query, $params);
		if (!$response) {
			$response = array('Error_Msg' => 'Ошибка при удалении записи');
		}
		return array($response);
		 * *
		 */
		$response = $this->db->query($query, $params);
		if (!is_object($response)) {
			return array('Error_Msg' => 'Ошибка при удалении доступа');
		}
		return $response->result('array');
	}

	/**
	 * Удаление доступа объекта к группе
	 */
	function deleteAccessRightsLimit($data) {
		$params = array('AccessRightsLimit_id' => $data['AccessRightsLimit_id']);

		$query = "
		    SELECT
		        error_code as \"Error_Code\",
		        error_message as \"Error_Msg\"
		    FROM p_AccessRightsLimit_del (
		        AccessRightsLimit_id => :AccessRightsLimit_id
		    )
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при удалении доступа');
		}
		return $result->result('array');
	}
	
	/**
	 * получаем все записи подразделений найденые по AccessRightsLimit_id в LpuBuildingAccessRightsLink
	 */
	function getAccessRightsLpuBuildingLimit($data){
		if(empty($data['AccessRightsLimit_id'])) return false;
		$params = array('AccessRightsLimit_id' => $data['AccessRightsLimit_id']);

		$query  = "
			select
				LBAR.LpuBuildingAccessRightsLink_id as \"LpuBuildingAccessRightsLink_id\",
				LBAR.AccessRightsLimit_id as \"AccessRightsLimit_id\",
				LBAR.LpuBuilding_id as \"LpuBuilding_id\"
			from v_LpuBuildingAccessRightsLink LBAR 

			where LBAR.AccessRightsLimit_id = :AccessRightsLimit_id
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}

	/**
	 * Формирование ограничений доступа к объектам для пользователя
	 */
	function getAccessRightsForUser($data) {
		$response = array();
		$params = array(
			'pmUser_id' => $data['pmUser_id'],
			'MedPersonal_id' => $data['MedPersonal_id']
		);

		$lpu_arr = isset($data['Lpus']) && is_array($data['Lpus']) ? $data['Lpus'] : array();
		$lpu_building_arr = array();
		$post_arr = array();
		$group_arr = $data['UserGroups'];

		$acnt = count($lpu_arr);
		for($i = 0; $i < $acnt; $i++) {
			if (empty($lpu_arr[$i])) {
				unset($lpu_arr[$i]);
			}
		}

		if (!empty($params['MedPersonal_id']) && $params['MedPersonal_id'] > 0) {
			$filter = '';
			if (count($lpu_arr) > 0) {
				$filter .= " and MSF.Lpu_id in (".implode(',', $lpu_arr).")";
			}

			$query = "
				select 
					MSF.Post_id as \"Post_id\",
					MSF.Lpu_id as \"Lpu_id\",
					MSF.LpuBuilding_id as \"LpuBuilding_id\"
				from v_MedStaffFact MSF 

				where MSF.MedPersonal_id = :MedPersonal_id
				and MSF.WorkData_begDate <= dbo.tzGetDate()
				and (MSF.WorkData_endDate is null or MSF.WorkData_endDate > dbo.tzGetDate())
				{$filter}
			";
			$result = $this->db->query($query, $params);
			if (!is_object($result)) {
				return $response;
			}
			$resp = $result->result('array');

			foreach($resp as $item) {
				if (!in_array($item['Lpu_id'], $lpu_arr)) {
					$lpu_arr[] = $item['Lpu_id'];
				}
				if ($item['LpuBuilding_id'] && !in_array($item['LpuBuilding_id'], $lpu_building_arr)) {
					$lpu_building_arr[] = $item['LpuBuilding_id'];
				}
				$post_arr[] = $item['Post_id'];
			}
		}
		
		$lpu_arr = array_filter($lpu_arr);

		$limit_where = array('ARL.AccessRightsType_User = :pmUser_id');
		if (count($lpu_arr) > 0) {
			$limit_where[] = "(
				ARL.Org_id in (select Org_id from v_Lpu  where Lpu_id in (".implode(',', $lpu_arr)."))

				and LBARL.LpuBuildingAccessRightsLink_id is null
			)";
		}
		if (count($lpu_building_arr) > 0) {
			$limit_where[] = "(
				ARL.Org_id is not null
				and LBARL.LpuBuilding_id in (".implode(',', $lpu_building_arr).")
			)";
		}
		if (count($post_arr) > 0) {
			$limit_where[] = "ARL.Post_id in (".implode(',', $post_arr).")";
		}
		if (count($group_arr) > 0) {
			$limit_where[] = "ARL.AccessRightsType_UserGroups in ('".implode("','", $group_arr)."')";
		}

		$query = "
			select
				ARN.AccessRightsName_id as \"AccessRightsName_id\",
				ARN.AccessRightsName_Name as \"AccessRightsName_Name\",
				case when
					exists(
						select ARL.AccessRightsLimit_id
						from v_AccessRightsLimit ARL 

						left join v_LpuBuildingAccessRightsLink LBARL  on LBARL.AccessRightsLimit_id = ARL.AccessRightsLimit_id

						where
							ARL.AccessRightsName_id = ARN.AccessRightsName_id
							and (
								".implode(' or ', $limit_where)."
							)
					) then 1 else 0
				end as \"hasAccess\"
			from v_AccessRightsName ARN 

				inner join v_AccessRightsType ART  on ART.AccessRightsType_id = ARN.AccessRightsType_id

		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return $response;
		}
		$resp = $result->result('array');
		$access_rights = array();
		foreach($resp as $item) {
			$key = $item['AccessRightsName_id'];
			$access_rights[$key] = $item;
		}

		$query = "
			select
				ARD.AccessRightsName_id as \"AccessRightsName_id\",
				ARD.Diag_fid as \"Diag_fid\",
				ARD.Diag_tid as \"Diag_tid\",
				fD.Diag_Code as \"Diag_fCode\",
				tD.Diag_Code as \"Diag_tCode\"
			from v_AccessRightsDiag ARD
				inner join lateral (
					select
						Diag_Code
					from v_Diag
					where v_Diag.Diag_id = ARD.Diag_fid
					limit 1
				) fD on true
				left join lateral (
					select
						Diag_Code
					from v_Diag
					where v_Diag.Diag_id = ARD.Diag_tid
					limit 1
				) td on true

		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return $response;
		}
		$response['diag'] = $result->result('array');

		foreach($response['diag'] as &$diag) {
			$key = $diag['AccessRightsName_id'];
			$diag = array_merge($diag, $access_rights[$key]);
		}

		$query = "
			select
				ART.AccessRightsName_id as \"AccessRightsName_id\",
				ART.UslugaComplex_id as \"UslugaComplex_id\"
			from v_AccessRightsTest ART 

		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return $response;
		}
		$response['test'] = $result->result('array');

		foreach($response['test'] as &$test) {
			$key = $test['AccessRightsName_id'];
			$test = array_merge($test, $access_rights[$key]);
		}

		$query = "
			select
				ART.AccessRightsName_id as \"AccessRightsName_id\",
				ART.PrivilegeType_id as \"PrivilegeType_id\"
			from v_AccessRightsPrivilegeType ART 

		";


		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return $response;
		}
		$response['privilege'] = $result->result('array');

		foreach($response['privilege'] as &$privilege) {
			$key = $privilege['AccessRightsName_id'];
			$privilege = array_merge($privilege, $access_rights[$key]);
		}

		$query = "
			select
				ARO.AccessRightsName_id as \"AccessRightsName_id\",
				Lpu.Lpu_id as \"Lpu_id\"
			from v_AccessRightsOrg ARO 

				inner join v_Lpu Lpu  on Lpu.Org_id = ARO.Org_id

				left join v_LpuBuildingAccessRightsLink LBARL  on LBARL.AccessRightsOrg_id = ARO.AccessRightsOrg_id

			where 
				LBARL.LpuBuildingAccessRightsLink_id is null
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return $response;
		}
		$response['lpu'] = $result->result('array');

		foreach($response['lpu'] as &$lpu) {
			$key = $lpu['AccessRightsName_id'];
			$lpu = array_merge($lpu, $access_rights[$key]);
		}

		$query = "
			select
				ARO.AccessRightsName_id as \"AccessRightsName_id\",
				LBARL.LpuBuilding_id as \"LpuBuilding_id\"
			from v_AccessRightsOrg ARO 

				inner join v_Lpu Lpu  on Lpu.Org_id = ARO.Org_id

				left join v_LpuBuildingAccessRightsLink LBARL  on LBARL.AccessRightsOrg_id = ARO.AccessRightsOrg_id

			where 
				LBARL.LpuBuildingAccessRightsLink_id is not null
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return $response;
		}
		$response['lpu_building'] = $result->result('array');

		foreach($response['lpu_building'] as &$lpu_building) {
			$key = $lpu_building['AccessRightsName_id'];
			$lpu_building = array_merge($lpu_building, $access_rights[$key]);
		}

		//var_dump($response);
		return $response;
	}


	/**
	 * Сохранение СМО для которого разрешен доступ к справочнику МЭСов из АРМа СМО
	 */
	function saveAccessRightsArmSmo($data) {
		$params = array(
			'AccessRightsOrg_id' => !empty($data['AccessRightsOrg_id'])?$data['AccessRightsOrg_id']:0,
			'AccessRightsName_Code' => $data['AccessRightsName_Code'],
			'Org_id' => $data['Org_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$procedure = 'p_AccessRightsOrg_ins';
		if (!empty($params['AccessRightsOrg_id'])) {
			$procedure = 'p_AccessRightsOrg_upd';
		}

		//Проверка что такой СМО уже нет в разрешенных
		$query = "
			select
				ARO.Org_id as \"Org_id\"
			from
				v_AccessRightsOrg ARO 

				inner join v_AccessRightsName ARN  on ARN.AccessRightsName_id = ARO.AccessRightsName_id

			where
				ARN.AccessRightsName_Code = :AccessRightsName_Code
				and ARO.AccessRightsOrg_id <> :AccessRightsOrg_id
				and ARO.Org_id = :Org_id
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка сохранения СМО для предоставления прав доступа'));
		} else {
			$response = $result->result('array');

			if (is_array($response) && count($response) > 0 && !empty($response[0]['Org_id'])) {
				return array(array('Error_Msg' => 'Данной СМО уже предоставлен доступ к справочнику МЭСов'));
			}
		}

		$query = "
			select AccessRightsName_id as \"AccessRightsName_id\"
			from v_AccessRightsName 

			where AccessRightsName_Code = :AccessRightsName_Code
			limit 1
		";
		$resp = $this->queryResult($query, $params);
		if (!$this->isSuccessful($resp) || empty($resp[0]['AccessRightsName_id'])) {
			return $this->createError('', 'Ошибка при определении наименования группы доступа');
		}

		$params['AccessRightsName_id'] = $resp[0]['AccessRightsName_id'];
		$query = "
		    SELECT 
		        AccessRightsOrg_id as \"AccessRightsOrg_id\", 
		        error_code as \"Error_Code\", 
		        error_message as \"Error_Msg\"
		    FROM {$procedure} (
		        AccessRightsOrg_id => :AccessRightsOrg_id,
				AccessRightsName_id => :AccessRightsName_id,
				Org_id => :Org_id,
				pmUser_id => :pmUser_id
			)
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка сохранения наименования группы для ограничения прав доступа'));
		}
		return $result->result('array');
	}


	/**
	 * Загрузка списка СМО с разрешеннымом доступ к справочнику МЭСов из АРМа СМО
	 */
	function loadAccessRightsArmSmoGrid($data) {
		$params = array();
		$codesStr = explode(',',$data['AccessRightsName_Code']);
		$codes = array();
		foreach($codesStr as $codeStr){
			if(!empty($codeStr) && is_numeric($codeStr))
				$codes[] = $codeStr;
		}
		$filterCodes = '';
		if(count($codes)>0)
			$filterCodes = "AND ARN.AccessRightsName_Code IN('".implode("','",$codes)."')";

		$query = "
			select
				ARO.AccessRightsOrg_id as \"AccessRightsOrg_id\",
				SMO.OrgSMO_Nick as \"OrgSMO_Nick\",
				ARO.Org_id as \"Org_id\",
				ARN.AccessRightsName_Code as \"AccessRightsName_Code\"
			from
				v_AccessRightsOrg ARO 

				inner join v_AccessRightsName ARN  on ARN.AccessRightsName_id = ARO.AccessRightsName_id

				left join v_OrgSMO SMO  on SMO.Org_id = ARO.Org_id

			where
				1=1
				{$filterCodes}
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return array('data' => $result->result('array'));
	}

	/**
	 * Удаление доступа СО к справочнику МЭСов
	 */
	function deleteAccessRightsArmSmo($data) {
		$params = array('AccessRightsOrg_id' => $data['AccessRightsOrg_id']);

		$query = "
		    SELECT 
		        error_code as \"Error_Code\", 
		        error_message as \"Error_Msg\"
		    FROM p_AccessRightsOrg_del (
		        AccessRightsOrg_id => :AccessRightsOrg_id
		    )
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при удалении доступа СМО к справочнику МЭСов');
		}
		return $result->result('array');
	}

	/**
	 * Проверка доступа СМО к справочнику МЭСов
	 */
	function checkArmSmoAccess($data) {

		$query = "
			select
				case
					when ARN.AccessRightsName_Code = 1 then 'mes'
					when ARN.AccessRightsName_Code = 101 then 'emk'
					when ARN.AccessRightsName_Code = 111 then 'query'
				end as access
			from
				v_AccessRightsOrg ARO 

				inner join v_AccessRightsName ARN  on ARN.AccessRightsName_id = ARO.AccessRightsName_id

			where
				Org_id = :Org_id
				and AccessRightsName_Code in (1, 101, 111)
		";

		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		} else {
			$response = $result->result('array');

			return $response;
		}
	}

	/**
	 * Возвращает список действующих СМО
	 */
	function loadSmoActualList($data) {
		$data['Current_Region'] = $data['session']['region']['number'];
		$query = "
			select
				OrgSMO_id as \"OrgSMO_id\",
				Org_id as \"Org_id\",
				OrgSMO_RegNomC as \"OrgSMO_RegNomC\",
				OrgSMO_RegNomN as \"OrgSMO_RegNomN\",
				OrgSMO_Nick as \"OrgSMO_Nick\",
				OrgSMO_isDMS as \"OrgSMO_isDMS\",
				KLRgn_id as \"KLRgn_id\",
				OrgSMO_endDate as \"OrgSMO_endDate\"
			from
				v_OrgSMO 

			where
				KLRgn_id = :Current_Region and
			 	(OrgSmo_endDate is null or OrgSmo_endDate >= dbo.tzGetDate())
		";

		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Проверка наличия доступа организации OrgSMO с признаком ТФОМС  к справочнику МЭСов
	 */
	function checkAccessTfomsToFunctionalEMK($data) {

		$query = "
			select
				case
					when ARN.AccessRightsName_Code = 101 then 'emk'
				end as access
			from
				v_AccessRightsOrg ARO 

				inner join v_AccessRightsName ARN  on ARN.AccessRightsName_id = ARO.AccessRightsName_id

				left join OrgSMO OS  on OS.Org_id=ARO.Org_id

			where
				ARO.Org_id = :Org_id
				--and OS.OrgSMO_IsTFOMS = 2
				and ARN.AccessRightsName_Code in (101)
			limit 1
		";

		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		} else {
			$response = $result->result('array');
			return $response;
		}
	}

	/**
	 * Доступ групп пользователей к Т9 
	 */
	function loadAccessT9Grid($data) {
		$query = "
			select
				t9.pmUserCacheGroup_id as \"Group_id\",
				ug.pmUserCacheGroup_Name as \"Group_Name\",
				ug.pmUserCacheGroup_Code as \"Group_Code\",
				t9.AccessRightsT9Limit_IsAllowedT9 as \"isAllowed\"
			from AccessRightsT9Limit t9  

				left join pmUserCacheGroup ug  on ug.pmUserCacheGroup_id = t9.pmUserCacheGroup_id

			where t9.Org_id = :Org_id
		";
		$result = $this->db->query($query, array('Org_id'=>$data['Org_id']));
		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Сохранить настройки доступа к Т9
	 */
	function saveAccessT9Grid($data) {
		$query = "
			DELETE FROM AccessRightsT9Limit WHERE Org_id = :Org_id
		";
		$result = $this->db->query($query, array('Org_id'=>$data['Org_id']));
		$result = true;
		
		if ( $result === true ) {
			$groups = $data['groups'];
			foreach($groups as $group) {
				$params = array();
				$query = "
				    SELECT 
				        error_code as \"Error_Code\",
				        error_message as \"Error_Msg\"
				    FROM p_AccessRightsT9Limit_ins (
				        pmUserCacheGroup_id => :Group_id,
					    Org_id => :Org_id,
					    AccessRightsT9Limit_IsAllowedT9 => 1,
					    pmUser_id => :pmUser_id
				    )
				";

				$result = $this->db->query($query, array(
					'Org_id'=>$data['Org_id'],
					'Group_id' => $group->Group_id,
					'pmUser_id' => $data['pmUser_id']
					));
				
			}
		}
		else return array(array('Error_Msg' => 'Ошибка базы данных. Данные не сохранены.'));
		
		if (!is_object($result)) {
			return false;
		} else {
			$response = $result->result('array');
			return $response;
		}
	}
}