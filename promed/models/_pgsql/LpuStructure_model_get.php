<?php

class LpuStructure_model_get
{
	/**
	 * @param CI_DB_driver $db
	 * @param $data
	 * @param $parent_object
	 * @return array|bool
	 */
	public static function GetMedServiceNodeList(CI_DB_driver $db, $data, $parent_object)
	{
		$params = [];
		$filterArray = [];
		//В дереве структуры МО отображать службы только на тех уровнях, на которых они заведены
		switch ($parent_object) {
			case "lpu":
				$params["Lpu_id"] = $data["object_id"];
				$filterArray[] = "ms.Lpu_id = :Lpu_id and ms.LpuBuilding_id is null and ms.LpuUnit_id is null and ms.LpuSection_id is null";
				break;
			case "lpubuilding":
				$params["LpuBuilding_id"] = $data["object_id"];
				$filterArray[] = "ms.LpuBuilding_id = :LpuBuilding_id and ms.LpuUnitType_id is null and ms.LpuUnit_id is null and ms.LpuSection_id is null";
				break;
			case "lpuunittype":
				$params["LpuBuilding_id"] = $data["object_id"];
				$params["LpuUnitType_id"] = $data["LpuUnitType_id"];
				$filterArray[] = "ms.LpuBuilding_id = :LpuBuilding_id and ms.LpuUnitType_id = :LpuUnitType_id and ms.LpuUnit_id is null and ms.LpuSection_id is null";
				break;
			case "lpuunit":
				$params["LpuUnit_id"] = $data["object_id"];
				$filterArray[] = "ms.LpuUnit_id = :LpuUnit_id and ms.LpuSection_id is null";
				break;
			case "lpusection":
				$params["LpuSection_id"] = $data["object_id"];
				$filterArray[] = "ms.LpuSection_id = :LpuSection_id";
				break;
		}
		$filterString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$sql = "
			select
				ms.MedService_id as \"MedService_id\",
				ms.MedService_Name as \"MedService_Name\",
				case when ms.MedService_endDT is not null and ms.MedService_endDT < tzgetdate() then 'medservice-closed16' else 'medservice16' end as \"iconCls\",
				ms.LpuBuilding_id as \"LpuBuilding_id\",
				ms.LpuSection_id as \"LpuSection_id\",
				ms.LpuUnit_id as \"LpuUnit_id\",
				ms.Lpu_id as \"Lpu_id\",
				mst.MedServiceType_SysNick as \"MedServiceType_SysNick\",
				eq.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\",
				0 as \"leafcount\"
			from
				v_MedService ms 
				left join v_MedServiceType mst  on mst.MedServiceType_id = ms.MedServiceType_id
				left join lateral (
					select eq.ElectronicQueueInfo_id
					from v_ElectronicQueueInfo eq 
					where eq.MedService_id = ms.MedService_id
					limit 1
				) as eq on true
			{$filterString}
			order by ms.MedService_Name
		";
		/**@var CI_DB_result $result */
		$result = $db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CI_DB_driver $db
	 * @param $data
	 * @return array|bool
	 */
	public static function GetMedServiceAppNodeList(CI_DB_driver $db, $data)
	{
		$params = [];
		$params["MedService_pid"] = $data["object_id"];
		$sql = "
			select
				ms.MedService_id as \"MedService_id\",
				ms.MedService_Name as \"MedService_Name\",
				ms.LpuBuilding_id as \"LpuBuilding_id\",
				ms.LpuSection_id as \"LpuSection_id\",
				ms.LpuUnit_id as \"LpuUnit_id\",
				ms.Lpu_id as \"Lpu_id\",
				mst.MedServiceType_SysNick as \"MedServiceType_SysNick\",
				(select count(1) from v_MedService ms2 where ms2.MedService_pid = ms.MedService_id) as \"leafcount\"
			from
				v_MedService ms 
				left join v_MedServiceType mst  on mst.MedServiceType_id = ms.MedServiceType_id
			where ms.MedService_pid = :MedService_pid
			order by ms.MedService_Name
		";
		/**
		 * @var CI_DB_result $result
		 */
		$result = $db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CI_DB_driver $db
	 * @param $data
	 * @param $parent_object
	 * @return array|bool
	 */
	public static function GetStorageNodeList(CI_DB_driver $db, $data, $parent_object)
	{
		$params = [];
		$filterArray = [];
		$leafcount = "0";

		//В дереве структуры МО отображать службы только на тех уровнях, на которых они заведены
		switch ($parent_object) {
			case "title":
				$params["Lpu_id"] = !empty($data["Lpu_id"]) ? $data["Lpu_id"] : $data["session"]["lpu_id"];
				$filterArray[] = "SSL.Lpu_id = :Lpu_id and S.Storage_pid is null";
				$leafcount = "(select count(CS.Storage_id) from v_Storage CS  where CS.Storage_pid = S.Storage_id)";
				break;
			case "lpu":
				$params["Lpu_id"] = $parent_object == "lpu" ? $data["object_id"] : $data["Lpu_id"];
				$filterArray[] = "SSL.Lpu_id = :Lpu_id and SSL.LpuBuilding_id is null and SSL.LpuUnit_id is null and SSL.LpuSection_id is null and SSL.MedService_id is null";
				break;
			case "lpubuilding":
				$params["LpuBuilding_id"] = $data["object_id"];
				$filterArray[] = "SSL.LpuBuilding_id = :LpuBuilding_id and SSL.LpuUnit_id is null and SSL.LpuSection_id is null and SSL.MedService_id is null";
				break;
			case "lpuunit":
				$params["LpuUnit_id"] = $data["object_id"];
				$filterArray[] = "SSL.LpuUnit_id = :LpuUnit_id and SSL.LpuSection_id is null and SSL.MedService_id is null";
				break;
			case "lpusection":
				$params["LpuSection_id"] = $data["object_id"];
				$filterArray[] = "SSL.LpuSection_id = :LpuSection_id and SSL.MedService_id is null";
				break;
			case "medservice":
				$params["MedService_id"] = $data["object_id"];
				$filterArray[] = "SSL.MedService_id = :MedService_id";
				break;
			case "storage":
				$params["Storage_pid"] = $data["object_id"];
				$filterArray[] = "S.Storage_pid = :Storage_pid";
				$leafcount = "(select count(CS.Storage_id) from v_Storage CS  where CS.Storage_pid = S.Storage_id)";
				break;
		}
		$filterString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		if (in_array($parent_object, ["title", "storage"])) {
			//В этих режимах нет дополнительных фильтров по элементам структуры, поэтому записи могут двоиться. Чтобы этого избежать, нужна отдельная версия запроса
			$selectString = "
				distinct
				S.Storage_id as \"Storage_id\",
				S.Storage_Name as \"Storage_Name\",
				null as \"LpuBuilding_id\",
				null as \"LpuSection_id\",
				null as \"LpuUnit_id\",
				SSL.Lpu_id as \"Lpu_id\",
				null as \"MedService_id\",
				case when S.Storage_endDate is not null and S.Storage_endDate < tzgetdate() then 'product-closed16' else 'product16' end as \"iconCls\",
				merch_ms.MedService_Nick as \"MerchMedService_Nick\",
				{$leafcount} as \"leafcount\"
			";
			$sql = "
				select {$selectString}
				from
					v_StorageStructLevel SSL 
					inner join v_Storage S on S.Storage_id = SSL.Storage_id
					LEFT JOIN LATERAL (
						select i_ms.MedService_Nick
						from 
							v_Storage i_s
							left join v_StorageStructLevel i_ssl  on i_ssl.Storage_id = i_s.Storage_id
							left join v_MedService i_ms  on i_ms.MedService_id = i_ssl.MedService_id 
							left join v_MedServiceType i_mst  on i_mst.MedServiceType_id = i_ms.MedServiceType_id
							LEFT JOIN LATERAL (
								select
									(case when i_s.Storage_id = S.Storage_id then 1 else 2 end) as val
							) as ord on true
						where
							(
								i_s.Storage_id = S.Storage_id or
								i_s.Storage_id = S.Storage_pid
							) and
							i_mst.MedServiceType_SysNick = 'merch'
						order by ord.val
						limit 1	
					) as merch_ms on true
				{$filterString}
				order by S.Storage_Name
			";
		} else {
			$selectString = "
				S.Storage_id as \"Storage_id\",
				S.Storage_Name as \"Storage_Name\",
				SSL.LpuBuilding_id as \"LpuBuilding_id\",
				SSL.LpuSection_id as \"LpuSection_id\",
				SSL.LpuUnit_id as \"LpuUnit_id\",
				SSL.Lpu_id as \"Lpu_id\",
				case when S.Storage_endDate is not null and S.Storage_endDate < tzgetdate() then 'product-closed16' else 'product16' end as \"iconCls\",
				SSL.MedService_id as \"MedService_id\",
				{$leafcount} as \"leafcount\"
			";
			$fromString = "
				v_StorageStructLevel SSL 
				inner join v_Storage S  on S.Storage_id = SSL.Storage_id
			";
			$orderByString = "
				S.Storage_Name
			";
			$sql = "
				select {$selectString}
				from {$fromString}
				{$filterString}
				order by {$orderByString}
			";
		}
		/**@var CI_DB_result $result */
		$result = $db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CI_DB_driver $db
	 * @param $data
	 * @return array|bool
	 */
	public static function GetLpuNodeList(CI_DB_driver $db, $data)
	{
		$filter = ($data["Lpu_id"] > 0) ? "where Lpu_id = " . $data["Lpu_id"] : "";
		$selectString = "
			Lpu.Lpu_id as \"Lpu_id\", 
			Lpu.Lpu_Nick as \"Lpu_Name\", 
			MALT.MesAgeLpuType_Code as \"MesAgeLpuType_Code\"
		";
		$fromString = "
			v_Lpu Lpu 
			left join v_MesAgeLpuType MALT  on MALT.MesAgeLpuType_id = Lpu.MesAgeLpuType_id
		";
		$orderByString = "Lpu.Lpu_Nick";
		$sql = "
			select {$selectString}
			from {$fromString}
			{$filter}
			order by {$orderByString}
		";
		/**@var CI_DB_result $result */
		$result = $db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CI_DB_driver $db
	 * @param $data
	 * @return array|bool
	 */
	public static function GetLpuFilialNodeList(CI_DB_driver $db, $data)
	{
		$sql = "
			select
				LF.LpuFilial_id as \"LpuFilial_id\",
				LpuFilial_Name as \"LpuFilial_Name\"
			from
				v_LpuBuilding LB 
			    join v_LpuFilial LF  on LB.LpuFilial_id = LF.LpuFilial_id
			where LF.Lpu_id = :Lpu_id
			  and LB.LpuFilial_id is not null
			group by LF.LpuFilial_id, LpuFilial_Name
		";
		/**@var CI_DB_result $result */
		$result = $db->query($sql, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CI_DB_driver $db
	 * @param $data
	 * @return array|bool
	 */
	public static function GetLpuBuildingNodeList(CI_DB_driver $db, $data)
	{
		$filter = "";
		$buildpass = "";
		$join = "";
		$case = "";
		if (!empty($data["SectionsOnly"]) && $data["SectionsOnly"] == true) {
			$add_filter = "";
			if (isset($data["deniedSectionsList"]) && is_array($data["deniedSectionsList"]) && !empty($data["deniedSectionsList"][0])) {
				$deniedSectionsListString = implode(",", $data["deniedSectionsList"]);
				$add_filter = "and LS.LpuSection_id not in ({$deniedSectionsListString})";
			}
			if (!empty($data["LpuBuildingPass_id"])) {
				$buildpass = "or LS.LpuBuildingPass_id = {$data["LpuBuildingPass_id"]}";
			}
			$join = "
				LEFT JOIN LATERAL (
					select count(LS.LpuSection_id) as LpuSectionsCount
					from
						LpuSection LS 
						inner join LpuUnit LU on LS.LpuUnit_id = LU.LpuUnit_id
					where LU.LpuBuilding_id = LB.LpuBuilding_id
					  and LS.LpuSection_pid is null
					  and (LS.LpuBuildingPass_id is null {$buildpass})
					  and coalesce(LS.LpuSection_deleted, 1) <> 2
					{$add_filter}
				) as av on true
			";
			$case = " case when av.LpuSectionsCount > 0 then 2 else 1 end as \"claimed\",";
		}
		// Ищутся здания прикрепленные к филиалу или ЛПУ, в зависмости от того, для какого объекта
		$lpuOrFilialFilter = ($data["object"] === "LpuFilial") ? "lf.LpuFilial_id = :object_id " : "lb.Lpu_id = :object_id AND lf.LpuFilial_id is null ";
		$selectString = "
			lb.Lpu_id as \"Lpu_id\",
			lb.LpuBuilding_id as \"LpuBuilding_id\",
			lb.LpuBuilding_Name as \"LpuBuilding_Name\",
			lb.LpuBuildingType_id as \"LpuBuildingType_id\",
			eq.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\",
			rm.RegisterMO_OID as \"RegisterMO_OID\",
			case when lb.LpuBuilding_endDate is not null and lb.LpuBuilding_endDate < tzgetdate() then 'lpu-building-closed16' else 'lpu-building16' end as \"iconCls\",
			{$case}
			(
				(select count(LpuUnit_id) from v_LpuUnit where LpuBuilding_id = LB.LpuBuilding_id) +
				(select count(MedService_id) from v_MedService where LpuBuilding_id = LB.LpuBuilding_id) +
				(select count(StorageStructLevel_id) from v_StorageStructLevel where LpuBuilding_id = LB.LpuBuilding_id)
			) as \"leafcount\"
		";
		$fromString = "
			v_LpuBuilding LB 
			left join v_LpuFilial lf  on lf.LpuFilial_id = LB.LpuFilial_id
			left join nsi.v_RegisterMO rm  on rm.RegisterMO_id = lf.RegisterMO_id
			{$join}
			LEFT JOIN LATERAL (
				select eq.ElectronicQueueInfo_id
				from v_ElectronicQueueInfo eq 
				where eq.LpuBuilding_id = LB.LpuBuilding_id
				and eq.LpuSection_id is null
				limit 1
			) as eq on true
		";
		$whereString = "
			{$lpuOrFilialFilter}
			{$filter} 
		";
		$orderByString = "LpuBuilding_Code";
		$sql = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
			order by {$orderByString}
		";
		/**@var CI_DB_result $result */
		$result = $db->query($sql, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CI_DB_driver $db
	 * @param $data
	 * @return array|bool
	 */
	public static function GetLpuUnitNodeList(CI_DB_driver $db, $data)
	{
		$filter = "";
		$buildpass = "";
		$join = "";
		$case = "";
		if (!empty($data["SectionsOnly"]) && $data["SectionsOnly"] == true) {
			$add_filter = "";
			if (isset($data["deniedSectionsList"]) && is_array($data["deniedSectionsList"]) && !empty($data["deniedSectionsList"][0])) {
				$deniedSectionsListString = implode(",", $data["deniedSectionsList"]);
				$add_filter = "and LS.LpuSection_id not in ({$deniedSectionsListString})";
			}
			if (!empty($data["LpuBuildingPass_id"])) {
				$buildpass = "or LS.LpuBuildingPass_id = {$data['LpuBuildingPass_id']}";
			}
			$join = "
				LEFT JOIN LATERAL (
					select count(LS.LpuSection_id) as LpuSectionsCount
					from
						LpuSection LS 
						inner join LpuUnit LU  on LS.LpuUnit_id = LU.LpuUnit_id
					where LU.LpuUnit_id = LpuUnit.LpuUnit_id
					  and LS.LpuSection_pid is null
					  and (LS.LpuBuildingPass_id is null {$buildpass})
					  and coalesce(LS.LpuSection_deleted, 1) <> 2
					  {$add_filter}
				) as av on true
			";
			$case = " case when av.LpuSectionsCount > 0 then 2 else 1 end as \"claimed\",";
		}

		$sql = "
			select
				LpuBuilding_id as \"LpuBuilding_id\",
				LpuUnit_id as \"LpuUnit_id\",
				LpuUnit_Name as \"LpuUnit_Name\",
				LpuUnit.UnitDepartType_fid as \"UnitDepartType_fid\",
				fu.FRMOUnit_OID as \"FRMOUnit_OID\",
				case when LpuUnit.LpuUnit_endDate is not null and LpuUnit.LpuUnit_endDate < tzgetdate() then 'lpu-unit-closed16' else 'lpu-unit16' end as \"iconCls\",
				{$case}
				(
					(select count(*) from v_LpuSection LpuSection  where LpuUnit.LpuUnit_id = LpuSection.LpuUnit_id) +
					(select count(StorageStructLevel_id) from v_StorageStructLevel  where LpuUnit_id = LpuUnit.LpuUnit_id) +
					(select count(MedService_id) from v_MedService  where LpuUnit_id = LpuUnit.LpuUnit_id)
				) as \"leafcount\"
			from
				v_LpuUnit LpuUnit 
				left join nsi.v_FRMOUnit fu  on fu.FRMOUnit_id = LpuUnit.FRMOUnit_id
				{$join}
			where LpuBuilding_id = {$data['object_id']}
			  and LpuUnitType_id = {$data['LpuUnitType_id']}
			  {$filter}
			order by LpuUnit_Code
		";
		/**@var CI_DB_result $result */
		$result = $db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CI_DB_driver $db
	 * @param $data
	 * @return array|bool
	 */
	public static function GetLpuUnitTypeNodeList(CI_DB_driver $db, $data)
	{
		$params = [
			"LpuBuilding_id" => $data["object_id"],
		];
		$join = "";
		$case = "";
		if (!empty($data["SectionsOnly"]) && $data["SectionsOnly"] == true) {
			$add_filter = "";
			$buildpass = "";
			if (isset($data["deniedSectionsList"]) && is_array($data["deniedSectionsList"]) && !empty($data["deniedSectionsList"][0])) {
				$deniedSectionsListString = implode(",", $data["deniedSectionsList"]);
				$add_filter = "and LS.LpuSection_id not in ({$deniedSectionsListString})";
			}
			if (!empty($data["LpuBuildingPass_id"])) {
				$buildpass = "or LS.LpuBuildingPass_id = {$data['LpuBuildingPass_id']}";
			}
			$join = "
				LEFT JOIN LATERAL (
					select count(LS.LpuSection_id) as LpuSectionsCount
					from
						LpuSection LS 
						inner join LpuUnit  on LS.LpuUnit_id = LpuUnit.LpuUnit_id
							and LpuUnit.LpuBuilding_id = :LpuBuilding_id
							and LpuUnit.LpuUnitType_id = LUT.LpuUnitType_id
					where LS.LpuSection_pid is null
					  and (LS.LpuBuildingPass_id is null {$buildpass})
					  and COALESCE(LS.LpuSection_deleted, 1) <> 2
					  {$add_filter}
				) as av on true
			";
			$case = " case when av.LpuSectionsCount > 0 then 2 else 1 end as \"claimed\",";
		}

		$sql = "
			select
				LU.LpuBuilding_id as \"LpuBuilding_id\",
				LUT.LpuUnitType_id as \"LpuUnitType_id\",
				rtrim(LUT.LpuUnitType_Name) as \"LpuUnitType_Name\",
				case when LU.LpuUnit_endDate is not null and LU.LpuUnit_endDate < tzgetdate() then 'lpu-unittype-closed16' else 'lpu-unittype16' end as \"iconCls\",
				{$case}
				RTrim(LUT.LpuUnitType_Nick) as \"LpuUnitType_Nick\"
			from
			    v_LpuUnitType LUT 
			    INNER JOIN LATERAL (
					select 
						v_LpuUnit.LpuBuilding_id,
						v_LpuUnit.LpuUnit_endDate
					from v_LpuUnit 
					where v_LpuUnit.LpuBuilding_id = :LpuBuilding_id
					  and v_LpuUnit.LpuUnitType_id = LUT.LpuUnitType_id
					order by v_LpuUnit.LpuUnit_endDate
					limit 1
				) as LU on true
			{$join}
		";
		/**@var CI_DB_result $result */
		$result = $db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}

	/**
	 * @param CI_DB_driver $db
	 * @param $data
	 * @return array|bool
	 */
	public static function GetLpuRegionTypeNodeList(CI_DB_driver $db, $data)
	{
		$Lpu_id = (isset($data["Lpu_id"]) && (!empty($data["Lpu_id"]))) ? $data["Lpu_id"] : $data["session"]["lpu_id"];
		$filter = ($Lpu_id > 0) ? "where LpuRegion.Lpu_id=" . $Lpu_id : "";
		// Типы участков только имеющиеся
		$sql = "
			select
				LpuRegionType.LpuRegionType_id as \"LpuRegionType_id\",
				rtrim(LpuRegionType.LpuRegionType_Name) as \"LpuRegionType_Name\"
			from
				v_LpuRegion LpuRegion 
				left join v_LpuRegionType LpuRegionType  on LpuRegionType.LpuRegionType_id = LpuRegion.LpuRegionType_id
			{$filter}
			group by LpuRegionType.LpuRegionType_id, LpuRegionType.LpuRegionType_Name
			order by LpuRegionType.LpuRegionType_id
		";
		/**@var CI_DB_result $result */
		$result = $db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	public static function GetLpuSectionNodeList(CI_DB_driver $db, $data)
	{
		$filter = "";
		$join = "";
		$case = "";
		$select = "
			(
				(select count(LS.LpuSection_id) from v_LpuSection LS where LS.LpuSection_pid = LpuSection.LpuSection_id) +
				(select count(MS.MedService_id) from v_MedService MS where MS.LpuSection_id = LpuSection.LpuSection_id) +
				(select count(StorageStructLevel_id) from v_StorageStructLevel where LpuSection_id = LpuSection.LpuSection_id)
			)as \"leafcount\",
			(
				select LU.UnitDepartType_fid
				from v_LpuUnit LU 
				where LpuUnit_id = {$data['object_id']}
			) as \"UnitDepartType_fid\"
		";
		if (!empty($data["SectionsOnly"]) && $data["SectionsOnly"] == true) {
			$add_filter = "";
			$select = "0 as \"leafcount\"";
			if (isset($data["deniedSectionsList"]) && is_array($data["deniedSectionsList"]) && !empty($data["deniedSectionsList"][0])) {
				$deniedSectionsListString = implode(",", $data["deniedSectionsList"]);
				$add_filter = "and LS.LpuSection_id not in ({$deniedSectionsListString})";
			}
			$join = "
				LEFT JOIN LATERAL (
					select count(LS.LpuSection_id) as LpuSectionsCount
					from LpuSection LS 
					where LpuSection.LpuSection_id = LS.LpuSection_id
					  and LS.LpuBuildingPass_id is null
					  and coalesce(LS.LpuSection_deleted, 1) <> 2
					  {$add_filter}
				) as av on true
			";
			$case = " case when av.LpuSectionsCount > 0 then 2 else 1 end as \"claimed\",";
		}
		if (!empty($data["Lpu_id"])) {
			$filter .= " AND lpu_id = {$data['Lpu_id']} ";
		}
		$selectString = "
			LpuUnit_id as \"LpuUnit_id\",
			LpuSectionProfile_id as \"LpuSectionProfile_id\",
			LpuSection_id as \"LpuSection_id\",
			eq.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\",
			case when LpuSection.LpuSection_disDate is not null and LpuSection.LpuSection_disDate < tzgetdate() then 'lpu-section-closed16' else 'lpu-section16' end as \"iconCls\",
			(rtrim(LpuSection_Code)||'. '||rtrim(LpuSection_Name)) as \"LpuSection_Name\",
			{$case}
			{$select}
		";
		$sql = "
			select {$selectString}
			from
				v_LpuSection LpuSection 
				{$join}
				LEFT JOIN LATERAL (
					select eq.ElectronicQueueInfo_id
					from v_ElectronicQueueInfo eq 
					where eq.LpuSection_id = LpuSection.LpuSection_id
					limit 1
				) as eq on true
			where LpuUnit_id = {$data['object_id']} and LpuSection_pid is null {$filter}
			order by LpuSection_Code
		";
		/**@var CI_DB_result $result */
		$result = $db->query($sql, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CI_DB_driver $db
	 * @param $data
	 * @return array|bool
	 */
	public static function GetLpuSectionPidNodeList(CI_DB_driver $db, $data)
	{
		$filter = "";
		$buildpass = "";
		if (!empty($data["SectionsOnly"]) && $data["SectionsOnly"] == true) {
			if (!empty($data["LpuBuildingPass_id"])) {
				$buildpass = "or LpuBuildingPass_id = {$data['LpuBuildingPass_id']}";
			}
			$filter = "
				and (LpuBuildingPass_id is null {$buildpass})
				and coalesce(LpuSection_deleted, 1) <> 2
			";
			if (isset($data["deniedSectionsList"]) && is_array($data["deniedSectionsList"]) && !empty($data["deniedSectionsList"][0])) {
				$deniedSectionsListString = implode(",", $data["deniedSectionsList"]);
				$filter .= " and LpuSection_id not in ({$deniedSectionsListString}) ";
			}
		}
		$sql = "
			select
				LpuSection_pid as \"LpuSection_pid\",
				LpuSectionProfile_id as \"LpuSectionProfile_id\",
				case when LpuSection.LpuSection_disDate is not null and LpuSection.LpuSection_disDate < tzgetdate() then 'lpu-subsection-closed16' else 'lpu-subsection16' end as \"iconCls\",
				LpuSection_id as \"LpuSection_id\",
				eq.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\",
				(rtrim(LpuSection_Code)||'. '||rtrim(LpuSection_Name)) as \"LpuSection_Name\",
				(select count(StorageStructLevel_id) from v_StorageStructLevel where LpuSection_id = LpuSection.LpuSection_id) as \"leafcount\"
			from
				v_LpuSection LpuSection 
				LEFT JOIN LATERAL (
					select eq.ElectronicQueueInfo_id
					from v_ElectronicQueueInfo eq 
					where eq.LpuSection_id = LpuSection.LpuSection_id
					limit 1
				) as eq on true
			where LpuSection_pid = {$data['object_id']} {$filter}
			order by LpuSection_Code
		";
		/**@var CI_DB_result $result */
		$result = $db->query($sql, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CI_DB_driver $db
	 * @param $data
	 * @return array|bool
	 */
	public static function GetLpuRegionNodeList(CI_DB_driver $db, $data)
	{
		$filter = (!empty($data["Lpu_id"])) ? "LR.Lpu_id = {$data['Lpu_id']}" : "(1=1)";
		$join = "";
		if (isset($data["uchOnly"])) {
			$join = " inner join v_MedStaffRegion on v_MedStaffRegion.LpuRegion_id = LR.LpuRegion_id and v_MedStaffRegion.MedPersonal_id = {$data['session']['medpersonal_id']} ";
		} else {
			$filter .= " and LpuRegionType_id = {$data['object_id']} ";
		}
		$sql = "
			SELECT
				LR.LpuRegion_id as \"LpuRegion_id\",
				LR.LpuRegion_Name as \"LpuRegion_Name\",
				coalesce(LR.LpuRegion_Descr, '') as \"LpuRegion_Descr\",
				LR.LpuRegionType_id as \"LpuRegionType_id\"
			FROM
				v_LpuRegion LR 
				{$join}
			where {$filter}
			order by
				case when (select LR.LpuRegion_Name ~ '^[0-9]+$') = true then cast(LpuRegion_Name as bigint) else 1488 end
		";
		/**@var CI_DB_result $result */
		$result = $db->query($sql, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CI_DB_driver $db
	 * @param $data
	 * @return array|bool
	 */
	public static function GetLpuAllQuery(CI_DB_driver $db, $data)
	{
		$sql = "
			select
				Lpu_id as \"Lpu_id\",
				Okopf_Name as \"Okopf_Name\",
				Org_Name as \"Org_Name\",
				Lpu_Nick as \"Lpu_Nick\",
				Org_Code as \"Org_Code\",
				LpuType_Name as \"LpuType_Name\",
				UAddress_Address as \"UAddress_Address\",
				PAddress_Address as \"PAddress_Address\",
				Lpu_IsLab as \"Lpu_IsLab\",
				LpuType_Code as \"LpuType_Code\"
			from v_Lpu_all 
			where Lpu_id=:Lpu_id
		";
		$sqlParams = [
			"Lpu_id" => $data["Lpu_id"]
		];
		/**@var CI_DB_result $result */
		$result = $db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CI_DB_driver $db
	 * @param $data
	 * @return array|bool
	 */
	public static function getIsNoFRMP(CI_DB_driver $db, $data)
	{
		$params = [
			"Lpu_id" => $data["Lpu_id"]
		];
		$query = "
            select coalesce(PasportMO_IsNoFRMP, '1') as \"PasportMO_IsNoFRMP\"
            from fed.PasportMO 
            where Lpu_id = :Lpu_id
        ";
		/**@var CI_DB_result $result */
		$result = $db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLpuSectionBedStateListBySectionForAPI(LpuStructure_model $callObject, $data)
	{
		$query = "
			select
				LpuSectionBedState_id as \"LpuSectionBedState_id\",
				LpuSectionBedProfile_id as \"LpuSectionBedProfile_id\",
				LpuSectionBedState_ProfileName as \"LpuSectionBedState_ProfileName\",
				LpuSectionBedState_Fact as \"LpuSectionBedState_Fact\",
				LpuSectionBedState_Plan as \"LpuSectionBedState_Plan\",
				LpuSectionBedState_Repair as \"LpuSectionBedState_Repair\",
				LpuSectionBedState_CountOms as \"LpuSectionBedState_CountOms\",
				LpuSectionBedState_MalePlan as \"LpuSectionBedState_MalePlan\",
				LpuSectionBedState_MaleFact as \"LpuSectionBedState_MaleFact\",
				LpuSectionBedState_FemalePlan as \"LpuSectionBedState_FemalePlan\",
				LpuSectionBedState_FemaleFact as \"LpuSectionBedState_FemaleFact\",
				to_char(LpuSectionBedState_begDate::date, '{$callObject->dateTimeForm120}') as \"LpuSectionBedState_begDate\",
				to_char(LpuSectionBedState_endDate::date, '{$callObject->dateTimeForm120}') as \"LpuSectionBedState_endDate\"
			from v_LpuSectionBedState 
			where LpuSection_id = :LpuSection_id
		";
		return $callObject->queryResult($query, $data);
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @param $town_id
	 * @param $street_id
	 * @param $lpuregion_id
	 * @param $lpuregionstreet_id
	 * @return array|bool
	 */
	public static function getStreetHouses(LpuStructure_model $callObject, $data, $town_id, $street_id, $lpuregion_id, $lpuregionstreet_id)
	{
		// Получаем тип участка
		if (!isset($lpuregion_id)) {
			return false;
		}
		$query = "
				select LpuRegionType_id as \"LpuRegionType_id\"
				from v_LpuRegion 
				where LpuRegion_id = ?
			";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, [$lpuregion_id]);
		if (!is_object($result)) {
			return false;
		}
		$res = $result->result("array");
		if (count($res) == 0) {
			return false;
		}
		$lpuregiontype_id = $res[0]["LpuRegionType_id"];
		$queryParams = [
			"Lpu_id" => $data["session"]["lpu_id"],
			"KLStreet_id" => ($street_id == 0) ? null : $street_id,
			"KLTown_id" => ($town_id == 0) ? null : $town_id,
			"LpuRegionType_id" => $lpuregiontype_id,
			"LpuRegionStreet_id" => ($lpuregionstreet_id == 0) ? null : $lpuregionstreet_id,
		];
		$query = "
				select
					LpuRegion.LpuRegion_id as \"LpuRegion_id\",
					LpuRegionStreet_HouseSet as \"LpuRegionStreet_HouseSet\"
				from
					LpuRegionStreet 
					inner join v_LpuRegion LpuRegion on LpuRegion.LpuRegion_id = LpuRegionStreet.LpuRegion_id
				where Lpu_id = :Lpu_id
				  and (KLStreet_id = :KLStreet_id or :KLStreet_id is null)
				  and (KLTown_id = :KLTown_id or :KLTown_id is null)
				  and (LpuRegion.LpuRegionType_id = :LpuRegionType_id)
				  and (LpuRegionStreet.LpuRegionStreet_id <> :LpuRegionStreet_id or :LpuRegionStreet_id is null)
			";
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuUnitList(LpuStructure_model $callObject, $data)
	{
		$additionalFields = [];
		$additionalWith = "";
		$filterList = ["(1 = 1)"];
		$params = [];
		if (!empty($data["LpuBuilding_id"])) {
			$filterList[] = "lu.LpuBuilding_id = :LpuBuilding_id";
			$params["LpuBuilding_id"] = $data["LpuBuilding_id"];
		}
		if (!empty($data["LpuUnit_Code"])) {
			$filterList[] = "lu.LpuUnit_Code = :LpuUnit_Code";
			$params["LpuUnit_Code"] = $data["LpuUnit_Code"];
		}
		if (!empty($data["LpuUnit_Name"])) {
			$filterList[] = "lu.LpuUnit_Name ilike :LpuUnit_Name";
			$params["LpuUnit_Name"] = $data["LpuUnit_Name"];
		}
		if (!empty($data["LpuUnitType_Code"])) {
			$filterList[] = "lut.LpuUnitType_Code = :LpuUnitType_Code";
			$params["LpuUnitType_Code"] = $data["LpuUnitType_Code"];
		}
		if (!empty($data["LpuUnit_id"])) {
			$filterList[] = "lu.LpuUnit_id = :LpuUnit_id";
			$params["LpuUnit_id"] = $data["LpuUnit_id"];
			$additionalFields[] = "
				case when exists (select id from LpuUnitChilds limit 1) then 1 else 0 end as \"ChildsCount\"

			";
			$additionalWith = "
				with LpuUnitChilds as (
					(
						select MedService_id as id
						from v_MedService 
						where LpuUnit_id = :LpuUnit_id limit 1
					)
					union all
					(
						select UslugaComplexPlace_id as id
						from v_UslugaComplexPlace 
						where LpuUnit_id = :LpuUnit_id limit 1
					)
				)
			";
		}
		$sql = "
			{$additionalWith}
			SELECT
				lu.LpuUnit_id as \"LpuUnit_id\",
				lu.Lpu_id as \"Lpu_id\",
				to_char(lu.LpuUnit_begDate::date, '{$callObject->dateTimeForm104}') as \"LpuUnit_begDate\",
				to_char(lu.LpuUnit_endDate::date, '{$callObject->dateTimeForm104}') as \"LpuUnit_endDate\",
				lu.LpuBuilding_id as \"LpuBuilding_id\",
				lu.LpuUnitSet_id as \"LpuUnitSet_id\",
				lu.LpuUnit_Code as \"LpuUnit_Code\",
				lu.LpuUnitType_id as \"LpuUnitType_id\",
				lu.LpuUnitTypeDop_id as \"LpuUnitTypeDop_id\",
				lu.LpuUnitProfile_fid as \"LpuUnitProfile_fid\",
				lu.LpuUnit_isStandalone as \"LpuUnit_isStandalone\",
				lu.LpuUnit_isCMP as \"LpuUnit_isCMP\",
				lu.LpuUnit_isHomeVisit as \"LpuUnit_isHomeVisit\",
				lu.LpuUnit_FRMOUnitID as \"LpuUnit_FRMOUnitID\",
				lu.FRMOUnit_id as \"FRMOUnit_id\",
				lu.UnitDepartType_fid as \"UnitDepartType_fid\",
				lu.LpuBuildingPass_id as \"LpuBuildingPass_id\",
				rtrim(lu.LpuUnit_Name) as \"LpuUnit_Name\",
				rtrim(lu.LpuUnit_Phone) as \"LpuUnit_Phone\",
				rtrim(lu.LpuUnit_Descr) as \"LpuUnit_Descr\",
				rtrim(lu.LpuUnit_Email) as \"LpuUnit_Email\",
				rtrim(lu.LpuUnit_IP) as \"LpuUnit_IP\",
				case when lu.LpuUnit_IsEnabled = 2 then 'on' else 'off' end as \"LpuUnit_IsEnabled\",
				case when lu.LpuUnit_isPallCC = 2 then 'on' else 'off' end as \"LpuUnit_isPallCC\",
				case when lu.LpuUnit_IsOMS = 2 or lu.LpuUnit_IsOMS is null then 'on' else 'off' end as \"LpuUnit_IsOMS\"
				" . (count($additionalFields) > 0 ? "," . implode(",", $additionalFields) : "") . "
			FROM 
				v_LpuUnit lu 
				inner join v_LpuUnitType lut on lut.LpuUnitType_id = lu.LpuUnitType_id
			WHERE " . implode(" and ", $filterList) . "
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuUnitCombo(LpuStructure_model $callObject, $data)
	{
		$filters = [];
		$params = [];
		if ((isset($data["Lpu_id"])) && ($data["Lpu_id"] > 0)) {
			$filters[] = "LB.Lpu_id = :Lpu_id";
			$params["Lpu_id"] = $data["Lpu_id"];
		} else if ((isset($data["session"]["lpu_id"])) && ($data["session"]["lpu_id"] > 0)) {
			$filters[] = "LB.Lpu_id = :Lpu_id";
			$params["Lpu_id"] = $data["session"]["lpu_id"];
		}
		if ((isset($data["LpuBuilding_id"])) && ($data["LpuBuilding_id"] > 0)) {
			$filters[] = "LU.LpuBuilding_id = :LpuBuilding_id";
			$params["LpuBuilding_id"] = $data["LpuBuilding_id"];
		}
		if ((isset($data["LpuUnit_id"])) && ($data["LpuUnit_id"] > 0)) {
			$filters[] = "LU.LpuUnit_id = :LpuUnit_id";
			$params["LpuUnit_id"] = $data["LpuUnit_id"];
		}
		if (count($filters) == 0) {
			return [[]];
		}
		$where = "WHERE " . join(" AND ", $filters);
		$sql = "
			SELECT 
				LU.LpuUnit_id as \"LpuUnit_id\",
				LU.LpuUnit_Code as \"LpuUnit_Code\",
				LU.LpuUnit_Name as \"LpuUnit_Name\",
				coalesce(LU.LpuUnit_IsEnabled, 1) as \"LpuUnit_IsEnabled\",
				LUT.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
				LB.LpuBuilding_id as \"LpuBuilding_id\",
				LB.LpuBuilding_Name as \"LpuBuilding_Name\"
			FROM
				v_LpuUnit LU 
				left join v_LpuBuilding LB on LB.LpuBuilding_id = LU.LpuBuilding_id
				left join v_LpuUnitType LUT on LUT.LpuUnitType_id = LU.LpuUnitType_id
			{$where}
			limit 500
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	public static function getLpuUnitSetCombo(LpuStructure_model $callObject, $data)
	{
		$filterList = [];
		$queryParams = [];
		$filterList[] = "(Lpu_id = {$data['session']['lpu_id']} or Lpu_id is null)";
		if (!empty($data["LpuUnitSet_IsCmp"])) {
			$filterList[] = "LpuUnitSet_IsCmp = :LpuUnitSet_IsCmp";
			$queryParams["LpuUnitSet_IsCmp"] = $data["LpuUnitSet_IsCmp"];
		}
		$filterListString = implode(" and ", $filterList);
		$sql = "
			SELECT
				LpuUnitSet_id as \"LpuUnitSet_id\",
			    LpuUnitSet_Code as \"LpuUnitSet_Code\",
			    to_char(LpuUnitSet_begDate::date, '{$callObject->dateTimeForm104}') as \"LpuUnitSet_begDate\",
			    to_char(LpuUnitSet_endDate::date, '{$callObject->dateTimeForm104}') as \"LpuUnitSet_endDate\",
			    LpuUnitSet_IsCmp as \"LpuUnitSet_IsCmp\"
			FROM v_LpuUnitSet lsc 
			WHERE {$filterListString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		// добавляем даты начала и конца текущего месяца
		foreach ($result as $key => $value) {
			$result[$key]["curBegDateMonth"] = date("01.m.Y");
			$result[$key]["curEndDateMonth"] = date("t.m.Y");
		}
		return $result;
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuBuildingList(LpuStructure_model $callObject, $data)
	{
		$filterArray = [];
		$params = [];
		if ((isset($data["Lpu_id"])) && ($data["Lpu_id"] > 0)) {
			$filterArray[] = "LpuBuilding.Lpu_id = :Lpu_id";
			$params["Lpu_id"] = $data["Lpu_id"];
		}
		if ((isset($data["LpuFilial_id"])) && ($data["LpuFilial_id"] > 0)) {
			$filterArray[] = "LpuBuilding.LpuFilial_id = :LpuFilial_id";
			$params["LpuFilial_id"] = $data["LpuFilial_id"];
		}
		if ((isset($data["LpuBuilding_id"])) && ($data["LpuBuilding_id"] > 0)) {
			$filterArray[] = "LpuBuilding.LpuBuilding_id = :LpuBuilding_id";
			$params["LpuBuilding_id"] = $data["LpuBuilding_id"];
		}
		if (!empty($data["isClose"]) && $data["isClose"] == 1) {
			$filterArray[] = "(LpuBuilding_endDate is null or LpuBuilding_endDate > tzgetdate())";
		} elseif (!empty($data["isClose"]) && $data["isClose"] == 2) {
			$filterArray[] = "LpuBuilding_endDate <= tzgetdate()";
		}
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$sql = "
			SELECT
				LpuBuilding.Lpu_id as \"Lpu_id\",
				LpuBuilding.LpuBuilding_id as \"LpuBuilding_id\",
				rtrim(LpuBuilding_Nick) as \"LpuBuilding_Nick\",
				rtrim(LpuBuilding_Name) as \"LpuBuilding_Name\",
				LpuBuilding_Code as \"LpuBuilding_Code\",
				to_char(LpuBuilding_begDate::date, '{$callObject->dateTimeForm104}') as \"LpuBuilding_begDate\",
				to_char(LpuBuilding_endDate::date, '{$callObject->dateTimeForm104}') as \"LpuBuilding_endDate\",
				LpuBuilding.LpuBuildingType_id as \"LpuBuildingType_id\",
				rtrim(LpuBuildingType_Name) as \"LpuBuildingType_Name\",
				rtrim(LpuBuilding_WorkTime) as \"LpuBuilding_WorkTime\",
				rtrim(LpuBuilding_RoutePlan) as \"LpuBuilding_RoutePlan\",
				LpuBuilding.LpuBuilding_IsExport as \"LpuBuilding_IsExport\",
				LpuBuilding.LpuBuilding_CmpStationCode as \"LpuBuilding_CmpStationCode\",
				LpuBuilding.LpuBuilding_CmpSubstationCode as \"LpuBuilding_CmpSubstationCode\",
				LpuBuilding.Address_id as \"Address_id\",
			    LpuBuilding.LpuBuilding_Longitude as \"LpuBuilding_Longitude\",
				LpuBuilding.LpuBuilding_Latitude as \"LpuBuilding_Latitude\",
				Address.KLAreaType_id as \"KLAreaType_id\",
				Address.Address_Zip as \"Address_Zip\",
				Address.KLCountry_id as \"KLCountry_id\",
				Address.KLRGN_id as \"KLRGN_id\",
				Address.KLSubRGN_id as \"KLSubRGN_id\",
				Address.KLCity_id as \"KLCity_id\",
				Address.KLTown_id as \"KLTown_id\",
				Address.KLStreet_id as \"KLStreet_id\",
				Address.Address_House as \"Address_House\",
				Address.Address_Corpus as \"Address_Corpus\",
				Address.Address_Flat as \"Address_Flat\",
				Address.Address_Address as \"Address_Address\",
				Address.Address_Address as \"Address_AddressText\",
				LpuBuilding.PAddress_id as \"PAddress_id\",
				PAddress.Address_Zip as \"PAddress_Zip\",
				PAddress.KLCountry_id as \"PKLCountry_id\",
				PAddress.KLRGN_id as \"PKLRGN_id\",
				PAddress.KLSubRGN_id as \"PKLSubRGN_id\",
				PAddress.KLCity_id as \"PKLCity_id\",
				PAddress.KLTown_id as \"PKLTown_id\",
				PAddress.KLStreet_id as \"PKLStreet_id\",
				PAddress.Address_House as \"PAddress_House\",
				PAddress.Address_Corpus as \"PAddress_Corpus\",
				PAddress.Address_Flat as \"PAddress_Flat\",
				PAddress.Address_Address as \"PAddress_Address\",
				PAddress.Address_Address as \"PAddress_AddressText\",
				LpuBuilding.LpuFilial_id as \"LpuFilial_id\",
				LFilial.LpuFilial_Name as \"LpuFilial_Name\",
				LFilial.LpuFilial_Code as \"LpuFilial_Code\",
				coalesce(LpuBuilding.LpuBuilding_IsAIDSCenter, 1) as \"LpuBuilding_IsAIDSCenter\",
				Lhealth.LpuBuildingHealth_Phone as \"LpuBuildingHealth_Phone\",
				Lhealth.LpuBuildingHealth_Email as \"LpuBuildingHealth_Email\"
			FROM
				v_LpuBuilding LpuBuilding 
				left join LpuBuildingType on LpuBuildingType.LpuBuildingType_id = LpuBuilding.LpuBuildingType_id
				left join v_Address Address on LpuBuilding.Address_id = Address.Address_id
				left join v_Address PAddress on LpuBuilding.PAddress_id = PAddress.Address_id
				left join v_LpuFilial LFilial on LpuBuilding.LpuFilial_id = LFilial.LpuFilial_id
				left join v_Lpu Lpu on Lpu.Lpu_id = LpuBuilding.Lpu_id
				left join v_LpuBuildingHealth Lhealth  on Lhealth.LpuBuilding_id = LpuBuilding.LpuBuilding_id
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuRegionList(LpuStructure_model $callObject, $data)
	{
		$filterArray = [];
		if ((isset($data["Lpu_id"])) && ($data["Lpu_id"] > 0)) {
			$filterArray[] = "Lpu_id = {$data['Lpu_id']}";
		}
		if ((isset($data["LpuRegion_id"])) && ($data["LpuRegion_id"] > 0)) {
			$filterArray[] = "LpuRegion_id = {$data['LpuRegion_id']}";
		}
		if (!empty($data["isClose"]) && $data["isClose"] == 1) {
			$filterArray[] = "(LpuRegion_endDate is null or LpuRegion_endDate > tzgetdate())";
		} elseif (!empty($data["isClose"]) && $data["isClose"] == 2) {
			$filterArray[] = "LpuRegion_endDate <= tzgetdate()";
		}
		if (!empty($data["LpuRegionType_id"])) {
			$filterArray[] = "LpuRegionType_id = {$data['LpuRegionType_id']}";
		}
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$sql = "
			select
				LpuRegion_id as \"LpuRegion_id\",
				LpuRegion_Name as \"LpuRegion_Name\",
				LpuRegion_Descr as \"LpuRegion_Descr\",
				LpuRegionType_id as \"LpuRegionType_id\",
				to_char(LpuRegion_begDate::date, '{$callObject->dateTimeForm104}') as \"LpuRegion_begDate\",
				to_char(LpuRegion_endDate::date, '{$callObject->dateTimeForm104}') as \"LpuRegion_endDate\",
				Lpu_id as \"Lpu_id\"
			from v_LpuRegion 
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getMedStaffRegion(LpuStructure_model $callObject, $data)
	{
		$filterArray = [];
		$queryParams = [];
		if ((isset($data["Lpu_id"])) && ($data["Lpu_id"] > 0)) {
			$filterArray[] = "msr.Lpu_id = :Lpu_id";
			$queryParams["Lpu_id"] = $data["Lpu_id"];
		} elseif (isset($data["session"]["lpu_id"])) {
			$filterArray[] = "msr.Lpu_id = :Lpu_id";
			$queryParams["Lpu_id"] = $data["session"]["lpu_id"];
		}
		if ((isset($data["MedStaffRegion_id"])) && ($data["MedStaffRegion_id"] > 0)) {
			$filterArray[] = "msr.MedStaffRegion_id = :MedStaffRegion_id";
			$queryParams["MedStaffRegion_id"] = $data["MedStaffRegion_id"];
		}
		if (!empty($data["LpuRegion_id"])) {
			$filterArray[] = "msr.LpuRegion_id = :LpuRegion_id";
			$queryParams["LpuRegion_id"] = $data["LpuRegion_id"];
		}
		if (!empty($data["showClosed"]) && $data["showClosed"] == 1) {
			$filterArray[] = "(msr.MedStaffRegion_endDate is null or cast(msr.MedStaffRegion_endDate as date) >= tzgetdate())";
		}
		$queryParams["LpuRegion_id"] = !empty($data["LpuRegion_id"]) ? $data["LpuRegion_id"] : null;

		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$sql = "
			SELECT
				msr.MedStaffRegion_id as \"MedStaffRegion_id\",
				msr.MedStaffFact_id as \"MedStaffFact_id\",
				coalesce(msf.MedPersonal_id, msr.MedPersonal_id) as \"MedPersonal_id\",
				msr.MedStaffRegion_isMain as \"MedStaffRegion_isMain\",
				case when msr.MedStaffRegion_isMain = 2 then '(Основной врач)' else '' end as \"msr_descr\",
				coalesce(msf.Person_Fio, mp.Person_Fio) as \"MedPersonal_FIO\",
				msr.LpuRegion_id as \"LpuRegion_id\",
				to_char(msr.MedStaffRegion_begDate::date, '{$callObject->dateTimeForm104}') as \"MedStaffRegion_begDate\",
				to_char(msr.MedStaffRegion_endDate::date, '{$callObject->dateTimeForm104}') as \"MedStaffRegion_endDate\",
				lr.LpuRegionType_id as \"LpuRegionType_id\",
				lr.LpuRegion_Name as \"LpuRegion_Name\",
				p.name as \"PostMed_Name\",
				msr.Lpu_id as \"Lpu_id\",
				1 as \"status\"
			FROM
			    v_MedStaffRegion msr 
				left join v_MedStaffFact msf on msf.MedStaffFact_id = msr.MedStaffFact_id and msr.Lpu_id = msf.Lpu_id
				LEFT JOIN LATERAL (
					select Person_Fio
					from v_MedPersonal 
					where MedPersonal_id = msr.MedPersonal_id
					limit 1
				) as mp on true
				inner join v_LpuRegion lr on lr.LpuRegion_id = msr.LpuRegion_id
				LEFT JOIN persis.Post p on p.id = msf.Post_id
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getUslugaSectionTariff(LpuStructure_model $callObject, $data)
	{
		$filterArray = [];
		if ((isset($data["Server_id"])) && ($data["Server_id"] > 0)) {
			$filterArray[] = "Server_id = {$data['Server_id']}";
		} elseif ($data["session"]["server_id"] > 0) {
			$filterArray[] = "Server_id = {$data['session']['server_id']}";
		}
		if (isset($data["UslugaSectionTariff_id"]) && is_numeric($data["UslugaSectionTariff_id"])) {
			$filterArray[] = "UslugaSectionTariff_id = {$data['UslugaSectionTariff_id']}";
		}
		if (isset($data["UslugaSection_id"]) && is_numeric($data["UslugaSection_id"])) {
			$filterArray[] = "UslugaSection_id = {$data['UslugaSection_id']}";
		}
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$sql = "
			Select
				Server_id as \"Server_id\",
				UslugaSectionTariff_id as \"UslugaSectionTariff_id\",
				UslugaSection_id as \"UslugaSection_id\",
				UslugaSectionTariff_Tariff as \"UslugaSectionTariff_Tariff\",
				to_char(UslugaSectionTariff_begDate::date, '{$callObject->dateTimeForm104}') as \"UslugaSectionTariff_begDate\",
				to_char(UslugaSectionTariff_endDate::date, '{$callObject->dateTimeForm104}') as \"UslugaSectionTariff_endDate\"
			from UslugaSectionTariff 
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function GetUslugaComplexTariff(LpuStructure_model $callObject, $data)
	{
		$filterArray = [];
		if ((isset($data["Server_id"])) && ($data["Server_id"] > 0)) {
			$filterArray[] = "Server_id = {$data['Server_id']}";
		} elseif ($data["session"]["server_id"] > 0) {
			$filterArray[] = "Server_id = {$data['session']['server_id']}";
		}
		if ((isset($data["UslugaComplexTariff_id"])) && (is_numeric($data["UslugaComplexTariff_id"]))) {
			$filterArray[] = "UslugaComplexTariff_id = {$data['UslugaComplexTariff_id']}";
		}
		if ((isset($data["UslugaComplex_id"])) && (is_numeric($data["UslugaComplex_id"]))) {
			$filterArray[] = "UslugaComplex_id = {$data['UslugaComplex_id']}";
		}
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$sql = "
			select
				Server_id as \"Server_id\",
				UslugaComplexTariff_id as \"UslugaComplexTariff_id\",
				UslugaComplex_id as \"UslugaComplex_id\",
				UslugaComplexTariff_Tariff as \"UslugaComplexTariff_Tariff\",
				to_char(UslugaComplexTariff_begDate::date, '{$callObject->dateTimeForm104}') as \"UslugaComplexTariff_begDate\",
				to_char(UslugaComplexTariff_endDate::date, '{$callObject->dateTimeForm104}') as \"UslugaComplexTariff_endDate\"
			from UslugaComplexTariff 
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuRegionStreet(LpuStructure_model $callObject, $data)
	{
		$filterArray = [];
		if ((isset($data["Server_id"])) && ($data["Server_id"] > 0)) {
			$filterArray[] = "LpuRegionStreet.Server_id = {$data['Server_id']}";
		}
		if ((isset($data["LpuRegionStreet_id"])) && (is_numeric($data["LpuRegionStreet_id"]))) {
			$filterArray[] = "LpuRegionStreet_id = {$data['LpuRegionStreet_id']}";
		}
		if ((isset($data["LpuRegion_id"])) && (is_numeric($data["LpuRegion_id"]))) {
			$filterArray[] = "LpuRegion_id = {$data['LpuRegion_id']}";
		}
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$sql = "
			select
				LpuRegionStreet.Server_id as \"Server_id\",
				LpuRegionStreet_id as \"LpuRegionStreet_id\",
				LpuRegion_id as \"LpuRegion_id\",
				LpuRegionStreet.KLCountry_id as \"KLCountry_id\",
				KLRGN_id as \"KLRGN_id\",
				KLSubRGN_id as \"KLSubRGN_id\",
				KLCity_id as \"KLCity_id\",
				LpuRegionStreet.KLTown_id as \"KLTown_id\",
				LpuRegionStreet_IsAll as \"LpuRegionStreet_IsAll\",
				case coalesce(LpuRegionStreet.KLTown_id, 0)
                    when 0 then RTrim(c.KLArea_Name)||' '||coalesce(cs.KLSocr_Nick, '')
                    else rtrim(t.KLArea_Name)||' '||coalesce(ts.KLSocr_Nick, '')
				end as \"KLTown_Name\",
				LpuRegionStreet.KLStreet_id as \"KLStreet_id\",
				rtrim(KLStreet_FullName) as \"KLStreet_Name\",
				LpuRegionStreet_HouseSet as \"LpuRegionStreet_HouseSet\"
			from
				LpuRegionStreet 
				left join KLArea t on t.KLArea_id = LpuRegionStreet.KLTown_id
				left join KLSocr ts on ts.KLSocr_id = t.KLSocr_id
				left join v_KLStreet KLStreet  on KLStreet.KLStreet_id = LpuRegionStreet.KLStreet_id
				left join KLArea c on c.Klarea_id = LpuRegionStreet.KLCity_id
				left join KLSocr cs on cs.KLSocr_id = c.KLSocr_id
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getMedServiceStreet(LpuStructure_model $callObject, $data)
	{
		$filterArray = [];
		if (isset($data["MedServiceStreet_id"]) && is_numeric($data["MedServiceStreet_id"])) {
			$filterArray[] = "MedServiceKLHouseCoordsRel_id = {$data['MedServiceStreet_id']}";
		}
		if (isset($data["MedService_id"]) && is_numeric($data["MedService_id"])) {
			$filterArray[] = "MedService_id = {$data['MedService_id']}";
		}
		$groupByArray = [
			"HC.KLHouse_id",
			"H.KLHouse_Name",
			"MedService_id",
			"t.KLCountry_id",
			"t.KLAreaLevel_id",
			"t.KLArea_id",
			"t.KLArea_Name",
			"cstatpid.KLArea_Name",
			"rstatpid.KLArea_Name",
			"cstatpid.KLArea_id",
			"cstatpid.KLAreaLevel_id",
			"rstatpid.KLArea_id",
			"rstatpid.KLAreaLevel_id",
			"rtatpid.KLArea_id",
			"rtatpid.KLAreaLevel_id",
			"t.KLArea_Name",
			"H.KLStreet_id",
			"KLStreet_FullName",
			"HCR.MedServiceStreet_isAll"
		];
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$groupByString = implode(",", $groupByArray);
		$sql = "
			select
				'1' as \"Server_id\",
				MAX(HCR.MedServiceKLHouseCoordsRel_id) as \"MedServiceStreet_id\",
				MedService_id as \"MedService_id\",
				t.KLCountry_id as \"KLCountry_id\",
				case when t.KLAreaLevel_id = 1 then t.KLArea_id else
					case when cstatpid.KLAreaLevel_id = 1 then cstatpid.KLArea_id else
						case when rstatpid.KLAreaLevel_id = 1 then rstatpid.KLArea_id else
						    case when rtatpid.KLAreaLevel_id = 1 then rtatpid.KLArea_id else null end end end end as \"KLRGN_id\",
				case when t.KLAreaLevel_id = 2 then t.KLArea_id else
				    case when cstatpid.KLAreaLevel_id = 2 then cstatpid.KLArea_id else
				        case when rstatpid.KLAreaLevel_id = 2 then rstatpid.KLArea_id else null end end end as \"KLSubRGN_id\",
				case when t.KLAreaLevel_id = 2 then t.KLArea_Name else
				    case when cstatpid.KLAreaLevel_id = 2 then cstatpid.KLArea_Name else
				        case when rstatpid.KLAreaLevel_id = 2 then rstatpid.KLArea_Name else null end end end as \"KLSubRGN_Name\",
				case when t.KLAreaLevel_id = 3 then t.KLArea_id else
				    case when cstatpid.KLAreaLevel_id = 3 then cstatpid.KLArea_id else null end end as \"KLCity_id\",
			    case when t.KLAreaLevel_id = 4 then t.KLArea_id else null end as \"KLTown_id\",
				t.KLArea_Name as \"KLTown_Name\",
				H.KLStreet_id as \"KLStreet_id\",
				RTrim(KLStreet_FullName) as \"KLStreet_Name\",
				H.KLHouse_Name as \"MedServiceStreet_HouseSet\",
				case when coalesce(HCR.MedServiceStreet_isAll, 1) = 1 then 'false' else 'true' end as \"MedServiceStreet_isAll\"
			from
				MedServiceKLHouseCoordsRel HCR 
				left join KLHouseCoords HC  on HC.KLHouseCoords_id = HCR.KLHouseCoords_id
				left join KLArea t  on t.KLArea_id = HC.KLArea_id
				left join KLAreaStat rstat  on rstat.KLSubRGN_id = HC.KLArea_id
				left join KLAreaStat cstat  on cstat.KLCity_id = HC.KLArea_id
				left join KLAreaStat tstat  on tstat.KLTown_id = HC.KLArea_id
				left join KLArea cstatpid  on cstatpid.KLArea_id = t.KLArea_pid
				left join KLArea rstatpid  on rstatpid.KLArea_id = cstatpid.KLArea_pid
				left join KLArea rtatpid  on rtatpid.KLArea_id = rstatpid.KLArea_pid
				left join KLHouse H  on H.KLHouse_id = HC.KLHouse_id
				left join v_KLStreet KLStreet  on KLStreet.KLStreet_id = H.KLStreet_id
				left join KLSocr ts  on ts.KLSocr_id = H.KLSocr_id
			{$whereString}
			group by {$groupByString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuBuildingStreet(LpuStructure_model $callObject, $data)
	{
		$filterArray = ["HC.KLHouse_id is not null"];
		if (isset($data["LpuBuildingStreet_id"]) && is_numeric($data["LpuBuildingStreet_id"])) {
			$filterArray[] = "LpuBuildingKLHouseCoordsRel_id = {$data['LpuBuildingStreet_id']}";
		}
		if (isset($data["LpuBuilding_id"]) && is_numeric($data["LpuBuilding_id"])) {
			$filterArray[] = "LpuBuilding_id = {$data['LpuBuilding_id']}";
		}
		$groupByArray = [
			"HC.KLHouse_id",
			"H.KLHouse_Name",
			"LpuBuilding_id",
			"t.KLCountry_id",
			"t.KLAreaLevel_id",
			"t.KLArea_id",
			"rstat.KLRGN_id",
			"cstat.KLRGN_id",
			"tstat.KLRGN_id",
			"t.KLArea_Name",
			"H.KLStreet_id",
			"KLStreet_FullName"
		];
		$whereString = implode(" and ", $filterArray);
		$groupByString = implode(",", $groupByArray);
		$sql = "
			select
				'1' as \"Server_id\",
				MAX(HCR.LpuBuildingKLHouseCoordsRel_id) as \"LpuBuildingStreet_id\",
				LpuBuilding_id as \"LpuBuilding_id\",
				t.KLCountry_id as \"KLCountry_id\",
				case when t.KLAreaLevel_id = 1 then t.KLArea_id else null end as \"KLRGN_id\",
				case when t.KLAreaLevel_id = 2 then t.KLArea_id else null end as \"KLSubRGN_id\",
				case when t.KLAreaLevel_id = 3 then t.KLArea_id else null end as \"KLCity_id\",
				case when t.KLAreaLevel_id = 4 then t.KLArea_id else null end as \"KLTown_id\",
				case when coalesce(rstat.KLRGN_id, 0) > 0 then rstat.KLRGN_id else
					case when coalesce(cstat.KLRGN_id, 0) > 0 then cstat.KLRGN_id else
						case when coalesce(tstat.KLRGN_id, 0) > 0 then tstat.KLRGN_id else null end end end as \"KLRGN_id\",
				t.KLArea_Name as \"KLTown_Name\",
				H.KLStreet_id as \"KLStreet_id\",
				rtrim(KLStreet_FullName) as \"KLStreet_Name\",
				H.KLHouse_Name as \"LpuBuildingStreet_HouseSet\"
			from
			    LpuBuildingKLHouseCoordsRel HCR 
				left join KLHouseCoords HC on (HC.KLHouseCoords_id = HCR.KLHouseCoords_id)
				left join KLArea t on t.KLArea_id = HC.KLArea_id
				left join KLAreaStat rstat on rstat.KLSubRGN_id = HC.KLArea_id
				left join KLAreaStat cstat on cstat.KLCity_id = HC.KLArea_id
				left join KLAreaStat tstat on tstat.KLTown_id = HC.KLArea_id
				left join KLHouse H on H.KLHouse_id = HC.KLHouse_id
				left join v_KLStreet KLStreet on KLStreet.KLStreet_id = H.KLStreet_id
				left join KLSocr ts on ts.KLSocr_id = H.KLSocr_id
			where {$whereString}
			group by {$groupByString}
			union
			select
				'1' as \"Server_id\",
				HCR.LpuBuildingKLHouseCoordsRel_id as \"LpuBuildingStreet_id\",
				LpuBuilding_id as \"LpuBuilding_id\",
				t.KLCountry_id as \"KLCountry_id\",
				case when t.KLAreaLevel_id = 1 then t.KLArea_id else null end as \"KLRGN_id\",
				case when t.KLAreaLevel_id = 2 then t.KLArea_id else null end as \"KLSubRGN_id\",
				case when t.KLAreaLevel_id = 3 then t.KLArea_id else null end as \"KLCity_id\",
				case when t.KLAreaLevel_id = 4 then t.KLArea_id else null end as \"KLTown_id\",
				case when coalesce(rstat.KLRGN_id, 0) > 0 then rstat.KLRGN_id else
					case when coalesce(cstat.KLRGN_id, 0) > 0 then cstat.KLRGN_id else
						case when coalesce(tstat.KLRGN_id, 0) > 0 then tstat.KLRGN_id else null end end end as \"KLRGN_id\",
				t.KLArea_Name as \"KLTown_Name\",
				HC.KLStreet_id as \"KLStreet_id\",
				rtrim(KLStreet_FullName) as \"KLStreet_Name\",
				HC.KLHouseCoords_Name as \"LpuBuildingStreet_HouseSet\"
			from
			    LpuBuildingKLHouseCoordsRel HCR 
				left join KLHouseCoords HC  on (HC.KLHouseCoords_id = HCR.KLHouseCoords_id)
				left join KLArea t  on t.KLArea_id = HC.KLArea_id
				left join KLAreaStat rstat  on rstat.KLSubRGN_id = HC.KLArea_id
				left join KLAreaStat cstat  on cstat.KLCity_id = HC.KLArea_id
				left join KLAreaStat tstat  on tstat.KLTown_id = HC.KLArea_id
				left join v_KLStreet KLStreet  on KLStreet.KLStreet_id = HC.KLStreet_id
			where {$whereString}
			order by KLStreet_Name
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		if (!is_object($result)){
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuSectionTariff(LpuStructure_model $callObject, $data)
	{
		$filterArray = [];
		if (isset($data["LpuSectionTariff_id"]) && is_numeric($data["LpuSectionTariff_id"])) {
			$filterArray[] = "LpuSectionTariff_id = {$data['LpuSectionTariff_id']}";
		}
		if (isset($data["LpuSection_id"]) && is_numeric($data["LpuSection_id"])) {
			$filterArray[] = "LpuSection_id = {$data['LpuSection_id']}";
		}
		if (!empty($data["isClose"]) && $data["isClose"] == 1) {
			$filterArray[] = "(LpuSectionTariff_disDate is null or LpuSectionTariff_disDate > tzgetdate())";
		} elseif (!empty($data["isClose"]) && $data["isClose"] == 2) {
			$filterArray[] = "LpuSectionTariff_disDate <= tzgetdate()";
		}
		if (count($filterArray) == 0) {
			if ((isset($data['Server_id'])) && ($data['Server_id'] > 0)) {
				$filterArray[] = "and Server_id = {$data['Server_id']}";
			} elseif ($data['session']['server_id'] > 0) {
				$filterArray[] = "Server_id = {$data['session']['server_id']}";
			}
		}
		$selectString = "
			Server_id as \"Server_id\",
			LpuSectionTariff_id as \"LpuSectionTariff_id\",
			LpuSection_id as \"LpuSection_id\",
			LpuSectionTariff.TariffClass_id as \"TariffClass_id\",
			rtrim(TariffClass_Name) as \"TariffClass_Name\",
			LpuSectionTariff_Tariff as \"LpuSectionTariff_Tariff\",
			LpuSectionTariff_TotalFactor as \"LpuSectionTariff_TotalFactor\",
			to_char(LpuSectionTariff_setDate::date, '{$callObject->dateTimeForm104}') as \"LpuSectionTariff_setDate\",
			to_char(LpuSectionTariff_disDate::date, '{$callObject->dateTimeForm104}') as \"LpuSectionTariff_disDate\"
		";
		$fromString = "
			LpuSectionTariff 
			left join TariffClass on TariffClass.TariffClass_id = LpuSectionTariff.TariffClass_id
		";
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$sql = "
			select {$selectString}
			from {$fromString}
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuSectionProfile(LpuStructure_model $callObject, $data)
	{
		$filterArray = [];
		$joinArray = ["v_LpuSectionProfile lsp"];
		switch ($data["LpuUnitType_id"]) {
			case 13:
				$filterArray[] = "lsp.LpuSectionProfile_Code in ('84')"; // Профили для СМП
				break;
			case 2: // https://redmine.swan.perm.ru/issues/20265
				$joinArray[] = "left join v_LpuSection ls on ls.LpuSectionProfile_id = lsp.LpuSectionProfile_id and ls.Lpu_id = :Lpu_id";
				$filterArray[] = "(ls.LpuSection_id is not null or lsp.LpuSectionProfile_Code in ('917', '1017', '1019'))"; // Профили для ДД
				break;
			default:
				$joinArray[] = "inner join v_LpuSection LpuSection  on LpuSection.LpuSectionProfile_id = lsp.LpuSectionProfile_id";
				break;
		}
		if (!empty($data["isProfileSpecCombo"])) {
			$filterArray[] = "lsp.ProfileSpec_Name is not null";
			$filterArray[] = "lsp.LpuSectionProfile_InetDontShow is null";
			$filterArray[] = "coalesce(msf.RecType_id, 6) not in (2,5,6,8)";
			$filterArray[] = "coalesce(msf.WorkData_endDate, '2030-01-01') > tzgetdate()";
			$joinArray[] = "inner join v_MedStaffFact msf on msf.LpuSection_id = ls.LpuSection_id";
		}
		$selectString = "
			lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
			lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
			lsp.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
			lsp.ProfileSpec_Name as \"ProfileSpec_Name\"
		";
		$fromString = implode(" ", $joinArray);
		$whereString = (count($filterArray) != 0)?"where ".implode(" and ", $filterArray):"";
		$orderBy = "lsp.LpuSectionProfile_Code";
		$sql = "
			Select distinct {$selectString}
			from {$fromString}
			{$whereString}
			order by {$orderBy}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $data);
		if(!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuSectionShift(LpuStructure_model $callObject, $data)
	{
		$filterArray = [];
		if ((isset($data["Server_id"])) && ($data["Server_id"] > 0)) {
			$filterArray[] = "Server_id = {$data['Server_id']}";
		} elseif ($data["session"]["server_id"] > 0) {
			$filterArray[] = "Server_id = {$data['session']['server_id']}";
		}
		if ((isset($data["LpuSectionShift_id"])) && (is_numeric($data["LpuSectionShift_id"]))) {
			$filterArray[] = "LpuSectionShift_id = {$data['LpuSectionShift_id']}";
		}
		if ((isset($data["LpuSection_id"])) && (is_numeric($data["LpuSection_id"]))) {
			$filterArray[] = "LpuSection_id = {$data['LpuSection_id']}";
		}
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$sql = "
			Select
				Server_id as \"Server_id\",
				LpuSectionShift_id as \"LpuSectionShift_id\",
				LpuSection_id as \"LpuSection_id\",
				LpuSectionShift_Count as \"LpuSectionShift_Count\",
				to_char(LpuSectionShift_setDate::date, '{$callObject->dateTimeForm104}') as \"LpuSectionShift_setDate\",
				to_char(LpuSectionShift_disDate::date, '{$callObject->dateTimeForm104}') as \"LpuSectionShift_disDate\"
			from LpuSectionShift 
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuSectionBedState(LpuStructure_model $callObject, $data)
	{
		$filterArray = [];
		if (!empty($data["LpuSectionBedState_id"])) {
			$filterArray[] = "LSBS.LpuSectionBedState_id = :LpuSectionBedState_id";
		}
		if (!empty($data["LpuSection_id"])) {
			$filterArray[] = "LSBS.LpuSection_id = :LpuSection_id";
		}
		if (isset($data["is_Act"])) {
			$filterArray[] = "LSBS.LpuSectionBedState_begDate <= tzgetdate()";
			$filterArray[] = "(LSBS.LpuSectionBedState_endDate >= tzgetdate() or LSBS.LpuSectionBedState_endDate is null)";
		}
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$sql = "
			Select
				LSBS.Server_id as \"Server_id\",
				LSBS.LpuSectionBedState_id as \"LpuSectionBedState_id\",
				LSBS.LpuSection_id as \"LpuSection_id\",
				LSBS.LpuSectionBedState_ProfileName as \"LpuSectionBedState_ProfileName\",
				LSBS.LpuSectionBedState_Plan as \"LpuSectionBedState_Plan\",
				LSBS.LpuSectionBedState_Fact as \"LpuSectionBedState_Fact\",
				LSBS.LpuSectionBedState_Repair as \"LpuSectionBedState_Repair\",
				LSP.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				LSBP.LpuSectionBedProfile_id as \"LpuSectionBedProfile_id\",
				LSBS.LpuSectionBedState_CountOms as \"LpuSectionBedState_CountOms\",
				coalesce(LSP.LpuSectionProfile_Name, LSBP.LpuSectionBedProfile_Name) as \"LpuSectionProfile_Name\", -- для Самары профили коек. (refs #16934)
				to_char(LSBS.LpuSectionBedState_begDate::timestamp, '{$callObject->dateTimeForm104}') as \"LpuSectionBedState_begDate\",
				to_char(LSBS.LpuSectionBedState_endDate::timestamp, '{$callObject->dateTimeForm104}') as \"LpuSectionBedState_endDate\",
				LSBS.LpuSectionBedState_MalePlan as \"LpuSectionBedState_MalePlan\",
				LSBS.LpuSectionBedState_MaleFact as \"LpuSectionBedState_MaleFact\",
				LSBS.LpuSectionBedState_FemalePlan as \"LpuSectionBedState_FemalePlan\",
				LSBS.LpuSectionBedState_FemaleFact as \"LpuSectionBedState_FemaleFact\",
				LSBS.LpuSectionBedProfileLink_fedid as \"LpuSectionBedProfileLink_id\"
			from
				v_LpuSectionBedState LSBS 
				left join v_LpuSectionProfile LSP on LSP.LpuSectionProfile_id = LSBS.LpuSectionProfile_id
				left join v_LpuSectionBedProfile LSBP on LSBP.LpuSectionBedProfile_id = LSBS.LpuSectionBedProfile_id
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuSectionFinans(LpuStructure_model $callObject, $data)
	{
		$filterArray = [];
		if (isset($data["LpuSectionFinans_id"]) && is_numeric($data["LpuSectionFinans_id"])) {
			$filterArray[] = "lsf.LpuSectionFinans_id = {$data['LpuSectionFinans_id']}";
		}
		if (isset($data["LpuSection_id"]) && is_numeric($data["LpuSection_id"])) {
			$filterArray[] = "lsf.LpuSection_id = {$data['LpuSection_id']}";
		}
		if (!empty($data["isClose"]) && $data["isClose"] == 1) {
			$filterArray[] = "(lsf.LpuSectionFinans_endDate is null or lsf.LpuSectionFinans_endDate > tzgetdate())";
		} elseif (!empty($data["isClose"]) && $data["isClose"] == 2) {
			$filterArray[] = "lsf.LpuSectionFinans_endDate <= tzgetdate()";
		}
		$whereString = (count($filterArray) != 0)?"where ".implode(" and ", $filterArray) : "";
		$sql = "
			Select
				lsf.Server_id as \"Server_id\",
				lsf.LpuSectionFinans_id as \"LpuSectionFinans_id\",
				lsf.LpuSection_id as \"LpuSection_id\",
				lsf.PayType_id as \"PayType_id\",
				rtrim(pt.PayType_Name) as \"PayType_Name\",
				lsf.LpuSectionFinans_Plan as \"LpuSectionFinans_Plan\",
				lsf.LpuSectionFinans_PlanHosp as \"LpuSectionFinans_PlanHosp\",
				lsf.LpuSectionFinans_IsMRC as \"LpuSectionFinans_IsMRC\",
				coalesce(mrc.YesNo_Name, '') as \"IsMRC_Name\",
				coalesce(qoff.YesNo_Name, '') as \"IsQuoteOff_Name\",
				lsf.LpuSectionFinans_IsQuoteOff as \"LpuSectionFinans_IsQuoteOff\",
				to_char(lsf.LpuSectionFinans_begDate::date, '{$callObject->dateTimeForm104}') as \"LpuSectionFinans_begDate\",
				to_char(lsf.LpuSectionFinans_endDate::date, '{$callObject->dateTimeForm104}') as \"LpuSectionFinans_endDate\"
			from
				LpuSectionFinans lsf 
				left join v_PayType pt on pt.PayType_id = lsf.PayType_id
				left join YesNo mrc on mrc.YesNo_id = lsf.LpuSectionFinans_IsMRC
				left join YesNo qoff on qoff.YesNo_id = lsf.LpuSectionFinans_IsQuoteOff
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuSectionLicence(LpuStructure_model $callObject, $data)
	{
		$filterArray = [];
		if (isset($data["Server_id"]) && $data["Server_id"] > 0) {
			$filterArray[] = "Server_id = {$data['Server_id']}";
		} elseif ($data["session"]["server_id"] > 0) {
			$filterArray[] = "Server_id = {$data['session']['server_id']}";
		}
		if (isset($data["LpuSectionLicence_id"]) && is_numeric($data["LpuSectionLicence_id"])) {
			$filterArray[] = "LpuSectionLicence_id = {$data['LpuSectionLicence_id']}";
		}
		if (isset($data["LpuSection_id"]) && is_numeric($data["LpuSection_id"])) {
			$filterArray[] = "LpuSection_id = {$data['LpuSection_id']}";
		}
		if (!empty($data["isClose"]) && $data["isClose"] == 1) {
			$filterArray[] = "(LpuSectionLicence_endDate is null or LpuSectionLicence_endDate > tzgetdate())";
		} elseif (!empty($data["isClose"]) && $data["isClose"] == 2) {
			$filterArray[] = "LpuSectionLicence_endDate <= tzgetdate()";
		}
		$whereString = (count($filterArray) != 0)?"where ".implode(" and ", $filterArray) : "";
		$sql = "
			select
				Server_id as \"Server_id\",
				LpuSectionLicence_id as \"LpuSectionLicence_id\",
				LpuSection_id as \"LpuSection_id\",
				rtrim(LpuSectionLicence_Num) as \"LpuSectionLicence_Num\",
				to_char(LpuSectionLicence_begDate::date, '{$callObject->dateTimeForm104}') as \"LpuSectionLicence_begDate\",
				to_char(LpuSectionLicence_endDate::date, '{$callObject->dateTimeForm104}') as \"LpuSectionLicence_endDate\"
			from LpuSectionLicence 
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuSectionTariffMes(LpuStructure_model $callObject, $data)
	{
		$filterArray = [];
		if (isset($data["LpuSectionTariffMes_id"]) && is_numeric($data["LpuSectionTariffMes_id"])) {
			$filterArray[] = "LpuSectionTariffMes_id = {$data['LpuSectionTariffMes_id']}";
		}
		if (isset($data["LpuSection_id"]) && is_numeric($data["LpuSection_id"])) {
			$filterArray[] = "LpuSection_id = {$data['LpuSection_id']}";
		}
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$sql = "
			Select
				tm.LpuSectionTariffMes_id as \"LpuSectionTariffMes_id\",
				tm.LpuSection_id as \"LpuSection_id\",
				tm.Mes_id as \"Mes_id\",
				m.Mes_Code as \"Mes_Code\",
				d.Diag_Name as \"Diag_Name\",
				tm.TariffMesType_id as \"TariffMesType_id\",
				tmt.TariffMesType_Name as \"TariffMesType_Name\",
				tm.LpuSectionTariffMes_Tariff as \"LpuSectionTariffMes_Tariff\",
				to_char(tm.LpuSectionTariffMes_setDate::date, '{$callObject->dateTimeForm104}') as \"LpuSectionTariffMes_setDate\",
				to_char(tm.LpuSectionTariffMes_disDate::date, '{$callObject->dateTimeForm104}') as \"LpuSectionTariffMes_disDate\"
			from
				LpuSectionTariffMes tm 
				left join MesOld m on tm.Mes_id = m.Mes_id
				left join Diag d on m.Diag_id = d.Diag_id
				left join TariffMesType tmt  on tm.TariffMesType_id = tmt.TariffMesType_id
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuSectionPlan(LpuStructure_model $callObject, $data)
	{
		$filter = "(1=1) and p.LpuSectionPlan_PlanHosp is null "; //следовательно запись не относится к плану госпитализаций
		if (isset($data["LpuSectionPlan_id"]) && is_numeric($data["LpuSectionPlan_id"])) {
			$filter .= " and LpuSectionPlan_id = {$data['LpuSectionPlan_id']}";
		}
		if (isset($data["LpuSection_id"]) && is_numeric($data["LpuSection_id"])) {
			$filter .= " and LpuSection_id = {$data['LpuSection_id']}";
		}
		$sql = "
			select
				p.LpuSectionPlan_id as \"LpuSectionPlan_id\",
				p.LpuSection_id as \"LpuSection_id\",
				p.LpuSectionPlanType_id as \"LpuSectionPlanType_id\",
				pt.LpuSectionPlanType_Name as \"LpuSectionPlanType_Name\",
				to_char(p.LpuSectionPlan_setDate::date, '{$callObject->dateTimeForm104}') as \"LpuSectionPlan_setDate\",
				to_char(p.LpuSectionPlan_disDate::date, '{$callObject->dateTimeForm104}') as \"LpuSectionPlan_disDate\"
			from
				LpuSectionPlan p 
				left join LpuSectionPlanType pt on p.LpuSectionPlanType_id = pt.LpuSectionPlanType_id
			where {$filter}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuSectionQuoteFact(LpuStructure_model $callObject, $data)
	{
		$filter = "(1=1) ";
		if ($data['LpuUnitType_id'] == 2) {
			$filter .= " and LpuUnit.LpuUnitType_id in (2, 10) ";
		} else {
			$filter .= " and LpuUnit.LpuUnitType_id = :LpuUnitType_id ";
		}
		$sql = "
			select coalesce(sum(registrydata_kdfact), 0) as \"LpuSectionQuote_Fact\"
			from
				v_Registry Registry 
				inner join v_RegistryData RegistryData on Registry.Registry_id = RegistryData.Registry_id and coalesce(RegistryData.RegistryData_IsPrev, 1) = 1
				inner join LpuSection on LpuSection.LpuSection_id = RegistryData.LpuSection_id and LpuSection.LpuSectionProfile_id = :LpuSectionProfile_id
			 	inner join LpuUnit on LpuUnit.LpuUnit_id = LpuSection.LpuUnit_id and {$filter}
			where Registry.Lpu_id = :Lpu_id
			  and extract(year from Registry.Registry_endDate) = :LpuSectionQuote_Year
			  and Registry.KatNasel_id = 1
			  and Registry.RegistryType_id = :RegistryType_id
			  and Registry.RegistryStatus_id = 4
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuSectionQuote(LpuStructure_model $callObject, $data)
	{
		$filter = "(1=1) ";
		if ($data["LpuSectionQuote_id"] > 0) {
			$filter .= " and Quote.LpuSectionQuote_id = :LpuSectionQuote_id ";
		}
		if ($data["Lpu_id"] > 0) {
			$filter .= " and Quote.Lpu_id = :Lpu_id";
		}
		if (!empty($data["LpuSectionQuote_Year"])) {
			$filter .= " and Quote.LpuSectionQuote_Year = :LpuSectionQuote_Year";
		}
		if (!empty($data["LpuUnitType_id"])) {
			$filter .= " and Quote.LpuUnitType_id = :LpuUnitType_id";
		}
		if (!empty($data["LpuSectionProfile_id"])) {
			$filter .= " and Quote.LpuSectionProfile_id = :LpuSectionProfile_id";
		}
		if (!empty($data["PayType_id"])) {
			$filter .= " and Quote.PayType_id = :PayType_id";
		}
		$sql = "
			select
				Quote.LpuSectionQuote_id as \"LpuSectionQuote_id\",
				Quote.Lpu_id as \"Lpu_id\",
				Quote.LpuSectionQuote_Year as \"LpuSectionQuote_Year\",
				Quote.LpuSectionQuote_Count as \"LpuSectionQuote_Count\",
				to_char(Quote.LpuSectionQuote_begDate::date, '{$callObject->dateTimeForm104}') as \"LpuSectionQuote_begDate\",
				Quote.LpuUnitType_id as \"LpuUnitType_id\",
				Quote.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				rtrim(LpuUnitType.LpuUnitType_Name) as \"LpuUnitType_Name\",
				rtrim(LpuSectionProfile.LpuSectionProfile_Name) as \"LpuSectionProfile_Name\",
				case
					when Quote.LpuUnitType_id in (2,10) then 2
					when Quote.LpuUnitType_id in (1,6,7,9) then 1
				end as \"RegistryType_id\",
				Quote.PayType_id as \"PayType_id\",
				PayType.PayType_Name as \"PayType_Name\",
				Quote.QuoteUnitType_id as \"QuoteUnitType_id\",
				QuoteUnitType.QuoteUnitType_Name as \"QuoteUnitType_Name\"
			from
				v_LpuSectionQuote Quote 
				left join LpuSectionProfile on LpuSectionProfile.LpuSectionProfile_id = Quote.LpuSectionProfile_id
				left join LpuUnitType on LpuUnitType.LpuUnitType_id = Quote.LpuUnitType_id
				left join PayType on PayType.PayType_id = Quote.PayType_id
				left join QuoteUnitType on QuoteUnitType.QuoteUnitType_id = Quote.QuoteUnitType_id
			where {$filter}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function GetPersonDopDispPlan(LpuStructure_model $callObject, $data)
	{
		$filter = "(1=1) ";
		if (!empty($data["PersonDopDispPlan_id"])) {
			$filter .= " and PDDP.PersonDopDispPlan_id = :PersonDopDispPlan_id ";
		}
		if (!empty($data["Lpu_id"])) {
			$filter .= " and PDDP.Lpu_id = :Lpu_id";
		}
		if (!empty($data["PersonDopDispPlan_Year"])) {
			$filter .= " and PDDP.PersonDopDispPlan_Year = :PersonDopDispPlan_Year";
		}
		if (!empty($data["DispDopClass_id"])) {
			$filter .= " and PDDP.DispDopClass_id = :DispDopClass_id";
		}
		$sql = "
			select
				PDDP.PersonDopDispPlan_id as \"PersonDopDispPlan_id\",
				PDDP.Lpu_id as \"Lpu_id\",
				PDDP.LpuRegion_id as \"LpuRegion_id\",
				LR.LpuRegion_Name as \"LpuRegion_Name\",
				PDDP.DispDopClass_id as \"DispDopClass_id\",
				PDDP.PersonDopDispPlan_Year as \"PersonDopDispPlan_Year\",
				PDDP.PersonDopDispPlan_Month as \"PersonDopDispPlan_Month\",
				PDDP.PersonDopDispPlan_Month as \"PersonDopDispPlan_MonthName\",
				PDDP.EducationInstitutionType_id as \"EducationInstitutionType_id\",
				PDDP.QuoteUnitType_id as \"QuoteUnitType_id\",
				EIT.EducationInstitutionType_Name as \"EducationInstitutionType_Name\",
				QUT.QuoteUnitType_Name as \"QuoteUnitType_Name\",
				PDDP.PersonDopDispPlan_Plan as \"PersonDopDispPlan_Plan\",
				puc.pmUser_groups as \"groups\"
			from
				v_PersonDopDispPlan PDDP 
				left join v_LpuRegion LR on LR.LpuRegion_id = PDDP.LpuRegion_id
				left join pmUserCache puc on puc.pmUser_id = PDDP.pmUser_updID
				left join v_EducationInstitutionType EIT on eit.EducationInstitutionType_id = PDDP.EducationInstitutionType_id
				left join v_QuoteUnitType QUT on QUT.QuoteUnitType_id = PDDP.QuoteUnitType_id
			where {$filter}
		";
		$arMonthOf = [
			1 => "Январь", 2 => "Февраль", 3 => "Март", 4 => "Апрель", 5 => "Май",
			6 => "Июнь", 7 => "Июль", 8 => "Август", 9 => "Сентябрь", 10 => "Октябрь",
			11 => "Ноябрь", 12 => "Декабрь"
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $data);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result("array");
		foreach ($resp as &$item) {
			if (isset($arMonthOf[$item["PersonDopDispPlan_Month"]])) {
				$item["PersonDopDispPlan_MonthName"] = $arMonthOf[$item["PersonDopDispPlan_Month"]];
			} else {
				$item["PersonDopDispPlan_MonthName"] = "";
			}
		}
		return $resp;
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function getLpuUnitIsOMS(LpuStructure_model $callObject, $data)
	{
		$join = "";
		$where = "(1=1)";
		$params = [];
		if (!empty($data["LpuSection_id"])) {
			$join .= " inner join LpuSection LS on LS.LpuUnit_id = LU.LpuUnit_id";
			$where .= " and LS.LpuSection_id = :LpuSection_id";
			$params["LpuSection_id"] = $data["LpuSection_id"];
		}
		if (!empty($data["LpuUnit_id"])) {
			$where .= " and LU.LpuUnit_id = :LpuUnit_id";
			$params["LpuUnit_id"] = $data["LpuUnit_id"];
		}
		$selectString = "
			case
				when LU.LpuUnit_IsOMS is null then 1 else YN.YesNo_Code
			end as \"LpuUnit_IsOMS\"
		";
		$fromString = "
			v_LpuUnit LU 
			left join v_YesNo YN  on YN.YesNo_id = LU.LpuUnit_IsOMS
			{$join}
		";
		$query = "
			select {$selectString}
			from {$fromString}
			where {$where}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuSectionBedStatePlan(LpuStructure_model $callObject, $data)
	{
		$queryParams = ($data['LpuSection_isParent']) ? ['LpuSection_id' => $data['LpuSection_id']] : ['LpuSection_id' => $data['LpuSection_pid']];
		$sql = "
			SELECT
				LpuSection.LpuSection_id as \"LpuSection_id\",
				LpuSection.LpuSection_pid as \"LpuSection_pid\",
				LSBS.LpuSectionBedState_Plan as \"LpuSectionBedState_Plan\"
			FROM
				v_LpuSection LpuSection 
				left join v_LpuSectionBedState LSBS  on LpuSection.LpuSection_id = LSBS.LpuSection_id AND LSBS.LpuSectionBedState_isAct = 2
			WHERE LpuSection.LpuSection_id = :LpuSection_id
			   OR LpuSection.LpuSection_pid = :LpuSection_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuSectionGrid(LpuStructure_model $callObject, $data)
	{
		$filter = "";
		$UDType_fid = "";
		$addselect = "";
		$addjoin = "";
		$queryParams = [];
		if (isset($data["LpuUnit_id"]) && $data["LpuUnit_id"] > 0) {
			$filter .= "and LpuUnit_id = :LpuUnit_id ";
			$queryParams["LpuUnit_id"] = $data["LpuUnit_id"];
			$UDType_fid = ",
				(
					select LU.UnitDepartType_fid
					from v_LpuUnit LU 
					where LpuUnit_id = :LpuUnit_id
				) as \"UnitDepartType_fid\" ";
		}
		if (isset($data["LpuSection_pid"]) && $data["LpuSection_pid"] > 0) {
			$filter .= "and LpuSection_pid = :LpuSection_pid ";
			$queryParams["LpuSection_pid"] = $data["LpuSection_pid"];
		} else {
			$filter .= "and LpuSection_pid is null";
		}
		if ($callObject->getRegionNick() == 'kz') {
			$addselect .= ", FP.NameRU||' ('||FP.CodeRu||')' as \"FPID\"";
			$addjoin .= "
				left join r101.LpuSectionFPIDLink lsfl on lsfl.LpuSection_id = LpuSection.LpuSection_id
				left join r101.GetFP FP on FP.FPID = lsfl.FPID
			";
		}
		if (!empty($data["isClose"]) && $data["isClose"] == 1) {
			$filter .= " and (LpuSection.LpuSection_disDate is null or LpuSection.LpuSection_disDate > tzgetdate())";
		} elseif (!empty($data["isClose"]) && $data["isClose"] == 2) {
			$filter .= " and LpuSection.LpuSection_disDate <= tzgetdate()";
		}
		$selectString = "
			LpuSection.LpuSection_id as \"LpuSection_id\",
			LpuSection.LpuSection_pid as \"LpuSection_pid\",
			LpuSection.LpuSection_Code as \"LpuSection_Code\",
			LpuSection.LpuSection_Name as \"LpuSection_Name\",
			LSP.LpuSectionProfile_id as \"LpuSectionProfile_id\",
			LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
			LSBS.LpuSectionBedState_id as \"LpuSectionBedState_id\",
			LSBS.LpuSectionBedState_Plan as \"LpuSectionBedState_Plan\",
			LSBS.LpuSectionBedState_Fact as \"LpuSectionBedState_Fact\",
			LSBS.LpuSectionBedState_Repair as \"LpuSectionBedState_Repair\"
			{$UDType_fid}
			{$addselect}
		";
		$fromString = "
			v_LpuSection LpuSection 
			left join v_LpuSectionProfile LSP on LpuSection.LpuSectionProfile_id = LSP.LpuSectionProfile_id
			left join v_LpuSectionBedState LSBS on LpuSection.LpuSection_id = LSBS.LpuSection_id AND LSBS.LpuSectionBedState_isAct = 2
			{$addjoin}
		";
		$whereString = "
			(1=1) {$filter}
		";
		$orderByString = "
			LpuSection.LpuSection_Code
		";
		$sql = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
			order by {$orderByString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuSectionList(LpuStructure_model $callObject, $data)
	{
		$filter = "(1=1) ";
		$UDType_fid = "";
		$params = [];
		if (isset($data["Lpu_id"]) && $data["Lpu_id"] > 0) {
			$filter .= "and Lpu_id = :Lpu_id ";
			$params["Lpu_id"] = $data["Lpu_id"];
		}
		if (isset($data["LpuUnit_id"]) && $data["LpuUnit_id"] > 0) {
			$filter .= "and LpuUnit_id = :LpuUnit_id ";
			$params["LpuUnit_id"] = $data["LpuUnit_id"];
			$UDType_fid = ",
				(select LU.UnitDepartType_fid from v_LpuUnit LU where LpuUnit_id = :LpuUnit_id) as \"UnitDepartType_fid\"
			";
		}
		if (isset($data["LpuSection_id"]) && $data["LpuSection_id"] > 0) {
			$filter .= "and LpuSection_id = :LpuSection_id ";
			$params["LpuSection_id"] = $data["LpuSection_id"];
		}
		if (isset($data["LpuSection_pid"]) && $data["LpuSection_pid"] > 0) {
			$filter .= "and LpuSection_pid =: LpuSection_pid ";
			$params["LpuSection_pid"] = $data["LpuSection_pid"];
		}
		$selectString = "
			LpuSection_id as \"LpuSection_id\",
			LpuSection_pid as \"LpuSection_pid\",
			LpuUnit_id as \"LpuUnit_id\",
			Lpu_id as \"Lpu_id\",
			LpuSectionProfile_id as \"LpuSectionProfile_id\",
			LpuSection_Code as \"LpuSection_Code\",
			LpuSectionCode_id as \"LpuSectionCode_id\",
			LpuSection_Name as \"LpuSection_Name\",
			PalliativeType_id as \"PalliativeType_id\",
			to_char(LpuSection_setDate::date, '{$callObject->dateTimeForm104}') as \"LpuSection_setDate\",
			to_char(LpuSection_disDate::date, '{$callObject->dateTimeForm104}') as \"LpuSection_disDate\",
			LpuSectionAge_id as \"LpuSectionAge_id\",
			LpuSectionBedProfile_id as \"LpuSectionBedProfile_id\",
			MESLevel_id as \"MESLevel_id\",
			case when LpuSection_IsF14 = 2 then 'on' else 'off' end as \"LpuSection_F14\",
			LpuSection_Descr as \"LpuSection_Descr\",
			LpuSection_Contacts as \"LpuSection_Contacts\",
			LpuSectionHospType_id as \"LpuSectionHospType_id\",
			LpuSection_PlanVisitShift as \"LpuSection_PlanVisitShift\",
			LpuSection_PlanTrip as \"LpuSection_PlanTrip\",
			LpuSection_PlanVisitDay as \"LpuSection_PlanVisitDay\",
			LpuSection_PlanAutopShift as \"LpuSection_PlanAutopShift\",
			LpuSection_PlanResShift as \"LpuSection_PlanResShift\",
			LpuSection_KolJob as \"LpuSection_KolJob\",
			LpuSection_KolAmbul as \"LpuSection_KolAmbul\",
			LpuSection_IsCons as \"LpuSection_IsCons\",
			LpuSection_IsExportLpuRegion as \"LpuSection_IsExportLpuRegion\",
			LevelType_id as \"LevelType_id\",
			LpuSectionDopType_id as \"LpuSectionDopType_id\",
			LpuSectionType_id as \"LpuSectionType_id\",
			LpuSection_Area as \"LpuSection_Area\",
			LpuSection_CountShift as \"LpuSection_CountShift\",
			LpuCostType_id as \"LpuCostType_id\",
			FRMPSubdivision_id as \"FRMPSubdivision_id\",
			FRMOUnit_id as \"FRMOUnit_id\",
			FRMOSection_id as \"FRMOSection_id\",
			LpuSection_FRMOBuildingOid as \"LpuSection_FRMOBuildingOid\",
			case when LpuSection_IsDirRec = 2 then 'on' else 'off' end as \"LpuSection_IsDirRec\",
			case when LpuSection_IsQueueOnFree = 2 then 'on' else 'off' end as \"LpuSection_IsQueueOnFree\",
			case when LpuSection_IsUseReg = 2 then 'on' else 'off' end as \"LpuSection_IsUseReg\",
			coalesce(LpuSection_IsHTMedicalCare, 1) as \"LpuSection_IsHTMedicalCare\",
			case when LpuSection_IsNoKSG = 2 then 'on' else 'off' end as \"LpuSection_IsNoKSG\",
			(select count(LpuSection_id) from LpuSection LS  where LS.LpuSection_pid = LpuSection.LpuSection_id and coalesce(LpuSection_deleted, 1) = 1) as \"pidcount\"
			{$UDType_fid}
		".$callObject->getLpuSectionListAdditionalFields();
		$fromString = "v_LpuSection LpuSection ".$callObject->getLpuSectionListAdditionalJoin();
		$sql = "
			select {$selectString}
			from {$fromString}
			where {$filter}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuSectionPid(LpuStructure_model $callObject, $data)
	{
		$filterArray = ["LpuSection_pid is null"];
		if (isset($data["Lpu_id"]) && $data["Lpu_id"] > 0) {
			$filterArray[] = "Lpu_id = :Lpu_id";
		}
		if (isset($data["LpuUnit_id"]) && $data["LpuUnit_id"] > 0) {
			$filterArray[] = "LpuUnit_id = :LpuUnit_id";
		}
		if (isset($data["LpuSection_id"]) && $data["LpuSection_id"] > 0) {
			$filterArray[] = "LpuSection_id != :LpuSection_id";
		}
		if (isset($data["LpuSection_pid"]) && $data["LpuSection_pid"] > 0) {
			$filterArray[]  = "LpuSection_pid = :LpuSection_pid";
		}
		$whereString = implode(" and ", $filterArray);
		$sql = "
			select
				LpuSection_id as \"LpuSection_id\",
				rtrim(LpuSection_Code) as \"LpuSection_Code\",
				rtrim(LpuSection_Name) as \"LpuSection_Name\"
			from v_LpuSection LpuSection 
			where {$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function GetLpuUsluga(LpuStructure_model $callObject, $data)
	{
		$filter = "(1=1) ";
		// Фильтры для первого уровня структуры МО ( на МО )
		if ((isset($data["level"])) && ($data["level"] == 1)) {
			if ((isset($data["Lpu_id"])) && ($data["Lpu_id"] > 0))
				$filter .= "and Lpu_id=" . $data["Lpu_id"];
			else
				$filter .= "and Lpu_id=" . $data["session"]["lpu_id"];
			if ((isset($data["UslugaSection_id"])) && ($data["UslugaSection_id"] > 0))
				$filter .= "and Usluga_id=" . $data["Usluga_id"];
		} else {
			if ((isset($data["LpuSection_id"])) && ($data["LpuSection_id"] > 0))
				$filter .= "and LpuSection_id=" . $data["LpuSection_id"];
			else if ((isset($data["LpuUnit_id"])) && ($data["LpuUnit_id"] > 0))
				$filter .= "and LpuUnit_id=" . $data["LpuUnit_id"];
			if ((isset($data["UslugaSection_id"])) && ($data["UslugaSection_id"] > 0))
				$filter .= "and UslugaSection_id=" . $data["UslugaSection_id"];
			if ((isset($data["Usluga_id"])) && ($data["Usluga_id"] > 0))
				$filter .= "and Usluga_id=" . $data["Usluga_id"];
		}
		if ((isset($data['level'])) && ($data['level'] == 1)) {
			$sql = "
                select
                    Usluga_id as \"Usluga_id\",
                    Lpu_id as \"Lpu_id\",
                    Usluga_Code as \"Usluga_Code\",
                    Usluga_Name as \"Usluga_Name\"
                from v_Usluga 
                where {$filter} and UslugaType_id = 2
			";
		} else {
			$sql = "
                select
                    us.Usluga_id as \"Usluga_id\",
                    us.UslugaSection_id as \"UslugaSection_id\",
                    Usluga_Code as \"Usluga_Code\",
                    Usluga_Name as \"Usluga_Name\",
                    us.LpuSection_id as \"LpuSection_id\",
                    us.UslugaPrice_ue as \"UslugaPrice_ue\"
                from
                	v_UslugaSection us 
                	left join Usluga on Usluga.Usluga_id = us.Usluga_id
                where {$filter}
			";
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function GetLpuSectionWard(LpuStructure_model $callObject, $data)
	{
		$filter = "";
		$queryParams = [
			"LpuSection_id" => $data["LpuSection_id"]
		];
		if (isset($data["LpuSectionWard_id"])) {
			$filter .= " and LpuSectionWard_id = :LpuSectionWard_id";
			$queryParams["LpuSectionWard_id"] = $data["LpuSectionWard_id"];
		}
		$sql = "
			select
			    LpuSectionWard.Server_id as \"Server_id\",
			    LpuSectionWard_id as \"LpuSectionWard_id\",
			    LpuSectionWard_isAct as \"LpuSectionWard_isAct\",
			    LpuSectionWard.LpuSection_id as \"LpuSection_id\",
			    LpuSectionWard_Name as \"LpuSectionWard_Name\",
				LpuSectionWard_Floor as \"LpuSectionWard_Floor\",
			    LpuWardType.LpuWardType_id as \"LpuWardType_id\",
			    LpuSectionWard.Sex_id as \"Sex_id\",
			    case
			        when LpuSectionWard.Sex_id = 1 then 'мужская'
			        when LpuSectionWard.Sex_id = 2 then 'женская'
			        else 'общая'
			    end as \"Sex_Name\",
			    LpuWardType.LpuWardType_Code as \"LpuWardType_Code\",
			    LpuWardType.LpuWardType_Name as \"LpuWardType_Name\",
			    coalesce(LpuSectionWard_BedCount, 0) as \"LpuSectionWard_BedCount\",
			    coalesce(LpuSectionWard_MainPlace, 0) as \"LpuSectionWard_MainPlace\",
			    coalesce(LpuSectionWard_BedRepair, 0) as \"LpuSectionWard_BedRepair\",
			    coalesce(LpuSectionWard_CountRoom, 0) as \"LpuSectionWard_CountRoom\",
			    coalesce(LpuSectionWard_DopPlace, 0) as \"LpuSectionWard_DopPlace\",
			    LpuSectionWard_Views as \"LpuSectionWard_Views\",
			    LpuSectionWard_Square as \"LpuSectionWard_Square\",
			    LpuSectionWard_DayCost as \"LpuSectionWard_DayCost\",
			    to_char(LpuSectionWard_setDate::date, '{$callObject->dateTimeForm104}') as \"LpuSectionWard_setDate\",
			    to_char(LpuSectionWard_disDate::date, '{$callObject->dateTimeForm104}') as \"LpuSectionWard_disDate\",
			    LpuSectionWard.pmUser_insID as \"pmUser_insID\",
			    LpuSectionWard.pmUser_updID as \"pmUser_updID\"
			from
			    v_LpuSectionWard LpuSectionWard
			    left join v_LpuWardType LpuWardType  on (LpuSectionWard.LpuWardType_id = LpuWardType.LpuWardType_id)
			where LpuSectionWard.LpuSection_id = :LpuSection_id
				{$filter}
			order by LpuSectionWard_Name
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getStaffOSMGridDetail(LpuStructure_model $callObject, $data)
	{
		$filterArray = [];
		$queryParams = [];
		if (isset($data["Staff_id"])) {
			$filterArray[] = "Staff_id = :Staff_id";
			$queryParams["Staff_id"] = $data["Staff_id"];
		} else {
			$filterArray[] = "Lpu_id = :Lpu_id";
			$queryParams["Lpu_id"] = $data["Lpu_id"];
		}
		$whereString = implode(" and ", $filterArray);
		$sql = "
			SELECT
			    Staff_id as \"Staff_id\",
			    Staff_Num as \"Staff_Num\",
			    Staff_OrgName as \"Staff_OrgName\",
			    Staff_OrgBasis as \"Staff_OrgBasis\",
				Lpu_id as \"Lpu_id\",
			    to_char(Staff_OrgDT::date, '{$callObject->dateTimeForm104}') as \"Staff_OrgDT\"
			FROM fed.v_Staff 
			WHERE {$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuSectionBedAllQuery(LpuStructure_model $callObject, $data)
	{
		$query = "
			select
				sum(LpuSectionBedState_PlanCount) as \"LpuSection_CommonCount\",-- Общее количество коек в отделении, план
				sum(LpuSectionBedState_ProfileCount) as \"LpuSection_ProfileCount\",-- Из них по основному профилю
				sum(LpuSectionBedState_UzCount) as \"LpuSection_UzCount\",-- Из них узких коек
				sum(LpuSectionBedState_Fact) as \"LpuSection_Fact\",-- Общее количество коек в отделении, факт
				sum(LpuSectionWard_BedCount) as \"LpuSection_BedCount\",-- Общее количество коек по палатам
				sum(LpuSectionWard_BedRepair) as \"LpuSection_BedRepair\",-- Из них на ремонте
				coalesce(LS.LpuSection_MaxEmergencyBed, 0) as \"LpuSection_MaxEmergencyBed\"-- Плановый резерв коек для экстренных госпитализаций, не более
			from v_LpuSection LS 
				left join lateral (
				    select
						sum(LpuSectionWard_BedCount) as LpuSectionWard_BedCount,
						sum(LpuSectionWard_BedRepair) as LpuSectionWard_BedRepair
					from v_LpuSectionWard LSW 
				    where
				        LSW.LpuSectionWard_setDate <= tzgetdate() and (LSW.LpuSectionWard_disDate >= tzgetdate() or LSW.LpuSectionWard_disDate is null) and
						LSW.LpuSection_id=LS.LpuSection_id
				) as LSW on true
				left join lateral (
					select
						sum(LpuSectionBedState_Plan) as LpuSectionBedState_PlanCount,
						sum(case when LSBS.LpuSectionProfile_id = LSS.LpuSectionProfile_id then LpuSectionBedState_Plan else 0 end) as LpuSectionBedState_ProfileCount,
						sum(case when LSBS.LpuSectionProfile_id != LSS.LpuSectionProfile_id then LpuSectionBedState_Plan else 0 end) as LpuSectionBedState_UzCount,
						sum(LpuSectionBedState_Fact) as LpuSectionBedState_Fact
					from
					    v_LpuSectionBedState LSBS 
					    left join v_LpuSection LSS  on LSS.LpuSection_id = LSBS.LpuSection_id
					where
						LSBS.LpuSectionBedState_begDate <= tzgetdate() and (LSBS.LpuSectionBedState_endDate >= tzgetdate() or LSBS.LpuSectionBedState_endDate is null) and
						LSBS.LpuSection_id=LS.LpuSection_id
				) as LSBS on true
			where LpuSection_id=:LpuSection_id
			group by LpuSection_MaxEmergencyBed
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuSectionProfileforCombo(LpuStructure_model $callObject, $data)
	{
		$query = "
			select
				LSP.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				LSP.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
			from
				v_LpuSectionProfile LSP 
				inner join v_LpuSection LpuSection on LpuSection.LpuSectionProfile_id = LSP.LpuSectionProfile_id
			where 
				( LpuSection.LpuSection_id = :LpuSection_id or LpuSection.LpuSection_pid = :LpuSection_id )
				and ( LSP.LpuSectionProfile_endDT is null or LSP.LpuSectionProfile_endDT > dbo.tzGetDate() )
			union
			select
				lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				lsp.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
			from
			    dbo.v_LpuSectionLpuSectionProfile lslsp 
				inner join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = lslsp.LpuSectionProfile_id
			where 
				lslsp.LpuSection_id = :LpuSection_id 
				and ( LSP.LpuSectionProfile_endDT is null or LSP.LpuSectionProfile_endDT > dbo.tzGetDate() )
			order by \"LpuSectionProfile_Code\"
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuSectionBedProfileforCombo(LpuStructure_model $callObject, $data)
	{
		$query = "
			select
				LSBP.LpuSectionBedProfile_id as \"LpuSectionBedProfile_id\",
				LSBP.LpuSectionBedProfile_Code as \"LpuSectionBedProfile_Code\",
				LSBP.LpuSectionBedProfile_Name as \"LpuSectionBedProfile_Name\"
			from
				v_LpuSectionBedProfile LSBP 
				inner join v_LpuSection LpuSection  on LpuSection.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
			where LpuSection.LpuSection_pid = :LpuSection_id -- только подотделений
			order by LpuSectionBedProfile_Code
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuSectionWardByIdData(LpuStructure_model $callObject, $data)
	{
		if (!isset($data['LpuSectionWard_id'])) {
			return false;
		}
		$query = "
			select 
				 LSW.LpuSectionWard_id as \"LpuSectionWard_id\"
				,LSW.LpuSectionWard_isAct as \"LpuSectionWard_isAct\"
				,LSW.LpuSection_id as \"LpuSection_id\"
				,LSW.LpuSectionWard_Name as \"LpuSectionWard_Name\"
				,LSW.LpuSectionWard_Floor as \"LpuSectionWard_Floor\"
				,LSW.LpuWardType_id as \"LpuWardType_id\"
				,LSW.LpuSectionWard_BedCount as \"LpuSectionWard_BedCount\"
				,LSW.LpuSectionWard_BedRepair as \"LpuSectionWard_BedRepair\"
				,LSW.LpuSectionWard_DayCost as \"LpuSectionWard_DayCost\"
				,LSW.LpuSectionWard_setDate as \"LpuSectionWard_setDate\"
				,LSW.LpuSectionWard_disDate as \"LpuSectionWard_disDate\"
				,LS.Lpu_id as \"Lpu_id\"
			from
				v_LpuSectionWard LSW 
				left join v_LpuSection LS  on LS.LpuSection_id = LSW.LpuSection_id
			where LSW.LpuSectionWard_id = :LpuSectionWard_id
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
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuSectionData(LpuStructure_model $callObject, $data)
	{
		$query = "
			select 
				LS.Lpu_id as \"Lpu_id\",
				LU.LpuBuilding_id as \"LpuBuilding_id\",
				LS.LpuUnit_id as \"LpuUnit_id\",
				LS.LpuSection_id as \"LpuSection_id\"
			from
				v_LpuSection LS 
				join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id
			where LS.LpuSection_id = :LpuSection_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		return ["data" => $result[0]];
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $query2export
	 * @return array|bool
	 */
	public static function getExp2DbfData(LpuStructure_model $callObject, $query2export)
	{
		$queries = [
			"REG_FOND" => [
				"
					select
					    distinct
					    l.Lpu_OKATO as \"TF_OKATO\",
					    l.Lpu_OGRN as \"C_OGRN\",
					    l.Lpu_Ouzold as \"LCOD\",
					    null as \"TYPE\",
					    right('000000000000'||wp.id::varchar , 12) as \"PCOD\",
					    p.Person_Surname as \"FAM_V\",
					    p.Person_Firname as \"IM_V\",
					    p.Person_Secname as \"OT_V\",
					    (select substring(Sex_Name, 1, 1) from v_sex where Sex_id = personState.Sex_id) as \"W\",
					    p.Person_BirthDay as \"DR\",
					    substring(p.Person_Snils, 1, 3)||'-'||substring(p.Person_Snils, 4, 3)||'-'||substring(p.Person_Snils, 7, 3)||' '||substring(p.Person_Snils, 10, 2) as \"SS\",
					    wp.Dolgnost_Code as \"PRVD\",
					    p.WorkData_begDate as \"D_PR\",
					    qic.DocumentRecieveDate as \"D_SER\",
					    s.spec_code as \"PRVS\",
					    coalesce(qc.Category_id, 0) as \"KV_KAT\",
					    extract(year from qc.AssigmentDate) as \"YEAR_KAT\",
					    wp.DLOBeginDate as \"DATE_B\",
					    null as \"DATE_E\",
					    CAST(wp.rate AS VARCHAR(6)) as \"STAVKA\",
					    '' as \"MSG_TEXT\",
					    p.WorkData_begDate as \"DATE_P\",
					    wp.Population as \"PRIKREP\",
					    (select sl.LpuSubjectionLevel_Name from v_LpuSubjectionLevel sl where sl.LpuSubjectionLevel_id = l.LpuSubjectionLevel_id) as \"VEDOM_P\"
					from
					    v_MedPersonal p
					    left join v_Lpu l ON L.Lpu_id = p.Lpu_id
					    left join v_PersonState personState ON p.Person_id = personState.Person_id
					    left join lateral (
					        select Speciality_id
					        from persis.Certificate c
					        where c.MedWorker_id = p.MedPersonal_id
					        order by CertificateReceipDate desc
					        limit 1
					    ) as c on true
					    left join lateral (
					        select spec.spec_code
					        from
					            persis.SpecialityDiploma s
					            inner join persis.DiplomaSpeciality d  ON s.DiplomaSpeciality_id = d.id
					            inner join tmp.spec spec  on spec.spec_id = d.id
					        where s.MedWorker_id = p.MedPersonal_id
					        limit 1
					    ) as s on true
					    left join lateral (
					        select qic.DocumentRecieveDate
					        from persis.QualificationImprovementCourse qic
					        where qic.MedWorker_id = p.MedPersonal_id
					        order by qic.DocumentRecieveDate desc
					        limit 1
					    ) as qic on true
					    left join lateral (
					        select
					            qc.Category_id,
					            qc.AssigmentDate
					        from persis.QualificationCategory qc
					        Where qc.MedWorker_id = p.MedPersonal_id
					        limit 1
					    ) as qc on true
					    inner join lateral (
					        select
					            wp.dlobegindate,
					            wp.rate,
					            wp.population,
					            wp.id,
					            post.Dolgnost_Code
					        from
					            persis.WorkPlace wp
					            inner join persis.v_staff s on s.id = wp.Staff_id and s.Lpu_id = p.Lpu_id
					            left join persis.post pp on pp.id = s.Post_id
					            left join tmp.postnew post on post.ID_NEW = pp.id
					        where wp.MedWorker_id = p.MedPersonal_id
					          and wp.enddate is null
					          and wp.rate > 0
					          and PrimaryHealthCare = 1
					        order by wp.PostOccupationType_id, wp.BeginDate, wp.id
					        limit 1
					    ) as wp on true
					order by \"FAM_V\", \"IM_V\", \"OT_V\"
				"
			],
			"REG_FOND_NEW" => [
				"
					select
					    distinct
					    l.Lpu_OKATO as \"TF_OKATO\",
					    l.Lpu_OGRN as \"C_OGRN\",
					    l.Lpu_f003mcod as \"MCOD\",
					    null as \"TYPE\",
					    right('000000000000'||wp.id::varchar, 12) as \"PCOD\",
					    p.Person_Surname as \"FAM_V\",
					    p.Person_Firname as \"IM_V\",
					    p.Person_Secname as \"OT_V\",
					    (select substring(Sex_Name, 1, 1) from v_sex where Sex_id = personState.Sex_id) as \"W\",
					    p.Person_BirthDay as \"DR\",
					    substring(p.Person_Snils, 1, 3)||'-'||substring(p.Person_Snils, 4, 3)||'-'||substring(p.Person_Snils, 7, 3)||' '||substring(p.Person_Snils, 10, 2) as \"SS\",
					    wp.frmpEntry_id as \"PRVD\",
					    p.WorkData_begDate as \"D_PR\",
					    qic.DocumentRecieveDate as \"D_SER\",
					    s.code as \"PRVS\",
					    coalesce(qc.Category_id, 0) as \"KV_KAT\",
					    extract(year from qc.AssigmentDate) as \"YEAR_KAT\",
					    wp.DLOBeginDate as \"DATE_B\",
					    null as \"DATE_E\",
					    'НЕТ ПРИМЕЧАНИЙ' as \"MSG_TEXT\",
					    p.WorkData_begDate as \"DATE_P\",
					    wp.Population as \"PRIKREP\",
					    (select sl.LpuSubjectionLevel_Name from v_LpuSubjectionLevel sl where sl.LpuSubjectionLevel_id = l.LpuSubjectionLevel_id) as \"VEDOM_P\"
					from
					    v_MedPersonal p
					    LEFT JOIN v_Lpu l ON L.Lpu_id = p.Lpu_id
					    LEFT JOIN v_PersonState personState ON p.Person_id = personState.Person_id
					    left join lateral (
					        select Speciality_id
					        from persis.Certificate c
					        where c.MedWorker_id = p.MedPersonal_id
					        order by CertificateReceipDate desc
					        limit 1
					    ) as c on true
					    left join lateral (
					        select d.code
					        from
					            persis.SpecialityDiploma s
					            inner join persis.DiplomaSpeciality d ON s.DiplomaSpeciality_id = d.id
					        where s.MedWorker_id = p.MedPersonal_id
					        limit 1
					    ) as s  on true
					    left join lateral (
					        select qic.DocumentRecieveDate
					        from persis.QualificationImprovementCourse qic
					        where qic.MedWorker_id = p.MedPersonal_id
					        order by qic.DocumentRecieveDate desc
					        limit 1
					    ) as qic  on true
					    left join lateral (
					        select
					            qc.Category_id,
					            qc.AssigmentDate
					        from persis.QualificationCategory qc
					        where qc.MedWorker_id = p.MedPersonal_id
					        limit 1
					    ) as qc  on true
					    inner join lateral (
					        select
					            wp.dlobegindate,
					            wp.rate,
					            wp.population,
					            wp.id,
					            pp.frmpEntry_id AS frmpEntry_id
					        from
					            persis.WorkPlace wp
					            inner join persis.v_staff s on s.id = wp.Staff_id and s.Lpu_id = p.Lpu_id
					            left join persis.post pp on pp.id = s.Post_id
					        where wp.MedWorker_id = p.MedPersonal_id
					          and wp.enddate is null
					          and wp.rate > 0
					          and PrimaryHealthCare = 1
					        order by wp.PostOccupationType_id, wp.BeginDate, wp.id
					        limit 1
					    ) as wp on true
					order by \"FAM_V\", \"IM_V\", \"OT_V\"
				"
			],
			"LPU_Q" => [
				"
					select
					    l.Lpu_Ouz as \"LPU_OUZ\",
					    coalesce(l.Lpu_OuzOld, l.Lpu_Ouz::varchar) as \"MCOD\",
					    l.Lpu_OKATO as \"TF_OKATO\",
					    l.Lpu_OGRN as \"C_OGRN\",
					    l.Lpu_Nick as \"M_NAMES\",
					    l.Lpu_Name as \"M_NAMEF\",
					    (select address_zip from v_Address a where a.Address_id = l.UAddress_id) as \"POST_ID\",
					    l.UAddress_Address as \"ADRES\",
					    gv.Person_SurName as \"FAM_GV\",
					    gv.Person_FirName as \"IM_GV\",
					    gv.Person_SecName as \"OT_GV\",
					    gb.Person_SurName as \"FAM_BUX\",
					    gb.Person_FirName as \"IM_BUX\",
					    gb.Person_SecName as \"OT_BUX\",
					    l.Org_Phone as \"TEL\",
					    gv.OrgHead_Fax as \"FAX\",
					    l.Org_Email as \"E_MAIL\",
					    LPD.LpuPeriodDLO_begDate as \"DATE_B\",
					    LPD.LpuPeriodDLO_endDate as \"DATE_E\",
					    substring(l.Lpu_Ouz::varchar, 3, 2) as \"KOD_TER\",
					    right(l.Lpu_Ouz::varchar, 3) as \"KOD_LPU\",
					    l.Lpu_Ouz as \"S_LR_LPU\"
					from
					    (
					        select
					            l1.* ,
					            o.Org_Phone,
					            o.Org_Email
					        from
					            v_lpu l1
					            left outer join v_org o ON o.Org_id = l1.org_id
					    ) AS l
					    left join lateral (
					        select
					            ps.Person_SurName,
					            ps.Person_FirName,
					            ps.Person_SecName,
					            oh.Lpu_id,
					            oh.OrgHead_Fax
					        from
					            OrgHead OH
					            inner join v_PersonState PS on PS.Person_id = OH.Person_id
					        where OH.Lpu_id = l.Lpu_id
					          and OH.LpuUnit_id is null
					          and OH.OrgHeadPost_id = 1
					        limit 1
					    ) as gv on true
					    left join lateral (
					        select
					            ps.Person_SurName,
					            ps.Person_FirName,
					            ps.Person_SecName,
					            OH.Lpu_id
					        from
					            OrgHead OH
					            inner join v_PersonState PS on PS.Person_id = OH.Person_id
					        where OH.Lpu_id = l.Lpu_id
					          and OH.LpuUnit_id is null
					          and OH.OrgHeadPost_id = 2
					        limit 1
					    ) as gb on true
					    inner join LpuPeriodDLO LPD on LPD.Lpu_id = l.Lpu_id
					where l.Lpu_id not in (13002457, 101) -- Минздрав исключаем и тестовую МО
				"
			],
			"SVF_Q" => [
				"
					select
					    distinct
					    l.Lpu_OKATO as \"TF_OKATO\",
					    l.Lpu_Ouz as \"MCOD\",
					    l.Lpu_OGRN||' '||right(repeat('0', 10)||p.MedPersonal_Code, 6) as \"PCOD\",
					    coalesce(rtrim(p.Person_SurName), '') as \"FAM_V\",
					    coalesce(rtrim(p.Person_FirName), '') as \"IM_V\",
					    coalesce(rtrim(p.Person_SecName), '') as \"OT_V\",
					    l.Lpu_OGRN as \"C_OGRN\",
					    coalesce(rtrim(wp.Dolgnost_Code::varchar), '') as \"PRVD\",
					    coalesce(rtrim(wp.Dolgnost_Name), '') as \"D_JOB\",
					    p.WorkData_begDate as \"D_PRIK\",
					    qic.DocumentRecieveDate as \"D_SER\",
					    c.Speciality_id as \"PRVS\",
					    coalesce(qc.Category_id, 0) as \"KV_KAT\",
					    wp.DLOBeginDate as \"DATE_B\",
					    wp.DLOEndDate as \"DATE_E\",
					    'НЕТ ПРИМЕЧАНИЙ' as \"MSG_TEXT\",
					    substring(L.Lpu_Ouz::varchar, 3, 2) as \"KOD_TER\",
					    right(L.Lpu_Ouz::varchar, 3) as \"KOD_LPU\"
					FROM
					    dbo.v_MedPersonal p
					    left join (
					        SELECT *
					        FROM v_lpu l1
					        WHERE exists(
					            select LpuPeriodDLO_id
					            from LpuPeriodDLO LPD
					            where LPD.Lpu_id = l1.Lpu_id
					              and LPD.LpuPeriodDLO_begDate <= tzgetdate()
					              and (LPD.LpuPeriodDLO_endDate >= tzgetdate() or LPD.LpuPeriodDLO_endDate is null)
					        )
					    ) l ON l.Lpu_id = p.lpu_id
					    inner join lateral (
					        select
					            t1.DLOBeginDate,
					            t1.dloEndDate,
					            t1.Descr,
					            t2.Code as Dolgnost_Code,
					            t2.name as Dolgnost_Name
					        from
					            persis.v_WorkPlace t1
					            inner join persis.Post t2  on t2.id = t1.Post_id
					        where t1.MedWorker_id = p.MedPersonal_id
					          and t1.Lpu_id = p.Lpu_id
					          and t1.DLOBeginDate is not null
					          and t1.DLOBeginDate <= dbo.tzGetDate()
					          and (coalesce(t1.EndDate,tzgetdate()) + (90||' days')::interval) > tzgetdate()
					        order by t1.PostOccupationType_id, t1.EndDate
					        limit 1
					    ) as wp on true
					    left join lateral (
					        select qic.DocumentRecieveDate
					        from persis.QualificationImprovementCourse qic
					        where qic.MedWorker_id = p.MedPersonal_id
					        order by qic.DocumentRecieveDate desc
					        limit 1
					    ) as qic on true
					    left join lateral (
					        select Speciality_id
					        from persis.Certificate c
					        where c.MedWorker_id = p.MedPersonal_id
					        order by CertificateReceipDate desc
					        limit 1
					    ) as c on true
					    left join lateral (
					        select Category_id
					        from persis.QualificationCategory qc
					        where qc.MedWorker_id = p.MedPersonal_id
					        order by AssigmentDate desc
					        limit 1
					    ) as qc on true
					where p.MedPersonal_Code IS NOT NULL
					  and p.MedPersonal_Code is not null
					  and p.MedPersonal_Code != '0'
					  and l.Lpu_id is not null
					  and l.Lpu_id not in (13002457, 101) -- Минздрав исключаем и тестовую МО
					ORDER BY \"FAM_V\", \"IM_V\", \"OT_V\"
        		"
			],
			"SVF_Q_2" => [
				"
					select
					    distinct
					    l.Lpu_OKATO as \"TF_OKATO\",
					    right(repeat('0', 6)||p.MedPersonal_Code::varchar, 6) as \"SCOD\",
					    l.Lpu_Ouz as \"MCOD\",
					    l.Lpu_OGRN||' '||right(repeat('0', 10)||p.MedPersonal_Code::varchar, 6) as \"PCOD\",
					    coalesce(rtrim(p.Person_SurName), '') as \"FAM_V\",
					    coalesce(rtrim(p.Person_FirName), '') as \"IM_V\",
					    coalesce(rtrim(p.Person_SecName), '') as \"OT_V\",
					    l.Lpu_OGRN as \"C_OGRN\",
					    coalesce(rtrim(wp.Dolgnost_Code::varchar), '') as \"PRVD\",
					    coalesce(rtrim(wp.Dolgnost_Name), '') as \"D_JOB\",
					    p.WorkData_begDate as \"D_PRIK\",
					    qic.DocumentRecieveDate as \"D_SER\",
					    c.Speciality_id as \"PRVS\",
					    coalesce(qc.Category_id, 0) as \"KV_KAT\",
					    wp.DLOBeginDate as \"DATE_B\",
					    wp.DLOEndDate as \"DATE_E\",
					    'НЕТ ПРИМЕЧАНИЙ' as \"MSG_TEXT\",
					    substring(L.Lpu_Ouz::varchar, 3, 2) as \"KOD_TER\",
					    RIGHT(L.Lpu_Ouz::varchar, 3) as \"KOD_LPU\"
					from
					    dbo.v_MedPersonal p
					    inner join (
					        select *
					        from v_lpu l1
					        where exists(
					            select LpuPeriodDLO_id
					            from LpuPeriodDLO LPD
					            where LPD.Lpu_id = l1.Lpu_id
					              and LPD.LpuPeriodDLO_begDate <= tzgetdate()
					              and (LPD.LpuPeriodDLO_endDate >= tzgetdate() or LPD.LpuPeriodDLO_endDate is null)
					            )
					    ) l ON l.Lpu_id = p.lpu_id
					    inner join lateral (
					        select
					            t1.DLOBeginDate,
					            t1.dloEndDate,
					            t1.Descr,
					            t2.Code as Dolgnost_Code,
					            t2.name as Dolgnost_Name
					        from
					            persis.v_WorkPlace t1
					            inner join persis.Post t2 on t2.id = t1.Post_id
					        where t1.MedWorker_id = p.MedPersonal_id
					          and coalesce(t1.Rate, 0) > 0
					          and t1.Lpu_id = p.Lpu_id
					          and t1.DLOBeginDate is not null
					          and t1.DLOBeginDate <= tzgetdate()
					          and (coalesce(t1.EndDate, tzgetdate()) + (90||' days')::interval) > tzgetdate()
					        order by t1.EndDate
					        limit 1
					    ) as wp on true
					    left join lateral (
					        select qic.DocumentRecieveDate
					        from persis.QualificationImprovementCourse qic
					        where qic.MedWorker_id = p.MedPersonal_id
					        order by qic.DocumentRecieveDate desc
					        limit 1
					    ) as qic on true
					    left join lateral (
					        select Speciality_id
					        from persis.Certificate c
					        where c.MedWorker_id = p.MedPersonal_id
					        order by CertificateReceipDate desc
					        limit 1
					    ) as c on true
					    left join lateral (
					        select Category_id
					        from persis.QualificationCategory qc
					        where qc.MedWorker_id = p.MedPersonal_id
					        order by AssigmentDate desc
					        limit 1
					    ) as qc on true
					where p.MedPersonal_Code is not null
					  and p.MedPersonal_Code != '0'
					  and l.Lpu_id is not null
					  and l.Lpu_id not in (13002457, 101) -- Минздрав исключаем и тестовую МО
					order by \"FAM_V\", \"IM_V\", \"OT_V\"
				"
			]
		];
		$query = $queries[$query2export][0];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	public static function getAllLpuNotFRMP(LpuStructure_model $callObject, $data)
	{
		$query = "
			select
			    l.Lpu_Name as \"M_NAMEF\",
			    l.Lpu_Nick as \"M_NAMES\",
			    l.Org_INN as \"LPU_INN\",
			    l.Org_KPP as \"LPU_KPP\",
			    l.Lpu_OGRN as \"C_OGRN\",
			    to_char(l.Lpu_begDate::date, '{$callObject->dateTimeForm104}') as \"B_DATE\"
			from
			    (
			        select
			            l1.Lpu_id,
			            l1.Lpu_Nick,
			            l1.Lpu_OGRN,
			            l1.Lpu_Name,
			            l1.Lpu_begDate,
			            o.Org_INN,
			            o.Org_KPP
			        from
			            v_lpu l1
			            left outer join v_org o on o.Org_id = l1.org_id
			        where l1.Lpu_endDate is null
			          and l1.Lpu_Nick not ilike '%закрыт%'
			          and l1.Lpu_Nick not ilike '%тест%'
			    ) as l
			where l.Lpu_id in (
			        select lpu_id
			        from
			            persis.v_staff s
			            inner join persis.post p on p.id = s.Post_id
			            inner join persis.postkind pk on p.postkind_id = pk.id and pk.id in (1, 3, 4, 6, 8)
			        where s.Rate > 0
			          and s.BeginDate <= tzgetdate()
			          and (s.EndDate >= tzgetdate() or s.EndDate is null)
			    )
			    and l.Lpu_id in (
			        select lpu_id
			        from
			            persis.v_staff s
			            inner join persis.WorkPlace w on w.Staff_id = s.id
			        where w.Rate >= 0.2
			          and w.Rate <= 3
			          and w.BeginDate <= tzgetdate()
			          and (w.EndDate >= tzgetdate() or w.EndDate is null)
			    )
			order by l.Org_KPP
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		foreach ($result as $k => $r) {
			foreach ($data as $d) {
				if (
					$d[1] == $r["LPU_INN"] && // ИНН
					$d[2] == $r["LPU_KPP"] && // КПП
					$d[3] == $r["C_OGRN"] // ОГРН
				)
					unset($result[$k]);
			}
		}
		return $result;
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return bool|mixed
	 */
	public static function getLpuSectionInfoForReg(LpuStructure_model $callObject, $data)
	{
		$sql = "
			SELECT
				rtrim(ls.LpuSection_Descr) as \"LpuSection_Descr\",
				ls.LpuSection_id as \"LpuSection_id\",
				ls.LpuSection_Name as \"LpuSection_Name\",
				ls.LpuSection_updDT as \"LpuSection_updDT\",
				rtrim(u.pmUser_Name) as \"pmUser_Name\",
				lp.Org_id as \"Org_id\",
				lu.LpuUnit_id as \"LpuUnit_id\",
				lu.LpuUnit_Name as \"LpuUnit_Name\",
				LS.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				LS.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				lp.Lpu_id as \"Lpu_id\",
				lp.Lpu_Nick as \"Lpu_Nick\"
			from
				v_LpuSection LS 
				left join v_LpuUnit lu on LS.LpuUnit_id = lu.LpuUnit_id
				left join v_pmUser u on u.pmUser_id = ls.pmUser_updID
				left join v_lpu_all lp on LS.Lpu_id = lp.Lpu_id
			where LpuSection_id = :LpuSection_id
		";
		$sqlParams = [
			'LpuSection_id' => $data['LpuSection_id']
		];
		/**
		 * @var CI_DB_result $result
		 *@var array $res
		 */
		$result = $callObject->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		$res = $result->result("array");
		return $res[0];
	}

	/**
	 * @param LpuStructure_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuSectionComment(LpuStructure_model $callObject, $data)
	{
		$sql = "
			SELECT
				rtrim(ls.LpuSection_Descr) as \"LpuSection_Descr\",
				ls.LpuSection_updDT as \"LpuSection_updDT\",
				rtrim(u.pmUser_Name) as \"pmUser_Name\"
			from
				v_LpuSection LS 
				left join v_pmUser u  on u.pmUser_id = ls.pmUser_updID
				left join v_lpu_all lp  on LS.Lpu_id = lp.Lpu_id
			where LpuSection_id = :LpuSection_id
		";
		$sqlParams = [
			'LpuSection_id' => $data['LpuSection_id']
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}



	//LpuStructure_model $callObject, 
}