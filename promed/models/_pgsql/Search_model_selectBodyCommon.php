<?php


class Search_model_selectBodyCommon
{
	public static function selectBody_PersonDisp($data)
	{
		switch (getRegionNumber()) {
			case "10":
				$sysNick = "consulspec";
				break;
			case "201":
				$sysNick = "dispdinnabl";
				break;
			case "3":
				$sysNick = "desease";
				break;
			default:
				$sysNick = "disp";
				break;
		}
		$query = "
			inner join v_PersonDisp PD on PD.Person_id = PS.Person_id
			left join v_Diag dg1 on PD.Diag_id = dg1.Diag_id
			left join v_Diag dg2 on PD.Diag_pid = dg2.Diag_id
			left join v_Diag dg3 on PD.Diag_nid = dg3.Diag_id
			left join v_MedPersonal mp1 on PD.MedPersonal_id = mp1.MedPersonal_id and PD.Lpu_id = mp1.Lpu_id
			left join LpuSection lpus1 on PD.LpuSection_id = lpus1.LpuSection_id
			left join Sickness scks on PD.Sickness_id = scks.Sickness_id
			left join v_Lpu lpu1 on PD.Lpu_id = lpu1.Lpu_id 
			left join lateral(
				select EVPL.EvnVizitPL_setDT as PersonDisp_LastDate
				from
					v_EvnVizitPL EVPL
					left join v_VizitType VT on VT.VizitType_id = EVPL.VizitType_id
				where VT.VizitType_SysNick='{$sysNick}'
				  and PD.PersonDisp_begDate::date <= EVPL.EvnVizitPL_setDT::date
				  and PD.Diag_id = EVPL.Diag_id
				  and EVPL.Person_id = PD.Person_id
				order by EVPL.EvnVizitPL_setDT desc
				limit 1
			) as LD on true
			left join lateral(
				select pdv.PersonDispVizit_NextFactDate
				from v_PersonDispVizit pdv
				where pdv.PersonDisp_id = PD.PersonDisp_id
				order by pdv.PersonDispVizit_NextFactDate desc
				limit 1
			) as lapdv on true
			left join lateral(
				select pdv.PersonDispVizit_NextDate
				from v_PersonDispVizit pdv
				where pdv.PersonDisp_id = PD.PersonDisp_id
				  and pdv.PersonDispVizit_NextDate::date >= (select dt from mv)::date
				  and pdv.PersonDispVizit_NextFactDate is null
				order by pdv.PersonDispVizit_NextDate asc
				limit 1
			) as oapdv on true
		";
		if (!empty($data["session"]["lpu_id"])) {
			$query .= "
				left join lateral(
					select
						PersonCard_id,
						LpuRegion_Name
					from v_PersonCard_all
					where Person_id = PS.Person_id
					  and LpuAttachType_id = 1
					  and Lpu_id = {$data["session"]["lpu_id"]}
					order by PersonCard_begDate desc
					limit 1
				) as PCA on true
			";
		} else {
			$query .= "
				left join lateral(
					select 
						null as  PersonCard_id,
						null as LpuRegion_Name
				) as PCA on true
			";
		}
		$query .= "
			left join lateral(
				select
					max(
						case when PersonDispMedicament_begDate <= (select dt from mv) and (PersonDispMedicament_endDate >= (select dt from mv) or PersonDispMedicament_endDate is null)
							then 1 
							else 
								case when PersonDispMedicament_begDate > (select dt from mv)
									then 0  else null 
								end
						end
					) as isnoz
				from PersonDispMedicament pdm 
				where pdm.PersonDisp_id = PD.PersonDisp_id and pdm.PersonDispMedicament_begDate is not null
			) as noz on true
			left join lateral(
				select
					MP_L.MedPersonal_id as MedPersonal_id_last,
					MP_L.Person_Fio as MedPersonal_FIO_last
				from
					v_PersonDispHist PDH_L
					left join v_MedPersonal MP_L on MP_L.MedPersonal_id = PDH_L.MedPersonal_id
				where PDH_L.PersonDisp_id = PD.PersonDisp_id
				order by PDH_L.PersonDispHist_begDate desc		
				limit 1			
			) as mph_last on true
		";
		return $query;
	}

	public static function selectBody_PersonCardStateDetail_old1()
	{
		return "
			inner join v_PersonCard_All PCSD on PCSD.Person_id = PS.Person_id
			left join CardCloseCause ccc on ccc.CardCloseCause_id = PCSD.CardCloseCause_id
			left join lateral(
				select
					pc1.LpuRegion_Name,
					lp.Lpu_Nick
				from
					v_PersonCard pc1
					inner join v_Lpu lp on pc1.Lpu_id=lp.Lpu_id
				where PCSD.Person_id = pc1.Person_id and PCSD.LpuAttachType_id = pc1.LpuAttachType_id
				order by pc1.PersonCard_Begdate
				limit 1
			) as attcard on true
			left join Address on ps.PAddress_id = Address.Address_id
			left join Address Address1 on ps.UAddress_id = Address1.Address_id
			left join lateral(
				select pall.Person_id as cnt
				from
					v_Person_all  pall 
					inner join v_Polis pol  on pall.Polis_id = pol.Polis_id
				where pall.Person_id = ps.Person_id
				  and pall.Server_id = ps.Server_id
				  and pol.Polis_begDate < CAST(:PCSD_begDate as date)
				  and (pol.Polis_endDate is null or pol.Polis_endDate > CAST(:PCSD_begDate as date))
				limit 1
			) as notHasPolis on true
			left join lateral(
				select pall.Person_id as cnt
				from
					v_Person_all pall 
					inner join v_Polis pol on pall.Polis_id = pol.Polis_id
				where pall.Person_id = ps.Person_id
				  and pall.Server_id = ps.Server_id
				  and pol.Polis_begDate >= CAST(:PCSD_begDate as date)
				  and pol.Polis_begDate <= CAST(:PCSD_endDate as date)
				limit 1
			) as hasPolis on true
			left join lateral(
				select pall.Person_id as cnt
				from
					v_Person_all  pall 
					inner join v_Polis pol on pall.Polis_id = pol.Polis_id
				where pall.Person_id = ps.Person_id
				  and pall.Server_id = ps.Server_id
				  and pol.Polis_begDate < CAST(:PCSD_begDate as date)
				  and pol.Polis_endDate >= CAST(:PCSD_begDate as date)
				  and pol.Polis_endDate <= CAST(:PCSD_endDate as date)
				limit 1
			) as HasPolisBefore on true
			left join lateral(
				select Polis.Polis_id
				from
					v_Person_all Person
					left join v_Polis Polis on Person.Polis_id = Polis.Polis_id
				where Person.Person_id = PS.Person_id
				  and Person.Server_id = 0
				  and ( Polis.Polis_begDate < CAST(:PCSD_endDate as date)
				  and (Polis.Polis_endDate is null or Polis.Polis_endDate > CAST(:PCSD_endDate as date)))
				limit 1
			) as Polis on true
			left join lateral(
				select
                 	pclast.PersonCard_id,
					pclast.Lpu_id
                from PersonCard pclast
				where PCSD.Person_id = pclast.Person_id
				  and pclast.PersonCard_id < PCSD.PersonCard_id
				  and pclast.LpuAttachType_id = PCSD.LpuAttachType_id
				order by pclast.PersonCard_id desc
				limit 1
			) as LastCard on true
			left join lateral(
				select
                	pclast.PersonCard_id,
					pclast.Lpu_id,
					pclast.PersonCard_begDate
                from PersonCard pclast
				where PCSD.Person_id = pclast.Person_id
				  and pclast.PersonCard_id > PCSD.PersonCard_id
				  and pclast.LpuAttachType_id = PCSD.LpuAttachType_id
				order by pclast.PersonCard_id asc
				limit 1
			) as NextCard on true
		";
	}

	public static function selectBody_PersonCardStateDetail_old()
	{
		return "
			inner join v_PersonCard_All PCSD on PCSD.Person_id = PS.Person_id
			left join CardCloseCause ccc on ccc.CardCloseCause_id = PCSD.CardCloseCause_id
			left join lateral(
				select
					pc1.LpuRegion_Name,
					lp.Lpu_Nick
				from
					v_PersonCard pc1
					inner join v_Lpu lp on pc1.Lpu_id=lp.Lpu_id
				where PCSD.Person_id = pc1.Person_id and PCSD.LpuAttachType_id = pc1.LpuAttachType_id
				order by pc1.PersonCard_Begdate
				limit 1
			) as attcard on true
			left join Address on ps.PAddress_id = Address.Address_id
			left join Address Address1 on ps.UAddress_id = Address1.Address_id
			left join v_Polis pols on pols.Polis_id = PS.Polis_id and pols.Polis_begDate < (select dt from mv) and (pols.Polis_EndDate is null or Pols.Polis_endDate > (select dt from mv))
		    left join lateral(
		        select Polis.Polis_id
		        from
		            v_Person_all Person
		            left join v_Polis Polis on Person.Polis_id = Polis.Polis_id
				where Person.Person_id = PCSD.Person_id
				  and Person.Server_id = 0
				  and Polis.Polis_begDate::date < CAST(:PCSD_begDate as date)
				  and (Polis.Polis_endDate is null or Polis.Polis_endDate::date >= CAST(:PCSD_begDate as date))
				limit 1
			) as PolisBeg on true
			left join lateral(
				select Polis.Polis_id
				from
					v_Person_all Person
					left join v_Polis Polis on Person.Polis_id = Polis.Polis_id
				where Person.Person_id = PCSD.Person_id
				  and Person.Server_id = 0
				  and Polis.Polis_begDate::date <= CAST(:PCSD_endDate as date)
				  and (Polis.Polis_endDate is null or Polis.Polis_endDate::date > CAST(:PCSD_endDate as date))
				limit 1 
			) as PolisEnd on true
			left join lateral(
				select
					pclast.PersonCard_id,
					pclast.Lpu_id
				from PersonCard pclast
				where PCSD.Person_id = pclast.Person_id
				  and pclast.PersonCard_id < PCSD.PersonCard_id
				  and pclast.LpuAttachType_id = PCSD.LpuAttachType_id
				order by pclast.PersonCard_id desc
				limit 1
			) as LastCard on true
			left join lateral(
				select
					pclast.PersonCard_id,
					pclast.Lpu_id,
					pclast.PersonCard_begDate
				from PersonCard pclast
				where PCSD.Person_id = pclast.Person_id
				  and pclast.PersonCard_id > PCSD.PersonCard_id
				  and pclast.LpuAttachType_id = PCSD.LpuAttachType_id
				order by pclast.PersonCard_id asc
				limit 1
			) as NextCard on true
		";
	}

	public static function selectBody_PersonCardStateDetail()
	{
		return "
			inner join v_PersonCard PCSD on PCSD1.PersonCard_id = PCSD.PersonCard_id
			left join v_PersonState pcc on pcc.Person_id = PCSD.Person_id
			left join v_LpuRegion lr on lr.LpuRegion_id = PCSD.LpuRegion_id
			left join LpuRegionType lrt on lrt.LpuRegionType_id = lr.LpuRegionType_id
			left join CardCloseCause ccc on ccc.CardCloseCause_id = PCSD.CardCloseCause_id
			left join lateral(
				select
					pc1.LpuRegion_Name,
					lp.Lpu_Nick
				from
					v_PersonCard pc1
					inner join v_Lpu lp on pc1.Lpu_id=lp.Lpu_id
				where PCSD.Person_id = pc1.Person_id and PCSD.LpuAttachType_id = pc1.LpuAttachType_id
				order by pc1.PersonCard_Begdate
				limit 1
			) as attcard on true
			left join Address on ps.PAddress_id = Address.Address_id
			left join Address Address1 on ps.UAddress_id = Address1.Address_id
			left join v_Polis pols on pols.Polis_id = PS.Polis_id and pols.Polis_begDate::date < (select dt from mv)::date and (pols.Polis_EndDate is null or Pols.Polis_endDate::date > (select dt from mv)::date)
			left join v_OrgSmo omsOrgSmo on pols.PolisType_id in (1, 4) and pols.OrgSmo_id = omsOrgSmo.OrgSmo_id
			left join v_PersonCard dmspc on dmspc.Lpu_id = :Lpu_id and dmspc.Person_id = PS.Person_id and  dmspc.LpuAttachType_id = 5 and dmspc.PersonCard_begDate::date < (select dt from mv)::date and (dmspc.PersonCard_endDate is null or dmspc.PersonCard_endDate::date > (select dt from mv)::date)
			left join v_OrgSmo dmsOrgSmo on dmspc.OrgSmo_id = dmsOrgSmo.OrgSmo_id
			left join lateral(
				select Polis.Polis_id
				from
					v_Person_all Person
					left join v_Polis Polis on Person.Polis_id = Polis.Polis_id
				where Person.Person_id = PCSD.Person_id
				  and Polis.Polis_begDate::date < CAST(:PCSD_begDate as date)
				  and (Polis.Polis_endDate is null or Polis.Polis_endDate::date >= CAST(:PCSD_begDate as date)) 
				limit 1
			) as PolisBeg on true
			left join lateral(
				select Polis.Polis_id
				from
					v_Person_all Person
					left join v_Polis Polis on Person.Polis_id = Polis.Polis_id
				where Person.Person_id = PCSD.Person_id
				  and Polis.Polis_begDate::date <= CAST(:PCSD_endDate as date)
				  and (Polis.Polis_endDate is null or Polis.Polis_endDate::date > CAST(:PCSD_endDate as date))
				limit 1
			) as PolisEnd on true
			left join lateral(
				select
					pclast.PersonCard_id,
					pclast.Lpu_id
				from PersonCard pclast
				where PCSD.Person_id = pclast.Person_id
				  and pclast.PersonCard_id < PCSD.PersonCard_id
				  and pclast.LpuAttachType_id = PCSD.LpuAttachType_id
				order by pclast.PersonCard_id desc
				limit 1
			) as LastCard on true
			left join lateral(
				select
					pclast.PersonCard_id,
					pclast.Lpu_id,
					pclast.PersonCard_begDate
				from PersonCard pclast
				where PCSD.Person_id = pclast.Person_id
				  and pclast.PersonCard_id > PCSD.PersonCard_id
				  and pclast.LpuAttachType_id = PCSD.LpuAttachType_id
				order by pclast.PersonCard_id
				limit 1
			) as NextCard on true
		";
	}

	public static function selectBody_EvnUslugaPar(Search_model $callObject, $data)
	{
	    $query = '';
		$lpuIdFilter = " and EUP.Lpu_id " . $callObject->getLpuIdFilter($data);
		if (getRegionNick() == 'penza' && havingGroup('OuzSpec')) {
			$lpuIdFilter = '';
		}
		if ($data['PersonPeriodicType_id'] == 2) {
			$query .= " inner join v_EvnUslugaPar EUP on EUP.Server_id = PS.Server_id and EUP.PersonEvn_id = PS.PersonEvn_id" . $lpuIdFilter;
		} else {
			$query .= " inner join v_EvnUslugaPar EUP on EUP.Person_id = PS.Person_id" . $lpuIdFilter;
		}
		$query .= "
			left join v_EvnCostPrint ecp on ecp.Evn_id = EUP.EvnUslugaPar_id
			left join v_LpuSection LS on LS.LpuSection_id = EUP.LpuSection_uid
			left join v_Lpu lpu on lpu.Lpu_id = LS.Lpu_id
			left join lateral(
				select Person_Fio
				from v_MedPersonal
				where MedPersonal_id = EUP.MedPersonal_id
				limit 1
			) MP on true
			left join Usluga on Usluga.Usluga_id = EUP.Usluga_id
			left join UslugaComplex on UslugaComplex.UslugaComplex_id = EUP.UslugaComplex_id
			left join v_Evn EvnParent on EvnParent.Evn_id = EUP.EvnUslugaPar_pid
			left join v_PayType PT on PT.PayType_id = EUP.PayType_id
			left join v_MedStaffFact MedStaffFact on MedStaffFact.MedStaffFact_id = EUP.MedStaffFact_id
			left join v_PostMed PostMed on PostMed.PostMed_id = MedStaffFact.Post_id
			left join v_EvnDirection_all ED on EUP.EvnDirection_id = ED.EvnDirection_id
			left join v_Lpu as LD on LD.Lpu_id = ED.Lpu_did
	        left join v_Lpu l on l.Lpu_id = ED.Lpu_sid -- NGS
            left join v_Lpu l2 on l2.Lpu_id = EUP.Lpu_did -- NGS 
			left join v_Lpu LD_sid on LD_sid.Lpu_id = ED.Lpu_sid
            left join Org on Org.Org_id = ED.Org_sid
            left join v_Lpu as v_Lpu_org_1 on v_Lpu_org_1.Org_id = EUP.Org_did
            left join v_Lpu as v_Lpu_org_2 on v_Lpu_org_2.Lpu_id = EUP.Lpu_did
            left join Org as Org_3 on Org_3.Org_id = EUP.Org_did
		";
		$query .= (isset($data["SignalInfo"]) && $data["SignalInfo"] == 1)
			? "
				left join lateral(
					select EvnXml.EvnXml_id, xth.XmlTemplateHtml_HtmlTemplate as XmlTemplate_HtmlTemplate
					from
						v_EvnXml  EvnXml
						left join XmlTemplateHtml xth on xth.XmlTemplateHtml_id = EvnXml.XmlTemplateHtml_id
					where Evn_id = EUP.EvnUslugaPar_id
					order by EvnXml_insDT desc
					limit 1
				) as doc on true
			"
			: "
				left join lateral(
					select EvnXml.EvnXml_id
					from v_EvnXml  EvnXml
					where Evn_id = EUP.EvnUslugaPar_id
					order by EvnXml_insDT desc
					limit 1
				) doc on true
			";
		if (!empty($data['UslugaExecutionType_id'])) {
			$query .= "
				inner join lateral(
					select EvnLabRequest_id
					from v_EvnLabRequestUslugaComplex ELRUC
					where ELRUC.EvnUslugaPar_id = EUP.EvnUslugaPar_id
					limit 1
				) ELRUC on true
				inner join v_EvnLabRequest ELR on ELR.EvnLabRequest_id = ELRUC.EvnLabRequest_id
			";
		}
		if ($callObject->regionNick == "kz") {
			$query .= "
				left join r101.AISResponse air on air.Evn_id = EUP.EvnUslugaPar_id  and air.AISFormLoad_id = 1
				left join r101.AISResponse air9 on air9.Evn_id = EUP.EvnUslugaPar_id  and air9.AISFormLoad_id = 2
				left join r101.AISUslugaComplexLink ucl on ucl.UslugaComplex_id = EUP.UslugaComplex_id
			";
		}
		return $query;
	}

