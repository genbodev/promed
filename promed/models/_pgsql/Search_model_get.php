<?php

class Search_model_get
{
	/**
	 * @param Search_model $callObject
	 * @param $data
	 * @return string
	 */
	public static function getLpuIdFilter(Search_model $callObject, $data)
	{
		if (count($callObject->lpuList) == 0) {
			if (in_array($data["SearchFormType"], ["EvnPL", "EvnVizitPL", "EvnPLStom", "EvnVizitPLStom"]) && !isSuperAdmin()) {
				$resp = $callObject->queryResult("select Lpu_id as \"Lpu_id\" from v_Lpu where Lpu_pid = :Lpu_id or Lpu_id = :Lpu_id", $data);
				if ($resp === false) {
					$callObject->lpuList[] = $data["Lpu_id"];
				} else {
					foreach ($resp as $row) {
						if (!in_array($row["Lpu_id"], $callObject->lpuList)) {
							$callObject->lpuList[] = $row["Lpu_id"];
						}
					}
				}
			} elseif (array_key_exists("linkedLpuIdList", $data["session"])) {
				$callObject->lpuList = $data["session"]["linkedLpuIdList"];
			}
			if (count($callObject->lpuList) == 0) {
				$callObject->lpuList[] = $data["Lpu_id"];
			}
		}
		return (count($callObject->lpuList) > 1 ? "in (" . implode(",", $callObject->lpuList) . ")" : "= :Lpu_id");
	}

