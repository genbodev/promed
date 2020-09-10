<?php defined('BASEPATH') or die ('No direct script access allowed');

class ReceptTask_model extends swModel {
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
			$where[] = "(rt.ReceptTask_endDT is null or cast(rt.ReceptTask_endDT as date) >= :begDate)";
			$params['begDate'] = $data['begDate'];
		}

		if(!empty($data['endDate'])) {
			$where[] = "cast(rt.ReceptTask_begDT as date) <= :endDate";
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
				rt.ReceptTask_id,
				convert(varchar(10), rt.ReceptTask_begDT, 104)+' '+convert(varchar(10), rt.ReceptTask_begDT, 108) as ReceptTask_begDT,
				convert(varchar(10), rt.ReceptTask_endDT, 104)+' '+convert(varchar(10), rt.ReceptTask_endDT, 108) as ReceptTask_endDT,
				rtt.ReceptTaskType_Code,
				rtt.ReceptTaskType_Name
			from
				{$this->schema}.v_ReceptTask rt with (nolock)				
				left join {$this->schema}.v_ReceptTaskType rtt with (nolock) on rtt.ReceptTaskType_id = rt.ReceptTaskType_id	
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
				rtl.ReceptTaskLog_id,
				'' as ReceptTaskOperation_Name,
				rtet.ReceptTaskErrorType_Name,
				isnull(er.EvnRecept_Ser, '') as EvnRecept_Ser,
				isnull(er.EvnRecept_Num, '') as EvnRecept_Num
			from
				{$this->schema}.v_ReceptTaskLog rtl with (nolock)				
				left join {$this->schema}.v_ReceptTaskErrorType rtet with (nolock) on rtet.ReceptTaskErrorType_id = rtl.ReceptTaskErrorType_id
				left join v_EvnRecept er with (nolock) on er.EvnRecept_id = rtl.EvnRecept_id
			where
				rtl.ReceptTask_id = :ReceptTask_id;
		";
		$result = $this->queryResult($query, $data);
		return $result;
	}
}
