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
class TFOMSQuery_model extends swModel {
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
			declare
				@TFOMSQueryEMK_id bigint = :TFOMSQueryEMK_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@TFOMSQueryEMK_id = @TFOMSQueryEMK_id output,
				@Lpu_id = :Lpu_id,
				@Org_id = :Org_id,
				@TFOMSQueryStatus_id = :TFOMSQueryStatus_id,
				@TFOMSQueryEMK_setDate = null,
				@TFOMSQueryEMK_begDate = :TFOMSQueryEMK_begDate,
				@TFOMSQueryEMK_endDate = :TFOMSQueryEMK_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @TFOMSQueryEMK_id as TFOMSQueryEMK_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
			end as TFOMSQueryStatus_Name";
		} else {
			$filter = '(1=1)';
			$TFOMSQueryStatus_Name = 'TFOMSQueryStatus_Name';
		}

		return $this->queryResult("
			select
				TFOMSQueryStatus_id,
				TFOMSQueryStatus_Code,
				{$TFOMSQueryStatus_Name}
			from
				v_TFOMSQueryStatus pp (nolock)
			where 
				{$filter}
		", $data);
	}
	/**
	 * Получение списка запросов
	 */
	function loadTFOMSQueryList($data,$formatDT = 104){
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
			end as TFOMSQueryStatus_Name";
		} else {
			$TFOMSQueryStatus_Name = 'tqs.TFOMSQueryStatus_Name';
		}

		$query = "
			select
				-- select
				tq.TFOMSQueryEMK_id
				,tq.TFOMSQueryStatus_id
				,{$TFOMSQueryStatus_Name}
				,tq.Org_id
				,tq.Lpu_id
				,l.Lpu_Nick
				,l.Lpu_Name
				,o.Org_Nick
				,o.Org_Name
				,convert(varchar(10), tq.TFOMSQueryEMK_insDT, {$formatDT}) as TFOMSQueryEMK_insDT
				,convert(varchar(10), tq.TFOMSQueryEMK_begDate, {$formatDT}) + isnull(' - ' + convert(varchar(10), tq.TFOMSQueryEMK_endDate, {$formatDT}), '') as TFOMSQueryEMK_Date
				,convert(varchar(10), tq.TFOMSQueryEMK_begDate, {$formatDT}) as TFOMSQueryEMK_begDate
				,convert(varchar(10), tq.TFOMSQueryEMK_endDate, {$formatDT}) as TFOMSQueryEMK_endDate
				-- end select
			from
				-- from
				v_TFOMSQueryEMK as tq with (nolock)
				left join v_Lpu l with(nolock) ON l.Lpu_id = tq.Lpu_id
				left join v_Org o with(nolock) ON o.Org_id = tq.Org_id
				left join v_TFOMSQueryStatus tqs with (nolock) on tqs.TFOMSQueryStatus_id = tq.TFOMSQueryStatus_id
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
			$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
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
				TP.TFOMSQueryPerson_id
				,TP.TFOMSQueryEMK_id
				,TP.Person_id
				,RTRIM(LTRIM(PS.Person_Surname + ' ' + ISNULL(PS.Person_Firname, '') + ' ' + ISNULL(PS.Person_Secname, ''))) as Person_Fio
				,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
				,CASE WHEN ISNULL(TP.TFOMSQueryPerson_IsView, 1) = 1 THEN 'false' else 'true' END as TFOMSQueryPerson_IsView
				,CASE WHEN ISNULL(TP.TFOMSQueryPerson_IsAccess, 1) = 1 THEN 0 else 1 END as MultiSelectValue
				,CASE WHEN ISNULL(TP.TFOMSQueryPerson_IsAccess, 1) = 1 THEN 'false' else 'true' END as TFOMSQueryPerson_IsAccess
				-- end select
			from
				-- from
				v_TFOMSQueryPerson as TP with (nolock)
				left join v_PersonState PS with(nolock) ON PS.Person_id = TP.Person_id
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

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
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
			declare
				@TFOMSQueryPerson_id bigint = NULL,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_TFOMSQueryPerson_ins
				@TFOMSQueryPerson_id = @TFOMSQueryPerson_id output,
				@TFOMSQueryEMK_id = :TFOMSQueryEMK_id,
				@Person_id = :Person_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @TFOMSQueryPerson_id as TFOMSQueryPerson_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_TFOMSQueryPerson_del
				@TFOMSQueryPerson_id = :TFOMSQueryPerson_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
				TP.TFOMSQueryPerson_id
				-- end select
			from
				-- from
				v_TFOMSQueryPerson as TP with (nolock)
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
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_TFOMSQueryEMK_del
				@TFOMSQueryEMK_id = :TFOMSQueryEMK_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
			select top 1
				-- select
				tq.TFOMSQueryEMK_id
				,convert(varchar(10), tq.TFOMSQueryEMK_insDT, 104) as DateCreate
				,ts.TFOMSQueryStatus_Name
				-- end select
			from
				-- from
				v_TFOMSQueryPerson as tp with (nolock)
				inner join v_TFOMSQueryEMK tq with(nolock) ON tq.TFOMSQueryEMK_id = tp.TFOMSQueryEMK_id
				left join v_TFOMSQueryStatus ts with(nolock) ON ts.TFOMSQueryStatus_id = tq.TFOMSQueryStatus_id
				left join v_PersonState PS with(nolock) ON PS.Person_id = TP.Person_id
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
			select top 1
				tp.TFOMSQueryPerson_id
			from
				v_TFOMSQueryEMK tq with(nolock)
				left join v_TFOMSQueryPerson tp with (nolock) ON tp.TFOMSQueryEMK_id = tq.TFOMSQueryEMK_id
			where
				tq.TFOMSQueryEMK_id = :TFOMSQueryEMK_id
				AND ISNULL(TP.TFOMSQueryPerson_IsAccess, 1) = 1
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
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_TFOMSQueryPerson_setAccess
				@TFOMSQueryPerson_id = :TFOMSQueryPerson_id,
				@TFOMSQueryPerson_isAccess = :TFOMSQueryPerson_isAccess,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_TFOMSQueryPerson_setAccessAll
				@TFOMSQueryEMK_id = :TFOMSQueryEMK_id,
				@TFOMSQueryPerson_isAccess = :TFOMSQueryPerson_isAccess,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_TFOMSQueryEMK_setStatus
				@TFOMSQueryEMK_id = :TFOMSQueryEMK_id,
				@TFOMSQueryStatus_id = :TFOMSQueryStatus_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_TFOMSQueryPerson_setView
				@TFOMSQueryPerson_id = :TFOMSQueryPerson_id,
				@TFOMSQueryPerson_isView = 2, -- Просмотрено
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
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