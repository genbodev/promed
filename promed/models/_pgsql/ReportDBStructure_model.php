<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * PromedWeb
 *
 * Класс модели для отчета о стуктуре базы данных
 *
 * @package				Common
 * @copyright			Copyright (c) 2011 Swan Ltd.
 * @author				Salakhov Rustam 
 * @version				24.05.11
 *
 * @property CI_DB_driver $db
 */

class ReportDBStructure_model extends SwPgModel
{
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение списка таблиц
	 * @return array|bool
	 */
	function getTableList()
	{
		$query = "
			select
			    cl.OID::varchar(50) as object_id,
			    tbl.schemaname::varchar(255) as schema_name,
			    tbl.tablename::varchar(50) as table_name,
			    pg_catalog.obj_description(cl.OID)::varchar(255) as description
			from pg_catalog.pg_tables tbl
			    ,pg_catalog.pg_class cl
			where tbl.tablename = cl.relname
			  and schemaname <> 'pg_catalog'
			  and schemaname <> 'information_schema'
			  and schemaname <> 'tmp'
			order by tbl.schemaname, tbl.tablename
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query);
		return (is_object($result))?$result->result_array():false;
	}

	/**
	 * Получение структуры таблиц
	 * @param $object_id
	 * @return array|bool
	 */
	function getTableStructure($object_id)
	{
		/**@var CI_DB_result $result */
		$query = "
			select
			    columns.column_name::varchar(50) as name,
			    columns.data_type as type,
			    pg_catalog.col_description(cl.OID, columns.ordinal_position)::varchar(255) as description
			from
				information_schema.columns columns,
			    pg_catalog.pg_tables tbl,
			    pg_catalog.pg_class cl
			where tbl.tablename = cl.relname
			  and cl.OID = {$object_id}
			  and columns.table_schema = tbl.schemaname
			  and columns.table_name = tbl.tablename
		";
		$result = $this->db->query($query);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение части данных из таблиц
	 * @param $table_name
	 * @param array $params
	 * @return array|bool
	 */
	function getTableData($table_name, $params = ["max_row" => 10, "show_row" => 10])
	{
		/**@var CI_DB_result $result */
		//контроль максимального количества строк
		if ($params["max_row"] > 500) {
			$params["max_row"] = 500;
		}
		if ($params["show_row"] > 500) {
			$params["show_row"] = 500;
		}
		$count = 0;
		$top = $params["max_row"];
		if ($params["max_row"] != $params["show_row"]) {
			$query = "select count(*) as cnt from {$table_name}";
			$result = $this->db->query($query);
			if (is_object($result)) {
				$res = $result->result("array");
				$count = $res[0]["cnt"];
			}
			if ($count > $params["max_row"])
				$top = $params["show_row"];
		}
		$query = "
			select *
			from  {$table_name}
			limit {$top}
		";
		$result = $this->db->query($query);
		return (is_object($result)) ? $result->result_array() : false;
	}
}