<?php defined('BASEPATH') or die ('No direct script access allowed');

class WhsDocumentOrderReserveDrug_model extends swModel {

	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
	}

	/**
	 * Загрузка первоначального списка медикаментов
	 */
	function loadRAWList($filter) {
        $where = "";

        if (!empty($filter['WhsDocumentSupply_id'])) {
            $where .= " and wds.WhsDocumentSupply_id = :WhsDocumentSupply_id";
        } else if (!empty($filter['DrugRequest_id'])) {
            if (!empty($filter['DrugRequest_id'])) {
                $where .= " and (
                    wds.WhsDocumentSupply_id in (
                        select
                            i_wdss.WhsDocumentSupply_id
                        from
                            v_DrugRequestPurchaseSpec i_drps with (nolock)
                            left join v_DrugRequestExec i_dre with (nolock) on i_dre.DrugRequestPurchaseSpec_id = i_drps.DrugRequestPurchaseSpec_id
                            inner join v_WhsDocumentSupplySpec i_wdss with (nolock) on i_wdss.WhsDocumentSupplySpec_id = i_dre.WhsDocumentSupplySpec_id
                        where
                            i_drps.DrugRequest_id = :DrugRequest_id
                    ) or
                    wds.WhsDocumentSupply_id in (
                        select
                            i_wds.WhsDocumentSupply_id
                        from
                            v_DrugRequestPurchaseSpec i_drps with (nolock)
                            left join v_WhsDocumentProcurementRequestSpec i_wdprs with (nolock) on i_wdprs.DrugRequestPurchaseSpec_id = i_drps.DrugRequestPurchaseSpec_id
                            left join v_WhsDocumentProcurementRequest i_wdpr with (nolock) on i_wdpr.WhsDocumentProcurementRequest_id = i_wdprs.WhsDocumentProcurementRequest_id
                            inner join v_WhsDocumentSupply i_wds with (nolock) on i_wds.WhsDocumentUc_pid = i_wdpr.WhsDocumentUc_id
                        where
                            i_drps.DrugRequest_id = :DrugRequest_id
                    )
                )";
            }
        }

        $filter['SubAccountType_Code'] = null;
        if ( $filter['WhsDocumentType_id'] == 12 ) { // Документ на включение
            $filter['SubAccountType_Code'] = 1; // 1 - Доступно
        }
        if ( $filter['WhsDocumentType_id'] == 13 ) { //Документ на исключение
            $filter['SubAccountType_Code'] = 2; // 2 - Зарезервировано
        }

		// Определяем организацию минздрава
		$query = "
			select
                wds.WhsDocumentSupply_id as WhsDocumentUc_pid,
                wds.WhsDocumentUc_Num,
                wds.WhsDocumentUc_Name,
                fin_year.yr as WhsDocumentSupply_Year,
                dor.Drug_id,
                dor.Okei_id,
                d.Drug_Name,
                dcm.DrugComplexMnn_RusName,
                tn.NAME as Tradenames_Name,
                dform.NAME as DrugForm_Name,
                dose.Value as Drug_Dose,
                fas.Value as Drug_Fas,
                round(dor.DrugOstatRegistry_Kolvo * :WhsDocumentOrderReserve_Percent / 100, 0, 1) as WhsDocumentOrderReserveDrug_Kolvo,
                cast(isnull(dor.DrugOstatRegistry_Cost, 0) as numeric(10, 2)) as WhsDocumentOrderReserveDrug_PriceNDS,
                rc.REGNUM as Reg_Num,
                rceff.FULLNAME as Reg_Firm,
                rceffc.NAME as Reg_Country,
                (convert(varchar, rc.REGDATE, 104)+isnull(' - '+convert(varchar, rc.ENDDATE, 104), '')) as Reg_Period,
                convert(varchar, rc.Reregdate, 104) as Reg_ReRegDate,
                'add' as [state]
            from
                v_DrugShipment with (nolock)
                inner join v_DrugOstatRegistry dor with (nolock) on dor.DrugShipment_id = v_DrugShipment.DrugShipment_id
                left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
                inner join rls.v_Drug d with (nolock) on d.Drug_id = dor.Drug_id
                left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentSupply_id = v_DrugShipment.WhsDocumentSupply_id
                left join rls.v_prep p with(nolock) on p.Prep_id = d.DrugPrep_id
                left join rls.REGCERT rc with (nolock) on rc.REGCERT_ID = p.REGCERTID
                left join rls.REGCERT_EXTRAFIRMS rcef with (nolock) on rcef.CERTID = rc.REGCERT_ID
                left join rls.v_FIRMS rceff with(nolock) on rceff.FIRMS_ID = rcef.FIRMID
                left join rls.v_COUNTRIES rceffc with(nolock) on rceffc.COUNTRIES_ID = rceff.COUNTID
                left join rls.v_TRADENAMES tn with(nolock) on tn.TRADENAMES_ID = p.TRADENAMEID
                left join rls.v_CLSDRUGFORMS dform with(nolock) on dform.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
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
                outer apply (
                    select
                        datepart(year, isnull(max(i_wdd.WhsDocumentDelivery_setDT), wds.WhsDocumentUc_Date)) as yr
                    from
                        v_WhsDocumentDelivery i_wdd with (nolock)
                    where
                        i_wdd.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
                ) fin_year
            where
                dor.Org_id = :Org_id and
                dor.DrugOstatRegistry_Kolvo > 0 and
                dor.DrugFinance_id = :DrugFinance_id and
                dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id and
                sat.SubAccountType_Code = :SubAccountType_Code
                {$where}
		";
		$result = $this->db->query($query, $filter);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка медикаментов
	 */
	function loadList($filter) {
		$query = "
			select
				wdord.WhsDocumentOrderReserveDrug_id,
				wdord.WhsDocumentUc_pid,
				wds.WhsDocumentUc_Num,
				wds.WhsDocumentUc_Name,
				fin_year.yr as WhsDocumentSupply_Year,
				wdord.Drug_id,
				wdord.Okei_id,
				d.Drug_Name,
				dcm.DrugComplexMnn_RusName,
				tn.NAME as Tradenames_Name,
				dform.NAME as DrugForm_Name,
				dose.Value as Drug_Dose,
				fas.Value as Drug_Fas,
				wdord.WhsDocumentOrderReserveDrug_Kolvo,
				wdord.WhsDocumentOrderReserveDrug_Price as WhsDocumentOrderReserveDrug_PriceNDS,
				wdord.WhsDocumentOrderReserveDrug_Price * WDORD.WhsDocumentOrderReserveDrug_Kolvo as WhsDocumentUc_Sum,
				rc.REGNUM as Reg_Num,
				rceff.FULLNAME as Reg_Firm,
				rceffc.NAME as Reg_Country,
				(convert(varchar, rc.REGDATE, 104)+isnull(' - '+convert(varchar, rc.ENDDATE, 104), '')) as Reg_Period,
				convert(varchar, rc.Reregdate, 104) as Reg_ReRegDate
			from
				v_WhsDocumentOrderReserveDrug wdord with (nolock)
				left join rls.v_Drug d with (nolock) on d.Drug_id = wdord.Drug_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = D.DrugComplexMnn_id
				left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentUc_id = wdord.WhsDocumentUc_pid
                left join rls.v_prep p with(nolock) on p.Prep_id = d.DrugPrep_id
                left join rls.REGCERT rc with (nolock) on rc.REGCERT_ID = p.REGCERTID
                left join rls.REGCERT_EXTRAFIRMS rcef with (nolock) on rcef.CERTID = rc.REGCERT_ID
                left join rls.v_FIRMS rceff with(nolock) on rceff.FIRMS_ID = rcef.FIRMID
                left join rls.v_COUNTRIES rceffc with(nolock) on rceffc.COUNTRIES_ID = rceff.COUNTID
                left join rls.v_TRADENAMES tn with(nolock) on tn.TRADENAMES_ID = p.TRADENAMEID
				left join rls.v_CLSDRUGFORMS dform with(nolock) on dform.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
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
				outer apply (
					select
						datepart(year, isnull(max(i_wdd.WhsDocumentDelivery_setDT), wds.WhsDocumentUc_Date)) as yr
					from
						v_WhsDocumentDelivery i_wdd with (nolock)
					where
						i_wdd.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
				) fin_year
			where
				wdord.WhsDocumentOrderReserve_id = :WhsDocumentOrderReserve_id
		";
		$result = $this->db->query($query, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение из сериализованного массива
	 */
	function saveFromJSON($data) {
		if (!empty($data['json_str']) && $data['WhsDocumentOrderReserve_id'] > 0) {
			ConvertFromWin1251ToUTF8($data['json_str']);
			$dt = json_decode($data['json_str'], true);
			foreach($dt as $record) {
				$record['WhsDocumentOrderReserve_id'] = $data['WhsDocumentOrderReserve_id'];
				$record['pmUser_id'] = $data['pmUser_id'];
				switch($record['state']) {
					case 'add':
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
		$procedure = 'p_WhsDocumentOrderReserveDrug_ins';
		if ( $data['state'] != 'add' ) {
			$procedure = 'p_WhsDocumentOrderReserveDrug_upd';
		} else {
			$data['WhsDocumentOrderReserveDrug_id'] = 0;
		}
		$q = "
			declare
				@WhsDocumentOrderReserveDrug_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @WhsDocumentOrderReserveDrug_id = :WhsDocumentOrderReserveDrug_id;
			exec dbo." . $procedure . "
				@WhsDocumentOrderReserveDrug_id = @WhsDocumentOrderReserveDrug_id output,
				@WhsDocumentOrderReserve_id = :WhsDocumentOrderReserve_id,
				@WhsDocumentUc_pid = :WhsDocumentUc_pid,
				@Drug_id = :Drug_id,
				@Okei_id = :Okei_id,
				@WhsDocumentOrderReserveDrug_Kolvo = :WhsDocumentOrderReserveDrug_Kolvo,
				@WhsDocumentOrderReserveDrug_Price = :WhsDocumentOrderReserveDrug_Price,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @WhsDocumentOrderReserveDrug_id as WhsDocumentOrderReserveDrug_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'WhsDocumentOrderReserveDrug_id' => $data['WhsDocumentOrderReserveDrug_id'],
			'WhsDocumentOrderReserve_id' => $data['WhsDocumentOrderReserve_id'],
			'WhsDocumentUc_pid' => $data['WhsDocumentUc_pid'],
			'Drug_id' => $data['Drug_id'],
			'Okei_id' => $data['Okei_id'],
			'WhsDocumentOrderReserveDrug_Kolvo' => $data['WhsDocumentOrderReserveDrug_Kolvo'],
			'WhsDocumentOrderReserveDrug_Price' => $data['WhsDocumentOrderReserveDrug_PriceNDS'],
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
	 * Удаление
	 */
	function delete($data) {
		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec dbo.p_WhsDocumentOrderReserveDrug_del
				@WhsDocumentOrderReserveDrug_id = :WhsDocumentOrderReserveDrug_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'WhsDocumentOrderReserveDrug_id' => $data['WhsDocumentOrderReserveDrug_id']
		));
		if ( is_object($r) ) {
			return $r->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadWhsDocumentOrderReserveDrugList($filter) {
		// Документ на включение 
		if ( $filter['WhsDocumentType_id'] == 12 ) {
            $query = "
				select
					d.Drug_id,
					d.Drug_Code,
					d.Drug_Name,
					dor.Okei_id,
					dor.DrugOstatRegistry_Kolvo,
					dcm.DrugComplexMnn_RusName,
                    tn.NAME as Tradenames_Name,
                    dform.NAME as DrugForm_Name,
                    dose.Value as Drug_Dose,
                    fas.Value as Drug_Fas,
					cast(round(isnull(wdss.WhsDocumentSupplySpec_Price, 0), 2) as numeric(10, 2)) as WhsDocumentSupplySpec_Price,
					cast(round(isnull(wdss.WhsDocumentSupplySpec_PriceNDS, 0), 2) as numeric(10, 2)) as WhsDocumentSupplySpec_PriceNDS,
                    rc.REGNUM as Reg_Num,
                    rceff.FULLNAME as Reg_Firm,
                    rceffc.NAME as Reg_Country,
                    (convert(varchar, rc.REGDATE, 104)+isnull(' - '+convert(varchar, rc.ENDDATE, 104), '')) as Reg_Period,
                    convert(varchar, rc.Reregdate, 104) as Reg_ReRegDate
				from
					v_DrugShipment ds with (nolock)
					inner join v_DrugOstatRegistry dor with (nolock) on dor.DrugShipment_id = ds.DrugShipment_id and dor.SubAccountType_id = 1
					inner join rls.v_Drug d with (nolock) on d.Drug_id = dor.Drug_id
					left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
					inner join v_WhsDocumentSupplySpec wdss with (nolock) on /*wdss.Drug_id = dor.Drug_id and */wdss.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
                    left join rls.v_prep p with(nolock) on p.Prep_id = d.DrugPrep_id
                    left join rls.REGCERT rc with (nolock) on rc.REGCERT_ID = p.REGCERTID
                    left join rls.REGCERT_EXTRAFIRMS rcef with (nolock) on rcef.CERTID = rc.REGCERT_ID
                    left join rls.v_FIRMS rceff with(nolock) on rceff.FIRMS_ID = rcef.FIRMID
                    left join rls.v_COUNTRIES rceffc with(nolock) on rceffc.COUNTRIES_ID = rceff.COUNTID
                    left join rls.v_TRADENAMES tn with(nolock) on tn.TRADENAMES_ID = p.TRADENAMEID
                    left join rls.v_CLSDRUGFORMS dform with(nolock) on dform.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
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
					ds.WhsDocumentSupply_id = :WhsDocumentSupply_id
			";
		} elseif ( $filter['WhsDocumentType_id'] == 13 ) { //Документ на исключение
            $query = "
				select
					d.Drug_id,
					d.Drug_Code,
					d.Drug_Name,
					dor.Okei_id,
					dor.DrugOstatRegistry_Kolvo,
					dcm.DrugComplexMnn_RusName,
                    tn.NAME as Tradenames_Name,
                    dform.NAME as DrugForm_Name,
                    dose.Value as Drug_Dose,
                    fas.Value as Drug_Fas,
					cast(round(isnull(wdss.WhsDocumentSupplySpec_Price, 0), 2) as numeric(10, 2)) as WhsDocumentSupplySpec_Price,
					cast(round(isnull(wdss.WhsDocumentSupplySpec_PriceNDS, 0), 2) as numeric(10, 2)) as WhsDocumentSupplySpec_PriceNDS,
                    rc.REGNUM as Reg_Num,
                    rceff.FULLNAME as Reg_Firm,
                    rceffc.NAME as Reg_Country,
                    (convert(varchar, rc.REGDATE, 104)+isnull(' - '+convert(varchar, rc.ENDDATE, 104), '')) as Reg_Period,
                    convert(varchar, rc.Reregdate, 104) as Reg_ReRegDate
				from
					v_DrugShipment ds with (nolock)
					inner join v_DrugOstatRegistry dor with (nolock) on dor.DrugShipment_id = ds.DrugShipment_id and dor.SubAccountType_id = 2
					inner join rls.v_Drug d with (nolock) on d.Drug_id = dor.Drug_id
					left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
					inner join v_WhsDocumentSupplySpec wdss with (nolock) on /*wdss.Drug_id = dor.Drug_id and */wdss.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
                    left join rls.v_prep p with(nolock) on p.Prep_id = d.DrugPrep_id
                    left join rls.REGCERT rc with (nolock) on rc.REGCERT_ID = p.REGCERTID
                    left join rls.REGCERT_EXTRAFIRMS rcef with (nolock) on rcef.CERTID = rc.REGCERT_ID
                    left join rls.v_FIRMS rceff with(nolock) on rceff.FIRMS_ID = rcef.FIRMID
                    left join rls.v_COUNTRIES rceffc with(nolock) on rceffc.COUNTRIES_ID = rceff.COUNTID
                    left join rls.v_TRADENAMES tn with(nolock) on tn.TRADENAMES_ID = p.TRADENAMEID
                    left join rls.v_CLSDRUGFORMS dform with(nolock) on dform.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
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
					ds.WhsDocumentSupply_id = :WhsDocumentSupply_id
			";
		}
	
		$result = $this->db->query($query, $filter);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	
}