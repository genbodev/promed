<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Соответствия конкретных ответов конкретному качественному тесту
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010-2014 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version
 */
class Organization_model extends SwPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Загрузка
	 */
	function load($data) {
		$q = "
			select
				Organization_id as \"Organization_id\",
				Organization_Code as \"Organization_Code\",
				Organization_Name as \"Organization_Name\",
				Org_id as \"Org_id\"
			from
				lis.v_Organization
			where
				Organization_id = :Organization_id
		";
		$r = $this->db->query($q, array('Organization_id' => $data['Organization_id']));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadList($data) {
		$where = array();
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		} else {
			$where_clause = '';
		}
		$q = "
			SELECT
				o.Organization_id as \"Organization_id\",
				o.Organization_Code as \"Organization_Code\",
				o.Organization_Name as \"Organization_Name\",
				o.Org_id as \"Org_id\",
				org.Org_Nick as \"Org_Nick\"
			FROM
				lis.v_Organization o
				left join v_Org org on org.Org_id = o.Org_id
			$where_clause
		";
		$result = $this->db->query($q, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Загрузка справочника организаций в ЛИС
	 */
	function loadLisOrganizationList($data) {
		$where = array();
		$where[] = "removed <> 'true'";
		if (!empty($data['Organization_id'])) {
			$where[] = "id = :Organization_id";
		}
		
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		} else {
			$where_clause = '';
		}
		$q = "
			SELECT
				id as \"Organization_id\",
				code as \"Organization_Code\",
				name as \"Organization_Name\"
			FROM
				lis._organization
			$where_clause
		";
		$result = $this->db->query($q, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение
	 */
	function save($data) {		
		$procedure = 'p_Organization_upd';
		if ($data['action'] == 'add') {
			$procedure = 'p_Organization_ins';
			
			// проверка на дубли
			$query = "
				select
					Organization_id as \"Organization_id\"
				from
					lis.v_Organization
				where
					Organization_id = :Organization_id
				limit 1
			";
			
			$result = $this->db->query($query, array(
				'Organization_id' => $data['Organization_id']
			));
			
			if ( is_object($result) ) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					return array('Error_Msg' => 'Указанная организация в ЛИС уже связана с МО');
				}
			}
		}
		
		$q = "
			select
				Organization_id as \"Organization_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from lis." . $procedure . " (
				Organization_id := :Organization_id,
				Organization_Code := :Organization_Code,
				Organization_Name := :Organization_Name,
				Org_id := :Org_id,
				pmUser_id := :pmUser_id
			)
		";
		$p = array(
			'Organization_id' => $data['Organization_id'],
			'Organization_Code' => $data['Organization_Code'],
			'Organization_Name' => $data['Organization_Name'],
			'Org_id' => $data['Org_id'],
			'pmUser_id' => $data['pmUser_id'],
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
			return $r->result('array');
		}

		return false;
	}

	/**
	 * Удаление
	 */
	function delete($data) {
		$q = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from lis.p_Organization_del (
				Organization_id := :Organization_id
			)
		";
		$r = $this->db->query($q, array(
			'Organization_id' => $data['Organization_id']
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}
}