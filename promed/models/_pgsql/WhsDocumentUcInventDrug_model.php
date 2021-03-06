<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Class WhsDocumentUcInventDrug_model
 */
class WhsDocumentUcInventDrug_model extends SwPgModel
{

    /**
     * Создание партии
     * @param array $data
     * @return array|false
     */
    public function createDrugShipment($data)
    {
        $params = [
            'WhsDocumentSupply_id' => $data['WhsDocumentSupply_id'],
            'pmUser_id' => $data['pmUser_id']
        ];
        $query = "
		    with cte as (
		        select coalesce(
		        (
                    select
                        max(cast(DrugShipment_Name as bigint))+1
                    from
                        v_DrugShipment
                    where
                        ISNUMERIC(DrugShipment_Name) = 1 and DrugShipment_Name not ilike '%.%' and DrugShipment_Name not ilike '%,%'
			    ), 1) as name,
			    dbo.tzGetDate() as date
		    )
		    select
		        DrugShipment_id as \"DrugShipment_id\",
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
			from p_DrugShipment_ins
			(
				DrugShipment_setDT := (select date from cte),
				DrugShipment_Name := (select name from cte)::varchar,
				WhsDocumentSupply_id := :WhsDocumentSupply_id,
				pmUser_id := :pmUser_id
			)
		";
        $resp = $this->queryResult($query, $params);
        if (!is_array($resp)) {
            return $this->createError('','Ошибка при создании партии');
        }
        return $resp;
    }

