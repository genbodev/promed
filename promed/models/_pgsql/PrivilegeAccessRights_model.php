<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PrivilegeAccessRights_model - модель для работы с ограничением прав доступа по льготам
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			22.10.2013
 */

class PrivilegeAccessRights_model extends SwPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Сохраняет ограничения прав доступа по льготе
	 */
	function savePrivilegeAccessRights($data)
	{
		$response = array(
			'PrivilegeType_id' => null
			,'Error_Code' => null
			,'Error_Msg' => null
		);

		$this->beginTransaction();

		if ($data['RecordStatus_isNewRecord'] == 1) {
			$query = "
				select PrivilegeType_id as \"PrivilegeType_id\"
				from v_PrivilegeAccessRights
				where PrivilegeType_id = :PrivilegeType_id
				limit 1
			";

			$result = $this->db->query($query, array(
				'PrivilegeType_id' => $data['PrivilegeType_id']
			));

			if ( !is_object($result) ) {
				$this->rollbackTransaction();
				$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (проверка дублей ограничения прав доступа по льготе)';
				return array($response);
			}

			$queryResponse = $result->result('array');

			if ( !is_array($queryResponse) ) {
				$this->rollbackTransaction();
				$response['Error_Msg'] = 'Ошибка при проверке дублей ограничения прав доступа по льготе';
				return array($response);
			}
			else if ( count($queryResponse) > 0 ) {
				$this->rollbackTransaction();
				$response['Error_Msg'] = 'Обнаружены дубли ограничения прав доступа по льготе';
				return array($response);
			}
		}

		if ( !empty($data['PrivilegeAccessRightsData']) ) {
			ConvertFromWin1251ToUTF8($_POST['PrivilegeAccessRightsData']);
			$PrivilegeAccessRightsData = json_decode($_POST['PrivilegeAccessRightsData'], true);

			if ( is_array($PrivilegeAccessRightsData) ) {
				for ( $i = 0; $i < count($PrivilegeAccessRightsData); $i++ ) {
					if ( !isset($PrivilegeAccessRightsData[$i]['RecordStatus_Code']) || !is_numeric($PrivilegeAccessRightsData[$i]['RecordStatus_Code']) || !in_array($PrivilegeAccessRightsData[$i]['RecordStatus_Code'], array(0, 2, 3)) ) {
						continue;
					}

					$PrivilegeAccessRight = array(
						'PrivilegeType_id' => $data['PrivilegeType_id'],
						'PrivilegeAccessRights_id' => $PrivilegeAccessRightsData[$i]['PrivilegeAccessRights_id'],
						'pmUser_id' => $data['pmUser_id']
					);

					if ($PrivilegeAccessRight['PrivilegeAccessRights_id']<=0) {
						$PrivilegeAccessRight['PrivilegeAccessRights_id'] = NULL;
					}

					if (empty($PrivilegeAccessRightsData[$i]['Lpu_id'])) {
						$PrivilegeAccessRight['Lpu_id'] = NULL;
						$PrivilegeAccessRight['PrivilegeAccessRights_UserGroups'] = $PrivilegeAccessRightsData[$i]['PrivilegeAccessRights_UserGroups'];
					} else {
						$PrivilegeAccessRight['Lpu_id'] = $PrivilegeAccessRightsData[$i]['Lpu_id'];
						$PrivilegeAccessRight['PrivilegeAccessRights_UserGroups'] = NULL;
					}

					switch ( $PrivilegeAccessRightsData[$i]['RecordStatus_Code'] ) {
						case 0:
						case 2:
							$queryResponse = $this->savePrivilegeAccessRightsUnit($PrivilegeAccessRight);
							break;

						case 3:
							$queryResponse = $this->deletePrivilegeAccessRightsUnit($PrivilegeAccessRight);
							break;
					}

					if ( !is_array($queryResponse) ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Ошибка при ' . ($PrivilegeAccessRightsData[$i]['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' ограничения прав доступа по льготе';
						return array($response);
					}
					else if ( !empty($queryResponse[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						return $queryResponse;
					}
				}
			}
		}

		$response['PrivilegeType_id'] = $data['PrivilegeType_id'];

		$this->commitTransaction();

		return array($response);
	}

	/**
	 * Сохраняет ограничение права доступа по льготе
	 */
	function savePrivilegeAccessRightsUnit($data) {
		$query = "
			select
			    PrivilegeAccessRights_id as \"FoodCookSpec_id\", 
			    Error_Code as \"Error_Code\", 
			    Error_Message as \"Error_Msg\"
			from p_PrivilegeAccessRights_" . (!empty($data['PrivilegeAccessRights_id']) && $data['PrivilegeAccessRights_id'] > 0 ? "upd" : "ins") . " (
				PrivilegeAccessRights_id := :PrivilegeAccessRights_id,
				PrivilegeType_id := :PrivilegeType_id,
				PrivilegeAccessRights_UserGroups := :PrivilegeAccessRights_UserGroups,
				Lpu_id := :Lpu_id,
				pmUser_id := :pmUser_id
				)
		";

		$params = array(
			'PrivilegeAccessRights_id' => $data['PrivilegeAccessRights_id'],
			'PrivilegeType_id' => $data['PrivilegeType_id'],
			'PrivilegeAccessRights_UserGroups' => $data['PrivilegeAccessRights_UserGroups'],
			'Lpu_id' => $data['Lpu_id'],
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
	 * Удаляет ограничения прав доступа по льготе
	 */
	function deletePrivilegeAccessRights($data)
	{
		$response = array(
			'Error_Code' => NULL,
			'Error_Msg' => NULL
		);

		$this->beginTransaction();

		$query = "
			select PrivilegeAccessRights_id as \"PrivilegeAccessRights_id\"
			from v_PrivilegeAccessRights
			where PrivilegeType_id = :PrivilegeType_id
		";

		$result = $this->db->query($query, array(
			'PrivilegeType_id' => $data['PrivilegeType_id']
		));

		if ( !is_object($result) ) {
			$this->rollbackTransaction();
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных';
			return array($response);
		}

		$queryResponse = $result->result('array');

		if (is_array($queryResponse)) {
			for ($i=0; $i<count($queryResponse); $i++) {
				$deleteResponse = $this->deletePrivilegeAccessRightsUnit($queryResponse[$i]);

				if ( !is_array($deleteResponse) ) {
					$this->rollbackTransaction();
					$response['Error_Msg'] = 'Ошибка при удалении ограничения прав доступа по льготе';
					return array($response);
				}
				else if ( !empty($deleteResponse[0]['Error_Msg']) ) {
					$this->rollbackTransaction();
					return $queryResponse;
				}
				$response[] = $deleteResponse;
			}
		} else {
			$this->rollbackTransaction();
			$response['Error_Msg'] = 'Ошибка при удалении ограничения прав доступа по льготе';
			return array($response);
		}

		$this->commitTransaction();
		return array($response);
	}

	/**
	 * Удаляет ограничение права доступа по льготе
	 */
	function deletePrivilegeAccessRightsUnit($data)
	{
		$query = "
            select 
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_PrivilegeAccessRights_del (
				PrivilegeAccessRights_id := :PrivilegeAccessRights_id
				)
		";

		$queryParams = array(
			'PrivilegeAccessRights_id' => $data['PrivilegeAccessRights_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}



	/**
	 * Возвращает список ограничений прав доступа по льготе
	 */
	function loadPrivilegeAccessRightsGrid($data)
	{
		$params = array();

		$query = "
			select distinct
				PAR.PrivilegeType_id as \"PrivilegeType_id\",
				PT.PrivilegeType_Name as \"PrivilegeType_Name\",
				PAR.PrivilegeAccessRights_UserGroups as \"PrivilegeAccessRights_UserGroups\",
				PAR1.PrivilegeAccessRights_HasLpu as \"PrivilegeAccessRights_HasLpu\"
			from
				v_PrivilegeAccessRights PAR
				left join v_PrivilegeType PT on PT.PrivilegeType_id = PAR.PrivilegeType_id
				LEFT JOIN LATERAL (
					select
						(CASE COUNT(Lpu_id) WHEN 0 THEN 0 ELSE 1 END) as PrivilegeAccessRights_HasLpu
					from v_PrivilegeAccessRights
					where PrivilegeType_id = PAR.PrivilegeType_id
				) PAR1 ON TRUE
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
	 * Возвращает список МО, которым разрешён доступ к льготам указанного типа
	 */
	function loadPrivilegeAccessRightsLpuGrid($data)
	{
		$params = array(
			'PrivilegeType_id' => $data['PrivilegeType_id']
		);

		$query = "
			select
				PAR.PrivilegeAccessRights_id as \"PrivilegeAccessRights_id\",
				PAR.Lpu_id as \"Lpu_id\",
				L.Lpu_Nick as \"Lpu_Nick\",
				1 as \"RecordStatus_Code\"
			from v_PrivilegeAccessRights PAR
				left join v_Lpu L on L.Lpu_id = PAR.Lpu_id
			where
				PAR.PrivilegeType_id = :PrivilegeType_id
				and PAR.Lpu_id is not NUll
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
	 * Возвращает данные для формы редактирования ораничений прав доступа по льготе
	 */
	function loadPrivilegeAccessRightsForm($data)
	{
		$params = array(
			'PrivilegeType_id' => $data['PrivilegeType_id']
		);

		$query = "
			select
				PAR.PrivilegeAccessRights_id as \"PrivilegeAccessRights_id\",
				PAR.PrivilegeAccessRights_UserGroups as \"PrivilegeAccessRights_UserGroups\",
				1 as \"RecordStatus_Code\"
			from v_PrivilegeAccessRights PAR
			where
				PAR.Lpu_id is null
				and PAR.PrivilegeType_id = :PrivilegeType_id
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
	 * Проверяет ограничение прав доступа ко льготе
	 */
	function checkPrivilegeAccessRights($data)
	{
		$response = false;
		$params = array(
			'PrivilegeType_id' => $data['PrivilegeType_id']
		);

		$query = "
			select
				COUNT(PAR.PrivilegeAccessRights_id) as \"Count\"
			from v_PrivilegeAccessRights PAR
			where
				PAR.PrivilegeType_id = :PrivilegeType_id
            limit 1
		";

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			$res_arr = $result->result('array');
			if ($res_arr[0]['Count'] == 0) {
				$response = true;
			} else {
				$groups = explode('|', $data['session']['groups']);
				$user_groups = "'".$groups[0]."'";
				if (count($groups)>1) {
					for ($i=1; $i<count($groups);$i++) {
						$user_groups .= ",'".$groups[$i]."'";
					}
				}

				$params['Lpu_id'] = $data['Lpu_id'];
				$query = "
					select
						(case when COUNT(PAR.PrivilegeAccessRights_id)>0 then 1 else 0 end) as \"AllowAccess\"
					from v_PrivilegeAccessRights PAR
					where
						PAR.PrivilegeType_id = :PrivilegeType_id
						and (
							(PAR.PrivilegeAccessRights_UserGroups in (".$user_groups.") and PAR.Lpu_id is null)
							or
							(PAR.Lpu_id = :Lpu_id and PAR.PrivilegeAccessRights_UserGroups is null)
						)
				";

				$result = $this->db->query($query, $params);

				if (is_object($result))
				{
					$res_arr = $result->result('array');
					if (!empty($res_arr) && $res_arr[0]['AllowAccess'] == 1) {
						$response = true;
					}
				}
			}
		}

		return $response;
	}
}