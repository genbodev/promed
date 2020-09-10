<?php defined('BASEPATH') or die ('No direct script access allowed');

class WhsDocumentOrderAllocationDrug_model extends swPgModel {

	/**
	 *  Конструктор
	 */
	function __construct(){
		parent::__construct();
	}

	/**
	 *  Загрузка списка позиций разнарядки
	 */
	function loadWhsDocumentOrderAllocationDrugGrid($data) {
		$params = array();
		$filter="(1=1)";
		
		$params['WhsDocumentOrderAllocation_id'] = $data['WhsDocumentOrderAllocation_id'];
		$filter .= " and wdoad.WhsDocumentOrderAllocation_id = :WhsDocumentOrderAllocation_id";
		
		$query = "
			Select
                   -- select
                   wdoad.WhsDocumentOrderAllocationDrug_id as \"WhsDocumentOrderAllocationDrug_id\",
                   svod_allocation.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
                   d.Drug_Name as \"Drug_Name\",
                   d.Drug_id as \"Drug_id\",
                   am.RUSNAME as \"ActMatters_RusName\", --МНН
                   tn.NAME as \"TradeName_Name\", --Лекарственный препарат
                   df.NAME as \"DrugForm_Name\", --Форма выпуска
                   Dose.Value as \"Drug_Dose\", --Дозировка
                   Fas.Value as \"Drug_Fas\", --Фасовка
                   rc.REGNUM as \"Reg_Num\", --№ РУ
                   fn.NAME as \"Firm_Name\", --Производитель
                   ltrim(CAST(CAST(wdoad.WhsDocumentOrderAllocationDrug_Kolvo as numeric(10, 0)) as varchar)) as \"WhsDocumentOrderAllocationDrug_Kolvo\",
                   ltrim(CAST(CAST(wdoad.WhsDocumentOrderAllocationDrug_Price as numeric(10, 2)) as varchar)) as \"WhsDocumentOrderAllocationDrug_Price\",
                   ltrim(CAST(CAST(wdoad.WhsDocumentOrderAllocationDrug_Price * wdoad.WhsDocumentOrderAllocationDrug_Kolvo as numeric(10, 2)) as varchar)) as \"WhsDocumentOrderAllocationDrug_Sum\"
                   -- end select
            from
                 -- from
                 v_WhsDocumentOrderAllocationDrug wdoad -- содержимое разнарядки
                 left join v_WhsDocumentOrderAllocation wdoa on wdoa.WhsDocumentOrderAllocation_id = wdoad.WhsDocumentOrderAllocation_id
                 left join rls.v_Drug d on d.Drug_id = wdoad.Drug_id
                 left join rls.Nomen n on n.NOMEN_ID = d.Drug_id
                 left join rls.Prep p on p.Prep_id = n.PREPID
                 left join rls.PREP_ACTMATTERS pa on pa.PREPID = n.PREPID
                 left join rls.TRADENAMES tn on tn.TRADENAMES_ID = p.TRADENAMEID
                 left join rls.CLSDRUGFORMS df on df.CLSDRUGFORMS_ID = p.DRUGFORMID
                 left join rls.REGCERT rc on rc.REGCERT_ID = p.REGCERTID
                 left join rls.FIRMS f on f.FIRMS_ID = p.FIRMID
                 left join rls.FIRMNAMES fn on fn.FIRMNAMES_ID = f.NAMEID
                 left join rls.ACTMATTERS am on am.ACTMATTERS_ID = pa.MATTERID
                 left join rls.MASSUNITS mu on mu.MASSUNITS_ID = n.PPACKMASSUNID
                 left join rls.CUBICUNITS cu on cu.CUBICUNITS_ID = n.PPACKCUBUNID
                 left join rls.MASSUNITS df_mu on df_mu.MASSUNITS_ID = p.DFMASSID
                 left join rls.CONCENUNITS df_cu on df_cu.CONCENUNITS_ID = p.DFCONCID
                 left join rls.ACTUNITS df_au on df_au.ACTUNITS_ID = p.DFACTID
                 left join rls.SIZEUNITS df_su on df_su.SIZEUNITS_ID = p.DFSIZEID
                 LEFT JOIN LATERAL
                 (
                   select coalesce(cast (cast (p.DFMASS as decimal) as varchar) || ' ' || df_mu.SHORTNAME, cast (cast (p.DFCONC as decimal) as varchar) || ' ' || df_cu.SHORTNAME, cast (p.DFACT as varchar) || ' ' || df_au.SHORTNAME, cast (p.DFSIZE as varchar) || ' ' || df_su.SHORTNAME) as Value
                 ) Dose ON true
                 LEFT JOIN LATERAL
                 (
                   select ((case
                              when D.Drug_Fas is not null then cast (D.Drug_Fas as varchar) || ' доз'
                              else ''
                            end) ||(case
                                     when D.Drug_Fas is not null and coalesce(D.Drug_Volume, D.Drug_Mass) is not null then ', '
                                     else ''
                                   end) ||(case
                                            when coalesce(D.Drug_Volume, D.Drug_Mass) is not null then coalesce(D.Drug_Volume, D.Drug_Mass)
                                            else ''
                                          end)) as Value
                 ) Fas ON true
                 LEFT JOIN LATERAL
                 (
                   select wds.WhsDocumentUc_Num
                   from v_WhsDocumentOrderAllocation wdoa1
                        left join v_WhsDocumentOrderAllocationDrug wdoad1 on wdoad1.WhsDocumentOrderAllocation_id = wdoa1.WhsDocumentOrderAllocation_id
                        left join v_WhsDocumentSupply wds on wds.WhsDocumentUc_id = wdoad1.WhsDocumentUc_pid
                   where wdoa1.WhsDocumentOrderAllocation_id = wdoa.WhsDocumentUc_pid and
                         wdoad1.Drug_id = wdoad.Drug_id
                   limit 1
                 ) svod_allocation ON true
                 -- end from                 
                 where
                 -- where
                    {$filter}
                 -- end where
            order by
                     -- order by
                     wdoad.WhsDocumentOrderAllocationDrug_id
                     -- end order by";
		/*
		 echo getDebugSql(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		 echo getDebugSql(getCountSQLPH($query), $params);
		 exit;
		*/
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
	 *  Получение списка заявок
	 */
	function loadDrugRequestList($data) {
		$filter = "1=1";
		$filter .= " and DR.DrugRequestCategory_id = 3"; // категория - "сводная заявка"
		$filter .= " and DR.DrugRequestStatus_id = 3"; // статус - "утвержденная"
		
		$filter .= "
			and exists(
                select WDS.WhsDocumentSupply_id
                from v_WhsDocumentSupply WDS -- все гк с указанным источником финансирования и статьёй расхода
                     inner join v_WhsDocumentSupplySpec WDSS ON WDSS.WhsDocumentSupply_id = WDS.WhsDocumentSupply_id -- спецификация ГК
                     inner join v_DrugShipment ds on ds.WhsDocumentSupply_id = WDSS.WhsDocumentSupply_id -- партии в ГК
                     inner join v_DrugOstatRegistry DOR ON DOR.DrugShipment_id = ds.DrugShipment_id and DOR.Drug_id = WDSS.Drug_id -- ЛС на регистре остатков
 and DOR.SubAccountType_id = 1 -- субсчёт доступно
 and DOR.Org_id =:Org_id -- организация пользователя (минздрав)
                     inner join WhsDocumentUc wdu on wdu.WhsDocumentUc_id = wds.WhsDocumentUc_pid and wdu.WhsDocumentType_id = 5 -- лоты для ГК
                     inner join v_DrugRequestPurchaseSpec drps on drps.WhsDocumentUc_id = wdu.WhsDocumentUc_id -- позиции сводной заявки
                where 
                	  WDS.DrugFinance_id =:DrugFinance_id and
                      WDS.WhsDocumentCostItemType_id =:WhsDocumentCostItemType_id and
                      drps.DrugRequest_id = DR.DrugRequest_id
                limit 1)
		";
		
		$data['Org_id'] = $data['session']['org_id'];
		
		if (!empty($data['begDate']) && !empty($data['endDate'])) {
			$filter .= "
				and DR.DrugRequest_id in (
                    select DrugRequestPurchase.DrugRequest_id
                    from DrugRequestPurchase
                         left join DrugRequest on DrugRequest.DrugRequest_id = DrugRequestPurchase.DrugRequest_lid
                    where DrugRequestPeriod_id in (
                                                    select DrugRequestPeriod_id
                                                    from DrugRequestPeriod
                                                    where DrugRequestPeriod_begDate <=:endDate and
                                                          DrugRequestPeriod_endDate >=:begDate
                          ))			
			";
		}
		
		$query = "
			select
				DR.DrugRequest_id
				,DR.DrugRequest_Name
			from
				DrugRequest DR 

				left join DrugRequestPeriod DRP  on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id

			where
				{$filter}
		";
		// echo getDebugSql($query, $data);
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Формирование первоначальной сводной разнарядки
	 */
	function loadRAWList($filter) {
		$q = "
            select wds.DrugFinance_id as \"DrugFinance_id\",
                   wds.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
                   WDS.WhsDocumentUc_id as \"WhsDocumentUc_pid\",
                   WDS.WhsDocumentUc_Num as \"WhsDocumentUc_Name\",
                   COALESCE(Supplier.Org_Name, '') as \"Supplier_Name\",
                   dor.Drug_id as \"Drug_id\",
                   dor.Okei_id as \"Okei_id\",
                   am.RUSNAME as \"Actmatters_RusName\",
                   tn.NAME as \"Tradenames_Name\",
                   dform.NAME as \"DrugForm_Name\",
                   dose.Value as \"Drug_Dose\",
                   fas.Value as \"Drug_Fas\",
                   dor.DrugOstatRegistry_Kolvo as \"Available_Kolvo\",
                   CEILING(DOR.DrugOstatRegistry_Kolvo * :WhsDocumentOrderAllocation_Percent / 100) as \"WhsDocumentOrderAllocationDrug_Kolvo\",
                   :WhsDocumentOrderAllocation_Percent as \"WhsDocumentOrderAllocation_Percent\",
                   CAST(CAST(COALESCE(dor.DrugOstatRegistry_Cost, 0) as  numeric(10, 2)) as varchar) as \"WhsDocumentOrderAllocationDrug_Price\",
                   rc.REGNUM as \"Reg_Num\",
                   rceff.FULLNAME as \"Reg_Firm\",
                   rceffc.NAME as \"Reg_Country\",
                   (to_char(rc.REGDATE, 'DD.MM.YYYY') || COALESCE(' - ' || to_char(rc.ENDDATE, 'DD.MM.YYYY'), '')) as \"Reg_Period\",
                   to_char(rc.Reregdate, 'DD.MM.YYYY') as \"Reg_ReRegDate\",
                   'add' as \"state\"
            from DrugOstatRegistry dor
                 left join SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
                 left join rls.v_Drug d on d.Drug_id = dor.Drug_id
                 left join v_DrugShipment ds on ds.DrugShipment_id = dor.DrugShipment_id
                 left join v_WhsDocumentSupply wds on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
                 left join v_Org Supplier on Supplier.Org_id = wds.Org_sid
                 left join rls.v_prep p on p.Prep_id = d.DrugPrep_id
                 left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                 left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                 left join rls.v_ACTMATTERS am on am.ACTMATTERS_ID = dcmn.Actmatters_id
                 left join rls.v_TRADENAMES tn on tn.TRADENAMES_ID = p.TRADENAMEID
                 left join rls.v_CLSDRUGFORMS dform on dform.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
                 left join rls.REGCERT rc on rc.REGCERT_ID = p.REGCERTID
                 left join rls.REGCERT_EXTRAFIRMS rcef on rcef.CERTID = rc.REGCERT_ID
                 left join rls.v_FIRMS rceff on rceff.FIRMS_ID = rcef.FIRMID
                 left join rls.v_COUNTRIES rceffc on rceffc.COUNTRIES_ID = rceff.COUNTID
                 left join rls.MASSUNITS dfmu on dfmu.MASSUNITS_ID = p.DFMASSID
                 left join rls.CONCENUNITS dfcu on dfcu.CONCENUNITS_ID = p.DFCONCID
                 left join rls.ACTUNITS dfau on dfau.ACTUNITS_ID = p.DFACTID
                 left join rls.SIZEUNITS dfsu on dfsu.SIZEUNITS_ID = p.DFSIZEID
                 left join rls.v_Nomen n on n.NOMEN_ID = dor.Drug_id
                 left join rls.MASSUNITS mu on mu.MASSUNITS_ID = n.PPACKMASSUNID
                 left join rls.CUBICUNITS cu on cu.CUBICUNITS_ID = n.PPACKCUBUNID
                 LEFT JOIN LATERAL
                 (
                   select coalesce(cast (cast (p.DFMASS as decimal) as varchar) || ' ' || dfmu.SHORTNAME, cast (cast (p.DFCONC as decimal) as varchar) || ' ' || dfcu.SHORTNAME, cast (p.DFACT as varchar) || ' ' || dfau.SHORTNAME, cast (p.DFSIZE as varchar) || ' ' || dfsu.SHORTNAME) as Value
                 ) dose ON true
                 LEFT JOIN LATERAL
                 (
                   select ((case
                              when d.Drug_Fas is not null then cast (d.Drug_Fas as varchar) || ' доз'
                              else ''
                            end) ||(case
                                     when d.Drug_Fas is not null and coalesce(d.Drug_Volume, d.Drug_Mass) is not null then ', '
                                     else ''
                                   end) ||(case
                                            when coalesce(d.Drug_Volume, d.Drug_Mass) is not null then coalesce(d.Drug_Volume, d.Drug_Mass)
                                            else ''
                                          end)) as Value
                 ) fas ON true
            where dor.Org_id = dbo.GetMinzdravDloOrgId() and
                  dor.DrugOstatRegistry_Kolvo > 0 and
                  sat.SubAccountType_Code = 1 and
                  wds.DrugFinance_id =:DrugFinance_id and
                  wds.WhsDocumentCostItemType_id =:WhsDocumentCostItemType_id and
                  wds.WhsDocumentUc_pid is not null
		";

		//echo getDebugSql($q, $filter); die();
		$result = $this->db->query($q, $filter);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Формирование первоначального списка медикамента, подлежащих распределению (план поставок)
	 */
	function loadRAWSupList($filter) {
		$q = "
			select dor.Drug_id as \"Drug_id\",
                   max(dor.Okei_id) as \"Okei_id\",
                   d.Drug_Name as \"Drug_Name\",
                   sum(DOR.DrugOstatRegistry_Kolvo) as \"WhsDocumentOrderAllocationDrug_Allocation\",
                   CAST(CAST(COALESCE(avg(wdss.WhsDocumentSupplySpec_PriceNDS), 0) as numeric(10, 2)) as varchar) as \"WhsDocumentOrderAllocationDrug_PriceNDS\",
                   null as \"Plan_Kolvo\",
                   'add' as \"state\"
            from DrugOstatRegistry dor
                 left join SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
                 left join rls.v_Drug d ON d.Drug_id = dor.Drug_id
                 left join v_DrugShipment ds on ds.DrugShipment_id = dor.DrugShipment_id
                 left join v_WhsDocumentSupply wds on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
                 LEFT JOIN LATERAL
                 (
                   select WhsDocumentSupplySpec_PriceNDS
                   from v_WhsDocumentSupplySpec
                   where WhsDocumentSupply_id = ds.WhsDocumentSupply_id and
                         Drug_id = dor.Drug_id
                   limit 1
                 ) wdss ON true
            where dor.Org_id =:Org_id and
                  dor.DrugOstatRegistry_Kolvo > 0 and
                  sat.SubAccountType_Code = 1 and
                  wds.DrugFinance_id =:DrugFinance_id and
                  wds.WhsDocumentCostItemType_id =:WhsDocumentCostItemType_id and
                  wds.WhsDocumentUc_pid is not null
            group by dor.Drug_id,
                     d.Drug_Name
            order by d.Drug_Name;
		";

		//echo getDebugSql($q, $filter); die();
		$result = $this->db->query($q, $filter);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Формирование первоначального списка медикамента, подлежащих распределению (распределение ЛС по аптекам)
	 */
	function loadRAWFarmDrugList($filter) {
		$q = "
			select wdord.Drug_id as \"Drug_id\",
                   wdord.Okei_id as \"Okei_id\",
                   d.Drug_Name as \"Drug_Name\",
                   ltrim(CAST(CAST(sum(wdord.WhsDocumentOrderAllocationDrug_Kolvo) as numeric(10, 0)) as varchar)) as \"WhsDocumentOrderAllocationDrug_Kolvo\",
                   ltrim(CAST(CAST(sum(wdord.WhsDocumentOrderAllocationDrug_Price * wdord.WhsDocumentOrderAllocationDrug_Kolvo) / sum(wdord.WhsDocumentOrderAllocationDrug_Kolvo) as numeric(10, 2)) as varchar)) as \"WhsDocumentOrderAllocationDrug_Price\",
                   ltrim(CAST(CAST(sum(wdord.WhsDocumentOrderAllocationDrug_Price * wdord.WhsDocumentOrderAllocationDrug_Kolvo) as numeric(10, 2)) as varchar)) as \"WhsDocumentUc_Sum\",
                   'add' as \"state\"
            from v_WhsDocumentOrderAllocationDrug wdord
                 left join v_WhsDocumentOrderAllocation wdoa on wdoa.WhsDocumentOrderAllocation_id = wdord.WhsDocumentOrderAllocation_id
                 left join rls.v_Drug d on d.Drug_id = wdord.Drug_id
                 left join v_WhsDocumentSupply wds on wds.WhsDocumentUc_id = wdord.WhsDocumentUc_pid
            where wdord.WhsDocumentOrderAllocation_id in (
                                                           select WhsDocumentOrderAllocation_id
                                                           from v_WhsDocumentOrderAllocation
                                                           where WhsDocumentUc_pid =:WhsDocumentUc_id
                  ) and
                  wds.Org_sid =:Org_id
            group by wdord.Drug_id,
                     wdord.Okei_id,
                     d.Drug_Name;
		";

		//echo getDebugSql($q, $filter); die();
		$result = $this->db->query($q, $filter);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Формирование первоначальной разнарядки МО
	 */
	function loadRAWListMO($filter) {
		$q = "
			with req_data as ( -- данные из заявок врачей
                select dr.Lpu_id,
                       drr.DrugComplexMnn_id,
                       sum(drr.DrugRequestRow_Kolvo) as kolvo
                from v_DrugRequestPurchase drp
                     left join v_DrugRequest regdr on regdr.DrugRequest_id = drp.DrugRequest_lid
                     left join v_DrugRequest dr on dr.DrugRequest_Version is null and dr.DrugRequestPeriod_id = regdr.DrugRequestPeriod_id and COALESCE(dr.PersonRegisterType_id, 0) = COALESCE(regdr.PersonRegisterType_id, 0) and COALESCE(dr.DrugRequestKind_id, 0) = COALESCE(regdr.DrugRequestKind_id, 0) and COALESCE(dr.DrugGroup_id, 0) = COALESCE(regdr.DrugGroup_id, 0) and dr.Lpu_id is not null and dr.MedPersonal_id is not null
                     left join v_DrugRequestRow drr on drr.DrugRequest_id = dr.DrugRequest_id
                     inner join v_Lpu l on l.Lpu_id = dr.Lpu_id
                where drp.DrugRequest_id =:DrugRequest_id and
                      drr.DrugFinance_id =:DrugFinance_id and
                      drr.DrugComplexMnn_id is not null
                group by dr.Lpu_id,
                         drr.DrugComplexMnn_id),
            svod_data as ( 
            -- данные из сводной заявки
                select DrugComplexMnn_id,
                       DrugRequestPurchaseSpec_Kolvo
                from DrugRequestPurchaseSpec
                where DrugRequest_id =:DrugRequest_id and
                      DrugFinance_id =:DrugFinance_id
			)
            select data.Lpu_id as \"Lpu_id\",
                   o.Org_id as \"Org_id\",
                   o.Org_Name as \"Lpu_Name\",
                   data.request_kolvo as \"Kolvo_Request\",
                   data.total_request_kolvo as \"Kolvo_Available\",
                   data.total_kolvo as \"WhsDocumentOrderAllocationDrug_Kolvo\",
                   data.WhsDocumentSupply_id as \"WhsDocumentUc_pid\",
                   data.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
                   COALESCE(Supplier.Org_Name, '') as \"Supplier_Name\",
                   data.Drug_id as \"Drug_id\",
                   data.Okei_id as \"Okei_id\",
                   tn.NAME as \"Tradenames_Name\",
                   dform.NAME as \"DrugForm_Name\",
                   dose.Value as \"Drug_Dose\",
                   fas.Value as \"Drug_Fas\",
                   round(CAST((data.total_kolvo * 100) / data.request_kolvo as numeric), 2) as \"WhsDocumentOrderAllocationDrug_Percent\",
                   COALESCE(data.price, '0') as \"WhsDocumentOrderAllocationDrug_Price\",
                   COALESCE(data.total_kolvo * CAST(data.price as numeric), 0) as \"WhsDocumentUc_Sum\",
                   rc.REGNUM as \"Reg_Num\",
                   rceff.FULLNAME as \"Reg_Firm\",
                   rceffc.NAME as \"Reg_Country\",
                   (to_char(rc.REGDATE, 'DD.MM.YYYY') || COALESCE(' - ' || to_char(rc.ENDDATE, 'DD.MM.YYYY'), '')) as \"Reg_Period\",
                   to_char(rc.Reregdate, 'DD.MM.YYYY') as \"Reg_ReRegDate\",
                   'add' as \"state\"
            from (
                   select con.WhsDocumentSupply_id,
                          con.WhsDocumentUc_Num,
                          con.Org_sid,
                          con.Drug_id,
                          con.Okei_id,
                          con.mnn_id,
                          con.price,
                          req_data.Lpu_id,
                          con.kolvo as allocation_kolvo,
                          req_data.kolvo as request_kolvo,
                          svod_data.DrugRequestPurchaseSpec_Kolvo as total_request_kolvo,
                          round(CAST(case
                                  when svod_data.DrugRequestPurchaseSpec_Kolvo <= con.kolvo -- если в сводной разнарядке медикамента хватает на полное покрытие потребностей
                          then req_data.kolvo
                                  else (con.kolvo * req_data.kolvo) / svod_data.DrugRequestPurchaseSpec_Kolvo
                                end as numeric), 0) as total_kolvo
                   from (
                          select wds.WhsDocumentSupply_id,
                                 wds.WhsDocumentUc_Num,
                                 wds.Org_sid,
                                 dor.Drug_id,
                                 max(dor.Okei_id) as Okei_id,
                                 max(d.DrugComplexMnn_id) as mnn_id,
                                 ceiling(sum(DOR.DrugOstatRegistry_Kolvo) *:WhsDocumentOrderAllocation_Percent / 100) as kolvo,
                                 CAST(CAST(COALESCE(dor.DrugOstatRegistry_Cost, 0) as numeric(10, 2)) as varchar) as price
                          from DrugOstatRegistry dor
                               left join SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
                               left join rls.v_Drug d ON d.Drug_id = dor.Drug_id
                               left join v_DrugShipment ds on ds.DrugShipment_id = dor.DrugShipment_id
                               left join v_WhsDocumentSupply wds on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
                               left join v_WhsDocumentSupplySpec wdss on wdss.WhsDocumentSupply_id = ds.WhsDocumentSupply_id and wdss.Drug_id = dor.Drug_id
                          where dor.DrugOstatRegistry_Kolvo > 0 and
                                dor.Org_id = dbo.GetMinzdravDloOrgId() and
                                sat.SubAccountType_Code = 1 and
                                wds.DrugFinance_id =:DrugFinance_id and
                                wds.WhsDocumentCostItemType_id =:WhsDocumentCostItemType_id and
                                wds.WhsDocumentUc_pid is not null
                          group by wds.WhsDocumentSupply_id,
                                   wds.WhsDocumentUc_Num,
                                   wds.Org_sid,
                                   dor.Drug_id,
                                   dor.DrugOstatRegistry_Cost
                        ) con
                        left join req_data on req_data.DrugComplexMnn_id = con.mnn_id
                        left join svod_data on svod_data.DrugComplexMnn_id = con.mnn_id
                   where req_data.kolvo > 0
                 ) data
                 left join rls.v_Drug d on d.Drug_id = data.Drug_id
                 left join Lpu l on l.Lpu_id = data.Lpu_id
                 left join Org o on o.Org_id = l.Org_id
                 left join v_Org Supplier on Supplier.Org_id = data.Org_sid
                 left join rls.v_prep p on p.Prep_id = d.DrugPrep_id
                 left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                 left join rls.v_TRADENAMES tn on tn.TRADENAMES_ID = p.TRADENAMEID
                 left join rls.v_CLSDRUGFORMS dform on dform.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
                 left join rls.REGCERT rc on rc.REGCERT_ID = p.REGCERTID
                 left join rls.REGCERT_EXTRAFIRMS rcef on rcef.CERTID = rc.REGCERT_ID
                 left join rls.v_FIRMS rceff on rceff.FIRMS_ID = rcef.FIRMID
                 left join rls.v_COUNTRIES rceffc on rceffc.COUNTRIES_ID = rceff.COUNTID
                 left join rls.MASSUNITS dfmu on dfmu.MASSUNITS_ID = p.DFMASSID
                 left join rls.CONCENUNITS dfcu on dfcu.CONCENUNITS_ID = p.DFCONCID
                 left join rls.ACTUNITS dfau on dfau.ACTUNITS_ID = p.DFACTID
                 left join rls.SIZEUNITS dfsu on dfsu.SIZEUNITS_ID = p.DFSIZEID
                 left join rls.v_Nomen n on n.NOMEN_ID = d.Drug_id
                 left join rls.MASSUNITS mu on mu.MASSUNITS_ID = n.PPACKMASSUNID
                 left join rls.CUBICUNITS cu on cu.CUBICUNITS_ID = n.PPACKCUBUNID
                 LEFT JOIN LATERAL
                 (
                   select coalesce(cast (cast (p.DFMASS as decimal) as varchar) || ' ' || dfmu.SHORTNAME, cast (cast (p.DFCONC as decimal) as varchar) || ' ' || dfcu.SHORTNAME, cast (p.DFACT as varchar) || ' ' || dfau.SHORTNAME, cast (p.DFSIZE as varchar) || ' ' || dfsu.SHORTNAME) as Value
                 ) dose ON true
                 LEFT JOIN LATERAL
                 (
                   select ((case
                              when d.Drug_Fas is not null then cast (d.Drug_Fas as varchar) || ' доз'
                              else ''
                            end) ||(case
                                     when d.Drug_Fas is not null and coalesce(d.Drug_Volume, d.Drug_Mass) is not null then ', '
                                     else ''
                                   end) ||(case
                                            when coalesce(d.Drug_Volume, d.Drug_Mass) is not null then coalesce(d.Drug_Volume, d.Drug_Mass)
                                            else ''
                                          end)) as Value
                 ) fas ON true
            where data.total_kolvo > 0;
		";
		
		//echo getDebugSql($q, $filter); die();
		$list = array();
		$result = $this->db->query($q, $filter);
		if (is_object($result)) {
			$list = $result->result('array');
		} else {
			return false;
		}


		//Так как у нас не берется в рассчет суммарное доступное количество Медиакмента в рамках одного ГК, нам нужна дополнительная корректировка результатов распределения. Требуется ограничить суммарное количество медикамента, количеством доступным на остатках по определенному ГК.
		$drug_arr = array();

		//собираем данные об избытке
		foreach($list as &$row) {
			$key = $row['WhsDocumentUc_pid'].'_'.$row['Drug_id'];
			if (!isset($drug_arr[$key])) {
				$drug_arr[$key] = array(
					'kolvo' => 0,
					'available' => $row['Kolvo_Available']
				);
			}
			$drug_arr[$key]['kolvo'] += $row['WhsDocumentOrderAllocationDrug_Kolvo'];
		}
		foreach($drug_arr as &$drug) {
			if ($drug['kolvo'] > $drug['available']) {
				$drug['rate'] = $drug['available']/$drug['kolvo'];
				$drug['excess'] = $drug['available']*(-1);
			}
		}

		//грубая корректировка
		foreach($list as &$row) {
			$key = $row['WhsDocumentUc_pid'].'_'.$row['Drug_id'];
			if (isset($drug_arr[$key]['rate'])) {
				$row['WhsDocumentOrderAllocationDrug_Kolvo'] = ceil($row['WhsDocumentOrderAllocationDrug_Kolvo'] * $drug_arr[$key]['rate']);
				$drug_arr[$key]['excess'] += $row['WhsDocumentOrderAllocationDrug_Kolvo'];
			}
		}

		//точная корректировка
		$iteration = 10;
		while($iteration-- > 0) {
			$row_cnt = 0;
			foreach($list as &$row) {
				$key = $row['WhsDocumentUc_pid'].'_'.$row['Drug_id'];
				if (isset($drug_arr[$key]['excess']) && $drug_arr[$key]['excess'] > 0) {
					$row_cnt++;
					if ($row['WhsDocumentOrderAllocationDrug_Kolvo'] > 1 || $iteration <= 3) {
						$row['WhsDocumentOrderAllocationDrug_Kolvo'] -= 1;
						$drug_arr[$key]['excess'] -= 1;
					}
				}
			}
			if ($row_cnt == 0) {
				break;
			}
		}

		//корректируем проценты и сумму
		foreach($list as &$row) {
			$key = $row['WhsDocumentUc_pid'].'_'.$row['Drug_id'];
			if (isset($drug_arr[$key]['excess'])) {
				$row['WhsDocumentUc_Sum'] = $row['WhsDocumentOrderAllocationDrug_Price']*$row['WhsDocumentOrderAllocationDrug_Kolvo'];
				$row['WhsDocumentOrderAllocationDrug_Percent'] = round(($row['WhsDocumentOrderAllocationDrug_Kolvo']*100)/$drug_arr[$key]['available'], 2);
			}
		}


		//Так как потребность у нас считается на медикамент и МО, а не на конкретную строку разнарядки, нам нужна дополнительная корректировка результатов распределения. Требуется ограничить суммарное количество медикамента величиной потребности.
		$drug_arr = array();

		//собираем данные об избытке
		foreach($list as &$row) {
			$key = $row['Lpu_id'].'_'.$row['Drug_id'];
			if (!isset($drug_arr[$key])) {
				$drug_arr[$key] = array(
					'kolvo' => 0,
					'need' => $row['Kolvo_Request']
				);
			}
			$drug_arr[$key]['kolvo'] += $row['WhsDocumentOrderAllocationDrug_Kolvo'];
		}
		foreach($drug_arr as &$drug) {
			if ($drug['kolvo'] > $drug['need']) {
				$drug['rate'] = $drug['need']/$drug['kolvo'];
				$drug['excess'] = $drug['need']*(-1);
			}
		}

		//грубая корректировка
		foreach($list as &$row) {
			$key = $row['Lpu_id'].'_'.$row['Drug_id'];
			if (isset($drug_arr[$key]['rate'])) {
				$row['WhsDocumentOrderAllocationDrug_Kolvo'] = ceil($row['WhsDocumentOrderAllocationDrug_Kolvo'] * $drug_arr[$key]['rate']);
				$drug_arr[$key]['excess'] += $row['WhsDocumentOrderAllocationDrug_Kolvo'];
			}
		}

		//точная корректировка
		$iteration = 10;
		while($iteration-- > 0) {
			$row_cnt = 0;
			foreach($list as &$row) {
				$key = $row['Lpu_id'].'_'.$row['Drug_id'];
				if (isset($drug_arr[$key]['excess']) && $drug_arr[$key]['excess'] > 0) {
					$row_cnt++;
					if ($row['WhsDocumentOrderAllocationDrug_Kolvo'] > 1 || $iteration <= 3) {
						$row['WhsDocumentOrderAllocationDrug_Kolvo'] -= 1;
						$drug_arr[$key]['excess'] -= 1;
					}
				}
			}
			if ($row_cnt == 0) {
				break;
			}
		}

		//корректируем проценты и сумму
		foreach($list as &$row) {
			$key = $row['Lpu_id'].'_'.$row['Drug_id'];
			if (isset($drug_arr[$key]['excess'])) {
				$row['WhsDocumentUc_Sum'] = $row['WhsDocumentOrderAllocationDrug_Price']*$row['WhsDocumentOrderAllocationDrug_Kolvo'];
				$row['WhsDocumentOrderAllocationDrug_Percent'] = round(($row['WhsDocumentOrderAllocationDrug_Kolvo']*100)/$drug_arr[$key]['need'], 2);
			}
		}

		return $list;
	}

	/**
	 *  Формирование первоначального плана поставок
	 */
	function loadRAWSupSpecList($filter) {
		$q = "
			with ostat as ( -- данные по остаткам на счету организации
                --select 216779 as Drug_id, 120 as Okei_id, 100 as price, 17 as kolvo union
                select dor.Drug_id,
                       max(dor.Okei_id) as Okei_id,
                       avg(wdss.price) as price,
                       sum(DOR.DrugOstatRegistry_Kolvo) as kolvo
                from DrugOstatRegistry dor
                     left join SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
                     left join rls.v_Drug d ON d.Drug_id = dor.Drug_id
                     left join v_DrugShipment ds on ds.DrugShipment_id = dor.DrugShipment_id
                     left join v_WhsDocumentSupply wds on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
                     LEFT JOIN LATERAL
                     (
                       select WhsDocumentSupplySpec_PriceNDS as price
                       from v_WhsDocumentSupplySpec
                       where WhsDocumentSupply_id = ds.WhsDocumentSupply_id and
                             Drug_id = dor.Drug_id
                       limit 1
                     ) wdss ON true
                where dor.Org_id =:Org_id and
                      dor.DrugOstatRegistry_Kolvo > 0 and
                      sat.SubAccountType_Code = 1 and
                      wds.DrugFinance_id =:DrugFinance_id and
                      wds.WhsDocumentCostItemType_id =:WhsDocumentCostItemType_id and
                      wds.WhsDocumentUc_pid is not null
                group by dor.Drug_id,
                         d.Drug_Name),
            total_drug as ( -- суммараная потребность в медикаментах
                select er.Drug_rlsid as Drug_id,
                       sum(er.EvnRecept_Kolvo) as kolvo
                from v_EvnRecept er
                     inner join v_OrgFarmacy ofr on ofr.OrgFarmacy_id = er.OrgFarmacy_oid
                     inner join v_Org o on o.Org_id = ofr.Org_id
                     inner join rls.v_Drug d on d.Drug_id = er.Drug_rlsid
                where er.ReceptDelayType_id =
                      (
                        select ReceptDelayType_id
                        from v_ReceptDelayType
                        where ReceptDelayType_Code = 1
                        limit 1
                      ) and
                      er.Drug_rlsid is not null and
                      er.OrgFarmacy_oid is not null
                group by Drug_rlsid)
            select o.Org_id as \"Org_id\",
                   o.Org_Name as \"Org_Name\",
                   d.Drug_id as \"Drug_id\",
                   d.Drug_Name as \"Drug_Name\",
                   ostat.Okei_id as \"Okei_id\",
                   p.EvnRecept_Kolvo as \"recept_kolvo\",
                   COALESCE(ostat.kolvo, 0) as \"ost_kolvo\",
                   total_drug.kolvo as \"total_recept_kolvo\",
                   (case
                      when ostat.kolvo < total_drug.kolvo then ceiling((ostat.kolvo / total_drug.kolvo) * p.EvnRecept_Kolvo)
                      else p.EvnRecept_Kolvo
                    end) as \"kolvo\",
                   ostat.price as \"price\",
                   'add' as \"state\"
            from (
                   select ofr.Org_id,
                          er.Drug_rlsid,
                          sum(er.EvnRecept_Kolvo) as EvnRecept_Kolvo
                   from v_EvnRecept er
                        inner join v_OrgFarmacy ofr on ofr.OrgFarmacy_id = er.OrgFarmacy_oid
                   where er.ReceptDelayType_id =
                         (
                           select ReceptDelayType_id
                           from v_ReceptDelayType
                           where ReceptDelayType_Code = 1
                           limit 1
                         ) and
                         er.Drug_rlsid is not null and
                         er.OrgFarmacy_oid is not null
                   group by ofr.Org_id,
                            er.Drug_rlsid
                 ) p
                 inner join v_Org o on o.Org_id = p.Org_id
                 inner join rls.v_Drug d on d.Drug_id = p.Drug_rlsid
                 inner join ostat on ostat.Drug_id = d.Drug_id
                 left join total_drug on total_drug.Drug_id = d.Drug_id
            order by o.Org_Name,
                     d.Drug_Name;
		";

		//echo getDebugSql($q, $filter); die();
		$result = $this->db->query($q, $filter);
		if (is_object($result)) {
			$drug_arr = $result->result('array');
			$cor_arr = array();

			//так как количество медикаментов может быть вычесленно с округлением, необходима дополнительная проверка и корректировка
			for($i = 0; $i < count($drug_arr); $i++) {
				//если на остатках медикамента меньше, чем требуется суммарно рецептам, есть вероятность ошибки
				if ($drug_arr[$i]['ost_kolvo'] < $drug_arr[$i]['total_recept_kolvo']) {
					if (!isset($cor_arr[$drug_arr[$i]['Drug_id']])) {
						$cor_arr[$drug_arr[$i]['Drug_id']] = 0;
					}
					//суммируем количество по медикаментам
					$cor_arr[$drug_arr[$i]['Drug_id']] += $drug_arr[$i]['kolvo'];
				}
			}

			//вычисляем расхождение
			$cor_sum = 0;
			foreach($cor_arr as $key=>&$value) {
				for($i = 0; $i < count($drug_arr); $i++) {
					if ($drug_arr[$i]['Drug_id'] == $key) {
						$value -= $drug_arr[$i]['ost_kolvo'];
						break;
					}
				}
				$cor_sum += $value;
			}

			//устраняем расхождение
			$iter = 30; //количество оставшихся итераций корректировки
			while($cor_sum > 0 && $iter-- > 0) {
				for($i = 0; $i < count($drug_arr); $i++) {
					if (isset($cor_arr[$drug_arr[$i]['Drug_id']]) && $cor_arr[$drug_arr[$i]['Drug_id']] > 0 && ($drug_arr[$i]['kolvo'] > 1 || ($drug_arr[$i]['kolvo'] > 0 && $iter < 10))) {
						$cor_sum--;
						$drug_arr[$i]['kolvo']--;
						$cor_arr[$drug_arr[$i]['Drug_id']]--;
					}
				}
			}

			/*for($i = 0; $i < count($drug_arr); $i++) {
				//устанавливаем начальное значение количества
				$drug_arr[$i]['kolvo'] = $drug_arr[$i]['raw_kolvo'];
			}*/

			return $drug_arr;
		} else {
			return false;
		}
	}

	/**
	 *  Формирование первоначальной спецификации распределения ЛС по аптекам
	 */
	function loadRAWFarmDrugSpecList($filter) {
		$q = "
			select ofi.Org_id as \"Org_id\",
                   COALESCE(ofi.OrgFarmacy_Name, '') as \"Org_Name\",
                   wdord.Drug_id as \"Drug_id\",
                   wdord.Okei_id as \"Okei_id\",
                   d.Drug_Name as \"Drug_Name\",
                   ltrim(CAST(sum(ceiling(wdord.WhsDocumentOrderAllocationDrug_Kolvo / farmacy_count.cnt)) as varchar)) as \"WhsDocumentOrderAllocationDrug_Kolvo\",
                   sum(wdord.WhsDocumentOrderAllocationDrug_Kolvo) as \"Total_Kolvo\",
                   ltrim(CAST(CAST(sum(wdord.WhsDocumentOrderAllocationDrug_Price * wdord.WhsDocumentOrderAllocationDrug_Kolvo) / sum(wdord.WhsDocumentOrderAllocationDrug_Kolvo) as numeric(10, 2)) as varchar)) as \"WhsDocumentOrderAllocationDrug_Price\",
                   ltrim(CAST(CAST(sum(wdord.WhsDocumentOrderAllocationDrug_Price * wdord.WhsDocumentOrderAllocationDrug_Kolvo)as numeric(10, 2)) as varchar)) as \"WhsDocumentUc_Sum\",
                   'add' as \"state\"
            from v_WhsDocumentOrderAllocationDrug wdord
                 left join v_WhsDocumentOrderAllocation wdoa on wdoa.WhsDocumentOrderAllocation_id = wdord.WhsDocumentOrderAllocation_id
                 left join rls.v_Drug d on d.Drug_id = wdord.Drug_id
                 left join v_WhsDocumentSupply wds on wds.WhsDocumentUc_id = wdord.WhsDocumentUc_pid
                 inner join v_Lpu l on l.Org_id = wdoa.Org_id
                 inner join v_OrgFarmacyIndex ofi on ofi.Lpu_id = l.Lpu_id
                 LEFT JOIN LATERAL
                 (
                   select count(OrgFarmacy_id) as cnt
                   from OrgFarmacyIndex
                   where Lpu_id = l.Lpu_id
                 ) farmacy_count ON true
            where wdord.WhsDocumentOrderAllocation_id in (
                                                           select WhsDocumentOrderAllocation_id
                                                           from v_WhsDocumentOrderAllocation
                                                           where WhsDocumentUc_pid =:WhsDocumentUc_id
                  ) and
                  wds.Org_sid =:Org_id
            group by ofi.Org_id,
                     ofi.OrgFarmacy_Name,
                     wdord.Drug_id,
                     wdord.Okei_id,
                     d.Drug_Name
            order by max(ofi.OrgFarmacyIndex_Index) desc;
		";

		//echo getDebugSql($q, $filter); die();
		$result = $this->db->query($q, $filter);
		if (is_object($result)) {
			//так как при распределении медикаментов используется округление, требуется дополнительная корректировка количества
			$list = $result->result('array');
			$drug_arr = array();

			//собираем данные об избытке
			foreach($list as $row) {
				$key = $row['Drug_id'];
				if (!isset($drug_arr[$key])) {
					$drug_arr[$key] = array(
						'kolvo' => 0,
						'need' => $row['Total_Kolvo']
					);
				}
				$drug_arr[$key]['kolvo'] += $row['WhsDocumentOrderAllocationDrug_Kolvo'];
			}
			foreach($drug_arr as &$drug) {
				if ($drug['kolvo'] > $drug['need']) {
					$drug['excess'] = $drug['need']*(-1);
				}
			}

			//рассчет избытка
			foreach($list as &$row) {
				$key = $row['Drug_id'];
				if (isset($drug_arr[$key]['excess'])) {
					$drug_arr[$key]['excess'] += $row['WhsDocumentOrderAllocationDrug_Kolvo'];
				}
			}

			//точная корректировка
			$iteration = 10;
			while($iteration-- > 0) {
				$row_cnt = 0;
				foreach($list as &$row) {
					$key = $row['Drug_id'];
					if (isset($drug_arr[$key]['excess']) && $drug_arr[$key]['excess'] > 0) {
						$row_cnt++;
						if ($row['WhsDocumentOrderAllocationDrug_Kolvo'] > 1 || $iteration <= 3) {
							$row['WhsDocumentOrderAllocationDrug_Kolvo'] -= 1;
							$drug_arr[$key]['excess'] -= 1;
						}
					}
				}
				if ($row_cnt == 0) {
					break;
				}
			}

			return array_reverse($list);
		} else {
			return false;
		}
	}

	/**
	 *  Загрузка сводной разнарядки
	 */
	function loadList($filter) {
		$where = array();
		$q = "
            select wdord.WhsDocumentOrderAllocationDrug_id as \"WhsDocumentOrderAllocationDrug_id\",
                   wdord.WhsDocumentUc_pid as \"WhsDocumentUc_pid\",
                   wds.WhsDocumentUc_Num as \"WhsDocumentUc_Name\",
                   COALESCE(Supplier.Org_Name, '') as \"Supplier_Name\",
                   wdord.Drug_id as \"Drug_id\",
                   wdord.Okei_id as \"Okei_id\",
                   am.RUSNAME as \"Actmatters_RusName\",
                   tn.NAME as \"Tradenames_Name\",
                   dform.NAME as \"DrugForm_Name\",
                   dose.Value as \"Drug_Dose\",
                   fas.Value as \"Drug_Fas\",
                   --FLOOR(STR(wdord.WhsDocumentOrderAllocationDrug_Kolvo,10,0)*100/wdoa.WhsDocumentOrderAllocation_Percent) as Available_Kolvo,
                   LTRIM(CAST(COALESCE(Available_Drug.Kolvo,0) as varchar)) as \"Available_Kolvo\",
                   LTRIM(CAST(CAST(wdord.WhsDocumentOrderAllocationDrug_Kolvo as numeric(10, 0)) as varchar)) as \"WhsDocumentOrderAllocationDrug_Kolvo\",
                   --wdoa.WhsDocumentOrderAllocation_Percent,
                   (case
                      when Available_Drug.Kolvo > 0 then LTRIM(CAST(CAST((WhsDocumentOrderAllocationDrug_Kolvo /(Available_Drug.Kolvo)) * 100 as numeric(10, 2)) as varchar))
                      else null
                    end) as \"WhsDocumentOrderAllocation_Percent\",
                   LTRIM(CAST(CAST(wdord.WhsDocumentOrderAllocationDrug_Price as numeric(10, 2)) as varchar)) as \"WhsDocumentOrderAllocationDrug_Price\",
                   LTRIM(CAST(CAST(wdord.WhsDocumentOrderAllocationDrug_Price * wdord.WhsDocumentOrderAllocationDrug_Kolvo as numeric(10, 2)) as varchar)) as \"WhsDocumentUc_Sum\",
                   rc.REGNUM as \"Reg_Num\",
                   rceff.FULLNAME as \"Reg_Firm\",
                   rceffc.NAME as \"Reg_Country\",
                   (to_char(rc.REGDATE, 'DD.MM.YYYY') || COALESCE(' - ' || to_char(rc.ENDDATE, 'DD.MM.YYYY'), '')) as \"Reg_Period\",
                   to_char(rc.Reregdate, 'DD.MM.YYYY') as \"Reg_ReRegDate\"
            from v_WhsDocumentOrderAllocationDrug wdord
                 left join v_WhsDocumentOrderAllocation wdoa on wdoa.WhsDocumentOrderAllocation_id = wdord.WhsDocumentOrderAllocation_id
                 left join rls.v_Drug d on d.Drug_id = wdord.Drug_id
                 left join v_WhsDocumentSupply wds on wds.WhsDocumentUc_id = wdord.WhsDocumentUc_pid
                 left join v_Org Supplier on Supplier.Org_id = wds.Org_sid
                 left join rls.v_prep p on p.Prep_id = d.DrugPrep_id
                 left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                 left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                 left join rls.v_ACTMATTERS am on am.ACTMATTERS_ID = dcmn.Actmatters_id
                 left join rls.v_TRADENAMES tn on tn.TRADENAMES_ID = p.TRADENAMEID
                 left join rls.v_CLSDRUGFORMS dform on dform.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
                 left join rls.REGCERT rc on rc.REGCERT_ID = p.REGCERTID
                 left join rls.REGCERT_EXTRAFIRMS rcef on rcef.CERTID = rc.REGCERT_ID
                 left join rls.v_FIRMS rceff on rceff.FIRMS_ID = rcef.FIRMID
                 left join rls.v_COUNTRIES rceffc on rceffc.COUNTRIES_ID = rceff.COUNTID
                 left join rls.MASSUNITS dfmu on dfmu.MASSUNITS_ID = p.DFMASSID
                 left join rls.CONCENUNITS dfcu on dfcu.CONCENUNITS_ID = p.DFCONCID
                 left join rls.ACTUNITS dfau on dfau.ACTUNITS_ID = p.DFACTID
                 left join rls.SIZEUNITS dfsu on dfsu.SIZEUNITS_ID = p.DFSIZEID
                 left join rls.v_Nomen n on n.NOMEN_ID = wdord.Drug_id
                 left join rls.MASSUNITS mu on mu.MASSUNITS_ID = n.PPACKMASSUNID
                 left join rls.CUBICUNITS cu on cu.CUBICUNITS_ID = n.PPACKCUBUNID
                 LEFT JOIN LATERAL
                 (
                   select coalesce(cast (cast (p.DFMASS as decimal) as varchar) || ' ' || dfmu.SHORTNAME, cast (cast (p.DFCONC as decimal) as varchar) || ' ' || dfcu.SHORTNAME, cast (p.DFACT as varchar) || ' ' || dfau.SHORTNAME, cast (p.DFSIZE as varchar) || ' ' || dfsu.SHORTNAME) as Value
                 ) dose ON true
                 LEFT JOIN LATERAL
                 (
                   select ((case
                              when d.Drug_Fas is not null then cast (d.Drug_Fas as varchar) || ' доз'
                              else ''
                            end) ||(case
                                     when d.Drug_Fas is not null and coalesce(d.Drug_Volume, d.Drug_Mass) is not null then ', '
                                     else ''
                                   end) ||(case
                                            when coalesce(d.Drug_Volume, d.Drug_Mass) is not null then coalesce(d.Drug_Volume, d.Drug_Mass)
                                            else ''
                                          end)) as Value
                 ) fas ON true
                 LEFT JOIN LATERAL
                 (
                   select COALESCE(a_dor.DrugOstatRegistry_Kolvo, 0) as Kolvo
                   from DrugOstatRegistry a_dor
                        left join SubAccountType a_sat on a_sat.SubAccountType_id = a_dor.SubAccountType_id
                        left join v_DrugShipment a_ds on a_ds.DrugShipment_id = a_dor.DrugShipment_id
                        left join v_WhsDocumentSupply a_wds on a_wds.WhsDocumentSupply_id = a_ds.WhsDocumentSupply_id
                   where a_dor.Org_id = dbo.GetMinzdravDloOrgId() and
                         a_dor.DrugOstatRegistry_Kolvo > 0 and
                         a_dor.Drug_id = wdord.Drug_id and
                         a_sat.SubAccountType_Code = 1 and
                         a_wds.WhsDocumentUc_id = wdord.WhsDocumentUc_pid
                 ) Available_Drug ON true
            where wdord.WhsDocumentOrderAllocation_id =:WhsDocumentOrderAllocation_id
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Загрузка списка медикаментов, подлежащих распределению (план поставок)
	 */
	function loadSupList($filter) {
		$where = array();
		$q = "
			with pln as (
                select Drug_id,
                       sum(WhsDocumentOrderAllocationDrug_Kolvo) as kolvo
                from v_WhsDocumentOrderAllocationDrug
                where WhsDocumentOrderAllocation_id in (
                                                         select WhsDocumentOrderAllocation_id
                                                         from v_WhsDocumentOrderAllocation
                                                         where WhsDocumentUc_pid =:WhsDocumentOrderAllocation_id
                      )
                group by Drug_id)
            select wdord.WhsDocumentOrderAllocationDrug_id as \"WhsDocumentOrderAllocationDrug_id\",
                   wdord.WhsDocumentUc_pid as \"WhsDocumentUc_pid\",
                   wds.WhsDocumentUc_Num as \"WhsDocumentUc_Name\",
                   COALESCE(Supplier.Org_Name, '') as \"Supplier_Name\",
                   wdord.Drug_id as \"Drug_id\",
                   wdord.Okei_id as \"Okei_id\",
                   d.Drug_Name as \"Drug_Name\",
                   ltrim(CAST(CAST(wdord.WhsDocumentOrderAllocationDrug_Kolvo as numeric(10, 0)) as varchar)) as \"WhsDocumentOrderAllocationDrug_Allocation\",
                   ltrim(CAST(CAST(wdord.WhsDocumentOrderAllocationDrug_Price as numeric(10, 2)) as varchar)) as \"WhsDocumentOrderAllocationDrug_PriceNDS\",
                   CAST(CAST(pln.kolvo as numeric(10, 0)) as varchar) as \"Plan_Kolvo\"
            from v_WhsDocumentOrderAllocationDrug wdord
                 left join v_WhsDocumentOrderAllocation wdoa on wdoa.WhsDocumentOrderAllocation_id = wdord.WhsDocumentOrderAllocation_id
                 left join rls.v_Drug d on d.Drug_id = wdord.Drug_id
                 left join v_WhsDocumentSupply wds on wds.WhsDocumentUc_id = wdord.WhsDocumentUc_pid
                 left join v_Org Supplier on Supplier.Org_id = wds.Org_sid
                 left join pln on pln.Drug_id = wdord.Drug_id
            where wdord.WhsDocumentOrderAllocation_id =:WhsDocumentOrderAllocation_id
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Загрузка списка медикаментов, подлежащих распределению (распределение ЛС по аптекам)
	 */
	function loadFarmDrugList($filter) {
		$where = array();
		$q = "
			with spec_list as (
                select Drug_id,
                       sum(WhsDocumentOrderAllocationDrug_Kolvo) as kolvo
                from v_WhsDocumentOrderAllocationDrug
                where WhsDocumentOrderAllocation_id in (
                                                         select WhsDocumentOrderAllocation_id
                                                         from v_WhsDocumentOrderAllocation
                                                         where WhsDocumentUc_pid =:WhsDocumentOrderAllocation_id
                      )
                group by Drug_id)
            select wdord.WhsDocumentOrderAllocationDrug_id as \"WhsDocumentOrderAllocationDrug_id\",
                   wdord.WhsDocumentUc_pid as \"WhsDocumentUc_pid\",
                   wdord.Drug_id as \"Drug_id\",
                   wdord.Okei_id as \"Okei_id\",
                   d.Drug_Name as \"Drug_Name\",
                   ltrim(CAST(CAST(wdord.WhsDocumentOrderAllocationDrug_Kolvo as numeric(10, 0)) as varchar)) as \"WhsDocumentOrderAllocationDrug_Kolvo\",
                   ltrim(CAST(CAST(wdord.WhsDocumentOrderAllocationDrug_Price as numeric(10, 2)) as varchar)) as \"WhsDocumentOrderAllocationDrug_Price\",
                   CAST(CAST(spec_list.kolvo as numeric(10, 0)) as varchar)  as \"Plan_Kolvo\"
            from v_WhsDocumentOrderAllocationDrug wdord
                 left join rls.v_Drug d on d.Drug_id = wdord.Drug_id
                 left join spec_list on spec_list.Drug_id = wdord.Drug_id
            where wdord.WhsDocumentOrderAllocation_id =:WhsDocumentOrderAllocation_id
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Загрузка разнарядки МО
	 */
	function loadListMo($filter) {
		$where = array();
		$q = "
			with cte AS (
                    select DrugFinance_id as DrugFinance_id,
                           WhsDocumentOrderAllocation_BegDate as begDate,
                           WhsDocumentOrderAllocation_EndDate as endDate
                    from v_WhsDocumentOrderAllocation
                    where WhsDocumentOrderAllocation_id = :WhsDocumentOrderAllocation_id 
            ),
            req_data as ( -- данные из заявок врачей
                select l.Org_id,
                       drr.DrugComplexMnn_id,
                       sum(drr.DrugRequestRow_Kolvo) as kolvo
                from v_DrugRequestPurchase drp
                     left join v_DrugRequest regdr on regdr.DrugRequest_id = drp.DrugRequest_lid
                     left join v_DrugRequest dr on dr.DrugRequest_Version is null and dr.DrugRequestPeriod_id = regdr.DrugRequestPeriod_id and COALESCE(dr.PersonRegisterType_id, 0) = COALESCE(regdr.PersonRegisterType_id, 0) and COALESCE(dr.DrugRequestKind_id, 0) = COALESCE(regdr.DrugRequestKind_id, 0) and COALESCE(dr.DrugGroup_id, 0) = COALESCE(regdr.DrugGroup_id, 0) and dr.Lpu_id is not null and dr.MedPersonal_id is not null
                     left join v_DrugRequestRow drr on drr.DrugRequest_id = dr.DrugRequest_id
                     inner join v_Lpu l on l.Lpu_id = dr.Lpu_id
                where drp.DrugRequest_id =
                      (
                        select DrugRequest_id
                        from v_WhsDocumentOrderAllocation
                        where WhsDocumentOrderAllocation_id =:WhsDocumentOrderAllocation_id
                        limit 1
                      ) and
                      drr.DrugFinance_id = (SELECT DrugFinance_id FROM cte) and
                      drr.DrugComplexMnn_id is not null
                group by l.Org_id,
                         drr.DrugComplexMnn_id)
            SELECT WDORD.WhsDocumentOrderAllocationDrug_id as \"WhsDocumentOrderAllocationDrug_id\",
                   WDORD.WhsDocumentOrderAllocation_id as \"WhsDocumentOrderAllocation_id\",
                   WDORD.WhsDocumentUc_pid as \"WhsDocumentUc_pid\",
                   WDS.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
                   WDS.WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
                   WDORD.Drug_id as \"Drug_id\",
                   WDORD.Okei_id as \"Okei_id\",
                   tn.NAME as \"Tradenames_Name\",
                   dform.NAME as \"DrugForm_Name\",
                   dose.Value as \"Drug_Dose\",
                   fas.Value as \"Drug_Fas\",
                   O.Org_Name as \"Lpu_Name\",
                   O.Org_id as \"Org_id\",
                   COALESCE(Supplier.Org_Name, '') as \"Supplier_Name\",
                   req_data.kolvo as \"Kolvo_Request\",
                   LTRIM(CAST(CAST(WDORD.WhsDocumentOrderAllocationDrug_Kolvo as numeric(10, 0)) as varchar)) as \"WhsDocumentOrderAllocationDrug_Kolvo\",
                   LTRIM(CAST(CAST(round(CAST((WDORD.WhsDocumentOrderAllocationDrug_Kolvo * 100) / req_data.kolvo as numeric), 2) as numeric(10, 2)) as varchar)) as \"WhsDocumentOrderAllocationDrug_Percent\",
                   LTRIM(CAST(CAST(WDORD.WhsDocumentOrderAllocationDrug_Price as numeric(10, 2)) as varchar)) as \"WhsDocumentOrderAllocationDrug_Price\",
                   LTRIM(CAST(CAST(WDORD.WhsDocumentOrderAllocationDrug_Price * WDORD.WhsDocumentOrderAllocationDrug_Kolvo as numeric(10, 2)) as varchar)) as \"WhsDocumentUc_Sum\",
                   rc.REGNUM as \"Reg_Num\",
                   rceff.FULLNAME as \"Reg_Firm\",
                   rceffc.NAME as \"Reg_Country\",
                   (to_char(rc.REGDATE, 'DD.MM.YYYY') || COALESCE(' - ' || to_char(rc.ENDDATE, 'DD.MM.YYYY'), '')) as \"Reg_Period\",
                   to_char(rc.Reregdate, 'DD.MM.YYYY') as \"Reg_ReRegDate\",
                   null as \"state\"
            FROM v_WhsDocumentOrderAllocationDrug wdord
                 left join v_WhsDocumentOrderAllocation wdoa on wdoa.WhsDocumentOrderAllocation_id = wdord.WhsDocumentOrderAllocation_id
                 left join rls.v_Drug d on d.Drug_id = wdord.Drug_id
                 left join v_WhsDocumentSupply wds on wds.WhsDocumentUc_id = wdord.WhsDocumentUc_pid
                 left join v_Org Supplier on Supplier.Org_id = wds.Org_sid
                 inner join v_Org O on O.Org_id = wdoa.Org_id
                 left join req_data on req_data.Org_id = wdoa.Org_id and req_data.DrugComplexMnn_id = D.DrugComplexMnn_id
                 left join rls.v_prep p on p.Prep_id = d.DrugPrep_id
                 left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                 left join rls.v_TRADENAMES tn on tn.TRADENAMES_ID = p.TRADENAMEID
                 left join rls.v_CLSDRUGFORMS dform on dform.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
                 left join rls.REGCERT rc on rc.REGCERT_ID = p.REGCERTID
                 left join rls.REGCERT_EXTRAFIRMS rcef on rcef.CERTID = rc.REGCERT_ID
                 left join rls.v_FIRMS rceff on rceff.FIRMS_ID = rcef.FIRMID
                 left join rls.v_COUNTRIES rceffc on rceffc.COUNTRIES_ID = rceff.COUNTID
                 left join rls.MASSUNITS dfmu on dfmu.MASSUNITS_ID = p.DFMASSID
                 left join rls.CONCENUNITS dfcu on dfcu.CONCENUNITS_ID = p.DFCONCID
                 left join rls.ACTUNITS dfau on dfau.ACTUNITS_ID = p.DFACTID
                 left join rls.SIZEUNITS dfsu on dfsu.SIZEUNITS_ID = p.DFSIZEID
                 left join rls.v_Nomen n on n.NOMEN_ID = d.Drug_id
                 left join rls.MASSUNITS mu on mu.MASSUNITS_ID = n.PPACKMASSUNID
                 left join rls.CUBICUNITS cu on cu.CUBICUNITS_ID = n.PPACKCUBUNID
                 LEFT JOIN LATERAL
                 (
                   select coalesce(cast (cast (p.DFMASS as decimal) as varchar) || ' ' || dfmu.SHORTNAME, cast (cast (p.DFCONC as decimal) as varchar) || ' ' || dfcu.SHORTNAME, cast (p.DFACT as varchar) || ' ' || dfau.SHORTNAME, cast (p.DFSIZE as varchar) || ' ' || dfsu.SHORTNAME) as Value
                 ) dose ON true
                 LEFT JOIN LATERAL
                 (
                   select ((case
                              when d.Drug_Fas is not null then cast (d.Drug_Fas as varchar) || ' доз'
                              else ''
                            end) ||(case
                                     when d.Drug_Fas is not null and coalesce(d.Drug_Volume, d.Drug_Mass) is not null then ', '
                                     else ''
                                   end) ||(case
                                            when coalesce(d.Drug_Volume, d.Drug_Mass) is not null then coalesce(d.Drug_Volume, d.Drug_Mass)
                                            else ''
                                          end)) as Value
                 ) fas ON true
            WHERE WDORD.WhsDocumentOrderAllocation_id IN (
                                                           select WhsDocumentOrderAllocation_id
                                                           from v_WhsDocumentOrderAllocation
                                                           where WhsDocumentUc_pid =:WhsDocumentOrderAllocation_id and
                                                                 WhsDocumentType_id = 9
                  )

		";
		//die(getDebugSQL($q, $filter));
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Загрузка плана поставок
	 */
	function loadSupSpecList($filter) {
		$where = array();
		$q = "
			select WDORD.WhsDocumentOrderAllocationDrug_id as \"WhsDocumentOrderAllocationDrug_id\",
                   WDORD.WhsDocumentOrderAllocation_id as \"WhsDocumentOrderAllocation_id\",
                   WDORD.WhsDocumentUc_pid as \"WhsDocumentUc_pid\",
                   WDORD.Drug_id as \"Drug_id\",
                   WDORD.Okei_id as \"Okei_id\",
                   D.Drug_Name as \"Drug_Name\",
                   O.Org_Name as \"Org_Name\",
                   O.Org_id as \"Org_id\",
                   CAST(CAST(WDORD.WhsDocumentOrderAllocationDrug_Kolvo as numeric(10, 0)) as varchar) as \"kolvo\",
                   WDORD.WhsDocumentOrderAllocationDrug_Price as \"price\",
                   'edit' as \"state\"
            from v_WhsDocumentOrderAllocationDrug WDORD
                 left join v_WhsDocumentOrderAllocation WDOA on WDOA.WhsDocumentOrderAllocation_id = WDORD.WhsDocumentOrderAllocation_id
                 LEFT JOIN rls.v_Drug D ON D.Drug_id = WDORD.Drug_id
                 LEFT JOIN v_WhsDocumentSupply WDS ON WDS.WhsDocumentUc_id = WDORD.WhsDocumentUc_pid
                 inner join v_Org O on O.Org_id = WDOA.Org_id
            where WDORD.WhsDocumentOrderAllocation_id IN (
                                                           select WhsDocumentOrderAllocation_id
                                                           from v_WhsDocumentOrderAllocation
                                                           where WhsDocumentUc_pid =:WhsDocumentOrderAllocation_id
                  );
		";
		//die(getDebugSQL($q, $filter));
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Загрузка спецификации распределения ЛС по аптекам
	 */
	function loadFarmDrugSpecList($filter) {
		$where = array();
		$q = "
			select WDORD.WhsDocumentOrderAllocationDrug_id as \"WhsDocumentOrderAllocationDrug_id\",
                   WDORD.WhsDocumentOrderAllocation_id as \"WhsDocumentOrderAllocation_id\",
                   WDORD.WhsDocumentUc_pid as \"WhsDocumentUc_pid\",
                   WDORD.Drug_id as \"Drug_id\",
                   WDORD.Okei_id as \"Okei_id\",
                   D.Drug_Name as \"Drug_Name\",
                   O.Org_Name as \"Org_Name\",
                   O.Org_id as \"Org_id\",
                   CAST(CAST(WDORD.WhsDocumentOrderAllocationDrug_Kolvo as numeric(10, 0)) as varchar) as \"WhsDocumentOrderAllocationDrug_Kolvo\",
                   WDORD.WhsDocumentOrderAllocationDrug_Price as \"WhsDocumentOrderAllocationDrug_Price\",
                   '' as \"state\"
            from v_WhsDocumentOrderAllocationDrug WDORD
                 left join v_WhsDocumentOrderAllocation WDOA on WDOA.WhsDocumentOrderAllocation_id = WDORD.WhsDocumentOrderAllocation_id
                 LEFT JOIN rls.v_Drug D ON D.Drug_id = WDORD.Drug_id
                 LEFT JOIN v_WhsDocumentSupply WDS ON WDS.WhsDocumentUc_id = WDORD.WhsDocumentUc_pid
                 inner join v_Org O on O.Org_id = WDOA.Org_id
            where WDORD.WhsDocumentOrderAllocation_id IN (
                                                           select WhsDocumentOrderAllocation_id
                                                           from v_WhsDocumentOrderAllocation
                                                           where WhsDocumentUc_pid =:WhsDocumentOrderAllocation_id
                  );
		";
		//die(getDebugSQL($q, $filter));
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Загрузка списка поставщиков
	 */
	function loadListSupplier($filter) {
		$where = array();
		$q = "
			SELECT WDORD.WhsDocumentOrderAllocationDrug_id as \"WhsDocumentOrderAllocationDrug_id\",
                   WDORD.WhsDocumentOrderAllocation_id as \"WhsDocumentOrderAllocation_id\",
                   WDORD.WhsDocumentUc_pid as \"WhsDocumentUc_pid\",
                   WDS.WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
                   WDORD.Drug_id as \"Drug_id\",
                   WDORD.Okei_id as \"Okei_id\",
                   D.Drug_Name as \"Drug_Name\",
                   O.Org_Name as \"Lpu_Name\",
                   LTRIM(CAST(CAST(WDORD.WhsDocumentOrderAllocationDrug_Kolvo as numeric(10, 0)) as varchar)) as \"WhsDocumentOrderAllocationDrug_Allocation\",
                   LTRIM(CAST(CAST(WDORD.WhsDocumentOrderAllocationDrug_Price as numeric(10, 2)) as varchar)) as \"WhsDocumentOrderAllocationDrug_PriceNDS\",
                   LTRIM(CAST(CAST(WDORD.WhsDocumentOrderAllocationDrug_Price * WDORD.WhsDocumentOrderAllocationDrug_Kolvo as numeric(10, 2)) as varchar)) as \"WhsDocumentUc_Sum\"
            FROM v_WhsDocumentOrderAllocationDrug WDORD
                 left join v_WhsDocumentOrderAllocation WDOA on WDOA.WhsDocumentOrderAllocation_id = WDORD.WhsDocumentOrderAllocation_id
                 LEFT JOIN rls.v_Drug D ON D.Drug_id = WDORD.Drug_id
                 LEFT JOIN v_WhsDocumentSupply WDS ON WDS.WhsDocumentUc_id = WDORD.WhsDocumentUc_pid
                 left join v_Org O on O.Org_id = WDOA.Org_id
            WHERE WDORD.WhsDocumentOrderAllocation_id IN (
                                                           select WhsDocumentOrderAllocation_id
                                                           from v_WhsDocumentOrderAllocation
                                                           where WhsDocumentUc_pid =:WhsDocumentOrderAllocation_id
                  )
		";
		// die(getDebugSQL($q, $filter));
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Сохранение данных из массива
	 */
	function saveFromArray($data) {
		if (!empty($data['array']) && $data['WhsDocumentOrderAllocation_id'] > 0) {
			foreach($data['array'] as $record) {
				$record['WhsDocumentOrderAllocation_id'] = $data['WhsDocumentOrderAllocation_id'];
				$record['pmUser_id'] = $data['pmUser_id'];
				switch($record['state']) {
					case 'add':
						$record['WhsDocumentOrderAllocationDrug_id'] = null;
					case 'edit':				
						$this->save($record);
					break;
					case 'delete':
						$this->delete($record);
					break;						
				}
			}
		}
	}
	
	/**
	 *  Сохранение
	 */
	function save($data) {
		$procedure = 'p_WhsDocumentOrderAllocationDrug_ins';
		if ( $data['state'] != 'add' ) {
			$procedure = 'p_WhsDocumentOrderAllocationDrug_upd';
		}

		//костыль, нужно дать однозначные названия полям в разнарядках МО и в планах поставок
		$kolvo = 0;
		$price = 0;
		if (!empty($data['WhsDocumentOrderAllocationDrug_Allocation'])) {
			$kolvo = $data['WhsDocumentOrderAllocationDrug_Allocation'];
		} else if (!empty($data['WhsDocumentOrderAllocationDrug_Kolvo'])) {
			$kolvo = $data['WhsDocumentOrderAllocationDrug_Kolvo'];
		}
		if (!empty($data['WhsDocumentOrderAllocationDrug_PriceNDS'])) {
			$price = $data['WhsDocumentOrderAllocationDrug_PriceNDS'];
		} else if (!empty($data['WhsDocumentOrderAllocationDrug_Price'])) {
			$price = $data['WhsDocumentOrderAllocationDrug_Price'];
		}

		$q = "
			select WhsDocumentOrderAllocationDrug_id as \"WhsDocumentOrderAllocationDrug_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"            
			from dbo." . $procedure . "(
				WhsDocumentOrderAllocationDrug_id := :WhsDocumentOrderAllocationDrug_id,
				WhsDocumentOrderAllocation_id := :WhsDocumentOrderAllocation_id,
				WhsDocumentUc_pid := :WhsDocumentUc_pid,
				Drug_id := :Drug_id,
				Okei_id := :Okei_id,
				WhsDocumentOrderAllocationDrug_Kolvo := :WhsDocumentOrderAllocationDrug_Kolvo,
				WhsDocumentOrderAllocationDrug_Price := :WhsDocumentOrderAllocationDrug_Price,
				pmUser_id := :pmUser_id);
		";
		$p = array(
			'WhsDocumentOrderAllocationDrug_id' => $data['WhsDocumentOrderAllocationDrug_id'],
			'WhsDocumentOrderAllocation_id' => $data['WhsDocumentOrderAllocation_id'],
			'WhsDocumentUc_pid' => empty($data['WhsDocumentUc_pid'])?null:$data['WhsDocumentUc_pid'],
			'Drug_id' => $data['Drug_id'],
			'Okei_id' => $data['Okei_id'],
			'WhsDocumentOrderAllocationDrug_Kolvo' => $kolvo,
			'WhsDocumentOrderAllocationDrug_Price' => $price,
			'pmUser_id' => $data['pmUser_id']
		);
		// echo getDebugSQL($q, $p);
		// die(getDebugSQL($q, $p));
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
	 *  Удаление
	 */
	function delete($data) {
		$q = "
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                
			from dbo.p_WhsDocumentOrderAllocationDrug_del(
				WhsDocumentOrderAllocationDrug_id := :WhsDocumentOrderAllocationDrug_id);
		";
		$r = $this->db->query($q, array(
			'WhsDocumentOrderAllocationDrug_id' => $data['WhsDocumentOrderAllocationDrug_id']
		));
		if ( is_object($r) ) {
			return $r->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Загрузка списка позиций разнарядки
	 */
	function loadWhsDocumentOrderAllocationDrugList($filter) {
		if ( $filter['WhsDocumentType_id'] == 12 ) { // Документ на включение
			$q = "
				SELECT D.Drug_id as \"Drug_id\",
                       D.Drug_Code as \"Drug_Code\",
                       D.Drug_Name as \"Drug_Name\",
                       DOR.Okei_id as \"Okei_id\",
                       DOR.DrugOstatRegistry_Kolvo as \"DrugOstatRegistry_Kolvo\",
                       cast (round(COALESCE(WDSS.WhsDocumentSupplySpec_Price, 0), 2) as numeric (10, 2)) as \"WhsDocumentSupplySpec_Price\",
                       cast (round(COALESCE(WDSS.WhsDocumentSupplySpec_PriceNDS, 0), 2) as numeric (10, 2)) as \"WhsDocumentSupplySpec_PriceNDS\"
                FROM v_DrugShipment
                     inner join v_DrugOstatRegistry DOR ON DOR.DrugShipment_id = v_DrugShipment.DrugShipment_id and DOR.SubAccountType_id = 1
                     inner join rls.v_Drug D ON D.Drug_id = DOR.Drug_id
                     inner join v_WhsDocumentSupplySpec WDSS ON /*WDSS.Drug_id = DOR.Drug_id and */
                     WDSS.WhsDocumentSupply_id = v_DrugShipment.WhsDocumentSupply_id
                WHERE v_DrugShipment.WhsDocumentSupply_id =:WhsDocumentSupply_id
			";

		} elseif ( $filter['WhsDocumentType_id'] == 13 ) { // Документ на исключение
			$q = "
				SELECT D.Drug_id as \"Drug_id\",
                       D.Drug_Code as \"Drug_Code\",
                       D.Drug_Name as \"Drug_Name\",
                       DOR.Okei_id as \"Okei_id\",
                       DOR.DrugOstatRegistry_Kolvo as \"DrugOstatRegistry_Kolvo\",
                       cast (round(COALESCE(WDSS.WhsDocumentSupplySpec_Price, 0), 2) as numeric (10, 2)) as \"WhsDocumentSupplySpec_Price\",
                       cast (round(COALESCE(WDSS.WhsDocumentSupplySpec_PriceNDS, 0), 2) as numeric (10, 2)) as \"WhsDocumentSupplySpec_PriceNDS\"
                FROM v_DrugShipment
                     inner join v_DrugOstatRegistry DOR ON DOR.DrugShipment_id = v_DrugShipment.DrugShipment_id and DOR.SubAccountType_id = 2
                     inner join rls.v_Drug D ON D.Drug_id = DOR.Drug_id
                     inner join v_WhsDocumentSupplySpec WDSS ON /*WDSS.Drug_id = DOR.Drug_id and */
                     WDSS.WhsDocumentSupply_id = v_DrugShipment.WhsDocumentSupply_id
                WHERE v_DrugShipment.WhsDocumentSupply_id =:WhsDocumentSupply_id
			";
		}
	
		$result = $this->db->query($q, $filter);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка лпу для комбобокса (редактирование разнарядки МО)
	 */
	function loadOrgLpuCombo($filter) {
		$where = '';

		if (!empty($filter['query'])) {
			$where .= " and l.Lpu_Name iLIKE '%'||:Lpu_Name||'%'";

			$filter['Lpu_Name'] = $filter['query'];
		}

		$query = "
			select l.Org_id as \"Org_id\",
                   l.Lpu_Name as \"Lpu_Name\"
            from v_Lpu l
                 inner join v_Org o on o.Org_id = l.Org_id
            where (1 = 1) 
            $where
            order by l.Lpu_Name desc
		";
		$result = $this->db->query($query, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 *  Загрузка списка остатков для формы добавления записи в сводную разнарядку
	 */
	function loadDrugOstatRegistryList($filter) {
		$where = '';

		if (!empty($filter['DrugFinance_id']) && $filter['DrugFinance_id'] > 0) {
			$where .= " and wds.DrugFinance_id = :DrugFinance_id";
			$filter['DrugFinance_id'] = $filter['DrugFinance_id'];
		}
		if (!empty($filter['WhsDocumentCostItemType_id']) && $filter['WhsDocumentCostItemType_id'] > 0) {
			$where .= " and wds.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
			$filter['WhsDocumentCostItemType_id'] = $filter['WhsDocumentCostItemType_id'];
		}

		$q = "
            select dor.DrugOstatRegistry_id as \"DrugOstatRegistry_id\",
                   wds.WhsDocumentUc_id as \"WhsDocumentUc_pid\",
                   wds.WhsDocumentUc_Num as \"WhsDocumentUc_Name\",
                   df.DrugFinance_Name as \"DrugFinance_Name\",
                   wdcit.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
                   COALESCE(sup.Org_Name, '') as \"Supplier_Name\",
                   o.Org_Name as \"Org_Name\",
                   dor.Drug_id as \"Drug_id\",
                   dor.Okei_id as \"Okei_id\",
                   am.RUSNAME as \"Actmatters_RusName\",
                   tn.NAME as \"Tradenames_Name\",
                   dform.NAME as \"DrugForm_Name\",
                   dose.Value as \"Drug_Dose\",
                   fas.Value as \"Drug_Fas\",
                   ceiling(dor.DrugOstatRegistry_Kolvo) as \"Kolvo\",
                   CAST(CAST(COALESCE(dor.DrugOstatRegistry_Sum / dor.DrugOstatRegistry_Kolvo, 0) as numeric(10, 2)) as varchar) as \"WhsDocumentOrderAllocationDrug_Price\",
                   rc.REGNUM as \"Reg_Num\",
                   rceff.FULLNAME as \"Reg_Firm\",
                   rceffc.NAME as \"Reg_Country\",
                   (to_char(rc.REGDATE, 'DD.MM.YYYY') || COALESCE(' - '  || to_char(rc.ENDDATE, 'DD.MM.YYYY'), '')) as \"Reg_Period\",
                   to_char(rc.Reregdate, 'DD.MM.YYYY') as \"Reg_ReRegDate\"
            from v_DrugOstatRegistry dor
                 left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
                 left join rls.v_Drug d on d.Drug_id = dor.Drug_id
                 left join v_DrugShipment ds on ds.DrugShipment_id = dor.DrugShipment_id
                 left join v_WhsDocumentSupply wds on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
                 left join v_DrugFinance df on df.DrugFinance_id = wds.DrugFinance_id
                 left join v_WhsDocumentCostItemType wdcit on wdcit.WhsDocumentCostItemType_id = wds.WhsDocumentCostItemType_id
                 left join v_Org sup on sup.Org_id = wds.Org_sid
                 left join v_Org o on o.Org_id = dor.Org_id
                 left join rls.v_prep p on p.Prep_id = d.DrugPrep_id
                 left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                 left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                 left join rls.v_ACTMATTERS am on am.ACTMATTERS_ID = dcmn.Actmatters_id
                 left join rls.v_TRADENAMES tn on tn.TRADENAMES_ID = p.TRADENAMEID
                 left join rls.v_CLSDRUGFORMS dform on dform.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
                 left join rls.REGCERT rc on rc.REGCERT_ID = p.REGCERTID
                 left join rls.REGCERT_EXTRAFIRMS rcef on rcef.CERTID = rc.REGCERT_ID
                 left join rls.v_FIRMS rceff on rceff.FIRMS_ID = rcef.FIRMID
                 left join rls.v_COUNTRIES rceffc on rceffc.COUNTRIES_ID = rceff.COUNTID
                 left join rls.MASSUNITS dfmu on dfmu.MASSUNITS_ID = p.DFMASSID
                 left join rls.CONCENUNITS dfcu on dfcu.CONCENUNITS_ID = p.DFCONCID
                 left join rls.ACTUNITS dfau on dfau.ACTUNITS_ID = p.DFACTID
                 left join rls.SIZEUNITS dfsu on dfsu.SIZEUNITS_ID = p.DFSIZEID
                 left join rls.v_Nomen n on n.NOMEN_ID = dor.Drug_id
                 left join rls.MASSUNITS mu on mu.MASSUNITS_ID = n.PPACKMASSUNID
                 left join rls.CUBICUNITS cu on cu.CUBICUNITS_ID = n.PPACKCUBUNID
                 LEFT JOIN LATERAL
                 (
                   select coalesce(cast (cast (p.DFMASS as decimal) as varchar) || ' ' || dfmu.SHORTNAME, cast (cast (p.DFCONC as decimal) as varchar) || ' ' || dfcu.SHORTNAME, cast (p.DFACT as varchar) || ' ' || dfau.SHORTNAME, cast (p.DFSIZE as varchar) || ' ' || dfsu.SHORTNAME) as Value
                 ) dose ON true
                 LEFT JOIN LATERAL
                 (
                   select ((case
                              when d.Drug_Fas is not null then cast (d.Drug_Fas as varchar) || ' доз'
                              else ''
                            end) ||(case
                                      when d.Drug_Fas is not null and coalesce(d.Drug_Volume, d.Drug_Mass) is not null then ', '
                                      else ''
                                    end) ||(case
                                              when coalesce(d.Drug_Volume, d.Drug_Mass) is not null then coalesce(d.Drug_Volume, d.Drug_Mass)
                                              else ''
                                            end)) as Value
                 ) fas ON true
            where dor.Org_id = dbo.GetMinzdravDloOrgId() and
                  dor.DrugOstatRegistry_Kolvo > 0 and
                  sat.SubAccountType_Code = 1 and
                  wds.WhsDocumentUc_pid is not null
            $where
		";

		//echo getDebugSql($q, $filter); die();
		$result = $this->db->query($q, $filter);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
}