	public static function selectBody_EvnReceptGeneral(Search_model $callObject, $data, $isFarmacy)
	{
		$getLpuIdFilterString = $callObject->getLpuIdFilter($data);
		$query = ($data["PersonPeriodicType_id"] == 2)
			? " inner join v_EvnReceptGeneral ERG on ERG.Server_id = PS.Server_id and ERG.PersonEvn_id = PS.PersonEvn_id"
			: " inner join v_EvnReceptGeneral ERG on ERG.Person_id = PS.Person_id";
		if (!empty($data["WithDrugComplexMnn"])) {
			$query .= " and ERG.DrugComplexMnn_id is not null";
		}
		if (!isMinZdrav() && !$isFarmacy && $data["Lpu_id"] > 0) {
			$query .= " and ERG.Lpu_id {$getLpuIdFilterString}";
		}
		$query .= "
			left join dbo.v_ReceptValid RV on RV.ReceptValid_id = ERG.ReceptValid_id
			left join v_ReceptForm RecF on RecF.ReceptForm_id = ERG.ReceptForm_id
			left join v_Drug ERDrug on ERDrug.Drug_id = ERG.Drug_id
			left join v_Polis pls on pls.Polis_id = ps.Polis_id
			left join rls.v_Drug ERDrugRls on ERDrugRls.Drug_id = ERG.Drug_rlsid
			left join rls.v_DrugComplexMnn DCM on DCM.DrugComplexMnn_id = ERG.DrugComplexMnn_id
			left join rls.v_DrugNomen DrugNomen on DrugNomen.Drug_id = ERG.Drug_rlsid
			left join dbo.v_WhsDocumentCostItemType wdcit on wdcit.WhsDocumentCostItemType_id = ERG.WhsDocumentCostItemType_id
			left join dbo.v_DrugFinance drugFin on drugFin.DrugFinance_id = ERG.DrugFinance_id
			left join v_ReceptDelayType ER_RDT on ER_RDT.ReceptDelayType_id = ERG.ReceptDelayType_id
			left join ReceptOtov RecOt on RecOt.EvnRecept_id = ERG.EvnReceptGeneral_id
			left join lateral(
				select Person_Fio
				from v_MedPersonal
				where MedPersonal_id = ERG.MedPersonal_id
				limit 1
			) as ERMP on true
			left join lateral(
				select sum((ROt.EvnRecept_Price * ROt.EvnRecept_Kolvo)) as recSum
				from ReceptOtov ROt
				where ROt.EvnRecept_id = ERG.EvnReceptGeneral_id
			) as RecOtovSum on true
			left join lateral(
				select dus.DocumentUc_id 
				from
					ReceptOtov ROtov
					left join DocumentUcStr dus on dus.ReceptOtov_id = ROtov.ReceptOtov_id
				where ROtov.EvnRecept_id = ERG.EvnReceptGeneral_id
				limit 1
			) as DocUc on true
			left join v_Diag ERDiag on ERDiag.Diag_id = ERG.Diag_id
		";
		if (!$isFarmacy && $data["Lpu_id"] > 0) {
			$query .= " and ERG.Lpu_id {$getLpuIdFilterString}";
		}
		return $query;
	}

	public static function selectBody_EvnRecept(Search_model $callObject, $data, $isFarmacy)
	{
		$getLpuIdFilterString = $callObject->getLpuIdFilter($data);
		$query = "";
		if ($data["PersonPeriodicType_id"] == 2) {
			$query .= " inner join v_EvnRecept ER on ER.Server_id = PS.Server_id and ER.PersonEvn_id = PS.PersonEvn_id";
			if (!isMinZdrav() && !$isFarmacy && $data["Lpu_id"] > 0) {
				$query .= " and ER.Lpu_id {$getLpuIdFilterString}";
			}
		} else {
			$query .= " inner join v_EvnRecept ER on ER.Person_id = PS.Person_id";
			if (!isMinZdrav() && !$isFarmacy && $data["Lpu_id"] > 0) {
				$query .= " and ER.Lpu_id {$getLpuIdFilterString}";
			}
		}
		$query .= "
			left join v_Lpu lpu on lpu.Lpu_id = er.lpu_id
			left join dbo.v_ReceptValid RV on RV.ReceptValid_id = ER.ReceptValid_id
		";
		$query .= "
			left join v_ReceptForm RecF on RecF.ReceptForm_id = ER.ReceptForm_id
			left join v_ReceptType RecT on RecT.ReceptType_id = ER.ReceptType_id
			left join v_Drug ERDrug on ERDrug.Drug_id = ER.Drug_id
			left join v_Polis pls on pls.Polis_id = ps.Polis_id
			left join rls.v_Drug ERDrugRls on ERDrugRls.Drug_id = ER.Drug_rlsid
			left join rls.v_DrugComplexMnn DCM on DCM.DrugComplexMnn_id = ER.DrugComplexMnn_id
			left join rls.v_DrugNomen DrugNomen on DrugNomen.Drug_id = ER.Drug_rlsid or DrugNomen.DrugNomen_Code = ERDrug.Drug_CodeG::varchar
			left join dbo.v_WhsDocumentCostItemType wdcit on wdcit.WhsDocumentCostItemType_id = ER.WhsDocumentCostItemType_id
			left join dbo.v_DrugFinance drugFin on drugFin.DrugFinance_id = ER.DrugFinance_id
			left join v_ReceptDelayType ER_RDT on ER_RDT.ReceptDelayType_id = ER.ReceptDelayType_id
			left join v_pmUserCache pmUC on pmUC.PMUser_id = ER.pmUser_updID
			left join lateral(
				select
					RecOt_t.EvnRecept_otpDate,
					RecOt_t.EvnRecept_obrDate
				from ReceptOtov RecOt_t
				where RecOt_t.EvnRecept_id = ER.EvnRecept_id
				order by RecOt_t.ReceptOtov_insDT desc
				limit 1
			) as RecOt on true
			left join lateral(
				select Person_Fio
				from v_MedPersonal
				where MedPersonal_id = ER.MedPersonal_id
				limit 1
			) as ERMP on true
			left join lateral(
				select sum((ROt.EvnRecept_Price * ROt.EvnRecept_Kolvo)) as recSum
				from ReceptOtov ROt
				where ROt.EvnRecept_id = ER.EvnRecept_id
			) as RecOtovSum on true
			left join lateral(
				select dus.DocumentUc_id
				from
					ReceptOtov ROtov
					left join DocumentUcStr dus on dus.ReceptOtov_id = ROtov.ReceptOtov_id
				where ROtov.EvnRecept_id = ER.EvnRecept_id
				limit 1
			) as DocUc on true
			left join v_Diag ERDiag on ERDiag.Diag_id = ER.Diag_id
		";
		if (!$isFarmacy && $data["Lpu_id"] > 0) {
			$query .= " and ER.Lpu_id {$getLpuIdFilterString}";
		}
		$query .= "
			left join lateral(
				select 
					Wr_t.ReceptWrong_id,
					Wr_t.ReceptWrong_Decr
				from ReceptWrong Wr_t
				where Wr_t.EvnRecept_id = ER.EvnRecept_id
				order by Wr_t.ReceptWrong_updDT desc
				limit 1
			) as Wr on true
		";
		if ($data["EvnReceptSearchDateType"] == "otkaz") {
			$query .= "
				left join v_WhsDocumentUcActReceptList WDUARL on WDUARL.EvnRecept_id = ER.EvnRecept_id
				left join v_WhsDocumentUcActReceptOut WDUARO on WDUARO.WhsDocumentUcActReceptOut_id = WDUARL.WhsDocumentUcActReceptOut_id
			";
		}
		return $query;
	}

	public static function selectBody_KvsEvnStick($data, $getLpuIdFilterString)
	{
		$query = '';
		if ($data['PersonPeriodicType_id'] == 2) {
			$query .= " inner join v_EvnPS EPS on EPS.Server_id = PS.Server_id
							and EPS.PersonEvn_id = PS.PersonEvn_id
							and EPS.Lpu_id {$getLpuIdFilterString}";
		} else {
			$query .= " inner join v_EvnPS EPS on EPS.Person_id = PS.Person_id
							and EPS.Lpu_id {$getLpuIdFilterString}";
			if (!empty($data['Person_citizen']) &&($data['Person_citizen']==3)) {
				//добавляем таблицу для поиска по гражданству, чтобы ссылаться на KLCountry_id из NationalityStatus, а не PS, так как PS меняется при изменениях адреса регистрации/проживания
				$query .= " left join NationalityStatus ns on ns.NationalityStatus_id = ps.NationalityStatus_id";
			}
		}

		return $query;
	}

	public static function selectBody_KvsEvnStick_EvnPS($getLpuIdFilterString)
	{
		return "
			left join v_Lpu as dbflpu on dbflpu.Lpu_id = EPS.Lpu_id
			left join PrehospArrive dbfpa on dbfpa.PrehospArrive_id = EPS.PrehospArrive_id
			left join PrehospDirect dbfpd on dbfpd.PrehospDirect_id = EPS.PrehospDirect_id
			left join PrehospToxic dbfpt on dbfpt.PrehospToxic_id = EPS.PrehospToxic_id
			left join PayType dbfpayt on dbfpayt.PayType_id = EPS.PayType_id
			left join PrehospTrauma dbfprtr on dbfprtr.PrehospTrauma_id = EPS.PrehospTrauma_id
			left join PrehospType dbfprtype on dbfprtype.PrehospType_id = EPS.PrehospType_id
			left join Org dbfdorg on dbfdorg.Org_id = EPS.Org_did
			left join LpuSection dbflsd on dbflsd.LpuSection_id = EPS.LpuSection_did
			left join Lpu dbfdlpu on dbfdlpu.Lpu_id = EPS.Lpu_did
			left join Org dbfoorg on dbfoorg.Org_id = dbfdlpu.Org_id
			left join v_MedPersonal dbfmp on dbfmp.MedPersonal_id = EPS.MedPersonal_pid and dbfmp.Lpu_id = EPS.Lpu_id
			left join v_EvnSection EPSLastES on EPSLastES.EvnSection_pid = EPS.EvnPS_id and EPSLastES.EvnSection_Index = EPSLastES.EvnSection_Count - 1 and EPSLastES.Lpu_id {$getLpuIdFilterString}
		";
	}

	public static function selectBody_KvsEvnStick_EvnSection($getLpuIdFilterString)
	{
		return "
			inner join v_EvnSection as ESEC on ESEC.EvnSection_pid = EPS.EvnPS_id and ESEC.Lpu_id {$getLpuIdFilterString}
			left join v_LpuSection as dbfsec on dbfsec.LpuSection_id = ESEC.LpuSection_id
			left join v_PayType as dbfpay on dbfpay.PayType_id = ESEC.PayType_id
			left join v_TariffClass as dbftar on dbftar.TariffClass_id = ESEC.TariffClass_id
			left join lateral(
				select *
				from v_MedPersonal
				where MedPersonal_id = EPS.MedPersonal_id and Lpu_id = EPS.Lpu_id
				limit 1
			) as dbfmp on true
		";
	}

	public static function selectBody_KvsEvnStick_EvnDiag($getLpuIdFilterString)
	{
		return "
			left join v_EvnSection sect on sect.EvnSection_pid = EPS.EvnPS_id and sect.Lpu_id {$getLpuIdFilterString}
			left join v_EvnLeave leav on leav.EvnLeave_pid = EPS.EvnPS_id and leav.Lpu_id {$getLpuIdFilterString}
			inner join v_EvnDiagPS EDPS on EDPS.EvnDiagPS_pid = EPS.EvnPS_id or EDPS.EvnDiagPS_pid = sect.EvnSection_id or EDPS.EvnDiagPS_pid = leav.EvnLeave_id
			left join lateral
				GetMesForEvnDiagPS(
					EDPS.Diag_id,
					EDPS.Person_id,
					EPS.Lpu_id,
					EPS.LpuSection_did,
					EDPS.EvnDiagPS_setDate
			) as dbfmes on true
		";
	}

	public static function selectBody_KvsEvnStick_EvnLeave($getLpuIdFilterString)
	{
		return "
			left join v_EvnLeave ELV on ELV.EvnLeave_pid = EPS.EvnPS_id and EPS.LeaveType_id = 1 and ELV.Lpu_id {$getLpuIdFilterString}
			left join v_EvnOtherLpu dbfeol on dbfeol.EvnOtherLpu_pid = EPS.EvnPS_id and EPS.LeaveType_id = 2 and dbfeol.Lpu_id {$getLpuIdFilterString}
			left join v_EvnDie dbfed on dbfed.EvnDie_pid = EPS.EvnPS_id and EPS.LeaveType_id = 3 and dbfed.Lpu_id {$getLpuIdFilterString}
			left join v_EvnOtherStac dbfeost on dbfeost.EvnOtherStac_pid = EPS.EvnPS_id and EPS.LeaveType_id = 4 and dbfeost.Lpu_id {$getLpuIdFilterString}
			left join v_EvnOtherSection dbfeos on dbfeos.EvnOtherSection_pid = EPS.EvnPS_id and EPS.LeaveType_id = 5 and dbfeos.Lpu_id {$getLpuIdFilterString}
			left join v_EvnOtherSectionBedProfile dbfeosbp on dbfeosbp.EvnOtherSectionBedProfile_pid = EPS.EvnPS_id and EPS.LeaveType_id = 6 and dbfeosbp.Lpu_id {$getLpuIdFilterString}
			inner join v_LeaveType dbflt on dbflt.LeaveType_id = EPS.LeaveType_id and dbflt.Lpu_id {$getLpuIdFilterString}
				and (
					ELV.EvnLeave_pid = EPS.EvnPS_id or
					dbfeol.EvnOtherLpu_pid = EPS.EvnPS_id or
					dbfed.EvnDie_pid = EPS.EvnPS_id or
					dbfeost.EvnOtherStac_pid = EPS.EvnPS_id or
					dbfeos.EvnOtherSection_pid = EPS.EvnPS_id or
					dbfeosbp.EvnOtherSectionBedProfile_pid = EPS.EvnPS_id
				)
			left join v_LpuSection dbfls on dbfls.LpuSection_id = EPS.LpuSection_did
			left join v_LpuUnit dbflu on dbflu.LpuUnit_id = dbfls.LpuUnit_id
		";
	}

	public static function selectBody_KvsEvnStick_KvsPerson($prefix, $data, $getLpuIdFilterString)
	{
		$query = ($prefix == "PS2")
			? ($data["kvs_date_type"] == 2)
				? " inner join v_Person_all PS2 on PS2.Server_id = EPS.Server_id and PS2.PersonEvn_id = EPS.PersonEvn_id "
				: " inner join v_PersonState PS2 on PS2.Person_id = EPS.Person_id "
			: "";
		$query .= "
			left join Sex on Sex.Sex_id = {$prefix}.Sex_id
			left join SocStatus Soc on Soc.SocStatus_id = {$prefix}.SocStatus_id
			left join PersonChild PCh on PCh.Person_id = {$prefix}.Person_id
			left join YesNo IsInv on IsInv.YesNo_id = PCh.PersonChild_IsInvalid
			left join Diag InvD on InvD.Diag_id = PCh.Diag_id
			left join Polis Pol on Pol.Polis_id = {$prefix}.Polis_id
			left join OMSSprTerr OMSST on OMSST.OMSSprTerr_id = Pol.OMSSprTerr_id
			left join PolisType PolTp on PolTp.PolisType_id = Pol.PolisType_id
			left join v_OrgSmo OS on OS.OrgSmo_id = Pol.OrgSmo_id
			left join v_Org OSO on OSO.Org_id = OS.Org_id
			left join v_Address_all UA on UA.Address_id = {$prefix}.UAddress_id 
			left join v_Address_all PA on PA.Address_id = {$prefix}.PAddress_id
			left join Document Doc on Doc.Document_id = {$prefix}.Document_id 
			left join DocumentType DocTp on DocTp.DocumentType_id = Doc.DocumentType_id
			left join v_OrgDep OrgD on OrgD.OrgDep_id = Doc.OrgDep_id
			left join v_EvnSection EPSLastES2 on EPSLastES2.EvnSection_pid = EPS.EvnPS_id and EPSLastES2.EvnSection_Index = EPSLastES2.EvnSection_Count-1 and EPSLastES2.Lpu_id {$getLpuIdFilterString}
		";
		return $query;
	}

	public static function selectBody_KvsEvnStick_KvsPersonCard()
	{
		return "
			inner join v_PersonCard PC on PC.Person_id = PS.Person_id
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
		";
	}

	public static function selectBody_KvsEvnStick_KvsEvnDiag($getLpuIdFilterString)
	{
		return "
			left join v_EvnSection ESEC on ESEC.EvnSection_pid = EPS.EvnPS_id and ESEC.Lpu_id {$getLpuIdFilterString}
			inner join v_EvnDiagPS EDPS on EDPS.EvnDiagPS_pid = EPS.EvnPS_id or EDPS.EvnDiagPS_pid = ESEC.EvnSection_id
			left join DiagSetClass DSC on DSC.DiagSetClass_id = EDPS.DiagSetClass_id
			left join DiagSetType DST on DST.DiagSetType_id = EDPS.DiagSetType_id 
			left join Diag on Diag.Diag_id = EDPS.Diag_id
			left join v_Lpu Lpu on (
				(Lpu.Lpu_id = EPS.Lpu_did and EDPS.DiagSetType_id = 1) or
				(Lpu.Lpu_id = EPS.Lpu_id and EDPS.DiagSetType_id = 2) or
				(Lpu.Lpu_id = ESEC.Lpu_id and EDPS.DiagSetType_id = 3)
			)
			left join v_LpuSection LS on (
				(LS.LpuSection_id = EPS.LpuSection_did and EDPS.DiagSetType_id = 1) or
				(LS.LpuSection_id = EPS.LpuSection_pid and EDPS.DiagSetType_id = 2) or
				(LS.LpuSection_id = ESEC.LpuSection_id and EDPS.DiagSetType_id = 3)
			)
		";
	}

