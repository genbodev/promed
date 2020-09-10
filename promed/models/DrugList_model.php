<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * DrugList_model - модель для работы с перечнями медикаментов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 * @author       Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version      17.10.2017
 *
 */

class DrugList_model extends SwModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugListGrid($data) {
		$params = array();
		$filters = array('1=1');
		$usedFilters = array();

		if (isset($data['DrugListRange']) && !empty($data['DrugListRange'][0])) {
			$filters[] = "DL.DrugList_begDate between :DrugList_begDate and :DrugList_endDate";
			$filters[] = "(DL.DrugList_endDate is null or DL.DrugList_endDate between :DrugList_begDate and :DrugList_endDate)";
			$params['DrugList_begDate'] = $data['DrugListRange'][0];
			$params['DrugList_endDate'] = $data['DrugListRange'][1];
		}
		if (!empty($data['DrugList_Name'])) {
			$filters[] = "DL.DrugList_Name = :DrugList_Name";
			$params['DrugList_Name'] = $data['DrugList_Name'];
		}
		if (!empty($data['PayType_id'])) {
			$filters[] = "PT.PayType_id = :PayType_id";
			$params['PayType_id'] = $data['PayType_id'];
		}
		if (!empty($data['DrugListType_id'])) {
			$filters[] = "DL.DrugListType_id = :DrugListType_id";
			$params['DrugListType_id'] = $data['DrugListType_id'];
		}
		if (!empty($data['DrugListObj_id'])) {
			$filters[] = "DL.DrugListObj_id = :DrugListObj_id";
			$params['DrugListObj_id'] = $data['DrugListObj_id'];
		}

