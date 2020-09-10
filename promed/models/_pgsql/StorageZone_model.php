<?php defined('BASEPATH') or die ('No direct script access allowed');

class StorageZone_model extends swPgModel {
	var $objectName = "";
	var $objectKey = "";
	/**
	 *  Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	*  Читает дерево складов
	*/
	function loadStorageZoneTree($data) {
		$filter = "";
		$inner_filter = "(1=1)";
		
		if (!empty($data['Org_id'])) {
			$inner_filter .= " and COALESCE(ssl.Org_id, l.Org_id) = :Org_id";

		}
		if (!empty($data['LpuBuilding_id'])) {
			$inner_filter .= " and ssl.LpuBuilding_id = :LpuBuilding_id";
		}
		if (!empty($data['LpuSection_id'])) {
			$inner_filter .= " and ssl.LpuSection_id = :LpuSection_id";
		}
		if (!empty($data['Storage_id'])) {
			$filter .= " and s.Storage_id = :Storage_id";
		}

		if($data['level'] == 0){
			$query = "
				select
					null as \"id\",
					null as \"code\",
					rtrim(ltrim(COALESCE(s.Storage_Code,'')||' '||COALESCE(s.Storage_Name,''))) as \"name\",

					'StorageZone' as \"object\",
					s.Storage_id as \"Storage_id\",
					0 as \"leaf\",
					null as \"Liable_Object\"
				from
					v_Storage s 

					LEFT JOIN LATERAL (

						select
							count(StorageZone_id) as cnt
						from
							v_StorageZone 

						where
							(1=1)
							and StorageZone_pid IS NULL
							and Storage_id = s.Storage_id
					) scount ON true
				where
					(1=1)
					and s.Storage_id in (
						select
						    ssl.Storage_id
						from
						    v_StorageStructLevel ssl 

						    left join v_Lpu l  on l.Lpu_id = ssl.Lpu_id

						where
						    {$inner_filter}
					)
					{$filter}
				order by
					leaf,
					s.Storage_Name
			";
		} else {
			if (!empty($data['StorageZone_pid'])) {
				$filter .= " and sz.StorageZone_pid = :StorageZone_pid";
			} else {
				$filter .= " and sz.StorageZone_pid IS NULL";
			}
			$query = "
				select
					sz.StorageZone_id as \"id\",
					sz.StorageZone_Code as \"code\",
					rtrim(ltrim(COALESCE(sz.StorageZone_Code,'')||' '||COALESCE(sut.StorageUnitType_Name,''))) as \"name\",

					'StorageZone' as \"object\",
					s.Storage_id as \"Storage_id\",
					case when scount.cnt = 0 then 1 else 0 end as \"leaf\",
					case 
						when sz.TempConditionType_id = 1 and sz.StorageZone_IsPKU = 2 and sz.StorageZone_IsMobile = 2 and sl.StorageZoneLiable_ObjectName is null then 'storage-tree-child16-cold-pku-mobile'
						when sz.TempConditionType_id = 1 and sz.StorageZone_IsPKU = 2 and sz.StorageZone_IsMobile = 2 and sl.StorageZoneLiable_ObjectName = 'Бригада СМП' then 'storage-tree-child16-cold-pku-mobile-smp'
						when sz.TempConditionType_id = 1 and sz.StorageZone_IsPKU = 2 and sz.StorageZone_IsMobile = 2 and sl.StorageZoneLiable_ObjectName is not null then 'storage-tree-child16-cold-pku-mobile-man'
						when sz.TempConditionType_id = 1 and sz.StorageZone_IsPKU = 2 then 'storage-tree-child16-cold-pku'
						when sz.TempConditionType_id = 1 then 'storage-tree-child16-cold'
						when sz.StorageZone_IsPKU = 2 and sz.StorageZone_IsMobile = 2 and sl.StorageZoneLiable_ObjectName is null then 'storage-tree-child16-pku-mobile'
						when sz.StorageZone_IsPKU = 2 and sz.StorageZone_IsMobile = 2 and sl.StorageZoneLiable_ObjectName = 'Бригада СМП' then 'storage-tree-child16-pku-mobile-smp'
						when sz.StorageZone_IsPKU = 2 and sz.StorageZone_IsMobile = 2 and sl.StorageZoneLiable_ObjectName is not null then 'storage-tree-child16-pku-mobile-man'
						when sz.StorageZone_IsPKU = 2 then 'storage-tree-child16-pku'
						when sz.StorageZone_IsMobile = 2 and sl.StorageZoneLiable_ObjectName is null then 'storage-tree-child16-mobile'
						when sz.StorageZone_IsMobile = 2 and sl.StorageZoneLiable_ObjectName = 'Бригада СМП' then 'storage-tree-child16-mobile-smp'
						when sz.StorageZone_IsMobile = 2 and sl.StorageZoneLiable_ObjectName is not null then 'storage-tree-child16-mobile-man'
					end as \"iconCls\",
					sl.StorageZoneLiable_ObjectName as \"Liable_Object\",
					sl.StorageZoneLiable_ObjectId as \"Liable_ObjectId\",
					sz.StorageZone_IsMobile as \"StorageZone_IsMobile\",
					o_ssl.Org_id as \"Org_id\",
					o_ssl.LpuBuilding_id as \"LpuBuilding_id\",
					o_ssl.LpuSection_id as \"LpuSection_id\",
					sz.StorageZone_AdditionalInfo as \"Comment\"
				from
					v_Storage s 

					LEFT JOIN LATERAL (

					    select 
					        COALESCE(ssl.Org_id, l.Org_id) as Org_id,

                            ssl.LpuBuilding_id,
                            ssl.LpuSection_id
						from
						    v_StorageStructLevel ssl 

						    left join v_Lpu l  on l.Lpu_id = ssl.Lpu_id

						where
						    ssl.Storage_id = s.Storage_id 
                            and
						    {$inner_filter}
						order by
						    ssl.StorageStructLevel_id
                        limit 1
					) o_ssl ON true
					left join v_StorageZone sz  on sz.Storage_id = s.Storage_id

					left join v_StorageUnitType sut  on sut.StorageUnitType_id = sz.StorageUnitType_id

					LEFT JOIN LATERAL (

						select
							count(StorageZone_id) as cnt
						from
							v_StorageZone 

						where
							StorageZone_pid = sz.StorageZone_id
					) scount ON true
					LEFT JOIN LATERAL (

						select 
							szl.StorageZoneLiable_ObjectId,
							szl.StorageZoneLiable_ObjectName
						from
							v_StorageZoneLiable szl 

						where
							szl.StorageZone_id = sz.StorageZone_id
							and szl.StorageZoneLiable_endDate is null
                        limit 1
					) sl ON true
				where
					(1=1)
					and s.Storage_id in (
						select
						    ssl.Storage_id
						from
						    v_StorageStructLevel ssl 

						    left join v_Lpu l  on l.Lpu_id = ssl.Lpu_id

						where
						    {$inner_filter}
					)
					{$filter}
				order by
					leaf,
					sut.StorageUnitType_Name
			";
		}
		

		//echo getDebugSql($query, $data); exit();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$result = $result->result('array');
			leafToInt($result);
			if($data['level'] == 1){
				// Добавление пункта Без места хранения
				$without = array(
					'0' => array(
						'id'=>null,
						'code'=>null,
						'name'=>'Без места хранения',
						'without_sz'=>1,
						'object'=>'StorageZone',
						'Storage_id'=>$data['Storage_id'],
						'Liable_Object'=>null,
						'Liable_ObjectId'=>null,
						'StorageZone_IsMobile'=>null,
						'leaf'=>1,
						'Comment'=>''
					)
				);
				if(count($result) == 1 && empty($result[0]['id'])){
					$result = $without;
				} else {
					$result = array_merge($without,$result);
				}
			}
			return $result;
		}
		else {
			return false;
		}
	}

	/**
	*  Сохранение места хранения
	*/
	function saveStorageZone($data) {
		if (!empty($data['StorageZone_id'])) {
			$procedure = "upd";
		} else {
			$procedure = "ins";
		}
		
		$query = "
			select StorageZone_id as \"StorageZone_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from dbo.p_StorageZone_".$procedure."(
				StorageZone_id := :StorageZone_id,
				StorageZone_pid := :StorageZone_pid,
				StorageZone_Code := :StorageZone_Code,
				Storage_id := :Storage_id,
				StorageUnitType_id := :StorageUnitType_id,
				StorageZone_IsPKU := :StorageZone_IsPKU,
				StorageZone_IsMobile := :StorageZone_IsMobile,
				TempConditionType_id := :TempConditionType_id,
				StorageZone_Address := :StorageZone_Address,
				StorageZone_AdditionalInfo := :StorageZone_AdditionalInfo,
				StorageZone_begDate := :StorageZone_begDate,
				StorageZone_endDate := :StorageZone_endDate,
				pmUser_id := :pmUser_id);


		";

		//echo getDebugSql($query, $data); exit();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	*  Удаление места хранения
	*/
	function deleteStorageZone($data) {
		$query = "
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from dbo.p_StorageZone_del(
				StorageZone_id := :StorageZone_id);


		";

		//echo getDebugSql($query, $data); exit();
		$result = $this->db->query($query, array('StorageZone_id'=>$data['StorageZone_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	*  Проверка наличия места хранения в журнале перемещений
	*/
	function checkStorageZoneJournal($data) {
		if (empty($data['StorageZone_id'])){
			return false;
		}
		$filter = "";
		$query = "
			select
				'1' as \"res\"
			from
				v_StorageDrugMove sdm 

			where
				sdm.StorageZone_oid = :StorageZone_id or sdm.StorageZone_nid = :StorageZone_id
			union all 
			select
				'1' as \"res\"
			from
				v_DrugStorageZone dsz 

			where
				dsz.StorageZone_id = :StorageZone_id
		";

		//echo getDebugSql($query, $data); exit();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	*  Проверка наличия дочерних мест хранения
	*/
	function checkStorageZoneChilds($data) {
		if (empty($data['StorageZone_id'])){
			return false;
		}
		$filter = "";
		$query = "
			select 1
			from
				v_StorageZone 

			where
				StorageZone_pid = :StorageZone_id
            limit 1
		";

		//echo getDebugSql($query, $data); exit();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	*  Читает список мест хранения склада
	*/
	function loadStorageZoneList($data) {
		if (!empty($data['withStorageOnly']) && empty($data['Storage_id'])){
			return false;
		}
		$filter = "";
		if (!empty($data['Storage_id'])){
			$filter .= " and sz.Storage_id = :Storage_id";
		}
		if(!empty($data['exceptStorageZone_id'])){
			$filter .= " and sz.StorageZone_id <> :exceptStorageZone_id";
		}
		if (
			!empty($data['LpuBuilding_id']) || !empty($data['LpuSection_id']) || !empty($data['OrgStruct_id'])
		) {
			$struct_filter = "1=1";
			if (!empty($data['LpuBuilding_id'])) {
				$struct_filter .= " and SSL.LpuBuilding_id = :LpuBuilding_id";
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
			}
			if (!empty($data['LpuSection_id'])) {
				$struct_filter .= " and SSL.LpuSection_id = :LpuSection_id";
				$params['LpuSection_id'] = $data['LpuSection_id'];
			}
			if (!empty($data['OrgStruct_id'])) {
				$struct_filter .= " and SSL.OrgStruct_id = :OrgStruct_id";
				$params['OrgStruct_id'] = $data['OrgStruct_id'];
			}
			$filter .= " and s.Storage_id in (
				select Storage_id
				from v_StorageStructLevel SSL 

				where {$struct_filter}
			)";
		}
		$query = "
			select
				sz.StorageZone_id as \"StorageZone_id\",
				sz.StorageZone_Code as \"StorageZone_Code\",
				sut.StorageUnitType_Name as \"StorageZone_Name\",
				sz.StorageZone_Address as \"StorageZone_Address\"
			from
				v_StorageZone sz 

				left join v_StorageUnitType sut  on sut.StorageUnitType_id = sz.StorageUnitType_id

				left join v_Storage s  on s.Storage_id = sz.Storage_id

			where
				(1=1)
				{$filter}
		";

		//echo getDebugSql($query, $data); exit();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	*  Читает данные места хранения склада
	*/
	function loadStorageZone($data) {
		if (empty($data['StorageZone_id'])){
			return false;
		}
		$filter = "";
		$query = "
			select
				sz.StorageZone_id as \"StorageZone_id\",
				sz.StorageZone_pid as \"StorageZone_pid\",
				sz.Storage_id as \"Storage_id\",
				sz.StorageZone_Code as \"StorageZone_Code\",
				sz.StorageUnitType_id as \"StorageUnitType_id\",
				sz.StorageZone_IsMobile as \"StorageZone_IsMobile\",
				sz.StorageZone_IsPKU as \"StorageZone_IsPKU\",
				sz.StorageZone_Address as \"StorageZone_Address\",
				sz.StorageZone_AdditionalInfo as \"StorageZone_AdditionalInfo\",
				sz.TempConditionType_id as \"TempConditionType_id\",
				to_char(sz.StorageZone_begDate, 'DD.MM.YYYY') as \"StorageZone_begDate\",

				to_char(sz.StorageZone_endDate, 'DD.MM.YYYY') as \"StorageZone_endDate\",

				COALESCE(ssl.Org_id,lpu.Org_id) as \"Org_id\",

				ssl.LpuBuilding_id as \"LpuBuilding_id\",
				ssl.LpuSection_id as \"LpuSection_id\",
				mol.Mol_id as \"Mol_id\"
			from
				v_StorageZone sz 

				left join v_StorageStructLevel ssl  on ssl.Storage_id = sz.Storage_id

				left join v_Lpu lpu  on lpu.Lpu_id = ssl.Lpu_id

				LEFT JOIN LATERAL (

					select 
						m.Mol_id
					from v_Mol m 

					where m.Storage_id = sz.Storage_id
                    limit 1
				) mol ON true
 			where
				(1=1)
				and sz.StorageZone_id = :StorageZone_id
				{$filter}
		";

		//echo getDebugSql($query, $data); exit();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	*  Проверка уровня иерархии
	*/
	function checkStorageZoneHierarchy($data) {
		if (empty($data['StorageZone_pid'])){
			return true;
		}
		$query = "
			select
				sz5.StorageZone_id as \"StorageZone_id\"
			from
				v_StorageZone sz 

				left join v_StorageZone sz1  on sz1.StorageZone_id = sz.StorageZone_pid

				left join v_StorageZone sz2  on sz2.StorageZone_id = sz1.StorageZone_pid

				left join v_StorageZone sz3  on sz3.StorageZone_id = sz2.StorageZone_pid

				left join v_StorageZone sz4  on sz4.StorageZone_id = sz3.StorageZone_pid

				left join v_StorageZone sz5  on sz5.StorageZone_id = sz4.StorageZone_pid

			where
				(1=1)
				and sz.StorageZone_id = :StorageZone_pid
		";

		//echo getDebugSql($query, $data); exit();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$result = $result->result('array');
			if(!empty($result[0]['StorageZone_id'])){
				return array('Error_Msg'=>'Создать указанное место хранения не возможно – превышен уровень вложенности мест хранения. Измените размещение места хранения.');
			} else {
				return true;
			}
		}
		else {
			return false;
		}
	}

	/**
	*  Формирование адреса места хранения
	*/
	function formStorageZoneAddress($data) {
		if(empty($data['StorageUnitType_id'])){
			return array('Error_Msg'=>'Не указано наименование сохраняемого места хранения');
		}
		if(empty($data['Storage_id'])){
			return array('Error_Msg'=>'Не указан склад для сохраняемого места хранения');
		}
		if(empty($data['StorageZone_Code'])){
			return array('Error_Msg'=>'Не указан код сохраняемого места хранения');
		}
		$address = "";
		$subaddress = "";
		// Первая часть адреса - склад
		$query = "
			select  
				Storage_Code as \"address\"
			from v_Storage  

			where Storage_id = :Storage_id
			limit 1
		";
		$result = $this->queryResult($query, $data);
		if(!empty($result[0]['address'])){
			$address .= ('Ск'.$result[0]['address'].'.');
		}
		// Все родительские места хранения
		if(!empty($data['StorageZone_pid'])){
			$res = array('StorageZone_pid'=>$data['StorageZone_pid']);
			do {
				$params = array('StorageZone_pid'=>$res['StorageZone_pid']);
				$query = "
					select
						rtrim(ltrim(COALESCE(sut.StorageUnitType_Code,'')||COALESCE(sz.StorageZone_Code,''))) as \"address\",

						sz.StorageZone_pid as \"StorageZone_pid\"
					from
						v_StorageZone sz 

						left join v_StorageUnitType sut  on sut.StorageUnitType_id = sz.StorageUnitType_id

					where
						sz.StorageZone_id = :StorageZone_pid
				";
				$result = $this->queryResult($query, $params);
				if(!empty($result[0]['address'])){
					$subaddress = ($result[0]['address'].'.').$subaddress;
				}
				$res['StorageZone_pid'] = (!empty($result[0]['StorageZone_pid'])?$result[0]['StorageZone_pid']:null);
			} while (!empty($res['StorageZone_pid']));
		}
		$address .= $subaddress;
		// Текущее сохраняемое место хранения
		$query = "
			select  
				StorageUnitType_Code as \"address\"
			from v_StorageUnitType  

			where StorageUnitType_id = :StorageUnitType_id
			limit 1
		";
		$result = $this->queryResult($query, $data);
		if(!empty($result[0]['address'])){
			$address .= ($result[0]['address'].$data['StorageZone_Code'].'.');
		}
		return $address;
	}

	/**
	*  Формирование адреса места хранения
	*/
	function updateChildsStorageZoneAddress($data) {
		if(empty($data['StorageZone_id'])){
			return false;
		}
		$childs = $this->getAllChildStorageZones($data); // вернет минимум 1 ид, т.к. метод возвращает все дочерние, включая переданный ид
		if(is_array($childs) && count($childs)>1){
			foreach ($childs as $value) {
				if($value != $data['StorageZone_id']){
					$sz = $this->loadStorageZone(array('StorageZone_id'=>$value));
					if(!empty($sz[0]['StorageZone_id'])){
						$address = $this->formStorageZoneAddress($sz[0]);
						if(!empty($address['Error_Msg'])){
							return array('Error_Msg'=>'Ошибка при формировании адресов дочерних мест хранения');
						} else {
							$sz[0]['StorageZone_Address'] = $address;
						}
						$sz[0]['pmUser_id'] = $data['pmUser_id'];
						if(!empty($sz[0]['StorageZone_begDate'])){
							$sz[0]['StorageZone_begDate'] = date('Y-m-d', strtotime($sz[0]['StorageZone_begDate']));
						}
						if(!empty($sz[0]['StorageZone_endDate'])){
							$sz[0]['StorageZone_endDate'] = date('Y-m-d', strtotime($sz[0]['StorageZone_endDate']));
						}
						$resp = $this->saveStorageZone($sz[0]);
						if(!empty($resp[0]['Error_Msg'])){
							return $resp;
						}
					} else {
						return array('Error_Msg'=>'Ошибка при загрузке дочерних мест хранения');
					}
				}
			}
		}

	}

	/**
	*  Загрузка списка медикаментов (на влкадке По местам хранения)
	*/
	function loadDrugGrid($data) {
		$this->load->model("DocumentUc_model", "DocumentUc_model");
		$data['DefaultGoodsUnit_id'] = $this->DocumentUc_model->getDefaultGoodsUnitId();
		$filter = "";

		if(!empty($data['StorageZone_id'])){
			$all_drugs = array();
			// Загружаем список всех дочерних мест хранения + изначальное которой пришло с запросом
			$childs = $this->getAllChildStorageZones($data);
			foreach ($childs as $value) {
				// Загружаем медикаменты для каждого места хранения
				$data['StorageZone_id'] = $value;
				$drugs = $this->loadStorageZoneDrugGrid($data);
				if(is_array($drugs) && count($drugs)>0){
					$all_drugs = array_merge($all_drugs,$drugs);
				}
			}
			return $all_drugs;
		} else {
			if (!empty($data['Drug_Name'])) {
				$filter .= " and dor.Drug_Name iLIKE ('%'||:Drug_Name||'%')";

			}
			if (!empty($data['DrugComplexMnn_Name'])) {
				$filter .= " and dor.DrugComplexMnn_RusName iLIKE ('%'||:DrugComplexMnn_Name||'%')";

			}
			if (!empty($data['DrugFinance_id'])) {
				$filter .= " and dor.DrugFinance_id = :DrugFinance_id";
			}
			if (!empty($data['WhsDocumentCostItemType_id'])) {
				$filter .= " and dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
			}
			if (!empty($data['GoodsUnit_id'])) {
				$filter .= " and gu.GoodsUnit_id = :GoodsUnit_id";
			}
			$filter .= " and s.Storage_id = :Storage_id";
			
			if(!empty($data['Without_sz'])){
				$filter .= " and ostat.kolvo > 0";
				$filter .= " and COALESCE(drug_sz.Drug_Count,0) < ostat.kolvo";

				$query = "
					select distinct
						ds.DrugShipment_id as \"DrugShipment_id\",
						dors.DrugOstatRegistry_ids as \"DrugOstatRegistry_ids\",
						dors.DrugOstatRegistry_ids as \"DrugListKey\",
						dor.Drug_id as \"Drug_id\",
						dor.Drug_Code as \"Drug_Code\",
						dor.Drug_Name as \"Drug_Name\",
						dor.PrepSeries_Ser as \"PrepSeries_Ser\",
						s.Storage_id as \"Storage_id\",
						dor.PrepSeries_GodnDate as \"PrepSeries_GodnDate\",
						(ostat.kolvo - COALESCE(drug_sz.Drug_Count,0)) as \"DrugCount\",

						(ostat.kolvo - COALESCE(drug_sz.Drug_Count,0)) as \"Drug_Kolvo\",

						gu.GoodsUnit_Name as \"GoodsUnit_Name\",
						rtrim(ltrim( COALESCE(dor.DrugOstatRegistry_Cost,'') || ' ' || COALESCE(dor.DrugFinance_Name,'') || ' ' || COALESCE(dor.WhsDocumentCostItemType_Name,'') || ' ' || COALESCE(wds.WhsDocumentUc_Num,'') || ' ' || COALESCE(ds.DrugShipment_Name,'')  || ' ' || COALESCE(to_char(ds.DrugShipment_setDT, 'DD.MM.YYYY'),''))) as \"DrugShipment_Row\"


					from
						v_Storage s 

						inner join v_StorageStructLevel ssl  on ssl.Storage_id = s.Storage_id

						left join v_Lpu lpu  on lpu.Lpu_id = ssl.Lpu_id

						inner join v_DrugOstatRegistry door  on COALESCE(ssl.Org_id,lpu.Org_id) = door.Org_id and s.Storage_id = door.Storage_id


						left join v_DrugShipment ds  on ds.DrugShipment_id = door.DrugShipment_id

						left join v_WhsDocumentSupply wds  on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id

						left join v_GoodsUnit gu  on COALESCE(gu.GoodsUnit_id, 
                        :DefaultGoodsUnit_id
                        ) = COALESCE(door.GoodsUnit_id, 
                        :DefaultGoodsUnit_id
                        )


						LEFT JOIN LATERAL (

							SELECT 
								(SELECT
									string_agg(cast(dor2.DrugOstatRegistry_id as varchar(20)),',')
								FROM
									v_DrugOstatRegistry dor2 

								WHERE
									COALESCE(ssl.Org_id,lpu.Org_id) = dor2.Org_id 

									and s.Storage_id = dor2.Storage_id
									and ds.DrugShipment_id = dor2.DrugShipment_id
								
								) as DrugOstatRegistry_ids 
						) dors ON true
						LEFT JOIN LATERAL (

							select 
								dor3.Drug_id,
								d.Drug_Code,
								d.Drug_Name,
								dcm.DrugComplexMnn_RusName,
								ps.PrepSeries_Ser,
								to_char(ps.PrepSeries_GodnDate, 'DD.MM.YYYY') as PrepSeries_GodnDate,

								cast(dor3.DrugOstatRegistry_Cost as varchar(20)) as DrugOstatRegistry_Cost,
								dor3.DrugFinance_id,
								df.DrugFinance_Name,
								dor3.WhsDocumentCostItemType_id,
								wcit.WhsDocumentCostItemType_Name
							from v_DrugOstatRegistry dor3 

							left join rls.v_Drug d  on d.Drug_id = dor3.Drug_id

							left join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id

							left join rls.v_PrepSeries ps  on ps.PrepSeries_id = dor3.PrepSeries_id

							left join v_DrugFinance df  on df.DrugFinance_id = dor3.DrugFinance_id

							left join v_WhsDocumentCostItemType wcit  on wcit.WhsDocumentCostItemType_id = dor3.WhsDocumentCostItemType_id

							where COALESCE(ssl.Org_id,lpu.Org_id) = dor3.Org_id 

								and s.Storage_id = dor3.Storage_id
								and ds.DrugShipment_id = dor3.DrugShipment_id
                            limit 1
						) dor ON true
						LEFT JOIN LATERAL (

							select 
								sum(dor4.DrugOstatRegistry_Kolvo) as kolvo
							from v_DrugOstatRegistry dor4 

							where COALESCE(ssl.Org_id,lpu.Org_id) = dor4.Org_id 

								and s.Storage_id = dor4.Storage_id
								and ds.DrugShipment_id = dor4.DrugShipment_id
						) ostat ON true
						LEFT JOIN LATERAL (

							select
								sum(dsz.DrugStorageZone_Count) as Drug_Count
							from 
								v_DrugStorageZone dsz 

								inner join v_StorageZone sz  on dsz.StorageZone_id = sz.StorageZone_id

							where 
								dsz.Drug_id = door.Drug_id
								and sz.Storage_id = door.Storage_id
								and COALESCE(door.PrepSeries_id,0) = COALESCE(dsz.PrepSeries_id,0)

								and COALESCE(door.DrugShipment_id,0) = COALESCE(dsz.DrugShipment_id,0)

								and COALESCE(door.GoodsUnit_id, 
                                :DefaultGoodsUnit_id
                                ) = COALESCE(dsz.GoodsUnit_id, 
                                :DefaultGoodsUnit_id
                                )

						) drug_sz ON true
					where
						(1=1)
						{$filter}
				";
			} else {
				$filter .= " and ostat.kolvo > 0";
				$query = "
					select distinct
						ds.DrugShipment_id as \"DrugShipment_id\",
						dors.DrugOstatRegistry_ids as \"DrugListKey\",
						dors.DrugOstatRegistry_ids as \"DrugOstatRegistry_ids\",
						dor.Drug_id as \"Drug_id\",
						dor.Drug_Code as \"Drug_Code\",
						dor.Drug_Name as \"Drug_Name\",
						dor.PrepSeries_Ser as \"PrepSeries_Ser\",
						dor.PrepSeries_GodnDate as \"PrepSeries_GodnDate\",
						ostat.kolvo as \"DrugCount\",
						gu.GoodsUnit_Name as \"GoodsUnit_Name\",
						rtrim(ltrim( COALESCE(dor.DrugOstatRegistry_Cost,'') || ' ' || COALESCE(dor.DrugFinance_Name,'') || ' ' || COALESCE(dor.WhsDocumentCostItemType_Name,'') || ' ' || COALESCE(wds.WhsDocumentUc_Num,'') || ' ' || COALESCE(ds.DrugShipment_Name,'')  || ' ' || COALESCE(to_char(ds.DrugShipment_setDT, 'DD.MM.YYYY'),''))) as \"DrugShipment_Row\"


					from
						v_Storage s 

						inner join v_StorageStructLevel ssl  on ssl.Storage_id = s.Storage_id

						left join v_Lpu lpu  on lpu.Lpu_id = ssl.Lpu_id

						inner join v_DrugOstatRegistry door  on COALESCE(ssl.Org_id,lpu.Org_id) = door.Org_id and s.Storage_id = door.Storage_id


						left join v_DrugShipment ds  on ds.DrugShipment_id = door.DrugShipment_id

						left join v_WhsDocumentSupply wds  on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id						

						left join v_GoodsUnit gu  on COALESCE(gu.GoodsUnit_id, 
                        :DefaultGoodsUnit_id
                        ) = COALESCE(door.GoodsUnit_id, 
                        :DefaultGoodsUnit_id
                        )


						LEFT JOIN LATERAL (

							SELECT 
								(SELECT
									string_agg(cast(dor2.DrugOstatRegistry_id as varchar(20)),',')
								FROM
									v_DrugOstatRegistry dor2 

								WHERE
									COALESCE(ssl.Org_id,lpu.Org_id) = dor2.Org_id 

									and s.Storage_id = dor2.Storage_id
									and ds.DrugShipment_id = dor2.DrugShipment_id
							) as DrugOstatRegistry_ids 
						) dors ON true
						LEFT JOIN LATERAL (

							select 
								dor3.Drug_id,
								d.Drug_Code,
								d.Drug_Name,
								dcm.DrugComplexMnn_RusName,
								ps.PrepSeries_Ser,
								to_char(ps.PrepSeries_GodnDate, 'DD.MM.YYYY') as PrepSeries_GodnDate,

								cast(dor3.DrugOstatRegistry_Cost as varchar(20)) as DrugOstatRegistry_Cost,
								dor3.DrugFinance_id,
								df.DrugFinance_Name,
								dor3.WhsDocumentCostItemType_id,
								wcit.WhsDocumentCostItemType_Name
							from v_DrugOstatRegistry dor3 

							left join rls.v_Drug d  on d.Drug_id = dor3.Drug_id

							left join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id

							left join rls.v_PrepSeries ps  on ps.PrepSeries_id = dor3.PrepSeries_id

							left join v_DrugFinance df  on df.DrugFinance_id = dor3.DrugFinance_id

							left join v_WhsDocumentCostItemType wcit  on wcit.WhsDocumentCostItemType_id = dor3.WhsDocumentCostItemType_id

							where COALESCE(ssl.Org_id,lpu.Org_id) = dor3.Org_id 

								and s.Storage_id = dor3.Storage_id
								and ds.DrugShipment_id = dor3.DrugShipment_id
                            limit 1
						) dor ON true
						LEFT JOIN LATERAL (

							select 
								sum(dor4.DrugOstatRegistry_Kolvo) as kolvo
							from v_DrugOstatRegistry dor4 

							where COALESCE(ssl.Org_id,lpu.Org_id) = dor4.Org_id 

								and s.Storage_id = dor4.Storage_id
								and ds.DrugShipment_id = dor4.DrugShipment_id
						) ostat ON true
					where
						(1=1)
						{$filter}
				";
			}
			//echo getDebugSql($query, $data); exit();
			$result = $this->db->query($query, $data);

			if ( is_object($result) ) {
				$result = $result->result('array');
				return $result;
			}
			else {
				return false;
			}
		}
	}

	/**
	*  Загрузка списка медикаментов по месту хранения (используется при загрузке из дерева мест хранения)
	*/
	function loadStorageZoneDrugGrid($data) {
        $this->load->model("DocumentUc_model", "DocumentUc_model");
        $data['DefaultGoodsUnit_id'] = $this->DocumentUc_model->getDefaultGoodsUnitId();

		$filter = "";
		
		if (!empty($data['Drug_Name'])) {
			$filter .= " and d.Drug_Name iLIKE ('%'||:Drug_Name ||'%')";

		}
		if (!empty($data['DrugMnnComplex_Name'])) {
			$filter .= " and dcm.DrugComplexMnn_RusName iLIKE ('%'||:DrugMnnComplex_Name||'%')";

		}
		if (!empty($data['DrugFinance_id'])) {
			$filter .= " and stor.DrugFinance_id = :DrugFinance_id";
		}
		if (!empty($data['WhsDocumentCostItemType_id'])) {
			$filter .= " and stor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
		}
        $data['DefaultGoodsUnit_id'] = $this->DocumentUc_model->getDefaultGoodsUnitId();

		if(!empty($data['StorageZone_id'])){
			$query = "
				select
					(cast(dsz.DrugStorageZone_id as varchar(20)) ||''|| stors.DrugOstatRegistry_ids) as \"DrugListKey\",
					dsz.DrugStorageZone_id as \"DrugStorageZone_id\",
					dsz.StorageZone_id as \"StorageZone_id\",
					dsz.Drug_id as \"Drug_id\",
					d.Drug_Code as \"Drug_Code\",
					d.Drug_Name as \"Drug_Name\",
					ps.PrepSeries_Ser as \"PrepSeries_Ser\",
					dsz.PrepSeries_id as \"PrepSeries_id\",
					dsz.DrugShipment_id as \"DrugShipment_id\",
					to_char(ps.PrepSeries_GodnDate, 'DD.MM.YYYY') as \"PrepSeries_GodnDate\",

					dsz.DrugStorageZone_Count as \"DrugCount\",
					dsz.DrugStorageZone_Count as \"Drug_Kolvo\",
					gu.GoodsUnit_id as \"GoodsUnit_id\",
					gu.GoodsUnit_Name as \"GoodsUnit_Name\",
					dsl.DocumentUcStr_id as \"DocumentUcStr_oid\",
					COALESCE(stor.DrugFinance_id,dus_ds.DrugFinance_id) as \"DrugFinance_oid\",

					stor.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
					stor.Storage_id as \"Storage_id\",
					stors.DrugOstatRegistry_ids as \"DrugOstatRegistry_ids\",
					cast(stor.DrugOstatRegistry_Cost as varchar(20)) as \"DrugOstatRegistry_Cost\",
					rtrim(ltrim( COALESCE(cast(stor.DrugOstatRegistry_Cost as varchar(20)),'') || ' ' || COALESCE(stor.DrugFinance_Name,'') || ' ' || COALESCE(stor.WhsDocumentCostItemType_Name,'') || ' ' || COALESCE(wds.WhsDocumentUc_Num,'') || ' ' || COALESCE(ds.DrugShipment_Name,'')  || ' ' || COALESCE(to_char(ds.DrugShipment_setDT, 'DD.MM.YYYY'),''))) as \"DrugShipment_Row\"
                    


				from
					v_DrugStorageZone dsz 

					left join rls.v_Drug d  on d.Drug_id = dsz.Drug_id

					left join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id

					left join rls.v_PrepSeries ps  on ps.PrepSeries_id = dsz.PrepSeries_id

					left join v_DrugShipment ds  on ds.DrugShipment_id = dsz.DrugShipment_id

					left join v_DrugShipmentLink dsl  on dsl.DrugShipment_id = ds.DrugShipment_id

					left join v_DocumentUcStr dus_ds  on dus_ds.DocumentUcStr_id = dsl.DocumentUcStr_id

					left join v_WhsDocumentSupply wds  on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id

					left join v_GoodsUnit gu  on COALESCE(gu.GoodsUnit_id, 
                    :DefaultGoodsUnit_id
                    ) = COALESCE(dsz.GoodsUnit_id, 
                    :DefaultGoodsUnit_id
                    )


					LEFT JOIN LATERAL (

						select 
							sz.Storage_id,
							df.DrugFinance_Name,
							wcit.WhsDocumentCostItemType_Name,
							dor.DrugFinance_id,
							dor.WhsDocumentCostItemType_id,
							dor.DrugOstatRegistry_id,
							dor.DrugOstatRegistry_Cost
						from 
							v_StorageZone sz 

							left join v_StorageStructLevel ssl  on ssl.Storage_id = sz.Storage_id

							left join v_Lpu lpu  on lpu.Lpu_id = ssl.Lpu_id

							left join v_DrugOstatRegistry dor  on COALESCE(ssl.Org_id,lpu.Org_id) = dor.Org_id 


								and sz.Storage_id = dor.Storage_id
								and dsz.Drug_id = dor.Drug_id
								and COALESCE(dsz.PrepSeries_id,0) = COALESCE(dor.PrepSeries_id,0)

								and COALESCE(dsz.DrugShipment_id,0) = COALESCE(dor.DrugShipment_id,0)

								and COALESCE(dsz.GoodsUnit_id, 
                                :DefaultGoodsUnit_id
                                ) = COALESCE(dor.GoodsUnit_id, 
                                :DefaultGoodsUnit_id
                                )

							left join v_DrugFinance df  on df.DrugFinance_id = dor.DrugFinance_id

							left join v_WhsDocumentCostItemType wcit  on wcit.WhsDocumentCostItemType_id = dor.WhsDocumentCostItemType_id

						where 
							sz.StorageZone_id = dsz.StorageZone_id
                        limit 1
					) stor ON true
					LEFT JOIN LATERAL (

						SELECT 
							(SELECT
								string_agg(cast(dor2.DrugOstatRegistry_id as varchar(20)),',')
							FROM
								v_StorageZone sz2 

								left join v_StorageStructLevel ssl2  on ssl2.Storage_id = sz2.Storage_id

								left join v_Lpu lpu2  on lpu2.Lpu_id = ssl2.Lpu_id

								left join v_DrugOstatRegistry dor2  on COALESCE(ssl2.Org_id,lpu2.Org_id) = dor2.Org_id 


									and sz2.Storage_id = dor2.Storage_id
									and dsz.Drug_id = dor2.Drug_id
									and COALESCE(dsz.PrepSeries_id,0) = COALESCE(dor2.PrepSeries_id,0)

									and COALESCE(dsz.DrugShipment_id,0) = COALESCE(dor2.DrugShipment_id,0)

									and COALESCE(dsz.GoodsUnit_id, 
                                    :DefaultGoodsUnit_id
                                    ) = COALESCE(dor2.GoodsUnit_id, 
                                    :DefaultGoodsUnit_id
                                    )

							WHERE
								sz2.StorageZone_id = dsz.StorageZone_id
						) as DrugOstatRegistry_ids 
					) stors ON true
				where
					(1=1)
					and dsz.StorageZone_id = :StorageZone_id
					{$filter}
			";
			//echo getDebugSql($query, $data); exit();
			$result = $this->db->query($query, $data);

			if ( is_object($result) ) {
				$result = $result->result('array');
				return $result;
			}
			else {
				return false;
			}
		} else {
			return array();
		}
	}

	/**
	*  Загрузка списка всех дочерних мест хранения
	*/
	function getAllChildStorageZones($data) {
		// Создаем контейнер в котором будем хранить данные
		$box = array(
			'all'=>array($data['StorageZone_id']),
			'loaded'=>array()
		);
		do {
			// загружаем дочерние места - передаем контейнер
			$box = $this->getChildStorageZones($box);
			$all = $box['all'];
			$loaded = $box['loaded'];
			$diff = array_diff($all,$loaded);
			// существует разница - значит остались незагруженные => повоторяем цикл
		} while(count($diff)>0);

		return $all;
	}

	/**
	*  Загрузка списка дочерних мест хранения
	*/
	function getChildStorageZones($box) {
		$all = $box['all'];
		$loaded = $box['loaded'];
		// находим разницу между массивами
		$diff = array_diff($all,$loaded);
		// существует разница - значит остались незагруженные => загружаем первый попавшийся из незагруженных
		if(count($diff)>0){
			$query = "
				select
					StorageZone_id as \"StorageZone_id\"
				from
					v_StorageZone 

				where
					StorageZone_pid = :StorageZone_id
			";
			$load = array_shift($diff);
			$res = $this->queryResult($query,array('StorageZone_id'=>$load));
			if(is_array($res) && count($res)>0){
				foreach ($res as $value) {
					// есть дочерние ? => добавляем их в список Все
					array_push($all, $value['StorageZone_id']);
				}
			}
			// добавляем загруженное место в список Загруженные
			array_push($loaded, $load);
			// возвращаем контейнер
			return array('all'=>$all,'loaded'=>$loaded);
		} else {
			return $box;	
		}
	}

	/**
	*  Загрузка списка медикаментов (вкладка По медикаментам)
	*/
	function loadAllDrugGrid($data) {
		$this->load->model("DocumentUc_model", "DocumentUc_model");
		$filter = "";
		$params = array();
		$params['DefaultGoodsUnit_id'] = $this->DocumentUc_model->getDefaultGoodsUnitId();
		
		if (!empty($data['Drug_Name'])) {
			$filter .= " and dor.Drug_Name iLIKE ('%'||:Drug_Name||'%')";

			$params['Drug_Name'] = $data['Drug_Name'];
		}
		if (!empty($data['DrugComplexMnn_Name'])) {
			$filter .= " and dor.DrugComplexMnn_RusName iLIKE ('%'||:DrugComplexMnn_Name||'%')";

			$params['DrugComplexMnn_Name'] = $data['DrugComplexMnn_Name'];
		}
		if (!empty($data['DrugFinance_id'])) {
			$filter .= " and dor.DrugFinance_id = :DrugFinance_id";
			$params['DrugFinance_id'] = $data['DrugFinance_id'];
		}
		if (!empty($data['WhsDocumentCostItemType_id'])) {
			$filter .= " and dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
			$params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		}
		if (!empty($data['Org_id'])) {
			$filter .= " and COALESCE(ssl.Org_id, lpu.Org_id) = :Org_id";

			$params['Org_id'] = $data['Org_id'];
		}
		if (!empty($data['LpuBuilding_id'])) {
			$filter .= " and ssl.LpuBuilding_id = :LpuBuilding_id";
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}
		if (!empty($data['LpuSection_id'])) {
			$filter .= " and ssl.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}
		if (!empty($data['Storage_id'])) {
			$filter .= " and s.Storage_id = :Storage_id";
			$params['Storage_id'] = $data['Storage_id'];
		}
		$filter .= " and ostat.kolvo > 0";

		$query = "
			select
			-- select
				ds.DrugShipment_id as \"DrugShipment_id\",
				-- dors.DrugOstatRegistry_ids as DrugListKey,
				dors.DrugOstatRegistry_ids as \"DrugOstatRegistry_ids\",
				dor.Drug_id as \"Drug_id\",
				dor.Drug_Code as \"Drug_Code\",
				dor.Drug_Name as \"Drug_Name\",
				dor.PrepSeries_Ser as \"PrepSeries_Ser\",
				dor.PrepSeries_id as \"PrepSeries_id\",
				s.Storage_id as \"Storage_id\",
				dor.PrepSeries_GodnDate as \"PrepSeries_GodnDate\",
				ostat.kolvo as \"DrugCount\",
				gu.GoodsUnit_Name as \"GoodsUnit_Name\",
				rtrim(ltrim( COALESCE(dor.DrugOstatRegistry_Cost,'') || ' ' || COALESCE(dor.DrugFinance_Name,'') || ' ' || COALESCE(dor.WhsDocumentCostItemType_Name,'') || ' ' || COALESCE(wds.WhsDocumentUc_Num,'') || ' ' || COALESCE(ds.DrugShipment_Name,'')  || ' ' || COALESCE(to_char(ds.DrugShipment_setDT, 'DD.MM.YYYY'),''))) as \"DrugShipment_Row\"


			-- end select
			from
			-- from
				v_Storage s 

				inner join v_StorageStructLevel ssl  on ssl.Storage_id = s.Storage_id

				left join v_Lpu lpu  on lpu.Lpu_id = ssl.Lpu_id

				inner join v_DrugOstatRegistry door  on COALESCE(ssl.Org_id,lpu.Org_id) = door.Org_id and s.Storage_id = door.Storage_id


				left join v_DrugShipment ds  on ds.DrugShipment_id = door.DrugShipment_id

				left join v_WhsDocumentSupply wds  on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id

				left join v_GoodsUnit gu  on COALESCE(gu.GoodsUnit_id, 
                :DefaultGoodsUnit_id
                ) = COALESCE(door.GoodsUnit_id, 
                :DefaultGoodsUnit_id
                )


				LEFT JOIN LATERAL (

					SELECT 
						(SELECT
							string_agg(cast(dor2.DrugOstatRegistry_id as varchar(20)), ',')
						FROM
							v_DrugOstatRegistry dor2 

						WHERE
							COALESCE(ssl.Org_id,lpu.Org_id) = dor2.Org_id 

							and s.Storage_id = dor2.Storage_id
							and ds.DrugShipment_id = dor2.DrugShipment_id
					) as DrugOstatRegistry_ids 
				) dors ON true
				LEFT JOIN LATERAL (

					select
						dor3.Drug_id,
						d.Drug_Code,
						d.Drug_Name,
						dor3.PrepSeries_id,
						dcm.DrugComplexMnn_RusName,
						ps.PrepSeries_Ser,
						to_char(ps.PrepSeries_GodnDate, 'DD.MM.YYYY') as PrepSeries_GodnDate,

						cast(dor3.DrugOstatRegistry_Cost as varchar(20)) as DrugOstatRegistry_Cost,
						dor3.DrugFinance_id,
						df.DrugFinance_Name,
						dor3.WhsDocumentCostItemType_id,
						wcit.WhsDocumentCostItemType_Name
					from v_DrugOstatRegistry dor3 

					left join rls.v_Drug d  on d.Drug_id = dor3.Drug_id

					left join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id

					left join rls.v_PrepSeries ps  on ps.PrepSeries_id = dor3.PrepSeries_id

					left join v_DrugFinance df  on df.DrugFinance_id = dor3.DrugFinance_id

					left join v_WhsDocumentCostItemType wcit  on wcit.WhsDocumentCostItemType_id = dor3.WhsDocumentCostItemType_id

					where COALESCE(ssl.Org_id,lpu.Org_id) = dor3.Org_id 

						and s.Storage_id = dor3.Storage_id
						and ds.DrugShipment_id = dor3.DrugShipment_id
                    limit 1
				) dor ON true
				LEFT JOIN LATERAL (

					select 
						sum(dor4.DrugOstatRegistry_Kolvo) as kolvo
					from v_DrugOstatRegistry dor4 

					where COALESCE(ssl.Org_id,lpu.Org_id) = dor4.Org_id 

						and s.Storage_id = dor4.Storage_id
						and ds.DrugShipment_id = dor4.DrugShipment_id
				) ostat ON true
			-- end from
			where
			-- where
				(1=1)
				{$filter}
			-- end where
			order by
			-- order by
				dor.Drug_Name
			-- end order by
		";

		//echo getDebugSql($query, $data); exit();
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit'], 'distinct'), $params);
		$result_count = $this->db->query(getCountSQLPH($query,'ds.DrugShipment_id','distinct'), $params);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}

	}

	/**
	*  Загрузка списка мест хранения медикамента (вкладка По медикаментам)
	*/
	function loadAllDrugStorageGrid($data) {
		$filter = "";
		$filter2 = "";
		$without_sz = "";
		$params = array();
		$filter .= " and dor.DrugOstatRegistry_id in (".$data['DrugOstatRegistry_ids'].")";
		$filter2 .= " and dor2.DrugOstatRegistry_id in (".$data['DrugOstatRegistry_ids'].")";
		
		$query = "
			select
				cast((COALESCE(drug_reg.DrugReg_Count,0) - COALESCE(drug_sz.Drug_Count,0)) as double precision) as \"DrugCountWsz\",

				rtrim(ltrim(COALESCE(s.Storage_Name,'') ||' '||COALESCE(S.Storage_Code,''))) as \"Storage_Header\"

			from
				v_DrugOstatRegistry dor 

				inner join v_Storage s  on s.Storage_id = dor.Storage_id

				LEFT JOIN LATERAL (

					select
						sum(dor2.DrugOstatRegistry_Kolvo) as DrugReg_Count
					from 
						v_DrugOstatRegistry dor2 

					where (1=1)
						{$filter2}
				) drug_reg ON true
				LEFT JOIN LATERAL (

					select
						sum(dsz2.DrugStorageZone_Count) as Drug_Count
					from 
						v_DrugStorageZone dsz2 

						inner join v_StorageZone sz2  on dsz2.StorageZone_id = sz2.StorageZone_id

					where 
						dsz2.Drug_id = dor.Drug_id
						and sz2.Storage_id = dor.Storage_id
						and COALESCE(dor.PrepSeries_id,0) = COALESCE(dsz2.PrepSeries_id,0)

						and COALESCE(dor.DrugShipment_id,0) = COALESCE(dsz2.DrugShipment_id,0)

				) drug_sz ON true
			where
				(1=1)
				{$filter}
		";

		//echo getDebugSql($query, $data); exit();
		$result = $this->queryResult($query, $params);
		if(is_array($result) && !empty($result[0]['DrugCountWsz']) && $result[0]['DrugCountWsz'] > 0 ){
			if($data['start'] > 0){
				$data['start'] -= 1;
			}
			$data['limit'] -= 1;
			$without_sz = array(
				'0' => array(
					'Key_field'=>'Без места хранения', 
					'Storage_Header'=>$result[0]['Storage_Header'],
					'StorageZone_Header'=>'Без места хранения',
					'DrugCount'=>$result[0]['DrugCountWsz'],
					'StorageZoneLiable_ObjectName'=>''
				)
			);
		}

		$filter = "";
		$filter .= " and dsz.Drug_id = :Drug_id";
		$params['Drug_id'] = $data['Drug_id'];

		$filter .= " and COALESCE(dsz.PrepSeries_id,0) = COALESCE(CAST(:PrepSeries_id as bigint),0)";

		$params['PrepSeries_id'] = $data['PrepSeries_id'];

		$filter .= " and COALESCE(dsz.DrugShipment_id,0) = COALESCE(CAST(:DrugShipment_id as bigint),0)";

		$params['DrugShipment_id'] = $data['DrugShipment_id'];

		$filter .= " and s.Storage_id = :Storage_id";
		$params['Storage_id'] = $data['Storage_id'];
		
		$query = "
			select
			--select
				rtrim(ltrim(COALESCE(sz.StorageZone_Address,'') ||' '||COALESCE(sut.StorageUnitType_Name,''))) as \"Key_field\",

				rtrim(ltrim(COALESCE(s.Storage_Name,'') ||' '||COALESCE(S.Storage_Code,''))) as \"Storage_Header\",

				rtrim(ltrim(COALESCE(sz.StorageZone_Address,'') ||' '||COALESCE(sut.StorageUnitType_Name,''))) as \"StorageZone_Header\",

				dsz.DrugStorageZone_Count as \"DrugCount\",
				szl_o.StorageZoneLiable_ObjectName as \"StorageZoneLiable_ObjectName\"
			--end select
			from
			-- from
				v_Storage s 

				inner join v_StorageZone sz  on sz.Storage_id = s.Storage_id

				left join v_StorageUnitType sut  on sut.StorageUnitType_id = sz.StorageUnitType_id

				inner join v_DrugStorageZone dsz  on dsz.StorageZone_id = sz.StorageZone_id 

				LEFT JOIN LATERAL (

					select
						szl.StorageZoneLiable_ObjectName
					from
						v_StorageZoneLiable szl  

					where 
						szl.StorageZone_id = sz.StorageZone_id
						and szl.StorageZoneLiable_begDate <= dbo.tzGetDate()
						and (szl.StorageZoneLiable_endDate is null or szl.StorageZoneLiable_endDate > dbo.tzGetDate())
				) szl_o ON true
			-- end from
			where
			-- where
				(1=1)
				{$filter}
			-- end where
			order by
			-- order by
				sz.StorageZone_Address
			-- end order by
		";

		//echo getDebugSql($query, $data); exit();
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$result = $result->result('array');
			if(!empty($without_sz)){
				$count += 1;
				if($data['start'] == 0){
					if(count($result)>0){
						$result = array_merge($without_sz,$result);
					} else {
						$result = $without_sz;
					}
				}
			}
			$response = array();
			$response['data'] = $result;
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}

	}

	/**
	*  Загрузка Журанала перемещений
	*/
	function loadStorageDrugMoveGrid($data) {
		$this->load->model("DocumentUc_model", "DocumentUc_model");
		$filter = "";
		$inner_filter = "1=1";
		$params = array();
		$params['DefaultGoodsUnit_id'] = $this->DocumentUc_model->getDefaultGoodsUnitId();

		if (!empty($data['begDate'])) {
			$filter .= " and sdm.StorageDrugMove_setDate >= cast(:begDate as date)";
			$params['begDate'] = $data['begDate'];
		}
		if (!empty($data['endDate'])) {
			$filter .= " and sdm.StorageDrugMove_setDate <= cast(:endDate as date)";
			$params['endDate'] = $data['endDate'];
		}
		if (!empty($data['Drug_Name'])) {
			$filter .= " and d.Drug_Name iLIKE ('%'||:Drug_Name||'%')";

			$params['Drug_Name'] = $data['Drug_Name'];
		}
		if (!empty($data['DrugComplexMnn_Name'])) {
			$filter .= " and dcm.DrugComplexMnn_RusName iLIKE ('%'||:DrugComplexMnn_Name||'%')";

			$params['DrugComplexMnn_Name'] = $data['DrugComplexMnn_Name'];
		}
		if (!empty($data['DrugFinance_id'])) {
			$filter .= " and wds.DrugFinance_id = :DrugFinance_id";
			$params['DrugFinance_id'] = $data['DrugFinance_id'];
		}
		if (!empty($data['WhsDocumentCostItemType_id'])) {
			$filter .= " and wds.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
			$params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		}
		if (!empty($data['Org_id'])) {
			if (!empty($data['LpuBuilding_id'])) {
				$inner_filter .= " and ssl.LpuBuilding_id = :LpuBuilding_id";
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
			}
			if (!empty($data['LpuSection_id'])) {
				$inner_filter .= " and ssl.LpuSection_id = :LpuSection_id";
				$params['LpuSection_id'] = $data['LpuSection_id'];
			}
			$inner_filter .= " and COALESCE(ssl.Org_id, L.Org_id) = :Org_id";

			$params['Org_id'] = $data['Org_id'];
			$filter .= " 
			and (osz.Storage_id in (
				select ssl.Storage_id
				from v_StorageStructLevel SSL 

				left join v_Lpu L  on L.Lpu_id = SSL.Lpu_id

				where ".$inner_filter."
			) or nsz.Storage_id in (
				select ssl.Storage_id
				from v_StorageStructLevel SSL 

				left join v_Lpu L  on L.Lpu_id = SSL.Lpu_id

				where ".$inner_filter."
			)) ";
		}
		
		if (!empty($data['Storage_id'])) {
			$filter .= " and (osz.Storage_id = :Storage_id or nsz.Storage_id = :Storage_id)";
			$params['Storage_id'] = $data['Storage_id'];
		}

		$query = "
			select
			--select
				sdm.StorageDrugMove_id as \"StorageDrugMove_id\",
				to_char(sdm.StorageDrugMove_setDate, 'DD.MM.YYYY') as \"StorageDrugMove_setDate\",

				d.Drug_Name as \"Drug_Name\",
				ps.PrepSeries_Ser as \"PrepSeries_Ser\",
				ds.DrugShipment_Name as \"DrugShipment_Name\",
				rtrim(ltrim( COALESCE(du.DocumentUc_Num,'') || ' ' || to_char(du.DocumentUc_setDate, 'DD.MM.YYYY') || ' ' || COALESCE(ddt.DrugDocumentType_Name,'') )) as \"DocumentUc\",


				to_char(ps.PrepSeries_GodnDate, 'DD.MM.YYYY') as \"PrepSeries_GodnDate\",

				sdm.StorageDrugMove_Count as \"DrugCount\",
				gu.GoodsUnit_Name as \"GoodsUnit_Name\",
				rtrim(COALESCE(df.DrugFinance_Name,'') || ' ' || COALESCE(wcit.WhsDocumentCostItemType_Name,'')) as \"DrugFinance_Name\",

				case 
					when sdm.StorageZone_oid is null then 'Без места' 
					else rtrim(ltrim(COALESCE(osz.StorageZone_Address,'')||' '||COALESCE(osut.StorageUnitType_Name,''))) 

				end as \"oStorageZone\",
				case 
					when sdm.StorageZone_nid is null then ''
					else rtrim(ltrim(COALESCE(nsz.StorageZone_Address,'')||' '||COALESCE(nsut.StorageUnitType_Name,''))) 

				end as \"nStorageZone\"
			--end select
			from
			-- from
				v_StorageDrugMove sdm 

				left join v_StorageZone osz  on osz.StorageZone_id = sdm.StorageZone_oid

				left join v_StorageUnitType osut  on osut.StorageUnitType_id = osz.StorageUnitType_id

				left join v_StorageZone nsz  on nsz.StorageZone_id = sdm.StorageZone_nid

				left join v_StorageUnitType nsut  on nsut.StorageUnitType_id = nsz.StorageUnitType_id

				left join v_DocumentUcStr dus  on dus.DocumentUcStr_id = sdm.DocumentUcStr_id

				left join v_DocumentUc du  on du.DocumentUc_id = dus.DocumentUc_id

				left join v_DrugDocumentType ddt  on ddt.DrugDocumentType_id = du.DrugDocumentType_id

				left join rls.v_Drug d  on d.Drug_id = sdm.Drug_id

				left join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id

				left join rls.v_PrepSeries ps  on ps.PrepSeries_id = sdm.PrepSeries_id

				left join v_DrugShipment ds  on ds.DrugShipment_id = sdm.DrugShipment_id

				left join v_WhsDocumentSupply wds  on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id

				left join v_DrugFinance df  on df.DrugFinance_id = wds.DrugFinance_id

				left join v_WhsDocumentCostItemType wcit  on wcit.WhsDocumentCostItemType_id = wds.WhsDocumentCostItemType_id

				left join v_GoodsUnit gu  on COALESCE(gu.GoodsUnit_id, 
                :DefaultGoodsUnit_id
                ) = COALESCE(sdm.GoodsUnit_id, 
                :DefaultGoodsUnit_id
                )


			-- end from
			where
			-- where
				(1=1)
				{$filter}
			-- end where
			order by
			-- order by
				sdm.StorageDrugMove_setDate
			-- end order by
		";

		//echo getDebugSql($query, $data); exit();
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}

	}

	/**
	*  Загрузка выписки из Журнала перемещений
	*/
	function loadStorageDrugMoveList($data) {
		$this->load->model("DocumentUc_model", "DocumentUc_model");
		$filter = "";
		$params = array();
		$params['DefaultGoodsUnit_id'] = $this->DocumentUc_model->getDefaultGoodsUnitId();

		if(empty($data['Drug_id']) || empty($data['StorageZone_id'])){
			$response = array();
			$response['data'] = array();
			$response['totalCount'] = 0;
			return $response;
		} else {
			$params['Drug_id'] = $data['Drug_id'];
			$params['StorageZone_id'] = $data['StorageZone_id'];
		}

		$query = "
			select
			--select
				sdm.StorageDrugMove_id as \"StorageDrugMove_id\",
				to_char(sdm.StorageDrugMove_setDate, 'DD.MM.YYYY') as \"StorageDrugMove_setDate\",

				sdm.StorageDrugMove_Count as \"StorageDrugMove_Count\",
				ds.DrugShipment_Name as \"DrugShipment_Name\",
				rtrim(ltrim( COALESCE(du.DocumentUc_Num,'') || ' ' || to_char(du.DocumentUc_setDate, 'DD.MM.YYYY') || ' ' || COALESCE(ddt.DrugDocumentType_Name,'') )) as \"DocumentUc\",


				gu.GoodsUnit_Name as \"GoodsUnit_Name\",
				case 
					when sdm.StorageZone_oid is null then 'Без места' 
					else COALESCE(osz.StorageZone_Address,'') 

				end as \"oStorageZone\",
				case 
					when sdm.StorageZone_nid is null then ''
					else COALESCE(nsz.StorageZone_Address,'') 

				end as \"nStorageZone\"
			--end select
			from
			-- from
				v_StorageDrugMove sdm 

				left join v_StorageZone osz  on osz.StorageZone_id = sdm.StorageZone_oid

				left join v_StorageZone nsz  on nsz.StorageZone_id = sdm.StorageZone_nid

				left join v_DrugShipment ds  on ds.DrugShipment_id = sdm.DrugShipment_id

				left join v_DocumentUcStr dus  on dus.DocumentUcStr_id = sdm.DocumentUcStr_id

				left join v_DocumentUc du  on du.DocumentUc_id = dus.DocumentUc_id

				left join v_DrugDocumentType ddt  on ddt.DrugDocumentType_id = du.DrugDocumentType_id

				left join v_GoodsUnit gu  on COALESCE(gu.GoodsUnit_id, 
                :DefaultGoodsUnit_id
                ) = COALESCE(sdm.GoodsUnit_id, 
                :DefaultGoodsUnit_id
                )


			-- end from
			where
			-- where
				(1=1)
				and sdm.Drug_id = :Drug_id
				and (sdm.StorageZone_oid = :StorageZone_id or sdm.StorageZone_nid = :StorageZone_id)
			-- end where
			order by
			-- order by
				sdm.StorageDrugMove_setDate
			-- end order by
		";

		//echo getDebugSql($query, $data); exit();
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}

	}

	/**
	*  Загрузка списка контрактов по складу
	*/
	function loadStorageDocSupplyList($data) {
		if(empty($data['Storage_id'])){
			return array();
		}

		$query = "
			select
				sdsl.StorageDocSupplyLink_id as \"StorageDocSupplyLink_id\",
				sdsl.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
				wds.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				to_char(wds.WhsDocumentUc_Date, 'DD.MM.YYYY') as \"WhsDocumentUc_Date\",

				date_part('YEAR',wds.WhsDocumentSupply_ExecDate) as \"WhsDocumentUc_Year\",
				wds.WhsDocumentUc_Sum as \"WhsDocumentUc_Sum\",
				org.Org_Nick as \"Org_Nick\"
			from
				v_StorageDocSupplyLink sdsl 

				left join v_WhsDocumentSupply wds  on wds.WhsDocumentSupply_id = sdsl.WhsDocumentSupply_id

				left join v_Org org  on org.Org_id = wds.Org_sid

			where
				(1=1)
				and sdsl.Storage_id = :Storage_id
		";
		//echo getDebugSql($query, $data); exit();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$result = $result->result('array');
			return $result;
		}
		else {
			return false;
		}
	}

	/**
	*  Поиск записи связи контракта и склада
	*/
	function findStorageDocSupplyLink($data) {
		if(empty($data['WhsDocumentSupply_id'])){
			return array();
		}
		$where = "";
		if(!empty($data['Storage_id'])){
			$where .= " and sdsl.Storage_id <> :Storage_id";
		}

		$query = "
			select
				sdsl.StorageDocSupplyLink_id as \"StorageDocSupplyLink_id\",
				wds.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				to_char(wds.WhsDocumentUc_Date, 'DD.MM.YYYY') as \"WhsDocumentUc_Date\",

				s.Storage_Name as \"Storage_Name\",
				sdsl.Storage_id as \"Storage_id\"
			from
				v_StorageDocSupplyLink sdsl 

				left join v_WhsDocumentSupply wds  on wds.WhsDocumentSupply_id = sdsl.WhsDocumentSupply_id

				left join v_Storage s  on s.Storage_id = sdsl.Storage_id

			where
				(1=1)
				and sdsl.WhsDocumentSupply_id = :WhsDocumentSupply_id and sdsl.Storage_id is not null
			limit 1 {$where}
		";
		//echo getDebugSql($query, $data); exit();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$result = $result->result('array');
			return $result;
		}
		else {
			return false;
		}
	}

	/**
	*  Сохранение записи связи склада и контракта
	*/
	function saveStorageDocSupplyLink($data) {
		if (!empty($data['StorageDocSupplyLink_id'])) {
			$procedure = "upd";
		} else {
			$procedure = "ins";
		}
		$query = "
			select StorageDocSupplyLink_id as \"StorageDocSupplyLink_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from dbo.p_StorageDocSupplyLink_".$procedure."(
				StorageDocSupplyLink_id := :StorageDocSupplyLink_id,
				Storage_id := :Storage_id,
				WhsDocumentSupply_id := :WhsDocumentSupply_id,
				pmUser_id := :pmUser_id);
		";

		//echo getDebugSql($query, $data); exit();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	*  Удаление записи связи склада и контракта
	*/
	function deleteStorageDocSupplyLink($data) {
		$query = "
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from dbo.p_StorageDocSupplyLink_del(
				StorageDocSupplyLink_id := :StorageDocSupplyLink_id);
		";

		//echo getDebugSql($query, $data); exit();
		$result = $this->db->query($query, array('StorageDocSupplyLink_id'=>$data['StorageDocSupplyLink_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Возвращает параметры процедуры
	 */
	function getParamsByProcedure($data) {
		$filter = "1=1";
		$filter .= "    lower(p.proname) = lower(:proc)
                        AND lower(n.nspname) = lower(:scheme)";
		$query = "
            select 
				field.name AS \"name\",
                t.typname AS \"type\",
				CASE WHEN field.mode_id = 'b' THEN 1 ELSE 0 END AS \"is_output\"
			from (
				select unnest(p.proargnames) as name,
                	   unnest(p.proargtypes) as type_id,
                       unnest(p.proargmodes) as mode_id
				from pg_catalog.pg_proc p
				inner join sys.types t on t.system_type_id = ps.system_type_id and t.user_type_id = ps.user_type_id
				where
				{$filter}				 
			) field
            	LEFT JOIN pg_type t ON t.oid = field.type_id
		";
		//echo getDebugSql($query, $data); die();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Получение данных записи таблицы по идентификатору
	 */
	private function getRecordById($id) {
		if( !isset($id) || empty($id) || $id < 0 ) {
			return null;
		}
		
		$query = "
                    SELECT c.relname AS \"name\"
                    FROM pg_class c
                         INNER JOIN pg_namespace n ON c.relnamespace = n.oid
                    WHERE c.relkind IN ('v', 'm')
                    AND c.relname  iLIKE 'v_{$this->objectName}'
                    ORDER BY n.nspname,
                             c.relname
		";
		$result = $this->db->query($query);
		if ( !is_object($result) ) {
			return false;
		}
		$result = $result->result('array');
		if( count($result) > 0 ) {
			$from = "dbo.v_{$this->objectName}";
		} else {
			$from = "dbo.{$this->objectName}";
		}
		
		$query = "
			select 
				*
			from
				{$from}
			where
				{$this->objectName}_id = {$id}
		    limit 1
		";
		$result = $this->db->query($query);
		if ( is_object($result) ) {
			$result = $result->result('array');
			return isset($result[0]) ? $result[0] : null;
		} else {
			return false;
		}
	}
	
	/**
	 *	Устанавливает значение объекта БД и значение ключа строки с которой работаем в дальнейшем
	 */
	function setObject($objectName) {
		$this->objectName = $objectName;
		return $this;
	}
	
	/**
	 *	Устанавливает значение ключа строки с которой работаем в дальнейшем
	 */
	function setRow($objectKey) {
		$this->objectKey = $objectKey;
		return $this;
	}
	
	/**
	 *	Устанавливает значение поля объекта БД
	 */
	function setValue($field, $value) {
		if( empty($this->objectName) || empty($this->objectKey) )
			return false;
		
		$procedure = "p_" . $this->objectName . "_upd";
		$params = $this->getParamsByProcedure(array('scheme' => 'dbo', 'proc' => $procedure));
		//print_r($params);
	
		$query = "
		    select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			FROM dbo." . $procedure . "(\n";
		
		foreach($params as $k=>$param) {
		    if (!($param['is_output'])) {
                $query .= "\t\t\t\t" . $param['name'] . " = " . ":" . $param['name'];
                $query .= (count($params) == ++$k ? ")" : ",") . "\n";
            }
		}
		$query .= "\t\t\t";
		//var_dump($query);
		
		$record = $this->getRecordById($this->objectKey);
		if( !is_array($record) ) {
			return false;
		}
		
		$record[$this->objectName.'_id'] = $this->objectKey;
		$sp = getSessionParams();
		$record['pmUser_id'] = $sp['pmUser_id'];
		
		if( array_key_exists($field, $record) ) {
			$record[$field] = $value;
			$result = $this->db->query($query, $record);
			if ( is_object($result) ) {
				return $result->result('array');
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	*  Перемещение медикаментов в другое место хранения
	*/
	function moveDrugsToStorageZone($data) {
		$this->load->model("DocumentUc_model", "DocumentUc_model");

		$check = $this->checkStorageZoneInvent($data);
		if(!is_array($check)){
			return array(
				'success' => false,
				'Error_Msg' => 'Ошибка при проверке участия мест хранения в инвентаризации.'
			);
		}
		if(count($check)>0){
			return array(
				'success' => false,
				'Error_Msg' => 'Места хранения задействованы в инвентаризации.'
			);
		}
		try {
			$this->beginTransaction();
			$params = array();
			$params['DefaultGoodsUnit_id'] = $this->DocumentUc_model->getDefaultGoodsUnitId();

			// Массив для сбора данных по медикаментам для создания документов, если место хранения подотчетное
			$all_records = array();
			if(!empty($data['StorageZone_id'])){
				// Проверка - передано ли место хранения на подотчет
				$sz_liable = $this->getStorageZoneLiable($data);
				if(!is_array($sz_liable)){
					throw new Exception('Ошибка при проверке связи места хранения с подотчетным лицом.');
				}
			}
			if(empty($data['record_ids']) && !empty($data['drugostatreg_ids'])){ // перемещение из Без места хранения
				if(empty($data['StorageZone_id'])){
					throw new Exception('Ошибка - не передано конечное место хранения при перемещении из Без места хранения.');
				}
				$records = explode('|', $data['drugostatreg_ids']);
				foreach ($records as $value) {
					$subrecords = explode(':', $value);
					// Берем нужные данные из регистра остатков для создания новой записи
					$where = "";
					$where2 = "";
					if(!empty($subrecords[0])){
						$where .= " and dor.DrugOstatRegistry_id in (".$subrecords[0].")";
						$where2 .= " and dor2.DrugOstatRegistry_id in (".$subrecords[0].")";
					}

					$query = "
						select
							dor.DrugOstatRegistry_id as \"DrugOstatRegistry_id\",
							dor.Drug_id as \"Drug_id\",
							dor.PrepSeries_id as \"PrepSeries_id\",
							COALESCE(ps.PrepSeries_Ser,psdor.PrepSeries_Ser) as \"PrepSeries_Ser\",

							dor.DrugShipment_id as \"DrugShipment_id\",
							COALESCE(dus.DrugFinance_id,dor.DrugFinance_id) as \"DrugFinance_oid\",

							dor.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
							dor.DrugOstatRegistry_Cost as \"DrugOstatRegistry_Cost\",
							COALESCE(ostat.kolvo,0) as \"DrugOstatRegistry_Kolvo\",

							dsl.DocumentUcStr_id as \"DocumentUcStr_oid\",
							COALESCE(dor.GoodsUnit_id, :DefaultGoodsUnit_id) as \"GoodsUnit_id\"

						from
							v_DrugOstatRegistry dor 

							left join v_DrugShipmentLink dsl  on dsl.DrugShipment_id = dor.DrugShipment_id

							left join v_DocumentUcStr dus  on dus.DocumentUcStr_id = dsl.DocumentUcStr_id

							left join rls.v_PrepSeries psdor  on psdor.PrepSeries_id = dor.PrepSeries_id

							left join rls.v_PrepSeries ps  on ps.PrepSeries_id = dus.PrepSeries_id

							LEFT JOIN LATERAL ( 

								select sum(dor2.DrugOstatRegistry_Kolvo) as kolvo
								from v_DrugOstatRegistry dor2 

								where (1=1) {$where2}
							) ostat ON true
						where
							(1=1)
							{$where}
						limit 1
					";
					//echo getDebugSql($query,$params); exit();
					$result = $this->db->query($query, $params);

					if ( is_object($result) ) {
						$result = $result->result('array');
						if( !is_array($result) || empty($result[0]['DrugOstatRegistry_id'])) {
							throw new Exception('Ошибка при получении данных по медикаментам.');
						}
						$record = $result[0];
						$record['Drug_Kolvo'] = $subrecords[1];
						array_push($all_records, $record);
					} else {
						throw new Exception('Ошибка при получении данных по медикаментам.');
					}

					if($record['DrugOstatRegistry_Kolvo'] < $subrecords[1]){
						throw new Exception('Ошибка при перемещении медикаментов - количество перемещаемого медикамента больше того, что есть в наличии.');
					}

					$params = array(
						'StorageZone_id' => null,
						'Drug_id' => !empty($record['Drug_id']) ? $record['Drug_id'] : null,
						'PrepSeries_id' => !empty($record['PrepSeries_id']) ? $record['PrepSeries_id'] : null,
						'DrugShipment_id' => !empty($record['DrugShipment_id']) ? $record['DrugShipment_id'] : null,
						'DrugStorageZone_Count' => ($subrecords[1]),
						'DrugStorageZone_Price' => !empty($record['DrugOstatRegistry_Cost']) ? $record['DrugOstatRegistry_Cost'] : null,
						'GoodsUnit_id' => !empty($record['GoodsUnit_id']) ? $record['GoodsUnit_id'] : null,
						'pmUser_id' => $data['pmUser_id']
					);

					$resl = $this->makeRecordInStorageDrugMoveJournal($params, $data['StorageZone_id']);
					if(!is_array($resl)){
						throw new Exception('Ошибка при перемещении медикаментов');
					}
					if(!empty($resl[0]['Error_Msg'])){
						throw new Exception($resl[0]['Error_Msg']);
					}

					// Ищем запись с таким же медикаментоми ед.уч. в новом месте хранения
					$find_params = $params;
					$find_params['StorageZone_id'] = $data['StorageZone_id'];
					$rs = $this->findDrugStorageZoneByDrug($find_params);
					if(!is_array($rs)){
						throw new Exception('Ошибка при перемещении медикаментов');
					}
					if(count($rs)>0){
						// Выбираем запись с тем же медикаментом в новом месте хранения
						$this->setObject('DrugStorageZone')->setRow($rs[0]['DrugStorageZone_id']);
						// Обновляем количество медикамента в записи
						$newCount = floatval($rs[0]['DrugStorageZone_Count']) + floatval($subrecords[1]);
						$res = $this->setValue('DrugStorageZone_Count',$newCount);
						if(!is_array($res)){
							throw new Exception('Ошибка при перемещении медикаментов');
						}
						if(!empty($res[0]['Error_Msg'])){
							throw new Exception($res[0]['Error_Msg']);
						}
					} else {
						$params['DrugStorageZone_id'] = null;
						$params['StorageZone_id'] = $data['StorageZone_id'];
						
						$res = $this->saveDrugStorageZone($params);
						
						if(!is_array($res)){
							throw new Exception('Ошибка при перемещении медикаментов');
						}
						if(!empty($res[0]['Error_Msg'])){
							throw new Exception($res[0]['Error_Msg']);
						}
					}
				}
				// Если место хранения подотчетное то создаем документы учета о передаче на подотчет
				if(!empty($sz_liable[0]['StorageZoneLiable_ObjectId'])){
					$data['fromMoveDrugs'] = 1;
					$data['StorageZoneLiable_ObjectId'] = $sz_liable[0]['StorageZoneLiable_ObjectId'];
					$data['drugListForGive'] = $all_records;
					if(!empty($sz_liable[0]['StorageZoneLiable_ObjectName']) && $sz_liable[0]['StorageZoneLiable_ObjectName'] == 'Бригада СМП'){
						$data['DrugDocumentType_id'] = 29;
					} else {
						$data['DrugDocumentType_id'] = 27;
					}
					$docs = $this->giveStorageZoneToPerson($data, true);
					if(empty($docs['success']) || $docs['success'] !== true){
						$msg = 'Ошибка при перемещении медикаментов в подотчетное место хранения.';
						if(!empty($docs[0]['Error_Msg'])){
							$msg .= ' '.$docs[0]['Error_Msg'];
						}
						if(!empty($docs['Error_Msg'])){
							$msg .= ' '.$docs['Error_Msg'];
						}
						throw new Exception($msg);
					}
				}
			} else if(!empty($data['record_ids'])){ // перемещение из Мест хранения
				$records = explode('|', $data['record_ids']);
				foreach ($records as $value) {
					$subrecords = explode(':', $value);
					$this->setObject('DrugStorageZone')->setRow($subrecords[0]);

					$record = $this->getRecordById($this->objectKey);
					if( !is_array($record) || empty($record['StorageZone_id'])) {
						throw new Exception('Ошибка при перемещении медикаментов');
					}
					if (empty($record['GoodsUnit_id'])) {
						$record['GoodsUnit_id'] = $this->DocumentUc_model->getDefaultGoodsUnitId();
					}
					$record['pmUser_id'] = $data['pmUser_id'];

					if($record['StorageZone_id'] != $data['StorageZone_id']) {
						// Создадим запись по медикаменту для документов учета
						$liable_record = $record;
						if(!empty($record['DrugShipment_id'])){
							//Получение данных для документов в случае если место хранения подотчетное
							$query = "
								select 
									dsl.DocumentUcStr_id as \"DocumentUcStr_oid\",
									COALESCE(dus.DrugFinance_id,stor.DrugFinance_id) as \"DrugFinance_oid\",

									stor.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
									COALESCE(ps.PrepSeries_Ser,stor.PrepSeries_Ser) as \"PrepSeries_Ser\"

								from
									v_DrugShipmentLink dsl 

									left join v_DocumentUcStr dus  on dus.DocumentUcStr_id = dsl.DocumentUcStr_id

									left join rls.v_PrepSeries ps  on ps.PrepSeries_id = dus.PrepSeries_id

									LEFT JOIN LATERAL (

										select 
											dor.WhsDocumentCostItemType_id,
											dor.DrugFinance_id,
											psdor.PrepSeries_Ser
										from v_DrugOstatRegistry dor 

										left join rls.v_PrepSeries psdor  on psdor.PrepSeries_id = dor.PrepSeries_id

										where dor.Drug_id = :Drug_id
										and COALESCE(dor.DrugShipment_id,0) = :DrugShipment_id
                                        limit 1

									) stor ON true
								where
									dsl.DrugShipment_id = :DrugShipment_id
								limit 1
							";
							//echo getDebugSql($query,$params); exit();
							$result = $this->db->query($query, $liable_record);

							if ( is_object($result) ) {
								$result = $result->result('array');
								if( !is_array($result) || empty($result[0]['DocumentUcStr_oid'])) {
									throw new Exception('Ошибка при получении партии для медикамента.');
								}
								$liable_record['PrepSeries_Ser'] = $result[0]['PrepSeries_Ser'];
								$liable_record['DocumentUcStr_oid'] = $result[0]['DocumentUcStr_oid'];
								$liable_record['DrugFinance_oid'] = $result[0]['DrugFinance_oid'];
								$liable_record['WhsDocumentCostItemType_id'] = $result[0]['WhsDocumentCostItemType_id'];
								$liable_record['Drug_Kolvo'] = $subrecords[1];
								
							} else {
								throw new Exception('Ошибка при получении данных по медикаментам.');
							}
						}
						array_push($all_records, $liable_record);

						if($record['DrugStorageZone_Count'] < $subrecords[1]){
							throw new Exception('Ошибка при перемещении медикаментов - количество перемещаемого медикамента больше того, что есть в наличии.');
						}
						
						$params = $record;
						$params['DrugStorageZone_Count'] = $subrecords[1];

						// Создаем запись в журнале перемещений
						$resl = $this->makeRecordInStorageDrugMoveJournal($params,$data['StorageZone_id']);
						if(!is_array($resl)){
							throw new Exception('Ошибка при перемещении медикаментов');
						}
						if(!empty($resl[0]['Error_Msg'])){
							throw new Exception($resl[0]['Error_Msg']);
						}

						if($record['DrugStorageZone_Count'] == $subrecords[1]){
							if(empty($data['StorageZone_id'])){
								// Перемещаем в Без места хранения
								$res = $this->deleteDrugStorageZone(array('DrugStorageZone_id'=>$subrecords[0]));
							} else {
								// Ищем запись с таким же медикаментом в новом месте хранения
								$find_params = $record;
								$find_params['StorageZone_id'] = $data['StorageZone_id'];
								$rs = $this->findDrugStorageZoneByDrug($find_params);
								if(!is_array($rs)){
									throw new Exception('Ошибка при перемещении медикаментов');
								}
								if(count($rs)>0){
									// Удаляем запись из старого места хранения
									$res = $this->deleteDrugStorageZone(array('DrugStorageZone_id'=>$subrecords[0]));
									if(!is_array($res)){
										throw new Exception('Ошибка при перемещении медикаментов');
									}
									if(!empty($res[0]['Error_Msg'])){
										throw new Exception($res[0]['Error_Msg']);
									}
									// Выбираем запись с тем же медикаментом в новом месте хранения
									$this->setObject('DrugStorageZone')->setRow($rs[0]['DrugStorageZone_id']);
									// Обновляем количество медикамента в записи
									$newCount = floatval($rs[0]['DrugStorageZone_Count']) + floatval($subrecords[1]);
									$res = $this->setValue('DrugStorageZone_Count',$newCount);
									if(!is_array($res)){
										throw new Exception('Ошибка при перемещении медикаментов');
									}
									if(!empty($res[0]['Error_Msg'])){
										throw new Exception($res[0]['Error_Msg']);
									}
								} else {
									// Полностью перемещаем в другое место хранения
									$res = $this->setValue('StorageZone_id',$data['StorageZone_id']);
									if(!is_array($res)){
										throw new Exception('Ошибка при перемещении медикаментов');
									}
									if(!empty($res[0]['Error_Msg'])){
										throw new Exception($res[0]['Error_Msg']);
									}
								}
							}
							
							if(!is_array($res)){
								throw new Exception('Ошибка при перемещении медикаментов');
							}
							if(!empty($res[0]['Error_Msg'])){
								throw new Exception($res[0]['Error_Msg']);
							}
						} else {
							if(empty($data['StorageZone_id'])){
								// Если не указано новое место хранения, значит просто уменьшаем количество медикамента в старом месте хранения
								$newCount = floatval($record['DrugStorageZone_Count']) - floatval($subrecords[1]);
								$res = $this->setValue('DrugStorageZone_Count',$newCount);
								if(!is_array($res)){
									throw new Exception('Ошибка при перемещении медикаментов');
								}
								if(!empty($res[0]['Error_Msg'])){
									throw new Exception($res[0]['Error_Msg']);
								}
							} else {
								// Ищем запись с таким же медикаментом в новом месте хранения
								$find_params = $record;
								$find_params['StorageZone_id'] = $data['StorageZone_id'];
								$rs = $this->findDrugStorageZoneByDrug($find_params);
								if(!is_array($rs)){
									throw new Exception('Ошибка при перемещении медикаментов');
								}
								if(count($rs)>0){
									// В новом месте хранения есть такой медикамент

									// Обновляем количество медикамента в старой записи - в первую очередь чтобы потом не возвращаться к этому объекту
									$newCount = floatval($record['DrugStorageZone_Count']) - floatval($subrecords[1]);
									$res = $this->setValue('DrugStorageZone_Count',$newCount);
									if(!is_array($res)){
										throw new Exception('Ошибка при перемещении медикаментов');
									}
									if(!empty($res[0]['Error_Msg'])){
										throw new Exception($res[0]['Error_Msg']);
									}

									// Выбираем запись с тем же медикаментом в новом месте хранения
									$this->setObject('DrugStorageZone')->setRow($rs[0]['DrugStorageZone_id']);
									// Обновляем количество медикамента в записи
									$newCount = floatval($rs[0]['DrugStorageZone_Count']) + floatval($subrecords[1]);
									$res = $this->setValue('DrugStorageZone_Count',$newCount);
									if(!is_array($res)){
										throw new Exception('Ошибка при перемещении медикаментов');
									}
									if(!empty($res[0]['Error_Msg'])){
										throw new Exception($res[0]['Error_Msg']);
									}
								} else {
									// В новом месте хранения нет такого медикамента
									// Создаем запись в новом месте хранения
									$params = array(
										'DrugStorageZone_id' => null,
										'StorageZone_id' => $data['StorageZone_id'],
										'Drug_id' => !empty($record['Drug_id']) ? $record['Drug_id'] : null,
										'PrepSeries_id' => !empty($record['PrepSeries_id']) ? $record['PrepSeries_id'] : null,
										'DrugShipment_id' => !empty($record['DrugShipment_id']) ? $record['DrugShipment_id'] : null,
										'DrugStorageZone_Count' => ($subrecords[1]),
										'DrugStorageZone_Price' => !empty($record['DrugStorageZone_Price']) ? $record['DrugStorageZone_Price'] : null,
										'GoodsUnit_id' => $record['GoodsUnit_id'],
										'pmUser_id' => $data['pmUser_id']
									);
									$res = $this->saveDrugStorageZone($params);
									
									if(!is_array($res)){
										throw new Exception('Ошибка при перемещении медикаментов');
									}
									if(!empty($res[0]['Error_Msg'])){
										throw new Exception($res[0]['Error_Msg']);
									}

									// Обновляем количество медикамента в старой записи
									$newCount = floatval($record['DrugStorageZone_Count']) - floatval($subrecords[1]);
									$res = $this->setValue('DrugStorageZone_Count',$newCount);
									if(!is_array($res)){
										throw new Exception('Ошибка при перемещении медикаментов');
									}
									if(!empty($res[0]['Error_Msg'])){
										throw new Exception($res[0]['Error_Msg']);
									}
								}
							}
							
							if(!is_array($res)){
								throw new Exception('Ошибка при перемещении медикаментов');
							}
							if(!empty($res[0]['Error_Msg'])){
								throw new Exception($res[0]['Error_Msg']);
							}
						}
					}
				}

				// Если место хранения подотчетное то создаем документы учета о передаче на подотчет
				if(!empty($sz_liable[0]['StorageZoneLiable_ObjectId']) && count($all_records)>0){
					$data['fromMoveDrugs'] = 1;
					$data['StorageZoneLiable_ObjectId'] = $sz_liable[0]['StorageZoneLiable_ObjectId'];
					$data['drugListForGive'] = $all_records;
					if(!empty($sz_liable[0]['StorageZoneLiable_ObjectName']) && $sz_liable[0]['StorageZoneLiable_ObjectName'] == 'Бригада СМП'){
						$data['DrugDocumentType_id'] = 29;
					} else {
						$data['DrugDocumentType_id'] = 27;
					}
					$docs = $this->giveStorageZoneToPerson($data, true);
					if(empty($docs['success']) || $docs['success'] !== true){
						$msg = 'Ошибка при перемещении медикаментов в подотчетное место хранения.';
						if(!empty($docs[0]['Error_Msg'])){
							$msg .= ' '.$docs[0]['Error_Msg'];
						}
						if(!empty($docs['Error_Msg'])){
							$msg .= ' '.$docs['Error_Msg'];
						}
						throw new Exception($msg);
					}
				}
			} else {
				throw new Exception('Не передан список медикаментов для перемещения.');
			}

			$this->commitTransaction();
			return array('success' => true);
		} catch (Exception $e) {
			$this->rollbackTransaction();
			return array(
				'success' => false,
				'Error_Msg' => toUtf($e->getMessage())
			);
		}
	}

	/**
	*  Перемещение медикаментов в другое место хранения
	*/
	function makeRecordInStorageDrugMoveJournal($record, $to) {
		$params = array(
			'StorageZone_oid' => $record['StorageZone_id'],
			'StorageZone_nid' => $to,
			'Drug_id' => $record['Drug_id'],
			'PrepSeries_id' => $record['PrepSeries_id'],
			'DrugShipment_id' => $record['DrugShipment_id'],
			'StorageDrugMove_Count' => $record['DrugStorageZone_Count'],
			'StorageDrugMove_Price' => $record['DrugStorageZone_Price'],
			'GoodsUnit_id' => $record['GoodsUnit_id'],
			'pmUser_id' => $record['pmUser_id'],
			'StorageDrugMove_setDate' => date('Y-m-d')
		);
		$query = "
			select StorageDrugMove_id as \"StorageDrugMove_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from dbo.p_StorageDrugMove_ins(
				StorageDrugMove_id := null,
				StorageDrugMove_setDate := :StorageDrugMove_setDate,
				StorageZone_oid := :StorageZone_oid,
				StorageZone_nid := :StorageZone_nid,
				Drug_id := :Drug_id,
				PrepSeries_id := :PrepSeries_id,
				DrugShipment_id := :DrugShipment_id,
				StorageDrugMove_Count := :StorageDrugMove_Count,
				StorageDrugMove_Price := :StorageDrugMove_Price,
				GoodsUnit_id := :GoodsUnit_id,
				pmUser_id := :pmUser_id);
		";
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	*  Удаление медикаментов с места хранения
	*/
	function deleteDrugStorageZone($data) {
		$query = "
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from dbo.p_DrugStorageZone_del(
				DrugStorageZone_id := :DrugStorageZone_id);


		";

		//echo getDebugSql($query, $data); exit();
		$result = $this->db->query($query, array('DrugStorageZone_id'=>$data['DrugStorageZone_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	*  Сохранение медикаментов на месте хранения
	*/
	function saveDrugStorageZone($data) {
		if (!empty($data['DrugStorageZone_id'])) {
			$procedure = "upd";
		} else {
			$procedure = "ins";
		}
		
		$query = "
			select DrugStorageZone_id as \"DrugStorageZone_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from dbo.p_DrugStorageZone_".$procedure."(
				DrugStorageZone_id := :DrugStorageZone_id,
				StorageZone_id := :StorageZone_id,
				Drug_id := :Drug_id,
				PrepSeries_id := :PrepSeries_id,
				DrugShipment_id := :DrugShipment_id,
				DrugStorageZone_Price := :DrugStorageZone_Price,
				DrugStorageZone_Count := :DrugStorageZone_Count,
				GoodsUnit_id := :GoodsUnit_id,
				pmUser_id := :pmUser_id);


		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	*  Поиск места хранения с указанным медикаментом
	*/
	function findDrugStorageZoneByDrug($data) {
		if(empty($data['StorageZone_id'])){
			return array();
		}
		$where = "";
		if(!empty($data['StorageZone_id'])){
			$where .= " and dsz.StorageZone_id = :StorageZone_id";
		}
		if(!empty($data['Drug_id'])){
			$where .= " and dsz.Drug_id = :Drug_id";
		}
		if(!empty($data['PrepSeries_id'])){
			$where .= " and dsz.PrepSeries_id = :PrepSeries_id";
		}
		if(!empty($data['DrugShipment_id'])){
			$where .= " and dsz.DrugShipment_id = :DrugShipment_id";
		}
		if(!empty($data['GoodsUnit_id'])){
			$where .= " and dsz.GoodsUnit_id = :GoodsUnit_id";
		}

		$query = "
            select 
				dsz.DrugStorageZone_id as \"DrugStorageZone_id\",
				dsz.DrugStorageZone_Count as \"DrugStorageZone_Count\"
			from
				v_DrugStorageZone dsz 

			where
				(1=1)
				{$where}
			limit 1		";
		//echo getDebugSql($query, $data); exit();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$result = $result->result('array');
			return $result;
		}
		else {
			return false;
		}
	}

	/**
	*  Проверка участия мест хранения в инвентаризации
	*/
	function checkStorageZoneInvent($data) {
		if(empty($data['StorageZone_id']) && empty($data['record_ids'])){
			return array();
		}
		$sz_str = '';
		if(!empty($data['record_ids'])){
			$sz_arr = explode('|', $data['record_ids']);
			if(count($sz_arr)>0){
				foreach ($sz_arr as $key => $value) {
					$sz = explode(':', $value);
					if(!empty($sz[0])){
						$sz_str .= $sz[0];
						if($key < (count($sz_arr)-1)){
							$sz_str .= ',';
						}
					}
				}
			}
		}
		if(!empty($data['StorageZone_id'])){
			if(strlen($sz_str) > 0){
				$sz_str .= ',';
			}
			$sz_str .= $data['StorageZone_id'];
		}

		$query = "
			select 
				wdui.StorageZone_id as \"StorageZone_id\",
				wduid.StorageZone_id as \"StorageZone_id\"
			from
				v_WhsDocumentUcInvent wdui 

				left join v_WhsDocumentUcInventDrug wduid  on wdui.WhsDocumentUcInvent_id = wduid.WhsDocumentUcInvent_id

			where
				(wdui.StorageZone_id in ({$sz_str}) or wduid.StorageZone_id in ({$sz_str}))
				and 
				(
					(wdui.WhsDocumentStatusType_id != 5 and wdui.WhsDocumentStatusType_id != 2)
					or
					( 
						wdui.WhsDocumentStatusType_id = 2 -- Статус Действующий
						and 
						(
							(
								(COALESCE(wduid.WhsDocumentUcInventDrug_Kolvo,0) - COALESCE(wduid.WhsDocumentUcInventDrug_Kolvo,0)) > 0

								and not exists (
									select du.DocumentUc_id 
									from v_DocumentUc du  

									where 
										du.WhsDocumentUc_id = wdui.WhsDocumentUc_id 
										and du.DrugDocumentType_id = 12 -- тип документа Оприходование
										and du.DrugDocumentStatus_id = 2 -- статус Исполнен
								)
							)
							or
							(
								(COALESCE(wduid.WhsDocumentUcInventDrug_Kolvo,0) - COALESCE(wduid.WhsDocumentUcInventDrug_Kolvo,0)) < 0

								and not exists (
									select du.DocumentUc_id 
									from v_DocumentUc du  

									where 
										du.WhsDocumentUc_id = wdui.WhsDocumentUc_id 
										and du.DrugDocumentType_id = 2 -- тип документа Списание
										and du.DrugDocumentStatus_id = 2 -- статус Исполнен
								)
							)
						)	
					)
				)
			limit 1
		";
		//echo getDebugSql($query, $data); exit();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$result = $result->result('array');
			return $result;
		}
		else {
			return false;
		}
	}

	/**
	*  Передача на подотчет
	*  $externalTrans - признак исполнения документа из уже запущенной ранее транзакции
	*/
	function giveStorageZoneToPerson($data,$externalTrans = false) {

		try {
			$this->load->model("DocumentUc_model", "DocumentUc_model");

			if(
				empty($data['StorageZone_id']) 
				|| empty($data['StorageZoneLiable_ObjectId']) 
				|| empty($data['DrugDocumentType_id']) 
				|| (!empty($data['fromMoveDrugs']) && (empty($data['drugListForGive']) || !is_array($data['drugListForGive']) || count($data['drugListForGive']) == 0))
			) {
				return array(array('Error_Msg'=>'Не достаточно данных для выполнения операции по передаче на подотчет.'));
			}

			// Проверка - передано ли место хранения бригаде на подотчет
			$check = $this->getStorageZoneLiable($data);
			if(!is_array($check)){
				return array(array('Error_Msg'=>'Ошибка при проверке связи места хранения с подотчетным лицом.'));
			}
			if(count($check)>0 && empty($data['fromMoveDrugs'])){
				return array(array('Error_Msg'=>'Место хранения уже передано на подотчет.'));
			}
			
			if(empty($data['Contragent_id'])){
				$contragentList = $this->DocumentUc_model->loadContragentList($data);
				if(!empty($contragentList[0]['Contragent_id'])){
					$data['Contragent_id'] = $contragentList[0]['Contragent_id'];
				} else {
					return array(array('Error_Msg'=>'Не удалось получить идентификатор контрагента организации пользователя.'));
				}
			}

			$sz = $this->loadStorageZone(array('StorageZone_id'=>$data['StorageZone_id']));
			if(!is_array($sz)){
				throw new Exception('Ошибка при получении данных о месте хранения.');
			}

			if(empty($data['fromMoveDrugs'])){
				$drugs = $this->loadStorageZoneDrugGrid(array('StorageZone_id'=>$data['StorageZone_id']));
				if(!is_array($drugs)){
					throw new Exception('Ошибка при получении списка медикаментов для места хранения.');
				}
			} else {
				$drugs = $data['drugListForGive'];
			}

			if(!$externalTrans){
				//старт транзакции
	        	$this->beginTransaction();
			}
			
			$num = $this->DocumentUc_model->generateDocumentUcNum(
				array(
					'Contragent_id'=>$data['Contragent_id'],
					'DrugDocumentType_id'=>$data['DrugDocumentType_id'],
					'DrugDocumentType_Code'=>$data['DrugDocumentType_id']
				)
			);
			if(empty($num[0]['DocumentUc_Num'])){
				throw new Exception('Ошибка при получении номера документа учета.');
			}
			$docUcParams = array(
				'DocumentUc_setDate'=>date('Y-m-d'),
				'DocumentUc_didDate'=>date('Y-m-d'),
				'DocumentUc_Num'=>(!empty($num[0]['DocumentUc_Num'])?$num[0]['DocumentUc_Num']:null),
				'Org_id'=>(!empty($data['session']['org_id'])?$data['session']['org_id']:null),
				'Contragent_id'=>(!empty($data['Contragent_id'])?$data['Contragent_id']:null),
				'DrugFinance_id'=>(!empty($drugs[0]['DrugFinance_oid'])?$drugs[0]['DrugFinance_oid']:null),
				'WhsDocumentCostItemType_id'=>(!empty($drugs[0]['WhsDocumentCostItemType_id'])?$drugs[0]['WhsDocumentCostItemType_id']:null),
				'Contragent_sid'=>(!empty($data['Contragent_id'])?$data['Contragent_id']:null),
				'Storage_sid'=>(!empty($sz[0]['Storage_id'])?$sz[0]['Storage_id']:null),
				'Mol_sid'=>(!empty($sz[0]['Mol_id'])?$sz[0]['Mol_id']:null),
				'SubAccountType_sid'=>1, // Субсчет поставщика: Доступно
				'Contragent_tid'=>(!empty($data['Contragent_id'])?$data['Contragent_id']:null),
				'Storage_tid'=>(!empty($sz[0]['Storage_id'])?$sz[0]['Storage_id']:null),
				'Mol_tid'=>(!empty($sz[0]['Mol_id'])?$sz[0]['Mol_id']:null),
				'SubAccountType_tid'=>2, // Субсчет получателя: Резерв
				'pmUser_id'=>$data['pmUser_id']
			);

			$docUcParams['DrugDocumentType_id'] = $data['DrugDocumentType_id'];
			$docUcParams['DrugDocumentStatus_id'] = $this->DocumentUc_model->getObjectIdByCode('DrugDocumentStatus', 1); // Статус документа - 1 - Новый

			// В зависимости от типа документа записываем в документ учета подотчетное лицо
			if($data['DrugDocumentType_id'] == 29){ // Тип документа - 29 - Передача укладки
				$docUcParams['EmergencyTeam_id'] = $data['StorageZoneLiable_ObjectId'];
			} else {
				$docUcParams['Person_id'] = $data['StorageZoneLiable_ObjectId'];
			}
			
			// Сохраняем документ учета
			$document = $this->saveObject('DocumentUc', $docUcParams);

			if(empty($document['DocumentUc_id']) || !empty($document['Error_Msg'])) {
				throw new Exception("Не удалось сохранить документ учета.");
			}

			foreach ($drugs as $drug) {
				if(empty($drug['DrugOstatRegistry_Cost'])){
					$drug['DrugOstatRegistry_Cost'] = null;
				}
				if(empty($drug['Drug_Kolvo'])){
					continue;
					$drug['Drug_Kolvo'] = null;
				}
				if(empty($drug['DrugShipment_id'])){
					throw new Exception("У медикамента".(!empty($drug['Drug_Name'])?$drug['Drug_Name']:'')." не указана партия.");
				}
				// Сохранение строки документа учета
				$docUcStrParams = array(
					'DocumentUcStr_setDate' => date('Y-m-d'),
					'DocumentUc_id' => $document['DocumentUc_id'],
					'StorageZone_id' => $data['StorageZone_id'],
					'DocumentUcStr_oid' => !empty($drug['DocumentUcStr_oid']) ? $drug['DocumentUcStr_oid'] : null,
					'Drug_id' => !empty($drug['Drug_id']) ? $drug['Drug_id'] : null,
					'PrepSeries_id' => !empty($drug['PrepSeries_id']) ? $drug['PrepSeries_id'] : null,
					'PrepSeries_Ser' => !empty($drug['PrepSeries_Ser']) ? $drug['PrepSeries_Ser'] : null,
					'DocumentUcStr_Count' => !empty($drug['Drug_Kolvo']) ? $drug['Drug_Kolvo'] : null,
					'DocumentUcStr_Price' => !empty($drug['DrugOstatRegistry_Cost']) ? $drug['DrugOstatRegistry_Cost'] : null,
					'DocumentUcStr_PriceR' => !empty($drug['DrugOstatRegistry_Cost']) ? $drug['DrugOstatRegistry_Cost'] : null,
					'DocumentUcStr_Sum' => ($drug['Drug_Kolvo']*$drug['DrugOstatRegistry_Cost']),
					'GoodsUnit_bid' => !empty($drug['GoodsUnit_id']) ? $drug['GoodsUnit_id'] : null,
					'pmUser_id' => $data['pmUser_id']
				);
				$documentStr = $this->saveObject('DocumentUcStr', $docUcStrParams);
				
				if(empty($documentStr['DocumentUcStr_id']) || !empty($documentStr['Error_Msg'])) {
					throw new Exception("Ошибка при сохранении строки документа учета.");
				}
			}

			// Исполняем документ
			$document['DrugDocumentStatus_Code'] = 1; // Статус документа - 1 - Новый
			if($data['DrugDocumentType_id'] == 29){
				$document['DrugDocumentType_Code'] = 29;
			} else {
				$document['DrugDocumentType_Code'] = 27;
			}
			$document['pmUser_id'] = $data['pmUser_id'];
			$execDoc = $this->DocumentUc_model->executeDocumentUc($document, true);
			if(!empty($execDoc['Error_Msg'])) {
				throw new Exception("Ошибка при исполнении документа учета: ".$execDoc['Error_Msg']);
			}
			
			if(empty($data['fromMoveDrugs'])){
				// Создаем запись связи места хранения и подотчетного лица
				$liableParams = array(
					'StorageZone_id'=>$data['StorageZone_id'],
					'StorageZoneLiable_ObjectName'=>'',
					'StorageZoneLiable_ObjectId'=>$data['StorageZoneLiable_ObjectId'],
					'StorageZoneLiable_begDate'=>date('Y-m-d H:i:s'),
					'pmUser_id'=>$data['pmUser_id']
				);
				if($data['DrugDocumentType_id'] == 29){
					$liableParams['StorageZoneLiable_ObjectName'] = 'Бригада СМП';
				}
				$storageZoneLiable = $this->saveObject('StorageZoneLiable', $liableParams);

				if(empty($storageZoneLiable['StorageZoneLiable_id']) || !empty($storageZoneLiable['Error_Msg'])) {
					throw new Exception("Ошибка при сохранении связи места хранения и подотчетного лица.");
				}
			}

			if(!$externalTrans){
				$this->commitTransaction();
			}
		} catch (Exception $e) {
			if(!$externalTrans){
				$this->rollbackTransaction();
			}
			return array('success'=>false,'Error_Msg'=>$e->getMessage());
		}
		return array('success'=>true);
	}

	/**
	*  Принятие с подотчета
	*/
	function takeStorageZoneFromPerson($data) {
		if(empty($data['StorageZone_id']) || empty($data['DrugDocumentType_id'])){
			return array(array('Error_Msg'=>'Не достаточно данных для выполнения операции по принятию с подотчета.'));
		}
		$this->load->model("DocumentUc_model", "DocumentUc_model");
		// Проверка наличия подотчетного лица для места хранения
		$szl = $this->getStorageZoneLiable($data);

		if(!is_array($szl)){
			return array(array('Error_Msg'=>'Ошибка при проверке связи места хранения с подотчетным лицом.'));
		}
		if(count($szl) == 0){
			return array(array('Error_Msg'=>'Медикаменты из указанного места хранения на подотчет не выданы, прием не возможен.'));
		}
		if(empty($szl[0]['StorageZoneLiable_ObjectId'])){
			return array(array('Error_Msg'=>'Не найден идентификатор подотчетного лица.'));
		}

		$data['StorageZoneLiable_ObjectId'] = $szl[0]['StorageZoneLiable_ObjectId'];

		// Првоерка ниличия медикаментов в укладке
		$drugsExist = $this->loadStorageZoneDrugGrid(array('StorageZone_id'=>$data['StorageZone_id']));
		if(!is_array($drugsExist)){
			throw new Exception('Ошибка при получении списка медикаментов для места хранения.');
		}

		// Поиск документа учета со статусом Подотчет действующий
		$params = $data;
		if($params['DrugDocumentType_id'] == 30){
			$params['DrugDocumentType_id'] = 29;
		}
		$du = $this->findDocumentUcWithStorageZoneLiable($params);
		if(!is_array($du)){
			return array(array('Error_Msg'=>'Ошибка при поиске документа учета, связанного с подотчетным лицом.'));
		}
		if(empty($du[0]['DocumentUc_id']) && count($drugsExist) > 0){
			return array(array('Error_Msg'=>'Не найдены документы о передаче на подотчет.'));
		} else if(empty($du[0]['DocumentUc_id']) && count($drugsExist) == 0){
			// На месте хранения нет медикаментов, документов нет - просто снимаем место с подотчета
			$skipDocs = true;
			// Обновляем запись о связи места хранения и подотчетного лица
			$this->setObject('StorageZoneLiable')->setRow($szl[0]['StorageZoneLiable_id']);
			$res = $this->setValue('StorageZoneLiable_endDate',date('Y-m-d H:i:s'));
			if(!is_array($res) || !empty($res['Error_Msg'])){
				return array(array('Error_Msg'=>'Ошибка при обновлении записи связи места хранения и подотчетного лица.'));
			}
		} else {
			$skipDocs = false;
		}

		if(!$skipDocs){
			// Сперва проверим резерв под все документы
			foreach ($du as $doc) {
				if(!$this->DocumentUc_model->haveReserve(array('DocumentUc_id' => $doc['DocumentUc_id']))){
					return array(array('Error_Msg'=>'Не найден резерв под документы о передаче на подотчет. Операция возврата из подотчета не может быть выполнена.'));
				}
			}

			try {
				//старт транзакции
	    		$this->beginTransaction();

	    		$sz = $this->loadStorageZone(array('StorageZone_id'=>$data['StorageZone_id']));
				if(!is_array($sz)){
					throw new Exception('Ошибка при получении данных о месте хранения.');
				}

				$num = $this->DocumentUc_model->generateDocumentUcNum(
					array(
						'Contragent_id'=>$data['Contragent_id'],
						'DrugDocumentType_id'=>$data['DrugDocumentType_id'],
						'DrugDocumentType_Code'=>$data['DrugDocumentType_id']
					)
				);
				if(empty($num[0]['DocumentUc_Num'])){
					throw new Exception('Ошибка при получении номера документа учета.');
				}
				$docUcParams = array(
					'DocumentUc_setDate'=>date('Y-m-d'),
					'DocumentUc_didDate'=>date('Y-m-d'),
					'DocumentUc_Num'=>(!empty($num[0]['DocumentUc_Num'])?$num[0]['DocumentUc_Num']:null),
					'Org_id'=>(!empty($data['session']['org_id'])?$data['session']['org_id']:null),
					'Contragent_id'=>(!empty($data['Contragent_id'])?$data['Contragent_id']:null),
					'DrugFinance_id'=>(!empty($du[0]['DrugFinance_id'])?$du[0]['DrugFinance_id']:null),
					'WhsDocumentCostItemType_id'=>(!empty($du[0]['WhsDocumentCostItemType_id'])?$du[0]['WhsDocumentCostItemType_id']:null),
					'Contragent_sid'=>(!empty($data['Contragent_id'])?$data['Contragent_id']:null),
					'Storage_sid'=>(!empty($sz[0]['Storage_id'])?$sz[0]['Storage_id']:null),
					'Mol_sid'=>(!empty($sz[0]['Mol_id'])?$sz[0]['Mol_id']:null),
					'SubAccountType_sid'=>2, // Субсчет поставщика: Резерв
					'Contragent_tid'=>(!empty($data['Contragent_id'])?$data['Contragent_id']:null),
					'Storage_tid'=>(!empty($sz[0]['Storage_id'])?$sz[0]['Storage_id']:null),
					'Mol_tid'=>(!empty($sz[0]['Mol_id'])?$sz[0]['Mol_id']:null),
					'SubAccountType_tid'=>1, // Субсчет получателя: Доступно
					'pmUser_id'=>$data['pmUser_id']
				);

				$docUcParams['DrugDocumentType_id'] = $data['DrugDocumentType_id'];
				$docUcParams['DrugDocumentStatus_id'] = $this->DocumentUc_model->getObjectIdByCode('DrugDocumentStatus', 1); // Статус документа - 1 - Новый

				// В зависимости от типа документа записываем в документ учета подотчетное лицо
				if($data['DrugDocumentType_id'] == 30){ // Тип документа - 30 - Возврат укладки
					$docUcParams['EmergencyTeam_id'] = $szl[0]['StorageZoneLiable_ObjectId'];
				} else {
					$docUcParams['Person_id'] = $szl[0]['StorageZoneLiable_ObjectId'];
				}
				
				// Сохраняем документ учета
				$document = $this->saveObject('DocumentUc', $docUcParams);

				if(empty($document['DocumentUc_id']) || !empty($document['Error_Msg'])) {
					throw new Exception("Не удалось сохранить документ учета.");
				}

				// Создаем строки нового документа учета
				foreach ($du as $doc) {
					$duStrs = $this->loadReservedDocumentUcStrList(array('DocumentUc_id' => $doc['DocumentUc_id']));
		    		if(!is_array($duStrs)){
						return array(array('Error_Msg'=>'Ошибка при получении списка строк документа учета.'));
					}
					foreach ($duStrs as $drug) {
						if(empty($drug['DrugOstatRegistry_Kolvo'])){
							continue;
						}
						if($this->DocumentUc_model->haveReserve(array('DocumentUcStr_id' => $drug['DocumentUcStr_id']))){
							// Сохранение строки документа учета
							$docUcStrParams = array(
								'DocumentUcStr_setDate' => date('Y-m-d'),
								'DocumentUc_id' => $document['DocumentUc_id'],
								'StorageZone_id' => $data['StorageZone_id'],
								'Drug_id' => !empty($drug['Drug_id']) ? $drug['Drug_id'] : null,
								'DocumentUcStr_oid' => !empty($drug['DocumentUcStr_oid']) ? $drug['DocumentUcStr_oid'] : null,
								'PrepSeries_id' => !empty($drug['PrepSeries_id']) ? $drug['PrepSeries_id'] : null,
								'DrugNds_id' => !empty($drug['DrugNds_id']) ? $drug['DrugNds_id'] : null,
								'DocumentUcStr_Price' => !empty($drug['DocumentUcStr_Price']) ? $drug['DocumentUcStr_Price'] : null,
								'DocumentUcStr_PriceR' => !empty($drug['DrugOstatRegistry_Cost']) ? $drug['DrugOstatRegistry_Cost'] : null,
								'DocumentUcStr_Count' => !empty($drug['DrugOstatRegistry_Kolvo']) ? $drug['DrugOstatRegistry_Kolvo'] : null,
								'DocumentUcStr_EdCount' => !empty($drug['DrugOstatRegistry_Kolvo']) ? $drug['DrugOstatRegistry_Kolvo'] : null,
								'DocumentUcStr_Sum' => !empty($drug['DocumentUcStr_Price']) ? ($drug['DrugOstatRegistry_Kolvo']*$drug['DocumentUcStr_Price']) : null,
								'DocumentUcStr_SumR' => !empty($drug['DrugOstatRegistry_Cost']) ? ($drug['DrugOstatRegistry_Kolvo']*$drug['DrugOstatRegistry_Cost']) : null,
								'DocumentUcStr_Ser' => !empty($drug['PrepSeries_Ser']) ? $drug['PrepSeries_Ser'] : null,
								'DocumentUcStr_CertNum' => !empty($drug['DocumentUcStr_CertNum']) ? $drug['DocumentUcStr_CertNum'] : null,
								'DocumentUcStr_CertDate' => !empty($drug['DocumentUcStr_CertDate']) ? $drug['DocumentUcStr_CertDate'] : null,
								'DocumentUcStr_CertGodnDate' => !empty($drug['DocumentUcStr_CertGodnDate']) ? $drug['DocumentUcStr_CertGodnDate'] : null,
								'DocumentUcStr_CertOrg' => !empty($drug['DocumentUcStr_CertOrg']) ? $drug['DocumentUcStr_CertOrg'] : null,
								'DocumentUcStr_IsLab' => !empty($drug['DocumentUcStr_IsLab']) ? $drug['DocumentUcStr_IsLab'] : null,
								'DrugLabResult_Name' => !empty($drug['DrugLabResult_Name']) ? $drug['DrugLabResult_Name'] : null,
								'DocumentUcStr_godnDate' => !empty($drug['DocumentUcStr_godnDate']) ? $drug['DocumentUcStr_godnDate'] : null,
								'DocumentUcStr_Decl' => !empty($drug['DocumentUcStr_Decl']) ? $drug['DocumentUcStr_Decl'] : null,
								'DocumentUcStr_Barcod' => !empty($drug['DocumentUcStr_Barcod']) ? $drug['DocumentUcStr_Barcod'] : null,
								'DocumentUcStr_CertNM' => !empty($drug['DocumentUcStr_CertNM']) ? $drug['DocumentUcStr_CertNM'] : null,
								'DocumentUcStr_CertDM' => !empty($drug['DocumentUcStr_CertDM']) ? $drug['DocumentUcStr_CertDM'] : null,
								'DocumentUcStr_IsNDS' => !empty($drug['DocumentUcStr_IsNDS']) ? $drug['DocumentUcStr_IsNDS'] : null,
								'GoodsUnit_bid' => !empty($drug['GoodsUnit_bid']) ? $drug['GoodsUnit_bid'] : null,
								'pmUser_id' => $data['pmUser_id']
							);
							$documentStr = $this->saveObject('DocumentUcStr', $docUcStrParams);
							if(empty($documentStr['DocumentUcStr_id']) || !empty($documentStr['Error_Msg'])) {
								throw new Exception("Ошибка при сохранении строки документа учета.");
							}
						}
					}
				}	

				// Исполняем документ
				$document['DrugDocumentStatus_Code'] = 1; // Статус документа - 1 - Новый
				if($data['DrugDocumentType_id'] == 30){
					$document['DrugDocumentType_Code'] = 30;
				} else {
					$document['DrugDocumentType_Code'] = 28;
				}
				$document['pmUser_id'] = $data['pmUser_id'];
				$document['oldDocumentUcs'] = $du;
				$execDoc = $this->DocumentUc_model->executeDocumentUc($document, true);
				if(!empty($execDoc['Error_Msg'])) {
					throw new Exception("Ошибка при исполнении документа учета: ".$execDoc['Error_Msg']);
				}

				foreach ($du as $doc) {
					$result = $this->saveObject('DocumentUc', array(
						'DocumentUc_id' =>$doc['DocumentUc_id'],
						'DrugDocumentStatus_id' => 15, // 15 - Подотчет прекращен
						'pmUser_id' => $data['pmUser_id']
					));
					if(!empty($result['Error_Msg'])){
						throw new Exception("Ошибка при смене статуса документа учета.");
					}
				}

	    		// Обновляем запись о связи места хранения и подотчетного лица
				$this->setObject('StorageZoneLiable')->setRow($szl[0]['StorageZoneLiable_id']);
				$res = $this->setValue('StorageZoneLiable_endDate',date('Y-m-d H:i:s'));
				if(!is_array($res) || !empty($res['Error_Msg'])){
					throw new Exception("Ошибка при обновлении записи связи места хранения и подотчетного лица.");
				}

	    		$this->commitTransaction();
			} catch (Exception $e) {
				$this->rollbackTransaction();
				return array('success'=>false,'Error_Msg'=>$e->getMessage());
			}
		}

		return array('success'=>true,'num'=>(!empty($szl[0]['EmergencyTeam_Num'])?$szl[0]['EmergencyTeam_Num']:''));
		
	}

	/**
	*  Получение списка бригад для передачи на подотчет
	*/
	function getBrigadesForGiveStorageZoneToPerson($data) {
		if(empty($data['StorageZone_id'])){
			return array(array('Error_Msg'=>'Не передан идентификатор места хранения'));
		}

		// Проверка - передано ли место хранения бригаде на подотчет
		$result = $this->getStorageZoneLiable($data);
		if(!is_array($result)){
			return array(array('Error_Msg'=>'Ошибка при проверке связи места хранения с подотчетным лицом.'));
		}

		if(count($result)>0){
			$errorMsg = 'Медикаменты уже выданы';
			if(!empty($result[0]['StorageZoneLiable_ObjectName'])){
				if($result[0]['StorageZoneLiable_ObjectName'] == 'Бригада СМП'){
					$errorMsg .= ' Бригада № '.(!empty($result[0]['EmergencyTeam_Num'])?$result[0]['EmergencyTeam_Num']:'');
				} else {
					$errorMsg .= ' '.(!empty($result[0]['Person_Fio'])?$result[0]['Person_Fio']:'').' '.(!empty($result[0]['Person_BirthDay'])?$result[0]['Person_BirthDay']:'');
				}
			}
			return array(array('Error_Msg'=>$errorMsg));
		} else {
			// Место хранения на подотчет бригаде не передано - грузим список бригад
			if(empty($data['Lpu_id'])){
				return array();
			}
			$where = "";
			if(!empty($data['LpuBuilding_id'])){
				$where .= " and et.LpuBuilding_id = :LpuBuilding_id";
			}
			$query = "
				select distinct
					et.EmergencyTeam_id as \"EmergencyTeam_id\",
					et.EmergencyTeam_Num as \"EmergencyTeam_Num\",
					ETS.EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\",
					COALESCE(ETSC.EmergencyTeamSpec_Name,'') as \"EmergencyTeamSpec_Name\",

					COALESCE(hs.Person_Fin,'') as \"EmergencyTeam_HeadShiftFIO\",

					etd.EmergencyTeamDuty_id as \"EmergencyTeamDuty_id\",
					(to_char(etd.EmergencyTeamDuty_DTStart, 'DD.MM.YYYY') || ' ' || to_char(etd.EmergencyTeamDuty_DTStart, 'HH24:MI:SS')) as \"EmergencyTeamDuty_DTStart\",


					(to_char(etd.EmergencyTeamDuty_DTFinish, 'DD.MM.YYYY') || ' ' || to_char(etd.EmergencyTeamDuty_DTFinish, 'HH24:MI:SS')) as \"EmergencyTeamDuty_DTFinish\"


				from
					v_EmergencyTeam et  

					left join v_StorageZoneLiable szl  on et.EmergencyTeam_id = szl.StorageZoneLiable_ObjectId 

						and szl.StorageZoneLiable_ObjectName = 'Бригада СМП' and szl.StorageZoneLiable_endDate is null
					LEFT JOIN v_EmergencyTeamStatus AS ETS  ON ETS.EmergencyTeamStatus_id = ET.EmergencyTeamStatus_id

					LEFT JOIN v_EmergencyTeamSpec AS ETSC  ON ETSC.EmergencyTeamSpec_id = ET.EmergencyTeamSpec_id

					LEFT JOIN v_MedPersonal AS hs  ON hs.MedPersonal_id = ET.EmergencyTeam_HeadShift
					inner join v_EmergencyTeamDuty etd  on et.EmergencyTeam_id = etd.EmergencyTeam_id

				where
					1 = 1 
					AND COALESCE(et.EmergencyTeam_isTemplate,1)=1 
					AND et.Lpu_id = :Lpu_id
					AND szl.StorageZoneLiable_ObjectId is null
					and etd.EmergencyTeamDuty_DTFinish >= dbo.tzGetDate()
					{$where}
				order by
					EmergencyTeamDuty_DTFinish,
					etd.EmergencyTeamDuty_id desc,
					ETS.EmergencyTeamStatus_id desc,
					et.EmergencyTeam_Num asc
			";
			//echo getDebugSql($query, $data); exit();
			$result = $this->db->query($query, $data);
			if ( is_object($result) ) {
				$result = $result->result('array');
				return $result;
			} else {
				return false;
			}
		}
		
	}


	/**
	*  Получение данных по отчетному лицу
	*/
	function getStorageZoneLiable($data) {
		if(empty($data['StorageZone_id'])){
			return false;
		}
		$query = "
			select
				szl.StorageZoneLiable_id as \"StorageZoneLiable_id\",
				szl.StorageZoneLiable_ObjectId as \"StorageZoneLiable_ObjectId\",
				szl.StorageZoneLiable_ObjectName as \"StorageZoneLiable_ObjectName\",
				p.Person_Fio as \"Person_Fio\",
				to_char(p.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\",

				et.EmergencyTeam_Num as \"EmergencyTeam_Num\"
			from
				v_StorageZoneLiable szl 

				left join v_EmergencyTeam et  on et.EmergencyTeam_id = szl.StorageZoneLiable_ObjectId

				left join v_Person_all p  on p.Person_id = szl.StorageZoneLiable_ObjectId

			where
				szl.StorageZone_id = :StorageZone_id
				and szl.StorageZoneLiable_endDate is null
            limit 1
		";
		//echo getDebugSql($query, $data); exit();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$result = $result->result('array');
			return $result;
		} else {
			return false;
		}
	}

	/**
	*  Поиск документа о передаче на подотчет
	*/
	function findDocumentUcWithStorageZoneLiable($data) {
		if(empty($data['StorageZone_id'])){
			return false;
		}
		$where = "";
		if(!empty($data['DrugDocumentType_id']) && !empty($data['StorageZoneLiable_ObjectId'])){
			if($data['DrugDocumentType_id'] == 29){
				$where .= " and du.EmergencyTeam_id = :StorageZoneLiable_ObjectId";
			}
		}
		$query = "
			select distinct
				du.DocumentUc_id as \"DocumentUc_id\",
				du.DrugFinance_id as \"DrugFinance_id\",
				du.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\"
			from
				v_DocumentUc du 

				inner join v_DocumentUcStr dus  on dus.DocumentUc_id = du.DocumentUc_id

			where
				dus.StorageZone_id = :StorageZone_id 
				and du.DrugDocumentStatus_id = 14 -- статус Подотчет действующий
				{$where}
		";
		//echo getDebugSql($query, $data); exit();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$result = $result->result('array');
			return $result;
		} else {
			return false;
		}
	}

	/**
	*  Загрузка списка строк документа учета
	*/
	function loadReservedDocumentUcStrList($data) {
		$this->load->model("DocumentUc_model", "DocumentUc_model");

		if(empty($data['DocumentUc_id'])){
			return false;
		}
		
		//получение данных по строкам документа учета
		$query = "
			select
				dus.DocumentUcStr_id as \"DocumentUcStr_id\",
				dus.DocumentUcStr_Count as \"DocumentUcStr_Count\",
				dus.DocumentUcStr_oid as \"DocumentUcStr_oid\",
				dor.DrugOstatRegistry_id as \"DrugOstatRegistry_id\",
				dor.DrugOstatRegistry_Kolvo as \"DrugOstatRegistry_Kolvo\",
				dor.Contragent_id as \"Contragent_id\",
				dor.Org_id as \"Org_id\",
				dor.Storage_id as \"Storage_id\",
				dor.DrugShipment_id as \"DrugShipment_id\",
				dor.Drug_id as \"Drug_id\",
				dor.PrepSeries_id as \"PrepSeries_id\",
				dor.SubAccountType_id as \"SubAccountType_id\",
				dor.Okei_id as \"Okei_id\",
				dor.DrugOstatRegistry_Cost as \"DrugOstatRegistry_Cost\",
				COALESCE(dor.GoodsUnit_id, :DefaultGoodsUnit_id) as \"GoodsUnit_bid\",

				d.Drug_Name as \"Drug_Name\",
				ps.PrepSeries_Ser as \"PrepSeries_Ser\",
				to_char(ps.PrepSeries_GodnDate, 'DD.MM.YYYY') as \"PrepSeries_GodnDate\",

				ds.DrugShipment_Name as \"DrugShipment_Name\",
				dus.StorageZone_id as \"StorageZone_id\",
				dusoid.DrugNds_id as \"DrugNds_id\",
				dusoid.DocumentUcStr_Price as \"DocumentUcStr_Price\",
				dusoid.DocumentUcStr_CertNum as \"DocumentUcStr_CertNum\",
				dusoid.DocumentUcStr_CertDate as \"DocumentUcStr_CertDate\",
				dusoid.DocumentUcStr_CertGodnDate as \"DocumentUcStr_CertGodnDate\",
				dusoid.DocumentUcStr_CertOrg as \"DocumentUcStr_CertOrg\",
				dusoid.DocumentUcStr_IsLab as \"DocumentUcStr_IsLab\",
				dusoid.DrugLabResult_Name as \"DrugLabResult_Name\",
				dusoid.DocumentUcStr_godnDate as \"DocumentUcStr_godnDate\",
				dusoid.DocumentUcStr_Decl as \"DocumentUcStr_Decl\",
				dusoid.DocumentUcStr_Barcod as \"DocumentUcStr_Barcod\",
				dusoid.DocumentUcStr_CertNM as \"DocumentUcStr_CertNM\",
				dusoid.DocumentUcStr_CertDM as \"DocumentUcStr_CertDM\",
				dusoid.DocumentUcStr_IsNDS as \"DocumentUcStr_IsNDS\"
			from
				v_DocumentUcStr dus 

				left join v_DrugShipmentLink dsl  on dsl.DocumentUcStr_id = dus.DocumentUcStr_oid

				left join rls.v_Drug d  on d.Drug_id = dus.Drug_id

				left join rls.v_PrepSeries ps  on ps.PrepSeries_id = dus.PrepSeries_id

				left join v_DrugShipment ds  on ds.DrugShipment_id = dsl.DrugShipment_id

				LEFT JOIN LATERAL (

					SELECT
						i_dor.DrugOstatRegistry_id,
						dorl.DrugOstatRegistryLink_Count as DrugOstatRegistry_Kolvo,
						i_dor.Contragent_id,
						i_dor.Org_id,
						i_dor.Storage_id,
						i_dor.DrugShipment_id,
						i_dor.Drug_id,
						i_dor.PrepSeries_id,
						i_dor.SubAccountType_id,
						i_dor.Okei_id,
						i_dor.DrugOstatRegistry_Cost
					from
						v_DrugOstatRegistry i_dor 

						inner join v_DrugOstatRegistryLink dorl  on dorl.DrugOstatRegistry_id = i_dor.DrugOstatRegistry_id

					where
						i_dor.Drug_id = dus.Drug_id
						and COALESCE(i_dor.GoodsUnit_id, :DefaultGoodsUnit_id) = COALESCE(dus.GoodsUnit_bid, :DefaultGoodsUnit_id)

						and i_dor.DrugShipment_id = dsl.DrugShipment_id
						and i_dor.Org_id IS NOT NULL
						and i_dor.SubAccountType_id = 2
						and dorl.DrugOstatRegistryLink_TableName = 'DocumentUcStr'
						and dorl.DrugOstatRegistryLink_TableID = dus.DocumentUcStr_id
					order by
						i_dor.DrugOstatRegistry_id
                    limit 1
				) dor ON true
				LEFT JOIN LATERAL (

					select 
						dus_oid.DrugNds_id
						,dus_oid.DocumentUcStr_Price
						,dus_oid.DocumentUcStr_CertNum
						,to_char(dus_oid.DocumentUcStr_CertDate, 'YYYY-MM-DD HH24:MI:SS') as DocumentUcStr_CertDate

						,to_char(dus_oid.DocumentUcStr_CertGodnDate, 'YYYY-MM-DD HH24:MI:SS') as DocumentUcStr_CertGodnDate

						,dus_oid.DocumentUcStr_CertOrg
						,dus_oid.DocumentUcStr_IsLab
						,dus_oid.DrugLabResult_Name
						,to_char(dus_oid.DocumentUcStr_godnDate, 'YYYY-MM-DD HH24:MI:SS') as DocumentUcStr_godnDate

						,dus_oid.DocumentUcStr_Decl
						,dus_oid.DocumentUcStr_Barcod
						,dus_oid.DocumentUcStr_CertNM
						,dus_oid.DocumentUcStr_CertDM
						,dus_oid.DocumentUcStr_IsNDS
					from
						v_DocumentUcStr dus_oid 

					where
						dus_oid.DocumentUcStr_id = dus.DocumentUcStr_oid
                    limit 1
				) dusoid ON true
			where
				dus.DocumentUc_id = :DocumentUc_id
		";
		//var_dump(getDebugSQL($query, array('DocumentUc_id' => $data['DocumentUc_id']))); exit;

		$result = $this->db->query($query, array(
			'DocumentUc_id' => $data['DocumentUc_id'],
			'DefaultGoodsUnit_id' => $this->DocumentUc_model->getDefaultGoodsUnitId()
		));
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
}