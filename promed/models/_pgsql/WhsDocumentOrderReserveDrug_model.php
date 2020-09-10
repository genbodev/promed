<?php
defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Class WhsDocumentOrderReserveDrug_model
 */
class WhsDocumentOrderReserveDrug_model extends SwPgModel
{
    protected $dateTimeForm104 = "'dd.mm.yyyy'";
    /**
     * Загрузка первоначального списка медикаментов
     * @param $filter
     * @return bool|array
     */
	public function loadRAWList($filter)
    {
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
                            v_DrugRequestPurchaseSpec i_drps
                            left join v_DrugRequestExec i_dre on i_dre.DrugRequestPurchaseSpec_id = i_drps.DrugRequestPurchaseSpec_id
                            inner join v_WhsDocumentSupplySpec i_wdss on i_wdss.WhsDocumentSupplySpec_id = i_dre.WhsDocumentSupplySpec_id
                        where
                            i_drps.DrugRequest_id = :DrugRequest_id
                    ) or
                    wds.WhsDocumentSupply_id in (
                        select
                            i_wds.WhsDocumentSupply_id
                        from
                            v_DrugRequestPurchaseSpec i_drps
                            left join v_WhsDocumentProcurementRequestSpec i_wdprs on i_wdprs.DrugRequestPurchaseSpec_id = i_drps.DrugRequestPurchaseSpec_id
                            left join v_WhsDocumentProcurementRequest i_wdpr on i_wdpr.WhsDocumentProcurementRequest_id = i_wdprs.WhsDocumentProcurementRequest_id
                            inner join v_WhsDocumentSupply i_wds on i_wds.WhsDocumentUc_pid = i_wdpr.WhsDocumentUc_id
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
                wds.WhsDocumentSupply_id as \"WhsDocumentUc_pid\",
                wds.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
                wds.WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
                fin_year.yr as \"WhsDocumentSupply_Year\",
                dor.Drug_id as \"Drug_id\",
                dor.Okei_id as \"Okei_id\",
                d.Drug_Name as \"Drug_Name\",
                dcm.DrugComplexMnn_RusName as \"DrugComplexMnn_RusName\",
                tn.NAME as \"Tradenames_Name\",
                dform.NAME as \"DrugForm_Name\",
                dose.Value as \"Drug_Dose\",
                fas.Value as \"Drug_Fas\",
                round(dor.DrugOstatRegistry_Kolvo * :WhsDocumentOrderReserve_Percent / 100) as \"WhsDocumentOrderReserveDrug_Kolvo\",
                cast(coalesce(dor.DrugOstatRegistry_Cost, 0) as numeric(10, 2)) as \"WhsDocumentOrderReserveDrug_PriceNDS\",
                rc.REGNUM as \"Reg_Num\",
                rceff.FULLNAME as \"Reg_Firm\",
                rceffc.NAME as \"Reg_Country\",
                (to_char(rc.REGDATE, {$this->dateTimeForm104}) || coalesce(' - ' || to_char(rc.ENDDATE, {$this->dateTimeForm104}), '')) as \"Reg_Period\",
                to_char(rc.Reregdate, {$this->dateTimeForm104}) as \"Reg_ReRegDate\",
                'add' as \"state\"
            from
                v_DrugShipment
                inner join v_DrugOstatRegistry dor on dor.DrugShipment_id = v_DrugShipment.DrugShipment_id
                left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
                inner join rls.v_Drug d on d.Drug_id = dor.Drug_id
                left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                left join v_WhsDocumentSupply wds on wds.WhsDocumentSupply_id = v_DrugShipment.WhsDocumentSupply_id
                left join rls.v_prep p on p.Prep_id = d.DrugPrep_id
                left join rls.REGCERT rc on rc.REGCERT_ID = p.REGCERTID
                left join rls.REGCERT_EXTRAFIRMS rcef on rcef.CERTID = rc.REGCERT_ID
                left join rls.v_FIRMS rceff on rceff.FIRMS_ID = rcef.FIRMID
                left join rls.v_COUNTRIES rceffc on rceffc.COUNTRIES_ID = rceff.COUNTID
                left join rls.v_TRADENAMES tn on tn.TRADENAMES_ID = p.TRADENAMEID
                left join rls.v_CLSDRUGFORMS dform on dform.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
                left join rls.MASSUNITS dfmu on dfmu.MASSUNITS_ID = p.DFMASSID
                left join rls.CONCENUNITS dfcu on dfcu.CONCENUNITS_ID = p.DFCONCID
                left join rls.ACTUNITS dfau on dfau.ACTUNITS_ID = p.DFACTID
                left join rls.SIZEUNITS dfsu on dfsu.SIZEUNITS_ID = p.DFSIZEID
                left join rls.v_Nomen n on n.NOMEN_ID = d.Drug_id
                left join rls.MASSUNITS mu on mu.MASSUNITS_ID = n.PPACKMASSUNID
                left join rls.CUBICUNITS cu on cu.CUBICUNITS_ID = n.PPACKCUBUNID
                left join lateral (
                    select coalesce(
                        cast(cast(p.DFMASS as float) as varchar) || ' ' || dfmu.SHORTNAME,
                        cast(cast(p.DFCONC as float) as varchar) || ' ' || dfcu.SHORTNAME,
                        cast(p.DFACT as varchar) || ' ' || dfau.SHORTNAME,
                        cast(p.DFSIZE as varchar) || ' ' ||dfsu.SHORTNAME
                    ) as Value
                ) dose on true
                left join lateral (
                    select (
                        (case when d.Drug_Fas is not null then cast(d.Drug_Fas as varchar) || ' доз' else '' end) ||
                        (case when d.Drug_Fas is not null and coalesce(d.Drug_Volume,d.Drug_Mass) is not null then ', ' else '' end) ||
                        (case when coalesce(d.Drug_Volume, d.Drug_Mass) is not null then coalesce(d.Drug_Volume, d.Drug_Mass) else '' end)
                    ) as Value
                ) fas on true
                left join lateral (
                    select
                        date_part('year', coalesce(max(i_wdd.WhsDocumentDelivery_setDT), wds.WhsDocumentUc_Date)) as yr
                    from
                        v_WhsDocumentDelivery i_wdd
                    where
                        i_wdd.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
                ) fin_year on true
            where
                dor.Org_id = :Org_id
            and
                dor.DrugOstatRegistry_Kolvo > 0
            and
                dor.DrugFinance_id = :DrugFinance_id
            and
                dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id
            and
                sat.SubAccountType_Code = :SubAccountType_Code
                {$where}
		";
		$result = $this->db->query($query, $filter);
		if (!is_object($result)) {
			return false;
		}
        return $result->result('array');
	}

