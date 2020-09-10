<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* RlsDrug - модель для работы с медикаментами, ну и до кучи с аптеками (для схемы rls)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov R.
* @version      23.01.2012
*
* @property DocumentUc_model dumodel
* @property DocNormative_model dnmodel
*/

class RlsDrug_model extends swModel {
	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение цетнрального склада подразделения или МО по идентификатору отделения
	 */
	function getCentralStorageIdByLpuSectionId($lpu_section_id) {
		$central_storage_id = null;

		//попытка получить центральный склад подразделения
		$query = "
			declare
				@LpuBuilding_id bigint,
				@LpuSection_id bigint = :LpuSection_id;

			set @LpuBuilding_id = (select top 1 LpuBuilding_id from v_LpuSection with (nolock) where LpuSection_id = @LpuSection_id);

			select
				ssl.Storage_id
			from
				v_StorageStructLevel ssl with (nolock)
				left join v_Storage s with (nolock) on s.Storage_id = ssl.Storage_id
				outer apply (
					select
						count(i_ssl.StorageStructLevel_id) as cnt
					from
						v_StorageStructLevel i_ssl with (nolock)
					where
						i_ssl.LpuBuilding_id = @LpuBuilding_id and
						i_ssl.Storage_id = s.Storage_pid
				) p_ssl
			where
				ssl.LpuBuilding_id = @LpuBuilding_id and
				ssl.LpuUnit_id is null and -- центральный склад подразделения должен быть прописан на уровне подразделения
				p_ssl.cnt = 0; -- центральный склад подразделения не может подчиняться другим складам своего или более низких уровней
		";
		$storage_list = $this->queryList($query, array(
			'LpuSection_id' => $lpu_section_id
		));
		if (is_array($storage_list) && count($storage_list) == 1 && !empty($storage_list[0])) { //центральный склад на своем уровне может быть только одним, если их несколько, ни один из них не центральный
			$central_storage_id = $storage_list[0];
		}

		//попытка получить цетральный склад МО, если центральный склад подразделения не удалось определить
		if (empty($central_storage_id)) {
			$query = "
				declare
					@Lpu_id bigint,
					@LpuSection_id bigint = :LpuSection_id;
					 
				set @Lpu_id = (select top 1 Lpu_id from v_LpuSection with (nolock) where LpuSection_id = @LpuSection_id);				
				
				select
					ssl.Storage_id
				from
					v_StorageStructLevel ssl with (nolock)
					left join v_Storage s with (nolock) on s.Storage_id = ssl.Storage_id
				where
					ssl.Lpu_id = @Lpu_id and
					ssl.LpuBuilding_id is null and -- центральный склад МО должен быть прописан на уровне МО
					s.Storage_pid is null; -- центральный склад МО не может подчиняться другим складам
			";
			$storage_list = $this->queryList($query, array(
				'LpuSection_id' => $lpu_section_id
			));
			if (is_array($storage_list) && count($storage_list) == 1 && !empty($storage_list[0])) { //центральный склад на своем уровне может быть только одним, если их несколько, ни один из них не центральный
				$central_storage_id = $storage_list[0];
			}
		}

		return $central_storage_id;
	}