	public static function selectBody_KvsEvnStick_KvsEvnPS($getLpuIdFilterString)
	{
		return "
			left join v_EvnSection EPSLastES on EPSLastES.EvnSection_pid = EPS.EvnPS_id and EPSLastES.EvnSection_Index = EPSLastES.EvnSection_Count - 1 and EPSLastES.Lpu_id {$getLpuIdFilterString}
			left join Diag on Diag.Diag_id = EPS.Diag_pid
			left join YesNo IsCont on IsCont.YesNo_id = EPS.EvnPS_IsCont
			left join PayType PT on PT.PayType_id = EPS.PayType_id
			left join PrehospDirect PD on PD.PrehospDirect_id = EPS.PrehospDirect_id
			left join LpuSection LS on LS.LpuSection_id = EPS.LpuSection_did
			left join Lpu on EPS.Lpu_did = Lpu.Lpu_id
			left join Org on coalesce(Lpu.Org_id, EPS.Org_did, EPS.OrgMilitary_did) = Org.Org_id
			left join PrehospArrive PA on PA.PrehospArrive_id = EPS.PrehospArrive_id
			left join Lpu LpuF on LpuF.Org_id = EPS.Org_did
			left join YesNo IsFond on IsFond.YesNo_id = LpuF.Lpu_IsOMS
			left join Diag DiagD on DiagD.Diag_id = EPS.Diag_did
			left join PrehospToxic Toxic on Toxic.PrehospToxic_id = EPS.PrehospToxic_id
			left join v_PrehospTrauma Trauma on Trauma.PrehospTrauma_id = EPS.PrehospTrauma_id
			left join PrehospType PType on PType.PrehospType_id = EPS.PrehospType_id
			left join YesNo IsUnlaw on IsUnlaw.YesNo_id = EPS.EvnPS_IsUnlaw
			left join YesNo IsUnport on IsUnport.YesNo_id = EPS.EvnPS_IsUnport
			left join v_MedPersonal MP on MP.MedPersonal_id = EPS.MedPersonal_pid and MP.Lpu_id = EPS.Lpu_id
			left join v_LpuSection pLS on pLS.LpuSection_id = EPS.LpuSection_pid
			left join YesNo IsImperHosp on IsImperHosp.YesNo_id = EPS.EvnPS_IsImperHosp
			left join YesNo IsShortVolume on IsShortVolume.YesNo_id = EPS.EvnPS_IsShortVolume
			left join YesNo IsWrongCure on IsWrongCure.YesNo_id = EPS.EvnPS_IsWrongCure
			left join YesNo IsDiagMismatch on IsDiagMismatch.YesNo_id = EPS.EvnPS_IsDiagMismatch
		";
	}

	public static function selectBody_KvsEvnStick_KvsEvnSection($getLpuIdFilterString)
	{
		return "
			inner join v_EvnSection as ESEC on ESEC.EvnSection_pid = EPS.EvnPS_id and ESEC.Lpu_id {$getLpuIdFilterString}
			left join v_LpuSection LS on LS.LpuSection_id = ESEC.LpuSection_id
			inner join v_LpuSectionProfile LSProf on LSProf.LpuSectionProfile_id = LS.LpuSectionProfile_id and LSProf.LpuSectionProfile_SysNick <> 'priem'
			left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
			left join v_LpuUnitType LUT on LUT.LpuUnitType_id = LU.LpuUnitType_id
			left join lateral(
				select *
				from v_MedPersonal
				where MedPersonal_id = ESEC.MedPersonal_id and Lpu_id = EPS.Lpu_id
				limit 1
			) as MP on true
			left join Diag Diag on Diag.Diag_id = ESEC.Diag_id
			left join PayType PT on PT.PayType_id = ESEC.PayType_id
			left join TariffClass TC on TC.TariffClass_id = ESEC.TariffClass_id
			left join v_MesOld Mes on Mes.Mes_id = ESEC.Mes_id
		";
	}

	public static function selectBody_KvsEvnStick_KvsNarrowBed($getLpuIdFilterString)
	{
		return "
			inner join v_EvnSection as ESEC on ESEC.EvnSection_pid = EPS.EvnPS_id and ESEC.Lpu_id {$getLpuIdFilterString}
			inner join v_EvnSectionNarrowBed as ESNB on ESNB.EvnSectionNarrowBed_pid = ESEC.EvnSection_id and ESNB.Lpu_id {$getLpuIdFilterString}
			left join LpuSection LS on LS.LpuSection_id = ESNB.LpuSection_id
		";
	}

	public static function selectBody_KvsEvnStick_KvsEvnUsluga($getLpuIdFilterString)
	{
		return "
			inner join v_EvnUsluga as EU on EU.EvnUsluga_rid = EPS.EvnPS_id and EU.Lpu_id {$getLpuIdFilterString}
			left join v_EvnUslugaOper EUO on EUO.EvnUslugaOper_id = EU.EvnUsluga_id
			left join OperType OT on OT.OperType_id = EUO.OperType_id
			left join OperDiff OD on OD.OperDiff_id = EUO.OperDiff_id
			left join YesNo IsEndoskop on IsEndoskop.YesNo_id = EUO.EvnUslugaOper_IsEndoskop
			left join YesNo IsLazer on IsLazer.YesNo_id = EUO.EvnUslugaOper_IsLazer
			left join YesNo IsKriogen on IsKriogen.YesNo_id = EUO.EvnUslugaOper_IsKriogen
			left join v_PayType as PT on PT.PayType_id = EU.PayType_id
			left join v_UslugaComplex as U on U.UslugaComplex_id = EU.UslugaComplex_id
			left join v_UslugaPlace as UP on UP.UslugaPlace_id = EU.UslugaPlace_id
			left join lateral(
				select MedPersonal_TabCode, Person_Fio
				from v_MedPersonal
				where MedPersonal_id = EU.MedPersonal_id and Lpu_id = EPS.Lpu_id
				limit 1
			) MP on true
			left join v_LpuSection LS on LS.LpuSection_id = EU.LpuSection_uid
			left join v_Lpu Lpu on Lpu.Lpu_id = EU.Lpu_uid
			left join v_Org Org on Org.Org_id = EU.Org_uid
			left join v_UslugaClass UC on UC.UslugaClass_SysNick = EU.EvnClass_SysNick
		";
	}

	public static function selectBody_KvsEvnStick_KvsEvnUslugaOB($getLpuIdFilterString)
	{
		return "
			left join v_EvnUsluga as EU on EU.EvnUsluga_rid = EPS.EvnPS_id and EU.Lpu_id {$getLpuIdFilterString}
			left join v_EvnUslugaOper EUO on EUO.EvnUslugaOper_id = EU.EvnUsluga_id
			inner join v_EvnUslugaOperBrig EUOB on EUOB.EvnUslugaOper_id = EUO.EvnUslugaOper_id
			left join v_MedPersonal MP on MP.MedPersonal_id = EUOB.MedPersonal_id and MP.Lpu_id = EPS.Lpu_id
			left join v_SurgType ST on ST.SurgType_id = EUOB.SurgType_id
		";
	}

	public static function selectBody_KvsEvnStick_KvsEvnUslugaAn($getLpuIdFilterString)
	{
		return "
			left join v_EvnUsluga as EU on EU.EvnUsluga_rid = EPS.EvnPS_id and EU.Lpu_id {$getLpuIdFilterString}
			left join v_EvnUslugaOper EUO on EUO.EvnUslugaOper_id = EU.EvnUsluga_id
			inner join v_EvnUslugaOperAnest EUOA on EUOA.EvnUslugaOper_id = EUO.EvnUslugaOper_id
			left join v_AnesthesiaClass AC on AC.AnesthesiaClass_id = EUOA.AnesthesiaClass_id
		";
	}

	public static function selectBody_KvsEvnStick_KvsEvnUslugaOsl($getLpuIdFilterString)
	{
		return "
			left join v_EvnUsluga as EU on EU.EvnUsluga_rid = EPS.EvnPS_id and EU.Lpu_id {$getLpuIdFilterString}
			inner join v_EvnAgg EA on EA.EvnAgg_pid = EU.EvnUsluga_id and EA.Lpu_id {$getLpuIdFilterString}
			left join v_AggType AT on AT.AggType_id = EA.AggType_id
			left join v_AggWhen AW on AW.AggWhen_id = EA.AggWhen_id
		";
	}

	public static function selectBody_KvsEvnStick_KvsEvnDrug($getLpuIdFilterString)
	{
		return "
			inner join v_EvnDrug as ED on ED.EvnDrug_rid = EPS.EvnPS_id and ED.Lpu_id {$getLpuIdFilterString}
			inner join v_DocumentUcOstat_Lite Part on Part.DocumentUcStr_id = ED.DocumentUcStr_oid
			inner join DocumentUcStr DUS on DUS.DocumentUcStr_id = ED.DocumentUcStr_id
			left join v_LpuSection LS on LS.LpuSection_id = ED.LpuSection_id
			left join rls.v_Drug Drug on Drug.Drug_id = ED.Drug_id
			left join v_Mol Mol on Mol.Mol_id = ED.Mol_id
		";
	}

	public static function selectBody_KvsEvnStick_KvsEvnLeave($getLpuIdFilterString)
	{
		return "
			inner join v_EvnSection as ESEC on ESEC.EvnSection_pid = EPS.EvnPS_id and ESEC.Lpu_id {$getLpuIdFilterString}
			left join v_EvnLeave ELV on ELV.EvnLeave_pid = ESEC.EvnSection_id and ESEC.LeaveType_id = 1 and ELV.Lpu_id {$getLpuIdFilterString}
			left join v_EvnOtherLpu EOLpu on EOLpu.EvnOtherLpu_pid = ESEC.EvnSection_id and ESEC.LeaveType_id = 2 and EOLpu.Lpu_id {$getLpuIdFilterString}
			left join v_EvnDie EDie on EDie.EvnDie_pid = ESEC.EvnSection_id and ESEC.LeaveType_id = 3 and EDie.Lpu_id {$getLpuIdFilterString}
			left join v_EvnOtherStac EOStac on EOStac.EvnOtherStac_pid = ESEC.EvnSection_id and ESEC.LeaveType_id = 4 and EOStac.Lpu_id {$getLpuIdFilterString}
			left join v_EvnOtherSection EOSect on EOSect.EvnOtherSection_pid = ESEC.EvnSection_id and ESEC.LeaveType_id = 5 and EOSect.Lpu_id {$getLpuIdFilterString}
			left join v_EvnOtherSectionBedProfile EOSectBP on EOSectBP.EvnOtherSectionBedProfile_pid = ESEC.EvnSection_id and ESEC.LeaveType_id = 6 and EOSectBP.Lpu_id {$getLpuIdFilterString}
			inner join v_LeaveType LType on LType.LeaveType_id = ESEC.LeaveType_id 
				and (
					ELV.EvnLeave_pid = ESEC.EvnSection_id or
					EOLpu.EvnOtherLpu_pid = ESEC.EvnSection_id or
					EDie.EvnDie_pid = ESEC.EvnSection_id or
					EOStac.EvnOtherStac_pid = ESEC.EvnSection_id or
					EOSect.EvnOtherSection_pid = ESEC.EvnSection_id or
					EOSectBP.EvnOtherSectionBedProfile_pid = ESEC.EvnSection_id
				)	
			left join ResultDesease RD on (
				RD.ResultDesease_id = ELV.ResultDesease_id or
				RD.ResultDesease_id = EOLpu.ResultDesease_id or
				RD.ResultDesease_id = EOStac.ResultDesease_id or
				RD.ResultDesease_id = EOSect.ResultDesease_id
			)
			left join LeaveCause LC on (
				LC.LeaveCause_id = ELV.LeaveCause_id or
				LC.LeaveCause_id = EOLpu.LeaveCause_id or
				LC.LeaveCause_id = EOStac.LeaveCause_id or
				LC.LeaveCause_id = EOSect.LeaveCause_id
			)
			left join YesNo IsAmbul on IsAmbul.YesNo_id = ELV.EvnLeave_IsAmbul
			left join v_Org EOLpuL on EOLpuL.Org_id = EOLpu.Org_oid
			left join v_LpuUnitType EOStacLUT on EOStacLUT.LpuUnitType_id = EOStac.LpuUnitType_oid
			left join v_LpuSection LS on (
				LS.LpuSection_id = EOStac.LpuSection_oid or
				LS.LpuSection_id = EOSect.LpuSection_oid or
				LS.LpuSection_id = EOSectBP.LpuSection_oid
			)
			left join v_MedPersonal MP on MP.MedPersonal_id = EDie.MedPersonal_aid and MP.Lpu_id = EPS.Lpu_id
			left join Diag DieDiag on DieDiag.Diag_id = EDie.Diag_aid
		";
	}

	public static function selectBody_KvsEvnStick_KvsEvnStick($getLpuIdFilterString)
	{
		return "
			inner join v_EvnStick EST on EST.EvnStick_pid = EPS.EvnPS_id and EST.Lpu_id {$getLpuIdFilterString}
			left join StickOrder SO on SO.StickOrder_id = EST.StickOrder_id
			left join StickCause SC on SC.StickCause_id = EST.StickCause_id
			left join Sex on Sex.Sex_id = EST.Sex_id
			left join StickRegime SR on SR.StickRegime_id = EST.StickRegime_id
			left join StickLeaveType SLT on SLT.StickLeaveType_id = EST.StickLeaveType_id
			left join v_MedPersonal MP on MP.MedPersonal_id = EST.MedPersonal_id and MP.Lpu_id = EST.Lpu_id
			left join v_Lpu Lpu on Lpu.Lpu_id = EST.Lpu_id
			left join Diag D1 on D1.Diag_id = EST.Diag_pid
		";
	}

	public static function selectBody_KvsEvnStick_NoSearchFormTypeSwitch($prefix, $data)
	{
		$query = "";
		if (isset($data["and_kvsperson"]) && $data["and_kvsperson"]) {
			if ($prefix == "PS2") {
				$query .= ($data["kvs_date_type"] == 2)
					? " left join v_Person_all PS2 on PS2.Server_id = EPS.Server_id and PS2.PersonEvn_id = EPS.PersonEvn_id "
					: " left join v_PersonState PS2 on PS2.Person_id = EPS.Person_id ";
			}
			$query .= "
				left join Sex PrsSex on PrsSex.Sex_id = {$prefix}.Sex_id
				left join SocStatus PrsSoc on PrsSoc.SocStatus_id = {$prefix}.SocStatus_id
				left join PersonChild PrsPCh on PrsPCh.Person_id = {$prefix}.Person_id
				left join YesNo PrsIsInv on PrsIsInv.YesNo_id = PrsPCh.PersonChild_IsInvalid
				left join Diag PrsInvD on PrsInvD.Diag_id = PrsPCh.Diag_id
				left join Polis PrsPol on PrsPol.Polis_id = {$prefix}.Polis_id
				left join OMSSprTerr PrsOMSST on PrsOMSST.OMSSprTerr_id = PrsPol.OMSSprTerr_id
				left join PolisType PrsPolTp on PrsPolTp.PolisType_id = PrsPol.PolisType_id
				left join v_OrgSmo PrsOS on PrsOS.OrgSmo_id = PrsPol.OrgSmo_id
				left join v_Org PrsOSO on PrsOSO.Org_id = PrsOS.Org_id
				left join v_Address_all PrsUA on PrsUA.Address_id = {$prefix}.UAddress_id 
				left join v_Address_all PrsPA on PrsPA.Address_id = {$prefix}.PAddress_id
				left join Document PrsDoc on PrsDoc.Document_id = {$prefix}.Document_id 
				left join DocumentType PrsDocTp on PrsDocTp.DocumentType_id = PrsDoc.DocumentType_id
				left join v_OrgDep PrsOrgD on PrsOrgD.OrgDep_id = PrsDoc.OrgDep_id
			";
		}
		return $query;
	}

	public static function selectBody_KvsEvnStick_EvnPSNoDbf(Search_model $callObject, $data, $getLpuIdFilterString)
	{
		$code = ($callObject->regionNick == "kz")
			? "
				left join r101.v_EvnPSLink epsl on epsl.EvnPS_id = EPS.EvnPS_id
				left join ObjectSynchronLog objsync on EPS.EvnPS_id = objsync.Object_id
			"
			: "";
		$query = "
			left join v_EvnCostPrint ecp on ecp.Evn_id = EPS.EvnPS_id
			left join v_EvnSection EPSLastES on EPSLastES.EvnSection_pid = EPS.EvnPS_id and EPSLastES.EvnSection_Index = EPSLastES.EvnSection_Count-1 and EPSLastES.Lpu_id {$getLpuIdFilterString}
			left join LpuSection LStmp on LStmp.LpuSection_id = EPSLastES.LpuSection_id
			left join lateral (select * from v_Diag Dtmp where Dtmp.Diag_id = EPSLastES.Diag_id limit 1) Dtmp on true
			left join LeaveType LT on LT.LeaveType_id = coalesce(EPSLastES.LeaveType_id, EPSLastES.LeaveType_prmid)
			left join lateral(
				select *
				from v_MedPersonal
				where MedPersonal_id = EPSLastES.MedPersonal_id and Lpu_id {$getLpuIdFilterString}
				order by case when Lpu_id = :Lpu_id then 1 else 2 end
				limit 1
			) as MP on true
			left join lateral (
				select *
				from v_Diag DP
				where DP.Diag_id = EPS.Diag_pid
				limit 1
			) as dp on true
			left join LpuSection LS on LS.LpuSection_id = EPS.LpuSection_id
			left join v_PrehospWaifRefuseCause pwrc on pwrc.PrehospWaifRefuseCause_id = EPS.PrehospWaifRefuseCause_id
			left join LpuUnit on LpuUnit.LpuUnit_id = LStmp.LpuUnit_id 
			left join LpuUnitType on LpuUnitType.LpuUnitType_id = LpuUnit.LpuUnitType_id 
			left join PayType dbfpayt on dbfpayt.PayType_id = EPS.PayType_id
			left join v_Polis pls on pls.Polis_id = ps.Polis_id
			left join lateral(
				select EvnDie_setDate
				from v_EvnDie
				where Person_id = PS.Person_id
				order by EvnDie_setDate
				limit 1
			) as EvnDie on true
			left join lateral(
				select
					DeathSvid_id,
					DeathSvid_DeathDate 
				from dbo.v_DeathSvid 
				where Person_id = PS.Person_id and (DeathSvid_IsBad is null or DeathSvid_IsBad = 1)
				limit 1
			) as DeathSvid on true
			left join v_MesTariff spmt on EPSLastES.MesTariff_id = spmt.MesTariff_id
			left join v_MesOld as sksg on sksg.Mes_id = EPSLastES.Mes_sid
			left join v_MesOld as ksg on ksg.Mes_id = case
				when spmt.Mes_id in (EPSLastES.Mes_sid, EPSLastES.Mes_tid) then spmt.Mes_id
				else coalesce(EPSLastES.Mes_sid, EPSLastES.Mes_tid)
			end
			left join v_MesOld as ksgkpg on spmt.Mes_id = ksgkpg.Mes_id
			left join v_MesOld as kpg on kpg.Mes_id = EPSLastES.Mes_kid
			{$code}
		";
		if(!empty($data['EvnReanimatPeriod_setDate']) || !empty($data['EvnReanimatPeriod_disDate'])){
			$query .= "
			left join v_EvnReanimatPeriod ERP on ERP.EvnReanimatPeriod_rid = EPS.EvnPS_id
			";
		}
		$acDiagFilter = getRevertAccessRightsDiagFilter("ESSDiag.Diag_Code");
		if (!empty($acDiagFilter)) {
			$accessDiagFilter = "
				left join lateral(
					select ESS.Evn_id AS EvnSection_id
					from
						EvnSection ESS
						inner join Diag ESSDiag on ESSDiag.Diag_id = ESS.Diag_id
					where ESS.Evn_pid = EPS.EvnPS_id
					  and ESS.Lpu_id {$getLpuIdFilterString}
			          and ($acDiagFilter)
			        limit 1
				) adf on true
			";
			$query .= $accessDiagFilter;
		}
		return $query;
	}