    /**
     * Ручное добавление медикамента в инветаризационной ведомости (для излишков)
     *
     * @param $data
     * @return array|bool|false
     * @throws Exception
     */
    public function save($data)
    {
        $this->beginTransaction();

        if (!empty($data['WhsDocumentUcInventDrug_FactKolvo']))
            $statusCode = 11; // 11 - Ввведено
        else
            $statusCode = 1; // 1 - Новый

        $params = [
            'WhsDocumentUcInventDrug_id' => !empty($data['WhsDocumentUcInventDrug_id']) ? $data['WhsDocumentUcInventDrug_id'] : null,
            'WhsDocumentUcInvent_id' => $data['WhsDocumentUcInvent_id'],
            'WhsDocumentUcInventDrug_Kolvo' => 0,
            'WhsDocumentUcInventDrug_FactKolvo' => $data['WhsDocumentUcInventDrug_FactKolvo'],
            'WhsDocumentStatusType_id' => $this->getObjectIdByCode('WhsDocumentStatusType', $statusCode),
            'WhsDocumentUcInventDrug_Cost' => $data['WhsDocumentUcInventDrug_Cost'],
            'WhsDocumentUcInventDrug_Sum' => $data['WhsDocumentUcInventDrug_FactKolvo'] * $data['WhsDocumentUcInventDrug_Cost'],
            'StorageZone_id' => !empty($data['StorageZone_id']) ? $data['StorageZone_id'] : null,
            'Drug_id' => $data['Drug_id'],
            'SubAccountType_id' => 1,
            'GoodsUnit_id' => $data['GoodsUnit_id'],
            'Server_id' => $data['Server_id'],
            'pmUser_id' => $data['pmUser_id'],
        ];

        //Создание/получение идентификатора серии
        $this->load->model('DocumentUc_model');
        $params['PrepSeries_id'] = $this->DocumentUc_model->savePrepSeries([
            'Drug_id' => $data['Drug_id'],
            'PrepSeries_Ser' => $data['PrepSeries_Ser'],
            'PrepSeries_GodnDate' => ConvertDateFormat($data['PrepSeries_GodnDate'], 'd.m.Y'),
            'pmUser_id' => $data['pmUser_id']
        ]);
        if (empty($params['PrepSeries_id'])) {
            $this->rollbackTransaction();
            return $this->createError('', 'Ошибка при создании серии медикамента');
        }

        if (empty($data['WhsDocumentUcInventDrug_id'])) {
            $resp = $this->createDrugShipment([
                'WhsDocumentSupply_id' => $data['WhsDocumentSupply_id'],
                'pmUser_id' => $data['pmUser_id'],
            ]);
            if (!$this->isSuccessful($resp)) {
                $this->rollbackTransaction();
                return $resp;
            }
            $params['DrugShipment_id'] = $resp[0]['DrugShipment_id'];
        }

        $response = $this->saveObject('WhsDocumentUcInventDrug', $params);
        if (!empty($response['Error_Msg'])) {
            $this->rollbackTransaction();
            return $this->createError('', $response['Error_Msg']);
        }

        $PersonWork = $this->getFirstRowFromQuery("
				select 
				    Person_id as \"Person_id\",
                    Post_id as \"Post_id\"
				from
				    v_PersonWork
				where
				    PersonWork_id = :PersonWork_id
				limit 1
			", $data);
        if (!is_array($PersonWork)) {
            $this->rollbackTransaction();
            return $this->createError('', 'Ошибка при получении данных исполнителя');
        }

        $params = [
            'DocumentUcStorageWork_id' => null,
            'WhsDocumentUcInventDrug_id' => $response['WhsDocumentUcInventDrug_id'],
            'DocumentUcTypeWork_id' => 4,    //Снятие остатков (инвентаризация)
            'Person_cid' => $PersonWork['Person_id'],
            'Post_cid' => $PersonWork['Post_id'],
            'Person_eid' => $PersonWork['Person_id'],
            'Post_eid' => $PersonWork['Post_id'],
            'DocumentUcStorageWork_FactQuantity' => $data['WhsDocumentUcInventDrug_FactKolvo'],
            'DocumentUcStorageWork_endDate' => date('Y-m-d H:i'),
        ];
        if (!empty($data['WhsDocumentUcInventDrug_id'])) {
            $params['DocumentUcStorageWork_id'] = $this->getFirstResultFromQuery("
				select
				    DocumentUcStorageWork_id as \"DocumentUcStorageWork_id\"
				from
				    v_DocumentUcStorageWork
				where
				    WhsDocumentUcInventDrug_id = :WhsDocumentUcInventDrug_id
				limti 1
			", $data);
            if (!$params['DocumentUcStorageWork_id']) {
                $this->rollbackTransaction();
                return $this->createError('', 'Ошибка при поиске наряда на проведения инвентаризации');
            }
        }

        $resp = $this->saveObject('DocumentUcStorageWork', $params);
        if (!empty($resp['Error_Msg'])) {
            $this->rollbackTransaction();
            return $this->createError('', $resp['Error_Msg']);
        }

        $this->commitTransaction();

        return [$response];
    }

    /**
     * Удаление списка медикаментов из инвентаризационной ведомости
     *
     * @param $data
     * @return array
     */
    public function deleteList($data)
    {
        $list = explode(",", $data['WhsDocumentUcInventDrug_List']);

        $this->beginTransaction();

        foreach ($list as $WhsDocumentUcInventDrug_id) {
            $DocumentUcStorageWork_id = $this->getFirstResultFromQuery("
				select
				    DocumentUcStorageWork_id as \"DocumentUcStorageWork_id\"
				from
				    v_DocumentUcStorageWork t
				where
				    WhsDocumentUcInventDrug_id = :WhsDocumentUcInventDrug_id
				limit 1
			", [
                'WhsDocumentUcInventDrug_id' => $WhsDocumentUcInventDrug_id
            ], true);
            if ($DocumentUcStorageWork_id === false) {
                $this->rollbackTransaction();
                return $this->createError('', 'Ошибка при поиске наряда на выполнение инвентаризации');
            }

            if (!empty($DocumentUcStorageWork_id)) {
                $resp = $this->deleteObject('DocumentUcStorageWork', array(
                    'DocumentUcStorageWork_id' => $DocumentUcStorageWork_id
                ));
                if (!empty($resp['Error_Msg'])) {
                    $this->rollbackTransaction();
                    return $this->createError('', $resp['Error_Msg']);
                }
            }

            $resp = $this->deleteObject('WhsDocumentUcInventDrug', array(
                'WhsDocumentUcInventDrug_id' => $WhsDocumentUcInventDrug_id
            ));
            if (!empty($resp['Error_Msg'])) {
                $this->rollbackTransaction();
                return $this->createError('', $resp['Error_Msg']);
            }
        }

        $this->commitTransaction();

        return [['success' => true]];
    }

    /**
     * Получение данных для редактирования медикаментов из инв. ведомости
     * @param $data
     * @return array|false
     */
    public function load($data)
    {
        $this->load->model('DocumentUc_model', 'DocumentUc_model');

        $params = [
            'WhsDocumentUcInventDrug_id' => $data['WhsDocumentUcInventDrug_id'],
            'DefaultGoodsUnit_id' => $this->DocumentUc_model->getDefaultGoodsUnitId()
        ];
        $query = "
			select
				WDUID.WhsDocumentUcInventDrug_id as \"WhsDocumentUcInventDrug_id\",
				WDUID.WhsDocumentUcInvent_id as \"WhsDocumentUcInvent_id\",
				WDUID.WhsDocumentUcInventDrug_FactKolvo as \"WhsDocumentUcInventDrug_FactKolvo\",
				WDUID.StorageZone_id as \"StorageZone_id\",
				WDUI.Org_id as \"Org_id\",
				WDUI.Storage_id as \"Storage_id\",
				WDS.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
				WDSS.WhsDocumentSupplyStr_id as \"WhsDocumentSupplyStr_id\",
				PS.PrepSeries_Ser as \"PrepSeries_Ser\",
				to_char(PS.PrepSeries_GodnDate, 'dd.mm.yyyy') as \"PrepSeries_GodnDate\",
				PW.PersonWork_id as \"PersonWork_id\",
				GU.GoodsUnit_id as \"GoodsUnit_id\",
				GU.GoodsUnit_Name as \"GoodsUnit_Name\"
			from
				v_WhsDocumentUcInventDrug WDUID
				left join v_WhsDocumentUcInvent WDUI on WDUI.WhsDocumentUcInvent_id = WDUID.WhsDocumentUcInvent_id
				left join rls.v_PrepSeries PS on PS.PrepSeries_id = WDUID.PrepSeries_id
				left join v_DrugShipment DS on DS.DrugShipment_id = WDUID.DrugShipment_id
				left join v_WhsDocumentSupply WDS on WDS.WhsDocumentSupply_id = DS.WhsDocumentSupply_id
				left join v_GoodsUnit GU on GU.GoodsUnit_id = coalesce(WDUID.GoodsUnit_id, :DefaultGoodsUnit_id)
				left join lateral(
					select 
						case when WDSSD.Drug_sid = WDUID.Drug_id and WDSSD.WhsDocumentSupplySpecDrug_PriceSyn = WDUID.WhsDocumentUcInventDrug_Cost
							then 'SD.'||cast(WDSSD.WhsDocumentSupplySpecDrug_id as varchar)
							else 'S.'||cast(WDSS.WhsDocumentSupplySpec_id as varchar)
						end as WhsDocumentSupplyStr_id
					from
						v_WhsDocumentSupplySpec WDSS
						left join v_WhsDocumentSupplySpecDrug WDSSD on WDSSD.WhsDocumentSupplySpec_id = WDSS.WhsDocumentSupplySpec_id
					where 
						WDSS.WhsDocumentSupply_id = WDS.WhsDocumentSupply_id
						and (
							(WDSS.Drug_id = WDUID.Drug_id and WDSS.WhsDocumentSupplySpec_PriceNDS = WDUID.WhsDocumentUcInventDrug_Cost) or
							(WDSSD.Drug_sid = WDUID.Drug_id and WDSSD.WhsDocumentSupplySpecDrug_PriceSyn = WDUID.WhsDocumentUcInventDrug_Cost)
						)
					limit 1
				) WDSS on true
				left join v_DocumentUcStorageWork DUSW on DUSW.WhsDocumentUcInventDrug_id = WDUID.WhsDocumentUcInventDrug_id
				left join lateral (
					select
					    PW.PersonWork_id
					from
					    v_PersonWork PW
					where
					    PW.Person_id = DUSW.Person_eid and PW.Post_id = DUSW.Post_eid
					limit 1
				) PW on true
			where
				WDUID.WhsDocumentUcInventDrug_id = :WhsDocumentUcInventDrug_id
			limit 1
		";
        //echo getDebugSQL($query, $params);exit;
        return $this->queryResult($query, $params);
    }

    /**
     * Вспомогательная функция получения идентификатора объекта по коду
     */
    public function getObjectIdByCode($object_name, $code)
    {
        $query = "
			select
				{$object_name}_id
			from
				v_{$object_name}
			where
				{$object_name}_Code = cast(:code as varchar);
		";
        $result = $this->getFirstResultFromQuery($query, array(
            'code' => $code
        ));

        return $result && $result > 0 ? $result : false;
    }
}