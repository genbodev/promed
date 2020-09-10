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

class FamilyRelation_model extends SwPgModel {

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
		SELECT
		error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        p_FamilyRelation_del(
          FamilyRelation_id => :FamilyRelation_id
        )
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
				FR.FamilyRelation_id as \"FamilyRelation_id\",
				FR.Person_id as \"Person_id\",
				FR.Person_cid as \"Person_cid\",
				FR.FamilyRelationType_id as \"FamilyRelationType_id\",
				FRT.FamilyRelationType_Name as \"FamilyRelationType_Name\",
				PS.Person_SurName as \"FamilyRelation_SurName\",
				PS.Person_FirName as \"FamilyRelation_FirName\",
				PS.Person_SecName as \"FamilyRelation_SecName\",
				to_char(PS.Person_BirthDay, 'DD.MM.YYYY') as \"FamilyRelation_BirthDay\",
				to_char(FR.FamilyRelation_begDate, 'DD.MM.YYYY') as \"FamilyRelation_begDate\",
				to_char(FR.FamilyRelation_endDate, 'DD.MM.YYYY') as \"FamilyRelation_endDate\"
				-- end select
			from
				-- from
				v_FamilyRelation FR 

				inner join v_PersonState PS  on PS.Person_id = FR.Person_cid

				inner join v_FamilyRelationType FRT  on FRT.FamilyRelationType_id = FR.FamilyRelationType_id

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
				FR.FamilyRelation_id as \"FamilyRelation_id\"
				,FR.Person_id as \"Person_id\"
				,FR.Person_cid as \"Person_cid\" 
				,PS.Person_SurName || ' ' || PS.Person_FirName || ' ' || PS.Person_SecName as \"Person_cid_Fio\"
				,FR.FamilyRelationType_id as \"FamilyRelationType_id\"
				,to_char(FR.FamilyRelation_begDate, 'DD.MM.YYYY') as \"FamilyRelation_begDate\"
				,to_char(FR.FamilyRelation_endDate, 'DD.MM.YYYY') as \"FamilyRelation_endDate\"

				-- end select
			from
				-- from
				v_FamilyRelation FR 

				inner join v_PersonState PS  on PS.Person_id = FR.Person_cid

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
		SELECT
		FamilyRelation_id as \"FamilyRelation_id\",
		error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        {$procedure}(
                FamilyRelation_id => :FamilyRelation_id,
				Person_id => :Person_id,
				Person_cid => :Person_cid,
				FamilyRelationType_id => :FamilyRelationType_id,
				FamilyRelation_begDate => :FamilyRelation_begDate,
				FamilyRelation_endDate => :FamilyRelation_endDate,
				pmUser_id => :pmUser_id
        )
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

}