	/**
	 * @param Search_model $callObject
	 * @param $data
	 * @param $filter
	 * @param $queryParams
	 */
	public static function getPrivilegeFilters(Search_model $callObject, $data, &$filter, &$queryParams)
	{
		if ($data["RegisterSelector_id"] == 1) {
			$filter .= " and PT.ReceptFinance_id = 1";
			$queryParams["RegisterSelector_id"] = $data["RegisterSelector_id"];
		} elseif ($data["RegisterSelector_id"] == 2) {
			$filter .= " and PT.ReceptFinance_id = 2";
			$queryParams["RegisterSelector_id"] = $data["RegisterSelector_id"];
		}
		// в связи с тем, что на форме swPrivilegeSearchWindow ("Регистр льготников: Поиск") закомментирован комбобокс RegisterSelector_id, для нее отдельно прописываем фильтр по типу финансирования
		// Кроме того, на всех формах, где используется поле RegisterSelector_id предусмотрены только два значения: Федеральный и Регоинальный, а на форме "Регистр льготников: Поиск"
		// добавлены значения "7 нозологий" и "Федеральный бюджет ОССЗ", для этих значений фильтрация тоже предусматривается в следующем блоке кода
		if ($data['SearchFormType'] == 'PersonPrivilege'){
			//в некторых регионах не выделены льготные категории для Программы ЛЛО со значением ВЗН (7 или 14 нозологий), там они являются региональными льготами
			if($data['ReceptFinance_id'] == 3 && in_array($callObject->getRegionNick(), ['perm', 'ufa'])) {
				$filter .= " and PT.ReceptFinance_id = 2 and PS.Person_Is7Noz = 1";
			} else if (!empty($data['ReceptFinance_id'])) {
				$filter .= " and PT.ReceptFinance_id = {$data['ReceptFinance_id']}";
			}
		}
		
		if (!empty($data["PersonPrivilege_deleted"])) {
			$filter .= " and coalesce(PP.PersonPrivilege_deleted, 1) = :PersonPrivilege_deleted";
			$queryParams["PersonPrivilege_deleted"] = $data["PersonPrivilege_deleted"];
		}
		if (isset($data["Lpu_prid"]) && $data["Lpu_prid"] > 0) {
			$filter .= " and PP.Lpu_id = :Lpu_prid";
			$queryParams["Lpu_prid"] = $data["Lpu_prid"];
		}
		if (isset($data["PrivilegeType_id"])) {
			$filter .= " and PT.PrivilegeType_id = :PrivilegeType_id";
			$queryParams["PrivilegeType_id"] = $data["PrivilegeType_id"];
		}
		if (isset($data["SubCategoryPrivType_id"]) && $callObject->regionNick == "kz") {
			$filter .= " and PPSCPT.SubCategoryPrivType_id = :SubCategoryPrivType_id";
			$queryParams["SubCategoryPrivType_id"] = $data["SubCategoryPrivType_id"];
		}
		if ($data["PrivilegeStateType_id"] == 1) {
			$filter .= "
				and PP.PersonPrivilege_begDate is not null
			    and PP.PersonPrivilege_begDate <= tzgetdate()
			    and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate > (select dt from mv))
			 ";
		}
		if (isset($data["SignalInfo"]) && $data["SignalInfo"] == 1) {
			$filter .= "
				and PP.PersonPrivilege_endDate is not null
				and PP.PersonPrivilege_endDate >  (tzgetdate() - interval '1 day')
				and PP.PersonPrivilege_endDate <  (tzgetdate() + interval '29 days')
			";
		}
		if (isset($data["Privilege_begDate"])) {
			$filter .= " and PP.PersonPrivilege_begDate = :Privilege_begDate::timestamp";
			$queryParams["Privilege_begDate"] = $data["Privilege_begDate"];
		}
		if (isset($data["Privilege_begDate_Range"][0])) {
			$filter .= " and PP.PersonPrivilege_begDate >= :Privilege_begDate_Range_0::timestamp";
			$queryParams["Privilege_begDate_Range_0"] = $data["Privilege_begDate_Range"][0];
		}
		if (isset($data["Privilege_begDate_Range"][1])) {
			$filter .= " and PP.PersonPrivilege_begDate <= :Privilege_begDate_Range_1::timestamp";
			$queryParams["Privilege_begDate_Range_1"] = $data["Privilege_begDate_Range"][1];
		}
		if (isset($data["Privilege_endDate"])) {
			$filter .= " and PP.PersonPrivilege_endDate = :Privilege_endDate::timestamp";
			$queryParams["Privilege_endDate"] = $data["Privilege_endDate"];
		}
		if (isset($data["Privilege_endDate_Range"][0])) {
			$filter .= " and PP.PersonPrivilege_endDate >= :Privilege_endDate_Range_0::timestamp";
			$queryParams["Privilege_endDate_Range_0"] = $data["Privilege_endDate_Range"][0];
		}
		if (isset($data["Privilege_endDate_Range"][1])) {
			$filter .= " and PP.PersonPrivilege_endDate <= :Privilege_endDate_Range_1::timestamp";
			$queryParams["Privilege_endDate_Range_1"] = $data["Privilege_endDate_Range"][1];
		}
	}

	/**
	 * @param Search_model $callObject
	 * @param $data
	 * @param $filter
	 * @param $queryParams
	 */
	public static function getPrivilegeAccessRightsFilters(Search_model $callObject, $data, &$filter, &$queryParams)
	{
		$groups = explode("|", $data["session"]["groups"]);
		$user_groups = "'{$groups[0]}'";
		if (count($groups) > 1) {
			for ($i = 1; $i < count($groups); $i++) {
				$user_groups .= ",'{$groups[$i]}'";
			}
		}
		$alias = ($data["SearchFormType"] == "EvnRecept") ? "ER" : "PT";
		$privilegeFilter = getAccessRightsPrivilegeTypeFilter("{$alias}.PrivilegeType_id");
		if (!empty($privilegeFilter)) {
			$filter .= " and {$privilegeFilter}";
		}
		if ($callObject->getRegionNick() == "ufa") {
			$alias = ($data["SearchFormType"] == "PersonPrivilege") ? "PP" : "ER";
			$lpuFilter = getAccessRightsLpuFilter("Lpu_id");
			$lpuFilter = !empty($lpuFilter) ? " and {$lpuFilter}" : "";
			$filter .= "
				and exists (
					select PersonPrivilege_id
					from v_PersonPrivilege
					where PrivilegeType_id = {$alias}.PrivilegeType_id
					  and Person_id = PS.Person_id {$lpuFilter}
				)
			";
		}
		$queryParams["Lpu_id"] = $data["Lpu_id"];
	}

	/**
	 * @param Search_model $callObject
	 * @param $data
	 * @param $filter
	 * @param $queryParams
	 * @param $orderby
	 * @param $pac_filter
	 * @throws Exception
	 */
	public static function getPersonCardFilters(Search_model $callObject, $data, &$filter, &$queryParams, &$orderby, &$pac_filter)
	{
		if (isset($data["PersonCard_endDate"])) {
			$filter .= " and PC.PersonCard_endDate = :PersonCard_endDate";
			$queryParams["PersonCard_endDate"] = $data["PersonCard_endDate"];
		}
		if (isset($data["PersonCard_endDate_Range"][0])) {
			$filter .= " and PC.PersonCard_endDate >= :PersonCard_endDate_Range_0";
			$queryParams["PersonCard_endDate_Range_0"] = $data["PersonCard_endDate_Range"][0];
		}
		if (isset($data["PersonCard_endDate_Range"][1])) {
			$filter .= " and PC.PersonCard_endDate <= :PersonCard_endDate_Range_1";
			$queryParams["PersonCard_endDate_Range_1"] = $data["PersonCard_endDate_Range"][1];
		}
		if ($data["AttachLpu_id"] > 0) {
			if ($data["AttachLpu_id"] == 666666 && in_array($data["SearchFormType"], ["EvnPLDispDop13", "EvnPLDispDop13Sec"]) && getRegionNick() === "ekb") {
				// Вариант "Без прикрепления к МО", используется только в екб на формах поиск двн 1 и 2 этап. Выводятся люди которые не имебт прикрепления ни к одной МО
			} else if ($data["AttachLpu_id"] == 100500) {
				$filter .= " and PC.Lpu_id is not null and PC.Lpu_id <> :Lpu_id";
			} else {
				//Прикрепление для ИПРА вернул array("BskRegistry", "IPRARegistry"))
				$filter .= !in_array($data["SearchFormType"], ["BskRegistry"]) ? " and PC.Lpu_id = :AttachLpu_id" : "";
				$filter .= " and PC.Lpu_id = :AttachLpu_id";
				//Прикрепление для Сигнальной информации
				//для участковых врачей показываем только пациентов с его участка
				if (in_array($data["SearchFormType"], ["EvnUslugaPar", "PersonPrivilege", "CmpCloseCard"])) {
					if (isset($data["MedPersonal_id"]) && ($data["MedPersonal_id"] > 0)) {
						$queryParams["MedPersonal_id"] = $data["MedPersonal_id"];
						$filter .= " and MedStaffRegion.MedPersonal_id = :MedPersonal_id";
					}
				}
			}
			$queryParams["AttachLpu_id"] = $data["AttachLpu_id"];
		}
		if ($data["SearchFormType"] == "IPRARegistry" && isset($data["LPU_id"]) && !empty($data["LPU_id"]) && $callObject->getRegionNick() == "ufa") {
			$filter .= " and (
					(
						IR.IPRARegistry_FGUMCEnumber in (11,12,13,14,16) and
						IR.IPRARegistry_DirectionLPU_id in (338, 392, 393, 89, 86, 391, 394, 150016) and
						IR.Lpu_id = :Lpu_id and
						IR.Lpu_id in (338, 392, 393, 89, 86, 391, 394, 150016)
					) or
					IR.Lpu_id not in (338, 392, 393, 89, 86, 391, 394, 150016)
				)
			";
			$queryParams["Lpu_id"] = $data["Lpu_id"];
		}
		if ($data["LpuAttachType_id"] > 0) {
			$filter .= " and PC.LpuAttachType_id = :LpuAttachType_id";
			$queryParams["LpuAttachType_id"] = $data["LpuAttachType_id"];
		}
		if (trim($data["PersonCard_Code"]) != "") {
			if (!empty($data["PartMatchSearch"])) {
				// включен чекбокс "Поиск по частичному совпадению"
				if (!empty($callObject->config->config["blockSlowDownFunctions"])) {
					throw new Exception("Функционал поиска по частичному совпадению временно заблокирован. Приносим извинения за доставленные неудобства.");
				}
				$filter .= " and upper(PC.PersonCard_Code) like '%'||upper(:PersonCard_Code)||'%'";
				$orderby = "case when coalesce(strpos(pc.PersonCard_Code, :PersonCard_Code), 0) > 0 then strpos(pc.PersonCard_Code, :PersonCard_Code) else 99 end,";
			} else {
				if (in_array($data["SearchFormType"], ["EvnPL", "EvnVizitPL"])) {
					$pac_filter .= "
						and exists(
							select *
							from v_PersonAmbulatCard PAC2
							where PAC2.PersonAmbulatCard_Num = :PersonCard_Code
							  and PAC2.Person_id = PS.Person_id
							  and PAC2.Lpu_id = {$data["session"]["lpu_id"]}
						)
					";
				} else {
					$filter .= " and PC.PersonCard_Code = :PersonCard_Code";
				}
			}
			$queryParams['PersonCard_Code'] = $data['PersonCard_Code'];
		}
		if (isset($data["PersonCard_begDate"])) {
			$filter .= " and PC.PersonCard_begDate::date = :PersonCard_begDate";
			$queryParams["PersonCard_begDate"] = $data["PersonCard_begDate"];
		}
		if (isset($data["PersonCard_begDate_Range"][0])) {
			$filter .= " and PC.PersonCard_begDate >= :PersonCard_begDate_Range_0::timestamp";
			$queryParams["PersonCard_begDate_Range_0"] = $data["PersonCard_begDate_Range"][0];
		}
		if (isset($data["PersonCard_begDate_Range"][1])) {
			$filter .= " and PC.PersonCard_begDate <= :PersonCard_begDate_Range_1::timestamp";
			$queryParams["PersonCard_begDate_Range_1"] = $data["PersonCard_begDate_Range"][1];
		}
		if ($data["PersonCard_IsAttachCondit"] > 0) {
			$filter .= " and coalesce(PC.PersonCard_IsAttachCondit, 1) = :PersonCard_IsAttachCondit";
			$queryParams["PersonCard_IsAttachCondit"] = $data["PersonCard_IsAttachCondit"];
		}
		if ($data["PersonCardAttach"] > 0) {
			$filter .= ($data["PersonCardAttach"] == 1) ? " and PC.PersonCardAttach_id is null" : " and PC.PersonCardAttach_id is not null";
		}
		if ($data["PersonCard_IsDms"] > 0) {
			$exists = "";
			if ($data["PersonCard_IsDms"] == 1) {
				$exists = " not ";
			}
			$filter .= "
				and {$exists} exists(
					select PersonCard_id
					from v_PersonCard
					where Person_id = PC.Person_id
					  and LpuAttachType_id = 5
					  and PersonCard_endDate >= tzgetdate()
					  and CardCloseCause_id is null
				) 
			";
		}
		if (isset($data["LpuRegion_id"])) {
			$queryParams["LpuRegion_id"] = $data["LpuRegion_id"];
			$filter .= ($data["LpuRegion_id"] == -1) ? " and LR.LpuRegion_id is null" : " and LR.LpuRegion_id = :LpuRegion_id";
		}
		if (isset($data["LpuRegion_Fapid"])) {
			$queryParams["LpuRegion_Fapid"] = $data["LpuRegion_Fapid"];
			$filter .= " and LR_Fap.LpuRegion_id = :LpuRegion_Fapid";
		}
		if ($data["LpuRegionType_id"] > 0) {
			$filter .= " and LR.LpuRegionType_id = :LpuRegionType_id";
			$queryParams["LpuRegionType_id"] = $data["LpuRegionType_id"];
		}
	}

	/**
	 * @param $data
	 * @param $filter
	 * @param $queryParams
	 * @param $main_alias
	 * @param string $alias
	 */
	public static function getPersonPeriodicFilters($data, &$filter, &$queryParams, $main_alias, $alias = "PS")
	{
		if (isset($data["Person_Birthday"])) {
			$filter .= ($data["SearchFormType"] == "CmpCallCard" || $data["SearchFormType"] == "CmpCloseCard")
				? " and coalesce({$alias}.Person_BirthDay, CCC.Person_BirthDay) = :Person_Birthday::timestamp"
				: " and {$alias}.Person_BirthDay = cast(:Person_Birthday as timestamp)";
			$queryParams["Person_Birthday"] = $data["Person_Birthday"];
		}
		if (isset($data["Person_BirthdayYear"])) {
			$filter .= " and date_part('year', {$alias}.Person_BirthDay) = :Person_BirthdayYear";
			$queryParams["Person_BirthdayYear"] = $data["Person_BirthdayYear"];
		}
		if (isset($data["EvnPLWOW_setDate_Range"][0])) {
			$filter .= " and EPW.EvnPLWow_setDate >= :EvnPLWOW_setDate_Range_0::timestamp";
			$queryParams["EvnPLWOW_setDate_Range_0"] = $data["EvnPLWOW_setDate_Range"][0];
		}
		if (isset($data["EvnPLWOW_setDate_Range"][1])) {
			$filter .= " and EPW.EvnPLWow_setDate <= :EvnPLWOW_setDate_Range_1::timestamp";
			$queryParams["EvnPLWOW_setDate_Range_1"] = $data["EvnPLWOW_setDate_Range"][1];
		}
		if (isset($data["Person_Birthday_Range"][0])) {
			$filter .= ($data["SearchFormType"] == "CmpCallCard" || $data["SearchFormType"] == "CmpCloseCard")
				? " and coalesce({$alias}.Person_BirthDay, CCC.Person_BirthDay) >= :Person_Birthday_Range_0::timestamp"
				: " and {$alias}.Person_BirthDay >= cast(:Person_Birthday_Range_0 as timestamp)";
			$queryParams["Person_Birthday_Range_0"] = $data["Person_Birthday_Range"][0];
		}
		if (isset($data["Person_Birthday_Range"][1])) {
			$filter .= ($data["SearchFormType"] == "CmpCallCard" || $data["SearchFormType"] == "CmpCloseCard")
				? " and coalesce({$alias}.Person_BirthDay, CCC.Person_BirthDay) <= :Person_Birthday_Range_1::timestamp"
				: " and {$alias}.Person_BirthDay <= :Person_Birthday_Range_1::timestamp";
			$queryParams["Person_Birthday_Range_1"] = $data["Person_Birthday_Range"][1];
		}
		if (trim($data["Person_Code"]) != "") {
			$filter .= " and {$alias}.Person_EdNum = :Person_Code";
			$queryParams["Person_Code"] = $data["Person_Code"];
		}
		if (trim($data["Person_Firname"]) != "") {
			$filter .= ($data["SearchFormType"] == "CmpCallCard")
				? " and upper(coalesce({$alias}.Person_FirName, CCC.Person_FirName)) like upper(:Person_Firname)||'%'"
				: ($data["SearchFormType"] == "CmpCloseCard"
					? " and upper(COALESCE(CLC.Name, {$alias}.Person_FirName, CCC.Person_FirName)) like upper(:Person_Firname)||'%'"
					: " and upper({$alias}.Person_FirName) like upper(:Person_Firname)||'%'");
			$queryParams["Person_Firname"] = rtrim($data["Person_Firname"]);
		}
		if (trim($data["Person_Secname"]) != "") {
			$filter .= ($data["SearchFormType"] == "CmpCallCard")
				? " and upper(coalesce({$alias}.Person_SecName, CCC.Person_SecName)) like upper(:Person_Secname)||'%'"
				: ($data["SearchFormType"] == "CmpCloseCard"
					? " and upper(COALESCE(CLC.Middle, {$alias}.Person_SecName, CCC.Person_SecName)) like upper(:Person_Secname)||'%'"
					: " and upper({$alias}.Person_SecName) like upper(:Person_Secname)||'%'");
			$queryParams["Person_Secname"] = rtrim($data["Person_Secname"]);
		}
		if (trim($data["Person_Surname"]) != "") {
			$queryParams["Person_Surname"] = rtrim($data["Person_Surname"]);
			switch ($data["SearchFormType"]) {
				case "CmpCallCard":
					$filter .= " and upper(coalesce({$alias}.Person_SurName, CCC.Person_SurName)) like upper(:Person_Surname)||'%'";
					break;
				case "CmpCloseCard":
					$filter .= " and upper(coalesce(CLC.Fam, {$alias}.Person_Surname, CCC.Person_SurName)) like upper(:Person_Surname)||'%'";
					break;
				case "EvnPS":
				case "EvnSection":
				case "EvnPL": // Талон амбулаторного пациента поиск
					$filter .= " and lower({$alias}.Person_SurName) like lower(:Person_Surname)||'%'";
					break;
				case "EvnPLStom":
				case "EvnVizitPL":
				case "EvnVizitPLStom":
				case "PersonCard":
				case "PersonPrivilege":
				case "EvnUslugaPar":
				case "EvnRecept":
				case "EvnReceptGeneral":
				case "EvnPLDispDop":
				case "EvnPLDispDop13":
				case "EvnPLDispDop13Sec":
				case "EvnPLDispTeen14":
				case "EvnPLDispProf":
				case "EvnPLDispOrp":
				case "EvnPLDispOrpSec":
				case "EvnPLDispOrpOld":
				case "EvnPLDispScreen":
				case "EvnPLDispScreenChild":
				case "EvnPLDispTeenInspectionPeriod":
				case "EvnPLDispTeenInspectionProf":
				case "EvnPLDispTeenInspectionPred":
				case "EvnPLDispMigrant":
				case "EvnPLDispDriver":
				case "PersonDisp":
				case "PersonDopDisp":
				case "PersonDispOrp":
				case "PersonDispOrpPeriod":
				case "PersonDispOrpPred":
				case "PersonDispOrpProf":
				case "PersonDispOrpOld":
				case "WorkPlacePolkaReg":
				case "PersonCallCenter":
					if (allowPersonEncrypHIV($data["session"]) && isSearchByPersonEncrypHIV($data["Person_Surname"])) {
						$queryParams["Person_Surname"] = rtrim($data["Person_Surname"]);
					}
					$filter .= (allowPersonEncrypHIV($data["session"]) && isSearchByPersonEncrypHIV($data["Person_Surname"]))
						? " and upper(PEH.PersonEncrypHIV_Encryp) like upper(:Person_Surname)||'%'"
						: " and upper({$alias}.Person_SurName) like upper(:Person_Surname)||'%'";
					break;
				default:
					$filter .= " and upper({$alias}.Person_SurName) like upper(:Person_Surname)||'%'";
			}
		}
		$getdate = "tzgetdate()";
		if (in_array($data["PersonPeriodicType_id"], [2, 3])) {
			if (in_array($data["SearchFormType"], ["EvnVizitPL", "EvnPS", "EvnSection", "EvnPLStom", "EvnVizitPLStom"])) {
				$getdate = "{$main_alias}.{$data["SearchFormType"]}_setDate";
			} else if (in_array($data["SearchFormType"], ["EvnPL"])) {
				$getdate = "Evn.Evn_setDT::date";
			}
		}
		if (in_array($data["SearchFormType"], ["EvnPLDispDop13", "EvnPLDispDop13Sec"])) {
			$getdate = "(select PPD_YearEndDate from mv)";
		}
		if (isset($data["PersonAge"])) {
			$filter .= ($data["SearchFormType"] == "CmpCallCard" || $data["SearchFormType"] == "CmpCloseCard")
				? " and dbo.Age2(coalesce({$alias}.Person_BirthDay, CCC.Person_BirthDay), {$getdate}) = :PersonAge"
				: " and dbo.Age2({$alias}.Person_BirthDay, {$getdate}) = :PersonAge";
			$queryParams["PersonAge"] = intval($data["PersonAge"]);
		}
		if (isset($data["PersonAge_Max"])) {
			$filter .= ($data["SearchFormType"] == "CmpCallCard" || $data["SearchFormType"] == "CmpCloseCard")
				? " and dbo.Age2(coalesce({$alias}.Person_BirthDay, CCC.Person_BirthDay), {$getdate}) <= :PersonAge_Max"
				: " and dbo.Age2({$alias}.Person_BirthDay, {$getdate}) <= :PersonAge_Max";
			$queryParams["PersonAge_Max"] = intval($data["PersonAge_Max"]);
		}
		if (isset($data["PersonAge_Min"])) {
			$filter .= ($data["SearchFormType"] == "CmpCallCard" || $data["SearchFormType"] == "CmpCloseCard")
				? " and dbo.Age2(coalesce({$alias}.Person_BirthDay, CCC.Person_BirthDay), {$getdate}) >= :PersonAge_Min"
				: " and dbo.Age2({$alias}.Person_BirthDay, {$getdate}) >= :PersonAge_Min";
			$queryParams["PersonAge_Min"] = intval($data["PersonAge_Min"]);
		}
		if (isset($data["PersonBirthdayYear"])) {
			$filter .= ($data["SearchFormType"] == "CmpCallCard" || $data["SearchFormType"] == "CmpCloseCard")
				? " and date_part('year', coalesce({$alias}.Person_BirthDay, CCC.Person_BirthDay)) = :PersonBirthdayYear"
				: " and date_part('year', {$alias}.Person_BirthDay) = :PersonBirthdayYear";
			$queryParams["PersonBirthdayYear"] = intval($data["PersonBirthdayYear"]);
		}
		if (isset($data["PersonBirthdayYear_Max"])) {
			$filter .= ($data["SearchFormType"] == "CmpCallCard" || $data["SearchFormType"] == "CmpCloseCard")
				? " and date_part('year', coalesce({$alias}.Person_BirthDay, CCC.Person_BirthDay)) <= :PersonBirthdayYear_Max"
				: " and date_part('year', {$alias}.Person_BirthDay) <= :PersonBirthdayYear_Max";
			$queryParams["PersonBirthdayYear_Max"] = intval($data["PersonBirthdayYear_Max"]);
		}
		if (isset($data["PersonBirthdayYear_Min"])) {
			$filter .= ($data["SearchFormType"] == "CmpCallCard" || $data["SearchFormType"] == "CmpCloseCard")
				? " and date_part('year', coalesce({$alias}.Person_BirthDay, CCC.Person_BirthDay)) >= :PersonBirthdayYear_Min"
				: " and date_part('year', {$alias}.Person_BirthDay) >= :PersonBirthdayYear_Min";
			$queryParams["PersonBirthdayYear_Min"] = intval($data["PersonBirthdayYear_Min"]);
		}
		if (isset($data["PersonBirthdayMonth"])) {
			$filter .= " and date_part('month', {$alias}.Person_BirthDay) = :PersonBirthdayMonth";
			$queryParams["PersonBirthdayMonth"] = $data["PersonBirthdayMonth"];
		}
		if (isset($data["SnilsExistence"]) && $data["SnilsExistence"] != "") {
			$filter .= ($data["SnilsExistence"] == 1)
				? " and (PS.Person_Snils = '' or PS.Person_Snils is null)"
				: " and (PS.Person_Snils <> '' or PS.Person_Snils is not null)";
		}
		if (trim($data["Person_Snils"]) != "") {
			$filter .= " and {$alias}.Person_Snils = :Person_Snils";
			$queryParams["Person_Snils"] = $data["Person_Snils"];
		}
		if (trim($data["Person_Inn"]) != "") {
			$filter .= "
				and exists(
					select t.Person_id
					from v_PersonInn t
					where t.Person_id = {$alias}.Person_id
					  and t.PersonInn_Inn = :Person_Inn
				)
			";
			$queryParams["Person_Inn"] = $data["Person_Inn"];
		}
		if ($data["Sex_id"] > 0) {
			$filter .= ($data["SearchFormType"] == "CmpCallCard" || $data["SearchFormType"] == "CmpCloseCard")
				? " and coalesce({$alias}.Sex_id, CCC.Sex_id) = :Sex_id"
				: " and coalesce({$alias}.Sex_id, 3) = :Sex_id";
			$queryParams["Sex_id"] = $data["Sex_id"];
		}
		if ($data["SocStatus_id"] > 0) {
			$filter .= " and {$alias}.SocStatus_id = :SocStatus_id";
			$queryParams["SocStatus_id"] = $data["SocStatus_id"];
		}
		if (isset($data["Person_IsBDZ"])) {
			$filter .= "
				and exists (
					select 1
					from Person PTemp
					where PTemp.Person_id = {$alias}.Person_id
					  and PTemp.Server_id " . ($data["Person_IsBDZ"] == 2 ? "=" : "<>") . " 0
					limit 1
				)
			";
		}
		if (isset($data["Person_isIdentified"])) {
			if ($data["SearchFormType"] == "CmpCallCard" || $data["SearchFormType"] == "CmpCloseCard") {
				$filter .= " and coalesce(CCC.Person_id, 0) " . ($data["Person_isIdentified"] == 2 ? "<>" : "=") . " 0";
			}
		}
		if (isset($data["Person_IsDisp"])) {
			$filter .= "
				and " . ($data["Person_IsDisp"] == 1 ? "not" : "") . " exists (
					select 1
					from PersonDisp PDTemp
					where PDTemp.Person_id = {$alias}.Person_id
					  and PDTemp.PersonDisp_begDate <= tzgetdate()
					  and (PDTemp.PersonDisp_endDate is null or PDTemp.PersonDisp_endDate > tzgetdate())
					limit 1
				)
			";
		}
		if ((trim($data["Document_Num"]) != "") || (trim($data["Document_Ser"]) != "") || ($data["DocumentType_id"] > 0) || ($data["OrgDep_id"] > 0)) {
			$filter .= "
				and exists (
					select Document_id
					from Document
					where Document.Document_id = {$alias}.Document_id
			";
			if (trim($data["Document_Num"]) != "") {
				$filter .= " and Document.Document_Num = :Document_Num";
				$queryParams["Document_Num"] = $data["Document_Num"];
			}
			if (trim($data["Document_Ser"]) != "") {
				$filter .= " and Document.Document_Ser = :Document_Ser";
				$queryParams["Document_Ser"] = $data["Document_Ser"];
			}
			if ($data["DocumentType_id"] > 0) {
				$filter .= " and Document.DocumentType_id = :DocumentType_id";
				$queryParams["DocumentType_id"] = $data["DocumentType_id"];
			}
			if ($data["OrgDep_id"] > 0) {
				$filter .= " and Document.OrgDep_id = :OrgDep_id";
				$queryParams["OrgDep_id"] = $data["OrgDep_id"];
			}
			$filter .= ") ";
		}
		if (($data['Org_id'] > 0) || ($data['Post_id'] > 0)) {
			$filter .= "
				and exists (
					select Job_id
					from Job
					where Job.Job_id = {$alias}.Job_id
			";
			if ($data["Org_id"] > 0) {
				$filter .= " and Job.Org_id = :Org_id";
				$queryParams["Org_id"] = $data["Org_id"];
			}
			if ($data["Post_id"] > 0) {
				$filter .= " and Job.Post_id = :Post_id";
				$queryParams["Post_id"] = $data["Post_id"];
			}
			$filter .= ") ";
		}
		if (strtolower($data["Person_NoAddress"]) == "on") {
			switch ($data["AddressStateType_id"]) {
				case 1:
					$filter .= " and {$alias}.UAddress_id is null";
					break;
				case 2:
					$filter .= " and {$alias}.PAddress_id is null";
					break;
				default:
					$filter .= " and {$alias}.UAddress_id is null";
					$filter .= " and {$alias}.PAddress_id is null";
					break;
			}
		} elseif (
			$data["KLAreaType_id"] > 0 ||
			$data["KLCountry_id"] > 0 ||
			(!empty($data["Person_citizen"]) && $data["Person_citizen"] != 1) ||
			$data["KLRgn_id"] > 0 ||
			$data["KLSubRgn_id"] > 0 ||
			$data["KLCity_id"] > 0 ||
			$data["KLTown_id"] > 0 ||
			$data["KLStreet_id"] > 0 ||
			strlen($data["Address_Corpus"]) > 0 ||
			strlen($data["Address_House"]) > 0 ||
			strlen($data["Address_Street"]) > 0
		) {
			if ($data["AddressStateType_id"] == 1) {
				if (!empty($data["Person_citizen"]) && ($data["Person_citizen"] == 3)) {
					$filter .= " and PS.KLCountry_id !=643";
				}
				$filter .= " and exists (select AR.Address_id from Address AR ";
				if (!empty($data["Address_Street"])) {
					$filter .= " inner join KLStreet KLS on KLS.KLStreet_id = AR.KLStreet_id ";
				}
				$filter .= " where AR.Address_id = {$alias}.UAddress_id";
				if (!empty($data["Address_Street"])) {
					$filter .= " and upper(KLS.KLStreet_Name) like upper(:Address_Street)||'%' ";
					$queryParams["Address_Street"] = $data["Address_Street"];
				}
				if ($data["KLCountry_id"] > 0) {
					$filter .= " and AR.KLCountry_id = :KLCountry_id";
					$queryParams["KLCountry_id"] = $data["KLCountry_id"];
				}
				if ($data["KLRgn_id"] > 0) {
					$filter .= " and AR.KLRgn_id = :KLRgn_id";
					$queryParams["KLRgn_id"] = $data["KLRgn_id"];
				}
				if ($data["KLSubRgn_id"] > 0) {
					$filter .= " and AR.KLSubRgn_id = :KLSubRgn_id";
					$queryParams["KLSubRgn_id"] = $data["KLSubRgn_id"];
				}
				if ($data["KLCity_id"] > 0) {
					$filter .= " and AR.KLCity_id = :KLCity_id";
					$queryParams["KLCity_id"] = $data["KLCity_id"];
				}
				if ($data["KLTown_id"] > 0) {
					$filter .= " and AR.KLTown_id = :KLTown_id";
					$queryParams["KLTown_id"] = $data["KLTown_id"];
				}
				if ($data["KLStreet_id"] > 0) {
					$filter .= " and AR.KLStreet_id = :KLStreet_id";
					$queryParams["KLStreet_id"] = $data["KLStreet_id"];
				}
				if (strlen($data["Address_House"]) > 0) {
					$filter .= " and AR.Address_House = :Address_House";
					$queryParams["Address_House"] = $data["Address_House"];
				}
				if (strlen($data["Address_Corpus"]) > 0) {
					$filter .= " and AR.Address_Corpus = :Address_Corpus";
					$queryParams["Address_Corpus"] = $data["Address_Corpus"];
				}
				if ($data["KLAreaType_id"] > 0) {
					if (getRegionNumber() == '50' && $data['KLAreaType_id'] == '1') {//#186092
						$filter .= " and AR.KLAreaType_id = 1";
					} else {
						$filter .= " and AR.KLAreaType_id = 2";
					}
					if (getRegionNumber() != '50') {
						$filter .= " and AR.KLAreaType_id = :KLAreaType_id";
					}

					$queryParams['KLAreaType_id'] = $data['KLAreaType_id'];
				}
				$filter .= ") ";
			} else if ($data["AddressStateType_id"] == 2) {
				if (!empty($data["Person_citizen"]) && ($data["Person_citizen"] == 3)) {
					$filter .= " and PS.KLCountry_id !=643";
				}
				$filter .= " and exists (select AP.Address_id from Address AP ";
				if (!empty($data["Address_Street"])) {
					$filter .= " inner join KLStreet KLS on KLS.KLStreet_id = AP.KLStreet_id ";
				}
				$filter .= " where AP.Address_id = {$alias}.PAddress_id";
				if (!empty($data["Address_Street"])) {
					$filter .= " and upper(KLS.KLStreet_Name) like upper(:Address_Street)||'%' ";
					$queryParams["Address_Street"] = $data["Address_Street"];
				}
				if ($data["KLCountry_id"] > 0) {
					$filter .= " and AP.KLCountry_id = :KLCountry_id";
					$queryParams["KLCountry_id"] = $data["KLCountry_id"];
				}
				if ($data["KLRgn_id"] > 0) {
					$filter .= " and AP.KLRgn_id = :KLRgn_id";
					$queryParams["KLRgn_id"] = $data["KLRgn_id"];
				}
				if ($data["KLSubRgn_id"] > 0) {
					$filter .= " and AP.KLSubRgn_id = :KLSubRgn_id";
					$queryParams["KLSubRgn_id"] = $data["KLSubRgn_id"];
				}
				if ($data["KLCity_id"] > 0) {
					$filter .= " and AP.KLCity_id = :KLCity_id";
					$queryParams["KLCity_id"] = $data["KLCity_id"];
				}
				if ($data["KLTown_id"] > 0) {
					$filter .= " and AP.KLTown_id = :KLTown_id";
					$queryParams["KLTown_id"] = $data["KLTown_id"];
				}
				if ($data["KLStreet_id"] > 0) {
					$filter .= " and AP.KLStreet_id = :KLStreet_id";
					$queryParams["KLStreet_id"] = $data["KLStreet_id"];
				}
				if (strlen($data["Address_House"]) > 0) {
					$filter .= " and AP.Address_House = :Address_House";
					$queryParams["Address_House"] = $data["Address_House"];
				}
				if (strlen($data["Address_Corpus"]) > 0) {
					$filter .= " and AP.Address_Corpus = :Address_Corpus";
					$queryParams["Address_Corpus"] = $data["Address_Corpus"];
				}
				if ($data["KLAreaType_id"] > 0) {
					if (getRegionNumber() == '50' && $data['KLAreaType_id'] == '1') {//#186092
						$filter .= " and AP.KLAreaType_id = 1";
					} else {
						$filter .= " and AP.KLAreaType_id = 2";
					}
					if (getRegionNumber() != '50') {
						$filter .= " and AP.KLAreaType_id = :KLAreaType_id";
					}
					$queryParams['KLAreaType_id'] = $data['KLAreaType_id'];
				}
				$filter .= ") ";
			} else {
				if (!empty($data["Person_citizen"]) && ($data["Person_citizen"] == 3)) {
					//список форм, для поиска по KLCountry_id из NationalityStatus
					$searchFormTypeArray = array('EvnPS',
						"EvnSection",
						"EvnDiag",
						"EvnLeave",
						"EvnStick",
						"KvsPerson",
						"KvsPersonCard",
						"KvsEvnDiag",
						"KvsEvnPS",
						"KvsEvnSection",
						"KvsNarrowBed",
						"KvsEvnUsluga",
						"KvsEvnUslugaOB",
						"KvsEvnUslugaAn",
						"KvsEvnUslugaOsl",
						"KvsEvnDrug",
						"KvsEvnLeave",
						"KvsEvnStick");
					if(isset($data["SearchFormType"]) && in_array($data["SearchFormType"], $searchFormTypeArray)) {
						$filter .= " and ((ns.KLCountry_id !=643";
					}
					else {
						$filter .= " and ((PS.KLCountry_id !=643";
					}
				}

				if (empty($data["Person_citizen"]) || ($data["Person_citizen"]!=3)) {
					$filter .= "
						and (
							exists (
								select Address_id
								from Address AR
								where (AR.Address_id = {$alias}.UAddress_id or AR.Address_id = {$alias}.PAddress_id)
					";
				}
				if ($data["KLCountry_id"] > 0 && !($data["KLRgn_id"] > 0 || $data["KLSubRgn_id"] > 0 || $data["KLCity_id"] > 0 || $data["KLTown_id"] > 0 || $data["KLStreet_id"] > 0)) {
					if (!empty($data["Person_citizen"]) && ($data["Person_citizen"] == 3)) {
						$filter .= " and PS.KLCountry_id = :KLCountry_id";
						$queryParams["KLCountry_id"] = $data["KLCountry_id"];
					}
					if (!empty($data["Person_citizen"]) && ($data["Person_citizen"] != 3)) {
						$filter .= " and AR.KLCountry_id = :KLCountry_id";
						$queryParams["KLCountry_id"] = $data["KLCountry_id"];
					}
				}
				if ($data["KLRgn_id"] > 0 && !($data["KLSubRgn_id"] > 0 || $data["KLCity_id"] > 0 || $data["KLTown_id"] > 0 || $data["KLStreet_id"] > 0)) {
					$filter .= " and AR.KLRgn_id = :KLRgn_id";
					$queryParams["KLRgn_id"] = $data["KLRgn_id"];
				}
				if ($data["KLSubRgn_id"] > 0 && !($data["KLCity_id"] > 0 || $data["KLTown_id"] > 0 || $data["KLStreet_id"] > 0)) {
					$filter .= " and AR.KLSubRgn_id = :KLSubRgn_id";
					$queryParams["KLSubRgn_id"] = $data["KLSubRgn_id"];
				}
				if ($data["KLCity_id"] > 0 && !($data["KLTown_id"] > 0 || $data["KLStreet_id"] > 0)) {
					$filter .= " and AR.KLCity_id = :KLCity_id";
					$queryParams["KLCity_id"] = $data["KLCity_id"];
				}
				if ($data["KLTown_id"] > 0 && !($data["KLStreet_id"] > 0)) {
					$filter .= " and AR.KLTown_id = :KLTown_id";
					$queryParams["KLTown_id"] = $data["KLTown_id"];
				}
				if ($data["KLStreet_id"] > 0) {
					$filter .= " and AR.KLStreet_id = :KLStreet_id";
					$queryParams["KLStreet_id"] = $data["KLStreet_id"];
				}
				if (trim($data["Address_House"]) != "") {
					$filter .= " and AR.Address_House = :Address_House";
					$queryParams["Address_House"] = $data["Address_House"];
				}
				if (trim($data["Address_Corpus"]) != "") {
					$filter .= " and AR.Address_Corpus = :Address_Corpus";
					$queryParams["Address_Corpus"] = $data["Address_Corpus"];
				}
				if ($data["KLAreaType_id"] > 0) {
					$filter .= " and AR.KLAreaType_id = :KLAreaType_id";
					$queryParams["KLAreaType_id"] = $data["KLAreaType_id"];
				}
				$filter .= ")) ";
			}
		}
		if ($data["Person_NoPolis"]) {
			$filter .= " and {$alias}.Polis_id is null";
		} else {
			if ((trim($data["Polis_Num"]) != "") || (trim($data["Polis_Ser"]) != "") || ($data["PolisType_id"] > 0) || ($data["OrgSmo_id"] > 0) || ($data["OMSSprTerr_id"] > 0) || $data["Person_NoOrgSMO"]) {
				$filter .= "
					and exists(
						select Polis_id
						from
							Polis
							left join OmsSprTerr on OmsSprTerr.OmsSprTerr_id = Polis.OmsSprTerr_id
						where Polis.Polis_id = {$alias}.Polis_id
				";
				if ($data["OMSSprTerr_id"] > 0) {
					if ($data["OMSSprTerr_id"] == 100500) {
						$sessionRegionNumber = @$data["session"]["region"]["number"];
						if (isset($data["session"]["region"]) && isset($sessionRegionNumber) && $sessionRegionNumber > 0) {
							$filter .= " and OmsSprTerr.KLRgn_id <> {$sessionRegionNumber}";
						}
					} else {
						$filter .= " and Polis.OmsSprTerr_id = :OMSSprTerr_id";
					}
					$queryParams["OMSSprTerr_id"] = $data["OMSSprTerr_id"];
				}
				if ($data["Person_NoOrgSMO"]) {
					$filter .= " and Polis.OrgSmo_id is null";
				} elseif ($data["OrgSmo_id"] > 0) {
					$filter .= " and Polis.OrgSmo_id = :OrgSmo_id";
					$queryParams["OrgSmo_id"] = $data["OrgSmo_id"];
				}
				if (trim($data["Polis_Num"]) != "") {
					$filter .= " and Polis.Polis_Num = :Polis_Num";
					$queryParams["Polis_Num"] = $data["Polis_Num"];
				}
				if (trim($data["Polis_Ser"]) != "") {
					$filter .= " and Polis.Polis_Ser = :Polis_Ser";
					$queryParams["Polis_Ser"] = $data["Polis_Ser"];
				}
				if ($data["PolisType_id"] > 0) {
					$filter .= " and Polis.PolisType_id = :PolisType_id";
					$queryParams["PolisType_id"] = $data["PolisType_id"];
				}
				$filter .= ") ";
			}
		}
	}

	/**
	 * @return array
	 */
	public static function getDbf1Array()
	{
		return [
			"EPLPerson" => [
				["PCT_ID", "N", 15, 8],
				["P_ID", "N", 15, 8],
				["SURNAME", "C", 50],
				["FIRNAME", "C", 50],
				["SECNAME", "C", 50],
				["BIRTHDAY", "D", 8],
				["SNILS", "C", 11],
				["INV_N", "N", 1, 0],
				["INV_DZ", "C", 5],
				["INV_DATA", "D", 8],
				["SEX", "C", 15],
				["SOC", "C", 30],
				["P_TERK", "C", 10],
				["P_TER", "C", 30],
				["P_NAME", "C", 10],
				["P_SER", "C", 10],
				["P_NUM", "C", 30],
				["P_NUMED", "C", 30],
				["P_DATA", "D", 8],
				["SMOK", "C", 10],
				["SMO", "C", 100],
				["AR_TP", "C", 20],
				["AR_IDX", "C", 10],
				["AR_LND", "C", 50],
				["AR_RGN", "C", 50],
				["AR_RN", "C", 50],
				["AR_CTY", "C", 50],
				["AR_NP", "C", 50],
				["AR_STR", "C", 50],
				["AR_DOM", "C", 5],
				["AR_K", "C", 5],
				["AR_KV", "C", 5],
				["AP_TP", "C", 20],
				["AP_IDX", "C", 10],
				["AP_LND", "C", 50],
				["AP_RGN", "C", 50],
				["AP_RN", "C", 50],
				["AP_CTY", "C", 50],
				["AP_NP", "C", 50],
				["AP_STR", "C", 50],
				["AP_DOM", "C", 5],
				["AP_K", "C", 5],
				["AP_KV", "C", 5],
				["D_TIP", "C", 60],
				["D_SER", "C", 10],
				["D_NOM", "C", 30],
				["D_OUT", "C", 100],
				["D_DATA", "D", 8]
			],
			"EvnPL" => [
				["EPL_ID", "N", 18, 0],
				["PCT_ID", "N", 18, 0],
				["DIR_CODE", "N", 2, 0],
				["PDO_CODE", "C", 10],
				["SETDATE", "D", 8],
				["SETTIME", "C", 5],
				["DISDATE", "D", 8],
				["DISTIME", "C", 5],
				["NUMCARD", "C", 10],
				["VPERV", "N", 1, 0],
				["KATEGOR", "N", 1, 0],
				["OGRN", "C", 15],
				["FINISH_ID", "N", 1, 0],
				["RSC_COD", "N", 2, 0],
				["DZ_COD", "C", 5, 0],
				["DZ_NAM", "C", 200],
				["DST_COD", "N", 10, 0],
				["UKL", "N", 3, 2],
				["CODE_NAP", "N", 1, 0],
				["KUDA_NAP", "C", 10],
				["INVALID", "N", 1, 0],
				["REG_PERM", "N", 1, 0],
				["BDZ", "C", 3]
			],
			"EvnVizitPL" => [
				["EPL_ID", "N", 18, 0],
				["PCT_ID", "N", 18, 0],
				["EVZ_ID", "N", 18, 0],
				["NUMCARD", "C", 10, 0],
				["SETDATE", "D", 8, 0],
				["SETTIME", "C", 5, 0],
				["PERVVTOR", "N", 1, 0],
				["LS_COD", "N", 9, 0],
				["LS_NAM", "C", 50, 0],
				["MP_COD", "C", 20, 0],
				["MP_FIO", "C", 100, 0],
				["PAY_COD", "N", 10, 0],
				["VZT_COD", "N", 10, 0],
				["SRT_COD", "N", 10, 0],
				["PRG_COD", "N", 2, 0],
				["DZ_COD", "C", 5, 0],
				["DZ_NAM", "C", 200, 0],
				["DST_COD", "N", 10, 0]
			],
			"EvnUsluga" => [
				["EPL_ID", "N", 18, 0], // EvnPL_id
				["PCT_ID", "N", 18, 0],
				["EVZ_ID", "N", 18, 0], // EvnVizitPL_id
				["EUS_ID", "N", 18, 0], // EvnUsluga_id
				["EU_CLASS", "C", 20], // EvnClass_SysNick
				["SETDATE", "D", 8], // EvnUsluga_setDate
				["SETTIME", "C", 5], // EvnUsluga_setTime
				["USL_CODE", "C", 20], // Usluga_Code
				["KOLVO", "N", 3, 3], // EvnUsluga_Kolvo
				["UP_CODE", "N", 1, 0], // UslugaPlace_Code
				["MP_CODE", "C", 5], // MedPersonal_Code
				["PT_CODE", "N", 1, 0] // PayType_Code
			],
			"EvnAgg" => [
				["EUS_ID", "N", 18, 0], // EvnUsluga_id
				["PCT_ID", "N", 18, 0],
				["EAGG_ID", "N", 18, 0], // EvnAgg_id
				["SETDATE", "D", 8], // EvnAgg_setDate
				["SETTIME", "C", 5], // EvnAgg_setTime
				["AW_CODE", "N", 1, 0], // AggWhen_Code
				["AT_CODE", "N", 1, 0] // AggType_Code
			]
		];
	}

	/**
	 * @return array
	 */
	public static function getDbf2Array()
	{
		return [
			"EPLStomPerson" => [
				["PCT_ID", "N", 15, 8],
				["P_ID", "N", 15, 8],
				["SURNAME", "C", 50],
				["FIRNAME", "C", 50],
				["SECNAME", "C", 50],
				["BIRTHDAY", "D", 8],
				["SNILS", "C", 11],
				["INV_N", "N", 1, 0],
				["INV_DZ", "C", 5],
				["INV_DATA", "D", 8],
				["SEX", "C", 15],
				["SOC", "C", 30],
				["P_TERK", "C", 10],
				["P_TER", "C", 30],
				["P_NAME", "C", 10],
				["P_SER", "C", 10],
				["P_NUM", "C", 30],
				["P_NUMED", "C", 30],
				["P_DATA", "D", 8],
				["SMOK", "C", 10],
				["SMO", "C", 100],
				["AR_TP", "C", 20],
				["AR_IDX", "C", 10],
				["AR_LND", "C", 50],
				["AR_RGN", "C", 50],
				["AR_RN", "C", 50],
				["AR_CTY", "C", 50],
				["AR_NP", "C", 50],
				["AR_STR", "C", 50],
				["AR_DOM", "C", 5],
				["AR_K", "C", 5],
				["AR_KV", "C", 5],
				["AP_TP", "C", 20],
				["AP_IDX", "C", 10],
				["AP_LND", "C", 50],
				["AP_RGN", "C", 50],
				["AP_RN", "C", 50],
				["AP_CTY", "C", 50],
				["AP_NP", "C", 50],
				["AP_STR", "C", 50],
				["AP_DOM", "C", 5],
				["AP_K", "C", 5],
				["AP_KV", "C", 5],
				["D_TIP", "C", 60],
				["D_SER", "C", 10],
				["D_NOM", "C", 30],
				["D_OUT", "C", 100],
				["D_DATA", "D", 8]
			],
			"EvnPLStom" => [
				["EPL_ID", "N", 18, 0],
				["PCT_ID", "N", 18, 0],
				["DIR_CODE", "N", 2, 0],
				["PDO_CODE", "C", 10],
				["SETDATE", "D", 8],
				["SETTIME", "C", 5],
				["DISDATE", "D", 8],
				["DISTIME", "C", 5],
				["NUMCARD", "C", 10],
				["VPERV", "N", 1, 0],
				["KATEGOR", "N", 1, 0],
				["OGRN", "C", 15],
				["FINISH_ID", "N", 1, 0],
				["RSC_COD", "N", 2, 0],
				["DZ_COD", "C", 5, 0],
				["DZ_NAM", "C", 200],
				["DST_COD", "N", 10, 0],
				["UKL", "N", 3, 2],
				["CODE_NAP", "N", 1, 0],
				["KUDA_NAP", "C", 10],
				["INVALID", "N", 1, 0],
				["REG_PERM", "N", 1, 0],
				["BDZ", "C", 3]
			],
			"EvnVizitPLStom" => [
				["EPL_ID", "N", 18, 0],
				["PCT_ID", "N", 18, 0],
				["EVZ_ID", "N", 18, 0],
				["NUMCARD", "C", 10, 0],
				["SETDATE", "D", 8, 0],
				["SETTIME", "C", 5, 0],
				["PERVVTOR", "N", 1, 0],
				["LS_COD", "N", 9, 0],
				["LS_NAM", "C", 50, 0],
				["MP_COD", "C", 20, 0],
				["MP_FIO", "C", 100, 0],
				["PAY_COD", "N", 10, 0],
				["VZT_COD", "N", 10, 0],
				["SRT_COD", "N", 10, 0],
				["PRG_COD", "N", 2, 0],
				["DZ_COD", "C", 5, 0],
				["DZ_NAM", "C", 200, 0],
				["DST_COD", "N", 10, 0]
			],
			"EvnUslugaStom" => [
				["EPL_ID", "N", 18, 0], // EvnPL_id
				["PCT_ID", "N", 18, 0],
				["EVZ_ID", "N", 18, 0], // EvnVizitPL_id
				["EUS_ID", "N", 18, 0], // EvnUsluga_id
				["EU_CLASS", "C", 20], // EvnClass_SysNick
				["SETDATE", "D", 8], // EvnUsluga_setDate
				["SETTIME", "C", 5], // EvnUsluga_setTime
				["USL_CODE", "C", 20], // Usluga_Code
				["KOLVO", "N", 3, 3], // EvnUsluga_Kolvo
				["UP_CODE", "N", 1, 0], // UslugaPlace_Code
				["MP_CODE", "C", 5], // MedPersonal_Code
				["PT_CODE", "N", 1, 0] // PayType_Code
			],
			"EvnAggStom" => [
				["EUS_ID", "N", 18, 0], // EvnUsluga_id
				["PCT_ID", "N", 18, 0],
				["EAGG_ID", "N", 18, 0], // EvnAgg_id
				["SETDATE", "D", 8], // EvnAgg_setDate
				["SETTIME", "C", 5], // EvnAgg_setTime
				["AW_CODE", "N", 1, 0], // AggWhen_Code
				["AT_CODE", "N", 1, 0] // AggType_Code
			]
		];
	}

	/**
	 * @return array
	 */
	public static function getDbf3Array()
	{
		return [
			"KvsPerson" => [
				["PCT_ID", "N", 15, 8],
				["P_ID", "N", 15, 8],
				["SURNAME", "C", 50],
				["FIRNAME", "C", 50],
				["SECNAME", "C", 50],
				["BIRTHDAY", "D", 8],
				["SNILS", "C", 11],
				["INV_N", "N", 1, 0],
				["INV_DZ", "C", 5],
				["INV_DATA", "D", 8],
				["SEX", "C", 15],
				["SOC", "C", 30],
				["P_TERK", "C", 10],
				["P_TER", "C", 30],
				["P_NAME", "C", 10],
				["P_SER", "C", 10],
				["P_NUM", "C", 30],
				["P_NUMED", "C", 30],
				["P_DATA", "D", 8],
				["SMOK", "C", 10],
				["SMO", "C", 100],
				["AR_TP", "C", 20],
				["AR_IDX", "C", 10],
				["AR_LND", "C", 50],
				["AR_RGN", "C", 50],
				["AR_RN", "C", 50],
				["AR_CTY", "C", 50],
				["AR_NP", "C", 50],
				["AR_STR", "C", 50],
				["AR_DOM", "C", 5],
				["AR_K", "C", 5],
				["AR_KV", "C", 5],
				["AP_TP", "C", 20],
				["AP_IDX", "C", 10],
				["AP_LND", "C", 50],
				["AP_RGN", "C", 50],
				["AP_RN", "C", 50],
				["AP_CTY", "C", 50],
				["AP_NP", "C", 50],
				["AP_STR", "C", 50],
				["AP_DOM", "C", 5],
				["AP_K", "C", 5],
				["AP_KV", "C", 5],
				["D_TIP", "C", 60],
				["D_SER", "C", 10],
				["D_NOM", "C", 30],
				["D_OUT", "C", 100],
				["D_DATA", "D", 8]
			],
			"KvsPersonCard" => [
				["REG_ID", "N", 15, 8],
				["PCT_ID", "N", 15, 8],
				["P_ID", "N", 15, 8],
				["PR_AK", "C", 10],
				["PR_TP", "C", 12],
				["PR_DATA", "D", 8],
				["LPUK", "C", 10],
				["LPU", "C", 150],
				["TPLOT", "C", 30],
				["LOT", "C", 30]
			],
			"KvsEvnDiag" => [
				["DZ_ID", "N", 15, 8],
				["GSP_ID", "N", 15, 8],
				["PCT_ID", "N", 15, 8],
				["P_ID", "N", 15, 8],
				["KTO", "N", 1, 0],
				["LPUK", "C", 10],
				["LPU", "C", 150],
				["OTDK", "C", 10],
				["OTD", "C", 100],
				["DZ_DATA", "D", 8],
				["DZ_W", "C", 25],
				["DZ_T", "C", 25],
				["DZ_DZ", "C", 5]
			],
			"KvsEvnPS" => [
				["GSP_ID", "N", 15, 8],
				["PCT_ID", "N", 15, 8],
				["P_ID", "N", 15, 8],
				["KARTPR", "N", 1, 0],
				["KART", "C", 10],
				["WOPL", "C", 10],
				["DATAPOST", "D", 8],
				["TIMEPOST", "C", 10],
				["AGEPOST", "N", 3, 0],
				["KN_KT", "C", 25],
				["KN_OTDLK", "C", 10],
				["KN_OTDL", "C", 100],
				["KN_ORGK", "C", 10],
				["KN_ORG", "C", 100],
				["KN_FD", "N", 1, 0],
				["KN_N", "C", 10],
				["KN_DATA", "D", 8],
				["KD_KT", "C", 20],
				["KD_KOD", "C", 10],
				["KD_NN", "C", 10],
				["DZGOSP", "C", 5],
				["DEF_NG", "N", 1, 0],
				["DEF_NOO", "N", 1, 0],
				["DEF_NTL", "N", 1, 0],
				["DEF_ND", "N", 1, 0],
				["ALKO", "N", 1, 0],
				["PR_GP", "C", 25],
				["PR_N", "N", 2, 0],
				["PR_W", "C", 10],
				["TR_T", "C", 30],
				["TR_P", "N", 1, 0],
				["TR_N", "N", 1, 0],
				["PRO_NK", "C", 10],
				["PRO_N", "C", 100],
				["PRO_DOCK", "C", 10],
				["PRO_DOC", "C", 35],
				["PRO_DZ", "C", 5]
			],
			"KvsEvnSection" => [
				["HSTRY_ID", "N", 15, 8],
				["GSP_ID", "N", 15, 8],
				["PCT_ID", "N", 15, 8],
				["P_ID", "N", 15, 8],
				["DATAP", "D", 8],
				["TIMEP", "C", 10],
				["DATAW", "D", 8],
				["TIMEW", "C", 10],
				["OTDLK", "C", 10],
				["OTDL", "C", 100],
				["WO", "C", 25],
				["WT", "C", 25],
				["DOCK", "C", 10],
				["DOC", "C", 35],
				["DZ", "C", 5],
				["MES", "C", 20],
				["NORM", "N", 3, 0],
				["KDN", "N", 5, 0]
			],
			"KvsNarrowBed" => [
				["UK_ID", "N", 15, 8],
				["PCT_ID", "N", 15, 8],
				["P_ID", "N", 15, 8],
				["GSP_ID", "N", 15, 8],
				["HSTRY_ID", "N", 15, 8],
				["DATAP", "D", 8],
				["DATAW", "D", 8],
				["OTDLK", "C", 10],
				["OTDL", "C", 100]
			],
			"KvsEvnUsluga" => [
				["U_ID", "N", 15, 8],
				["PCT_ID", "N", 15, 8],
				["P_ID", "N", 15, 8],
				["GSP_ID", "N", 15, 8],
				["U_TIP", "C", 35, 0],
				["U_DATA", "D", 8],
				["U_TIME", "C", 10],
				["U_MESTO", "C", 100],
				["U_OTELK", "C", 10],
				["U_OTEL", "C", 100],
				["U_LPUK", "C", 10],
				["U_LPU", "C", 100],
				["U_ORGK", "C", 10],
				["U_ORG", "C", 100],
				["U_DOCK", "C", 10],
				["U_DOC", "C", 35],
				["U_USLKOD", "N", 10, 0],
				["U_USL", "C", 100],
				["U_WO", "C", 25],
				["U_TIPOP", "N", 2, 0],
				["U_KATSLOJ", "N", 2, 0],
				["U_PREND", "N", 2, 0],
				["U_PRLAS", "N", 2, 0],
				["U_PRKRI", "N", 2, 0],
				["U_KOL", "N", 5, 0]
			],
			"KvsEvnUslugaOB" => [
				["U_ID", "N", 15, 8],
				["PCT_ID", "N", 15, 8],
				["P_ID", "N", 15, 8],
				["GSP_ID", "N", 15, 8],
				["U_WID", "C", 35],
				["U_DOCK", "C", 10],
				["U_DOC", "C", 35]
			],
			"KvsEvnUslugaAn" => [
				["U_ID", "N", 15, 8],
				["PCT_ID", "N", 15, 8],
				["P_ID", "N", 15, 8],
				["GSP_ID", "N", 15, 8],
				["U_ANEST", "C", 35]
			],
			"KvsEvnUslugaOsl" => [
				["U_ID", "N", 15, 8],
				["PCT_ID", "N", 15, 8],
				["P_ID", "N", 15, 8],
				["GSP_ID", "N", 15, 8],
				["U_DATA", "D", 8],
				["U_TIME", "C", 10],
				["U_WID", "C", 100],
				["U_KONT", "C", 100]
			],
			"KvsEvnDrug" => [
				["MED_ID", "N", 15, 8],
				["PCT_ID", "N", 15, 8],
				["P_ID", "N", 15, 8],
				["GSP_ID", "N", 15, 8],
				["M_DATA", "D", 8],
				["M_OTDLK", "C", 10],
				["M_OTDL", "C", 100],
				["M_MOL", "C", 100],
				["MEDK", "C", 10],
				["MED", "C", 100],
				["M_PART", "C", 100],
				["M_KOL", "N", 10, 0],
				["M_EU", "C", 10],
				["M_EU_OCT", "N", 10, 0],
				["M_EU_KOL", "N", 10, 0],
				["M_ED", "C", 10],
				["M_ED_OCT", "N", 10, 0],
				["M_ED_KOL", "N", 10, 0],
				["M_CENA", "N", 10, 0],
				["M_SUM", "N", 10, 0],
			],
			"KvsEvnLeave" => [
				["ISCH_ID", "N", 15, 8],
				["PCT_ID", "N", 15, 8],
				["P_ID", "N", 15, 8],
				["GSP_ID", "N", 15, 8],
				["IG_W", "C", 30],
				["IS_DATA", "D", 8],
				["IS_TIME", "C", 10],
				["IS_URUW", "C", 5],
				["IS_BOL", "C", 50],
				["IS_PR", "C", 20],
				["IS_NAPR", "N", 1, 0],
				["IS_LPUK", "C", 10],
				["IS_LPU", "C", 100],
				["IS_TS", "C", 50],
				["IS_STACK", "C", 10],
				["IS_STAC", "C", 100],
				["IS_DOCK", "C", 10],
				["IS_DOC", "C", 100],
				["IS_DZ", "C", 5]
			],
			"KvsEvnStick" => [
				["LWN_ID", "N", 15, 8],
				["PCT_ID", "N", 15, 8],
				["P_ID", "N", 15, 8],
				["GSP_ID", "N", 15, 8],
				["PORYAD", "C", 20],
				["LWNOLD", "C", 20],
				["LWN_S", "C", 20],
				["LWN_N", "C", 20],
				["LWN_D", "D", 8],
				["LWN_PR", "C", 50],
				["ROD_FIO", "C", 50],
				["ROD_W", "N", 2, 0],
				["ROD_POL", "C", 15],
				["SKL_DN", "D", 8],
				["SKL_DK", "D", 8],
				["SKL_NOM", "C", 20],
				["SKL_LPU", "C", 100],
				["LWN_R", "C", 20],
				["LWN_ISCH", "C", 50],
				["LWN_SP", "C", 20],
				["LWN_NP", "C", 20],
				["LWN_DR", "D", 8],
				["LWN_DOCK", "C", 10],
				["LWN_DOC", "C", 100],
				["LWN_LPUK", "C", 10],
				["LWN_LPU", "C", 100],
				["LWN_DZ1", "C", 5],
				["LWN_DZ2", "C", 5]
			]
		];
	}

	public static function functionRefactorOrder($data, $orderby, $dbf, $print)
	{
		$query = "";
		switch ($data["SearchFormType"]) {
			case "CmpCallCard":
			case "CmpCloseCard":
				$query .= "
					order by
						-- order by
						PS.Person_Surname,
						CCC.Person_SurName,
						PS.Person_Firname,
						CCC.Person_FirName,
						PS.Person_Secname,
						CCC.Person_SecName
						-- end order by
				";
				break;
			case "EvnPL":
				$query .= "
					order by
						-- order by
						EPL.EvnPL_id
						-- end order by
				";
				break;
			case "EvnVizitPL":
				if ($dbf == true)
					$query .= "
						order by
							-- order by
							5
							-- end order by
					";
				else
					$query .= "
						order by
							-- order by
							EVizitPL.EvnVizitPL_setDate DESC
							-- end order by
					";
				break;
			case "EvnPLStom":
				$query .= "
					order by
						-- order by
						EPLS.EvnPLStom_id
						-- end order by
				";
				break;
			case "EvnVizitPLStom":
				$query .= "
					order by
						-- order by
						EVPLS.EvnVizitPLStom_setDate DESC
						-- end order by
				";
				break;
			case "EvnSection":
			case "EvnPS":
				if ($print === true) {
					$query .= "
						order by
							-- order by
							PS.Person_SurName,
							PS.Person_FirName,
							PS.Person_SecName
							-- end order by
					";
				} else {
					$query .= "
						order by
							-- order by
							EPS.EvnPS_id
							-- end order by
					";
				}
				break;
			case "EvnDtpWound":
				$query .= "
					order by
						-- order by
						EDW.EvnDtpWound_id
						-- end order by
				";
				break;
			case "EvnDtpDeath":
				$query .= "
					order by
						-- order by
						EDD.EvnDtpDeath_id
						-- end order by
				";
				break;
			case "EvnUslugaPar":
				$query .= "
					order by
						-- order by
						EUP.EvnUslugaPar_id
						-- end order by
				";
				break;
			case "EvnRecept":
				$query .= "
					order by
						-- order by
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName,
						ER.EvnRecept_Num
						-- end order by
					limit 10000
				";
				break;
			case "EvnReceptGeneral":
				$query .= "
					order by
						-- order by
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName,
						ERG.EvnReceptGeneral_Num
						-- end order by
					limit 10000
				";
				break;
			case "PersonDopDisp":
				$query .= "
					group by
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName,
						DD.PersonDopDisp_id,
						PS.Person_id,
						PS.Server_id,
						epldd.Server_id,
						PS.PersonEvn_id,
						epldd.PersonEvn_id,
						Person_Birthday,
						Sex.Sex_Name,
						PS.Polis_Ser,
						PS.Polis_Num,
						okved1.Okved_Name,
						org1.Org_OGRN,
						astat1.KLArea_Name,
						astat2.KLArea_Name,
						addr1.Address_Nick,
						otherddlpu.Lpu_Nick
						" . (allowPersonEncrypHIV($data["session"]) ? ",PEH.PersonEncrypHIV_id, PEH.PersonEncrypHIV_Encryp" : "") . "
					-- end where
					order by
						-- order by
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName
						-- end order by
				";
				break;
			case "PersonDopDispPlan":
				$query .= "
					group by
						PS.Person_id,
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName,
						Sex.Sex_Name,
						PS.Person_Birthday,
						IsPersonDopDispPassed.EvnPLDisp_setDate,
						IsPersonDopDispPassed.EvnPLDisp_disDate
					-- end where
					order by
						-- order by
						{$orderby}
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName
						-- end order by
				";
				break;
			case "PersonDispOrp":
			case "PersonDispOrpPeriod":
			case "PersonDispOrpPred":
			case "PersonDispOrpProf":
			case "PersonDispOrpOld":
			case "EvnPLDispDop":
			case "EvnPLDispDop13Sec":
			case "EvnPLDispProf":
			case "EvnPLDispScreen":
			case "EvnPLDispScreenChild":
			case "EvnPLDispDopStream":
			case "EvnPLDispTeen14":
			case "EvnPLDispTeen14Stream":
			case "EvnPLDispOrp":
			case "EvnPLDispOrpOld":
			case "EvnPLDispOrpSec":
			case "EvnPLDispTeenInspectionPeriod":
			case "EvnPLDispTeenInspectionProf":
			case "EvnPLDispTeenInspectionPred":
			case "EvnPLDispOrpStream":
			case "EvnPLDispMigrant":
			case "EvnPLDispDriver":
			case "PersonDisp":
			case "PersonCardStateDetail":
			case "WorkPlacePolkaReg":
			case "PersonCallCenter":
			case "PersonCard":
			case "PersonPrivilegeWOW":
			case "EvnPLWOW":
				$query .= "
					order by
						-- order by
						{$orderby}
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName
						-- end order by
				";
				break;
			case "EvnPLDispDop13":
				$query .= "
					order by
						-- order by
							EPLDD13.Person_SurName,
							EPLDD13.Person_FirName,
							EPLDD13.Person_SecName
						-- end order by
				";
				break;
			case "EvnNotifyHepatitis":
				$query .= "
					order by
						-- order by
						ENH.EvnNotifyHepatitis_setDT DESC
						-- end order by
				";
				break;
			case "EvnOnkoNotify":
				$query .= "
					order by
						-- order by
						EON.EvnOnkoNotify_setDT DESC
						-- end order by
				";
				break;
			case "EvnNotifyRegister":
				$query .= "
					order by
						-- order by
						E.Evn_setDT DESC
						-- end order by
				";
				break;
			case "EvnNotifyOrphan":
				$query .= "
					order by
						-- order by
						ENO.EvnNotifyOrphan_setDT DESC
						-- end order by
				";
				break;
			case "EvnNotifyCrazy":
				$query .= "
					order by
						-- order by
						ENC.EvnNotifyCrazy_setDT DESC
						-- end order by
				";
				break;
			case "EvnNotifyNarko":
				$query .= "
					order by
						-- order by
						ENC.EvnNotifyNarco_setDT DESC
						-- end order by
				";
				break;
			case "EvnNotifyTub":
				$query .= "
					order by
						-- order by
						ENC.EvnNotifyTub_setDT DESC
						-- end order by
				";
				break;
			case "EvnNotifyNephro":
				$query .= "
					order by
						-- order by
						ENC.EvnNotifyNephro_setDT DESC
						-- end order by
				";
				break;
			case "EvnNotifyProf":
				$query .= "
					order by
						-- order by
						ENC.EvnNotifyProf_setDT DESC
						-- end order by
				";
				break;
			case "PalliatNotify":
			case "EvnNotifyHIV":
				$query .= "
					order by
						-- order by
						ENB.EvnNotifyBase_setDT DESC
						-- end order by
				";
				break;
			case "EvnNotifyVener":
				$query .= "
					order by
						-- order by
						ENC.EvnNotifyVener_setDT DESC
						-- end order by
				";
				break;
			case "OnkoRegistry":
				$query .= "
					group by
						PR.PersonRegister_id,
						PR.Lpu_iid,
						PR.MedPersonal_iid,
						PR.EvnNotifyBase_id,
						EONN.EvnOnkoNotifyNeglected_id,	
						MOV.MorbusOnkoVizitPLDop_id,
						MOL.MorbusOnkoLeave_id,
						MO.MorbusOnko_id,
						M.Morbus_id,
						PS.Person_id,
						PS.Server_id,
						PS.PersonEvn_id,
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName,
						PS.Person_Birthday,
						Lpu.Lpu_Nick,
						Diag.Diag_id,
						Diag.diag_FullName,
						PROUT.PersonRegisterOutCause_id,
						PROUT.PersonRegisterOutCause_Name,
						OnkoDiag.OnkoDiag_Code,
						OnkoDiag.OnkoDiag_Name,
						MO.MorbusOnko_IsMainTumor,
						TumorStage.TumorStage_Name,
						MO.MorbusOnko_setDiagDT,
						PR.PersonRegister_setDate,
						PR.PersonRegister_disDate
					-- end where
					order by
						-- order by
						PR.PersonRegister_setDate DESC,
						PR.PersonRegister_id
						-- end order by
				";
				break;
			case "BskRegistry":
				$query .= "
					ORDER BY
					-- order by
						BSKRegistry_setDate DESC
					-- end order by";
				break;
			case "IPRARegistry":
			case "ECORegistry":
			case "HepatitisRegistry":
			case "OrphanRegistry":
			case "ACSRegistry":
			case "CrazyRegistry":
			case "NarkoRegistry":
			case "TubRegistry":
			case "NephroRegistry":
			case "EndoRegistry":
			case "IBSRegistry":
			case "ProfRegistry":
			case "FmbaRegistry":
			case "DiabetesRegistry":
			case "LargeFamilyRegistry":
			case "HIVRegistry":
			case "VenerRegistry":
			case "PalliatRegistry":
			case "PersonRegisterBase":
			case "GeriatricsRegistry":
			case "GibtRegistry":
				$query .= "
					order by
						-- order by
						PR.PersonRegister_setDate DESC,
						PR.PersonRegister_id
						-- end order by
				";
				break;
			case "ONMKRegistry":
				$query .= "
					order by
						-- order by
						ONMKR.ONMKRegistry_EvnDT DESC,
						PR.PersonRegister_id
						-- end order by
				";
				break;
			case "ReabRegistry":
			case "AdminVIPPerson":
			case "ZNOSuspectRegistry":
				$query .= "
					order by
						-- order by
						\"Person_Surname\" asc,
						\"Person_Firname\" asc,
						\"Person_Secname\" asc
						-- end order by
				";
				break;
			case "EvnERSBirthCertificate":
				$query .= "
					order by
						-- order by
						ers.EvnERSBirthCertificate_CreateDate desc
						-- end order by
				";
				break;
			case "EvnInfectNotify":
				$query .= "
					group by
						EIN.EvnInfectNotify_id,
						EIN.EvnInfectNotify_insDT,
						PS.Person_id,
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName,
						PS.Person_Birthday,
						Lpu.Lpu_Nick,
						pc.lpu_id,
						Diag.diag_FullName,
						Diag1.diag_FullName
					-- end where
					order by
						-- order by
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName
						-- end order by
				";
				break;
			case "PersonPrivilege":
				$query .= "
					order by
						-- order by
						Person_Surname,
						Person_Firname,
						Person_Secname,
						PP.PersonPrivilege_id
						-- end order by
				";
				break;
			case "HTMRegister":
				$query .= "
					order by
						-- order by
							hr.HTMRegister_Number
						-- end order by
				";
				break;
			case 'RzhdRegistry':
				$query .= "
					order by
						-- order by
							R.Register_setDate DESC
						-- end order by
				";
				break;
			case "ReanimatRegistry":
				$query .= "
					order by
						-- order by
						RR.ReanimatRegister_IsPeriodNow  desc,
						ERP.selrow desc,
						RR.ReanimatRegister_setDate DESC,
						RR.ReanimatRegister_id
						-- end order by
				";
				break;
			case "RegisterSixtyPlus":
				$query .= "
					order by
						-- order by
						(case when (RPlus.RegisterSixtyPlus_IMTMeasure is null or RPlus.RegisterSixtyPlus_IMTMeasure::float8 < 25.0 ) then 0
								 when (RPlus.RegisterSixtyPlus_IMTMeasure::float8 >= 25.0 and RPlus.RegisterSixtyPlus_IMTMeasure::float8 < 30.0) then 1
								 when RPlus.RegisterSixtyPlus_IMTMeasure::float8 >= 30.0 then 2 end 
								 +
							case when (RPlus.RegisterSixtyPlus_CholesterolMeasure is null or replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.')::float8 < 5.1) then 0
								 when (replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.')::float8 >= 5.1 and replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.')::float8 < 7.1 ) then 1 
								 when replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.')::float8 >= 7.1 then 2 end
								 +
							case when (RPlus.RegisterSixtyPlus_GlucoseMeasure is null or replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.')::float8 < 6.2) then 0
								 when (replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.')::float8 >= 6.2 and replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.')::float8 < 7.0 ) then 1 
								 when replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.')::float8 >= 7.0 then 2 end
								) desc,
						rg.LpuRegion_Name,
						LpuRegionFap.LpuRegion_Name
						-- end order by
				";
				break;
			case "SportRegistry":
				$query .= "
					order by
						-- order by
						SRUMO.SportRegisterUMO_UMODate desc
						-- end order by";
				break;
		}
		return $query;
	}
}
