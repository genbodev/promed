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
class Organization_model extends swModel {
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
				Organization_id,
				Organization_Code,
				Organization_Name,
				Org_id
			from
				lis.v_Organization (nolock)
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
				o.Organization_id,
				o.Organization_Code,
				o.Organization_Name,
				o.Org_id,
				org.Org_Nick
			FROM
				lis.v_Organization o (nolock)
				left join v_Org org (nolock) on org.Org_id = o.Org_id
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
				id as Organization_id,
				code as Organization_Code,
				name as Organization_Name
			FROM
				lis._organization (nolock)
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
				select top 1
					Organization_id
				from
					lis.v_Organization (nolock)
				where
					Organization_id = :Organization_id
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
			declare
				@Organization_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Organization_id = :Organization_id;
			exec lis." . $procedure . "
				@Organization_id = @Organization_id output,
				@Organization_Code = :Organization_Code,
				@Organization_Name = :Organization_Name,
				@Org_id = :Org_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Organization_id as Organization_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec lis.p_Organization_del
				@Organization_id = :Organization_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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