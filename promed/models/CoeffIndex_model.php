<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * CoeffIndex_model - модель для работы с коэффициентами индексации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			29.10.2013
 */

class CoeffIndex_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Возвращает список коэффициентов индексации
	 */
	function loadCoeffIndexGrid($data)
	{
		$params = array();

		$query = "
			select
				CI.CoeffIndex_id,
				CI.CoeffIndex_Code,
				CI.CoeffIndex_SysNick,
				CI.CoeffIndex_Name,
				CI.CoeffIndex_Min,
				CI.CoeffIndex_Max
			from
				v_CoeffIndex CI with(nolock)
		";

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			$response['data'] = $result->result('array');
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Возвращает список значений коэффициентов индексации
	 */
	function loadCoeffIndexTariffGrid($data)
	{
		$params = array();
		$filter = '';

		if (!empty($data['TariffClass_id'])) {
			$filter .= ' and CIT.TariffClass_id = :TariffClass_id';
			$params['TariffClass_id'] = $data['TariffClass_id'];
		}

		if (!empty($data['CoeffIndex_id'])) {
			$filter .= ' and CIT.CoeffIndex_id = :CoeffIndex_id';
			$params['CoeffIndex_id'] = $data['CoeffIndex_id'];
		}

		if (!empty($data['LpuSection_id'])) {
			$filter .= ' and CIT.LpuSection_id = :LpuSection_id';
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}

		if (!empty($data['CoeffIndexTariff_begDate'])) {
			$filter .= ' and ( CoeffIndexTariff_begDate >= :CoeffIndexTariff_begDate';
			$filter .= ' or CoeffIndexTariff_endDate >= :CoeffIndexTariff_begDate )';
			$params['CoeffIndexTariff_begDate'] = $data['CoeffIndexTariff_begDate'];
		}

		if (!empty($data['CoeffIndexTariff_endDate'])) {
			$filter .= ' and ( CoeffIndexTariff_begDate <= :CoeffIndexTariff_endDate';
			$filter .= ' or CoeffIndexTariff_endDate <= :CoeffIndexTariff_endDate )';
			$params['CoeffIndexTariff_endDate'] = $data['CoeffIndexTariff_endDate'];
		}

		if (!empty($data['isClose']) && $data['isClose'] == 1) {
			$filter .= " and (CoeffIndexTariff_endDate is null or CoeffIndexTariff_endDate > dbo.tzGetDate())";
		} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
			$filter .= " and CoeffIndexTariff_endDate <= dbo.tzGetDate()";
		}

		$query = "
			select
				CIT.CoeffIndexTariff_id,
				CIT.TariffClass_id,
				CIT.CoeffIndex_id,
				TC.TariffClass_Name,
				CI.CoeffIndex_Code,
				CI.CoeffIndex_SysNick,
				CIT.CoeffIndexTariff_Value,
				CIT.LpuSection_id,
				convert(varchar,cast(CIT.CoeffIndexTariff_begDate as datetime),104) as CoeffIndexTariff_begDate,
				convert(varchar,cast(CIT.CoeffIndexTariff_endDate as datetime),104) as CoeffIndexTariff_endDate
			from
				v_CoeffIndexTariff CIT with(nolock)
				inner join v_TariffClass TC with(nolock) on TC.TariffClass_id = CIT.TariffClass_id
				inner join v_CoeffIndex CI with(nolock) on CI.CoeffIndex_id = CIT.CoeffIndex_id
			where
				(1=1)
				{$filter}
		";

		//print_r($data); exit;
		//echo getDebugSQL($query, $params); exit;

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			$response['data'] = $result->result('array');
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Возвращает данные для редактирования коэффициента индексации
	 */
	function loadCoeffIndexEditForm($data)
	{
		$params = array('CoeffIndex_id' => $data['CoeffIndex_id']);

		$query = "
			select top 1
				CI.CoeffIndex_id,
				CI.CoeffIndex_Code,
				CI.CoeffIndex_SysNick,
				CI.CoeffIndex_Name,
				CI.CoeffIndex_Min,
				CI.CoeffIndex_Max
			from
				v_CoeffIndex CI with(nolock)
			where CI.CoeffIndex_id = :CoeffIndex_id
		";

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}


	/**
	 * Возвращает данные для редактирования значения коэффициента индексации
	 */
	function loadCoeffIndexTariffEditForm($data)
	{
		$params = array('CoeffIndexTariff_id' => $data['CoeffIndexTariff_id']);

		$query = "
			select top 1
				CIT.CoeffIndexTariff_id,
				CIT.TariffClass_id,
				CIT.CoeffIndex_id,
				CIT.CoeffIndexTariff_Value,
				CIT.LpuSection_id,
				convert(varchar,cast(CIT.CoeffIndexTariff_begDate as datetime),104) as CoeffIndexTariff_begDate,
				convert(varchar,cast(CIT.CoeffIndexTariff_endDate as datetime),104) as CoeffIndexTariff_endDate
			from
				v_CoeffIndexTariff CIT with(nolock)
			where CIT.CoeffIndexTariff_id = :CoeffIndexTariff_id
		";

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 * Возвращает список коэффициентов индексации
	 */
	function loadCoeffIndexList($data)
	{
		$params = array();

		$query = "
			select
				CI.CoeffIndex_id,
				CI.CoeffIndex_Code,
				CI.CoeffIndex_SysNick,
				CI.CoeffIndex_Name,
				CI.CoeffIndex_Min,
				CI.CoeffIndex_Max
			from
				v_CoeffIndex CI with(nolock)
		";

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 * Сохранение коэффициента индексации
	 */
	function saveCoeffIndex($data)
	{
		if ( !empty($data['CoeffIndex_Max']) && !empty($data['CoeffIndex_Min']) && $data['CoeffIndex_Min'] > $data['CoeffIndex_Max'] ) {
			$response['Error_Msg'] = 'Максимальное значение должно быть не меньше минимального';
			return array($response);
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :CoeffIndex_id;
			exec p_CoeffIndex_" . (!empty($data['CoeffIndex_id']) && $data['CoeffIndex_id'] > 0 ? "upd" : "ins") . "
				@CoeffIndex_id = @Res output,
				@CoeffIndex_Code = :CoeffIndex_Code,
				@CoeffIndex_SysNick = :CoeffIndex_SysNick,
				@CoeffIndex_Name = :CoeffIndex_Name,
				@CoeffIndex_Min = :CoeffIndex_Min,
				@CoeffIndex_Max = :CoeffIndex_Max,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as FoodCookSpec_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$params = array(
			'CoeffIndex_id' => (isset($data['CoeffIndex_id']) ? $data['CoeffIndex_id'] : null),
			'CoeffIndex_Code' => $data['CoeffIndex_Code'],
			'CoeffIndex_SysNick' => $data['CoeffIndex_SysNick'],
			'CoeffIndex_Name' => $data['CoeffIndex_Name'],
			'CoeffIndex_Min' => $data['CoeffIndex_Min'],
			'CoeffIndex_Max' => $data['CoeffIndex_Max'],
			'pmUser_id' => $data['pmUser_id']
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
	 * Сохранение значения коэффициента индексации
	 */
	function saveCoeffIndexTariff($data)
	{
		$response = array(
			'CoeffIndexTariff_id' => null,
			'Error_Code' => null,
			'Error_Msg' => null
		);

		$check = $this->checkCoeffIndexTariffDate($data);
		if ($check['success'] == false) {
			$response['Error_Msg'] = $check['Error_Msg'];
			return array($response);
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :CoeffIndexTariff_id;
			exec p_CoeffIndexTariff_" . (!empty($data['CoeffIndexTariff_id']) && $data['CoeffIndexTariff_id'] > 0 ? "upd" : "ins") . "
				@CoeffIndexTariff_id = @Res output,
				@TariffClass_id = :TariffClass_id,
				@CoeffIndex_id = :CoeffIndex_id,
				@CoeffIndexTariff_Value = :CoeffIndexTariff_Value,
				@CoeffIndexTariff_begDate = :CoeffIndexTariff_begDate,
				@CoeffIndexTariff_endDate = :CoeffIndexTariff_endDate,
				@LpuSection_id = :LpuSection_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as CoeffIndexTariff_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$params = array(
			'CoeffIndexTariff_id' => (isset($data['CoeffIndexTariff_id']) ? $data['CoeffIndexTariff_id'] : null),
			'TariffClass_id' => $data['TariffClass_id'],
			'CoeffIndex_id' => $data['CoeffIndex_id'],
			'CoeffIndexTariff_Value' => $data['CoeffIndexTariff_Value'],
			'CoeffIndexTariff_begDate' => $data['CoeffIndexTariff_begDate'],
			'CoeffIndexTariff_endDate' => $data['CoeffIndexTariff_endDate'],
			'LpuSection_id' => $data['LpuSection_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array($response);
		}
	}

	/**
	 * Удаление коэффициента индексации
	 */
	function deleteCoeffIndex($data)
	{
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_CoeffIndex_del
				@CoeffIndex_id = :CoeffIndex_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$params = array(
			'CoeffIndex_id' => $data['CoeffIndex_id']
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
	 * Проверка периода дейстия значения коэффициента идексации при сохранении
	 */
	function checkCoeffIndexTariffDate($data)
	{
		$check = array('success' => true, 'Error_Msg' => null);

		$filter = '';
		if (!empty($data['CoeffIndexTariff_id'])) {
			$filter .= ' and CIT.CoeffIndexTariff_id <> :CoeffIndexTariff_id';
		}
		if (!empty($data['CoeffIndexTariff_endDate'])) {
			$filter .= ' and ( ( CIT.CoeffIndexTariff_begDate <= :CoeffIndexTariff_begDate and (CIT.CoeffIndexTariff_endDate >= :CoeffIndexTariff_begDate or CIT.CoeffIndexTariff_endDate is null))';
			$filter .= ' or (CIT.CoeffIndexTariff_begDate <= :CoeffIndexTariff_endDate and (CIT.CoeffIndexTariff_endDate >= :CoeffIndexTariff_endDate or CIT.CoeffIndexTariff_endDate is null)) )';
		} else {
			$filter .= ' and ( (CIT.CoeffIndexTariff_begDate <= :CoeffIndexTariff_begDate and (CIT.CoeffIndexTariff_endDate >= :CoeffIndexTariff_begDate or CIT.CoeffIndexTariff_endDate is null))';
			$filter .= ' or CIT.CoeffIndexTariff_begDate > :CoeffIndexTariff_begDate)';
		}

		$query = "
			select
				COUNT(*) as Count
			from v_CoeffIndexTariff CIT with(nolock)
			where
				CIT.TariffClass_id = :TariffClass_id
				and CIT.CoeffIndex_id = :CoeffIndex_id
				and CIT.LpuSection_id = :LpuSection_id
				{$filter}
		";

		$params = array(
			'CoeffIndexTariff_id' => $data['CoeffIndexTariff_id'],
			'TariffClass_id' => $data['TariffClass_id'],
			'CoeffIndex_id' => $data['CoeffIndex_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'CoeffIndexTariff_begDate' => $data['CoeffIndexTariff_begDate'],
			'CoeffIndexTariff_endDate' => $data['CoeffIndexTariff_endDate']
		);

		//echo getDebugSQL($query, $params); exit;
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			$response = $result->result('array');
			if (isset($response[0]['Count']) && $response[0]['Count'] > 0) {
				$check['success'] = false;
				$check['Error_Msg'] = 'Не должно быть перечечение периодов по виду тарифа и коэффициента индексации';
				return $check;
			}
		}
		else {
			$check['success'] = false;
			$check['ErrorMsg'] = 'Ошибка запроса при проверки данных';
			return $check;
		}
		return $check;

	}
}