    /**
     * Загрузка списка медикаментов
     * @param $filter
     * @return bool|array
     */
	public function loadList($filter)
    {
		$query = "
			select
				wdord.WhsDocumentOrderReserveDrug_id as \"WhsDocumentOrderReserveDrug_id\",
				wdord.WhsDocumentUc_pid as \"WhsDocumentUc_pid\",
				wds.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				wds.WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
				fin_year.yr as \"WhsDocumentSupply_Year\",
				wdord.Drug_id as \"Drug_id\",
				wdord.Okei_id as \"Okei_id\",
				d.Drug_Name as \"Drug_Name\",
				dcm.DrugComplexMnn_RusName as \"DrugComplexMnn_RusName\",
				tn.NAME as \"Tradenames_Name\",
				dform.NAME as \"DrugForm_Name\",
				dose.Value as \"Drug_Dose\",
				fas.Value as \"Drug_Fas\",
				wdord.WhsDocumentOrderReserveDrug_Kolvo as \"WhsDocumentOrderReserveDrug_Kolvo\",
				wdord.WhsDocumentOrderReserveDrug_Price as \"WhsDocumentOrderReserveDrug_PriceNDS\",
				wdord.WhsDocumentOrderReserveDrug_Price * WDORD.WhsDocumentOrderReserveDrug_Kolvo as \"WhsDocumentUc_Sum\",
				rc.REGNUM as \"Reg_Num\",
				rceff.FULLNAME as \"Reg_Firm\",
				rceffc.NAME as \"Reg_Country\",
				(to_char(rc.REGDATE, {$this->dateTimeForm104}) || coalesce (' - ' || to_char(rc.ENDDATE, {$this->dateTimeForm104}), '')) as \"Reg_Period\",
				to_char(rc.Reregdate, {$this->dateTimeForm104}) as \"Reg_ReRegDate\"
			from
				v_WhsDocumentOrderReserveDrug wdord
				left join rls.v_Drug d on d.Drug_id = wdord.Drug_id
				left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = D.DrugComplexMnn_id
				left join v_WhsDocumentSupply wds on wds.WhsDocumentUc_id = wdord.WhsDocumentUc_pid
                left join rls.v_prep p  on p.Prep_id = d.DrugPrep_id
                left join rls.REGCERT rc on rc.REGCERT_ID = p.REGCERTID
                left join rls.REGCERT_EXTRAFIRMS rcef on rcef.CERTID = rc.REGCERT_ID
                left join rls.v_FIRMS rceff  on rceff.FIRMS_ID = rcef.FIRMID
                left join rls.v_COUNTRIES rceffc  on rceffc.COUNTRIES_ID = rceff.COUNTID
                left join rls.v_TRADENAMES tn  on tn.TRADENAMES_ID = p.TRADENAMEID
				left join rls.v_CLSDRUGFORMS dform  on dform.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
				left join rls.MASSUNITS dfmu on dfmu.MASSUNITS_ID = p.DFMASSID
				left join rls.CONCENUNITS dfcu on dfcu.CONCENUNITS_ID = p.DFCONCID
				left join rls.ACTUNITS dfau on dfau.ACTUNITS_ID = p.DFACTID
				left join rls.SIZEUNITS dfsu on dfsu.SIZEUNITS_ID = p.DFSIZEID
				left join rls.v_Nomen n on n.NOMEN_ID = d.Drug_id
				left join rls.MASSUNITS mu on mu.MASSUNITS_ID = n.PPACKMASSUNID
				left join rls.CUBICUNITS cu on cu.CUBICUNITS_ID = n.PPACKCUBUNID
				left join lateral (
					select coalesce(
						cast(cast(p.DFMASS as float) as varchar) || ' ' || dfmu.SHORTNAME,
						cast(cast(p.DFCONC as float) as varchar) || ' ' ||dfcu.SHORTNAME,
						cast(p.DFACT as varchar) || ' ' || dfau.SHORTNAME,
						cast(p.DFSIZE as varchar) || ' ' || dfsu.SHORTNAME
					) as Value
				) dose on true
				left join lateral (
					select (
						(case when d.Drug_Fas is not null then cast(d.Drug_Fas as varchar) || ' доз' else '' end) ||
						(case when d.Drug_Fas is not null and coalesce(d.Drug_Volume,d.Drug_Mass) is not null then ', ' else '' end) ||
						(case when coalesce(d.Drug_Volume,d.Drug_Mass) is not null then coalesce(d.Drug_Volume,d.Drug_Mass) else '' end)
					) as Value
				) fas on true
				left join lateral (
					select
						date_part('year', coalesce(max(i_wdd.WhsDocumentDelivery_setDT), wds.WhsDocumentUc_Date)) as yr
					from
						v_WhsDocumentDelivery i_wdd
					where
						i_wdd.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
				) fin_year on true
			where
				wdord.WhsDocumentOrderReserve_id = :WhsDocumentOrderReserve_id
		";
		$result = $this->db->query($query, $filter);
		if (! is_object($result) ) {
			return false;
		}
        return $result->result('array');
	}

