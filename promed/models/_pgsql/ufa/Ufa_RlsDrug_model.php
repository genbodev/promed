<?php

defined('BASEPATH') or die('No direct script access allowed');
require_once(APPPATH . 'models/_pgsql/RlsDrug_model.php');

class Ufa_RlsDrug_model extends RlsDrug_model
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
	 * Загрузка комбобокса sw.Promed.SwDrugComplexMnnCombo
	 */
	function loadDrugComplexMnnList($data, $withPaging = false)
	{
		$query = '';
		$queryParams = array();
		$where = "1=1";
		$mainWhere = "1=1";
		$whereOst = "";
		$join = ' left join rls.DrugComplexMnnDose on DrugComplexMnnDose.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id';
		$relevant = '';
		$drug_fas = '';

		$sessionParams = getSessionParams();
		$region = $sessionParams['session']['region']['nick'];


		if ($data['needFas']) {
			$join .= ' left join rls.v_DrugComplexMnn DCM2 on DCM2.DrugComplexMnn_id = DCM.DrugComplexMnn_pid
			left join rls.v_DrugComplexMnnFas DCMF on DCMF.DrugComplexMnnFas_id = COALESCE(DCM.DrugComplexMnnFas_id,DCM2.DrugComplexMnnFas_id) ';
			$drug_fas = ",(
					COALESCE(DCMF.DrugComplexMnnFas_Kol,1) * 
					COALESCE(DCMF.DrugComplexMnnFas_KolPrim,1) * 
					COALESCE(DCMF.DrugComplexMnnFas_KolSec,1) * 
					COALESCE(DCMF.DrugComplexMnnFas_Tert,1)
				) as \"Drug_Fas\" ";
		}
		if (!isset($data['LpuSection_id']))
			$data['LpuSection_id'] = $sessionParams ['session']['CurLpuSection_id'];

		$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		if ($data['Storage_id'] > 0) {
			$queryParams['Storage_id'] = $data['Storage_id'];
			$whereOst .= ' and dor.Storage_id = :Storage_id';
		}
		if ($data['DrugComplexMnn_id'] > 0) {
			$queryParams['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
			$where .= ' and dcm.DrugComplexMnn_id = :DrugComplexMnn_id';
			$mainWhere .= ' and dcm.DrugComplexMnn_id = :DrugComplexMnn_id';
		} else {
			if (strlen($data['query']) > 0 || !empty($data['Tradenames_id'])) {
				if (!empty($data['Tradenames_id'])) {
					$queryParams['Tradenames_id'] = $data['Tradenames_id'];
					$where .= " and dcm.DrugComplexMnn_id in (
						select d.DrugComplexMnn_id
						from rls.v_Drug d 
						where d.DrugTorg_id = :Tradenames_id
					)";
					$mainWhere .= " and dcm.DrugComplexMnn_id in (
						select d.DrugComplexMnn_id
						from rls.v_Drug d
						where d.DrugTorg_id = :Tradenames_id
					)";
				}

				// поднимаем на верх те, которые начинаются с query и где одно действующее вещество
				$queryParams['query_relevant'] = preg_replace('/ /', '%', $data['query']) . '%';
				$relevant = "case
					when dcm.DrugComplexMnn_RusName iLIKE :query_relevant and STRPOS ('+',dcm.drugcomplexmnn_rusname)>0 then 2
					when dcm.DrugComplexMnn_RusName iLIKE :query_relevant and STRPOS ('+',dcm.drugcomplexmnn_rusname)=0 then 1
					else 3
				end,";
				$queryParams['query'] = '%' . preg_replace('/ /', '%', $data['query']) . '%';
				$where .= ' and dcm.DrugComplexMnn_RusName iLIKE :query';
				$mainWhere .= ' and dcm.DrugComplexMnn_RusName iLIKE :query';

				//выбор с остатков
				$dc_module = $this->options['drugcontrol']['drugcontrol_module'];

				if (!$data['isFromDocumentUcOst']) {

					//если выбор ведется не из остатков, пытаемся фильтровать список по формуляру
					if ($data['LpuSection_id'] > 0) {
						//поиск подходящего формуляра
						$query = "

							select 
								dl.DrugList_id as \"DrugList_id\"
							from
								v_DrugList dl
								left join v_DrugListType dlt on dlt.DrugListType_id = dl.DrugListType_id
								left join v_DrugListUsed dlu on dlu.DrugList_id = dl.DrugList_id
								left join v_DrugListObj dlo on dlo.DrugListObj_id = dlu.DrugListObj_id
								left join lateral (
									select
										(case
											when dlo.LpuSection_id = :LpuSection_id then 1
											when dlo.LpuBuilding_id = :LpuSection_id then 2
											else 3
										end) as val
								) ord on true
							where
								dlt.DrugListType_Code::integer = 1 and -- Формуляр
								(
									dlo.LpuSection_id = :LpuSection_id or
									dlo.LpuBuilding_id = (select
                                                            ls.LpuBuilding_id
                                                        from
                                                            v_LpuSection ls
                                                        where
                                                            ls.LpuSection_id = :LpuSection_id) or
									dlo.Lpu_id = (select
									                ls.Lpu_id
                                                from
                                                    v_LpuSection ls
                                                where
                                                    ls.LpuSection_id = :LpuSection_id)
								) and
								exists ( -- формуляр не должен быть пустым
									select
										dls.DrugListStr_id
									from
										v_DrugListStr dls
									where
										dls.DrugList_id = dl.DrugList_id and
										dls.DrugComplexMnn_id is not null
									limit 1
								)
							order by
								ord.val
							limit 1
						";
						$dl_data = $this->getFirstRowFromQuery($query, array(
							'LpuSection_id' => $data['LpuSection_id']
						));

						if (!empty($dl_data['DrugList_id'])) { //не пустой формуляр найден
							$where .= " and exists(
								select 
									dls.DrugListStr_id
								from
									v_DrugListStr dls
								where
									dls.DrugList_id = :DrugList_id and
									dls.DrugComplexMnn_id = dcm.DrugComplexMnn_id
								limit 1
							)";
							$queryParams['DrugList_id'] = $dl_data['DrugList_id'];
						}
					}
				}
				if (!empty($data['hasDrugComplexMnnCode']) && $data['hasDrugComplexMnnCode']) {
					$where .= " and exists(
						select * from rls.v_DrugComplexMnnCode DCMC
						where DCMC.DrugComplexMnn_id = dcm.DrugComplexMnn_id
					)";
				}
			} else {
				return false;
			}
		}

		$query = "
				-- variables

				with tmp as (
				select 
					EPTD.Drug_id,  EPTD.DrugComplexMnn_id,
					cast(round(EPTD.EvnPrescrTreatDrug_Kolvo * COALESCE(Treat.EvnPrescrTreat_PrescrCount, 1)  / COALESCE(gpc.GoodsPackCount_Count, 1), 6) as numeric(18,6)) EvnCourseTreatDrug_Count
				from dbo.v_EvnPrescr EP
					inner join v_EvnSection ES  on ES.EvnSection_id = EP.EvnPrescr_pid or  ES.EvnSection_pid = EP.EvnPrescr_pid
					inner join Evn e on e.Evn_id = ES.EvnSection_pid -- КВС для фильтрации по датам	
					left join v_EvnPrescrTreat Treat on Treat.EvnPrescrTreat_id = EP.EvnPrescr_id		
					inner join v_EvnPrescrTreatDrug EPTD on  EPTD.EvnPrescrTreat_id = EP.EvnPrescr_id
					inner join lateral( Select DrugComplexMnn_id, COALESCE(GoodsUnit_id, GoodsUnit_sid) GoodsUnit_id  
						from  v_EvnCourseTreatDrug ec_drug where Treat.EvnCourse_id = ec_drug.EvnCourseTreat_id
							and  COALESCE(EPTD.Drug_id, 0) = case when ec_drug.Drug_id is not null then ec_drug.Drug_id else COALESCE(EPTD.Drug_id, 0) end 
							and  COALESCE(EPTD.DrugComplexMnn_id, 0) = case when ec_drug.DrugComplexMnn_id is not null then ec_drug.DrugComplexMnn_id else COALESCE(EPTD.DrugComplexMnn_id, 0) end 
							limit 1
							)
						ec_drug on true
					left join v_GoodsUnit ec_gu  on ec_drug.GoodsUnit_id = ec_gu.GoodsUnit_id		
					left join lateral( Select  * from  r2.fn_GoodsPackCount(ec_drug.DrugComplexMnn_id) gpc
						where COALESCE(ec_drug.GoodsUnit_id, 0) = coalesce(gpc.GoodsUnit_ID, ec_gu.GoodsUnit_id, 0)
					) gpc on true
				where EP.PrescriptionType_id = '5'
					and COALESCE(cast(EP.EvnPrescr_setDT as date), GetDate() - 7 * INTERVAL '1 day') >= (GetDate() - 7 * INTERVAL '1 day')
					and ES.LpuSection_id = :LpuSection_id
					and EP.Lpu_id = (select
                                        ls.Lpu_id
                                    from
                                        v_LpuSection ls
                                    where
                                        ls.LpuSection_id = :LpuSection_id)
					and COALESCE(EP.EvnPrescr_IsExec, 1) = 1
				)
                ,nazn_tmp as(
					select DrugComplexMnn_id, sum(EvnCourseTreatDrug_Count) EvnCourseTreatDrug_Count 
						--yl:into #nazn 
						from tmp
						group by DrugComplexMnn_id
                ),
				--SET NOCOUNT OFF;
				
				Osttmp as (
				select   drug.Drug_id, drug.DrugComplexMnn_id, dor.DrugOstatRegistry_Kolvo--, dor.GoodsUnit_id
					from rls.v_Drug Drug
					inner join v_DrugOstatRegistry DOR on DOR.Drug_id = Drug.Drug_id
						and dor.SubAccountType_id = 1
					inner join v_StorageStructLevel SSL on SSL.Storage_id = DOR.Storage_id
					inner join v_Storage SG on SG.Storage_id = DOR.Storage_id
					left join rls.v_PrepSeries PS on PS.PrepSeries_id = DOR.PrepSeries_id
					where 
						SSL.LpuSection_id = :LpuSection_id
						and DrugOstatRegistry_Kolvo > 0
						and (SG.Storage_endDate > GETDATE() or SG.Storage_endDate is null)
						and COALESCE(PS.PrepSeries_IsDefect, 1) = 1
						and	(PS.PrepSeries_GodnDate is null or PS.PrepSeries_GodnDate >= getDate())
						{$whereOst}
				),
				ost as (
					SElect DrugComplexMnn_id, sum(DrugOstatRegistry_Kolvo) DrugOstatRegistry_Kolvo
						from ostTmp
						group by DrugComplexMnn_id)
				-- end addit with		
				select 
					-- select
					ost.DrugComplexMnn_id
					,dcm.DrugComplexMnn_RusName as DrugComplexMnn_Name
					,DrugComplexMnnDose.DrugComplexMnnDose_Name as DrugComplexMnn_Dose
					,DrugComplexMnnDose.DrugComplexMnnDose_Mass
					,dcm.CLSDRUGFORMS_ID as RlsClsdrugforms_id
					,dcmn.Actmatters_id as RlsActmatters_id
					,coalesce(df.CLSDRUGFORMS_NameLatinSocr,df.NAME,'') as RlsClsdrugforms_Name
					,df.NAME as RlsClsdrugforms_RusName
					,cast(cast(
						case when COALESCE(ost.DrugOstatRegistry_Kolvo, 0) < COALESCE(nazn.EvnCourseTreatDrug_Count, 0) then  0
								else COALESCE (ost.DrugOstatRegistry_Kolvo, 0) - COALESCE(nazn.EvnCourseTreatDrug_Count, 0) end
					  as numeric(19,2)) as varchar) as Ostat_Kolvo
					,cast(cast(ost.DrugOstatRegistry_Kolvo as numeric(19,2)) as varchar) DrugOstatRegistry_Kolvo 
					,cast(cast(nazn.EvnCourseTreatDrug_Count as numeric(19,2)) as varchar) EvnCourseTreatDrug_Count  
					{$drug_fas}
					-- end select 
					
				from 
				-- from
					Ost 
					left join nazn_tmp nazn on nazn.DrugComplexMnn_id = ost.DrugComplexMnn_id
					join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = ost.DrugComplexMnn_id
					left join rls.CLSDRUGFORMS df on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
					left join rls.DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
					{$join} 
				-- end from
				Where
				-- where
					{$mainWhere}
				-- end where
				order by
				-- order by
				{$relevant}
				 dcm.DrugComplexMnn_RusName
				-- end order by			
								";


		if (!$data['isFromDocumentUcOst']) {
			$result = $this->db->query($query, $queryParams);
			if (!is_object($result))
				return false;

			$ost = $result->result('array');

			$query = "
				select 
					-- select
					dcm.DrugComplexMnn_id as \"DrugComplexMnn_id\" 
					,dcm.DrugComplexMnn_RusName as \"DrugComplexMnn_Name\"
					,DrugComplexMnnDose.DrugComplexMnnDose_Name as \"DrugComplexMnn_Dose\"
					,DrugComplexMnnDose.DrugComplexMnnDose_Mass as \"DrugComplexMnnDose_Mass\"
					,dcm.CLSDRUGFORMS_ID as \"RlsClsdrugforms_id\"
					,dcmn.Actmatters_id as \"RlsActmatters_id\"
					,coalesce(df.CLSDRUGFORMS_NameLatinSocr,df.NAME,'') as \"RlsClsdrugforms_Name\"
					,df.NAME as \"RlsClsdrugforms_RusName\"
					{$drug_fas}
					, null \"Ostat_Kolvo\", null \"DrugOstatRegistry_Kolvo\", null \"EvnCourseTreatDrug_Count	\"
					-- end select
				from
					-- from
					rls.v_DrugComplexMnn dcm
					left join rls.CLSDRUGFORMS df on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
					left join rls.DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
					{$join}
					-- end from
				where
					-- where
					{$where}
					-- end where
				order by
					-- order by
					{$relevant}
					dcm.DrugComplexMnn_RusName
					-- end order by
				" . /*!$withPaging ? "limit 100" : "" .*/ "
			";
		}
		if ($withPaging) {

			$response = array();
			$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $queryParams);
			if (is_object($result)) {
				$response['data'] = $result->result('array');
				$response['totalCount'] = count($response['data']);
			} else {
				return false;
			}
			if (!(empty($data['start']) && $response['totalCount'] < $data['limit'])) {
				$result = $this->db->query(getCountSQLPH($query), $queryParams);
				if (is_object($result)) {
					$cnt_arr = $result->result('array');
					$response['totalCount'] = $cnt_arr[0]['cnt'];
				} else {
					return false;
				}
			}
			if (!$data['isFromDocumentUcOst']) {
				foreach ($response['data'] as $key => $rec) {
					$i = -1;
					$i = array_search($rec['DrugComplexMnn_id'], array_column($ost, 'DrugComplexMnn_id'));
					if (!($i === false)) {
						$response['data'][$key]['Ostat_Kolvo'] = $ost[$i]['Ostat_Kolvo'];
						$response['data'][$key]['DrugOstatRegistry_Kolvo'] = $ost[$i]['DrugOstatRegistry_Kolvo'];
						$response['data'][$key]['EvnCourseTreatDrug_Count'] = $ost[$i]['EvnCourseTreatDrug_Count'];
					}
				}
			}
			return $response;
		} else {
			$result = $this->db->query($query." limit 100", $queryParams);
			if (is_object($result)) {
				$response = $result->result('array');
				if (!$data['isFromDocumentUcOst']) {
					foreach ($response as $key => $rec) {
						$i = -1;
						$i = array_search($rec['DrugComplexMnn_id'], array_column($ost, 'DrugComplexMnn_id'));
						if (!($i === false)) {
							$response[$key]['Ostat_Kolvo'] = $ost[$i]['Ostat_Kolvo'];
							$response[$key]['DrugOstatRegistry_Kolvo'] = $ost[$i]['DrugOstatRegistry_Kolvo'];
							$response[$key]['EvnCourseTreatDrug_Count'] = $ost[$i]['EvnCourseTreatDrug_Count'];
						}
					}
				}
				return $response;
			} else {
				return false;
			}
		}
	}

	/**
	 * Загрузка комбобокса sw.Promed.SwDrugSimpleCombo
	 */
	function loadDrugSimpleList($data, $withPaging = false)
	{

		$queryParams = array();
		$relevant = '';
		$join = 'left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = Drug.DrugComplexMnn_id
				left join rls.DrugComplexMnnDose on DrugComplexMnnDose.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id';

		$having = '';
		$whereOst = '';
		$sessionParams = getSessionParams();
		$region = $sessionParams['session']['region']['nick'];


		if (strlen($data['query']) > 0 || $data['Drug_id'] > 0) {
			if ($data['Drug_id'] > 0) {
				$queryParams['Drug_id'] = $data['Drug_id'];
				$where = 'Drug.Drug_id = :Drug_id';
			} else if (strlen($data['query']) > 0) {
				// поднимаем на верх те, которые начинаются с query и где одно действующее вещество
				$queryParams['query_relevant'] = preg_replace('/ /', '%', $data['query']) . '%';
				$relevant = "case
					when Drug.Drug_Name iLIKE :query_relevant and STRPOS ('+',dcm.drugcomplexmnn_rusname)>0 then 2
					when Drug.Drug_Name iLIKE :query_relevant and STRPOS ('+',dcm.drugcomplexmnn_rusname)=0 then 1
					else 3
				end,";
				$queryParams['query'] = '%' . preg_replace('/ /', '%', $data['query']) . '%';
				$where = 'Drug.Drug_Name iLIKE :query';
			}
			//выбор с остатков
			$dc_module = $this->options['drugcontrol']['drugcontrol_module'];

			//echo '<pre>' . print_r($sessionParams ['session']) . '</pre>'; exit;
			if (!isset($data['LpuSection_id']))
				$data['LpuSection_id'] = $sessionParams ['session']['CurLpuSection_id'];
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
			if ($data['Storage_id'] > 0) {
				$queryParams['Storage_id'] = $data['Storage_id'];
				$whereOst .= ' and dor.Storage_id = :Storage_id';
			}
			$query = "
						with nazn as (
							select 
							 EPTD.Drug_id,  EPTD.DrugComplexMnn_id,
												cast(round(EPTD.EvnPrescrTreatDrug_Kolvo * COALESCE(Treat.EvnPrescrTreat_PrescrCount, 1)  / COALESCE(gpc.GoodsPackCount_Count, 1), 6) as numeric(19,2)) EvnCourseTreatDrug_Count
											from dbo.v_EvnPrescr EP
												inner join v_EvnSection ES on ES.EvnSection_id = EP.EvnPrescr_pid or  ES.EvnSection_pid = EP.EvnPrescr_pid
												inner join Evn e on e.Evn_id = ES.EvnSection_pid -- КВС для фильтрации по датам	
												left join v_EvnPrescrTreat Treat on Treat.EvnPrescrTreat_id = EP.EvnPrescr_id		
												inner join v_EvnPrescrTreatDrug EPTD on  EPTD.EvnPrescrTreat_id = EP.EvnPrescr_id
												inner join lateral( Select DrugComplexMnn_id, COALESCE(GoodsUnit_id, GoodsUnit_sid limit 1) GoodsUnit_id  
													from  v_EvnCourseTreatDrug ec_drug where Treat.EvnCourse_id = ec_drug.EvnCourseTreat_id
														and  COALESCE(EPTD.Drug_id, 0) = case when ec_drug.Drug_id is not null then ec_drug.Drug_id else COALESCE(EPTD.Drug_id, 0) end 
														and  COALESCE(EPTD.DrugComplexMnn_id, 0) = case when ec_drug.DrugComplexMnn_id is not null then ec_drug.DrugComplexMnn_id else COALESCE(EPTD.DrugComplexMnn_id, 0) end 
														)
													ec_drug on true
												left join v_GoodsUnit ec_gu  on ec_drug.GoodsUnit_id = ec_gu.GoodsUnit_id		
												left join lateral( Select  * from  r2.fn_GoodsPackCount(ec_drug.DrugComplexMnn_id) gpc
													where COALESCE(ec_drug.GoodsUnit_id, 0) = coalesce(gpc.GoodsUnit_ID, ec_gu.GoodsUnit_id, 0)
												) gpc on true
											where EP.PrescriptionType_id = '5'
												and COALESCE(cast(EP.EvnPrescr_setDT as date), GetDate() - 7) >= GetDate() - 7
												and ES.LpuSection_id = :LpuSection_id
												and EP.Lpu_id = (select
                                                                ls.Lpu_id
                                                            from
                                                                v_LpuSection ls
                                                            where
                                                                ls.LpuSection_id = :LpuSection_id)
												and COALESCE(EP.EvnPrescr_IsExec, 1) = 1
												and EPTD.Drug_id is not null
									)
									select 
									Drug_id, sum(EvnCourseTreatDrug_Count) EvnCourseTreatDrug_Count 
										into #nazn 
										from nazn
										group by Drug_id;

						With
									Osttmp as (
									select   drug.Drug_id, drug.DrugComplexMnn_id, dor.DrugOstatRegistry_Kolvo / COALESCE(gpc.GoodsPackCount_Count, 1) DrugOstatRegistry_Kolvo
										from rls.v_Drug Drug
										inner join v_DrugOstatRegistry DOR on DOR.Drug_id = Drug.Drug_id
											and dor.SubAccountType_id = 1
										inner join v_StorageStructLevel SSL on SSL.Storage_id = DOR.Storage_id
										inner join v_Storage SG on SG.Storage_id = DOR.Storage_id
										left join rls.v_PrepSeries PS on PS.PrepSeries_id = DOR.PrepSeries_id
										left join lateral( Select  * from  r2.fn_GoodsPackCount(drug.DrugComplexMnn_id) gpc
													where COALESCE(dor.GoodsUnit_id, 0) = coalesce(gpc.GoodsUnit_ID, dor.GoodsUnit_id, 0)
												) gpc on true
										where 
											SSL.LpuSection_id = :LpuSection_id
											and DrugOstatRegistry_Kolvo > 0
											and (SG.Storage_endDate > GETDATE() or SG.Storage_endDate is null)
											and COALESCE(PS.PrepSeries_IsDefect, 1) = 1
											and	(PS.PrepSeries_GodnDate is null or PS.PrepSeries_GodnDate >= getDate())
											{$whereOst}
									),
									ost as (
										SElect Drug_id, sum(DrugOstatRegistry_Kolvo) DrugOstatRegistry_Kolvo
											from ostTmp

											group by Drug_id)
					-- end addit with
					Select 
					-- select
						Drug.Drug_Name as \"Drug_Name\",
						Drug.Drug_id as \"Drug_id\",
						Drug.Drug_Code as \"Drug_Code\",
						Drug.Drug_Dose as \"Drug_Dose\",
						Drug.DrugPrep_id as \"DrugPrep_id\",
						Drug.DrugTorg_id as \"Tradenames_id\",
						Drug.DrugComplexMnn_id as \"DrugComplexMnn_id\",
						DrugComplexMnnDose.DrugComplexMnnDose_Mass,
						coalesce(df.CLSDRUGFORMS_NameLatinSocr,df.NAME,Drug.DrugForm_Name,'') as \"DrugForm_Name\"
						,(cast(cast(
								case when COALESCE(ost.DrugOstatRegistry_Kolvo, 0) < COALESCE(nazn.EvnCourseTreatDrug_Count, 0) then  0
										else COALESCE (ost.DrugOstatRegistry_Kolvo, 0) - COALESCE(nazn.EvnCourseTreatDrug_Count, 0) end
							 ) as numeric(19,2) as varchar)) as \"Ostat_Kolvo\"
							,cast(cast(ost.DrugOstatRegistry_Kolvo as numeric(19,2)) as varchar) \"DrugOstatRegistry_Kolvo \"
							,cast(cast(nazn.EvnCourseTreatDrug_Count as numeric(19,2)) as varchar) \"EvnCourseTreatDrug_Count\"
					-- end select  
					from 
					-- from
					ost
						inner join rls.v_Drug Drug on drug.Drug_id = ost.Drug_id
						left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = Drug.DrugComplexMnn_id
						left join rls.DrugComplexMnnDose on DrugComplexMnnDose.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
						left join rls.CLSDRUGFORMS df on df.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
						left join #nazn nazn on nazn.Drug_id = ost.Drug_id
					-- end from
					Where
					-- where
						{$where}
						-- end where
					order by
						-- order by
						{$relevant}
						Drug.Drug_Name
						-- end order by		
					";
		} else {
			return false;
		}
		if (!($data['isFromDocumentUcOst'])) {

			$result = $this->db->query($query, $queryParams);
			if (!is_object($result))
				return false;

			$ost = $result->result('array');


			//если выбор ведется не из остатков, пытаемся фильтровать список по формуляру
			if ($data['LpuSection_id'] > 0) {
				//поиск подходящего формуляра
				$query = "						
						select
							dl.DrugList_id as \"DrugList_id\"
						from
							v_DrugList dl
							left join v_DrugListType dlt on dlt.DrugListType_id = dl.DrugListType_id
							left join v_DrugListUsed dlu on dlu.DrugList_id = dl.DrugList_id
							left join v_DrugListObj dlo on dlo.DrugListObj_id = dlu.DrugListObj_id
							left join lateral (
								select
									(case
										when dlo.LpuSection_id = :LpuSection_id then 1
										when dlo.LpuBuilding_id = :LpuSection_id then 2
										else 3
									end) as val
							) ord on true
						where
							dlt.DrugListType_Code = 1 and -- Формуляр
							(
								dlo.LpuSection_id = COALESCE(:LpuSection_id, dlo.LpuSection_id) or
								dlo.LpuBuilding_id = (select 
                                                        ls.LpuBuilding_id
                                                    from
                                                        v_LpuSection ls
                                                    where
                                                        ls.LpuSection_id = :LpuSection_id) or
								dlo.Lpu_id = (select 
								                ls.Lpu_id
                                            from
                                                v_LpuSection ls
                                            where
                                                ls.LpuSection_id = :LpuSection_id)
							) and
							exists ( -- формуляр не должен быть пустым
								select
									dls.DrugListStr_id
								from
									v_DrugListStr dls
								where
									dls.DrugList_id = dl.DrugList_id and
									dls.Drug_id is not null
								limit 1
							)
						order by
							ord.val
						limit 1
					";
				if (!isset($data['LpuSection_id']))
					$data['LpuSection_id'] = null;
				$dl_data = $this->getFirstRowFromQuery($query, array(
					'LpuSection_id' => $data['LpuSection_id']
				));

				if (!empty($dl_data['DrugList_id'])) { //не пустой формуляр найден
					$where .= " and exists(
							select
								dls.DrugListStr_id
							from
								v_DrugListStr dls
							where
								dls.DrugList_id = :DrugList_id and
								dls.Drug_id = Drug.Drug_id
							limit 1
						)";
					$queryParams['DrugList_id'] = $dl_data['DrugList_id'];
				}
			}
			$query = " 
					select
						-- select
						Drug.Drug_Name as \"Drug_Name\",
						Drug.Drug_id as \"Drug_id\",
						Drug.Drug_Code as \"Drug_Code\",
						Drug.Drug_Dose as \"Drug_Dose\",
						Drug.DrugPrep_id as \"DrugPrep_id\",
						Drug.DrugTorg_id as \"Tradenames_id\",
						Drug.DrugComplexMnn_id as \"DrugComplexMnn_id\",
						DrugComplexMnnDose.DrugComplexMnnDose_Mass as \"DrugComplexMnnDose_Mass\",
						coalesce(df.CLSDRUGFORMS_NameLatinSocr,df.NAME,Drug.DrugForm_Name,'') as \"DrugForm_Name\"
						-- end select
					from
						-- from
						rls.v_Drug Drug
						left join rls.v_DrugPrep DrugPrep on Drug.DrugPrepFas_id = DrugPrep.DrugPrepFas_id
						{$join}
						left join rls.CLSDRUGFORMS df on df.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
						-- end from
					where
						-- where
						{$where}
						-- end where
					order by
						-- order by
						{$relevant}
						Drug.Drug_Name
						-- end order by
					" . !$withPaging ? "limit 100" : "" . "
				";
		}
		if ($withPaging) {
			$response = array();
			if (!empty($data['getCountOnly']) && $data['getCountOnly'] == 1) {
				// подсчет только количества строк
				$get_count_query = getCountSQLPH($query);
				//echo '<pre>' . print_r(getDebugSQL($get_count_query, $queryParams), 1) . '</pre>'; exit;
				$get_count_result = $this->db->query($get_count_query, $queryParams);

				if (is_object($get_count_result)) {
					$response['totalCount'] = $get_count_result->result('array');
					$response['totalCount'] = $response['totalCount'][0]['cnt'];
				} else {
					return false;
				}
			} else {
				$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $queryParams);
				if (is_object($result)) {
					$response['data'] = $result->result('array');
					$response['totalCount'] = count($response['data']);
				} else {
					return false;
				}
				if (count($response['data']) >= $data['limit']) {
					$response['overLimit'] = true; // лимит весь вошел на страницу, а значит реальный каунт может отличаться от totalCount и пусть юезр запросит его сам, если он ему нужен
				}
			}
		} else {
			//echo(getDebugSQL($query, $queryParams)); exit;
			$result = $this->db->query($query, $queryParams);
			if (is_object($result)) {
				$response = $result->result('array');
				if (!$data['isFromDocumentUcOst']) {
					foreach ($response as $key => $rec) {
						$i = -1;
						$i = array_search($rec['Drug_id'], array_column($ost, 'Drug_id'));
						if (!($i === false)) {
							$response [$key]['Ostat_Kolvo'] = $ost[$i]['Ostat_Kolvo'];
							$response [$key]['DrugOstatRegistry_Kolvo'] = $ost[$i]['DrugOstatRegistry_Kolvo'];
							$response [$key]['EvnCourseTreatDrug_Count'] = $ost[$i]['EvnCourseTreatDrug_Count'];
						}
					}
				}
				return $response;
			} else {
				return false;
			}
		}
		if (!$data['isFromDocumentUcOst']) {
			foreach ($response['data'] as $key => $rec) {
				$i = -1;
				$i = array_search($rec['Drug_id'], array_column($ost, 'Drug_id'));
				if (!($i === false)) {
					$response['data'][$key]['Ostat_Kolvo'] = $ost[$i]['Ostat_Kolvo'];
					$response['data'][$key]['DrugOstatRegistry_Kolvo'] = $ost[$i]['DrugOstatRegistry_Kolvo'];
					$response['data'][$key]['EvnCourseTreatDrug_Count'] = $ost[$i]['EvnCourseTreatDrug_Count'];
				}
			}
		}
		return $response;
	}

}
