<?php defined('BASEPATH') or die ('No direct script access allowed');

class DrugNonpropNames_model extends swModel {
	
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 *	Возвращает данные для постраничного вывода
	 */
	function returnPagingData($q, &$p) {
		$get_count_result = $this->db->query(getCountSQLPH($q), $p);
		if( !is_object($get_count_result) ) {
			return false;
		}
		$get_count_result = $get_count_result->result('array');
		
		$result = $this->db->query(getLimitSQLPH($q, $p['start'], $p['limit']), $p);
		if( !is_object($result) ) {
			return false;
		}
		$result = $result->result('array');
		return array(
			'data' => $result,
			'totalCount' => $get_count_result[0]['cnt']
		);
	}

    /**
	 * Получение списка непатентованных наименований
	 */
	function loadDrugNonpropNamesList($data) {
		$filters = "1=1";
		$params = array();

		if (!empty($data['query'])) {
			$filters .= " and DNN.DrugNonpropNames_Nick like :query";
			$params['query'] = '%'.$data['query'].'%';
		}
		if (!empty($data['DrugNonpropNames_Nick'])) {
			$filters .= " and DNN.DrugNonpropNames_Nick like :DrugNonpropNames_Nick";
			$params['DrugNonpropNames_Nick'] = '%'.$data['DrugNonpropNames_Nick'].'%';
		}
		if(!empty($data['DrugNonpropNames_Code'])) {
			$filters .= " and DNN.DrugNonpropNames_Code = :DrugNonpropNames_Code";
			$params['DrugNonpropNames_Code'] = $data['DrugNonpropNames_Code'];
		}
		if (!empty($data['DrugNonpropNames_Property'])) {
			$filters .= " and DNN.DrugNonpropNames_Property like :DrugNonpropNames_Property";
			$params['DrugNonpropNames_Property'] = '%'.$data['DrugNonpropNames_Property'].'%';
		}
		if (!empty($data['RlsActmatters_id'])) {
			$filters .= " and exists (
				select *
				from rls.PREP_ACTMATTERS PA with(nolock)
				inner join rls.v_Prep Prep with(nolock) on Prep.Prep_id = PA.PREPID
				where PA.MATTERID = :RlsActmatters_id and Prep.DrugNonpropNames_id = DNN.DrugNonpropNames_id
			)";
			$params['RlsActmatters_id'] = $data['RlsActmatters_id'];
		}

		$query = "
			select 
			-- select
				DNN.DrugNonpropNames_id,
				DNN.DrugNonpropNames_Name,
				DNN.DrugNonpropNames_Nick,
				DNN.DrugNonpropNames_Code,
				DNN.DrugNonpropNames_Property
			-- end select
			from
			-- from
				rls.v_DrugNonpropNames DNN with(nolock)
			-- end from
			where
			-- where
				{$filters}
			-- end where
			order by
			-- order by
				DNN.DrugNonpropNames_Name
			-- end order by
		";
		$params['start'] = $data['start'];
		$params['limit'] = $data['limit'];
		//echo getDebugSQL($query, $params);exit;
		if(!empty($data['forCombo'])){
			return $this->queryResult($query, $params);
		} else {
			return $this->returnPagingData($query, $params);
		}
	}

	/**
	 * Сохранение непатентованного наименования
	 */
	function saveDrugNonpropNames($data) {
		if(!empty($data['DrugNonpropNames_id'])){
			$proc = 'rls.p_DrugNonpropNames_upd';
		} else {
			$proc = 'rls.p_DrugNonpropNames_ins';
		}
		$params = array(
			'DrugNonpropNames_id' => (!empty($data['DrugNonpropNames_id'])?$data['DrugNonpropNames_id']:null),
			'DrugNonpropNames_Name' => (!empty($data['DrugNonpropNames_Name'])?$data['DrugNonpropNames_Name']:null),
			'DrugNonpropNames_Nick' => (!empty($data['DrugNonpropNames_Nick'])?$data['DrugNonpropNames_Nick']:null),
			'DrugNonpropNames_Code' => (!empty($data['DrugNonpropNames_Code'])?$data['DrugNonpropNames_Code']:null),
			'DrugNonpropNames_Property' => (!empty($data['DrugNonpropNames_Property'])?$data['DrugNonpropNames_Property']:null),
			'pmUser_id' => (!empty($data['pmUser_id'])?$data['pmUser_id']:1)
		);
		return $this->execCommonSP($proc,$params);
	}

	/**
	 * Удаление непатентованного наименования
	 */
	function deleteDrugNonpropNames($data) {
		if(!empty($data['id'])){
			$params = array(
				'DrugNonpropNames_id' => (!empty($data['id'])?$data['id']:null)
			);
			return $this->execCommonSP('rls.p_DrugNonpropNames_del',$params);
		} else {
			return array('Error_Msg'=>'Не указан идентификатор Непатентованного наименования');
		}
	}

	/**
	 * Проверка связанных значений для непатентованого наименования
	 */
	function checkDrugNonpropNames($data) {

		$query = "
			select 
				count(*) as Count
			from
				rls.v_Prep prep with(nolock)
			where
				prep.DrugNonpropNames_id = :DrugNonpropNames_id
		";
		//echo getDebugSQL($query, $params);exit;
		$res = $this->queryResult($query, $data);
		if(count($res) == 1 && $res[0]['Count'] > 0){
			$query = "
				select 
					table_description as tbl_desc
				from
					v_columns 
				where 
					table_name like 'prep'
			";
			//echo getDebugSQL($query, $params);exit;
			return $this->queryResult($query);
		}
		return array(array());
	}
}