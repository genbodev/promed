<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Invoice - модель для работы с накладными
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			14.07.2014
 */

class Invoice_model extends SwPgModel {
    /**
     * Конструктор
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Получение списка приходных/расходных накладных
     */
    function loadInvoiceGrid($data) {
        $params = array();
        $filters = "(1=1)";

        if (!empty($data['Lpu_id'])) {
            $params['Lpu_id'] = $data['Lpu_id'];
        } else {
            return array('data' => array(), 'totalCount' => 0);
        }

        if (!empty($data['InvoiceType_Code'])) {
            $filters .= " and IT.InvoiceType_Code = :InvoiceType_Code";
            $params['InvoiceType_Code'] = $data['InvoiceType_Code'];
        }
        if (!empty($data['DateRange'][0]) && !empty($data['DateRange'][0])) {
            $filters .= " and I.Invoice_Date between :beginDate and :endDate";
            $params['beginDate'] = $data['DateRange'][0];
            $params['endDate'] = $data['DateRange'][1];
        }
        if (!empty($data['Storage_id'])) {
            $filters .= " and S.Storage_id = :Storage_id";
            $params['Storage_id'] = $data['Storage_id'];
        }
        if (!empty($data['InvoiceSubject_id'])) {
            $filters .= " and I.InvoiceSubject_id = :InvoiceSubject_id";
            $params['InvoiceSubject_id'] = $data['InvoiceSubject_id'];
        }
        if (!empty($data['InventoryItem_id'])) {
            $filters .= " and exists(
				select InvoicePosition_id
				from v_InvoicePosition IP 
				inner join v_Shipment Sh on Sh.Shipment_id = IP.Shipment_id
				where IP.Invoice_id = I.Invoice_id and Sh.InventoryItem_id = :InventoryItem_id
				limit 1
			)";
            $params['InventoryItem_id'] = $data['InventoryItem_id'];
        }

        $query = "
			-- addit with
			with storage_list as (
				select distinct
					S.Storage_id,
					S.Storage_Name
				from
					v_StorageStructLevel SSL 
					inner join v_Storage S on S.Storage_id = SSL.Storage_id
				where SSL.Lpu_id = :Lpu_id
			)
			-- end addit with
			select
				-- select
				I.Invoice_id as \"Invoice_id\",				
				to_char(I.Invoice_Date, 'DD.MM.YYYY') as \"Invoice_Date\",
				I.Invoice_Num as \"Invoice_Num\",
				IPSum.Invoice_Sum as \"Invoice_Sum\",
				IT.InvoiceType_id as \"InvoiceType_id\",
				IT.InvoiceType_Code as \"InvoiceType_Code\",
				Subj.InvoiceSubject_id as \"InvoiceSubject_id\",
				Subj.InvoiceSubject_Name as \"InvoiceSubject_Name\",
				S.Storage_id as \"Storage_id\",
				S.Storage_Name as \"Storage_Name\"
				-- end select
			from
				-- from
				v_Invoice I 
				inner join v_InvoiceType IT on IT.InvoiceType_id = I.InvoiceType_id
				inner join v_InvoiceSubject Subj on Subj.InvoiceSubject_id = I.InvoiceSubject_id
				inner join storage_list S on S.Storage_id = I.Storage_id
				LEFT JOIN LATERAL (
					select
						sum(InvoicePosition_Count*InvoicePosition_Price) as Invoice_Sum
					from v_InvoicePosition
					where Invoice_id = I.Invoice_id
					limit 1
				) IPSum on true
				-- end from
			where
				-- where
				{$filters}
				-- end where
			order by
				-- order by
				I.Invoice_Date
				-- end order by
		";

        //echo getDebugSQL($query, $params);exit;
        $response = array();
        $count_result = $this->queryResult(getCountSQLPH($query),$params);
        if (!$this->isSuccessful($count_result)) {
            return $count_result;
        } else {
            $response['totalCount']=$count_result[0]['cnt'];
        }

        $data_result = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']),$params);
        if( count($data_result)>0 ){
            if (!$this->isSuccessful($data_result)) {
                return $data_result;
            } else {
                $response['data']=$data_result;
            }
            return $response;
        }else{
            return false;
        }

