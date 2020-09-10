<?php
defined('BASEPATH') or die ('No direct script access allowed');

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
class InventoryItem_model extends SwPgModel
{
    /**
     * Получение списка ТМЦ
     * @param $data
     * @return array|false
     */
	public function loadInventoryItemList($data)
    {
		$params = [];
		$filters = ["(1=1)"];
		$join = "";
		$withArr = [];

		if (!empty($data['InventoryItem_id'])) {
			$filters[] = "II.InventoryItem_id = :InventoryItem_id";
			$params['InventoryItem_id'] = $data['InventoryItem_id'];
		} else {
			if (!empty($data['query'])) {
				$filters[] = "II.InventoryItem_Name ilike :InventoryItem_Name||'%'";
				$params['InventoryItem_Name'] = $data['query'];
			}

			if (!empty($data['Storage_id'])) {	//Должны быть остатки на складе
				$withFilters = ["Sh.Storage_id = :Storage_id"];
				$params['Storage_id'] = $data['Storage_id'];

				if (!empty($data['Date'])) {	//Учитывать дату
					$withFilters[] = "I.Invoice_Date <= :Date";
					$params['Date'] = $data['Date'];
				}
				if (!empty($data['InvoicePosition_id'])) {	//НЕ учитывать указанную позицию накладной
					$withFilters[] = "IP.InvoicePosition_id <> :InvoicePosition_id";
					$params['InvoicePosition_id'] = $data['InvoicePosition_id'];
				}

				$withArr[] = "
                    sum_list as (
                        select
                            Sh.InventoryItem_id,
                            SUM(IP.InvoicePosition_Count * coalesce(IP.InvoicePosition_Coeff, 1)) as Summa
                        from
                            v_InvoicePosition IP
                            inner join v_Shipment Sh on Sh.Shipment_id = IP.Shipment_id
                            inner join v_Invoice I on I.Invoice_id = IP.Invoice_id
                        where
                            I.InvoiceType_id = 1
                            ".implode(' and ', $withFilters)."
                        group by
                            Sh.InventoryItem_id
				    )
				";
				$withArr[] = "
                    reserved_list as (
                        select
                            Sh.InventoryItem_id,
                            SUM(IP.InvoicePosition_Count * coalesce(IP.InvoicePosition_Coeff,1)) as Summa
                        from
                            v_InvoicePosition IP
                            inner join v_Shipment Sh on Sh.Shipment_id = IP.Shipment_id
                            inner join v_Invoice I on I.Invoice_id = IP.Invoice_id
                        where
                            I.InvoiceType_id = 2
                            {$withFilters}
                        group by
                            Sh.InventoryItem_id
                    )
                ";

				$join .= " left join sum_list sl on sl.InventoryItem_id = II.InventoryItem_id";
				$join .= " left join reserved_list rl on rl.InventoryItem_id = II.InventoryItem_id";
				$filters [] = "coalesce(sl.Summa, 0) - coalesce(rl.Summa, 0) > 0";
			}
		}

		$with = count($withArr)>0 ? "with\n".implode(",",$withArr) : '';

		$query = "
			{$with}
			select
				II.InventoryItem_id as \"InventoryItem_id\",
				II.InventoryItem_Code as \"InventoryItem_Code\",
				II.InventoryItem_Name as \"InventoryItem_Name\",
				II.InventoryItemType_id as \"InventoryItemType_id\",
				II.Okei_id as \"Okei_id\"
			from
				v_InventoryItem II
				{$join}
			where" .implode(' and ', $filters);
		
		return $this->queryResult($query, $params);
	}
}