	public static function selectBody_KvsEvnStick_EvnSectionNoDbf($getLpuIdFilterString)
	{
		$query = "
			inner join v_EvnSection as ESEC on ESEC.EvnSection_pid = EPS.EvnPS_id and ESEC.Lpu_id {$getLpuIdFilterString}
			left join v_EvnCostPrint ecp on ecp.Evn_id = EPS.EvnPS_id
			left join v_Diag Dtmp on Dtmp.Diag_id = ESEC.Diag_id
			left join LpuSection LS on LS.LpuSection_id = ESEC.LpuSection_id
			inner join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
			inner join LpuUnitType LUT on LUT.LpuUnitType_id = LU.LpuUnitType_id
			left join LpuSectionWard LSW on LSW.LpuSectionWard_id = ESEC.LpuSectionWard_id
			left join v_Polis pls on pls.Polis_id = ps.Polis_id
			left join PayType as PT on PT.PayType_id = ESEC.PayType_id
			left join lateral(
				select Person_Fio
				from v_MedPersonal
				where MedPersonal_id = ESEC.MedPersonal_id
				limit 1
			) as MP on true
			left join v_MesOld as MES on MES.Mes_id = ESEC.Mes_id
			left join LeaveType LT on LT.LeaveType_id = ESEC.LeaveType_id
			left join v_MesTariff spmt on ESEC.MesTariff_id = spmt.MesTariff_id
			left join v_MesOld as sksg on sksg.Mes_id = ESEC.Mes_sid
			left join v_MesOld as ksg on ksg.Mes_id = case
				when spmt.Mes_id in (ESEC.Mes_sid, ESEC.Mes_tid) then spmt.Mes_id
				else coalesce(ESEC.Mes_sid, ESEC.Mes_tid)
			end
			left join v_MesOld as ksgkpg on spmt.Mes_id = ksgkpg.Mes_id
			left join v_MesOld as kpg on kpg.Mes_id = ESEC.Mes_kid
		";
		$acDiagFilter = getRevertAccessRightsDiagFilter("ESSDiag.Diag_Code");
		if (!empty($acDiagFilter)) {
			$accessDiagFilter = "
				left join lateral(
					select ESS.EvnSection_id
					from
						v_EvnSection ESS
						inner join v_Diag ESSDiag on ESSDiag.Diag_id = ESS.Diag_id
					where ESS.EvnSection_pid = EPS.EvnPS_id
					  and ESS.Lpu_id {$getLpuIdFilterString}
			          and ($acDiagFilter)
			        limit 1
				) adf on true
			";
			$query .= $accessDiagFilter;
		}
		return $query;
	}

	public static function selectBody_KvsEvnStick_NoDbfNoSwitch()
	{
		return "
			left join lateral(
				select
					PersonEvn_id,
					Server_id
				from v_PersonState
				where Person_id = PS.Person_id
				limit 1
			) as CPS on true
		";
	}

	public static function selectBody_KvsEvnStick_LpuUnitType_did($getLpuIdFilterString)
	{
		return "
			left join lateral(
				select LU_tmp.LpuUnitType_id
				from
					v_EvnSection ES_tmp
					inner join LpuSection LS_tmp on LS_tmp.LpuSection_id = ES_tmp.LpuSection_id
					inner join LpuUnit LU_tmp on LU_tmp.LpuUnit_id = LS_tmp.LpuUnit_id
				where ES_tmp.EvnSection_rid = EPS.EvnPS_id
				  and ES_tmp.Lpu_id {$getLpuIdFilterString}
				  and coalesce(ES_tmp.EvnSection_IsPriem, 1) = 1
				order by ES_tmp.EvnSection_setDT
				limit 1
			) as ESHosp on true
		";
	}

   public static function selectBody_KvsEvnStick_common(Search_model $callObject, $data, $getLpuIdFilterString)
	{
		$query = "";
		if (!empty($data["EvnSection_isPaid"]) && $data["SearchFormType"] == "EvnPS" && $callObject->getRegionNick() != "ekb") {
			$code = (in_array($callObject->getRegionNick(), $callObject->EvnPS_model->getListRegionNickWithEvnSectionPriem()))
				? "and (EvnSection_Count = 1 or coalesce(EvnSection_IsPriem, 1) = 1)"
				: "";
			$query .= "
				left join lateral(
					select 1 as EvnSection_isPaid
					from v_EvnSection
					where EvnSection_pid = EPS.EvnPS_id
					  {$code}
					  and coalesce(EvnSection_isPaid, 1) = 1
					limit 1
				) as ESpaid on true
			";
		}
		if (
			empty($data["EvnLeave_IsNotSet"]) &&
			(
				isset($data["LeaveCause_id"]) ||
				isset($data["ResultDesease_id"]) ||
				isset($data["Org_oid"]) ||
				isset($data["LpuUnitType_oid"]) ||
				isset($data["LpuSection_oid"]) ||
				isset($data["EvnLeaveBase_UKL"]) ||
				isset($data["EvnLeave_IsAmbul"]) ||
				isset($data["EvnDie_IsAnatom"])
			)
		) {
			switch ($data["SearchFormType"]) {
				case "EvnPS":
					$code = ($callObject->regionNick == "khak")
						? "'leave', 'ksleave', 'dsleave', 'inicpac', 'ksinicpac', 'ksiniclpu', 'iniclpu', 'ksprerv', 'prerv', 'ksprod', "
						: "";
					$query .= "
						left join v_EvnLeave EL on EL.EvnLeave_pid = EPSLastES.EvnSection_id and LT.LeaveType_SysNick in ('leave', 'ksleave', 'dsleave', 'inicpac', 'ksinicpac', 'ksiniclpu', 'iniclpu', 'ksprerv', 'prerv', 'ksprod') and EL.Lpu_id {$getLpuIdFilterString}
						left join v_EvnOtherLpu EOL on EOL.EvnOtherLpu_pid = EPSLastES.EvnSection_id and LT.LeaveType_SysNick in ('other', 'dsother', 'ksother', 'ksperitar') and EOL.Lpu_id {$getLpuIdFilterString}
						left join v_EvnDie ED on ED.EvnDie_pid = EPSLastES.EvnSection_id and LT.LeaveType_SysNick in ({$code}'die', 'ksdie', 'ksdiepp', 'diepp', 'dsdie', 'dsdiepp', 'kslet', 'ksletitar') and ED.Lpu_id {$getLpuIdFilterString}
						left join v_EvnOtherStac EOST on EOST.EvnOtherStac_pid = EPSLastES.EvnSection_id and LT.LeaveType_SysNick in ('stac', 'ksstac', 'dsstac') and EOST.Lpu_id {$getLpuIdFilterString}
						left join v_EvnOtherSection EOS on EOS.EvnOtherSection_pid = EPSLastES.EvnSection_id and LT.LeaveType_SysNick in ('section', 'dstac', 'kstac') and EOS.Lpu_id {$getLpuIdFilterString}
						left join v_EvnOtherSectionBedProfile EOSBP on EOSBP.EvnOtherSectionBedProfile_pid = EPSLastES.EvnSection_id and LT.LeaveType_SysNick in ('ksper', 'dsper') and EOSBP.Lpu_id {$getLpuIdFilterString}
					";
					break;
				case "EvnSection":
					$code = ($callObject->regionNick == "khak")
						? "'leave', 'ksleave', 'dsleave', 'inicpac', 'ksinicpac', 'ksiniclpu', 'iniclpu', 'ksprerv', 'prerv', 'ksprod', "
						: "";
					$query .= "
						left join v_EvnLeave EL on EL.EvnLeave_pid = ESEC.EvnSection_id and LT.LeaveType_SysNick in ('leave', 'ksleave', 'dsleave', 'ksinicpac', 'inicpac', 'ksiniclpu', 'iniclpu', 'ksprerv', 'prerv', 'ksprod')
						left join v_EvnOtherLpu EOL on EOL.EvnOtherLpu_pid = ESEC.EvnSection_id and LT.LeaveType_SysNick in ('other', 'dsother', 'ksother', 'ksperitar')
						left join v_EvnDie ED on ED.EvnDie_pid = ESEC.EvnSection_id and LT.LeaveType_SysNick in ({$code}'die', 'ksdie', 'ksdiepp', 'diepp', 'dsdie', 'dsdiepp', 'kslet', 'ksletitar')
						left join v_EvnOtherStac EOST on EOST.EvnOtherStac_pid = ESEC.EvnSection_id and LT.LeaveType_SysNick in ('stac', 'ksstac', 'dsstac')
						left join v_EvnOtherSection EOS on EOS.EvnOtherSection_pid = ESEC.EvnSection_id and LT.LeaveType_SysNick in ('section', 'dstac', 'kstac')
						left join v_EvnOtherSectionBedProfile EOSBP on EOSBP.EvnOtherSectionBedProfile_pid = ESEC.EvnSection_id and LT.LeaveType_SysNick in ('ksper', 'dsper')
					";
					break;
			}
		}
		$query .= "
			left join lateral(
				select ServiceEvnStatus_id
				from v_ServiceEvnHist
				where Evn_id = EPS.EvnPS_id
				  and ServiceEvnList_id = 1
				order by ServiceEvnHist_id desc
				limit 1
			) as SEH1 on true
			left join v_ServiceEvnStatus SES1 on SES1.ServiceEvnStatus_id = SEH1.ServiceEvnStatus_id
		";
		return $query;
	}

