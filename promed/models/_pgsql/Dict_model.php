<?php
/**
 * Dict - получение справочников для обертки их в SOAP для взаимодействия со
 * сторонним ПО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2009 Swan Ltd.
 * @author			Ivan Petukhov aka Lich (ethereallich@gmail.com)
 * @version			1
 */

class Dict_model extends SwPgModel
{
	/**
	 * Comment
	 */
	function SprLoader_model()
	{
		parent::__construct();
	}
	
	
	/**
	 * Получение списка справочников
	 */
	function dictList($data) {
		$query ="
			select
				name as \"name\",
				value as \"value\"
	        from (
                select t.table_name as name,
                obj_description((t.Table_schema||'.'||t.table_name)::regclass) as value,
                0 ordinal_position from dbo.LocalObjectType t
            union
                select 
                    t.table_name,
                    col_description((t.Table_schema||'.'||t.table_name)::regclass, ordinal_position),
                    ordinal_position from information_schema.\"columns\" c 
                    inner join dbo.LocalObjectType t on lower(t.Table_schema)=c.Table_schema and lower(t.Table_Name)=c.Table_Name
                ) t
                where value is not null
                order by name,ordinal_position
";

		$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	
	/**
	 * Получение содержимого справочника
	 */
   function dictContent($data) {
		$dict = $this->checkDictExists($data);
		if ($dict !== false) {
			$query = "
				select
					{$data['name']}_id as \"id\",
					{$data['name']}_code as \"code\",
					{$data['name']}_name as \"name\",
					to_char({$data['name']}_updDT, 'yyyy-mm-dd hh:mi:ss' ) as \"upd_dt\"
				from v_{$data['name']}
			";

			$result = $this->db->query($query);

			if ( is_object($result) ) {
				$res['data'] = $result->result('array');
				$res['desc'] = $dict['value'];
				return $res;
			}
			else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	
	/**
	 * Проверка существует ли заданный справочник
	 * Возвращает false если нет, или массив с названием и описанием если да
	 */
    function checkDictExists($data) {
		$params = array();

		$params['DictName'] = $data['name'];

        $query ="
			select
				name as \"name\",
				value as \"value\"
	        from (
                select t.table_name as name,
                obj_description((t.Table_schema||'.'||t.table_name)::regclass) as value,
                0 ordinal_position from dbo.LocalObjectType t
            union
                select
                    t.table_name,
                    col_description((t.Table_schema||'.'||t.table_name)::regclass, ordinal_position),
                    ordinal_position from information_schema.\"columns\" c
                    inner join dbo.LocalObjectType t on lower(t.Table_schema)=c.Table_schema and lower(t.Table_Name)=c.Table_Name
                ) t
                where
                    value is not null and
                    name = :DictName
                order by name,ordinal_position
";

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			$res = $result->result('array');
			if (count($res) > 0) {
				$res = $result->result('array');
				return $res[0];
			} else {
				return false;
			}
		}
		else {
			return false;
		}
	}
}
?>