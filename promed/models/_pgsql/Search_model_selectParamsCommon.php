<?php

class Search_model_selectParamsCommon
{
	public static function selectParams_EvnInfectNotify(Search_model $callObject, &$data, &$filter, &$queryParams)
	{
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and (Diag.Diag_Code >= :Diag_Code_From or Diag1.Diag_Code >= :Diag_Code_From) ";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and (Diag.Diag_Code <= :Diag_Code_To or Diag1.Diag_Code <= :Diag_Code_To) ";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][0])) {
			$filter .= " and EIN.EvnInfectNotify_insDT >= :EvnInfectNotify_insDT_Range_0::timestamp ";
			$queryParams["EvnInfectNotify_insDT_Range_0"] = "{$data["EvnNotifyBase_setDT_Range"][0]} 00:00:00";
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][1])) {
			$filter .= " and EIN.EvnInfectNotify_insDT <= :EvnInfectNotify_insDT_Range_1::timestamp ";
			$queryParams["EvnInfectNotify_insDT_Range_1"] = "{$data["EvnNotifyBase_setDT_Range"][1]} 23:59:59";
		}
		if (!havingGroup("HIVRegistry")) {
			$filter .= " and coalesce(MT.MorbusType_SysNick,'') not ilike 'hiv'";
		}
		if (!havingGroup("HepatitisRegistry")) {
			$filter .= " and coalesce(MT.MorbusType_SysNick,'') not ilike 'hepa'";
		}
		if (!havingGroup("TubRegistry")) {
			$filter .= " and coalesce(MT.MorbusType_SysNick,'') not ilike 'tub'";
		}
		if (!havingGroup("NephroRegistry")) {
			$filter .= " and coalesce(MT.MorbusType_SysNick,'') not ilike 'nephro'";
		}
		if (!havingGroup("ProfRegistry")) {
			$filter .= " and coalesce(MT.MorbusType_SysNick,'') not ilike 'prof'";
		}
		if (!havingGroup("IBSRegistry")) {
			$filter .= " and coalesce(MT.MorbusType_SysNick,'') not ilike 'ibs'";
		}
		if (havingGroup("NarkoMORegistry")) {
			$filter .= " and EIN.Lpu_id " . $callObject->getLpuIdFilter($data);
		} else if (!havingGroup(["NarkoRegistry", "NarkoMORegistry"])) {
			$filter .= " and coalesce(MT.MorbusType_SysNick,'') not ilike 'narc'";
		}
		if (!havingGroup("CrazyRegister")) {
			$filter .= " and coalesce(MT.MorbusType_SysNick,'') not ilike 'crazy'";
		}
		if (!havingGroup("VenerRegister")) {
			$filter .= " and coalesce(MT.MorbusType_SysNick,'') not ilike 'vener'";
		}
	}

	public static function selectParams_PersonDisp(&$data, &$filter, &$queryParams)
	{
		if (!empty($data["session"]["lpu_id"])) {
			$filter .= " and PD.Lpu_id = {$data["session"]["lpu_id"]} ";
		}
		if (isset($data["ViewAll_id"]) && $data["ViewAll_id"] == 1) {
			$filter .= " and (PD.PersonDisp_endDate is null or PD.PersonDisp_endDate > (select dt from mv)) ";
		}
		if (isset($data["DispLpuSection_id"])) {
			$filter .= " and PD.LpuSection_id = :DispLpuSection_id ";
			$queryParams["DispLpuSection_id"] = $data["DispLpuSection_id"];
		}
		if (isset($data["DispLpuSectionProfile_id"])) {
			$filter .= " and lpus1.LpuSectionProfile_id = :DispLpuSectionProfile_id ";
			$queryParams["DispLpuSectionProfile_id"] = $data["DispLpuSectionProfile_id"];
		}
		if (isset($data["DispMedPersonal_id"])) {
			$filter .= " and PD.MedPersonal_id = :DispMedPersonal_id ";
			$queryParams["DispMedPersonal_id"] = $data["DispMedPersonal_id"];
		}
		if (isset($data["HistMedPersonal_id"])) {
			$and_filter = "";
			if (empty($data["checkMPHistory"])) {
				$and_filter = " and PerDH.MedPersonal_id = mph_last.MedPersonal_id_last";
			}
			$filter .= "
				and exists (
					select 1 
					from v_PersonDispHist PerDH
					where PerDH.PersonDisp_id = PD.PersonDisp_id
					  and PerDH.MedPersonal_id = :HistMedPersonal_id
					  {$and_filter}
					limit 1
				)
			";
			$queryParams["HistMedPersonal_id"] = $data["HistMedPersonal_id"];
		}
		if (isset($data["PersonDisp_begDate"])) {
			$filter .= " and PD.PersonDisp_begDate = :PersonDisp_begDate::timestamp ";
			$queryParams["PersonDisp_begDate"] = $data["PersonDisp_begDate"];
		}
		if (isset($data["PersonDisp_begDate_Range"][0])) {
			$filter .= " and PD.PersonDisp_begDate >= :PersonDisp_begDate_Range_0::timestamp ";
			$queryParams["PersonDisp_begDate_Range_0"] = $data["PersonDisp_begDate_Range"][0];
		}
		if (isset($data["PersonDisp_begDate_Range"][1])) {
			$filter .= " and PD.PersonDisp_begDate <= :PersonDisp_begDate_Range_1::timestamp ";
			$queryParams["PersonDisp_begDate_Range_1"] = $data["PersonDisp_begDate_Range"][1];
		}
		if (isset($data["PersonDisp_endDate"])) {
			$filter .= " and PD.PersonDisp_endDate = :PersonDisp_endDate::timestamp ";
			$queryParams["PersonDisp_endDate"] = $data["PersonDisp_endDate"];
		}
		if (isset($data["PersonDisp_endDate_Range"][0])) {
			$filter .= " and PD.PersonDisp_endDate >= :PersonDisp_endDate_Range_0::timestamp ";
			$queryParams["PersonDisp_endDate_Range_0"] = $data["PersonDisp_endDate_Range"][0];
		}
		if (isset($data["PersonDisp_endDate_Range"][1])) {
			$filter .= " and PD.PersonDisp_endDate <= :PersonDisp_endDate_Range_1::timestamp ";
			$queryParams["PersonDisp_endDate_Range_1"] = $data["PersonDisp_endDate_Range"][1];
		}
		if (isset($data["PersonDisp_NextDate"])) {
			$filter .= " and coalesce(oapdv.PersonDispVizit_NextDate, PD.PersonDisp_NextDate)::date = :PersonDisp_NextDate::date ";
			$queryParams["PersonDisp_NextDate"] = $data["PersonDisp_NextDate"];
		}
		if (isset($data["PersonDisp_NextDate_Range"][0])) {
			$filter .= " and coalesce(oapdv.PersonDispVizit_NextDate, PD.PersonDisp_NextDate)::date >= :PersonDisp_NextDate_Range_0::date ";
			$queryParams["PersonDisp_NextDate_Range_0"] = $data["PersonDisp_NextDate_Range"][0];
		}
		if (isset($data["PersonDisp_NextDate_Range"][1])) {
			$filter .= " and coalesce(oapdv.PersonDispVizit_NextDate, PD.PersonDisp_NextDate)::date <= :PersonDisp_NextDate_Range_1::date ";
			$queryParams["PersonDisp_NextDate_Range_1"] = $data["PersonDisp_NextDate_Range"][1];
		}
		if (isset($data["PersonDisp_LastDate"])) {
			$filter .= " and coalesce(LD.PersonDisp_LastDate,lapdv.PersonDispVizit_NextFactDate)::date = :PersonDisp_LastDate::date ";
			$queryParams["PersonDisp_LastDate"] = $data["PersonDisp_LastDate"];
		}
		if (isset($data["PersonDisp_LastDate_Range"][0])) {
			$filter .= " and coalesce(LD.PersonDisp_LastDate,lapdv.PersonDispVizit_NextFactDate)::date >= :PersonDisp_LastDate_Range_0::date ";
			$queryParams["PersonDisp_LastDate_Range_0"] = $data["PersonDisp_LastDate_Range"][0];
		}
		if (isset($data["PersonDisp_LastDate_Range"][1])) {
			$filter .= " and coalesce(LD.PersonDisp_LastDate,lapdv.PersonDispVizit_NextFactDate)::date <= :PersonDisp_LastDate_Range_1::date ";
			$queryParams["PersonDisp_LastDate_Range_1"] = $data["PersonDisp_LastDate_Range"][1];
		}
		if (isset($data["PersonDisp_IsAutoClose"]) && $data["PersonDisp_IsAutoClose"] == "on") {
			$filter .= " and PD.PersonDisp_IsAutoClose = 2";
		}
		if (isset($data["DispOutType_id"])) {
			$filter .= " and PD.DispOutType_id = :DispOutType_id ";
			$queryParams["DispOutType_id"] = $data["DispOutType_id"];
		}
		if (isset($data["PersonDisp_IsDop"])) {
			$filter .= " and coalesce(PD.PersonDisp_IsDop, 1) = :PersonDisp_IsDop ";
			$queryParams["PersonDisp_IsDop"] = $data["PersonDisp_IsDop"];
		}
		if (isset($data["DiagDetectType"])) {
			$filter .= " and coalesce(PD.DiagDetectType_id, 1) = :DiagDetectType ";
			$queryParams["DiagDetectType"] = $data["DiagDetectType"];
		}
		if (isset($data["Sickness_id"])) {
			$filter .= " and PD.Sickness_id = :Sickness_id ";
			$queryParams["Sickness_id"] = $data["Sickness_id"];
		}
		if (isset($data["Disp_Diag_id"])) {
			$filter .= " and PD.Diag_id = :Disp_Diag_id ";
			$queryParams["Disp_Diag_id"] = $data["Disp_Diag_id"];
		}
		if (strlen($data["Disp_Diag_Code_From"]) > 0) {
			$filter .= " and dg1.Diag_Code >= :Disp_Diag_Code_From";
			$queryParams["Disp_Diag_Code_From"] = $data["Disp_Diag_Code_From"];
		}
		if (strlen($data["Disp_Diag_Code_To"]) > 0) {
			$filter .= " and dg1.Diag_Code <= :Disp_Diag_Code_To";
			$queryParams["Disp_Diag_Code_To"] = $data["Disp_Diag_Code_To"];
		}
		if (isset($data["Disp_Diag_pid"])) {
			$filter .= " and PD.Diag_pid = :Disp_Diag_pid ";
			$queryParams["Disp_Diag_pid"] = $data["Disp_Diag_pid"];
		}
		if (strlen($data["Disp_PredDiag_Code_From"]) > 0) {
			$filter .= " and dg2.Diag_Code >= :Disp_PredDiag_Code_From";
			$queryParams["Disp_PredDiag_Code_From"] = $data["Disp_PredDiag_Code_From"];
		}
		if (strlen($data["Disp_PredDiag_Code_To"]) > 0) {
			$filter .= " and dg2.Diag_Code <= :Disp_PredDiag_Code_To";
			$queryParams["Disp_PredDiag_Code_To"] = $data["Disp_PredDiag_Code_To"];
		}
		if (isset($data["Disp_Diag_nid"])) {
			$filter .= " and PD.Diag_nid = :Disp_Diag_nid ";
			$queryParams["Disp_Diag_nid"] = $data["Disp_Diag_nid"];
		}
		if (strlen($data["Disp_NewDiag_Code_From"]) > 0) {
			$filter .= " and dg3.Diag_Code >= :Disp_NewDiag_Code_From";
			$queryParams["Disp_NewDiag_Code_From"] = $data["Disp_NewDiag_Code_From"];
		}
		if (strlen($data["Disp_NewDiag_Code_To"]) > 0) {
			$filter .= " and dg3.Diag_Code <= :Disp_NewDiag_Code_To";
			$queryParams["Disp_NewDiag_Code_To"] = $data["Disp_NewDiag_Code_To"];
		}
	}

	public static function selectParams_PersonCardStateDetail_old1(&$data, &$filter, &$queryParams)
	{
		if ($data["PCSD_LpuMotion_id"] == 2) {
			$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id != PCSD.Lpu_id ";
		}
		if ($data["PCSD_LpuMotion_id"] == 3) {
			$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id = PCSD.Lpu_id ";
		}
		if (isset($data["PCSD_FromLpu_id"]) && (int)$data["PCSD_FromLpu_id"] > 0) {
			$queryParams["FromLpu_id"] = (int)$data["PCSD_FromLpu_id"];
			$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id = :FromLpu_id ";
		}
		if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
			$queryParams["ToLpu_id"] = (int)$data["PCSD_ToLpu_id"];
			$filter .= " and NextCard.Lpu_id is not NULL and NextCard.Lpu_id = :ToLpu_id ";
		}
		if (isset($data["PCSD_LpuRegion_id"])) {
			$queryParams["PCSD_LpuRegion_id"] = $data["PCSD_LpuRegion_id"];
			$filter .= " and PCSD.LpuRegion_id = :PCSD_LpuRegion_id ";
		} else {
			$filter .= " and PCSD.LpuRegion_id is null and PCSD.Lpu_id = :Lpu_id ";
		}
		switch ($data["PCSD_mode"]) {
			case "BegCount":
				$filter .= "
					and PCSD.PersonCard_begDate < :PCSD_begDate
					and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "BegCountBDZ":
				$filter .= "
					and PCSD.PersonCard_begDate < :PCSD_begDate
					and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
					and (Polis.Polis_id is not null )
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "EndCount":
				$filter .= "
					and PCSD.PersonCard_begDate <= :PCSD_endDate
					and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate > :PCSD_endDate)
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "EndCountBDZ":
				$filter .= "
					and PCSD.PersonCard_begDate <= :PCSD_endDate
					and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate > :PCSD_endDate)
					and (Polis.Polis_id is not null )
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "AttachCount":
				$filter .= "
					and PCSD.PersonCard_begDate between :PCSD_begDate and :PCSD_endDate
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "AttachCountBDZ":
				$filter .= "
					and PCSD.PersonCard_begDate between :PCSD_begDate and :PCSD_endDate
					and (Polis.Polis_id is not null )
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "AttachIncomeBDZ":
				$filter .= "
					and PCSD.PersonCard_begDate between '1970-01-01' and :PCSD_endDate
					and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
					and PS.Server_pid = 0
					and hasPolis.cnt is not null
					and notHasPolis.cnt is null
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "AttachOutcomeBDZ":
				$filter .= "
					and PCSD.PersonCard_begDate between '1970-01-01'' and :PCSD_endDate
					and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
					and PS.Server_pid = 0
					and HasPolisBefore.cnt is not null
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "DettachCount":
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= "
						and NextCard.PersonCard_begDate between :PCSD_begDate and :PCSD_endDate and NextCard.Lpu_id = :ToLpu_id
					";
				} else {
					$filter .= "
						and PCSD.PersonCard_endDate between :PCSD_begDate and :PCSD_endDate
					";
				}
				break;
			case "DettachCountBDZ":
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= "
						and NextCard.PersonCard_begDate between :PCSD_begDate and :PCSD_endDate
						and NextCard.Lpu_id = :ToLpu_id
						and Polis.Polis_id is not null
					";
				} else {
					$filter .= "
						and PCSD.PersonCard_endDate between :PCSD_begDate and :PCSD_endDate
						and Polis.Polis_id is not null
					";
				}
				break;
		}
		$queryParams["PCSD_begDate"] = $data["PCSD_StartDate"];
		$queryParams["PCSD_endDate"] = $data["PCSD_EndDate"];
	}

	public static function selectParams_PersonCardStateDetail_old(&$data, &$filter, &$queryParams)
	{
		if ($data["PCSD_LpuMotion_id"] == 2) {
			$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id != PCSD.Lpu_id ";
		}
		if ($data["PCSD_LpuMotion_id"] == 3) {
			$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id = PCSD.Lpu_id ";
		}
		if (isset($data["PCSD_FromLpu_id"]) && (int)$data["PCSD_FromLpu_id"] > 0) {
			$queryParams["FromLpu_id"] = (int)$data["PCSD_FromLpu_id"];
			$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id = :FromLpu_id ";
		}
		if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
			$queryParams["ToLpu_id"] = (int)$data["PCSD_ToLpu_id"];
			$filter .= " and NextCard.Lpu_id is not NULL and NextCard.Lpu_id = :ToLpu_id ";
		}
		if (isset($data["PCSD_LpuRegion_id"])) {
			$queryParams["PCSD_LpuRegion_id"] = $data["PCSD_LpuRegion_id"];
			$filter .= " and PCSD.LpuRegion_id = :PCSD_LpuRegion_id ";
		} else {
			$filter .= " and PCSD.LpuRegion_id is null and PCSD.Lpu_id = :Lpu_id ";
		}
		switch ($data["PCSD_mode"]) {
			case "BegCount":
				$filter .= "
					and PCSD.PersonCard_begDate < :PCSD_begDate
					and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "BegCountBDZ":
				$filter .= "
					and PCSD.PersonCard_begDate < :PCSD_begDate
					and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
					and PolisBeg.Polis_id is not null
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "EndCount":
				$filter .= "
					and PCSD.PersonCard_begDate <= :PCSD_endDate
					and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate > :PCSD_endDate)
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "EndCountBDZ":
				$filter .= "
					and PCSD.PersonCard_begDate <= :PCSD_endDate
					and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate > :PCSD_endDate)
					and PolisEnd.Polis_id is not null
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "AttachCount":
				$filter .= "
					and PCSD.PersonCard_begDate between :PCSD_begDate and :PCSD_endDate
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "AttachCountBDZ":
				$filter .= "
					and PCSD.PersonCard_begDate between :PCSD_begDate and :PCSD_endDate
					and (Polis.Polis_id is not null )
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "AttachIncomeBDZ":
				$filter .= "
					and PCSD.PersonCard_begDate between '1970-01-01' and :PCSD_endDate
					and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
					and PS.Server_pid = 0
					and hasPolis.cnt is not null
					and notHasPolis.cnt is null
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "AttachOutcomeBDZ":
				$filter .= "
					and PCSD.PersonCard_begDate between '1970-01-01' and :PCSD_endDate
					and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
					and PS.Server_pid = 0
					and HasPolisBefore.cnt is not null
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "DettachCount":
				$filter .= (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0)
					? " and NextCard.PersonCard_begDate between :PCSD_begDate and :PCSD_endDate and NextCard.Lpu_id = :ToLpu_id "
					: " and PCSD.PersonCard_endDate between :PCSD_begDate and :PCSD_endDate ";
				break;
			case "DettachCountBDZ":
				$filter .= (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0)
					? " and NextCard.PersonCard_begDate between :PCSD_begDate and :PCSD_endDate and NextCard.Lpu_id = :ToLpu_id and Polis.Polis_id is not null "
					: " and PCSD.PersonCard_endDate between :PCSD_begDate and :PCSD_endDate and Polis.Polis_id is not null ";
				break;
		}
		$queryParams["PCSD_begDate"] = $data["PCSD_StartDate"];
		$queryParams["PCSD_endDate"] = $data["PCSD_EndDate"];
	}

	public static function selectParams_PersonCardStateDetail(&$data, &$filter, &$queryParams)
	{
		if ($data["PCSD_LpuMotion_id"] == 2) {
			$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id != PCSD.Lpu_id ";
		}
		if ($data["PCSD_LpuMotion_id"] == 3) {
			$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id = PCSD.Lpu_id ";
		}
		if (isset($data["PCSD_FromLpu_id"]) && (int)$data["PCSD_FromLpu_id"] > 0) {
			$queryParams["FromLpu_id"] = (int)$data["PCSD_FromLpu_id"];
			$filter .= " and LastCard.Lpu_id is not NULL and LastCard.Lpu_id = :FromLpu_id ";
		}
		if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
			$queryParams["ToLpu_id"] = (int)$data["PCSD_ToLpu_id"];
			$filter .= " and NextCard.Lpu_id is not NULL and NextCard.Lpu_id = :ToLpu_id ";
		}
		if (isset($data["PCSD_LpuRegion_id"])) {
			$queryParams["PCSD_LpuRegion_id"] = $data["PCSD_LpuRegion_id"];
			$filter .= " and PCSD.LpuRegion_id = :PCSD_LpuRegion_id ";
		} else {
			$filter .= " and PCSD.LpuRegion_id is null and PCSD.Lpu_id = :Lpu_id ";
		}
		if (isset($data["PCSD_LpuAttachType_id"]) && $data["PCSD_LpuAttachType_id"] > 0) {
			$queryParams["PCSD_LpuAttachType_id"] = $data["PCSD_LpuAttachType_id"];
			$filter .= " and PCSD.LpuAttachType_id = :PCSD_LpuAttachType_id ";
		}
		switch ($data["PCSD_mode"]) {
			case "BegCount":
				$filter .= "
					and PCSD.PersonCard_begDate < :PCSD_begDate
					and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "BegCountBDZ":
				$filter .= "
					and PCSD.PersonCard_begDate < :PCSD_begDate
					and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
					and PolisBeg.Polis_id is not null
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "BegCountNotInBDZ":
				$filter .= "
					and PCSD.PersonCard_begDate::date < :PCSD_begDate
					and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate >= :PCSD_begDate)
					and PolisBeg.Polis_id is null
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "EndCount":
				$filter .= "
					and PCSD.PersonCard_begDate::date <= :PCSD_endDate
					and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate::date > :PCSD_endDate)
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "EndCountBDZ":
				$filter .= "
					and PCSD.PersonCard_begDate::date <= :PCSD_endDate
					and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate::date > :PCSD_endDate)
					and PolisEnd.Polis_id is not null
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "EndCountNotInBDZ":
				$filter .= "
					and PCSD.PersonCard_begDate::date <= :PCSD_endDate
					and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate::date > :PCSD_endDate)
					and PolisEnd.Polis_id is null
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "AttachCount":
				$filter .= "
					and PCSD.PersonCard_begDate between :PCSD_begDate and :PCSD_endDate
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "AttachCountBDZ":
				$filter .= "
					and PCSD.PersonCard_begDate between :PCSD_begDate and :PCSD_endDate
					and Polis.Polis_id is not null
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "AttachIncomeBDZ":
				$filter .= "
					and PCSD.PersonCard_begDate::date between '1970-01-01' and :PCSD_endDate
					and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate::date >= :PCSD_begDate)
					and PS.Server_pid = 0
					and hasPolis.cnt is not null
					and notHasPolis.cnt is null
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "AttachOutcomeBDZ":
				$filter .= "
					and PCSD.PersonCard_begDate::date between '1970-01-01' and :PCSD_endDate
					and (PCSD.PersonCard_endDate is null or PCSD.PersonCard_endDate::date >= :PCSD_begDate)
					and PS.Server_pid = 0
					and HasPolisBefore.cnt is not null
				";
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= " and 1 = 2 ";
				}
				break;
			case "DettachCount":
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= "
						and NextCard.PersonCard_begDate::date between :PCSD_begDate and :PCSD_endDate and NextCard.Lpu_id = :ToLpu_id
					";
				} else {
					$filter .= "
						and PCSD.PersonCard_endDate::date between :PCSD_begDate and :PCSD_endDate
					";
				}
				break;
			case "DettachCountBDZ":
				if (isset($data["PCSD_ToLpu_id"]) && (int)$data["PCSD_ToLpu_id"] > 0) {
					$filter .= "
						and NextCard.PersonCard_begDate::date between :PCSD_begDate and :PCSD_endDate and NextCard.Lpu_id = :ToLpu_id
						and Polis.Polis_id is not null
					";
				} else {
					$filter .= "
						and PCSD.PersonCard_endDate::date between :PCSD_begDate and :PCSD_endDate
						and Polis.Polis_id is not null
					";
				}
				break;
		}
		$queryParams["PCSD_begDate"] = $data["PCSD_StartDate"];
		$queryParams["PCSD_endDate"] = $data["PCSD_EndDate"];
	}

	public static function selectParams_EvnUslugaPar(Search_model $callObject, &$data, &$filter, &$queryParams)
	{
		if (getRegionNick() == 'penza' && havingGroup('OuzSpec')) {
			if (isset($data['EUPSWLpu_id'])) {
				$filter .= " and lpu.Lpu_id = :EUPSWLpu_id";
				$queryParams['EUPSWLpu_id'] = $data['EUPSWLpu_id'];
			} else {
				$filter .= " and lpu.Region_id = 58";
			}
		}
		if (!empty($data["UslugaExecutionType_id"])) {
			$filter .= " and coalesce(ELR.UslugaExecutionType_id, 4) = :UslugaExecutionType_id";
			$queryParams["UslugaExecutionType_id"] = $data["UslugaExecutionType_id"];
			if (in_array($data["UslugaExecutionType_id"], [1, 2])) {
				$filter .= " and EUP.EvnUslugaPar_setDate is not null";
			}
		}
		$filter .= " and eup.EvnUslugaPar_setDate is not null";
		if (isset($data["EvnDirection_Num"])) {
			$filter .= " AND CASE WHEN ED.EvnDirection_id IS NOT NULL THEN ED.EvnDirection_Num ELSE EUP.EvnDirection_Num END  = :EvnDirection_Num";
//			$filter .= " and ED.EvnDirection_Num = :EvnDirection_Num";
			$queryParams["EvnDirection_Num"] = $data["EvnDirection_Num"];
		}
		if (isset($data["EvnDirection_setDate"])) {
			$filter .= " and ED.EvnDirection_setDT = :EvnDirection_setDate";
			$queryParams["EvnDirection_setDate"] = $data["EvnDirection_setDate"];
		}
		if (isset($data["EvnUslugaPar_setDate_Range"][0])) {
			$filter .= "   
				  AND CAST((CASE
				                WHEN ED.EvnDirection_id IS NOT NULL THEN ED.EvnDirection_setDT
				                ELSE EUP.EvnDirection_setDT END) AS DATE)
				    >= CAST(:EvnUslugaPar_setDate_Range_0 AS DATE)
             ";
//			$filter .= " and EUP.EvnUslugaPar_setDT::date >= :EvnUslugaPar_setDate_Range_0::date";
			$queryParams["EvnUslugaPar_setDate_Range_0"] = $data["EvnUslugaPar_setDate_Range"][0];
		}
		if (isset($data["EvnUslugaPar_setDate_Range"][1])) {
			$filter .= " 
				  AND CAST((CASE
			                WHEN ED.EvnDirection_id IS NOT NULL THEN ED.EvnDirection_setDT
			                ELSE EUP.EvnDirection_setDT END) AS DATE)
			        <= CAST(:EvnUslugaPar_setDate_Range_1 AS DATE)
			";
//			$filter .= " and EUP.EvnUslugaPar_setDT::date <= :EvnUslugaPar_setDate_Range_1::date";
			$queryParams["EvnUslugaPar_setDate_Range_1"] = $data["EvnUslugaPar_setDate_Range"][1];
		}
		if (isset($data["LpuSection_uid"])) {
			$filter .= " and EUP.LpuSection_uid = :LpuSection_uid";
			$queryParams["LpuSection_uid"] = $data["LpuSection_uid"];
		}
		if (isset($data["LpuSection_did"])) {
			$filter .= " AND coalesce(ED.LpuSection_id, EUP.LpuSection_did) = :LpuSection_did";     // NGS
			//$filter .= " and coalesce(ED.LpuSection_did, EUP.LpuSection_did) = :LpuSection_did";  // NGS
			$queryParams["LpuSection_did"] = $data["LpuSection_did"];
		}
		if (isset($data["MedPersonal_did"])) {
			$filter .= " AND CASE WHEN ED.MedPersonal_id IS NOT NULL THEN ED.MedPersonal_id ELSE EUP.MedPersonal_did END = :MedPersonal_did"; // NGS
			//$filter .= " and EUP.MedPersonal_did = :MedPersonal_did";                            // NGS
			$queryParams["MedPersonal_did"] = $data["MedPersonal_did"];
		}
		if (isset($data["MedPersonal_uid"])) {
			$filter .= " and EUP.MedPersonal_id = :MedPersonal_uid";
			$queryParams["MedPersonal_uid"] = $data["MedPersonal_uid"];
		}
		if (isset($data["PayType_id"])) {
			$filter .= " and EUP.PayType_id = :PayType_id";
			$queryParams["PayType_id"] = $data["PayType_id"];
		}
		if (isset($data["PrehospDirect_id"])) {
			$filter .= " AND CASE WHEN ED.PrehospDirect_id IS NOT NULL THEN ED.PrehospDirect_id ELSE EUP.PrehospDirect_id END = :PrehospDirect_id";
			//$filter .= " and coalesce(ED.PrehospDirect_id, EUP.PrehospDirect_id) = :PrehospDirect_id";
			$queryParams["PrehospDirect_id"] = $data["PrehospDirect_id"];
		}
		if (isset($data["UslugaCategory_id"])) {
			$filter .= " and UslugaComplex.UslugaCategory_id = :UslugaCategory_id";
			$queryParams["UslugaCategory_id"] = $data["UslugaCategory_id"];
		}
		if (isset($data["UslugaComplex_id"])) {
			$queryParams["UslugaComplex_id"] = $data["UslugaComplex_id"];
		}
		if (isset($data["Org_did"])) {
			$filter .= " and coalesce(LD_sid.Org_id, EUP.Org_did) = :Org_did";
			$queryParams["Org_did"] = $data["Org_did"];
		}
		if ($callObject->regionNick == "kz") {
			if ($data["toAis25"] == 2) {
				$filter .= " and air.AISResponse_id is not null";
			} elseif ($data["toAis25"] == 1) {
				$filter .= " and air.AISResponse_id is null and ucl.AISFormLoad_id = 1";
			}
			if ($data["toAis259"] == 2) {
				$filter .= " and air9.AISResponse_id is not null";
			} elseif ($data["toAis259"] == 1) {
				$filter .= " and air9.AISResponse_id is null and ucl.AISFormLoad_id = 2";
			}
		}
	}

	public static function selectParams_EvnReceptGeneral(&$data, &$filter, &$queryParams)
	{
		if ($data["EvnRecept_IsSigned"] > 0) {
			$filter .= " and ERG.EvnReceptGeneral_IsSigned = :EvnRecept_IsSigned";
			$queryParams["EvnRecept_IsSigned"] = $data["EvnRecept_IsSigned"];
		}
		if ($data["Drug_id"] > 0) {
			$filter .= " and ERDrug.Drug_id = :Drug_id";
			$queryParams["Drug_id"] = $data["Drug_id"];
		}
		if ($data["ER_MedPersonal_id"] > 0) {
			$filter .= " and ERG.MedPersonal_id = :ER_MedPersonal_id";
			$queryParams["ER_MedPersonal_id"] = $data["ER_MedPersonal_id"];
		}
		if (strlen($data["EvnRecept_Num"]) > 0) {
			$filter .= " and ERG.EvnReceptGeneral_Num = :EvnReceptGeneral_Num";
			$queryParams["EvnReceptGeneral_Num"] = $data["EvnRecept_Num"];
		}
		if (strlen($data["EvnRecept_Ser"]) > 0) {
			$filter .= " and ERG.EvnReceptGeneral_Ser = :EvnReceptGeneral_Ser";
			$queryParams["EvnReceptGeneral_Ser"] = $data["EvnRecept_Ser"];
		}
		if (isset($data["EvnRecept_setDate"])) {
			$queryParams["EvnReceptGeneral_setDate"] = $data["EvnRecept_setDate"];
		}
		if (isset($data["EvnRecept_setDate_Range"][0])) {
			$queryParams["EvnReceptGeneral_setDate_Range_0"] = $data["EvnRecept_setDate_Range"][0];
		}
		if (isset($data["EvnRecept_setDate_Range"][1])) {
			$queryParams["EvnReceptGeneral_setDate_Range_1"] = $data["EvnRecept_setDate_Range"][1];
		}
		switch ($data["EvnReceptSearchDateType"]) {
			case "obr":
				if (isset($data["EvnRecept_setDate"])) {
					$filter .= " and ERG.EvnReceptGeneral_obrDT = :EvnReceptGeneral_setDate::timestamp";
				}
				if (isset($data["EvnRecept_setDate_Range"][0])) {
					$filter .= " and ERG.EvnReceptGeneral_obrDT >= :EvnReceptGeneral_setDate_Range_0::timestamp";
				}
				if (isset($data["EvnRecept_setDate_Range"][1])) {
					$filter .= " and ERG.EvnReceptGeneral_obrDT <= :EvnReceptGeneral_setDate_Range_1::timestamp";
				}
				break;
			case "obesp":
				if (isset($data["EvnRecept_setDate"])) {
					$filter .= " and ERG.EvnReceptGeneral_otpDT = :EvnReceptGeneral_setDate::timestamp";
				}
				if (isset($data["EvnRecept_setDate_Range"][0])) {
					$filter .= " and ERG.EvnReceptGeneral_otpDT >= :EvnReceptGeneral_setDate_Range_0::timestamp";
				}
				if (isset($data["EvnRecept_setDate_Range"][1])) {
					$filter .= " and ERG.EvnReceptGeneral_otpDT <= :EvnReceptGeneral_setDate_Range_1::timestamp";
				}
				break;
			default:
				if (isset($data["EvnRecept_setDate"])) {
					$filter .= " and ERG.EvnReceptGeneral_setDate = :EvnReceptGeneral_setDate::timestamp";
				}
				if (isset($data["EvnRecept_setDate_Range"][0])) {
					$filter .= " and ERG.EvnReceptGeneral_setDate >= :EvnReceptGeneral_setDate_Range_0::timestamp";
				}
				if (isset($data["EvnRecept_setDate_Range"][1])) {
					$filter .= " and ERG.EvnReceptGeneral_setDate <= :EvnReceptGeneral_setDate_Range_1::timestamp";
				}
				break;
		}
		if (isset($data["OrgFarmacyIndex_OrgFarmacy_id"])) {
			$queryParams["OrgFarmacyIndex_OrgFarmacy_id"] = $data["OrgFarmacyIndex_OrgFarmacy_id"];
			if ($data["OrgFarmacyIndex_OrgFarmacy_id"] > 0) {
				$filter .= " and ERG.Lpu_id in (select Lpu_id from v_OrgFarmacyIndex where OrgFarmacy_id = :OrgFarmacyIndex_OrgFarmacy_id)";
			}
		}
		if (!(isset($data["inValidRecept"]) && $data["inValidRecept"] == 1)) {
			$filter .= "
				and (
					(RV.ReceptValidType_id = 1 and (ERG.EvnReceptGeneral_setDate + RV.ReceptValid_Value * interval '1 day') >= (select dt from mv)) or
					(RV.ReceptValidType_id = 2 and (ERG.EvnReceptGeneral_setDate + (RV.ReceptValid_Value * 30 * interval '1 day')) >= (select dt from mv))
				)
			";
		}
		if (isset($data["ReceptDelayType_id"]) && $data["ReceptDelayType_id"] != 7) {
			if ($data["ReceptDelayType_id"] != 6) {
				$filter .= " and ERG.ReceptDelayType_id = :ReceptDelayType_id";
				$queryParams["ReceptDelayType_id"] = $data["ReceptDelayType_id"];
			} else {
				$filter .= "
					and ERG.ReceptDelayType_id is null
					and (
						(RV.ReceptValidType_id = 1 and (ERG.EvnReceptGeneral_setDate + RV.ReceptValid_Value * interval '1 day') >= (select dt from mv)) or
						(RV.ReceptValidType_id = 2 and (ERG.EvnReceptGeneral_setDate + (RV.ReceptValid_Value * 30 * interval '1 day')) >= (select dt from mv))
						)
					and not exists(
						select RO.ReceptOtov_id
						from v_ReceptOtov RO
						where RO.EvnRecept_id = ERG.EvnReceptGeneral_id
						limit 1
					)
				";
			}
		}
		if (isset($data["Drug_Name"])) {
			$filter .= "
				and (
					DCM.DrugComplexMnn_RusName ilike '%'||:Drug_Name||'%' or
					ERDrug.Drug_Name ilike '%'||:Drug_Name||'%' or
					ERDrugRls.Drug_Name ilike '%'||:Drug_Name||'%'
				)
			";
			$queryParams["Drug_Name"] = $data["Drug_Name"];
		}
	}

	public static function selectParams_EvnRecept(Search_model $callObject, $isFarmacy, &$data, &$filter, &$queryParams)
	{
		if (!empty($data["WithDrugComplexMnn"]) && $data["WithDrugComplexMnn"]) {
			$filter .= " and coalesce(ER.DrugComplexMnn_id, ERDrugRls.DrugComplexMnn_id) is not null";
		}
		if (!empty($data["EvnRecept_MarkDeleted"])) {
			$filter .= " and ER_RDT.ReceptDelayType_Code = 4 ";
		}
		if (!empty($data["EvnRecept_IsSigned"])) {
			if ($data["EvnRecept_IsSigned"] == "2") {
				$filter .= " and (ER.EvnRecept_IsSigned = 2 or (ER.pmUser_signID is not null and ER.EvnRecept_signDT is not null)) ";
			}
			if ($data["EvnRecept_IsSigned"] == "1") {
				$filter .= " and (coalesce(ER.EvnRecept_IsSigned,1) <> 2 and ER.pmUser_signID is null and ER.EvnRecept_signDT is null) ";
			}
		}
		if (!empty($data["WhsDocumentCostItemType_id"]) && $data["WhsDocumentCostItemType_id"]) {
			$filter .= " and wdcit.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
			$queryParams["WhsDocumentCostItemType_id"] = $data["WhsDocumentCostItemType_id"];
		}
		if ((strlen($data["ER_Diag_Code_From"]) > 0) || (strlen($data["ER_Diag_Code_To"]) > 0)) {
			if (strlen($data["ER_Diag_Code_From"]) > 0) {
				$filter .= " and ERDiag.Diag_Code >= :ER_Diag_Code_From";
				$queryParams["ER_Diag_Code_From"] = $data["ER_Diag_Code_From"];
			}
			if (strlen($data["ER_Diag_Code_To"]) > 0) {
				$filter .= " and ERDiag.Diag_Code <= :ER_Diag_Code_To";
				$queryParams["ER_Diag_Code_To"] = $data["ER_Diag_Code_To"];
			}
		}
		if (!$isFarmacy) {
			$callObject->getPrivilegeAccessRightsFilters($data, $filter, $queryParams);
		}
		if ($data["Drug_id"] > 0) {
			$filter .= " and ERDrug.Drug_id = :Drug_id";
			$queryParams["Drug_id"] = $data["Drug_id"];
		}
		if ($data["DrugMnn_id"] > 0) {
			$filter .= " and ERDrug.DrugMnn_id = :DrugMnn_id";
			$queryParams["DrugMnn_id"] = $data["DrugMnn_id"];
		}
		if ($data["ER_MedPersonal_id"] > 0) {
			$filter .= " and ER.MedPersonal_id = :ER_MedPersonal_id";
			$queryParams["ER_MedPersonal_id"] = $data["ER_MedPersonal_id"];
		}
		if (isset($data["ER_PrivilegeType_id"])) {
			$filter .= " and ER.PrivilegeType_id = :ER_PrivilegeType_id";
			$queryParams["ER_PrivilegeType_id"] = $data["ER_PrivilegeType_id"];
		}
		if (isset($data["EvnRecept_Is7Noz"])) {
			$filter .= " and coalesce(ER.EvnRecept_Is7Noz, 1) = :EvnRecept_Is7Noz";
			$queryParams["EvnRecept_Is7Noz"] = $data["EvnRecept_Is7Noz"];
		}
		if (isset($data["ReceptForm_id"])) {
			$filter .= " and ER.ReceptForm_id = :ReceptForm_id";
			$queryParams["ReceptForm_id"] = $data["ReceptForm_id"];
		}
		if ($data["EvnRecept_IsKEK"] > 0) {
			$filter .= " and ER.EvnRecept_IsKEK = :EvnRecept_IsKEK";
			$queryParams["EvnRecept_IsKEK"] = $data["EvnRecept_IsKEK"];
		}
		if (!empty($data['EvnRecept_VKProtocolNum'])) {
			$filter .= " and ER.EvnRecept_VKProtocolNum = :EvnRecept_VKProtocolNum";
			$queryParams['EvnRecept_VKProtocolNum'] = $data['EvnRecept_VKProtocolNum'];
		}
		if (!empty($data['EvnRecept_VKProtocolDT'])) {
			$filter .= " and ER.EvnRecept_VKProtocolDT = :EvnRecept_VKProtocolDT";
			$queryParams['EvnRecept_VKProtocolDT'] = $data['EvnRecept_VKProtocolDT'];
		}
		if ($data["EvnRecept_IsNotOstat"] > 0) {
			$filter .= " and coalesce(ER.EvnRecept_IsNotOstat, 1) = :EvnRecept_IsNotOstat";
			$queryParams["EvnRecept_IsNotOstat"] = $data["EvnRecept_IsNotOstat"];
		}
		if (strlen($data["EvnRecept_Num"]) > 0) {
			$filter .= " and replace(ltrim(replace(ER.EvnRecept_Num, '0', ' ')), ' ', '0') = replace(ltrim(replace(:EvnRecept_Num, '0', ' ')), ' ', '0')";
			$queryParams["EvnRecept_Num"] = $data["EvnRecept_Num"];
		}
		if (strlen($data["EvnRecept_Ser"]) > 0) {
			$filter .= " and ER.EvnRecept_Ser = :EvnRecept_Ser";
			$queryParams["EvnRecept_Ser"] = $data["EvnRecept_Ser"];
		}
		if (isset($data["EvnRecept_setDate"])) {
			$queryParams["EvnRecept_setDate"] = $data["EvnRecept_setDate"];
		}
		if (isset($data["EvnRecept_setDate_Range"][0])) {
			$queryParams["EvnRecept_setDate_Range_0"] = $data["EvnRecept_setDate_Range"][0];
		}
		if (isset($data["EvnRecept_setDate_Range"][1])) {
			$queryParams["EvnRecept_setDate_Range_1"] = $data["EvnRecept_setDate_Range"][1];
		}
		switch ($data["EvnReceptSearchDateType"]) {
			case "obr":
				if (isset($data["EvnRecept_setDate"])) {
					$filter .= " and ER.EvnRecept_obrDT = :EvnRecept_setDate::timestamp";
				}
				if (isset($data["EvnRecept_setDate_Range"][0])) {
					$filter .= " and ER.EvnRecept_obrDT >= :EvnRecept_setDate_Range_0::timestamp";
				}
				if (isset($data["EvnRecept_setDate_Range"][1])) {
					$filter .= " and ER.EvnRecept_obrDT <= :EvnRecept_setDate_Range_1::timestamp";
				}
				break;
			case "obesp":
				if (isset($data["EvnRecept_setDate"])) {
					$filter .= " and ER.EvnRecept_otpDT = :EvnRecept_setDate::timestamp";
				}
				if (isset($data["EvnRecept_setDate_Range"][0])) {
					$filter .= " and ER.EvnRecept_otpDT >= :EvnRecept_setDate_Range_0::timestamp";
				}
				if (isset($data["EvnRecept_setDate_Range"][1])) {
					$filter .= " and ER.EvnRecept_otpDT <= :EvnRecept_setDate_Range_1::timestamp";
				}
				break;
			case "otkaz":
				if (isset($data["EvnRecept_setDate"])) {
					$filter .= "
						and (
							(Wr.ReceptWrong_id is not null and ER.EvnRecept_obrDT = :EvnRecept_setDate::timestamp) or
							(WDUARO.WhsDocumentUcActReceptOut_id is not null and WDUARO.WhsDocumentUcActReceptOut_setDT = :EvnRecept_setDate::timestamp)
						)
					";
				}
				if (isset($data["EvnRecept_setDate_Range"][0])) {
					$filter .= "
						and (
							(Wr.ReceptWrong_id is not null and ER.EvnRecept_obrDT = :EvnRecept_setDate_Range_0::timestamp) or
							(WDUARO.WhsDocumentUcActReceptOut_id is not null and WDUARO.WhsDocumentUcActReceptOut_setDT = :EvnRecept_setDate_Range_0::timestamp)
						)
					";
				}
				if (isset($data["EvnRecept_setDate_Range"][1])) {
					$filter .= "
						and (
							(Wr.ReceptWrong_id is not null and ER.EvnRecept_obrDT = :EvnRecept_setDate_Range_1::timestamp) or
							(WDUARO.WhsDocumentUcActReceptOut_id is not null and WDUARO.WhsDocumentUcActReceptOut_setDT = :EvnRecept_setDate_Range_1::timestamp)
						)
					";
				}
				break;
			default:
				if (isset($data["EvnRecept_setDate"])) {
					$filter .= " and ER.EvnRecept_setDate = :EvnRecept_setDate::timestamp";
				}
				if (isset($data["EvnRecept_setDate_Range"][0])) {
					$filter .= " and ER.EvnRecept_setDate >= :EvnRecept_setDate_Range_0::timestamp";
				}
				if (isset($data["EvnRecept_setDate_Range"][1])) {
					$filter .= " and ER.EvnRecept_setDate <= :EvnRecept_setDate_Range_1::timestamp";
				}
				break;
		}
		if (isset($data["OrgFarmacy_id"])) {
			$queryParams["OrgFarmacy_id"] = $data["OrgFarmacy_id"];
			if ($data["OrgFarmacy_id"] == -1) {
				$filter .= " and ER.OrgFarmacy_id is null";
			} else {
				$filter .= " and ER.OrgFarmacy_id = :OrgFarmacy_id";
			}
		}
		if (isset($data["OrgFarmacyIndex_OrgFarmacy_id"])) {
			$queryParams["OrgFarmacyIndex_OrgFarmacy_id"] = $data["OrgFarmacyIndex_OrgFarmacy_id"];
			if ($data["OrgFarmacyIndex_OrgFarmacy_id"] > 0) {
				$filter .= "
					and ER.Lpu_id in (
						select
							Lpu_id
						from v_OrgFarmacyIndex
						where OrgFarmacy_id = :OrgFarmacyIndex_OrgFarmacy_id
					)
					and coalesce(ER.OrgFarmacy_id, '') = :OrgFarmacyIndex_OrgFarmacy_id";
			}
		}
		if ($data["ReceptDiscount_id"] > 0) {
			$filter .= " and ER.ReceptDiscount_id = :ReceptDiscount_id";
			$queryParams["ReceptDiscount_id"] = $data["ReceptDiscount_id"];
		}
		if ($data["ReceptFinance_id"] > 0) {
			$filter .= " and ER.ReceptFinance_id = :ReceptFinance_id";
			$queryParams["ReceptFinance_id"] = $data["ReceptFinance_id"];
		}
		if ($data["ReceptType_id"] > 0) {
			$filter .= " and ER.ReceptType_id = :ReceptType_id";
			$queryParams["ReceptType_id"] = $data["ReceptType_id"];
		}
		if ($data["ReceptValid_id"] > 0) {
			$filter .= " and ER.ReceptValid_id = :ReceptValid_id";
			$queryParams["ReceptValid_id"] = $data["ReceptValid_id"];
		}
		if (isset($data["EvnRecept_IsExtemp"])) {
			$filter .= " and coalesce(ER.EvnRecept_IsExtemp, 1) = :EvnRecept_IsExtemp";
			$queryParams["EvnRecept_IsExtemp"] = $data["EvnRecept_IsExtemp"];
		}
		if (!(isset($data["inValidRecept"]) && $data["inValidRecept"] == 1) && (isset($data["DistributionPoint"]) && $data["DistributionPoint"] == 1)) {
			$filter .= "
				and (
					(RV.ReceptValidType_id = 1 and (ER.EvnRecept_setDate + RV.ReceptValid_Value * interval '1 day') >= (select dt from mv)) or
					(RV.ReceptValidType_id = 2 and (ER.EvnRecept_setDate + (RV.ReceptValid_Value * 30 * interval '1 day')) >= (select dt from mv))
				)
			";
		}
		if (isset($data["ReceptDelayType_id"]) && $data["ReceptDelayType_id"] != 7) {
			if ($data["ReceptDelayType_id"] != 6) {
				$filter .= " and ER.ReceptDelayType_id = :ReceptDelayType_id";
				$queryParams["ReceptDelayType_id"] = $data["ReceptDelayType_id"];
			} else {
				$filter .= "
					and ER.ReceptDelayType_id is null and (
						(RV.ReceptValidType_id = 1 and (ER.EvnRecept_setDate + RV.ReceptValid_Value * interval '1 day') >= (select dt from mv)) or
						(RV.ReceptValidType_id = 2 and (ER.EvnRecept_setDate + (RV.ReceptValid_Value * 30 * interval '1 day')) >= (select dt from mv))
					)
				";
			}
		}
		if (isset($data["Drug_Name"])) {
			$filter .= "
				and (
					DCM.DrugComplexMnn_RusName ilike '%'||:Drug_Name||'%' or
					ERDrug.Drug_Name ilike '%'||:Drug_Name||'%' or
					ERDrugRls.Drug_Name ilike '%'||:Drug_Name||'%' or
					ER.EvnRecept_ExtempContents ilike '%'||:Drug_Name||'%'
				)
			";
			$queryParams["Drug_Name"] = $data["Drug_Name"];
		}
	}

   public static function selectParams_KvsEvnStick(Search_model $callObject, $dbf, &$data, &$filter, &$queryParams)
	{
		if ($dbf === true) {
			if ($data["SearchFormType"] == "KvsEvnPS" && isset($data["EvnPS_InRegistry"])) {
				if (in_array($callObject->regionNick, ["ekb"])) {
					if ($data["EvnPS_InRegistry"] == 1) {
						$filter .= " and (EPS.EvnPS_IsInReg = 1 or EPS.EvnPS_IsInReg is null)";
					} elseif ($data["EvnPS_InRegistry"] == 2) {
						$filter .= " and EPS.EvnPS_IsInReg = 2";
					}
				} elseif (in_array($callObject->regionNick, ["penza"])) {
					if ($data["EvnPS_InRegistry"] == 1) {
						$filter .= "
							and not exists (
								select EvnSection_IsInReg
								from v_EvnSection ES
								where ES.EvnSection_rid = EPS.EvnPS_id and ES.EvnSection_IsInReg = 2
								limit 1
							)
						";
					} elseif ($data["EvnPS_InRegistry"] == 2) {
						$filter .= "
							and exists (
								select EvnSection_IsInReg
								from v_EvnSection ES
								where ES.EvnSection_rid = EPS.EvnPS_id and ES.EvnSection_IsInReg = 2
								limit 1
							)
						";
					}
				}
			}
		} else {
			if ($data["SearchFormType"] == "EvnPS") {
				if (isset($data["toERSB"]) and $data["toERSB"] != 0 and $callObject->regionNick == "kz") {
					$code = ($data["toERSB"] == 2) ? "not" : "";
					$filter .= "
						and {$code} EXISTS (
							select osl.ObjectSynchronLog_id
							from ObjectSynchronLog osl
							where osl.ObjectSynchronLogService_id = 6 and osl.Object_id = eps.EvnPS_id
							limit 1
						)
					";
				}
				$acDiagFilter = getRevertAccessRightsDiagFilter("ESSDiag.Diag_Code");
				if (!empty($acDiagFilter)) {
					$filter .= " and adf.EvnSection_id is null";
				}
			} elseif ($data["SearchFormType"] == "EvnSection") {
				$acDiagFilter = getRevertAccessRightsDiagFilter("ESSDiag.Diag_Code");
				if (!empty($acDiagFilter)) {
					$filter .= " and adf.EvnSection_id is null";
				}
			}
			if ($data["PersonPeriodicType_id"] == 3) {
				if ($data["SearchFormType"] == "EvnPS") {
					$filter .= "
						and PS.PersonEvn_id = coalesce(EPSLastES.PersonEvn_id, CPS.PersonEvn_id)
						and PS.Server_id = coalesce(EPSLastES.Server_id, CPS.Server_id)
					";
				} elseif ($data["SearchFormType"] == "EvnSection") {
					$filter .= "
						and PS.PersonEvn_id = coalesce(ESEC.PersonEvn_id, CPS.PersonEvn_id)
						and PS.Server_id = coalesce(ESEC.Server_id, CPS.Server_id)
					";
				}
			}
		}
		switch ($data["SearchFormType"]) {
			case "EvnPS":
				if (isset($data["EvnPS_InRegistry"])) {
					if (in_array($callObject->regionNick, ["ekb"])) {
						if ($data["EvnPS_InRegistry"] == 1) {
							$filter .= " and (EPS.EvnPS_IsInReg = 1 or EPS.EvnPS_IsInReg is null)";
						} elseif ($data["EvnPS_InRegistry"] == 2) {
							$filter .= " and EPS.EvnPS_IsInReg = 2";
						}
					} elseif (in_array($callObject->regionNick, ["penza"])) {
						if ($data["EvnPS_InRegistry"] == 1) {
							$filter .= "
								and not exists (
									select EvnSection_IsInReg
									from v_EvnSection ES
									where ES.EvnSection_rid = EPS.EvnPS_id AND ES.EvnSection_IsInReg = 2
									limit 1
								)
							";
						} elseif ($data["EvnPS_InRegistry"] == 2) {
							$filter .= "
								and exists (
									select EvnSection_IsInReg
									from v_EvnSection ES
									where ES.EvnSection_rid = EPS.EvnPS_id and ES.EvnSection_IsInReg = 2
									limit 1
								)
							";
						}
					}
				}
				break;
			case "EvnSection":
				if (isset($data["EvnPS_InRegistry"])) {
					if (in_array($callObject->regionNick, ["ekb"])) {
						if ($data["EvnPS_InRegistry"] == 1) {
							$filter .= " and (EPS.EvnPS_IsInReg = 1 or EPS.EvnPS_IsInReg is null)";
						} elseif ($data["EvnPS_InRegistry"] == 2) {
							$filter .= " and EPS.EvnPS_IsInReg = 2";
						}
					} elseif (in_array($callObject->regionNick, ["penza"])) {
						if ($data["EvnPS_InRegistry"] == 1) {
							$filter .= " and (ESEC.EvnSection_IsInReg = 1 or ESEC.EvnSection_IsInReg is null)";
						} elseif ($data["EvnPS_InRegistry"] == 2) {
							$filter .= " and ESEC.EvnSection_IsInReg = 2";
						}
					}
				}
				break;
		}
		if (isset($data["EvnDirection_Num"])) {
			$filter .= " and EPS.EvnDirection_Num = :EvnDirection_Num";
			$queryParams["EvnDirection_Num"] = $data["EvnDirection_Num"];
		}
		if (isset($data["EvnSection_IsAdultEscort"])) {
			switch ($data["SearchFormType"]) {
				case "EvnPS":
					switch ($data["EvnSection_IsAdultEscort"]) {
						case 2:
							$filter .= " and EPSLastES.EvnSection_IsAdultEscort = 2";
							break;
						case 1:
							$filter .= " and (EPSLastES.EvnSection_IsAdultEscort = 1 or EPSLastES.EvnSection_IsAdultEscort is null)";
							break;
					}
					break;
				case "EvnSection":
					switch ($data["EvnSection_IsAdultEscort"]) {
						case 2:
							$filter .= " and ESEC.EvnSection_IsAdultEscort = 2";
							break;
						case 1:
							$filter .= " and (ESEC.EvnSection_IsAdultEscort = 1 or ESEC.EvnSection_IsAdultEscort is null)";
							break;
					}
					break;
			}
		}
		if (isset($data["EvnDirection_setDate_Range"][0])) {
			$filter .= " and EPS.EvnDirection_setDT >= :EvnDirection_setDate_Range_0::timestamp";
			$queryParams["EvnDirection_setDate_Range_0"] = $data["EvnDirection_setDate_Range"][0];
		}
		if (isset($data["EvnDirection_setDate_Range"][1])) {
			$filter .= " and EPS.EvnDirection_setDT <= :EvnDirection_setDate_Range_1::timestamp";
			$queryParams["EvnDirection_setDate_Range_1"] = $data["EvnDirection_setDate_Range"][1];
		}
		if (!empty($data["Hospitalization_id"]) && $callObject->regionNick == "kz") {
			switch ($data["Hospitalization_id"]) {
				case 1:
					$filter .= " and epsl.Hospitalization_id IS NULL";
					break;
				case 2:
					$filter .= " and epsl.Hospitalization_id IS NOT NULL";
					break;
			}
		}
		if (isset($data["EvnPS_disDate_Range"][0])) {
			$stats_hour = "+9 hours";
			if ($data['session']['region']['nick'] == 'msk') $stats_hour = "+8 hours";
			$new_date = date('Y-m-d H:i:s', strtotime($stats_hour, strtotime($data['EvnPS_disDate_Range'][0])));
			if ($data['session']['region']['nick'] == 'ufa' || $data['session']['region']['nick'] == 'msk')
				$temp_date = date('Y-m-d H:i:s', strtotime("-1 days", strtotime($new_date)));
			else
				$temp_date = $new_date;
			
			switch ($data["SearchFormType"]) {
				case "EvnPS":
				case "KvsEvnPS":
					if (isset($data["Date_Type"]) && $data["Date_Type"] == "2") {
						$filter .= " and EPSLastES.EvnSection_disDT >= :EvnPS_disDate_Range_0";
						$queryParams["EvnPS_disDate_Range_0"] = $temp_date;
					} else {
						$filter .= " and EPSLastES.EvnSection_disDate >= :EvnPS_disDate_Range_0";
						$queryParams["EvnPS_disDate_Range_0"] = $data["EvnPS_disDate_Range"][0];
					}
					break;
				case "KvsPerson":
					if (isset($data["Date_Type"]) && $data["Date_Type"] == "2") {
						$filter .= " and EPSLastES2.EvnSection_disDT >= :EvnPS_disDate_Range_0";
						$queryParams["EvnPS_disDate_Range_0"] = $temp_date;
					} else {
						$filter .= " and EPSLastES2.EvnSection_disDate >= :EvnPS_disDate_Range_0";
						$queryParams["EvnPS_disDate_Range_0"] = $data["EvnPS_disDate_Range"][0];
					}
					break;
				case "EvnSection":
				case "KvsEvnSection":
					if (isset($data["Date_Type"]) && $data["Date_Type"] == "2") {
						$filter .= " and ESEC.EvnSection_disDT >= :EvnPS_disDate_Range_0";
						$queryParams["EvnPS_disDate_Range_0"] = $temp_date;
					} else {
						$filter .= " and ESEC.EvnSection_disDate >= :EvnPS_disDate_Range_0";
						$queryParams["EvnPS_disDate_Range_0"] = $data["EvnPS_disDate_Range"][0];
					}
					break;
			}
		}
		if (isset($data["EvnPS_disDate_Range"][1])) {
			$stats_hour = "+9 hours";
			if ($data['session']['region']['nick'] == 'msk') $stats_hour = "+8 hours";
			$new_date = date('Y-m-d H:i:s', strtotime($stats_hour, strtotime($data['EvnPS_disDate_Range'][1])));
			if ($data['session']['region']['nick'] == 'ufa' || $data['session']['region']['nick'] == 'msk')
				$temp_date = $new_date;
			else
				$temp_date = date('Y-m-d H:i:s', strtotime("+1 days", strtotime($new_date)));
						
			switch ($data["SearchFormType"]) {
				case "EvnPS":
				case "KvsEvnPS":
					if (isset($data["Date_Type"]) && $data["Date_Type"] == "2") {
						$filter .= " and EPSLastES.EvnSection_disDT < :EvnPS_disDate_Range_1";
						$queryParams["EvnPS_disDate_Range_1"] = $temp_date;
					} else {
						$filter .= " and EPSLastES.EvnSection_disDate <= :EvnPS_disDate_Range_1";
						$queryParams["EvnPS_disDate_Range_1"] = $data["EvnPS_disDate_Range"][1];
					}
					break;
				case "KvsPerson":
					if (isset($data["Date_Type"]) && $data["Date_Type"] == "2") {
						$filter .= " and EPSLastES2.EvnSection_disDT <= :EvnPS_disDate_Range_1";
						$queryParams["EvnPS_disDate_Range_1"] = $temp_date;
					} else {
						$filter .= " and EPSLastES2.EvnSection_disDate <= :EvnPS_disDate_Range_1";
						$queryParams["EvnPS_disDate_Range_1"] = $data["EvnPS_disDate_Range"][1];
					}
					break;
				case "EvnSection":
				case "KvsEvnSection":
					if (isset($data["Date_Type"]) && $data["Date_Type"] == "2") {
						$filter .= " and ESEC.EvnSection_disDT <= :EvnPS_disDate_Range_1";
						$queryParams["EvnPS_disDate_Range_1"] = $temp_date;
					} else {
						$filter .= " and ESEC.EvnSection_disDate <= :EvnPS_disDate_Range_1";
						$queryParams["EvnPS_disDate_Range_1"] = $data["EvnPS_disDate_Range"][1];
					}
					break;
			}
		}
		if (isset($data["EvnPS_HospCount_Max"])) {
			$filter .= " and EPS.EvnPS_HospCount <= :EvnPS_HospCount_Max";
			$queryParams["EvnPS_HospCount_Max"] = $data["EvnPS_HospCount_Max"];
		}
		if (isset($data["EvnPS_HospCount_Min"])) {
			$filter .= " and EPS.EvnPS_HospCount >= :EvnPS_HospCount_Min";
			$queryParams["EvnPS_HospCount_Min"] = $data["EvnPS_HospCount_Min"];
		}
		if (isset($data["EvnPS_IsUnlaw"])) {
			$filter .= " and EPS.EvnPS_IsUnlaw = :EvnPS_IsUnlaw";
			$queryParams["EvnPS_IsUnlaw"] = $data["EvnPS_IsUnlaw"];
		}
		if (isset($data["EvnPS_IsUnport"])) {
			$filter .= " and EPS.EvnPS_IsUnport = :EvnPS_IsUnport";
			$queryParams["EvnPS_IsUnport"] = $data["EvnPS_IsUnport"];
		}
		if (isset($data["MedicalCareFormType_id"])) {
			$filter .= " and EPS.MedicalCareFormType_id = :MedicalCareFormType_id";
			$queryParams["MedicalCareFormType_id"] = $data["MedicalCareFormType_id"];
		}
		if (!empty($data["EvnPS_IsWithoutDirection"])) {
			$filter .= " and coalesce(EvnDirection_id, 0) = 0";
		}
		if (isset($data["EvnPS_NumCard"])) {
			$filter .= " and EPS.EvnPS_NumCard = :EvnPS_NumCard";
			$queryParams["EvnPS_NumCard"] = $data["EvnPS_NumCard"];
		}
		if (isset($data["EvnSection_insideNumCard"])) {
			switch ($data["SearchFormType"]) {
				case "EvnPS":
					$filter .= " and EPSLastES.EvnSection_insideNumCard = :EvnSection_insideNumCard";
					$queryParams["EvnSection_insideNumCard"] = $data["EvnSection_insideNumCard"];
					break;
				case "EvnSection":
					$filter .= " and ESEC.EvnSection_insideNumCard = :EvnSection_insideNumCard";
					$queryParams["EvnSection_insideNumCard"] = $data["EvnSection_insideNumCard"];
					break;
			}
		}
		if (isset($data["EvnPS_setDate_Range"][0])) {
			$stats_hour = "+9 hours";
			if ($data['session']['region']['nick'] == 'msk') $stats_hour = "+8 hours";
			$new_date = date('Y-m-d H:i:s', strtotime($stats_hour, strtotime($data['EvnPS_setDate_Range'][0])));
			if ($data['session']['region']['nick'] == 'ufa' || $data['session']['region']['nick'] == 'msk')
				$temp_date = date('Y-m-d H:i:s', strtotime("-1 days", strtotime($new_date)));
			else
				$temp_date = $new_date;
				
			if (isset($data["Date_Type"]) && $data["Date_Type"] == "2") {
				$filter .= " and EPS.EvnPS_setDT >= :EvnPS_setDate_Range_0";
				$queryParams["EvnPS_setDate_Range_0"] = $temp_date;
			} else {
				$filter .= " and EPS.EvnPS_setDate >= :EvnPS_setDate_Range_0";
				$queryParams["EvnPS_setDate_Range_0"] = $data["EvnPS_setDate_Range"][0];
			}
		}
		if (isset($data["EvnPS_setDate_Range"][1])) {
			$stats_hour = "+9 hours";
			if ($data['session']['region']['nick'] == 'msk') $stats_hour = "+8 hours";
			$temp_date = date('Y-m-d H:i:s', strtotime($stats_hour, strtotime($data['EvnPS_setDate_Range'][1])));
			if ($data['session']['region']['nick'] == 'ufa' || $data['session']['region']['nick'] == 'msk')
				$new_date = $temp_date;
			else
				$new_date = date('Y-m-d H:i:s', strtotime("+1 days", strtotime($temp_date)));
						
			if (isset($data["Date_Type"]) && $data["Date_Type"] == "2") {
				$filter .= " and EPS.EvnPS_setDT < :EvnPS_setDate_Range_1";
				$queryParams["EvnPS_setDate_Range_1"] = $new_date;
			} else {
				$filter .= " and EPS.EvnPS_setDate <= :EvnPS_setDate_Range_1";
				$queryParams["EvnPS_setDate_Range_1"] = $data["EvnPS_setDate_Range"][1];
			}
		}
		if (!empty($data['EvnReanimatPeriod_setDate'][0])) {
			$filter .= " and ERP.EvnReanimatPeriod_setDate >= :EvnReanimatPeriod_setDate_Start";
			$queryParams['EvnReanimatPeriod_setDate_Start'] = $data['EvnReanimatPeriod_setDate'][0];
		}
		if (!empty($data['EvnReanimatPeriod_setDate'][1])) {
			$filter .= " and ERP.EvnReanimatPeriod_setDate <= :EvnReanimatPeriod_setDate_End";
			$queryParams['EvnReanimatPeriod_setDate_End'] = $data['EvnReanimatPeriod_setDate'][1];
		}
		if (!empty($data['EvnReanimatPeriod_disDate'][0])) {
			$filter .= " and ERP.EvnReanimatPeriod_disDate >= :EvnReanimatPeriod_disDate_Start";
			$queryParams['EvnReanimatPeriod_disDate_Start'] = $data['EvnReanimatPeriod_disDate'][0];
		}
		if (!empty($data['EvnReanimatPeriod_disDate'][1])) {
			$filter .= " and ERP.EvnReanimatPeriod_disDate <= :EvnReanimatPeriod_disDate_End";
			$queryParams['EvnReanimatPeriod_disDate_End'] = $data['EvnReanimatPeriod_disDate'][1];
		}
		if (isset($data["Lpu_IsFondHolder"])) {
			$filter .= " and exists(select 1 from LpuFondHolder where Lpu_id = EPS.Lpu_did and coalesce(LpuFondHolder_IsEnabled, 2) = :Lpu_IsFondHolder limit 1)";
			$queryParams["Lpu_IsFondHolder"] = $data["Lpu_IsFondHolder"];
		}
		if (isset($data["LpuSection_did"])) {
			$filter .= " and EPS.LpuSection_did = :LpuSection_did";
			$queryParams["LpuSection_did"] = $data["LpuSection_did"];
		}
		if (isset($data["Lpu_did"])) {
			$filter .= " and EPS.Lpu_did = :Lpu_did";
			$queryParams["Lpu_did"] = $data["Lpu_did"];
		} else if (isset($data["OrgMilitary_did"])) {
			$filter .= " and EPS.OrgMilitary_did = :OrgMilitary_did";
			$queryParams["OrgMilitary_did"] = $data["OrgMilitary_did"];
		} else if (isset($data["Org_did"])) {
			$filter .= " and EPS.Org_did = :Org_did";
			$queryParams["Org_did"] = $data["Org_did"];
		}
		if (isset($data["PayType_id"])) {
			switch ($data["SearchFormType"]) {
				case "EvnPS":
					$filter .= " and EPS.PayType_id = :PayType_id";
					break;
				case "EvnSection":
					$filter .= " and ESEC.PayType_id = :PayType_id";
					break;
			}
			$queryParams["PayType_id"] = $data["PayType_id"];
		}
		if (isset($data["PrehospArrive_id"])) {
			$filter .= " and EPS.PrehospArrive_id = :PrehospArrive_id";
			$queryParams["PrehospArrive_id"] = $data["PrehospArrive_id"];
		}
		if (isset($data["PrehospDirect_id"])) {
			if ($data["PrehospDirect_id"] > 0) {
				$filter .= " and EPS.PrehospDirect_id = :PrehospDirect_id";
				$queryParams["PrehospDirect_id"] = $data["PrehospDirect_id"];
			} else if ($data["PrehospDirect_id"] == -1) {
				$filter .= " and EPS.PrehospDirect_id is null";
			}
		}
		if (isset($data["PrehospToxic_id"])) {
			$filter .= " and EPS.PrehospToxic_id = :PrehospToxic_id";
			$queryParams["PrehospToxic_id"] = $data["PrehospToxic_id"];
		}
		if (isset($data["PrehospTrauma_id"])) {
			$filter .= " and EPS.PrehospTrauma_id = :PrehospTrauma_id";
			$queryParams["PrehospTrauma_id"] = $data["PrehospTrauma_id"];
		}
		if (isset($data["PrehospType_id"])) {
			$filter .= " and EPS.PrehospType_id = :PrehospType_id";
			$queryParams["PrehospType_id"] = $data["PrehospType_id"];
		}
		if (isset($data["EvnPS_TimeDesease_Max"])) {
			$filter .= " and EPS.EvnPS_TimeDesease <= :EvnPS_TimeDesease_Max";
			$queryParams["EvnPS_TimeDesease_Max"] = $data["EvnPS_TimeDesease_Max"];
		}
		if (isset($data["EvnPS_TimeDesease_Min"])) {
			$filter .= " and EPS.EvnPS_TimeDesease >= :EvnPS_TimeDesease_Min";
			$queryParams["EvnPS_TimeDesease_Min"] = $data["EvnPS_TimeDesease_Min"];
		}
		if (isset($data["LpuSection_hid"])) {
			$filter .= " and EPS.LpuSection_id = :LpuSection_hid";
			$queryParams["LpuSection_hid"] = $data["LpuSection_hid"];
		}
		if (isset($data["PrehospWaifRefuseCause_id"])) {
			$filter .= " and EPS.PrehospWaifRefuseCause_id = :PrehospWaifRefuseCause_id";
			$queryParams["PrehospWaifRefuseCause_id"] = $data["PrehospWaifRefuseCause_id"];
		}
		if (isset($data["Ksg_id"])) {
			if ($callObject->getRegionNick() == "ekb") {
				$filter .= " and sksg.Mes_id = :Ksg_id";
				$queryParams["Ksg_id"] = $data["Ksg_id"];
			} else {
				$filter .= " and (ksgkpg.Mes_id = :Ksg_id)";
				$queryParams["Ksg_id"] = $data["Ksg_id"];
			}
		}
		if (isset($data["Kpg_id"])) {
			$filter .= " and (kpg.Mes_id = :Kpg_id)";
			$queryParams["Kpg_id"] = $data["Kpg_id"];
		}
		if (isset($data["LpuUnitType_did"])) {
			$filter .= " and ESHosp.LpuUnitType_id = :LpuUnitType_did";
			$queryParams["LpuUnitType_did"] = $data["LpuUnitType_did"];
		}
		if (isset($data["EvnPS_IsWaif"])) {
			$filter .= " and EPS.EvnPS_IsWaif = :EvnPS_IsWaif";
			$queryParams["EvnPS_IsWaif"] = $data["EvnPS_IsWaif"];
		}
		if ($data["session"]["region"]["nick"] == "ufa") {
			if (!empty($data["HTMedicalCareClass_id"])) {
				switch ($data["SearchFormType"]) {
					case "EvnPS":
						$filter .= " and EPSLastES.HTMedicalCareClass_id = :HTMedicalCareClass_id";
						$queryParams["HTMedicalCareClass_id"] = $data["HTMedicalCareClass_id"];
						break;

					case "EvnSection":
						$filter .= " and ESEC.HTMedicalCareClass_id = :HTMedicalCareClass_id";
						$queryParams["HTMedicalCareClass_id"] = $data["HTMedicalCareClass_id"];
						break;
				}
			} else if (!empty($data["HTMedicalCareType_id"])) {
				switch ($data["SearchFormType"]) {
					case "EvnPS":
						$filter .= "
							and EPSLastES.HTMedicalCareClass_id in (
								select HTMedicalCareClass_id
								from nsi.HTMedicalCareClass
								where HTMedicalCareType_id = :HTMedicalCareType_id
							)
						";
						$queryParams["HTMedicalCareType_id"] = $data["HTMedicalCareType_id"];
						break;

					case "EvnSection":
						$filter .= "
							and ESEC.HTMedicalCareClass_id in (
								select HTMedicalCareClass_id
								from nsi.HTMedicalCareClass
								where HTMedicalCareType_id = :HTMedicalCareType_id
							)
						";
						$queryParams["HTMedicalCareType_id"] = $data["HTMedicalCareType_id"];
						break;
				}
			}
		}
		if (!empty($data["EvnSection_isPaid"])) {
			switch ($data["SearchFormType"]) {
				case "EvnPS":
					switch ($callObject->getRegionNick()) {
						case "ekb":
							$filter .= " and coalesce(EPS.EvnPS_isPaid,1) = :EvnSection_isPaid";
							break;
						default:
							$filter .= " and coalesce(ESpaid.EvnSection_isPaid, 2) = :EvnSection_isPaid";
							break;
					}
					break;
				case "EvnSection":
					$filter .= " and coalesce(ESEC.EvnSection_isPaid,1) = :EvnSection_isPaid";
					break;
			}
			$queryParams["EvnSection_isPaid"] = $data["EvnSection_isPaid"];
		}
		$getLpuIdFilterString = $callObject->getLpuIdFilter($data);
		if (isset($data["LpuSection_cid"]) ||
			isset($data["LpuBuilding_cid"]) ||
			isset($data["MedPersonal_cid"]) ||
			isset($data["MedStaffFact_cid"]) ||
			isset($data["EvnSection_disDate_Range"][0]) ||
			isset($data["EvnSection_disDate_Range"][1]) ||
			isset($data["EvnSection_setDate_Range"][0]) ||
			isset($data["EvnSection_setDate_Range"][1]) ||
			isset($data["DiagSetClass_id"]) ||
			isset($data["DiagSetType_id"]) ||
			isset($data["Diag_Code_From"]) ||
			isset($data["Diag_Code_To"])
		) {
			switch ($data["SearchFormType"]) {
				case "KvsPerson":
					$filter .= "
						and exists (
							select 1
							from v_EvnSection ES
							where ES.EvnSection_rid = EPS.EvnPS_id
							  and ES.Lpu_id {$getLpuIdFilterString}
					";
					if (isset($data["EvnSection_disDate_Range"][0])) {
						$filter .= " and ES.EvnSection_disDate >= :EvnSection_disDate_Range_0";
						$queryParams["EvnSection_disDate_Range_0"] = $data["EvnSection_disDate_Range"][0];
					}
					if (isset($data["EvnSection_disDate_Range"][1])) {
						$filter .= " and ES.EvnSection_disDate <= :EvnSection_disDate_Range_1";
						$queryParams["EvnSection_disDate_Range_1"] = $data["EvnSection_disDate_Range"][1];
					}
					if (isset($data["EvnSection_setDate_Range"][0])) {
						$filter .= " and ES.EvnSection_setDate >= :EvnSection_setDate_Range_0";
						$queryParams["EvnSection_setDate_Range_0"] = $data["EvnSection_setDate_Range"][0];
					}
					if (isset($data["EvnSection_setDate_Range"][1])) {
						$filter .= " and ES.EvnSection_setDate <= :EvnSection_setDate_Range_1";
						$queryParams["EvnSection_setDate_Range_1"] = $data["EvnSection_setDate_Range"][1];
					}
					$filter .= " limit 1 )";
					break;
				case "EvnPS":
					$filter .= "
						and exists (
							select 1
							from v_EvnSection ES
							where ES.EvnSection_rid = EPS.EvnPS_id
							  and ES.Lpu_id {$getLpuIdFilterString}

					";
					if (isset($data["EvnSection_disDate_Range"][0])) {
						$filter .= " and ES.EvnSection_disDate >= :EvnSection_disDate_Range_0";
						$queryParams["EvnSection_disDate_Range_0"] = $data["EvnSection_disDate_Range"][0];
					}
					if (isset($data["EvnSection_disDate_Range"][1])) {
						$filter .= " and ES.EvnSection_disDate <= :EvnSection_disDate_Range_1";
						$queryParams["EvnSection_disDate_Range_1"] = $data["EvnSection_disDate_Range"][1];
					}
					if (isset($data["EvnSection_setDate_Range"][0])) {
						$filter .= " and ES.EvnSection_setDate >= :EvnSection_setDate_Range_0";
						$queryParams["EvnSection_setDate_Range_0"] = $data["EvnSection_setDate_Range"][0];
					}
					if (isset($data["EvnSection_setDate_Range"][1])) {
						$filter .= " and ES.EvnSection_setDate <= :EvnSection_setDate_Range_1";
						$queryParams["EvnSection_setDate_Range_1"] = $data["EvnSection_setDate_Range"][1];
					}
					if (isset($data["LpuSection_cid"])) {
						$filter .= " and ES.LpuSection_id = :LpuSection_id";
						$queryParams["LpuSection_id"] = $data["LpuSection_cid"];
					} elseif (isset($data["LpuBuilding_cid"])) {
						$filter .= "
							and exists (
								select 1
								from
									LpuSection LS
									left join LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
								where LU.LpuBuilding_id = :LpuBuilding_cid and LS.LpuSection_id = ES.LpuSection_id
							)
						";
						$queryParams["LpuBuilding_cid"] = $data["LpuBuilding_cid"];
					}
					if (isset($data["MedPersonal_cid"])) {
						$filter .= " and ES.MedPersonal_id = :MedPersonal_id";
						$queryParams["MedPersonal_id"] = $data["MedPersonal_cid"];
					}
					$filter .= " limit 1 )";


					if (isset($data["DiagSetClass_id"]) || isset($data["DiagSetType_id"]) || isset($data["Diag_Code_From"]) || isset($data["Diag_Code_To"])) {
						$filter .= "
							and exists (
								select 1
								from
									v_EvnSection ES2
									left join v_EvnDiagPS EDPS on EDPS.EvnDiagPS_pid = ES2.EvnSection_id
									left join Diag DiagES on DiagES.Diag_id = ES2.Diag_id
									left join Diag DiagEDPS on DiagEDPS.Diag_id = EDPS.Diag_id
									left join Diag DiagD on DiagD.Diag_id = EPS.Diag_did
									left join Diag DiagP on DiagP.Diag_id = EPS.Diag_pid
									left join v_LeaveType L on L.LeaveType_id = ES2.LeaveType_id
									left join v_EvnDie ED on ED.EvnDie_pid = ES2.EvnSection_id
									left join Diag DiagA on DiagA.Diag_id = ED.Diag_aid
								where ES2.EvnSection_rid = EPS.EvnPS_id and ES2.Lpu_id {$getLpuIdFilterString}
						";
						if (isset($data["DiagSetType_id"])) {
							$queryParams["DiagSetType_id"] = $data["DiagSetType_id"];
							switch ($data["DiagSetType_id"]) {
								case 1:
									$filter .= " and ((EDPS.DiagSetType_id = :DiagSetType_id ";
									$filter .= (isset($data["DiagSetClass_id"]))
										? " and EDPS.DiagSetClass_id = :DiagSetClass_id)"
										: ")";
									if (isset($data["DiagSetClass_id"])) {
										$queryParams["DiagSetClass_id"] = $data["DiagSetClass_id"];
									}
									$filter .= " or EPS.Diag_did is not null) and ((1 = 1)";
									if (isset($data["Diag_Code_From"])) {
										$filter .= " and (DiagD.Diag_Code >= :Diag_Code_From or DiagEDPS.Diag_Code >= :Diag_Code_From)";
										$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
									}
									if (isset($data["Diag_Code_To"])) {
										$filter .= " and (DiagD.Diag_Code <= :Diag_Code_To or DiagEDPS.Diag_Code <= :Diag_Code_To)";
										$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
									}
									$filter .= ")";
									break;
								case 2:
									$filter .= " and ((EDPS.DiagSetType_id = :DiagSetType_id ";
									$filter .= (isset($data["DiagSetClass_id"]))
										? " and EDPS.DiagSetClass_id = :DiagSetClass_id)"
										: ")";
									if (isset($data["DiagSetClass_id"])) {
										$queryParams["DiagSetClass_id"] = $data["DiagSetClass_id"];
									}
									$filter .= " or EPS.Diag_pid is not null) and ((1 = 1)";
									if (isset($data["Diag_Code_From"])) {
										$filter .= " and (DiagP.Diag_Code >= :Diag_Code_From or DiagEDPS.Diag_Code >= :Diag_Code_From)";
										$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
									}
									if (isset($data["Diag_Code_To"])) {
										$filter .= " and (DiagP.Diag_Code <= :Diag_Code_To or DiagEDPS.Diag_Code <= :Diag_Code_To)";
										$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
									}
									$filter .= ")";
									break;
								case 5:
									$filter .= " and (((EDPS.DiagSetType_id = :DiagSetType_id ";
									$filter .= (isset($data["DiagSetClass_id"]))
										? " and EDPS.DiagSetClass_id = :DiagSetClass_id)"
										: ")";
									if (isset($data["DiagSetClass_id"])) {
										$queryParams["DiagSetClass_id"] = $data["DiagSetClass_id"];
									}
									if (isset($data["Diag_Code_From"]) || isset($data["Diag_Code_To"])) {
										$filter .= " and ((1 = 1)";
										if (isset($data["Diag_Code_From"])) {
											$filter .= " and (DiagEDPS.Diag_Code >= :Diag_Code_From)";
											$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
										}
										if (isset($data["Diag_Code_To"])) {
											$filter .= " and (DiagEDPS.Diag_Code <= :Diag_Code_To)";
											$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
										}
										$filter .= "
											))
										    or (L.LeaveType_SysNick ilike '%die%'
										    and (((1 = 1)
										";
										if (isset($data["Diag_Code_From"])) {
											$filter .= " and (DiagA.Diag_Code >= :Diag_Code_From)";
											$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
										}
										if (isset($data["Diag_Code_To"])) {
											$filter .= " and (DiagA.Diag_Code <= :Diag_Code_To)";
											$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
										}
										$filter .= ") or ((1 = 1)";
										if (isset($data["Diag_Code_From"])) {
											$filter .= " and (DiagES.Diag_Code >= :Diag_Code_From)";
											$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
										}
										if (isset($data["Diag_Code_To"])) {
											$filter .= " and (DiagES.Diag_Code <= :Diag_Code_To)";
											$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
										}
										$filter .= ")) ))";
									} else {
										$filter .= ") or L.LeaveType_SysNick ilike " % die % ")";
									}
									break;
								default:
									$filter .= " and coalesce(ES2.EvnSection_IsPriem, 1) = 1 ";
									$filter .= " and (((EDPS.DiagSetType_id in (3,4) or EDPS.DiagSetType_id is null) ";
									if (isset($data["DiagSetClass_id"]) && $data["DiagSetClass_id"] != 1) {
										$filter .= " and EDPS.DiagSetClass_id = :DiagSetClass_id)";
										$queryParams["DiagSetClass_id"] = $data["DiagSetClass_id"];
									} else {
										$filter .= ")";
									}
									$filter .= ") and ((1 = 1)";
									if (!empty($data["DiagSetClass_id"])) {
										if ($data["DiagSetClass_id"] == 1) {
											if (isset($data["Diag_Code_From"])) {
												$filter .= " and ((DiagES.Diag_Code >= :Diag_Code_From))";
												$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
											}
											if (isset($data["Diag_Code_To"])) {
												$filter .= " and ((DiagES.Diag_Code <= :Diag_Code_To))";
												$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
											}
										} else {
											if (isset($data["Diag_Code_From"])) {
												$filter .= " and ((DiagEDPS.Diag_Code >= :Diag_Code_From))";
												$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
											}
											if (isset($data["Diag_Code_To"])) {
												$filter .= " and ((DiagEDPS.Diag_Code <= :Diag_Code_To))";
												$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
											}
										}
									} else {
										if (isset($data["Diag_Code_From"])) {
											$filter .= " and ((DiagEDPS.Diag_Code >= :Diag_Code_From) or (DiagES.Diag_Code >= :Diag_Code_From))";
											$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
										}
										if (isset($data["Diag_Code_To"])) {
											$filter .= " and ((DiagEDPS.Diag_Code <= :Diag_Code_To) or (DiagES.Diag_Code <= :Diag_Code_To))";
											$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
										}
									}
									$filter .= ")";
									break;
							}
						} else {
							if (isset($data["DiagSetClass_id"])) {
								if ($data["DiagSetClass_id"] == 1) {
									if (isset($data["Diag_Code_From"])) {
										$filter .= " and DiagES.Diag_Code >= :Diag_Code_From";
										$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
									}
									if (isset($data["Diag_Code_To"])) {
										$filter .= " and DiagES.Diag_Code <= :Diag_Code_To";
										$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
									}
								} else {
									$filter .= " and EDPS.DiagSetClass_id = :DiagSetClass_id";
									$queryParams["DiagSetClass_id"] = $data["DiagSetClass_id"];
									if (isset($data["Diag_Code_From"])) {
										$filter .= " and DiagEDPS.Diag_Code >= :Diag_Code_From";
										$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
									}
									if (isset($data["Diag_Code_To"])) {
										$filter .= " and DiagEDPS.Diag_Code <= :Diag_Code_To";
										$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
									}
								}
							} else {
								$filter .= " and (((1 = 1)";
								if (isset($data["Diag_Code_From"])) {
									$filter .= " and DiagES.Diag_Code >= :Diag_Code_From";
									$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
								}
								if (isset($data["Diag_Code_To"])) {
									$filter .= " and DiagES.Diag_Code <= :Diag_Code_To";
									$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
								}
								$filter .= ") or ((1 = 1)";
								if (isset($data["Diag_Code_From"])) {
									$filter .= " and DiagEDPS.Diag_Code >= :Diag_Code_From";
									$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
								}
								if (isset($data["Diag_Code_To"])) {
									$filter .= " and DiagEDPS.Diag_Code <= :Diag_Code_To";
									$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
								}
								$filter .= "))";
							}
						}
						$filter .= ")";
					}
					break;
				case "EvnSection":
					if (!empty($data["Person_Surname"])) {
						$filter .= " and PS.Person_SurName ilike :Person_Surname ||'%'";
						$queryParams["Person_Surname"] = $data["Person_Surname"];
					}
					if (!empty($data["Person_Firname"])) {
						$filter .= " and PS.Person_FirName ilike :Person_Firname ||'%'";
						$queryParams["Person_Firname"] = $data["Person_Firname"];
					}
					if (!empty($data["Person_Secname"])) {
						$filter .= " and PS.Person_SecName ilike :Person_Secname ||'%'";
						$queryParams["Person_Secname"] = $data["Person_Secname"];
					}
					if (!empty($data["Person_Birthday_Range"][0])) {
						$filter .= " and PS.Person_BirthDay >= :Person_Birthday_Range_0";
						$queryParams["Person_Birthday_Range_0"] = $data["Person_Birthday_Range"][0];
					}
					if (!empty($data["Person_Birthday_Range"][1])) {
						$filter .= " and PS.Person_BirthDay <= :Person_Birthday_Range_1";
						$queryParams["Person_Birthday_Range_1"] = $data["Person_Birthday_Range"][1];
					}
					if (!empty($data["EvnSection_disDate_Range"][0])) {
						$filter .= " and ESEC.EvnSection_disDate >= :EvnSection_disDate_Range_0";
						$queryParams["EvnSection_disDate_Range_0"] = $data["EvnSection_disDate_Range"][0];
					}
					if (!empty($data["EvnSection_disDate_Range"][1])) {
						$filter .= " and ESEC.EvnSection_disDate <= :EvnSection_disDate_Range_1";
						$queryParams["EvnSection_disDate_Range_1"] = $data["EvnSection_disDate_Range"][1];
					}
					if (!empty($data["EvnSection_isPaid"])) {
						$filter .= " and coalesce(ESEC.EvnSection_isPaid,1) = :EvnSection_isPaid";
						$queryParams["EvnSection_isPaid"] = $data["EvnSection_isPaid"];
					}
					if (!empty($data["EvnSection_setDate_Range"][0])) {
						$filter .= " and ESEC.EvnSection_setDate >= :EvnSection_setDate_Range_0";
						$queryParams["EvnSection_setDate_Range_0"] = $data["EvnSection_setDate_Range"][0];
					}
					if (!empty($data["EvnSection_setDate_Range"][1])) {
						$filter .= " and ESEC.EvnSection_setDate <= :EvnSection_setDate_Range_1";
						$queryParams["EvnSection_setDate_Range_1"] = $data["EvnSection_setDate_Range"][1];
					}
					if (!empty($data["LpuSection_cid"])) {
						$filter .= " and ESEC.LpuSection_id = :LpuSection_id";
						$queryParams["LpuSection_id"] = $data["LpuSection_cid"];
					} else if (!empty($data["LpuBuilding_cid"])) {
						$filter .= "
							and exists (
								select 1
								from
									LpuSection LStmp
									left join LpuUnit LUtmp on LUtmp.LpuUnit_id = LStmp.LpuUnit_id
								where LUtmp.LpuBuilding_id = :LpuBuilding_cid and LStmp.LpuSection_id = ESEC.LpuSection_id
							)
						";
						$queryParams["LpuBuilding_cid"] = $data["LpuBuilding_cid"];
					}
					if (!empty($data["MedPersonal_cid"])) {
						$filter .= " and ESEC.MedPersonal_id = :MedPersonal_id";
						$queryParams["MedPersonal_id"] = $data["MedPersonal_cid"];
					}
					if (!empty($data["DiagSetClass_id"]) || !empty($data["DiagSetType_id"]) || !empty($data["Diag_Code_From"]) || !empty($data["Diag_Code_To"])) {
						if (!empty($data["DiagSetClass_id"])) {
							if ($data["DiagSetClass_id"] == 1) {
								if (!empty($data["Diag_Code_From"])) {
									$filter .= " and Dtmp.Diag_Code >= :Diag_Code_From";
									$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
								}
								if (!empty($data["Diag_Code_To"])) {
									$filter .= " and Dtmp.Diag_Code <= :Diag_Code_To";
									$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
								}
							} else {
								$filter .= "
									and exists (
										select EvnDiagPS_id
										from
											v_EvnDiagPS EDPS
											left join v_Diag DiagEDPS on DiagEDPS.Diag_id = EDPS.Diag_id
										where EDPS.EvnDiagPS_pid = ESEC.EvnSection_id and EDPS.DiagSetClass_id = :DiagSetClass_id
								";
								$queryParams["DiagSetClass_id"] = $data["DiagSetClass_id"];
								if (!empty($data["DiagSetType_id"])) {
									$filter .= ($data["DiagSetType_id"] == 2)
										? " and (EDPS.DiagSetType_id = :DiagSetType_id or ESEC.EvnSection_IsPriem = 2)"
										: " and EDPS.DiagSetType_id = :DiagSetType_id";
									$queryParams["DiagSetType_id"] = $data["DiagSetType_id"];
								}
								if (!empty($data["Diag_Code_From"])) {
									$filter .= " and DiagEDPS.Diag_Code >= :Diag_Code_From";
									$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
								}
								if (!empty($data["Diag_Code_To"])) {
									$filter .= " and DiagEDPS.Diag_Code <= :Diag_Code_To";
									$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
								}
								$filter .= ")";
							}
						} else {
							$filter .= "
								and exists (
									select EvnDiagPS_id
									from
										v_EvnDiagPS EDPS
										left join v_Diag DiagEDPS on DiagEDPS.Diag_id = EDPS.Diag_id
										left join v_EvnDie ED on ED.EvnDie_pid = ESEC.EvnSection_id
										left join Diag DiagA on DiagA.Diag_id = ED.Diag_aid
									where EDPS.EvnDiagPS_pid = ESEC.EvnSection_id
							";
							if (!empty($data["DiagSetType_id"]) && $data["DiagSetType_id"] == 5) {
								$filter .= " and ((1 = 1) and ED.EvnDie_id is not null and (((1 = 1)";
								$queryParams["DiagSetType_id"] = $data["DiagSetType_id"];
								if (!empty($data["Diag_Code_From"])) {
									$filter .= " and DiagA.Diag_Code >= :Diag_Code_From";
									$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
								}
								if (!empty($data["Diag_Code_To"])) {
									$filter .= " and DiagA.Diag_Code <= :Diag_Code_To";
									$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
								}
								$filter .= ") or ((1 = 1)";
								if (!empty($data["Diag_Code_From"])) {
									$filter .= " and DiagEDPS.Diag_Code >= :Diag_Code_From";
									$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
								}
								if (!empty($data["Diag_Code_To"])) {
									$filter .= " and DiagEDPS.Diag_Code <= :Diag_Code_To";
									$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
								}
								$filter .= ")) ))";
							} else {
								$filter .= " and (((1 = 1)";
								if (!empty($data["DiagSetType_id"])) {
									$filter .= ($data["DiagSetType_id"] == 2)
										? " and (EDPS.DiagSetType_id = :DiagSetType_id or ESEC.EvnSection_IsPriem = 2)"
										: " and EDPS.DiagSetType_id = :DiagSetType_id";
									$queryParams["DiagSetType_id"] = $data["DiagSetType_id"];
								}
								if (!empty($data["Diag_Code_From"])) {
									$filter .= " and DiagEDPS.Diag_Code >= :Diag_Code_From";
									$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
								}
								if (!empty($data["Diag_Code_To"])) {
									$filter .= " and DiagEDPS.Diag_Code <= :Diag_Code_To";
									$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
								}
								$filter .= ") or ((1 = 1)";
								if (!empty($data["Diag_Code_From"])) {
									$filter .= " and Dtmp.Diag_Code >= :Diag_Code_From";
									$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
								}
								if (!empty($data["Diag_Code_To"])) {
									$filter .= " and Dtmp.Diag_Code <= :Diag_Code_To";
									$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
								}
								$filter .= ")))";
							}
						}
					}
					break;
			}
		}
		if (
			!empty($data["UslugaCategory_id"]) ||
			!empty($data["UslugaComplex_Code_From"]) ||
			!empty($data["UslugaComplex_Code_To"]) ||
			(
				isset($data["EvnUsluga_setDate_Range"]) &&
				is_array($data["EvnUsluga_setDate_Range"]) &&
				(!empty($data["EvnUsluga_setDate_Range"][0]) || !empty($data["EvnUsluga_setDate_Range"][1]))
			)
		) {
			$filter .= "
				and exists (
					select t2.UslugaComplex_id
					from
						v_EvnUsluga t1
						inner join UslugaComplex t2 on t2.UslugaComplex_id = t1.UslugaComplex_id
					where t1.EvnUsluga_rid = EPS.EvnPS_id
					  and t1.Lpu_id {$getLpuIdFilterString}
					  and t1.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaPar')
			";
			if (!empty($data["EvnUsluga_setDate_Range"][0])) {
				$filter .= " and t1.EvnUsluga_setDT::date >= :EvnUsluga_setDate_Range_0::date";
				$queryParams["EvnUsluga_setDate_Range_0"] = $data["EvnUsluga_setDate_Range"][0];
			}
			if (!empty($data["EvnUsluga_setDate_Range"][1])) {
				$filter .= " and t1.EvnUsluga_setDT::date <= :EvnUsluga_setDate_Range_1::date";
				$queryParams["EvnUsluga_setDate_Range_1"] = $data["EvnUsluga_setDate_Range"][1];
			}
			if (!empty($data["UslugaCategory_id"])) {
				$filter .= " and t2.UslugaCategory_id = :UslugaCategory_id ";
				$queryParams["UslugaCategory_id"] = $data["UslugaCategory_id"];
			}
			if (!empty($data["UslugaComplex_Code_From"])) {
				$filter .= " and t2.UslugaComplex_Code >= :UslugaComplex_Code_From ";
				$queryParams["UslugaComplex_Code_From"] = $data["UslugaComplex_Code_From"];
			}
			if (!empty($data["UslugaComplex_Code_To"])) {
				$filter .= " and t2.UslugaComplex_Code <= :UslugaComplex_Code_To ";
				$queryParams["UslugaComplex_Code_To"] = $data["UslugaComplex_Code_To"];
			}
			$filter .= ")";
		}
		if (isset($data["CureResult_id"]) && !empty($data["CureResult_id"])) {
			switch ($data["SearchFormType"]) {
				case "EvnPS":
					$filter .= " and EPSLastES.CureResult_id = :CureResult_id";
					$queryParams["CureResult_id"] = $data["CureResult_id"];
					break;
				case "EvnSection":
					$filter .= " and ESEC.CureResult_id = :CureResult_id";
					$queryParams["CureResult_id"] = $data["CureResult_id"];
					break;
			}
		}
		if (!empty($data["EvnLeave_IsNotSet"])) {
			switch ($data["SearchFormType"]) {
				case "EvnPS":
					$filter .= " and EPS.LeaveType_id is null and EPS.PrehospWaifRefuseCause_id is null";
					break;
				case "EvnSection":
					$filter .= " and ESEC.LeaveType_id is null and coalesce(ESEC.EvnSection_IsPriem, 1) = 1";
					break;
			}
		} else {
			if (!empty($data["LeaveType_id"])) {
				switch ($data["SearchFormType"]) {
					case "EvnPS":
						$filter .= " and EPS.LeaveType_id = :LeaveType_id";
						if ($data["session"]["region"]["nick"] == "perm") {
							if (isset($data["EvnSection_disDate_Range"][0])) {
								$filter .= " and EPSLastES.EvnSection_disDate >= :EvnSectionLast_disDate_Range_0";
								$queryParams["EvnSectionLast_disDate_Range_0"] = $data["EvnSection_disDate_Range"][0];
							}
							if (isset($data["EvnSection_disDate_Range"][1])) {
								$filter .= " and EPSLastES.EvnSection_disDate <= :EvnSectionLast_disDate_Range_1";
								$queryParams["EvnSectionLast_disDate_Range_1"] = $data["EvnSection_disDate_Range"][1];
							}
						}
						break;
					case "EvnSection":
						$filter .= " and ESEC.LeaveType_id = :LeaveType_id";
						break;
				}
				$queryParams["LeaveType_id"] = $data["LeaveType_id"];
			}
			if (isset($data["LeaveCause_id"]) ||
				isset($data["ResultDesease_id"]) ||
				isset($data["Org_oid"]) ||
				isset($data["LpuUnitType_oid"]) ||
				isset($data["LpuSection_oid"]) ||
				isset($data["EvnLeaveBase_UKL"]) ||
				isset($data["EvnLeave_IsAmbul"]) ||
				isset($data["EvnDie_IsAnatom"])
			) {
				if (isset($data["EvnDie_IsAnatom"]) && !empty($data["EvnDie_IsAnatom"])) {
					$filter .= " and ED.EvnDie_IsAnatom = :EvnDie_IsAnatom";
					$queryParams["EvnDie_IsAnatom"] = $data["EvnDie_IsAnatom"];
				}
				if (isset($data["LeaveCause_id"])) {
					$filter .= " and COALESCE(EL.LeaveCause_id, EOL.LeaveCause_id, EOST.LeaveCause_id, EOS.LeaveCause_id) = :LeaveCause_id";
					$queryParams["LeaveCause_id"] = $data["LeaveCause_id"];
				}
				if (isset($data["ResultDesease_id"])) {
					$filter .= " and COALESCE(EL.ResultDesease_id, ED.ResultDesease_id, EOL.ResultDesease_id, EOST.ResultDesease_id, EOS.ResultDesease_id) = :ResultDesease_id";
					$queryParams["ResultDesease_id"] = $data["ResultDesease_id"];
				}
				if (isset($data["Org_oid"])) {
					$filter .= " and EOL.Org_oid = :Org_oid";
					$queryParams["Org_oid"] = $data["Org_oid"];
				}
				if (isset($data["LpuUnitType_oid"])) {
					$filter .= " and EOST.LpuUnitType_oid = :LpuUnitType_oid";
					$queryParams["LpuUnitType_oid"] = $data["LpuUnitType_oid"];
				}
				if (isset($data["LpuSection_oid"])) {
					$filter .= " and coalesce(EOS.LpuSection_oid, EOST.LpuSection_oid) = :LpuSection_oid";
					$queryParams["LpuSection_oid"] = $data["LpuSection_oid"];
				}
				if (isset($data["EvnLeaveBase_UKL"])) {
					$filter .= " and COALESCE(ED.EvnDie_UKL, EL.EvnLeave_UKL, EOL.EvnOtherLpu_UKL, EOST.EvnOtherStac_UKL, EOS.EvnOtherSection_UKL, EOSBP.EvnOtherSectionBedProfile_UKL) = :EvnLeaveBase_UKL";
					$queryParams["EvnLeaveBase_UKL"] = $data["EvnLeaveBase_UKL"];
				}
				if (isset($data["EvnLeave_IsAmbul"])) {
					$filter .= " and coalesce(EL.EvnLeave_IsAmbul, 1) = :EvnLeave_IsAmbul";
					$queryParams["EvnLeave_IsAmbul"] = $data["EvnLeave_IsAmbul"];
				}
			}
		}
		if (isset($data["StickCause_id"]) ||
			isset($data["StickType_id"]) ||
			isset($data["EvnStick_begDate_Range"][0]) ||
			isset($data["EvnStick_begDate_Range"][1]) ||
			isset($data["EvnStick_endDate_Range"][0]) ||
			isset($data["EvnStick_endDate_Range"][1])
		) {
			$evn_stick_filter = "";
			if (isset($data["EvnStick_begDate_Range"][0])) {
				$evn_stick_filter .= " and ESB.EvnStickBase_setDT >= :EvnStick_begDate_Range_0";
				$queryParams["EvnStick_begDate_Range_0"] = $data["EvnStick_begDate_Range"][0];
			}
			if (isset($data["EvnStick_begDate_Range"][1])) {
				$evn_stick_filter .= " and ESB.EvnStickBase_setDT <= :EvnStick_begDate_Range_1";
				$queryParams["EvnStick_begDate_Range_1"] = $data["EvnStick_begDate_Range"][1];
			}
			if (isset($data["EvnStick_endDate_Range"][0])) {
				$evn_stick_filter .= "
					and (
						case
							when ESB.StickType_id = 1 and ESB.EvnStickBase_disDT >= :EvnStick_endDate_Range_0 then 1
							when ESB.StickType_id = 2 and exists (
								select EvnStickWorkRelease_id
								from v_EvnStickWorkRelease
								where EvnStickBase_id = ESB.EvnStickBase_id and EvnStickWorkRelease_endDT >= :EvnStick_endDate_Range_0
							) then 1
							else 0
						end = 1
					)
				";
				$queryParams["EvnStick_endDate_Range_0"] = $data["EvnStick_endDate_Range"][0];
			}
			if (isset($data["EvnStick_endDate_Range"][1])) {
				$evn_stick_filter .= "
					and (
						case
							when ESB.StickType_id = 1 and ESB.EvnStickBase_disDT <= :EvnStick_endDate_Range_1 then 1
							when ESB.StickType_id = 2 and exists (
								select EvnStickWorkRelease_id
								from v_EvnStickWorkRelease
								where EvnStickBase_id = ESB.EvnStickBase_id and EvnStickWorkRelease_endDT <= :EvnStick_endDate_Range_1
							) then 1
							else 0
						end = 1
					)
				";
				$queryParams["EvnStick_endDate_Range_1"] = $data["EvnStick_endDate_Range"][1];
			}
			if (isset($data["StickCause_id"])) {
				$evn_stick_filter .= " and ESB.StickCause_id = :StickCause_id";
				$queryParams["StickCause_id"] = $data["StickCause_id"];
			}
			if (isset($data["StickType_id"])) {
				$evn_stick_filter .= " and ESB.StickType_id = :StickType_id";
				$queryParams["StickType_id"] = $data["StickType_id"];
			}
			$filter .= "
				and exists (
					select EvnStickBase_id
					from v_EvnStickBase ESB
					where ESB.EvnStickBase_mid = EPS.EvnPS_id
					  {$evn_stick_filter}
					union all
					select Evn_id as EvnStickBase_id
					from
						v_EvnLink EL
						inner join v_EvnStickBase ESB on ESB.EvnStickBase_id = EL.Evn_lid
					where EL.Evn_id = EPS.EvnPS_id
					  {$evn_stick_filter}
				)
			";
		}
	}
	public static function selectParams_EvnAggStom(Search_model $callObject, $dbf, $getLpuIdFilter, &$data, &$filter, &$queryParams)
	{
		if ($dbf !== true) {
			if ("EvnPLStom" == $data["SearchFormType"]) {
				if (!empty($data["DeseaseType_id"]) || !empty($data["Diag_Code_From"]) || !empty($data["Diag_Code_To"])) {
					$filter .= "
						and exists (
							select t1.EvnVizitPLStom_id
							from
								v_EvnVizitPLStom t1
								inner join v_Diag t2 on t2.Diag_id = t1.Diag_id
							where t1.EvnVizitPLStom_pid = EPLS.EvnPLStom_id
					";
					if (!empty($data['DeseaseType_id'])) {
						if ($data['DeseaseType_id'] == 99) {
							$filter .= " and t1.DeseaseType_id is null";
						} else {
							$filter .= " and t1.DeseaseType_id = :DeseaseType_id";
							$queryParams['DeseaseType_id'] = $data['DeseaseType_id'];
						}
					}
					if (!empty($data['Diag_Code_From'])) {
						$filter .= " and t2.Diag_Code >= :Diag_Code_From";
						$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
					}
					if (!empty($data['Diag_Code_To'])) {
						$filter .= " and t2.Diag_Code <= :Diag_Code_To";
						$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
					}
					$filter .= ")";
				}
				if ($callObject->regionNick == "kz") {
					if ($data['toAis25'] == 2) {
						$filter .= " and air.AISResponse_id is not null";
					} elseif ($data['toAis25'] == 1) {
						$filter .= " and air.AISResponse_id is null and euais.UslugaComplex_id is not null";
					}
					if ($data['toAis259'] == 2) {
						$filter .= " and air9.AISResponse_id is not null";
					} elseif ($data['toAis259'] == 1) {
						$filter .= " and air9.AISResponse_id is null and euais9.UslugaComplex_id is not null";
					}
				}
			}
			if ("EvnVizitPLStom" == $data["SearchFormType"]) {
				if (!empty($data["DeseaseType_id"]) || !empty($data["Diag_Code_From"]) || !empty($data["Diag_Code_To"])) {
					if (!empty($data["DeseaseType_id"])) {
						$filter .= " and EVPLS.DeseaseType_id = :DeseaseType_id";
						$queryParams["DeseaseType_id"] = $data["DeseaseType_id"];
					}
					if (!empty($data["Diag_Code_From"])) {
						$filter .= " and evpldiag.Diag_Code >= :Diag_Code_From";
						$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
					}
					if (!empty($data["Diag_Code_To"])) {
						$filter .= " and evpldiag.Diag_Code <= :Diag_Code_To";
						$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
					}
				}
			}
		}
		if (!empty($data["UslugaCategory_id"]) || !empty($data["UslugaComplex_Code_From"]) || !empty($data["UslugaComplex_Code_To"])) {
			$filter_evnvizit = ("EvnVizitPLStom" == $data["SearchFormType"])
				? "EU.EvnUsluga_pid = EVPLS.EvnVizitPLStom_id"
				: "EU.EvnUsluga_rid = EPLS.EvnPLStom_id";
			$filter .= "
				and exists (
					select uc.UslugaComplex_id
					from
						v_EvnUsluga EU
						inner join UslugaComplex uc on uc.UslugaComplex_id = EU.UslugaComplex_id
					where {$filter_evnvizit}
					  and EU.Lpu_id {$getLpuIdFilter}
					  and EU.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaStom')
			";
			if (!empty($data["UslugaCategory_id"])) {
				$filter .= " and uc.UslugaCategory_id = :UslugaCategory_id ";
				$queryParams["UslugaCategory_id"] = $data["UslugaCategory_id"];
			}
			if (!empty($data["UslugaComplex_Code_From"])) {
				$filter .= " and uc.UslugaComplex_Code >= :UslugaComplex_Code_From ";
				$queryParams["UslugaComplex_Code_From"] = $data["UslugaComplex_Code_From"];
			}
			if (!empty($data["UslugaComplex_Code_To"])) {
				$filter .= " and uc.UslugaComplex_Code <= :UslugaComplex_Code_To ";
				$queryParams["UslugaComplex_Code_To"] = $data["UslugaComplex_Code_To"];
			}
			$filter .= ")";
		}
		if (!empty($data["UslugaComplex_uid"]) || !empty($data["UslugaComplex_Code"])) {
			$filter_evnvizit = ("EvnVizitPLStom" == $data["SearchFormType"])
				? "EU.EvnUsluga_pid = EVPLS.EvnVizitPLStom_id"
				: "EU.EvnUsluga_rid = EPLS.EvnPLStom_id";
			if (!empty($data["UslugaComplex_uid"])) {
				$filter_evnvizit .= " and uc.UslugaComplex_id = :UslugaComplex_uid";
				$queryParams["UslugaComplex_uid"] = $data["UslugaComplex_uid"];
			}
			if (!empty($data["UslugaComplex_Code"])) {
				$data["UslugaComplex_Code"] = str_replace("%", "", $data["UslugaComplex_Code"]);
				$filter_evnvizit .= " and uc.UslugaComplex_Code ilike '%'||:UslugaComplexCode";
				$queryParams["UslugaComplexCode"] = $data["UslugaComplex_Code"];
			}
			$filter .= "
				and exists (
					select uc.UslugaComplex_id
					from
						v_EvnUsluga EU
						inner join v_UslugaComplex uc on uc.UslugaComplex_id = EU.UslugaComplex_id
					where {$filter_evnvizit} and EU.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaStom')
				)
			";
		}
		if (isset($data["TreatmentClass_id"])) {
			$filter .= " and EVPLS.TreatmentClass_id = :TreatmentClass_id";
			$queryParams["TreatmentClass_id"] = $data["TreatmentClass_id"];
		}
		if (isset($data["EvnPL_IsUnlaw"])) {
			$filter .= " and EPLS.EvnPLStom_IsUnlaw = :EvnPLStom_IsUnlaw";
			$queryParams["EvnPLStom_IsUnlaw"] = $data["EvnPL_IsUnlaw"];
		}
		if (isset($data["EvnPL_IsUnport"])) {
			$filter .= " and EPLS.EvnPLStom_IsUnport = :EvnPLStom_IsUnport";
			$queryParams["EvnPLStom_IsUnport"] = $data["EvnPL_IsUnport"];
		}
		if (isset($data["EvnPL_NumCard"])) {
			$filter .= " and EPLS.EvnPLStom_NumCard = :EvnPLStom_NumCard";
			$queryParams["EvnPLStom_NumCard"] = $data["EvnPL_NumCard"];
		}
		if (isset($data["EvnPL_setDate_Range"][0])) {
			$filter .= " and EPLS.EvnPLStom_setDate >= :EvnPLStom_setDate_Range_0";
			$queryParams["EvnPLStom_setDate_Range_0"] = $data["EvnPL_setDate_Range"][0];
		}
		if (isset($data["EvnPL_setDate_Range"][1])) {
			$filter .= " and EPLS.EvnPLStom_setDate <= :EvnPLStom_setDate_Range_1";
			$queryParams["EvnPLStom_setDate_Range_1"] = $data["EvnPL_setDate_Range"][1];
		}
		if (isset($data["EvnPL_disDate_Range"][0])) {
			$filter .= " and EPLS.EvnPLStom_disDate >= :EvnPLStom_disDate_Range_0";
			$queryParams["EvnPLStom_disDate_Range_0"] = $data["EvnPL_disDate_Range"][0];
		}
		if (isset($data["EvnPL_disDate_Range"][1])) {
			$filter .= " and EPLS.EvnPLStom_disDate <= :EvnPLStom_disDate_Range_1";
			$queryParams["EvnPLStom_disDate_Range_1"] = $data["EvnPL_disDate_Range"][1];
		}
		if (isset($data["PrehospTrauma_id"])) {
			$filter .= " and EPLS.PrehospTrauma_id = :PrehospTrauma_id";
			$queryParams["PrehospTrauma_id"] = $data["PrehospTrauma_id"];
		}
		if (isset($data["EvnPLStom_InRegistry"])) {
			if (in_array($callObject->regionNick, ["ekb"])) {
				if ($data["EvnPLStom_InRegistry"] == 1) {
					$filter .= " and (EPLS.EvnPLStom_IsInReg = 1 or EPLS.EvnPLStom_IsInReg is null)";
				} elseif ($data["EvnPLStom_InRegistry"] == 2) {
					$filter .= " and EPLS.EvnPLStom_IsInReg = 2";
				}
			} elseif (in_array($callObject->regionNick, ["penza"])) {
				if ($data["EvnPLStom_InRegistry"] == 1) {
					$filter .= "
						and not exists (
							select EvnDiagPLStom_id
							from v_EvnDiagPLStom EDPLS
							where EDPLS.EvnDiagPLStom_rid = EPLS.EvnPLStom_id and EDPLS.EvnDiagPLStom_IsInreg = 2
							limit 1
						)
					";
				} elseif ($data["EvnPLStom_InRegistry"] == 2) {
					$filter .= "
						and exists (
							select EvnDiagPLStom_id
							from v_EvnDiagPLStom EDPLS
							where EDPLS.EvnDiagPLStom_rid = EPLS.EvnPLStom_id and EDPLS.EvnDiagPLStom_IsInreg = 2
							limit 1
						)
					";
				}
			}
		}
		if (!empty($data["Diag_IsNotSet"])) {
			$filter .= ("EvnVizitPLStom" == $data["SearchFormType"])
				? " and EVPLS.Diag_id is null"
				: " and EPLS.Diag_id is null";
		}
		if (!empty($data["EvnVizitPLStom_isPaid"])) {
			switch ($data["SearchFormType"]) {
				case "EvnPLStom":
					switch ($callObject->getRegionNick()) {
						case "astra":
						case "kareliya":
						case "pskov":
							$filter .= " and coalesce(LEVPLS.EvnVizitPLStom_isPaid, 1) = :EvnVizitPLStom_isPaid";
							break;
						case "samara":
						case "penza":
						case "ufa":
						case "vologda":
						case "perm":
							$filter .= " and coalesce(EVPLSpaid.EvnVizitPLStom_isPaid, 1) = :EvnVizitPLStom_isPaid";
							break;
						case "khak":
						case "ekb":
						case "buryatiya":
							$filter .= " and coalesce(EPLS.EvnPLStom_isPaid, 1) = :EvnVizitPLStom_isPaid";
							break;
					}
					break;
				case "EvnVizitPLStom":
					$filter .= " and coalesce(EVPLS.EvnVizitPLStom_isPaid,1) = :EvnVizitPLStom_isPaid";
					break;
			}
			$queryParams["EvnVizitPLStom_isPaid"] = $data["EvnVizitPLStom_isPaid"];
		}
		if (isset($data["LpuSectionViz_id"]) ||
			isset($data["LpuBuildingViz_id"]) ||
			isset($data["MedPersonalViz_id"]) ||
			isset($data["MedStaffFactViz_id"]) ||
			isset($data["MedPersonalViz_sid"]) ||
			isset($data["PayType_id"]) ||
			isset($data["ServiceType_id"]) ||
			isset($data["Vizit_Date_Range"][0]) ||
			isset($data["Vizit_Date_Range"][1]) ||
			isset($data["VizitType_id"]) ||
			isset($data["EvnVizitPLStom_IsPrimaryVizit"])
		) {
			$filter .= "
				and exists (
					select 1
					from v_EvnVizitPLStom EVPLS2
					where (1 = 1) and EVPLS2.Lpu_id {$getLpuIdFilter}
			";
			$filter .= ("EvnVizitPLStom" == $data["SearchFormType"])
				? " and EVPLS2.EvnVizitPLStom_id = EVPLS.EvnVizitPLStom_id"
				: " and EVPLS2.EvnVizitPLStom_pid = EPLS.EvnPLStom_id";
			if (isset($data["LpuSectionViz_id"])) {
				$filter .= " and EVPLS2.LpuSection_id = :LpuSectionViz_id";
				$queryParams["LpuSectionViz_id"] = $data["LpuSectionViz_id"];
			} elseif (isset($data["LpuBuildingViz_id"])) {
				$filter .= "
					and exists (
						select 1
						from
							LpuSection LS
							left join LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
						where LU.LpuBuilding_id = :LpuBuildingViz_id and LS.LpuSection_id = EVPLS2.LpuSection_id
					)
				";
				$queryParams["LpuBuildingViz_id"] = $data["LpuBuildingViz_id"];
			}
			if (isset($data["MedStaffFactViz_id"])) {
				$filter .= " and EVPLS2.MedStaffFact_id = :MedStaffFactViz_id";
				$queryParams["MedStaffFactViz_id"] = $data["MedStaffFactViz_id"];
			}
			if (isset($data["MedPersonalViz_id"])) {
				$filter .= " and EVPLS2.MedPersonal_id = :MedPersonalViz_id";
				$queryParams["MedPersonalViz_id"] = $data["MedPersonalViz_id"];
			}
			if (isset($data["MedPersonalViz_sid"])) {
				$filter .= " and EVPLS2.MedPersonal_sid = :MedPersonalViz_sid";
				$queryParams["MedPersonalViz_sid"] = $data["MedPersonalViz_sid"];
			}
			if (isset($data["PayType_id"])) {
				$filter .= " and EVPLS2.PayType_id = :PayType_id";
				$queryParams["PayType_id"] = $data["PayType_id"];
			}
			if (isset($data["EvnVizitPLStom_IsPrimaryVizit"])) {
				$filter .= " and EVPLS2.EvnVizitPLStom_IsPrimaryVizit = :EvnVizitPLStom_IsPrimaryVizit";
				$queryParams["EvnVizitPLStom_IsPrimaryVizit"] = $data["EvnVizitPLStom_IsPrimaryVizit"];
			}
			if (isset($data["ServiceType_id"])) {
				$filter .= " and EVPLS2.ServiceType_id = :ServiceType_id";
				$queryParams["ServiceType_id"] = $data["ServiceType_id"];
			}
			if (isset($data["Vizit_Date_Range"][0])) {
				$filter .= " and EVPLS2.EvnVizitPLStom_setDate >= :Vizit_Date_Range_0";
				$queryParams["Vizit_Date_Range_0"] = $data["Vizit_Date_Range"][0];
			}
			if (isset($data["Vizit_Date_Range"][1])) {
				$filter .= " and EVPLS2.EvnVizitPLStom_setDate <= :Vizit_Date_Range_1";
				$queryParams["Vizit_Date_Range_1"] = $data["Vizit_Date_Range"][1];
			}
			if (isset($data["VizitType_id"])) {
				$filter .= " and EVPLS2.VizitType_id = :VizitType_id";
				$queryParams["VizitType_id"] = $data["VizitType_id"];
			}
			$filter .= ")";
		}
		if (isset($data["PL_NumDirection"])) {
			$filter .= " and EPLS.EvnDirection_Num = :PL_NumDirection";
			$queryParams["PL_NumDirection"] = $data["PL_NumDirection"];
		}
		if (isset($data["PL_DirectionDate"])) {
			$filter .= " and coalesce(EPLS.EvnDirection_setDT,EPLS.EvnPLStom_setDate) = :PL_DirectionDate";
			$queryParams["PL_DirectionDate"] = $data["PL_DirectionDate"];
		}
		if (isset($data["PL_ElDirection"]) && $data["PL_ElDirection"] == "on") {
			$filter .= " and EPLS.EvnDirection_id is null";
		}
		if (isset($data["PL_Org_id"])) {
			$filter .= " and EPLS.Org_did = :PL_Org_id";
			$queryParams["PL_Org_id"] = $data["PL_Org_id"];
		}
		if (isset($data["PL_LpuSection_id"])) {
			$filter .= " and EPLS.LpuSection_did = :PL_LpuSection_id";
			$queryParams["PL_LpuSection_id"] = $data["PL_LpuSection_id"];
		}
		if (isset($data["PL_Diag_id"])) {
			$filter .= " and EPLS.diag_did = :PL_Diag_id";
			$queryParams["PL_Diag_id"] = $data["PL_Diag_id"];
		}
		if (isset($data["PL_PrehospDirect_id"])) {
			if ($data["PL_PrehospDirect_id"] == 99) {
				$filter .= " and EPLS.PrehospDirect_id is null";
			} else {
				$filter .= " and EPLS.PrehospDirect_id = :PL_PrehospDirect_id";
				$queryParams["PL_PrehospDirect_id"] = $data["PL_PrehospDirect_id"];
			}
		}
		if (isset($data["DirectClass_id"])) {
			$filter .= " and EPLS.DirectClass_id = :DirectClass_id";
			$queryParams["DirectClass_id"] = $data["DirectClass_id"];
		}
		if (isset($data["DirectType_id"])) {
			$filter .= " and EPLS.DirectType_id = :DirectType_id";
			$queryParams["DirectType_id"] = $data["DirectType_id"];
		}
		if (isset($data["EvnPL_IsFinish"])) {
			$filter .= " and EPLS.EvnPLStom_IsFinish = :EvnPLStom_IsFinish";
			$queryParams["EvnPLStom_IsFinish"] = $data["EvnPL_IsFinish"];
		}
		if (isset($data["Lpu_oid"])) {
			$filter .= " and EPLS.Lpu_oid = :Lpu_oid";
			$queryParams["Lpu_oid"] = $data["Lpu_oid"];
		}
		if (isset($data["LpuSection_oid"])) {
			$filter .= " and EPLS.LpuSection_oid = :LpuSection_oid";
			$queryParams["LpuSection_oid"] = $data["LpuSection_oid"];
		}
		if (isset($data["ResultClass_id"])) {
			$filter .= " and EPLS.ResultClass_id = :ResultClass_id";
			$queryParams["ResultClass_id"] = $data["ResultClass_id"];
		}
		if (isset($data["StickCause_id"]) || isset($data["StickType_id"]) ||
			isset($data["EvnStick_begDate_Range"][0]) || isset($data["EvnStick_begDate_Range"][1]) ||
			isset($data["EvnStick_endDate_Range"][0]) || isset($data["EvnStick_endDate_Range"][1])
		) {
			$evn_stick_filter = "";
			if (isset($data["EvnStick_begDate_Range"][0])) {
				$evn_stick_filter .= " and ESB.EvnStickBase_setDT >= :EvnStick_begDate_Range_0";
				$queryParams["EvnStick_begDate_Range_0"] = $data["EvnStick_begDate_Range"][0];
			}
			if (isset($data["EvnStick_begDate_Range"][1])) {
				$evn_stick_filter .= " and ESB.EvnStickBase_setDT <= :EvnStick_begDate_Range_1";
				$queryParams["EvnStick_begDate_Range_1"] = $data["EvnStick_begDate_Range"][1];
			}
			if (isset($data["EvnStick_endDate_Range"][0])) {
				$evn_stick_filter .= "
					and (
						case
							when ESB.StickType_id = 1 and ESB.EvnStickBase_disDT >= :EvnStick_endDate_Range_0 then 1
							when ESB.StickType_id = 2 and exists (
								select EvnStickWorkRelease_id
								from v_EvnStickWorkRelease
								where EvnStickBase_id = ESB.EvnStickBase_id and EvnStickWorkRelease_endDT >= :EvnStick_endDate_Range_0
							) then 1
							else 0
						end = 1
					)
				";
				$queryParams["EvnStick_endDate_Range_0"] = $data["EvnStick_endDate_Range"][0];
			}
			if (isset($data["EvnStick_endDate_Range"][1])) {
				$evn_stick_filter .= "
					and (
						case
							when ESB.StickType_id = 1 and ESB.EvnStickBase_disDT <= :EvnStick_endDate_Range_1 then 1
							when ESB.StickType_id = 2 and exists (
								select EvnStickWorkRelease_id
								from v_EvnStickWorkRelease
								where EvnStickBase_id = ESB.EvnStickBase_id and EvnStickWorkRelease_endDT <= :EvnStick_endDate_Range_1
							) then 1
							else 0
						end = 1
					)
				";
				$queryParams["EvnStick_endDate_Range_1"] = $data["EvnStick_endDate_Range"][1];
			}
			if (isset($data["StickCause_id"])) {
				$evn_stick_filter .= " and ESB.StickCause_id = :StickCause_id";
				$queryParams["StickCause_id"] = $data["StickCause_id"];
			}
			if (isset($data["StickType_id"])) {
				$evn_stick_filter .= " and ESB.StickType_id = :StickType_id";
				$queryParams["StickType_id"] = $data["StickType_id"];
			}
			$filter .= "
				and exists (
					select EvnStickBase_id
					from v_EvnStickBase ESB
					where ESB.EvnStickBase_mid = EPLS.EvnPLStom_id {$evn_stick_filter}
					union all
					select Evn_id as EvnStickBase_id
					from v_EvnLink EL
						inner join v_EvnStickBase ESB on ESB.EvnStickBase_id = EL.Evn_lid
					where EL.Evn_id = EPLS.EvnPLStom_id {$evn_stick_filter}
				)
			";
		}
	}

	public static function selectParams_EvnVizitPL(Search_model $callObject, $dbf, $getLpuIdFilter, &$data, &$filter, &$queryParams, &$queryWithArray)
	{
		if ($data["PersonPeriodicType_id"] == 2) {
			if ("EvnVizitPL" == $data["SearchFormType"]) {
				$filter .= " and EVizitPL.Lpu_id {$getLpuIdFilter} ";
			}
		} else {
			if ("EvnVizitPL" == $data["SearchFormType"]) {
				$filter .= " and EVizitPL.Lpu_id {$getLpuIdFilter} ";
			}
			if ("EvnPL" == $data["SearchFormType"]) {
			    $filter .= " and EPL.Lpu_id {$getLpuIdFilter} ";
            }
		}
		if ($dbf !== true) {
			if ("EvnPL" == $data["SearchFormType"]) {
				if ($callObject->regionNick == "kz") {
					if ($data["toAis25"] == 2) {
						$filter .= " and air.AISResponse_id is not null";
					} elseif ($data["toAis25"] == 1) {
						$filter .= " and air.AISResponse_id is null and euais.UslugaComplex_id is not null";
					}
					if ($data["toAis259"] == 2) {
						$filter .= " and air9.AISResponse_id is not null";
					} elseif ($data["toAis259"] == 1) {
						$filter .= " and air9.AISResponse_id is null and euais9.UslugaComplex_id is not null";
					}
				}
			}
		}
		switch ($data["SearchFormType"]) {
			case "EvnPL":
				if (isset($data["EvnPL_InRegistry"])) {
					if (in_array($callObject->regionNick, ["ekb"])) {
						if ($data["EvnPL_InRegistry"] == 1) {
							$filter .= " and (EPL.EvnPL_IsInReg = 1 or EPL.EvnPL_IsInReg is null)";
						} elseif ($data["EvnPL_InRegistry"] == 2) {
							$filter .= " and EPL.EvnPL_IsInReg = 2";
						}
					} elseif (in_array($callObject->regionNick, ["penza"])) {
						if ($data["EvnPL_InRegistry"] == 1) {
							$filter .= "
								and not exists (
									select EvnVizitPL_IsInReg
									from v_EvnVizitPL EVPL2
									where EVPL2.EvnVizitPL_rid = EPL.EvnPL_id and EVPL2.EvnVizitPL_IsInReg = 2
									limit 1
								)
							";
						} elseif ($data["EvnPL_InRegistry"] == 2) {
							$filter .= "
								and exists (
									select EvnVizitPL_IsInReg
									from v_EvnVizitPL EVPL2
									where EVPL2.EvnVizitPL_rid = EPL.EvnPL_id and EVPL2.EvnVizitPL_IsInReg = 2
									limit 1
								)
							";
						}
					}
				}
				break;
			case "EvnVizitPL":
				if (isset($data["EvnPL_InRegistry"])) {
					if (in_array($callObject->regionNick, ["ekb"])) {
						if ($data["EvnPL_InRegistry"] == 1) {
							$filter .= " and (EPL.EvnPL_IsInReg = 1 or EPL.EvnPL_IsInReg is null)";
						} elseif ($data["EvnPL_InRegistry"] == 2) {
							$filter .= " and EPL.EvnPL_IsInReg = 2";
						}
					} elseif (in_array($callObject->regionNick, ["penza"])) {
						if ($data["EvnPL_InRegistry"] == 1) {
							$filter .= " and (EVizitPL.EvnVizitPL_IsInReg = 1 or EVizitPL.EvnVizitPL_IsInReg is null)";
						} elseif ($data["EvnPL_InRegistry"] == 2) {
							$filter .= " and EVizitPL.EvnVizitPL_IsInReg = 2";
						}
					}
				}
				break;
		}
		if (!empty($data["UslugaCategory_id"]) || !empty($data["UslugaComplex_Code_From"]) || !empty($data["UslugaComplex_Code_To"])) {
			$filter_evnvizit = ("EvnVizitPL" == $data["SearchFormType"])
				? "EU.EvnUsluga_pid = EVizitPL.EvnVizitPL_id"
				: "EU.EvnUsluga_rid = EPL.EvnPL_id";
			$filter .= "
				and exists (
					select uc.UslugaComplex_id
					from
						v_EvnUsluga EU
						inner join UslugaComplex uc on uc.UslugaComplex_id = EU.UslugaComplex_id
					where {$filter_evnvizit}
					   and EU.Lpu_id {$getLpuIdFilter}
					   and EU.EvnClass_SysNick in ('EvnUslugaCommon','EvnUslugaOper','EvnUslugaPar')
			";
			if (!empty($data["UslugaCategory_id"])) {
				$filter .= " and uc.UslugaCategory_id = :UslugaCategory_id ";
				$queryParams["UslugaCategory_id"] = $data["UslugaCategory_id"];
			}
			if (!empty($data["UslugaComplex_Code_From"])) {
				$filter .= " and uc.UslugaComplex_Code >= :UslugaComplex_Code_From ";
				$queryParams["UslugaComplex_Code_From"] = $data["UslugaComplex_Code_From"];
			}
			if (!empty($data["UslugaComplex_Code_To"])) {
				$filter .= " and uc.UslugaComplex_Code <= :UslugaComplex_Code_To ";
				$queryParams["UslugaComplex_Code_To"] = $data["UslugaComplex_Code_To"];
			}
			$filter .= ")";
		}
		if (!empty($data["UslugaComplex_uid"]) || !empty($data["UslugaComplex_Code"])) {
			$filter_evnvizit = ("EvnVizitPL" == $data["SearchFormType"])
				? "EU.EvnUsluga_pid = EVizitPL.EvnVizitPL_id"
				: "EU.EvnUsluga_rid = EPL.EvnPL_id";
			if (!empty($data["UslugaComplex_uid"])) {
				$filter_evnvizit .= " and uc.UslugaComplex_id = :UslugaComplex_uid";
				$queryParams["UslugaComplex_uid"] = $data["UslugaComplex_uid"];
			}
			if (!empty($data["UslugaComplex_Code"])) {
				$searchMode = 0;
				if (substr($data["UslugaComplex_Code"], 0, 1) == "%") {
					$searchMode += 1;
				}
				if (substr($data["UslugaComplex_Code"], -1) == "%") {
					$searchMode += 2;
				}
				$data["UslugaComplex_Code"] = str_replace("%", "", $data["UslugaComplex_Code"]);
				switch ($searchMode) {
					case 0:
						$filter_evnvizit .= " and uc.UslugaComplex_Code = :UslugaComplexCode";
						$queryParams["UslugaComplexCode"] = $data["UslugaComplex_Code"];
						break;
					case 1:
						$filter_evnvizit .= " and uc.UslugaComplex_Code ilike '%'||:UslugaComplexCode";
						$queryParams["UslugaComplexCode"] = $data["UslugaComplex_Code"];
						break;
					case 2:
						$filter_evnvizit .= " and uc.UslugaComplex_Code ilike :UslugaComplexCode||'%'";
						$queryParams["UslugaComplexCode"] = $data["UslugaComplex_Code"];
						break;
					case 3:
						$filter_evnvizit .= " and uc.UslugaComplex_Code ilike '%'||:UslugaComplexCode||'%'";
						$queryParams["UslugaComplexCode"] = $data["UslugaComplex_Code"];
						break;
				}
			}
			$filter_usluga_category = "";
			switch ($callObject->getRegionNick()) {
				case "perm":
					if (!empty($data["UslugaComplex_uid"])) {
						switch ($data["SearchFormType"]) {
							case "EvnPL":
								$filter .= " and EVPL.UslugaComplex_id = :UslugaComplex_uid";
								break;
							case "EvnVizitPL":
								$filter .= " and EVizitPL.UslugaComplex_id = :UslugaComplex_uid";
								break;
						}
						$queryParams["UslugaComplex_uid"] = $data["UslugaComplex_uid"];
					}
					break;
				default:
					$filter .= "
						and exists (
							select uc.UslugaComplex_id
							from
								v_EvnUsluga EU
								inner join v_UslugaComplex uc on uc.UslugaComplex_id = EU.UslugaComplex_id
								inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id
							where {$filter_evnvizit}
							  and EU.EvnClass_SysNick in ('EvnUslugaCommon','EvnUslugaOper','EvnUslugaPar')
							  {$filter_usluga_category}
							limit 1
						)
					";
					//TODO Странное условие, устанавливается уже после использования, нужно как-то проверить
					//$filter_usluga_category = "and ucat.UslugaCategory_SysNick = 'lpusection'";
					break;
			}
		}
		if (isset($data["EvnPL_IsUnlaw"])) {
			$filter .= " and EPL.EvnPL_IsUnlaw = :EvnPL_IsUnlaw";
			$queryParams["EvnPL_IsUnlaw"] = $data["EvnPL_IsUnlaw"];
		}
		if (isset($data["EvnPL_IsUnport"])) {
			$filter .= " and EPL.EvnPL_IsUnport = :EvnPL_IsUnport";
			$queryParams["EvnPL_IsUnport"] = $data["EvnPL_IsUnport"];
		}
		if (isset($data["EvnPL_NumCard"])) {
			$filter .= " and EPL.EvnPL_NumCard = :EvnPL_NumCard";
			$queryParams["EvnPL_NumCard"] = $data["EvnPL_NumCard"];
		}
		if (isset($data["EvnPL_setDate_Range"][0])) {
			$filter .= " and Evn.Evn_setDT::date >= :EvnPL_setDate_Range_0";
			$queryParams["EvnPL_setDate_Range_0"] = $data["EvnPL_setDate_Range"][0];
		}
		if (isset($data["EvnPL_setDate_Range"][1])) {
			$filter .= " and Evn.Evn_setDT::date <= :EvnPL_setDate_Range_1";
			$queryParams["EvnPL_setDate_Range_1"] = $data["EvnPL_setDate_Range"][1];
		}
		if (!empty($data["Diag_IsNotSet"])) {
			$filter .= ("EvnVizitPL" == $data["SearchFormType"])
				? " and EVizitPL.Diag_id is null"
				: " and EPL.Diag_id is null";
		}
		if (($data["SearchFormType"] == "EvnVizitPL") && (isset($data["VizitClass_id"]))) {
			$filter .= " and EVizitPL.VizitClass_id = :VizitClass_id";
			$queryParams["VizitClass_id"] = $data["VizitClass_id"];
		}
		if (isset($data["EvnPL_disDate_Range"][0])) {
			$filter .= " and Evn.Evn_disDT::date >= :EvnPL_disDate_Range_0";
			$queryParams["EvnPL_disDate_Range_0"] = $data["EvnPL_disDate_Range"][0];
		}
		if (isset($data["EvnPL_disDate_Range"][1])) {
			$filter .= " and Evn.Evn_disDT::date <= :EvnPL_disDate_Range_1";
			$queryParams["EvnPL_disDate_Range_1"] = $data["EvnPL_disDate_Range"][1];
		}
		if (isset($data["TreatmentClass_id"])) {
			$filter .= ($data["SearchFormType"] == "EvnVizitPL")
				? " and EVizitPL.TreatmentClass_id = :TreatmentClass_id"
				: " and EVPL.TreatmentClass_id = :TreatmentClass_id";
			$queryParams["TreatmentClass_id"] = $data["TreatmentClass_id"];
		}
		if (isset($data["LpuSectionViz_id"]) ||
			isset($data["LpuBuildingViz_id"]) ||
			isset($data["MedPersonalViz_id"]) ||
			isset($data["MedStaffFactViz_id"]) ||
			isset($data["MedPersonalViz_sid"]) ||
			isset($data["PayType_id"]) ||
			isset($data["ServiceType_id"]) ||
			isset($data["Vizit_Date_Range"][0]) ||
			isset($data["Vizit_Date_Range"][1]) ||
			isset($data["VizitType_id"]) ||
			isset($data["DeseaseType_id"]) ||
			isset($data["Diag_Code_From"]) ||
			isset($data["Diag_Code_To"]) ||
			!empty($data["HealthKind_id"]) ||
			($data["SearchFormType"] == "EvnPL" && isset($data["VizitClass_id"]))
		) {
			$queryWithAdditionalJoin = [];
			$queryWithAdditionalWhere = ["EVPL.Lpu_id " . $getLpuIdFilter];
			if (!empty($data["Diag_Code_From"]) || !empty($data["Diag_Code_To"])) {
				$queryWithAdditionalJoin[] = "left join v_Diag D on D.Diag_id = EVPL.Diag_id";
			}
			if (!empty($data["LpuSectionViz_id"]) || !empty($data["LpuBuildingViz_id"])) {
				$queryWithAdditionalJoin[] = "left join v_LpuSection LS on LS.LpuSection_id = EVPL.LpuSection_id";
				if (!empty($data["LpuBuildingViz_id"])) {
					$queryWithAdditionalJoin[] = "left join LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id";
				}
			}
			if (!empty($data["DeseaseType_id"])) {
				if ($data["DeseaseType_id"] == 99) {
					$queryWithAdditionalWhere[] = "EVPL.DeseaseType_id is null";
				} else {
					$queryWithAdditionalWhere[] = "EVPL.DeseaseType_id = :DeseaseType_id";
					$queryParams["DeseaseType_id"] = $data["DeseaseType_id"];
				}
			}
			if (!empty($data["Diag_Code_From"])) {
				$queryWithAdditionalWhere[] = "D.Diag_Code >= :Diag_Code_From";
				$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
			}
			if (!empty($data["Diag_Code_To"])) {
				$queryWithAdditionalWhere[] = "D.Diag_Code <= :Diag_Code_To";
				$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
			}
			if (!empty($data["LpuSectionViz_id"])) {
				$queryWithAdditionalWhere[] = "EVPL.LpuSection_id = :LpuSectionViz_id";
				$queryParams["LpuSectionViz_id"] = $data["LpuSectionViz_id"];
			} elseif (!empty($data["LpuBuildingViz_id"])) {
				$queryWithAdditionalWhere[] = "LU.LpuBuilding_id = :LpuBuildingViz_id";
				$queryParams["LpuBuildingViz_id"] = $data["LpuBuildingViz_id"];
			}
			if (!empty($data["MedStaffFactViz_id"])) {
				$queryWithAdditionalWhere[] = "EVPL.MedStaffFact_id = :MedStaffFactViz_id";
				$queryParams["MedStaffFactViz_id"] = $data["MedStaffFactViz_id"];
			}
			if (!empty($data["MedPersonalViz_id"])) {
				$queryWithAdditionalWhere[] = "EVPL.MedPersonal_id = :MedPersonalViz_id";
				$queryParams["MedPersonalViz_id"] = $data["MedPersonalViz_id"];
			}
			if (!empty($data["MedPersonalViz_sid"])) {
				$queryWithAdditionalWhere[] = "EVPL.MedPersonal_sid = :MedPersonalViz_sid";
				$queryParams["MedPersonalViz_sid"] = $data["MedPersonalViz_sid"];
			}
			if (!empty($data["HealthKind_id"])) {
				$queryWithAdditionalWhere[] = "EVPL.HealthKind_id = :HealthKind_id";
				$queryParams["HealthKind_id"] = $data["HealthKind_id"];
			}
			if (!empty($data["PayType_id"])) {
				$queryWithAdditionalWhere[] = "EVPL.PayType_id = :PayType_id";
				$queryParams["PayType_id"] = $data["PayType_id"];
			}
			if (!empty($data["ServiceType_id"])) {
				$queryWithAdditionalWhere[] = "EVPL.ServiceType_id = :ServiceType_id";
				$queryParams["ServiceType_id"] = $data["ServiceType_id"];
			}
			if (!empty($data["Vizit_Date_Range"][0])) {
				$queryWithAdditionalWhere[] = "EVPL.EvnVizitPL_setDT::date >= :Vizit_Date_Range_0";
				$queryParams["Vizit_Date_Range_0"] = $data["Vizit_Date_Range"][0];
			}
			if (!empty($data["Vizit_Date_Range"][1])) {
				$queryWithAdditionalWhere[] = "EVPL.EvnVizitPL_setDT::date <= :Vizit_Date_Range_1";
				$queryParams["Vizit_Date_Range_1"] = $data["Vizit_Date_Range"][1];
			}
			if (($data["SearchFormType"] == "EvnPL") && (!empty($data["VizitClass_id"]))) {
				$queryWithAdditionalWhere[] = "EVPL.VizitClass_id = :VizitClass_id";
				$queryParams["VizitClass_id"] = $data["VizitClass_id"];
			}
			if (!empty($data["VizitType_id"])) {
				$queryWithAdditionalWhere[] = "EVPL.VizitType_id = :VizitType_id";
				$queryParams["VizitType_id"] = $data["VizitType_id"];
			}
			$queryWithAdditionalJoinString = implode(" ", $queryWithAdditionalJoin);
			$queryWithAdditionalWhereString = implode(" and ", $queryWithAdditionalWhere);
			$queryWithArray[] = "
				EvnVizitTmp (EvnVizitPL_id, EvnVizitPL_pid) as (
					select
						EVPL.EvnVizitPL_id,
						EVPL.EvnVizitPL_pid
					from
						v_EvnVizitPL EVPL
						{$queryWithAdditionalJoinString}
					where {$queryWithAdditionalWhereString}
				)
			";
			$filter .= ($data["SearchFormType"] == "EvnVizitPL")
				? " and exists (select EvnVizitPL_id from EvnVizitTmp where EvnVizitPL_id = EVizitPL.EvnVizitPL_id limit 1) "
				: " and exists (select EvnVizitPL_pid from EvnVizitTmp where EvnVizitPL_pid = EPL.EvnPL_id limit 1) ";
			unset($queryWithAdditionalJoin);
			unset($queryWithAdditionalJoinString);
			unset($queryWithAdditionalWhere);
			unset($queryWithAdditionalWhereString);
		}
		if (!empty($data["EvnVizitPL_isPaid"])) {
			if (in_array($data["SearchFormType"], ["EvnVizitPL", "EvnPL"])) {
				$regionNick = $callObject->getRegionNick();
				if (in_array($regionNick, ["perm", "astra", "kareliya", "pskov"])) {
					$filter .= " and coalesce(LEVPL.EvnVizitPL_isPaid, 1) = :EvnVizitPL_isPaid";
				}
				if (in_array($regionNick, ["samara", "penza", "ufa", "vologda"])) {
					$filter .= " and coalesce(LEVPLpaid.EvnVizitPL_isPaid, 1) = :EvnVizitPL_isPaid";
				}
				if (in_array($regionNick, ["khak", "ekb", "buryatiya"])) {
					$filter .= " and coalesce(EPL.EvnPL_isPaid, 1) = :EvnVizitPL_isPaid";
				}
			}
			$queryParams["EvnVizitPL_isPaid"] = $data["EvnVizitPL_isPaid"];
		}
		if (isset($data["PrehospTrauma_id"])) {
			$filter .= " and EPL.PrehospTrauma_id = :PrehospTrauma_id";
			$queryParams["PrehospTrauma_id"] = $data["PrehospTrauma_id"];
		}
		if (isset($data["PL_NumDirection"])) {
			$filter .= " and EPL.EvnDirection_Num = :PL_NumDirection";
			$queryParams["PL_NumDirection"] = $data["PL_NumDirection"];
		}
		if (isset($data["PL_DirectionDate"])) {
			$filter .= " and EPL.EvnDirection_setDT = :PL_DirectionDate";
			$queryParams["PL_DirectionDate"] = $data["PL_DirectionDate"];
		}
		if (isset($data["PL_ElDirection"]) && $data["PL_ElDirection"] == "on") {
			$filter .= " and EPL.EvnDirection_id is null";
		}
		if (isset($data["PL_Org_id"])) {
			$filter .= ($data["SearchFormType"] == "EvnPL" && $dbf !== true)
				? " and (EPL.Org_did = :PL_Org_id or dlpu.Org_id = :PL_Org_id)"
				: " and EPL.Org_did = :PL_Org_id";
			$queryParams["PL_Org_id"] = $data["PL_Org_id"];
		}
		if (isset($data["PL_LpuSection_id"])) {
			$filter .= " and EPL.LpuSection_did = :PL_LpuSection_id";
			$queryParams["PL_LpuSection_id"] = $data["PL_LpuSection_id"];
		}
		if (isset($data["PL_Diag_id"])) {
			$filter .= " and EPL.Diag_did = :PL_Diag_id";
			$queryParams["PL_Diag_id"] = $data["PL_Diag_id"];
		}
		if (isset($data["PL_PrehospDirect_id"])) {
			$filter .= ($data["PL_PrehospDirect_id"] == 99)
				? " and EPL.PrehospDirect_id is null"
				: " and EPL.PrehospDirect_id = :PL_PrehospDirect_id";
			if ($data["PL_PrehospDirect_id"] != 99) {
				$queryParams["PL_PrehospDirect_id"] = $data["PL_PrehospDirect_id"];
			}
		}
		if (isset($data["DirectClass_id"])) {
			$filter .= " and EPL.DirectClass_id = :DirectClass_id";
			$queryParams["DirectClass_id"] = $data["DirectClass_id"];
		}
		if (isset($data["DirectType_id"])) {
			$filter .= " and EPL.DirectType_id = :DirectType_id";
			$queryParams["DirectType_id"] = $data["DirectType_id"];
		}
		if (isset($data["EvnPL_IsFinish"])) {
			$filter .= " and EvnPLBase.EvnPLBase_IsFinish = :EvnPL_IsFinish";
			$queryParams["EvnPL_IsFinish"] = $data["EvnPL_IsFinish"];
		}
		if (isset($data["Lpu_oid"])) {
			$filter .= " and EPL.Lpu_oid = :Lpu_oid";
			$queryParams["Lpu_oid"] = $data["Lpu_oid"];
		}
		if (isset($data["LpuSection_oid"])) {
			$filter .= " and EPL.LpuSection_oid = :LpuSection_oid";
			$queryParams["LpuSection_oid"] = $data["LpuSection_oid"];
		}
		if (isset($data["ResultClass_id"])) {
			$filter .= " and EPL.ResultClass_id = :ResultClass_id";
			$queryParams["ResultClass_id"] = $data["ResultClass_id"];
		}
		if (isset($data["StickCause_id"]) || isset($data["StickType_id"]) ||
			isset($data["EvnStick_begDate_Range"][0]) || isset($data["EvnStick_begDate_Range"][1]) ||
			isset($data["EvnStick_endDate_Range"][0]) || isset($data["EvnStick_endDate_Range"][1])
		) {
			$evn_stick_filter = "";
			if (isset($data["EvnStick_begDate_Range"][0])) {
				$evn_stick_filter .= " and ESB.EvnStickBase_setDT >= :EvnStick_begDate_Range_0";
				$queryParams["EvnStick_begDate_Range_0"] = $data["EvnStick_begDate_Range"][0];
			}
			if (isset($data["EvnStick_begDate_Range"][1])) {
				$evn_stick_filter .= " and ESB.EvnStickBase_setDT <= :EvnStick_begDate_Range_1";
				$queryParams["EvnStick_begDate_Range_1"] = $data["EvnStick_begDate_Range"][1];
			}
			if (isset($data["EvnStick_endDate_Range"][0])) {
				$evn_stick_filter .= "
					and (
						case
							when ESB.StickType_id = 1 and ESB.EvnStickBase_disDT >= :EvnStick_endDate_Range_0 then 1
							when ESB.StickType_id = 2 and exists (
								select EvnStickWorkRelease_id
								from v_EvnStickWorkRelease
								where EvnStickBase_id = ESB.EvnStickBase_id and EvnStickWorkRelease_endDT >= :EvnStick_endDate_Range_0
							) then 1
							else 0
						end = 1
					)
				";
				$queryParams["EvnStick_endDate_Range_0"] = $data["EvnStick_endDate_Range"][0];
			}
			if (isset($data["EvnStick_endDate_Range"][1])) {
				$evn_stick_filter .= "
					and (
						case
							when ESB.StickType_id = 1 and ESB.EvnStickBase_disDT <= :EvnStick_endDate_Range_1 then 1
							when ESB.StickType_id = 2 and exists (
								select EvnStickWorkRelease_id
								from v_EvnStickWorkRelease
								where EvnStickBase_id = ESB.EvnStickBase_id and EvnStickWorkRelease_endDT <= :EvnStick_endDate_Range_1
							) then 1
							else 0
						end = 1
					)
				";
				$queryParams["EvnStick_endDate_Range_1"] = $data["EvnStick_endDate_Range"][1];
			}
			if (isset($data["StickCause_id"])) {
				$evn_stick_filter .= " and ESB.StickCause_id = :StickCause_id";
				$queryParams["StickCause_id"] = $data["StickCause_id"];
			}
			if (isset($data["StickType_id"])) {
				$evn_stick_filter .= " and ESB.StickType_id = :StickType_id";
				$queryParams["StickType_id"] = $data["StickType_id"];
			}
			$filter .= "
				and exists (
					select EvnStickBase_id
					from v_EvnStickBase ESB
					where ESB.EvnStickBase_mid = EPL.EvnPL_id
					  {$evn_stick_filter}
					union all
					select Evn_id as EvnStickBase_id
					from
						v_EvnLink EL
						inner join v_EvnStickBase ESB on ESB.EvnStickBase_id = EL.Evn_lid
					where EL.Evn_id = EPL.EvnPL_id
					  {$evn_stick_filter}
				)
			";
		}
	}

	public static function selectParams_EvnPLDispTeenInspectionPred(&$data, &$filter, &$queryParams)
	{
		if (isset($data["EvnPLDispTeenInspection_setDate"])) {
			$filter .= " and EPLDTI.EvnPLDispTeenInspection_setDate = :EvnPLDispTeenInspection_setDate::timestamp ";
			$queryParams["EvnPLDispTeenInspection_setDate"] = $data["EvnPLDispTeenInspection_setDate"];
		}
		if (isset($data["EvnPLDispTeenInspection_setDate_Range"][0])) {
			$filter .= " and EPLDTI.EvnPLDispTeenInspection_setDate >= :EvnPLDispTeenInspection_setDate_Range_0::timestamp ";
			$queryParams["EvnPLDispTeenInspection_setDate_Range_0"] = $data["EvnPLDispTeenInspection_setDate_Range"][0];
		}
		if (isset($data["EvnPLDispTeenInspection_setDate_Range"][1])) {
			$filter .= " and EPLDTI.EvnPLDispTeenInspection_setDate <= :EvnPLDispTeenInspection_setDate_Range_1::timestamp ";
			$queryParams["EvnPLDispTeenInspection_setDate_Range_1"] = $data["EvnPLDispTeenInspection_setDate_Range"][1];
		}
		if (isset($data["EvnPLDispTeenInspection_HealthKind_id"])) {
			$filter .= " and HK.HealthKind_id = :EvnPLDispTeenInspection_HealthKind_id ";
			$queryParams["EvnPLDispTeenInspection_HealthKind_id"] = $data["EvnPLDispTeenInspection_HealthKind_id"];
		}
		if (isset($data["EvnPLDispTeenInspection_isPaid"])) {
			if ($data['session']['region']['nick'] == 'ufa') {
				if ($data["EvnPLDispTeenInspection_isPaid"] == 2) {
					$filter .= "
						and exists (
							select EvnPLDispTeenInspection_id
							from v_EvnPLDispTeenInspection t1
							where t1.EvnPLDispTeenInspection_pid = EPLDTI.EvnPLDispTeenInspection_id and coalesce(t1.EvnPLDispTeenInspection_IsPaid, 1) = 2
							limit 1 )";
				} else if ($data["EvnPLDispTeenInspection_isPaid"] == 1) {
					$filter .= "
						and case when coalesce(EPLDO.EvnPLDispTeenInspection_VizitCount, 0) = 0
								then 0
								else (
									select count(EvnPLDispTeenInspection_id)
									from v_EvnPLDispTeenInspection t1
									where t1.EvnPLDispTeenInspection_pid = EPLDTI.EvnPLDispTeenInspection_id and coalesce(t1.EvnPLDispTeenInspection_IsPaid, 1) = 2
								)
							end = 0 ";
				}
			} else {
				$filter .= " and coalesce(EPLDTI.EvnPLDispTeenInspection_isPaid, 1) = :EvnPLDispTeenInspection_isPaid ";
				$queryParams["EvnPLDispTeenInspection_isPaid"] = $data["EvnPLDispTeenInspection_isPaid"];
			}
		}
		if (isset($data["EvnPLDispTeenInspection_disDate"])) {
			$filter .= " and EPLDTI.EvnPLDispTeenInspection_disDate = :EvnPLDispTeenInspection_disDate::timestamp ";
			$queryParams["EvnPLDispTeenInspection_disDate"] = $data["EvnPLDispTeenInspection_disDate"];
		}
		if (isset($data["EvnPLDispTeenInspection_disDate_Range"][0])) {
			$filter .= " and EPLDTI.EvnPLDispTeenInspection_disDate >= :EvnPLDispTeenInspection_disDate_Range_0::timestamp ";
			$queryParams["EvnPLDispTeenInspection_disDate_Range_0"] = $data["EvnPLDispTeenInspection_disDate_Range"][0];
		}
		if (isset($data["EvnPLDispTeenInspection_disDate_Range"][1])) {
			$filter .= " and EPLDTI.EvnPLDispTeenInspection_disDate <= :EvnPLDispTeenInspection_disDate_Range_1::timestamp ";
			$queryParams["EvnPLDispTeenInspection_disDate_Range_1"] = $data["EvnPLDispTeenInspection_disDate_Range"][1];
		}
		if (isset($data["EvnPLDispTeenInspection_IsFinish"])) {
			$filter .= " and coalesce(EPLDTI.EvnPLDispTeenInspection_IsFinish, 1) = :EvnPLDispTeenInspection_IsFinish ";
			$queryParams["EvnPLDispTeenInspection_IsFinish"] = $data["EvnPLDispTeenInspection_IsFinish"];
		}
		if (isset($data["EvnPLDispTeenInspection_IsRefusal"])) {
			$filter .= " and EPLDTI.EvnPLDispTeenInspection_IsRefusal = :EvnPLDispTeenInspection_IsRefusal ";
			$queryParams["EvnPLDispTeenInspection_IsRefusal"] = $data["EvnPLDispTeenInspection_IsRefusal"];
		}
		if (isset($data["EvnPLDispTeenInspection_isMobile"])) {
			$filter .= " and coalesce(EPLDTI.EvnPLDispTeenInspection_isMobile, 1) = :EvnPLDispTeenInspection_isMobile ";
			$queryParams["EvnPLDispTeenInspection_isMobile"] = $data["EvnPLDispTeenInspection_isMobile"];
		}
		if (isset($data["EvnPLDispTeenInspection_IsTwoStage"])) {
			$filter .= " and coalesce(EPLDTI.EvnPLDispTeenInspection_IsTwoStage, 1) = :EvnPLDispTeenInspection_IsTwoStage ";
			$queryParams["EvnPLDispTeenInspection_IsTwoStage"] = $data["EvnPLDispTeenInspection_IsTwoStage"];
		}
		if (isset($data["AgeGroupDisp_id"])) {
			$filter .= " and coalesce(EPLDTI.AgeGroupDisp_id, 0) = :AgeGroupDisp_id ";
			$queryParams["AgeGroupDisp_id"] = $data["AgeGroupDisp_id"];
		}
		if (isset($data["DispClass_id"])) {
			$filter .= " and coalesce(EPLDTI.DispClass_id, 6) = :DispClass_id ";
			$queryParams["DispClass_id"] = $data["DispClass_id"];
		}
		if (isset($data["HealthGroupType_id"])) {
			$filter .= " and AH.HealthGroupType_id = :HealthGroupType_id ";
			$queryParams["HealthGroupType_id"] = $data["HealthGroupType_id"];
		}
		if (isset($data["HealthGroupType_oid"])) {
			$filter .= " and AH.HealthGroupType_oid = :HealthGroupType_oid ";
			$queryParams["HealthGroupType_oid"] = $data["HealthGroupType_oid"];
		}
		$queryParams["Lpu_id"] = $data["Lpu_id"];
		if (isset($data["EvnPLDisp_UslugaComplex"]) && $data["EvnPLDisp_UslugaComplex"] > 0) {
			$filter .= "
				and exists (
					select UslugaComplex_id
					from v_EvnUslugaDispDop
					where EvnUslugaDispDop_didDate is not null
					  and UslugaComplex_id = :EvnPLDisp_UslugaComplex
					  and EvnUslugaDispDop_rid = EPLDTI.EvnPLDispTeenInspection_id
					limit 1
				)
			";
			$queryParams["EvnPLDisp_UslugaComplex"] = $data["EvnPLDisp_UslugaComplex"];
		}
		if (!empty($data["Disp_MedStaffFact_id"]) || !empty($data["Disp_LpuSection_id"]) || !empty($data["Disp_LpuBuilding_id"])) {
			$disp_msf = $disp_msf2 = $disp_ls = $disp_ls2 = $disp_b = $disp_b2 = $join1 = $join2 = "";
			if (!empty($data["Disp_MedStaffFact_id"])) {
				$disp_msf = " and msf1.MedStaffFact_id = :MedStaffFact_id";
				$disp_msf2 = " and msf2.MedStaffFact_id = :MedStaffFact_id";
				$queryParams["MedStaffFact_id"] = $data["Disp_MedStaffFact_id"];
			} else {
				if (!empty($data["Disp_LpuSection_id"])) {
					$disp_ls = " and msf1.LpuSection_id = :LpuSection_id";
					$disp_ls2 = " and msf2.LpuSection_uid = :LpuSection_id";
					$queryParams["LpuSection_id"] = $data["Disp_LpuSection_id"];
				}
				if (!empty($data["Disp_LpuBuilding_id"])) {
					$join1 = " left join v_LpuSection ls1 on ls1.LpuSection_id = msf1.LpuSection_id ";
					$disp_b = " and ls1.LpuBuilding_id = :LpuBuilding_id";
					$join2 = " left join v_LpuSection ls3 on ls3.LpuSection_id = msf2.LpuSection_uid ";
					$disp_b2 = " and ls3.LpuBuilding_id = :LpuBuilding_id";
					$queryParams["LpuBuilding_id"] = $data["Disp_LpuBuilding_id"];
				}
			}
			$filter .= " 
				and (
					exists (
						select msf1.EvnVizitDispDop_id 
						from
							v_EvnVizitDispDop msf1 
							{$join1}
						where msf1.EvnVizitDispDop_pid = EPLDTI.EvnPLDispTeenInspection_id {$disp_b} {$disp_msf} {$disp_ls}
						limit 1
					) or
					exists (
						select msf2.EvnUslugaDispDop_id 
						from
							v_EvnUslugaDispDop msf2
							{$join2} 
						where msf2.EvnUslugaDispDop_pid = EPLDTI.EvnPLDispTeenInspection_id {$disp_b2} {$disp_msf2} {$disp_ls2}
						limit 1
					)
				)
			";
		}
		$filter .= " and dbo.Age(PS.Person_BirthDay, (select dt from mv)) <= 18";
		if (in_array($data["session"]["region"]["nick"], ["buryatiya", "krym"])) {
			if (!empty($data["UslugaComplex_id"])) {
				$filter .= " and euddvizit.UslugaComplex_id = :UslugaComplex_id ";
				$queryParams["UslugaComplex_id"] = $data["UslugaComplex_id"];
			}
		}
	}

	public static function selectParams_EvnPLDispOrpSec(&$data, &$filter, &$queryParams)
	{
		if (isset($data["EvnPLDispOrp_setDate"])) {
			$filter .= " and EPLDO.EvnPLDispOrp_setDate = :EvnPLDispOrp_setDate::timestamp ";
			$queryParams["EvnPLDispOrp_setDate"] = $data["EvnPLDispOrp_setDate"];
		}
		if (isset($data["EvnPLDispOrp_setDate_Range"][0])) {
			$filter .= " and EPLDO.EvnPLDispOrp_setDate >= :EvnPLDispOrp_setDate_Range_0::timestamp ";
			$queryParams["EvnPLDispOrp_setDate_Range_0"] = $data["EvnPLDispOrp_setDate_Range"][0];
		}
		if (isset($data["EvnPLDispOrp_setDate_Range"][1])) {
			$filter .= " and EPLDO.EvnPLDispOrp_setDate <= :EvnPLDispOrp_setDate_Range_1::timestamp ";
			$queryParams["EvnPLDispOrp_setDate_Range_1"] = $data["EvnPLDispOrp_setDate_Range"][1];
		}
		if (isset($data["EvnPLDispOrp_disDate"])) {
			$filter .= " and EPLDO.EvnPLDispOrp_disDate = :EvnPLDispOrp_disDate::timestamp ";
			$queryParams["EvnPLDispOrp_disDate"] = $data["EvnPLDispOrp_disDate"];
		}
		if (isset($data["EvnPLDispOrp_disDate_Range"][0])) {
			$filter .= " and EPLDO.EvnPLDispOrp_disDate >= :EvnPLDispOrp_disDate_Range_0::timestamp ";
			$queryParams["EvnPLDispOrp_disDate_Range_0"] = $data["EvnPLDispOrp_disDate_Range"][0];
		}
		if (isset($data["EvnPLDispOrp_disDate_Range"][1])) {
			$filter .= " and EPLDO.EvnPLDispOrp_disDate <= :EvnPLDispOrp_disDate_Range_1::timestamp ";
			$queryParams["EvnPLDispOrp_disDate_Range_1"] = $data["EvnPLDispOrp_disDate_Range"][1];
		}
		if (isset($data["EvnPLDispOrp_VizitCount"])) {
			$filter .= " and EPLDO.EvnPLDispOrp_VizitCount = :EvnPLDispOrp_VizitCount ";
			$queryParams["EvnPLDispOrp_VizitCount"] = $data["EvnPLDispOrp_VizitCount"];
		}
		if (isset($data["EvnPLDispOrp_VizitCount_From"])) {
			$filter .= " and EPLDO.EvnPLDispOrp_VizitCount >= :EvnPLDispOrp_VizitCount_From ";
			$queryParams["EvnPLDispOrp_VizitCount_From"] = $data["EvnPLDispOrp_VizitCount_From"];
		}
		if (isset($data["EvnPLDispOrp_VizitCount_To"])) {
			$filter .= " and EPLDO.EvnPLDispOrp_VizitCount <= :EvnPLDispOrp_VizitCount_To ";
			$queryParams["EvnPLDispOrp_VizitCount_To"] = $data["EvnPLDispOrp_VizitCount_To"];
		}
		if (isset($data["EvnPLDispOrp_IsFinish"])) {
			$filter .= " and coalesce(EPLDO.EvnPLDispOrp_IsFinish, 1) = :EvnPLDispOrp_IsFinish ";
			$queryParams["EvnPLDispOrp_IsFinish"] = $data["EvnPLDispOrp_IsFinish"];
		}
		if (isset($data["EvnPLDispOrp_isPaid"])) {
			$filter .= " and coalesce(EPLDO.EvnPLDispOrp_isPaid, 1) = :EvnPLDispOrp_isPaid ";
			$queryParams["EvnPLDispOrp_isPaid"] = $data["EvnPLDispOrp_isPaid"];
		}
		if (isset($data["EvnPLDispOrp_HealthKind_id"])) {
			$filter .= " and HK.HealthKind_id = :EvnPLDispOrp_HealthKind_id ";
			$queryParams["EvnPLDispOrp_HealthKind_id"] = $data["EvnPLDispOrp_HealthKind_id"];
		}
		if (isset($data["EvnPLDispOrp_ChildStatusType_id"])) {
			$filter .= " and EPLDO.ChildStatusType_id = :EvnPLDispOrp_ChildStatusType_id ";
			$queryParams["EvnPLDispOrp_ChildStatusType_id"] = $data["EvnPLDispOrp_ChildStatusType_id"];
		}
		if (isset($data["EvnPLDispOrp_IsTwoStage"])) {
			$filter .= " and coalesce(EPLDO.EvnPLDispOrp_IsTwoStage, 1) = :EvnPLDispOrp_IsTwoStage ";
			$queryParams["EvnPLDispOrp_IsTwoStage"] = $data["EvnPLDispOrp_IsTwoStage"];
		}
		$queryParams["Lpu_id"] = $data["Lpu_id"];
		if (in_array($data["session"]["region"]["nick"], ["buryatiya", "krym"])) {
			if (!empty($data["UslugaComplex_id"])) {
				$filter .= " and euddvizit.UslugaComplex_id = :UslugaComplex_id ";
				$queryParams["UslugaComplex_id"] = $data["UslugaComplex_id"];
			}
		}
	}

	public static function selectParams_EvnPLDispOrpOld(&$data, &$filter, &$queryParams)
	{
		if (isset($data["EvnPLDispOrp_setDate"])) {
			$filter .= " and EPLDO.EvnPLDispOrp_setDate = :EvnPLDispOrp_setDate::timestamp ";
			$queryParams["EvnPLDispOrp_setDate"] = $data["EvnPLDispOrp_setDate"];
		}
		if (isset($data["EvnPLDispOrp_setDate_Range"][0])) {
			$filter .= " and EPLDO.EvnPLDispOrp_setDate >= :EvnPLDispOrp_setDate_Range_0::timestamp ";
			$queryParams["EvnPLDispOrp_setDate_Range_0"] = $data["EvnPLDispOrp_setDate_Range"][0];
		}
		if (isset($data["EvnPLDispOrp_setDate_Range"][1])) {
			$filter .= " and EPLDO.EvnPLDispOrp_setDate <= :EvnPLDispOrp_setDate_Range_1::timestamp ";
			$queryParams["EvnPLDispOrp_setDate_Range_1"] = $data["EvnPLDispOrp_setDate_Range"][1];
		}
		if (isset($data["EvnPLDispOrp_disDate"])) {
			$filter .= " and EPLDO.EvnPLDispOrp_disDate = :EvnPLDispOrp_disDate::timestamp ";
			$queryParams["EvnPLDispOrp_disDate"] = $data["EvnPLDispOrp_disDate"];
		}
		if (isset($data["EvnPLDispOrp_disDate_Range"][0])) {
			$filter .= " and EPLDO.EvnPLDispOrp_disDate >= :EvnPLDispOrp_disDate_Range_0::timestamp ";
			$queryParams["EvnPLDispOrp_disDate_Range_0"] = $data["EvnPLDispOrp_disDate_Range"][0];
		}
		if (isset($data["EvnPLDispOrp_disDate_Range"][1])) {
			$filter .= " and EPLDO.EvnPLDispOrp_disDate <= :EvnPLDispOrp_disDate_Range_1::timestamp ";
			$queryParams["EvnPLDispOrp_disDate_Range_1"] = $data["EvnPLDispOrp_disDate_Range"][1];
		}
		if (isset($data["EvnPLDispOrp_VizitCount"])) {
			$filter .= " and EPLDO.EvnPLDispOrp_VizitCount = :EvnPLDispOrp_VizitCount ";
			$queryParams["EvnPLDispOrp_VizitCount"] = $data["EvnPLDispOrp_VizitCount"];
		}
		if (isset($data["EvnPLDispOrp_VizitCount_From"])) {
			$filter .= " and EPLDO.EvnPLDispOrp_VizitCount >= :EvnPLDispOrp_VizitCount_From ";
			$queryParams["EvnPLDispOrp_VizitCount_From"] = $data["EvnPLDispOrp_VizitCount_From"];
		}
		if (isset($data["EvnPLDispOrp_VizitCount_To"])) {
			$filter .= " and EPLDO.EvnPLDispOrp_VizitCount <= :EvnPLDispOrp_VizitCount_To ";
			$queryParams["EvnPLDispOrp_VizitCount_To"] = $data["EvnPLDispOrp_VizitCount_To"];
		}
		if (isset($data["EvnPLDispOrp_IsFinish"])) {
			$filter .= " and coalesce(EPLDO.EvnPLDispOrp_IsFinish, 1) = :EvnPLDispOrp_IsFinish ";
			$queryParams["EvnPLDispOrp_IsFinish"] = $data["EvnPLDispOrp_IsFinish"];
		}
		$queryParams["Lpu_id"] = $data["Lpu_id"];
		if (isset($data["EvnPLDispOrp_HealthKind_id"])) {
			$queryParams["EvnPLDispOrp_HealthKind_id"] = $data["EvnPLDispOrp_HealthKind_id"];
		}
	}

	public static function selectParams_EvnPLDispOrp(&$data, &$filter, &$queryParams)
	{
		if (isset($data["EvnPLDispOrp_setDate"])) {
			$filter .= " and EPLDO.EvnPLDispOrp_setDate = :EvnPLDispOrp_setDate::timestamp ";
			$queryParams["EvnPLDispOrp_setDate"] = $data["EvnPLDispOrp_setDate"];
		}
		if (isset($data["EvnPLDispOrp_setDate_Range"][0])) {
			$filter .= " and EPLDO.EvnPLDispOrp_setDate >= :EvnPLDispOrp_setDate_Range_0::timestamp ";
			$queryParams["EvnPLDispOrp_setDate_Range_0"] = $data["EvnPLDispOrp_setDate_Range"][0];
		}
		if (isset($data["EvnPLDispOrp_setDate_Range"][1])) {
			$filter .= " and EPLDO.EvnPLDispOrp_setDate <= :EvnPLDispOrp_setDate_Range_1::timestamp ";
			$queryParams["EvnPLDispOrp_setDate_Range_1"] = $data["EvnPLDispOrp_setDate_Range"][1];
		}
		if (isset($data["EvnPLDispOrp_disDate"])) {
			$filter .= " and EPLDO.EvnPLDispOrp_disDate = :EvnPLDispOrp_disDate::timestamp ";
			$queryParams["EvnPLDispOrp_disDate"] = $data["EvnPLDispOrp_disDate"];
		}
		if (isset($data["EvnPLDispOrp_disDate_Range"][0])) {
			$filter .= " and EPLDO.EvnPLDispOrp_disDate >= :EvnPLDispOrp_disDate_Range_0::timestamp ";
			$queryParams["EvnPLDispOrp_disDate_Range_0"] = $data["EvnPLDispOrp_disDate_Range"][0];
		}
		if (isset($data["EvnPLDispOrp_disDate_Range"][1])) {
			$filter .= " and EPLDO.EvnPLDispOrp_disDate <= :EvnPLDispOrp_disDate_Range_1::timestamp ";
			$queryParams["EvnPLDispOrp_disDate_Range_1"] = $data["EvnPLDispOrp_disDate_Range"][1];
		}
		if (isset($data["EvnPLDispOrp_VizitCount"])) {
			$filter .= " and EPLDO.EvnPLDispOrp_VizitCount = :EvnPLDispOrp_VizitCount ";
			$queryParams["EvnPLDispOrp_VizitCount"] = $data["EvnPLDispOrp_VizitCount"];
		}
		if (isset($data["EvnPLDispOrp_VizitCount_From"])) {
			$filter .= " and EPLDO.EvnPLDispOrp_VizitCount >= :EvnPLDispOrp_VizitCount_From ";
			$queryParams["EvnPLDispOrp_VizitCount_From"] = $data["EvnPLDispOrp_VizitCount_From"];
		}
		if (isset($data["EvnPLDispOrp_VizitCount_To"])) {
			$filter .= " and EPLDO.EvnPLDispOrp_VizitCount <= :EvnPLDispOrp_VizitCount_To ";
			$queryParams["EvnPLDispOrp_VizitCount_To"] = $data["EvnPLDispOrp_VizitCount_To"];
		}
		if (isset($data["EvnPLDispOrp_IsFinish"])) {
			$filter .= " and coalesce(EPLDO.EvnPLDispOrp_IsFinish, 1) = :EvnPLDispOrp_IsFinish ";
			$queryParams["EvnPLDispOrp_IsFinish"] = $data["EvnPLDispOrp_IsFinish"];
		}
		if (isset($data["EvnPLDispOrp_isPaid"])) {
			if ($data["session"]["region"]["nick"] == "ufa") {
				$filter .= ($data["EvnPLDispOrp_isPaid"] == 2)
					?"
						and exists (
							select EvnVizitDispOrp_id
							from v_EvnVizitDispOrp t1
							where t1.EvnVizitDispOrp_pid = EPLDO.EvnPLDispOrp_id and coalesce(t1.EvnVizitDispOrp_IsPaid, 1) = 2
							limit 1
						)
						and not exists (
							select EvnVizitDispOrp_id
							from v_EvnVizitDispOrp t1
							where t1.EvnVizitDispOrp_pid = EPLDO.EvnPLDispOrp_id and coalesce(t1.EvnVizitDispOrp_IsPaid, 1) = 1
							limit 1
						)
					"
					:"
						and case when coalesce(EPLDO.EvnPLDispOrp_VizitCount, 0) = 0
								then 1
								else (
									select count(EvnVizitDispOrp_id)
									from v_EvnVizitDispOrp t1
									where t1.EvnVizitDispOrp_pid = EPLDO.EvnPLDispOrp_id and coalesce(t1.EvnVizitDispOrp_IsPaid, 1) = 1
								)
							end > 0
					";
			} else {
				$filter .= " and coalesce(EPLDO.EvnPLDispOrp_isPaid, 1) = :EvnPLDispOrp_isPaid ";
				$queryParams['EvnPLDispOrp_isPaid'] = $data['EvnPLDispOrp_isPaid'];
			}
		}
		if (isset($data["EvnPLDispOrp_isMobile"])) {
			$filter .= " and coalesce(EPLDO.EvnPLDispOrp_isMobile, 1) = :EvnPLDispOrp_isMobile ";
			$queryParams["EvnPLDispOrp_isMobile"] = $data["EvnPLDispOrp_isMobile"];
		}
		if (!empty($data["EvnPLDispOrp_IsRefusal"])) {
			$filter .= " and EPLDO.EvnPLDispOrp_IsRefusal = :EvnPLDispOrp_IsRefusal ";
			$queryParams["EvnPLDispOrp_IsRefusal"] = $data["EvnPLDispOrp_IsRefusal"];
		}
		if (isset($data["EvnPLDispOrp_IsTwoStage"])) {
			$filter .= " and coalesce(EPLDO.EvnPLDispOrp_IsTwoStage, 1) = :EvnPLDispOrp_IsTwoStage ";
			$queryParams["EvnPLDispOrp_IsTwoStage"] = $data["EvnPLDispOrp_IsTwoStage"];
		}
		if (isset($data["EvnPLDispOrp_HealthKind_id"])) {
			$filter .= " and HK.HealthKind_id = :EvnPLDispOrp_HealthKind_id ";
			$queryParams["EvnPLDispOrp_HealthKind_id"] = $data["EvnPLDispOrp_HealthKind_id"];
		}
		if (isset($data["EvnPLDispOrp_ChildStatusType_id"])) {
			$filter .= " and EPLDO.ChildStatusType_id = :EvnPLDispOrp_ChildStatusType_id ";
			$queryParams["EvnPLDispOrp_ChildStatusType_id"] = $data["EvnPLDispOrp_ChildStatusType_id"];
		}
		$queryParams["Lpu_id"] = $data["Lpu_id"];
		if (isset($data["EvnPLDisp_UslugaComplex"]) && $data["EvnPLDisp_UslugaComplex"] > 0) {
			$queryParams["EvnPLDisp_UslugaComplex"] = $data["EvnPLDisp_UslugaComplex"];
		}
		if (!empty($data["Disp_MedStaffFact_id"]) || !empty($data["Disp_LpuSection_id"]) || !empty($data["Disp_LpuBuilding_id"])) {
			$disp_msf = $disp_msf2 = $disp_ls = $disp_ls2 = $disp_b = $disp_b2 = $join1 = $join2 = "";
			if (!empty($data["Disp_MedStaffFact_id"])) {
				$disp_msf = " and msf1.MedStaffFact_id = :MedStaffFact_id";
				$disp_msf2 = " and msf2.MedStaffFact_id = :MedStaffFact_id";
				$queryParams["MedStaffFact_id"] = $data["Disp_MedStaffFact_id"];
			} else {
				if (!empty($data["Disp_LpuSection_id"])) {
					$disp_ls = " and msf1.LpuSection_id = :LpuSection_id";
					$disp_ls2 = " and msf2.LpuSection_uid = :LpuSection_id";
					$queryParams["LpuSection_id"] = $data["Disp_LpuSection_id"];
				}
				if (!empty($data["Disp_LpuBuilding_id"])) {
					$join1 = " left join v_LpuSection ls1 on ls1.LpuSection_id = msf1.LpuSection_id ";
					$disp_b = " and ls1.LpuBuilding_id = :LpuBuilding_id";
					$join2 = " left join v_LpuSection ls3 on ls3.LpuSection_id = msf2.LpuSection_uid ";
					$disp_b2 = " and ls3.LpuBuilding_id = :LpuBuilding_id";
					$queryParams["LpuBuilding_id"] = $data["Disp_LpuBuilding_id"];
				}
			}
			$filter .= " 
				and (
					exists (
						select msf1.EvnVizitDispOrp_id
						from
							v_EvnVizitDispOrp msf1 
							left join v_EvnUslugaDispOrp msf2 on msf2.EvnUslugaDispOrp_pid = msf1.EvnVizitDispOrp_id
							{$join1}
						where msf1.EvnVizitDispOrp_pid = EPLDO.EvnPLDispOrp_id {$disp_b} {$disp_msf} {$disp_ls}
						limit 1
					) or
					exists (
						select msf2.EvnUslugaDispOrp_id
						from
							v_EvnUslugaDispOrp msf2
							{$join2} 
						where msf2.EvnUslugaDispOrp_pid = EPLDO.EvnPLDispOrp_id {$disp_b2} {$disp_msf2} {$disp_ls2}
						limit 1
					)
				)
			";
		}
		if (in_array($data["session"]["region"]["nick"], ["buryatiya", "krym"])) {
			if (!empty($data["UslugaComplex_id"])) {
				$filter .= " and euddvizit.UslugaComplex_id = :UslugaComplex_id ";
				$queryParams["UslugaComplex_id"] = $data["UslugaComplex_id"];
			}
		}
	}

	public static function selectParams_EvnPLDispTeen14(&$data, &$filter, &$queryParams)
	{
		if (isset($data["EvnPLDispTeen14_setDate"])) {
			$filter .= " and EPLDT14.EvnPLDispTeen14_setDate = :EvnPLDispTeen14_setDate::timestamp ";
			$queryParams["EvnPLDispTeen14_setDate"] = $data["EvnPLDispTeen14_setDate"];
		}
		if (isset($data["EvnPLDispTeen14_setDate_Range"][0])) {
			$filter .= " and EPLDT14.EvnPLDispTeen14_setDate >= :EvnPLDispTeen14_setDate_Range_0::timestamp ";
			$queryParams["EvnPLDispTeen14_setDate_Range_0"] = $data["EvnPLDispTeen14_setDate_Range"][0];
		}
		if (isset($data["EvnPLDispTeen14_setDate_Range"][1])) {
			$filter .= " and EPLDT14.EvnPLDispTeen14_setDate <= :EvnPLDispTeen14_setDate_Range_1::timestamp ";
			$queryParams["EvnPLDispTeen14_setDate_Range_1"] = $data["EvnPLDispTeen14_setDate_Range"][1];
		}
		if (isset($data["EvnPLDispTeen14_disDate"])) {
			$filter .= " and EPLDT14.EvnPLDispTeen14_disDate = :EvnPLDispTeen14_disDate::timestamp ";
			$queryParams["EvnPLDispTeen14_disDate"] = $data["EvnPLDispTeen14_disDate"];
		}
		if (isset($data["EvnPLDispTeen14_disDate_Range"][0])) {
			$filter .= " and EPLDT14.EvnPLDispTeen14_disDate >= :EvnPLDispTeen14_disDate_Range_0::timestamp ";
			$queryParams["EvnPLDispTeen14_disDate_Range_0"] = $data["EvnPLDispTeen14_disDate_Range"][0];
		}
		if (isset($data["EvnPLDispTeen14_disDate_Range"][1])) {
			$filter .= " and EPLDT14.EvnPLDispTeen14_disDate <= :EvnPLDispTeen14_disDate_Range_1::timestamp ";
			$queryParams["EvnPLDispTeen14_disDate_Range_1"] = $data["EvnPLDispTeen14_disDate_Range"][1];
		}
		if (isset($data["EvnPLDispTeen14_VizitCount"])) {
			$filter .= " and EPLDT14.EvnPLDispTeen14_VizitCount = :EvnPLDispTeen14_VizitCount ";
			$queryParams["EvnPLDispTeen14_VizitCount"] = $data["EvnPLDispTeen14_VizitCount"];
		}
		if (isset($data["EvnPLDispTeen14_VizitCount_From"])) {
			$filter .= " and EPLDT14.EvnPLDispTeen14_VizitCount >= :EvnPLDispTeen14_VizitCount_From ";
			$queryParams["EvnPLDispTeen14_VizitCount_From"] = $data["EvnPLDispTeen14_VizitCount_From"];
		}
		if (isset($data["EvnPLDispTeen14_VizitCount_To"])) {
			$filter .= " and EPLDT14.EvnPLDispTeen14_VizitCount <= :EvnPLDispTeen14_VizitCount_To ";
			$queryParams["EvnPLDispTeen14_VizitCount_To"] = $data["EvnPLDispTeen14_VizitCount_To"];
		}
		if (isset($data["EvnPLDispTeen14_IsFinish"])) {
			$filter .= " and coalesce(EPLDT14.EvnPLDispTeen14_IsFinish, 1) = :EvnPLDispTeen14_IsFinish ";
			$queryParams["EvnPLDispTeen14_IsFinish"] = $data["EvnPLDispTeen14_IsFinish"];
		}
		$queryParams["Lpu_id"] = $data["Lpu_id"];
		if (isset($data["EvnPLDispTeen14_HealthKind_id"])) {
			$queryParams["EvnPLDispTeen14_HealthKind_id"] = $data["EvnPLDispTeen14_HealthKind_id"];
		}
	}

	public static function selectParams_EvnPLDispDop(&$data, &$filter, &$queryParams)
	{
		if (isset($data["EvnPLDispDop_setDate"])) {
			$filter .= " and EPLDD.EvnPLDispDop_setDate = :EvnPLDispDop_setDate::timestamp ";
			$queryParams["EvnPLDispDop_setDate"] = $data["EvnPLDispDop_setDate"];
		}
		if (isset($data["EvnPLDispDop_setDate_Range"][0])) {
			$filter .= " and EPLDD.EvnPLDispDop_setDate >= :EvnPLDispDop_setDate_Range_0::timestamp ";
			$queryParams["EvnPLDispDop_setDate_Range_0"] = $data["EvnPLDispDop_setDate_Range"][0];
		}
		if (isset($data["EvnPLDispDop_setDate_Range"][1])) {
			$filter .= " and EPLDD.EvnPLDispDop_setDate <= :EvnPLDispDop_setDate_Range_1::timestamp ";
			$queryParams["EvnPLDispDop_setDate_Range_1"] = $data["EvnPLDispDop_setDate_Range"][1];
		}
		if (isset($data["EvnPLDispDop_disDate"])) {
			$filter .= " and EPLDD.EvnPLDispDop_disDate = :EvnPLDispDop_disDate::timestamp ";
			$queryParams["EvnPLDispDop_disDate"] = $data["EvnPLDispDop_disDate"];
		}
		if (isset($data["EvnPLDispDop_disDate_Range"][0])) {
			$filter .= " and EPLDD.EvnPLDispDop_disDate >= :EvnPLDispDop_disDate_Range_0::timestamp ";
			$queryParams["EvnPLDispDop_disDate_Range_0"] = $data["EvnPLDispDop_disDate_Range"][0];
		}
		if (isset($data["EvnPLDispDop_disDate_Range"][1])) {
			$filter .= " and EPLDD.EvnPLDispDop_disDate <= :EvnPLDispDop_disDate_Range_1::timestamp ";
			$queryParams["EvnPLDispDop_disDate_Range_1"] = $data["EvnPLDispDop_disDate_Range"][1];
		}
		if (isset($data["EvnPLDispDop_VizitCount"])) {
			$filter .= " and EPLDD.EvnPLDispDop_VizitCount = :EvnPLDispDop_VizitCount ";
			$queryParams["EvnPLDispDop_VizitCount"] = $data["EvnPLDispDop_VizitCount"];
		}
		if (isset($data["EvnPLDispDop_VizitCount_From"])) {
			$filter .= " and EPLDD.EvnPLDispDop_VizitCount >= :EvnPLDispDop_VizitCount_From ";
			$queryParams["EvnPLDispDop_VizitCount_From"] = $data["EvnPLDispDop_VizitCount_From"];
		}
		if (isset($data["EvnPLDispDop_VizitCount_To"])) {
			$filter .= " and EPLDD.EvnPLDispDop_VizitCount <= :EvnPLDispDop_VizitCount_To ";
			$queryParams["EvnPLDispDop_VizitCount_To"] = $data["EvnPLDispDop_VizitCount_To"];
		}
		if (isset($data["EvnPLDispDop_IsFinish"])) {
			$filter .= " and coalesce(EPLDD.EvnPLDispDop_IsFinish, 1) = :EvnPLDispDop_IsFinish ";
			$queryParams["EvnPLDispDop_IsFinish"] = $data["EvnPLDispDop_IsFinish"];
		}
		$queryParams["Lpu_id"] = $data["Lpu_id"];
		if (isset($data["EvnPLDispDop_HealthKind_id"])) {
			$queryParams["EvnPLDispDop_HealthKind_id"] = $data["EvnPLDispDop_HealthKind_id"];
		}
	}

	public static function selectParams_EvnPLDispScreenChild(&$data, &$filter, &$queryParams)
	{
		if (isset($data["EvnPLDispScreenChild_setDate"])) {
			$filter .= " and EPLDS.EvnPLDispScreenChild_setDate = cast(:EvnPLDispScreenChild_setDate as timestamp) ";
			$queryParams["EvnPLDispScreenChild_setDate"] = $data["EvnPLDispScreenChild_setDate"];
		}
		if (isset($data["EvnPLDispScreenChild_setDate_Range"][0])) {
			$filter .= " and EPLDS.EvnPLDispScreenChild_setDate >= cast(:EvnPLDispScreenChild_setDate_Range_0 as timestamp) ";
			$queryParams["EvnPLDispScreenChild_setDate_Range_0"] = $data["EvnPLDispScreenChild_setDate_Range"][0];
		}
		if (isset($data["EvnPLDispScreenChild_setDate_Range"][1])) {
			$filter .= " and EPLDS.EvnPLDispScreenChild_setDate <= cast(:EvnPLDispScreenChild_setDate_Range_1 as timestamp) ";
			$queryParams["EvnPLDispScreenChild_setDate_Range_1"] = $data["EvnPLDispScreenChild_setDate_Range"][1];
		}
		if (isset($data["EvnPLDispScreenChild_disDate"])) {
			$filter .= " and EPLDS.EvnPLDispScreenChild_disDate = cast(:EvnPLDispScreenChild_disDate as timestamp) ";
			$queryParams["EvnPLDispScreenChild_disDate"] = $data["EvnPLDispScreenChild_disDate"];
		}
		if (isset($data["EvnPLDispScreenChild_disDate_Range"][0])) {
			$filter .= " and EPLDS.EvnPLDispScreenChild_disDate >= cast(:EvnPLDispScreenChild_disDate_Range_0 as timestamp) ";
			$queryParams["EvnPLDispScreenChild_disDate_Range_0"] = $data["EvnPLDispScreenChild_disDate_Range"][0];
		}
		if (isset($data["EvnPLDispScreenChild_disDate_Range"][1])) {
			$filter .= " and EPLDS.EvnPLDispScreenChild_disDate <= cast(:EvnPLDispScreenChild_disDate_Range_1 as timestamp) ";
			$queryParams["EvnPLDispScreenChild_disDate_Range_1"] = $data["EvnPLDispScreenChild_disDate_Range"][1];
		}
		if (isset($data["EvnPLDispScreenChild_IsEndStage"])) {
			$filter .= " and coalesce(EPLDS.EvnPLDispScreenChild_IsEndStage, 1) = :EvnPLDispScreenChild_IsEndStage ";
			$queryParams["EvnPLDispScreenChild_IsEndStage"] = $data["EvnPLDispScreenChild_IsEndStage"];
		}
		if (isset($data["AgeGroupDisp_id"])) {
			$filter .= " and EPLDS.AgeGroupDisp_id = :AgeGroupDisp_id ";
			$queryParams["AgeGroupDisp_id"] = $data["AgeGroupDisp_id"];
		}
		$queryParams["PersonDopDisp_Year"] = (isset($data["PersonDopDisp_Year"])) ? $data["PersonDopDisp_Year"] : 2013;
		$queryParams["Lpu_id"] = $data["Lpu_id"];
	}

	public static function selectParams_EvnPLDispScreen(&$data, &$filter, &$queryParams)
	{
		if (isset($data["EvnPLDispScreen_setDate"])) {
			$filter .= " and EPLDS.EvnPLDispScreen_setDate = :EvnPLDispScreen_setDate::timestamp ";
			$queryParams["EvnPLDispScreen_setDate"] = $data["EvnPLDispScreen_setDate"];
		}
		if (isset($data["EvnPLDispScreen_setDate_Range"][0])) {
			$filter .= " and EPLDS.EvnPLDispScreen_setDate >= :EvnPLDispScreen_setDate_Range_0::timestamp ";
			$queryParams["EvnPLDispScreen_setDate_Range_0"] = $data["EvnPLDispScreen_setDate_Range"][0];
		}
		if (isset($data["EvnPLDispScreen_setDate_Range"][1])) {
			$filter .= " and EPLDS.EvnPLDispScreen_setDate <= :EvnPLDispScreen_setDate_Range_1::timestamp ";
			$queryParams["EvnPLDispScreen_setDate_Range_1"] = $data["EvnPLDispScreen_setDate_Range"][1];
		}
		if (isset($data["EvnPLDispScreen_disDate"])) {
			$filter .= " and EPLDS.EvnPLDispScreen_disDate = :EvnPLDispScreen_disDate::timestamp ";
			$queryParams["EvnPLDispScreen_disDate"] = $data["EvnPLDispScreen_disDate"];
		}
		if (isset($data["EvnPLDispScreen_disDate_Range"][0])) {
			$filter .= " and EPLDS.EvnPLDispScreen_disDate >= :EvnPLDispScreen_disDate_Range_0::timestamp ";
			$queryParams["EvnPLDispScreen_disDate_Range_0"] = $data["EvnPLDispScreen_disDate_Range"][0];
		}
		if (isset($data["EvnPLDispScreen_disDate_Range"][1])) {
			$filter .= " and EPLDS.EvnPLDispScreen_disDate <= :EvnPLDispScreen_disDate_Range_1::timestamp ";
			$queryParams["EvnPLDispScreen_disDate_Range_1"] = $data["EvnPLDispScreen_disDate_Range"][1];
		}
		if (isset($data["EvnPLDispScreen_IsEndStage"])) {
			$filter .= " and coalesce(EPLDS.EvnPLDispScreen_IsEndStage, 1) = :EvnPLDispScreen_IsEndStage ";
			$queryParams["EvnPLDispScreen_IsEndStage"] = $data["EvnPLDispScreen_IsEndStage"];
		}
		if (isset($data["AgeGroupDisp_id"])) {
			$filter .= " and EPLDS.AgeGroupDisp_id = :AgeGroupDisp_id ";
			$queryParams["AgeGroupDisp_id"] = $data["AgeGroupDisp_id"];
		}
		$queryParams["PersonDopDisp_Year"] = (isset($data["PersonDopDisp_Year"]))?$data["PersonDopDisp_Year"]:2013;
		$queryParams["Lpu_id"] = $data["Lpu_id"];
	}

	public static function selectParams_EvnPLDispProf(&$data, &$filter, &$queryParams)
	{
		if (isset($data["EvnPLDispProf_setDate"])) {
			$filter .= " and EPLDP.EvnPLDispProf_setDate = :EvnPLDispProf_setDate::timestamp ";
			$queryParams["EvnPLDispProf_setDate"] = $data["EvnPLDispProf_setDate"];
		}
		if (isset($data["EvnPLDispProf_setDate_Range"][0])) {
			$filter .= " and EPLDP.EvnPLDispProf_setDate >= :EvnPLDispProf_setDate_Range_0::timestamp ";
			$queryParams["EvnPLDispProf_setDate_Range_0"] = $data["EvnPLDispProf_setDate_Range"][0];
		}
		if (isset($data["EvnPLDispProf_setDate_Range"][1])) {
			$filter .= " and EPLDP.EvnPLDispProf_setDate <= :EvnPLDispProf_setDate_Range_1::timestamp ";
			$queryParams["EvnPLDispProf_setDate_Range_1"] = $data["EvnPLDispProf_setDate_Range"][1];
		}
		if (isset($data["EvnPLDispProf_disDate"])) {
			$filter .= " and EPLDP.EvnPLDispProf_disDate = :EvnPLDispProf_disDate::timestamp ";
			$queryParams["EvnPLDispProf_disDate"] = $data["EvnPLDispProf_disDate"];
		}
		if (isset($data["EvnPLDispProf_disDate_Range"][0])) {
			$filter .= " and EPLDP.EvnPLDispProf_disDate >= :EvnPLDispProf_disDate_Range_0::timestamp ";
			$queryParams["EvnPLDispProf_disDate_Range_0"] = $data["EvnPLDispProf_disDate_Range"][0];
		}
		if (isset($data["EvnPLDispProf_disDate_Range"][1])) {
			$filter .= " and EPLDP.EvnPLDispProf_disDate <= :EvnPLDispProf_disDate_Range_1::timestamp ";
			$queryParams["EvnPLDispProf_disDate_Range_1"] = $data["EvnPLDispProf_disDate_Range"][1];
		}
		if (isset($data["EvnPLDispProf_IsFinish"])) {
			$filter .= " and coalesce(EPLDP.EvnPLDispProf_IsEndStage, 1) = :EvnPLDispProf_IsFinish ";
			$queryParams["EvnPLDispProf_IsFinish"] = $data["EvnPLDispProf_IsFinish"];
		}
		if (isset($data["EvnPLDispProf_isPaid"])) {
			$filter .= " and coalesce(EPLDP.EvnPLDispProf_isPaid, 1) = :EvnPLDispProf_isPaid ";
			$queryParams["EvnPLDispProf_isPaid"] = $data["EvnPLDispProf_isPaid"];
		}
		if (isset($data["EvnPLDispProf_isMobile"])) {
			$filter .= " and coalesce(EPLDP.EvnPLDispProf_isMobile,1) = :EvnPLDispProf_isMobile ";
			$queryParams["EvnPLDispProf_isMobile"] = $data["EvnPLDispProf_isMobile"];
		}
		if (isset($data["EvnPLDispProf_IsRefusal"])) {
			$filter .= " and EPLDP.EvnPLDispProf_IsRefusal = :EvnPLDispProf_IsRefusal ";
			$queryParams["EvnPLDispProf_IsRefusal"] = $data["EvnPLDispProf_IsRefusal"];
		}
		if (isset($data["EvnPLDispProf_HealthKind_id"])) {
			$filter .= " and EPLDP.HealthKind_id = :EvnPLDispProf_HealthKind_id ";
			$queryParams["EvnPLDispProf_HealthKind_id"] = $data["EvnPLDispProf_HealthKind_id"];
		}
		$queryParams["PersonDopDisp_Year"] = (isset($data["PersonDopDisp_Year"]))?$data["PersonDopDisp_Year"]:2013; 
		$queryParams["Lpu_id"] = $data["Lpu_id"];
		if (isset($data["EvnPLDisp_UslugaComplex"]) && $data["EvnPLDisp_UslugaComplex"] > 0) {
			$filter .= "
				and exists (
					select UslugaComplex_id
					from v_EvnUslugaDispDop
					where EvnUslugaDispDop_didDate is not null
					  and UslugaComplex_id = :EvnPLDisp_UslugaComplex
					  and EvnUslugaDispDop_rid = EPLDP.EvnPLDispProf_id
					limit 1
				)
			";
			$queryParams["EvnPLDisp_UslugaComplex"] = $data["EvnPLDisp_UslugaComplex"];
		}
		if (!empty($data["Disp_MedStaffFact_id"]) || !empty($data["Disp_LpuSection_id"]) || !empty($data["Disp_LpuBuilding_id"])) {
			if (!empty($data["Disp_MedStaffFact_id"])) {
				$queryParams["MedStaffFact_id"] = $data["Disp_MedStaffFact_id"];
			} else {
				if (!empty($data["Disp_LpuSection_id"])) {
					$queryParams["LpuSection_id"] = $data["Disp_LpuSection_id"];
				}
				if (!empty($data["Disp_LpuBuilding_id"])) {
					$queryParams["LpuBuilding_id"] = $data["Disp_LpuBuilding_id"];
				}
			}
			$filter .= " and (evapply.EvnVizitDispDop_id is not null or euapply.EvnUslugaDispDop_id is not null)";
		}
	}

	public static function selectParams_EvnPLDispDop13Sec(Search_model $callObject, &$data, &$filter, &$queryParams)
	{
		$getLpuIdFilter = $callObject->getLpuIdFilter($data);
		$filterEPLDD13 = "";
		if (isset($data["EvnPLDispDop13_setDate"])) {
			$filterEPLDD13 .= " and EPLDD13.EvnPLDispDop13_setDate = :EvnPLDispDop13_setDate::timestamp ";
			$queryParams["EvnPLDispDop13_setDate"] = $data["EvnPLDispDop13_setDate"];
		}
		if (isset($data["EvnPLDispDop13_setDate_Range"][0])) {
			$filterEPLDD13 .= " and EPLDD13.EvnPLDispDop13_setDate >= :EvnPLDispDop13_setDate_Range_0::timestamp ";
			$queryParams["EvnPLDispDop13_setDate_Range_0"] = $data["EvnPLDispDop13_setDate_Range"][0];
		}
		if (isset($data["EvnPLDispDop13_setDate_Range"][1])) {
			$filterEPLDD13 .= " and EPLDD13.EvnPLDispDop13_setDate <= :EvnPLDispDop13_setDate_Range_1::timestamp ";
			$queryParams["EvnPLDispDop13_setDate_Range_1"] = $data["EvnPLDispDop13_setDate_Range"][1];
		}
		if (isset($data["EvnPLDispDop13_disDate"])) {
			$filterEPLDD13 .= " and EPLDD13.EvnPLDispDop13_disDate = :EvnPLDispDop13_disDate::timestamp ";
			$queryParams["EvnPLDispDop13_disDate"] = $data["EvnPLDispDop13_disDate"];
		}
		if (isset($data["EvnPLDispDop13_disDate_Range"][0])) {
			$filterEPLDD13 .= " and EPLDD13.EvnPLDispDop13_disDate >= :EvnPLDispDop13_disDate_Range_0::timestamp ";
			$queryParams["EvnPLDispDop13_disDate_Range_0"] = $data["EvnPLDispDop13_disDate_Range"][0];
		}
		if (isset($data["EvnPLDispDop13_disDate_Range"][1])) {
			$filterEPLDD13 .= " and EPLDD13.EvnPLDispDop13_disDate <= :EvnPLDispDop13_disDate_Range_1::timestamp ";
			$queryParams["EvnPLDispDop13_disDate_Range_1"] = $data["EvnPLDispDop13_disDate_Range"][1];
		}
		if (isset($data["EvnPLDispDop13_IsFinish"])) {
			$filterEPLDD13 .= " and coalesce(EPLDD13.EvnPLDispDop13_IsEndStage, 1) = :EvnPLDispDop13_IsFinish ";
			$queryParams["EvnPLDispDop13_IsFinish"] = $data["EvnPLDispDop13_IsFinish"];
		}
		if (isset($data["EvnPLDispDop13_Cancel"])) {
			$data["DopDispInfoConsent_IsAgree"] = ($data["EvnPLDispDop13_Cancel"] == 2) ? 1 : 2;
			$queryParams["DopDispInfoConsent_IsAgree"] = $data["DopDispInfoConsent_IsAgree"];
		}
		if (isset($data["EvnPLDispDop13_IsTwoStage"])) {
			$filterEPLDD13 .= " and coalesce(EPLDD13.EvnPLDispDop13_IsTwoStage, 1) = :EvnPLDispDop13_IsTwoStage ";
			$queryParams["EvnPLDispDop13_IsTwoStage"] = $data["EvnPLDispDop13_IsTwoStage"];
		}
		if (isset($data["EvnPLDispDop13_HealthKind_id"])) {
			$filterEPLDD13 .= " and EPLDD13.HealthKind_id = :EvnPLDispDop13_HealthKind_id ";
			$queryParams["EvnPLDispDop13_HealthKind_id"] = $data["EvnPLDispDop13_HealthKind_id"];
		}
		if (isset($data["EvnPLDispDop13_isPaid"])) {
			if ($data["session"]["region"]["nick"] == "ufa") {
				$filter .= ($data["EvnPLDispDop13_isPaid"] == 2)
					? "
						and exists (
							select EvnVizitDispDop_id
							from v_EvnVizitDispDop t1
							where t1.EvnVizitDispDop_pid = EPLDD13.EvnPLDispDop13_id and coalesce(t1.EvnVizitDispDop_IsPaid, 1) = 2
							limit 1
						)
					"
					: "
						and not exists (
							select EvnVizitDispDop_id
							from v_EvnVizitDispDop t1
							where t1.EvnVizitDispDop_pid = EPLDD13.EvnPLDispDop13_id and coalesce(t1.EvnVizitDispDop_IsPaid, 1) = 2
							limit 1
						)
					";
			} else {
				$filterEPLDD13 .= " and coalesce(EPLDD13.EvnPLDispDop13_isPaid, 1) = :EvnPLDispDop13_isPaid ";
				$queryParams["EvnPLDispDop13_isPaid"] = $data["EvnPLDispDop13_isPaid"];
			}
		}
		if (isset($data["EvnPLDispDop13Second_isPaid"])) {
			if ($data["session"]["region"]["nick"] == "ufa") {
				$filter .= ($data["EvnPLDispDop13Second_isPaid"] == 2)
					? "
						and exists (
							select EvnVizitDispDop_id
							from v_EvnVizitDispDop t1
							where t1.EvnVizitDispDop_pid = DopDispSecond.EvnPLDispDop13_id and coalesce(t1.EvnVizitDispDop_IsPaid, 1) = 2
							limit 1
						)
					"
					: "
						and not exists (
							select EvnVizitDispDop_id
							from v_EvnVizitDispDop t1
							where t1.EvnVizitDispDop_pid = DopDispSecond.EvnPLDispDop13_id and coalesce(t1.EvnVizitDispDop_IsPaid, 1) = 2
							limit 1
						)
					";
			} else {
				$queryParams["EvnPLDispDop13Second_isPaid"] = $data["EvnPLDispDop13Second_isPaid"];
			}
		}
		if (isset($data["EvnPLDispDop13_isMobile"])) {
			$filterEPLDD13 .= " and coalesce(EPLDD13.EvnPLDispDop13_isMobile,1) = :EvnPLDispDop13_isMobile ";
			$queryParams["EvnPLDispDop13_isMobile"] = $data["EvnPLDispDop13_isMobile"];
		}
		if (isset($data["EvnPLDispDop13Second_isMobile"])) {
			$queryParams["EvnPLDispDop13Second_isMobile"] = $data["EvnPLDispDop13Second_isMobile"];
		}
		if (isset($data["EvnPLDispDop13Second_setDate"])) {
			$queryParams["EvnPLDispDop13Second_setDate"] = $data["EvnPLDispDop13Second_setDate"];
		}
		if (isset($data["EvnPLDispDop13Second_setDate_Range"][0])) {
			$queryParams["EvnPLDispDop13Second_setDate_Range_0"] = $data["EvnPLDispDop13Second_setDate_Range"][0];
		}
		if (isset($data["EvnPLDispDop13Second_setDate_Range"][1])) {
			$queryParams["EvnPLDispDop13Second_setDate_Range_1"] = $data["EvnPLDispDop13Second_setDate_Range"][1];
		}
		if (isset($data["EvnPLDispDop13Second_disDate"])) {
			$queryParams["EvnPLDispDop13Second_disDate"] = $data["EvnPLDispDop13Second_disDate"];
		}
		if (isset($data["EvnPLDispDop13Second_disDate_Range"][0])) {
			$queryParams["EvnPLDispDop13Second_disDate_Range_0"] = $data["EvnPLDispDop13Second_disDate_Range"][0];
		}
		if (isset($data["EvnPLDispDop13Second_disDate_Range"][1])) {
			$queryParams["EvnPLDispDop13Second_disDate_Range_1"] = $data["EvnPLDispDop13Second_disDate_Range"][1];
		}
		if (isset($data["EvnPLDispDop13Second_IsFinish"])) {
			$queryParams["EvnPLDispDop13Second_IsFinish"] = $data["EvnPLDispDop13Second_IsFinish"];
		}
		if (isset($data["EvnPLDispDop13Second_HealthKind_id"])) {
			$queryParams["EvnPLDispDop13Second_HealthKind_id"] = $data["EvnPLDispDop13Second_HealthKind_id"];
		}
		$queryParams["PersonDopDisp_Year"] = (isset($data["PersonDopDisp_Year"]))?$data["PersonDopDisp_Year"]:2013;
		$queryParams["Lpu_id"] = $data["Lpu_id"];
		if ($data["PersonPeriodicType_id"] != 2) {
			if ($callObject->getRegionNick() == "ufa") {
				$newFilterString = "(1 = 1) and EPLDD13.Lpu_id {$getLpuIdFilter} and coalesce(EPLDD13.DispClass_id,1) = 1 and date_part('year', EPLDD13.EvnPLDispDop13_consDT) = :PersonDopDisp_Year {$filterEPLDD13}";
				$filter = str_replace("(1 = 1)", $newFilterString, $filter);
			}
		}
		if (in_array($data["session"]["region"]["nick"], ["buryatiya", "krym"])) {
			if (!empty($data["UslugaComplex_id"])) {
				$filter .= " and euddvizit.UslugaComplex_id = :UslugaComplex_id ";
				$queryParams["UslugaComplex_id"] = $data["UslugaComplex_id"];
			}
		}
	}

	public static function selectParams_EvnPLDispDop13(Search_model $callObject, &$data, &$filter, &$queryParams)
	{
		if (isset($data["EvnPLDispDop13_setDate"])) {
			$queryParams["EvnPLDispDop13_setDate"] = $data["EvnPLDispDop13_setDate"];
		}
		if (isset($data["EvnPLDispDop13_setDate_Range"][0])) {
			$queryParams["EvnPLDispDop13_setDate_Range_0"] = $data["EvnPLDispDop13_setDate_Range"][0];
		}
		if (isset($data["EvnPLDispDop13_setDate_Range"][1])) {
			$queryParams["EvnPLDispDop13_setDate_Range_1"] = $data["EvnPLDispDop13_setDate_Range"][1];
		}
		if (isset($data["EvnPLDispDop13_disDate"])) {
			$queryParams["EvnPLDispDop13_disDate"] = $data["EvnPLDispDop13_disDate"];
		}
		if (isset($data["EvnPLDispDop13_disDate_Range"][0])) {
			$queryParams["EvnPLDispDop13_disDate_Range_0"] = $data["EvnPLDispDop13_disDate_Range"][0];
		}
		if (isset($data["EvnPLDispDop13_disDate_Range"][1])) {
			$queryParams["EvnPLDispDop13_disDate_Range_1"] = $data["EvnPLDispDop13_disDate_Range"][1];
		}
		if (isset($data["EvnPLDispDop13_IsFinish"])) {
			$queryParams["EvnPLDispDop13_IsFinish"] = $data["EvnPLDispDop13_IsFinish"];
		}
		if (isset($data["EvnPLDispDop13_IsRefusal"])) {
			$queryParams["EvnPLDispDop13_IsRefusal"] = $data["EvnPLDispDop13_IsRefusal"];
		}
		if (isset($data["EvnPLDispDop13_IsTwoStage"])) {
			$queryParams["EvnPLDispDop13_IsTwoStage"] = $data["EvnPLDispDop13_IsTwoStage"];
		}
		if (isset($data["EvnPLDispDop13_HealthKind_id"])) {
			$queryParams["EvnPLDispDop13_HealthKind_id"] = $data["EvnPLDispDop13_HealthKind_id"];
		}
		if (isset($data["EvnPLDispDop13_isPaid"])) {
			if ($data["session"]["region"]["nick"] == "ufa") {
				$filter .= ($data["EvnPLDispDop13_isPaid"] == 2)
					? "
						and exists (
							select EvnVizitDispDop_id
							from v_EvnVizitDispDop t1
							where t1.EvnVizitDispDop_pid = EvnPLDispDop13.EvnPLDispDop13_id and coalesce(t1.EvnVizitDispDop_IsPaid, 1) = 2
							limit 1
						)
					"
					: "
						and not exists (
							select EvnVizitDispDop_id
							from v_EvnVizitDispDop t1
							where t1.EvnVizitDispDop_pid = EvnPLDispDop13.EvnPLDispDop13_id and coalesce(t1.EvnVizitDispDop_IsPaid, 1) = 2
							limit 1
						)
					";
			} else {
				$queryParams["EvnPLDispDop13_isPaid"] = $data["EvnPLDispDop13_isPaid"];
			}
		}
		if (isset($data["EvnPLDispDop13Second_isPaid"])) {
			if ($data["session"]["region"]["nick"] == "ufa") {
				$filter .= ($data["EvnPLDispDop13Second_isPaid"] == 2)
					? "
						and exists (
							select EvnVizitDispDop_id
							from v_EvnVizitDispDop t1
							where t1.EvnVizitDispDop_pid = EPLDD13_SEC.EvnPLDispDop13_id and coalesce(t1.EvnVizitDispDop_IsPaid, 1) = 2
							limit 1
						)
					"
					: "
						and not exists (
							select EvnVizitDispDop_id
							from v_EvnVizitDispDop t1
							where t1.EvnVizitDispDop_pid = EPLDD13_SEC.EvnPLDispDop13_id and coalesce(t1.EvnVizitDispDop_IsPaid, 1) = 2
							limit 1
						)
					";
			} else {
				$queryParams["EvnPLDispDop13Second_isPaid"] = $data["EvnPLDispDop13Second_isPaid"];
			}
		}
		if (isset($data["EvnPLDispDop13_isMobile"])) {
			$queryParams["EvnPLDispDop13_isMobile"] = $data["EvnPLDispDop13_isMobile"];
		}
		if (isset($data["EvnPLDispDop13Second_isMobile"])) {
			$queryParams["EvnPLDispDop13Second_isMobile"] = $data["EvnPLDispDop13Second_isMobile"];
		}
		if (isset($data["EvnPLDispDop13Second_setDate"])) {
			$queryParams["EvnPLDispDop13Second_setDate"] = $data["EvnPLDispDop13Second_setDate"];
		}
		if (isset($data["EvnPLDispDop13Second_setDate_Range"][0])) {
			$queryParams["EvnPLDispDop13Second_setDate_Range_0"] = $data["EvnPLDispDop13Second_setDate_Range"][0];
		}
		if (isset($data["EvnPLDispDop13Second_setDate_Range"][1])) {
			$queryParams["EvnPLDispDop13Second_setDate_Range_1"] = $data["EvnPLDispDop13Second_setDate_Range"][1];
		}
		if (isset($data["EvnPLDispDop13Second_disDate"])) {
			$queryParams["EvnPLDispDop13Second_disDate"] = $data["EvnPLDispDop13Second_disDate"];
		}
		if (isset($data["EvnPLDispDop13Second_disDate_Range"][0])) {
			$queryParams["EvnPLDispDop13Second_disDate_Range_0"] = $data["EvnPLDispDop13Second_disDate_Range"][0];
		}
		if (isset($data["EvnPLDispDop13Second_disDate_Range"][1])) {
			$queryParams["EvnPLDispDop13Second_disDate_Range_1"] = $data["EvnPLDispDop13Second_disDate_Range"][1];
		}
		if (isset($data["EvnPLDispDop13Second_IsFinish"])) {
			$queryParams["EvnPLDispDop13Second_IsFinish"] = $data["EvnPLDispDop13Second_IsFinish"];
		}
		if (isset($data["EvnPLDispDop13Second_HealthKind_id"])) {
			$queryParams["EvnPLDispDop13Second_HealthKind_id"] = $data["EvnPLDispDop13Second_HealthKind_id"];
		}
		$queryParams["PersonDopDisp_Year"] = (isset($data["PersonDopDisp_Year"]))?$data["PersonDopDisp_Year"]:2013;
		$filter .= " and (PS.Person_deadDT >= :PersonDopDisp_Year||'-01-01' OR PS.Person_deadDT IS NULL)";
		$queryParams["Lpu_id"] = $data["Lpu_id"];
		$add_filter = "";
		$maxage = 999;
		$personPrivilegeCodeList = $callObject->EvnPLDispDop13_model->getPersonPrivilegeCodeList("{$queryParams["PersonDopDisp_Year"]}-01-01");
		if (in_array($data["session"]["region"]["nick"], ["ufa", "ekb", "kareliya", "penza", "astra"])) {
			$add_filter .= "
				or exists (
					select PersonPrivilegeWOW_id
					from v_PersonPrivilegeWOW
					where Person_id = PS.Person_id
					limit 1
				)
			";
		}
		$dateX = $callObject->EvnPLDispDop13_model->getNewDVNDate();
		if ( !empty($dateX) && $dateX <= date('Y-m-d') ) {
			$add_filter .= "
						or
						(dbo.Age2(PS.Person_BirthDay, CAST(:PersonDopDisp_YearEndDate as timestamp)) >= 40)
					";
		}
		else {
			// @task https://redmine.swan.perm.ru/issues/124302
			if (!in_array($data['session']['region']['nick'], array('kz')) && $data['PersonDopDisp_Year'] >= 2018) {
				$add_filter .= "
							or
							(PS.Sex_id = 1 and dbo.Age2(PS.Person_BirthDay, CAST(:PersonDopDisp_YearEndDate as timestamp)) between 49 and 73 and dbo.Age2(PS.Person_BirthDay, CAST(:PersonDopDisp_YearEndDate as timestamp)) % 2 = 1)
							or
							(PS.Sex_id = 2 and dbo.Age2(PS.Person_BirthDay, CAST(:PersonDopDisp_YearEndDate as timestamp)) between 48 and 73)
						";
			}
		}
		if (count($personPrivilegeCodeList) > 0) {
			$add_filter .= "
				or (
					(dbo.Age2(PS.Person_BirthDay, (select PPD_YearEndDate from mv)) BETWEEN 18 AND {$maxage}) and
					exists (
						select pp.PersonPrivilege_id
						from
							v_PersonPrivilege pp
							inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
						where pt.PrivilegeType_Code in ('" . implode("','", $personPrivilegeCodeList) . "')
						  and pp.Person_id = PS.Person_id
						  and pp.PersonPrivilege_begDate <= (select PPD_YearEndDate from mv)
						  and (pp.PersonPrivilege_endDate > (select PPD_YearEndDate from mv) or pp.PersonPrivilege_endDate is null)
						limit 1
					)
				)
			";
		}
		$DDfilter = "
			(
				(dbo.Age2(PS.Person_BirthDay, (select PPD_YearEndDate from mv)) - 21 >= 0 and (dbo.Age2(PS.Person_BirthDay, (select PPD_YearEndDate from mv)) - 21) % 3 = 0)
				{$add_filter}
			)
			and dbo.Age2(PS.Person_BirthDay, (select PPD_YearEndDate from mv)) <= {$maxage}
		";
		if ($data["session"]["region"]["nick"] == "perm") {
			$DDfilter .= "
				and not exists (
					select EvnPLDispProf_id
					from v_EvnPLDispProf
					where date_part('year', EvnPLDispProf_consDT) = :PersonDopDisp_Year and Person_id = PS.Person_id
					limit 1
				)
			";
		}
		if (!empty($data["Disp_MedStaffFact_id"]) || !empty($data["Disp_LpuSection_id"]) || !empty($data["Disp_LpuBuilding_id"])) {
			$disp_msf = $disp_msf2 = $disp_ls = $disp_ls2 = $disp_b = $disp_b2 = $join1 = $join2 = "";
			if (!empty($data["Disp_MedStaffFact_id"])) {
				$disp_msf = " and msf1.MedStaffFact_id = :MedStaffFact_id";
				$disp_msf2 = " and msf2.MedStaffFact_id = :MedStaffFact_id";
				$queryParams["MedStaffFact_id"] = $data["Disp_MedStaffFact_id"];
			} else {
				if (!empty($data["Disp_LpuSection_id"])) {
					$disp_ls = " and msf1.LpuSection_id = :LpuSection_id";
					$disp_ls2 = " and msf2.LpuSection_uid = :LpuSection_id";
					$queryParams["LpuSection_id"] = $data["Disp_LpuSection_id"];
				}
				if (!empty($data["Disp_LpuBuilding_id"])) {
					$join1 = " left join v_LpuSection ls1 on ls1.LpuSection_id = msf1.LpuSection_id ";
					$disp_b = " and ls1.LpuBuilding_id = :LpuBuilding_id";
					$join2 = " left join v_LpuSection ls3 on ls3.LpuSection_id = msf2.LpuSection_uid ";
					$disp_b2 = " and ls3.LpuBuilding_id = :LpuBuilding_id";
					$queryParams["LpuBuilding_id"] = $data["Disp_LpuBuilding_id"];
				}
			}
			$filter .= " 
				and (
					exists (
						select msf1.EvnVizitDispDop_id 
						from v_EvnVizitDispDop msf1 {$join1}
						where msf1.EvnVizitDispDop_pid = EvnPLDispDop13.EvnPLDispDop13_id {$disp_b} {$disp_msf} {$disp_ls}
						limit 1
					) or
					exists (
						select msf2.EvnUslugaDispDop_id 
						from v_EvnUslugaDispDop msf2 {$join2} 
						where msf2.EvnUslugaDispDop_pid = EvnPLDispDop13.EvnPLDispDop13_id {$disp_b2} {$disp_msf2} {$disp_ls2}
						limit 1
					)
				)
			";
		}
		$filter .= " and (EvnPLDispDop13.EvnPLDispDop13_id is not null or ({$DDfilter}))";
		if (isset($data["EvnPLDisp_UslugaComplex"]) && $data["EvnPLDisp_UslugaComplex"] > 0) {
			$filter .= "
				and exists (
					select UslugaComplex_id
					from v_EvnUslugaDispDop
					where EvnUslugaDispDop_didDate is not null
					  and UslugaComplex_id = :EvnPLDisp_UslugaComplex
					  and EvnUslugaDispDop_rid = EPLDD13.EvnPLDispDop13_id
					limit 1
				)
			";
			$queryParams["EvnPLDisp_UslugaComplex"] = $data["EvnPLDisp_UslugaComplex"];
		}
		if (in_array($data["session"]["region"]["nick"], ["buryatiya", "krym"])) {
			if (!empty($data["UslugaComplex_id"])) {
				$filter .= " and euddvizit.UslugaComplex_id = :UslugaComplex_id ";
				$queryParams["UslugaComplex_id"] = $data["UslugaComplex_id"];
			}
		}
	}
	//selectParams_EvnPLDispDop13
}