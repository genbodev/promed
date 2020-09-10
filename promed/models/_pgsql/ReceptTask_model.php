<?php defined('BASEPATH') or die ('No direct script access allowed');

class ReceptTask_model extends swPgModel {
	var $schema = "dbo"; //региональная схема

	/**
	 *  Конструктор
	 */
	function __construct() {
		parent::__construct();

		//установка региональной схемы
		$config = get_config();
		$this->schema = $config['regions'][getRegionNumber()]['schema'];
	}

	/**
	 * Загрузка списка
	 */
	function loadList($data) {
		$params = array();
		$where = array();
		$where_clause = "";

		if(!empty($data['begDate'])) {
			$where[] = "(rt.ReceptTask_endDT is null or cast(rt.ReceptTask_endDT as date) >= cast(:begDate as date))";
			$params['begDate'] = $data['begDate'];
		}

		if(!empty($data['endDate'])) {
			$where[] = "cast(rt.ReceptTask_begDT as date) <= cast(:endDate as date)";
			$params['endDate'] = $data['endDate'];
		}

		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = "
				where
					{$where_clause}
			";
		}

		$query = "
			select
				rt.ReceptTask_id as \"ReceptTask_id\",
				to_char(rt.ReceptTask_begDT, 'dd.mm.yyyy HH24:MI:SS') as \"ReceptTask_begDT\",
				to_char(rt.ReceptTask_endDT, 'dd.mm.yyyy HH24:MI:SS') as \"ReceptTask_endDT\",
				rtt.ReceptTaskType_Code as \"ReceptTaskType_Code\",
				rtt.ReceptTaskType_Name as \"ReceptTaskType_Name\"
			from
				{$this->schema}.v_ReceptTask rt
				left join {$this->schema}.v_ReceptTaskType rtt on rtt.ReceptTaskType_id = rt.ReceptTaskType_id
			{$where_clause};
		";

		$result = $this->queryResult($query, $params);
		return $result;
	}

	/**
	 * Загрузка лога
	 */
	function loadReceptTaskLogList($data) {
		$query = "
			select
				rtl.ReceptTaskLog_id as \"ReceptTaskLog_id\",
				'' as \"ReceptTaskOperation_Name\",
				rtet.ReceptTaskErrorType_Name as \"ReceptTaskErrorType_Name\",
				coalesce(er.EvnRecept_Ser, '') as \"EvnRecept_Ser\",
				coalesce(er.EvnRecept_Num, '') as \"EvnRecept_Num\"
			from
				{$this->schema}.v_ReceptTaskLog rtl
				left join {$this->schema}.v_ReceptTaskErrorType rtet on rtet.ReceptTaskErrorType_id = rtl.ReceptTaskErrorType_id
				left join v_EvnRecept er on er.EvnRecept_id = rtl.EvnRecept_id
			where
				rtl.ReceptTask_id = :ReceptTask_id;
		";
		$result = $this->queryResult($query, $data);
		return $result;
	}
}
