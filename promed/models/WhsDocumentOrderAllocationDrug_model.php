<?php defined('BASEPATH') or die ('No direct script access allowed');

class WhsDocumentOrderAllocationDrug_model extends swModel {

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
				wdoad.WhsDocumentOrderAllocationDrug_id,
				svod_allocation.WhsDocumentUc_Num,
				d.Drug_Name,
				d.Drug_id,
				am.RUSNAME as ActMatters_RusName, --МНН
				tn.NAME as TradeName_Name, --Лекарственный препарат
				df.NAME as DrugForm_Name, --Форма выпуска
				Dose.Value as Drug_Dose, --Дозировка
				Fas.Value as Drug_Fas, --Фасовка
				rc.REGNUM as Reg_Num, --№ РУ
				fn.NAME as Firm_Name, --Производитель
				ltrim(str(wdoad.WhsDocumentOrderAllocationDrug_Kolvo,10,0)) as WhsDocumentOrderAllocationDrug_Kolvo,
				ltrim(str(wdoad.WhsDocumentOrderAllocationDrug_Price,10,2)) as WhsDocumentOrderAllocationDrug_Price,
				ltrim(str(wdoad.WhsDocumentOrderAllocationDrug_Price * wdoad.WhsDocumentOrderAllocationDrug_Kolvo,10,2)) as WhsDocumentOrderAllocationDrug_Sum
				-- end select
			from
				-- from
				v_WhsDocumentOrderAllocationDrug wdoad (nolock) -- содержимое разнарядки
				left join v_WhsDocumentOrderAllocation wdoa with (nolock) on wdoa.WhsDocumentOrderAllocation_id = wdoad.WhsDocumentOrderAllocation_id
				left join rls.v_Drug d with (nolock) on d.Drug_id = wdoad.Drug_id
				left join rls.Nomen n with (nolock) on n.NOMEN_ID = d.Drug_id
				left join rls.Prep p with (nolock) on p.Prep_id = n.PREPID
				left join rls.PREP_ACTMATTERS pa with (nolock) on pa.PREPID = n.PREPID
				left join rls.TRADENAMES tn with (nolock) on tn.TRADENAMES_ID = p.TRADENAMEID
				left join rls.CLSDRUGFORMS df with (nolock) on df.CLSDRUGFORMS_ID = p.DRUGFORMID
				left join rls.REGCERT rc with (nolock) on rc.REGCERT_ID = p.REGCERTID
				left join rls.FIRMS f with (nolock) on f.FIRMS_ID = p.FIRMID
				left join rls.FIRMNAMES fn with (nolock) on fn.FIRMNAMES_ID = f.NAMEID
				left join rls.ACTMATTERS am with (nolock) on am.ACTMATTERS_ID = pa.MATTERID
				left join rls.MASSUNITS mu with (nolock) on mu.MASSUNITS_ID = n.PPACKMASSUNID
				left join rls.CUBICUNITS cu with (nolock) on cu.CUBICUNITS_ID = n.PPACKCUBUNID
				left join rls.MASSUNITS df_mu with (nolock) on df_mu.MASSUNITS_ID = p.DFMASSID
				left join rls.CONCENUNITS df_cu with (nolock) on df_cu.CONCENUNITS_ID = p.DFCONCID
				left join rls.ACTUNITS df_au with (nolock) on df_au.ACTUNITS_ID = p.DFACTID
				left join rls.SIZEUNITS df_su with (nolock) on df_su.SIZEUNITS_ID = p.DFSIZEID
				outer apply (
					select coalesce(
						cast(cast(p.DFMASS as float) as varchar)+' '+df_mu.SHORTNAME,
						cast(cast(p.DFCONC as float) as varchar)+' '+df_cu.SHORTNAME,
						cast(p.DFACT as varchar)+' '+df_au.SHORTNAME,
						cast(p.DFSIZE as varchar)+' '+df_su.SHORTNAME
					) as Value
				) Dose
				outer apply(
					select (
						(case when D.Drug_Fas is not null then cast(D.Drug_Fas as varchar)+' доз' else '' end)+
						(case when D.Drug_Fas is not null and coalesce(D.Drug_Volume,D.Drug_Mass) is not null then ', ' else '' end)+
						(case when coalesce(D.Drug_Volume,D.Drug_Mass) is not null then coalesce(D.Drug_Volume,D.Drug_Mass) else '' end)
					) as Value
				) Fas
				outer apply (
					select top 1
						wds.WhsDocumentUc_Num
					from
						v_WhsDocumentOrderAllocation wdoa1 with (nolock)
						left join v_WhsDocumentOrderAllocationDrug wdoad1 with (nolock) on wdoad1.WhsDocumentOrderAllocation_id = wdoa1.WhsDocumentOrderAllocation_id
						left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentUc_id = wdoad1.WhsDocumentUc_pid
					where
						wdoa1.WhsDocumentOrderAllocation_id = wdoa.WhsDocumentUc_pid
						and wdoad1.Drug_id = wdoad.Drug_id
				) svod_allocation
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
				select top 1
					WDS.WhsDocumentSupply_id
				from v_WhsDocumentSupply WDS (nolock) -- все гк с указанным источником финансирования и статьёй расхода
					inner join v_WhsDocumentSupplySpec WDSS (nolock) ON WDSS.WhsDocumentSupply_id = WDS.WhsDocumentSupply_id -- спецификация ГК
					inner join v_DrugShipment ds (nolock) on ds.WhsDocumentSupply_id = WDSS.WhsDocumentSupply_id -- партии в ГК
					inner join v_DrugOstatRegistry DOR WITH (NOLOCK) ON DOR.DrugShipment_id = ds.DrugShipment_id and DOR.Drug_id = WDSS.Drug_id -- ЛС на регистре остатков
						and DOR.SubAccountType_id = 1 -- субсчёт доступно
						and DOR.Org_id = :Org_id -- организация пользователя (минздрав)
					inner join WhsDocumentUc wdu (nolock) on wdu.WhsDocumentUc_id = wds.WhsDocumentUc_pid and wdu.WhsDocumentType_id = 5 -- лоты для ГК
					inner join v_DrugRequestPurchaseSpec drps (nolock) on drps.WhsDocumentUc_id = wdu.WhsDocumentUc_id -- позиции сводной заявки
				where WDS.DrugFinance_id = :DrugFinance_id and WDS.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id and drps.DrugRequest_id = DR.DrugRequest_id
			)
		";
		
		$data['Org_id'] = $data['session']['org_id'];
		
		if (!empty($data['begDate']) && !empty($data['endDate'])) {
			$filter .= "
				and DR.DrugRequest_id in (
					select DrugRequestPurchase.DrugRequest_id from DrugRequestPurchase  with (nolock)
						left join DrugRequest with (nolock) on DrugRequest.DrugRequest_id = DrugRequestPurchase.DrugRequest_lid
					where
						DrugRequestPeriod_id in (
							select DrugRequestPeriod_id from DrugRequestPeriod with (nolock)
							where
								DrugRequestPeriod_begDate <= :endDate and
								DrugRequestPeriod_endDate >= :begDate
						)
				)			
			";
		}
		
		$query = "
			select
				DR.DrugRequest_id
				,DR.DrugRequest_Name
			from
				DrugRequest DR with(nolock)
				left join DrugRequestPeriod DRP with(nolock) on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
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
            declare
                @MinzdravOrg_id bigint; --id организации минздрава


            set @MinzdravOrg_id = dbo.GetMinzdravDloOrgId();

			select
				wds.DrugFinance_id,
				wds.WhsDocumentCostItemType_id,
				WDS.WhsDocumentUc_id as WhsDocumentUc_pid,
				WDS.WhsDocumentUc_Num as WhsDocumentUc_Name,
				isnull(Supplier.Org_Name, '') as Supplier_Name,
				dor.Drug_id,
				dor.Okei_id,
				am.RUSNAME as Actmatters_RusName,
				tn.NAME as Tradenames_Name,
				dform.NAME as DrugForm_Name,
				dose.Value as Drug_Dose,
				fas.Value as Drug_Fas,
				dor.DrugOstatRegistry_Kolvo as Available_Kolvo,
				CEILING(DOR.DrugOstatRegistry_Kolvo * :WhsDocumentOrderAllocation_Percent / 100) as WhsDocumentOrderAllocationDrug_Kolvo,
				:WhsDocumentOrderAllocation_Percent as WhsDocumentOrderAllocation_Percent,
				str(isnull(dor.DrugOstatRegistry_Cost, 0), 10, 2) as WhsDocumentOrderAllocationDrug_Price,
				rc.REGNUM as Reg_Num,
				rceff.FULLNAME as Reg_Firm,
				rceffc.NAME as Reg_Country,
				(convert(varchar, rc.REGDATE, 104)+isnull(' - '+convert(varchar, rc.ENDDATE, 104), '')) as Reg_Period,
				convert(varchar, rc.Reregdate, 104) as Reg_ReRegDate,
				'add' as [state]
			from
				DrugOstatRegistry dor with (nolock)
				left join SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
				left join rls.v_Drug d with (nolock) on d.Drug_id = dor.Drug_id
				left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dor.DrugShipment_id
				left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
				left join v_Org Supplier with (nolock) on Supplier.Org_id = wds.Org_sid
				left join rls.v_prep p with(nolock) on p.Prep_id = d.DrugPrep_id
                left join rls.v_DrugComplexMnn dcm with(nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                left join rls.v_DrugComplexMnnName dcmn with(nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                left join rls.v_ACTMATTERS am with(nolock) on am.ACTMATTERS_ID = dcmn.Actmatters_id
				left join rls.v_TRADENAMES tn with(nolock) on tn.TRADENAMES_ID = p.TRADENAMEID
				left join rls.v_CLSDRUGFORMS dform with(nolock) on dform.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
				left join rls.REGCERT rc with (nolock) on rc.REGCERT_ID = p.REGCERTID
				left join rls.REGCERT_EXTRAFIRMS rcef with (nolock) on rcef.CERTID = rc.REGCERT_ID
				left join rls.v_FIRMS rceff with(nolock) on rceff.FIRMS_ID = rcef.FIRMID
                left join rls.v_COUNTRIES rceffc with(nolock) on rceffc.COUNTRIES_ID = rceff.COUNTID
				left join rls.MASSUNITS dfmu with (nolock) on dfmu.MASSUNITS_ID = p.DFMASSID
				left join rls.CONCENUNITS dfcu with (nolock) on dfcu.CONCENUNITS_ID = p.DFCONCID
				left join rls.ACTUNITS dfau with (nolock) on dfau.ACTUNITS_ID = p.DFACTID
				left join rls.SIZEUNITS dfsu with (nolock) on dfsu.SIZEUNITS_ID = p.DFSIZEID
				left join rls.v_Nomen n with (nolock) on n.NOMEN_ID = dor.Drug_id
				left join rls.MASSUNITS mu with (nolock) on mu.MASSUNITS_ID = n.PPACKMASSUNID
				left join rls.CUBICUNITS cu with (nolock) on cu.CUBICUNITS_ID = n.PPACKCUBUNID
				outer apply (
					select coalesce(
						cast(cast(p.DFMASS as float) as varchar)+' '+dfmu.SHORTNAME,
						cast(cast(p.DFCONC as float) as varchar)+' '+dfcu.SHORTNAME,
						cast(p.DFACT as varchar)+' '+dfau.SHORTNAME,
						cast(p.DFSIZE as varchar)+' '+dfsu.SHORTNAME
					) as Value
				) dose
				outer apply(
					select (
						(case when d.Drug_Fas is not null then cast(d.Drug_Fas as varchar)+' доз' else '' end)+
						(case when d.Drug_Fas is not null and coalesce(d.Drug_Volume,d.Drug_Mass) is not null then ', ' else '' end)+
						(case when coalesce(d.Drug_Volume,d.Drug_Mass) is not null then coalesce(d.Drug_Volume,d.Drug_Mass) else '' end)
					) as Value
				) fas
			where
				dor.Org_id = @MinzdravOrg_id and
				dor.DrugOstatRegistry_Kolvo > 0 and
				sat.SubAccountType_Code = 1 and
				wds.DrugFinance_id = :DrugFinance_id and
				wds.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id and
				wds.WhsDocumentUc_pid is not null;
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
			select
				dor.Drug_id,
				max(dor.Okei_id) as Okei_id,
				d.Drug_Name,
				sum(DOR.DrugOstatRegistry_Kolvo) as WhsDocumentOrderAllocationDrug_Allocation,
				str(isnull(avg(wdss.WhsDocumentSupplySpec_PriceNDS), 0), 10, 2) as WhsDocumentOrderAllocationDrug_PriceNDS,
				null as Plan_Kolvo,
				'add' as [state]
			from
				DrugOstatRegistry dor with (nolock)
				left join SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
				left join rls.v_Drug d with (nolock) ON d.Drug_id = dor.Drug_id
				left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dor.DrugShipment_id
				left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
				outer apply (
					select top 1
						WhsDocumentSupplySpec_PriceNDS
					from
						v_WhsDocumentSupplySpec with (nolock)
					where
						WhsDocumentSupply_id = ds.WhsDocumentSupply_id and
						Drug_id = dor.Drug_id
				) wdss
			where
				dor.Org_id = :Org_id and
				dor.DrugOstatRegistry_Kolvo > 0 and
				sat.SubAccountType_Code = 1 and
				wds.DrugFinance_id = :DrugFinance_id and
				wds.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id and
				wds.WhsDocumentUc_pid is not null
			group by
				dor.Drug_id, d.Drug_Name
			order by
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
	 *  Формирование первоначального списка медикамента, подлежащих распределению (распределение ЛС по аптекам)
	 */
	function loadRAWFarmDrugList($filter) {
		$q = "
			select
				wdord.Drug_id,
				wdord.Okei_id,
				d.Drug_Name,
				ltrim(str(sum(wdord.WhsDocumentOrderAllocationDrug_Kolvo),10,0)) as WhsDocumentOrderAllocationDrug_Kolvo,
				ltrim(str(sum(wdord.WhsDocumentOrderAllocationDrug_Price * wdord.WhsDocumentOrderAllocationDrug_Kolvo)/sum(wdord.WhsDocumentOrderAllocationDrug_Kolvo),10,2)) as WhsDocumentOrderAllocationDrug_Price,
				ltrim(str(sum(wdord.WhsDocumentOrderAllocationDrug_Price * wdord.WhsDocumentOrderAllocationDrug_Kolvo),10,2)) as WhsDocumentUc_Sum,
				'add' as [state]
			from
				v_WhsDocumentOrderAllocationDrug wdord with (nolock)
				left join v_WhsDocumentOrderAllocation wdoa with (nolock) on wdoa.WhsDocumentOrderAllocation_id = wdord.WhsDocumentOrderAllocation_id
				left join rls.v_Drug d with (nolock) on d.Drug_id = wdord.Drug_id
				left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentUc_id = wdord.WhsDocumentUc_pid
			where
				wdord.WhsDocumentOrderAllocation_id in (
					select
						WhsDocumentOrderAllocation_id
					from
						v_WhsDocumentOrderAllocation with(nolock)
					where
						WhsDocumentUc_pid = :WhsDocumentUc_id
				) and
				wds.Org_sid = :Org_id
			group by
				wdord.Drug_id, wdord.Okei_id, d.Drug_Name;
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
			declare
				@MinzdravOrg_id bigint, --id организации минздрава
				@SvodRequest_id bigint = :DrugRequest_id, --id сводной заявки
				@percent int = :WhsDocumentOrderAllocation_Percent, --процент количества ЛС включенного в разнардку
				@DrugFinance_id bigint = :DrugFinance_id,
				@WhsDocumentCostItemType_id bigint = :WhsDocumentCostItemType_id;


			set @MinzdravOrg_id = dbo.GetMinzdravDloOrgId();

			with req_data as ( -- данные из заявок врачей
				select
					dr.Lpu_id,
					drr.DrugComplexMnn_id,
					sum(drr.DrugRequestRow_Kolvo) as kolvo
				from
					v_DrugRequestPurchase drp with (nolock)
					left join v_DrugRequest regdr with (nolock) on regdr.DrugRequest_id = drp.DrugRequest_lid
					left join v_DrugRequest dr with (nolock) on
						dr.DrugRequest_Version is null and
						dr.DrugRequestPeriod_id = regdr.DrugRequestPeriod_id and
						isnull(dr.PersonRegisterType_id, 0) = isnull(regdr.PersonRegisterType_id, 0) and
						isnull(dr.DrugRequestKind_id, 0) = isnull(regdr.DrugRequestKind_id, 0) and
						isnull(dr.DrugGroup_id, 0) = isnull(regdr.DrugGroup_id, 0) and
						dr.Lpu_id is not null and
						dr.MedPersonal_id is not null
					left join v_DrugRequestRow drr with (nolock) on drr.DrugRequest_id = dr.DrugRequest_id
					inner join v_Lpu l with (nolock) on l.Lpu_id = dr.Lpu_id
				where
					drp.DrugRequest_id = @SvodRequest_id and
					drr.DrugFinance_id = @DrugFinance_id and
					drr.DrugComplexMnn_id is not null
				group by
					dr.Lpu_id, drr.DrugComplexMnn_id
			),
			svod_data as ( -- данные из сводной заявки
				select
					DrugComplexMnn_id,
					DrugRequestPurchaseSpec_Kolvo
				from
					DrugRequestPurchaseSpec with(nolock)
				where
					DrugRequest_id = @SvodRequest_id and
					DrugFinance_id = @DrugFinance_id
			)
			select
				data.Lpu_id,
				o.Org_id,
				o.Org_Name as Lpu_Name,
				data.request_kolvo as Kolvo_Request,
				data.total_request_kolvo as Kolvo_Available,
				data.total_kolvo as WhsDocumentOrderAllocationDrug_Kolvo,
				data.WhsDocumentSupply_id as WhsDocumentUc_pid,
				data.WhsDocumentUc_Num,
				isnull(Supplier.Org_Name, '') as Supplier_Name,
				data.Drug_id,
				data.Okei_id,
				tn.NAME as Tradenames_Name,
				dform.NAME as DrugForm_Name,
				dose.Value as Drug_Dose,
				fas.Value as Drug_Fas,
				round((data.total_kolvo * 100)/data.request_kolvo, 2) as WhsDocumentOrderAllocationDrug_Percent,
				isnull(data.price, 0) as WhsDocumentOrderAllocationDrug_Price,
				isnull(data.total_kolvo * data.price,0) as WhsDocumentUc_Sum,
				rc.REGNUM as Reg_Num,
				rceff.FULLNAME as Reg_Firm,
				rceffc.NAME as Reg_Country,
				(convert(varchar, rc.REGDATE, 104)+isnull(' - '+convert(varchar, rc.ENDDATE, 104), '')) as Reg_Period,
				convert(varchar, rc.Reregdate, 104) as Reg_ReRegDate,
				'add' as [state]
			from (
				select
					con.WhsDocumentSupply_id,
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
					round(
						case
							when
								svod_data.DrugRequestPurchaseSpec_Kolvo <= con.kolvo -- если в сводной разнарядке медикамента хватает на полное покрытие потребностей
							then
								req_data.kolvo
							else
								(con.kolvo*req_data.kolvo)/svod_data.DrugRequestPurchaseSpec_Kolvo
						end, 0
					) as total_kolvo
				from (
					select
						wds.WhsDocumentSupply_id,
						wds.WhsDocumentUc_Num,
						wds.Org_sid,
						dor.Drug_id,
						max(dor.Okei_id) as Okei_id,
						max(d.DrugComplexMnn_id) as mnn_id,
						ceiling(sum(DOR.DrugOstatRegistry_Kolvo)*@percent/100) as kolvo,
						str(isnull(dor.DrugOstatRegistry_Cost, 0), 10, 2) as price
					from
						DrugOstatRegistry dor with (nolock)
						left join SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
						left join rls.v_Drug d with (nolock) ON d.Drug_id = dor.Drug_id
						left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dor.DrugShipment_id
						left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
						left join v_WhsDocumentSupplySpec wdss with (nolock) on wdss.WhsDocumentSupply_id = ds.WhsDocumentSupply_id and wdss.Drug_id = dor.Drug_id
					where
						dor.DrugOstatRegistry_Kolvo > 0 and
						dor.Org_id = @MinzdravOrg_id and
						sat.SubAccountType_Code = 1 and
						wds.DrugFinance_id = @DrugFinance_id and
						wds.WhsDocumentCostItemType_id = @WhsDocumentCostItemType_id and
						wds.WhsDocumentUc_pid is not null
					group by
						wds.WhsDocumentSupply_id, wds.WhsDocumentUc_Num, wds.Org_sid, dor.Drug_id, dor.DrugOstatRegistry_Cost
				) con
				left join req_data with(nolock) on req_data.DrugComplexMnn_id = con.mnn_id
				left join svod_data with(nolock) on svod_data.DrugComplexMnn_id = con.mnn_id
				where
					req_data.kolvo > 0
			) data
			left join rls.v_Drug d with (nolock) on d.Drug_id = data.Drug_id
			left join Lpu l with (nolock) on l.Lpu_id = data.Lpu_id
			left join Org o with (nolock) on o.Org_id = l.Org_id
			left join v_Org Supplier with (nolock) on Supplier.Org_id = data.Org_sid
			left join rls.v_prep p with(nolock) on p.Prep_id = d.DrugPrep_id
            left join rls.v_DrugComplexMnn dcm with(nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
            left join rls.v_TRADENAMES tn with(nolock) on tn.TRADENAMES_ID = p.TRADENAMEID
            left join rls.v_CLSDRUGFORMS dform with(nolock) on dform.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
            left join rls.REGCERT rc with (nolock) on rc.REGCERT_ID = p.REGCERTID
            left join rls.REGCERT_EXTRAFIRMS rcef with (nolock) on rcef.CERTID = rc.REGCERT_ID
            left join rls.v_FIRMS rceff with(nolock) on rceff.FIRMS_ID = rcef.FIRMID
            left join rls.v_COUNTRIES rceffc with(nolock) on rceffc.COUNTRIES_ID = rceff.COUNTID
            left join rls.MASSUNITS dfmu with (nolock) on dfmu.MASSUNITS_ID = p.DFMASSID
            left join rls.CONCENUNITS dfcu with (nolock) on dfcu.CONCENUNITS_ID = p.DFCONCID
            left join rls.ACTUNITS dfau with (nolock) on dfau.ACTUNITS_ID = p.DFACTID
            left join rls.SIZEUNITS dfsu with (nolock) on dfsu.SIZEUNITS_ID = p.DFSIZEID
            left join rls.v_Nomen n with (nolock) on n.NOMEN_ID = d.Drug_id
            left join rls.MASSUNITS mu with (nolock) on mu.MASSUNITS_ID = n.PPACKMASSUNID
            left join rls.CUBICUNITS cu with (nolock) on cu.CUBICUNITS_ID = n.PPACKCUBUNID
            outer apply (
                select coalesce(
                    cast(cast(p.DFMASS as float) as varchar)+' '+dfmu.SHORTNAME,
                    cast(cast(p.DFCONC as float) as varchar)+' '+dfcu.SHORTNAME,
                    cast(p.DFACT as varchar)+' '+dfau.SHORTNAME,
                    cast(p.DFSIZE as varchar)+' '+dfsu.SHORTNAME
                ) as Value
            ) dose
            outer apply(
                select (
                    (case when d.Drug_Fas is not null then cast(d.Drug_Fas as varchar)+' доз' else '' end)+
                    (case when d.Drug_Fas is not null and coalesce(d.Drug_Volume,d.Drug_Mass) is not null then ', ' else '' end)+
                    (case when coalesce(d.Drug_Volume,d.Drug_Mass) is not null then coalesce(d.Drug_Volume,d.Drug_Mass) else '' end)
                ) as Value
            ) fas
			where
				data.total_kolvo > 0;
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
			declare
				@ReceptDelayType_id bigint;

			set @ReceptDelayType_id = (select top 1 ReceptDelayType_id from v_ReceptDelayType with(nolock) where ReceptDelayType_Code = 1); --отложен

			with ostat as ( -- данные по остаткам на счету организации
				--select 216779 as Drug_id, 120 as Okei_id, 100 as price, 17 as kolvo union
				select
					dor.Drug_id,
					max(dor.Okei_id) as Okei_id,
					avg(wdss.price) as price,
					sum(DOR.DrugOstatRegistry_Kolvo) as kolvo
				from
					DrugOstatRegistry dor with (nolock)
					left join SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
					left join rls.v_Drug d with (nolock) ON d.Drug_id = dor.Drug_id
					left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dor.DrugShipment_id
					left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
					outer apply (
						select top 1
							WhsDocumentSupplySpec_PriceNDS as price
						from
							v_WhsDocumentSupplySpec with (nolock)
						where
							WhsDocumentSupply_id = ds.WhsDocumentSupply_id and
							Drug_id = dor.Drug_id
					) wdss
				where
					dor.Org_id = :Org_id and
					dor.DrugOstatRegistry_Kolvo > 0 and
					sat.SubAccountType_Code = 1 and
					wds.DrugFinance_id = :DrugFinance_id and
					wds.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id and
					wds.WhsDocumentUc_pid is not null
				group by
					dor.Drug_id, d.Drug_Name
			),
			total_drug as ( -- суммараная потребность в медикаментах
				select
					er.Drug_rlsid as Drug_id,
					sum(er.EvnRecept_Kolvo) as kolvo
				from
					v_EvnRecept er with (nolock)
					inner join v_OrgFarmacy ofr with(nolock) on ofr.OrgFarmacy_id = er.OrgFarmacy_oid
					inner join v_Org o with(nolock) on o.Org_id = ofr.Org_id
					inner join rls.v_Drug d with(nolock) on d.Drug_id = er.Drug_rlsid
				where
					er.ReceptDelayType_id = @ReceptDelayType_id and
					er.Drug_rlsid is not null and
					er.OrgFarmacy_oid is not null
				group by
					Drug_rlsid
			)
			select
				o.Org_id,
				o.Org_Name,
				d.Drug_id,
				d.Drug_Name,
				ostat.Okei_id,
				p.EvnRecept_Kolvo as recept_kolvo,
				isnull(ostat.kolvo, 0) as ost_kolvo,
				total_drug.kolvo as total_recept_kolvo,
				(case
					when ostat.kolvo < total_drug.kolvo then ceiling((ostat.kolvo/total_drug.kolvo)*p.EvnRecept_Kolvo)
					else p.EvnRecept_Kolvo
				end) as kolvo,
				ostat.price as price,
				'add' as [state]
			from (
					select
						ofr.Org_id,
						er.Drug_rlsid,
						sum(er.EvnRecept_Kolvo) as EvnRecept_Kolvo
					from
						v_EvnRecept er with (nolock)
						inner join v_OrgFarmacy ofr with(nolock) on ofr.OrgFarmacy_id = er.OrgFarmacy_oid
					where
						er.ReceptDelayType_id = @ReceptDelayType_id and
						er.Drug_rlsid is not null and
						er.OrgFarmacy_oid is not null
					group by
						ofr.Org_id, er.Drug_rlsid
				) p
				inner join v_Org o with(nolock) on o.Org_id = p.Org_id
				inner join rls.v_Drug d with(nolock) on d.Drug_id = p.Drug_rlsid
				inner join ostat with(nolock) on ostat.Drug_id = d.Drug_id
				left join total_drug with(nolock) on total_drug.Drug_id = d.Drug_id
			order by
				o.Org_Name, d.Drug_Name;
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
			select
				ofi.Org_id,
				isnull(ofi.OrgFarmacy_Name, '') as Org_Name,
				wdord.Drug_id,
				wdord.Okei_id,
				d.Drug_Name,
				ltrim(sum(ceiling(wdord.WhsDocumentOrderAllocationDrug_Kolvo/farmacy_count.cnt))) as WhsDocumentOrderAllocationDrug_Kolvo,
				sum(wdord.WhsDocumentOrderAllocationDrug_Kolvo) as Total_Kolvo,
				ltrim(str(sum(wdord.WhsDocumentOrderAllocationDrug_Price * wdord.WhsDocumentOrderAllocationDrug_Kolvo)/sum(wdord.WhsDocumentOrderAllocationDrug_Kolvo),10,2)) as WhsDocumentOrderAllocationDrug_Price,
				ltrim(str(sum(wdord.WhsDocumentOrderAllocationDrug_Price * wdord.WhsDocumentOrderAllocationDrug_Kolvo),10,2)) as WhsDocumentUc_Sum,
				'add' as [state]
			from
				v_WhsDocumentOrderAllocationDrug wdord with (nolock)
				left join v_WhsDocumentOrderAllocation wdoa with (nolock) on wdoa.WhsDocumentOrderAllocation_id = wdord.WhsDocumentOrderAllocation_id
				left join rls.v_Drug d with (nolock) on d.Drug_id = wdord.Drug_id
				left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentUc_id = wdord.WhsDocumentUc_pid
				inner join v_Lpu l with (nolock) on l.Org_id = wdoa.Org_id
				inner join v_OrgFarmacyIndex ofi with (nolock) on ofi.Lpu_id = l.Lpu_id
				outer apply (
					select
						count(OrgFarmacy_id) as cnt
					from
						OrgFarmacyIndex with (nolock)
					where
						Lpu_id = l.Lpu_id
				) farmacy_count
			where
				wdord.WhsDocumentOrderAllocation_id in (
					select
						WhsDocumentOrderAllocation_id
					from
						v_WhsDocumentOrderAllocation with(nolock)
					where
						WhsDocumentUc_pid = :WhsDocumentUc_id
				) and
				wds.Org_sid = :Org_id
			group by
				ofi.Org_id, ofi.OrgFarmacy_Name, wdord.Drug_id, wdord.Okei_id, d.Drug_Name
			order by
				max(ofi.OrgFarmacyIndex_Index) desc;
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
            declare
				@MinzdravOrg_id bigint; --id организации минздрава

			set @MinzdravOrg_id = dbo.GetMinzdravDloOrgId();

			select
				wdord.WhsDocumentOrderAllocationDrug_id,
				wdord.WhsDocumentUc_pid,
				wds.WhsDocumentUc_Num as WhsDocumentUc_Name,
				isnull(Supplier.Org_Name, '') as Supplier_Name,
				wdord.Drug_id,
				wdord.Okei_id,
				am.RUSNAME as Actmatters_RusName,
				tn.NAME as Tradenames_Name,
				dform.NAME as DrugForm_Name,
				dose.Value as Drug_Dose,
				fas.Value as Drug_Fas,
				--FLOOR(STR(wdord.WhsDocumentOrderAllocationDrug_Kolvo,10,0)*100/wdoa.WhsDocumentOrderAllocation_Percent) as Available_Kolvo,
				LTRIM(STR(isnull(Available_Drug.Kolvo, 0))) as Available_Kolvo,
				LTRIM(STR(wdord.WhsDocumentOrderAllocationDrug_Kolvo,10,0)) as WhsDocumentOrderAllocationDrug_Kolvo,
				--wdoa.WhsDocumentOrderAllocation_Percent,
				(case
					when
						Available_Drug.Kolvo > 0
					then
						LTRIM(STR((WhsDocumentOrderAllocationDrug_Kolvo/(Available_Drug.Kolvo))*100,10,2))
					else
						null
				end) as WhsDocumentOrderAllocation_Percent,
				LTRIM(STR(wdord.WhsDocumentOrderAllocationDrug_Price,10,2)) as WhsDocumentOrderAllocationDrug_Price,
				LTRIM(STR(wdord.WhsDocumentOrderAllocationDrug_Price * wdord.WhsDocumentOrderAllocationDrug_Kolvo,10,2)) as WhsDocumentUc_Sum,
				rc.REGNUM as Reg_Num,
				rceff.FULLNAME as Reg_Firm,
				rceffc.NAME as Reg_Country,
				(convert(varchar, rc.REGDATE, 104)+isnull(' - '+convert(varchar, rc.ENDDATE, 104), '')) as Reg_Period,
				convert(varchar, rc.Reregdate, 104) as Reg_ReRegDate
			from
				v_WhsDocumentOrderAllocationDrug wdord with (nolock)
				left join v_WhsDocumentOrderAllocation wdoa with (nolock) on wdoa.WhsDocumentOrderAllocation_id = wdord.WhsDocumentOrderAllocation_id
				left join rls.v_Drug d with (nolock) on d.Drug_id = wdord.Drug_id
				left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentUc_id = wdord.WhsDocumentUc_pid
				left join v_Org Supplier with (nolock) on Supplier.Org_id = wds.Org_sid
				left join rls.v_prep p with(nolock) on p.Prep_id = d.DrugPrep_id
                left join rls.v_DrugComplexMnn dcm with(nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                left join rls.v_DrugComplexMnnName dcmn with(nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                left join rls.v_ACTMATTERS am with(nolock) on am.ACTMATTERS_ID = dcmn.Actmatters_id
				left join rls.v_TRADENAMES tn with(nolock) on tn.TRADENAMES_ID = p.TRADENAMEID
				left join rls.v_CLSDRUGFORMS dform with(nolock) on dform.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
				left join rls.REGCERT rc with (nolock) on rc.REGCERT_ID = p.REGCERTID
				left join rls.REGCERT_EXTRAFIRMS rcef with (nolock) on rcef.CERTID = rc.REGCERT_ID
				left join rls.v_FIRMS rceff with(nolock) on rceff.FIRMS_ID = rcef.FIRMID
                left join rls.v_COUNTRIES rceffc with(nolock) on rceffc.COUNTRIES_ID = rceff.COUNTID
				left join rls.MASSUNITS dfmu with (nolock) on dfmu.MASSUNITS_ID = p.DFMASSID
				left join rls.CONCENUNITS dfcu with (nolock) on dfcu.CONCENUNITS_ID = p.DFCONCID
				left join rls.ACTUNITS dfau with (nolock) on dfau.ACTUNITS_ID = p.DFACTID
				left join rls.SIZEUNITS dfsu with (nolock) on dfsu.SIZEUNITS_ID = p.DFSIZEID
				left join rls.v_Nomen n with (nolock) on n.NOMEN_ID = wdord.Drug_id
				left join rls.MASSUNITS mu with (nolock) on mu.MASSUNITS_ID = n.PPACKMASSUNID
				left join rls.CUBICUNITS cu with (nolock) on cu.CUBICUNITS_ID = n.PPACKCUBUNID
				outer apply (
					select coalesce(
						cast(cast(p.DFMASS as float) as varchar)+' '+dfmu.SHORTNAME,
						cast(cast(p.DFCONC as float) as varchar)+' '+dfcu.SHORTNAME,
						cast(p.DFACT as varchar)+' '+dfau.SHORTNAME,
						cast(p.DFSIZE as varchar)+' '+dfsu.SHORTNAME
					) as Value
				) dose
				outer apply(
					select (
						(case when d.Drug_Fas is not null then cast(d.Drug_Fas as varchar)+' доз' else '' end)+
						(case when d.Drug_Fas is not null and coalesce(d.Drug_Volume,d.Drug_Mass) is not null then ', ' else '' end)+
						(case when coalesce(d.Drug_Volume,d.Drug_Mass) is not null then coalesce(d.Drug_Volume,d.Drug_Mass) else '' end)
					) as Value
				) fas
				outer apply (
					select
						isnull(a_dor.DrugOstatRegistry_Kolvo, 0) as Kolvo
					from
						DrugOstatRegistry a_dor with (nolock)
						left join SubAccountType a_sat with (nolock) on a_sat.SubAccountType_id = a_dor.SubAccountType_id
						left join v_DrugShipment a_ds with (nolock) on a_ds.DrugShipment_id = a_dor.DrugShipment_id
						left join v_WhsDocumentSupply a_wds with (nolock) on a_wds.WhsDocumentSupply_id = a_ds.WhsDocumentSupply_id
					where
						a_dor.Org_id = @MinzdravOrg_id and
						a_dor.DrugOstatRegistry_Kolvo > 0 and
						a_dor.Drug_id = wdord.Drug_id and
						a_sat.SubAccountType_Code = 1 and
						a_wds.WhsDocumentUc_id = wdord.WhsDocumentUc_pid
				) Available_Drug
			where
				wdord.WhsDocumentOrderAllocation_id = :WhsDocumentOrderAllocation_id
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
				select
					Drug_id,
					sum(WhsDocumentOrderAllocationDrug_Kolvo) as kolvo
				from
					v_WhsDocumentOrderAllocationDrug with (nolock)
				where
					WhsDocumentOrderAllocation_id in (select WhsDocumentOrderAllocation_id from v_WhsDocumentOrderAllocation (nolock) where WhsDocumentUc_pid = :WhsDocumentOrderAllocation_id)
				group by
					Drug_id
			)
			select
				wdord.WhsDocumentOrderAllocationDrug_id,
				wdord.WhsDocumentUc_pid,
				wds.WhsDocumentUc_Num as WhsDocumentUc_Name,
				isnull(Supplier.Org_Name, '') as Supplier_Name,
				wdord.Drug_id,
				wdord.Okei_id,
				d.Drug_Name,
				ltrim(str(wdord.WhsDocumentOrderAllocationDrug_Kolvo,10,0)) as WhsDocumentOrderAllocationDrug_Allocation,
				ltrim(str(wdord.WhsDocumentOrderAllocationDrug_Price,10,2)) as WhsDocumentOrderAllocationDrug_PriceNDS,
				str(pln.kolvo,10,0) as Plan_Kolvo
			from
				v_WhsDocumentOrderAllocationDrug wdord with (nolock)
				left join v_WhsDocumentOrderAllocation wdoa with (nolock) on wdoa.WhsDocumentOrderAllocation_id = wdord.WhsDocumentOrderAllocation_id
				left join rls.v_Drug d with (nolock) on d.Drug_id = wdord.Drug_id
				left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentUc_id = wdord.WhsDocumentUc_pid
				left join v_Org Supplier with (nolock) on Supplier.Org_id = wds.Org_sid
				left join pln with(nolock) on pln.Drug_id = wdord.Drug_id
			where
				wdord.WhsDocumentOrderAllocation_id = :WhsDocumentOrderAllocation_id
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
				select
					Drug_id,
					sum(WhsDocumentOrderAllocationDrug_Kolvo) as kolvo
				from
					v_WhsDocumentOrderAllocationDrug with (nolock)
				where
					WhsDocumentOrderAllocation_id in (select WhsDocumentOrderAllocation_id from v_WhsDocumentOrderAllocation (nolock) where WhsDocumentUc_pid = :WhsDocumentOrderAllocation_id)
				group by
					Drug_id
			)
			select
				wdord.WhsDocumentOrderAllocationDrug_id,
				wdord.WhsDocumentUc_pid,
				wdord.Drug_id,
				wdord.Okei_id,
				d.Drug_Name,
				ltrim(str(wdord.WhsDocumentOrderAllocationDrug_Kolvo,10,0)) as WhsDocumentOrderAllocationDrug_Kolvo,
				ltrim(str(wdord.WhsDocumentOrderAllocationDrug_Price,10,2)) as WhsDocumentOrderAllocationDrug_Price,
				str(spec_list.kolvo,10,0) as Plan_Kolvo
			from
				v_WhsDocumentOrderAllocationDrug wdord with (nolock)
				left join rls.v_Drug d with (nolock) on d.Drug_id = wdord.Drug_id
				left join spec_list with(nolock) on spec_list.Drug_id = wdord.Drug_id
			where
				wdord.WhsDocumentOrderAllocation_id = :WhsDocumentOrderAllocation_id;
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
			declare
				@SvodRequest_id bigint,
				@DrugFinance_id bigint,
				@begDate date,
				@endDate date,
				@WhsDocumentOrderAllocation_id bigint = :WhsDocumentOrderAllocation_id;

			select @DrugFinance_id = DrugFinance_id, @begDate = WhsDocumentOrderAllocation_BegDate, @endDate = WhsDocumentOrderAllocation_EndDate from v_WhsDocumentOrderAllocation with(nolock) where WhsDocumentOrderAllocation_id = @WhsDocumentOrderAllocation_id;

			set @SvodRequest_id = (select top 1 DrugRequest_id from v_WhsDocumentOrderAllocation (nolock) where WhsDocumentOrderAllocation_id = @WhsDocumentOrderAllocation_id);

			with req_data as ( -- данные из заявок врачей
				select
					l.Org_id,
					drr.DrugComplexMnn_id,
					sum(drr.DrugRequestRow_Kolvo) as kolvo
				from
					v_DrugRequestPurchase drp with (nolock)
					left join v_DrugRequest regdr with (nolock) on regdr.DrugRequest_id = drp.DrugRequest_lid
					left join v_DrugRequest dr with (nolock) on
						dr.DrugRequest_Version is null and
						dr.DrugRequestPeriod_id = regdr.DrugRequestPeriod_id and
						isnull(dr.PersonRegisterType_id, 0) = isnull(regdr.PersonRegisterType_id, 0) and
						isnull(dr.DrugRequestKind_id, 0) = isnull(regdr.DrugRequestKind_id, 0) and
						isnull(dr.DrugGroup_id, 0) = isnull(regdr.DrugGroup_id, 0) and
						dr.Lpu_id is not null and
						dr.MedPersonal_id is not null
					left join v_DrugRequestRow drr with (nolock) on drr.DrugRequest_id = dr.DrugRequest_id
					inner join v_Lpu l with (nolock) on l.Lpu_id = dr.Lpu_id
				where
					drp.DrugRequest_id = @SvodRequest_id and
					drr.DrugFinance_id = @DrugFinance_id and
					drr.DrugComplexMnn_id is not null
				group by
					l.Org_id, drr.DrugComplexMnn_id
			)
			SELECT
				WDORD.WhsDocumentOrderAllocationDrug_id,
				WDORD.WhsDocumentOrderAllocation_id,
				WDORD.WhsDocumentUc_pid,
				WDS.WhsDocumentUc_Num,
				WDS.WhsDocumentUc_Name,
				WDORD.Drug_id,
				WDORD.Okei_id,
				tn.NAME as Tradenames_Name,
				dform.NAME as DrugForm_Name,
				dose.Value as Drug_Dose,
				fas.Value as Drug_Fas,
				O.Org_Name as Lpu_Name,
				O.Org_id,
				isnull(Supplier.Org_Name, '') as Supplier_Name,
				req_data.kolvo as Kolvo_Request,
				LTRIM(STR(WDORD.WhsDocumentOrderAllocationDrug_Kolvo,10,0)) as WhsDocumentOrderAllocationDrug_Kolvo,
				LTRIM(STR(round((WDORD.WhsDocumentOrderAllocationDrug_Kolvo * 100)/req_data.kolvo, 2),10,2)) as WhsDocumentOrderAllocationDrug_Percent,
				LTRIM(STR(WDORD.WhsDocumentOrderAllocationDrug_Price,10,2)) as WhsDocumentOrderAllocationDrug_Price,
				LTRIM(STR(WDORD.WhsDocumentOrderAllocationDrug_Price * WDORD.WhsDocumentOrderAllocationDrug_Kolvo,10,2)) as WhsDocumentUc_Sum,
				rc.REGNUM as Reg_Num,
				rceff.FULLNAME as Reg_Firm,
				rceffc.NAME as Reg_Country,
				(convert(varchar, rc.REGDATE, 104)+isnull(' - '+convert(varchar, rc.ENDDATE, 104), '')) as Reg_Period,
				convert(varchar, rc.Reregdate, 104) as Reg_ReRegDate,
				null as state
			FROM
				v_WhsDocumentOrderAllocationDrug wdord with (nolock)
				left join v_WhsDocumentOrderAllocation wdoa (nolock) on wdoa.WhsDocumentOrderAllocation_id = wdord.WhsDocumentOrderAllocation_id
				left join rls.v_Drug d with (nolock) on d.Drug_id = wdord.Drug_id
				left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentUc_id = wdord.WhsDocumentUc_pid
				left join v_Org Supplier with (nolock) on Supplier.Org_id = wds.Org_sid
				inner join v_Org O (nolock) on O.Org_id = wdoa.Org_id
				left join req_data with(nolock) on req_data.Org_id = wdoa.Org_id and req_data.DrugComplexMnn_id = D.DrugComplexMnn_id
				left join rls.v_prep p with(nolock) on p.Prep_id = d.DrugPrep_id
                left join rls.v_DrugComplexMnn dcm with(nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
				left join rls.v_TRADENAMES tn with(nolock) on tn.TRADENAMES_ID = p.TRADENAMEID
				left join rls.v_CLSDRUGFORMS dform with(nolock) on dform.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
				left join rls.REGCERT rc with (nolock) on rc.REGCERT_ID = p.REGCERTID
				left join rls.REGCERT_EXTRAFIRMS rcef with (nolock) on rcef.CERTID = rc.REGCERT_ID
				left join rls.v_FIRMS rceff with(nolock) on rceff.FIRMS_ID = rcef.FIRMID
                left join rls.v_COUNTRIES rceffc with(nolock) on rceffc.COUNTRIES_ID = rceff.COUNTID
				left join rls.MASSUNITS dfmu with (nolock) on dfmu.MASSUNITS_ID = p.DFMASSID
				left join rls.CONCENUNITS dfcu with (nolock) on dfcu.CONCENUNITS_ID = p.DFCONCID
				left join rls.ACTUNITS dfau with (nolock) on dfau.ACTUNITS_ID = p.DFACTID
				left join rls.SIZEUNITS dfsu with (nolock) on dfsu.SIZEUNITS_ID = p.DFSIZEID
				left join rls.v_Nomen n with (nolock) on n.NOMEN_ID = d.Drug_id
				left join rls.MASSUNITS mu with (nolock) on mu.MASSUNITS_ID = n.PPACKMASSUNID
				left join rls.CUBICUNITS cu with (nolock) on cu.CUBICUNITS_ID = n.PPACKCUBUNID
				outer apply (
					select coalesce(
						cast(cast(p.DFMASS as float) as varchar)+' '+dfmu.SHORTNAME,
						cast(cast(p.DFCONC as float) as varchar)+' '+dfcu.SHORTNAME,
						cast(p.DFACT as varchar)+' '+dfau.SHORTNAME,
						cast(p.DFSIZE as varchar)+' '+dfsu.SHORTNAME
					) as Value
				) dose
				outer apply(
					select (
						(case when d.Drug_Fas is not null then cast(d.Drug_Fas as varchar)+' доз' else '' end)+
						(case when d.Drug_Fas is not null and coalesce(d.Drug_Volume,d.Drug_Mass) is not null then ', ' else '' end)+
						(case when coalesce(d.Drug_Volume,d.Drug_Mass) is not null then coalesce(d.Drug_Volume,d.Drug_Mass) else '' end)
					) as Value
				) fas
			WHERE 
				WDORD.WhsDocumentOrderAllocation_id IN (select WhsDocumentOrderAllocation_id from v_WhsDocumentOrderAllocation (nolock) where WhsDocumentUc_pid = @WhsDocumentOrderAllocation_id and WhsDocumentType_id = 9)
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
			declare
				@WhsDocumentOrderAllocation_id bigint = :WhsDocumentOrderAllocation_id;

			select
				WDORD.WhsDocumentOrderAllocationDrug_id,
				WDORD.WhsDocumentOrderAllocation_id,
				WDORD.WhsDocumentUc_pid,
				WDORD.Drug_id,
				WDORD.Okei_id,
				D.Drug_Name,
				O.Org_Name,
				O.Org_id,
				str(WDORD.WhsDocumentOrderAllocationDrug_Kolvo,10,0) as kolvo,
				WDORD.WhsDocumentOrderAllocationDrug_Price as price,
				'edit' as state
			from
				v_WhsDocumentOrderAllocationDrug WDORD WITH (NOLOCK)
				left join v_WhsDocumentOrderAllocation WDOA (nolock) on WDOA.WhsDocumentOrderAllocation_id = WDORD.WhsDocumentOrderAllocation_id
				LEFT JOIN rls.v_Drug D WITH (NOLOCK) ON D.Drug_id = WDORD.Drug_id
				LEFT JOIN v_WhsDocumentSupply WDS WITH (NOLOCK) ON WDS.WhsDocumentUc_id = WDORD.WhsDocumentUc_pid
				inner join v_Org O (nolock) on O.Org_id = WDOA.Org_id
			where
				WDORD.WhsDocumentOrderAllocation_id IN (select WhsDocumentOrderAllocation_id from v_WhsDocumentOrderAllocation (nolock) where WhsDocumentUc_pid = @WhsDocumentOrderAllocation_id);

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
			declare
				@WhsDocumentType_id bigint,
				@WhsDocumentOrderAllocation_id bigint = :WhsDocumentOrderAllocation_id;

			select
				WDORD.WhsDocumentOrderAllocationDrug_id,
				WDORD.WhsDocumentOrderAllocation_id,
				WDORD.WhsDocumentUc_pid,
				WDORD.Drug_id,
				WDORD.Okei_id,
				D.Drug_Name,
				O.Org_Name,
				O.Org_id,
				str(WDORD.WhsDocumentOrderAllocationDrug_Kolvo,10,0) as WhsDocumentOrderAllocationDrug_Kolvo,
				WDORD.WhsDocumentOrderAllocationDrug_Price as WhsDocumentOrderAllocationDrug_Price,
				'' as state
			from
				v_WhsDocumentOrderAllocationDrug WDORD WITH (NOLOCK)
				left join v_WhsDocumentOrderAllocation WDOA (nolock) on WDOA.WhsDocumentOrderAllocation_id = WDORD.WhsDocumentOrderAllocation_id
				LEFT JOIN rls.v_Drug D WITH (NOLOCK) ON D.Drug_id = WDORD.Drug_id
				LEFT JOIN v_WhsDocumentSupply WDS WITH (NOLOCK) ON WDS.WhsDocumentUc_id = WDORD.WhsDocumentUc_pid
				inner join v_Org O (nolock) on O.Org_id = WDOA.Org_id
			where
				WDORD.WhsDocumentOrderAllocation_id IN (
					select
						WhsDocumentOrderAllocation_id
					from
						v_WhsDocumentOrderAllocation (nolock)
					where
						WhsDocumentUc_pid = @WhsDocumentOrderAllocation_id
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
			SELECT
				WDORD.WhsDocumentOrderAllocationDrug_id,
				WDORD.WhsDocumentOrderAllocation_id,
				WDORD.WhsDocumentUc_pid,
				WDS.WhsDocumentUc_Name,
				WDORD.Drug_id,
				WDORD.Okei_id,
				D.Drug_Name,
				O.Org_Name as Lpu_Name,
				LTRIM(STR(WDORD.WhsDocumentOrderAllocationDrug_Kolvo,10,0)) as WhsDocumentOrderAllocationDrug_Allocation,
				LTRIM(STR(WDORD.WhsDocumentOrderAllocationDrug_Price,10,2)) as WhsDocumentOrderAllocationDrug_PriceNDS,
				LTRIM(STR(WDORD.WhsDocumentOrderAllocationDrug_Price * WDORD.WhsDocumentOrderAllocationDrug_Kolvo,10,2)) as WhsDocumentUc_Sum
			FROM
				v_WhsDocumentOrderAllocationDrug WDORD WITH (NOLOCK)
				left join v_WhsDocumentOrderAllocation WDOA (nolock) on WDOA.WhsDocumentOrderAllocation_id = WDORD.WhsDocumentOrderAllocation_id
				LEFT JOIN rls.v_Drug D WITH (NOLOCK) ON D.Drug_id = WDORD.Drug_id
				LEFT JOIN v_WhsDocumentSupply WDS WITH (NOLOCK) ON WDS.WhsDocumentUc_id = WDORD.WhsDocumentUc_pid
				left join v_Org O (nolock) on O.Org_id = WDOA.Org_id
			WHERE
				WDORD.WhsDocumentOrderAllocation_id IN (select WhsDocumentOrderAllocation_id from v_WhsDocumentOrderAllocation (nolock) where WhsDocumentUc_pid = :WhsDocumentOrderAllocation_id)

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
			declare
				@WhsDocumentOrderAllocationDrug_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @WhsDocumentOrderAllocationDrug_id = :WhsDocumentOrderAllocationDrug_id;
			exec dbo." . $procedure . "
				@WhsDocumentOrderAllocationDrug_id = @WhsDocumentOrderAllocationDrug_id output,
				@WhsDocumentOrderAllocation_id = :WhsDocumentOrderAllocation_id,
				@WhsDocumentUc_pid = :WhsDocumentUc_pid,
				@Drug_id = :Drug_id,
				@Okei_id = :Okei_id,
				@WhsDocumentOrderAllocationDrug_Kolvo = :WhsDocumentOrderAllocationDrug_Kolvo,
				@WhsDocumentOrderAllocationDrug_Price = :WhsDocumentOrderAllocationDrug_Price,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @WhsDocumentOrderAllocationDrug_id as WhsDocumentOrderAllocationDrug_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec dbo.p_WhsDocumentOrderAllocationDrug_del
				@WhsDocumentOrderAllocationDrug_id = :WhsDocumentOrderAllocationDrug_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				SELECT
					D.Drug_id,
					D.Drug_Code,
					D.Drug_Name,
					DOR.Okei_id,
					DOR.DrugOstatRegistry_Kolvo,
					cast(round(isnull(WDSS.WhsDocumentSupplySpec_Price, 0), 2) as numeric(10, 2)) as WhsDocumentSupplySpec_Price,
					cast(round(isnull(WDSS.WhsDocumentSupplySpec_PriceNDS, 0), 2) as numeric(10, 2)) as WhsDocumentSupplySpec_PriceNDS
				FROM
					v_DrugShipment WITH (NOLOCK)
					inner join v_DrugOstatRegistry DOR WITH (NOLOCK) ON DOR.DrugShipment_id = v_DrugShipment.DrugShipment_id and DOR.SubAccountType_id = 1
					inner join rls.v_Drug D WITH (NOLOCK) ON D.Drug_id = DOR.Drug_id
					inner join v_WhsDocumentSupplySpec WDSS WITH (NOLOCK) ON /*WDSS.Drug_id = DOR.Drug_id and */WDSS.WhsDocumentSupply_id = v_DrugShipment.WhsDocumentSupply_id
				WHERE 
					v_DrugShipment.WhsDocumentSupply_id = :WhsDocumentSupply_id
			";

		} elseif ( $filter['WhsDocumentType_id'] == 13 ) { // Документ на исключение
			$q = "
				SELECT
					D.Drug_id,
					D.Drug_Code,
					D.Drug_Name,
					DOR.Okei_id,
					DOR.DrugOstatRegistry_Kolvo,
					cast(round(isnull(WDSS.WhsDocumentSupplySpec_Price, 0), 2) as numeric(10, 2)) as WhsDocumentSupplySpec_Price,
					cast(round(isnull(WDSS.WhsDocumentSupplySpec_PriceNDS, 0), 2) as numeric(10, 2)) as WhsDocumentSupplySpec_PriceNDS
				FROM
					v_DrugShipment WITH (NOLOCK)
					inner join v_DrugOstatRegistry DOR WITH (NOLOCK) ON DOR.DrugShipment_id = v_DrugShipment.DrugShipment_id and DOR.SubAccountType_id = 2
					inner join rls.v_Drug D WITH (NOLOCK) ON D.Drug_id = DOR.Drug_id
					inner join v_WhsDocumentSupplySpec WDSS WITH (NOLOCK) ON /*WDSS.Drug_id = DOR.Drug_id and */WDSS.WhsDocumentSupply_id = v_DrugShipment.WhsDocumentSupply_id
				WHERE 
					v_DrugShipment.WhsDocumentSupply_id = :WhsDocumentSupply_id
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
			$where .= " and l.Lpu_Name like '%'+:Lpu_Name+'%'";
			$filter['Lpu_Name'] = $filter['query'];
		}

		$query = "
			select
				l.Org_id,
				l.Lpu_Name
			from
				v_Lpu l with (nolock)
				inner join v_Org o with (nolock) on o.Org_id = l.Org_id
			where
				(1=1)
				$where
			order by
				l.Lpu_Name desc
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
            declare
				@MinzdravOrg_id bigint; --id организации минздрава

			set @MinzdravOrg_id = dbo.GetMinzdravDloOrgId();

			select
				dor.DrugOstatRegistry_id,
				wds.WhsDocumentUc_id as WhsDocumentUc_pid,
				wds.WhsDocumentUc_Num as WhsDocumentUc_Name,
				df.DrugFinance_Name as DrugFinance_Name,
				wdcit.WhsDocumentCostItemType_Name as WhsDocumentCostItemType_Name,
				isnull(sup.Org_Name, '') as Supplier_Name,
				o.Org_Name,
				dor.Drug_id,
				dor.Okei_id,
				am.RUSNAME as Actmatters_RusName,
                tn.NAME as Tradenames_Name,
				dform.NAME as DrugForm_Name,
				dose.Value as Drug_Dose,
				fas.Value as Drug_Fas,
				ceiling(dor.DrugOstatRegistry_Kolvo) as Kolvo,
				str(isnull(dor.DrugOstatRegistry_Sum/dor.DrugOstatRegistry_Kolvo, 0), 10, 2) as WhsDocumentOrderAllocationDrug_Price,
				rc.REGNUM as Reg_Num,
				rceff.FULLNAME as Reg_Firm,
				rceffc.NAME as Reg_Country,
				(convert(varchar, rc.REGDATE, 104)+isnull(' - '+convert(varchar, rc.ENDDATE, 104), '')) as Reg_Period,
				convert(varchar, rc.Reregdate, 104) as Reg_ReRegDate
			from
				v_DrugOstatRegistry dor with (nolock)
				left join v_SubAccountType sat with(nolock) on sat.SubAccountType_id = dor.SubAccountType_id
				left join rls.v_Drug d with (nolock) on d.Drug_id = dor.Drug_id
				left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dor.DrugShipment_id
				left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
				left join v_DrugFinance df with (nolock) on df.DrugFinance_id = wds.DrugFinance_id
				left join v_WhsDocumentCostItemType wdcit with (nolock) on wdcit.WhsDocumentCostItemType_id = wds.WhsDocumentCostItemType_id
				left join v_Org sup with (nolock) on sup.Org_id = wds.Org_sid
				left join v_Org o with (nolock) on o.Org_id = dor.Org_id
				left join rls.v_prep p with(nolock) on p.Prep_id = d.DrugPrep_id
                left join rls.v_DrugComplexMnn dcm with(nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                left join rls.v_DrugComplexMnnName dcmn with(nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                left join rls.v_ACTMATTERS am with(nolock) on am.ACTMATTERS_ID = dcmn.Actmatters_id
				left join rls.v_TRADENAMES tn with(nolock) on tn.TRADENAMES_ID = p.TRADENAMEID
				left join rls.v_CLSDRUGFORMS dform with(nolock) on dform.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
				left join rls.REGCERT rc with (nolock) on rc.REGCERT_ID = p.REGCERTID
				left join rls.REGCERT_EXTRAFIRMS rcef with (nolock) on rcef.CERTID = rc.REGCERT_ID
				left join rls.v_FIRMS rceff with(nolock) on rceff.FIRMS_ID = rcef.FIRMID
                left join rls.v_COUNTRIES rceffc with(nolock) on rceffc.COUNTRIES_ID = rceff.COUNTID
				left join rls.MASSUNITS dfmu with (nolock) on dfmu.MASSUNITS_ID = p.DFMASSID
				left join rls.CONCENUNITS dfcu with (nolock) on dfcu.CONCENUNITS_ID = p.DFCONCID
				left join rls.ACTUNITS dfau with (nolock) on dfau.ACTUNITS_ID = p.DFACTID
				left join rls.SIZEUNITS dfsu with (nolock) on dfsu.SIZEUNITS_ID = p.DFSIZEID
				left join rls.v_Nomen n with (nolock) on n.NOMEN_ID = dor.Drug_id
				left join rls.MASSUNITS mu with (nolock) on mu.MASSUNITS_ID = n.PPACKMASSUNID
				left join rls.CUBICUNITS cu with (nolock) on cu.CUBICUNITS_ID = n.PPACKCUBUNID
				outer apply (
					select coalesce(
						cast(cast(p.DFMASS as float) as varchar)+' '+dfmu.SHORTNAME,
						cast(cast(p.DFCONC as float) as varchar)+' '+dfcu.SHORTNAME,
						cast(p.DFACT as varchar)+' '+dfau.SHORTNAME,
						cast(p.DFSIZE as varchar)+' '+dfsu.SHORTNAME
					) as Value
				) dose
				outer apply(
					select (
						(case when d.Drug_Fas is not null then cast(d.Drug_Fas as varchar)+' доз' else '' end)+
						(case when d.Drug_Fas is not null and coalesce(d.Drug_Volume,d.Drug_Mass) is not null then ', ' else '' end)+
						(case when coalesce(d.Drug_Volume,d.Drug_Mass) is not null then coalesce(d.Drug_Volume,d.Drug_Mass) else '' end)
					) as Value
				) fas
			where
				dor.Org_id = @MinzdravOrg_id and
				dor.DrugOstatRegistry_Kolvo > 0 and
				sat.SubAccountType_Code = 1 and
				wds.WhsDocumentUc_pid is not null
				$where;
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