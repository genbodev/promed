<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PersonNotice_model - модель для работы с уведомлениями по пациенту
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			04.04.2014
 *
 */

class PersonNotice_model extends SwPgModel {
	/**
	 * Сохраниение настройки уведомлений по пациенту
	 */
	function savePersonNotice($data) {
		$params = array(
			'PersonNotice_id' => $data['PersonNotice_id'],
			'Person_id' => $data['Person_id'],
			'PersonNotice_IsSend' => $data['PersonNotice_IsSend'],
			'pmUser_id' => $data['pmUser_id']
		);

		if (empty($params['PersonNotice_id'])) {
			$procedure = 'p_PersonNotice_ins';
		} else {
			$procedure = 'p_PersonNotice_upd';
		}

		$query = "
			select
				PersonNotice_id as \"PersonNotice_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				PersonNotice_id := :PersonNotice_id,
				Person_id := :Person_id,
				PersonNotice_IsSend := :PersonNotice_IsSend,
				pmUser_id := :pmUser_id
			)
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при сохранении настройки уведомлений пациента');
		}
		$response = $result->result('array');

		return $response;
	}

	/**
	 * Получение настройки уведомлений по пациенту для пользователя
	 */
	function getPersonNotice($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "
			select
				PersonNotice_id as \"PersonNotice_id\",
				PersonNotice_IsSend as \"PersonNotice_IsSend\"
			from v_PersonNotice
			where
				Person_id = :Person_id
				and pmUser_insID = :pmUser_id
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при запросе настройки уведомлений пациента');
		}

		return $result->result('array');
	}

	/**
	 * Получение пользователей для рассылки уведомлений по пациенту
	 */
	function getUsersForPersonNotice($data) {
		$params = array('Person_id' => $data['Person_id']);

		$query = "
			with mv as(
				select dbo.tzgetdate() as dt
			)

			select distinct
				UC.pmUser_id as \"pmUser_id\",
				MSF.MedPersonal_id as \"MedPersonal_id\",
				UC.pmUser_Email as \"pmUser_Email\",
				UC.pmUser_Phone as \"pmUser_Phone\",
				UC.pmUser_IsEmail as \"pmUser_IsEmail\",
				UC.pmUser_IsSMS as \"pmUser_IsSMS\",
				UC.pmUser_IsMessage as \"pmUser_IsMessage\",
				UC.pmUser_EvnClass as \"pmUser_EvnClass\"
			from
				v_MedStaffFact MSF
				inner join v_pmUserCache UC on UC.MedPersonal_id = MSF.MedPersonal_id
			where
				(
					UC.pmUser_IsMessage = 1
					or (UC.pmUser_IsEmail = 1 and UC.PMUser_Email is not null)
					or (UC.pmUser_IsSMS = 1 and UC.PMUser_PhoneAct = '1' and UC.PMUser_Phone is not null)
				)
				and UC.pmUser_deleted <> 2
				and not exists(
					select t.PersonNotice_id from v_PersonNotice t
					where t.Person_id = 
                    :Person_id 
                    and t.pmUser_insID = UC.pmUser_id
					and t.PersonNotice_IsSend = 1
				)
				and exists (
					select
						t.LpuSection_id
                    from v_EvnSection t
                    where t.Person_id = '59551918'
						and t.LpuSection_id=MSF.LpuSection_id 
						and t.EvnSection_setDT <= (select dt from mv)
						and (t.EvnSection_disDT > (select dt from mv) or t.EvnSection_disDT is null)
						and (case
							when UC.pmUser_GroupType = 1 then 1
							when UC.pmUser_GroupType = 2 and (t.MedPersonal_id = MSF.MedPersonal_id) then 1
							else 0
						end) = 1
                )
		";

		$response = $this->queryResult($query, $params);
		if (!$this->isSuccessful($response)) {
			return array();
		}

		foreach($response as &$item) {
			$EvnClass_arr = json_decode($item['pmUser_EvnClass'], true);
			$item['notice_settings'] = array();
			foreach($EvnClass_arr as $EvnClass) {
				$sysnick = $EvnClass['sysnick'];
				$item['notice_settings'][$sysnick] = 1;
			}
			unset($item['pmUser_EvnClass']);
		}

		return $response;
	}

	/**
	 * Получение пользователей для рассылки уведомлений по пациенту
	 * (для поликлиники)
	 */
	function getUsersForPersonNoticePolka($data) {
		$params = array('Person_id' => $data['Person_id']);

		$query = "			
			select distinct
				UC.pmUser_id as \"pmUser_id\",
				MSF.MedPersonal_id as \"MedPersonal_id\",
				UC.pmUser_Email as \"pmUser_Email\",
				UC.pmUser_Phone as \"pmUser_Phone\",
				UC.pmUser_IsEmail as \"pmUser_IsEmail\",
				UC.pmUser_IsSMS as \"pmUser_IsSMS\",
				UC.pmUser_IsMessage as \"pmUser_IsMessage\",
				UC.pmUser_EvnClass as \"pmUser_EvnClass\"
			from
				v_MedStaffFact MSF
				inner join v_pmUserCache UC on UC.MedPersonal_id = MSF.MedPersonal_id
				inner join v_MedStaffRegion MSR on MSR.MedStaffFact_id = MSF.MedStaffFact_id
				inner join v_PersonCardState PCS on PCS.LpuRegion_id = MSR.LpuRegion_id
				left join v_EvnVizitPL EVPL on EVPL.MedPersonal_id = MSF.MedPersonal_id and EVPL.Person_id = :Person_id
				left join v_EvnPL EPL on EVPL.EvnVizitPL_pid = EPL.EvnPL_id
			where (
					UC.pmUser_IsMessage = 1
					or (UC.pmUser_IsEmail = 1 and UC.PMUser_Email is not null)
					or (UC.pmUser_IsSMS = 1 and UC.PMUser_PhoneAct::text = '1' and UC.PMUser_Phone is not null)
				)
				and coalesce(UC.pmUser_deleted,1) = 1
				and not exists(
					select t.PersonNotice_id from v_PersonNotice t
					where t.Person_id = :Person_id and t.pmUser_insID = UC.pmUser_id
					and t.PersonNotice_IsSend = 1
					limit 1
				)
				and UC.pmUser_PolkaGroupType = 1
				and PCS.Person_id = :Person_id
			
			union
			
			select distinct
				UC.pmUser_id as \"pmUser_id\",
				EVPL.MedPersonal_id as \"MedPersonal_id\",
				UC.pmUser_Email as \"pmUser_Email\",
				UC.pmUser_Phone as \"pmUser_Phone\",
				UC.pmUser_IsEmail as \"pmUser_IsEmail\",
				UC.pmUser_IsSMS as \"pmUser_IsSMS\",
				UC.pmUser_IsMessage as \"pmUser_IsMessage\",
				UC.pmUser_EvnClass as \"pmUser_EvnClass\"
			from
				v_EvnVizitPL EVPL
				inner join v_EvnPL EPL on EVPL.EvnVizitPL_pid = EPL.EvnPL_id
				inner join v_pmUserCache UC on UC.MedPersonal_id = EVPL.MedPersonal_id	
			where (
					UC.pmUser_IsMessage = 1
					or (UC.pmUser_IsEmail = 1 and UC.PMUser_Email is not null)
					or (UC.pmUser_IsSMS = 1 and UC.PMUser_PhoneAct::text = '1' and UC.PMUser_Phone is not null)
				)
				and coalesce(UC.pmUser_deleted,1) = 1
				and not exists(
					select t.PersonNotice_id from v_PersonNotice t
					where t.Person_id = :Person_id and t.pmUser_insID = UC.pmUser_id
					and t.PersonNotice_IsSend = 1
					limit 1
				)
				and coalesce(UC.pmUser_PolkaGroupType, 2) = 2
				and EPL.EvnPL_IsFinish != 2
				and EVPL.Person_id = :Person_id		
		";
		//~ echo getDebugSQL($query, $params);exit;
		$response = $this->queryResult($query, $params);
		if (!$this->isSuccessful($response)) {
			return array();
		}

		foreach($response as &$item) {
			$EvnClass_arr = json_decode($item['pmUser_EvnClass'], true);
			$item['notice_settings'] = array();
			foreach($EvnClass_arr as $EvnClass) {
				$sysnick = $EvnClass['sysnick'];
				$item['notice_settings'][$sysnick] = 1;
			}
			unset($item['pmUser_EvnClass']);
		}

		return $response;
	}
}