<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb
 *
 * Класс модели для отчета о стуктуре базы данных
 *
 * The New Generation of Medical Statistic Software
 *
 * @package				Common
 * @copyright			Copyright (c) 2011 Swan Ltd.
 * @author				Salakhov Rustam 
 * @version				24.05.11
 */

class ReportDBStructure_model extends CI_Model {

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение списка таблиц
	 */
	function getTableList() {
		$query = "
			select 
				cast(object_id as varchar(50)) as object_id,
				cast(sh.name as varchar(255)) as schema_name,
				cast(tbl.name as varchar(50)) as table_name,
				cast(pr.value as varchar(255)) as description
			from 
				sys.tables tbl with(nolock)
				left join sys.schemas sh with(nolock) on sh.schema_id = tbl.schema_id
				outer apply (
					SELECT top 1 value
					FROM   ::fn_listextendedproperty (NULL, 'user', 'dbo', 'table', tbl.name, null, null)
					where name = 'MS_Description'
				) as pr
			where 
				type = 'U' and 
				tbl.schema_id not in (schema_id('tmp')) 
			order by
				sh.name, tbl.name";
				
		$result = $this->db->query($query, array());
		
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Получение структуры таблиц
	 */
	function getTableStructure($object_id) {
		$query = "
			select
				cast(sys.columns.name as varchar(50)) as name,
				(case 
					when sys.columns.is_computed=1 then 'is computed'
					else (rtrim(ltrim(sys.types.name)))
				end) +
				(case 
					when sys.columns.is_computed=1 then ''
					when rtrim(ltrim(sys.types.name)) in ('char','varchar','varbinary') then case when sys.columns.max_length=-1 then '(max)' else '('+rtrim(convert(char, sys.columns.max_length))+')' end
					when rtrim(ltrim(sys.types.name)) in ('nchar','nvarchar') then '('+rtrim(convert(char, sys.columns.max_length/2))+')'
					when rtrim(ltrim(sys.types.name)) in ('binary') then '('+rtrim(convert(char, sys.columns.max_length))+')'
					when rtrim(ltrim(sys.types.name)) in ('decimal','numeric') then '('+rtrim(convert(char, sys.columns.precision))+','+rtrim(convert(char, sys.columns.scale))+')'
					when rtrim(ltrim(sys.types.name)) in ('tinyint','smallint','int','bigint','bit','float','datetme','smalldatetime','timestamp','uniqueidentifier','image','money','smallmoney','real','text','ntext','sql_variant','xml') then ''	
					else ''
				end) + '' as type,
				cast(pr.value as varchar(255)) as description
			from
				sys.columns with(nolock)
				left join sys.tables with(nolock) on sys.tables.object_id = sys.columns.object_id
				left join sys.types with(nolock) on sys.columns.system_type_id=
					case 
						 when sys.types.user_type_id!=sys.types.system_type_id
						 then sys.types.user_type_id
						 else sys.types.system_type_id
					end
				outer apply (
					SELECT top 1 value
					FROM   ::fn_listextendedproperty (NULL, 'user', 'dbo', 'table', sys.tables.name, 'column', sys.columns.name)
					where name = 'MS_Description'
				) as pr
			where
				sys.columns.object_id = ".$object_id;
				
		$result = $this->db->query($query, array());
		
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Получение части данных из таблиц
	 */
	function getTableData($table_name, $params = array('max_row' => 10, 'show_row' => 10)) {
		//контроль максимального количества строк
		if ($params['max_row'] > 500) $params['max_row'] = 500;
		if ($params['show_row'] > 500) $params['show_row'] = 500;
	
		$count = 0;
		$top = $params['max_row'];
		
		if ($params['max_row'] != $params['show_row']) {
			$query = "select count(*) as cnt from ".$table_name;
			$result = $this->db->query($query, array());
			if ( is_object($result) ) {
				$res = $result->result('array');
				$count = $res[0]['cnt'];
			}
			if ($count > $params['max_row'])
				$top = $params['show_row'];
		}
	
		$query = "
			select top ".$top."
				*
			from 
				".$table_name;
				
		$result = $this->db->query($query, array());
		
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
}