<?php
/**
 * EvnSectionDrugPSLink_model - модель для работы с медикаментами/мероприятиями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package				Polka
 * @copyright			Copyright (c) 2017 Swan Ltd.
 * @link				http://swan.perm.ru/PromedWeb
 */
class EvnSectionDrugPSLink_model extends swModel {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 *	Удаление
	 */
	function deleteEvnSectionDrugPSLink($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_EvnSectionDrugPSLink_del
				@EvnSectionDrugPSLink_id = :EvnSectionDrugPSLink_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$queryParams = array(
			'EvnSectionDrugPSLink_id' => $data['EvnSectionDrugPSLink_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление медикамента/мероприятия)'));
		}
	}


	/**
	 *	Получение списка
	 */
	function loadEvnSectionDrugPSLinkGrid($data) {
		$query = "
			select
				ESDPSL.EvnSectionDrugPSLink_id,
				ESDPSL.EvnSection_id,
				ESDPSL.DrugPS_id,
				ESDPSL.DrugPSForm_id,
				ESDPSL.EvnSectionDrugPSLink_Dose,
				DPS.DrugPS_Name,
				DPSF.DrugPSForm_Name
			from r91.v_EvnSectionDrugPSLink ESDPSL with (nolock)
				left join r91.v_DrugPS DPS with (nolock) on DPS.DrugPS_id = ESDPSL.DrugPS_id
				left join r91.v_DrugPSForm DPSF with (nolock) on DPSF.DrugPSForm_id = ESDPSL.DrugPSForm_id
			where
				ESDPSL.EvnSection_id = :EvnSection_id
		";
		$result = $this->db->query($query, array(
			'EvnSection_id' => $data['EvnSection_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение списка
	 */
	function loadDrugPSList($data) {
		$filter = "";
		$queryParams = array();

		if (!empty($data['DrugPS_id'])) {
			$filter .= " and DPS.DrugPS_id = :DrugPS_id";
			$queryParams['DrugPS_id'] = $data['DrugPS_id'];
		} else {
			if (!empty($data['MesTariff_id'])) {
				// достаём КСГ по тарифу
				$resp_mt = $this->queryResult("select Mes_id from v_MesTariff (nolock) where MesTariff_id = :MesTariff_id", array(
					'MesTariff_id' => $data['MesTariff_id']
				));
				if (!empty($resp_mt[0]['Mes_id'])) {
					$queryParams['Mes_id'] = $resp_mt[0]['Mes_id'];

					$dpsmlFilter = "";
					if (!empty($data['onDate'])) {
						$dpsmlFilter .= " and ISNULL(DPSML.DrugPSMesLink_begDate, :onDate) <= :onDate";
						$dpsmlFilter .= " and ISNULL(DPSML.DrugPSMesLink_endDate, :onDate) >= :onDate";
						$queryParams['onDate'] = $data['onDate'];
					}

					$resp_dpsml = $this->queryResult("select top 1 DPSML.DrugPSMesLink_id from v_DrugPSMesLink DPSML (nolock) where DPSML.MesOld_id = :Mes_id {$dpsmlFilter}", $queryParams);
					if (!empty($resp_dpsml[0]['DrugPSMesLink_id'])) {
						$filter .= " and exists(select top 1 DPSML.DrugPSMesLink_id from v_DrugPSMesLink DPSML (nolock) where DPSML.MesOld_id = :Mes_id and DPSML.DrugPS_id = DPS.DrugPS_id {$dpsmlFilter})";
					}
				}
			}

			if (!empty($data['onDate'])) {
				$filter .= " and ISNULL(DPS.DrugPS_begDate, :onDate) <= :onDate";
				$filter .= " and ISNULL(DPS.DrugPS_endDate, :onDate) >= :onDate";
				$queryParams['onDate'] = $data['onDate'];
			}

			if (!empty($data['query'])) {
				$filter .= " and ISNULL(DPS.DrugPS_Code, '') + ' ' + ISNULL(DPS.DrugPS_Name, '') like '%' + :query + '%'";
				$queryParams['query'] = $data['query'];
			}
		}

		$query = "
			select
				DPS.DrugPS_id,
				DPS.DrugPS_Code,
				DPS.DrugPS_Name
			from
				r91.v_DrugPS DPS with (nolock)
			where
				(1=1)
				{$filter}
		";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Сохранение
	 */
	function saveEvnSectionDrugPSLink($data) {
		if ( (!isset($data['EvnSectionDrugPSLink_id'])) || ($data['EvnSectionDrugPSLink_id'] <= 0) ) {
			$procedure = 'p_EvnSectionDrugPSLink_ins';
		}
		else {
			$procedure = 'p_EvnSectionDrugPSLink_upd';
		}
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnSectionDrugPSLink_id;
			exec r91." . $procedure . "
				@EvnSectionDrugPSLink_id = @Res output,
				@EvnSection_id = :EvnSection_id,
				@DrugPS_id = :DrugPS_id,
				@DrugPSForm_id = :DrugPSForm_id,
				@EvnSectionDrugPSLink_Dose = :EvnSectionDrugPSLink_Dose,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnSectionDrugPSLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnSectionDrugPSLink_id' => ((!isset($data['EvnSectionDrugPSLink_id'])) || ($data['EvnSectionDrugPSLink_id'] <= 0) ? NULL : $data['EvnSectionDrugPSLink_id']),
			'EvnSection_id' => $data['EvnSection_id'],
			'DrugPS_id' => $data['DrugPS_id'],
			'DrugPSForm_id' => $data['DrugPSForm_id'],
			'EvnSectionDrugPSLink_Dose' => $data['EvnSectionDrugPSLink_Dose'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}