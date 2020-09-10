<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_PalliatInfoConsent_del
				@PalliatInfoConsent_id = :PalliatInfoConsent_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				pi.PalliatInfoConsent_id,
				convert(varchar(10), pi.PalliatInfoConsent_consDT, 104) as PalliatInfoConsent_consDT,
				pict.PalliatInfoConsentType_Name,
				msf.Person_Fio as MedPersonal_Fio,
				l.Lpu_Nick,
				pi.Person_id,
				pi.pmUser_insID
			from
				v_PalliatInfoConsent pi with (nolock)
				left join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = pi.MedStaffFact_id
				left join v_PalliatInfoConsentType pict with (nolock) on pict.PalliatInfoConsentType_id = pi.PalliatInfoConsentType_id
				left join v_Lpu l with (nolock) on l.Lpu_id = msf.Lpu_id
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
			select top 1
				PalliatInfoConsent_id,
				PalliatInfoConsentType_id,
				convert(varchar(10), PalliatInfoConsent_consDT, 104) as PalliatInfoConsent_consDT,
				PalliatInfoConsent_isSelf,
				MedStaffFact_id,
				Person_id
			from
				v_PalliatInfoConsent with (nolock)
			where
				PalliatInfoConsent_id = :PalliatInfoConsent_id
		";

		$resp = $this->queryResult($query, array(
			'PalliatInfoConsent_id' => $data['PalliatInfoConsent_id']
		));

		if (!empty($resp[0]['PalliatInfoConsent_id'])) {
			$resp[0]['PalliatMedCareTypeLinkData'] = array();
			// выбираем отказы от мероприятий
			$resp_pmct = $this->queryResult("
				select
					PalliatMedCareType_id
				from
					v_PalliatMedCareTypeLink (nolock)
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :PalliatInfoConsent_id;

			exec " . $procedure . "
				@PalliatInfoConsent_id = @Res output,
				@PalliatInfoConsentType_id = :PalliatInfoConsentType_id,
				@PalliatInfoConsent_consDT = :PalliatInfoConsent_consDT,
				@PalliatInfoConsent_isSelf = :PalliatInfoConsent_isSelf,
				@MedStaffFact_id = :MedStaffFact_id,
				@Person_id = :Person_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as PalliatInfoConsent_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
					PalliatMedCareTypeLink_id,
					PalliatMedCareType_id
				from
					v_PalliatMedCareTypeLink (nolock)
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
				$this->queryResult("
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
		
					exec p_PalliatMedCareTypeLink_del
						@PalliatMedCareTypeLink_id = :PalliatMedCareTypeLink_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
		
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				", array(
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
				$this->queryResult("
					declare
						@ErrCode int,
						@ErrMessage varchar(4000),
						@PalliatMedCareTypeLink_id bigint = null;
		
					exec p_PalliatMedCareTypeLink_ins
						@PalliatMedCareTypeLink_id = @PalliatMedCareTypeLink_id output,
						@PalliatMedCareType_id = :PalliatMedCareType_id,
						@PalliatInfoConsent_id = :PalliatInfoConsent_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
		
					select @PalliatMedCareTypeLink_id as PalliatMedCareTypeLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				", array(
					'PalliatInfoConsent_id' => $resp[0]['PalliatInfoConsent_id'],
					'PalliatMedCareType_id' => $PalliatMedCareType_id,
					'pmUser_id' => $data['pmUser_id']
				));
			}
		}

		return $resp;
	}
}
