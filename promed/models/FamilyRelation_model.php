<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * FamilyRelation_model - модель для работы с родственнми связями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 * @author			Aleksandr Chebukin
 * @version			18.11.2016
 */

class FamilyRelation_model extends swModel {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Удаление связи
	 */
	function delete($data) {

		$query = "
			declare
				@Error_Code int,
				@Error_Msg varchar(4000);

			exec p_FamilyRelation_del
				@FamilyRelation_id = :FamilyRelation_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;

			select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";

		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Возвращает список связей	
	 */
	function loadList($data) {
	
		$params = array();
		$params['Person_id'] = $data['Person_id'];

		$query = "
			select
				-- select
				FR.FamilyRelation_id,
				FR.Person_id,
				FR.Person_cid,
				FR.FamilyRelationType_id,
				FRT.FamilyRelationType_Name,
				PS.Person_SurName as FamilyRelation_SurName,
				PS.Person_FirName as FamilyRelation_FirName,
				PS.Person_SecName as FamilyRelation_SecName,
				convert(varchar(10), PS.Person_BirthDay, 104) as FamilyRelation_BirthDay,
				convert(varchar(10), FR.FamilyRelation_begDate, 104) as FamilyRelation_begDate,
				convert(varchar(10), FR.FamilyRelation_endDate, 104) as FamilyRelation_endDate
				-- end select
			from
				-- from
				v_FamilyRelation FR with(nolock)
				inner join v_PersonState PS with(nolock) on PS.Person_id = FR.Person_cid
				inner join v_FamilyRelationType FRT with(nolock) on FRT.FamilyRelationType_id = FR.FamilyRelationType_id
				-- end from
			where
				-- where
				FR.Person_id = :Person_id
				-- end where
		";

		//echo getDebugSQL($query, $params);die;
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Возвращает связь
	 */
	function load($data) {

		$query = "
			select
				-- select
				FR.FamilyRelation_id
				,FR.Person_id
				,FR.Person_cid
				,PS.Person_SurName + ' ' + PS.Person_FirName + ' ' + PS.Person_SecName as Person_cid_Fio
				,FR.FamilyRelationType_id
				,convert(varchar(10), FR.FamilyRelation_begDate, 104) as FamilyRelation_begDate
				,convert(varchar(10), FR.FamilyRelation_endDate, 104) as FamilyRelation_endDate
				-- end select
			from
				-- from
				v_FamilyRelation FR with(nolock)
				inner join v_PersonState PS with(nolock) on PS.Person_id = FR.Person_cid
				-- end from
			where
				-- where
				FR.FamilyRelation_id = :FamilyRelation_id
				-- end where
		";

		//echo getDebugSQL($query, $params);die;
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет связь
	 */
	function save($data) {

		$params = array(
			'FamilyRelation_id' => empty($data['FamilyRelation_id']) ? null : $data['FamilyRelation_id'],
			'Person_id' => $data['Person_id'],
			'Person_cid' => $data['Person_cid'],
			'FamilyRelationType_id' => $data['FamilyRelationType_id'],
			'FamilyRelation_begDate' => $data['FamilyRelation_begDate'] ?: null,
			'FamilyRelation_endDate' => $data['FamilyRelation_endDate'] ?: null,
			'pmUser_id' => $data['pmUser_id']
		);

		$procedure = empty($params['FamilyRelation_id']) ? 'p_FamilyRelation_ins' : 'p_FamilyRelation_upd';

		$query = "
			declare
				@FamilyRelation_id bigint = :FamilyRelation_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@FamilyRelation_id = @FamilyRelation_id output,
				@Person_id = :Person_id,
				@Person_cid = :Person_cid,
				@FamilyRelationType_id = :FamilyRelationType_id,
				@FamilyRelation_begDate = :FamilyRelation_begDate,
				@FamilyRelation_endDate = :FamilyRelation_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @FamilyRelation_id as FamilyRelation_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

}