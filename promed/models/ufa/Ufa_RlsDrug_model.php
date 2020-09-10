<?php

defined('BASEPATH') or die('No direct script access allowed');
require_once(APPPATH . 'models/RlsDrug_model.php');

class Ufa_RlsDrug_model extends RlsDrug_model {

	/**
	 * construct
	 */
	function __construct() {
		//parent::__construct();
		parent::__construct();
	}

	/**
	 * Загрузка комбобокса sw.Promed.SwDrugComplexMnnCombo
	 */
	function loadDrugComplexMnnList($data, $withPaging = false) {
		$query = '';
		$queryParams = array();
		$where = "1=1";
		$mainWhere = "1=1";
		$whereOst = "";
		$join = ' left join rls.DrugComplexMnnDose with (nolock) on DrugComplexMnnDose.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id';
		$relevant = '';
		$drug_fas = '';
		
		$sessionParams = getSessionParams();
		$region = $sessionParams['session']['region']['nick'];


		if ($data['needFas']) {
			$join .= ' left join rls.v_DrugComplexMnn DCM2 (nolock) on DCM2.DrugComplexMnn_id = DCM.DrugComplexMnn_pid
			left join rls.v_DrugComplexMnnFas DCMF (nolock) on DCMF.DrugComplexMnnFas_id = ISNULL(DCM.DrugComplexMnnFas_id,DCM2.DrugComplexMnnFas_id) ';
			$drug_fas = ',(
					isnull(DCMF.DrugComplexMnnFas_Kol,1) * 
					isnull(DCMF.DrugComplexMnnFas_KolPrim,1) * 
					isnull(DCMF.DrugComplexMnnFas_KolSec,1) * 
					isnull(DCMF.DrugComplexMnnFas_Tert,1)
				) as Drug_Fas ';
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
						from rls.v_Drug d with(nolock)
						where d.DrugTorg_id = :Tradenames_id
					)";
					$mainWhere .= " and dcm.DrugComplexMnn_id in (
						select d.DrugComplexMnn_id
						from rls.v_Drug d with(nolock)
						where d.DrugTorg_id = :Tradenames_id
					)";
				}

				// поднимаем на верх те, которые начинаются с query и где одно действующее вещество
				$queryParams['query_relevant'] = preg_replace('/ /', '%', $data['query']) . '%';
				$relevant = "case
					when dcm.DrugComplexMnn_RusName LIKE :query_relevant and CHARINDEX ('+',dcm.drugcomplexmnn_rusname)>0 then 2
					when dcm.DrugComplexMnn_RusName LIKE :query_relevant and CHARINDEX ('+',dcm.drugcomplexmnn_rusname)=0 then 1
					else 3
				end,";
				$queryParams['query'] = '%' . preg_replace('/ /', '%', $data['query']) . '%';
				$where .= ' and dcm.DrugComplexMnn_RusName LIKE :query';
				$mainWhere .= ' and dcm.DrugComplexMnn_RusName LIKE :query';

				//выбор с остатков
				$dc_module = $this->options['drugcontrol']['drugcontrol_module'];
				
				if (!$data['isFromDocumentUcOst']) {

					//если выбор ведется не из остатков, пытаемся фильтровать список по формуляру
					if ($data['LpuSection_id'] > 0) {
						//поиск подходящего формуляра
						$query = "
							declare
								@LpuSection_id bigint = :LpuSection_id,
								@LpuBuilding_id bigint,
								@Lpu_id bigint;
							
							select
								@LpuBuilding_id = ls.LpuBuilding_id,
								@Lpu_id = ls.Lpu_id
							from
								v_LpuSection ls with (nolock)
							where
								ls.LpuSection_id = @LpuSection_id;
							
							select top 1
								dl.DrugList_id
							from
								v_DrugList dl with (nolock)
								left join v_DrugListType dlt with (nolock) on dlt.DrugListType_id = dl.DrugListType_id
								left join v_DrugListUsed dlu with (nolock) on dlu.DrugList_id = dl.DrugList_id
								left join v_DrugListObj dlo with (nolock) on dlo.DrugListObj_id = dlu.DrugListObj_id
								outer apply (
									select
										(case
											when dlo.LpuSection_id = @LpuSection_id then 1
											when dlo.LpuBuilding_id = @LpuBuilding_id then 2
											else 3
										end) as val
								) ord
							where
								dlt.DrugListType_Code = 1 and -- Формуляр
								(
									dlo.LpuSection_id = @LpuSection_id or
									dlo.LpuBuilding_id = @LpuBuilding_id or
									dlo.Lpu_id = @Lpu_id
								) and
								exists ( -- формуляр не должен быть пустым
									select top 1
										dls.DrugListStr_id
									from
										v_DrugListStr dls with (nolock)
									where
										dls.DrugList_id = dl.DrugList_id and
										dls.DrugComplexMnn_id is not null
								)
							order by
								ord.val
						";
						$dl_data = $this->getFirstRowFromQuery($query, array(
							'LpuSection_id' => $data['LpuSection_id']
						));

						if (!empty($dl_data['DrugList_id'])) { //не пустой формуляр найден
							$where .= " and exists(
								select top 1
									dls.DrugListStr_id
								from
									v_DrugListStr dls with (nolock)
								where
									dls.DrugList_id = :DrugList_id and
									dls.DrugComplexMnn_id = dcm.DrugComplexMnn_id
							)";
							$queryParams['DrugList_id'] = $dl_data['DrugList_id'];
						}
					}
				}
				if (!empty($data['hasDrugComplexMnnCode']) && $data['hasDrugComplexMnnCode']) {
					$where .= " and exists(
						select * from rls.v_DrugComplexMnnCode DCMC with(nolock)
						where DCMC.DrugComplexMnn_id = dcm.DrugComplexMnn_id
					)";
				}
			} else {
				return false;
			}
		}

		$query = "
				-- variables
				Declare
					@lpu_id bigint,
					@LpuBuilding_id bigint,
					@LpuSection_id bigint = :LpuSection_id,
					@begDate date = GetDate() - 7;
				-- end variables
				-- addit with
				SET NOCOUNT ON;	
				select
								@LpuBuilding_id = ls.LpuBuilding_id,
								@Lpu_id = ls.Lpu_id
							from
								v_LpuSection ls with (nolock)
							where
								ls.LpuSection_id = @LpuSection_id;

				with tmp as (
				select 
					EPTD.Drug_id,  EPTD.DrugComplexMnn_id,
					convert(numeric (18, 6), round(EPTD.EvnPrescrTreatDrug_Kolvo * isnull(Treat.EvnPrescrTreat_PrescrCount, 1)  / isnull(gpc.GoodsPackCount_Count, 1), 6)) EvnCourseTreatDrug_Count
				from dbo.v_EvnPrescr EP with (nolock)
					inner join v_EvnSection ES with (nolock) on ES.EvnSection_id = EP.EvnPrescr_pid or  ES.EvnSection_pid = EP.EvnPrescr_pid
					inner join Evn e with (nolock) on e.Evn_id = ES.EvnSection_pid -- КВС для фильтрации по датам	
					left join v_EvnPrescrTreat Treat with (nolock) on Treat.EvnPrescrTreat_id = EP.EvnPrescr_id		
					inner join v_EvnPrescrTreatDrug EPTD with (nolock) on  EPTD.EvnPrescrTreat_id = EP.EvnPrescr_id
					cross apply( Select top 1  DrugComplexMnn_id, isnull(GoodsUnit_id, GoodsUnit_sid) GoodsUnit_id  
						from  v_EvnCourseTreatDrug ec_drug with (nolock) where Treat.EvnCourse_id = ec_drug.EvnCourseTreat_id
							and  isnull(EPTD.Drug_id, 0) = case when ec_drug.Drug_id is not null then ec_drug.Drug_id else isnull(EPTD.Drug_id, 0) end 
							and  isnull(EPTD.DrugComplexMnn_id, 0) = case when ec_drug.DrugComplexMnn_id is not null then ec_drug.DrugComplexMnn_id else isnull(EPTD.DrugComplexMnn_id, 0) end 
							)
						ec_drug
					left join v_GoodsUnit ec_gu  with (nolock) on ec_drug.GoodsUnit_id = ec_gu.GoodsUnit_id		
					outer apply( Select  * from  r2.fn_GoodsPackCount(ec_drug.DrugComplexMnn_id) gpc
						where isnull(ec_drug.GoodsUnit_id, 0) = coalesce(gpc.GoodsUnit_ID, ec_gu.GoodsUnit_id, 0)
					) gpc
				where EP.PrescriptionType_id = '5'
					and isnull(cast(EP.EvnPrescr_setDT as date), @begDate) >= @begDate
					and ES.LpuSection_id = @LpuSection_id
					and EP.Lpu_id = @Lpu_id
					and isnull(EP.EvnPrescr_IsExec, 1) = 1
				)
				select DrugComplexMnn_id, sum(EvnCourseTreatDrug_Count) EvnCourseTreatDrug_Count 
					into #nazn 
					from tmp
					group by DrugComplexMnn_id;
				--SET NOCOUNT OFF;
				
				With
				Osttmp as (
				select   drug.Drug_id, drug.DrugComplexMnn_id, dor.DrugOstatRegistry_Kolvo--, dor.GoodsUnit_id
					from rls.v_Drug Drug with(nolock)
					inner join v_DrugOstatRegistry DOR WITH (NOLOCK) on DOR.Drug_id = Drug.Drug_id
						and dor.SubAccountType_id = 1
					inner join v_StorageStructLevel SSL with(nolock) on SSL.Storage_id = DOR.Storage_id
					inner join v_Storage SG with(nolock) on SG.Storage_id = DOR.Storage_id
					left join rls.v_PrepSeries PS with (nolock) on PS.PrepSeries_id = DOR.PrepSeries_id
					where 
						SSL.LpuSection_id = @LpuSection_id
						and DrugOstatRegistry_Kolvo > 0
						and (SG.Storage_endDate > GETDATE() or SG.Storage_endDate is null)
						and isnull(PS.PrepSeries_IsDefect, 1) = 1
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
					,convert(varchar, convert(numeric(19,2),
						case when isnull(ost.DrugOstatRegistry_Kolvo, 0) < isnull(nazn.EvnCourseTreatDrug_Count, 0) then  0
								else isnull (ost.DrugOstatRegistry_Kolvo, 0) - isnull(nazn.EvnCourseTreatDrug_Count, 0) end
					 )) as Ostat_Kolvo
					,convert(varchar, convert(numeric(19,2), ost.DrugOstatRegistry_Kolvo)) DrugOstatRegistry_Kolvo 
					,convert(varchar, convert(numeric(19,2), nazn.EvnCourseTreatDrug_Count)) EvnCourseTreatDrug_Count  
					{$drug_fas}
					-- end select 
					
				from 
				-- from
					Ost 
					left join #nazn nazn on nazn.DrugComplexMnn_id = ost.DrugComplexMnn_id
					join rls.v_DrugComplexMnn dcm with (NOLOCK) on dcm.DrugComplexMnn_id = ost.DrugComplexMnn_id
					left join rls.CLSDRUGFORMS df with (nolock) on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
					left join rls.DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
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

			$top = '';
			if (false == $withPaging) {
				$top = 'top 100';
			}
			$query = "
				select {$top}
					-- select
					dcm.DrugComplexMnn_id 
					,dcm.DrugComplexMnn_RusName as DrugComplexMnn_Name
					,DrugComplexMnnDose.DrugComplexMnnDose_Name as DrugComplexMnn_Dose
					,DrugComplexMnnDose.DrugComplexMnnDose_Mass
					,dcm.CLSDRUGFORMS_ID as RlsClsdrugforms_id
					,dcmn.Actmatters_id as RlsActmatters_id
					,coalesce(df.CLSDRUGFORMS_NameLatinSocr,df.NAME,'') as RlsClsdrugforms_Name
					,df.NAME as RlsClsdrugforms_RusName
					{$drug_fas}
					, null Ostat_Kolvo, null DrugOstatRegistry_Kolvo, null EvnCourseTreatDrug_Count	
					-- end select
				from
					-- from
					rls.v_DrugComplexMnn dcm with (NOLOCK)
					left join rls.CLSDRUGFORMS df with (nolock) on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
					left join rls.DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
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
			";
		}
		if ($withPaging) {

			$response = array();
			$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $queryParams);
			if ( is_object($result) ) {
				$response['data'] = $result->result('array');
				$response['totalCount'] = count($response['data']);
			} else {
				return false;
			}
			if (!(empty($data['start']) && $response['totalCount'] < $data['limit'])) {
				$result = $this->db->query(getCountSQLPH($query), $queryParams);
				if ( is_object($result) ) {
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
			$result = $this->db->query($query, $queryParams);
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
	function loadDrugSimpleList($data, $withPaging = false) {
	
		$queryParams = array();
		$relevant = '';
		$join = 'left join rls.v_DrugComplexMnn dcm with (NOLOCK) on dcm.DrugComplexMnn_id = Drug.DrugComplexMnn_id
				left join rls.DrugComplexMnnDose with (nolock) on DrugComplexMnnDose.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id';

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
					when Drug.Drug_Name LIKE :query_relevant and CHARINDEX ('+',dcm.drugcomplexmnn_rusname)>0 then 2
					when Drug.Drug_Name LIKE :query_relevant and CHARINDEX ('+',dcm.drugcomplexmnn_rusname)=0 then 1
					else 3
				end,";
				$queryParams['query'] = '%' . preg_replace('/ /', '%', $data['query']) . '%';
				$where = 'Drug.Drug_Name LIKE :query';
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
				-- variables
						Declare
							@lpu_id bigint,
							@LpuBuilding_id bigint,
							@LpuSection_id bigint = :LpuSection_id,
							@begDate date = GetDate() - 7;
						-- end variables
						-- addit with
						                      SET NOCOUNT ON;	
						select
								@LpuBuilding_id = ls.LpuBuilding_id,
								@Lpu_id = ls.Lpu_id
							from
								v_LpuSection ls with (nolock)
							where
								ls.LpuSection_id = @LpuSection_id;

						with nazn as (
							select 
							 EPTD.Drug_id,  EPTD.DrugComplexMnn_id,
												convert(numeric (18, 6), round(EPTD.EvnPrescrTreatDrug_Kolvo * isnull(Treat.EvnPrescrTreat_PrescrCount, 1)  / isnull(gpc.GoodsPackCount_Count, 1), 6)) EvnCourseTreatDrug_Count
											from dbo.v_EvnPrescr EP with (nolock)
												inner join v_EvnSection ES with (nolock) on ES.EvnSection_id = EP.EvnPrescr_pid or  ES.EvnSection_pid = EP.EvnPrescr_pid
												inner join Evn e with (nolock) on e.Evn_id = ES.EvnSection_pid -- КВС для фильтрации по датам	
												left join v_EvnPrescrTreat Treat with (nolock) on Treat.EvnPrescrTreat_id = EP.EvnPrescr_id		
												inner join v_EvnPrescrTreatDrug EPTD with (nolock) on  EPTD.EvnPrescrTreat_id = EP.EvnPrescr_id
												cross apply( Select top 1  DrugComplexMnn_id, isnull(GoodsUnit_id, GoodsUnit_sid) GoodsUnit_id  
													from  v_EvnCourseTreatDrug ec_drug with (nolock) where Treat.EvnCourse_id = ec_drug.EvnCourseTreat_id
														and  isnull(EPTD.Drug_id, 0) = case when ec_drug.Drug_id is not null then ec_drug.Drug_id else isnull(EPTD.Drug_id, 0) end 
														and  isnull(EPTD.DrugComplexMnn_id, 0) = case when ec_drug.DrugComplexMnn_id is not null then ec_drug.DrugComplexMnn_id else isnull(EPTD.DrugComplexMnn_id, 0) end 
														)
													ec_drug
												left join v_GoodsUnit ec_gu  with (nolock) on ec_drug.GoodsUnit_id = ec_gu.GoodsUnit_id		
												outer apply( Select  * from  r2.fn_GoodsPackCount(ec_drug.DrugComplexMnn_id) gpc
													where isnull(ec_drug.GoodsUnit_id, 0) = coalesce(gpc.GoodsUnit_ID, ec_gu.GoodsUnit_id, 0)
												) gpc
											where EP.PrescriptionType_id = '5'
												and isnull(cast(EP.EvnPrescr_setDT as date), @begDate) >= @begDate
												and ES.LpuSection_id = @LpuSection_id
												and EP.Lpu_id = @Lpu_id
												and isnull(EP.EvnPrescr_IsExec, 1) = 1
												and EPTD.Drug_id is not null
									)
									select 
									Drug_id, sum(EvnCourseTreatDrug_Count) EvnCourseTreatDrug_Count 
										into #nazn 
										from nazn
										group by Drug_id;

						With
									Osttmp as (
									select   drug.Drug_id, drug.DrugComplexMnn_id, dor.DrugOstatRegistry_Kolvo / isnull(gpc.GoodsPackCount_Count, 1) DrugOstatRegistry_Kolvo
										from rls.v_Drug Drug with(nolock)
										inner join v_DrugOstatRegistry DOR WITH (NOLOCK) on DOR.Drug_id = Drug.Drug_id
											and dor.SubAccountType_id = 1
										inner join v_StorageStructLevel SSL with(nolock) on SSL.Storage_id = DOR.Storage_id
										inner join v_Storage SG with(nolock) on SG.Storage_id = DOR.Storage_id
										left join rls.v_PrepSeries PS with (nolock) on PS.PrepSeries_id = DOR.PrepSeries_id
										outer apply( Select  * from  r2.fn_GoodsPackCount(drug.DrugComplexMnn_id) gpc
													where isnull(dor.GoodsUnit_id, 0) = coalesce(gpc.GoodsUnit_ID, dor.GoodsUnit_id, 0)
												) gpc
										where 
											SSL.LpuSection_id = @LpuSection_id
											and DrugOstatRegistry_Kolvo > 0
											and (SG.Storage_endDate > GETDATE() or SG.Storage_endDate is null)
											and isnull(PS.PrepSeries_IsDefect, 1) = 1
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
						Drug.Drug_Name,
						Drug.Drug_id as Drug_id,
						Drug.Drug_Code as Drug_Code,
						Drug.Drug_Dose,
						Drug.DrugPrep_id,
						Drug.DrugTorg_id as Tradenames_id,
						Drug.DrugComplexMnn_id,
						DrugComplexMnnDose.DrugComplexMnnDose_Mass,
						coalesce(df.CLSDRUGFORMS_NameLatinSocr,df.NAME,Drug.DrugForm_Name,'') as DrugForm_Name
						,convert(varchar, convert(numeric(19,2),
								case when isnull(ost.DrugOstatRegistry_Kolvo, 0) < isnull(nazn.EvnCourseTreatDrug_Count, 0) then  0
										else isnull (ost.DrugOstatRegistry_Kolvo, 0) - isnull(nazn.EvnCourseTreatDrug_Count, 0) end
							 )) as Ostat_Kolvo
							,convert(varchar, convert(numeric(19,2), ost.DrugOstatRegistry_Kolvo)) DrugOstatRegistry_Kolvo 
							,convert(varchar, convert(numeric(19,2), nazn.EvnCourseTreatDrug_Count)) EvnCourseTreatDrug_Count
					-- end select  
					from 
					-- from
					ost
						inner join rls.v_Drug Drug WITH (NOLOCK) on drug.Drug_id = ost.Drug_id
						left join rls.v_DrugComplexMnn dcm with (NOLOCK) on dcm.DrugComplexMnn_id = Drug.DrugComplexMnn_id
						left join rls.DrugComplexMnnDose with (nolock) on DrugComplexMnnDose.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id
						left join rls.CLSDRUGFORMS df with (nolock) on df.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
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
						declare
							@LpuSection_id bigint = :LpuSection_id,
							@LpuBuilding_id bigint,
							@Lpu_id bigint;
						
						select 
							@LpuBuilding_id = ls.LpuBuilding_id,
							@Lpu_id = ls.Lpu_id
						from
							v_LpuSection ls with (nolock)
						where
							ls.LpuSection_id = @LpuSection_id;
						
						select top 1
							dl.DrugList_id
						from
							v_DrugList dl with (nolock)
							left join v_DrugListType dlt with (nolock) on dlt.DrugListType_id = dl.DrugListType_id
							left join v_DrugListUsed dlu with (nolock) on dlu.DrugList_id = dl.DrugList_id
							left join v_DrugListObj dlo with (nolock) on dlo.DrugListObj_id = dlu.DrugListObj_id
							outer apply (
								select
									(case
										when dlo.LpuSection_id = @LpuSection_id then 1
										when dlo.LpuBuilding_id = @LpuBuilding_id then 2
										else 3
									end) as val
							) ord
						where
							dlt.DrugListType_Code = 1 and -- Формуляр
							(
								dlo.LpuSection_id = isnull(@LpuSection_id, dlo.LpuSection_id) or
								dlo.LpuBuilding_id = @LpuBuilding_id or
								dlo.Lpu_id = @Lpu_id
							) and
							exists ( -- формуляр не должен быть пустым
								select top 1
									dls.DrugListStr_id
								from
									v_DrugListStr dls with (nolock)
								where
									dls.DrugList_id = dl.DrugList_id and
									dls.Drug_id is not null
							)
						order by
							ord.val
					";
				if (!isset($data['LpuSection_id']))
					$data['LpuSection_id'] = null;
				$dl_data = $this->getFirstRowFromQuery($query, array(
					'LpuSection_id' => $data['LpuSection_id']
				));

				if (!empty($dl_data['DrugList_id'])) { //не пустой формуляр найден
					$where .= " and exists(
							select top 1
								dls.DrugListStr_id
							from
								v_DrugListStr dls with (nolock)
							where
								dls.DrugList_id = :DrugList_id and
								dls.Drug_id = Drug.Drug_id
						)";
					$queryParams['DrugList_id'] = $dl_data['DrugList_id'];
				}
			} 
			$top = '';
			if (false == $withPaging) {
				$top = 'top 100';
			}
			$query = "
					select {$top}
						-- select
						Drug.Drug_Name,
						Drug.Drug_id as Drug_id,
						Drug.Drug_Code as Drug_Code,
						Drug.Drug_Dose,
						Drug.DrugPrep_id,
						Drug.DrugTorg_id as Tradenames_id,
						Drug.DrugComplexMnn_id,
						DrugComplexMnnDose.DrugComplexMnnDose_Mass,
						coalesce(df.CLSDRUGFORMS_NameLatinSocr,df.NAME,Drug.DrugForm_Name,'') as DrugForm_Name
						-- end select
					from
						-- from
						rls.v_Drug Drug WITH (NOLOCK)
						left join rls.v_DrugPrep DrugPrep WITH (NOLOCK) on Drug.DrugPrepFas_id = DrugPrep.DrugPrepFas_id
						{$join}
						left join rls.CLSDRUGFORMS df with (nolock) on df.CLSDRUGFORMS_ID = dcm.CLSDRUGFORMS_ID
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
			if ( is_object($result) ) {
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
