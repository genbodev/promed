<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MedStaffFactLink_model - модель для работы со связками мест работы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Bykov Stanislav (savage@swan.perm.ru)
 * @version			08.10.2013
 */

class MedStaffFactLink_model extends SwPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Удаляет связку мест работы врача и среднего мед. персонала
	 */
	function deleteMedStaffFactLink($data) {
		$query = "
		    select 
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"

			from p_MedStaffFactLink_del(
				MedStaffFactLink_id := :MedStaffFactLink_id
			)
		";

		$params = array(
			'MedStaffFactLink_id' => $data['MedStaffFactLink_id']
		);

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Возвращает список связанных мест работы
	 */
	function loadMedStaffFactLinkGrid($data) {
		$query = "
			select
				 msfl.MedStaffFactLink_id as \"MedStaffFactLink_id\",
				 msfl.MedStaffFact_id as \"MedStaffFact_id\",
				 msfl.MedStaffFact_sid as \"MedStaffFact_sid\",
				 to_char (msfl.MedStaffFactLink_begDT, 'dd.mm.yyyy') as \"MedStaffFactLink_begDT\",
				 to_char (msfl.MedStaffFactLink_endDT, 'dd.mm.yyyy') as \"MedStaffFactLink_endDT\",
				 msf.Person_SurName as \"Person_SurName\",
				 msf.Person_FirName as \"Person_FirName\",
				 msf.Person_SecName as \"Person_SecName\"
			from
				v_MedStaffFactLink msfl
				inner join v_MedStaffFact msf on msf.MedStaffFact_id = msfl.MedStaffFact_sid
			where
				msfl.MedStaffFact_id = :MedStaffFact_id
			order by
				 msfl.MedStaffFactLink_begDT,
				 msfl.MedStaffFactLink_endDT
		";
		$result = $this->db->query($query, array('MedStaffFact_id' => $data['MedStaffFact_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Возвращает данные для редактировния связки мест работы
	 */
	function loadMedStaffFactLinkEditForm($data) {
		$query = "
			select
				 MedStaffFactLink_id as \"MedStaffFactLink_id\",
				 MedStaffFact_id as \"MedStaffFact_id\",
				 MedStaffFact_sid as \"MedStaffFact_sid\",
				 to_char (MedStaffFactLink_begDT, 'dd.mm.yyyy') as \"MedStaffFactLink_begDT\",
				 to_char (MedStaffFactLink_endDT, 'dd.mm.yyyy') as \"MedStaffFactLink_endDT\"
			from
				v_MedStaffFactLink
			where
				MedStaffFactLink_id = :MedStaffFactLink_id
		";
		$result = $this->db->query($query, array('MedStaffFactLink_id' => $data['MedStaffFactLink_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохраняет связку мест работы врача и среднего мед. персонала
	 */
	function saveMedStaffFactLink($data) {
		$params = array(
			 'MedStaffFactLink_id' => (!empty($data['MedStaffFactLink_id']) && $data['MedStaffFactLink_id'] > 0 ? $data['MedStaffFactLink_id'] : null)
			,'MedStaffFact_id' => $data['MedStaffFact_id']
			,'MedStaffFact_sid' => $data['MedStaffFact_sid']
			,'MedStaffFactLink_begDT' => $data['MedStaffFactLink_begDT']
			,'MedStaffFactLink_endDT' => $data['MedStaffFactLink_endDT']
			,'pmUser_id' => $data['pmUser_id']
		);
		/*
		// Проверяем на дубли
		$query = "
			select top 1 MedStaffFactLink_id
			from v_MedStaffFactLink with (nolock)
			where
				MedStaffFactLink_id != ISNULL(:MedStaffFactLink_id, 0)
				and MedStaffFact_id = :MedStaffFact_id
				and MedStaffFact_sid = :MedStaffFact_sid
				and ((
					MedStaffFactLink_begDT <= :MedStaffFactLink_begDT
					and (MedStaffFactLink_endDT is null or MedStaffFactLink_endDT > :MedStaffFactLink_begDT)
				)
		";

		if ( !empty($data['MedStaffFactLink_endDT']) ) {
			
		}
		else {
			$query .= ")";
		}

		$result = $this->db->query($query, $params);

		if ( !is_object($result) ) {
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (проверка пересечения периодов')));
		}

		$queryResponse = $result->result('array');

		if ( !is_array($queryResponse) ) {
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при проверке пересечения периодов')));
		}
		else if ( count($queryResponse) > 0 ) {
			return array(array('success' => false, 'Error_Msg' => 'Обнаружено пересечение периодов действия записей')));
		}
		*/
		// Сохраняем связку мест работы
		$query = "
			select 
			    MedStaffFactLink_id as \"MedStaffFactLink_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"

			from p_MedStaffFactLink_" . (!empty($data['MedStaffFactLink_id']) && $data['MedStaffFactLink_id'] > 0 ? "upd" : "ins") . "(
				MedStaffFactLink_id := :MedStaffFactLink_id,
				MedStaffFact_id := :MedStaffFact_id,
				MedStaffFact_sid := :MedStaffFact_sid,
				MedStaffFactLink_begDT := :MedStaffFactLink_begDT,
				MedStaffFactLink_endDT := :MedStaffFactLink_endDT,
				pmUser_id := :pmUser_id
			)

		";
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Возвращает список связанных мест работы для сессии
	 */
	function getMedStaffFactLinkList($data) {
		if (!empty($data['MedPersonal_sid'])) {
			$params = array('MedPersonal_sid' => $data['MedPersonal_sid']);

			$query = "
				select
					MSFL.MedStaffFactLink_id as \"MedStaffFactLink_id\",
					MSFL.MedStaffFact_id as \"MSFL.MedStaffFact_id\",
					MSFL.MedStaffFact_sid as \"MSFL.MedStaffFact_sid\",
					MSF.MedPersonal_id as \"MSF.MedPersonal_id\",
					to_char (MSFL.MedStaffFactLink_begDT, 'dd.mm.yyyy') as \"MedStaffFactLink_begDT\",
					to_char (MSFL.MedStaffFactLink_endDT, 'dd.mm.yyyy') as \"MedStaffFactLink_endDT\"
				from v_MedStaffFactLink MSFL
					inner join v_MedStaffFact MSF on MSF.MedStaffFact_id = MSFL.MedStaffFact_id
					inner join v_MedStaffFact sMSF on sMSF.MedStaffFact_id = MSFL.MedStaffFact_sid
				where
					sMSF.MedPersonal_id = :MedPersonal_sid
					and (MSFL.MedStaffFactLink_begDT <= dbo.tzGetDate() or MSFL.MedStaffFactLink_begDT is null)
					and (MSFL.MedStaffFactLink_endDT > dbo.tzGetDate() or MSFL.MedStaffFactLink_endDT is null)
			";
			$result = $this->db->query($query, $params);

			if ( is_object($result) ) {
				return $result->result('array');
			}
		}
		return array();
	}
}