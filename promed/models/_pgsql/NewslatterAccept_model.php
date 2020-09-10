<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * NewslatterAccept_model - модель для работы с согласиями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 * @author			Aleksandr Chebukin
 * @version			16.12.2015
 */

class NewslatterAccept_model extends SwPgModel {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Удаление согласия
	 */
	function delete($data) {

		$query = "
			select PersonNewslatter_id as \"PersonNewslatter_id\"
			from v_PersonNewslatter
			where NewslatterAccept_id = :NewslatterAccept_id
		";

		$result = $this->db->query($query, array('NewslatterAccept_id' => $data['NewslatterAccept_id']));
		if (is_object($result)) {
			$data['Newslatter_ids'] = $result->result('array');
		} else {
			return false;
		}

		foreach ($data['Newslatter_ids'] as $id) {
	
        $query = "
            Select  Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
            From p_PersonNewslatter_del(PersonNewslatter_id := :PersonNewslatter_id)
        ";

			$result = $this->db->query($query, array('PersonNewslatter_id' => $id['PersonNewslatter_id']));
			if (!is_object($result)) {
				return false;
			}
		}

        $query = "
            Select  Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
            From p_NewslatterAccept_del(NewslatterAccept_id := :NewslatterAccept_id)
        ";

		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Возвращает список согласий
	 */
	function loadList($data) {

		$query = "
			select
				NA.NewslatterAccept_id as \"NewslatterAccept_id\"
				,NA.Lpu_id as \"Lpu_id\"
				,Lpu.Lpu_Nick as \"Lpu_Nick\"
				,NA.NewslatterAccept_Phone as \"NewslatterAccept_Phone\"
				,CASE WHEN COALESCE(NA.NewslatterAccept_IsSMS, 1) = 1 THEN 'false' else 'true' END as \"NewslatterAccept_IsSMS\"
				,NA.NewslatterAccept_Email as \"NewslatterAccept_Email\"
				,CASE WHEN COALESCE(NA.NewslatterAccept_IsEmail, 1) = 1 THEN 'false' else 'true' END as \"NewslatterAccept_IsEmail\"
				,TO_CHAR(NA.NewslatterAccept_begDate,'DD.MM.YYYY') as \"NewslatterAccept_begDate\"
				,TO_CHAR( NA.NewslatterAccept_endDate,'DD.MM.YYYY') as \"NewslatterAccept_endDate\"
			from
				v_NewslatterAccept NA
				inner join v_Lpu Lpu on Lpu.Lpu_id = NA.Lpu_id
			where
				NA.Person_id = :Person_id
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
	 * Возвращает согласие
	 */
	function load($data) {

		$query = "
			select
				NA.NewslatterAccept_id as \"NewslatterAccept_id\"
				,NA.Lpu_id as \"Lpu_id\"
				,NA.Person_id as \"Person_id\"
				,NA.NewslatterAccept_Phone as \"NewslatterAccept_Phone\"
				,CASE WHEN COALESCE(NA.NewslatterAccept_IsSMS, 1) = 1 THEN 'false' else 'true' END as \"NewslatterAccept_IsSMS\"
				,NA.NewslatterAccept_Email as \"NewslatterAccept_Email\"
				,CASE WHEN COALESCE(NA.NewslatterAccept_IsEmail, 1) = 1 THEN 'false' else 'true' END as \"NewslatterAccept_IsEmail\"
				,TO_CHAR(NA.NewslatterAccept_begDate,'DD.MM.YYYY') as \"NewslatterAccept_begDate\"
				,TO_CHAR(NA.NewslatterAccept_endDate,'DD.MM.YYYY') as \"NewslatterAccept_endDate\"
			from
				v_NewslatterAccept NA
			where
				NA.NewslatterAccept_id = :NewslatterAccept_id
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
	 * Проверка наличия активного согласия на рассылку
	 */
	function check($data) {

		$query = "
			select
				NA.NewslatterAccept_id as \"NewslatterAccept_id\"
				,NA.Person_id as \"Person_id\"
				,CASE WHEN COALESCE(NA.NewslatterAccept_IsSMS, 1) = 1 THEN '1' else '2' END as \"NewslatterAccept_IsSMS\"
				,CASE WHEN COALESCE(NA.NewslatterAccept_IsEmail, 1) = 1 THEN '1' else '2' END as \"NewslatterAccept_IsEmail\"
			from
				v_NewslatterAccept NA
			where
				NA.Person_id = :Person_id and
				NA.Lpu_id = :Lpu_id and
				NA.NewslatterAccept_endDate is null
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
	 * Сохраняет согласие
	 */
	function save($data) {

		$params = array(
			'NewslatterAccept_id' => empty($data['NewslatterAccept_id']) ? null : $data['NewslatterAccept_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Person_id' => $data['Person_id'],
			'NewslatterAccept_Phone' => str_replace(array('(',')',' '), '', $data['NewslatterAccept_Phone']),
			'NewslatterAccept_IsSMS' => (isset($data['NewslatterAccept_IsSMS']) && $data['NewslatterAccept_IsSMS'] > 0) ? 2 : 1,
			'NewslatterAccept_Email' => $data['NewslatterAccept_Email'],
			'NewslatterAccept_IsEmail' => (isset($data['NewslatterAccept_IsEmail']) && $data['NewslatterAccept_IsEmail'] > 0) ? 2 : 1,
			'NewslatterAccept_begDate' => $data['NewslatterAccept_begDate'] ?: null,
			'NewslatterAccept_endDate' => $data['NewslatterAccept_endDate'] ?: null,
			'pmUser_id' => $data['pmUser_id']
		);

		$procedure = empty($params['NewslatterAccept_id']) ? 'p_NewslatterAccept_ins' : 'p_NewslatterAccept_upd';


        $query = "
            Select  NewslatterAccept_id as \"NewslatterAccept_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
            From {$procedure}( 
				NewslatterAccept_id := :NewslatterAccept_id,
				Lpu_id := :Lpu_id,
				Person_id := :Person_id,
				NewslatterAccept_Phone := :NewslatterAccept_Phone,
				NewslatterAccept_IsSMS := :NewslatterAccept_IsSMS,
				NewslatterAccept_Email := :NewslatterAccept_Email,
				NewslatterAccept_IsEmail := :NewslatterAccept_IsEmail,
				NewslatterAccept_begDate := :NewslatterAccept_begDate,
				NewslatterAccept_endDate := :NewslatterAccept_endDate,
				pmUser_id := :pmUser_id
            )
        ";


		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}




	/**
	 * Проверка согласий на дубли
	 */
	function checkNewslatterAcceptDoubles ($data) {

		$params = array(
			'NewslatterAccept_id' => empty($data['NewslatterAccept_id']) ? null : $data['NewslatterAccept_id'],
			'Lpu_id' => empty($data['Lpu_id']) ? null : $data['Lpu_id'],
			'Person_id' => empty($data['Lpu_id']) ? null : $data['Person_id'],
			'NewslatterAccept_begDate' => $data['NewslatterAccept_begDate'] ?: null,
			'NewslatterAccept_endDate' => $data['NewslatterAccept_endDate'] ?: null,
		);

		$query = "
			select
				NA.NewslatterAccept_id as \"NewslatterAccept_id\"
			from
				v_NewslatterAccept NA
			where
				NA.NewslatterAccept_id != COALESCE(:NewslatterAccept_id::NUMERIC, 0) and
				NA.Lpu_id = :Lpu_id and
				NA.Person_id = :Person_id and
				(
					:NewslatterAccept_begDate BETWEEN NewslatterAccept_begDate AND NewslatterAccept_endDate OR
					:NewslatterAccept_endDate BETWEEN NewslatterAccept_begDate AND NewslatterAccept_endDate OR
					NewslatterAccept_endDate IS NULL
				)
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Печать согласия
	 */
	function printAccept($data) {

		$query = "
			select
				NA.NewslatterAccept_id as \"NewslatterAccept_id\"
				,PS.Person_SurName as \"Person_SurName\"
				,PS.Person_FirName as \"Person_FirName\"
				,PS.Person_SecName as \"Person_SecName\"
				,RTRIM(COALESCE(UAddr.Address_Address, '')) as \"UAddress_Name\"
				,COALESCE(TO_CHAR(Document.Document_begDate,'DD.MM.YYYY'), '') as \"Document_begDate\"
				,RTRIM(COALESCE(Document.Document_Num, '')) as \"Document_Num\"
				,RTRIM(COALESCE(Document.Document_Ser, '')) as \"Document_Ser\"
				,RTRIM(COALESCE(DocumentType.DocumentType_Name, '')) as \"DocumentType_Name\"
				,RTRIM(COALESCE(OrgDep.OrgDep_Name, '')) as \"OrgDep_Name\"
				,NA.NewslatterAccept_Phone as \"NewslatterAccept_Phone\"
				,CASE WHEN COALESCE(NA.NewslatterAccept_IsSMS, 1) = 1 THEN 'false' else 'true' END as \"NewslatterAccept_IsSMS\"
				,NA.NewslatterAccept_Email as \"NewslatterAccept_Email\"
				,CASE WHEN COALESCE(NA.NewslatterAccept_IsEmail, 1) = 1 THEN 'false' else 'true' END as \"NewslatterAccept_IsEmail\"
				,Lpu.Lpu_Name as \"Lpu_Name\"
				,Lpu.UAddress_Address as \"UAddress_Address\"
			from
				v_NewslatterAccept NA
				inner join v_PersonState PS  on PS.Person_id = NA.Person_id
				left join v_Address UAddr  on UAddr.Address_id = PS.UAddress_id
				left join v_Document Document  on Document.Document_id = PS.Document_id
				left join v_DocumentType DocumentType  on DocumentType.DocumentType_id = Document.DocumentType_id
				left join v_OrgDep OrgDep on OrgDep.OrgDep_id = Document.OrgDep_id
				left join v_Lpu Lpu  on Lpu.Lpu_id = NA.Lpu_id
			where
				NA.NewslatterAccept_id = :NewslatterAccept_id
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
	 * Печать отказа
	 */
	function printDenial($data) {

		// Пока данные идентичны
		return $this->printAccept($data);
	}

}