        return $response;
    }

    /**
     * Получение списка партий
     */
    function loadShipmentGrid($data) {
        $params = array();
        $filters = "(1=1)";

        if (!empty($data['Lpu_id'])) {
            $params['Lpu_id'] = $data['Lpu_id'];
        } else {
            return array('data' => array(), 'totalCount' => 0);
        }

        if (!empty($data['DateRange'][0]) && !empty($data['DateRange'][0])) {
            $filters .= " and Sh.Shipment_setDate between :beginDate and :endDate";
            $params['beginDate'] = $data['DateRange'][0];
            $params['endDate'] = $data['DateRange'][1];
        }
        if (!empty($data['Storage_id'])) {
            $filters .= " and S.Storage_id = :Storage_id";
            $params['Storage_id'] = $data['Storage_id'];
        }
        if (!empty($data['InvoiceSubject_id'])) {
            $filters .= " and Subj.InvoiceSubject_id = :InvoiceSubject_id";
            $params['InvoiceSubject_id'] = $data['InvoiceSubject_id'];
        }
        if (!empty($data['InventoryItem_id'])) {
            $filters .= " and II.InventoryItem_id = :InventoryItem_id";
            $params['InventoryItem_id'] = $data['InventoryItem_id'];
        }

        $query = "
			-- addit with
			with storage_list as (
				select distinct
					S.Storage_id,
					S.Storage_Name
				from
					v_StorageStructLevel SSL
					inner join v_Storage S on S.Storage_id = SSL.Storage_id
				where SSL.Lpu_id = :Lpu_id
			)
			-- end addit with
			select
				-- select
				Sh.Shipment_id as \"Shipment_id\",				
				to_char(Sh.Shipment_setDate, 'DD.MM.YYYY') as \"Shipment_setDate\",
				Sh.Shipment_Price as \"Shipment_Price\",
				Sh.Shipment_Count - coalesce(t.Summa, 0) as \"Shipment_Count\",
				II.InventoryItem_id as \"InventoryItem_id\",
				II.InventoryItem_Name as \"InventoryItem_Name\",
				S.Storage_id as \"Storage_id\",
				S.Storage_Name as \"Storage_Name\",
				Subj.InvoiceSubject_id as \"InvoiceSubject_id\",
				Subj.InvoiceSubject_Name as \"InvoiceSubject_Name\",
				O.Okei_id as \"Okei_id\",
				O.Okei_NationSymbol as \"Okei_NationSymbol\",				
				to_char(LastInvoiceOut.Invoice_Date, 'DD.MM.YYYY') as \"LastInvoiceOut_Date\"
				-- end select
			from
				-- from
				v_Shipment Sh
				inner join v_InventoryItem II on II.InventoryItem_id = Sh.InventoryItem_id
				inner join v_Okei O on O.Okei_id = II.Okei_id
				inner join storage_list S on S.Storage_id = Sh.Storage_id
				LEFT JOIN LATERAL (
					select
						ISubj.InvoiceSubject_id,
						ISubj.InvoiceSubject_Name
					from v_InvoicePosition IP
					inner join v_Invoice I on I.Invoice_id = IP.Invoice_id
					inner join v_InvoiceSubject ISubj on ISubj.InvoiceSubject_id = I.InvoiceSubject_id
					where IP.Shipment_id = Sh.Shipment_id and I.InvoiceType_id = 1
					limit 1
				) Subj on true
				LEFT JOIN LATERAL (
					select
						sum(IP.InvoicePosition_Count*coalesce(IP.InvoicePosition_Coeff, 1)) as Summa
					from v_InvoicePosition IP
					inner join v_Invoice I on I.Invoice_id = IP.Invoice_id
					where I.InvoiceType_id = 2 and IP.Shipment_id = Sh.Shipment_id
					limit 1
				) t on true
				LEFT JOIN LATERAL (
					select
						I.Invoice_Date
					from v_InvoicePosition IP
					inner join v_Invoice I on I.Invoice_id = IP.Invoice_id
					where IP.Shipment_id = Sh.Shipment_id and I.InvoiceType_id = 2
					order by I.Invoice_Date desc
					limit 1
				) LastInvoiceOut on true
				-- end from
			where
				-- where
				{$filters}
				-- end where
			order by
				-- order by
				Sh.Shipment_setDate
				-- end order by
		";

        $response = array();
        $count_result = $this->queryResult(getCountSQLPH($query),$params);
        if (!$this->isSuccessful($count_result)) {
            return $count_result;
        } else {
            $response['totalCount']=$count_result[0]['cnt'];
        }

        $data_result = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']),$params);

        if(count($data_result)>0){
            if (!$this->isSuccessful($data_result)) {
                return $data_result;
            } else {
                $response['data']=$data_result;
            }
            return $response;
        }else{
            return false;
        }
    }

    /**
     * Получение списка позиций в накладной
     */
    function loadInvoicePositionGrid($data) {
        $params = array('Invoice_id' => $data['Invoice_id']);

        $query = "
			select
				IP.InvoicePosition_id as \"InvoicePosition_id\",
				IP.Invoice_id as \"Invoice_id\",
				1 as \"RecordStatus_Code\",
				IP.InvoicePosition_PositionNum as \"InvoicePosition_PositionNum\",
				IP.InvoicePosition_Coeff as \"InvoicePosition_Coeff\",
				IP.InvoicePosition_Comment as \"InvoicePosition_Comment\",
				IP.InvoicePosition_Count as \"InvoicePosition_Count\",
				IP.InvoicePosition_Count as \"InvoicePosition_PrevCount\",
				IP.InvoicePosition_Price as \"InvoicePosition_Price\",
				(IP.InvoicePosition_Count*IP.InvoicePosition_Price) as \"InvoicePosition_Sum\",
				Sh.Shipment_id as \"Shipment_id\",
				O.Okei_id as \"Okei_id\",
				O.Okei_NationSymbol as \"Okei_NationSymbol\",
				II.InventoryItem_id as \"InventoryItem_id\",
				II.InventoryItem_Name as \"InventoryItem_Name\"
			from v_InvoicePosition IP
				left join v_Okei O on O.Okei_id = IP.Okei_id
				left join v_Shipment Sh on Sh.Shipment_id = IP.Shipment_id
				left join v_InventoryItem II on II.InventoryItem_id = Sh.InventoryItem_id
			where IP.Invoice_id = :Invoice_id
		";

        $response['data'] = $this->queryResult($query, $params);
        return $response;
    }

    /**
     * Получегние списка объектов аналитического учета
     */
    function loadInvoiceSubjectList($data) {
        $params = array();
        $filters = "(1=1)";

        if (!empty($data['InvoiceSubject_id'])) {
            $filters .= " and Subj.InvoiceSubject_id = :InvoiceSubject_id";
            $params['InvoiceSubject_id'] = $data['InvoiceSubject_id'];
        } else {
            if (!empty($data['query'])) {
                $filters .= " and CAST(Subj.InvoiceSubject_Code as varchar)||' '||Subj.InvoiceSubject_Name ilike '%'||:query||'%'";
                $params['query'] = $data['query'];
            }
        }

        $query = "
			select
				Subj.InvoiceSubject_id as \"InvoiceSubject_id\",
				Subj.InvoiceSubject_pid as \"InvoiceSubject_pid\",
				Subj.InvoiceSubject_Code as \"InvoiceSubject_Code\",
				Subj.InvoiceSubject_Name as \"InvoiceSubject_Name\",
				to_char(Subj.InvoiceSubject_begDate, 'DD.MM.YYYY') as \"InvoiceSubject_begDate\",
				to_char(Subj.InvoiceSubject_endDate, 'DD.MM.YYYY') as \"InvoiceSubject_begDate\",
				Subj.InvoiceSubject_IsLevel as \"InvoiceSubject_IsLevel\"
			from v_InvoiceSubject Subj
			where
				{$filters}
		";

        return $this->queryResult($query, $params);
    }

    /**
     * Удаление накладной
     */
    function deleteInvoice($data) {
        $params = array('Invoice_id' => $data['Invoice_id']);

        $query = "
			select
				IP.InvoicePosition_id as \"InvoicePosition_id\",
				I.InvoiceType_id as \"InvoiceType_id\"
			from v_InvoicePosition IP
			inner join v_Invoice I on I.Invoice_id = IP.Invoice_id
			where I.Invoice_id = :Invoice_id
		";

        $InvoicePositionList = $this->queryResult($query, $params);
        if (!$this->isSuccessful($InvoicePositionList)) {
            return $this->createError('', 'Ошибка при получении списка позиций в накладной');
        }

        $this->beginTransaction();
        foreach($InvoicePositionList as $InvoicePosition) {
            $resp = $this->deleteInvoicePosition($InvoicePosition);
            if (!$this->isSuccessful($resp)) {
                $this->rollbackTransaction();
                return $resp;
            }
        }

        $query = "
        SELECT
        error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        p_Invoice_del(
          Invoice_id => :Invoice_id
        )
        ";

        $response = $this->queryResult($query, $params);
        if (!$this->isSuccessful($response)) {
            $this->rollbackTransaction();
        } else {
            $this->commitTransaction();
        }

        return $response;
    }

    /**
     * Сохранение накладной
     */
    function saveInvoice($data) {
        $this->beginTransaction();
        $this->isAllowTransaction = false;

        $params = array(
            'Invoice_id' => empty($data['Invoice_id'])?null:$data['Invoice_id'],
            'InvoiceType_id' => $data['InvoiceType_id'],
            'Invoice_Date' => $data['Invoice_Date'],
            'Invoice_Num' => $data['Invoice_Num'],
            'InvoiceSubject_id' => $data['InvoiceSubject_id'],
            'Storage_id' => $data['Storage_id'],
            'PayInvoiceType_id' => $data['PayInvoiceType_id'],
            'Invoice_Comment' => empty($data['Invoice_Comment'])?null:$data['Invoice_Comment'],
            'pmUser_id' => $data['pmUser_id']
        );

        $procedure = 'p_Invoice_ins';
        if (!empty($params['Invoice_id'])) {
            $procedure = 'p_Invoice_upd';
        }

        $query = "
        SELECT
        Invoice_id as \"Invoice_id\",
        error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        {$procedure}(
                Invoice_id => :Invoice_id,
				InvoiceType_id => :InvoiceType_id,
				Invoice_Date => :Invoice_Date,
				Invoice_Num => :Invoice_Num,
				InvoiceSubject_id => :InvoiceSubject_id,
				Storage_id => :Storage_id,
				PayInvoiceType_id => :PayInvoiceType_id,
				Invoice_Comment => :Invoice_Comment,
				pmUser_id => :pmUser_id
        )
        ";

        $result = $this->queryResult($query, $params);
        if (!$result) {
            $this->isAllowTransaction = true;
            $this->rollbackTransaction();
            return $this->createError('','Ошибка при сохранении накладной');
        }
        $data['Invoice_id'] = $result[0]['Invoice_id'];

        if ($data['InvoiceType_id'] == 2) {
            $res = $this->calculateInvoicePositions($data);
            if (!$this->isSuccessful($res)) {
                $this->isAllowTransaction = true;
                $this->rollbackTransaction();
                return $res;
            }
        }

        $this->isAllowTransaction = true;
        $this->commitTransaction();
        return $result;
    }

    /**
     * Перерасчет расходной накладной
     */
    function calculateInvoicePositions($data) {
        $params = array('Invoice_id' => $data['Invoice_id']);

        $query = "
			select
				IP.InvoicePosition_id as \"InvoicePosition_id\",
				Sh.InventoryItem_id as \"InventoryItem_id\",
				IP.Shipment_id as \"Shipment_id\",
				IP.Okei_id as \"Okei_id\",
				coalesce(IP.InvoicePosition_Coeff, 1) as \"InvoicePosition_Coeff\",
				IP.InvoicePosition_Price as \"InvoicePosition_Price\",
				IP.InvoicePosition_Count as \"InvoicePosition_Count\",
				IP.InvoicePosition_Comment as \"InvoicePosition_Comment\",
				3 as \"RecordStatus_Code\"
			from v_InvoicePosition IP
			inner join v_Shipment Sh on Sh.Shipment_id = IP.Shipment_id
			where IP.Invoice_id = :Invoice_id
		";

        $InvoicePositions = $this->queryResult($query, $params);
        if (!is_array($InvoicePositions)) {
            return $this->createError('', 'Ошибка при запросе позиций в накладной');
        }
        if (count($InvoicePositions) == 0) {
            return array(array('success' => true));
        }

        $query = "
			select
				Sh.InventoryItem_id as \"InventoryItem_id\",
				coalesce(IP.InvoicePosition_Coeff, 1) as \"InvoicePosition_Coeff\",
				sum(IP.InvoicePosition_Count) as \"InvoicePosition_Count\",
				IP.Okei_id as \"Okei_id\"
			from
				v_InvoicePosition IP
				inner join v_Shipment Sh on Sh.Shipment_id = IP.Shipment_id
			where
				IP.Invoice_id = :Invoice_id
			group by
				IP.Invoice_id,
				Sh.InventoryItem_id,
				coalesce(IP.InvoicePosition_Coeff, 1),
				IP.Okei_id
		";

        $dataForSave = $this->queryResult($query, $params);
        if (!$dataForSave) {
            return $this->createError('', 'Ошибка при запросе позиций в накладной');
        }

        $tmp_arr = array();
        foreach($InvoicePositions as $position) {
            $id = $position['InvoicePosition_id'];
            $tmp_arr[$id] = $InvoicePositions;
        }

        $num = 0;
        //Расчет новых позиций в накладной
        foreach($dataForSave as $item) {
            $ShipmentData = $this->getShipmentData(array(
                'Storage_id' => $data['Storage_id'],
                'InvoicePosition_ids' => array_keys($tmp_arr),
                'Invoice_Date' => $data['Invoice_Date'],
                'InventoryItem_id' => $item['InventoryItem_id'],
                'InvoicePosition_Count' => $item['InvoicePosition_Count'],
                'InvoicePosition_Coeff' => $item['InvoicePosition_Coeff'],
            ));

            if (!$this->isSuccessful($ShipmentData)) {
                return $ShipmentData;
            }

            foreach($ShipmentData as $shipment) {
                $tmp = array(
                    'InvoicePosition_id' => null,
                    'InventoryItem_id' => $item['InventoryItem_id'],
                    'Shipment_id' => $shipment['Shipment_id'],
                    'Okei_id' => $item['Okei_id'],
                    'InvoicePosition_Coeff' => $item['InvoicePosition_Coeff'],
                    'InvoicePosition_Price' => $shipment['Shipment_Price'],
                    'InvoicePosition_Count' => $shipment['Shipment_ReservedCount'],
                    'InvoicePosition_Comment' => '',
                    'RecordStatus_Code' => 0
                );

                foreach($InvoicePositions as $key => $position) {
                    if (
                        $position['RecordStatus_Code'] == 3
                        && $position['InventoryItem_id'] == $tmp['InventoryItem_id']
                        && $position['Okei_id'] == $tmp['Okei_id']
                        && $position['InvoicePosition_Coeff'] == $tmp['InvoicePosition_Coeff']
                        && $position['InvoicePosition_Price'] == $tmp['InvoicePosition_Price']
                    ) {
                        if ($position['Shipment_id'] == $tmp['Shipment_id'] && $position['InvoicePosition_Count'] == $tmp['InvoicePosition_Count']) {
                            $position['RecordStatus_Code'] = 1;
                        } else {
                            $position['RecordStatus_Code'] = 2;
                            $position['Shipment_id'] = $shipment['Shipment_id'];
                            $position['InvoicePosition_Count'] = $shipment['Shipment_ReservedCount'];
                        }
                        $tmp = $position;
                        unset($InvoicePositions[$key]);
                        unset($tmp_arr[$tmp['InvoicePosition_id']]);
                        break;
                    }
                }

                $tmp['InvoicePosition_PositionNum'] = ++$num;
                $tmp['Invoice_id'] = $data['Invoice_id'];
                $tmp['Invoice_Date'] = $data['Invoice_Date'];
                $tmp['InvoiceType_id'] = $data['InvoiceType_id'];
                $tmp['pmUser_id'] = $data['pmUser_id'];

                $resp = $this->saveInvoicePosition($tmp, array_keys($tmp_arr));
                if (!$this->isSuccessful($resp)) {
                    return $resp;
                }
            }
        }

        //Удаление неиспользуемых позиций
        foreach($InvoicePositions as $position) {
            $resp = $this->deleteInvoicePosition(array(
                'InvoicePosition_id' => $position['InvoicePosition_id'],
                'InvoiceType_id' => $data['InvoiceType_id']
            ));
            if (!$this->isSuccessful($resp)) {
                return $resp;
            }
        }

        return array(array('success' => true));
    }

    /**
     * Сахранение позиций в накладной
     */
    function saveInvoicePositionData($data){
        $InvoicePositionData = json_decode($data['InvoicePositionData'], true);

        $this->beginTransaction();
        $this->isAllowTransaction = false;

        foreach($InvoicePositionData as $item) {
            $item['pmUser_id'] = $data['pmUser_id'];

            $resp = $this->saveInvoicePosition($item);

            if (!empty($resp[0]['Error_Msg'])) {
                $this->isAllowTransaction = true;
                $this->rollbackTransaction();
                return $resp;
            }
        }

        $this->isAllowTransaction = true;
        $this->commitTransaction();
        return array(array('success' => true, 'Error_Msg' => ''));
    }

    /**
     * Сохранение позиции в накладной
     */
    function saveInvoicePosition($data, $excludeReserved = array()) {
        if (empty($data['InvoiceType_id'])) {
            return $this->createError('','Отсутсвует информация о типе накладной при сохранении позиции');
        }
        if (empty($data['InvoicePosition_id']) || $data['InvoicePosition_id'] < 0) {
            $data['InvoicePosition_id'] = null;
        }

        $check = $this->checkShipment($data, $excludeReserved);
        if (!empty($check[0]['Error_Msg'])) {
            return $check;
        }

        $this->beginTransaction();

        //При сохранении приходной накладной сохраняем партию
        if ($data['InvoiceType_id'] == 1) {
            $coeff = empty($data['InvoicePosition_Coeff'])?1:$data['InvoicePosition_Coeff'];
            $saveData = array(
                'Shipment_id' => empty($data['Shipment_id'])?null:$data['Shipment_id'],
                'Storage_id' => $data['Storage_id'],
                'InventoryItem_id' => $data['InventoryItem_id'],
                'Shipment_setDate' => $data['Invoice_Date'],
                'Shipment_Price' => $data['InvoicePosition_Price']/$coeff,
                'Shipment_Count' => $data['InvoicePosition_Count']*$coeff,
                'Shipment_Comment' => $data['InvoicePosition_Comment'],
                'pmUser_id' => $data['pmUser_id'],
            );

            $resp = $this->saveShipment($saveData);
            if (!empty($resp[0]['Error_Msg'])) {
                $this->rollbackTransaction();
                return $resp;
            }
            $data['Shipment_id'] = $resp[0]['Shipment_id'];
        }

        $params = array(
            'InvoicePosition_id' => empty($data['InvoicePosition_id'])?null:$data['InvoicePosition_id'],
            'Invoice_id' => $data['Invoice_id'],
            'InvoicePosition_PositionNum' => empty($data['InvoicePosition_PositionNum'])?null:$data['InvoicePosition_PositionNum'],
            'Shipment_id' => $data['Shipment_id'],
            'Okei_id' => $data['Okei_id'],
            'InvoicePosition_Count' => $data['InvoicePosition_Count'],
            'InvoicePosition_Price' => $data['InvoicePosition_Price'],
            'InvoicePosition_Coeff' => empty($data['InvoicePosition_Coeff'])?null:$data['InvoicePosition_Coeff'],
            'InvoicePosition_Comment' => $data['InvoicePosition_Comment'],
            'pmUser_id' => $data['pmUser_id'],
        );

        $procedure = 'p_InvoicePosition_ins';
        if (!empty($params['InvoicePosition_id'])) {
            $procedure = 'p_InvoicePosition_upd';
        }

        $query = "
        with mv1 as (select :InvoicePosition_id as InvoicePosition_id ,case when :InvoicePosition_id is null then (coalesce((select max(IP.InvoicePosition_PositionNum)
        from v_InvoicePosition IP where IP.Invoice_id = :Invoice_id),0)+1) else :InvoicePosition_PositionNum end as InvoicePosition_PositionNum)
        SELECT InvoicePosition_id as \"InvoicePosition_id\", 
        Error_Code as \"Error_Code\",
        Error_Message as \"Error_Msg\"
        from {$procedure} (
        InvoicePosition_id => (select InvoicePosition_id from mv1),
        Invoice_id => :Invoice_id,
        InvoicePosition_PositionNum => (select InvoicePosition_PositionNum from mv1),
        Shipment_id => :Shipment_id,
        Okei_id => :Okei_id,
        InvoicePosition_Count => :InvoicePosition_Count,
        InvoicePosition_Price => :InvoicePosition_Price,
        InvoicePosition_Coeff => :InvoicePosition_Coeff,
        InvoicePosition_Comment => :InvoicePosition_Comment,
        pmUser_id => :pmUser_id)
        ";

        $result = $this->queryResult($query, $params);
        if (!$this->isSuccessful($result)) {
            $result = $this->createError('','Ошибка при сохранении накладной');
        }
        if (!empty($result[0]['Error_Msg'])) {
            $this->rollbackTransaction();
            return $result;
        }

        $this->commitTransaction();
        return $result;
    }

    /**
     * Удаление позиции в накладной
     */
    function deleteInvoicePosition($data) {
        if (empty($data['InvoiceType_id'])) {
            return $this->createError('','Отсутсвует информация о типе накладной при сохранении позиции');
        }

        $params = array('InvoicePosition_id' => $data['InvoicePosition_id']);

        if ($data['InvoiceType_id'] == 1) {
            $query = "
				select Shipment_id as \"Shipment_id\"
				from v_InvoicePosition
				where InvoicePosition_id = :InvoicePosition_id
				limit 1
			";
            $data['Shipment_id'] = $this->getFirstResultFromQuery($query, $params);
            if (!$data['Shipment_id']) {
                return $this->createError('', 'Ошибка при определении партии');
            }

            $check = $this->checkShipment($data);
            if (!empty($check[0]['Error_Msg'])) {
                return $check;
            }
        }

        $this->beginTransaction();

        $query = "
        SELECT
        error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        p_InvoicePosition_del(
          InvoicePosition_id => :InvoicePosition_id
        )
        ";

        $result = $this->queryResult($query, $params);
        if (!$result) {
            $result = $this->createError('','Ошибка при удалении накладной');
        }
        if (!empty($result[0]['Error_Msg'])) {
            $this->rollbackTransaction();
            return $result;
        }

        if ($data['InvoiceType_id'] == 1) {
            $resp = $this->deleteShipment($data);
            if (!empty($resp[0]['Error_Msg'])) {
                $this->rollbackTransaction();
                return $resp;
            }
        }

        $this->commitTransaction();
        return $result;
    }

    /**
     * Сохранение партии
     */
    function saveShipment($data) {
        $params = array(
            'Shipment_id' => empty($data['Shipment_id'])?null:$data['Shipment_id'],
            'Storage_id' => $data['Storage_id'],
            'InventoryItem_id' => $data['InventoryItem_id'],
            'Shipment_setDate' => $data['Shipment_setDate'],
            'Shipment_Price' => $data['Shipment_Price'],
            'Shipment_Count' => $data['Shipment_Count'],
            'Shipment_Comment' => $data['Shipment_Comment'],
            'pmUser_id' => $data['pmUser_id'],
        );

        $procedure = 'p_Shipment_ins';
        if (!empty($params['Shipment_id'])) {
            $procedure = 'p_Shipment_upd';
        }

        $query = "
        SELECT
        Shipment_id as \"Shipment_id\",
        error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        {$procedure}(
                Shipment_id => :Shipment_id,
				Storage_id => :Storage_id,
				InventoryItem_id => :InventoryItem_id,
				Shipment_setDate => :Shipment_setDate,
				Shipment_Price => :Shipment_Price,
				Shipment_Count => :Shipment_Count,
				Shipment_Comment => :Shipment_Comment,
				pmUser_id => :pmUser_id
        )
        ";

        $result = $this->queryResult($query, $params);
        if (!$result) {
            $result = $this->createError('','Ошибка при сохранении накладной');
        }

        return $result;
    }

    /**
     * Удаление партии
     */
    function deleteShipment($data) {
        $params = array('Shipment_id' => $data['Shipment_id']);

        $query = "
        SELECT
        error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        p_Shipment_del(
          Shipment_id => :Shipment_id
        )
        ";

        $result = $this->queryResult($query, $params);
        if (!$result) {
            $result = $this->createError('','Ошибка при удалении партии');
        }

        return $result;
    }

    /**
     * Получение данных для формы редактирования накладной
     */
    function loadInvoiceForm($data) {
        $params = array('Invoice_id' => $data['Invoice_id']);

        $query = "
			select
				I.Invoice_id as \"Invoice_id\",
				I.InvoiceType_id as \"InvoiceType_id\",
				to_char(I.Invoice_Date, 'DD.MM.YYYY') as \"Invoice_Date\",
				I.Invoice_Num as \"Invoice_Num\",
				I.InvoiceSubject_id as \"InvoiceSubject_id\",
				I.Storage_id as \"Storage_id\",
				I.PayInvoiceType_id as \"PayInvoiceType_id\",
				I.Invoice_Comment as \"Invoice_Comment\"
			from v_Invoice I
			where I.Invoice_id = :Invoice_id
			limit 1
		";

        return $this->queryResult($query, $params);
    }

    /**
     * Получение данных для формы редактирования позиции в накладной
     */
    function loadInvoicePositionForm($data) {
        $params = array('InvoicePosition_id' => $data['InvoicePosition_id']);

        $query = "
			select
				IP.InvoicePosition_id as \"InvoicePosition_id\",
				IP.Invoice_id as \"Invoice_id\",
				IP.Invoice_id as \"Invoice_id\",
				Sh.Shipment_id as \"Shipment_id\",
				IP.InvoicePosition_PositionNum as \"InvoicePosition_PositionNum\",
				Sh.InventoryItem_id as \"InventoryItem_id\",
				IP.InvoicePosition_Count as \"InvoicePosition_Count\",
				IP.Okei_id as \"Okei_id\",
				IP.InvoicePosition_Price as \"InvoicePosition_Price\",
				IP.InvoicePosition_Coeff as \"InvoicePosition_Coeff\",
				IP.InvoicePosition_Comment as \"InvoicePosition_Comment\"
			from
				v_InvoicePosition IP
				inner join v_Shipment Sh on Sh.Shipment_id = IP.Shipment_id
			where IP.InvoicePosition_id = :InvoicePosition_id
			limit 1
		";

        return $this->queryResult($query, $params);
    }

    /**
     * Проверка возможности редактирования партии / списания из партии
     */
    function checkShipment($data, $excludeReserved = array()) {
        if ($data['InvoiceType_id'] == 1 && !empty($data['Shipment_id'])) {
            $params = array('Shipment_id' => $data['Shipment_id']);

            $query = "
				select COUNT(InvoicePosition_id) as \"Count\"
				from v_InvoicePosition IP
				inner join v_Invoice I on I.Invoice_id = IP.Invoice_id
				inner join v_Shipment Sh on Sh.Shipment_id = IP.Shipment_id
				where Sh.Shipment_id = :Shipment_id and I.InvoiceType_id = 2
				limit 1
			";

            $resp = $this->queryResult($query, $params);
            if (!$this->isSuccessful($resp)) {
                return $this->createError('', 'Ошибка при определении возможности редактирования партии');
            }
            if ($resp[0]['Count'] > 0) {
                return $this->createError('', 'Не возможно редактирование партии, поскольку из неё уже произошло списание');
            }
        } else if ($data['InvoiceType_id'] == 2) {
            $params = array(
                'Shipment_id' => $data['Shipment_id'],
                'InvoicePosition_id' => empty($data['InvoicePosition_id'])?null:$data['InvoicePosition_id'],
                'InvoicePosition_Count' => $data['InvoicePosition_Count'],
                'Invoice_Date' => $data['Invoice_Date']
            );

            $exclude = "";
            if (count($excludeReserved)) {
                $exclude .= " and IP.InvoicePosition_id not in (".implode(',', $excludeReserved).")";
            }

            $query = "
				select SRWT.StorageRecWriteType_Code as \"StorageRecWriteType_Code\"
				from v_Shipment Sh
				inner join v_Storage S on S.Storage_id = Sh.Storage_id
				inner join v_StorageRecWriteType SRWT on SRWT.StorageRecWriteType_id = S.StorageRecWriteType_id
				where Sh.Shipment_id = :Shipment_id
			";
            $srwt_code = $this->getFirstResultFromQuery($query, $params);
            if (!$srwt_code) {
                return $this->createError('', 'Ошибка при определении вида прием списания склада');
            }

            $shipmentFilter = "";
            switch($srwt_code) {
                case 1:
                    $shipmentFilter .= " and Sh.Shipment_setDate <= :Invoice_Date";
                    break;

                case 2:
                    $shipmentFilter .= " and Sh.Shipment_setDate <= :Invoice_Date";
                    break;

                case 3:
                    $shipmentFilter .= " and Sh.Shipment_setDate <= :Invoice_Date";
                    break;

                case 4:
                    $shipmentFilter .= " and Sh.Shipment_setDate = :Invoice_Date";
                    break;

                case 5:
                    $shipmentFilter .= " and Sh.Shipment_setDate <= :Invoice_Date";
                    break;

                case 6:
                    $shipmentFilter .= " and Sh.Shipment_setDate <= :Invoice_Date";
                    break;
            }

            $reserved_count = $this->getFirstResultFromQuery("SELECT coalesce((
					select
						SUM(IP.InvoicePosition_Count*coalesce(IP.InvoicePosition_Coeff,1)) as \"Summa\"
					from
						v_InvoicePosition IP
						left join v_Shipment Sh on Sh.Shipment_id = IP.Shipment_id
						inner join v_Invoice I on I.Invoice_id = IP.Invoice_id
					where
						I.InvoiceType_id = 2 and IP.Shipment_id = :Shipment_id
						and (:InvoicePosition_id is null or IP.InvoicePosition_id <> :InvoicePosition_id)
						{$exclude}
						{$shipmentFilter}
						limit 1
				), 0)", $params);
            $params['reserved_count'] = $reserved_count;

            $query = "
				select
					sum(Sh.Shipment_Count)-:reserved_count-:InvoicePosition_Count as \"Shipment_Count\",
					S.Storage_Name as \"Storage_Name\",
					II.InventoryItem_Name as \"InventoryItem_Name\",
					O.Okei_NationSymbol as \"Okei_NationSymbol\"
				from
					v_Shipment Sh
					inner join v_Storage S on S.Storage_id = Sh.Storage_id
					inner join v_InventoryItem II on II.InventoryItem_id = Sh.InventoryItem_id
					inner join v_Okei O on O.Okei_id = II.Okei_id
				where
					Sh.Shipment_id = :Shipment_id
					{$shipmentFilter}
				group by
					S.Storage_Name,
					II.InventoryItem_Name,
					O.Okei_NationSymbol
			";
            //echo getDebugSQL($query, $params);exit;
            $resp = $this->queryResult($query, $params);
            if (!$this->isSuccessful($resp)) {
                return $this->createError('', 'Ошибка при определении возможности списания из партии');
            }
            if (count($resp) == 0) {
                return $this->createError('', "Не найдена партия.");
            }
            if ($resp[0]['Shipment_Count'] < 0) {
                return $this->createError('', "В партии недостаточно ТМЦ для списания.");
            }
        }

        return array(array('success' => true, 'Error_Msg' => ''));
    }

    /**
     * Получение данных о партиях для списания
     */
    function getShipmentData($data) {
        $params = array(
            'Storage_id' => $data['Storage_id'],
            'InventoryItem_id' => $data['InventoryItem_id'],
            'Invoice_id' => empty($data['Invoice_id']) ? null : $data['Invoice_id'],
            'InvoicePosition_id' => empty($data['InvoicePosition_id']) ? null : $data['InvoicePosition_id'],
            'Invoice_Date' => $data['Invoice_Date']
        );

        $query = "
			select SRWT.StorageRecWriteType_Code as \"StorageRecWriteType_Code\"
			from v_Storage S
			inner join v_StorageRecWriteType SRWT on SRWT.StorageRecWriteType_id = S.StorageRecWriteType_id
			where S.Storage_id = :Storage_id
		";
        $srwt_code = $this->getFirstResultFromQuery($query, $params);
        if (!$srwt_code) {
            return $this->createError('', 'Ошибка при определении вида прием списания склада');
        }

        $order = "";
        $shipmentFilter = "";
        $reserveFilter = "";

        switch($srwt_code) {
            case 1:
                $shipmentFilter .= " and Sh.Shipment_setDate <= :Invoice_Date";
                $order .= "Sh.Shipment_id asc";
                break;

            case 2:
                $shipmentFilter .= " and Sh.Shipment_setDate <= :Invoice_Date";
                $order .= "Sh.Shipment_setDate asc";
                break;

            case 3:
                $shipmentFilter .= " and Sh.Shipment_setDate <= :Invoice_Date";
                $order .= "Sh.Shipment_setDate desc";
                break;

            case 4:
                $shipmentFilter .= " and Sh.Shipment_setDate = :Invoice_Date";
                $order .= "Sh.Shipment_setDate desc";
                break;

            case 5:
                $shipmentFilter .= " and Sh.Shipment_setDate <= :Invoice_Date";
                $order .= "Sh.Shipment_Price asc";
                break;

            case 6:
                $shipmentFilter .= " and Sh.Shipment_setDate <= :Invoice_Date";
                $order .= "Sh.Shipment_Price desc";
                break;
        }

        $coeff = empty($data['InvoicePosition_Coeff']) ? 1 : $data['InvoicePosition_Coeff'];
        $need_count = $data['InvoicePosition_Count'] * $coeff;

        if (!empty($data['InvoicePosition_ids'])) {
            $reserveFilter .= " and IP.InvoicePosition_id not in(".implode(',', $data['InvoicePosition_ids']).")";
        } else if (!empty($params['InvoicePosition_id'])) {
            $reserveFilter .= " and IP.InvoicePosition_id <> :InvoicePosition_id";
        } else if (!empty($params['Invoice_id'])) {
            $reserveFilter .= " and IP.Invoice_id <> :Invoice_id";
        }

        $reserved_count = $this->getFirstResultFromQuery("SELECT coalesce((
				select
					SUM(IP.InvoicePosition_Count*coalesce(IP.InvoicePosition_Coeff,1)) as \"Summa\"
				from
					v_InvoicePosition IP
					left join v_Shipment Sh on Sh.Shipment_id = IP.Shipment_id
					inner join v_Invoice I on I.Invoice_id = IP.Invoice_id
				where
					I.InvoiceType_id = 2
					and Sh.InventoryItem_id = :InventoryItem_id
					and Sh.Storage_id = :Storage_id
					{$shipmentFilter}
					{$reserveFilter}
			), 0)", $params);
        $params['reserved_count'] = $reserved_count;

        $query = "
			select
				sum(Sh.Shipment_Count)-:reserved_count as \"Count\",
				S.Storage_Name as \"Storage_Name\",
				II.InventoryItem_Name as \"InventoryItem_Name\",
				O.Okei_NationSymbol as \"Okei_NationSymbol\"
			from
				v_Shipment Sh
				inner join v_Storage S on S.Storage_id = Sh.Storage_id
				inner join v_InventoryItem II on II.InventoryItem_id = Sh.InventoryItem_id
				inner join v_Okei O on O.Okei_id = II.Okei_id
			where
				Sh.InventoryItem_id = :InventoryItem_id
				and Sh.Storage_id = :Storage_id
				and Sh.Shipment_Count > 0
				{$shipmentFilter}
			group by
				S.Storage_Name,
				II.InventoryItem_Name,
				O.Okei_NationSymbol
		";

        //echo getDebugSQL($query, $params);exit;
        $resp = $this->queryResult($query, $params);
        if (!$this->isSuccessful($resp)) {
            return $this->createError('', 'Ошибка при выборе партии для списания');
        }
        if (count($resp) == 0) {
            return $this->createError('', 'На складе отсутсвует выбранная ТМЦ');
        }
        if ($resp[0]['Count'] < $need_count) {
            return $this->createError('', "На складе '{$resp[0]['Storage_Name']}' недостаточное количество ТМЦ '{$resp[0]['InventoryItem_Name']}', доступное количество {$resp[0]['Count']} {$resp[0]['Okei_NationSymbol']}.");
        }

        $query = "
			select
				Sh.Shipment_id as \"Shipment_id\",
				Sh.Shipment_Price as \"Shipment_Price\",
				Sh.Shipment_Count-coalesce(t.Summa, 0) as \"Shipment_Count\"
			from
				v_Shipment Sh
				LEFT JOIN LATERAL (
					select
						SUM(IP.InvoicePosition_Count*coalesce(IP.InvoicePosition_Coeff,1)) as Summa
					from
						v_InvoicePosition IP
						inner join v_Invoice I on I.Invoice_id = IP.Invoice_id
					where
						I.InvoiceType_id = 2 and IP.Shipment_id = Sh.Shipment_id
						{$reserveFilter}
						limit 1
				) t on true
			where
				Sh.InventoryItem_id = :InventoryItem_id
				and Sh.Storage_id = :Storage_id
				and Sh.Shipment_Count > 0
				and Sh.Shipment_Count-coalesce(t.Summa, 0) > 0
				{$shipmentFilter}
			order by
				{$order}
		";

        //echo getDebugSQL($query, $params);exit;
        $resp = $this->queryResult($query, $params);
        if (!$this->isSuccessful($resp)) {
            return $this->createError('', 'Ошибка при выборе партии для списания');
        }

        $result = array();
        foreach($resp as $item) {
            if ($item['Shipment_Count'] >= $need_count) {
                $item['Shipment_ReservedCount'] = $need_count / $coeff;
                $item['Shipment_Price'] *= $coeff;
                $result[] = $item;
                $need_count = 0;
                break;
            } else {
                $item['Shipment_ReservedCount'] = $item['Shipment_Count'] / $coeff;
                $item['Shipment_Price'] *= $coeff;
                $result[] = $item;
                $need_count -= $item['Shipment_Count'];
            }
        }

        return $result;
    }
}