	/**
	 * Загрузка комбобокса sw.Promed.SwDrugSimpleCombo
	 */
	function loadDrugSimpleList($data, $withPaging = false) {
		$queryParams = array();
		$relevant = '';
		$join = 'left join rls.v_DrugComplexMnn dcm with (NOLOCK) on dcm.DrugComplexMnn_id = Drug.DrugComplexMnn_id
				left join rls.DrugComplexMnnDose with (nolock) on DrugComplexMnnDose.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id';
		
		/*  
		 *  По задаче #113868 для Уфы заменяю условие
		 *  DOR.DrugOstatRegistry_Kolvo-isnull(reserved.EvnDrug_Kolvo,0)) > 0 на
		 *  having  (sum(DOR.DrugOstatRegistry_Kolvo)-isnull(reserved.EvnDrug_Kolvo,0)) > 0'
		*/
		$having = '';
		$whereOst = '';
		
		$sessionParams = getSessionParams();
		$region =  $sessionParams['session']['region']['nick'];
		if ($region == 'ufa') {
			$having = 'group by dor.Drug_id, reserved.EvnDrug_Kolvo
					   having  (sum(DOR.DrugOstatRegistry_Kolvo)-isnull(reserved.EvnDrug_Kolvo,0)) > 0';
		}
		else {
			$whereOst = 'and (DOR.DrugOstatRegistry_Kolvo-isnull(reserved.EvnDrug_Kolvo,0)) > 0';
		}
		
		if ( $data['Drug_id'] > 0 )
		{
			$queryParams['Drug_id'] = $data['Drug_id'];			
			$where = 'Drug.Drug_id = :Drug_id';
		}
		else if ( strlen($data['query']) > 0 )
		{
			// поднимаем на верх те, которые начинаются с query и где одно действующее вещество
			$queryParams['query_relevant'] = preg_replace('/ /', '%', $data['query']) . '%';
			$relevant = "case
					when Drug.Drug_Name LIKE :query_relevant and CHARINDEX ('+',dcm.drugcomplexmnn_rusname)>0 then 2
					when Drug.Drug_Name LIKE :query_relevant and CHARINDEX ('+',dcm.drugcomplexmnn_rusname)=0 then 1
					else 3
				end,";
			$queryParams['query'] = '%'. preg_replace('/ /', '%', $data['query']) . '%';
			$where = 'Drug.Drug_Name LIKE :query';

			//выбор с остатков
			$dc_module = $this->options['drugcontrol']['drugcontrol_module'];

			if ($data['isFromDocumentUcOst']) {
				if ($data['LpuSection_id'] > 0) {
					$queryParams['date'] = date("Y-m-d");
					$queryParams['LpuSection_id'] = $data['LpuSection_id'];

					switch($dc_module) {
						case 1:
							//Остатки отделения по контрагенту (старая схема учета)
							$where .= ' and exists(
								select top 1 DUO.Drug_id
								from v_DocumentUcOst_Lite DUO WITH (NOLOCK)
								inner join Contragent C WITH (NOLOCK) on C.Contragent_id = DUO.Contragent_tid and C.LpuSection_id = :LpuSection_id
								where DUO.Drug_id = Drug.Drug_id and DUO.DocumentUcStr_Ost > 0
							)';
							break;
						case 2:
							//Остатки на складе отделения с учетом зарезервированных ЛС
							$where .= " and exists(
								select top 1 DOR.Drug_id
								from v_DrugOstatRegistry DOR WITH (NOLOCK)
								inner join v_StorageStructLevel SSL with(nolock) on SSL.Storage_id = DOR.Storage_id
								left join rls.v_PrepSeries PS with (nolock) on PS.PrepSeries_id = DOR.PrepSeries_id
								outer apply (
									select top 1 isnull(sum(ED.EvnDrug_Kolvo),0) as EvnDrug_Kolvo
									from v_EvnDrug ED with(nolock)
									inner join v_DocumentUcStr DUS with(nolock) on DUS.DocumentUcStr_id = ED.DocumentUcStr_id
									inner join v_DocumentUc DU with(nolock) on DU.DocumentUc_id = DUS.DocumentUc_id
									where ED.Drug_id = DOR.Drug_id and ED.LpuSection_id = SSL.LpuSection_id
									and isnull(DU.DrugDocumentStatus_id,1) = 1 --новый
								) reserved
								where DOR.Drug_id = Drug.Drug_id and SSL.LpuSection_id = :LpuSection_id
									--and (DOR.DrugOstatRegistry_Kolvo-isnull(reserved.EvnDrug_Kolvo,0)) > 0
									{$whereOst}
									and isnull(PS.PrepSeries_IsDefect, 1) = 1
									and	(PS.PrepSeries_GodnDate is null or PS.PrepSeries_GodnDate >= :date)
									$having
							)";
							break;
					}
				} else if ($data['Storage_id'] > 0 && $dc_module == 2) {
					$queryParams['date'] = date("Y-m-d");
					$queryParams['Storage_id'] = $data['Storage_id'];
					$queryParams['CentralStorage_id'] = null;

					if ($data['isFromCentralStorageOst'] && !empty($data['UserLpuSection_id'])) {
						$queryParams['CentralStorage_id'] = $this->getCentralStorageIdByLpuSectionId($data['UserLpuSection_id']);
					}

					//Остатки на складе отделения с учетом зарезервированных ЛС
					$where .= " and exists(
						select top 1
							DOR.Drug_id
						from
							v_DrugOstatRegistry DOR WITH (NOLOCK)
							left join rls.v_PrepSeries PS with (nolock) on PS.PrepSeries_id = DOR.PrepSeries_id
						where
							DOR.Drug_id = Drug.Drug_id
							and (
								DOR.Storage_id = :Storage_id or
								DOR.Storage_id = :CentralStorage_id
							)
							and DOR.DrugOstatRegistry_Kolvo > 0
							and isnull(PS.PrepSeries_IsDefect, 1) = 1
							and	(PS.PrepSeries_GodnDate is null or PS.PrepSeries_GodnDate >= :date)
					)";
				}
			} else {
				//если выбор ведется не из остатков, пытаемся фильтровать список по формуляру
				if($data['LpuSection_id'] > 0) {
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
									dls.Drug_id is not null
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
								dls.Drug_id = Drug.Drug_id
						)";
						$queryParams['DrugList_id'] = $dl_data['DrugList_id'];
					}
				}
			}
		}
		else
		{
			return false;
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
		if ($withPaging) {
			$response = array();
			$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $queryParams);
			if ( is_object($result) ) {
				$response['data'] = $result->result('array');
				$response['totalCount'] = count($response['data']);
			} else {
				return false;
			}
			if (empty($data['start']) && $response['totalCount'] < $data['limit']) {
				return $response;
			}
			$result = $this->db->query(getCountSQLPH($query), $queryParams);
			if ( is_object($result) ) {
				$cnt_arr = $result->result('array');
				$response['totalCount'] = $cnt_arr[0]['cnt'];
			} else {
				return false;
			}
			return $response;
		} else {
			//exit(getDebugSQL($query, $queryParams));
			$result = $this->db->query($query, $queryParams);
			if ( is_object($result) ) {
				return $result->result('array');
			} else {
				return false;
			}
		}
	}

	/**
	 * Получение списка торговых наименований
	 */
	function loadDrugTorgList($data) {
		$filter = "(1=1)";
		$with_filter = "(1=1)";
		$params = array();

		if (!empty($data['DrugTorg_id'])) {
			$with_filter .= " and d.DrugTorg_id = :DrugTorg_id";
			$params['DrugTorg_id'] = $data['DrugTorg_id'];
		} else {
			if (!empty($data['query'])) {
				$filter .= " and list.DrugTorg_Name like '%'+:query+'%'";
				$params['query'] = $data['query'];
			}
			if (!empty($data['Drug_id'])) {
				$with_filter .= " and d.Drug_id = :Drug_id";
				$params['Drug_id'] = $data['Drug_id'];
			}
			if (!empty($data['DrugComplexMnn_id'])) {
				$with_filter .= " and d.DrugComplexMnn_id = :DrugComplexMnn_id";
				$params['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
			}
			if (!empty($data['WhsDocumentSupply_id'])) {
				$with_filter .= " and exists(
					select * from v_WhsDocumentSupplySpec with(nolock)
					where Drug_id = d.Drug_id and WhsDocumentSupply_id = :WhsDocumentSupply_id
				)";
				$params['WhsDocumentSupply_id'] = $data['WhsDocumentSupply_id'];
			}
		}

		$query = "
			with drug_torg_list as (
				select distinct
					d.DrugTorg_id,
					d.DrugTorg_Name
				from rls.v_Drug d with(nolock)
				where {$with_filter}
			)
			select distinct top 100
				list.DrugTorg_id,
				list.DrugTorg_Name
			from drug_torg_list list with(nolock)
			where {$filter}
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Загрузка комбобокса sw.Promed.SwDrugNomenSimpleCombo
	 */
	function loadDrugNomenSimpleList($data) {
		$queryParams = array();
		$where = '(1 = 1)';
		$join = '';

		if ( $data['DrugNomen_id'] > 0 )
		{
			$queryParams['DrugNomen_id'] = $data['DrugNomen_id'];			
			$where = 'DrugNomen_id = :DrugNomen_id';
		}
		else if ( strlen($data['query']) > 0 )
		{
			$queryParams['query'] = '%'. $data['query'] . '%';
			$where = 'DrugNomen_Name LIKE :query';
		}

		$query = "
			select distinct top 500
				DrugNomen_id,
				DrugNomen_Name,
				DrugNomen_Code
			from rls.v_DrugNomen (nolock)
				{$join}
			where 
				{$where}
				AND PrepClass_id = 10 --только реактивы
		";
		//echo getDebugSql($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Загрузка комбобокса sw.Promed.SwDrugComplexMnnCombo
	 */
	function loadDrugComplexMnnList($data, $withPaging = false)
	{
		$queryParams = array();
		$where = "1=1";
		$join = ' left join rls.DrugComplexMnnDose with (nolock) on DrugComplexMnnDose.DrugComplexMnnDose_id = dcm.DrugComplexMnnDose_id';
		$relevant = '';
		$drug_fas = '';

		if($data['needFas']){
			$join .= ' left join rls.v_DrugComplexMnn DCM2 (nolock) on DCM2.DrugComplexMnn_id = DCM.DrugComplexMnn_pid
			left join rls.v_DrugComplexMnnFas DCMF (nolock) on DCMF.DrugComplexMnnFas_id = ISNULL(DCM.DrugComplexMnnFas_id,DCM2.DrugComplexMnnFas_id) ';
			$drug_fas = ',(
					isnull(DCMF.DrugComplexMnnFas_Kol,1) * 
					isnull(DCMF.DrugComplexMnnFas_KolPrim,1) * 
					isnull(DCMF.DrugComplexMnnFas_KolSec,1) * 
					isnull(DCMF.DrugComplexMnnFas_Tert,1)
				) as Drug_Fas ';
		}
		if ($data['DrugComplexMnn_id'] > 0) {
			$queryParams['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
			$where .= ' and dcm.DrugComplexMnn_id = :DrugComplexMnn_id';
		} else {
			if (strlen($data['query']) > 0 || !empty($data['Tradenames_id'])) {
				if (!empty($data['Tradenames_id'])) {
					$queryParams['Tradenames_id'] = $data['Tradenames_id'];
					$where .= " and dcm.DrugComplexMnn_id in (
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

				//выбор с остатков
				$dc_module = $this->options['drugcontrol']['drugcontrol_module'];

				if ($data['isFromDocumentUcOst']) {
					if($data['LpuSection_id'] > 0) {
						$queryParams['date'] = date("Y-m-d");
						$queryParams['LpuSection_id'] = $data['LpuSection_id'];

						switch ($dc_module) {
							case 1:
								//Остатки отделения по контрагенту (старая схема учета)
								$where .= ' and exists(
									select top 1 DUO.Drug_id
									from rls.v_Drug Drug WITH (NOLOCK)
									inner join v_DocumentUcOst_Lite DUO WITH (NOLOCK) on Drug.Drug_id = DUO.Drug_id and DUO.DocumentUcStr_Ost > 0
									inner join Contragent C WITH (NOLOCK) on C.Contragent_id = DUO.Contragent_tid and C.LpuSection_id = :LpuSection_id
									where Drug.DrugComplexMnn_id = dcm.DrugComplexMnn_id
								)';
								break;
							case 2:
								//Остатки на складе отделения с учетом зарезервированных ЛС
								$where .= ' and exists(
									select top 1 DOR.Drug_id
									from rls.v_Drug Drug with(nolock)
									inner join v_DrugOstatRegistry DOR WITH (NOLOCK) on DOR.Drug_id = Drug.Drug_id
									inner join v_StorageStructLevel SSL with(nolock) on SSL.Storage_id = DOR.Storage_id
									inner join v_Storage SG with(nolock) on SG.Storage_id = DOR.Storage_id
									left join rls.v_PrepSeries PS with (nolock) on PS.PrepSeries_id = DOR.PrepSeries_id
									outer apply (
										select top 1 isnull(sum(ED.EvnDrug_Kolvo),0) as EvnDrug_Kolvo
										from v_EvnDrug ED with(nolock)
										inner join v_DocumentUcStr DUS with(nolock) on DUS.DocumentUcStr_id = ED.DocumentUcStr_id
										inner join v_DocumentUc DU with(nolock) on DU.DocumentUc_id = DUS.DocumentUc_id
										where ED.Drug_id = DOR.Drug_id and ED.LpuSection_id = SSL.LpuSection_id
										and isnull(DU.DrugDocumentStatus_id,1) = 1 --новый
									) reserved
									where Drug.DrugComplexMnn_id = dcm.DrugComplexMnn_id and SSL.LpuSection_id = :LpuSection_id
										and (DOR.DrugOstatRegistry_Kolvo-isnull(reserved.EvnDrug_Kolvo,0)) > 0
										and (SG.Storage_endDate > GETDATE() or SG.Storage_endDate is null)
										and isnull(PS.PrepSeries_IsDefect, 1) = 1
										and	(PS.PrepSeries_GodnDate is null or PS.PrepSeries_GodnDate >= :date)
								)';
								break;
						}
					} else if ($data['Storage_id'] > 0 && $dc_module == 2) {
						$queryParams['date'] = date("Y-m-d");
						$queryParams['Storage_id'] = $data['Storage_id'];
						$queryParams['CentralStorage_id'] = null;

						if ($data['isFromCentralStorageOst'] && !empty($data['UserLpuSection_id'])) {
							$queryParams['CentralStorage_id'] = $this->getCentralStorageIdByLpuSectionId($data['UserLpuSection_id']);
						}

						//Остатки на складе с учетом зарезервированных ЛС
						$where .= ' and exists (
							select top 1
								DOR.Drug_id
							from
								rls.v_Drug Drug with(nolock)
								inner join v_DrugOstatRegistry DOR WITH (NOLOCK) on DOR.Drug_id = Drug.Drug_id
								inner join v_Storage SG with(nolock) on SG.Storage_id = DOR.Storage_id
								left join rls.v_PrepSeries PS with (nolock) on PS.PrepSeries_id = DOR.PrepSeries_id
							where
								Drug.DrugComplexMnn_id = dcm.DrugComplexMnn_id and
								(
									DOR.Storage_id = :Storage_id or
									DOR.Storage_id = :CentralStorage_id
								) and
								DOR.DrugOstatRegistry_Kolvo > 0 and
								(
									SG.Storage_endDate > GETDATE() or
									SG.Storage_endDate is null
								) and
								isnull(PS.PrepSeries_IsDefect, 1) = 1 and
								(
									PS.PrepSeries_GodnDate is null or
									PS.PrepSeries_GodnDate >= :date
								)
						)';
					}
				} else {
					//если выбор ведется не из остатков, пытаемся фильтровать список по формуляру
					if($data['LpuSection_id'] > 0) {
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
		if ($withPaging) {
			$response = array();
			$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $queryParams);
			if ( is_object($result) ) {
				$response['data'] = $result->result('array');
				$response['totalCount'] = count($response['data']);
			} else {
				return false;
			}
			if (empty($data['start']) && $response['totalCount'] < $data['limit']) {
				return $response;
			}
			$result = $this->db->query(getCountSQLPH($query), $queryParams);
			if ( is_object($result) ) {
				$cnt_arr = $result->result('array');
				$response['totalCount'] = $cnt_arr[0]['cnt'];
			} else {
				return false;
			}
			return $response;
		} else {
			//exit(getDebugSQL($query, $queryParams));
			$result = $this->db->query($query, $queryParams);
			if ( is_object($result) ) {
				return $result->result('array');
			} else {
				return false;
			}
		}
	}
	
	/**
	 * Поиск медикаментов по всему справочнику
	 * Используется в окне поиске АРМ фармацевта и оп. склада
	 */
	function searchFullDrugList($data) {
		$queryParams = array();
		$where       = '';

		if ( $data['Drug_id'] > 0 ) {
			$queryParams['Drug_id'] = $data['Drug_id'];			
			$where .= " and Drug.Drug_id = :Drug_id";
		} else {
			if ( strlen($data['query']) > 0 ) {
				$queryParams['query'] = $data['query'] . "%";
				$where .= " and Drug.Drug_Name like replace(ltrim(rtrim(:query)),' ', '%') + '%'";
			}
			if ( $data['DrugMnn_id'] > 0 ) {
				$queryParams['DrugMnn_id'] = $data['DrugMnn_id'];
				$where .= " and Drug.DrugMnn_id = :DrugMnn_id";
			}
			if ( $data['DrugComplexMnn_id'] > 0 ) {
				$queryParams['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
				$where .= " and Drug.DrugComplexMnn_id = :DrugComplexMnn_id";
			}
			if ( !empty($data['Drug_Ean']) ) {
				$queryParams['Drug_Ean'] = $data['Drug_Ean'];
				$where .= " and Drug.Drug_Ean = :Drug_Ean";
			}
		}

		$query = "
			SELECT distinct top 100
				[Drug].[Drug_id],
				[Drug].[Drug_Code],
				RTRIM([Drug].[Drug_Name]) as [Drug_Name],
				[Drug].[DrugMnn_id],
				[Drug].[DrugComplexMnn_id]
			FROM
				rls.v_Drug [Drug] with (NOLOCK)				
			WHERE (1 = 1)
				".$where."
			ORDER BY [Drug_Name]
		";
		//echo getDebugSQL($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Поиск МНН по всему справочнику без учета даты
	 * Используется в фильтре в окне поиске рецепта
	 */
	function searchFullDrugMnnList($data) {
		$queryParams = array();
		$where       = '';

		if ( $data['DrugMnn_id'] > 0 ) {
			$queryParams['DrugMnn_id'] = $data['DrugMnn_id'];
			$where .= " and DrugMnn.DrugMnn_id = :DrugMnn_id";
		} else {
			$queryParams['query'] = $data['query'] . "%";
			$where .= " and DrugMnn.DrugMnn_Name LIKE :query";
		}

		$query = "
			SELECT distinct top 100
				DrugMnn.DrugMnn_id,
				DrugMnn.DrugMnn_Code,
				RTRIM(DrugMnn.DrugMnn_Name) as DrugMnn_Name
			FROM rls.v_Drug Drug with (NOLOCK)
				inner join rls.DrugMnn with (NOLOCK) on DrugMnn.DrugMnn_id = Drug.DrugMnn_id
			WHERE (1 = 1)
				".$where."
			ORDER BY DrugMnn_Name
		";
		$result = $this->db->query($query, $queryParams);

		
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Поиск производителя по всему справочнику
	 * Используется в редактировании спецификации договоров на поставку
	 */
	function searchFullFirmNamesList($data) {
		$queryParams = array();
		$where       = '';

		if ( $data['FIRMNAMES_ID'] > 0 ) {
			$queryParams['FIRMNAMES_ID'] = $data['FIRMNAMES_ID'];
			$where .= " and FIRMNAMES.FIRMNAMES_ID = :FIRMNAMES_ID";
		} else {
			$queryParams['query'] = $data['query'] . "%";
			$where .= " and FIRMNAMES.NAME LIKE :query";
		}

		$query = "
			SELECT distinct top 100
				FIRMNAMES.FIRMNAMES_ID,				
				RTRIM(FIRMNAMES.NAME) as NAME
			FROM rls.FIRMNAMES with (NOLOCK)
			WHERE (1 = 1)
				".$where."
			ORDER BY NAME
		";
		$result = $this->db->query($query, $queryParams);

		
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Поиск упаковки по всему справочнику
	 * Используется в редактировании спецификации договоров на поставку
	 */
	function searchFullDrugPackList($data) {
		$queryParams = array();
		$where       = '';

		if ( $data['DRUGPACK_ID'] > 0 ) {
			$queryParams['DRUGPACK_ID'] = $data['DRUGPACK_ID'];
			$where .= " and DRUGPACK.DRUGPACK_ID = :DRUGPACK_ID";
		} else {
			$queryParams['query'] = $data['query'] . "%";
			$where .= " and DRUGPACK.FULLNAME LIKE :query";
		}

		$query = "
			SELECT distinct top 100
				DRUGPACK.DRUGPACK_ID,				
				RTRIM(DRUGPACK.NAME) as NAME,
				RTRIM(DRUGPACK.FULLNAME) as FULLNAME
			FROM rls.DRUGPACK with (NOLOCK)
			WHERE (1 = 1)
				".$where."
			ORDER BY FULLNAME
		";
		$result = $this->db->query($query, $queryParams);

		
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * вывод информации о всех остатках для конкретного медикамента
	 */
	function loadFullOstatList($filter) {
		$query = "
			select
				dor.DrugOstatRegistry_id,
				--isnull(D.Drug_Name, '') as Drug_Name,
				isnull(org.Org_Name, '') as Org_Name,
				isnull(dor.DrugOstatRegistry_Kolvo, 0.00) as DrugOstatRegistry_Kolvo,
				str(isnull(dor.DrugOstatRegistry_Sum, 0.00), 10, 2) as DrugOstatRegistry_Sum,
				case
					when ISNULL(DOR.DrugOstatRegistry_Kolvo,0) > 0 then STR(DOR.DrugOstatRegistry_Sum/DOR.DrugOstatRegistry_Kolvo,10,2)
					else STR(0,10,2)
				end as DrugOstatRegistry_Price,
				ISNULL(O.Okei_Name, '') as Okei_Name,
				wds.WhsDocumentUc_Num,
				wds.WhsDocumentUc_Name,
				convert(varchar,wds.WhsDocumentUc_Date,104) as WhsDocumentUc_Date,
				df.DrugFinance_Name,
				wdcit.WhsDocumentCostItemType_Name,
				sat.SubAccountType_Name
			from
				v_DrugOstatRegistry dor with (nolock)
				left join rls.v_Drug d with (nolock) on d.Drug_id = dor.Drug_id
				left join v_Org org with (nolock) on org.Org_id = dor.Org_id
				left join v_Okei o with (nolock) on o.Okei_id = dor.Okei_id
				left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dor.DrugShipment_id
				left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
				left join v_DrugFinance df with (nolock) on df.DrugFinance_id = dor.DrugFinance_id
				left join v_WhsDocumentCostItemType wdcit with (nolock) on wdcit.WhsDocumentCostItemType_id = dor.WhsDocumentCostItemType_id
				left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
			where
				dor.Drug_id = :Drug_id and
				dor.DrugOstatRegistry_Kolvo is not null and
				dor.DrugOstatRegistry_Kolvo > 0
			order by
				dor.DrugOstatRegistry_id;
		";
		$result = $this->db->query($query, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}


	/**
	 * вывод информации о всех рецептах для конкретного медикамента
	 */
	function loadFullReceptList($filter) {
		$query = "
			select TOP 1000
				ER.EvnRecept_id,
				ER.Person_id,
				ER.Server_id,
				ER.Drug_id,
				isnull((select top 1 ReceptDelayType_Name from ReceptDelayType ER_RDT with(nolock) where ER_RDT.ReceptDelayType_id = ER.ReceptDelayType_id), 'Выписан') as Recept_Status,
				RTRIM(PS.Person_Surname) as Person_Surname,
				RTRIM(PS.Person_Firname) as Person_Firname,
				RTRIM(PS.Person_Secname) as Person_Secname,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_Birthday,
				convert(varchar(10), ER.EvnRecept_setDate,104) as EvnRecept_setDate,
				RTRIM(ER.EvnRecept_Ser) as EvnRecept_Ser,
				RTRIM(ER.EvnRecept_Num) as EvnRecept_Num,
				ROUND(ER.EvnRecept_Kolvo, 3) as EvnRecept_Kolvo,
				RTRIM(ERMP.Person_Fio) as MedPersonal_Fio,
				RTRIM(COALESCE(ERDrugRls.Drug_Name, ERDrug.Drug_Name, ER.EvnRecept_ExtempContents)) as Drug_Name,
				ER.ReceptRemoveCauseType_id
			from
				v_PersonState PS with (nolock)
				inner join v_EvnRecept ER with (nolock) on ER.Person_id = PS.Person_id
				left join v_Drug ERDrug with (nolock) on ERDrug.Drug_id = ER.Drug_id
				left join rls.v_Drug ERDrugRls with (nolock) on ERDrugRls.Drug_id = ER.Drug_rlsid
				left join v_MedPersonal ERMP with (nolock) on ERMP.MedPersonal_id = ER.MedPersonal_id
			where
				ER.ReceptRemoveCauseType_id is null and
				--ER.Drug_rlsid is not null
				ER.Drug_rlsid = :Drug_id
			order by
				Recept_Status,
				ER.EvnRecept_setDate desc;
		";
		$result = $this->db->query($query, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Добавление данных в номенклатурный справочник
	 * $object - наименование сущности
	 * $id - идентификатор сущности
	 *
	 * возвращает id записи из таблицы справочника
	 */
	function addNomenData($object, $id, $data) {

		if (empty($object) || $id <= 0)
			return null;

		$code_tbl = null;
		$code_id = null;
		$query = null;

		switch($object) {
			case 'Drug':
				if(empty($code_tbl)) $code_tbl = 'DrugNomen';
			case 'TRADENAMES':
				if(empty($code_tbl)) $code_tbl = 'DrugTorgCode';
			case 'ACTMATTERS':
				if(empty($code_tbl)) $code_tbl = 'DrugMnnCode';
			case 'DrugComplexMnn':
				if(empty($code_tbl)) $code_tbl = 'DrugComplexMnnCode';

				// Ищем запись в таблице номенклатурного справочника
				$query = "
					select
						{$code_tbl}_id as code_id
					from
						rls.v_{$code_tbl} with(nolock)
					where
						{$object}_id = :id;
				";
				$result = $this->db->query($query, array('id' => $id));
				if (is_object($result)) {
					$result = $result->result('array');
					if (isset($result[0]) && $result[0]['code_id'] > 0) { //возвращаем найденый id кода
						$code_id = $result[0]['code_id'];
					} else { //добавляем запись в номенклатурный справочник
						//получаем новый код
						$query = "
							select
								isnull(max(cast({$code_tbl}_Code as numeric(14,0))), 0)+1 as new_code
							from
								rls.v_{$code_tbl} with(nolock)
							where
								isnumeric(rtrim(ltrim({$code_tbl}_Code)) + 'e0') = 1 and
	                            len({$code_tbl}_Code) <= 14;
						";
						$result = $this->db->query($query, array('id' => $id));
						if (is_object($result)) {
							$result = $result->result('array');
							if ($result[0]['new_code'] > 0) {
								$new_code = $result[0]['new_code'];

								if ($object == 'Drug') {
									//получаем информацию о медикаменте
									$q = "
										select
											d.Drug_Name as name,
											d.DrugTorg_Name as nick,
											d.DrugTorg_id as tradenames_id,
											DrugComplexMnnName.ActMatters_id as actmatters_id,
											dcm.DrugComplexMnn_id as complexmnn_id,
											A.STRONGGROUPID,
											A.NARCOGROUPID,
											P.NTFRID as CLSNTFR_ID,
											d.PrepType_id
										from
											rls.v_Drug d with (nolock)
											left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
											left join rls.DrugComplexMnnName with (nolock) on DrugComplexMnnName.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
											left join rls.v_ACTMATTERS A with (nolock) on A.Actmatters_id = DrugComplexMnnName.ActMatters_id
											left join rls.Prep P with (nolock) on P.Prep_id = d.DrugPrep_id
										where
											Drug_id = :id;
									";
									$r = $this->db->query($q, array(
										'id' => $id
									));
									if (is_object($r)) {
										$r = $r->result('array');

										$p = array();
										$p['name'] = $r[0]['name'];
										$p['nick'] = $r[0]['nick'];
										$p['tradenames_code'] = $r[0]['tradenames_id'] > 0 ? $this->addNomenData('TRADENAMES', $r[0]['tradenames_id'], $data) : null;
										$p['actmatters_code'] = $r[0]['actmatters_id'] > 0 ? $this->addNomenData('ACTMATTERS', $r[0]['actmatters_id'], $data) : null;
										$p['complexmnn_code'] = $r[0]['complexmnn_id'] > 0 ? $this->addNomenData('DrugComplexMnn', $r[0]['complexmnn_id'], $data) : null;
										$p['PrepClass_id'] = $this->getDrugPrepClassId(array_merge($r[0], array('Actmatters_id' => $r[0]['actmatters_id'])));
										$p['id'] = $id;
										$p['code'] = $new_code;
										$p['pmUser_id'] = $data['pmUser_id'];

										$p['nds_id'] = !empty($data['DrugNds_id']) ? $data['DrugNds_id'] : null;
										$p['okei_id'] = !empty($data['Okei_id']) ? $data['Okei_id'] : null;

										//добавляем запись в таблицу
										$q = "
											declare
												@{$code_tbl}_id bigint,
												@PrepClass_id bigint,
												@ErrCode int,
												@ErrMessage varchar(4000);

											set @PrepClass_id = (select PrepClass_id from rls.v_PrepClass with (nolock) where PrepClass_Code = 2);
											if @PrepClass_id is null set @PrepClass_id = :PrepClass_id;

											exec rls.p_{$code_tbl}_ins
												@{$code_tbl}_id = @{$code_tbl}_id output,
												@{$object}_id = :id,
												@{$code_tbl}_Code = :code,
												@DrugNomen_Name = :name,
												@DrugNomen_Nick = :nick,
												@DrugNds_id = :nds_id,
												@Okei_id = :okei_id,
												@DrugTorgCode_id = :tradenames_code,
												@DrugMnnCode_id = :actmatters_code,
												@DrugComplexMnnCode_id = :complexmnn_code,
												@PrepClass_id = @PrepClass_id,
												@Region_id = null,
												@pmUser_id = :pmUser_id,
												@Error_Code = @ErrCode output,
												@Error_Message = @ErrMessage output;
												select @{$code_tbl}_id as code_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
										";
										$r = $this->db->query($q, $p);
										if (is_object($r)) {
											$r = $r->result('array');
											$code_id = $r[0]['code_id'];
										}
									}
								} else {
									//добавляем запись в таблицу
									$q = "
										declare
											@{$code_tbl}_id bigint,
											@ErrCode int,
											@ErrMessage varchar(4000);
										exec rls.p_{$code_tbl}_ins
											@{$code_tbl}_id = @{$code_tbl}_id output,
											@{$object}_id = :id,
											@{$code_tbl}_Code = :code,
											@Region_id = null,
											@pmUser_id = :pmUser_id,
											@Error_Code = @ErrCode output,
											@Error_Message = @ErrMessage output;
											select @{$code_tbl}_id as code_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
									";
									$r = $this->db->query($q, array(
										'id' => $id,
										'code' => $new_code,
										'pmUser_id' => $data['pmUser_id']
									));
									if (is_object($r)) {
										$result = $r->result('array');
										$code_id = $result[0]['code_id'];
									}

									if ($object == 'DrugComplexMnn') { //При добавлении в справочник комплексного МНН необходимо позаботится и о добавлении действующего вещества
										//получаем информацию о комплексном МНН
										$q = "
											select
												DrugComplexMnnName.ActMatters_id as actmatters_id
											from
												rls.v_DrugComplexMnn with (nolock)
												left join rls.DrugComplexMnnName with (nolock) on DrugComplexMnnName.DrugComplexMnnName_id = v_DrugComplexMnn.DrugComplexMnnName_id
											where
												DrugComplexMnn_id = :id;
										";
										$r = $this->db->query($q, array(
											'id' => $id
										));
										if (is_object($r)) {
											$r = $r->result('array');
											if ($r[0]['actmatters_id'] > 0)
												$this->addNomenData('ACTMATTERS', $r[0]['actmatters_id'], $data);
										}
									}
								}
							}
						}
					}
				}


				break;
		}

		return $code_id;
	}

	/**
	* Получение класса номенклатуры
	*/
	function getDrugPrepClassId ($drug) {
        $class_id = null;
        $drug_data = array(
            'Actmatters_id' => !empty($drug['Actmatters_id']) ? $drug['Actmatters_id'] : null,
            'STRONGGROUPID' => !empty($drug['STRONGGROUPID']) ? $drug['STRONGGROUPID'] : null,
            'NARCOGROUPID' => !empty($drug['NARCOGROUPID']) ? $drug['NARCOGROUPID'] : null,
            'CLSNTFR_ID' => !empty($drug['CLSNTFR_ID']) ? $drug['CLSNTFR_ID'] : null,
            'PrepType_id' => !empty($drug['PrepType_id']) ? $drug['PrepType_id'] : null
        );
	
		// Этиловый спирт
		// этанол также относится к ядовитым, поэтому первым
        if (empty($class_id) && $drug_data['Actmatters_id'] == 1796) {
            $class_id = 13;
        }

		// Ядовитые лекарственные средства
		if (empty($class_id) && $drug_data['STRONGGROUPID'] == 2) {
            $class_id = 11;
        }
			
		// Наркотические лекарственные средства
		if (empty($class_id) && $drug_data['NARCOGROUPID'] == 2) {
            $class_id = 12;
        }
			
		// Лечебные минеральные воды
		if (empty($class_id) && $drug_data['CLSNTFR_ID'] == 182) {
            $class_id = 6;
        }
			
		// Перевязочные средства
		if (empty($class_id) && in_array($drug_data['CLSNTFR_ID'], array(6, 7, 8, 34, 38, 39))) {
            $class_id = 8;
        }
		
		// Дезинфекционные средства
		if (empty($class_id) && $drug_data['CLSNTFR_ID'] == 213) {
            $class_id = 7;
        }
		
		// Реактивы
		if (empty($class_id) && in_array($drug_data['CLSNTFR_ID'], array(153, 154, 155, 157, 158))) {
            $class_id = 10;
        }
		
		// Экстемпоральные 
		if (empty($class_id) && $drug_data['PrepType_id'] == 2) {
            $class_id = 22;
        }
		
		// Вспомогательные материалы 
		if (empty($class_id) && $drug_data['CLSNTFR_ID'] == 207) {
            $class_id = 18;
        }
		
		// Медикаменты – во всех остальных случаях.
        if (empty($class_id)) {
            $class_id = 1;
        }

        return $class_id;
	}

	/**
	 *  Получение списка цен на ЖНВЛП
	 */
	function loadJNVLPPriceGrid($data) {
		$filter = "";

		if (!empty($data['ActMatters_RusName'])) {
			$filter .= " and am.RUSNAME like :ActMatters_RusName";
			$data['ActMatters_RusName'] = "%".$data['ActMatters_RusName']."%";
		}

		if (!empty($data['Prep_Name'])) {
			$filter .= " and tn.NAME like :Prep_Name";
			$data['Prep_Name'] = "%".$data['Prep_Name']."%";
		}

		if (!empty($data['DrugMarkup_Delivery'])) {
			$filter .= " and DrugMarkup.Drugmarkup_Delivery = :DrugMarkup_Delivery";
		}

		if (!empty($data['DrugForm_Name'])) {
			$filter .= " and df.NAME like :DrugForm_Name";
			$data['DrugForm_Name'] = "%".$data['DrugForm_Name']."%";
		}

		if ($data['IsNarko'] != '') {
			$filter .= " and IsNarko.Code = :IsNarko";
		}

		$query = "
			select
				-- select
				cast(n.NOMEN_ID as varchar)+cast(DrugMarkup.DrugMarkup_id as varchar) as Key_id,
				n.NOMEN_ID as Nomen_id,
				IsNarko.Code as Prep_IsNarko,
				am.RUSNAME as ActMatters_RusName, --МНН
				tn.NAME as Prep_Name, --Лекарственный препарат
				df.NAME as DrugForm_Name, --Форма выпуска
				Dose.Value as Drug_Dose, --Дозировка
				Fas.Value as Drug_Fas, --Фасовка
				rc.REGNUM as Reg_Num, --№ РУ
				fn.NAME as Firm_Name, --Производитель
				convert(varchar(10), n.PRICEDATE, 104) as Price_Date, --Дата рег.цены
				n.PRICEORDER as Price_Order, --№ решения
				n.PRICEINRUB as Price, --Зарег.цена произв. (руб.)
				DrugMarkup.Drugmarkup_Delivery, --Зона
				cast(round(n.PRICEINRUB*DrugMarkup.Wholesale/100, 2) as decimal(12,2)) as Wholesale_Markup, --Предельно допустимая оптовая надбавка (руб.)
				cast(n.PRICEINRUB+round(n.PRICEINRUB*DrugMarkup.Wholesale/100, 2) as decimal(12,2)) as Wholesale_Price, --Предельно допустимая оптовая цена без НДС (руб.)
				cast(round((n.PRICEINRUB+round(n.PRICEINRUB*DrugMarkup.Wholesale/100, 2))*1.1, 2) as decimal(12,2)) as Wholesale_NdsPrice, --Предельно допустимая оптовая цена с НДС (руб.)
				cast(round(n.PRICEINRUB*DrugMarkup.Retail/100, 2) as decimal(12,2)) as Retail_Markup, --Предельно допустимая розничная надбавка (руб.)
				cast(n.PRICEINRUB+round(n.PRICEINRUB*DrugMarkup.Wholesale/100, 2)+round(n.PRICEINRUB*DrugMarkup.Retail/100, 2) as decimal(12,2)) as Retail_Price, --Предельно допустимая розничная цена без НДС (руб.)
				cast(round((n.PRICEINRUB+round(n.PRICEINRUB*DrugMarkup.Wholesale/100, 2)+round(n.PRICEINRUB*DrugMarkup.Retail/100, 2))*1.1, 2) as decimal(12,2)) as Retail_NdsPrice --Предельно допустимая розничная цена с НДС (руб.)
				-- end select
			from
				-- from
				rls.Nomen n with (nolock)
				inner join rls.Prep p with (nolock) on p.Prep_id = n.PREPID
				inner join rls.PREP_ACTMATTERS pa with (nolock) on pa.PREPID = n.PREPID
				left join rls.v_Drug d with(nolock) on d.Drug_id = n.NOMEN_ID
				left join rls.AM_DF_LIMP adl with (nolock) on adl.ACTMATTERID = pa.MATTERID and adl.DRUGFORMID = p.DRUGFORMID
				left join rls.TN_DF_LIMP tdl with (nolock) on tdl.TRADENAMEID = p.TRADENAMEID and tdl.DRUGFORMID = p.DRUGFORMID
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
					select (case when isnull(am.NARCOGROUPID, 0) = 2 then 1 else 0 end) as Code
				) IsNarko
				outer apply (
					select
						DrugMarkup_id,
						Drugmarkup_Delivery,
						DrugMarkup_Wholesale as Wholesale,
						DrugMarkup_Retail as Retail
					from
						v_DrugMarkup dm with(nolock)
						left join v_YesNo is_narko with(nolock) on is_narko.YesNo_id = DrugMarkup_IsNarkoDrug
					where
						n.PRICEINRUB between DrugMarkup_MinPrice and DrugMarkup_MaxPrice and
						isnull(is_narko.YesNo_Code, 0) = IsNarko.Code and
						(
							n.PRICEDATE is null or (
								DrugMarkup_begDT <= n.PRICEDATE and
								(
									DrugMarkup_endDT is null or
									DrugMarkup_endDT >= n.PRICEDATE
								)
							)
						)
				) DrugMarkup
				-- end from
			where
				-- where
				(adl.ACTMATTERID is not null or tdl.DRUGFORMID is not null) and
				n.PRICEINRUB is not null and
				isnull(Nomen_deleted, 1) <> 2
				{$filter}
				-- end where
			order by
				-- order by
				p.Prep_id
				-- end order by
		";

		if (isset($data['export']) && $data['export']) {
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				return $result->result('array');
			} else {
				return false;
			}
		} else {
			$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
			$result_count = $this->db->query(getCountSQLPH($query), $data);

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
	 *  Получения списка зон из справочника предельных наценок
	 */
	function loadDrugMarkupDeliveryList() {
		$query = "
			select distinct
				DrugMarkup_Delivery
			from
				v_DrugMarkup with(nolock)
			where
				DrugMarkup_Delivery is not null
			order by
				DrugMarkup_Delivery;
		";
		$result = $this->db->query($query);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Возвращает список серий препаратов
	 */
	function loadPrepSeriesList($data) {
		$params = array();
		$join = "";
		$filters = "";

		if (!empty($data['query'])) {
			$filters .= " and PS.PrepSeries_Ser like :PrepSeries_Ser+'%'";
			$params['PrepSeries_Ser'] = $data['query'];
		}
		if (!empty($data['Prep_id'])) {
			$filters .= " and PS.Prep_id = :Prep_id";
			$params['Prep_id'] = $data['Prep_id'];
		}
		if (!empty($data['Drug_id'])) {
			$join .= " left join rls.v_Drug D with(nolock) on D.DrugPrep_id = PS.Prep_id";
			$filters .= " and D.Drug_id = :Drug_id";
			$params['Drug_id'] = $data['Drug_id'];
		}

		$query = "
			select top 100
				PS.PrepSeries_id,
				PS.Prep_id,
				PS.PrepSeries_Ser,
				convert(varchar(10), PS.PrepSeries_GodnDate, 104) as PrepSeries_GodnDate
			from
				rls.v_PrepSeries PS with(nolock)
				{$join}
			where (1=1)
				{$filters}
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Возвращает список причин блокировки серий препаратов
	 */
	function loadPrepBlockCauseGrid($data) {
		$params = array();

		$query = "
			select
			-- select
				PrepBlockCause_id,
				PrepBlockCause_Code,
				PrepBlockCause_Name
			-- end select
			from
			-- from
				rls.v_PrepBlockCause with(nolock)
			-- end from
			order by
			-- order by
				PrepBlockCause_Code
			-- end order by
		";

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

	/**
	 * Возвращает список причин блокировки серий препаратов
	 */
	function loadPrepBlockCauseList($data) {
		$params = array();

		$query = "
			select
				PrepBlockCause_id,
				PrepBlockCause_Code,
				PrepBlockCause_Name
			from
				rls.v_PrepBlockCause with(nolock)
			order by
				PrepBlockCause_Code
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Сохраняет причину блокировки серий препаратов
	 */
	function savePrepBlockCause($data) {
		$params = array(
			'PrepBlockCause_id' => !empty($data['PrepBlockCause_id'])?$data['PrepBlockCause_id']:null,
			'PrepBlockCause_Code' => $data['PrepBlockCause_Code'],
			'PrepBlockCause_Name' => $data['PrepBlockCause_Name'],
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
		);

		if (empty($params['PrepBlockCause_id'])) {
			$procedure = 'p_PrepBlockCause_ins';
		} else {
			$procedure = 'p_PrepBlockCause_upd';
		}

		$query = "
			declare
				@PrepBlockCause_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec rls.{$procedure}
				@PrepBlockCause_id = @PrepBlockCause_id output,
				@PrepBlockCause_Code = :PrepBlockCause_Code,
				@PrepBlockCause_Name = :PrepBlockCause_Name,
				@Server_id = :Server_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @PrepBlockCause_id as PrepBlockCause_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Удаляет причину блокировки серий препаратов
	 */
	function deletePrepBlockCause($data) {
		$params = array('PrepBlockCause_id' => $data['PrepBlockCause_id']);

		$query = "
			select top 1
				count(PB.PrepBlock_id) as Count
			from rls.v_PrepBlock PB with(nolock)
			where PB.PrepBlockCause_id = :PrepBlockCause_id
		";
		$count = $this->getFirstResultFromQuery($query, $params);
		if ($count === false) {
			return $this->createError('','Ошибка при проверке использования причниы блокировки');
		}
		if ($count > 0) {
			return $this->createError('','Невозможно удалить. Причина испольуется для блокировки серии ЛС');
		}


		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec rls.p_PrepBlockCause_del
				@PrepBlockCause_id = :PrepBlockCause_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение кода для причины блокировки серий препаратов
	 */
	function getPrepBlockCauseCode($data) {
		$query = "
			declare @PrepBlockCause_Code int = (
				select top 1 max(PrepBlockCause_Code)
				from rls.v_PrepBlockCause with(nolock)
				where isnumeric(PrepBlockCause_Code) = 1
			)
			select isnull(@PrepBlockCause_Code,0)+1 as PrepBlockCause_Code
		";
		return $this->queryResult($query);
	}

	/**
	 * Сохраняет связь между блокировкой серии препарата и нормативным документом
	 */
	function savePrepBlockLink($data) {
		$params = array(
			'PrepBlockLink_id' => !empty($data['PrepBlockLink_id'])?$data['PrepBlockLink_id']:null,
			'PrepBlock_id' => $data['PrepBlock_id'],
			'DocNormative_id' => $data['DocNormative_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$procedure = 'rls.p_PrepBlockLink_ins';
		if (!empty($params['PrepBlockLink_id'])) {
			$procedure = 'rls.p_PrepBlockLink_upd';
		}

		$query = "
			declare
				@PrepBlockLink_id bigint = :PrepBlockLink_id,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec {$procedure}
				@PrepBlockLink_id = @PrepBlockLink_id output,
				@PrepBlock_id = :PrepBlock_id,
				@DocNormative_id = :DocNormative_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @PrepBlockLink_id as PrepBlockLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Удаляет связь между блокировкой серии препарата и нормативным документом
	 */
	function deletePrepBlockLink($data) {
		$params = array('PrepBlockLink_id' => $data['PrepBlockLink_id']);

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec rls.p_PrepBlockLink_del
				@PrepBlockLink_id = :PrepBlockLink_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Обнавляет флаг дефекта серии препарата на основе блокировок
	 */
	function refreshPrepSeriesDefect($data) {
		$params = array('PrepSeries_id' => $data['PrepSeries_id']);
		$query = "
			declare @date date = dbo.tzGetDate()

			select top 1 count(PB.PrepBlock_id) as Count
			from rls.v_PrepBlock PB with(nolock)
			where
				PB.PrepSeries_id = :PrepSeries_id
				and PB.PrepBlock_begDate <= @date
				and (PB.PrepBlock_endDate is null or PB.PrepBlock_endDate > @date)
		";
		$count = $this->getFirstResultFromQuery($query, $params);
		if ($count === false) {
			return $this->createError('', 'Ошибка при поиске блокировок серии ЛС');
		}

		$params = array(
			'PrepSeries_id' => $data['PrepSeries_id'],
			'PrepSerise_IsDefect' => ($count > 0)?2:1
		);
		$query = "
			update rls.PrepSeries
			set PrepSeries_IsDefect = :PrepSerise_IsDefect
			where PrepSeries_id = :PrepSeries_id
		";
		$this->db->query($query, $params);

		return array(array('success' => true));
	}

	/**
	 * Сохранение блокировки серии препарата
	 */
	function savePrepBlock($data) {
		$this->beginTransaction();

		if (empty($data['PrepSeries_id'])) {
			$this->load->model('DocumentUc_model', 'dumodel');
			$data['PrepSeries_id'] = $this->dumodel->savePrepSeries($data);
			if (empty($data['PrepSeries_id'])) {
				$this->rollbackTransaction();
				return $this->createError('', 'Ошибка при сохранении номера серии');
			}
		}

		$params = array(
			'PrepBlock_id' => !empty($data['PrepBlock_id'])?$data['PrepBlock_id']:null,
			'Drug_id' => $data['Drug_id'],
			'PrepSeries_id' => $data['PrepSeries_id'],
			'PrepBlockCause_id' => $data['PrepBlockCause_id'],
			'PrepBlock_begDate' => $data['PrepBlock_begDate'],
			'PrepBlock_endDate' => !empty($data['PrepBlock_endDate'])?$data['PrepBlock_endDate']:null,
			'PrepBlock_Comment' => !empty($data['PrepBlock_Comment'])?$data['PrepBlock_Comment']:null,
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		if (empty($data['PrepBlock_id'])) {
			$procedure = 'rls.p_PrepBlock_ins';
		} else {
			$procedure = 'rls.p_PrepBlock_upd';
		}

		$query = "
			declare
				@PrepBlock_id bigint = :PrepBlock_id,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec {$procedure}
				@PrepBlock_id = @PrepBlock_id output,
				@Drug_id = :Drug_id,
				@PrepSeries_id = :PrepSeries_id,
				@PrepBlockCause_id = :PrepBlockCause_id,
				@PrepBlock_begDate = :PrepBlock_begDate,
				@PrepBlock_endDate = :PrepBlock_endDate,
				@PrepBlock_Comment = :PrepBlock_Comment,
				@Server_id = :Server_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @PrepBlock_id as PrepBlock_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$response = $this->queryResult($query, $params);
		if (!$this->isSuccessful($response)) {
			$this->rollbackTransaction();
			return $this->createError('', 'Ошибка при сохранении блокировки ЛС');
		}
		$data['PrepBlock_id'] = $response[0]['PrepBlock_id'];

		$DocNormativeList = json_decode($data['DocNormativeList'], true);

		$this->load->model('DocNormative_model', 'dnmodel');

		$query = "
			select
				PBL.PrepBlockLink_id,
				DN.DocNormative_id,
				DN.DocNormativeType_id
			from
				rls.v_PrepBlockLink PBL with(nolock)
				inner join v_DocNormative DN with(nolock) on DN.DocNormative_id = PBL.DocNormative_id
			where
				PBL.PrepBlock_id = :PrepBlock_id
		";
		$link_list = $this->queryResult($query, $data);

		if (!is_array($link_list)) {
			$this->rollbackTransaction();
			return $this->createError('', 'Ошибка при проверке связи между блокировкой ЛС и нормативными документами');
		}

		$saved_ids = array();
		foreach($DocNormativeList as $DocNormative) {
			$DocNormative['DocNormative_Editor'] = 'Росздравнадзор';
			$DocNormative['DocNormative_begDate'] = ConvertDateFormat($DocNormative['DocNormative_begDate']);

			$params = $DocNormative;
			$params['pmUser_id'] = $data['pmUser_id'];
			$resp = $this->dnmodel->saveDocNormative($params);
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $this->createError('', 'Ошибка при сохранении нормативного документа');
			}
			$DocNormative['DocNormative_id'] = $resp[0]['DocNormative_id'];

			$params = array(
				'PrepBlock_id' => $data['PrepBlock_id'],
				'DocNormativeType_id' => $DocNormative['DocNormativeType_id'],
			);

			$PrepBlockLink_id = null;
			foreach($link_list as $PrepBlockLink) {
				//Поиск существующей связи
				if ($PrepBlockLink['DocNormativeType_id'] == $DocNormative['DocNormativeType_id']) {
					if ($PrepBlockLink['DocNormative_id'] == $DocNormative['DocNormative_id']) {
						$PrepBlockLink_id = $PrepBlockLink['PrepBlockLink_id'];
					}
				}
			}

			if (empty($PrepBlockLink_id)) {
				//Сохранение новой связи
				$params = array(
					'PrepBlock_id' => $data['PrepBlock_id'],
					'DocNormative_id' => $DocNormative['DocNormative_id'],
					'pmUser_id' => $data['pmUser_id']
				);
				$resp = $this->savePrepBlockLink($params);
				if (!$this->isSuccessful($resp)) {
					$this->rollbackTransaction();
					return $this->createError('', 'Ошибка при сохранении связи между блокировкой ЛС и нормативными документами');
				}
				$PrepBlockLink_id = $resp[0]['PrepBlockLink_id'];
			}
			$saved_ids[] = $PrepBlockLink_id;
		}

		foreach($link_list as $PrepBlockLink) {
			//Удаление неиспользуемых связей
			if (!in_array($PrepBlockLink['PrepBlockLink_id'], $saved_ids)) {
				$params = array('PrepBlockLink_id' => $PrepBlockLink['PrepBlockLink_id']);
				$resp = $this->deletePrepBlockLink($params);
				if (!$this->isSuccessful($resp)) {
					$this->rollbackTransaction();
					return $this->createError('', 'Ошибка при очистке связей между блокировкой ЛС и нормативными документами');
				}
			}
		}

		$resp = $this->refreshPrepSeriesDefect($data);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		$this->commitTransaction();

		return $response;
	}

	/**
	 * Удаление блокировки серии препарата
	 */
	function deletePrepBlock($data) {
		$params = array('PrepBlock_id' => $data['PrepBlock_id']);

		$this->beginTransaction();

		$data['PrepSeries_id'] = $this->getFirstResultFromQuery("
			select top 1 PrepSeries_id
			from rls.v_PrepBlock with(nolock)
			where PrepBlock_id = :PrepBlock_id
		", $params);
		if (!$data['PrepSeries_id']) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при получении заблокированной серии');
		}

		$query = "
			select
				PBL.PrepBlockLink_id,
				DN.DocNormative_id
			from
				rls.v_PrepBlockLink PBL with(nolock)
				inner join v_DocNormative DN with(nolock) on DN.DocNormative_id = PBL.DocNormative_id
			where
				PBL.PrepBlock_id = :PrepBlock_id
		";
		$link_list = $this->queryResult($query, $params);

		if (!is_array($link_list)) {
			$this->rollbackTransaction();
			return $this->createError('', 'Ошибка при проверке связи между блокировкой ЛС и нормативными документами');
		}

		foreach($link_list as $PrepBlockLink) {
			$resp = $this->deletePrepBlockLink(array('PrepBlockLink_id' => $PrepBlockLink['PrepBlockLink_id']));
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $this->createError('', 'Ошибка при очистке связей между блокировкой ЛС и нормативными документами');
			}
		}

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec rls.p_PrepBlock_del
				@PrepBlock_id = :PrepBlock_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$response = $this->queryResult($query, $params);
		if (!$this->isSuccessful($response)) {
			$this->rollbackTransaction();
			return $response;
		}

		$resp = $this->refreshPrepSeriesDefect($data);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		$this->commitTransaction();

		return $response;
	}

	/**
	 * Возрашает список блокировок серий препаратов
	 */
	function loadPrepBlockGrid($data) {
		$params = array();
		$filters = '(1=1)';

		if (!empty($data['Tradenames_id'])) {
			$filters .= " and TN.Tradenames_id = :Tradenames_id";
			$params['Tradenames_id'] = $data['Tradenames_id'];
		}
		if (!empty($data['Actmatters_id'])) {
			$filters .= " and AM.Actmatters_id = :Actmatters_id";
			$params['Actmatters_id'] = $data['Actmatters_id'];
		}
		if (!empty($data['RlsClsdrugforms_id'])) {
			$filters .= " and DF.CLSDRUGFORMS_ID = :RlsClsdrugforms_id";
			$params['RlsClsdrugforms_id'] = $data['RlsClsdrugforms_id'];
		}
		if (!empty($data['RlsCountries_id'])) {
			$filters .= " and F.COUNTID = :RlsCountries_id";
			$params['RlsCountries_id'] = $data['RlsCountries_id'];
		}
		if (!empty($data['Drug_Dose'])) {
			$filters .= " and Dose.Value like '%'+:Drug_Dose+'%'";
			$params['Drug_Dose'] = $data['Drug_Dose'];
		}
		if (!empty($data['Drug_Fas'])) {
			$filters .= " and Fas.Value like '%'+:Drug_Fas+'%'";
			$params['Drug_Fas'] = $data['Drug_Fas'];
		}
		if (!empty($data['Prep_RegNum'])) {
			$filters .= " and RC.REGNUM like '%'+:Prep_RegNum+'%'";
			$params['Prep_RegNum'] = $data['Prep_RegNum'];
		}
		if (!empty($data['PrepSeries_Ser'])) {
			$filters .= " and PS.PrepSeries_Ser like '%'+:PrepSeries_Ser+'%'";
			$params['PrepSeries_Ser'] = $data['PrepSeries_Ser'];
		}
		if (!empty($data['PrepBlockCause_id'])) {
			$filters .= " and PBC.PrepBlockCause_id = :PrepBlockCause_id";
			$params['PrepBlockCause_id'] = $data['PrepBlockCause_id'];
		}
		if (!empty($data['DocNormative_Num'])) {
			$filters .= " and (DN1.DocNormative_Num like '%'+:DocNormative_Num+'%' or DN2.DocNormative_Num like '%'+:DocNormative_Num+'%')";
			$params['DocNormative_Num'] = $data['DocNormative_Num'];
		}

		$query = "
			select
				-- select
				PB.PrepBlock_id,
				AM.RUSNAME as Actmatters_Name,	--МНН
				TN.NAME as Prep_Name,
				DF.FULLNAME as RlsClsdrugforms_Name,
				Dose.Value as Drug_Dose,
				Fas.Value as Drug_Fas,
				rc.REGNUM as Prep_RegNum,
				FN.NAME as Firm_Name,
				PS.PrepSeries_Ser,
				PBC.PrepBlockCause_Name,
				rtrim(
					DN1.DocNormative_Num+' '+
					convert(varchar(10), DN1.DocNormative_BegDate, 104)+' '+
					isnull(DN1.DocNormative_Name,'')
				) as DocNormative_Name_1,
				rtrim(
					DN2.DocNormative_Num+' '+
					convert(varchar(10), DN2.DocNormative_BegDate, 104)+' '+
					isnull(DN2.DocNormative_Name,'')
				) as DocNormative_Name_2,
				DN1.DocNormative_File as DocNormative_File_1,
				DN2.DocNormative_File as DocNormative_File_2,
				PB.PrepBlock_Comment,
				lpu.Lpu_Nick as Lpu_Name
				-- end select
			from
				-- from
				rls.v_PrepBlock PB with(nolock)
				left join rls.v_Drug D with(nolock) on D.Drug_id = PB.Drug_id
				left join rls.v_PrepSeries PS with(nolock) on PS.PrepSeries_id = PB.PrepSeries_id
				left join rls.v_Prep P with(nolock) on P.Prep_id = PS.Prep_id
				left join rls.v_PrepBlockCause PBC with(nolock) on PBC.PrepBlockCause_id = PB.PrepBlockCause_id
				left join rls.TRADENAMES TN with (nolock) on TN.TRADENAMES_ID = P.TRADENAMEID
				left join rls.PREP_ACTMATTERS PA with (nolock) on PA.PREPID = P.Prep_id
				left join rls.v_ACTMATTERS AM with(nolock) on AM.ACTMATTERS_ID = PA.MATTERID
				left join rls.CLSDRUGFORMS DF with (nolock) on DF.CLSDRUGFORMS_ID = P.DRUGFORMID
				left join rls.REGCERT rc with (nolock) on rc.REGCERT_ID = p.REGCERTID
				left join rls.FIRMS f with (nolock) on f.FIRMS_ID = p.FIRMID
				left join rls.FIRMNAMES fn with (nolock) on fn.FIRMNAMES_ID = f.NAMEID
				left join rls.MASSUNITS df_mu with (nolock) on df_mu.MASSUNITS_ID = p.DFMASSID
				left join rls.CONCENUNITS df_cu with (nolock) on df_cu.CONCENUNITS_ID = p.DFCONCID
				left join rls.ACTUNITS df_au with (nolock) on df_au.ACTUNITS_ID = p.DFACTID
				left join rls.SIZEUNITS df_su with (nolock) on df_su.SIZEUNITS_ID = p.DFSIZEID
				left join dbo.v_pmUser pu with (nolock) on pu.PMUser_id = PB.pmUser_updID
				left join dbo.v_Lpu_all lpu with (nolock) on pu.Lpu_id = lpu.Lpu_id
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
						DN.DocNormative_id,
						DN.DocNormative_Num,
						DN.DocNormative_Name,
						DN.DocNormative_begDate,
						DN.DocNormative_endDate,
						DN.DocNormative_File
					from
						rls.v_PrepBlockLink PBL with(nolock)
						inner join v_DocNormative DN with(nolock) on DN.DocNormative_id = PBL.DocNormative_id
					where DN.DocNormativeType_id = 1 and PBL.PrepBlock_id = PB.PrepBlock_id
				) as DN1
				outer apply (
					select top 1
						DN.DocNormative_id,
						DN.DocNormative_Num,
						DN.DocNormative_Name,
						DN.DocNormative_begDate,
						DN.DocNormative_endDate,
						DN.DocNormative_File
					from
						rls.v_PrepBlockLink PBL with(nolock)
						inner join v_DocNormative DN with(nolock) on DN.DocNormative_id = PBL.DocNormative_id
					where DN.DocNormativeType_id = 2 and PBL.PrepBlock_id = PB.PrepBlock_id
				) as DN2
				-- end from
			where
				-- where
				{$filters}
				-- end where
			order by
				-- order by
				AM.RUSNAME,
				TN.NAME
				-- end order by
		";
		//echo getDebugSQL($query, $params);exit;
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

	/**
	 * Возвращает данные для формы редактирования блокировки серии препарата
	 */
	function loadPrepBlockForm($data) {
		$params = array('PrepBlock_id' => $data['PrepBlock_id']);

		$query = "
			select top 1
				PB.PrepBlock_id,
				PB.Drug_id,
				PB.PrepSeries_id,
				PB.PrepBlockCause_id,
				PB.PrepBlock_Comment,

				DN1.DocNormative_id as DocNormative_id_1,
				DN1.DocNormative_Num as DocNormative_Num_1,
				DN1.DocNormative_Name as DocNormative_Name_1,
				convert(varchar(10), DN1.DocNormative_begDate, 104) as DocNormative_begDate_1,
				DN1.DocNormative_File as DocNormative_File_1,

				DN2.DocNormative_id as DocNormative_id_2,
				DN2.DocNormative_Num as DocNormative_Num_2,
				DN2.DocNormative_Name as DocNormative_Name_2,
				convert(varchar(10), DN2.DocNormative_begDate, 104) as DocNormative_begDate_2,
				DN2.DocNormative_File as DocNormative_File_2
			from
				rls.v_PrepBlock PB with(nolock)
				outer apply (
					select top 1
						DN.DocNormative_id,
						DN.DocNormative_Num,
						DN.DocNormative_Name,
						DN.DocNormative_begDate,
						DN.DocNormative_endDate,
						DN.DocNormative_File
					from
						rls.v_PrepBlockLink PBL with(nolock)
						inner join v_DocNormative DN with(nolock) on DN.DocNormative_id = PBL.DocNormative_id
					where DN.DocNormativeType_id = 1 and PBL.PrepBlock_id = PB.PrepBlock_id
				) as DN1
				outer apply (
					select top 1
						DN.DocNormative_id,
						DN.DocNormative_Num,
						DN.DocNormative_Name,
						DN.DocNormative_begDate,
						DN.DocNormative_endDate,
						DN.DocNormative_File
					from
						rls.v_PrepBlockLink PBL with(nolock)
						inner join v_DocNormative DN with(nolock) on DN.DocNormative_id = PBL.DocNormative_id
					where DN.DocNormativeType_id = 2 and PBL.PrepBlock_id = PB.PrepBlock_id
				) as DN2
			where
				PB.PrepBlock_id = :PrepBlock_id
		";

		return $this->queryResult($query, $params);
	}
	/**
	 * Поиск медикаментов по всему справочнику
	 * Используется в окне поиске АРМ фармацевта и оп. склада
	 */
	function searchByNameInMnnAndDrugList($data) {
		$queryParams = array();
		$where_mnn   = '';
		$where_drug  = '';
		$selectDrug  = '';
		$mnnCount 	 = '26';

		if ( strlen($data['query']) > 0 ) {
			$queryParams['query'] = $data['query'] . "%";
			$where_mnn .= " and DCM.DrugComplexMnn_RusName like replace(ltrim(rtrim(:query)),' ', '%') + '%'";
			$where_drug .= " and Drug.Drug_Name like replace(ltrim(rtrim(:query)),' ', '%') + '%'";
		}
		if (!empty($data['findByLatName'])) {
			$where_mnn = " and COALESCE(ACT.LATNAME,DrugComplexMnn_LatName,'') like replace(ltrim(rtrim(:query)),' ', '%') + '%'";
			$where_drug = " and COALESCE(ln.NAME,'') like replace(ltrim(rtrim(:query)),' ', '%') + '%'";
		}
		if (empty($data['onlyMnn'])) {
			$mnnCount 	 = '13';
			$selectDrug = "
				union all
				
					SELECT distinct top 13
						RTRIM(Drug.Drug_Name) as Drug_Name,
						Drug.Drug_id,
						Drug.DrugComplexMnn_id,
						COALESCE(ln.NAME,'') as LatName,
						null as ActMatters_id
					FROM rls.v_Drug Drug with (NOLOCK)
						inner join rls.DrugComplexMnn DrugMnn with (NOLOCK) on DrugMnn.DrugComplexMnn_id = Drug.DrugComplexMnn_id
						left join rls.PREP p with (nolock) on p.Prep_id = Drug.DrugPrep_id 
						left join rls.LATINNAMES ln with (nolock) on ln.LATINNAMES_ID = p.LATINNAMEID
					where (1=1)
						".$where_drug;
		}

		$query = "
			SELECT distinct top ".$mnnCount."
				RTRIM(DCM.DrugComplexMnn_RusName) as Drug_Name,
				null as Drug_id,
				DCM.DrugComplexMnn_id,
				COALESCE(ACT.LATNAME,DrugComplexMnn_LatName,'') as LatName,
				ACT.ACTMATTERS_id as ActMatters_id
			FROM rls.v_DrugComplexMnn DCM with (NOLOCK)
				left join rls.drugcomplexmnnname cmnn  with (nolock) on cmnn.DrugComplexMnnName_id= DCM.DrugComplexMnnName_id
				left join rls.ACTMATTERS ACT with (nolock) on ACT.ACTMATTERS_ID = cmnn.ACTMATTERS_id
			where (1=1)
				".$where_mnn."
		  		
			".$selectDrug."
		";

		//echo getDebugSQL($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/** Проверка на наличие записи в справочнике Действующих веществ с именем равным значению поля МНН */
	function checkActmatters($data)
	{
		$params = [];

		$query = "SELECT TOP 1 ACTMATTERS_ID FROM rls.v_ACTMATTERS WITH (nolock) WHERE LOWER(RUSNAME) = LOWER(:Actmatters_Names)";
		$params['Actmatters_Names'] = $data['Actmatters_Names'] ?? '';

		$res = $this->db->query($query, $params);

		return ( is_object($res) ) ? $res->result('array') : false;
	}
	/** Загрузка записи из справочнике Действующих веществ по id */
	function loadActmatters($data)
	{
		$params = ['Actmatters_id' => $data['Actmatters_id'] ?? ''];

		$query = "
			SELECT top 1 
				-- *
				a.ACTMATTERS_ID          AS Actmatters_id, 
				a.RUSNAME                AS Actmatters_Names, 
				a.LATNAME                AS Actmatters_LatName, 
				a.ACTMATTERS_LatNameGen  AS Actmatters_LatNameGen, 
				a.STRONGGROUPID          AS Actmatters_StrongGroupID, 
				a.NARCOGROUPID           AS Actmatters_NarcoGroupID, 
				a.ACTMATTERS_IsInterName AS Actmatters_isMNN,
				CASE WHEN ISNULL(p.PREPID, 1) = 1 THEN 0 ELSE 1 END AS usedBy
				-- *
			FROM rls.v_ACTMATTERS AS a WITH (nolock)
				LEFT JOIN rls.prep_actmatters AS p ON p.MATTERID = a.ACTMATTERS_ID			
			WHERE a.ACTMATTERS_ID = :Actmatters_id
		";

		return $this->queryResult($query, $params);
	}
	/** Добавление новой записи или обновление по id в справочнике Действующих веществ */
	function saveActmatters($data)
	{
		$procedure = (empty($data['Actmatters_id'])) 
			? 'rls.p_ACTMATTERS_ins' 
			: 'rls.p_ACTMATTERS_upd'
		;
		
		$params = [
			'Actmatters_id' => $data['Actmatters_id'] ?? NULL,
			'Actmatters_Names' => $data['Actmatters_Names'] ?? '',
			'Actmatters_LatName' => $data['Actmatters_LatName'] ?? '',
			'Actmatters_LatNameGen' => $data['Actmatters_LatNameGen'] ?? '',
			'Actmatters_StrongGroupID' => $data['Actmatters_StrongGroupID'] ?? NULL,
			'Actmatters_NarcoGroupID' => $data['Actmatters_NarcoGroupID'] ?? NULL,
			'Actmatters_isMNN' => $data['Actmatters_isMNN'] ?? 0,
			'pmUser_id' => $data['pmUser_id'] ?? NULL,
		];

		$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :Actmatters_id;
				exec {$procedure}
					@ACTMATTERS_ID          = @Res output,
					@RUSNAME                = :Actmatters_Names,
					@LATNAME                = :Actmatters_LatName,
					@ACTMATTERS_LatNameGen  = :Actmatters_LatNameGen,
					@STRONGGROUPID          = :Actmatters_StrongGroupID,
					@NARCOGROUPID           = :Actmatters_NarcoGroupID,
					@ACTMATTERS_IsInterName = :Actmatters_isMNN,
					@pmUser_id              = :pmUser_id,
					@Error_Code             = @ErrCode output,
					@Error_Message          = @ErrMessage output;
				select @Res as Actmatters_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		return $this->queryResult($query, $params);
	}
}
