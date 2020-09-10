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

class PersonNotice_model extends swModel {
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
			declare
				@PersonNotice_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @PersonNotice_id = :PersonNotice_id;
			exec {$procedure}
				@PersonNotice_id = @PersonNotice_id output,
				@Person_id = :Person_id,
				@PersonNotice_IsSend = :PersonNotice_IsSend,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @PersonNotice_id as PersonNotice_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				PersonNotice_id,
				PersonNotice_IsSend
			from v_PersonNotice with(nolock)
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
			declare @curdate datetime = dbo.tzGetDate();

			select distinct
				UC.pmUser_id,
				MSF.MedPersonal_id,
				UC.pmUser_Email,
				UC.pmUser_Phone,
				UC.pmUser_IsEmail,
				UC.pmUser_IsSMS,
				UC.pmUser_IsMessage,
				UC.pmUser_EvnClass
			from
				v_MedStaffFact MSF with(nolock)
				inner join v_pmUserCache UC with(nolock) on UC.MedPersonal_id = MSF.MedPersonal_id
			where
				(
					UC.pmUser_IsMessage = 1
					or (UC.pmUser_IsEmail = 1 and UC.PMUser_Email is not null)
					or (UC.pmUser_IsSMS = 1 and UC.PMUser_PhoneAct = 1 and UC.PMUser_Phone is not null)
				)
				and UC.pmUser_deleted <> 2
				and not exists(
					select t.PersonNotice_id from v_PersonNotice t with(nolock)
					where t.Person_id = :Person_id and t.pmUser_insID = UC.pmUser_id
					and t.PersonNotice_IsSend = 1
				)
				and MSF.LpuSection_id in (
					select t.LpuSection_id
					from v_EvnSection t with(nolock)
					where t.Person_id = :Person_id
					and t.EvnSection_setDT <= @curdate
					and (t.EvnSection_disDT > @curdate or t.EvnSection_disDT is null)
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
				UC.pmUser_id,
				MSF.MedPersonal_id,
				UC.pmUser_Email,
				UC.pmUser_Phone,
				UC.pmUser_IsEmail,
				UC.pmUser_IsSMS,
				UC.pmUser_IsMessage,
				UC.pmUser_EvnClass
			from
				v_MedStaffFact MSF with(nolock)
				inner join v_pmUserCache UC with(nolock) on UC.MedPersonal_id = MSF.MedPersonal_id
				inner join v_MedStaffRegion MSR with(nolock) on MSR.MedStaffFact_id = MSF.MedStaffFact_id
				inner join v_PersonCardState PCS with(nolock) on PCS.LpuRegion_id = MSR.LpuRegion_id
				left join v_EvnVizitPL EVPL on EVPL.MedPersonal_id = MSF.MedPersonal_id and EVPL.Person_id = :Person_id
				left join v_EvnPL EPL with(nolock) on EVPL.EvnVizitPL_pid = EPL.EvnPL_id
			where (
					UC.pmUser_IsMessage = 1
					or (UC.pmUser_IsEmail = 1 and UC.PMUser_Email is not null)
					or (UC.pmUser_IsSMS = 1 and UC.PMUser_PhoneAct = 1 and UC.PMUser_Phone is not null)
				)
				and ISNULL(UC.pmUser_deleted,1) = 1
				and not exists(
					select top 1 t.PersonNotice_id from v_PersonNotice t with(nolock)
					where t.Person_id = :Person_id and t.pmUser_insID = UC.pmUser_id
					and t.PersonNotice_IsSend = 1
				)
				and UC.pmUser_PolkaGroupType = 1
				and PCS.Person_id = :Person_id
			
			union
			
			select distinct
				UC.pmUser_id,
				EVPL.MedPersonal_id,
				UC.pmUser_Email,
				UC.pmUser_Phone,
				UC.pmUser_IsEmail,
				UC.pmUser_IsSMS,
				UC.pmUser_IsMessage,
				UC.pmUser_EvnClass
			from
				v_EvnVizitPL EVPL with(nolock)
				inner join v_EvnPL EPL with(nolock) on EVPL.EvnVizitPL_pid = EPL.EvnPL_id
				inner join v_pmUserCache UC with(nolock) on UC.MedPersonal_id = EVPL.MedPersonal_id	
			where (
					UC.pmUser_IsMessage = 1
					or (UC.pmUser_IsEmail = 1 and UC.PMUser_Email is not null)
					or (UC.pmUser_IsSMS = 1 and UC.PMUser_PhoneAct = 1 and UC.PMUser_Phone is not null)
				)
				and ISNULL(UC.pmUser_deleted,1) = 1
				and not exists(
					select top 1 t.PersonNotice_id from v_PersonNotice t with(nolock)
					where t.Person_id = :Person_id and t.pmUser_insID = UC.pmUser_id
					and t.PersonNotice_IsSend = 1
				)
				and ISNULL(UC.pmUser_PolkaGroupType, 2) = 2
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