<?php


class Polka_PersonDisp_model_get
{
	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonDispHistoryList(Polka_PersonDisp_model $callObject, $data)
	{
		$filter = "";
		$diag_filters = getAccessRightsDiagFilter("v_Diag.Diag_Code", true);
		if (count($diag_filters) > 0) {
			$filter .= "and " . implode(" and ", $diag_filters);
		}
		$sql = "
			select
				PersonDisp_id as \"PersonDisp_id\",
				to_char(PersonDisp_begDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_begDate\",
				to_char(PersonDisp_endDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_endDate\",
				v_Diag.Diag_Code as \"Diag_Code\",
				v_Lpu.Lpu_Nick as \"Lpu_Nick\",
				v_LpuSection.LpuSection_Name as \"LpuSection_Name\",
				v_LpuRegion.LpuRegion_Name as \"LpuRegion_Name\",
				v_MedPersonal.Person_Fio as \"MedPersonal_FIO\",
				CASE WHEN v_PersonDisp.Lpu_id = :Lpu_id THEN 2 ELSE 1 END as \"IsOurLpu\"
			from
				v_PersonDisp
				left join v_Diag on v_PersonDisp.Diag_id = v_Diag.Diag_id
				left join v_Lpu on v_PersonDisp.Lpu_id = v_Lpu.Lpu_id
				left join v_MedPersonal on v_PersonDisp.MedPersonal_id = v_MedPersonal.MedPersonal_id
				left join v_LpuRegion on v_PersonDisp.LpuRegion_id = v_LpuRegion.LpuRegion_id
				left join v_LpuSection on v_PersonDisp.LpuSection_id = v_LpuSection.LpuSection_id
			where v_PersonDisp.Person_id = :Person_id
				{$filter}
			order by
				PersonDisp_begDate,
				PersonDisp_endDate
		";
		$sqlParams = [
			"Lpu_id" => $data["Lpu_id"],
			"Person_id" => $data["Person_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $PersonDisp_id
	 * @return array|bool
	 */
	public static function getPersonDispMedicamentList(Polka_PersonDisp_model $callObject, $PersonDisp_id)
	{
		$sql = "
			select
            	PersonDispMedicament.PersonDispMedicament_id as \"PersonDispMedicament_id\",
				PersonDispMedicament.PersonDisp_id as \"PersonDisp_id\",
				PersonDispMedicament.Drug_id as \"Drug_id\",
				Drug.DrugMnn_id as \"DrugMnn_id\",
				Drug.Drug_Name as \"Drug_Name\",
				DrugState_Price as \"Drug_Price\",
				PersonDispMedicament.PersonDispMedicament_Norma as \"Drug_Count\",
				to_char(PersonDispMedicament.PersonDispMedicament_begDate, '{$callObject->dateTimeForm104}') as \"PersonDispMedicament_begDate\",
				to_char(PersonDispMedicament.PersonDispMedicament_endDate, '{$callObject->dateTimeForm104}') as \"PersonDispMedicament_endDate\"
			from
				PersonDispMedicament
				left join DrugState on PersonDispMedicament.Drug_id = DrugState.Drug_id
				left join Drug on PersonDispMedicament.Drug_id = Drug.Drug_id
			where PersonDisp_id = ?
		";
		$sqlParams = [$PersonDisp_id];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $PersonDisp_id
	 * @return array|bool
	 */
	public static function getPersonDispMedicamentCount(Polka_PersonDisp_model $callObject, $PersonDisp_id)
	{
		$sql = "
			select count(*) as \"cnt\"
			from
				PersonDispMedicament
				left join DrugState on PersonDispMedicament.Drug_id = DrugState.Drug_id
				left join Drug on PersonDispMedicament.Drug_id = Drug.Drug_id
			where PersonDisp_id = ?
		";
		$sqlParams = [$PersonDisp_id];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function getPersonDispNumber(Polka_PersonDisp_model $callObject, $data)
	{
		$callObject->load->library("swMongoExt");
		return [[
			"PersonDisp_NumCard" => $callObject->swmongoext->generateCode("PersonDisp", "", ["Lpu_id" => $data["Lpu_id"]])
		]];
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonDispListByTree(Polka_PersonDisp_model $callObject, $data)
	{
		$baseParams = [];
		$filters = ["PD.Lpu_id = :Lpu_id"];
		$joinList = [];
		$baseParams["Lpu_id"] = $data["Lpu_id"];
		// 0. Фильтры по дереву
		// включая не актуальные актуальные карты
		if (!isset($data["view_all_id"]) || $data["view_all_id"] == 1) {
			$filters[] = "(PD.PersonDisp_endDate is null or PD.PersonDisp_endDate > dbo.tzGetDate())";
		}
		if (isset($data["disp_med_personal"]) && $data["disp_med_personal"] > 0) {
			//Указали поставившего врача
			$filters[] = "PD.MedPersonal_id = :disp_med_personal";
			$baseParams["disp_med_personal"] = $data["disp_med_personal"];
		}
		if (isset($data["hist_med_personal"]) && $data["hist_med_personal"] > 0) {
			//Указали ответственного врача
			$and_filter = "";
			if (isset($data["check_mph"]) && $data["check_mph"]) {
				$and_filter = " and PerDH.MedPersonal_id = mph_last.MedPersonal_id_last";
			}
			$filters[] = "(
					exists (
						select 1
						from v_PersonDispHist PerDH
						where PerDH.PersonDisp_id = PD.PersonDisp_id
						  and PerDH.MedPersonal_id = :hist_med_personal
						  {$and_filter}
					)
				)
			";
			$baseParams["hist_med_personal"] = $data["hist_med_personal"];
		}
		//1189120
		if (isset($data["object"])) {
			if (isset($data["id"]) || $data["object"] == "LpuUnitType") {
				$id = $data["id"];

				switch ($data["object"]) {
					case "Common":
						$filters[] .= "PD.Sickness_id is null";
						break;
					case "Sickness":
						$filters[] .= "PD.Sickness_id = :id";
						$baseParams["id"] = $id;
						break;
					case "LpuSectionPid":
					case "LpuSection":
						$filters[] = "PD.LpuSection_id = :id";
						$baseParams["id"] = $id;
						break;
					case "LpuRegion":
						$joinList[] = "inner join v_PersonCard pc1 on pc1.Person_id = PD.Person_id";
						$filters[] = "pc1.LpuRegion_id = :id";
						$baseParams["id"] = $id;
						break;
					case "LpuUnit":
						$joinList[] = "inner join v_LpuSection ls1 on ls1.LpuSection_id = PD.LpuSection_id";
						$joinList[] = "inner join v_LpuUnit lu1 on lu1.LpuUnit_id = ls1.LpuUnit_id";
						$filters[] = "lu1.LpuUnit_id = :id";
						$baseParams["id"] = $id;
						break;
					case "LpuUnitType":
						$joinList[] = "inner join v_LpuSection ls1 on ls1.LpuSection_id = PD.LpuSection_id";
						$joinList[] = "inner join v_LpuUnit lu1 on lu1.LpuUnit_id = ls1.LpuUnit_id";
						$arr = explode("_", $id);
						$filters[] = "lu1.LpuUnitType_id = :id1";
						$filters[] = "lu1.LpuBuilding_id = :id2";
						$baseParams["id1"] = $arr[0];
						$baseParams["id2"] = $arr[1];
						break;
					case "LpuBuilding":
						$joinList[] = "inner join LpuSection ls1 on ls1.LpuSection_id = PD.LpuSection_id";
						$joinList[] = "inner join LpuUnit lu1 on lu1.LpuUnit_id = ls1.LpuUnit_id";
						$filters[] .= "lu1.LpuBuilding_id = :id";
						$baseParams["id"] = $id;
						break;
					case "LpuRegionType":
						$joinList[] = "inner join v_PersonCard pc2 on pc2.Person_id = PD.Person_id";
						$filters[] = "pc2.LpuRegionType_id = :id";
						$baseParams["id"] = $id;
						break;
					case "MedPersonal":
						if (!empty($data["view_mp_id"]) && $data["view_mp_id"] != 2) {
							if ($data["view_mp_id"] == 1) {
								$filters[] = "
									(
										PD.MedPersonal_id = :id or
										exists(
											select 1
											from v_PersonDispHist PDH 
											where PDH.PersonDisp_id = PD.PersonDisp_id
											  and PDH.MedPersonal_id = :id
										)
									)
								";
							} else if ($data["view_mp_id"] == 3) {
								if (!empty($data["view_mp_onDate"])) {
									$filters[] = "(
											exists(
												select 1
												from v_PersonDispHist PDH 
												where PDH.PersonDisp_id = PD.PersonDisp_id
												  and PDH.MedPersonal_id = :id
												  and (
													(PDH.PersonDispHist_begDate <= :onDate::timestamp and PDH.PersonDispHist_endDate is null) or
													(PDH.PersonDispHist_begDate <= :onDate::timestamp and PDH.PersonDispHist_endDate >= :onDate::timestamp) 
												  )
											)
										)
									";
									$baseParams["onDate"] = $data["view_mp_onDate"];
								} else {
									$filters[] = "
										(
											exists (
												select 1
												from v_PersonDispHist PDH 
												where PDH.PersonDisp_id = PD.PersonDisp_id
												  and PDH.MedPersonal_id = :id
											)
										)
									";
								}
							}
						} else {
							if (empty($baseParams["disp_med_personal"]) && empty($baseParams["hist_med_personal"]))
								$filters[] = "
									(PD.MedPersonal_id = :id or
										exists (
											select 1 
											from v_PersonDispHist PerDH
											where PerDH.PersonDisp_id = PD.PersonDisp_id
											  and PerDH.MedPersonal_id = :id
										)
									)
								";
						}
						$baseParams["id"] = $id;
						break;
					case "Diag":
						switch ($data["DiagLevel_id"]) {
							case 1:
								$joinList[] = "LEFT JOIN v_Diag dg1 ON dg1.Diag_id = PD.Diag_id";
								$joinList[] = "LEFT JOIN v_Diag dg2 ON dg1.Diag_pid = dg2.Diag_id";
								$joinList[] = "LEFT JOIN v_Diag dg3 ON dg2.Diag_pid = dg3.Diag_id";
								$joinList[] = "LEFT JOIN v_Diag dg4 ON dg3.Diag_pid = dg4.Diag_id";
								$filters[] = "dg4.Diag_id = :id";
								$baseParams["id"] = $id;
								break;
							case 2:
								$joinList[] = "LEFT JOIN v_Diag dg1 ON dg1.Diag_id = PD.Diag_id";
								$joinList[] = "LEFT JOIN v_Diag dg2 ON dg1.Diag_pid = dg2.Diag_id";
								$joinList[] = "LEFT JOIN v_Diag dg3 ON dg2.Diag_pid = dg3.Diag_id";
								$filters[] = "dg3.Diag_id = :id";
								$baseParams["id"] = $id;
								break;
							case 3:
								$joinList[] = "LEFT JOIN v_Diag dg1 ON dg1.Diag_id = PD.Diag_id";
								$joinList[] = "LEFT JOIN v_Diag dg2 ON dg1.Diag_pid = dg2.Diag_id";
								$filters[] = "dg2.Diag_id = :id";
								$baseParams["id"] = $id;
								break;
							case 4:
								$joinList[] = "LEFT JOIN v_Diag dg1 ON dg1.Diag_id = PD.Diag_id";
								$filters[] = "dg1.Diag_id = :id";
								$baseParams["id"] = $id;
								break;
						}
						break;
				}
			}
		}
		//Фильтрация по ограничению доступа к группе диагнозов
		$filters = array_merge($filters, getAccessRightsDiagFilter('dg.Diag_Code', true));
		$whereString = (count($filters) != 0) ? implode(" and ", $filters) : "";
		if($whereString != "") {
			$whereString = "
				where
				-- where
				{$whereString}
				-- end where
			";
		}
		$joinListString = (count($joinList) != 0) ? implode(" ", $joinList) : "";
		$sql = "
			select
				-- select
				PD.PersonDisp_id as \"PersonDisp_id\",
				PD.Person_id as \"Person_id\",
				PD.Server_id as \"Server_id\",
				rtrim(PS.Person_SurName) as \"Person_SurName\",
				rtrim(PS.Person_FirName) as \"Person_FirName\",
				rtrim(PS.Person_SecName) as \"Person_SecName\",
				dg.Diag_Code as \"Diag_Code\",
				mp1.Person_Fio as \"MedPersonal_FIO\",
				mph_last.MedPersonal_FIO_last as \"MedPersonalHist_FIO\",
				lpus1.LpuSection_Name as \"LpuSection_Name\",
				to_char(PD.PersonDisp_begDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_begDate\",
				to_char(PD.PersonDisp_endDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_endDate\",
				to_char(coalesce(PD.PersonDisp_NextDate, oapdv.PersonDispVizit_NextDate), '{$callObject->dateTimeForm104}') as \"PersonDisp_NextDate\",
				to_char(PS.Person_BirthDay, '{$callObject->dateTimeForm104}') as \"Person_BirthDay\",
				Sickness.Sickness_Name as \"Sickness_Name\",
				case
					when noz.isnoz = 1 then 'true'
					when noz.isnoz is not null then 'gray'
					else 'false'
				end as \"Is7Noz\",
				coalesce(PCA.LpuRegion_Name,'') as \"LpuRegion_Name\"
				-- end select
			from
				-- from
				v_PersonDisp PD
				inner join v_PersonState PS on PD.Person_id = PS.Person_id
				left join v_Sickness Sickness on Sickness.Sickness_id = PD.Sickness_id
				left join v_Diag dg on PD.Diag_id = dg.Diag_id
				left join v_LpuSection lpus1 on PD.LpuSection_id = lpus1.LpuSection_id
				left join v_LpuRegion lpur1 on PD.LpuRegion_id = lpur1.LpuRegion_id				
				left join lateral(
					select *
					from v_PersonCard_all
					where Person_id = PS.Person_id
					  and LpuAttachType_id = 1
					  and Lpu_id = :Lpu_id
					order by PersonCard_begDate desc
					limit 1
				) as PCA on true
				left join lateral(
					select
						max(
							case
								when PersonDispMedicament_begDate::date <= dbo.tzGetDate()::date and (PersonDispMedicament_endDate is null or PersonDispMedicament_endDate::date >= dbo.tzGetDate()::date)
								then 1 
								when PersonDispMedicament_begDate::date > dbo.tzGetDate()::date then 0 else null 
							end
						) as isnoz
					from PersonDispMedicament
					where PersonDisp_id = PD.PersonDisp_id
					  and PersonDispMedicament_begDate is not null
				) as noz on true
				{$joinListString}
				left join lateral(
					select *
					from v_MedPersonal
					where MedPersonal_id = PD.MedPersonal_id
					  and Lpu_id = :Lpu_id
					limit 1
				) mp1 on true
				left join lateral(
					select
						MP_L.MedPersonal_id as MedPersonal_id_last,
						MP_L.Person_Fio as MedPersonal_FIO_last
					from v_PersonDispHist PDH_L
					left join v_MedPersonal MP_L on MP_L.MedPersonal_id = PDH_L.MedPersonal_id
					where PDH_L.PersonDisp_id = PD.PersonDisp_id
					order by PDH_L.PersonDispHist_begDate desc
					limit 1				
				) mph_last on true
				left join lateral(
					select PersonDispVizit_NextDate
					from v_PersonDispVizit
					where PersonDisp_id = PD.PersonDisp_id
					  and PersonDispVizit_NextDate::date >= dbo.tzgetdate()::date
					  and PersonDispVizit_NextFactDate is null
					order by PersonDispVizit_NextDate
					limit 1
				) oapdv on true
				--end from
			{$whereString}
			order by
				-- order by
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName
				-- end order by
			limit 101
		";
		return $callObject->getPagingResponse($sql, $baseParams, $data["start"], $data["limit"], true);
	}

	/**
	 * @return string
	 */
	public static function getVizitTypeSysNick()
	{
		switch (getRegionNumber()) {
			case "10":
				return "consulspec";
				break;
			case "201":
				return "dispdinnabl";
				break;
			case "3":
				return "desease";
				break;
			default:
				return "disp";
				break;
		}
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonDispViewData(Polka_PersonDisp_model $callObject, $data)
	{
		$filters = (!(isset($data["from_MZ"]) && $data["from_MZ"] == 2))
			? array_merge(
				getAccessRightsDiagFilter("D.Diag_Code", true),
				getAccessRightsLpuFilter("PD.Lpu_id", true),
				getAccessRightsLpuBuildingFilter("LS.LpuBuilding_id", true),
				["PD.Person_id = :Person_id"]
			)
			: array_merge(["PD.Person_id = :Person_id"]);
		$queryParams = ["Person_id" => $data["Person_id"]];
		$signFilter = "";
		if (!isLpuAdmin() && !empty($data["session"]["medpersonal_id"])) {
			$signFilter = " and PDH.MedPersonal_id = :MedPersonal_id";
			$queryParams["MedPersonal_id"] = $data["session"]["medpersonal_id"];
		} else if (isLpuAdmin() && !empty($data["Lpu_id"])) {
			$signFilter = " and PDHLS.Lpu_id = :Lpu_id";
			$queryParams["Lpu_id"] = $data["Lpu_id"];
		}
		$signAccess = (!empty($signFilter))
			? "case when exists(
					select PDH.PersonDispHist_id
					from
						v_PersonDispHist PDH
						left join v_LpuSection PDHLS on PDHLS.LpuSection_id = PDH.LpuSection_id
					where PDH.PersonDisp_id = PD.PersonDisp_id
					  and coalesce(PDH.PersonDispHist_begDate, tzgetdate()) <= tzgetdate()
					  and coalesce(PDH.PersonDispHist_endDate, tzgetdate()) >= tzgetdate()
					  {$signFilter}
					limit 1	
			) then 'edit' else 'view' end as \"signAccess\""
			: "'view' as signAccess";
		$whereString = (count($filters) != 0) ? "where " . implode(" and ", $filters) : "";
		$query = "
			select
				PD.Lpu_id as \"Lpu_id\",
				PD.Diag_id as \"Diag_id\",
				PD.Person_id as \"Person_id\",
				0 as \"Children_Count\",
				PD.PersonDisp_id as \"PersonDisp_id\",
				PD.PersonDisp_id as \"PersonDispInfo_id\",
				to_char(PD.PersonDisp_begDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_begDate\",
				to_char(PD.PersonDisp_endDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_endDate\",
				coalesce(DOT.DispOutType_Name, '') as \"DispOutType_Name\",
				UPPER(case when Right( D.Diag_Code ,1) = '.' then LEFT(D.Diag_Code, length(D.Diag_Code) - 1) else D.Diag_Code end) AS \"Diag_Code\",
				coalesce(D.Diag_Name, '') as \"Diag_Name\",
				coalesce(MP.Person_Fio, '') as \"MedPersonal_Fio\",
				coalesce(LS.LpuSectionProfile_Name, '') as \"LpuSectionProfile_Name\",
				PD.PersonDisp_IsSignedEP as \"PersonDisp_IsSignedEP\"
				,{$signAccess} 
			from
				v_PersonDisp PD
				left join v_DispOutType DOT on DOT.DispOutType_id = PD.DispOutType_id
				left join v_Diag D on D.Diag_id = PD.Diag_id
				left join v_LpuSection LS on LS.LpuSection_id = PD.LpuSection_id
				left join lateral(
					select DM.Person_Fio 
					from 
						v_PersonDispHist PDSD
						left join lateral(select D2.Person_Fio from v_MedPersonal D2 where D2.MedPersonal_id = PDSD.MedPersonal_id limit 1) as DM on true
					where PDSD.PersonDisp_id = PD.PersonDisp_id
					order by PDSD.PersonDispHist_begDate desc
					limit 1
				) as MP on true
			{$whereString}
			order by
				PD.PersonDisp_begDate,
				D.Diag_Code
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? swFilterResponse::filterNotViewDiag($result->result_array(), $data) : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getMorbusOnkoPersonDispViewData(Polka_PersonDisp_model $callObject, $data)
	{
		if (!empty($data["object"]) && $data["object"] == "MorbusOnkoPersonDisp" && !empty($data["object_value"])) {
			$data["PersonRegister_id"] = $data["object_value"];
		}
		if (empty($data["PersonRegister_id"])) {
			$data["PersonRegister_id"] = null;
		}
		if (empty($data["MorbusOnkoPersonDisp_id"])) {
			$data["MorbusOnkoPersonDisp_id"] = null;
		}
		if (empty($data["PersonRegister_id"]) && empty($data["MorbusOnkoPersonDisp_id"])) {
			return [];
		}
		$query = "
			select
				PD.Lpu_id as \"Lpu_id\",
				PD.Diag_id as \"Diag_id\",
				PD.Person_id as \"Person_id\",
				0 as \"Children_Count\",
				PD.PersonDisp_id as \"PersonDisp_id\",
				PD.PersonDisp_id as \"MorbusOnkoPersonDisp_id\",
				to_char(PD.PersonDisp_begDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_begDate\",
				to_char(PD.PersonDisp_endDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_endDate\",
				coalesce(MP.Person_Fio, '') as \"MedPersonal_Fio\",
				coalesce(MPH.Person_Fio, '') as \"MedPersonalH_Fio\",
				PR.Morbus_id as \"Morbus_id\"
			from
				v_PersonRegisterDispLink PRDL
				inner join v_PersonDisp PD on PD.PersonDisp_id = PRDL.PersonDisp_id
				inner join v_PersonRegister PR on PR.PersonRegister_id = PRDL.PersonRegister_id
				left join lateral(
					select Person_Fio
					from v_MedPersonal
					where MedPersonal_id = PD.MedPersonal_id
					limit 1
				) as MP on true
				left join lateral(
					select mpp.Person_Fio
					from
						v_PersonDispHist pdh
						left join v_MedPersonal mpp on mpp.MedPersonal_id = pdh.MedPersonal_id
					where PersonDisp_id = PD.PersonDisp_id
					  and (
						(pdh.PersonDispHist_begDate <= dbo.tzGetDate() and PDH.PersonDispHist_endDate is null) or
						(PDH.PersonDispHist_begDate <= dbo.tzGetDate() and PDH.PersonDispHist_endDate >= dbo.tzGetDate()) 
					  )
					limit 1
				) as MPH on true
			where PRDL.PersonRegister_id = :PersonRegister_id or PRDL.PersonDisp_id = :PersonDisp_id
			order by PD.PersonDisp_begDate
		";
		$queryParams = [
			"PersonRegister_id" => $data["PersonRegister_id"],
			"PersonDisp_id" => $data["MorbusOnkoPersonDisp_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		if (is_array($result) && count($result) > 0) {
			foreach ($result as $key => $value) {
				$result[$key]["MorbusOnko_pid"] = $data["MorbusOnko_pid"];
			}
		}
		return swFilterResponse::filterNotViewDiag($result, $data);
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonDispSignalViewData(Polka_PersonDisp_model $callObject, $data)
	{
		$filter = "";
		$from = "";
		$diagFilter = getAccessRightsDiagFilter("D.Diag_Code");
		$lpuFilter = getAccessRightsLpuFilter("PD.Lpu_id");
		$lpuBuildingFilter = getAccessRightsLpuBuildingFilter("LS.LpuBuilding_id");

		if (!empty($diagFilter)) {
			$filter .= " and $diagFilter";
		}
		if (!empty($lpuFilter)) {
			$filter .= " and $lpuFilter";
		}
		if (!empty($lpuBuildingFilter)) {
			$filter .= " and $lpuBuildingFilter";
		}
		if ($callObject->getRegionNick() == "perm") {
			//https://redmine.swan.perm.ru/issues/122979
			$from = "
				inner join v_MorbusDiag MD on MD.Diag_id = D.Diag_id
				inner join v_MorbusType MT on MT.MorbusType_id = MD.MorbusType_id
			";
			$filter .= " and PD.Person_id = :Person_id and PD.DispOutType_id is null and PD.PersonDisp_endDate is null and coalesce(MT.MorbusType_SysNick, '') not ilike 'onko'";
		} else {
			$filter .= " and PD.Person_id = :Person_id and PD.DispOutType_id is null and PD.PersonDisp_endDate is null";
		}
		$query = "
			select distinct
				Lpu_id as \"Lpu_id\",
				Diag_id as \"Diag_id\",
				PersonDispSetType_id as \"PersonDispSetType_id\",
				Diag_Code as \"Diag_Code\",
				Diag_Name as \"Diag_Name\",
				LastOsmotr_setDate as \"LastOsmotr_setDate\"
			from (
				select
					PD.Lpu_id,
					PD.Diag_id,
					DU.PersonDispSetType_id,
					D.Diag_Code,
					D.Diag_Name,
					to_char(LastOsmotr.setDate, '{$callObject->dateTimeForm104}') as LastOsmotr_setDate
				from
					v_PersonDisp PD
					left join v_Diag D on D.Diag_id = PD.Diag_id
					left join v_MedStaffFact usermsf on usermsf.MedStaffFact_id = :UserMedStaffFact_id
					left join v_LpuSection LS on LS.LpuSection_id = PD.LpuSection_id
					{$from}
					--определяем поставлен ли пациент на ДУ на участке текущего врача (2) или им самим (3) или чужими врачами (1)
					left join lateral(
						select
							case
								-- поставлен ли пациент на ДУ текущим врачом
								when usermsf.MedPersonal_id = PD.MedPersonal_id then 3
								-- поставлен ли пациент на ДУ на участке текущего врача
								when (select COUNT(usermsr.LpuRegion_id) from v_MedStaffRegion usermsr where usermsr.MedPersonal_id = usermsf.MedPersonal_id and usermsr.Lpu_id = usermsf.Lpu_id and usermsr.LpuRegion_id in (select LpuRegion_id from v_MedStaffRegion where MedPersonal_id = PD.MedPersonal_id and Lpu_id = PD.Lpu_id)) > 0 then 2
								-- пациент поставлен на ДУ чужим врачом
								else 1
							end as PersonDispSetType_id
					) as DU on true
					--определяем дату последнего осмотра по ДУ
					left join lateral(
						select osmotr.setDate
						from (
							select EvnVizitDisp_setDT as setDate
							from v_EvnVizitDisp
							where Person_id = PD.Person_id and Diag_id = PD.Diag_id
							union all
							select EvnVizitPl_setDT as setDate
							from v_EvnVizitPl
							where Person_id = PD.Person_id and Diag_id = PD.Diag_id
						) osmotr
						order by osmotr.setDate desc
						limit 1
					) as LastOsmotr on true
				where (1=1) {$filter}
				union all 
				select
					coalesce(MOL.Lpu_id, MOV.Lpu_id) as Lpu_id,
					PR.Diag_id,
					DU.PersonDispSetType_id,
					D.Diag_Code,
					D.Diag_Name,
					to_char(coalesce(MOL.setDate, MOV.setDate), '{$callObject->dateTimeForm104}') as LastOsmotr_setDate
				from
					v_PersonRegister PR
					inner join v_MorbusType MT on PR.MorbusType_id = MT.MorbusType_id and MT.MorbusType_SysNick ilike 'onko'
					inner join v_Diag D on D.Diag_id = PR.Diag_id
					left join lateral(
						select
							MOL2.Diag_id,
						    MOL2.MorbusOnkoLeave_insDT as setDate,
						    ES.Lpu_id, ES.MedPersonal_id
						from
							v_MorbusOnkoLeave MOL2
							inner join v_EvnSection ES on ES.EvnSection_id = MOL2.EvnSection_id
						where MOL2.Diag_id = PR.Diag_id
						  and ES.Person_id = PR.Person_id
						limit 1
					) as MOL on true
					left join lateral(
						select
							MOLd2.Diag_id,
						    MOLd2.MorbusOnkoVizitPLDop_setDT as setDate,
						    EVP.Lpu_id,
						    EVP.MedPersonal_id
						from
							v_MorbusOnkoVizitPLDop MOLd2 
							inner join v_EvnVizitPL EVP on EVP.EvnVizitPL_id = MOLd2.EvnVizit_id
						where MOLd2.Diag_id = PR.Diag_id
						  and EVP.Person_id = PR.Person_id
						limit 1
					) as MOV on true
					left join v_MedStaffFact usermsf on usermsf.MedStaffFact_id = :UserMedStaffFact_id
					--определяем поставлен ли пациент на ДУ на участке текущего врача (2) или им самим (3) или чужими врачами (1)
					left join lateral(
						select
							case
								-- поставлен ли пациент на ДУ текущим врачом
								when usermsf.MedPersonal_id = coalesce(MOL.MedPersonal_id, MOV.MedPersonal_id) then 3
								-- поставлен ли пациент на ДУ на участке текущего врача
								when (select COUNT(usermsr.LpuRegion_id) from v_MedStaffRegion usermsr where usermsr.MedPersonal_id = usermsf.MedPersonal_id and usermsr.Lpu_id = usermsf.Lpu_id and usermsr.LpuRegion_id in (select LpuRegion_id from v_MedStaffRegion where MedPersonal_id = coalesce(MOL.MedPersonal_id, MOV.MedPersonal_id) and Lpu_id = coalesce(MOL.Lpu_id, MOV.Lpu_id))) > 0 then 2
								-- пациент поставлен на ДУ чужим врачом
								else 1
							end as PersonDispSetType_id
					) as DU on true
				where PR.Person_id = :Person_id
				  and (MOL.Diag_id is not null or MOV.Diag_id is not null)
			) as t
			order by
				PersonDispSetType_id desc,
				LastOsmotr_setDate
		";
		$params = [
			"UserMedStaffFact_id" => $data["UserMedStaffFact_id"],
			"Person_id" => $data["Person_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		return (is_object($result)) ? swFilterResponse::filterNotViewDiag($result->result_array(), $data) : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonDispHistoryListForPrint(Polka_PersonDisp_model $callObject, $data)
	{
		$filter = "";
		$diag_filters = getAccessRightsDiagFilter("d.Diag_Code", true);
		if (count($diag_filters) > 0) {
			$filter .= "and " . implode(" and ", $diag_filters);
		}
		$sql = "
			select
				pd.PersonDisp_id as \"PersonDisp_id\",
				pd.MedPersonal_id as \"MedPersonal_id\",
				pd.LpuSection_id as \"LpuSection_id\",
				to_char(pd.PersonDisp_begDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_begDate\",
				to_char(pd.PersonDisp_endDate, '{$callObject->dateTimeForm104}') as \"PersonDisp_endDate\",
				d.Diag_Code as \"Diag_Code\",
				d.Diag_Name as \"Diag_Name\",
				l.Lpu_Nick as \"Lpu_Nick\",
				ls.LpuSection_Name as \"LpuSection_Name\",
				mp.Person_Fio as \"MedPersonal_FIO\",
				p.name as \"Post_Name\",
				CASE WHEN pd.Lpu_id = :Lpu_id THEN 2 ELSE 1 END as \"IsOurLpu\"
			from
				v_PersonDisp pd
				left join v_Diag d on d.Diag_id = pd.Diag_id
				left join v_Lpu l on l.Lpu_id = pd.Lpu_id
				left join v_MedPersonal mp on mp.MedPersonal_id = pd.MedPersonal_id
				left join v_LpuSection ls on ls.LpuSection_id = pd.LpuSection_id
				left join lateral(
					select Post_id
					from v_MedStaffFact
					where MedPersonal_id = pd.MedPersonal_id
					  and LpuSection_id = pd.LpuSection_id
					  and (WorkData_begDate is null or WorkData_begDate <= pd.PersonDisp_begDate)
					  and (WorkData_endDate is null or WorkData_endDate >= pd.PersonDisp_begDate)
					limit 1
				) as msf on true
				left join persis.Post p on p.id = msf.Post_id
			where pd.Person_id = :Person_id
			  {$filter}
			order by
				PersonDisp_begDate,
				PersonDisp_endDate
		";
		$sqlParams = [
			"Lpu_id" => $data["Lpu_id"],
			"Person_id" => $data["Person_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonDispHistoryListAdresses(Polka_PersonDisp_model $callObject, $data)
	{
		$sql = "
			select
				pd.Address_id as \"Address_id\",
				PAdr.Address_Address as \"PAddress_Address\",
				pd.PersonPAddress_begDT as \"PersonPAddress_begDT\"
			from
				v_PersonPAddress pd
				left join v_Address PAdr on PAdr.Address_id  = pd.Address_id
			where pd.Person_id = :Person_id
			order by pd.PersonPAddress_begDT
		";
		$sqlParams = ["Person_id" => $data["Person_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function getPersonLabelCounts(Polka_PersonDisp_model $callObject, $data)
	{
		$params = [
			"Lpu_id" => $data["MonitorLpu_id"],
			"outBegDate" => $data["outBegDate"],
			"outEndDate" => $data["outEndDate"],
			"Label_id" => (!empty($data["Label_id"]))?$data["Label_id"]:1
		];
		$tabs = ["new" => 0, "on" => 0, "off" => 0, "all" => 0];
		foreach ($tabs as $key => $tab) {
			
			$filters = ["PL.Label_id = :Label_id and PS.Lpu_id = :Lpu_id"];
			$inner_filter = "";
			$exist_join = "";

			switch ($key) {
				case "new":
					//Новые
					//	У человека есть запись о наличии метки (запись открыта)
					//	У человека нет связанной с меткой карты наблюдений в МО Пользователя (как открытой, так и закрытой)
					$filters[] = "LOC.LabelObserveChart_id is null and LOC.LabelObserveChart_id is null and PL.PersonLabel_disDate is null";
					break;
				case "on":
					//Включенные
					//	У человека есть открытая карта наблюдений в МО Пользователя
					$filters[] = "LOC.LabelObserveChart_id is not null";

					$inner_filter = "
						and LOCO.LabelObserveChart_endDate is null
					";
					
					break;
				case "off":
					//Выбывшие
					//	У человека есть карта наблюдений в МО Пользователя с заполненной датой закрытия
					$filters[] = "
						LOC.LabelObserveChart_id is not null 
						and LOCExist.LabelObserveChart_id is null
					";

					$inner_filter = "
						and LOCO.LabelObserveChart_endDate is not null
					";

					$exist_join .= "
						left join lateral (
							select
								LOCO.LabelObserveChart_id,
								LOCO.LabelObserveChart_endDate
							from v_LabelObserveChart LOCO
								inner join v_MedStaffFact MSF on MSF.MedStaffFact_id = LOCO.MedStaffFact_id
							WHERE LOCO.PersonLabel_id = PL.PersonLabel_id
								AND MSF.Lpu_id = :Lpu_id
								and LOCO.LabelObserveChart_endDate is null
							limit 1
						) LOCexist on true
					";
					
					break;
				case "all":
					//Все пациенты
					//	У человека есть запись о наличии метки (запись открыта)
					//	У человека есть карта наблюдений в МО Пользователя (как открытая, так и закрытая)
					$filters[] = "PL.PersonLabel_disDate is null and LOC.LabelObserveChart_id is not null";
					break;
			}
			if (($key == "off" or $key == "all") and !empty($data["outBegDate"]) and !empty($data["outEndDate"])) {
				$filters[] = "((LOC.LabelObserveChart_endDate > :outBegDate and LOC.LabelObserveChart_endDate < :outEndDate) or LOC.LabelObserveChart_endDate is null)";
			}
			$whereString = implode(" and ", $filters);
			$query = "
				select count(PL.PersonLabel_id)
				from
					v_PersonLabel PL 
					inner join v_PersonState PS on PS.Person_id = PL.Person_id
					left join lateral(
						select
							LOCO.LabelObserveChart_id,
							LOCO.LabelObserveChart_endDate
						from
							v_LabelObserveChart LOCO
							inner join v_MedStaffFact MSF on MSF.MedStaffFact_id = LOCO.MedStaffFact_id
						where LOCO.PersonLabel_id = PL.PersonLabel_id
						  and MSF.Lpu_id = :Lpu_id
						  {$inner_filter}
						limit 1	
					) LOC on true
					{$exist_join}
				where {$whereString}
			";
			$count = $callObject->getFirstResultFromQuery($query, $params);
			$tabs[$key] = $count;
		}
		return $tabs;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonChartInfo(Polka_PersonDisp_model $callObject, $data)
	{
		$params = [
			"Person_id" => $data["Person_id"],
		];

		$select = "
				null as \"email\",
				null as \"LOC.FeedbackMethod_id\",
				null as \"LOC.PersonModel_id\",	
				null as \"Chart_begDate\",
				null as \"Chart_endDate\",
				null as \"DOT.DispOutType_id\",
				null as \"DOT.DispOutType_Name\",
				null as \"ChartPhone\",
				null as \"ChartEmail\",
				null as \"MailingConsDT\",
		";

		$join = "";

		if (!empty($data['Chart_id'])) {
			
			$params['Chart_id']	= $data['Chart_id'];
			
			$join .= " 
				left join v_LabelObserveChart LOC on LOC.LabelObserveChart_id = :Chart_id
				left join v_DispOutType DOT on DOT.DispOutType_id = LOC.DispOutType_id
			";

			$select = "
				LOC.LabelObserveChart_Email as \"email\",
				LOC.FeedbackMethod_id as \"FeedbackMethod_id\",
				LOC.PersonModel_id as \"PersonModel_id\",
				to_char(LOC.LabelObserveChart_begDate, '{$callObject->dateTimeForm104}') as \"Chart_begDate\",
				to_char(LOC.LabelObserveChart_endDate, '{$callObject->dateTimeForm104}') as \"Chart_endDate\",
				DOT.DispOutType_id as \"DispOutType_id\",
				DOT.DispOutType_Name as \"DispOutType_Name\",
				LOC.LabelObserveChart_Phone as \"ChartPhone\",
				LOC.LabelObserveChart_Email as \"ChartEmail\",
				to_char(LOC.LabelObserveChart_consDT, '{$callObject->dateTimeForm104}') as \"MailingConsDT\",
			";
		}

		$chartinfo = $callObject->getFirstRowFromQuery("
			select
				PS.Person_id as \"Person_id\",
				PS.Server_id as \"Server_id\",
				PS.Person_Phone as \"PersonPhone\",
				PH.PersonHeight_Height::float8 as \"PersonHeight\",
				case when PW.Okei_id = 36 then PW.PersonWeight_Weight::float8 / 1000 else PW.PersonWeight_Weight end as \"PersonWeight\",
				pcard.Lpu_id as \"Lpu_id\",
				pcard.Lpu_Nick as \"Lpu_Nick\",
				pcard.LpuRegion_Name as \"AttachNum\",
				{$select}
				to_char(pcard.AttachDate, '{$callObject->dateTimeForm104}') as \"AttachDate\"
			from
				v_PersonState PS
				{$join}
				left join lateral (
					select PH.PersonHeight_Height
					from v_PersonHeight PH
					where PH.Person_id = PS.Person_id
					order by PH.PersonHeight_setDT DESC
					limit 1
				) as PH on true
				left join lateral(
					select
						PW.PersonWeight_Weight,
						PW.Okei_id
					from v_PersonWeight PW
					where PW.Person_id = PS.Person_id
					order by PW.PersonWeight_setDT DESC
					limit 1
				) as PW on true
				left join lateral(
					select
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id,
						lpu.Lpu_Nick,
						pc.LpuRegion_id,
						pc.LpuRegion_Name,
						pc.PersonCard_begDate as AttachDate,
						case when pc.LpuAttachType_id = 1 then pc.PersonCard_Code else null end as PersonCard_Code
					from
						v_PersonCard pc
						left join v_Lpu lpu on pc.Lpu_id = lpu.Lpu_id
					where pc.Person_id = PS.Person_id and LpuAttachType_id = 1
					order by PersonCard_begDate desc
					limit 1
				) as pcard on true
			where PS.Person_id = :Person_id
			limit 1	
		", $params);

		$rates = $callObject->queryResult("
			select 
				CR.LabelObserveChartRate_id as \"ChartRate_id\",
				CR.LabelObserveChartRate_Min as \"ChartRate_Min\",
				CR.LabelObserveChartRate_Max as \"ChartRate_Max\",
				CR.LabelObserveChartRate_IsShowValue as \"LabelObserveChartRate_IsShowValue\",
				CR.LabelObserveChartRate_IsShowEMK as \"LabelObserveChartRate_IsShowEMK\",
				CR.LabelObserveChartSource_id as \"LabelObserveChartSource_id\",
				locs.LabelObserveChartSource_Name as \"LabelObserveChartSource_Name\",
				CR.LabelRate_id as \"LabelRate_id\",
				RT.RateType_id as \"RateType_id\",
				RT.RateValueType_id as \"RateValueType_id\",
				RT.RateType_SysNick as \"RateType_SysNick\"
			from v_LabelObserveChartRate CR
			left join v_LabelRate LR on LR.LabelRate_id = CR.LabelRate_id
			left join v_RateType RT on RT.RateType_id = coalesce(LR.RateType_id, CR.RateType_id)
			left join v_LabelObserveChartSource locs on locs.LabelObserveChartSource_id = CR.LabelObserveChartSource_id
			WHERE Person_id = :Person_id
		", $params);
		
		//данные с портала
		
		//если найдена запись - значит человек имеет учетку на портале и в моб.приложении
		//если FCM_Token is not null - точно пользовался моб.приложением (если NULL - неизвестно)
		// todo: это не верно, пациент может быть в нескольких картотеках разных аккаунтов
		$portal_resp = $callObject->db->query("
			select 
				U.email as \"email\",
				U.FCM_Token as \"FCM_Token\"
			from UserPortal.Person P
			left join UserPortal.Users U on U.id = P.pmUser_id
			where P.Person_mainId = :Person_id
			order by U.FCM_Token desc
			limit 1
		", ["Person_id" => $chartinfo["Person_id"]]);

		$portal_result = array();
		if (is_object($portal_resp)) {
			$portal_result = $portal_resp->result_array();
			if (!empty($portal_result[0])) {
				$portal_result = $portal_result[0];
			}
		}
		return ["info" => $chartinfo, "rates" => $rates, "portal" => $portal_result];
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function getPersonDataFromPortal(Polka_PersonDisp_model $callObject, $data)
	{
		$Person_ids = [];
		foreach ($data["Person_ids"] as $Person_id) {
			$Person_ids[] = intval($Person_id);
		}

		//данные с портала

		$portal = [];
		foreach ($Person_ids as $Person_id) {
			$selectString = "
				P.Person_mainId as \"Person_id\",
				U.FCM_Token as \"isApp\",
				U.email as \"email\",
				U.last_login as \"last_login\",
				P.Person_Phone as \"phone\"
			";
			$fromString = "
				UserPortal.Person P
				left join UserPortal.Users U on U.id = P.pmUser_Id
			";
			$whereString = "P.Person_mainId = :Person_id";
			$orderByString = "P.Person_updDT desc";
			$sql = "
				select {$selectString}
				from {$fromString}
				where {$whereString}
				order by {$orderByString}
			";
			$sqlParams = ["Person_id" => $Person_id];
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($sql, $sqlParams);
			if (is_object($result)) {
				$row = $result->result_array();
				if (count($row) > 0) {
					$portal[] = $row[0];
				}
			}
		}
		return $portal;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return bool|float|int|string
	 */
	public static function getMeasuresNumberAfterDate(Polka_PersonDisp_model $callObject, $data)
	{
		$params = [
			"Chart_id" => $data["Chart_id"],
			"endDate" => $data["endDate"]
		];
		$sql = "
			select count(CI.LabelObserveChart_id)
			from LabelObserveChartInfo CI
			where CI.LabelObserveChart_id=:Chart_id
			  and CI.LabelObserveChartInfo_ObserveDate > :endDate
		";
		return $callObject->getFirstResultFromQuery($sql, $params);
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return bool|mixed
	 */
	public static function getLpuBuildingHealth(Polka_PersonDisp_model $callObject, $data)
	{
		$params = ["LpuSection_id" => $data["LpuSection_id"]];
		$query = "
			select
				trim(coalesce(LBH.LpuBuildingHealth_Phone, '')) as \"phone\",
				trim(coalesce(LBH.LpuBuildingHealth_Email, '')) as \"email\",
				L.Lpu_Nick as \"Lpu_Nick\"
			from
				v_LpuSection LS 
				left join v_LpuUnit LU on LS.LpuUnit_id = LU.LpuUnit_id
				left join v_Lpu L on L.Lpu_id = LU.Lpu_id
				left join v_LpuBuilding LB on LU.LpuBuilding_id = LB.LpuBuilding_id
				left join v_LpuBuildingHealth LBH on LBH.LpuBuilding_id = LB.LpuBuilding_id
			WHERE LS.LpuSection_id = :LpuSection_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		return (is_object($result)) ? $result->first_row() : false;
	}

	/**
	 * @param $number
	 * @return string
	 */
	public static function getPhoneNumber($number)
	{
		$regexp = "/^(\+?7)?[\s\-]?\(?(\d{3})\)?[\s\-]?(\d{3})[\s\-]?(\d{2})[\s\-]?(\d{2})$/";
		return (preg_match($regexp, $number, $match)) ? "{$match[2]}{$match[3]}{$match[4]}{$match[5]}" : "";
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getMonitorTemperatureInfo(Polka_PersonDisp_model $callObject, $data)
	{
		$sql = "
			select to_char(PLA.PersonLabel_setDate, '{$callObject->dateTimeForm104}') as \"MonitorTemperatureStartDate\"
			from
				v_PersonLabel PLA
				inner join v_LabelObserveChart LOC on PLA.PersonLabel_id = LOC.PersonLabel_id
			where PLA.Person_id = :Person_id
			  and PLA.PersonLabel_disDate is null
			  and PLA.Label_id = 7
		";
		$params = [
			"Lpu_id" => $data["Lpu_id"],
			"Person_id" => $data["Person_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLabelObserveCharts(Polka_PersonDisp_model $callObject, $data)
	{
		$params = [
			"Person_id" => $data["Person_id"],
			"Lpu_id" => $data["Person_lpu_id"]
		];
		$sql = "
			select
				PL.Label_id as \"Label_id\",
				LOC.LabelObserveChart_id as \"LabelObserveChart_id\"
			from
				v_PersonLabel PL 
				inner join v_LabelObserveChart LOC on LOC.PersonLabel_id = PL.PersonLabel_id
				inner join v_MedStaffFact MSF on MSF.MedStaffFact_id = LOC.MedStaffFact_id
			where PL.Person_id=:Person_id
			  and LOC.LabelObserveChart_endDate is null
			  and MSF.Lpu_id=:Lpu_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonDisp_model $callObject
	 * @param $data
	 * @return bool|int
	 */
	public static function getAvailabilityDispensaryCardCauseDeath(Polka_PersonDisp_model $callObject, $data)
	{
		if (empty($data["Person_id"])) {
			return 0;
		}
		$sql = "
			select count(*) as count
			from
				v_PersonDisp PD
				left join v_DispOutType DOT on DOT.DispOutType_id = PD.DispOutType_id
			where PD.Person_id = :Person_id
			  and DOT.DispOutType_Code = 3
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $data);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		return $result[0]["count"];
	}
}