	public static function selectBody_EvnAggStom(Search_model $callObject, $dbf, $prefix, $data, $getLpuIdFilter)
	{
		$query = " left join v_Polis pls on pls.Polis_id = ps.Polis_id ";
		$query .= ($data["PersonPeriodicType_id"] == 2)
			? (("EvnVizitPLStom" == $data["SearchFormType"])
				? "
					inner join v_EvnVizitPLStom as EVPLS on EVPLS.Server_id = PS.Server_id and EVPLS.PersonEvn_id = PS.PersonEvn_id and EVPLS.Lpu_id {$getLpuIdFilter}
					inner join v_EvnPLStom EPLS on EPLS.EvnPLStom_id = EVPLS.EvnVizitPLStom_pid and EPLS.Lpu_id {$getLpuIdFilter} and EPLS.EvnClass_id = 6
				"
				: " inner join v_EvnPLStom EPLS on EPLS.Server_id = PS.Server_id and EPLS.PersonEvn_id = PS.PersonEvn_id and EPLS.Lpu_id {$getLpuIdFilter} and EPLS.EvnClass_id = 6")
			: (("EvnVizitPLStom" == $data["SearchFormType"])
				? "
					inner join v_EvnVizitPLStom as EVPLS on EVPLS.Person_id = PS.Person_id and EVPLS.Lpu_id {$getLpuIdFilter}
					inner join v_EvnPLStom EPLS on EPLS.EvnPLStom_id = EVPLS.EvnVizitPLStom_pid and EPLS.Lpu_id {$getLpuIdFilter} and EPLS.EvnClass_id = 6
				"
				: " inner join v_EvnPLStom EPLS on EPLS.Person_id = PS.Person_id and EPLS.Lpu_id {$getLpuIdFilter} and EPLS.EvnClass_id = 6");
		if ($dbf !== true) {
			if ("EvnPLStom" == $data["SearchFormType"]) {
				$query .= "
					left join v_EvnVizitPLStom EVPLS on EVPLS.EvnVizitPLStom_pid = EPLS.EvnPLStom_id and EVPLS.EvnVizitPLStom_Index = EVPLS.EvnVizitPLStom_Count - 1 and EVPLS.Lpu_id {$getLpuIdFilter}
					left join YesNo IsFinish on IsFinish.YesNo_id = EPLS.EvnPLStom_IsFinish
					left join v_Diag EVPLSD on EVPLSD.Diag_id = coalesce(EPLS.Diag_id, EVPLS.Diag_id)
					left join v_MedStaffFact MSF on MSF.MedStaffFact_id = EVPLS.MedStaffFact_id
					left join v_Address ua on ua.Address_id = PS.UAddress_id
					left join v_Address pa on pa.Address_id = PS.PAddress_id
					left join lateral(
						select count(EvnVizitPLStom_id) as EvnPLStom_VizitCount
						from v_EvnVizitPLStom
						where EvnVizitPLStom_pid = EPLS.EvnPLStom_id
					) as CNT on true
				";
				if ($callObject->regionNick == "kz") {
					$query .= "
						left join r101.AISResponse air on air.Evn_id = EPLS.EvnPLStom_id and air.AISFormLoad_id = 1
						left join r101.AISResponse air9 on air9.Evn_id = EPLS.EvnPLStom_id and air9.AISFormLoad_id = 2
						left join lateral(
							select eu.UslugaComplex_id
							from
								v_EvnUsluga_all eu
								inner join r101.AISUslugaComplexLink ucl on ucl.UslugaComplex_id = eu.UslugaComplex_id
							where eu.EvnUsluga_rid = EPLS.EvnPLStom_id and ucl.AISFormLoad_id = 1
							limit 1
						) as euais on true
						left join lateral(
							select eu.UslugaComplex_id
							from
								v_EvnUsluga_all eu
								inner join r101.AISUslugaComplexLink ucl on ucl.UslugaComplex_id = eu.UslugaComplex_id
							where eu.EvnUsluga_rid = EPLS.EvnPLStom_id and ucl.AISFormLoad_id = 1
							limit 1
						) as euais9 on true
					";
				}
			}
			if ("EvnVizitPLStom" == $data["SearchFormType"]) {
				$query .= "
					left join v_LpuSection as evplls on evplls.LpuSection_id=EVPLS.LpuSection_id
					left join v_PayType as evplpt on evplpt.PayType_id=EVPLS.PayType_id
					left join v_VizitType as evplvt on evplvt.VizitType_id=EVPLS.VizitType_id
					left join v_ServiceType as evplst on evplst.ServiceType_id=EVPLS.ServiceType_id
					left join v_Diag as evpldiag on evpldiag.Diag_id = EVPLS.Diag_id
				";
			}
			$query .= " left join v_EvnCostPrint ecp on ecp.Evn_id = EPLS.EvnPLStom_id ";
		} else {
			switch ($data["SearchFormType"]) {
				case "EPLStomPerson":
					$query .= " inner join v_EvnPLStom EPLS2 on EPLS2.EvnPLStom_id = EPLS.EvnPLStom_id ";
					if ($prefix == "PS2") {
						$query .= ($data["eplstom_date_type"] == 2)
							? " inner join v_Person_all PS2 on PS2.Server_id = EPLS2.Server_id and PS2.PersonEvn_id = EPLS2.PersonEvn_id "
							: " inner join v_PersonState PS2 on PS2.Person_id = EPLS.Person_id ";
					}
					$query .= "
						left join Sex on Sex.Sex_id = {$prefix}.Sex_id
						left join SocStatus Soc on Soc.SocStatus_id = {$prefix}.SocStatus_id
						left join PersonChild PCh on PCh.Person_id = {$prefix}.Person_id
						left join YesNo IsInv on IsInv.YesNo_id = PCh.PersonChild_IsInvalid
						left join Diag InvD on InvD.Diag_id = PCh.Diag_id
						left join Polis Pol on Pol.Polis_id = {$prefix}.Polis_id
						left join OMSSprTerr OMSST on OMSST.OMSSprTerr_id = Pol.OMSSprTerr_id
						left join PolisType PolTp on PolTp.PolisType_id = Pol.PolisType_id
						left join v_OrgSmo OS on OS.OrgSmo_id = Pol.OrgSmo_id
						left join v_Org OSO on OSO.Org_id = OS.Org_id
						left join v_Address_all UA on UA.Address_id = {$prefix}.UAddress_id 
						left join v_Address_all PA on PA.Address_id = {$prefix}.PAddress_id
						left join Document Doc on Doc.Document_id = {$prefix}.Document_id 
						left join DocumentType DocTp on DocTp.DocumentType_id = Doc.DocumentType_id
						left join v_OrgDep OrgD on OrgD.OrgDep_id = Doc.OrgDep_id
					";
					break;
				case "EvnAggStom":
					$query .= "
						inner join v_EvnUslugaStom EvnUsluga on EvnUsluga.EvnUslugaStom_rid = EPLS.EvnPLStom_id and EvnUsluga.Lpu_id {$getLpuIdFilter} 
						inner join v_EvnAgg EvnAgg on EvnAgg.EvnAgg_pid = EvnUsluga.EvnUslugaStom_id and EvnAgg.Lpu_id {$getLpuIdFilter}
						left join AggType as dbfat on dbfat.AggType_id = EvnAgg.AggType_id
						left join AggWhen as dbfaw on dbfaw.AggWhen_id = EvnAgg.AggWhen_id
					";
					if (!in_array($data["PersonPeriodicType_id"], [2]) && isset($data["Refuse_id"])) {
						$query .= "
							inner join v_PersonState_all PS on Evn.Person_id = PS.Person_id
						";
					}
					break;
				case "EvnPLStom":
					$query .= "
						left join v_Diag as dbfdiag on dbfdiag.Diag_id = EPLS.Diag_id
						left join v_Lpu as dbflpu on dbflpu.Lpu_id = EPLS.Lpu_id
						left join ResultClass dbfrc on dbfrc.ResultClass_id = EPLS.ResultClass_id
						left join DeseaseType dbfdt on dbfdt.DeseaseType_id = EPLS.DeseaseType_id
						left join PrehospDirect dbfpd on dbfpd.PrehospDirect_id = EPLS.PrehospDirect_id
						left join v_Lpu dbfprehosplpu on dbfprehosplpu.Lpu_id = EPLS.Lpu_did
						left join LpuSection dbflsd on dbflsd.LpuSection_id = EPLS.LpuSection_did
						left join YesNo dbfift on dbfift.YesNo_id = EPLS.EvnPLStom_IsFirstTime
						left join DirectClass dbfdc on dbfdc.DirectClass_id = EPLS.DirectClass_id
						left join v_Lpu dbflpudir on dbflpudir.Lpu_id = EPLS.Lpu_oid
						left join LpuSection dbflsdir on dbflsdir.LpuSection_id = EPLS.LpuSection_oid
						left join lateral(
							select
								YesNo.YesNo_Code as PersonChild_IsInvalid_Code,
								PersonSprTerrDop.PersonSprTerrDop_Code as PermRegion_Code
							from PersonChild
								left join YesNo on YesNo.YesNo_id = PersonChild.PersonChild_IsInvalid
								left join PersonSprTerrDop on PersonSprTerrDop.PersonSprTerrDop_id = PersonChild.PersonSprTerrDop_id
							where PersonChild.Person_id = EPLS.Person_id
							order by PersonChild.PersonChild_insDT desc
							limit 1
						) dbfinv on true
					";
					if (in_array($data["PersonPeriodicType_id"], [2])) {
						$query .= "
							left join SocStatus as dbfss on dbfss.SocStatus_id = PS.SocStatus_id
							left join Sex dbfsex on dbfsex.Sex_id = PS.Sex_id
							left join Address dbfaddr on dbfaddr.Address_id = coalesce(PS.PAddress_id, PS.UAddress_id)
							left join KLStreet dbfkls on dbfkls.KLStreet_id = dbfaddr.KLStreet_id
							left join KLArea dbfkla on dbfkla.KLArea_id = COALESCE(dbfaddr.KLTown_id, dbfaddr.KLCity_id, dbfaddr.KLSubRgn_id, dbfaddr.KLRgn_id) and dbfkls.KLStreet_id is null
						";
					} else {
						if (!isset($data["Refuse_id"])) {
							$query .= "
								left join SocStatus as dbfss on dbfss.SocStatus_id = PS.SocStatus_id
								left join Sex dbfsex on dbfsex.Sex_id = PS.Sex_id
								left join Address dbfaddr on dbfaddr.Address_id = coalesce(PS.PAddress_id, PS.UAddress_id)
								left join KLStreet dbfkls on dbfkls.KLStreet_id = dbfaddr.KLStreet_id
								left join KLArea dbfkla on dbfkla.KLArea_id = COALESCE(dbfaddr.KLTown_id, dbfaddr.KLCity_id, dbfaddr.KLSubRgn_id, dbfaddr.KLRgn_id) and dbfkls.KLStreet_id is null
							";
						} else {
							$query .= "
								left join v_Polis pls on pls.Polis_id = ps.Polis_id
								left join SocStatus as dbfss on dbfss.SocStatus_id = PS.SocStatus_id
								left join Sex dbfsex on dbfsex.Sex_id = PS.Sex_id
								left join Address dbfaddr on dbfaddr.Address_id = coalesce(PS.PAddress_id, PS.UAddress_id)
								left join KLStreet dbfkls on dbfkls.KLStreet_id = dbfaddr.KLStreet_id
								left join KLArea dbfkla on dbfkla.KLArea_id = COALESCE(dbfaddr.KLTown_id, dbfaddr.KLCity_id, dbfaddr.KLSubRgn_id, dbfaddr.KLRgn_id) and dbfkls.KLStreet_id is null
							";
						}
					}
					break;
				case "EvnUslugaStom":
					$query .= "
						inner join v_EvnUslugaStom EvnUsluga on EvnUsluga.EvnUslugaStom_rid = EPLS.EvnPLStom_id and EvnUsluga.Lpu_id {$getLpuIdFilter}
						left join v_PayType as dbfpt on dbfpt.PayType_id = EvnUsluga.PayType_id
						left join v_UslugaComplex as dbfusluga on dbfusluga.UslugaComplex_id = EvnUsluga.UslugaComplex_id
						left join v_UslugaPlace as dbfup on dbfup.UslugaPlace_id = EvnUsluga.UslugaPlace_id
						left join v_MedPersonal dbfmp on dbfmp.MedPersonal_id = EvnUsluga.MedPersonal_id
					";
					if (!in_array($data["PersonPeriodicType_id"], [2]) && isset($data["Refuse_id"])) {
						$query .= "
							inner join v_PersonState_all PS on Evn.Person_id = PS.Person_id
						";
					}
					break;
				case "EvnVizitPLStom":
					$query .= "
						left join v_Diag as dbfdiag on dbfdiag.Diag_id = EVPLS.Diag_id
						left join LpuSection as dbfls on dbfls.LpuSection_id = EVPLS.LpuSection_id
						left join v_MedPersonal as dbfmp on dbfmp.MedPersonal_id = EVPLS.MedPersonal_id
						left join PayType as dbfpt on dbfpt.PayType_id = EVPLS.PayType_id
						left join VizitClass as dbfvc on dbfvc.VizitClass_id = EVPLS.VizitClass_id
						left join VizitType as dbfvt on dbfvt.VizitType_id = EVPLS.VizitType_id
						left join DeseaseType as dbfdt on dbfdt.DeseaseType_id = EVPLS.DeseaseType_id
						left join ServiceType as dbfst on dbfst.ServiceType_id = EVPLS.ServiceType_id
						left join ProfGoal as dbfpg on dbfpg.ProfGoal_id = EVPLS.ProfGoal_id
					";
					if (!in_array($data["PersonPeriodicType_id"], [2]) && isset($data["Refuse_id"])) {
						$query .= "
							inner join v_PersonState_all PS on EVPLS.Person_id = PS.Person_id
							left join v_Polis pls on pls.Polis_id = ps.Polis_id
						";
					}
					break;
			}
			if (isset($data["and_eplstomperson"]) && $data["and_eplstomperson"]) {
				if ($prefix == "PS2") {
					$query .= " inner join v_EvnPLStom EPLS2 on EPLS2.EvnPLStom_id = EPLS.EvnPLStom_id ";
					$query .= ($data["eplstom_date_type"] == 2)
						? " left join v_Person_all PS2 on PS2.Server_id = EPLS2.Server_id and PS2.PersonEvn_id = EPLS2.PersonEvn_id "
						: " left join v_PersonState PS2 on PS2.Person_id = EPLS2.Person_id ";
				}
				$query .= "
					left join Sex PrsSex on PrsSex.Sex_id = {$prefix}.Sex_id
					left join SocStatus PrsSoc on PrsSoc.SocStatus_id = {$prefix}.SocStatus_id
					left join PersonChild PrsPCh on PrsPCh.Person_id = {$prefix}.Person_id
					left join YesNo PrsIsInv on PrsIsInv.YesNo_id = PrsPCh.PersonChild_IsInvalid
					left join Diag PrsInvD on PrsInvD.Diag_id = PrsPCh.Diag_id
					left join Polis PrsPol on PrsPol.Polis_id = {$prefix}.Polis_id
					left join OMSSprTerr PrsOMSST on PrsOMSST.OMSSprTerr_id = PrsPol.OMSSprTerr_id
					left join PolisType PrsPolTp on PrsPolTp.PolisType_id = PrsPol.PolisType_id
					left join v_OrgSmo PrsOS on PrsOS.OrgSmo_id = PrsPol.OrgSmo_id
					left join v_Org PrsOSO on PrsOSO.Org_id = PrsOS.Org_id
					left join v_Address_all PrsUA on PrsUA.Address_id = {$prefix}.UAddress_id 
					left join v_Address_all PrsPA on PrsPA.Address_id = {$prefix}.PAddress_id
					left join Document PrsDoc on PrsDoc.Document_id = {$prefix}.Document_id 
					left join DocumentType PrsDocTp on PrsDocTp.DocumentType_id = PrsDoc.DocumentType_id
					left join v_OrgDep PrsOrgD on PrsOrgD.OrgDep_id = PrsDoc.OrgDep_id
				";
			}
		}
		if ("EvnVizitPLStom" == $data["SearchFormType"]) {
			$mp_filter = "MedPersonal_id = EVPLS.MedPersonal_id";
		} else if ("EvnPLStom" == $data["SearchFormType"] && !$dbf) {
			$mp_filter = "MedPersonal_id = coalesce(EPLS.MedPersonal_id, EVPLS.MedPersonal_id, MSF.MedPersonal_id)";
		} else {
			$mp_filter = "MedPersonal_id = EPLS.MedPersonal_id";
		}
		$query .= "
			left join lateral(
				select *
				from v_MedPersonal
				where {$mp_filter}
				  and Lpu_id {$getLpuIdFilter}
				order by case when Lpu_id = :Lpu_id then 1 else 2 end
				limit 1
			) as MP on true
		";
		if (!empty($data["EvnVizitPLStom_isPaid"])) {
			switch ($data["SearchFormType"]) {
				case "EvnPLStom":
					switch ($callObject->getRegionNick()) {
						case "astra":
						case "kareliya":
						case "pskov":
							$query .= "
								left join lateral(
									select EvnVizitPLStom_isPaid
									from v_EvnVizitPLStom
									where EvnVizitPLStom_pid = EPLS.EvnPLStom_id and EvnVizitPLStom_Index = EvnVizitPLStom_Count - 1
									limit 1
								) as LEVPLS on true
							";
							break;
						case "penza":
						case "ufa":
						case "vologda":
						case "perm":
							$query .= "
								left join lateral(
									select EvnVizitPLStom_isPaid
									from v_EvnVizitPLStom
									where EvnVizitPLStom_pid = EPLS.EvnPLStom_id
										and coalesce(EvnVizitPLStom_isPaid, 1) = '2'
									limit 1
								) as EVPLSpaid on true
							";
							break;
					}
					break;
			}
		}

		return $query;
	}

