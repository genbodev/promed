<?php defined('BASEPATH') or die ('No direct script access allowed');

class WhsDocumentUcInvent_model extends swPgModel {
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
	}

	/**
	 * Загрузка
	 */
	function load($data) {
		$q = "
			select
				wdui.WhsDocumentUc_id as \"WhsDocumentUc_id\",
				wdui.WhsDocumentUcInvent_id as \"WhsDocumentUcInvent_id\",
				wdui.WhsDocumentUc_pid as \"WhsDocumentUc_pid\",
				wdui.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				wdui.WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
				wdui.WhsDocumentUc_Date as \"WhsDocumentUc_Date\",
				o.Org_id as \"Org_id\",
				o.Org_Name as \"Org_Name\",
				c.Contragent_id as \"Contragent_id\",
				c.Contragent_Name as \"Contragent_Name\",
				s.Storage_id as \"Storage_id\",
				s.Storage_Name as \"Storage_Name\",
				sz.StorageZone_Address as \"StorageZone_Name\",
				wdui.StorageZone_id as \"StorageZone_id\",
				df.DrugFinance_id as \"DrugFinance_id\",
				df.DrugFinance_Name as \"DrugFinance_Name\",
				wdcit.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				wdcit.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
				wdst.WhsDocumentStatusType_Code as \"WhsDocumentStatusType_Code\"
			from
				dbo.v_WhsDocumentUcInvent wdui 

				left join dbo.Org o  on o.Org_id = wdui.Org_id

				left join dbo.v_Contragent c  on c.Contragent_id = wdui.Contragent_id

				left join dbo.v_Storage s  on s.Storage_id = wdui.Storage_id

				left join dbo.v_StorageZone sz  on sz.StorageZone_id = wdui.StorageZone_id

				left join dbo.v_DrugFinance df  on df.DrugFinance_id = wdui.DrugFinance_id

				left join dbo.v_WhsDocumentCostItemType wdcit  on wdcit.WhsDocumentCostItemType_id = wdui.WhsDocumentCostItemType_id

				left join dbo.v_WhsDocumentStatusType wdst  on wdst.WhsDocumentStatusType_id = wdui.WhsDocumentStatusType_id

			where
				WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id
		";
		$r = $this->db->query($q, array('WhsDocumentUcInvent_id' => $data['WhsDocumentUcInvent_id']));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Загрузка данных приказа на проведение инвентаризации
	 */
	function loadWhsDocumentUcInventOrder($data) {
		$q = "
			select
				WhsDocumentUc_id as \"WhsDocumentUc_id\",
				WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
				WhsDocumentType_id as \"WhsDocumentType_id\",
				WhsDocumentUc_Date as \"WhsDocumentUc_Date\",
				WhsDocumentUc_Sum as \"WhsDocumentUc_Sum\",
				WhsDocumentStatusType_id as \"WhsDocumentStatusType_id\",
				Org_aid as \"Org_aid\",
				null as \"DrugFinance_id\",
				null as \"WhsDocumentCostItemType_id\"
			from
				dbo.v_WhsDocumentUc 

			where
				WhsDocumentUc_id = :WhsDocumentUc_id
		";
		$r = $this->db->query($q, array('WhsDocumentUc_id' => $data['WhsDocumentUc_id']));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadList($filter) {
		$where = array();
		$params = array();
        $with_clause = "";

		//в списке отображаются только те ведомости, которые соответствую подписанным приказам
		$where[] = '(p_wdst.WhsDocumentStatusType_Code = 2::varchar)';

        //организация пользователя
        $params['UserOrg_id'] = !empty($filter['session']['org_id']) ? $filter['session']['org_id'] : null;

		if (isset($filter['WhsDocumentUc_DateRange']) && count($filter['WhsDocumentUc_DateRange']) == 2 && !empty($filter['WhsDocumentUc_DateRange'][0])) {
			$where[] = 'wdui.WhsDocumentUc_Date between :WhsDocumentUc_Date1 and :WhsDocumentUc_Date2';
			$params['WhsDocumentUc_Date1'] = $filter['WhsDocumentUc_DateRange'][0];
			$params['WhsDocumentUc_Date2'] = $filter['WhsDocumentUc_DateRange'][1];
		}
		if (isset($filter['DrugFinance_id']) && $filter['DrugFinance_id']) {
			$where[] = 'wdui.DrugFinance_id = :DrugFinance_id';
			$params['DrugFinance_id'] = $filter['DrugFinance_id'];
		}
		if (isset($filter['WhsDocumentCostItemType_id']) && $filter['WhsDocumentCostItemType_id']) {
			$where[] = 'wdui.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id';
			$params['WhsDocumentCostItemType_id'] = $filter['WhsDocumentCostItemType_id'];
		}
		if (isset($filter['WhsDocumentStatusType_id']) && $filter['WhsDocumentStatusType_id']) {
			$where[] = 'wdui.WhsDocumentStatusType_id = :WhsDocumentStatusType_id';
			$params['WhsDocumentStatusType_id'] = $filter['WhsDocumentStatusType_id'];
		}
		if (!empty($filter['Contragent_id'])) {
			$where[] = "wdui.Contragent_id = :Contragent_id";
			$params['Contragent_id'] = $filter['Contragent_id'];
		}
		if (!empty($filter['Storage_Name'])) {
			$where[] = "s.Storage_Name iLIKE '%'||:Storage_Name||'%'";

			$params['Storage_Name'] = $filter['Storage_Name'];
		}
        if (!empty($filter['LpuSection_id'])) {
            $where[] = "ls.LpuSection_id = :LpuSection_id";
            $params['LpuSection_id'] = $filter['LpuSection_id'];
        }
        if (!empty($filter['LpuBuilding_id'])) {
            $where[] = "lb.LpuBuilding_id = :LpuBuilding_id";
            $params['LpuBuilding_id'] = $filter['LpuBuilding_id'];
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
			if ($filter['ARMType'] == 'merch') { //АРМ Товароведа
                if (!empty($filter['session']['orgtype']) && $filter['session']['orgtype'] == 'lpu') { //организация пользователя - МО
					if (!empty($filter['MedService_id'])) {
						//получение списка складов службы и их дочерних складов
						$query = "
							with recursive ms_storage_list as (
								select
									i_ssl.Storage_id,
									null as Storage_pid
								from	
									v_StorageStructLevel i_ssl 

								where
									i_ssl.MedService_id = :MedService_id
							),
							 storage_tree (Storage_id, Storage_pid) as (
								select
									i_s.Storage_id,
									cast(i_s.Storage_pid as bigint) as Storage_pid
								from
									ms_storage_list i_s
								union all
								select
									i_s.Storage_id,
									i_s.Storage_pid
								from
									v_Storage i_s 

									inner join storage_tree i_tr on i_s.Storage_pid = i_tr.Storage_id
							)
							select
								st.Storage_id as \"Storage_id\"
							from
								storage_tree st
						";
						$storage_list = $this->queryList($query, array(
							'MedService_id' => $filter['MedService_id']
						));
						if (count($storage_list) > 0) {
							$where[] = "
								wdui.Storage_id in (".join(',', $storage_list).")
							";
						}
					}

                } else {
                    $with_clause = "
                        with recursive OrgTree (Org_id) as (
                            select
                                o.Org_id
                            from
                                Org o
                            where
                                o.Org_id = :UserOrg_id
                            union all
                            select
                                o.Org_id
                            from
                                Org o
                                inner join OrgTree ot on ot.Org_id = o.Org_pid
                        )
                    ";
                    $where[] = "(
                            wdui.Org_id = :UserOrg_id or
                            (
                                p_wdu.Org_aid = :UserOrg_id and
                                wdui.Org_id in (select Org_id from OrgTree)
                            )
                        )
                    ";
                }
			}
			if ($filter['ARMType'] == 'adminllo') { //АРМ Администратора ЛЛО
				$where[] = "
					p_wdu.Org_aid in (
						select
							i_ms.Org_id
						from
							v_MedService i_ms 

							left join v_MedServiceType i_mst  on i_mst.MedServiceType_id = i_ms.MedServiceType_id

						where
							i_mst.MedServiceType_SysNick in ('adminllo', 'spesexpertllo') and
							i_ms.Org_id is not null
					)
				";

			}
			if ($filter['ARMType'] == 'lpupharmacyhead') { //АРМ Заведующего аптекой МО
				$where[] = "
					p_wdu.Org_aid = :UserOrg_id
				";
			}
		}

		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'where '.$where_clause;
		}

		$query = "
		    {$with_clause}
			select
				wdui.WhsDocumentUc_id as \"WhsDocumentUc_id\",
				wdui.WhsDocumentUcInvent_id as \"WhsDocumentUcInvent_id\",
				wdui.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				wdst.WhsDocumentStatusType_Code as \"WhsDocumentStatusType_Code\",
				wdst.WhsDocumentStatusType_Name as \"WhsDocumentStatusType_Name\",
				to_char(wdui.WhsDocumentUc_Date, 'DD.MM.YYYY') as \"WhsDocumentUc_Date\",

				wdui.WhsDocumentUc_Sum as \"WhsDocumentUc_Sum\",
				p_wdu.WhsDocumentUc_Name as \"WhsDocumentUc_pName\",
				o.Org_Name as \"Org_Name\",
				s.Storage_Name as \"Storage_Name\",
				DrugFinance_Name as \"DrugFinance_Name\",
				wdcit.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
				row_cnt.cnt  as \"Drug_Count\",
				ls.LpuSection_Name as \"LpuSection_Name\",
				case 
					when ot.OrgType_SysNick = 'lpu' then lb.LpuBuilding_Name
					else os.OrgStruct_Name
				end as \"LpuBuilding_Name\",
				sz.StorageZone_Address as \"StorageZone_Name\",
				docs.DocumentUc_id as \"kolDoc\"
			from
				dbo.v_WhsDocumentUcInvent wdui 

				left join dbo.v_WhsDocumentUc p_wdu  on p_wdu.WhsDocumentUc_id = wdui.WhsDocumentUc_pid

				left join dbo.v_WhsDocumentStatusType p_wdst  on p_wdst.WhsDocumentStatusType_id = p_wdu.WhsDocumentStatusType_id

				left join dbo.Org o  on o.Org_id = wdui.Org_id

				left join dbo.v_OrgType ot  on ot.OrgType_id = o.OrgType_id

				left join dbo.v_Storage s  on s.Storage_id = wdui.Storage_id

				left join dbo.v_StorageZone sz  on sz.StorageZone_id = wdui.StorageZone_id

				left join dbo.v_Contragent c  on c.Contragent_id = wdui.Contragent_id

				left join dbo.v_DrugFinance df  on df.DrugFinance_id = wdui.DrugFinance_id

				left join dbo.v_WhsDocumentCostItemType wdcit  on wdcit.WhsDocumentCostItemType_id = wdui.WhsDocumentCostItemType_id

				left join dbo.v_WhsDocumentStatusType wdst  on wdst.WhsDocumentStatusType_id = wdui.WhsDocumentStatusType_id

				LEFT JOIN LATERAL (

					select
						count(wduid.WhsDocumentUcInventDrug_id) as cnt
					from
						v_WhsDocumentUcInventDrug wduid 

					where
						wduid.WhsDocumentUcInvent_id = wdui.WhsDocumentUcInvent_id
				) row_cnt ON true
				LEFT JOIN LATERAL (

					select
						i_ls.LpuSection_id,
						i_ls.LpuSection_Name
					from
						v_StorageStructLevel i_ssl 

						left join v_MedService i_ms  on i_ms.MedService_id = i_ssl.MedService_id

						left join v_LpuSection i_ls  on i_ls.LpuSection_id = COALESCE(i_ssl.LpuSection_id, i_ms.LpuSection_id)


					where
						i_ssl.Storage_id = wdui.Storage_id
					order by
						i_ssl.LpuSection_id desc, i_ms.LpuSection_id desc
                    limit 1
				) ls ON true
				LEFT JOIN LATERAL (

					select 
						i_lb.LpuBuilding_id,
						i_lb.LpuBuilding_Name
					from
						v_StorageStructLevel i_ssl 

						left join v_MedService i_ms  on i_ms.MedService_id = i_ssl.MedService_id

						left join v_LpuBuilding i_lb  on i_lb.LpuBuilding_id = COALESCE(i_ssl.LpuBuilding_id, i_ms.LpuBuilding_id)


					where
						i_ssl.Storage_id = wdui.Storage_id
					order by
						i_ssl.LpuBuilding_id desc, i_ms.LpuBuilding_id desc
                    limit 1
				) lb ON true
				LEFT JOIN LATERAL (

					select 
						i_ssl.OrgStruct_id,
						i_os.OrgStruct_Name
					from
						v_StorageStructLevel i_ssl 

						left join v_OrgStruct i_os  on i_os.OrgStruct_id = i_ssl.OrgStruct_id

					where
						i_ssl.Storage_id = wdui.Storage_id
					order by
						i_ssl.OrgStruct_id desc
                    limit 1
				) os ON true
				LEFT JOIN LATERAL (

					select 
						i_doc.DocumentUc_id
					from
						v_DocumentUc i_doc 

					where
						i_doc.DrugDocumentStatus_id = 1 and 
						(
							(
								i_doc.DrugDocumentType_id in (10,17,22,25,26) and 
								(i_doc.Storage_sid = wdui.Storage_id or i_doc.Storage_tid = wdui.Storage_id)
							)
							or
							(
								i_doc.DrugDocumentType_id = 15 and i_doc.Storage_sid = wdui.Storage_id
							)
						)
                     limit 1
				) docs ON true
			{$where_clause}
		";
			//echo getDebugSQL($query, $params);die();
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка приказов на проведение инвентаризации
	 */
	function loadWhsDocumentUcInventOrderList($filter) {
		$where = array();
		$p = array();

		$where[] = "wdt.WhsDocumentType_Code = '20'"; //20 - Приказ о проведении инвентаризации
		if (!empty($filter['Year'])) {
			$where[] = "(date_part('YEAR', wdu.WhsDocumentUc_Date) = :Year)";
			$p['Year'] = $filter['Year'];
		}
		if (!empty($filter['Org_aid'])) {
			$where[] = 'wdu.Org_aid = :Org_aid';
			$p['Org_aid'] = $filter['Org_aid'];
		}
		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'where '.$where_clause;
		}

		$q = "
			select
				wdu.WhsDocumentUc_id as \"WhsDocumentUc_id\",
				wdu.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				wdu.WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
				to_char(wdu.WhsDocumentUc_Date, 'DD.MM.YYYY') as \"WhsDocumentUc_Date\",

				wdst.WhsDocumentStatusType_Code as \"WhsDocumentStatusType_Code\",
				wdst.WhsDocumentStatusType_Name as \"WhsDocumentStatusType_Name\",
				o.Org_Name as \"Org_Name\",
				inv.DrugFinance_Name as \"DrugFinance_Name\",
				inv.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
				doc.cnt as \"cnt\",
				doc.approved_cnt as \"approved_cnt\",
				(
					case
						when doc.cnt > 0 and doc.cnt = doc.approved_cnt then 'Утверждены'
						else null
					end
				) as \"WhsDocumentUc_Result\"
			from
				v_WhsDocumentUc wdu 

				left join dbo.v_WhsDocumentType wdt  on wdt.WhsDocumentType_id = wdu.WhsDocumentType_id

				left join dbo.v_WhsDocumentStatusType wdst  on wdst.WhsDocumentStatusType_id = wdu.WhsDocumentStatusType_id

				left join dbo.v_Org o  on o.Org_id = wdu.Org_aid

				LEFT JOIN LATERAL (

					select
						count(i_wdu.WhsDocumentUc_id) as cnt,
						sum(case when i_wdst.WhsDocumentStatusType_Code = 2::text	then 1 else 0 end) as approved_cnt -- 2 - Действующий
					from
						v_WhsDocumentUc i_wdu 

						left join dbo.v_WhsDocumentStatusType i_wdst  on i_wdst.WhsDocumentStatusType_id = i_wdu.WhsDocumentStatusType_id

					where
						i_wdu.WhsDocumentUc_pid = wdu.WhsDocumentUc_id
				) doc ON TRUE
				LEFT JOIN LATERAL (Select i.WhsDocumentCostItemType_id, WhsDocumentCostItemType_Name, fin.DrugFinance_Name from v_WhsDocumentUcInvent i 


					left join dbo.v_WhsDocumentCostItemType wdcit  on wdcit.WhsDocumentCostItemType_id = i.WhsDocumentCostItemType_id

					left join DrugFinance fin  on fin.DrugFinance_id = i.DrugFinance_id

					where  i.WhsDocumentUc_pid = wdu.WhsDocumentUc_id
                    limit 1
                    ) inv ON true
$where_clause
		";
		//print getDebugSQL($q, $p); exit;
		$result = $this->db->query($q, $filter);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка госконтрактов
	 */
	function loadWhsDocumentSupplyInventList($filter) {
		$where = array();
		$p = array();

		if (!empty($filter['WhsDocumentUc_id'])) {
			$where[] = 'wdsi.WhsDocumentUc_id = :WhsDocumentUc_id';
			$p['WhsDocumentUc_id'] = $filter['WhsDocumentUc_id'];
		}
		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'where '.$where_clause;
		}

		$q = "
			select
				wdsi.WhsDocumentSupplyInvent_id as \"WhsDocumentSupplyInvent_id\",
				wdsi.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
				wds.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				to_char(wds.WhsDocumentUc_Date, 'DD.MM.YYYY') as \"WhsDocumentUc_Date\",

				s_o.Org_Nick as \"Org_sid_Nick\",
				wds.WhsDocumentUc_Name as \"WhsDocumentUc_Name\"
			from
				v_WhsDocumentSupplyInvent wdsi 

				left join dbo.v_WhsDocumentSupply wds  on wds.WhsDocumentSupply_id = wdsi.WhsDocumentSupply_id

				left join dbo.v_Org s_o  on s_o.Org_id = wds.Org_sid

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
	 * Загрузка списка ведомостей
	 */
	function loadWhsDocumentUcInventList($filter) {
		$where = array();
		$p = array();

		if (!empty($filter['WhsDocumentUc_pid'])) {
			$where[] = 'wdui.WhsDocumentUc_pid = :WhsDocumentUc_pid';
			$p['WhsDocumentUc_pid'] = $filter['WhsDocumentUc_pid'];
		}
		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'where '.$where_clause;
		}

		$q = "
			select
				wdui.WhsDocumentUc_id as \"WhsDocumentUc_id\",
				wdui.WhsDocumentUcInvent_id as \"WhsDocumentUcInvent_id\",
				wdui.WhsDocumentUc_pid as \"WhsDocumentUc_pid\",
				wdui.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				wdui.WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
				wdui.WhsDocumentType_id as \"WhsDocumentType_id\",
				to_char(wdui.WhsDocumentUc_Date, 'DD.MM.YYYY') as \"WhsDocumentUc_Date\",

				to_char(wdui.WhsDocumentUcInvent_begDT, 'DD.MM.YYYY') as \"WhsDocumentUcInvent_begDT\",

				wdui.WhsDocumentStatusType_id as \"WhsDocumentStatusType_id\",
				wdst.WhsDocumentStatusType_Code as \"WhsDocumentStatusType_Code\",
				wdst.WhsDocumentStatusType_Name as \"WhsDocumentStatusType_Name\",
				wdui.Contragent_id as \"Contragent_id\",
				c.Contragent_Name as \"Contragent_Name\",
				wdui.Storage_id as \"Storage_id\",
				s.Storage_Name as \"Storage_Name\",
				wdui.StorageZone_id as \"StorageZone_id\",
				sz.StorageZone_Address as \"StorageZone_Name\",
				wdui.Org_id as \"Org_id\",
				o.Org_Name as \"Org_Name\",
				wdui.DrugFinance_id as \"DrugFinance_id\",
				wdui.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\"
			from
				v_WhsDocumentUcInvent wdui 

				left join dbo.v_WhsDocumentStatusType wdst  on wdst.WhsDocumentStatusType_id = wdui.WhsDocumentStatusType_id

				left join dbo.v_Contragent c  on c.Contragent_id = wdui.Contragent_id

				left join dbo.v_Storage s  on s.Storage_id = wdui.Storage_id

				left join dbo.v_StorageZone sz  on sz.StorageZone_id = wdui.StorageZone_id

				left join dbo.Org o  on o.Org_id = wdui.Org_id

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
	* Получение номера описи
	*/
	function loadWhsDocumentUcInventDrugInventoryNum($data)
	{
		$params = array(
			'WhsDocumentUcInventDrugInventory_id' => $data['WhsDocumentUcInventDrugInventory_id']
		);
		$query = "
			select wduidi.WhsDocumentUcInventDrugInventory_InvNum as \"WhsDocumentUcInventDrugInventory_InvNum\"
			from v_WhsDocumentUcInventDrugInventory wduidi 

			where wduidi.WhsDocumentUcInventDrugInventory_id = :WhsDocumentUcInventDrugInventory_id
		";
		$result = $this->db->query($query,$params);
		if(is_object($result))
		{
			return $result->result('array');
		}
		else
			return false;
	}

	/**
	 * Загрузка списка описей
	 */
	function loadWhsDocumentUcInventDrugInventoryNumList($filter) {

		$q = "
			select
				wduidi.WhsDocumentUcInventDrugInventory_id as \"WhsDocumentUcInventDrugInventory_id\",
				wduidi.WhsDocumentUcInventDrugInventory_InvNum as \"WhsDocumentUcInventDrugInventory_InvNum\",
				COALESCE(wdst.WhsDocumentStatusType_Name,'') as \"WhsDocumentStatusType_Name\",

				--'' as StorageWork_Person
				StorageWork_Person as \"StorageWork_Person\"
			from
				v_WhsDocumentUcInventDrugInventory wduidi 

				left join v_WhsDocumentStatusType wdst  on wdst.WhsDocumentStatusType_id = wduidi.WhsDocumentStatusType_id

				LEFT JOIN LATERAL

				(
					SELECT string_agg(i_ps.Person_SurName , '. ') AS StorageWork_Person
					FROM 
						WhsDocumentUcInventDrug WDUID
						left join v_DocumentUcStorageWork i_dusw  on i_dusw.WhsDocumentUcInventDrug_id = WDUID.WhsDocumentUcInventDrug_id

						left join v_PersonState i_ps  on i_ps.Person_id = i_dusw.Person_eid

					WHERE WhsDocumentUcInventDrugInventory_id = wduidi.WhsDocumentUcInventDrugInventory_id
				) v ON true
			where wduidi.WhsDocumentUcInvent_id =:WhsDocumentUcInvent_id
		";
		$result = $this->db->query($q, $filter);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение номера описи инв. ведомости
	 */
	function saveWhsDocumentUcInventDrugInventoryNum($data) {

		$procedure = 'p_WhsDocumentUcInventDrugInventory_ins';
//		$set = 'set @Res = null;';
		//$set = '';

		if (!empty($data['WhsDocumentUcInventDrugInventory_id']) && $data['WhsDocumentUcInventDrugInventory_id'] > 0 ) {
			$procedure = 'p_WhsDocumentUcInventDrugInventory_upd';
//			$set = "set @Res = :WhsDocumentUcInventDrugInventory_id;";
		}

		//Проверим на уникальность
		$and = '';
		if(!empty($data['WhsDocumentUcInventDrugInventory_id']) && $data['WhsDocumentUcInventDrugInventory_id'] > 0)
			$and = ' and WhsDocumentUcInventDrugInventory_id <> :WhsDocumentUcInventDrugInventory_id';
		$query_check = "
			select WhsDocumentUcInventDrugInventory_id as \"WhsDocumentUcInventDrugInventory_id\"
			from v_WhsDocumentUcInventDrugInventory
			where WhsDocumentUcInventDrugInventory_InvNum = :WhsDocumentUcInventDrugInventory_InvNum
			and WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id
			{$and}
			limit 1
		";
		$data["WhsDocumentUcInventDrugInventory_InvNum"] = (string)$data["WhsDocumentUcInventDrugInventory_InvNum"];
		$result_check = $this->db->query($query_check,$data);
		if(is_object($result_check))
		{
			$result_check = $result_check->result('array');
			if(is_array($result_check) && count($result_check) > 0)
			{
				$result = array(array('Error_Msg' => 'Данный номер описи уже существует'));
				if(!empty($result_check[0]['WhsDocumentUcInventDrugInventory_id'])){
					$result[0]['WhsDocumentUcInventDrugInventory_id'] = $result_check[0]['WhsDocumentUcInventDrugInventory_id'];
				}
				return $result;
			}
		}
		$q = "
			select WhsDocumentUcInventDrugInventory_id as \"WhsDocumentUcInventDrugInventory_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from dbo." . $procedure . "(
				WhsDocumentUcInventDrugInventory_id := ". ((!empty($data['WhsDocumentUcInventDrugInventory_id']) && $data['WhsDocumentUcInventDrugInventory_id'] > 0 ) ? ":WhsDocumentUcInventDrugInventory_id":"NULL") .",
				WhsDocumentUcInventDrugInventory_InvNum := :WhsDocumentUcInventDrugInventory_InvNum,
				WhsDocumentUcInvent_id := :WhsDocumentUcInvent_id,
				WhsDocumentStatusType_id := 1,
				pmUser_id := :pmUser_id);


		";
		//print getDebugSQL ($q, $data); exit;
		$r = $this->db->query($q, $data);
		if ( is_object($r) ) {
			$result = $r->result('array');
		} else {
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Загрузка списка медикаментов ведомости
	 */
	function loadWhsDocumentUcInventDrugList($filter) {
		$this->load->model('DocumentUc_model', 'DocumentUc_model');

		$where = array();
		$p = array();

		$filter['DefaultGoodsUnit_id'] = $this->DocumentUc_model->getDefaultGoodsUnitId();

		$where[] = '((wduid.WhsDocumentUcInventDrug_Kolvo is not null and wduid.WhsDocumentUcInventDrug_Kolvo>0) OR (wduid.WhsDocumentUcInventDrug_FactKolvo is not null and wduid.WhsDocumentUcInventDrug_FactKolvo>0))';
		if (!empty($filter['WhsDocumentUcInvent_id'])) {
			$where[] = 'wduid.WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id';
			$p['WhsDocumentUcInvent_id'] = $filter['WhsDocumentUcInvent_id'];
		}
		if (!empty($filter['Drug_Name'])) {
			$where[] = "d.Drug_Name iLIKE :Drug_Name||'%'";

			$p['Drug_Name'] = $filter['Drug_Name'];
		}
		if (!empty($filter['PersonWork_eid'])) {
			$where[] = "s_work.PersonWork_eid = :PersonWork_eid";
			$p['PersonWork_eid'] = $filter['PersonWork_eid'];
		}
		if (!empty($filter['WhsDocumentStatusType_id'])) {
			$where[] = "wduid.WhsDocumentStatusType_id = :WhsDocumentStatusType_id";
			$p['WhsDocumentStatusType_id'] = $filter['WhsDocumentStatusType_id'];
		}
		if (!empty($filter['StorageZone_id'])) {
			$where[] = "wdui.StorageZone_id = :StorageZone_id";
			$p['StorageZone_id'] = $filter['StorageZone_id'];
		}
		if (!empty($filter['GoodsUnit_id'])) {
			$where[] = "gu.GoodsUnit_id = :GoodsUnit_id";
			$p['GoodsUnit_id'] = $filter['GoodsUnit_id'];
		}
		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'where '.$where_clause;
		}

		$q = "
			select
				wduid.WhsDocumentUcInventDrug_id as \"WhsDocumentUcInventDrug_id\",
				COALESCE(wduid.Server_id, 0) as \"Server_id\",

				wdui.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				COALESCE(wduidi.WhsDocumentUcInventDrugInventory_InvNum, '0') as \"WhsDocumentUcInventDrugInventory_InvNum\",

				wdst.WhsDocumentStatusType_Name as \"WhsDocumentStatusType_Name\",
				d.Drug_Name as \"Drug_Name\",
				ps.PrepSeries_Ser as \"PrepSeries_Ser\",
				COALESCE(isdef.YesNo_Code, 0) as \"PrepSeries_isDefect\",

				ds.DrugShipment_Name as \"DrugShipment_Name\",
				wds.WhsDocumentUc_Name as \"WhsDocumentSupply_Name\",
				sat.SubAccountType_Name as \"SubAccountType_Name\",
				gu.GoodsUnit_Name as \"GoodsUnit_Name\",
				wduid.WhsDocumentUcInventDrug_Kolvo as \"WhsDocumentUcInventDrug_Kolvo\",
				wduid.WhsDocumentUcInventDrug_FactKolvo as \"WhsDocumentUcInventDrug_FactKolvo\",
				wduid.WhsDocumentUcInventDrug_Sum as \"WhsDocumentUcInventDrug_Sum\",
				wduid.WhsDocumentUcInventDrug_Cost as \"WhsDocumentUcInventDrug_Cost\",
				s_work.StorageWork_id as \"StorageWork_id\",
				s_work.StorageWork_Person as \"StorageWork_Person\",
				s_work.StorageWork_Comment as \"StorageWork_Comment\",
				s_work.StorageWork_endDate as \"StorageWork_endDate\",
				COALESCE(sz.StorageZone_Address, 'Без места хранения') as \"StorageZone_Name\"

			from
				v_WhsDocumentUcInventDrug wduid 

				left join dbo.v_WhsDocumentUcInvent wdui  on wdui.WhsDocumentUcInvent_id = wduid.WhsDocumentUcInvent_id

				left join dbo.v_WhsDocumentStatusType wdst  on wdst.WhsDocumentStatusType_id = wduid.WhsDocumentStatusType_id

				left join dbo.v_StorageZone sz  on sz.StorageZone_id = wdui.StorageZone_id

				left join rls.v_Drug d  on d.Drug_id = wduid.Drug_id

				left join rls.v_PrepSeries ps  on ps.PrepSeries_id = wduid.PrepSeries_id

				left join v_YesNo isdef  on isdef.YesNo_id = ps.PrepSeries_isDefect

				left join dbo.v_DrugShipment ds  on ds.DrugShipment_id = wduid.DrugShipment_id

				left join dbo.v_SubAccountType sat  on sat.SubAccountType_id = wduid.SubAccountType_id

				left join dbo.v_GoodsUnit gu  on gu.GoodsUnit_id = COALESCE(wduid.GoodsUnit_id, 
                :DefaultGoodsUnit_id
                )


				left join dbo.v_WhsDocumentSupply wds  on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id

				left join dbo.v_WhsDocumentUcInventDrugInventory wduidi  on wduidi.WhsDocumentUcInventDrugInventory_id = wduid.WhsDocumentUcInventDrugInventory_id

				LEFT JOIN LATERAL (

                    select 
                    	i_dusw.DocumentUcStorageWork_id as StorageWork_id,
                        (
                            COALESCE(i_ps.Person_SurName, '')||

                            COALESCE(' '||i_ps.Person_FirName, '')||

                            COALESCE(' '||i_ps.Person_SecName, '')

                        ) as StorageWork_Person,
                        i_dusw.DocumentUcStorageWork_Comment as StorageWork_Comment,
                        to_char(i_dusw.DocumentUcStorageWork_endDate, 'DD.MM.YYYY')||' '||to_char(i_dusw.DocumentUcStorageWork_endDate, 'HH24:MI:SS') as StorageWork_endDate,


                        i_pw.PersonWork_id as PersonWork_eid
                    from
                        v_DocumentUcStorageWork i_dusw 

                        left join v_PersonState i_ps  on i_ps.Person_id = i_dusw.Person_eid

                        left join v_PersonWork i_pw  on i_pw.Person_id = i_dusw.Person_eid and i_pw.Post_id = i_dusw.Post_eid and i_pw.Org_id = 
                        :Org_id

                    where
                        i_dusw.WhsDocumentUcInventDrug_id = wduid.WhsDocumentUcInventDrug_id
                    order by
                        i_dusw.DocumentUcStorageWork_id desc
                    limit 1
                ) s_work ON true
			$where_clause
		";
		$filter['Org_id'] = $filter['session']['org_id'];
		//echo getDebugSQL($q, $filter);die();
		$result = $this->db->query($q, $filter);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка складов
	 */
	function loadStorageList($filter) {
		$join = array();
		$where = array();
		$params = array();

        $query_type = null;

        if (!empty($filter['Org_aid'])) {
            $query = "
                select (
                         select OrgType_SysNick
                         from Org o
                              left join OrgType ot on ot.OrgType_id = o.OrgType_id
                         where Org_id =:Org_id
                         limit 1
                       ) as \"OrgType_SysNick\",
                       (
                         select count(mst.MedServiceType_id) as cnt
                         from (
                                select ms.MedServiceType_id
                                from OrgStruct os
                                     left join MedService ms on ms.OrgStruct_id = os.OrgStruct_id
                                where os.Org_id =:Org_id
                                union
                                select ms.MedServiceType_id
                                from MedService ms
                                where ms.Org_id =:Org_id
                              ) p
                              inner join MedServiceType mst on mst.MedServiceType_id = p.MedServiceType_id
                         where mst.MedServiceType_SysNick = 'adminllo'
                       ) as \"AdminLLO_Cnt\"
            ";
            $org_data = $this->getFirstRowFromQuery($query, array(
                'Org_id' => $filter['Org_aid']
            ));
			if ($org_data === false) {
				return false;
			}

            if ($org_data['AdminLLO_Cnt'] > 0) { //Если приказ создан  в организации, в структуре которой есть служба «Администратор ЛЛО»
                $query_type = "lpu_admin";
            } else if ($org_data['OrgType_SysNick'] == 'lpu') { //Если приказ создан в организации c типом МО
                $query_type = "lpu";
            }
        }

        if(!empty($filter['withStorageZones'])){
        	$join[] = " 
        		left join v_StorageZone sz  on sz.Storage_id = ssl.Storage_id

        		left join v_StorageUnitType sut  on sz.StorageUnitType_id = sut.StorageUnitType_id

        	";
        	$szName = " 
        		,rtrim(cast(ssl.Storage_id as varchar(20)) || cast(sz.StorageZone_id as varchar(20))) as \"GridKey\"
        		,sz.StorageZone_id
        		,rtrim(COALESCE(sz.StorageZone_Address,'') || ' ' ||COALESCE(sut.StorageUnitType_Name,'')) as \"StorageZone_Name\"

        	";
        } else {
        	$szName = " ,ssl.Storage_id as \"GridKey\"";
        }

        switch($query_type) {
            case 'lpu_admin': //Если приказ создан  в организации, в структуре которой есть служба «Администратор ЛЛО»
            case 'lpu': //Если приказ создан в организации c типом МО

                //фильтр по организации
                if ($query_type == 'lpu_admin') {
                    $where[] = "
                        o.Org_id in (
                            select
                                Org_id
                            from
                                v_Contragent c 

                                left join v_ContragentType ct  on ct.ContragentType_id = c.ContragentType_id

                            where
                                ContragentType_SysNick in ('store', 'apt') -- store - Региональный склад; apt - Аптека.
                        )
                    ";
                } else {
                    $where[] = "o.Org_id = :UserOrg_id";
                    $params['UserOrg_id'] = !empty($filter['session']['org_id']) ? $filter['session']['org_id'] : null; //организация пользователя из сессии
                }

                $join[] = "
                    LEFT JOIN LATERAL (

                        select
                            sum(i_dor.DrugOstatRegistry_Kolvo) as Ost_Cnt
                        from
                            v_DrugOstatRegistry i_dor 

                        where
                            i_dor.SubAccountType_id = 1 and
                            i_dor.Storage_id = s.Storage_id and
                            i_dor.Org_id = o.Org_id and
                            (:DrugFinance_id is null or i_dor.DrugFinance_id = :DrugFinance_id) and
                            (:WhsDocumentCostItemType_id is null or i_dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id)
                    ) ost ON true
                ";
                $where[] = "
                    (
                        (
                            (
                                s.Storage_begDate is null or s.Storage_begDate <= dbo.tzGetDate()
                            ) and (
                                s.Storage_endDate is null or s.Storage_endDate >= dbo.tzGetDate()
                            )
                        ) or (
                            s.Storage_endDate < dbo.tzGetDate() and
                            ost.Ost_Cnt > 0
                        )
                    )
                ";
                $params['DrugFinance_id'] = $filter['DrugFinance_id'];
                $params['WhsDocumentCostItemType_id'] = $filter['WhsDocumentCostItemType_id'];

                $query = "
                    select
				        ssl.Storage_id as \"Storage_id\",
                        s.Storage_Name as \"Storage_Name\",
                        o.Org_Nick as \"Org_Nick\",
                        pa.Address_Address as \"PAddress_Address\"
                        {$szName}
                    from
                        v_StorageStructLevel ssl 

                        left join v_Lpu l  on l.Lpu_id = ssl.Lpu_id

                        inner join Org o  on o.Org_id = COALESCE(ssl.Org_id, l.Org_id)


                        inner join Storage s  on s.Storage_id = ssl.Storage_id

                        left join v_Address pa  on pa.Address_id = o.PAddress_id

                ";
                break;
            default: //Если приказ создан в иной организации
                $params['UserOrg_id'] = !empty($filter['session']['org_id']) ? $filter['session']['org_id'] : null; //организация пользователя из сессии

                $query = "
                    with recursive OrgTree (Org_id, Org_Nick, PAddress_id) as (
                        select
                            o.Org_id, o.Org_Nick, o.PAddress_id
                        from
                            Org o
                        where
                            o.Org_id = :UserOrg_id
                        union all
                        select
                            o.Org_id, o.Org_Nick, o.PAddress_id
                        from
                            Org o
                            inner join OrgTree ot on ot.Org_id = o.Org_pid
                    )
                    select
				        ssl.Storage_id as \"Storage_id\",
                        s.Storage_Name as \"Storage_Name\",
                        ot.Org_Nick as \"Org_Nick\",
                        pa.Address_Address as \"PAddress_Address\"
                        {$szName}
                    from
                        v_StorageStructLevel ssl 

                        left join v_Lpu l  on l.Lpu_id = ssl.Lpu_id

                        inner join OrgTree ot  on ot.Org_id = COALESCE(ssl.Org_id, l.Org_id)


                        inner join Storage s  on s.Storage_id = ssl.Storage_id

                        left join v_Address pa  on pa.Address_id = ot.PAddress_id

                ";
                break;
        }

        if (count($join) > 0) {
             $query .= " ".implode(" ", $join);
        }
        if (count($where) > 0) {
             $query .= " where ".implode(" and ", $where);
        }

        $result = $this->db->query($query, $params);
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
	}

	/**
	 * Сохранение
	 */
	function save($data) {
		$procedure = 'p_WhsDocumentUcInvent_ins';
		if ( $data['WhsDocumentUcInvent_id'] > 0 ) {
			$procedure = 'p_WhsDocumentUcInvent_upd';
		}
		$q = "
			select WhsDocumentUcInvent_id as \"WhsDocumentUcInvent_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from dbo." . $procedure . "(
				WhsDocumentUc_pid := :WhsDocumentUc_pid,
				WhsDocumentUc_Num := :WhsDocumentUc_Num,
				WhsDocumentUc_Name := :WhsDocumentUc_Name,
				WhsDocumentType_id := :WhsDocumentType_id,
				WhsDocumentUc_Date := :WhsDocumentUc_Date,
				WhsDocumentUc_Sum := :WhsDocumentUc_Sum,
				WhsDocumentStatusType_id := :WhsDocumentStatusType_id,
				Org_aid := :Org_aid,
				WhsDocumentUcInvent_id := :WhsDocumentUcInvent_id,
				WhsDocumentUc_id := :WhsDocumentUc_id,
				WhsDocumentUcInvent_begDT := :WhsDocumentUcInvent_begDT,
				Org_id := :Org_id,
				Contragent_id := :Contragent_id,
				Storage_id := :Storage_id,
				DrugFinance_id := :DrugFinance_id,
				WhsDocumentCostItemType_id := :WhsDocumentCostItemType_id,
				pmUser_id := :pmUser_id);


		";
		$r = $this->db->query($q, $data);
		if ( is_object($r) ) {
			$result = $r->result('array');
		} else {
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Сохранение списка госконтрактов из сереализованного массива
	 */
	function saveWhsDocumentSupplyInventFromJSON($data) {
		$result = array();
		if (!empty($data['WhsDocumentSupplyInventListJSON']) && $data['WhsDocumentUc_id'] > 0) {
			ConvertFromWin1251ToUTF8($data['WhsDocumentSupplyInventListJSON']);
			$dt = (array) json_decode($data['WhsDocumentSupplyInventListJSON']);
			foreach($dt as $record) {
				switch($record->state) {
					case 'add':
					case 'edit':
						$save_data = array(
							'WhsDocumentSupplyInvent_id' => $record->state == 'edit' ? $record->WhsDocumentSupplyInvent_id : null,
							'WhsDocumentUc_id' => $data['WhsDocumentUc_id'],
							'WhsDocumentSupply_id' => $record->WhsDocumentSupply_id,
							'pmUser_id' => $data['pmUser_id']
						);
						$response = $this->saveObject('WhsDocumentSupplyInvent', $save_data);
						break;
					case 'delete':
						$response = $this->deleteObject('WhsDocumentSupplyInvent', array(
							'WhsDocumentSupplyInvent_id' => $record->WhsDocumentSupplyInvent_id
						));
						break;
				}
			}
		}
		return $result;
	}

	/**
	 * Сохранение списка госконтрактов из сереализованного массива
	 */
	function saveWhsDocumentUcInventFromJSON($data) {
		$result = array();
		if (!empty($data['WhsDocumentUcInventListJSON']) && $data['WhsDocumentUc_id'] > 0) {
			ConvertFromWin1251ToUTF8($data['WhsDocumentUcInventListJSON']);
			$dt = (array) json_decode($data['WhsDocumentUcInventListJSON']);
			foreach($dt as $record) {
				switch($record->state) {
					case 'add':
					case 'edit':
						$save_data = array(
							'key_field' => 'WhsDocumentUc_id',
							'enable_data_merging' => true,
							'WhsDocumentUc_id' => $record->state == 'edit' ? $record->WhsDocumentUc_id : null,
							'WhsDocumentUc_pid' => $data['WhsDocumentUc_id'],
							'WhsDocumentUc_Num' => '' . $record->WhsDocumentUc_Num,
							'WhsDocumentUc_Name' => $record->WhsDocumentUc_Name,
							'WhsDocumentType_id' => $record->WhsDocumentType_id,
							'WhsDocumentUc_Date' => !empty($record->WhsDocumentUc_Date) ? $this->formatDate($record->WhsDocumentUc_Date) : null,
							'WhsDocumentStatusType_id' => $record->WhsDocumentStatusType_id,
							'WhsDocumentUcInvent_id' => isset($record->WhsDocumentUcInvent_id) && $record->WhsDocumentUcInvent_id > 0 ? $record->WhsDocumentUcInvent_id : null,
							'WhsDocumentUcInvent_begDT' => !empty($record->WhsDocumentUcInvent_begDT) ? $this->formatDate($record->WhsDocumentUcInvent_begDT) : null,
							'Org_id' => !empty($record->Org_id) ? $record->Org_id : null,
							'Contragent_id' => !empty($record->Contragent_id) ? $record->Contragent_id : null,
							'Storage_id' => !empty($record->Storage_id) ? $record->Storage_id : null,
							'StorageZone_id' => !empty($record->StorageZone_id) ? $record->StorageZone_id : null,
							'DrugFinance_id' => !empty($record->DrugFinance_id) ? $record->DrugFinance_id : null,
							'WhsDocumentCostItemType_id' => !empty($record->WhsDocumentCostItemType_id) ? $record->WhsDocumentCostItemType_id : null,
							'pmUser_id' => $data['pmUser_id']
						);
						$response = $this->saveObject('WhsDocumentUcInvent', $save_data);
						break;
					case 'delete':
						$response = $this->delete(array(
							'no_trans' => true,
							'WhsDocumentUcInvent_id' => $record->WhsDocumentUcInvent_id,
							'pmUser_id' => $data['pmUser_id']
						));
						break;
				}
			}
		}
		return $result;
	}

	/**
	 * Удаление
	 */
	function delete($data) {
		$error = array();
		$result = array();
		if (!isset($data['WhsDocumentUcInvent_id'])) {
			$data['WhsDocumentUcInvent_id'] = $data['id'];
		}

		//старт транзакции
		if (!isset($data['no_trans'])) {
			$this->beginTransaction();
		}

		//сбор и удаление списка нарядов
		if (count($error) == 0) {
			$query = "
				select
					dusw.DocumentUcStorageWork_id as \"DocumentUcStorageWork_id\"
				from
					v_WhsDocumentUcInventDrug wduid 

                	inner join v_DocumentUcStorageWork dusw  on dusw.WhsDocumentUcInventDrug_id = wduid.WhsDocumentUcInventDrug_id

				where
					wduid.WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id;
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$item_array = $result->result('array');
				foreach($item_array as $item) {
					$result = $this->deleteObject('DocumentUcStorageWork', $item);
					if (!empty($result['Error_Msg'])) {
						$error[] = $result['Error_Msg'];
					}
				};
			}
		}

		//сбор и удаление списка медикаментов
		if (count($error) == 0) {
			$query = "
				select
					WhsDocumentUcInventDrug_id as \"WhsDocumentUcInventDrug_id\"
				from
					v_WhsDocumentUcInventDrug 

				where
					WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id;
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$item_array = $result->result('array');
				foreach($item_array as $item) {
					$result = $this->deleteObject('WhsDocumentUcInventDrug', $item);
					if (!empty($result['Error_Msg'])) {
						$error[] = $result['Error_Msg'];
					}
				};
			}
		}

		//удаление ведомости
		if (count($error) == 0) {
			$result = $this->deleteObject('WhsDocumentUcInvent', array(
				'WhsDocumentUcInvent_id' => $data['WhsDocumentUcInvent_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		//вывод ошибок
		if (count($error) > 0) {
			$result = array('Error_Msg' => $error[0]);
			if (!isset($data['no_trans'])) {
				$this->rollbackTransaction();
			}
			return $result;
		}

		//коммит транзакции
		if (!isset($data['no_trans'])) {
			$this->commitTransaction();
		}

		return $result;
	}

	/**
	 * Удаление приказа на проведение инвентаризации
	 */
	function deleteWhsDocumentUcInventOrder($data) {
		$error = array();
		$result = array();
		if (!isset($data['WhsDocumentUc_id'])) {
			$data['WhsDocumentUc_id'] = $data['id'];
		}

		//проверка наличия действующих ведомостей
		$query = "
			select
				count(wdui.WhsDocumentUcInvent_id) as \"cnt\"
			from
				v_WhsDocumentUcInvent wdui 

				left join v_WhsDocumentStatusType wdst  on wdst.WhsDocumentStatusType_id = wdui.WhsDocumentStatusType_id

			where
				WhsDocumentUc_pid = :WhsDocumentUc_id and
				wdst.WhsDocumentStatusType_Code = 2::text;
		";
		$result = $this->getFirstRowFromQuery($query, $data);
		if ($result && isset($result['cnt'])) {
			if ($result['cnt'] > 0) {
				$error[] = 'По приказу есть утвержденные инвентаризационные ведомости, удаление приказа запрещено.';
			}
		} else {
			$error[] = 'При проверке списка ведомостей произошла ошибка.';
		}

		//старт транзакции
		$this->beginTransaction();

		//сбор и удаление списка ГК
		if (count($error) == 0) {
			$query = "
				select
					WhsDocumentSupplyInvent_id as \"WhsDocumentSupplyInvent_id\"
				from
					v_WhsDocumentSupplyInvent 

				where
					WhsDocumentUc_id = :WhsDocumentUc_id;
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$item_array = $result->result('array');
				foreach($item_array as $item) {
					$result = $this->deleteObject('WhsDocumentSupplyInvent', $item);
					if (!empty($result['Error_Msg'])) {
						$error[] = $result['Error_Msg'];
					}
				}
			}
		}

		//сбор и удаление списка ведомостей
		if (count($error) == 0) {
			$query = "
				select
					WhsDocumentUcInvent_id as \"WhsDocumentUcInvent_id\"
				from
					v_WhsDocumentUcInvent 

				where
					WhsDocumentUc_pid = :WhsDocumentUc_id;
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$item_array = $result->result('array');
				foreach($item_array as $item) {
					$result = $this->delete(array(
						'no_trans' => true,
						'WhsDocumentUcInvent_id' => $item['WhsDocumentUcInvent_id'],
						'pmUser_id' => $data['pmUser_id']
					));
					if (!empty($result['Error_Msg'])) {
						$error[] = $result['Error_Msg'];
					}
				}
			}
		}

		//удаление приказа
		if (count($error) == 0) {
			$result = $this->deleteObject('WhsDocumentUc', array(
				'WhsDocumentUc_id' => $data['WhsDocumentUc_id']
			));
		}

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
	 *  Получение списка организаций для комбобокса
	 */
	function loadOrgCombo($filter) {
		if (isset($filter['Org_id']) && $filter['Org_id'] > 0) {
			$q = "
				select
					Org_id as \"Org_id\",
					Org_Name as \"Org_Name\"
				from
					v_Org 

				where
					Org_id = :Org_id;
			";
		} else if (!empty($filter['query'])) {
			$q = "
				select 
					o.Org_id as \"Org_id\",
					o.Org_Name as \"Org_Name\"
				from
					v_Org o 

				where
					o.Org_Name iLIKE '%'||:query||'%'
			    limit 100;

			";
		}
		if (!empty($q)) {
			$result = $this->db->query($q, $filter);
			if (is_object($result)) {
				return $result->result('array');
			} else {
				return false;
			}
		}
	}

	/**
	 * Получение номеров описи внутри инв. ведомости, для назначения
	 */
	function getWhsDocumentUcInventNumbers($data) {

		$item_arr = array();

		//проверка списка строк
		if (!empty($data['WhsDocumentUcInventDrug_List'])) {
			$id_array = explode(',', $data['WhsDocumentUcInventDrug_List']);
			foreach($id_array as $id) {
				if (!is_numeric($id)) {
					$error[] = "Некорректный идентификатор медикамента.";
					break;
				}
			}
		}
		/*
		$query = "
				select distinct
					wduid.WhsDocumentUcInventDrug_InvNum,
					(	COALESCE(ps.Person_SurName, '')+

						COALESCE(' '+ps.Person_FirName, '')+

						COALESCE(' '+ps.Person_SecName, '')

                    ) as StorageWork_Person
				from
					v_WhsDocumentUcInventDrug as wduid 

					left join v_DocumentUcStorageWork as dusw  on dusw.WhsDocumentUcInventDrug_id = wduid.WhsDocumentUcInventDrug_id

					left join v_PersonState as ps  on ps.Person_id = dusw.Person_eid

				where
					wduid.WhsDocumentUcInvent_id =:WhsDocumentUcInvent_id
					and wduid.WhsDocumentUcInventDrug_InvNum is NOT NULL
			";
		 * *
		 */
		$query = "
				select
				wduidi.WhsDocumentUcInventDrugInventory_id as \"WhsDocumentUcInventDrugInventory_id\",
				wduidi.WhsDocumentUcInventDrugInventory_InvNum as \"WhsDocumentUcInventDrug_InvNum\",
				COALESCE(wdst.WhsDocumentStatusType_Name,'') as \"StorageWork_Person\"

			from
				dbo.v_WhsDocumentUcInventDrugInventory wduidi 

				left join v_WhsDocumentStatusType wdst  on wdst.WhsDocumentStatusType_id = wduidi.WhsDocumentStatusType_id

			where wduidi.WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id
			";
		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			$item_arr = $result->result('array');
		}

		return $item_arr;
	}

	/**
	 * Получение последнего номера описи + 1 в инв. ведомости
	 */
	function getWhsDocumentUcInventDrugInventoryLastNum($data) {

		$item_arr = array();

		$query = "
				select
					max(WhsDocumentUcInventDrugInventory_InvNum) as \"lastInvNum\"
				from
					WhsDocumentUcInventDrugInventory as wduidi 

				where
					wduidi.WhsDocumentUcInvent_id =:WhsDocumentUcInvent_id
			";

		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			$item_arr = $result->result('array');
		}

		if (!empty($item_arr[0]))
			$item_arr[0]['lastInvNum'] += 1;
		else
			$item_arr[0]['lastInvNum'] = 1;

		return $item_arr[0];
	}

	/**
	 * Назначение номера описи выбранным медикаментам
	 */
	function assignWhsDocumentUcInventNumber($data) {

		$id_array = array();
		$error = array();

		//проверка списка строк
		if (!empty($data['WhsDocumentUcInventDrug_List'])) {

			$id_array = explode(',', $data['WhsDocumentUcInventDrug_List']);

			foreach($id_array as $id) {

				if (!is_numeric($id)) {
					$error[] = "Некорректный идентификатор медикамента.";
					break;
				}
			}
		}

		//старт транзакции
		$this->beginTransaction();

		foreach ($id_array as $id) {

			$data['WhsDocumentUcInventDrug_id'] = $id;
			$response = $this->saveObject('WhsDocumentUcInventDrug', $data);

			if (!empty($response['Error_Msg'])) {
				$error[] = $response['Error_Msg'];
				break;
			}
		}

		//откат изменений при наличии ошибок
		if (count($error) > 0) {
			$result['Error_Msg'] = $error[0];
			$this->rollbackTransaction();
		} else {
			//коммит транзакции
			$result['success'] = true;
			$this->commitTransaction();
		}

		return $result;
	}

	/**
	 * Создание списка инвентаризационных ведомостей
	 */
	function createDocumentUcInventList($data) {
		$where = array();
		$sz_where = array();
		$params = array();
		$union = "";

		if (!empty($data['Storage_List'])) {
			// список пришедших записей складов где не указано место хранения - докумены создадутся для каждого места хранения в таком складе
			$stor_check_arr = array();

			// массив мест хранения и родительского для этого места хранения склада - докумены создадутся только для указанного здесь места хранения, а не для всего скалад
			$stor_sz_arr = array();

			$stor_arr = explode(',', $data['Storage_List']);
			foreach ($stor_arr as $stor) {
				$item = explode('|', $stor);
				if(empty($item[1])){
					array_push($stor_check_arr, $item[0]);
				} else {
					if(!in_array($item[0], $stor_check_arr)){
						array_push($stor_sz_arr, array('Storage_id'=>$item[0],'StorageZone_id'=>$item[1]));
					}
				}
			}
			if(count($stor_sz_arr) > 0){
				$sz_arr = array();
				foreach ($stor_sz_arr as $stor_sz) {
					if(!in_array($stor_sz['Storage_id'], $stor_check_arr)){
						array_push($sz_arr,  $stor_sz['StorageZone_id']);
					}
				}
				if(count($sz_arr) > 0){
					$sz_where[] = "sz.StorageZone_id in (".implode(',', $sz_arr).")";
				}
			}
			if(count($stor_check_arr) > 0){
				$where[] = "ssl.Storage_id in (".implode(',', $stor_check_arr).")";
			}
		}
		if (!empty($data['Org_List'])) {
			$where[] = "o.Org_id in ({$data['Org_List']})";
		}

		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'where '.$where_clause;
		}

		if(count($sz_where) > 0){
			$sz_where_clause = implode(' and ', $sz_where);
			if (strlen($sz_where_clause)) {
				$sz_where_clause = 'where '.$sz_where_clause;
			}
			$union = "
				union all
				select
                        (
                         select COALESCE(max(cast (WhsDocumentUc_Num as bigint)), 0) + 1 as WhsDocumentUc_Num
                         from v_WhsDocumentUc
                         where WhsDocumentType_id =
                               (
                                 SELECT WhsDocumentType_id
                                 FROM cte
                               ) and
                               WhsDocumentUc_Num not iLIKE '%.%' and
                               WhsDocumentUc_Num not iLIKE '%,%' and
                               isnumeric(WhsDocumentUc_Num) = 1 and
                               length(WhsDocumentUc_Num) <= 18
                       ) as \"WhsDocumentUc_Num\",
                       (
                         select WhsDocumentType_Name
                         from v_WhsDocumentType
                         where WhsDocumentType_id =
                               (
                                 SELECT WhsDocumentType_id
                                 FROM cte
                               )
                       ) as \"WhsDocumentType_Name\",
                       (
                         SELECT WhsDocumentType_id
                         FROM cte
                       ) as \"WhsDocumentType_id\",
                       (
                         SELECT WhsDocumentStatusType_id
                         FROM cte
                       ) as \"WhsDocumentStatusType_id\",
                       1 as \"WhsDocumentStatusType_Code\",
                       (
                         select WhsDocumentStatusType_Name
                         from v_WhsDocumentStatusType
                         where WhsDocumentStatusType_id =
                               (
                                 SELECT WhsDocumentStatusType_id
                                 FROM cte
                               )
                       ) as \"WhsDocumentStatusType_Name\",
					o.Org_id as \"Org_id\",
					o.Org_Name as \"Org_Name\",
					s.Storage_id as \"Storage_id\",
					s.Storage_Name as \"Storage_Name\",
					sz.StorageZone_id as \"StorageZone_id\",
					rtrim(COALESCE(sut.StorageUnitType_Name,'') || ' ' || COALESCE(sz.StorageZone_Address,'')) as \"StorageZone_Name\"

				from
					v_StorageStructLevel ssl 

					left join v_Lpu l  on l.Lpu_id = ssl.Lpu_id

					inner join v_Org o  on o.Org_id = COALESCE(ssl.Org_id, l.Org_id)


					inner join v_Storage s  on s.Storage_id = ssl.Storage_id

					left join v_StorageZone sz  on sz.Storage_id = s.Storage_id

					left join v_StorageUnitType sut  on sut.StorageUnitType_id = sz.StorageUnitType_id

				$sz_where_clause
			";
		}

        $query = "
            select 
                c.Contragent_id as \"Contragent_id\",
                c.Contragent_Name as \"Contragent_Name\"
            from
                v_Org o 

                left join v_OrgType ot  on ot.OrgType_id = o.OrgType_id

                left join v_Contragent c  on c.Org_id = o.Org_id

                left join v_ContragentType ct  on ct.ContragentType_id = c.ContragentType_id

            where
                c.Org_id = :Org_id and
                (
                    (ot.OrgType_SysNick = 'lpu' and ct.ContragentType_SysNick = 'mo') or
                    (ot.OrgType_SysNick = 'farm' and ct.ContragentType_SysNick = 'apt') or
                    (ot.OrgType_SysNick = 'reg_dlo' and ct.ContragentType_SysNick = 'store')
                )
            limit 1
        ";
        $contragent_data = $this->getFirstRowFromQuery($query, array(
            'Org_id' => $data['session']['org_id']
        ), true);
		if ($contragent_data === false) {
			return $this->createError(null, 'Ошибка при поиске записи в справочнике контрагентов.');
		}
        if (empty($contragent_data)) {
            return $this->createError(null, 'Организация пользователя не включена в справочник контрагентов. Создание ведомости не возможно.');
        }

		$query = "
        			WITH cte AS (
                    SELECT 
                        (select WhsDocumentStatusType_id from v_WhsDocumentStatusType  where WhsDocumentStatusType_Code = 1::text limit 1) AS WhsDocumentStatusType_id,
                        (select WhsDocumentType_id from v_WhsDocumentType  where WhsDocumentType_Code = 21::text limit 1) AS WhsDocumentType_id -- 21 - Инвентаризационная ведомость
                    )
                    
                    select (
                             select COALESCE(max(cast (WhsDocumentUc_Num as bigint)), 0) + 1 as WhsDocumentUc_Num
                             from v_WhsDocumentUc
                             where WhsDocumentType_id =
                                   (
                                     SELECT WhsDocumentType_id
                                     FROM cte
                                   ) and
                                   WhsDocumentUc_Num not iLIKE '%.%' and
                                   WhsDocumentUc_Num not iLIKE '%,%' and
                                   isnumeric(WhsDocumentUc_Num) = 1 and
                                   length(WhsDocumentUc_Num) <= 18
                           ) as \"WhsDocumentUc_Num\",
                           (
                             select WhsDocumentType_Name
                             from v_WhsDocumentType
                             where WhsDocumentType_id =
                                   (
                                     SELECT WhsDocumentType_id
                                     FROM cte
                                   )
                           ) as \"WhsDocumentType_Name\",
                           (
                             SELECT WhsDocumentType_id
                             FROM cte
                           ) as \"WhsDocumentType_id\",
                           (
                             SELECT WhsDocumentStatusType_id
                             FROM cte
                           ) as \"WhsDocumentStatusType_id\",
                           1 as \"WhsDocumentStatusType_Code\",
                           (
                             select WhsDocumentStatusType_Name
                             from v_WhsDocumentStatusType
                             where WhsDocumentStatusType_id =
                                   (
                                     SELECT WhsDocumentStatusType_id
                                     FROM cte
                                   )
                           ) as \"WhsDocumentStatusType_Name\",
                           o.Org_id as \"Org_id\",
                           o.Org_Name as \"Org_Name\",
                           s.Storage_id as \"Storage_id\",
                           s.Storage_Name as \"Storage_Name\",
                           sz.StorageZone_id as \"StorageZone_id\",
                           rtrim(COALESCE(sut.StorageUnitType_Name, '') || ' ' || COALESCE(sz.StorageZone_Address, '')) as \"StorageZone_Name\"
                    from v_StorageStructLevel ssl
                         left join v_Lpu l on l.Lpu_id = ssl.Lpu_id
                         inner join v_Org o on o.Org_id = COALESCE(ssl.Org_id, l.Org_id)
                         inner join v_Storage s on s.Storage_id = ssl.Storage_id
                         left join v_StorageZone sz on sz.Storage_id = s.Storage_id
                         left join v_StorageUnitType sut on sut.StorageUnitType_id = sz.StorageUnitType_id

			$where_clause
			union all
			select (
                     select COALESCE(max(cast (WhsDocumentUc_Num as bigint)), 0) + 1 as WhsDocumentUc_Num
                     from v_WhsDocumentUc
                     where WhsDocumentType_id =
                           (
                             SELECT WhsDocumentType_id
                             FROM cte
                           ) and
                           WhsDocumentUc_Num not iLIKE '%.%' and
                           WhsDocumentUc_Num not iLIKE '%,%' and
                           isnumeric(WhsDocumentUc_Num) = 1 and
                           length(WhsDocumentUc_Num) <= 18
                   ) as \"WhsDocumentUc_Num\",
                   (
                     select WhsDocumentType_Name
                     from v_WhsDocumentType
                     where WhsDocumentType_id =
                           (
                             SELECT WhsDocumentType_id
                             FROM cte
                           )
                   ) as \"WhsDocumentType_Name\",
                   (
                     SELECT WhsDocumentType_id
                     FROM cte
                   ) as \"WhsDocumentType_id\",
                   (
                     SELECT WhsDocumentStatusType_id
                     FROM cte
                   ) as \"WhsDocumentStatusType_id\",
                   1 as \"WhsDocumentStatusType_Code\",
                   (
                     select WhsDocumentStatusType_Name
                     from v_WhsDocumentStatusType
                     where WhsDocumentStatusType_id =
                           (
                             SELECT WhsDocumentStatusType_id
                             FROM cte
                           )
                   ) as \"WhsDocumentStatusType_Name\",
                   o.Org_id as \"Org_id\",
                   o.Org_Name as \"Org_Name\",
                   s.Storage_id as \"Storage_id\",
                   s.Storage_Name as \"Storage_Name\",
                   null as \"StorageZone_id\",
                   null as \"StorageZone_Name\"
            from v_StorageStructLevel ssl
                 left join v_Lpu l on l.Lpu_id = ssl.Lpu_id
                 inner join v_Org o on o.Org_id = COALESCE(ssl.Org_id, l.Org_id)
                 inner join v_Storage s on s.Storage_id = ssl.Storage_id

			$where_clause
			$union
		";
		//print getDebugSQL ($query, $params); exit;
		
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
            $inv_array = $result->result('array');

            //дописываем данные контрагента в массив с данными ведомостей
            for($i = 0; $i < count($inv_array); $i++) {
                $inv_array[$i]['Contragent_id'] = $contragent_data['Contragent_id'];
                $inv_array[$i]['Contragent_Name'] = $contragent_data['Contragent_Name'];
            }

			return $inv_array;
		} else {
			return false;
		}
	}

	/**
	 * получение списка медикаментов для инвентаризационной ведомости
	 */
	function getDocumentUcInventDrugList($data) {
		$item_arr = array();

		//подсчет количества ГК в приказе
		$query = "
				select
					count(WhsDocumentSupply_id) as \"cnt\"
				from
					v_WhsDocumentUcInvent wdui 

					left join v_WhsDocumentSupplyInvent wdsi  on wdsi.WhsDocumentUc_id = wdui.WhsDocumentUc_pid

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

						left join v_WhsDocumentSupplyInvent wdsi  on wdsi.WhsDocumentUc_id = wdui.WhsDocumentUc_pid

					where
						WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id
				)
				select
					wdui.WhsDocumentUcInvent_id as \"WhsDocumentUcInvent_id\",
					dor.DrugShipment_id as \"DrugShipment_id\",
					dor.Drug_id as \"Drug_id\",
					dor.PrepSeries_id as \"PrepSeries_id\",
					dor.SubAccountType_id as \"SubAccountType_id\",
					dor.Okei_id as \"Okei_id\",
					dor.GoodsUnit_id as \"GoodsUnit_id\",
					case 
						when wdui.StorageZone_id is not null then dsz.DrugStorageZone_Count
						else dor.DrugOstatRegistry_Kolvo 
					end as \"WhsDocumentUcInventDrug_Kolvo\",
					case 
						when wdui.StorageZone_id is not null then cast((dsz.DrugStorageZone_Count*dsz.DrugStorageZone_Price) as numeric(15,2))
						else dor.DrugOstatRegistry_Sum 
					end as \"WhsDocumentUcInventDrug_Sum\",
					case 
						when wdui.StorageZone_id is not null then dsz.DrugStorageZone_Price
						else dor.DrugOstatRegistry_Cost 
					end as \"WhsDocumentUcInventDrug_Cost\",
					wdui.StorageZone_id as \"StorageZone_id\"
				from
					v_WhsDocumentUcInvent wdui 

					inner join v_DrugOstatRegistry dor  on

						dor.Org_id = wdui.Org_id and
						dor.Contragent_id = wdui.Contragent_id and
						dor.Storage_id = wdui.Storage_id and
						dor.DrugFinance_id = wdui.DrugFinance_id and
						dor.WhsDocumentCostItemType_id = wdui.WhsDocumentCostItemType_id
					left join v_DrugStorageZone dsz  on dsz.StorageZone_id = wdui.StorageZone_id

					left join v_DrugShipment ds  on ds.DrugShipment_id = dor.DrugShipment_id

					left join v_DrugShipmentLink dsl  on dsl.DrugShipment_id = ds.DrugShipment_id

					left join supply_list  on supply_list.WhsDocumentSupply_id = ds.WhsDocumentSupply_id

				where
					wdui.WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id and
					(:supply_count = 0 or supply_list.WhsDocumentSupply_id is not null) and
					(wdui.StorageZone_id is null or dsz.DrugStorageZone_id is not null) and
					dsl.DocumentUcStr_id is not null;
			";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$item_arr = $result->result('array');
		}

		return $item_arr;
	}

	/**
	 * Создание списка медикаментов для инвентаризационной ведомости
	 */
	function createDocumentUcInventDrugList($data) {
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
			if(!empty($data['forAll'])){
				return array();
			}
		}

		if (count($error) == 0) {
			$item_arr = $this->getDocumentUcInventDrugList($data);
		}

		//старт транзакции
		$this->beginTransaction();
		/*
		if(count($item_arr)>0){
			$data['WhsDocumentUcInventDrugInventory_InvNum'] = 1;
			$res = $this->saveWhsDocumentUcInventDrugInventoryNum($data);
		}
		 * */
		
		//создаем 1-й номер описи
		$data['WhsDocumentUcInventDrugInventory_InvNum'] = 1;
		$res = $this->saveWhsDocumentUcInventDrugInventoryNum($data);
		if(empty($res[0]['WhsDocumentUcInventDrugInventory_id'])){
			$this->rollbackTransaction();
			return array('Error_Msg' => 'Произошла ошибка при создании номера описи инв. ведомости');
		}
		
		$whsDocumentUcInventDrugInventoryNumID = $res[0]['WhsDocumentUcInventDrugInventory_id'];
		
		//сохранение списка медикаментов
		if (count($error) == 0) {
			foreach($item_arr as $item) {
				$item['WhsDocumentUcInventDrug_id'] = null;

				// Статус "Новый", Номер описи = 1
				$item['WhsDocumentStatusType_id'] = $this->getObjectIdByCode('WhsDocumentStatusType', 1);
				//$item['WhsDocumentUcInventDrug_InvNum'] = 1;
				$item['WhsDocumentUcInventDrugInventory_id'] = $whsDocumentUcInventDrugInventoryNumID;

				$item['pmUser_id'] = $data['pmUser_id'];
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
	 * Создание списка медикаментов для инвентаризационных ведомостей
	 */
	function createDocumentUcInventDrugListAllCommon($data) {
		$ids = $data['WhsDocumentUcInvent_ids'];
		$ids = explode(',', $ids);
		foreach ($ids as $id) {
			$data['WhsDocumentUcInvent_id'] = $id;
			$data['forAll'] = 1;
			$resp = $this->createDocumentUcInventDrugList($data);
			if(!empty($resp['Error_Msg'])){
				return $resp;
			}
		}
		return array('success'=>true);
	}

	/**
	 * Создание дочернего документа для инвентаризационной ведомости
	 */
	function createDocumentUc($data) {
		$error = array();
		$drug_data = array();
		$common_data = array();
		$invent_data = array();
		$doc_id = null;
		$current_date = date('Y-m-d'); // текущая дата
		$type = $data['DrugDocumentType_SysNick'];

		//  В рамках разделения АРМа товароведа
		$session_data = getSessionParams();
		$orgtype = $session_data['session']['orgtype'];
		$region =  $session_data['session']['region']['nick'];

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
					to_char(i_ord.WhsDocumentUc_Date, 'DD.MM.YYYY') as \"Order_Date\"

				from
					v_WhsDocumentUcInvent wdui 

					left join v_WhsDocumentUc i_ord  on i_ord.WhsDocumentUc_id = wdui.WhsDocumentUc_pid

				where
					wdui.WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id;
			";
			$invent_data = $this->getFirstRowFromQuery($query, array(
				'WhsDocumentUcInvent_id' => $data['WhsDocumentUcInvent_id']
			));
			if (count($invent_data) < 1) {
				$error[] = "Не удалось получить данные инвентаризационной ведомости.";
			}
		}

		//проверяем не создан ли уже документ учета для данной ведомости
		if (count($error) == 0) {
			$query = "
				select 
					du.DocumentUc_id as \"DocumentUc_id\",
					du.DocumentUc_Num as \"DocumentUc_Num\",
					to_char(du.DocumentUc_setDate, 'DD.MM.YYYY') as \"DocumentUc_setDate\"

				from
					v_DocumentUc du 

					left join v_DrugDocumentType ddt  on ddt.DrugDocumentType_id = du.DrugDocumentType_id

				where
					du.WhsDocumentUc_id = :WhsDocumentUc_id and
					ddt.DrugDocumentType_SysNick = :DrugDocumentType_SysNick;
                limit 1
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

				$w_query = "wduid.WhsDocumentUcInventDrug_FactKolvo > COALESCE(wduid.WhsDocumentUcInventDrug_Kolvo, 0)";

				$error_msg = "Выполнение операции не доступно, т.к. в результатах инвентаризации излишки не зафиксированы.";
			}
			if ($type == 'DokSpis') {
				$k_query = "COALESCE(wduid.WhsDocumentUcInventDrug_Kolvo, 0)-wduid.WhsDocumentUcInventDrug_FactKolvo";

				$w_query = "wduid.WhsDocumentUcInventDrug_FactKolvo < COALESCE(wduid.WhsDocumentUcInventDrug_Kolvo, 0)";

				$error_msg = "Выполнение операции не доступно, т.к. в результатах инвентаризации недостача не зафиксирована.";
			}

			$query = "
				select
					COALESCE(wduid.Drug_id, wduid.Drug_did) as \"Drug_id\",

					wduid.DrugShipment_id as \"DrugShipment_id\",
					wduid.SubAccountType_id as \"SubAccountType_id\",
					dus.DocumentUcStr_id as \"DocumentUcStr_oid\",
					dus.DrugNds_id as \"DrugNds_id\",
					dus.DocumentUcStr_IsNDS as \"DocumentUcStr_IsNDS\",
					dus.DocumentUcStr_Price as \"DocumentUcStr_Price\",
					c.NewCount as \"DocumentUcStr_Count\",
					cast(dus.DocumentUcStr_EdCount*k.koef as decimal(12,2)) as \"DocumentUcStr_EdCount\",
					cast(dus.DocumentUcStr_Sum*k.koef as decimal(12,2)) as \"DocumentUcStr_Sum\",
					cast(dus.DocumentUcStr_SumNds*k.koef as decimal(12,2)) as \"DocumentUcStr_SumNds\",
					dus.PrepSeries_id as \"PrepSeries_id\",
					dus.DocumentUcStr_Ser as \"DocumentUcStr_Ser\",
					dus.DocumentUcStr_godnDate as \"DocumentUcStr_godnDate\",
					dus.DocumentUcStr_CertNum as \"DocumentUcStr_CertNum\",
					dus.DocumentUcStr_CertOrg as \"DocumentUcStr_CertOrg\",
					dus.DrugLabResult_Name as \"DrugLabResult_Name\",
					dus.DocumentUcStr_CertDate as \"DocumentUcStr_CertDate\",
					dus.DocumentUcStr_CertGodnDate as \"DocumentUcStr_CertGodnDate\",
					dus.DrugFinance_id as \"DrugFinance_id\",
					dus.GoodsUnit_bid as \"GoodsUnit_bid\",
					dus.GoodsUnit_id as \"GoodsUnit_id\"
				from
					v_WhsDocumentUcInventDrug wduid 

					left join DrugShipmentLink dsl  on dsl.DrugShipment_id = wduid.DrugShipment_id

					left join v_DocumentUcStr dus  on dus.DocumentUcStr_id = dsl.DocumentUcStr_id

					LEFT JOIN LATERAL (

						select
                        {$k_query} 
                        as NewCount
					) as c ON true
					LEFT JOIN LATERAL (

						select c.NewCount/dus.DocumentUcStr_Count as koef
					) as k ON true
				where
					wduid.WhsDocumentUcInventDrug_FactKolvo is not null
					and {$w_query}
					and wduid.WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id
					and dus.DocumentUcStr_Count > 0
				union all
				select
					wduid.Drug_id as \"Drug_id\",
					wduid.DrugShipment_id as \"DrugShipment_id\",
					wduid.SubAccountType_id as \"SubAccountType_id\",
					null as \"DocumentUcStr_oid\",
					wds.DrugNds_id as \"DrugNds_id\",
					2 as \"DocumentUcStr_IsNDS\",
					Price.Value as \"DocumentUcStr_Price\",
					c.NewCount as \"DocumentUcStr_Count\",
					null as \"DocumentUcStr_EdCount\",
					wduid.WhsDocumentUcInventDrug_Sum as \"DocumentUcStr_Sum\",
					cast(wduid.WhsDocumentUcInventDrug_Sum-(Price.Value*c.NewCount) as numeric(12,2)) as \"DocumentUcStr_SumNds\",
					ps.PrepSeries_id as \"PrepSeries_id\",
					ps.PrepSeries_Ser as \"DocumentUcStr_Ser\",
					ps.PrepSeries_GodnDate as \"DocumentUcStr_godnDate\",
					null as \"DocumentUcStr_CertNum\",
					null as \"DocumentUcStr_CertOrg\",
					null as \"DrugLabResult_Name\",
					null as \"DocumentUcStr_CertDate\",
					null as \"DocumentUcStr_CertGodnDate\",
					wds.DrugFinance_id as \"DrugFinance_id\",
					wduid.GoodsUnit_id as \"GoodsUnit_bid\",
					null as \"GoodsUnit_id\"
				from
					v_WhsDocumentUcInventDrug wduid 

					left join v_DrugShipment ds  on ds.DrugShipment_id = wduid.DrugShipment_id

					left join DrugShipmentLink dsl  on dsl.DrugShipment_id = wduid.DrugShipment_id

					left join v_WhsDocumentSupply wds  on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id

					left join v_DrugNds dn  on dn.DrugNds_id = wds.DrugNds_id

					left join rls.v_PrepSeries ps  on ps.PrepSeries_id = wduid.PrepSeries_id

					left join rls.v_Drug d  on d.Drug_id = wduid.Drug_id

					LEFT JOIN LATERAL (

						select wduid.WhsDocumentUcInventDrug_FactKolvo-COALESCE(wduid.WhsDocumentUcInventDrug_Kolvo, 0) as NewCount

					) as c ON true
					LEFT JOIN LATERAL (

						select cast(wduid.WhsDocumentUcInventDrug_Cost/(1+dn.DrugNds_Code/100) as numeric(12,2)) as Value
					) Price ON true
				where
					wduid.WhsDocumentUcInventDrug_FactKolvo is not null
					and wduid.WhsDocumentUcInventDrug_FactKolvo > COALESCE(wduid.WhsDocumentUcInventDrug_Kolvo, 0)

					and wduid.WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id
					and dsl.DrugShipmentLink_id is null
			";
			
			if ($region == 'ufa' && $orgtype == 'farm') {
			    $query = "
					select
						--wduid.Drug_id,
						COALESCE(wduid.Drug_id, wduid.Drug_did) as \"Drug_id\",

						wduid.DrugShipment_id as \"DrugShipment_id\",
						wduid.SubAccountType_id as \"SubAccountType_id\",
						dus.DocumentUcStr_id as \"DocumentUcStr_oid\",
						dus.DrugNds_id as \"DrugNds_id\",
						dus.DocumentUcStr_IsNDS as \"DocumentUcStr_IsNDS\",
						dus.DocumentUcStr_Price as \"DocumentUcStr_Price\",
						c.NewCount as \"DocumentUcStr_Count\",
						cast(dus.DocumentUcStr_EdCount*k.koef as decimal(12,2)) as \"DocumentUcStr_EdCount\",
						cast(dus.DocumentUcStr_Sum*k.koef as decimal(12,2)) as \"DocumentUcStr_Sum\",
						cast(dus.DocumentUcStr_SumNds*k.koef as decimal(12,2)) as \"DocumentUcStr_SumNds\",
						dus.PrepSeries_id as \"PrepSeries_id\",
						dus.DocumentUcStr_Ser as \"DocumentUcStr_Ser\",
						dus.DocumentUcStr_godnDate as \"DocumentUcStr_godnDate\",
						dus.DocumentUcStr_CertNum as \"DocumentUcStr_CertNum\",
						dus.DocumentUcStr_CertOrg as \"DocumentUcStr_CertOrg\",
						dus.DrugLabResult_Name as \"DrugLabResult_Name\",
						dus.DocumentUcStr_CertDate as \"DocumentUcStr_CertDate\",
						dus.DocumentUcStr_CertGodnDate as \"DocumentUcStr_CertGodnDate\",
						dus.DrugFinance_id as \"DrugFinance_id\",
						farm.Lpu_id as \"Lpu_id\",
						dus.GoodsUnit_id AS \"GoodsUnit_bid\",
						null  AS \"GoodsUnit_id\"
					from
						v_WhsDocumentUcInventDrug wduid 

						inner join v_WhsDocumentUcInvent wdui   on wdui.WhsDocumentUcInvent_id = wduid.WhsDocumentUcInvent_id

						LEFT JOIN LATERAL( 

							Select lpu_id from v_OrgFarmacyIndex farm  

							where  farm.Storage_id = wdui.Storage_id and farm.Org_id = wdui.Org_id
                            limit 1
						) farm ON true
						LEFT JOIN LATERAL (Select dus.DocumentUcStr_id,

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
                                    dus.GoodsUnit_id
						 from  DrugShipmentLink dsl  

							inner join v_DocumentUcStr dus  on dus.DocumentUcStr_id = dsl.DocumentUcStr_id and dus.DocumentUcStr_Price = wduid.WhsDocumentUcInventDrug_Cost

							inner join v_DocumentUc du  on du.DocumentUc_id = dus.DocumentUc_id and du.Org_id = wdui.org_id 

							where  dsl.DrugShipment_id = wduid.DrugShipment_id
                            limit 1
						) dus ON true
						LEFT JOIN LATERAL (

							select
                            {$k_query} 
                            as NewCount
						) as c ON true
						LEFT JOIN LATERAL (

							select c.NewCount/dus.DocumentUcStr_Count as koef
						) as k ON true
					where
						wduid.WhsDocumentUcInventDrug_FactKolvo is not null and
						{$w_query}
						wduid.WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id and
						dus.DocumentUcStr_Count > 0
					union all
					select
						wduid.Drug_id as \"Drug_id\",
						wduid.DrugShipment_id as \"DrugShipment_id\",
						wduid.SubAccountType_id as \"SubAccountType_id\",
						null as \"DocumentUcStr_oid\",
						wds.DrugNds_id as \"DrugNds_id\",
						2 as \"DocumentUcStr_IsNDS\",
						Price.Value as \"DocumentUcStr_Price\",
						c.NewCount as \"DocumentUcStr_Count\",
						null as \"DocumentUcStr_EdCount\",
						wduid.WhsDocumentUcInventDrug_Sum as \"DocumentUcStr_Sum\",
						cast(wduid.WhsDocumentUcInventDrug_Sum-(Price.Value*c.NewCount) as decimal(12,2)) as \"DocumentUcStr_SumNds\",
						ps.PrepSeries_id as \"PrepSeries_id\",
						ps.PrepSeries_Ser as \"DocumentUcStr_Ser\",
						ps.PrepSeries_GodnDate as \"DocumentUcStr_godnDate\",
						null as \"DocumentUcStr_CertNum\",
						null as \"DocumentUcStr_CertOrg\",
						null as \"DrugLabResult_Name\",
						null as \"DocumentUcStr_CertDate\",
						null as \"DocumentUcStr_CertGodnDate\",
						wds.DrugFinance_id as \"DrugFinance_id\",
						farm.Lpu_id as \"Lpu_id\",
						wduid.GoodsUnit_id as \"GoodsUnit_bid\",
						null as \"GoodsUnit_id\"
					from
						v_WhsDocumentUcInventDrug wduid 

						inner join v_WhsDocumentUcInvent wdui   on wdui.WhsDocumentUcInvent_id = wduid.WhsDocumentUcInvent_id

						LEFT JOIN LATERAL( 

							Select lpu_id from v_OrgFarmacyIndex farm  

							where  farm.Storage_id = wdui.Storage_id and farm.Org_id = wdui.Org_id
                            limit 1
						) farm ON true
						left join v_DrugShipment ds  on ds.DrugShipment_id = wduid.DrugShipment_id

						left join DrugShipmentLink dsl  on dsl.DrugShipment_id = wduid.DrugShipment_id

						left join v_WhsDocumentSupply wds  on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id

						left join v_DrugNds dn  on dn.DrugNds_id = wds.DrugNds_id

						left join rls.v_PrepSeries ps  on ps.PrepSeries_id = wduid.PrepSeries_id

						left join rls.v_Drug d  on d.Drug_id = wduid.Drug_id

						LEFT JOIN LATERAL (

							select wduid.WhsDocumentUcInventDrug_FactKolvo-COALESCE(wduid.WhsDocumentUcInventDrug_Kolvo, 0) as NewCount

						) as c ON true
						LEFT JOIN LATERAL (

							select cast(wduid.WhsDocumentUcInventDrug_Cost/(1+dn.DrugNds_Code/100) as decimal(12,2)) as Value
						) Price ON true
					where
						wduid.WhsDocumentUcInventDrug_FactKolvo is not null
						and wduid.WhsDocumentUcInventDrug_FactKolvo > COALESCE(wduid.WhsDocumentUcInventDrug_Kolvo, 0)

						and wduid.WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id
						and dsl.DrugShipmentLink_id is null
			    ";
			};

			//print getDebugSQL($query, $data);exit;
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
				select DrugDocumentType_id AS \"DrugDocumentType_id\"
                 from v_DrugDocumentType
                 where DrugDocumentType_SysNick =:DrugDocumentType_SysNick
                 limit 1;
			";
			$common_data = $this->getFirstResultFromQuery($query, array(
				'DrugDocumentType_SysNick' => $data['DrugDocumentType_SysNick']
			));

			if (count($common_data) < 1) {
				$error[] = "Не удалось получить данные для создания документа учета.";
			}
		}

		$docs_data = array();
		foreach($drug_data as $drug) {
			$docs_data[$drug['SubAccountType_id']]['drugs'][] = $drug;
		}

		//старт транзакции
		$this->beginTransaction();

		//сохранение документа учета
		if (count($error) == 0) {
			foreach($docs_data as $SubAccountType_id => &$doc) {
				$save_data = array();

				$save_data['DocumentUc_id'] = null;
				$save_data['DrugDocumentStatus_id'] = $this->getObjectIdByCode('DrugDocumentStatus', 1); //1 - Новый;
				$save_data['DocumentUc_Num'] = $this->generateNum('DocumentUc', 'DocumentUc_Num');
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
					$save_data['SubAccountType_tid'] = $SubAccountType_id;
				}
				if ($type == 'DokSpis') {
					$save_data['DrugDocumentType_id'] = $this->getObjectIdByCode('DrugDocumentType',2); //
					$save_data['Contragent_sid'] = $invent_data['Contragent_id'];
					$save_data['Storage_sid'] = $invent_data['Storage_id'];
					$save_data['SubAccountType_sid'] = $SubAccountType_id;
				}

				$save_data['pmUser_id'] = $data['pmUser_id'];

				$doc_save_result = $this->saveObject('DocumentUc', $save_data);
				if (!empty($doc_save_result['Error_Msg'])) {
					$error[] = $doc_save_result['Error_Msg'];
				}
				if (!empty($doc_save_result['DocumentUc_id'])) {
					$doc['id'] = $doc_save_result['DocumentUc_id'];
				} else {
					$error[] = "Не удалось сохранить документ учета.";
				}
			}
			unset($doc);
		}

		//сохранение списка медикаментов
		if (count($error) == 0) {
			foreach ($docs_data as $SubAccount => $doc) {
				foreach($doc['drugs'] as $drug) {
					$drug['DocumentUcStr_id'] = null;
					$drug['DocumentUc_id'] = $doc['id'];
					$drug['pmUser_id'] = $data['pmUser_id'];
					if ($type == 'DokSpis') {
						$drug['DocumentUcStr_Reason'] = "Результаты инвентаризации по прик. ".$invent_data['Order_Num']." от ".$invent_data['Order_Date'];
					}
					if ($type == 'DocOprih' || $type == 'DokSpis') {
						$drug['StorageZone_id'] = $invent_data['StorageZone_id'];
					}
					$result = $this->saveObject('DocumentUcStr', $drug);
					if (!empty($result['Error_Msg'])) {
						$error[] = $result['Error_Msg'];
						break;
					}
					else if ($type == 'DocOprih') {
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
									select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
									from r2.p_attachMoByFarmacy_ins(
									DocumentUcStr_id := :DocumentUcStr_id,
									Lpu_id := :Lpu_id );

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
						}
					}
				}
			}
		}

		$response = array('success' => true);

		//вывод ошибок
		if (count($error) > 0) {
			$response = array('Error_Msg' => $error[0]);
			$this->rollbackTransaction();
			return $response;
		}

		//коммит транзакции
		$this->commitTransaction();
		//$this->rollbackTransaction();

		//возвращаем тип документа для открытия формы
		foreach($docs_data as $SubAccountType_id => $doc) {
			//12 - Оприходование; 2 - Документ списания медикаментов;
			$response['DocumentUcList'][] = array(
				'DocumentUc_id' => $doc['id'],
				'DrugDocumentType_Code' => ($type=='DocOprih')?12:2
			);
		}

		return $response;
	}



	/**
	 *	Получение списка параметров хранимой процедуры
	 */
	function getStoredProcedureParamsList($sp, $schema) {
		$query = "
            SELECT
                name as \"name\"
            FROM (
            SELECT
                   unnest(proargnames) as name
            FROM pg_proc p
                 LEFT OUTER JOIN pg_description ds ON ds.objoid = p.oid
                 INNER JOIN pg_namespace n ON p.pronamespace = n.oid
            WHERE p.proname = :name AND
                  n.nspname = :schema
            ) t
            WHERE t.name not in ('pmuser_id', 'error_code', 'error_message', 'isreloadcount')
		";

		$queryParams = array(
			'name' => strtolower($sp),
			'schema' => strtolower($schema)
		);

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$outputData = array();
		$response = $result->result('array');

		foreach ( $response as $row ) {
			$outputData[] = str_replace('@', '', $row['name']);
		}

		return $outputData;
	}

	/**
	 * Удаление произвольного обьекта.
	 */
	function deleteObject($object_name, $data) {
		$key_field = !empty($data['key_field']) ? $data['key_field'] : "{$object_name}_id";

		$query = "
            select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from dbo.p_{$object_name}_del(
				{$key_field} := :{$key_field} )
		";

		if (isset($data['debug_query'])) {
			print getDebugSQL($query, $data);
		}
		$result = $this->getFirstRowFromQuery($query, $data);
		if ($result && is_array($result)) {
			if(empty($result['Error_Msg'])) {
				$result['success'] = true;
			}
			return $result;
		} else {
			return array('Error_Msg' => 'При удалении произошла ошибка');
		}
	}

	/**
	 * Вспомогательная функция преобразования формата даты
	 * Получает строку c датой в формате d.m.Y, возвращает строку с датой в формате Y-m-d
	 */
	function formatDate($date) {
		$d_str = null;
		if (!empty($date)) {
			$date = preg_replace('/\//', '.', $date);
			$d_arr = explode('.', $date);
			if (is_array($d_arr)) {
				$d_arr = array_reverse($d_arr);
			}
			if (count($d_arr) == 3) {
				$d_str = join('-', $d_arr);
			}
		}
		return $d_str;
	}

	/**
	 * Вспомогательная функция получения идентификатора объекта по коду
	 */
	function getObjectIdByCode($object_name, $code) {
		$query = "
			select 
				{$object_name}_id
			from
				v_{$object_name} 
			where
				cast({$object_name}_Code as integer) = :code
			limit 1
		";
		$result = $this->getFirstResultFromQuery($query, array(
			'code' => $code
		));

		return $result && $result > 0 ? $result : false;
	}

	/**
	 * Вспомогательная функция получения имени объекта по коду
	 */
	function getObjectNameByCode($object_name, $code) {
		$query = "
			select 
				{$object_name}_Name
			from
				v_{$object_name} 

			where
				{$object_name}_Code = :code;
			limit 1
		";
		$result = $this->getFirstResultFromQuery($query, array(
			'code' => $code
		));

		return $result ? $result : false;
	}

	/**
	 * Получение сгенерированного номера для произвольного объекта
	 */
	function generateNum($object_name, $num_field) {
		$query = "
			select
				COALESCE(max(cast({$num_field} as bigint)),0) + 1 as \"NextNum\"

			from
				{$object_name}
			where
				{$num_field} not iLIKE '%.%' and

				{$num_field} not iLIKE '%,%' and

				isnumeric({$num_field}) = 1 and
				length({$num_field}) <= 18;
		";

		$result = $this->getFirstResultFromQuery($query);
		if ($result) {
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Подписание ведомости
	 */
	function signWhsDocumentUcInvent($data) {

		// проверка текущего статуса документа
		$query = "
			select
				wdst.WhsDocumentStatusType_Code as \"WhsDocumentStatusType_Code\"
			from
				v_WhsDocumentUcInvent wdui 

				left join v_WhsDocumentStatusType wdst  on wdst.WhsDocumentStatusType_id = wdui.WhsDocumentStatusType_id

			where
				wdui.WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id;
		";

		$result = $this->getFirstResultFromQuery($query, $data);

		// 1 - Новый, 10 - В работе, 11 - Введено
		$approvedStates = array(1,10,11);

		if (
			!$result
			|| ($data['sign'] && !in_array($result, $approvedStates))
			|| (!$data['sign'] && $result != 2) // 2 - Действующий
		) {
			return array(
				array(
					'Error_Msg' => $data['sign']
						? 'Можно утвердить документ только в статусе "Новый", "В работе" или "Введено".'
						: 'Отменить утверждение можно только для документа со статусом "Действующий".'
				)
			);
		}

		// при утверждении проверка остатков,
		// если список медикаментов ведомости пуст, а остатки есть - выдается сообщение
		if ($data['sign']) {

			// получаем список медикаментов ведомости
			$drug_data = $this->loadWhsDocumentUcInventDrugList($data);

			if (count($drug_data) == 0) {

				// получаем количество строк остатков в регистре
				$ost_count = count($this->getDocumentUcInventDrugList($data));

				if ($ost_count > 0) {
					return array(
						array(
							'Error_Msg' => 'Утверждение ведомости не возможно, т.к. остатки склада не пусты. Сформируйте список медикаментов инвентаризационной ведомости.'
						)
					);
				}
			}
		}

		$cancelApprovalCode = 1;

		//при отмене утверждения
		if (!$data['sign']) {

			// дополнительная проверка на наличе связанных документов
			$query = "
				select
					count(du.DocumentUc_id) as \"cnt\"
				from
					v_DocumentUc du 

					left join v_WhsDocumentUcInvent wdui  on wdui.WhsDocumentUc_id = du.WhsDocumentUc_id

				where
					wdui.WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id;
			";

			$result = $this->getFirstRowFromQuery($query, $data);

			if ($result && $result['cnt'] > 0) {

				return array(
					array(
						'Error_Msg' => 'Изменение статуса ведомости не возможно, т.к. есть документы учета, связанные с ведомостью.'
					)
				);
			}

			// а так же определение статуса ведомости после отмены
			$query = "
				select
					count(wduid.WhsDocumentUcInvent_id) as \"TotalDocCount\",
					sum(case when WhsDocumentStatusType_Code = 1::text then 1 else 0 end) as \"DocNewTypeCount\",
    				sum(case when WhsDocumentStatusType_Code = 10::text then 1 else 0 end) as \"DocInWorkTypeCount\",
    				sum(case when WhsDocumentStatusType_Code = 11::text then 1 else 0 end) as \"DocEnteredTypeCount\"
				from
					v_WhsDocumentUcInventDrug as wduid 

					left join v_WhsDocumentStatusType as wdst  on wdst.WhsDocumentStatusType_id = wduid.WhsDocumentStatusType_id

				where
					wduid.WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id;
			";

			$result = $this->getFirstRowFromQuery($query, $data);

			if ($result && $result['TotalDocCount'] > 0) {

				if ($result['DocNewTypeCount'] > 0)
					$cancelApprovalCode = 1; // 1 - Новый
				elseif ($result['DocInWorkTypeCount'] > 0)
					$cancelApprovalCode = 10; // 10 - В работе
				elseif ($result['DocEnteredTypeCount'] > 0)
					$cancelApprovalCode = 11; // 11 - Введено
			}
		}

		// 2 - Действующий;
		$new_code = $data['sign'] ? 2 : $cancelApprovalCode;

		$result = $this->saveObject('WhsDocumentUcInvent', array(

			'enable_data_merging' => true,
			'WhsDocumentUcInvent_id' => $data['WhsDocumentUcInvent_id'],
			'WhsDocumentStatusType_id' => $this->getObjectIdByCode('WhsDocumentStatusType', $new_code),
			'pmUser_id' => $data['pmUser_id']

		));

		if (!empty($result['WhsDocumentUcInvent_id']) && empty($result['Error_Msg'])) {
			$result['WhsDocumentStatusType_Code'] = $new_code;
		}

		return $result;
	}

	/**
	 * Подписание приказа на инвентаризацию
	 */
	function signWhsDocumentUcInventOrder($data) {
		$query = "
			select
				wdst.WhsDocumentStatusType_Code as \"WhsDocumentStatusType_Code\"
			from
				v_WhsDocumentUc wdu 

				left join v_WhsDocumentStatusType wdst  on wdst.WhsDocumentStatusType_id = wdu.WhsDocumentStatusType_id

			where
				wdu.WhsDocumentUc_id = :WhsDocumentUc_id;
		";
		$result = $this->getFirstResultFromQuery($query, $data);
		if (!$result || $result != 1) { //1 - Новый
			return array(array('Error_Msg' => 'Подписать можно только документ со статусом "Новый".'));
		}

		$result = $this->saveObject('WhsDocumentUc', array(
			'enable_data_merging' => true,
			'WhsDocumentUc_id' => $data['WhsDocumentUc_id'],
			'WhsDocumentStatusType_id' => $this->getObjectIdByCode('WhsDocumentStatusType', 2), //2 - Действующий
			'pmUser_id' => $data['pmUser_id']
		));
		return $result;
	}

	/**
	 * Пересчет суммы для конкретной ведомости
	 */
	function updateWhsDocumentUcInventSum($data) {
		$query = "
			select
				sum(COALESCE(wduid.WhsDocumentUcInventDrug_Kolvo, 0) * COALESCE(wduid.WhsDocumentUcInventDrug_Cost, 0)) as \"invent_sum\"

			from
				v_WhsDocumentUcInventDrug wduid 

			where
				wduid.WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id;
		";
		$sum = $this->getFirstResultFromQuery($query, $data);

		$result = $this->saveObject('WhsDocumentUcInvent', array(
			'enable_data_merging' => true,
			'WhsDocumentUcInvent_id' => $data['WhsDocumentUcInvent_id'],
			'WhsDocumentUc_Sum' => $sum > 0 ? $sum : null,
			'pmUser_id' => $data['pmUser_id']
		));
		return $result;
	}

	/**
	 * Получение списка получателей для отправки сообщения
	 */
	function getRecipientForNotice($data) {
		$recipient = array();

		//получаем список пользователей
		$q = "
			select distinct
				uc.PMUser_id as \"PMUser_id\"
			from
				v_WhsDocumentUcInvent wdui 

				left join pmUserCacheOrg uco  on uco.Org_id = wdui.Org_id

				left join pmUserCache uc  on uc.PMUser_id = uco.pmUserCache_id

			where
				wdui.WhsDocumentUc_pid = :WhsDocumentUc_id and
				COALESCE(uc.PMUser_Blocked,0) = 0 and

				(
					uc.pmUser_groups iLIKE '%\"OrgAdmin\"%' or

					uc.pmUser_groups iLIKE '%\"LpuAdmin\"%' or

					uc.pmUser_groups iLIKE '%\"FarmacyAdmin\"%'

				)
		";
		$r = $this->db->query($q, $data);
		if (is_object($r)) {
			$r = $r->result('array');
			for($i = 0; $i < count($r); $i++) {
				$recipient[] = $r[$i]['PMUser_id'];
			}
		}

		return $recipient;
	}

    /**
     * Сохранение фактического количества
     */
    function saveDrugFactKolvo($data) {
        $error = array();
        $result = array();

        //старт транзакции
        $this->beginTransaction();

		// проверяем есть ли наряд
		$query = "
                select
                    dusw.DocumentUcStorageWork_id as \"DocumentUcStorageWork_id\"
                from
                    v_DocumentUcStorageWork dusw 

                where
                    dusw.WhsDocumentUcInventDrug_id = :WhsDocumentUcInventDrug_id
                order by
                    dusw.DocumentUcStorageWork_id
				limit 1
            ";
		$sw_id = $this->getFirstResultFromQuery($query, array(
			'WhsDocumentUcInventDrug_id' => $data['WhsDocumentUcInventDrug_id']
		));

		$statusCode = 1; // 1 - Новый

		if (!empty($data['WhsDocumentUcInventDrug_FactKolvo'])) {
				$statusCode = 11; // 11 - Ввведено
		} else {
			if (!empty($sw_id))
				$statusCode = 10; // 10 - В работе
		}

        $response = $this->saveObject('WhsDocumentUcInventDrug', array(
            'WhsDocumentUcInventDrug_id' => $data['WhsDocumentUcInventDrug_id'],
            'WhsDocumentUcInventDrug_FactKolvo' => $data['WhsDocumentUcInventDrug_FactKolvo'],
			'WhsDocumentStatusType_id' => $this->getObjectIdByCode('WhsDocumentStatusType', $statusCode)
        ));
        if (empty($response['Error_Msg'])) {
            $result = $response;
			$result['WhsDocumentStatusType_Name'] = $this->getObjectNameByCode('WhsDocumentStatusType', $statusCode);
        } else {
            $error[] = $response['Error_Msg'];
        }

        //обновление фактического количества в строках наряда
        if (count($error) == 0) {
            if (!empty($sw_id)) {
                $end_date = $data['WhsDocumentUcInventDrug_FactKolvo'] > 0 ? date("Y-m-d H:i:s") : null;
                $response = $this->saveObject('DocumentUcStorageWork', array(
                    'DocumentUcStorageWork_id' => $sw_id,
                    'DocumentUcStorageWork_FactQuantity' => $data['WhsDocumentUcInventDrug_FactKolvo'] > 0 ? $data['WhsDocumentUcInventDrug_FactKolvo'] : 0,
                    'DocumentUcStorageWork_endDate' => $end_date
                ));
                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                } else {
                    $result['DocumentUcStorageWork_endDate'] = $end_date ? date_create($end_date)->format('d.m.Y H:i:s') : null;
                }
            }
        }

        //откат изменений при наличии ошибок
        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
            $this->rollbackTransaction();
        } else {
            //коммит транзакции
            $result['success'] = true;
            $this->commitTransaction();
        }

        return $result;
    }

	/**
	 * Получение данных для заполнения шаблона сообщения
	 */
	function getDataForNotice($data) {
		//получение данных приказа
		$query = "
			select
				o.Org_Name as \"Org_Name\",
				wdu.WhsDocumentUc_Name as \"WhsDocumentUcInventOrder_Name\"
			from
				v_WhsDocumentUc wdu 

				left join dbo.Org o  on o.Org_id = wdu.Org_aid

			where
				wdu.WhsDocumentUc_id = :WhsDocumentUc_id
		";
		$result = $this->getFirstRowFromQuery($query, $data);
		if (!is_array($result)) {
			return false;
		}

		//получение данных для таблицы
		$query = "
			select
				o.Org_Name as \"Org_Name\",
				s.Storage_Name as \"Storage_Name\",
				to_char(wdui.WhsDocumentUc_Date, 'DD.MM.YYYY') as \"WhsDocumentUc_Date\"

			from
				v_WhsDocumentUcInvent wdui 

				left join dbo.Org o  on o.Org_id = wdui.Org_id

				left join dbo.v_Storage s  on s.Storage_id = wdui.Storage_id

			where
				WhsDocumentUc_pid = :WhsDocumentUc_id;
		";
		$res = $this->db->query($query, $data);

		if (is_object($res)) {
			$result['Invent_Table'] = "";
			$res = $res->result('array');
			if (count($res) > 0) {
				foreach($res as $r) {
					$result['Invent_Table'] .= "<tr><td>{$r['Org_Name']}</td><td>{$r['Storage_Name']}</td><td>{$r['WhsDocumentUc_Date']}</td></tr>";
				}
				$result['Invent_Table'] = "<table class=\"messageTbl\" style=\"margin-top: 5px; margin-bottom: 5px;\"><tr class=\"messageHeader\"><td>Организация</td><td>Склад</td><td>Дата инвентаризации</td></tr>{$result['Invent_Table']}</table>";
			}
			return $result;
		} else {
			return false;
		}
	}

    /**
     * Создание наряда на выполнение работ
     */
    function createDocumentUcStorageWork($data) {
        $error = array();
        $result = array();
		$id_array = array();
        $rec_array = array();

        //проверка списка строк
        if (!empty($data['WhsDocumentUcInventDrug_List'])) {
            $id_array = explode(',', $data['WhsDocumentUcInventDrug_List']);
            foreach($id_array as $id) {
                if (!is_numeric($id)) {
                    $error[] = "Некорректный идентификатор медикамента.";
                    break;
                }
            }
        }

        //получение данных о медикаментах в ведомости
        if (count($error) == 0) {
            $query = "
               select
                    wduid.WhsDocumentUcInventDrug_id as \"WhsDocumentUcInventDrug_id\",
                    wduid.WhsDocumentUcInventDrug_FactKolvo as \"WhsDocumentUcInventDrug_FactKolvo\"
                from
                    v_WhsDocumentUcInventDrug wduid 

                where
                    wduid.WhsDocumentUcInventDrug_id in ({$data['WhsDocumentUcInventDrug_List']}) 
                    and 
                    not exists(
						select * from v_DocumentUcStorageWork 
						where WhsDocumentUcInventDrug_id = wduid.WhsDocumentUcInventDrug_id 
						and DocumentUcStorageWork_endDate is null
					)
            ";
            $rec_array = $this->queryResult($query);
			if (!is_array($rec_array)) {
				$error[] = "Ошибка при получении данных о медикаментах в ведомости.";
			}
            if (count($rec_array) < count($id_array)) {
                $error[] = "По документу есть невыполненные работы. Создание нового наряда невозможно.";
            }
        }

        //старт транзакции
        $this->beginTransaction();

        //формирование строк наряда
        if (count($error) == 0) {
            $save_data = array(
                'DocumentUcTypeWork_id' => $data['DocumentUcTypeWork_id'],
                'Person_cid' => $data['Person_cid'],
                'Post_cid' => $data['Post_cid'],
                'Person_eid' => $data['Person_eid'],
                'Post_eid' => $data['Post_eid']
            );
            foreach($rec_array as $rec) {

				$save_data['WhsDocumentUcInventDrug_id'] = $rec['WhsDocumentUcInventDrug_id'];
                $save_data['DocumentUcStorageWork_FactQuantity'] = $rec['WhsDocumentUcInventDrug_FactKolvo'] > 0 ? $rec['WhsDocumentUcInventDrug_FactKolvo'] : 0;

				$response = $this->saveObject('DocumentUcStorageWork', $save_data);

				if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                    break;
                }

				// 11 - Введено, 10 - В работе
				$statusCode = 11;

				if (empty($rec['WhsDocumentUcInventDrug_FactKolvo']))
					$statusCode = 10;

				$save_data['WhsDocumentStatusType_id'] = $this->getObjectIdByCode('WhsDocumentStatusType', $statusCode);
				$updateStatusResponse = $this->saveObject('WhsDocumentUcInventDrug', $save_data);

				if (!empty($updateStatusResponse['Error_Msg'])) {
					$error[] = $updateStatusResponse['Error_Msg'];
					break;
				}
            }
        }

        //откат изменений при наличии ошибок
        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
            $this->rollbackTransaction();
        } else {
            //коммит транзакции
            $result['success'] = true;
            $this->commitTransaction();
        }

        return $result;
    }

	/**
	 * Редактирование нарядов на выполнение работ
	 */
    function editDocumentUcStorageWork($data) {
		$error = array();
		$result = array();
		$rec_array = array();

		//проверка списка строк
		if (!empty($data['DocumentUcStorageWork_List'])) {
			$id_array = explode(',', $data['DocumentUcStorageWork_List']);
			foreach($id_array as $id) {
				if (!is_numeric($id)) {
					$error[] = "Некорректный идентификатор наряда.";
					break;
				}
			}
		}

		foreach($id_array as $id) {
			$rec_array[] = array('DocumentUcStorageWork_id' => $id);
		}

		//старт транзакции
		$this->beginTransaction();

		//формирование строк наряда
		if (count($error) == 0) {
			$save_data = array(
				'DocumentUcTypeWork_id' => $data['DocumentUcTypeWork_id'],
				'Person_cid' => $data['Person_cid'],
				'Post_cid' => $data['Post_cid'],
				'Person_eid' => $data['Person_eid'],
				'Post_eid' => $data['Post_eid']
			);
			foreach($rec_array as $rec) {
				$save_data['DocumentUcStorageWork_id'] = $rec['DocumentUcStorageWork_id'];
				$response = $this->saveObject('DocumentUcStorageWork', $save_data);
				if (!empty($response['Error_Msg'])) {
					$error[] = $response['Error_Msg'];
					break;
				}
			}
		}

		//откат изменений при наличии ошибок
		if (count($error) > 0) {
			$result['Error_Msg'] = $error[0];
			$this->rollbackTransaction();
		} else {
			//коммит транзакции
			$result['success'] = true;
			$this->commitTransaction();
		}

		return $result;
	}

    /**
     * Удаление наряда на выполнение работ
     */
    function deleteDocumentUcStorageWork($data) {
        $error = array();
        $result = array();
        $rec_array = array();

        //проверка списка строк
        if (!empty($data['WhsDocumentUcInventDrug_List'])) {
            $id_array = explode(',', $data['WhsDocumentUcInventDrug_List']);
            foreach($id_array as $id) {
                if (!is_numeric($id)) {
                    $error[] = "Некорректный идентификатор медикамента.";
                    break;
                }
            }
        }

        //получение данных о нарядах
        if (count($error) == 0) {
            $query = "
                select
                    dusw.DocumentUcStorageWork_id as \"DocumentUcStorageWork_id\",
                    dusw.WhsDocumentUcInventDrug_id as \"WhsDocumentUcInventDrug_id\",
                    wduid.WhsDocumentUcInventDrug_FactKolvo as \"WhsDocumentUcInventDrug_FactKolvo\"
                from
                    v_DocumentUcStorageWork as dusw 

                    left join v_WhsDocumentUcInventDrug as wduid  on wduid.WhsDocumentUcInventDrug_id = dusw.WhsDocumentUcInventDrug_id

                where
                    dusw.WhsDocumentUcInventDrug_id in ({$data['WhsDocumentUcInventDrug_List']});


            ";
            $rec_array = $this->queryResult($query);
        }

        //старт транзакции
        $this->beginTransaction();

        //удаление строк наряда
        if (count($error) == 0) {
            foreach($rec_array as $rec) {

                $response = $this->deleteObject('DocumentUcStorageWork', array(
                    'DocumentUcStorageWork_id' => $rec['DocumentUcStorageWork_id']
                ));

                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                    break;
                }

				// 1 - Новый, 11 - Введено
				$statusCode = 1;

				if (!empty($rec['WhsDocumentUcInventDrug_FactKolvo']))
					$statusCode = 11;

				$save_data['WhsDocumentUcInventDrug_id'] = $rec['WhsDocumentUcInventDrug_id'];
				$save_data['WhsDocumentStatusType_id'] = $this->getObjectIdByCode('WhsDocumentStatusType', $statusCode);
				$updateStatusResponse = $this->saveObject('WhsDocumentUcInventDrug', $save_data);

				if (!empty($updateStatusResponse['Error_Msg'])) {
					$error[] = $updateStatusResponse['Error_Msg'];
					break;
				}
            }
        }

        //откат изменений при наличии ошибок
        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
            $this->rollbackTransaction();
        } else {
            //коммит транзакции
            $result['success'] = true;
            $this->commitTransaction();
        }

        return $result;
    }

    /**
     * Очистка поля "Фактическое количество" в строках ведомости
     */
    function clearDrugFactKolvo($data) {
        $error = array();
        $result = array();

        //проверка списка строк
        if (!empty($data['WhsDocumentUcInventDrug_List'])) {
            $id_array = explode(',', $data['WhsDocumentUcInventDrug_List']);
            foreach($id_array as $id) {
                if (!is_numeric($id)) {
                    $error[] = "Некорректный идентификатор медикамента.";
                    break;
                }
            }
        }

        //старт транзакции
        $this->beginTransaction();

        //очистка количества в строках ведомости
        if (count($error) == 0) {
            $query = "
                select
                    wduid.WhsDocumentUcInventDrug_id as \"WhsDocumentUcInventDrug_id\",
                    dusw.DocumentUcStorageWork_id as \"DocumentUcStorageWork_id\"
                from
                    v_WhsDocumentUcInventDrug as wduid 

                    left join v_DocumentUcStorageWork as dusw  on dusw.WhsDocumentUcInventDrug_id = wduid.WhsDocumentUcInventDrug_id

                where
                    wduid.WhsDocumentUcInventDrug_id in ({$data['WhsDocumentUcInventDrug_List']});
            ";

            $rec_array = $this->queryResult($query);

            foreach($rec_array as $rec) {

				// 1 - Новый, 10 - В работе
				$statusCode = 1;

				if (!empty($rec['DocumentUcStorageWork_id']))
					$statusCode = 10;

				$response = $this->saveObject('WhsDocumentUcInventDrug', array(
                    'WhsDocumentUcInventDrug_id' => $rec['WhsDocumentUcInventDrug_id'],
                    'WhsDocumentUcInventDrug_FactKolvo' => null,
					'WhsDocumentStatusType_id' => $this->getObjectIdByCode('WhsDocumentStatusType', $statusCode)
                ));

                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                    break;
                }
            }
        }

        //очистка количества в нарядах
        if (count($error) == 0) {
            $query = "
                select
                    dusw.DocumentUcStorageWork_id as \"DocumentUcStorageWork_id\"
                from
	                v_DocumentUcStorageWork dusw 

                where
                    dusw.WhsDocumentUcInventDrug_id in ({$data['WhsDocumentUcInventDrug_List']});
            ";
            $rec_array = $this->queryResult($query);
            foreach($rec_array as $rec) {
                $response = $this->saveObject('DocumentUcStorageWork', array(
                    'DocumentUcStorageWork_id' => $rec['DocumentUcStorageWork_id'],
                    'DocumentUcStorageWork_FactQuantity' => 0,
                    'DocumentUcStorageWork_endDate' => null
                ));
                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                    break;
                }
            }
        }

        //откат изменений при наличии ошибок
        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
            $this->rollbackTransaction();
        } else {
            //коммит транзакции
            $result['success'] = true;
            $this->commitTransaction();
        }

        return $result;
    }
	
	/**
	 * обновить статус ведомости
	 */
	function updateStatusDocumentUcInvent($data){
		if(empty($data['WhsDocumentUcInvent_id'])) return false;
		//	Новый – если хотя бы одна запись в списке медикаментов имеет статус «новый».
		//	В работе – устанавливается, если хотя бы одна запись в списке медикаментов имеет статус «в работе» и нет строк со статусом «новый». 
		//	Введено – устанавливается, если все строки списка медикаментов имеют статус «Введено». 
		$newStatus = false;
		$procedure = 'p_WhsDocumentUcInvent_upd';
		$query = "select
			(
				select 
					count(*) AS NN 
				from 
					dbo.v_WhsDocumentUcInventDrug wduid 

					left join dbo.v_WhsDocumentStatusType wdst  on wdst.WhsDocumentStatusType_id = wduid.WhsDocumentStatusType_id

				where wduid.WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id and wdst.WhsDocumentStatusType_Code=1
			) as \"statusNuw\",
			(
				select 
					count(*) AS NN
				from 
					dbo.v_WhsDocumentUcInventDrug wduid 

					left join dbo.v_WhsDocumentStatusType wdst  on wdst.WhsDocumentStatusType_id = wduid.WhsDocumentStatusType_id

				 where wduid.WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id and wdst.WhsDocumentStatusType_Code=10
			 ) as \"statusInWork\",
			case 
				when 
					(SELECT count(*) AS NN 
					from dbo.v_WhsDocumentUcInventDrug wduid 

					where wduid.WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id
                    )
					 = 
					(SELECT count(*) AS NN 
					from dbo.v_WhsDocumentUcInventDrug wduid 

					left join dbo.v_WhsDocumentStatusType wdst  on wdst.WhsDocumentStatusType_id = wduid.WhsDocumentStatusType_id

					where wduid.WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id and wdst.WhsDocumentStatusType_Code=11
			)
				then 1
				else 0
			end as \"isStatusIntroduced\"";
		$result = $this->getFirstRowFromQuery($query, $data);
		if (!is_array($result)) {
			return array(
				array('Error_Msg' => 'Не удалось получить данные списков медикаментов.')
			);
		}
		
		if(!empty($result['statusNuw'])){
			$newStatus = 1;
		}elseif (!empty($result['statusInWork'])) {
			$newStatus = 10;
		}elseif (!empty($result['isStatusIntroduced'])) {
			$newStatus = 11;
		}else{
			//ничего не делаем
			return array(
				array('Error_Msg' => 'Условий для обновления статуса нет.')
			);
		}
		
		if(!$newStatus) return false;
		$data['WhsDocumentStatusType_id'] = $newStatus;
		//получение данных ведомости
		$query = "
			select
				*
			from
				 v_WhsDocumentUcInvent wdui 

			where
				wdui.WhsDocumentUcInvent_id = :WhsDocumentUcInvent_id;
		";
		$result = $this->getFirstRowFromQuery($query, $data);
		if (!is_array($result) || count($result)<0) {
			return array(
				array('Error_Msg' => 'Не удалось получить данные инвентаризационной ведомости.')
			);
		}
		
		$params_list = $this->getStoredProcedureParamsList($procedure, 'dbo');
		$saveParams = array();
		$query_part = "";
		
		foreach($result as $key => $value) {
			if (in_array($key, $params_list)) {
				if (is_object($result[$key]) && get_class($result[$key]) == 'DateTime'){
					$value = $result[$key]->format('Y-m-d H:i:s');
				}
				$saveParams[$key] = $value;
				if($key != 'WhsDocumentUcInvent_id') $query_part .= "{$key} = :{$key}, ";
			}
		}
		$saveParams['WhsDocumentStatusType_id'] = $newStatus;
		$saveParams['pmUser_id'] = $data['pmUser_id'];
		
		$q = "
			select WhsDocumentUcInvent_id as \"WhsDocumentUcInvent_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from dbo.p_WhsDocumentUcInvent_upd(
				WhsDocumentUcInvent_id := :WhsDocumentUcInvent_id,
				{$query_part}
			);
		";
		//echo getDebugSQL ($q, $saveParams); exit;
		$r = $this->db->query($q, $saveParams);
		if ( is_object($r) ) {
			$result = $r->result('array');
		} else {
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 *  Получение списка отделений для фильтрации списка инвентаризационных ведомостей
	 */
	function loadLpuSectionCombo($data) {
		$params = array();
		$with = array();
		$join = array();
		$where = array();

		if (!empty($data['LpuSection_id'])) {
			$where[] = "ls.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		} else {
			if (!empty($data['LpuBuilding_id'])) {
				$where[] = "ls.LpuBuilding_id = :LpuBuilding_id";
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
			}
			if (!empty($data['Storage_id'])) {
				$with[] = "
					recursive storage_tree (Storage_id, Storage_pid) as (
						select
							i_s.Storage_id,
							i_s.Storage_pid
						from
							v_Storage i_s 

						where
							i_s.Storage_id = :Storage_id 
						union all
						select
							i_s.Storage_id,
							i_s.Storage_pid
						from
							v_Storage i_s 

							inner join storage_tree i_tr on i_s.Storage_pid = i_tr.Storage_id
					)
				";
				$with[] = "
					ls_list as (
						select distinct
							p.LpuSection_id
						from (
							select distinct
								ssl.LpuSection_id
							from
								storage_tree st
								left join v_StorageStructLevel ssl  on ssl.Storage_id = st.Storage_id

							where
								ssl.LpuSection_id is not null
							union all
							select distinct
								ls.LpuSection_id
							from
								storage_tree st
								left join v_StorageStructLevel ssl  on ssl.Storage_id = st.Storage_id

								left join v_LpuSection ls  on ls.LpuUnit_id = ssl.LpuUnit_id

							where
								ssl.LpuSection_id is null and
								ssl.LpuUnit_id is not null
							union all
							select distinct
								ls.LpuSection_id
							from
								storage_tree st
								left join v_StorageStructLevel ssl  on ssl.Storage_id = st.Storage_id

								left join v_LpuSection ls  on ls.LpuBuilding_id = ssl.LpuBuilding_id

							where
								ssl.LpuUnit_id is null and
								ssl.LpuBuilding_id is not null
							union all	
							select distinct
								ls.LpuSection_id
							from
								storage_tree st
								left join v_StorageStructLevel ssl  on ssl.Storage_id = st.Storage_id

								left join v_LpuSection ls  on ls.Lpu_id = ssl.Lpu_id

							where
								ssl.LpuBuilding_id is null and
								ssl.Lpu_id is not null
						) p
						where
							p.LpuSection_id is not null
					)
				";
				$join[] = "left join ls_list on ls_list.LpuSection_id = ls.LpuSection_id";
				$where[] = "ls_list.LpuSection_id is not null";
				$params['Storage_id'] = $data['Storage_id'];
			}
			if (!empty($data['query'])) {
				$where[] = "ls.LpuSection_Code||' '||ls.LpuSection_Name iLIKE :query";

				$params['query'] = "%".$data['query']."%";
			}
		}

		$with_clause = implode(', ', $with);
		if (strlen($with_clause)) {
			$with_clause = "
				with {$with_clause}
			";
		}

		$join_clause = implode(' ', $join);

		$where_clause = implode(" and ", $where);
		if (strlen($where_clause)) {
			$where_clause = "
				where
					{$where_clause}
			";
		}

		$query = "
			{$with_clause}
			select 
				ls.LpuSection_id as \"LpuSection_id\",
				ls.LpuSection_Code as \"LpuSection_Code\",
				ls.LpuSection_Name as \"LpuSection_Name\"
			from
				v_LpuSection ls 

				{$join_clause}
			{$where_clause}
			order by
				ls.LpuSection_Name
			limit 250
		";
		$result = $this->queryResult($query, $params);
		return $result;
	}

	/**
	 *  Получение списка отделений для фильтрации списка инвентаризационных ведомостей
	 */
	function loadStorageCombo($data) {
		$params = array();
		$where = array();

		if (!empty($data['Storage_id'])) {
			$where[] = "s.Storage_id = :Storage_id";
			$params['Storage_id'] = $data['Storage_id'];
		} else {
			if (!empty($data['LpuSection_id']) || !empty($data['OrgStruct_id'])) {
				$struct_where = array();

				if (!empty($data['LpuBuilding_id'])) {
					$struct_where[] = "ssl.LpuBuilding_id = :LpuBuilding_id";
					$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
				}
				if (!empty($data['LpuSection_id'])) {
					$struct_where[] = "ssl.LpuSection_id = :LpuSection_id";
					$params['LpuSection_id'] = $data['LpuSection_id'];
				}
				if (!empty($data['OrgStruct_id'])) {
					$struct_where[] = "ssl.OrgStruct_id = :OrgStruct_id";
					$params['OrgStruct_id'] = $data['OrgStruct_id'];
				}
				if (!empty($data['MedServiceStorage_id'])) {
					$query = "
						with recursive storage_tree (Storage_id, Storage_pid) as (
							select
								i_s.Storage_id,
								i_s.Storage_pid
							from
								v_Storage i_s 

							where
								i_s.Storage_id = :MedServiceStorage_id 
							union all
							select
								i_s.Storage_id,
								i_s.Storage_pid
							from
								v_Storage i_s 

								inner join storage_tree i_tr on i_s.Storage_pid = i_tr.Storage_id
						)
						select distinct
							ssl.LpuSection_id as \"LpuSection_id\"
						from
							storage_tree st
							left join v_StorageStructLevel ssl  on ssl.Storage_id = st.Storage_id

						where
							ssl.LpuSection_id is null
					";
					$ls_list = $this->queryList($query, array(
						'MedServiceStorage_id' => $data['MedServiceStorage_id']
					));
					$ls_list_str = count($ls_list) > 0 ? implode(", ", $ls_list) : "0";
					$struct_where[] = "ssl.LpuSection_id in ({$ls_list_str})";
				}

				$struct_where_clause = implode(" and ", $struct_where);
				if (strlen($struct_where_clause)) {
					$struct_where_clause = "
						where
							{$struct_where_clause}
					";
				}
				if (count($struct_where) > 0) {
					$where[] = "s.Storage_id in (
						select
							ssl.Storage_id
						from
							v_StorageStructLevel ssl 

						{$struct_where_clause}
					)";
				}
			}

			if (!empty($data['query'])) {
				$where[] = "s.Storage_Code||' '||s.Storage_Name iLIKE :query";

				$params['query'] = "%".$data['query']."%";
			}
		}

		$where_clause = implode(" and ", $where);
		if (strlen($where_clause)) {
			$where_clause = "
				where
					{$where_clause}
			";
		}

		$query = "
			select 
				s.Storage_id,
				s.Storage_Code,
				s.Storage_Name
			from
				v_Storage s 

			{$where_clause}
			order by
				s.Storage_Name
			limit 250
		";
		$result = $this->queryResult($query, $params);
		return $result;
	}
}