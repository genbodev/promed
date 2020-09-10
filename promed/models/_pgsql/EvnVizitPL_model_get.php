<?php

class EvnVizitPL_model_get
{
	/**
	 * @param EvnVizitPL_model $callObject
	 * @return bool|float|int|string|null
	 * @throws Exception
	 */
	public static function getVizitTypeSysNick(EvnVizitPL_model $callObject)
	{
		if (empty($callObject->VizitType_id)) {
			return null;
		}
		if (empty($callObject->_vizitTypeSysNick)) {
			$query = "
				select VizitType_SysNick as \"VizitType_SysNick\"
				from v_VizitType
				where VizitType_id = :VizitType_id
			";
			$queryParams = ["VizitType_id" => $callObject->VizitType_id];
			$callObject->_vizitTypeSysNick = $callObject->getFirstResultFromQuery($query, $queryParams);
			if (empty($callObject->_vizitTypeSysNick)) {
				throw new Exception("Ошибка при получении типа цели посещения", 500);
			}
		}
		return $callObject->_vizitTypeSysNick;
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @return bool|float|int|string|null
	 * @throws Exception
	 */
	public static function getServiceTypeSysNick(EvnVizitPL_model $callObject)
	{
		if (empty($callObject->ServiceType_id)) {
			return null;
		}
		if (empty($callObject->_serviceTypeSysNick)) {
			$query = "
				select ServiceType_SysNick as \"ServiceType_SysNick\"
				from v_ServiceType
				where ServiceType_id = :ServiceType_id
			";
			$queryParams = ["ServiceType_id" => $callObject->ServiceType_id];
			$callObject->_serviceTypeSysNick = $callObject->getFirstResultFromQuery($query, $queryParams);
			if (empty($callObject->_serviceTypeSysNick)) {
				throw new Exception("Ошибка при получении типа места посещения", 500);
			}
		}
		return $callObject->_serviceTypeSysNick;
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @return array|bool
	 * @throws Exception
	 */
	public static function getLpuSectionData(EvnVizitPL_model $callObject)
	{
		if (empty($callObject->LpuSection_id)) {
			$callObject->_LpuSectionData = [];
		}
		if (!empty($callObject->_LpuSectionData["LpuSection_id"]) && $callObject->_LpuSectionData["LpuSection_id"] != $callObject->LpuSection_id) {
			$callObject->_LpuSectionData = [];
		}
		if (empty($callObject->_LpuSectionData) && !empty($callObject->LpuSection_id)) {
			$query = "
				select
					LS.LpuSection_id as \"LpuSection_id\",
					LS.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					LSP.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
					LS.LpuSectionAge_id as \"LpuSectionAge_id\",
					LUT.LpuUnitType_SysNick as \"LpuUnitType_SysNick\"
				from
					v_LpuSection LS
					left join v_LpuSectionProfile LSP on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
					left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
					left join v_LpuUnitType LUT on LUT.LpuUnitType_id = LU.LpuUnitType_id
				where LS.LpuSection_id = :LpuSection_id
				limit 1
			";
			$queryParams = ["LpuSection_id" => $callObject->LpuSection_id];
			$callObject->_LpuSectionData = $callObject->getFirstRowFromQuery($query, $queryParams);
			if (empty($callObject->_LpuSectionData)) {
				throw new Exception("Ошибка при получении данных отделения", 500);
			}
		}
		return $callObject->_LpuSectionData;
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @return array
	 * @throws Exception
	 */
	public static function getEvnUslugaList(EvnVizitPL_model $callObject)
	{
		if (!isset($callObject->_evnUslugaList)) {
			$selectIsVizitCode = "coalesce(eu.EvnUsluga_IsVizitCode, 1) as \"EvnUsluga_IsVizitCode\"";
			$add_join = "";
			if ($callObject->regionNick == "ufa") {
				// для Уфы правильнее определять услугу посещения по ucat.UslugaCategory_SysNick,
				// т.к. eu.EvnUsluga_IsVizitCode появился позднее
				$add_join = "left join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id";
				$selectIsVizitCode = "case
					when ucat.UslugaCategory_SysNick = 'lpusection'
						then 2
						else 1
					end as \"EvnUsluga_IsVizitCode\"";
			}
			$selectString = "
				eu.EvnUsluga_id as \"EvnUsluga_id\",
				eu.UslugaComplex_id as \"UslugaComplex_id\",
				uc.UslugaComplex_Code as \"UslugaComplex_Code\",
				{$selectIsVizitCode}
			";
			$fromString = "
				v_EvnUsluga eu
				left join v_UslugaComplex uc on uc.UslugaComplex_id = eu.UslugaComplex_id
				{$add_join}
			";
			$whereString = "eu.EvnUsluga_pid = :id";
			$query = "
				select {$selectString}
				from {$fromString}
				where {$whereString}
			";
			$queryParams = ["id" => $callObject->id];
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($query, $queryParams);
			if (!is_object($result)) {
				throw new Exception("Ошибка при чтении услуг посещения", 500);
			}
			$callObject->_evnUslugaList = $result->result("array");
		}
		return $callObject->_evnUslugaList;
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @return int
	 */
	public static function getLpuUnitSetCode(EvnVizitPL_model $callObject)
	{
		if (!isset($callObject->_lpuUnitSetCode) && !empty($callObject->UslugaComplex_id)) {
			// 1) получем код сохраняемого посещения
			$query = "
				select lu.LpuUnitSet_Code as \"LpuUnitSet_Code\"
				from
					v_LpuSection ls
					left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
				where ls.LpuSection_id = :LpuSection_id
				limit 1
			";
			$queryParams = ["LpuSection_id" => $callObject->LpuSection_id];
			$callObject->_lpuUnitSetCode = (int)$callObject->getFirstResultFromQuery($query, $queryParams);
		}
		return $callObject->_lpuUnitSetCode;
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @return bool
	 */
	public static function getIsUseVizitCode(EvnVizitPL_model $callObject)
	{
		return in_array($callObject->regionNick, ["ufa", "pskov", "ekb", "buryatiya", "kz", "perm", "vologda"]);
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @return bool|float|int|string
	 * @throws Exception
	 */
	public static function getVizitCode(EvnVizitPL_model $callObject)
	{
		if (empty($callObject->_vizitCode) && !empty($callObject->UslugaComplex_id) && $callObject->isUseVizitCode) {
			// 1) получем код сохраняемого посещения
			$query = "
				select uc.UslugaComplex_Code as \"UslugaComplex_Code\"
				from v_UslugaComplex uc
				where uc.UslugaComplex_id = :UslugaComplex_id
				limit 1
			";
			$queryParams = ["UslugaComplex_id" => $callObject->UslugaComplex_id];
			$callObject->_vizitCode = $callObject->getFirstResultFromQuery($query, $queryParams);
			if (empty($callObject->_vizitCode)) {
				throw new Exception("Ошибка при получении кода посещения");
			}
			if ($callObject->regionNick == "ufa" && !in_array(mb_strlen($callObject->_vizitCode, "utf-8"), [6, 7])) {
				throw new Exception("Недопустимый код посещения");
			}
		}
		return $callObject->_vizitCode;
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLastEvnVizitPL(EvnVizitPL_model $callObject, $data)
	{
		$filters = [];
		$params = [];
		if (!empty($data["LpuSection_id"])) {
			$filters[] = "LpuSection_id = :LpuSection_id";
			$params["LpuSection_id"] = $data["LpuSection_id"];
		}
		if (!empty($data["Person_id"])) {
			$filters[] = "Person_id = :Person_id";
			$params["Person_id"] = $data["Person_id"];
		}
		$whereString = (count($filters) != 0) ? "where " . implode(" and ", $filters) : "";
		$query = "
			select EvnVizitPL_id as \"EvnVizitPL_id\"
			from v_EvnVizitPL
			{$whereString}
			order by EvnVizitPL_setDT desc
			limit 1
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @return array
	 * @throws Exception
	 */
	public static function getEvnVizitPLDoubles(EvnVizitPL_model $callObject)
	{
		$doubles = $callObject->_getEvnVizitPLDoubles();
		$firstDoubleEdit = true;
		$doublesEvnPL = [];
		if (strtotime($callObject->setDate) >= strtotime("01.06.2017")) {
			foreach ($doubles as $double) {
				if ($double["EvnVizitPL_pid"] == $callObject->pid) {
					$doublesEvnPL[] = $double;
					if (!empty($double["VizitPLDouble_id"])) {
						$firstDoubleEdit = false;
					}
				}
			}
		}
		if (!empty($callObject->VizitPLDouble_id)) {
			$firstDoubleEdit = false;
		}
		if ($firstDoubleEdit) {
			$firstDouble = true;
			foreach ($doublesEvnPL as $key => $double) {
				$doublesEvnPL[$key]['VizitPLDouble_id'] = ($firstDouble) ? 1 : 2;
				$firstDouble = false;
			}
		}
		// Для посещений с датой после 01.06.2017 если дублирующее посещения находится в той же ТАП
		// вместо предупреждения выводится форма с таблицей, в которой отображаются все такие посещения, включая текущее.
		$doublesEvnPL[] = [
			"EvnVizitPL_id" => !empty($callObject->id) ? $callObject->id : -1,
			"LpuSection_Name" => $callObject->getFirstResultFromQuery("select LpuSection_Name as \"LpuSection_Name\" from v_LpuSection where LpuSection_id = :LpuSection_id", ["LpuSection_id" => $callObject->LpuSection_id]),
			"MedPersonal_Fio" => $callObject->getFirstResultFromQuery("select Person_Fio as \"Person_Fio\" from v_MedStaffFact where MedStaffFact_id = :MedStaffFact_id", ["MedStaffFact_id" => $callObject->MedStaffFact_id]),
			"EvnVizitPL_setDate" => date("d.m.Y", strtotime($callObject->setDate)),
			"EvnVizitPL_pid" => $callObject->pid,
			"VizitPLDouble_id" => $firstDoubleEdit ? 2 : $callObject->VizitPLDouble_id,
			"accessType" => "edit"
		];
		return $doublesEvnPL;
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getDataForDispPrint(EvnVizitPL_model $callObject, $data)
	{
		$query = "
			select
				TC.TreatmentClass_Code as \"TreatmentClass_Code\",
				VT.VizitType_Code as \"VizitType_Code\"
			from
				v_EvnVizitPL EVPL
				left join v_TreatmentClass TC on TC.TreatmentClass_id = EVPL.TreatmentClass_id
				left join v_VizitType VT on VT.VizitType_id = EVPL.VizitType_id
			where
				EVPL.EvnVizitPL_id = :EvnVizitPL_id
			limit 1
		";
		$queryParams = ["EvnVizitPL_id" => $data["EvnVizitPL_id"]];
		return $callObject->queryResult($query, $queryParams);
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	public static function _getEvnVizitPLOldDoubles(EvnVizitPL_model $callObject, $data)
	{
		$add_where = $fadd_field = "";
		if (empty($data["EvnVizitPL_id"])) {
			return [];
		}
		$parentAlias = "EvnPL";
		// Получаем значения дублей
		$query = "
			select 
				{$callObject->tableName()}_id as \"EvnVizitPL_id\",
				Lpu_id as \"Lpu_id\",
				Person_id as \"Person_id\",
				to_char({$callObject->tableName()}_setDate, 'yyyy-mm-dd') as \"setDate\",
				MedStaffFact_id as \"MedStaffFact_id\",
				LpuSectionProfile_id as \"LpuSectionProfile_id\",
				PayType_id as \"PayType_id\"
			from v_{$callObject->tableName()} EVPL
			where EVPL.{$callObject->tableName()}_id = :EvnVizitPL_id
			limit 1
		";
		$queryParams = ["EvnVizitPL_id" => $data["EvnVizitPL_id"]];
		$result = $callObject->queryResult($query, $queryParams);
		if (empty($result[0]["EvnVizitPL_id"])) {
			throw new Exception("Ошибка получения данных по случаю");
		}
		$params = $result[0];
		if ($callObject->evnClassId == 13) {
			$parentAlias = "EvnPLStom";
			if (!empty($result[0]["Tooth_id"])) {
				$add_where .= " and :Tooth_id = EVPLD.Tooth_id";
			} else {
				$add_where .= " and EVPLD.Tooth_id is null";
			}
		}
		$join = "";
		if (getRegionNick() == "ufa") {
			$msfd_filter = " and MSF.LpuSectionProfile_id = MSFD.LpuSectionProfile_id and MSF.MedPersonal_id = MSFD.MedPersonal_id";
		} else {
			$msfd_filter = " and MSF.MedSpecOms_id = MSFD.MedSpecOms_id";
			if (getRegionNick() == "perm" && $callObject->evnClassId == 13) {
				$msfd_filter .= " and (MSF.MedSpecOms_id <> 73 or :LpuSectionProfile_id = EVPLD.LpuSectionProfile_id)"; // специальность не 171 или профили совпрадают
			}
			$join .= " inner join v_{$parentAlias} EPLD on EPLD.{$parentAlias}_id = EVPLD.{$callObject->tableName()}_pid
				and EPLD.{$parentAlias}_IsFinish = 2";
		}
		$add_field = "";
		if (getRegionNick() == "perm") {
			$add_field .= "
				,case
					when EVPLD.{$callObject->tableName()}_setDate >= '2018-01-01' and '2018-01СверхПодуш' in (
						select vt.VolumeType_Code
						from
							v_AttributeVision avis
							inner join v_VolumeType vt on vt.VolumeType_id = avis.AttributeVision_TablePKey
							inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
						where avis.AttributeVision_TableName = 'dbo.VolumeType'
						  and avis.AttributeVision_IsKeyValue = 2
						  and av.AttributeValue_ValueIdent = UC.UslugaComplex_id
						  and av.AttributeValue_begDate <= EVPLD.{$callObject->tableName()}_setDate
						  and (av.AttributeValue_endDate is null or av.AttributeValue_endDate > EVPLD.{$callObject->tableName()}_setDate)
					)
						then 1
						else 0
					end as \"isSverhPodush\"
			";
		}
		$query = "
			select
				EVPLD.{$callObject->tableName()}_id as \"EvnVizitPL_id\",
				EVPLD.{$callObject->tableName()}_pid as \"EvnVizitPL_pid\",
				EVPLD.VizitPLDouble_id as \"VizitPLDouble_id\",
				UC.UslugaComplex_Code as \"UslugaComplex_Code\"
				{$add_field}
			from
				v_{$callObject->tableName()} EVPLD
				{$join}
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EVPLD.UslugaComplex_id
				inner join v_MedStaffFact MSF on MSF.MedStaffFact_id = :MedStaffFact_id
				inner join v_MedStaffFact MSFD on MSFD.MedStaffFact_id = EVPLD.MedStaffFact_id {$msfd_filter}
				inner join v_PayType PT on PT.PayType_id = :PayType_id and PT.PayType_SysNick = 'oms'
				inner join v_PayType PTD on EVPLD.PayType_id = PTD.PayType_id and PTD.PayType_SysNick = 'oms'
			where :Lpu_id = EVPLD.Lpu_id
			  and :Person_id = EVPLD.Person_id
			  and :setDate = EVPLD.{$callObject->tableName()}_setDate
			  and :EvnVizitPL_id <> EVPLD.{$callObject->tableName()}_id  
			  {$add_where}
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @return array|false
	 */
	public static function _getEvnVizitPLDoubles(EvnVizitPL_model $callObject)
	{
		if ($callObject->payTypeSysNick != "oms") {
			return [];
		}
		$add_where = "";
		$add_field = "";
		$add_params = [];
		$parentAlias = "EvnPL";
		if ($callObject->evnClassId == 13) {
			$parentAlias = "EvnPLStom";
			$add_where .= "
				and coalesce(EVPL.Tooth_id, 0) = coalesce(CAST(:Tooth_id as bigint), 0)
			";
			$add_params["Tooth_id"] = $callObject->Tooth_id;
		}
		$accessType = "'edit'";
		if (empty($callObject->sessionParams["isMedStatUser"]) && !empty($callObject->sessionParams["medpersonal_id"])) {
			$accessType = "case when msf.MedPersonal_id = :MedPersonal_id then 'edit' else 'view' end";
		}
		if ($callObject->regionNick == "ufa") {
			$add_where .= "
				and msf.MedPersonal_id = (select medpersonal_id from myvars)
				and msf.LpuSectionProfile_id = (select LpuSectionProfile_id from myvars)
				";
		} else {
			$add_where .= "
				and msf.MedSpecOms_id = (select MedSpecOms_id from myvars)
				and EPL.{$parentAlias}_IsFinish = 2
				and EPLD.{$parentAlias}_IsFinish = 2
			";
			if (getRegionNick() == "perm" && $callObject->evnClassId == 13) {
				$add_where .= " and (msf.MedSpecOms_id <> 73 or EVPL.LpuSectionProfile_id = :LpuSectionProfile_id)"; // специальность не 171 или профили совпрадают
			}
		}
		if ($callObject->regionNick == "perm") {
			$add_field .= "
				,case
					when EVPL.EvnVizitPL_setDate >= '2018-01-01' and '2018-01СверхПодуш' in (
						select vt.VolumeType_Code
						from
							v_AttributeVision avis
							inner join v_VolumeType vt on vt.VolumeType_id = avis.AttributeVision_TablePKey
							inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
						where avis.AttributeVision_TableName = 'dbo.VolumeType'
						  and avis.AttributeVision_IsKeyValue = 2
						  and av.AttributeValue_ValueIdent = UC.UslugaComplex_id
						  and av.AttributeValue_begDate <= EVPL.EvnVizitPL_setDate
						  and (av.AttributeValue_endDate is null or av.AttributeValue_endDate > EVPL.EvnVizitPL_setDate)
					)
						then 1
						else 0
					end as \"isSverhPodush\"
			";
		}
		$query = "
			with myvars as (
				select
					MedPersonal_id,
					LpuSectionProfile_id,
					MedSpecOms_id
				from v_MedStaffFact
				where MedStaffFact_id = :MedStaffFact_id
				limit 1
			)
			select
				EVPL.EvnVizitPL_id as \"EvnVizitPL_id\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				msf.Person_Fio as \"MedPersonal_Fio\",
				to_char(EVPL.EvnVizitPL_setDate, 'dd.mm.yyyy') as \"EvnVizitPL_setDate\",
				EVPL.EvnVizitPL_pid as \"EvnVizitPL_pid\",
				EVPL.VizitPLDouble_id as \"VizitPLDouble_id\",
				{$accessType} as \"accessType\",
				EPL.{$parentAlias}_NumCard as \"EvnPL_NumCard\",
				EPLD.{$parentAlias}_NumCard as \"EvnPLDouble_NumCard\",
				UC.UslugaComplex_Code as \"UslugaComplex_Code\"
				{$add_field}
			from
				v_EvnVizitPL EVPL
				inner join v_{$parentAlias} EPL on EPL.{$parentAlias}_id = :EvnVizitPL_pid
				inner join v_{$parentAlias} EPLD on EPLD.{$parentAlias}_id = EVPL.EvnVizitPL_pid
				inner join v_LpuSection LS on LS.LpuSection_id = EVPL.LpuSection_id
				inner join v_PayType PT on EVPL.PayType_id = PT.PayType_id and PT.PayType_SysNick = 'oms'
				inner join v_MedStaffFact msf on msf.MedStaffFact_id = evpl.MedStaffFact_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EVPL.UslugaComplex_id
			where EVPL.Lpu_id = :Lpu_id
			  and EVPL.Person_id = :Person_id
			  and EVPL.EvnVizitPL_id <> coalesce(CAST(:EvnVizitPL_id as bigint), 0)
			  and EVPL.EvnVizitPL_setDate = :EvnVizitPL_setDate::timestamp
			  {$add_where}
		";
		$params = array_merge($add_params, [
			"EvnVizitPL_pid" => $callObject->pid,
			"EvnVizitPL_id" => $callObject->id,
			"EvnVizitPL_setDate" => $callObject->setDate,
			"Lpu_id" => $callObject->Lpu_id,
			"MedStaffFact_id" => $callObject->MedStaffFact_id,
			"LpuSectionProfile_id" => $callObject->LpuSectionProfile_id,
			"MedPersonal_id" => $callObject->MedPersonal_id,
			"PayType_id" => $callObject->PayType_id,
			"Person_id" => $callObject->Person_id,
		]);
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param EvnVizitPL_model $callObject
	 * @return int
	 */
	public static function _getNextNumGroup(EvnVizitPL_model $callObject)
	{
		$callObject->_groupNum++;
		while (in_array($callObject->_groupNum, $callObject->_groupNumExceptions)) {
			$callObject->_groupNum++;
		}
		return $callObject->_groupNum;
	}

	public static function getEvnVizitPLSetDate(EvnVizitPL_model $callObject, $data)
	{
		return $callObject->db->query("
			select 
				EVPL.EvnVizitPL_setDate as \"EvnVizitPL_setDate\"
			from 
				v_EvnVizitPL EVPL
			where 
				EVPL.EvnVizitPL_id = :EvnVizitPL_id
		", $data);
	}
}