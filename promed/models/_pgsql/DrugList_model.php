<?php
defined("BASEPATH") or die ("No direct script access allowed");
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
 * @property CI_DB_driver $db
 */

class DrugList_model extends SwPgModel
{
	public $dateTimeForm104 = "DD.MM.YYYY";

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugListGrid($data)
	{
		$params = [];
		$filters = [];
		$usedFilters = [];

		if (isset($data["DrugListRange"]) && !empty($data["DrugListRange"][0])) {
			$filters[] = "DL.DrugList_begDate between :DrugList_begDate and :DrugList_endDate";
			$filters[] = "(DL.DrugList_endDate is null or DL.DrugList_endDate between :DrugList_begDate and :DrugList_endDate)";
			$params["DrugList_begDate"] = $data["DrugListRange"][0];
			$params["DrugList_endDate"] = $data["DrugListRange"][1];
		}
		if (!empty($data["DrugList_Name"])) {
			$filters[] = "DL.DrugList_Name = :DrugList_Name";
			$params["DrugList_Name"] = $data["DrugList_Name"];
		}
		if (!empty($data["PayType_id"])) {
			$filters[] = "PT.PayType_id = :PayType_id";
			$params["PayType_id"] = $data["PayType_id"];
		}
		if (!empty($data["DrugListType_id"])) {
			$filters[] = "DL.DrugListType_id = :DrugListType_id";
			$params["DrugListType_id"] = $data["DrugListType_id"];
		}
		if (!empty($data["DrugListObj_id"])) {
			$filters[] = "DL.DrugListObj_id = :DrugListObj_id";
			$params["DrugListObj_id"] = $data["DrugListObj_id"];
		}
		if (!empty($data["Org_id"])) {
			$usedFilters[] = "coalesce(L.Org_id, DLO.Org_id) = :Org_id";
			$params["Org_id"] = $data["Org_id"];
		}
		if (!empty($data["LpuBuilding_id"])) {
			$usedFilters[] = "DLO.LpuBuilding_id = :LpuBuilding_id";
			$params["LpuBuilding_id"] = $data["LpuBuilding_id"];
		}
		if (!empty($data["LpuSection_id"])) {
			$usedFilters[] = "DLO.LpuSection_id = :LpuSection_id";
			$params["LpuSection_id"] = $data["LpuSection_id"];
		}
		if (!empty($data["LpuSection_id"])) {
			$usedFilters[] = "DLO.LpuSection_id = :LpuSection_id";
			$params["LpuSection_id"] = $data["LpuSection_id"];
		}
		if (!empty($data["DrugList_Profile"])) {
			$usedFilters[] = "(
				LS.LpuSectionProfile_Name ilike :DrugList_Profile||'%'
				or ETS.EmergencyTeamSpec_Name ilike :DrugList_Profile||'%'
			)";
			$params["DrugList_Profile"] = $data["DrugList_Profile"];
		}
		if (!empty($data["UslugaComplex_id"])) {
			$usedFilters[] = "DLO.UslugaComplex_id = :UslugaComplex_id";
			$params["UslugaComplex_id"] = $data["UslugaComplex_id"];
		}
		if (!empty($data["Storage_id"])) {
			$usedFilters[] = "DLO.Storage_id = :Storage_id";
			$params["Storage_id"] = $data["Storage_id"];
		}
		if (count($usedFilters) > 0) {
			$usedFilters[] = "DLU.DrugList_id = DL.DrugList_id";
			$usedFilters_str = implode(" and ", $usedFilters);
			$filters[] = "exists(
				select 1
				from
					v_DrugListUsed DLU
					inner join v_DrugListObj DLO on DLO.DrugListObj_id = DLU.DrugListObj_id
					left join v_Lpu L on L.Lpu_id = DLO.Lpu_id
					left join v_LpuSection LS on LS.LpuSection_id = DLO.LpuSection_id
					left join v_EmergencyTeamSpec ETS on ETS.EmergencyTeamSpec_id = DLO.EmergencyTeamSpec_id
				where {$usedFilters_str}
			)";
		}
		$whereString = (count($filters) != 0) ? implode(" and ", $filters) : "";
		if ($whereString != "") {
			$whereString = "
				where
				-- where
				{$whereString}
				-- end where
			";
		}
		$query = "
			select
			-- select
				DL.DrugList_id as \"DrugList_id\",
				DL.DrugList_Name as \"DrugList_Name\",
				DLT.DrugListType_id as \"DrugListType_id\",
				DLT.DrugListType_Code as \"DrugListType_Code\",
				DLT.DrugListType_Name as \"DrugListType_Name\",
				to_char(DL.DrugList_begDate, '{$this->dateTimeForm104}') as \"DrugList_begDate\",
				to_char(DL.DrugList_endDate, '{$this->dateTimeForm104}') as \"DrugList_endDate\",
				PT.PayType_id as \"PayType_id\",
				PT.PayType_Name as \"PayType_Name\",
				DN.DocNormative_id as \"DocNormative_id\",
				DN.DocNormative_Num as \"DocNormative_Num\",
				DN.DocNormative_Name as \"DocNormative_Name\",
				DL.KLCountry_id as \"KLCountry_id\",
				DL.Region_id as \"Region_id\",
				case when DL.KLCountry_id = 643 then Region.KLArea_FullName else Country.KLCountry_Name end \"Region\",
				Publisher.DrugListObj_id as \"DrugListObj_id\",
				O.Org_id as \"Org_id\",
				L.Lpu_id as \"Lpu_id\",
				case 
					when coalesce(O.Org_id, L.Lpu_id) is not null then coalesce(O.Org_Nick, L.Lpu_Nick)||coalesce(' / '||LS.LpuSection_Name, '')
					when PublisherRegion.KLArea_id is not null then PublisherRegion.KLArea_FullName
					else Country.KLCountry_Name
				end as \"Publisher\"
			-- end select
			from
			-- from
				v_DrugList DL
				left join v_DrugListType DLT on DLT.DrugListType_id = DL.DrugListType_id
				left join v_DrugListObj Publisher on Publisher.DrugListObj_id = DL.DrugListObj_id
				left join v_UslugaComplexTariff UCT on UCT.UslugaComplexTariff_id = Publisher.UslugaComplexTariff_id
				left join v_PayType PT on PT.PayType_id = UCT.PayType_id
				left join v_DocNormative DN on DN.DocNormative_id = DL.DocNormative_id
				left join v_KLCountry Country on Country.KLCountry_id = DL.KLCountry_id
				left join v_KLArea Region on Region.KLArea_id = DL.Region_id
				left join v_Org O on O.Org_id = Publisher.Org_id
				left join v_Lpu L on L.Lpu_id = Publisher.Lpu_id
				left join v_LpuSection LS on LS.LpuSection_id = Publisher.LpuSection_id
				left join v_KLArea PublisherRegion on PublisherRegion.KLArea_id = Publisher.Region_id
			-- end from
			{$whereString}
			order by
			-- order by
			DL.DrugList_begDate
			-- end order by
		";
		$count_result = $this->queryResult(getCountSQLPH($query), $params);
		if (!is_array($count_result)) {
			return false;
		}
		$dataStart = (@$data["start"] != null) ? $data["start"] : 0;
		$result = $this->queryResult(getLimitSQLPH($query, $dataStart, $data["limit"]), $params);
		if (!is_array($result)) {
			return false;
		}
		return [
			"data" => $result,
			"totalCount" => $count_result[0]["cnt"]
		];
	}

	/**
	 * @param $data
	 * @return array
	 */
	function loadDrugListUsedGrid($data)
	{
		$params = [];
		$filter = "";
		if (!empty($data["DrugList_id"])) {
			$filter = "where DLU.DrugList_id = :DrugList_id";
			$params["DrugList_id"] = $data["DrugList_id"];
		}
		$query = "
			select
				DLU.DrugListUsed_id as \"DrugListUsed_id\",
				DLU.DrugList_id as \"DrugList_id\",
				DLO.DrugListObj_id as \"DrugListObj_id\",
				DLO.DrugListObj_Name as \"DrugListObj_Name\",
				L.Lpu_id as \"Lpu_id\",
				O.Org_id as \"Org_id\",
				(
					coalesce(O.Org_Nick, L.Lpu_Nick, '')||
					coalesce(' / '||LB.LpuBuilding_Nick, LB.LpuBuilding_Name, '')||
					coalesce(' / '||LS.LpuSection_Name, '')||
					coalesce(' / '||UC.UslugaComplex_Code||' '||UC.UslugaComplex_Name||', '||PT.PayType_Name||', '||MAG.MesAgeGroup_Name, '')||
					coalesce(' / '||S.Storage_Code||' '||S.Storage_Name, '')
				) as \"DrugListObj_Nick\",
				ETS.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
				LSP.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				coalesce(ETS.EmergencyTeamSpec_Name, LSP.LpuSectionProfile_Name) as \"DrugListObj_Profile\",
				Region.KLArea_FullName||', '||Country.KLCountry_Name as \"Region\"
			from
				v_DrugListUsed DLU
				inner join v_DrugListObj DLO on DLO.DrugListObj_id = DLU.DrugListObj_id
				left join v_Org O on O.Org_id = DLO.Org_id
				left join v_Lpu L on L.Lpu_id = DLO.Lpu_id
				left join v_LpuBuilding LB on LB.LpuBuilding_id = DLO.LpuBuilding_id
				left join v_LpuSection LS on LS.LpuSection_id = DLO.LpuSection_id
				left join v_LpuSectionProfile LSP on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = DLO.UslugaComplex_id
				left join v_UslugaComplexTariff UCT on UCT.UslugaComplexTariff_id = DLO.UslugaComplexTariff_id
				left join v_PayType PT on PT.PayType_id = UCT.PayType_id
				left join v_MesAgeGroup MAG on MAG.MesAgeGroup_id = UCT.MesAgeGroup_id
				left join v_Storage S on S.Storage_id = DLO.Storage_id
				left join v_EmergencyTeamSpec ETS on ETS.EmergencyTeamSpec_id = DLO.EmergencyTeamSpec_id
				left join v_KLArea Region on Region.KLArea_id = DLO.Region_id
				left join v_KLCountry Country on Country.KLCountry_id = Region.KLCountry_id
			{$filter}
			order by DLU.DrugListUsed_insDT
		";
		return ["data" => $this->queryResult($query, $params),];
	}

	/**
	 * @param array $data
	 * @return array|bool
	 */
	function loadDrugListStrGrid($data)
	{
		$params = [];
		$filters = [];
		if (!empty($data["DrugList_id"])) {
			$filters[] = "DLS.DrugList_id = :DrugList_id";
			$params["DrugList_id"] = $data["DrugList_id"];
		}
		if (!empty($data["DrugListGroup_id"])) {
			$filters[] = "DLS.DrugListGroup_id = :DrugListGroup_id";
			$params["DrugListGroup_id"] = $data["DrugListGroup_id"];
		}
		if (!empty($data["ClsATC_id"])) {
			$filters[] = "DLS.ClsATC_id = :ClsATC_id";
			$params["ClsATC_id"] = $data["ClsATC_id"];
		}
		if (!empty($data["DrugListStr_Name"])) {
			$filters[] = "DrugListStrName.Value ilike :DrugListStr_Name || '%'";
			$params["DrugListStr_Name"] = $data["DrugListStr_Name"];
		}
		$whereString = (count($filters) != 0) ? implode(" and ", $filters) : "";
		if($whereString != "") {
			$whereString = "
				where
				-- where
				{$whereString}
				-- end where
			";
		}
		$query = "
			select
			-- select
				DLS.DrugListStr_id as \"DrugListStr_id\",
				DLS.DrugList_id as \"DrugList_id\",
				DrugListStrName.Value as \"DrugListStr_Name\",
				DLS.DrugListStr_Num as \"DrugListStr_Num\",
				DLG.DrugListGroup_id as \"DrugListGroup_id\",
				DLG.DrugListGroup_Name as \"DrugListGroup_Name\",
				CDF.Clsdrugforms_id as \"Clsdrugforms_id\",
				CDF.NAME as \"Clsdrugforms_Name\",
				ATC.NAME as \"ClsATC_Name\",
				DLS.DrugListStr_Dose::varchar||coalesce(' '||DoseGU.GoodsUnit_Nick, '') as \"DrugListStr_Dose\",
				DLS.DrugListStr_Num::varchar||coalesce(' '||NumGU.GoodsUnit_Nick, '') as \"DrugListStr_Num\"
			-- end select
			from
			-- from
				v_DrugListStr DLS
				left join v_DrugListGroup DLG on DLG.DrugListGroup_id = DLS.DrugListGroup_id
				left join rls.v_Drug D on D.Drug_id = DLS.Drug_id
				left join rls.v_DrugComplexMnn DCM on DCM.DrugComplexMnn_id = DLS.DrugComplexMnn_id
				left join rls.v_Actmatters A on A.Actmatters_id = DLS.Actmatters_id
				left join rls.v_DrugNonpropNames DNN on DNN.DrugNonpropNames_id = DLS.DrugNonpropNames_id
				left join rls.v_Tradenames T on T.Tradenames_id = DLS.Tradenames_id
				left join lateral (
					select coalesce(
						D.Drug_Name,
						coalesce(DCM.DrugComplexMnn_RusName, A.RUSNAME, DNN.DrugNonpropNames_Name)||coalesce(' / '||T.NAME, ''),
						T.NAME,
						DLS.DrugListStr_Name
					) as Value
				) as DrugListStrName on true
				left join rls.v_Clsdrugforms CDF on CDF.Clsdrugforms_id = DLS.Clsdrugforms_id
				left join rls.v_PREP_ATC PA on PA.PREPID = D.DrugPrep_id
				left join rls.v_ClsATC ATC on ATC.ClsATC_id = coalesce(DLS.ClsATC_id, PA.UNIQID)
				left join v_GoodsUnit DoseGU on DoseGU.GoodsUnit_id = DLS.GoodsUnit_did
				left join v_GoodsUnit NumGU on NumGU.GoodsUnit_id = DLS.GoodsUnit_nid
			-- end from
			{$whereString}
			order by
			-- order by
				DLS.DrugListStr_id
			-- end order by
		";
		$count_result = $this->queryResult(getCountSQLPH($query), $params);
		if (!is_array($count_result)) {
			return false;
		}
		$result = $this->queryResult(getLimitSQLPH($query, $data["start"], $data["limit"]), $params);
		if (!is_array($result)) {
			return false;
		}
		return [
			"data" => $result,
			"totalCount" => $count_result[0]["cnt"]
		];
	}

	/**
	 * @param $data
	 * @return array|false
	 */
	function loadDrugListObjList($data)
	{
		$params = [];
		$filters = [];
		if (!empty($data["query"])) {
			$filters[] = "DLO.DrugListObj_Name ilike :DrugListObj_Name || '%'";
			$params["DrugListObj_Name"] = $data["query"];
		}
		if (!empty($data["DrugListObj_id"])) {
			$filters[] = "DLO.DrugListObj_id = :DrugListObj_id";
			$params["DrugListObj_id"] = $data["DrugListObj_id"];
		} else {
			if (!empty($data["Lpu_oid"])) {
				$filters[] = "DLO.Lpu_id = :Lpu_oid";
				$params["Lpu_oid"] = $data["Lpu_oid"];
			}
			if (!empty($data["Org_id"])) {
				$filters[] = "coalesce(L.Org_id, DLO.Org_id) = :Org_id";
				$params["Org_id"] = $data["Org_id"];
			}
			if (!empty($data["isPublisher"]) && $data["isPublisher"]) {
				$filters[] = "DLO.Region_id is not null and coalesce(DLO.Lpu_id, DLO.Org_id) is not null";
				$params["DrugListObj_id"] = $data["DrugListObj_id"];
			}
		}
		$whereString = (count($filters) != 0) ? "where " . implode(" and ", $filters) : "";
		$query = "
			select
				DLO.DrugListObj_id as \"DrugListObj_id\",
				DLO.DrugListObj_Name as \"DrugListObj_Name\",
				(
					coalesce(O.Org_Nick, '')||
					coalesce(L.Lpu_Nick, '')||
					coalesce(' / '||LB.LpuBuilding_Nick, '')||
					coalesce(' / '||LS.LpuSection_Name, '')
				) as \"DrugListObj_PublisherNick\"
			from
				v_DrugListObj DLO
				left join v_Lpu L on L.Lpu_id = DLO.Lpu_id
				left join v_Org O on L.Org_id = DLO.Org_id
				left join v_LpuBuilding LB on LB.LpuBuilding_id = DLO.LpuBuilding_id
				left join v_LpuSection LS on LS.LpuSection_id = DLO.LpuSection_id
			{$whereString}
			limit 1
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function loadDrugListForm($data)
	{
		$params = ["DrugList_id" => $data["DrugList_id"]];
		$query = "
			select
				DL.DrugList_id as \"DrugList_id\",
				to_char(DL.DrugList_begDate, '{$this->dateTimeForm104}') as \"DrugList_begDate\",
				to_char(DL.DrugList_endDate, '{$this->dateTimeForm104}') as \"DrugList_endDate\",
				DL.DrugList_Name as \"DrugList_Name\",
				DL.DrugListType_id as \"DrugListType_id\",
				DL.DocNormative_id as \"DocNormative_id\",
				DL.DrugListObj_id as \"DrugListObj_id\",
				DL.KLCountry_id as \"KLCountry_id\",
				DL.Region_id as \"Region_id\"
			from v_DrugList DL
			where DL.DrugList_id = :DrugList_id
			limit 1
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function loadDrugListObjForm($data)
	{
		$params = ["DrugListObj_id" => $data["DrugListObj_id"]];
		$query = "
			select
				DLO.DrugListObj_id as \"DrugListObj_id\",
				DLO.DrugListObj_Name as \"DrugListObj_Name\",
				coalesce(L.Org_id, DLO.Org_id) as \"Org_id\",
				DLO.LpuBuilding_id as \"LpuBuilding_id\",
				DLO.LpuSection_id as \"LpuSection_id\",
				DLO.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
				DLO.UslugaComplex_id as \"UslugaComplex_id\",
				DLO.UslugaComplexTariff_id as \"UslugaComplexTariff_id\",
				DLO.Storage_id as \"Storage_id\",
				DLO.Region_id as \"Region_id\"
			from
				v_DrugListObj DLO
				left join v_Lpu L on L.Lpu_id = DLO.Lpu_id
			where DLO.DrugListObj_id = :DrugListObj_id
			limit 1
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function loadDrugListUsedForm($data)
	{
		$params = ["DrugListUsed_id" => $data["DrugListUsed_id"]];
		$query = "
			select
				DLU.DrugListUsed_id as \"DrugListUsed_id\",
				DLU.DrugList_id as \"DrugList_id\",
				DLO.DrugListObj_id as \"DrugListObj_id\",
				DLO.DrugListObj_Name as \"DrugListObj_Name\",
				coalesce(L.Org_id, DLO.Org_id) as Org_id,
				DLO.LpuBuilding_id as \"LpuBuilding_id\",
				DLO.LpuSection_id as \"LpuSection_id\",
				DLO.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
				DLO.UslugaComplex_id as \"UslugaComplex_id\",
				DLO.UslugaComplexTariff_id as \"UslugaComplexTariff_id\",
				DLO.Storage_id as \"Storage_id\",
				DLO.Region_id as \"Region_id\"
			from
				v_DrugListUsed DLU
				inner join v_DrugListObj DLO on DLO.DrugListObj_id = DLU.DrugListObj_id
				left join v_Lpu L on L.Lpu_id = DLO.Lpu_id
			where DLU.DrugListUsed_id = :DrugListUsed_id
			limit 1
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных для редактирвоания медикамента в перечне
	 * @param array $data
	 * @return array
	 */
	function loadDrugListStrForm($data)
	{
		$params = ["DrugListStr_id" => $data["DrugListStr_id"]];
		$query = "
			select
				DLS.DrugListStr_id as \"DrugListStr_id\",
				DLS.DrugList_id as \"DrugList_id\",
				DLS.DrugListStr_Name as \"DrugListStr_Name\",
				DLS.DrugListGroup_id as \"DrugListGroup_id\",
				DLS.Drug_id as \"Drug_id\",
				DLS.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				DLS.Actmatters_id as \"Actmatters_id\",
				DLS.DrugNonpropNames_id as \"DrugNonpropNames_id\",
				DLS.Tradenames_id as \"Tradenames_id\",
				DLS.Clsdrugforms_id as \"Clsdrugforms_id\",
				DLS.DrugListStr_Comment as \"DrugListStr_Comment\",
				DLS.DrugListStr_Dose as \"DrugListStr_Dose\",
				DLS.GoodsUnit_did as \"GoodsUnit_did\",
				DLS.DrugListStr_Num as \"DrugListStr_Num\",
				DLS.GoodsUnit_nid as \"GoodsUnit_nid\"
			from v_DrugListStr DLS
			where DLS.DrugListStr_id = :DrugListStr_id
			limit 1
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Сохранение перечня медикаментов
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function saveDrugList($data)
	{
		$params = [
			"DrugList_id" => !empty($data["DrugList_id"]) ? $data["DrugList_id"] : null,
			"DrugList_begDate" => $data["DrugList_begDate"],
			"DrugList_endDate" => !empty($data["DrugList_endDate"]) ? $data["DrugList_endDate"] : null,
			"DrugList_Name" => $data["DrugList_Name"],
			"DrugListType_id" => $data["DrugListType_id"],
			"DocNormative_id" => !empty($data["DocNormative_id"]) ? $data["DocNormative_id"] : null,
			"DrugListObj_id" => !empty($data["DrugListObj_id"]) ? $data["DrugListObj_id"] : null,
			"KLCountry_id" => $data["KLCountry_id"],
			"Region_id" => !empty($data["Region_id"]) ? $data["Region_id"] : null,
			"pmUser_id" => $data["pmUser_id"],
		];
		$procedure = (empty($params['DrugList_id'])) ? "p_DrugList_ins" : "p_DrugList_upd";
		$selectString = "
		    druglist_id as \"DrugList_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    druglist_id := :DrugList_id,
			    druglist_name := :DrugList_Name,
			    druglisttype_id := :DrugListType_id,
			    docnormative_id := :DocNormative_id,
			    druglist_begdate := :DrugList_begDate,
			    druglist_enddate := :DrugList_endDate,
			    klcountry_id := :KLCountry_id,
			    region_id := :Region_id,
			    druglistobj_id := :DrugListObj_id,
			    pmuser_id := :pmUser_id
			);
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			throw new Exception("Ошибка при сохранении перечня медикаментов");
		}
		return $response;
	}

	/**
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function updateDataLpuOrg(&$data)
	{
		if (empty($data["Lpu_oid"]) && !empty($data["Org_id"])) {
			$query = "
				select Lpu_id
				from v_Lpu_all
				where Org_id = :Org_id
				limit 1
			";
			$data["Lpu_oid"] = $this->getFirstResultFromQuery($query, $data, true);
			if ($data["Lpu_oid"] === false) {
				throw new Exception("Ошибка при поиске идентификатора МО");
			}
		}
		if (!empty($data["Lpu_oid"])) {
			$data["Org_id"] = null;
		}
		return [["success" => true]];
	}

	/**
	 * Сохранение объекта, использующего перечни медикаментов
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function saveDrugListObj($data)
	{
		$resp = $this->updateDataLpuOrg($data);
		if (!$this->isSuccessful($data)) {
			return $resp;
		}
		$params = [
			"DrugListObj_id" => !empty($data["DrugListObj_id"]) ? $data["DrugListObj_id"] : null,
			"DrugListObj_Name" => $data["DrugListObj_Name"],
			"Org_id" => !empty($data["Org_id"]) ? $data["Org_id"] : null,
			"Lpu_id" => !empty($data["Lpu_oid"]) ? $data["Lpu_oid"] : null,
			"LpuBuilding_id" => !empty($data["LpuBuilding_id"]) ? $data["LpuBuilding_id"] : null,
			"LpuSection_id" => !empty($data["LpuSection_id"]) ? $data["LpuSection_id"] : null,
			"EmergencyTeamSpec_id" => !empty($data["EmergencyTeamSpec_id"]) ? $data["EmergencyTeamSpec_id"] : null,
			"UslugaComplex_id" => !empty($data["UslugaComplex_id"]) ? $data["UslugaComplex_id"] : null,
			"UslugaComplexTariff_id" => !empty($data["UslugaComplexTariff_id"]) ? $data["UslugaComplexTariff_id"] : null,
			"Storage_id" => !empty($data["Storage_id"]) ? $data["Storage_id"] : null,
			"Region_id" => !empty($data["Region_id"]) ? $data["Region_id"] : getRegionNumber(),
			"pmUser_id" => $data["pmUser_id"],
		];
		$procedure = (empty($params["DrugListObj_id"])) ? "p_DrugListObj_ins" : "p_DrugListObj_upd";
		$selectString = "
			    druglistobj_id as \"DrugListObj_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    druglistobj_id := :DrugListObj_id,
			    druglistobj_name := :DrugListObj_Name,
			    org_id := :Org_id,
			    lpu_id := :Lpu_id,
			    lpubuilding_id := :LpuBuilding_id,
			    lpusection_id := :LpuSection_id,
			    emergencyteamspec_id := :EmergencyTeamSpec_id,
			    uslugacomplex_id := :UslugaComplex_id,
			    uslugacomplextariff_id := :UslugaComplexTariff_id,
			    storage_id := :Storage_id,
			    region_id := :Region_id,
			    pmuser_id := :pmUser_id
			);
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception("Ошибка при сохрании объекта, использующего перечни медикаментов");
		}
		return $resp;
	}

	/**
	 * Сохранение использование перечня медикаментов
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function saveDrugListUsed($data)
	{
		$params = [
			"DrugListUsed_id" => !empty($data["DrugListUsed_id"]) ? $data["DrugListUsed_id"] : null,
			"DrugList_id" => $data["DrugList_id"],
			"DrugListObj_id" => $data["DrugListObj_id"],
			"pmUser_id" => $data["pmUser_id"],
		];
		$procedure = (empty($params['DrugListUsed_id'])) ? "p_DrugListUsed_ins" : "p_DrugListUsed_upd";
		$selectString = "
		    druglistused_id as \"DrugListUsed_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    druglistused_id := :DrugListUsed_id,
			    druglist_id := :DrugList_id,
			    druglistobj_id := :DrugListObj_id,
			    pmuser_id := :pmUser_id
			);
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			throw new Exception("Ошибка при сохрании использования перечня медикаментов");
		}
		return $response;
	}

	/**
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function getDrugListObjId($data)
	{
		$resp = $this->updateDataLpuOrg($data);
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}
		$params = [
			"DrugListObj_Name" => !empty($data["DrugListObj_Name"]) ? $data["DrugListObj_Name"] : null,
			"Org_id" => !empty($data["Org_id"]) ? $data["Org_id"] : null,
			"Lpu_id" => !empty($data["Lpu_oid"]) ? $data["Lpu_oid"] : null,
			"LpuBuilding_id" => !empty($data["LpuBuilding_id"]) ? $data["LpuBuilding_id"] : null,
			"LpuSection_id" => !empty($data["LpuSection_id"]) ? $data["LpuSection_id"] : null,
			"EmergencyTeamSpec_id" => !empty($data["EmergencyTeamSpec_id"]) ? $data["EmergencyTeamSpec_id"] : null,
			"UslugaComplex_id" => !empty($data["UslugaComplex_id"]) ? $data["UslugaComplex_id"] : null,
			"UslugaComplexTariff_id" => !empty($data["UslugaComplexTariff_id"]) ? $data["UslugaComplexTariff_id"] : null,
			"Storage_id" => !empty($data["Storage_id"]) ? $data["Storage_id"] : null,
			"Region_id" => !empty($data["Region_id"]) ? $data["Region_id"] : getRegionNumber(),
		];
		$filters = [];
		foreach ($params as $key => $value) {
			if (isset($value)) {
				$filters[] = "DLO.{$key} = :{$key}";
			} else {
				$filters[] = "DLO.{$key} is null";
			}
		}
		$whereString = (count($filters) != 0) ? "where " . implode(" and ", $filters) : "";
		$query = "
			select DLO.DrugListObj_id
			from v_DrugListObj DLO
			{$whereString}
			order by DrugListObj_insDT desc
			limit 1
		";
		$id = $this->getFirstResultFromQuery($query, $params, true);
		if ($id === false) {
			throw new Exception("Ошибка при поиске идентификатора объекта, использующего перечни медикаментов");
		}
		return [[
			"success" => true,
			"DrugListObj_id" => $id,
		]];
	}

	/**
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function saveDrugListObjOrUsed($data)
	{
		$response = ["success" => true];
		try {
			$this->beginTransaction();
			if (empty($data["DrugList_id"]) || empty($data["DrugListObj_id"])) {
				//Поиск идентификатора объекта по полному совпадения параметров
				$resp = $this->getDrugListObjId($data);
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]["Error_Msg"]);
				}
				if (empty($resp[0]["DrugListObj_id"])) {
					//Если идентификатор не найден, то создание нового объекта
					$resp = $this->saveDrugListObj($data);
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]["Error_Msg"]);
					}
				}
				$response["DrugListObj_id"] = $data["DrugListObj_id"] = $resp[0]["DrugListObj_id"];
			}
			if (!empty($data["DrugList_id"])) {
				if (empty($data["DrugListUsed_id"])) {
					$query = "
						select DrugListUsed_id
						from v_DrugListUsed
						where DrugList_id = :DrugList_id
						  and DrugListObj_id = :DrugListObj_id
						limit 1
					";
					$data["DrugListUsed_id"] = $this->getFirstResultFromQuery($query, $data, true);
					if ($data["DrugListUsed_id"] === false) {
						throw new Exception("Ошибка при проверке использования перечня медикаментов");
					}
				} else {
					$query = "
						select DrugListObj_id
						from v_DrugListUsed
						where DrugListUsed_id = :DrugListUsed_id
						limit 1
					";
					$prevDrugListObj_id = $this->getFirstResultFromQuery($query, $data, true);
					if ($prevDrugListObj_id === false) {
						throw new Exception("Ошибка при получнии данных об использовании перечня медикаментов");
					}
					if ($prevDrugListObj_id && $prevDrugListObj_id != $data["DrugListObj_id"]) {
						$resp = $this->deleteDrugListObj(["DrugListObj_id" => $prevDrugListObj_id]);
						if (!$this->isSuccessful($resp) && $resp[0]["Error_Code"] != 101) {
							throw new Exception($resp[0]["Error_Msg"]);
						}
					}
				}
				$resp = $this->saveDrugListUsed([
					"DrugListUsed_id" => $data["DrugListUsed_id"],
					"DrugList_id" => $data["DrugList_id"],
					"DrugListObj_id" => $data["DrugListObj_id"],
					"pmUser_id" => $data["pmUser_id"],
				]);
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]["Error_Msg"]);
				}
				$response["DrugListUsed_id"] = $resp[0]["DrugListUsed_id"];
			}
			$this->commitTransaction();
		} catch (Exception $e) {
			$this->rollbackTransaction();
			throw new Exception($e->getMessage());
		}
		return $response;
	}

	/**
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function checkDoubleDrugListStr($data)
	{
		$params = [
			"DrugListStr_id" => !empty($data["DrugListStr_id"]) ? $data["DrugListStr_id"] : null,
			"DrugList_id" => $data["DrugList_id"],
			"DrugListStr_Name" => $data["DrugListStr_Name"],
			"DrugListGroup_id" => !empty($data["DrugListGroup_id"]) ? $data["DrugListGroup_id"] : null,
			"Drug_id" => !empty($data["Drug_id"]) ? $data["Drug_id"] : null,
			"DrugComplexMnn_id" => !empty($data["DrugComplexMnn_id"]) ? $data["DrugComplexMnn_id"] : null,
			"Actmatters_id" => !empty($data["Actmatters_id"]) ? $data["Actmatters_id"] : null,
			"DrugNonpropNames_id" => !empty($data["DrugNonpropNames_id"]) ? $data["DrugNonpropNames_id"] : null,
			"Tradenames_id" => !empty($data["Tradenames_id"]) ? $data["Tradenames_id"] : null,
			"Clsdrugforms_id" => !empty($data["Clsdrugforms_id"]) ? $data["Clsdrugforms_id"] : null,
			"DrugListStr_Comment" => !empty($data["DrugListStr_Comment"]) ? $data["DrugListStr_Comment"] : null,
			"DrugListStr_Dose" => !empty($data["DrugListStr_Dose"]) ? $data["DrugListStr_Dose"] : null,
			"GoodsUnit_did" => !empty($data["GoodsUnit_did"]) ? $data["GoodsUnit_did"] : null,
			"DrugListStr_Num" => !empty($data["DrugListStr_Num"]) ? $data["DrugListStr_Num"] : null,
			"GoodsUnit_nid" => !empty($data["GoodsUnit_nid"]) ? $data["GoodsUnit_nid"] : null,
		];
		$query = "
			select
				count(*) as cnt
			from v_DrugListStr DLS
			where DLS.DrugList_id = :DrugList_id
				and coalesce(DLS.DrugListStr_id, 0) <> coalesce(cast(:DrugListStr_id as bigint), 0)
				and DLS.DrugList_id = :DrugList_id
				and DLS.DrugListStr_Name = :DrugListStr_Name
				and coalesce(DLS.DrugListGroup_id, 0) = coalesce(cast(:DrugListGroup_id as bigint), 0)
				and coalesce(DLS.Drug_id, 0) = coalesce(cast(:Drug_id as bigint), 0)
				and coalesce(DLS.DrugComplexMnn_id, 0) = coalesce(cast(:DrugComplexMnn_id as bigint), 0)
				and coalesce(DLS.Actmatters_id, 0) = coalesce(cast(:Actmatters_id as bigint), 0)
				and coalesce(DLS.DrugNonpropNames_id, 0) = coalesce(cast(:DrugNonpropNames_id as bigint), 0)
				and coalesce(DLS.Tradenames_id, 0) = coalesce(cast(:Tradenames_id as integer), 0)
				and coalesce(DLS.Clsdrugforms_id, 0) = coalesce(cast(:Clsdrugforms_id as integer), 0)
				and coalesce(DLS.DrugListStr_Comment,'') = coalesce(cast(:DrugListStr_Comment as varchar),'')
				and coalesce(DLS.DrugListStr_Dose,'') = coalesce(cast(:DrugListStr_Dose as varchar), '')
				and coalesce(DLS.GoodsUnit_did, 0) = coalesce(cast(:GoodsUnit_did as bigint), 0)
				and coalesce(DLS.DrugListStr_Num, 0) = coalesce(cast(:DrugListStr_Num as integer), 0)
				and coalesce(DLS.GoodsUnit_nid, 0) = coalesce(cast(:GoodsUnit_nid as bigint), 0)
			limit 1
		";
		$cnt = $this->getFirstResultFromQuery($query, $params);
		if ($cnt === false) {
			throw new Exception("Ошибка при проверке дублирования медикаментов в перечне");
		}
		if ($cnt > 0) {
			throw new Exception("Такой медикамент уже есть в списке. Сохранение невозможно.");
		}
		return [['success' => true]];
	}

	/**
	 * Сохранение медикамента в перечне
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function saveDrugListStr($data)
	{
		$resp = $this->checkDoubleDrugListStr($data);
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}
		$params = [
			"DrugListStr_id" => !empty($data["DrugListStr_id"]) ? $data["DrugListStr_id"] : null,
			"DrugList_id" => $data["DrugList_id"],
			"DrugListStr_Name" => $data["DrugListStr_Name"],
			"DrugListGroup_id" => !empty($data["DrugListGroup_id"]) ? $data["DrugListGroup_id"] : null,
			"Drug_id" => !empty($data["Drug_id"]) ? $data["Drug_id"] : null,
			"DrugComplexMnn_id" => !empty($data["DrugComplexMnn_id"]) ? $data["DrugComplexMnn_id"] : null,
			"Actmatters_id" => !empty($data["Actmatters_id"]) ? $data["Actmatters_id"] : null,
			"DrugNonpropNames_id" => !empty($data["DrugNonpropNames_id"]) ? $data["DrugNonpropNames_id"] : null,
			"Tradenames_id" => !empty($data["Tradenames_id"]) ? $data["Tradenames_id"] : null,
			"Clsdrugforms_id" => !empty($data["Clsdrugforms_id"]) ? $data["Clsdrugforms_id"] : null,
			"DrugListStr_Comment" => !empty($data["DrugListStr_Comment"]) ? $data["DrugListStr_Comment"] : null,
			"DrugListStr_Dose" => !empty($data["DrugListStr_Dose"]) ? $data["DrugListStr_Dose"] : null,
			"GoodsUnit_did" => !empty($data["GoodsUnit_did"]) ? $data["GoodsUnit_did"] : null,
			"DrugListStr_Num" => !empty($data["DrugListStr_Num"]) ? $data["DrugListStr_Num"] : null,
			"GoodsUnit_nid" => !empty($data["GoodsUnit_nid"]) ? $data["GoodsUnit_nid"] : null,
			"pmUser_id" => $data["pmUser_id"],
		];
		$procedure = (empty($params["DrugListStr_id"])) ? "p_DrugListStr_ins" : "p_DrugListStr_upd";
		$selectString = "
		    drugliststr_id as \"DrugListStr_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    drugliststr_id := :DrugListStr_id,
			    druglist_id := :DrugList_id,
			    druglistgroup_id := :DrugListGroup_id,
			    drugliststr_name := :DrugListStr_Name,
			    actmatters_id := :Actmatters_id,
			    drugnonpropnames_id := :DrugNonpropNames_id,
			    tradenames_id := :Tradenames_id,
			    clsdrugforms_id := :Clsdrugforms_id,
			    drugliststr_dose := :DrugListStr_Dose,
			    goodsunit_did := :GoodsUnit_did,
			    drugcomplexmnn_id := :DrugComplexMnn_id,
			    drug_id := :Drug_id,
			    drugliststr_num := :DrugListStr_Num,
			    goodsunit_nid := :GoodsUnit_nid,
			    drugliststr_comment := :DrugListStr_Comment,
			    pmuser_id := :pmUser_id
			);
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			throw new Exception("Ошибка при сохрании медикамента в перечне");
		}
		return $response;
	}

	/**
	 * Удаление перечня медикаментов
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function deleteDrugList($data)
	{
		$params = ['DrugList_id' => $data['DrugList_id']];
		$query = "
			select DrugListStr_id as \"DrugListStr_id\"
			from v_DrugListStr
			where DrugList_id = :DrugList_id
		";
		$DrugListStr_ids = $this->queryList($query, $params);
		if (!is_array($DrugListStr_ids)) {
			throw new Exception("Ошибка при получнии списка медикаментов перечня");
		}
		$query = "
			select DrugListUsed_id as \"DrugListUsed_id\"
			from v_DrugListUsed
			where DrugList_id = :DrugList_id
		";
		$DrugListUsed_ids = $this->queryList($query, $params);
		if (!is_array($DrugListStr_ids)) {
			throw new Exception("Ошибка при получнии списка использований перечня медикаментов");
		}
		$this->beginTransaction();
		try {
			$this->isAllowTransaction = false;
			$query = "
				update DrugList
				set DrugListObj_id = null
				where DrugList_id = :DrugList_id
			";
			$this->db->query($query, $params);
			foreach ($DrugListStr_ids as $DrugListStr_id) {
				$resp = $this->deleteDrugListStr(["DrugListStr_id" => $DrugListStr_id]);
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]["Error_Code"]);
				}
			}
			foreach ($DrugListUsed_ids as $DrugListUsed_id) {
				$resp = $this->deleteDrugListUsed(["DrugListUsed_id" => $DrugListUsed_id]);
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]["Error_Code"]);
				}
			}
			$query = "
				select
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from p_druglist_del(druglist_id := :DrugList_id);
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
			throw new Exception($e->getMessage());
		}
		return $response;
	}

	/**
	 * Удаление объекта, использующего перечень
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function deleteDrugListObj($data)
	{
		$params = ["DrugListObj_id" => $data["DrugListObj_id"]];
		$query = "
			select (
				select count(1) as cnt
				from v_DrugList
				where DrugListObj_id = :DrugListObj_id
			    limit 1
			) + (
				select count(1) as cnt
				from v_DrugListUsed
				where DrugListObj_id = :DrugListObj_id
			    limit 1
			) as cnt
		";
		$cnt = $this->getFirstResultFromQuery($query, $params);
		if ($cnt === false) {
			throw new Exception("Ошибка при проверке связей объектов с перечнями");
		}
		if ($cnt > 0) {
			throw new Exception("Невозможно удалить объект, поскольку у него существуют связи с перечнями медикаментов");
		}
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_druglistobj_del(druglistobj_id := :DrugListObj_id);
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			throw new Exception("Ошибка при удалении объекта, использующего перечень медикаментов");
		}
		return $response;
	}

	/**
	 * Удаление использования перечня
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function deleteDrugListUsed($data)
	{
		$params = ["DrugListUsed_id" => $data["DrugListUsed_id"]];
		$query = "
			select DrugListObj_id
			from v_DrugListUsed
			where DrugListUsed_id = :DrugListUsed_id
			limit 1
		";
		$DrugListObj_id = $this->getFirstResultFromQuery($query, $params);
		if (empty($DrugListObj_id)) {
			throw new Exception("Ошибка при получении данных об использовании перечня медикаментов");
		}
		$this->beginTransaction();
		try {
			$query = "
				select
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from p_druglistused_del(druglistused_id := :DrugListUsed_id);
			";
			$response = $this->queryResult($query, $params);
			if (!is_array($response)) {
				throw new Exception("Ошибка при удалении использования перечня медикаментов");
			}
			$resp = $this->deleteDrugListObj(["DrugListObj_id" => $DrugListObj_id]);
			if (!$this->isSuccessful($resp) && $resp[0]["Error_Code"] != 101) {
				throw new Exception($resp[0]["Error_Msg"]);
			}
			$this->commitTransaction();
		} catch (Exception $e) {
			$this->rollbackTransaction();
			throw new Exception($e->getMessage());
		}
		return $response;
	}

	/**
	 * Удаления медикамента из перечня
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function deleteDrugListStr($data)
	{
		$params = ["DrugListStr_id" => $data["DrugListStr_id"]];
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_drugliststr_del(drugliststr_id := :DrugListStr_id);
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			throw new Exception("Ошибка при удалении медикамента из перечня");
		}
		return $response;
	}
}