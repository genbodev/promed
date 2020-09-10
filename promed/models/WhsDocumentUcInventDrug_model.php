<?php defined('BASEPATH') or die ('No direct script access allowed');

class WhsDocumentUcInventDrug_model extends SwModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Создание партии
	 */
	function createDrugShipment($data) {
		$params = array(
			'WhsDocumentSupply_id' => $data['WhsDocumentSupply_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@name varchar(30),
				@date date;
			set @name = isnull((
				select max(cast(DrugShipment_Name as bigint))+1
				from v_DrugShipment with(nolock)
				where ISNUMERIC(DrugShipment_Name)=1 and DrugShipment_Name not like '%.%' and DrugShipment_Name not like '%,%'
			), 1);
			set @date = dbo.tzGetDate();
			exec p_DrugShipment_ins
				@DrugShipment_id = @Res output,
				@DrugShipment_setDT = @date,
				@DrugShipment_Name = @name,
				@WhsDocumentSupply_id = :WhsDocumentSupply_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as DrugShipment_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при создании партии');
		}
		return $resp;
	}

	/**
	 * Ручное добавление медикамента в инветаризационной ведомости (для излишков)
	 */
	function save($data) {
		$this->beginTransaction();

		if (!empty($data['WhsDocumentUcInventDrug_FactKolvo']))
			$statusCode = 11; // 11 - Ввведено
		else
			$statusCode = 1; // 1 - Новый

		$params = array(
			'WhsDocumentUcInventDrug_id' => !empty($data['WhsDocumentUcInventDrug_id'])?$data['WhsDocumentUcInventDrug_id']:null,
			'WhsDocumentUcInvent_id' => $data['WhsDocumentUcInvent_id'],
			'WhsDocumentUcInventDrug_Kolvo' => 0,
			'WhsDocumentUcInventDrug_FactKolvo' => $data['WhsDocumentUcInventDrug_FactKolvo'],
			'WhsDocumentStatusType_id' => $this->getObjectIdByCode('WhsDocumentStatusType', $statusCode),
			'WhsDocumentUcInventDrug_Cost' => $data['WhsDocumentUcInventDrug_Cost'],
			'WhsDocumentUcInventDrug_Sum' => $data['WhsDocumentUcInventDrug_FactKolvo'] * $data['WhsDocumentUcInventDrug_Cost'],
			'StorageZone_id' => !empty($data['StorageZone_id'])?$data['StorageZone_id']:null,
			'Drug_id' => $data['Drug_id'],
			'SubAccountType_id' => 1,
			'GoodsUnit_id' => $data['GoodsUnit_id'],
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
		);

		//Создание/получение идентификатора серии
		$this->load->model('DocumentUc_model');
		$params['PrepSeries_id'] = $this->DocumentUc_model->savePrepSeries(array(
			'Drug_id' => $data['Drug_id'],
			'PrepSeries_Ser' => $data['PrepSeries_Ser'],
			'PrepSeries_GodnDate' => ConvertDateFormat($data['PrepSeries_GodnDate'], 'd.m.Y'),
			'pmUser_id' => $data['pmUser_id']
		));
		if (empty($params['PrepSeries_id'])) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при создании серии медикамента');
		}

		if (empty($data['WhsDocumentUcInventDrug_id'])) {
			$resp = $this->createDrugShipment(array(
				'WhsDocumentSupply_id' => $data['WhsDocumentSupply_id'],
				'pmUser_id' => $data['pmUser_id'],
			));
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
				select top 1 Person_id, Post_id
				from v_PersonWork with(nolock)
				where PersonWork_id = :PersonWork_id
			", $data);
		if (!is_array($PersonWork)) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при получении данных исполнителя');
		}

		$params = array(
			'DocumentUcStorageWork_id' => null,
			'WhsDocumentUcInventDrug_id' => $response['WhsDocumentUcInventDrug_id'],
			'DocumentUcTypeWork_id' => 4,	//Снятие остатков (инвентаризация)
			'Person_cid' => $PersonWork['Person_id'],
			'Post_cid' => $PersonWork['Post_id'],
			'Person_eid' => $PersonWork['Person_id'],
			'Post_eid' => $PersonWork['Post_id'],
			'DocumentUcStorageWork_FactQuantity' => $data['WhsDocumentUcInventDrug_FactKolvo'],
			'DocumentUcStorageWork_endDate' => date('Y-m-d H:i'),
		);
		if (!empty($data['WhsDocumentUcInventDrug_id'])) {
			$params['DocumentUcStorageWork_id'] = $this->getFirstResultFromQuery("
				select top 1 DocumentUcStorageWork_id
				from v_DocumentUcStorageWork with(nolock)
				where WhsDocumentUcInventDrug_id = :WhsDocumentUcInventDrug_id
			", $data);
			if (!$params['DocumentUcStorageWork_id']) {
				$this->rollbackTransaction();
				return $this->createError('','Ошибка при поиске наряда на проведения инвентаризации');
			}
		}

		$resp = $this->saveObject('DocumentUcStorageWork', $params);
		if (!empty($resp['Error_Msg'])) {
			$this->rollbackTransaction();
			return $this->createError('', $resp['Error_Msg']);
		}

		$this->commitTransaction();

		return array($response);
	}

	/**
	 * Удаление списка медикаментов из инвентаризационной ведомости
	 */
	function deleteList($data) {
		$list = explode(",", $data['WhsDocumentUcInventDrug_List']);

		$this->beginTransaction();

		foreach ($list as $WhsDocumentUcInventDrug_id) {
			$DocumentUcStorageWork_id = $this->getFirstResultFromQuery("
				select top 1 DocumentUcStorageWork_id
				from v_DocumentUcStorageWork t with(nolock) 
				where WhsDocumentUcInventDrug_id = :WhsDocumentUcInventDrug_id
			", array(
				'WhsDocumentUcInventDrug_id' => $WhsDocumentUcInventDrug_id
			), true);
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

		return array(array('success' => true));
	}

	/**
	 * Получение данных для редактирования медикаментов из инв. ведомости
	 */
	function load($data) {
		$this->load->model('DocumentUc_model', 'DocumentUc_model');

		$params = array(
			'WhsDocumentUcInventDrug_id' => $data['WhsDocumentUcInventDrug_id'],
			'DefaultGoodsUnit_id' => $this->DocumentUc_model->getDefaultGoodsUnitId()
		);
		$query = "
			select top 1
				WDUID.WhsDocumentUcInventDrug_id,
				WDUID.WhsDocumentUcInvent_id,
				WDUID.WhsDocumentUcInventDrug_FactKolvo,
				WDUID.StorageZone_id,
				WDUI.Org_id,
				WDUI.Storage_id,
				WDS.WhsDocumentSupply_id,
				WDSS.WhsDocumentSupplyStr_id,
				PS.PrepSeries_Ser,
				convert(varchar(10), PS.PrepSeries_GodnDate, 104) as PrepSeries_GodnDate,
				PW.PersonWork_id,
				GU.GoodsUnit_id,
				GU.GoodsUnit_Name
			from
				v_WhsDocumentUcInventDrug WDUID with(nolock)
				left join v_WhsDocumentUcInvent WDUI with(nolock) on WDUI.WhsDocumentUcInvent_id = WDUID.WhsDocumentUcInvent_id
				left join rls.v_PrepSeries PS with(nolock) on PS.PrepSeries_id = WDUID.PrepSeries_id
				left join v_DrugShipment DS with(nolock) on DS.DrugShipment_id = WDUID.DrugShipment_id
				left join v_WhsDocumentSupply WDS with(nolock) on WDS.WhsDocumentSupply_id = DS.WhsDocumentSupply_id
				left join v_GoodsUnit GU with (nolock) on GU.GoodsUnit_id = isnull(WDUID.GoodsUnit_id, :DefaultGoodsUnit_id)
				outer apply(
					select top 1
						case when WDSSD.Drug_sid = WDUID.Drug_id and WDSSD.WhsDocumentSupplySpecDrug_PriceSyn = WDUID.WhsDocumentUcInventDrug_Cost
							then 'SD.'+cast(WDSSD.WhsDocumentSupplySpecDrug_id as varchar)
							else 'S.'+cast(WDSS.WhsDocumentSupplySpec_id as varchar)
						end as WhsDocumentSupplyStr_id
					from
						v_WhsDocumentSupplySpec WDSS with(nolock)
						left join v_WhsDocumentSupplySpecDrug WDSSD with(nolock) on WDSSD.WhsDocumentSupplySpec_id = WDSS.WhsDocumentSupplySpec_id
					where 
						WDSS.WhsDocumentSupply_id = WDS.WhsDocumentSupply_id
						and (
							(WDSS.Drug_id = WDUID.Drug_id and WDSS.WhsDocumentSupplySpec_PriceNDS = WDUID.WhsDocumentUcInventDrug_Cost) or
							(WDSSD.Drug_sid = WDUID.Drug_id and WDSSD.WhsDocumentSupplySpecDrug_PriceSyn = WDUID.WhsDocumentUcInventDrug_Cost)
						)
				) WDSS
				left join v_DocumentUcStorageWork DUSW with(nolock) on DUSW.WhsDocumentUcInventDrug_id = WDUID.WhsDocumentUcInventDrug_id
				outer apply(
					select top 1 PW.PersonWork_id
					from v_PersonWork PW with(nolock)
					where PW.Person_id = DUSW.Person_eid and PW.Post_id = DUSW.Post_eid
				) PW
			where
				WDUID.WhsDocumentUcInventDrug_id = :WhsDocumentUcInventDrug_id
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Вспомогательная функция получения идентификатора объекта по коду
	 */
	function getObjectIdByCode($object_name, $code) {
		$query = "
			select top 1
				{$object_name}_id
			from
				v_{$object_name} with(nolock)
			where
				{$object_name}_Code = :code;
		";
		$result = $this->getFirstResultFromQuery($query, array(
			'code' => $code
		));

		return $result && $result > 0 ? $result : false;
	}
}