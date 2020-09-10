<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * RegistryRecept_model - модель для работы с таблицей RegistryRecept
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Registry
 * @access       public
 * @copyright    Copyright (c) 2012 Swan Ltd.
 * @author       Vlasenko Dmitry
 * @version      20.12.2012
 */

class RegistryRecept_model extends swPgModel
{
	var $schema = "dbo"; //региональная схема

	/**
	 *  Конструктор
	 */
	function __construct() {
		parent::__construct();

		//установка региональной схемы
		$config = get_config();
		$this->schema = $config['regions'][getRegionNumber()]['schema'];
	}

	/**
	 *  Данные рецепта по реестру
	 */
	function loadRegistryReceptDataGrid($data) {
		$query = "
			select
				RR2.RegistryRecept_id as \"RegistryReceptData_id\",
				RR2.RegistryRecept_DrugNomCode as \"Drug_Code\",
				RR2.RegistryRecept_Persent as \"Drug_Ser\",
				COALESCE(RR2.RegistryRecept_DrugKolvo,0) as \"Drug_KolVo\",

				RR2.RegistryRecept_Price as \"Drug_Sum\",
				case 
					when COALESCE(RR2.RegistryRecept_DrugKolvo,0) > 0 then ((RR2.RegistryRecept_Price/RR2.RegistryRecept_DrugKolvo)::numeric(20,2))::text

					else (0::numeric(20,2))::text
				end as \"Drug_Price\",
				RR2.RegistryRecept_SupplyNum as \"WhsDocumentSupply_Num\",
				rlsdrug.DrugTorg_Name as \"Drug_Name\"
			from
				{$this->schema}.v_RegistryRecept RR 

				inner join 
                {$this->schema}.v_RegistryRecept RR2  on RR2.RegistryRecept_ReceptId = RR.RegistryRecept_ReceptId and RR2.ReceptUploadLog_id = RR.ReceptUploadLog_id

				LEFT JOIN LATERAL(

					select
						dn.Drug_id,
						dn.DrugNomen_Name,
						d.DrugTorg_Name
					from
						rls.v_DrugNomen dn 

						inner join rls.v_Drug d  on d.Drug_id = dn.Drug_id

					where
						dn.DrugNomen_Code = RR2.RegistryRecept_DrugNomCode
                    limit 1
				) rlsdrug ON true
			where
				RR.RegistryRecept_id = :RegistryRecept_id
		";
		
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Данные о выписке рецепта
	 */
	function loadRegistryReceptEvnReceptGrid($data) {
		$query = "
			select
				ER.EvnRecept_id as \"RegistryReceptEvnRecept_id\",
				ER.EvnRecept_Ser || ' №' || ER.EvnRecept_Num as \"EvnRecept_Num\",
				to_char(ER.EvnRecept_setDate, 'DD.MM.YYYY') as \"EvnRecept_setDate\",

				RV.ReceptValid_Name as \"EvnRecept_endDate\",
				D.Diag_Code as \"Diag_Code\",
				case when COALESCE(ER.EvnRecept_IsKEK,1) = 1 then 'Нет' else 'Да' end as \"EvnRecept_isVK\",

				RF.ReceptFinance_Name as \"EvnRecept_Finance\",
				RD.ReceptDiscount_Name as \"EvnRecept_Persent\",
				am.RUSNAME as \"EvnRecept_Mnn\",
				RTRIM(LTRIM(COALESCE(MP.Person_Surname, '') || ' ' || COALESCE(MP.Person_Firname, '') || ' ' || COALESCE(MP.Person_Secname, ''))) as \"MedPersonal_Fio\",

				COALESCE(cast(L.Lpu_Ouz as varchar) || ' ','') || L.Lpu_Nick as \"Lpu_Name\",

				RDT.ReceptDelayType_Name as \"EvnRecept_Status\"
			from
				{$this->schema}.v_RegistryRecept RR 

				inner join v_ReceptOtovUnSub RO  on RO.ReceptOtov_id = RR.ReceptOtov_id

				inner join v_EvnRecept ER  on ER.EvnRecept_id = RO.EvnRecept_id

				left join v_Diag D  on D.Diag_id = ER.Diag_id

				left join rls.v_Drug DR  on DR.Drug_id = RO.Drug_cid

				left join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = DR.DrugComplexMnn_id

				left join rls.v_DrugComplexMnnName dcmn  on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id

				left join rls.v_ActMatters am  on am.ActMatters_id = dcmn.ActMatters_id

				left join dbo.v_ReceptValid RV  on RV.ReceptValid_id = ER.ReceptValid_id

				left join v_ReceptFinance RF  on RF.ReceptFinance_id = ER.ReceptFinance_id

				left join v_Lpu L  on ER.Lpu_id = L.Lpu_id

				left join v_ReceptDiscount RD  on RD.ReceptDiscount_id = ER.ReceptDiscount_id

				left join v_MedPersonal MP  on MP.MedPersonal_id = ER.MedPersonal_id

				left join v_ReceptDelayType RDT  on RDT.ReceptDelayType_id = ER.ReceptDelayType_id

			where
				RR.RegistryRecept_id = :RegistryRecept_id
		";
		
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Данные об обеспечении рецепта лекарственными средствами
	 */
	function loadRegistryReceptReceptOtovGrid($data) {
		$query = "
			select
				RO.ReceptOtov_id as \"RegistryReceptReceptOtov_id\",
				RO.EvnRecept_Ser || ' №' || RO.EvnRecept_Num as \"EvnRecept_Num\",
				to_char(RO.EvnRecept_setDate, 'DD.MM.YYYY') as \"EvnRecept_setDate\",

				D.Diag_Code as \"Diag_Code\",
				RF.ReceptFinance_Name as \"EvnRecept_Finance\",
				am.RUSNAME as \"EvnRecept_Mnn\",
				RTRIM(LTRIM(COALESCE(MP.Person_Surname, '') || ' ' || COALESCE(MP.Person_Firname, '') || ' ' || COALESCE(MP.Person_Secname, ''))) as \"MedPersonal_Fio\",

				COALESCE(cast(L.Lpu_Ouz as varchar) || ' ','') || L.Lpu_Nick as \"Lpu_Name\",

				RDT.ReceptDelayType_Name as \"EvnRecept_Status\"
			from
				{$this->schema}.v_RegistryRecept RR 

				inner join v_ReceptOtovUnSub RO  on RO.ReceptOtov_id = RR.ReceptOtov_id

				left join v_Diag D  on D.Diag_id = RO.Diag_id

				left join rls.v_Drug DR  on DR.Drug_id = RO.Drug_cid

				left join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = DR.DrugComplexMnn_id

				left join rls.v_DrugComplexMnnName dcmn  on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id

				left join rls.v_ActMatters am  on am.ActMatters_id = dcmn.ActMatters_id

				left join v_ReceptFinance RF  on RF.ReceptFinance_id = RO.ReceptFinance_id

				left join v_Lpu L  on RO.Lpu_id = L.Lpu_id

				left join v_MedPersonal MP  on MP.MedPersonal_id = RO.MedPersonalRec_id

				left join v_ReceptDelayType RDT  on RDT.ReceptDelayType_id = RO.ReceptDelayType_id

			where
				RR.RegistryRecept_id = :RegistryRecept_id
		";
		
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Данные об отпущенных аптеке лекарственных средствах
	 */
	function loadRegistryReceptDocumentUcGrid($data) {
		$query = "
			select
				DUS.DocumentUcStr_id as \"RegistryReceptDocumentUc_id\",
				ER.EvnRecept_Ser || ' №' || ER.EvnRecept_Num as \"EvnRecept_Num\",
				COALESCE(DUS.DocumentUcStr_Count,0) as \"DocumentUcStr_Count\",

				DUS.DocumentUcStr_Sum as \"DocumentUcStr_Sum\",
				DUS.DocumentUcStr_Price as \"DocumentUcStr_Price\",
				DUS.DocumentUcStr_Ser as \"DocumentUcStr_Ser\",
				DU.DocumentUc_Num as \"DocumentUc_Num\",
				D.Drug_Name as \"Drug_Name\"
			from
				{$this->schema}.v_RegistryRecept RR 

				inner join v_DocumentUcStr DUS  on DUS.ReceptOtov_id = RR.ReceptOtov_id

				left join v_DocumentUc DU  on DU.DocumentUc_id = DUS.DocumentUc_id

				left join v_EvnRecept ER  on ER.EvnRecept_id = DUS.EvnRecept_id

				left join rls.v_Drug D  on D.Drug_id = DUS.Drug_id

			where
				RR.RegistryRecept_id = :RegistryRecept_id
		";
		
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Список реестров рецептов
	 */
	function loadDrugOstatRegistryList($data) {
		$params = array();
		$from = '';
		$filter="(1=1)";

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
					$mz_id = $this->getFirstResultFromQuery("select dbo.GetMinzdravDloOrgId() as \"mz_id\";");
					$filter .= " and ORG.Org_id = :Minzdrav_id";
					$params['Minzdrav_id'] = $mz_id;
					break;
				case 'mo':
					//https://redmine.swan.perm.ru/issues/76362#note-15 для МО отображать все организации
					//$filter .= " and org_type.OrgType_Code = 11"; //11 - МО (Медицинская организация);
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
			$filter .= " and DOR.Org_id=:Org_id";
			$params['Org_id'] = $data['Org_id'];
		}

		if (!empty($data['Storage_id'])) {
			$filter .= " and DOR.Storage_id=:Storage_id";
			$params['Storage_id'] = $data['Storage_id'];
		} else if (!empty($data['LpuBuilding_id']) || !empty($data['LpuSection_id'])) {
			$struct_filter_ssl = "1=1";
			$struct_filter_ms = "1=1";
			if (!empty($data['LpuBuilding_id'])) {
				$struct_filter_ssl .= " and SSL.LpuBuilding_id = :LpuBuilding_id";
				$struct_filter_ms .= " and MS.LpuBuilding_id = :LpuBuilding_id";
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
			}
			if (!empty($data['LpuSection_id'])) {
				$struct_filter_ssl .= " and SSL.LpuSection_id = :LpuSection_id";
				$struct_filter_ms .= " and MS.LpuSection_id = :LpuSection_id";
				$params['LpuSection_id'] = $data['LpuSection_id'];
			}
			$filter .= " and STOR.Storage_id in (
				select
				    Storage_id
				from
				    v_StorageStructLevel SSL 

				    left join v_MedService MS  on MS.MedService_id = SSL.MedService_id

				where
				    ({$struct_filter_ssl}) or
				    ({$struct_filter_ms})
			)";
		}

        if (empty($data['Storage_id']) && !empty($data['Storage_id_state'])) {
            if ($data['Storage_id_state'] == 'empty') {
                $filter .= " and DOR.Storage_id is null";
            }
            if ($data['Storage_id_state'] == 'not_empty') {
                $filter .= " and DOR.Storage_id is not null";
            }
        }

		if (!empty($data['SubAccountType_id'])) {
			$filter .= " and DOR.SubAccountType_id=:SubAccountType_id";
			$params['SubAccountType_id'] = $data['SubAccountType_id'];
		}
		
		if (!empty($data['WhsDocumentCostItemType_id'])) {
			$filter .= " and DOR.WhsDocumentCostItemType_id=:WhsDocumentCostItemType_id";
			$params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		}
		
		if (!empty($data['WhsDocumentUc_Date'])) {
			$filter .= " and WDS.WhsDocumentUc_Date=:WhsDocumentUc_Date";
			$params['WhsDocumentUc_Date'] = $data['WhsDocumentUc_Date'];
		}
		
		if (!empty($data['WhsDocumentUc_Num'])) {
			$filter .= " and WDS.WhsDocumentUc_Num iLIKE (:WhsDocumentUc_Num||'%')";

			$params['WhsDocumentUc_Num'] = $data['WhsDocumentUc_Num'];
		}
		
		if (!empty($data['WhsDocumentUc_Name'])) {
			$filter .= " and WDS.WhsDocumentUc_Name iLIKE :WhsDocumentUc_Name||'%'";

			$params['WhsDocumentUc_Name'] = $data['WhsDocumentUc_Name'];
		}

		if (!empty($data['AllowReservation']) && $data['AllowReservation'] == 1) {
			$from .= "
				LEFT JOIN LATERAL(

					select
						SUM(ER.EvnRecept_Kolvo) as SumEvnRecept_Kolvo
					from
						v_OrgFarmacy OrgF 

						left join v_EvnRecept ER  on ER.OrgFarmacy_id = OrgF.OrgFarmacy_id

						left join v_YesNo IsNotOstat  on IsNotOstat.YesNo_id = ER.EvnRecept_IsNotOstat

					where
						OrgF.Org_id = DOR.Org_id
						and ER.Drug_rlsid = DOR.Drug_id
						and ER.WhsDocumentCostItemType_id = DOR.WhsDocumentCostItemType_id
						and ER.ReceptDelayType_id is null
						and DATEDIFF('DAY', ER.EvnRecept_setDate, GETDATE())<=3
						and (ER.EvnRecept_IsNotOstat is null or IsNotOstat.YesNo_Code = 0)
						and DOR.SubAccountType_id = 1
					group by
						ER.Drug_id
				) SER ON true
			";
			$filter .= " and (COALESCE(DOR.DrugOstatRegistry_Kolvo, 0) - COALESCE(SER.SumEvnRecept_Kolvo, 0)) > 0";

		} else {
			$from .= "
				LEFT JOIN LATERAL(

					select 0 as SumEvnRecept_Kolvo
				) SER ON true
			";
		}

		if (!empty($data['DrugNomen_Code'])) {
			$filter .= ' and DN.DrugNomen_Code = :DrugNomen_Code';
			$params['DrugNomen_Code'] = $data['DrugNomen_Code'];
		}

		if (!empty($data['DrugComplexMnnCode_Code'])) {
			$from .= " left join rls.v_DrugComplexMnnCode DCMC  on DCMC.DrugComplexMnnCode_id = DN.DrugComplexMnnCode_id";

			$filter .= ' and DCMC.DrugComplexMnnCode_Code = :DrugComplexMnnCode_Code';
			$params['DrugComplexMnnCode_Code'] = $data['DrugComplexMnnCode_Code'];
		}

		if (!empty($data['RlsActmatters_RusName'])) {
			$filter .= " and AM.RUSNAME iLIKE :RlsActmatters_RusName||'%'";

			$params['RlsActmatters_RusName'] = $data['RlsActmatters_RusName'];
		}

		if (!empty($data['RlsTorg_Name'])) {
			$filter .= " and TN.NAME iLIKE :RlsTorg_Name||'%'";

			$params['RlsTorg_Name'] = $data['RlsTorg_Name'];
		}

		if (!empty($data['RlsClsdrugforms_Name'])) {
			$from .= " left join rls.CLSDRUGFORMS CDF  on CDF.CLSDRUGFORMS_ID = DCM.CLSDRUGFORMS_ID";

			$filter .= " and CDF.FULLNAME iLIKE :RlsClsdrugforms_Name||'%'";

			$params['RlsClsdrugforms_Name'] = $data['RlsClsdrugforms_Name'];
		}

		if (!empty($data['PrepSeries_Ser'])) {
			$filter .= " and ps.PrepSeries_Ser iLIKE :PrepSeries_Ser";

			$params['PrepSeries_Ser'] = $data['PrepSeries_Ser'];
		}

		if (!empty($data['PrepSeries_isDefect'])) {
			$filter .= " and COALESCE(isdef.YesNo_Code, 0) = :PrepSeries_isDefect";

			$params['PrepSeries_isDefect'] = $data['PrepSeries_isDefect'];
		}

		if (!empty($data['PrepSeries_godnMinMonthCount'])) {
			$filter .= " and (PS.PrepSeries_GodnDate is null or datediff('month', dbo.tzGetDate(), PS.PrepSeries_GodnDate) >= :PrepSeries_godnMinMonthCount)";
			$params['PrepSeries_godnMinMonthCount'] = $data['PrepSeries_godnMinMonthCount'];
		}

		if (!empty($data['PrepSeries_godnMaxMonthCount'])) {
			$filter .= " and (PS.PrepSeries_GodnDate is null or datediff('month', dbo.tzGetDate(), PS.PrepSeries_GodnDate) <= :PrepSeries_godnMaxMonthCount)";
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

		if (!empty($data['isPKU'])) {
			$filter .= ' and (COALESCE(AM.STRONGGROUPID, 0) > 0 or COALESCE(AM.NARCOGROUPID, 0) > 0)';

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

		if (!empty($data['AccountType_id'])) {
			$filter .= ' and DS.AccountType_id = :AccountType_id';
			$params['AccountType_id'] = $data['AccountType_id'];
		}

		if (!empty($data['CLSATC_ID'])) {
			//Получение списка потомков для фильтрации по ним
			$query = "
				with RECURSIVE Rec (CLSATC_ID) 
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

					$from .= " inner join rls.v_PREP_ATC PA  on PA.PREPID = D.DrugPrep_id";

					$filter .= " and PA.UNIQID in ({$atc_arr})";
				}
			}
		}

		if (!empty($data['CLSPHARMAGROUP_ID'])) {
			//Получение списка потомков для фильтрации по ним
			$query = "
				with RECURSIVE Rec(CLSPHARMAGROUP_ID)
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
					$from .= " inner join rls.v_PREP_PHARMAGROUP PP  on PP.PREPID = D.DrugPrep_id";

					$filter .= " and PP.UNIQID in ({$ph_gr_str})";
				}
			}
		}

		if (!empty($data['CLS_MZ_PHGROUP_ID'])) {
			//Получение списка потомков для фильтрации по ним
			$query = "
				with RECURSIVE Rec(CLS_MZ_PHGROUP_ID)
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
					$from .= " inner join rls.TRADENAMES_DRUGFORMS TD  on TD.TRADENAMEID = P.TRADENAMEID and TD.DRUGFORMID = DCM.CLSDRUGFORMS_ID";

					$filter .= " and TD.MZ_PHGR_ID in ({$mz_pg_str})";
				}
			}
		}

		if (!empty($data['KLAreaStat_id'])) {
			$filter .= " and area_stat.KLAreaStat_id = :KLAreaStat_id";
			$params['KLAreaStat_id'] = $data['KLAreaStat_id'];
		}

		if (!empty($data['GoodsUnit_id'])) {
			$filter .= " and GU.GoodsUnit_id = :GoodsUnit_id";
			$params['GoodsUnit_id'] = $data['GoodsUnit_id'];
		}

		$select = "";

		/*if (getRegionNick() == 'kz') {
			// Ед. изм
			$select .= ", case when gpc.GoodsUnit_Descr in ('единицы в упаковках', 'единицы количества', 'лекарственная форма') then COALESCE(gpc.GoodsUnit_Nick,'') else COALESCE(DFORM.NAME,'') end as Okei_Name";

			$select .= ", case when gpc.GoodsUnit_Descr in ('единицы в упаковках', 'единицы количества', 'лекарственная форма') then 1 else 0 end as IsGoodsUnit";
			// Кол-во
			$select .= ",
				case
					when gpc.GoodsUnit_Nick iLIKE 'уп%' then

						STR(COALESCE(DOR.DrugOstatRegistry_Kolvo, 0) - COALESCE(SER.SumEvnRecept_Kolvo, 0), 10, 2)

					when gpc.GoodsPackCount_Count is not null then
						STR((COALESCE(DOR.DrugOstatRegistry_Kolvo, 0) - COALESCE(SER.SumEvnRecept_Kolvo, 0)) * gpc.GoodsPackCount_Count, 10, 2)

					else
						STR((COALESCE(DOR.DrugOstatRegistry_Kolvo, 0) - COALESCE(SER.SumEvnRecept_Kolvo, 0)) * COALESCE(D.Drug_Fas, 1), 10, 2)

				end as DrugOstatRegistry_Kolvo
			";
			$select .= ",
				case
					when gpc.GoodsUnit_Nick iLIKE 'уп%' then

						STR(DOR.DrugOstatRegistry_Cost, 10, 2)
					when gpc.GoodsPackCount_Count is not null and gpc.GoodsPackCount_Count > 0 then
						STR(DOR.DrugOstatRegistry_Cost / gpc.GoodsPackCount_Count, 10, 2)
					when COALESCE(D.Drug_Fas, 1) > 0 then

						STR(DOR.DrugOstatRegistry_Cost / COALESCE(D.Drug_Fas, 1), 10, 2)

					else
						'0.00'
				end as DrugOstatRegistry_Price
			";
		} else {*/
			$select .= ", COALESCE(O.Okei_Name, '') as \"Okei_Name\"";

			$select .= ", ((COALESCE(DOR.DrugOstatRegistry_Kolvo, 0) - COALESCE(SER.SumEvnRecept_Kolvo, 0))::numeric(20,2))::text as \"DrugOstatRegistry_Kolvo\"";

			$select .= ", ((DOR.DrugOstatRegistry_Cost)::numeric(20,2))::text as \"DrugOstatRegistry_Price\"";

			// Ед. изм лек. форм
			$select .= ", case when gpc.GoodsUnit_Descr in ('единицы в упаковках', 'единицы количества', 'лекарственная форма') then COALESCE(gpc.GoodsUnit_Nick,'') else COALESCE(DFORM.NAME,'') end as \"Okei_NameLek\"";

			$select .= ", case when gpc.GoodsUnit_Descr in ('единицы в упаковках', 'единицы количества', 'лекарственная форма') then 1 else 0 end as \"IsGoodsUnit\"";

			// Кол-во лек. форм
			$select .= ",
				case
					when gpc.GoodsPackCount_Count is not null then
						(((COALESCE(DOR.DrugOstatRegistry_Kolvo, 0) - COALESCE(SER.SumEvnRecept_Kolvo, 0)) * gpc.GoodsPackCount_Count)::numeric(20,2))::text

					else
						(((COALESCE(DOR.DrugOstatRegistry_Kolvo, 0) - COALESCE(SER.SumEvnRecept_Kolvo, 0)) * COALESCE(D.Drug_Fas, 1))::numeric(20,2))::text

				end as \"DrugOstatRegistry_KolvoLek\"
			";
			$select .= ",
				case
					when gpc.GoodsPackCount_Count is not null and ((COALESCE(DOR.DrugOstatRegistry_Kolvo, 0) - COALESCE(SER.SumEvnRecept_Kolvo, 0)) * gpc.GoodsPackCount_Count) > 0 then

						((DOR.DrugOstatRegistry_Cost / ((COALESCE(DOR.DrugOstatRegistry_Kolvo, 0) - COALESCE(SER.SumEvnRecept_Kolvo, 0)) * gpc.GoodsPackCount_Count))::numeric(20,2))::text

					when ((COALESCE(DOR.DrugOstatRegistry_Kolvo, 0) - COALESCE(SER.SumEvnRecept_Kolvo, 0)) * COALESCE(D.Drug_Fas, 1)) > 0 then

						((DOR.DrugOstatRegistry_Cost / ((COALESCE(DOR.DrugOstatRegistry_Kolvo, 0) - COALESCE(SER.SumEvnRecept_Kolvo, 0)) * COALESCE(D.Drug_Fas, 1)))::numeric(20,2))::text

					else
						'0.00'
				end as \"DrugOstatRegistry_PriceLek\"
			";
		//}

        //получение единиц учета по умолчанию
        $default_goodsunit_id = $this->getFirstResultFromQuery("
            select
                GoodsUnit_id as \"GoodsUnit_id\"
            from
                v_GoodsUnit 

            where
                GoodsUnit_Name = 'упаковка'
            order by
                GoodsUnit_id
        ");
        $params['DefaultGoodsUnit_id'] = $default_goodsunit_id > 0 ? $default_goodsunit_id : null;

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

				DOR.Drug_id as \"Drug_id\",
				COALESCE(D.Drug_Name, '') as \"Drug_Name\",
				case
					when COALESCE(SER.SumEvnRecept_Kolvo,0) > 0 then

						((COALESCE(DOR.DrugOstatRegistry_Sum - DOR.DrugOstatRegistry_Sum/DOR.DrugOstatRegistry_Kolvo*COALESCE(SER.SumEvnRecept_Kolvo, 0.00), 0.00))::numeric(20,2))::text

					else
						((COALESCE(DOR.DrugOstatRegistry_Sum, 0.00))::numeric(20,2))::text

				end as \"DrugOstatRegistry_Sum\",
				WDS.WhsDocumentUc_Num as \"WhsDocumentUc_Num\",
				fin_year.yr as \"WhsDocumentSupply_Year\",
				WDS.WhsDocumentUc_Name as \"WhsDocumentUc_Name\",
				to_char(WDS.WhsDocumentUc_Date, 'DD.MM.YYYY') as \"WhsDocumentUc_Date\",

				DOR.DrugFinance_id as \"DrugFinance_id\",
				DF.DrugFinance_Name as \"DrugFinance_Name\",
				DOR.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				WDCIT.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
				AM.RUSNAME as \"ActMatters_RusName\",
				TN.NAME as \"Prep_Name\",
				DFORM.NAME as \"DrugForm_Name\",
				Dose.Value as \"Drug_Dose\",
				Fas.Value as \"Drug_Fas\",
				F.FULLNAME as \"Firm_Name\",
				rc.REGNUM as \"Reg_Num\",
				DN.DrugNomen_Code as \"DrugNomen_Code\",
				PS.PrepSeries_Ser as \"PrepSeries_Ser\",
				to_char(PS.PrepSeries_GodnDate, 'DD.MM.YYYY') as \"PrepSeries_GodnDate\",

				DOR.DrugShipment_id as \"DrugShipment_id\",
				to_char(DS.DrugShipment_setDT, 'DD.MM.YYYY') as \"DrugShipment_setDT\",

				DS.DrugShipment_Name as \"DrugShipment_Name\",
				to_char(DOR.DrugOstatRegistry_insDT, 'DD.MM.YYYY') as \"DrugOstatRegistry_insDT\",

				DOR.Contragent_id as \"Contragent_id\",
				GU.GoodsUnit_Nick as \"GoodsUnit_Nick\"
				{$select}
				-- end select
			from
				-- from
				v_DrugOstatRegistry DOR 

				left join rls.v_Drug D  on D.Drug_id = DOR.Drug_id

				left join v_Okei O  on O.Okei_id = DOR.Okei_id

				left join v_DrugShipment DS  on DS.DrugShipment_id = DOR.DrugShipment_id

				left join v_WhsDocumentSupply WDS  on WDS.WhsDocumentSupply_id = DS.WhsDocumentSupply_id

				left join v_DrugFinance DF  on DF.DrugFinance_id = DOR.DrugFinance_id

				left join v_WhsDocumentCostItemType WDCIT  on WDCIT.WhsDocumentCostItemType_id = DOR.WhsDocumentCostItemType_id

				left join v_Org ORG  on ORG.Org_id = DOR.Org_id

				left join v_OrgType org_type  on org_type.OrgType_id = ORG.OrgType_id

				left join v_Address org_ua  on org_ua.Address_id = ORG.UAddress_id

				left join v_Address org_pa  on org_pa.Address_id = ORG.PAddress_id

				left join v_Storage STOR  on STOR.Storage_id = DOR.Storage_id

				left join v_SubAccountType SAT  on SAT.SubAccountType_id = DOR.SubAccountType_id

				left join rls.v_DrugComplexMnn DCM  on DCM.DrugComplexMnn_id = D.DrugComplexMnn_id

				left join rls.v_DrugComplexMnnName DCMN  on DCMN.DrugComplexMnnName_id = DCM.DrugComplexMnnName_id

				left join rls.v_ACTMATTERS AM  on AM.ACTMATTERS_ID = DCMN.ActMatters_id

				left join rls.v_prep P  on P.Prep_id = D.DrugPrep_id

				left join rls.PrepSeries PS  on PS.PrepSeries_id = DOR.PrepSeries_id

				left join v_YesNo isdef  on isdef.YesNo_id = ps.PrepSeries_isDefect

				left join rls.v_TRADENAMES TN  on TN.TRADENAMES_ID = P.TRADENAMEID

				left join rls.v_FIRMS F  on F.FIRMS_ID = P.FIRMID

				left join rls.v_CLSDRUGFORMS DFORM  on DFORM.CLSDRUGFORMS_ID = DCM.CLSDRUGFORMS_ID

				left join rls.REGCERT RC  on RC.REGCERT_ID = P.REGCERTID

				left join rls.MASSUNITS DFMU  on DFMU.MASSUNITS_ID = P.DFMASSID

				left join rls.CONCENUNITS DFCU  on DFCU.CONCENUNITS_ID = P.DFCONCID

				left join rls.ACTUNITS DFAU  on DFAU.ACTUNITS_ID = P.DFACTID

				left join rls.SIZEUNITS DFSU  on DFSU.SIZEUNITS_ID = P.DFSIZEID

				left join rls.v_Nomen N  on N.NOMEN_ID = DOR.Drug_id

				left join rls.v_DrugPack dp  on dp.DRUGPACK_ID = N.PPACKID

				left join rls.MASSUNITS MU  on MU.MASSUNITS_ID = N.PPACKMASSUNID

				left join rls.CUBICUNITS CU  on CU.CUBICUNITS_ID = N.PPACKCUBUNID

				left join v_GoodsUnit GU  on GU.GoodsUnit_id = COALESCE(DOR.GoodsUnit_id, 
                :DefaultGoodsUnit_id
                )


				LEFT JOIN LATERAL (

					select 
						DrugNomen_id,
						DrugNomen_Code,
						DrugComplexMnnCode_id
					from
						rls.v_DrugNomen DN 

					where
						Drug_id = DOR.Drug_id
					order by
						DrugNomen_id
                    limit 1
				) DN ON true
				LEFT JOIN LATERAL (

					select coalesce(
						cast(cast(P.DFMASS as double precision) as varchar)||' '||DFMU.SHORTNAME,
						cast(cast(P.DFCONC as double precision) as varchar)||' '||DFCU.SHORTNAME,
						cast(P.DFACT as varchar)||' '||DFAU.SHORTNAME,
						cast(P.DFSIZE as varchar)||' '||DFSU.SHORTNAME
					) as Value
				) Dose ON true
				LEFT JOIN LATERAL(

					select (
						(case when D.Drug_Fas is not null then cast(D.Drug_Fas as varchar) else '' end)||
						(case when D.Drug_Fas is not null and coalesce(D.Drug_Volume,D.Drug_Mass) is not null then ', ' else '' end)||
						(case when coalesce(D.Drug_Volume,D.Drug_Mass) is not null then coalesce(D.Drug_Volume,D.Drug_Mass) else '' end)||
						(case when coalesce(N.PPACKVOLUME,N.PPACKMASS) is not null then ' (' || dp.Name || ' ' || cast(cast(coalesce(N.PPACKVOLUME,N.PPACKMASS) as decimal(10,2)) as varchar) || ' ' || coalesce(CU.SHORTNAME,MU.SHORTNAME) || ')' else '' end)
					) as Value
				) Fas ON true
				LEFT JOIN LATERAL (

					select
						date_part('year', COALESCE(max(i_wdd.WhsDocumentDelivery_setDT), wds.WhsDocumentUc_Date)) as yr

					from
						v_WhsDocumentDelivery i_wdd 

					where
						i_wdd.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
				) fin_year ON true
				LEFT JOIN LATERAL (

					select 
						coalesce(i_kl_t.KLTown_Name, i_kl_c.KLCity_Name, i_kl_sr.KLSubRgn_FullName, i_kl_r.KLRgn_FullName) as area_name,
						i_ost.KLCountry_id,
						i_ost.KLRgn_id,
						i_ost.KLSubRgn_id,
						i_ost.KLCity_id,
						i_ost.KLTown_id
					from
						v_OrgServiceTerr i_ost 

						left join v_KLRgn i_kl_r  on i_kl_r.KLRgn_id = i_ost.KLRgn_id

						left join v_KLSubRgn i_kl_sr  on i_kl_sr.KLSubRgn_id = i_ost.KLSubRgn_id

						left join v_KLCity i_kl_c  on i_kl_c.KLCity_id = i_ost.KLCity_id

						left join v_KLTown i_kl_t  on i_kl_t.KLTown_id = i_ost.KLTown_id

					where
						i_ost.Org_id = ORG.Org_id
                    limit 1
				) org_area ON true
				LEFT JOIN LATERAL (

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
				) area_stat ON true
                LEFT JOIN LATERAL ( -- получение ед. списания из партии прихода

                    select 
                        i_dus.GoodsUnit_id
                    from
                        v_DrugShipmentLink i_dsl2 

                        left join v_DocumentUcStr i_dus on i_dus.DocumentUcStr_id = i_dsl2.DocumentUcStr_id
                    where
                        i_dsl2.DrugShipment_id = COALESCE(ds.DrugShipment_pid, ds.DrugShipment_id) and

                        i_dus.Drug_id = D.Drug_id and
                        i_dus.GoodsUnit_id is not null
                    limit 1
                ) s_gu ON true
                LEFT JOIN LATERAL (

                    select 
                        i_gpc.GoodsPackCount_Count,
                        i_gu.GoodsUnit_Nick,
                        i_gu.GoodsUnit_Descr
                    from
                        v_GoodsPackCount i_gpc 

                        inner join v_GoodsUnit i_gu  on i_gu.GoodsUnit_id = i_gpc.GoodsUnit_id

                    where
                        i_gpc.DrugComplexMnn_id = D.DrugComplexMnn_id and
                        i_gpc.GoodsUnit_id = s_gu.GoodsUnit_id
                    limit 1
                ) gpc ON true
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
			$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
			$result_count = $this->db->query(getCountSQLPH($query), $params);

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
	 *  Сохранение данных рецепта
	 */
	function saveRegistryReceptData($data) {
		$query = "
			select RegistryRecept_id as \"RegistryRecept_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from {$this->schema}.p_RegistryRecept_ins(
				RegistryRecept_Snils := :RegistryRecept_Snils,
				RegistryRecept_UAddOKATO := :RegistryRecept_UAddOKATO,
				RegistryRecept_LpuOGRN := :RegistryRecept_LpuOGRN,
				RegistryRecept_LpuMod := :RegistryRecept_LpuMod,
				RegistryRecept_MedPersonalCode := :RegistryRecept_MedPersonalCode,
				RegistryRecept_Diag := :RegistryRecept_Diag,
				RegistryRecept_Recent := :RegistryRecept_Recent,
				RegistryRecept_setDT := :RegistryRecept_setDT,
				RegistryRecept_RecentFinance := :RegistryRecept_RecentFinance,
				RegistryRecept_Persent := :RegistryRecept_Persent,
				RegistryRecept_FarmacyACode := :RegistryRecept_FarmacyACode,
				RegistryRecept_DrugNomCode := :RegistryRecept_DrugNomCode,
				RegistryRecept_DrugKolvo := :RegistryRecept_DrugKolvo,
				RegistryRecept_DrugDose := :RegistryRecept_DrugDose,
				RegistryRecept_DrugCode := :RegistryRecept_DrugCode,
				RegistryRecept_obrDate := :RegistryRecept_obrDate,
				RegistryRecept_otpDate := :RegistryRecept_otpDate,
				RegistryRecept_Price := :RegistryRecept_Price,
				RegistryRecept_SchetType := :RegistryRecept_SchetType,
				RegistryRecept_FarmacyOGRN := :RegistryRecept_FarmacyOGRN,
				RegistryRecept_ProtoKEK := :RegistryRecept_ProtoKEK,
				RegistryRecept_SpecialCase := :RegistryRecept_SpecialCase,
				RegistryRecept_ReceptId := :RegistryRecept_ReceptId,
				RegistryRecept_SupplyNum := :RegistryRecept_SupplyNum,
				RegistryReceptType_id := :RegistryReceptType_id,
				pmUser_id := :pmUser_id);


		";

		$queryParams = array(
			'RegistryRecept_Snils' => $data['SS'],
			'RegistryRecept_UAddOKATO' => $data['OKATO_REG'],
			'RegistryRecept_LpuOGRN' => $data['C_OGRN'],
			'RegistryRecept_LpuMod' => $data['MCOD'],
			'RegistryRecept_MedPersonalCode' => $data['PCOD'],
			'RegistryRecept_Diag' => $data['DS'],
			'RegistryRecept_Recent' => $data['SN_LR'],
			'RegistryRecept_setDT' => $data['DATE_VR'],
			'RegistryRecept_RecentFinance' => $data['C_FINL'],
			'RegistryRecept_Persent' => $data['PR_LR'],
			'RegistryRecept_FarmacyACode' => $data['A_COD'],
			'RegistryRecept_DrugNomCode' => $data['NOMK_LS'],
			'RegistryRecept_DrugKolvo' => $data['KO_ALL'],
			'RegistryRecept_DrugDose' => $data['DOZ_ME'],
			'RegistryRecept_DrugCode' => $data['C_PFS'],
			'RegistryRecept_obrDate' => $data['DATE_OBR'],
			'RegistryRecept_otpDate' => $data['DATE_OTP'],
			'RegistryRecept_Price' => $data['SL_ALL'],
			'RegistryRecept_SchetType' => $data['TYPE_SCHET'],
			'RegistryRecept_FarmacyOGRN' => $data['FO_OGRN'],
			'RegistryRecept_ProtoKEK' => $data['P_KEK'],
			'RegistryRecept_SpecialCase' => $data['D_TYPE'],
			'RegistryRecept_ReceptId' => $data['RECIPEID'],
			'RegistryRecept_SupplyNum' => $data['GK_NUM'],
			'RegistryReceptType_id' => $data['RegistryReceptType_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		// echo getDebugSql($query, $queryParams); die();
		
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Сохранение данных о пациенте
	 */
	function saveRegistryReceptPersonData($data) {
		$query = "
			select RegistryReceptPerson_id as \"RegistryReceptPerson_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from {$this->schema}.p_RegistryReceptPerson_ins(
				RegistryReceptPerson_Snils := :RegistryReceptPerson_Snils,
				RegistryReceptPerson_Polis := :RegistryReceptPerson_Polis,
				RegistryReceptPerson_SurName := :RegistryReceptPerson_SurName,
				RegistryReceptPerson_FirName := :RegistryReceptPerson_FirName,
				RegistryReceptPerson_SecName := :RegistryReceptPerson_SecName,
				RegistryReceptPerson_Sex := :RegistryReceptPerson_Sex,
				RegistryReceptPerson_BirthDay := :RegistryReceptPerson_BirthDay,
				RegistryReceptPerson_Privilege := :RegistryReceptPerson_Privilege,
				RegistryReceptPerson_Document := :RegistryReceptPerson_Document,
				RegistryReceptPerson_DocumentType := :RegistryReceptPerson_DocumentType,
				RegistryReceptPerson_OmsSprTerrOKATO := :RegistryReceptPerson_OmsSprTerrOKATO,
				RegistryReceptPerson_SmoOGRN := :RegistryReceptPerson_SmoOGRN,
				RegistryReceptPerson_UAddOKATO := :RegistryReceptPerson_UAddOKATO,
				RegistryReceptPerson_SpecialCase := :RegistryReceptPerson_SpecialCase,
				RegistryReceptType_id := :RegistryReceptType_id,
				pmUser_id := :pmUser_id);


		";

		$queryParams = array(
			'RegistryReceptPerson_Snils' => $data['SS'],
			'RegistryReceptPerson_Polis' => $data['SN_POL'],
			'RegistryReceptPerson_SurName' => $data['FAM'],
			'RegistryReceptPerson_FirName' => $data['IM'],
			'RegistryReceptPerson_SecName' => $data['OT'],
			'RegistryReceptPerson_Sex' => $data['W'],
			'RegistryReceptPerson_BirthDay' => $data['DR'],
			'RegistryReceptPerson_Privilege' => $data['C_KAT'],
			'RegistryReceptPerson_Document' => $data['SN_DOC'],
			'RegistryReceptPerson_DocumentType' => $data['C_DOC'],
			'RegistryReceptPerson_OmsSprTerrOKATO' => $data['OKATO_OMS'],
			'RegistryReceptPerson_SmoOGRN' => $data['QM_OGRN'],
			'RegistryReceptPerson_UAddOKATO' => $data['OKATO_REG'],
			'RegistryReceptPerson_SpecialCase' => $data['D_TYPE'],
			'RegistryReceptType_id' => $data['RegistryReceptType_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Удаление реестра рецептов
	 */
	function deleteRegistryRecept($data) {
		$query = "
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from {$this->schema}.p_RegistryRecept_del(
				RegistryRecept_id := :RegistryRecept_id);


		";
		
		// echo getDebugSql($query, $data); die();
		
		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Список рецептов в реестре
	 */
	function loadRegistryDataReceptList($data) {
		$params = array();
		$filter="(1=1)";
		
		if (!empty($data['RegistryRecept_id'])) {
			$filter .= " and RDR.Registry_id=:RegistryRecept_id";
			$params['RegistryRecept_id'] = $data['RegistryRecept_id'];
		}
		
		if (!empty($data['RegistryDataRecept_Snils'])) {
			$filter .= " and RDR.RegistryDataRecept_Snils=:RegistryDataRecept_Snils";
			$params['RegistryDataRecept_Snils'] = $data['RegistryDataRecept_Snils'];
		}
		
		$query = "
			Select
				-- select
				RDR.RegistryDataRecept_id as \"RegistryDataRecept_id\",
				RDR.Registry_id as \"Registry_id\",
				RDR.RegistryDataRecept_Snils as \"RegistryDataRecept_Snils\",
				RDR.Polis_Ser as \"Polis_Ser\",
				RDR.Polis_Num as \"Polis_Num\",
				RDR.RegistryDataRecept_SurName as \"RegistryDataRecept_SurName\",
				RDR.RegistryDataRecept_FirName as \"RegistryDataRecept_FirName\",
				RDR.RegistryDataRecept_SecName as \"RegistryDataRecept_SecName\",
				RDR.Sex_id as \"Sex_id\",
				RDR.RegistryDataRecept_BirthDay as \"RegistryDataRecept_BirthDay\",
				RDR.PrivilegeType_id as \"PrivilegeType_id\",
				RDR.Document_Ser as \"Document_Ser\",
				RDR.Document_Num as \"Document_Num\",
				RDR.DocumentType_id as \"DocumentType_id\",
				RDR.OmsSprTerr_id as \"OmsSprTerr_id\",
				RDR.OrgSmo_id as \"OrgSmo_id\",
				RDR.OrgSmo_OGRN as \"OrgSmo_OGRN\",
				RDR.RegistryDataRecept_UAddOKATO as \"RegistryDataRecept_UAddOKATO\",
				RDR.Lpu_id as \"Lpu_id\",
				RDR.Lpu_OGRN as \"Lpu_OGRN\",
				RDR.Lpu_f003mcod as \"Lpu_f003mcod\",
				RDR.MedPersonalRec_id as \"MedPersonalRec_id\",
				RDR.Diag_id as \"Diag_id\",
				RDR.RegistryDataRecept_Ser as \"RegistryDataRecept_Ser\",
				RDR.RegistryDataRecept_Num as \"RegistryDataRecept_Num\",
				RDR.RegistryDataRecept_setDT as \"RegistryDataRecept_setDT\",
				RDR.ReceptFinance_id as \"ReceptFinance_id\",
				RDR.RegistryDataRecept_Persent as \"RegistryDataRecept_Persent\",
				RDR.OrgFarmacy_id as \"OrgFarmacy_id\",
				RDR.OrgFarmacy_OGRN as \"OrgFarmacy_OGRN\",
				RDR.RegistryDataRecept_DrugNomCode as \"RegistryDataRecept_DrugNomCode\",
				RDR.RegistryDataRecept_DrugKolvo as \"RegistryDataRecept_DrugKolvo\",
				RDR.RegistryDataRecept_DrugDose as \"RegistryDataRecept_DrugDose\",
				RDR.RegistryDataRecept_DrugCode as \"RegistryDataRecept_DrugCode\",
				RDR.RegistryDataRecept_obrDate as \"RegistryDataRecept_obrDate\",
				RDR.RegistryDataRecept_otpDate as \"RegistryDataRecept_otpDate\",
				RDR.RegistryDataRecept_Price as \"RegistryDataRecept_Price\",
				RDR.RegistryDataRecept_SchetType as \"RegistryDataRecept_SchetType\",
				RDR.RegistryDataRecept_ProtoKEK as \"RegistryDataRecept_ProtoKEK\",
				RDR.RegistryDataRecept_SpecialCase as \"RegistryDataRecept_SpecialCase\",
				RDR.RegistryDataRecept_ReceptId as \"RegistryDataRecept_ReceptId\",
				RDR.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
				RDR.RegistryType_id as \"RegistryType_id\"
				-- end select
			from
				-- from
				v_RegistryDataRecept RDR 

			-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				RDR.RegistryDataRecept_id
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
	 *  Загрузка данных реестра рецептов
	 */
	function loadRegistryReceptViewForm($data) {
		$query = "
			Select
			-- select
				-- данные реестра
				RUL.ReceptUploadLog_id as \"ReceptUploadLog_id\",
				RUT.ReceptUploadType_Name as \"ReceptUploadType_Name\",
				to_char(RUL.ReceptUploadLog_setDT, 'DD.MM.YYYY') as \"ReceptUploadLog_setDT\",

				C.Contragent_Name as \"Contragent_Name\",
				RR.RegistryRecept_Price as \"RegistryRecept_Price\",
				-- данные пациента из реестра
				RTRIM(LTRIM(COALESCE(RRP.RegistryReceptPerson_Surname, ''))) || ' ' || RTRIM(LTRIM(COALESCE(RRP.RegistryReceptPerson_Firname, ''))) || ' ' || RTRIM(LTRIM(COALESCE(RRP.RegistryReceptPerson_Secname, ''))) as \"RegistryReceptPerson_Fio\",

				RRP.RegistryReceptPerson_Sex as \"RegistryReceptPerson_Sex\",
				to_char(cast(RRP.RegistryReceptPerson_BirthDay as timestamp), 'DD.MM.YYYY') as \"RegistryReceptPerson_BirthDay\",

				RRP.RegistryReceptPerson_Snils as \"RegistryReceptPerson_Snils\",
				RRP.RegistryReceptPerson_UAddOKATO as \"RegistryReceptPerson_UAddOKATO\",
				RRP.RegistryReceptPerson_Privilege || COALESCE(' ' || PT.PrivilegeType_Name,'') as \"RegistryReceptPerson_Privilege\",				

				-- данные идентифицированного пациента
				RO.Person_id as \"Person_id\",
				RTRIM(LTRIM(COALESCE(PS.Person_Surname, '') || ' ' || COALESCE(PS.Person_Firname, '') || ' ' || COALESCE(PS.Person_Secname, ''))) as \"Person_Fio\",

				S.Sex_Name as \"Sex_Name\",
				to_char(PS.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\",

				PS.Person_Snils as \"Person_Snils\",
				CASE
					WHEN street.KLStreet_id is not null and street.KLAdr_Ocatd is not null THEN street.KLAdr_Ocatd
					WHEN town.KLArea_id is not null and town.KLAdr_Ocatd is not null THEN town.KLAdr_Ocatd
					WHEN city.KLArea_id is not null and city.KLAdr_Ocatd is not null THEN city.KLAdr_Ocatd
					WHEN srgn.KLArea_id is not null and srgn.KLAdr_Ocatd is not null THEN srgn.KLAdr_Ocatd
					WHEN rgn.KLArea_id is not null and rgn.KLAdr_Ocatd is not null THEN rgn.KLAdr_Ocatd
					WHEN country.KLArea_id is not null and country.KLAdr_Ocatd is not null THEN country.KLAdr_Ocatd
					ELSE ''
				END as \"Person_OKATO\",
				-- данные рецепта по реестру
				RR.RegistryRecept_Recent as \"RegistryRecept_Recent\",
				RR.RegistryRecept_Diag as \"RegistryRecept_Diag\",
				case when COALESCE(RR.RegistryRecept_ProtoKEK,0) <> 0 then 'Да' else 'Нет' end as \"RegistryRecept_ProtoKEK\",

				RR.RegistryRecept_MedPersonalCode as \"RegistryRecept_MedPersonalCode\",
				RR.RegistryRecept_LpuMod as \"RegistryRecept_LpuMod\",
				ORF.OrgFarmacy_Name as \"OrgFarmacy_Name\",
				to_char(RR.RegistryRecept_obrDate, 'DD.MM.YYYY') as \"RegistryRecept_obrDate\",

				to_char(RR.RegistryRecept_otpDate, 'DD.MM.YYYY') as \"RegistryRecept_otpDate\",

				RF.ReceptFinance_Name as \"RegistryRecept_RecentFinance\",
				WDCIT.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
				RR.RegistryRecept_Persent as \"RegistryRecept_Persent\",
				case 
					when RR.RegistryRecept_SchetType = 0 then 'основной'
					when RR.RegistryRecept_SchetType = 1 then 'дополнительный'
					when RR.RegistryRecept_SchetType = 3 then 'скорректированный'
				end as \"RegistryRecept_SchetType\",
				DUCH.DocumentUc_Farmacy as \"DocumentUc_Farmacy\",
				DUCH.DocumentUc_Date as \"DocumentUc_Date\",
				DUCH.DocumentUc_Finance as \"DocumentUc_Finance\",
				DUCH.DocumentUc_Statya as \"DocumentUc_Statya\"
			-- end select
			from 
			-- from
				{$this->schema}.v_RegistryRecept RR 

				left join v_RegistryReceptType RRT  on RR.RegistryReceptType_id = RRT.RegistryReceptType_id

				left join v_ReceptUploadLog RUL  on RR.ReceptUploadLog_id = RUL.ReceptUploadLog_id

				left join v_Contragent C  on C.Contragent_id = RUL.Contragent_id

				left join v_ReceptUploadType RUT  on RUT.ReceptUploadType_id = RUL.ReceptUploadType_id

				left join v_ReceptOtovUnSub RO  on RO.ReceptOtov_id = RR.ReceptOtov_id

				left join v_PersonState PS  on PS.Person_id = RO.Person_id

				left join v_Sex S  on S.Sex_id = PS.Sex_id

				left join v_ReceptFinance RF  on RF.ReceptFinance_id = RR.RegistryRecept_RecentFinance

				left join v_Address_all UA  on PS.UAddress_id = UA.Address_id

				left join KLArea country  on country.KLArea_id = UA.KLCountry_id

				left join KLArea rgn  on rgn.KLArea_id = UA.KLRgn_id

				left join KLArea srgn  on srgn.KLArea_id = UA.KLSubRgn_id

				left join KLArea city  on city.KLArea_id = UA.KLCity_id

				left join KLArea town  on town.KLArea_id = UA.KLSubRgn_id

				left join KLStreet street  on street.KLStreet_id = UA.KLStreet_id

				LEFT JOIN LATERAL(

					select 
						ORGF.OrgFarmacy_Name as DocumentUc_Farmacy,
						to_char(DU.DocumentUc_setDate, 'DD.MM.YYYY') as DocumentUc_Date,

						DF.DrugFinance_Name as DocumentUc_Finance,
						WDCITDU.WhsDocumentCostItemType_Name as DocumentUc_Statya
					from v_DocumentUcStr DUS 

						inner join v_DocumentUc DU  on DU.DocumentUc_id = DUS.DocumentUc_id

						left join v_WhsDocumentCostItemType WDCITDU  on WDCITDU.WhsDocumentCostItemType_id = DU.WhsDocumentCostItemType_id

						left join v_DrugFinance DF  on DF.DrugFinance_id = DU.DrugFinance_id

						left join v_Contragent CDU  on CDU.Contragent_id = DU.Contragent_sid

						left join v_OrgFarmacy ORGF  on ORGF.OrgFarmacy_id = CDU.OrgFarmacy_id and ORGF.Org_id = DU.Org_id

					where DUS.ReceptOtov_id = RR.ReceptOtov_id
                    limit 1
				) DUCH ON true
				LEFT JOIN LATERAL(

					select WhsDocumentCostItemType_id from v_WhsDocumentSupply  where WhsDocumentUc_Num = RR.RegistryRecept_SupplyNum limit 1
 
				) WDS ON true
				left join v_EvnRecept ER  on ER.EvnRecept_id = RO.EvnRecept_id

				left join v_WhsDocumentCostItemType WDCIT  on WDCIT.WhsDocumentCostItemType_id = WDS.WhsDocumentCostItemType_id

				left join {$this->schema}.v_ReceptStatusFLKMEK RSFM  on RR.ReceptStatusFLKMEK_id = RSFM.ReceptStatusFLKMEK_id
				LEFT JOIN LATERAL(

					select 
						RegistryReceptPerson_Snils,
						RegistryReceptPerson_UAddOKATO,
						RegistryReceptPerson_Privilege,
						RegistryReceptPerson_BirthDay,
						RegistryReceptPerson_Sex,
						RegistryReceptPerson_Surname,
						RegistryReceptPerson_Firname,
						RegistryReceptPerson_Secname
					from 
						{$this->schema}.v_RegistryReceptPerson  

					where 
						RR.RegistryRecept_Snils = RegistryReceptPerson_Snils
                    limit 1
				) RRP ON true
				LEFT JOIN LATERAL(

					select PrivilegeType_Name FROM v_PrivilegeType  where PrivilegeType_Code = RRP.RegistryReceptPerson_Privilege limit 1

				) PT ON true

				LEFT JOIN LATERAL(

					select 
						RTRIM(LTRIM(COALESCE(MPers.Person_Surname, '') || ' ' || COALESCE(MPers.Person_Firname, '') || ' ' || COALESCE(MPers.Person_Secname, ''))) as MedPersonal_Name 

					from v_MedPersonal MPers  

						inner join v_Lpu L  on L.Lpu_id = MPers.Lpu_id

					where 
						MPers.MedPersonal_Code = SUBSTRING(RR.RegistryRecept_MedPersonalCode, POSITION (' ' IN RR.RegistryRecept_MedPersonalCode)+1, 9)
						and L.Lpu_OGRN = SUBSTRING(RR.RegistryRecept_MedPersonalCode, 0, POSITION (' ' IN RR.RegistryRecept_MedPersonalCode))
                    limit 1
				) MP ON true
				LEFT JOIN LATERAL(

					select Lpu_id, Lpu_Name from v_Lpu  where Lpu_Ouz::text = RR.RegistryRecept_LpuMod limit 1

				) L ON true
				LEFT JOIN LATERAL(

					select ORGF.OrgFarmacy_id, ORGF.OrgFarmacy_Name from v_OrgFarmacy ORGF 

						inner join v_Contragent C  on C.OrgFarmacy_id = ORGF.OrgFarmacy_id 

					where C.Contragent_Code::text = SUBSTRING(RR.RegistryRecept_FarmacyACode, POSITION (' ' IN RR.RegistryRecept_FarmacyACode)+1, 9) and C.ContragentType_id = 3 -- аптека
                    limit 1
				) ORF ON true
			-- end from
			where
			-- where
			RR.RegistryRecept_id = :RegistryRecept_id
			-- end where
			order by
			-- order by
				RR.RegistryRecept_id
			-- end order by";
				
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$query = "
					select COALESCE(PT.PrivilegeType_Name, '') as \"PrivilegeType_Name\"
                    from v_PersonPrivilege PP
                         inner join PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
                    where PP.Person_id =:Person_id and
                          PersonPrivilege_begDate <=:RegistryRecept_otpDate and
                          (PersonPrivilege_endDate >=:RegistryRecept_otpDate OR
                          PersonPrivilege_endDate IS NULL)
				";
				
				$resp[0]['PrivilegeType_Name'] = '';
				$result = $this->db->query($query, array('Person_id' => $resp[0]['Person_id'], 'RegistryRecept_otpDate' => date('Y-m-d', strtotime($resp[0]['RegistryRecept_otpDate']))));
				if ( is_object($result) ) {
					$resp_lgot = $result->result('array');
					$first = true;
					foreach($resp_lgot as $onelgot) {
						$resp[0]['PrivilegeType_Name'] .= ((!$first)?', ':'').$onelgot['PrivilegeType_Name'];
						$first = false;
					}
				}
			}
			return $resp;
		}
		else {
			return false;
		}
	}

	/**
	 *  Загрузка списка документов учета
	 */
	function loadDocumentUcList($data) {
        $result =  array();

        $query = "
            select sat.SubAccountType_Code as \"SubAccountType_Code\",
                   dor.DrugShipment_id  as \"DrugShipment_id\"
            from v_DrugOstatRegistry dor
                 left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
            where dor.DrugOstatRegistry_id =:DrugOstatRegistry_id;
		";
        $dor_data = $this->getFirstRowFromQuery($query, array(
            'DrugOstatRegistry_id' => $data['DrugOstatRegistry_id']
        ));

        if (is_array($dor_data) && count($dor_data) > 0) {
            $sub_query = "";
            $params = array();

            if ($dor_data['SubAccountType_Code'] == '2') { //2 - Зарезервировано
                $sub_query = "
                    select
                        i_dus.DocumentUc_id as \"DocumentUc_id\"
                    from
                        v_DrugOstatRegistryLink i_dorl 

                        left join v_DocumentUcStr i_dus  on i_dus.DocumentUcStr_id = i_dorl.DrugOstatRegistryLink_TableID

                    where
                        i_dorl.DrugOstatRegistry_id = :DrugOstatRegistry_id and
                        i_dorl.DrugOstatRegistryLink_TableName = 'DocumentUcStr'
    		    ";
                $params['DrugOstatRegistry_id'] = $data['DrugOstatRegistry_id'];
            } else {
                $sub_query = "
                    select
                        i_dus.DocumentUc_id as \"DocumentUc_id\"
                    from
                        v_DrugShipmentLink i_dsl 

                        left join v_DocumentUcStr i_dus  on i_dus.DocumentUcStr_id = i_dsl.DocumentUcStr_id

                    where
                        i_dsl.DrugShipment_id = :DrugShipment_id
    		    ";
                $params['DrugShipment_id'] = $dor_data['DrugShipment_id'];
            }

            $query = "
                select 
                    du.DocumentUc_id as \"DocumentUc_id\",
                    dds.DrugDocumentStatus_Name as \"DrugDocumentStatus_Name\",
                    du.DocumentUc_Num as \"DocumentUc_Num\",
                    to_char(du.DocumentUc_setDate, 'DD.MM.YYYY') as \"DocumentUc_setDate\",

                    ddt.DrugDocumentType_Name as \"DrugDocumentType_Name\",
                    (
                        COALESCE(c_s.Contragent_Name, '')||

                        COALESCE(' / '||s_s.Storage_Name, '')

                    ) as \"S_Name\",
                    (
                        COALESCE(c_t.Contragent_Name, '')||

                        COALESCE(' / '||s_t.Storage_Name, '')

                    ) as \"T_Name\"
                from
                    v_DocumentUc du 

                    left join v_DrugDocumentStatus dds  on dds.DrugDocumentStatus_id = du.DrugDocumentStatus_id

                    left join v_DrugDocumentType ddt  on ddt.DrugDocumentType_id = du.DrugDocumentType_id

                    left join v_Contragent c_s  on c_s.Contragent_id = du.Contragent_sid

                    left join v_Contragent c_t  on c_t.Contragent_id = du.Contragent_tid

                    left join v_Storage s_s  on s_s.Storage_id = du.Storage_sid

                    left join v_Storage s_t  on s_t.Storage_id = du.Storage_tid

                where
                    du.DocumentUc_id in ({$sub_query})
				limit 100
            ";

            $result = $this->queryResult($query, $params);
        }

		return $result;
	}

	/**
	 *  Список реестров рецептов
	 */
	function loadRegistryReceptList($data) {
		$filter = "(1=1)";
		$join = "";

		$params = array();

		if (!empty($data['ReceptUploadLog_id'])) {
			$filter .= ' and RR.ReceptUploadLog_id = :ReceptUploadLog_id';
			$params['ReceptUploadLog_id'] = $data['ReceptUploadLog_id'];
		}
		
		if (!empty($data['RegistryRecept_id'])) {
			$filter .= ' and RR.RegistryRecept_id = :RegistryRecept_id';
			$params['RegistryRecept_id'] = $data['RegistryRecept_id'];
		}
		
		if (!empty($data['RegistryRecept_Snils'])) {
			$filter .= ' and RR.RegistryRecept_Snils = :RegistryRecept_Snils';
			$params['RegistryRecept_Snils'] = $data['RegistryRecept_Snils'];
		}
		
		if (!empty($data['RegistryRecept_Fio'])) {
			$filter .= " and RTRIM(LTRIM(COALESCE(RRP.RegistryReceptPerson_Surname, '') || ' ' || COALESCE(RRP.RegistryReceptPerson_Firname, '') || ' ' || COALESCE(RRP.RegistryReceptPerson_Secname, ''))) iLIKE :RegistryRecept_Fio || '%'";


			$params['RegistryRecept_Fio'] = $data['RegistryRecept_Fio'];
		}
		
		if (!empty($data['PrivilegeType_Code'])) {
			$filter .= ' and RRP.RegistryReceptPerson_Privilege = :PrivilegeType_Code';
			$params['PrivilegeType_Code'] = $data['PrivilegeType_Code'];
		}
		
		if (!empty($data['RegistryRecept_Ser'])) {
			$filter .= " and RR.RegistryRecept_Recent iLIKE :RegistryRecept_Ser || ' %'";

			$params['RegistryRecept_Ser'] = $data['RegistryRecept_Ser'];
		}
		
		if (!empty($data['RegistryRecept_Num'])) {
			$filter .= " and RR.RegistryRecept_Recent iLIKE '% ' || :RegistryRecept_Num || '%'";

			$params['RegistryRecept_Num'] = $data['RegistryRecept_Num'];
		}
		
		if (!empty($data['MedPersonal_Name'])) {
			$filter .= ' and MP.MedPersonal_Name = :MedPersonal_Name';
			$params['MedPersonal_Name'] = $data['MedPersonal_Name'];
		}
		
		if (!empty($data['Lpu_id'])) {
			$filter .= ' and L.Lpu_id = :Lpu_id';
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		
		if (!empty($data['OrgFarmacy_id'])) {
			$filter .= ' and ORF.OrgFarmacy_id = :OrgFarmacy_id';
			$params['OrgFarmacy_id'] = $data['OrgFarmacy_id'];
		}
		
		if (!empty($data['RegistryReceptType_id'])) {
			$filter .= ' and RR.RegistryReceptType_id = :RegistryReceptType_id';
			$params['RegistryReceptType_id'] = $data['RegistryReceptType_id'];
		}

		if (!empty($data['Drug_id'])) {
			$filter .= ' and RO.Drug_cid = :Drug_id';
			$params['Drug_id'] = $data['Drug_id'];
		}

		$query = "
			Select
			-- select
				RR.RegistryRecept_id as \"RegistryRecept_id\",
				RSFM.ReceptStatusFLKMEK_Name as \"ReceptStatusFLKMEK_Name\",
				RRT.RegistryReceptType_Name as \"RegistryReceptType_Name\",
				RR.RegistryRecept_Recent as \"RegistryRecept_Recent\",
				to_char(RR.RegistryRecept_setDT, 'DD.MM.YYYY') as \"RegistryRecept_setDT\",

				case when COALESCE(RR.RegistryRecept_ProtoKEK,0) <> 0 then 'Да' else 'Нет' end as \"RegistryRecept_ProtoKEK\",

				RR.RegistryRecept_Snils as \"RegistryRecept_Snils\",
				RTRIM(LTRIM(COALESCE(RRP.RegistryReceptPerson_Surname, '') || ' ' || COALESCE(RRP.RegistryReceptPerson_Firname, '') || ' ' || COALESCE(RRP.RegistryReceptPerson_Secname, ''))) as \"RegistryReceptPerson_FIO\",

				RRP.RegistryReceptPerson_Sex as \"RegistryReceptPerson_Sex\",
				to_char(cast(RRP.RegistryReceptPerson_BirthDay as timestamp), 'DD.MM.YYYY') as \"RegistryReceptPerson_BirthDay\",

				RR.RegistryRecept_Diag as \"RegistryRecept_Diag\",
				RRP.RegistryReceptPerson_Privilege || COALESCE(' ' || PT.PrivilegeType_Name,'') as \"RegistryReceptPerson_Privilege\",

				RR.RegistryRecept_Persent as \"RegistryRecept_Persent\",
				RF.ReceptFinance_Name as \"RegistryRecept_RecentFinance\",
				to_char(RR.RegistryRecept_obrDate, 'DD.MM.YYYY') as \"RegistryRecept_obrDate\",

				to_char(RR.RegistryRecept_otpDate, 'DD.MM.YYYY') as \"RegistryRecept_otpDate\",

				RR.RegistryRecept_DrugNomCode as \"RegistryRecept_DrugNomCode\",
				D.DrugTorg_Name as \"Drug_Name\",
				DOLD.Drug_Name as \"Drug_NameOld\",
				COALESCE(RR.RegistryRecept_DrugKolvo,0) as \"RegistryRecept_DrugKolvo\",

				RR.RegistryRecept_Price as \"RegistryRecept_Price\",
				case 
					when COALESCE(RR.RegistryRecept_DrugKolvo,0) > 0 then CAST((RR.RegistryRecept_Price/RR.RegistryRecept_DrugKolvo)::numeric(20,2) as text)

					else CAST(0::numeric(20,2) as text)
				end as \"RegistryRecept_PriceOne\",
				RR.RegistryRecept_SupplyNum as \"RegistryRecept_SupplyNum\",
				RR.RegistryRecept_MedPersonalCode as \"RegistryRecept_MedPersonalCode\",
				MP.MedPersonal_Name as \"MedPersonal_Name\",
				RR.RegistryRecept_LpuMod as \"RegistryRecept_LpuMod\",
				L.Lpu_Name as \"Lpu_Name\",
				RR.RegistryRecept_FarmacyACode as \"RegistryRecept_FarmacyACode\",
				ORF.OrgFarmacy_Name as \"OrgFarmacy_Name\",
				RR.RegistryRecept_SchetType as \"RegistryRecept_SchetType\"
			-- end select
			from 
			-- from
				{$this->schema}.v_RegistryRecept RR 

				left join v_RegistryReceptType RRT  on RR.RegistryReceptType_id = RRT.RegistryReceptType_id

				left join v_ReceptOtovUnSub RO  on RO.ReceptOtov_id = RR.ReceptOtov_id

				left join rls.v_Drug D  on D.Drug_id = RO.Drug_cid

				left join v_Drug DOLD  on DOLD.Drug_id = RO.Drug_id

				left join v_ReceptFinance RF  on RF.ReceptFinance_id = RR.RegistryRecept_RecentFinance
				left join {$this->schema}.v_ReceptStatusFLKMEK RSFM  on RR.ReceptStatusFLKMEK_id = RSFM.ReceptStatusFLKMEK_id

				LEFT JOIN LATERAL(

					select 
						RegistryReceptPerson_Privilege,
						RegistryReceptPerson_BirthDay,
						RegistryReceptPerson_Sex,
						RegistryReceptPerson_Surname,
						RegistryReceptPerson_Firname,
						RegistryReceptPerson_Secname
					from 
						{$this->schema}.v_RegistryReceptPerson  

					where 
						RR.RegistryRecept_Snils = RegistryReceptPerson_Snils
                    limit 1
				) RRP ON true
				LEFT JOIN LATERAL(

					select PrivilegeType_Name FROM v_PrivilegeType  where PrivilegeType_Code = RRP.RegistryReceptPerson_Privilege limit 1
 
				) PT ON true

				LEFT JOIN LATERAL(

					select 
						RTRIM(LTRIM(COALESCE(MPers.Person_Surname, '') || ' ' || COALESCE(MPers.Person_Firname, '') || ' ' || COALESCE(MPers.Person_Secname, ''))) as MedPersonal_Name 

					from v_MedPersonal MPers  

						inner join v_Lpu L  on L.Lpu_id = MPers.Lpu_id

					where 
						MPers.MedPersonal_Code = SUBSTRING(RR.RegistryRecept_MedPersonalCode, POSITION (' ' IN RR.RegistryRecept_MedPersonalCode)+1, 9)
						and L.Lpu_OGRN = SUBSTRING(RR.RegistryRecept_MedPersonalCode, 0, POSITION (' ' IN RR.RegistryRecept_MedPersonalCode))
                    limit 1
				) MP ON true
				LEFT JOIN LATERAL(

					select Lpu_id, Lpu_Name from v_Lpu  where Lpu_Ouz::text = RR.RegistryRecept_LpuMod limit 1

				) L ON true
				LEFT JOIN LATERAL(

					select ORGF.OrgFarmacy_id, ORGF.OrgFarmacy_Name from v_OrgFarmacy ORGF 

						inner join v_Contragent C  on C.OrgFarmacy_id = ORGF.OrgFarmacy_id 

					where C.Contragent_Code::text = SUBSTRING(RR.RegistryRecept_FarmacyACode, POSITION (' ' IN RR.RegistryRecept_FarmacyACode)+1, 9) and C.ContragentType_id = 3 -- аптека
                    limit 1
				) ORF ON true
				{$join}
			-- end from
			where
			-- where
			{$filter}
			-- end where
			order by
			-- order by
				RR.RegistryRecept_id
			-- end order by";

		if (!isset($data['start'])) {
			$result = $this->db->query($query, $params);
			if (is_object($result))
			{
				return $result->result('array');
			} 
			else 
			{
				return false;
			}
		} else {
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
	}

	/**
	 *  Получение списка ошибок
	 */
	function loadRegistryReceptErrorList($data) {
		$filter = "(1=1)";
		$join = "";

		$params = array();

		if (!empty($data['RegistryRecept_id'])) {
			$filter .= ' and RRE.RegistryRecept_id = :RegistryRecept_id';
			$params['RegistryRecept_id'] = $data['RegistryRecept_id'];
		}
		
		$query = "
			Select
			-- select
				RRE.RegistryReceptError_id as \"RegistryReceptError_id\",
				RRET.RegistryReceptErrorType_Type as \"RegistryReceptErrorType_Type\",
				RRET.RegistryReceptErrorType_Name as \"RegistryReceptErrorType_Name\",
				ER.EvnRecept_Num as \"EvnRecept_Num\"
			-- end select
			from 
			-- from
				{$this->schema}.v_RegistryReceptError RRE 

				left join {$this->schema}.v_RegistryReceptErrorType RRET  on RRE.RegistryReceptErrorType_id = RRET.RegistryReceptErrorType_id

				left join v_EvnRecept ER  on ER.EvnRecept_id = RRE.EvnRecept_id

			-- end from
			where
			-- where
			{$filter}
			-- end where
			order by
			-- order by
				RRET.RegistryReceptErrorType_Type
			-- end order by";

		if (!isset($data['start'])) {
			$result = $this->db->query($query, $params);
			if (is_object($result))
			{
				return $result->result('array');
			} 
			else 
			{
				return false;
			}
		} else {
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
	}

	/**
	 *  Получение списка типов ошибок
	 */
	function loadRegistryReceptErrorTypeList($data) {
		$params = array();

		$query = "
			Select
				RRET.RegistryReceptErrorType_id as \"RegistryReceptErrorType_id\",
				RRET.RegistryReceptErrorType_Type as \"RegistryReceptErrorType_Type\",
				RRET.RegistryReceptErrorType_Name as \"RegistryReceptErrorType_Name\"
			from
				{$this->schema}.v_RegistryReceptErrorType RRET 

		";

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 *  Обновляет статус активности критерия экспертизы реестров
	 */
	function saveRegistryReceptExpertiseTypeActive($data)
	{
		$query = "
			select RegistryReceptExpertiseType_id as \"RegistryReceptExpertiseType_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from p_RegistryReceptExpertiseType_upd(
				RegistryReceptExpertiseType_id := :RegistryReceptExpertiseType_id,
				RegistryReceptExpertiseType_SysNick := :RegistryReceptExpertiseType_SysNick,
				RegistryReceptExpertiseType_Name := :RegistryReceptExpertiseType_Name,
				RegistryReceptExpertiseType_IsFLK := :RegistryReceptExpertiseType_IsFLK,
				RegistryReceptExpertiseType_IsActive := :RegistryReceptExpertiseType_IsActive,
				RegistryReceptExpertiseType_ErrorList := :RegistryReceptExpertiseType_ErrorList,
				pmUser_id := :pmUser_id);


		";

		$params = array(
			'RegistryReceptExpertiseType_id' => $data['RegistryReceptExpertiseType_id'],
			'RegistryReceptExpertiseType_SysNick' => $data['RegistryReceptExpertiseType_SysNick'],
			'RegistryReceptExpertiseType_Name' => $data['RegistryReceptExpertiseType_Name'],
			'RegistryReceptExpertiseType_IsFLK' => $data['RegistryReceptExpertiseType_IsFLK'],
			'RegistryReceptExpertiseType_IsActive' => $data['RegistryReceptExpertiseType_IsActive'],
			'RegistryReceptExpertiseType_ErrorList' => $data['RegistryReceptExpertiseType_ErrorList'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Возвращает список критериев экспертизы
	 */
	function loadRegistryReceptExpertiseTypeGrid($data)
	{
		$params = array();

		$query = "
			select
				RRET.RegistryReceptExpertiseType_id as \"RegistryReceptExpertiseType_id\",
				RRET.RegistryReceptExpertiseType_SysNick as \"RegistryReceptExpertiseType_SysNick\",
				RRET.RegistryReceptExpertiseType_IsFLK as \"RegistryReceptExpertiseType_IsFLK\",
				(CASE WHEN YN.YesNo_Code is null THEN 0 ELSE 1 END) as \"AllowEdit\",
				COALESCE(YN.YesNo_Code,1) as \"RegistryReceptExpertiseType_IsActive\",

				RRET.RegistryReceptExpertiseType_Name as \"RegistryReceptExpertiseType_Name\",
				RRET.RegistryReceptExpertiseType_ErrorList as \"RegistryReceptExpertiseType_ErrorList\"
			from
				v_RegistryReceptExpertiseType RRET 

				left join v_YesNo YN  on YN.YesNo_id = RRET.RegistryReceptExpertiseType_IsActive

		";

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			$response['data'] = $result->result('array');
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 *  Возвращает список ошибок
	 */
	function loadRegistryReceptErrorTypeGrid($data)
	{
		$params = array();

		$query = "
			select
				RRErrT.RegistryReceptErrorType_id as \"RegistryReceptErrorType_id\",
				RRErrT.RegistryReceptErrorType_Type as \"RegistryReceptErrorType_Type\",
				RRErrT.RegistryReceptErrorType_Name as \"RegistryReceptErrorType_Name\",
				RRErrT.RegistryReceptErrorType_Descr as \"RegistryReceptErrorType_Descr\"
			from
				r64.RegistryReceptErrorType RRErrT 

		";

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			$response['data'] = $result->result('array');
			return $response;
		}
		else
		{
			return false;
		}
	}
        
        
	/**
	 *  Оборотная ведомость по аптеке
	 */
	function loadDrugTurnoverList($data) {
		$params = array();
		$from = '';
		$filter="1=1";
		$set = '';
		
		if($_SESSION ['orgtype'] != 'lpu') {
			$from = 'from r2.fn_ReportFarmStart (:BegDate, :EndDate, :Org_id, :Differences)';
		} else {
			$from = 'from r2.fn_ReportFarmMOStart (:BegDate, :EndDate, :Org_id, :LpuSection_id, :Differences)';
		}
		
		if (isset ($data['Storage_id'])) {
			$filter .= ' and Storage_id = :Storage_id ';
			$params['Storage_id'] = $data['Storage_id'];		   
		};
		if (isset ($data['WhsDocumentCostItemType_id'])) {
			$filter .= ' and WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id ';
			$params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		};
		
		if (isset ($data['Drug_Code'])) {
			$filter .= ' and Drug_Code = :Drug_Code ';
			$params['Drug_Code'] = $data['Drug_Code'];
		};
		
		if (isset ($data['Drug_Name'])) {
			$filter .= " and Drug_Name iLIKE ('%'||:Drug_Name||'%')";

			$params['Drug_Name'] = $data['Drug_Name'];
		}; 
		if (isset ($data['DrugMNN_Name'])) {
			$filter .= " and DrugMNN_Name iLIKE ('%'||:DrugMNN_Name||'%')";

			$params['DrugMNN_Name'] = $data['DrugMNN_Name'];
		}; 
		if (isset ($data['SubAccountType_id'])) {
			$filter .= ' and SubAccountType_id = :SubAccountType_id ';
			$params['SubAccountType_id'] = $data['SubAccountType_id'];		   
		}; 
		
		if (isset ($data['LpuSection_id'])) {
//			$set = 'set @LpuSection_id = :LpuSection_id;';
			$params['LpuSection_id'] = $data['LpuSection_id'];		   
		}; 
		
		$query = "
	    
	    SElect ROW_NUMBER() OVER(
                    ORDER BY Drug_Name, lpu_id, DrugShipment_Name, DocumentUcStr_Price) AS \"RowNumber\",
                             DrugOstatRegistry_id as \"DrugOstatRegistry_id\",
                             Lpu_id as \"Lpu_id\",
                             DrugShipment_id as \"DrugShipment_id\",
                             DrugShipment_Name as \"DrugShipment_Name\",
                             to_char(DocumentUcStr_GodnDate, 'DD.MM.YYYY') as \"DocumentUcStr_GodnDate\",
                             Drug_id as \"Drug_id\",
                             Drug_Code as \"Drug_Code\",
                             GoodsUnit_Name as \"GoodsUnit_Name\",
                             Drug_Name as \"Drug_Name\",
                             DrugMNN_Name as \"DrugMNN_Name\",
                             DocumentUcStr_Ser as \"DocumentUcStr_Ser\",
                             SubAccountType_id as \"SubAccountType_id\",
                             SubAccountType_Name as \"SubAccountType_Name\",
                             CAST(COALESCE(DocumentUcStr_Price, 0)::numeric(20,3) as varchar) \"DocumentUcStr_Price\",
                             CAST(COALESCE(BegOst_Kol, 0)::numeric(20,2) as varchar) as \"BegOst_Kol\",
                             CAST(COALESCE(BegOst_Sum, 0)::numeric(20,2) as varchar) as \"BegOst_Sum\",
                             CAST(COALESCE(Pr_Kol, 0)::numeric(20,2) as varchar) as \"Pr_Kol\",
                             CAST(COALESCE(Pr_Sum, 0)::numeric(20,2) as varchar) as \"Pr_Sum\",
                             CAST(COALESCE(Ras_Kol, 0)::numeric(20,2) as varchar) as \"Ras_Kol\",
                             CAST(COALESCE(Ras_Sum, 0)::numeric(20,2) as varchar) as \"Ras_Sum\",
                             CAST(COALESCE(EndOst_Kol, 0)::numeric(20,2) as varchar) as \"EndOst_Kol\",
                             CAST(COALESCE(EndOst_Sum, 0)::numeric(20,2) as varchar) as \"EndOst_Sum\",
                             CAST(COALESCE(DrugOstatRegistry_Kolvo, 0)::numeric(20,2) as varchar) as \"DrugOstatRegistry_Kolvo\",
                             CAST(COALESCE(DrugOstatRegistry_Sum, 0.00)::numeric(20,2) as varchar) as \"DrugOstatRegistry_Sum\",
                             case
                               when DrugOstatRegistry_Kolvo <> 0 and BegOst_Kol = 0 and Pr_Kol = 0 and Ras_Kol = 0 then 2
                               when DrugOstatRegistry_Kolvo = EndOst_Kol then 0
                               else 1
                             end as \"EndOst_Contr\",
                             WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
                             WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
                             Storage_id as \"Storage_id\",
                             Storage_Name as \"Storage_Name\",
                             type_rec  as \"type_rec\"
					{$from}
                    where type_rec = 0 or
                          ({$filter})
                    Order by type_rec,
                             Drug_Name,
                             lpu_id,
                             DrugShipment_Name,
                             DocumentUcStr_Price
                   
                    ";
                
		$params['Org_id'] = $data['Org_id'];
		$params['BegDate'] = $data['PeriodRange'][0];
		$params['EndDate'] = $data['PeriodRange'][1];
		$params['Differences'] = $data['Differences'];
		
		//echo getDebugSql($query, $params); exit;
		
		//$dbrep = $this->load->database('bdwork', true);
 
		$dbrep = $this->db;
		
		$result = $dbrep->query($query, $params);

		if (is_object($result))
		{
				$response['data'] = $result->result('array');
				return $response;
		}
		else
		{
				return false;
		}
           
                    

	}
        
        /**
	 *  Оборотная ведомость по аптеке
	 */
	function loadDrugTurnoverDetail($data) {
		$params = array();
		
		if($_SESSION ['orgtype'] != 'lpu') {
			$from = 'from r2.fn_ReportFarmDetail (:BegDate, :EndDate, :Org_id, :WhsDocumentCostItemType_id, :DrugShipment_id, :Drug_Code, :Lpu_id, :SubAccountType_id)';
		} else {
			$from = 'from tmp.fn_ReportFarmMoDetail (:BegDate, :EndDate, :Org_id, :WhsDocumentCostItemType_id, :DrugShipment_id, :Drug_Code, :Lpu_id, :SubAccountType_id, :DrugOstatRegistry_id)';
		}
                
                $query = "
                     SElect ROW_NUMBER() OVER(
                                ORDER BY DocumentUc_didDate, DocumentUcStr_id, rectype) AS \"ID\",
                             DocumentUcStr_id as \"DocumentUcStr_id\",
                             Drug_id as \"Drug_id\",
                             DrugDocumentType_id as \"DrugDocumentType_id\",
                             DrugDocumentType_Name as \"DrugDocumentType_Name\",
                             DocumentUcStr_GodnDate as \"DocumentUcStr_GodnDate\",
                             GodnDay as \"GodnDay\",
                             DocumentUcStr_Price as \"DocumentUcStr_Price\",
                             DocumentUc_Num as \"DocumentUc_Num\",
                             DocumentUc_didDate  as \"dd\",
                             to_char(DocumentUc_didDate, 'DD.MM.YYYY') as \"DocumentUc_didDate\",
                             case
                               when BegOst = 0 then '0'
                               else ((BegOst)::numeric(20,2))::text
                             end as \"BegOst\",
                             case
                               when Pr_Kol = 0 then '0'
                               else ((Pr_Kol)::numeric(20,2))::text
                             end as \"Pr_Kol\",
                             case
                               when Ras_Kol = 0 then '0'
                               else ((Ras_Kol)::numeric(20,2))::text
                             end as \"Ras_Kol\",
                             case
                               when endOst = 0 then '0'
                               else (endOst::numeric(20,2))::text
                             end as \"endOst\",
                             WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
                             DrugShipment_id as \"DrugShipment_id\",
                             DrugShipment_name as \"DrugShipment_name\",
                             lpu_id as \"lpu_id\",
                             recdeleted as \"recdeleted\",
                             recdeleted_name as \"recdeleted_name\",
                             recType  as \"recType\"
                    {$from}
                    order by recType,
                             dd
                    ";
               
		$params['BegDate'] = $data['PeriodRange'][0];
		$params['EndDate'] = $data['PeriodRange'][1];
		
		
		$params['Org_id'] = $data['Org_id'];
		$params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		$params['DrugShipment_id'] = $data['DrugShipment_id'];
		$params['Drug_Code'] = $data['Drug_Code'];
		if (isset ($data['Lpu_id']))
			$params['Lpu_id'] = $data['Lpu_id'];
		else
		   $params['Lpu_id'] = '0';

        if (isset ($data['DrugOstatRegistry_id']))
            $params['DrugOstatRegistry_id'] = $data['DrugOstatRegistry_id'];
        else
            $params['DrugOstatRegistry_id'] = '0';
		
		if (isset ($data['SubAccountType_id']))
			$params['SubAccountType_id'] = $data['SubAccountType_id'];
		else
		   $params['SubAccountType_id'] = '1';
		
		//echo getDebugSql($query, $params); die();

                //$dbrep = $this->load->database('bdwork', true);
 
                $dbrep = $this->db;
                
		$result = $dbrep->query($query, $params);

		if (is_object($result))
		{
				$response['data'] = $result->result('array');
				return $response;
		}
		else
		{
				return false;
		}
                
                
                
        }

        /**
	 * Корректировка очтатков
	 * 
        */
	function UpdateDrugOstatRegistry_balances($data)
	{
            $queryParams = array(
                'pmUser_id' => $_SESSION['pmuser_id'],
                'DrugOstat_Kolvo' => $data['DrugOstat_Kolvo'],
                'DrugOstatRegistry_id' => $data['DrugOstatRegistry_id'],
            );
            
		$query = "
                select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
                from r2.p_DrugOstatRegistry_balances(
                    DrugOstatRegistry_id := :DrugOstatRegistry_id,
                    KOl := (CASE WHEN :DrugOstat_Kolvo < 0 THEN 0 ELSE :DrugOstat_Kolvo END),
                    pmUser_id := pmUser_id);
            ";
		    
		    
            //echo getDebugSql($query, $queryParams); die();
	    
	    //$dbrep = $this->load->database('bdwork', true);
 
          $dbrep = $this->db;
	    
            $result = $dbrep->query($query, $queryParams);
            //return $result->result('array');
            //return ($result === TRUE) ? array('success'=>true, 'Error_Msg'=>null) : $result;
	    return array('success'=>true);
	    // return array(array());
        }
	
	/**
	 *  Закрытие отчетного периода по аптеке (список)
	 */
	function loadDrugPeriodCloseList($data) {
	    $params = array();
	    $filter = " where (1=1) ";
	    
		if (isset($data['DrugPeriodCloseView_Apteka'])) {
			//$queryParams['DrugPeriodCloseView_Apteka'] = $data['DrugPeriodCloseView_Apteka'];
			$filter .= " and o.Org_Name iLIKE '%" .$data['DrugPeriodCloseView_Apteka'] ."%' ";	

	    }
	    if (isset($data['DrugPeriodCloseType_id'])) {
			//$queryParams['DrugPeriodClose_id'] = $data['DrugPeriodClose_id'];
			$filter .= " and DrugPeriodClose_Sign = ".$data['DrugPeriodCloseType_id'];	
	    }
	    
	   
	    $query = " 
		    SELECT DrugPeriodClose_id as \"DrugPeriodClose_id\",
                           cl.Org_id as \"Org_id\",
                           o.Org_Name as \"Org_Name\",
                           to_char(cl.DrugPeriodOpen_DT, 'DD.MM.YYYY') as \"DrugPeriodOpen_DT\",
                           to_char(cl.DrugPeriodClose_DT, 'DD.MM.YYYY') as \"DrugPeriodClose_DT\"
            FROM DrugPeriodClose cl
                   join v_Org o on o.Org_id = cl.Org_id 
            {$filter}
		    ";
			     
	    //echo getDebugSql($query, $queryParams); die();
		    
	    //$dbrep = $this->db;
		
	    $result = $this->db->query($query, $params);

	    if (is_object($result))
	    {
			    $response['data'] = $result->result('array');
			    return $response;
	    }
	    else
	    {
			    return false;
	    }
	    
	}
	
	/**
	 *  Сохранение отчетного периода по аптекам в БД
	 */
	function saveDrugPeriodClose($data) {
	    
	    $queryParams = array();
	    $declare = '';
	    
	    $queryParams['pmUser'] = $_SESSION['pmuser_id'];
	    if (isset($data['DrugPeriodClose_id'])) {
			$queryParams['DrugPeriodClose_id'] = $data['DrugPeriodClose_id'];
	    }
	    
	    if (isset($data['Org_id'])) {
			$queryParams['Org_id'] = $data['Org_id'];
	    }
	    
	    if (isset($data['DrugPeriodOpen_DT'])) {
			$queryParams['DrugPeriodOpen_DT'] = $data['DrugPeriodOpen_DT'];
	    }
	    
	    if (isset($data['DrugPeriodClose_DT'])) {
			$queryParams['DrugPeriodClose_DT'] = $data['DrugPeriodClose_DT'];
	    }
	    /*
	    if (isset($data['DrugPeriodClose_Sign'])) {
			$queryParams['DrugPeriodClose_Sign'] = $data['DrugPeriodClose_Sign'];
			$declare .= "
			   Set @DrugPeriodClose_Sign = :DrugPeriodClose_Sign";
	    }
	     */
	     
	    //echo ($declare);
	    $query = " 
            select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
		    from dbo.p_DrugPeriodClose_Save(
                DrugPeriodClose_id := :DrugPeriodClose_id,
                Org_id := :Org_id,
                DrugPeriodOpen_DT := :DrugPeriodOpen_DT,
                DrugPeriodClose_DT := :DrugPeriodClose_DT,
                pmUser := :pmUser);
		    ";

	    //echo getDebugSql($query, $queryParams); die();
	    
	    $result = $this->db->query($query, $queryParams);
	    
	    if ( is_object($result) ) {
			return array('success'=>true);
		}
		else {
			return false;
		};
	}
	
	/**
	 * Получение даты закрытия отчетного периода
	 */
	function geDrugPeriodCloseDT($data) {
	    //var_dump($_SESSION ['org_id']);
	    $params = array();

	    $query = "  
            Select max(DrugPeriodClose_DT) as \"DrugPeriodClose_DT\",
                   max(WhsDocumentUcInvent_DT) as \"WhsDocumentUcInvent_DT\",
                   max(Org_id) as \"Org_id\"
            from (
                   SElect
                          --  Дата закрытия периода
                          to_char(COALESCE(case
                                             when DrugPeriodClose_DT > dbo.tzGetDate() then DrugPeriodOpen_DT
                                             else DrugPeriodClose_DT
                                           end, '01.01.1900'), 'YYYY/MM/DD') DrugPeriodClose_DT,
                          --  Дата инвентаризации
                          (
                            select to_char(COALESCE(min(wdui.WhsDocumentUc_Date) - interval '1 day', '01.01.3000'), 'YYYY/MM/DD') WhsDocumentUc_Date
                            from v_WhsDocumentUcInvent wdui
                                 left join dbo.v_WhsDocumentStatusType i_wdst on i_wdst.WhsDocumentStatusType_id = wdui.WhsDocumentStatusType_id
                                 left join v_WhsDocumentUc i_ord on i_ord.WhsDocumentUc_id = wdui.WhsDocumentUc_pid
                                 left join dbo.v_WhsDocumentStatusType wdst on wdst.WhsDocumentStatusType_id = i_ord.WhsDocumentStatusType_id
                                 LEFT JOIN LATERAL
                                 (
                                   select count(WhsDocumentUcInvent_id) as cnt
                                   from v_WhsDocumentUcInventDrug wduid
                                   where wduid.WhsDocumentUcInvent_id = wdui.WhsDocumentUcInvent_id
                                 ) as drug_list ON true
                                 left join dbo.DocumentUc du on du.DocumentUc_id = 
                                 :Org_id
                            where wdst.WhsDocumentStatusType_Code = 2::text and
                                  i_wdst.WhsDocumentStatusType_Code != 2::text and
                                  wdui.Org_id = 
                                  :Org_id and
                                  wdui.WhsDocumentUc_Date <= COALESCE(du.DocumentUc_setDate, wdui.WhsDocumentUc_Date + interval '1 day') and
                                  drug_list.cnt = 0
                          ) WhsDocumentUcInvent_DT,
                          :Org_id as Org_id
                   from DrugPeriodClose
                   where Org_id = :Org_id
                   union all
                   Select '1900/01/01' as DrugPeriodClose_DT,
                          '1900/01/01' as WhsDocumentUc_Date,
                          :Org_id Org_id
            ) t
		 ";
	    
	    //$params['Org_id'] = $_SESSION ['org_id'];
	    $params['Org_id'] = isset($data['Org_id']) ? $data['Org_id'] : $data['session']['org_id'];
	    //echo getDebugSql($query, $params); die();
	    
	    $result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

    /**
     *  Формирование списка складов
     */
    function loadStorageList($data) {
        $params = array();
        $filter = "(1=1)";

        if(!empty($data['Storage_id']) && empty($data['query'])) {
            $filter .= " and Storage.Storage_id = :Storage_id";
            $params['Storage_id'] = $data['Storage_id'];
        } else {
            if(!empty($data['StorageType_id'])) {
                $filter .= " and Storage.StorageType_id = :StorageType_id";
                $params['StorageType_id'] = $data['StorageType_id'];
            }
            if(!empty($data['StorageTypeCode_List'])) {
                $filter .= " and StorageType.StorageType_Code in ({$data['StorageTypeCode_List']})";
            }
            if (
                !empty($data['Org_id']) || !empty($data['Lpu_oid']) || !empty($data['LpuBuilding_id'])
                || !empty($data['LpuUnit_id']) || !empty($data['LpuSection_id']) || !empty($data['MedService_id'])
            ) {
                $struct_filter_ssl = "1=1";
                $struct_filter_ms = "1=1";

                if (!empty($data['Org_id'])) {
                    $struct_filter_ssl .= " and COALESCE(SSL.Org_id, L.Org_id) = :Org_id";

                    $struct_filter_ms .= " and COALESCE(MS.Org_id, L.Org_id) = :Org_id";

                    $params['Org_id'] = $data['Org_id'];
                }
                if (!empty($data['Lpu_oid'])) {
                    $struct_filter_ssl .= " and SSL.Lpu_id = :Lpu_oid";
                    $struct_filter_ms .= " and MS.Lpu_id = :Lpu_oid";
                    $params['Lpu_oid'] = $data['Lpu_oid'];
                }
                if (!empty($data['LpuBuilding_id'])) {
                    $struct_filter_ssl .= " and SSL.LpuBuilding_id = :LpuBuilding_id";
                    $struct_filter_ms .= " and MS.LpuBuilding_id = :LpuBuilding_id";
                    $params['LpuBuilding_id'] = $data['LpuBuilding_id'];
                }
                if (!empty($data['LpuUnit_id'])) {
                    $struct_filter_ssl .= " and SSL.LpuUnit_id = :LpuUnit_id";
                    $struct_filter_ms .= " and MS.LpuUnit_id = :LpuUnit_id";
                    $params['LpuUnit_id'] = $data['LpuUnit_id'];
                }
                if (!empty($data['LpuSection_id'])) {
                    $struct_filter_ssl .= " and SSL.LpuSection_id = :LpuSection_id";
                    $struct_filter_ms .= " and MS.LpuSection_id = :LpuSection_id";
                    $params['LpuSection_id'] = $data['LpuSection_id'];
                }
                /*if (!empty($data['MedService_id'])) {
                    $struct_filter .= " and SSL.MedService_id = :MedService_id";
                    $params['MedService_id'] = $data['MedService_id'];
                }*/
                $filter .= " and Storage.Storage_id in (
                    select
                        Storage_id
                    from
                        v_StorageStructLevel SSL 

                        left join v_Lpu L  on L.Lpu_id = SSL.Lpu_id

                        left join v_MedService MS  on MS.MedService_id = SSL.MedService_id

                        left join v_Lpu MSL  on MSL.Lpu_id = MS.Lpu_id

                    where
                        ({$struct_filter_ssl}) or
                        ({$struct_filter_ms})
                )";
            }
            if (!empty($data['date'])) {
                $filter .= " and Storage.Storage_begDate <= :date";
                $filter .= " and (Storage.Storage_endDate > :date or Storage.Storage_endDate is null)";
                $params['date'] = $data['date'];
            }
            if (!empty($data['query'])) {
                $filter .= " and Storage.Storage_Name iLIKE '%'||:Storage_Name||'%'";

                $params['Storage_Name'] = $data['query'];
            }
        }

        $filter .= " and (Storage.Storage_endDate > GETDATE() or Storage.Storage_endDate is null)";
        $order_by = "Storage.StorageType_id, Storage_Name";

        if (!empty($data['StorageForAptMuFirst']) && $data['StorageForAptMuFirst']) {
            $order_by = "
				case when StrucLevel.Name = 'Lpu' then 1 else 0 end desc,
				Storage.StorageType_id,
				Storage_Name
			";
        }

        $query = "
			select
				Storage.Storage_id as \"Storage_id\",
				Storage.StorageType_id as \"StorageType_id\",
				Storage.Storage_Code as \"Storage_Code\",
				rtrim(Storage.Storage_Name) as \"Storage_Name\",
				to_char(Storage.Storage_begDate, 'DD.MM.YYYY') as \"Storage_begDate\",

				to_char(Storage.Storage_endDate, 'DD.MM.YYYY') as \"Storage_endDate\",

				StSL.LpuSection_id as \"LpuSection_id\",
				StrucLevel.Name as \"StorageStructLevel\",
				StSL.MedService_id as \"MedService_id\",
				COALESCE(StSL.Org_id,t1.Org_id) as \"Org_id\"

			from
				v_Storage Storage 

				left join v_StorageStructLevel StSL  on StSL.Storage_id = Storage.Storage_id

				left join v_Lpu_all t1  on t1.Lpu_id = StSL.Lpu_id

				LEFT JOIN LATERAL (

					select case
						when StSL.MedService_id is not null then 'MedService_id'
						when StSL.LpuSection_id is not null then 'LpuSection'
						when StSL.LpuUnit_id is not null then 'LpuUnit'
						when StSL.LpuBuilding_id is not null then 'LpuBuilding'
						when StSL.Lpu_id is not null then 'Lpu'
						when StSL.Org_id is not null then 'Org'
					end as Name
                    limit 1
				) StrucLevel ON true
			where
				{$filter}
			order by {$order_by}
		";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }
}