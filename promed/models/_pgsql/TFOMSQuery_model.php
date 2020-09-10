<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * TFOMSQuery_model - модель для работы с ТФОМС запросами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Sobenin Alex aka GTP_fox
 * @version			24.10.2018
 */
class TFOMSQuery_model extends swPgModel {
	/**
	 * TFOMSQuery_model constructor
	 */
	function __construct()
	{
		parent::__construct();
	}
	/**
	 * Сохраняет рассылку
	 * @param $data
	 * @return array|boolean
	 */
	function saveTFOMSQuery($data) {
		$params = array(
			'TFOMSQueryEMK_id' => empty($data['TFOMSQueryEMK_id']) ? null : $data['TFOMSQueryEMK_id'],
			'Lpu_id' => !empty($data['Lpu_id']) ? $data['Lpu_id'] : null,
			'Org_id' => !empty($data['Org_id']) ? $data['Org_id'] : null,
			'TFOMSQueryStatus_id' =>  !empty($data['TFOMSQueryStatus_id']) ? $data['TFOMSQueryStatus_id'] : 1,
			'TFOMSQueryEMK_begDate' => !empty($data['TFOMSQueryEMK_begDate']) ? $data['TFOMSQueryEMK_begDate'] : null,
			'TFOMSQueryEMK_endDate' => !empty($data['TFOMSQueryEMK_endDate']) ? $data['TFOMSQueryEMK_endDate'] : null,
			'pmUser_id' => $data['pmUser_id']
		);

		$procedure = empty($params['TFOMSQueryEMK_id']) ? 'p_TFOMSQueryEMK_ins' : 'p_TFOMSQueryEMK_upd';

		$query = "
		    select 
		        TFOMSQueryEMK_id as \"TFOMSQueryEMK_id\", 
		        Error_Code as \"Error_Code\", 
		        Error_Message as \"Error_Msg\"
			from {$procedure} (
				TFOMSQueryEMK_id := :TFOMSQueryEMK_id,
				Lpu_id := :Lpu_id,
				Org_id := :Org_id,
				TFOMSQueryStatus_id := :TFOMSQueryStatus_id,
				TFOMSQueryEMK_setDate := null,
				TFOMSQueryEMK_begDate := :TFOMSQueryEMK_begDate,
				TFOMSQueryEMK_endDate := :TFOMSQueryEMK_endDate,
				pmUser_id := :pmUser_id
	            )
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$result = $result->result('array');
			$data['TFOMSQueryEMK_id'] = $result[0]['TFOMSQueryEMK_id'];
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка статусов запроса
	 */
	function loadTFOMSQueryStatusList($data){

		if (!empty($data['forMO'])) {
			$filter = 'TFOMSQueryStatus_id != 1';
			$TFOMSQueryStatus_Name = "case 
				when TFOMSQueryStatus_id = 2 then 'Новый' 
				else TFOMSQueryStatus_Name 
			end";
		} else {
			$filter = '(1=1)';
			$TFOMSQueryStatus_Name = 'TFOMSQueryStatus_Name';
		}

		return $this->queryResult("
			select
				TFOMSQueryStatus_id as \"TFOMSQueryStatus_id\",
                TFOMSQueryStatus_Code as \"TFOMSQueryStatus_Code\",
                {$TFOMSQueryStatus_Name} as \"TFOMSQueryStatus_Name\"

			from
				v_TFOMSQueryStatus pp
			where 
				{$filter}
		", $data);
	}
	/**
	 * Получение списка запросов
	 */
	function loadTFOMSQueryList($data){
		$filter = '';
		$join = '';

		if (!empty($data['TFOMSQueryEMK_id'])) {
			$filter .= ' and tq.TFOMSQueryEMK_id = :TFOMSQueryEMK_id';
		}
		if (!empty($data['Org_id'])) {
			$filter .= ' and tq.Org_id = :Org_id';
		}
		if (!empty($data['Lpu_id'])) {
			$filter .= ' and tq.Lpu_id = :Lpu_id';
		}
		if (!empty($data['TFOMSQueryStatus_id'])) {
			$filter .= ' and tq.TFOMSQueryStatus_id = :TFOMSQueryStatus_id';
		}

		if (!empty($data['TFOMSQueryEMK_insDT'][0]) && !empty($data['TFOMSQueryEMK_insDT'][1])) {
			$filter .= ' and cast(tq.TFOMSQueryEMK_insDT as date) between :TFOMSQueryEMK_insDT_1 and :TFOMSQueryEMK_insDT_2';
			$data['TFOMSQueryEMK_insDT_1'] = $data['TFOMSQueryEMK_insDT'][0];
			$data['TFOMSQueryEMK_insDT_2'] = $data['TFOMSQueryEMK_insDT'][1];
		}
		if (!empty($data['forMO'])) { // МО не видят запросы со статусом "Новый"
			$filter .= ' and tq.TFOMSQueryStatus_id != 1';
			$TFOMSQueryStatus_Name = "case 
				when tq.TFOMSQueryStatus_id = 2 then 'Новый' 
				else tqs.TFOMSQueryStatus_Name 
			end";
		} else {
			$TFOMSQueryStatus_Name = 'tqs.TFOMSQueryStatus_Name';
		}

		$query = "
			select
				-- select
				tq.TFOMSQueryEMK_id as \"TFOMSQueryEMK_id\",
				tq.TFOMSQueryStatus_id as \"TFOMSQueryStatus_id\",
				{$TFOMSQueryStatus_Name} as \"TFOMSQueryStatus_Name\",
				tq.Org_id as \"Org_id\",
				tq.Lpu_id as \"Lpu_id\",
				l.Lpu_Nick as \"Lpu_Nick\",
				l.Lpu_Name as \"Lpu_Name\",
				o.Org_Nick as \"Org_Nick\",
				o.Org_Name as \"Org_Name\",
				to_char (tq.TFOMSQueryEMK_insDT, 'dd.mm.yyyy') as \"TFOMSQueryEMK_insDT\",
				to_char (tq.TFOMSQueryEMK_begDate, 'dd.mm.yyyy') || coalesce(' - ' || to_char (tq.TFOMSQueryEMK_endDate, 'dd.mm.yyyy'), '') as \"TFOMSQueryEMK_Date\",
				to_char (tq.TFOMSQueryEMK_begDate, 'dd.mm.yyyy') as \"TFOMSQueryEMK_begDate\",
				to_char (tq.TFOMSQueryEMK_endDate, 'dd.mm.yyyy') as \"TFOMSQueryEMK_endDate\"
				-- end select
			from
				-- from
				v_TFOMSQueryEMK as tq 
				left join v_Lpu l ON l.Lpu_id = tq.Lpu_id
				left join v_Org o ON o.Org_id = tq.Org_id
				left join v_TFOMSQueryStatus tqs on tqs.TFOMSQueryStatus_id = tq.TFOMSQueryStatus_id
				{$join}
				-- end from
			where
				-- where
				1 = 1
				{$filter}
				-- end where
			order by
				-- order by
				tq.TFOMSQueryEMK_id desc
				-- end order by
		";

		if(!empty($data['noCount'])){
			$result = $this->db->query($query, $data);
		}
		else {
			$result = $this->db->query(getLimitSQLPH($query, isset($data['start']) ? $data['start'] : 0 , isset($data['limit']) ? $data['limit'] : 1000), $data);
			$result_count = $this->db->query(getCountSQLPH($query), $data);
		}

		if (isset($result_count) && is_object($result_count)) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		if (is_object($result)) {
			if(!empty($data['noCount'])){
				$response = $result->result('array');
			}
			else{
				$response = array();
				$response['data'] = $result->result('array');
				$response['totalCount'] = $count;
			}
			return $response;
		} else {
			return false;
		}
	}
	/**
	 * Получение списка пациентов в запросе
	 */
	function loadTFOMSQueryPersonList($data){
		$join = '';

		$query = "
			select
				-- select
				TP.TFOMSQueryPerson_id as \"TFOMSQueryPerson_id\",
				TP.TFOMSQueryEMK_id as \"TFOMSQueryEMK_id\",
				TP.Person_id as \"Person_id\",
				RTRIM(LTRIM(PS.Person_Surname || ' ' || coalesce(PS.Person_Firname, '') || ' ' || coalesce(PS.Person_Secname, ''))) as \"Person_Fio\",
				to_char (PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
				CASE WHEN coalesce(TP.TFOMSQueryPerson_IsView, 1) = 1 THEN 'false' else 'true' END as \"TFOMSQueryPerson_IsView\",
				CASE WHEN coalesce(TP.TFOMSQueryPerson_IsAccess, 1) = 1 THEN 0 else 1 END as \"MultiSelectValue\",
				CASE WHEN coalesce(TP.TFOMSQueryPerson_IsAccess, 1) = 1 THEN 'false' else 'true' END as \"TFOMSQueryPerson_IsAccess\"
				-- end select
			from
				-- from
				v_TFOMSQueryPerson as TP
				left join v_PersonState PS ON PS.Person_id = TP.Person_id
				-- end from
			where
				-- where
				TP.TFOMSQueryEMK_id = :TFOMSQueryEMK_id
				-- end where
			order by
				-- order by
				PS.Person_SurName asc
				-- end order by
		";

		$result = $this->db->query(getLimitSQLPH($query, isset($data['start']) ? $data['start'] : 0 , isset($data['limit']) ? $data['limit'] : 1000), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

		if (is_object($result_count)) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		} else {
			$count = 0;
		}

		if (is_object($result)) {
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	}
	/**
	 * Добавление человека к запросу
	 * @param $data
	 * @return array|boolean
	 */
	function addPersonToQuery($data) {

		$available = $this->checkPersonBeforeAdd($data);

		if(!$available['success'] || !empty($available['Error_Msg']))
			return array($available);

		$params = array(
			'TFOMSQueryEMK_id' => $data['TFOMSQueryEMK_id'],
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "
		    select 
		        TFOMSQueryPerson_id as \"TFOMSQueryPerson_id\", 
		        Error_Code as \"Error_Code\", 
		        Error_Message as \"Error_Msg\"
			from p_TFOMSQueryPerson_ins (
				TFOMSQueryPerson_id := :TFOMSQueryPerson_id,
				TFOMSQueryEMK_id := :TFOMSQueryEMK_id,
				Person_id := :Person_id,
				pmUser_id := :pmUser_id
			    )
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$result = $result->result('array');
			$data['TFOMSQueryPerson_id'] = $result[0]['TFOMSQueryPerson_id'];
			return $result;
		} else {
			return false;
		}
	}
	/**
	 * Удаление человека из запроса
	 * @param $data
	 * @return array|boolean
	 */
	function deletePersonFromQuery($data) {

		$params = array(
			'TFOMSQueryPerson_id' => $data['TFOMSQueryPerson_id']
		);

		$query = "
		    select 
		        Error_Code as \"Error_Code\", 
		        Error_Message as \"Error_Msg\"
			from p_TFOMSQueryPerson_del (
				TFOMSQueryPerson_id := :TFOMSQueryPerson_id
				)
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$result = $result->result('array');
			return $result;
		} else {
			return false;
		}
	}
	/**
	 * Удаление запроса
	 * @param $data
	 * @return array|boolean
	 */
	function deleteQuery($data) {

		$params = array(
			'TFOMSQueryEMK_id' => $data['TFOMSQueryEMK_id']
		);

		$query = "
			select
				-- select
				TP.TFOMSQueryPerson_id as \"TFOMSQueryPerson_id\"
				-- end select
			from
				-- from
				v_TFOMSQueryPerson as TP
				-- end from
			where
				-- where
				TP.TFOMSQueryEMK_id = :TFOMSQueryEMK_id
				-- end where
		";

		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			$result = $result->result('array');
			foreach($result as $person){
				$this->deletePersonFromQuery($person);
			}
		}

		$query = "
		    select 
		        Error_Code as \"Error_Code\", 
		        Error_Message as \"Error_Msg\"
			
			from p_TFOMSQueryEMK_del(
				TFOMSQueryEMK_id := :TFOMSQueryEMK_id
                )
		";

		$resultQuery = $this->db->query($query, $params);

		if (is_object($resultQuery)) {
			$resultQuery = $resultQuery->result('array');
			return $resultQuery;
		} else {
			return false;
		}
	}
	/**
	 * Проверка наличия человека в открытых запросах
	 * @param $data
	 * @return array|boolean
	 */
	function checkPersonBeforeAdd($data) {
		$ret = array('success'=>false);
		$query = "
			select 
				-- select
				tq.TFOMSQueryEMK_id as \"TFOMSQueryEMK_id\",
				to_char (tq.TFOMSQueryEMK_insDT, 'dd.mm.yyyy') as \"DateCreate\",
				ts.TFOMSQueryStatus_Name as \"TFOMSQueryStatus_Name\"
				-- end select
			from
				-- from
				v_TFOMSQueryPerson as tp
				inner join v_TFOMSQueryEMK tq ON tq.TFOMSQueryEMK_id = tp.TFOMSQueryEMK_id
				left join v_TFOMSQueryStatus ts ON ts.TFOMSQueryStatus_id = tq.TFOMSQueryStatus_id
				left join v_PersonState PS ON PS.Person_id = TP.Person_id
				-- end from
			where
				-- where
				TP.Person_id = :Person_id
				AND tq.TFOMSQueryStatus_id != 5 -- любые запросы кроме закрытых
				AND tq.Lpu_id = :Lpu_id
				AND tq.Org_id = :Org_id
				-- end where
			order by
				tq.TFOMSQueryEMK_id DESC
			limit 1
		";

		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$result = $result->result('array');
			// По умолчанию пациент в других запросах не нашелся
			$ret['success'] = true;
			// Если нашелся пациент в других запросах, формируем сообщение
			if(!empty($result[0]['TFOMSQueryEMK_id'])){
				$errorMsg = 'Данный пациент состоит в запросе id:'.$result[0]['TFOMSQueryEMK_id'].' от '.$result[0]['DateCreate'];
				if(!empty($result[0]['TFOMSQueryStatus_Name']))
					$errorMsg .= ' в статусе "'.$result[0]['TFOMSQueryStatus_Name'].'"';
				$ret = array('success'=>false,'Error_Msg'=>$errorMsg);
			}
		}
		return $ret;
	}
	/**
	 * Проверка наличия в запросе ограничений на просмотр ЭМК
	 * @param $params
	 * @return array|boolean
	 */
	function checkQueryAccessAll($params) {
		$ret = false;
		$query = "
			select
				tp.TFOMSQueryPerson_id as \"TFOMSQueryPerson_id\"
			from
				v_TFOMSQueryEMK tq
				left join v_TFOMSQueryPerson tp ON tp.TFOMSQueryEMK_id = tq.TFOMSQueryEMK_id
			where
				tq.TFOMSQueryEMK_id = :TFOMSQueryEMK_id
				AND coalesce(TP.TFOMSQueryPerson_IsAccess, 1) = 1
			limit 1
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$result = $result->result('array');
			$ret = 3;
			if(!empty($result[0]['TFOMSQueryPerson_id'])){
				$ret = 4;
			}
		}
		return $ret;
	}
	/**
	 * Изменение доступа для пациента в запросе
	 * @param $data
	 * @return array|boolean
	 */
	function setAccessPerson($data) {

		$params = array(
			'TFOMSQueryPerson_id' => $data['TFOMSQueryPerson_id'],
			'TFOMSQueryPerson_isAccess' => $data['TFOMSQueryPerson_isAccess'],
			'pmUser_id' => $data['pmUser_id']
		);
		$query = "
		    select 
		        Error_Code as \"Error_Code\", 
		        Error_Message as \"Error_Msg\"
			from p_TFOMSQueryPerson_setAccess (
				TFOMSQueryPerson_id := :TFOMSQueryPerson_id,
				TFOMSQueryPerson_isAccess := :TFOMSQueryPerson_isAccess,
				pmUser_id := :pmUser_id
				)
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$result = $result->result('array');
			return $result;
		} else {
			return false;
		}
	}
	/**
	 * Изменение доступа для пациента в запросе
	 * @param $data
	 * @return array|boolean
	 */
	function setAccessAllPerson($data) {

		$params = array(
			'TFOMSQueryEMK_id' => $data['TFOMSQueryEMK_id'],
			'TFOMSQueryPerson_isAccess' => $data['TFOMSQueryPerson_isAccess'],
			'pmUser_id' => $data['pmUser_id']
		);
		$query = "
		    select 
		        Error_Code as \"Error_Code\", 
		        Error_Message as \"Error_Msg\"
			from p_TFOMSQueryPerson_setAccessAll (
				TFOMSQueryEMK_id := :TFOMSQueryEMK_id,
				TFOMSQueryPerson_isAccess := :TFOMSQueryPerson_isAccess,
				pmUser_id := :pmUser_id
				)
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$result = $result->result('array');
			return $result;
		} else {
			return false;
		}
	}
	/**
	 * Изменение статуса запроса
	 * @param $data
	 * @return array|boolean
	 */
	function setQueryStatus($data) {

		$params = array(
			'TFOMSQueryEMK_id' => $data['TFOMSQueryEMK_id'],
			'TFOMSQueryStatus_id' => $data['TFOMSQueryStatus_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$query = "
		    select 
		        Error_Code as \"Error_Code\", 
		        Error_Message as \"Error_Msg\"
			from p_TFOMSQueryEMK_setStatus (
				TFOMSQueryEMK_id := :TFOMSQueryEMK_id,
				TFOMSQueryStatus_id := :TFOMSQueryStatus_id,
				pmUser_id := :pmUser_id
				)
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$result = $result->result('array');
			return $result;
		} else {
			return false;
		}
	}
	/**
	 * Изменение статуса просмотра ЭМК пациента
	 * @param $data
	 * @return array|boolean
	 */
	function setViewPersonStatus($data) {

		$params = array(
			'TFOMSQueryPerson_id' => $data['TFOMSQueryPerson_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$query = "
		    select 
		        Error_Code as \"Error_Code\", 
		        Error_Message as \"Error_Msg\"
			from p_TFOMSQueryPerson_setView (
				TFOMSQueryPerson_id := :TFOMSQueryPerson_id,
				TFOMSQueryPerson_isView := 2, -- Просмотрено
				pmUser_id := :pmUser_id
				)
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$result = $result->result('array');
			return $result;
		} else {
			return false;
		}
	}
}