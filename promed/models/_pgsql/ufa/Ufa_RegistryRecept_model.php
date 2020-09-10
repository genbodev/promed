<?php defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH . 'models/_pgsql/RegistryRecept_model.php');

class Ufa_RegistryRecept_model extends RegistryRecept_model
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
	 *  Список реестров рецептов
	 */
	function farm_loadDrugOstatRegistryList($data)
	{
		$params = array();
		$from = '';
		$filter = "(1=1)";
		$filter .= " and ps.DrugShipment_id = dor.DrugShipment_id";
		$filter .= " and (DOR.DrugOstatRegistry_Kolvo is not null and DOR.DrugOstatRegistry_Kolvo > 0)";

		if (!empty($data['DrugFinance_id'])) {
			$filter .= " and DOR.DrugFinance_id=:DrugFinance_id";
			$params['DrugFinance_id'] = $data['DrugFinance_id'];
		}

		if (!empty($data['OrgType_id'])) {
			$filter .= " and ORG.OrgType_id=:OrgType_id";
			$params['OrgType_id'] = $data['OrgType_id'];
		}

		if (!empty($data['OrgType_Filter'])) {
			switch ($data['OrgType_Filter']) {
				case 'touz':
					$mz_id = $this->getFirstResultFromQuery("select dbo.GetMinzdravDloOrgId() as \"mz_id\";");
					$filter .= " and ORG.Org_id = :Minzdrav_id";
					$params['Minzdrav_id'] = $mz_id;
					break;
				case 'mo':
					$filter .= " and org_type.OrgType_Code = 11"; //11 - МО (Медицинская организация);
					break;
				case 'supplier':
					$filter .= " and org_type.OrgType_Code = 16"; //16 - Поставщик;
					break;
				case 'farmacy_and_store':
					$filter .= " and org_type.OrgType_Code in (4, 5)"; //4 - Аптека; 5 - Региональный склад ДЛО;
					break;
			}
			//$filter .= " and ORG.OrgType_id=:OrgType_id";
		}

		if (!empty($data['Org_id'])) {
			//$data['Org_id']  = '68320121203';
			$filter .= " and DOR.Org_id=:Org_id";
			$params['Org_id'] = $data['Org_id'];
		}

		if (!empty($data['Storage_id'])) {
			$filter .= " and DOR.Storage_id=:Storage_id";
			$params['Storage_id'] = $data['Storage_id'];
		} else if (!empty($data['LpuBuilding_id']) || !empty($data['LpuSection_id'])) {
			$struct_filter = "1=1";
			if (!empty($data['LpuBuilding_id'])) {
				$struct_filter .= " and SSL.LpuBuilding_id = :LpuBuilding_id";
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
			}
			if (!empty($data['LpuSection_id'])) {
				$struct_filter .= " and SSL.LpuSection_id = :LpuSection_id";
				$params['LpuSection_id'] = $data['LpuSection_id'];
			}
			$filter .= " and STOR.Storage_id in (
				select Storage_id
				from v_StorageStructLevel SSL
				where {$struct_filter}
			)";
		}

		if (!empty($data['SubAccountType_id'])) {
			$filter .= " and DOR.SubAccountType_id=:SubAccountType_id";
			$params['SubAccountType_id'] = $data['SubAccountType_id'];
		}

		if (!empty($data['WhsDocumentCostItemType_id'])) {
			$filter .= " and PS.WhsDocumentCostItemType_id=:WhsDocumentCostItemType_id";
			$params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		}

		if (!empty($data['WhsDocumentUc_Date'])) {
			$filter .= " and WDS.WhsDocumentUc_Date=:WhsDocumentUc_Date";
			$params['WhsDocumentUc_Date'] = $data['WhsDocumentUc_Date'];
		}

		if (!empty($data['WhsDocumentUc_Num'])) {
			$filter .= " and WDS.WhsDocumentUc_Num=:WhsDocumentUc_Num";
			$params['WhsDocumentUc_Num'] = $data['WhsDocumentUc_Num'];
		}

		if (!empty($data['WhsDocumentUc_Name'])) {
			$filter .= " and WDS.WhsDocumentUc_Name iLIKE :WhsDocumentUc_Name||'%'";
			$params['WhsDocumentUc_Name'] = $data['WhsDocumentUc_Name'];
		}

		if (!empty($data['AllowReservation']) && $data['AllowReservation'] == 1) {
			$from .= "
				left join lateral(
					select
						SUM(ER.EvnRecept_Kolvo) as SumEvnRecept_Kolvo
					from
						v_OrgFarmacy OrgF
						left join v_EvnRecept ER on ER.OrgFarmacy_id = OrgF.OrgFarmacy_id
						left join v_YesNo IsNotOstat on IsNotOstat.YesNo_id = ER.EvnRecept_IsNotOstat
					where
						OrgF.Org_id = DOR.Org_id
						and ER.Drug_rlsid = DOR.Drug_id
						and ER.WhsDocumentCostItemType_id = DOR.WhsDocumentCostItemType_id
						and ER.ReceptDelayType_id is null
						and DATEDIFF('day', ER.EvnRecept_setDate, GETDATE())<=3
						and (ER.EvnRecept_IsNotOstat is null or IsNotOstat.YesNo_Code = 0)
						and DOR.SubAccountType_id = 1
						and DOR.SubAccountType_id in (1, 2)
					group by
						ER.Drug_id
				) SER on true
			";
			$filter .= " and (COALESCE(DOR.DrugOstatRegistry_Kolvo, 0) - COALESCE(SER.SumEvnRecept_Kolvo, 0)) > 0";
		} else {
			$from .= "
				left join lateral(
					select 0 as SumEvnRecept_Kolvo
				) SER on true
			";
		}

		if (!empty($data['DrugNomen_Code'])) {
			//$filter .= ' and DN.DrugNomen_Code = :DrugNomen_Code';
			$filter .= ' and d.Drug_Code = :DrugNomen_Code';
			$params['DrugNomen_Code'] = $data['DrugNomen_Code'];
		}

		if (!empty($data['DrugComplexMnnCode_Code'])) {
			$from .= " left join rls.v_DrugComplexMnnCode DCMC on DCMC.DrugComplexMnnCode_id = DN.DrugComplexMnnCode_id";
			$filter .= ' and DCMC.DrugComplexMnnCode_Code = :DrugComplexMnnCode_Code';
			$params['DrugComplexMnnCode_Code'] = $data['DrugComplexMnnCode_Code'];
		}

		if (!empty($data['RlsActmatters_RusName'])) {
			$filter .= " and AM.drugMnn_name ilike :RlsActmatters_RusName||'%'";
			$params['RlsActmatters_RusName'] = $data['RlsActmatters_RusName'];
		}

		if (!empty($data['RlsTorg_Name'])) {
			$filter .= " and TN.DrugTorg_NAME ilike :RlsTorg_Name||'%'";
			$params['RlsTorg_Name'] = $data['RlsTorg_Name'];
		}

		if (!empty($data['RlsClsdrugforms_Name'])) {
			/*
$from .= " left join rls.CLSDRUGFORMS CDF with(nolock) on CDF.CLSDRUGFORMS_ID = DCM.CLSDRUGFORMS_ID";
$filter .= " and CDF.FULLNAME like :RlsClsdrugforms_Name+'%'";
$params['RlsClsdrugforms_Name'] = $data['RlsClsdrugforms_Name'];
			*/
			$from .= " left join DrugForm CDF on CDF.DrugForm_id = D.DrugForm_id ";
			$filter .= " and CDF.DrugForm_Name ilike :RlsClsdrugforms_Name||'%'";
			$params['RlsClsdrugforms_Name'] = $data['RlsClsdrugforms_Name'];
		}

		if (!empty($data['PrepSeries_isDefect'])) {
			$filter .= " and COALESCE(isdef.YesNo_Code, 0) = :PrepSeries_isDefect";
			$params['PrepSeries_isDefect'] = $data['PrepSeries_isDefect'];
		}

		if (!empty($data['PrepSeries_godnMinMonthCount'])) {
			//$filter .= " and (PS.DocumentUcStr_godnDate is null or datediff(month, dbo.tzGetDate(), PS.DocumentUcStr_godnDate) >= :PrepSeries_godnMinMonthCount)";
			// По просьбе заказчика изменен месяц на день (задача 81233)
			$filter .= " and (PS.DocumentUcStr_godnDate is null or datediff('day', dbo.tzGetDate(), PS.DocumentUcStr_godnDate) >= :PrepSeries_godnMinMonthCount)";
			$params['PrepSeries_godnMinMonthCount'] = $data['PrepSeries_godnMinMonthCount'];
		}

		if (!empty($data['PrepSeries_godnMaxMonthCount'])) {
			//$filter .= " and (PS.DocumentUcStr_godnDate is null or datediff(month, dbo.tzGetDate(), PS.DocumentUcStr_godnDate) <= :PrepSeries_godnMaxMonthCount)";
			// По просьбе заказчика изменен месяц на день (задача 81233)
			$filter .= " and (PS.DocumentUcStr_godnDate is null or datediff('day', dbo.tzGetDate(), PS.DocumentUcStr_godnDate) <= :PrepSeries_godnMaxMonthCount)";
			$params['PrepSeries_godnMaxMonthCount'] = $data['PrepSeries_godnMaxMonthCount'];
		}

		if (!empty($data['LastUpdateDayCount'])) {
			$filter .= " and datediff('day', DOR.DrugOstatRegistry_updDT, dbo.tzGetDate()) >= :LastUpdateDayCount";
			$params['LastUpdateDayCount'] = $data['LastUpdateDayCount'];
		}

		if (!empty($data['STRONGGROUPS_ID'])) {
			$filter .= ' and AM.STRONGGROUPID = :STRONGGROUPS_ID';
			$params['STRONGGROUPS_ID'] = $data['STRONGGROUPS_ID'];
		}

		if (!empty($data['NARCOGROUPS_ID'])) {
			$filter .= ' and AM.NARCOGROUPID = :NARCOGROUPS_ID';
			$params['NARCOGROUPS_ID'] = $data['NARCOGROUPS_ID'];
		}

		if (!empty($data['FIRMS_ID'])) {
			$filter .= ' and P.FIRMID = :FIRMS_ID';
			$params['FIRMS_ID'] = $data['FIRMS_ID'];
		}

		if (!empty($data['COUNTRIES_ID'])) {
			$filter .= ' and F.COUNTID = :COUNTRIES_ID';
			$params['COUNTRIES_ID'] = $data['COUNTRIES_ID'];
		}

		if (!empty($data['WhsDocumentSupply_Year'])) {
			$filter .= ' and fin_year.yr = :WhsDocumentSupply_Year';
			$params['WhsDocumentSupply_Year'] = $data['WhsDocumentSupply_Year'];
		}

		if (!empty($data['CLSATC_ID'])) {
			//Получение списка потомков для фильтрации по ним
			$query = "
				with recursive Rec(CLSATC_ID)
				as
				(
					select t.CLSATC_ID
					from rls.v_CLSATC t
					where
						t.CLSATC_ID = :CLSATC_ID
					union all
					select t.CLSATC_ID
					from rls.v_CLSATC t
						join Rec R on t.PARENTID = R.CLSATC_ID
				)
				select
					R.CLSATC_ID as \"CLSATC_ID\"
				from Rec R
			";
			$result = $this->db->query($query, array(
				'CLSATC_ID' => $data['CLSATC_ID']
			));
			if (is_object($result)) {
				$res_arr = $result->result('array');
				if (is_array($res_arr) && !empty($res_arr)) {
					$atc_arr = array();
					foreach ($res_arr as $row) {
						$atc_arr[] = $row['CLSATC_ID'];
					}
					$atc_arr = empty($atc_arr) ? 'null' : implode(',', $atc_arr);

					$from .= " inner join rls.v_PREP_ATC PA on PA.PREPID = D.DrugPrep_id";
					$filter .= " and PA.UNIQID in ({$atc_arr})";
				}
			}
		}

		if (!empty($data['CLSPHARMAGROUP_ID'])) {
			//Получение списка потомков для фильтрации по ним
			$query = "
				with recursive Rec(CLSPHARMAGROUP_ID)
				as
				(
					select t.CLSPHARMAGROUP_ID
					from rls.v_CLSPHARMAGROUP t
					where
						t.CLSPHARMAGROUP_ID = :CLSPHARMAGROUP_ID
					union all
					select t.CLSPHARMAGROUP_ID
					from rls.v_CLSPHARMAGROUP t
						join Rec R on t.PARENTID = R.CLSPHARMAGROUP_ID
				)
				select
					R.CLSPHARMAGROUP_ID as \"CLSPHARMAGROUP_ID\"
				from Rec R
			";
			$result = $this->db->query($query, array(
				'CLSPHARMAGROUP_ID' => $data['CLSPHARMAGROUP_ID']
			));
			if (is_object($result)) {
				$res_arr = $result->result('array');
				if (is_array($res_arr) && !empty($res_arr)) {
					$ph_gr_arr = array();
					foreach ($res_arr as $row) {
						$ph_gr_arr[] = $row['CLSPHARMAGROUP_ID'];
					}
					$ph_gr_str = empty($ph_gr_arr) ? 'null' : implode(',', $ph_gr_arr);
					$from .= " inner join rls.v_PREP_PHARMAGROUP PP on PP.PREPID = D.DrugPrep_id";
					$filter .= " and PP.UNIQID in ({$ph_gr_str})";
				}
			}
		}

		if (!empty($data['CLS_MZ_PHGROUP_ID'])) {
			//Получение списка потомков для фильтрации по ним
			$query = "
				with recursive Rec(CLS_MZ_PHGROUP_ID)
				as
				(
					select t.CLS_MZ_PHGROUP_ID
					from rls.v_CLS_MZ_PHGROUP t
					where
						t.CLS_MZ_PHGROUP_ID = :CLS_MZ_PHGROUP_ID
					union all
					select t.CLS_MZ_PHGROUP_ID
					from rls.v_CLS_MZ_PHGROUP t
						join Rec R on t.PARENTID = R.CLS_MZ_PHGROUP_ID
				)
				select
					R.CLS_MZ_PHGROUP_ID as \"CLS_MZ_PHGROUP_ID\"
				from Rec R
			";
			$result = $this->db->query($query, array(
				'CLS_MZ_PHGROUP_ID' => $data['CLS_MZ_PHGROUP_ID']
			));
			if (is_object($result)) {
				$res_arr = $result->result('array');
				if (is_array($res_arr) && !empty($res_arr)) {
					$mz_pg_arr = array();
					foreach ($res_arr as $row) {
						$mz_pg_arr[] = $row['CLS_MZ_PHGROUP_ID'];
					}
					$mz_pg_str = empty($mz_pg_arr) ? 'null' : implode(',', $mz_pg_arr);
					$from .= " inner join rls.TRADENAMES_DRUGFORMS TD on TD.TRADENAMEID = P.TRADENAMEID and TD.DRUGFORMID = DCM.CLSDRUGFORMS_ID";
					$filter .= " and TD.MZ_PHGR_ID in ({$mz_pg_str})";
				}
			}
		}

		if (!empty($data['KLAreaStat_id'])) {
			$filter .= " and area_stat.KLAreaStat_id = :KLAreaStat_id";
			$params['KLAreaStat_id'] = $data['KLAreaStat_id'];
		}


		$query = "
			Select
				-- select
				DOR.DrugOstatRegistry_id as \"DrugOstatRegistry_id\",
				DOR.Org_id as \"Org_id\",
				org_area.area_name as \"Org_Area\",
				ORG.Org_Nick as \"Org_Name\",
				DOR.Storage_id as \"Storage_id\",
				STOR.Storage_Name as \"Storage_Name\",
				COALESCE(SAT.SubAccountType_Name, '') as \"SubAccountType_Name\",
				DOR.Drug_did as \"Drug_did\",
                DOR.Drug_did as \"Drug_id\",
				COALESCE(D.Drug_Name, '') as \"Drug_Name\",
				
				
				CAST(COALESCE(DOR.DrugOstatRegistry_Kolvo, 0) - COALESCE(SER.SumEvnRecept_Kolvo, 0) as numeric(10, 2)) as \"DrugOstatRegistry_Kolvo\",
				case
					when COALESCE(SER.SumEvnRecept_Kolvo,0) > 0 then
						CAST(COALESCE(DOR.DrugOstatRegistry_Sum - DOR.DrugOstatRegistry_Sum/DOR.DrugOstatRegistry_Kolvo*COALESCE(SER.SumEvnRecept_Kolvo, 0.00), 0.00) as numeric(10, 2))
					else
						cast(COALESCE(DOR.DrugOstatRegistry_Sum, 0.00) as numeric(10, 2))
				end as \"DrugOstatRegistry_Sum\",
				
				CAST(DOR.DrugOstatRegistry_Cost as numeric(10, 2)) as \"DrugOstatRegistry_Price\",
				
				COALESCE(O.Okei_Name, '') as \"Okei_Name\",
				WDS.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				--fin_year.yr as WhsDocumentSupply_Year,
				WDS.WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
				
				to_char(WDS.WhsDocumentUc_Date,'dd.mm.yyyy') as \"WhsDocumentUc_Date\",
				DOR.DrugFinance_id as \"DrugFinance_id\",
				PS.DrugFinance_Name as \"DrugFinance_Name\",
				DOR.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				PS.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
				AM.DrugMnn_Name as \"ActMatters_RusName\",
				TN.DrugTorg_Name  as \"Prep_Name\",
				DFORM.DrugForm_Name  as \"DrugForm_Name\",
				Drug_DoseQ || ' ' ||  Drug_DoseEi as \"Drug_Dose\",
				Case when d.Drug_Vol is not null and d.Drug_Vol > 0 then cast(d.Drug_vol as varchar) || ' ' || fas.DrugEdVol_Name else '' end ||
				Case when d.Drug_Vol is not null and d.Drug_Vol > 0 and Drug_Fas is not null then ', ' else '' end ||
				case
					when Drug_Fas is not null
						then '№ ' || rtrim(cast(Drug_Fas as varchar))
					else ''	
				end as \"Drug_Fas\",
				
				null as \"Firm_Name\",
				null as \"Reg_Num\",
				D.Drug_Code as \"DrugNomen_Code\",
                DocumentUcStr_Ser as \"PrepSeries_Ser\",
				--PS.PrepSeries_Ser,
				to_char(PS.DocumentUcStr_godnDate,'dd.mm.yyyy') as \"PrepSeries_GodnDate\",
                                Case  --  Устанавливаем 'Критичность' срока годности
					when PS.DocumentUcStr_godnDate IS Null
						then 1        
					when DATEADD('month', -3, PS.DocumentUcStr_godnDate) < GETDATE ()
						Then 1
					else 0	
				end as \"GodnDate_Ctrl\",
				DOR.DrugShipment_id as \"DrugShipment_id\",
				to_char(DS.DrugShipment_setDT,'dd.mm.yyyy') as \"DrugShipment_setDT\",
				--d.Drug_Code DrugShipment_Name
				DS.DrugShipment_Name as \"DrugShipment_Name\" 
				-- end select
			FROM  
                                -- from
				v_DrugOstatRegistry DOR
				left join dbo.v_Drug D on D.Drug_id = DOR.Drug_did
                left join DrugEdvol Fas  on   Fas.DrugEdvol_id = D.DrugEdvol_id
				left join v_Okei O on O.Okei_id = DOR.Okei_id
				left join v_DrugShipment DS on DS.DrugShipment_id = DOR.DrugShipment_id
				left join v_WhsDocumentSupply WDS on WDS.WhsDocumentSupply_id = DS.WhsDocumentSupply_id
				--left join v_DrugFinance DF with (nolock) on DF.DrugFinance_id = DOR.DrugFinance_id
				--left join v_WhsDocumentCostItemType WDCIT with (nolock) on WDCIT.WhsDocumentCostItemType_id = DOR.WhsDocumentCostItemType_id
				left join v_Org ORG on ORG.Org_id = DOR.Org_id
				left join v_OrgType org_type on org_type.OrgType_id = ORG.OrgType_id
				left join v_Address org_ua on org_ua.Address_id = ORG.UAddress_id
				left join v_Address org_pa on org_pa.Address_id = ORG.PAddress_id
				left join v_Storage STOR on STOR.Storage_id = DOR.Storage_id
				left join v_SubAccountType SAT on SAT.SubAccountType_id = DOR.SubAccountType_id
				--left join rls.v_DrugComplexMnn DCM with(nolock) on DCM.DrugComplexMnn_id = D.DrugComplexMnn_id
				--left join rls.v_DrugComplexMnnName DCMN with(nolock) on DCMN.DrugComplexMnnName_id = DCM.DrugComplexMnnName_id
				--left join rls.v_ACTMATTERS AM with(nolock) on AM.ACTMATTERS_ID = DCMN.ActMatters_id
				left join dbo.DrugMnn AM on AM.DrugMnn_id = d.DrugMnn_id
				--left join rls.v_prep P with(nolock) on P.Prep_id = D.DrugPrep_id
				--left join rls.PrepSeries PS with (nolock) on PS.PrepSeries_id = DOR.PrepSeries_id
				left join v_YesNo isdef on isdef.YesNo_id = 1-- ps.PrepSeries_isDefect  Временно без дефектов
			
				left join dbo.DrugTorg TN  on TN.DrugTorg_id = D.DrugTorg_id
				
				--left join rls.v_FIRMS F with(nolock) on F.FIRMS_ID = P.FIRMID
				left join dbo.DrugForm DFORM on DFORM.DrugForm_ID = D.DrugForm_ID
				--left join rls.REGCERT RC with (nolock) on RC.REGCERT_ID = P.REGCERTID
				--left join rls.MASSUNITS DFMU with (nolock) on DFMU.MASSUNITS_ID = P.DFMASSID
				--left join rls.CONCENUNITS DFCU with (nolock) on DFCU.CONCENUNITS_ID = P.DFCONCID
				--left join rls.ACTUNITS DFAU with (nolock) on DFAU.ACTUNITS_ID = P.DFACTID
				--left join rls.SIZEUNITS DFSU with (nolock) on DFSU.SIZEUNITS_ID = P.DFSIZEID
				left join rls.v_Nomen N on N.NOMEN_ID = DOR.Drug_id
				left join rls.MASSUNITS MU on MU.MASSUNITS_ID = N.PPACKMASSUNID
				left join rls.CUBICUNITS CU on CU.CUBICUNITS_ID = N.PPACKCUBUNID

				left join lateral (
					select
						coalesce(i_kl_t.KLTown_Name, i_kl_c.KLCity_Name, i_kl_sr.KLSubRgn_FullName, i_kl_r.KLRgn_FullName) as area_name,
						i_ost.KLCountry_id,
						i_ost.KLRgn_id,
						i_ost.KLSubRgn_id,
						i_ost.KLCity_id,
						i_ost.KLTown_id
					from
						v_OrgServiceTerr i_ost
						left join v_KLRgn i_kl_r on i_kl_r.KLRgn_id = i_ost.KLRgn_id
						left join v_KLSubRgn i_kl_sr on i_kl_sr.KLSubRgn_id = i_ost.KLSubRgn_id
						left join v_KLCity i_kl_c on i_kl_c.KLCity_id = i_ost.KLCity_id
						left join v_KLTown i_kl_t on i_kl_t.KLTown_id = i_ost.KLTown_id
					where
						i_ost.Org_id = ORG.Org_id
					limit 1
				) org_area on true

				left join lateral (
					select
						i_p.KLAreaStat_id,
						i_p.KLArea_Name
					from (
						select
							oa_area.KLAreaStat_id,
							oa_area.KLArea_Name
						from
							v_KLAreaStat oa_area
						where
							(oa_area.KLRgn_id is null or oa_area.KLRgn_id = org_area.KLRgn_id) and
							(oa_area.KLSubRgn_id is null or oa_area.KLSubRgn_id = org_area.KLSubRgn_id) and
							(oa_area.KLCity_id is null or oa_area.KLCity_id = org_area.KLCity_id) and
							(oa_area.KLTown_id is null or oa_area.KLTown_id = org_area.KLTown_id)
						limit 1
					) i_p
				) area_stat on true
                left join lateral (
					select
						s.DocumentUcStr_id, ln.DrugShipment_id,
					DocumentUcStr_godnDate, DocumentUcStr_Ser, s.DrugFinance_id, DF.DrugFinance_Name, WDCIT.WhsDocumentCostItemType_id, WDCIT.WhsDocumentCostItemType_Name
					from
						dbo.DocumentUcStr s
						inner join v_DrugShipmentlink ln on ln.DocumentUcStr_id = s.DocumentUcStr_id
							and ln.DrugShipment_id = dor.DrugShipment_id
						left join v_DrugFinance DF on DF.DrugFinance_id = dor.DrugFinance_id
						inner join dbo.DocumentUc Doc on Doc.DocumentUc_id= s.DocumentUc_id
							and Doc.Org_id = DOR.Org_id
						inner join v_WhsDocumentCostItemType WDCIT on WDCIT.WhsDocumentCostItemType_id = Dor.WhsDocumentCostItemType_id
					where
						Drug_id = D.Drug_id
					limit 1
				) PS on true
                                
				{$from}
			-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				--DOR.DrugOstatRegistry_id
				SAT.SubAccountType_Name,
				D.Drug_Name  
				-- end order by";

		if (isset($data['export']) && $data['export']) {
			$result = $this->db->query($query, $params);
			if (is_object($result)) {
				return $result->result('array');
			} else {
				return false;
			}
		} else {
			//echo getDebugSQL($query, $params); exit();

			//$dbrep = $this->load->database('bdwork', true);
			$dbrep = $this->db;

			$result = $dbrep->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
			$result_count = $dbrep->query(getCountSQLPH($query), $params);

			if (is_object($result_count)) {
				$cnt_arr = $result_count->result('array');
				$count = $cnt_arr[0]['cnt'];
				unset($cnt_arr);
			} else {
				$count = 0;
			}

			if (is_object($result)) {
				$response = array();
				$response['data'] = $result->result('array');
				$response['totalCount'] = $count;
				return $response;
			} else {
				return false;
			}
		}

	}

	/**
	 * Получение данных файла P для выгрузки рецептов
	 */
	function ufaExportPL2dbf_Pers($data)
	{

		$params = array();

		$query = "
			Select
				SS as \"SS\"
				,SN_POL as \"SN_POL\"
				,FAM as \"FAM\"
				,IM as \"IM\"
				,OT as \"OT\"
				,W as \"W\"
				,DR as \"DR\"
				,C_KATL as \"C_KATL\"
				,SN_DOC as \"SN_DOC\"
				,C_DOC as \"C_DOC\"
				,OKATO_OMS as \"OKATO_OMS\"
				,QM_OGRN as \"QM_OGRN\"
				,OKATO_REG as \"OKATO_REG\"
				,D_TYPE as \"D_TYPE\"
		    from tmp.fn_ufaExportPL2dbfPers
		    (:dtBeg, :dtEnd, :WhsDocumentCostItemType_id);
		";

		$params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		$params['BegDate'] = $data['PeriodRange'][0];
		$params['EndDate'] = $data['PeriodRange'][1];


		//$dbrep = $this->load->database('bdwork', true);

		$dbrep = $this->db;


		$dbrep->query_timeout = 600;
		$result = $dbrep->query($query, $params);

		if (is_object($result)) {
			$response['data'] = $result->result('array');
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных файла L для выгрузки рецептов
	 */
	function ufaExportPL2dbf_L($data)
	{

		$params = array();

		$query = "
			Select
				 SS as \"SS\"
				,OKATO_REG as \"OKATO_REG\"
				,OGRN as \"OGRN\"
				,MCOD as \"MCOD\"
				,PCOD as \"PCOD\"
				,DS as \"DS\"
				,SN_LR as \"SN_LR\"
				,DATE_VR as \"DATE_VR\"
				,C_FINL as \"C_FINL\"
				,PR_LR as \"PR_LR\"
				,A_COD as \"A_COD\"
				,NOMK_LS as \"NOMK_LS\"
				,KO_ALL as \"KO_ALL\"
				,DOZ_ME as \"DOZ_ME\"
				,C_PFS as \"C_PFS\"
				,DATE_OBR as \"DATE_OBR\"
				,DATE_OTP as \"DATE_OTP\"
				,SL_ALL as \"SL_ALL\"
				,TYPE_SCHET as \"TYPE_SCHET\"
				,FO_OGRN as\"FO_OGRN\"
				,P_KEK as \"P_KEK\"
				,D_TYPE as \"D_TYPE\"
				,LINEID as \"LINEID\"
				,OWNER as \"OWNER\"
				,RAS as \"RAS\"
				,SPR_TYPE as \"SPR_TYPE\"
				,SPECIFIC as \"SPECIFIC\"
		    from tmp.fn_ufaExportPL2dbfL
			(:dtBeg, :dtEnd, :WhsDocumentCostItemType_id);
		";

		$params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		$params['BegDate'] = $data['PeriodRange'][0];
		$params['EndDate'] = $data['PeriodRange'][1];


		//$dbrep = $this->load->database('bdwork', true);

		$dbrep = $this->db;

		//echo getDebugSQL($query, $params); exit();

		$dbrep->query_timeout = 6000;

		$result = $dbrep->query($query, $params);

		if (is_object($result)) {
			$response['data'] = $result->result('array');
			return $response;
		} else {
			return false;
		}

	}

	/**
	 * Выгрузка справочника врачей
	 */
	function ufaExportCVF2dbf()
	{

		/*
		$query2 = "
			 SELECT TF_OKATO
			,MCOD
			,PCOD
			,FAM_V
			,IM_V
			,OT_V
			,C_OGRN
			,PRVD
			,D_JOB
			,D_PRIK
			,D_SER
			,PRVS
			,KV_KAT
			,DATE_B
			,DATE_E
			,MSG_TEXT
			FROM tmp.v_CVF
	     ";
		 */

		$query = "
			 SELECT
				 TF_OKATO as \"TF_OKATO\"
				,MCOD as \"MCOD\"
				,PCOD as \"PCOD\"
				,FAM_V as \"FAM_V\"
				,IM_V as \"IM_V\"
				,OT_V as \"OT_V\"
				,C_OGRN as \"C_OGRN\"
				,PRVD as \"PRVD\"
				,D_JOB as \"D_JOB\"
				,D_PRIK as \"D_PRIK\"
				,D_SER as \"D_SER\"
				,PRVS as \"PRVS\"
				,KV_KAT as \"KV_KAT\"
				,DATE_B as \"DATE_B\"
				,DATE_E as \"DATE_E\"
				,MSG_TEXT as \"MSG_TEXT\"
			FROM (
				SELECT
					msf.MedPersonal_id,
					msf.MedStaffFact_id,
					lpu.lpu_id,
					lpu.Lpu_OKATO TF_OKATO,
					lpu.Lpu_f003mcod MCOD,
					lpu.Lpu_OGRN || ' ' || cast(msf.MedPersonal_id as varchar) PCOD,
					msf.Person_SurName  FAM_V,
					msf.Person_FirName IM_V,
					msf.Person_SecName OT_V,
					lpu.Lpu_OGRN C_OGRN,
					ps.PostMed_Code PRVD,
					ps.PostMed_Name D_JOB,
					msf.PostOccupationType_id AS PostOccupationType_id, --Тип занятия должности: 1 - основное место работы; 2 - внутреннее совместительство; 3 - внешнее совместительство
					to_char(WorkPlace4DloApply_updDT, 'yyyymmdd') D_PRIK,
					--WorkPlace4DloApply_updDT D_PRIK,
					to_char(crt.CertificateReceipDate, 'yyyymmdd') D_SER,
					--crt.CertificateReceipDate D_SER,
					null PRVS,
					null KV_KAT,
					to_char(msf.WorkData_begDate, 'yyyymmdd') DATE_B,
					--msf.WorkData_begDate DATE_B,
					to_char(msf.WorkData_endDate, 'yyyymmdd') DATE_E,
					--msf.WorkData_endDate DATE_E,
					'с ' || to_char(WorkPlace4DloApply_updDT, 'dd.mm.yyyy') MSG_TEXT,
					msf.WorkData_begDate,
					msf.WorkData_endDate,
					msf.WorkData_dlobegDate,
					msf.WorkData_dloendDate,
					case
						when Post.name not like '%фельдш%'
						then 'врач'
						else Null
					end doctor,
					case
						when Post.name  like '%фельдш%'
						then 'Фельдшер'
						else Null
					end paramedic
				
				FROM
					-- from
					v_MedStaffFact msf
					--left join  dbo.MedPersonalCache Cache with (nolock) on Cache.Person_id = msf.Person_id
					left join  dbo.WorkPlace4DloApply app on app.MedStaffFact_id = msf.MedStaffFact_id
					and app.WorkPlace4DloApplyStatus_id = 0
					INNER JOIN v_Lpu Lpu on Lpu.Lpu_id=msf.Lpu_id and lpu.lpu_id <> 4
					INNER JOIN v_LpuPeriodDLO  LpuDlo on LpuDlo.Lpu_id=msf.Lpu_id
					and LpuDlo.LpuPeriodDLO_begDate <= GetDate() and COALESCE(LpuDlo.LpuPeriodDLO_endDate, getDate()) >= GetDate()
					INNER JOIN v_LpuUnit lu on lu.LpuUnit_id=msf.LpuUnit_id  and  LpuUnitType_SysNick in('polka', 'fap')
					INNER JOIN v_LpuSection ls on ls.LpuSection_id=msf.LpuSection_id  and COALESCE(LpuSectionProfile_SysNick, '') <> 'priem'
					LEFT JOIN v_PostMed ps on ps.PostMed_id=msf.Post_id
					left join lateral (Select max(crt.CertificateReceipDate) CertificateReceipDate  from  persis.Certificate crt where  crt.MedWorker_id = msf.MedPersonal_id) crt on true
					left join persis.v_Post Post on Post.id = msf.Post_id
				where
					(1=1)
					and msf.WorkData_dlobegDate <= getDate()
					and (msf.WorkData_dloendDate>= getDate() or msf.WorkData_dloendDate is null) and msf.MedPersonal_Code is not null
					and LpuSection_Name <> 'Фиктивные ставки'
					-- берем уволенных за последнии три года
					and isnull(msf.WorkData_endDate, getDate()) >= DATEADD('year', -3, GetDate ())
				-- end where
			) t
		";

		//$dbrep = $this->load->database('bdwork', true);

		$dbrep = $this->db;

		//echo getDebugSQL($query, $params); exit();

		$dbrep->query_timeout = 6000;

		$result = $dbrep->query($query);

		if (is_object($result)) {
			$response['data'] = $result->result('array');
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Экстренный импорт данных из Башфармации
	 */
	function ufaExtraImportDataBF($data)
	{

		$params = array();

		$query = "
			SELECT
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			FROM r2.p_ExtraImportDataBF (
				Mode := :Mode
			);
		";

		$params['Mode'] = $data['Mode'];


		//$dbrep = $this->load->database('bdwork', true);

		$dbrep = $this->db;

		//echo getDebugSQL($query, $params); exit();

		$dbrep->query_timeout = 6000;

		$result = $dbrep->query($query, $params);

		if (is_object($result)) {
			$response['data'] = $result->result('array');
			return $response;
		} else {
			return false;
		}

	}


}