		if (!empty($data['Org_id'])) {
			$usedFilters[] = "isnull(L.Org_id, DLO.Org_id) = :Org_id";
			$params['Org_id'] = $data['Org_id'];
		}
		if (!empty($data['LpuBuilding_id'])) {
			$usedFilters[] = "DLO.LpuBuilding_id = :LpuBuilding_id";
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}
		if (!empty($data['LpuSection_id'])) {
			$usedFilters[] = "DLO.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}
		if (!empty($data['LpuSection_id'])) {
			$usedFilters[] = "DLO.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}
		if (!empty($data['DrugList_Profile'])) {
			$usedFilters[] = "(
				LS.LpuSectionProfile_Name like :DrugList_Profile+'%'
				or ETS.EmergencyTeamSpec_Name like :DrugList_Profile+'%'
			)";
			$params['DrugList_Profile'] = $data['DrugList_Profile'];
		}
		if (!empty($data['UslugaComplex_id'])) {
			$usedFilters[] = "DLO.UslugaComplex_id = :UslugaComplex_id";
			$params['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}
		if (!empty($data['Storage_id'])) {
			$usedFilters[] = "DLO.Storage_id = :Storage_id";
			$params['Storage_id'] = $data['Storage_id'];
		}

		if (count($usedFilters) > 0) {
			$usedFilters[] = "DLU.DrugList_id = DL.DrugList_id";
			$usedFilters_str = implode(" and ", $usedFilters);
			$filters[] = "exists(
				select * from v_DrugListUsed DLU with(nolock)
				inner join v_DrugListObj DLO with(nolock) on DLO.DrugListObj_id = DLU.DrugListObj_id
				left join v_Lpu L with(nolock) on L.Lpu_id = DLO.Lpu_id
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = DLO.LpuSection_id
				left join v_EmergencyTeamSpec ETS with(nolock) on ETS.EmergencyTeamSpec_id = DLO.EmergencyTeamSpec_id
				where {$usedFilters_str}
			)";
		}

		$filters_str = implode(' and ', $filters);

		$query = "
			select
			-- select
				DL.DrugList_id,
				DL.DrugList_Name,
				DLT.DrugListType_id,
				DLT.DrugListType_Code,
				DLT.DrugListType_Name,
				convert(varchar(10), DL.DrugList_begDate, 104) as DrugList_begDate,
				convert(varchar(10), DL.DrugList_endDate, 104) as DrugList_endDate,
				PT.PayType_id,
				PT.PayType_Name,
				DN.DocNormative_id,
				DN.DocNormative_Num,
				DN.DocNormative_Name,
				DL.KLCountry_id,
				DL.Region_id,
				case when DL.KLCountry_id = 643 then Region.KLArea_FullName else Country.KLCountry_Name end Region,
				Publisher.DrugListObj_id,
				O.Org_id,
				L.Lpu_id,
				case 
					when isnull(O.Org_id, L.Lpu_id) is not null then isnull(O.Org_Nick, L.Lpu_Nick)+isnull(' / '+LS.LpuSection_Name, '')
					when PublisherRegion.KLArea_id is not null then PublisherRegion.KLArea_FullName
					else Country.KLCountry_Name
				end as Publisher
			-- end select
			from
			-- from
				v_DrugList DL with(nolock)
				left join v_DrugListType DLT with(nolock) on DLT.DrugListType_id = DL.DrugListType_id
				left join v_DrugListObj Publisher with(nolock) on Publisher.DrugListObj_id = DL.DrugListObj_id
				left join v_UslugaComplexTariff UCT with(nolock) on UCT.UslugaComplexTariff_id = Publisher.UslugaComplexTariff_id
				left join v_PayType PT with(nolock) on PT.PayType_id = UCT.PayType_id
				left join v_DocNormative DN with(nolock) on DN.DocNormative_id = DL.DocNormative_id
				left join v_KLCountry Country with(nolock) on Country.KLCountry_id = DL.KLCountry_id
				left join v_KLArea Region with(nolock) on Region.KLArea_id = DL.Region_id
				left join v_Org O with(nolock) on O.Org_id = Publisher.Org_id
				left join v_Lpu L with(nolock) on L.Lpu_id = Publisher.Lpu_id
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = Publisher.LpuSection_id
				left join v_KLArea PublisherRegion with(nolock) on PublisherRegion.KLArea_id = Publisher.Region_id
			-- end from
			where
			-- where
				{$filters_str}
			-- end where
			order by
			-- order by
				DL.DrugList_begDate
			-- end order by
		";

		//echo getDebugSQL($query, $params);exit;

		$count_result = $this->queryResult(getCountSQLPH($query), $params);
		if (!is_array($count_result)) {
			return false;
		}

		$result = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		if (!is_array($result)) {
			return false;
		}

		return array(
			'data' => $result,
			'totalCount' => $count_result[0]['cnt']
		);
	}

	/**
	 * @param $data
	 * @return array
	 */
	function loadDrugListUsedGrid($data) {
		$params = array();
		$filters = array('1=1');

		if (!empty($data['DrugList_id'])) {
			$filters[] = "DLU.DrugList_id = :DrugList_id";
			$params['DrugList_id'] = $data['DrugList_id'];
		}

		$filters_str = implode(" and ", $filters);

		$query = "
			select
				DLU.DrugListUsed_id,
				DLU.DrugList_id,
				DLO.DrugListObj_id,
				DLO.DrugListObj_Name,
				L.Lpu_id,
				O.Org_id,
				(
					coalesce(O.Org_Nick, L.Lpu_Nick, '')+
					coalesce(' / '+LB.LpuBuilding_Nick, LB.LpuBuilding_Name, '')+
					isnull(' / '+LS.LpuSection_Name, '')+
					isnull(' / '+UC.UslugaComplex_Code+' '+UC.UslugaComplex_Name+', '+PT.PayType_Name+', '+MAG.MesAgeGroup_Name, '')+
					isnull(' / '+S.Storage_Code+' '+S.Storage_Name, '')
				) as DrugListObj_Nick,
				ETS.EmergencyTeamSpec_id,
				LSP.LpuSectionProfile_id,
				isnull(ETS.EmergencyTeamSpec_Name, LSP.LpuSectionProfile_Name) as DrugListObj_Profile,
				Region.KLArea_FullName+', '+Country.KLCountry_Name as Region
			from
				v_DrugListUsed DLU with(nolock)
				inner join v_DrugListObj DLO with(nolock) on DLO.DrugListObj_id = DLU.DrugListObj_id
				left join v_Org O with(nolock) on O.Org_id = DLO.Org_id
				left join v_Lpu L with(nolock) on L.Lpu_id = DLO.Lpu_id
				left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = DLO.LpuBuilding_id
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = DLO.LpuSection_id
				left join v_LpuSectionProfile LSP with(nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
				left join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = DLO.UslugaComplex_id
				left join v_UslugaComplexTariff UCT with(nolock) on UCT.UslugaComplexTariff_id = DLO.UslugaComplexTariff_id
				left join v_PayType PT with(nolock) on PT.PayType_id = UCT.PayType_id
				left join v_MesAgeGroup MAG with(nolock) on MAG.MesAgeGroup_id = UCT.MesAgeGroup_id
				left join v_Storage S with(nolock) on S.Storage_id = DLO.Storage_id
				left join v_EmergencyTeamSpec ETS with(nolock) on ETS.EmergencyTeamSpec_id = DLO.EmergencyTeamSpec_id
				left join v_KLArea Region with(nolock) on Region.KLArea_id = DLO.Region_id
				left join v_KLCountry Country with(nolock) on Country.KLCountry_id = Region.KLCountry_id
			where
				{$filters_str}
			order by
				DLU.DrugListUsed_insDT
		";

		$result = $this->queryResult($query, $params);

		return array(
			'data' => $result,
		);
	}

	/**
	 * @param array $data
	 * @return array|bool
	 */
	function loadDrugListStrGrid($data) {
		$params = array();
		$filters = array('1=1');

		if (!empty($data['DrugList_id'])) {
			$filters[] = "DLS.DrugList_id = :DrugList_id";
			$params['DrugList_id'] = $data['DrugList_id'];
		}
		if (!empty($data['DrugListGroup_id'])) {
			$filters[] = "DLS.DrugListGroup_id = :DrugListGroup_id";
			$params['DrugListGroup_id'] = $data['DrugListGroup_id'];
		}
		if (!empty($data['ClsATC_id'])) {
			$filters[] = "DLS.ClsATC_id = :ClsATC_id";
			$params['ClsATC_id'] = $data['ClsATC_id'];
		}
		if (!empty($data['DrugListStr_Name'])) {
			$filters[] = "DrugListStrName.Value like :DrugListStr_Name+'%'";
			$params['DrugListStr_Name'] = $data['DrugListStr_Name'];
		}

		$filters_str = implode(" and ", $filters);

		$query = "
			select
			-- select
				DLS.DrugListStr_id,
				DLS.DrugList_id,
				DrugListStrName.Value as DrugListStr_Name,
				DLS.DrugListStr_Num,
				DLG.DrugListGroup_id,
				DLG.DrugListGroup_Name,
				CDF.Clsdrugforms_id,
				CDF.NAME as Clsdrugforms_Name,
				ATC.NAME as ClsATC_Name,
				cast(DLS.DrugListStr_Dose as varchar)+isnull(' '+DoseGU.GoodsUnit_Nick, '') as DrugListStr_Dose,
				cast(DLS.DrugListStr_Num as varchar)+isnull(' '+NumGU.GoodsUnit_Nick, '') as DrugListStr_Num
			-- end select
			from
			-- from
				v_DrugListStr DLS with(nolock)
				left join v_DrugListGroup DLG with(nolock) on DLG.DrugListGroup_id = DLS.DrugListGroup_id
				left join rls.v_Drug D with(nolock) on D.Drug_id = DLS.Drug_id
				left join rls.v_DrugComplexMnn DCM with(nolock) on DCM.DrugComplexMnn_id = DLS.DrugComplexMnn_id
				left join rls.v_Actmatters A with(nolock) on A.Actmatters_id = DLS.Actmatters_id
				left join rls.v_DrugNonpropNames DNN with(nolock) on DNN.DrugNonpropNames_id = DLS.DrugNonpropNames_id
				left join rls.v_Tradenames T with(nolock) on T.Tradenames_id = DLS.Tradenames_id
				outer apply (
					select top 1 coalesce(
						D.Drug_Name,
						coalesce(DCM.DrugComplexMnn_RusName, A.RUSNAME, DNN.DrugNonpropNames_Name)+isnull(' / '+T.NAME, ''),
						T.NAME,
						DLS.DrugListStr_Name
					) as Value
				) DrugListStrName
				left join rls.v_Clsdrugforms CDF with(nolock) on CDF.Clsdrugforms_id = DLS.Clsdrugforms_id
				left join rls.v_PREP_ATC PA with(nolock) on PA.PREPID = D.DrugPrep_id
				left join rls.v_ClsATC ATC with(nolock) on ATC.ClsATC_id = isnull(DLS.ClsATC_id, PA.UNIQID)
				left join v_GoodsUnit DoseGU with(nolock) on DoseGU.GoodsUnit_id = DLS.GoodsUnit_did
				left join v_GoodsUnit NumGU with(nolock) on NumGU.GoodsUnit_id = DLS.GoodsUnit_nid
			-- end from
			where
			-- where
				{$filters_str}
			-- end where
			order by
			-- order by
				DLS.DrugListStr_id
			-- end order by
		";
		//echo getDebugSQL($query, $params);exit;
		$count_result = $this->queryResult(getCountSQLPH($query), $params);
		if (!is_array($count_result)) {
			return false;
		}

		$result = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		if (!is_array($result)) {
			return false;
		}

		return array(
			'data' => $result,
			'totalCount' => $count_result[0]['cnt']
		);
	}

	/**
	 * @param $data
	 * @return array|false
	 */
	function loadDrugListObjList($data) {
		$params = array();
		$filters = array('1=1');

		if (!empty($data['query'])) {
			$filters[] = "DLO.DrugListObj_Name like :DrugListObj_Name+'%'";
			$params['DrugListObj_Name'] = $data['query'];
		}
		if (!empty($data['DrugListObj_id'])) {
			$filters[] = "DLO.DrugListObj_id = :DrugListObj_id";
			$params['DrugListObj_id'] = $data['DrugListObj_id'];
		} else {
			if (!empty($data['Lpu_oid'])) {
				$filters[] = "DLO.Lpu_id = :Lpu_oid";
				$params['Lpu_oid'] = $data['Lpu_oid'];
			}
			if (!empty($data['Org_id'])) {
				$filters[] = "isnull(L.Org_id, DLO.Org_id) = :Org_id";
				$params['Org_id'] = $data['Org_id'];
			}
			if (!empty($data['isPublisher']) && $data['isPublisher']) {
				$filters[] = "DLO.Region_id is not null and isnull(DLO.Lpu_id, DLO.Org_id) is not null";
				$params['DrugListObj_id'] = $data['DrugListObj_id'];
			}
		}

		$filters_str = implode(" and ", $filters);

		$query = "
			select top 100
				DLO.DrugListObj_id,
				DLO.DrugListObj_Name,
				(
					isnull(O.Org_Nick, '') +
					isnull(L.Lpu_Nick, '') +
					isnull(' / '+LB.LpuBuilding_Nick, '') +
					isnull(' / '+LS.LpuSection_Name, '')
				) as DrugListObj_PublisherNick
			from
				v_DrugListObj DLO with(nolock)
				left join v_Lpu L with(nolock) on L.Lpu_id = DLO.Lpu_id
				left join v_Org O with(nolock) on L.Org_id = DLO.Org_id
				left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = DLO.LpuBuilding_id
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = DLO.LpuSection_id
			where
				{$filters_str}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function loadDrugListForm($data) {
		$params = array('DrugList_id' => $data['DrugList_id']);
		$query = "
			select top 1
				DL.DrugList_id,
				convert(varchar(10), DL.DrugList_begDate, 104) as DrugList_begDate,
				convert(varchar(10), DL.DrugList_endDate, 104) as DrugList_endDate,
				DL.DrugList_Name,
				DL.DrugListType_id,
				DL.DocNormative_id,
				DL.DrugListObj_id,
				DL.KLCountry_id,
				DL.Region_id
			from
				v_DrugList DL with(nolock)
			where
				DL.DrugList_id = :DrugList_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function loadDrugListObjForm($data) {
		$params = array('DrugListObj_id' => $data['DrugListObj_id']);
		$query = "
			select top 1
				DLO.DrugListObj_id,
				DLO.DrugListObj_Name,
				isnull(L.Org_id, DLO.Org_id) as Org_id,
				DLO.LpuBuilding_id,
				DLO.LpuSection_id,
				DLO.EmergencyTeamSpec_id,
				DLO.UslugaComplex_id,
				DLO.UslugaComplexTariff_id,
				DLO.Storage_id,
				DLO.Region_id
			from
				v_DrugListObj DLO with(nolock)
				left join v_Lpu L with(nolock) on L.Lpu_id = DLO.Lpu_id
			where
				DLO.DrugListObj_id = :DrugListObj_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function loadDrugListUsedForm($data) {
		$params = array('DrugListUsed_id' => $data['DrugListUsed_id']);
		$query = "
			select top 1
				DLU.DrugListUsed_id,
				DLU.DrugList_id,
				DLO.DrugListObj_id,
				DLO.DrugListObj_Name,
				isnull(L.Org_id, DLO.Org_id) as Org_id,
				DLO.LpuBuilding_id,
				DLO.LpuSection_id,
				DLO.EmergencyTeamSpec_id,
				DLO.UslugaComplex_id,
				DLO.UslugaComplexTariff_id,
				DLO.Storage_id,
				DLO.Region_id
			from
				v_DrugListUsed DLU with(nolock)
				inner join v_DrugListObj DLO with(nolock) on DLO.DrugListObj_id = DLU.DrugListObj_id
				left join v_Lpu L with(nolock) on L.Lpu_id = DLO.Lpu_id
			where
				DLU.DrugListUsed_id = :DrugListUsed_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных для редактирвоания медикамента в перечне
	 * @param array $data
	 * @return array
	 */
	function loadDrugListStrForm($data) {
		$params = array('DrugListStr_id' => $data['DrugListStr_id']);
		$query = "
			select top 1
				DLS.DrugListStr_id,
				DLS.DrugList_id,
				DLS.DrugListStr_Name,
				DLS.DrugListGroup_id,
				DLS.Drug_id,
				DLS.DrugComplexMnn_id,
				DLS.Actmatters_id,
				DLS.DrugNonpropNames_id,
				DLS.Tradenames_id,
				DLS.Clsdrugforms_id,
				DLS.DrugListStr_Comment,
				DLS.DrugListStr_Dose,
				DLS.GoodsUnit_did,
				DLS.DrugListStr_Num,
				DLS.GoodsUnit_nid
			from
				v_DrugListStr DLS with(nolock)
			where
				DLS.DrugListStr_id = :DrugListStr_id
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Сохранение перечня медикаментов
	 * @param array $data
	 * @return array
	 */
	function saveDrugList($data) {
		$params = array(
			'DrugList_id' => !empty($data['DrugList_id'])?$data['DrugList_id']:null,
			'DrugList_begDate' => $data['DrugList_begDate'],
			'DrugList_endDate' => !empty($data['DrugList_endDate'])?$data['DrugList_endDate']:null,
			'DrugList_Name' => $data['DrugList_Name'],
			'DrugListType_id' => $data['DrugListType_id'],
			'DocNormative_id' => !empty($data['DocNormative_id'])?$data['DocNormative_id']:null,
			'DrugListObj_id' => !empty($data['DrugListObj_id'])?$data['DrugListObj_id']:null,
			'KLCountry_id' => $data['KLCountry_id'],
			'Region_id' => !empty($data['Region_id'])?$data['Region_id']:null,
			'pmUser_id' => $data['pmUser_id'],
		);
		if (empty($params['DrugList_id'])) {
			$procedure = "p_DrugList_ins";
		} else {
			$procedure = "p_DrugList_upd";
		}
		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint = :DrugList_id;
			exec {$procedure}
				@DrugList_id = @Res output,
				@DrugList_begDate = :DrugList_begDate,
				@DrugList_endDate = :DrugList_endDate,
				@DrugList_Name = :DrugList_Name,
				@DrugListType_id = :DrugListType_id,
				@DocNormative_id = :DocNormative_id,
				@DrugListObj_id = :DrugListObj_id,
				@KLCountry_id = :KLCountry_id,
				@Region_id = :Region_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Res as DrugList_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		//echo getDebugSQL($query, $params);exit;
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при сохранении перечня медикаментов');
		}
		return $response;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function updateDataLpuOrg(&$data) {
		if (empty($data['Lpu_oid']) && !empty($data['Org_id'])) {
			$data['Lpu_oid'] = $this->getFirstResultFromQuery("
					select top 1 Lpu_id from v_Lpu_all with(nolock) where Org_id = :Org_id
				", $data, true);
			if ($data['Lpu_oid'] === false) {
				return $this->createError('','Ошибка при поиске идентификатора МО');
			}
		}
		if (!empty($data['Lpu_oid'])) {
			$data['Org_id'] = null;
		}
		return array(array('success' => true));
	}

	/**
	 * Сохранение объекта, использующего перечни медикаментов
	 * @param array $data
	 * @return array
	 */
	function saveDrugListObj($data) {
		$resp = $this->updateDataLpuOrg($data);
		if (!$this->isSuccessful($data)) {
			return $resp;
		}

		$params = array(
			'DrugListObj_id' => !empty($data['DrugListObj_id'])?$data['DrugListObj_id']:null,
			'DrugListObj_Name' => $data['DrugListObj_Name'],
			'Org_id' => !empty($data['Org_id'])?$data['Org_id']:null,
			'Lpu_id' => !empty($data['Lpu_oid'])?$data['Lpu_oid']:null,
			'LpuBuilding_id' => !empty($data['LpuBuilding_id'])?$data['LpuBuilding_id']:null,
			'LpuSection_id' => !empty($data['LpuSection_id'])?$data['LpuSection_id']:null,
			'EmergencyTeamSpec_id' => !empty($data['EmergencyTeamSpec_id'])?$data['EmergencyTeamSpec_id']:null,
			'UslugaComplex_id' => !empty($data['UslugaComplex_id'])?$data['UslugaComplex_id']:null,
			'UslugaComplexTariff_id' => !empty($data['UslugaComplexTariff_id'])?$data['UslugaComplexTariff_id']:null,
			'Storage_id' => !empty($data['Storage_id'])?$data['Storage_id']:null,
			'Region_id' => !empty($data['Region_id'])?$data['Region_id']:getRegionNumber(),
			'pmUser_id' => $data['pmUser_id'],
		);

		if (empty($params['DrugListObj_id'])) {
			$procedure = "p_DrugListObj_ins";
		} else {
			$procedure = "p_DrugListObj_upd";
		}

		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint = :DrugListObj_id;
			exec {$procedure}
				@DrugListObj_id = @Res output,
				@DrugListObj_Name = :DrugListObj_Name,
				@Org_id = :Org_id,
				@Lpu_id = :Lpu_id,
				@LpuBuilding_id = :LpuBuilding_id,
				@LpuSection_id = :LpuSection_id,
				@EmergencyTeamSpec_id = :EmergencyTeamSpec_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@UslugaComplexTariff_id = :UslugaComplexTariff_id,
				@Storage_id = :Storage_id,
				@Region_id = :Region_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Res as DrugListObj_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохрании объекта, использующего перечни медикаментов');
		}
		return $resp;
	}

	/**
	 * Сохранение использование перечня медикаментов
	 * @param array $data
	 * @return array
	 */
	function saveDrugListUsed($data) {
		$params = array(
			'DrugListUsed_id' => !empty($data['DrugListUsed_id'])?$data['DrugListUsed_id']:null,
			'DrugList_id' => $data['DrugList_id'],
			'DrugListObj_id' => $data['DrugListObj_id'],
			'pmUser_id' => $data['pmUser_id'],
		);

		if (empty($params['DrugListUsed_id'])) {
			$procedure = "p_DrugListUsed_ins";
		} else {
			$procedure = "p_DrugListUsed_upd";
		}

		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint = :DrugListUsed_id;
			exec {$procedure}
				@DrugListUsed_id = @Res output,
				@DrugList_id = :DrugList_id,
				@DrugListObj_id = :DrugListObj_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Res as DrugListUsed_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;	
		";
		//echo getDebugSQL($query, $params);exit;
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при сохрании использования перечня медикаментов');
		}
		return $response;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function getDrugListObjId($data) {
		$resp = $this->updateDataLpuOrg($data);
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		$params = array(
			'DrugListObj_Name' => !empty($data['DrugListObj_Name'])?$data['DrugListObj_Name']:null,
			'Org_id' => !empty($data['Org_id'])?$data['Org_id']:null,
			'Lpu_id' => !empty($data['Lpu_oid'])?$data['Lpu_oid']:null,
			'LpuBuilding_id' => !empty($data['LpuBuilding_id'])?$data['LpuBuilding_id']:null,
			'LpuSection_id' => !empty($data['LpuSection_id'])?$data['LpuSection_id']:null,
			'EmergencyTeamSpec_id' => !empty($data['EmergencyTeamSpec_id'])?$data['EmergencyTeamSpec_id']:null,
			'UslugaComplex_id' => !empty($data['UslugaComplex_id'])?$data['UslugaComplex_id']:null,
			'UslugaComplexTariff_id' => !empty($data['UslugaComplexTariff_id'])?$data['UslugaComplexTariff_id']:null,
			'Storage_id' => !empty($data['Storage_id'])?$data['Storage_id']:null,
			'Region_id' => !empty($data['Region_id'])?$data['Region_id']:getRegionNumber(),
		);

		$filters = array();
		foreach($params as $key => $value) {
			if (isset($value)) {
				$filters[] = "DLO.{$key} = :{$key}";
			} else {
				$filters[] = "DLO.{$key} is null";
			}
		}

		$filters_str = implode(" and ", $filters);

		$query = "
			select top 1 DLO.DrugListObj_id
			from v_DrugListObj DLO with(nolock)
			where {$filters_str}
			order by DrugListObj_insDT desc
		";

		$id = $this->getFirstResultFromQuery($query, $params, true);
		if ($id === false) {
			return $this->createError('','Ошибка при поиске идентификатора объекта, использующего перечни медикаментов');
		}
		return array(array(
			'success' => true,
			'DrugListObj_id' => $id,
		));
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function saveDrugListObjOrUsed($data) {
		$response = array(
			'success' => true,
		);

		try {
			$this->beginTransaction();

			if (empty($data['DrugList_id']) || empty($data['DrugListObj_id'])) {
				//Поиск идентификатора объекта по полному совпадения параметров
				$resp = $this->getDrugListObjId($data);
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}
				if (empty($resp[0]['DrugListObj_id'])) {
					//Если идентификатор не найден, то создание нового объекта
					$resp = $this->saveDrugListObj($data);
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg']);
					}
				}
				$response['DrugListObj_id'] = $data['DrugListObj_id'] = $resp[0]['DrugListObj_id'];
			}

			if (!empty($data['DrugList_id'])) {
				if (empty($data['DrugListUsed_id'])) {
					$data['DrugListUsed_id'] = $this->getFirstResultFromQuery("
						select top 1 DrugListUsed_id
						from v_DrugListUsed with(nolock)
						where DrugList_id = :DrugList_id and DrugListObj_id = :DrugListObj_id
					", $data, true);
					if ($data['DrugListUsed_id'] === false) {
						throw new Exception('Ошибка при проверке использования перечня медикаментов');
					}
				} else {
					$prevDrugListObj_id = $this->getFirstResultFromQuery("
						select top 1 DrugListObj_id
						from v_DrugListUsed with(nolock)
						where DrugListUsed_id = :DrugListUsed_id
					", $data, true);
					if ($prevDrugListObj_id === false) {
						throw new Exception('Ошибка при получнии данных об использовании перечня медикаментов');
					}
					if ($prevDrugListObj_id && $prevDrugListObj_id != $data['DrugListObj_id']) {
						$resp = $this->deleteDrugListObj(array(
							'DrugListObj_id' => $prevDrugListObj_id
						));
						if (!$this->isSuccessful($resp) && $resp[0]['Error_Code'] != 101) {
							throw new Exception($resp[0]['Error_Msg']);
						}
					}
				}

				$resp = $this->saveDrugListUsed(array(
					'DrugListUsed_id' => $data['DrugListUsed_id'],
					'DrugList_id' => $data['DrugList_id'],
					'DrugListObj_id' => $data['DrugListObj_id'],
					'pmUser_id' => $data['pmUser_id'],
				));
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}
				$response['DrugListUsed_id'] = $resp[0]['DrugListUsed_id'];
			}

			$this->commitTransaction();
		} catch(Exception $e) {
			$this->rollbackTransaction();
			return $this->createError('', $e->getMessage());
		}

		return $response;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function checkDoubleDrugListStr($data) {
		$params = array(
			'DrugListStr_id' => !empty($data['DrugListStr_id'])?$data['DrugListStr_id']:null,
			'DrugList_id' => $data['DrugList_id'],
			'DrugListStr_Name' => $data['DrugListStr_Name'],
			'DrugListGroup_id' => !empty($data['DrugListGroup_id'])?$data['DrugListGroup_id']:null,
			'Drug_id' => !empty($data['Drug_id'])?$data['Drug_id']:null,
			'DrugComplexMnn_id' => !empty($data['DrugComplexMnn_id'])?$data['DrugComplexMnn_id']:null,
			'Actmatters_id' => !empty($data['Actmatters_id'])?$data['Actmatters_id']:null,
			'DrugNonpropNames_id' => !empty($data['DrugNonpropNames_id'])?$data['DrugNonpropNames_id']:null,
			'Tradenames_id' => !empty($data['Tradenames_id'])?$data['Tradenames_id']:null,
			'Clsdrugforms_id' => !empty($data['Clsdrugforms_id'])?$data['Clsdrugforms_id']:null,
			'DrugListStr_Comment' => !empty($data['DrugListStr_Comment'])?$data['DrugListStr_Comment']:null,
			'DrugListStr_Dose' => !empty($data['DrugListStr_Dose'])?$data['DrugListStr_Dose']:null,
			'GoodsUnit_did' => !empty($data['GoodsUnit_did'])?$data['GoodsUnit_did']:null,
			'DrugListStr_Num' => !empty($data['DrugListStr_Num'])?$data['DrugListStr_Num']:null,
			'GoodsUnit_nid' => !empty($data['GoodsUnit_nid'])?$data['GoodsUnit_nid']:null,
		);
		$query = "
			select top 1 count(*) as cnt
			from v_DrugListStr DLS with(nolock)
			where DLS.DrugList_id = :DrugList_id
			and isnull(DLS.DrugListStr_id,0) <> isnull(:DrugListStr_id,0)
			and DLS.DrugList_id = :DrugList_id
			and DLS.DrugListStr_Name = :DrugListStr_Name
			and isnull(DLS.DrugListGroup_id,0) = isnull(:DrugListGroup_id,0)
			and isnull(DLS.Drug_id,0) = isnull(:Drug_id,0)
			and isnull(DLS.DrugComplexMnn_id,0) = isnull(:DrugComplexMnn_id,0)
			and isnull(DLS.Actmatters_id,0) = isnull(:Actmatters_id,0)
			and isnull(DLS.DrugNonpropNames_id,0) = isnull(:DrugNonpropNames_id,0)
			and isnull(DLS.Tradenames_id,0) = isnull(:Tradenames_id,0)
			and isnull(DLS.Clsdrugforms_id,0) = isnull(:Clsdrugforms_id,0)
			and isnull(DLS.DrugListStr_Comment,'') = isnull(:DrugListStr_Comment,'')
			and isnull(DLS.DrugListStr_Dose,0) = isnull(:DrugListStr_Dose,0)
			and isnull(DLS.GoodsUnit_did,0) = isnull(:GoodsUnit_did,0)
			and isnull(DLS.DrugListStr_Num,0) = isnull(:DrugListStr_Num,0)
			and isnull(DLS.GoodsUnit_nid,0) = isnull(:GoodsUnit_nid,0)
		";
		$cnt = $this->getFirstResultFromQuery($query, $params);
		if ($cnt === false) {
			return $this->createError('','Ошибка при проверке дублирования медикаментов в перечне');
		}
		if ($cnt > 0) {
			return $this->createError('','Такой медикамент уже есть в списке. Сохранение невозможно.');
		}
		return array(array('success' => true));
	}

	/**
	 * Сохранение медикамента в перечне
	 * @param array $data
	 * @return array
	 */
	function saveDrugListStr($data) {
		$resp = $this->checkDoubleDrugListStr($data);
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}
		$params = array(
			'DrugListStr_id' => !empty($data['DrugListStr_id'])?$data['DrugListStr_id']:null,
			'DrugList_id' => $data['DrugList_id'],
			'DrugListStr_Name' => $data['DrugListStr_Name'],
			'DrugListGroup_id' => !empty($data['DrugListGroup_id'])?$data['DrugListGroup_id']:null,
			'Drug_id' => !empty($data['Drug_id'])?$data['Drug_id']:null,
			'DrugComplexMnn_id' => !empty($data['DrugComplexMnn_id'])?$data['DrugComplexMnn_id']:null,
			'Actmatters_id' => !empty($data['Actmatters_id'])?$data['Actmatters_id']:null,
			'DrugNonpropNames_id' => !empty($data['DrugNonpropNames_id'])?$data['DrugNonpropNames_id']:null,
			'Tradenames_id' => !empty($data['Tradenames_id'])?$data['Tradenames_id']:null,
			'Clsdrugforms_id' => !empty($data['Clsdrugforms_id'])?$data['Clsdrugforms_id']:null,
			'DrugListStr_Comment' => !empty($data['DrugListStr_Comment'])?$data['DrugListStr_Comment']:null,
			'DrugListStr_Dose' => !empty($data['DrugListStr_Dose'])?$data['DrugListStr_Dose']:null,
			'GoodsUnit_did' => !empty($data['GoodsUnit_did'])?$data['GoodsUnit_did']:null,
			'DrugListStr_Num' => !empty($data['DrugListStr_Num'])?$data['DrugListStr_Num']:null,
			'GoodsUnit_nid' => !empty($data['GoodsUnit_nid'])?$data['GoodsUnit_nid']:null,
			'pmUser_id' => $data['pmUser_id'],
		);
		if (empty($params['DrugListStr_id'])) {
			$procedure = 'p_DrugListStr_ins';
		} else {
			$procedure = 'p_DrugListStr_upd';
		}
		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint = :DrugListStr_id;
			exec {$procedure}
				@DrugListStr_id = @Res output,
				@DrugList_id = :DrugList_id,
				@DrugListStr_Name = :DrugListStr_Name,
				@DrugListGroup_id = :DrugListGroup_id,
				@Drug_id = :Drug_id,
				@DrugComplexMnn_id = :DrugComplexMnn_id,
				@Actmatters_id = :Actmatters_id,
				@DrugNonpropNames_id = :DrugNonpropNames_id,
				@Tradenames_id = :Tradenames_id,
				@Clsdrugforms_id = :Clsdrugforms_id,
				@DrugListStr_Comment = :DrugListStr_Comment,
				@DrugListStr_Dose = :DrugListStr_Dose,
				@GoodsUnit_did = :GoodsUnit_did,
				@DrugListStr_Num = :DrugListStr_Num,
				@GoodsUnit_nid = :GoodsUnit_nid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Res as DrugListStr_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;	
		";
		//echo getDebugSQL($query, $params);exit;
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при сохрании медикамента в перечне');
		}
		return $response;
	}

	/**
	 * Удаление перечня медикаментов
	 * @param array $data
	 * @return array
	 */
	function deleteDrugList($data) {
		$params = array('DrugList_id' => $data['DrugList_id']);

		$DrugListStr_ids = $this->queryList("
			select DrugListStr_id from v_DrugListStr with(nolock) where DrugList_id = :DrugList_id
		", $params);
		if (!is_array($DrugListStr_ids)) {
			return $this->createError('','Ошибка при получнии списка медикаментов перечня');
		}

		$DrugListUsed_ids = $this->queryList("
			select DrugListUsed_id from v_DrugListUsed with(nolock) where DrugList_id = :DrugList_id
		", $params);
		if (!is_array($DrugListStr_ids)) {
			return $this->createError('','Ошибка при получнии списка использований перечня медикаментов');
		}

		$this->beginTransaction();

		try {
			$this->isAllowTransaction = false;

			$this->db->query("
				update DrugList with(rowlock)
				set DrugListObj_id = null
				where DrugList_id = :DrugList_id
			", $params);

			foreach($DrugListStr_ids as $DrugListStr_id) {
				$resp = $this->deleteDrugListStr(array(
					'DrugListStr_id' => $DrugListStr_id
				));
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Code']);
				}
			}

			foreach($DrugListUsed_ids as $DrugListUsed_id) {
				$resp = $this->deleteDrugListUsed(array(
					'DrugListUsed_id' => $DrugListUsed_id
				));
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Code']);
				}
			}

			$query = "
				declare
					@Error_Message varchar(4000),
					@Error_Code bigint
				exec p_DrugList_del
					@DrugList_id = :DrugList_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @Error_Code as Error_Code, @Error_Message as Error_Msg;	
			";
			$response = $this->queryResult($query, $params);
			if (!is_array($response)) {
				throw new Exception('Ошибка при удалении перечня медикаментов');
			}

			$this->isAllowTransaction = true;
			$this->rollbackTransaction();
		} catch (Exception $e) {
			$this->isAllowTransaction = true;
			$this->rollbackTransaction();
			return $this->createError('', $e->getMessage());
		}

		return $response;
	}

	/**
	 * Удаление объекта, использующего перечень
	 * @param array $data
	 * @return array
	 */
	function deleteDrugListObj($data) {
		$params = array('DrugListObj_id' => $data['DrugListObj_id']);

		$cnt = $this->getFirstResultFromQuery("
			select (--Издатель
				select top 1 count(*) as cnt from v_DrugList with(nolock) where DrugListObj_id = :DrugListObj_id
			) + (--Использование перечня
				select top 1 count(*) as cnt from v_DrugListUsed with(nolock) where DrugListObj_id = :DrugListObj_id
			) as cnt
		", $params);
		if ($cnt === false) {
			return $this->createError('','Ошибка при проверке связей объектов с перечнями');
		}
		if ($cnt > 0) {
			return $this->createError(101, 'Невозможно удалить объект, поскольку у него существуют связи с перечнями медикаментов');
		}

		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint
			exec p_DrugListObj_del
				@DrugListObj_id = :DrugListObj_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;	
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при удалении объекта, использующего перечень медикаментов');
		}
		return $response;
	}

	/**
	 * Удаление использования перечня
	 * @param array $data
	 * @return array
	 */
	function deleteDrugListUsed($data) {
		$params = array('DrugListUsed_id' => $data['DrugListUsed_id']);

		$DrugListObj_id = $this->getFirstResultFromQuery("
			select top 1 DrugListObj_id
			from v_DrugListUsed with(nolock)
			where DrugListUsed_id = :DrugListUsed_id
		", $params);
		if (empty($DrugListObj_id)) {
			return $this->createError('','Ошибка при получении данных об использовании перечня медикаментов');
		}

		$this->beginTransaction();

		try {
			$query = "
				declare
					@Error_Message varchar(4000),
					@Error_Code bigint
				exec p_DrugListUsed_del
					@DrugListUsed_id = :DrugListUsed_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @Error_Code as Error_Code, @Error_Message as Error_Msg;	
			";
			$response = $this->queryResult($query, $params);
			if (!is_array($response)) {
				throw new Exception('Ошибка при удалении использования перечня медикаментов');
			}

			$resp = $this->deleteDrugListObj(array(
				'DrugListObj_id' => $DrugListObj_id
			));
			if (!$this->isSuccessful($resp) && $resp[0]['Error_Code'] != 101) {
				throw new Exception($resp[0]['Error_Msg']);
			}

			$this->commitTransaction();
		} catch (Exception $e) {
			$this->rollbackTransaction();
			return $this->createError('', $e->getMessage());
		}

		return $response;
	}

	/**
	 * Удаления медикамента из перечня
	 * @param array $data
	 * @return array
	 */
	function deleteDrugListStr($data) {
		$params = array('DrugListStr_id' => $data['DrugListStr_id']);
		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint
			exec p_DrugListStr_del
				@DrugListStr_id = :DrugListStr_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;	
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при удалении медикамента из перечня');
		}
		return $response;
	}
}