<?php

class Drug_model_get
{
	/**
	 * @param Drug_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getDrugGrid(Drug_model $callObject, $data)
	{
		$filter = "";
		$org_farm_filter = "";
		$queryParams = [];

		if ( isset($data['Drug_id']) ) {
            $filter .= " and d.Drug_id = :Drug_id";
            $queryParams['Drug_id'] = $data['Drug_id'];
        }

		if (isset($data["mnn"])) {
			$filter .= " and dm.DrugMnn_Name ilike :mnn";
			$queryParams["mnn"] = $data["mnn"] . "%";
		}
		if (isset($data["torg"])) {
			$filter .= " and d.Drug_Name ilike :torg";
			$queryParams["torg"] = $data["torg"] . "%";
		}
		if (isset($data["org_farm_filter"])) {
			$org_farm_filter .= "
				and exists(
					select 1
					from v_OrgFarmacy OrgF
					where OrgF.Orgfarmacy_Name ilike :org_farm_filter or OrgF.Orgfarmacy_HowGo ilike :org_farm_filter and OrgF.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
				)
			";
			$queryParams["org_farm_filter"] = "%" . $data["org_farm_filter"] . "%";
		}
		$ostat_filter = "
			and exists(
				select Drug_id
				FROM
					v_DrugOstat drugostat
					inner join v_OrgFarmacy OrgFarmacy on drugostat.OrgFarmacy_id=OrgFarmacy.OrgFarmacy_id {$org_farm_filter}
				WHERE drugostat.drugostat_kolvo > 0
				  and d.Drug_id = drugostat.Drug_id
			)
		";
		$sql = "
			select
			-- select
				d.Drug_id as \"Drug_id\",
			    dm.DrugMnn_Name as \"DrugMnn_Name\",
			    d.Drug_Name as \"Drug_Name\",
			    d.Drug_CodeG as \"Drug_CodeG\"
			-- end select
			from
			-- from
				v_Drug d
				inner join v_DrugMnn dm on dm.DrugMnn_id = d.DrugMnn_id
			-- end from
			where
			-- where
				d.Drug_IsDel = 1
				{$filter} {$ostat_filter}
			-- end where
			order by
			-- order by
			DrugMnn_Name, Drug_Name
			-- end order by
		";
		$count_sql = getCountSQLPH($sql);
		if (isset($data["start"]) && $data["start"] >= 0 && isset($data["limit"]) && $data["limit"] >= 0) {
			$sql = getLimitSQLPH($sql, $data["start"], $data["limit"]);
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$response = [];
		$response["data"] = $result->result("array");
		// Если количество записей запроса равно limit, то, скорее всего еще есть страницы и каунт надо посчитать
		if (count($response["data"]) == $data["limit"]) {
			// считаем каунт запроса по БД
			$result_count = $callObject->db->query($count_sql, $queryParams);
			if (is_object($result_count)) {
				$cnt_arr = $result_count->result("array");
				$count = $cnt_arr[0]["cnt"];
				unset($cnt_arr);
			} else {
				$count = 0;
			}
		} else {
			// Иначе считаем каунт по реальному количеству + start
			$count = $data["start"] + count($response["data"]);
		}
		$response["totalCount"] = $count;
		return $response;
	}

	/**
	 * Получение списка аптек с остатками по выбранному медикаменту
	 * @param $data
	 * @return array|bool
	 */
	public static function getDrugOstat(Drug_model $callObject, $data)
	{
		switch ($data["ReceptFinance_Code"]) {
			case 1:
				$finance_type = "Fed";
				break;
			case 2:
				$finance_type = "Reg";
				break;
			case 3:
				$finance_type = "Noz";
				$data["ReceptFinance_Code"] = 1;
				break;
			default:
				return false;
				break;
		}
		switch ($data["mode"]) {
			case "all":
				$filter = "";
				if (isset($data['ReceptType_Code']) && $data['ReceptType_Code'] == 2 && $data['session']['region']['nick'] != 'saratov') {
					$filter .= " and coalesce(Farm_Ostat.DrugOstat_Kolvo, 0) > 0";
				}
				$selectString1 = "
					OrgFarmacy.OrgFarmacy_id as \"OrgFarmacy_id\",
					OrgFarmacyIndex.OrgFarmacyIndex_id as \"OrgFarmacyIndex_id\",
					rtrim(Org.Org_Name) as \"OrgFarmacy_Name\",
					rtrim(OrgFarmacy.OrgFarmacy_HowGo) as \"OrgFarmacy_HowGo\",
					YesNo.YesNo_Code as \"OrgFarmacy_IsFarmacy\",
					to_char(coalesce(Farm_Ostat.DrugOstat_Kolvo, 0), '{$callObject->numericForm18_2}') as \"DrugOstat_Kolvo\",
					coalesce(OST.OMSSprTerr_Code, 0) as \"OMSSprTerr_Code\",
					0 as \"sort\"
				";
				$fromString1 = "
					v_OrgFarmacy OrgFarmacy
					inner join v_Org Org on Org.Org_id = OrgFarmacy.Org_id
					left join lateral(
						select SUM(DD.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from
							v_DrugOstat DD
							inner join v_ReceptFinance RF on RF.ReceptFinance_id = DD.ReceptFinance_id and RF.ReceptFinance_Code = :ReceptFinance_Code
						where DD.Drug_id = :Drug_id
						  and DD.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
						group by DD.OrgFarmacy_id
					) as Farm_Ostat on true
					left join OrgFarmacyIndex on OrgFarmacyIndex.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id and OrgFarmacyIndex.Lpu_id = :Lpu_id
					left join YesNo on YesNo.YesNo_id = coalesce(OrgFarmacy.OrgFarmacy_IsFarmacy, 2)
					left join Address PAddr on PAddr.Address_id = Org.PAddress_id
					left join OMSSprTerr OST on coalesce(OST.KLRgn_id, 0) = coalesce(PAddr.KLRgn_id, 0)
						and coalesce(OST.KLSubRgn_id, 0) = coalesce(PAddr.KLSubRgn_id, 0)
						and coalesce(OST.KLCity_id, 0) = coalesce(PAddr.KLCity_id, 0)
						and coalesce(OST.KLTown_id, 0) = coalesce(PAddr.KLTown_id, 0)
				";
				$whereString1 = "
						coalesce(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2
					and coalesce(OrgFarmacy.OrgFarmacy_Is{$finance_type}Lgot, 2) = 2
					and OrgFarmacy.OrgFarmacy_id not in (1, 2)
					{$filter}
				";
				$selectString2 = "
					OrgFarmacy.OrgFarmacy_id as \"OrgFarmacy_id\",
					OrgFarmacyIndex.OrgFarmacyIndex_id as \"OrgFarmacyIndex_id\",
					'Остатки на аптечном складе' as \"OrgFarmacy_Name\",
					'' as \"OrgFarmacy_HowGo\",
					YesNo.YesNo_Code as \"OrgFarmacy_IsFarmacy\",
					to_char(coalesce(Farm_Ostat.DrugOstat_Kolvo, 0), '{$callObject->numericForm18_2}') as \"DrugOstat_Kolvo\",
					'' as \"OMSSprTerr_Code\",
					1 as \"sort\"
				";
				$fromString2 = "
					v_OrgFarmacy OrgFarmacy
					inner join v_Org Org on Org.Org_id = OrgFarmacy.Org_id
					left join lateral (
						select SUM(DD.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from
							v_DrugOstat DD
							inner join v_ReceptFinance RF on RF.ReceptFinance_id = DD.ReceptFinance_id and RF.ReceptFinance_Code = :ReceptFinance_Code
						where DD.Drug_id = :Drug_id
						  and DD.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
						group by DD.OrgFarmacy_id
					) as Farm_Ostat on true
					left join OrgFarmacyIndex on OrgFarmacyIndex.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id and OrgFarmacyIndex.Lpu_id = :Lpu_id
					left join YesNo on YesNo.YesNo_id = coalesce(OrgFarmacy.OrgFarmacy_IsFarmacy, 2)
					left join Address PAddr on PAddr.Address_id = Org.PAddress_id
				";
				$whereString2 = "OrgFarmacy.OrgFarmacy_id = 1 {$filter}";
				$orderByString = "sort";
				$query = "
					select {$selectString1}
					from {$fromString1}
					where {$whereString1}
					union all
					select {$selectString2}
					from {$fromString2}
					where {$whereString2}
					order by {$orderByString}
				";
				break;
			default:
				$query = "
					select
						OrgFarmacy.OrgFarmacy_id as \"OrgFarmacy_id\",
						OrgFarmacyIndex.OrgFarmacyIndex_id as \"OrgFarmacyIndex_id\",
						rtrim(DD.OrgFarmacy_Name) as \"OrgFarmacy_Name\",
						rtrim(DD.OrgFarmacy_HowGo) as \"OrgFarmacy_HowGo\",
						YesNo.YesNo_Code as \"OrgFarmacy_IsFarmacy\",
						to_char(SUM(DD.DrugOstat_Kolvo), '{$callObject->numericForm18_2}') as \"DrugOstat_Kolvo\",
						coalesce(OST.OMSSprTerr_Code, 0) as \"OMSSprTerr_Code\"
					from
						v_DrugOstat DD
						inner join v_ReceptFinance RF on RF.ReceptFinance_id = DD.ReceptFinance_id and RF.ReceptFinance_Code = :ReceptFinance_Code
						inner join v_OrgFarmacy OrgFarmacy on OrgFarmacy.OrgFarmacy_id = DD.OrgFarmacy_id
						inner join v_Org Org on Org.Org_id = OrgFarmacy.Org_id
						left join OrgFarmacyIndex on OrgFarmacyIndex.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id and OrgFarmacyIndex.Lpu_id = :Lpu_id
						left join YesNo on YesNo.YesNo_id = coalesce(DD.OrgFarmacy_IsFarmacy, 2)
						left join Address PAddr on PAddr.Address_id = Org.PAddress_id
						left join OMSSprTerr OST on coalesce(OST.KLRgn_id, 0) = coalesce(PAddr.KLRgn_id, 0)
							and coalesce(OST.KLSubRgn_id, 0) = coalesce(PAddr.KLSubRgn_id, 0)
							and coalesce(OST.KLCity_id, 0) = coalesce(PAddr.KLCity_id, 0)
							and coalesce(OST.KLTown_id, 0) = coalesce(PAddr.KLTown_id, 0)
					where DD.Drug_id = :Drug_id
					  and DD.OrgFarmacy_id <> 1
					group by
						OrgFarmacy.OrgFarmacy_id,
						OrgFarmacyIndex.OrgFarmacyIndex_id,
						DD.OrgFarmacy_Name,
						DD.OrgFarmacy_HowGo,
						YesNo.YesNo_Code,
						OMSSprTerr_Code
					having coalesce(SUM(DD.DrugOstat_Kolvo), 0) > 0
				";
				break;
		}
		$queryParams = [
			"Drug_id" => $data["Drug_id"],
			"Lpu_id" => $data["Lpu_id"],
			"ReceptFinance_Code" => $data["ReceptFinance_Code"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Еще одно получение списка аптек с остатками по выбранному медикаменту
	 * @param $data
	 * @return array|bool
	 */
	public static function getDrugOstatGrid(Drug_model $callObject, $data)
	{
		$filter = "";
		$queryParams = [
			"Drug_id" => $data["Drug_id"],
			"Lpu_id" => $data["Lpu_id"]
		];
		if (isset($data["org_farm_filter"])) {
			$filter .= " and ( drugostat.OrgFarmacy_Name ilike :OrgFarmacyFilter or drugostat.OrgFarmacy_HowGo ilike :OrgFarmacyFilter )";
			$queryParams["OrgFarmacyFilter"] = "%" . $data["org_farm_filter"] . "%";
		}
		$sql = "
			select
				1 as \"DrugOstat_id\",
				drugostat.OrgFarmacy_id as \"OrgFarmacy_id\",
				drugostat.OrgFarmacy_Name as \"OrgFarmacy_Name\",
				drugostat.OrgFarmacy_HowGo as \"OrgFarmacy_HowGo\",
				drugostat.Drug_id as \"Drug_id\",
				drugostat.DrugOstat_Kolvo as \"DrugOstat_Kolvo\",
				to_char(sum(case when drugostat.ReceptFinance_id = 1 then case when drugostat.OrgFarmacy_id = 1 then -drugostat.DrugOstat_Kolvo else drugostat.DrugOstat_Kolvo end end), '{$callObject->numericForm18_2}') as \"DrugOstat_Fed\",
				to_char(sum(case when drugostat.ReceptFinance_id = 2 then case when drugostat.OrgFarmacy_id = 1 then -drugostat.DrugOstat_Kolvo else drugostat.DrugOstat_Kolvo end end), '{$callObject->numericForm18_2}') as \"DrugOstat_Reg\",
				to_char(sum(case when drugostat.ReceptFinance_id = 3 then case when drugostat.OrgFarmacy_id = 1 then -drugostat.DrugOstat_Kolvo else drugostat.DrugOstat_Kolvo end end), '{$callObject->numericForm18_2}') as \"DrugOstat_7Noz\",
				CASE WHEN ofix.OrgFarmacyIndex_Index >= 0 THEN 'true' ELSE 'false' END as \"OrgFarmacy_IsVkl\",
				to_char(drugostat.DrugOstat_setDT, '{$callObject->dateTimeForm104}') as \"DrugOstat_setDT\",
				to_char(drugostat.DrugOstat_updDT, '{$callObject->dateTimeForm104}') as \"DrugOstat_updDT\"
			from
				v_drugostat drugostat
				left join OrgFarmacyIndex ofix on ofix.OrgFarmacy_id = drugostat.OrgFarmacy_id and ofix.Lpu_id=:Lpu_id
			where  drugostat.Drug_id = :Drug_id
				{$filter}
			group by
				drugostat.OrgFarmacy_id,
				drugostat.OrgFarmacy_Name,
				drugostat.OrgFarmacy_HowGo,
				drugostat.Drug_id,
				ofix.OrgFarmacyIndex_Index,
				drugostat.DrugOstat_Kolvo,
				drugostat.DrugOstat_setDT,
				drugostat.DrugOstat_updDT
			having sum(drugostat.drugostat_kolvo) > 0
			order by
				-ofix.OrgFarmacyIndex_Index DESC,
				drugostat.OrgFarmacy_Name
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение последней даты обновления остатков
	 * @return array|bool
	 */
	public static function getDrugOstatUpdateTime(Drug_model $callObject)
	{
		$sql = "
			select to_char(max(DrugOstat_insDT), '{$callObject->dateTimeForm104}')|| ' '||to_char(max(DrugOstat_insDT), '{$callObject->dateTimeForm108}') as \"DrugOstatUpdateTime\"
			from v_DrugOstat
			where OrgFarmacy_id <> 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение последней даты обновления остатков
	 * @return array|bool
	 */
	public static function getDrugOstatRASUpdateTime(Drug_model $callObject)
	{
		$sql = "
			select to_char(max(DrugOstat_insDT), '{$callObject->dateTimeForm104}')|| ' '||to_char(max(DrugOstat_insDT), '{$callObject->dateTimeForm108}') as \"DrugOstatUpdateTime\"
			from v_DrugOstat
			where OrgFarmacy_id = 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение списка остатков по медикаментам по выбранной аптеке
	 * @param $data
	 * @return array|bool
	 */
	public static function getDrugOstatByFarmacyGrid(Drug_model $callObject, $data)
	{
		$mnn_filter = "";
		$queryParams = ["OrgFarmacy_id" => $data["OrgFarmacy_id"]];
		$torg_filter = "";
		if (isset($data["mnn"])) {
			$mnn_filter = " and DrugMnn.DrugMnn_Name ilike :Mnn";
			$queryParams["Mnn"] = $data["mnn"] . "%";
		}
		if (isset($data["torg"])) {
			$torg_filter = " and Drug.Drug_Name ilike :Torg";
			$queryParams["Torg"] = $data["torg"] . "%";
		}
		$sql = "
            SELECT * FROM (
			select
				1 as \"DrugOstat_id\",
				drugostat.OrgFarmacy_id as \"OrgFarmacy_id\",
				drugostat.Drug_id as \"Drug_id\",
				drugostat.Drug_Name as \"Drug_Name\",
				Drug.Drug_CodeG as \"Drug_CodeG\",
				DrugMnn.DrugMnn_Name as \"DrugMnn_Name\",
				to_char(sum(case when drugostat.ReceptFinance_id = 1 then drugostat.DrugOstat_Kolvo end), '{$callObject->numericForm18_2}') as \"DrugOstat_Fed\",
				to_char(sum(case when drugostat.ReceptFinance_id = 2 then drugostat.DrugOstat_Kolvo end), '{$callObject->numericForm18_2}') as \"DrugOstat_Reg\",
				to_char(sum(case when drugostat.ReceptFinance_id = 3 then drugostat.DrugOstat_Kolvo end), '{$callObject->numericForm18_2}') as \"DrugOstat_7Noz\",
				to_char(drugostat.DrugOstat_setDT, '{$callObject->dateTimeForm104}') as \"DrugOstat_setDT\",
				to_char(drugostat.DrugOstat_updDT, '{$callObject->dateTimeForm104}') as \"DrugOstat_updDT\"
			from
				v_drugostat drugostat
				left join v_Drug Drug on drugostat.Drug_id = Drug.Drug_id
				left join v_DrugMnn DrugMnn on Drug.DrugMnn_id = DrugMnn.DrugMnn_id
			where
				drugostat.OrgFarmacy_id=:OrgFarmacy_id
				{$torg_filter} {$mnn_filter}
			group by
				drugostat.OrgFarmacy_id,
				drugostat.Drug_id,
				drugostat.Drug_Name,
				Drug.Drug_CodeG,
				DrugMnn.DrugMnn_Name,
				drugostat.DrugOstat_setDT,
				drugostat.DrugOstat_updDT
			having sum(drugostat.drugostat_kolvo) > 0
			) t
			order by \"Drug_Name\" 
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение списка подразделений МО прикрепленных к аптеке
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuBuildingLinkedByOrgFarmacy(Drug_model $callObject, $data)
	{
		$queryParams = [
			"Lpu_id" => $data["Lpu_id"],
			"OrgFarmacy_id" => $data["OrgFarmacy_id"],
			"WhsDocumentCostItemType_id" => $data["WhsDocumentCostItemType_id"]
		];
		$sql = "
			select
				lb.LpuBuilding_id as \"LpuBuilding_id\",
				lb.LpuBuilding_Name as \"LpuBuilding_Name\",
				(case
					when coalesce(ofix.is_narko_cnt, 0) > 0 and coalesce(ofix.is_not_narko_cnt, 0) > 0 then 1
					when coalesce(ofix.is_narko_cnt, 0) = 0 and coalesce(ofix.is_not_narko_cnt, 0) > 0 then 2
					when coalesce(ofix.is_narko_cnt, 0) > 0 and coalesce(ofix.is_not_narko_cnt, 0) = 0 then 3
					else null
				end) as \"LsGroup_id\",
				(case
					when ofix.OrgFarmacyIndex_id is not null then 'true'
					else 'false'
				end) as \"IsVkl\",
				(case
					when ofix.OrgFarmacyIndex_id is not null then 'saved'
					else ''
				end) as \"state\"
			from
				v_LpuBuilding lb
				left join lateral (
					select
						max(i_ofix.OrgFarmacyIndex_id) as OrgFarmacyIndex_id,
						sum(case when coalesce(i_ofix.OrgFarmacyIndex_IsNarko, 0) = 2 then 1 else 0 end) as is_narko_cnt,
						sum(case when coalesce(i_ofix.OrgFarmacyIndex_IsNarko, 0) = 1 then 1 else 0 end) as is_not_narko_cnt
					from v_OrgFarmacyIndex i_ofix
					where i_ofix.lpu_id = :Lpu_id
					  and i_ofix.OrgFarmacy_id = :OrgFarmacy_id
					  and coalesce(i_ofix.WhsDocumentCostItemType_id, 0) = coalesce(:WhsDocumentCostItemType_id, 0)
					  and i_ofix.LpuBuilding_id = lb.LpuBuilding_id
				) as ofix on true
				left join lateral (
					select i_lu.LpuUnit_id
					from v_LpuUnit i_lu
					where i_lu.LpuBuilding_id = lb.LpuBuilding_id
					  and i_lu.LpuUnitType_SysNick in ('fap', 'polka')
				    limit 1
				) as lu on true
			where lb.Lpu_id = :Lpu_id
			  and lu.LpuUnit_id is not null
			order by lb.LpuBuilding_Name
		";
		$result = $callObject->queryResult($sql, $queryParams);
		return $result;
	}

	/**
	 * Получение списка подразделений МО прикрепленных к аптеке склада
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuBuildingStorageLinkedByOrgFarmacy(Drug_model $callObject, $data)
	{
		$queryParams = [
			"Lpu_id" => $data["Lpu_id"],
			"OrgFarmacy_id" => $data["OrgFarmacy_id"],
			"WhsDocumentCostItemType_id" => $data["WhsDocumentCostItemType_id"]
		];
		$sql = "
			select
				ofix.OrgFarmacyIndex_id as \"OrgFarmacyIndex_id\",
				(case
					when coalesce(ofix.OrgFarmacyIndex_IsNarko, 0) = 1 then 1
					when coalesce(ofix.OrgFarmacyIndex_IsNarko, 0) = 2 then 2
					else 3
				end) as \"LsGroup_id\",
				(case
					when coalesce(ofix.OrgFarmacyIndex_IsNarko, 0) = 1 then 'Все кроме НС и ПВ'
					when coalesce(ofix.OrgFarmacyIndex_IsNarko, 0) = 2 then 'НС и ПВ'
					else ''
				end) as \"LsGroup_Name\",
				lb.LpuBuilding_id as \"LpuBuilding_id\",
				lb.LpuBuilding_Name as \"LpuBuilding_Name\",
				ofix.Storage_id as \"Storage_id\",
				s.Storage_Name as \"Storage_Name\"
			from
				v_OrgFarmacyIndex ofix
				left join v_LpuBuilding lb on lb.LpuBuilding_id = ofix.LpuBuilding_id
				left join v_Storage s on s.Storage_id = ofix.Storage_id
			where ofix.lpu_id = :Lpu_id
			  and ofix.OrgFarmacy_id = :OrgFarmacy_id
			  and coalesce(ofix.WhsDocumentCostItemType_id, 0) = coalesce(:WhsDocumentCostItemType_id, 0)
			  and ofix.LpuBuilding_id is not null
			order by
				lb.LpuBuilding_Name, 
				lb.LpuBuilding_id,
				coalesce(ofix.OrgFarmacyIndex_IsNarko, 0);
		";
		$result = $callObject->queryResult($sql, $queryParams);
		return $result;
	}

	/**
	 * Получение списка прикрепления МО/подразделений МО к аптеке
	 * @param $data
	 * @return array|bool
	 */
	public static function GetMoByFarmacy(Drug_model $callObject, $data)
	{
		$queryParams = [
			"Lpu_id" => $data["Lpu_id"],
			"OrgFarmacy_id" => $data["OrgFarmacy_id"],
			"WhsDocumentCostItemType_id" => null
		];
		
		if (isset($data['WhsDocumentCostItemType_id'])) {
			$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		}
		//TODO 111
		$sql = "
	        select
	        	LpuBuilding_id,
				LpuBuilding_Name,
	            case when( moAttachLS = 1 and OrgFarmacyLS_id = :OrgFarmacy_id) or (moAttachNS = 1 and OrgFarmacyNS_id = :OrgFarmacy_id)
	            	then 1 
	            	else 0 
	            end moAttach,
				moAttachLS,
				OrgFarmacyLS_id,
				OrgFarmacyIndexLS_id,
				moAttachNS,
				OrgFarmacyNS_id,
				OrgFarmacyIndexNS_id,
                Attach_Other  
            from fn_MoByFarmacy(:Lpu_id, :OrgFarmacy_id, :WhsDocumentCostItemType_id)
        ";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		if (is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение полного списка аптек
	 * @param $data
	 * @return array|bool
	 */
	public static function getOrgFarmacyGrid(Drug_model $callObject, $data)
	{
		$filter = "";
		$join = "";
		$queryParams = [
			"Lpu_id" => $data["Lpu_id"]
		];
		if (isset($data["orgfarm"])) {
			$filter .= " and (ofr.OrgFarmacy_Name ilike :OrgFarmacy_Name or ofr.OrgFarmacy_HowGo ilike :OrgFarmacy_Name)";
			$queryParams["OrgFarmacy_Name"] = "%" . $data["orgfarm"] . "%";
		}
		if (isset($data["OrgFarmacys"])) {
			$filter .= " and (ofr.OrgFarmacy_id in (:OrgFarmacyList))";
			$queryParams["OrgFarmacyList"] = $data["OrgFarmacys"];
		}
		if ((isset($data["mnn"])) || (isset($data["torg"]))) {
			$join .= "
				inner join v_DrugOstat drugostat_mnn on drugostat_mnn.OrgFarmacy_id = ofr.OrgFarmacy_id and DrugOstat_Kolvo > 0
				inner join v_Drug drug1 on drugostat_mnn.Drug_id = drug1.Drug_id and drug1.Drug_IsDel = 1
			";
			if (isset($data['torg'])) {
				$filter .= " and drug1.Drug_Name ilike :Drug_Name";
				$queryParams['Drug_Name'] = $data['torg'] . "%";
			}
			if (isset($data["mnn"])) {
				$join .= "
					inner join v_DrugMnn drugmnn1 on drug1.DrugMnn_id = drugmnn1.DrugMnn_id and drugmnn1.DrugMnn_Name ilike :DrugMnn_Name
				";
				$queryParams["DrugMnn_Name"] = $data["mnn"] . "%";
			}
		}
		if (!empty($data["onlyAttachLpu"])) {
			$filter_pers = "";
			if (!empty($data["Person_id"])) {
				// выводить любимую аптеку человека, даже если она не прикреплена к МО
				$filter_pers = "
					or exists(
						select ofp.OrgFarmacyPerson_id
						from v_OrgFarmacyPerson ofp
						where ofp.Person_id = :Person_id and ofp.OrgFarmacy_id = ofr.OrgFarmacy_id
					)
				";
				$queryParams["Person_id"] = $data["Person_id"];
			}
			$filter .= " and (ofix.OrgFarmacyIndex_id is not null{$filter_pers})";
		}
		$fields = "";
		if (!empty($data["Person_id"])) {
			// отдадим признак является ли аптека любимой для пациента
			$fields .= ", case when exists(
					select ofp.OrgFarmacyPerson_id
					from v_OrgFarmacyPerson ofp
					where ofp.Person_id = :Person_id
					  and ofp.OrgFarmacy_id = ofr.OrgFarmacy_id
					) then 2 else 1 end
				as OrgFarmacy_IsFavorite
			";
			$queryParams["Person_id"] = $data["Person_id"];
		}
		$query = "
			select distinct
				ofr.OrgFarmacy_id as \"OrgFarmacy_id\",
				ofr.Org_id as \"Org_id\",
				ofr.OrgFarmacy_Code as \"OrgFarmacy_Code\",
				ofr.OrgFarmacy_Name as \"OrgFarmacy_Name\",
				ofr.OrgFarmacy_HowGo as \"OrgFarmacy_HowGo\",
				(case when coalesce(ofix.OrgFarmacyIndex_id, 1) = 1 then 0 else 1 end) as \"OrgFarmacy_Vkl\",
				(case when coalesce(ofix.OrgFarmacyIndex_id, 1) = 1 then 'false' else 'true' end) as \"OrgFarmacy_IsVkl\",
				ofix.OrgFarmacyIndex_Index as \"OrgFarmacyIndex_Index\",
				ofix.OrgFarmacyIndex_id as \"OrgFarmacyIndex_id\"
				{$fields}
			from
				v_OrgFarmacy ofr 
				left join OrgFarmacyIndex ofix on ofr.OrgFarmacy_id = ofix.OrgFarmacy_id and ofix.Lpu_id = :Lpu_id
				{$join}
			where (1 = 1) 
				{$filter}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение полного списка аптек (для формы просмотра прикрепления к МО)
	 * @param $data
	 * @return array|bool
	 */
	public static function getOrgFarmacyGridByLpu(Drug_model $callObject, $data)
	{
		$filter = "";
		$join = "";
		$queryParams = [
			"Lpu_id" => $data["Lpu_id"],
			"WhsDocumentCostItemType_id" => $data["WhsDocumentCostItemType_id"]
		];
		if (isset($data["OrgFarmacy_id"])) {
			$filter .= " and ofr.OrgFarmacy_id = :OrgFarmacy_id";
			$queryParams["OrgFarmacy_id"] = $data["OrgFarmacy_id"];
		}
		if (!empty($data["show_storage"])) {
			$subWhereString = "
					ofr2.Org_id = ofr.Org_id
				and ofix2.Lpu_id = ofix.Lpu_id
				and coalesce(ofix2.WhsDocumentCostItemType_id, 0) = coalesce(:WhsDocumentCostItemType_id, 0)
			";
			$lb_sub_query = "
				select
					string_agg(LpuBuilding_Name||coalesce(' (' || str || ')', '') || coalesce(' / ' || Storage_Name, ''), '$ ')
				from (
					select
						bil.LpuBuilding_Name,
						is_narko.str,
						s.Storage_Name
					from v_OrgFarmacy ofr2
						left join v_OrgFarmacyIndex ofix2 on ofr2.OrgFarmacy_id = ofix2.OrgFarmacy_id
						left join v_LpuBuilding bil on bil.LpuBuilding_id = ofix2.LpuBuilding_id
						left join v_Storage s on s.Storage_id = ofix2.Storage_id
						left join lateral (
							select (case
								when coalesce(ofix2.OrgFarmacyIndex_IsNarko, 0) = 2 then 'НС и ПВ'
								when coalesce(ofix2.OrgFarmacyIndex_IsNarko, 0) = 1 then 'Все кроме НС и ПВ'
								else null
							end) as str
						) is_narko ON true 
					where {$subWhereString}
					order by bil.LpuBuilding_Name
				) sbq
			";
		} else {
			$subWhereString = "
					ofr2.Org_id = ofr.Org_id
				and ofix2.Lpu_id = ofix.Lpu_id
				and coalesce(ofix2.WhsDocumentCostItemType_id, 0) = coalesce(:WhsDocumentCostItemType_id, 0)
			";
			$lb_sub_query = "
				select
					string_agg(LpuBuilding_Name, '$ ')
				from (
					select
						bil.LpuBuilding_Name
					from
						v_OrgFarmacy ofr2 
						left join v_OrgFarmacyIndex ofix2 on ofr2.OrgFarmacy_id = ofix2.OrgFarmacy_id
						left join v_LpuBuilding bil on bil.LpuBuilding_id = ofix2.LpuBuilding_id
					where {$subWhereString}
					group by bil.LpuBuilding_Name 
					order by 1
				) sbq
			";
		}
		$query = "
			select 
				t.OrgFarmacyIndex_id as \"OrgFarmacyIndex_id\",
				t.OrgFarmacyIndex_Index as \"OrgFarmacyIndex_Index\",
				t.OrgFarmacy_id as \"OrgFarmacy_id\",
				t.Org_id as \"Org_id\",
				t.OrgFarmacy_Code as \"OrgFarmacy_Code\",
				t.OrgFarmacy_Name as \"OrgFarmacy_Name\",
				t.OrgFarmacy_Nick as \"OrgFarmacy_Nick\", 
				t.OrgFarmacy_HowGo as \"OrgFarmacy_HowGo\",
				t.OrgFarmacy_Vkl as \"OrgFarmacy_Vkl\",
				t.OrgFarmacy_IsVkl as \"OrgFarmacy_IsVkl\",
                t.OrgFarmacy_IsNarko as \"OrgFarmacy_IsNarko\",
				t.Lpu_id as \"Lpu_id\",
				(case
					when t.OrgFarmacy_Vkl = 1 and length(t.LpuBuilding_Name) = 0
					then 'Все подразделения' 
					else replace(substring(t.LpuBuilding_Name, 1 , length(t.LpuBuilding_Name)), '$', '<br/>')
				end) as \"LpuBuilding_Name\",
				wdcit.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
				t.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				(case
					when coalesce(ofix.is_narko_cnt, 0) > 0 and coalesce(ofix.is_not_narko_cnt, 0) > 0 then 'Все ЛП' 
					when coalesce(ofix.is_narko_cnt, 0) = 0 and coalesce(ofix.is_not_narko_cnt, 0) > 0 then 'Все кроме НС и ПВ'
					when coalesce(ofix.is_narko_cnt, 0) > 0 and coalesce(ofix.is_not_narko_cnt, 0) = 0 then 'НС и ПВ'
					else null
				end) as \"LsGroup_Name\"
			from (
				select 
					ofr.OrgFarmacy_id,
					ofr.Org_id,
					ofr.OrgFarmacy_Code,
					ofr.OrgFarmacy_Nick,
					ofr.OrgFarmacy_Name,
					ofr.OrgFarmacy_HowGo,
					(case
						when coalesce(ofr.OrgFarmacy_IsNarko, 1) = 1 then 'false'
						else 'true'
					end) as OrgFarmacy_IsNarko,
					({$lb_sub_query}) as LpuBuilding_Name,
					(case
						when coalesce(ofix.OrgFarmacyIndex_id, 1) = 1 then 0
						else 1
					end) as OrgFarmacy_Vkl,
					(case
						when coalesce(ofix.OrgFarmacyIndex_id, 1) = 1 then 'false'
						else 'true'
					end) as OrgFarmacy_IsVkl,
					max(ofix.OrgFarmacyIndex_Index) OrgFarmacyIndex_Index,
					min(ofix.OrgFarmacyIndex_id) OrgFarmacyIndex_id,
					ofix.Lpu_id,
					ofix.WhsDocumentCostItemType_id
				from
					v_OrgFarmacy ofr 
					left join v_OrgFarmacyIndex ofix on ofr.OrgFarmacy_id = ofix.OrgFarmacy_id
					    and ofix.Lpu_id = :Lpu_id
					    and coalesce(ofix.WhsDocumentCostItemType_id, 0) = coalesce(:WhsDocumentCostItemType_id, 0)
					{$join}
				where
					(1 = 1) 
					{$filter}
				group by
					ofr.OrgFarmacy_id,
					ofr.Org_id,
					ofr.OrgFarmacy_Code,
					ofr.OrgFarmacy_Name,
					ofr.OrgFarmacy_Nick,
					ofr.OrgFarmacy_IsNarko,
					ofix.Lpu_id,
					ofix.WhsDocumentCostItemType_id,
					ofr.OrgFarmacy_HowGo,
					(case when coalesce(ofix.OrgFarmacyIndex_id, 1) = 1 then 0 else 1 end),
					(case when coalesce(ofix.OrgFarmacyIndex_id, 1) = 1 then 'false' else 'true' end)				
    		) t
    		left join v_WhsDocumentCostItemType wdcit on wdcit.WhsDocumentCostItemType_id = t.WhsDocumentCostItemType_id
			left join lateral (
				select
					sum(case when coalesce(i_ofix.OrgFarmacyIndex_IsNarko, 0) = 2 then 1 else 0 end) as is_narko_cnt,
					sum(case when coalesce(i_ofix.OrgFarmacyIndex_IsNarko, 0) = 1 then 1 else 0 end) as is_not_narko_cnt
				from v_OrgFarmacyIndex i_ofix
				where i_ofix.lpu_id = t.Lpu_id
				  and i_ofix.OrgFarmacy_id = t.OrgFarmacy_id
				  and coalesce(i_ofix.WhsDocumentCostItemType_id, 0) = coalesce(t.WhsDocumentCostItemType_id, 0)
				  and i_ofix.LpuBuilding_id is not null
			) as ofix on true
    	";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение полного списка аптек
	 * @param array $data
	 * @return array|bool
	 */
	public static function getOrgFarmacyNetGrid(Drug_model $callObject, $data)
	{
		$query = "
			select
				ct.Org_pid as \"OrgFarmacy_id\",
				og.Org_Nick as \"OrgFarmacy_Name\"
			from
				v_Contragent ct
				inner join v_Org og on ct.Org_pid = og.Org_id
			where ct.Org_pid is not null
			group by
				ct.Org_pid,
				og.Org_Nick
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение комбобокса медикамента
	 * @param $data
	 * @return array|bool
	 */
	public static function loadDrugProtoMnnCombo(Drug_model $callObject, $data)
	{
		$where = "";
		$params = [];
		if (!empty($data["DrugProtoMnn_id"])) {
			$where .= " and DrugProtoMnn.DrugProtoMnn_id = :DrugProtoMnn_id";
			$params["DrugProtoMnn_id"] = $data["DrugProtoMnn_id"];
		}
		if (!empty($data["ReceptFinance_id"])) {
			$where .= " and DrugProtoMnn.ReceptFinance_id = :ReceptFinance_id";
			$params["ReceptFinance_id"] = $data["ReceptFinance_id"];
		}
		if (strlen($data["query"]) > 0) {
			$where .= " and DrugProtoMnn.DrugProtoMnn_Name iLike :DrugProtoMnn_Name";
			$params["DrugProtoMnn_Name"] = $data["query"] . "%";
		} else if (strlen($data["DrugProtoMnn_Name"]) > 0) {
			$where .= " and DrugProtoMnn.DrugProtoMnn_Name iLike :DrugProtoMnn_Name";
			$params["DrugProtoMnn_Name"] = $data["DrugProtoMnn_Name"] . "%";
		}
		$query = "
			select
				DrugProtoMnn.DrugProtoMnn_id as \"DrugProtoMnn_id\",
				DrugProtoMnn.DrugProtoMnn_Name as \"DrugProtoMnn_Name\",
				DrugProtoMnn.ReceptFinance_id as \"ReceptFinance_id\"
			From v_DrugProtoMnn DrugProtoMnn
			where DrugProtoMnn.DrugProtoMnn_Name <> '~'
			{$where}
			order by DrugProtoMnn.DrugProtoMnn_Name
			limit 50
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение списка МНН с возможностью фильтрации по части наименования
	 * @param $data
	 * @return array|bool
	 */
	public static function loadDrugMnnGrid(Drug_model $callObject,$data)
	{
		$filter = "";
		$queryParams = [];
		if (isset($data["DrugMnn_Name"])) {
			$filter = "where DrugMnn_Name ilike :DrugMnn_Name";
			$queryParams["DrugMnn_Name"] = $data["DrugMnn_Name"] . "%";
		}
		switch ($data["privilegeType"]) {
			case "fed":
				$drug_mnn_table = "v_DrugFed";
				break;
			case "noz":
				$drug_mnn_table = "v_Drug7noz";
				break;
			case "reg":
				$drug_mnn_table = "v_DrugReg";
				break;
			default:
				$drug_mnn_table = "DrugMnn";
				break;
		}
		$selectString = "
			DrugMnn_id as \"DrugMnn_id\",
			DrugMnn_Code as \"DrugMnn_Code\",
			rtrim(DrugMnn_Name) as \"DrugMnn_Name\",
			rtrim(DrugMnn_NameLat) as \"DrugMnn_NameLat\"
		";
		$query = "
            SELECT * FROM (
			select distinct
			{$selectString}
			from {$drug_mnn_table}
			{$filter}
			) t
			order by \"DrugMnn_Name\"
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение списка торговых наименований с возможностью фильтрации по части наименования
	 * @param $data
	 * @return array|bool
	 */
	public static function loadDrugTorgGrid(Drug_model $callObject,$data)
	{
		$filter = "";
		$queryParams = [];
		if (isset($data["DrugTorg_Name"])) {
			$filter = "where DrugTorg_Name ilike :DrugTorg_Name";
			$queryParams["DrugTorg_Name"] = $data["DrugTorg_Name"] . "%";
		}
		$query = "
			select
				DrugTorg_id as \"DrugTorg_id\",
				DrugTorg_Code as \"DrugTorg_Code\",
				rtrim(DrugTorg_Name) as \"DrugTorg_Name\",
				rtrim(DrugTorg_NameLat) as \"DrugTorg_NameLat\"
			from v_DrugTorg DrugTorg
			{$filter}
			order by DrugTorg_Name
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 *  Получение списка остатков по медикаменту в аптеках
	 * @param $data
	 * @param $options
	 * @return array|bool
	 */
	public static function loadFarmacyOstatList(Drug_model $callObject,$data, $options)
	{
		$queryParams = [];
		$recept_drug_ostat_control = $options["recept_drug_ostat_control"];
		if (isset($data["OrgFarmacy_id"])) {
			if ($data["ReceptFinance_Code"] == 3) {
				$data["ReceptFinance_Code"] = 1;
			}
			$query = "
				select
					OrgFarmacy.OrgFarmacy_id as \"OrgFarmacy_id\",
					rtrim(Org.Org_Name) as \"OrgFarmacy_Name\",
					rtrim(OrgFarmacy.OrgFarmacy_HowGo) as \"OrgFarmacy_HowGo\",
					YesNo.YesNo_Code as \"OrgFarmacy_IsFarmacy\",
					case when coalesce(DrugOstat.DrugOstat_Kolvo, 0) <= 0
					    then 0
					    else to_char(coalesce(DrugOstat.DrugOstat_Kolvo, 0), '{$callObject->numericForm18_2}')
					end as \"DrugOstat_Kolvo\"
				from
					v_OrgFarmacy OrgFarmacy
					inner join v_Org Org on Org.Org_id = OrgFarmacy.Org_id
					inner join YesNo on YesNo.YesNo_id = coalesce(OrgFarmacy.OrgFarmacy_IsFarmacy, 2)
					left join lateral (
						select SUM(DOA.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from
					    	v_DrugOstat DOA
							inner join v_ReceptFinance ReceptFinance on DOA.ReceptFinance_id = ReceptFinance.ReceptFinance_id and ReceptFinance.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
						  and DOA.Drug_id = :Drug_id
					) as DrugOstat on true
				where OrgFarmacy.OrgFarmacy_id = :OrgFarmacy_id
				  and coalesce(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2
			";
			$queryParams["Drug_id"] = $data["Drug_id"];
			$queryParams["OrgFarmacy_id"] = $data["OrgFarmacy_id"];
			$queryParams["ReceptFinance_Code"] = $data["ReceptFinance_Code"];
		} else if ($data["ReceptFinance_Code"] == 3) {
			// 7 нозологий
			$data["ReceptFinance_Code"] = 1;
			$query = "
				select
					OrgFarmacy.OrgFarmacy_id as \"OrgFarmacy_id\",
					rtrim(Org.Org_Name) as \"OrgFarmacy_Name\",
					rtrim(OrgFarmacy.OrgFarmacy_HowGo) as \"OrgFarmacy_HowGo\",
					YesNo.YesNo_Code as \"OrgFarmacy_IsFarmacy\",
					case when coalesce(DrugOstat.DrugOstat_Kolvo, 0) <= 0
					    then 0
					    else coalesce(DrugOstat.DrugOstat_Kolvo, 0)
					end as \"DrugOstat_Kolvo\"
				from
					v_OrgFarmacy OrgFarmacy
					inner join v_Org Org on Org.Org_id = OrgFarmacy.Org_id
					inner join YesNo on YesNo.YesNo_id = coalesce(OrgFarmacy.OrgFarmacy_IsFarmacy, 2)
					left join lateral (
						select SUM(DOA.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from
							v_DrugOstat DOA
							inner join v_ReceptFinance ReceptFinance on DOA.ReceptFinance_id = ReceptFinance.ReceptFinance_id and ReceptFinance.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
						  and DOA.Drug_id = :Drug_id
					) as DrugOstat on true
				where coalesce(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2
				  and coalesce(OrgFarmacy.OrgFarmacy_IsNozLgot, 2) = 2
				  and OrgFarmacy.OrgFarmacy_id <> 1
			";
			$queryParams["Drug_id"] = $data["Drug_id"];
			$queryParams["ReceptFinance_Code"] = $data["ReceptFinance_Code"];
		} else if ($data["ReceptType_Code"] == 1) {
			// Тип рецепта "На бланке"
			$query = "
				select
					OrgFarmacy.OrgFarmacy_id as \"OrgFarmacy_id\",
					rtrim(Org.Org_Name) as \"OrgFarmacy_Name\",
					rtrim(OrgFarmacy.OrgFarmacy_HowGo) as \"OrgFarmacy_HowGo\",
					YesNo.YesNo_Code as \"OrgFarmacy_IsFarmacy\",
					case when coalesce(DrugOstat.DrugOstat_Kolvo, 0) <= 0
					    then 0
					    else coalesce(DrugOstat.DrugOstat_Kolvo, 0)
					end as \"DrugOstat_Kolvo\"
				from
					v_OrgFarmacy OrgFarmacy
					inner join OrgFarmacyIndex on OrgFarmacyIndex.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id and OrgFarmacyIndex.Lpu_id = :Lpu_id
					inner join v_Org Org on Org.Org_id = OrgFarmacy.Org_id
					inner join YesNo on YesNo.YesNo_id = coalesce(OrgFarmacy.OrgFarmacy_IsFarmacy, 2)
					left join lateral (
						select SUM(DOA.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from
							v_DrugOstat DOA
							inner join v_ReceptFinance ReceptFinance on DOA.ReceptFinance_id = ReceptFinance.ReceptFinance_id and ReceptFinance.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
						  and DOA.Drug_id = :Drug_id
					) as DrugOstat on true
				where coalesce(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2
				  and OrgFarmacy.OrgFarmacy_id <> 1
			";
			$queryParams["Drug_id"] = $data["Drug_id"];
			$queryParams["Lpu_id"] = $data["Lpu_id"];
			$queryParams["ReceptFinance_Code"] = $data["ReceptFinance_Code"];
		} else {
			$query = "
				select
					OrgFarmacy.OrgFarmacy_id as \"OrgFarmacy_id\",
					rtrim(Org.Org_Name) as \"OrgFarmacy_Name\",
					rtrim(OrgFarmacy.OrgFarmacy_HowGo) as \"OrgFarmacy_HowGo\",
					YesNo.YesNo_Code as \"OrgFarmacy_IsFarmacy\",
					case when coalesce(DrugOstat.DrugOstat_Kolvo, 0) <= 0
					    then 0
					    else coalesce(DrugOstat.DrugOstat_Kolvo, 0)
					end as \"DrugOstat_Kolvo\"
				from
					v_OrgFarmacy OrgFarmacy
					inner join OrgFarmacyIndex on OrgFarmacyIndex.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id and OrgFarmacyIndex.Lpu_id = :Lpu_id
					inner join v_Org Org on Org.Org_id = OrgFarmacy.Org_id
					inner join YesNo on YesNo.YesNo_id = coalesce(OrgFarmacy.OrgFarmacy_IsFarmacy, 2)
					left join lateral (
						select SUM(DOA.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from
							v_DrugOstat DOA
							inner join v_ReceptFinance ReceptFinance on DOA.ReceptFinance_id = ReceptFinance.ReceptFinance_id and ReceptFinance.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id = OrgFarmacy.OrgFarmacy_id
						  and DOA.Drug_id = :Drug_id
					) as DrugOstat on true
					left join lateral (
						select SUM(DOA.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from
							v_DrugOstat DOA
							inner join v_ReceptFinance ReceptFinance on DOA.ReceptFinance_id = ReceptFinance.ReceptFinance_id and ReceptFinance.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id = 1
						  and DOA.Drug_id = :Drug_id
					) as RAS_Ostat on true
				where coalesce(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2
			";
			if ($recept_drug_ostat_control) {
				if (isSuperadmin()) {
					$query .= " and (coalesce(DrugOstat.DrugOstat_Kolvo, 0) > 0 or coalesce(RAS_Ostat.DrugOstat_Kolvo, 0) > 0)";
				} else {
					$query .= " and coalesce(RAS_Ostat.DrugOstat_Kolvo, 0) > 0";
				}
			}
			$queryParams["Drug_id"] = $data["Drug_id"];
			$queryParams["Lpu_id"] = $data["Lpu_id"];
			$queryParams["ReceptFinance_Code"] = $data["ReceptFinance_Code"];
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение списка медикаментов в комбобокс в рецепте
	 * @param $data
	 * @param $options
	 * @return array|bool
	 */
	public static function loadDrugList(Drug_model $callObject,$data, $options)
	{
		$queryParams = [];
		$filter = "
			and Drug.Drug_begDate < :Date
			and (Drug.Drug_endDate is null or Drug.Drug_endDate > :Date)
		";
		$mi_1_join = "";
		$mi_1_where = "";
		$recept_drug_ostat_control = $options["recept_drug_ostat_control"];
		if (isset($data["is_mi_1"]) && ($data["is_mi_1"] == "true") && !isset($data["Drug_id"])) {
			$mi_1_join = "
				inner join rls.DrugNomen DN on DN.DrugNomen_Code = Drug.Drug_CodeG
				left join rls.Drug RD on RD.Drug_id = DN.Drug_id
				left join rls.Prep P on P.Prep_id = RD.DrugPrep_id
				left join rls.CLSNTFR NTFR on NTFR.CLSNTFR_ID = P.NTFRID
			";
			$mi_1_where = "
				and NTFR.PARENTID not in (1, 176) and NTFR.CLSNTFR_ID not in (1, 137, 138, 139, 140, 141, 142, 144, 146, 149, 153, 159, 176, 180, 181, 184, 199, 207)
			";
		}
		if (isset($data["query"])) {
			switch ($data["mode"]) {
				case "any":
					$data["query"] = $data["query"] . "%";
					break;

				case "start":
					$data["query"] .= "%";
					break;
			}
			$filter .= " and Drug.Drug_Name iLIKE :query";
			$queryParams["query"] = $data["query"] . "%";
		}
		if (isset($data["DrugMnn_id"])) {
			$filter .= " and Drug.DrugMnn_id = :DrugMnn_id";
			$queryParams["DrugMnn_id"] = $data["DrugMnn_id"];
		}
		//Если не задан один из этих параметров, не выполняем запрос
		if (!isset($queryParams["query"]) && !isset($queryParams["DrugMnn_id"]) && !isset($data["Drug_id"])) {
			return false;
		}
		if ($data["Drug_id"] > 0) {
			if ($data["ReceptFinance_Code"] == 3) {
				$data["ReceptFinance_Code"] = 1;
			}
			$query = "
				select
					Drug.Drug_id as \"Drug_id\",
					Drug.Drug_Code as \"Drug_Code\",
					rtrim(Drug.Drug_Name) as \"Drug_Name\",
					Drug.DrugMnn_id as \"DrugMnn_id\",
					to_char(DrugPrice.DrugState_Price, '{$callObject->numericForm18_2}') as \"Drug_Price\",
					Drug.Drug_IsKek as \"Drug_IsKEK\",
					coalesce(Drug_IsKEK.YesNo_Code, 0) as \"Drug_IsKEK_Code\",
					0 as \"DrugOstat_Flag\"
				from
					v_Drug Drug
					left join v_DrugPrice DrugPrice on DrugPrice.Drug_id = Drug.Drug_id
						and DrugPrice.DrugProto_id = (
							select max(DP.DrugProto_id)
							from
								v_DrugPrice DP
								inner join v_ReceptFinance RF on RF.ReceptFinance_id = DP.ReceptFinance_id and RF.ReceptFinance_Code = :ReceptFinance_Code
							where DP.Drug_id = Drug.Drug_id
							  and DP.DrugProto_begDate <= :Date
						)
					left join YesNo Drug_IsKEK on Drug_IsKEK.YesNo_id = Drug.Drug_IsKek
					left join v_ReceptFinance ReceptFinance on ReceptFinance.ReceptFinance_id = DrugPrice.ReceptFinance_id
					left join lateral (
						select SUM(DOA.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from
							v_DrugOstat DOA
							inner join OrgFarmacyIndex on OrgFarmacyIndex.OrgFarmacy_id = DOA.OrgFarmacy_id and OrgFarmacyIndex.Lpu_id = :Lpu_id
							inner join v_ReceptFinance RF on RF.ReceptFinance_id = DOA.ReceptFinance_id and RF.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id <> 1
						  and DOA.Drug_id = Drug.Drug_id
					) as Farm_Ostat on true
					left join lateral (
						select SUM(DOA.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from
							v_DrugOstat DOA
							inner join v_ReceptFinance RF on RF.ReceptFinance_id = DOA.ReceptFinance_id and RF.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id = 1
						  and DOA.Drug_id = Drug.Drug_id
					) as RAS_Ostat on true
				where Drug.Drug_id = :Drug_id
				order by \"Drug_Name\"
				limit 1
			";
			$queryParams["Drug_id"] = $data["Drug_id"];
		} else if ($data["EvnRecept_Is7Noz_Code"] == 1) {
			$query = "
				select
					Drug.Drug_id as \"Drug_id\",
					Drug.Drug_Code as \"Drug_Code\",
					RTRIM(Drug.Drug_Name) as \"Drug_Name\",
					Drug.DrugMnn_id as \"DrugMnn_id\",
					to_char(DrugPrice.DrugState_Price, {$callObject->numericForm18_2}) as \"Drug_Price\",
					Drug.Drug_IsKek as \"Drug_IsKEK\",
					coalesce(Drug_IsKEK.YesNo_Code, 0) as \"Drug_IsKEK_Code\",
					case when coalesce(Farm_Ostat.DrugOstat_Kolvo, 0) <= 0
					    then
					    	case when coalesce(RAS_Ostat.DrugOstat_Kolvo, 0) <= 0
					        	then 2
					        	else 1
					    	end
					    else 0
					end as \"DrugOstat_Flag\"
				from
					v_Drug7Noz Drug
					left join v_DrugPrice DrugPrice on DrugPrice.Drug_id = Drug.Drug_id
						and DrugPrice.DrugProto_id = (
							select max(DP.DrugProto_id)
							from
								v_DrugPrice DP
								inner join v_ReceptFinance RF on RF.ReceptFinance_id = DP.ReceptFinance_id and RF.ReceptFinance_Code = :ReceptFinance_Code
							where DP.Drug_id = Drug.Drug_id
							  and DP.DrugProto_begDate <= :Date
						)
					left join YesNo Drug_IsKEK on Drug_IsKEK.YesNo_id = Drug.Drug_IsKek
					left join lateral (
						select SUM(DOA.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from
							v_DrugOstat DOA
							inner join v_OrgFarmacy OrgFarmacy on OrgFarmacy.OrgFarmacy_id = DOA.OrgFarmacy_id
							inner join v_ReceptFinance RF on RF.ReceptFinance_id = DOA.ReceptFinance_id and RF.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id <> 1
						  and coalesce(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2
						  and coalesce(OrgFarmacy.OrgFarmacy_IsNozLgot, 2) = 2
						  and DOA.Drug_id = Drug.Drug_id
					) as Farm_Ostat on true
					left join lateral (
						select SUM(DOA.DrugOstat_Kolvo) as DrugOstat_Kolvo
						from
							v_DrugOstat DOA
							inner join v_ReceptFinance RF on RF.ReceptFinance_id = DOA.ReceptFinance_id and RF.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id = 1
						  and DOA.Drug_id = Drug.Drug_id
					) as RAS_Ostat on true
					{$mi_1_join}
				where (1 = 1) {$filter} {$mi_1_where}
				order by \"Drug_Name\"
			";
		} else {
			switch ($data["ReceptFinance_Code"]) {
				case 1:
					$table = "v_DrugFed";
					break;
				case 2:
					$table = "v_DrugReg";
					break;
				default:
					return false;
					break;
			}
			if ($data["ReceptType_Code"] != 1 && $recept_drug_ostat_control) {
				$filter .= " and coalesce(RAS_Ostat.DrugOstat_Kolvo, 0) + coalesce(Farm_Ostat.DrugOstat_Kolvo, 0) > 0";
			}
			$query = "
				SELECT
					Drug.Drug_id as \"Drug_id\",
					Drug.Drug_Code as \"Drug_Code\",
					RTRIM(Drug.Drug_Name) as \"Drug_Name\",
					Drug.DrugMnn_id as \"DrugMnn_id\",
					to_char(DrugPrice.DrugState_Price, '{$callObject->numericForm18_2}') as \"Drug_Price\",
					Drug.Drug_IsKek as \"Drug_IsKEK\",
					coalesce(Drug_IsKEK.YesNo_Code, 0) as \"Drug_IsKEK_Code\",
					case when coalesce(Farm_Ostat.DrugOstat_Kolvo, 0) <= 0
					    then
					    	case when coalesce(RAS_Ostat.DrugOstat_Kolvo, 0) <= 0
					    	    then 2
					    	    else 1
					    	end
						else 0
					end as \"DrugOstat_Flag\"
				from
					{$table}Drug
					left join v_DrugPrice DrugPrice on DrugPrice.Drug_id = Drug.Drug_id
						and DrugPrice.DrugProto_id = (
							select max(DP.DrugProto_id)
							from
					    		v_DrugPrice DP
								inner join v_ReceptFinance RF on RF.ReceptFinance_id = DP.ReceptFinance_id and RF.ReceptFinance_Code = :ReceptFinance_Code
							where DP.Drug_id = Drug.Drug_id
							  and DP.DrugProto_begDate <= :Date
						)
					left join YesNo Drug_IsKEK on Drug_IsKEK.YesNo_id = Drug.Drug_IsKek
					left join v_ReceptFinance ReceptFinance on ReceptFinance.ReceptFinance_id = DrugPrice.ReceptFinance_id
					left join lateral (
						select DOA.DrugOstat_Kolvo as DrugOstat_Kolvo
						from
					    	v_DrugOstat DOA
							inner join v_ReceptFinance RF on RF.ReceptFinance_id = DOA.ReceptFinance_id and RF.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id <> 1
						  and DOA.Drug_id = Drug.Drug_id
						  and DOA.DrugOstat_Kolvo > 0
					    limit 1
					) as Farm_Ostat on true
					left join lateral (
						select DOA.DrugOstat_Kolvo as DrugOstat_Kolvo
						from
					    	v_DrugOstat DOA
							inner join v_ReceptFinance RF on RF.ReceptFinance_id = DOA.ReceptFinance_id and RF.ReceptFinance_Code = :ReceptFinance_Code
						where DOA.OrgFarmacy_id = 1
						  and DOA.Drug_id = Drug.Drug_id
						  and DOA.DrugOstat_Kolvo > 0
					    limit 1
					) as RAS_Ostat on true
					{$mi_1_join}
				where (1 = 1)
					{$filter} {$mi_1_where}
				order by \"Drug_Name\"
			";
		}
		$queryParams["Date"] = $data["Date"];
		$queryParams["Lpu_id"] = $data["Lpu_id"];
		$queryParams["ReceptFinance_Code"] = $data["ReceptFinance_Code"];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Загрузка списка
	 * @param $data
	 * @return array|bool
	 */
	public static function loadSicknessDrugList(Drug_model $callObject, $data)
	{
		$where = "";
		if ($data["Drug_id"] > 0) {
			$where = "where Drug.Drug_id = {$data["Drug_id"]}";
		} else {
			if ($data["DrugMnn_id"] > 0) {
				$where = "where  Drug.DrugMnn_id = {$data["DrugMnn_id"]}";
			}
		}
		$selectString = "
			Drug.Drug_id as \"Drug_id\",
			Drug.Drug_Code as \"Drug_Code\",
			RTRIM(Drug.Drug_Name) as \"Drug_Name\",
			Drug.DrugMnn_id as \"DrugMnn_id\",
			to_char(DrugPrice.DrugState_Price, '{$callObject->numericForm18_2}') as \"Drug_Price\"
		";
		$fromString = "
			v_Drug Drug
			left join v_DrugPrice DrugPrice on DrugPrice.Drug_id = Drug.Drug_id
		";
		$orderByString = "\"Drug_Name\"";
		$query = "
            SELECT * FROM (
			select distinct
				{$selectString}
			from {$fromString}
			{$where}
			) t
			order by {$orderByString}
			limit 100
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Загрузка списка МНН
	 * @param $data
	 * @param $options
	 * @return array|bool
	 */
	public static function loadDrugMnnList(Drug_model $callObject, $data, $options)
	{
		$queryParams = [];
		$filter = "";
		$recept_drug_ostat_control = $options["recept_drug_ostat_control"];
		switch ($data["mode"]) {
			case "any":
				$data["query"] = $data["query"] . "%";
				break;
			case "start":
				$data["query"] .= "%";
				break;
		}
		if (isset($data["DrugMnn_id"])) {
			$queryParams["DrugMnn_id"] = $data["DrugMnn_id"];
			$query = "
                SELECT * FROM (
				select distinct
					DrugMnn.DrugMnn_id as \"DrugMnn_id\",
					DrugMnn.DrugMnn_Code as \"DrugMnn_Code\",
					RTRIM(DrugMnn.DrugMnn_Name) as \"DrugMnn_Name\"
				from
					v_Drug Drug
					inner join v_DrugMnn DrugMnn on DrugMnn.DrugMnn_id = Drug.DrugMnn_id
				where Drug.DrugMnn_id = :DrugMnn_id
				) t
				order by \"DrugMnn_Name\" 
			";
		} else {
			if ($data["EvnRecept_Is7Noz_Code"] == 1) {
				$table = "v_Drug7noz";
			} else {
				if (empty($data['byDrugRequest']) && !empty($data['WhsDocumentCostItemType_id'])) {
					$table = "dbo.fn_DrugFromDrugNormativeList({$data['WhsDocumentCostItemType_id']})";
				} else {
					if ( $data['ReceptFinance_Code'] == 1 ) {
						$table = "v_DrugFedMnn";
					} else {
						$table = "v_DrugRegMnn";
					}
				}
				if (!empty($data["byDrugRequest"])) {
					$drrfilter = "";
					switch ($data["DrugRequestRow_IsReserve"]) {
						case 1:
							$drrfilter .= " and DRR.Person_id = :Person_id";
							$queryParams['Person_id'] = $data['Person_id'];
							break;
						case 2:
							$drrfilter .= " and DRR.Person_id is null";
							if (!empty($data["MedPersonal_id"])) {
								$drrfilter .= " and DR.MedPersonal_id = :MedPersonal_id";
								$queryParams["MedPersonal_id"] = $data["MedPersonal_id"];
							}
							if (!empty($data["DrugProtoMnn_id"])) {
								$drrfilter .= " and DRR.DrugProtoMnn_id = :DrugProtoMnn_id";
								$queryParams["DrugProtoMnn_id"] = $data["DrugProtoMnn_id"];
							}
							break;
						default:
							return false;
							break;
					}
					if (isset($data["ReceptFinance_id"])) {
						if ($data["ReceptFinance_id"] == 1) {
							$drrfilter .= " and DRT.DrugRequestType_id = 1";
						} else if ($data["ReceptFinance_id"] == 2) {
							$drrfilter .= " and DRT.DrugRequestType_id = 2";
						} else {
							return false;
						}
					}
					$filter .= "
						and exists(
							select DRR.DrugRequestRow_id
							from
								DrugRequestRow DRR
								inner join v_DrugRequest DR on DR.DrugRequest_id = DRR.DrugRequest_id
								inner join v_DrugRequestPeriod DRP on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id and cast(:Date as timestamp) between DRP.DrugRequestPeriod_begDate and DRP.DrugRequestPeriod_endDate
								inner join v_DrugRequestType DRT on DRT.DrugRequestType_id = DRR.DrugRequestType_id
								inner join v_Lpu Lpu on Lpu.Lpu_id = DR.Lpu_id
								left join v_DrugProtoMnn DPM on DPM.DrugProtoMnn_id = DRR.DrugProtoMnn_id
							where
								DPM.DrugMnn_id = Drug.DrugMnn_id
								{$drrfilter}
						)
					";
				} else {
					if ($data["ReceptType_Code"] != 1 && $recept_drug_ostat_control && !in_array($data['session']['region']['nick'], array('ufa', 'perm'))) {
						// Контроль остатков только по РАС
						$filter .= " and exists (
							select 1
							from
								v_DrugOstat DrugOstat
								inner join v_OrgFarmacy OrgFarmacy on OrgFarmacy.OrgFarmacy_id = DrugOstat.OrgFarmacy_id
									and coalesce(OrgFarmacy.OrgFarmacy_IsEnabled, 2) = 2
								inner join v_Org Org on Org.Org_id = OrgFarmacy.Org_id
								inner join v_ReceptFinance ReceptFinance on ReceptFinance.ReceptFinance_id = DrugOstat.ReceptFinance_id
								left join v_DrugReserv DrugReserv on DrugOstat.Drug_id = DrugReserv.Drug_id
									and DrugOstat.OrgFarmacy_id = DrugReserv.OrgFarmacy_id
									and DrugOstat.ReceptFinance_id = DrugReserv.ReceptFinance_id
							where DrugOstat.DrugOstat_Kolvo > 0
							  and DrugOstat.DrugOstat_Kolvo > coalesce(DrugReserv.DrugReserv_Kolvo,0)
							  and ReceptFinance.ReceptFinance_Code = :ReceptFinance_Code
							  and DrugOstat.Drug_id = Drug.Drug_id
						 )
						";
						$queryParams["Lpu_id"] = $data["Lpu_id"];
					}
				}
			}
			$queryParams["Date"] = $data["Date"];
			$queryParams["query"] = $data["query"];
			$queryParams["ReceptFinance_Code"] = $data["ReceptFinance_Code"];
			$filter .= " and Drug.Drug_begDate < :Date";
			$filter .= " and (Drug.Drug_endDate is null or Drug.Drug_endDate > :Date)";
			$filter .= " and Drug.DrugMnn_Name iLIKE :query";
			$selectString = "
				Drug.DrugMnn_id as \"DrugMnn_id\",
				Drug.DrugMnn_Code as \"DrugMnn_Code\",
				rtrim(Drug.DrugMnn_Name) as \"DrugMnn_Name\"
			";
			$query = "
                SELECT * FROM (
				select distinct
				{$selectString}
				from " . $table . " Drug
				where (1 = 1)
					" . $filter . "
				) t
				order by \"DrugMnn_Name\" 
			";
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Загрузка списка комплексных мнн
	 * @param $data
	 * @param $options
	 * @return array|bool
	 */
	public static function loadDrugComplexMnnList(Drug_model $callObject, $data, $options)
	{
		$filterList = [];
		$queryParams = [];
        $region_nick = getRegionNick();

		if ($data["recept_drug_ostat_viewing"] != -1) {
			$options["recept_drug_ostat_viewing"] = $data["recept_drug_ostat_viewing"];
		}
		if ($data["recept_drug_ostat_control"] != -1) {
			$options["recept_drug_ostat_control"] = $data["recept_drug_ostat_control"];
		}
		if ($data["recept_empty_drug_ostat_allow"] != -1) {
			$options["recept_empty_drug_ostat_allow"] = $data["recept_empty_drug_ostat_allow"];
		}
		if (!empty($data["select_drug_from_list"])) {
			$options["select_drug_from_list"] = $data["select_drug_from_list"];
		}
		if (!empty($data["DrugComplexMnn_id"]) && (empty($data["withOptions"]) || !$data["withOptions"])) {
			$query = "
				select
					dcm.DrugComplexMnn_id as \"DrugComplexMnn_id\",
					rtrim(dcm.DrugComplexMnn_RusName) as \"DrugComplexMnn_Name\"
				from rls.v_DrugComplexMnn dcm
				where dcm.DrugComplexMnn_id = :DrugComplexMnn_id
			";
			$queryParams["DrugComplexMnn_id"] = $data["DrugComplexMnn_id"];
		} // В зависимости от настроек, надо будет вытаскивать данные из WhsDocumentOrderAllocation и DrugRequest
		else {
			$from = "rls.v_DrugComplexMnn dcm";
			$additionFieldsArr = [];
			$withArr = [];
			if ($data["Lpu_id"] > 0) {
				$queryParams["Lpu_id"] = $data["Lpu_id"];
			}
			if (!empty($data["DrugComplexMnn_id"])) {
				$filterList[] = "dcm.DrugComplexMnn_id ilike :DrugComplexMnn_id";
				$queryParams["DrugComplexMnn_id"] = $data["DrugComplexMnn_id"];
			}
			if (!empty($data["is_mi_1"]) && ($data["is_mi_1"] == "true")) {
				$filterList[] = "NTFR.PARENTID not in (1, 176)";
				$filterList[] = "NTFR.CLSNTFR_ID not in (1, 137, 138, 139, 140, 141, 142, 144, 146, 149, 153, 159, 176, 180, 181, 184, 199, 207)";
			}
			if (!empty($data["query"])) {
				switch ($data["mode"]) {
					case "any":
						$data["query"] = "%" . $data["query"] . "%";
						break;
					default:
						$data["query"] .= "%";
						break;
				}
				$filterList[] = "dcm.DrugComplexMnn_RusName ilike :query";
				$queryParams["query"] = $data["query"];
			}

			//должен быть передан либо идентификатор комплексного мнн либо строка поиска, иначе запрос получится слишком медленным
			if (empty($data['DrugComplexMnn_id']) && empty($data['query'])) {
				return false;
			}

			$ostat_type = 0;
			if ($options["recept_drug_ostat_viewing"]) {
				$ostat_type = 1;
			}
			if ($options["recept_drug_ostat_control"]) {
				$ostat_type = 2;
			}
			if ($options["recept_empty_drug_ostat_allow"]) {
				$ostat_type = 3;
			}

			$drug_from_list = (!empty($options['select_drug_from_list']) ? $options['select_drug_from_list'] : '');
			$drug_ostat_control = !empty($options['recept_drug_ostat_control']);

			//для Москвы: при выписке из ЖНВЛП, если признак протокола ВК = "да" то в качестве базовый выборки используется таблица с комплексными МНН
			if ($region_nick == 'msk' && $drug_from_list == 'jnvlp' && $data['EvnRecept_IsKEK'] == 2) {
				$drug_from_list = 'drugcomplexmnn_table';
			}

			if (!empty($drug_from_list)) {
				switch ($drug_from_list) {
					case 'drugcomplexmnn_table':
						$from = "
							rls.v_DrugComplexMnn dcm
							inner join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
						";

						if ($data['is_mi_1'] == 'true') {
							$from .= "
								left join lateral ( --для определения медизделий
									select
										i_p.NTFRID
									from
										rls.v_Drug i_d
										left join rls.v_Prep i_p on i_p.Prep_id = i_d.DrugPrep_id
									where
										i_d.DrugComplexMnn_id = dcm.DrugComplexMnn_id and
										i_p.NTFRID is not null
									limit 1
								) as p on true
								left join rls.CLSNTFR ntfr on ntfr.CLSNTFR_ID = p.NTFRID
							";
						}

						//для Москвы: при выписке из ЖНВЛП, если признак протокола ВК = "да" и признак выписки по МНН, то выбираем только комплексные мнн имеющие действующее вещество
						if ($region_nick == 'msk' && $data['EvnRecept_IsKEK'] == 2 && $data['EvnRecept_IsMnn'] == 2) {
							$filterList[] = "dcmn.ACTMATTERS_id is not null";
						}
						break;
					case "jnvlp":
                        $withArr[] = "
							df_tree (name, id, pid) -- рекурсивный запрос для получения лекарственных форм не относящихся к лекарственным средствам
							as (
								select
									t.NAME, t.CLSDRUGFORMS_ID, t.PARENTID
								from
									rls.CLSDRUGFORMS t
								where
									t.CLSDRUGFORMS_ID > 1 and t.PARENTID = 0 
								union all
								select
									t.NAME, t.CLSDRUGFORMS_ID, t.PARENTID
								from
									rls.CLSDRUGFORMS t
									inner join df_tree tr on t.PARENTID = tr.id
								where
									t.CLSDRUGFORMS_ID not in (979)
							)
						";

						if (!empty($data['EvnRecept_IsMnn']) && $data['EvnRecept_IsMnn'] == 2) { //выписка по МНН = "Да"
							$from = "
								v_DrugNormativeListSpec dnls
								inner join rls.v_DrugComplexMnnName dcmn on dcmn.ActMatters_id = dnls.DrugNormativeListSpecMNN_id
								inner join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnnName_id = dcmn.DrugComplexMnnName_id
								inner join v_DrugNormativeList dnl on dnl.DrugNormativeList_id = dnls.DrugNormativeList_id
								left join v_DrugNormativeListSpecFormsLink dnlsfl on dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
									and dcm.CLSDRUGFORMS_ID = dnlsfl.DrugNormativeListSpecForms_id
								left join lateral (
									select i_p.NTFRID
									from
										rls.v_Drug i_d
										left join rls.v_Prep i_p on i_p.Prep_id = i_d.DrugPrep_id
									where i_d.DrugComplexMnn_id = dcm.DrugComplexMnn_id
									  and i_p.NTFRID is not null
									limit 1
								) as p on true
								left join rls.CLSNTFR ntfr on ntfr.CLSNTFR_ID = p.NTFRID
								left join lateral (
									select
										i_dnlsfl.DrugNormativeListSpecFormsLink_id
									from
										v_DrugNormativeListSpecFormsLink i_dnlsfl
									where
										i_dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
									limit 1										
								) dnlsfl_exists on true
								left join df_tree on df_tree.id = dcm.CLSDRUGFORMS_ID
							";
						} else {
							$from = "
								v_DrugNormativeListSpecTorgLink dnlstl
								inner join v_DrugNormativeListSpec dnls on dnls.DrugNormativeListSpec_id = dnlstl.DrugNormativeListSpec_id
								inner join rls.v_Prep p on p.TRADENAMEID = dnlstl.DrugNormativeListSpecTorg_id
								inner join rls.v_Drug d on d.DrugPrep_id = p.Prep_id
								inner join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
								inner join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
								inner join v_DrugNormativeList dnl on dnl.DrugNormativeList_id = dnls.DrugNormativeList_id
								left join v_DrugNormativeListSpecFormsLink dnlsfl on dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
									and dcm.CLSDRUGFORMS_ID = dnlsfl.DrugNormativeListSpecForms_id
								left join rls.CLSNTFR ntfr on ntfr.CLSNTFR_ID = p.NTFRID
								left join lateral (
									select
										i_dnlsfl.DrugNormativeListSpecFormsLink_id
									from
										v_DrugNormativeListSpecFormsLink i_dnlsfl
									where
										i_dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
									limit 1										
								) dnlsfl_exists on true
								left join df_tree on df_tree.id = dcm.CLSDRUGFORMS_ID
							";
						}

						$filterList[] = "
							(
								coalesce(dnlsfl.DrugNormativeListSpecForms_id, 0) = coalesce(dcm.CLSDRUGFORMS_ID, 0)
								or
								dnlsfl_exists.DrugNormativeListSpecFormsLink_id is null and df_tree.id is null
							)
						";
						if (!empty($data["Date"])) {
							$filterList[] = "dnl.DrugNormativeList_BegDT <= :Date";
							$filterList[] = "(dnl.DrugNormativeList_EndDT > :Date or dnl.DrugNormativeList_EndDT is null)";
							$queryParams["Date"] = $data["Date"];
						}
						if (!empty($data["PersonRegisterType_id"])) {
							$filterList[] = "dnl.PersonRegisterType_id = :PersonRegisterType_id";
							$queryParams["PersonRegisterType_id"] = $data["PersonRegisterType_id"];
						}
						if (!empty($data["EvnRecept_IsKEK"]) && ($region_nick != 'msk' || $data['EvnRecept_IsKEK'] == 1)) {
							$filterList[] = "coalesce(dnls.DrugNormativeListSpec_IsVK,1) = :EvnRecept_IsKEK";
							$queryParams["EvnRecept_IsKEK"] = $data["EvnRecept_IsKEK"];
						}

						//для Москвы: при выписке из ЖНВЛП, если признак протокола ВК != "да" и признак выписки по МНН = "нет", то выбираем только комплексные мнн не имеющие действующего вещества
						if ($region_nick == 'msk' && $data['EvnRecept_IsKEK'] != 2 && $data['EvnRecept_IsMnn'] == 1) {
							$filterList[] = "dcmn.ACTMATTERS_id is null";
						}
						break;
					case "request":
						$from = "
                            v_DrugRequestRow drr
                            inner join v_DrugRequest dr on dr.DrugRequest_id = drr.DrugRequest_id
                            inner join v_DrugRequestStatus drs on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
                            inner join v_DrugRequestPeriod drp on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
                            left join lateral (
                                select coalesce(i_yn.YesNo_Code, 0) as isPrivilegeAllowed
                                from
                                    v_WhsDocumentCostItemType i_wdcit
                                    left join YesNo i_yn on i_yn.YesNo_id = i_wdcit.WhsDocumentCostItemType_isPrivilegeAllowed
                                where i_wdcit.PersonRegisterType_id = dr.PersonRegisterType_id
                            ) as is_priv_all on true
                            left join lateral (
                                select i_hms.HeadMedSpec_id
                                from
                                    v_MedPersonal i_mp
                                    left join persis.v_MedWorker i_mw on i_mw.Person_id = i_mp.Person_id
                                    inner join v_HeadMedSpec i_hms on i_hms.MedWorker_id = i_mw.MedWorker_id
                                where i_mp.MedPersonal_id = dr.MedPersonal_id
                                  and drp.DrugRequestPeriod_begDate between i_hms.HeadMedSpec_begDT and i_hms.HeadMedSpec_endDT
                                limit 1
                            ) as is_hms on true
                            left join lateral (
                                select
                                    sum(i_drpo.DrugRequestPersonOrder_OrdKolvo) as Kolvo,
                                    max(i_drpo.Person_id) as Person_id
                                from v_DrugRequestPersonOrder i_drpo
                                where :Person_id is not null
                                  and drr.DrugRequest_id = dr.DrugRequest_id
                                  and i_drpo.Person_id = :Person_id
                                  and (i_drpo.DrugComplexMnn_id = drr.DrugComplexMnn_id or i_drpo.Tradenames_id = drr.TRADENAMES_id)
                            ) as drpo on true
                            left join lateral (
                                select (coalesce(drr.DrugRequestRow_Kolvo, 0) - sum(i_drpo.DrugRequestPersonOrder_OrdKolvo)) as Kolvo
                                from v_DrugRequestPersonOrder i_drpo
                                where drr.DrugRequest_id = dr.DrugRequest_id
                                  and (i_drpo.DrugComplexMnn_id = drr.DrugComplexMnn_id or i_drpo.Tradenames_id = drr.TRADENAMES_id)
                            ) as drpo_reserve on true
                            inner join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = drr.DrugComplexMnn_id
                            inner join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
                            left join v_PersonState ps on ps.Person_id = drpo.Person_id
                            left join lateral (
                                select i_p.NTFRID
                                from
                                    rls.v_Drug i_d
                                    left join rls.v_Prep i_p on i_p.Prep_id = i_d.DrugPrep_id
                                where i_d.DrugComplexMnn_id = drr.DrugComplexMnn_id
                                  and i_p.NTFRID is not null
                                limit 1
                            ) as p on true
                            left join rls.CLSNTFR ntfr on ntfr.CLSNTFR_ID = p.NTFRID
						";
						$queryParams["Person_id"] = !empty($data["Person_id"]) ? $data["Person_id"] : null;
						if (!empty($data["Person_id"]) && !empty($data["fromReserve"])) {
							$filterList[] = "(drpo.Kolvo > 0 or drpo_reserve.Kolvo > 0)";
						} else if (!empty($data["Person_id"])) {
							$filterList[] = "drpo.Kolvo > 0";
						} else if (!empty($data["fromReserve"])) {
							$filterList[] = "drpo_reserve.Kolvo > 0";
						}
						$filterList[] = "drs.DrugRequestStatus_Code = 3";
						if (!empty($data["Date"])) {
							$filterList[] = ":Date between drp.DrugRequestPeriod_begDate and drp.DrugRequestPeriod_endDate";
							$queryParams["Date"] = $data["Date"];
						}

						$filterList[] = "
							(dr.MedPersonal_id = :MedPersonal_id or (is_hms.HeadMedSpec_id is not null and is_priv_all.isPrivilegeAllowed = 1))
						";
						$queryParams["MedPersonal_id"] = $data["session"]["medpersonal_id"];
						$additionFieldsArr[] = "
							case when ps.Person_id is null
								then 'Резерв'
								else ps.Person_SurName||' '||ps.Person_FirName||' '||coalesce(ps.Person_SecName, '')
							end as Person_Fio";
						$additionFieldsArr[] = "drr.DrugRequestRow_id";
						break;
					case "allocation":
						$lpuid = "";
						$filterList[] = "sat.SubAccountType_Code = 1";
						$filterList[] = "dor.DrugOstatRegistry_Kolvo > 0";
						$filterList[] = "coalesce(isdef.YesNo_Code, 0) = 0";
						if ($data['Lpu_id'] != "" && $data['Lpu_id'] > 0) {
							$filterList[] = "l.Lpu_id = :Lpu_id";
							$filterList[] = "dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
							$lpuid = "inner join v_Lpu l on l.Org_id = dor.Org_id";
							$queryParams["Lpu_id"] = $data["Lpu_id"];
						}
						$queryParams["WhsDocumentCostItemType_id"] = $data["WhsDocumentCostItemType_id"];
						$additionFieldsArr[] = "dor.DrugOstatRegistry_id";
						$additionFieldsArr[] = "dsh.WhsDocumentSupply_id";
						$from = "
							v_DrugOstatRegistry dor
							left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
							left join rls.v_Drug d on d.Drug_id = dor.Drug_id
							left join v_DrugShipment dsh on dsh.DrugShipment_id = dor.DrugShipment_id
							left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
							left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id							
							left join rls.v_Prep p on p.Prep_id = d.DrugPrep_id
							left join rls.CLSNTFR ntfr on ntfr.CLSNTFR_ID = p.NTFRID
							left join rls.v_PrepSeries ps on ps.PrepSeries_id = dor.PrepSeries_id
							left join v_YesNo isdef on isdef.YesNo_id = ps.PrepSeries_isDefect
							{$lpuid}
						";
						if (!empty($data["WhsDocumentCostItemType_id"])) {
							//проверка программы ЛЛО
							$wdcit_query = "
								select coalesce(yn.YesNo_Code, 0) as \"isPersonAllocation\"
								from
									v_WhsDocumentCostItemType wdcit
									left join v_YesNo yn on yn.YesNo_id = wdcit.WhsDocumentCostItemType_isPersonAllocation
								where wdcit.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id;
							";
							$wdcit_data = $callObject->getFirstRowFromQuery($wdcit_query, ["WhsDocumentCostItemType_id" => $data["WhsDocumentCostItemType_id"]]);
							if (!empty($wdcit_data["isPersonAllocation"])) {
								//если установлен признак выписки рецепта по персональной разнарядке
								//проверка включения медикамента и пациента в разнарядку
								$wdcit_where = "";
								$drpo_where = "";
								if (!empty($data["DrugFinance_id"])) {
									$wdcit_where .= " and ii_wdcit.DrugFinance_id = :DrugFinance_id ";
									$drpo_where .= " and i_drr.DrugFinance_id = :DrugFinance_id ";
									$queryParams["DrugFinance_id"] = $data["DrugFinance_id"];
								}
								if (!empty($lpuid)) {
									$drpo_where .= " and i_dr.Lpu_id = l.Lpu_id ";
								}
								$from .= "
									left join lateral (
										select i_drpo.DrugRequestPersonOrder_id
										from
											v_DrugRequestPersonOrder i_drpo
											left join v_DrugRequest i_dr on i_dr.DrugRequest_id = i_drpo.DrugRequest_id
											left join v_DrugRequestPeriod i_drp on i_drp.DrugRequestPeriod_id = i_dr.DrugRequestPeriod_id
											left join lateral (
												select ii_drr.DrugFinance_id
												from v_DrugRequestRow ii_drr
												where ii_drr.DrugRequest_id = i_drpo.DrugRequest_id
												  and ii_drr.DrugComplexMnn_id = i_drpo.DrugComplexMnn_id
												  and coalesce(ii_drr.TRADENAMES_id, 0) = coalesce(i_drpo.Tradenames_id, 0)
												order by ii_drr.DrugRequestRow_id
												limit 1
											) as i_drr on true
											left join lateral (
												select ii_wdcit.WhsDocumentCostItemType_id
												from v_WhsDocumentCostItemType ii_wdcit
												where ii_wdcit.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id
												  and coalesce(ii_wdcit.PersonRegisterType_id, 0) = coalesce(i_dr.PersonRegisterType_id, 0)
												  {$wdcit_where}
												limit 1
											) as i_wdcit on true
										where i_drpo.Person_id = :Person_id
										  and i_drpo.DrugComplexMnn_id = dcm.DrugComplexMnn_id
										  and i_wdcit.WhsDocumentCostItemType_id is not null
										  and i_drp.DrugRequestPeriod_begDate <= :Date
										  and i_drp.DrugRequestPeriod_endDate >= :Date
										  and i_drpo.DrugRequestPersonOrder_OrdKolvo > 0
										  {$drpo_where}
										limit 1
									) as drpo on true
								";
								$filterList[] = "drpo.DrugRequestPersonOrder_id is not null";
								$queryParams["Person_id"] = $data["Person_id"];
								$queryParams["Date"] = $data["Date"];
							}
						}
						break;
					case "request_and_allocation":
						$lpuid = "";
						$filterList[] = "drs.DrugRequestStatus_Code = 3";
						$filterList[] = "sat.SubAccountType_Code = 1";
						$filterList[] = "dor.DrugOstatRegistry_Kolvo > 0";
						$filterList[] = "coalesce(isdef.YesNo_Code, 0) = 0";
						if ($data["Lpu_id"] != "" && $data["Lpu_id"] > 0) {
							$filterList[] = "l.Lpu_id = :Lpu_id";
							$filterList[] = "dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
							$lpuid = "inner join v_Lpu l on l.Org_id = dor.Org_id";
							$queryParams["Lpu_id"] = $data["Lpu_id"];
							$queryParams["WhsDocumentCostItemType_id"] = $data["WhsDocumentCostItemType_id"];
						}
						if (!empty($data["Date"])) {
							$filterList[] = ":Date between drp.DrugRequestPeriod_begDate and drp.DrugRequestPeriod_endDate";
							$queryParams["Date"] = $data["Date"];
						}
						if (!empty($data["Person_id"]) && !empty($data["fromReserve"]) && $data["fromReserve"]) {
							$filterList[] = "(drr.Person_id = :Person_id or drr.Person_id is null)";
							$queryParams["Person_id"] = $data["Person_id"];
							$filterList[] = "(drr.Person_id is not null or dr.MedPersonal_id = :MedPersonal_id)";
							$queryParams["MedPersonal_id"] = $data["session"]["medpersonal_id"];
						} else if (!empty($data["Person_id"])) {
							$filterList[] = "drr.Person_id = :Person_id";
							$queryParams["Person_id"] = $data["Person_id"];
						} else if (!empty($data["fromReserve"]) && $data["fromReserve"]) {
							$filterList[] = "drr.Person_id is null";
							$filterList[] = "dr.MedPersonal_id = :MedPersonal_id";
							$queryParams["MedPersonal_id"] = $data["session"]["medpersonal_id"];
						}
						$additionFieldsArr[] = "
							case when ps.Person_id is null
								then 'Резерв'
								else ps.Person_SurName||' '||ps.Person_FirName||' '||coalesce(ps.Person_SecName, '')
							end as \"Person_Fio\"
						";
						$additionFieldsArr[] = "drr.DrugRequestRow_id";
						$additionFieldsArr[] = "dor.DrugOstatRegistry_id";
						$additionFieldsArr[] = "dsh.WhsDocumentSupply_id";
						$from = "
							v_DrugOstatRegistry dor
							left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
							inner join rls.v_Drug d on d.Drug_id = dor.Drug_id
							inner join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
							inner join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
							left join v_DrugShipment dsh on dsh.DrugShipment_id = dor.DrugShipment_id
							{$lpuid}
							inner join v_DrugRequestRow drr on drr.DrugComplexMnn_id = d.DrugComplexMnn_id
							inner join v_DrugRequest dr on dr.DrugRequest_id = drr.DrugRequest_id
							inner join v_DrugRequestStatus drs on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
							inner join v_DrugRequestPeriod drp on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
							left join v_PersonState ps on ps.Person_id = drr.Person_id
							left join rls.v_Prep p on p.Prep_id = d.DrugPrep_id
							left join rls.CLSNTFR ntfr on ntfr.CLSNTFR_ID = p.NTFRID
							left join rls.v_PrepSeries psr on psr.PrepSeries_id = dor.PrepSeries_id
							left join v_YesNo isdef on isdef.YesNo_id = psr.PrepSeries_isDefect
						";
						break;
				}
			}

			if ($drug_ostat_control) { //в настройках включен контроль остатков
				$withFilterList = ["1=1"];
				$withFilterList[] = "sat.SubAccountType_Code = 1";
				$withFilterList[] = "dor.DrugOstatRegistry_Kolvo > 0";
				$withFilterList[] = "coalesce(isdef.YesNo_Code, 0) = 0";
				if (!empty($data["WhsDocumentCostItemType_id"])) {
					$withFilterList[] = "dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
					$queryParams["WhsDocumentCostItemType_id"] = $data["WhsDocumentCostItemType_id"];
				}
				$ras = null;
				$apt = null;
				$ofi_subquery = "";
				if (!empty($options["recept_by_ras_drug_ostat"]) && $options["recept_by_ras_drug_ostat"] && $ostat_type != 2) {
					$ras = "ct.ContragentType_SysNick = 'store'";
				}
				if (!empty($options["recept_by_farmacy_drug_ostat"]) && $options["recept_by_farmacy_drug_ostat"]) {
					$apt = "ct.ContragentType_SysNick = 'apt'";
					if (!empty($options["recept_farmacy_type"]) && $options["recept_farmacy_type"] == "mo_farmacy" && !empty($queryParams["Lpu_id"])) {
						$ofi_subquery = "left join v_OrgFarmacyIndex ofi on ofi.OrgFarmacy_id = ofm.OrgFarmacy_id";
						$apt .= " and ofi.Lpu_id = :Lpu_id";
						//если пришел идентификатор отделения, то ищем идентификатор подразделения
						if (!empty($data["LpuSection_id"])) {
							//получение подразделения по отделению
							$query = "
								select LpuBuilding_id
								from v_LpuSection
								where LpuSection_id = :LpuSection_id
								limit 1
							";
							$data["LpuBuilding_id"] = $callObject->getFirstResultFromQuery($query, ["LpuSection_id" => $data["LpuSection_id"]]);
						}
						//если есть идентификатор подразделения, то проверяем прикрепления к подразделениям
						if (!empty($data["LpuBuilding_id"])) {
							//проверям есть ли прикрепление подразделения к каким либо аптекам
							$query = "
								select count(ofi.OrgFarmacyIndex_id) as \"cnt\"
								from v_OrgFarmacyIndex ofi
								where ofi.Lpu_id = :Lpu_id
								  and ofi.LpuBuilding_id = :LpuBuilding_id
							";
							$cnt_data = $callObject->getFirstRowFromQuery($query, [
								"Lpu_id" => $data["Lpu_id"],
								"LpuBuilding_id" => $data["LpuBuilding_id"]
							]);
							if (!empty($cnt_data["cnt"])) {
								$withArr[] = "
									ofi_list as (
										select
											i_ofi.OrgFarmacy_id,
											i_ofi.Lpu_id,
											i_ofi.WhsDocumentCostItemType_id,
											i_ofi.Storage_id,
											i_ofi.OrgFarmacyIndex_IsNarko,
											ofi_cnt.storage_cnt
										from
											v_OrgFarmacyIndex i_ofi
											left join lateral (
												select sum(case when ii_ofi.Storage_id is not null then 1 else 0 end) as storage_cnt
												from v_OrgFarmacyIndex ii_ofi
												where ii_ofi.Lpu_id = i_ofi.Lpu_id
												  and ii_ofi.LpuBuilding_id = i_ofi.LpuBuilding_id
												  and ii_ofi.OrgFarmacy_id = i_ofi.OrgFarmacy_id
												  and ii_ofi.WhsDocumentCostItemType_id = i_ofi.WhsDocumentCostItemType_id
											) as ofi_cnt on true
										where i_ofi.Lpu_id = :Lpu_id
										  and i_ofi.LpuBuilding_id = :LpuBuilding_id 
									)
								";
								$ofi_subquery = "
									left join ofi_list ofi on ofi.OrgFarmacy_id = ofm.OrgFarmacy_id		
									left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
									left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
									left join rls.v_ACTMATTERS am on am.ACTMATTERS_ID = dcmn.ACTMATTERS_id
									left join lateral (
										select (case when coalesce(am.NARCOGROUPID, 0) = 2 then 2 else 1 end) as IsNarko
									) as IsNarko on true
								";
								$apt .= " and (ofi.storage_cnt = 0 or (ofi.Storage_id = dor.Storage_id and ofi.OrgFarmacyIndex_IsNarko = IsNarko.IsNarko))"; //если для подразделения указано прикрепление к конкретным складам, то склад остатков должен совпадать со складом прикрепления, кроме того должен учитываться признак наркотики
								$queryParams["LpuBuilding_id"] = $data["LpuBuilding_id"];
								if (!empty($data["WhsDocumentCostItemType_id"])) { //прикрепление к подразделению производится всегда по определенной программе ЛЛО, поэтому если с формы передана программа, ищем прикрепление по ней
									$apt .= " and ofi.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
									$queryParams["WhsDocumentCostItemType_id"] = $data["WhsDocumentCostItemType_id"];
								}
							}
						}
					}
				}
				if (!empty($ras) && !empty($apt)) {
					$withFilterList[] = "(($ras) or ($apt))";
					$withFilterList[] = "dor.Storage_id is not null";
				} else if (!empty($ras) || !empty($apt)) {
					$withFilterList[] = empty($ras) ? $apt : $ras;
					$withFilterList[] = "dor.Storage_id is not null";
				}
				$withFilterListString = implode(" and ", $withFilterList);
				$withArr[] = "
					ostat_list as (
						select distinct
							d.Drug_id,
							d.DrugComplexMnn_id
						from
							v_DrugOstatRegistry dor
							inner join rls.v_Drug d on d.Drug_id = dor.Drug_id
							left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
							left join v_DrugShipment dsh on dsh.DrugShipment_id = dor.DrugShipment_id
							left join v_Contragent c on c.Org_id = dor.Org_id
							left join v_ContragentType ct on ct.ContragentType_id = c.ContragentType_id
							left join rls.v_PrepSeries ps on ps.PrepSeries_id = dor.PrepSeries_id
							left join v_YesNo isdef on isdef.YesNo_id = ps.PrepSeries_isDefect
							left join v_OrgFarmacy ofm on ofm.Org_id = dor.Org_id
							{$ofi_subquery}
						where {$withFilterListString}
					)
				";
				$filterList[] = "exists(select DrugComplexMnn_id from ostat_list where DrugComplexMnn_id = dcm.DrugComplexMnn_id)";
			}

            //для Москвы действует дополнительный фильтр на наличие медикаментов с комплексным МНН в номенклатурном справочнике и в справочнике СПО УЛО, при условии что выписка идет из ЖНВЛП
			if ($region_nick == 'msk' && $options['select_drug_from_list'] == 'jnvlp') {
				$spo_ulo_filters = array();

				if (!empty($data['Date'])) {
					$spo_ulo_filters[] = "(
						i_sud.SPOULODrug_begDT is null or
						i_sud.SPOULODrug_begDT <= :Date
					)";
					$spo_ulo_filters[] = "(
						i_sud.SPOULODrug_endDT is null or
						i_sud.SPOULODrug_endDT >= :Date
					)";
					$queryParams['Date'] = $data['Date'];
				}

				if (!empty($data['PrivilegeType_id']) && !empty($data['WhsDocumentCostItemType_id'])) {
					$query = "
						select
							(
								select
									df.DrugFinance_SysNick
								from
									v_PrivilegeType pt
									left join v_DrugFinance df on df.DrugFinance_id = pt.DrugFinance_id
								where
									pt.PrivilegeType_id = :PrivilegeType_id
								limit 1
							) as \"DrugFinance_SysNick\",
							(
								select
									wdcit.WhsDocumentCostItemType_Nick
								from
									v_WhsDocumentCostItemType wdcit
								where
									wdcit.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id
								limit 1
							) as \"WhsDocumentCostItemType_Nick\",
							(
								select
									rd.ReceptDiscount_Code
								from
									v_PrivilegeType pt with
									left join v_ReceptDiscount rd on rd.ReceptDiscount_id = pt.ReceptDiscount_id
								where
									pt.PrivilegeType_id = :PrivilegeType_id
								limit 1
							) as \"ReceptDiscount_Code\"
					";
					$priv_data = $callObject->getFirstRowFromQuery($query, array(
						'PrivilegeType_id' => $data['PrivilegeType_id'],
						'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id']
					));
					if (!empty($priv_data['DrugFinance_SysNick'])) {
						if ($priv_data['DrugFinance_SysNick'] == 'fed' && $priv_data['WhsDocumentCostItemType_Nick'] == 'fl') { //федеральная льготная категория и программа «ОНЛС»
							$spo_ulo_filters[] = "(
								coalesce(i_sud.fed, 0) = 1 or
								coalesce(i_sud.reg, 0) = 1
							)";
						}
						if ($priv_data['DrugFinance_SysNick'] == 'reg' && $priv_data['WhsDocumentCostItemType_Nick'] == 'rl') { //региональная льготная категория и программа «РЛО»
							$spo_ulo_filters[] = "coalesce(i_sud.reg, 0) = 1";
						}
						if (($priv_data['DrugFinance_SysNick'] == 'fed' || $priv_data['DrugFinance_SysNick'] == 'reg') && $priv_data['ReceptDiscount_Code'] == '1') { //указана федеральная льготная категория или региональная льготная категория со 100% скидкой
							$spo_ulo_filters[] = "coalesce(i_sud.sale100, 0) = 1";
						}
						if ($priv_data['DrugFinance_SysNick'] == 'reg' && $priv_data['ReceptDiscount_Code'] == '2') { //указана региональная льготная категория с 50% скидкой
							$spo_ulo_filters[] = "coalesce(i_sud.sale50, 0) = 1";
						}
					}
				}

				$spo_ulo_where = count($spo_ulo_filters) > 0 ? " and ".implode(" and ", $spo_ulo_filters) : "";

				$from .= "
					left join lateral (
						select
							i_d.DrugComplexMnn_id
						from
							rls.v_Drug i_d
							inner join rls.v_DrugNomen i_dn on i_dn.Drug_id = i_d.Drug_id
							inner join r50.SPOULODrug i_sud on cast(i_sud.NOMK_LS as varchar) = i_dn.DrugNomen_Code
						where
							i_d.DrugComplexMnn_id = dcm.DrugComplexMnn_id
							{$spo_ulo_where}
						limit 1
					) drug_nomen on true
				";
				$filterList[] = "drug_nomen.DrugComplexMnn_id is not null";
			}

			$filterList[] = "dcm.DrugComplexMnnGroupType_id = 2";
			$additionFields = count($additionFieldsArr) > 0 ? "," . implode(",", $additionFieldsArr) : "";
			$with = count($withArr) > 0 ? "with recursive\n" . implode(",\n", $withArr) : "";
			$query = "";
			if (!empty($with)) {
				$query = "
					-- addit with
					{$with}
					-- end addit with
				";
			}
			$selectString = "
				dcm.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				RTRIM(dcm.DrugComplexMnn_RusName) as \"DrugComplexMnn_Name\",
				dcmn.ACTMATTERS_id as \"Actmatters_id\"
				{$additionFields}
			";
			$whereString = implode(" and ", $filterList);
			$orderByString = "\"DrugComplexMnn_Name\"";
			$query .= "
				select distinct
				-- select
					{$selectString}
				-- end select
				from
				-- from
					{$from}
				-- end from
				where
				-- where
					{$whereString}
				-- end where
				order by
				-- order by
					{$orderByString}
				-- end order by
				limit 500
			";
		}
		/**@var CI_DB_result $result */
		if ($data["paging"]) {
			$result = $callObject->db->query(getLimitSQLPH($query, $data["start"], $data["limit"], "distinct"), $queryParams);
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
		} else {
			$result = $callObject->db->query($query, $queryParams);
			if (!is_object($result)) {
				return false;
			}
			return $result->result("array");
		}
	}

	/**
	 * Получение списка комплексных МНН (выборка из ЖНВЛП)
	 * @param $data
	 * @return array|bool
	 */
	public static function loadDrugComplexMnnJnvlpList(Drug_model $callObject, $data)
	{
		$filterList = [];
		$queryParams = [];
		//определение регистра по программе ЛЛО
		$query = "
			select PersonRegisterType_id
			from v_WhsDocumentCostItemType
			where WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id	
		";
		$wdcit_id = $callObject->getFirstResultFromQuery($query, [
			"WhsDocumentCostItemType_id" => $data["WhsDocumentCostItemType_id"]
		]);
		$filterList[] = "dnl.PersonRegisterType_id = :PersonRegisterType_id";
		$queryParams["PersonRegisterType_id"] = !empty($wdcit_id) ? $wdcit_id : null;
		$filterList[] = "
			(case when exists(select DrugNormativeListSpec_id from v_DrugNormativeListSpecFormsLink where DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id)
				then dnlsfl.DrugNormativeListSpecForms_id
				else coalesce(dcm.CLSDRUGFORMS_ID,0)
			end) = coalesce(dcm.CLSDRUGFORMS_ID,0)
		";
		if (!empty($data["Date"])) {
			$filterList[] = "(dnls.DrugNormativeListSpec_BegDT is null or dnls.DrugNormativeListSpec_BegDT <= :Date)";
			$filterList[] = "(dnls.DrugNormativeListSpec_EndDT is null or dnls.DrugNormativeListSpec_EndDT > :Date)";
			$queryParams["Date"] = $data["Date"];
		}
		if (!empty($data["query"])) {
			switch ($data["mode"]) {
				case "any":
					$data["query"] = "%" . $data["query"] . "%";
					break;
				default:
					$data["query"] .= "%";
					break;
			}
			$filterList[] = "dcm.DrugComplexMnn_RusName ilike :query";
			$queryParams["query"] = $data["query"];
		}
		$whereString = implode(" and ", $filterList);
		$query = "
            SELECT * FROM (
			select distinct
			-- select
				dcm.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				RTRIM(dcm.DrugComplexMnn_RusName) as \"DrugComplexMnn_Name\",
				dcmn.ACTMATTERS_id as \"Actmatters_id\"
			-- end select
			from
			-- from
				v_DrugNormativeListSpec dnls
				inner join rls.v_DrugComplexMnnName dcmn on dcmn.ActMatters_id = dnls.DrugNormativeListSpecMNN_id
				inner join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnnName_id = dcmn.DrugComplexMnnName_id
				inner join v_DrugNormativeList dnl on dnl.DrugNormativeList_id = dnls.DrugNormativeList_id
				left join v_DrugNormativeListSpecFormsLink dnlsfl on dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
					and dcm.CLSDRUGFORMS_ID = dnlsfl.DrugNormativeListSpecForms_id
			-- end from
			where
			-- where
				{$whereString}
			-- end where
			) t
			order by
			-- order by
				\"DrugComplexMnn_Name\"
			-- end order by
		";
		/**@var CI_DB_result $result */
		if ($data["paging"]) {
			$result = $callObject->db->query(getLimitSQLPH($query, $data["start"], $data["limit"], "distinct"), $queryParams);
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
		} else {
			$result = $callObject->db->query($query, $queryParams);
			if (!is_object($result)) {
				return false;
			}
			return $result->result("array");
		}
	}

	/**
	 * Загрузка списка
	 * @param $data
	 * @param $options
	 * @return array|bool
	 */
	public static function loadDrugRlsList(Drug_model $callObject, $data, $options)
	{
		$filterList = ["1=1"];
		$queryParams = [];
		$additionFieldsArr = [];
		$withArr = [];
		$from = "";
        $region_nick = getRegionNick();

        if ($data["recept_drug_ostat_viewing"] != -1) {
			$options["recept_drug_ostat_viewing"] = $data["recept_drug_ostat_viewing"];
		}
		if ($data["recept_drug_ostat_control"] != -1) {
			$options["recept_drug_ostat_control"] = $data["recept_drug_ostat_control"];
		}
		if ($data["recept_empty_drug_ostat_allow"] != -1) {
			$options["recept_empty_drug_ostat_allow"] = $data["recept_empty_drug_ostat_allow"];
		}
		if (!empty($data["select_drug_from_list"])) {
			$options["select_drug_from_list"] = $data["select_drug_from_list"];
		}
		if (!empty($data['Drug_rlsid'])) {
			$query = "
				select
					d.Drug_id as \"Drug_rlsid\",
				    d.DrugComplexMnn_id as \"DrugComplexMnn_id\",
				    d.Drug_Code as \"Drug_Code\",
				    rtrim(d.Drug_Name) as \"Drug_Name\"
				from rls.v_Drug d
				where d.Drug_id = :Drug_rlsid
			";
			$queryParams["Drug_rlsid"] = $data["Drug_rlsid"];
		} else {
			if ($data["Lpu_id"] > 0) {
				$queryParams["Lpu_id"] = $data["Lpu_id"];
			}
			if (!empty($data["DrugComplexMnn_id"])) {
				$filterList[] = "d.DrugComplexMnn_id = :DrugComplexMnn_id";
				$queryParams["DrugComplexMnn_id"] = $data["DrugComplexMnn_id"];
			}
			if (!empty($data["query"])) {
				switch ($data["mode"]) {
					case "any":
						$data["query"] = "%" . $data["query"] . "%";
						break;
					default:
						$data["query"] .= "%";
						break;
				}
				$filterList[] = "lower(d.Drug_Name) like lower(:query)";
				$queryParams["query"] = $data["query"];
			}
			$ostat_type = 0;
			if ($options["recept_drug_ostat_viewing"]) {
				$ostat_type = 1;
			}
			if ($options["recept_drug_ostat_control"]) {
				$ostat_type = 2;
			}
			if ($options["recept_empty_drug_ostat_allow"]) {
				$ostat_type = 3;
			}

			$drug_from_list = (!empty($options["select_drug_from_list"]) ? $options["select_drug_from_list"] : "");
			if (in_array($options["select_drug_from_list"], ["allocation", "request_and_allocation"])) {
				$drug_from_list = "allocation";
			}
			if ($drug_from_list == 'jnvlp' && $region_nick == 'msk') { //для Москвы: если выписка ведется из ЖНВЛП, то базовой выборкой является справочник медикаментов
				$drug_from_list = 'drug_table';
			}

			if (!empty($drug_from_list)) {
				switch ($drug_from_list) {
					case 'drug_table':
						$from = "
							rls.v_Drug d
						";
						break;
					case "jnvlp":
                        $additionFieldsArr[] = "coalesce(list.DrugNormativeListSpec_IsVK, 1) as Drug_IsKEK";
                        $withFilterList = ["1=1"];
						$withFilterList[] = "
							(case when exists(select DrugNormativeListSpec_id from v_DrugNormativeListSpecFormsLink where DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id)
								then dnlsfl.DrugNormativeListSpecForms_id
								else coalesce(dcm.CLSDRUGFORMS_ID,0)
							end) = coalesce(dcm.CLSDRUGFORMS_ID,0)
						";
						if (!empty($data["Date"])) {
							$withFilterList[] = "dnl.DrugNormativeList_BegDT <= :Date";
							$withFilterList[] = "(dnl.DrugNormativeList_EndDT > :Date or dnl.DrugNormativeList_EndDT is null)";
							$queryParams["Date"] = $data["Date"];
						}
						if (!empty($data["PersonRegisterType_id"])) {
							$withFilterList[] = "dnl.PersonRegisterType_id = :PersonRegisterType_id";
							$queryParams["PersonRegisterType_id"] = $data["PersonRegisterType_id"];
						}
						if (!empty($data["EvnRecept_IsKEK"])) {
							$withFilterList[] = "coalesce(dnls.DrugNormativeListSpec_IsVK, 1) = :EvnRecept_IsKEK";
							$queryParams["EvnRecept_IsKEK"] = $data["EvnRecept_IsKEK"];
						}
						if (!empty($data["DrugComplexMnn_id"])) {
							$withFilterList[] = "dcm.DrugComplexMnn_id = :DrugComplexMnn_id";
							$queryParams["DrugComplexMnn_id"] = $data["DrugComplexMnn_id"];
						}
						if (!empty($data['WhsDocumentCostItemType_id'])){
                            $withFilterList[] = "dnl.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id"; // Программа ЛЛО
                            $queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
                        }
						$withFilterList[] = "dcm.DrugComplexMnnGroupType_id = 2";
						if (!empty($data["is_mi_1"]) && ($data["is_mi_1"] == "true")) {
							$filterList[] = "ntfr.PARENTID not in (1, 176)";
							$filterList[] = "ntfr.CLSNTFR_ID not in (1, 137, 138, 139, 140, 141, 142, 144, 146, 149, 153, 159, 176, 180, 181, 184, 199, 207)";
						}
                        if ((!empty($data['EvnRecept_IsMnn']) && $data['EvnRecept_IsMnn'] == 2) || (empty($data['EvnRecept_IsMnn']) && $region_nick == 'msk')) { //для Москвы, отстутсвие значения в поле "выписка по мнн" в двнном блоке кода считается идентичным значению "да"
                            $withArr[] = "
								normativ_list as (
									select
									    dcm.DrugComplexMnn_id,
									    max(dnls.DrugNormativeListSpec_IsVK) as DrugNormativeListSpec_IsVK
									from
										v_DrugNormativeListSpec dnls
										inner join rls.v_DrugComplexMnnName dcmn on dcmn.ActMatters_id = dnls.DrugNormativeListSpecMNN_id
										inner join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnnName_id = dcmn.DrugComplexMnnName_id
										inner join v_DrugNormativeList dnl on dnl.DrugNormativeList_id = dnls.DrugNormativeList_id
										left join v_DrugNormativeListSpecFormsLink dnlsfl on dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
											and dcm.CLSDRUGFORMS_ID = dnlsfl.DrugNormativeListSpecForms_id
										left join lateral (
											select i_p.NTFRID
											from
												rls.v_Drug i_d
												left join rls.v_Prep i_p on i_p.Prep_id = i_d.DrugPrep_id
											where i_d.DrugComplexMnn_id = dcm.DrugComplexMnn_id
											  and i_p.NTFRID is not null
											limit 1
										) as p on true
									where " . implode(' and ', $withFilterList) . "
									group by
									    dcm.DrugComplexMnn_id
								)
							";
						} else {
							$withArr[] = "
								normativ_list as (
									select 
									    dcm.DrugComplexMnn_id,
									    max(dnls.DrugNormativeListSpec_IsVK) as DrugNormativeListSpec_IsVK
									from
										v_DrugNormativeListSpecTorgLink dnlstl
										inner join v_DrugNormativeListSpec dnls on dnls.DrugNormativeListSpec_id = dnlstl.DrugNormativeListSpec_id
										inner join rls.v_Prep p on p.TRADENAMEID = dnlstl.DrugNormativeListSpecTorg_id
										inner join rls.v_Drug d on d.DrugPrep_id = p.Prep_id
										inner join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
										inner join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
										inner join v_DrugNormativeList dnl on dnl.DrugNormativeList_id = dnls.DrugNormativeList_id
										left join v_DrugNormativeListSpecFormsLink dnlsfl on dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
											and dcm.CLSDRUGFORMS_ID = dnlsfl.DrugNormativeListSpecForms_id
									where " . implode(' and ', $withFilterList) . "
									group by
									    dcm.DrugComplexMnn_id
								)
							";
						}
						$from = "
							normativ_list list
							inner join rls.v_Drug d on d.DrugComplexMnn_id = list.DrugComplexMnn_id
						";
						break;
					case "request":
                        //блок "нормативный перечень" почти полностью скопирован из раздела ЖНВЛП (выглядит сомнительно, но так прописано в ТЗ #178323)
                        $additionFieldsArr[] = "COALESCE(n_list.DrugNormativeListSpec_IsVK, 1) as \"Drug_IsKEK\"";
                        $withFilterList = ['1=1'];
                        $withFilterList[] = "
							(case when exists
							    (
							        select 
							            DrugNormativeListSpec_id
							        from
							            v_DrugNormativeListSpecFormsLink
							        where
							            DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
							        limit 1
							    )
								then dnlsfl.DrugNormativeListSpecForms_id
								else coalesce(dcm.CLSDRUGFORMS_ID, 0)
							end) = coalesce(dcm.CLSDRUGFORMS_ID,0)
						";
                        if (!empty($data['Date'])) {
                            $withFilterList[] = "dnl.DrugNormativeList_BegDT <= :Date";
                            $withFilterList[] = "(dnl.DrugNormativeList_EndDT > :Date or dnl.DrugNormativeList_EndDT is null)";
                            $queryParams['Date'] = $data['Date'];
                        }
                        if (!empty($data['PersonRegisterType_id'])) {
                            $withFilterList[] = "dnl.PersonRegisterType_id = :PersonRegisterType_id";
                            $queryParams['PersonRegisterType_id'] = $data['PersonRegisterType_id'];
                        }
                        if (!empty($data['EvnRecept_IsKEK'])) {
                            $withFilterList[] = "coalesce(dnls.DrugNormativeListSpec_IsVK,1) = :EvnRecept_IsKEK";
                            $queryParams['EvnRecept_IsKEK'] = $data['EvnRecept_IsKEK'];
                        }
                        if (!empty($data['DrugComplexMnn_id'])) {
                            $withFilterList[] = "dcm.DrugComplexMnn_id = :DrugComplexMnn_id";
                            $queryParams['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
                        }
                        if (!empty($data['WhsDocumentCostItemType_id'])){
                            $withFilterList[] = "dnl.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id"; // Программа ЛЛО
                            $queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
                        }
                        $withFilterList[] = 'dcm.DrugComplexMnnGroupType_id = 2';

                        if (!empty($data['is_mi_1']) && ($data['is_mi_1'] == 'true')){
                            $filterList[] = "ntfr.PARENTID not in (1, 176)";
                            $filterList[] = "ntfr.CLSNTFR_ID not in (1, 137, 138, 139, 140, 141, 142, 144, 146, 149, 153, 159, 176, 180, 181, 184, 199, 207)";
                        }

                        if ((!empty($data['EvnRecept_IsMnn']) && $data['EvnRecept_IsMnn'] == 2) || (empty($data['EvnRecept_IsMnn']) && $region_nick == 'msk')) { //для Москвы, отстутсвие значения в поле "выписка по мнн" в двнном блоке кода считается идентичным значению "да"
                            $withArr[] = "normativ_list as (
								select
									dcm.DrugComplexMnn_id,
									max(dnls.DrugNormativeListSpec_IsVK) as DrugNormativeListSpec_IsVK
								from
									v_DrugNormativeListSpec dnls
									inner join rls.v_DrugComplexMnnName dcmn on dcmn.ActMatters_id = dnls.DrugNormativeListSpecMNN_id
									inner join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnnName_id = dcmn.DrugComplexMnnName_id
									inner join v_DrugNormativeList dnl on dnl.DrugNormativeList_id = dnls.DrugNormativeList_id
									left join v_DrugNormativeListSpecFormsLink dnlsfl on dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
										and dcm.CLSDRUGFORMS_ID = dnlsfl.DrugNormativeListSpecForms_id
									left join lateral ( --для определения медизделий
										select 
											i_p.NTFRID
										from
											rls.v_Drug i_d
											left join rls.v_Prep i_p on i_p.Prep_id = i_d.DrugPrep_id
										where
											i_d.DrugComplexMnn_id = dcm.DrugComplexMnn_id and
											i_p.NTFRID is not null
										limit 1
									) p on true
								where ".implode(' and ',$withFilterList)."
								group by
									dcm.DrugComplexMnn_id
							)";
                        } else {
                            $withArr[] = "normativ_list as (
								select
									dcm.DrugComplexMnn_id,
									max(dnls.DrugNormativeListSpec_IsVK) as DrugNormativeListSpec_IsVK
								from
									v_DrugNormativeListSpecTorgLink dnlstl
									inner join v_DrugNormativeListSpec dnls on dnls.DrugNormativeListSpec_id = dnlstl.DrugNormativeListSpec_id
									inner join rls.v_Prep p on p.TRADENAMEID = dnlstl.DrugNormativeListSpecTorg_id
									inner join rls.v_Drug d on d.DrugPrep_id = p.Prep_id
									inner join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
									inner join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
									inner join v_DrugNormativeList dnl on dnl.DrugNormativeList_id = dnls.DrugNormativeList_id
									left join v_DrugNormativeListSpecFormsLink dnlsfl on dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
										and dcm.CLSDRUGFORMS_ID = dnlsfl.DrugNormativeListSpecForms_id
								where ".implode(' and ',$withFilterList)."
								group by
									dcm.DrugComplexMnn_id
							)";
                        }

                        //блок "заявка"
						$withFilterList = ["1=1"];
						$withFilterList[] = "dcm.DrugComplexMnnGroupType_id = 2";
						$withFilterList[] = "drs.DrugRequestStatus_Code = 3";
						if (!empty($data["Date"])) {
							$withFilterList[] = ":Date between drp.DrugRequestPeriod_begDate and drp.DrugRequestPeriod_endDate";
							$queryParams["Date"] = $data["Date"];
						}
						$queryParams["Person_id"] = !empty($data["Person_id"]) ? $data["Person_id"] : null;
						if (!empty($data["Person_id"])) {
							$withFilterList[] = "(drpo.Kolvo > 0 or drpo_reserve.Kolvo > 0)";
						} else {
							$withFilterList[] = "drpo_reserve.Kolvo > 0";
						}
						$withFilterList[] = "(dr.MedPersonal_id = :MedPersonal_id or (is_hms.HeadMedSpec_id is not null and is_priv_all.isPrivilegeAllowed = 1))";
						$queryParams["MedPersonal_id"] = $data["session"]["medpersonal_id"];
						if (!empty($data["is_mi_1"]) && ($data["is_mi_1"] == "true")) {
							$filterList[] = "ntfr.PARENTID not in (1, 176)";
							$filterList[] = "ntfr.CLSNTFR_ID not in (1, 137, 138, 139, 140, 141, 142, 144, 146, 149, 153, 159, 176, 180, 181, 184, 199, 207)";
						}
						$withArr[] = "
							request_list as (
								select distinct
									dcm.DrugComplexMnn_id
								from
									v_DrugRequestRow drr
									inner join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = drr.DrugComplexMnn_id
									inner join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
									inner join v_DrugRequest dr on dr.DrugRequest_id = drr.DrugRequest_id
									inner join v_DrugRequestStatus drs on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
									inner join v_DrugRequestPeriod drp on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
	                                left join lateral (
	                                    select coalesce(i_yn.YesNo_Code, 0) as isPrivilegeAllowed
	                                    from
	                                        v_WhsDocumentCostItemType i_wdcit
	                                        left join YesNo i_yn on i_yn.YesNo_id = i_wdcit.WhsDocumentCostItemType_isPrivilegeAllowed
	                                    where i_wdcit.PersonRegisterType_id = dr.PersonRegisterType_id
	                                ) as is_priv_all on true
	                                left join lateral (
	                                    select i_hms.HeadMedSpec_id
	                                    from
	                                        v_MedPersonal i_mp
	                                        left join persis.v_MedWorker i_mw on i_mw.Person_id = i_mp.Person_id
	                                        inner join v_HeadMedSpec i_hms on i_hms.MedWorker_id = i_mw.MedWorker_id
	                                    where i_mp.MedPersonal_id = dr.MedPersonal_id
	                                      and drp.DrugRequestPeriod_begDate between i_hms.HeadMedSpec_begDT and i_hms.HeadMedSpec_endDT
	                                    limit 1
	                                ) as is_hms on true
	                                left join lateral (
	                                    select
	                                        sum(i_drpo.DrugRequestPersonOrder_OrdKolvo) as Kolvo,
	                                        max(i_drpo.Person_id) as Person_id
	                                    from v_DrugRequestPersonOrder i_drpo
	                                    where :Person_id is not null
	                                      and drr.DrugRequest_id = dr.DrugRequest_id
	                                      and i_drpo.Person_id = :Person_id
	                                      and (i_drpo.DrugComplexMnn_id = drr.DrugComplexMnn_id or i_drpo.Tradenames_id = drr.TRADENAMES_id)
	                                ) as drpo on true
	                                left join lateral (
	                                    select (coalesce(drr.DrugRequestRow_Kolvo, 0) - sum(i_drpo.DrugRequestPersonOrder_OrdKolvo)) as Kolvo
	                                    from v_DrugRequestPersonOrder i_drpo
	                                    where drr.DrugRequest_id = dr.DrugRequest_id
	                                      and (i_drpo.DrugComplexMnn_id = drr.DrugComplexMnn_id or i_drpo.Tradenames_id = drr.TRADENAMES_id)
	                                ) as drpo_reserve on true
									left join v_PersonState ps on ps.Person_id = drpo.Person_id
									left join lateral (
										select i_p.NTFRID
										from
											rls.v_Drug i_d
											left join rls.v_Prep i_p on i_p.Prep_id = i_d.DrugPrep_id
										where i_d.DrugComplexMnn_id = drr.DrugComplexMnn_id
										  and i_p.NTFRID is not null
										limit 1
									) as p on true
								where " . implode(' and ', $withFilterList) . "
							)
						";
						$from = "
							request_list list
							inner join normativ_list n_list on n_list.DrugComplexMnn_id = list.DrugComplexMnn_id
						    inner join rls.v_Drug d on d.DrugComplexMnn_id = list.DrugComplexMnn_id
						";
						break;
					case "allocation":
						$withFilterList = ["1=1"];
						$lpuid = "";
						$a_list_from = "";
						$withFilterList[] = "sat.SubAccountType_Code = 1";
						$withFilterList[] = "dor.DrugOstatRegistry_Kolvo > 0";
						$withFilterList[] = "coalesce(isdef.YesNo_Code, 0) = 0";
						if (!empty($data["DrugOstatRegistry_id"])) {
							$withFilterList[] = "dor.DrugOstatRegistry_id = :DrugOstatRegistry_id";
							$queryParams["DrugOstatRegistry_id"] = $data["DrugOstatRegistry_id"];
						}
						if ($data["Lpu_id"] != "" && $data["Lpu_id"] > 0) {
							$withFilterList[] = "l.Lpu_id = :Lpu_id";
							$lpuid = "inner join v_Lpu l on l.Org_id = dor.Org_id";
							$queryParams["Lpu_id"] = $data["Lpu_id"];
							if (!empty($data["WhsDocumentCostItemType_id"])) {
								$withFilterList[] = "dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
								$queryParams["WhsDocumentCostItemType_id"] = $data["WhsDocumentCostItemType_id"];
							}
						}
						if (!empty($data["is_mi_1"]) && ($data["is_mi_1"] == "true")) {
							$filterList[] = "ntfr.PARENTID not in (1, 176)";
							$filterList[] = "ntfr.CLSNTFR_ID not in (1, 137, 138, 139, 140, 141, 142, 144, 146, 149, 153, 159, 176, 180, 181, 184, 199, 207)";
						}
						if (!empty($data["WhsDocumentCostItemType_id"])) {
							//проверка программы ЛЛО
							$wdcit_query = "
								select coalesce(yn.YesNo_Code, 0) as \"isPersonAllocation\"
								from
									v_WhsDocumentCostItemType wdcit
									left join v_YesNo yn on yn.YesNo_id = wdcit.WhsDocumentCostItemType_isPersonAllocation
								where wdcit.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id;
							";
							$wdcit_data = $callObject->getFirstRowFromQuery($wdcit_query, [
								"WhsDocumentCostItemType_id" => $data["WhsDocumentCostItemType_id"]
							]);
							if (!empty($wdcit_data['isPersonAllocation'])) {
								//если установлен признак выписки рецепта по персональной разнарядке
								//проверка включения медикамента и пациента в разнарядку
								$wdcit_where = "";
								$drpo_where = "";
								if (!empty($data["DrugFinance_id"])) {
									$wdcit_where .= " and ii_wdcit.DrugFinance_id = :DrugFinance_id ";
									$drpo_where .= " and i_drr.DrugFinance_id = :DrugFinance_id ";
									$queryParams["DrugFinance_id"] = $data["DrugFinance_id"];
								}
								if (!empty($lpuid)) {
									$drpo_where .= " and i_dr.Lpu_id = l.Lpu_id ";
								}
								$a_list_from .= "
									left join lateral (
										select i_drpo.DrugRequestPersonOrder_id
										from
											v_DrugRequestPersonOrder i_drpo
											left join v_DrugRequest i_dr on i_dr.DrugRequest_id = i_drpo.DrugRequest_id
											left join v_DrugRequestPeriod i_drp on i_drp.DrugRequestPeriod_id = i_dr.DrugRequestPeriod_id
											left join lateral (
												select ii_drr.DrugFinance_id
												from v_DrugRequestRow ii_drr
												where ii_drr.DrugRequest_id = i_drpo.DrugRequest_id
												  and ii_drr.DrugComplexMnn_id = i_drpo.DrugComplexMnn_id
												  and coalesce(ii_drr.TRADENAMES_id, 0) = coalesce(i_drpo.Tradenames_id, 0)
												order by ii_drr.DrugRequestRow_id
												limit 1
											) as i_drr on true
											left join lateral (
												select ii_wdcit.WhsDocumentCostItemType_id
												from v_WhsDocumentCostItemType ii_wdcit
												where ii_wdcit.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id
												  and coalesce(ii_wdcit.PersonRegisterType_id, 0) = coalesce(i_dr.PersonRegisterType_id, 0)
												  {$wdcit_where}
												limit 1
											) as i_wdcit on true
										where i_drpo.Person_id = :Person_id
										  and i_drpo.DrugComplexMnn_id = dcm.DrugComplexMnn_id
										  and i_wdcit.WhsDocumentCostItemType_id is not null
										  and i_drp.DrugRequestPeriod_begDate <= :Date
										  and i_drp.DrugRequestPeriod_endDate >= :Date
										  and i_drpo.DrugRequestPersonOrder_OrdKolvo > 0
										  {$drpo_where}
										limit 1
									) as drpo on true
								";
								$withFilterList[] = "drpo.DrugRequestPersonOrder_id is not null";
								$queryParams["Person_id"] = $data["Person_id"];
								$queryParams["Date"] = $data["Date"];
							}
						}
						$withArr[] = "
							allocation_list as (
								select distinct
									d.Drug_id,
									dcm.DrugComplexMnn_id,
									dor.DrugOstatRegistry_Cost as Drug_Price
								from
									v_DrugOstatRegistry dor
									left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
									left join rls.v_Drug d on d.Drug_id = dor.Drug_id
									left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
									left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
									left join v_DrugShipment dsh on dsh.DrugShipment_id = dor.DrugShipment_id
									left join rls.v_Prep p on p.Prep_id = d.DrugPrep_id
									left join rls.v_PrepSeries ps on ps.PrepSeries_id = dor.PrepSeries_id
									left join v_YesNo isdef on isdef.YesNo_id = ps.PrepSeries_isDefect
									{$lpuid}
									{$a_list_from}
								where " . implode(' and ', $withFilterList) . "
							)
						";
						$additionFieldsArr[] = "list.Drug_Price as \"Drug_Price\"";
						$from = "
							allocation_list list
							inner join rls.v_Drug d on d.Drug_id = list.Drug_id
						";
						break;
					case "request_and_allocation":
						$withFilterList = array("1=1");
						$lpuid = "";
						$withFilterList[] = "drs.DrugRequestStatus_Code = 3";
						$withFilterList[] = "sat.SubAccountType_Code = 1";
						$withFilterList[] = "dor.DrugOstatRegistry_Kolvo > 0";
						$withFilterList[] = "coalesce(isdef.YesNo_Code, 0) = 0";
						if ($data["Lpu_id"] != "" && $data["Lpu_id"] > 0) {
							$withFilterList[] = "l.Lpu_id = :Lpu_id";
							$lpuid = "inner join v_Lpu l on l.Org_id = dor.Org_id";
							$queryParams["Lpu_id"] = $data["Lpu_id"];
							if (!empty($data["WhsDocumentCostItemType_id"])) {
								$withFilterList[] = "dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
								$queryParams["WhsDocumentCostItemType_id"] = $data["WhsDocumentCostItemType_id"];
							}
						}
						if (!empty($data["DrugOstatRegistry_id"])) {
							$withFilterList[] = "dor.DrugOstatRegistry_id = :DrugOstatRegistry_id";
							$queryParams["DrugOstatRegistry_id"] = $data["DrugOstatRegistry_id"];
						}
						if (!empty($data["Date"])) {
							$withFilterList[] = ":Date between drp.DrugRequestPeriod_begDate and drp.DrugRequestPeriod_endDate";
							$queryParams["Date"] = $data["Date"];
						}
						if (!empty($data["Person_id"])) {
							$withFilterList[] = "(drr.Person_id = :Person_id or drr.Person_id is null)";
							$queryParams["Person_id"] = $data["Person_id"];
							$withFilterList[] = "(drr.Person_id is not null or dr.MedPersonal_id = :MedPersonal_id)";
							$queryParams["MedPersonal_id"] = $data["session"]["medpersonal_id"];
						} else {
							$withFilterList[] = "drr.Person_id is null";
							$withFilterList[] = "dr.MedPersonal_id = :MedPersonal_id";
							$queryParams["MedPersonal_id"] = $data["session"]["medpersonal_id"];
						}
						if (!empty($data["is_mi_1"]) && ($data["is_mi_1"] == "true")) {
							$filterList[] = "ntfr.PARENTID not in (1, 176)";
							$filterList[] = "ntfr.CLSNTFR_ID not in (1, 137, 138, 139, 140, 141, 142, 144, 146, 149, 153, 159, 176, 180, 181, 184, 199, 207)";
						}
						$withArr[] = "
							rna_list as (
								select distinct
									d.Drug_id,
									dcm.DrugComplexMnn_id,
									dor.DrugOstatRegistry_Cost as Drug_Price
								from
									v_DrugOstatRegistry dor
									left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
									inner join rls.v_Drug d on d.Drug_id = dor.Drug_id
									inner join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
									inner join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
									left join v_DrugShipment dsh on dsh.DrugShipment_id = dor.DrugShipment_id
									{$lpuid}
									inner join v_DrugRequestRow drr on drr.DrugComplexMnn_id = d.DrugComplexMnn_id
									inner join v_DrugRequest dr on dr.DrugRequest_id = drr.DrugRequest_id
									inner join v_DrugRequestStatus drs on drs.DrugRequestStatus_id = dr.DrugRequestStatus_id
									inner join v_DrugRequestPeriod drp on drp.DrugRequestPeriod_id = dr.DrugRequestPeriod_id
									left join v_PersonState ps on ps.Person_id = drr.Person_id
									left join rls.v_Prep p on p.Prep_id = d.DrugPrep_id
									left join rls.v_PrepSeries psr on psr.PrepSeries_id = dor.PrepSeries_id
									left join v_YesNo isdef on isdef.YesNo_id = psr.PrepSeries_isDefect
								where " . implode(' and ', $withFilterList) . "
							)
						";
						$additionFieldsArr[] = "list.Drug_Price as \"Drug_Price\"";
						$from = "
							rna_list list
							inner join rls.v_Drug d on d.Drug_id = list.Drug_id
						";
						break;
				}
			} else {
				$from = "
					rls.v_Drug d
				";
			}
			if (
				$region_nick != 'msk' && ( //для Москвы контроль наличия остатков отключен вне зависимости от настроек
					($ostat_type == 1 && in_array($options['select_drug_from_list'], array('jnvlp','request')))
					|| $ostat_type == 2 || $ostat_type == 3
				)
			) {
				$withFilterList = ["1=1"];
				$withFilterList[] = "sat.SubAccountType_Code = 1";
				$withFilterList[] = "dor.DrugOstatRegistry_Kolvo > 0";
				$withFilterList[] = "COALESCE(isdef.YesNo_Code, 0) = 0";
				if (!empty($data["WhsDocumentCostItemType_id"])) {
					$withFilterList[] = "dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
					$queryParams["WhsDocumentCostItemType_id"] = $data["WhsDocumentCostItemType_id"];
				}
				if (!empty($data["WhsDocumentSupply_id"])) {
					$withFilterList[] = "dsh.WhsDocumentSupply_id = :WhsDocumentSupply_id";
					$queryParams["WhsDocumentSupply_id"] = $data["WhsDocumentSupply_id"];
				}
				if (!empty($data["is_mi_1"]) && ($data["is_mi_1"] == "true")) {
					$filterList[] = "ntfr.PARENTID not in (1, 176)";
					$filterList[] = "ntfr.CLSNTFR_ID not in (1, 137, 138, 139, 140, 141, 142, 144, 146, 149, 153, 159, 176, 180, 181, 184, 199, 207)";
				}
				$ras = null;
				$apt = null;
				$ofi_subquery = "";
				if (!empty($options["recept_by_ras_drug_ostat"]) && $options["recept_by_ras_drug_ostat"] && $ostat_type != 2) {
					$ras = "ct.ContragentType_SysNick = 'store'";
				}
				if (!empty($options["recept_by_farmacy_drug_ostat"]) && $options["recept_by_farmacy_drug_ostat"]) {
					$apt = "ct.ContragentType_SysNick = 'apt'";
					if (!empty($options["recept_farmacy_type"]) && $options["recept_farmacy_type"] == "mo_farmacy" && !empty($queryParams["Lpu_id"])) {
						$ofi_subquery = "left join v_OrgFarmacyIndex ofi on ofi.OrgFarmacy_id = ofm.OrgFarmacy_id";
						$apt .= " and ofi.Lpu_id = :Lpu_id";
						//если пришел идентификатор отделения, то ищем идентификатор подразделения
						if (!empty($data["LpuSection_id"])) {
							//получение подразделения по отделению
							$query = "
								select LpuBuilding_id as \"LpuBuilding_id\"
								from v_LpuSection
								where LpuSection_id = :LpuSection_id
								limit 1
							";
							$data["LpuBuilding_id"] = $callObject->getFirstResultFromQuery($query, [
								"LpuSection_id" => $data["LpuSection_id"]
							]);
						}
						//если есть идентификатор подразделения, то проверяем прикрепления к подразделениям
						if (!empty($data["LpuBuilding_id"])) {
							//проверям есть ли прикрепление подразделения к каким либо аптекам
							$query = "
								select count(ofi.OrgFarmacyIndex_id) as \"cnt\"
								from v_OrgFarmacyIndex ofi
								where ofi.Lpu_id = :Lpu_id
								  and ofi.LpuBuilding_id = :LpuBuilding_id;
							";
							$cnt_data = $callObject->getFirstRowFromQuery($query, [
								"Lpu_id" => $data["Lpu_id"],
								"LpuBuilding_id" => $data["LpuBuilding_id"]
							]);
							if (!empty($cnt_data["cnt"])) {
								$withArr[] = "
									ofi_list as (
										select
											i_ofi.OrgFarmacy_id,
											i_ofi.Lpu_id,
											i_ofi.WhsDocumentCostItemType_id,
											i_ofi.Storage_id,
											i_ofi.OrgFarmacyIndex_IsNarko,
											ofi_cnt.storage_cnt
										from
											v_OrgFarmacyIndex i_ofi
											left join lateral (
												select sum(case when ii_ofi.Storage_id is not null then 1 else 0 end) as storage_cnt
												from v_OrgFarmacyIndex ii_ofi
												where ii_ofi.Lpu_id = i_ofi.Lpu_id
												  and ii_ofi.LpuBuilding_id = i_ofi.LpuBuilding_id
												  and ii_ofi.OrgFarmacy_id = i_ofi.OrgFarmacy_id
												  and ii_ofi.WhsDocumentCostItemType_id = i_ofi.WhsDocumentCostItemType_id
											) as ofi_cnt on true
										where i_ofi.Lpu_id = :Lpu_id
										  and i_ofi.LpuBuilding_id = :LpuBuilding_id 
									)
								";
								$ofi_subquery = "
									left join ofi_list ofi on ofi.OrgFarmacy_id = ofm.OrgFarmacy_id		
									left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
									left join rls.v_DrugComplexMnnName dcmn on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
									left join rls.v_ACTMATTERS am on am.ACTMATTERS_ID = dcmn.ACTMATTERS_id
									left join lateral (
										select (case when coalesce(am.NARCOGROUPID, 0) = 2 then 2 else 1 end) as IsNarko
									) as IsNarko on true
								";
								$apt .= " and (ofi.storage_cnt = 0 or (ofi.Storage_id = dor.Storage_id and ofi.OrgFarmacyIndex_IsNarko = IsNarko.IsNarko))"; //если для подразделения указано прикрепление к конкретным складам, то склад остатков должен совпадать со складом прикрепления, кроме того должен учитываться признак наркотики
								$queryParams["LpuBuilding_id"] = $data["LpuBuilding_id"];
								if (!empty($data["WhsDocumentCostItemType_id"])) { //прикрепление к подразделению производится всегда по определенной программе ЛЛО, поэтому если с формы передана программа, ищем прикрепление по ней
									$apt .= " and ofi.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
									$queryParams["WhsDocumentCostItemType_id"] = $data["WhsDocumentCostItemType_id"];
								}
							}
						}
					}
				}
				if (!empty($ras) && !empty($apt)) {
					$withFilterList[] = "(($ras) or ($apt))";
					$withFilterList[] = "dor.Storage_id is not null";
				} else if (!empty($ras) || !empty($apt)) {
					$withFilterList[] = empty($ras) ? $apt : $ras;
					$withFilterList[] = "dor.Storage_id is not null";
				}
				$withArr[] = "
					ostat_list as (
						select distinct
							dor.Drug_id
						from
							v_DrugOstatRegistry dor
							left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
							left join v_DrugShipment dsh on dsh.DrugShipment_id = dor.DrugShipment_id
							left join v_Contragent c on c.Org_id = dor.Org_id
							left join v_ContragentType ct on ct.ContragentType_id = c.ContragentType_id
							left join rls.v_Drug d on d.Drug_id = dor.Drug_id
							left join rls.v_Prep p on p.Prep_id = d.DrugPrep_id
							left join rls.v_PrepSeries ps on ps.PrepSeries_id = dor.PrepSeries_id
							left join v_YesNo isdef on isdef.YesNo_id = ps.PrepSeries_isDefect
							left join v_OrgFarmacy ofm on ofm.Org_id = dor.Org_id
							{$ofi_subquery}
						where " . implode(' and ', $withFilterList) . "
					)
				";
                if ($options['recept_drug_ostat_control']) {
                    $from .= "inner join ostat_list os on os.Drug_id = d.Drug_id";
                } else {
                    $from .= "left join ostat_list os on os.Drug_id = d.Drug_id";
                }
            }

			if ($region_nick == 'msk') { //для Москвы действует дополнительный фильтр на наличие медикамента в номенклатурном справочнике
				$spo_ulo_filters = array();

				if (!empty($data['Date'])) {
					$spo_ulo_filters[] = "(
						i_sud.SPOULODrug_begDT is null or
						i_sud.SPOULODrug_begDT <= :Date
					)";
					$spo_ulo_filters[] = "(
						i_sud.SPOULODrug_endDT is null or
						i_sud.SPOULODrug_endDT >= :Date
					)";
					$queryParams['Date'] = $data['Date'];
				}

				if (!empty($data['PrivilegeType_id']) && !empty($data['WhsDocumentCostItemType_id'])) {
					$query = "
						select
							(
								select
									df.DrugFinance_SysNick
								from
									v_PrivilegeType pt
									left join v_DrugFinance df on df.DrugFinance_id = pt.DrugFinance_id
								where
									pt.PrivilegeType_id = :PrivilegeType_id
								limit 1
							) as \"DrugFinance_SysNick\",
							(
								select
									wdcit.WhsDocumentCostItemType_Nick
								from
									v_WhsDocumentCostItemType wdcit
								where
									wdcit.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id
								limit 1
							) as \"WhsDocumentCostItemType_Nick\",
							(
								select
									rd.ReceptDiscount_Code
								from
									v_PrivilegeType pt
									left join v_ReceptDiscount rd on rd.ReceptDiscount_id = pt.ReceptDiscount_id
								where
									pt.PrivilegeType_id = :PrivilegeType_id
								limit 1
							) as \"ReceptDiscount_Code\"
					";
					$priv_data = $callObject->getFirstRowFromQuery($query, array(
						'PrivilegeType_id' => $data['PrivilegeType_id'],
						'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id']
					));
					if (!empty($priv_data['DrugFinance_SysNick'])) {
						if ($priv_data['DrugFinance_SysNick'] == 'fed' && $priv_data['WhsDocumentCostItemType_Nick'] == 'fl') { //федеральная льготная категория и программа «ОНЛС»
							$spo_ulo_filters[] = "(
								coalesce(i_sud.fed, 0) = 1 or
								coalesce(i_sud.reg, 0) = 1
							)";
						}
						if ($priv_data['DrugFinance_SysNick'] == 'reg' && $priv_data['WhsDocumentCostItemType_Nick'] == 'rl') { //региональная льготная категория и программа «РЛО»
							$spo_ulo_filters[] = "coalesce(i_sud.reg, 0) = 1";
						}
						if (($priv_data['DrugFinance_SysNick'] == 'fed' || $priv_data['DrugFinance_SysNick'] == 'reg') && $priv_data['ReceptDiscount_Code'] == '1') { //указана федеральная льготная категория или региональная льготная категория со 100% скидкой
							$spo_ulo_filters[] = "coalesce(i_sud.sale100, 0) = 1";
						}
						if ($priv_data['DrugFinance_SysNick'] == 'reg' && $priv_data['ReceptDiscount_Code'] == '2') { //указана региональная льготная категория с 50% скидкой
							$spo_ulo_filters[] = "coalesce(i_sud.sale50, 0) = 1";
						}
					}
				}

				$spo_ulo_where = count($spo_ulo_filters) > 0 ? " and ".implode(" and ", $spo_ulo_filters) : "";

				$from .= "
					left join lateral (
						select
							i_dn.DrugNomen_id
						from
							rls.v_DrugNomen i_dn
							inner join r50.SPOULODrug i_sud on cast(i_sud.NOMK_LS as varchar) = i_dn.DrugNomen_Code
						where
							i_dn.Drug_id = d.Drug_id
							{$spo_ulo_where}
						limit 1
					) drug_nomen on true
				";
				$filterList[] = "drug_nomen.DrugNomen_id is not null";
			}
			$with = count($withArr) > 0 ? "with\n" . implode(",\n", $withArr) : "";
			$additionFields = count($additionFieldsArr) > 0 ? "," . implode(",", $additionFieldsArr) : "";

			if (!empty($data["is_mi_1"]) && ($data["is_mi_1"] == "true")) {
				$from .= " left join rls.v_Prep p on p.Prep_id = d.DrugPrep_id";
				$from .= " left join rls.CLSNTFR ntfr on ntfr.CLSNTFR_ID = p.NTFRID";
			}
			$query = "
				{$with}
				select
					d.Drug_id as \"Drug_rlsid\",
					d.DrugComplexMnn_id as \"DrugComplexMnn_id\",
					d.Drug_Code as \"Drug_Code\",
					rtrim(d.Drug_Name) as \"Drug_Name\"
					{$additionFields}
				from
					{$from}
				where
					" . implode(" and ", $filterList) . "
				order by
					d.Drug_Name
				limit 500;
			";
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение списка остатков по медикаменту в аптеках
	 * @param $data
	 * @param $options
	 * @return array|bool
	 */
	public static function loadFarmacyRlsOstatList(Drug_model $callObject, $data, $options)
	{
		$region_nick = getRegionNick();
		$with = array();
		$order_by = array();
		$queryParams = [];
		$get_storage_kolvo = ($region_nick == "penza");
		//длительность пребывания медикаментов "в резерве" выраженная в днях
		$day_count = 3;
		//для Пензы обнуляем длительность резервирования, в таком случае резервирование будет актуально для рецепта на протяжении его действия
		if (getRegionNick() == "penza") {
			$day_count = null;
		}
		if ($day_count > 0) {
			$from_res = "
				left join lateral (
					select
						er.EvnRecept_setDate + CAST(:Day_Count||' days' as interval) as endDate
				) as end_dt on true
			";
			$queryParams["Day_Count"] = $day_count;
		} else {
			//если количество дней не задано, то дата окончания резерва считается по сроку действия
			$from_res = "
				left join v_ReceptValid rv on rv.ReceptValid_id = er.Receptvalid_id
				left join lateral (
					select
						(case
							when rv.ReceptValidType_id = 1 then er.EvnRecept_setDate + CAST(rv.ReceptValid_Value||' days' as interval)
							when rv.ReceptValidType_id = 2 then er.EvnRecept_setDate + CAST(rv.ReceptValid_Value||' months' as interval)
							when rv.ReceptValidType_id = 3 then er.EvnRecept_setDate + CAST(rv.ReceptValid_Value||' years' as interval)
							else null
						end) as endDate
				) as end_dt on true
			";
		}
		//если пришел идентификатор отделения, то ищем идентификатор подразделения (понадобится при определении прикреплений)
		if (!empty($data["LpuSection_id"])) {
			//получение подразделения по отделению
			$query = "
				select LpuBuilding_id as \"LpuBuilding_id\"
				from v_LpuSection
				where LpuSection_id = :LpuSection_id
				limit 1
			";
			$data["LpuBuilding_id"] = $callObject->getFirstResultFromQuery($query, [
				"LpuSection_id" => $data["LpuSection_id"]
			]);
		}
		if (!empty($data["OrgFarmacy_id"])) {
			//определение прикреплений
			if ($get_storage_kolvo) {
				$query_ofi = "
					select
						1 as exst,
						min(OrgFarmacyIndex_Index) as OrgFarmacyIndex_Index,
						OrgFarmacy_id,
						Storage_id
					from v_OrgFarmacyIndex
					where OrgFarmacy_id = :OrgFarmacy_id
					  and Lpu_id = :Lpu_id
					group by OrgFarmacy_id, Storage_id
				";
			} else {
				$query_ofi = "
					select
						1 as exst,
						OrgFarmacyIndex_Index,
						OrgFarmacy_id,
						cast(null as int8) as Storage_id
					from v_OrgFarmacyIndex
					where OrgFarmacy_id = :OrgFarmacy_id
					  and Lpu_id = :Lpu_id
					order by OrgFarmacyIndex_Index
					limit 1
				";
			}
			if (!empty($options["recept_by_farmacy_drug_ostat"]) && !empty($options["recept_farmacy_type"]) && $options["recept_farmacy_type"] == "mo_farmacy") {
				//если есть идентификатор подразделения, то проверяем прикрепления к подразделениям
				if (!empty($data["LpuBuilding_id"])) {
					//проверям есть ли прикрепление подразделения к каким либо аптекам
					$query = "
						select count(ofi.OrgFarmacyIndex_id) as cnt
						from v_OrgFarmacyIndex ofi
						where ofi.Lpu_id = :Lpu_id
						  and ofi.LpuBuilding_id = :LpuBuilding_id;
					";
					$cnt_data = $callObject->getFirstRowFromQuery($query, [
						"Lpu_id" => $data["Lpu_id"],
						"LpuBuilding_id" => $data["LpuBuilding_id"]
					]);
					if (!empty($cnt_data["cnt"])) {
						if ($get_storage_kolvo) {
							$query_ofi = "
								select
									1 as exst,
									i_ofi.OrgFarmacyIndex_Index,
									i_ofi.OrgFarmacy_id,
									i_ofi.Storage_id
								from v_OrgFarmacyIndex i_ofi
								where i_ofi.OrgFarmacy_id = :OrgFarmacy_id
								  and i_ofi.Lpu_id = :Lpu_id
								  and i_ofi.LpuBuilding_id = :LpuBuilding_id
								  and (:WhsDocumentCostItemType_id is null or i_ofi.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id)
							";
						} else {
							$query_ofi = "
								select
									1 as exst,
									i_ofi.OrgFarmacyIndex_Index,
									i_ofi.OrgFarmacy_id,
									cast(null as int8) as Storage_id
								from v_OrgFarmacyIndex i_ofi
								where i_ofi.OrgFarmacy_id = :OrgFarmacy_id
								  and i_ofi.Lpu_id = :Lpu_id
								  and i_ofi.LpuBuilding_id = :LpuBuilding_id
								  and (:WhsDocumentCostItemType_id is null or i_ofi.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id)
								order by i_ofi.OrgFarmacyIndex_Index
								limit 1
							";
						}
						$queryParams["LpuBuilding_id"] = $data["LpuBuilding_id"];
						$queryParams["WhsDocumentCostItemType_id"] = !empty($data["WhsDocumentCostItemType_id"]) ? $data["WhsDocumentCostItemType_id"] : null;
					}
				}
			}
			$reserve_enabled = (!$data['isKardio'] && $region_nick != 'msk'); //флаг "учет резервирования при расчете количества"
			$select_dor_kolvo = "to_char(coalesce(do1.DrugOstatRegistry_Kolvo, 0), '{$callObject->numericForm18_2}')";
			$from_res_subquery = "";
			if ($reserve_enabled) {
				$select_dor_kolvo = "to_char(case when coalesce(do1.DrugOstatRegistry_Kolvo - coalesce(rd.kolvo, 0), 0) <= 0 then 0 else coalesce(do1.DrugOstatRegistry_Kolvo - coalesce(rd.kolvo, 0), 0) end, '{$callObject->numericForm18_2}')";
				$from_res_subquery = "
					left join lateral (
						select coalesce(sum(EvnRecept_Kolvo), 0) as kolvo
						from
							v_EvnRecept er
							{$from_res}
						where er.Drug_rlsid = :Drug_rlsid
						  and er.ReceptDelayType_id is null
						  and end_dt.endDate >= tzgetdate()
						  and (er.EvnRecept_IsNotOstat is null or EvnRecept_IsNotOstat = (select YesNo_id from v_YesNo where YesNo_Code = 0))
						  and er.OrgFarmacy_id = :OrgFarmacy_id
						  and (ofi.Storage_id is null or er.Storage_id = ofi.Storage_id)
					) as rd on true
				";
			}

			$do_subquery = "
				left join lateral (
					select
						SUM(dor.DrugOstatRegistry_Kolvo) as DrugOstatRegistry_Kolvo,
						MAX(dor.DrugOstatRegistry_updDT) as DrugOstatRegistry_updDT
					from
						v_DrugOstatRegistry dor
						left join rls.v_PrepSeries ps on ps.PrepSeries_id = dor.PrepSeries_id
					where
						dor.Org_id = o.Org_id
						and dor.Drug_id = :Drug_rlsid
						and dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id
						and (ps.PrepSeries_IsDefect is null or ps.PrepSeries_IsDefect = (select YesNo_id from v_YesNo where YesNo_Code = 0))
						and (
							(
								ofi.Storage_id is null
								and dor.Storage_id is not null
							) or
							ofi.Storage_id = dor.Storage_id
						) 
				) do1 on true
			";

			if ($region_nick == 'msk') {
				if (!empty($data['Drug_id'])) {
					$with[] = "
						drug_list as (
							select distinct
								dn2.Drug_id 
							from
								rls.v_DrugNomen dn
								inner join r50.SPOULODrug sud on cast(sud.NOMK_LS as varchar) = dn.DrugNomen_Code
								inner join r50.SPOULODrug sud2 on
									coalesce(sud2.C_MNN, '0') = coalesce(sud.C_MNN, '0') and
									coalesce(sud2.C_LF, '0') = coalesce(sud.C_LF, '0') and
									coalesce(sud2.DosageId, '0') = coalesce(sud.DosageId, '0')
								inner join rls.v_DrugNomen dn2 on dn2.DrugNomen_Code = cast(sud2.NOMK_LS as varchar)
							where
								dn.Drug_id = :Drug_id and
								dn2.Drug_id is not null
						)
					";
				} else if (!empty($data['DrugComplexMnn_id'])) {
					$with[] = "
						drug_list as (
							select distinct
								dn2.Drug_id 
							from
								rls.Drug d
								inner join rls.v_DrugNomen dn on dn.Drug_id = d.Drug_id
								inner join r50.SPOULODrug sud on cast(sud.NOMK_LS as varchar) = dn.DrugNomen_Code
								inner join r50.SPOULODrug sud2 on
									coalesce(sud2.C_MNN, '0') = coalesce(sud.C_MNN, '0') and
									coalesce(sud2.C_LF, '0') = coalesce(sud.C_LF, '0') and
									coalesce(sud2.DosageId, '0') = coalesce(sud.DosageId, '0')
								inner join rls.v_DrugNomen dn2 on dn2.DrugNomen_Code = cast(sud2.NOMK_LS as varchar)
							where
								d.DrugComplexMnn_id = :DrugComplexMnn_id and
								dn2.Drug_id is not null
						)
					";
					$queryParams['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
				} else { //в противном случае собрать запрос не получится
					return false;
				}

				$do_subquery_from_array = array();

				//этот блок предназначен для поиска остатков по источнику финансирования не соответвтующему программе лло (статье расхода)
				if (!empty($data['DrugFinance_id'])) {
					$do_subquery_from_array[] = "dor.DrugFinance_id = :DrugFinance_id";
					$queryParams['DrugFinance_id'] = $data['DrugFinance_id'];
				} else {
					$do_subquery_from_array[] = "dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
				}

				$do_subquery_from = count($do_subquery_from_array) > 0 ? " and ".implode(" and ", $do_subquery_from_array) : "";

				$do_subquery = "
					left join lateral (
						select
							SUM(dor.DrugOstatRegistry_Kolvo) as DrugOstatRegistry_Kolvo,
							MAX(dor.DrugOstatRegistry_updDT) as DrugOstatRegistry_updDT
						from
							drug_list dl
							inner join v_DrugOstatRegistry dor on
								dor.Drug_id = dl.Drug_id and
								dor.Org_id = o.Org_id
								{$do_subquery_from}
							left join rls.v_PrepSeries ps on ps.PrepSeries_id = dor.PrepSeries_id
						where
							(ps.PrepSeries_IsDefect is null or ps.PrepSeries_IsDefect = (select YesNo_id from v_YesNo where YesNo_Code = 0))
							and (
								(
									ofi.Storage_id is null
									and dor.Storage_id is not null
								) or
								ofi.Storage_id = dor.Storage_id
							) 
					) do1 on true
				";
			}

			$with_clause = count($with) > 0 ? 'with '.implode(', ', $with) : '';

			$selectString = "
				ofarm.OrgFarmacy_id as \"OrgFarmacy_id\",
			    RTRIM(o.Org_Name) as \"OrgFarmacy_Name\",
			    RTRIM(ofarm.OrgFarmacy_HowGo) as \"OrgFarmacy_HowGo\",
			    IsFarmacy.YesNo_Code as \"OrgFarmacy_IsFarmacy\",
			    {$select_dor_kolvo} as \"DrugOstatRegistry_Kolvo\",
			    null as \"index_exists\",
			    s.Storage_id as \"Storage_id\",
			    s.Storage_Name as \"Storage_Name\"
			";
			$fromString = "
				v_OrgFarmacy ofarm
				inner join v_Org o on o.Org_id = ofarm.Org_id
				inner join v_YesNo IsFarmacy on IsFarmacy.YesNo_id = coalesce(ofarm.OrgFarmacy_IsFarmacy, 2)
				left join lateral (
					{$query_ofi}
				) as ofi on true
				{$do_subquery}
				{$from_res_subquery}
				left join v_Storage s on s.Storage_id = ofi.Storage_id
			";
			$whereString = "
					ofarm.OrgFarmacy_id = :OrgFarmacy_id
				and coalesce(ofarm.OrgFarmacy_IsEnabled, 2) = 2
			";
			$query = "
				{$with_clause}
				select {$selectString}
				from {$fromString}
				where {$whereString}
			";
			$queryParams["Lpu_id"] = isset($data["Lpu_id"]) ? $data["Lpu_id"] : null;
			$queryParams["Drug_rlsid"] = $data["Drug_rlsid"];
			$queryParams["OrgFarmacy_id"] = $data["OrgFarmacy_id"];
			$queryParams["WhsDocumentCostItemType_id"] = $data["WhsDocumentCostItemType_id"];
		} else {
			$contrList = ["(1=1)"];
			$ostatList = ["(1=1)"];
			$where = "";
			$ostat_type = 0;
            $only_attached = false; //флаг, отображение только прикрепленных аптек

            if ($region_nick == 'msk') { //для Москвы отображаем только прикрепленные аптеки
                $only_attached = true;
            }
			if ($options["recept_drug_ostat_viewing"]) {
				$ostat_type = 1;
			}
			if ($options["recept_drug_ostat_control"]) {
				$ostat_type = 2;
			}
			if ($options["recept_empty_drug_ostat_allow"]) {
				$ostat_type = 3;
			}
			//Возможен выбор только из таких контрагентов
			$contrList[] = "ct.ContragentType_SysNick in ('store','apt')";
			$contr_join = '';
			//Выбор конкретного типа контрагента в зависимости от настроек
			if ($ostat_type > 0) {
				$ras = null;
				$apt = null;
				if (!empty($options["recept_by_ras_drug_ostat"]) && $options["recept_by_ras_drug_ostat"]) {
					$ras = "ct.ContragentType_SysNick = 'store'";
				}
				if (!empty($options["recept_by_farmacy_drug_ostat"]) && $options["recept_by_farmacy_drug_ostat"]) {
					$apt = "ct.ContragentType_SysNick = 'apt'";
					if (!empty($options["recept_farmacy_type"]) && $options["recept_farmacy_type"] == "mo_farmacy") {
						$contr_join = " inner join v_OrgFarmacyIndex OFI on (OFI.Org_id = c.Org_id and OFI.Lpu_id = :Lpu_id)";
					}
				}
				if (!empty($ras) && !empty($apt)) {
					$contrList[] = "(($ras) or ($apt))";
				} else if (!empty($ras) || !empty($apt)) {
					$contrList[] = empty($ras) ? $apt : $ras;
				}
			}
			if (
				!empty($options["select_drug_from_list"])
				&& in_array($options["select_drug_from_list"], ["allocation", "request_and_allocation"])
				&& !empty($data["WhsDocumentSupply_id"])
			) {
				$ostatList[] = "dsh.WhsDocumentSupply_id = :WhsDocumentSupply_id";
				$queryParams["WhsDocumentSupply_id"] = $data["WhsDocumentSupply_id"];
			}
			$with_ofi = "
				ofi_list as (
					select
						1 as exst,
						OrgFarmacyIndex_Index,
						OrgFarmacy_id,
						Storage_id
					from v_OrgFarmacyIndex
					where Lpu_id = :Lpu_id
				)
			";
			if (!empty($options["recept_by_farmacy_drug_ostat"]) && !empty($options["recept_farmacy_type"]) && $options["recept_farmacy_type"] == "mo_farmacy") {
				//если есть идентификатор подразделения, то проверяем прикрепления к подразделениям
				if (!empty($data['LpuBuilding_id']) && $region_nick != 'msk') { //Для москвы не учитываем прикрепление к подразделению
					//проверям есть ли прикрепление подразделения к каким либо аптекам
					$query = "
						select count(ofi.OrgFarmacyIndex_id) as \"cnt\"
						from v_OrgFarmacyIndex ofi
						where ofi.Lpu_id = :Lpu_id
						  and ofi.LpuBuilding_id = :LpuBuilding_id;
					";
					$cnt_data = $callObject->getFirstRowFromQuery($query, [
						"Lpu_id" => $data["Lpu_id"],
						"LpuBuilding_id" => $data["LpuBuilding_id"]
					]);
					if (!empty($cnt_data["cnt"])) {
						$with_ofi = "
							ofi_list as (
								select
									1 as exst,
									i_ofi.OrgFarmacyIndex_Index,
									i_ofi.OrgFarmacy_id,
									i_ofi.Storage_id
								from v_OrgFarmacyIndex i_ofi
								where i_ofi.Lpu_id = :Lpu_id
								  and i_ofi.LpuBuilding_id = :LpuBuilding_id
								  and (:WhsDocumentCostItemType_id is null or i_ofi.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id)
							)
						";
						$queryParams["LpuBuilding_id"] = $data["LpuBuilding_id"];
						$queryParams["WhsDocumentCostItemType_id"] = !empty($data["WhsDocumentCostItemType_id"]) ? $data["WhsDocumentCostItemType_id"] : null;
					}
				}
                //выводим только прикрепленные аптеки
                $only_attached = true;
			}
			$with[] = $with_ofi;

            //выводим только прикрепленные аптеки
			if($only_attached) {
                $where .= " and ofi.exst = 1 ";
            }

			$reserve_enabled = (!$data['isKardio'] && $region_nick != 'msk'); //флаг "учет резервирования при расчете количества"
			$select_dor_kolvo = "to_char(coalesce(do1.DrugOstatRegistry_Kolvo, 0), '{$callObject->numericForm18_2}')";
			$from_res_subquery = "";

			if ($reserve_enabled) {
				$select_dor_kolvo = "to_char(case when coalesce(do1.DrugOstatRegistry_Kolvo - coalesce(rd.kolvo, 0), 0) <= 0 then 0 else coalesce(do1.DrugOstatRegistry_Kolvo - coalesce(rd.kolvo, 0), 0) end, '{$callObject->numericForm18_2}')";
				$from_res_subquery = "
					left join lateral (
						select coalesce(sum(EvnRecept_Kolvo),0) as kolvo
						from
							v_EvnRecept er
							{$from_res}
						where er.Drug_rlsid = :Drug_rlsid
						  and er.ReceptDelayType_id is null
						  and end_dt.endDate >= tzgetdate()
						  and (er.EvnRecept_IsNotOstat is null or EvnRecept_IsNotOstat = (select YesNo_id from v_YesNo where YesNo_Code = 0))
						  and er.OrgFarmacy_id = ofarm.OrgFarmacy_id
						  and (ofi.Storage_id is null or er.Storage_id = ofi.Storage_id)
					) as rd on true
				";
			}

			$do_subquery = "
				left join lateral (
					select
						SUM(dor.DrugOstatRegistry_Kolvo) as DrugOstatRegistry_Kolvo,
						MAX(dor.DrugOstatRegistry_Cost) as DrugOstatRegistry_Cost,
						MAX(dor.DrugOstatRegistry_updDT) as DrugOstatRegistry_updDT
					from v_DrugOstatRegistry dor
						left join v_DrugShipment dsh on dsh.DrugShipment_id = dor.DrugShipment_id
						left join rls.v_PrepSeries ps on ps.PrepSeries_id = dor.PrepSeries_id
					where
						dor.Org_id = o.Org_id
						and dor.Drug_id = :Drug_rlsid
						and dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id							
						and (ps.PrepSeries_IsDefect is null or ps.PrepSeries_IsDefect = (select YesNo_id from v_YesNo where YesNo_Code = 0))
						and ".implode(' and ', $ostatList)."
						and (
							(
								ofi.Storage_id is null
								and dor.Storage_id is not null
							) or
							ofi.Storage_id = dor.Storage_id
						) 
				) do1 on true
			";

			if ($region_nick == 'msk') {
				if (!empty($data['Drug_id'])) {
					$with[] = "
						drug_list as (
							select distinct
								dn2.Drug_id 
							from
								rls.v_DrugNomen dn
								inner join r50.SPOULODrug sud on cast(sud.NOMK_LS as varchar) = dn.DrugNomen_Code
								inner join r50.SPOULODrug sud2 on
									coalesce(sud2.C_MNN, '0') = coalesce(sud.C_MNN, '0') and
									coalesce(sud2.C_LF, '0') = coalesce(sud.C_LF, '0') and
									coalesce(sud2.DosageId, '0') = coalesce(sud.DosageId, '0')
								inner join rls.v_DrugNomen dn2 on dn2.DrugNomen_Code = cast(sud2.NOMK_LS as varchar)
							where
								dn.Drug_id = :Drug_id and
								dn2.Drug_id is not null
						)
					";
				} else if (!empty($data['DrugComplexMnn_id'])) {
					$with[] = "
						drug_list as (
							select distinct
								dn2.Drug_id 
							from
								rls.Drug d
								inner join rls.v_DrugNomen dn on dn.Drug_id = d.Drug_id
								inner join r50.SPOULODrug sud on cast(sud.NOMK_LS as varchar) = dn.DrugNomen_Code
								inner join r50.SPOULODrug sud2 on
									coalesce(sud2.C_MNN, '0') = coalesce(sud.C_MNN, '0') and
									coalesce(sud2.C_LF, '0') = coalesce(sud.C_LF, '0') and
									coalesce(sud2.DosageId, '0') = coalesce(sud.DosageId, '0')
								inner join rls.v_DrugNomen dn2 on dn2.DrugNomen_Code = cast(sud2.NOMK_LS as varchar)
							where
								d.DrugComplexMnn_id = :DrugComplexMnn_id and
								dn2.Drug_id is not null
						)
					";
					$queryParams['DrugComplexMnn_id'] = $data['DrugComplexMnn_id'];
				} else { //в противном случае собрать запрос не получится
					return false;
				}

				$do_subquery = "
					left join lateral (
						select
							SUM(dor.DrugOstatRegistry_Kolvo) as DrugOstatRegistry_Kolvo,
							MAX(dor.DrugOstatRegistry_Cost) as DrugOstatRegistry_Cost,
							MAX(dor.DrugOstatRegistry_updDT) as DrugOstatRegistry_updDT
						from
							drug_list dl
							inner join v_DrugOstatRegistry dor on
								dor.Drug_id = dl.Drug_id and
								dor.Org_id = o.Org_id and
								dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id
							left join v_DrugShipment dsh on dsh.DrugShipment_id = dor.DrugShipment_id
							left join rls.v_PrepSeries ps on ps.PrepSeries_id = dor.PrepSeries_id
						where
							(ps.PrepSeries_IsDefect is null or ps.PrepSeries_IsDefect = (select YesNo_id from v_YesNo where YesNo_Code = 0))
							and ".implode(' and ', $ostatList)."
							and (
								(
									ofi.Storage_id is null
									and dor.Storage_id is not null
								) or
								ofi.Storage_id = dor.Storage_id
							)
					) do1 on true
				";
			}

			$with[] = "
				contr_org as (
					select distinct
						c.Org_id,
						ct.ContragentType_id,
						ct.ContragentType_SysNick
					from
						v_Contragent c
						left join v_ContragentType ct on ct.ContragentType_id = c.ContragentType_id
						{$contr_join}
					where
						".implode(' and ', $contrList)."
				)
			";
			$with_clause = count($with) > 0 ? 'with '.implode(', ', $with) : '';

			if (!empty($options['recept_by_farmacy_drug_ostat']) && $region_nick == 'msk') {
				$order_by[] = "do1.DrugOstatRegistry_Kolvo desc";
			}

			$order_by[] = "ofi.exst desc";
			$order_by[] = "ofi.OrgFarmacyIndex_Index";
			$order_by[] = "o.Org_Name";

			$order_by_clause = count($with) > 0 ? 'order by '.implode(', ', $order_by) : '';

			$query = "
				{$with_clause}
				select
					coalesce(ofarm.OrgFarmacy_id, -1*c.Org_id) as \"OrgFarmacy_id\",
					RTRIM(o.Org_Name) as \"OrgFarmacy_Name\",
					rtrim(coalesce(ofarm.OrgFarmacy_HowGo, '')) as \"OrgFarmacy_HowGo\",
					IsFarmacy.YesNo_Code as \"OrgFarmacy_IsFarmacy\",
					{$select_dor_kolvo} as \"DrugOstatRegistry_Kolvo\",
					do1.DrugOstatRegistry_Cost as \"DrugOstatRegistry_Cost\",
                    coalesce(to_char(do1.DrugOstatRegistry_updDT, 'dd.mm.yyyy HH24:MI') , '') as \"DrugOstatRegistry_updDT\",
					ofi.exst as \"index_exists\",
					s.Storage_id as \"Storage_id\",
					s.Storage_Name as \"Storage_Name\"
				from
					contr_org c
					inner join v_Org o on o.Org_id = c.Org_id
					left join v_OrgFarmacy ofarm on ofarm.Org_id = c.Org_id
					inner join v_YesNo IsFarmacy on IsFarmacy.YesNo_id = coalesce(ofarm.OrgFarmacy_IsFarmacy, 2)
					left join lateral (
						select
							exst,
							" . ($get_storage_kolvo ? "min(OrgFarmacyIndex_Index) as OrgFarmacyIndex_Index," : "OrgFarmacyIndex_Index,") . "
							" . ($get_storage_kolvo ? "Storage_id" : "cast(null as int8) as Storage_id") . "
						from
							ofi_list
						where
							ofi_list.OrgFarmacy_id = ofarm.OrgFarmacy_id
						" . ($get_storage_kolvo ? "group by Storage_id, exst" : "order by OrgFarmacyIndex_Index") . "
						" . ($get_storage_kolvo ? "" : "limit 1") . "
					) as ofi on true
					{$do_subquery}
					{$from_res_subquery}
					left join v_Storage s on s.Storage_id = ofi.Storage_id
				where
					coalesce(ofarm.OrgFarmacy_IsEnabled, 2) = 2
					{$where}
				{$order_by_clause}
			";
			$queryParams["Lpu_id"] = isset($data["Lpu_id"]) ? $data["Lpu_id"] : null;
			$queryParams["Drug_rlsid"] = $data["Drug_rlsid"];
			$queryParams["OrgFarmacy_id"] = $data["OrgFarmacy_id"];
			$queryParams["WhsDocumentCostItemType_id"] = $data["WhsDocumentCostItemType_id"];
		}
		$of_list = $callObject->queryResult($query, $queryParams);
		if (!is_array($of_list) || count($of_list) == 0) {
			return false;
		}
		$of_array = [];
		//перепаковка данных таким образом, чтобы идентификатор аптеки был уникален
		//группировка данных по аптекам
		foreach ($of_list as $of_data) {
			$id = $of_data["OrgFarmacy_id"];
			if (!isset($of_array[$id])) {
				$of_array[$id] = $of_data;
				$of_array[$id]["storage_list"] = [];
			}
			if (!empty($of_data["Storage_id"])) {
				$of_array[$id]["storage_list"][] = [
					"Storage_id" => $of_data["Storage_id"],
					"Storage_Name" => $of_data["Storage_Name"],
					"Storage_Kolvo" => $of_data["DrugOstatRegistry_Kolvo"]
				];
			} else {
				if (!empty($of_array[$id]["Storage_id"])) {
					$of_array[$id]["Storage_id"] = null;
					$of_array[$id]["Storage_Name"] = null;
					$of_array[$id]["DrugOstatRegistry_Kolvo"] = $of_data["DrugOstatRegistry_Kolvo"];
				}
			}
		}
		//приведение массива данных, к виду пригодному для вывода
		foreach ($of_array as $of_id => $of_data) {
			$id_str = "";
			$name_str = "";
			$kolvo_str = "";
			if (count($of_data["storage_list"]) > 0) {
				foreach ($of_data["storage_list"] as $storage_data) {
					$id_str .= (!empty($id_str) ? "<br/>" : "") . $storage_data["Storage_id"];
					$name_str .= (!empty($name_str) ? "<br/>" : "") . $storage_data["Storage_Name"];
					$kolvo_str .= (!empty($kolvo_str) ? "<br/>" : "") . (!empty($storage_data["Storage_Kolvo"]) ? $storage_data["Storage_Kolvo"] : "0");
				}
			} else {
				$kolvo_str = $of_data["DrugOstatRegistry_Kolvo"];
			}
			$of_array[$of_id]["Storage_id"] = $id_str;
			$of_array[$of_id]["Storage_Name"] = $name_str;
			$of_array[$of_id]["Storage_Kolvo"] = $kolvo_str;
		}
		$of_array = array_values($of_array);
		return $of_array;
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	public static function loadDrugStateGrid(Drug_model $callObject, $data)
	{
		$params = [];
		$and = "";
		if (!empty($data["DrugRequestPeriod_id"])) {
			$and .= " and DP.DrugRequestPeriod_id = :DrugRequestPeriod_id";
			$params["DrugRequestPeriod_id"] = $data["DrugRequestPeriod_id"];
		}
		if (!empty($data["ReceptFinance_id"])) {
			$and .= " and RF.ReceptFinance_id = :ReceptFinance_id";
			$params["ReceptFinance_id"] = $data["ReceptFinance_id"];
		}
		if (!empty($data["DrugProtoMnn_Code"])) {
			$and .= " and DPM.DrugProtoMnn_Code = :DrugProtoMnn_Code";
			$params["DrugProtoMnn_Code"] = $data["DrugProtoMnn_Code"];
		}
		if (!empty($data["DrugProtoMnn_Name"])) {
			$and .= " and DPM.DrugProtoMnn_Name ilike :DrugProtoMnn_Name";
			$params["DrugProtoMnn_Name"] = "%" . $data["DrugProtoMnn_Name"] . "%";
		}
		if (!empty($data["Drug_Code"])) {
			$and .= " and D.Drug_Code = :Drug_Code";
			$params["Drug_Code"] = $data["Drug_Code"];
		}
		if (!empty($data["Drug_Name"])) {
			$and .= " and D.Drug_Name ilike :Drug_Name";
			$params["Drug_Name"] = "%" . $data["Drug_Name"] . "%";
		}
		$sql = "
			select
			-- select
				DS.DrugState_id as \"DrugState_id\",
				coalesce(RF.ReceptFinance_Name, '') as \"ReceptFinance_Name\",
				coalesce(DPM.DrugProtoMnn_Code, '') as \"DrugProtoMnn_Code\",
				coalesce(DPM.DrugProtoMnn_Name, '') as \"DrugProtoMnn_Name\",
				coalesce(D.Drug_Code, '') as \"Drug_Code\",
				coalesce(D.Drug_Name, '') as \"Drug_Name\",
				coalesce(DS.DrugState_Price, 0) as \"DrugState_Price\",
				to_char(DS.DrugState_insDT, '{$callObject->dateTimeForm104}') as \"DrugState_insDT\",
				to_char(DS.DrugState_updDT, '{$callObject->dateTimeForm104}') as \"DrugState_updDT\"
			-- end select
			from
			-- from
				v_DrugState DS
				left join v_DrugProto DP on DP.DrugProto_id = DS.DrugProto_id
				left join v_ReceptFinance RF on RF.ReceptFinance_id = DP.ReceptFinance_id
				left join v_DrugProtoMnn DPM on DPM.DrugProtoMnn_id = DS.DrugProtoMnn_id
				left join v_Drug D on D.Drug_id = DS.Drug_id
			-- end from
			where
			-- where
				DPM.DrugProtoMnn_Code <> 0
				{$and}
			-- end where
			order by
			-- order by
				DS.DrugState_updDT desc,
			    DPM.DrugProtoMnn_Name
			-- end order by
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_count
		 */
		$result = $callObject->db->query(getLimitSQLPH($sql, $data["start"], $data["limit"]), $params);
		$result_count = $callObject->db->query(getCountSQLPH($sql), $params);
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
	 * @param $data
	 * @return array|bool
	 */
	public static function loadDrugState(Drug_model $callObject, $data)
	{
		$params = [
			"DrugState_id" => $data["DrugState_id"]
		];
		$sql = "
			select
				DS.DrugState_id as \"DrugState_id\",
				DP.DrugRequestPeriod_id as \"DrugRequestPeriod_id\",
				RF.ReceptFinance_id as \"ReceptFinance_id\",
				DP.DrugProto_id as \"DrugProto_id\",
				DPM.DrugProtoMnn_id as \"DrugProtoMnn_id\",
				D.Drug_id as \"Drug_id\",
				DS.DrugState_Price as \"DrugState_Price\"
			from
				v_DrugState DS
				left join v_DrugProto DP on DP.DrugProto_id = DS.DrugProto_id
				left join v_ReceptFinance RF on RF.ReceptFinance_id = DP.ReceptFinance_id
				left join v_DrugProtoMnn DPM on DPM.DrugProtoMnn_id = DS.DrugProtoMnn_id
				left join v_Drug D on D.Drug_id = DS.Drug_id
			where DS.DrugState_id = :DrugState_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	public static function loadDrugProtoCombo(Drug_model $callObject, $data)
	{
		$params = [];
		$and = "";
		if (isset($data["ReceptFinance_id"]) && $data["ReceptFinance_id"] > 0) {
			$params["ReceptFinance_id"] = $data["ReceptFinance_id"];
			$and .= " and DP.ReceptFinance_id = :ReceptFinance_id";
		}
		if (isset($data["DrugRequestPeriod_id"]) && $data["DrugRequestPeriod_id"] > 0) {
			$params["DrugRequestPeriod_id"] = $data["DrugRequestPeriod_id"];
			$and .= " and DP.DrugRequestPeriod_id = :DrugRequestPeriod_id";
		}
		$sql = "
			select *
			from v_DrugProto DP
			where DP.DrugRequestPeriod_id is not null
			{$and}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение данных для формы признания рецепта неправильно выписанным
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function loadReceptWrongInfo(Drug_model $callObject, $data)
	{
		$queryParams = [];
		$query = "
			select
				ReceptWrong_id as \"ReceptWrong_id\",
			    EvnRecept_id as \"EvnRecept_id\",
			    Org_id as \"Org_id\",
			    ReceptWrong_Decr as \"ReceptWrong_Decr\"
			from ReceptWrong
			where EvnRecept_id = :EvnRecept_id
			limit 1
		";
		$queryParams["EvnRecept_id"] = $data["EvnRecept_id"];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (доп инфа для формы медотводов)");
		}
		return $result->result("array");
	}

	/**
	 * Получение списка аптек для комбобокса
	 * @param $data
	 * @return array|bool|false
	 */
	public static function loadOrgFarmacyCombo(Drug_model $callObject, $data)
	{
		$where = [];
		$params = [];
		if (!empty($data["OrgFarmacy_id"])) {
			$where[] = "ofr.OrgFarmacy_id = :OrgFarmacy_id";
			$params["OrgFarmacy_id"] = $data["OrgFarmacy_id"];
		} else {
			if (!empty($data["query"])) {
				$where[] = "(ofr.OrgFarmacy_Code ilike :query or ofr.OrgFarmacy_Nick ilike :query)";
				$params["query"] = $data["query"] . "%";
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
				ofr.OrgFarmacy_id as \"OrgFarmacy_id\",
				coalesce(CAST(ofr.OrgFarmacy_Code as varchar), '') as \"OrgFarmacy_Code\",
				coalesce(ofr.OrgFarmacy_Name, '') as \"OrgFarmacy_Name\",
				coalesce(ofr.OrgFarmacy_Nick, '') as \"OrgFarmacy_Nick\",
				coalesce(ofr.OrgFarmacy_HowGo, '') as \"OrgFarmacy_HowGo\"
			from v_OrgFarmacy ofr
			{$where_clause}
		";
		$result = $callObject->queryResult($query, $params);
		return is_array($result) && count($result) ? $result : false;
	}

	/**
	 * Получение списка складов аптеки для комбобокса
	 * @param $data
	 * @return array|bool|false
	 */
	public static function loadOrgFarmacyStorageCombo(Drug_model $callObject, $data)
	{
		$query = "
			select distinct
				s.Storage_id as \"Storage_id\",
				s.Storage_Name as \"Storage_Name\"
			from
				v_OrgFarmacy ofr
				left join v_StorageStructLevel ssl on ssl.Org_id = ofr.Org_id
				left join v_Storage s on s.Storage_id = ssl.Storage_id
			where ofr.OrgFarmacy_id = :OrgFarmacy_id
			  and ssl.Storage_id is not null;
		";
		$result = $callObject->queryResult($query, $data);
		return is_array($result) && count($result) ? $result : false;
	}
}