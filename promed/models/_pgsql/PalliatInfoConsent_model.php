<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PostgreSQL
 * PaidService - модель для работы с согласиями на паллиативное лечение
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Person
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 */
class PalliatInfoConsent_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Удаление согласия
	 */
	function deletePalliatInfoConsent($data) {
	
		$query = "
		Select  Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
        From p_PalliatInfoConsent_del(PalliatInfoConsent_id:= :PalliatInfoConsent_id)
        ";

		
		$result = $this->db->query($query, array(
			'PalliatInfoConsent_id' => $data['PalliatInfoConsent_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных по согласию на паллиативную помощь для ЭМК
	 */
	function getPalliatInfoConsentViewData($data) {
		$query = "
			select
				pi.PalliatInfoConsent_id as \"PalliatInfoConsent_id\",
				TO_CHAR(pi.PalliatInfoConsent_consDT,'DD.MM.YYYY') as \"PalliatInfoConsent_consDT\",
				pict.PalliatInfoConsentType_Name as \"PalliatInfoConsentType_Name\",
				msf.Person_Fio as \"MedPersonal_Fio\",
				l.Lpu_Nick as \"Lpu_Nick\",
				pi.Person_id as \"Person_id\",
				pi.pmUser_insID as \"pmUser_insID\"
			from
				v_PalliatInfoConsent pi
				left join v_MedStaffFact msf on msf.MedStaffFact_id = pi.MedStaffFact_id
				left join v_PalliatInfoConsentType pict on pict.PalliatInfoConsentType_id = pi.PalliatInfoConsentType_id
				left join v_Lpu l on l.Lpu_id = msf.Lpu_id
			where
				pi.Person_id = :Person_id
			order by
				pi.PalliatInfoConsent_consDT desc
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * Загрузка формы редактирования
	 */
	function loadPalliatInfoConsentEditForm($data) {
		$query = "
			select 
				PalliatInfoConsent_id as \"PalliatInfoConsent_id\",
				PalliatInfoConsentType_id as \"PalliatInfoConsentType_id\",
				TO_CHAR(PalliatInfoConsent_consDT,'DD.MM.YYYY') as \"PalliatInfoConsent_consDT\",
				PalliatInfoConsent_isSelf as \"PalliatInfoConsent_isSelf\",
				MedStaffFact_id as \"MedStaffFact_id\",
				Person_id as \"Person_id\"
			from
				v_PalliatInfoConsent 
			where
				PalliatInfoConsent_id = :PalliatInfoConsent_id
			LIMIT 1
		";

		$resp = $this->queryResult($query, array(
			'PalliatInfoConsent_id' => $data['PalliatInfoConsent_id']
		));

		if (!empty($resp[0]['PalliatInfoConsent_id'])) {
			$resp[0]['PalliatMedCareTypeLinkData'] = array();
			// выбираем отказы от мероприятий
			$resp_pmct = $this->queryResult("
				select
					PalliatMedCareType_id as \"PalliatMedCareType_id\"
				from
					v_PalliatMedCareTypeLink 
				where
					PalliatInfoConsent_id = :PalliatInfoConsent_id
			", array(
				'PalliatInfoConsent_id' => $resp[0]['PalliatInfoConsent_id']
			));

			foreach($resp_pmct as $one_pmct) {
				$resp[0]['PalliatMedCareTypeLinkData'][] = $one_pmct['PalliatMedCareType_id'];
			}
		}

		return $resp;
	}

	/**
	 * Сохраненние
	 */
	function savePalliatInfoConsent($data) {
		$procedure = '';

		if ( (!isset($data['PalliatInfoConsent_id'])) || ($data['PalliatInfoConsent_id'] <= 0) ) {
			$procedure = 'p_PalliatInfoConsent_ins';
		}
		else {
			$procedure = 'p_PalliatInfoConsent_upd';
		}

		if (empty($data['PalliatMedCareTypeLinkData'])) {
			$data['PalliatMedCareTypeLinkData'] = array();

			if (in_array($data['PalliatInfoConsentType_id'], array(2,3))) {
				$data['PalliatInfoConsentType_id'] = 2; // полное согласие
			}
		} else {
			if (in_array($data['PalliatInfoConsentType_id'], array(2,3))) {
				$data['PalliatInfoConsentType_id'] = 3; // частичное согласие
			}
		}



        $query = "
            Select  PalliatInfoConsent_id as \"PalliatInfoConsent_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
            From {$procedure}
            (
            	PalliatInfoConsent_id := :PalliatInfoConsent_id,
                PalliatInfoConsentType_id := :PalliatInfoConsentType_id,
				PalliatInfoConsent_consDT := cast(:PalliatInfoConsent_consDT as date),
				PalliatInfoConsent_isSelf := :PalliatInfoConsent_isSelf,
				MedStaffFact_id := :MedStaffFact_id,
				Person_id := :Person_id,
				pmUser_id := :pmUser_id
            )
        ";



		$queryParams = array(
			'PalliatInfoConsent_id' => $data['PalliatInfoConsent_id'],
			'PalliatInfoConsentType_id' => $data['PalliatInfoConsentType_id'],
			'PalliatInfoConsent_consDT' => $data['PalliatInfoConsent_consDT'],
			'PalliatInfoConsent_isSelf' => $data['PalliatInfoConsent_isSelf'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$resp = $this->queryResult($query, $queryParams);

		if (!empty($resp[0]['PalliatInfoConsent_id'])) {
			$resp[0]['PalliatInfoConsentType_id'] = $data['PalliatInfoConsentType_id'];

			// достаем все уже сохраненные мероприятия
			$toDel = array();
			$resp_pmct = $this->queryResult("
				select
					PalliatMedCareTypeLink_id as \"PalliatMedCareTypeLink_id\",
					PalliatMedCareType_id as \"PalliatMedCareType_id\"
				from
					v_PalliatMedCareTypeLink 
				where
					PalliatInfoConsent_id = :PalliatInfoConsent_id
			", array(
				'PalliatInfoConsent_id' => $resp[0]['PalliatInfoConsent_id']
			));

			$savedArray = array();
			foreach($resp_pmct as $one_pmct) {
				if (!in_array($one_pmct['PalliatMedCareType_id'], $data['PalliatMedCareTypeLinkData'])) {
					$toDel[] = $one_pmct;
				}

				$savedArray[] = $one_pmct['PalliatMedCareType_id'];
			}

			// удаляем те, что не нужны
			foreach($toDel as $item) {
				$this->queryResult(
				"
				Select  Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
                From p_PalliatMedCareTypeLink_del(PalliatMedCareTypeLink_id := :PalliatMedCareTypeLink_id)
                "
				, array(
					'PalliatMedCareTypeLink_id' => $item['PalliatMedCareTypeLink_id']
				));
			}

			$toSave = array();
			foreach($data['PalliatMedCareTypeLinkData'] as $one) {
				if (!in_array($one, $savedArray)) {
					$toSave[] = $one;
				}
			}

			// сохраняем новые
			foreach($toSave as $PalliatMedCareType_id) {
				$this->queryResult(
				"
				select PalliatMedCareTypeLink_id as \"PalliatMedCareTypeLink_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
                from 	p_PalliatMedCareTypeLink_ins
                    (
                        PalliatMedCareTypeLink_id:= null, 
                        PalliatMedCareType_id:= :PalliatMedCareType_id,
                        PalliatInfoConsent_id := :PalliatInfoConsent_id,
                        pmUser_id := :pmUser_id
                    )
                "                                   
				, array(
					'PalliatInfoConsent_id' => $resp[0]['PalliatInfoConsent_id'],
					'PalliatMedCareType_id' => $PalliatMedCareType_id,
					'pmUser_id' => $data['pmUser_id']
				));
			}
		}

		return $resp;
	}
}
