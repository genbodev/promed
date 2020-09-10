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

class Dict_model extends CI_Model
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
		$query = "
			select
				cast(so.name as varchar(100)) as name,
				cast(sept.value as varchar(100)) as value
			from sys.objects so with(nolock)
			inner join sys.extended_properties sep with(nolock) on 
				so.object_id = sep.major_id
				and sep.minor_id = 0
			cross apply (select value from sys.extended_properties with(nolock) where major_id = sep.major_id and name = 'MS_Description') sept
			where
				type = 'U'
				and sep.name = 'sw_objectType' 
				and sep.value = 'dict'
			order by so.name";
		
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
					{$data['name']}_id as id,
					{$data['name']}_code as code,
					{$data['name']}_name as name,
					convert(varchar, {$data['name']}_updDT, 121 ) as upd_dt
				from v_{$data['name']} with (nolock)
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
		
		$query = "
			select
				cast(so.name as varchar(100)) as name,
				cast(sept.value as varchar(100)) as value
			from sys.objects so with(nolock)
			inner join sys.extended_properties sep with(nolock) on 
				so.object_id = sep.major_id
				and sep.minor_id = 0
			cross apply (select value from sys.extended_properties with(nolock) where major_id = sep.major_id and name = 'MS_Description') sept
			where
				type = 'U'
				and sep.name = 'sw_objectType' 
				and sep.value = 'dict'
				and so.name = :DictName
			order by so.name";
		
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