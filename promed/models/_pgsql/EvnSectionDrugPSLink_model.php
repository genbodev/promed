<?php

/**
 * EvnSectionDrugPSLink_model - модель для работы с медикаментами/мероприятиями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package                Polka
 * @copyright            Copyright (c) 2017 Swan Ltd.
 * @link                http://swan.perm.ru/PromedWeb
 */
class EvnSectionDrugPSLink_model extends swPgModel
{
	/**
	 *    Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 *    Удаление
	 */
	function deleteEvnSectionDrugPSLink($data)
	{
		$process = 'p_EvnSectionDrugPSLink_del';
		$queryParams = array(
			'EvnSectionDrugPSLink_id' => $data['EvnSectionDrugPSLink_id']
		);

		$selectString = "
			error_code as \"Error_Code\", 
			error_message as \"Error_Msg\"
        ";
		$query = "
			select {$selectString}
            from {$process}(
                evnsectiondugpslink_id := :EvnSectionDrugPSLink_id
            );
        ";

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление медикамента/мероприятия)'));
		}
	}


	/**
	 *    Получение списка
	 */
	function loadEvnSectionDrugPSLinkGrid($data)
	{

		$query_params = array(
			'EvnSection_id' => $data['EvnSection_id']
		);
		$filters = "ESDPSL.EvnSection_id = :EvnSection_id";

		$query = "
			select
				ESDPSL.EvnSectionDrugPSLink_id as \"EvnSectionDrugPSLink_id\",
				ESDPSL.EvnSection_id as \"EvnSection_id\",
				ESDPSL.DrugPS_id as \"DrugPS_id\",
				ESDPSL.DrugPSForm_id as \"DrugPSForm_id\",
				ESDPSL.EvnSectionDrugPSLink_Dose as \"EvnSectionDrugPSLink_Dose\",
				DPS.DrugPS_Name as \"DrugPS_Name\",
				DPSF.DrugPSForm_Name as \"DrugPSForm_Name\"
			from r91.v_EvnSectionDrugPSLink ESDPSL
				left join r91.v_DrugPS DPS on DPS.DrugPS_id = ESDPSL.DrugPS_id
				left join r91.v_DrugPSForm DPSF on DPSF.DrugPSForm_id = ESDPSL.DrugPSForm_id
			where
				{$filters}
		";

		$result = $this->db->query($query, $query_params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *    Получение списка
	 */
	function loadDrugPSList($data)
	{
		$filter = "";
		$queryParams = array();

		if (!empty($data['DrugPS_id'])) {
			$filter .= " and DPS.DrugPS_id = :DrugPS_id";
			$queryParams['DrugPS_id'] = $data['DrugPS_id'];
		} else {
			if (!empty($data['MesTariff_id'])) {
				// достаём КСГ по тарифу
				$resp_mt = $this->queryResult("select Mes_id from v_MesTariff where MesTariff_id = :MesTariff_id", array(
					'MesTariff_id' => $data['MesTariff_id']
				));
				if (!empty($resp_mt[0]['Mes_id'])) {
					$queryParams['Mes_id'] = $resp_mt[0]['Mes_id'];

					$dpsmlFilter = "";
					if (!empty($data['onDate'])) {
						$dpsmlFilter .= " and coalesce(DPSML.DrugPSMesLink_begDate, :onDate) <= :onDate";
						$dpsmlFilter .= " and coalesce(DPSML.DrugPSMesLink_endDate, :onDate) >= :onDate";
						$queryParams['onDate'] = $data['onDate'];
					}

					$resp_dpsml = $this->queryResult("select DPSML.DrugPSMesLink_id as \"DrugPSMesLink_id\" from v_DrugPSMesLink DPSML where DPSML.MesOld_id = :Mes_id {$dpsmlFilter} limit 1", $queryParams);
					if (!empty($resp_dpsml[0]['DrugPSMesLink_id'])) {
						$filter .= " and exists(select DPSML.DrugPSMesLink_id from v_DrugPSMesLink DPSML where DPSML.MesOld_id = :Mes_id and DPSML.DrugPS_id = DPS.DrugPS_id {$dpsmlFilter}) limit 1";
					}
				}
			}

			if (!empty($data['onDate'])) {
				$filter .= " and coalesce(DPS.DrugPS_begDate, :onDate) <= :onDate";
				$filter .= " and coalesce(DPS.DrugPS_endDate, :onDate) >= :onDate";
				$queryParams['onDate'] = $data['onDate'];
			}

			if (!empty($data['query'])) {
				$filter .= " and coalesce(DPS.DrugPS_Code, '') || ' ' || coalesce(DPS.DrugPS_Name, '') like ('%' || :query || '%')";
				$queryParams['query'] = $data['query'];
			}
		}

		$query = "
			select
				DPS.DrugPS_id as \"DrugPS_id\",
				DPS.DrugPS_Code as \"DrugPS_Code\",
				DPS.DrugPS_Name as \"DrugPS_Name\"
			from
				r91.v_DrugPS DPS
			where
				(1=1)
				{$filter}
		";
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *    Сохранение
	 */
	function saveEvnSectionDrugPSLink($data)
	{
		if ((!isset($data['EvnSectionDrugPSLink_id'])) || ($data['EvnSectionDrugPSLink_id'] <= 0)) {
			$procedure = 'p_EvnSectionDrugPSLink_ins';
		} else {
			$procedure = 'p_EvnSectionDrugPSLink_upd';
		}

		$selectString = "
            evnsectiondrugpslink_id as \"EvnSectionDrugPSLink_id\", 
            error_code as \"Error_Code\", 
            error_message as \"Error_Msg\"
        ";

		$queryParams = array(
			'EvnSectionDrugPSLink_id' => ((!isset($data['EvnSectionDrugPSLink_id'])) || ($data['EvnSectionDrugPSLink_id'] <= 0) ? NULL : $data['EvnSectionDrugPSLink_id']),
			'EvnSection_id' => $data['EvnSection_id'],
			'DrugPS_id' => $data['DrugPS_id'],
			'DrugPSForm_id' => $data['DrugPSForm_id'],
			'EvnSectionDrugPSLink_Dose' => $data['EvnSectionDrugPSLink_Dose'],
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "
			select {$selectString}
			from r91.{$procedure}(
			    evnsectiondrugpslink_id := :EvnSectionDrugPSLink_id,
			    evnsection_id := :EvnSection_id,
			    drugps_id := :DrugPS_id,
			    drugpsform_id := :DrugPSForm_id,
			    evnsectiondrugpslink_dose := :EvnSectionDrugPSLink_Dose,
			    pmuser_id := :pmUser_id
			);
		";

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
}