    /**
     * Сохранение из сериализованного массива
     * @param $data
     * @throws Exception
     */
	public function saveFromJSON($data)
    {
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
     * @param $data
     * @return array
     * @throws Exception
     */
	public function save($data)
    {
		$procedure = 'p_WhsDocumentOrderReserveDrug_ins';
		if ( $data['state'] != 'add' ) {
			$procedure = 'p_WhsDocumentOrderReserveDrug_upd';
		} else {
			$data['WhsDocumentOrderReserveDrug_id'] = 0;
		}

		$q = "
			select
			    WhsDocumentOrderReserveDrug_id as \"WhsDocumentOrderReserveDrug_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from dbo." . $procedure . "
			(
				WhsDocumentOrderReserveDrug_id := :WhsDocumentOrderReserveDrug_id,
				WhsDocumentOrderReserve_id := :WhsDocumentOrderReserve_id,
				WhsDocumentUc_pid := :WhsDocumentUc_pid,
				Drug_id := :Drug_id,
				Okei_id := :Okei_id,
				WhsDocumentOrderReserveDrug_Kolvo := :WhsDocumentOrderReserveDrug_Kolvo,
				WhsDocumentOrderReserveDrug_Price := :WhsDocumentOrderReserveDrug_Price,
				pmUser_id := :pmUser_id
			)
		";
		$p = [
			'WhsDocumentOrderReserveDrug_id' => $data['WhsDocumentOrderReserveDrug_id'],
			'WhsDocumentOrderReserve_id' => $data['WhsDocumentOrderReserve_id'],
			'WhsDocumentUc_pid' => $data['WhsDocumentUc_pid'],
			'Drug_id' => $data['Drug_id'],
			'Okei_id' => $data['Okei_id'],
			'WhsDocumentOrderReserveDrug_Kolvo' => $data['WhsDocumentOrderReserveDrug_Kolvo'],
			'WhsDocumentOrderReserveDrug_Price' => $data['WhsDocumentOrderReserveDrug_PriceNDS'],
			'pmUser_id' => $data['pmUser_id']
		];
		//die(getDebugSQL($q, $p));
		$r = $this->db->query($q, $p);
		
		if (! is_object($r) ) {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
		    throw new Exception('Ошибка при выполнении запроса к базе данных');
		}

        return $r->result('array');
	}

    /**
     * Удаление
     * @param $data
     * @return bool|array
     */
	public function delete($data) {
		$q = "
		    select
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
			from dbo.p_WhsDocumentOrderReserveDrug_del
			(
				WhsDocumentOrderReserveDrug_id := :WhsDocumentOrderReserveDrug_id
			)
		";
		$r = $this->db->query($q, [
			'WhsDocumentOrderReserveDrug_id' => $data['WhsDocumentOrderReserveDrug_id']
		]);
		if (! is_object($r) )
			return false;

        return $r->result('array');
    }

    /**
     * Загрузка списка
     * @param $filter
     * @return bool
     */
	public function loadWhsDocumentOrderReserveDrugList($filter) {
		// Документ на включение 
		if ( $filter['WhsDocumentType_id'] == 12 ) {
            $query = "
				select
					d.Drug_id as \"Drug_id\",
					d.Drug_Code as \"Drug_Code\",
					d.Drug_Name as \"Drug_Name\",
					dor.Okei_id as \"Okei_id\",
					dor.DrugOstatRegistry_Kolvo as \"DrugOstatRegistry_Kolvo\",
					dcm.DrugComplexMnn_RusName as \"DrugComplexMnn_RusName\",
                    tn.NAME as \"Tradenames_Name\",
                    dform.NAME as \"DrugForm_Name\",
                    dose.Value as \"Drug_Dose\",
                    fas.Value as \"Drug_Fas\",
					cast(round(coalesce(wdss.WhsDocumentSupplySpec_Price, 0), 2) as numeric(10, 2)) as \"WhsDocumentSupplySpec_Price\",
					cast(round(coalesce(wdss.WhsDocumentSupplySpec_PriceNDS, 0), 2) as numeric(10, 2)) as \"WhsDocumentSupplySpec_PriceNDS\",
                    rc.REGNUM as \"Reg_Num\",
                    rceff.FULLNAME as \"Reg_Firm\",
                    rceffc.NAME as \"Reg_Country\",
                    (to_char(rc.REGDATE, {$this->dateTimeForm104}) || coalesce(' - ' || to_char(rc.ENDDATE, {$this->dateTimeForm104}), '')) as \"Reg_Period\",
                    to_char(rc.Reregdate, {$this->dateTimeForm104}) as \"Reg_ReRegDate\"
				from
					v_DrugShipment ds
					inner join v_DrugOstatRegistry dor on dor.DrugShipment_id = ds.DrugShipment_id and dor.SubAccountType_id = 1
					inner join rls.v_Drug d on d.Drug_id = dor.Drug_id
					left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
					inner join v_WhsDocumentSupplySpec wdss on /*wdss.Drug_id = dor.Drug_id and */wdss.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
                    left join rls.v_prep p  on p.Prep_id = d.DrugPrep_id
                    left join rls.REGCERT rc on rc.REGCERT_ID = p.REGCERTID
                    left join rls.REGCERT_EXTRAFIRMS rcef on rcef.CERTID = rc.REGCERT_ID
                    left join rls.v_FIRMS rceff  on rceff.FIRMS_ID = rcef.FIRMID
                    left join rls.v_COUNTRIES rceffc  on rceffc.COUNTRIES_ID = rceff.COUNTID
                    left join rls.v_TRADENAMES tn  on tn.TRADENAMES_ID = p.TRADENAMEID
                    left join rls.v_CLSDRUGFORMS dform  on dform.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
                    left join rls.MASSUNITS dfmu on dfmu.MASSUNITS_ID = p.DFMASSID
                    left join rls.CONCENUNITS dfcu on dfcu.CONCENUNITS_ID = p.DFCONCID
                    left join rls.ACTUNITS dfau on dfau.ACTUNITS_ID = p.DFACTID
                    left join rls.SIZEUNITS dfsu on dfsu.SIZEUNITS_ID = p.DFSIZEID
                    left join rls.v_Nomen n on n.NOMEN_ID = d.Drug_id
                    left join rls.MASSUNITS mu on mu.MASSUNITS_ID = n.PPACKMASSUNID
                    left join rls.CUBICUNITS cu on cu.CUBICUNITS_ID = n.PPACKCUBUNID
                    left join lateral (
                        select coalesce(
                            cast(cast(p.DFMASS as float) as varchar) || ' ' || dfmu.SHORTNAME,
                            cast(cast(p.DFCONC as float) as varchar) || ' ' || dfcu.SHORTNAME,
                            cast(p.DFACT as varchar) || ' ' || dfau.SHORTNAME,
                            cast(p.DFSIZE as varchar) || ' ' || dfsu.SHORTNAME
                        ) as Value
                    ) dose on true
                    left join lateral (
                        select (
                            (case when d.Drug_Fas is not null then cast(d.Drug_Fas as varchar) || ' доз' else '' end) ||
                            (case when d.Drug_Fas is not null and coalesce(d.Drug_Volume, d.Drug_Mass) is not null then ', ' else '' end) ||
                            (case when coalesce(d.Drug_Volume,d.Drug_Mass) is not null then coalesce(d.Drug_Volume,d.Drug_Mass) else '' end)
                        ) as Value
                    ) fas on true
				where
					ds.WhsDocumentSupply_id = :WhsDocumentSupply_id
			";
		} elseif ( $filter['WhsDocumentType_id'] == 13 ) { //Документ на исключение
            $query = "
				select
					d.Drug_id as \"Drug_id\",
					d.Drug_Code as \"Drug_Code\",
					d.Drug_Name as \"Drug_Name\",
					dor.Okei_id as \"Okei_id\",
					dor.DrugOstatRegistry_Kolvo as \"DrugOstatRegistry_Kolvo\",
					dcm.DrugComplexMnn_RusName as \"DrugComplexMnn_RusName\",
                    tn.NAME as \"Tradenames_Name\",
                    dform.NAME as \"DrugForm_Name\",
                    dose.Value as \"Drug_Dose\",
                    fas.Value as \"Drug_Fas\",
					cast(round(coalesce(wdss.WhsDocumentSupplySpec_Price, 0), 2) as numeric(10, 2)) as \"WhsDocumentSupplySpec_Price\",
					cast(round(coalesce(wdss.WhsDocumentSupplySpec_PriceNDS, 0), 2) as numeric(10, 2)) as \"WhsDocumentSupplySpec_PriceNDS\",
                    rc.REGNUM as \"Reg_Num\",
                    rceff.FULLNAME as \"Reg_Firm\",
                    rceffc.NAME as \"Reg_Country\",
                    (to_char( rc.REGDATE, {$this->dateTimeForm104}) || coalesce(' - ' || to_char(rc.ENDDATE, {$this->dateTimeForm104}), '')) as \"Reg_Period\",
                    to_char(rc.Reregdate, {$this->dateTimeForm104}) as \"Reg_ReRegDate\"
				from
					v_DrugShipment ds
					inner join v_DrugOstatRegistry dor on dor.DrugShipment_id = ds.DrugShipment_id and dor.SubAccountType_id = 2
					inner join rls.v_Drug d on d.Drug_id = dor.Drug_id
					left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
					inner join v_WhsDocumentSupplySpec wdss on /*wdss.Drug_id = dor.Drug_id and */wdss.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
                    left join rls.v_prep p  on p.Prep_id = d.DrugPrep_id
                    left join rls.REGCERT rc on rc.REGCERT_ID = p.REGCERTID
                    left join rls.REGCERT_EXTRAFIRMS rcef on rcef.CERTID = rc.REGCERT_ID
                    left join rls.v_FIRMS rceff  on rceff.FIRMS_ID = rcef.FIRMID
                    left join rls.v_COUNTRIES rceffc  on rceffc.COUNTRIES_ID = rceff.COUNTID
                    left join rls.v_TRADENAMES tn  on tn.TRADENAMES_ID = p.TRADENAMEID
                    left join rls.v_CLSDRUGFORMS dform  on dform.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
                    left join rls.MASSUNITS dfmu on dfmu.MASSUNITS_ID = p.DFMASSID
                    left join rls.CONCENUNITS dfcu on dfcu.CONCENUNITS_ID = p.DFCONCID
                    left join rls.ACTUNITS dfau on dfau.ACTUNITS_ID = p.DFACTID
                    left join rls.SIZEUNITS dfsu on dfsu.SIZEUNITS_ID = p.DFSIZEID
                    left join rls.v_Nomen n on n.NOMEN_ID = d.Drug_id
                    left join rls.MASSUNITS mu on mu.MASSUNITS_ID = n.PPACKMASSUNID
                    left join rls.CUBICUNITS cu on cu.CUBICUNITS_ID = n.PPACKCUBUNID
                    left join lateral (
                        select coalesce(
                            cast(cast(p.DFMASS as float) as varchar) || ' ' ||dfmu.SHORTNAME,
                            cast(cast(p.DFCONC as float) as varchar) || ' ' || dfcu.SHORTNAME,
                            cast(p.DFACT as varchar) || ' ' ||dfau.SHORTNAME,
                            cast(p.DFSIZE as varchar) || ' ' || dfsu.SHORTNAME
                        ) as Value
                    ) dose on true
                    left join lateral (
                        select (
                            (case when d.Drug_Fas is not null then cast(d.Drug_Fas as varchar) || ' доз' else '' end) ||
                            (case when d.Drug_Fas is not null and coalesce(d.Drug_Volume,d.Drug_Mass) is not null then ', ' else '' end) ||
                            (case when coalesce(d.Drug_Volume,d.Drug_Mass) is not null then coalesce(d.Drug_Volume,d.Drug_Mass) else '' end)
                        ) as Value
                    ) fas on true
				where
					ds.WhsDocumentSupply_id = :WhsDocumentSupply_id
			";
		}
		if(!isset($query)) {
		    return false;
        }

		$result = $this->db->query($query, $filter);
		if (!is_object($result)) {
			return false;
		}

        return $result->result('array');
	}
	
	
}