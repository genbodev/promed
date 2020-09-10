<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * HTMedicalCare_model - модель для с работы с высокотехнологичной медицинской помощью
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Hospital
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			17.03.2014
 *
 */
require(APPPATH.'controllers/Address.php');

class HTMedicalCare_model extends swPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Возвращает список настроек кодов видов медицинской помощи
	 */
	function loadHTMedicalCareClassList($data)
	{
		$params = array();
		$filterList = array('(1 = 1)');

		if (in_array(getRegionNick(), array('ufa', 'astra', 'kareliya', 'krym', 'perm', 'pskov'))){
			$linkFilters = array('PayTypeLink.HTMedicalCareClass_id = DHTMCC.HTMedicalCareClass_id');

			if (!empty($data['PayType_id'])) {
				$linkFilters[] = "PayTypeLink.PayType_id = :PayType_id";
				$params['PayType_id'] = $data['PayType_id'];
			}

			if (!empty($data['endDate'])) {
				$linkFilters[] = "coalesce(PayTypeLink.HTMedicalCareClassLink_begDate, :endDate) <= :endDate";
				$linkFilters[] = "coalesce(PayTypeLink.HTMedicalCareClassLink_endDate, :endDate) >= :endDate";
				$params['endDate'] = $data['endDate'];
			} else if (!empty($data['begDate'])) {
				$linkFilters[] = "coalesce(PayTypeLink.HTMedicalCareClassLink_begDate, :begDate) <= :begDate";
				$linkFilters[] = "coalesce(PayTypeLink.HTMedicalCareClassLink_endDate, :begDate) >= :begDate";
				$params['begDate'] = $data['begDate'];
			}

			//Если вид ВМП НЕ имеет связей ни с одним из видов оплаты, то считается, что он связан с видом оплаты «ОМС».
			$addFilter = "";
			$PayTypeOms_id = $this->getFirstResultFromQuery("
				select
					PayType_id as \"PayType_id\"
				from
					v_PayType
				where
					PayType_SysNick = 'oms'
			");

			if ($data['PayType_id'] == $PayTypeOms_id) {
				$addFilter .= "
					or not exists (select HTMedicalCareClassLink_id from dbo.v_HTMedicalCareClassLink PayTypeLink where PayTypeLink.HTMedicalCareClass_id = DHTMCC.HTMedicalCareClass_id limit 1)
				";
			}

			//учет изменений схемы: схема r2 -> dbo
			$filterList[] = "(
					exists (
						select HTMedicalCareClassLink_id
						from dbo.v_HTMedicalCareClassLink PayTypeLink
						where " . implode(' and ', $linkFilters) . "
						limit 1
					)
					{$addFilter}
				)
			";
		}

		if (!empty($data['endDate'])) {
			$filterList[] = "coalesce(DHTMCC.HTMedicalCareClass_begDate, :endDate) <= :endDate";
			$filterList[] = "coalesce(DHTMCC.HTMedicalCareClass_endDate, :endDate) >= :endDate";
			$params['endDate'] = $data['endDate'];
		} else if (!empty($data['begDate'])) {
			$filterList[] = "coalesce(DHTMCC.HTMedicalCareClass_begDate, :begDate) <= :begDate";
			$filterList[] = "coalesce(DHTMCC.HTMedicalCareClass_endDate, :begDate) >= :begDate";
			$params['begDate'] = $data['begDate'];
		} else {
			$filterList[] = "DHTMCC.HTMedicalCareClass_begDate <= cast(dbo.tzGetDate() as date)";
			$filterList[] = "(DHTMCC.HTMedicalCareClass_endDate is null or DHTMCC.HTMedicalCareClass_endDate > cast(dbo.tzGetDate() as date))";
		}

		if (!empty($data['Diag_ids'])) {
			$diag_arr = json_decode($data['Diag_ids'], true);
			if (is_array($diag_arr) && count($diag_arr) > 0) {
				$filterList[] = "exists (
					select HTMedicalCareDiag_id
					from dbo.v_HTMedicalCareDiag DiagLink
					where DiagLink.HTMedicalCareClass_id = DHTMCC.HTMedicalCareClass_id
						and DiagLink.Diag_id in (".implode(',',$diag_arr).")
					 limit 1
					)
				";
			}
		}

		$query = "
			select
				DHTMCC.HTMedicalCareClass_id as \"HTMedicalCareClass_id\",
				DHTMCC.HTMedicalCareClass_Code as \"HTMedicalCareClass_Code\",
				DHTMCC.HTMedicalCareClass_Name as \"HTMedicalCareClass_Name\",
				HTMPM.HTMedicalPersonModel_Name as \"HTMedicalPersonModel_Name\"
			from
				dbo.v_HTMedicalCareClass DHTMCC
				left join  fed.v_HTMedicalCareClass FHTMCC on FHTMCC.HTMedicalCareClass_id = DHTMCC.HTMedicalCareClass_fid
				left join fed.v_HTMedicalPersonModel HTMPM on FHTMCC.HTMedicalPersonModel_id = HTMPM.HTMedicalPersonModel_id
			where
				" . implode(' and ', $filterList) . "
		";

		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if ( !is_object($result) ) {
			return false;
		}

		$response = $result->result('array');

		if ( $data['session']['region']['nick'] == 'ufa' && !empty($data['endDate']) && count($response) == 0 ) {
			// Для Уфы - повторный запрос
			// https://redmine.swan.perm.ru/issues/54092
			// 3) Доработать в формах фильтрацию по виду оплаты указанному в поле “Вид оплаты”. При фильтрации надо учитывать надо дату выписки, если пусто,
			// то дату госпитализации.
			$data['endDate'] = null;
			return $this->loadHTMedicalCareClassList($data);
		}
		else {
			return $response;
		}
	}

	/**
	 * Возвращает список видов медицинской помощи по источнику финансирования и дате
	 */
	function loadHTMedicalCareClassListByHTFinance($data)
	{
		$params = array();
		$eHtmfCode = !empty($data['HTMFinance_Code']);
		$eEndDate = !empty($data['endDate']);

		$query = "
			SELECT DISTINCT
				htmcc.HTMedicalCareClass_id as \"HTMedicalCareClass_id\",
				htmcc.HTMedicalCareClass_Code as \"HTMedicalCareClass_Code\",
				htmcc.HTMedicalCareClass_Name as \"HTMedicalCareClass_Name\"
			FROM dbo.v_HTMedicalCareClass htmcc
			WHERE 1 = 1";

		if ($eEndDate)
		{
			$params['endDate'] = $data['endDate'];

			$query .= "
				AND COALESCE(htmcc.HTMedicalCareClass_begDate, :endDate) <= :endDate
				AND COALESCE(htmcc.HTMedicalCareClass_endDate, :endDate) >= :endDate";

			if ($eHtmfCode)
			{
				$params['HTMFinance_Code'] = $data['HTMFinance_Code'];

				$query .= "
					AND (htmcc.HTMedicalCareClass_id IN
						(SELECT htmccl.HTMedicalCareClass_id
							FROM dbo.v_HTMedicalCareClassLink htmccl
								INNER JOIN v_HTMFinancePayType htmfpt
									ON htmccl.PayType_id = htmfpt.PayType_id
								INNER JOIN dbo.v_HTMFinance htmf
									ON htmfpt.HTMFinance_id = htmf.HTMFinance_id
							WHERE htmf.HTMFinance_Code = :HTMFinance_Code
								AND COALESCE(htmccl.HTMedicalCareClassLink_begDate, :endDate) <= :endDate
								AND COALESCE(htmccl.HTMedicalCareClassLink_endDate, :endDate) >= :endDate
								AND COALESCE(htmf.HTMFinance_begDate, :endDate) <= :endDate
								AND COALESCE(htmf.HTMFinance_endDate, :endDate) >= :endDate)";

				if ($eHtmfCode == 3)  // ОМС
					$query .= "
						OR
						htmcc.HTMedicalCareClass_id NOT IN
						(SELECT htmccl.HTMedicalCareClass_id
							FROM dbo.v_HTMedicalCareClassLink htmccl
								INNER JOIN v_HTMFinancePayType htmfpt
									ON htmccl.PayType_id = htmfpt.PayType_id
							WHERE COALESCE(htmccl.HTMedicalCareClassLink_begDate, :endDate) <= :endDate
								AND COALESCE(htmccl.HTMedicalCareClassLink_endDate, :endDate) >= :endDate))";
				else
					$query .= ")";
			}
		}

		$result = $this->db->query($query, $params);

		if (!is_object($result))
			return (false);

		return ($result->result('array'));
	}

	/**
	 * Возвращает список методов медицинской помощи
	 */
	function loadHTMedicalCareClassListFed($data)
	{
		$params = array();
		$filter='';
		if ( !empty($data['query']) )
		{
			$params['query'] = '%'. $data['query'] . '%';
			$filter .= " and (cast(HTMCC.HTMedicalCareClass_Code as varchar) ||' '|| RTrim(HTMCC.HTMedicalCareClass_Name)) LIKE :query ";
		}
		$query = "
			select
				HTMCC.HTMedicalCareClass_id as \"HTMedicalCareClass_id\",
				HTMCC.HTMedicalCareClass_Code as \"HTMedicalCareClass_Code\",
				HTMCC.HTMedicalCareClass_Name as \"HTMedicalCareClass_Name\",
				HTMCC.HTMedicalCareType_id as \"HTMedicalCareType_id\",
				HTMCC.HTMedicalCareClass_GroupCode as \"HTMedicalCareClass_GroupCode\"
			from
				fed.v_HTMedicalCareClass HTMCC
			where
				HTMCC.HTMedicalCareClass_begDate <= cast(dbo.tzGetDate() as date)
				and (HTMCC.HTMedicalCareClass_endDate is null or HTMCC.HTMedicalCareClass_endDate > cast(dbo.tzGetDate() as date))
				".$filter."
		";

		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Возвращает список видов медицинской помощи
	 */
	function loadHTMedicalCareTypeListFed($data)
	{
		$params = array();
		$filter='';
		if ( !empty($data['query']) )
		{
			$params['query'] = '%'. $data['query'] . '%';
			$filter .= " and (cast(HTMCT.HTMedicalCareType_Code as varchar) ||' '|| RTrim(HTMCT.HTMedicalCareType_Name)) LIKE :query ";
		}
		$query = "
			select
				HTMCT.HTMedicalCareType_id as \"HTMedicalCareType_id\",
				HTMCT.HTMedicalCareType_Code as \"HTMedicalCareType_Code\",
				HTMCT.HTMedicalCareType_Name as \"HTMedicalCareType_Name\"
			from
				fed.v_HTMedicalCareType HTMCT
			where
				HTMCT.HTMedicalCareType_begDate <= cast(dbo.tzGetDate() as date)
				and (HTMCT.HTMedicalCareType_endDate is null or HTMCT.HTMedicalCareType_endDate > cast(dbo.tzGetDate() as date))
				".$filter."
		";

		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}
}