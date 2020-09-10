<?php defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH . 'models/_pgsql/WhsDocumentUcInvent_model.php');

class Ufa_WhsDocumentUcInvent_model extends WhsDocumentUcInvent_model
{
	/**
	 * construct
	 */
	function __construct()
	{
		//parent::__construct();
		parent::__construct();
	}

	/**
	 * получение списка медикаментов для инвентаризационной ведомости
	 */
	function getDocumentUcInventDrugList($data)
	{
		$item_arr = array();

		//подсчет количества ГК в приказе
		$query = "
				select
					count(WhsDocumentSupply_id) as \"cnt\"
				from
					v_WhsDocumentUcInvent wdui
					left join v_WhsDocumentSupplyInvent wdsi on wdsi.WhsDocumentUc_id = wdui.WhsDocumentUc_pid
				where
					WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id;
			";
		$data['supply_count'] = $this->getFirstResultFromQuery($query, $data);

		//получение списка остатков
		$query = "
				with supply_list as (
					select distinct
						WhsDocumentSupply_id
					from
						v_WhsDocumentUcInvent wdui
						left join v_WhsDocumentSupplyInvent wdsi on wdsi.WhsDocumentUc_id = wdui.WhsDocumentUc_pid
					where
						WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id
				)
				select
					wdui.WhsDocumentUcInvent_id as \"WhsDocumentUcInvent_id\",
					dor.DrugShipment_id as \"DrugShipment_id\",
					dor.Drug_did as \"Drug_did\",
					dor.PrepSeries_id as \"PrepSeries_id\",
					dor.SubAccountType_id as \"SubAccountType_id\",
					dor.Okei_id as \"Okei_id\",
					dor.DrugOstatRegistry_Kolvo as \"WhsDocumentUcInventDrug_Kolvo\",
					dor.DrugOstatRegistry_Sum as \"WhsDocumentUcInventDrug_Sum\",
					dor.DrugOstatRegistry_Cost as \"WhsDocumentUcInventDrug_Cost\",
					wdui.StorageZone_id as \"StorageZone_id\"
				from
					v_WhsDocumentUcInvent wdui
					inner join v_DrugOstatRegistry dor on
						dor.Org_id = wdui.Org_id and
						dor.Contragent_id = wdui.Contragent_id and
                                                COALESCE(dor.Storage_id, 0) = COALESCE(wdui.Storage_id, 0) and
                                                dor.DrugFinance_id = wdui.DrugFinance_id and
                                                dor.WhsDocumentCostItemType_id = wdui.WhsDocumentCostItemType_id
                                        --inner join v_DocumentUcStr dus on dus.Drug_id = dor.Drug_did
                                        inner join v_DrugFinance df on df.DrugFinance_id = dor.DrugFinance_id
                                        --inner join v_DocumentUc du on du.DocumentUc_id = dus.DocumentUc_id 
                                        inner join v_WhsDocumentCostItemType wdcit on wdcit.WhsDocumentCostItemType_id = dor.WhsDocumentCostItemType_id

					left join v_DrugShipment ds on ds.DrugShipment_id = dor.DrugShipment_id
					--left join v_DrugShipmentLink dsl on dsl.DrugShipment_id = ds.DrugShipment_id
					left join supply_list on supply_list.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
				where 
					wdui.WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id and
					(:supply_count = 0 or supply_list.WhsDocumentSupply_id is not null) 
					and dor.DrugOstatRegistry_Kolvo > 0;
					--and dsl.DocumentUcStr_id is not null
			";

		//print getDebugSQL($query, $data);exit;

		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$item_arr = $result->result('array');
		}

