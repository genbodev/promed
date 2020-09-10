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

class HTMedicalCare_model extends swModel {
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
				$linkFilters[] = "ISNULL(PayTypeLink.HTMedicalCareClassLink_begDate, :endDate) <= :endDate";
				$linkFilters[] = "ISNULL(PayTypeLink.HTMedicalCareClassLink_endDate, :endDate) >= :endDate";
				$params['endDate'] = $data['endDate'];
			} else if (!empty($data['begDate'])) {
				$linkFilters[] = "ISNULL(PayTypeLink.HTMedicalCareClassLink_begDate, :begDate) <= :begDate";
				$linkFilters[] = "ISNULL(PayTypeLink.HTMedicalCareClassLink_endDate, :begDate) >= :begDate";
				$params['begDate'] = $data['begDate'];
			}

			//Если вид ВМП НЕ имеет связей ни с одним из видов оплаты, то считается, что он связан с видом оплаты «ОМС».
			$addFilter = "";
			$PayTypeOms_id = $this->getFirstResultFromQuery("
				select
					PayType_id
				from
					v_PayType with (nolock)
				where
					PayType_SysNick = 'oms'
			");

			if ($data['PayType_id'] == $PayTypeOms_id) {
				$addFilter .= "
					or not exists ( select top 1 HTMedicalCareClassLink_id from dbo.v_HTMedicalCareClassLink PayTypeLink with(nolock) where PayTypeLink.HTMedicalCareClass_id = DHTMCC.HTMedicalCareClass_id)
				";
			}

			//учет изменений схемы: схема r2 -> dbo
			$filterList[] = "(
					exists (
						select top 1 HTMedicalCareClassLink_id
						from dbo.v_HTMedicalCareClassLink PayTypeLink with(nolock)
						where " . implode(' and ', $linkFilters) . "
					)
					{$addFilter}
				)
			";
		}

		if (!empty($data['endDate'])) {
			$filterList[] = "ISNULL(DHTMCC.HTMedicalCareClass_begDate, :endDate) <= :endDate";
			$filterList[] = "ISNULL(DHTMCC.HTMedicalCareClass_endDate, :endDate) >= :endDate";
			$params['endDate'] = $data['endDate'];
		} else if (!empty($data['begDate'])) {
			$filterList[] = "ISNULL(DHTMCC.HTMedicalCareClass_begDate, :begDate) <= :begDate";
			$filterList[] = "ISNULL(DHTMCC.HTMedicalCareClass_endDate, :begDate) >= :begDate";
			$params['begDate'] = $data['begDate'];
		} else {
			$filterList[] = "DHTMCC.HTMedicalCareClass_begDate <= cast(dbo.tzGetDate() as date)";
			$filterList[] = "(DHTMCC.HTMedicalCareClass_endDate is null or DHTMCC.HTMedicalCareClass_endDate > cast(dbo.tzGetDate() as date))";
		}

		if (!empty($data['Diag_ids'])) {
			$diag_arr = json_decode($data['Diag_ids'], true);
			if (is_array($diag_arr) && count($diag_arr) > 0) {
				$filterList[] = "exists (
					select top 1 HTMedicalCareDiag_id
					from dbo.v_HTMedicalCareDiag DiagLink with(nolock)
					where DiagLink.HTMedicalCareClass_id = DHTMCC.HTMedicalCareClass_id
						and DiagLink.Diag_id in (".implode(',',$diag_arr).")
					)
				";
			}
		}

		$query = "
			select
				DHTMCC.HTMedicalCareClass_id,
				DHTMCC.HTMedicalCareClass_Code,
				DHTMCC.HTMedicalCareClass_Name,
				HTMPM.HTMedicalPersonModel_Name as HTMedicalPersonModel_Name
			from
				dbo.v_HTMedicalCareClass DHTMCC with(nolock)
				left join  fed.v_HTMedicalCareClass FHTMCC with(nolock) on FHTMCC.HTMedicalCareClass_id = DHTMCC.HTMedicalCareClass_fid
				left join fed.v_HTMedicalPersonModel HTMPM with(nolock) on FHTMCC.HTMedicalPersonModel_id = HTMPM.HTMedicalPersonModel_id
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
				htmcc.HTMedicalCareClass_id,
				htmcc.HTMedicalCareClass_Code,
				htmcc.HTMedicalCareClass_Name
			FROM dbo.v_HTMedicalCareClass htmcc WITH (NOLOCK)
			WHERE 1 = 1";

		if ($eEndDate)
		{
			$params['endDate'] = $data['endDate'];

			$query .= "
				AND ISNULL(htmcc.HTMedicalCareClass_begDate, :endDate) <= :endDate
				AND ISNULL(htmcc.HTMedicalCareClass_endDate, :endDate) >= :endDate";

			if ($eHtmfCode)
			{
				$params['HTMFinance_Code'] = $data['HTMFinance_Code'];

				$query .= "
					AND (htmcc.HTMedicalCareClass_id IN
						(SELECT htmccl.HTMedicalCareClass_id
							FROM dbo.v_HTMedicalCareClassLink htmccl WITH (NOLOCK)
								INNER JOIN v_HTMFinancePayType htmfpt WITH (NOLOCK)
									ON htmccl.PayType_id = htmfpt.PayType_id
								INNER JOIN dbo.v_HTMFinance htmf WITH (NOLOCK)
									ON htmfpt.HTMFinance_id = htmf.HTMFinance_id
							WHERE htmf.HTMFinance_Code = :HTMFinance_Code
								AND ISNULL(htmccl.HTMedicalCareClassLink_begDate, :endDate) <= :endDate
								AND ISNULL(htmccl.HTMedicalCareClassLink_endDate, :endDate) >= :endDate
								AND ISNULL(htmf.HTMFinance_begDate, :endDate) <= :endDate
								AND ISNULL(htmf.HTMFinance_endDate, :endDate) >= :endDate)";

				if ($eHtmfCode == 3)  // ОМС
					$query .= "
						OR
						htmcc.HTMedicalCareClass_id NOT IN
						(SELECT htmccl.HTMedicalCareClass_id
							FROM dbo.v_HTMedicalCareClassLink htmccl WITH (NOLOCK)
								INNER JOIN v_HTMFinancePayType htmfpt WITH (NOLOCK)
									ON htmccl.PayType_id = htmfpt.PayType_id
							WHERE ISNULL(htmccl.HTMedicalCareClassLink_begDate, :endDate) <= :endDate
								AND ISNULL(htmccl.HTMedicalCareClassLink_endDate, :endDate) >= :endDate))";
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
			$filter .= " and (cast(HTMCC.HTMedicalCareClass_Code as varchar) +' '+ RTrim(HTMCC.HTMedicalCareClass_Name)) LIKE :query ";
		}
		$query = "
			select
				HTMCC.HTMedicalCareClass_id,
				HTMCC.HTMedicalCareClass_Code,
				HTMCC.HTMedicalCareClass_Name,
				HTMCC.HTMedicalCareType_id,
				HTMCC.HTMedicalCareClass_GroupCode
			from
				fed.v_HTMedicalCareClass HTMCC with (nolock)
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
			$filter .= " and (cast(HTMCT.HTMedicalCareType_Code as varchar) +' '+ RTrim(HTMCT.HTMedicalCareType_Name)) LIKE :query ";
		}
		$query = "
			select
				HTMCT.HTMedicalCareType_id,
				HTMCT.HTMedicalCareType_Code,
				HTMCT.HTMedicalCareType_Name
			from
				fed.v_HTMedicalCareType HTMCT with (nolock)
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