	public static function selectBody_EvnVizitPL(Search_model $callObject, $dbf, $prefix, $data, $getLpuIdFilter)
	{
		$query = "";
		if ($data["PersonPeriodicType_id"] == 2) {
			$query .= ("EvnVizitPL" == $data["SearchFormType"])
				? "
					v_EvnVizitPL EVizitPL
					inner join Evn on Evn.Evn_id = EVizitPL.EvnVizitPL_pid and Evn.EvnClass_id = 3 and Evn.Evn_deleted = 1 and Evn.Lpu_id {$getLpuIdFilter}
					left join lateral(
						select
							EvnPLBase.EvnPLBase_IsFinish,
							EvnPLBase.EvnPLBase_VizitCount
						from v_EvnPLBase EvnPLBase
						where EvnPLBase.EvnPLBase_id = Evn.Evn_id
						limit 1
					) as EvnPLBase on true
					inner join v_EvnPL EPL on EPL.EvnPL_id = Evn.Evn_id
				"
				: "
					EvnClass
					inner join Evn on Evn.EvnClass_id = EvnClass.EvnClass_id and EvnClass.EvnClass_id = 3 and Evn.Evn_deleted = 1 and Evn.Lpu_id {$getLpuIdFilter}
					left join lateral(
						select
							EvnPLBase.EvnPLBase_IsFinish,
							EvnPLBase.EvnPLBase_VizitCount
						from v_EvnPLBase EvnPLBase
						where EvnPLBase.EvnPLBase_id = Evn.Evn_id
						limit 1
					) as EvnPLBase on true
					inner join v_EvnPL EPL on EPL.EvnPL_id = Evn.Evn_id
				";
		} else {
			$query .= ("EvnVizitPL" == $data["SearchFormType"])
				? "
					v_EvnVizitPL EVizitPL
					inner join Evn on Evn.Evn_id = EVizitPL.EvnVizitPL_pid and Evn.EvnClass_id = 3 and Evn.Evn_deleted = 1 and Evn.Lpu_id {$getLpuIdFilter}
					left join lateral(
						select
							EvnPLBase.EvnPLBase_IsFinish,
							EvnPLBase.EvnPLBase_VizitCount
						from v_EvnPLBase EvnPLBase
						where EvnPLBase.EvnPLBase_id = Evn.Evn_id
						limit 1
					) as EvnPLBase on true
					inner join v_EvnPL EPL on EPL.EvnPL_id = Evn.Evn_id
				"
				: "
					v_EvnPL EPL
					inner join Evn on Evn.Evn_id = EPL.EvnPL_id and Evn.EvnClass_id = 3 and Evn.Evn_deleted = 1 and Evn.Lpu_id {$getLpuIdFilter}
					left join lateral(
						select
							EvnPLBase.EvnPLBase_IsFinish,
							EvnPLBase.EvnPLBase_VizitCount
						from v_EvnPLBase EvnPLBase
						where EvnPLBase.EvnPLBase_id = Evn.Evn_id
						limit 1
					) as EvnPLBase on true
				";
		}
		if ($dbf !== true) {
			if ("EvnVizitPL" == $data["SearchFormType"]) {
				$query .= "
					left join v_Diag as evpldiag on evpldiag.Diag_id = EVizitPL.Diag_id
					left join v_LpuSection as evplls on evplls.LpuSection_id = EVizitPL.LpuSection_id
					left join v_MedPersonal as evplmp on evplmp.MedPersonal_id = EVizitPL.MedPersonal_id and evplmp.Lpu_id = Evn.Lpu_id
					left join v_PayType as evplpt on evplpt.PayType_id = EVizitPL.PayType_id
					left join v_VizitType as evplvt on evplvt.VizitType_id = EVizitPL.VizitType_id
					left join v_ServiceType as evplst on evplst.ServiceType_id = EVizitPL.ServiceType_id
					left join v_HealthKind as evplhk on evplhk.HealthKind_id = EVizitPL.HealthKind_id
					left join v_VizitType VT on VT.VizitType_id = EVizitPL.VizitType_id
				";
				if (in_array($data["session"]["region"]["nick"], ["ufa"])) {
					$query .= "
						left join lateral(
							select
								t1.EvnUslugaCommon_id,
								t1.Usluga_id,
								t1.UslugaComplex_id as UslugaComplex_uid,
								t3.UslugaComplex_Code
							from
								v_EvnUslugaCommon t1
								left join v_Usluga t2 on t2.Usluga_id = t1.Usluga_id
								left join v_UslugaComplex t3 on t3.UslugaComplex_id = t1.UslugaComplex_id
								left join v_UslugaCategory t4 on t4.UslugaCategory_id = coalesce(t2.UslugaCategory_id, t3.UslugaCategory_id)
							where t1.EvnUslugaCommon_pid = EVizitPL.EvnVizitPL_id and t4.UslugaCategory_SysNick in ('tfoms', 'lpusection')
							order by t1.EvnUslugaCommon_setDT desc
							limit 1
						) as EU on true
					";
				}
				$query .= (in_array($data["PersonPeriodicType_id"], [2]))
					? " inner join v_Person_all PS on EVizitPL.PersonEvn_id = PS.PersonEvn_id
							and EVizitPL.Server_id = PS.Server_id "
					: (!isset($data["Refuse_id"])
						? " inner join v_PersonState PS on EVizitPL.Person_id = PS.Person_id "
						: " inner join v_PersonState_all PS on EVizitPL.Person_id = PS.Person_id ");
				$query .= " left join v_Polis pls on pls.Polis_id = ps.Polis_id ";
				if (allowPersonEncrypHIV($data["session"])) {
					$query .= " left join v_PersonEncrypHIV PEH on PEH.Person_id = PS.Person_id ";
				}
			} elseif ("EvnPL" == $data["SearchFormType"]) {
				$query .= "
					left join lateral(
						select
							EVPL.EvnVizitPL_id, 
							EVPL.HealthKind_id, 
							EVPL.VizitType_id, 
							EVPL.MedPersonal_id, 
							EVPL.Lpu_id,
							EVPL.Diag_id,
							EVPL.LpuSection_id,
							EVPL.DeseaseType_id,
							EVPL.MedPersonal_sid,
							EVPL.EvnVizitPL_isPaid,
							EVPL.ServiceType_id,
							EVPL.TreatmentClass_id,
							EVPL.EvnVizitPL_setDT,
							EVPL.UslugaComplex_id,
							EVPL.EvnVizitPL_IsInReg,
							EVPL.VizitClass_id
						from v_EvnVizitPL EVPL 
						where EVPL.EvnVizitPL_pid = EPL.EvnPL_id and EVPL.EvnVizitPL_Index = EVPL.EvnVizitPL_IndexMinusOne
						limit 1
					) as EVPL on true
					left join v_Diag EVPLD on EVPLD.Diag_id = EPL.Diag_id
					left join v_YesNo IsFinish on IsFinish.YesNo_id = EPL.EvnPL_IsFinish
					left join v_HealthKind as HK on HK.HealthKind_id = EVPL.HealthKind_id
					left join v_VizitType VT on VT.VizitType_id = EVPL.VizitType_id
					left join Lpu dlpu on dlpu.Lpu_id = EPL.Lpu_did		                
					left join lateral(
						select MP.Person_Fio
						from v_MedPersonal MP
						where MP.MedPersonal_id = EVPL.MedPersonal_id and MP.Lpu_id = EVPL.Lpu_id
						limit 1
					) as MP on true
				";
				$query .= (in_array($data["PersonPeriodicType_id"], [2]))
					? " inner join v_Person_all PS on Evn.Server_id = PS.Server_id and Evn.PersonEvn_id = PS.PersonEvn_id "
					: (!isset($data["Refuse_id"])
						? " inner join v_PersonState PS on EPL.Person_id = PS.Person_id "
						: " inner join v_PersonState_all PS on EPL.Person_id = PS.Person_id ");
				$query .= " left join v_Polis pls on pls.Polis_id = ps.Polis_id ";
				if (allowPersonEncrypHIV($data["session"])) {
					$query .= " left join v_PersonEncrypHIV PEH on PEH.Person_id = PS.Person_id ";
				}
				if ($callObject->regionNick == "kz") {
					$query .= "
						left join r101.AISResponse air on air.Evn_id = EPL.EvnPL_id and air.AISFormLoad_id = 1
						left join r101.AISResponse air9 on air9.Evn_id = EPL.EvnPL_id and air9.AISFormLoad_id = 2
						left join lateral (
							select eu.UslugaComplex_id
							from
								v_EvnUsluga_all eu
								inner join r101.AISUslugaComplexLink ucl on ucl.UslugaComplex_id = eu.UslugaComplex_id
							where eu.EvnUsluga_rid = epl.EvnPL_id and ucl.AISFormLoad_id = 1
							limit 1
						) as euais on true
						left join lateral(
							select eu.UslugaComplex_id
							from
								v_EvnUsluga_all eu
								inner join r101.AISUslugaComplexLink ucl on ucl.UslugaComplex_id = eu.UslugaComplex_id
							where eu.EvnUsluga_rid = epl.EvnPL_id and ucl.AISFormLoad_id = 1
							limit 1
						) as euais9 on true
					";
				}
			} else {
				$query .= "
					left join lateral(
						select
							EVPL.EvnVizitPL_id, 
							EVPL.HealthKind_id, 
							EVPL.VizitType_id, 
							EVPL.MedPersonal_id, 
							EVPL.Lpu_id,
							EVPL.Diag_id,
							EVPL.LpuSection_id,
							EVPL.DeseaseType_id,
							EVPL.MedPersonal_sid,
							EVPL.EvnVizitPL_isPaid,
							EVPL.ServiceType_id,
							EVPL.EvnVizitPL_setDT,
							EVPL.EvnVizitPL_IsInReg,
							EVPL.VizitClass_id
						from v_EvnVizitPL EVPL 
						where EVPL.EvnVizitPL_pid = EPL.EvnPL_id and EVPL.EvnVizitPL_Index = EVPL.EvnVizitPL_IndexMinusOne
						limit 1
					) as EVPL on true
					left join v_Diag EVPLD on EVPLD.Diag_id = EPL.Diag_id
					left join v_YesNo IsFinish on IsFinish.YesNo_id = EvnPLBase.EvnPLBase_IsFinish
					left join v_HealthKind as HK on HK.HealthKind_id = EVPL.HealthKind_id
					left join v_VizitType VT on VT.VizitType_id = EVPL.VizitType_id
					left join lateral(
						select MP.Person_Fio
						from v_MedPersonal MP
						where MP.MedPersonal_id = EVPL.MedPersonal_id and MP.Lpu_id = EVPL.Lpu_id
						limit 1
					) as MP on true
				";
			}
			//$query .= " left join v_EvnCostPrint ecp on ecp.Evn_id = EPL.EvnPL_id ";
			$query .= " LEFT JOIN LATERAL (
				SELECT
					EvnCostPrint_setDT,
					EvnCostPrint_IsNoPrint
				FROM v_EvnCostPrint ecp 
				where ecp.Evn_id = EPL.EvnPL_id
				LIMIT 1 
			) ecp on true";
		} else {
			if (isset($data["TreatmentClass_id"])) {
				$query .= "
					left join lateral(
						select
							EVPL.EvnVizitPL_id, 
							EVPL.HealthKind_id, 
							EVPL.VizitType_id, 
							EVPL.MedPersonal_id, 
							EVPL.Lpu_id,
							EVPL.Diag_id,
							EVPL.LpuSection_id,
							EVPL.DeseaseType_id,
							EVPL.MedPersonal_sid,
							EVPL.EvnVizitPL_isPaid,
							EVPL.ServiceType_id,
							EVPL.TreatmentClass_id,
							EVPL.EvnVizitPL_setDT,
							EVPL.UslugaComplex_id,
							EVPL.EvnVizitPL_IsInReg,
							EVPL.VizitClass_id
						from v_EvnVizitPL EVPL 
						where EVPL.EvnVizitPL_pid = EPL.EvnPL_id and EVPL.EvnVizitPL_Index = EVPL.EvnVizitPL_IndexMinusOne
						limit 1
					) as EVPL on true
				";
			}
			switch ($data["SearchFormType"]) {
				case "EPLPerson":
					$query .= " inner join v_EvnPL EPL2 on EPL2.EvnPL_id = EPL.EvnPL_id ";
					if ($prefix == "PS2") {
						$query .= ($data["epl_date_type"] == 2)
							? " inner join v_Person_all PS2 on PS2.Server_id = EPL2.Server_id and PS2.PersonEvn_id = EPL2.PersonEvn_id "
							: " inner join v_PersonState PS2 on PS2.PersonEvn_id = Evn.PersonEvn_id ";
					}
					$query .= "
						inner join v_PersonState PS on PS.Person_id = EPL2.Person_id
						left join Sex on Sex.Sex_id = {$prefix}.Sex_id
						left join SocStatus Soc on Soc.SocStatus_id = {$prefix}.SocStatus_id
						left join PersonChild PCh on PCh.Person_id = {$prefix}.Person_id
						left join YesNo IsInv on IsInv.YesNo_id = PCh.PersonChild_IsInvalid
						left join Diag InvD on InvD.Diag_id = PCh.Diag_id
						left join Polis Pol on Pol.Polis_id = {$prefix}.Polis_id
						left join OMSSprTerr OMSST on OMSST.OMSSprTerr_id = Pol.OMSSprTerr_id
						left join PolisType PolTp on PolTp.PolisType_id = Pol.PolisType_id
						left join v_OrgSmo OS on OS.OrgSmo_id = Pol.OrgSmo_id
						left join v_Org OSO on OSO.Org_id = OS.Org_id
						left join v_Address_all UA on UA.Address_id = {$prefix}.UAddress_id 
						left join v_Address_all PA on PA.Address_id = {$prefix}.PAddress_id
						left join Document Doc on Doc.Document_id = {$prefix}.Document_id 
						left join DocumentType DocTp on DocTp.DocumentType_id = Doc.DocumentType_id
						left join v_OrgDep OrgD on OrgD.OrgDep_id = Doc.OrgDep_id
					";
					break;
				case "EvnAgg":
					$query .= "
						inner join v_EvnUsluga EvnUsluga on EvnUsluga.EvnUsluga_rid = EPL.EvnPL_id and EvnUsluga.Lpu_id {$getLpuIdFilter}
						inner join v_EvnAgg EvnAgg on EvnAgg.EvnAgg_pid = EvnUsluga.EvnUsluga_id and EvnAgg.Lpu_id {$getLpuIdFilter}
						left join AggType as dbfat on dbfat.AggType_id = EvnAgg.AggType_id
						left join AggWhen as dbfaw on dbfaw.AggWhen_id = EvnAgg.AggWhen_id
					";
					$query .= (in_array($data["PersonPeriodicType_id"], [2]))
						? " inner join v_Person_all PS on Evn.Server_id = PS.Server_id and Evn.PersonEvn_id = PS.PersonEvn_id "
						: (!isset($data["Refuse_id"])
							? " inner join v_PersonState PS on Evn.Person_id = PS.Person_id "
							: " inner join v_PersonState_all PS on Evn.Person_id = PS.Person_id ");
					break;
				case "EvnPL":
					$query .= "
						left join v_Diag as dbfdiag on dbfdiag.Diag_id = EPL.Diag_id
						left join v_Lpu as dbflpu on dbflpu.Lpu_id = Evn.Lpu_id
						left join ResultClass dbfrc on dbfrc.ResultClass_id = EPL.ResultClass_id
						left join DeseaseType dbfdt on dbfdt.DeseaseType_id = EPL.DeseaseType_id
						left join PrehospDirect dbfpd on dbfpd.PrehospDirect_id = EPL.PrehospDirect_id
						left join v_Lpu dbfprehosplpu on dbfprehosplpu.Lpu_id = EPL.Lpu_did
						left join LpuSection dbflsd on dbflsd.LpuSection_id = EPL.LpuSection_did
						left join YesNo dbfift on dbfift.YesNo_id = EPL.EvnPL_IsFirstTime
						left join DirectClass dbfdc on dbfdc.DirectClass_id = EPL.DirectClass_id
						left join v_Lpu dbflpudir on dbflpudir.Lpu_id = EPL.Lpu_oid
						left join LpuSection dbflsdir on dbflsdir.LpuSection_id = EPL.LpuSection_oid
						left join lateral(
							select
								YesNo.YesNo_Code as PersonChild_IsInvalid_Code,
								PersonSprTerrDop.PersonSprTerrDop_Code as PermRegion_Code
							from
								PersonChild
								left join YesNo on YesNo.YesNo_id = PersonChild.PersonChild_IsInvalid
								left join PersonSprTerrDop on PersonSprTerrDop.PersonSprTerrDop_id = PersonChild.PersonSprTerrDop_id
							where PersonChild.Person_id = Evn.Person_id
							order by PersonChild.PersonChild_insDT desc
							limit 1
						) as dbfinv on true
					";
					$query .= (in_array($data["PersonPeriodicType_id"], [2]))
						? "
							inner join v_Person_all PS on Evn.Server_id = PS.Server_id and Evn.PersonEvn_id = PS.PersonEvn_id
							left join v_Polis pls on pls.Polis_id = ps.Polis_id
							left join SocStatus as dbfss on dbfss.SocStatus_id = PS.SocStatus_id
							left join Sex dbfsex on dbfsex.Sex_id = PS.Sex_id
							left join Address dbfaddr on dbfaddr.Address_id = coalesce(PS.PAddress_id, PS.UAddress_id)
							left join KLStreet dbfkls on dbfkls.KLStreet_id = dbfaddr.KLStreet_id
							left join KLArea dbfkla on dbfkla.KLArea_id = COALESCE(dbfaddr.KLTown_id, dbfaddr.KLCity_id, dbfaddr.KLSubRgn_id, dbfaddr.KLRgn_id) and dbfkls.KLStreet_id is null
						"
						: (!isset($data["Refuse_id"])
							? "
								inner join v_PersonState PS on Evn.Person_id = PS.Person_id
								left join v_Polis pls on pls.Polis_id = ps.Polis_id
								left join SocStatus as dbfss on dbfss.SocStatus_id = PS.SocStatus_id
								left join Sex dbfsex on dbfsex.Sex_id = PS.Sex_id
								left join Address dbfaddr on dbfaddr.Address_id = coalesce(PS.PAddress_id, PS.UAddress_id)
								left join KLStreet dbfkls on dbfkls.KLStreet_id = dbfaddr.KLStreet_id
								left join KLArea dbfkla on dbfkla.KLArea_id = COALESCE(dbfaddr.KLTown_id, dbfaddr.KLCity_id, dbfaddr.KLSubRgn_id, dbfaddr.KLRgn_id) and dbfkls.KLStreet_id is null
							"
							: "
								inner join v_PersonState_all PS on Evn.Person_id = PS.Person_id
								left join v_Polis pls on pls.Polis_id = ps.Polis_id
								left join SocStatus as dbfss on dbfss.SocStatus_id = PS.SocStatus_id
								left join Sex dbfsex on dbfsex.Sex_id = PS.Sex_id
								left join Address dbfaddr on dbfaddr.Address_id = coalesce(PS.PAddress_id, PS.UAddress_id)
								left join KLStreet dbfkls on dbfkls.KLStreet_id = dbfaddr.KLStreet_id
								left join KLArea dbfkla on dbfkla.KLArea_id = COALESCE(dbfaddr.KLTown_id, dbfaddr.KLCity_id, dbfaddr.KLSubRgn_id, dbfaddr.KLRgn_id) and dbfkls.KLStreet_id is null
							");
					break;
				case "EvnUsluga":
					$query .= "
						inner join v_EvnUsluga EvnUsluga on EvnUsluga.EvnUsluga_rid = EPL.EvnPL_id and EvnUsluga.Lpu_id {$getLpuIdFilter}
						left join v_PayType as dbfpt on dbfpt.PayType_id = EvnUsluga.PayType_id
						left join v_UslugaComplex as dbfusluga on dbfusluga.UslugaComplex_id = EvnUsluga.UslugaComplex_id
						left join v_UslugaPlace as dbfup on dbfup.UslugaPlace_id = EvnUsluga.UslugaPlace_id
						left join v_MedPersonal dbfmp on dbfmp.MedPersonal_id = EvnUsluga.MedPersonal_id and dbfmp.Lpu_id = Evn.Lpu_id
					";
					$query .= (in_array($data["PersonPeriodicType_id"], [2]))
						? " inner join v_Person_all PS on Evn.Server_id = PS.Server_id and Evn.PersonEvn_id = PS.PersonEvn_id "
						: (!isset($data["Refuse_id"])
							? " inner join v_PersonState PS on Evn.Person_id = PS.Person_id "
							: " inner join v_PersonState_all PS on Evn.Person_id = PS.Person_id ");
					break;
				case "EvnVizitPL":
					$query .= "
						left join v_Diag as dbfdiag on dbfdiag.Diag_id=EVizitPL.Diag_id
						left join LpuSection as dbfls on dbfls.LpuSection_id=EVizitPL.LpuSection_id
						left join v_MedPersonal as dbfmp on dbfmp.MedPersonal_id = EVizitPL.MedPersonal_id and dbfmp.Lpu_id = Evn.Lpu_id
						left join PayType as dbfpt on dbfpt.PayType_id=EVizitPL.PayType_id
						left join VizitClass as dbfvc on dbfvc.VizitClass_id=EVizitPL.VizitClass_id
						left join VizitType as dbfvt on dbfvt.VizitType_id=EVizitPL.VizitType_id
						left join DeseaseType as dbfdt on dbfdt.DeseaseType_id=EVizitPL.DeseaseType_id
						left join ServiceType as dbfst on dbfst.ServiceType_id=EVizitPL.ServiceType_id
						left join ProfGoal as dbfpg on dbfpg.ProfGoal_id=EVizitPL.ProfGoal_id
					";
					$query .= (in_array($data["PersonPeriodicType_id"], [2]))
						? "
							inner join v_Person_all PS on EVizitPL.PersonEvn_id = PS.PersonEvn_id
								and EVizitPL.Server_id = PS.Server_id
							left join v_Polis pls on pls.Polis_id = ps.Polis_id
						"
						: (!isset($data["Refuse_id"])
							? "
								inner join v_PersonState PS on EVizitPL.Person_id = PS.Person_id
								left join v_Polis pls on pls.Polis_id = ps.Polis_id
							"
							: "
								inner join v_PersonState_all PS on EVizitPL.Person_id = PS.Person_id
								left join v_Polis pls on pls.Polis_id = ps.Polis_id
							");
					break;
			}
			if (isset($data["and_eplperson"]) && $data["and_eplperson"]) {
				if ($prefix == "PS2") {
					$query .= " inner join v_EvnPL EPL2 on EPL2.EvnPL_id = EPL.EvnPL_id ";
					$query .= ($data["epl_date_type"] == 2)
						? " left join v_Person_all PS2 on PS2.Server_id = EPL2.Server_id and PS2.PersonEvn_id = EPL2.PersonEvn_id "
						: " left join v_PersonState PS2 on PS2.Person_id = EPL2.Person_id ";
				}
				$query .= "
					left join Sex PrsSex on PrsSex.Sex_id = {$prefix}.Sex_id
					left join SocStatus PrsSoc on PrsSoc.SocStatus_id = {$prefix}.SocStatus_id
					left join PersonChild PrsPCh on PrsPCh.Person_id = {$prefix}.Person_id
					left join YesNo PrsIsInv on PrsIsInv.YesNo_id = PrsPCh.PersonChild_IsInvalid
					left join Diag PrsInvD on PrsInvD.Diag_id = PrsPCh.Diag_id
					left join Polis PrsPol on PrsPol.Polis_id = {$prefix}.Polis_id
					left join OMSSprTerr PrsOMSST on PrsOMSST.OMSSprTerr_id = PrsPol.OMSSprTerr_id
					left join PolisType PrsPolTp on PrsPolTp.PolisType_id = PrsPol.PolisType_id
					left join v_OrgSmo PrsOS on PrsOS.OrgSmo_id = PrsPol.OrgSmo_id
					left join v_Org PrsOSO on PrsOSO.Org_id = PrsOS.Org_id
					left join v_Address_all PrsUA on PrsUA.Address_id = {$prefix}.UAddress_id 
					left join v_Address_all PrsPA on PrsPA.Address_id = {$prefix}.PAddress_id
					left join Document PrsDoc on PrsDoc.Document_id = {$prefix}.Document_id 
					left join DocumentType PrsDocTp on PrsDocTp.DocumentType_id = PrsDoc.DocumentType_id
					left join v_OrgDep PrsOrgD on PrsOrgD.OrgDep_id = PrsDoc.OrgDep_id
				";
			}
		}
		if (!empty($data["EvnVizitPL_isPaid"])) {
			$regionNick = $callObject->getRegionNick();
			if ($data["SearchFormType"] == "EvnPL") {
				if (in_array($regionNick, ["perm", "astra", "kareliya", "pskov"])) {
					$query .= "
						left join lateral(
							select EvnVizitPL_isPaid
							from v_EvnVizitPL
							where EvnVizitPL_pid = EPL.EvnPL_id and EvnVizitPL_Index = EvnVizitPL_Count - 1
							limit 1
						) as LEVPL on true
					";
				} elseif (in_array($regionNick, ["penza", "ufa", "vologda"])) {
					$query .= "
						left join lateral(
							select EvnVizitPL_isPaid
							from v_EvnVizitPL
							where EvnVizitPL_pid = EPL.EvnPL_id and coalesce(EvnVizitPL_isPaid, 1) = '2'
							limit 1
						) as LEVPLpaid on true
					";
				}
			} elseif ($data["SearchFormType"] == "EvnVizitPL") {
				if (in_array($regionNick, ["perm", "astra", "kareliya", "pskov"])) {
					$query .= "
						left join lateral(
							select EvnVizitPL_isPaid
							from v_EvnVizitPL
							where EvnVizitPL_pid = EVizitPL.EvnVizitPL_pid and EvnVizitPL_Index = EvnVizitPL_Count - 1
							limit 1
						) as LEVPL on true
					";
				} elseif (in_array($regionNick, ["penza", "ufa", "vologda"])) {
					$query .= "
						left join lateral(
							select EvnVizitPL_isPaid
							from v_EvnVizitPL
							where EvnVizitPL_pid = EVizitPL.EvnVizitPL_pid and coalesce(EvnVizitPL_isPaid, 1) = 2
							limit 1
						) LEVPLpaid on true
					";
				}
			}
		}
		$query .= "
			left join lateral(
				select ServiceEvnStatus_id
				from v_ServiceEvnHist
				where Evn_id = EPL.EvnPL_id and ServiceEvnList_id = 1
				order by ServiceEvnHist_id desc
				limit 1
			) as SEH1 on true
			left join v_ServiceEvnStatus SES1 on SES1.ServiceEvnStatus_id = SEH1.ServiceEvnStatus_id
		";
		return $query;
	}

	public static function selectBody_EvnPLDispTeenInspectionPred(Search_model $callObject, $data)
	{
		$getLpuIdFilter = $callObject->getLpuIdFilter($data);
		$query = ($data["PersonPeriodicType_id"] == 2)
			? " inner join v_EvnPLDispTeenInspection EPLDTI on EPLDTI.Server_id = PS.Server_id and EPLDTI.PersonEvn_id = PS.PersonEvn_id and EPLDTI.Lpu_id {$getLpuIdFilter} "
			: " inner join v_EvnPLDispTeenInspection EPLDTI on PS.Person_id = EPLDTI.Person_id and EPLDTI.Lpu_id {$getLpuIdFilter} ";
		$query .= "
			left join v_EvnCostPrint ecp on ecp.Evn_id = EPLDTI.EvnPLDispTeenInspection_id
			left join YesNo IsFinish on IsFinish.YesNo_id = coalesce(EPLDTI.EvnPLDispTeenInspection_IsFinish, 1)
			left join YesNo IsTwoStage on IsTwoStage.YesNo_id = coalesce(EPLDTI.EvnPLDispTeenInspection_IsTwoStage, 1)
			left join v_AssessmentHealth AH on AH.EvnPLDisp_id = EPLDTI.EvnPLDispTeenInspection_id
			left join v_HealthGroupType HGT on HGT.HealthGroupType_id = AH.HealthGroupType_id
			left join v_HealthKind HK on HK.HealthKind_id = AH.HealthKind_id
			left join v_Sex Sex on Sex.Sex_id = PS.Sex_id
			left join v_Address UAdd on UAdd.Address_id = ps.UAddress_id
			left join v_Address PAdd on PAdd.Address_id = ps.PAddress_id
			left join v_PersonDispOrp PDORP on PDORP.PersonDispOrp_id = EPLDTI.PersonDispOrp_id
			left join v_AgeGroupDisp AGD on AGD.AgeGroupDisp_id = coalesce(EPLDTI.AgeGroupDisp_id, PDORP.AgeGroupDisp_id)
		";
		if (in_array($data["session"]["region"]["nick"], ["buryatiya", "krym"])) {
			$query .= "
				left join lateral(
					select UslugaComplex_id
					from v_EvnUslugaDispDop
					where EvnUslugaDispDop_IsVizitCode = 2
					  and EvnUslugaDispDop_pid = EPLDTI.EvnPLDispTeenInspection_id
					limit 1
				) as euddvizit on true
				left join v_UslugaComplex UC on uc.UslugaComplex_id = euddvizit.UslugaComplex_id
			";
		}
		return $query;
	}

	public static function selectBody_EvnPLDispOrpSec(Search_model $callObject, $data)
	{
		$getLpuIdFilter = $callObject->getLpuIdFilter($data);
		$query = ($data["PersonPeriodicType_id"] == 2)
			? " inner join v_EvnPLDispOrp EPLDO on EPLDO.Server_id = PS.Server_id and EPLDO.PersonEvn_id = PS.PersonEvn_id and EPLDO.Lpu_id {$getLpuIdFilter} and EPLDO.DispClass_id in (4,8) "
			: " inner join v_EvnPLDispOrp EPLDO on PS.Person_id = EPLDO.Person_id and EPLDO.Lpu_id {$getLpuIdFilter} and EPLDO.DispClass_id in (4,8) ";
		$query .= "
			left join v_EvnCostPrint ecp on ecp.Evn_id = EPLDO.EvnPLDispOrp_id
			left join v_Sex Sex on Sex.Sex_id = PS.Sex_id
			left join YesNo IsFinish on IsFinish.YesNo_id = EPLDO.EvnPLDispOrp_IsFinish
			left join YesNo IsTwoStage on IsTwoStage.YesNo_id = EPLDO.EvnPLDispOrp_IsTwoStage
			left join v_AssessmentHealth AH on AH.EvnPLDisp_id = EPLDO.EvnPLDispOrp_id
			left join v_HealthKind HK on HK.HealthKind_id = AH.HealthKind_id
		";
		if (in_array($data["session"]["region"]["nick"], ["buryatiya", "krym"])) {
			$query .= "
				left join lateral(
					select UslugaComplex_id
					from v_EvnUslugaDispOrp
					where EvnUslugaDispOrp_IsVizitCode = 2 and EvnUslugaDispOrp_pid = EPLDO.EvnPLDispOrp_id
					limit 1
				) as euddvizit on true
				left join v_UslugaComplex UC on uc.UslugaComplex_id = euddvizit.UslugaComplex_id
			";
		}
		return $query;
	}

	public static function selectBody_EvnPLDispOrpOld(Search_model $callObject, $data)
	{
		$getLpuIdFilter = $callObject->getLpuIdFilter($data);
		$query = ($data["PersonPeriodicType_id"] == 2)
			? " inner join v_EvnPLDispOrp EPLDO on EPLDO.Server_id = PS.Server_id and EPLDO.PersonEvn_id = PS.PersonEvn_id and EPLDO.Lpu_id {$getLpuIdFilter} "
			: " inner join v_EvnPLDispOrp EPLDO on PS.Person_id = EPLDO.Person_id and EPLDO.Lpu_id {$getLpuIdFilter} ";
		if (isset($data['EvnPLDispOrp_HealthKind_id'])) {
			$query .= " 
				inner join v_EvnVizitDispDop EVPLDD on EVPLDD.EvnVizitDispDop_pid = EPLDO.EvnPLDispOrp_id
					and coalesce(EPLDO.EvnPLDispOrp_IsFinish, 1) = 2
					and EVPLDD.DopDispSpec_id = 1
					and EVPLDD.HealthKind_id = :EvnPLDispOrp_HealthKind_id
					and EVPLDD.Lpu_id {$getLpuIdFilter}
			";
		}
		$query .= " left join YesNo IsFinish on IsFinish.YesNo_id = EPLDO.EvnPLDispOrp_IsFinish ";
		return $query;
	}

	public static function selectBody_EvnPLDispOrp(Search_model $callObject, $data)
	{
		$getLpuIdFilter = $callObject->getLpuIdFilter($data);
		$query = ($data["PersonPeriodicType_id"] == 2)
			? " inner join v_EvnPLDispOrp EPLDO on EPLDO.Server_id = PS.Server_id and EPLDO.PersonEvn_id = PS.PersonEvn_id and EPLDO.Lpu_id {$getLpuIdFilter} and EPLDO.DispClass_id in (3,7) "
			: " inner join v_EvnPLDispOrp EPLDO on PS.Person_id = EPLDO.Person_id and EPLDO.Lpu_id {$getLpuIdFilter} and EPLDO.DispClass_id in (3,7) ";
		if (isset($data["EvnPLDisp_UslugaComplex"]) && $data["EvnPLDisp_UslugaComplex"] > 0) {
			$query .= "
				left join v_EvnVizitDispOrp EVDO on EVDO.EvnVizitDispOrp_pid = EPLDO.EvnPLDispOrp_id
				inner join v_EvnUslugaDispOrp EUDO on EUDO.EvnUslugaDispOrp_pid in (EPLDO.EvnPLDispOrp_id, EVDO.EvnVizitDispOrp_id) and EUDO.UslugaComplex_id = :EvnPLDisp_UslugaComplex
			";
		}
		$query .= "
			left join v_EvnCostPrint ecp on ecp.Evn_id = EPLDO.EvnPLDispOrp_id
			left join v_Sex Sex on Sex.Sex_id = PS.Sex_id
			left join YesNo IsFinish on IsFinish.YesNo_id = EPLDO.EvnPLDispOrp_IsFinish
			left join YesNo IsTwoStage on IsTwoStage.YesNo_id = EPLDO.EvnPLDispOrp_IsTwoStage
			left join v_AssessmentHealth AH on AH.EvnPLDisp_id = EPLDO.EvnPLDispOrp_id
			left join v_HealthKind HK on HK.HealthKind_id = AH.HealthKind_id
		";
		if (in_array($data["session"]["region"]["nick"], ["buryatiya", "krym"])) {
			$query .= "
				left join lateral(
					select UslugaComplex_id
					from v_EvnUslugaDispOrp
					where EvnUslugaDispOrp_IsVizitCode = 2 and EvnUslugaDispOrp_pid = EPLDO.EvnPLDispOrp_id
					limit 1
				) as euddvizit on true
				left join v_UslugaComplex UC on uc.UslugaComplex_id = euddvizit.UslugaComplex_id
			";
		}
		return $query;
	}

	public static function selectBody_EvnPLDispTeen14(Search_model $callObject, $data)
	{
		$getLpuIdFilter = $callObject->getLpuIdFilter($data);
		$query = ($data["PersonPeriodicType_id"] == 2)
			? " inner join v_EvnPLDispTeen14 EPLDT14 on EPLDT14.Server_id = PS.Server_id and EPLDT14.PersonEvn_id = PS.PersonEvn_id and EPLDT14.Lpu_id {$getLpuIdFilter} "
			: " inner join v_EvnPLDispTeen14 EPLDT14 on PS.Person_id = EPLDT14.Person_id and EPLDT14.Lpu_id {$getLpuIdFilter} ";
		if (isset($data["EvnPLDispTeen14_HealthKind_id"])) {
			$query .= " 
				inner join v_EvnVizitDispTeen14 EVPLDT14 on EVPLDT14.EvnVizitDispTeen14_pid = EPLDT14.EvnPLDispTeen14_id
					and coalesce(EPLDT14.EvnPLDispTeen14_IsFinish, 1) = 2
					and EVPLDT14.Teen14DispSpecType_id = 1
					and EVPLDT14.HealthKind_id = :EvnPLDispTeen14_HealthKind_id
					and EVPLDT14.Lpu_id {$getLpuIdFilter}
			";
		}
		$query .= " left join YesNo IsFinish on IsFinish.YesNo_id = EPLDT14.EvnPLDispTeen14_IsFinish ";
		return $query;
	}

	public static function selectBody_EvnPLDispDop(Search_model $callObject, $data)
	{
		$getLpuIdFilter = $callObject->getLpuIdFilter($data);
		$query = ($data["PersonPeriodicType_id"] == 2)
			? " inner join v_EvnPLDispDop EPLDD on EPLDD.Server_id = PS.Server_id and EPLDD.PersonEvn_id = PS.PersonEvn_id and EPLDD.Lpu_id {$getLpuIdFilter} "
			: " inner join v_EvnPLDispDop EPLDD on PS.Person_id = EPLDD.Person_id and EPLDD.Lpu_id {$getLpuIdFilter} ";
		if (isset($data["EvnPLDispDop_HealthKind_id"])) {
			$query .= " 
				inner join v_EvnVizitDispDop EVPLDD on EVPLDD.EvnVizitDispDop_pid = EPLDD.EvnPLDispDop_id
					and coalesce(EPLDD.EvnPLDispDop_IsFinish, 1) = 2
					and EVPLDD.DopDispSpec_id = 1
					and EVPLDD.HealthKind_id = :EvnPLDispDop_HealthKind_id
					and EVPLDD.Lpu_id {$getLpuIdFilter}
			";
		}
		$query .= " left join YesNo IsFinish on IsFinish.YesNo_id = EPLDD.EvnPLDispDop_IsFinish ";
		return $query;
	}

	public static function selectBody_EvnPLDispScreenChild(Search_model $callObject, $data)
	{
		$getLpuIdFilter = $callObject->getLpuIdFilter($data);
		$query = ($data["PersonPeriodicType_id"] == 2)
			? "
				inner join v_EvnPLDispScreenChild EPLDS on EPLDS.Server_id = PS.Server_id
					and EPLDS.PersonEvn_id = PS.PersonEvn_id
					and EPLDS.Lpu_id {$getLpuIdFilter}
					and date_part('year', EvnPLDispScreenChild_setDate) = :PersonDopDisp_Year
			"
			: "
				inner join v_EvnPLDispScreenChild EPLDS on PS.Person_id = EPLDS.Person_id
					and EPLDS.Lpu_id {$getLpuIdFilter}
					and date_part('year', EvnPLDispScreenChild_setDate) = :PersonDopDisp_Year
			";
		$query .= "
			left join v_Sex Sex on Sex.Sex_id = PS.Sex_id
			left join v_AgeGroupDisp AGD on AGD.AgeGroupDisp_id = EPLDS.AgeGroupDisp_id
			left join YesNo IsFinish on IsFinish.YesNo_id = coalesce(EPLDS.EvnPLDispScreenChild_IsEndStage, 1)
		";
		return $query;
	}

	public static function selectBody_EvnPLDispScreen(Search_model $callObject, $data)
	{
		$getLpuIdFilter = $callObject->getLpuIdFilter($data);
		$query = ($data["PersonPeriodicType_id"] == 2)
			? "
				inner join v_EvnPLDispScreen EPLDS on EPLDS.Server_id = PS.Server_id
					and EPLDS.PersonEvn_id = PS.PersonEvn_id
					and EPLDS.Lpu_id {$getLpuIdFilter}
					and date_part('year', EvnPLDispScreen_setDate) = :PersonDopDisp_Year
			"
			: "
				inner join v_EvnPLDispScreen EPLDS on PS.Person_id = EPLDS.Person_id
					and EPLDS.Lpu_id {$getLpuIdFilter}
					and date_part('year', EvnPLDispScreen_setDate) = :PersonDopDisp_Year
			";
		$query .= "
			left join v_Sex Sex on Sex.Sex_id = PS.Sex_id
			left join v_AgeGroupDisp AGD on AGD.AgeGroupDisp_id = EPLDS.AgeGroupDisp_id
			left join YesNo IsFinish on IsFinish.YesNo_id = coalesce(EPLDS.EvnPLDispScreen_IsEndStage, 1)
		";
		return $query;
	}

	public static function selectBody_EvnPLDispProf(Search_model $callObject, $data)
	{
		$getLpuIdFilter = $callObject->getLpuIdFilter($data);
		$query = ($data["PersonPeriodicType_id"] == 2)
			? " 
				inner join v_EvnPLDispProf EPLDP on EPLDP.Server_id = PS.Server_id
					and EPLDP.PersonEvn_id = PS.PersonEvn_id
					and EPLDP.Lpu_id {$getLpuIdFilter}
					and date_part('year', EvnPLDispProf_setDate) = :PersonDopDisp_Year
			"
			: " 
				inner join v_EvnPLDispProf EPLDP on PS.Person_id = EPLDP.Person_id
					and EPLDP.Lpu_id {$getLpuIdFilter}
					and date_part('year', EvnPLDispProf_setDate) = :PersonDopDisp_Year
			";
		$query .= "
			left join v_EvnCostPrint ecp on ecp.Evn_id = EPLDP.EvnPLDispProf_id
			left join YesNo IsFinish on IsFinish.YesNo_id = coalesce(EPLDP.EvnPLDispProf_IsEndStage, 1)
			left join v_HealthKind HK on HK.HealthKind_id = EPLDP.HealthKind_id
			left join v_Address UAdd on UAdd.Address_id = ps.UAddress_id
				left join v_Address PAdd on PAdd.Address_id = ps.PAddress_id
		";
		if (!empty($data["Disp_MedStaffFact_id"]) || !empty($data["Disp_LpuSection_id"]) || !empty($data["Disp_LpuBuilding_id"])) {
			$disp_msf = $disp_msf2 = $disp_ls = $disp_ls2 = $disp_b = $disp_b2 = $join1 = $join2 = "";
			if (!empty($data["Disp_MedStaffFact_id"])) {
				$disp_msf = " and msf1.MedStaffFact_id = :MedStaffFact_id";
				$disp_msf2 = " and msf2.MedStaffFact_id = :MedStaffFact_id";
				$queryParams["MedStaffFact_id"] = $data["Disp_MedStaffFact_id"];
			} else {
				if (!empty($data["Disp_LpuSection_id"])) {
					$disp_ls = " and msf1.LpuSection_id = :LpuSection_id";
					$disp_ls2 = " and msf2.LpuSection_uid = :LpuSection_id";
					$queryParams["LpuSection_id"] = $data["Disp_LpuSection_id"];
				}
				if (!empty($data["Disp_LpuBuilding_id"])) {
					$join1 = " left join v_LpuSection ls1 on ls1.LpuSection_id = msf1.LpuSection_id ";
					$disp_b = " and ls1.LpuBuilding_id = :LpuBuilding_id";
					$join2 = " left join v_LpuSection ls3 on ls3.LpuSection_id = msf2.LpuSection_uid ";
					$disp_b2 = " and ls3.LpuBuilding_id = :LpuBuilding_id";
					$queryParams["LpuBuilding_id"] = $data["Disp_LpuBuilding_id"];
				}
			}
			$query .= "
				left join lateral(
					select msf1.EvnVizitDispDop_id 
					from
						v_EvnVizitDispDop msf1 
						{$join1}
					where msf1.EvnVizitDispDop_pid = EPLDP.EvnPLDispProf_id {$disp_b} {$disp_msf} {$disp_ls}
					limit 1
				) as evapply on true
				left join lateral(
					select msf2.EvnUslugaDispDop_id 
					from
						v_EvnUslugaDispDop msf2
						{$join2} 
					where msf2.EvnUslugaDispDop_pid = EPLDP.EvnPLDispProf_id {$disp_b2} {$disp_msf2} {$disp_ls2}
					limit 1
				) as euapply on true
			";
		}
		return $query;
	}

	public static function selectBody_EvnPLDispDop13Sec(&$query, &$joinDopDispSecond, &$filterDopDispSecond, Search_model $callObject, $data)
	{
		$getLpuIdFilter = $callObject->getLpuIdFilter($data);
		$filterEPLDD13 = "";
		$filterDDICData = "";
		$filterDopDispSecond = "";
		if (isset($data["EvnPLDispDop13_setDate"])) {
			$filterEPLDD13 .= " and EPLDD13.EvnPLDispDop13_setDate = :EvnPLDispDop13_setDate::timestamp ";
		}
		if (isset($data["EvnPLDispDop13_setDate_Range"][0])) {
			$filterEPLDD13 .= " and EPLDD13.EvnPLDispDop13_setDate >= :EvnPLDispDop13_setDate_Range_0::timestamp ";
		}
		if (isset($data["EvnPLDispDop13_setDate_Range"][1])) {
			$filterEPLDD13 .= " and EPLDD13.EvnPLDispDop13_setDate <= :EvnPLDispDop13_setDate_Range_1::timestamp ";
		}
		if (isset($data["EvnPLDispDop13_disDate"])) {
			$filterEPLDD13 .= " and EPLDD13.EvnPLDispDop13_disDate = :EvnPLDispDop13_disDate::timestamp ";
		}
		if (isset($data["EvnPLDispDop13_disDate_Range"][0])) {
			$filterEPLDD13 .= " and EPLDD13.EvnPLDispDop13_disDate >= :EvnPLDispDop13_disDate_Range_0::timestamp ";
		}
		if (isset($data["EvnPLDispDop13_disDate_Range"][1])) {
			$filterEPLDD13 .= " and EPLDD13.EvnPLDispDop13_disDate <= :EvnPLDispDop13_disDate_Range_1::timestamp ";
		}
		if (isset($data["EvnPLDispDop13_IsFinish"])) {
			$filterEPLDD13 .= " and coalesce(EPLDD13.EvnPLDispDop13_IsEndStage, 1) = :EvnPLDispDop13_IsFinish ";
		}
		if (isset($data["EvnPLDispDop13_Cancel"])) {
			$filterDDICData .= " and coalesce(DDIC.DopDispInfoConsent_IsAgree,2) = :DopDispInfoConsent_IsAgree ";
		}
		if (isset($data["EvnPLDispDop13_IsTwoStage"])) {
			$filterEPLDD13 .= " and coalesce(EPLDD13.EvnPLDispDop13_IsTwoStage, 1) = :EvnPLDispDop13_IsTwoStage ";
		}
		if (isset($data["EvnPLDispDop13_HealthKind_id"])) {
			$filterEPLDD13 .= " and EPLDD13.HealthKind_id = :EvnPLDispDop13_HealthKind_id ";
		}
		if (isset($data["EvnPLDispDop13_isPaid"])) {
			if ($data["session"]["region"]["nick"] != "ufa") {
				$filterEPLDD13 .= " and coalesce(EPLDD13.EvnPLDispDop13_isPaid,1) = :EvnPLDispDop13_isPaid ";
			}
		}
		if (isset($data["EvnPLDispDop13Second_isPaid"])) {
			if ($data["session"]["region"]["nick"] != "ufa") {
				$filterDopDispSecond .= " and coalesce(DopDispSecond.EvnPLDispDop13_isPaid,1) = :EvnPLDispDop13Second_isPaid ";
			}
		}
		if (isset($data["EvnPLDispDop13_isMobile"])) {
			$filterEPLDD13 .= " and coalesce(EPLDD13.EvnPLDispDop13_isMobile,1) = :EvnPLDispDop13_isMobile ";
		}
		if (isset($data["EvnPLDispDop13Second_isMobile"])) {
			$filterDopDispSecond .= " and coalesce(DopDispSecond.EvnPLDispDop13_isMobile,1) = :EvnPLDispDop13Second_isMobile ";
		}
		if (isset($data["EvnPLDispDop13Second_setDate"])) {
			$filterDopDispSecond .= " and DopDispSecond.EvnPLDispDop13_setDate = :EvnPLDispDop13Second_setDate::timestamp ";
		}
		if (isset($data["EvnPLDispDop13Second_setDate_Range"][0])) {
			$filterDopDispSecond .= " and DopDispSecond.EvnPLDispDop13_setDate >= :EvnPLDispDop13Second_setDate_Range_0::timestamp ";
		}
		if (isset($data["EvnPLDispDop13Second_setDate_Range"][1])) {
			$filterDopDispSecond .= " and DopDispSecond.EvnPLDispDop13_setDate <= :EvnPLDispDop13Second_setDate_Range_1::timestamp ";
		}
		if (isset($data["EvnPLDispDop13Second_disDate"])) {
			$filterDopDispSecond .= " and DopDispSecond.EvnPLDispDop13_disDate = :EvnPLDispDop13Second_disDate::timestamp ";
		}
		if (isset($data["EvnPLDispDop13Second_disDate_Range"][0])) {
			$filterDopDispSecond .= " and DopDispSecond.EvnPLDispDop13_disDate >= :EvnPLDispDop13Second_disDate_Range_0::timestamp ";
		}
		if (isset($data["EvnPLDispDop13Second_disDate_Range"][1])) {
			$filterDopDispSecond .= " and DopDispSecond.EvnPLDispDop13_disDate <= :EvnPLDispDop13Second_disDate_Range_1::timestamp ";
		}
		if (isset($data["EvnPLDispDop13Second_IsFinish"])) {
			$filterDopDispSecond .= " and coalesce(DopDispSecond.EvnPLDispDop13_IsEndStage, 1) = :EvnPLDispDop13Second_IsFinish ";
		}
		if (isset($data["EvnPLDispDop13Second_HealthKind_id"])) {
			$filterDopDispSecond .= " and DopDispSecond.HealthKind_id = :EvnPLDispDop13Second_HealthKind_id ";
		}
		$joinDopDisp = "inner";
		$mainFilterDopDispSecond = "(DopDispSecond.EvnPLDispDop13_fid = EPLDD13.EvnPLDispDop13_id)";
		if (getRegionNick() == "ekb") {
			$joinDopDisp = "left";
			$mainFilterDopDispSecond = "
				(
					DopDispSecond.EvnPLDispDop13_fid = EPLDD13.EvnPLDispDop13_id or (
						DopDispSecond.EvnPLDispDop13_id is not null and
						DopDispSecond.Person_id = PS.Person_id and
						DopDispSecond.Lpu_id {$getLpuIdFilter} and
						DopDispSecond.DispClass_id = 2 and
						DopDispSecond.EvnPLDispDop13_fid is null and
						date_part('year', DopDispSecond.EvnPLDispDop13_consDT) = :PersonDopDisp_Year
					)
				)
			";
		}
		if ($data["PersonPeriodicType_id"] == 2) {
			$query .= "
				{$joinDopDisp} join v_EvnPLDispDop13 EPLDD13 on EPLDD13.Server_id = PS.Server_id
					and EPLDD13.PersonEvn_id = PS.PersonEvn_id
					and EPLDD13.Lpu_id {$getLpuIdFilter}
					and coalesce(EPLDD13.DispClass_id,1) = 1
					and date_part('year', EPLDD13.EvnPLDispDop13_consDT) = :PersonDopDisp_Year {$filterEPLDD13}
			";
		} else {
			if ($callObject->getRegionNick() == "ufa") {
				$query = str_replace("v_PersonState PS", "v_EvnPLDispDop13 EPLDD13
							inner join v_PersonState PS on PS.Person_id = EPLDD13.Person_id", $query);
			} else {
				$query .= "
					{$joinDopDisp} join v_EvnPLDispDop13 EPLDD13 on PS.Person_id = EPLDD13.Person_id and EPLDD13.Lpu_id {$getLpuIdFilter} and coalesce(EPLDD13.DispClass_id,1) = 1 and date_part('year', EPLDD13.EvnPLDispDop13_consDT) = :PersonDopDisp_Year {$filterEPLDD13}
				";
			}
		}
		$joinDDICData = "outer";
		if (!empty($filterDDICData)) {
			$joinDDICData = "cross";
		}
		$joinDopDispSecond = "left";
		if (!empty($filterDopDispSecond)) {
			$joinDopDispSecond = "inner";
		}
		$query .= "
			{$joinDopDispSecond} join v_EvnPLDispDop13 DopDispSecond on {$mainFilterDopDispSecond} {$filterDopDispSecond}
			left join v_EvnCostPrint ecp on ecp.Evn_id = DopDispSecond.EvnPLDispDop13_id
			left join YesNo IsFinish on IsFinish.YesNo_id = coalesce(EPLDD13.EvnPLDispDop13_IsEndStage, 1)
			left join YesNo IsMobile on IsMobile.YesNo_id = coalesce(EPLDD13.EvnPLDispDop13_isMobile, 1)
			left join v_HealthKind HK on HK.HealthKind_id = EPLDD13.HealthKind_id
			left join v_Address UAdd on UAdd.Address_id = PS.UAddress_id
			left join v_Address PAdd on PAdd.Address_id = PS.PAddress_id
			{$joinDDICData} join lateral(
				select DDIC.DopDispInfoConsent_IsAgree
				from
					v_DopDispInfoConsent DDIC
					left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				where DDIC.EvnPLDisp_id = EPLDD13.EvnPLDispDop13_id
				  and ST.SurveyType_Code = 1
				  {$filterDDICData}
				limit 1
			) as DDICData on true
			left join lateral(
				select coalesce(EUDD.EvnUslugaDispDop_disDate, EUDD.EvnUslugaDispDop_didDate) as EvnUslugaDispDop_disDate
				from
					v_EvnUslugaDispDop EUDD
					left join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
					left join v_UslugaComplex UC on UC.UslugaComplex_id = EUDD.UslugaComplex_id
					left join v_DopDispInfoConsent DDIC on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				where EVDD.EvnVizitDispDop_pid = EPLDD13.EvnPLDispDop13_id
				  and ST.SurveyType_Code = 19
				  and coalesce(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				limit 1
			) as DOCOSMDT on true
			left join v_HealthKind HK_SEC on HK_SEC.HealthKind_id = DopDispSecond.HealthKind_id
			left join YesNo IsFinishSecond on IsFinishSecond.YesNo_id = DopDispSecond.EvnPLDispDop13_IsEndStage
			left join lateral(
				select DDIC.DopDispInfoConsent_IsAgree
				from
					v_DopDispInfoConsent DDIC
					left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				where DDIC.EvnPLDisp_id = DopDispSecond.EvnPLDispDop13_id and ST.SurveyType_Code = 48
				limit 1
			) as DDICDataSecond on true
			left join lateral(
				select EvnPLDispDop13_id, Lpu_id
				from v_EvnPLDispDop13
				where Person_id = PS.Person_id
				  and date_part('year', EvnPLDispDop13_setDate) = :PersonDopDisp_Year
				  and Lpu_id " . getLpuIdFilter($data, true) . "
				  and coalesce(DispClass_id,1) = 1
				  limit 1
			) as EPLDD13AL on true
			left join v_Lpu lpu on lpu.Lpu_id = coalesce(EPLDD13.Lpu_id, EPLDD13AL.Lpu_id)
		";
		if (in_array($data["session"]["region"]["nick"], ["buryatiya", "krym"])) {
			$query .= "
				left join lateral(
					select UslugaComplex_id
					from v_EvnUslugaDispDop
					where EvnUslugaDispDop_IsVizitCode = 2 and EvnUslugaDispDop_pid = DopDispSecond.EvnPLDispDop13_id
					limit 1
				) as euddvizit on true
				left join v_UslugaComplex UC on uc.UslugaComplex_id = euddvizit.UslugaComplex_id
			";
		}
	}

	public static function selectBody_EvnPLDispDop13(&$joinDopDispSecond, &$filterDopDispSecond, $data)
	{
		$filterDopDispSecond = "";
		if (isset($data["EvnPLDispDop13Second_isPaid"]) && $data["session"]["region"]["nick"] != "ufa") {
			$filterDopDispSecond .= " and coalesce(EPLDD13_SEC.EvnPLDispDop13_isPaid, 1) = :EvnPLDispDop13Second_isPaid ";
		}
		if (isset($data["EvnPLDispDop13Second_isMobile"])) {
			$filterDopDispSecond .= " and coalesce(EPLDD13_SEC.EvnPLDispDop13_isMobile, 1) = :EvnPLDispDop13Second_isMobile ";
		}
		if (isset($data["EvnPLDispDop13Second_setDate"])) {
			$filterDopDispSecond .= " and EPLDD13_SEC.EvnPLDispDop13_setDate = :EvnPLDispDop13Second_setDate::timestamp ";
		}
		if (isset($data["EvnPLDispDop13Second_setDate_Range"][0])) {
			$filterDopDispSecond .= " and EPLDD13_SEC.EvnPLDispDop13_setDate >= :EvnPLDispDop13Second_setDate_Range_0::timestamp ";
		}
		if (isset($data["EvnPLDispDop13Second_setDate_Range"][1])) {
			$filterDopDispSecond .= " and EPLDD13_SEC.EvnPLDispDop13_setDate <= :EvnPLDispDop13Second_setDate_Range_1::timestamp ";
		}
		if (isset($data["EvnPLDispDop13Second_disDate"])) {
			$filterDopDispSecond .= " and DopDispSecond.EvnPLDispDop13_disDate = :EvnPLDispDop13Second_disDate::timestamp ";
		}
		if (isset($data["EvnPLDispDop13Second_disDate_Range"][0])) {
			$filterDopDispSecond .= " and EPLDD13_SEC.EvnPLDispDop13_disDate >= :EvnPLDispDop13Second_disDate_Range_0::timestamp ";
		}
		if (isset($data["EvnPLDispDop13Second_disDate_Range"][1])) {
			$filterDopDispSecond .= " and EPLDD13_SEC.EvnPLDispDop13_disDate <= :EvnPLDispDop13Second_disDate_Range_1::timestamp ";
		}
		if (isset($data["EvnPLDispDop13Second_IsFinish"])) {
			$filterDopDispSecond .= " and coalesce(EPLDD13_SEC.EvnPLDispDop13_IsEndStage, 1) = :EvnPLDispDop13Second_IsFinish ";
		}
		if (isset($data["EvnPLDispDop13Second_HealthKind_id"])) {
			$filterDopDispSecond .= " and EPLDD13_SEC.HealthKind_id = :EvnPLDispDop13Second_HealthKind_id ";
		}
		$joinDopDispSecond = (!empty($filterDopDispSecond)) ? "inner" : "left";
		$query = "
			left join v_EvnCostPrint ecp on ecp.Evn_id = EPLDD13.EvnPLDispDop13_id
			left join YesNo IsFinish on IsFinish.YesNo_id = coalesce(EPLDD13.EvnPLDispDop13_IsEndStage, 1)
			left join YesNo IsMobile on IsMobile.YesNo_id = coalesce(EPLDD13.EvnPLDispDop13_isMobile, 1)
			left join v_HealthKind HK on HK.HealthKind_id = EPLDD13.HealthKind_id
			left join v_Address UAdd on UAdd.Address_id = EPLDD13.UAddress_id
			left join v_Address PAdd on PAdd.Address_id = EPLDD13.PAddress_id
			{$joinDopDispSecond} join lateral(
				select
					EPLDD13_SEC.EvnPLDispDop13_id,
					EPLDD13_SEC.EvnPLDispDop13_isPaid,
					EPLDD13_SEC.EvnPLDispDop13_IsTransit,
					EPLDD13_SEC.EvnPLDispDop13_setDate,
					EPLDD13_SEC.EvnPLDispDop13_disDate,
					EPLDD13_SEC.EvnPLDispDop13_consDT,
					EPLDD13_SEC.HealthKind_id,
					coalesce(EPLDD13_SEC.EvnPLDispDop13_IsEndStage, 1) as EvnPLDispDop13_IsEndStage,
					HK_SEC.HealthKind_Name,
					EPLDD13_SEC.EvnPLDispDop13_insDT,
					EPLDD13_SEC.EvnPLDispDop13_updDT,
					EPLDD13_SEC.pmUser_insID,
					EPLDD13_SEC.pmUser_updID
				from
					v_EvnPLDispDop13 EPLDD13_SEC
					left join v_HealthKind HK_SEC on HK_SEC.HealthKind_id = EPLDD13_SEC.HealthKind_id
				where EPLDD13_SEC.EvnPLDispDop13_fid = EPLDD13.EvnPLDispDop13_id {$filterDopDispSecond}
				limit 1
			) as DopDispSecond on true
			left join YesNo IsFinishSecond on IsFinishSecond.YesNo_id = DopDispSecond.EvnPLDispDop13_IsEndStage
			left join lateral(
				select DDIC.DopDispInfoConsent_IsAgree
				from
					v_DopDispInfoConsent DDIC 
					left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				where DDIC.EvnPLDisp_id = DopDispSecond.EvnPLDispDop13_id and ST.SurveyType_Code = 48
				limit 1
			) as DDICDataSecond on true
			left join lateral(
				select EvnPLDispDop13_id, Lpu_id
				from v_EvnPLDispDop13
				where Person_id = EPLDD13.Person_id
				  and date_part('year', EvnPLDispDop13_setDate) = :PersonDopDisp_Year
				  and Lpu_id " . getLpuIdFilter($data, true) . "
				  and coalesce(DispClass_id,1) = 1
				limit 1
			) as EPLDD13AL on true
			left join v_Lpu lpu on lpu.Lpu_id = coalesce(EPLDD13.Lpu_id, EPLDD13AL.Lpu_id)
		";
		if (in_array($data["session"]["region"]["nick"], ["buryatiya", "krym"])) {
			$query .= "
				left join lateral(
					select UslugaComplex_id
					from v_EvnUslugaDispDop
					where EvnUslugaDispDop_IsVizitCode = 2 and EvnUslugaDispDop_pid = EPLDD13.EvnPLDispDop13_id
					limit 1
				) as euddvizit on true
				left join v_UslugaComplex UC on uc.UslugaComplex_id = euddvizit.UslugaComplex_id
			";
		}
		return $query;
	}
	//selectBody_EvnPLDispDop13
}