		return $item_arr;
	}

	/**
	 * Загрузка списка медикаментов ведомости
	 * для уфимских аптек ЛЛО
	 */
	function farm_loadWhsDocumentUcInventDrugList($filter)
	{
		$where = array();
		$p = array();

		if (!empty($filter['WhsDocumentUcInvent_id'])) {
			$where[] = 'wduid.WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id';
			$p['WhsDocumentUcInvent_id'] = $filter['WhsDocumentUcInvent_id'];
		}
		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'where ' . $where_clause;
		}

		$q = "
			select
				wduid.WhsDocumentUcInventDrug_id as \"WhsDocumentUcInventDrug_id\",
				wdui.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				d.Drug_Code as \"Drug_Code\",
				d.Drug_Name as \"Drug_Name\",
				--ps.PrepSeries_Ser,
                dus.DocumentUcStr_Ser as \"PrepSeries_Ser\",
				COALESCE(isdef.YesNo_Code, 0) as \"PrepSeries_isDefect\",
				ds.DrugShipment_Name as \"DrugShipment_Name\",
                                --d.Drug_Code DrugShipment_Name,
				wds.WhsDocumentUc_Name as \"WhsDocumentSupply_Name\",
				sat.SubAccountType_Name as \"SubAccountType_Name\",
				o.Okei_Name as \"Okei_Name\",
				wduid.WhsDocumentUcInventDrug_Kolvo as \"WhsDocumentUcInventDrug_Kolvo\",
				wduid.WhsDocumentUcInventDrug_FactKolvo as \"WhsDocumentUcInventDrug_FactKolvo\",
				wduid.WhsDocumentUcInventDrug_Sum as \"WhsDocumentUcInventDrug_Sum\",
				wduid.WhsDocumentUcInventDrug_Cost as \"WhsDocumentUcInventDrug_Cost\"
			from 
				dbo.v_WhsDocumentUcInventDrug wduid
				left join dbo.v_WhsDocumentUcInvent wdui on wdui.WhsDocumentUcInvent_id = wduid.WhsDocumentUcInvent_id
				left join dbo.v_Drug d on d.Drug_id = wduid.Drug_did
                                --left join v_DocumentUcStr dus with (nolock) on dus.Drug_id = wduid.Drug_did
				left join dbo.v_DrugShipment ds  on ds.DrugShipment_id = wduid.DrugShipment_id
				left join lateral( Select  DocumentUcStr_Ser from v_DocumentUcStr dus
					join  dbo.v_DrugShipmentLink ln on ln.DocumentUcStr_id = dus.DocumentUcStr_id and ln.DrugShipment_id =  wduid.DrugShipment_id
				 where dus.Drug_id = wduid.Drug_did
				 limit 1
				) dus on true
				left join rls.v_PrepSeries ps on ps.PrepSeries_id = wduid.PrepSeries_id
				left join v_YesNo isdef on isdef.YesNo_id = ps.PrepSeries_isDefect
				left join dbo.v_SubAccountType sat on sat.SubAccountType_id = wduid.SubAccountType_id
				left join dbo.v_Okei o on o.Okei_id = wduid.Okei_id
				left join dbo.v_WhsDocumentSupply wds on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
			$where_clause
		";
		//print getDebugSQL($q, $filter);exit;

		$result = $this->db->query($q, $filter);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Создание списка медикаментов для инвентаризационной ведомости
	 *для уфимских аптек ЛЛО
	 */
	function farm_createDocumentUcInventDrugList($data)
	{
		$error = array();
		$item_arr = array();

		//проверяем не существует ли уже для данной ведомости списка медикаментов
		$query = "
			select
				count(WhsDocumentUcInventDrug_id) as \"cnt\"
			from
				v_WhsDocumentUcInventDrug
			where
				WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id;
		";
		$result = $this->getFirstResultFromQuery($query, $data);
		if ($result > 0) {
			$error[] = 'Для данной ведомости уже существует список медикаментов.';
			if (!empty($data['forAll'])) {
				return array();
			}
		}

		if (count($error) == 0) {
			$item_arr = $this->getDocumentUcInventDrugList($data);
		}

		//старт транзакции
		$this->beginTransaction();

		//сохранение списка медикаментов
		if (count($error) == 0) {
			foreach ($item_arr as $item) {
				$item['WhsDocumentUcInventDrug_id'] = null;
				$item['pmUser_id'] = $data['pmUser_id'];
				//  Присваиваем факту расчетные остатки
				$item['WhsDocumentUcInventDrug_FactKolvo'] = $item['WhsDocumentUcInventDrug_Kolvo'];
				$result = $this->saveObject('WhsDocumentUcInventDrug', $item);
				if (!empty($result['Error_Msg'])) {
					$error[] = $result['Error_Msg'];
					break;
				}
			}
		}

		//пересчет суммы ведомости
		$result = $this->updateWhsDocumentUcInventSum($data);

		//вывод ошибок
		if (count($error) > 0) {
			$result = array('Error_Msg' => $error[0]);
			$this->rollbackTransaction();
			return $result;
		}

		//коммит транзакции
		$this->commitTransaction();

		return $result;
	}

	/**
	 * Создание списка медикаментов для нескольких инвентаризационных ведомостей
	 */
	function createDocumentUcInventDrugListAll($data)
	{
		$queryParams = array();

		$query = "
			SELECT
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Message\"
			FROM r2.p_createDocumentUcInventDrugList (
				List := :WhsDocumentUcInvent_List,
				pmUser_id := :pmUser_id,
				Remake := :Remake
			);
		";


		$queryParams['WhsDocumentUcInvent_List'] = $data['WhsDocumentUcInvent_List'];
		$queryParams['Remake'] = $data['Remake'];
		$queryParams['pmUser_id'] = $data['pmUser_id'];

		//print getDebugSQL ($query, $queryParams);exit;

		$result = $this->getFirstRowFromQuery($query, $queryParams);

		//$result = $this->db->query($query, $queryParams);
		//var_dump($result);
		//echo $result['Error_Msg'];

		if ($result && is_array($result)) {
			//if($result[$key_field] > 0) {
			$result['success'] = true;
			//}
			return $result;
		} else {
			var_dump($result);
			return array('Error_Msg' => 'При сохранении произошла ошибка');
			//return array('Error_Msg' => $result['Error_Msg']);
		}
	}


	/**
	 * Создание списка инвентаризационных ведомостей
	 * для уфимских аптек ЛЛО
	 */
	function farm_createDocumentUcInventList($data)
	{
		$where = array();
		$params = array();
		$sz_where = array();
		$where_org = '';
		$where_clause2 = '';
		$union = "";

		if (!empty($data['Storage_List'])) {
			// список пришедших записей складов где не указано место хранения - докумены создадутся для каждого места хранения в таком складе
			$stor_check_arr = array();

			// массив мест хранения и родительского для этого места хранения склада - докумены создадутся только для указанного здесь места хранения, а не для всего скалад
			$stor_sz_arr = array();

			$stor_arr = explode(',', $data['Storage_List']);
			foreach ($stor_arr as $stor) {
				$item = explode('|', $stor);
				if (empty($item[1])) {
					array_push($stor_check_arr, $item[0]);
				} else {
					if (!in_array($item[0], $stor_check_arr)) {
						array_push($stor_sz_arr, array('Storage_id' => $item[0], 'StorageZone_id' => $item[1]));
					}
				}
			}
			if (count($stor_sz_arr) > 0) {
				$sz_arr = array();
				foreach ($stor_sz_arr as $stor_sz) {
					if (!in_array($stor_sz['Storage_id'], $stor_check_arr)) {
						array_push($sz_arr, $stor_sz['StorageZone_id']);
					}
				}
				if (count($sz_arr) > 0) {
					$sz_where[] = "sz.StorageZone_id in (" . implode(',', $sz_arr) . ")";
				}
			}
			if (count($stor_check_arr) > 0) {
				$where[] = "ssl.Storage_id in (" . implode(',', $stor_check_arr) . ")";
			}
		}
		if (!empty($data['Org_List'])) {
			$where[] = "ssl.Org_id in ({$data['Org_List']})";
			$where_org = " where o.org_id in ({$data['Org_List']})";
		}

		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'where ' . $where_clause;
			$where_clause2 = str_replace('where', 'and', $where_clause);
			$where_clause .= ' and  exists (Select 1 from DrugOstatRegistry dor
					where Dor.Org_id = ssl.Org_id
						and dor.Storage_id = ssl.Storage_id)';
		}

		if (count($sz_where) > 0) {
			$sz_where_clause = implode(' and ', $sz_where);
			if (strlen($sz_where_clause)) {
				$sz_where_clause = 'where ' . $sz_where_clause;
				$$sz_where_clause .= ' and  exists (Select 1 from DrugOstatRegistry dor
					where Dor.Org_id = ssl.Org_id
						and dor.Storage_id = ssl.Storage_id)';
			}
			$union = "
				union all
				select
					(
						select
							COALESCE(max(cast(WhsDocumentUc_Num as bigint)),0) + 1 as WhsDocumentUc_Num
						from
							v_WhsDocumentUc
						where
							WhsDocumentType_id = (select WhsDocumentType_id from v_WhsDocumentType where WhsDocumentType_Code = 21 limit 1) and
							WhsDocumentUc_Num not ilike '%.%' and
							WhsDocumentUc_Num not ilike '%,%' and
							(WhsDocumentUc_Num ~ '^([0-9]+[.]?[0-9]*|[.][0-9]+)$')  and
							length(WhsDocumentUc_Num) <= 18
					) as WhsDocumentUc_Num,
					(select WhsDocumentType_Name from v_WhsDocumentType where WhsDocumentType_id = (select WhsDocumentType_id from v_WhsDocumentType where WhsDocumentType_Code = 21 limit 1)) as WhsDocumentType_Name,
					(select WhsDocumentType_id from v_WhsDocumentType where WhsDocumentType_Code = 21 limit 1) as WhsDocumentType_id,
					(select WhsDocumentStatusType_id from v_WhsDocumentStatusType where WhsDocumentStatusType_Code = 1 limit 1) as WhsDocumentStatusType_id,
					1 as WhsDocumentStatusType_Code,
					(select WhsDocumentStatusType_Name from v_WhsDocumentStatusType where WhsDocumentStatusType_id = (select WhsDocumentStatusType_id from v_WhsDocumentStatusType where WhsDocumentStatusType_Code = 1 limit 1)) as WhsDocumentStatusType_Name,
					o.Org_id,
					o.Org_Name,
                    c.Contragent_id,
                    c.Contragent_Name,
					s.Storage_id,
					s.Storage_Name,
					sz.StorageZone_id,
					rtrim(COALESCE(sut.StorageUnitType_Name,'') || ' ' || COALESCE(sz.StorageZone_Address,'')) as StorageZone_Name
				from
					v_StorageStructLevel ssl
					left join v_Lpu l on l.Lpu_id = ssl.Lpu_id
					inner join v_Org o on o.Org_id = isnull(ssl.Org_id, l.Org_id)
					inner join v_Storage s on s.Storage_id = ssl.Storage_id
					left join v_StorageZone sz on sz.Storage_id = s.Storage_id
					left join v_StorageUnitType sut on sut.StorageUnitType_id = sz.StorageUnitType_id
					inner join v_Contragent c on c.Org_id = ssl.Org_id and ContragentType_id = 3
				$sz_where_clause
			";
		}
		$union = '';  // Убрал: непонятно, что хотели
		$query = "
			select
				(
					select
						COALESCE(max(cast(WhsDocumentUc_Num as bigint)),0) + 1 as WhsDocumentUc_Num
					from
						v_WhsDocumentUc
					where
						WhsDocumentType_id = (select WhsDocumentType_id from v_WhsDocumentType where WhsDocumentType_Code = 21 limit 1) and
						WhsDocumentUc_Num not ilike '%.%' and
						WhsDocumentUc_Num not ilike '%,%' and
						(WhsDocumentUc_Num ~ '^([0-9]+[.]?[0-9]*|[.][0-9]+)$')  and
						length(WhsDocumentUc_Num) <= 18
				) as WhsDocumentUc_Num,
				(select WhsDocumentType_Name from v_WhsDocumentType where WhsDocumentType_id = (select WhsDocumentType_id from v_WhsDocumentType where WhsDocumentType_Code = 21 limit 1)) as WhsDocumentType_Name,
				(select WhsDocumentType_id from v_WhsDocumentType where WhsDocumentType_Code = 21 limit 1) as WhsDocumentType_id,
				(select WhsDocumentStatusType_id from v_WhsDocumentStatusType where WhsDocumentStatusType_Code = 1 limit 1) as WhsDocumentStatusType_id,
				1 as WhsDocumentStatusType_Code,
				(select WhsDocumentStatusType_Name from v_WhsDocumentStatusType where WhsDocumentStatusType_id = (select WhsDocumentStatusType_id from v_WhsDocumentStatusType where WhsDocumentStatusType_Code = 1 limit 1)) as WhsDocumentStatusType_Name,
				o.Org_id,
				o.Org_Name,
				c.Contragent_id,
				c.Contragent_Name,
				s.Storage_id,
				s.Storage_Name,
				sz.StorageZone_id,
				rtrim(COALESCE(sut.StorageUnitType_Name,'') || ' ' || COALESCE(sz.StorageZone_Address,'')) as StorageZone_Name
			from
				v_StorageStructLevel ssl
				inner join v_Org o on o.Org_id = ssl.Org_id
				inner join v_Storage s on s.Storage_id = ssl.Storage_id
				left join v_StorageZone sz on sz.Storage_id = s.Storage_id
				left join v_StorageUnitType sut on sut.StorageUnitType_id = sz.StorageUnitType_id
				left join lateral (
					select
						i_c.Contragent_id,
						i_c.Contragent_Name
					from
						v_Contragent i_c
					where
						i_c.Org_id = ssl.Org_id
						and ContragentType_id = 3
				) c on true
			$where_clause
			union all
			select
				(
					select
						COALESCE(max(cast(WhsDocumentUc_Num as bigint)),0) + 1 as WhsDocumentUc_Num
					from
						v_WhsDocumentUc
					where
						WhsDocumentType_id = (select WhsDocumentType_id from v_WhsDocumentType where WhsDocumentType_Code = 21 limit 1) and
						WhsDocumentUc_Num not ilike '%.%' and
						WhsDocumentUc_Num not ilike '%,%' and
						(WhsDocumentUc_Num ~ '^([0-9]+[.]?[0-9]*|[.][0-9]+)$')  and
						length(WhsDocumentUc_Num) <= 18
				) as WhsDocumentUc_Num,
				(select WhsDocumentType_Name from v_WhsDocumentType where WhsDocumentType_id = (select WhsDocumentType_id from v_WhsDocumentType where WhsDocumentType_Code = 21 limit 1)) as WhsDocumentType_Name,
				(select WhsDocumentType_id from v_WhsDocumentType where WhsDocumentType_Code = 21 limit 1) as WhsDocumentType_id,
				(select WhsDocumentStatusType_id from v_WhsDocumentStatusType where WhsDocumentStatusType_Code = 1 limit 1) as WhsDocumentStatusType_id,
				1 as WhsDocumentStatusType_Code,
				(select WhsDocumentStatusType_Name from v_WhsDocumentStatusType where WhsDocumentStatusType_id = (select WhsDocumentStatusType_id from v_WhsDocumentStatusType where WhsDocumentStatusType_Code = 1 limit 1)) as WhsDocumentStatusType_Name,
				o.Org_id,
				o.Org_Name,
				c.Contragent_id,
				c.Contragent_Name,
				s.Storage_id,
				s.Storage_Name,
				null as StorageZone_id,
				null as StorageZone_Name
			from
				v_StorageStructLevel ssl
				left join v_Lpu l on l.Lpu_id = ssl.Lpu_id
				inner join v_Org o on o.Org_id = COALESCE(ssl.Org_id, l.Org_id)
					and 1 > 1  --  Убрал: непонятно, что хотели 
				inner join v_Storage s on s.Storage_id = ssl.Storage_id
				inner join v_Contragent c on c.Org_id = ssl.Org_id and ContragentType_id = 3
			$where_clause
			$union
			union all 
			Select 
				(
					select
						COALESCE(max(cast(WhsDocumentUc_Num as bigint)),0) + 1 as WhsDocumentUc_Num
					from
						v_WhsDocumentUc
					where
						WhsDocumentType_id = (select WhsDocumentType_id from v_WhsDocumentType where WhsDocumentType_Code = 21 limit 1) and
						WhsDocumentUc_Num not ilike '%.%' and
						WhsDocumentUc_Num not ilike '%,%' and
						(WhsDocumentUc_Num ~ '^([0-9]+[.]?[0-9]*|[.][0-9]+)$')  and
						length(WhsDocumentUc_Num) <= 18
				) as WhsDocumentUc_Num,
				(select WhsDocumentType_Name from v_WhsDocumentType where WhsDocumentType_id = (select WhsDocumentType_id from v_WhsDocumentType where WhsDocumentType_Code = 21 limit 1)) as WhsDocumentType_Name,
				(select WhsDocumentType_id from v_WhsDocumentType where WhsDocumentType_Code = 21 limit 1) as WhsDocumentType_id,
				(select WhsDocumentStatusType_id from v_WhsDocumentStatusType where WhsDocumentStatusType_Code = 1 limit 1) as WhsDocumentStatusType_id,
				1 as WhsDocumentStatusType_Code,
				(select WhsDocumentStatusType_Name from v_WhsDocumentStatusType where WhsDocumentStatusType_id = (select WhsDocumentStatusType_id from v_WhsDocumentStatusType where WhsDocumentStatusType_Code = 1 limit 1)) as WhsDocumentStatusType_Name,
				o.Org_id,
				o.Org_Name,
				c.Contragent_id,
				c.Contragent_Name,
				null Storage_id,
				'Без МО' Storage_Name,
				null as StorageZone_id,
				null as StorageZone_Name
				from v_Org o
				left join lateral (
					select
						i_c.Contragent_id,
						i_c.Contragent_Name
					from
						v_Contragent i_c
					where
						i_c.Org_id = o.Org_id
						and ContragentType_id = 3
						and exists (Select 1 from  v_StorageStructLevel ssl where ssl.Org_id = o.Org_id  $where_clause2)
				) c on true
			$where_org
		";
		//print getDebugSQL ($query, $params); exit;

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка ведомостей
	 */
	function loadWhsDocumentUcInventList($filter)
	{
		$where = array();
		$p = array();

		if (!empty($filter['WhsDocumentUc_pid'])) {
			$where[] = 'wdui.WhsDocumentUc_pid = :WhsDocumentUc_pid';
			$p['WhsDocumentUc_pid'] = $filter['WhsDocumentUc_pid'];
		}
		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'where ' . $where_clause;
		}

		$q = "
			select
				wdui.WhsDocumentUc_id as \"WhsDocumentUc_id\",
				wdui.WhsDocumentUcInvent_id as \"WhsDocumentUcInvent_id\",
				wdui.WhsDocumentUc_pid as \"WhsDocumentUc_pid\",
				wdui.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				wdui.WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
				wdui.WhsDocumentType_id as \"WhsDocumentType_id\",
				to_char(wdui.WhsDocumentUc_Date, 'dd.mm.yyyy') as \"WhsDocumentUc_Date\",
				to_char(wdui.WhsDocumentUcInvent_begDT, 'dd.mm.yyyy') as \"WhsDocumentUcInvent_begDT\",
				wdui.WhsDocumentStatusType_id as \"WhsDocumentStatusType_id\",
				wdst.WhsDocumentStatusType_Code as \"WhsDocumentStatusType_Code\",
				wdst.WhsDocumentStatusType_Name as \"WhsDocumentStatusType_Name\",
				wdui.Contragent_id as \"Contragent_id\",
				c.Contragent_Name as \"Contragent_Name\",
				wdui.Storage_id as \"Storage_id\",
				COALESCE(s.Storage_Name, 'Без МО') as \"Storage_Name\",
				wdui.Org_id as \"Org_id\",
				o.Org_Name as \"Org_Name\",
				wdui.DrugFinance_id as \"DrugFinance_id\",
				wdui.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\"
			from
				dbo.v_WhsDocumentUcInvent wdui
				left join dbo.v_WhsDocumentStatusType wdst on wdst.WhsDocumentStatusType_id = wdui.WhsDocumentStatusType_id
				left join dbo.v_Contragent c on c.Contragent_id = wdui.Contragent_id
				left join dbo.v_Storage s on s.Storage_id = wdui.Storage_id
				left join dbo.Org o on o.Org_id = wdui.Org_id
			$where_clause
		";
		$result = $this->db->query($q, $filter);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка для
	 * для уфимских аптек ЛЛО
	 */
	function farm_loadList($filter)
	{
		$where = array();
		$p = array();

		//в списке отображаются только те ведомости, которые соответствую подписанным приказам
		$where[] = '(p_wdst.WhsDocumentStatusType_Code = 2)';
		//$filter['Org_id'] = 68320115968;
		if (isset($filter['Org_id']) && $filter['Org_id']) {
			$where[] = '(wdui.Org_id = :Org_id or p_wdu.Org_aid = :Org_id)';
			$p['Org_id'] = $filter['Org_id'];
		}
		if (isset($filter['WhsDocumentUc_DateRange']) && count($filter['WhsDocumentUc_DateRange']) == 2 && !empty($filter['WhsDocumentUc_DateRange'][0])) {
			$where[] = 'wdui.WhsDocumentUc_Date between :WhsDocumentUc_Date1 and :WhsDocumentUc_Date2';
			$p['WhsDocumentUc_Date1'] = $filter['WhsDocumentUc_DateRange'][0];
			$p['WhsDocumentUc_Date2'] = $filter['WhsDocumentUc_DateRange'][1];
		}
		if (isset($filter['DrugFinance_id']) && $filter['DrugFinance_id']) {
			$where[] = 'wdui.DrugFinance_id = :DrugFinance_id';
			$p['DrugFinance_id'] = $filter['DrugFinance_id'];
		}
		if (isset($filter['WhsDocumentCostItemType_id']) && $filter['WhsDocumentCostItemType_id']) {
			$where[] = 'wdui.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id';
			$p['WhsDocumentCostItemType_id'] = $filter['WhsDocumentCostItemType_id'];
		}
		if (!empty($filter['Contragent_Name'])) {
			$where[] = "c.Contragent_Name ilike '%'+:Contragent_Name+'%'";
			$p['Contragent_Name'] = $filter['Contragent_Name'];
		}
		if (!empty($filter['Contragent_id'])) {
			$where[] = "wdui.Contragent_id = :Contragent_id";
			$params['Contragent_id'] = $filter['Contragent_id'];
		}
		if (!empty($filter['Storage_Name'])) {
			$where[] = "s.Storage_Name ilike '%'+:Storage_Name+'%'";
			$p['Storage_Name'] = $filter['Storage_Name'];
		}
		if (!empty($filter['Storage_id'])) {
			$where[] = "wdui.Storage_id = :Storage_id";
			$params['Storage_id'] = $filter['Storage_id'];
		}
		if (!empty($filter['StorageZone_id'])) {
			$where[] = "wdui.StorageZone_id = :StorageZone_id";
			$params['StorageZone_id'] = $filter['StorageZone_id'];
		}
		if (!empty($filter['ARMType'])) {
			if ($filter['ARMType'] == 'adminllo') {
				$where[] = "
					p_wdu.Org_aid in (
						select
							i_ms.Org_id
						from
							v_MedService i_ms
							left join v_MedServiceType i_mst on i_mst.MedServiceType_id = i_ms.MedServiceType_id
						where
							--i_mst.MedServiceType_SysNick in ('adminllo', 'spesexpertllo') and
							i_ms.Org_id is not null
					)
				";

			}
		}

		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'where ' . $where_clause;
		}
		$q = "
			select
				wdui.WhsDocumentUc_id as \"WhsDocumentUc_id\",
				wdui.WhsDocumentUcInvent_id as \"WhsDocumentUcInvent_id\",
				wdui.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				wdst.WhsDocumentStatusType_Code as \"WhsDocumentStatusType_Code\",
				wdst.WhsDocumentStatusType_Name as \"WhsDocumentStatusType_Name\",
				to_char(wdui.WhsDocumentUc_Date, 'dd.mm.yyyy') as \"WhsDocumentUc_Date\",
				wdui.WhsDocumentUc_Sum as \"WhsDocumentUc_Sum\",
				p_wdu.WhsDocumentUc_Name as \"WhsDocumentUc_pName\",
				o.Org_Name as \"Org_Name\",
				COALESCE(s.Storage_Name, 'Без МО') as \"Storage_Name\",
				DrugFinance_Name as \"DrugFinance_Name\",
				wdcit.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
				row_cnt.cnt as \"Drug_Count\",
				case 
					when ot.OrgType_SysNick = 'lpu' then lb.LpuBuilding_Name
					else os.OrgStruct_Name
				end as \"LpuBuilding_Name\",
				sz.StorageZone_Address as \"StorageZone_Name\",
				Doc_New.kolDoc as \"kolDoc\", to_char(Doc_New.minDate, 'dd.mm.yyyy') as \"minDate\"
			from
				dbo.v_WhsDocumentUcInvent wdui
				left join dbo.v_WhsDocumentUc p_wdu  on p_wdu.WhsDocumentUc_id = wdui.WhsDocumentUc_pid
				left join dbo.v_WhsDocumentStatusType p_wdst on p_wdst.WhsDocumentStatusType_id = p_wdu.WhsDocumentStatusType_id
				left join dbo.Org o on o.Org_id = wdui.Org_id
				left join dbo.v_OrgType ot on ot.OrgType_id = o.OrgType_id
				left join dbo.v_Storage s on s.Storage_id = wdui.Storage_id
				left join dbo.v_StorageZone sz  on sz.StorageZone_id = wdui.StorageZone_id
				left join dbo.v_Contragent c on c.Contragent_id = wdui.Contragent_id
				left join dbo.v_DrugFinance df on df.DrugFinance_id = wdui.DrugFinance_id
				left join dbo.v_WhsDocumentCostItemType wdcit on wdcit.WhsDocumentCostItemType_id = wdui.WhsDocumentCostItemType_id
				left join dbo.v_WhsDocumentStatusType wdst on wdst.WhsDocumentStatusType_id = wdui.WhsDocumentStatusType_id
				left join lateral (
					select
						count(wduid.WhsDocumentUcInventDrug_id) as cnt
					from
						v_WhsDocumentUcInventDrug wduid
					where
						wduid.WhsDocumentUcInvent_id = wdui.WhsDocumentUcInvent_id
				) row_cnt on true
				left join lateral (SElect  count(8) kolDoc, min(du.DocumentUc_setDate) minDate 
					from DocumentUc du
						left join lateral (Select max(DrugPeriodClose_DT) DrugPeriodClose_DT from DrugPeriodClose prd 
						where prd.Org_id = du.Org_id
							and DrugPeriodClose_Sign = 2) prd on true
						where du.DrugDocumentStatus_id = 1
							and du.Org_id = wdui.Org_id
							--and du.WhsDocumentCostItemType_id = wdui.WhsDocumentCostItemType_id
							and du.DocumentUc_setDate >= COALESCE(prd.DrugPeriodClose_DT, '2016-06-01')
							and du.DocumentUc_setDate <= wdui.WhsDocumentUc_Date
					) Doc_New on true
				left join lateral (
					select
						i_lb.LpuBuilding_id,
						i_lb.LpuBuilding_Name
					from
						v_StorageStructLevel i_ssl
						left join v_MedService i_ms on i_ms.MedService_id = i_ssl.MedService_id
						left join v_LpuBuilding i_lb on i_lb.LpuBuilding_id = isnull(i_ssl.LpuBuilding_id, i_ms.LpuBuilding_id)
					where
						i_ssl.Storage_id = wdui.Storage_id
					order by
						i_ssl.LpuBuilding_id desc, i_ms.LpuBuilding_id desc
					limit 1
				) lb on true
				left join lateral (
					select
						i_ssl.OrgStruct_id,
						i_os.OrgStruct_Name
					from
						v_StorageStructLevel i_ssl
						left join v_OrgStruct i_os on i_os.OrgStruct_id = i_ssl.OrgStruct_id
					where
						i_ssl.Storage_id = wdui.Storage_id
					order by
						i_ssl.OrgStruct_id desc
					limit 1
				) os on true
			$where_clause
		";
		//print getDebugSQL ($q, $p); exit;
		//$dbrep = $this->load->database('bdprogress', true);

		$dbrep = $this->db;
		//print getDebugSQL($q, $p); exit;
		$result = $dbrep->query($q, $p);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Создание дочернего документа для инвентаризационной ведомости
	 */
	function createDocumentUc($data)
	{
		$error = array();
		$drug_data = array();
		$common_data = array();
		$invent_data = array();
		$doc_id = null;
		$current_date = date('Y-m-d'); // текущая дата
		$new_num = '';
		//var_dump($data['Contragent_id']); exit;

		//$new_num = $this->generateNum('DocumentUc', 'DocumentUc_Num');
		$type = $data['DrugDocumentType_SysNick'];
		$param = array();
		$param ['Contragent_id'] = $data['Contragent_id'];
		if ($type == 'DocOprih') {
			$param ['DrugDocumentType_id'] = 12;
			$param ['DrugDocumentType_Code'] = 12;
		} else if ($type == 'DokSpis') {
			$param ['DrugDocumentType_id'] = 2;
			$param ['DrugDocumentType_Code'] = 2;
		}
		$this->load->model("DocumentUc_model", "doc_model");
		$r_num = $this->doc_model->generateDocumentUcNum($param);

		if ($r_num && is_array($r_num)) {
			if (!empty($r_num[0]['DocumentUc_Num']))
				$new_num = $r_num[0]['DocumentUc_Num'];
		}
		if ($new_num == '')
			$new_num = $this->generateNum('DocumentUc', 'DocumentUc_Num');

		//получение данных ведомости
		if (count($error) == 0) {
			$query = "
				select
					wdui.WhsDocumentUc_id as \"WhsDocumentUc_id\",
					wdui.DrugFinance_id as \"DrugFinance_id\",
					wdui.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
					wdui.Org_id as \"Org_id\",
					wdui.Contragent_id as \"Contragent_id\",
					wdui.Storage_id as \"Storage_id\",
					wdui.StorageZone_id as \"StorageZone_id\",
					i_ord.WhsDocumentUc_Num as \"Order_Num\",
					to_char(i_ord.WhsDocumentUc_Date, 'dd.mm.yyyy') as \"Order_Date\"
				from
					v_WhsDocumentUcInvent wdui
					left join v_WhsDocumentUc i_ord on i_ord.WhsDocumentUc_id = wdui.WhsDocumentUc_pid
				where
					wdui.WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id;
			";
			$invent_data = $this->getFirstRowFromQuery($query, array(
				'WhsDocumentUcInvent_id' => $data['WhsDocumentUcInvent_id']
			));
			if (count($invent_data) < 1) {
				$error[] = "Не удалось получить данные инвентаризационно ведомости.";
			}
		}

		//проверяем не создан ли уже документ учета для данной ведомости
		if (count($error) == 0) {
			$query = "
				select
					du.DocumentUc_id as \"DocumentUc_id\",
					du.DocumentUc_Num as \"DocumentUc_Num\",
					to_char(du.DocumentUc_setDate, 'dd.mm.yyyy') as \"DocumentUc_setDate\"
				from
					v_DocumentUc du
					left join v_DrugDocumentType ddt on ddt.DrugDocumentType_id = du.DrugDocumentType_id
				where
					du.WhsDocumentUc_id = :WhsDocumentUc_id and
					ddt.DrugDocumentType_SysNick = :DrugDocumentType_SysNick
				limit 1;
			";
			$result = $this->getFirstRowFromQuery($query, array(
				'WhsDocumentUc_id' => $invent_data['WhsDocumentUc_id'],
				'DrugDocumentType_SysNick' => $data['DrugDocumentType_SysNick']
			));
			if ($result && is_array($result)) {
				if (!empty($result['DocumentUc_id'])) {
					if ($type == 'DocOprih') {
						$error[] = "Для данной ведомости уже создан документ оприходования № {$result['DocumentUc_Num']} от {$result['DocumentUc_setDate']}.";
					}
					if ($type == 'DokSpis') {
						$error[] = "Для данной ведомости уже создан документ списания № {$result['DocumentUc_Num']} от {$result['DocumentUc_setDate']}.";
					}
				}
			}
		}

		//проверяем есть ли медикаменты для создания документа учета
		if (count($error) == 0) {
			$k_query = "";
			$w_query = "";
			$error_msg = "";

			if ($type == 'DocOprih') {
				$k_query = "wduid.WhsDocumentUcInventDrug_FactKolvo-COALESCE(wduid.WhsDocumentUcInventDrug_Kolvo, 0)";
				$w_query = "wduid.WhsDocumentUcInventDrug_FactKolvo > COALESCE(wduid.WhsDocumentUcInventDrug_Kolvo, 0) and";
				$error_msg = "Выполнение операции не доступно, т.к. в результатах инвентаризации излишки не зафиксированы.";
			}
			if ($type == 'DokSpis') {
				$k_query = "COALESCE(wduid.WhsDocumentUcInventDrug_Kolvo, 0)-wduid.WhsDocumentUcInventDrug_FactKolvo";
				$w_query = "wduid.WhsDocumentUcInventDrug_FactKolvo < COALESCE(wduid.WhsDocumentUcInventDrug_Kolvo, 0) and";
				$error_msg = "Выполнение операции не доступно, т.к. в результатах инвентаризации недостача не зафиксирована.";
			}

			$query = "
				select
					--wduid.Drug_id,
					COALESCE(wduid.Drug_id, wduid.Drug_did) as \"Drug_id\",
					wduid.DrugShipment_id as \"DrugShipment_id\",
					dus.DocumentUcStr_id as \"DocumentUcStr_oid\",
					dus.DrugNds_id as \"DrugNds_id\",
					dus.DocumentUcStr_IsNDS as \"DocumentUcStr_IsNDS\",
					dus.DocumentUcStr_Price as \"DocumentUcStr_Price\",
					c.NewCount as \"DocumentUcStr_Count\",
					cast(dus.DocumentUcStr_EdCount*k.koef as decimal(12,2)) as \"DocumentUcStr_EdCount\",
					cast(dus.DocumentUcStr_Sum*k.koef as decimal(12,2)) as \"DocumentUcStr_Sum\",
					cast(dus.DocumentUcStr_SumNds*k.koef as decimal(12,2)) as \"DocumentUcStr_SumNds\",
					dus.DocumentUcStr_Ser as \"DocumentUcStr_Ser\",
					dus.PrepSeries_id as \"PrepSeries_id\",
					dus.DocumentUcStr_CertNum as \"DocumentUcStr_CertNum\",
					dus.DocumentUcStr_CertOrg as \"DocumentUcStr_CertOrg\",
					dus.DrugLabResult_Name as \"DrugLabResult_Name\",
					dus.DocumentUcStr_CertDate as \"DocumentUcStr_CertDate\",
					dus.DocumentUcStr_CertGodnDate as \"DocumentUcStr_CertGodnDate\",
					dus.DrugFinance_id as \"DrugFinance_id\"
				from
					v_WhsDocumentUcInventDrug wduid
					left join DrugShipmentLink dsl on dsl.DrugShipment_id = wduid.DrugShipment_id
					left join v_DocumentUcStr dus on dus.DocumentUcStr_id = dsl.DocumentUcStr_id
					left join lateral (
						select {$k_query} as NewCount
					) as c on true
					left join lateral (
						select c.NewCount/dus.DocumentUcStr_Count as koef
					) as k on true
				where
					wduid.WhsDocumentUcInventDrug_FactKolvo is not null and
					{$w_query}
					wduid.WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id and
					dus.DocumentUcStr_Count > 0;
			";

			//  В рамках разделения АРМа товароведа
			$session_data = getSessionParams();
			$orgtype = $session_data['session']['orgtype'];
			$region = $session_data['session']['region']['nick'];

			if ($region == 'ufa' && $orgtype == 'farm') {
				$query = "
				select
					--wduid.Drug_id,
					COALESCE(wduid.Drug_id, wduid.Drug_did) as \"Drug_id\",
					wduid.DrugShipment_id as \"DrugShipment_id\",
					dus.DocumentUcStr_id as \"DocumentUcStr_oid\",
					dus.DrugNds_id as \"DrugNds_id\",
					dus.DocumentUcStr_IsNDS as \"DocumentUcStr_IsNDS\",
					dus.DocumentUcStr_Price as \"DocumentUcStr_Price\",
					c.NewCount as \"DocumentUcStr_Count\",
					cast(dus.DocumentUcStr_EdCount*k.koef as decimal(12,2)) as \"DocumentUcStr_EdCount\",
					cast(dus.DocumentUcStr_Sum*k.koef as decimal(12,2)) as \"DocumentUcStr_Sum\",
					cast(dus.DocumentUcStr_SumNds*k.koef as decimal(12,2)) as \"DocumentUcStr_SumNds\",
					dus.DocumentUcStr_Ser as \"DocumentUcStr_Ser\",
					dus.PrepSeries_id as \"PrepSeries_id\",
					dus.DocumentUcStr_CertNum as \"DocumentUcStr_CertNum\",
					dus.DocumentUcStr_CertOrg as \"DocumentUcStr_CertOrg\",
					dus.DrugLabResult_Name as \"DrugLabResult_Name\",
					dus.DocumentUcStr_CertDate as \"DocumentUcStr_CertDate\",
					dus.DocumentUcStr_CertGodnDate as \"DocumentUcStr_CertGodnDate\",
					dus.DrugFinance_id as \"DrugFinance_id\",
					dus.DocumentUcStr_godnDate as \"DocumentUcStr_godnDate\",
					farm.Lpu_id as \"Lpu_id\"
				from
					v_WhsDocumentUcInventDrug wduid
					inner join v_WhsDocumentUcInvent wdui  on wdui.WhsDocumentUcInvent_id = wduid.WhsDocumentUcInvent_id
					left join lateral( Select lpu_id from v_OrgFarmacyIndex farm
						where  farm.Storage_id = wdui.Storage_id
							and farm.Org_id = wdui.Org_id limit 1) farm on true
					left join lateral (Select dus.DocumentUcStr_id,
								dus.DrugNds_id,
								dus.DocumentUcStr_IsNDS,
								dus.DocumentUcStr_Price,
								dus.DocumentUcStr_EdCount,
								dus.DocumentUcStr_Sum,
								DocumentUcStr_SumNds,
								dus.DocumentUcStr_Ser,
								dus.PrepSeries_id,
								dus.DocumentUcStr_CertNum,
								dus.DocumentUcStr_CertOrg,
								dus.DrugLabResult_Name,
								dus.DocumentUcStr_CertDate,
								dus.DocumentUcStr_CertGodnDate,
								dus.DrugFinance_id,
								dus.DocumentUcStr_Count,
								dus.DocumentUcStr_godnDate,
								a.Lpu_id
					 from  DrugShipmentLink dsl
						inner join v_DocumentUcStr dus on dus.DocumentUcStr_id = dsl.DocumentUcStr_id and dus.DocumentUcStr_Price = wduid.WhsDocumentUcInventDrug_Cost
						inner join v_DocumentUc du on du.DocumentUc_id = dus.DocumentUc_id and du.Org_id = wdui.org_id 
						left join r2.attachMoByFarmacy a  on a.DocumentUcStr_id = dus.DocumentUcStr_id 
						where  dsl.DrugShipment_id = wduid.DrugShipment_id
						    and COALESCE(a.Lpu_id, 0) = COALESCE(farm.Lpu_id, 0)
						limit 1
					) dus on true
					left join lateral (
						select {$k_query} as NewCount
					) as c on true
					left join lateral (
						select c.NewCount/dus.DocumentUcStr_Count as koef
					) as k on true
				where
					wduid.WhsDocumentUcInventDrug_FactKolvo is not null and
					{$w_query}
					wduid.WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id and
					dus.DocumentUcStr_Count > 0;
			    ";
			};

			//print getDebugSQL($query, $data); exit;
			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$drug_data = $result->result('array');
			}
			if (count($drug_data) < 1) {
				$error[] = $error_msg;
			}
		}

		//получение данных общего характера
		if (count($error) == 0) {
			$query = "
				
				select DrugDocumentType_id as \"DrugDocumentType_id\" from v_DrugDocumentType where DrugDocumentType_SysNick = :DrugDocumentType_SysNick limit 1;
			";
			$common_data = $this->getFirstResultFromQuery($query, array(
				'DrugDocumentType_SysNick' => $data['DrugDocumentType_SysNick']
			));

			if (count($common_data) < 1) {
				$error[] = "Не удалось получить данные для создания документа учета.";
			}
		}

		//старт транзакции
		$this->beginTransaction();

		//сохранение документа учета
		if (count($error) == 0) {
			$save_data = array();

			$save_data['DocumentUc_id'] = null;
			$save_data['DrugDocumentStatus_id'] = $this->getObjectIdByCode('DrugDocumentStatus', 1); //1 - Новый;
			$save_data['DocumentUc_Num'] = $new_num;
			$save_data['WhsDocumentUc_id'] = $invent_data['WhsDocumentUc_id'];
			$save_data['DocumentUc_setDate'] = $current_date;
			$save_data['DocumentUc_didDate'] = $current_date;
			$save_data['DrugFinance_id'] = $invent_data['DrugFinance_id'];
			$save_data['WhsDocumentCostItemType_id'] = $invent_data['WhsDocumentCostItemType_id'];
			$save_data['Org_id'] = $invent_data['Org_id'];
			$save_data['Contragent_id'] = $invent_data['Contragent_id'];

			if ($type == 'DocOprih') {
				$save_data['DrugDocumentType_id'] = $this->getObjectIdByCode('DrugDocumentType', 12); //12 - Оприходование;
				$save_data['Contragent_tid'] = $invent_data['Contragent_id'];
				$save_data['Storage_tid'] = $invent_data['Storage_id'];
				$save_data['SubAccountType_tid'] = $this->getObjectIdByCode('SubAccountType', 1); //1 - Доступно;
			}
			if ($type == 'DokSpis') {
				$save_data['DrugDocumentType_id'] = $this->getObjectIdByCode('DrugDocumentType', 2); //
				$save_data['Contragent_sid'] = $invent_data['Contragent_id'];
				$save_data['Storage_sid'] = $invent_data['Storage_id'];
				$save_data['SubAccountType_sid'] = $this->getObjectIdByCode('SubAccountType', 1); //1 - Доступно;
			}

			$save_data['pmUser_id'] = $data['pmUser_id'];

			$doc_save_result = $this->saveObject('DocumentUc', $save_data);
			if (!empty($doc_save_result['Error_Msg'])) {
				$error[] = $doc_save_result['Error_Msg'];
			}
			if (!empty($doc_save_result['DocumentUc_id'])) {
				$doc_id = $doc_save_result['DocumentUc_id'];
			} else {
				$error[] = "Не удалось сохранить документ учета.";
			}
		}

		//сохранение списка медикаментов
		if (count($error) == 0) {
			foreach ($drug_data as $drug) {
				$drug['DocumentUcStr_id'] = null;
				$drug['DocumentUc_id'] = $doc_id;
				$drug['pmUser_id'] = $data['pmUser_id'];
				if ($type == 'DokSpis') {
					$drug['DocumentUcStr_Reason'] = "Результаты инвентаризации по прик. " . $invent_data['Order_Num'] . " от " . $invent_data['Order_Date'];
				}
				if ($type == 'DocOprih' || $type == 'DokSpis') {
					$drug['StorageZone_id'] = $invent_data['StorageZone_id'];
				}
				$result = $this->saveObject('DocumentUcStr', $drug);

				if (!empty($result['Error_Msg'])) {
					$error[] = $result['Error_Msg'];
					break;
				} else if ($type == 'DocOprih') {
					$r = $this->saveObject('DrugShipmentLink',
						array(
							'DrugShipment_id' => $drug['DrugShipment_id'],
							'DocumentUcStr_id' => $result['DocumentUcStr_id'],
							'pmUser_id' => $data['pmUser_id']
						)
					);

					if ($region == 'ufa' && $orgtype == 'farm') {
						if (!empty($drug['Lpu_id'])) {
							$query = "
								SELECT
									Error_Code as \"Error_Code\",
									Error_Message as \"Error_Message\"
								FROM r2.p_attachMoByFarmacy_ins (
									DocumentUcStr_id := :DocumentUcStr_id,
									Lpu_id := :Lpu_id
								);
							";

							$queryParams = array(
								'DocumentUcStr_id' => $result['DocumentUcStr_id'],
								'Lpu_id' => $drug['Lpu_id']
							);
							//print getDebugSQL($query, $queryParams);
							$result = $this->getFirstRowFromQuery($query, $queryParams);
							if ($result && is_array($result)) {
								$result['success'] = true;
							} else {
								return array('Error_Msg' => 'При сохранении произошла ошибка');
							}
						}
					};
				}
			}
		}

		//вывод ошибок
		if (count($error) > 0) {
			$doc_save_result = array('Error_Msg' => $error[0]);
			$this->rollbackTransaction();
			return $doc_save_result;
		}

		//коммит транзакции
		$this->commitTransaction();

		//возвращаем тип документа для открытия формы
		if ($type == 'DocOprih') {
			$doc_save_result['DrugDocumentType_Code'] = 12; //12 - Оприходование;
		}
		if ($type == 'DokSpis') {
			$doc_save_result['DrugDocumentType_Code'] = 2; //2 - Документ списания медикаментов;
		}


		return $doc_save_result;
	}


}