<?php defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH.'models/RegistryRecept_model.php');

class Ufa_RegistryRecept_model extends RegistryRecept_model {
	/**
	 * construct
	 */
	function __construct() {
		//parent::__construct();
            parent::__construct();
	}
        
        	/**
	 *  Список реестров рецептов
	 */
	function farm_loadDrugOstatRegistryList($data) {
		$params = array();
		$from = '';
		$filter="(1=1)";
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
			switch($data['OrgType_Filter']) {
				case 'touz':
					$mz_id = $this->getFirstResultFromQuery("select dbo.GetMinzdravDloOrgId() as mz_id;");
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
				from v_StorageStructLevel SSL with(nolock)
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
			$filter .= " and WDS.WhsDocumentUc_Name LIKE :WhsDocumentUc_Name+'%'";
			$params['WhsDocumentUc_Name'] = $data['WhsDocumentUc_Name'];
		}

		if (!empty($data['AllowReservation']) && $data['AllowReservation'] == 1) {
			$from .= "
				outer apply(
					select
						SUM(ER.EvnRecept_Kolvo) as SumEvnRecept_Kolvo
					from
						v_OrgFarmacy OrgF with(nolock)
						left join v_EvnRecept ER with(nolock) on ER.OrgFarmacy_id = OrgF.OrgFarmacy_id
						left join v_YesNo IsNotOstat with(nolock) on IsNotOstat.YesNo_id = ER.EvnRecept_IsNotOstat
					where
						OrgF.Org_id = DOR.Org_id
						and ER.Drug_rlsid = DOR.Drug_id
						and ER.WhsDocumentCostItemType_id = DOR.WhsDocumentCostItemType_id
						and ER.ReceptDelayType_id is null
						and DATEDIFF(DAY, ER.EvnRecept_setDate, GETDATE())<=3
						and (ER.EvnRecept_IsNotOstat is null or IsNotOstat.YesNo_Code = 0)
						and DOR.SubAccountType_id = 1
						and DOR.SubAccountType_id in (1, 2)
					group by
						ER.Drug_id
				) SER
			";
			$filter .= " and (ISNULL(DOR.DrugOstatRegistry_Kolvo, 0) - ISNULL(SER.SumEvnRecept_Kolvo, 0)) > 0";
		} else {
			$from .= "
				outer apply(
					select 0 as SumEvnRecept_Kolvo
				) SER
			";
		}

		if (!empty($data['DrugNomen_Code'])) {
			//$filter .= ' and DN.DrugNomen_Code = :DrugNomen_Code';
			$filter .= ' and d.Drug_Code = :DrugNomen_Code';
			$params['DrugNomen_Code'] = $data['DrugNomen_Code'];
		}

		if (!empty($data['DrugComplexMnnCode_Code'])) {
			$from .= " left join rls.v_DrugComplexMnnCode DCMC with(nolock) on DCMC.DrugComplexMnnCode_id = DN.DrugComplexMnnCode_id";
			$filter .= ' and DCMC.DrugComplexMnnCode_Code = :DrugComplexMnnCode_Code';
			$params['DrugComplexMnnCode_Code'] = $data['DrugComplexMnnCode_Code'];
		}

		if (!empty($data['RlsActmatters_RusName'])) {
			$filter .= " and AM.drugMnn_name like :RlsActmatters_RusName+'%'";
			$params['RlsActmatters_RusName'] = $data['RlsActmatters_RusName'];
		}

		if (!empty($data['RlsTorg_Name'])) {
			$filter .= " and TN.DrugTorg_NAME like :RlsTorg_Name+'%'";
			$params['RlsTorg_Name'] = $data['RlsTorg_Name'];
		}

		if (!empty($data['RlsClsdrugforms_Name'])) {
                        /*
			$from .= " left join rls.CLSDRUGFORMS CDF with(nolock) on CDF.CLSDRUGFORMS_ID = DCM.CLSDRUGFORMS_ID";
			$filter .= " and CDF.FULLNAME like :RlsClsdrugforms_Name+'%'";
			$params['RlsClsdrugforms_Name'] = $data['RlsClsdrugforms_Name'];
                        */
                        $from .= " left join DrugForm CDF with(nolock) on CDF.DrugForm_id = D.DrugForm_id ";
			$filter .= " and CDF.DrugForm_Name like :RlsClsdrugforms_Name+'%'";
			$params['RlsClsdrugforms_Name'] = $data['RlsClsdrugforms_Name'];
		}

		if (!empty($data['PrepSeries_isDefect'])) {
			$filter .= " and isnull(isdef.YesNo_Code, 0) = :PrepSeries_isDefect";
			$params['PrepSeries_isDefect'] = $data['PrepSeries_isDefect'];
		}

		if (!empty($data['PrepSeries_godnMinMonthCount'])) {
			//$filter .= " and (PS.DocumentUcStr_godnDate is null or datediff(month, dbo.tzGetDate(), PS.DocumentUcStr_godnDate) >= :PrepSeries_godnMinMonthCount)";
                        // По просьбе заказчика изменен месяц на день (задача 81233)
                    $filter .= " and (PS.DocumentUcStr_godnDate is null or datediff(day, dbo.tzGetDate(), PS.DocumentUcStr_godnDate) >= :PrepSeries_godnMinMonthCount)";
			$params['PrepSeries_godnMinMonthCount'] = $data['PrepSeries_godnMinMonthCount'];
		}

		if (!empty($data['PrepSeries_godnMaxMonthCount'])) {
			//$filter .= " and (PS.DocumentUcStr_godnDate is null or datediff(month, dbo.tzGetDate(), PS.DocumentUcStr_godnDate) <= :PrepSeries_godnMaxMonthCount)";
                        // По просьбе заказчика изменен месяц на день (задача 81233)
                        $filter .= " and (PS.DocumentUcStr_godnDate is null or datediff(day, dbo.tzGetDate(), PS.DocumentUcStr_godnDate) <= :PrepSeries_godnMaxMonthCount)";
			$params['PrepSeries_godnMaxMonthCount'] = $data['PrepSeries_godnMaxMonthCount'];
		}

		if (!empty($data['LastUpdateDayCount'])) {
			$filter .= " and datediff(day, DOR.DrugOstatRegistry_updDT, dbo.tzGetDate()) >= :LastUpdateDayCount";
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
				with Rec(CLSATC_ID)
				as
				(
					select t.CLSATC_ID
					from rls.v_CLSATC t with(nolock)
					where
						t.CLSATC_ID = :CLSATC_ID
					union all
					select t.CLSATC_ID
					from rls.v_CLSATC t with(nolock)
						join Rec R on t.PARENTID = R.CLSATC_ID
				)
				select
					R.CLSATC_ID
				from Rec R
			";
			$result = $this->db->query($query,array(
				'CLSATC_ID' => $data['CLSATC_ID']
			));
			if (is_object($result)) {
				$res_arr = $result->result('array');
				if (is_array($res_arr) && !empty($res_arr)) {
					$atc_arr = array();
					foreach($res_arr as $row) {
						$atc_arr[] = $row['CLSATC_ID'];
					}
					$atc_arr = empty($atc_arr)?'null':implode(',', $atc_arr);

					$from .= " inner join rls.v_PREP_ATC PA with(nolock) on PA.PREPID = D.DrugPrep_id";
					$filter .= " and PA.UNIQID in ({$atc_arr})";
				}
			}
		}

		if (!empty($data['CLSPHARMAGROUP_ID'])) {
			//Получение списка потомков для фильтрации по ним
			$query = "
				with Rec(CLSPHARMAGROUP_ID)
				as
				(
					select t.CLSPHARMAGROUP_ID
					from rls.v_CLSPHARMAGROUP t with(nolock)
					where
						t.CLSPHARMAGROUP_ID = :CLSPHARMAGROUP_ID
					union all
					select t.CLSPHARMAGROUP_ID
					from rls.v_CLSPHARMAGROUP t with(nolock)
						join Rec R on t.PARENTID = R.CLSPHARMAGROUP_ID
				)
				select
					R.CLSPHARMAGROUP_ID
				from Rec R
			";
			$result = $this->db->query($query,array(
				'CLSPHARMAGROUP_ID' => $data['CLSPHARMAGROUP_ID']
			));
			if (is_object($result)) {
				$res_arr = $result->result('array');
				if (is_array($res_arr) && !empty($res_arr)) {
					$ph_gr_arr = array();
					foreach($res_arr as $row) {
						$ph_gr_arr[] = $row['CLSPHARMAGROUP_ID'];
					}
					$ph_gr_str = empty($ph_gr_arr)?'null':implode(',', $ph_gr_arr);
					$from .= " inner join rls.v_PREP_PHARMAGROUP PP with(nolock) on PP.PREPID = D.DrugPrep_id";
					$filter .= " and PP.UNIQID in ({$ph_gr_str})";
				}
			}
		}

		if (!empty($data['CLS_MZ_PHGROUP_ID'])) {
			//Получение списка потомков для фильтрации по ним
			$query = "
				with Rec(CLS_MZ_PHGROUP_ID)
				as
				(
					select t.CLS_MZ_PHGROUP_ID
					from rls.v_CLS_MZ_PHGROUP t with(nolock)
					where
						t.CLS_MZ_PHGROUP_ID = :CLS_MZ_PHGROUP_ID
					union all
					select t.CLS_MZ_PHGROUP_ID
					from rls.v_CLS_MZ_PHGROUP t with(nolock)
						join Rec R on t.PARENTID = R.CLS_MZ_PHGROUP_ID
				)
				select
					R.CLS_MZ_PHGROUP_ID
				from Rec R
			";
			$result = $this->db->query($query,array(
				'CLS_MZ_PHGROUP_ID' => $data['CLS_MZ_PHGROUP_ID']
			));
			if (is_object($result)) {
				$res_arr = $result->result('array');
				if (is_array($res_arr) && !empty($res_arr)) {
					$mz_pg_arr = array();
					foreach($res_arr as $row) {
						$mz_pg_arr[] = $row['CLS_MZ_PHGROUP_ID'];
					}
					$mz_pg_str = empty($mz_pg_arr)?'null':implode(',', $mz_pg_arr);
					$from .= " inner join rls.TRADENAMES_DRUGFORMS TD with(nolock) on TD.TRADENAMEID = P.TRADENAMEID and TD.DRUGFORMID = DCM.CLSDRUGFORMS_ID";
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
				DOR.DrugOstatRegistry_id,
				DOR.Org_id,
				org_area.area_name as Org_Area,
				ORG.Org_Nick as Org_Name,
				DOR.Storage_id,
				STOR.Storage_Name,
				ISNULL(SAT.SubAccountType_Name, '') as SubAccountType_Name,
				DOR.Drug_did,
                                DOR.Drug_did Drug_id,
				ISNULL(D.Drug_Name, '') as Drug_Name,
				
				
				STR(ISNULL(DOR.DrugOstatRegistry_Kolvo, 0) - ISNULL(SER.SumEvnRecept_Kolvo, 0), 10, 2) as DrugOstatRegistry_Kolvo,
				case
					when ISNULL(SER.SumEvnRecept_Kolvo,0) > 0 then
						STR(ISNULL(DOR.DrugOstatRegistry_Sum - DOR.DrugOstatRegistry_Sum/DOR.DrugOstatRegistry_Kolvo*ISNULL(SER.SumEvnRecept_Kolvo, 0.00), 0.00), 10, 2)
					else
						STR(ISNULL(DOR.DrugOstatRegistry_Sum, 0.00), 10, 2)
				end as DrugOstatRegistry_Sum,
				
				STR(DOR.DrugOstatRegistry_Cost, 10, 2) as DrugOstatRegistry_Price,
				
				ISNULL(O.Okei_Name, '') as Okei_Name,
				WDS.WhsDocumentUc_Num,
				--fin_year.yr as WhsDocumentSupply_Year,
				WDS.WhsDocumentUc_Name,
				
				convert(varchar,WDS.WhsDocumentUc_Date,104) as WhsDocumentUc_Date,
				DOR.DrugFinance_id,
				PS.DrugFinance_Name,
				DOR.WhsDocumentCostItemType_id,
				PS.WhsDocumentCostItemType_Name,
				AM.DrugMnn_Name as ActMatters_RusName,
				TN.DrugTorg_Name  as Prep_Name,
				DFORM.DrugForm_Name  as DrugForm_Name,
				Drug_DoseQ + ' ' +  Drug_DoseEi as Drug_Dose,
				Case when d.Drug_Vol is not null and d.Drug_Vol > 0 then Convert(varchar, d.Drug_vol) + ' ' + fas.DrugEdVol_Name else '' end +
				Case when d.Drug_Vol is not null and d.Drug_Vol > 0 and Drug_Fas is not null then ', ' else '' end +
				case
					when Drug_Fas is not null
						then '№ ' + rtrim(convert(varchar (5), Drug_Fas))
					else ''	
				end Drug_Fas,
				
				null as Firm_Name,
				null Reg_Num,
				D.Drug_Code DrugNomen_Code,
                                DocumentUcStr_Ser PrepSeries_Ser,
				--PS.PrepSeries_Ser,
				convert(varchar,PS.DocumentUcStr_godnDate,104) as PrepSeries_GodnDate,
                                Case  --  Устанавливаем 'Критичность' срока годности
					when PS.DocumentUcStr_godnDate IS Null
						then 1        
					when DATEADD(m, -3, PS.DocumentUcStr_godnDate) < GETDATE ()
						Then 1
					else 0	
				end GodnDate_Ctrl,
				DOR.DrugShipment_id,
				convert(varchar,DS.DrugShipment_setDT,104) as DrugShipment_setDT,
				--d.Drug_Code DrugShipment_Name
				DS.DrugShipment_Name 
				-- end select
			FROM  
                                -- from
				v_DrugOstatRegistry DOR with (NOLOCK)
				left join dbo.v_Drug D with (nolock) on D.Drug_id = DOR.Drug_did
                                left join DrugEdvol Fas with (nolock)  on   Fas.DrugEdvol_id = D.DrugEdvol_id
				left join v_Okei O with (nolock) on O.Okei_id = DOR.Okei_id
				left join v_DrugShipment DS with (nolock) on DS.DrugShipment_id = DOR.DrugShipment_id
				left join v_WhsDocumentSupply WDS with (nolock) on WDS.WhsDocumentSupply_id = DS.WhsDocumentSupply_id
				--left join v_DrugFinance DF with (nolock) on DF.DrugFinance_id = DOR.DrugFinance_id
				--left join v_WhsDocumentCostItemType WDCIT with (nolock) on WDCIT.WhsDocumentCostItemType_id = DOR.WhsDocumentCostItemType_id
				left join v_Org ORG with (nolock) on ORG.Org_id = DOR.Org_id
				left join v_OrgType org_type with (nolock) on org_type.OrgType_id = ORG.OrgType_id
				left join v_Address org_ua with (nolock) on org_ua.Address_id = ORG.UAddress_id
				left join v_Address org_pa with (nolock) on org_pa.Address_id = ORG.PAddress_id
				left join v_Storage STOR with (nolock) on STOR.Storage_id = DOR.Storage_id
				left join v_SubAccountType SAT with (nolock) on SAT.SubAccountType_id = DOR.SubAccountType_id
				--left join rls.v_DrugComplexMnn DCM with(nolock) on DCM.DrugComplexMnn_id = D.DrugComplexMnn_id
				--left join rls.v_DrugComplexMnnName DCMN with(nolock) on DCMN.DrugComplexMnnName_id = DCM.DrugComplexMnnName_id
				--left join rls.v_ACTMATTERS AM with(nolock) on AM.ACTMATTERS_ID = DCMN.ActMatters_id
				left join dbo.DrugMnn AM with(nolock) on AM.DrugMnn_id = d.DrugMnn_id
				--left join rls.v_prep P with(nolock) on P.Prep_id = D.DrugPrep_id
				--left join rls.PrepSeries PS with (nolock) on PS.PrepSeries_id = DOR.PrepSeries_id
				left join v_YesNo isdef with (nolock) on isdef.YesNo_id = 1-- ps.PrepSeries_isDefect  Временно без дефектов
			
				left join dbo.DrugTorg TN  with(nolock) on TN.DrugTorg_id = D.DrugTorg_id
				
				--left join rls.v_FIRMS F with(nolock) on F.FIRMS_ID = P.FIRMID
				left join dbo.DrugForm DFORM with(nolock) on DFORM.DrugForm_ID = D.DrugForm_ID
				--left join rls.REGCERT RC with (nolock) on RC.REGCERT_ID = P.REGCERTID
				--left join rls.MASSUNITS DFMU with (nolock) on DFMU.MASSUNITS_ID = P.DFMASSID
				--left join rls.CONCENUNITS DFCU with (nolock) on DFCU.CONCENUNITS_ID = P.DFCONCID
				--left join rls.ACTUNITS DFAU with (nolock) on DFAU.ACTUNITS_ID = P.DFACTID
				--left join rls.SIZEUNITS DFSU with (nolock) on DFSU.SIZEUNITS_ID = P.DFSIZEID
				left join rls.v_Nomen N with (nolock) on N.NOMEN_ID = DOR.Drug_id
				left join rls.MASSUNITS MU with (nolock) on MU.MASSUNITS_ID = N.PPACKMASSUNID
				left join rls.CUBICUNITS CU with (nolock) on CU.CUBICUNITS_ID = N.PPACKCUBUNID

				outer apply (
					select top 1
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
				) org_area

				outer apply (
					select top 1
						i_p.KLAreaStat_id,
						i_p.KLArea_Name
					from (
						select
							oa_area.KLAreaStat_id,
							oa_area.KLArea_Name
						from
							v_KLAreaStat oa_area with (nolock)
						where
							(oa_area.KLRgn_id is null or oa_area.KLRgn_id = org_area.KLRgn_id) and
							(oa_area.KLSubRgn_id is null or oa_area.KLSubRgn_id = org_area.KLSubRgn_id) and
							(oa_area.KLCity_id is null or oa_area.KLCity_id = org_area.KLCity_id) and
							(oa_area.KLTown_id is null or oa_area.KLTown_id = org_area.KLTown_id)
					) i_p
				) area_stat
                outer apply (
					select Top 1
						s.DocumentUcStr_id, ln.DrugShipment_id,
					DocumentUcStr_godnDate, DocumentUcStr_Ser, s.DrugFinance_id, DF.DrugFinance_Name, WDCIT.WhsDocumentCostItemType_id

, WDCIT.WhsDocumentCostItemType_Name
					from
						dbo.DocumentUcStr s with(nolock)
						inner join v_DrugShipmentlink ln with (nolock) on ln.DocumentUcStr_id = s.DocumentUcStr_id
							and ln.DrugShipment_id = dor.DrugShipment_id
						left join v_DrugFinance DF with (nolock) on DF.DrugFinance_id = dor.DrugFinance_id
						inner join dbo.DocumentUc Doc with (nolock) on Doc.DocumentUc_id= s.DocumentUc_id
							and Doc.Org_id = DOR.Org_id
						inner join v_WhsDocumentCostItemType WDCIT with (nolock) on WDCIT.WhsDocumentCostItemType_id = Dor.WhsDocumentCostItemType_id
					where
						Drug_id = D.Drug_id
						) PS
                                
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
	function ufaExportPL2dbf_Pers($data) {
	    
	    $params = array();
	    
	    $query = "
		--Set dateFormat dmy;

		Declare
		    @dtBeg date,
		    @dtEnd date,
		    @WhsDocumentCostItemType_id bigint;

		Set @dtBeg = :BegDate;
		Set @dtEnd = :EndDate;
		Set @WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id;
		
		Select 	
		    SS
		    ,SN_POL
		    ,FAM
		    ,IM
		    ,OT
		    ,W
		    ,DR
		    ,C_KATL
		    ,SN_DOC
		    ,C_DOC
		    ,OKATO_OMS
		    ,QM_OGRN
		    ,OKATO_REG
		    ,D_TYPE
		    from [tmp].[fn_ufaExportPL2dbfPers]
			(@dtBeg, @dtEnd, @WhsDocumentCostItemType_id);
			";
    
		$params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		$params['BegDate'] = $data['PeriodRange'][0];
		$params['EndDate'] = $data['PeriodRange'][1];
		
	    
		//$dbrep = $this->load->database('bdwork', true);

	       $dbrep = $this->db;
	     
	     
		$dbrep->query_timeout = 600;
		$result = $dbrep->query($query, $params);

		if ( is_object($result) ) {
			 $response['data'] = $result->result('array');
			return $response;
		}
		else {
			return false;
		}
	}
	
		/**
	 * Получение данных файла L для выгрузки рецептов
	 */
	function ufaExportPL2dbf_L($data) {
	    
	    $params = array();
	    
	     $query = "
		--Set dateFormat dmy;

		Declare
		    @dtBeg date,
		    @dtEnd date,
		    @WhsDocumentCostItemType_id bigint;

		Set @dtBeg = :BegDate;
		Set @dtEnd = :EndDate;
		Set @WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id;
		
		Select 
		 SS
		,OKATO_REG
		,OGRN
		,MCOD
		,PCOD
		,DS
		,SN_LR
		,DATE_VR
		,C_FINL
		,PR_LR
		,A_COD
		,NOMK_LS
		,KO_ALL
		,DOZ_ME
		,C_PFS
		,DATE_OBR
		,DATE_OTP
		,SL_ALL
		,TYPE_SCHET
		,FO_OGRN
		,P_KEK
		,D_TYPE
		,LINEID
		,OWNER
		,RAS
		,SPR_TYPE
		,SPECIFIC
		    from [tmp].[fn_ufaExportPL2dbfL]
			(@dtBeg, @dtEnd, @WhsDocumentCostItemType_id);
		    ";
	    
	    $params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
	    $params['BegDate'] = $data['PeriodRange'][0];
	    $params['EndDate'] = $data['PeriodRange'][1];
	    
	    
	    //$dbrep = $this->load->database('bdwork', true);

	    $dbrep = $this->db;
	     
	     //echo getDebugSQL($query, $params); exit();
	     
	    $dbrep->query_timeout = 6000;

	    $result = $dbrep->query($query, $params);

	    if ( is_object($result) ) {
		     $response['data'] = $result->result('array');
		    return $response;
	    }
	    else {
		    return false;
	    }
	    
	}
	
	/**
     * Выгрузка справочника врачей
     */
    function ufaExportCVF2dbf() {
		
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
			FROM  (
			 SELECT msf.MedPersonal_id, msf.MedStaffFact_id,
	lpu.lpu_id,
	lpu.Lpu_OKATO TF_OKATO,
		lpu.Lpu_f003mcod MCOD,
		 lpu.Lpu_OGRN + ' ' + convert(varchar, msf.MedPersonal_id) PCOD,
		msf.Person_SurName  FAM_V,
		msf.Person_FirName IM_V,
		msf.Person_SecName OT_V,
		lpu.Lpu_OGRN C_OGRN,
		ps.PostMed_Code PRVD,
		ps.PostMed_Name D_JOB, 
		msf.PostOccupationType_id AS PostOccupationType_id, --Тип занятия должности: 1 - основное место работы; 2 - внутреннее совместительство; 3 - внешнее совместительство
		convert(varchar(8), WorkPlace4DloApply_updDT, 112) D_PRIK,
		--WorkPlace4DloApply_updDT D_PRIK,
		convert(varchar(8), crt.CertificateReceipDate, 112) D_SER,
		--crt.CertificateReceipDate D_SER,
		null PRVS,
		null KV_KAT,
		convert(varchar(8), msf.WorkData_begDate, 112) DATE_B,
		--msf.WorkData_begDate DATE_B,
		convert(varchar(8), msf.WorkData_endDate, 112) DATE_E,
		--msf.WorkData_endDate DATE_E, 
		'с ' + convert(varchar, WorkPlace4DloApply_updDT, 104) MSG_TEXT,
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
				v_MedStaffFact msf with (nolock)
				--left join  dbo.MedPersonalCache Cache with (nolock) on Cache.Person_id = msf.Person_id
				left join  dbo.WorkPlace4DloApply app with (nolock) on app.MedStaffFact_id = msf.MedStaffFact_id
					and app.WorkPlace4DloApplyStatus_id = 0
				INNER JOIN v_Lpu Lpu with (nolock) on Lpu.Lpu_id=msf.Lpu_id and lpu.lpu_id <> 4
				INNER JOIN v_LpuPeriodDLO  LpuDlo with (nolock) on LpuDlo.Lpu_id=msf.Lpu_id 
					and LpuDlo.LpuPeriodDLO_begDate <= GetDate() and isnull(LpuDlo.LpuPeriodDLO_endDate, getDate()) >= GetDate()				
				INNER JOIN v_LpuUnit lu with (nolock) on lu.LpuUnit_id=msf.LpuUnit_id  and  LpuUnitType_SysNick in('polka', 'fap')
				INNER JOIN v_LpuSection ls with (nolock) on ls.LpuSection_id=msf.LpuSection_id  and isnull(LpuSectionProfile_SysNick, '') <> 'priem'
				LEFT JOIN v_PostMed ps with (nolock) on ps.PostMed_id=msf.Post_id
				outer apply (Select max(crt.CertificateReceipDate) CertificateReceipDate  from  persis.Certificate crt  with (nolock) where  crt.MedWorker_id = msf.MedPersonal_id) crt
				left join persis.v_Post Post with (nolock) on Post.id = msf.Post_id 
			where	
			(1=1) 
			 and msf.WorkData_dlobegDate <= getDate()
			 and (msf.WorkData_dloendDate>= getDate() or msf.WorkData_dloendDate is null) and msf.MedPersonal_Code is not null 
				    and LpuSection_Name <> 'Фиктивные ставки'
				    -- берем уволенных за последнии три года
				    and isnull(msf.WorkData_endDate, getDate()) >= DATEADD(yy, -3, GetDate ())
			-- end where
		) t
	     ";
	 
	    //$dbrep = $this->load->database('bdwork', true);

	    $dbrep = $this->db;
	     
	     //echo getDebugSQL($query, $params); exit();
	     
	    $dbrep->query_timeout = 6000;

	    $result = $dbrep->query($query);

	    if ( is_object($result) ) {
		     $response['data'] = $result->result('array');
		    return $response;
	    }
	    else {
		    return false;
	    }
    }
	
	/**
	 * Экстренный импорт данных из Башфармации
	 */
	function ufaExtraImportDataBF($data) {
	    
	    $params = array();
	    
	     $query = "
		declare
				@ErrCode int,
				@ErrMessage varchar(4000),
				@Mode varchar(25) = :Mode;
				
				exec r2.p_ExtraImportDataBF
					@Mode = @Mode,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
					
select @ErrCode as Error_Code, @ErrMessage as Error_Msg;  

		    ";
	    
	    $params['Mode'] = $data['Mode'];
	    
	    
	    //$dbrep = $this->load->database('bdwork', true);

	    $dbrep = $this->db;
	 
	     //echo getDebugSQL($query, $params); exit();
	     
	    $dbrep->query_timeout = 6000;

	    $result = $dbrep->query($query, $params);

	    if ( is_object($result) ) {
		     $response['data'] = $result->result('array');
		    return $response;
	    }
	    else {
		    return false;
	    }
	    
	}
	

}        