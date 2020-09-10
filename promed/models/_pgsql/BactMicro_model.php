<?php
require_once('Collection_model.php');
/**
 * BactMicro_model
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       Qusijue
 * @version      Сентябрь 2019
*/
class BactMicro_model extends Collection_model {
	protected $fields = [];

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	function getMicroList($params) {
		$additional = ""; $whereClause = "1=1";
		
		$isEmptyMode = empty($params['mode']);
		$isEmptyMS = empty($params['MedService_id']);
		$isLab = $params['mode'] == 'lab';
		if (!$isEmptyMode && !$isEmptyMS && $isLab) {
			$additional = "with Lab as (
			select
				BactMicro_id as \"BactMicro_id\"
			from v_BactMicroLab
			where MedService_id = :MedService_id
			)";
			
			$whereClause .= " and BactMicro_id in (select \"BactMicro_id\" from Lab)";
		}
		if (!empty($params['BactMicro_Level'])) $whereClause .= " and BactMicro_Level = :BactMicro_Level";
		if (!empty($params['BactGramColor_Code'])) $whereClause .= " and bgc.BactGramColor_Code = :BactGramColor_Code";
		if (!empty($params['target'])) {
			if ($params['target'] == 'Micro') $whereClause .= " and bm.BactMicroWorld_id = 1";
			else if ($params['target'] == 'Mushroom') $whereClause .= " and bm.BactMicroWorld_id = 2";
		}
		try {
			$query = "{$additional}
			select
				bm.BactMicro_id as \"BactMicro_id\",
				bm.BactMicro_Level as \"BactMicro_Level\",
				bm.BactMicro_pid as \"BactMicro_pid\",
				bm.BactMicro_Name as \"BactMicro_Name\",
				coalesce(bm.BactMicro_Synonym, '') as \"BactMicro_Synonym\",
				coalesce(bgc.BactGramColor_Name, '') as \"BactGramColor_Name\",
				bm.BactMicro_SNOMEDCT as \"BactMicro_SNOMEDCT\",
				bgc.BactGramColor_Code as \"BactGramColor_Code\",
				case
					WHEN (select count(BactMicro_id) from v_BactMicro where BactMicro_pid = bm.BactMicro_id) = 0 THEN 1
					else 0
				end as \"isLeaf\"
			from v_BactMicro bm
			left join v_BactMicroWorld bmw on bmw.BactMicroWorld_id = bm.BactMicroWorld_id
			left join v_BactGramColor bgc on bgc.BactGramColor_id = bm.BactGramColor_id
			where {$whereClause}
			order by BactMicro_Level DESC, BactMicro_Name ASC";
			return $this->queryResult($query, $params);
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}
	
	function getLabMicroList($params) {
		$whereClause = "1=1";
		
		if (!empty($params['MedService_id'])) $whereClause .= " and MedService_id = :MedService_id";
		if (!empty($params['BactMicro_id'])) $whereClause .= " and BactMicro_id in ($params[BactMicro_id])";
		
		try {
			$query = "select
				  BactMicroLab_id as \"BactMicroLab_id\"
				 ,MedService_id as \"MedService_id\"
				 ,BactMicro_id as \"BactMicro_id\"
				 ,BactMicro_id as \"BactMicro_id\"
				 ,pmUser_updID as \"pmUser_updID\"
				 ,BactMicroLab_insDT as \"BactMicroLab_insDT\"
				 ,BactMicroLab_updDT as \"BactMicroLab_updDT\"
			from v_BactMicroLab
			where {$whereClause}";
			return $this->queryResult($query, $params);
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}

	function getElementParentList($params) {
		$query = "
			with recursive rec (BactMicro_id, BactMicro_pid) as (
			select bm1.BactMicro_id, bm1.BactMicro_pid
			from v_BactMicro bm1
			where bm1.BactMicro_id = :BactMicro_id
			
			union all
			
			select bm2.BactMicro_id, bm2.BactMicro_pid
			from rec, v_BactMicro bm2
			where rec.BactMicro_pid = bm2.BactMicro_id
			)
			
			select BactMicro_id as \"BactMicro_id\" from rec";

		try {
			return $this->queryResult($query, $params);
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}
}
