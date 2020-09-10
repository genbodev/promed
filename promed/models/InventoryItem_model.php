<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * InventoryItem_model - модель для работы с ТМЦ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			27.11.2014
 */

class InventoryItem_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение списка ТМЦ
	 */
	function loadInventoryItemList($data) {
		$params = array();
		$filters = "(1=1)";
		$join = "";
		$with = "";
		$withArr = array();

		if (!empty($data['InventoryItem_id'])) {
			$filters .= " and II.InventoryItem_id = :InventoryItem_id";
			$params['InventoryItem_id'] = $data['InventoryItem_id'];
		} else {
			if (!empty($data['query'])) {
				$filters .= " and II.InventoryItem_Name like :InventoryItem_Name+'%'";
				$params['InventoryItem_Name'] = $data['query'];
			}

			if (!empty($data['Storage_id'])) {	//Должны быть остатки на складе
				$withFilters = " and Sh.Storage_id = :Storage_id";
				$params['Storage_id'] = $data['Storage_id'];

				if (!empty($data['Date'])) {	//Учитывать дату
					$withFilters .= " and I.Invoice_Date <= :Date";
					$params['Date'] = $data['Date'];
				}
				if (!empty($data['InvoicePosition_id'])) {	//НЕ учитывать указанную позицию накладной
					$withFilters .= " and IP.InvoicePosition_id <> :InvoicePosition_id";
					$params['InvoicePosition_id'] = $data['InvoicePosition_id'];
				}

				$withArr[] = "
				sum_list as (
					select
						Sh.InventoryItem_id,
						SUM(IP.InvoicePosition_Count*isnull(IP.InvoicePosition_Coeff,1)) as Summa
					from
						v_InvoicePosition IP with(nolock)
						inner join v_Shipment Sh with(nolock) on Sh.Shipment_id = IP.Shipment_id
						inner join v_Invoice I with(nolock) on I.Invoice_id = IP.Invoice_id
					where
						I.InvoiceType_id = 1
						{$withFilters}
					group by
						Sh.InventoryItem_id
				)";
				$withArr[] = "
				reserved_list as (
					select
						Sh.InventoryItem_id,
						SUM(IP.InvoicePosition_Count*isnull(IP.InvoicePosition_Coeff,1)) as Summa
					from
						v_InvoicePosition IP with(nolock)
						inner join v_Shipment Sh with(nolock) on Sh.Shipment_id = IP.Shipment_id
						inner join v_Invoice I with(nolock) on I.Invoice_id = IP.Invoice_id
					where
						I.InvoiceType_id = 2
						{$withFilters}
					group by
						Sh.InventoryItem_id
				)";

				$join .= " left join sum_list sl with(nolock) on sl.InventoryItem_id = II.InventoryItem_id";
				$join .= " left join reserved_list rl with(nolock) on rl.InventoryItem_id = II.InventoryItem_id";
				$filters .= " and isnull(sl.Summa,0)-isnull(rl.Summa,0) > 0";
			}
		}

		$with = count($withArr)>0 ? "with\n".implode(",",$withArr) : '';

		$query = "
			{$with}
			select
				II.InventoryItem_id,
				II.InventoryItem_Code,
				II.InventoryItem_Name,
				II.InventoryItemType_id,
				II.Okei_id
			from
				v_InventoryItem II with(nolock)
				{$join}
			where
				{$filters}
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}
}