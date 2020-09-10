<?php defined('BASEPATH') or die ('No direct script access allowed');

class WhsDocumentOrderReserve_model extends SwPgModel {
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
	}

	/**
	 * Загрузка списка
	 */
	function loadList($filter) {
		$where = array();
		$p = array();
		if ( isset($filter['WhsDocumentUc_Date_Range'][0]) )
		{
			$where[] = "cast(WDOR.WhsDocumentUc_Date as date) >= cast(:WhsDocumentUc_Date_Beg as date)";
			$p['WhsDocumentUc_Date_Beg'] = $filter['WhsDocumentUc_Date_Range'][0];
		}
		if ( isset($filter['WhsDocumentUc_Date_Range'][1]) )
		{
			$where[] = "cast(WDOR.WhsDocumentUc_Date as date) <= cast(:WhsDocumentUc_Date_End as date)";
			$p['WhsDocumentUc_Date_End'] = $filter['WhsDocumentUc_Date_Range'][1];
		}
		if (isset($filter['WhsDocumentCostItemType_id']) && $filter['WhsDocumentCostItemType_id']) {
			$where[] = 'WDOR.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id';
			$p['WhsDocumentCostItemType_id'] = $filter['WhsDocumentCostItemType_id'];
		}
		if (isset($filter['DrugFinance_id']) && $filter['DrugFinance_id']) {
			$where[] = 'WDOR.DrugFinance_id = :DrugFinance_id';
			$p['DrugFinance_id'] = $filter['DrugFinance_id'];
		}
		if (isset($filter['WhsDocumentType_id']) && $filter['WhsDocumentType_id']) {
			$where[] = 'WDOR.WhsDocumentType_id = :WhsDocumentType_id';
			$p['WhsDocumentType_id'] = $filter['WhsDocumentType_id'];
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		$q = "
			SELECT
				WDOR.WhsDocumentUc_id as \"WhsDocumentUc_id\",
				WDOR.WhsDocumentOrderReserve_id as \"WhsDocumentOrderReserve_id\",
				WDT.WhsDocumentType_id as \"WhsDocumentType_id\",
				WDT.WhsDocumentType_Name as \"WhsDocumentType_Name\",
				WDOR.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				to_char(cast(WDOR.WhsDocumentUc_Date as timestamp(3)),'dd.mm.yyyy') as \"WhsDocumentUc_Date\",
				DF.DrugFinance_Name as \"DrugFinance_Name\",
				WDCIT.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
				WDOR.WhsDocumentUc_Sum as \"WhsDocumentUc_Sum\",
				to_char(cast(WDOR.WhsDocumentOrderReserve_updDT as timestamp(3)),'dd.mm.yyyy') || ' ' || SUBSTRING(to_char(cast(WhsDocumentOrderReserve_updDT as timestamp(3)),'hh:mm:ss'),1,5) as \"WhsDocumentUc_updDT\",
				WDST.WhsDocumentStatusType_id as \"WhsDocumentStatusType_id\",
				WDST.WhsDocumentStatusType_Name as \"WhsDocumentStatusType_Name\"
			FROM
				v_WhsDocumentOrderReserve WDOR
				LEFT JOIN v_WhsDocumentType WDT ON WDT.WhsDocumentType_id = WDOR.WhsDocumentType_id
				LEFT JOIN v_DrugFinance DF ON DF.DrugFinance_id = WDOR.DrugFinance_id
				LEFT JOIN v_WhsDocumentCostItemType WDCIT ON WDCIT.WhsDocumentCostItemType_id = WDOR.WhsDocumentCostItemType_id
				LEFT JOIN v_WhsDocumentStatusType WDST ON WDST.WhsDocumentStatusType_id = coalesce(WDOR.WhsDocumentStatusType_id, 1)
			$where_clause
		";
		$result = $this->db->query($q, $p);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка
	 */
	function load($filter) {
		$q = "
			SELECT
				WDOR.WhsDocumentUc_id as \"WhsDocumentUc_id\",
				WDOR.WhsDocumentOrderReserve_id as \"WhsDocumentOrderReserve_id\",
				WDOR.WhsDocumentType_id as \"WhsDocumentType_id\",
				WDOR.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				WDOR.WhsDocumentUc_Date as \"WhsDocumentUc_Date\",
				WDOR.WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
				WDOR.DrugFinance_id as \"DrugFinance_id\",
				WDOR.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",			
				WDOR.WhsDocumentOrderReserve_Percent as \"WhsDocumentOrderReserve_Percent\",
				WDOR.WhsDocumentUc_Sum as \"WhsDocumentUc_Sum\",
				coalesce(WDOR.WhsDocumentStatusType_id, 1) as \"WhsDocumentStatusType_id\",
				WDOR.DrugRequest_id as \"DrugRequest_id\",
				WDOR.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
				WDOR.Org_id as \"Org_id\"
			FROM
				v_WhsDocumentOrderReserve WDOR
			WHERE WhsDocumentUc_id = :WhsDocumentUc_id
		";
		$result = $this->db->query($q, array('WhsDocumentUc_id' => $filter['WhsDocumentUc_id']));
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение
	 */
	function save($data) {
		$procedure = 'p_WhsDocumentOrderReserve_ins';
		if ( $data['WhsDocumentUc_id'] > 0 ) {
			$procedure = 'p_WhsDocumentOrderReserve_upd';
		}
		$q = "
			select 
			    WhsDocumentUc_id as \"WhsDocumentUc_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from dbo." . $procedure . " (
				WhsDocumentUc_id := :WhsDocumentUc_id,
				WhsDocumentUc_Num := :WhsDocumentUc_Num,
				WhsDocumentUc_Name := :WhsDocumentUc_Name,
				WhsDocumentType_id := :WhsDocumentType_id,
				WhsDocumentUc_Date := :WhsDocumentUc_Date,
				WhsDocumentUc_Sum := :WhsDocumentUc_Sum,
				WhsDocumentOrderReserve_id := :WhsDocumentOrderReserve_id,
				DrugFinance_id := :DrugFinance_id,
				WhsDocumentCostItemType_id := :WhsDocumentCostItemType_id,
				WhsDocumentOrderReserve_Percent := :WhsDocumentOrderReserve_Percent,
				DrugRequest_id := :DrugRequest_id,
				WhsDocumentSupply_id := :WhsDocumentSupply_id,
				Org_id := :Org_id,
				pmUser_id := :pmUser_id
				)
		";
		$p = array(
			'WhsDocumentUc_id' => $data['WhsDocumentUc_id'],
			'WhsDocumentOrderReserve_id' => $data['WhsDocumentOrderReserve_id'],
			'WhsDocumentUc_Num' => $data['WhsDocumentUc_Num'],
			'WhsDocumentUc_Name' => $data['WhsDocumentUc_Name'],
			'WhsDocumentType_id' => $data['WhsDocumentType_id'],
			'WhsDocumentUc_Date' => $data['WhsDocumentUc_Date'],						
			'DrugFinance_id' => $data['DrugFinance_id'],
			'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id'],			
			'WhsDocumentOrderReserve_Percent' => $data['WhsDocumentOrderReserve_Percent'],
			'WhsDocumentUc_Sum' => $data['WhsDocumentUc_Sum'],
			'DrugRequest_id' => $data['DrugRequest_id'],
			'WhsDocumentSupply_id' => $data['WhsDocumentSupply_id'],
			'Org_id' => $data['Org_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		//die(getDebugSQL($q, $p));
		
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
		    $result = $r->result('array');
		} else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Получение номера
	 */
	function getWhsDocumentOrderReserveNumber($data) {
		$query = "
			select 
			    ObjectId as \"WhsDocumentUc_Num\"
			from xp_GenpmID ( 
			    ObjectName := 'WhsDocumentOrderReserve',
			    Lpu_id := :Lpu_id
			    )
		";
		$result = $this->db->query($query, array(
			'Lpu_id' => $data['Lpu_id'] > 0 ? $data['Lpu_id'] : null
		));

		if ( !is_object($result) ) {
			return false;
		}

        return $result->result('array');
	}

	/**
	 * Подписание
	 */
	function sign($data) {
        $error = array();
        $drug_array = array();

		// Стартуем транзакцию
        $this->beginTransaction();

        // Получаем идентификаторы субсчетов
        $query = "
            select
                (select SubAccountType_id from v_SubAccountType where SubAccountType_SysNick = 'available') as \"AvailableSubAccountType_id\", -- Доступно
                (select SubAccountType_id from v_SubAccountType where SubAccountType_SysNick = 'reserve') as \"ReserveSubAccountType_id\" -- Зарезервировано
        ";
        $common_data = $this->getFirstRowFromQuery($query);

        // Получаем данные документа
        $query = "
            select
                wdor.WhsDocumentUc_id as \"WhsDocumentUc_id\",
                wdor.Org_id as \"Org_id\",
                wdt.WhsDocumentType_Code as \"WhsDocumentType_Code\"
            from
                v_WhsDocumentOrderReserve wdor
                left join v_WhsDocumentType wdt on wdt.WhsDocumentType_id = wdor.WhsDocumentType_id
            where
                wdor.WhsDocumentOrderReserve_id = :WhsDocumentOrderReserve_id;
        ";
        $doc_data = $this->getFirstRowFromQuery($query, $data);
        if (empty($doc_data['WhsDocumentUc_id'])) {
            $error[] = 'Не удалось получить данные документа';
        }
		
		// Сформировать регистр остатков по субсчету Резерв
		// получаем строки из WhsDocumentOrderReserveDrug и регистра Доступно
        if (count($error) == 0) {
            $query = "
                select
                    dor.DrugOstatRegistry_Kolvo as \"DrugOstatRegistry_Kolvo\",
                    dor.Org_id as \"Org_id\",
                    dor.Contragent_id as \"Contragent_id\",
                    dor.DrugShipment_id as \"DrugShipment_id\",
                    wdord.Drug_id as \"Drug_id\",
                    d.Drug_Name as \"Drug_Name\",
                    wdord.Okei_id as \"Okei_id\",
                    wdord.WhsDocumentOrderReserveDrug_Kolvo as \"WhsDocumentOrderReserveDrug_Kolvo\",
                    wdord.WhsDocumentOrderReserveDrug_Price as \"WhsDocumentOrderReserveDrug_Price\",
                    wdord.WhsDocumentOrderReserveDrug_Price * wdord.WhsDocumentOrderReserveDrug_Kolvo as \"WhsDocumentUc_Sum\"
                from
                    v_WhsDocumentOrderReserveDrug wdord
                    left join rls.v_Drug d on d.Drug_id = wdord.Drug_id
                    inner join v_WhsDocumentSupply wds on wds.WhsDocumentUc_id = wdord.WhsDocumentUc_pid
                    inner join v_DrugShipment ds on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
                    left join v_DrugOstatRegistry dor on
                        dor.DrugShipment_id = ds.DrugShipment_id and
                        dor.Drug_id = wdord.Drug_id and
                        dor.DrugOstatRegistry_Cost = wdord.WhsDocumentOrderReserveDrug_Price and
                        dor.SubAccountType_id = :SubAccountType_id
                where
                    wdord.WhsDocumentOrderReserve_id = :WhsDocumentUc_id and
                    dor.Org_id = :Org_id and
                    wdord.Drug_id is not null and
                    wdord.WhsDocumentOrderReserveDrug_Kolvo > 0
            ";

            $result = $this->db->query($query, array(
                'WhsDocumentUc_id' => $doc_data['WhsDocumentUc_id'],
                'Org_id' => $doc_data['Org_id'],
                'SubAccountType_id' => $doc_data['WhsDocumentType_Code'] == 11 ? $common_data['AvailableSubAccountType_id'] : $common_data['ReserveSubAccountType_id'], // 11 - Распоряжение на включение в резерв
                'pmUser_id' => $data['pmUser_id']
            ));
            if ( is_object($result) ) {
                $drug_array = $result->result('array');
            } else {
                $error[] = 'Ошибка запроса получения данных из спецификации документа';
            }
        }

        if (count($drug_array) == 0) {
            $error[] = 'Нет остатков для списания';
        }

		// для каждой строки вызываем пересчет остатков
		foreach ($drug_array as $drug) {
            if (count($error) == 0) { //проверка количества медикамента
                if (empty($drug['DrugOstatRegistry_Kolvo']) || $drug['DrugOstatRegistry_Kolvo'] < $drug['WhsDocumentOrderReserveDrug_Kolvo']) {
                    $error[] = "На остатках недостаточное количество медикамента"." ".$drug['Drug_Name'];
                }
            }
            if (count($error) == 0) {
                // списание остатков
                $query = "
                    select 
                        Error_Code as \"Error_Code\",
                        Error_Message as \"Error_Msg\"
                    from xp_DrugOstatRegistry_count (
                        Contragent_id := :Contragent_id,
                        Org_id := :Org_id,
                        DrugShipment_id := :DrugShipment_id,
                        Drug_id := :Drug_id,
                        PrepSeries_id := NULL,
                        SubAccountType_id := :SubAccountType_id,
                        Okei_id := :Okei_id,
                        DrugOstatRegistry_Kolvo := :DrugOstatRegistry_Kolvo,
                        DrugOstatRegistry_Sum := :DrugOstatRegistry_Sum,
                        DrugOstatRegistry_Cost := :DrugOstatRegistry_Cost,
                        pmUser_id := :pmUser_id
                        )
                ";

                $kolvo = $drug['WhsDocumentOrderReserveDrug_Kolvo'];
                $sum = round($kolvo * $drug['WhsDocumentOrderReserveDrug_Price'], 2);

                $params = array(
                    'Contragent_id' => $drug['Contragent_id'],
                    'Org_id' => $drug['Org_id'],
                    'DrugShipment_id' => $drug['DrugShipment_id'],
                    'Drug_id' => $drug['Drug_id'],
                    'SubAccountType_id' => $doc_data['WhsDocumentType_Code'] == 11 ? $common_data['AvailableSubAccountType_id'] : $common_data['ReserveSubAccountType_id'], // 11 - Распоряжение на включение в резерв
                    'Okei_id' => $drug['Okei_id'],
                    'DrugOstatRegistry_Kolvo' => $kolvo*(-1),
                    'DrugOstatRegistry_Sum' => $sum*(-1),
                    'DrugOstatRegistry_Cost' => $drug['WhsDocumentOrderReserveDrug_Price'],
                    'pmUser_id' => $data['pmUser_id']
                );

                $result = $this->getFirstRowFromQuery($query, $params);
                if (!empty($result)) {
                    if (!empty($result['Error_Msg'])) {
                        $error[] = 'Ошибка создания регистра резерва1';
                    }
                } else {
                    $error[] = 'Ошибка запроса создания регистра резерва1';
                }

                // зачисление остатков
                // используется прежний запрос изменяются толкьо знаки суммы и количества, а также субсчет
                $params['DrugOstatRegistry_Kolvo'] = $kolvo;
                $params['DrugOstatRegistry_Sum'] = $sum;
                $params['SubAccountType_id'] = $doc_data['WhsDocumentType_Code'] == 11 ? $common_data['ReserveSubAccountType_id'] : $common_data['AvailableSubAccountType_id']; // 11 - Распоряжение на включение в резерв

                $result = $this->getFirstRowFromQuery($query, $params);
                if (!empty($result)) {
                    if (!empty($result['Error_Msg'])) {
                        $error[] = 'Ошибка создания регистра резерва';
                    }
                } else {
                    $error[] = 'Ошибка запроса создания регистра резерва';
                }
            }
		}

        // Обновляем статус документа
        if (count($error) == 0) {
            $query = "
                select 
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
                from p_WhsDocumentUc_sign (
                    WhsDocumentUc_id := :WhsDocumentUc_id,
                    WhsDocumentStatusType_id := 2,
                    pmUser_id := :pmUser_id
                    )
            ";
            $result = $this->getFirstRowFromQuery($query, array(
                'WhsDocumentUc_id' => $doc_data['WhsDocumentUc_id'],
                'pmUser_id' => $data['pmUser_id']
            ));
            if ($result) {
                if (!empty($$result['Error_Msg'])) {
                    $error[] = 'Ошибка обновления статуса документа';
                }
            } else {
                $error[] = 'Ошибка запроса обновления статуса документа';
            }
        }

        if (count($error) > 0) {
            $this->rollbackTransaction();
            return array(0 => array('Error_Msg' => $error[0]));
        } else {
            $this->commitTransaction();
            return array(0 => array('Error_Msg' => ''));
        }
	}
	
	/**
	 * Удаление
	 */
	function delete($data) {
		$q = "
			delete from
				WhsDocumentOrderReserveDrug
			where
				WhsDocumentOrderReserve_id = :WhsDocumentOrderReserve_id
		";
		$r = $this->db->query($q, array(
			'WhsDocumentOrderReserve_id' => $data['WhsDocumentUc_id']
		));
	
		$q = "
            select 
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from dbo.p_WhsDocumentOrderReserve_del (
				WhsDocumentOrderReserve_id := :WhsDocumentOrderReserve_id
				)
		";
		$r = $this->db->query($q, array(
			'WhsDocumentOrderReserve_id' => $data['WhsDocumentUc_id']
		));
		if ( is_object($r) ) {
			return $r->result('array');
		} else {
			return false;
		}
	
	}

    /**
     * Загрузка комбобокса для выбора сводной заявки
     */
    function loadConsolidatedDrugRequestCombo($filter) {
        $where = array();
        $params = array();

        if (!empty($filter['DrugRequest_id'])) {
            $where[] = "dr.DrugRequest_id = :DrugRequest_id";
            $params['DrugRequest_id'] = $filter['DrugRequest_id'];
        } else {
            $where[] = "drc.DrugRequestCategory_SysNick = 'svod'"; //Сводная заявка
            $where[] = "drs.DrugRequestStatus_Code = 3"; //3 - Утвержденная

            //Непонятно как фильтровать по Org_id пока попросили выбирать только заявки с пустым Lpu_id
            /*if (!empty($filter['Org_id'])) {
                $where[] = "dr.Org_id = :Org_id";
                $params['Org_id'] = $filter['Org_id'];
            }*/
            $where[] = "dr.Lpu_id is null";

            if (!empty($filter['query'])) {
                $where[] = "dr.DrugRequest_Name ilike :query";
                $params['query'] = '%'.$filter['query'].'%';
            }
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = 'where '.$where_clause;
        }

        $query = "
			select
                dr.DrugRequest_id as \"DrugRequest_id\",
                drp.DrugRequestPeriod_id as \"DrugRequestPeriod_id\",
                wdcit.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
                df.DrugFinance_id as \"DrugFinance_id\",
                coalesce(dr.DrugRequest_Name, '') as \"DrugRequest_Name\",
                coalesce(drp.DrugRequestPeriod_Name, '') as \"DrugRequestPeriod_Name\",
                coalesce(wdcit.WhsDocumentCostItemType_Name, '') as \"WhsDocumentCostItemType_Name\",
                coalesce(df.DrugFinance_Name, '') as \"DrugFinance_Name\"
            from
                v_DrugRequest dr
                left join v_DrugRequestCategory drc on drc.DrugRequestCategory_id = dr.DrugRequestCategory_id
                left join v_DrugRequestStatus drs on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
                left join v_DrugRequestPeriod drp on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
                left join v_PersonRegisterType prt on prt.PersonRegisterType_id = dr.PersonRegisterType_id
                LEFT JOIN LATERAL (
                    select
                        i_df.DrugFinance_id as DrugFinance_id,
                        i_df.DrugFinance_Name as DrugFinance_Name
                    from
                        v_DrugRequestPurchaseSpec i_drps
                        inner join v_DrugFinance i_df on i_df.DrugFinance_id = i_drps.DrugFinance_id
                    where
                        i_drps.DrugRequest_id = dr.DrugRequest_id and
                        i_drps.DrugFinance_id is not null
                    order by
                        i_drps.DrugRequestPurchaseSpec_id
                    limit 1
                ) df ON TRUE
                LEFT JOIN LATERAL (
                    select
                        i_wdcit.WhsDocumentCostItemType_id as WhsDocumentCostItemType_id,
                        i_wdcit.WhsDocumentCostItemType_Name as WhsDocumentCostItemType_Name
                    from
                        v_WhsDocumentCostItemType i_wdcit
                    where
                        i_wdcit.PersonRegisterType_id = prt.PersonRegisterType_id and
                        i_wdcit.DrugFinance_id = df.DrugFinance_id
                    order by
                        i_wdcit.WhsDocumentCostItemType_id
                    limit 1
                ) wdcit ON TRUE
			$where_clause
			limit 250
		";

        $result = $this->db->query($query, $params);
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка комбобокса для выбора контракта
     */
    function loadWhsDocumentSupplyCombo($filter) {
        $where = array();
        $params = array();

        if (!empty($filter['WhsDocumentSupply_id']) || !empty($filter['WhsDocumentUc_id'])) {
            if (!empty($filter['WhsDocumentSupply_id'])) {
                $where[] = "wds.WhsDocumentSupply_id = :WhsDocumentSupply_id";
                $params['WhsDocumentSupply_id'] = $filter['WhsDocumentSupply_id'];
            } else {
                $where[] = "wds.WhsDocumentUc_id = :WhsDocumentUc_id";
                $params['WhsDocumentUc_id'] = $filter['WhsDocumentUc_id'];
            }
        } else {
            $where[] = "(
                cast(wdt.WhsDocumentType_Code as int) = 18 or -- 18 - Контракт ввода остатков
                (
                    cast(wdt.WhsDocumentType_Code as int) in (1, 3, 6) and -- 1 - Договор поставки; 3 - Контракт на поставку; 6 - Контракт на поставку и отпуск.
                    cast(wdst.WhsDocumentStatusType_Code as int) = 2 -- 2 - Действующий
                )
            )";

            if (!empty($filter['DrugRequest_id'])) {
                $where[] = "(
                    wds.WhsDocumentSupply_id in (
                        select
                            i_wdss.WhsDocumentSupply_id as WhsDocumentSupply_id
                        from
                            v_DrugRequestPurchaseSpec i_drps
                            left join v_DrugRequestExec i_dre on i_dre.DrugRequestPurchaseSpec_id = i_drps.DrugRequestPurchaseSpec_id
                            inner join v_WhsDocumentSupplySpec i_wdss on i_wdss.WhsDocumentSupplySpec_id = i_dre.WhsDocumentSupplySpec_id
                        where
                            i_drps.DrugRequest_id = :DrugRequest_id
                    ) or
                    wds.WhsDocumentSupply_id in (
                        select
                            i_wds.WhsDocumentSupply_id as WhsDocumentSupply_id
                        from
                            v_DrugRequestPurchaseSpec i_drps
                            left join v_WhsDocumentProcurementRequestSpec i_wdprs on i_wdprs.DrugRequestPurchaseSpec_id = i_drps.DrugRequestPurchaseSpec_id
                            left join v_WhsDocumentProcurementRequest i_wdpr on i_wdpr.WhsDocumentProcurementRequest_id = i_wdprs.WhsDocumentProcurementRequest_id
                            inner join v_WhsDocumentSupply i_wds on i_wds.WhsDocumentUc_pid = i_wdpr.WhsDocumentUc_id
                        where
                            i_drps.DrugRequest_id = :DrugRequest_id
                    )
                )";
                $params['DrugRequest_id'] = $filter['DrugRequest_id'];
            }

            if ($filter['DrugFinance_id'] > 0) {
                $where[] = "wds.DrugFinance_id = :DrugFinance_id";
                $params['DrugFinance_id'] = $filter['DrugFinance_id'];
            }

            if ($filter['WhsDocumentCostItemType_id'] > 0) {
                $where[] = "wds.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
                $params['WhsDocumentCostItemType_id'] = $filter['WhsDocumentCostItemType_id'];
            }

            if ($filter['Org_cid'] > 0) {
                $where[] = "wds.Org_cid = :Org_cid";
                $params['Org_cid'] = $filter['Org_cid'];
            }

            if (!empty($filter['SubAccountType_SysNick']) && $filter['DrugOstatRegistry_Org_id'] > 0) {
                $where[] = "wds.WhsDocumentSupply_id in (
                    select
                        i_ds.WhsDocumentSupply_id
                    from
                        v_DrugOstatRegistry i_dor
                        left join v_SubAccountType i_sat on i_sat.SubAccountType_id = i_dor.SubAccountType_id
                        left join v_DrugShipment i_ds on i_ds.DrugShipment_id = i_dor.DrugShipment_id
                    where
                        i_dor.Org_id = :DrugOstatRegistry_Org_id and
                        i_dor.DrugOstatRegistry_Kolvo > 0 and
                        i_sat.SubAccountType_SysNick = :SubAccountType_SysNick
                )";
                $params['SubAccountType_SysNick'] = $filter['SubAccountType_SysNick'];
                $params['DrugOstatRegistry_Org_id'] = $filter['DrugOstatRegistry_Org_id'];
            }

            if (!empty($filter['query'])) {
                $where[] = "wds.WhsDocumentUc_Num like :query";
                $params['query'] = '%'.$filter['query'].'%';
            }
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = 'where '.$where_clause;
        }

        $query = "
			select
                wds.WhsDocumentUc_id as \"WhsDocumentUc_id\",
                wds.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
                wds.DrugFinance_id as \"DrugFinance_id\",
                wds.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
                coalesce(wds.WhsDocumentUc_Name, '') as \"WhsDocumentSupply_Name\",
                coalesce(sup.Org_Name, '') as \"Supplier_Name\",
                fin_year.yr as \"WhsDocumentSupply_Year\",
                wds.WhsDocumentUc_Num as \"WhsDocumentUc_Num\"
            from
                v_WhsDocumentSupply wds
                left join v_WhsDocumentType wdt on wdt.WhsDocumentType_id = wds.WhsDocumentType_id
                left join v_WhsDocumentStatusType wdst on wdst.WhsDocumentStatusType_id = wds.WhsDocumentStatusType_id
                left join v_Org sup on sup.Org_id = wds.Org_sid
                LEFT JOIN LATERAL (
                    select
                        date_part('year', coalesce(max(i_wdd.WhsDocumentDelivery_setDT), wds.WhsDocumentUc_Date)) as yr
                    from
                        v_WhsDocumentDelivery i_wdd
                    where
                        i_wdd.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
                ) fin_year ON TRUE
			$where_clause
			limit 250
		";

        $result = $this->db->query($query, $params);
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
}