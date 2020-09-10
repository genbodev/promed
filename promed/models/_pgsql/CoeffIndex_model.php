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

class CoeffIndex_model extends swPgModel
{
    protected $dateForm104 = "'DD.MM.YYYY'";

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
				CI.CoeffIndex_id as \"CoeffIndex_id\",
				CI.CoeffIndex_Code as \"CoeffIndex_Code\",
				CI.CoeffIndex_SysNick as \"CoeffIndex_SysNick\",
				CI.CoeffIndex_Name as \"CoeffIndex_Name\",
				CI.CoeffIndex_Min as \"CoeffIndex_Min\",
				CI.CoeffIndex_Max as \"CoeffIndex_Max\"
			from
				v_CoeffIndex CI
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
				CIT.CoeffIndexTariff_id as \"CoeffIndexTariff_id\",
				CIT.TariffClass_id as \"TariffClass_id\",
				CIT.CoeffIndex_id as \"CoeffIndex_id\",
				TC.TariffClass_Name as \"TariffClass_Name\",
				CI.CoeffIndex_Code as \"CoeffIndex_Code\",
				CI.CoeffIndex_SysNick as \"CoeffIndex_SysNick\",
				CIT.CoeffIndexTariff_Value as \"CoeffIndexTariff_Value\",
				CIT.LpuSection_id as \"LpuSection_id\",
				to_char(cast(CIT.CoeffIndexTariff_begDate as timestamp), {$this->dateForm104}) as \"CoeffIndexTariff_begDate\",
				to_char(cast(CIT.CoeffIndexTariff_endDate as timestamp), {$this->dateForm104}) as \"CoeffIndexTariff_endDate\"
			from
				v_CoeffIndexTariff CIT
				inner join v_TariffClass TC on TC.TariffClass_id = CIT.TariffClass_id
				inner join v_CoeffIndex CI on CI.CoeffIndex_id = CIT.CoeffIndex_id
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
			select
				CI.CoeffIndex_id as \"CoeffIndex_id\",
				CI.CoeffIndex_Code as \"CoeffIndex_Code\",
				CI.CoeffIndex_SysNick as \"CoeffIndex_SysNick\",
				CI.CoeffIndex_Name as \"CoeffIndex_Name\",
				CI.CoeffIndex_Min as \"CoeffIndex_Min\",
				CI.CoeffIndex_Max as \"CoeffIndex_Max\"
			from
				v_CoeffIndex CI
			where 
			    CI.CoeffIndex_id = :CoeffIndex_id
			limit 1
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
			select
				CIT.CoeffIndexTariff_id as \"CoeffIndexTariff_id\",
				CIT.TariffClass_id as \"TariffClass_id\",
				CIT.CoeffIndex_id as \"CoeffIndex_id\",
				CIT.CoeffIndexTariff_Value as \"CoeffIndexTariff_Value\",
				CIT.LpuSection_id as \"LpuSection_id\",
				to_char(cast(CIT.CoeffIndexTariff_begDate as timestamp), {$this->dateForm104}) as \"CoeffIndexTariff_begDate\",
				to_char(cast(CIT.CoeffIndexTariff_endDate as timestamp), {$this->dateForm104}) as \"CoeffIndexTariff_endDate\"
			from
				v_CoeffIndexTariff CIT
			where 
			    CIT.CoeffIndexTariff_id = :CoeffIndexTariff_id
			limit 1
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
				CI.CoeffIndex_id as \"CoeffIndex_id\",
				CI.CoeffIndex_Code as \"CoeffIndex_Code\",
				CI.CoeffIndex_SysNick as \"CoeffIndex_SysNick\",
				CI.CoeffIndex_Name as \"CoeffIndex_Name\",
				CI.CoeffIndex_Min as \"CoeffIndex_Min\",
				CI.CoeffIndex_Max as \"CoeffIndex_Max\"
			from
				v_CoeffIndex CI
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

		$action = (!empty($data['CoeffIndex_id']) && $data['CoeffIndex_id'] > 0 )? "upd" : "ins" ;

		$query = "
		    select 
		        CoeffIndex_id as \"FoodCookSpec_id\",
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
		    from p_CoeffIndex_{$action}
		    (
		        CoeffIndex_id := :CoeffIndex_id,
				CoeffIndex_Code := :CoeffIndex_Code,
				CoeffIndex_SysNick := :CoeffIndex_SysNick,
				CoeffIndex_Name := :CoeffIndex_Name,
				CoeffIndex_Min := :CoeffIndex_Min,
				CoeffIndex_Max := :CoeffIndex_Max,
				pmUser_id := :pmUser_id
		    )
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

		$action = (!empty($data['CoeffIndexTariff_id']) && $data['CoeffIndexTariff_id'] > 0 ) ? "upd" : "ins";
		$query = "
		    select 
		        CoeffIndexTariff_id as \"CoeffIndexTariff_id\",
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
		    from p_CoeffIndexTariff_{$action}
		    (
		        CoeffIndexTariff_id := :CoeffIndexTariff_id,
				TariffClass_id := :TariffClass_id,
				CoeffIndex_id := :CoeffIndex_id,
				CoeffIndexTariff_Value := :CoeffIndexTariff_Value,
				CoeffIndexTariff_begDate := :CoeffIndexTariff_begDate,
				CoeffIndexTariff_endDate := :CoeffIndexTariff_endDate,
				LpuSection_id := :LpuSection_id,
				pmUser_id := :pmUser_id
		    )
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
		    select 
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
		    from p_CoeffIndex_del
		    (
		        CoeffIndex_id := :CoeffIndex_id
		    )
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
				COUNT(*) as \"Count\"
			from
			    v_CoeffIndexTariff CIT
			where
				CIT.TariffClass_id = :TariffClass_id
            and 
                CIT.CoeffIndex_id = :CoeffIndex_id
			and 
			    CIT.LpuSection_id = :LpuSection_id
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
			$check['Error_Msg'] = 'Ошибка запроса при проверки данных';
			return $check;
		}
		return $check;

	}
}