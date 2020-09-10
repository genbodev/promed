<?php

require_once(APPPATH.'models/_pgsql/Org_model.php');

class Samara_Org_model extends Org_model {
	/**
	 * __construct
	 */
    function __construct() {
		parent::__construct();
    }
	/**
	 * savePost
	 */
	function savePost($data){
        //throw 'error';
		$trans_good = true;
		$trans_result = array();

		if ( (!isset($data['Post_id'])) || ($data['Post_id'] <= 0) ) {
			$procedure = 'p_Post_ins';
		}
		else {
			$procedure = 'p_Post_upd';
		}

		if ($trans_good === true) {
			$query = "
				select
					Post_id as \"Post_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from " . $procedure . "(
					Post_id := :Post_id, -- bigint
					Server_id := :Server_id, -- bigint
					Post_Name := :Post_Name , -- varchar(50)
					pmUser_id := :pmUser_id -- bigint
				)
			";
			//параметры, которые надо передать в запрос
			$paramset = array(
				'Post_id',
				'Server_id',
				'Post_Name',
				'pmUser_id'
			);
			//формируем массив параметров
			$queryParams = array();
			foreach ($paramset as $p)  {
				if (isset($data[$p])) {
					$queryParams[$p] = $data[$p];
				} else {
					$queryParams[$p] = null;
				}
			}

			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				//todo: ошибка при сохранении
			}
			$response = $result->result('array');

			if ( !is_array($response) || count($response) == 0 ) {
				//ошибка при сохранении, база ничего не сказала
			}
			else {
				//если была ошибка при сохранении, база вернула сообщение об ошибке в $response[0]['Error_Msg']
				$trans_result = $response;
			}
		}
		return $trans_result;
	}//function save()   	
	
	/**
	 * Возвращает список ЛПУ
	 */
	function getLpuList($data) {
		$filter = "";
		$queryParams = array();

		if ( isset($data['Org_id']) ) {
			$filter .= " and Org_id = :Org_id";
			$queryParams['Org_id'] = $data['Org_id'];
		}
		elseif ( isset($data['Lpu_oid']) ) {
			$filter .= " and Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_oid'];
		}
		else {
			if ( isset($data['Org_Name']) ) {
				$filter .= " and Lpu_Name ilike :Lpu_Name";
				$queryParams['Lpu_Name'] = "%" . $data['Org_Name'] . "%";
			}

			if ( isset($data['Org_Nick']) ) {
				$filter .= " and Lpu_Nick ilike :Lpu_Nick";
				$queryParams['Lpu_Nick'] = "%" . $data['Org_Nick'] . "%";
			}
		}

		// Petrov Pavel
		if (isset($data['query']) && !empty($data['query']) && $data['OrgType'] == "lpu") {
			$filter .= " and (Lpu_Nick ilike :query or Lpu_Name ilike :query or Lpu_id ilike :id)";
			$queryParams['query'] = "%" . $data['query'] . "%";
			$queryParams['id'] = $data['query'] . "%";
		}
		
		$query = "
			SELECT
				Org_id as \"Org_id\"
				Lpu_id as \"Lpu_id\"
				null as \"Org_Code\",
				rtrim(Lpu_Nick) as \"Org_Nick\",
				rtrim(Lpu_Nick) as \"Lpu_Nick\",
				rtrim(Lpu_Name) as \"Org_Name\",
				Lpu_f003mcod as \"Lpu_f003mcod\"
			FROM
				v_Lpu o
			WHERE LpuType_Code = 1
				" . $filter . "
		";
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}
}
