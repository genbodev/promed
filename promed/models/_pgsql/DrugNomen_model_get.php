<?php

class DrugNomen_model_get
{
	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getDrugByDrugNomenCode(DrugNomen_model $callObject, $data)
	{
		$query = "
			select
				d.Drug_id as \"Drug_id\",
				d.Drug_Name as \"Drug_Name\"
			from
				rls.v_DrugNomen dn
				left join rls.v_Drug d on dn.Drug_id = d.Drug_id
			where dn.DrugNomen_Code = :DrugNomen_Code
			order by dn.DrugNomen_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getGoodsUnitData(DrugNomen_model $callObject, $data)
	{
		$query = "
			select GoodsUnit_id as \"GoodsUnit_id\"
			from v_GoodsPackCount
			where DrugComplexMnn_id = :DrugComplexMnn_id
			  and Org_id = :Org_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getDrugMnnCodeByActMattersId(DrugNomen_model $callObject, $data)
	{
		$query = "
			select DrugMnnCode_Code as \"DrugMnnCode_Code\"
			from rls.v_DrugMnnCode
			where ACTMATTERS_id = :ActMatters_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getDrugNomenCode(DrugNomen_model $callObject, $data)
	{
		$params = ["Drug_id" => $data["Drug_id"]];
		$query = "
			select dn.DrugNomen_Code as \"DrugNomen_Code\"
			from rls.v_DrugNomen dn
			where dn.Drug_id = :Drug_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getDrugNomenData(DrugNomen_model $callObject, $data)
	{
		$params = ["Drug_id" => $data["Drug_id"]];
		$query = "
			select
            	DN.DrugNomen_Code as \"DrugNomen_Code\",
				case when AM.ACTMATTERS_ID > 0 then DMC.DrugMnnCode_id else DMCP.DrugMnnCode_id end as \"DrugMnnCode_id\",
				case when AM.ACTMATTERS_ID > 0 then DMC.DrugMnnCode_Code else DMCP.DrugMnnCode_Code end as \"DrugMnnCode_Code\",
				DTC.DrugTorgCode_id as \"DrugTorgCode_id\",
				DTC.DrugTorgCode_Code as \"DrugTorgCode_Code\",
				DCMC.DrugComplexMnnCode_id as \"DrugComplexMnnCode_id\",
				DCMC.DrugComplexMnnCode_Code as \"DrugComplexMnnCode_Code\",
				DCM.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				DCM.DrugComplexMnn_RusName as \"DrugComplexMnn_RusName\",
				DCM.DrugComplexMnn_LatName as \"DrugComplexMnn_LatName\",
				DPF.DrugTorg_NameLatin as \"DrugTorg_NameLatin\",
				coalesce(AM.ACTMATTERS_ID,AMP.ACTMATTERS_ID) as \"Actmatters_id\",
				case when AM.ACTMATTERS_ID > 0 then AM.RUSNAME else AMP.RUSNAME end as \"Actmatters_RusName\",
				case when AM.ACTMATTERS_ID > 0 then AM.LATNAME else AMP.LATNAME end as \"Actmatters_LatN\",
				case when AM.ACTMATTERS_ID > 0 then coalesce(AM.ACTMATTERS_LatNameGen, AM.LATNAME) else coalesce(AMP.ACTMATTERS_LatNameGen, AMP.LATNAME) end as \"Actmatters_LatName\",
				TN.TRADENAMES_ID as \"Tradenames_id\",
				TN.NAME as \"Tradenames_RusName\",
				LN.LATINNAMES_ID as \"Tradenames_LatName_id\",
				LN.NAME as \"Tradenames_LatN\",
				coalesce(LN.LATINNAMES_NameGen, LN.NAME) as \"Tradenames_LatName\",
				CDF.CLSDRUGFORMS_ID as \"Clsdrugforms_id\",
				CDF.FULLNAME as \"Clsdrugforms_RusName\",
				CDF.CLSDRUGFORMS_NameLatin as \"Clsdrugforms_LatName\",
				CDF.CLSDRUGFORMS_NameLatinSocr as \"Clsdrugforms_LatNameSocr\",
				(case
					when P.DFSIZEID > 0 then P.DFSIZE
					when P.DFMASSID > 0 then P.DFMASS::varchar
					when P.DFCONCID > 0 then P.DFCONC::varchar
					when P.DFACTID > 0 then P.DFACT::varchar
				end) as \"Unit_Value\",
				(case
					when P.DFMASSID > 0 then MU.FULLNAME
					when P.DFSIZEID > 0 then SU.FULLNAME
					when P.DFCONCID > 0 then CU.FULLNAME
					when P.DFACTID > 0 then AU.FULLNAME
				end) as \"Unit_RusName\",
				replace((case
					when P.DFMASSID > 0 then MU.MassUnits_NameLatin
					when P.DFSIZEID > 0 then SU.FULLNAMELATIN
					when P.DFCONCID > 0 then CU.CONCENUNITS_NameLatin
					when P.DFACTID > 0 then AU.ACTUNITS_NameLatin
				end), '\t', '') as \"Unit_LatName\",
				(case
					when P.DFMASSID > 0 then P.DFMASSID
					when P.DFSIZEID > 0 then P.DFSIZEID
					when P.DFCONCID > 0 then P.DFCONCID
					when P.DFACTID > 0 then P.DFACTID
				end) as \"Unit_id\",
				(case
					when P.DFMASSID > 0 then 'MassUnits'
					when P.DFSIZEID > 0 then 'sizeunits'
					when P.DFCONCID > 0 then 'CONCENUNITS'
					when P.DFACTID > 0 then 'ACTUNITS'
				end) as \"Unit_table\",
				DRMZ.DrugRMZ_id as \"DrugRMZ_id\",
				DRMZ.DrugRMZ_id as \"DrugRMZ_oldid\",
				DRMZ.DrugRPN_id as \"DrugRPN_id\",
				coalesce(DRMZ.DrugRMZ_RegNum, '')||coalesce(' '||to_char(DRMZ.DrugRMZ_RegDate, '{$callObject->dateTimeForm104}'), '') as \"DrugRMZ_RegNum\",
				DRMZ.DrugRMZ_EAN13Code as \"DrugRMZ_EAN13Code\",
				DRMZ.DrugRMZ_Name as \"DrugRMZ_Name\",
				DRMZ.DrugRMZ_Form as \"DrugRMZ_Form\",
				DRMZ.DrugRMZ_Dose as \"DrugRMZ_Dose\",
				DRMZ.DrugRMZ_Pack as \"DrugRMZ_Pack\",
				DRMZ.DrugRMZ_PackSize as \"DrugRMZ_PackSize\",
				DRMZ.DrugRMZ_Firm as \"DrugRMZ_Firm\",
				DRMZ.DrugRMZ_Country as \"DrugRMZ_Country\",
				DRMZ.DrugRMZ_FirmPack as \"DrugRMZ_FirmPack\",
				RC.REGNUM as \"Reg_Num\",
				D.Drug_Ean as \"Drug_Ean\",
				nomen.DRUGSINPPACK as \"DRUGSINPPACK\",
				D.DrugPrepFas_id as \"DrugPrepFas_id\",
				DPF.DrugPrep_Name as \"DrugPrep_Name\",
				ext.Extemporal_id as \"Extemporal_id\",
				ext.Extemporal_Name as \"Extemporal_Name\",
				replace(replace((
                    select
                        string_agg(coalesce(I_DNOL.Org_id::varchar, '0')||'|'||coalesce(I_DNOL.DrugNomenOrgLink_Code, '0'), '|::|')
                    from rls.v_DrugNomenOrgLink I_DNOL
                    where I_DNOL.DrugNomen_id = DN.DrugNomen_id
                    limit 1
                )||':::|', '|::|:::|', ''), ':::|', '') as \"DrugNomenOrgLink_Data\",
				replace(replace((
                    select
                        string_agg(coalesce(I_DPFC.Org_id::varchar, 'reg')||'|'||coalesce(I_DPFC.DrugPrepFasCode_Code, '0'), '|::|')
                    from rls.v_DrugPrepFasCode I_DPFC
                    where I_DPFC.DrugPrepFas_id = DPF.DrugPrepFas_id 
                    limit 1
                )||':::|', '|::|:::|', ''), ':::|', '') as \"DrugPrepFasCode_Data\",
                DCMC.DrugComplexMnnCode_DosKurs as \"DrugComplexMnnCode_DosKurs\"
			from
				rls.v_Drug D
				left join rls.v_DrugComplexMnn DCM on DCM.DrugComplexMnn_id = D.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnFas DCMF on DCMF.DrugComplexMnnFas_id = DCM.DrugComplexMnnFas_id
				left join rls.v_DrugComplexMnnName DCMN on DCMN.DrugComplexMnnName_id = DCM.DrugComplexMnnName_id
				left join rls.v_Nomen nomen on nomen.NOMEN_ID = D.Drug_id
				left join rls.v_ACTMATTERS AM on AM.ACTMATTERS_ID = DCMN.ActMatters_id
				left join rls.v_Prep P on P.Prep_id = D.DrugPrep_id
				left join rls.v_DrugPrep DPF on DPF.DrugPrepFas_id = D.DrugPrepFas_id
				left join rls.PREP_ACTMATTERS PA on PA.PREPID = P.Prep_id
				left join rls.v_ACTMATTERS AMP on AMP.ACTMATTERS_ID = PA.MATTERID
				left join rls.v_TRADENAMES TN on TN.TRADENAMES_ID = P.TRADENAMEID
				left join rls.v_LATINNAMES LN on LN.LATINNAMES_ID = P.LATINNAMEID
				left join rls.v_CLSDRUGFORMS CDF on CDF.CLSDRUGFORMS_ID = DCM.CLSDRUGFORMS_ID
				left join rls.v_MassUnits MU on MU.MASSUNITS_ID = P.DFMASSID
				left join rls.v_CONCENUNITS CU on CU.CONCENUNITS_ID = P.DFCONCID
				left join rls.v_ACTUNITS AU on AU.ACTUNITS_ID = P.DFACTID
				left join rls.v_sizeunits SU on SU.SIZEUNITS_ID = P.DFSIZEID
				left join rls.v_DrugMnnCode DMC on DMC.ACTMATTERS_id = AM.ACTMATTERS_ID
				left join rls.v_DrugMnnCode DMCP on DMCP.ACTMATTERS_id = AMP.ACTMATTERS_ID
				left join rls.v_DrugTorgCode DTC on DTC.TRADENAMES_id = TN.TRADENAMES_ID
				left join rls.v_DrugComplexMnnCode DCMC on DCMC.DrugComplexMnn_id = DCM.DrugComplexMnn_id
				left join rls.v_REGCERT RC on RC.REGCERT_ID = P.REGCERTID
				left join rls.v_ExtemporalNomen extnomen on extnomen.Nomen_id = D.Drug_id
				left join rls.v_Extemporal ext on ext.Extemporal_id = extnomen.Extemporal_id
				left join lateral (
					select *
					from rls.v_DrugRMZ
					where v_DrugRMZ.Drug_id = D.Drug_id
					order by DrugRMZ_id
				    limit 1
				) as DRMZ on true
                left join lateral (
                    select
                    	I_DN.DrugNomen_id,
                        I_DN.DrugNomen_Code
                    from rls.v_DrugNomen I_DN
                    where I_DN.Drug_id = D.Drug_id
                    order by I_DN.DrugNomen_id
				    limit 1
                ) as DN on true
			where D.Drug_id = :Drug_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return string
	 */
	public static function getDrugNomenGridFilter(DrugNomen_model $callObject, $data)
	{
		$filter = [];
		$filter_str = "";
		if (!empty($data["DrugNomenOrgLink_Code"])) {
			$filter[] = "DNOL.DrugNomenOrgLink_Code = :DrugNomenOrgLink_Code";
		}
		if (!empty($data["SprType_Code"]) && in_array($data["SprType_Code"], ["org_nom", "llo_nom"])) {
			$filter[] = "DNOL.DrugNomenOrgLink_id is not null";
		}
		if (!empty($data["DrugComplexMnnCode_Code"])) {
			$filter[] = "DCMC.DrugComplexMnnCode_Code = :DrugComplexMnnCode_Code";
		}
		if (!empty($data["DrugPrepFasCode_Code"])) {
			$filter[] = "DPFC.DrugPrepFasCode_Code = :DrugPrepFasCode_Code";
		}
		if (!empty($data["Actmatters_id"])) {
			$filter[] = "DMC.ACTMATTERS_id = :Actmatters_id";
		}
		if (!empty($data["Tradenames_id"])) {
			$filter[] = "DTC.TRADENAMES_id = :Tradenames_id";
		}
		if (!empty($data["Clsdrugforms_id"])) {
			$filter[] = "DCM.CLSDRUGFORMS_ID ilike :Clsdrugforms_id";
		}
		if (!empty($data["RlsActmatters_RusName"])) {
			$filter[] = "AM.RUSNAME ilike :RlsActmatters_RusName||'%'";
		}
		if (!empty($data["RlsTorg_Name"])) {
			$filter[] = "TN.NAME ilike :RlsTorg_Name||'%'";
		}
		if (!empty($data["RlsClsdrugforms_Name"])) {
			$filter[] = "CDF.FULLNAME ilike :RlsClsdrugforms_Name||'%'";
		}
		if (!empty($data["CLSATC_ID"])) {
			$filter[] = "PA.UNIQID = :CLSATC_ID";
		}
		if (!empty($data["STRONGGROUPS_ID"])) {
			$filter[] = "AM.STRONGGROUPID = :STRONGGROUPS_ID";
		}
		if (!empty($data["NARCOGROUPS_ID"])) {
			$filter[] = "AM.NARCOGROUPID = :NARCOGROUPS_ID";
		}
		if (!empty($data["FIRMS_ID"])) {
			$filter[] = "P.FIRMID = :FIRMS_ID";
		}
		if (!empty($data["COUNTRIES_ID"])) {
			$filter[] = "F.COUNTID = :COUNTRIES_ID";
		}
		if (!empty($data["CLSPHARMAGROUP_ID"])) {
			//Получение списка потомков для фильтрации по ним
			$query = "
				with recursive Rec (CLSPHARMAGROUP_ID) as (
					select t.CLSPHARMAGROUP_ID
					from rls.v_CLSPHARMAGROUP t
					where t.CLSPHARMAGROUP_ID = :CLSPHARMAGROUP_ID
					union all
					select t.CLSPHARMAGROUP_ID
					from
						rls.v_CLSPHARMAGROUP t
						join Rec R on t.PARENTID = R.CLSPHARMAGROUP_ID
				)
				select R.CLSPHARMAGROUP_ID as \"CLSPHARMAGROUP_ID\"
				from Rec R
			";
			$queryParams = [
				"CLSPHARMAGROUP_ID" => $data["CLSPHARMAGROUP_ID"]
			];
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($query, $queryParams);
			if (is_object($result)) {
				$res_arr = $result->result("array");
				if (is_array($res_arr) && !empty($res_arr)) {
					$ph_gr_arr = array();
					foreach ($res_arr as $row) {
						$ph_gr_arr[] = $row["CLSPHARMAGROUP_ID"];
					}
					$ph_gr_str = empty($ph_gr_arr) ? "null" : implode(",", $ph_gr_arr);
					$filter[] = "PP.UNIQID in ({$ph_gr_str})";
				}
			}
		}
		if (!empty($data["CLS_MZ_PHGROUP_ID"])) {
			//Получение списка потомков для фильтрации по ним
			$query = "
				with Rec(CLS_MZ_PHGROUP_ID) as (
                        select 
                            t.CLS_MZ_PHGROUP_ID
                        from 
                            rls.v_CLS_MZ_PHGROUP t
                        where 
                            t.CLS_MZ_PHGROUP_ID = :CLS_MZ_PHGROUP_ID 
                        or 
                        t.PARENTID = :CLS_MZ_PHGROUP_ID 
				)
				select R.CLS_MZ_PHGROUP_ID as \"CLS_MZ_PHGROUP_ID\"
				from Rec R
			";
			$queryParams = ["CLS_MZ_PHGROUP_ID" => $data["CLS_MZ_PHGROUP_ID"]];
			$result = $callObject->db->query($query, $queryParams);
			if (is_object($result)) {
				$res_arr = $result->result("array");
				if (is_array($res_arr) && !empty($res_arr)) {
					$mz_pg_arr = array();
					foreach ($res_arr as $row) {
						$mz_pg_arr[] = $row["CLS_MZ_PHGROUP_ID"];
					}
					$mz_pg_str = empty($mz_pg_arr) ? "null" : implode(",", $mz_pg_arr);
					$filter[] = "TD.MZ_PHGR_ID in ({$mz_pg_str})";
				}
			}
		}
		if (!empty($data["no_rmz"]) && $data["no_rmz"] == 1) {
			$filter[] = "DRMZ.DrugRMZ_id is null";
		}
		//если в результат велючены позиции без идентификатора медикамента, то для записей без медикамента отключаем все фильтры кроме фильтра по коду
		if (count($filter) > 0 && !empty($data["rls_drug_link"]) && in_array($data["rls_drug_link"], ["all", "no"])) {
			if ($data["rls_drug_link"] == "all") {
				//если вытаскиваем все виды записей то обьединяем фильтры
				$sub_filter = "(DN.Drug_id is null or (" . join(" and ", $filter) . "))";
				$filter = [];
				$filter[] = $sub_filter;
			} else {
				//иначе просто сбрасываем
				$filter = [];
			}
		}
		if (!empty($data["DrugNomen_Code"])) {
			$filter[] = "DN.DrugNomen_Code = :DrugNomen_Code";
		}
		if (!empty($data["rls_drug_link"])) {
			if ($data["rls_drug_link"] == "yes") {
				$filter[] = "DN.Drug_id is not null";
			}
			if ($data["rls_drug_link"] == "no") {
				$filter[] = "DN.Drug_id is null";
			}
		} else {
			$filter[] = "DN.Drug_id is not null";
		}
		if (count($filter) > 0) {
			$filter_str = " and " . join(" and ", $filter);
		}
		return $filter_str;
	}

	/**
	 * @param $data
	 * @return string
	 */
	public static function getDrugNomenGridJoin($data)
	{
		$join = "";
		if (!empty($data["CLSPHARMAGROUP_ID"]) || !empty($data["CLSATC_ID"])) {
			if (!empty($data["CLSPHARMAGROUP_ID"])) {
				$join .= " inner join rls.v_PREP_PHARMAGROUP PP on PP.PREPID = D.DrugPrep_id";
			}
			if (!empty($data["CLSATC_ID"])) {
				$join .= " inner join rls.v_PREP_ATC PA on PA.PREPID = D.DrugPrep_id";
			}
		}
		if (!empty($data["CLS_MZ_PHGROUP_ID"])) {
			$join .= " inner join rls.TRADENAMES_DRUGFORMS TD on TD.TRADENAMEID = DTC.TRADENAMES_id and TD.DRUGFORMID = DCM.CLSDRUGFORMS_ID";
		}
		return $join;
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function getDrugRMZExportData(DrugNomen_model $callObject, $data)
	{
		$query = "
			select
				1 as \"Error_Code\",
				null as \"DrugID\",
				d.Drug_Name as \"Drug_Name\",
				null as \"VZN\",
				null as \"RecordType\",
				null as \"FinYear\",
				null as \"ExpDate\",
				null as \"Ser\",
				null as \"Amount\",
				null as \"Summa\",
				null as \"NumOfUnits\"
			from
				v_DrugOstatRegistry dor
				left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
				left join WhsDocumentCostItemType wdcit on wdcit.WhsDocumentCostItemType_id = dor.WhsDocumentCostItemType_id
				left join Contragent c on c.Contragent_id = dor.Contragent_id
				left join ContragentType ct on ct.ContragentType_id = c.ContragentType_id
				left join rls.v_Drug d on d.Drug_id = dor.Drug_id
				left join lateral (
					select
						i_drmz.Drug_id,
						i_drmz.DrugRPN_id
					from rls.DrugRMZ i_drmz
					where i_drmz.Drug_id = dor.Drug_id
					limit 1
				) as drmz on true
			where dor.DrugOstatRegistry_Kolvo > 0
			  and sat.SubAccountType_Code = 1
			  and wdcit.WhsDocumentCostItemType_Nick in ('vzn', 'fl')
			  and ct.ContragentType_SysNick in ('apt', 'store')
			  and drmz.Drug_id is null
			group by d.Drug_Name
			union all
			select
				(case
					when p.PrepSeries_GodnDate <= (tzgetdate() + (2||' months')::interval) then 3
					when coalesce(isdef.YesNo_Code, 0) = 1 then 4
					else null
				end) as \"Error_Code\",
				p.DrugRPN_id as \"DrugID\",
				d.Drug_Name as \"Drug_Name\",
				(case
					when p.WhsDocumentCostItemType_Nick = 'vzn' then 1
					else 0
				end) as \"VZN\",
				2 as \"RecordType\",
				date_part('year', WhsDocumentSupply_ExecDate) as \"FinYear\",
				to_char(p.PrepSeries_GodnDate, '{$callObject->dateTimeForm104}') as \"ExpDate\",
				to_char(to_date(p.PrepSeries_Ser), '{$callObject->dateTimeForm104}') as \"Ser\",
				p.DrugOstatRegistry_Kolvo as \"Amount\",
				p.DrugOstatRegistry_Sum as \"Summa\",
				(case
					when coalesce(nomen.DRUGSINPPACK, 0) > 0
					    then p.DrugOstatRegistry_Kolvo*nomen.DRUGSINPPACK
						else p.DrugOstatRegistry_Kolvo
				end) as \"NumOfUnits\"
			from
				(
					select
						dor.Drug_id,
						drmz.DrugRPN_id,
						wdcit.WhsDocumentCostItemType_Nick,
						ps.PrepSeries_GodnDate,
						ps.PrepSeries_Ser,
						ps.PrepSeries_isDefect,
						wds.WhsDocumentSupply_ExecDate,
						sum(dor.DrugOstatRegistry_Kolvo) as DrugOstatRegistry_Kolvo,
						sum(dor.DrugOstatRegistry_Sum) as DrugOstatRegistry_Sum
					from
						v_DrugOstatRegistry dor
						left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
						left join WhsDocumentCostItemType wdcit on wdcit.WhsDocumentCostItemType_id = dor.WhsDocumentCostItemType_id
						left join Contragent c on c.Contragent_id = dor.Contragent_id
						left join ContragentType ct on ct.ContragentType_id = c.ContragentType_id
						left join rls.PrepSeries ps on ps.PrepSeries_id = dor.PrepSeries_id
						left join DrugShipment ds on ds.DrugShipment_id = dor.DrugShipment_id
						left join v_WhsDocumentSupply wds on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
						left join lateral (
							select
								i_drmz.Drug_id,
								i_drmz.DrugRPN_id
							from rls.DrugRMZ i_drmz
							where i_drmz.Drug_id = dor.Drug_id
						    limit 1
						) as drmz on true
					where dor.DrugOstatRegistry_Kolvo > 0
					  and sat.SubAccountType_Code = 1
					  and wdcit.WhsDocumentCostItemType_Nick in ('vzn', 'fl')
					  and ct.ContragentType_SysNick in ('apt', 'store')
					  and drmz.Drug_id is not null
					group by
						dor.Drug_id,
					    drmz.DrugRPN_id,
					    wdcit.WhsDocumentCostItemType_Nick,
					    ps.PrepSeries_GodnDate,
					    ps.PrepSeries_Ser,
					    ps.PrepSeries_isDefect,
					    wds.WhsDocumentSupply_ExecDate
				) p
				left join rls.v_Drug d on d.Drug_id = p.Drug_id
				left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnFas dcmf on dcmf.DrugComplexMnnFas_id = dcm.DrugComplexMnnFas_id
				left join rls.v_Nomen nomen on nomen.NOMEN_ID = d.Drug_id
				left join v_YesNo isdef on isdef.YesNo_id = p.PrepSeries_isDefect
			union
			select
				(case
					when p.DocumentUcStr_Count < 0 then 2
					when p.PrepSeries_GodnDate <= (tzgetdate() + (2||' months')::interval) then 3
					when coalesce(isdef.YesNo_Code, 0) = 1 then 4
					else null
				end) as \"Error_Code\",
				p.DrugRPN_id as \"DrugID\",
				d.Drug_Name as \"Drug_Name\",
				(case
					when p.WhsDocumentCostItemType_Nick = 'vzn' then 1 else 0
				end) as \"VZN\",
				1 as \"RecordType\",
				date_part('year', WhsDocumentSupply_ExecDate) as \"FinYear\",
				to_char(p.PrepSeries_GodnDate, '{$callObject->dateTimeForm104}') as \"ExpDate\",
				to_char(to_date(p.PrepSeries_Ser), '{$callObject->dateTimeForm104}') as \"Ser\",
				p.DocumentUcStr_Count as \"Amount\",
				p.DocumentUcStr_SumR as \"Summa\",
				(case
					when coalesce(nomen.DRUGSINPPACK, 0) > 0
					    then p.DocumentUcStr_Count*nomen.DRUGSINPPACK
						else p.DocumentUcStr_Count
				end) as \"NumOfUnits\"
			from
				(
					select
						dus.Drug_id,
						drmz.DrugRPN_id,
						wdcit.WhsDocumentCostItemType_Nick,
						ps.PrepSeries_GodnDate,
						ps.PrepSeries_Ser,
						ps.PrepSeries_isDefect,
						wds.WhsDocumentSupply_ExecDate,
						sum((case
							when ddt.DrugDocumentType_SysNick = 'DocVozNakR' then dus.DocumentUcStr_Count*(-1)
							else dus.DocumentUcStr_Count
						end)) as DocumentUcStr_Count,
						sum((case
							when ddt.DrugDocumentType_SysNick = 'DocVozNakR' then dus.DocumentUcStr_SumR*(-1)
							else dus.DocumentUcStr_SumR
						end)) as DocumentUcStr_SumR
					from
						v_DocumentUc du
						left join Contragent c_s on c_s.Contragent_id = du.Contragent_sid
						left join Contragent c_t on c_t.Contragent_id = du.Contragent_tid
						left join v_DrugDocumentType ddt on ddt.DrugDocumentType_id = du.DrugDocumentType_id
						left join v_DrugDocumentStatus dds on dds.DrugDocumentStatus_id = du.DrugDocumentStatus_id
						left join v_WhsDocumentCostItemType wdcit on wdcit.WhsDocumentCostItemType_id = du.WhsDocumentCostItemType_id
						inner join v_WhsDocumentSupply wds on wds.WhsDocumentUc_id = du.WhsDocumentUc_id
						left join v_DocumentUcStr dus on dus.DocumentUc_id = du.DocumentUc_id
						left join rls.v_PrepSeries ps on ps.PrepSeries_id = dus.PrepSeries_id
						left join lateral (
							select
								i_drmz.Drug_id,
								i_drmz.DrugRPN_id
							from rls.DrugRMZ i_drmz
							where i_drmz.Drug_id = dus.Drug_id
				    		limit 1
						) as drmz on true
					where ddt.DrugDocumentType_SysNick in ('DokNak', 'DocVozNakR')
					  and dds.DrugDocumentStatus_Code = 4
					  and wdcit.WhsDocumentCostItemType_Nick in ('vzn', 'fl')
					  and du.DocumentUc_didDate between :Date1 and :Date2
					  and dus.DocumentUcStr_Count <> 0
					  and drmz.Drug_id is not null
					  and ((ddt.DrugDocumentType_SysNick = 'DokNak' and c_s.Org_id = wds.Org_sid) or (ddt.DrugDocumentType_SysNick = 'DocVozNakR' and c_t.Org_id = wds.Org_sid))
					group by
						dus.Drug_id,
					    drmz.DrugRPN_id,
					    wdcit.WhsDocumentCostItemType_Nick,
					    ps.PrepSeries_GodnDate,
					    ps.PrepSeries_Ser,
					    ps.PrepSeries_isDefect,
					    wds.WhsDocumentSupply_ExecDate
				) p
				left join rls.v_Drug d on d.Drug_id = p.Drug_id
				left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnFas dcmf on dcmf.DrugComplexMnnFas_id = dcm.DrugComplexMnnFas_id
				left join rls.v_Nomen nomen on nomen.NOMEN_ID = d.Drug_id
				left join v_YesNo isdef on isdef.YesNo_id = p.PrepSeries_isDefect
		";
		$queryParams = [
			"Date1" => $data["Supply_DateRange"][0],
			"Date2" => $data["Supply_DateRange"][1]
		];
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	public static function getDrugRMZLinkData(DrugNomen_model $callObject, $data)
	{
		$next_drug_id = null;
		$query = "
			select
				count(dn.Drug_id) as \"no_linked_cnt\",
				max(dn.Drug_id) as \"max_drug_id\"
			from
				rls.v_DrugNomen dn
				left join lateral (
					select v_DrugRMZ.Drug_id
					from rls.v_DrugRMZ
					where v_DrugRMZ.Drug_id = dn.Drug_id
					limit 1
				) as drmz on true
			where drmz.Drug_id is null;
		";
		$drug_data = $callObject->getFirstRowFromQuery($query);
		if (!$drug_data || count($drug_data) < 2) {
			return false;
		}
		$query = "
			select dn.Drug_id as \"Drug_id\"
			from
				rls.v_DrugNomen dn
				left join lateral (
					select v_DrugRMZ.Drug_id
					from rls.v_DrugRMZ
					where v_DrugRMZ.Drug_id = dn.Drug_id
				    limit 1
				) as drmz on true
			where dn.Drug_id > coalesce(:Drug_id, 0)
			  and drmz.Drug_id is null
			order by dn.Drug_id
			limit 1
		";
		$next_drug_id = $callObject->getFirstResultFromQuery($query, $data);
		//если записи кончились, переходим к первой
		if ($next_drug_id <= 0) {
			$next_drug_id = $drug_data["max_drug_id"];
		}
		if ($next_drug_id > 0) {
			$query = "
				select
					D.Drug_id as \"Drug_id\",
					DN.DrugNomen_Code as \"DrugNomen_Code\",
					coalesce(RC.REGNUM, '')||coalesce(' '||to_char(D.Drug_begDate, '{$callObject->dateTimeForm104}'), '') as \"Reg_Data\",
					RC.REGNUM as \"Reg_Num\",
					D.Drug_Ean as \"Drug_Ean\",
					TN.NAME as \"Tradenames_RusName\",
					CDF.FULLNAME as \"Clsdrugforms_RusName\",
					(case
						when P.DFSIZEID > 0 then P.DFSIZE::varchar||' '||SU.SHORTNAME
						when P.DFMASSID > 0 then P.DFMASS::varchar||' '||MU.SHORTNAME
						when P.DFCONCID > 0 then P.DFCONC::varchar||' '||CU.SHORTNAME
						when P.DFACTID > 0 then P.DFACT::varchar||' '||AU.SHORTNAME
					end) as \"Unit_Value\",
					(case
						when F.FULLNAME is null or F.FULLNAME ilike ''
						then FN.NAME else F.FULLNAME
					end) as \"Firm_Name\",
					D.Drug_Fas as \"Drug_Fas\",
					(
						coalesce(N.DRUGSINPPACK::varchar||'шт., '||DP1.FULLNAME||' ', '')||
						coalesce('('||N.PPACKINUPACK::varchar||'), '||DP2.FULLNAME||' ', '')||
						coalesce('('||N.UPACKINSPACK::varchar||'), '||DP3.FULLNAME||' ', '')
					) as \"DrugPack_Name\",
					(case
						when PACK_F.FULLNAME is null or PACK_F.FULLNAME ilike ''
						then PACK_FN.NAME else PACK_F.FULLNAME
					end || coalesce((case when PACK_C.NAME not ilike '' then ', ' else '' end)||PACK_C.NAME, '')) as \"DrugPack_FirmName\"
				from
					rls.v_Drug D
					left join rls.v_DrugComplexMnn DCM on DCM.DrugComplexMnn_id = D.DrugComplexMnn_id
					left join rls.v_Prep P on P.Prep_id = D.DrugPrep_id
					left join rls.v_FIRMS F on F.FIRMS_ID = P.FIRMID
					left join rls.v_FIRMNAMES FN on FN.FIRMNAMES_ID = F.NAMEID
					left join rls.v_NOMEN N on N.PREPID = P.Prep_id
					left join rls.v_DRUGPACK DP1 on DP1.DRUGPACK_ID = N.PPACKID
					left join rls.v_DRUGPACK DP2 on DP2.DRUGPACK_ID = N.UPACKID
					left join rls.v_DRUGPACK DP3 on DP3.DRUGPACK_ID = N.SPACKID
					left join rls.v_FIRMS PACK_F on PACK_F.FIRMS_ID = N.FIRMID
					left join rls.v_FIRMNAMES PACK_FN on PACK_FN.FIRMNAMES_ID = PACK_F.NAMEID
					left join rls.v_COUNTRIES PACK_C on PACK_C.COUNTRIES_ID = PACK_F.COUNTID
					left join rls.v_REGCERT RC on RC.REGCERT_ID = P.REGCERTID
					left join rls.v_TRADENAMES TN on TN.TRADENAMES_ID = P.TRADENAMEID
					left join rls.v_CLSDRUGFORMS CDF on CDF.CLSDRUGFORMS_ID = DCM.CLSDRUGFORMS_ID
					left join rls.v_MassUnits MU on MU.MASSUNITS_ID = P.DFMASSID
					left join rls.v_CONCENUNITS CU on CU.CONCENUNITS_ID = P.DFCONCID
					left join rls.v_ACTUNITS AU on AU.ACTUNITS_ID = P.DFACTID
					left join rls.v_sizeunits SU on SU.SIZEUNITS_ID = P.DFSIZEID
					left join lateral (
						select DrugNomen_Code
						from rls.v_DrugNomen
						where v_DrugNomen.Drug_id = D.Drug_id
						order by v_DrugNomen.DrugNomen_id
						limit 1
					) as DN on true
				where D.Drug_id = :Drug_id
				limit 1
			";
			$queryParams = ["Drug_id" => $next_drug_id];
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($query, $queryParams);
			if (is_object($result)) {
				$result = $result->result("array");
				$result[0]["no_linked_cnt"] = $drug_data["no_linked_cnt"];
				return $result;
			}
		}
		return false;
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @return array|bool
	 */
	public static function getDrugRMZInformation(DrugNomen_model $callObject)
	{
		$query = "
			select
				to_char(max(DrugRMZ_updDT), '{$callObject->dateTimeForm104}') as \"LastUpdate_Date\",
				count(DrugRMZ_id) as \"Record_Count\"
			from
				rls.v_DrugRMZ
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadPrepClassTree(DrugNomen_model $callObject, $data)
	{
		$filter = (empty($data["PrepClass_pid"])) ? "PrepClass_pid is null" : "PrepClass_pid = :PrepClass_pid";
		if (!empty($data["PrepClass_Code"])) {
			$filter .= " and pc.PrepClass_Code = :PrepClass_Code";
		}
		$query = "
			select
				pc.PrepClass_id as \"id\",
				pc.PrepClass_Code as \"code\",
				pc.PrepClass_Name as \"name\",
				'PrepClass' as \"object\",
				case when pccount.cnt = 0 then 1 else 0 end as \"leaf\"
			from
				rls.v_PrepClass pc
				left join lateral (
					select count(PrepClass_id) as cnt
					from rls.v_PrepClass
					where PrepClass_pid = pc.PrepClass_id
				) as pccount on true
			where {$filter}
			order by \"leaf\", pc.PrepClass_Name
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}

		$result = $result->result("array");
		leafToInt($result);
		return $result;
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadDrugNomenGrid(DrugNomen_model $callObject, $data)
	{
		$params = $data;
		$join = $callObject->getDrugNomenGridJoin($params);
		$filter = $callObject->getDrugNomenGridFilter($params);
		$prepclass = [];
		$prepclass[] = $data["PrepClass_id"];
		//получение дочерних классов
		$query = "
            with recursive PrepClassTree (id, pid) as (
                select
                    pc.PrepClass_id,
                    pc.PrepClass_pid
                from rls.v_PrepClass pc
                where coalesce(pc.PrepClass_pid, 0) = coalesce(:PrepClass_id, 0)
                union all
                select
                    pc.PrepClass_id,
                    pc.PrepClass_pid
                from
                    rls.v_PrepClass pc
                    inner join PrepClassTree pct on pc.PrepClass_pid = pct.id
            )
            select pct.id as \"id\"
            from PrepClassTree pct;
        ";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (is_object($result)) {
			$arr = $result->result("array");
			for ($i = 0; $i < count($arr); $i++) {
				$prepclass[] = $arr[$i]["id"];
			}
		}
		$prepclass_filter = "DN.PrepClass_id in (" . join(",", $prepclass) . ")";
		$query = "
			select
			-- select
                DN.DrugNomen_id as \"DrugNomen_id\",
                DN.DrugNomen_Code as \"DrugNomen_Code\",
                DN.DrugNomen_Name as \"DrugNomen_Name\",
                DNOL.DrugNomenOrgLink_Code as \"DrugNomenOrgLink_Code\",
                DPFC.DrugPrepFasCode_Code as \"DrugPrepFasCode_Code\",
                DN.PrepClass_id as \"PrepClass_id\",
                D.Drug_Ean as \"Drug_Ean\",
                DMC.DrugMnnCode_Code as \"DrugMnnCode_Code\",
                DTC.DrugTorgCode_Code as \"DrugTorgCode_Code\",
                DCMC.DrugComplexMnnCode_Code as \"DrugComplexMnnCode_Code\",
                DRMZ.DrugRPN_id as \"DrugRPN_id\",
                DCM.DrugComplexMnn_RusName as \"DrugComplexMnn_RusName\",
                AM.RUSNAME as \"ActMatters_RusName\", --МНН
				TN.NAME as \"RlsTorg_Name\", --Лекарственный препарат -- Торговое наименование
				CDF.NAME as \"RlsClsdrugforms_Name\", --Форма выпуска -- лекарственная форма
				Dose.Value as \"Drug_Dose\", --Дозировка
				Fas.Value as \"Drug_Fas\", --Фасовка
                Pack.Value as \"DrugPack_Name\", --Упаковка
				RC.REGNUM as \"Reg_Num\", --№ РУ
				RCEFF.FULLNAME as \"Reg_Firm\",
				RCEFFC.NAME as \"Reg_Country\",
				to_char(RC.REGDATE, '{$callObject->dateTimeForm104}')||coalesce(' - '||to_char(RC.ENDDATE, '{$callObject->dateTimeForm104}'), '') as \"Reg_Period\",
				to_char(RC.Reregdate, '{$callObject->dateTimeForm104}') as \"Reg_ReRegDate\",
				DCMC.DrugComplexMnnCode_DosKurs as \"DrugComplexMnnCode_DosKurs\"
			-- end select
			from
			-- from
				rls.v_DrugNomen DN
				left join rls.v_Drug D on D.Drug_id = DN.Drug_id
				left join rls.v_DrugComplexMnnCode DCMC on DCMC.DrugComplexMnnCode_id = DN.DrugComplexMnnCode_id
				left join rls.v_DrugComplexMnn DCM on DCM.DrugComplexMnn_id = DCMC.DrugComplexMnn_id
				left join rls.v_DrugMnnCode DMC on DMC.DrugMnnCode_id = DN.DrugMnnCode_id
				left join rls.v_ACTMATTERS AM on AM.ACTMATTERS_ID = DMC.ACTMATTERS_id
				left join rls.v_DrugTorgCode DTC on DTC.DrugTorgCode_id = DN.DrugTorgCode_id
				left join rls.v_TRADENAMES TN on TN.TRADENAMES_ID = DTC.TRADENAMES_id
				left join rls.CLSDRUGFORMS CDF on CDF.CLSDRUGFORMS_ID = DCM.CLSDRUGFORMS_ID
				left join rls.v_Prep P on P.Prep_id = D.DrugPrep_id
				left join rls.v_FIRMS F on F.FIRMS_ID = P.FIRMID
				left join rls.v_FIRMNAMES FN on FN.FIRMNAMES_ID = F.NAMEID
				left join rls.v_REGCERT RC on RC.REGCERT_ID = P.REGCERTID
				left join rls.REGCERT_EXTRAFIRMS RCEF on RCEF.CERTID = RC.REGCERT_ID
				left join rls.v_FIRMS RCEFF on RCEFF.FIRMS_ID = RCEF.FIRMID
                left join rls.v_COUNTRIES RCEFFC on RCEFFC.COUNTRIES_ID = RCEFF.COUNTID
				left join rls.MASSUNITS MU on MU.MASSUNITS_ID = p.DFMASSID
				left join rls.CONCENUNITS CU on CU.CONCENUNITS_ID = p.DFCONCID
				left join rls.ACTUNITS AU on AU.ACTUNITS_ID = p.DFACTID
				left join rls.SIZEUNITS SU on SU.SIZEUNITS_ID = p.DFSIZEID
				left join lateral (
					select
					    DrugRMZ_id,
						DrugRPN_id
					from rls.v_DrugRMZ
					where v_DrugRMZ.Drug_id = D.Drug_id
					order by DrugRMZ_id
					limit 1
				) as DRMZ on true
				left join lateral (
				    select
				        DrugNomenOrgLink_id,
				        DrugNomenOrgLink_Code
				    from rls.v_DrugNomenOrgLink
				    where v_DrugNomenOrgLink.DrugNomen_id = DN.DrugNomen_id
				      and (:DrugNomenOrgLink_Org_id is null or v_DrugNomenOrgLink.Org_id = :DrugNomenOrgLink_Org_id::bigint)
				    order by DrugNomenOrgLink_id
				) as DNOL on true
				left join lateral (
				    select DrugPrepFasCode_Code
				    from rls.v_DrugPrepFasCode
				    where v_DrugPrepFasCode.DrugPrepFas_id = D.DrugPrepFas_id
				      and coalesce(v_DrugPrepFasCode.Org_id, 0) = coalesce(:DrugNomenOrgLink_Org_id::bigint, 0)
				    order by DrugNomenOrgLink_id
				    limit 1
				) as DPFC on true
				left join lateral (
					select coalesce(
						P.DFMASS::float8::varchar||' '||MU.SHORTNAME,
						p.DFCONC::float8::varchar||' '||CU.SHORTNAME,
						P.DFACT::varchar||' '||AU.SHORTNAME,
						P.DFSIZE::varchar||' '||SU.SHORTNAME
					) as Value
				) as Dose on true
				left join lateral (
					select (
						(case when D.Drug_Fas is not null then D.Drug_Fas::varchar||' доз' else '' end)||
						(case when D.Drug_Fas is not null and coalesce(D.Drug_Volume, D.Drug_Mass) is not null then ', ' else '' end)||
						(case when coalesce(D.Drug_Volume,D.Drug_Mass) is not null then coalesce(D.Drug_Volume, D.Drug_Mass) else '' end)
					) as Value
				) as Fas on true
				left join lateral (
					select
					    (
					        coalesce(N.DRUGSINPPACK::varchar||'шт., '||DP1.FULLNAME||' ', '')||
                            coalesce('('||N.PPACKINUPACK::varchar||'), '||DP2.FULLNAME||' ', '')||
                            coalesce('('||N.UPACKINSPACK::varchar||'), '||DP3.FULLNAME||' ', '')
					    ) as Value
					from
					    rls.v_NOMEN N
                        left join rls.v_DRUGPACK DP1 on DP1.DRUGPACK_ID = N.PPACKID
                        left join rls.v_DRUGPACK DP2 on DP2.DRUGPACK_ID = N.UPACKID
                        left join rls.v_DRUGPACK DP3 on DP3.DRUGPACK_ID = N.SPACKID
                    where N.PREPID = P.Prep_id
                    order by N.NOMEN_ID
                    limit 1
				) as Pack on true
				{$join}
			-- end from
			where
			-- where
				{$prepclass_filter}
				{$filter}
			-- end where
			order by
			-- order by
				DN.DrugNomen_Code
			-- end order by
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_count
		 */
		$result = $callObject->db->query(getLimitSQLPH($query, $data["start"], $data["limit"]), $params);
		$result_count = $callObject->db->query(getCountSQLPH($query), $params);
		if (is_object($result_count)) {
			$cnt_arr = $result_count->result("array");
			$count = $cnt_arr[0]["cnt"];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		if (!is_object($result)) {
			return false;
		}
		$response = [];
		$response["data"] = $result->result("array");
		$response["totalCount"] = $count;
		return $response;
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool
	 */
   public static function loadDrugNomenCmpDrugUsageCombo(DrugNomen_model $callObject, $data)
	{
		$prepclass = [];
		$data["PrepClass_id"] = 1; // только медикаменты
		$data["start"] = 0;
		$data["limit"] = 100;
		$params = $data;
		$prepclass[] = $data["PrepClass_id"];
		//получение дочерних классов
		$query = "
            with recursive PrepClassTree (id, pid) as (
                select
                    pc.PrepClass_id,
                    pc.PrepClass_pid
                from rls.v_PrepClass pc
                where coalesce(pc.PrepClass_pid, 0) = coalesce(:PrepClass_id, 0)
                union all
                select
                    pc.PrepClass_id,
                    pc.PrepClass_pid
                from
                    rls.v_PrepClass pc
                    inner join PrepClassTree pct on pc.PrepClass_pid = pct.id
            )
            select pct.id as \"id\"
            from PrepClassTree pct
        ";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (is_object($result)) {
			$arr = $result->result("array");
			for ($i = 0; $i < count($arr); $i++) {
				$prepclass[] = $arr[$i]["id"];
			}
		}
		$prepclass_filter = "DN.PrepClass_id in (" . join(",", $prepclass) . ")";
		$filters = "";
		if (!empty($data["query"])) {
			$filters .= " and DN.DrugNomen_Name ilike :query";
			$params["query"] = "%" . $data["query"] . "%";
		}
		$query = "
			select
                -- select
                DN.DrugNomen_id as \"DrugNomen_id\",
                DN.Drug_id as \"Drug_id\",
                DN.DrugNomen_Code as \"DrugNomen_Code\",
                DN.DrugNomen_Name as \"DrugNomen_Name\",
				DN.GoodsUnit_id as \"GoodsUnit_id\",
				GU.GoodsUnit_Name as \"GoodsUnit_Name\"
                -- end select
			from
                -- from
				rls.v_DrugNomen DN
				left join v_GoodsUnit GU on GU.GoodsUnit_id = DN.GoodsUnit_id
				left join lateral (
				    select
				        DrugNomenOrgLink_id,
				        DrugNomenOrgLink_Code
				    from
				        rls.v_DrugNomenOrgLink
				        left join v_Lpu L on v_DrugNomenOrgLink.Org_id = L.Org_id
				    where v_DrugNomenOrgLink.DrugNomen_id = DN.DrugNomen_id
				      and L.Lpu_id = :Lpu_id
				    order by DrugNomenOrgLink_id
				    limit 1
				) as DNOL on true
            -- end from
			where
            -- where
                DNOL.DrugNomenOrgLink_id is not null
			  and DN.GoodsUnit_id is not null
			  and {$prepclass_filter}
			  {$filters}
            -- end where
			order by
            -- order by
                 DN.DrugNomen_Code
            -- end order by
		";
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function loadNsiDrugDoseCombo(DrugNomen_model $callObject, $data)
	{
		$where = [];
		$params = [];
		if (!empty($data["DrugDose_id"])) {
			$where[] = "dd.DrugDose_id = :DrugDose_id";
			$params["DrugDose_id"] = $data["DrugDose_id"];
		} else {
			if (!empty($data["query"])) {
				$where[] = "(dd.DrugDose_Code ilike :query or dd.DrugDose_Name ilike :query)";
				$params["query"] = "" . $data["query"] . "%";
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
                dd.DrugDose_id as \"DrugDose_id\",
                coalesce(dd.DrugDose_Code||' - ', '')||dd.DrugDose_Name as \"DrugDose_Name\"
			from nsi.v_DrugDose dd
            $where_clause
            order by dd.DrugDose_id
			limit 100
		";
		$result = $callObject->queryResult($query, $params);
		return $result;
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function loadNsiDrugKolDoseCombo(DrugNomen_model $callObject, $data)
	{
		$where = [];
		$params = [];
		if (!empty($data["DrugKolDose_id"])) {
			$where[] = "dkd.DrugKolDose_id = :DrugKolDose_id";
			$params["DrugKolDose_id"] = $data["DrugKolDose_id"];
		} else {
			if (!empty($data["query"])) {
				$where[] = "(dkd.DrugKolDose_Code ilike :query or dkd.DrugKolDose_KolDose ilike :query)";
				$params["query"] = "" . $data["query"] . "%";
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
                dkd.DrugKolDose_id as \"DrugKolDose_id\",
                coalesce(dkd.DrugKolDose_Code||' - ', '')||dkd.DrugKolDose_KolDose as \"DrugKolDose_Name\"
			from nsi.v_DrugKolDose dkd
            $where_clause
            order by dkd.DrugKolDose_id
			limit 100
		";
		$result = $callObject->queryResult($query, $params);
		return $result;
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadDrugNomenEditForm(DrugNomen_model $callObject, $data)
	{
		$query = "
			select
				DN.DrugNomen_id as \"DrugNomen_id\",
				DN.Drug_id as \"Drug_id\",
				DN.DrugNomen_Code as \"DrugNomen_Code\",
				DN.DrugNomen_Nick as \"DrugNomen_Nick\",
				DMC.DrugMnnCode_id as \"DrugMnnCode_id\",
				DMC.DrugMnnCode_Code as \"DrugMnnCode_Code\",
				DTC.DrugTorgCode_id as \"DrugTorgCode_id\",
				DTC.DrugTorgCode_Code as \"DrugTorgCode_Code\",
				DCMC.DrugComplexMnnCode_id as \"DrugComplexMnnCode_id\",
				coalesce(Drug.DrugComplexMnn_id, 0) as \"DrugComplexMnn_id\",
				DCMC.DrugComplexMnnCode_Code as \"DrugComplexMnnCode_Code\",
				DN.Okpd_id as \"Okpd_id\"
			from
				rls.v_DrugNomen DN
				left join rls.v_DrugMnnCode DMC on DMC.DrugMnnCode_id = DN.DrugMnnCode_id
				left join rls.v_DrugTorgCode DTC on DTC.DrugTorgCode_id = DN.DrugTorgCode_id
				left join rls.v_DrugComplexMnnCode DCMC on DCMC.DrugComplexMnnCode_id = DN.DrugComplexMnnCode_id
				left join rls.v_Drug Drug on Drug.Drug_id = DN.Drug_id
			where DN.DrugNomen_id = :DrugNomen_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadDrugVznData(DrugNomen_model $callObject, $data)
	{
		$query = "
			select
				coalesce(dv.DrugVZN_fid, nsi_dv.DrugVZN_id) as \"DrugVZN_fid\", -- МНН
				coalesce(dv.DrugRelease_id, nsi_dr.DrugRelease_id) as \"DrugRelease_id\", -- Торг. наим.
				coalesce(dv.DrugFormVZN_id, nsi_dfv.DrugFormVZN_id) as \"DrugFormVZN_id\", -- Лек.форма 
				dv.DrugDose_id as \"DrugDose_id\", -- Дозировка
				coalesce(dv.DrugKolDose_id, nsi_dkd.DrugKolDose_id) as \"DrugKolDose_id\", -- Кол-во доз в уп.
				coalesce(nsi_dv.DrugVZN_Code||' - ', '')||nsi_dv.DrugVZN_Name as \"DrugMnnVZN_Code\",
				coalesce(nsi_dr.DrugRelease_Code||' - ', '')||nsi_dr.DrugRelease_Name as \"TradeNamesVZN_Code\",
				coalesce(nsi_dfv.DrugFormVZN_Code||' - ', '')||nsi_dfv.DrugFormVZN_Name as \"DrugFormVZN_Code\"
			from
				rls.v_Drug d
				left join lateral (
					select
						i_dv.DrugVZN_fid,
						i_dv.DrugFormVZN_id,
						i_dv.DrugDose_id,
						i_dv.DrugKolDose_id,
						i_dv.DrugRelease_id 
					from rls.DrugVzn i_dv
					where i_dv.Drug_id = d.Drug_id
					order by i_dv.DrugVZN_id
					limit 1
				) as dv on true
				left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
				left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.v_ACTMATTERS am on am.ACTMATTERS_ID = dcmn.ActMatters_id
				left join rls.v_TRADENAMES tn on tn.TRADENAMES_ID = d.DrugTorg_id
				left join rls.CLSDRUGFORMS cdf on cdf.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
				left join lateral (
					select
						i_nsi_dv.DrugVzn_id,
						i_nsi_dv.DrugVzn_Code,
						i_nsi_dv.DrugVzn_Name
					from nsi.v_DrugVzn i_nsi_dv
					where i_nsi_dv.DrugVzn_id = dv.DrugVZN_fid
					   or (dv.DrugVZN_fid is null and i_nsi_dv.ACTMATTERS_ID = dcmn.ActMatters_id)
					order by i_nsi_dv.DrugVzn_id
					limit 1
				) as nsi_dv on true
				left join lateral (
					select
						i_nsi_dr.DrugRelease_id,
						i_nsi_dr.DrugRelease_Code,
						i_nsi_dr.DrugRelease_Name
					from nsi.v_DrugRelease i_nsi_dr
					where i_nsi_dr.DrugRelease_id = dv.DrugRelease_id
					   or (dv.DrugRelease_id is null and i_nsi_dr.TRADENAMES_ID = d.DrugTorg_id)
					order by i_nsi_dr.DrugRelease_id
					limit 1
				) as nsi_dr on true
				left join lateral (
					select i_nsi_dkd.DrugKolDose_id
					from nsi.v_DrugKolDose i_nsi_dkd
					where i_nsi_dkd.DrugKolDose_KolDose = d.Drug_Fas::varchar
					order by i_nsi_dkd.DrugKolDose_id
					limit 1
				) as nsi_dkd on true
				left join nsi.v_DrugFormVZN nsi_dfv on nsi_dfv.DrugFormVZN_id = coalesce(dv.DrugFormVZN_id, cdf.DrugFormVZN_id)
				left join nsi.v_DrugDose nsi_dd on nsi_dd.DrugDose_id = dv.DrugDose_id
			where d.Drug_id = :Drug_id;
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadOkpdList(DrugNomen_model $callObject, $data)
	{
		if (empty($data["query"]) && empty($data["Okpd_id"])) {
			return false;
		}
		$where = "";
		$params = [];

		if (!empty($data["Okpd_id"])) {
			$where .= " O.Okpd_id = :Okpd_id";
			$params["Okpd_id"] = $data["Okpd_id"];
		} else if (!empty($data["query"])) {
			$where .= " Okpd_Name ilike '%'||:Okpd_Name||'%'";
			$params = ["Okpd_Name" => $data["query"]];
		}
		$query = "
			select distinct
				O.Okpd_id as \"Okpd_id\",
				O.Okpd_Code as \"Okpd_Code\",
				O.Okpd_Name as \"Okpd_Name\"
			from v_Okpd O
			where {$where}
			limit 500
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$response = $result->result("array");
		if (!is_array($response) || count($response) == 0) {
			return false;
		}
		return $response;
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadDrugRMZList(DrugNomen_model $callObject, $data)
	{
		$queryParams = [];
		$whereArray = [];
		if (!empty($data["no_rls"]) && $data["no_rls"] == 1) {
			$whereArray[] = "dr.Drug_id is null";
		}
		if (!empty($data["DrugRMZ_MNN"])) {
			$whereArray[] = "dr.DrugRMZ_MNN ilike :DrugRMZ_MNN";
			$queryParams["DrugRMZ_MNN"] = "%" . $data["DrugRMZ_MNN"] . "%";
		}
		if (!empty($data["DrugRMZ_Name"])) {
			$whereArray[] = "dr.DrugRMZ_Name ilike :DrugRMZ_Name";
			$queryParams["DrugRMZ_Name"] = "%" . $data["DrugRMZ_Name"] . "%";
		}
		if (!empty($data["DrugRMZ_Form"])) {
			$whereArray[] = "dr.DrugRMZ_Form ilike :DrugRMZ_Form";
			$queryParams["DrugRMZ_Form"] = "%" . $data["DrugRMZ_Form"] . "%";
		}
		if (!empty($data["DrugRMZ_Dose"])) {
			$whereArray[] = "dr.DrugRMZ_Dose ilike :DrugRMZ_Dose";
			$queryParams["DrugRMZ_Dose"] = "%" . $data["DrugRMZ_Dose"] . "%";
		}
		if (!empty($data["DrugRMZ_PackSize"])) {
			$whereArray[] = "dr.DrugRMZ_PackSize::varchar ilike :DrugRMZ_PackSize";
			$queryParams["DrugRMZ_PackSize"] = "%" . $data["DrugRMZ_PackSize"] . "%";
		}
		if (!empty($data["DrugRMZ_RegNum"])) {
			$whereArray[] = "dr.DrugRMZ_RegNum ilike :DrugRMZ_RegNum";
			$queryParams["DrugRMZ_RegNum"] = "%" . $data["DrugRMZ_RegNum"] . "%";
		}
		if (!empty($data["DrugRMZ_Firm"])) {
			$whereArray[] = "dr.DrugRMZ_Firm ilike :DrugRMZ_Firm";
			$queryParams["DrugRMZ_Firm"] = "%" . $data["DrugRMZ_Firm"] . "%";
		}
		$whereString = (count($whereArray) != 0) ? "where
                                                    -- where
                                                    " . implode(" and ", $whereArray)."
                                                    -- end where": "";
		$query = "
			select
            -- select
				dr.DrugRMZ_id as \"DrugRMZ_id\",
				dr.DrugRPN_id as \"DrugRPN_id\",
				dr.DrugRMZ_RegNum as \"DrugRMZ_RegNum\",
				to_char(dr.DrugRMZ_RegDate, '{$callObject->dateTimeForm104}') as \"DrugRMZ_RegDate\",
				dr.DrugRMZ_MNN as \"DrugRMZ_MNN\",
				dr.DrugRMZ_EAN13Code as \"DrugRMZ_EAN13Code\",
				dr.DrugRMZ_CodeRZN as \"DrugRMZ_CodeRZN\",
				dr.DrugRMZ_Name as \"DrugRMZ_Name\",
				dr.DrugRMZ_Form as \"DrugRMZ_Form\",
				dr.DrugRMZ_Dose as \"DrugRMZ_Dose\",
				dr.DrugRMZ_Pack as \"DrugRMZ_Pack\",
				dr.DrugRMZ_PackSize as \"DrugRMZ_PackSize\",
				dr.DrugRMZ_Firm as \"DrugRMZ_Firm\",
				dr.DrugRMZ_Country as \"DrugRMZ_Country\",
				dr.DrugRMZ_FirmPack as \"DrugRMZ_FirmPack\",
				dr.DrugRMZ_CountryPack as \"DrugRMZ_CountryPack\",
				dr.DrugRMZ_GodnDate as \"DrugRMZ_GodnDate\",
				dr.DrugRMZ_GodnDateDay as \"DrugRMZ_GodnDateDay\"
            -- end select
			from
            -- from
                rls.v_DrugRMZ dr
            -- end from
			    {$whereString}
			order by
            -- order by
                dr.DrugRPN_id
            -- end order by
		";
		/**
         * @var CI_DB_result $result
         * @var CI_DB_result $result_count
         */
		$result = $callObject->db->query(getLimitSQLPH($query, $data["start"], $data["limit"]), $queryParams);
		$result_count = $callObject->db->query(getCountSQLPH($query), $queryParams);
		if (is_object($result_count)) {
			$cnt_arr = $result_count->result("array");
			$count = $cnt_arr[0]["cnt"];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		if (!is_object($result)) {
			return false;
		}
		$response = [];
		$response["data"] = $result->result("array");
		$response["totalCount"] = $count;
		return $response;
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadDrugRMZListByQuery(DrugNomen_model $callObject, $data)
	{
		$queryParams = [];
		$join = "";
		$queryParams["Reg_Num"] = !empty($data["Reg_Num"]) ? $data["Reg_Num"] : null;
		$queryParams["Drug_Ean"] = !empty($data["Drug_Ean"]) ? $data["Drug_Ean"] : null;
		$where = "where ((:Reg_Num is not null and DrugRMZ_RegNum = :Reg_Num) or (:Drug_Ean is not null and DrugRMZ_EAN13Code = :Drug_Ean))";
		if (!empty($data["no_rls"]) && $data["no_rls"] == 1) {
			$where .= " and Drug_id is null";
		}
		if (!empty($data["Drug_Fas"]) && $data["Drug_Fas"] > 0) {
			$where .= " and DrugRMZ_PackSize::float8 = :Drug_Fas::float8";
			$queryParams["Drug_Fas"] = $data["Drug_Fas"];
		}
		if (strlen($data["query"]) > 0) {
			$query_arr = explode(" ", $data["query"]);
			$w_arr = [];
			foreach ($query_arr as $qr) {
				if (!empty($qr)) {
					$w2_arr = [];
					$w2_arr[] = "DrugRMZ_Name ilike '%{$qr}%'";
					$w2_arr[] = "DrugRMZ_Form ilike '%{$qr}%'";
					$w2_arr[] = "DrugRMZ_Dose ilike '%{$qr}%'";
					$w2_arr[] = "DrugRMZ_Pack ilike '%{$qr}%'";
					$w2_arr[] = "DrugRMZ_PackSize::varchar ilike '%{$qr}%'";
					$w2_arr[] = "DrugRMZ_Firm ilike '%{$qr}%'";
					$w_arr[] = join(" or ", $w2_arr);
				}
			}
			if (count($w_arr) > 0) {
				$where .= " and (" . join(") and (", $w_arr) . ")";
			}
		}
		$query = "
			select distinct
				DrugRMZ_id as \"DrugRMZ_id\",
				DrugRPN_id as \"DrugRPN_id\",
				coalesce(DrugRMZ_RegNum, '')||coalesce(' '||to_char(DrugRMZ_RegDate, '{$callObject->dateTimeForm104}'), '') as \"DrugRMZ_RegNum\",
				DrugRMZ_EAN13Code as \"DrugRMZ_EAN13Code\",
				DrugRMZ_Name as \"DrugRMZ_Name\",
				DrugRMZ_Form as \"DrugRMZ_Form\",
				DrugRMZ_Dose as \"DrugRMZ_Dose\",
				DrugRMZ_Pack as \"DrugRMZ_Pack\",
				DrugRMZ_PackSize as \"DrugRMZ_PackSize\",
				DrugRMZ_Firm as \"DrugRMZ_Firm\",
				DrugRMZ_Country as \"DrugRMZ_Country\",
				coalesce(DrugRMZ_FirmPack||', ', '')||coalesce(DrugRMZ_CountryPack, '') as \"DrugRMZ_FirmPack\",
				DrugRMZ_CountryPack as \"DrugRMZ_CountryPack\"
			from
				rls.v_DrugRMZ
				{$join}
			{$where}
			limit 100
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	public static function loadDrugMnnCode(DrugNomen_model $callObject, $data)
	{
		$query = "
			select
				DrugMnnCode_id as \"DrugMnnCode_id\",
				ACTMATTERS_id as \"Actmatters_id\",
				DrugMnnCode_Code as \"DrugMnnCode_Code\"
			from rls.v_DrugMnnCode
			where DrugMnnCode_id = :DrugMnnCode_id
		";
		$queryParams = ["DrugMnnCode_id" => $data["DrugMnnCode_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		if (!isset($result[0])) {
			return false;
		}
		return $result;
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $filter
	 * @return array|bool
	 */
   public static function loadDrugMnnCodeList(DrugNomen_model $callObject, $filter)
	{
		$where = [];
		if (!empty($filter["query"])) {
			$where[] = "am.RUSNAME ilike '%'||:query||'%'";
		}
		if (!empty($filter["DrugMnnCode_Code"])) {
			$where[] = "dcm.DrugMnnCode_Code = :DrugMnnCode_Code";
		}
		$where_clause = implode(" and ", $where);
		if (strlen($where_clause)) {
			$where_clause = "where 
			                -- where 
			                " . $where_clause . "
			                -- end where";
		}
		$query = "
			select
                -- select
				dcm.DrugMnnCode_id as \"DrugMnnCode_id\",
				dcm.ACTMATTERS_id as \"Actmatters_id\",
				dcm.DrugMnnCode_Code as \"DrugMnnCode_Code\",
				am.RUSNAME as \"ACTMATTERS_RUSNAME\"
                -- end select
			from
                -- from
				rls.v_DrugMnnCode dcm
				left join rls.v_ACTMATTERS am on am.ACTMATTERS_id = dcm.ACTMATTERS_id
				-- end from
				
			$where_clause
                
			order by
                -- order by
                dcm.DrugMnnCode_Code::float8 desc
                -- end order by
		";
		/**
         * @var CI_DB_result $result
         * @var CI_DB_result $result_count
         */
		$result = $callObject->db->query(getLimitSQLPH($query, $filter["start"], $filter["limit"]), $filter);
		$result_count = $callObject->db->query(getCountSQLPH($query), $filter);
		if (is_object($result_count)) {
			$cnt_arr = $result_count->result("array");
			$count = $cnt_arr[0]["cnt"];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		if (!is_object($result)) {
			return false;
		}
		$response = [];
		$response["data"] = $result->result("array");
		$response["totalCount"] = $count;
		return $response;
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	public static function loadDrugTorgCode(DrugNomen_model $callObject, $data)
	{
		$query = "
			select
				DrugTorgCode_id as \"DrugTorgCode_id\",
				TRADENAMES_id as \"Tradenames_id\",
				DrugTorgCode_Code as \"DrugTorgCode_Code\"
			from rls.v_DrugTorgCode
			where DrugTorgCode_id = :DrugTorgCode_id
		";
		$queryParams = ["DrugTorgCode_id" => $data["DrugTorgCode_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		if (!isset($result[0])) {
			return false;
		}
		return $result;
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $filter
	 * @return array|bool
	 */
   public static function loadDrugTorgCodeList(DrugNomen_model $callObject, $filter)
	{
		$where = [];
		if (!empty($filter["query"])) {
			$where[] = "tn.NAME ilike '%'||:query||'%'";
		}
		if (!empty($filter["DrugTorgCode_Code"])) {
			$where[] = "dtc.DrugTorgCode_Code = :DrugTorgCode_Code";
		}
		$where_clause = implode(" and ", $where);
		if (strlen($where_clause)) {
			$where_clause = "where 
			                --where 
			                " . $where_clause ."
			                -- end where";
		}
		$query = "
			select
                -- select
				dtc.DrugTorgCode_id as \"DrugTorgCode_id\",
				dtc.TRADENAMES_id as \"Tradenames_id\",
				dtc.DrugTorgCode_Code as \"DrugTorgCode_Code\",
				tn.NAME as \"TRADENAMES_NAME\"
                -- end select
			from
                -- from
				rls.v_DrugTorgCode dtc
				left join rls.v_TRADENAMES tn on tn.TRADENAMES_id = dtc.TRADENAMES_id
			    -- end from
			
			$where_clause
                
			order by
                -- order by
                dtc.DrugTorgCode_Code::float8 desc
                -- end order by
		";
		/**
         * @var CI_DB_result $result
         * @var CI_DB_result $result_count
         */
		$result = $callObject->db->query(getLimitSQLPH($query, $filter["start"], $filter["limit"]), $filter);
		$result_count = $callObject->db->query(getCountSQLPH($query), $filter);
		if (is_object($result_count)) {
			$cnt_arr = $result_count->result("array");
			$count = $cnt_arr[0]["cnt"];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		if (!is_object($result)) {
			return false;
		}
		$response = [];
		$response["data"] = $result->result("array");
		$response["totalCount"] = $count;
		return $response;
	}
	/**
	 * @param DrugNomen_model $callObject
	 * @param $filter
	 * @return array|bool
	 */
	public static function loadDboDrugMnnCodeListByName(DrugNomen_model $callObject, $filter)
	{
		$where = [];
		if (!empty($filter["DrugMnn_Name"])) {
			$where[] = "DrugMnn_Name ilike :DrugMnn_Name";
			$filter["DrugMnn_Name"] = preg_replace('/\*/', '', $filter["DrugMnn_Name"]);
			$filter["DrugMnn_Name"] = preg_replace('/\-/', '%', $filter["DrugMnn_Name"]);
			$filter["DrugMnn_Name"] = preg_replace('/ /', '%', $filter["DrugMnn_Name"]);
			$filter["DrugMnn_Name"] = "%" . $filter["DrugMnn_Name"] . "%";
		}
		$where_clause = implode(" and ", $where);
		if (strlen($where_clause)) {
			$where_clause = "where " . $where_clause;
		}
		$query = "
			select
				DrugMnn_id as \"DrugMnn_id\",
				DrugMnn_Code as \"DrugMnn_Code\",
				DrugMnn_Name as \"DrugMnn_Name\",
				DrugMnn_Code::varchar||' '||DrugMnn_Name as \"DrugMnn_FullName\"
			from v_DrugMnn
			$where_clause
			order by DrugMnn_Name desc
			limit 100
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $filter);
		if (is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $filter
	 * @return array|bool
	 */
	public static function loadDboDrugTorgCodeListByName(DrugNomen_model $callObject, $filter)
	{
		$where = [];
		if (!empty($filter["DrugTorg_Name"])) {
			$where[] = "DrugTorg_Name ilike :DrugTorg_Name";
			$filter["DrugTorg_Name"] = preg_replace('/\*/', '', $filter["DrugTorg_Name"]);
			$filter["DrugTorg_Name"] = preg_replace('/\-/', '%', $filter["DrugTorg_Name"]);
			$filter["DrugTorg_Name"] = preg_replace('/ /', '%', $filter["DrugTorg_Name"]);
			$filter["DrugTorg_Name"] = "%" . $filter["DrugTorg_Name"] . "%";
		}
		$where_clause = implode(" and ", $where);
		if (strlen($where_clause)) {
			$where_clause = "where " . $where_clause;
		}
		$query = "
			select
				DrugTorg_id as \"DrugTorg_id\",
				DrugTorg_Code as \"DrugTorg_Code\",
				DrugTorg_Name as \"DrugTorg_Name\",
				DrugTorg_Code::varchar||' '||DrugTorg_Name as \"DrugTorg_FullName\"
			from v_DrugTorg
			$where_clause
			order by DrugTorg_Name desc
			limit 100
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $filter);
		if (is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $filter
	 * @return array|bool
	 */
	public static function loadDrugFormMnnVZNCombo(DrugNomen_model $callObject, $filter)
	{
		$where = [];
		if (!empty($filter["DrugFormMnnVZN_id"]) && $filter["DrugFormMnnVZN_id"] > 0) {
			$where[] = "dfmv.DrugFormMnnVZN_id = :DrugFormMnnVZN_id";
		} else {
			if (!empty($filter["query"]) && strlen($filter["query"]) > 0) {
				$filter["query"] = "%" . $filter["query"] . "%";
				$where[] = "dfmv.DrugFormVipVZN_Name ilike :query";
			}
			if (!empty($filter["Drug_id"]) && $filter["Drug_id"]) {
				$where[] = "
					dfmv.DrugMnnVZN_Code in (
						select dmv.DrugMnnVZN_Code
						from
							rls.v_Drug d
							left join rls.DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
							left join rls.DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
							left join rls.v_prep p on p.Prep_id = d.DrugPrep_id
							inner join rls.v_DrugMnnVZN dmv on dmv.ACTMATTERS_ID = dcmn.ActMatters_id
							inner join rls.TradeNamesVZN tnv on tnv.TRADENAMES_ID = p.TRADENAMEID
						where d.Drug_id = :Drug_id
					)
				";
			}
		}
		$where_sql = join($where, ' and ');
		if (!empty($where_sql)) {
			$where_sql = "where {$where_sql}";
		}
		$query = "
			select
				dfmv.DrugFormMnnVZN_id as \"DrugFormMnnVZN_id\",
				dfmv.DrugFormVipVZN_Name as \"DrugFormVipVZN_Name\"
			from rls.v_DrugFormMnnVZN dfmv
			{$where_sql}
			limit 500
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $filter);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function loadDrugNomenList(DrugNomen_model $callObject, $data)
	{
		$queryParams = [];
		$whereArray = [];
		if (!empty($data["DrugNomen_id"])) {
			$queryParams["DrugNomen_id"] = $data["DrugNomen_id"];
			$whereArray[] = "DrugNomen_id = :DrugNomen_id";
		} else {
			if (!empty($data["query"])) {
				$queryBy = !empty($data["queryBy"]) ? $data["queryBy"] : null;
				$queryParams["query"] = $data["query"];
				switch ($queryBy) {
					case "DrugComplexMnnCode_Code":
						$whereArray[] = "DCMC.DrugComplexMnnCode_Code ilike :query||'%'";
						break;
					default:
						$whereArray[] = "DN.DrugNomen_Code||' '||DN.DrugNomen_Name ilike '%'||:query||'%'";
				}
			}
			if (!empty($data["DrugComplexMnn_id"])) {
				$queryParams["DrugComplexMnn_id"] = $data["DrugComplexMnn_id"];
				$whereArray[] = "D.DrugComplexMnn_id = :DrugComplexMnn_id";
			}
			if (!empty($data["Tradenames_id"])) {
				$queryParams["Tradenames_id"] = $data["Tradenames_id"];
				$whereArray[] = "D.DrugTorg_id = :Tradenames_id";
			}
		}
		$whereString = (count($whereArray) != 0)?"where ".implode(" and ", $whereArray):"";
		$query = "
			select
				DN.DrugNomen_id as \"DrugNomen_id\",
				DN.DrugNomen_Name as \"DrugNomen_Name\",
				DN.DrugNomen_Code as \"DrugNomen_Code\",
				D.Drug_id as \"Drug_id\",
				D.Drug_Ean as \"Drug_Ean\",
				D.Drug_Dose as \"Drug_Dose\",
				D.DrugForm_Name as \"DrugForm_Name\",
				D.DrugTorg_id as \"DrugTorg_id\",
				D.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				DCMC.DrugComplexMnnCode_Code as \"DrugComplexMnnCode_Code\"
			from
				rls.v_DrugNomen DN
				left join rls.v_Drug D on D.Drug_id = DN.Drug_id
				left join rls.v_DrugComplexMnnCode DCMC on DCMC.DrugComplexMnnCode_id = DN.DrugComplexMnnCode_id
			{$whereString}
			order by DN.DrugNomen_Name
			limit 500
		";
		return $callObject->queryResult($query, $queryParams);
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function loadGoodsPackCountList(DrugNomen_model $callObject, $data)
	{
		$params = [];
		$where = "";
		if (isset($data["GoodsUnit_id"])) {
			$params["GoodsUnit_id"] = $data["GoodsUnit_id"];
			$where .= " and GPC.GoodsUnit_id = :GoodsUnit_id ";
		}
		if (isset($data["DrugComplexMnn_id"])) {
			$params["DrugComplexMnn_id"] = $data["DrugComplexMnn_id"];
			$where .= " and GPC.DrugComplexMnn_id = :DrugComplexMnn_id ";
		}
		$query = "
			select
				GPC.GoodsPackCount_id as \"GoodsPackCount_id\",
				GPC.GoodsPackCount_Count as \"GoodsPackCount_Count\",
				GPC.GoodsUnit_id as \"GoodsUnit_id\"
			from v_GoodsPackCount GPC
			where (1=1)
				{$where}
			order by GPC.GoodsPackCount_Count
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function loadGoodsPackCountListGrid(DrugNomen_model $callObject, $data)
	{
		$params = [
			"DrugComplexMnn_id" => $data["DrugComplexMnn_id"],
			"Org_id" => $data["Org_id"]
		];
		$query = "
			select
				GPC.GoodsPackCount_id as \"GoodsPackCount_id\",
				GPC.GoodsPackCount_Count::float8 as \"GoodsPackCount_Count\",
				GPC.GoodsUnit_id as \"GoodsUnit_id\",
				GPC.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				gu.GoodsUnit_Name as \"GoodsUnit_Name\",
				GPC.Org_id as \"Org_id\",
				coalesce(o.Org_Name, 'Регион') as \"Org_Name\"
			from
				v_GoodsPackCount GPC
				left join v_GoodsUnit gu on gu.GoodsUnit_id = GPC.GoodsUnit_id
				left join v_Org o on o.Org_id = GPC.Org_id
			where GPC.DrugComplexMnn_id = :DrugComplexMnn_id
			  and (coalesce(GPC.Org_id, 0) = coalesce(:Org_id::bigint, 0) or GPC.Org_id is null)
			order by GPC.GoodsPackCount_id
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param DrugNomen_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function loadDrugPrepEdUcCountListGrid(DrugNomen_model $callObject, $data)
	{
		$params = [
			"Drug_id" => $data["Drug_id"],
			"Org_id" => $data["Org_id"]
		];
		$query = "
			select
				dpeuc.DrugPrepEdUcCount_id as \"DrugPrepEdUcCount_id\",
				dpeuc.DrugPrepEdUcCount_Count as \"DrugPrepEdUcCount_Count\",
				dpeuc.DrugPrepFas_id as \"DrugPrepFas_id\",
				dpeuc.GoodsUnit_id as \"GoodsUnit_id\",
				D.Drug_id as \"Drug_id\",
				gu.GoodsUnit_Name as \"GoodsUnit_Name\",
				dpeuc.Org_id as \"Org_id\",
				coalesce(o.Org_Name, 'Регион') as \"Org_Name\"
			from
				rls.v_DrugPrepEdUcCount dpeuc
				left join rls.v_Drug D on D.DrugPrepFas_id = dpeuc.DrugPrepFas_id
				left join v_GoodsUnit gu on gu.GoodsUnit_id = dpeuc.GoodsUnit_id
				left join v_Org o on o.Org_id = dpeuc.Org_id
			where D.Drug_id = :Drug_id
			  and (coalesce(dpeuc.Org_id, 0) = coalesce(:Org_id, 0) or dpeuc.Org_id is null)
			order by dpeuc.DrugPrepEdUcCount_id
		";
		return $callObject->queryResult($query, $params);
	}
}